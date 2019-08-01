<?php
    require '../functions.php';

    $clear = $_GET['clear'];
    
    if ($clear) clearFileContent(__DIR__.'/id.ini');
?>
