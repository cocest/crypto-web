<?php 

// import all the necessary liberaries
require_once '../includes/config.php';
require_once '../includes/utils.php'; // include utility liberary
require_once '../includes/Nnochi.php';

// notification and pass back variables
$transaction_id = $_GET['txn_id'];
$bitcoin_address = $_GET['address'];
$pass_secret = $_GET['secret'];
$transaction_hash = $_GET['transaction_hash'];
$confirmations = $_GET['confirmations'];
$value_in_satoshi = $_GET['value'];
$user_payment_in_btc = $value_in_satoshi / 100000000;

// authenticate the request
if (!hash_equals(BC_CALLBACK_SECRET, $pass_secret)) {
    die('Unauthorised caller.');
}

date_default_timezone_set('UTC');

// mysql configuration
$db = $config['db']['mysql'];
        
// enable mysql exception
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

try {

    // check confirmation count
    if ($confirmations == 0) { // transaction is been processed
        // connect to database
        $conn = new mysqli($db['host'], $db['username'], $db['password'], $db['dbname']);

        //check connection
        if ($conn->connect_error) {
            throw new mysqli_sql_exception('Database connection failed: '.$conn->connect_error);
        }

        // fetch user's transaction
        $query = 
            'SELECT userID, packageID, currency, amount, amountInUSD, processed, time 
             FROM verify_user_deposit WHERE transactionID = ?  LIMIT 1';
        $stmt = $conn->prepare($query); // prepare statement
        $stmt->bind_param('s', $transaction_id);
        $stmt->execute();
        $stmt->store_result(); // needed for num_rows

        // check if transaction exist
        if ($stmt->num_rows > 0) {
            $stmt->bind_result(
                $user_id, 
                $package_id, 
                $crypto_currency,
                $amount_in_btc, 
                $amount_in_usd,
                $deposit_processed,
                $deposit_time
            );
            $stmt->fetch();

        } else {
            // close connection
            $stmt->close();
            $conn->close();

            die('*ok*');
        }

        $stmt->close();

        // check if this deposit has been processed before
        if ($deposit_processed == 1) {
            die(); // abort the process
        }

        // acknowledge the transaction
        $nnochi = new Nnochi(); // template parser

        // get package information
        $query = 'SELECT package FROM crypto_investment_packages WHERE id = ? LIMIT 1';
        $stmt = $conn->prepare($query); // prepare statement
        $stmt->bind_param('i', $package_id);
        $stmt->execute();
        $stmt->bind_result($package_name);
        $stmt->fetch();
        $stmt->close();

        $is_refund_payment = false;

        // check for user under payment and dual payment
        if ($user_payment_in_btc < $amount_in_btc) {
            $is_refund_payment = true;

            $msg_id = randomText('hexdec', 32);
            $msg_title = 'Transaction';
            $msg_content = $nnochi->render(
                '../templates/under_payment_msg.txt',
                [
                    'package_name' => $package_name,
                    'amount' => round($amount_in_usd, 2, PHP_ROUND_HALF_DOWN),
                    'transaction_id' => $transaction_hash
                ]
            );
            $msg_time = time();

        } else if (checkForDualPayment($conn, $user_id)) {
            $is_refund_payment = true;

            $msg_id = randomText('hexdec', 32);
            $msg_title = 'Transaction';
            $msg_content = $nnochi->render(
                '../templates/dual_payment_msg.txt',
                [
                    'package_name' => $package_name,
                    'amount' => round($amount_in_usd, 2, PHP_ROUND_HALF_DOWN),
                    'transaction_id' => $transaction_hash
                ]
            );
            $msg_time = time();

        } else {
            $msg_id = randomText('hexdec', 32);
            $msg_title = 'Transaction';
            $msg_content = $nnochi->render(
                '../templates/payment_ack_msg.txt',
                [
                    'package_name' => $package_name,
                    'amount' => round($amount_in_usd, 2, PHP_ROUND_HALF_DOWN),
                    'transaction_id' => $transaction_hash
                ]
            );
            $msg_time = time();
        }

        try {
            $conn->begin_transaction(); // start transaction

            // add user's payment to transaction
            $query = 
                'INSERT INTO user_transactions (transactionID, userID, transactionHash, currency, transaction, amount, amountInUSD, refund, committed, time)
                VALUES(?, ?, ?, ?, ?, ?, ?, ?, ?, ?)';

            $stmt = $conn->prepare($query); // prepare statement
            $stmt->bind_param(
                'sisssddiii', 
                $transaction_id, 
                $user_id, 
                $transaction_hash,
                $crypto_currency, 
                $transaction_type, 
                $amount_in_btc,
                $amount_in_usd, 
                $refund_payment,
                $transaction_committed, 
                $deposit_time
            );
            $transaction_type = 'deposit';
            $refund_payment = $is_refund_payment ? 1 : 0;
            $transaction_committed = 0;
            $stmt->execute();
            $stmt->close();

            // update the user's deposit state
            $query = 'UPDATE verify_user_deposit SET processed = ? WHERE transactionID = ? LIMIT 1';
            $stmt = $conn->prepare($query); // prepare statement
            $stmt->bind_param('is', $processed, $transaction_id);
            $processed = 1;
            $stmt->execute();
            $stmt->close();

            // delete the address
            $query = 'DELETE FROM unpaid_address WHERE transactionID = ? LIMIT 1';
            $stmt = $conn->prepare($query); // prepare statement
            $stmt->bind_param('s', $transaction_id);
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
            $stmt->execute();
            $stmt->close();

            $conn->commit(); // commit all the transaction

            // close connection
            $conn->close();

        } catch (Exception $e) {
            $conn->rollback(); // remove all queries from queue if error occured (undo)
            $conn->close(); // close connection to database
        }

    } else if ($confirmations >= 5) { // transaction is successfull
        // connect to database
        $conn = new mysqli($db['host'], $db['username'], $db['password'], $db['dbname']);

        //check connection
        if ($conn->connect_error) {
            throw new mysqli_sql_exception('Database connection failed: '.$conn->connect_error);
        }

        // fetch user's transaction
        $query = 
            'SELECT userID, currency, amountInUSD, refund, committed 
            FROM user_transactions WHERE transactionID = ?  LIMIT 1';
        $stmt = $conn->prepare($query); // prepare statement
        $stmt->bind_param('s', $transaction_id);
        $stmt->execute();
        $stmt->store_result(); // needed for num_rows

        // check if transaction exist
        if ($stmt->num_rows > 0) {
            $stmt->bind_result(
                $user_id, 
                $crypto_currency, 
                $amount_in_usd, 
                $refund_payment, 
                $transaction_committed
            );
            $stmt->fetch();

        } else {
            // close connection
            $stmt->close();
            $conn->close();

            die('*ok*');
        }

        $stmt->close();

        // check if transaction haven't be handled before
        if ($transaction_committed == 0) {
            // get package information
            $query = 
                'SELECT A.packageID, B.package, B.durationInMonth FROM verify_user_deposit AS A 
                LEFT JOIN crypto_investment_packages AS B ON A.packageID = B.id WHERE A.transactionID  = ? LIMIT 1';
            $stmt = $conn->prepare($query); // prepare statement
            $stmt->bind_param('s', $transaction_id);
            $stmt->execute();
            $stmt->bind_result($package_id, $package_name, $duration_in_month);
            $stmt->fetch();
            $stmt->close();

            // template parser
            $nnochi = new Nnochi();

            // check if payment should be refunded
            if ($refund_payment == 1) {
                try {
                    $conn->begin_transaction(); // start transaction

                    // update user's transaction records
                    $query = 'UPDATE user_transactions SET address = ?, committed = ? WHERE transactionID = ? LIMIT 1';
                    $stmt = $conn->prepare($query); // prepare statement
                    $stmt->bind_param('sis', $bitcoin_address, $committed, $transaction_id);
                    $committed = 1;
                    $stmt->execute();
                    $stmt->close();

                    // delete user's deposit
                    $query = 'DELETE FROM verify_user_deposit WHERE transactionID = ? LIMIT 1';
                    $stmt = $conn->prepare($query); // prepare statement
                    $stmt->bind_param('s', $transaction_id);
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
                        '../templates/refund_payment_msg.txt',
                        [
                            'package_name' => $package_name,
                            'transaction_id' => $transaction_hash
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
                    $conn->close(); // close connection to database
                }

                die('*ok*');

            } else {
                $update_current_investment = false;

                $query = 'SELECT 1 FROM user_current_investment WHERE userID = ? LIMIT 1';
                $stmt = $conn->prepare($query); // prepare statement
                $stmt->bind_param('i', $user_id);
                $stmt->execute();
                $stmt->store_result(); // needed for num_rows

                if ($stmt->num_rows > 0) {
                    $update_current_investment = true;
                }

                $stmt->close();

                try {
                    $conn->begin_transaction(); // start transaction

                    // update user's transaction records
                    $query = 'UPDATE user_transactions SET address = ?, committed = ? WHERE transactionID = ? LIMIT 1';
                    $stmt = $conn->prepare($query); // prepare statement
                    $stmt->bind_param('sis', $bitcoin_address, $committed, $transaction_id);
                    $committed = 1;
                    $stmt->execute();
                    $stmt->close();

                    // update user's account table
                    $query = 'UPDATE user_account SET totalBalance = totalBalance + ? WHERE userID = ? LIMIT 1';
                    $stmt = $conn->prepare($query); // prepare statement
                    $stmt->bind_param('di', $amount_in_usd, $user_id);
                    $stmt->execute();
                    $stmt->close();

                    // update or create new user's investment
                    $start_time = time();
                    $end_time = $start_time + ($duration_in_month * 2592000); // current time + (number of month * seconds in 30 days)

                    if ($update_current_investment) {
                        $query = 
                            'UPDATE user_current_investment SET packageID = ?, amountInvested = ?, profitCollected = ? 
                             startTime = ?, endTime = ? WHERE userID = ? LIMIT 1';
                        $stmt = $conn->prepare($query); // prepare statement
                        $stmt->bind_param('idiiii', $package_id, $amount_in_usd, $profit_collected, $start_time, $end_time, $user_id);
                        $profit_collected = 0;
                        $stmt->execute();
                        $stmt->close();

                    } else {
                        $query = 
                            'INSERT INTO user_current_investment (userID, packageID, amountInvested, startTime, endTime) VALUES(?, ?, ?, ?, ?)';
                        $stmt = $conn->prepare($query); // prepare statement
                        $stmt->bind_param('iidii', $user_id, $package_id, $amount_in_usd, $start_time, $end_time);
                        $stmt->execute();
                        $stmt->close();
                    }

                    // delete user's deposit
                    $query = 'DELETE FROM verify_user_deposit WHERE transactionID = ? LIMIT 1';
                    $stmt = $conn->prepare($query); // prepare statement
                    $stmt->bind_param('s', $transaction_id);
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
                    $msg_title = 'Package';
                    $msg_content = 'Your investment on '.$package_name.' package was successfull';
                    $msg_time = time();
                    $stmt->execute();
                    $stmt->close();

                    $conn->commit(); // commit all the transaction

                    // close connection to database
                    $conn->close();

                } catch (Exception $e) {
                    $conn->rollback(); // remove all queries from queue if error occured (undo)
                    $conn->close(); // close connection to database
                }

                die('*ok*');
            }

        } else {
            // transaction successfull
            die('*ok*');
        }
    }

} catch (mysqli_sql_exception $e) {
    handleErrorAndDie('Mysql error: '.$e->getMessage().PHP_EOL);

}catch (Exception $e) {
    handleErrorAndDie('Caught exception: '.$e->getMessage().PHP_EOL);
}

// check for dual payment
function checkForDualPayment($db_conn, $user_id) {
    $user_has_active_investment = false;

    // check if user has an active transanction
    $query = 'SELECT 1 FROM user_transactions WHERE userID = ? AND committed = 0 LIMIT 1';
    $stmt = $db_conn->prepare($query); // prepare statement
    $stmt->bind_param('i', $user_id);
    $stmt->execute();
    $stmt->store_result(); // needed for num_rows

    if ($stmt->num_rows > 0) {
        $user_has_active_investment = true;
    }

    $stmt->close();

    if ($user_has_active_investment) {
        return true;
    }

    // check if user has an active investment
    $query = 'SELECT endTime FROM user_current_investment WHERE userID = ? LIMIT 1';
    $stmt = $db_conn->prepare($query); // prepare statement
    $stmt->bind_param('i', $user_id);
    $stmt->execute();
    $stmt->store_result(); // needed for num_rows

    if ($stmt->num_rows > 0) {
        $stmt->bind_result($investment_end_time);
        $stmt->fetch();

        if (time() < $investment_end_time) {
            $user_has_active_investment = true;
        }
    }

    $stmt->close();

    // return a boolean value
    return $user_has_active_investment;
}

// utility function to handle error
function handleErrorAndDie($error_msg) {
    error_log($error_msg, 3, CUSTOM_ERR_DIR.'custom_errors.log');
    die('*ok*');
}

?>