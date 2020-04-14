<?php 

// start session
session_start();

// import all the necessary liberaries
require_once '../includes/config.php';
require_once '../includes/library/Requests.php';
require_once '../includes/utils.php'; // include utility liberary
require_once '../includes/Nnochi.php';

// make sure Requests can load internal classes
Requests::register_autoloader();

// check if request method is post
if ($_SERVER['REQUEST_METHOD'] != 'POST') {
    die(); // stop script
}

// check if we are the one that serve the page
if (!hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
    die(); // stop script
}

date_default_timezone_set('UTC');

if (isset($_SESSION['last_auth_time']) && time() < $_SESSION['last_auth_time']) {
    // update the time
    $_SESSION['last_auth_time'] = time() + 1800; // expire in 30 minutes

} else {
    // clear the user's login session
    unset($_SESSION['auth']);
    unset($_SESSION['user_id']);

    // redirect user to login pages
    header('Location: '. BASE_URL . 'user/login.html');
    exit;
}

// validate user submitted form
if (!validatedSubmittedForm()) {
    // send error message back to client
    echo json_encode([
        'success' => false,
        'error_msg' => 'Transaction can not be processed due to an error.'
    ]);

    exit;
}

// mysql configuration
$db = $config['db']['mysql'];
        
// enable mysql exception
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

