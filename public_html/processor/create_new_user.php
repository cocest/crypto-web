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
            die(); // stop script
        }

        // check if upload file is valid
        if (!validateUploadedImage($_FILES)) {
            // delete the file
            die(); // stop script
        }

        // directory to move uploaded file
        $target_dir = USER_ID_UPLOAD_DIR . basename($_FILES['file']['name']);

        // move file to targeted directory
        move_uploaded_file($_FILES['file']['tmp_name'], $target_dir);

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
                $_POST['firstname'],
                $_POST['lastname'],
                $_POST['username'],
                $_POST['email'],
                $search_email_hash,
                $_POST['country'],
                $_POST['phoneCountryCode'],
                $_POST['phoneNumber'],
                $_POST['gender']
            );
    
            $stmt->execute();
            $new_user_id = $stmt->insert_id;
            $stmt->close();

            // add uploaded file information into table
            $query = 'INSERT INTO user_identification (userID, identificationURL) VALUES(?, ?)';
            $stmt = $conn->prepare($query); // prepare statement
            $stmt->bind_param('is', $new_user_id, basename($_FILES['file']['name']));
            $stmt->execute();
            $stmt->close();

            // insert user's login credential into table
            $password_hash = password_hash($_POST['password'], PASSWORD_DEFAULT);
            $query = 'INSERT INTO user_authentication (userID, username, password) VALUES(?, ?, ?)';
            $stmt = $conn->prepare($query); // prepare statement
            $stmt->bind_param('iss', $new_user_id, $_POST['userrname'], $password_hash);
            $stmt->execute();
            $stmt->close();

            // insert user's account verification
            $query = 'INSERT INTO user_account_verification (userID) VALUES(?)';
            $stmt = $conn->prepare($query); // prepare statement
            $stmt->bind_param('i', $new_user_id);
            $stmt->execute();
            $stmt->close();

            // send verification email to the user
            // generate 16 digit unique key plus user ID
            $verification_token = generateToken();
            $token = $new_user_id . ':' . $verification_token;

            $encrypted_token = opensslEncrypt($token, OPENSSL_ENCR_KEY); // encrypt the token
            $username = $_POST['lastname'] . ' ' . $_POST['firstname'];
            $verification_url = BASE_URL . 'verify_email?token=' . base64_encode($encrypted_token);

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
            $mail->Body = $nnochi->render(
                '../templates/email_verification.html',
                [
                    'username' => $username,
                    'verification_url' => $verification_url,
                    'year' => date('Y')
                ]
            );

            if (!$mail->send()) {
                echo 'Mailer Error: ' . $mail->ErrorInfo;
                exit;
            }

            // create login session and redirect user to email verification page
            $_SESSION['auth'] = true;
            $_SESSION['user_id'] = $new_user_id;
            header('Location: ' . BASE_URL . 'user/home/email_verification.html');
            exit;

        } catch (mysqli_sql_exception $e) { // catch only mysqli exceptions
            $conn->rollback(); // remove all queries from queue if error occured (undo)            
            unlink($target_dir); // delete uploaded image

        } catch (Exception $e) { // catch other exception
            echo 'Caught exception: ',  $e->getMessage(), "\n";
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

// utility function to validate user's uploaded file
function validateUploadedImage($uploaded_files) {
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $type = finfo_file($finfo, $uploaded_files['file']['tmp_name']);

    if (isset($type) && in_array($type, ['image/jpg', 'image/jpeg', 'image/png', 'image/gif'])) {
        if ($uploaded_files['file']['size'] / 1048576 < 4) { // less than 4 megabytes
            return true;
        }
    }

    return false;
}

?>