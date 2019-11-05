function init() {
    // variables here
    let user_menu_active = false;
    let help_menu_active = false;
    let fetch_new_notification;

    // show user drop down menu
    window.showUserDropDownMenu = function() {
        let elem = document.querySelector('.user-drop-down-menu-cont');

        if (user_menu_active) {
            user_menu_active = false;
            elem.setAttribute("class", "user-drop-down-menu-cont remove-elem");

        } else {
            user_menu_active = true;
            elem.setAttribute("class", "user-drop-down-menu-cont");
        }
    };

    // show help drop down menu
    window.showHelpDropDownMenu = function() {
        let elem = document.querySelector('.help-drop-down-menu-cont');

        if (help_menu_active) {
            help_menu_active = false;
            elem.setAttribute("class", "help-drop-down-menu-cont remove-elem");

        } else {
            help_menu_active = true;
            elem.setAttribute("class", "help-drop-down-menu-cont");
        }
    };

    // open window
    window.openWin = function(win) {
        let elem = document.getElementById(win);
        elem.removeAttribute("class");
    };

    // close active window
    window.closeActiveWin = function(win) {
        let elem = document.getElementById(win);
        elem.setAttribute("class", "remove-elem");
    };

    // utiility function to update numbers of unread messages and 
    // insert new message into message list
    function updateNotification(data) {
        let list_cont = document.getElementById("notification-list-cont");
        let item;
        let ref_child_elem = list_cont.children.length > 0 ? list_cont.children[0] : null;

        // update numbers of unread messages counter
        document.getElementById("unread-msg-counter").innerHTML = data.unread_msg_count;

        // append the message to the list
        for (let i = 0; i < data.messages.length; i++) {
            item = document.createElement("div");
            item.setAttribute("id", data.messages[i].id)
            item.setAttribute("class", "item-cont");
            item.innerHTML = 
                `<div class="title-bar-cont">
                     <div class="msg-title">Verify Your Email</div>
                     <ul class="msg-action-btn-cont">
                        <li class="mark-msg no-read">
                            <img src="../../images/icons/icons_sprite_2.png" />
                        </li>
                        <li class="delete-msg">
                            <img src="../../images/icons/icons_sprite_2.png" />
                        </li>
                    </ul>
                </div>
                <div class="msg-body"></div>
                <div class="footer">
                    <div class="expand-msg-btn expand">
                        <img src="../../images/icons/icons_sprite_2.png" />
                    </div>
                    <div class="msg-date">10/18/2019 08:22 PM</div>
                </div>`;

            // add message to list
            if (list_cont.children.length < 1) {
                list_cont.appendChild(item);

            } else {
                list_cont.insertBefore(item, ref_child_elem);
            }
        }
    }

    // fetch new notification from server
    function fetchNewNotification() {
        if (typeof fetch_new_notification == "undefined") {
            fetch_new_notification = new Worker("js/fetchNewNotificationWorker.js");

            // listen to when data is sent
            fetch_new_notification.addEventListener("message", function(event) {
                updateNotification(event.data);
            });
        }
    }
}

//initialise the script
if (window.attachEvent) {
    window.attachEvent("onload", init);

} else {
    window.addEventListener("load", init, false);
}