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
require_once '../includes/Nnochi.php';
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
require_once '../includes/PHPMailer/Exception.php';
require_once '../includes/PHPMailer/PHPMailer.php';
require_once '../includes/PHPMailer/SMTP.php';

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
            // send message to the client
            echo json_encode([
                'success' => false,
                'error_msg' => 'Registeration was unsuccessfull.'
            ]);
        
            exit; // stop script
        }

        date_default_timezone_set('UTC');

        // mysql configuration
        $db = $config['db']['mysql'];
        
        // enable mysql exception
        mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

        // connect to database
        $conn = new mysqli($db['host'], $db['username'], $db['password'], $db['dbname']);

        // check connection
        if ($conn->connect_error) {
            trigger_error('Database connection failed: '.$conn->connect_error, E_USER_ERROR);
        }

        try {
            // generate referral ID for the user
            $user_ref_id = randomText('distinct', 16);

            // generate hash of 40 characters length from user's email address
            $search_email_hash = hash('sha1', strtolower($_POST['email']));

            // start transaction
            $conn->begin_transaction();

            // create new user
            $query = 
            'INSERT INTO users (
                referralID, 
                firstName, 
                lastName,
                email,
                searchEmailHash,
                country, 
                birthdate,
                gender
            ) VALUES(?, ?, ?, ?, ?, ?, ?, ?)';

            $stmt = $conn->prepare($query); // prepare statement
            
            $stmt->bind_param(
                'ssssssss', 
                $user_ref_id,
                $first_name,
                $last_name,
                $_POST['email'],
                $search_email_hash,
                $_POST['country'], 
                $birth_date,
                $user_gender
            );
    
            $first_name = ucfirst(strtolower($_POST['firstname']));
            $last_name = ucfirst(strtolower($_POST['lastname']));
            $user_gender = strtolower($_POST['gender']);
            $splitted_birth_date = explode('/', $_POST['birthdate']);
            $birth_date = $splitted_birth_date[2].'-'.$splitted_birth_date[0].'-'.$splitted_birth_date[1];
            $stmt->execute();
            $new_user_id = $stmt->insert_id;
            $stmt->close();

            // create user's account
            $query = 'INSERT INTO user_account (userID) VALUES(?)';
            $stmt = $conn->prepare($query); // prepare statement
            $stmt->bind_param('i', $new_user_id);
            $stmt->execute();
            $stmt->close();

            // investment statistics
            $query = 'INSERT INTO user_investment_statistics (userID) VALUES(?)';
            $stmt = $conn->prepare($query); // prepare statement
            $stmt->bind_param('i', $new_user_id);
            $stmt->execute();
            $stmt->close();

            // insert user's login credential into table
            $password_hash = password_hash($_POST['password'], PASSWORD_DEFAULT);
            $query = 'INSERT INTO user_authentication (userID, userEmailHash, password) VALUES(?, ?, ?)';
            $stmt = $conn->prepare($query); // prepare statement
            $stmt->bind_param('iss', $new_user_id, $search_email_hash, $password_hash);
            $stmt->execute();
            $stmt->close();

            // insert user's account verification
            $query = 'INSERT INTO user_account_verification (userID, time) VALUES(?, ?)';
            $stmt = $conn->prepare($query); // prepare statement
            $stmt->bind_param('ii', $new_user_id, $current_time);
            $current_time = time();
            $stmt->execute();
            $stmt->close();

            // send verification email to the user
            // generate 32 digit unique key plus user ID
            $verification_token = randomText('hexdec', 32);
            $token = $new_user_id . ':' . $verification_token;

            $encrypted_token = opensslEncrypt($token, OPENSSL_ENCR_KEY); // encrypt the token
            $username = $_POST['firstname'];
            $verification_url = BASE_URL . 'verify_email?token=' . urlencode(base64_encode($encrypted_token));

            // add the token to the database for later verification
            $query = 'INSERT INTO verify_user_email (userID, token) VALUES(?, ?)';
            $stmt = $conn->prepare($query); // prepare statement
            $stmt->bind_param('is', $new_user_id, $verification_token);
            $stmt->execute();

            // commit all the transaction
            $conn->commit();

            // close connection to database
            $stmt->close();
            $conn->close();

            // template parser
            $nnochi = new Nnochi();

            // send email to user
            $mail = new PHPMailer;

            // server settings
            $mail->isSMTP();
            $mail->Host = SMTP_HOST;
            $mail->Port = SMTP_PORT;
            $mail->SMTPAuth = true;
            $mail->Username = SMTP_USERNAME;
            $mail->Password = SMTP_PASSWORD;
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS; // Enable TLS encryption
           
            // recipient
            $mail->setFrom(SENDER_EMAIL, SENDER_NAME); // sender mail
            $mail->addAddress($_POST['email']);

            // content
            $mail->isHTML(true);
            $mail->Subject = 'Email Verification';
            $mail->AddEmbeddedImage("../templates/logo.png", "site-logo");
            $mail->AddEmbeddedImage("../templates/fb_logo.png", "fb-logo");
            $mail->AddEmbeddedImage("../templates/tw_logo.png", "tw-logo");
            $mail->Body = $nnochi->render(
                '../templates/email_verification.html',
                [
                    'username' => $username,
                    'verification_url' => $verification_url,
                    'year' => date('Y')
                ]
            );

            if (!$mail->send()) {
                // log the error to a file
                error_log('Mailer Error: '.$mail->ErrorInfo.PHP_EOL, 3, CUSTOM_ERR_DIR.'custom_errors.log');
            }

            // create login session and redirect user to email verification page
            $_SESSION['auth'] = true;
            $_SESSION['user_id'] = $new_user_id;
            $_SESSION['last_auth_time'] = time() + 1800; // expire in 30 minutes

            echo json_encode([
                'success' => true,
                'redirect_url' => BASE_URL . 'user/home/email_verification.html'
            ]);
        
            exit;

        } catch (mysqli_sql_exception $e) { // catch only mysqli exceptions
            $conn->rollback(); // remove all queries from queue if error occured (undo)            
            unlink($target_dir); // delete uploaded image

            // log the error to a file
            error_log('Mysql error: '.$e->getMessage().PHP_EOL, 3, CUSTOM_ERR_DIR.'custom_errors.log');

            // send message to the client
            echo json_encode([
                'success' => false,
                'error_msg' => 'Registeration was unsuccessfull.'
            ]);
        
            exit;

        } catch (Exception $e) { // catch other exception
            // log the error to a file
            error_log('Caught exception: '.$e->getMessage().PHP_EOL, 3, CUSTOM_ERR_DIR.'custom_errors.log');

            // send message to the client
            echo json_encode([
                'success' => false,
                'error_msg' => 'Registeration was unsuccessfull.'
            ]);
        
            exit;
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
                if (!preg_match("/^.+$/", $input_value)) {
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