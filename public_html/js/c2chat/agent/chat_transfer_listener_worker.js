/* 
 * The script retrieve new chat transfer request and 
 * closed chat transfer request.
 * 
 */

// constants and variables here
const _CHAT_TRANSFER_REQUEST = 10;
const _CLOSED_CHAT_TRANSFER = 11;

let last_check_time_offset = 0;
let wait = false;
let set_interval_handler;
let run_interval = 5000; // 5 seconds
let server_url;
let agent_id;
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

// retrieve all the new chat transfer request and closed request
function chatTransferRequest() {
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
                    last_check_time_offset = response_data.time_offset; 

                    // send data back to client
                    if (typeof response_data.request != "undefined") {
                        self.postMessage({
                            event_type: _CHAT_TRANSFER_REQUEST,
                            list: response_data.request
                        });
                    }

                    if (typeof response_data.closed_request != "undefined") {
                        self.postMessage({
                            event_type: _CLOSED_CHAT_TRANSFER,
                            list: response_data.closed_request
                        });
                    }
                }

                wait = false;

            } else if (status == 504 || status == 503) { // server is busy
                // wait for 10 seconds
                setTimeout(() => { wait = false; }, 10000);

            } else {
                wait =  false;
            }
        }
    );
}

// listen to start initialisation
self.addEventListener("message", (e) => {
    server_url = e.data.server_url;
    agent_id = e.data.agent_id;

    // check if setInterval is not initiated
    if (typeof set_interval_handler == "undefined") {
        // run at every interval
        set_interval_handler = setInterval(() => {
            /*form = new FormData();
            form.append("request", "chat_transfer_state");
            form.append("agent_id", agent_id);
            form.append("time_offset", last_check_time_offset);*/

            form = "request=chat_transfer_state&";
            form += "agent_id=" + encodeURIComponent(agent_id) + "&";
            form += "time_offset=" + encodeURIComponent(last_check_time_offset);

            chatTransferRequest();
        }, run_interval);
    }

}, false);