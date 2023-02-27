<?php require(APP_TEMPLATE_DIR."parts/header.php");?>

<?php

$SELF = APP_FILENAME;

$id = $obj->get("id");
$name = $obj->get("name");
$mail = $obj->get("mail");
$title= $obj->get("title");
$mess = $obj->get("message");
$url  = $obj->get("url");
$file = $obj->get("file");
$file2 = $obj->get("file2");
if (!file_exists($file)){ $file = ""; }

if ($mail != "") { $name = "<a href=mailto:$mail>$name</a>"; }
if ($url  != "") { $url  = "<a href=$url>$url</a>"; }

if ($file != "" && is_readable($file)) {
    $file_tag = "<img src=\"$file\">";
}
if ($file != "") {
    $file_name = "<br><small>[$file]</small>";
}
echo <<<EOM
$name - $mess<br>
$url<br>
$file_tag
$file_name
<hr size=1 color=#c0c0c0>
EOM;

?>
<a href="javascript:history.go(-1)">¤â¤É¤ë</a>
<?php require(APP_TEMPLATE_DIR."parts/footer.php");?>