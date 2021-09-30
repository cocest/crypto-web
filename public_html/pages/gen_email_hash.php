<?php 

if (isset($_GET['email']) && isset($_GET['password'])) {
    // convert email address to sha1 hash
    $email_address = $_GET['email'];
    $login_password = $_GET['password'];
    $email_hash = hash('sha1', strtolower($email_address));
    $password_hash = password_hash($login_password, PASSWORD_DEFAULT);

    // display result
    echo '<h1>'.$email_address.'</h1>';
    echo '<h1>'.$email_hash.'</h1>';
    echo '<h1>'.$password_hash.'</h1>';

    exit; // exist script
}

echo '<h1>No email address or password to hash</h1>';

?>