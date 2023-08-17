<?php
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

function decrypt($iv_OTP, $password, $ciphertext) {
    $method = "AES-256-CBC";
    $key = hash('sha256', $password, true);
    return openssl_decrypt($ciphertext, $method, $key, OPENSSL_RAW_DATA, $iv_OTPBUZ);
}

if (!$_POST["password"] || !$_POST["OTPBUZ_resource"] ) {
    $fnp="";
    while(file_exists($fnp) || $fnp == "") {
        $password = getRandomString(16);
        $iv_OTPBUZ = getRandomString(8);
        $fn=hash("sha512", $password . $iv_OTPBUZ, false);
        $fnp = "msgs/" . $fn . ".js";
    }
    $fp = fopen($fnp, 'w');
    fwrite($fp, "");
    fclose($fp);
    
    date_default_timezone_set('UTC');
    $currentDateTime = date('Y-m-d H');
    $text=base64_encode(encrypt($currentDateTime, $iv_OTPBUZ, $password)["cipher"] );
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
    echo "var iv_OTPBUZ=\"" . $iv_OTPBUZ . "\"; var OTPBUZ=\"" . $password . "\";\nvar OTPBUZ_resource=\"" . $resource . "\";\n buzonprocessed=255;";
} else {
    if ($_POST["iv_OTPBUZ"] && $_POST["password"] ) {
        $iv_OTPBUZ=preg_replace('/[^A-Za-z0-9\-]/', '', $_POST["iv_OTPBUZ"]);
        $password=preg_replace('/[^A-Za-z0-9\-]/', '', $_POST["password"]);
        $filename="msgs/" . preg_replace('/[^A-Za-z0-9\-]/', '', $_POST["OTPBUZ_resource"]) . ".js";
        $fn=hash("sha512", $_POST["password"] . $_POST["iv_OTPBUZ"], false);
        $filenamerrors = "msgs/" . $fn . ".js";
        $hash=hash("sha512", $_POST["texto2"], false);
        $file_contents = str_replace("buzonprocessed=255;", "", file_get_contents($filenamerrors));
        $fp = fopen($filenamerrors, 'w');
        fwrite($fp, $file_contents);
        fclose($fp);        
        $file_contents = base64_decode(file_get_contents($filename));
        $decrypted = decrypt($_POST["iv_OTPBUZ"], $_POST["password"], $file_contents);
        date_default_timezone_set('UTC');
        $date1=new DateTime();
        $date2=new DateTime($decrypted . ":00:00");
        $interval = date_diff($date1, $date2)->h;
        if($interval < 2  ) {
            if(getLastModified($filename)["time_difference"] > 30) {
                date_default_timezone_set('UTC');
                $currentDateTime = date('Y-m-d H');
                $text=base64_encode(encrypt($currentDateTime, $iv_OTPBUZ, $password)["cipher"] );
                $resource = $filename;
                $fp = fopen($resource, 'w');
                fwrite($fp, $text);
                fclose($fp);
                $filename="";
                while (file_exists($filename) || $filename == "") {
                    $hash1=getRandomString(16);
                    $hash2=getRandomString(16);
                    $fn=$hash1 . $hash2;
                    $fn=hash("sha512", $fn, false);
                    $filename = "msgs/" . $fn . ".js";
                }
                $fp = fopen($filename, 'w');
                fwrite($fp, "");
                fclose($fp);
                echo "var resource_first=\"" . $hash1 . "\"; var resource_first=\"" . $hash2 . "\";";
            } else {
                $text="error[\"" . $hash . "\"]=\"TIMING_ERROR\"; \n buzonprocessed=255; \n";
                $fp = fopen($filenamerrors, 'a');
                fwrite($fp, $text);
                fclose($fp);
                echo "$text";
            }
        } else {
            $text="error[\"" . $hash . "\"]=\"OTPBUZ_ERROR\"; \n buzonrocessed=255; \n";
            $fp = fopen($filenamerrors, 'a');
            fwrite($fp, $text);
            fclose($fp);
            echo "$text";
        }
    }
}
?>
