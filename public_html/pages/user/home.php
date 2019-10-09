<?php 

// start session
session_start();

// error handler function
function customError($errno, $errstr) {
    echo "<b>Error:</b> [$errno] $errstr<br>";
    die();
}

// import all the necessary liberaries
require_once '../../includes/config.php';
require_once '../../includes/utils.php'; // include utility liberary

// check if user is authenticated
if (!isset($_SESSION['auth']) || !$_SESSION['auth']) {
    // redirect user to login pages
    header('Location: '. BASE_URL . 'user/login.html');
    exit;
}

?>

<!DOCTYPE html>
<html>
    <head>
    <meta charset="UTF-8">
    <title>CryptoWeb - Sign In</title>
    <link rel="icon" type="image/png" href="favicon1.png" sizes="16x16">
    <link rel="icon" type="image/png" href="favicon2.png" sizes="32x32">
    <link rel="icon" type="image/png" href="favicon3.png" sizes="120x120">
    <meta name="description" content="CryptoWeb registeration page">
    <meta name="keywords" content="sign in, sign up, register, register to CryptoWeb, create account with CryptoWeb">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">
    <link type="text/css" href="../fonts/css/all.min.css" rel="stylesheet">
    <link type="text/css" rel="stylesheet" href="../styles/UdaraX.css">
    <link type="text/css" rel="stylesheet" href="../styles/login.css">
    <script type="text/javascript" src="../js/utils.js"></script>
    <script type="text/javascript" src="../js/login.js"></script>
    </head>
    <body>
        // start here
    </body>
</html>