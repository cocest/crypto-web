    <div class="page-left-menu-cont close">
        <div class="site-logo-cont">
            <a href="./dashboard.html">
                <img src="../images/icons/wt_citadel_capital_logo.png" alt="site logo" />
            </a>
        </div>
        <div class="scroll-wrapper">
            <ul class="menu-list-cont">
                <li <?php echo $side_menu_active_links['dashboard'] ? 'class="active-link"' : ''; ?>>
                    <a href="./dashboard.html">
                        <i class="link-icon fas fa-chart-bar"></i><span class="link-name">Dashboard</span>
                    </a>
                </li>
                <li <?php echo $side_menu_active_links['users'] ? 'class="active-link"' : ''; ?>>
                    <a href="./registered_users.html">
                        <i class="link-icon fas fa-users"></i><span class="link-name">Users</span>
                    </a>
                </li>
                <li <?php echo $side_menu_active_links['settings'] ? 'class="active-link"' : ''; ?>>
                    <a href="./settings.html">
                        <i class="link-icon fas fa-cog"></i><span class="link-name">Settings</span>
                    </a>
                </li>
            </ul>
        </div>
    </div>