<?php 

// start session
session_start();

// import all the necessary liberaries
require_once '../../includes/config.php';
require_once '../../includes/utils.php'; // include utility liberary

date_default_timezone_set('UTC');

// global variables here
$csrf_token;

// check if client should be logged-in automatically
if (isset($_SESSION['admin_auth']) && $_SESSION['admin_auth'] == true && 
    isset($_SESSION['admin_last_auth_time']) && time() < $_SESSION['admin_last_auth_time']) {
    
    // redirect user to there account
    header('Location: '. BASE_URL . 'admin/dashboard.html');
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
    <title>Admin - Login</title>
    <link rel="icon" type="image/png" href="../images/icons/favicon1.png" sizes="16x16">
    <link rel="icon" type="image/png" href="../images/icons/favicon2.png" sizes="32x32">
    <link rel="icon" type="image/png" href="../images/icons/favicon3.png" sizes="120x120">
    <meta name="description" content="Thecitadelcapital Admin login page">
    <meta name="keywords" content="sign in, log in">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">
    <link type="text/css" href="../fonts/css/all.min.css" rel="stylesheet">
    <link type="text/css" rel="stylesheet" href="../styles/UdaraX.css">
    <link type="text/css" rel="stylesheet" href="../styles/admin_login.css">
    <script type="text/javascript" src="../js/utils.js"></script>
</head>

<body>
    <div class="page-cont">
        <div class="site-logo-cont">
            <img src="../images/icons/citadel_capital_logo.png" alt="thecitadelcapital" />
        </div>
        <div class="login-cont">
            <h1 class="header-caption">Sign In</h1>
            <div id="error-msg-cont" class="remove-elem">
                <div class="msg"></div>
                <div class="btn" onclick="closeErrMsg()">
                    <i class="far fa-times-circle"></i>
                </div>
            </div>
            <form name="login-form" onsubmit="return processLoginForm(event)" autocomplete="off">
                <div class="input-cont">
                    <input type="text" placeholder="USER" name="username" required autofocus spellcheck="false">
                    <div class="input-icon-cont">
                        <i class="fas fa-user"></i>
                    </div>
                </div>
                <div class="input-cont">
                    <input type="password" placeholder="PASSWORD" name="password" required>
                    <div class="input-icon-cont">
                        <i class="fas fa-lock"></i>
                    </div>
                </div>
                <div class="csrf-input-cont">
                    <input type="text" name="csrf_token" value="<?php echo $csrf_token; ?>">
                </div>
                <div class="submit-btn-cont">
                    <button class="login-btn" type="submit">Sign In</button>
                </div>
            </form>
        </div>
    </div>
    <script>
        // initialise constants and variables here
        let err_msg_displayed = false;

        // close displayed error message
        window.closeErrMsg = function () {
            document.getElementById("error-msg-cont").setAttribute("class", "remove-elem");
            err_msg_displayed = false;
        };

        window.processLoginForm = function (e) {
            e.preventDefault(); // prevent form from submitting

            // close displayed error message
            if (err_msg_displayed) {
                document.getElementById("error-msg-cont").setAttribute("class", "remove-elem");
                err_msg_displayed = false;
            }

            let login_form = document.forms["login-form"];
            let form_data = new FormData(login_form);

            if (login_form.elements["username"].value.trim().length < 1 || 
                login_form.elements["username"].value.trim().length < 1) {
                return;
            }

            // disable inputs
            login_form.elements["username"].disabled = true;
            login_form.elements["password"].disabled = true;
            document.querySelector(".login-btn").disabled = true;

            let req_url = '../login_admin';

            // send request to server
            window.ajaxRequest(
                req_url,
                form_data,
                { contentType: false },

                // listen to response from the server
                function (response) {
                    // enable inputs
                    login_form.elements["username"].disabled = false;
                    login_form.elements["password"].disabled = false;
                    document.querySelector(".login-btn").disabled = false;

                    // convert response to object
                    let response_data = JSON.parse(response);

                    if (response_data.success) {
                        // redirect to logged in page
                        window.location.replace(response_data.redirect_url);

                    } else { // invalid username or password
                        let elem = document.getElementById("error-msg-cont");
                        elem.querySelector(".msg").innerHTML = "Sorry, username or password is incorrect.";
                        elem.removeAttribute("class");
                        err_msg_displayed = true;
                    }
                },

                // listen to server error
                function (err_status, msg) {
                    // check if is a timeout
                    if (err_status == 408 || err_status == 504) {

                        window.processLoginForm(e);

                    } else if (err_status == 503) { // check if is server busy
                        // enable inputs
                        login_form.elements["username"].disabled = false;
                        login_form.elements["password"].disabled = false;
                        document.querySelector(".login-btn").disabled = false;

                        let elem = document.getElementById("error-msg-cont");
                        elem.querySelector(".msg").innerHTML = "Server is busy try again later.";
                        elem.removeAttribute("class");
                        err_msg_displayed = true;

                    } else if (err_status == 429) { // too many request error
                        response_data = JSON.parse(msg); // convert string to object

                        setTimeout(function () {
                            // enable inputs
                            login_form.elements["username"].disabled = false;
                            login_form.elements["password"].disabled = false;
                            document.querySelector(".login-btn").disabled = false;

                            let elem = document.getElementById("error-msg-cont");
                            elem.querySelector(".msg").innerHTML = "Sorry, username or password is incorrect.";
                            elem.removeAttribute("class");
                            err_msg_displayed = true;

                        }, response_data.retry_after * 1000);

                    } else {
                        // enable inputs
                        login_form.elements["username"].disabled = false;
                        login_form.elements["password"].disabled = false;
                        document.querySelector(".login-btn").disabled = false;

                        let elem = document.getElementById("error-msg-cont");
                        elem.querySelector(".msg").innerHTML = "Error occured, check your connection.";
                        elem.removeAttribute("class");
                        err_msg_displayed = true;
                    }
                }
            );
        };
    </script>
</body>

</html>