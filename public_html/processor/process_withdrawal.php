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

    $user_entered_usd = round($_POST['amount'], 2, PHP_ROUND_HALF_DOWN); // user's entered withdrawal amount in USD
    $crypto_withdraw_amount = 0;

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

    // get crypto_exchange_rate_status
    $query = 'SELECT isUpdating, nextUpdateTime FROM crypto_exchange_rate_status WHERE id = ? LIMIT 1';
    $stmt = $conn->prepare($query); // prepare statement
    $stmt->bind_param('i', $status_id);
    $status_id = 1;
    $stmt->execute();
    $stmt->bind_result($is_updating, $next_update_time);
    $stmt->fetch();
    $stmt->close();

    // Create a new API wrapper instance
    $cps_api = new CoinpaymentsAPI(CPS_PRIVATE_KEY, CPS_PUBLIC_KEY, 'json');

    // check if convert value is not up to date
    if (time() > $next_update_time && $is_updating == 0) {
        // notify other request that convert rate table is about to be updated
        $query = 'UPDATE crypto_exchange_rate_status SET isUpdating = ?, nextUpdateTime = ? LIMIT 1';
        $ext_status_stmt = $conn->prepare($query); // prepare statement
        $ext_status_stmt->bind_param('ii', $status_update_state, $status_next_update_time);
        $status_update_state = 1;
        $status_next_update_time = $next_update_time;
        $ext_status_stmt->execute();

        // fetch convert rate for usd to cryptocurrrency
        try {
            $response = $cps_api->GetShortRates();

            // check for API call success
            if ($response['error'] != 'ok') {
                throw new Exception($response['error']);
            }

            // extract the needed convert rate
            $user_currency_rate_btc = $response['result'][$_POST['currency']]['rate_btc'];
            $usd_rate_btc = $response['result']['USD']['rate_btc']; // dollar
            $btc_rate_btc = $response['result']['BTC']['rate_btc']; // bitcoin
            $eth_rate_btc = $response['result']['ETH']['rate_btc']; // ethereum
            $xrp_rate_btc = $response['result']['XRP']['rate_btc']; // ripple

            // convert user's entered USD to chosen cryptocurrency exchange amount
            $crypto_withdraw_amount = ($user_entered_usd * $usd_rate_btc) / $user_currency_rate_btc;

            // update convert rate table
            $query = 'UPDATE crypto_exchange_rate SET rateBTC = ? WHERE currency = ? LIMIT 1';
            $stmt = $conn->prepare($query); // prepare statement
            $stmt->bind_param('ds', $rate_btc, $rate_currency);

            // USD
            $rate_btc = $usd_rate_btc;
            $rate_currency = 'USD';
            $stmt->execute();

            // BTC
            $rate_btc = $btc_rate_btc;
            $rate_currency = 'BTC';
            $stmt->execute();

            // ETH
            $rate_btc = $eth_rate_btc;
            $rate_currency = 'ETH';
            $stmt->execute();

            // XRP
            $rate_btc = $xrp_rate_btc;
            $rate_currency = 'XRP';
            $stmt->execute();

            $stmt->close();

            // notify other request that convert rate table is updated
            $status_update_state = 0;
            $status_next_update_time = time() + (10 * 60); // update should occure in next 10 minutes
            $ext_status_stmt->execute();
            $ext_status_stmt->close();

        } catch (Exception $e) {
            // reset table update status
            $status_update_state = 0;
            $status_next_update_time = $next_update_time;
            $ext_status_stmt->execute();
            $ext_status_stmt->close();

            // close connection to database
            $conn->close();
            
            // send error message back to client
            echo json_encode([
                'success' => false,
                'error_msg' => 'Transaction can not be processed due to an error. Please, try again later.'
            ]);

            exit;
        }

    } else { // convert rate is up to date
        // fetch convert rate from table
        $query = 'SELECT * FROM crypto_exchange_rate';
        $stmt = $conn->prepare($query); // prepare statement
        $stmt->execute();
        $result = $stmt->get_result();
        $crypto_exchange_rates = [];
        while ($row = $result->fetch_assoc()) {
            $crypto_exchange_rates = array_merge(
                $crypto_exchange_rates,
                [
                    $row['currency'] => $row['rateBTC']
                ]
            );
        }
    
        $stmt->close();

        // extract the needed convert rate
        $user_currency_rate_btc = $crypto_exchange_rates[$_POST['currency']];
        $usd_rate_btc = $crypto_exchange_rates['USD']; // dollar

        // convert user's entered USD to choose cryptocurrency exchange amount
        $crypto_withdraw_amount = ($user_entered_usd * $usd_rate_btc) / $user_currency_rate_btc;
    }

    // initiate withdrawal for user's specified amount
    // set withdrawal details
    $withdrawal = [
        'amount' => $crypto_withdraw_amount,
        'currency' => $_POST['currency'],
        'add_tx_fee' => 1, //  If set to 1, add the coin TX fee to the withdrawal amount so the sender pays the TX fee instead of the receiver
        'address' => $_POST['walletaddress'], // start here
        'auto_confirm' => 1,
        'ipn_url' => BASE_URL . 'ipn_handler' // Coinpayments API call this url after completion or failure
    ];

    // request for withdrawal
    try {
        $response = $cps_api->CreateWithdrawal($withdrawal);

        // check for API call success
        if ($response['error'] != 'ok') {
            throw new Exception($response['error']);
        }
        
        // add withdrawal to user's transactions table
        $query = 
            'INSERT INTO user_transactions (transactionID, userID, currency, transaction, ammount, amountInUSD, committed, time)
             VALUES(?, ?, ?, ?, ?, ?, ?, ?)';

        $stmt = $conn->prepare($query); // prepare statement
        $stmt->bind_param(
            'sissddii', 
            $response['result']['id'], 
            $_SESSION['user_id'], 
            $_POST['currency'], 
            $transaction_type, 
            $response['result']['amount'],
            $user_entered_usd, 
            $transaction_committed, 
            $transaction_time
        );
        $transaction_type = 'withdrawal';
        $transaction_committed = 0;
        $transaction_time = time();
        $stmt->execute();
        $stmt->close();

        // update user's account table
        $query = 'UPDATE user_account SET availableBalance = ? WHERE userID = ? LIMIT 1';
        $stmt = $conn->prepare($query); // prepare statement
        $stmt->bind_param('di', $new_available_balance, $_SESSION['user_id']);
        $new_available_balance = $available_balance - $user_entered_usd;
        $stmt->execute();
        $stmt->close();

        // close connection to database
        $conn->close();

        // return successfully response back to client
        echo json_encode([
            'success' => true,
            'available_balance' => $new_available_balance
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

    } else if (!preg_match("/^[a-zA-Z0-9]+$/", $_POST['walletaddress'])) {
        return false;
    }

    return true;
}

?>