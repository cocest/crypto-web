<?php 

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
    header('Method Not Allowed', true, 405);
    die(); // exit the script
}

try {
    // check if submitted form's input is correct
    if (!validateUserFormInputs($_POST)) {
        // send message to the client
        echo json_encode([
            'success' => false
        ]);
    
        exit; // stop script
    }

    // template parser
    $nnochi = new Nnochi();

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
    $mail->setFrom(SENDER_EMAIL, SENDER_NAME); // sender mail
    $mail->addAddress(SEND_US_EMAIL_ADDRESS);

    // content
    $mail->isHTML(true);
    $mail->Subject = 'CONTACT US MESSSAGE';
    $mail->Body = $nnochi->render(
        '../templates/contact_us_message.html',
        [
            'name' => $_POST['name'],
            'email_address' => $_POST['email'],
            'message' => sanitiseInput($_POST['message'])
        ]
    );

    if (!$mail->send()) {
        // log the error to a file
        error_log('Mailer Error: '.$mail->ErrorInfo.PHP_EOL, 3, CUSTOM_ERR_DIR.'custom_errors.log');

        // send message to client
        echo json_encode([
            'success' => false
        ]);

    } else {
        // send message to client
        echo json_encode([
            'success' => true
        ]);
    }

} catch (Exception $e) { // catch other exception
    // log the error to a file
    error_log('Caught exception: '.$e->getMessage().PHP_EOL, 3, CUSTOM_ERR_DIR.'custom_errors.log');
}

// utility function to validate form inputs
function validateUserFormInputs($inputs) {
    foreach ($inputs as $input_name => $input_value) {
        switch($input_name) {
            case 'name':
                if (!preg_match("/^[a-zA-Z]+([ ]{1}[a-zA-Z]+)*$/", $input_value)) {
                    return false;
                }

                break;

            case 'email':
                if (!filter_var($input_value, FILTER_VALIDATE_EMAIL)) {
                    return false;
                }

                break;

            case 'message':
                if (!preg_match("/^.+$/", trim($input_value))) {
                    return false;
                }

                break;

            default:
                // you shouldn't be here
        }
    }

    return true;
}

?>