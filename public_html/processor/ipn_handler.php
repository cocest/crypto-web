<?php 

// import all the necessary liberaries
require_once '../includes/config.php';
require_once '../includes/utils.php'; // include utility liberary
require_once '../includes/Nnochi.php';

// utility function to handle error
function handleErrorAndDie($error_msg) {
    if (!empty(DEBUG_EMAIL)) {
        $report = 'Error: '.$error_msg."\n\n";
        $report .= "POST Data\n\n";

        foreach ($_POST as $k => $v) {
            $report .= "|$k| = |$v|\n";
        }

        mail(DEBUG_EMAIL, 'CoinPayments IPN Error', $report);
    }

    die('IPN Error: '.$error_msg);
}

// check if IPN mode is HMAC and it has a signature sent
if (!isset($_POST['ipn_mode']) || $_POST['ipn_mode'] != 'hmac') {
    handleErrorAndDie('IPN Mode is not HMAC');
}

if (!isset($_SERVER['HTTP_HMAC']) || empty($_SERVER['HTTP_HMAC'])) {
    handleErrorAndDie('No HMAC signature sent.');
}

// reads entire file into a string
$request = file_get_contents('php://input');
if ($request === FALSE || empty($request)) {
    handleErrorAndDie('Error reading POST data');
}

// check if passed merchant ID is valid
if (!isset($_POST['merchant']) || $_POST['merchant'] != CPS_MERCHANT_ID) {
    handleErrorAndDie('No or incorrect Merchant ID passed');
}

$hmac = hash_hmac("sha512", $request, CPS_IPN_SECRET);
if (!hash_equals($hmac, $_SERVER['HTTP_HMAC'])) {
    handleErrorAndDie('HMAC signature does not match');
}

// IPN status for the transaction
$ipn_status = intval($_POST['status']);
$ipn_type = $_POST['ipn_type'];
$ipn_id = $ipn_type == 'deposit' ? $_POST['txn_id'] : $_POST['id'];

date_default_timezone_set('UTC');

// mysql configuration
$db = $config['db']['mysql'];
        
// enable mysql exception
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

