<?php
if ( $_GET["fname"] ) {
    $filename="msgs/" . $_GET["fname"];
    if(file_exists($filename)) {
        echo "var error=\"Success\"; var content=\"" . base64_encode(file_get_contents($filename)) . "\"; ";
    } else {
        echo "var error=\"Not Found\";";
    }
} else {
    echo "var error=\"Missing Argument\";";
}
?>
