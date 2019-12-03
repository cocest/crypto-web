<?php 

// start session
session_start();

// error handler function
function customError($errno, $errstr) {
    echo "<b>Error:</b> [$errno] $errstr<br>";
    die();
}

// set the handler
set_error_handler('customError');

// import all the necessary liberaries
require_once '../includes/config.php';
require_once '../includes/utils.php'; // include utility liberary
require_once '../includes/coinpayment/CoinpaymentsAPI.php';
require_once '../includes/coinpayment/CoinpaymentsCurlRequest.php';
require_once '../includes/coinpayment/CoinpaymentsValidator.php';

// check if request method is post
if ($_SERVER['REQUEST_METHOD'] != 'POST') {
    die(); // stop script
}

// check if we are the one that serve the page
if (!hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
    die(); // stop script
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

date_default_timezone_set('UTC');

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

    if ($user_entered_usd < $min_amount || $user_entered_usd > $max_amount) {
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
    
    // Create a new API wrapper instance
    $cps_api = new CoinpaymentsAPI(CPS_PRIVATE_KEY, CPS_PUBLIC_KEY, 'json');

    // set payment fields
    $payment_fields = [
        'currency1' => 'USD',
        'currency2' => $_POST['currency'],
        'amount' => $user_entered_usd,
        'buyer_email' => $user_email,
        'buyer_name' => $user_last_name.' '.$user_first_name,
        'item_name' => $package_name.' package',
        'ipn_url' => BASE_URL . 'ipn_handler' // Coinpayments API call this url after completion or failure
    ];

    // make payment
    try {
        $response = $cps_api->CreateCustomTransaction($payment_fields);

        // check for API call success
        if ($response['error'] != 'ok') {
            throw new Exception($response['error']);
        }

        // add payment to user's transactions table
        $query = 
            'INSERT INTO user_transactions (transactionID, userID, currency, transaction, ammount, amountInUSD, committed, time)
             VALUES(?, ?, ?, ?, ?, ?, ?, ?)';

        $stmt = $conn->prepare($query); // prepare statement
        $stmt->bind_param(
            'sissddii', 
            $response['result']['txn_id'], 
            $_SESSION['user_id'], 
            $_POST['currency'], 
            $transaction_type, 
            $response['result']['amount'],
            $user_entered_usd, 
            $transaction_committed, 
            $transaction_time
        );
        $transaction_type = 'deposit';
        $transaction_committed = 0;
        $transaction_time = time();
        $stmt->execute();
        $stmt->close();

        // update user's account table
        $query = 'UPDATE user_account SET totalBalance = totalBalance + ? WHERE userID = ? LIMIT 1';
        $stmt = $conn->prepare($query); // prepare statement
        $stmt->bind_param('di', $user_entered_usd, $_SESSION['user_id']);
        $stmt->execute();
        $stmt->close();

        // close connection to database
        $conn->close();

        // return result to be processed by client
        echo json_encode([
            'success' => true,
            'amount' => $response['result']['amount'].' '.$_POST['currency'],
            'wallet_address' => $response['result']['address'],
            'payment_timeout' => $response['result']['timeout'],
            'status_url' => $response['result']['status_url'],
            'qrcode_url' => $response['result']['qrcode_url']
        ]);

    } catch (Exception $e) {
        // close connection to database
        $conn->close();

        // send error message back to client
        echo json_encode([
            'success' => false,
            'error_msg' => 'Transaction can not be processed due to an error. Please, try again later.'
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