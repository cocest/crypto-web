/**
 * Customer care chat system.
 * 
 * Author: Attamah Celestine
 * Copyright (c) 2020
 * 
 */

(function () {
    // constants
    const _ONLINE = 10;
    const _OFFLINE = 11;

    const _CONNECTED = 10;
    const _CONNECTION_FAILED = 11;
    const _CONNECTING = 12;
    const _DISCONNECTED = 13;
    const _CONNECTION_LOST = 14;
    const _SERVER_BUSY = 15;
    const _AGENT_UNAVAILABLE = 16;

    const _OK = 10;
    const _FAILED = 11;
    const _CLIENT_ATTENDED = 12;
    const _CLIENT_OFFLINE = 13;

    const _UPLOADING = 10;
    const _UPLOADED = 11;
    const _UPLOAD_FAILED = 12;

    const _CLIENT_CONNECTED = 10;
    const _CLIENT_DISCONNECTED = 11;
    const _CLIENT_HANDLED = 12;

    const _AGENT_CONNECTED = 10;
    const _AGENT_DISCONNECTED = 11;

    function init(_export, _settings) {
        // event handler
        let _received_message_handler = null;
        let _disconnect_listener = null;
        let _upload_progress_listener = null;
        let _client_event_handler = null;
        let _agent_event_handler = null;
        let _chat_transfer_handler = null;

        // variables
        let _script_path = _settings.script_path;
        let _chat_server_url = _settings.server;
        let _chat_id;
        let _chatting_session = false;
        let _agent_id;
        let _agent_department;
        let _is_connected = false;
        let _is_client_online = false;
        let _active_chat_transfer_request = false;
        let _ping_server_worker;
        let _client_listener_worker;
        let _agent_listener_worker;
        let _chat_transfer_listener_worker;
        let _retrieve_msg_worker;
        let _chat_transfer_accepted_worker;

        // set received message listener
        function _messageListener(handler) {
            _received_message_handler = handler;
        }

        // set disconnect message listener
        function _disconnectListener(handler) {
            _disconnect_listener = handler;
        }

        // set upload progress listener
        function _uploadProgressListener(handler) {
            _upload_progress_listener = handler;
        }

        // set client event handler
        function _clientConnectionEventHandler(handler) {
            _client_event_handler = handler;
        }

        // set agent event handler
        function _agentConnectionEventHandler(handler) {
            _agent_event_handler = handler;
        }

        // transfer chat to agent currfently online
        function _transferChatToAgent(agent_id, callback) {
            // check if agent have active chat with customer
            if (!_is_client_online) {
                callback(_NO_ACTIVE_CHAT);
                return;
            }

            // check if agent have 
            if (_active_chat_transfer_request) {
                callback(_ACTIVE_CHAT_TRANSFER_REQUEST);
                return;
            }

            _active_chat_transfer_request = true;
            
            // notify agent for chat transfer
            let form = new FormData();
            form.append("request", "chat_transfer");
            form.append("agent_id", agent_id);
            form.append("chat_id", _chat_id);

            // send request
            ajaxRequest(
                _chat_server_url,
                form,
                { contentType: false }, 
                function (response, status) {
                    if (status == 200) {
                        // check if the agent have accept the invitation at every interval
                        _chat_transfer_accepted_worker = new Worker(_script_path + "agent/chat_transfer_accepted_worker.js");

                        // start the script
                        _chat_transfer_accepted_worker.postMessage({
                            server_url: _chat_server_url,
                            agent_id: agent_id
                        });

                        // listen for received message(s)
                        _chat_transfer_accepted_worker.addEventListener("message", (e) => {
                            _active_chat_transfer_request = false;

                            // pass message to client
                            if (e.data.status == "ok") {
                                // terminate the worker
                                _chat_transfer_accepted_worker.terminate();
                                _chat_transfer_accepted_worker = undefined;

                                _terminateCLientChat();
                                callback(_CHAT_TRANSFER_ACCEPTED);

                            } else if (e.data.status == "closed") {
                                callback(_CHAT_TRANSFER_CLOSED);
                            }

                        }, false);
                    }
                }
            );
        }

        // set chat transfer handler
        function _chatTransferHandler(handler) {
            _chat_transfer_handler = handler;
        }

        // terminate chat session
        function _terminateChat() {
            // terminate worker process
            _ping_server_worker.terminate();
            _ping_server_worker = undefined;
            _retrieve_msg_worker.terminate();
            _retrieve_msg_worker = undefined;

            _is_client_online = false;
        }

        // terminate client chating with agent
        function _terminateCLientChat() {
            // terminate worker process
            _retrieve_msg_worker.terminate();
            _retrieve_msg_worker = undefined;

            _is_client_online = false;
        }

        // close client chat session
        function _closeClientChatSession(callback) {
            // send agent message that communication is droped
            if (!_is_client_online) {
                return;
            }

            let form = new FormData();
            form.append("request", "terminate_chat");
            form.append("request_user", "agent");
            form.append("chat_id", _chat_id); // this might be "agent_id"

            // send request
            ajaxRequest(
                _chat_server_url,
                form,
                { contentType: false }, 
                function (response, status) {
                    if (status == 200) {
                        // notify client that chat has successfully disconnected
                        callback(_OK);

                        // terminate chat
                        _terminateCLientChat();

                    } else {
                        callback(_FAILED);
                    }
                }
            );
        }

        function _startClientCommunicationProcess() {
            // script that maintain communication with chat server
            _ping_server_worker = new Worker(_script_path + "client/ping_server_worker.js");

            // start the ping/pong script
            _ping_server_worker.postMessage({ 
                server_url: _chat_server_url,
                chat_id: _chat_id 
            });

            // script that retrieve message from chat server
            _retrieve_msg_worker = new Worker(_script_path + "retrieve_msg_worker.js");

            // start the script
            _retrieve_msg_worker.postMessage({
                server_url: _chat_server_url,
                req_user: "client",
                chat_id: _chat_id,
                retrieve_time_offset: 0
            });

            // listen for received message(s)
            _retrieve_msg_worker.addEventListener("message", (e) => {
                // pass message to client
                if (e.data.status == "ok") {
                    if (_received_message_handler != null) {
                        _received_message_handler(e.data.messages);
                    }

                } else if (e.data.status == "disconnected") { // agent exit the chat
                    // terminate chat
                    _terminateChat();

                    if (_disconnect_listener != null) {
                        _disconnect_listener();
                    }

                } else { // connection lost
                    // terminate chat
                    _terminateChat();

                    if (_disconnect_listener != null) {
                        _disconnect_listener();
                    }
                }

            }, false);
        }

        function _startAgentCommunicationProcess() {
            // script that maintain communication with chat server
            _ping_server_worker = new Worker(_script_path + "agent/ping_server_worker.js");

            // start the ping/pong script
            _ping_server_worker.postMessage({ 
                server_url: _chat_server_url,
                agent_id: _agent_id 
            });

            // script that listen to client connection request
            _client_listener_worker = new Worker(_script_path + "agent/client_listener_worker.js");
            
            // start the script
            _client_listener_worker.postMessage({ 
                server_url: _chat_server_url,
                department: _agent_department
            });

            // listen for client connection event
            _client_listener_worker.addEventListener("message", (e) => {
                if (e.data.event_type == _CLIENT_CONNECTED) {
                    if (!(_client_event_handler == null || typeof _client_event_handler.connectedClient == "undefined")) {
                        _client_event_handler.connectedClient(e.data.list);
                    }

                } else if (e.data.event_type == _CLIENT_DISCONNECTED) {
                    if (!(_client_event_handler == null || typeof _client_event_handler.disconnectedClient == "undefined")) {
                        _client_event_handler.disconnectedClient(e.data.list);
                    }

                } else if (e.data.event_type == _CLIENT_HANDLED) {
                    if (!(_client_event_handler == null || typeof _client_event_handler.handledClient == "undefined")) {
                        _client_event_handler.handledClient(e.data.list);
                    }
                }
            }, false);

            // this script update the client with agent connection state
            _agent_listener_worker = new Worker(_script_path + "agent/agent_listener_worker.js");

            // start the script
            _agent_listener_worker.postMessage({ server_url: _chat_server_url });

            // listen for agent connection event
            _agent_listener_worker.addEventListener("message", (e) => {
                if (e.data.event_type == _AGENT_CONNECTED) {
                    if (!(_agent_event_handler == null || typeof _agent_event_handler.connectedAgent == "undefined")) {
                        _agent_event_handler.connectedAgent(e.data.list);
                    }

                } else if (e.data.event_type == _AGENT_DISCONNECTED) {
                    if (!(_agent_event_handler == null || typeof _agent_event_handler.disconnectedAgent == "undefined")) {
                        _agent_event_handler.disconnectedAgent(e.data.list);
                    }
                }
            }, false);

            // this script listen for chat transfer from other agent
            _chat_transfer_listener_worker = new Worker(_script_path + "agent/chat_transfer_listener_worker.js");
            
            // start the script
            _chat_transfer_listener_worker.postMessage({
                server_url: _chat_server_url,
                agent_id: _agent_id
            });

            // listen for chat transfer event
            _chat_transfer_listener_worker.addEventListener("message", (e) => {
                if (e.data.event_type == _CHAT_TRANSFER_REQUEST) {
                    if (!(_chat_transfer_handler == null || typeof _chat_transfer_handler.transferedRequest == "undefined")) {
                        _chat_transfer_handler.transferedRequest(e.data.list);
                    }

                } else if (e.data.event_type == _CLOSED_CHAT_TRANSFER) {
                    if (!(_chat_transfer_handler == null || typeof _chat_transfer_handler.closedTransferedRequest == "undefined")) {
                        _chat_transfer_handler.closedTransferedRequest(e.data.list);
                    }
                }
            }, false);
        }

        // this utility function start the worker that fetch client sent messages
        function _startMessageRetrieverWorker(retrieve_time_offset) {
            // check worker is not defined
            if (typeof _retrieve_msg_worker == "undefined" ) {
                // initialise the client's message retriever worker
                _retrieve_msg_worker = new Worker(_script_path + "retrieve_msg_worker.js");

                // start the script
                _retrieve_msg_worker.postMessage({
                    server_url: _chat_server_url,
                    req_user: "agent",
                    chat_id: _chat_id,
                    retrieve_time_offset: retrieve_time_offset
                });

                // listen to post message
                _retrieve_msg_worker.addEventListener("message", (e) => {
                    // pass message to client
                    if (e.data.status == "ok") {
                        if (_received_message_handler != null) {
                            _received_message_handler(e.data.messages);
                        }

                    } else if (e.data.status == "disconnected") { // agent exit the chat
                        // terminate chat
                        _terminateCLientChat();

                    } else { // connection lost
                        // terminate chat
                        _terminateCLientChat();
                    }
                }, false);
            }
        }

        // initiate chat session with connected client or customer
        function _initiateClientChat(chat_id, callback) {
            _chat_id = chat_id; // set the chat ID

            let form = new FormData();
            form.append("request", "initiate_client_chat");
            form.append("chat_id", chat_id);
            form.append("agent_id", _agent_id);

            // send request
            ajaxRequest(
                _chat_server_url,
                form,
                { contentType: false }, 
                function (response, status) {
                    let response_data = JSON.parse(response);

                    if (status == 200) {
                        if (response_data.status == "ok") {
                            _is_client_online = true;
                            _startMessageRetrieverWorker(0);
                            callback(_OK);

                        } else if (response_data.status == "client_attended") {
                            callback(_CLIENT_ATTENDED);

                        } else if (response_data.status == "client_offline") {
                            callback(_CLIENT_OFFLINE);
                        }

                    } else if (status == 408 || status == 504 || status == 503) { // check if is a timeout or server busy
                        _initiateClientChat(chat_id, callback);
        
                    } else { // no internet service or server error
                        callback(_FAILED);
                    }
                }
            );
        }

        // accept chat transfer request
        function _acceptChatTransfer(chat_id, callback) {
            // check if agent is not chatting with client
            if (_chatting_session) {
                callback(_FAILED, null);
                return;
            }

            let form = new FormData();
            form.append("request", "accept_chat_transfer");
            form.append("chat_id", chat_id);
            form.append("agent_id", _agent_id);

            // send request
            ajaxRequest(
                _chat_server_url,
                form,
                { contentType: false }, 
                function (response, status) {
                    let response_data = JSON.parse(response);

                    if (status == 200) {
                        if (response_data.status == "ok") {
                            _startMessageRetrieverWorker(response_data.next_time_offset);
                            callback(_OK, response_data.chat_history);
                        } else {
                            callback(_FAILED, null)
                        }

                    } else if (status == 408 || status == 504 || status == 503) { // check if is a timeout or server busy
                        _acceptChatTransfer(chat_id, callback);
        
                    } else { // no internet service or server error
                        callback(_FAILED, null);
                    }
                }
            );
        }

        // decline chat transfer request
        function _declineChatTransfer(chat_id) {
            let form = new FormData();
            form.append("request", "decline_chat_transfer");
            form.append("chat_id", chat_id);
            form.append("agent_id", _agent_id);

            // send request
            ajaxRequest(
                _chat_server_url,
                form,
                { contentType: false }, 
                function (response, status) {
                    // leave it empty
                }
            );
        }

        // send messge to the receiver
        function _sendMessage(receiver, message) {
            // check if communication has been established
            if (!_is_client_online) {
                return;
            }

            let form = new FormData();
            form.append("request", "send_msg");
            form.append("receiver", receiver);
            form.append("chat_id", _chat_id);
            form.append("message", message);

            // send request
            ajaxRequest(
                _chat_server_url,
                form,
                { contentType: false }, 
                function (response, status) {
                    // leave it empty
                }
            );
        }

        // send message to agent
        function _sendMessageAsClient(message) {
            _sendMessage("agent", message);
        }

        // send message to client
        function _sendMessageAsAgent(message) {
            _sendMessage("client", message);
        }

        // send the uploaded file(s) to receiver
        function _sendDocument(req_user, uploaded_files, message) {
            // don't send if user is not currently chating
            if (!_chatting_session) {
                return;
            }

            let form = new FormData();
            form.append("request", "send_uploaded_file");
            form.append("request_user", req_user);
            form.append("uploads", JSON.stringify(uploaded_files));
            form.append("message", message);
            form.append("chat_id", _chat_id);
            
            // send request
            ajaxRequest(
                _chat_server_url,
                form,
                { contentType: false }, 
                function (response, status) {
                    // leave it empty
                }
            );
        }

        // send the uploaded file(s) to receiver as client
        function _sendDocumentAsClient(uploaded_files, message) {
            _sendDocument("client", uploaded_files, message);
        }

        // send the uploaded file(s) to receiver as agent
        function _sendDocumentAsAgent(uploaded_files, message) {
            _sendDocument("agent", uploaded_files, message);
        }

        // upload the document and return the referal ID
        function _uploadDocument(files, callback) {
            // check if client or agent have active chat
            if (!_chatting_session) {
                callback(_UPLOAD_FAILED);
                return;
            }

            let form = new FormData();

            // append all the selected files
            for (let i = 0; i < files.length; i++) {
                form.append("files", files[i]);
            }

            form.append("request", "upload_file");
            form.append("chat_id", _chat_id);

            // upload the file(s) to server
            let xmlhttp = new XMLHttpRequest();
            xmlhttp.setRequestHeader("Content-type", "multipart/form-data");

            // listen to state change event
            xmlhttp.onreadystatechange = function () {
                if (xmlhttp.readyState == 1) {
                    callback(_UPLOADING, null);

                } else if (xmlhttp.readyState === 4 && xmlhttp.status === 200) {
                    // parse the respon
                    let response = JSON.parse(xmlhttp.responseText);

                    // check if file uploaded successfully
                    if (response.status == "ok") {
                        callback(_UPLOADED, response.uploaded_files);

                    } else if (response.status == "upload_failed") {
                        callback(_UPLOAD_FAILED, null);
                    }

                } else if (xmlhttp.status !== 200) {
                    callback(_UPLOAD_FAILED, null);
                }
            };

            // listen to upload progress and notify client about that
            xmlhttp.upload.addEventListener('progress', function(e){
                let progress = Math.ceil(e.loaded / e.total * 100);
                if (_upload_progress_listener != null) {
                    _upload_progress_listener(progress);
                }

            }, false);
            
            // send to server
            xmlhttp.open("POST", _chat_server_url, true);
            xmlhttp.send(form);
        }

        // connect agent to chat service
        function _connectAgent(user_info, department, callback) {
            _agent_department = department;

            let form = new FormData();
            form.append("request", "connect_agent");
            form.append("user_name", user_info.name);
            form.append("user_email", user_info.email);
            form.append("user_picture", user_info.picture);
            form.append("department", department);

            // send request
            ajaxRequest(
                _chat_server_url,
                form,
                { contentType: false }, 
                function (response, status) {
                    let response_data = JSON.parse(response);

                    if (status == 200) {
                        _agent_id = response_data.agent_id;
                        _is_connected = true;

                        _startAgentCommunicationProcess();
                        callback(_CONNECTED);

                    } else if (status == 408 || status == 504 || status == 503) { // check if is a timeout or server busy
                        callback(_SERVER_BUSY);
        
                    } else { // no internet service or server error
                        callback(_CONNECTION_FAILED);
                    }
                }
            );
        }

        // connect client to agent
        function _connectCLient(user_info, department, message, callback) {
            // connect to available agent
            let connect_client_worker = new Worker(_script_path + "client/connect_worker.js");

            // start the connection
            connect_client_worker.postMessage({
                server_url: _chat_server_url,
                user_name: user_info.name,
                user_picture: user_info.picture,
                user_email: user_info.email,
                department: department,
                message: message
            });

            // listen for response from chat server
            connect_client_worker.addEventListener("message", (e) => {
                let response = e.data.response;
                let http_status = e.data.http.status;

                if (http_status == 200) {
                    // check if awaiting conection to agent
                    if (response.status == "connecting") {
                        callback(_CONNECTING, null);

                    } else if (response.status == "connected") {
                        connect_client_worker.terminate();
                        _chat_id = response.chat_id;
                        _is_client_online = true;

                        callback(_CONNECTED, response.agent_info);
                        _startClientCommunicationProcess();

                    } else { // all the agent are busy
                        connect_client_worker.terminate();
                        callback(_AGENT_UNAVAILABLE, null);
                    }

                } else if (http_status == 408 || http_status == 504 || http_status == 503) { // check if is a timeout or server busy
                    connect_client_worker.terminate();
                    callback(_SERVER_BUSY, null);

                } else { // no internet service or server error
                    connect_client_worker.terminate();
                    callback(_CONNECTION_FAILED, null);
                }

            }, false);
        }

        // check if any agent is online or offline
        function _isAgentOnlineListener(callback) {
            // script that listen to agent online state
            let is_agent_online_listener = new Worker(_script_path + "agent/is_agent_online_listener_worker.js");
            
            // start the script
            is_agent_online_listener.postMessage({ 
                server_url: _chat_server_url
            });

            // listen for state change
            is_agent_online_listener.addEventListener("message", (e) => {
                if (e.data.status == "online") {
                    callback(_ONLINE);

                } else { // offline
                    callback(_OFFLINE);
                }
            });
        }

        // disconnect the client from chat server
        function _disconnectClient(callback) {
            // send agent message that communication is droped
            if (!_is_client_online) {
                callback(_OK);
                return;
            }

            let form = new FormData();
            form.append("request", "disconnect_client");
            form.append("chat_id", _chat_id);

            // send request
            ajaxRequest(
                _chat_server_url,
                form,
                { contentType: false }, 
                function (response, status) {
                    if (status == 200) {
                        // notify client that chat has successfully disconnected
                        if (_disconnect_listener != null) {
                            _disconnect_listener();
                        }

                        callback(_OK);

                        // terminate chat
                        _terminateChat();

                    } else {
                        callback(_FAILED);
                    }
                }
            );
        }

        // disconnect the agent from chat server
        function _disconnectAgent() {
            // send agent message that communication is droped
            if (!_is_connected) {
                return;
            }

            let form = new FormData();
            form.append("request", "disconnect_agent");
            form.append("chat_id", _chat_id);

            // send request
            ajaxRequest(
                _chat_server_url,
                form,
                { contentType: false }, 
                function (response, status) {
                    if (status == 200) {
                        callback(_OK);

                        // terminate all the initiated web worker
                        _ping_server_worker.terminate();
                        _ping_server_worker = undefined;
                        _client_listener_worker.terminate();
                        _client_listener_worker = undefined;
                        _agent_listener_worker.terminate();
                        _agent_listener_worker = undefined;
                        _chat_transfer_listener_worker.terminate();
                        _chat_transfer_listener_worker = undefined;
                        _retrieve_msg_worker.terminate();
                        _retrieve_msg_worker = undefined;

                        _is_connected = false;
                        _is_client_online = false;

                    } else {
                        callback(_FAILED);
                    }
                }
            );
        }

        // utility function to send request to server
        function ajaxRequest(_url, _form, _settings, _callback) {
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
        }

        // export functions
        if (_export == "agent") {
            return {
                connect: _connectAgent,
                messageListener: _messageListener,
                clientConnectionEventHandler: _clientConnectionEventHandler,
                agentConnectionEventHandler: _agentConnectionEventHandler,
                initiateClientChat: _initiateClientChat,
                sendMessage: _sendMessageAsAgent,
                sendDocument: _sendDocumentAsAgent,
                uploadDocument: _uploadDocument,
                uploadProgressListener: _uploadProgressListener,
                transferChatToAgent: _transferChatToAgent,
                chatTransferHandler: _chatTransferHandler,
                acceptChatTransfer: _acceptChatTransfer,
                declineChatTransfer: _declineChatTransfer,
                closeClientChatSession: _closeClientChatSession,
                disconnect: _disconnectAgent
            };

        } else if (_export == "client") {
            return {
                connect: _connectCLient,
                isAgentOnlineListener: _isAgentOnlineListener,
                messageListener: _messageListener,
                endChatSessionListener: _disconnectListener,
                sendMessage: _sendMessageAsClient,
                sendDocument: _sendDocumentAsClient,
                uploadDocument: _uploadDocument,
                uploadProgressListener: _uploadProgressListener,
                disconnect: _disconnectClient
            };
        }
    }

    // export public function for agent
    function _agent(settings) {
        return init("agent", settings);
    }

    // export public function for client
    function _client(settings) {
        return init("client", settings);
    }

    // call this function to initialise C2Chat.js
    window.C2Chat = function () {
        // export public function and constant
        return {
            // functions
            agent: _agent,
            client: _client,

            // constants
            ONLINE: _ONLINE,
            OFFLINE: _OFFLINE,

            CONNECTED: _CONNECTED,
            CONNECTION_FAILED: _CONNECTION_FAILED,
            CONNECTING: _CONNECTING,
            DISCONNECTED: _DISCONNECTED,
            CONNECTION_LOST: _CONNECTION_LOST,
            SERVER_BUSY: _SERVER_BUSY,
            AGENT_UNAVAILABLE: _AGENT_UNAVAILABLE,

            OK: _OK,
            FAILED: _FAILED,
            CLIENT_ATTENDED: _CLIENT_ATTENDED,
            CLIENT_OFFLINE: _CLIENT_OFFLINE,

            UPLOADING: _UPLOADING,
            UPLOADED: _UPLOADED,
            UPLOAD_FAILED: _UPLOAD_FAILED,

            CLIENT_CONNECTED: _CLIENT_CONNECTED,
            CLIENT_DISCONNECTED: _CLIENT_DISCONNECTED,
            CLIENT_HANDLED: _CLIENT_HANDLED,

            AGENT_CONNECTED: _AGENT_CONNECTED,
            AGENT_DISCONNECTED: _AGENT_DISCONNECTED
        };
    };
})()