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
$data_for_page_rendering = [
    'current_investment' => null,
    'account' => null,
    'investment_statistics' => null,
    'invested_package_records' => null
];

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

    // get user's current investment
    $query = 
        'SELECT A.packageID, A.amountInvested, A.profitCollected, A.startTime, A.endTime, B.package, B.durationInMonth, B.monthlyROI, B.bonus 
         FROM user_current_investment AS A LEFT JOIN crypto_investment_packages AS B 
         ON A.packageID = B.id WHERE A.userID = ? LIMIT 1';
    $investment_stmt = $conn->prepare($query); // prepare statement
    $investment_stmt->bind_param('i', $_SESSION['user_id']);
    $investment_stmt->execute();
    $investment_result = $investment_stmt->get_result();

    if ($investment_row = $investment_result->fetch_assoc()) {
        // check if investment has due
        if (time() > $investment_row['endTime'] && $investment_row['profitCollected'] < 1) {
            // total revenue from current investment
            $bonus = $investment_row['amountInvested'] * ($investment_row['bonus'] / 100);
            $revenue_of_investment = $investment_row['amountInvested'] * ($investment_row['monthlyROI'] / 100);
            $revenue_of_investment = $revenue_of_investment + $bonus;

            try {
                $conn->begin_transaction(); // start transaction

                // update user's account
                $query = 'UPDATE user_account SET totalBalance = totalBalance + ?, availableBalance = availableBalance + ?, pendingRevenue = ? WHERE userID = ? LIMIT 1';
                $stmt = $conn->prepare($query); // prepare statement
                $stmt->bind_param('dddi', $revenue_of_investment, $revenue_of_investment, $pendingRevenue, $_SESSION['user_id']);
                $pendingRevenue = 0;
                $stmt->execute();
                $stmt->close();

                // update user's investment statistics
                $query = 'UPDATE user_investment_statistics SET totalInvestment = totalInvestment + ?, totalRevenue = totalRevenue + ? WHERE userID = ? LIMIT 1';
                $stmt = $conn->prepare($query); // prepare statement
                $stmt->bind_param('ddi', $investment_row['amountInvested'], $revenue_of_investment, $_SESSION['user_id']);
                $stmt->execute();
                $stmt->close();

                // add the invested package to user's records
                $query = 
                    'INSERT INTO user_invested_package_records (userID, packageID, ROI, amountInvested, revenue, duration, time)
                     VALUES(?, ?, ?, ?, ?, ?, ?)';

                $stmt = $conn->prepare($query); // prepare statement
                $stmt->bind_param(
                    'iidddii', 
                    $_SESSION['user_id'], 
                    $investment_row['packageID'], 
                    $investment_row['monthlyROI'], 
                    $investment_row['amountInvested'], 
                    $revenue_of_investment, 
                    $revenue_of_investment['durationInMonth'], 
                    $revenue_of_investment['startTime']
                );
                $stmt->execute();
                $stmt->close();

                // update user's investment
                $query = 'UPDATE user_current_investment SET profitCollected = ? WHERE userID = ? LIMIT 1';
                $stmt = $conn->prepare($query); // prepare statement
                $stmt->bind_param('ii', $profit_collected, $_SESSION['user_id']);
                $profit_collected = 1;
                $stmt->execute();
                $stmt->close();

                $conn->commit(); // commit all the transaction

            }  catch (Exception $e) {
                $conn->rollback(); // remove all queries from queue if error occured (undo)
            }

        } else {
            $current_profit = 
                (convertSecondsToDays(time() - $investment_row['startTime']) * ($investment_row['amountInvested'] * ($investment_row['monthlyROI'] / 100))) / 
                ($investment_row['durationInMonth'] * 30);
    
            $data_for_page_rendering['current_investment'] = 
                [
                    'amount_invested' => cladNumberFormat($investment_row['amountInvested']) . ' USD',
                    'date_of_investment' => date("M j, Y g:i A", $investment_row['startTime']),
                    'package' => $investment_row['package'],
                    'duration' => $investment_row['durationInMonth'] . ' month',
                    'roi' => floor($investment_row['monthlyROI']) . '%',
                    'bonus' => floor($investment_row['bonus']) . '%',
                    'current_profit' => cladNumberFormat($current_profit) . ' USD'
                ];
        }
    }

    $investment_stmt->close(); // close previous statement

    // get user's account 
    $query = 'SELECT totalBalance, availableBalance, pendingRevenue FROM user_account WHERE userID = ? LIMIT 1';
    $stmt = $conn->prepare($query); // prepare statement
    $stmt->bind_param('i', $_SESSION['user_id']);
    $stmt->execute();
    $stmt->bind_result($total_balance, $available_balance, $pending_revenue);
    $stmt->fetch();

    $data_for_page_rendering['account'] = 
        [
            'total_balance' => cladNumberFormat($total_balance) . ' USD',
            'available_balance' => cladNumberFormat($available_balance) . ' USD',
            'pending_revenue' => cladNumberFormat($pending_revenue) . ' USD'
        ];

    $stmt->close();

    // get user's statistics
    $query = 'SELECT totalInvestment, totalRevenue FROM user_investment_statistics WHERE userID = ? LIMIT 1';
    $stmt = $conn->prepare($query); // prepare statement
    $stmt->bind_param('i', $_SESSION['user_id']);
    $stmt->execute();
    $stmt->bind_result($total_investment, $total_revenue);
    $stmt->fetch();

    $data_for_page_rendering['investment_statistics'] = 
        [
            'total_investment' => cladNumberFormat($total_investment) . ' USD',
            'total_revenue' => cladNumberFormat($total_revenue) . ' USD'
        ];

    $stmt->close();

    // get user's invested package records
    $query = 'SELECT COUNT(*) AS total FROM user_invested_package_records WHERE userID = ?';
    $stmt = $conn->prepare($query); // prepare statement
    $stmt->bind_param('i', $_SESSION['user_id']);
    $stmt->execute();
    $stmt->bind_result($invested_package_count);
    $stmt->fetch();
    $stmt->close();

    $query = 
        'SELECT B.package, A.ROI, A.amountInvested, A.revenue, A.duration, A.time 
         FROM user_invested_package_records AS A LEFT JOIN crypto_investment_packages AS B 
         ON A.packageID = B.id WHERE userID = ? ORDER BY time LIMIT ?';
    $stmt = $conn->prepare($query); // prepare statement
    $stmt->bind_param('ii', $_SESSION['user_id'], $list_limit);
    $list_limit = 6;
    $stmt->execute();
    $result = $stmt->get_result();

    $data_for_page_rendering['invested_package_records'] = 
        [
            'page_count' => pageCountForListItem(6, $invested_package_count),
            'records' => null
        ];
    $records = [];

    while ($row = $result->fetch_assoc()) {
        $records[] = [
            'package' => $row['package'],
            'roi' => $rows['ROI'],
            'amount_invested' => $rows['amountInvested'],
            'revenue' => $rows['revenue'],
            'duration' => $rows['duration'],
            'time' => date("M j, Y g:i A", $rows['time'])
        ];
    }

    $data_for_page_rendering['invested_package_records']['records'] = $records;
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
        <h1 class="page-title-hd">My Investment</h1>
        <?php 
            if ($data_for_page_rendering['current_investment']) {
        ?>
        <div class="current-inv-sec-1">
            <h4 class="section-group-header">Current Investment</h4>
            <div class="current-inv-tbl-cont">
                <table class="current-inv-tbl">
                    <tr>
                        <td>Package:</td>
                        <td><?php echo $data_for_page_rendering['current_investment']['package']; ?></td>
                    </tr>
                    <tr>
                        <td>Date of Investment:</td>
                        <td><?php echo $data_for_page_rendering['current_investment']['date_of_investment']; ?></td>
                    </tr>
                    <tr>
                        <td>Duration:</td>
                        <td><?php echo $data_for_page_rendering['current_investment']['duration']; ?></td>
                    </tr>
                </table>
            </div>
            <div class="current-inv-stat-cont">
                <div class="current-inv-stat-wrapper ux-layout-grid columns-4">
                    <div class="grid-item">
                        <div class="amt-inv-cont">
                            <div class="data">
                                <?php echo $data_for_page_rendering['current_investment']['amount_invested']; ?>
                            </div>
                            <div class="title">Amount Invested</div>
                        </div>
                    </div>
                    <div class="grid-item">
                        <div class="roi-cont">
                            <div class="data">
                                <div class="figure">
                                    <?php echo $data_for_page_rendering['current_investment']['roi']; ?>
                                </div>
                                <img class="icon" src="../../images/icons/chart-arrow-up.png" />
                            </div>
                            <div class="title">Return of Investment</div>
                        </div>
                    </div>
                    <div class="grid-item">
                        <div class="mb-inv-cont">
                            <div class="data">
                                <?php echo $data_for_page_rendering['current_investment']['bonus']; ?>
                            </div>
                            <div class="title">Monthly Bonus</div>
                        </div>
                    </div>
                    <div class="grid-item">
                        <div class="cp-inv-cont">
                            <div class="data">
                                <?php echo $data_for_page_rendering['current_investment']['current_profit']; ?>
                            </div>
                            <div id="inv-curr-profit" class="title">Current Profit</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php 
            }
        ?>
        <div class="current-inv-sec-2">
            <h4 class="section-group-header">My Revenue</h4>
            <div class="inv-revenue-cont">
                <div class="inv-revenue-wrapper ux-layout-grid columns-3">
                    <div class="grid-item">
                        <div class="total-bal-inv-cont">
                            <div class="data">
                                <?php echo $data_for_page_rendering['account']['total_balance']; ?>
                            </div>
                            <div class="title">Total Balance</div>
                        </div>
                    </div>
                    <div class="grid-item">
                        <div class="available-bal-inv-cont">
                            <div class="data">
                                <?php echo $data_for_page_rendering['account']['available_balance']; ?>
                            </div>
                            <div class="title">Available Balance</div>
                        </div>
                    </div>
                    <div class="grid-item">
                        <div class="pending-revenue-inv-cont">
                            <div class="data">
                                <?php echo $data_for_page_rendering['account']['pending_revenue']; ?>
                            </div>
                            <div class="title">Pending Revenue</div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="cash-out-btn-cont">
                <!--<input id="make-tansfer" class="cash-out-btn" type="button" value="Cashout" />-->
                <a id="make-tansfer" class="cash-out-btn" href="cashout.html">Cashout</a>
            </div>
        </div>
        <div class="current-inv-sec-3">
            <h4 class="section-group-header">Overview</h4>
            <div class="inv-overview-cont">
                <div class="inv-overview-wrapper ux-layout-grid columns-2">
                    <div class="grid-item">
                        <div class="total-inv-cont">
                            <div class="data">
                                <?php echo $data_for_page_rendering['investment_statistics']['total_investment']; ?>
                            </div>
                            <div class="title">Total Investment</div>
                        </div>
                    </div>
                    <div class="grid-item">
                        <div class="total--revenue-inv-cont">
                            <div class="data">
                                <?php echo $data_for_page_rendering['investment_statistics']['total_revenue']; ?>
                            </div>
                            <div class="title">Total Revenue</div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="overview-inv-tbl-cont-wrapper <?php echo count($data_for_page_rendering['invested_package_records']['records']) < 1 ? 'remove-elem' : ''; ?>">
                <div class="overview-inv-tbl-cont">
                    <?php 
                        if (count($data_for_page_rendering['invested_package_records']['records']) > 0) {
                    ?>
                    <table class="overview-inv-tbl">
                        <tr>
                            <th>Package</th>
                            <th>ROI</th>
                            <th>Amount Invested</th>
                            <th>Revenue</th>
                            <th>Bonus</th>
                            <th>Duration</th>
                            <th>Date</th>
                        </tr>
                        <?php 
                            for ($i = 0; $i < count($data_for_page_rendering['invested_package_records']['records']); $i++) {
                                $table_row = $data_for_page_rendering['invested_package_records']['records'][$i];
                        ?>
                        <tr>
                            <td><?php echo $table_row['package']; ?></td>
                            <td><?php echo $table_row['roi']; ?></td>
                            <td><?php echo $table_row['amount_invested']; ?></td>
                            <td><?php echo $table_row['revenue']; ?></td>
                            <td><?php echo $table_row['duration']; ?></td>
                            <td><?php echo $table_row['time']; ?></td>
                        </tr>
                        <?php 
                            }
                        ?>
                    </table>
                    <?php 
                        }
                    ?>
                </div>
                <div class="overview-tbl-navi-cont">
                    <div class="curr-page-indicator">Page 1 of 1</div>
                    <div class="navi-btn-cont">
                        <button title="Previous"><span class="fas fa-caret-left"></span></button>
                        <button title="Next"><span class="fas fa-caret-right"></span></button>
                    </div>
                </div>
            </div>
        </div>
<?php

// page footer
require_once 'footer.php';

?>