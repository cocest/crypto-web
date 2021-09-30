<?php 

// start session
session_start();

// import all the necessary liberaries
require_once '../includes/config.php';
require_once '../includes/utils.php'; // include utility liberary

// generate CSRF token
$csrf_token = randomText('hexdec', 16);

// add the CSRF token to session
$_SESSION["csrf_token"] = $csrf_token;

// mysql configuration
$db = $config['db']['mysql'];
        
// enable mysql exception
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

// fetch result for page rendering
$data_for_page_rendering = [
    'select_countries' => null
];

try {
    // connect to database
    $conn = new mysqli($db['host'], $db['username'], $db['password'], $db['dbname']);

    //check connection
    if ($conn->connect_error) {
        throw new mysqli_sql_exception('Database connection failed: '.$conn->connect_error);
    }

    // fetch all the countries from database
    $query = 'SELECT countryName AS country FROM countries';
    $stmt = $conn->prepare($query); // prepare statement
    $stmt->execute();
    $result = $stmt->get_result();
    $countries = $result->fetch_all(MYSQLI_ASSOC);

    if ($countries) {
        $data_for_page_rendering['select_countries'] = $countries;
    } else {
        $data_for_page_rendering['select_countries'] = [];
    }

    // close connection to database
    $stmt->close();
    $conn->close();

} catch (mysqli_sql_exception $e) {
    // log the error to a file
    error_log('Mysql error: '.$e->getMessage().PHP_EOL, 3, CUSTOM_ERR_DIR.'custom_errors.log');
    
} catch (Exception $e) { // catch other exception
    // log the error to a file
    error_log('Caught exception: '.$e->getMessage().PHP_EOL, 3, CUSTOM_ERR_DIR.'custom_errors.log');
}

?>

<!DOCTYPE html>
<html lang="en-US">

<head>
    <meta charset="UTF-8">
    <title>The Citadel Capital Partners - Registration</title>
    <link rel="icon" type="image/png" href="./images/icons/favicon1.png" sizes="16x16">
    <link rel="icon" type="image/png" href="./images/icons/favicon2.png" sizes="32x32">
    <link rel="icon" type="image/png" href="./images/icons/favicon3.png" sizes="120x120">
    <meta name="description" content="thecitadelcapital registration page">
    <meta name="keywords" content="sign up, register, register to thecitadelcapital, create account with thecitadelcapital">
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
        <div class="header-site-logo-cont">
            <img src="./images/icons/w_citadel_capital_logo.png" alt="thecitadelcapital" />
        </div>
        <div class="signup-cont ux-center-elem ux-rd-corner-2">
            <div class="left-section-cont">
                <div class="bg-pattern-wrapper">
                    <div class="bg-circle-1 ux-f-rd-corner"></div>
                    <div class="bg-circle-2 ux-f-rd-corner"></div>
                    <div class="bg-circle-3 ux-f-rd-corner"></div>
                </div>
                <div class="text-layer ux-txt-align-ct">
                    <div class="site-logo-cont">
                        <img src="./images/icons/w_citadel_capital_logo.png" alt="thecitadelcapital" />
                    </div>
                    <div class="headline-cont">
                        <p>
                            Become a limited partner today and enjoy our exclusive services 
                            tailored to suit your financial goals!
                        </p>
                    </div>
                    <div class="copyright">
                        &copy; <?php echo date("Y");?> Thecitadelcapital. All Rights Reserved
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
                    <div class="msg"></div>
                </div>

                <form name="registeration-form" onsubmit="return processRegisterationForm(event)" autocomplete="off"
                    novalidate>
                    <h3 class="form-header-title">Registration Form</h3>
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
                                <?php 
                                    foreach ($data_for_page_rendering['select_countries'] as $value) {
                                        echo '<option value="'.$value['country'].'">'.$value['country'].'</option>';
                                    }
                                ?>
                            </select>
                        </div>
                    </div>
                    <div class="input-cont">
                        <div class="birthday-input-wrapper lb-normal-color">
                            <label for="birthday-input">Date of Birth</label>
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
                                                    fill: white;
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
                        <input id="acceptterms-input" type="checkbox" name="acceptterms" value="0">
                        <div class="acceppterms-cont">
                            <div class="item-1">
                                <label for="acceptterms-input">
                                    <img src="images/icons/check-button-1.png" />
                                </label>
                            </div>
                            <div class="item-2">
                                <p>I do accept the <a class="terms-link" href="<?php echo BASE_URL . 'terms_and_condition.html'; ?>">Terms and Conditions</a> of Thecitadelcapital</p>
                            </div>
                        </div>
                        <div class="reg-btn-cont">
                            <input class="reg-btn" type="submit" value="Register">
                        </div>
                    </div>
                    <div class="csrf-input-cont">
                        <input type="text" name="csrf_token" value="<?php echo $csrf_token; ?>">
                    </div>
                </form>
            </div>
        </div>
    </div>
    <div class="footer-copyright">
        &copy; <?php echo date("Y");?> Thecitadelcapital. All Rights Reserved
    </div>
</body>

</html>
