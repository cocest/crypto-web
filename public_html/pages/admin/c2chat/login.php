<?php 

// start session
session_start();

// import all the necessary liberaries
require_once '../../../includes/config.php';
require_once '../../../includes/utils.php'; // include utility liberary

date_default_timezone_set('UTC');

// global variables here
$csrf_token;

// check if client should be logged-in automatically
if (isset($_SESSION['agent_auth']) && $_SESSION['agent_auth'] == true && 
    isset($_SESSION['agent_last_auth_time']) && time() < $_SESSION['agent_last_auth_time']) {
    
    // redirect user to there account
    header('Location: '. BASE_URL . 'admin/c2chat/dashboard.html');
    exit;

} else {
    // generate CSRF token
    $csrf_token = generateToken();

    // add the CSRF token to session
    $_SESSION["csrf_token"] = $csrf_token;
}

?>

<!DOCTYPE html>
<html>
    <head>
        <meta charset="UTF-8">
        <title>C2Chat - Login</title>
        <meta name="viewport" content="width=device-width, initial-scale=0.9, maximum-scale=0.9">
        <link type="text/css" href="../../fonts/css/all.min.css" rel="stylesheet">
        <link type="text/css" rel="stylesheet" href="../../styles/c2chat_login.css">
        <script type="text/javascript" src="../../js/utils.js"></script>
    </head>

    <body>
        <div class="page-cont">
            <div class="site-logo-cont">
                <svg class="c2chat-icon" viewBox="0 0 100 100">
                    <path d="M 8.8068652,29.362755 C 13.558884,20.618125 20.689215,14.251892 29.292383,9.3585464 L 27.846347,5.9843426 C 17.618068,12.033995 10.11834,19.296379 5.6737859,27.916667 Z m 7.7121948,3.856232 c 4.347542,-7.372247 9.874068,-12.386443 16.147407,-15.90696 l -1.446036,-3.13319 C 23.215606,18.670138 17.538591,24.713366 13.385981,31.7729 Z m 4.011773,15.250263 c 0.500132,-7.960304 9.610761,-26.930525 32.787925,-27.377287 21.254224,0.413808 32.634043,18.819053 33.24178,27.377287 L 83.440657,48.44984 83.328085,63.112915 H 94.701583 V 48.469251 l -4.781245,0.01583 C 88.995854,37.432113 77.049844,18.004858 53.318758,17.773027 31.959113,17.600011 17.87304,35.748385 17.029211,48.46925 h -4.934105 v 14.962006 h 5.411599 c 1.211451,10.13949 12.529899,26.787867 32.310429,28.332308 l 0.0032,2.570921 7.99756,-0.09317 -0.0046,-9.817322 -8.154604,0.216879 1.31e-4,3.956044 C 35.212332,85.597945 24.273063,77.780915 20.530833,63.43126 H 23.79145 L 23.627766,48.469254 Z M 39.948925,63.749597 H 66.68859 c -1.818433,4.235799 -6.793083,7.928417 -13.369832,7.958512 -6.57675,0.0301 -11.568356,-3.818892 -13.369833,-7.958512 z m 41.090011,-8.899276 c 0,15.302095 -12.404363,27.706892 -27.705921,27.706892 -15.301559,0 -27.705922,-12.404797 -27.705922,-27.706892 0,-15.302094 12.404363,-27.706891 27.705922,-27.706891 15.301558,0 27.705921,12.404797 27.705921,27.706891 m -12.83321,-4.297595 c 0,2.029744 -1.645375,3.675176 -3.675047,3.675176 -2.029673,0 -3.675047,-1.645433 -3.675047,-3.675176 0,-2.029742 1.645374,-3.675175 3.675047,-3.675175 2.029672,0 3.675047,1.645431 3.675047,3.675175 m -22.498356,0 c 0,2.029743 -1.645374,3.675176 -3.675047,3.675176 -2.029672,0 -3.675047,-1.645432 -3.675047,-3.675176 0,-2.029744 1.645375,-3.675175 3.675047,-3.675175 2.029673,0 3.675047,1.645433 3.675047,3.675175 m 32.148874,4.297599 c 0,13.544277 -10.979423,24.524084 -24.523226,24.524084 -13.543805,2e-6 -24.523228,-10.979806 -24.523228,-24.524084 -2e-6,-13.544279 10.979423,-24.524088 24.523228,-24.524087 13.543805,0 24.523228,10.979808 24.523226,24.524087 z"></path>
                </svg>
                <div class="c2chat-name">C2Chat</div>
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
                        <input type="email" placeholder="EMAIL" name="email" required autofocus spellcheck="false">
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

                if (login_form.elements["email"].value.trim().length < 1 || 
                    login_form.elements["password"].value.trim().length < 1) {
                    return;
                }

                // disable inputs
                login_form.elements["email"].disabled = true;
                login_form.elements["password"].disabled = true;
                document.querySelector(".login-btn").disabled = true;

                let req_url = '../../login_c2chat_agent';

                // send request to server
                window.ajaxRequest(
                    req_url,
                    form_data,
                    { contentType: false },

                    // listen to response from the server
                    function (response) {
                        // enable inputs
                        login_form.elements["email"].disabled = false;
                        login_form.elements["password"].disabled = false;
                        document.querySelector(".login-btn").disabled = false;

                        // convert response to object
                        let response_data = JSON.parse(response);

                        if (response_data.success) {
                            // redirect to logged in page
                            window.location.replace(response_data.redirect_url);

                        } else { // invalid email or password
                            let elem = document.getElementById("error-msg-cont");
                            elem.querySelector(".msg").innerHTML = "Sorry, email or password is incorrect.";
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
                            login_form.elements["email"].disabled = false;
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
                                login_form.elements["email"].disabled = false;
                                login_form.elements["password"].disabled = false;
                                document.querySelector(".login-btn").disabled = false;

                                let elem = document.getElementById("error-msg-cont");
                                elem.querySelector(".msg").innerHTML = "Sorry, email or password is incorrect.";
                                elem.removeAttribute("class");
                                err_msg_displayed = true;

                            }, response_data.retry_after * 1000);

                        } else {
                            // enable inputs
                            login_form.elements["email"].disabled = false;
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