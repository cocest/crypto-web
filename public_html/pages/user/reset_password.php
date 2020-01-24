<?php 

// start session
session_start();

// import all the necessary liberaries
require_once '../../includes/utils.php'; // include utility liberary

// generate CSRF token
$csrf_token = generateToken();

// add the CSRF token to session
$_SESSION["csrf_token"] = $csrf_token;

// switch between verifying email and entering a new password
$switch_index = 0;

if (isset($_SESSION['reset_password']) && $_SESSION['reset_password'] == true) {
    if (time() < $_SESSION['reset_pswd_time']) {
        $switch_index = 1;
    }
}

?>

<!DOCTYPE html>
<html lang="en-US">

<head>
    <meta charset="UTF-8">
    <title>Thecitadelcapital - Reset Password</title>
    <link rel="icon" type="image/png" href="../images/icons/favicon1.png" sizes="16x16">
    <link rel="icon" type="image/png" href="../images/icons/favicon2.png" sizes="32x32">
    <link rel="icon" type="image/png" href="../images/icons/favicon3.png" sizes="120x120">
    <meta name="description" content="Reset your sign in password.">
    <meta name="keywords" content="reset password, recover account, forgot password">
    <meta name="viewport" content="width=device-width, initial-scale=0.9, maximum-scale=0.9">
    <link type="text/css" href="../fonts/css/all.min.css" rel="stylesheet">
    <link type="text/css" rel="stylesheet" href="../styles/UdaraX.css">
    <link type="text/css" rel="stylesheet" href="../styles/reset_password.css">
    <script type="text/javascript" src="../js/utils.js"></script>
    <script type="text/javascript" src="../js/zxcvbn.js"></script>
</head>

