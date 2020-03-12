<?php 

// start session
session_start();

// import all the necessary liberaries
require_once '../../includes/config.php';
require_once '../../includes/utils.php'; // include utility liberary

date_default_timezone_set('UTC');

// check if user is authenticated
if (isset($_SESSION['admin_auth']) && $_SESSION['admin_auth'] == true) {
    if (isset($_SESSION['admin_last_auth_time']) && time() < $_SESSION['admin_last_auth_time']) {
        // update the time
        $_SESSION['admin_last_auth_time'] = time() + 1800; // expire in 30 minutes
    
    } else {
        // clear the user's login session
        unset($_SESSION['admin_auth']);

        // redirect user to login page
        header('Location: '. BASE_URL . 'admin/login.html');
        exit;
    }

} else {
    // redirect user to login page
    header('Location: '. BASE_URL . 'admin/login.html');
    exit;
}

// set page left menu active menu
// Note: remeber to set this variable before you include "page_left_menu.php"
$side_menu_active_links = [
    'dashboard' => true,
    'users' => false,
    'testimony' => false,
    'settings' => false
];

// assemble all the part of the page
require_once 'admin_header.php';
require_once 'admin_side_menu.php';

?>

    <div class="page-content-cont">
        <?php require_once 'admin_top_main_menu.php' ?>
        <div class="page-content">
            <h1 class="page-title-hd">Dashboard</h1>
            <div class="dashboard-widget-wrapper">
                <div class="dashboard-widget-cont">
                    <div class="users-widget grid-item">
                        <div class="content-cont">
                            <div class="header">
                                <div class="title">Users</div>
                            </div>
                            <div class="reg-users">
                                <div class="data">0</div>
                                <div class="title">Registered Users</div>
                            </div>
                            <div class="un-account">
                                <div class="data">0</div>
                                <div class="title">Unverified Account</div>
                            </div>
                            <div></div>
                        </div>
                    </div>
                    <div class="investment-widget grid-item">
                        <div class="content-cont">
                        <div class="header">
                                <div class="title">Investment</div>
                            </div>
                            <div class="active-investment">
                                <div class="data">0</div>
                                <div class="title">Active Investment</div>
                            </div>
                            <div class="total-investment">
                                <div class="data">0</div>
                                <div class="title">Total Investment</div>
                            </div>
                        </div>
                    </div>
                    <div class="account-widget grid-item">
                        <div class="content-cont">
                        <div class="header">
                                <div class="title">All Account</div>
                            </div>
                            <div class="total-balance">
                                <div class="data">0</div>
                                <div class="title">Total Balance</div>
                            </div>
                            <div class="available-balance">
                                <div class="data">0</div>
                                <div class="title">Available Balance</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script>
        function init() {
            // initialise contants and variables here
            let dash_board_wroker;

            // display new fetch dashboard statistics
            function renderDashboardStat(data) {
                if (data.success) {
                    // render users stat
                    document.querySelector('.users-widget .reg-users .data').innerHTML = data.user.total_users;
                    document.querySelector('.users-widget .un-account .data').innerHTML = data.user.total_unverified_account;

                    // investment stat
                    document.querySelector('.investment-widget .active-investment .data').innerHTML = data.investment.active_investment;
                    document.querySelector('.investment-widget .total-investment .data').innerHTML = data.investment.total_investment;

                    // account stat
                    document.querySelector('.account-widget .total-balance .data').innerHTML = data.account.total_balance;
                    document.querySelector('.account-widget .available-balance .data').innerHTML = data.account.available_balance;
                }
            }

            // update dashboard statistics every interval
            function initDashboardStatUpdate() {
                // checks if the worker does not exists
                if (typeof dash_board_wroker == 'undefined') {
                    dash_board_wroker = new Worker("../js/dashboardStatUpdateWorker.js");

                    // listen to when data is sent
                    dash_board_wroker.addEventListener("message", function (e) {
                        // render result to view
                        renderDashboardStat(e.data);
                    }, false);
                }
            }

            // call after page load
            initDashboardStatUpdate();
        }

        //initialise the script
        if (window.attachEvent) {
            window.attachEvent("onload", init);

        } else {
            window.addEventListener("load", init, false);
        }
    </script>
<?php

// page footer
require_once 'admin_footer.php';

?>