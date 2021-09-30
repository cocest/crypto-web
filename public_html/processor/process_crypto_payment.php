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

    // check if user's typed amount is within the range for chose package
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

    // Coinqvest checkout
    try {
        $api_url = 'https://www.coinqvest.com/api/v1/checkout';

        $fields = [
            'charge' => [
                'customerId' => $_SESSION['user_id'],
                'currency' => 'USD',
                'lineItems' => [
                    'description' => "$package_name plan",
                    'netAmount' => $user_entered_usd
                ]
            ],
            'settlementCurrency' => 'USD',
            'webhook' => BASE_URL . 'payment_handler'
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
            throw new Exception("Checkout can't be created due to an error.");
        }

        // prepare to commit the checkout
        $checkout_id = $request_data['id'];
        $selected_payment_method;

        // extract user's chose payment cureency
        foreach ($request_data['paymentMethods'] as $payment_method) {
            if ($payment_method['assetCode'] == $_POST['currency']) {
                $selected_payment_method = $payment_method;
                break;
            }
        }

        // commit the checkout
        $api_url = 'https://www.coinqvest.com/api/v1/checkout/commit';
        $fields = [
            'checkoutId' => $checkout_id,
            'assetCode' => $selected_payment_method['assetCode']
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
            throw new Exception("Checkout can't be commited due to an error.");
        }

    } catch (\Exception $e) {
        // close connection to database
        $conn->close();

        // log the error to a file
        error_log('Caught exception: '.$e->getMessage().PHP_EOL, 3, CUSTOM_ERR_DIR.'custom_errors.log');

        // send error message back to client
        echo json_encode([
            'success' => false,
            'error_msg' => 'Transaction can not be processed due to an error.'
        ]);

        die();
    }

    // add transanction to deposit table for later verification
    $query = 
    'INSERT INTO verify_user_deposit (transactionID, userID, packageID, currency, amount, amountInUSD, address, time) 
    VALUES (?, ?, ?, ?, ?, ?, ?, ?)';

    $stmt = $conn->prepare($query); // prepare statement
    $stmt->bind_param(
        'siisddsi', 
        $request_data['checkoutId'], 
        $_SESSION['user_id'], 
        $_POST['package_id'], 
        $request_data['depositInstructions']['assetCode'], 
        $request_data['depositInstructions']['amount'],
        $user_entered_usd,  
        $request_data['depositInstructions']['address'], 
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
        'amount' => $request_data['depositInstructions']['amount'].' '.$request_data['depositInstructions']['assetCode'],
        'wallet_address' => $request_data['depositInstructions']['address'],
        'memo' => $request_data['depositInstructions']['memo'], 
        'memoType' => $request_data['depositInstructions']['memoType'], 
        'destinationTag' => $request_data['depositInstructions']['destinationTag']
    ]);

} catch (mysqli_sql_exception $e) {
    echo 'Mysql error: ' . $e->getMessage() . PHP_EOL;

} catch (Exception $e) { // catch other exception
    echo 'Caught exception: ' .  $e->getMessage() . PHP_EOL;
}

?>