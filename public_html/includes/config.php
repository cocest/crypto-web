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
            "dbname" => "citadelcapital",
            "username" => "root",
            "password" => "cocest",
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
define("BASE_URL", "http://localhost/workspace/thecitadelcapital/crypto-web/public_html/");
define("DEBUG_EMAIL", ""); // don't forget to change this
define("OPENSSL_ENCR_KEY", "");
define("CRYPTOCOMPARE_API_KEY", "here");
define("CPS_PRIVATE_KEY", "keyhere");
define("CPS_PUBLIC_KEY", "keyhere");
define("CPS_MERCHANT_ID", "idhere");
define("CPS_IPN_SECRET", "secrethere");
define("BC_CALLBACK_SECRET", "secrethere");
define("BC_XPUB_ADDRESS", "address");
define("BC_API_KEY", "key");
define("BC_GUID", "id");
define("BC_WALLET_PASSWORD", "password");
define("CQ_API_KEY", "d2c4b2296b52");
define("CQ_API_SECRET", "ZVnt-X!39-$29B-2bD@-!t4S-N9mR");
define("SMTP_HOST", "smtp.sendgrid.net");
define("SMTP_PORT", 587);
define("SMTP_USERNAME", "");
define("SMTP_PASSWORD", "");
define("SENDER_EMAIL", "noreply@thecitadelcapital.com");
define("SENDER_NAME", "Thecitadelcapital");
define("SEND_US_EMAIL_ADDRESS", "thecitadelcapital@gmail.com");
define("USER_PROFILE_URL","http://localhost/workspace/thecitadelcapital/crypto-web/public_html/uploads/users/profile/");
define("USER_ID_UPLOAD_URL","http://localhost/workspace/thecitadelcapital/crypto-web/public_html/uploads/users/identification/");
define("USER_ID_UPLOAD_DIR", dirname(__DIR__, 1)."/uploads/users/identification/");
define("USER_PROFILE_DIR", dirname(__DIR__, 1)."/uploads/users/profile/");
define("CUSTOM_ERR_DIR", dirname(__DIR__, 1)."/errors/");
 
?>