/**
 * Author: Attamah Celestine
 * Copyright (c) 2020
 * 
 * If want this library to be open source on github, send me an email.
 * Email: attamahcelestine@gmail.com
 */

(function () {
    // constants
    const _DEFAULT = 10;
    const _MAIL = 11;
    const _PLAIN = 12;

    // internal global variables here
    let _editor;
    let _call_back_func = null;
    let _default_format_applied = false;

    // change font name
    function _fontName(name) {
        document.execCommand("fontName", false, name);
    }

    function _fontSize(size) {
        document.execCommand("fontSize", false, size);
    }

    function _bold() {
        document.execCommand("bold", false, null);
    }

    function _italic() {
        document.execCommand("italic", false, null);
    }

    function _underline() {
        document.execCommand("underline", false, null);
    }

    function _strikeThrough() {
        document.execCommand("strikeThrough", false, null);
    }

    // change the text color. Pass color value must be hexadecimal
    function _foreColor(color) {
        document.execCommand("foreColor", false, color);
    }

    function _justifyLeft() {
        document.execCommand("justifyLeft", false, null);
    }

    function _justifyCenter() {
        document.execCommand("justifyCenter", false, null);
    }

    function _justifyRight() {
        document.execCommand("justifyRight", false, null);
    }

    function _justifyFull() {
        document.execCommand("justifyFull", false, null);
    }

    function _insertOrderedList() {
        document.execCommand("insertOrderedList", false, null);
    }

    function _insertUnorderedList() {
        document.execCommand("insertUnorderedList", false, null);
    }

    function _indent() {
        document.execCommand("indent", false, null);
    }

    function _outdent() {
        document.execCommand("outdent", false, null);
    }

    function _removeFormat() {
        document.execCommand("removeFormat", false, null);
    }

    // convert rgb to hex color
    function _convertToHexColor(rgb_color) {
        if (!/^#[0-9a-fA-F]{6,6}$/.test(rgb_color)) {
            let parts = rgb_color.replace(/(rgb|\(|\)|[ ]+)/g, "").split(",");
            let hex_color = "#";
            let temp_hex_color;

            for (let i = 0; i < parts.length; i++) {
                temp_hex_color = Number(parts[i]).toString(16);

                if ((temp_hex_color.length % 2) > 0) {
                    temp_hex_color = "0" + temp_hex_color;
                }

                hex_color += temp_hex_color;
            }

            return hex_color;
        }

        return rgb_color;
    }

    // get number size equivalent to small, medium, large etc
    function _convertToFontSizeNum(size) {
        if (/^([x]{1,3}[-]?[a-z]+|[a-z]+|3em)$/.test(size)) {
            return ({
                "xx-small": 1,
                "x-small": 1,
                "smaller": 2,
                "small": 2,
                "medium": 3,
                "large": 4,
                "larger": 4,
                "x-large": 5,
                "xx-large": 6,
                "xxx-large": 7,
                "3em": 7 // for browser that doesn't support xxx-large
            })[size];
        }

        return size;
    }

    // convert font attribute size value to name equivalent
    function _convertFontSizeToName(size) {
        if (/^[1-7]$/.test(size)) {
            return ({
                "1": "x-small",
                "2": "small",
                "3": "medium",
                "4": "large",
                "5": "x-large",
                "6": "xx-large",
                "7": "3em" // xxx-large only supprted by Chrome and Firefox
            })[size];
        }

        return size;
    }

    // get all the applied format on the text block
    function _getAppliedFormatOnTextBlock(curr_fragment) {
        // check to abort this operation
        if (_call_back_func == null) {
            return;
        }

        let attributes;
        let applied_format = {};

        // iterate outward until we found the editor root element
        while (true) {
            // extract applied format on each fragment (element)
            switch (curr_fragment.nodeName) {
                case 'DIV':
                case 'SPAN':
                case 'LI':
                    attributes = curr_fragment.attributes;

                    // iterate through the attribute
                    for (let i = 0; i < attributes.length; i++) {
                        if (attributes[i].name == "style") {
                            let styles = attributes[i].value.split(";");

                            for (let j = 0; j < styles.length; j++) {
                                let style_and_value = styles[j].split(":");

                                if (style_and_value[0].trim() == "text-align" && typeof applied_format.align == "undefined") {
                                    if (style_and_value[1].trim() == "left") {
                                        applied_format.align = "left";

                                    } else if (style_and_value[1].trim() == "center") {
                                        applied_format.align = "center";

                                    } else if (style_and_value[1].trim() == "right") {
                                        applied_format.align = "right";

                                    } else if (style_and_value[1].trim() == "justify") {
                                        applied_format.align = "justify";
                                    }

                                } else if (style_and_value[0].trim() == "font-family" && typeof applied_format.font_name == "undefined") {
                                    applied_format.font_name = style_and_value[1].trim();

                                } else if (style_and_value[0].trim() == "font-size" && typeof applied_format.font_size == "undefined") {
                                    applied_format.font_size = _convertToFontSizeNum(style_and_value[1].trim());

                                } else if (style_and_value[0].trim() == "color" && typeof applied_format.font_color == "undefined") {
                                    applied_format.font_color = _convertToHexColor(style_and_value[1].trim());

                                }

                                // other style value here
                            }

                        } else if (attributes[i].name == "align" && typeof applied_format.align == "undefined") { // MS browser
                            applied_format.align = attributes[i].value;

                        }
                    }

                    break;

                case 'BLOCKQUOTE':
                    applied_format.indented = true;

                    break;

                case 'FONT':
                    attributes = curr_fragment.attributes

                    // iterate through the attribute
                    for (let i = 0; i < attributes.length; i++) {
                        if (attributes[i].name == "color" && typeof applied_format.font_color == "undefined") {
                            applied_format.font_color = attributes[i].value;

                        } else if (attributes[i].name == "face" && typeof applied_format.font_name == "undefined") {
                            applied_format.font_name = attributes[i].value;

                        } else if (attributes[i].name == "size" && typeof applied_format.font_size == "undefined") {
                            applied_format.font_size = attributes[i].value;

                        } else if (attributes[i].name = "style") { // Chrome browser
                            let styles = attributes[i].value.split(";");

                            for (let j = 0; j < styles.length; j++) {
                                let style_and_value = styles[j].split(":");

                                if (style_and_value[0].trim() == "color" && typeof applied_format.font_color == "undefined") {
                                    applied_format.font_color = _convertToHexColor(style_and_value[1].trim());

                                } else if (style_and_value[0].trim() == "font-family" && typeof applied_format.font_name == "undefined") {
                                    applied_format.font_name = style_and_value[1].trim();

                                } else if (style_and_value[0].trim() == "font-size" && typeof applied_format.font_size == "undefined") {
                                    applied_format.font_size = _convertToFontSizeNum(style_and_value[1].trim());
                                }
                            }
                        }
                    }

                    break;

                case 'OL': // ordered list
                    if (typeof applied_format.list == "undefined") {
                        applied_format.list = "ordered";
                    }

                    break;

                case 'UL': // ordered list
                    if (typeof applied_format.list == "undefined") {
                        applied_format.list = "unordered";
                    }

                    break;

                case 'B': // bold
                case 'STRONG':
                    applied_format.bold = true;

                    break;

                case 'U': // underline
                    applied_format.underline = true;

                    break;

                case 'STRIKE':
                    applied_format.strike_through = true;

                    break;

                case 'I': // italic
                case 'EM':
                    applied_format.italic = true;

                    break;

                default:
                // you shouldn't be here
            }

            // base case
            if (!(curr_fragment.getAttribute("udaeditor") == null)) {
                break;
            }

            curr_fragment = curr_fragment.parentElement;
        }

        // return applied formats to listener
        _call_back_func(applied_format);
    }

    // replace unsupported element in the document
    function _refomartContentForEmail(parent_elem, nodes) {
        // base case conditions
        if (parent_elem.nodeName == "#text") {
            return document.createTextNode(parent_elem.nodeValue);

        } else if (parent_elem.nodeName == "BR") {
            return document.createElement("br");
        }

        let new_elem;
        let attributes = parent_elem.attributes;

        // check if is a tag we want to change
        if (parent_elem.nodeName == "BLOCKQUOTE") {
            new_elem = document.createElement("div");

            // assign attributes
            for (let j = 0; j < attributes.length; j++) {
                if (attributes[j].name == "style") {
                    // check if left margin is not set
                    if (!/(margin-left:[ ]*[0-9]+[a-z]*|margin:[ ]*[0-9]+[a-z]*)/.test(attributes[j].value)) {
                        new_elem.setAttribute("style", (attributes[j].value + " margin-left: 40px;").trim());

                    } else {
                        new_elem.setAttribute(attributes[j].name, attributes[j].value);
                    }

                } else {
                    new_elem.setAttribute(attributes[j].name, attributes[j].value);
                }
            }

        } else if (parent_elem.nodeName == "FONT") {
            let styles = "";
            new_elem = document.createElement("span");

            // assign attributes
            for (let j = 0; j < attributes.length; j++) {
                if (attributes[j].name == "style") {
                    styles += attributes[j].value;

                } else if (attributes[j].name == "color") {
                    styles += "color: " + _convertToHexColor(attributes[j].value) + "; ";

                } else if (attributes[j].name == "face") {
                    styles += "font-family: " + attributes[j].value + "; ";

                } else if (attributes[j].name == "size") {
                    styles += "font-size: " + _convertFontSizeToName(attributes[j].value) + "; ";
                }
            }

            new_elem.setAttribute("style", styles.trim());

        } else { // supported tag
            new_elem = document.createElement(parent_elem.localName);

            // assign all the attributes
            for (let j = 0; j < attributes.length; j++) {
                new_elem.setAttribute(attributes[j].name, attributes[j].value);
            }
        }

        // append all the child nodes
        for (let i = 0; i < nodes.length; i++) {
            new_elem.appendChild(_refomartContentForEmail(nodes[i], nodes[i].childNodes));
        }

        // return the new element to caller
        return new_elem;
    }

    // get the content of the editor
    function _getContent(content_type) {
        if (content_type == _DEFAULT) {
            // default tag and semantics generated by the browser
            return _editor.innerHTML;

        } else if (content_type == _MAIL) {
            // change some tags or element to make it more compactible for mail system
            let doc_nodes = _editor.childNodes;

            // create new document root
            let doc_root = document.createElement("div");

            // check if nodes is empty
            if (doc_nodes.length < 1) {
                doc_root.appendChild(document.createTextNode(""));
            }

            // append all the child nodes
            for (let i = 0; i < doc_nodes.length; i++) {
                doc_root.appendChild(_refomartContentForEmail(doc_nodes[i], doc_nodes[i].childNodes));
            }

            // return formatted document to caller
            return doc_root.innerHTML;

        } else if (content_type == _PLAIN) {
            // only typed plain text
            return _editor.innerText;
        }
    }

    /**
     * Return applied format on text block to attach callback function.
     * Note: only one callback can be attach
     */

    function _attachBlockFormatListener(listener) {
        if (typeof listener == "function") {
            _call_back_func = listener;
        }
    }

    // remove attach event
    function _removeBlockFormatListener() {
        _call_back_func = null;
    }

    // initialise the configuration with default settings
    function _initConfigDefault() {
        if (typeof config == "undefined") {
            return {
                spellcheck: false
                // other settings here
            }

        } else {
            // check if some object is defined, if not add the default
            if (typeof config.spellcheck == "undefined") {
                config.spellcheck = false;
            }

            return config;
        }
    }

    // position caret or cursor to a set text offset in text input element
    function _setCaretPosition(elem, pos) {
        let range = document.createRange();
        let sel = window.getSelection();

        // position caret
        range.setStart(elem, pos);
        range.collapse(true);
        sel.removeAllRanges();
        sel.addRange(range);
    }

    // apply pass in default format
    function _applyDefaultFormat(format) {
        // applly the format
        if (typeof format.font_name != "undefined") {
            _fontName(format.font_name);
        }

        if (typeof format.font_size != "undefined") {
            _fontSize(format.font_size);
        }

        if (typeof format.font_color != "undefined") {
            _foreColor(format.font_color);
        }
    }

    // call this function to initialise the editor
    window.UdaraEditor = function (edit_elem_id, config, format) {
        if (typeof format == "undefined") {
            _default_format_applied = true;
        }

        config = _initConfigDefault(config);

        // set up the element for editing
        _editor = document.getElementById(edit_elem_id);
        _editor.setAttribute("contenteditable", "true");
        _editor.setAttribute("spellcheck", config.spellcheck);
        _editor.setAttribute("udaeditor", "true");

        // listen to click event
        _editor.addEventListener("click", function (e) {
            // get all the applied format on the text block (div)
            _getAppliedFormatOnTextBlock(e.target);

        }, false);

        // listen to keydown event
        _editor.addEventListener("keydown", function (e) {
            _curr_editor_text_length = _editor.innerText.length;

            // apply default pass format
            if (!_default_format_applied) {
                _default_format_applied = true;
                _applyDefaultFormat(format);
            }

        }, false);

        // listen to keyup event
        _editor.addEventListener("keyup", function (e) {
            // call this function if input doesn't change content
            if (_editor.innerText.length == _curr_editor_text_length) {
                // get all the applied format on the text block (div)
                _getAppliedFormatOnTextBlock(window.getSelection().focusNode.parentElement);
            }

        }, false);

        // expose commands
        return {
            format: {
                fontName: _fontName,
                fontSize: _fontSize,
                bold: _bold,
                italic: _italic,
                underline: _underline,
                strikeThrough: _strikeThrough,
                foreColor: _foreColor,
                justifyLeft: _justifyLeft,
                justifyCenter: _justifyCenter,
                justifyRight: _justifyRight,
                justifyFull: _justifyFull,
                insertOrderedList: _insertOrderedList,
                insertUnorderedList: _insertUnorderedList,
                indent: _indent,
                outdent: _outdent,
                removeFormat: _removeFormat
            },
            getContent: _getContent,
            attachBlockFormatListener: _attachBlockFormatListener,
            removeBlockFormatListener: _removeBlockFormatListener,

            // constants
            DEFAULT: _DEFAULT,
            MAIL: _MAIL,
            PLAIN: _PLAIN
        };
    };
})()