try {
    // connect to database
    $conn = new mysqli($db['host'], $db['username'], $db['password'], $db['dbname']);

    // check connection
    if ($conn->connect_error) {
        throw new mysqli_sql_exception('Database connection failed: ' . $conn->connect_error);
    }

    $user_entered_usd = round($_POST['amount'], 2, PHP_ROUND_HALF_DOWN); // user's entered withdrawal amount in USD

    // check if crypto currency is supported
    $query = 'SELECT 1 FROM crypto_currency_supported WHERE symbol = ?  LIMIT 1';
    $stmt = $conn->prepare($query); // prepare statement
    $stmt->bind_param('s', $_POST['currency']);
    $stmt->execute();
    $stmt->store_result(); // needed for num_rows

    if ($stmt->num_rows < 1) {
        // close connection to database
        $stmt->close();
        $conn->close();

        // send error message back to client
        echo json_encode([
            'success' => false,
            'error_msg' => 'Transaction can not be processed due to an error.'
        ]);

        exit;
    }

    $stmt->close();

    // check if user's placed withdrawal amount is available
    $query = 'SELECT totalBalance, availableBalance FROM user_account WHERE userID = ? LIMIT 1';
    $stmt = $conn->prepare($query); // prepare statement
    $stmt->bind_param('i', $_SESSION['user_id']);
    $stmt->execute();
    $stmt->bind_result($total_balance, $available_balance);
    $stmt->fetch();

    if ($user_entered_usd > round($available_balance, 2, PHP_ROUND_HALF_DOWN)) {
        // close connection to database
        $stmt->close();
        $conn->close();

        // send error message back to client
        echo json_encode([
            'success' => false,
            'error_msg' => 'Your available balance is insufficient for the requested amount.'
        ]);

        exit;
    }

    $stmt->close();

    // get the convert rate of USD to BTC
    try {
        $req_url = 'https://blockchain.info/tobtc?currency=USD&value='.$user_entered_usd;
        $response = Requests::get($req_url);

        if ($response->success) {
            $withdraw_amount_in_btc = $response->body;

        } else {
            throw new Exception("Blockchain API failed to convert value to btc.");
        }

    } catch (Exception $e) {
        // close connection to database
        $conn->close();

        // send error message back to client
        echo json_encode([
            'success' => false,
            'error_msg' => 'Transaction can not be processed due to an error.'
        ]);

        exit;
    }

    // get current Bitcoin transaction fee predictions (using Bitcoinfees Developer API)
    try {
        $req_url = 'https://bitcoinfees.earn.com/api/v1/fees/recommended';
        $response = Requests::get($req_url);

        if ($response->success) {
            $fees = json_decode($response->body, true); // decode to associative array
            $transaction_fee = $fees['fastestFee'];  // transaction fee-per-byte in satoshi

        } else {
            throw new Exception("Unable to fetch bitcoin fees.");
        }

    } catch (Exception $e) {
        $transaction_fee = 10; // fallback transaction fee-per-byte in satoshi
    }

    // generate transaction ID for this withdrawal
    $transaction_id = randomText('alnum', 32);

    // initiate withdrawal for user's specified amount
    $blockchain_wallet_url = 'http://localhost:3000/merchant/' . BC_GUID . '/payment';
    $amount_in_satoshi = $withdraw_amount_in_btc * 100000000;
    $parameters = 
        'to=' . $_POST['walletaddress'] . 
        '&amount=' . $amount_in_satoshi . 
        '&password=' . BC_WALLET_PASSWORD . 
        '&fee_per_byte=' . $transaction_fee;

    try {
        $req_url = $blockchain_wallet_url . '?' . $parameters;
        $response = Requests::post($req_url);

        if ($response->success) {
            $transaction_response = json_decode($response->body, true); // decode to associative array

            // check if transaction wasn't sucessfull
            if (!$transaction_response['success']) {
                throw new Exception("Withdrawal failed.");
            }

        } else {
            throw new Exception("Withdrawal failed.");
        }

        // template parser
        $nnochi = new Nnochi();

        $conn->begin_transaction(); // start transaction

         // add withdrawal to user's transactions table
         $query = 
            'INSERT INTO user_transactions (transactionID, userID, currency, transaction, amount, amountInUSD, address, committed, time)
            VALUES(?, ?, ?, ?, ?, ?, ?, ?, ?)';

        $stmt = $conn->prepare($query); // prepare statement
        $stmt->bind_param(
            'sissddsii', 
            $transaction_id, 
            $_SESSION['user_id'], 
            $_POST['currency'], 
            $transaction_type, 
            $withdraw_amount_in_btc,
            $user_entered_usd, 
            $transaction_response['to'],
            $transaction_committed, 
            $transaction_time
        );
        $transaction_type = 'withdrawal';
        $transaction_committed = 1;
        $transaction_time = time();
        $stmt->execute();
        $stmt->close();

        // update user's account
        $query = 'UPDATE user_account SET availableBalance = availableBalance - ?, totalBalance = totalBalance - ? WHERE userID = ? LIMIT 1';
        $stmt = $conn->prepare($query); // prepare statement
        $stmt->bind_param('di', $user_entered_usd, $user_entered_usd, $user_id);
        $stmt->execute();
        $stmt->close();

        // send user a notification
        $query = 
            'INSERT INTO users_notification (msgID, userID, title, content, time)
                VALUES(?, ?, ?, ?, ?)';

        $stmt = $conn->prepare($query); // prepare statement
        $stmt->bind_param(
            'sissi', 
            $msg_id, 
            $user_id, 
            $msg_title, 
            $msg_content, 
            $msg_time
        );
        $msg_id = randomText('hexdec', 32);
        $msg_title = 'Transaction';
        $msg_content = $nnochi->render(
            '../templates/withdrawal_msg.txt',
            [
                'amount' => $transaction_response['amounts'] / 100000000,
                'currency' => $_POST['currency'],
                'amount_in_usd' => number_format($user_entered_usd, 2).' USD',
                'address' => $transaction_response['to'],
                'balance' => number_format($available_balance - $user_entered_usd, 2).' USD',
                'txn_id' => $transaction_id
            ]
        );
        $msg_time = time();
        $stmt->execute();
        $stmt->close();

        $conn->commit(); // commit all the transaction

        // close connection to database
        $conn->close();

        // return successfully response back to client
        echo json_encode([
            'success' => true,
            'total_balance' => number_format($total_balance - $user_entered_usd, 2),
            'available_balance' => number_format($available_balance - $user_entered_usd, 2)
        ]);

    } catch (mysqli_sql_exception $e) {
        $conn->rollback(); // remove all queries from queue if error occured (undo)
        $conn->close(); // close connection to database

        // send error message back to client
        echo json_encode([
            'success' => false,
            'error_msg' => 'Transaction can not be processed due to an error.'
        ]);

        exit;

    } catch (Exception $e) {
        // close connection to database
        $conn->close();

        // send error message back to client
        echo json_encode([
            'success' => false,
            'error_msg' => 'Transaction can not be processed due to an error.'
        ]);

        exit;
    }

}  catch (mysqli_sql_exception $e) {
    echo 'Mysql error: ' . $e->getMessage() . PHP_EOL;

} catch (Exception $e) { // catch other exception
    echo 'Caught exception: ' .  $e->getMessage() . PHP_EOL;
}

// utility function to validate user's submitted form
function validatedSubmittedForm() {
    if (!preg_match("/^[A-Z]+$/", $_POST['currency'])) {
        return false;

    } else if (!preg_match("/^([0-9]+|[0-9]+.?[0-9]+)$/", $_POST['amount'])) {
        return false;

    } else if (!preg_match("/^[a-zA-Z0-9]+$/", $_POST['walletaddress'])) {
        return false;
    }

    return true;
}

?>