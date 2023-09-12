<?php
if ( $_GET["fname"] ) {
    $filename="msgs/" . $_GET["fname"];
    if(file_exists($filename)) {
        echo "var error=\"Success\"; ";
        if(isset($_GET["nocontent"])) {
            echo "\nvar content=\"\";\n";
            echo "\nvar content2=\"\";\n";
        } else {
            echo "\nvar content=\"" . base64_encode(file_get_contents($filename)) . "\";\n";
        }
    } else {
        echo "var error=\"Not Found\";";
    }
} else {
    echo "var error=\"Missing Argument\";";
}
?>
