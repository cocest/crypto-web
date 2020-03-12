/* 
 * Web Worker to retrieve new testimony from the server
 * Please don't edit these code if don't know what your doing
 * 
 */

importScripts('./webworker_utils.js');

const req_url = '../request';
let form_data;
let temp_time_offset;
let max_msg;
let response_data;
let interval_running = false;
let call_interval = 15000 // every 15 seconds
let wait = false;

// send request to server at every interval
function sendRequestAtInterval() {
    setInterval(function () {
        sendRequest();

    }, call_interval); // every 15 seconds
}

function sendRequest() {
    // check to wait for server to send response
    if (wait) {
        return;

    } else {
        wait = true;
    }

    // set form data
    form_data = 'req=fetch_new_testimony&time_offset=' + temp_time_offset + '&limit=' + max_msg;

    // send request to server
    ajaxRequest(
        req_url,
        form_data,
        { contentType: "application/x-www-form-urlencoded" },

        // listen to response from the server
        function (response) {
            wait = false; // unwait

            response_data = JSON.parse(response);

            // set offset for next fetch
            if (response_data.testimonies.length > 0) {
                temp_time_offset = response_data.testimonies[0].time;
            }

            // send the retrieved message(s) to listener
            self.postMessage(response_data);

            // start sending the request at every interval
            if (!interval_running) {
                interval_running = true;
                sendRequestAtInterval();
            }
        },

        // listen to server error
        function (err_status) {
            // check if is timeout error
            if (err_status == 408 && err_status == 504) {
                wait = false; // unwait

            } else if (err_status == 503) { // check if server is busy or unavalaible
                // check if call inteval value is less than a minute
                if (call_interval <= 60000) {
                    // wait for 2 minutes
                    setTimeout(function () { wait = false; }, 60000 * 2);

                } else {
                    wait = false; // unwait
                }

            } else { // other error here
                wait = false; // unwait
            }
        }
    );
}

// listen to start initialisation message and send request to server
self.addEventListener("message", function (event) {
    temp_time_offset = event.data.time_offset; // set start time offset
    max_msg = event.data.msg_limit; // set max message to fetch every instance
    sendRequest(); // start request
}, false);