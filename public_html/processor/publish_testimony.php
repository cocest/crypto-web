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

// check if request method is post
if ($_SERVER['REQUEST_METHOD'] != 'POST') {
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

// check if testimony is empty
if (isset($_POST['testimony']) && preg_match("/^[ ]*$/", $_POST['testimony'])) {
    echo '{"success":false}';
    exit; // exit script
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

    // sanitise and reformat user's testimony
    $fmt_testimony = sanitiseInput(substr($_POST['testimony'], 0, 1500));

    // insert testimony to the database
    $query = 'INSERT INTO user_testimonies (userID, testimoney, time) VALUES(?, ?, ?)';
    $stmt = $conn->prepare($query); // prepare statement
    $stmt->bind_param('isi', $_SESSION['user_id'], $fmt_testimony, $testimony_time);
    $testimony_time = time();
    $stmt->execute();
    $new_testimony_id = $stmt->insert_id;
    $stmt->close();

    // send response back to client
    echo json_encode(
        [
            'success' => true,
            'testimony_id' => $new_testimony_id,
            'time' => $testimony_time,
            'testimony' => $fmt_testimony
        ]
    );

    // close connection to database
    $conn->close();

} catch (mysqli_sql_exception $e) {
    echo 'Mysql error: ' . $e->getMessage() . PHP_EOL;

} catch (Exception $e) { // catch other exception
    echo 'Caught exception: ' .  $e->getMessage() . PHP_EOL;
}

?>