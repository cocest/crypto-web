<?php 

// start session
session_start();

// import all the necessary liberaries
require_once '../../../includes/config.php';
require_once '../../../includes/utils.php'; // include utility liberary

// generate CSRF token
$csrf_token = randomText('hexdec', 16);

// add the CSRF token to session
$_SESSION["csrf_token"] = $csrf_token;

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
        <h1 class="page-title-hd">Make Payment</h1>
        <div class="payment-pkg-sec-1">
            <h4 class="section-group-header">Investment</h4>
            <div class="inv-cont">
                <div class="inv-img-cont pkg-1">
                    <img src='../../images/icons/package_icon_sprint.png' />
                </div>
                <div class="inv-name-cont">
                    <h2>Trial</h2>
                </div>
            </div>
            <div class="inv-feature-list-cont">
                <h3>Features & benefits</h3>
                <ul class="inv-feature-list">
                    <li>10% return of investment (ROI).</li>
                    <li>No investment bonus.</li>
                    <li>Investment matured after 2 month.</li>
                    <li>You can only withdraw profit, capital is rolled over to next package.</li>
                </ul>
            </div>
        </div>
        <div class="payment-method-sec-2">
            <h4 class="section-group-header">Payment Method</h4>
            <form name="cashout-form" onsubmit="return processPaymentForm(event)" autocomplete="off" novalidate>
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
                    <input id="crypto-amount" type="number" name="amount" min="1000" max="4000"  attachevent />
                </div>
                <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
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
                // start here
            };
        </script>

<?php

// page footer
require_once 'footer.php';

?>