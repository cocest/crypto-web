//This script contain all the javascript utilities function

(function () {
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
    window.toSTDTimeString = function (date = new Date()) {
        let hours = date.getHours();
        let minutes = date.getMinutes();
        let seconds = date.getSeconds();
        let ampm = hours >= 12 ? 'PM' : 'AM';
        hours = hours % 12;
        hours = hours ? hours : 12; // the hour '0' should be '12'
        minutes = ('0' + minutes).slice(-2);
        seconds = ('0' + seconds).slice(-2);
        let strTime = hours + ':' + minutes + ':' + seconds + ' ' + ampm;
        return strTime;
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

    // utility function to send request to server
    window.ajaxRequest = function (_url, _form, _send_callback, _err_callback) {
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
    };
})()