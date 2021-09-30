function init() {
    //contants and variables here
    const ONLINE = 10;
    const CONNECTED = 11;
    const OFFLINE = 12;

    const CLIENT_MSG = 10;
    const AGENT_MSG = 11;

    let active_drop_menu = null;
    let active_drop_menu_id = "";
    let closed_active_drop_menu = {
        "chat-win-drop-menu": false
    };
    let placeholder_toggle = true;
    let chat_text_area = document.querySelector('.chat-window-cont .chat-text-area-cont .text-area');
    let chat_text_area_placeholder = document.querySelector('.chat-window-cont .chat-text-area-cont .text-area-placeholder');
    let is_agent_connected_to_server = false;
    let chat_customer_list_elem = document.querySelector('#chat-customer-list-cont .chat-list');
    let transfered_chat_list_elem = document.querySelector('#chat-transfered-list-cont .chat-list');
    let chat_window_msg_list_elem = document.querySelector('.chat-window-cont .chat-msg-cont');
    let initiated_chat_id = null;
    let current_chat_id = null;
    let client_prev_sent_msg_time = 0;
    let client_curr_sent_msg_time = 0;
    let expanded_chat_list_id = null;
    let agent_info;
    let is_page_in_view = true;

    // load c2chat message sounds
    let sound = new Howl({
        src: [
            'http://localhost/workspace/thecitadelcapital/crypto-web/public_html/sounds/juntos-607.mp3', 
            'http://localhost/workspace/thecitadelcapital/crypto-web/public_html/sounds/juntos-607.ogg', 
            'http://localhost/workspace/thecitadelcapital/crypto-web/public_html/sounds/juntos-607.m4r'
        ]
    });

    let connected_client_list = new Map();
    let client_connection_status = new Map();
    let chat_messages = new Map();
    let chat_messages_type = new Map();

    // C2Chat server declaration
    let chat = window.C2Chat();
    let agent_chat;

    // connect agent to chat server
    window.connectAgentToChatServer = function (server, script_path, agent_profile_info) {
        agent_info = agent_profile_info;
        let department_id = agent_profile_info.department; // all

        agent_chat = chat.agent({
            server: server,
            script_path: script_path
        });

        agent_chat.connect(agent_profile_info, department_id, (status) => {
            // check if connection is successfull
            if (status == chat.CONNECTED) {
                is_agent_connected_to_server = true;
                initiateChatListenerAndHandler();

            } else if (status == chat.SERVER_DOWN) {
                console.log(status);

            } else if (status == chat.CONNECTION_FAILED) {
                console.log(status);
            }
        });
    };

    // initiate functions that listen and handle chat event
    function initiateChatListenerAndHandler() {
        // handle client connection event
        agent_chat.clientConnectionEventHandler({
            connectedClient: handleConnectedClient,
            disconnectedClient: handleDisconnectedClient,
            handledClient: handleHandledClient // client handled by other agent
        });

        // handle agent connection event
        agent_chat.agentConnectionEventHandler({
            connectedAgent: (agents) => {},
            disconnectedAgent: (agents) => {}
        });

        // handle chat transfer event
        agent_chat.chatTransferHandler({
            transferedRequest: handleChatTransferedRequest,
            closedTransferedRequest: handleChatClosedTransferedRequest
        });

        // listen to sent message
        agent_chat.messageListener(processReceivedMessages);
    }

    // add connected client to chat list
    function handleConnectedClient(clients) {
        let client;

        if (!is_page_in_view && clients.length > 0) {
            // play message notification sound
            sound.play();
        }

        // iterate through the list
        for (let i = 0; i < clients.length; i++) {
            client = clients[i];

            // add connected client to list
            connected_client_list.set(client.chat_id, client);

            // set client connection state
            client_connection_status.set(client.chat_id, ONLINE);

            // add client to chat list panel
            chat_customer_list_elem.appendChild(createClientChatListPanel(client));
        }
    }

    // process diconnected client
    function handleDisconnectedClient(clients) {
        let client;

        // iterate through the list
        for(let i = 0; i < clients.length; i++) {
            client = clients[i];

            // check if the client the agent is chating with have disconnected
            if (initiated_chat_id == client.chat_id) {
                initiated_chat_id = null;
            }

            // check if agent has chatted with this client
            if (chat_messages.has(client.chat_id)) {
                // set client connection state
                client_connection_status.set(client.chat_id, OFFLINE);

                // change client connection state indicator
                let elem = document.getElementById("indicator-" + client.chat_id);
                elem.setAttribute("class", "indicator offline");

                // check if client chat window is the one opened
                if (client.chat_id == current_chat_id) {
                    elem = document.getElementById("chat-win-indicator");
                    elem.setAttribute("class", "indicator offline");
                    elem = document.getElementById("chat-win-status");
                    elem.innerHTML = "offline";
                    elem = document.getElementById("user-info-indicator");
                    elem.setAttribute("class", "indicator offline");

                    // disable message input
                    elem = document.querySelector('.chat-window-cont .chat-text-area-cont .text-area');
                    elem.setAttribute("contenteditable", "false");
                    elem = document.querySelector('.chat-window-cont .send-msg-menu-cont #attach-file-input');
                    elem.disabled = true;
                    elem = document.querySelector('.chat-window-cont .send-msg-menu-cont .send-msg-btn');
                    elem.disabled = true;
                }

            } else if (connected_client_list.has(client.chat_id)) { // no chat history
                // remove client
                connected_client_list.delete(client.chat_id);

                // remove client connection state
                client_connection_status.delete(client.chat_id);

                // remove client from chat list
                chat_customer_list_elem.removeChild(document.getElementById("client-" + client.chat_id));
            }
        }
    }

    // remove client chatting with another agent from the chat list
    function handleHandledClient(clients) {
        let client;

        // iterate through the list
        for (let i = 0; i < clients.length; i++) {
            client = clients[i];

            // client if client exist
            if (connected_client_list.has(client.chat_id)) {
                // remove client
                connected_client_list.delete(client.chat_id);

                // remove client connection state
                client_connection_status.delete(client.chat_id);

                // remove client from chat list
                chat_customer_list_elem.removeChild(document.getElementById("client-" + client.chat_id));
            }
        }
    }

    // handle tranfered chat request from agent
    function handleChatTransferedRequest(requests) {
        let request;

        // iterate through the list
        for (let i = 0; i < requests.length; i++) {
            request = requests[i];

            // set client connection state
            client_connection_status.set(request.chat_id, ONLINE);

            // add client to transfered chat list panel
            transfered_chat_list_elem.appendChild(createTransferedChatListPanel(request));
        }
    }

    // utility function to create new client chat list item
    function createClientChatListPanel(client) {
        let client_picture;

        // check if client profile picture is set
        if (client.client.picture == null || /^[ ]*$/.test(client.client.picture)) {
            client_picture = ""; // set to fall back image
        } else {
            client_picture = client.client.picture;
        }

        // connected time
        let connected_time = window.toSTDTimeString(new Date(), false);

        let item = document.createElement("li");
        item.setAttribute("id", "client-" + client.chat_id);
        item.setAttribute("class", "client-cont hide-init-btn");
        item.setAttribute("onclick", "chatListClicked('" + client.chat_id + "')");
        item.innerHTML = `
            <div class="upper-cont">
                <div class="profile-picture-indicator-cont">
                    <img class="profile-picture" src="${client_picture}" />
                    <div id="indicator-${client.chat_id}" class="indicator online"></div>
                </div>
                <div class="profile-name-msg-cont">
                    <div class="name-logtime-cont">
                        <h4 class="name">${client.client.name}</h4>
                        <div class="log-time">${connected_time}</div>
                    </div>
                    <div class="message">${clipOutText(64, client.message)}</div>
                </div>
            </div>
            <div class="lower-cont initiate-chat">
                <div class="init-chat-btn-cont">
                    <button class="init-chat-btn hide-loading-anim" onclick="initiateChat(this, '${client.chat_id}')">
                        <span class="btn-name">Initiate</span>
                        <span class="btn-wait-anim">
                            <span class="rot-bar rot-quart"></span>
                        </span>
                    </button>
                </div>
            </div>
        `;

        return item;
    }

    // utility function to create new transfered chat list item
    function createTransferedChatListPanel(request) {
        let client_picture;

        // check if client profile picture is set
        if (request.client.picture == null || /^[ ]*$/.test(request.client.picture)) {
            client_picture = ""; // set to fall back image
        } else {
            client_picture = request.client.picture;
        }

        // connected time
        let connected_time = window.toSTDTimeString(new Date(), false);

        let item = document.createElement("li");
        item.setAttribute("id", "client-" + request.chat_id);
        item.setAttribute("class", "client-cont hide-init-btn");
        item.setAttribute("onclick", "chatListClicked('" + request.chat_id + "')");
        item.innerHTML = `
            <div class="upper-cont">
                <div class="profile-picture-indicator-cont">
                    <img class="profile-picture" src="${client_picture}" />
                    <div id="indicator-${request.chat_id}" class="indicator online"></div>
                </div>
                <div class="profile-name-msg-cont">
                    <div class="name-logtime-cont">
                        <h4 class="name">${request.client.name}</h4>
                        <div class="log-time">${connected_time}</div>
                    </div>
                    <div class="message">${clipOutText(64, request.message)}</div>
                </div>
            </div>
            <div class="lower-cont initiate-chat">
                <div class="init-chat-btn-cont">
                    <button class="init-chat-btn hide-loading-anim" onclick="acceptChatTransfer(this, '${request.chat_id}')">
                        <span class="btn-name">Accept</span>
                        <span class="btn-wait-anim">
                            <span class="rot-bar rot-quart"></span>
                        </span>
                    </button>
                </div>
            </div>
        `;

        return item;
    }

    // remove closed chat transfer request from the list
    function handleChatClosedTransferedRequest(requests) {
        let request;

        // iterate through the list
        for (let i = 0; i < requests.length; i++) {
            request = requests[i];

            // remove request connection state
            client_connection_status.delete(request.chat_id);

            // remove request from chat list
            transfered_chat_list_elem.removeChild(document.getElementById("client-" + request.chat_id));
        }
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

    // clipt out text
    function clipOutText(txt_length, text) {
        let clipped_text = text;

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
        }

        return clipped_text;
    }

    // enable the chat GUI
    function enableChatGUI() {
        // enable chat window
        let elem = document.querySelector('.chat-window-cont .header-menu-bar .left-menu-section');
        elem.setAttribute("class", "left-menu-section");
        elem = document.querySelector('.chat-window-cont .chat-text-area-cont .text-area');
        elem.setAttribute("contenteditable", "true");
        elem = document.querySelector('.chat-window-cont .send-msg-menu-cont #attach-file-input');
        elem.disabled = false;
        elem = document.querySelector('.chat-window-cont .send-msg-menu-cont .send-msg-btn');
        elem.disabled = false;

        // enable chat user's info
        elem = document.getElementById("chat-user-connect-info-wrapper");
        elem.removeAttribute("class");
        elem = document.getElementById("chat-user-personal-info-wrapper");
        elem.removeAttribute("class");
    }

    // handle and process received message
    function processReceivedMessages(messages) {
        let message_list = chat_messages.get(initiated_chat_id);
        let message_type = chat_messages_type.get(initiated_chat_id);

        if (!is_page_in_view && messages.length > 0) {
            // play message notification sound
            sound.play();
        }

        // add messages to list
        for (let i = 0; i < messages.length; i++) {
            message_list.push(messages[i]);
            message_type.push(CLIENT_MSG);
        }

        // check to add message to chat window
        if (current_chat_id == initiated_chat_id) {
            let prev_sent_msg_time = 0;
            let curr_sent_msg_time;

            for (let i = 0; i < messages.length; i++) {
                curr_sent_msg_time = messages[i].time;

                // check to create header or tail message
                if ((curr_sent_msg_time - prev_sent_msg_time) > 60) { // create header message
                    chat_window_msg_list_elem.appendChild(createClientHeaderMessageBox(messages[i]));

                } else { // create tail message
                    chat_window_msg_list_elem.appendChild(createClientTailMessageBox(messages[i]));
                }

                prev_sent_msg_time = curr_sent_msg_time;
            }
        }
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
        let sent_time = window.toSTDTimeString(new Date(message.time * 1000), false);

        // create message panel
        let elem = document.createElement("div");
        elem.setAttribute("class", "client-header-msg-cont");
        elem.innerHTML = `
            <div class="profile-picture-cont">
                <img class="profile-picture" src="${client_picture}">
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

    // create client tail message box
    function createClientTailMessageBox(message) {
        // create message panel
        let elem = document.createElement("div");
        elem.setAttribute("class", "client-tail-msg-cont");
        elem.innerHTML = `
            <div class="space"></div>
            <div class="msg-cont">
                <p class="msg">${message.message}</p>
            </div>
        `;

        return elem;
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
        let sent_time = window.toSTDTimeString(new Date(message.time * 1000), false);

        // create message panel
        let elem = document.createElement("div");
        elem.setAttribute("class", "agent-header-msg-cont");
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
                <img class="profile-picture" src="${agent_picture}">
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
            <div class="msg-cont">
                <p class="msg">${message.message}</p>
            </div>
            <div class="space"></div>
        `;

        return elem;
    }

    // adapt page content container to window size
    function adaptPagePanels() {
        adaptChatListPanel();
        adaptChatMsgPanel();
        adaptUserInfoPanel();
    }

    // adapt chat list container to screen
    function adaptChatListPanel() {
        let list_panel = document.querySelector('.chat-list-scroll-cont');
        let panel_offset = window.getElementOffset(list_panel);

        // set panel height
        list_panel.setAttribute("style", "height: " + (window.innerHeight - panel_offset.top) + "px;");
    }

    // adapt the chat messages panel to occupy as much space available
    function adaptChatMsgPanel() {
        let chat_msg_panel = document.querySelector('.chat-window-cont .chat-msg-cont');
        let send_msg_panel = document.querySelector('.chat-window-cont .send-msg-cont');
        let chat_msg_panel_offset = window.getElementOffset(chat_msg_panel);
        let send_msg_panel_height = send_msg_panel.offsetHeight;
        let bottom_padding = 10; // remove this if there is no bottom padding
        let new_height = window.innerHeight - (chat_msg_panel_offset.top + send_msg_panel_height + bottom_padding);

        // set the height
        chat_msg_panel.setAttribute("style", "height: " + new_height + "px;");
    }

    // adapt the user's information panel to screen
    function adaptUserInfoPanel() {
        let user_info_panel = document.querySelector('.chat-user-info-cont .user-info-scroll-wrapper');
        let panel_offset = window.getElementOffset(user_info_panel);

        // set panel height
        user_info_panel.setAttribute("style", "height: " + (window.innerHeight - panel_offset.top) + "px;");
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
            }
        }
    }

    // send message to client
    function processSendMessage() {
        // check to send message
        if (client_connection_status.get(current_chat_id) != CONNECTED) {
            return;
        }

        let current_time = (new Date()).getTime() / 1000; // time in seconds since epoch time
        client_curr_sent_msg_time = current_time;

        // add message to user's chat history
        let message_list = chat_messages.get(current_chat_id);
        message_list.push({
            message: chat_text_area.innerHTML,
            sender_profile: {
                name: agent_info.name,
                picture: agent_info.picture
            },
            time: current_time
        });

        // check to create header or tail message
        if ((client_curr_sent_msg_time - client_prev_sent_msg_time) > 60) { // create header message
            // add message to chat view
            chat_window_msg_list_elem.appendChild(createAgentHeaderMessageBox({
                message: chat_text_area.innerHTML,
                sender_profile: {
                    name: agent_info.name,
                    picture: agent_info.picture
                },
                time: current_time
            }));

        } else { // create tail message
            chat_window_msg_list_elem.appendChild(createAgentTailMessageBox({
                message: chat_text_area.innerHTML
            }));
        }

        client_prev_sent_msg_time = client_curr_sent_msg_time;

        // forward the message
        agent_chat.sendMessage(chat_text_area.innerHTML);

        // clear the message input and show the placeholder text
        chat_text_area.innerHTML = "";
        placeholder_toggle = true;
        chat_text_area_placeholder.removeAttribute("style");

        // scroll chat message container to bottom
        chat_window_msg_list_elem.scrollTop = 100000;
    }

    // load user's chat session and information
    function loadClientChatSessionAndInfo(chat_id) {
        let client_info = connected_client_list.get(chat_id); // get client info
        let connection_status = client_connection_status.get(chat_id);

        // update client connection status
        let elem = document.getElementById("indicator-" + chat_id);
        elem.setAttribute("class", connection_status == CONNECTED ? "indicator chatting" : "indicator offline");
        
        // set chat window's title bar
        elem = document.getElementById("chat-win-indicator");
        elem.setAttribute("class", connection_status == CONNECTED ? "indicator chatting" : "indicator offline");
        elem = document.querySelector('.chat-window-cont .header-menu-bar .profile-picture');
        elem.setAttribute("src", client_info.client.picture);
        elem = document.querySelector('.chat-window-cont .header-menu-bar .profile-name-connect-time .name');
        elem.innerHTML = client_info.client.name;
        elem = document.getElementById("chat-win-status");
        elem.innerHTML = connection_status == CONNECTED ? "online" : "offline";

        // set user's information
        elem = document.getElementById("user-info-indicator");
        elem.setAttribute("class", connection_status == CONNECTED ? "indicator chatting" : "indicator offline");
        elem = document.querySelector('.chat-user-info-cont .user-connect-info .profile-picture');
        elem.setAttribute("src", client_info.client.picture);
        elem = document.querySelector('.chat-user-info-cont .user-connect-info .name');
        elem.innerHTML = client_info.client.name;
        elem = document.querySelector('.chat-user-info-cont .user-connect-info .user-type');
        elem.innerHTML = "Customer";
        elem = document.querySelector('.chat-user-info-cont .user-personal-info .info-list .list-data');
        elem.innerHTML = client_info.client.email;

        // load and render user's chat history
        let message_list = chat_messages.get(chat_id);
        let message_type = chat_messages_type.get(chat_id);
        chat_window_msg_list_elem.innerHTML = ""; // clear messages if there is any
        let client_prev_sent_msg_time = 0;
        let client_curr_sent_msg_time;
        let agent_prev_sent_msg_time = 0;
        let agent_curr_sent_msg_time;
        
        // iterate through the messages
        for (let i = 0; i < message_list.length; i++) {
            if (message_type[i] == CLIENT_MSG) {
                client_curr_sent_msg_time = message_list[i].time;

                // check to create header or tail message
                if ((client_curr_sent_msg_time - client_prev_sent_msg_time) > 60) { // create header message
                    chat_window_msg_list_elem.appendChild(createClientHeaderMessageBox(message_list[i]));

                } else { // create tail message
                    chat_window_msg_list_elem.appendChild(createClientTailMessageBox(message_list[i]));
                }

                client_prev_sent_msg_time = client_curr_sent_msg_time;

            } else { // AGENT_MSG
                agent_curr_sent_msg_time = message_list[i].time;

                // check to create header or tail message
                if ((agent_curr_sent_msg_time - agent_prev_sent_msg_time) > 60) { // create header message
                    chat_window_msg_list_elem.appendChild(createAgentHeaderMessageBox(message_list[i]));

                } else { // create tail message
                    chat_window_msg_list_elem.appendChild(createAgentTailMessageBox(message_list[i]));
                }

                agent_prev_sent_msg_time = agent_curr_sent_msg_time;
            }
        }

        // scroll chat message container to bottom
        chat_window_msg_list_elem.scrollTop = 100000;
    }
    
    // initiate chat session with client
    window.initiateChat = function (btn, client_chat_id) {
        // show initiate animation
        btn.setAttribute("class", "init-chat-btn show-loading-anim");

        // initiate chat
        agent_chat.initiateClientChat(client_chat_id, (status) => {
            // hide initiate animation and button
            btn.setAttribute("class", "init-chat-btn hide-loading-anim");
            let elem = document.getElementById("client-" + client_chat_id);
            elem.setAttribute("class", "client-cont hide-init-btn");

            // check if initiation was successfully
            if (status == chat.OK) {
                let client_info = connected_client_list.get(client_chat_id);
                chat_messages.set(client_chat_id, [{
                    message: client_info.message,
                    sender_profile: {
                        name: client_info.client.name,
                        picture: client_info.client.picture
                    },
                    time: client_info.time
                }]);
                chat_messages_type.set(client_chat_id, [CLIENT_MSG]);
                initiated_chat_id = client_chat_id;
                client_connection_status.set(client_chat_id, CONNECTED);

                // load the user's chat session and personal information
                loadClientChatSessionAndInfo(client_chat_id);
                current_chat_id = client_chat_id;
                enableChatGUI();

            } else if (status == chat.CLIENT_ATTENDED) {
                // code here

            } else if (status == chat.CLIENT_OFFLINE) {
                // code here
            }
        });
    };

    // load the client chat session
    window.chatListClicked = function (client_chat_id) {
        // check the connection status of the client
        if (client_connection_status.get(client_chat_id) == ONLINE) {
            // check if agent is not having a conversation with another client
            if (initiated_chat_id == null) {
                let elem;

                // collapse other expanded list
                if (expanded_chat_list_id != null && expanded_chat_list_id != client_chat_id) {
                    elem = document.getElementById("client-" + expanded_chat_list_id);
                    elem.setAttribute("class", "client-cont hide-init-btn");
                }

                // show initiate chat button
                elem = document.getElementById("client-" + client_chat_id);
                elem.setAttribute("class", "client-cont show-init-btn");

                expanded_chat_list_id = client_chat_id;

            } else {
                // show the user message (You have an active chat. End the current chat session to connect.)
            }

        } else if (client_connection_status.get(client_chat_id) == CONNECTED) {
            // check if chat session is not already loaded
            if (!(current_chat_id == client_chat_id)) {
                let elem;

                // collapse other expanded list
                if (expanded_chat_list_id != null) {
                    elem = document.getElementById("client-" + expanded_chat_list_id);
                    elem.setAttribute("class", "client-cont hide-init-btn");
                }

                // load the user's chat session and personal information
                loadClientChatSessionAndInfo(client_chat_id);
                current_chat_id = client_chat_id;

                // enable message inputs
                elem = document.querySelector('.chat-window-cont .chat-text-area-cont .text-area');
                elem.setAttribute("contenteditable", "true");
                elem = document.querySelector('.chat-window-cont .send-msg-menu-cont #attach-file-input');
                elem.disabled = false;
                elem = document.querySelector('.chat-window-cont .send-msg-menu-cont .send-msg-btn');
                elem.disabled = false;
            }
            
        } else if (client_connection_status.get(client_chat_id) == OFFLINE) {
            // check if chat session is not already loaded
            if (!(current_chat_id == client_chat_id)) {
                let elem;

                // collapse other expanded list
                if (expanded_chat_list_id != null) {
                    elem = document.getElementById("client-" + expanded_chat_list_id);
                    elem.setAttribute("class", "client-cont hide-init-btn");
                }

                // load the user's chat session and personal information
                loadClientChatSessionAndInfo(client_chat_id);
                current_chat_id = client_chat_id;

                // disable chat inputs
                elem = document.querySelector('.chat-window-cont .chat-text-area-cont .text-area');
                elem.setAttribute("contenteditable", "false");
                elem = document.querySelector('.chat-window-cont .send-msg-menu-cont #attach-file-input');
                elem.disabled = true;
                elem = document.querySelector('.chat-window-cont .send-msg-menu-cont .send-msg-btn');
                elem.disabled = true;
            }
        }
    };

    // close client chat session
    window.closeChatSession = function () {
        agent_chat.closeClientChatSession((status) => {
            if (status == chat.OK) {
                initiated_chat_id = null;

                // disable message input
                let elem = document.querySelector('.chat-window-cont .chat-text-area-cont .text-area');
                elem.setAttribute("contenteditable", "false");
                elem = document.querySelector('.chat-window-cont .send-msg-menu-cont #attach-file-input');
                elem.disabled = true;
                elem = document.querySelector('.chat-window-cont .send-msg-menu-cont .send-msg-btn');
                elem.disabled = true;
            }
        });
    };

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

    // expand and close chat list container
    window.expandAndCollapseChatList = function (btn, list_cont_id) {
        let cont_elem = document.getElementById(list_cont_id);

        // check to collapse or expand the container
        if (btn.getAttribute("toggle") == "0") { // collapse
            btn.setAttribute("toggle", "1");
            btn.setAttribute("class", "collapse-expand-btn");
            cont_elem.setAttribute("style", "height: 0px;");

        } else { // expand
            btn.setAttribute("toggle", "0");
            btn.setAttribute("class", "collapse-expand-btn collapse");
            cont_elem.removeAttribute("style");
        }
    };

    // listen to mouse click event
    let chat_win_drop_menu_btn = document.querySelector('.chat-window-cont .header-menu-bar .drop-menu-btn')
    chat_win_drop_menu_btn.onclick = (e) => {
        showDropDownMenu("chat-win-drop-menu");
    };

    // notify that menu is already closed
    chat_win_drop_menu_btn.onmousedown = function (e) {
        // check if menu is active
        if (active_drop_menu_id == "chat-win-drop-menu") {
            closed_active_drop_menu[active_drop_menu_id] = true;
            active_drop_menu_id = "";
        }
    };

    // close menu when menu's link or button in is clicked
    let menu_items = document.querySelectorAll('.close-menu');
    menu_items.forEach((item) => {
        item.onclick = function (e) {
            if (active_drop_menu != null) {
                // close the menu
                active_drop_menu.setAttribute("class", "remove-elem");
                active_drop_menu = null;
                active_drop_menu_id = "";
            }
        };
    });

    // listen for when user type into chat text area
    chat_text_area.onkeydown = function (e) {
        sendMessageOnEnterKeyPress(e);
        textAreaPlaceholder(e);
    };

    // lisen to when user click the send message button
    let elem = document.querySelector('.chat-window-cont .send-msg-menu-cont .send-msg-btn');
    elem.onclick = function (e) {
        processSendMessage();
    };

    // listen to chat message textarea height change event
    window.onElementHeightChange(chat_text_area, (height) => {
        adaptChatMsgPanel();
    });

    // listen to mouse down event on document
    document.addEventListener("mousedown", (e) => {
        closeDropDownMenu(e);
    }, false);

    // listen to page resize event
    window.addEventListener("resize", (e) => {
        adaptPagePanels();
    }, false);

    // call functions after page load
    adaptPagePanels();
    pageInView();
}

// initialise the script
if (window.attachEvent) {
    window.attachEvent("onload", init);

} else {
    window.addEventListener("load", init, false);
}