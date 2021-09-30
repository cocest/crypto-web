<?php 

// import all the necessary liberaries
require_once '../includes/config.php';
require_once '../includes/utils.php'; // include utility liberary
require_once '../includes/Nnochi.php';

// check if HTTP_X_WEBHOOK_AUTH exist and set
if (!isset($_SERVER['HTTP_X_WEBHOOK_AUTH']) || empty($_SERVER['HTTP_X_WEBHOOK_AUTH'])) {
    header('Forbidden', true, 403);
    die();
}

$auth_header = $_SERVER['HTTP_X_WEBHOOK_AUTH'];
$payload = file_get_contents('php://input');
$auth_hash_header = hash('sha256', CQ_API_SECRET . $payload);

if (!hash_equals($auth_header, $auth_hash_header)) {
    header('Forbidden', true, 403);
    die();
}

$parsed_payload = json_decode($payload, true);
$event_type = $parsed_payload['eventType'];
$data = $parsed_payload['data'];

date_default_timezone_set('UTC');

// mysql configuration
$db = $config['db']['mysql'];
        
// enable mysql exception
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

switch($event_type) {
    case 'CHECKOUT_COMPLETED':
        try {
            // connect to database
            $conn = new mysqli($db['host'], $db['username'], $db['password'], $db['dbname']);

            //check connection
            if ($conn->connect_error) {
                throw new mysqli_sql_exception('Database connection failed: '.$conn->connect_error);
            }

            $transaction_committed = 0;

            // fetch user's transaction
            $query = 'SELECT committed FROM user_transactions WHERE transactionID = ? LIMIT 1';
            $stmt = $conn->prepare($query); // prepare statement
            $stmt->bind_param('s', $data['checkout']['id']);
            $stmt->execute();
            $stmt->store_result(); // needed for num_rows

            // check if transaction exist
            if ($stmt->num_rows > 0) {
                $stmt->bind_result($transaction_committed);
                $stmt->fetch();
            }

            $stmt->close();

            // check if transaction have been handled before
            if ($transaction_committed == 1) {
                // close connection
                $conn->close();

                // response to the client
                header('OK', true, 200);
                die();
            }

            $user_id = $data['checkout']['payload']['charge']['customerId']; // customer ID
            $crypto_currency = $data['checkout']['sourceBlockchainAssetCode'];
            $amount_in_usd = $data['checkout']['settlementAmountReceived'];
            $amount_in_crypto = $data['checkout']['sourceAmountReceived'];

            // get package information and wallet address
            $query = 
                'SELECT A.packageID, B.package, B.durationInMonth, A.address FROM verify_user_deposit AS A 
                LEFT JOIN crypto_investment_packages AS B ON A.packageID = B.id WHERE A.transactionID  = ? LIMIT 1';
            $stmt = $conn->prepare($query); // prepare statement
            $stmt->bind_param('s', $data['checkout']['id']);
            $stmt->execute();
            $stmt->bind_result($package_id, $package_name, $duration_in_month, $wallet_address);
            $stmt->fetch();
            $stmt->close();

            $nnochi = new Nnochi(); // template parser
            $update_current_investment = false;

            // check if user have subscribe to any investment before
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

                // add user's payment to transaction
                $query = 
                    'INSERT INTO user_transactions (transactionID, userID, transactionHash, currency, transaction, amount, amountInUSD, address, committed, time)
                    VALUES(?, ?, ?, ?, ?, ?, ?, ?, ?, ?)';

                $stmt = $conn->prepare($query); // prepare statement
                $stmt->bind_param(
                    'sisssddsii', 
                    $transaction_id, 
                    $user_id, 
                    $transaction_hash,
                    $crypto_currency, 
                    $transaction_type, 
                    $amount_in_crypto,
                    $amount_in_usd, 
                    $wallet_address, 
                    $transaction_committed, 
                    $deposit_time
                );
                $transaction_id = $data['checkout']['id'];
                $transaction_hash = extractBlockchainTransactionInfo('ORIGIN', "tx");
                $transaction_type = 'deposit';
                $transaction_committed = 1;
                $deposit_time = time();
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

            // response to the client
            header('OK', true, 200);
            die();

        } catch (Exception $e) { // catch other exception
            // log the error to a file
            error_log('Caught exception: '.$e->getMessage().PHP_EOL, 3, CUSTOM_ERR_DIR.'custom_errors.log');

            // send message to the client
            header('Internal Server Error', true, 500);
            die();
        }

        break;

    case 'CHECKOUT_UNDERPAID':
        try {
            // connect to database
            $conn = new mysqli($db['host'], $db['username'], $db['password'], $db['dbname']);

            //check connection
            if ($conn->connect_error) {
                throw new mysqli_sql_exception('Database connection failed: '.$conn->connect_error);
            }

            $transaction_committed = 0;

            // fetch user's transaction
            $query = 'SELECT committed FROM user_transactions WHERE transactionID = ? LIMIT 1';
            $stmt = $conn->prepare($query); // prepare statement
            $stmt->bind_param('s', $data['checkout']['id']);
            $stmt->execute();
            $stmt->store_result(); // needed for num_rows

            // check if transaction exist
            if ($stmt->num_rows > 0) {
                $stmt->bind_result($transaction_committed);
                $stmt->fetch();
            }

            $stmt->close();

            // check if transaction have been handled before
            if ($transaction_committed == 1) {
                // close connection
                $conn->close();

                // response to the client
                header('OK', true, 200);
                die();
            }

            $user_id = $data['checkout']['payload']['charge']['customerId']; // customer ID
            $crypto_currency = $data['checkout']['sourceBlockchainAssetCode'];
            $amount_in_usd = $data['checkout']['settlementAmountReceived'];
            $amount_in_crypto = $data['checkout']['sourceAmountReceived'];

            // get package information and wallet address
            $query = 
                'SELECT A.packageID, B.package, B.durationInMonth, A.address FROM verify_user_deposit AS A 
                LEFT JOIN crypto_investment_packages AS B ON A.packageID = B.id WHERE A.transactionID  = ? LIMIT 1';
            $stmt = $conn->prepare($query); // prepare statement
            $stmt->bind_param('s', $data['checkout']['id']);
            $stmt->execute();
            $stmt->bind_result($package_id, $package_name, $duration_in_month, $wallet_address);
            $stmt->fetch();
            $stmt->close();

            try {
                $conn->begin_transaction(); // start transaction

                // add user's payment to transaction
                $query = 
                    'INSERT INTO user_transactions (transactionID, userID, transactionHash, currency, transaction, amount, amountInUSD, address, refund, committed, time)
                    VALUES(?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)';

                $stmt = $conn->prepare($query); // prepare statement
                $stmt->bind_param(
                    'sisssddsiii', 
                    $transaction_id, 
                    $user_id, 
                    $transaction_hash,
                    $crypto_currency, 
                    $transaction_type, 
                    $amount_in_crypto,
                    $amount_in_usd, 
                    $wallet_address, 
                    $refund, 
                    $transaction_committed, 
                    $deposit_time
                );
                $transaction_id = $data['checkout']['id'];
                $transaction_hash = extractBlockchainTransactionInfo('ORIGIN', "tx");
                $transaction_type = 'deposit';
                $refund = 1;
                $transaction_committed = 1;
                $deposit_time = time();
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

            // response to the client
            header('OK', true, 200);
            die();

        } catch (Exception $e) { // catch other exception
            // log the error to a file
            error_log('Caught exception: '.$e->getMessage().PHP_EOL, 3, CUSTOM_ERR_DIR.'custom_errors.log');

            // send message to the client
            header('Internal Server Error', true, 500);
            die();
        }

        break;

    case 'UNDERPAID_ACCEPTED':
        // code here

        break;

    default:
        // you shouldn't be here
}

// utility function to extract blockchain information
function extractBlockchainTransactionInfo($type, $data) {
    $transactions = $data['checkout']['blockchainTransactions'];

    // iterate through the transactions
    foreach ($transactions as $transaction) {
        if ($transaction['type'] == $type) {
            return $transaction[$data];
        }
    }

    return null;
}

?>