<?php 

// start session
session_start();

// import all the necessary liberaries
require_once '../../../includes/config.php';
require_once '../../../includes/utils.php'; // include utility liberary

date_default_timezone_set('UTC');

// check if user is authenticated
if (isset($_SESSION['agent_auth']) && $_SESSION['agent_auth'] == true) {
    if (isset($_SESSION['agent_last_auth_time']) && time() < $_SESSION['agent_last_auth_time']) {
        // update the time
        $_SESSION['agent_last_auth_time'] = time() + 1800; // expire in 30 minutes
    
    } else {
        // clear the user's login session
        unset($_SESSION['agent_auth']);

        // redirect user to login page
        header('Location: '. BASE_URL . 'admin/c2chat/login.html');
        exit;
    }

} else {
    // redirect user to login page
    header('Location: '. BASE_URL . 'admin/c2chat/login.html');
    exit;
}

// mysql configuration
$db = $config['db']['mysql'];
        
// enable mysql exception
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

// fetch result for page rendering
$data_for_page_rendering = [
    'agent_info' => null
];

try {
    // connect to database
    $conn = new mysqli($db['host'], $db['username'], $db['password'], $db['dbname']);

    // check connection
    if ($conn->connect_error) {
        throw new mysqli_sql_exception('Database connection failed: '.$conn->connect_error);
    }

    // get agent account information
    $query = 'SELECT * FROM c2chat_agent WHERE id = ? LIMIT 1';
    $stmt = $conn->prepare($query); // prepare statement
    $stmt->bind_param('i', $_SESSION['agent_user_id']);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();

    // set connect data
    $data_for_page_rendering['agent_info'] = [
        'name'  => $row['userName'],
        'picture' => $row['userProfilePicture'],
        'email' => $row['email'],
        'department' => $row['department']
    ];

    // close connection to database
    $stmt->close();
    $conn->close();

} catch (mysqli_sql_exception $e) {
    // log the error to a file
    error_log('Mysql error: '.$e->getMessage().PHP_EOL, 3, CUSTOM_ERR_DIR.'custom_errors.log');
    
} catch (Exception $e) { // catch other exception
    // log the error to a file
    error_log('Caught exception: '.$e->getMessage().PHP_EOL, 3, CUSTOM_ERR_DIR.'custom_errors.log');
}

?>

