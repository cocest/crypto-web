/* 
 * Constantly check for new messsage and retrieve them.
 * 
 */

// constants and variables here
let chat_id;
let last_send_msg_time_offset = 0;
let wait = false;
let set_interval_handler;
let run_interval = 2000; // 2 seconds
let server_url;
let request;
let form;

// utility function to send request to server
function ajaxRequest(_url, _form, _settings, _callback) {
    // code for IE7+, Firefox, Chrome, Opera, Safari
    let xmlhttp = new XMLHttpRequest();

    try {
        if (_settings == null) _settings = {};

        // set send method
        if (typeof _settings.method != "undefined") {
            xmlhttp.open(_settings.method, _url, true);

        } else { // default
            xmlhttp.open("POST", _url, true);
        }

        // set content type
        if (typeof _settings.contentType != "undefined") {
            if (_settings.contentType) {
                xmlhttp.setRequestHeader("Content-type", _settings.contentType);
            }

        } else { // default
            xmlhttp.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
        }

        // set custom headers
        if (typeof _settings.headers != "undefined") {
            let headers = _settings.headers;
            for (let i = 0; i < headers.length; i++) {
                xmlhttp.setRequestHeader(headers[i][0], headers[i][1]);
            }
        }

        // send request to server
        if (_form == null) {
            xmlhttp.send();

        } else {
            xmlhttp.send(_form);
        }

        // response on state change and return the responds
        xmlhttp.onreadystatechange = function () {
            if (xmlhttp.readyState === 4 && xmlhttp.status === 200) {
                _callback(xmlhttp.responseText, xmlhttp.status);

            } else if (xmlhttp.status !== 200) {
                _callback(null, xmlhttp.status);
            }
        };
    }
    catch (err) { // catch client error
        console.error(err);
    }
}

// retrieve sent message from the chat server
function retrieveMessage() {
    // wait for server to response
    if (wait) {
        return;
    }

    wait = true;

    // send request
    ajaxRequest(
        server_url,
        form,
        { contentType: "application/x-www-form-urlencoded" }, 
        function (response, status) {
            if (status == 200) {
                let response_data = JSON.parse(response);

                // check if request is successfull
                if (response_data.status == "ok") {
                    // update time offset for next retrieval
                    last_send_msg_time_offset = response_data.time_offset; 

                    // send recieved message back to client
                    self.postMessage({
                        status: response_data.status,
                        messages: response_data.messages
                    });

                } else if (response_data.status == "disconnected") {
                    self.postMessage({
                        status: response_data.status,
                        messages: null
                    });

                } else { // connection lost
                    self.postMessage({
                        status: response_data.status,
                        messages: null
                    });
                }

                wait = false;

            } else if (status == 408 || status == 504) { // timeout error
                wait =  false;

            } else if (status == 503) { // server is busy
                // wait for 5 seconds
                setTimeout(() => { wait = false; }, 5000);

            } else {
                self.postMessage({
                    status: "connection_lost",
                    messages: null
                });
            }
        }
    );
}

// listen to start initialisation
self.addEventListener("message", (e) => {
    server_url = e.data.server_url;
    chat_id = e.data.chat_id;
    last_send_msg_time_offset = e.data.retrieve_time_offset;

    if (e.data.req_user == "client") {
        request = "retrieve_agent_msg";

    } else if (e.data.req_user == "agent") {
        request = "retrieve_client_msg";
    }

    // check if setInterval is not initiated
    if (typeof set_interval_handler == "undefined") {
        // run at every interval
        set_interval_handler = setInterval(() => {
            /*form = new FormData();
            form.append("request", request);
            form.append("chat_id", chat_id);
            form.append("time_offset", last_send_msg_time_offset);*/

            form = "request=" + encodeURIComponent(request) + "&";
            form += "chat_id=" + encodeURIComponent(chat_id) + "&";
            form += "time_offset=" + encodeURIComponent(last_send_msg_time_offset);

            // retrieve sent message from server
            retrieveMessage();
        }, run_interval);
    }

}, false);