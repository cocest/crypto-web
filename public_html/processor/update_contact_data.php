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

// check if submitted form's input is correct
if (!validateUserFormInputs($_POST)) {
    die(); // exit script
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

    // update user's personal information
    $query = 'UPDATE users SET phoneCountryCode = ?, phoneNumber = ? WHERE id = ? LIMIT 1';
    $stmt = $conn->prepare($query); // prepare statement
    $stmt->bind_param('ssi', $_POST['countrycode'], $_POST['phonenumber'], $_SESSION['user_id']);
    $stmt->execute();

    // close connection to database
    $conn->close();

    echo 'SUCCESS';

} catch (mysqli_sql_exception $e) {
    // log the error to a file
    error_log('Mysql error: '.$e->getMessage().PHP_EOL, 3, CUSTOM_ERR_DIR.'custom_errors.log');
    
} catch (Exception $e) { // catch other exception
    // log the error to a file
    error_log('Caught exception: '.$e->getMessage().PHP_EOL, 3, CUSTOM_ERR_DIR.'custom_errors.log');
}

// utility function to validate form inputs
function validateUserFormInputs($inputs) {
    foreach ($inputs as $input_name => $input_value) {
        switch($input_name) {
            case 'countrycode':
                if (!preg_match("/^\+\d+$/", $input_value)) {
                    return false;
                }

                break;

            case 'phonenumber':
                if (!preg_match("/^\d+$/", $input_value)) {
                    return false;
                }

                break;

            case 'email':
                if (!filter_var($input_value, FILTER_VALIDATE_EMAIL)) {
                    return false;
                }

                break;

            default:
                // shouldn't be here
        }
    }

    return true;
}

?>