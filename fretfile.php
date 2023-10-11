<?php
if ( $_GET["fname"] ) {
    $filename="msgs/" . $_GET["fname"];
    if(file_exists($filename)) {
        if(isset($_GET["nocontent"])) {
            echo "\nvar content=\"\";\n";
            echo "\nvar content2=\"\";\n";
        } else {
            echo "\nvar content=\"" . base64_encode(file_get_contents($filename)) . "\";\n";
        }
        echo "var error=\"Success\"; ";
    } else {
        echo "var error=\"Not Found\";";
    }
} else {
    echo "var error=\"Missing Argument\";";
}
?>
