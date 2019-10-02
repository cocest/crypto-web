<?php 

// error handler function
function customError($errno, $errstr) {
    echo "<b>Error:</b> [$errno] $errstr<br>";
    die();
}

// import all the necessary liberaries
require_once '../includes/config.php';
require_once '../includes/utils.php'; // include utility liberary

// check if request method is post
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // check if URL has request query
    if (isset($_POST['req']) && isset($_POST['d'])) {
        $db = $config['db']['mysql']; // mysql configuration

        // connect to database
        $conn = new mysqli($db['host'], $db['username'], $db['password'], $db['dbname']);

        //check connection
        if ($conn->connect_error) {
            trigger_error('Database connection failed: '.$conn->connect_error, E_USER_ERROR);
        }

        // process user request
        switch($_POST['req']) {
            case 'emailexist':
                // generate hash of 40 characters length from user's email address
                $search_email_hash = hash('sha1', sanitiseInput($_POST['d']));

                // check if email exist
                $query = 'SELECT 1 FROM users WHERE searchEmailHash = ? LIMIT 1';
                $stmt = $conn->prepare($query); // prepare statement

                // check if statement compile
                if($stmt === false) {
                    trigger_error('Wrong SQL: '.$query.' Error: '.$conn->error, E_USER_ERROR);
                }

                $stmt->bind_param('s', $search_email_hash);
                $stmt->execute();

                if ($stmt->num_rows > 0) { // email exist
                    // send result to client
                    echo '{"email_exist": true}'; // JSON

                } else { // email doesn't exist
                    // send result to client
                    echo '{"email_exist": false}'; // JSON
                }

                // close connection to database
                $stmt->close();
                $conn->close();

                break;

            case 'usernameexist':
                // sanitise pass in user name
                $user_name = sanitiseInput($_POST['d']);

                // check if user name exist
                $query = 'SELECT 1 FROM users WHERE userName = ? LIMIT 1';
                $stmt = $conn->prepare($query); // prepare statement

                // check if statement compile
                if($stmt === false) {
                    trigger_error('Wrong SQL: '.$query.' Error: '.$conn->error, E_USER_ERROR);
                }

                $stmt->bind_param('s', $user_name);
                $stmt->execute();

                if ($stmt->num_rows > 0) { // username exist
                    // send result to client
                    echo '{"username_exist": true}'; // JSON

                } else { // username doesn't exist
                    // send result to client
                    echo '{"username_exist": false}'; // JSON
                }

                // close connection to database
                $stmt->close();
                $conn->close();

                break;

            case 'referralexist':
                // sanitise pass in referral ID
                $ref_id = sanitiseInput($_POST['d']);

                // check if referral ID exist
                $query = 'SELECT 1 FROM users WHERE referralID = ? LIMIT 1';
                $stmt = $conn->prepare($query); // prepare statement

                // check if statement compile
                if($stmt === false) {
                    trigger_error('Wrong SQL: '.$query.' Error: '.$conn->error, E_USER_ERROR);
                }

                $stmt->bind_param('s', $ref_id);
                $stmt->execute();

                if ($stmt->num_rows > 0) { // ID exist
                    // send result to client
                    echo '{"referral_exist": true}'; // JSON

                } else { // ID doesn't exist
                    // send result to client
                    echo '{"referral_exist": false}'; // JSON
                }

                // close connection to database
                $stmt->close();
                $conn->close();

                break;

            default:
                // you shouldn't be here
        }

    } else {
        trigger_error('Request URL is not properly form', E_USER_ERROR);
    }
}

?>