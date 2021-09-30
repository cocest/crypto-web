<?php 

// start session
session_start();

// import all the necessary liberaries
require_once '../includes/config.php';
require_once '../includes/utils.php'; // include utility liberary
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
require_once '../includes/PHPMailer/Exception.php';
require_once '../includes/PHPMailer/PHPMailer.php';
require_once '../includes/PHPMailer/SMTP.php';

// check if request method is post
if ($_SERVER['REQUEST_METHOD'] != 'POST') {
    die(); // stop script
}

// check if user is authenticated
if (!(isset($_SESSION['auth']) && $_SESSION['auth'] == true)) {
    header('Unauthorized', TRUE, '401');
    exit; // exit the script
}

date_default_timezone_set('UTC');

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

    // check if email contain header and body
    if (!validateComposeMail()) {
        // send message to client
        echo json_encode([
            'success' => false,
            'error_msg' => 'Email contains no header or body.'
        ]);

        exit; // exit script
    }

    // add message to mail box
    /*
    $query = 'INSERT INTO user_mail (userID, header, body, time) VALUES (?, ?, ?)';
    $stmt = $conn->prepare($query); // prepare statement
    $stmt->bind_param('issi', $_SESSION['user_id'], $mail_header, $mail_body, $sent_time);
    $mail_header = sanitiseInput($_POST['mail_header']);
    $mail_body = $_POST['mail_body'];
    $sent_time = time(); // sent time in UTC
    $stmt->execute();
    

    // send message to client
    echo json_encode([
        'success' => true'
    ]);
    */

    // get user's information
    $query = 'SELECT firstName, lastName FROM users WHERE id = ? LIMIT 1';
    $stmt = $conn->prepare($query); // prepare statement
    $stmt->bind_param('i', $_SESSION['user_id']);
    $stmt->execute();
    $stmt->bind_result($first_name, $last_name);
    $stmt->fetch();

    // user full name
    $user_account_name = $last_name . ' ' . $first_name;

    // send mail to our customer care
    $mail = new PHPMailer();

    // server settings
    /*$mail->isSMTP();
    $mail->Host = SMTP_HOST;
    $mail->Port = SMTP_PORT;
    $mail->SMTPAuth = true;
    $mail->Username = SMTP_USERNAME;
    $mail->Password = SMTP_PASSWORD;
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS; // Enable TLS encryption*/
   
    // recipient
    $mail->setFrom(SENDER_EMAIL, 'Username: '.$user_account_name); // sender mail
    $mail->addAddress(SEND_US_EMAIL_ADDRESS);

    // content
    $mail->isHTML(true);
    $mail->Subject = sanitiseInput($_POST['mail_header']);
    $mail->Body = $_POST['mail_body'];

    if (!$mail->send()) {
        // log the error to a file
        error_log('Mailer Error: '.$mail->ErrorInfo.PHP_EOL, 3, CUSTOM_ERR_DIR.'custom_errors.log');

        // send message to client
        echo json_encode([
            'success' => false,
            'error_msg' => "Email can't be sent due to error."
        ]);

    } else {
        // send message to client
        echo json_encode([
            'success' => true
        ]);
    }

} catch (mysqli_sql_exception $e) {
    // log the error to a file
    error_log('Mysql error: '.$e->getMessage().PHP_EOL, 3, CUSTOM_ERR_DIR.'custom_errors.log');
    
} catch (Exception $e) { // catch other exception
    // log the error to a file
    error_log('Caught exception: '.$e->getMessage().PHP_EOL, 3, CUSTOM_ERR_DIR.'custom_errors.log');
}

function validateComposeMail() {
    if (!(isset($_POST['mail_header']) && !empty($_POST['mail_header']))) {
        return false;

    } else if (!(isset($_POST['mail_body']) && !empty($_POST['mail_body']))) {
        return false;
    }

    return true;
}

?> 