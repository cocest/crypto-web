<?php 

// start session
session_start();

// import all the necessary liberaries
require_once '../../../includes/config.php';
require_once '../../../includes/utils.php'; // include utility liberary

// error handler function
function customError($errno, $errstr) {
    echo "<b>Error:</b> [$errno] $errstr<br>";
    die();
}

// set the handler
set_error_handler('customError');

// generate CSRF token
$csrf_token = randomText('hexdec', 16);

// add the CSRF token to session
$_SESSION["csrf_token"] = $csrf_token;

// get user chose package
$chose_package_id = sanitiseInput($_GET['package_id']); // filter this data first

// mysql configuration
$db = $config['db']['mysql'];
        
// enable mysql exception
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

// fetch result for page rendering
$data_for_page_rendering = null;

try {
    // connect to database
    $conn = new mysqli($db['host'], $db['username'], $db['password'], $db['dbname']);

    //check connection
    if ($conn->connect_error) {
        throw new mysqli_sql_exception('Database connection failed: '.$conn->connect_error);
    }

    // check if user choose trial package
    if ($chose_package_id == 1) {
        // check if user haven't invest before
        $query = 'SELECT 1 FROM user_invested_package_records WHERE userID = ? LIMIT 1';
        $stmt = $conn->prepare($query); // prepare statement
        $stmt->bind_param('i', $_SESSION['user_id']);
        $stmt->execute();
        $stmt->store_result(); // needed for num_rows

        if ($stmt->num_rows > 0) {
            // close database connection
            $stmt->close();
            $conn->close();

            // redirect user back to packages page
            header('Location: packages.html');
            exit;
        }
    }

    // fetch package from database
    $query = "SELECT * FROM crypto_investment_packages WHERE id = ? LIMIT 1";
    $stmt = $conn->prepare($query); // prepare statement
    $stmt->bind_param('i', $chose_package_id);
    $stmt->execute();
    $result = $stmt->get_result();

    while ($row = $result->fetch_assoc()) {
        $data_for_page_rendering = $row;
    }

    $stmt->close();

    // close database connection
    $conn->close();

} catch (mysqli_sql_exception $e) {
    echo 'Mysql error: ' . $e->getMessage() . PHP_EOL;
    
} catch (Exception $e) { // catch other exception
    echo 'Caught exception: ' .  $e->getMessage() . PHP_EOL;
}

// set page left menu active menu
// Note: remeber to set this variable before you include "page_left_menu.php"
$left_menu_active_links = [
    'my_investment' => false,
    'packages' => true,
    'testimony' => false,
    'profile' => false,
    'settings' => false
];

// assemble all the part of the page
require_once 'header.php';
require_once 'page_left_menu.php';

