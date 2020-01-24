<?php 

// start session
session_start();

// import all the necessary liberaries
require_once '../includes/config.php';
require_once '../includes/utils.php'; // include utility liberary

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

    // check if is to verify user's email address and direct user to change password page
    if ($_SERVER['REQUEST_METHOD'] == 'GET') {
        // decrypt the verification token
        $decrypted_token = opensslDecrypt(base64_decode($_GET['token']), OPENSSL_ENCR_KEY);

        if ($decrypted_token == null) {
            die(); // stop the script
        }

        list($user_id, $verification_token) = explode(':', $decrypted_token);

        // validate the token
        $query = 'SELECT 1 FROM user_reset_password WHERE userID = ? AND token = ? LIMIT 1';
        $stmt = $conn->prepare($query); // prepare statement
        $stmt->bind_param('is', $user_id, $verification_token);
        $stmt->execute();
        $stmt->store_result(); // needed for num_rows

        // check if token exist
        if ($stmt->num_rows > 0) {
            $stmt->close(); // close previous prepared statement

            // delete user's all verification token
            $query = 'DELETE FROM user_reset_password WHERE userID = ?';
            $stmt = $conn->prepare($query); // prepare statement
            $stmt->bind_param('i', $user_id);
            $stmt->execute();

            // redirect user to password reset page
            $_SESSION['reset_password'] = true;
            $_SESSION['reset_pswd_time'] = time() + 900; // expire in 15 minutes
            $_SESSION['user_id'] = $user_id;
            header('Location: ' . BASE_URL . 'user/reset_password.html');
        }

        // close connection to database
        $stmt->close();
        $conn->close();

        exit; // stop the script

    } else if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_SESSION['reset_password'])) {
        // check if we are the one that serve the page
        if (!hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
            die(); // abort the operation
        }

        // reset and change the password to new password
        $password_hash = password_hash($_POST['password'], PASSWORD_DEFAULT); // hash user new password

        // update login authentication table
        $query = 'UPDATE user_authentication SET password = ? WHERE userID = ? LIMIT 1';
        $stmt = $conn->prepare($query); // prepare statement
        $stmt->bind_param('si', $password_hash, $_SESSION['user_id']);
        $stmt->execute();
        $stmt->close();

        // create login session
        $_SESSION['auth'] = true;
        $_SESSION['last_auth_time'] = time() + 1800; // expire in 30 minutes

        // clear session
        unset($_SESSION['reset_password']);
        unset($_SESSION['reset_pswd_time']);

        // close connection to database
        $conn->close();

        // return redirect url to client
        echo json_encode([
            'redirect_url' => BASE_URL . 'user/home/my_investment.html'
        ]);

        exit; // stop the script
    }

    // close connection to database
    $conn->close();

    die();   


} catch (mysqli_sql_exception $e) {
    // log the error to a file
    error_log('Mysql error: '.$e->getMessage().PHP_EOL, 3, CUSTOM_ERR_DIR.'custom_errors.log');

} catch (Exception $e) { // catch other exception
    // log the error to a file
    error_log('Caught exception: '.$e->getMessage().PHP_EOL, 3, CUSTOM_ERR_DIR.'custom_errors.log');
}

?>