<?php 

// start session
session_start();

// import all the necessary liberaries
require_once '../../../includes/config.php';
require_once '../../../includes/utils.php'; // include utility liberary

date_default_timezone_set('UTC');

// check if user is authenticated
if (isset($_SESSION['auth']) && $_SESSION['auth'] == true) {
    if (isset($_SESSION['last_auth_time']) && time() < $_SESSION['last_auth_time']) {
        // update the time
        $_SESSION['last_auth_time'] = time() + 1800; // expire in 30 minutes
    
    } else {
        // clear the user's login session
        unset($_SESSION['auth']);
        unset($_SESSION['user_id']);

        // redirect user to login pages
        header('Location: '. BASE_URL . 'user/login.html');
        exit;
    }

} else {
    // redirect user to login pages
    header('Location: '. BASE_URL . 'user/login.html');
    exit;
}

// error handler function
function customError($errno, $errstr) {
    echo "<b>Error:</b> [$errno] $errstr<br>";
    die();
}

// set the handler
set_error_handler('customError');

// mysql configuration
$db = $config['db']['mysql'];
        
// enable mysql exception
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

// fetch result for page rendering
$data_for_page_rendering = [];

try {
    // connect to database
    $conn = new mysqli($db['host'], $db['username'], $db['password'], $db['dbname']);

    //check connection
    if ($conn->connect_error) {
        throw new mysqli_sql_exception('Database connection failed: '.$conn->connect_error);
    }

    // check if user has activated his account
    $query = 'SELECT accountActivated FROM users WHERE id = ? LIMIT 1';
    $stmt = $conn->prepare($query); // prepare statement
    $stmt->bind_param('i', $_SESSION['user_id']);
    $stmt->execute();
    $stmt->bind_result($account_activated);
    $stmt->fetch();
    $stmt->close();

    if ($account_activated == 0) {
        // account not yet activated
        $conn->close(); // close connection

        // redirect user
        header('Location: '. BASE_URL . 'user/home/email_verification.html');
        exit;
    }

    // get user's testimonies
    $query = 'SELECT * FROM user_testimonies WHERE userID = ?';
    $stmt = $conn->prepare($query); // prepare statement
    $stmt->bind_param('i', $_SESSION['user_id']);
    $stmt->execute();
    $result = $stmt->get_result();

    while ($row = $result->fetch_assoc()) {
        $data_for_page_rendering[] = $row;
    }

    $stmt->close();

    // close connection to database
    $conn->close();

} catch (mysqli_sql_exception $e) {
    // log the error to a file
    error_log('Mysql error: '.$e->getMessage().PHP_EOL, 3, CUSTOM_ERR_DIR.'custom_errors.log');
    
} catch (Exception $e) { // catch other exception
    // log the error to a file
    error_log('Caught exception: '.$e->getMessage().PHP_EOL, 3, CUSTOM_ERR_DIR.'custom_errors.log');
}

// set page left menu active menu
// Note: remeber to set this variable before you include "page_left_menu.php"
$left_menu_active_links = [
    'my_investment' => false,
    'packages' => false,
    'testimony' => true,
    'profile' => false,
    'settings' => false
];

// assemble all the part of the page
require_once 'header.php';
require_once 'page_left_menu.php';

