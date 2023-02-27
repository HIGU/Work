<?php

$buff = "";

$buff .= <<<EOM
document.write("<div style=\"margin-bottom:px\">");
EOM;

foreach ($objs as $obj) {
    $id = $obj->get("id");
    $name = $obj->get("name");
    $mail = $obj->get("mail");
    $title= $obj->get("title");
    $mess = $obj->get("message");
    $url  = $obj->get("url");
    
    if ($title != "") { $title = "$title - "; }
    $href = $scriptpath."html/".$id.".html";
    
    $buff .= <<<EOM
document.write("<a href=\"$href\">$title$name</a><br>");
EOM;
}

$buff .= <<<EOM
document.write("</div>");\n
document.write("<br />");\n
EOM;

if ($code == "s") {
    if (function_exists("mb_convert_encoding")) {
        $buff = mb_convert_encoding($buff,"SJIS","EUC-JP");
    } else {
        include_once(APP_LIB_DIR.'lib/jcode.php');
        $buff = EUCtoSJIS($buff);
    }
} elseif($code == "u") {
    if (function_exists("mb_convert_encoding")) {
        $buff = mb_convert_encoding($buff,"UTF-8","EUC-JP");
    } else {
        include_once(APP_LIB_DIR.'lib/jcode.php');
        require(APP_LIB_DIR."lib/code_table.jis2ucs");
        $buff = EUCtoUTF8($buff,$table_jis_utf8);
    }
}

echo $buff;
exit;

?>