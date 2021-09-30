/* 
 * The script retrieve new chat transfer request and 
 * closed chat transfer request.
 * 
 */

// constants and variables here
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
function chatTransferAccepted() {
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

                // send message to caller
                self.postMessage({ status: response_data.status });

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
            form.append("request", "chat_transfer_accepted");
            form.append("agent_id", agent_id);*/

            form = "request=chat_transfer_accepted&";
            form += "agent_id=" + encodeURIComponent(agent_id);

            chatTransferAccepted();
        }, run_interval);
    }

}, false);