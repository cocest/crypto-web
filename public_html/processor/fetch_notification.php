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

// mysql configuration
$db = $config['db']['mysql'];
        
// enable mysql exception
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

try {
    // connect to database
    $conn = new mysqli($db['host'], $db['username'], $db['password'], $db['dbname']);

    //check connection
    if ($conn->connect_error) {
        throw new mysqli_sql_exception('Database connection failed: '.$conn->connect_error);
    }

    $unread_msg_count = 0;
    $messages = []; // list of notification
    $unread_msg = 0;

    // count unread notification
    $query = 'SELECT COUNT(*) AS total FROM users_notification WHERE userID = ? AND readState = ?';
    $stmt = $conn->prepare($query); // prepare statement
    $stmt->bind_param('ii', $_SESSION['user_id'], $unread_msg);
    $stmt->execute();
    $stmt->bind_result($unread_msg_count);
    $stmt->fetch();
    $stmt->close();

    // fetch notification
    $query = 'SELECT * FROM users_notification WHERE userID = ? AND time > ? ORDER BY time DESC LIMIT ?';
    $stmt = $conn->prepare($query); // prepare statement
    $stmt->bind_param('iii', $_SESSION['user_id'], $_POST['time_offset'], $_POST['limit']);
    $stmt->execute();
    $result = $stmt->get_result();

    // iterate through the result
    while ($row = $result->fetch_assoc()) {
        $messages[] = [
            'id' => $row['msgID'],
            'title' => $row['title'],
            'content' => $row['content'],
            'read' => $row['readState'],
            'time' => $row['time']
        ];
    }
    
    $user_notification = [
        'unread_msg_count' => $unread_msg_count,
        'messages' => $messages
    ];

    $stmt->close();
    $conn->close();

    // send result to client
    echo json_encode($user_notification);

} catch (mysqli_sql_exception $e) {
    echo 'Mysql error: ' . $e->getMessage() . PHP_EOL;

} catch (Exception $e) { // catch other exception
    echo 'Caught exception: ' .  $e->getMessage() . PHP_EOL;
}

?>