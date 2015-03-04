<?php
session_start();
$target_dir = "uploads_tmp/";
if(isset($_FILES["fileToUpload_1"])){
    
    $target_file_1 = $target_dir . basename($_FILES["fileToUpload_1"]["name"]);
    if (move_uploaded_file($_FILES['fileToUpload_1']['tmp_name'], $target_file_1)) {
        echo "El archivo es válido y fue cargado exitosamente.\n";
        $_SESSION['archivo_1'] = $_FILES["fileToUpload_1"]["name"];
        header("Location: index.php");
        die();
    } else {
        echo "¡Posible ataque de carga de archivos!\n";
    }
}
if(isset($_FILES["fileToUpload_2"])){
    $target_file_2 = $target_dir . basename($_FILES["fileToUpload_2"]["name"]);
    if (move_uploaded_file($_FILES['fileToUpload_2']['tmp_name'], $target_file_2)) {
        $_SESSION['archivo_2'] = $_FILES["fileToUpload_2"]["name"];
        header("Location: index.php");
        die();
    } else {
        echo "¡Posible ataque de carga de archivos!\n";
    }
}
?>