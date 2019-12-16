<?php 

// import all the necessary liberaries
require_once '../../../includes/config.php';

// mysql configuration
$db = $config['db']['mysql'];
        
// enable mysql exception
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

// fetch result for page rendering
$data_for_header_rendering = null;

try {
    // connect to database
    $conn = new mysqli($db['host'], $db['username'], $db['password'], $db['dbname']);

    //check connection
    if ($conn->connect_error) {
        throw new mysqli_sql_exception('Database connection failed: '.$conn->connect_error);
    }

    // fetch user name
    // validate the token
    $query = 'SELECT firstName, smallProfilePictureURL FROM users WHERE id = ? LIMIT 1';
    $stmt = $conn->prepare($query); // prepare statement
    $stmt->bind_param('i', $_SESSION['user_id']);
    $stmt->execute();
    $stmt->bind_result($user_firstname, $profile_img_url);
    $stmt->fetch();

    $data_for_header_rendering = [
        'firstname' => $user_firstname,
        'profile_img' => $profile_img_url
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

<!DOCTYPE HTML>
<html lang="en-US">

<head>
    <meta charset="UTF-8">
    <title>CryptoWeb - My Account</title>
    <link rel="icon" type="image/png" href="favicon1.png" sizes="16x16">
    <link rel="icon" type="image/png" href="favicon2.png" sizes="32x32">
    <link rel="icon" type="image/png" href="favicon3.png" sizes="120x120">
    <meta name="description" content="CryptoWeb registeration page">
    <meta name="keywords" content="sign in, sign up, register, register to CryptoWeb, create account with CryptoWeb">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">
    <link type="text/css" href="../../fonts/css/all.min.css" rel="stylesheet">
    <link type="text/css" rel="stylesheet" href="../../styles/UdaraX.css">
    <link type="text/css" rel="stylesheet" href="../../styles/user_account.css">
    <script type="text/javascript" src="../../js/utils.js"></script>
    <script type="text/javascript" src="../../js/user_account.js"></script>
</head>

<body>
    <div class="page-top-menu-cont">
        <nav>
            <div class="site-logo-cont">
                <a href="./my_investment.html">
                    <img src="site_logo.png" alt="site logo" />
                </a>
            </div>
            <ul class="menu-link-cont">
                <li class="help-cont" onclick="showHelpDropDownMenu()">
                    <span class="fas fa-question-circle"></span>
                    <span class="txt">Help</span>
                </li>
                <li class="notification-cont" onclick="openWin('notification-win-cont')">
                    <span class="fas fa-bell"></span>
                    <div id="unread-msg-counter" class="count remove-elem"><div>
                </li>
                <li class="user-cont" onclick="showUserDropDownMenu()">
                    <img id="header-profile-image" src="<?php echo empty($data_for_header_rendering['profile_img']) ? '../../images/icons/profile_pic.png' : '../../uploads/users/profile/'.$data_for_header_rendering['profile_img']; ?>" />
                    <div class="user">
                        <div class="name">
                            <?php echo $data_for_header_rendering['firstname']; ?>
                        </div>
                        <span class="fas fa-caret-down"></span>
                    </div>
                </li>
            </ul>
        </nav>
    </div>

    <!--user menu-->
    <div id="user-drop-down-menu-cont" class="remove-elem">
        <div class="pointer"></div>
        <ul class="menu-list-cont">
            <li>
                <a href="profile.html">
                    <div class="profile-icon-cont">
                        <img src="../../images/icons/icons_sprite_1.png" />
                    </div>
                    <span class="link-name">Profile</span>
                </a>
            </li>
            <li>
                <a href="settings.html">
                    <div class="settings-icon-cont">
                        <img src="../../images/icons/icons_sprite_1.png" />
                    </div>
                    <span class="link-name">Settings</span>
                </a>
            </li>
            <li>
                <a href="<?php echo BASE_URL.'logout_user'; ?>">
                    <div class="signout-icon-cont">
                        <img src="../../images/icons/icons_sprite_1.png" />
                    </div>
                    <span class="link-name">Sign out</span>
                </a>
            </li>
        </ul>
    </div>

    <!--help menu-->
    <div id="help-drop-down-menu-cont" class="remove-elem">
        <div class="pointer"></div>
        <ul class="menu-list-cont">
            <li>
                <div class="writeus-icon-cont">
                    <img src="../../images/icons/icons_sprite_1.png" />
                </div>
                <span class="link-name">Write to us</span>
            </li>
            <li>
                <a href="#">
                    <div class="faq-icon-cont">
                        <img src="../../images/icons/icons_sprite_1.png" />
                    </div>
                    <span class="link-name">FAQ</span>
                </a>
            </li>
        </ul>
    </div>

    <!--notification window-->
    <div id="notification-win-cont" class="remove-elem">
        <div class="title-bar-cont">
            <div class="title">Notifications</div>
            <div class="close-btn ux-f-rd-corner" onclick="closeActiveWin('notification-win-cont')">
                <img src="../../images/icons/notification_icons.png" />
            </div>
        </div>
        <div class="scroll-list-cont">
            <div id="notification-list-cont">
                <div id="load-prev-notification" class="remove-elem" onclick="loadPreviousNotfication()">Load more</div>
                <div id="loading-notification-anim-cont">
                    <div class="vt-bars-anim-cont">
                        <div class="vt-bar-cont">
                            <div class="vt-bar-1"></div>
                        </div>
                        <div class="vt-bar-cont">
                            <div class="vt-bar-2"></div>
                        </div>
                        <div class="vt-bar-cont">
                            <div class="vt-bar-3"></div>
                        </div>
                    </div>
                </div>
                <div id="notification-status-msg" class="remove-elem">No notification</div>
            </div>
        </div>
    </div>

    <!--message window-->
    <div id="msg-win-cont" class="remove-elem">
        <div class="title-bar-cont">
            <div class="title">Message</div>
            <div class="close-btn ux-f-rd-corner" onclick="closeActiveWin('msg-win-cont')">
                <img src="../../images/icons/notification_icons.png" />
            </div>
        </div>
        <div class="body-cont"></div>
    </div>

    <!--listen to mouse click event on particular section of the page-->
    <div onclick="sectionClickEvent(event)">
    