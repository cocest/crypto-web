<?php 

// start session
session_start();

// import all the necessary liberaries
require_once '../includes/config.php';
require_once '../includes/utils.php'; // include utility liberary

// set default timezone
date_default_timezone_set('UTC');

// check if request method is post
if ($_SERVER['REQUEST_METHOD'] != 'POST') {
    header('Method Not Allowed', true, 405);
    die();
}

// check if URL has request query
if (!isset($_POST['request'])) {
    trigger_error('Request is not properly formed', E_USER_ERROR);
    die();
}

// get user's request command
$request = $_POST['request'];

// mysql configuration
$db = $config['db']['mysql'];

// process user chat request
switch($request) {
    case 'connect_client':
        // enable mysql exception
        mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

        try {
            // connect to database
            $conn = new mysqli($db['host'], $db['username'], $db['password'], $db['dbname']);

            // check connection
            if ($conn->connect_error) {
                throw new Exception('Database connection failed: '.$conn->connect_error);
            }

            $department = $_POST['department'];
            $agent_online = 1;

            // check if any agent is available
            $query = 'SELECT 1 FROM c2chat_agent_online WHERE department = ? AND online = ? LIMIT 1';
            $stmt = $conn->prepare($query); // prepare statement
            $stmt->bind_param('si', $department, $agent_online);
            $stmt->execute();
            $stmt->store_result(); // needed for num_rows

            if ($stmt->num_rows < 1) { // agent not available
                // send response back to client
                echo json_encode([
                    'status' => 'agent_unavailable'
                ]);

                // close connection to database
                $stmt->close();
                $conn->close();

                die(); // exit script
            }

            $stmt->close(); // close prepared statement

            $chat_id = bin2hex(openssl_random_pseudo_bytes(16)); // generate 32 digit unique chat ID
            $current_time = time(); // current UTC time
            $encoded_msg = base64_encode($_POST['message']);

            // add client chat request to connect table so that available agent can handle the request
            $query = 
                'INSERT INTO c2chat_client_online (
                    chatID, 
                    department, 
                    userName, 
                    userPicture, 
                    emailAddress,
                    message, 
                    pingTime,
                    time
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?)';
            $stmt = $conn->prepare($query); // prepare statement
            $stmt->bind_param(
                'ssssssii', 
                $chat_id, 
                $department,
                $_POST['user_name'],
                $_POST['user_picture'],
                $_POST['user_email'],
                $encoded_msg,
                $current_time,
                $current_time
            );
            $stmt->execute();

            // close connection to database
            $stmt->close();
            $conn->close();

            // send response back to client
            echo json_encode([
                'status' => 'connecting',
                'chat_id' => $chat_id
            ]);

        } catch (mysqli_sql_exception $e) { // catch only mysqli exceptions
            // log the error to a file
            error_log('Mysql error: '.$e->getMessage().PHP_EOL, 3, CUSTOM_ERR_DIR.'custom_errors.log');
        
        } catch (Exception $e) { // catch other exception
            // log the error to a file
            error_log('Caught exception: '.$e->getMessage().PHP_EOL, 3, CUSTOM_ERR_DIR.'custom_errors.log');
        }

        break;

    case 'connect_agent':
        // enable mysql exception
        mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

        try {
            // connect to database
            $conn = new mysqli($db['host'], $db['username'], $db['password'], $db['dbname']);

            // check connection
            if ($conn->connect_error) {
                throw new Exception('Database connection failed: '.$conn->connect_error);
            }

            $agent_id = bin2hex(openssl_random_pseudo_bytes(16)); // generate 32 digit unique agent ID
            $current_time = time(); // current UTC time

            // connect agent to server to handle client chat request
            $query = 
                'INSERT INTO c2chat_agent_online (
                    agentID,
                    department,
                    userName,
                    userPicture,
                    emailAddress,
                    pingTime,
                    time
                ) VALUES (?, ?, ?, ?, ?, ?, ?)';
            $stmt = $conn->prepare($query); // prepare statement
            $stmt->bind_param(
                'sssssii', 
                $agent_id, 
                $_POST['department'],
                $_POST['user_name'],
                $_POST['user_picture'],
                $_POST['user_email'],
                $current_time,
                $current_time
            );
            $stmt->execute();

            // close connection to database
            $stmt->close();
            $conn->close();

            // send response back to client
            echo json_encode([
                'status' => 'ok',
                'agent_id' => $agent_id
            ]);

        } catch (mysqli_sql_exception $e) { // catch only mysqli exceptions
            // log the error to a file
            error_log('Mysql error: '.$e->getMessage().PHP_EOL, 3, CUSTOM_ERR_DIR.'custom_errors.log');
        
        } catch (Exception $e) { // catch other exception
            // log the error to a file
            error_log('Caught exception: '.$e->getMessage().PHP_EOL, 3, CUSTOM_ERR_DIR.'custom_errors.log');
        }

        break;

    case 'check_connection':
        // enable mysql exception
        mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

        try {
            // connect to database
            $conn = new mysqli($db['host'], $db['username'], $db['password'], $db['dbname']);

            // check connection
            if ($conn->connect_error) {
                throw new Exception('Database connection failed: '.$conn->connect_error);
            }

            // check if chat request have been accepted by agent
            $query = 'SELECT chatInitiated, time FROM c2chat_client_online WHERE chatID = ? LIMIT 1';
            $stmt = $conn->prepare($query); // prepare statement
            $stmt->bind_param('s', $_POST['chat_id']);
            $stmt->execute();
            $stmt->store_result(); // needed for num_rows

            if ($stmt->num_rows > 0) {
                $stmt->bind_result($chat_initiated, $connected_time);
                $stmt->fetch();
                $stmt->close();

                // check if chat is accepted
                if ($chat_initiated == 1) {
                    // get agent profile information
                    $query = 'SELECT userName, userPicture FROM c2chat_agent_online WHERE chatID = ? LIMIT 1';
                    $stmt = $conn->prepare($query); // prepare statement
                    $stmt->bind_param('s', $_POST['chat_id']);
                    $stmt->execute();
                    $stmt->bind_result($user_name, $user_picture);
                    $stmt->fetch();
                    $stmt->close();

                    $conn->close(); // close connection to database

                    // send response back to client
                    echo json_encode([
                        'status' => 'connected',
                        'agent_info' => [
                            'name' => $user_name,
                            'picture' => $user_picture
                        ]
                    ]);
                    
                    die(); // exit script
                } 

                $time_duration = time() - $connected_time; // waited seconds in UTC
                $max_wait_time = 4 * 60; // 4 minutes
                
                // check if maximum wait time has been reached
                if ($time_duration > $max_wait_time) {
                    // set client connection status to offline
                    $query = 'UPDATE c2chat_client_online SET online = ?, time = ? WHERE chatID = ? LIMIT 1';
                    $stmt = $conn->prepare($query); // prepare statement
                    $stmt->bind_param('iis', $connection_status, $current_time, $_POST['chat_id']);
                    $connection_status = 0;
                    $current_time = time();
                    $stmt->execute();
                    $stmt->close();

                    // close connection to database
                    $conn->close();

                    // send response back to client
                    echo json_encode([
                        'status' => 'agent_unavailable'
                    ]);
                    
                    die(); // exit script

                } else { // chat not yet accepted
                    // close connection to database
                    $conn->close();

                    // send response back to client
                    echo json_encode([
                        'status' => 'connecting'
                    ]);
                    
                    die(); // exit script
                }

            } else {
                $stmt->close(); // close prepared statement
            }

            // close connection to database
            $conn->close();

            // send response back to client
            echo json_encode([
                'status' => 'agent_unavailable'
            ]);

        } catch (mysqli_sql_exception $e) { // catch only mysqli exceptions
            // log the error to a file
            error_log('Mysql error: '.$e->getMessage().PHP_EOL, 3, CUSTOM_ERR_DIR.'custom_errors.log');
        
        } catch (Exception $e) { // catch other exception
            // log the error to a file
            error_log('Caught exception: '.$e->getMessage().PHP_EOL, 3, CUSTOM_ERR_DIR.'custom_errors.log');
        }

        break;

    case 'disconnect_client':
        // enable mysql exception
        mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

        try {
            // connect to database
            $conn = new mysqli($db['host'], $db['username'], $db['password'], $db['dbname']);

            // check connection
            if ($conn->connect_error) {
                throw new Exception('Database connection failed: '.$conn->connect_error);
            }

            // disconnect client chat session
            $query = 'UPDATE c2chat_client_online SET online = ?, time = ? WHERE chatID = ? LIMIT 1';
            $stmt = $conn->prepare($query); // prepare statement
            $stmt->bind_param('iis', $connection_status, $current_time, $_POST['chat_id']);
            $connection_status = 0;
            $current_time = time();
            $stmt->execute();
            $stmt->close();

            // close connection to database
            $conn->close();

            // send response back to client
            echo json_encode([
                'status' => 'ok'
            ]);

        } catch (mysqli_sql_exception $e) { // catch only mysqli exceptions
            // log the error to a file
            error_log('Mysql error: '.$e->getMessage().PHP_EOL, 3, CUSTOM_ERR_DIR.'custom_errors.log');
        
        } catch (Exception $e) { // catch other exception
            // log the error to a file
            error_log('Caught exception: '.$e->getMessage().PHP_EOL, 3, CUSTOM_ERR_DIR.'custom_errors.log');
        }

        break;

    case 'terminate_chat':
        // enable mysql exception
        mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

        try {

            // connect to database
            $conn = new mysqli($db['host'], $db['username'], $db['password'], $db['dbname']);

            // check connection
            if ($conn->connect_error) {
                throw new Exception('Database connection failed: '.$conn->connect_error);
            }

            // reset agent's client chat session
            $query = 'UPDATE c2chat_agent_online SET chatID = ?, chatting = ?, time = ? WHERE chatID = ? LIMIT 1';
            $stmt = $conn->prepare($query); // prepare statement
            $stmt->bind_param('siis', $chat_id, $chat_status, $current_time, $_POST['chat_id']);
            $chat_id = '';
            $chat_status = 0;
            $current_time = time();
            $stmt->execute();
            $stmt->close();

            // disconnect client
            $query = 'UPDATE c2chat_client_online SET online = ? WHERE chatID = ? LIMIT 1';
            $stmt = $conn->prepare($query); // prepare statement
            $stmt->bind_param('is', $client_online, $_POST['chat_id']);
            $client_online = 0;
            $stmt->execute();
            $stmt->close();

            // close connection to database
            $conn->close();

            // send response back to client
            echo json_encode([
                'status' => 'ok'
            ]);

        } catch (mysqli_sql_exception $e) { // catch only mysqli exceptions
            // log the error to a file
            error_log('Mysql error: '.$e->getMessage().PHP_EOL, 3, CUSTOM_ERR_DIR.'custom_errors.log');
        
        } catch (Exception $e) { // catch other exception
            // log the error to a file
            error_log('Caught exception: '.$e->getMessage().PHP_EOL, 3, CUSTOM_ERR_DIR.'custom_errors.log');
        }

        break;

    case 'is_agent_online':
        // enable mysql exception
        mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

        try {
            // connect to database
            $conn = new mysqli($db['host'], $db['username'], $db['password'], $db['dbname']);

            // check connection
            if ($conn->connect_error) {
                throw new Exception('Database connection failed: '.$conn->connect_error);
            }

            $query = 'SELECT 1 FROM c2chat_agent_online WHERE online = ? AND pingTime > ? LIMIT 1';
            $stmt = $conn->prepare($query); // prepare statement
            $stmt->bind_param('ii', $agent_online, $ping_time);
            $agent_online = 1;
            $ping_time = time() - 120; // minus 2 minutes
            $stmt->execute();
            $stmt->store_result(); // needed for num_rows

            if ($stmt->num_rows > 0) { // agent is online
                // send response back to client
                echo json_encode([
                    'status' => 'online'
                ]);

            } else { // agent is offline
                // send response back to client
                echo json_encode([
                    'status' => 'offline'
                ]);
            }

            // close connection to database
            $stmt->close();
            $conn->close();

            die(); // exit script

        } catch (mysqli_sql_exception $e) { // catch only mysqli exceptions
            // log the error to a file
            error_log('Mysql error: '.$e->getMessage().PHP_EOL, 3, CUSTOM_ERR_DIR.'custom_errors.log');
        
        } catch (Exception $e) { // catch other exception
            // log the error to a file
            error_log('Caught exception: '.$e->getMessage().PHP_EOL, 3, CUSTOM_ERR_DIR.'custom_errors.log');
        }

        break;

    case 'ping':
        // enable mysql exception
        mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

        try {
            // connect to database
            $conn = new mysqli($db['host'], $db['username'], $db['password'], $db['dbname']);

            // check connection
            if ($conn->connect_error) {
                throw new Exception('Database connection failed: '.$conn->connect_error);
            }

            if ($_POST['request_user'] == 'client') {
                $query = 'UPDATE c2chat_client_online SET pingTime = ? WHERE chatID = ? LIMIT 1';
                $table_id = $_POST['chat_id'];

            } else { // agent
                $query = 'UPDATE c2chat_agent_online SET pingTime = ? WHERE agentID = ? LIMIT 1';
                $table_id = $_POST['agent_id'];
            }

            // update user's connection state
            $stmt = $conn->prepare($query); // prepare statement
            $stmt->bind_param('is', $current_time, $table_id);
            $current_time = time();
            $stmt->execute();
            $stmt->close();

            // close connection to database
            $conn->close();

            // send response back to client
            echo json_encode([
                'status' => 'ok'
            ]);

        } catch (mysqli_sql_exception $e) { // catch only mysqli exceptions
            // log the error to a file
            error_log('Mysql error: '.$e->getMessage().PHP_EOL, 3, CUSTOM_ERR_DIR.'custom_errors.log');
        
        } catch (Exception $e) { // catch other exception
            // log the error to a file
            error_log('Caught exception: '.$e->getMessage().PHP_EOL, 3, CUSTOM_ERR_DIR.'custom_errors.log');
        }

        break;

    case 'client_connect_state':
        // enable mysql exception
        mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

        try {
            // connect to database
            $conn = new mysqli($db['host'], $db['username'], $db['password'], $db['dbname']);

            // check connection
            if ($conn->connect_error) {
                throw new Exception('Database connection failed: '.$conn->connect_error);
            }

            $next_time_offset = $_POST['time_offset'];

            // get client status changes after last check
            $query = 'SELECT * FROM c2chat_client_online WHERE department = ?';
            $stmt = $conn->prepare($query); // prepare statement
            $stmt->bind_param('s', $_POST['department']);
            $stmt->execute();
            $result = $stmt->get_result();

            $connected_client = [];
            $disconnected_client = [];
            $handled_client = [];

            // iterate through the row
            while ($row = $result->fetch_assoc()) {
                // check if the update occure since the last update
                if ($row['time'] > $_POST['time_offset']) {
                    if ($row['online'] == 1 && $row['chatInitiated'] == 0) { // user connected
                        $connected_client[] = [
                            'client' => [
                                'name' => $row['userName'],
                                'picture' => $row['userPicture'],
                                'email' => $row['emailAddress']
                            ],
                            'message' => base64_decode($row['message']),
                            'time' => $row['time'],
                            'chat_id' => $row['chatID']
                        ];
                    }
    
                    if ($row['chatInitiated'] == 1 && $row['online'] == 1) { // chat initiated
                        $handled_client[] = [
                            'client' => [
                                'name' => $row['userName'],
                                'picture' => $row['userPicture'],
                                'email' => $row['emailAddress']
                            ],
                            'time' => $row['time'],
                            'chat_id' => $row['chatID']
                        ];
                    }
                }

                if ($row['online'] == 0 || (time() - $row['pingTime']) > (2 * 60)) { // client offline or disconnected from server
                    $disconnected_client[] = [
                        'client' => [
                            'name' => $row['userName'],
                            'picture' => $row['userPicture'],
                            'email' => $row['emailAddress']
                        ],
                        'time' => $row['time'],
                        'chat_id' => $row['chatID']
                    ];
                }

                // calculate for next time offset
                if ($row['time'] > $next_time_offset) {
                    $next_time_offset = $row['time'];
                }
            }

            // close connection to database
            $stmt->close();
            $conn->close();

            // prepare the result
            $response = [
                'status' => 'ok',
                'time_offset' => $next_time_offset
            ];

            if (count($connected_client) > 0) {
                $response['connected_client'] = $connected_client;
            }

            if (count($handled_client) > 0) {
                $response['handled_client'] = $handled_client;
            }

            if (count($disconnected_client) > 0) {
                $response['disconnected_client'] = $disconnected_client;
            }

            // send result back to client
            echo json_encode($response);

        } catch (mysqli_sql_exception $e) { // catch only mysqli exceptions
            // log the error to a file
            error_log('Mysql error: '.$e->getMessage().PHP_EOL, 3, CUSTOM_ERR_DIR.'custom_errors.log');
        
        } catch (Exception $e) { // catch other exception
            // log the error to a file
            error_log('Caught exception: '.$e->getMessage().PHP_EOL, 3, CUSTOM_ERR_DIR.'custom_errors.log');
        }

        break;

    case 'agent_connect_state':
        // enable mysql exception
        mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

        try {
            // connect to database
            $conn = new mysqli($db['host'], $db['username'], $db['password'], $db['dbname']);

            // check connection
            if ($conn->connect_error) {
                throw new Exception('Database connection failed: '.$conn->connect_error);
            }

            $next_time_offset = $_POST['time_offset'];

            // get agent status changes after last check
            $query = 'SELECT * FROM c2chat_agent_online';
            $stmt = $conn->prepare($query); // prepare statement
            $stmt->execute();
            $result = $stmt->get_result();

            $connected_agent = [];
            $disconnected_agent = [];

            // iterate through the row
            while ($row = $result->fetch_assoc()) {
                if ($row['time'] > $_POST['time_offset'] && $row['online'] == 1) { // user connected
                    $connected_agent[] = [
                        'agent' => [
                            'name' => $row['userName'],
                            'picture' => $row['userPicture'],
                            'email' => $row['emailAddress']
                        ],
                        'agent_id' => $row['agentID'],
                        'time' => $row['time']
                    ];
                }

                if ($row['online'] == 0 || (time() - $row['pingTime']) > (2 * 60)) { // agent offline or disconnected from server
                    $disconnected_agent[] = [
                        'agent' => [
                            'name' => $row['userName'],
                            'picture' => $row['userPicture'],
                            'email' => $row['emailAddress']
                        ],
                        'agent_id' => $row['agentID'],
                        'time' => $row['time']
                    ];
                }

                // calculate for next time offset
                if ($row['time'] > $next_time_offset) {
                    $next_time_offset = $row['time'];
                }
            }

            // close connection to database
            $stmt->close();
            $conn->close();

            // prepare the result
            $response = [
                'status' => 'ok',
                'time_offset' => $next_time_offset
            ];

            if (count($connected_agent) > 0) {
                $response['connected_agent'] = $connected_agent;
            }

            if (count($disconnected_agent) > 0) {
                $response['disconnected_agent'] = $disconnected_agent;
            }

            // send result back to client
            echo json_encode($response);

        } catch (mysqli_sql_exception $e) { // catch only mysqli exceptions
            // log the error to a file
            error_log('Mysql error: '.$e->getMessage().PHP_EOL, 3, CUSTOM_ERR_DIR.'custom_errors.log');
        
        } catch (Exception $e) { // catch other exception
            // log the error to a file
            error_log('Caught exception: '.$e->getMessage().PHP_EOL, 3, CUSTOM_ERR_DIR.'custom_errors.log');
        }

        break;

    case 'chat_transfer':
        // enable mysql exception
        mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

        try {
            // connect to database
            $conn = new mysqli($db['host'], $db['username'], $db['password'], $db['dbname']);

            // check connection
            if ($conn->connect_error) {
                throw new Exception('Database connection failed: '.$conn->connect_error);
            }

            // notify the agent for chat transfer
            $query = 
                'UPDATE c2chat_agent_online 
                SET 
                    transferChatTo = ?, 
                    chatTransferInitiatedTime = ?, 
                    chatTransferRequestClosed = ?, 
                    chatTransferRequestAccepted = ? 
                WHERE chatID = ? LIMIT 1';

            $stmt = $conn->prepare($query); // prepare statement
            $stmt->bind_param(
                'siiis', 
                $_POST['agent_id'], 
                $current_time, 
                $default_value, 
                $default_value, 
                $_POST['chat_id']
            );
            $current_time = time();
            $default_value = 0;
            $stmt->execute();
            $stmt->close();

            // close connection to database
            $conn->close();

            // send response back to client
            echo json_encode([
                'status' => 'ok'
            ]);

        } catch (mysqli_sql_exception $e) { // catch only mysqli exceptions
            // log the error to a file
            error_log('Mysql error: '.$e->getMessage().PHP_EOL, 3, CUSTOM_ERR_DIR.'custom_errors.log');
        
        } catch (Exception $e) { // catch other exception
            // log the error to a file
            error_log('Caught exception: '.$e->getMessage().PHP_EOL, 3, CUSTOM_ERR_DIR.'custom_errors.log');
        }

        break;

    case 'accept_chat_transfer':
        // enable mysql exception
        mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

        try {
            // connect to database
            $conn = new mysqli($db['host'], $db['username'], $db['password'], $db['dbname']);

            // check connection
            if ($conn->connect_error) {
                throw new Exception('Database connection failed: '.$conn->connect_error);
            }

            // check if agent have closed the request or request timeout
            $query = 
                'SELECT 
                    chatTransferInitiatedTime, 
                    chatTransferRequestClosed  
                FROM c2chat_agent_online WHERE chatID = ? LIMIT 1';

            $stmt = $conn->prepare($query); // prepare statement
            $stmt->bind_param('s', $_POST['chat_id']);
            $stmt->execute();
            $stmt->store_result(); // needed for num_rows

            if ($stmt->num_rows > 0) {
                $stmt->bind_result($initiated_time, $request_closed);
                $stmt->fetch();
                $stmt->close();

                // request closed or timeout
                if ($request_closed == 1 || (time() - $initiated_time) > (4 * 60)) {
                    // close connection to database
                    $conn->close();

                    // send response back to client
                    echo json_encode([
                        'status' => 'closed'
                    ]);

                    die(); // exit script
                }

            } else {
                // close connection to database
                $stmt->close();
                $conn->close();

                // send response back to client
                echo json_encode([
                    'status' => 'agent_offline'
                ]);

                die(); // exit script
            }

            $agent_profile_username = '';
            $agent_profile_picture = '';
            $client_profile_username = '';
            $client_profile_picture = '';

            // get agent profile information
            $query = 'SELECT userName, userPicture FROM c2chat_agent_online WHERE agentID = ? LIMIT 1';
            $stmt = $conn->prepare($query); // prepare statement
            $stmt->bind_param('s', $_POST['agent_id']);
            $stmt->execute();
            $stmt->bind_result($agent_profile_username, $agent_profile_picture);
            $stmt->fetch();
            $stmt->close();

            // get client profile information
            $query = 'SELECT userName, userPicture FROM c2chat_client_online WHERE chatID = ? LIMIT 1';
            $stmt = $conn->prepare($query); // prepare statement
            $stmt->bind_param('s', $_POST['chat_id']);
            $stmt->execute();
            $stmt->bind_result($client_profile_username, $client_profile_picture);
            $stmt->fetch();
            $stmt->close();

            $next_time_offset = 0;

            // get next retrieve time offset
            $query = 
                'SELECT MAX(time) AS time_offset FROM c2chat_chat_messages 
                 WHERE chatID = ?';
            $stmt = $conn->prepare($query); // prepare statement
            $stmt->bind_param('s', $_POST['chat_id']);
            $stmt->execute();
            $stmt->bind_result($next_time_offset);
            $stmt->fetch();
            $stmt->close();

            // set to value 0 if chat messages is empty
            $next_time_offset = empty($next_time_offset) ? 0 : $next_time_offset;

            // retrieve all chat history
            $query = 
                'SELECT receiver, messageID, message, retrieveFileID, time FROM c2chat_chat_messages 
                WHERE chatID = ? ORDER BY time ASC';
            $stmt = $conn->prepare($query); // prepare statement
            $stmt->bind_param('s', $_POST['chat_id']);
            $stmt->execute();
            $result = $stmt->get_result();

            // prepared statement to fetch attached file(s) if any
            $query_2 = 'SELECT * FROM c2chat_uploaded_files WHERE retrieveFileID = ?';
            $stmt_2 = $conn->prepare($query_2); // prepare statement
            $stmt_2->bind_param('s', $retrieve_file_id);

            $messages = [];
 
            // organise the message
            while ($row = $result->fetch_assoc()) {
                // check if there is attachment
                if (empty($row['retrieveFileID'])) {
                    // retrieve the uploaded file(s)
                    $retrieve_file_id = $row['retrieveFileID'];
                    $stmt_2->execute();
                    $result_2 = $stmt_2->get_result();

                    $uploaded_files = [];

                    while ($row_2 = $result_2->fetch_assoc()) {
                        $uploaded_files[] = [
                            'name' => $row_2['name'],
                            'type' => $row_2['type'],
                            'size' => $row_2['size'],
                            'ref_id' => $row_2['referenceID'], 
                            'url' => $row_2['path']
                        ];
                    }

                    if ($row['receiver'] == 'client') { // message sent by agent
                        $messages[] = [
                            'msg_id' => $row['messageID'],
                            'message' => base64_decode($row['message']),
                            'attachment' => $uploaded_files,
                            'sender_profile' => [
                                'name' => $agent_profile_username,
                                'picture' => $agent_profile_picture
                            ],
                            'time' => $row['time']
                        ];

                    } else if ($row['receiver'] == 'agent') { // message sent by client
                        $messages[] = [
                            'msg_id' => $row['messageID'],
                            'message' => base64_decode($row['message']),
                            'attachment' => $uploaded_files,
                            'sender_profile' => [
                                'name' => $client_profile_username,
                                'picture' => $client_profile_picture
                            ],
                            'time' => $row['time']
                        ];
                    }

                } else { // no attachment
                    if ($row['receiver'] == 'client') { // message sent by agent
                        $messages[] = [
                            'msg_id' => $row['messageID'],
                            'message' => base64_decode($row['message']),
                            'sender_profile' => [
                                'name' => $agent_profile_username,
                                'picture' => $agent_profile_picture
                            ],
                            'time' => $row['time']
                        ];

                    } else if ($row['receiver'] == 'agent') { // message sent by client
                        $messages[] = [
                            'msg_id' => $row['messageID'],
                            'message' => base64_decode($row['message']),
                            'sender_profile' => [
                                'name' => $client_profile_username,
                                'picture' => $client_profile_picture
                            ],
                            'time' => $row['time']
                        ];
                    }
                }
            }

            // close connection to database
            $stmt->close();
            $stmt_2->close();
            $conn->close();

            // send result back to client
            echo json_encode([
                'status' => 'ok',
                'chat_history' => $messages,
                'time_offset' => $next_time_offset
            ]);

        } catch (mysqli_sql_exception $e) { // catch only mysqli exceptions
            // log the error to a file
            error_log('Mysql error: '.$e->getMessage().PHP_EOL, 3, CUSTOM_ERR_DIR.'custom_errors.log');
        
        } catch (Exception $e) { // catch other exception
            // log the error to a file
            error_log('Caught exception: '.$e->getMessage().PHP_EOL, 3, CUSTOM_ERR_DIR.'custom_errors.log');
        }

        break;

    case 'chat_transfer_accepted':
        // enable mysql exception
        mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

        try {
            // connect to database
            $conn = new mysqli($db['host'], $db['username'], $db['password'], $db['dbname']);

            // check connection
            if ($conn->connect_error) {
                throw new Exception('Database connection failed: '.$conn->connect_error);
            }

            // check if agent have accepted or decline the request
            $query = 
                'SELECT 
                    chatTransferInitiatedTime, 
                    chatTransferRequestClosed, 
                    chatTransferRequestAccepted 
                FROM c2chat_agent_online WHERE agentID = ? LIMIT 1';

            $stmt = $conn->prepare($query); // prepare statement
            $stmt->bind_param('s', $_POST['agent_id']);
            $stmt->execute();
            $stmt->store_result(); // needed for num_rows

            if ($stmt->num_rows > 0) {
                $stmt->bind_result($initiated_time, $request_closed, $request_accepted);
                $stmt->fetch();
                $stmt->close();

                // requested accepted bt agent
                if ($request_accepted == 1) {
                    // close connection to database
                    $conn->close();

                    // send response back to client
                    echo json_encode([
                        'status' => 'ok'
                    ]);

                    die(); // exit script
                }

                // request closed or timeout
                if ($request_closed == 1 || (time() - $initiated_time) > (4 * 60)) {
                    // close connection to database
                    $conn->close();

                    // send response back to client
                    echo json_encode([
                        'status' => 'closed'
                    ]);

                    die(); // exit script
                }

            } else {
                $stmt->close();
            }

            // close connection to database
            $conn->close();

            // send response back to client
            echo json_encode([
                'status' => 'closed'
            ]);

        } catch (mysqli_sql_exception $e) { // catch only mysqli exceptions
            // log the error to a file
            error_log('Mysql error: '.$e->getMessage().PHP_EOL, 3, CUSTOM_ERR_DIR.'custom_errors.log');
        
        } catch (Exception $e) { // catch other exception
            // log the error to a file
            error_log('Caught exception: '.$e->getMessage().PHP_EOL, 3, CUSTOM_ERR_DIR.'custom_errors.log');
        }

        break;

    case 'chat_transfer_state':
        // enable mysql exception
        mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

        try {
            // connect to database
            $conn = new mysqli($db['host'], $db['username'], $db['password'], $db['dbname']);

            // check connection
            if ($conn->connect_error) {
                throw new Exception('Database connection failed: '.$conn->connect_error);
            }

            // check for chat transfer request or closed chat transfer request
            $query = 
                'SELECT 
                    A.chatID, 
                    A.chatTransferInitiatedTime, 
                    A.chatTransferRequestClosed, 
                    B.userName, 
                    B.userPicture, 
                    B.emailAddress, 
                    B.message, 
                    A.time 
                 FROM c2chat_agent_online AS A LEFT JOIN c2chat_client_online AS B ON A.chatID = B.chatID 
                 WHERE A.transferChatTo = ? AND A.time > ?';
            $stmt = $conn->prepare($query); // prepare statement
            $stmt->bind_param('si', $_POST['agent_id'], $_POST['time_offset']);
            $stmt->execute();
            $result = $stmt->get_result();

            $next_time_offset = $_POST['time_offset'];
            $request = [];
            $closed_request = [];

            // iterate through the row
            while ($row = $result->fetch_assoc()) {
                if ($row['chatTransferRequestClosed'] == 0 && 
                    (time() - $row['chatTransferInitiatedTime']) < (4 * 60)) { // user connected
                    
                    $request[] = [
                        'chat_id' => $row['chatID'],
                        'client' => [
                            'name' => $row['userName'],
                            'picture' => $row['userPicture'],
                            'email' => $row['emailAddress']
                        ],
                        'message' => $row['message']
                    ];
                }

                if ($row['chatTransferRequestClosed'] == 1 || 
                    (time() - $row['chatTransferInitiatedTime']) > (4 * 60)) { // chat transfer request closed or timeout
                    
                    $closed_request[] = [
                        'chat_id' => $row['chatID']
                    ];
                }

                // calculate for next time offset
                if ($row['time'] > $next_time_offset) {
                    $next_time_offset = $row['time'];
                }
            }

            // close connection to database
            $stmt->close();
            $conn->close();

            // prepare the result
            $response = [
                'status' => 'ok',
                'time_offset' => $next_time_offset
            ];

            if (count($request) > 0) {
                $response = array_merge($response, [
                    'request' => $request
                ]);
            }

            if (count($closed_request) > 0) {
                $response = array_merge($response, [
                    'closed_request' => $closed_request
                ]);
            }

            // send result back to client
            echo json_encode($response);

        } catch (mysqli_sql_exception $e) { // catch only mysqli exceptions
            // log the error to a file
            error_log('Mysql error: '.$e->getMessage().PHP_EOL, 3, CUSTOM_ERR_DIR.'custom_errors.log');
        
        } catch (Exception $e) { // catch other exception
            // log the error to a file
            error_log('Caught exception: '.$e->getMessage().PHP_EOL, 3, CUSTOM_ERR_DIR.'custom_errors.log');
        }

        break;

    case 'initiate_client_chat':
        // enable mysql exception
        mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

        try {
            // connect to database
            $conn = new mysqli($db['host'], $db['username'], $db['password'], $db['dbname']);

            // check connection
            if ($conn->connect_error) {
                throw new Exception('Database connection failed: '.$conn->connect_error);
            }

            // check if client is attend by another agent or offline
            $query = 'SELECT online, chatInitiated, pingTime FROM c2chat_client_online WHERE chatID = ? LIMIT 1';
            $stmt = $conn->prepare($query); // prepare statement
            $stmt->bind_param('s', $_POST['chat_id']);
            $stmt->execute();
            $stmt->store_result(); // needed for num_rows

            if ($stmt->num_rows > 0) {
                $stmt->bind_result($online, $chat_initiated, $ping_time);
                $stmt->fetch();
                $stmt->close();

                if ($online == 0) {
                    // close connection to database
                    $conn->close();

                    // send response back to client
                    echo json_encode([
                        'status' => 'client_offline'
                    ]);

                    die(); // exit script
                }

                if ($chat_initiated == 1) {
                    // close connection to database
                    $conn->close();

                    // send response back to client
                    echo json_encode([
                        'status' => 'client_attended'
                    ]);

                    die(); // exit script
                }

                if ((time() - $ping_time) > (4 * 60)) {
                    // close connection to database
                    $conn->close();

                    // send response back to client
                    echo json_encode([
                        'status' => 'client_offline'
                    ]);

                    die(); // exit script
                }

            } else {
                // close connection to database
                $stmt->close();
                $conn->close();

                // send response back to client
                echo json_encode([
                    'status' => 'client_offline'
                ]);

                die(); // exit script
            }

            try {
                $conn->begin_transaction(); // start transaction

                // update agent chat state
                $query = 'UPDATE c2chat_agent_online SET chatID = ?, chatting = ? WHERE agentID = ? LIMIT 1';
                $stmt = $conn->prepare($query); // prepare statement
                $stmt->bind_param('sis', $_POST['chat_id'], $chatting, $_POST['agent_id']);
                $chatting = 1;
                $stmt->execute();
                $stmt->close();

                // update client chat state
                $query = 'UPDATE c2chat_client_online SET chatInitiated = ? WHERE chatID = ? LIMIT 1';
                $stmt = $conn->prepare($query); // prepare statement
                $stmt->bind_param('is', $chat_initiated, $_POST['chat_id']);
                $chat_initiated = 1;
                $stmt->execute();
                $stmt->close();

                $conn->commit(); // commit all the transaction

                // close connection to database
                $conn->close();

                // send response back to client
                echo json_encode([
                    'status' => 'ok'
                ]);

                die(); // exit script

            } catch (Exception $e) {
                $conn->rollback(); // remove all queries from queue if error occured (undo)
            }

        } catch (mysqli_sql_exception $e) { // catch only mysqli exceptions
            // log the error to a file
            error_log('Mysql error: '.$e->getMessage().PHP_EOL, 3, CUSTOM_ERR_DIR.'custom_errors.log');
        
        } catch (Exception $e) { // catch other exception
            // log the error to a file
            error_log('Caught exception: '.$e->getMessage().PHP_EOL, 3, CUSTOM_ERR_DIR.'custom_errors.log');
        }

        break;

    case 'send_msg':
        // enable mysql exception
        mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

        try {
            // connect to database
            $conn = new mysqli($db['host'], $db['username'], $db['password'], $db['dbname']);

            // check connection
            if ($conn->connect_error) {
                throw new Exception('Database connection failed: '.$conn->connect_error);
            }

            $msg_reciever = $_POST['receiver']; // receiver of the message
            $encoded_msg = base64_encode($_POST['message']);
            $msg_id = bin2hex(openssl_random_pseudo_bytes(16)); // generate 32 digit unique message ID;
            $current_time = time(); // UTC time in seconds

            // send text message to agent
            $query = 
                'INSERT INTO c2chat_chat_messages (
                    messageID, 
                    chatID, 
                    receiver, 
                    message,
                    time
                ) VALUES (?, ?, ?, ?, ?)';
            $stmt = $conn->prepare($query); // prepare statement
            $stmt->bind_param(
                'ssssi', 
                $msg_id, 
                $_POST['chat_id'],
                $msg_reciever,
                $encoded_msg,
                $current_time
            );
            $stmt->execute();
            $stmt->close();

            // close connection to database
            $conn->close();

            // send response back to client
            echo json_encode([
                'status' => 'ok'
            ]);

        } catch (mysqli_sql_exception $e) { // catch only mysqli exceptions
            // log the error to a file
            error_log('Mysql error: '.$e->getMessage().PHP_EOL, 3, CUSTOM_ERR_DIR.'custom_errors.log');
        
        } catch (Exception $e) { // catch other exception
            // log the error to a file
            error_log('Caught exception: '.$e->getMessage().PHP_EOL, 3, CUSTOM_ERR_DIR.'custom_errors.log');
        }

        break;

    case 'upload_file':
        // enable mysql exception
        mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

        try {
            // validate uploaded files type and it size
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            $counter = 0;

            // iterate through the file(s)
            foreach ($_FILES['file']['tmp_name'] as $uploaded_file) {
                $type = finfo_file($finfo, $uploaded_file);

                if (isset($type)) {
                    if (in_array($type, [
                        'image/jpg', 
                        'image/jpeg', 
                        'image/png', 
                        'image/gif'
                    ])) { // images
                        if ($_FILES['file']['size'][$counter] / 1048576 > 2) { // greater than 2 megabytes
                            // send response back to client
                            echo json_encode([
                                'status' => 'upload_failed'
                            ]);

                            die(); // exit the script
                        }

                    } else if (in_array($type, [
                        'text/plain', // txt
                        'application/rtf', // rtf
                        'application/msword', // doc
                        'application/pdf', // pdf
                        'application/vnd.openxmlformats-officedocument.wordprocessingml.document' // docx
                    ])) { // documents
                        if ($_FILES['file']['size'][$counter] / 1048576 > 8) { // greater than 8 megabytes
                            // send response back to client
                            echo json_encode([
                                'status' => 'upload_failed'
                            ]);

                            die(); // exit the script
                        }

                    } else { // unsupported file
                        // send response back to client
                        echo json_encode([
                            'status' => 'upload_failed'
                        ]);

                        die(); // exit the script
                    }

                } else { // unknown file
                    // send response back to client
                    echo json_encode([
                        'status' => 'upload_failed'
                    ]);

                    die(); // exit the script
                }

                $counter++; // increment counter by one
            }

            $moved_file_meta_info = [];
            $counter = 0;

            // move file(s) to a directory and save the meta data to array
            foreach ($_FILES['file']['tmp_name'] as $uploaded_file) {
                $img_ext = pathinfo($_FILES['file']['name'][$counter], PATHINFO_EXTENSION);
                $unique_id = bin2hex(openssl_random_pseudo_bytes(16)); // 32 digit
                $file_name = $unique_id.'.'.$img_ext;
                $target_dir = C2CHAT_UPLOAD_DIR.$file_name;

                // move file and check if file failed to be moved
                if (!move_uploaded_file($uploaded_file, $target_dir)) {
                    // delete all the uploaded files
                    foreach ($moved_file_meta_info as $file_meta_info) {
                        unlink($file_meta_info['path']);
                    }

                    // send response back to client
                    echo json_encode([
                        'status' => 'upload_failed'
                    ]);

                    die(); // exit the script
                }

                $pieces = explode('.', $_FILES['file']['name']);

                $moved_file_meta_info[] = [
                    'ref_id' => $unique_id,
                    'path' => $target_dir,
                    'ref_name' => $file_name,
                    'name' => $pieces[0],
                    'type' => finfo_file($finfo, $uploaded_file),
                    'size' => $_FILES['file']['size'][$counter] // size in bytes
                ];
            }

            // connect to database
            $conn = new mysqli($db['host'], $db['username'], $db['password'], $db['dbname']);

            // check connection
            if ($conn->connect_error) {
                throw new Exception('Database connection failed: '.$conn->connect_error);
            }
            
            // save the meta data to database
            try {
                $conn->begin_transaction(); // start transaction

                // insert all the upload file's meta info to database
                foreach($moved_file_meta_info as $file_meta_info) {
                    // insert info to database table
                    $query = 
                        'INSERT INTO c2chat_uploaded_files (
                            referenceID, 
                            chatID, 
                            path, 
                            name, 
                            type, 
                            size
                        ) VALUES (?, ?, ?, ?, ?, ?)';
                    $stmt = $conn->prepare($query); // prepare statement
                    $stmt->bind_param(
                        'sssssi', 
                        $file_meta_info['ref_id'], 
                        $_POST['chat_id'],
                        $file_meta_info['path'],
                        $file_meta_info['name'],
                        $file_meta_info['type'],
                        $file_meta_info['size']
                    );
                    $stmt->execute();
                    $stmt->close();
                }

                $conn->commit(); // commit all the transaction

                // close connection to database
                $conn->close();

                // send upload files' reference ID and URL back to uploader
                $uploaded_files = [];

                foreach ($moved_file_meta_info as $file_meta_info) {
                    $response[] = [
                        'ref_id' => $file_meta_info['ref_id'],
                        'url' => C2CHAT_UPLOAD_URL.$file_meta_info['ref_name']
                    ];
                }

                echo json_encode([
                    'status' => 'ok',
                    'uploaded_files' => $uploaded_files
                ]);

                die(); // exit script
                
            } catch (mysqli_sql_exception $e) {
                // remove all queries from queue if error occured (undo)
                $conn->rollback();
                $conn->close();

                // delete all the uploaded files
                foreach ($moved_file_meta_info as $file_meta_info) {
                    unlink($file_meta_info['path']);
                }

                throw new mysqli_sql_exception($e->getMessage());

            } catch (Exception $e) {
                // close database connection
                $conn->close();
                
                // delete all the uploaded files
                foreach ($moved_file_meta_info as $file_meta_info) {
                    unlink($file_meta_info['path']);
                }

                throw new Exception($e->getMessage());
            }

        } catch (mysqli_sql_exception $e) { // catch only mysqli exceptions
            // log the error to a file
            error_log('Mysql error: '.$e->getMessage().PHP_EOL, 3, CUSTOM_ERR_DIR.'custom_errors.log');
        
        } catch (Exception $e) { // catch other exception
            // log the error to a file
            error_log('Caught exception: '.$e->getMessage().PHP_EOL, 3, CUSTOM_ERR_DIR.'custom_errors.log');
        }

        break;

    case 'retrieve_client_msg':
    case 'retrieve_agent_msg':
        // enable mysql exception
        mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

        try {
            // connect to database
            $conn = new mysqli($db['host'], $db['username'], $db['password'], $db['dbname']);

            // check connection
            if ($conn->connect_error) {
                throw new Exception('Database connection failed: '.$conn->connect_error);
            }

            $next_time_offset = 0;

            if ($request == "retrieve_client_msg") {
                $msg_reciever = "agent";
                $user_online_table = "c2chat_client_online";

            } else { // retrieve_agent_msg
                $msg_reciever = "client";
                $user_online_table = "c2chat_agent_online";
            }

            // check if the client is still connected or active
            $query = 'SELECT online, pingTime FROM ' . $user_online_table . ' WHERE chatID = ? LIMIT 1';
            $stmt = $conn->prepare($query); // prepare statement
            $stmt->bind_param('s', $_POST['chat_id']);
            $stmt->execute();
            $stmt->store_result(); // needed for num_rows

            if ($stmt->num_rows > 0) {
                $stmt->bind_result($online, $ping_time);
                $stmt->fetch();
                $stmt->close();

                // check if connection has been closed or connect session has expired
                if ($online == 0 || (time() - $ping_time) > (2 * 60)) {
                    // close connection to database
                    $conn->close();

                    // send response back to client
                    echo json_encode([
                        'status' => 'disconnected'
                    ]);

                    die(); // exit script
                }

            } else { // chat session ended
                // close connection to database
                $stmt->close();
                $conn->close();

                // send response back to client
                echo json_encode([
                    'status' => 'disconnected'
                ]);

                die(); // exit script
            }

            // get next retrieve time offset
            $query = 
                'SELECT MAX(time) AS time_offset FROM c2chat_chat_messages 
                 WHERE chatID = ? AND receiver = ?';
            $stmt = $conn->prepare($query); // prepare statement
            $stmt->bind_param('ss', $_POST['chat_id'], $msg_reciever);
            $stmt->execute();
            $stmt->bind_result($next_time_offset);
            $stmt->fetch();
            $stmt->close();

            // set to value 0 if chat messages is empty
            $next_time_offset = empty($next_time_offset) ? 0 : $next_time_offset;

            // retrieve all the sent messages
            $query = 
                'SELECT A.messageID, A.message, A.retrieveFileID, B.userName, B.userPicture, A.time 
                 FROM c2chat_chat_messages AS A LEFT JOIN ' . $user_online_table . ' AS B ON A.chatID = B.chatID 
                 WHERE A.chatID = ? AND A.receiver = ? AND A.time > ? ORDER BY A.time ASC';
            $stmt = $conn->prepare($query); // prepare statement
            $stmt->bind_param('ssi', $_POST['chat_id'], $msg_reciever, $_POST['time_offset']);
            $stmt->execute();
            $result = $stmt->get_result();

            // prepared statement to fetch attached file(s) if any
            $query_2 = 'SELECT * FROM c2chat_uploaded_files WHERE retrieveFileID = ?';
            $stmt_2 = $conn->prepare($query_2); // prepare statement
            $stmt_2->bind_param('s', $retrieve_file_id);

            $messages = [];
 
            // organise the message
            while ($row = $result->fetch_assoc()) {
                // check if there is attachment
                if (empty($row['retrieveFileID'])) {
                    // retrieve the uploaded file(s)
                    $retrieve_file_id = $row['retrieveFileID'];
                    $stmt_2->execute();
                    $result_2 = $stmt_2->get_result();

                    $uploaded_files = [];

                    while ($row_2 = $result_2->fetch_assoc()) {
                        $uploaded_files[] = [
                            'name' => $row_2['name'],
                            'type' => $row_2['type'],
                            'size' => $row_2['size'],
                            'ref_id' => $row_2['referenceID'], 
                            'url' => $row_2['path']
                        ];
                    }

                    $messages[] = [
                        'msg_id' => $row['messageID'],
                        'message' => base64_decode($row['message']),
                        'attachment' => $uploaded_files,
                        'sender_profile' => [
                            'name' => $row['userName'],
                            'picture' => $row['userPicture']
                        ],
                        'time' => $row['time']
                    ];

                } else {
                    $messages[] = [
                        'msg_id' => $row['messageID'],
                        'message' => base64_decode($row['message']),
                        'sender_profile' => [
                            'name' => $row['userName'],
                            'picture' => $row['userPicture']
                        ],
                        'time' => $row['time']
                    ];
                }
            }

            // close connection to database
            $stmt->close();
            $stmt_2->close();
            $conn->close();

            // send result back to client
            echo json_encode([
                'status' => 'ok',
                'messages' => $messages,
                'time_offset' => $next_time_offset
            ]);

        } catch (mysqli_sql_exception $e) { // catch only mysqli exceptions
            // log the error to a file
            error_log('Mysql error: '.$e->getMessage().PHP_EOL, 3, CUSTOM_ERR_DIR.'custom_errors.log');
        
        } catch (Exception $e) { // catch other exception
            // log the error to a file
            error_log('Caught exception: '.$e->getMessage().PHP_EOL, 3, CUSTOM_ERR_DIR.'custom_errors.log');
        }

        break;

    default:
        // you shouldn't be here
}

?>