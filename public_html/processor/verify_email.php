<?php

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

// check if request method is GET
if ($_SERVER['REQUEST_METHOD'] != 'GET') {
    die(); // terminate the script
}

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

    // decrypt the verification token
    $decrypted_token = opensslDecrypt(base64_decode($_GET['token']), OPENSSL_ENCR_KEY);

    if ($decrypted_token == null) {
        die(); // stop the script
    }

    list($user_id, $verification_token) = explode(':', $decrypted_token);

    // validate the token
    $query = 'SELECT 1 FROM verify_user_email WHERE userID = ? AND token = ? LIMIT 1';
    $stmt = $conn->prepare($query); // prepare statement
    $stmt->bind_param('is', $user_id, $verification_token);
    $stmt->execute();
    $stmt->store_result(); // needed for num_rows

    // check if token exist
    if ($stmt->num_rows > 0) {
        $stmt->close(); // close previous prepared statement

        try {
            $conn->begin_transaction(); // start transaction

            // delete user's all verification token
            $query = 'DELETE FROM verify_user_email WHERE userID = ?';
            $stmt = $conn->prepare($query); // prepare statement
            $stmt->bind_param('i', $user_id);
            $stmt->execute();
            $stmt->close();

            // mark email as verified
            $query = 'UPDATE user_account_verification SET email = ? WHERE userID = ? LIMIT 1';
            $stmt = $conn->prepare($query); // prepare statement
            $stmt->bind_param('ii', $mail_verified, $user_id);
            $mail_verified = 1;
            $stmt->execute();
            $stmt->close();

            $conn->commit(); // commit all the transaction
            $conn->close(); // close connection to database

            // redirect user to there dashboard
            $_SESSION['auth'] = true;
            $_SESSION['user_id'] = $user_id;
            $_SESSION['last_auth_time'] = time() + 1800; // expire in 30 minutes
            header('Location: ' . BASE_URL . 'user/home/my_investment.html');
            exit;

        } catch (Exception $e) {
            $conn->rollback(); // remove all queries from queue if error occured (undo)
            $conn->close(); // close connection to database

            // log the error to a file
            error_log('Mysql error: '.$e->getMessage(), 3, CUSTOM_ERR_DIR.'custom_errors.log');

            exit;
        }
    }

    // close connection to database
    $stmt->close();
    $conn->close();

} catch (mysqli_sql_exception $e) {
    // log the error to a file
    error_log('Mysql error: '.$e->getMessage().PHP_EOL, 3, CUSTOM_ERR_DIR.'custom_errors.log');

} catch (Exception $e) { // catch other exception
    // log the error to a file
    error_log('Caught exception: '.$e->getMessage().PHP_EOL, 3, CUSTOM_ERR_DIR.'custom_errors.log');
}

?>