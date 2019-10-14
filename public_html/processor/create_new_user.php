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
    die(); // exit the script
}

// check if we are the one that serve the page
if (hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
    // check if is not a robot
    if (empty($_POST['leaveitempty']) && sanitiseInput($_POST['acceptterms']) == 1) {
        // check if submitted form's input is correct
        if (!validateUserFormInputs($_POST)) {
            die(); // stop script
        }

        $db = $config['db']['mysql']; // mysql configuration
        
        // enable mysql exception
        mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

        // connect to database
        $conn = new mysqli($db['host'], $db['username'], $db['password'], $db['dbname']);

        //check connection
        if ($conn->connect_error) {
            trigger_error('Database connection failed: '.$conn->connect_error, E_USER_ERROR);
        }

        try {
            // generate referral ID for the user
            $user_ref_id = randomText('distinct', 24);

            // generate hash of 40 characters length from user's email address
            $search_email_hash = hash('sha1', $_POST['email']);

            // start transaction
            $conn->begin_transaction();

            // create new user
            $query = 
            'INSERT INTO users (
                referralID, 
                firstName, 
                lastName, 
                userName,
                email,
                searchEmailHash,
                country,
                phoneCountryCode,
                phoneNumber,
                gender
            ) VALUES(?, ?, ?, ?, ?, ?, ?, ?, ?, ?)';

            $stmt = $conn->prepare($query); // prepare statement
            
            $stmt->bind_param(
                'sssssssiis', 
                $user_ref_id,
                sanitiseInput($_POST['firstname']),
                sanitiseInput($_POST['lastname']),
                sanitiseInput($_POST['username']),
                sanitiseInput($_POST['email']),
                $search_email_hash,
                sanitiseInput($_POST['country']),
                sanitiseInput($_POST['phoneCountryCode']),
                sanitiseInput($_POST['phoneNumber']),
                sanitiseInput($_POST['gender'])
            );
    
            $stmt->execute();
            $new_user_id = $stmt->insert_id;
            $stmt->close();

            // insert user's login credential into table
            $password_hash = password_hash($_POST['password'], PASSWORD_DEFAULT);
            $query = 'INSERT INTO user_authentication (userID, username, password) VALUES(?, ?, ?)';
            $stmt = $conn->prepare($query); // prepare statement
            $stmt->bind_param('iss', $new_user_id, $_POST['userrname'], $password_hash);
            $stmt->execute();

            // commit all the transaction
            $conn->commit();

            // close connection to database
            $stmt->close();
            $conn->close();

            // create login session and redirect user to their page
            $_SESSION['auth'] = true;
            header('Location: ' . BASE_URL . 'user/home.php');
            exit;

        } catch (mysqli_sql_exception $e) { // catch only mysqli exceptions
            // $e->getMessage(); 
            // $e->getCode();
            $conn->rollback(); // remove all queries from queue if error occured (undo)

        } catch (Exception $e) { // catch other exception
            echo 'Caught exception: ',  $e->getMessage(), "\n";

        } finally {
            // close connection to database
            $stmt->close();
            $conn->close();
        }
    }
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

            case 'country':
            case 'password':
            case 'confirmpasswd': 
                if (!preg_match("/^.+$/", $input_value)) {
                    return false;
                }

                break;

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

            case 'birthdate': {
                if (!preg_match("/^(0[1-9]|1[0-2])\/(0[1-9]|[1-2][0-9]|3[0-1])\/[1-9]\d{3}$/", $input_value)) {
                    return false;
                }
            }

            case 'gender':
                if (!preg_match("/^(male|female|others)$/i", $input_value)) {
                    return false;
                }

                break;

            case 'username':
                if (!preg_match("/^([a-zA-Z0-9]+|[a-zA-Z0-9]+@?[a-zA-Z0-9]+)$/", $input_value)) {
                    return false;
                }

                break;

            case 'referralid':
                if (!preg_match("/^[a-zA-Z0-9]+$/", $input_value)) {
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