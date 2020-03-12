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
    'dashboard' => false,
    'users' => true,
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
            <h1 class="page-title-hd">Users</h1>
            <div class="tab-menu-cont">
                <ul>
                    <li class="tab-menu active">
                        <a href="./registered_users.html">Registered Users</a>
                    </li>
                    <li class="tab-menu">
                        <a href="./unverified_account.html">Unverified Account</a>
                    </li>
                </ul>
            </div>
            <div id="section-cont-1">
                <div class="reg-user-menu-cont">
                    <div class="select-input-cont">
                        <button class="custom-select-input">
                            <span class="value">User Name</span><i class="icon fas fa-caret-down"></i>
                        </button>
                        <div class="custom-select-input-option-cont remove-elem">
                            <ul>
                                <li class="input-option" selected="true">
                                    <i class="icon fas fa-check"></i><span class="value">User Name</span>
                                </li>
                                <li class="input-option">
                                    <i class="icon fas fa-check"></i><span class="value">Referral ID</span>
                                </li>
                            </ul>
                        </div>
                    </div>
                    <div class="search-input-cont">
                        <input id="search-reg-user-input" class="search-input" type="text" placeholder="Search users" spellcheck="false">
                        <div class="search-input-icon">
                            <i class="fas fa-search"></i>
                        </div>
                    </div>
                </div>
                <div class="section-wrapper">
                    <div class="table-wrapper-cont">
                        <div id="registered-user-list" class="user-list-cont remove-elem"></div>
                    </div>
                    <div class="reg-user-navigator-cont remove-elem">
                        <div class="reg-user-navigator">
                            <span id="reg-user-navi-indicator"></span>
                            <div class="nav-button-cont">
                                <button class="prev-btn" title="Previous" onclick="navigateRegisteredUsers('prev')"><i class="fas fa-caret-left"></i></button>
                                <button class="next-btn" title="Next" onclick="navigateRegisteredUsers('next')"><i class="fas fa-caret-right"></i></button>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="loading-anim-cont">
                    <div class="rot-bar-cont">
                        <span class="rot-bar rot-quart"></span>
                    </div>
                </div>
            </div>
            <div id="section-cont-2" class="remove-elem">
                <div class="reg-user-top-menu-cont">
                    <ul>
                        <li id="goto-current-section" class="back-button" title="Go back">
                            <i class="fas fa-arrow-left"></i>
                        </li>
                        <li class="tab-button active-tab" onclick="switchTabSection(this, 'tab-section-cont-1')">Profile</li>
                        <li class="tab-button" onclick="switchTabSection(this, 'tab-section-cont-2')">Investment</li>
                    </ul>
                </div>
                <div id="tab-section-cont-1">
                    <div class="tab-content-cont">
                        <div class="profile-pic-cont">
                            <img id="profile-user-pic" class="user-pic" src="#">
                        </div>
                        <div class="info-group-cont">
                            <h2 class="info-header">Account Information</h2>
                            <div class="info-row">
                                <div class="info-hd">Username</div>
                                <div id="profile-username" class="info-dt"></div>
                            </div>
                            <div class="info-row">
                                <div class="info-hd">Referral ID</div>
                                <div id="profile-refid" class="info-dt"></div>
                            </div>
                            <div class="info-row">
                                <div class="info-hd">Reg Date</div>
                                <div id="profile-regdate" class="info-dt"></div>
                            </div>
                        </div>
                        <div class="info-group-cont">
                            <h2 class="info-header">Personal Information</h2>
                            <div class="info-row">
                                <div class="info-hd">Name</div>
                                <div id="profile-name" class="info-dt"></div>
                            </div>
                            <div class="info-row">
                                <div class="info-hd">Birthdate</div>
                                <div id="profile-birthdate" class="info-dt"></div>
                            </div>
                            <div class="info-row">
                                <div class="info-hd">Country</div>
                                <div id="profile-country" class="info-dt"></div>
                            </div>
                        </div>
                        <div class="info-group-cont">
                            <h2 class="info-header">Contact Information</h2>
                            <div class="info-row">
                                <div class="info-hd">Email</div>
                                <div id="profile-email" class="info-dt"></div>
                            </div>
                            <div class="info-row">
                                <div class="info-hd">Phone</div>
                                <div id="profile-phone" class="info-dt"></div>
                            </div>
                        </div>
                    </div>
                    <div class="loading-anim-cont">
                        <div class="rot-bar-cont">
                            <span class="rot-bar rot-quart"></span>
                        </div>
                    </div>
                </div>
                <div id="tab-section-cont-2" class="remove-elem">
                    <div class="tab-content-cont">
                        <div class="investment-widget-cont">
                            <div id="user-current-investment" class="investment-cont grid-item remove-elem">
                                <div class="investment">
                                    <div class="header">
                                        <div class="title">Current Investment</div>
                                        <div id="invested-date" class="date"></div>
                                    </div>
                                    <div id="inv-package-name" class="package-name"></div>
                                    <div class="package-duration">
                                        <div id="investment-duration" class="data"></div>
                                        <div class="title">Duration</div>
                                    </div>
                                    <div class="package-roi">
                                        <div class="data">
                                            <div id="investment-roi" class="figure"></div>
                                            <img class="icon" src="../images/icons/chart-arrow-up.png" />
                                        </div>
                                        <div class="title">ROI</div>
                                    </div>
                                    <div class="package-amt-invested">
                                        <div id="amount-invested" class="data"></div>
                                        <div class="title">Amount Invested</div>
                                    </div>
                                </div>
                            </div>
                            <div class="revenue-cont grid-item">
                                <div class="revenue">
                                    <div class="header">
                                        <div class="title">Revenue</div>
                                    </div>
                                    <div class="revenue-total-balance">
                                        <div id="inv-total-balance" class="data"></div>
                                        <div class="title">Total Balance</div>
                                    </div>
                                    <div class="revenue-available-balance">
                                        <div id="inv-available-balance" class="data"></div>
                                        <div class="title">Available Balance</div>
                                    </div>
                                </div>
                            </div>
                            <div class="overview-cont grid-item">
                                <div class="overview">
                                    <div class="header">
                                        <div class="title">Overview</div>
                                    </div>
                                    <div class="overview-total-investment">
                                        <div id="inv-total-investment" class="data"></div>
                                        <div class="title">Total Investment</div>
                                    </div>
                                    <div class="overview-total-revenue">
                                        <div id="inv-total-revenue" class="data"></div>
                                        <div class="title">Total Revenue</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="loading-anim-cont">
                        <div class="rot-bar-cont">
                            <span class="rot-bar rot-quart"></span>
                        </div>
                    </div>
                </div>
                <div id="tab-section-cont-3" class="remove-elem">
                    <div class="loading-anim-cont">
                        <div class="rot-bar-cont">
                            <span class="rot-bar rot-quart"></span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script>
        function init() {
            // define and initialise variables
            const SEARCH_USERNAME = 10;
            const SEARCH_REFERRAL_ID = 11;
            let target_user_id;
            let prev_tab_section = null;
            let page_count = 1; // after page load it will be set to right value
            let max_records_per_page = 10; // set this to maximum items that can be display at once
            let curr_records_page = 1;
            let start_offset = 0;
            let field_to_search_reg_user = SEARCH_USERNAME;
            let search_reg_user_value = "";
            let search_reg_user_again = false;
            let searching_reg_user = false;

            // open a section for user's account information
            window.openUserAccountDetails = function (user_id) {
                // set user identification
                target_user_id = user_id;

                // hide section 1 and show section 2
                document.getElementById("section-cont-1").setAttribute("class", "remove-elem");
                document.getElementById("section-cont-2").removeAttribute("class");

                // show first tab
                switchTabSection(document.querySelector('.tab-button'), 'tab-section-cont-1');
            };

            // switch to tab section
            window.switchTabSection = function (tab_btn, tab_id) {
                // unmark tab button
                prev_active_tab = document.querySelector('.tab-button.active-tab');
                prev_active_tab.setAttribute("class", "tab-button");

                // mark the current tab button
                tab_btn.setAttribute("class", "tab-button active-tab");

                // close previous tab section
                if (prev_tab_section != null) {
                    prev_tab_section.setAttribute("class", "remove-elem");
                }

                prev_tab_section = document.getElementById(tab_id);

                // show current tab section
                prev_tab_section.removeAttribute("class");

                // load the tab section content or data
                if (tab_id == "tab-section-cont-1") { // user's profile
                    // show loading animation
                    let loading_anim = document.querySelector('#tab-section-cont-1 .loading-anim-cont');
                    loading_anim.setAttribute("class", "loading-anim-cont");

                    // hide tab content container
                    let tab_content_cont = document.querySelector('#tab-section-cont-1 .tab-content-cont');
                    tab_content_cont.setAttribute("class", "tab-content-cont remove-elem");

                    loadUserAccountInfo(
                        "profile",
                        function (response) {
                            if (response.success) {
                                if (response.user != null) {
                                    // insert values
                                    document.getElementById("profile-user-pic").setAttribute("src", response.user.profile_url);
                                    document.getElementById("profile-username").innerHTML = response.user.username;
                                    document.getElementById("profile-refid").innerHTML = response.user.referral_id;
                                    document.getElementById("profile-regdate").innerHTML = response.user.reg_date;
                                    document.getElementById("profile-name").innerHTML = response.user.name;
                                    document.getElementById("profile-birthdate").innerHTML = response.user.birthdate;
                                    document.getElementById("profile-country").innerHTML = response.user.country;
                                    document.getElementById("profile-email").innerHTML = response.user.email;
                                    document.getElementById("profile-phone").innerHTML = response.user.phone;
                                }

                            } else {
                                // notify the user to reload the page again
                            }

                            // hide loading animation and show tab content container
                            loading_anim.setAttribute("class", "loading-anim-cont remove-elem");
                            tab_content_cont.setAttribute("class", "tab-content-cont");
                        }
                    );

                } else if (tab_id == "tab-section-cont-2") { // user's investment
                    // show loading animation
                    let loading_anim = document.querySelector('#tab-section-cont-2 .loading-anim-cont');
                    loading_anim.setAttribute("class", "loading-anim-cont");

                    // hide tab content container
                    let tab_content_cont = document.querySelector('#tab-section-cont-2 .tab-content-cont');
                    tab_content_cont.setAttribute("class", "tab-content-cont remove-elem");

                    loadUserAccountInfo("investment", function (response) {
                        if (response.success) {
                            if (response.current_investment != null) {
                                document.getElementById("invested-date").innerHTML = response.current_investment.invested_date;
                                document.getElementById("inv-package-name").innerHTML = response.current_investment.package;
                                document.getElementById("investment-duration").innerHTML = response.current_investment.duration;
                                document.getElementById("investment-roi").innerHTML = response.current_investment.roi;
                                document.getElementById("amount-invested").innerHTML = response.current_investment.amount_invested;

                                // display the widget
                                document.getElementById("user-current-investment").setAttribute("class", "investment-cont grid-item");
                            }

                            document.getElementById("inv-total-balance").innerHTML = response.revenue.total_balance;
                            document.getElementById("inv-available-balance").innerHTML = response.revenue.available_balance;
                            document.getElementById("inv-total-investment").innerHTML = response.overview.total_investment;
                            document.getElementById("inv-total-revenue").innerHTML = response.overview.total_revenue;

                        } else {
                            // notify the user to reload the page again
                        }

                        // hide loading animation and show tab content container
                        loading_anim.setAttribute("class", "loading-anim-cont remove-elem");
                        tab_content_cont.setAttribute("class", "tab-content-cont");
                    });

                } else { // user's account details
                    //
                }
            };

            // search for a registered user
            function searchRegisteredUser() {
                // check if we should conduct another search
                if (searching_reg_user) {
                    search_reg_user_again = true;
                    return;
                }

                // notify others that search is in progress
                searching_reg_user = true; 

                // get search value
                search_reg_user_value = document.getElementById("search-reg-user-input").value.trim();

                // show loading animation and hide registered user list
                document.querySelector('#section-cont-1 .loading-anim-cont').setAttribute("class", "loading-anim-cont");
                document.getElementById("registered-user-list").setAttribute("class", "user-list-cont remove-elem");
                document.querySelector('.reg-user-navigator-cont').setAttribute("class", "reg-user-navigator-cont remove-elem");

                fetchRegisteredUsers(start_offset, max_records_per_page, function (response) {
                    renderRegisteredUsers(response.users);
                    attachEventToCustomDropDownInputOnLoad();

                    let nav_indicator = document.getElementById("reg-user-navi-indicator");
                    page_count = window.pageCountForListItem(max_records_per_page, response.metadata.total);
                    nav_indicator.innerHTML = 
                        (start_offset + 1) + " - " + 
                        ((curr_records_page * max_records_per_page) > response.metadata.total ? 
                        response.metadata.total : (curr_records_page * max_records_per_page)) + " of " + 
                        response.metadata.total;

                    // hide load animation and show registered users
                    document.querySelector('#section-cont-1 .loading-anim-cont').setAttribute("class", "loading-anim-cont remove-elem");
                    document.getElementById("registered-user-list").setAttribute("class", "user-list-cont");
                    document.querySelector('.reg-user-navigator-cont').setAttribute("class", "reg-user-navigator-cont")

                    // check to search again
                    if (search_reg_user_again) {
                        searching_reg_user = false;
                        search_reg_user_again = false;
                        searchRegisteredUser();

                    } else {
                        searching_reg_user = false;
                    }
                });
            }

            // listen for keyup event on search input
            document.getElementById("search-reg-user-input").onkeyup = function (e) {
                searchRegisteredUser();
            };

            // navigate through registered users
            window.navigateRegisteredUsers = function (direction) {
                if (direction == 'next') {
                    if ((curr_records_page + 1) <= page_count) {
                        start_offset = curr_records_page * max_records_per_page;
                    } else {
                        return;
                    }

                } else { // prev
                    if ((curr_records_page - 1) >= 1) {
                        start_offset = ((curr_records_page - 1) * max_records_per_page) - max_records_per_page;
                    } else {
                        return;
                    }
                }

                // fetch users
                fetchRegisteredUsers(start_offset, max_records_per_page, function (response) {
                    renderRegisteredUsers(response.users);
                    let nav_indicator = document.getElementById("reg-user-navi-indicator");

                    if (direction == 'next') {
                        curr_records_page += 1;
                    } else {
                        curr_records_page -= 1;
                    }

                    page_count = window.pageCountForListItem(max_records_per_page, response.metadata.total);
                    nav_indicator.innerHTML = 
                        (start_offset + 1) + " - " + 
                        ((curr_records_page * max_records_per_page) > response.metadata.total ? 
                        response.metadata.total : (curr_records_page * max_records_per_page)) + " of " + 
                        response.metadata.total;
                });
            };

            // render the fetched users
            function renderRegisteredUsers(users) {
                let col_elem;
                let table_elem = document.getElementById("registered-user-list");
                window.removeAllChildElement(table_elem);

                // table header
                let row_elem = document.createElement("div");
                row_elem.setAttribute("class", "user-list-header-cont");
                row_elem.innerHTML =
                    `<div class="col-1">Referral ID</div>
                    <div class="col-2">User Name</div>
                    <div class="col-3">Name</div>
                    <div class="col-4"></div>`;

                table_elem.appendChild(row_elem);

                // iterate through the row
                for (let i = 0; i < users.length; i++) {
                    row_elem = document.createElement("div");
                    row_elem.setAttribute("class", "user-list-data-cont");

                    // iterate through the column
                    for (let j = 0; j < users[i].length; j++) {
                        col_elem = document.createElement("div");
                        col_elem.setAttribute("class", "col-" + (j + 1));

                        if (j == 3) { // 4th column
                            col_elem.innerHTML =
                                `<div class="custom-drop-down-input">
                                    <i class="fas fa-ellipsis-v"></i>
                                </div>
                                <div class="custom-drop-down-menu-cont remove-elem">
                                    <ul>
                                        <li class="menu-option" onclick="sendUserNotification('${users[i][3]}')">Send Message</li>
                                        <li class="menu-option" onclick="disableUserAcccount('${users[i][3]}')">Disable Account</li>
                                    </ul>
                                </div>`;

                        } else {
                            col_elem.setAttribute("onclick", "openUserAccountDetails('" + users[i][3] + "')");
                            col_elem.innerHTML = users[i][j];
                        }

                        row_elem.appendChild(col_elem);
                    }

                    table_elem.appendChild(row_elem);
                }
            }

            // fetch list of registered users
            function fetchRegisteredUsers(offset, limit, callback) {
                let req_url = '../request';
                let form_data = 
                    'req=get_registered_users&search=' + encodeURIComponent(search_reg_user_value) + 
                    '&field=' + (field_to_search_reg_user == 10 ? 'username' : 'referral_id') + 
                    '&offset=' + offset + '&limit=' + limit; // request query

                // send request to server
                window.ajaxRequest(
                    req_url,
                    form_data,
                    { contentType: "application/x-www-form-urlencoded" },

                    // listen to response from the server
                    function (response) {
                        callback(JSON.parse(response));
                    },

                    // listen to server error
                    function (err_status) {
                        //check if is a timeout or server busy
                        if (error_status == 408 ||
                            error_status == 504 ||
                            error_status == 503) {

                            //send the request again
                            fetchRegisteredUsers(offset, limit, callback);
                        }
                    }
                );
            }

            // get list of registered users on page load
            function loadRegisteredUsers() {
                fetchRegisteredUsers(start_offset, max_records_per_page, function (response) {
                    renderRegisteredUsers(response.users);

                    let nav_indicator = document.getElementById("reg-user-navi-indicator");
                    page_count = window.pageCountForListItem(max_records_per_page, response.metadata.total);
                    nav_indicator.innerHTML = 
                        (start_offset + 1) + " - " + 
                        ((curr_records_page * max_records_per_page) > response.metadata.total ? 
                        response.metadata.total : (curr_records_page * max_records_per_page)) + " of " + 
                        response.metadata.total;

                    // hide load animation and show registered users
                    document.querySelector('#section-cont-1 .loading-anim-cont').setAttribute("class", "loading-anim-cont remove-elem");
                    document.getElementById("registered-user-list").setAttribute("class", "user-list-cont");
                    document.querySelector('.reg-user-navigator-cont').setAttribute("class", "reg-user-navigator-cont");

                    attachEventToCustomDropDownInputOnLoad();
                });
            }

            // load user's account information from server
            function loadUserAccountInfo(info_type, callback) {
                let req_url = '../request';
                let form_data = 'req=get_user_info&info_type=' + info_type + '&user_id=' + target_user_id; // request query

                // send request to server
                window.ajaxRequest(
                    req_url,
                    form_data,
                    { contentType: "application/x-www-form-urlencoded" },

                    // listen to response from the server
                    function (response) {
                        callback(JSON.parse(response));
                    },

                    // listen to server error
                    function (err_status) {
                        // check if is timeout error
                        if (err_status == 408 && err_status == 504) {
                            window.loadUserAccountInfo(info_type, callback);

                        } else if (err_status == 503) { // check if server is busy or unavalaible
                            // wait for 2 minutes
                            setTimeout(function () {
                                window.loadUserAccountInfo(info_type, callback);

                            }, 60000 * 2);

                        } else { // other error here
                            callback({ success: false });
                        }
                    }
                );
            }

            // utility function to check if user generated click event happened over custom select input
            function eventGeneratedOverCustomInput(elem, class_value) {
                let counter = 0;
                while (true) {
                    if (elem.nodeName == "BODY" || elem.nodeName == "DOCUMENT" || elem.nodeName == "HTML") {
                        return false;
                    } else if (elem.getAttribute("class") == class_value) {
                        return true;
                    } else if (counter > 1) { // maximum iteration is 3
                        return false;
                    }

                    elem = elem.parentElement;
                    counter++;
                }
            }

            // custom select input
            let custom_select_opened = false;
            let custom_select_option_cont = document.querySelector('.custom-select-input-option-cont');
            let custom_select_input = document.querySelector('.custom-select-input');

            // listen to click event on custom select input
            custom_select_input.onclick = function (e) {
                if (custom_select_opened) {
                    custom_select_opened = false;
                    return;
                }

                // drop down select option has been opened
                custom_select_opened = true;

                // show option menu
                custom_select_option_cont.setAttribute("class", "custom-select-input-option-cont");
            };

            // listen to click event on custom select options
            let custom_select_options = custom_select_option_cont.querySelectorAll('.input-option');
            for (let i = 0; i < custom_select_options.length; i++) {
                custom_select_options[i].onmousedown = function (e) {
                    // unselect previously selected option
                    let selected_option = document.querySelector('.custom-select-input-option-cont .input-option[selected="true"]');
                    selected_option.removeAttribute("selected");

                    // select an option
                    let option = e.currentTarget;
                    option.setAttribute("selected", "true");

                    // set the custom select input value
                    custom_select_input.querySelector('.value').innerHTML = option.querySelector('.value').innerHTML;

                    // set field to search against
                    if (option.querySelector('.value').innerText == "User Name") {
                        field_to_search_reg_user = SEARCH_USERNAME;

                    } else { // Referral ID
                        field_to_search_reg_user = SEARCH_REFERRAL_ID;
                    }

                    searchRegisteredUser();
                };
            }

            // custom drop down input
            let hide_drop_down_menu = false;
            let custom_drop_down_index = -1;
            let custom_drop_down_opened = null;

            // custom drop down input handler
            function customDropDownInputHandler(event, index) {
                if (custom_drop_down_index == index && hide_drop_down_menu) {
                    hide_drop_down_menu = false;
                    return;
                }

                // set index for current input
                custom_drop_down_index = index;

                // drop down menu is showing
                hide_drop_down_menu = true;

                let clicked_elem = event.currentTarget;
                let bounding_rect = clicked_elem.getBoundingClientRect();

                // show the drop down menu
                let menu = clicked_elem.nextElementSibling;
                menu.setAttribute("class", "custom-drop-down-menu-cont");

                // position the menu
                let x = ((bounding_rect.x || bounding_rect.left) - menu.offsetWidth) + clicked_elem.offsetWidth;
                let y = (bounding_rect.y || bounding_rect.top) + clicked_elem.offsetHeight;
                menu.setAttribute("style", "top: " + y + "px; left: " + x + "px;");

                custom_drop_down_opened = menu;
            }

            // attach event to custom drop down input
            function attachEventToCustomDropDownInputOnLoad() {
                let custom_drop_down_inputs = document.querySelectorAll('.custom-drop-down-input');

                // listen to click event
                for (let i = 0; i < custom_drop_down_inputs.length; i++) {
                    custom_drop_down_inputs[i].onclick = function (e) {
                        customDropDownInputHandler(e, i);
                    };
                }
            }

            // listen to mousedown event on document
            document.onmousedown = function (e) {
                custom_select_option_cont.setAttribute("class", "custom-select-input-option-cont remove-elem");
                if (!eventGeneratedOverCustomInput(e.target, "custom-select-input")) {
                    custom_select_opened = false;
                }

                // custom drop down input
                if (custom_drop_down_opened != null) {
                    custom_drop_down_opened.setAttribute("class", "custom-drop-down-menu-cont remove-elem");
                    if (!eventGeneratedOverCustomInput(e.target, "custom-drop-down-input")) {
                        hide_drop_down_menu = false;
                    }
                }
            };

            // listen to when user click the back button on "registered users" page
            document.getElementById("goto-current-section").onclick = function (e) {
                // show section 1 and hide section 2
                document.getElementById("section-cont-1").removeAttribute("class");
                document.getElementById("section-cont-2").setAttribute("class", "remove-elem");
            };

            // call function after page is loaded
            loadRegisteredUsers();
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