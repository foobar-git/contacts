<?php

function clearFileContent ($file) {
    $myfile = fopen("$file", "w") or die("Unable to open file.");
    fwrite($myfile, '');
    fclose($myfile);
}

function readWriteToFile ($flag, $id, $file) {
    if ($flag) { // write to file
        $myfile = fopen($file, "w") or die("Unable to open file.");
        fwrite($myfile, $id);
    }
    else { // read from file
        $myfile = fopen("$file", "r") or die("Unable to open file.");
        $id = fgets($myfile); // reads single line from file
        //$id = fread($myfile,filesize("id.ini")); // reads whole file
        
        // clear file content after read
        clearFileContent($file);
    }

    fclose($myfile);
    return $id;
}

?>