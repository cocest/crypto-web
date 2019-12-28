function init() {
    // variables here
    let close_element_handler = null;
    let close_element_id;
    let close_element_toggle_state = {
        "help-drop-down-menu": false,
        "user-drop-down-menu": false
    };
    let notification_msg_map = new Map();
    let notification_max_list = 5;
    let load_prev_msg_offset;
    let fetch_new_notification;
    let notification_first_load = true;
    let notification_first_run = true;

    // show show and hide page menu
    window.showPageSideMenu = function (elem) {
        let side_menu_elem = document.getElementById("page-left-menu-cont");

        if (elem.getAttribute("toggle") == 0) { // hide the menu
            elem.setAttribute("toggle", "1");
            elem.setAttribute("class", "show-side-menu-icon open");
            side_menu_elem.setAttribute("class", "show-menu");

        } else {
            elem.setAttribute("toggle", "0");
            elem.setAttribute("class", "show-side-menu-icon close");
            side_menu_elem.setAttribute("class", "hide-menu");
        }
    };

    // clip unffitted text out
    function clipOutText(txt_length, text) {
        let clipped_text = text;
        let text_clipped = false;

        if (txt_length < text.length) {
            if (text[txt_length - 1] == ' ') {
                clipped_text = text.substring(0, txt_length - 1);

            } else { // iterate forward until you found whitespace
                for (let i = txt_length; i < text.length; i++) {
                    if (text[i] == ' ') {
                        clipped_text = text.substring(0, i);
                        break;
                    }
                }
            }

            clipped_text += '...';
            text_clipped = true;
        }

        return [clipped_text, text_clipped];
    }

    // show user drop down menu
    window.showUserDropDownMenu = function (e) {
        let elem = document.getElementById('user-drop-down-menu-cont');

        // close active menu
        if (close_element_handler != null && close_element_id != 'user-drop-down-menu') {
            close_element_handler.setAttribute("class", "remove-elem");
            close_element_handler = null;
            close_element_toggle_state[close_element_id] = false;
        }

        if (close_element_toggle_state['user-drop-down-menu']) {
            close_element_toggle_state['user-drop-down-menu'] = false;
            elem.setAttribute("class", "remove-elem");

        } else {
            close_element_toggle_state['user-drop-down-menu'] = true;
            elem.removeAttribute("class");
        }

        close_element_id = "user-drop-down-menu";
        close_element_handler = elem;
    };

    // show help drop down menu
    window.showHelpDropDownMenu = function () {
        let elem = document.getElementById('help-drop-down-menu-cont');

        // close active menu
        if (close_element_handler != null && close_element_id != 'help-drop-down-menu') {
            close_element_handler.setAttribute("class", "remove-elem");
            close_element_handler = null;
            close_element_toggle_state[close_element_id] = false;
        }

        if (close_element_toggle_state['help-drop-down-menu']) {
            close_element_toggle_state['help-drop-down-menu'] = false;
            elem.setAttribute("class", "remove-elem");

        } else {
            close_element_toggle_state['help-drop-down-menu'] = true;
            elem.removeAttribute("class");
        }

        close_element_id = "help-drop-down-menu";
        close_element_handler = elem;
    };

    // open window
    window.openWin = function (win) {
        closeActiveMenu();

        let elem = document.getElementById(win);
        elem.removeAttribute("class");
    };

    // close active window
    window.closeActiveWin = function (win) {
        let elem = document.getElementById(win);
        elem.setAttribute("class", "remove-elem");
    };

    // load previous notification if there is any
    window.loadPreviousNotification = function () {
        let req_url = '../../request';
        let form_data =
            'req=get_prev_notification&time_offset=' + load_prev_msg_offset +
            '&limit= ' + notification_max_list; // request query

        // hide load more button and show loading animation
        document.getElementById("load-prev-notification").setAttribute("class", "remove-elem");
        document.getElementById("loading-notification-anim-cont").removeAttribute("class");

        // send request to server
        window.ajaxRequest(
            req_url,
            form_data,
            { contentType: "application/x-www-form-urlencoded" },

            // listen to response from the server
            function (response) {
                appendLoadedNotification(JSON.parse(response).messages);
            },

            // listen to server error
            function (err_status) {
                // check if is timeout error
                if (err_status == 408 && err_status == 504) {
                    window.loadPreviousNotification();

                } else if (err_status == 503) { // check if server is busy or unavalaible
                    // wait for 2 minutes
                    setTimeout(function () {
                        window.loadPreviousNotification();

                    }, 60000 * 2);

                } else { // other error here
                    // show load more button and hide loading animation
                    document.getElementById("load-prev-notification").removeAttribute("class");
                    document.getElementById("loading-notification-anim-cont").setAttribute("class", "remove-elem");
                }
            }
        );
    };

    // expand notification message
    window.expandNotificationMsg = function (msg_id) {
        let elem = document.getElementById(msg_id);
        let msg_body = elem.querySelector('.msg-body');
        let expand_btn = elem.querySelector('.expand-msg-btn');

        if (msg_body.getAttribute("toggle") == 0) { // expand
            msg_body.innerHTML = notification_msg_map.get(msg_id);
            msg_body.setAttribute("toggle", "1");
            expand_btn.setAttribute("class", "expand-msg-btn collapse");

        } else { // collapse
            let [clipped_text, is_text_clipped] = clipOutText(90, notification_msg_map.get(msg_id));
            msg_body.innerHTML = clipped_text;
            msg_body.setAttribute("toggle", "0");
            expand_btn.setAttribute("class", "expand-msg-btn expand");
        }
    };

    // utility function to append fetch notification
    function appendLoadedNotification(messages) {
        let item;
        let msg_date;
        let ref_child_elem = document.getElementById("load-prev-notification");

        // hide loading animation
        document.getElementById("loading-notification-anim-cont").setAttribute("class", "remove-elem");

        // check if there more message to load
        if (messages.length >= notification_max_list) {
            ref_child_elem.removeAttribute("class"); // show load more button
        }

        // check if there is loaded message
        if (messages.length > 0) {
            load_prev_msg_offset = messages[messages.length - 1].time;
        }

        // append the message to the list
        for (let i = 0; i < messages.length; i++) {
            msg_date = new Date(parseInt(data.messages[i].time) * 1000);
            let [clipped_text, is_text_clipped] = clipOutText(90, messages[i].content);

            item = document.createElement("div");
            item.setAttribute("id", messages[i].id)
            item.setAttribute("class", "item-cont");
            item.innerHTML =
                `<div class="title-bar-cont">
                     <div class="msg-title">${messages[i].title}</div>
                     <ul class="msg-action-btn-cont">
                        <li class="mark-msg ${messages[i].read ? 'read' : 'no-read'}" title="Mark message as read" ${messages[i].read ? '' : 'onclick="processUserCommandNotification(\'' + messages[i].id + '\', \'markAsRead\', ' + messages[i].read + ')"'}>
                            <img src="../../images/icons/icons_sprite_2.png" />
                        </li>
                        <li class="delete-msg" title="Delete message" onclick="processUserCommandNotification('${messages[i].id}', 'deleteMsg', ${messages[i].read})">
                            <img src="../../images/icons/icons_sprite_2.png" />
                        </li>
                    </ul>
                </div>
                <div class="msg-body" toggle="0">${clipped_text}</div>
                <div class="footer">
                    ${
                is_text_clipped ?
                    '<div class="expand-msg-btn expand" title="Expand or collapse the message" onclick="expandNotificationMsg(\'' + messages[i].id + '\')">' +
                    '    <img src="../../images/icons/icons_sprite_2.png" />' +
                    '</div>' : ''
                }
                <div class="msg-date">${msg_date.getMonth() + 1}/${msg_date.getDate()}/${msg_date.getFullYear()} ${window.toSTDTimeString(msg_date, false)}</div>
                </div>`;

            // add message to list
            list_cont.insertBefore(item, ref_child_elem);

            // add message to map
            notification_msg_map.set(messages[i].id, messages[i].content);
        }
    }

    // utiility function to update numbers of unread messages and 
    // insert new message into message list
    function updateNotification(data) {
        let unread_msg_counter_label = document.getElementById("unread-msg-counter");
        let list_cont = document.getElementById("notification-list-cont");
        let item;
        let msg_date;
        let ref_child_elem = notification_first_load ? document.getElementById("load-prev-notification") : list_cont.children[0];

        // update numbers of unread messages counter
        document.getElementById("unread-msg-counter").innerHTML = data.unread_msg_count;

        // check if this function is called the first time
        if (notification_first_run) {
            notification_first_run = false;

            // hide loading animation
            document.getElementById("loading-notification-anim-cont").setAttribute("class", "remove-elem");

            // check if user has no notification
            if (data.messages.length < 1) {
                document.getElementById("notification-status-msg").removeAttribute("class");
            }
        }

        // check if message is loaded the first time
        if (notification_first_load) {
            if (data.messages.length > 0) {
                notification_first_load = false;

                load_prev_msg_offset = data.messages[data.messages.length - 1].time;

                // show uread message counter
                unread_msg_counter_label.setAttribute("class", "count");

                // hide "no notification" message
                document.getElementById("notification-status-msg").setAttribute("class", "remove-elem");

                // check if there is more message to load
                if (data.messages.length >= notification_max_list) {
                    ref_child_elem.removeAttribute("class");
                }
            }
        }

        // append the message to the list
        for (let i = 0; i < data.messages.length; i++) {
            msg_date = new Date(parseInt(data.messages[i].time) * 1000);
            let [clipped_text, is_text_clipped] = clipOutText(90, data.messages[i].content);

            item = document.createElement("div");
            item.setAttribute("id", data.messages[i].id)
            item.setAttribute("class", "item-cont");
            item.innerHTML =
                `<div class="title-bar-cont">
                     <div class="msg-title">${data.messages[i].title}</div>
                     <ul class="msg-action-btn-cont">
                        <li class="mark-msg ${data.messages[i].read ? 'read' : 'no-read'}" title="Mark message as read" ${data.messages[i].read ? '' : 'onclick="processUserCommandNotification(\'' + data.messages[i].id + '\', \'markAsRead\', ' + data.messages[i].read + ')"'}>
                            <img src="../../images/icons/icons_sprite_2.png" />
                        </li>
                        <li class="delete-msg" title="Delete message" onclick="processUserCommandNotification('${data.messages[i].id}', 'deleteMsg', ${data.messages[i].read})">
                            <img src="../../images/icons/icons_sprite_2.png" />
                        </li>
                    </ul>
                </div>
                <div class="msg-body" toggle="0">${clipped_text}</div>
                <div class="footer">
                    ${
                is_text_clipped ?
                    '<div class="expand-msg-btn expand" title="Expand or collapse the message" onclick="expandNotificationMsg(\'' + data.messages[i].id + '\')">' +
                    '    <img src="../../images/icons/icons_sprite_2.png" />' +
                    '</div>' : ''
                }
                    <div class="msg-date">${msg_date.getMonth() + 1}/${msg_date.getDate()}/${msg_date.getFullYear()} ${window.toSTDTimeString(msg_date, false)}</div>
                </div>`;

            // add message to list
            list_cont.insertBefore(item, ref_child_elem);

            // add message to map
            notification_msg_map.set(data.messages[i].id, data.messages[i].content);
        }

        // update uread message count
        unread_msg_counter_label.innerHTML = data.unread_msg_count;
    }

    // process user's command on notification
    window.processUserCommandNotification = function (msg_id, command, count_down) {
        let list_cont = document.getElementById("notification-list-cont");
        let unread_msg_counter_label = document.getElementById("unread-msg-counter");
        let req_url = '../../request';
        let form_data;

        if (command == "markAsRead") {
            // request query
            form_data = "req=read_notification&msg_id=" + msg_id;

            // mark the notification as read
            document.getElementById(msg_id).querySelector('.mark-msg').setAttribute("class", "mark-msg read");

        } else if (command == "deleteMsg") {
            // request query
            form_data = "req=delete_notification&msg_id=" + msg_id;

            // delete the notification
            list_cont.removeChild(document.getElementById(msg_id));
            notification_msg_map.delete(msg_id);
        }

        if (!count_down) {
            if (parseInt(unread_msg_counter_label.innerText) == 1) {
                unread_msg_counter_label.setAttribute("class", "count remove-elem");

            } else {
                unread_msg_counter_label.innerHTML = parseInt(unread_msg_counter_label.innerText) - 1;
            }
        }

        // send request to server
        window.ajaxRequest(
            req_url,
            form_data,
            { contentType: "application/x-www-form-urlencoded" },

            // listen to response from the server
            function (response) {
                // leave it empty
            },

            // listen to server error
            function (err_status) {
                // leave it empty
            }
        );
    };

    // resend verification to user's email again
    window.resendEmailVerification = function () {
        let req_url = '../../request';
        let form_data = 'req=resend_email_verification'; // request query

        // hide resend button and show resending animation
        document.querySelector('.email-resend-btn-cont').setAttribute("class", "email-resend-btn-cont remove-elem");
        document.querySelector('.resend-email-anim-cont').setAttribute("class", "resend-email-anim-cont");

        // send request to server
        window.ajaxRequest(
            req_url,
            form_data,
            { contentType: "application/x-www-form-urlencoded" },

            // listen to response from the server
            function (response) {
                // show resend button and hide resending animation
                document.querySelector('.email-resend-btn-cont').setAttribute("class", "email-resend-btn-cont");
                document.querySelector('.resend-email-anim-cont').setAttribute("class", "resend-email-anim-cont remove-elem");
            },

            // listen to server error
            function (err_status) {
                // check if is timeout error
                if (err_status == 408 && err_status == 504) {
                    window.resendEmailVerification();

                } else if (err_status == 503) { // check if server is busy or unavalaible
                    // wait for 2 minutes
                    setTimeout(function () {
                        window.resendEmailVerification();

                    }, 60000 * 2);

                } else {
                    // show resend button and hide resending animation
                    document.querySelector('.email-resend-btn-cont').setAttribute("class", "email-resend-btn-cont");
                    document.querySelector('.resend-email-anim-cont').setAttribute("class", "resend-email-anim-cont remove-elem");
                }
            }
        );
    };

    // fetch new notification from server
    function fetchNewNotification() {
        if (typeof fetch_new_notification == "undefined") {
            fetch_new_notification = new Worker("../../js/fetchNewNotificationWorker.js");

            // initialise fetch of new notification
            fetch_new_notification.postMessage({ msg_limit: notification_max_list });

            // listen to when data is sent
            fetch_new_notification.addEventListener("message", function (event) {
                updateNotification(event.data);
            }, false);
        }
    }

    // utility function to close menu window
    function closeActiveMenu() {
        if (close_element_handler != null) {
            close_element_handler.setAttribute("class", "remove-elem");
            close_element_handler = null;
            close_element_toggle_state[close_element_id] = false;
        }
    }

    // adapt page content height
    function adaptPageContent () {
        let elem = document.querySelector('.page-content-cont');
        elem.removeAttribute("style");
        let padding_h = 90; // content top + bottom padding
        let ch = elem.offsetHeight;
        let wh = window.innerHeight;

        // check to fit content height to window height
        if (ch < wh) {
            elem.setAttribute("style", "height: " + (wh - padding_h) + "px;");
        }
    }

    // set page side menu scroll wrapper height on page load or resize
    function adaptPageSideMenu () {
        let page_top_menu_height = document.querySelector('.page-top-menu-cont').offsetHeight;
        let wh = window.innerHeight;
        let elem = document.querySelector('#page-left-menu-cont #scroll-wrapper');

        // set container height
        elem.setAttribute("style", "height: " + (wh - page_top_menu_height) + "px;");
    }

    // listen to when user click the section
    window.sectionClickEvent = function (e) {
        closeActiveMenu();
    };

    // call function onload
    adaptPageSideMenu();
    adaptPageContent();
    fetchNewNotification();

    // listen to page resize
    window.onresize = function (e) {
        adaptPageSideMenu();
        adaptPageContent();
    };
}

//initialise the script
if (window.attachEvent) {
    window.attachEvent("onload", init);

} else {
    window.addEventListener("load", init, false);
}