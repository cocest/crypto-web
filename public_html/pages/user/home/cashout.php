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

// generate CSRF token
$csrf_token = randomText('hexdec', 16);

// add the CSRF token to session
$_SESSION["csrf_token"] = $csrf_token;

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
$data_for_page_rendering = null;

try {
    // connect to database
    $conn = new mysqli($db['host'], $db['username'], $db['password'], $db['dbname']);

    //check connection
    if ($conn->connect_error) {
        throw new mysqli_sql_exception('Database connection failed: ' . $conn->connect_error);
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

    // get user's account
    $query = 'SELECT totalBalance, availableBalance FROM user_account WHERE userID = ? LIMIT 1';
    $stmt = $conn->prepare($query); // prepare statement
    $stmt->bind_param('i', $_SESSION['user_id']);
    $stmt->execute();
    $stmt->bind_result($total_balance, $available_balance);
    $stmt->fetch();
    $stmt->close();

    // append result
    $data_for_page_rendering = [
        'total_balance' => $total_balance,
        'available_balance' => $available_balance
    ];

    $conn->close(); // close connection

} catch (mysqli_sql_exception $e) {
    echo 'Mysql error: ' . $e->getMessage() . PHP_EOL;
    
} catch (Exception $e) { // catch other exception
    echo 'Caught exception: ' .  $e->getMessage() . PHP_EOL;
}

// set page left menu active menu
// Note: remeber to set this variable before you include "page_left_menu.php"
$left_menu_active_links = [
    'my_investment' => true,
    'packages' => false,
    'testimony' => false,
    'profile' => false,
    'settings' => false
];

// assemble all the part of the page
require_once 'header.php';
require_once 'page_left_menu.php';

?>

    <div class="page-content-cont">
        <h1 class="page-title-hd">Cashout</h1>
        <div class="cashout-inv-sec-1">
            <h4 class="section-group-header">My Account</h4>
            <div class="cashout-account-tbl-cont">
                <table id="cashout-account-tbl">
                    <tr>
                        <td>Total Balance:</td>
                        <td id="total-bal">
                            <?php echo number_format($data_for_page_rendering['total_balance'], 2) . ' USD'; ?>
                        </td>
                    </tr>
                    <tr>
                        <td>Available Balance:</td>
                        <td id="available-bal">
                            <?php echo number_format($data_for_page_rendering['available_balance'], 2) . ' USD'; ?>
                        </td>
                    </tr>
                </table>
            </div>
        </div>
        <div class="cashout-inv-sec-2">
            <h4 class="section-group-header">Withdrawal Details</h4>
            <form name="cashout-form" onsubmit="return processCashoutForm(event)" autocomplete="off" novalidate>
                <div class="select-crypto-cont">
                    <div class="select-input-descr">
                        Please select cryptocurrency of choice payments should be made in below:
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
                    <label for="crypto-amount">Amount in USD (available balance)</label></br>
                    <input id="crypto-amount" type="number" name="amount" min="0"  attachevent />
                </div>
                <div class="crypto-input-cont">
                    <label for="crypto-wallet-address">Wallet Address</label></br>
                    <input id="crypto-wallet-address" type="text" name="walletaddress" attachevent />
                </div>
                <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                <div class="cashout-proceed-btn-cont">
                    <input class="cashout-proceed-btn" type="submit" value="Proceed" />
                </div>
                <div class="cashout-anim-cont remove-elem">
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
            window.processCashoutForm = function(e) {
                e.preventDefault(); // prevent default behaviour

                // regex to validate each crypto currency
                let regex_crypto_validator = {
                    'BTC': /^[13][a-km-zA-HJ-NP-Z1-9]{25,34}$/,
                    'ETH': /^(0x)?[0-9a-fA-F]{40}$/,
                    'XRP': /^r[0-9a-zA-Z]{24,34}$/
                };

                // get user filled form
                let form = document.forms["cashout-form"];

                // check if any input is left empty or contain invalid data
                if (requiredInputLeftEmptyOrInvalid(
                    [
                        {name: 'amount', regex: /^([0-9]+|[0-9]+.?[0-9]+)$/}, 
                        {name: 'walletaddress', regex: regex_crypto_validator[form.elements['currency'].value]}
                    ])
                    ) {
                    return false;
                }

                let req_url = '../../process_withdrawal';
                let reg_form = new FormData(form);

                // hide proceed button and show processing animation
                document.querySelector('.cashout-proceed-btn-cont').setAttribute("class", "cashout-proceed-btn-cont remove-elem");
                document.querySelector('.cashout-anim-cont').setAttribute("class", "cashout-anim-cont");

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

                        // check if withdraw order is placed successfully
                        if (response_data.success) {
                            // update display available balance
                            document.getElementById("available-bal").innerHTML = response_data.available_balance + ' USD';

                            // show message to client
                            let msg_elem = document.getElementById("msg-win-cont");
                            msg_elem.querySelector('.title').innerHTML = "Withdrawal";
                            msg_elem.querySelector('.body-cont').innerHTML = 
                                "Withraw order has been placed successfully. You will receive notification once the order is completed.";
                            msg_elem.removeAttribute("class");

                        } else { // order can't be place due to error
                            // show error message to user
                            let msg_elem = document.getElementById("msg-win-cont");
                            msg_elem.querySelector('.title').innerHTML = "Withdrawal Error";
                            msg_elem.querySelector('.body-cont').innerHTML = response_data.error_msg;
                            msg_elem.removeAttribute("class");
                        }

                        // show proceed button and hide processing animation
                        document.querySelector('.cashout-proceed-btn-cont').setAttribute("class", "cashout-proceed-btn-cont");
                        document.querySelector('.cashout-anim-cont').setAttribute("class", "cashout-anim-cont remove-elem");

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

                            window.processCashoutForm(e);

                        } else {
                            // show proceed button and hide processing animation
                            document.querySelector('.cashout-proceed-btn-cont').setAttribute("class", "cashout-proceed-btn-cont");
                            document.querySelector('.cashout-anim-cont').setAttribute("class", "cashout-anim-cont remove-elem");

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
            function requiredInputLeftEmptyOrInvalid(input_name_and_regex) {
                let login_form = document.forms["cashout-form"];

                for (let i = 0; i < input_name_and_regex.length; i++) {
                    let input = login_form.elements[input_name_and_regex[i].name];

                    if (/^[ ]*$/.test(input.value)) {
                        // underline the input
                        input.setAttribute("style", "border: 1px solid #ff7878;");

                        return true;

                    } else if (!input_name_and_regex[i].regex.test(input.value)) {
                        // show the input error message
                        input.setAttribute("style", "border: 1px solid #ff7878;");

                        return true;
                    }
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

            // reset wallet address input error
            document.querySelectorAll('.crypto-currency-cont > input').forEach(function (elem) {
                // remove red border line
                elem.onclick = function (e) {
                    document.getElementById("crypto-wallet-address").removeAttribute("style");
                };
            });

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
        </script>
<?php

// page footer
require_once 'footer.php';

?>