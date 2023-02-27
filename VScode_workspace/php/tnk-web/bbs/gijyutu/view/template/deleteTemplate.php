<?php require(APP_TEMPLATE_DIR."parts/header.php");?>

<div class="waku">
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

$requri = $cgi->env("REQUEST_URI");

$c_user = $cgi->cookie->get("USER");
$c_pass = $cgi->cookie->get("PASS");

echo <<<EOM
$name - $mess<br>
$file_tag
$file_name
<hr>
<strong>投稿者自身による削除</strong>
<form method="post" action="$SELF">
<input type="hidden" name="m" value="delete">
<input type="hidden" name="id" value="$id">
<input type="hidden" name="exec" value="1">
<input type="hidden" name="from" value="$requri">
削除キー: <input type="password" name="delkey" size="8" value="">
<input type="submit" value="削除する">
</form>
<hr>
<strong>管理人による削除</strong>
<form method="post" action="$SELF">
<input type="hidden" name="m" value="delete">
<input type="hidden" name="id" value="$id">
<input type="hidden" name="exec" value="1">
<input type="hidden" name="from" value="$requri">
USER: <input type="text" name="user" size="8" value="">
PASS: <input type="password" name="pass" size="8" value="">
<input type="submit" value="削除する">
</form>
EOM;

?>
<a href="javascript:history.go(-1)">もどる</a>
</div>

<?php require(APP_TEMPLATE_DIR."parts/footer.php");?>