?>

    <div class="page-content-cont">
        <h1 class="page-title-hd">Reviews</h1>
        <div class="testimony-sec-1">
            <h4 class="section-group-header">List of Reviews</h4>
            <div class="testimony-list-cont">
                <div id="testimony-list-wrapper">
                    <?php
                        for ($i = 0; $i < count($data_for_page_rendering); $i++) {
                    ?>
                    <div id="<?php echo $data_for_page_rendering[$i]['id']; ?>" class="testimony-item">
                        <div class="top-bar-cont">
                            <div class="delete-btn-cont" title="Delete testimony." onclick="deleteTestimony(<?php echo $data_for_page_rendering[$i]['id']; ?>)">
                                <img src="../../images/icons/icons_sprite_2.png" />
                            </div>
                        </div>
                        <div class="msg-body">
                            <?php echo $data_for_page_rendering[$i]['testimoney']; ?>
                        </div>
                        <div class="footer-bar-cont">
                            <div class="write-time-label"><?php echo date("m/d/y g:i A", $data_for_page_rendering[$i]['time']); ?></div>
                        </div>
                    </div>
                    <?php 
                        }

                        if (count($data_for_page_rendering) < 1) {
                    ?>
                    <div id="no-testimony-msg">
                        No personal review
                    </div>
                    <?php 
                        }
                    ?>
                </div>
            </div>
            <div class="testimony-text-input-cont">
                <form name="publish-testimony-form" onsubmit="return publishTestimonyForm(event)">
                    <div class="textarea-cont">
                        <label for="testimony-textarea">
                            <span id="text_input_counter">0</span> / 1000
                        </label>
                        <textarea id="testimony-textarea" name="testimony" placeholder="Write your Review"></textarea>
                    </div>
                    <div class="publish-btn-cont">
                        <button class="testimony-publish-btn" type="submit">Publish</button>
                    </div>
                </form>
            </div>
        </div>
        <script>
            // utility function that create the testimony element
            function createTestimonyElem(data) {
                testimony_date = new Date(parseInt(data.time) * 1000);

                let item = document.createElement("div");
                item.setAttribute("id", data.testimony_id);
                item.setAttribute("class", "testimony-item");
                item.innerHTML = 
                    `<div class="top-bar-cont">
                        <div class="delete-btn-cont" title="Delete testimony." onclick="deleteTestimony(${data.testimony_id})">
                            <img src="../../images/icons/icons_sprite_2.png">
                        </div>
                     </div>
                     <div class="msg-body">${data.testimony}</div>
                     <div class="footer-bar-cont">
                        <div class="write-time-label">${testimony_date.getMonth() + 1}/${testimony_date.getDate()}/${testimony_date.getFullYear()} ${window.toSTDTimeString(testimony_date, false)}</div>
                     </div>`;

                return item;
            }

            // delete user's testimony from database
            window.deleteTestimony = function (testimony_id) {
                let list_cont_elem = document.getElementById("testimony-list-wrapper");
                let req_url = '../../request';
                let form_data = "req=delete_testimony&id=" + testimony_id;

                // send request to server
                window.ajaxRequest(
                    req_url,
                    form_data,
                    { contentType: "application/x-www-form-urlencoded" },

                    // listen to response from the server
                    function (response) {
                        // remove the testimony from the list
                        list_cont_elem.removeChild(document.getElementById(testimony_id));

                        // check if is a last testimony in the list
                        if (list_cont_elem.childElementCount < 2) {
                            document.getElementById("no-testimony-msg").removeAttribute("class");
                        }
                    },

                    // listen to server error
                    function (err_status) {
                        // leave it empty
                    }
                );
            };

            // submit testimony to server
            window.publishTestimonyForm = function (e) {
                e.preventDefault(); // prevent default behaviour

                // get user filled form
                let form = document.forms["publish-testimony-form"];

                // check if textarea is empty
                if (/^[ ]*$/.test(form.elements["testimony"])) {
                    return false;
                }

                let req_url = '../../publish_testimony';
                let reg_form = new FormData(form);

                // disable the textarea and submit button
                document.querySelector('#testimony-textarea').disabled = true;
                document.querySelector('.testimony-publish-btn').disabled = true;

                // send request to server
                window.ajaxRequest(
                    req_url,
                    reg_form,
                    { contentType: false },
                    
                    // listen to response from the server
                    function (response) {
                        let response_data = JSON.parse(response);

                        // check if publication was successfully
                        if (response_data.success) {
                            // clear textarea and reset text input counter
                            document.getElementById("testimony-textarea").value = "";
                            document.getElementById("text_input_counter").innerHTML = "0";

                            // get testimony list container
                            let list_cont_elem = document.getElementById("testimony-list-wrapper");

                            // create testimony and add it to the list
                            list_cont_elem.appendChild(createTestimonyElem(response_data));

                            // remove no testimony message
                            let elem = document.getElementById("no-testimony-msg");
                            if (elem != null) {
                                elem.setAttribute("class", "remove-elem");
                            }
                        }

                        // enable the textarea and submit button
                        document.querySelector('#testimony-textarea').disabled = false;
                        document.querySelector('.testimony-publish-btn').disabled = false;
                    },

                    // listen to server error
                    function (err_status) {
                        //check if is a timeout or server busy
                        if (err_status == 408 ||
                            err_status == 504 ||
                            err_status == 503) {

                            window.publishTestimonyForm(e);

                        } else {
                            // enable the textarea and submit button
                            document.querySelector('#testimony-textarea').disabled = false;
                            document.querySelector('.testimony-publish-btn').disabled = false;
                        }
                    }
                );
            };

            // get textarea element
            let testimony_text_area = document.getElementById("testimony-textarea");

            // listen for keyup on textarea
            testimony_text_area.onkeyup = function (e) {
                let max_txt = 1000;
                let entered_txt = e.target.value;
                // check if entered text has exceed allowed number of characters
                if (entered_txt.length > max_txt) {
                    // clipout the excess text
                    e.target.value = entered_txt.substring(0, max_txt);

                    // update user's text input counter
                    document.getElementById("text_input_counter").innerHTML = entered_txt.length - 1;

                } else {
                    // update user's text input counter
                    document.getElementById("text_input_counter").innerHTML = entered_txt.length;
                }
            };

            // listen for input on textarea and expand area as user type
            testimony_text_area.oninput = function (e) {
                let elem = e.target;
                elem.style.height = 'auto';
                // Customize if you want
                elem.style.height = (elem.scrollHeight - 1) + 'px'; //The weight is 30
            };
        </script>

<?php

// page footer
require_once 'footer.php';

?>