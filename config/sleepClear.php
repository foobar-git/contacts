<?php
    require '../functions.php';

    $clear = $_GET['clear'];

    if ($clear == 'sleepThenClear') {
        sleep(30);
        clearFileContent(__DIR__.'/id.ini');
    }
?>