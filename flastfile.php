<?php
if ( isset($_GET["sha"]) && isset($_GET["count"])) {
    $count=$_GET["count"]*1;
    $sha=$_GET["sha"];
    $continua=true;
    while($continua) {
        $sha2=hash('sha512', "$sha$count");
        $sha2="msgs/$sha2.js";
        if (!file_exists($sha2)) {
            echo "var error=\"Success\"; var sha=\"$sha2\"; var count=$count;";
            $continua=false;
            break;
        }
        $count+=1;
    }
} else {
    echo "var error=\"Missing Argument\";";
}
?>
