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
require_once '../includes/ImageResize/ImageResize.php';

// check if request method is post
if ($_SERVER['REQUEST_METHOD'] != 'POST') {
    die(); // stop script
}

// check if we are the one that serve the page
if (!hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
    die(); // stop script
}

// check if post contain needed data
if (!isset($_POST['imgcropinfo'])) {
    die(); // stop script
}

// check if upload file is valid
if (!validateUploadedImage($_FILES)) {
    // send message to the client
    echo json_encode([
        'success' => false
    ]);

    exit; // stop script
}

$db = $config['db']['mysql']; // mysql configuration
        
// enable mysql exception
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

try {
    // directory to save uploaded file
    $img_ext = pathinfo($_FILES['file']['name'], PATHINFO_EXTENSION);
    $file_name1 = randomText('hexdec', 32).'.'.$img_ext;
    $target_dir1 = USER_PROFILE_DIR.$file_name1;
    $file_name2 = randomText('hexdec', 32).'.'.$img_ext;
    $target_dir2 = USER_PROFILE_DIR.$file_name2;

    // convert json data to object
    $img_crop_info = json_decode($_POST['imgcropinfo'], true);
    $scale_factor = $img_crop_info['scale_factor'];

    // now crop and resize the new uploaded image to medium
    $img_resize = new ImageResize($_FILES['file']['tmp_name']);
    $img_resize->cropResize(
        $img_crop_info['clip_rect']['x'] * $scale_factor,
        $img_crop_info['clip_rect']['y'] * $scale_factor,
        $img_crop_info['clip_rect']['w'] * $scale_factor,
        $img_crop_info['clip_rect']['h'] * $scale_factor,
        $img_crop_info['clip_rect']['w'],
        $img_crop_info['clip_rect']['h']
    );
    $img_resize->saveImage($target_dir1);

    // resize image to small (thumbnail)
    $img_resize->setImage($target_dir1);
    $img_resize->resizeTo(80, 80);
    $img_resize->saveImage($target_dir2);

    // connect to database
    $conn = new mysqli($db['host'], $db['username'], $db['password'], $db['dbname']);

    //check connection
    if ($conn->connect_error) {
        trigger_error('Database connection failed: '.$conn->connect_error, E_USER_ERROR);
    }

    // check if user has uploaded profile picture
    $query = 'SELECT smallProfilePictureURL, mediumProfilePictureURL FROM users WHERE id = ? LIMIT 1';
    $stmt = $conn->prepare($query); // prepare statement
    $stmt->bind_param('i', $_SESSION['user_id']);
    $stmt->execute();
    $stmt->bind_result($small_profile_url, $medium_profile_url);
    $stmt->fetch();
    $stmt->close();

    // check to delete user's profile pictures
    if (!empty($small_profile_url)) {
        unlink(USER_PROFILE_DIR.$small_profile_url);
        unlink(USER_PROFILE_DIR.$medium_profile_url);
    }

    // add new images' path to database
    $query = 'UPDATE users SET smallProfilePictureURL = ?, mediumProfilePictureURL = ? WHERE id = ?';
    $stmt = $conn->prepare($query); // prepare statement
    $stmt->bind_param('ssi', $file_name2, $file_name1, $_SESSION['user_id']);
    $stmt->execute();
    $stmt->close();

    // send response back to client
    echo json_encode([
        'success' => true,
        'small_img_url' => BASE_URL.'uploads/users/profile/'.$file_name2,
        'medium_img_url' => BASE_URL.'uploads/users/profile/'.$file_name1
    ]);

    // close connection to database
    $conn->close();

} catch (mysqli_sql_exception $e) { // catch only mysqli exceptions 
    // delete uploaded image           
    unlink($target_dir1);
    unlink($target_dir2);

    // log the error to a file
    error_log('Mysql error: '.$e->getMessage().PHP_EOL, 3, CUSTOM_ERR_DIR.'custom_errors.log');

    // send message to the client
    echo json_encode([
        'success' => false
    ]);

    exit;

} catch (Exception $e) { // catch other exception
    // log the error to a file
    error_log('Caught exception: '.$e->getMessage().PHP_EOL, 3, CUSTOM_ERR_DIR.'custom_errors.log');

    // send message to the client
    echo json_encode([
        'success' => false
    ]);

    exit;
}

// utility function to validate user's uploaded file
function validateUploadedImage($uploaded_files) {
    /*
    // uncomment this if your web hosting support it
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $type = finfo_file($finfo, $uploaded_files['file']['tmp_name']);
    */

    // using this one for now
    $size = getimagesize($uploaded_files['file']['tmp_name']);
	$type = $size['mime'];

    if (isset($type) && in_array($type, ['image/jpg', 'image/jpeg', 'image/png', 'image/gif'])) {
        if ($uploaded_files['file']['size'] / 1048576 < 4) { // less than 4 megabytes
            return true;
        }
    }

    return false;
}

?>