function init() {
    // define and initialise variables
    let active_elem_handler = null;
    let active_elem;
    let admin_menu_toggle = false;

    // utility function to calculate page count
    window.pageCountForListItem = function (view_count, list_count) {
        let page_count = list_count / view_count;
        if (page_count % 1 == 0) {
            return Math.floor(page_count);
        }
    
        return Math.floor(page_count) + 1;
    };

    // show and hide page side menu
    window.showSideMenu = function (btn) {
        let elem = document.querySelector('.page-left-menu-cont');
        elem.setAttribute("class", "page-left-menu-cont open");
        active_elem_handler = elem;
        active_elem = "side-menu";
        document.getElementById("bg-modal").setAttribute("class", "show");
    };

    // listen on click event
    document.getElementById("bg-modal").onclick = function (e) {
        // remove the background highlight
        e.currentTarget.setAttribute("class", "remove");

        // close any active window
        if (active_elem_handler) {
            if (active_elem == "side-menu") {
                active_elem_handler.setAttribute("class", "page-left-menu-cont close");
            }

            active_elem_handler = null;
        }
    };

    // open admin drop down menu
    document.querySelector('.login-user-cont').onclick = function (e) {
        admin_menu_toggle = true;
        document.getElementById("admin-drop-down-menu-cont").removeAttribute("class");
        document.querySelector('.login-user-cont').setAttribute("style", "pointer-events: none;");
    };

    // listen to mouseup event on document
    document.onmouseup = function (e) {
        if (admin_menu_toggle) {
            admin_menu_toggle = false;
            document.getElementById("admin-drop-down-menu-cont").setAttribute("class", "remove-elem");
            document.querySelector('.login-user-cont').removeAttribute("style");
        }
    };
}

//initialise the script
if (window.attachEvent) {
    window.attachEvent("onload", init);

} else {
    window.addEventListener("load", init, false);
}