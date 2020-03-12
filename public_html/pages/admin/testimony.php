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
    'users' => false,
    'testimony' => true,
    'settings' => false
];

// assemble all the part of the page
require_once 'admin_header.php';
require_once 'admin_side_menu.php';

?>

    <div class="page-content-cont">
        <?php require_once 'admin_top_main_menu.php' ?>
        <div class="page-content">
            <h1 class="page-title-hd">Testimony</h1>
            <div class="testimony-list-cont">
                <div id="testimony-empty-msg" class="remove-elem">No testimony</div>
                <div id="testimony-list" class="remove-elem">
                    <div id="2" class="testimony-cont">
                        <div class="top-bar-cont">
                            <div class="user-name">Attamah Celestine</div>
                            <div class="verify-btn-cont" title="Verify the testimony." onclick="verifyTestimony('2')">
                                <i class="fas fa-check"></i>
                            </div>
                        </div>
                        <div class="content-cont">
                            To use this API, you will need to run small local service which be responsible for 
                            managing your Blockchain.info wallet. Your application interacts with this service 
                            locally via HTTP API calls.
                        </div>
                        <div class="footer-bar-cont">
                            <div class="write-time-label">Mar 10, 2020 12:35 PM</div>
                        </div>
                    </div>
                </div>
                <div class="testimonies-navigator-cont remove-elem">
                    <div class="testimonies-navigator">
                        <span id="testimonies-navi-indicator"></span>
                        <div class="nav-button-cont">
                            <button class="prev-btn" title="Previous" onclick="navigateTestimonies('prev')"><i class="fas fa-caret-left"></i></button>
                            <button class="next-btn" title="Next" onclick="navigateTestimonies('next')"><i class="fas fa-caret-right"></i></button>
                        </div>
                    </div>
                </div>
                <div class="loading-anim-cont">
                    <div class="rot-bar-cont">
                        <span class="rot-bar rot-quart"></span>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script>
        function init() {
            // initialise contants and variables here
            let page_count = 1; // after page load it will be set to right value
            let max_records_per_page = 5; // set this to maximum items that can be display at once
            let curr_records_page = 1;
            let start_offset = 0;
            let update_start_time_offset = 0;
            let update_testimony_wroker;
            let hide_tm_empty_msg = false;
            let defer_testimony_update = false;
            let processing_req = false;

            // verify user's testimony
            window.verifyTestimony = function (testimony_id) {
                if (processing_req) {
                    return;
                }

                processing_req = true;
                defer_testimony_update = true;

                let req_url = '../request';
                let form_data = 'req=verify_user_testimony&id=' + testimony_id; // request query

                // send request to server
                window.ajaxRequest(
                    req_url,
                    form_data,
                    { contentType: "application/x-www-form-urlencoded" },

                    // listen to response from the server
                    function (response) {
                        // remove the testimony from the list
                        let list_cont = document.getElementById("testimony-list");
                        let item = document.getElementById("testimony-" + testimony_id);
                        list_cont.removeChild(item);

                        // update list view
                        updateTestimonyCurrentList();

                        processing_req = false;
                        defer_testimony_update = false;
                    },

                    // listen to server error
                    function (error_status) {
                        //check if is a timeout or server busy
                        if (error_status == 408 ||
                            error_status == 504 ||
                            error_status == 503) {

                            // send the request again
                            fetchUserTestimony(offset, limit, callback);
                        }

                        processing_req = false;
                        defer_testimony_update = false;
                    }
                );
            };

            // navigate through account
            window.navigateTestimonies = function (direction) {
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

                // fetch testimonies
                fetchUsersTestimony(start_offset, max_records_per_page, function (response) {
                    renderUsersTestimony(response.testimonies);
                    let nav_indicator = document.getElementById("testimonies-navi-indicator");

                    if (direction == 'next') {
                        curr_records_page += 1;
                    } else {
                        curr_records_page -= 1;
                    }

                    page_count = pageCountForListItem(max_records_per_page, response.metadata.total);
                    nav_indicator.innerHTML = 
                        (start_offset + 1) + " - " + 
                        ((curr_records_page * max_records_per_page) > response.metadata.total ? 
                        response.metadata.total : (curr_records_page * max_records_per_page)) + " of " + 
                        response.metadata.total;
                });
            };

            // update testimony current list
            function updateTestimonyCurrentList() {
                // reload current list
                fetchUsersTestimony(start_offset, max_records_per_page, function (response) {
                    // check if result is empty and this is not the first page
                    if (response.testimonies.length < 1 && curr_records_page > 1) {
                        curr_records_page -= 1; // decrement by one
                        start_offset = max_records_per_page * (curr_records_page - 1);

                        updateTestimonyCurrentList();

                    } else if (response.testimonies.length < 1) {
                        // show empty message
                        document.getElementById("testimony-list").setAttribute("class", "remove-elem");
                        document.querySelector('.testimonies-navigator-cont').setAttribute("class", "testimonies-navigator-cont remove-elem");
                        document.getElementById("testimony-empty-msg").removeAttribute("class");
                        hide_tm_empty_msg = true;

                    } else {
                        renderUsersTestimony(response.testimonies);
                        let nav_indicator = document.getElementById("testimonies-navi-indicator");

                        page_count = pageCountForListItem(max_records_per_page, response.metadata.total);
                        nav_indicator.innerHTML = 
                            (start_offset + 1) + " - " + 
                            ((curr_records_page * max_records_per_page) > response.metadata.total ? 
                            response.metadata.total : (curr_records_page * max_records_per_page)) + " of " + 
                            response.metadata.total;
                    }
                });
            }

            function renderUsersTestimony(testimonies) {
                let list_cont = document.getElementById("testimony-list");
                window.removeAllChildElement(list_cont); // clear the list if there any

                // iterate through the list
                for (let i = 0; i < testimonies.length; i++) {
                    let tm_elem = document.createElement("div");
                    tm_elem.setAttribute("id", "testimony-" + testimonies[i].id);
                    tm_elem.setAttribute("class", "testimony-cont");
                    tm_elem.innerHTML = 
                        `<div class="top-bar-cont">
                            <div class="user-name">${testimonies[i].name}</div>
                            <div class="verify-btn-cont" title="Verify the testimony." onclick="verifyTestimony('${testimonies[i].id}')">
                                <i class="fas fa-check"></i>
                            </div>
                        </div>
                        <div class="content-cont">${testimonies[i].content}</div>
                        <div class="footer-bar-cont">
                            <div class="write-time-label">${testimonies[i].fmt_time}</div>
                        </div>`;

                    list_cont.appendChild(tm_elem);
                }
            }

            // update listed testimony
            function renderNewTestimony(response) {
                let testimonies = response.testimonies;

                if (testimonies.length > 0) {
                    // check to hide empty list message
                    if (hide_tm_empty_msg) {
                        hide_tm_empty_msg = false;
                        document.getElementById("testimony-empty-msg").setAttribute("class", "remove-elem");
                        document.getElementById("testimony-list").removeAttribute("class");
                        document.querySelector('.testimonies-navigator-cont').setAttribute("class", "testimonies-navigator-cont");
                    }

                    // check to clear all the list
                    if (testimonies.length >= max_records_per_page) {
                        renderUsersTestimony(testimonies);

                    } else {
                        let list_cont = document.getElementById("testimony-list");
                        let list_items = list_cont.children;
                        let first_item = list_items[0];
                        let rm_item_count = testimonies.length - (max_records_per_page - list_items.length);

                        // remove item(s) in the list for new testimony
                        for (let i = 0; i < rm_item_count; i++) {
                            // remove last item in the list
                            list_cont.removeChild(list_items[list_items.length - 1]);
                            list_items = list_cont.children;
                        }

                        // insert the new item(s)
                        for (let j = 0; j < testimonies.length; j++) {
                            let tm_elem = document.createElement("div");
                            tm_elem.setAttribute("id", "testimony-" + testimonies[j].id);
                            tm_elem.setAttribute("class", "testimony-cont");
                            tm_elem.innerHTML = 
                                `<div class="top-bar-cont">
                                    <div class="user-name">${testimonies[j].name}</div>
                                    <div class="verify-btn-cont" title="Verify the testimony." onclick="verifyTestimony('${testimonies[j].id}')">
                                        <i class="fas fa-check"></i>
                                    </div>
                                </div>
                                <div class="content-cont">${testimonies[j].content}</div>
                                <div class="footer-bar-cont">
                                    <div class="write-time-label">${testimonies[j].fmt_time}</div>
                                </div>`;

                            list_cont.insertBefore(tm_elem, first_item);
                        }
                    }

                    let nav_indicator = document.getElementById("testimonies-navi-indicator");
                    page_count = window.pageCountForListItem(max_records_per_page, response.metadata.total);
                    nav_indicator.innerHTML = 
                        (start_offset + 1) + " - " + 
                        ((curr_records_page * max_records_per_page) > response.metadata.total ? 
                        response.metadata.total : (curr_records_page * max_records_per_page)) + " of " + 
                        response.metadata.total;
                }
            }

            // update view with new testimonies every interval
            function initTestimonyUpdate() {
                // checks if the worker does not exists
                if (typeof update_testimony_wroker == 'undefined') {
                    update_testimony_wroker = new Worker("../js/updateTestimonyWorker.js");

                    // listen to when data is sent
                    update_testimony_wroker.addEventListener("message", function (e) {
                        if (!defer_testimony_update) {
                            // render result to view
                            renderNewTestimony(e.data);
                        }
                        
                    }, false);

                    // initialise fetch of new notification
                    update_testimony_wroker.postMessage({ 
                        time_offset: update_start_time_offset, 
                        msg_limit: max_records_per_page
                    });
                }
            }

            // load users' testimonies
            function loadUsersTestimony() {
                fetchUsersTestimony(start_offset, max_records_per_page, function (response) {
                    if (response.testimonies.length > 0) {
                        renderUsersTestimony(response.testimonies);

                        // start offset for new testimony
                        update_start_time_offset = response.testimonies[0].time;

                        let nav_indicator = document.getElementById("testimonies-navi-indicator");
                        page_count = window.pageCountForListItem(max_records_per_page, response.metadata.total);
                        nav_indicator.innerHTML = 
                            (start_offset + 1) + " - " + 
                            ((curr_records_page * max_records_per_page) > response.metadata.total ? 
                            response.metadata.total : (curr_records_page * max_records_per_page)) + " of " + 
                            response.metadata.total;

                        // hide loading animation and show users' testimonies
                        document.querySelector('.testimony-list-cont .loading-anim-cont').setAttribute("class", "loading-anim-cont remove-elem");
                        document.getElementById("testimony-list").removeAttribute("class");
                        document.querySelector('.testimonies-navigator-cont').setAttribute("class", "testimonies-navigator-cont");

                    } else {
                        // hide loading animation
                        document.querySelector('.testimony-list-cont .loading-anim-cont').setAttribute("class", "loading-anim-cont remove-elem");

                        // show testimony empty message
                        document.getElementById("testimony-empty-msg").removeAttribute("class");
                        hide_tm_empty_msg = true;

                    }

                    // initiate list update every interval
                    initTestimonyUpdate();
                });
            }

            // fetch list of unverified account
            function fetchUsersTestimony(offset, limit, callback) {
                defer_testimony_update = true;

                let req_url = '../request';
                let form_data = 'req=get_user_testimony&offset=' + offset + '&limit=' + limit; // request query

                // send request to server
                window.ajaxRequest(
                    req_url,
                    form_data,
                    { contentType: "application/x-www-form-urlencoded" },

                    // listen to response from the server
                    function (response) {
                        callback(JSON.parse(response));
                        defer_testimony_update = false;
                    },

                    // listen to server error
                    function (error_status) {
                        //check if is a timeout or server busy
                        if (error_status == 408 ||
                            error_status == 504 ||
                            error_status == 503) {

                            // send the request again
                            fetchUserTestimony(offset, limit, callback);
                        }

                        defer_testimony_update = false;
                    }
                );
            }

            // call after page load
            loadUsersTestimony();
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