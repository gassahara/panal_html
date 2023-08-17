<?php
if ($_POST["texto2"]) {
    while(strlen($fn)<128){
        $cadn=rand(0, 255);
        if($cadn<16) {
            $fn = $fn . "0" . dechex($cadn);
        } else {
            $fn = $fn . dechex($cadn);
        }
    }
    $filename = $fn . ".js";
    while (file_exists($filename)) {
        $filename = $fn . ".js";
    }

    if(isset($_POST["name"])) {
        $fn=$_POST["name"] . ".js";
        $fp = fopen($fn, 'w');
        fwrite($fp, "processed=255;");
        fclose($fp);
    }
        
    $fp = fopen($filename, 'w');
    fwrite($fp, $_POST["texto2"]);
    fclose($fp);
    
    echo $fn;
    echo $filename;
    echo $_POST["texto2"];
    //echo "<br>" . "<SCRIPT>history.go(-1);</SCRIPT></BODY></HTML>";
}
?>
