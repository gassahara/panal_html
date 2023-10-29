<?php
function random_number() {
  $bytes = openssl_random_pseudo_bytes(1);
  $number = ord($bytes) % 20 + 1;
  return $number;
}

function getLastModified($filePath) {
    $modifiedTime = filemtime($filePath); // get the last modified time of the file
    $currentTime = time(); // get the current time
    $timeDifference = $currentTime - $modifiedTime; // calculate the time difference
    return array('modified_timestamp' => $modifiedTime, 'time_difference' => $timeDifference); // return the modified timestamp and time difference in an array
}
function getRandomString($length) {
    // Open the /dev/random device
    $handle = fopen("/dev/random", "rb");
    $r=0;
    $randomstring="";
    while ( $r < $length) {
        $bytes = fread($handle,1);
        $randomstring.=$bytes;
        $r+=1;
    }
    fclose($handle);
    $string = bin2hex($randomstring);
    return $string;
}

function encrypt($plaintext, $iv_OTP, $password) {
    $method = "AES-256-CBC";
    $key = hash('sha256', $password, true);
    $ciphertext = openssl_encrypt($plaintext, $method, $key, OPENSSL_RAW_DATA, $iv_OTP);
    $decrypted=decrypt($iv_OTP, $password, $ciphertext);
    $hash = hash_hmac('sha256', $ciphertext . $iv_OTP, $key, true);
    return array( "hash" => $hash , "cipher" => $ciphertext );
}

function decrypt($iv_OTPREG, $password, $ciphertext) {
    $method = "AES-256-CBC";
    $key = hash('sha256', $password, true);
    return openssl_decrypt($ciphertext, $method, $key, OPENSSL_RAW_DATA, $iv_OTPREG);
}

if (!$_POST["password"] || !$_POST["OTPREG_resource"] ) {
    $fnp="";
    while(file_exists($fnp) || $fnp == "") {
        $password = getRandomString(16);
        $iv_OTPREG = getRandomString(8);
        $fn=hash("sha512", $password . $iv_OTPREG, false);
        $fnp = "msgs/" . $fn . ".js";
    }
    $fp = fopen($fnp, 'w');
    fwrite($fp, "");
    fclose($fp);
    
    date_default_timezone_set('UTC');
    $currentDateTime = date('Y-m-d H');
    $text=base64_encode(encrypt($currentDateTime, $iv_OTPREG, $password)["cipher"] );
    $resource = "";
    while (file_exists($resource) || $resource == "") {
        $fn="";
        while(strlen($fn)<4){
            $cadn=rand(0, 32);
            $fn = $fn . chr($cadn);
        }
        $fn=hash("sha512", $fn, false);
        $resource = "msgs/" . $fn . ".js";
    }
    $fp = fopen($resource, 'w');
    fwrite($fp, $text);
    fclose($fp);
        $fn=hash("sha512", $password . $iv_OTPREG, false);
        $filenamerrors = "msgs/" . $fn . ".js.js";
    $fp = fopen($filenamerrors, 'w');
    fwrite($fp, "");
    fclose($fp);
    echo "var filenamerrors=\"" . $filenamerrors . "\"; var colour_index=" . random_number() . "; var iv_OTPREG=\"" . $iv_OTPREG . "\"; var OTPREG=\"" . $password . "\";\nvar OTPREG_resource=\"" . $resource . "\";\n regprocessed=255;";
} else {
    if ($_POST["iv_OTPREG"] && $_POST["password"] && $_POST["first_hash"] && $_POST["second_hash"]  ) {
        $iv_OTPREG=preg_replace('/[^A-Za-z0-9\-]/', '', $_POST["iv_OTPREG"]);
        $password=preg_replace('/[^A-Za-z0-9\-]/', '', $_POST["password"]);
        $filename="msgs/" . preg_replace('/[^A-Za-z0-9\-]/', '', $_POST["OTPREG_resource"]) . ".js.js";
        $fn=hash("sha512", $_POST["password"] . $_POST["iv_OTPREG"], false);
        $filenamerrors = "msgs/" . $fn . ".js.js";
        $hash=hash("sha512", $_POST["texto2"], false);
        $fp = fopen($filenamerrors, 'w');
        fwrite($fp, "");
        fclose($fp);        
        $file_contents = base64_decode(file_get_contents($filename));
        $decrypted = decrypt($_POST["iv_OTPREG"], $_POST["password"], $file_contents);
        date_default_timezone_set('UTC');
        $date1=new DateTime();
        $date2=new DateTime($decrypted . ":00:00");
        $interval = date_diff($date1, $date2)->h;
        if($interval < 2  ) {
            if(getLastModified($filename)["time_difference"] > 30) {
                date_default_timezone_set('UTC');
                $currentDateTime = date('Y-m-d H');
                $text=base64_encode(encrypt($currentDateTime, $iv_OTPREG, $password)["cipher"] );
                $resource = $filename;
                $fp = fopen($resource, 'w');
                fwrite($fp, $text);
                fclose($fp);
                $filename=$_POST["first_hash"] . $_POST["second_hash"] ;
                $fn=hash("sha512", $filename, false);
                $fp = fopen($fn, 'w');
                fwrite($fp, "");
                fclose($fp);
                $text="resource_index" . $fn . "; regprocessed=255;";
                $fp = fopen($filenamerrors, 'w');
                fwrite($fp, $text);
                fclose($fp);
                echo "var resource_index=\"" . $fn . "\";";
            } else {
                $text="error[\"" . $hash . "\"]=\"TIMING_ERROR\"; \n regprocessed=255; \n";
                $fp = fopen($filenamerrors, 'w');
                fwrite($fp, $text);
                fclose($fp);
                echo "$text";
            }
        } else {
            $text="error[\"" . $hash . "\"]=\"OTPREG_ERROR\"; \n regprocessed=255; \n";
            $fp = fopen($filenamerrors, 'w');
            fwrite($fp, $text);
            fclose($fp);
            echo "$text";
        }
    }
}
?>
