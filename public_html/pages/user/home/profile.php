<?php 

// start session
session_start();

// import all the necessary liberaries
require_once '../../../includes/config.php';
require_once '../../../includes/utils.php'; // include utility liberary

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

// fetch result for page rendering
$data_for_page_rendering = null;

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

    // get user's testimonies
    $query = 'SELECT * FROM users WHERE id = ? LIMIT 1';
    $stmt = $conn->prepare($query); // prepare statement
    $stmt->bind_param('i', $_SESSION['user_id']);
    $stmt->execute();
    $result = $stmt->get_result();
    $data_for_page_rendering = $result->fetch_assoc();
    $stmt->close();

    // reformat date for display and editting
    $splitted_birth_date = explode('-', $data_for_page_rendering['birthdate']);
    $birth_date_1 = $splitted_birth_date[1].'/'.$splitted_birth_date[2].'/'.$splitted_birth_date[0];

    $date_obj   = DateTime::createFromFormat('!m', $splitted_birth_date[1]);
    $month_name = $date_obj->format('M'); // Mar
    $birth_date_2 = $month_name.' '.$splitted_birth_date[2].', '.$splitted_birth_date[0];

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
    'profile' => true,
    'settings' => false
];

// assemble all the part of the page
require_once 'header.php';
require_once 'page_left_menu.php';

