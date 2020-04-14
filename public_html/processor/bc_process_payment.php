<?php 

// start session
session_start();

// import all the necessary liberaries
require_once '../includes/config.php';
require_once '../includes/library/Requests.php';
require_once '../includes/utils.php'; // include utility liberary

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

    // user's entered payment in USD
    $user_entered_usd = round($_POST['amount'], 2, PHP_ROUND_HALF_DOWN);

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

    // check if user's typed amount is within the range for chosed package
    $query = 'SELECT package, minAmount, maxAmount FROM crypto_investment_packages WHERE id = ? LIMIT 1';
    $stmt = $conn->prepare($query); // prepare statement
    $stmt->bind_param('i', $_POST['package_id']);
    $stmt->execute();
    $stmt->bind_result($package_name, $min_amount, $max_amount);
    $stmt->fetch();

    if (!($user_entered_usd >= $min_amount && ($user_entered_usd <= $max_amount || $max_amount == 0))) {
        // close connection to database
        $stmt->close();
        $conn->close();

        // send error message back to client
        echo json_encode([
            'success' => false,
            'error_msg' => 'Entered amount is not within the price range for this package.'
        ]);

        exit;
    }

    $stmt->close();

    // get user's information
    $query = 'SELECT firstName, lastName, email FROM users WHERE id = ? LIMIT 1';
    $stmt = $conn->prepare($query); // prepare statement
    $stmt->bind_param('i', $_SESSION['user_id']);
    $stmt->execute();
    $stmt->bind_result($user_first_name, $user_last_name, $user_email);
    $stmt->fetch();
    $stmt->close();

    // get the convert rate of USD to BTC
    try {
        $req_url = 'https://blockchain.info/tobtc?currency=USD&value='.$user_entered_usd;
        $response = Requests::get($req_url);

        if ($response->success) {
            $amount_in_btc = $response->body;

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

    // generate transaction ID for this order
    $transaction_id = randomText('alnum', 32);
    
    try {
        // check if we have created a gap of over 20 unused addresses
        $req_url = 'https://api.blockchain.info/v2/receive/checkgap?xpub=' . BC_XPUB_ADDRESS . '&key=' . BC_API_KEY;
        $response = Requests::get($req_url);

        if ($response->success) {
            $unuse_address;
            $decoded_response = json_decode($response->body, true); // decode to associative array

            // check to reuse already generated address
            if ($decoded_response["gap"] > 18) {
                // get the first expired unpaid address
                $query = 'SELECT transactionID, address FROM unpaid_address WHERE expirationTime < ? LIMIT 1';
                $stmt = $conn->prepare($query); // prepare statement
                $stmt->bind_param('i', $current_time);
                $current_time = time();
                $stmt->execute();
                $stmt->store_result(); // needed for num_rows

                // check if address is available for reuse
                if ($stmt->num_rows > 0) {
                    $stmt->bind_result($current_txn_id, $unuse_address);
                    $stmt->fetch();
                    $stmt->close();

                } else {
                    // close connection to database
                    $stmt->close();
                    $conn->close();

                    // send error message back to client
                    echo json_encode([
                        'success' => false,
                        'error_msg' => "New payment address can't be generated due to error. Please try again later."
                    ]);

                    exit;
                }

                // update "unpaid_address" table
                $query = 'UPDATE unpaid_address SET transactionID = ?, expirationTime = ? WHERE transactionID = ? LIMIT 1';
                $stmt = $conn->prepare($query); // prepare statement
                $stmt->bind_param('sis', $transaction_id, $expiration_time, $current_txn_id);
                $expiration_time = time() + (60 * 30); // current time + (seconds in a minute * 30)
                $stmt->execute();
                $stmt->close();

            } else { // generate new address
                // generating a receiving address for BTC using Blockchain API
                $callback_url = BASE_URL . 'bc_pn_handler?txn_id=' . $transaction_id . '&secret=' . BC_CALLBACK_SECRET;
                $blockchain_url = 'https://api.blockchain.info/v2/receive';
                $parameters = 'xpub=' . BC_XPUB_ADDRESS . '&callback=' . urlencode($callback_url) . '&key=' . BC_API_KEY;

                $req_url = $blockchain_url . '?' . $parameters;
                $response = Requests::get($req_url);

                if ($response->success) {
                    $decoded_response = json_decode($response->body, true); // decode to associative array

                } else {
                    throw new Exception("Blockchain API failed to generate receiving address.");
                }

                // add new generated address to "unpaid_address" table
                $query = 
                    'INSERT INTO unpaid_address (transactionID, address, expirationTime) 
                    VALUES (?, ?, ?)';

                $stmt = $conn->prepare($query); // prepare statement
                $stmt->bind_param(
                    'ssi', 
                    $transaction_id, 
                    $decoded_response['address'],
                    $expiration_time
                );
                $expiration_time = time() + (60 * 30); // current time + (seconds in a minute * 30)
                $stmt->execute();
                $stmt->close();

                // set address
                $unuse_address = $decoded_response['address'];
            }

            // add transanction to deposit table for later verification
            $query = 
                'INSERT INTO verify_user_deposit (transactionID, userID, packageID, currency, amount, amountInUSD, time) 
                VALUES (?, ?, ?, ?, ?, ?, ?)';

            $stmt = $conn->prepare($query); // prepare statement
            $stmt->bind_param(
                'siisddi', 
                $transaction_id, 
                $_SESSION['user_id'], 
                $_POST['package_id'], 
                $_POST['currency'], 
                $amount_in_btc,
                $user_entered_usd,  
                $transaction_time
            );
            $transaction_time = time();
            $stmt->execute();
            $stmt->close();

            // close connection to database
            $conn->close();

            // return result to be processed by client
            echo json_encode([
                'success' => true,
                'amount' => $amount_in_btc.' '.$_POST['currency'],
                'wallet_address' => $unuse_address
            ]);

        } else {
            throw new Exception("Blockchain API failed to retrieve gap.");
        }

    } catch (Exception $e) {
        // close connection to database
        $conn->close();

        // log the error to a file
        error_log('Caught exception: '.$e->getMessage().PHP_EOL, 3, CUSTOM_ERR_DIR.'custom_errors.log');

        // send error message back to client
        echo json_encode([
            'success' => false,
            'error_msg' => 'Transaction can not be processed due to an error.'
        ]);

        exit;
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
    }

    return true;
}

?>