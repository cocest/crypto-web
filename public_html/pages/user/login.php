<?php 

// start session
session_start();

// import all the necessary liberaries
require_once '../../includes/config.php';
require_once '../../includes/utils.php'; // include utility liberary

// global variables here
$csrf_token;

// check if client should be logged-in automatically
if (isset($_COOKIE['auto_login'])) {
    // redirect user to login processor
    $_SESSION['auto_login_user'] = true;
    header('Location: ' . BASE_URL . 'login_user');
    exit;

} else {
    // generate CSRF token
    $csrf_token = generateToken();

    // add the CSRF token to session
    $_SESSION["csrf_token"] = $csrf_token;
}

?>

<!DOCTYPE html>
<html lang="en-US">

<head>
    <meta charset="UTF-8">
    <title>CryptoWeb - Sign In</title>
    <link rel="icon" type="image/png" href="favicon1.png" sizes="16x16">
    <link rel="icon" type="image/png" href="favicon2.png" sizes="32x32">
    <link rel="icon" type="image/png" href="favicon3.png" sizes="120x120">
    <meta name="description" content="CryptoWeb registeration page">
    <meta name="keywords" content="sign in, sign up, register, register to CryptoWeb, create account with CryptoWeb">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">
    <link type="text/css" href="../fonts/css/all.min.css" rel="stylesheet">
    <link type="text/css" rel="stylesheet" href="../styles/UdaraX.css">
    <link type="text/css" rel="stylesheet" href="../styles/login.css">
    <script type="text/javascript" src="../js/utils.js"></script>
    <script type="text/javascript" src="../js/login.js"></script>
</head>

<body>
    <div class="login-page ux-center-elem shadow ux-rd-corner-2">
        <div class="site-logo-cont">Websitename</div>
        <h2 class="sign-in-header">Sign In</h2>
        <form name="login-form" onsubmit="return processLoginForm(event)" autocomplete="off" novalidate>
            <div class="input-cont">
                <div class="username-input-wrapper lb-normal-color">
                    <label for="username-input">Username</label>
                    <input id="username-input" class="hr-line-input" attachevent type="text" name="username">
                </div>
            </div>
            <div class="input-cont">
                <div class="password-input-wrapper lb-normal-color">
                    <label for="password-input">Password</label>
                    <div class="input-icon-cont">
                        <div class="showpasswd-btn-cont hide" onclick="showUserPassword(this)">
                            <span class="fas fa-eye"></span>
                        </div>
                    </div>
                    <input id="password-input" class="hr-line-input" attachevent type="password" name="password">
                </div>
            </div>
            <!--error message box-->
            <div id="err-msg-box" class="hide-elem">
                <div class="pointer"></div>
                <div class="close-btn">
                    <span class="far fa-times-circle"></span>
                </div>
                <div class="msg">Username or password is incorrect.</div>
            </div>
            <div class="remember-input-cont">
                <input id="remember-input" type="checkbox" name="remember" value="1">
                <div class="remember-cont">
                    <div class="item-1">
                        <label for="remember-input">
                            <img src="../images/icons/check_button_1.png" />
                        </label>
                    </div>
                    <div class="item-2">
                        <p>Remember me</p>
                    </div>
                </div>
            </div>
            <div class="input-cont">
                <input id="login-submit-input" type="submit" value="Sign In">
            </div>
            <div class="forgot-pswd-cont">
                <a href="#">Forgot your password?</a>
            </div>
            <div class="vt-bars-anim-cont hide-elem">
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
            <div class="csrf-input-cont">
                <input type="text" name="csrf_token" value="<?php echo $csrf_token; ?>">
            </div>
            <div class="create-account-cont">
                <p>You don't have account? <a href="../register.html">create one</a></p>
            </div>
        </form>
    </div>
</body>

</html>