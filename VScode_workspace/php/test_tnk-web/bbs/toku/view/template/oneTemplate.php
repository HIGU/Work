<?php require(APP_TEMPLATE_DIR."parts/header.php");?>

<div class="waku_i">
<a href="<?php echo APP_FILENAME.""; ?>">リロード</a>
<?php if (APP_MAIL_POST) { ?>
 | 
<a href="<?php echo APP_FILENAME."?m=pop3"; ?>">メール投稿チェック</a>
<?php } ?>
</div>
<br />
<div class="waku">
<?php

$SELF = APP_FILENAME;

if ($cachemode == "yes") {
    $IPATH = "../";
} else {
    $IPATH = "";
}

$id = $obj->get("id");
$name = $obj->get("name");
$mail = $obj->get("mail");
$title= $obj->get("title");
$mess = $obj->get("message");
$url  = $obj->get("url");
if (!file_exists($file)){ $file = ""; }

if ($mail != "") { $name = "<a href=mailto:$mail>$name</a>"; }
if ($url  != "") { $url  = "<a href=$url>$url</a>"; }


echo <<<EOM
<strong>$name</strong><br />
$mess
<hr>
EOM;

// 記事レスの表示
$resobjs = Article_Res::getObjects(APP_RES_DIR.$id.".cgi");

foreach ($resobjs as $resobj) {
    $res_name = $resobj->get("name");
    $res_message = $resobj->get("message");
    $res_date = date("Y/m/d H:i:s",$resobj->get("date"));
    echo "$res_name - $res_message <span style=\"font-size:10px;font-family:Tahoma\">[$res_date]</span><br />\n";
}

if (count($resobjs) > 0) {
    echo "<hr>";
}

?>
<a href="javascript:history.go(-1)">もどる</a>
</div>

<?php require(APP_TEMPLATE_DIR."parts/footer.php");?>