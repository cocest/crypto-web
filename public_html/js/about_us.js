function init() {
    // define and initialise variables
    let top_main_menu_hidden = false;
    let top_main_menu_changed = false;
    let drop_mobi_menu_active = false;
    let page_scroll_y = 0;
    let curr_page_scroll_y = 0;

    // display mobile menu
    window.dropMobileMenu = function (btn) {
        let menu_bar = document.querySelector('.page-top-menu-cont');
        let menu_icon = btn.querySelector('.drop-menu-icon');
        let menu_elem = document.querySelector('.drop-down-mobi-menu-cont');

        if (btn.getAttribute('toggle') == '0') {
            menu_bar.setAttribute('class', 'page-top-menu-cont fixed theme-bg-color show');
            menu_icon.setAttribute('class', 'drop-menu-icon open');
            menu_elem.setAttribute('class', 'drop-down-mobi-menu-cont theme-bg-color show shadow');
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
                elem.setAttribute('class', 'page-top-menu-cont theme-bg-color');
                top_main_menu_changed = false;
            }

        } else {
            top_main_menu_changed = true;

            if (curr_page_scroll_y - page_scroll_y > 0) { //check if page is scroll down
                // check if menu has not been hidden
                if (!top_main_menu_hidden) {
                    // hide menu
                    elem.setAttribute('class', 'page-top-menu-cont fixed theme-bg-color hide shadow');
                    top_main_menu_hidden = true;
                }

            } else { // page is scroll up
                // check if menu has not been shown
                if (top_main_menu_hidden) {
                    // hide menu
                    elem.setAttribute('class', 'page-top-menu-cont fixed theme-bg-color show shadow');
                    top_main_menu_hidden = false;
                }
            }
        }

        // set page current y-position
        page_scroll_y = curr_page_scroll_y;
    }

    // listen to page scroll event
    window.onscroll = function (e) {
        changeMainMenuOnScroll();
    };
}

//initialise the script
if (window.attachEvent) {
    window.attachEvent("onload", init);

} else {
    window.addEventListener("load", init, false);
}