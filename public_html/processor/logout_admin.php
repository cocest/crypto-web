<?php 

/**
 * This script sign-out logged in admin and redirect admin to sign-in page
 */

// start session
session_start();

// import all the necessary liberaries
require_once '../includes/config.php';

// clear the user's login session
unset($_SESSION['admin_auth']);
unset($_SESSION['admin_user_id']);
unset($_SESSION['admin_last_auth_time']);

// redirect user to login pages
header('Location: '. BASE_URL . 'admin/login.html');
exit;

?>