<?php 

// start session
session_start();

// import all the necessary liberaries
require_once '../../../includes/config.php';

// check if user is authenticated
if (!(isset($_SESSION['auth']) && $_SESSION['auth'] == true)) {
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

    /*if ($account_activated == 0) {
        // account not yet activated
        $conn->close(); // close connection

        // redirect user
        header('Location: '. BASE_URL . 'user/home/email_verification.html');
        exit;
    }*/

    // check if user has invested on any package before
    $query = 'SELECT 1 FROM user_invested_package_records WHERE userID = ? LIMIT 1';
    $stmt = $conn->prepare($query); // prepare statement
    $stmt->bind_param('i', $_SESSION['user_id']);
    $stmt->execute();
    $stmt->store_result(); // needed for num_rows

    $include_trial_pkg = true; // include trial package

    if ($stmt->num_rows > 0) {
        $include_trial_pkg = false;
    }

    $stmt->close();

    // fetch packages from database
    $query = "SELECT * FROM crypto_investment_packages WHERE id > ?";
    $stmt = $conn->prepare($query); // prepare statement
    $stmt->bind_param('i', $list_offset);
    $list_offset = $include_trial_pkg ? 0 : 1;
    $stmt->execute();
    $result = $stmt->get_result();

    while ($row = $result->fetch_assoc()) {
        $data_for_page_rendering[] = $row;
    }

    $stmt->close();

    // close connection to database
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
        <h1 class="page-title-hd">Packages</h1>
        <div class="packages-sec-1">
            <div class="package-descr-cont">
                Here are list of exclusive packages you can choose from, and we do the rest for you.
            </div>
            <div class="package-list-cont">
                <div class="package-list-wrapper ux-layout-grid columns-2">
                    <?php 
                        for ($i = 0; $i < count($data_for_page_rendering); $i++) {
                    ?>
                    <div class="grid-item">
                        <div class="package-cont item-<?php echo $i + 1; ?>">
                            <div class="upper-sec">
                                <h2 class="title"><?php echo $data_for_page_rendering[$i]['package']; ?></h2>
                                <div class="price-range">
                                    <?php 
                                        echo '$'.number_format($data_for_page_rendering[$i]['minAmount']).' - '.
                                        ($data_for_page_rendering[$i]['maxAmount'] == 0 ? 'unlimited' : '$'.number_format($data_for_page_rendering[$i]['maxAmount'])); 
                                    ?>
                                </div>
                                <div class="duration-cont">
                                    <span class="duration"><?php echo $data_for_page_rendering[$i]['durationInMonth']; ?></span> month due
                                </div>
                            </div>
                            <div class="lower-sec">
                                <ul class="pkg-features-list">
                                    <li>
                                        <?php echo intval($data_for_page_rendering[$i]['monthlyROI']); ?>% return of investment (ROI).
                                    </li>
                                    <li>
                                        <?php echo $data_for_page_rendering[$i]['bonus'] == 0 ? 'No' : intval($data_for_page_rendering[$i]['bonus']).'%'; ?> investment bonus.
                                    </li>
                                    <?php 
                                        if ($data_for_page_rendering[$i]['withdrawInvestment'] == 0) {
                                    ?>
                                    <li>You can only withdraw profit, capital is rolled over to next package</li>
                                    <?php 
                                        }
                                    ?>
                                </ul>
                            </div>
                            <div class="invest-btn-cont">
                                <input class="invest-on-pkg-btn" type="button" value="Invest" 
                                 onclick="window.location.href='package_payment.html?package_id=<?php echo $data_for_page_rendering[$i]['id']; ?>';" />
                            </div>
                        </div>
                    </div>
                    <?php 
                        }
                    ?>
                </div>
            </div>
        </div>

<?php

// page footer
require_once 'footer.php';

?>