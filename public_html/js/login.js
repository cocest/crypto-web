function init() {
    // define variables here
    let is_passwd_shown = false;
    let login_locked = false;
    let error_msg_active = false;

    // show password and hide after some seconds
    window.showUserPassword = function (btn) {
        let passwd_input = document.getElementById("password-input");

        // check if password is shown
        if (!login_locked && !is_passwd_shown && passwd_input.value.length > 0) {
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

    // process events for form input
    function processInputEvents(e) {
        let input_elem = e.target; // get element that fire the event

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
    function requirdInputLeftEmpty(input_names) {
        let login_form = document.forms["login-form"];

        for (let i = 0; i < input_names.length; i++) {
            let input = login_form.elements[input_names[i]];

            if (/^[ ]*$/.test(input.value)) {
                // underline the input
                input.setAttribute("class", "err-hr-line-input");

                return true;
            }
        }

        return false;
    }

    // process form and submit to server
    window.processLoginForm = function (e) {
        e.preventDefault(); // prevent form from submitting

        // check if any input is left empty
        if (!requirdInputLeftEmpty(["username", "password"])) {
            let login_form = document.forms["login-form"];
            let form_data = new FormData(login_form);

            // disable inputs
            login_locked = true;
            login_form.elements["username"].disabled = true;
            login_form.elements["password"].disabled = true;
            login_form.elements["remember"].disabled = true;
            document.getElementById("login-submit-input").disabled = true;

            // show wait animation
            document.querySelector(".vt-bars-anim-cont").setAttribute("class", "vt-bars-anim-cont");

            let req_url = '../login_user';

            // send request to server
            window.ajaxRequest(
                req_url,
                form_data,
                { contentType: false },

                // listen to response from the server
                function (response) {
                    // enable inputs
                    login_locked = false;
                    login_form.elements["username"].disabled = false;
                    login_form.elements["password"].disabled = false;
                    login_form.elements["remember"].disabled = false;
                    document.getElementById("login-submit-input").disabled = false;

                    // hide wait animation
                    document.querySelector(".vt-bars-anim-cont").setAttribute("class", "vt-bars-anim-cont hide-elem");

                    // convert response to object
                    let response_data = JSON.parse(response);

                    if (response_data.success) {
                        //redirect to user homepage
                        window.location.replace(response_data.redirect_url);

                    } else { // invalid username or password
                        let elem = document.getElementById("err-msg-box");
                        elem.querySelector(".msg").innerHTML = "Username or password is incorrect.";
                        elem.removeAttribute("class");

                        error_msg_active = true;
                    }
                },

                // listen to server error
                function (err_status, msg) {
                    // check if is a timeout
                    if (err_status == 408 || err_status == 504) {

                        window.processLoginForm(e);

                    } else if (err_status == 503) { // check if is server busy
                        // enable inputs
                        login_locked = false;
                        login_form.elements["username"].disabled = false;
                        login_form.elements["password"].disabled = false;
                        login_form.elements["remember"].disabled = false;
                        document.getElementById("login-submit-input").disabled = false;

                        // hide wait animation
                        document.querySelector(".vt-bars-anim-cont").setAttribute("class", "vt-bars-anim-cont hide-elem");

                        let elem = document.getElementById("err-msg-box");
                        elem.querySelector(".msg").innerHTML = "Server is busy try again later.";
                        elem.removeAttribute("class");

                        error_msg_active = true;

                    } else if (err_status == 429) { // too many request error
                        response_data = JSON.parse(msg); // convert string to object

                        setTimeout(function () {
                            // enable inputs
                            login_locked = false;
                            login_form.elements["username"].disabled = false;
                            login_form.elements["password"].disabled = false;
                            login_form.elements["remember"].disabled = false;
                            document.getElementById("login-submit-input").disabled = false;

                            // hide wait animation
                            document.querySelector(".vt-bars-anim-cont").setAttribute("class", "vt-bars-anim-cont hide-elem");

                            let elem = document.getElementById("err-msg-box");
                            elem.querySelector(".msg").innerHTML = "Username or password is incorrect.";
                            elem.removeAttribute("class");

                            error_msg_active = true;

                        }, response_data.retry_after * 1000);

                    } else {
                        // enable inputs
                        login_locked = false;
                        login_form.elements["username"].disabled = false;
                        login_form.elements["password"].disabled = false;
                        login_form.elements["remember"].disabled = false;
                        document.getElementById("login-submit-input").disabled = false;

                        // hide wait animation
                        document.querySelector(".vt-bars-anim-cont").setAttribute("class", "vt-bars-anim-cont hide-elem");

                        let elem = document.getElementById("err-msg-box");
                        elem.querySelector(".msg").innerHTML = "Error occured, check your connection.";
                        elem.removeAttribute("class");

                        error_msg_active = true;
                    }
                }
            );
        }
    }

    // get all the input element to attach events
    let inputs = document.getElementsByTagName("input");
    attachEventsToInputs(inputs);

    // attach event to page
    document.querySelector(".login-page").addEventListener("click", function (e) {
        // close error message
        if (error_msg_active) {
            error_msg_active = false;
            document.getElementById("err-msg-box").setAttribute("class", "hide-elem");
        }

    }, false);
}

//initialise the script
if (window.attachEvent) {
    window.attachEvent("onload", init);

} else {
    window.addEventListener("load", init, false);
}