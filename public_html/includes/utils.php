<?php 

// This function sanitise user input
function sanitiseInput($input) {
    return htmlspecialchars(trim(stripslashes($input)));
}
  
// Generate random token
function generateToken($length = 16) {
    return bin2hex(openssl_random_pseudo_bytes($length));
}

// Client IP on internet
function getUserIpAddress(){
    if (!empty($_SERVER["HTTP_CLIENT_IP"])){
      // check ip from share internet
      $ip = $_SERVER["HTTP_CLIENT_IP"];
    }
    else if (!empty($_SERVER["HTTP_X_FORWARDED_FOR"])){
      // check if ip is pass from proxy
      $ip = $_SERVER["HTTP_X_FORWARDED_FOR"];
    }
    else {
      $ip = $_SERVER["REMOTE_ADDR"];
    }

    return $ip;
}

/*
 * Generate cryptographically secure random strings. 
 * Based on Kohana's Text::random() method and this 
 * answer:http://stackoverflow.com/a/13733588/179104
 * 
 */
function randomText( $type = 'alnum', $length = 8 ) {
	switch ( $type ) {
		case 'alnum':
			$pool = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
			break;
		case 'alpha':
			$pool = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
			break;
		case 'hexdec':
			$pool = '0123456789abcdef';
			break;
		case 'numeric':
			$pool = '0123456789';
			break;
		case 'nozero':
			$pool = '123456789';
			break;
		case 'distinct':
			$pool = '2345679ACDEFHJKLMNPRSTUVWXYZ';
			break;
		default:
			$pool = (string) $type;
			break;
	}


	$crypto_rand_secure = function ( $min, $max ) {
		$range = $max - $min;
		if ( $range < 0 ) return $min; // not so random...
		$log    = log( $range, 2 );
		$bytes  = (int) ( $log / 8 ) + 1; // length in bytes
		$bits   = (int) $log + 1; // length in bits
		$filter = (int) ( 1 << $bits ) - 1; // set all lower bits to 1
		do {
			$rnd = hexdec( bin2hex( openssl_random_pseudo_bytes( $bytes ) ) );
			$rnd = $rnd & $filter; // discard irrelevant bits
		} while ( $rnd >= $range );
		return $min + $rnd;
	};

	$token = "";
	$max   = strlen( $pool );
	for ( $i = 0; $i < $length; $i++ ) {
		$token .= $pool[$crypto_rand_secure( 0, $max )];
	}
	return $token;
}

/* 
 * Base on ruudrp answer and modified by shashank to support Internet Explorer 11, 
 * modified by cocest to support Microsoft Edge
 * Code: http://php.net/manual/en/function.get-browser.php#101125
 */

function getBrowser() {
    $u_agent = $_SERVER['HTTP_USER_AGENT'];
    $bname = 'Unknown';
    $platform = 'Unknown';
    $version= "";

    //First get the platform?
    if (preg_match('/linux/i', $u_agent)) {
        $platform = 'linux';
    }
    else if (preg_match('/macintosh|mac os x/i', $u_agent)) {
        $platform = 'mac';
    }
    else if (preg_match('/windows|win32/i', $u_agent)) {
        $platform = 'windows';
    }

    // Next get the name of the useragent yes seperately and for good reason
    if(preg_match('/MSIE/i',$u_agent) && !preg_match('/Opera/i',$u_agent)) {
        $bname = 'Internet Explorer';
        $ub = "MSIE";
    }
    else if(preg_match('/Trident/i',$u_agent)) { 
		// this condition is for IE11
        $bname = 'Internet Explorer';
        $ub = "rv";
    }
    else if(preg_match('/Firefox/i',$u_agent)) {
        $bname = 'Mozilla Firefox';
        $ub = "Firefox";
    }
    else if (preg_match('/Edge/i',$u_agent)) {
        $bname = 'Microsoft Edge';
        $ub = "Edge";
    }
    else if(preg_match('/Chrome/i',$u_agent)) {
        $bname = 'Google Chrome';
        $ub = "Chrome";
    }
    else if(preg_match('/Safari/i',$u_agent)) {
        $bname = 'Apple Safari';
        $ub = "Safari";
    }
    else if(preg_match('/Opera/i',$u_agent)) {
        $bname = 'Opera';
        $ub = "Opera";
    }
    else if(preg_match('/Netscape/i',$u_agent)) {
        $bname = 'Netscape';
        $ub = "Netscape";
    }
   
    // finally get the correct version number
    // Added "|:"
    $known = array('Version', $ub, 'other');
	$pattern = '#(?<browser>' . join('|', $known).')[/|: ]+(?<version>[0-9.|a-zA-Z.]*)#';
    if (!preg_match_all($pattern, $u_agent, $matches)) {
        // we have no matching number just continue
    }

    // see how many we have
    $i = count($matches['browser']);
    if ($i != 1) {
        //we will have two since we are not using 'other' argument yet
        //see if version is before or after the name
        if (strripos($u_agent,"Version") < strripos($u_agent,$ub)){
            $version= $matches['version'][0];
        }
        else {
            $version= $matches['version'][1];
        }
    }
    else {
        $version= $matches['version'][0];
    }

    // check if we have a number
    if ($version==null || $version=="") {$version="?";}

    return array(
        'userAgent' => $u_agent,
        'name'      => $bname,
        'version'   => $version,
        'platform'  => $platform,
        'pattern'    => $pattern
    );
}

