/* 
 * The script check for client chat request. It check if the 
 * client chat request has been handled by another agent, and 
 * also check if client request chat session is terminated.
 * 
 */

// constants and variables here
const _CLIENT_CONNECTED = 10;
const _CLIENT_DISCONNECTED = 11;
const _CLIENT_HANDLED = 12;

let last_check_time_offset = 0;
let wait = false;
let set_interval_handler;
let run_interval = 5000; // 5 seconds
let server_url;
let department;
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

// retrieve all the client their connection state changed
function getClientConnectionState() {
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
                    if (typeof response_data.connected_client != "undefined") {
                        self.postMessage({
                            event_type: _CLIENT_CONNECTED,
                            list: response_data.connected_client
                        });
                    }

                    if (typeof response_data.disconnected_client != "undefined") {
                        self.postMessage({
                            event_type: _CLIENT_DISCONNECTED,
                            list: response_data.disconnected_client
                        });
                    }

                    if (typeof response_data.handled_client != "undefined") {
                        self.postMessage({
                            event_type: _CLIENT_HANDLED,
                            list: response_data.handled_client
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
    department = e.data.department;

    // check if setInterval is not initiated
    if (typeof set_interval_handler == "undefined") {
        // run at every interval
        set_interval_handler = setInterval(() => {
            /*form = new FormData();
            form.append("request", "client_connect_state");
            form.append("department", department);
            form.append("time_offset", last_check_time_offset);*/

            form = "request=client_connect_state&";
            form += "department=" + encodeURIComponent(department) + "&";
            form += "time_offset=" + encodeURIComponent(last_check_time_offset);

            getClientConnectionState();
        }, run_interval);
    }

}, false);