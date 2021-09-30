function init() {
    // constants and variables
    let active_drop_menu = null;
    let active_drop_menu_id = "";
    let closed_active_drop_menu = {
        "drop-down-more-menu": false
    };
    let placeholder_toggle = true;
    let temp_init_chat_inputs = [];
    let c2chat_launch_btn = document.getElementById("c2chat-launch-btn");
    let initiating_chat = false;
    let init_chat_form = document.forms["c2chat-init-chat-form"];
    let is_client_connected = false;
    let chat_view_cont = document.querySelector('#c2chat-client-chat-win .chat-frame-cont');
    let chat_msg_panel = document.querySelector('#c2chat-client-chat-win .chat-msg-cont');
    let chat_text_area = document.querySelector('#c2chat-client-chat-win .chat-text-area-cont .text-area');
    let chat_text_area_placeholder = document.querySelector('#c2chat-client-chat-win .chat-text-area-cont .text-area-placeholder');
    let client_info;
    let client_prev_sent_msg_time = 0;
    let client_curr_sent_msg_time = 0;
    let is_page_in_view = true;

    // load c2chat message sounds
    let sound = new Howl({
        src: [
            'http://localhost/workspace/thecitadelcapital/crypto-web/public_html/sounds/juntos-607.mp3', 
            'http://localhost/workspace/thecitadelcapital/crypto-web/public_html/sounds/juntos-607.ogg', 
            'http://localhost/workspace/thecitadelcapital/crypto-web/public_html/sounds/juntos-607.m4r'
        ]
    });

    // C2Chat server declaration
    let c2chat = window.C2Chat();
    let client_chat = c2chat.client(
        {
            script_path: "http://localhost/workspace/thecitadelcapital/crypto-web/public_html/js/c2chat/",
            server: "http://localhost/workspace/thecitadelcapital/crypto-web/public_html/c2chat_server"
        }
    );

    // utility function to format time 12 hours with AM or PM
    function toSTDTimeFormat (date = new Date(), include_seconds = true) {
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

    // utility function to determine when height of element changes
    function onElementHeightChange(elem, callback) {
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

    // adapt the chat messages panel to occupy as much space available
    function adaptChatMsgPanel() {
        let chat_win = document.getElementById("c2chat-client-chat-win");
        let send_msg_panel = document.querySelector('#c2chat-client-chat-win .send-msg-cont');
        let new_height = chat_win.offsetHeight - (chat_msg_panel.offsetTop + send_msg_panel.offsetHeight);

        // set the height
        chat_msg_panel.setAttribute("style", "height: " + new_height + "px;");
    }

    // utility function to check if page is currently in view
    function pageInView() {
        let idle_timer;
        is_page_in_view = true;

        // listen for mouse focus on page
        window.addEventListener("focus", (e) => {
            is_page_in_view = true;
        }, false);

        // listen on page blur event
        window.addEventListener("blur", (e) => {
            is_page_in_view = false
        }, false);

        // listen for mouse move on the page
        window.addEventListener("mousemove", (e) => {
            clearTimeout(idle_timer);
            idle_timer = setTimeout(() => {is_page_in_view = false;}, 600000);
        }, false);
    }

    // show drop down menu
    function showDropDownMenu(menu_id) {
        // check if menu has been close already
        if (closed_active_drop_menu[menu_id]) {
            closed_active_drop_menu[menu_id] = false;
            return;
        }

        let menu = document.querySelector('#' + menu_id);

        // check if there is active drop down menu and close it
        if (active_drop_menu != null) {
            active_drop_menu.setAttribute("class", "remove-elem");
        }

        // check if is not the current close menu
        if (!(menu == active_drop_menu)) {
            // show drop down menu
            menu.removeAttribute("class");
            active_drop_menu = menu;
            active_drop_menu_id = menu_id;

        } else {
            active_drop_menu = null;
            active_drop_menu_id = "";
        }
    };

    // close active drop down menu
    function closeDropDownMenu(e) {
        if (active_drop_menu != null) {
            let bounding_rect = active_drop_menu.getBoundingClientRect();
            let click_x = e.clientX;
            let click_y = e.clientY;
            let input_x = bounding_rect.left;
            let input_y = bounding_rect.top;
            let input_w = bounding_rect.width;
            let input_h = bounding_rect.height;

            // check if mouse click happen outside the menu
            if (!(click_x > input_x && click_x < (input_x + input_w) && 
                click_y > input_y && click_y < (input_y + input_h))) {
            
                // close the menu
                active_drop_menu.setAttribute("class", "remove-elem");
                active_drop_menu = null;
                active_drop_menu_id = "";
            }
        }
    }

    // process user's input
    function processUserInput(e) {
        let input_elem = e.target; // get element that fire the event
        
        // process event type
        switch(e.type) {
            case "keydown":
                input_elem.removeAttribute("class");

                break;

            default:
                // shouldn't be here
        }
    }

    // validate user initiate chat form
    function validateInitChatForm(form_input_value) {
        let email_exp = pattern = /^(([^<>()\[\]\\.,;:\s@"]+(\.[^<>()\[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;
        let error_msg_elem;

        if (!/^[a-zA-Z]+([ ]{1}[a-zA-Z]+)*$/.test(form_input_value["name"].value)) {
            form_input_value["name"].setAttribute("class", "error-input");
            error_msg_elem = document.querySelector('#c2chat-client-chat-win #name-input + .input-error-msg');

            // input is left empty
            if (/^[ ]*$/.test(form_input_value["name"].value)) {
                error_msg_elem.innerHTML = "Field is required.";

            } else {
                error_msg_elem.innerHTML = "Entered name is invalid.";
            }

            return false;
        }

        if (!email_exp.test(form_input_value["email"].value)) {
            form_input_value["email"].setAttribute("class", "error-input");
            error_msg_elem = document.querySelector('#c2chat-client-chat-win #email-input + .input-error-msg');

            // input is left empty
            if (/^[ ]*$/.test(form_input_value["email"].value)) {
                error_msg_elem.innerHTML = "Field is required.";

            } else {
                error_msg_elem.innerHTML = "Your email is not acceptable.";
            }

            return false;
        }

        if (!/^.+$/.test(form_input_value["message"].value.trim())) {
            form_input_value["message"].setAttribute("class", "error-input");
            error_msg_elem = document.querySelector('#c2chat-client-chat-win #name-input + .input-error-msg');

            // input is left empty
            error_msg_elem.innerHTML = "Field is required.";

            return false;
        }

        return true;
    }

    // connect client to chat server
    function connectClientToChatServer(form_input_value) {
        let department_id = "all";
        let message = form_input_value['message'];
        client_info = {
            name: form_input_value['name'],
            picture: "http://localhost/workspace/thecitadelcapital/crypto-web/public_html/images/icons/user_profile_icon.svg",
            email: form_input_value['email']
        };

        // remove start chat form
        let elem = document.querySelector('#c2chat-client-chat-win .start-chat-form-cont');
        elem.setAttribute("class", "start-chat-form-cont remove-elem");

        // show wait message and animation
        elem = document.querySelector('#c2chat-client-chat-win .init-chat-wait-cont');
        elem.setAttribute("class", "init-chat-wait-cont");

        client_chat.connect(client_info, department_id, message, (status, agent_info) => {
            initiating_chat = false;

            // check connection state
            if (status == c2chat.CONNECTING) { // connecting to agent
                // leave it empty

            } else if (status == c2chat.AGENT_UNAVAILABLE) {
                // remove start chat form
                let elem = document.querySelector('#c2chat-client-chat-win .start-chat-form-cont');
                elem.setAttribute("class", "start-chat-form-cont remove-elem");

                // remove wait message and animation
                elem = document.querySelector('#c2chat-client-chat-win .init-chat-wait-cont');
                elem.setAttribute("class", "init-chat-wait-cont remove-elem");

                // show connection state to client
                elem = document.querySelector('#c2chat-client-chat-win .notification-cont');
                elem.setAttribute("class", "notification-cont");
                elem = document.querySelector('#c2chat-client-chat-win .notification-cont .header-title');
                elem.innerHTML = "Sorry! All our agents are currently unavailable. Please try again later.";

            } else if (status == c2chat.CONNECTED) {
                // remove wait message and animation
                let elem = document.querySelector('#c2chat-client-chat-win .init-chat-wait-cont');
                elem.setAttribute("class", "init-chat-wait-cont remove-elem");

                // show chat window
                elem = document.querySelector('#c2chat-client-chat-win .chat-frame-cont');
                elem.setAttribute("class", "chat-frame-cont");

                // set connected agent
                elem = document.querySelector('#c2chat-client-chat-win .agent-profile-bar .profile-picture-indicator-cont .profile-picture');
                elem.setAttribute("src", agent_info.picture);
                elem = document.querySelector('#c2chat-client-chat-win .agent-profile-bar .profile-name-connect-status .name');
                elem.innerHTML = agent_info.name;

                // add the question or request to chat view
                chat_msg_panel.appendChild(createClientHeaderMessageBox({
                    message: form_input_value['message'],
                    sender_profile: {
                        name: client_info.name,
                        picture: client_info.picture
                    },
                    time: (new Date()).getTime() / 1000 // in seconds
                }));

                // initiate handlers
                is_client_connected = true;
                initiateChatListenerAndHandler();

            } else if (status == c2chat.SERVER_BUSY || status == c2chat.CONNECTION_FAILED) {
                // remove wait message and animation
                elem = document.querySelector('#c2chat-client-chat-win .init-chat-wait-cont');
                elem.setAttribute("class", "init-chat-wait-cont remove-elem");
                
                // show connection state to client
                elem = document.querySelector('#c2chat-client-chat-win .notification-cont');
                elem.setAttribute("class", "notification-cont");
                elem = document.querySelector('#c2chat-client-chat-win .notification-cont .header-title');
                elem.innerHTML = "Chat can't be initiated due to an error.";
            }
        });
    }

    // initiate functions that listen and handle chat event
    function initiateChatListenerAndHandler() {
        // listen to sent message
        client_chat.messageListener(processReceivedMessages);

        // listen to when chat session is disconnected
        client_chat.endChatSessionListener(handleEndChatSession);
    }

    // handle and process received message
    function processReceivedMessages(messages) {
        if (!is_page_in_view && messages.length > 0) {
            // play message notification sound
            sound.play();
        }

        // add message to chat window
        let prev_sent_msg_time = 0;
        let curr_sent_msg_time;

        for (let i = 0; i < messages.length; i++) {
            curr_sent_msg_time = messages[i].time;

            // check to create header or tail message
            if ((curr_sent_msg_time - prev_sent_msg_time) > 60) { // create header message
                chat_msg_panel.appendChild(createAgentHeaderMessageBox(messages[i]));

            } else { // create tail message
                chat_msg_panel.appendChild(createAgentTailMessageBox(messages[i]));
            }

            prev_sent_msg_time = curr_sent_msg_time;
        }

        // scroll chat message container to bottom
        chat_msg_panel.scrollTop = 100000;
    }

    // create agent header message box
    function createAgentHeaderMessageBox(message) {
        let agent_picture;

        // check if client profile picture is set
        if (message.sender_profile.picture == null || /^[ ]*$/.test(message.sender_profile.picture)) {
            agent_picture = ""; // set to fall back image
        } else {
            agent_picture = message.sender_profile.picture;
        }

        // format sent time
        let sent_time = toSTDTimeFormat(new Date(message.time * 1000), false);

        // create message panel
        let elem = document.createElement("div");
        elem.setAttribute("class", "agent-header-msg-cont");
        elem.innerHTML = `
            <div class="profile-picture-cont">
                <img class="profile-picture" src="${agent_picture}">
            </div>
            <div class="msg-body-cont">
                <div class="profile-name-sent-time">
                    <h4 class="profile-name">${message.sender_profile.name}</h4>
                    <div class="sent-time">${sent_time}</div>
                </div>
                <div class="msg-cont">
                    <p class="msg">${message.message}</p>
                </div>
            </div>
        `;

        return elem;
    }

    // create agent tail message box
    function createAgentTailMessageBox(message) {
        // create message panel
        let elem = document.createElement("div");
        elem.setAttribute("class", "agent-tail-msg-cont");
        elem.innerHTML = `
            <div class="space"></div>
            <div class="msg-cont">
                <p class="msg">${message.message}</p>
            </div>
        `;

        return elem;
    }

    // create client header message box
    function createClientHeaderMessageBox(message) {
        let client_picture;

        // check if client profile picture is set
        if (message.sender_profile.picture == null || /^[ ]*$/.test(message.sender_profile.picture)) {
            client_picture = ""; // set to fall back image
        } else {
            client_picture = message.sender_profile.picture;
        }

        // format sent time
        let sent_time = toSTDTimeFormat(new Date(message.time * 1000), false);

        // create message panel
        let elem = document.createElement("div");
        elem.setAttribute("class", "client-header-msg-cont");
        elem.innerHTML = `
            <div class="msg-body-cont">
                <div class="profile-name-sent-time">
                    <h4 class="profile-name">${message.sender_profile.name}</h4>
                    <div class="sent-time">${sent_time}</div>
                </div>
                <div class="msg-cont">
                    <p class="msg">${message.message}</p>
                </div>
            </div>
            <div class="profile-picture-cont">
                <img class="profile-picture" src="${client_picture}">
            </div>
        `;

        return elem;
    }

    // create client tail message box
    function createClientTailMessageBox(message) {
        // create message panel
        let elem = document.createElement("div");
        elem.setAttribute("class", "client-tail-msg-cont");
        elem.innerHTML = `
            <div class="msg-cont">
                <p class="msg">${message.message}</p>
            </div>
            <div class="space"></div>
        `;

        return elem;
    }

    // handle end chat session
    function handleEndChatSession() {
        is_client_connected = false;

        // agent is away or offline
        let elem = document.querySelector('#c2chat-client-chat-win .agent-profile-bar .profile-picture-indicator-cont .indicator');
        elem.setAttribute("class", "indicator offline");
        elem = document.querySelector('#c2chat-client-chat-win .agent-profile-bar .profile-name-connect-status .status');
        elem.innerText = "offline";
    }

    // show and hide text area placeholder
    function textAreaPlaceholder(e) {
        let code = e.keyCode || e.which;

        if (placeholder_toggle && /^.$/.test(e.key)) {
            placeholder_toggle = false;
            chat_text_area_placeholder.setAttribute("style", "display: none;");

        } else if (!placeholder_toggle && (code == 8 || e.key == "Backspace") && chat_text_area.innerText.length < 2) {
            placeholder_toggle = true;
            chat_text_area_placeholder.removeAttribute("style");
        }
    }

    // show message on enter key press
    function sendMessageOnEnterKeyPress(e) {
        let code = e.keyCode || e.which;

        // check if use press only enter key
        if (!e.shiftKey && (code == 13 || e.key == "Enter")) {
            e.preventDefault();

            // pass to function that will run the process
            if (chat_text_area.innerText.trim().length > 0) {
                processSendMessage();

                // clear the message input and show the placeholder text
                chat_text_area.innerHTML = "";
                placeholder_toggle = true;
                chat_text_area_placeholder.removeAttribute("style");
            }
        }
    }

    // send message to agent
    function processSendMessage() {
        let current_time = (new Date()).getTime() / 1000; // time in seconds since epoch time
        client_curr_sent_msg_time = current_time;

        // check to create header or tail message
        if ((client_curr_sent_msg_time - client_prev_sent_msg_time) > 60) { // create header message
            // the the message to chat view
            chat_msg_panel.appendChild(createClientHeaderMessageBox({
                message: chat_text_area.innerHTML,
                sender_profile: {
                    name: client_info.name,
                    picture: client_info.picture
                },
                time: current_time
            }));

        } else { // create tail message
            chat_msg_panel.appendChild(createClientTailMessageBox({
                message: chat_text_area.innerHTML
            }));
        }

        client_prev_sent_msg_time = client_curr_sent_msg_time;

        // forward the message
        client_chat.sendMessage(chat_text_area.innerHTML);

        // scroll chat message container to bottom
        chat_msg_panel.scrollTop = 100000;
    }

    // reconnect client to chat server
    window.c2chatReconnect = function() {
        // remove connection notification
        let elem = document.querySelector('#c2chat-client-chat-win .notification-cont');
        elem.setAttribute("class", "notification-cont remove-elem");

        // show wait message and animation
        elem = document.querySelector('#c2chat-client-chat-win .init-chat-wait-cont');
        elem.setAttribute("class", "init-chat-wait-cont");

        connectClientToChatServer(temp_init_chat_inputs);
    };

    // process form and initiate chat
    window.initChat = function(e) {
        e.preventDefault(); // prevent form from submitting

        // check if chat is been initiated
        if (initiating_chat) {
            return;
        }

        initiating_chat = true;

        // copy chat form
        temp_init_chat_inputs["name"] = init_chat_form.elements["name"].value;
        temp_init_chat_inputs["email"] = init_chat_form.elements["email"].value;
        temp_init_chat_inputs["message"] = init_chat_form.elements["message"].value;

        // validate form's input value
        if (!validateInitChatForm(init_chat_form.elements)) {
            initiating_chat = false;
            return;
        }

        connectClientToChatServer(temp_init_chat_inputs);
    };

    // disconnect client from C2Chat server
    window.disconnectC2Chat = function() {
        // check if client is connected to chat server
        if (is_client_connected) {
            client_chat.disconnect((status) => {
                console.log(status);
            });
        }

        // add message
    };

    /* 
     * disconnect client from C2Chat server, clear chat history, 
     * and take you to reconnection page 
     */
    window.initNewC2Chat = function() {
        // check if client is connected to chat server
        if (is_client_connected) {
            client_chat.disconnect((status) => {
                console.log(status);
            });
        }

        // clear the chat messages
        chat_msg_panel.innerHTML = "";
        chat_text_area.innerHTML = "";
        chat_view_cont.setAttribute("class", "chat-frame-cont remove-elem");

        // remove displayed notification
        elem = document.querySelector('#c2chat-client-chat-win .notification-cont');
        elem.setAttribute("class", "notification-cont remove-elem");


        // show initiate chat form and reset the input
        elem = document.querySelector('#c2chat-client-chat-win .start-chat-form-cont');
        elem.setAttribute("class", "start-chat-form-cont");
        init_chat_form.reset();
    };

    // close the c2chat window
    window.closeC2ChatWindow = function() {
        // close the chat window
        let elem = document.getElementById("c2chat-client-chat-win");
        elem.setAttribute("class", "remove-elem");

        // show the launch button
        c2chat_launch_btn.removeAttribute("class");
    };

    // close menu when menu's link or button in is clicked
    let menu_items = document.querySelectorAll('.close-menu');
    menu_items.forEach((item) => {
        item.onmouseup = function (e) {
            if (active_drop_menu != null) {
                // close the menu
                active_drop_menu.setAttribute("class", "remove-elem");
                active_drop_menu = null;
                active_drop_menu_id = "";
            }
        };
    });

    // listen to mouse click event
    let win_drop_menu_btn = document.querySelector('#c2chat-client-chat-win .header-menu-bar .more-btn')
    win_drop_menu_btn.onclick = (e) => {
        showDropDownMenu("drop-down-more-menu");
    };

    // notify that menu is already closed
    win_drop_menu_btn.onmousedown = function (e) {
        // check if menu is active
        if (active_drop_menu_id == "drop-down-more-menu") {
            closed_active_drop_menu[active_drop_menu_id] = true;
            active_drop_menu_id = "";
        }
    };

    // listen to click event on c2chat launch button
    c2chat_launch_btn.onclick = function(e) {
        // launch the chat widow
        let elem = document.getElementById("c2chat-client-chat-win");
        elem.removeAttribute("class");

        // hide the launch button
        c2chat_launch_btn.setAttribute("class", "remove-elem");
    };

    // add form's input to process that handle user's input
    function addInputToProcess(input_elements) {
        for (let i = 0; i < input_elements.length; i++) {
            input_elements[i].addEventListener("keydown", processUserInput, false);
        }
    }

    // get all the form's input to be processed
    addInputToProcess([
        init_chat_form.elements["name"],
        init_chat_form.elements["email"],
        init_chat_form.elements["message"]
    ]);

    // listen for when user type into chat text area
    chat_text_area.addEventListener("keydown", (e) => {
        sendMessageOnEnterKeyPress(e);
        textAreaPlaceholder(e);
    }, false);

    // lisen to when user click the send message button
    let elem = document.querySelector('#c2chat-client-chat-win .send-msg-menu-cont .send-msg-btn');
    elem.addEventListener("click", (e) => {
        processSendMessage();

        // clear the message input and show the placeholder text
        chat_text_area.innerHTML = "";
        placeholder_toggle = true;
        chat_text_area_placeholder.removeAttribute("style");
    }, false);

    // listen to when agent is online or offline
    client_chat.isAgentOnlineListener((status) => {
        if (status == c2chat.ONLINE) {
            let elem = document.querySelector('#c2chat-launch-btn .agent-online-indicator');
            elem.setAttribute("class", "agent-online-indicator online");

            // play sound
            sound.play();

        } else { // OFFLINE
            let elem = document.querySelector('#c2chat-launch-btn .agent-online-indicator');
            elem.setAttribute("class", "agent-online-indicator offline");
        }
    });

    // listen to chat message textarea height change event
    onElementHeightChange(chat_text_area, (height) => {
        adaptChatMsgPanel();
    });

    // listen to mouse down event on document
    document.addEventListener("mousedown", (e) => {
        closeDropDownMenu(e);
    }, false);

    // listen to page resize event
    document.addEventListener("resize", (e) => {
        adaptChatMsgPanel();
    }, false);

    // call functions after page load
    pageInView();
}

// initialise the script
if (window.attachEvent) {
    window.attachEvent("onload", init);

} else {
    window.addEventListener("load", init, false);
}