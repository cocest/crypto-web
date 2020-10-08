<?php 

if (isset($_GET['email'])) {
    // convert email address to sha1 hash
    $email_address = $_GET['email'];
    $email_hash = hash('sha1', strtolower($email_address));

    // display result
    echo '<h1>'.$email_address.'</h1>';
    echo '<h1>'.$email_hash.'</h1>';

    exit; // exist script
}

echo '<h1>No email address to hash</h1>';

?>