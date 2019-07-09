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

    //utility function that allow only one check box be selected
    window.allowOneCheckBox = function (id, check_boxes_id) {
        for (let i = 0; i < check_boxes_id.length; i++) {
            document.getElementById(check_boxes_id[i]).checked = false;
        }

        document.getElementById(id).checked = true;
    };
})()