function opensslEncrypt ($pure_string, $encryption_key) {
    $cipher     = 'AES-256-CBC';
    $options    = OPENSSL_RAW_DATA;
    $hash_algo  = 'sha256';
    $sha2len    = 32;
    $ivlen = openssl_cipher_iv_length($cipher);
    $iv = openssl_random_pseudo_bytes($ivlen);
    $ciphertext_raw = openssl_encrypt($pure_string, $cipher, $encryption_key, $options, $iv);
    $hmac = hash_hmac($hash_algo, $ciphertext_raw, $encryption_key, true);
    return $iv.$hmac.$ciphertext_raw;
}

function opensslDecrypt ($encrypted_string, $encryption_key) {
    $cipher     = 'AES-256-CBC';
    $options    = OPENSSL_RAW_DATA;
    $hash_algo  = 'sha256';
    $sha2len    = 32;
    $ivlen = openssl_cipher_iv_length($cipher);
    $iv = substr($encrypted_string, 0, $ivlen);
    $hmac = substr($encrypted_string, $ivlen, $sha2len);
    $ciphertext_raw = substr($encrypted_string, $ivlen+$sha2len);
    $original_plaintext = openssl_decrypt($ciphertext_raw, $cipher, $encryption_key, $options, $iv);
    $calcmac = hash_hmac($hash_algo, $ciphertext_raw, $encryption_key, true);
    if (hash_equals($hmac, $calcmac)) {
        return $original_plaintext;
    } else {
        return null;
    }
}

// Send email to client using "PHPMailer"
function sendEmailTo($recipient_email, $recipient_name, $msg_subject, $msg_content, $attachments = []) {
    require_once 'PHPMailer/Exception.php';
    require_once 'PHPMailer/PHPMailer.php';
    require_once 'PHPMailer/SMTP.php';

    $mail = new PHPMailer;
    $mail->isSMTP();
    // $mail->SMTPDebug = 2; // Set it to 0 in the final version to avoid the end user from seeing the SMTP delivery report.
    $mail->Host = 'smtp.hostinger.com';
    $mail->Port = 587;
    $mail->SMTPAuth = true;
    $mail->Username = 'test@hostinger-tutorials.com';
    $mail->Password = 'EMAIL_ACCOUNT_PASSWORD';
    $mail->setFrom('test@hostinger-tutorials.com', 'website name'); // sender mail
    // $mail->addReplyTo('reply-box@hostinger-tutorials.com', 'Your Name');
    $mail->addAddress($recipient_email, $recipient_name);
    $mail->Subject = $msg_subject;
    $mail->msgHTML(file_get_contents('message.html'), __DIR__);
    // $mail->AltBody = 'This is a plain text message body';

    // add attachment
    for ($i = 0; $i < count($attachments); $i++) {
        $mail->addAttachment($attachments[$i]); // url of file to attach
    }
    
    if (!$mail->send()) {
        echo 'Mailer Error: ' . $mail->ErrorInfo;
    } else {
        echo 'Message sent!';
    }
}