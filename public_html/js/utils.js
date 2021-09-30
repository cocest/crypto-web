//This script contain all the javascript utilities function

(function () {
    // utility function to return days in a month of a particular year
    window.daysInMonth = function (m, y) { // m is 0 indexed: 0-11
        switch (m) {
            case 1:
                return (y % 4 == 0 && y % 100) || y % 400 == 0 ? 29 : 28;

            case 8:
            case 3:
            case 5:
            case 10:
                return 30;

            default:
                return 31;
        }
    };

    //utility function for setting cookies
    window.setCookie = function (cookie_name, cookie_value, exdays = 1, path = "path=/") {
        let date = new Date();
        date.setTime(date.getTime() + (exdays * 24 * 60 * 60 * 1000));
        let expires = "expires=" + date.toUTCString();
        document.cookie = cookie_name + "=" + cookie_value + "; " + expires + "; " + path;
    };

    //utility function for getting cookies
    window.getCookie = function (cookie_name) {
        let name = cookie_name + "=";
        let decodedCookie = decodeURIComponent(document.cookie);
        let ca = decodedCookie.split(';');

        for (let i = 0; i < ca.length; i++) {
            let c = ca[i];
            while (c.charAt(0) == ' ') {
                c = c.substring(1);
            }
            if (c.indexOf(name) == 0) {
                return c.substring(name.length, c.length);
            }
        }

        return "";
    };

    //get page scroll top position
    window.getPageScrollTop = function () {
        return document.body.scrollTop || document.documentElement.scrollTop;
    };

    //get page scroll left position
    window.getPageScrollLeft = function () {
        return document.body.scrollLeft || document.documentElement.scrollLeft;
    };

    // get element offset top in a document
    window.getElementOffset = function (el) {
        const rect = el.getBoundingClientRect();
      
        return {
            top: rect.top + window.pageYOffset || window.getPageScrollTop(),
            left: rect.left + window.pageXOffset || window.getPageScrollLeft(),
        };
    }

    //utility function to remove all child element
    window.removeAllChildElement = function (parent) {
        while (parent.firstChild) {
            parent.removeChild(parent.firstChild);
        }

        //OR 

        /*
        //this method is slower
        parent.innerHTML = "";
        */
    };

    //utility function for resetting form input to default state
    window.resetForm = function (forms_name) {
        for (let i = 0; i < forms_name.length; i++) {
            document.forms[forms_name[i]].reset();
        }
    };

    //utility function that allow only one check box to be selected
    window.allowOneCheckBox = function (id, check_boxes_id) {
        for (let i = 0; i < check_boxes_id.length; i++) {
            document.getElementById(check_boxes_id[i]).checked = false;
        }

        document.getElementById(id).checked = true;
    };

    // utility function to format time 12 hours with AM or PM
    window.toSTDTimeString = function (date = new Date(), include_seconds = true) {
        let hours = date.getHours();
        let minutes = date.getMinutes();
        let seconds = date.getSeconds();
        let ampm = hours >= 12 ? 'PM' : 'AM';
        hours = hours % 12;
        hours = hours ? hours : 12; // the hour '0' should be '12'
        minutes = ('0' + minutes).slice(-2);
        seconds = ('0' + seconds).slice(-2);
        if (include_seconds) {
            return hours + ':' + minutes + ':' + seconds + ' ' + ampm;
        } else {
            return hours + ':' + minutes + ' ' + ampm;
        }
    };

    // utility function to seperate number by thousands
    window.seperateNumberBy = function (number, seperator) {
        let splits = number.toString().trim().split('.');
        let decimal = splits[0];
        let fraction = splits.length > 1 ? '.' + splits[1] : '';
        let counter = 1;
        let formatted_number = '';

        // seperate decimal by thousand
        for (let i = decimal.length - 1; i >= 0; i--) {
            if (counter % 3 == 0 && i != 0) {
                formatted_number = seperator + decimal[i] + formatted_number;
            } else {
                formatted_number = decimal[i] + formatted_number;
            }

            counter++;
        }

        return formatted_number + fraction;
    };

    // get caret or cursor position in text input element
    window.getCaretPosition = function (txt_elem) {
        var caret_pos = 0;

        if (txt_elem.selectionStart || txt_elem.selectionStart == 0) {// Standard.
            caret_pos = txt_elem.selectionStart;
        }
        else if (document.selection) {// Legacy IE
            txt_elem.focus();
            var sel = document.selection.createRange();
            sel.moveStart('character', txt_elem.value.length * -1);
            caret_pos = sel.text.length;
        }

        return caret_pos;
    }


    // position caret or cursor to a set text offset in text input element
    window.setCaretPosition = function (txt_elem, pos) {
        if (txt_elem.setSelectionRange) {
            txt_elem.focus();
            txt_elem.setSelectionRange(pos, pos);
        }
        else if (txt_elem.createTextRange) {
            var range = txt_elem.createTextRange();
            range.collapse(true);
            range.moveEnd('character', pos);
            range.moveStart('character', pos);
            range.select();
        }
    }

    // utility function to determine when height of element changes
    window.onElementHeightChange = function (elem, callback) {
        let lastHeight = elem.clientHeight, newHeight;
      
        (function run() {
            newHeight = elem.clientHeight;
            if (lastHeight != newHeight) {
                callback(newHeight);
                lastHeight = newHeight;
            }
      
            if (elem.onElementHeightChangeTimer) {
                clearTimeout(elem.onElementHeightChangeTimer);
            }
      
            elem.onElementHeightChangeTimer = setTimeout(run, 200);
        })()
    }

    // utility function to get page full path
    window.getFullPathURL = function () {
        let url_scheme = window.location.protocol + "//" + window.location.host;
        let split_url_path = window.location.pathname.split("/");

        // return base URL
        return url_scheme + split_url_path.slice(0, split_url_path.length - 1).join("/") + "/";
    }

    // utility function to base URL
    window.getBaseURL = function () {
        return window.location.protocol + "//" + window.location.host + "/";
    }

    // utility function to send request to server
    window.ajaxRequest = function (_url, _form, _settings, _callback) {
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
    };
})()