function init() {
    // define and initialise variables
    const DESKTOP_VIEW = 10;
    const TABLET_VIEW = 11;
    const MOBILE_VIEW = 12;
    const SWIPE_HORIZONTAL = 10;
    const SWIPE_VERTICAL = 11;
    const SWIPE_START = 12;
    const SWIPE_REMOVED = 13;
    const SWIPE_END = 14;
    const SWIPE_LEFT = 15;
    const SWIPE_RIGHT = 16;
    
    let active_view;
    let top_main_menu_hidden = false;
    let top_main_menu_changed = true;
    let page_scroll_y = 0;
    let curr_page_scroll_y = 0;
    //let window_active = false;
    let drop_mobi_menu_active = false;
    /*let curr_panel_view_count = 3;
    let curr_tm_panel_index = 0;
    let slide_tm_wait = false;
    let testimonies_ready = false;*/
    let less_more_pkgs_toggle = false;
    let curr_crypto_price_btn_id = 0;
    let is_crypto_stat_loading = true;
    let update_crypto_statistics_worker;
    let current_inv_pkg_swipe_index = 0;
    let requestAnimationFrame = window.requestAnimationFrame
        || window.mozRequestAnimationFrame
        || window.webkitRequestAnimationFrame
        || function (fn) { return window.setTimeout(fn, 16); };
    let cancelAnimationFrame = window.cancelAnimationFrame
        || window.mozCancelAnimationFrame
        || window.webkitCancelAnimationFrame
        || function (request_id) { clearTimeout(request_id); };
    /*let req_tm_anim_handler;
    let tm_anim_running = false;
    let touch_event_attached = false;
    let active_touches_tm = [];*/
    let crypto_price_table_header = [
        'NAME', 'PRICE', 'LAST UPDATE', 'MARKET CAP', 'SUPPLY', 'CHANGE PCT', 'LAST VOLUME'
    ];
    let exchange_currencies = [
        'US_DOLLAR', 'EURO', 'BRITISH_POUNDS'
    ];
    let testimonies;

    // cryptocurrencies' price
    let cryptoprices;

    let prev_active_link = document.querySelector('.drop-down-mobi-menu-cont .active');
    let page_sections = [];
    page_sections[0] = document.querySelector('.page-upper-section');
    page_sections[1] = document.querySelector('.contact-us-section-cont');

    // get contact us form
    let contact_us_form = document.forms["contact-us-form"];

    // scroll to the page to section
    window.scrollToSection = function (index, link_elem) {
        window.smoothScroll(page_sections[index], 1000, 0, function(elem) {
            if (window.innerWidth <= 1200) { // for desktop don't mark the link
                prev_active_link.setAttribute("class", "link"); // reset previousely clicked link

                link_elem.setAttribute("class", "link active"); // select the click link
                prev_active_link = link_elem;
            }
        });
    };

    window.closeWindowPanel = function (win_id) {
        let elem = document.getElementById(win_id);
        elem.setAttribute('class', 'remove-elem');

        window_active = false;
    };

    window.displayFullTestimoney = function (btn_elem, index) {
        let toggle = parseInt(btn_elem.getAttribute('toggle'));
        let elem;

        if (toggle == 0) {
            btn_elem.setAttribute('toggle', '1');
            btn_elem.innerHTML = "Read Less";

            elem = document.getElementById('tm-txt-cont-' + index);
            elem.setAttribute('class', 'txt-cont expand');

            elem = document.getElementById('tm-txt-' + index);
            elem.innerHTML = testimonies[index].testimoney;

        } else {
            btn_elem.setAttribute('toggle', '0');
            btn_elem.innerHTML = "Read More";

            elem = document.getElementById('tm-txt-cont-' + index);
            elem.setAttribute('class', 'txt-cont collapse');

            elem = document.getElementById('tm-txt-' + index);
            elem.innerHTML = clipOutText(250, testimonies[index].testimoney)[0];
        }
    };

    // navigate through testimonial
    /*window.slideTestimonial = function (direction) {
        // wait for the animation to finish
        if (slide_tm_wait) {
            return;
        } else {
            slide_tm_wait = true; // wait
        }

        let panel_cont, panels, panel, elem, scroll_index, clipped_text;

        if (direction == 'next') {
            scroll_index = getTestimonialNextIndex('next', curr_panel_view_count);
            clipped_text = clipOutText(250, testimonies[scroll_index].testimoney);

            panel_cont = document.querySelector('.testimonial-panel-cont');
            panels = panel_cont.getElementsByClassName('testimonial-panel');

            if (curr_panel_view_count == 3) {
                panel_cont.setAttribute('style', 'width: 133%;');
                panel_cont.setAttribute('class', 'testimonial-panel-cont view-4');

            } else if (curr_panel_view_count == 2) {
                panel_cont.setAttribute('style', 'width: 151%;');
                panel_cont.setAttribute('class', 'testimonial-panel-cont view-3');

            } else if (curr_panel_view_count == 1) {
                panel_cont.setAttribute('style', 'width: 200%;');
                panel_cont.setAttribute('class', 'testimonial-panel-cont view-2');
            }

            panel = document.createElement('div');
            panel.setAttribute('class', 'testimonial-panel');

            elem = document.createElement('div');
            elem.setAttribute('class', 'cont');
            elem.innerHTML =
                `<i class="quote-icon fas fa-quote-left"></i>
                         <div id="tm-txt-cont-${scroll_index}" class="txt-cont collapse">
                             <p id="tm-txt-${scroll_index}" class="txt ux-fs-px-18">${clipped_text[0]}</p>
                             ${clipped_text[1] ? '<div class="expand-btn ux-fs-px-18 ux-txt-grayblue" toggle="0" onclick="displayFullTestimoney(this, ' + scroll_index + ')">Read More</div>' : ''}
                         </div>
                         <div class="pointer"></div>
                         <div class="profile-cont">
                             <div class="picture-cont">
                                 <img class="prof-pic ux-f-rd-corner" src="${testimonies[scroll_index].profile_picture}" alt="user's profile picture" />
                             </div>
                             <div class="name ux-txt-white ux-fs-px-20">${testimonies[scroll_index].name}</div>
                         </div>`;

            panel.appendChild(elem)
            panel_cont.appendChild(panel);

            // scroll right
            if (curr_panel_view_count == 3) {
                panel_cont.setAttribute('class', 'testimonial-panel-cont view-4 scroll-right');

            } else if (curr_panel_view_count == 2) {
                panel_cont.setAttribute('class', 'testimonial-panel-cont view-3 scroll-right-50');

            } else if (curr_panel_view_count == 1) {
                panel_cont.setAttribute('class', 'testimonial-panel-cont view-2 scroll-right-100');
            }

            // wait for 0.5 second and remove the first element at left
            setTimeout(function () {
                panel_cont.removeChild(panels[0]);
                panel_cont.removeAttribute('style');

                if (curr_panel_view_count == 3) {
                    panel_cont.setAttribute('class', 'testimonial-panel-cont view-3');

                } else if (curr_panel_view_count == 2) {
                    panel_cont.setAttribute('class', 'testimonial-panel-cont view-2');

                } else if (curr_panel_view_count == 1) {
                    panel_cont.setAttribute('class', 'testimonial-panel-cont view-1');
                }

                slide_tm_wait = false

            }, 550);

        } else { // previous
            scroll_index = getTestimonialNextIndex('prev', curr_panel_view_count);
            clipped_text = clipOutText(250, testimonies[scroll_index].testimoney);

            panel_cont = document.querySelector('.testimonial-panel-cont');
            panels = panel_cont.getElementsByClassName('testimonial-panel');

            if (curr_panel_view_count == 3) {
                panel_cont.setAttribute('style', 'width: 133%;');
                panel_cont.setAttribute('class', 'testimonial-panel-cont view-4 shift-left');

            } else if (curr_panel_view_count == 2) {
                panel_cont.setAttribute('style', 'width: 151%;');
                panel_cont.setAttribute('class', 'testimonial-panel-cont view-3 shift-left-50');

            } else if (curr_panel_view_count == 1) {
                panel_cont.setAttribute('style', 'width: 200%;');
                panel_cont.setAttribute('class', 'testimonial-panel-cont view-2 shift-left-100');
            }

            panel = document.createElement('div');
            panel.setAttribute('class', 'testimonial-panel');

            elem = document.createElement('div');
            elem.setAttribute('class', 'cont');
            elem.innerHTML =
                `<i class="quote-icon fas fa-quote-left"></i>
                         <div id="tm-txt-cont-${scroll_index}" class="txt-cont collapse">
                             <p id="tm-txt-${scroll_index}" class="txt ux-fs-px-18">${clipped_text[0]}</p>
                             ${clipped_text[1] ? '<div class="expand-btn ux-fs-px-18 ux-txt-grayblue" toggle="0" onclick="displayFullTestimoney(this, ' + scroll_index + ')">Read More</div>' : ''}
                         </div>
                         <div class="pointer"></div>
                         <div class="profile-cont">
                             <div class="picture-cont">
                                 <img class="prof-pic ux-f-rd-corner" src="${testimonies[scroll_index].profile_picture}" alt="user's profile picture" />
                             </div>
                             <div class="name ux-txt-white ux-fs-px-20">${testimonies[scroll_index].name}</div>
                         </div>`;

            panel.appendChild(elem)
            panel_cont.insertBefore(panel, panels[0]);

            // scroll right
            if (curr_panel_view_count == 3) {
                panel_cont.setAttribute('class', 'testimonial-panel-cont view-4 shift-left scroll-left');

            } else if (curr_panel_view_count == 2) {
                panel_cont.setAttribute('class', 'testimonial-panel-cont view-3 shift-left-50 scroll-left-50');

            } else if (curr_panel_view_count == 1) {
                panel_cont.setAttribute('class', 'testimonial-panel-cont view-2 shift-left-100 scroll-left-100');
            }

            // wait for 0.5 second and remove the first element at left
            setTimeout(function () {
                panel_cont.removeChild(panels[panels.length - 1]);
                panel_cont.removeAttribute('style');

                if (curr_panel_view_count == 3) {
                    panel_cont.setAttribute('class', 'testimonial-panel-cont view-3');

                } else if (curr_panel_view_count == 2) {
                    panel_cont.setAttribute('class', 'testimonial-panel-cont view-2');

                } else if (curr_panel_view_count == 1) {
                    panel_cont.setAttribute('class', 'testimonial-panel-cont view-1');
                }

                slide_tm_wait = false

            }, 550);
        }
    };*/

    // display full testimoney panel
    /*window.displayFullTestimoneyPanel = function (index) {
        if (!window_active) {
            window_active = true;

            let panel = document.getElementById('testimonial-full-panel-cont');

            let elem = panel.querySelector('.prof-pic');
            elem.setAttribute('src', testimonies[index].profile_picture);

            elem = panel.querySelector('.user-name');
            elem.innerHTML = testimonies[index].name;

            elem = panel.querySelector('.txt');
            elem.innerHTML = testimonies[index].testimoney;

            // display the panel
            panel.removeAttribute('class');
        }
    };*/

    // change the cryptocurrencies' price to other exchange currency
    window.changeCryptoPriceTo = function (exchange_to, btn_id, btn) {
        if (!(curr_crypto_price_btn_id == btn_id)) {
            curr_crypto_price_btn_id = btn_id;

            // current active tab button
            let elem = document.querySelector('#crypto-st-table-cont .tab-btn.active');
            elem.setAttribute('class', 'tab-btn');

            // clicked tab button
            btn.setAttribute('class', 'tab-btn active');

            if (exchange_to == 'usd') {
                addTableRowsForCrptoPrices(cryptoprices, 'US_DOLLAR');
            } else if (exchange_to == 'eur') {
                addTableRowsForCrptoPrices(cryptoprices, 'EURO');
            } else { // gbp
                addTableRowsForCrptoPrices(cryptoprices, 'BRITISH_POUNDS');
            }
        }
    };

    // display mobile menu
    window.dropMobileMenu = function (btn) {
        let menu_bar = document.querySelector('.page-top-menu-cont');
        let menu_icon = btn.querySelector('.drop-menu-icon');
        let menu_elem = document.querySelector('.drop-down-mobi-menu-cont');

        if (btn.getAttribute('toggle') == '0') {
            menu_bar.setAttribute('class', 'page-top-menu-cont fixed theme-bg-color show');
            menu_icon.setAttribute('class', 'drop-menu-icon open');
            menu_elem.setAttribute('class', 'drop-down-mobi-menu-cont show theme-bg-color shadow');
            btn.setAttribute('toggle', '1');
            drop_mobi_menu_active = true;

        } else {
            menu_bar.setAttribute('class', 'page-top-menu-cont fixed theme-bg-color show shadow');
            menu_icon.setAttribute('class', 'drop-menu-icon close');
            menu_elem.setAttribute('class', 'drop-down-mobi-menu-cont theme-bg-color hide shadow');
            btn.setAttribute('toggle', '0');
            drop_mobi_menu_active = false;
            top_main_menu_changed = true;
            changeMainMenuOnScroll();
        }
    };

    /*function getTestimonialNextIndex(direction, panel_count) {
        let index;
        let tm_length = testimonies.length;

        if (direction == 'next') {
            index = curr_tm_panel_index + panel_count;

            if (curr_tm_panel_index == tm_length - 1) {
                curr_tm_panel_index = 0;
            } else {
                curr_tm_panel_index++;
            }

            if (index < tm_length) {
                return index;
            } else {
                return index - tm_length;
            }

        } else { // previous
            if (curr_tm_panel_index == 0) {
                curr_tm_panel_index = tm_length - 1;
            } else {
                curr_tm_panel_index--;
            }

            return curr_tm_panel_index;
        }
    }*/

    // clip unffitted text out
    function clipOutText(txt_length, text) {
        let clipped_text = text;
        let text_clipped = false;

        if (txt_length < text.length) {
            if (text[txt_length - 1] == ' ') {
                clipped_text = text.substring(0, txt_length - 1);

            } else { // iterate forward until you found whitespace
                for (let i = txt_length; i < text.length; i++) {
                    if (text[i] == ' ') {
                        clipped_text = text.substring(0, i);
                        break;
                    }
                }
            }

            clipped_text += '...';
            text_clipped = true;
        }

        return [clipped_text, text_clipped];
    }

    // add testimonies' panel to page
    /*function createTestimonialPanels(panel_count) {
        let elem = document.querySelector('.testimonial-panel-cont');
        let panel, cont, clipped_text;

        window.removeAllChildElement(elem); // remove already existing testimony if there is any
        elem.setAttribute('class', 'testimonial-panel-cont view-' + panel_count);

        for (let i = 0; i < panel_count; i++) {
            clipped_text = clipOutText(250, testimonies[i].testimoney);

            panel = document.createElement('div');
            panel.setAttribute('class', 'testimonial-panel');

            cont = document.createElement('div');
            cont.setAttribute('class', 'cont');
            cont.innerHTML =
                `<i class="quote-icon fas fa-quote-left"></i>
                         <div id="tm-txt-cont-${i}" class="txt-cont collapse">
                             <p id="tm-txt-${i}" class="txt ux-fs-px-18">${clipped_text[0]}</p>
                             ${clipped_text[1] ? '<div class="expand-btn ux-fs-px-18 ux-txt-grayblue" toggle="0" onclick="displayFullTestimoney(this, ' + i + ')">Read More</div>' : ''}
                         </div>
                         <div class="pointer"></div>
                         <div class="profile-cont">
                             <div class="picture-cont">
                                 <img class="prof-pic ux-f-rd-corner" src="${testimonies[i].profile_picture}" alt="user's profile picture" />
                             </div>
                             <div class="name ux-txt-white ux-fs-px-20">${testimonies[i].name}</div>
                         </div>`;

            panel.appendChild(cont);
            elem.appendChild(panel);
        }
    }*/

    // this function start testimonial slide animation
    /*function startTMSlideAnimation() {
        if (!tm_anim_running) { // if is not running start animation
            tm_anim_running = true;
            let elapsed_time;
            let time_duration = Date.now() + 6 * 1000;

            let step = () => {
                elapsed_time = Date.now();

                if (elapsed_time > time_duration) {
                    time_duration = Date.now() + 6 * 1000;
                    window.slideTestimonial('next');
                }

                req_tm_anim_handler = requestAnimationFrame(step);
            };

            step(); // start
        }
    }*/

    // this function stop testimonial slide animation
    /*function stopTMSlideAnimation() {
        if (tm_anim_running) { // if is running, stop animation
            cancelAnimationFrame(req_tm_anim_handler);
            tm_anim_running = false;
        }
    }*/

    // format cryptocurrency statistics data
    function formatCryptoStatistics(data, data_name) {
        switch (data_name) {
            case 'price':
            case 'supply':
                return window.seperateNumberBy(new Number(data).toFixed(2), ',');

            case 'last_update':
                return window.toSTDTimeString(new Date(data * 1000));

            case 'mkt_cap':
                if (data >= 1000000000) {
                    return new Number(data / 1000000000).toFixed(2) + ' B';

                } else if (data >= 1000000) {
                    return new Number(data / 1000000).toFixed(2) + ' M';

                } else {
                    return window.seperateNumberBy(new Number(data).toFixed(2), ',');
                }

            case 'change_pct_hour':
            case 'last_vol':
                return new Number(data).toFixed(4);

            default:
            // shouldn't be here
        }
    }

    // create table for cryptocurrencies' price
    function addTableRowsForCrptoPrices(crypto_prices, exchange_currency) {
        let obj_keys = [
            'crypto_name',
            'price',
            'last_update',
            'mkt_cap',
            'supply',
            'change_pct_hour',
            'last_vol'
        ];
        let crypto_price;
        let tbl_column_length = crypto_price_table_header.length;
        let tbl_dt;
        let table_elem = document.getElementById('crypto-st-tbl');

        window.removeAllChildElement(table_elem);

        // add table header
        let tbl_row = document.createElement('tr');

        for (let i = 0; i < tbl_column_length; i++) {
            tbl_dt = document.createElement('th');
            tbl_dt.innerHTML = crypto_price_table_header[i];
            tbl_row.appendChild(tbl_dt);
        }

        table_elem.appendChild(tbl_row);


        // add table's rows
        for (let j = 0; j < crypto_prices.length; j++) {
            if (exchange_currency == 'US_DOLLAR') {
                crypto_price = crypto_prices[j].usd;

            } else if (exchange_currency == 'EURO') { // EURO
                crypto_price = crypto_prices[j].eur;

            } else { // BRITISH_POUNDS
                crypto_price = crypto_prices[j].gbp;
            }

            tbl_row = document.createElement('tr');

            // columns
            for (let k = 0; k < tbl_column_length; k++) {
                tbl_dt = document.createElement('td');

                if (k == 0) { // crypto name
                    tbl_dt.innerHTML = crypto_prices[j][obj_keys[k]];

                } else {
                    tbl_dt.innerHTML = formatCryptoStatistics(crypto_price[obj_keys[k]], obj_keys[k]);
                }

                tbl_row.appendChild(tbl_dt);
            }

            table_elem.appendChild(tbl_row);
        }
    }

    // start image slider animation
    /*window.imageSlider(
        [
            './images/anim/0908Japan09.bmp',
            './images/anim/0908Japan13.bmp',
            './images/anim/0908Japan16.bmp'
        ],
        2, // animation time (in seconds)
        5, // wait for next animation time (in seconds)
        function (prev_indicator_index, curr_indicator_index) {
            // mark the indicator as image slide play
            var elem_1 = document.getElementById('img-ind-' + prev_indicator_index);
            var elem_2 = document.getElementById('img-ind-' + curr_indicator_index);

            elem_1.setAttribute('class', 'ux-bg-white ux-f-rd-corner');
            elem_2.setAttribute('class', 'ux-bg-grayblue bg-hover ux-f-rd-corner');
        }
    );*/

    // show and hide main top menu on scroll
    function changeMainMenuOnScroll() {
        // check if drop mobile menu is active
        if (drop_mobi_menu_active) {
            return;
        }

        // get top menu container
        let elem = document.querySelector('.page-top-menu-cont');

        curr_page_scroll_y = getPageScrollTop();

        if (curr_page_scroll_y < 70) {
            if (top_main_menu_changed) {
                elem.setAttribute('class', 'page-top-menu-cont absolute');
                top_main_menu_changed = false;
            }

        } else {
            top_main_menu_changed = true;

            if (curr_page_scroll_y - page_scroll_y > 0) { //check if page is scroll down
                // check if menu has not been hidden
                if (!top_main_menu_hidden) {
                    // hide menu
                    elem.setAttribute('class', 'page-top-menu-cont theme-bg-color fixed hide shadow');
                    top_main_menu_hidden = true;
                }

            } else { // page is scroll up
                // check if menu has not been shown
                if (top_main_menu_hidden) {
                    // hide menu
                    elem.setAttribute('class', 'page-top-menu-cont theme-bg-color fixed show shadow');
                    top_main_menu_hidden = false;
                }
            }
        }

        // set page current y-position
        page_scroll_y = curr_page_scroll_y;
    }

    // utility function that update cryptocurrency statistics at every interval
    function requestForCryptoStatisticsUpdate() {
        //checks if the worker does not exists
        if (typeof update_crypto_statistics_worker == "undefined") {
            update_crypto_statistics_worker = new Worker("js/updateCryptoStatisticsWorker.js");

            //listen to when data is sent
            update_crypto_statistics_worker.addEventListener("message", function (event) {
                if (event.data.length > 0) {
                    cryptoprices = event.data;
                    if (is_crypto_stat_loading) {
                        is_crypto_stat_loading = false;
                        document.querySelector('.crypto-statistics-loading-cont').setAttribute('class', 'crypto-statistics-loading-cont remove-elem');
                        document.querySelector('.crypto-statistics-section').setAttribute('class', 'crypto-statistics-section page-cont-max-width');
                    }
                    
                    addTableRowsForCrptoPrices(cryptoprices, exchange_currencies[curr_crypto_price_btn_id]);
                }
            });
        }
    }

    // utility function that fetch users' testimoney from server
    /*function requestForCustomerTestimonial() {
        let req_url = 'request';
        let form_data = 'req=get_user_testimonial'; // request query

        // send request to server
        window.ajaxRequest(
            req_url,
            form_data,
            { contentType: "application/x-www-form-urlencoded" },

            // listen to response from the server
            function (response) {
                let response_data = JSON.parse(response);
                testimonies = response_data.testimonies;
                testimonies_ready = true;

                adaptPageLayout();
            },

            // listen to server error
            function (err_status) {
                //check if is a timeout or server busy
                if (error_status == 408 ||
                    error_status == 504 ||
                    error_status == 503) {

                    //send the request again
                    requestForCustomerTestimonial();
                }
            }
        );
    }*/

    // utility function to get touch index in array
    function getTouchIndexByID(touches, id) {
        for (let i = 0; i < touches.length; i++) {
            if (touches[i].identifier == id) {
                return i;
            }
        }

        return -1; // touch not found
    }

    // utility function to listen for touch event
    function swipeGestures(touch_target, swipe_direction, callback) {
        let active_touches = [];
        let is_touch_start_fired = false;

        // attach touch start event handler
        touch_target.addEventListener("touchstart", (e) => {
            let touches = e.changedTouches;

            // finger(s) placed on touch target
            if (!is_touch_start_fired) {
                is_touch_start_fired = true;
                callback({
                    state: SWIPE_START,
                    direction: null
                });
            }

            // we track only one finger in the array
            for (let i = 0; i < touches.length; i++) {
                active_touches.push(touches[i]);
            }

        }, false);

        // attach touch move event handler
        touch_target.addEventListener("touchend", (e) => {
            let touches = e.changedTouches;

            // check if is only one finger is on touch target
            if (active_touches.length <= 1) {
                // distance move by the finger
                let dx = active_touches[0].pageX - touches[0].pageX; // x-direction
                let dy = active_touches[0].pageY - touches[0].pageY; // y-direction
                let rad = Math.atan(dy / dx);

                // check if distance is above our set threshold and
                // swiping happen closely in horizontal direction; between 20 degree from horizontal
                if (Math.abs(dx) > 100 && Math.abs(rad) < 0.3490658503988659) {
                    callback({
                        state: SWIPE_REMOVED,
                        direction: null
                    });
                    callback({
                        state: SWIPE_END,
                        direction: dx > 0 ? SWIPE_RIGHT : SWIPE_LEFT
                    });

                } else {
                    callback({
                        state: SWIPE_REMOVED,
                        direction: null
                    });
                }

                // remove the touch from the array
                active_touches = [];

            } else { // find the finger and remove it from the array
                for (let i = 0; i < touches.length; i++) {
                    active_touches.splice(getTouchIndexByID(active_touches, touches[i].identifier), 1);
                }
            }

        }, false);
    }

    // slide package to clicked view index
    window.slidePackageList = function (view_index) {
        if (view_index == current_inv_pkg_swipe_index) {
            return;
        }

        let elem = document.querySelector('.inv-pkg-list-cont');
        elem.setAttribute("class", "inv-pkg-list-cont ux-layout-grid columns-4 slide-" + (view_index + 1));

        // determine slide direction
        if (current_inv_pkg_swipe_index < view_index) { // right direction
            current_inv_pkg_swipe_index = view_index;

            // slide indicator
            elem = document.getElementById("inv-pkg-slide-indicator-cont");
            elem.setAttribute("class", "set-" + current_inv_pkg_swipe_index);
            setTimeout(() => {
                elem.setAttribute("class", "set-" + current_inv_pkg_swipe_index  + " dot-" + (view_index + 1));
            }, 100);

        } else { // left direction
            current_inv_pkg_swipe_index = view_index + 1;

            // slide indicator
            elem = document.getElementById("inv-pkg-slide-indicator-cont");
            elem.setAttribute("class", "set-" + current_inv_pkg_swipe_index);
            setTimeout(() => {
                elem.setAttribute("class", "set-" + current_inv_pkg_swipe_index  + " dot-" + current_inv_pkg_swipe_index);
                current_inv_pkg_swipe_index -= 1;
            }, 100);
        }
    };

    // process events for form input
    function processInputEvents(e) {
        let input_elem = e.target; // get element that fire the event

        switch (e.type) {
            case "keydown":
                // remove error border line
                input_elem.removeAttribute("style");

                break;

            default:
                // you shoudn't be here
        }
    }

    // attach event listener to input or select element
    function attachEventsToInputs(input_elements) {
        for (let i = 0; i < input_elements.length; i++) {
            input_elements[i].addEventListener("keydown", processInputEvents, false);
        }
    }

    // validate contact us form
    function vallidateContactUsFormInput(inputs) {
        let email_exp = pattern = /^(([^<>()\[\]\\.,;:\s@"]+(\.[^<>()\[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;

        if (!/^[a-zA-Z]+([ ]{1}[a-zA-Z]+)*$/.test(inputs["name"].value)) {
            // mark the input with invalid value
            inputs["name"].setAttribute("style", "border: 1px solid red;");

            // scroll page to input that has the error
            inputs["name"].scrollIntoView({behavior: "smooth"});
            
            return false;
        }

        if (!email_exp.test(inputs["email"].value)) {
            // mark the input with invalid value
            inputs["email"].setAttribute("style", "border: 1px solid red;");

            // scroll page to input that has the error
            inputs["email"].scrollIntoView({behavior: "smooth"});
            
            return false;
        }

        if (!/^.+$/.test(inputs["message"].value.trim())) {
            // mark the input with invalid value
            inputs["message"].setAttribute("style", "border: 1px solid red;");

            // scroll page to input that has the error
            inputs["message"].scrollIntoView({behavior: "smooth"});
            
            return false;
        }

        return true;
    }

    // open the contact us message dialog
    function openMessageDialog(message_title, message) {
        let msg_bg_cover = document.getElementById("pop-up-message-bg-cover");
        let msg_dialog = document.getElementById("contact-us-pop-up-message");

        // set message title
        let elem = msg_dialog.querySelector('.message-title');
        elem.innerHTML = message_title;

        // set message body
        elem = msg_dialog.querySelector('.message-body');
        elem.innerHTML = message;

        // show message dialog
        msg_dialog.removeAttribute("class");
        msg_bg_cover.removeAttribute("class");
    }

    // close opened window or dialog
    window.closePopMessage = function (win_id, modal = false) {
        // close the window
        let elem = document.getElementById(win_id);
        elem.setAttribute("class", "remove-elem");

        if (modal) {
            // remove background cover
            elem = document.getElementById("pop-up-message-bg-cover");
            elem.setAttribute("class","remove-elem");
        }
    };

    // send message to us
    window.processSendUsMessageForm = function (e) {
        e.preventDefault();

        // get contact us form's input
        let form_inputs = contact_us_form.elements;

        // get submit button
        let submit_btn = document.getElementById("contact-us-submit-btn");

        // check if form's input contain valid value
        if (!vallidateContactUsFormInput(form_inputs)) {
            return;
        }

        let url = 'contact_us';
        let form = new FormData(contact_us_form);

        // disable form's input
        form_inputs["name"].disabled = true;
        form_inputs["email"].disabled = true;
        form_inputs["message"].disabled = true;
        submit_btn.disabled = true;

        // send request to server
        window.ajaxRequest(
            url,
            form,
            { contentType: false },

            // listen to response from the server
            function (response) {
                // enable form's input
                form_inputs["name"].disabled = false;
                form_inputs["email"].disabled = false;
                form_inputs["message"].disabled = false;
                submit_btn.disabled = false;

                response_data = JSON.parse(response);

                // check if registeration was succesfull
                if (response_data.success) {
                    form_inputs["name"].value = "";
                    form_inputs["email"].value = "";
                    form_inputs["message"].value = "";

                    // show succes message to user
                    openMessageDialog(
                        "Success!",
                        "Your message is successfully sent. Our agent will contact you as soon as possible."
                    );

                } else {
                    // send error message to user
                    openMessageDialog(
                        "Oops! Error occured",
                        "We are sorry! Your message couldn't get through, try again later."
                    );
                }
            },

            // listen to server error
            function (err_status) {
                // enable form's input
                form_inputs["name"].disabled = false;
                form_inputs["email"].disabled = false;
                form_inputs["message"].disabled = false;
                submit_btn.disabled = false;

                // send error message to user
                openMessageDialog(
                    "Oops! Error occured",
                    "An error occured. Please check your connection and try again later."
                );
            }
        );
    };

    // utility function that change page layout on page resize
    function adaptPageLayout() {
        let win_width_size = window.innerWidth;

        if (win_width_size < 800) { // mobile view
            active_view = MOBILE_VIEW;

            // change testimonial view
            /*if (testimonies_ready) {
                curr_panel_view_count = 1;
                // testimonies varible must contain one testimoney
                if (testimonies.length == 0) {
                    // hide the testimoney panel
                    document.querySelector('.testimoney-section-cont').setAttribute('class', 'testimoney-section-cont remove-elem');
                    return;

                } else {
                    // show the testimoney panel
                    document.querySelector('.testimoney-section-cont').setAttribute('class', 'testimoney-section-cont');

                    stopTMSlideAnimation();
                    createTestimonialPanels(curr_panel_view_count);

                    if (testimonies.length > 1) {
                        startTMSlideAnimation();
                    }
                }
            }*/

        } else if (win_width_size < 1200) { // tablet view
            active_view = TABLET_VIEW;

            // change testimonial view
            /*if (testimonies_ready) {
                curr_panel_view_count = 2;
                // testimonies varible must contain atleast two testimoney
                if (testimonies.length < 2) {
                    // hide the testimoney panel
                    document.querySelector('.testimoney-section-cont').setAttribute('class', 'testimoney-section-cont remove-elem');
                    return;

                } else {
                    // show the testimoney panel
                    document.querySelector('.testimoney-section-cont').setAttribute('class', 'testimoney-section-cont');

                    stopTMSlideAnimation();
                    createTestimonialPanels(curr_panel_view_count);

                    if (testimonies.length > 2) {
                        startTMSlideAnimation();
                    }
                }
            }*/

        } else { // desktop view
            active_view = DESKTOP_VIEW;

            // check if drop down mobile menu is active
            if (drop_mobi_menu_active) {
                dropMobileMenu(document.querySelector('.drop-menu-icon-cont'));
            }

            /*if (testimonies_ready) {
                curr_panel_view_count = 3;
                // testimonies varible must contain atleast three testimoney
                if (testimonies.length < 3) {
                    // hide the testimoney panel
                    document.querySelector('.testimoney-section-cont').setAttribute('class', 'testimoney-section-cont remove-elem');
                    return;

                } else {
                    // show the testimoney panel
                    document.querySelector('.testimoney-section-cont').setAttribute('class', 'testimoney-section-cont');

                    stopTMSlideAnimation();
                    createTestimonialPanels(curr_panel_view_count);

                    if (testimonies.length > 3) {
                        startTMSlideAnimation();
                    }
                }
            }*/
        }
    }

    // attach event to contact us form's input
    let form_inputs = contact_us_form.elements;
    attachEventsToInputs([
        form_inputs["name"],
        form_inputs["email"],
        form_inputs["message"]
    ]);

    // call after page is loaded
    adaptPageLayout();
    //requestForCryptoStatisticsUpdate();
    changeMainMenuOnScroll();
    //requestForCustomerTestimonial();

    //listen to page scroll event
    window.onscroll = function (e) {
        changeMainMenuOnScroll();
    };

    // listen for page resize
    window.addEventListener("resize", function (e) {
        adaptPageLayout();

    }, false);
}

//initialise the script
if (window.attachEvent) {
    window.attachEvent("onload", init);

} else {
    window.addEventListener("load", init, false);
}