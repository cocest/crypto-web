<?php 

// start session
session_start();

// import all the necessary liberaries
require_once '../../../includes/config.php';
require_once '../../../includes/utils.php'; // include utility liberary

// generate CSRF token
$csrf_token = randomText('hexdec', 16);

// add the CSRF token to session
$_SESSION["csrf_token"] = $csrf_token;

date_default_timezone_set('UTC');

// check if user is authenticated
if (isset($_SESSION['auth']) && $_SESSION['auth'] == true) {
    if (isset($_SESSION['last_auth_time']) && time() < $_SESSION['last_auth_time']) {
        // update the time
        $_SESSION['last_auth_time'] = time() + 1800; // expire in 30 minutes
    
    } else {
        // clear the user's login session
        unset($_SESSION['auth']);
        unset($_SESSION['user_id']);

        // redirect user to login pages
        header('Location: '. BASE_URL . 'user/login.html');
        exit;
    }

} else {
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
        throw new mysqli_sql_exception('Database connection failed: '.$conn->connect_error);
    }

    // check if user has activated his account
    $query = 'SELECT accountActivated FROM users WHERE id = ? LIMIT 1';
    $stmt = $conn->prepare($query); // prepare statement
    $stmt->bind_param('i', $_SESSION['user_id']);
    $stmt->execute();
    $stmt->bind_result($account_activated);
    $stmt->fetch();
    $stmt->close();

    if ($account_activated == 0) {
        // account not yet activated
        $conn->close(); // close connection

        // redirect user
        header('Location: '. BASE_URL . 'user/home/email_verification.html');
        exit;
    }

    // close connection to database
    $conn->close();

} catch (mysqli_sql_exception $e) {
    // log the error to a file
    error_log('Mysql error: '.$e->getMessage().PHP_EOL, 3, CUSTOM_ERR_DIR.'custom_errors.log');
    
} catch (Exception $e) { // catch other exception
    // log the error to a file
    error_log('Caught exception: '.$e->getMessage().PHP_EOL, 3, CUSTOM_ERR_DIR.'custom_errors.log');
}

// set page left menu active menu
// Note: remeber to set this variable before you include "page_left_menu.php"
$left_menu_active_links = [
    'my_investment' => false,
    'packages' => false,
    'testimony' => false,
    'profile' => false,
    'settings' => true
];

// assemble all the part of the page
require_once 'header.php';
require_once 'page_left_menu.php';

