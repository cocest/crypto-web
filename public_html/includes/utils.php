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
 * Base on ruudrp answer and modified by shashank to support Internet Explorer 11
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

?>