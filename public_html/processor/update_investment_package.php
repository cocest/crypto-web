<?php 

// start session
session_start();

// error handler function
function customError($errno, $errstr) {
    echo "<b>Error:</b> [$errno] $errstr<br>";
    die();
}

// set the handler
set_error_handler('customError');

// import all the necessary liberaries
require_once '../includes/config.php';
require_once '../includes/utils.php'; // include utility liberary

// check if request method is post
if ($_SERVER['REQUEST_METHOD'] != 'POST') {
    die(); // stop script
}

// check if we are the one that serve the page
if (!hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
    die(); // stop script
}

// mysql configuration
$db = $config['db']['mysql'];
        
// enable mysql exception
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

try {
    // connect to database
    $conn = new mysqli($db['host'], $db['username'], $db['password'], $db['dbname']);

    // check connection
    if ($conn->connect_error) {
        throw new mysqli_sql_exception('Database connection failed: ' . $conn->connect_error);
    }

    // update investment package
    $query = 
        'UPDATE crypto_investment_packages 
         SET minAmount = ?, maxAmount = ?, durationInMonth = ?, monthlyROI = ?, bonus = ?, withdrawInvestmentPercent = ? 
         WHERE id = ? LIMIT 1';
    $stmt = $conn->prepare($query); // prepare statement
    $stmt->bind_param(
        'ddiddii', 
        $_POST['minamount'], 
        $_POST['maxamount'],
        $_POST['duration'],
        $_POST['roi'],
        $_POST['bonus'],
        $_POST['withdrawpercent'],
        $_POST['id'],
    );
    $stmt->execute();
    $stmt->close();

    // send response to client
    echo json_encode([
        'success' => true,
    ]); 

    // close connection to database
    $conn->close();

} catch (mysqli_sql_exception $e) {
    // log the error to a file
    error_log('Mysql error: '.$e->getMessage().PHP_EOL, 3, CUSTOM_ERR_DIR.'custom_errors.log');
    
} catch (Exception $e) { // catch other exception
    // log the error to a file
    error_log('Caught exception: '.$e->getMessage().PHP_EOL, 3, CUSTOM_ERR_DIR.'custom_errors.log');
}

?>