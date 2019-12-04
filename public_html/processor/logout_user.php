<?php 

/**
 * This script sign-out logged in user and redirect user to sign-in page
 */

// start session
session_start();

// import all the necessary liberaries
require_once '../includes/config.php';

// clear the user's login session
unset($_SESSION['auth']);
unset($_SESSION['user_id']);
unset($_SESSION['last_auth_time']);

// redirect user to login pages
header('Location: '. BASE_URL . 'user/login.html');
exit;

?>