<!DOCTYPE html>
<html>
    <head>
        <meta charset="UTF-8">
        <title>C2Chat - Dashboard</title>
        <meta name="viewport" content="width=device-width, initial-scale=0.9, maximum-scale=0.9">
        <link type="text/css" rel="stylesheet" href="../../styles/c2chat_dashboard.css">
        <script type="text/javascript" src="../../js/utils.js"></script>
        <script type="text/javascript" src="../../js/howler.js"></script>
        <script type="text/javascript" src="../../js/c2chat/C2Chat.js"></script>
        <script type="text/javascript" src="../../js/c2chat_dashboard.js"></script>
    </head>

    <body>
        <!--page top main menu-->
        <div class="header-main-menu">
            <nav>
                <div class="mobile-menu-btn" toggle="0" onclick="mainSideMenu(this)">
                    <svg class="menu-icon">
                        <use xlink:href="#mobile-menu-icon"></use>
                    </svg>
                </div>
                <div class="site-logo-cont">
                    <svg class="c2chat-icon">
                        <use xlink:href="#c2chat-icon"></use>
                    </svg>
                    <div class="c2chat-name">C2Chat</div>
                </div>
                <ul class="menu-list-cont">
                    <li>
                        <div class="user-drop-menu-btn" onclick="showDropDownMenu('user-drop-menu')">
                            <img src="../../images/icons/user_profile_icon.svg" />
                        </div>
                    </li>
                </ul>
            </nav>
        </div>

        <!--page main side menu-->
        <div id="side-main-menu">
            <div class="menu-link-cont">
                <ul class="menu-link">
                    <li class="active">
                        <a href="#">
                            <div class="link-icon">
                                <svg class="chat-dialog-icon">
                                    <use xlink:href="#chat-dialog-icon"></use>
                                </svg>
                            </div>
                            <div class="link-name">Chat</div>
                        </a>
                    </li>
                </ul>
            </div>
        </div>

        <!--page content section-->
        <div id="content-section">
            <div class="content-section-wrapper">
                <!--list of connected client-->
                <div class="chat-list-cont">
                    <div class="header-menu-bar">
                        <div class="header-title-cont">
                            <h2 class="header-title">Chat list</h2>
                        </div>
                        <div class="close-btn-cont">
                            <button class="close-btn">
                                <svg class="close-icon">
                                    <use xlink:href="#close-win-icon"></use>
                                </svg>
                            </button>
                        </div>
                    </div>
                    <div class="chat-list-scroll-cont">
                        <div class="customer-header-bar">
                            <div class="header-title-cont">
                                <h3 class="header-title">Customer</h3>
                            </div>
                            <div class="collapse-expand-btn-cont">
                                <button class="collapse-expand-btn collapse" toggle="0" onclick="expandAndCollapseChatList(this, 'chat-customer-list-cont')">
                                    <svg class="collapse-expand-icon">
                                        <use xlink:href="#collapse-expand-icon"></use>
                                    </svg>
                                </button>
                            </div>
                        </div>
                        <div id="chat-customer-list-cont">
                            <ul class="chat-list"></ul>
                        </div>
                        <div class="transfered-chat-header-bar">
                            <div class="header-title-cont">
                                <h3 class="header-title">Transfered chat</h3>
                            </div>
                            <div class="collapse-expand-btn-cont">
                                <button class="collapse-expand-btn collapse" toggle="0" onclick="expandAndCollapseChatList(this, 'chat-transfered-list-cont')">
                                    <svg class="collapse-expand-icon">
                                        <use xlink:href="#collapse-expand-icon"></use>
                                    </svg>
                                </button>
                            </div>
                        </div>
                        <div id="chat-transfered-list-cont">
                            <ul class="chat-list"></ul>
                        </div>
                    </div>
                </div>

                <!--chat window-->
                <div class="chat-window-cont">
                    <div class="header-menu-bar">
                        <div class="left-menu-section hide-elem">
                            <div class="profile-picture-indicator-cont">
                                <img class="profile-picture" src="../../images/icons/user_profile_icon.svg" />
                                <div id="chat-win-indicator" class="indicator offline"></div>
                            </div>
                            <div class="profile-name-connect-time">
                                <h4 class="name"></h4>
                                <div id="chat-win-status" class="log-time"></div>
                            </div>
                        </div>
                        <div class="right-menu-section">
                            <button class="user-info-btn">
                                <svg class="user-info-icon">
                                    <use xlink:href="#user-info-icon"></use>
                                </svg>
                            </button>
                            <button class="drop-menu-btn">
                                <svg class="drop-menu-icon">
                                    <use xlink:href="#menu-tray-icon"></use>
                                </svg>
                            </button>
                            <div id="chat-win-drop-menu" class="remove-elem">
                                <ul class="item-list-cont">
                                    <li class="close-menu">
                                        <div class="item-icon">
                                            <svg class="right-arrow-icon">
                                                <use xlink:href="#right-arrow-icon"></use>
                                            </svg>
                                        </div>
                                        <div class="item-name">Transfer chat</div>
                                    </li>
                                    <li class="close-menu">
                                        <div class="item-icon">
                                            <svg class="close-icon">
                                                <use xlink:href="#close-win-icon"></use>
                                            </svg>
                                        </div>
                                        <div class="item-name" onclick="closeChatSession()">Close this chat</div>
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>
                    <div class="chat-msg-cont"></div>
                    <div class="send-msg-cont">
                        <div class="chat-text-area-cont">
                            <div class="text-area" contenteditable="false" spellcheck="false"></div>
                            <div class="text-area-placeholder">Type a message here</div>
                        </div>
                        <div class="send-msg-menu-cont">
                            <div class="left-section">
                                <label for="attach-file-input">
                                    <svg class="papar-clip-icon">
                                        <use xlink:href="#papar-clip-icon"></use>
                                    </svg>
                                </label>
                                <input id="attach-file-input" type="file" name="file" accept="image/png, image/jpeg, image/gif" disabled />
                            </div>
                            <div class="right-section">
                                <button class="send-msg-btn" disabled>
                                    <svg class="send-msg-icon">
                                        <use xlink:href="#send-msg-icon"></use>
                                    </svg>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!--connected user's information-->
                <div class="chat-user-info-cont">
                    <div class="header-menu-bar">
                        <div class="header-title-cont">
                            <h2 class="header-title">Visitor's info</h2>
                        </div>
                        <div class="close-btn-cont">
                            <button class="close-btn">
                                <svg class="close-icon">
                                    <use xlink:href="#close-win-icon"></use>
                                </svg>
                            </button>
                        </div>
                    </div>
                    <div class="user-info-scroll-wrapper">
                        <div class="user-connect-info">
                            <div id="chat-user-connect-info-wrapper" class="hide-elem">
                                <div class="profile-picture-indicator-cont">
                                    <img class="profile-picture" src="../../images/icons/user_profile_icon.svg" />
                                    <div id="user-info-indicator" class="indicator offline"></div>
                                </div>
                                <h4 class="name"></h4>
                                <div class="user-type"></div>
                            </div>
                        </div>
                        <div class="user-personal-info">
                            <div id="chat-user-personal-info-wrapper" class="hide-elem">
                                <div class="header-title-cont">
                                    <h4 class="header-title">Personal Information</h4>
                                </div>
                                <div class="content-cont">
                                    <ul class="info-list">
                                        <li>
                                            <div class="list-icon">
                                                <svg class="mail-envelop-icon">
                                                    <use xlink:href="#mail-envelop-icon"></use>
                                                </svg>
                                            </div>
                                            <div class="list-data"></div>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!--page script-->
        <script>
            function init() {
                // constants and variables
                // code here

                // connect agent to chat server
                window.connectAgentToChatServer(
                    "http://localhost/workspace/thecitadelcapital/crypto-web/public_html/c2chat_server",
                    "http://localhost/workspace/thecitadelcapital/crypto-web/public_html/js/c2chat/",
                    {
                        name: "<?php echo $data_for_page_rendering['agent_info']['name']; ?>",
                        picture: "<?php echo $data_for_page_rendering['agent_info']['picture']; ?>",
                        email: "<?php echo $data_for_page_rendering['agent_info']['email']; ?>",
                        department: "<?php echo $data_for_page_rendering['agent_info']['department']; ?>"
                    }
                );
            }

            //initialise the script
            if (window.attachEvent) {
                window.attachEvent("onload", init);

            } else {
                window.addEventListener("load", init, false);
            }
        </script>

        <!--import web svg library here-->
        <?php include_once '../../../images/icons/web_svg_lib.svg'; ?>
    </body>
</html>