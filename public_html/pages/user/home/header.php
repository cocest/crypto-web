<?php 

// import all the necessary liberaries
require_once '../../../includes/config.php';
require_once '../../../includes/utils.php'; // include utility liberary

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
                    <img src="#" alt="site logo" />
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
                    <img src="../../images/icons/profile_pic.png" />
                    <div class="user">
                        <div class="name">Cocest</div>
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
                <div class="signout-icon-cont">
                    <img src="../../images/icons/icons_sprite_1.png" />
                </div>
                <span class="link-name">Sign out</span>
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

    <!--listen to mouse click event on particular section of the page-->
    <div onclick="sectionClickEvent(event)">
    