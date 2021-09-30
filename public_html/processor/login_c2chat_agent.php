<?php 

// start session
session_start();

// import all the necessary liberaries
require_once '../includes/config.php';
require_once '../includes/utils.php'; // include utility liberary

// check if request method is post
if ($_SERVER['REQUEST_METHOD'] != 'POST') {
    header('Method Not Allowed', true, 405);
    die();
}

// check if we are the one that serve the page
if (!hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
    header('Forbidden', true, 403);
    die();
}

date_default_timezone_set('UTC');

$db = $config['db']['mysql']; // mysql configuration
        
// enable mysql exception
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

try {
    // connect to database
    $conn = new mysqli($db['host'], $db['username'], $db['password'], $db['dbname']);

    // check connection
    if ($conn->connect_error) {
        throw new mysqli_sql_exception('Database connection failed: '.$conn->connect_error);
    }

    // generate hash of 40 characters length from user's email address
    $search_email_hash = hash('sha1', strtolower($_POST['email']));

    // authenticate c2chat agent
    $query = 'SELECT userID, password FROM c2chat_agent_login_auth WHERE emailHash = ? LIMIT 1';
    $stmt = $conn->prepare($query); // prepare statement
    $stmt->bind_param('s', $search_email_hash);
    $stmt->execute();
    $stmt->store_result(); // needed for num_rows

    if ($stmt->num_rows > 0) { // agent exist
        // validate user password
        $stmt->bind_result($user_id, $password_hash);
        $stmt->fetch();

        // match password
        if (password_verify($_POST['password'], $password_hash)) {
            // close connection to database
            $stmt->close();
            $conn->close();

            // create login session and send redirect url to client
            $_SESSION['agent_auth'] = true;
            $_SESSION['agent_user_id'] = $user_id;
            $_SESSION['agent_last_auth_time'] = time() + 1800; // expire in 30 minutes

            $redirect_url = BASE_URL . 'admin/c2chat/dashboard.html';
            echo '{"success": true, "redirect_url": "' . $redirect_url . '"}';

            exit; // exit script
        }
    }

    // close connection to database
    $stmt->close();
    $conn->close();

    // send response back to client
    echo '{"success": false}';

} catch (mysqli_sql_exception $e) { // catch only mysqli exceptions
    // log the error to a file
    error_log('Mysql error: '.$e->getMessage().PHP_EOL, 3, CUSTOM_ERR_DIR.'custom_errors.log');

} catch (Exception $e) { // catch other exception
    // log the error to a file
    error_log('Caught exception: '.$e->getMessage().PHP_EOL, 3, CUSTOM_ERR_DIR.'custom_errors.log');
}

?>