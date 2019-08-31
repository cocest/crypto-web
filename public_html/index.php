<!DOCTYPE html>
<html lang="en-US">

<head>
    <meta charset="UTF-8">
    <title>CrytoWeb - Homepage</title>
    <link rel="icon" type="image/png" href="favicon1.png" sizes="16x16">
    <link rel="icon" type="image/png" href="favicon2.png" sizes="32x32">
    <link rel="icon" type="image/png" href="favicon3.png" sizes="120x120">
    <meta name="description" content="PrimeDesk login page">
    <meta name="keywords" content="sign in, login">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">
    <link type="text/css" href="./fonts/css/all.min.css" rel="stylesheet">
    <link type="text/css" rel="stylesheet" href="./styles/UdaraX.css">
    <link type="text/css" rel="stylesheet" href="./styles/homepage.css">
    <script type="text/javascript" src="./scripts/utils.js"></script>
    <script type="text/javascript" src="./scripts/slider.js"></script>
    <script type="text/javascript">
        function init() {
            // define and initialise variables
            let top_main_menu_hidden = false;
            let top_main_menu_changed = false;
            let page_scroll_y = 0;
            let curr_page_scroll_y = 0;
            let window_active = false;
            let curr_tm_panel_index = 0;
            let slide_tm_wait = false;
            let curr_crypto_price_btn_id = 0;
            let update_crypto_statistics_worker;
            let crypto_price_table_header = [
                'NAME', 'PRICE', 'LAST UPDATE', 'MARKET CAP', 'SUPPLY', 'CHANGE PCT', 'LAST VOLUME'
            ];

            // testimonial data list
            let testimonies = [
                {
                    name: 'Attamah Celestine',
                    profile_picture: './images/background/mimi-thian--VHQ0cw2euA-unsplash.jpg',
                    testimoney: 'Hootsuite, which offers a social media dashboard, buckets their testimonials based on goals. For example, in one blade, they show testimonials from companies who.'
                },
                {
                    name: 'Ugwumah Chigozie',
                    profile_picture: './images/background/mimi-thian--VHQ0cw2euA-unsplash.jpg',
                    testimoney: 'Hootsuite, which offers a social media dashboard, buckets their testimonials based on goals. For example, in one blade.'
                },
                {
                    name: 'Geofrey Christian',
                    profile_picture: './images/background/mimi-thian--VHQ0cw2euA-unsplash.jpg',
                    testimoney: 'Hootsuite, which offers a social media dashboard, buckets their testimonials based on goals. For example, in one blade, they show testimonials from companies who have used social media to build their brand. which offers a social media dashboard, buckets their testimonials based on goals. Testimonials based on goals. For example, in one blade, they show testimonials'
                },
                {
                    name: 'Morgan Freeman',
                    profile_picture: './images/background/mimi-thian--VHQ0cw2euA-unsplash.jpg',
                    testimoney: 'Hootsuite, which offers a social media dashboard, buckets their testimonials based on goals. For example, in one blade, they show testimonials from companies who.'
                }
            ];

            // cryptocurrencies' price
            let cryptoprices = [
                {
                    crypto_name: 'Bitcoin',
                    crpto_symbol: 'BTC',
                    usd: {
                        price: 9459.19,
                        last_update: 1567080149,
                        mkt_cap: 169351898725.75,
                        supply: 17903425,
                        change_pct_hour: -0.032550328832298456,
                        last_vol: 0.0027
                    },
                    eur: {
                        price: 8556.79,
                        last_update: 1567080157,
                        mkt_cap: 153195848005.75003,
                        supply: 17903425,
                        change_pct_hour: -0.032550328832298456,
                        last_vol: 0.06690278
                    }
                },
                {
                    crypto_name: 'Ethereum',
                    crpto_symbol: 'ETH',
                    usd: {
                        price: 168.72,
                        last_update: 1567080146,
                        mkt_cap: 18140773419.14628,
                        supply: 107519994.1865,
                        change_pct_hour: -0.09474182851728836,
                        last_vol: 0.20135509
                    },
                    eur: {
                        price: 152.57,
                        last_update: 1567080158,
                        mkt_cap: 16404325513.034303,
                        supply: 107519994.1865,
                        change_pct_hour: -0.10476003404700884,
                        last_vol: 9
                    }
                },
                {
                    crypto_name: 'Litecoin',
                    crpto_symbol: 'LTC',
                    usd: {
                        price: 67.62,
                        last_update: 1567085874,
                        mkt_cap: 721141421.2462507,
                        supply: 9005262.50307506,
                        change_pct_hour: 0.15007503751876508,
                        last_vol: 0.35325336
                    },
                    eur: {
                        price: 72.41,
                        last_update: 1567085170,
                        mkt_cap: 652071057.8476651,
                        supply: 9005262.50307506,
                        change_pct_hour: 0.2908587257617642,
                        last_vol: 3.5
                    }
                },
                {
                    crypto_name: 'Dash',
                    crpto_symbol: 'DASH',
                    usd: {
                        price: 80.08,
                        last_update: 1567085874,
                        mkt_cap: 721141421.2462507,
                        supply: 9005262.50307506,
                        change_pct_hour: 0.15007503751876508,
                        last_vol: 0.35325336
                    },
                    eur: {
                        price: 72.41,
                        last_update: 1567085170,
                        mkt_cap: 652071057.8476651,
                        supply: 9005262.50307506,
                        change_pct_hour: 0.2908587257617642,
                        last_vol: 3.5
                    }
                },
                {
                    crypto_name: 'Monero',
                    crpto_symbol: 'XMR',
                    usd: {
                        price: 70.28,
                        last_update: 1567085886,
                        mkt_cap: 1207434119.038162,
                        supply: 17180337.4934286,
                        change_pct_hour: 0.2138884927990955,
                        last_vol: 0.1
                    },
                    eur: {
                        price: 63.27,
                        last_update: 1567085775,
                        mkt_cap: 1086999953.2092275,
                        supply: 17180337.4934286,
                        change_pct_hour: 0.20589166930630748,
                        last_vol: 0.27835534
                    }
                },
                {
                    crypto_name: 'ZCash',
                    crptosymbol: 'ZEC',
                    usd: {
                        price: 45.23,
                        last_update: 1567085861,
                        mkt_cap: 330038504.3125,
                        supply: 7296893.75,
                        change_pct_hour: -0.08835873647008228,
                        last_vol: 2.076651
                    },
                    eur: {
                        price: 40.9,
                        last_update: 1567085818,
                        mkt_cap: 298442954.375,
                        supply: 7296893.75,
                        change_pct_hour: -0.21956574774336038,
                        last_vol: 0.44052
                    }
                }
            ];

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
            window.slideTestimonial = function (direction) {
                // wait for the animation to finish
                if (slide_tm_wait) {
                    return;
                } else {
                    slide_tm_wait = true; // wait
                }

                let panel_cont, panels, panel, elem, scroll_index, clipped_text;

                if (direction == 'next') {
                    scroll_index = getTestimonialNextIndex('next', 3);
                    clipped_text = clipOutText(250, testimonies[scroll_index].testimoney);

                    panel_cont = document.querySelector('.testimonial-panel-cont');
                    panels = panel_cont.getElementsByClassName('testimonial-panel');

                    panel_cont.setAttribute('style', 'width: 133%;');

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
                    panel_cont.setAttribute('class', 'testimonial-panel-cont scroll-right');

                    // wait for 0.5 second and remove the first element at left
                    setTimeout(function () {
                        panel_cont.removeChild(panels[0]);
                        panel_cont.removeAttribute('style');
                        panel_cont.setAttribute('class', 'testimonial-panel-cont');

                        slide_tm_wait = false

                    }, 550);

                } else { // previous
                    scroll_index = getTestimonialNextIndex('prev', 3);
                    clipped_text = clipOutText(250, testimonies[scroll_index].testimoney);

                    panel_cont = document.querySelector('.testimonial-panel-cont');
                    panels = panel_cont.getElementsByClassName('testimonial-panel');

                    panel_cont.setAttribute('style', 'width: 133%;');
                    panel_cont.setAttribute('class', 'testimonial-panel-cont shift-left');

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
                    panel_cont.setAttribute('class', 'testimonial-panel-cont shift-left scroll-left');

                    // wait for 0.5 second and remove the first element at left
                    setTimeout(function () {
                        panel_cont.removeChild(panels[panels.length - 1]);
                        panel_cont.removeAttribute('style');
                        panel_cont.setAttribute('class', 'testimonial-panel-cont');

                        slide_tm_wait = false

                    }, 550);
                }
            };

            // display full testimoney panel
            window.displayFullTestimoneyPanel = function (index) {
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
            };

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
                    } else {
                        addTableRowsForCrptoPrices(cryptoprices, 'EURO');
                    }
                }
            };

            function getTestimonialNextIndex(direction, panel_count) {
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
            }

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
            function createTestimonialPanels() {
                let elem = document.querySelector('.testimonial-panel-cont');
                let panel, cont, clipped_text;

                for (let i = 0; i < 3; i++) {
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
            }

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

                    } else { // EURO
                        crypto_price = crypto_prices[j].eur;
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
                            elem.setAttribute('class', 'page-top-menu-cont fixed hide ux-bg-grayblue shadow');
                            top_main_menu_hidden = true;
                        }

                    } else { // page is scroll up
                        // check if menu has not been shown
                        if (top_main_menu_hidden) {
                            // hide menu
                            elem.setAttribute('class', 'page-top-menu-cont fixed show ux-bg-grayblue shadow');
                            top_main_menu_hidden = false;
                        }
                    }
                }

                // set page current y-position
                page_scroll_y = curr_page_scroll_y;
            }

            //listen to page scroll event
            window.onscroll = function (e) {
                changeMainMenuOnScroll();
            };

            // utility function that execute function in loop
            function requestForCryptoStatisticsUpdate() {
                //checks if the worker does not exists
                if (typeof (update_crypto_statistics_worker) === "undefined") {
                    update_crypto_statistics_worker = new Worker("updateCryptoStatisticsWorker.js");

                    //listen to when data is sent
                    update_crypto_statistics_worker.addEventListener("message", function (event) {
                        cryptoprices = event.data;
                        addTableRowsForCrptoPrices(cryptoprices, curr_crypto_price_btn_id == 0 ? 'US_DOLLAR' : 'EURO');
                    });
                }
            }

            // utility function that fetch users' testimoney from server
            function requestForCustomerTestimonial() {
                let req_url = 'process_request.php';
                let form_data = 'request=GET_CUSTOMER_TESTIMONIAL'; // request query

                // send request to server
                window.ajaxRequest(
                    req_url,
                    form_data,

                    // listen to response from the server
                    function (response) {
                        response_data = JSON.parse(response);
                        testimonies = response.testimonies;
                        createTestimonialPanels();
                    },

                    // listen to server error
                    function (err_status) {
                        //check if is a timeout or server busy
                        if (error_status === 408 ||
                            error_status === 504 ||
                            error_status === 503) {

                            //send the request again
                            requestForCustomerTestimonial();
                        }
                    }
                );
            }

            // call after page is loaded
            addTableRowsForCrptoPrices(cryptoprices, 'US_DOLLAR'); // remove this later
            //requestForCryptoStatisticsUpdate();
            createTestimonialPanels();
            //requestForCustomerTestimonial();
        }

        //initialise the script
        if (window.attachEvent) {
            window.attachEvent("onload", init);

        } else {
            window.addEventListener("load", init, false);
        }
    </script>
