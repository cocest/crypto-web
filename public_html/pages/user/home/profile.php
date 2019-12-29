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
        <!--image editor window-->
        <div id="img-editor-win-cont" class="remove-elem">
            <div class="title-bar-cont">
                <div class="close-btn ux-f-rd-corner" onclick="closeImgEditorWin()">
                    <img src="../../images/icons/notification_icons.png" />
                </div>
            </div>
            <div class="profile-img-cont">
                <div class="profile-img-wrapper">
                    <img id="new-profile-pic" />
                </div>
                <div id="profile-img-drag-event" class="img-clip-marker">
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
                </div>
            </div>
            <div class="profile-btn-cont">
                <div class="apply-btn-cont">
                    <input type="button" value="Upload" onclick="uploadProfilePicture(this)" />
                </div>
            </div>
        </div>
        <h1 class="page-title-hd">Profile</h1>
        <div class="profile-sec-1">
            <div class="account-profile-cont">
                <form name="new-profile-pic-form">
                    <div class="profile-pic-cont">
                        <img id="user-profile-picture" 
                            class="<?php echo empty($data_for_page_rendering['mediumProfilePictureURL']) ? 'default-pic' : 'user-pic'; ?>" 
                            src="<?php echo empty($data_for_page_rendering['mediumProfilePictureURL']) ? '../../images/icons/profile_pic2.png' : '../../uploads/users/profile/'.$data_for_page_rendering['mediumProfilePictureURL']; ?>" />
                        <input id="upload-profile-pic-input" type="file" name="file" accept="image/png, image/jpeg, image/gif">
                        <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                        <label for="upload-profile-pic-input" class="upload-profile-pic" title="Click to upload.">
                            <span class="fas fa-camera"></span>
                        </label>
                    </div>
                </form>
                <div class="account-details-cont">
                    <h3 class="name"><?php echo $data_for_page_rendering['lastName'].' '.$data_for_page_rendering['firstName']; ?></h3>
                    <div class="username">
                        <span>Username</span> <?php echo $data_for_page_rendering['userName']; ?>
                    </div>
                    <div class="ref-id">
                        <span>Referral ID</span> <?php echo $data_for_page_rendering['referralID']; ?>
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
            let max_img_scr_up = 0;
            let max_img_scr_left = 0;
            let img_pos_x = 0;
            let img_pos_y = 0;
            let is_mouse_down = false;
            let mouse_start_point = {x: 0, y: 0};
            let profile_img_editing_disable = false;
            let profile_img_scale_factor = 1;

            // close profile picture editor
            window.closeImgEditorWin = function () {
                if (profile_img_editing_disable) {
                    return;
                }

                // close the window
                document.getElementById("new-profile-pic").removeAttribute("style");
                document.getElementById("img-editor-win-cont").setAttribute("class", "remove-elem");

                // reset file input element
                document.getElementById("upload-profile-pic-input").value = null;
            };

            // upload new profile image
            window.uploadProfilePicture = function (upload_btn) {
                profile_img_editing_disable = true;

                // get user filled form
                let form = document.forms["new-profile-pic-form"];

                let req_url = '../../profile_picture';
                let reg_form = new FormData(form);

                // add new data
                let new_profile_img = document.getElementById("new-profile-pic");
                let img_crop_info = {
                    clip_rect: {
                        x: Math.abs(img_pos_x), 
                        y: Math.abs(img_pos_y), 
                        w: 250, 
                        h: 250
                    },
                    scale_factor: profile_img_scale_factor
                };

                reg_form.append("imgcropinfo", JSON.stringify(img_crop_info))

                // disable upload button
                upload_btn.disabled = true;

                // show upload animation
                document.querySelector('#img-editor-win-cont .img-clip-marker .vt-bars-anim-cont').setAttribute("class", "vt-bars-anim-cont");


                // send request to server
                window.ajaxRequest(
                    req_url,
                    reg_form,
                    { contentType: false },
                    
                    // listen to response from the server
                    function (response) {
                        let response_data = JSON.parse(response);

                        if (response_data.success) {
                            let elem = document.getElementById("user-profile-picture");
                            elem.setAttribute("class", "user-pic");
                            elem.setAttribute("src", response_data.medium_img_url);

                            // set profile image in the page header menu
                            document.getElementById("header-profile-image").setAttribute("src", response_data.small_img_url);
                        }

                        // enable upload button
                        upload_btn.disabled = false;

                        // hide upload animation and close the window
                        document.querySelector('#img-editor-win-cont .img-clip-marker .vt-bars-anim-cont').setAttribute("class", "vt-bars-anim-cont remove-elem");
                        document.getElementById("new-profile-pic").removeAttribute("style");
                        document.getElementById("img-editor-win-cont").setAttribute("class", "remove-elem");

                        // reset file input element
                        document.getElementById("upload-profile-pic-input").value = null;

                        profile_img_editing_disable = false;
                    },

                    // listen to server error
                    function (err_status) {
                        //check if is a timeout or server busy
                        if (err_status == 408 ||
                            err_status == 504 ||
                            err_status == 503) {

                            window.uploadProfilePicture(upload_btn);

                        } else {
                            // enable upload button
                            upload_btn.disabled = false;

                            // hide upload animation and close the window
                            document.querySelector('#img-editor-win-cont .img-clip-marker .vt-bars-anim-cont').setAttribute("class", "vt-bars-anim-cont remove-elem");
                            document.getElementById("new-profile-pic").removeAttribute("style");
                            document.getElementById("img-editor-win-cont").setAttribute("class", "remove-elem");

                            // reset file input element
                            document.getElementById("upload-profile-pic-input").value = null;

                            profile_img_editing_disable = false;
                        }
                    }
                );
            };

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
                        // underline input with wrong value
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

            // utility function to validate file input
            function validFileType(file, file_types) {
                for (let i = 0; i < file_types.length; i++) {
                    if (file.type == file_types[i]) {
                        return true;
                    }
                }

                return false;
            }

            // setup window for image editing
            function initImageEditorPanel(image) {
                let w = 250;
                let h = 250;
                let o_w = image.width;
                let o_h = image.height;
                let n_h = Math.floor((w * o_h) / o_w);
                let n_w = Math.floor((h * o_w) / o_h);
                img_pos_x = 0;
                img_pos_y = 0;
                max_img_scr_up = 0;
                max_img_scr_left = 0;

                // get image element
                let img_elem = document.getElementById("new-profile-pic");

                // check to fit the image by width
                if (n_h >= h) { // fit the width
                    // set image attribute
                    img_elem.setAttribute("width", w);
                    img_elem.setAttribute("height", n_h);
                    img_elem.setAttribute("src", image.src);

                    // set the maximum image can scroll up
                    max_img_scr_up = n_h - h;

                    profile_img_scale_factor = o_w / w;

                } else { // fit the height
                    // set image attribute
                    img_elem.setAttribute("width", n_w);
                    img_elem.setAttribute("height", h);
                    img_elem.setAttribute("src", image.src);

                    // set the maximum image can scroll left
                    max_img_scr_left = n_w - w;

                    profile_img_scale_factor = o_h / h;
                }

                // show the image editor
                document.getElementById("img-editor-win-cont").removeAttribute("class");
            }

            function processSelectedProfileImage(e) {
                let img_exts = ["image/jpg", "image/jpeg", "image/png", "image/gif"]; //supported image extension
                let files = e.target.files; // FileList object
                is_uploaded_file_valid = false;

                if (files.length > 0) { // check if file is selected
                    //check if selected file is supported
                    if (validFileType(files[0], img_exts)) {
                        // check if file size is less than 4mb
                        if ((files[0].size / 1048576) < 4) {
                            let reader = new FileReader(); // read the image
                            reader.onload = function(event) {
                                // get selected image dimension
                                let img = new Image();
                                img.onload = function() { 
                                    // initialise image editor panel
                                    initImageEditorPanel(img);
                                }

                                img.src = event.target.result;
                            };

                            // Read in the image file as a data URL.
                            reader.readAsDataURL(files[0]);

                        } else { // file size is too large
                            let msg_elem = document.getElementById("msg-win-cont");
                            msg_elem.querySelector('.title').innerHTML = "Profile Image";
                            msg_elem.querySelector('.body-cont').innerHTML = 
                                "Selected profile image has exceeded maximum size of 4 megabytes.";
                            msg_elem.removeAttribute("class");

                            // reset file input
                            e.target.value = null;
                        }

                    } else { // file is not supported
                        // show error message to user
                        let msg_elem = document.getElementById("msg-win-cont");
                        msg_elem.querySelector('.title').innerHTML = "Profile Image";
                        msg_elem.querySelector('.body-cont').innerHTML = 
                            "Selected profile image is not supported.";
                        msg_elem.removeAttribute("class");

                        // reset file input
                        e.target.value = null;
                    }
                }
            };

            // position user's uploaded profile picture for croping
            function positionImageForCroping(position) {
                let img_scr_x = position.x - mouse_start_point.x;
                let img_scr_y = position.y - mouse_start_point.y;

                // scroll image and apply scroll constraint
                if (img_scr_x < 0) { // left
                    if (Math.abs(img_pos_x + img_scr_x) > max_img_scr_left) {
                        img_pos_x = max_img_scr_left * -1;
                    } else {
                        img_pos_x = img_pos_x + img_scr_x;
                    }

                } else { // right
                    if ((img_pos_x + img_scr_x) > 0) {
                        img_pos_x = 0;
                    } else {
                        img_pos_x = img_pos_x + img_scr_x;
                    }
                }

                if (img_scr_y < 0) { // up
                    if (Math.abs(img_pos_y + img_scr_y) > max_img_scr_up) {
                        img_pos_y = max_img_scr_up * -1;
                    } else {
                        img_pos_y = img_pos_y + img_scr_y;
                    }

                } else { // down
                    if ((img_pos_y + img_scr_y) > 0) {
                        img_pos_y = 0;
                    } else {
                        img_pos_y = img_pos_y + img_scr_y;
                    }
                }

                // position the image
                let profile_img = document.getElementById("new-profile-pic");
                profile_img.setAttribute("style", "top: " + img_pos_y + "px; left: " + img_pos_x + "px;");

                // reposition start point
                mouse_start_point.x = position.x;
                mouse_start_point.y = position.y;
            }

            // listen to drag event (simulated drag event for desktop)
            let profile_img = document.getElementById("profile-img-drag-event");

            // for mouse event
            profile_img.onmousemove = function (e) {
                if (profile_img_editing_disable) {
                    return;
                }

                if (is_mouse_down) {
                    positionImageForCroping({x: e.offsetX, y: e.offsetY});
                }
            };

            profile_img.onmousedown = function (e) {
                if (!is_mouse_down) {
                    is_mouse_down = true;
                }

                mouse_start_point.x = e.offsetX;
                mouse_start_point.y = e.offsetY;
            };

            profile_img.onmouseup = function (e) {
                if (is_mouse_down) {
                    is_mouse_down = false;
                }
            };

            // for touch devices
            let tracked_touch = null;

            profile_img.addEventListener("touchmove", function (e) {
                e.preventDefault();
                let touches = e.changedTouches;
                let touch = null;

                // check if user's tracked finger is still active
                if (tracked_touch == null) return false;

                // find the track finger
                for (let i = 0; i < touches.length; i++) {
                    if (tracked_touch.identifier == touches[i].identifier) {
                        touch = touches[i];
                        break; // exit for
                    }
                }

                if (touch) {
                    if (profile_img_editing_disable) {
                        return;
                    }

                    positionImageForCroping({x: touch.pageX, y: touch.pageY});
                }

            }, false);

            profile_img.addEventListener("touchstart", function (e) {
                e.preventDefault();
                let touches = e.changedTouches;

                // check if we haven't start tracking a finger
                if (tracked_touch == null) {
                    // we track only one finger
                    tracked_touch = touches[0];

                    mouse_start_point.x = touches[0].pageX;
                    mouse_start_point.y = touches[0].pageY;
                }

            }, false);

            profile_img.addEventListener("touchend", function (e) {
                e.preventDefault();
                let touches = e.changedTouches;

                if (tracked_touch != null) {
                    tracked_touch = null;
                }

            }, false);

            // add change event listener to file input
            document.getElementById('upload-profile-pic-input').addEventListener(
                'change', processSelectedProfileImage, false
            );

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