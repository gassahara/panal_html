<?php
function minus($mins) {
    $current_time = time(); // Get the current timestamp
    $new_time = $current_time - ($mins * 60); // Subtract 5 minutes (5 * 60 seconds)
    $formatted_time = gmdate("Y-m-d H:i", $new_time); // Format the new timestamp    
    return $formatted_time; // Print the new time
}
if (! isset($_POST["iv_OTP"]) || ! isset($_POST["OTP_resource"]) || ! isset($_POST["OTP"])) {
    $OTP_resource="";
    while(strlen($OTP_resource)<128){
        $cadn=rand(0, 255);
        if($cadn<16) {
            $OTP_resource = $OTP_resource . "0" . dechex($cadn);
        } else {
            $OTP_resource = $OTP_resource . dechex($cadn);
        }
    }
    $OTP="";
    while(strlen($OTP)<32){
        $cadn=rand(0, 255);
        if($cadn<16) {
            $OTP = $OTP . "0" . dechex($cadn);
        } else {
            $OTP = $OTP . dechex($cadn);
        }
    }
    $iv_OTP="";
    while(strlen($iv_OTP)<16){
        $cadn=rand(0, 255);
        if($cadn<16) {
            $iv_OTP = $iv_OTP . "0" . dechex($cadn);
        } else {
            $iv_OTP = $iv_OTP . dechex($cadn);
        }
    }
    $fecha=gmdate("Y-m-d H:i");
    $ciphertext = base64_encode(openssl_encrypt($fecha, 'aes-256-ctr', $OTP, OPENSSL_RAW_DATA, $iv_OTP));
    $OTP_resource="";
    $filename="";
    while (file_exists($filename) || $filename=="") {
        while(strlen($OTP_resource)<128){
            $cadn=rand(0, 255);
            if($cadn<16) {
                $OTP_resource = $OTP_resource . "0" . dechex($cadn);
            } else {
                $OTP_resource = $OTP_resource . dechex($cadn);
            }
        }
        $filename = "msgs/" . $OTP_resource . ".js";
    }
    file_put_contents($filename, $ciphertext);
    /*
    $fp = fopen($filename, 'w');
    fwrite($fp, $ciphertext);
    fclose($fp);
    */
    echo "var iv_OTP=\"$iv_OTP\"; var OTP_resource=\"$OTP_resource\"; var OTP=\"$OTP\"; var init_OTProcessed=255;error=\"Success\"\n";
}
if ( isset($_POST["iv_OTP"]) &&  isset($_POST["OTP_resource"]) &&  isset($_POST["OTP"]) &&  isset($_POST["texto2"])) {
    $filename = "msgs/" . $_POST["OTP_resource"] . ".js";
    $ciphertext=file_get_contents($filename);
    $fecha2=gmdate("Y-m-d H:i");
    $fecha1 = openssl_decrypt(base64_decode($ciphertext), 'aes-256-ctr', $_POST["OTP"], OPENSSL_RAW_DATA, $_POST["iv_OTP"]);
    $mins=1;
    unlink($filename);
    if($fecha1 != $fecha2) {
        while($mins<5) {
            $fecha2=minus($mins);
            if($fecha1==$fecha2) break;
            $mins=$mins+1;
        }
    }
    if($mins<5) {
        $filename="";
        while(strlen($filename)<128){
            $cadn=rand(0, 255);
            if($cadn<16) {
                $filename = $filename . "0" . dechex($cadn);
            } else {
                $filename = $filename . dechex($cadn);
            }
        }
        $fp = fopen("msgs/" . $filename . ".js", 'w');
        fwrite($fp, $_POST["texto2"]);
        fclose($fp);
        echo "var error=\"Success\";var filename=\"$filename\"";
    } else {
        echo "var error=\"Could not decrypt $mins $fecha1 $fecha2\";";
    }
}
?>
