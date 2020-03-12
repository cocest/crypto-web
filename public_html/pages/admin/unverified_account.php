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
                    <li class="tab-menu">
                        <a href="./registered_users.html">Registered Users</a>
                    </li>
                    <li class="tab-menu active">
                        <a href="./unverified_account.html">Unverified Account</a>
                    </li>
                </ul>
            </div>
            <div id="section-cont-1" class="top-border">
                <div class="section-wrapper">
                    <div class="table-wrapper-cont">
                        <div id="unverified-account-list" class="account-list-cont remove-elem"></div>
                    </div>
                    <div class="unverified-account-navigator-cont remove-elem">
                        <div class="unverified-account-navigator">
                            <span id="unverified-account-navi-indicator"></span>
                            <div class="nav-button-cont">
                                <button class="prev-btn" title="Previous" onclick="navigateUnverifiedAccount('prev')"><i class="fas fa-caret-left"></i></button>
                                <button class="next-btn" title="Next" onclick="navigateUnverifiedAccount('next')"><i class="fas fa-caret-right"></i></button>
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
            <div id="section-cont-2" class="remove-elem">
                <div class="unverify-account-top-menu-cont">
                    <div id="goto-current-section" class="back-button" title="Go back">
                        <i class="fas fa-arrow-left"></i>
                    </div>
                    <div class="header-title">User's Identification</div>
                </div>
                <div class="content-cont remove-elem">
                    <div class="user-uploded-id-cont">
                        <div class="img-top-menu-cont">
                            <button class="button" title="Zoom Out" onclick="zoomUserIDImg('out')"><i class="fas fa-minus"></i></button>
                            <button class="button" title="Zoom In" onclick="zoomUserIDImg('in')"><i class="fas fa-plus"></i></button>
                        </div>
                        <div class="img-cont">
                            <img id="user-uploded-id-img" alt="user's ID" />
                        </div>
                        <div id="img-id-drag-listener"></div>
                    </div>
                    <div class="accept-id-btn-cont">
                        <button class="accept-btn" onclick="acceptUserID()">Accept</button>
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
            // define and initialise variables
            let target_user_id;
            let page_count = 1; // after page load it will be set to right value
            let max_records_per_page = 10; // set this to maximum items that can be display at once
            let curr_records_page = 1;
            let start_offset = 0;
            let img_elem;
            let id_img_width;
            let id_img_height;
            let id_img_cont_elem;
            let id_img_zoom_max;
            let id_img_zoom_factor;
            let id_img_zoom_count = 1;
            let pan_img_x = 0;
            let pan_img_y = 0;
            let is_mouse_down = false;
            let mouse_start_point = {x: 0, y: 0};
            let verifying_user_id = false;

            // delete account in the list and update the current list
            function updateAccountCurrentList() {
                // hide current section
                document.querySelector('#section-cont-2 .content-cont').setAttribute("class", "content-cont remove-elem");
                document.querySelector('#section-cont-2 .loading-anim-cont').setAttribute("class", "loading-anim-cont");
                document.getElementById("section-cont-2").setAttribute("class", "remove-elem");

                // show section 1
                document.getElementById("section-cont-1").setAttribute("class", "top-border");
                document.querySelector('#section-cont-1 .loading-anim-cont').setAttribute("class", "loading-anim-cont");
                document.getElementById("unverified-account-list").setAttribute("class", "account-list-cont remove-elem");
                document.querySelector('.unverified-account-navigator-cont').setAttribute("class", "unverified-account-navigator-cont remove-elem");
                
                // reload current list of unverified account
                fetchUnverifiedAccount(start_offset, max_records_per_page, function (response) {
                    // check if result is empty and this is not the first page
                    if (response.accounts.length < 1 && curr_records_page > 1) {
                        curr_records_page -= 1; // decrement by one
                        start_offset = max_records_per_page * (curr_records_page - 1);

                        updateAccountCurrentList();

                    } else {
                        renderUnverifiedAccount(response.accounts);
                        let nav_indicator = document.getElementById("unverified-account-navi-indicator");

                        page_count = pageCountForListItem(max_records_per_page, response.metadata.total);
                        nav_indicator.innerHTML = 
                            (start_offset + 1) + " - " + 
                            ((curr_records_page * max_records_per_page) > response.metadata.total ? 
                            response.metadata.total : (curr_records_page * max_records_per_page)) + " of " + 
                            response.metadata.total;

                        // hide load animation and show unverified account
                        document.querySelector('#section-cont-1 .loading-anim-cont').setAttribute("class", "loading-anim-cont remove-elem");
                        document.getElementById("unverified-account-list").setAttribute("class", "account-list-cont");
                        document.querySelector('.unverified-account-navigator-cont').setAttribute("class", "unverified-account-navigator-cont");
                    }
                });
            }

            // accept user's uploaded identification
            window.acceptUserID = function () {
                verifying_user_id = true;

                let accept_btn = document.querySelector('.accept-id-btn-cont .accept-btn');
                let bg_modal = document.getElementById("bg-modal-2");
                let bg_modal_loading_anim = document.getElementById("bg-modal-loading-anim");
                accept_btn.disabled = true;
                bg_modal.setAttribute("class", "show");
                bg_modal_loading_anim.removeAttribute("class");

                let req_url = '../request';
                let form_data = 'req=accept_user_id&user_id=' + target_user_id; // request query

                // send request to server
                window.ajaxRequest(
                    req_url,
                    form_data,
                    { contentType: "application/x-www-form-urlencoded" },

                    // listen to response from the server
                    function (response) {
                        accept_btn.disabled = false;
                        bg_modal.setAttribute("class", "remove");
                        bg_modal_loading_anim.setAttribute("class", "remove-elem");

                        let parse_response = JSON.parse(response);
                        if (parse_response.success) {
                            updateAccountCurrentList();
                        }

                        verifying_user_id = false;
                    },

                    // listen to server error
                    function (error_status) {
                        //check if is a timeout or server busy
                        if (error_status == 408 ||
                            error_status == 504 ||
                            error_status == 503) {

                            //send the request again
                            acceptUserID();

                        } else {
                            accept_btn.disabled = false;
                            bg_modal.setAttribute("class", "remove");
                            bg_modal_loading_anim.setAttribute("class", "remove-elem");
                        }
                    }
                );
            };

            // zoom user's identification
            window.zoomUserIDImg = function (zoom) {
                if (zoom == "in") {
                    if (id_img_zoom_count < id_img_zoom_max + 1) {
                        id_img_zoom_count += 1;
                    } else {
                        return;
                    }

                } else { // out
                    if (id_img_zoom_count > 1) {
                        id_img_zoom_count -= 1;
                    } else {
                        return;
                    }
                }

                // set pan to default
                pan_img_x = 0;
                pan_img_y = 0;

                zoomAndPositionImage(
                    img_elem,
                    id_img_cont_elem.offsetWidth, // view width
                    id_img_cont_elem.offsetHeight, // view height
                    id_img_width, // image default width
                    id_img_height, // image default height
                    id_img_zoom_count * id_img_zoom_factor, // zoom factor
                    pan_img_x, // pan left and right
                    pan_img_y // pan up and down
                );
            };

            // open a section for user's uploaded identification
            window.verifyUserAccount = function (user_id) {
                // set user identification
                target_user_id = user_id;

                // hide section 1 and show section 2
                document.getElementById("section-cont-1").setAttribute("class", "remove-elem");
                document.getElementById("section-cont-2").removeAttribute("class");

                getUserUploadedID(function (response) {
                    // hide loading animation and show the content
                    document.querySelector('#section-cont-2 .loading-anim-cont').setAttribute("class", "loading-anim-cont remove-elem");
                    document.querySelector('#section-cont-2 .content-cont').setAttribute("class", "content-cont");

                    // get image container element
                    id_img_cont_elem = document.querySelector('.user-uploded-id-cont .img-cont');

                    // display the image
                    img_elem = document.getElementById("user-uploded-id-img");
                    img_elem.setAttribute("src", response.user_id_url);

                    // set image dimension and position
                    id_img_width = response.metadata.width;
                    id_img_height = response.metadata.height;

                    // calculate the maximum we can zoom an image
                    id_img_zoom_factor = calcBestFitZoomFactor(
                        id_img_cont_elem.offsetWidth, 
                        id_img_cont_elem.offsetHeight, 
                        id_img_width, 
                        id_img_height
                    );

                    id_img_zoom_max = Math.round(1.5 / id_img_zoom_factor);

                    zoomAndPositionImage(
                        img_elem,
                        id_img_cont_elem.offsetWidth, // view width
                        id_img_cont_elem.offsetHeight, // view height
                        id_img_width, // image default width
                        id_img_height, // image default height
                        id_img_zoom_factor, // zoom factor
                        pan_img_x, // pan left and right
                        pan_img_y // pan up and down
                    );
                });
            };

            function calcBestFitZoomFactor(v_w, v_h, img_w, img_h) {
                let scale_factor = 1;

                // check if image should be fitted
                if (img_w > v_w || img_h > v_h) {
                    // check if both width and height can't fit
                    if (img_w > v_w && img_h > v_h) {
                        // check to fit width
                        if (img_w > img_h) {
                            scale_factor = v_w / img_w;

                        } else { // fit height
                            scale_factor = v_h / img_h;
                        }

                    } else if (img_w > v_w) { // fit width
                        scale_factor = v_w / img_w;

                    } else { // fit height
                        scale_factor = v_h / img_h;
                    }
                }

                return scale_factor;
            }

            // utility function that zoom an image and position it. 
            function zoomAndPositionImage(img_frame, v_w, v_h, img_w, img_h, zoom_factor, pan_x, pan_y) {
                // image new width after zoom
                let new_img_w = img_w * zoom_factor;
                let new_img_h = img_h * zoom_factor;

                // calculate image positioning
                let img_pos_x = (v_w - new_img_w) / 2 + pan_x;
                let img_pos_y = (v_h - new_img_h) / 2 + pan_y;

                // set the image
                img_frame.setAttribute("width", new_img_w);
                img_frame.setAttribute("height", new_img_h);
                img_frame.setAttribute("style", "top: " + img_pos_y + "px; " + "left: " + img_pos_x + "px;");
            }

            // navigate through account
            window.navigateUnverifiedAccount = function (direction) {
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

                // fetch account
                fetchUnverifiedAccount(start_offset, max_records_per_page, function (response) {
                    renderUnverifiedAccount(response.accounts);
                    let nav_indicator = document.getElementById("unverified-account-navi-indicator");

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

            // render the fetched account
            function renderUnverifiedAccount(accounts) {
                let col_elem;
                let table_elem = document.getElementById("unverified-account-list");
                window.removeAllChildElement(table_elem);

                // iterate through the row
                for (let i = 0; i < accounts.length; i++) {
                    row_elem = document.createElement("div");
                    row_elem.setAttribute("id", "user-" + accounts[i][3]);
                    row_elem.setAttribute("class", "account-list-data-cont");

                    // iterate through the column
                    for (let j = 0; j < accounts[i].length - 1; j++) {
                        col_elem = document.createElement("div");
                        col_elem.setAttribute("class", "col-" + (j + 1));

                        if (j == 2) { // 3th column
                            col_elem.setAttribute("onclick", "verifyUserAccount('" + accounts[i][3] + "')");
                            col_elem.innerHTML = '<div class="reg-date-cont">' + accounts[i][2] + '</div>';

                        } else {
                            col_elem.setAttribute("onclick", "verifyUserAccount('" + accounts[i][3] + "')");
                            col_elem.innerHTML = accounts[i][j];
                        }

                        row_elem.appendChild(col_elem);
                    }

                    table_elem.appendChild(row_elem);
                }
            }

            // get user's uploaded ID
            function getUserUploadedID(callback) {
                let req_url = '../request';
                let form_data = 'req=get_user_uploaded_id&user_id=' + target_user_id; // request query

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
                    function (error_status) {
                        //check if is a timeout or server busy
                        if (error_status == 408 ||
                            error_status == 504 ||
                            error_status == 503) {

                            //send the request again
                            getUserUploadedID(callback);
                        }
                    }
                );
            }

            // fetch list of unverified account
            function fetchUnverifiedAccount(offset, limit, callback) {
                let req_url = '../request';
                let form_data = 'req=get_unverified_account&offset=' + offset + '&limit=' + limit; // request query

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
                    function (error_status) {
                        //check if is a timeout or server busy
                        if (error_status == 408 ||
                            error_status == 504 ||
                            error_status == 503) {

                            //send the request again
                            fetchUnverifiedAccount(offset, limit, callback);
                        }
                    }
                );
            }

            // get list of unverified account on page load
            function loadUnverifiedAccount() {
                fetchUnverifiedAccount(start_offset, max_records_per_page, function (response) {
                    renderUnverifiedAccount(response.accounts);

                    let nav_indicator = document.getElementById("unverified-account-navi-indicator");
                    page_count = window.pageCountForListItem(max_records_per_page, response.metadata.total);
                    nav_indicator.innerHTML = 
                        (start_offset + 1) + " - " + 
                        ((curr_records_page * max_records_per_page) > response.metadata.total ? 
                        response.metadata.total : (curr_records_page * max_records_per_page)) + " of " + 
                        response.metadata.total;

                    // hide load animation and show unverified account
                    document.querySelector('#section-cont-1 .loading-anim-cont').setAttribute("class", "loading-anim-cont remove-elem");
                    document.getElementById("unverified-account-list").setAttribute("class", "account-list-cont");
                    document.querySelector('.unverified-account-navigator-cont').setAttribute("class", "unverified-account-navigator-cont");
                });
            }

            // pan image arround
            function panImage(position, e) {
                let img_scr_x = position.x - mouse_start_point.x;
                let img_scr_y = position.y - mouse_start_point.y;

                // calculate maximum scroll left and up
                let zoom_factor = id_img_zoom_count * id_img_zoom_factor;
                let scroll_left_max = (id_img_cont_elem.offsetWidth - (id_img_width * zoom_factor)) / 2;
                let scroll_up_max = (id_img_cont_elem.offsetHeight - (id_img_height * zoom_factor)) / 2;

                // check if image should be moved horizontally
                if (img_scr_x < 0) { // left
                    if (scroll_left_max > 0) {
                        pan_img_x = 0;
                    } else if (pan_img_x + img_scr_x > scroll_left_max) {
                        pan_img_x = pan_img_x + img_scr_x;
                    } else {
                        pan_img_x = scroll_left_max;
                    }

                } else { // right
                    if (scroll_left_max > 0) {
                        pan_img_x = 0;
                    } else if (pan_img_x + img_scr_x < scroll_left_max * -1) {
                        pan_img_x = pan_img_x + img_scr_x;
                    } else {
                        pan_img_x = scroll_left_max * -1;
                    }
                }

                // check if image should be moved vertically
                if (img_scr_y < 0) { // up
                    if (scroll_up_max > 0) {
                        pan_img_x = 0;
                    } else if (pan_img_y + img_scr_y > scroll_up_max) {
                        pan_img_y = pan_img_y + img_scr_y;

                        // prevent page scrolling
                        e.preventDefault();

                    } else {
                        pan_img_y = scroll_up_max;
                    }

                } else { // down
                    if (scroll_up_max > 0) {
                        pan_img_y = 0;
                    } else if (pan_img_y + img_scr_y < scroll_up_max * -1) {
                        pan_img_y = pan_img_y + img_scr_y;

                        // prevent page scrolling
                        e.preventDefault();

                    } else {
                        pan_img_y = scroll_up_max * -1;
                    }
                }

                zoomAndPositionImage(
                    img_elem,
                    id_img_cont_elem.offsetWidth, // view width
                    id_img_cont_elem.offsetHeight, // view height
                    id_img_width, // image default width
                    id_img_height, // image default height
                    zoom_factor, // zoom factor
                    pan_img_x, // pan left and right
                    pan_img_y // pan up and down
                );

                // reposition start point
                mouse_start_point.x = position.x;
                mouse_start_point.y = position.y;
            }

            // listen to mouse drag on user's identification
            let user_id = document.querySelector('#img-id-drag-listener');

            // for mouse event
            user_id.onmousemove = function (e) {
                if (is_mouse_down) {
                    panImage({x: e.offsetX, y: e.offsetY}, e);
                }
            };

            user_id.onmousedown = function (e) {
                if (!is_mouse_down) {
                    is_mouse_down = true;
                }

                mouse_start_point.x = e.offsetX;
                mouse_start_point.y = e.offsetY;
            };

            user_id.onmouseup = function (e) {
                if (is_mouse_down) {
                    is_mouse_down = false;
                }
            };

            // for touch devices
            let tracked_touch = null;

            user_id.addEventListener("touchmove", function (e) {
                //e.preventDefault();
                let touches = e.changedTouches;
                let touch = null;

                // check if user's tracked finger is still active
                if (tracked_touch == null) return false;

                // find the track finger
                for (let i = 0; i < touches.length; i++) {
                    if (tracked_touch.identifier == touches[i].identifier) {
                        touch = touches[i];
                        break; // exit for
                    }
                }

                if (touch) {
                    panImage({x: touch.pageX, y: touch.pageY}, e);
                }

            }, false);

            user_id.addEventListener("touchstart", function (e) {
                //e.preventDefault();
                let touches = e.changedTouches;

                // check if we haven't start tracking a finger
                if (tracked_touch == null) {
                    // we track only one finger
                    tracked_touch = touches[0];

                    mouse_start_point.x = touches[0].pageX;
                    mouse_start_point.y = touches[0].pageY;
                }

            }, false);

            user_id.addEventListener("touchend", function (e) {
                //e.preventDefault();
                let touches = e.changedTouches;

                if (tracked_touch != null) {
                    tracked_touch = null;
                }

            }, false);

            // listen to click event on back button
            document.getElementById("goto-current-section").onclick = function (e) {
                if (verifying_user_id) {
                    return;
                }

                // show section 1
                document.getElementById("section-cont-1").setAttribute("class", "top-border");

                // hide section 2
                document.querySelector('#section-cont-2 .content-cont').setAttribute("class", "content-cont remove-elem");
                document.querySelector('#section-cont-2 .loading-anim-cont').setAttribute("class", "loading-anim-cont");
                document.getElementById("section-cont-2").setAttribute("class", "remove-elem");
            };

            // listen to resize event
            window.onresize = function (e) {
                zoomAndPositionImage(
                    img_elem,
                    id_img_cont_elem.offsetWidth, // view width
                    id_img_cont_elem.offsetHeight, // view height
                    id_img_width, // image default width
                    id_img_height, // image default height
                    id_img_zoom_count * id_img_zoom_factor, // zoom factor
                    pan_img_x, // pan left and right
                    pan_img_y // pan up and down
                );
            };

            // call function after page is loaded
            loadUnverifiedAccount();
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