try {
    // connect to database
    $conn = new mysqli($db['host'], $db['username'], $db['password'], $db['dbname']);

    //check connection
    if ($conn->connect_error) {
        throw new mysqli_sql_exception('Database connection failed: '.$conn->connect_error);
    }

    $user_id; 
    $crypto_currency;
    $amount_in_usd; 
    $ammount;
    $transaction_committed;

    // fetch user's transaction
    $query = 'SELECT userID, currency, ammount, amountInUSD, committed FROM user_transactions WHERE transactionID = ?  LIMIT 1';
    $stmt = $conn->prepare($query); // prepare statement
    $stmt->bind_param('s', $ipn_id);
    $stmt->execute();
    $stmt->store_result(); // needed for num_rows

    // check if transaction exist
    if ($stmt->num_rows > 0) {
        $stmt->bind_result($user_id, $crypto_currency, $ammount, $amount_in_usd, $transaction_committed);
        $stmt->fetch();

    } else {
        // close connection
         $stmt->close();
         $conn->close();

         die('IPN OK');
    }

    $stmt->close();

    // check if the currency and price of the package is altered
    if (!($_POST['currency'] == $crypto_currency && $_POST['currency'] == $ammount)) {
        handleErrorAndDie('Currency changed or amount altered.');
    }
    
    /* 
     * for deposit:  0 = pending and 100 = confirmed/complete.
     * for withrawal: 0 = waiting email confirmation, 1 = pending, and 2 = sent/complete
     */ 
    
    if ($ipn_status >= 100 || $ipn_status == 2) { // check if transaction was successfully
        $query = "SELECT availableBalance FROM user_account WHERE userID = ? LIMIT 1";
        $stmt = $conn->prepare($query); // prepare statement
        $stmt->bind_param('i', $user_id);
        $stmt->execute();
        $stmt->bind_result($available_balance);
        $stmt->fetch();
        $stmt->close();

        // check if transaction haven't be handled before
        if ($transaction_committed == 0) {
            $nnochi = new Nnochi(); // template parser

            // check if is deposit or withdrawal
            if ($ipn_type == 'deposit') {
                $move_invested_amount = false; // move user's invested amount to available balance
                $update_current_investment = false;
                $amount_invested;
                $new_available_balance;

                $query = 'SELECT amountInvested, withdrawInvestment FROM user_current_investment WHERE userID = ? LIMIT 1';
                $stmt = $conn->prepare($query); // prepare statement
                $stmt->bind_param('i', $user_id);
                $stmt->execute();
                $stmt->store_result(); // needed for num_rows

                if ($stmt->num_rows > 0) {
                    $stmt->bind_result($amount_invested, $withdraw_investment);
                    $stmt->fetch();
                    $stmt->close();

                    if ($withdraw_investment == 0) {
                        $move_invested_amount = true;
                    }

                    $update_current_investment = true;
                }

                // get "user_pending_investment"
                $query = 
                    'SELECT A.packageID, B.package, B.durationInMonth, B.withdrawInvestment FROM user_pending_investment AS A 
                     LEFT JOIN crypto_investment_packages AS B ON A.packageID = B.id WHERE A.userID = ? LIMIT 1';
                $stmt = $conn->prepare($query); // prepare statement
                $stmt->bind_param('i', $user_id);
                $stmt->execute();
                $stmt->bind_result($package_id, $package_name, $duration_in_month, $package_withdraw_investment);
                $stmt->fetch();
                $stmt->close();

                // check to move user's previous invested amount to available balance if there is any
                if ($move_invested_amount) {
                    if ($package_withdraw_investment == 1) {
                        $new_available_balance = $available_balance + $amount_invested + $amount_in_usd;
                    } else {
                        $new_available_balance = $available_balance + $amount_invested;
                    }

                } else {
                    if ($package_withdraw_investment == 1) {
                        $new_available_balance = $available_balance + $amount_in_usd;
                    } else {
                        $new_available_balance = $available_balance;
                    }
                }

                try {
                    $conn->begin_transaction(); // start transaction

                    // update user's transaction records
                    $query = 'UPDATE user_transactions SET address = ?, committed = ? WHERE transactionID = ? LIMIT 1';
                    $stmt = $conn->prepare($query); // prepare statement
                    $stmt->bind_param('sis', $_POST['address'], $committed, $ipn_id);
                    $committed = 1;
                    $stmt->execute();
                    $stmt->close();

                    // update user's account
                    $query = 'UPDATE user_account SET availableBalance = ? WHERE userID = ? LIMIT 1';
                    $stmt = $conn->prepare($query); // prepare statement
                    $stmt->bind_param('di', $new_available_balance, $user_id);
                    $stmt->execute();
                    $stmt->close();

                    // update or create new user's investment
                    $start_time = time();
                    $end_time = $start_time + ($duration_in_month * 2592000); // current time + (number of month * seconds in 30 days)

                    if ($update_current_investment) {
                        $query = 
                            'UPDATE user_current_investment SET packageID = ?, amountInvested = ?, withdrawInvestment = ?, 
                             startTime = ?, endTime = ? WHERE userID = ? LIMIT 1';
                        $stmt = $conn->prepare($query); // prepare statement
                        $stmt->bind_param('idiiii', $package_id, $amount_in_usd, $package_withdraw_investment, $start_time, $end_time, $user_id);
                        $stmt->execute();
                        $stmt->close();

                    } else {
                        $query = 
                            'INSERT INTO user_current_investment (userID, packageID, amountInvested, 
                             withdrawInvestment, startTime, endTime) VALUES(?, ?, ?, ?, ?, ?)';
                        $stmt = $conn->prepare($query); // prepare statement
                        $stmt->bind_param('iidiii', $user_id, $package_id, $amount_in_usd, $package_withdraw_investment, $start_time, $end_time);
                        $stmt->execute();
                        $stmt->close();
                    }

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
                    $msg_title = 'Package';
                    $msg_content = 'Your investment on '.$package_name.' package was successfull';
                    $msg_time = time();
                    $stmt->execute();
                    $stmt->close();

                    // delete user's pending investment
                    $query = 'DELETE FROM user_pending_investment WHERE userID = ? LIMIT 1';
                    $stmt = $conn->prepare($query); // prepare statement
                    $stmt->bind_param('i', $user_id);
                    $stmt->execute();
                    $stmt->close();

                    $conn->commit(); // commit all the transaction

                    // close connection to database
                    $conn->close();

                } catch (Exception $e) {
                    $conn->rollback(); // remove all queries from queue if error occured (undo)
                    handleErrorAndDie("Transaction information can't be updated");
                }

            } else { // withdrawal
                try {
                    $conn->begin_transaction(); // start transaction

                    // update user's transaction records
                    $query = 'UPDATE user_transactions SET address = ?, committed = ? WHERE transactionID = ? LIMIT 1';
                    $stmt = $conn->prepare($query); // prepare statement
                    $stmt->bind_param('sis', $_POST['address'], $committed, $ipn_id);
                    $committed = 1;
                    $stmt->execute();
                    $stmt->close();

                    // update user's account
                    $query = 'UPDATE user_account SET totalBalance = totalBalance - ? WHERE userID = ? LIMIT 1';
                    $stmt = $conn->prepare($query); // prepare statement
                    $stmt->bind_param('di', $amount_in_usd, $user_id);
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
                            'amount' => $_POST['amount'],
                            'currency' => $_POST['currency'],
                            'amount_in_usd' => number_format($amount_in_usd, 2).' USD',
                            'address' => $_POST['address'],
                            'balance' => number_format($available_balance, 2).' USD',
                            'txn_id' => $ipn_id
                        ]
                    );
                    $msg_time = time();
                    $stmt->execute();
                    $stmt->close();

                    $conn->commit(); // commit all the transaction

                    // close connection to database
                    $conn->close();

                } catch (Exception $e) {
                    $conn->rollback(); // remove all queries from queue if error occured (undo)
                    handleErrorAndDie("Transaction information can't be updated");
                }
            }
        }

    } else if ($ipn_status >= 0) { // waiting/pending
        // leave it empty for now

    } else { // transaction failed
        $nnochi = new Nnochi(); // template parser

        try {
            $conn->begin_transaction(); // start transaction

            // delete the transaction
            $query = 'DELETE FROM user_transactions WHERE transactionID = ? LIMIT 1';
            $stmt = $conn->prepare($query); // prepare statement
            $stmt->bind_param('s', $ipn_id);
            $stmt->execute();
            $stmt->close();

            $msg_content; // response message

            // check if is deposit or withdrawal
            if ($ipn_type == 'deposit') {
                // delete user's pending investment
                $query = 'DELETE FROM user_pending_investment WHERE userID = ? LIMIT 1';
                $stmt = $conn->prepare($query); // prepare statement
                $stmt->bind_param('i', $user_id);
                $stmt->execute();
                $stmt->close();

                // roll back user's account
                $query = 'UPDATE user_account SET totalBalance = totalBalance - ? WHERE userID = ? LIMIT 1';

                // prepare message
                $msg_content = $nnochi->render(
                    '../templates/deposit_failed_msg.txt',
                    [
                        'amount' => $_POST['amount'],
                        'currency' => $_POST['currency'],
                        'amount_in_usd' => number_format($amount_in_usd, 2).' USD',
                        'address' => $_POST['address']
                    ]
                );

            } else { // withdrawal
                // roll back user's account
                $query = 'UPDATE user_account SET availableBalance = availableBalance + ? WHERE userID = ? LIMIT 1';

                // prepare message
                $msg_content = $nnochi->render(
                    '../templates/withdrawal_failed_msg.txt',
                    [
                        'amount' => $_POST['amount'],
                        'currency' => $_POST['currency'],
                        'amount_in_usd' => number_format($amount_in_usd, 2).' USD',
                        'address' => $_POST['address']
                    ]
                );
            }

            $stmt = $conn->prepare($query); // prepare statement
            $stmt->bind_param('di', $amount_in_usd, $user_id);
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
            $msg_time = time();
            $stmt->execute();
            $stmt->close();

            $conn->commit(); // commit all the transaction

            // close connection to database
            $conn->close();

        } catch (Exception $e) {
            $conn->rollback(); // remove all queries from queue if error occured (undo)
            handleErrorAndDie("Transaction information can't be updated");
        }
    }

} catch (mysqli_sql_exception $e) {
    handleErrorAndDie('Mysql error: '.$e->getMessage());

} catch (Exception $e) { // catch other exception
    handleErrorAndDie('Caught exception: '.$e->getMessage());
}

die('IPN OK'); // do not remove

?>