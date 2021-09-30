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
                $search_email_hash = hash('sha1', strtolower(sanitiseInput($_POST['d'])));

                // check if email exist
                $query = 'SELECT 1 FROM users WHERE searchEmailHash = ? LIMIT 1';
                $stmt = $conn->prepare($query); // prepare statement

                // check if statement compile
                if($stmt === false) {
                    trigger_error('Wrong SQL: '.$query.' Error: '.$conn->error, E_USER_ERROR);
                }

                $stmt->bind_param('s', $search_email_hash);
                $stmt->execute();
                $stmt->store_result(); // needed for num_rows

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
                $user_name = strtolower(sanitiseInput($_POST['d']));

                // check if user name exist
                $query = 'SELECT 1 FROM users WHERE userName = ? LIMIT 1';
                $stmt = $conn->prepare($query); // prepare statement

                // check if statement compile
                if($stmt === false) {
                    trigger_error('Wrong SQL: '.$query.' Error: '.$conn->error, E_USER_ERROR);
                }

                $stmt->bind_param('s', $user_name);
                $stmt->execute();
                $stmt->store_result(); // needed for num_rows

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
                $stmt->store_result(); // needed for num_rows

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
                     FROM user_testimonies AS A LEFT JOIN users AS B ON A.userID = B.id 
                     WHERE verified = 1 ORDER BY RAND() LIMIT 10';

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
                                'profile_picture' => empty($row['smallProfilePictureURL']) ? BASE_URL.'images/icons/profile_pic2.png' : BASE_URL.'uploads/users/profile/'.$row['smallProfilePictureURL'],
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
                $query = 'SELECT email, accountActivated, firstName FROM users WHERE id = ? LIMIT 1';
                $stmt = $conn->prepare($query); // prepare statement
                $stmt->bind_param('i', $_SESSION['user_id']);
                $stmt->execute();
                $stmt->store_result(); // needed for num_rows

                // check if is empty
                if ($stmt->num_rows > 0) {
                    $stmt->bind_result($user_email, $account_activated, $first_name);
                    $stmt->fetch();

                    // check if account is not activated
                    if ($account_activated == 0) {
                        // generate 32 digit unique key plus user ID
                        $verification_token = randomText('hexdec', 32);
                        $token = $_SESSION['user_id'] . ':' . $verification_token;

                        $encrypted_token = opensslEncrypt($token, OPENSSL_ENCR_KEY); // encrypt the token
                        $username = $first_name;
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
                    }
                }

                $stmt->close();
                $conn->close();

                echo 'SUCCESS';

                break;

            case 'reset_password':
                if (!isset($_POST['email_address'])) {
                    trigger_error('Request is not properly formed', E_USER_ERROR);
                }

                // generate hash of 40 characters length from user's email address
                $search_email_hash = hash('sha1', strtolower($_POST['email_address']));

                // check if user email exist
                $query = 'SELECT id, email FROM users WHERE searchEmailHash = ? LIMIT 1';
                $stmt = $conn->prepare($query); // prepare statement
                $stmt->bind_param('s', $search_email_hash);
                $stmt->execute();
                $stmt->store_result(); // needed for num_rows

                // check if is empty
                if ($stmt->num_rows > 0) {
                    $stmt->bind_result($user_id, $user_email);
                    $stmt->fetch();
                    $stmt->close();

                    // generate 32 digit unique key plus user ID
                    $verification_token = randomText('hexdec', 32);
                    $token = $user_id . ':' . $verification_token;

                    $encrypted_token = opensslEncrypt($token, OPENSSL_ENCR_KEY); // encrypt the token
                    $verification_url = BASE_URL . 'reset_password?token=' . urlencode(base64_encode($encrypted_token));

                    // add the token to the database for later verification
                    $query = 'INSERT INTO user_reset_password (userID, token) VALUES(?, ?)';
                    $stmt = $conn->prepare($query); // prepare statement
                    $stmt->bind_param('is', $user_id, $verification_token);
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
                    $mail->Subject = 'Reset Password';
                    $mail->AddEmbeddedImage("../templates/logo.png", "site-logo");
                    $mail->AddEmbeddedImage("../templates/fb_logo.png", "fb-logo");
                    $mail->AddEmbeddedImage("../templates/tw_logo.png", "tw-logo");
                    $mail->Body = $nnochi->render(
                        '../templates/reset_password.html',
                        [
                            'verification_url' => $verification_url,
                            'year' => date('Y')
                        ]
                    );

                    if (!$mail->send()) {
                        // log the error to a file
                        error_log('Mailer Error: '.$mail->ErrorInfo.PHP_EOL, 3, CUSTOM_ERR_DIR.'custom_errors.log');
                    }

                    // send response back to client
                    echo json_encode([
                        'success' => true
                    ]);

                } else {
                    // email doesn't exist
                    echo json_encode([
                        'success' => false,
                        'error_code' => 'no_email_address'
                    ]);
                }

                $stmt->close();
                $conn->close();

                break;

            case 'delete_testimony':
                if (!isset($_POST['id'])) {
                    trigger_error('Request is not properly formed', E_USER_ERROR);
                }

                $query = 'DELETE FROM  user_testimonies WHERE id = ? AND userID = ? LIMIT 1';
                $stmt = $conn->prepare($query); // prepare statement
                $stmt->bind_param('ii', $_POST['id'], $_SESSION['user_id']);
                $stmt->execute();

                $stmt->close();
                $conn->close();

                echo 'SUCCESS';

                break;

            case 'get_investment_records':
                if (!(isset($_POST['offset']) && isset($_POST['limit']))) {
                    trigger_error('Request is not properly formed', E_USER_ERROR);
                }

                $query = 
                    'SELECT B.package, A.ROI, A.amountInvested, A.revenue, A.duration, A.time 
                     FROM user_invested_package_records AS A LEFT JOIN crypto_investment_packages AS B 
                     ON A.packageID = B.id WHERE userID = ? ORDER BY time DESC LIMIT ?, ?';
                $stmt = $conn->prepare($query); // prepare statement
                $stmt->bind_param('iii', $_SESSION['user_id'], $_POST['offset'], $_POST['limit']);
                $stmt->execute();
                $result = $stmt->get_result();

                $records = [];

                while ($row = $result->fetch_assoc()) {
                    $records[] = [
                        $row['package'],
                        $row['ROI'],
                        $row['amountInvested'],
                        $row['revenue'],
                        $row['duration'],
                        date("M j, Y g:i A", $row['time'])
                    ];
                }

                $stmt->close();
                $conn->close();

                // send result to client
                echo json_encode($records);

                break;

            case 'get_user_info':
                if (!(isset($_POST['info_type']) && isset($_POST['user_id']))) {
                    trigger_error('Request is not properly formed', E_USER_ERROR);
                }

                // get reguested user data
                if ($_POST['info_type'] == "profile") {
                    $query = 'SELECT * FROM users WHERE id = ? LIMIT 1';
                    $stmt = $conn->prepare($query); // prepare statement
                    $stmt->bind_param('i', $_POST['user_id']);
                    $stmt->execute();
                    $result = $stmt->get_result();

                    if ($result->num_rows > 0) {
                        $row = $result->fetch_assoc();

                        // reformat date for display and editting
                        $splitted_date = explode('-', explode(' ', $row['time'])[0]);
                        $date_obj   = DateTime::createFromFormat('!m', $splitted_date[1]);
                        $month_name = $date_obj->format('M'); // Jan - Dec
                        $registered_date = $month_name.' '.$splitted_date[2].', '.$splitted_date[0];

                        $splitted_date = explode('-', $row['birthdate']);
                        $date_obj   = DateTime::createFromFormat('!m', $splitted_date[1]);
                        $month_name = $date_obj->format('M'); // Jan - Dec
                        $birth_date = $month_name.' '.$splitted_date[2].', '.$splitted_date[0];

                        $req_result = [
                            'success' => true,
                            'profile_url' => empty($row['mediumProfilePictureURL']) ? BASE_URL.'images/icons/profile_pic2.png' : USER_PROFILE_URL.$row['mediumProfilePictureURL'],
                            'username' => '',
                            'referral_id' => $row['referralID'],
                            'reg_date' => $registered_date,
                            'name' => $row['lastName'].' '.$row['firstName'],
                            'birthdate' => $birth_date,
                            'country' => $row['country'],
                            'email' => $row['email'],
                            'phone' => $row['phoneCountryCode'].' '.$row['phoneNumber']
                        ];

                        // close connection to database
                        $stmt->close();
                        $conn->close();

                        // send result back to client
                        echo json_encode([
                            'success' => true,
                            'user' => $req_result
                        ]);

                    } else {
                        // close connection to database
                        $stmt->close();
                        $conn->close();

                        // send result back to client
                        echo json_encode([
                            'success' => true,
                            'user' => null
                        ]);
                    }

                } else if ($_POST['info_type'] == "investment") {
                    // set default timezone
                    date_default_timezone_set('UTC');

                    // get user's current investment
                    $query = 
                        'SELECT A.amountInvested, A.startTime, B.package, B.durationInMonth, B.monthlyROI 
                        FROM user_current_investment AS A LEFT JOIN crypto_investment_packages AS B 
                        ON A.packageID = B.id WHERE A.userID = ? AND A.endTime > ? LIMIT 1';
                    $investment_stmt = $conn->prepare($query); // prepare statement
                    $investment_stmt->bind_param('ii', $_POST['user_id'], $current_time);
                    $current_time = time();
                    $investment_stmt->execute();
                    $investment_result = $investment_stmt->get_result();
                    $investment = $investment_result->fetch_assoc();

                    // get user's account and statistics
                    $query = 
                        'SELECT A.totalBalance, A.availableBalance, B.totalInvestment, B.totalRevenue 
                        FROM user_account AS A LEFT JOIN user_investment_statistics AS B ON A.userID = B.userID 
                        WHERE A.userID = ? LIMIT 1';
                    $stmt = $conn->prepare($query); // prepare statement
                    $stmt->bind_param('i', $_SESSION['user_id']);
                    $stmt->execute();
                    $stmt->bind_result($total_balance, $available_balance, $total_investment, $total_revenue);
                    $stmt->fetch();

                    // close connection to database
                    $stmt->close();
                    $conn->close();

                    // send result back to client
                    echo json_encode([
                        'success' => true,
                        'current_investment' => (empty($investment) ? null : [
                            'amount_invested' => cladNumberFormat($investment['amountInvested']).' USD',
                            'invested_date' => date("M j, Y", $investment['startTime']),
                            'package' => $investment['package'],
                            'duration' => $investment['durationInMonth'].' month',
                            'roi' => floor($investment['monthlyROI']).'%',
                        ]),
                        'revenue' => [
                            'total_balance' => cladNumberFormat($total_balance).' USD',
                            'available_balance' => cladNumberFormat($available_balance).' USD'
                        ],
                        'overview' => [
                            'total_investment' => cladNumberFormat($total_investment).' USD',
                            'total_revenue' => cladNumberFormat($total_revenue).' USD'
                        ]
                    ]);
                }

                break;

            case 'get_registered_users':
                if (!(isset($_POST['search']) && isset($_POST['field']) && 
                    isset($_POST['offset']) && isset($_POST['limit']))) {

                    trigger_error('Request is not properly formed', E_USER_ERROR);
                }

                // get total number of registered users
                if (empty($_POST['search'])) {
                    $query = 'SELECT COUNT(*) AS total FROM users';
                    $stmt = $conn->prepare($query); // prepare statement
                } else {
                    // decode search value
                    $search_reg_user = urldecode($_POST['search']);
                    $search_reg_user = trim($search_reg_user);

                    $query = 'SELECT COUNT(*) AS total FROM users WHERE referralID LIKE ?';
                    $stmt = $conn->prepare($query); // prepare statement
                    $stmt->bind_param('s', $search_user);
                    $search_user = '%'.$search_reg_user.'%';
                }
                
                $stmt->execute();
                $stmt->bind_result($total_reg_users);
                $stmt->fetch();
                $stmt->close();

                // get registered users
                if (empty($_POST['search'])) {
                    $query = 'SELECT id, referralID, firstName, lastName FROM users ORDER BY time DESC LIMIT ?, ?';
                    $stmt = $conn->prepare($query); // prepare statement
                    $stmt->bind_param('ii', $_POST['offset'], $_POST['limit']);
                } else {
                    $query = 'SELECT id, referralID, firstName, lastName FROM users WHERE referralID LIKE ? ORDER BY time DESC LIMIT ?, ?';
                    $stmt = $conn->prepare($query); // prepare statement
                    $stmt->bind_param('sii', $search_user, $_POST['offset'], $_POST['limit']);
                    $search_user = '%'.$search_reg_user.'%';
                }
                
                $stmt->execute();
                $result = $stmt->get_result();

                $users = [];

                while ($row = $result->fetch_assoc()) {
                    $users[] = [
                        $row['referralID'],
                        '',
                        $row['lastName'].' '.$row['firstName'],
                        $row['id']
                    ];
                }

                $stmt->close();
                $conn->close();

                // send result to client
                echo json_encode([
                    'users' => $users,
                    'metadata' => [
                        'total' => $total_reg_users
                    ]
                ]);

                break;

            case 'get_unverified_account':
                if (!(isset($_POST['offset']) && isset($_POST['limit']))) {
                    trigger_error('Request is not properly formed', E_USER_ERROR);
                }

                // get total number of unverified account
                $query = 'SELECT COUNT(*) AS total FROM user_account_verification WHERE identification = 0';
                $stmt = $conn->prepare($query); // prepare statement
                $stmt->execute();
                $stmt->bind_result($total_unverified_account);
                $stmt->fetch();
                $stmt->close();

                // get unverified account
                $query = 
                    'SELECT A.userID, A.time, B.firstName, B.lastName 
                    FROM user_account_verification AS A LEFT JOIN users AS B ON A.userID = B.id 
                    WHERE A.identification = 0 ORDER BY A.time DESC LIMIT ?, ?';
                $stmt = $conn->prepare($query); // prepare statement
                $stmt->bind_param('ii', $_POST['offset'], $_POST['limit']);
                $stmt->execute();
                $result = $stmt->get_result();

                $accounts = [];

                while ($row = $result->fetch_assoc()) {
                    $accounts[] = [
                        '',
                        $row['lastName'].' '.$row['firstName'],
                        date("M j, Y g:i A", $row['time']),
                        $row['userID']
                    ];
                }

                $stmt->close();
                $conn->close();

                // send result to client
                echo json_encode([
                    'accounts' => $accounts,
                    'metadata' => [
                        'total' => $total_unverified_account
                    ]
                ]);

                break;

            case 'get_user_uploaded_id':
                if (!isset($_POST['user_id'])) {
                    trigger_error('Request is not properly formed', E_USER_ERROR);
                }

                // get user's identification
                $query = 'SELECT identificationURL FROM user_identification WHERE userID = ? LIMIT 1';
                $stmt = $conn->prepare($query); // prepare statement
                $stmt->bind_param('i', $_POST['user_id']);
                $stmt->execute();
                $stmt->bind_result($identification_url);
                $stmt->fetch();

                // close connection to database
                $stmt->close();
                $conn->close();

                $size = getimagesize(USER_ID_UPLOAD_DIR.$identification_url);

                // send result to client
                echo json_encode([
                    'user_id_url' => USER_ID_UPLOAD_URL.$identification_url,
                    'metadata' => [
                        'type' => $size['mime'],
                        'width' => $size['0'],
                        'height' => $size['1']
                    ]
                ]);

                break;

            case 'accept_user_id':
                if (!isset($_POST['user_id'])) {
                    trigger_error('Request is not properly formed', E_USER_ERROR);
                }

                // enable mysql exception
                mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

                try {
                    // start transaction
                    $conn->begin_transaction();

                    // mark uploaded identification as verified
                    $query = 'UPDATE user_account_verification SET identification = ? WHERE userID = ? LIMIT 1';
                    $stmt = $conn->prepare($query); // prepare statement
                    $stmt->bind_param('ii', $id_verified, $_POST['user_id']);
                    $id_verified = 1;
                    $stmt->execute();
                    $stmt->close();

                    // mark identification as verified
                    $query = 'UPDATE user_identification SET verified = ? WHERE userID = ? LIMIT 1';
                    $stmt = $conn->prepare($query); // prepare statement
                    $stmt->bind_param('ii', $verified, $_POST['user_id']);
                    $verified = 1;
                    $stmt->execute();
                    $stmt->close();

                    // send user a notification
                    $query = 
                        'INSERT INTO users_notification (msgID, userID, title, content, time)
                        VALUES(?, ?, ?, ?, ?)';

                    $stmt = $conn->prepare($query); // prepare statement
                    $stmt->bind_param(
                        'sissi', 
                        $msg_id, 
                        $_POST['user_id'], 
                        $msg_title, 
                        $msg_content, 
                        $msg_time
                    );
                    $msg_id = randomText('hexdec', 32);
                    $msg_content = "Your uploaded identification has been successfully verified. Reload the page to continue.";
                    $msg_title = 'Account Verification';
                    $msg_time = time();
                    $stmt->execute();
                    $stmt->close();

                    // commit all the transaction
                    $conn->commit();

                    // close connection to database
                    $conn->close();

                    // send result back to client
                    echo json_encode([
                        'success' => true
                    ]);

                } catch (Exception $e) {
                    $conn->rollback(); // remove all queries from queue if error occured (undo)
                    $conn->close(); // close connection to database

                    // send result back to client
                    echo json_encode([
                        'success' => false
                    ]);
                }

                break;

            case 'dashboard_stat':
                // set default timezone
                date_default_timezone_set('UTC');

                // enable mysql exception
                mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

                try {
                    // start transaction
                    $conn->begin_transaction();

                    // get total number of registered users
                    $query = 'SELECT COUNT(*) AS total FROM users';
                    $stmt = $conn->prepare($query); // prepare statement
                    $stmt->execute();
                    $stmt->bind_result($total_reg_user);
                    $stmt->fetch();
                    $stmt->close();

                    // get total number of unverified account
                    $query = 'SELECT COUNT(*) AS total FROM user_account_verification WHERE identification = 0';
                    $stmt = $conn->prepare($query); // prepare statement
                    $stmt->execute();
                    $stmt->bind_result($total_unverified_account);
                    $stmt->fetch();
                    $stmt->close();

                    // get total active investment
                    $query = 'SELECT COUNT(*) AS total FROM user_current_investment WHERE endTime > ?';
                    $stmt = $conn->prepare($query); // prepare statement
                    $stmt->bind_param('i', $current_time);
                    $current_time = time();
                    $stmt->execute();
                    $stmt->bind_result($active_investment);
                    $stmt->fetch();
                    $stmt->close();

                    // get total investment
                    $query = 'SELECT COUNT(*) AS total FROM user_invested_package_records';
                    $stmt = $conn->prepare($query); // prepare statement
                    $stmt->execute();
                    $stmt->bind_result($total_investment);
                    $stmt->fetch();
                    $stmt->close();

                    // get total balance and available balance
                    $query = 'SELECT SUM(totalBalance) AS total_balance, SUM(availableBalance) AS available_balance FROM user_account';
                    $stmt = $conn->prepare($query); // prepare statement
                    $stmt->execute();
                    $stmt->bind_result($total_balance, $available_balance);
                    $stmt->fetch();
                    $stmt->close();

                    // commit all the transaction
                    $conn->commit();

                    // close connection to database
                    $conn->close();

                    // send result back to client
                    echo json_encode([
                        'success' => true,
                        'user' => [
                            'total_users' => cladNumberFormat($total_reg_user),
                            'total_unverified_account' => cladNumberFormat($total_unverified_account)
                        ],
                        'investment' => [
                            'active_investment' => $active_investment,
                            'total_investment' => $total_investment
                        ],
                        'account' => [
                            'total_balance' => cladNumberFormat($total_balance).' USD',
                            'available_balance' => cladNumberFormat($available_balance).' USD'
                        ]
                    ]);

                } catch (Exception $e) {
                    $conn->rollback(); // remove all queries from queue if error occured (undo)
                    $conn->close(); // close connection to database

                    // send result back to client
                    echo json_encode([
                        'success' => false
                    ]);
                }

                break;

            case 'fetch_new_testimony':
                if (!(isset($_POST['time_offset']) && isset($_POST['limit']))) {
                    trigger_error('Request is not properly formed', E_USER_ERROR);
                }

                // get total number of unverified testimony
                $query = 'SELECT COUNT(*) AS total FROM user_testimonies WHERE verified = 0';
                $stmt = $conn->prepare($query); // prepare statement
                $stmt->execute();
                $stmt->bind_result($total_unverified_testimony);
                $stmt->fetch();
                $stmt->close();

                // get unverified account
                $query = 
                    'SELECT A.id, A.testimoney, A.time, B.firstName, B.lastName 
                    FROM user_testimonies AS A LEFT JOIN users AS B ON A.userID = B.id 
                    WHERE A.verified = 0 AND A.time > ? ORDER BY A.time DESC LIMIT ?';
                $stmt = $conn->prepare($query); // prepare statement
                $stmt->bind_param('ii', $_POST['time_offset'], $_POST['limit']);
                $stmt->execute();
                $result = $stmt->get_result();

                $testimonies = [];

                while ($row = $result->fetch_assoc()) {
                    $testimonies[] = [
                        'id' => $row['id'],
                        'name' => $row['lastName'] . ' ' . $row['firstName'],
                        'content' => $row['testimoney'],
                        'fmt_time' => date("M j, Y g:i A", $row['time']),
                        'time' => $row['time']
                    ];
                }

                $stmt->close();
                $conn->close();

                // send result to client
                echo json_encode([
                    'testimonies' => $testimonies,
                    'metadata' => [
                        'total' => $total_unverified_testimony
                    ]
                ]);

                break;

            case 'get_user_testimony':
                if (!(isset($_POST['offset']) && isset($_POST['limit']))) {
                    trigger_error('Request is not properly formed', E_USER_ERROR);
                }

                // get total number of unverified testimony
                $query = 'SELECT COUNT(*) AS total FROM user_testimonies WHERE verified = 0';
                $stmt = $conn->prepare($query); // prepare statement
                $stmt->execute();
                $stmt->bind_result($total_unverified_testimony);
                $stmt->fetch();
                $stmt->close();

                // get unverified account
                $query = 
                    'SELECT A.id, A.testimoney, A.time, B.firstName, B.lastName 
                    FROM user_testimonies AS A LEFT JOIN users AS B ON A.userID = B.id 
                    WHERE A.verified = 0 ORDER BY A.time DESC LIMIT ?, ?';
                $stmt = $conn->prepare($query); // prepare statement
                $stmt->bind_param('ii', $_POST['offset'], $_POST['limit']);
                $stmt->execute();
                $result = $stmt->get_result();

                $testimonies = [];

                while ($row = $result->fetch_assoc()) {
                    $testimonies[] = [
                        'id' => $row['id'],
                        'name' => $row['lastName'] . ' ' . $row['firstName'],
                        'content' => $row['testimoney'],
                        'fmt_time' => date("M j, Y g:i A", $row['time']),
                        'time' => $row['time']
                    ];
                }

                $stmt->close();
                $conn->close();

                // send result to client
                echo json_encode([
                    'testimonies' => $testimonies,
                    'metadata' => [
                        'total' => $total_unverified_testimony
                    ]
                ]);

                break;

            case 'verify_user_testimony':
                if (!isset($_POST['id'])) {
                    trigger_error('Request is not properly formed', E_USER_ERROR);
                }

                $query = 'UPDATE user_testimonies SET verified = 1 WHERE id = ? LIMIT 1';
                $stmt = $conn->prepare($query); // prepare statement
                $stmt->bind_param('i', $_POST['id']);
                $stmt->execute();

                $stmt->close();
                $conn->close();

                echo 'SUCCESS';

                break;

            case 'get_investment_package':
                if (!isset($_POST['id'])) {
                    trigger_error('Request is not properly formed', E_USER_ERROR);
                }

                // get unverified account
                $query = 'SELECT * FROM crypto_investment_packages WHERE id = ?';
                $stmt = $conn->prepare($query); // prepare statement
                $stmt->bind_param('i', $_POST['id']);
                $stmt->execute();
                $result = $stmt->get_result();

                $package;

                while ($row = $result->fetch_assoc()) {
                    $package = [
                        'package' => $row['package'],
                        'min_amount' => $row['minAmount'],
                        'max_amount' => $row['maxAmount'],
                        'duration' => $row['durationInMonth'],
                        'roi' => $row['monthlyROI'],
                        'bonus' => $row['bonus'],
                        'withdraw_percent' => $row['withdrawInvestmentPercent']
                    ];
                }

                $stmt->close();
                $conn->close();

                // send result to client
                echo json_encode($package);

                break;

            case 'subscribe_newsletter':
                if (!isset($_POST['email'])) {
                    trigger_error('Request is not properly formed', E_USER_ERROR);
                }

                // check if email address is valid
                if (!filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)) {
                    echo json_encode([
                        'success' => false,
                        'already_subscribed' => false
                    ]);

                    exit(); // exit script
                }

                // generate hash of 40 characters length from user's email address
                $search_email_hash = hash('sha1', strtolower($_POST['email']));

                // check if email has been subcribed
                $query = 'SELECT 1 FROM user_newsletter_subscription WHERE emailHash = ? LIMIT 1';
                $stmt = $conn->prepare($query); // prepare statement
                $stmt->bind_param('s', $search_email_hash);
                $stmt->execute();
                $stmt->store_result(); // needed for num_rows

                if ($stmt->num_rows > 0) { // email exist
                    // send result to client
                    echo json_encode([
                        'success' => false,
                        'already_subscribed' => true
                    ]);
 
                    // close database connection
                    $stmt->close();
                    $conn->close();

                    exit(); // exit script
                }

                $stmt->close(); // close prepared statement

                // subscribe user to our newsletter
                $query = 'INSERT INTO user_newsletter_subscription (emailHash, emailAddress) VALUES(?, ?)';
                $stmt = $conn->prepare($query); // prepare statement
                $stmt->bind_param('ss', $search_email_hash, $_POST['email']);
                $stmt->execute();

                // close database connection
                $stmt->close();
                $conn->close();

                // send result to client
                echo json_encode([
                    'success' => true,
                    'already_subscribed' => false
                ]);

                break;

            default:
                // you shouldn't be here
        }

    } else {
        trigger_error('Request is not properly formed', E_USER_ERROR);
    }
}

?>