?>

    <div class="page-content-cont">
        <!--payment details dialog-->
        <div id="payment-win-cont" class="remove-elem">
            <div class="title-bar-cont">
                <div class="title">Payment details</div>
                <div class="close-btn ux-f-rd-corner" onclick="closeActiveWin('payment-win-cont')">
                    <img src="../../images/icons/notification_icons.png" />
                </div>
            </div>
            <div class="body-cont">
                <p>
                    Send the exact amount to the address shown below, or you can scan 
                    the QR code with a cryptocurrency payment app to make payment.
                </p>
                <div class="payment-details-tbl-cont">
                    <table class="payment-details-tbl">
                        <tr>
                            <td>Amount:</td>
                            <td class="payment-amount"></td>
                        </tr>
                        <tr>
                            <td>Address:</td>
                            <td class="payment-address"></td>
                        </tr>
                    </table>
                </div>
                <div class="payment-qr-code-cont">
                    <img class="payment-qr-code" />
                    <div class="qr-text">Scan code to make payment</div>
                </div>
                <div class="payment-sent-btn-cont">
                    <a class="payment-sent-btn" href="#" target="_blank">Payment sent</a>
                </div>
            </div>
        </div>

        <h1 class="page-title-hd">Make Payment</h1>
        <div class="payment-pkg-sec-1">
            <h4 class="section-group-header">Investment</h4>
            <div class="inv-cont">
                <div class="inv-img-cont pkg-<?php echo $data_for_page_rendering['id']; ?>">
                    <img src='../../images/icons/package_icon_sprint.png' />
                </div>
                <div class="inv-name-cont">
                    <h2><?php echo $data_for_page_rendering['package']; ?></h2>
                </div>
            </div>
            <div class="inv-feature-list-cont">
                <h3>Features & benefits</h3>
                <ul class="inv-feature-list">
                    <li>
                        <?php echo intval($data_for_page_rendering['monthlyROI']); ?>% return of investment (ROI).
                    </li>
                    <li>
                        <?php echo $data_for_page_rendering['bonus'] == 0 ? 'No' : intval($data_for_page_rendering['bonus']).'%'; ?> investment bonus.
                    </li>
                    <li>
                        Investment matured after <?php echo $data_for_page_rendering['durationInMonth']; ?> month.
                    </li>
                    <?php 
                        if ($data_for_page_rendering['withdrawInvestment'] == 0) {
                    ?>
                    <li>You can only withdraw profit, capital is rolled over to next package.</li>
                    <?php 
                        }
                    ?>
                </ul>
            </div>
        </div>
        <div class="payment-method-sec-2">
            <h4 class="section-group-header">Payment Method</h4>
            <form name="payment-form" onsubmit="return processPaymentForm(event)" autocomplete="off" novalidate>
                <div class="select-crypto-cont">
                    <div class="select-input-descr">
                        Please, select cryptocurrency you want to use for payment below:
                    </div>
                    <div class="crypto-currency-cont">
                        <input id="btc-crypto-input" type="radio" name="currency" value="BTC" checked />
                        <label for="btc-crypto-input">
                            <div class="marker"></div>
                            <img class="crypto-icon" src="../../images/icons/bitcoin_icon.png" alt="bitcoin" />
                            <div class="crypt-name">BTC</div>
                        </label>
                        <input id="eth-crypto-input" type="radio" name="currency" value="ETH" />
                        <label for="eth-crypto-input">
                            <div class="marker"></div>
                            <img class="crypto-icon" src="../../images/icons/ethereum_icon.png" alt="ethereum" />
                            <div class="crypt-name">ETH</div>
                        </label>
                        <input id="xrp-crypto-input" type="radio" name="currency" value="XRP" />
                        <label for="xrp-crypto-input">
                            <div class="marker"></div>
                            <img class="crypto-icon" src="../../images/icons/ripple_icon.png" alt="ripple" />
                            <div class="crypt-name">XRP</div>
                        </label>
                    </div>
                </div>
                <div class="crypto-input-cont">
                    <label for="crypto-amount">Amount in USD</label></br>
                    <input id="crypto-amount" type="number" name="amount"  attachevent />
                </div>
                <input type="hidden" name="package_id" value="<?php echo $chose_package_id; ?>" />
                <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>" />
                <div class="payment-proceed-btn-cont">
                    <input class="payment-proceed-btn" type="submit" value="Proceed" />
                </div>
                <div class="payment-anim-cont remove-elem">
                    <div class="vt-bars-anim-cont">
                        <div class="vt-bar-cont">
                            <div class="vt-bar-1"></div>
                        </div>
                        <div class="vt-bar-cont">
                            <div class="vt-bar-2"></div>
                        </div>
                        <div class="vt-bar-cont">
                            <div class="vt-bar-3"></div>
                        </div>
                    </div>
                    <div class="anim-txt">Processing...</div>
                </div>
            </form>
        </div>
        <script>
            window.processPaymentForm = function(e) {
                e.preventDefault(); // prevent default behaviour

                // check if any input is left empty or contain invalid data
                if (requiredInputLeftEmptyOrInvalid()) {
                    return false;
                }

                // get user filled form
                let form = document.forms["payment-form"];

                let req_url = '../../process_payment';
                let reg_form = new FormData(form);

                // hide proceed button and show processing animation
                document.querySelector('.payment-proceed-btn-cont').setAttribute("class", "payment-proceed-btn-cont remove-elem");
                document.querySelector('.payment-anim-cont').setAttribute("class", "payment-anim-cont");

                // disable input
                let input_elems = document.querySelectorAll('.crypto-input-cont > input');
                input_elems.forEach(function (elem) {
                    elem.disabled = true;
                });

                // send request to server
                window.ajaxRequest(
                    req_url,
                    reg_form,
                    { contentType: false },

                    // listen to response from the server
                    function (response) {
                        let response_data = JSON.parse(response);

                        // check if payment order is placed successfully
                        if (response_data.success) {
                            // show payment details window
                            let payment_elem = document.getElementById("payment-win-cont");
                            payment_elem.querySelector('.payment-amount').innerHTML = response_data.amount;
                            payment_elem.querySelector('.payment-address').innerHTML = response_data.wallet_address;
                            payment_elem.querySelector('.payment-qr-code').setAttribute("src", response_data.qrcode_url);
                            payment_elem.querySelector('.payment-sent-btn').setAttribute("href", response_data.status_url)
                            payment_elem.removeAttribute("class");

                        } else { // order can't be place due to error
                            // show error message to user
                            let msg_elem = document.getElementById("msg-win-cont");
                            msg_elem.querySelector('.title').innerHTML = "Payment Error";
                            msg_elem.querySelector('.body-cont').innerHTML = response_data.error_msg;
                            msg_elem.removeAttribute("class");
                        }

                        // show proceed button and hide processing animation
                        document.querySelector('.payment-proceed-btn-cont').setAttribute("class", "payment-proceed-btn-cont");
                        document.querySelector('.payment-anim-cont').setAttribute("class", "payment-anim-cont remove-elem");

                        // enable input
                        let input_elems = document.querySelectorAll('.crypto-input-cont > input');
                        input_elems.forEach(function (elem) {
                            elem.disabled = false;
                        });
                    },

                    // listen to server error
                    function (err_status) {
                        //check if is a timeout or server busy
                        if (err_status == 408 ||
                            err_status == 504 ||
                            err_status == 503) {

                            window.processPaymentForm(e);

                        } else {
                            // show proceed button and hide processing animation
                            document.querySelector('.payment-proceed-btn-cont').setAttribute("class", "payment-proceed-btn-cont");
                            document.querySelector('.payment-anim-cont').setAttribute("class", "payment-anim-cont remove-elem");

                            // enable input
                            let input_elems = document.querySelectorAll('.crypto-input-cont > input');
                            input_elems.forEach(function (elem) {
                                elem.disabled = false;
                            });

                            // show error message to user
                            let msg_elem = document.getElementById("msg-win-cont");
                            msg_elem.querySelector('.title').innerHTML = "Error";
                            msg_elem.querySelector('.body-cont').innerHTML = 
                                "Transanction can't be processed due to error. Please, check your connection and try again.";
                            msg_elem.removeAttribute("class");
                        }
                    }
                );
            };

            // utility function to validate user's input
            function requiredInputLeftEmptyOrInvalid() {
                let input = document.getElementById("crypto-amount");

                if (!/^([0-9]+|[0-9]+.?[0-9]+)$/.test(input.value)) {
                    // underline the input
                    input.setAttribute("style", "border: 1px solid #ff7878;");

                    return true;
                }

                return false;
            }

            // process events for form input
            function processInputEvents(e) {
                let input_elem = e.target; // get element that fire the event

                switch (e.type) {
                    case "keyup":
                        // remove the red underline
                        input_elem.removeAttribute("style");

                    default:
                        // you don't suppose to be here
                }
            }

            // attach event listener to input or select element
            function attachEventsToInputs(input_elements) {
                let attach_event = false;

                for (let i = 0; i < input_elements.length; i++) {
                    attach_event = input_elements[i].getAttribute("attachevent") == null ? false : true;
                    // check type of element
                    if (attach_event) {
                        input_elements[i].addEventListener("keyup", processInputEvents, false);
                    }
                }
            }

            // get all the input element to attach events
            let inputs = document.getElementsByTagName("input");
            attachEventsToInputs(inputs);

            // listen for payment sent click event
            document.querySelector('.payment-sent-btn').onclick = function (e) {
                // close window
                document.getElementById("payment-win-cont").setAttribute("class", "remove-elem");
            };

        </script>

<?php

// page footer
require_once 'footer.php';

?>