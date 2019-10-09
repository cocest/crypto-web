<?php 

// start session
session_start();

// error handler function
function customError($errno, $errstr) {
    echo "<b>Error:</b> [$errno] $errstr<br>";
    die();
}

// import all the necessary liberaries
require_once '../includes/config.php';
require_once '../includes/utils.php'; // include utility liberary

// check if request method is post
if ($_SERVER['REQUEST_METHOD'] != 'POST') {
    die(); // exit the script
}

// check if we are the one that serve the page
if (hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
    $db = $config['db']['mysql']; // mysql configuration
        
    // enable mysql exception
    mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

    // connect to database
    $conn = new mysqli($db['host'], $db['username'], $db['password'], $db['dbname']);

    //check connection
    if ($conn->connect_error) {
        trigger_error('Database connection failed: '.$conn->connect_error, E_USER_ERROR);
    }

    try {
        date_default_timezone_set('UTC');
        $current_time = time();
        $login_attempt = 0;
        $count;
        $del_user_limit = false;

        // check if user has failed login
        $query = 'SELECT loginAttempt, count, retryTime FROM limit_login_throttling WHERE username = ? LIMIT 1';
        $stmt = $conn->prepare($query); // prepare statement
        $stmt->bind_param('s', $_POST['username']);
        $stmt->execute();
        $stmt->store_result(); // needed for num_rows

        if ($stmt->num_rows > 0) {
            $del_user_limit = true;
            $stmt->bind_result($login_attempt, $count, $retry_time);
            $stmt->fetch();

            // check if throttling protection is active
            if ($count > 0 && $current_time < $retry_time) {
                // close connection to database
                $stmt->close();
                $conn->close();

                $duration = $retry_time - $current_time;
                header('Retry-After: ' . $duration, TRUE, '429');
                echo '{"success": false, "rate_limit": true, "retry_after": ' . $duration . ' }';
                exit; // exit script
            }
        }

        $stmt->close(); // close previous prepared statement

        // authenticate username and password
        $query = 'SELECT password FROM user_authentication WHERE username = ? LIMIT 1';
        $stmt = $conn->prepare($query); // prepare statement
        $stmt->bind_param('s', $_POST['username']);
        $stmt->execute();
        $stmt->store_result(); // needed for num_rows

        if ($stmt->num_rows > 0) { // user exist
            // validate user password
            $stmt->bind_result($password_hash);
            $stmt->fetch();

            // match password
            if (password_verify($_POST['password'], $password_hash)) {
                $stmt->close(); // close previous prepared statement

                // delete user from limit_login_throttling table if exist
                if ($del_user_limit) {
                    $query = 'DELETE FROM limit_login_throttling WHERE username = ? LIMIT 1';
                    $stmt = $conn->prepare($query); // prepare statement
                    $stmt->bind_param('s', $_POST['username']);
                    $stmt->execute();

                    // close connection to database
                    $stmt->close();
                    $conn->close();
                }

                // create login session and send redirect url to client
                $_SESSION['auth'] = true;
                $redirect_url = BASE_URL . 'user/home.php';
                echo '{"success": true, "redirect_url": "' . $redirect_url . '"}';
                exit; // exit script
            }
        }

        $stmt->close(); // close previous prepared statement

        // check if throttling protection should kick in
        if ($login_attempt > 3) {
            $wait_max_time = 30; // thirty seconds
            $wait_time = 5 * ($count + 1); // five seconds
            $login_attempt += 1; 
            $count += 1;
            $wait_time = $wait_time > $wait_max_time ? $current_time + $wait_max_time : $current_time + $wait_time;
            $query = 'UPDATE limit_login_throttling SET loginAttempt = ?, count = ?, retryTime = ? WHERE username = ? LIMIT 1';
            $stmt = $conn->prepare($query); // prepare statement
            $stmt->bind_param('iiis', $login_attempt, $count, $wait_time, $_POST['username']);
            $stmt->execute();

        } else if ($login_attempt == 0) { // insert user into table
            $login_attempt += 1;
            $client_ip = getUserIpAddress();
            $query = 'INSERT INTO limit_login_throttling (username, clientIP, loginAttempt) VALUES (?, ?, ?)';
            $stmt = $conn->prepare($query); // prepare statement
            $stmt->bind_param('ssi', $_POST['username'], $client_ip, $login_attempt);
            $stmt->execute();

        } else {
            // update table data
            $login_attempt += 1;
            $query = 'UPDATE limit_login_throttling SET loginAttempt = ? WHERE username = ? LIMIT 1';
            $stmt = $conn->prepare($query); // prepare statement
            $stmt->bind_param('is', $login_attempt, $_POST['username']);
            $stmt->execute();
        }

        // close connection to database
        $stmt->close();
        $conn->close();

        // user login failed
        echo '{"success": false, "rate_limit": false}';

    } catch (mysqli_sql_exception $e) { // catch only mysqli exceptions
        // $e->getMessage(); 
        // $e->getCode();
        // handle mysqli exception here
        echo 'Mysql error: ' . $e->getMessage() . PHP_EOL;

    } catch (Exception $e) { // catch other exception
        echo 'Caught exception: ' . $e->getMessage() . PHP_EOL;
    }
}

?>