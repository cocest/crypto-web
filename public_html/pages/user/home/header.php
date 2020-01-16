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
    <title>Thecitadelcapital - My Account</title>
    <link rel="icon" type="image/png" href="../../images/icons/favicon1.png" sizes="16x16">
    <link rel="icon" type="image/png" href="../../images/icons/favicon2.png" sizes="32x32">
    <link rel="icon" type="image/png" href="../../images/icons/favicon3.png" sizes="120x120">
    <meta name="description" content="Thecitadelcapital user's account">
    <meta name="keywords" content="sign in, sign up, register, register to CryptoWeb, create account with CryptoWeb">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">
    <link type="text/css" href="../../fonts/css/all.min.css" rel="stylesheet">
    <link type="text/css" rel="stylesheet" href="../../styles/UdaraX.css">
    <link type="text/css" rel="stylesheet" href="../../styles/user_account.css">
    <script type="text/javascript" src="../../js/udaraeditor.js"></script>
    <script type="text/javascript" src="../../js/utils.js"></script>
    <script type="text/javascript" src="../../js/user_account.js"></script>
</head>

<body>
    <div class="page-top-menu-cont">
        <nav>
            <div class="show-side-menu-icon close" toggle="0" onclick="showPageSideMenu(this)">
                <img src="../../images/icons/drop_menu_icon3.png" />
            </div>
            <div class="site-logo-cont">
                <a href="./my_investment.html">
                    <img src="../../images/icons/citadel_capital_logo.png" alt="thecitadelcapital" />
                </a>
            </div>
            <ul class="menu-link-cont">
                <li class="help-cont" onclick="showHelpDropDownMenu()">
                    <span class="fas fa-question-circle"></span>
                    <span class="txt">Help</span>
                </li>
                <li class="notification-cont" onclick="openWin('notification-win-cont')">
                    <span class="fas fa-bell"></span>
                    <div id="unread-msg-counter" class="count remove-elem">
                        <div>
                </li>
                <li class="user-cont" onclick="showUserDropDownMenu()">
                    <img id="header-profile-image"
                        src="<?php echo empty($data_for_header_rendering['profile_img']) ? '../../images/icons/profile_pic.png' : '../../uploads/users/profile/'.$data_for_header_rendering['profile_img']; ?>" />
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
            <li onclick="openWin('compose-mail-editor')">
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

    <!--compose mail editor window-->
    <div id="compose-mail-editor" class="remove-elem">
        <div class="compose-mail-editor-wrapper">
            <div class="title-bar-cont">
                <div class="title">New Message</div>
                <div class="close-btn ux-f-rd-corner" onclick="closeActiveWin('compose-mail-editor')">
                    <img src="../../images/icons/notification_icons.png" />
                </div>
            </div>
            <div class="mail-header-cont">
                <input id="send-us-email-header" type="text" placeholder="Subject" />
            </div>
            <div class="edit-body-cont">
                <div id="editor" class="edit-body"></div>
            </div>
            <div class="editor-menu-btn-cont">
                <div class="main-menu-cont hide-tray" toggle="0">
                    <div class="menu-btn">
                        <div class="editor-font-name-select-input ux-custom-select-input" tag="font-name" title="Select font">
                            <button class="select-option">
                                <div class="selected-item">
                                    <div class="name-cont">
                                        <div class="name">Arial</div>
                                    </div>
                                </div>
                                <div class="icon"><i class="fas fa-caret-down"></i></div>
                            </button>
                            <div class="option-list-cont remove-elem" kopen>
                                <ul class="option-list">
                                    <li class="selected">
                                        <div class="option-value" style="font-family: Arial;">Arial</div>
                                    </li>
                                    <li>
                                        <div class="option-value" style="font-family: 'Arial Black';">Arial Black</div>
                                    </li>
                                    <li>
                                        <div class="option-value" style="font-family: Avenir;">Avenir</div>
                                    </li>
                                    <li>
                                        <div class="option-value" style="font-family: Calibri;">Calibri</div>
                                    </li>
                                    <li>
                                        <div class="option-value" style="font-family: 'Comic Sans MS';">Comic Sans MS
                                        </div>
                                    </li>
                                    <li>
                                        <div class="option-value" style="font-family: 'Courier New';">Courier New</div>
                                    </li>
                                    <li>
                                        <div class="option-value" style="font-family: Geneva;">Geneva</div>
                                    </li>
                                    <li>
                                        <div class="option-value" style="font-family: Georgia;">Georgia</div>
                                    </li>
                                    <li>
                                        <div class="option-value" style="font-family: Impact;">Impact</div>
                                    </li>
                                    <li>
                                        <div class="option-value" style="font-family: 'Sans Serif';">Sans Serif</div>
                                    </li>
                                    <li>
                                        <div class="option-value" style="font-family: 'Segoe UI';">Segoe UI</div>
                                    </li>
                                    <li>
                                        <div class="option-value" style="font-family: 'Times New Roman';">Times New Roman</div>
                                    </li>
                                    <li>
                                        <div class="option-value" style="font-family: Verdana;">Verdana</div>
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>
                    <div class="menu-btn">
                        <div class="editor-font-size-select-input ux-custom-select-input" tag="font-size" title="Select font size">
                            <button class="select-option">
                                <div class="selected-item">
                                    <div class="name-cont">
                                        <div class="name">4</div>
                                    </div>
                                </div>
                                <div class="icon"><i class="fas fa-caret-down"></i></div>
                            </button>
                            <div class="option-list-cont remove-elem" kopen>
                                <ul class="option-list">
                                    <li>
                                        <div class="option-value">1</div>
                                    </li>
                                    <li>
                                        <div class="option-value">2</div>
                                    </li>
                                    <li>
                                        <div class="option-value">3</div>
                                    </li>
                                    <li class="selected">
                                        <div class="option-value">4</div>
                                    </li>
                                    <li>
                                        <div class="option-value">5</div>
                                    </li>
                                    <li>
                                        <div class="option-value">6</div>
                                    </li>
                                    <li>
                                        <div class="option-value">7</div>
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>
                    <div class="menu-cont-1 menu-group">
                        <div class="menu-btn">
                            <div id="mail-editor-input-bold" class="mail-editor-input mail-editor-icon" onmousedown="editorFormatCommand(event, 'bold')" title="Bold">
                                <i class="fa fa-bold"></i>
                            </div>
                        </div>
                        <div class="menu-btn">
                            <div id="mail-editor-input-italic" class="mail-editor-input mail-editor-icon" onmousedown="editorFormatCommand(event, 'italic')" title="Italic">
                                <i class="fa fa-italic"></i>
                            </div>
                        </div>
                        <div class="menu-btn">
                            <div id="mail-editor-input-underline" class="mail-editor-input mail-editor-icon" onmousedown="editorFormatCommand(event, 'underline')" title="Underline">
                                <i class="fa fa-underline"></i>
                            </div>
                        </div>
                        <div class="menu-btn">
                            <div id="mail-editor-input-strikethrough" class="mail-editor-input mail-editor-icon" onmousedown="editorFormatCommand(event, 'strikethrough')" title="Strikethrough">
                                <i class="fa fa-strikethrough"></i>
                            </div>
                        </div>
                        <div class="menu-btn">
                            <div class="editor-font-color-selector" title="Select font color">
                                <div class="label-cont" style="color: #373435;">
                                    <i class="fas fa-font"></i>
                                </div>
                                <div class="selector-cont remove-elem" kopen>
                                    <div class="color-grid-cont ux-layout-grid columns-6">
                                        <div class="grid-item" color="#373435" style="background-color: #373435;"></div>
                                        <div class="grid-item" color="#4b4b4d" style="background-color: #4b4b4d;"></div>
                                        <div class="grid-item" color="#606062" style="background-color: #606062;"></div>
                                        <div class="grid-item" color="#727376" style="background-color: #727376;"></div>
                                        <div class="grid-item" color="#848688" style="background-color: #848688;"></div>
                                        <div class="grid-item" color="#96989a" style="background-color: #96989a;"></div>
                                        <div class="grid-item" color="#a9abae" style="background-color: #a9abae;"></div>
                                        <div class="grid-item" color="#bdbfc1" style="background-color: #bdbfc1;"></div>
                                        <div class="grid-item" color="#d2d3d5" style="background-color: #d2d3d5;"></div>
                                        <div class="grid-item" color="#e6e7e8" style="background-color: #e6e7e8;"></div>
                                        <div class="grid-item" color="#fefefe" style="background-color: #fefefe;"></div>
                                        <div class="grid-item" color="#3e4095" style="background-color: #3e4095;"></div>
                                        <div class="grid-item" color="#00afef" style="background-color: #00afef;"></div>
                                        <div class="grid-item" color="#00a859" style="background-color: #00a859;"></div>
                                        <div class="grid-item" color="#fff212" style="background-color: #fff212;"></div>
                                        <div class="grid-item" color="#ed3237" style="background-color: #ed3237;"></div>
                                        <div class="grid-item" color="#ec268f" style="background-color: #ec268f;"></div>
                                        <div class="grid-item" color="#a8518a" style="background-color: #a8518a;"></div>
                                        <div class="grid-item" color="#f58634" style="background-color: #f58634;"></div>
                                        <div class="grid-item" color="#f7adaf" style="background-color: #f7adaf;"></div>
                                        <div class="grid-item" color="#84716b" style="background-color: #84716b;"></div>
                                        <div class="grid-item" color="#718fc8" style="background-color: #718fc8;"></div>
                                        <div class="grid-item" color="#a8cf45" style="background-color: #a8cf45;"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="menu-cont-2 menu-group">
                            <div class="menu-btn">
                                <div id="mail-editor-input-left" class="mail-editor-input mail-editor-icon" onmousedown="editorFormatCommand(event, 'left')" title="Align left">
                                    <i class="fa fa-align-left"></i>
                                </div>
                            </div>
                            <div class="menu-btn">
                                <div id="mail-editor-input-center" class="mail-editor-input mail-editor-icon" onmousedown="editorFormatCommand(event, 'center')" title="Align center">
                                    <i class="fa fa-align-center"></i>
                                </div>
                            </div>
                            <div class="menu-btn">
                                <div id="mail-editor-input-right" class="mail-editor-input mail-editor-icon" onmousedown="editorFormatCommand(event, 'right')" title="Align right">
                                    <i class="fa fa-align-right"></i>
                                </div>
                            </div>
                            <div class="menu-btn">
                                <div id="mail-editor-input-justify" class="mail-editor-input mail-editor-icon" onmousedown="editorFormatCommand(event, 'justify')" title="Align justify">
                                    <i class="fa fa-align-justify"></i>
                                </div>
                            </div>
                            <div class="menu-cont-3 menu-group">
                                <div class="menu-btn">
                                    <div id="mail-editor-input-orderedlist" class="mail-editor-input mail-editor-icon" onmousedown="editorFormatCommand(event, 'orderedlist')" title="Ordered list">
                                        <i class="fa fa-list-ol"></i>
                                    </div>
                                </div>
                                <div class="menu-btn">
                                    <div id="mail-editor-input-unorderedlist" class="mail-editor-input mail-editor-icon" onmousedown="editorFormatCommand(event, 'unorderedlist')" title="Unordered list">
                                        <i class="fa fa-list-ul"></i>
                                    </div>
                                </div>
                                <div class="menu-btn">
                                    <div id="mail-editor-input-indent" class="mail-editor-input mail-editor-icon" onmousedown="editorFormatCommand(event, 'indent')" title="Indent">
                                        <i class="fa fa-indent"></i>
                                    </div>
                                </div>
                                <div class="menu-btn">
                                    <div class="mail-editor-icon" onmousedown="editorFormatCommand(event, 'outdent')" title="Outdent">
                                        <i class="fa fa-outdent"></i>
                                    </div>
                                </div>
                                <div class="menu-btn">
                                    <div class="mail-editor-icon" onmousedown="editorFormatCommand(event, 'removeformat')" title="Clear applied format">
                                        <i class="fas fa-remove-format"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="show-hidden-menu-btn" onmousedown="showMailEditorMenuTray(event)">
                        <i class="fas fa-ellipsis-h"></i>
                    </div>
                </div>
            </div>
            <div class="edit-footer-cont">
                <button class="send-msg-btn" onclick="sendComposeEmail()">Send</button>
            </div>
        </div>
    </div>

    <!--notification window-->
    <div id="notification-win-cont" class="remove-elem">
        <div class="notification-win-wrapper">
            <div class="title-bar-cont">
                <div class="title">Notifications</div>
                <div class="close-btn ux-f-rd-corner" onclick="closeActiveWin('notification-win-cont')">
                    <img src="../../images/icons/notification_icons.png" />
                </div>
            </div>
            <div class="scroll-list-cont">
                <div id="notification-list-cont">
                    <div id="load-prev-notification" class="remove-elem" onclick="loadPreviousNotfication()">Load more
                    </div>
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
    </div>

    <!--message window-->
    <div id="msg-win-cont" class="remove-elem">
        <div class="msg-win-wrapper">
            <div class="title-bar-cont">
                <div class="title">Message</div>
                <div class="close-btn ux-f-rd-corner" onclick="closeActiveWin('msg-win-cont')">
                    <img src="../../images/icons/notification_icons.png" />
                </div>
            </div>
            <div class="body-cont"></div>
        </div>
    </div>

    <!--listen to mouse click event on particular section of the page-->
    <div onclick="sectionClickEvent(event)">