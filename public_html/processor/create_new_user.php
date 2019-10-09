<?php 

// start session
session_start();

// error handler function
function customError($errno, $errstr) {
    echo "<b>Error:</b> [$errno] $errstr<br>";
    die();
}

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
            die(); // exit script
        }

        // generate referral ID for the user
        $user_ref_id = randomText('distinct', 24);

        // generate hash of 40 characters length from user's email address
        $search_email_hash = hash('sha1', sanitiseInput($_POST['email']));

        $db = $config['db']['mysql']; // mysql configuration
        
        // enable mysql exception
        mysqli_report(MYSQLI_REPORT_STRICT | MYSQLI_REPORT_ALL);

        // connect to database
        $conn = new mysqli($db['host'], $db['username'], $db['password'], $db['dbname']);

        //check connection
        if ($conn->connect_error) {
            trigger_error('Database connection failed: '.$conn->connect_error, E_USER_ERROR);
        }

        try {
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
            $stmt->store_result(); // needed for affected row

            // check if user is created successfully
            if ($stmt->affected_rows > 0) {
                // redirect user to there home page
                echo 'Your are registered';
            }

        } catch (mysqli_sql_exception $e) { // catch only mysqli exceptions
            // $e->getMessage(); 
            // $e->getCode();
            // handle mysqli exception here

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
                if (!preg_match("/^([a-zA-Z]|[a-zA-Z]+[']?[a-zA-Z]+)$/", sanitiseInput($input_value))) {
                    return false;
                }

                break;

            case 'country': 
                if (!preg_match("/^.+$/", sanitiseInput($input_value))) {
                    return false;
                }

                break;

            case 'countrycode':
                if (!preg_match("/^\+\d+$/", sanitiseInput($input_value))) {
                    return false;
                }

                break;

            case 'phonenumber':
                if (!preg_match("/^\d+$/", sanitiseInput($input_value))) {
                    return false;
                }

                break;

            case 'email':
                if (!filter_var(sanitiseInput($input_value), FILTER_VALIDATE_EMAIL)) {
                    return false;
                }

                break;

            case 'birthdate': {
                if (!preg_match("/^(0[1-9]|1[0-2])\/(0[1-9]|[1-2][0-9]|3[0-1])\/[1-9]\d{3}$/", sanitiseInput($input_value))) {
                    return false;
                }
            }

            case 'gender':
                if (!preg_match("/^(male|female|others)$/i", sanitiseInput($input_value))) {
                    return false;
                }

                break;

            case 'username':
                if (!preg_match("/^([a-zA-Z0-9]+|[a-zA-Z0-9]+@?[a-zA-Z0-9]+)$/", sanitiseInput($input_value))) {
                    return false;
                }

                break;

            case 'referralid':
                if (!preg_match("/^[a-zA-Z0-9]+$/", sanitiseInput($input_value))) {
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