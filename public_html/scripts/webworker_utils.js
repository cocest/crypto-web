/*
 * This contain all the necessary utility function for Web Worker
 * Note: DOM element or function can't be reference in this script
 */

function ajaxRequest(_url, _form, _send_callback, _err_callback) {
    // code for IE7+, Firefox, Chrome, Opera, Safari
    let xmlhttp = new XMLHttpRequest();

    try {
        // send request to server
        xmlhttp.open("POST", _url, true);
        xmlhttp.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
        xmlhttp.send(_form);

        // response on state change and return the responds
        xmlhttp.onreadystatechange = function () {
            if (xmlhttp.readyState === 4 && xmlhttp.status === 200) {
                _send_callback(xmlhttp.responseText);
            }
            else if (xmlhttp.status !== 200) { // handle server error
                _err_callback(xmlhttp.status);
            }
        };
    }
    catch (err) { // catch client error
        console.error(err);
    }
}