<body>
    <div class="page-top-menu-cont absolute">
        <!--Header menu container-->
        <div class="page-cont-max-width">
            <nav>
                <div class="menu-links-cont">
                    <ul>
                        <li><a href="../index.html">Home</a></li>
                        <li><a href="./login.html">Sign In</a></li>
                    </ul>
                </div>
            </nav>
        </div>
    </div>
    <div class="recover-account-page">
        <div class="site-logo-cont">
            <img src="../images/icons/citadel_capital_logo.png" alt="thecitadelcapital">
        </div>
        <h2 class="recover-account-header">Reset Password</h2>
        <?php 
            if ($switch_index == 0) {
        ?>
        <div class="recover-form-cont">
            <form name="verifyemail-form" onsubmit="return processVerifyEmailForm(event)" autocomplete="off" novalidate>
                <div class="email-input-cont">
                    <label for="email-input">Email</label>
                    <input id="email-input" class="hr-line-input" attachevent type="text" name="email" spellcheck="false">
                </div>
                <!--error message box-->
                <div id="err-msg-box" class="hide-elem">
                    <div class="pointer"></div>
                    <div class="close-btn">
                        <span class="far fa-times-circle"></span>
                    </div>
                    <div class="msg"></div>
                </div>
                <div class="submit-btn-cont">
                    <input id="submit-btn" type="submit" value="Reset Password" />
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
            </form>
        </div>
        <?php 
            } else {
        ?>
        <div class="recover-form-cont">
            <form name="changepswd-form" onsubmit="return processNewPasswordForm(event)" autocomplete="off" novalidate>
                <div class="password-input-cont">
                    <label for="password-input">New Password</label>
                    <input id="password-input" class="hr-line-input" attachevent type="password" name="password" spellcheck="false">
                    <div id="settings-show-passwd-cont" class="hide" onclick="showNewUserPassword()">
                        <i class="fas fa-eye"></i>
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
                <div class="confirm-password-input-cont">
                    <label for="confirm-password-input">Confirm Password</label>
                    <input id="confirm-password-input" class="hr-line-input" attachevent type="password" name="confirmpassword">
                    <div id="password-match-icon-cont" class="remove-elem">
                        <i class="fas fa-check"></i>
                    </div>
                </div>
                <div class="cpswd-submit-btn-cont">
                    <div class="cpswd-submit-btn-wrapper">
                        <input id="submit-btn" type="submit" value="Apply">
                    </div>
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
            </form>
        </div>
        <?php 
            }
        ?>
    </div>
    <div class="recover-account-footer">
        &copy; <?php echo date("Y");?> Thecitadelcapital. All Rights Reserved
    </div>
    <script>
        // variables here
        let submit_locked = false;
        let error_msg_active = false;
        let user_passwd_strength = 0;
        let is_passwd_shown = false;
        let passwd_indicator_elems = document.querySelectorAll(".passwd-strength-indicator-cont .indicator");
        let is_passwd_confirmed = false;

        // show password and hide after some seconds
        window.showNewUserPassword = function () {
            let passwd_input = document.getElementById("password-input");

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

        // fit page footer
        function fitPageFooter() {
            let elem = document.querySelector('.recover-account-footer');
            elem.removeAttribute("style");

            if (window.innerHeight < 740) {
                let elem = document.querySelector('.recover-account-footer');
                elem.setAttribute("style", "position: relative; margin-top: 150px;");
            }
        }

        // process events for form input
        function processInputEvents(e) {
            let input_elem = e.target; // get element that fire the event
            let input_name = input_elem.getAttribute("name");

            switch (e.type) {
                case "focus":
                    // push the input label up
                    label_elem = input_elem.parentElement.firstElementChild;
                    label_elem.setAttribute("class", "push-up");

                    break;

                case "blur":
                    if (input_elem.value.length < 1) {
                        // push the input label down
                        label_elem = input_elem.parentElement.firstElementChild;
                        label_elem.removeAttribute("class");
                    }

                    break;

                case "keyup":
                    // remove the red underline
                    input_elem.setAttribute("class", "hr-line-input");

                    if (input_name == "password") {
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
                                user_passwd_strength = 4;
                            }

                        } else {
                            applyColorToPasswdStrengthIndicator(passwd_indicator_elems, 0, "indicator", "indicator");
                            txt_indicator.innerHTML = "Strength";
                            user_passwd_strength = 0;
                        }

                        // check if the two password match
                        let confirm_passwd_input = document.getElementById("confirm-password-input");
                        let mark_icon = document.getElementById("password-match-icon-cont");

                        if (input_elem.value == confirm_passwd_input.value && confirm_passwd_input.value.length > 0) {
                            mark_icon.removeAttribute("class");
                            is_passwd_confirmed = true;

                        } else {
                            mark_icon.setAttribute("class", "remove-elem");
                            is_passwd_confirmed = false;
                        }

                    } else if (input_name == "confirmpassword") {
                        // check if the two password match
                        let passwd_input = document.getElementById("password-input");
                        let mark_icon = document.getElementById("password-match-icon-cont");

                        if (input_elem.value == passwd_input.value && passwd_input.value.length > 0) {
                            mark_icon.removeAttribute("class");
                            is_passwd_confirmed = true;

                        } else {
                            mark_icon.setAttribute("class", "remove-elem");
                            is_passwd_confirmed = false;
                        }
                    }

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
                    input_elements[i].addEventListener("focus", processInputEvents, false);
                    input_elements[i].addEventListener("blur", processInputEvents, false);
                    input_elements[i].addEventListener("keyup", processInputEvents, false);
                }
            }
        }

        // utility function to check if required input is left empty
        function validateRequiredInput(form, input_names) {
            for (let i = 0; i < input_names.length; i++) {
                let input = form.elements[input_names[i]];

                if (/^[ ]*$/.test(input.value)) {
                    // underline the input
                    input.setAttribute("class", "err-hr-line-input");

                    return true;
                }

                if (input_names[i] == "email"  && !/^(([^<>()\[\]\\.,;:\s@"]+(\.[^<>()\[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/.test(input.value)) {
                    let err_msg = document.getElementById("err-msg-box");
                    err_msg.querySelector('.msg').innerHTML = "Email is not acceptable.";
                    err_msg.removeAttribute("class");
                    error_msg_active = true;

                    return true;
                }

                if (input_names[i] == "password" && user_passwd_strength < 3) {
                    // underline the input
                    input.setAttribute("class", "err-hr-line-input");

                    return true;
                }

                if (input_names[i] == "confirmpassword"  && !is_passwd_confirmed) {
                    // underline the input
                    input.setAttribute("class", "err-hr-line-input");

                    return true;
                }
            }

            return false;
        }

        // process form and submit to server
        window.processVerifyEmailForm = function (e) {
            e.preventDefault(); // prevent form from submitting

            // server is still processing request
            if (submit_locked) {
                return false;
            }

            let recover_form = document.forms["verifyemail-form"];

            // check if any input is left empty or invalid
            if (!validateRequiredInput(recover_form, ["email"])) {
                // disable inputs
                submit_locked = true;
                recover_form.elements["email"].disabled = true;
                document.getElementById("submit-btn").disabled = true;

                // show wait animation
                document.querySelector(".vt-bars-anim-cont").setAttribute("class", "vt-bars-anim-cont");

                let req_url = '../request';
                let form_data = 'req=reset_password&email_address=' + recover_form.elements["email"].value; // request query

                // send request to server
                window.ajaxRequest(
                    req_url,
                    form_data,
                    { contentType: "application/x-www-form-urlencoded" },

                    // listen to response from the server
                    function (response) {
                        // enable inputs
                        submit_locked = false;
                        recover_form.elements["email"].disabled = false;
                        document.getElementById("submit-btn").disabled = false;

                        // hide wait animation
                        document.querySelector(".vt-bars-anim-cont").setAttribute("class", "vt-bars-anim-cont hide-elem");

                        // convert response to object
                        let response_data = JSON.parse(response);

                        if (response_data.success) {
                            alert("We have sent you an email, follow the instruction to reset your password.");

                        } else if (response_data.error_code == "no_email_address") {
                            let err_msg = document.getElementById("err-msg-box");
                            err_msg.querySelector('.msg').innerHTML = "We couldn't found a match.";
                            err_msg.removeAttribute("class");
                            error_msg_active = true;

                        } else { // invalid username or password
                            alert("Account recovery failed.");
                        }
                    },

                    // listen to server error
                    function (err_status, msg) {
                        //check if is a timeout or server busy
                        if (err_status == 408 ||
                            err_status == 504 ||
                            err_status == 503) {

                            window.processVerifyEmailForm(e);

                        } else {
                            // enable inputs
                            submit_locked = false;
                            recover_form.elements["email"].disabled = false;
                            document.getElementById("submit-btn").disabled = false;

                            // hide wait animation
                            document.querySelector(".vt-bars-anim-cont").setAttribute("class", "vt-bars-anim-cont hide-elem");
                        }
                    }
                );
            }
        }

        // send user's new password to server
        window.processNewPasswordForm = function (e) {
            e.preventDefault(); // prevent form from submitting

            // server is still processing request
            if (submit_locked) {
                return false;
            }

            let new_password_form = document.forms["changepswd-form"];

            // check if any input is left empty or invalid
            if (!validateRequiredInput(new_password_form, ["password", "confirmpassword"])) {
                let req_url = '../reset_password';
                let form_data = new FormData(new_password_form);

                // disable inputs
                submit_locked = true;
                new_password_form.elements["password"].disabled = true;
                new_password_form.elements["confirmpassword"].disabled = true;
                document.getElementById("submit-btn").disabled = true;

                // show wait animation
                document.querySelector(".vt-bars-anim-cont").setAttribute("class", "vt-bars-anim-cont");

                // send request to server
                window.ajaxRequest(
                    req_url,
                    form_data,
                    { contentType: false },

                    // listen to response from the server
                    function (response) {
                        let response_data = JSON.parse(response);

                        // enable inputs
                        submit_locked = false;
                        new_password_form.elements["password"].disabled = false;
                        new_password_form.elements["confirmpassword"].disabled = false;
                        document.getElementById("submit-btn").disabled = false;

                        // hide wait animation
                        document.querySelector(".vt-bars-anim-cont").setAttribute("class", "vt-bars-anim-cont hide-elem");

                        // redirect user to their page
                        window.location.replace(response_data.redirect_url);
                    },

                    // listen to server error
                    function (err_status, msg) {
                        //check if is a timeout or server busy
                        if (err_status == 408 ||
                            err_status == 504 ||
                            err_status == 503) {

                            window.processNewPasswordForm(e);

                        } else {
                            // enable inputs
                            submit_locked = false;
                            new_password_form.elements["password"].disabled = false;
                            new_password_form.elements["confirmpassword"].disabled = false;
                            document.getElementById("submit-btn").disabled = false;

                            // hide wait animation
                            document.querySelector(".vt-bars-anim-cont").setAttribute("class", "vt-bars-anim-cont hide-elem");
                        }
                    }
                );
            }
        };

        // get all the input element to attach events
        let inputs = document.getElementsByTagName("input");
        attachEventsToInputs(inputs);

        // attach event to page
        document.querySelector(".recover-account-page").addEventListener("click", function (e) {
            // close error message
            if (error_msg_active) {
                error_msg_active = false;
                document.getElementById("err-msg-box").setAttribute("class", "hide-elem");
            }

        }, false);

        // fit page footer after initialisation
        fitPageFooter();

        // listen to page resize event
        window.onresize = function (e) {
            fitPageFooter();
        };

    </script>
</body>

</html>