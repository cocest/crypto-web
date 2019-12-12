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
    $query = 'UPDATE users SET firstName = ?, lastName = ?, birthdate = ?, gender = ? WHERE id = ? LIMIT 1';
    $stmt = $conn->prepare($query); // prepare statement
    $stmt->bind_param('ssssi', $_POST['firstname'], $_POST['lastname'], $birth_date, $_POST['gender'], $_SESSION['user_id']);
    $splitted_birth_date = explode('/', $_POST['birthdate']);
    $birth_date = $splitted_birth_date[2].'-'.$splitted_birth_date[0].'-'.$splitted_birth_date[1];
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
            case 'firstname':
            case 'lastname':
                if (!preg_match("/^([a-zA-Z]|[a-zA-Z]+[']?[a-zA-Z]+)$/", $input_value)) {
                    return false;
                }

                break;

            case 'birthdate': {
                if (!preg_match("/^(0[1-9]|1[0-2])\/(0[1-9]|[1-2][0-9]|3[0-1])\/[1-9]\d{3}$/", $input_value)) {
                    return false;
                }

                break;
            }

            case 'gender':
                if (!preg_match("/^(male|female|others)$/i", $input_value)) {
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