<?php 

// start session
session_start();

// import all the necessary liberaries
require_once '../includes/utils.php'; // include utility liberary

// generate CSRF token
$csrf_token = generateToken();

// add the CSRF token to session
$_SESSION["csrf_token"] = $csrf_token;

?>

<!DOCTYPE html>
<html lang="en-US">

<head>
    <meta charset="UTF-8">
    <title>CryptoWeb - Registeration</title>
    <link rel="icon" type="image/png" href="favicon1.png" sizes="16x16">
    <link rel="icon" type="image/png" href="favicon2.png" sizes="32x32">
    <link rel="icon" type="image/png" href="favicon3.png" sizes="120x120">
    <meta name="description" content="CryptoWeb registeration page">
    <meta name="keywords" content="sign up, register, register to CryptoWeb, create account with CryptoWeb">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">
    <link type="text/css" href="fonts/css/all.min.css" rel="stylesheet">
    <link type="text/css" rel="stylesheet" href="styles/UdaraX.css">
    <link type="text/css" rel="stylesheet" href="styles/register.css">
    <script type="text/javascript" src="js/zxcvbn.js"></script>
    <script type="text/javascript" src="js/utils.js"></script>
    <script type="text/javascript" src="js/register.js"></script>
</head>

<body>
    <div class="page-cont">
        <div class="signup-cont ux-center-elem shadow ux-rd-corner-2">
            <div class="left-section-cont">
                <div class="bg-pattern-wrapper">
                    <div class="bg-circle-1 ux-f-rd-corner"></div>
                    <div class="bg-circle-2 ux-f-rd-corner"></div>
                    <div class="bg-circle-3 ux-f-rd-corner"></div>
                </div>
                <div class="bg-image"></div>
                <div class="text-layer ux-txt-align-ct">
                    <div class="site-logo-cont">Websitename</div>
                    <div class="headline-cont">
                        <p>Register to enjoy our great packages and services exclusively design for you.</p>
                    </div>
                    <div class="copyright">
                        Copyright &copy; <?php echo date("Y");?>. All Rights Reserved
                    </div>
                </div>
            </div>
            <div class="form-cont">
                <!--error message box-->
                <div id="err-msg-box" class="hide-elem">
                    <div class="pointer"></div>
                    <div class="close-btn">
                        <span class="far fa-times-circle"></span>
                    </div>
                    <div class="msg">This error message box is awesome, doesn't it?</div>
                </div>

                <div id="form-slide-wrapper" class="navi-form-1">
                    <form name="registeration-form" onsubmit="return processRegisterationForm(event)" autocomplete="off"
                        novalidate>
                        <div class="form-1">
                            <h3 class="form-header-title">Personal Information</h3>
                            <div class="robot-input-cont">
                                <input type="text" name="leaveitempty">
                            </div>
                            <div class="input-cont">
                                <div class="firstname-input-wrapper lb-normal-color">
                                    <label for="firstname-input">First Name</label>
                                    <input id="firstname-input" class="hr-line-input" attachevent type="text"
                                        name="firstname" />
                                </div>
                                <div class="lastname-input-wrapper lb-normal-color">
                                    <label for="lastname-input">Last Name</label>
                                    <input id="lastname-input" class="hr-line-input" attachevent type="text"
                                        name="lastname" />
                                </div>
                            </div>
                            <div class="input-cont">
                                <div class="country-input-wrapper lb-normal-color">
                                    <label for="country-input">Country</label>
                                    <select id="country-input" class="hr-line-input" attachevent name="country">
                                        <option selected></option>
                                        <option value="Ghana">Ghana</option>
                                        <option value="Liberia">Liberia</option>
                                        <option value="Niger">Niger</option>
                                        <option value="Nigeria">Nigeria</option>
                                    </select>
                                </div>
                            </div>
                            <div class="input-cont">
                                <div class="countrycode-input-wrapper lb-normal-color">
                                    <label for="countrycode-input">Country Code</label>
                                    <input id="countrycode-input" class="hr-line-input" attachevent type="text"
                                        name="countrycode" />
                                </div>
                                <div class="phonenumber-input-wrapper lb-normal-color">
                                    <label for="phonenumber-input">Phone Number</label>
                                    <input id="phonenumber-input" class="hr-line-input" attachevent type="text"
                                        name="phonenumber" />
                                </div>
                            </div>
                            <div class="input-cont">
                                <div class="email-input-wrapper lb-normal-color">
                                    <label for="email-input">Your Email</label>
                                    <div class="input-icon-cont">
                                        <div class="vt-bars-anim-cont remove-elem">
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
                                        <div class="mark-icon-cont remove-elem">
                                            <svg xmlns="http://www.w3.org/2000/svg" version="1.1" xml:space="preserve"
                                                width="100%" height="100%" viewBox="0 0 8268 11693">
                                                <defs>
                                                    <style type="text/css">
                                                        .fil0 {
                                                            fill: rgb(0, 120, 215);
                                                            transform: matrix(2, 0, 0, 2, -500, 800);
                                                        }
                                                    </style>
                                                </defs>
                                                <g>
                                                    <polygon class="fil0"
                                                        points="820,1946 1970,3103 3769,1282 3439,955 1978,2412 1180,1580 ">
                                                    </polygon>
                                                </g>
                                            </svg>
                                        </div>
                                        <div class="reload-btn-cont remove-elem" onclick="recheckEmailAddress()">
                                            <span class="fas fa-redo-alt"></span>
                                        </div>
                                    </div>
                                    <input id="email-input" class="hr-line-input" attachevent type="email"
                                        name="email" />
                                </div>
                            </div>
                            <div class="input-cont">
                                <div class="birthday-input-wrapper lb-normal-color">
                                    <label for="birthday-input">Date of Birth</label>
                                    <div class="input-icon-cont">
                                        <span class="far fa-calendar-alt"></span>
                                    </div>
                                    <input id="birthday-input" class="hr-line-input" attachevent type="text"
                                        name="birthdate" />
                                </div>
                                <div class="gender-input-wrapper lb-normal-color">
                                    <label for="gender-input">Gender</label>
                                    <select id="gender-input" class="hr-line-input" attachevent name="gender">
                                        <option selected></option>
                                        <option value="Male">Male</option>
                                        <option value="Female">Female</option>
                                        <option value="Others">Others</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="form-2">
                            <h3 class="form-header-title">Your Sign In Details</h3>
                            <div class="input-cont">
                                <div class="username-input-wrapper lb-normal-color">
                                    <label for="username-input">Username</label>
                                    <div class="input-icon-cont">
                                        <div class="vt-bars-anim-cont remove-elem">
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
                                        <div class="mark-icon-cont remove-elem">
                                            <svg xmlns="http://www.w3.org/2000/svg" version="1.1" xml:space="preserve"
                                                width="100%" height="100%" viewBox="0 0 8268 11693">
                                                <defs>
                                                    <style type="text/css">
                                                        .fil0 {
                                                            fill: rgb(0, 120, 215);
                                                            transform: matrix(2, 0, 0, 2, -500, 800);
                                                        }
                                                    </style>
                                                </defs>
                                                <g>
                                                    <polygon class="fil0"
                                                        points="820,1946 1970,3103 3769,1282 3439,955 1978,2412 1180,1580 ">
                                                    </polygon>
                                                </g>
                                            </svg>
                                        </div>
                                        <div class="reload-btn-cont remove-elem" onclick="recheckUsername()">
                                            <span class="fas fa-redo-alt"></span>
                                        </div>
                                    </div>
                                    <input id="username-input" class="hr-line-input" attachevent type="text"
                                        name="username">
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
                                    <input id="password-input" class="hr-line-input" attachevent type="password"
                                        name="password">
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
                                        <span>Strength</span>
                                    </div>
                                </div>
                            </div>
                            <div class="input-cont">
                                <div class="confirmpasswd-input-wrapper lb-normal-color">
                                    <label for="confirmpasswd-input">Confirm Password</label>
                                    <div class="input-icon-cont">
                                        <div class="mark-icon-cont remove-elem">
                                            <svg xmlns="http://www.w3.org/2000/svg" version="1.1" xml:space="preserve"
                                                width="100%" height="100%" viewBox="0 0 8268 11693">
                                                <defs>
                                                    <style type="text/css">
                                                        .fil0 {
                                                            fill: rgb(0, 120, 215);
                                                            transform: matrix(2, 0, 0, 2, -500, 800);
                                                        }
                                                    </style>
                                                </defs>
                                                <g>
                                                    <polygon class="fil0"
                                                        points="820,1946 1970,3103 3769,1282 3439,955 1978,2412 1180,1580 ">
                                                    </polygon>
                                                </g>
                                            </svg>
                                        </div>
                                    </div>
                                    <input id="confirmpasswd-input" class="hr-line-input" attachevent="" type="password"
                                        name="confirmpasswd">
                                </div>
                            </div>
                        </div>
                        <div class="form-3">
                            <h3 class="form-header-title">Personal Identification</h3>
                            <div class="input-cont">
                                <div class="referralid-input-wrapper lb-normal-color">
                                    <label for="referralid-input">Referral ID (Optional)</label>
                                    <div class="input-icon-cont">
                                        <div class="vt-bars-anim-cont remove-elem">
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
                                        <div class="mark-icon-cont remove-elem">
                                            <svg xmlns="http://www.w3.org/2000/svg" version="1.1" xml:space="preserve"
                                                width="100%" height="100%" viewBox="0 0 8268 11693">
                                                <defs>
                                                    <style type="text/css">
                                                        .fil0 {
                                                            fill: rgb(0, 120, 215);
                                                            transform: matrix(2, 0, 0, 2, -500, 800);
                                                        }
                                                    </style>
                                                </defs>
                                                <g>
                                                    <polygon class="fil0"
                                                        points="820,1946 1970,3103 3769,1282 3439,955 1978,2412 1180,1580 ">
                                                    </polygon>
                                                </g>
                                            </svg>
                                        </div>
                                        <div class="reload-btn-cont remove-elem" onclick="recheckReferralID()">
                                            <span class="fas fa-redo-alt"></span>
                                        </div>
                                    </div>
                                    <input id="referralid-input" class="hr-line-input" attachevent="" type="text"
                                        name="referralid">
                                </div>
                            </div>
                            <div class="input-cont">
                                <h5 class="f-upload-h">
                                    Upload Your Driver's License Or International Passport. It must be in these scanned
                                    formats: JPEG, PNG, and GIF. File size must not be greater than 4MB (Megabyte).
                                </h5>
                                <div class="f-upload-input-wrapper">
                                    <input id="f-upload-input" type="file" name="file"
                                        accept="image/png, image/jpeg, image/gif">
                                    <label for="f-upload-input">Browse File</label>
                                    <div id="f-upload-msg" class="hide-elem">
                                        <div class="bar"></div>
                                        <div class="msg"></div>
                                    </div>
                                </div>
                            </div>
                            <div class="input-cont">
                                <input id="acceptterms-input" type="checkbox" name="acceptterms" value="0">
                                <div class="acceppterms-cont">
                                    <div class="item-1">
                                        <label for="acceptterms-input">
                                            <img src="images/icons/check_button_1.png" />
                                        </label>
                                    </div>
                                    <div class="item-2">
                                        <p>I do accept the <a class="terms-link" href="#">Terms and Conditions</a> of
                                            Websitename</p>
                                    </div>
                                </div>
                                <div class="reg-btn-cont">
                                    <input class="reg-btn" type="submit" value="Register">
                                </div>
                            </div>
                            <div class="csrf-input-cont">
                                <input type="text" name="csrf_token" value="<?php echo $csrf_token; ?>">
                            </div>
                        </div>
                    </form>
                </div>
                <div class="form-navigation-cont">
                    <div class="form-navi-btn-cont">
                        <button class="form-navi-btn hr-pos ux-f-rd-corner" onclick="navigateForm(this)">
                            <span class="fas fa-arrow-right"></span>
                        </button>
                    </div>
                    <div class="form-progress-bar-cont">
                        <div class="progress-bar-wrapper">
                            <div class="progress-bar-bg">
                                <div class="progress-bar stage-1"></div>
                            </div>
                        </div>
                        <div class="progress-number">1 / 3</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>

</html>