?>

    <div class="page-content-cont">
        <h1 class="page-title-hd">Settings</h1>
        <div class="settings-sec-1">
            <h4 class="settings-header-title">Change Password</h4>
            <div class="change-password-input-cont">
                <form name="change-password-form" onsubmit="return processChangePasswordForm(event)" autocomplete="off" novalidate>
                    <div class="settings-input-cont">
                        <label for="current-passwd-input">Current Password</label>
                        <input id="current-passwd-input" type="password" name="currentpassword" attachevent>
                    </div>
                    <div class="settings-input-cont">
                        <label for="new-passwd-input">New Password</label>
                        <input id="new-passwd-input" type="password" name="newpassword" attachevent>
                        <div id="settings-show-passwd-cont" class="hide" onclick="showNewUserPassword()">
                            <span class="fas fa-eye"></span>
                        </div>
                        <div class="passwd-strength-indicator-cont">
                            <div class="ind-wrapper">
                                <div class="indicator"></div>
                            </div>
                            <div class="ind-wrapper">
                                <div class="indicator"></div>
                            </div>
                            <div class="ind-wrapper">
                                <div class="indicator"></div>
                            </div>
                            <div class="ind-wrapper">
                                <div class="indicator"></div>
                            </div>
                            <div class="txt-ind-wrapper">
                                <span id="passwd-txt-indicator">Strength</span>
                            </div>
                        </div>
                    </div>
                    <div class="settings-input-cont">
                        <label for="confirm-passwd-input">Confirm Password</label>
                        <input id="confirm-passwd-input" type="password" name="confirmpassword" attachevent>
                        <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                    </div>
                    <div class="settings-passwd-submit-btn-cont">
                        <div class="settings-passwd-submit-btn-wrapper">
                            <button class="settings-passwd-submit-btn" type="submit">Apply</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
        <script type="text/javascript" src="../../js/zxcvbn.js"></script>
        <script>
            // define and initialise variable here
            let user_passwd_strength = 0;
            let is_passwd_shown = false;
            let passwd_indicator_elems = document.querySelectorAll(".settings-input-cont .passwd-strength-indicator-cont .indicator");

            // show password and hide after some seconds
            window.showNewUserPassword = function () {
                let passwd_input = document.getElementById("new-passwd-input");

                // check if password is shown
                if (!is_passwd_shown && passwd_input.value.length > 0) {
                    is_passwd_shown = true;

                    // show password
                    passwd_input.setAttribute("type", "text");
                    let elem = document.getElementById("settings-show-passwd-cont");
                    elem.setAttribute("class", "show");

                    setTimeout(function () {
                        // hide password
                        passwd_input.setAttribute("type", "password");
                        elem.setAttribute("class", "hide");
                        is_passwd_shown = false;
                    }, 2000);
                }
            };

            window.processChangePasswordForm = function (e) {
                e.preventDefault(); // prevent default behaviour

                // get user filled form
                let form = document.forms["change-password-form"];

                let req_url = '../../change_password';
                let reg_form = new FormData(form);

                if (!validateChangePasswdForm(form)) {
                    return;
                }

                // disable all the input button
                document.getElementById("current-passwd-input").disabled = true;
                document.getElementById("new-passwd-input").disabled = true;
                document.getElementById("confirm-passwd-input").disabled = true;
                document.querySelector('.settings-passwd-submit-btn').disabled = true;

                // send request to server
                window.ajaxRequest(
                    req_url,
                    reg_form,
                    { contentType: false },
                    
                    // listen to response from the server
                    function (response) {
                        let response_data = JSON.parse(response);

                        if (response_data.success) {
                            // reset user's input
                            window.resetForm(["change-password-form"]);
                            applyColorToPasswdStrengthIndicator(passwd_indicator_elems, 0, "indicator", "indicator");
                            document.getElementById("passwd-txt-indicator").innerHTML = "Strength";
                            user_passwd_strength = 0;

                            // successfully message to user
                            let msg_elem = document.getElementById("msg-win-cont");
                            msg_elem.querySelector('.title').innerHTML = "Change Password";
                            msg_elem.querySelector('.body-cont').innerHTML = 
                                "Login password changed sucesssfully.";
                            msg_elem.removeAttribute("class");

                        } else {
                            // show error message to user
                            let msg_elem = document.getElementById("msg-win-cont");
                            msg_elem.querySelector('.title').innerHTML = "Change Password";
                            msg_elem.querySelector('.body-cont').innerHTML = response_data.error_msg;
                            msg_elem.removeAttribute("class");
                        }
                        
                        // enable all the input button
                        document.getElementById("current-passwd-input").disabled = false;
                        document.getElementById("new-passwd-input").disabled = false;
                        document.getElementById("confirm-passwd-input").disabled = false;
                        document.querySelector('.settings-passwd-submit-btn').disabled = false;
                    },

                    // listen to server error
                    function (err_status) {
                        //check if is a timeout or server busy
                        if (err_status == 408 ||
                            err_status == 504 ||
                            err_status == 503) {

                            window.processChangePasswordForm(e);

                        } else {
                            // enable all the input button
                            document.getElementById("current-passwd-input").disabled = false;
                            document.getElementById("new-passwd-input").disabled = false;
                            document.getElementById("confirm-passwd-input").disabled = false;
                            document.querySelector('.settings-passwd-submit-btn').disabled = false;
                        }
                    }
                );
            };

            // utility function to validate change password form
            function validateChangePasswdForm(form) {
                let inputs = form.elements;
                if (/^[ ]*$/.test(inputs['currentpassword'].value)) {
                    // underline input with wrong value
                    inputs['currentpassword'].setAttribute("style", "border-bottom: 1px solid #ff7878;");

                    return false;
                }

                if (/^[ ]*$/.test(inputs['newpassword'].value)) {
                    // underline input with wrong value
                    inputs['newpassword'].setAttribute("style", "border-bottom: 1px solid #ff7878;");

                    return false;

                } else if (user_passwd_strength < 3) {
                    // underline input with wrong value
                    inputs['newpassword'].setAttribute("style", "border-bottom: 1px solid #ff7878;");

                    return false;
                }

                if (/^[ ]*$/.test(inputs['confirmpassword'].value)) {
                    // underline input with wrong value
                    inputs['confirmpassword'].setAttribute("style", "border-bottom: 1px solid #ff7878;");

                    return false;

                } else if (!(inputs['newpassword'].value == inputs['confirmpassword'].value)) {
                    // show error message to user
                    let msg_elem = document.getElementById("msg-win-cont");
                    msg_elem.querySelector('.title').innerHTML = "Confirm Password";
                    msg_elem.querySelector('.body-cont').innerHTML = 
                        "The two password does not match. Check your password and try again.";
                    msg_elem.removeAttribute("class");
                    return false;
                }

                return true;
            }

            // utility function to apply color to class of element
            function applyColorToPasswdStrengthIndicator(elems, limit, class_sel, def_class_sel) {
                for (let i = 0; i < elems.length; i++) {
                    if (i < limit) {
                        elems[i].setAttribute("class", class_sel);

                    } else { // apply default css
                        elems[i].setAttribute("class", def_class_sel);
                    }
                }
            }

            // process events for form input
            function processInputEvents(e) {
                let input_elem = e.target; // get element that fire the event
                let input_name = input_elem.getAttribute("name");

                switch (e.type) {
                    case "keyup":
                        // remove the red underline
                        input_elem.removeAttribute("style");

                        if (input_name == "newpassword") {
                            let txt_indicator = document.getElementById("passwd-txt-indicator");

                            // check if password contain input
                            if (input_elem.value.length > 0) {
                                let pass_strength = zxcvbn(input_elem.value);

                                if (pass_strength.score < 2) { // guessable
                                    applyColorToPasswdStrengthIndicator(passwd_indicator_elems, 1, "indicator str-clr-1", "indicator");
                                    txt_indicator.innerHTML = "Too weak";
                                    user_passwd_strength = 0;

                                } else if (pass_strength.score == 2) { // somewhat guessable
                                    applyColorToPasswdStrengthIndicator(passwd_indicator_elems, 2, "indicator str-clr-2", "indicator");
                                    txt_indicator.innerHTML = "Still weak";
                                    user_passwd_strength = 2;

                                } else if (pass_strength.score == 3) { // safely unguessable
                                    applyColorToPasswdStrengthIndicator(passwd_indicator_elems, 3, "indicator str-clr-3", "indicator");
                                    txt_indicator.innerHTML = "Good";
                                    user_passwd_strength = 3;

                                } else { // very unguessable
                                    applyColorToPasswdStrengthIndicator(passwd_indicator_elems, 4, "indicator str-clr-4", "indicator");
                                    txt_indicator.innerHTML = "Very good";
                                    user_passwd_strength = 3;
                                }

                            } else {
                                applyColorToPasswdStrengthIndicator(passwd_indicator_elems, 0, "indicator", "indicator");
                                txt_indicator.innerHTML = "Strength";
                                user_passwd_strength = 0;
                            }

                        } else if (input_name == "confirmpassword") {
                            // code here
                        }

                        break;

                    default:
                        // you don't suppose to be here
                }
            }

            // attach event listener to input or select element
            function attachEventsToInputs(input_elements) {
                let attach_event = false;

                for (let i = 0; i < input_elements.length; i++) {
                    attach_event = input_elements[i].getAttribute("attachevent") == null ? false : true;
                    // check type of element
                    if (attach_event) {
                        input_elements[i].addEventListener("keyup", processInputEvents, false);
                    }
                }
            }

            // get all the input element and attach events
            attachEventsToInputs(document.getElementsByTagName("input"));

        </script>

<?php

// page footer
require_once 'footer.php';

?>