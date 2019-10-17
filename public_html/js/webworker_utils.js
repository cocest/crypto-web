/*
 * This contain all the necessary utility function for Web Worker
 * Note: DOM element or function can't be reference in this script
 */

function ajaxRequest(_url, _form, _settings, _send_callback, _err_callback) {
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

        // send request to server
        if (_form == null) {
            xmlhttp.send();

        } else {
            xmlhttp.send(_form);
        }

        // response on state change and return the responds
        xmlhttp.onreadystatechange = function () {
            if (xmlhttp.readyState === 4 && xmlhttp.status === 200) {
                _send_callback(xmlhttp.responseText);
            }
            else if (xmlhttp.status !== 200) { // handle server error
                _err_callback(xmlhttp.status, xmlhttp.responseText);
            }
        };
    }
    catch (err) { // catch client error
        console.error(err);
    }
}