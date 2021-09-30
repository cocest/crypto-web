function init() {
    let err_msg_box = document.getElementById("err-msg-box");
    let validating_user_input = false;
    let is_email_validated = false;
    let user_passwd_strength = 0;
    let passwd_indicator_elems = document.querySelectorAll(".passwd-strength-indicator-cont .indicator");
    let is_passwd_shown = false;
    let curr_text_caret_offset = 0;
    let remove_user_input = false;
    let del_input_offset = 0;
    let curr_text_input_value = "";
    let input_has_err_msg = {
        firstname: { error: null, message: "" },
        lastname: { error: null, message: "" },
        email: { error: null, message: "" },
        birthdate: { error: null, message: "" }
    };

    let inputs_current_value = {
        email: ""
    };

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
                { contentType: "application/x-www-form-urlencoded" },

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
                    if (input_name == "email") {
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
                    if (input_name == "email" ||
                        input_name == "birthdate") {

                        input_elem.removeAttribute("placeholder");
                    }

                    // validate user's input
                    if (input_name == "firstname" || input_name == "lastname") {
                        // check if input contain value
                        if (input_elem.value.length > 0) {
                            // validate input
                            if (!/^([a-zA-Z]|[a-zA-Z]+[']?[a-zA-Z]+)$/.test(input_elem.value.trime())) {
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

                    }

                    break;

                case "keydown":

                    if (input_name == "birthdate") {
                        if (!remove_user_input) {
                            remove_user_input = true;
                            curr_text_caret_offset = window.getCaretPosition(input_elem);
                        }

                        del_input_offset = window.getCaretPosition(input_elem);
                        curr_text_input_value = input_elem.value;
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
                        if (remove_user_input) {
                            remove_user_input = false;
                        }

                        if (curr_text_input_value.length > input_elem.value.length) {
                            if (del_input_offset !=  window.getCaretPosition(input_elem)) { // backspace
                                if (curr_text_input_value.substring(del_input_offset - 1, del_input_offset) == "/") {
                                    input_elem.value = input_elem.value.substring(0, del_input_offset - 2);
    
                                } else {
                                    input_elem.value = input_elem.value.substring(0, del_input_offset - 1);
                                }

                            } else { // delete
                                if (curr_text_input_value.substring(del_input_offset, del_input_offset + 1) == "/") {
                                    input_elem.value = input_elem.value.substring(0, del_input_offset - 1);
    
                                } else {
                                    input_elem.value = input_elem.value.substring(0, del_input_offset);
                                }
                            }

                            return;
                        }

                        let date_split = input_elem.value.split("/"); // split date into parts

                        // check if date fo birth is invalid
                        if (!/^((0|([1-9]|0[1-9]|1[0-9])\/?)|((0[1-9]|1[0-2])\/(0|([1-9]|0[1-9]|[1-2][0-9]|3[0-9])\/?))|((0[1-9]|1[0-2])\/(0[1-9]|[1-2][0-9]|3[0-1])\/([1-9]\d{0,3})))$/.test(input_elem.value)) {
                            input_elem.value = 
                                input_elem.value.substring(0, curr_text_caret_offset) + 
                                input_elem.value.substring(window.getCaretPosition(input_elem), input_elem.value.length);

                            window.setCaretPosition(input_elem, curr_text_caret_offset); // reposition caret or cursor
                            return;
                        }

                        if (date_split.length == 1) { // typing month
                            if (/^0[1-9]$/.test(date_split[0])) {
                                input_elem.value = date_split[0] + "/";

                            } else if (date_split[0] > 1 && date_split[0] < 10) { // 2 -> 9
                                input_elem.value = "0" + date_split[0] + "/";

                            } else if (date_split[0] > 9) { // 10 -> 19
                                if (date_split[0] < 13) { // 10 -> 12
                                    input_elem.value = date_split[0] + "/";

                                } else if (date_split[0] < 14) { // 13
                                    input_elem.value = "01/3";

                                } else { // 14 -> 19
                                    input_elem.value = "01/0" + date_split[0][1] + "/";
                                }
                            }

                        } else if (date_split.length == 2) { // typing day
                            if (/^1\/$/.test(input_elem.value)) {
                                input_elem.value = "01/";

                            } else if (date_split[1].length == 1 && date_split[1] > 2 && date_split[1] < 10) { // 3 -> 9
                                if (date_split[0] == "02" && date_split[1] == 3) {
                                    input_elem.value = date_split[0] + "/03/";

                                } else if (date_split[1] == 3) { // 3
                                    input_elem.value = date_split[0] + "/3";

                                } else {
                                    input_elem.value = date_split[0] + "/0" + date_split[1] + "/";
                                }

                            } else if (date_split[1].length == 2 && date_split[1] < 30) { // 10 -> 29
                                input_elem.value += "/";

                            } else if (date_split[1].length == 2 && date_split[1] > 29) { // 30 -> 31
                                if (date_split[1] > maxDays(parseInt(date_split[0]))) {
                                    input_elem.value = date_split[0] + "/0" + date_split[1][0] + "/" + date_split[1][1];

                                } else {
                                    input_elem.value += "/";
                                }
                            }

                        } else { // typing year
                            if (/^[0-9]{2}\/[1-3]\/$/.test(input_elem.value)) {
                                input_elem.value = date_split[0] + "/0" + date_split[1] + "/";

                            } else if (date_split[2].length == 4 && ((parseInt(date_split[0]) == 2 && parseInt(date_split[1]) == 29) || parseInt(date_split[1]) == 31)) { // check if the day is correct base on selected month
                                input_elem.value = date_split[0] + "/" + maxDays(parseInt(date_split[0]), parseInt(date_split[2])) + "/" + date_split[2];
                            }
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

                    } else if (input_name == "email") {
                        // check if content is modified by user's input
                        if (!(inputs_current_value[input_name] == input_elem.value)) {
                            is_email_validated = false;
                            inputs_current_value[input_name] = input_elem.value;

                            // hide all the input icon
                            let input_cont_elem = document.querySelector(".email-input-wrapper .input-icon-cont");
                            input_cont_elem.querySelector(".mark-icon-cont").setAttribute("class", "mark-icon-cont remove-elem");
                            input_cont_elem.querySelector(".reload-btn-cont").setAttribute("class", "reload-btn-cont remove-elem");
                            input_cont_elem.querySelector(".vt-bars-anim-cont").setAttribute("class", "vt-bars-anim-cont remove-elem");
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
            elem.setAttribute("style", "color: white;");

            setTimeout(function () {
                // hide password
                passwd_input.setAttribute("type", "password");
                elem.removeAttribute("style");
                is_passwd_shown = false;
            }, 2000);
        }
    };

    // validate the user's registeration form input
    function validateRegisterationFormInput() {
        if (!inputHasError(["birthdate", "email"])) {
            if (validateFormInput(
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
                        name: "birthdate",
                        regExp: /^(0[1-9]|1[0-2])\/(0[1-9]|[1-2][0-9]|3[0-1])\/[1-9]\d{3}$/,
                        err_msg: "Date of birth is invalid."
                    },
                    {
                        name: "gender",
                        regExp: /^.+$/
                    }, 
                    {
                        name: "email",
                        regExp: /^.+$/
                    }
                ]
            )) {
                return false;
            };

            // check if email has been validated
            if (!is_email_validated) {
                return false;
            }

            // check if password strength is acceptable
            if (user_passwd_strength < 3) {
                document.getElementById("password-input").setAttribute("class", "err-hr-line-input");
                return false;
            }

            return true;

        } else { // input already has an error
            return false;
        }
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

    // process form and submit to server
    window.processRegisterationForm = function (e) {
        e.preventDefault(); // prevent form from submitting

        let submit_btn = document.querySelector(".reg-btn");
        submit_btn.disabled = true; // disable registeration button

        setTimeout(() => {
            // check if user's email is still validating
            if (validating_user_input) {
                submit_btn.disabled = false; // enable submit button
                return;
            }

            // check if form contain valid data and terms check button is checked
            if (validateRegisterationFormInput() && document.getElementById("acceptterms-input").checked) {
                let req_url = 'create_new_user';
                let reg_form = new FormData(document.forms["registeration-form"]);

                // send request to server
                window.ajaxRequest(
                    req_url,
                    reg_form,
                    { contentType: false },

                    // listen to response from the server
                    function (response) {
                        submit_btn.disabled = false; // enable submit button
                        response_data = JSON.parse(response);

                        // check if registeration was succesfull
                        if (response_data.success) {
                            // redirect user
                            window.location.replace(response_data.redirect_url);

                        } else {
                            alert(response_data.error_msg);
                        }
                    },

                    // listen to server error
                    function (err_status) {
                        submit_btn.disabled = false; // enable submit button

                        //check if is a timeout or server busy
                        if (err_status == 408 ||
                            err_status == 504 ||
                            err_status == 503) {

                            window.processRegisterationForm(e);

                        } else {
                            alert("An error occured, please try again.");
                        }
                    }
                );

            } else {
                submit_btn.disabled = false; // enable submit button
            }

        }, 200);
    };

    // get all the input element to attach events
    let inputs = document.getElementsByTagName("input");
    attachEventsToInputs(inputs, "input");

    // get all the select element to attach events
    inputs = document.getElementsByTagName("select");
    attachEventsToInputs(inputs, "select");

    // listen to click event on accept terms check button
    document.getElementById("acceptterms-input").addEventListener("click", analyseUserInput, false);

    // call function after reload
    pushUpInputLabelOnReload();
}

// initialise the script
if (window.attachEvent) {
    window.attachEvent("onload", init);

} else {
    window.addEventListener("load", init, false);
}