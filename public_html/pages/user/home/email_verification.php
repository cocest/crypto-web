<?php 

// start session
session_start();

// import all the necessary liberaries
require_once '../../../includes/config.php';

// check if user is authenticated
if (!(isset($_SESSION['auth']) && $_SESSION['auth'] == true)) {
    // redirect user to login pages
    header('Location: '. BASE_URL . 'user/login.html');
    exit;
}

// error handler function
function customError($errno, $errstr) {
    echo "<b>Error:</b> [$errno] $errstr<br>";
    die();
}

// set the handler
set_error_handler('customError');

// mysql configuration
$db = $config['db']['mysql'];
        
// enable mysql exception
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

try {
    // connect to database
    $conn = new mysqli($db['host'], $db['username'], $db['password'], $db['dbname']);

    //check connection
    if ($conn->connect_error) {
        throw new mysqli_sql_exception('Database connection failed: ' . $conn->connect_error);
    }

    // redirect user to another page
    $redirect_user = true;

    // check if user's account is not yet activated
    $query = 'SELECT email, identification FROM user_account_verification WHERE userID = ? LIMIT 1';
    $stmt = $conn->prepare($query); // prepare statement
    $stmt->bind_param('i', $_SESSION['user_id']);
    $stmt->execute();
    $stmt->store_result(); // needed for num_rows

    if ($stmt->num_rows > 0) {
        $stmt->bind_result($is_email_verified, $is_id_verified);
        $stmt->fetch();
        $stmt->close();

        // check if personal identification is not verified
        if ($is_email_verified == 1 && $is_id_verified == 0) {
            $conn->close(); // close connection to database

            // redirect user
            header('Location: '. BASE_URL . 'user/home/id_verification.html');
            exit;

        } else if ($is_email_verified == 1 && $is_id_verified == 1) { // user account has been verified
            $query = 'DELETE FROM user_account_verification WHERE userID = ? LIMIT 1';
            $stmt = $conn->prepare($query); // prepare statement
            $stmt->bind_param('i', $_SESSION['user_id']);
            $stmt->execute();
            $stmt->close();

            // set user account to activated
            $query = 'UPDATE users SET accountActivated = ? WHERE id = ? LIMIT 1';
            $stmt = $conn->prepare($query); // prepare statement
            $stmt->bind_param('ii', $account_activated, $_SESSION['user_id']);
            $account_activated = 1;
            $stmt->execute();

            // close connection to database
            $stmt->close();
            $conn->close();

        } else {
            $redirect_user = false;
        }

    }

    // check to redirect user
    if ($redirect_user) {
        header('Location: '. BASE_URL . 'user/home/my_investment.html');
        exit;
    }

} catch (mysqli_sql_exception $e) {
    // log the error to a file
    error_log('Mysql error: '.$e->getMessage().PHP_EOL, 3, CUSTOM_ERR_DIR.'custom_errors.log');

} catch (Exception $e) { // catch other exception
    // log the error to a file
    error_log('Caught exception: '.$e->getMessage().PHP_EOL, 3, CUSTOM_ERR_DIR.'custom_errors.log');
}

// assemble all the part of the page
require_once 'header.php';
require_once 'left_bar_menu.php';

?>
    
    <div class="page-content-cont">
        <h1 class="page-title-hd">Account Verification</h1>
        <div class="content-body-cont">
            <div class="header-description-cont">
                <div class="sub-section-hd">
                    <h4 class="section-group-header">Email Verification</h4>
                </div>
                <div class="courrent-step-indicator">Step 1 of 2</div>
            </div>
            <p class="p1 txt-block-fmt">
                Please verify your email by clicking the link sent 
                to your email address <span class="txt-decor">attamahcelestine@gmail.com</span>.
            </p>
            <p class="p2 txt-block-fmt">
                Note: If you don’t receive any email after 5 minutes 
                click the below button to resend.
            </p>
            <div class="email-resend-btn-cont">
                <input class="fmt-btn" type="button" value="Resend" onclick="resendEmailVerification()" />
            </div>
            <div class="resend-email-anim-cont remove-elem">
                <div class="vt-bars-anim-cont">
                    <div class="vt-bar-cont">
                        <div class="vt-bar-1"></div>
                    </div>
                    <div class="vt-bar-cont">
                        <div class="vt-bar-2"></div>
                    </div>
                    <div class="vt-bar-cont">
                        <div class="vt-bar-3"></div>
                    </div>
                </div>
                <div class="anim-txt">Resending...</div>
            </div>
        </div>
<?php

// page footer
require_once 'footer.php';

?>