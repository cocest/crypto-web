<?php 

//

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
            <div>
                <div>
                    <img src='img_here.png' />
                </div>
                <div>
                    <h2>Trial</h2>
                </div>
            </div>
            <div>
                <ul>
                    <li></li>
                    <li></li>
                    <li></li>
                </ul>
            </div>
        </div>
        <div class="payment-method-sec-2">
            <h4 class="section-group-header">Payment Method</h4>
        </div>

<?php

// page footer
require_once 'footer.php';

?>