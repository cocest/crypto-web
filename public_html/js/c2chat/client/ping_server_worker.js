/* 
 * Constantly ping the chat server to maintain the connection.
 * 
 */

// constants and variables here
let chat_id;
let wait = false;
let set_interval_handler;
let run_interval = 15000; // 15 seconds
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

// ping the chat server at an interval
function ping() {
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
                wait = false;

            } else if (status == 408 || status == 504) { // timeout error
                wait =  false;

            } else if (status == 503) { // server is busy
                // wait for 10 seconds
                setTimeout(() => { wait = false; }, 10000);
            }
        }
    );
}

// listen to start initialisation
self.addEventListener("message", (e) => {
    server_url = e.data.server_url;
    chat_id = e.data.chat_id;

    // check if setInterval is not initiated
    if (typeof set_interval_handler == "undefined") {
        // run at every interval
        set_interval_handler = setInterval(() => {
            /*form = new FormData();
            form.append("request", "ping");
            form.append("request_user", "client");
            form.append("chat_id", chat_id);*/

            form = "request=ping&";
            form += "request_user=client&";
            form += "chat_id=" + encodeURIComponent(chat_id);

            // ping the server
            ping();
        }, run_interval);
    }

}, false);