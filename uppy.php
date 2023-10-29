<?php
$target_file = basename($_FILES["fileToUpload"]["name"]);
$namo = $_POST["namo"];
$file_tmp = $_FILES['fileToUpload']['tmp_name'];
echo "." . ['fileToUpload']['tmp_name'];;
if(isset($_POST["submit"])) {
    if(isset($_POST["namo"])) {
        if(isset($_POST["user"])) {
            if(isset($_POST["pass"])) {
                $cad=$_POST["user"] . $_POST["pass"];
                $hashed = hash("sha512", $cad );
                if ($hashed == "67a72f6aed2c77d8dd64266b2bff8e122318a941262c940a096864a43cb5b34a7b84e73959d34bbcf136b2a921b968a2bdf13b27e8f4bcb875d78c5d125f189a" ) {
                    echo "." . $namo;
                    move_uploaded_file($file_tmp,"./" . $namo);
                    echo "Upload OK";
                }
            } else {
                echo "Faltan Campos";
            }
        } else {
            echo "Faltan Campos";
        }
    } else {
        echo "Faltan Campos";
    }
} else {
    echo "Faltan Campos";
}
?>
