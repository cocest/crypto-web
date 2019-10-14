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

date_default_timezone_set('UTC');

// check if request method is post
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
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
            $query = 'SELECT userID, password FROM user_authentication WHERE username = ? LIMIT 1';
            $stmt = $conn->prepare($query); // prepare statement
            $stmt->bind_param('s', $_POST['username']);
            $stmt->execute();
            $stmt->store_result(); // needed for num_rows

            if ($stmt->num_rows > 0) { // user exist
                // validate user password
                $stmt->bind_result($user_id, $password_hash);
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
                    }

                    $stmt->close(); // close previous prepared statement

                    // check if user tick remember me login
                    if (isset($_POST['remember'])) {
                        // generate auto login token
                        $login_token;
                        $selector = randomText('distinct', 16);
                        $validator = generateToken();
                        $token_id = generateClientIDFromUserAgent();
                        $hashed_validator = password_hash($validator, PASSWORD_DEFAULT);
                        $token_expires = $current_time = (86400 * 30); // life-span of the token is 30 days

                        if ($token_id != null) {
                            $hashed_token_id = password_hash($token_id, PASSWORD_DEFAULT);
                            $encrypted_token_id = opensslEncrypt($token_id, OPENSSL_ENCR_KEY);
                            $login_token = $selector . ':' . $validator . ':' . $encrypted_token_id;

                            // insert into database
                            $query = 
                                'INSERT INTO user_auth_tokens 
                                 (selector, hashedValidator, hashedTokenID, userID, expires) 
                                 VALUES (?, ?, ?, ?, ?)';
                            $stmt = $conn->prepare($query); // prepare statement
                            $stmt->bind_param('sssii', $selector, $hashed_validator, $hashed_token_id, $user_id, $token_expires);
                            $stmt->execute();

                        } else {
                            $login_token = $selector . ':' . $validator;

                            // insert into database
                            $query = 
                                'INSERT INTO user_auth_tokens 
                                 (selector, hashedValidator, userID, expires) 
                                 VALUES (?, ?, ?, ?)';
                            $stmt = $conn->prepare($query); // prepare statement
                            $stmt->bind_param('ssii', $selector, $hashed_validator, $user_id, $token_expires);
                            $stmt->execute();
                        }

                        $stmt->close();

                        // set the auto-login cookies
                        // Note: remember to change secure to true
                        setrawcookie('auto_login', $login_token, $token_expires, '/', '', false, true);
                    }

                    // close connection to database
                    $conn->close();

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

} else if ($_SERVER['REQUEST_METHOD'] == 'GET' && $_SESSION['auto_login_user'] == true) { // check to automatically login user
    // check if token is valid
    if (!preg_match('/^([a-zA-Z0-9]+[:]{1}[a-zA-Z0-9]+[:]{1}[a-zA-Z0-9]+|[a-zA-Z0-9]+[:]{1}[a-zA-Z0-9]+)$/', $_COOKIE['auto_login'])) {
        die(); // exit script
    }

    $token_parts = explode(':', $_COOKIE['auto_login']);

    // mysql configuration
    $db = $config['db']['mysql'];
        
    // enable mysql exception
    mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

    // connect to database
    $conn = new mysqli($db['host'], $db['username'], $db['password'], $db['dbname']);

    //check connection
    if ($conn->connect_error) {
        trigger_error('Database connection failed: '.$conn->connect_error, E_USER_ERROR);
    }

    try {
        // select hashes from database
        $query = 'SELECT hashedValidator, hashedTokenID, userID, expires FROM user_auth_tokens WHERE selector = ? LIMIT 1';
        $stmt = $conn->prepare($query); // prepare statement
        $stmt->bind_param('s', $token_parts[0]);
        $stmt->execute();
        $stmt->store_result(); // needed for num_rows

        if ($stmt->num_rows > 0) { // user exist
            // bind result
            $stmt->bind_result($hashed_validator, $hashed_token_id, $user_id, $expires);
            $stmt->fetch();
            $stmt->close();

            // check if validator is valid
            if (password_verify($token_parts[1], $hashed_validator)) {
                $token_id_valid = false;

                if (!empty($hashed_token_id)) { // check if token has ID
                    $decrypted_token_id = opensslDecrypt($token_parts[2], OPENSSL_ENCR_KEY);
                    if ($decrypted_token_id != null && password_verify($decrypted_token_id, $hashed_token_id)) {
                        $token_id_valid = true;
                    }

                } else {
                    $token_id_valid = true;
                }

                if ($token_id_valid) {
                    // check if token hasn't expired
                    if (time() < $expires) {
                        // create login session and redirect user to their page
                        $_SESSION['auth'] = true;
                        header('Location: ' . BASE_URL . 'user/home.php');
                        exit;

                    } else { // token has expired
                        // remove the token from the database
                        $query = 'DELETE FROM user_auth_tokens WHERE selector = ? LIMIT 1';
                        $stmt = $conn->prepare($query); // prepare statement
                        $stmt->bind_param('s', $token_parts[0]);
                        $stmt->execute();

                        // close connection to database
                        $stmt->close();
                        $conn->close();
                    }
                }

                // remove auto login cookie
                setrawcookie('auto_login', '', time() - 3600);

                // redirect user to login page
                header('Location: ' . BASE_URL . 'user/login.php');
                exit;
            }

            // stop the script
            die();

        } else { // redirect user to login page
            header('Location: ' . BASE_URL . 'user/login.php');
            exit;
        }

    } catch (mysqli_sql_exception $e) { // catch only mysqli exceptions
        // $e->getMessage(); 
        // $e->getCode();
        // handle mysqli exception here
        echo 'Mysql error: ' . $e->getMessage() . PHP_EOL;

    } catch (Exception $e) { // catch other exception
        echo 'Caught exception: ' . $e->getMessage() . PHP_EOL;
    }
}

// utility function to get client identification from user agent
function generateClientIDFromUserAgent() {
    $user_agent = $_SERVER['HTTP_USER_AGENT'];
    $matches;

    if (!empty($user_agent)) {
        preg_match("/\([a-zA-Z0-9.; ]+\)/", $user_agent, $matches);
        return $matches[0];

    } else {
        return null;
    }
}

?>