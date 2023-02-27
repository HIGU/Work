<?php
if ($dir = opendir("/home/www/html/tnk-web/")) {
    while (($file = readdir($dir)) !== false) {
        if ($file != "." && $file != "..") {
            echo "$file\n";
        }
    } 
    closedir($dir);
}
?> 