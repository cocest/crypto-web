<?php 

// start session
session_start();

// import all the necessary liberaries
require_once '../../includes/config.php';
require_once '../../includes/utils.php'; // include utility liberary

// generate CSRF token
$csrf_token = randomText('hexdec', 16);

// add the CSRF token to session
$_SESSION["csrf_token"] = $csrf_token;

date_default_timezone_set('UTC');

// check if user is authenticated
if (isset($_SESSION['admin_auth']) && $_SESSION['admin_auth'] == true) {
    if (isset($_SESSION['admin_last_auth_time']) && time() < $_SESSION['admin_last_auth_time']) {
        // update the time
        $_SESSION['admin_last_auth_time'] = time() + 1800; // expire in 30 minutes
    
    } else {
        // clear the user's login session
        unset($_SESSION['admin_auth']);

        // redirect user to login page
        header('Location: '. BASE_URL . 'admin/login.html');
        exit;
    }

} else {
    // redirect user to login page
    header('Location: '. BASE_URL . 'admin/login.html');
    exit;
}

// mysql configuration
$db = $config['db']['mysql'];
        
// enable mysql exception
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

// fetch result for page rendering
$data_for_page_rendering = [
    'investment_packages' => null
];

try {
    // connect to database
    $conn = new mysqli($db['host'], $db['username'], $db['password'], $db['dbname']);

    //check connection
    if ($conn->connect_error) {
        throw new mysqli_sql_exception('Database connection failed: '.$conn->connect_error);
    }

    // fetch investment packages
    $query = 'SELECT * FROM crypto_investment_packages';
    $stmt = $conn->prepare($query); // prepare statement
    $stmt->execute();
    $result = $stmt->get_result();

    $records = [];

    while ($row = $result->fetch_assoc()) {
        $records[] = [
            'id' =>  $row['id'],
            'package' => $row['package'],
            'min_amount' => '$' . number_format(round($row['minAmount'], 2, PHP_ROUND_HALF_DOWN), 2),
            'max_amount' => '$' . number_format(round($row['maxAmount'], 2, PHP_ROUND_HALF_DOWN), 2),
            'duration' => $row['durationInMonth'],
            'roi' => round($row['monthlyROI'], 2, PHP_ROUND_HALF_DOWN) . '%',
            'bonus' => round($row['bonus'], 2, PHP_ROUND_HALF_DOWN) . '%',
            'withdraw_percent' => round($row['withdrawInvestmentPercent'], 2, PHP_ROUND_HALF_DOWN) . '%'
        ];
    }

    $data_for_page_rendering['investment_packages'] = $records;
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
$side_menu_active_links = [
    'dashboard' => false,
    'users' => false,
    'testimony' => false,
    'settings' => true
];

// assemble all the part of the page
require_once 'admin_header.php';
require_once 'admin_side_menu.php';

?>

    <div class="page-content-cont">
        <?php require_once 'admin_top_main_menu.php' ?>
        <div class="page-content">
            <h1 class="page-title-hd">Settings</h1>
            <div class="tab-menu-cont">
                <ul>
                    <li class="tab-menu active">
                        <a href="./settings.html">Packages</a>
                    </li>
                </ul>
            </div>
            <div id="section-cont-1">
                <!--Package edit window-->
                <div id="edit-package-win" class="hide">
                    <div class="edit-package-wrapper">
                        <div class="title-bar-cont">
                            <div class="title">Edit package (Copper)</div>
                            <button class="close-btn" onclick="closeEditPackageWin()">
                                <i class="fas fa-times"></i>
                            </button>
                        </div>
                        <div class="body-cont">
                            <form name="edit-package-form" onsubmit="return saveEditedPackage(event)">
                                <div class="edit-package-input-cont">
                                    <div class="label-cont">
                                        <label class="label" for="min-amount-input">Minimum Amount</label>
                                    </div>
                                    <div class="input-cont">
                                        <input class="input" id="min-amount-input" type="number" name="minamount" min="0" />
                                    </div>
                                </div>
                                <div class="edit-package-input-cont">
                                    <div class="label-cont">
                                        <label class="label" for="max-amount-input">Maximum Amount</label>
                                    </div>
                                    <div class="input-cont">
                                        <input class="input" id="max-amount-input" type="number" name="maxamount" min="0" />
                                    </div>
                                </div>
                                <div class="edit-package-input-cont">
                                    <div class="label-cont">
                                        <label class="label" for="duration-input">Duration</label>
                                    </div>
                                    <div class="input-cont">
                                        <input class="input" id="duration-input" type="number" name="duration" min="0" />
                                    </div>
                                </div>
                                <div class="edit-package-input-cont">
                                    <div class="label-cont">
                                        <label class="label" for="roi-input">ROI</label>
                                    </div>
                                    <div class="input-cont">
                                        <input class="input" id="roi-input" type="number" name="roi" min="0" />
                                    </div>
                                </div>
                                <div class="edit-package-input-cont">
                                    <div class="label-cont">
                                        <label class="label" for="bonus-input">Bonus</label>
                                    </div>
                                    <div class="input-cont">
                                        <input class="input" id="bonus-input" type="number" name="bonus" min="0" />
                                    </div>
                                </div>
                                <div class="edit-package-input-cont">
                                    <div class="label-cont">
                                        <label class="label" for="withdraw-percent-input">Withdraw Percent</label>
                                    </div>
                                    <div class="input-cont">
                                        <input class="input" id="withdraw-percent-input" type="number" name="withdrawpercent" min="0" />
                                    </div>
                                </div>
                                <div class="csrf-input-cont">
                                    <input type="text" name="csrf_token" value="<?php echo $csrf_token; ?>">
                                </div>
                                <div class="edit-package-footer-input-cont">
                                    <button class="confirm-btn" type="submit">Save</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
                <?php if ($data_for_page_rendering['investment_packages']) { ?>
                <div class="package-list-cont">
                    <div class="package-list-wrapper">
                        <table class="package-list">
                            <tr>
                                <th>Name</th>
                                <th>Min Amount</th>
                                <th>Max Amount</th>
                                <th>Duration</th>
                                <th>ROI</th>
                                <th>Bonus</th>
                                <th>Withdraw Percent</th>
                                <th></th>
                            </tr>
                            <?php 
                                for ($i = 0; $i < count($data_for_page_rendering['investment_packages']); $i++) { 
                                    $package_id = $data_for_page_rendering['investment_packages'][$i]['id'];
                            ?>
                            <tr>
                                <td><?php echo $data_for_page_rendering['investment_packages'][$i]['package']; ?></td>
                                <td id="td-min-amount-<?php echo $package_id; ?>"><?php echo $data_for_page_rendering['investment_packages'][$i]['min_amount']; ?></td>
                                <td id="td-max-amount-<?php echo $package_id; ?>"><?php echo $data_for_page_rendering['investment_packages'][$i]['max_amount']; ?></td>
                                <td id="td-duration-<?php echo $package_id; ?>"><?php echo $data_for_page_rendering['investment_packages'][$i]['duration']; ?></td>
                                <td id="td-roi-<?php echo $package_id; ?>"><?php echo $data_for_page_rendering['investment_packages'][$i]['roi']; ?></td>
                                <td id="td-bonus-<?php echo $package_id; ?>"><?php echo $data_for_page_rendering['investment_packages'][$i]['bonus']; ?></td>
                                <td id="td-withdraw-percent-<?php echo $package_id; ?>"><?php echo $data_for_page_rendering['investment_packages'][$i]['withdraw_percent']; ?></td>
                                <td>
                                    <button class="package-edit-btn" title="Edit package" onclick="editInvestmentPackage(<?php echo $package_id; ?>)">
                                        <i class="far fa-edit"></i>
                                    </button>
                                </td>
                            </tr>
                            <?php } ?>
                        </table>
                    </div>
                </div>
                <?php } else { ?>
                <div class="no-investment-pkg-msg">
                    No investment package
                </div>
                <?php } ?>
            </div>
        </div>
    </div>
    <script>
        function init() {
            // global variables here
            let package_data;
            let edited_package_id;
            let edit_package_win = document.getElementById("edit-package-win");

            function launchEditPackageWin(data) {
                // set window title 
                edit_package_win.querySelector('.title').innerHTML = "Edit package (" + data['package'] + ")";

                // set the package edit values
                edit_package_win.querySelector('#min-amount-input').setAttribute("value", parseFloat(data['min_amount']));
                edit_package_win.querySelector('#max-amount-input').setAttribute("value", parseFloat(data['max_amount']));
                edit_package_win.querySelector('#duration-input').setAttribute("value", parseFloat(data['duration']));
                edit_package_win.querySelector('#roi-input').setAttribute("value", parseFloat(data['roi']));
                edit_package_win.querySelector('#bonus-input').setAttribute("value", parseFloat(data['bonus']));
                edit_package_win.querySelector('#withdraw-percent-input').setAttribute("value", parseFloat(data['withdraw_percent']));

                // show th window
                edit_package_win.removeAttribute("class");
            }

            window.closeEditPackageWin = function () {
                document.getElementById("edit-package-win").setAttribute("class", "hide");
            };

            window.editInvestmentPackage = function (package_id) {
                edited_package_id = package_id;

                // fetch package records
                let req_url = '../request';
                let form_data = 'req=get_investment_package&id=' + package_id; // request query

                // send request to server
                window.ajaxRequest(
                    req_url,
                    form_data,
                    { contentType: "application/x-www-form-urlencoded" },

                    // listen to response from the server
                    function (response) {
                        package_data = JSON.parse(response);
                        launchEditPackageWin(package_data);
                    },

                    // listen to server error
                    function (err_status) {
                        // check if is a timeout or server busy
                        if (error_status == 408 ||
                            error_status == 504 ||
                            error_status == 503) {

                            // send the request again
                            window.editInvestmentPackage(package_id);
                        }
                    }
                );
            };

            window.saveEditedPackage = function (e) {
                e.preventDefault();

                let save_btn = edit_package_win.querySelector('.confirm-btn');
                save_btn.disabled = true; // disable the save button

                let req_url = '../update_investment_package';
                let form_data = new FormData();
                let form_input = document.forms["edit-package-form"].elements;

                // add package id
                form_data.append("id", edited_package_id);

                // add the CSRF token
                form_data.append("csrf_token", form_input["csrf_token"].value);

                // validate and set the value to be updated
                if (!isNaN(Number(form_input["minamount"].value)) && form_input["minamount"].value > -1) {
                    form_data.append("minamount", form_input["minamount"].value);
                    document.getElementById("td-min-amount-" + edited_package_id).innerHTML = "$" + window.seperateNumberBy(form_input["minamount"].value, ",");
                } else {
                    form_data.append("minamount", package_data["min_amount"]);
                    document.getElementById("td-min-amount-" + edited_package_id).innerHTML = "$" + window.seperateNumberBy(form_input["minamount"].value, ",");
                }

                if (!isNaN(Number(form_input["maxamount"].value)) && form_input["maxamount"].value > -1) {
                    form_data.append("maxamount", form_input["maxamount"].value);
                    document.getElementById("td-max-amount-" + edited_package_id).innerHTML = "$" + window.seperateNumberBy(form_input["maxamount"].value, ",");
                } else {
                    form_data.append("maxamount", package_data["max_amount"]);
                    document.getElementById("td-max-amount-" + edited_package_id).innerHTML = "$" + window.seperateNumberBy(form_input["maxamount"].value, ",");
                }

                if (!isNaN(Number(form_input["duration"].value)) && form_input["duration"].value > 0) {
                    form_data.append("duration", form_input["duration"].value);
                    document.getElementById("td-duration-" + edited_package_id).innerHTML = form_input["duration"].value;
                } else {
                    form_data.append("duration", package_data["duration"]);
                    document.getElementById("td-duration-" + edited_package_id).innerHTML = form_input["duration"].value;
                }

                if (!isNaN(Number(form_input["roi"].value)) && form_input["roi"].value > 0) {
                    form_data.append("roi", form_input["roi"].value);
                    document.getElementById("td-roi-" + edited_package_id).innerHTML = form_input["roi"].value + "%";
                } else {
                    form_data.append("roi", package_data["roi"]);
                    document.getElementById("td-roi-" + edited_package_id).innerHTML = form_input["roi"].value + "%";
                }

                if (!isNaN(Number(form_input["bonus"].value)) && form_input["bonus"].value > 0) {
                    form_data.append("bonus", form_input["bonus"].value);
                    document.getElementById("td-bonus-" + edited_package_id).innerHTML = form_input["bonus"].value + "%";
                } else {
                    form_data.append("bonus", package_data["bonus"]);
                    document.getElementById("td-bonus-" + edited_package_id).innerHTML = form_input["bonus"].value + "%";
                }

                if (!isNaN(Number(form_input["withdrawpercent"].value)) && form_input["withdrawpercent"].value > 0) {
                    form_data.append("withdrawpercent", form_input["withdrawpercent"].value);
                    document.getElementById("td-withdraw-percent-" + edited_package_id).innerHTML = form_input["withdrawpercent"].value + "%";
                } else {
                    form_data.append("withdrawpercent", package_data["withdraw_percent"]);
                    document.getElementById("td-withdraw-percent-" + edited_package_id).innerHTML = form_input["withdrawpercent"].value + "%";
                }

                // send update to server
                window.ajaxRequest(
                    req_url,
                    form_data,
                    { contentType: false },

                    // listen to response from the server
                    function (response) {
                        save_btn.disabled = false; // enable the save button

                        let response_data = JSON.parse(response);

                        // check if registeration was succesfull
                        if (response_data.success) {
                            edit_package_win.setAttribute("class", "hide");
                        }
                    },

                    // listen to server error
                    function (err_status) {
                        save_btn.disabled = false; // enable the save button
                    }
                );
            };
        }

        //initialise the script
        if (window.attachEvent) {
            window.attachEvent("onload", init);

        } else {
            window.addEventListener("load", init, false);
        }

    </script>
<?php

// page footer
require_once 'admin_footer.php';

?>