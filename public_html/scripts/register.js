function init() {
    let current_birthdate = "";
    let edit_birthdate = false;
    let err_msg_box = document.getElementById("err-msg-box");
    let current_form_index = 1;
    let validating_user_input = false;
    let is_email_validated = false;
    let is_username_validated = false;
    let is_referralid_validated = false;
    let user_passwd_strength = 0;
    let passwd_indicator_elems = document.querySelectorAll(".passwd-strength-indicator-cont .indicator");
    let is_passwd_shown = false;
    let is_passwd_confirmed = false;
    let is_uploaded_file_valid = false;
    let input_has_err_msg = {
        firstname: { error: null, message: "" },
        lastname: { error: null, message: "" },
        countrycode: { error: null, message: "" },
        phonenumber: { error: null, message: "" },
        email: { error: null, message: "" },
        birthdate: { error: null, message: "" },
        username: { error: null, message: "" },
        referralid: { error: null, message: "" }
    };

    // allowed keys for inputs
    let allowed_keys_for_inputs = {
        countrycode: [
            187, // '+' keyCode
            8, // Backspace keyCode
            46, // Delete keyCode
            37, // Left arrow keyCode
            39, // Right arrow keyCode
            38, // Up arrow keyCode
            40, // Down arrow keyCode
            36, // Home keyCode
            35, // End keyCode
        ],
        phonenumber: [
            8, // Backspace keyCode
            46, // Delete keyCode
            37, // Left arrow keyCode
            39, // Right arrow keyCode
            38, // Up arrow keyCode
            40, // Down arrow keyCode
            36, // Home keyCode
            35, // End keyCode
        ],
        birthdate: [
            191, // Forward slash
            8, // Backspace keyCode
            46, // Delete keyCode
            37, // Left arrow keyCode
            39, // Right arrow keyCode
            38, // Up arrow keyCode
            40, // Down arrow keyCode
            36, // Home keyCode
            35, // End keyCode
        ]
    };

    // non-alphanumeric keys that modify input content
    let modify_keys = [
        8, // Backspace keyCode
        46, // Delete keyCode
    ];

    // utility function to get the index of deleted character(s)
    function getDeletedStringIndex(original_text, altered_text) {
        for (let i = 0; i < altered_text.length; i++) {
            if (altered_text[i] != original_text[i]) {
                return i;
            }
        }

        if (altered_text.length == original_text.length) {
            return -1;
        } else {
            return altered_text.length;
        }
    }

    // utility function that return the maximum days of the month
    function maxDays(month, year = null) {
        if (month == 2 && year == null) {
            return 29;

        } else {
            return window.daysInMonth(month - 1, year);
        }
    }

    // utility function to get position of input element relative to form container
    function getPositionForErrorMsg(input_elem, container_elem, msg_width) {
        let input_pos = input_elem.getBoundingClientRect();
        let cont_pos = container_elem.getBoundingClientRect();

        let x = input_pos.left - cont_pos.left;
        let y = input_pos.top + input_pos.height - cont_pos.top;
        let position = "left";

        if (x < (cont_pos.width / 4)) { // position error message to left
            x = x;

        } else { // position error message to right
            x = x - (msg_width - input_pos.width);
            position = "right";
        }

        return { x: x, y: y + 5, position: position };
    }

    // analyse user input 
    function analyseUserInput(e) {
        if (e.isTrusted && e.target.checked) {
            e.target.value = 1;

        } else {
            e.target.value = 0;
        }
    }

    // utility function to validate the user's email address
    function validateUserEmailAddress(input_elem) {
        validating_user_input = true;

        // check if email is already validated
        if (is_email_validated) {
            return;
        }

        let email_exp = pattern = /^(([^<>()\[\]\\.,;:\s@"]+(\.[^<>()\[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;
        // validate input
        if (email_exp.test(input_elem.value)) {
            // check if email has been used by another person
            // disable email input
            input_elem.disabled = true;

            // display busy or wait animation
            let input_cont_elem = document.querySelector(".email-input-wrapper .input-icon-cont");
            input_cont_elem.querySelector(".mark-icon-cont").setAttribute("class", "mark-icon-cont remove-elem");
            input_cont_elem.querySelector(".reload-btn-cont").setAttribute("class", "reload-btn-cont remove-elem");
            input_cont_elem.querySelector(".vt-bars-anim-cont").setAttribute("class", "vt-bars-anim-cont");

            let req_url = 'request';
            let form_data = 'req=emailexist&d=' + input_elem.value; // request query

            // send request to server
            window.ajaxRequest(
                req_url,
                form_data,
                "application/x-www-form-urlencoded",

                // listen to response from the server
                function (response) {
                    response_data = JSON.parse(response);

                    if (response_data.email_exist) {
                        input_elem.setAttribute("class", "err-hr-line-input");
                        input_has_err_msg[input_elem.getAttribute("name")].error = "input_exist";
                        input_has_err_msg[input_elem.getAttribute("name")].message = "Email has been claimed by another person.";

                        // hide wait animation
                        input_cont_elem.querySelector(".vt-bars-anim-cont").setAttribute("class", "vt-bars-anim-cont remove-elem");

                    } else {
                        // display check icon
                        input_cont_elem.querySelector(".vt-bars-anim-cont").setAttribute("class", "vt-bars-anim-cont remove-elem");
                        input_cont_elem.querySelector(".mark-icon-cont").setAttribute("class", "mark-icon-cont");

                        is_email_validated = true;
                    }

                    input_elem.disabled = false; // enable email input
                    validating_user_input = false;
                },

                // listen to server error
                function (err_status) {
                    // display reload button
                    input_cont_elem.querySelector(".vt-bars-anim-cont").setAttribute("class", "vt-bars-anim-cont remove-elem");
                    input_cont_elem.querySelector(".reload-btn-cont").setAttribute("class", "reload-btn-cont");

                    input_elem.setAttribute("class", "err-hr-line-input");
                    input_has_err_msg[input_elem.getAttribute("name")].error = "network_err";

                    //check if is a timeout or server busy
                    if (err_status == 408 ||
                        err_status == 504 ||
                        err_status == 503) {

                        input_has_err_msg[input_elem.getAttribute("name")].message = "Server busy or timeout, Please click the retry button to try again.";

                    } else {
                        input_has_err_msg[input_elem.getAttribute("name")].message = "Check your internet connection and click the retry button.";
                    }

                    input_elem.disabled = false; // enable email input
                    validating_user_input = false;
                }
            );

        } else { // invalid email address
            input_elem.setAttribute("class", "err-hr-line-input");
            input_has_err_msg[input_elem.getAttribute("name")].error = "invalid_input";
            input_has_err_msg[input_elem.getAttribute("name")].message = "Your email is not acceptable.";

            validating_user_input = false;
        }
    }

    // utility function to check if another user have claim the "username"
    function validateUsername(input_elem) {
        validating_user_input = true;

        // check if username is already validated
        if (is_username_validated) {
            return;
        }

        // validate input
        if (/^([a-zA-Z0-9]+|[a-zA-Z0-9]+@?[a-zA-Z0-9]+)$/.test(input_elem.value)) {
            // disable email input
            input_elem.disabled = true;

            // display busy or wait animation
            let input_cont_elem = document.querySelector(".username-input-wrapper .input-icon-cont");
            input_cont_elem.querySelector(".mark-icon-cont").setAttribute("class", "mark-icon-cont remove-elem");
            input_cont_elem.querySelector(".reload-btn-cont").setAttribute("class", "reload-btn-cont remove-elem");
            input_cont_elem.querySelector(".vt-bars-anim-cont").setAttribute("class", "vt-bars-anim-cont");

            let req_url = 'request';
            let form_data = 'req=usernameexist&d=' + input_elem.value; // request query

            // send request to server
            window.ajaxRequest(
                req_url,
                form_data,
                "application/x-www-form-urlencoded",

                // listen to response from the server
                function (response) {
                    response_data = JSON.parse(response);

                    if (response_data.username_exist) {
                        input_elem.setAttribute("class", "err-hr-line-input");
                        input_has_err_msg[input_elem.getAttribute("name")].error = "input_exist";
                        input_has_err_msg[input_elem.getAttribute("name")].message = "Username has been chosen, try another name.";

                        // hide wait animation
                        input_cont_elem.querySelector(".vt-bars-anim-cont").setAttribute("class", "vt-bars-anim-cont remove-elem");

                    } else {
                        // display check icon
                        input_cont_elem.querySelector(".vt-bars-anim-cont").setAttribute("class", "vt-bars-anim-cont remove-elem");
                        input_cont_elem.querySelector(".mark-icon-cont").setAttribute("class", "mark-icon-cont");

                        is_username_validated = true;
                    }

                    input_elem.disabled = false; // enable input
                    validating_user_input = false;
                },

                // listen to server error
                function (err_status) {
                    // display reload button
                    input_cont_elem.querySelector(".vt-bars-anim-cont").setAttribute("class", "vt-bars-anim-cont remove-elem");
                    input_cont_elem.querySelector(".reload-btn-cont").setAttribute("class", "reload-btn-cont");

                    input_elem.setAttribute("class", "err-hr-line-input");
                    input_has_err_msg[input_elem.getAttribute("name")].error = "network_err";

                    //check if is a timeout or server busy
                    if (err_status == 408 ||
                        err_status == 504 ||
                        err_status == 503) {

                        input_has_err_msg[input_elem.getAttribute("name")].message = "Server busy or timeout, Please click the retry button to try again.";

                    } else {
                        input_has_err_msg[input_elem.getAttribute("name")].message = "Check your internet connection and click the retry button.";
                    }

                    input_elem.disabled = false; // enable input
                    validating_user_input = false;
                }
            );

        } else { // invalid username
            input_elem.setAttribute("class", "err-hr-line-input");
            input_has_err_msg[input_elem.getAttribute("name")].error = "invalid_input";
            input_has_err_msg[input_elem.getAttribute("name")].message = "Username should contain only number, alphabet or @ character in-between.";

            validating_user_input = false;
        }
    }

    // utility function to check if referral id exist
    function validateReferralID(input_elem) {
        validating_user_input = true;

        // check if referral id is already validated
        if (is_referralid_validated) {
            return;
        }

        // validate input
        if (/^([a-zA-Z0-9]+)$/.test(input_elem.value)) {
            // disable email input
            input_elem.disabled = true;

            // display busy or wait animation
            let input_cont_elem = document.querySelector(".referralid-input-wrapper .input-icon-cont");
            input_cont_elem.querySelector(".mark-icon-cont").setAttribute("class", "mark-icon-cont remove-elem");
            input_cont_elem.querySelector(".reload-btn-cont").setAttribute("class", "reload-btn-cont remove-elem");
            input_cont_elem.querySelector(".vt-bars-anim-cont").setAttribute("class", "vt-bars-anim-cont");

            let req_url = 'request';
            let form_data = 'req=referralexist&d=' + input_elem.value; // request query

            // send request to server
            window.ajaxRequest(
                req_url,
                form_data,
                "application/x-www-form-urlencoded",

                // listen to response from the server
                function (response) {
                    response_data = JSON.parse(response);

                    if (response_data.referral_exist) {
                        // display check icon
                        input_cont_elem.querySelector(".vt-bars-anim-cont").setAttribute("class", "vt-bars-anim-cont remove-elem");
                        input_cont_elem.querySelector(".mark-icon-cont").setAttribute("class", "mark-icon-cont");

                        is_referralid_validated = true;

                    } else {
                        input_elem.setAttribute("class", "err-hr-line-input");
                        input_has_err_msg[input_elem.getAttribute("name")].error = "input_not_exist";
                        input_has_err_msg[input_elem.getAttribute("name")].message = "Referral ID is invalid.";

                        // hide wait animation
                        input_cont_elem.querySelector(".vt-bars-anim-cont").setAttribute("class", "vt-bars-anim-cont remove-elem");
                    }

                    input_elem.disabled = false; // enable input
                    validating_user_input = false;
                },

                // listen to server error
                function (err_status) {
                    // display reload button
                    input_cont_elem.querySelector(".vt-bars-anim-cont").setAttribute("class", "vt-bars-anim-cont remove-elem");
                    input_cont_elem.querySelector(".reload-btn-cont").setAttribute("class", "reload-btn-cont");

                    input_elem.setAttribute("class", "err-hr-line-input");
                    input_has_err_msg[input_elem.getAttribute("name")].error = "network_err";

                    //check if is a timeout or server busy
                    if (err_status == 408 ||
                        err_status == 504 ||
                        err_status == 503) {

                        input_has_err_msg[input_elem.getAttribute("name")].message = "Server busy or timeout, Please click the retry button to try again.";

                    } else {
                        input_has_err_msg[input_elem.getAttribute("name")].message = "Check your internet connection and click the retry button.";
                    }

                    input_elem.disabled = false; // enable input
                    validating_user_input = false;
                }
            );

        } else { // invalid username
            input_elem.setAttribute("class", "err-hr-line-input");
            input_has_err_msg[input_elem.getAttribute("name")].error = "invalid_input";
            input_has_err_msg[input_elem.getAttribute("name")].message = "Referral ID should contain only number and alphabet.";

            validating_user_input = false;
        }
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
        let input_name;
        let label_elem;

        if (input_elem.tagName == "INPUT") {
            let input_name = input_elem.getAttribute("name");

            switch (e.type) {
                case "focus":
                    // push the input label up
                    label_elem = input_elem.parentElement.firstElementChild;
                    label_elem.setAttribute("class", "push-up");

                    // check if there error message and display it
                    if (typeof input_has_err_msg[input_name] != "undefined" && input_has_err_msg[input_name].error != null) {
                        // set the error message
                        err_msg_box.querySelector(".msg").innerHTML = input_has_err_msg[input_name].message;
                        let pos = getPositionForErrorMsg(input_elem, document.querySelector(".form-cont"), err_msg_box.clientWidth);
                        err_msg_box.setAttribute("style", "left: " + pos.x + "px; top: " + pos.y + "px;");

                        // check to position message pointer
                        if (pos.position == "right") {
                            // display it
                            err_msg_box.setAttribute("class", "right-pointer");

                        } else {
                            // display it
                            err_msg_box.setAttribute("class", "left-pointer");
                        }
                    }

                    // add placehoder to input
                    if (input_name == "countrycode") {
                        input_elem.setAttribute("placeholder", "+234");

                    } else if (input_name == "email") {
                        input_elem.setAttribute("placeholder", "example@mail.com");

                    } else if (input_name == "birthdate") {
                        input_elem.setAttribute("placeholder", "mm/dd/yyyy");
                    }

                    break;

                case "blur":

                    if (input_elem.value.length < 1) {
                        // push the input label down
                        label_elem = input_elem.parentElement.firstElementChild;
                        label_elem.removeAttribute("class");
                    }

                    // check if error message is open and close it
                    if (typeof input_has_err_msg[input_name] != "undefined" && input_has_err_msg[input_name].error != null) {
                        err_msg_box.setAttribute("class", "hide-elem left-pointer");
                    }

                    // remove placehoder added to email input
                    if (input_name == "countrycode" ||
                        input_name == "email" ||
                        input_name == "birthdate") {

                        input_elem.removeAttribute("placeholder");
                    }

                    // check if email is valid
                    if (input_name == "firstname" || input_name == "lastname") {
                        // check if input contain value
                        if (input_elem.value.length > 0) {
                            // validate input
                            if (!/^([a-zA-Z]|[a-zA-Z]+[']?[a-zA-Z]+)$/.test(input_elem.value)) {
                                input_elem.setAttribute("class", "err-hr-line-input");

                                input_has_err_msg[input_name].error = "invalid_input";
                                input_has_err_msg[input_name].message = "Name should contain only alphabet or ' character.";
                            }
                        }

                    } else if (input_name == "email") {
                        // check if input contain value
                        if (input_elem.value.length > 0) {
                            if (input_has_err_msg[input_name].error == null || input_has_err_msg[input_name].error == "invalid_input") {
                                validateUserEmailAddress(input_elem);
                            }
                        }

                    } else if (input_name == "birthdate") {
                        // check if input contain value
                        if (input_elem.value.length > 0) {
                            // validate input
                            if (!/^(0[1-9]|1[0-2])\/(0[1-9]|[1-2][0-9]|3[0-1])\/[1-9]\d{3}$/.test(input_elem.value)) {
                                input_elem.setAttribute("class", "err-hr-line-input");

                                input_has_err_msg[input_name].error = "invalid_input";
                                input_has_err_msg[input_name].message = "Date of birth is invalid.";
                            }
                        }

                    } else if (input_name == "username") {
                        // check if input contain value
                        if (input_elem.value.length > 0) {
                            // validate input
                            if (input_has_err_msg[input_name].error == null || input_has_err_msg[input_name].error == "invalid_input") {
                                validateUsername(input_elem);
                            }
                        }

                    } else if (input_name == "referralid") {
                        // check if input contain value
                        if (input_elem.value.length > 0) {
                            // validate input
                            if (input_has_err_msg[input_name].error == null || input_has_err_msg[input_name].error == "invalid_input") {
                                validateReferralID(input_elem);
                            }
                        }
                    }

                    break;

                case "keydown":

                    if (input_name == "countrycode") { // allow only '+', numbers and some key
                        // check if '+' has been existing in the input and block '=' character
                        if ((e.keyCode == 187 && (new RegExp("[" + input_elem.value + "]")).test(e.key)) || e.key == "=") {
                            e.preventDefault();

                        } else if (!(allowed_keys_for_inputs[input_name].find(kc => kc == e.keyCode) ||
                            !isNaN(parseInt(e.key)))) {
                            e.preventDefault();
                        }

                    } else if (input_name == "phonenumber") { // allow only numbers and some key
                        if (!(allowed_keys_for_inputs[input_name].find(kc => kc == e.keyCode) ||
                            !isNaN(parseInt(e.key)))) {
                            e.preventDefault();
                        }

                    } else if (input_name == "birthdate") {
                        current_birthdate = input_elem.value; // get birthdate value for deletion

                        // block '=' character
                        if (e.keyCode == 191 && e.key != "/") {
                            e.preventDefault();
                            return;

                        } else if (!(allowed_keys_for_inputs[input_name].find(kc => kc == e.keyCode) ||
                            !isNaN(parseInt(e.key)))) {
                            e.preventDefault();
                            return;
                        }

                        // de-activate edit mode if backspace and delete button is pressed
                        if (e.keyCode == 8 || e.keyCode == 46) {
                            if (edit_birthdate) {
                                edit_birthdate = false;
                            }
                        }

                        // check to activate edit mode
                        if (e.keyCode == 37) { // left arrow key is pressed
                            if (!edit_birthdate) {
                                edit_birthdate = true;
                            }
                        }

                        // check if edit mode is active
                        if (edit_birthdate) {
                            if (e.key == "/") {
                                e.preventDefault();
                            }

                            return;
                        }

                        // help the user format the date
                        let date_split = input_elem.value.split("/");

                        if (date_split.length == 1) { // month
                            if (/^(00|0\/|\/)$/.test(input_elem.value + e.key)) {
                                e.preventDefault();

                            } else if (parseInt(e.key) > 1 && date_split[0].length < 1) {
                                input_elem.value = "0";

                            } else if (e.key == "/" && date_split[0].length < 2) {
                                input_elem.value =
                                    input_elem.value.substring(0, input_elem.value.length - 1) + "0" +
                                    input_elem.value.substring(input_elem.value.length - 1);

                            } else if (!isNaN(parseInt(e.key)) && input_elem.value.length == 2) {
                                input_elem.value = input_elem.value + "/";

                            } else if (parseInt(input_elem.value + e.key) > 12) {
                                input_elem.value = "0" + input_elem.value + "/";
                            }

                        } else if (date_split.length == 2) { // day
                            if (/^(00|0\/|\/)$/.test(date_split[1] + e.key)) {
                                e.preventDefault();

                            } else if (parseInt(e.key) > 3 && date_split[1].length < 1) {
                                input_elem.value = input_elem.value + "0";

                            } else if (e.key == "/" && date_split[1].length < 2) {
                                input_elem.value =
                                    input_elem.value.substring(0, input_elem.value.length - 1) + "0" +
                                    input_elem.value.substring(input_elem.value.length - 1);

                            } else if (!isNaN(parseInt(e.key)) && date_split[1].length == 2) {
                                // since the input will fall to year, we don't allow zero for start
                                if (e.key == "0") {
                                    e.preventDefault();
                                } else {
                                    input_elem.value = input_elem.value + "/";
                                }

                            } else if (parseInt(date_split[1] + e.key) > maxDays(parseInt(date_split[0]))) {
                                // since the input will fall to year, we don't allow zero for start
                                if (e.key == "0") {
                                    e.preventDefault();
                                } else {
                                    input_elem.value = date_split[0] + "/0" + date_split[1] + "/";
                                }
                            }

                        } else { // year
                            if (/^(0|\/)$/.test(date_split[2] + e.key) || (e.key == "/" && date_split[2].length > 0)) {
                                e.preventDefault();

                            } else if (!isNaN(parseInt(e.key)) && date_split[2].length == 3 && parseInt(date_split[0]) == 2) {
                                // check if user day input is 29
                                if (parseInt(date_split[1]) > 28) {
                                    input_elem.value =
                                        date_split[0] + "/" +
                                        maxDays(parseInt(date_split[0]), parseInt(date_split[2] + e.key)) + "/" +
                                        date_split[2];
                                }

                            } else if (!isNaN(parseInt(e.key)) && date_split[2].length == 4) { // you only allowed to enter for digit number
                                e.preventDefault();
                            }
                        }
                    }

                    break;

                case "keyup":

                    // remove the red underline
                    input_elem.setAttribute("class", "hr-line-input");

                    // check if error message is open and close it
                    if (typeof input_has_err_msg[input_name] != "undefined" && input_has_err_msg[input_name].error != null) {
                        err_msg_box.setAttribute("class", "hide-elem");
                        input_has_err_msg[input_name].error = null;
                    }

                    if (input_name == "birthdate") {
                        // handle delete event
                        if (e.keyCode == 8 || e.keyCode == 46) {
                            let del_index = getDeletedStringIndex(current_birthdate, input_elem.value);
                            if (del_index > -1) {
                                input_elem.value = current_birthdate.substring(0, del_index);
                            }
                        }

                        // check if date should be edited
                        if (!edit_birthdate) {
                            return;
                        }

                        // check if user edit the date and reformat the current date
                        if (!isNaN(parseInt(e.key))) {
                            let curr_date_split = current_birthdate.split("/");
                            let date_split = input_elem.value.split("/");
                            let date_section = 0;

                            for (let i = 0; i < date_split.length; i++) {
                                if (!(curr_date_split[i] == date_split[i])) {
                                    date_section = i;
                                    break;
                                }
                            }

                            // format the date
                            if (date_section == 0) {
                                input_elem.value = "";
                            }

                            for (let j = 0; j < date_section; j++) {
                                // append number at left of edited number
                                if (j == 0) {
                                    input_elem.value = date_split[j] + "/";
                                } else {
                                    input_elem.value = input_elem.value + date_split[j] + "/";
                                }
                            }

                            // add new number and delete all the numbers at right
                            if (date_section == 0) { // month
                                if (e.key < 2) {
                                    input_elem.value = input_elem.value + e.key;
                                } else {
                                    input_elem.value = input_elem.value + "0" + e.key;
                                }

                            } else if (date_section == 1) { // day
                                if (parseInt(date_split[0]) == 2) { // feb
                                    if (e.key < 3) {
                                        input_elem.value = input_elem.value + e.key;
                                    } else {
                                        input_elem.value = input_elem.value + "0" + e.key;
                                    }

                                } else {
                                    if (e.key < 4) {
                                        input_elem.value = input_elem.value + e.key;
                                    } else {
                                        input_elem.value = input_elem.value + "0" + e.key;
                                    }
                                }

                            } else { // year
                                if (e.key != 0) {
                                    input_elem.value = input_elem.value + e.key;
                                }
                            }

                            edit_birthdate = false;
                        }

                    } else if (input_name == "password") {
                        let txt_indicator = document.querySelector(".passwd-strength-indicator-cont .txt-ind-wrapper span");

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

                        // check if the two password match
                        let confirm_passwd_input = document.getElementById("confirmpasswd-input");
                        let mark_icon = document.querySelector(".confirmpasswd-input-wrapper .mark-icon-cont");

                        if (input_elem.value == confirm_passwd_input.value && confirm_passwd_input.value.length > 0) {
                            mark_icon.setAttribute("class", "mark-icon-cont");
                            is_passwd_confirmed = true;

                        } else {
                            mark_icon.setAttribute("class", "mark-icon-cont remove-elem");
                            is_passwd_confirmed = false;
                        }

                    } else if (input_name == "confirmpasswd") {
                        // check if the two password match
                        let passwd_input = document.getElementById("password-input");
                        let mark_icon = document.querySelector(".confirmpasswd-input-wrapper .mark-icon-cont");

                        if (input_elem.value == passwd_input.value && passwd_input.value.length > 0) {
                            mark_icon.setAttribute("class", "mark-icon-cont");
                            is_passwd_confirmed = true;

                        } else {
                            mark_icon.setAttribute("class", "mark-icon-cont remove-elem");
                            is_passwd_confirmed = false;
                        }

                    } else if (input_name == "email") {
                        // hide all the input icon
                        let input_cont_elem = document.querySelector(".email-input-wrapper .input-icon-cont");
                        input_cont_elem.querySelector(".mark-icon-cont").setAttribute("class", "mark-icon-cont remove-elem");
                        input_cont_elem.querySelector(".reload-btn-cont").setAttribute("class", "reload-btn-cont remove-elem");
                        input_cont_elem.querySelector(".vt-bars-anim-cont").setAttribute("class", "vt-bars-anim-cont remove-elem");

                        // check if press key is key that modify the content
                        if (modify_keys.find(kc => kc == e.keyCode)) {
                            is_email_validated = false;
                        }

                    } else if (input_name == "username") {
                        // hide all the input icon
                        let input_cont_elem = document.querySelector(".username-input-wrapper .input-icon-cont");
                        input_cont_elem.querySelector(".mark-icon-cont").setAttribute("class", "mark-icon-cont remove-elem");
                        input_cont_elem.querySelector(".reload-btn-cont").setAttribute("class", "reload-btn-cont remove-elem");
                        input_cont_elem.querySelector(".vt-bars-anim-cont").setAttribute("class", "vt-bars-anim-cont remove-elem");

                        // check if press key is key that modify the content
                        if (modify_keys.find(kc => kc == e.keyCode)) {
                            is_email_validated = false;
                        }

                    } else if (input_name == "referralid") {
                        // hide all the input icon
                        let input_cont_elem = document.querySelector(".referralid-input-wrapper .input-icon-cont");
                        input_cont_elem.querySelector(".mark-icon-cont").setAttribute("class", "mark-icon-cont remove-elem");
                        input_cont_elem.querySelector(".reload-btn-cont").setAttribute("class", "reload-btn-cont remove-elem");
                        input_cont_elem.querySelector(".vt-bars-anim-cont").setAttribute("class", "vt-bars-anim-cont remove-elem");

                        // check if press key is key that modify the content
                        if (modify_keys.find(kc => kc == e.keyCode)) {
                            is_email_validated = false;
                        }
                    }

                    break;

                case "keypress":

                    if (input_name == "countrycode") {
                        // add '+' to country code input
                        if (!(e.charCode == 43)) {
                            // don't add '+' if user type '+'
                            if (input_elem.value.length < 1) {
                                input_elem.value = "+" + input_elem.value;
                            }
                        }

                    } else if (input_name == "email") {
                        is_email_validated = false;

                    } else if (input_name == "username") {
                        is_username_validated = false;

                    } else if (input_name == "referralid") {
                        is_referralid_validated = false;
                    }

                    break;

                case "click":

                    if (input_name == "birthdate") {
                        if (!edit_birthdate && input_elem.value.length > 0) {
                            edit_birthdate = true;
                        }
                    }

                    break;

                default:
                // you shouldn't be here
            }

        } else { // SELECT
            // remove the red underline
            input_elem.setAttribute("class", "hr-line-input");

            if (input_elem.value.length > 0) {
                // push the input label down
                label_elem = input_elem.parentElement.firstElementChild;
                label_elem.setAttribute("class", "push-up");

            } else {
                // push the input label down
                label_elem = input_elem.parentElement.firstElementChild;
                label_elem.removeAttribute("class");
            }
        }
    }

    // attach event listener to input or select element
    function attachEventsToInputs(input_elements, tag_name) {
        let attach_event = false;

        for (let i = 0; i < input_elements.length; i++) {
            attach_event = input_elements[i].getAttribute("attachevent") == null ? false : true;
            // check type of element
            if (tag_name == "input" && attach_event) {
                input_elements[i].addEventListener("focus", processInputEvents, false);
                input_elements[i].addEventListener("blur", processInputEvents, false);
                input_elements[i].addEventListener("keydown", processInputEvents, false);
                input_elements[i].addEventListener("keyup", processInputEvents, false);
                input_elements[i].addEventListener("keypress", processInputEvents, false);
                input_elements[i].addEventListener("click", processInputEvents, false);

            } else if (tag_name == "select" && attach_event) {
                input_elements[i].addEventListener("change", processInputEvents, false);
            }
        }
    }

    // utility function to check if any input has an error
    function inputHasError(input_name) {
        for (let i = 0; i < input_name.length; i++) {
            if (input_has_err_msg[input_name[i]].error != null) {
                return true;
            }
        }

        return false;
    }

    // utility function to check if any form input is valid before next form
    function validateFormInput(form_input) {
        // get registeration form
        let reg_form = document.forms["registeration-form"];
        let input;

        for (let i = 0; i < form_input.length; i++) {
            input = reg_form.elements[form_input[i].name];

            if (/^[ ]*$/.test(input.value)) {
                // underline the input
                input.setAttribute("class", "err-hr-line-input");

                return true;

            } else if (!form_input[i].regExp.test(input.value)) {
                input.setAttribute("class", "err-hr-line-input");

                input_has_err_msg[form_input[i].name].error = "invalid_input";
                input_has_err_msg[form_input[i].name].message = form_input[i].err_msg;

                return true;
            }
        }

        return false;
    }

    // retry email validation again
    window.recheckEmailAddress = function () {
        validateUserEmailAddress(document.getElementById("email-input"));
    }

    // retry username validation again
    window.recheckUsername = function () {
        validateUsername(document.getElementById("username-input"));
    };

    // retry referral ID validation again
    window.recheckReferralID = function () {
        validateReferralID(document.getElementById("referralid-input"));
    }

    // show password and hide after some seconds
    window.showUserPassword = function (btn) {
        let passwd_input = document.getElementById("password-input");

        // check if password is shown
        if (!is_passwd_shown && passwd_input.value.length > 0) {
            is_passwd_shown = true;

            // show password
            passwd_input.setAttribute("type", "text");
            let elem = btn.getElementsByTagName("span")[0];
            elem.setAttribute("style", "color: #057bd9");

            setTimeout(function () {
                // hide password
                passwd_input.setAttribute("type", "password");
                elem.removeAttribute("style");
                is_passwd_shown = false;
            }, 2000);
        }
    };

    // validate the current form and navigate user to next form
    window.navigateForm = function (btn) {
        btn.disabled = true; // disable the button

        // for wait for sometime
        setTimeout(function () {
            // check if user input is still being validated by server
            if (!validating_user_input) {
                let is_invalid;

                if (current_form_index == 1) { // first form
                    if (!inputHasError(["email", "birthdate"])) {
                        is_invalid = validateFormInput(
                            [
                                {
                                    name: "firstname",
                                    regExp: /^([a-zA-Z]|[a-zA-Z]+[']?[a-zA-Z]+)$/,
                                    err_msg: "Name should contain only alphabet or ' character."
                                },
                                {
                                    name: "lastname",
                                    regExp: /^([a-zA-Z]|[a-zA-Z]+[']?[a-zA-Z]+)$/,
                                    err_msg: "Name should contain only alphabet or ' character in-between."
                                },
                                {
                                    name: "country",
                                    regExp: /^.+$/
                                },
                                {
                                    name: "countrycode",
                                    regExp: /^\+\d+$/,
                                    err_msg: "Country code is invalid."
                                },
                                {
                                    name: "phonenumber",
                                    regExp: /^\d+$/,
                                    err_msg: "Phone number is not acceptable."
                                },
                                {
                                    name: "email",
                                    regExp: /^.+$/
                                },
                                {
                                    name: "birthdate",
                                    regExp: /^(0[1-9]|1[0-2])\/(0[1-9]|[1-2][0-9]|3[0-1])\/[1-9]\d{3}$/,
                                    err_msg: "Date of birth is invalid."
                                },
                                {
                                    name: "gender",
                                    regExp: /^.+$/
                                }
                            ]
                        );

                        // check if email has been validated and other input contain valid data
                        if (is_email_validated && !is_invalid) {
                            // navigate to next form
                            document.getElementById("form-slide-wrapper").setAttribute("class", "navi-form-2");
                            document.querySelector(".progress-bar").setAttribute("class", "progress-bar stage-2");
                            document.querySelector(".progress-number").innerHTML = "2 / 3";
                            current_form_index = 2;

                        } else if (!is_email_validated) {
                            let input_elem = document.getElementById("email-input");

                            if (input_elem.value.length > 0) {
                                validateUserEmailAddress(input_elem);
                            }
                        }
                    }

                } else if (current_form_index == 2) { // second form
                    if (!inputHasError(["username"])) {
                        is_invalid = validateFormInput(
                            [
                                {
                                    name: "username",
                                    regExp: /^.+$/
                                }
                            ]
                        );

                        // check if password strength is acceptable
                        if (!is_invalid && user_passwd_strength < 3) {
                            is_invalid = true;
                            document.getElementById("password-input").setAttribute("class", "err-hr-line-input");

                        } else if (!is_invalid && !is_passwd_confirmed) {
                            is_invalid = true;
                            document.getElementById("confirmpasswd-input").setAttribute("class", "err-hr-line-input");
                        }

                        // check if email has been validated and other input contain valid data
                        if (is_username_validated && !is_invalid) {
                            // navigate to next form
                            document.getElementById("form-slide-wrapper").setAttribute("class", "navi-form-3");
                            document.querySelector(".form-navi-btn").setAttribute("class", "form-navi-btn hide-hr-pos ux-f-rd-corner");
                            document.querySelector(".progress-bar").setAttribute("class", "progress-bar stage-3");
                            document.querySelector(".progress-number").innerHTML = "3 / 3";
                            current_form_index = 3;

                        } else if (!is_username_validated) {
                            let input_elem = document.getElementById("username-input");

                            if (input_elem.value.length > 0) {
                                validateUsername(input_elem);
                            }
                        }
                    }
                }
            }

            btn.disabled = false; // enable the button

        }, 100);
    };

    /* 
       Push up label on reload if input has text on it.
       This solution is for IE Edge or browser that retain user's input after page reload
     */
    function pushUpInputLabelOnReload() {
        let input_name = [
            "firstname",
            "lastname",
            "country",
            "countrycode",
            "phonenumber",
            "email",
            "birthdate",
            "gender"
        ];

        // get registeration form
        let reg_form = document.forms["registeration-form"];
        let input_elem;

        for (let i = 0; i < input_name.length; i++) {
            input_elem = reg_form.elements[input_name[i]];

            if (!/^[ ]*$/.test(input_elem.value)) {
                input_elem.parentElement.firstElementChild.setAttribute("class", "push-up");
            }
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

    // uitlity function to shorten file name
    function shortenFileName(file_name, limit) {
        if (file_name.length > limit) {
            return "..." + file_name.substring(file_name.length - limit, file_name.length);
        }

        return file_name;
    }

    // this handle file input event by validating selected file
    function fileInputEventHandler(e) {
        let img_exts = ["image/jpg", "image/jpeg", "image/png", "image/gif"]; //supported image extension
        let files = e.target.files; // FileList object
        is_uploaded_file_valid = false;

        // check if file is selected
        if (files.length > 0) {
            //check if selected file is supported
            if (validFileType(files[0], img_exts)) {
                // check if file size is less than 4mb
                if ((files[0].size / 1048576) < 4) {
                    let elem = document.getElementById("f-upload-msg");
                    elem.querySelector(".msg").innerHTML = shortenFileName(files[0].name, 40);
                    elem.setAttribute("class", "no-error");

                    is_uploaded_file_valid = true;

                } else { // file size is too large
                    let elem = document.getElementById("f-upload-msg");
                    elem.querySelector(".msg").innerHTML = "File size exceed allowed maximum";
                    elem.setAttribute("class", "error");
                }

            } else { // file is not supported
                let elem = document.getElementById("f-upload-msg");
                elem.querySelector(".msg").innerHTML = "File type is not supported";
                elem.setAttribute("class", "error");
            }

        } else {
            let elem = document.getElementById("f-upload-msg");
            elem.setAttribute("class", "hide-elem");
        }
    }

    // process form and submit to server
    window.processRegisterationForm = function (e) {
        // check if user has reach this stage and terms check button is checked
        if (current_form_index == 3 && document.getElementById("acceptterms-input").checked) {
            e.preventDefault(); // prevent form from submitting
            let submit_btn = document.querySelector(".reg-btn");
            submit_btn.disabled = true; // disable registeration button

            setTimeout(function () {
                if (!inputHasError(["referralid"])) {
                    // check if user input is still being validated by server
                    if (!validating_user_input) {
                        let is_invalid = false;

                        if (document.getElementById("referralid-input").value != "" && !is_referralid_validated) {
                            is_invalid = true;

                        } else if (document.getElementById("f-upload-input").files.length < 1) {
                            let elem = document.getElementById("f-upload-msg");
                            elem.querySelector(".msg").innerHTML = "No scanned file is selected";
                            elem.setAttribute("class", "error");

                            is_invalid = true;

                        } else if (!is_uploaded_file_valid) {
                            is_invalid = true;
                        }

                        // check if there is no error
                        if (!is_invalid) {
                            let req_url = 'create_new_user';
                            let reg_form = new FormData(document.forms["registeration-form"]);

                            // send request to server
                            window.ajaxRequest(
                                req_url,
                                reg_form,
                                null,

                                // listen to response from the server
                                function (response) {
                                    //response_data = JSON.parse(response);

                                    alert("Still working on user page's and dashboard");
                                },

                                // listen to server error
                                function (err_status) {
                                    //check if is a timeout or server busy
                                    if (err_status == 408 ||
                                        err_status == 504 ||
                                        err_status == 503) {

                                        window.processRegisterationForm();

                                    } else {
                                        alert("An error occured, please try again.");
                                    }
                                }
                            );
                        }
                    }
                }

                submit_btn.disabled = false;

            }, 100);


        } else {
            e.preventDefault();
            return false; // just in case
        }
    };

    // get all the input element to attach events
    let inputs = document.getElementsByTagName("input");
    attachEventsToInputs(inputs, "input");

    // get all the select element to attach events
    inputs = document.getElementsByTagName("select");
    attachEventsToInputs(inputs, "select");

    // listen to click event on accept terms check button
    document.getElementById("acceptterms-input").addEventListener("click", analyseUserInput, false);

    // attach change event to file input
    document.getElementById("f-upload-input").addEventListener("change", fileInputEventHandler, false);

    // call function after reload
    pushUpInputLabelOnReload();
}

//initialise the script
if (window.attachEvent) {
    window.attachEvent("onload", init);

} else {
    window.addEventListener("load", init, false);
}