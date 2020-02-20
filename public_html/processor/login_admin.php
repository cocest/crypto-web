<?php 

// start session
session_start();

// import all the necessary liberaries
require_once '../includes/config.php';
require_once '../includes/utils.php'; // include utility liberary

// check if request method is post
if ($_SERVER['REQUEST_METHOD'] != 'POST') {
    die();
}

// check if we are the one that serve the page
if (!hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
    die();
}

date_default_timezone_set('UTC');

$db = $config['db']['mysql']; // mysql configuration
        
// enable mysql exception
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

// connect to database
$conn = new mysqli($db['host'], $db['username'], $db['password'], $db['dbname']);

try {
    // check connection
    if ($conn->connect_error) {
        throw new mysqli_sql_exception('Database connection failed: '.$conn->connect_error);
    }

    $user_ip_address = getUserIpAddress(); // get user's IP address
    $is_login_failed_before = false;

    // check if throttling protection is activated for user's IP address
    $query = 'SELECT attemptCount, blockCount, waitTime FROM admin_suspend_login WHERE ipAddress = ? LIMIT 1';
    $stmt = $conn->prepare($query); // prepare statement
    $stmt->bind_param('s', $user_ip_address);
    $stmt->execute();
    $stmt->store_result(); // needed for num_rows

    if ($stmt->num_rows > 0) {
        $is_login_failed_before = true;
        
        $stmt->bind_result($attempt_count, $block_count, $wait_time);
        $stmt->fetch();

        $current_time = time();

        // check if wait time haven't elapse
        if ($current_time < $wait_time) {
            // close connection to database
            $stmt->close();
            $conn->close();

            $duration = $wait_time - $current_time;
            header('Retry-After: ' . $duration, TRUE, '429');
            echo '{"success": false, "rate_limit": true, "retry_after": ' . $duration . '}';
            exit; // exit script
        }
    }

    $stmt->close(); // close previous prepared statement

    // authenticate admistrator
    $query = 'SELECT userID, password FROM admin_authentication WHERE username = ? LIMIT 1';
    $stmt = $conn->prepare($query); // prepare statement
    $stmt->bind_param('s', $_POST['username']);
    $stmt->execute();
    $stmt->store_result(); // needed for num_rows

    if ($stmt->num_rows > 0) { // user exist
        // validate user password
        $stmt->bind_result($user_id, $password_hash);
        $stmt->fetch();
        $stmt->close();

        // match password
        if (password_verify($_POST['password'], $password_hash)) {
            if ($is_login_failed_before) {
                $query = 'DELETE FROM admin_suspend_login WHERE ipAddress = ? LIMIT 1';
                $stmt = $conn->prepare($query); // prepare statement
                $stmt->bind_param('s', $user_ip_address);
                $stmt->execute();
                $stmt->close();
            }

            // close connection to database
            $conn->close();

            // create login session and send redirect url to client
            $_SESSION['admin_auth'] = true;
            $_SESSION['admin_user_id'] = $user_id;
            $_SESSION['admin_last_auth_time'] = time() + 1800; // expire in 30 minutes

            $redirect_url = BASE_URL . 'admin/dashboard.html';
            echo '{"success": true, "redirect_url": "' . $redirect_url . '"}';

            exit; // exit script
        }
    }

    // insert login failure
    if (!$is_login_failed_before) {
        $query = 'INSERT INTO admin_suspend_login (ipAddress, attemptCount) VALUES (?, ?)';
        $stmt = $conn->prepare($query); // prepare statement
        $stmt->bind_param('si', $user_ip_address, $counter);
        $counter = 1;
        $stmt->execute();
        $stmt->close();

        // close connection to database
        $conn->close();

        // user login failed
        echo '{"success": false, "rate_limit": false}';
        exit; // exit script
    }

    // count the number of login failure. If is over four attempt, kick in login throttling protection
    if ($attempt_count > 3) {
        $set_wait_time = pow(60 * 5, $block_count + 1); // base number is 5 minutes

        // increment block counter and set attempt counter to default value
        $query = 'UPDATE admin_suspend_login SET attemptCount = 0, blockCount = blockCount + 1, waitTime = ? WHERE ipAddress = ? LIMIT 1';
        $stmt = $conn->prepare($query); // prepare statement
        $stmt->bind_param('is', $set_wait_time, $user_ip_address);
        $stmt->execute();
        $stmt->close();

        // close connection to database
        $conn->close();

        // send response back to client
        header('Retry-After: ' . $set_wait_time, TRUE, '429');
        echo '{"success": false, "rate_limit": true, "retry_after": ' . $set_wait_time . '}';
        exit; // exit script

    } else {
        // increment attempt counter
        $query = 'UPDATE admin_suspend_login SET attemptCount = attemptCount + 1 WHERE ipAddress = ? LIMIT 1';
        $stmt = $conn->prepare($query); // prepare statement
        $stmt->bind_param('s', $user_ip_address);
        $stmt->execute();
        $stmt->close();

        // close connection to database
        $conn->close();

        // user login failed
        echo '{"success": false, "rate_limit": false}';
        exit; // exit script
    }

} catch (mysqli_sql_exception $e) { // catch only mysqli exceptions
    // $e->getMessage(); 
    // $e->getCode();
    // log the error to a file
    error_log('Mysql error: '.$e->getMessage().PHP_EOL, 3, CUSTOM_ERR_DIR.'custom_errors.log');

} catch (Exception $e) { // catch other exception
    // log the error to a file
    error_log('Caught exception: '.$e->getMessage().PHP_EOL, 3, CUSTOM_ERR_DIR.'custom_errors.log');
}

?>