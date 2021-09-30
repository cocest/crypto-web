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
        'error_msg' => 'Withdrawal can not be processed due to an error.'
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

    // Coinqvest withdrawal
    try {
        // get wallet to withdraw from
        $api_url = 'https://www.coinqvest.com/api/v1/wallet?assetCode=USD';
        $headers = [
            'X-Basic' => hash('sha256', CQ_API_KEY . ':' . CQ_API_SECRET),
            'Accepts' => 'application/json'
        ];
        $response = Requests::get($api_url, $headers); // fetch data
        $request_data = json_decode($response->body, true); // decode to associative array

        // check if is not successfully
        if (!($response->success && $response->status_code == 200)) {
            // throw an exception
            throw new Exception("Unable to connect to the wallet.");
        }

        $withdraw_wallet = $request_data['wallet'];

        // check if withdrawal amount is available in the wallet
        if ($user_entered_usd > $withdraw_wallet['balance']) {
            // throw an exception
            throw new Exception("Not enough balance in the wallet.");
        }

        // initiate withdrawal
        $api_url = 'https://www.coinqvest.com/api/v1/withdrawal';
        $fields;

        switch ($_POST['currency']) {
            case 'BTC':
            case 'ETH':
            case 'LTC':
                $fields= [
                    'sourceAsset' => $withdraw_wallet['assets']['id'],
                    'sourceAmount' =>  $user_entered_usd,
                    'targetNetwork' => $_POST['currency'],
                    'targetAccount' => [
                        'address' => $_POST['walletaddress']
                    ]
                ];

                break;

            case 'XRP':
                $fields= [
                    'sourceAsset' => $withdraw_wallet['assets']['id'],
                    'sourceAmount' =>  $user_entered_usd,
                    'targetNetwork' => $_POST['currency'],
                    'targetAccount' => [
                        'account' => $_POST['walletaddress'],
                        'destinationTag' => $_POST['desttag']
                    ]
                ];

                break;
            
            default:
                // you shoudn't be here
                break;
        }

        $headers = [
            'X-Basic' => hash('sha256', CQ_API_KEY . ':' . CQ_API_SECRET),
            'Accepts' => 'application/json'
        ];

        $response = Requests::post($api_url, $headers, json_encode($fields));
        $request_data = json_decode($response->body, true); // decode to associative array

        // check if is not successfully
        if (!($response->success && $response->status_code == 200)) {
            // throw an exception
            var_dump($response);
            throw new Exception("Unable to initiate withdrawal.");
        }

        // set the script to run for additional 130 seconds or more
        set_time_limit(130);

        // commit the withdrawal
        $api_url = 'https://www.coinqvest.com/api/v1/withdrawal/commit';

        $fields = [
            'withdrawalId' => $request_data['withdrawal']['id']
        ];

        $headers = [
            'X-Basic' => hash('sha256', CQ_API_KEY . ':' . CQ_API_SECRET),
            'Accepts' => 'application/json'
        ];

        $response = Requests::post($api_url, $headers, json_encode($fields));
        $request_data = json_decode($response->body, true); // decode to associative array

        // check if transaction initialization was not successfully
        if (!($response->success && $response->status_code == 200)) {
            // throw an exception
            var_dump($response);
            throw new Exception("Withdrawal can't be commited due to an error.");
        }

        try {
            // template parser
            $nnochi = new Nnochi();

            $conn->begin_transaction(); // start transaction

            // add withdrawal to user's transactions table
            $query = 
                'INSERT INTO user_transactions (transactionID, userID, transactionHash, currency, transaction, 
                amount, amountInUSD, address, committed, time) VALUES(?, ?, ?, ?, ?, ?, ?, ?, ?, ?)';

            $stmt = $conn->prepare($query); // prepare statement
            $stmt->bind_param(
                'sisssddsii', 
                $transaction_id, 
                $_SESSION['user_id'], 
                $transaction_hash, 
                $_POST['currency'], 
                $transaction_type, 
                $withdraw_amount_in_crypto,
                $user_entered_usd, 
                $_POST['walletaddress'],
                $transaction_committed, 
                $transaction_time
            );
            $transaction_id = $request_data['withdrawal']['id'];
            $transaction_hash = '';
            $withdraw_amount_in_crypto = $request_data['withdrawal']['targetAmount'];
            $transaction_type = 'withdrawal';
            $transaction_committed = 1;
            $transaction_time = time();
            $stmt->execute();
            $stmt->close();

            // update user's account
            $query = 'UPDATE user_account SET availableBalance = availableBalance - ?, totalBalance = totalBalance - ? WHERE userID = ? LIMIT 1';
            $stmt = $conn->prepare($query); // prepare statement
            $stmt->bind_param('ddi', $user_entered_usd, $user_entered_usd, $_SESSION['user_id']);
            $stmt->execute();
            $stmt->close();

            // send user a notification
            $query = 'INSERT INTO users_notification (msgID, userID, title, content, time) VALUES(?, ?, ?, ?, ?)';
            $stmt = $conn->prepare($query); // prepare statement
            $stmt->bind_param(
                'sissi', 
                $msg_id, 
                $_SESSION['user_id'], 
                $msg_title, 
                $msg_content, 
                $msg_time
            );
            $msg_id = bin2hex(openssl_random_pseudo_bytes(16));
            $msg_title = 'Withdrawal';
            $msg_content = $nnochi->render(
                '../templates/withdrawal_msg.txt',
                [
                    'amount' => $withdraw_amount_in_crypto,
                    'currency' => $_POST['currency'],
                    'amount_in_usd' => number_format($user_entered_usd, 2),
                    'address' => $_POST['walletaddress'],
                    'balance' => number_format($available_balance - $user_entered_usd, 2),
                    'txn_id' => $transaction_hash
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

        } catch (Exception $e) {
            $conn->rollback(); // remove all queries from queue if error occured (undo)
            $conn->close(); // close connection to database

            // send error message back to client
            echo json_encode([
                'success' => false,
                'error_msg' => 'Unable to record the withdrawal due to an error.'
            ]);
        }

    } catch (\Exception $e) {
        // close connection to database
        $conn->close();

        // log the error to a file
        error_log('Caught exception: '.$e->getMessage().PHP_EOL, 3, CUSTOM_ERR_DIR.'custom_errors.log');

        // send error message back to client
        echo json_encode([
            'success' => false,
            'error_msg' => 'Withdrawal can not be processed due to an error.'
        ]);

        die();
    }

} catch (mysqli_sql_exception $e) {
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