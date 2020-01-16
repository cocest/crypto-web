function init() {
    // variables here
    let close_element_handler = null;
    let close_element_id;
    let close_element_toggle_state = {
        "help-drop-down-menu": false,
        "user-drop-down-menu": false
    };
    let notification_msg_map = new Map();
    let notification_max_list = 5;
    let load_prev_msg_offset;
    let fetch_new_notification;
    let notification_first_load = true;
    let notification_first_run = true;
    let active_win_id = null;
    let temp_tray_icon = { elem: null, class_value: "" };
    let udara_editor = window.UdaraEditor(
        "editor",
        {
            spellcheck: false
        },
        {
            font_name: "Arial",
            font_size: 4,
            font_color: "#373435"
        }
    );

    // show show and hide page menu
    window.showPageSideMenu = function (elem) {
        let side_menu_elem = document.getElementById("page-left-menu-cont");

        if (elem.getAttribute("toggle") == 0) { // hide the menu
            elem.setAttribute("toggle", "1");
            elem.setAttribute("class", "show-side-menu-icon open");
            side_menu_elem.setAttribute("class", "show-menu");

        } else {
            elem.setAttribute("toggle", "0");
            elem.setAttribute("class", "show-side-menu-icon close");
            side_menu_elem.setAttribute("class", "hide-menu");
        }
    };

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

    // show user drop down menu
    window.showUserDropDownMenu = function (e) {
        let elem = document.getElementById('user-drop-down-menu-cont');

        // close active menu
        if (close_element_handler != null && close_element_id != 'user-drop-down-menu') {
            close_element_handler.setAttribute("class", "remove-elem");
            close_element_handler = null;
            close_element_toggle_state[close_element_id] = false;
        }

        if (close_element_toggle_state['user-drop-down-menu']) {
            close_element_toggle_state['user-drop-down-menu'] = false;
            elem.setAttribute("class", "remove-elem");

        } else {
            close_element_toggle_state['user-drop-down-menu'] = true;
            elem.removeAttribute("class");
        }

        close_element_id = "user-drop-down-menu";
        close_element_handler = elem;
    };

    // show help drop down menu
    window.showHelpDropDownMenu = function () {
        let elem = document.getElementById('help-drop-down-menu-cont');

        // close active menu
        if (close_element_handler != null && close_element_id != 'help-drop-down-menu') {
            close_element_handler.setAttribute("class", "remove-elem");
            close_element_handler = null;
            close_element_toggle_state[close_element_id] = false;
        }

        if (close_element_toggle_state['help-drop-down-menu']) {
            close_element_toggle_state['help-drop-down-menu'] = false;
            elem.setAttribute("class", "remove-elem");

        } else {
            close_element_toggle_state['help-drop-down-menu'] = true;
            elem.removeAttribute("class");
        }

        close_element_id = "help-drop-down-menu";
        close_element_handler = elem;
    };

    // calculate possible width needed to contain menu icons in the tray
    function calculateMinWidthForTrayIcon() {
        let menu_slots = document.querySelectorAll('.menu-group.pop-menu .menu-btn');
        let min_width = 0;

        for (let i = 0; i < menu_slots.length; i++) {
            min_width = min_width + menu_slots[i].offsetWidth;
        }

        return min_width + 8; // padding of 4px is applied arround tray menu
    }

    // show email editor tray icon
    window.showMailEditorMenuTray = function (e) {
        e.preventDefault();

        let elem = document.querySelector('.main-menu-cont');

        if (elem.getAttribute("toggle") == 1) {
            // show the tray menu
            elem.setAttribute("class", "main-menu-cont hide-tray");
            elem.setAttribute("toggle", "0");

        } else { // toggle is 0
            // hide the tray menu
            elem.setAttribute("class", "main-menu-cont show-tray");
            elem.setAttribute("toggle", "1");
        }
    }

    // fit email editor menu to screen size
    function adaptMailEditorMenu() {
        // set menu full width
        let menu_full_width = 820;
        let menu_second_width = 660;
        let menu_third_width = 510;
        let group_menu_index = 0;
        let tags = document.getElementsByTagName("body");
        let w_w = tags.length > 0 ? tags[0].offsetWidth : window.innerWidth;
        let show_menu_tray_button = document.querySelector('#compose-mail-editor .main-menu-cont .show-hidden-menu-btn');

        // show tray menu icon button
        show_menu_tray_button.setAttribute("class", "show-hidden-menu-btn");

        // determine menu icons to be added to tray
        if (w_w < menu_third_width) {
            group_menu_index = 1;

        } else if (w_w < menu_second_width) {
            group_menu_index = 2;

        } else if (w_w < menu_full_width) {
            group_menu_index = 3;

        } else {
            // hide show tray menu icon
            show_menu_tray_button.setAttribute("class", "show-hidden-menu-btn remove-elem");

            if (temp_tray_icon.elem != null) {
                temp_tray_icon.elem.setAttribute("class", temp_tray_icon.class_value);
                temp_tray_icon.elem.removeAttribute("style");
            }

            temp_tray_icon.elem = null;
            temp_tray_icon.class_value = "";

            return;
        }


        // fit menu to window's width
        if (temp_tray_icon.elem != null) {
            temp_tray_icon.elem.setAttribute("class", temp_tray_icon.class_value);
            temp_tray_icon.elem.removeAttribute("style");
        }

        let main_menu_elem = document.querySelector('#compose-mail-editor .main-menu-cont');
        let tray_icon_elem = document.querySelector('#compose-mail-editor .main-menu-cont .menu-cont-' + group_menu_index);

        // add menu to tray before we do the calculation
        tray_icon_elem.setAttribute("class", "menu-cont-" + group_menu_index + " menu-group pop-menu");

        // needed min width to contain menu icons in the tray
        let min_tray_icon_width = calculateMinWidthForTrayIcon();

        // calculate menu tray position and width
        let m_x = (main_menu_elem.offsetLeft + main_menu_elem.offsetWidth) - min_tray_icon_width;
        let m_y = main_menu_elem.offsetTop + main_menu_elem.offsetHeight + 10;

        if (m_x < 0) {
            m_x = 10;

            // check if the menu can fit into window's width
            if (min_tray_icon_width < w_w) {
                tray_icon_elem.setAttribute("style",
                    "top: " + m_y + "px; " +
                    "left: " + m_x + "px;"
                );

            } else {
                tray_icon_elem.setAttribute("style",
                    "top: " + m_y + "px; " +
                    "left: " + m_x + "px; " +
                    "width: " + (w_w - 28) + "px;"
                );
            }

        } else {
            tray_icon_elem.setAttribute("style",
                "top: " + m_y + "px; " +
                "left: " + m_x + "px;"
            );
        }

        temp_tray_icon.elem = tray_icon_elem;
        temp_tray_icon.class_value = "menu-cont-" + group_menu_index + " menu-group";
    }

    // format editing text
    window.editorFormatCommand = function (e, cmd, value) {
        e.preventDefault();

        switch (cmd) {
            case 'bold':
                udara_editor.format.bold();
                break;

            case 'italic':
                udara_editor.format.italic();
                break;

            case 'underline':
                udara_editor.format.underline();
                break;

            case 'strikethrough':
                udara_editor.format.strikeThrough();
                break;

            case 'left':
                udara_editor.format.justifyLeft();
                break;

            case 'center':
                udara_editor.format.justifyCenter();
                break;

            case 'right':
                udara_editor.format.justifyRight();
                break;

            case 'justify':
                udara_editor.format.justifyFull();
                break;

            case 'orderedlist':
                udara_editor.format.insertOrderedList();
                break;

            case 'unorderedlist':
                udara_editor.format.insertUnorderedList();
                break;

            case 'indent':
                udara_editor.format.indent();
                break;

            case 'outdent':
                udara_editor.format.outdent();
                break;

            case 'removeformat':
                udara_editor.format.removeFormat();
                break;

            default:
            // shouldn't be here
        }
    }

    // reset all the editor highlight
    let editor_cmd_elems = document.querySelectorAll('.mail-editor-input');
    function resetEditorCommmandsHighlight() {
        for (let i = 0; i < editor_cmd_elems.length; i++) {
            editor_cmd_elems[i].removeAttribute("highlighted");
        }
    }

    // attach listener to get applied format to text or block of text
    udara_editor.attachBlockFormatListener(function (applied_format) {
        // highlight the font name
        if (typeof applied_format.font_name != "undefined") {
            selectCustomInputOptionByIndex("font-name",
                ({
                    "Arial": 0,
                    "Arial Black": 1,
                    "Avenir": 2,
                    "Calibri": 3,
                    "Comic Sans MS": 4,
                    "Courier New": 5,
                    "Geneva": 6,
                    "Georgia": 7,
                    "Impact": 8,
                    "Sans Serif": 9,
                    "Segoe UI": 10,
                    "Times New Roman": 11,
                    "Verdana": 12

                })[applied_format.font_name]
            );
        }

        // highlight the font size
        if (typeof applied_format.font_size != "undefined") {
            selectCustomInputOptionByIndex("font-size", applied_format.font_size - 1);
        }

        // reset editor commands highlight
        resetEditorCommmandsHighlight();

        // highlight the bold
        if (typeof applied_format.bold != "undefined") {
            document.getElementById("mail-editor-input-bold").setAttribute("highlighted", "true");
        }

        // highlight the italic
        if (typeof applied_format.italic != "undefined") {
            document.getElementById("mail-editor-input-italic").setAttribute("highlighted", "true");
        }

        // highlight the underline
        if (typeof applied_format.underline != "undefined") {
            document.getElementById("mail-editor-input-underline").setAttribute("highlighted", "true");
        }

        // highlight the strikethrough
        if (typeof applied_format.strike_through != "undefined") {
            document.getElementById("mail-editor-input-strikethrough").setAttribute("highlighted", "true");
        }

        // highlight the color
        if (typeof applied_format.font_color != "undefined") {
            let elem = document.querySelector('.editor-font-color-selector .label-cont');
            elem.setAttribute("style", "color: " + applied_format.font_color + ";");
        }

        // highlight the left align
        if (typeof applied_format.align != "undefined") {
            if (applied_format.align == "left") {
                document.getElementById("mail-editor-input-left").setAttribute("highlighted", "true");

            } else if (applied_format.align == "center") {
                document.getElementById("mail-editor-input-center").setAttribute("highlighted", "true");

            } else if (applied_format.align == "right") {
                document.getElementById("mail-editor-input-right").setAttribute("highlighted", "true");

            } else if (applied_format.align == "justify") {
                document.getElementById("mail-editor-input-justify").setAttribute("highlighted", "true");
            }
        }

        // highlight the list
        if (typeof applied_format.list != "undefined") {
            if (applied_format.list == "ordered") {
                document.getElementById("mail-editor-input-orderedlist").setAttribute("highlighted", "true");

            } else if (applied_format.list == "unordered") {
                document.getElementById("mail-editor-input-unorderedlist").setAttribute("highlighted", "true");
            }
        }

        // highlight the indent
        if (typeof applied_format.indented != "undefined") {
            document.getElementById("mail-editor-input-indent").setAttribute("highlighted", "true");
        }
    });

    /** 
     * code for custom select input
     */

    // get all the ux custom select input in the page
    let ux_select_elems = document.querySelectorAll('.ux-custom-select-input');
    let select_elem;
    let ux_select_input_active = [];

    // add event to part of the custom select input
    for (let i = 0; i < ux_select_elems.length; i++) {

        ux_select_input_active.push(false);
        select_elem = ux_select_elems[i].querySelector('.select-option');

        // to prevent lost of focus on edit area
        select_elem.onmousedown = function (e) {
            e.preventDefault();
        };

        // show select list menu
        select_elem.onclick = function (e) {
            e.preventDefault();
            fitCustomSelectInput(ux_select_elems[i], i);
        }

        addEventInSelectInputOptions(ux_select_elems[i], function (value, tag) {
            // determine the right input event occure on
            if (tag == "font-name") {
                udara_editor.format.fontName(value);

            } else if (tag == "font-size") {
                udara_editor.format.fontSize(value);
            }
        });
    }

    // utility function that select input option
    function selectCustomInputOptionByIndex(tag, index) {
        let select_input = null;

        // find the custom select input with tag
        for (let i = 0; i < ux_select_elems.length; i++) {
            if (ux_select_elems[i].getAttribute("tag") == tag) {
                select_input = ux_select_elems[i];
                break;
            }
        }

        if (select_input != null) {
            let select_options = select_input.querySelectorAll('.option-list > li');

            // find option at user's pass index
            if (!(index < select_options.length && index >= 0)) {
                return;
            }

            // remove previous selected option
            let selected_option = select_input.querySelector('.option-list > li.selected');
            if (selected_option != null) {
                selected_option.removeAttribute("class");
            }

            // select new option
            select_options[index].setAttribute("class", "selected");

            // display the selected option
            let label = select_input.querySelector('.select-option .selected-item .name');
            let option_value = select_options[index].querySelector('.option-value');
            label.innerHTML = option_value.innerHTML;
        }
    }

    // function to position list menu and fit it to screen resolution
    function fitCustomSelectInput(select_input_elem, index) {
        // get the window and menu
        let win_elem = document.getElementById("compose-mail-editor"); // not needed if position is absolute or not set
        let menu = select_input_elem.querySelector('.option-list-cont');

        // clear the menu set style
        menu.removeAttribute("style");

        // show menu
        menu.setAttribute("class", "option-list-cont");

        // check if list should be displayed downward or upward
        let m_h = menu.offsetHeight;
        let m_w = menu.offsetWidth;
        let bounding_rect = select_input_elem.querySelector('.select-option').getBoundingClientRect();
        let w_h = window.innerHeight;
        let w_w = window.innerWidth;
        let b_y = (bounding_rect.y || bounding_rect.top);
        let top_space = b_y;
        let down_space = w_h - (b_y + bounding_rect.height);
        let menu_pos_x = bounding_rect.x - win_elem.offsetLeft;

        // calculate the distance we should shift the menu left
        if ((w_w - bounding_rect.x) < m_w) {
            menu_pos_x = menu_pos_x - (m_w - (w_w - bounding_rect.x));
        }

        // check if menu should placed down or up
        if (m_h < down_space || down_space >= top_space) { // drop down
            if (m_h < down_space) {
                // position the menu
                menu.setAttribute(
                    "style",
                    "top: " + (((bounding_rect.y || bounding_rect.top) + bounding_rect.height) - win_elem.offsetTop) + "px; " +
                    "left: " + menu_pos_x + "px;"
                );

            } else {
                // postion and set maximum height for menu
                menu.setAttribute(
                    "style",
                    "top: " + (((bounding_rect.y || bounding_rect.top) + bounding_rect.height) - win_elem.offsetTop) + "px; " +
                    "left: " + menu_pos_x + "px; " +
                    "max-height: " + (down_space - 20) + "px;"
                );
            }

        } else { // drop up
            if (m_h < top_space) {
                // position the menu
                menu.setAttribute(
                    "style",
                    "top: " + (b_y - (m_h + win_elem.offsetTop)) + "px; " +
                    "left: " + menu_pos_x + "px;"
                );

            } else {
                // postion and set maximum height for menu
                menu.setAttribute(
                    "style",
                    "top: " + (-1 * (win_elem.offsetTop - 20)) + "px; " +
                    "left: " + menu_pos_x + "px;" +
                    "max-height: " + (top_space - 20) + "px;"
                );
            }
        }

        ux_select_input_active[index] = true;
    }

    // add mousedown event in all custom select input option or items
    function addEventInSelectInputOptions(select_input_elem, call_back) {
        // get custom select input options
        let custom_select_options = select_input_elem.querySelectorAll('.option-list > li');
        for (let i = 0; i < custom_select_options.length; i++) {
            // listen to click event
            custom_select_options[i].onmousedown = function (e) {
                e.preventDefault();

                // remove previous selected option
                let selected_option = select_input_elem.querySelector('.option-list > li.selected');
                if (selected_option != null) {
                    selected_option.removeAttribute("class");
                }

                // get custom select option that generate the event
                let target_option = e.currentTarget;

                // select new option
                target_option.setAttribute("class", "selected");

                // display the selected option
                let label = select_input_elem.querySelector('.select-option .selected-item .name');
                let option_value = target_option.querySelector('.option-value');
                label.innerHTML = option_value.innerHTML;

                // call pass in function
                call_back(option_value.innerText, select_input_elem.getAttribute("tag"));
            };
        }
    }

    // code for custom select input end here

    /**
     * code for editor color picker
     */

    let editor_color_select = document.querySelector('.editor-font-color-selector');
    let editor_color_select_input_active = false;

    // to prevent lost of focus on edit area
    editor_color_select.onmousedown = function (e) {
        e.preventDefault();
    };

    // show color selector menu
    editor_color_select.onclick = function (e) {
        e.preventDefault();
        fitColorSelectInput(editor_color_select);
    }

    // listen when user click the a
    let color_grid_cont = document.querySelector('.editor-font-color-selector .color-grid-cont');
    color_grid_cont.onmousedown = function (e) {
        e.preventDefault();

        // get click color
        let color = e.target.getAttribute("color");

        if (color != null) {
            // display the selected color
            let label = editor_color_select.querySelector('.label-cont');
            label.setAttribute("style", "color: " + color + ";");

            // set font color for the editor
            udara_editor.format.foreColor(color);
        }
    };

    // function to position color menu and fit it to screen resolution
    function fitColorSelectInput(select_input_elem) {
        // get the window and menu
        let menu = select_input_elem.querySelector('.selector-cont');

         // clear the menu set style
         menu.removeAttribute("style");

        // show menu
        menu.setAttribute("class", "selector-cont");

        // check if list should be displayed downward or upward
        let m_h = menu.offsetHeight;
        let m_w = menu.offsetWidth;
        let bounding_rect = select_input_elem.getBoundingClientRect();
        let w_h = window.innerHeight;
        let w_w = window.innerWidth - 30;
        let b_y = select_input_elem.offsetTop;
        let top_space = (bounding_rect.y || bounding_rect.top);
        let down_space = w_h - (top_space + select_input_elem.offsetHeight);
        let menu_pos_x = select_input_elem.offsetLeft;

        // calculate the distance we should shift the menu left
        if ((w_w - select_input_elem.offsetLeft) < m_w) {
            menu_pos_x = menu_pos_x - (m_w - (w_w - select_input_elem.offsetLeft));
        }

        // check if menu should placed down or up
        if (m_h < down_space || down_space >= top_space) { // drop down
            if (m_h < down_space) {
                // position the menu
                menu.setAttribute(
                    "style",
                    "top: " + (select_input_elem.offsetTop + select_input_elem.offsetHeight) + "px; " +
                    "left: " + menu_pos_x + "px;"
                );

            } else {
                // position and set maximum height for menu
                menu.setAttribute(
                    "style",
                    "top: " + (select_input_elem.offsetTop + select_input_elem.offsetHeight) + "px; " +
                    "left: " + menu_pos_x + "px; " +
                    "max-height: " + (down_space - 20) + "px;"
                );
            }

        } else { // drop up
            if (m_h < top_space) {
                // position the menu
                menu.setAttribute(
                    "style",
                    "top: " + (b_y - m_h) + "px; " +
                    "left: " + menu_pos_x + "px;"
                );

            } else {
                // position and set maximum height for menu
                menu.setAttribute(
                    "style",
                    "top: " + ((select_input_elem.offsetTop - top_space) + 20) + "px; " +
                    "left: " + menu_pos_x + "px; " +
                    "max-height: " + (top_space - 20) + "px;"
                );
            }
        }

        editor_color_select_input_active = true;
    }

    // code for editor color picker end here

    // close select menu when clicked on document
    document.onmousedown = function (e) {
        if (!(e.target.getAttribute("kopen") == null)) {
            e.preventDefault();
            return false;
        }

        // hide custom select menu
        for (let i = 0; i < ux_select_input_active.length; i++) {
            if (ux_select_input_active[i]) {
                ux_select_input_active[i] = false;
                let menu = ux_select_elems[i].querySelector('.option-list-cont');
                menu.setAttribute("class", "option-list-cont remove-elem");
            }
        }

        // hide color selector menu
        if (editor_color_select_input_active) {
            editor_color_select_input_active = false;
            let menu = editor_color_select.querySelector('.selector-cont');
            menu.setAttribute("class", "selector-cont remove-elem");
        }
    };

    // open window
    window.openWin = function (win) {
        closeActiveMenu();

        if (active_win_id != null) {
            window.closeActiveWin(active_win_id);
        }

        let elem = document.getElementById(win);
        elem.removeAttribute("class");
        active_win_id = win;

        if (win == "compose-mail-editor") {
            adaptMailEditorMenu();
        }
    };

    // close active window
    window.closeActiveWin = function (win) {
        let elem = document.getElementById(win);
        elem.setAttribute("class", "remove-elem");
        active_win_id = null;

        // clear content and reset mail editor
        if (win == "compose-mail-editor") {
            // clear typed header
            document.getElementById("send-us-email-header").value = "";

            // clear typed message
            document.getElementById("editor").innerHTML = "";

            resetEditorCommmandsHighlight();
            selectCustomInputOptionByIndex("font-name", 0);
            selectCustomInputOptionByIndex("font-size", 3);

            // reset color to default value
            document.querySelector('.editor-font-color-selector .label-cont').setAttribute("style", "color: #373435;");
        }
    };

    // load previous notification if there is any
    window.loadPreviousNotification = function () {
        let req_url = '../../request';
        let form_data =
            'req=get_prev_notification&time_offset=' + load_prev_msg_offset +
            '&limit= ' + notification_max_list; // request query

        // hide load more button and show loading animation
        document.getElementById("load-prev-notification").setAttribute("class", "remove-elem");
        document.getElementById("loading-notification-anim-cont").removeAttribute("class");

        // send request to server
        window.ajaxRequest(
            req_url,
            form_data,
            { contentType: "application/x-www-form-urlencoded" },

            // listen to response from the server
            function (response) {
                appendLoadedNotification(JSON.parse(response).messages);
            },

            // listen to server error
            function (err_status) {
                // check if is timeout error
                if (err_status == 408 && err_status == 504) {
                    window.loadPreviousNotification();

                } else if (err_status == 503) { // check if server is busy or unavalaible
                    // wait for 2 minutes
                    setTimeout(function () {
                        window.loadPreviousNotification();

                    }, 60000 * 2);

                } else { // other error here
                    // show load more button and hide loading animation
                    document.getElementById("load-prev-notification").removeAttribute("class");
                    document.getElementById("loading-notification-anim-cont").setAttribute("class", "remove-elem");
                }
            }
        );
    };

    // expand notification message
    window.expandNotificationMsg = function (msg_id) {
        let elem = document.getElementById(msg_id);
        let msg_body = elem.querySelector('.msg-body');
        let expand_btn = elem.querySelector('.expand-msg-btn');

        if (msg_body.getAttribute("toggle") == 0) { // expand
            msg_body.innerHTML = notification_msg_map.get(msg_id);
            msg_body.setAttribute("toggle", "1");
            expand_btn.setAttribute("class", "expand-msg-btn collapse");

        } else { // collapse
            let [clipped_text, is_text_clipped] = clipOutText(90, notification_msg_map.get(msg_id));
            msg_body.innerHTML = clipped_text;
            msg_body.setAttribute("toggle", "0");
            expand_btn.setAttribute("class", "expand-msg-btn expand");
        }
    };

    // utility function to append fetch notification
    function appendLoadedNotification(messages) {
        let item;
        let msg_date;
        let ref_child_elem = document.getElementById("load-prev-notification");

        // hide loading animation
        document.getElementById("loading-notification-anim-cont").setAttribute("class", "remove-elem");

        // check if there more message to load
        if (messages.length >= notification_max_list) {
            ref_child_elem.removeAttribute("class"); // show load more button
        }

        // check if there is loaded message
        if (messages.length > 0) {
            load_prev_msg_offset = messages[messages.length - 1].time;
        }

        // append the message to the list
        for (let i = 0; i < messages.length; i++) {
            msg_date = new Date(parseInt(data.messages[i].time) * 1000);
            let [clipped_text, is_text_clipped] = clipOutText(90, messages[i].content);

            item = document.createElement("div");
            item.setAttribute("id", messages[i].id)
            item.setAttribute("class", "item-cont");
            item.innerHTML =
                `<div class="title-bar-cont">
                     <div class="msg-title">${messages[i].title}</div>
                     <ul class="msg-action-btn-cont">
                        <li class="mark-msg ${messages[i].read ? 'read' : 'no-read'}" title="Mark message as read" ${messages[i].read ? '' : 'onclick="processUserCommandNotification(\'' + messages[i].id + '\', \'markAsRead\', ' + messages[i].read + ')"'}>
                            <img src="../../images/icons/icons_sprite_2.png" />
                        </li>
                        <li class="delete-msg" title="Delete message" onclick="processUserCommandNotification('${messages[i].id}', 'deleteMsg', ${messages[i].read})">
                            <img src="../../images/icons/icons_sprite_2.png" />
                        </li>
                    </ul>
                </div>
                <div class="msg-body" toggle="0">${clipped_text}</div>
                <div class="footer">
                    ${
                is_text_clipped ?
                    '<div class="expand-msg-btn expand" title="Expand or collapse the message" onclick="expandNotificationMsg(\'' + messages[i].id + '\')">' +
                    '    <img src="../../images/icons/icons_sprite_2.png" />' +
                    '</div>' : ''
                }
                <div class="msg-date">${msg_date.getMonth() + 1}/${msg_date.getDate()}/${msg_date.getFullYear()} ${window.toSTDTimeString(msg_date, false)}</div>
                </div>`;

            // add message to list
            list_cont.insertBefore(item, ref_child_elem);

            // add message to map
            notification_msg_map.set(messages[i].id, messages[i].content);
        }
    }

    // utiility function to update numbers of unread messages and 
    // insert new message into message list
    function updateNotification(data) {
        let unread_msg_counter_label = document.getElementById("unread-msg-counter");
        let list_cont = document.getElementById("notification-list-cont");
        let item;
        let msg_date;
        let ref_child_elem = notification_first_load ? document.getElementById("load-prev-notification") : list_cont.children[0];

        // update numbers of unread messages counter
        document.getElementById("unread-msg-counter").innerHTML = data.unread_msg_count;

        // check if this function is called the first time
        if (notification_first_run) {
            notification_first_run = false;

            // hide loading animation
            document.getElementById("loading-notification-anim-cont").setAttribute("class", "remove-elem");

            // check if user has no notification
            if (data.messages.length < 1) {
                document.getElementById("notification-status-msg").removeAttribute("class");
            }
        }

        // check if message is loaded the first time
        if (notification_first_load) {
            if (data.messages.length > 0) {
                notification_first_load = false;

                load_prev_msg_offset = data.messages[data.messages.length - 1].time;

                // show uread message counter
                unread_msg_counter_label.setAttribute("class", "count");

                // hide "no notification" message
                document.getElementById("notification-status-msg").setAttribute("class", "remove-elem");

                // check if there is more message to load
                if (data.messages.length >= notification_max_list) {
                    ref_child_elem.removeAttribute("class");
                }
            }
        }

        // append the message to the list
        for (let i = 0; i < data.messages.length; i++) {
            msg_date = new Date(parseInt(data.messages[i].time) * 1000);
            let [clipped_text, is_text_clipped] = clipOutText(90, data.messages[i].content);

            item = document.createElement("div");
            item.setAttribute("id", data.messages[i].id)
            item.setAttribute("class", "item-cont");
            item.innerHTML =
                `<div class="title-bar-cont">
                     <div class="msg-title">${data.messages[i].title}</div>
                     <ul class="msg-action-btn-cont">
                        <li class="mark-msg ${data.messages[i].read ? 'read' : 'no-read'}" title="Mark message as read" ${data.messages[i].read ? '' : 'onclick="processUserCommandNotification(\'' + data.messages[i].id + '\', \'markAsRead\', ' + data.messages[i].read + ')"'}>
                            <img src="../../images/icons/icons_sprite_2.png" />
                        </li>
                        <li class="delete-msg" title="Delete message" onclick="processUserCommandNotification('${data.messages[i].id}', 'deleteMsg', ${data.messages[i].read})">
                            <img src="../../images/icons/icons_sprite_2.png" />
                        </li>
                    </ul>
                </div>
                <div class="msg-body" toggle="0">${clipped_text}</div>
                <div class="footer">
                    ${
                is_text_clipped ?
                    '<div class="expand-msg-btn expand" title="Expand or collapse the message" onclick="expandNotificationMsg(\'' + data.messages[i].id + '\')">' +
                    '    <img src="../../images/icons/icons_sprite_2.png" />' +
                    '</div>' : ''
                }
                    <div class="msg-date">${msg_date.getMonth() + 1}/${msg_date.getDate()}/${msg_date.getFullYear()} ${window.toSTDTimeString(msg_date, false)}</div>
                </div>`;

            // add message to list
            list_cont.insertBefore(item, ref_child_elem);

            // add message to map
            notification_msg_map.set(data.messages[i].id, data.messages[i].content);
        }

        // update uread message count
        unread_msg_counter_label.innerHTML = data.unread_msg_count;
    }

    // process user's command on notification
    window.processUserCommandNotification = function (msg_id, command, count_down) {
        let list_cont = document.getElementById("notification-list-cont");
        let unread_msg_counter_label = document.getElementById("unread-msg-counter");
        let req_url = '../../request';
        let form_data;

        if (command == "markAsRead") {
            // request query
            form_data = "req=read_notification&msg_id=" + msg_id;

            // mark the notification as read
            document.getElementById(msg_id).querySelector('.mark-msg').setAttribute("class", "mark-msg read");

        } else if (command == "deleteMsg") {
            // request query
            form_data = "req=delete_notification&msg_id=" + msg_id;

            // delete the notification
            list_cont.removeChild(document.getElementById(msg_id));
            notification_msg_map.delete(msg_id);
        }

        if (!count_down) {
            if (parseInt(unread_msg_counter_label.innerText) == 1) {
                unread_msg_counter_label.setAttribute("class", "count remove-elem");

            } else {
                unread_msg_counter_label.innerHTML = parseInt(unread_msg_counter_label.innerText) - 1;
            }
        }

        // send request to server
        window.ajaxRequest(
            req_url,
            form_data,
            { contentType: "application/x-www-form-urlencoded" },

            // listen to response from the server
            function (response) {
                // leave it empty
            },

            // listen to server error
            function (err_status) {
                // leave it empty
            }
        );
    };

    // resend verification to user's email again
    window.resendEmailVerification = function () {
        let req_url = '../../request';
        let form_data = 'req=resend_email_verification'; // request query

        // hide resend button and show resending animation
        document.querySelector('.email-resend-btn-cont').setAttribute("class", "email-resend-btn-cont remove-elem");
        document.querySelector('.resend-email-anim-cont').setAttribute("class", "resend-email-anim-cont");

        // send request to server
        window.ajaxRequest(
            req_url,
            form_data,
            { contentType: "application/x-www-form-urlencoded" },

            // listen to response from the server
            function (response) {
                // show resend button and hide resending animation
                document.querySelector('.email-resend-btn-cont').setAttribute("class", "email-resend-btn-cont");
                document.querySelector('.resend-email-anim-cont').setAttribute("class", "resend-email-anim-cont remove-elem");
            },

            // listen to server error
            function (err_status) {
                // check if is timeout error
                if (err_status == 408 && err_status == 504) {
                    window.resendEmailVerification();

                } else if (err_status == 503) { // check if server is busy or unavalaible
                    // wait for 2 minutes
                    setTimeout(function () {
                        window.resendEmailVerification();

                    }, 60000 * 2);

                } else {
                    // show resend button and hide resending animation
                    document.querySelector('.email-resend-btn-cont').setAttribute("class", "email-resend-btn-cont");
                    document.querySelector('.resend-email-anim-cont').setAttribute("class", "resend-email-anim-cont remove-elem");
                }
            }
        );
    };

    // send the compose email to customer care
    window.sendComposeEmail = function () {
        // disable send mail button
        let send_mail_btn = document.querySelector('#compose-mail-editor .send-msg-btn');
        send_mail_btn.disabled = true;

        let req_url = '../../sendus_email';
        let mail_form = new FormData();

        // append data
        mail_form.append("mail_header", document.getElementById("send-us-email-header").value);
        mail_form.append("mail_body", udara_editor.getContent(udara_editor.MAIL));

        // send request to server
        window.ajaxRequest(
            req_url,
            mail_form,
            { contentType: false },

            // listen to response from the server
            function (response) {
                let response_data = JSON.parse(response);

                if (response_data.success) {
                    // send message button
                    alert("Message sent successfully.");

                } else {
                    // display error message
                    alert(response_data.error_msg);
                }

                // enable button
                send_mail_btn.disabled = false;
            },

            // listen to server error
            function (err_status) {
                //check if is a timeout or server busy
                if (err_status == 408 ||
                    err_status == 504 ||
                    err_status == 503) {

                    window.sendComposeEmail();

                } else {
                    // enable button
                    send_mail_btn.disabled = false;
                    
                    // diplay error message to user
                    alert("Sending message failed.");
                }
            }
        );
    };

    // fetch new notification from server
    function fetchNewNotification() {
        if (typeof fetch_new_notification == "undefined") {
            fetch_new_notification = new Worker("../../js/fetchNewNotificationWorker.js");

            // initialise fetch of new notification
            fetch_new_notification.postMessage({ msg_limit: notification_max_list });

            // listen to when data is sent
            fetch_new_notification.addEventListener("message", function (event) {
                updateNotification(event.data);
            }, false);
        }
    }

    // utility function to close menu window
    function closeActiveMenu() {
        if (close_element_handler != null) {
            close_element_handler.setAttribute("class", "remove-elem");
            close_element_handler = null;
            close_element_toggle_state[close_element_id] = false;
        }
    }

    // adapt page content height
    function adaptPageContent() {
        let elem1 = document.querySelector('.page-content-cont');
        let elem2 = document.querySelector('.page-footer');
        elem2.removeAttribute("style"); // this is needed
        let footer_top_margin = 200;
        let ch = elem1.offsetHeight;
        let wh = window.innerHeight;

        // check to fit content height to window height
        if (ch < wh) {
            let new_top_margin = (wh - ch) + footer_top_margin;
            elem2.setAttribute("style", "margin-top: " + new_top_margin + "px;");
        }
    }

    // set page side menu scroll wrapper height on page load or resize
    function adaptPageSideMenu() {
        let page_top_menu_height = document.querySelector('.page-top-menu-cont').offsetHeight;
        let wh = window.innerHeight;
        let elem = document.querySelector('#page-left-menu-cont #scroll-wrapper');

        // set container height
        elem.setAttribute("style", "height: " + (wh - page_top_menu_height) + "px;");
    }

    // listen to when user click the section
    window.sectionClickEvent = function (e) {
        closeActiveMenu();
    };

    // call function onload
    adaptPageSideMenu();
    adaptPageContent();
    fetchNewNotification();
    adaptMailEditorMenu();

    // listen to page resize
    window.onresize = function (e) {
        adaptPageSideMenu();
        adaptPageContent();
        adaptMailEditorMenu();
    };
}

//initialise the script
if (window.attachEvent) {
    window.attachEvent("onload", init);

} else {
    window.addEventListener("load", init, false);
}