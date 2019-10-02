<?php 

// this function sanitise user input
function sanitiseInput($input) {
    return htmlspecialchars(trim(stripslashes($input)));
}
  
// generate random token
function generateToken($length = 16) {
    return bin2hex(openssl_random_pseudo_bytes($length));
}

?>