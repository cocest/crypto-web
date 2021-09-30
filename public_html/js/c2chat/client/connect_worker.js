/* 
 * Connect user to any available agent online.
 * 
 */

// constants and variables here
let chat_id;
let wait = false;
let set_interval_handler;
let run_interval = 2000; // 2 seconds
let user_name;
let user_picture;
let user_email;
let department;
let message;
let server_url;
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

// connect to chat server
function connect() {
    /*form = new FormData();
    form.append("request", "connect_client");
    form.append("user_name", user_name);
    form.append("user_picture", user_picture);
    form.append("user_email", user_email);
    form.append("department", department);
    form.append("message", message);*/

    form = "request=connect_client&";
    form += "user_name=" + encodeURIComponent(user_name) + "&";
    form += "user_picture=" + encodeURIComponent(user_picture) + "&";
    form += "user_email=" + encodeURIComponent(user_email) + "&";
    form += "department=" + encodeURIComponent(department) + "&";
    form += "message=" + encodeURIComponent(message);

    // send request
    ajaxRequest(
        server_url,
        form,
        { contentType: "application/x-www-form-urlencoded" }, 
        function (response, status) {
            let response_data = JSON.parse(response);

            if (status == 200) {
                if (response_data.status == "connecting") {
                    chat_id = response_data.chat_id;

                    // send response back to client
                    self.postMessage({
                        response: { 
                            status: response_data.status
                        },
                        http: { status: status }
                    });

                    // start listening to when customer care accept the conection
                    startAwaitConnection();

                } else { // all the agent are busy
                    // send response back to client
                    self.postMessage({
                        response: { status: response_data.status },
                        http: { status: status }
                    });
                }

            } else if (status == 408 || status == 504 || status == 503) { // server is busy or timeout error
                // send response back to client
                self.postMessage({
                    http: { status: status }
                });

            } else { // no internet service or server error
                // send response back to client
                self.postMessage({
                    http: { status: status }
                });
            }
        }
    );
}

// check if agent have accepted the chat request at an interval
function startAwaitConnection() {
    // check if setInterval is not initiated
    if (typeof set_interval_handler == "undefined") {
        // run at every interval
        set_interval_handler = setInterval(() => {
            /*form = new FormData();
            form.append("request", "check_connection");
            form.append("chat_id", chat_id);*/

            form = "request=check_connection&";
            form += "chat_id=" + encodeURIComponent(chat_id);

            // ping the server
            awaitConnection();
        }, run_interval);
    }
}

// wait for agent to accept or decline the chat
function awaitConnection() {
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

                // check if agent have accept the connection
                if (response_data.status == "connected") {
                    // send response back to client
                    self.postMessage({
                        response: { 
                            status: response_data.status, 
                            chat_id: chat_id, 
                            agent_info: response_data.agent_info 
                        },
                        http: { status: status }
                    });

                    // clear the interval
                    clearInterval(set_interval_handler);

                } else if (response_data.status == "agent_unavailable") { // all the agent are busy
                    // send response back to client
                    self.postMessage({
                        response: { status: response_data.status, agent_info: null },
                        http: { status: status }
                    });

                    // clear the interval
                    clearInterval(set_interval_handler);
                }

                wait = false;

            } else if (status == 408 || status == 504) { // timeout error
                wait =  false;

            } else if (status == 503) { // server is busy
                // wait for 10 seconds
                setTimeout(() => { wait = false; }, 10000);

            } else { // no internet service or server error
                // send response back to client
                self.postMessage({
                    http: { status: status }
                });

                // clear the interval
                clearInterval(set_interval_handler);
            }
        }
    );
}

// listen to start initialisation
self.addEventListener("message", (e) => {
    server_url = e.data.server_url;
    user_name = e.data.user_name;
    user_picture = e.data.user_picture;
    user_email = e.data.user_email;
    department = e.data.department;
    message = e.data.message;

    // start connection to chat server
    connect();

}, false);