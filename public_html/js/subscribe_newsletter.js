function init() {
    // constants and variables here
    let input_value_error = false;
    let server_email_error = false;
    let close_error_msg_handler;
    let email_input = document.getElementById("footer-sub-newsletter-input");
    let message_panel = document.getElementById("footer-subscription-message");

    // validate user's entered email address
    function validateUserInputEmail(email_address) {
        let message = message_panel.querySelector('.message');
        let email_exp = pattern = /^(([^<>()\[\]\\.,;:\s@"]+(\.[^<>()\[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;

        if (email_exp.test(email_address)) {
            return true;
        }

        // invalid input
        input_value_error = true;

        // error message to user
        message_panel.setAttribute("class", "subscription-message");
        message.innerHTML = "Sorry, your email is not acceptable.";
        return false;
    }

    window.subscribeToNewsletter = function(btn) {
        // check if enetered email is valid
        if (!validateUserInputEmail(email_input.value)) {
            return;
        }

        // notify user subscription in progress
        btn.innerHTML = "Signing...";

        // disable subscribe button
        email_input.disabled = true;
        btn.disabled = true;

        let req_url = 'https://thecitadelcapital.com/request';
        let form_data = 'req=subscribe_newsletter&email=' + email_input.value; // request query

        // send request to server
        window.ajaxRequest(
            req_url,
            form_data,
            { contentType: "application/x-www-form-urlencoded" },

            // listen to response from the server
            function (response) {
                // enable button and input
                email_input.disabled = false;
                btn.disabled = false;
                btn.innerHTML = "Sign Up";

                // convert response to object
                let response_data = JSON.parse(response);

                if (response_data.success) {
                    // clear the email input
                    email_input.value = "";

                    // show success message to subscriber
                    let message = message_panel.querySelector('.message');
                    message.innerHTML = "Subscribed successfully.";
                    message_panel.setAttribute("class", "subscription-message");

                    close_error_msg_handler = setTimeout((e) => {
                        message_panel.setAttribute("class", "subscription-message remove-elem");
                    }, 4000);
                    

                } else if (response_data.already_subscribed) { // email already exist
                    // show a message to subscriber
                    let message = message_panel.querySelector('.message');
                    message.innerHTML = "You have already subscribed.";
                    message_panel.setAttribute("class", "subscription-message");

                    close_error_msg_handler = setTimeout((e) => {
                        message_panel.setAttribute("class", "subscription-message remove-elem");
                    }, 4000);

                } else {
                    // show error message to subscriber
                    let message = message_panel.querySelector('.message');
                    message.innerHTML = "Subscription to newsletter failed.";
                    message_panel.setAttribute("class", "subscription-message");

                    close_error_msg_handler = setTimeout((e) => {
                        message_panel.setAttribute("class", "subscription-message remove-elem");
                    }, 4000);
                }
            },

            // listen to server error
            function (err_status, msg) {
                // enable button and input
                email_input.disabled = false;
                btn.disabled = false;
                btn.innerHTML = "Sign Up";

                // check if is a timeout
                if (err_status == 408 || err_status == 504 || err_status == 503) {

                    // show error message to subscriber
                    let message = message_panel.querySelector('.message');
                    message.innerHTML = "Error occured, try it again.";
                    message_panel.setAttribute("class", "subscription-message");

                    close_error_msg_handler = setTimeout((e) => {
                        message_panel.setAttribute("class", "subscription-message remove-elem");
                    }, 4000);

                } else {
                    // show error message to subscriber
                    let message = message_panel.querySelector('.message');
                    message.innerHTML = "Error occured, check your internet.";
                    message_panel.setAttribute("class", "subscription-message");

                    close_error_msg_handler = setTimeout((e) => {
                        message_panel.setAttribute("class", "subscription-message remove-elem");
                    }, 4000);
                }
            }
        );
    };

    // listen to focus event
    email_input.addEventListener("focus", (e) => {
        // remove error message
        if (server_email_error) {
            server_email_error = false;
            clearTimeout(close_error_msg_handler);
            message_panel.setAttribute("class", "subscription-message remove-elem");
        }

    }, false);

    // listen to keydown event
    email_input.addEventListener("keydown", (e) => {
        // remove error message
        if (input_value_error) {
            input_value_error = false;
            message_panel.setAttribute("class", "subscription-message remove-elem");
        }

    }, false);
}

// initialise the script
if (window.attachEvent) {
    window.attachEvent("onload", init);

} else {
    window.addEventListener("load", init, false);
}