</head>

<body>
    <div class="page-top-menu-cont absolute">
        <!--Header menu container-->
        <div class="page-cont-max-width">
            <nav>
                <div class="site-logo-cont">
                    <a href="./index.html">
                        <img class="site" src="./images/logo/img1.png" alt="Site Logo" />
                    </a>
                </div>
                <div class="main-menu-cont">
                    <ul class="ux-hr-menu fmt-link-med ux-txt-align-rt">
                        <li><a class="link ux-txt-smokewhite txt-hover" href="#">Home</a></li>
                        <li><a class="link ux-txt-smokewhite txt-hover" href="#">Investment Packages</a></li>
                        <li><a class="link ux-txt-smokewhite txt-hover" href="#">Testimoney</a></li>
                        <li><a class="link ux-txt-smokewhite txt-hover" href="#">About Us</a></li>
                        <li><a class="link ux-txt-smokewhite txt-hover" href="#">Contact Us</a></li>
                        <li><a class="link ux-txt-smokewhite txt-hover" href="#">FAQ</a></li>
                        <li><a class="ux-btn ux-bg-chocolate bg-hover ux-txt-white ux-rd-corner-1" href="#">Get
                                Started</a></li>
                        <li><a class="link ux-txt-smokewhite txt-hover" href="#">Sign In</a></li>
                    </ul>
                </div>
            </nav>
        </div>
    </div>
    <!--page upper section-->
    <div class="page-upper-section">
        <div id="imageslider-cont"></div>
        <div class="headline-left-bg-img"></div>
        <!--headline container-->
        <div id="headline-cont" class="page-cont-max-width">
            <div class="headline">
                <h1>
                    <span class="ux-txt-white">The Crypto Currency </span>
                    </br>
                    <span class="ux-txt-chocolate ux-fs-px-19">You Ever Wanted</span>
                </h1>
            </div>
            <div class="sub-headline">
                <h2 style="line-height: 28px;">
                    <span class="ux-txt-white ux-fs-px-15 sub-headline-txt">
                        CRYENGINE makes the learning curve less steep with Full Source Code.
                        Clear tutorials, detailed documentation, and a strong development community.
                    </span>
                </h2>
            </div>
            <div class="action-btn-cont">
                <a class="ux-btn custom-action-btn ux-bg-chocolate bg-hover ux-txt-white ux-rd-corner-1 shadow"
                    href="#">Create Account</a>
            </div>
        </div>
        <!--animated image indicator-->
        <div id="anim-img-indicator">
            <div id="img-ind-0" class="ux-bg-grayblue bg-hover ux-f-rd-corner" onclick="gotoImageSlider(0)">
            </div>
            <div id="img-ind-1" class="ux-bg-white ux-f-rd-corner" onclick="gotoImageSlider(1)"></div>
            <div id="img-ind-2" class="ux-bg-white ux-f-rd-corner" onclick="gotoImageSlider(2)"></div>
        </div>
    </div>

    <!--why choose us section-->
    <div class="package-section page-cont-max-width">
        <div class="package-headline ux-txt-align-ct">
            <h1 class="ux-txt-grayblue">Why Choose Us</h1>
            <p class="descr-txt ux-fs-px-20">
                You have come to the right place because we have the services you always want. It is our
                obligation to provide unparalleled services for your success. We are working 24 hours to
                improve our service to suit your preference at best.
            </p>
        </div>
        <div class="package-panel-cont">
            <div class="package-panel">
                <div class="featured-art-cont">
                    <i class="fas fa-shield-alt ux-txt-grayblue"></i>
                </div>
                <div class="content">
                    <h3>Secure & Reliable</h3>
                    <p class="ux-fs-px-16">
                        Once you begin altering your site, you can easily check conversions by running Google
                        Analytics reports. More conversions must mean something’s working.
                    </p>
                </div>
            </div>
            <div class="package-panel">
                <div class="featured-art-cont">
                    <i class="fas fa-box-open ux-txt-grayblue"></i>
                </div>
                <div class="content">
                    <h3>10% bonus on referal</h3>
                    <p class="ux-fs-px-16">
                        Marketers seeking to dominate their respective niches should be focused on the best website
                        layouts for maximum UX and conversions.
                    </p>
                </div>
            </div>
            <div class="package-panel">
                <div class="featured-art-cont">
                    <i class="far fa-question-circle ux-txt-grayblue"></i>
                </div>
                <div class="content">
                    <h3>You already know</h3>
                    <p class="ux-fs-px-16">
                        If you want to infuse these best practices into your own website layout, here are a few steps to
                        follow.
                    </p>
                </div>
            </div>
        </div>
    </div>

    <!--Our service section-->
    <div class="service-section-cont">
        <div class="service-section page-cont-max-width">
            <div class="service-headline ux-txt-align-ct">
                <h1 class="ux-txt-grayblue">Our Services</h1>
            </div>
            <div class="service-panel-cont">
                <div class="service-panel">
                    <div class="cont">
                        <div class="featured-art-cont">
                            <i class="fas fa-chart-area ux-txt-grayblue"></i>
                        </div>
                        <h3 class="title ux-txt-grayblue">Cryptotrading</h3>
                        <p class="txt ux-fs-px-16">
                            It’s said that 94% of first-impressions are design driven. Not only that, but it takes less
                            than half a second for a visitor to form an opinion of your site.
                        </p>
                    </div>
                </div>
                <div class="service-panel">
                    <div class="cont">
                        <div class="featured-art-cont">
                            <i class="fas fa-chart-pie ux-txt-grayblue"></i>
                        </div>
                        <h3 class="title ux-txt-grayblue">Equities and Hedge Funds management</h3>
                        <p class="txt ux-fs-px-16">
                            It’s said that 94% of first-impressions are design driven. Not only that, but it takes less
                            than half a second for a visitor to form an opinion of your site.
                        </p>
                    </div>
                </div>
                <div class="service-panel">
                    <div class="cont">
                        <div class="featured-art-cont">
                            <i class="fas fa-cannabis ux-txt-grayblue"></i>
                        </div>
                        <h3 class="title ux-txt-grayblue">Cannabis Stocks Trading</h3>
                        <p class="txt ux-fs-px-16">
                            It’s said that 94% of first-impressions are design driven. Not only that, but it takes less
                            than half a second for a visitor to form an opinion of your site.
                        </p>
                    </div>
                </div>
                <div class="service-panel">
                    <div class="cont">
                        <div class="featured-art-cont">
                            <i class="fas fa-hands-helping ux-txt-grayblue"></i>
                        </div>
                        <h3 class="title ux-txt-grayblue">OTC brokerage</h3>
                        <p class="txt">
                            It’s said that 94% of first-impressions are design driven. Not only that, but it takes less
                            than half a second for a visitor to form an opinion of your site.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!--About cryptocurrency section-->
    <div class="about-crypto-section-cont">
        <div class="about-crypto-section page-cont-max-width">
            <div class="about-crypto-headline-cont">
                <h1 class="ux-txt-white">About WebsiteName</h1>
                <!--<div class="about-crypto-headline-underline ux-bg-grayblue"></div>-->
            </div>
            <div class="about-crypto-img-cont">
                <img src="./images/background/about-us.jpg" alt="About cryptocurrency image" />
            </div>
            <div class="about-crypto-cont">
                <p>
                    Cryptopro investment is a financial group that help it client invest their cryptocurrency
                    to make profitable ROI in accordance with the investment package the client subscribe to.
                    We ... to ratify our client and help them on their journey to financial freedom while
                    cryptocurrency are digital asset ... (digital money). They are bit of valuable information
                    which can be transfered among users on a blockchain network. There are thousands of
                    cryptocurrency with different features and bitcoin is the oldest, which was introduced by
                    Satophi Nakemoto in 2008 and it is the most widely accepted crytocurrency.
                </p>
            </div>
        </div>
    </div>

    <!--About Us section-->
    <!--<div class="about-us-section page-cont-max-width">
        <div class="about-us-headline-cont">
            <h1 class="ux-txt-grayblue">About Us</h1>
            <div class="about-us-headline-underline ux-bg-grayblue"></div>
        </div>
        <div class="about-us-img-cont">
            <img src="./images/background/computer-3368242_640.jpg" alt="About us image" />
        </div>
        <div class="about-us-cont">
            <p>
                Cryptopro investment is a financial group that help it client invest their cryptocurrency
                to make profitable ROI in accordance with the investment package the client subscribe to.
                We ... to ratify our client and help them on their journey to financial freedom.
            </p>
        </div>
    </div>-->

    <!--Cryptocurrencies' price and trade statistics-->
    <div class="crypto-statistics-section-cont">
        <div class="crypto-statistics-section page-cont-max-width">
            <div class="title-cont">
                <h1 class="ux-txt-grayblue">Cryptocurrency Live Price</h1>
            </div>
            <div id="crypto-st-table-cont">
                <div class="tab-btn-cont">
                    <div class="tab-btn active" onclick="changeCryptoPriceTo('usd', 0, this)">DOLLAR</div>
                    <div class="tab-btn" onclick="changeCryptoPriceTo('eur', 1, this)">EURO</div>
                </div>
                <div class="table-wrapper">
                    <table id="crypto-st-tbl"></table>
                </div>
            </div>
        </div>
    </div>

    <!--User Testimonial section-->
    <div class="testimoney-section-cont">
        <div class="testimoney-section page-cont-max-width">
            <div class="tm-scroll-left-btn ux-f-rd-corner shadow anim-btn" onclick="slideTestimonial('prev')"><i
                    class="fas fa-angle-left"></i></div>
            <div class="tm-scroll-right-btn ux-f-rd-corner shadow anim-btn" onclick="slideTestimonial('next')"><i
                    class="fas fa-angle-right"></i></div>
            <div class="testimonial-descr-cont">
                <h1 class="ux-txt-grayblue">Customers' Testimony</h1>
            </div>
            <div class="testimonial-panel-cont"></div>
            <div id="testimonial-full-panel-cont" class="remove-elem">
                <div class="win-close-btn" onclick="closeWindowPanel('testimonial-full-panel-cont')">
                    <i class="fas fa-window-close"></i>
                </div>
                <div>
                    <p class="testimony ux-fs-px-18">
                        <img class="prof-pic ux-f-rd-corner shadow" alt="user's profile picture" />
                        <span class="user-name ux-txt-grayblue"></span>
                        <span class="txt"></span>
                    </p>
                </div>
            </div>
        </div>
    </div>

    <!--Achievement section-->
    <div class="achievement-section-cont">
        <div class="achievement-section page-cont-max-width">
            <div class="records-cont">
                <div class="grid-item">
                    <div class="upper-sec">
                        <span class="icon">
                            <i class="fab fa-bitcoin"></i>
                        </span>
                        <span class="figure">627,403+</span>
                    </div>
                    <div class="lower-sec">
                        <h4>Cryptocurrency Traded</h4>
                    </div>
                </div>
                <div class="grid-item">
                    <div class="upper-sec">
                        <span class="icon">
                            <i class="far fa-question-circle"></i>
                        </span>
                        <span class="figure">3,421+</span>
                    </div>
                    <div class="lower-sec">
                        <h4>Something Here</h4>
                    </div>
                </div>
                <div class="grid-item">
                    <div class="upper-sec">
                        <span class="icon">
                            <i class="fas fa-users"></i>
                        </span>
                        <span class="figure">12,740+</span>
                    </div>
                    <div class="lower-sec">
                        <h4>Registered Users</h4>
                    </div>
                </div>
                <div class="grid-item">
                    <div class="upper-sec">
                        <span class="icon">
                            <i class="far fa-smile"></i>
                        </span>
                        <span class="figure">8,204+</span>
                    </div>
                    <div class="lower-sec">
                        <h4>Happy Customers</h4>
                    </div>
                </div>
            </div>
            <div class="sponsor-cont">
                <div class="grid-item">
                    <img class="img-1" src="./images/organisation/fca_logo.png" alt="fca logo" />
                </div>
                <div class="grid-item">
                    <img class="img-2" src="./images/organisation/fsca_logo.png" alt="fsca logo" />
                </div>
                <div class="grid-item">
                    <img class="img-3" src="./images/organisation/mfsa_logo.png" alt="mfsa logo" />
                </div>
                <div class="grid-item">
                    <img class="img-4" src="./images/organisation/cfpb_logo.png" alt="cfpb logo" />
                </div>
            </div>
        </div>
    </div>

    <!--page footer section-->
    <div class="ux-bg-grayblue">
        <div class="footer-header-cont">
            <a href="#" class="ux-txt-smokewhite txt-hover">
                <i class="fas fa-user-cog"></i><span>Admin Sign In</span>
            </a>
        </div>
        <div class="footer-cont">
            <div class="footer-col_1">
                <h4>CONTACT US</h4>
                <ul class="ux-vt-menu fmt-link-med">
                    <li>
                        <a href="#" class="link ux-txt-smokewhite txt-hover">
                            <i class="fas fa-map-marker-alt"></i><span>10A Enugu, Nigeria</span>
                        </a>
                    </li>
                    <li>
                        <a href="#" class="link ux-txt-smokewhite txt-hover">
                            <!--<i class="fas fa-envelope"></i>-->officialwebsite@gmail.com
                        </a>
                    </li>
                    <li>
                        <a href="#" class="link ux-txt-smokewhite txt-hover">
                            <!--<i class="fas fa-phone-square-alt"></i>-->+234 8156654434
                        </a>
                    </li>
                </ul>
            </div>
            <div class="footer-col_2">
                <h4>TERMS</h4>
                <ul class="ux-vt-menu fmt-link-med">
                    <li>
                        <a href="#" class="link ux-txt-smokewhite txt-hover">Terms & Condition</a>
                    </li>
                    <li>
                        <a href="#" class="link ux-txt-smokewhite txt-hover">Privacy Policy</a>
                    </li>
                    <li>
                        <a href="#" class="link ux-txt-smokewhite txt-hover">Terms of Use</a>
                    </li>
                    <li>
                        <a href="#" class="link ux-txt-smokewhite txt-hover">Cookies Statement</a>
                    </li>
                </ul>
            </div>
            <div class="footer-col_3">
                <h4>CONNECT WITH US</h4>
                <ul class="ux-hr-menu">
                    <li>
                        <a href="#" class="ux-txt-smokewhite txt-hover">
                            <i class="fab fa-facebook-square"></i>
                        </a>
                    </li>
                    <li>
                        <a href="#" class="ux-txt-smokewhite txt-hover">
                            <i class="fab fa-twitter-square"></i>
                        </a>
                    </li>
                    <li>
                        <a href="#" class="ux-txt-smokewhite txt-hover">
                            <i class="fab fa-linkedin"></i>
                        </a>
                    </li>
                </ul>
            </div>
        </div>
        <div class="footer-hr-line"></div>
        <div class="footer-base-cont ux-bg-grayblue ux-txt-white">
            Copyright &copy; WebsiteName <?php echo date("Y");?>. All Rights Reserved
        </div>
    </div>
</body>

</html>