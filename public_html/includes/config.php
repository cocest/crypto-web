<?php
 
/*
    The important thing to realize is that the config file should be included in every
    page of your project, or at least any page you want access to these settings.
    This allows you to confidently use these settings throughout a project because
    if something changes such as your database credentials, or a path to a specific resource,
    you'll only need to update it here.
*/
 
$config = array(
    "db" => array(
        "mysql" => array(
            "dbname" => "cryptoweb",
            "username" => "root",
            "password" => "root",
            "host" => "localhost"
        )
    ),
    "urls" => array(
        "baseUrl" => "http://example.com"
    ),
    "paths" => array(
        "resources" => "/path/to/resources",
        "images" => array(
            "content" => $_SERVER["DOCUMENT_ROOT"] . "/images/content",
            "layout" => $_SERVER["DOCUMENT_ROOT"] . "/images/layout"
        )
    )
);
 
/*
    I will usually place the following in a bootstrap file or some type of environment
    setup file (code that is run at the start of every page request), but they work 
    just as well in your config file if it's in php (some alternatives to php are xml or ini files).
*/
 
/*
    Creating constants for heavily used paths makes things a lot easier.
    ex. require_once(LIBRARY_PATH . "Paginator.php")
*/
define("BASE_URL", "http://localhost/Workspace/PHP/Crypto-web/crypto-web/public_html/");
define("DEBUG_EMAIL", "attamahcelestine@gmail.com"); // don't forget to change this
define("OPENSSL_ENCR_KEY", "dkf!WEqpmW_@4rt#&&kit");
define("CRYPTOCOMPARE_API_KEY", "here");
define("CPS_PRIVATE_KEY", "keyhere");
define("CPS_PUBLIC_KEY", "keyhere");
define("CPS_MERCHANT_ID", "idhere");
define("CPS_IPN_SECRET", "87yRHfdcb_@45ah#");
define("SMTP_HOST", "smtp.sendgrid.net");
define("SMTP_PORT", 587);
define("SMTP_USERNAME", "here");
define("SMTP_PASSWORD", "here");
define("SENDER_EMAIL", "attamahcelestine@gmail.com");
define("SENDER_NAME", "Thecitadelcapital.com");
define("USER_ID_UPLOAD_DIR", dirname(__DIR__, 1)."/uploads/users/identification/");
define("CUSTOM_ERR_DIR", dirname(__DIR__, 1)."/errors/");
 
?>