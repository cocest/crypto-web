<div class="top-main-menu-cont">
    <div class="left-wrapper">
        <div class="show-side-menu-btn" onclick="showSideMenu()">
            <img src="../images/icons/drop_menu_icon3.png" />
        </div>
    </div>
    <div class="right-wrapper">
        <div class="login-user-cont">
            <img src="../images/icons/profile_pic.png" atl="user's image" />
        </div>
        <div class="notification-cont">
            <i class="fas fa-bell"></i>
            <div id="unread-msg-counter" class="remove-elem">1</div>
        </div>
    </div>
</div>

<!--Logout admin menu-->
<div id="admin-drop-down-menu-cont" class="remove-elem">
    <ul class="menu-list-cont">
        <li>
            <a href="./settings.html">
                <div class="settings-icon-cont">
                    <i class="fas fa-cog"></i>
                </div>
                <span class="link-name">Settings</span>
            </a>
        </li>
        <li>
            <a href="<?php echo BASE_URL.'logout_admin'; ?>">
                <div class="signout-icon-cont">
                    <i class="fas fa-long-arrow-alt-right"></i>
                </div>
                <span class="link-name">Sign out</span>
            </a>
        </li>
    </ul>
</div>