?>

    <div class="page-content-cont">
        <h1 class="page-title-hd">Profile</h1>
        <div class="profile-sec-1">
            <div class="account-profile-cont">
                <div class="profile-pic-cont">
                    <img class="default-pic" 
                         src="<?php echo empty($data_for_page_rendering['mediumProfilePictureURL']) ? '../../images/icons/profile_pic2.png' : '../../uploads/users/profile/'.$data_for_page_rendering['mediumProfilePictureURL']; ?>" />
                    <div class="upload-profile-pic" title="Click to upload.">
                        <span class="fas fa-camera"></span>
                    </div>
                </div>
                <div class="account-details-cont">
                    <h3 class="name"><?php echo $data_for_page_rendering['lastName'].' '.$data_for_page_rendering['firstName']; ?></h3>
                    <div class="username">
                        <span>Username</span><?php echo $data_for_page_rendering['userName']; ?>
                    </div>
                    <div class="ref-id">
                        <span>Referral ID</span><?php echo $data_for_page_rendering['referralID']; ?>
                    </div>
                    <div class="location">
                        <span class="fas fa-map-marker-alt"></span><?php echo $data_for_page_rendering['country']; ?>
                    </div>
                </div>
            </div>
        </div>
        <div class="profile-sec-2">
            <div class="header-title-cont">
                <h3 class="header-title">Personal Information</h3>
                <div class="edit-sec-btn" title="Click to edit." onclick="editPersonalInfo()">
                    <span class="fas fa-pen"></span>
                </div>
            </div>
            <form name="personal-info-form" onsubmit="return processPersonalInfoForm(event)" autocomplete="off" novalidate>
                <div class="edit-profile-input-cont">
                    <label for="profile-first-name-input">First Name</label>
                    <input id="profile-first-name-input" type="text" name="firstname" value="<?php echo $data_for_page_rendering['firstName']; ?>" attachevent disabled />
                </div>
                <div class="edit-profile-input-cont">
                    <label for="profile-last-name-input">Last Name</label>
                    <input id="profile-last-name-input" type="text" name="lastname" value="<?php echo $data_for_page_rendering['lastName']; ?>" attachevent disabled />
                </div>
                <div class="edit-profile-input-cont">
                    <label for="profile-birthdate-input">Birthdate</label>
                    <input id="profile-birthdate-input" data="<?php echo $birth_date_1; ?>" type="text" name="birthdate" value="<?php echo $birth_date_2; ?>" placeholder="mm/dd/yyyy" attachevent disabled />
                </div>
                <div class="edit-profile-input-cont">
                    <label for="profile-gender-input">Gender</label>
                    <select id="profile-gender-input" name="gender" disabled>
                        <option <?php echo $data_for_page_rendering['gender'] == 'Male' ? 'selected' : ''; ?> value="Male">Male</option>
                        <option <?php echo $data_for_page_rendering['gender'] == 'Female' ? 'selected' : ''; ?> value="Female">Female</option>
                        <option <?php echo $data_for_page_rendering['gender'] == 'Others' ? 'selected' : ''; ?> value="Others">Others</option>
                    </select>
                    <div class="cover-select-icon"></div>
                </div>
                <div class="personal-info-save-btn-cont">
                    <div id="personal-info-save-btn-wrapper" class="remove-elem">
                        <div class="save-btn-cont">
                            <button id="personal-profile-ok-btn" type="submit">OK</button>
                        </div>
                        <div class="cancel-btn-cont">
                            <button id="personal-profile-cancel-btn" type="button" onclick="cancelEdit('personal_info')">CANCEL</button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
        <div class="profile-sec-3">
            <div class="header-title-cont">
                <h3 class="header-title">Contact Information</h3>
                <div class="edit-sec-btn" title="Click to edit." onclick="editContactInfo()">
                    <span class="fas fa-pen"></span>
                </div>
            </div>
            <form name="contact-info-form" onsubmit="return processContactInfoForm(event)" autocomplete="off" novalidate>
                <div class="edit-profile-input-cont">
                    <label for="profile-email-input">Email</label>
                    <input id="profile-email-input" type="text" name="email" value="<?php echo $data_for_page_rendering['email']; ?>" attachevent disabled />
                </div>
                <div class="edit-profile-input-cont">
                    <label for="profile-countrycode-input">Country Code</label>
                    <input id="profile-countrycode-input" type="text" name="countrycode" value="<?php echo $data_for_page_rendering['phoneCountryCode']; ?>" attachevent disabled />
                </div>
                <div class="edit-profile-input-cont">
                    <label for="profile-phone-input">Phone</label>
                    <input id="profile-phone-input" type="text" name="phonenumber" value="<?php echo $data_for_page_rendering['phoneNumber']; ?>" attachevent disabled />
                </div>
                <div class="contact-info-save-btn-cont">
                    <div id="contact-info-save-btn-wrapper" class="remove-elem">
                        <div class="save-btn-cont">
                            <button id="contact-profile-ok-btn" type="submit">OK</button>
                        </div>
                        <div class="cancel-btn-cont">
                            <button id="contact-profile-cancel-btn" type="button" onclick="cancelEdit('contact_info')">CANCEL</button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
        <script>
            // variables here
            let personal_info = [];
            let contact_info = [];
            let personal_edit_enabled = false;
            let contact_edit_enabled = false;
            let invalid_input = null;
            let month_name = new Map([
                ["01", "Jan"],
                ["02", "Feb"],
                ["03", "Mar"],
                ["04", "Apr"],
                ["05", "May"],
                ["06", "Jun"],
                ["07", "Jul"],
                ["08", "Aug"],
                ["09", "Sep"],
                ["10", "Oct"],
                ["11", "Nov"],
                ["12", "Dec"]
            ]);

            // edit user's personal info
            window.editPersonalInfo = function (e) {
                if (!personal_edit_enabled) {
                    personal_edit_enabled = true;
                } else {
                    return;
                }

                let form = document.forms["personal-info-form"];
                let input_name = [
                    'firstname',
                    'lastname',
                    'birthdate',
                    'gender'
                ];

                // cache current data and enable the input
                for (let i = 0; i < input_name.length; i++) {
                    if (input_name[i] == "gender") {
                        personal_info[input_name[i]] = form.elements[input_name[i]].selectedIndex;
                    } else {
                        personal_info[input_name[i]] = form.elements[input_name[i]].value;
                    }

                    if (input_name[i] == "birthdate") {
                        form.elements[input_name[i]].value = form.elements[input_name[i]].getAttribute("data");
                    }

                    form.elements[input_name[i]].disabled = false;
                }

                // show cancel and ok button
                document.getElementById("personal-info-save-btn-wrapper").removeAttribute("class");
            };

            // edit user's contact info
            window.editContactInfo = function (e) {
                if (!contact_edit_enabled) {
                    contact_edit_enabled = true;
                } else {
                    return;
                }

                let form = document.forms["contact-info-form"];
                let input_name = [
                    'email',
                    'countrycode',
                    'phonenumber'
                ];

                // cache current data and enable the input
                for (let i = 0; i < input_name.length; i++) {
                    personal_info[input_name[i]] = form.elements[input_name[i]].value;
                    form.elements[input_name[i]].disabled = false;
                }

                // show cancel and ok button
                document.getElementById("contact-info-save-btn-wrapper").removeAttribute("class");
            };

            // cancel editing of the profile information
            window.cancelEdit = function (edit) {
                if (edit == "personal_info") {
                    personal_edit_enabled = false;

                    let form = document.forms["personal-info-form"];
                    let input_name = [
                        'firstname',
                        'lastname',
                        'birthdate',
                        'gender'
                    ];

                    for (let i = 0; i < input_name.length; i++) {
                        if (input_name[i] == "gender") {
                            form.elements[input_name[i]].selectedIndex = personal_info[input_name[i]];
                        } else {
                            form.elements[input_name[i]].value = personal_info[input_name[i]];
                        }

                        form.elements[input_name[i]].disabled = true; // enable input
                    }

                    // remove underline in the input
                    if (invalid_input) {
                        invalid_input.removeAttribute("style");
                        invalid_input = null;
                    }

                    // hide cancel and ok button
                    document.getElementById("personal-info-save-btn-wrapper").setAttribute("class", "remove-elem");

                } else { // contact information
                    contact_edit_enabled = false;

                    let form = document.forms["contact-info-form"];
                    let input_name = [
                        'email',
                        'countrycode',
                        'phonenumber'
                    ];

                    for (let i = 0; i < input_name.length; i++) {
                        form.elements[input_name[i]].value = personal_info[input_name[i]]
                        form.elements[input_name[i]].disabled = true; // enable input
                    }

                    // remove underline in the input
                    if (invalid_input) {
                        invalid_input.removeAttribute("style");
                        invalid_input = null;
                    }

                    // hide cancel and ok button
                    document.getElementById("contact-info-save-btn-wrapper").setAttribute("class", "remove-elem");
                }
            };

            window.processPersonalInfoForm = function (e) {
                e.preventDefault(); // prevent default behaviour

                // get user filled form
                let form = document.forms["personal-info-form"];

                let req_url = '../../update_personal_data';
                let reg_form = new FormData(form);

                // check if any input is left empty or contain invalid data
                if (requiredInputLeftEmptyOrInvalid(
                    form,
                    [
                        {name: 'firstname', regex: /^([a-zA-Z]|[a-zA-Z]+[']?[a-zA-Z]+)$/},
                        {name: 'lastname', regex: /^([a-zA-Z]|[a-zA-Z]+[']?[a-zA-Z]+)$/},
                        {name: 'birthdate', regex: /^(0[1-9]|1[0-2])\/(0[1-9]|[1-2][0-9]|3[0-1])\/[1-9]\d{3}$/},
                        {name: 'gender', regex: /^(male|female|others)$/i}
                    ]
                )) {
                    return false;
                }

                // disable cancel and ok button
                document.getElementById("personal-profile-cancel-btn").disabled = true;
                document.getElementById("personal-profile-ok-btn").disabled = true;

                // send request to server
                window.ajaxRequest(
                    req_url,
                    reg_form,
                    { contentType: false },
                    
                    // listen to response from the server
                    function (response) {
                        personal_edit_enabled = false;

                        // enable cancel and ok button
                        document.getElementById("personal-profile-cancel-btn").disabled = false;
                        document.getElementById("personal-profile-ok-btn").disabled = false;
                        
                        // disable input
                        let input_name = [
                            'firstname',
                            'lastname',
                            'birthdate',
                            'gender'
                        ];

                        for (let i = 0; i < input_name.length; i++) {
                            if (input_name[i] == "birthdate") {
                                let splitted_date = form.elements[input_name[i]].value.split('/')
                                form.elements[input_name[i]].setAttribute("data", form.elements[input_name[i]].value);
                                form.elements[input_name[i]].value = month_name.get(splitted_date[0]) + " " + splitted_date[1] + ", " + splitted_date[2];
                            }

                            form.elements[input_name[i]].disabled = true;
                        }

                        // hide cancel and ok button
                        document.getElementById("personal-info-save-btn-wrapper").setAttribute("class", "remove-elem");
                    },

                    // listen to server error
                    function (err_status) {
                        //check if is a timeout or server busy
                        if (err_status == 408 ||
                            err_status == 504 ||
                            err_status == 503) {

                            window.processPersonalInfoForm(e);

                        } else {
                            // enable cancel and ok button
                            document.getElementById("personal-profile-cancel-btn").disabled = false;
                            document.getElementById("personal-profile-ok-btn").disabled = false;
                        }
                    }
                );
            };

            window.processContactInfoForm = function (e) {
                e.preventDefault(); // prevent default behaviour

                // get user filled form
                let form = document.forms["contact-info-form"];

                let req_url = '../../update_contact_data';
                let reg_form = new FormData(form);

                // check if any input is left empty or contain invalid data
                if (requiredInputLeftEmptyOrInvalid(
                    form,
                    [
                        {name: 'email', regex: /^(([^<>()\[\]\\.,;:\s@"]+(\.[^<>()\[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/},
                        {name: 'countrycode', regex: /^\+\d+$/},
                        {name: 'phonenumber', regex: /^\d+$/}
                    ]
                )) {
                    return false;
                }

                // disable cancel and ok button
                document.getElementById("contact-profile-cancel-btn").disabled = true;
                document.getElementById("contact-profile-ok-btn").disabled = true;

                // send request to server
                window.ajaxRequest(
                    req_url,
                    reg_form,
                    { contentType: false },
                    
                    // listen to response from the server
                    function (response) {
                        contact_edit_enabled = false;

                        // enable cancel and ok button
                        document.getElementById("contact-profile-cancel-btn").disabled = false;
                        document.getElementById("contact-profile-ok-btn").disabled = false;
                        
                        // disable input
                        let input_name = [
                            'email',
                            'countrycode',
                            'phonenumber'
                        ];

                        for (let i = 0; i < input_name.length; i++) {
                            form.elements[input_name[i]].disabled = true;
                        }

                        // hide cancel and ok button
                        document.getElementById("contact-info-save-btn-wrapper").setAttribute("class", "remove-elem");
                    },

                    // listen to server error
                    function (err_status) {
                        //check if is a timeout or server busy
                        if (err_status == 408 ||
                            err_status == 504 ||
                            err_status == 503) {

                            window.processContactInfoForm(e);

                        } else {
                            // enable cancel and ok button
                            document.getElementById("contact-profile-cancel-btn").disabled = false;
                            document.getElementById("contact-profile-ok-btn").disabled = false;
                        }
                    }
                );
            };

            // utility function to validate user's input
            function requiredInputLeftEmptyOrInvalid(form, input_name_and_regex) {
                for (let i = 0; i < input_name_and_regex.length; i++) {
                    let input = form.elements[input_name_and_regex[i].name];

                    // validate input
                    if (!input_name_and_regex[i].regex.test(input.value)) {
                        // underline input with wrong input
                        input.setAttribute("style", "border-bottom: 1px solid #ff7878;");
                        invalid_input = input;

                        return true;
                    }
                }

                return false;
            }

            // process events for form input
            function processInputEvents(e) {
                let input_elem = e.target; // get element that fire the event

                switch (e.type) {
                    case "keyup":
                        // remove the red underline
                        input_elem.removeAttribute("style");

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