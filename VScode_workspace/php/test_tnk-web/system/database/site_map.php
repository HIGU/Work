<?php
if ($dir = opendir("/var/www/html/")) {
    while (($file = readdir($dir)) !== false) {
        if ($file != "." && $file != "..") {
            echo "$file\n";
        }
    } 
    closedir($dir);
}
?> 