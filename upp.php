<?php
$public = file_get_contents('public.pem');
function minus($mins) {
    $current_time = time(); // Get the current timestamp
    $new_time = $current_time - ($mins * 60); // Subtract 5 minutes (5 * 60 seconds)
    $formatted_time = gmdate("Y-m-d H:i", $new_time); // Format the new timestamp    
    return $formatted_time; // Print the new time
}
if ( $_POST["namo"] &&  $_POST["signature"] &&  $_POST["content"] &&  $_POST["datesigned"] && $_POST["submit"] ) {
    $ok=0;
    $content=$_POST["content"];
    $signature=base64_decode($_POST["signature"]);
    $datesigned=base64_decode($_POST["datesigned"]);
    $ok = openssl_verify($content, $signature, $public, OPENSSL_ALGO_SHA256);
    echo "($ok)";
    if ($ok == 1) {
        $ok=0;
        $fecha2=gmdate("Y-m-d H:i");
        $ok = openssl_verify($fecha2, $datesigned, $public, OPENSSL_ALGO_SHA256);
        echo "..." . $ok;
        $mins=1;
        echo "$fecha2 $ok";
        while($ok != 1 && $mins<5) {
            $ok = openssl_verify($fecha2, $datesigned, $public, OPENSSL_ALGO_SHA256);
            $fecha2=minus($mins);
            echo "$fecha2 $ok";
            $mins=$mins+1;
        }
        if($ok) {
            $content=$_POST["content"];
            $filename="msgs/" . $_POST["namo"];
            echo "var filename=\"$filename\";\n";
                if (!$fp = fopen($filename, 'w')) {
                    echo "var error=\"Cannot open file ($filename)\";";
                    exit;
                }
                $content=base64_decode($_POST["content"]);
                if (fwrite($fp, $content) === FALSE) {
                    echo "var error=\"Cannot write to file ($filename)\";";
                    exit;
                }
                echo "var error=\"Success, wrote ($content) to file ($filename)\";";
                fclose($fp);
            echo "var error=\"Success\"; var filename=\"$filename\";";
        } else {
            echo "var error=\"Could not decrypt\";";
        }
    }
} else {
    echo "var error=\"Missing Arguments\";";
}
?>
