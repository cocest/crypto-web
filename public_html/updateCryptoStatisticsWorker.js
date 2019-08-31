/* 
 * Web Worker to retrieve update from the server
 * Please don't edit these code if don't know what your doing
 * 
 */

importScripts('./scripts/webworker_utils.js');

const req_url = 'process_request.php';
let form_data = 'request=GET_CRYPTO_PRICES'; // request query
let response_data;
let interval_running = false;
let call_interval = 60000 * 2; // every 2 minutes

// send request to server at every interval
function sendRequestAtInterval() {
    setInterval(function() {
        sendRequest();

    }, call_interval); // every 2 minutes
}

function sendRequest() {
    // check to wait for server to send response
    if (wait) {
        return;

    } else {
        wait = true;
    }

    // send request to server
    ajaxRequest(
        req_url,
        form_data,

        // listen to response from the server
        function (response) {
            wait = false; // unwait

            response_data = JSON.parse(response);

            //send the retrieved message(s) to listener
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
                    // wait for 5 minutes
                    setTimeout(function() {wait = false;}, 60000 * 5);

                } else {
                    wait = false; // unwait
                }

            } else { // other error here
                wait = false; // unwait
            }
        }
    );
}

// send request to server
sendRequest();