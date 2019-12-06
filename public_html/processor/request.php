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
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // check if URL has request query
    if (isset($_POST['req'])) {
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
                if (!isset($_POST['d'])) {
                    trigger_error('Request is not properly formed', E_USER_ERROR);
                }

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
                if (!isset($_POST['d'])) {
                    trigger_error('Request is not properly formed', E_USER_ERROR);
                }

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
                if (!isset($_POST['d'])) {
                    trigger_error('Request is not properly formed', E_USER_ERROR);
                }

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

            case 'get_user_testimonial':
                $formatted_result;

                // select user's testimonial from database
                $query = 
                    'SELECT A.testimoney, B.firstName, B.lastName, B.smallProfilePictureURL, A.time 
                     FROM user_testimonies AS A LEFT JOIN users AS B 
                     ON A.userID = B.id ORDER BY RAND() LIMIT 10';

                $stmt = $conn->prepare($query); // prepare statement

                // check if statement compile
                if ($stmt === false) {
                    trigger_error('Wrong SQL: '.$query.' Error: '.$conn->error, E_USER_ERROR);
                }

                $stmt->execute();
                $result = $stmt->get_result();
                if ($result->num_rows > 0) {
                    while($row = $result->fetch_assoc()) {
                        $formatted_result[] = 
                            [
                                'name' => $row['lastName'] . ' ' . $row['firstName'],
                                'profile_picture' => $row['smallProfilePictureURL'],
                                'testimoney' => $row['testimoney'],
                                'time' => $row['time']
                            ];
                    }

                    // send result to client testimonies
                    $formatted_result = ['testimonies' => $formatted_result];
                    echo json_encode($formatted_result);

                } else {
                    echo '{"testimonies": []}';
                }

                break;

            case 'get_prev_notification':
                if (!(isset($_POST['time_offset']) && isset($_POST['limit']))) {
                    trigger_error('Request is not properly formed', E_USER_ERROR);
                }

                $messages = []; // list of notification

                // fetch notification
                $query = 'SELECT * FROM users_notification WHERE userID = ? AND time < ? ORDER BY time DESC LIMIT ?';
                $stmt = $conn->prepare($query); // prepare statement
                $stmt->bind_param('iii', $_SESSION['user_id'], $_POST['time_offset'], $_POST['limit']);
                $stmt->execute();
                $result = $stmt->get_result();

                // iterate through the result
                while ($row = $result->fetch_assoc()) {
                    $messages[] = [
                        'id' => $row['msgID'],
                        'title' => $row['title'],
                        'content' => $row['content'],
                        'read' => $row['readState'],
                        'time' => $row['time']
                    ];
                }
    
                $user_notification = [
                    'messages' => $messages
                ];

                $stmt->close();
                $conn->close();

                // send result to client
                echo json_encode($user_notification);

                break;

            case 'read_notification':
                if (!isset($_POST['msg_id'])) {
                    trigger_error('Request is not properly formed', E_USER_ERROR);
                }

                // mark message as read
                $query = 'UPDATE users_notification SET readState = ? WHERE msgID = ? LIMIT 1';
                $stmt = $conn->prepare($query); // prepare statement
                $stmt->bind_param('is', $read_state, $_POST['msg_id']);
                $read_state = 1;
                $stmt->execute();
                $stmt->close();
                $conn->close();

                echo 'SUCCESS';
                
                break;

            case 'delete_notification':
                if (!isset($_POST['msg_id'])) {
                    trigger_error('Request is not properly formed', E_USER_ERROR);
                }

                // delete a message
                $query = 'DELETE FROM users_notification WHERE msgID = ? AND userID = ? LIMIT 1';
                $stmt = $conn->prepare($query); // prepare statement
                $stmt->bind_param('si', $_POST['msg_id'], $_SESSION['user_id']);
                $stmt->execute();
                $stmt->close();
                $conn->close();

                echo 'SUCCESS';
                
                break;

            case 'resend_email_verification':
                $query = 'SELECT email, accountActivated, firstName, lastName FROM users WHERE id = ? LIMIT 1';
                $stmt = $conn->prepare($query); // prepare statement
                $stmt->bind_param('i', $_SESSION['user_id']);
                $stmt->execute();
                $stmt->store_result(); // needed for num_rows

                // check if is empty
                if ($stmt->num_rows > 0) {
                    $stmt->bind_result($user_email, $account_activated, $first_name, $last_name);
                    $stmt->fetch();

                    // check if account is not activated
                    if ($account_activated == 0) {
                        // generate 16 digit unique key plus user ID
                        $verification_token = randomText('hexdec', 32);
                        $token = $_SESSION['user_id'] . ':' . $verification_token;

                        $encrypted_token = opensslEncrypt($token, OPENSSL_ENCR_KEY); // encrypt the token
                        $username = $last_name . ' ' . $first_name;
                        $verification_url = BASE_URL . 'verify_email?token=' . urlencode(base64_encode($encrypted_token));

                        // add the token to the database for later verification
                        $stmt->close(); // close previous
                        $query = 'INSERT INTO verify_user_email (userID, token) VALUES(?, ?)';
                        $stmt = $conn->prepare($query); // prepare statement
                        $stmt->bind_param('is', $_SESSION['user_id'], $verification_token);
                        $stmt->execute();

                        // template parser
                        $nnochi = new Nnochi();

                        // send email to user
                        $mail = new PHPMailer();

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
                        $mail->addAddress($user_email);

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
                            // log the error to a file
                            error_log('Mailer Error: '.$mail->ErrorInfo.PHP_EOL, 3, CUSTOM_ERR_DIR.'custom_errors.log');
                        }
                    }
                }

                $stmt->close();
                $conn->close();

                echo 'SUCCESS';

                break;

            default:
                // you shouldn't be here
        }

    } else {
        trigger_error('Request is not properly formed', E_USER_ERROR);
    }
}

?>