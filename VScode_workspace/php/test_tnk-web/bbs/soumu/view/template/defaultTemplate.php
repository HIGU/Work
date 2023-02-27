<?php require(APP_TEMPLATE_DIR."parts/header.php");?>
<h1><?php echo APP_TITLE; ?></h1>

<?php if ($cachemode != "yes") { ?>
<div class="waku">

<table width="95%"><tr><td>

<script language="javascript" src="<?php echo APP_FILENAME."?m=writebox&cname=".urlencode($cname); ?>"></script>
<noscript>

<table width="95%">
<form method="post" action="<?php echo APP_FILENAME; ?>" name="fm1">
<input type="hidden" name="m" value="write">
<tr>
<td width=1% nowrap align=right valign=top>名前 : </td>
<td width=99%><input type="text" size="20" name="name" value="<?php echo $cname; ?>"></td>
</tr>
<tr>
<td width=1% nowrap align=right valign=top>タイトル : </td>
<td width=99%><input type="text" size="80" name="title"></td>
</tr>
<tr>
<td width=1% nowrap align=right valign=top>メッセージ :</td>
<td width=99%><textarea name="message" rows="5" cols="80"></textarea></td>
</tr>
<tr>
<td width=1% nowrap align=right valign=top>削除キー : </td>
<td width=99%><input type="password" size="9" name="delkey"></td>
</tr>
<tr>
<td width=1% nowrap></td>
<td width=99%><input type="submit" value="　　投稿　　">
</td>
</tr>
</form>
</table>

</noscript>

</td></tr></table>

</div>
<?php } ?>

<div class="waku_i">
<a href="<?php echo APP_FILENAME.""; ?>">リロード</a>
<?php if (APP_MAIL_POST) { ?>
 | 
<a href="<?php echo APP_FILENAME."?m=pop3"; ?>">メール投稿チェック</a>
<?php } ?>
</div>
<?php

$SELF = APP_FILENAME;

if ($cachemode == "yes") {
    $IPATH = "../";
    $HPATH = "";
} else {
    $IPATH = "";
    $HPATH = "html/";
}

foreach ($objs as $obj) {
    $id = $obj->get("id");
    $name = $obj->get("name");
    $mail = $obj->get("mail");
    $title= $obj->get("title");
    $mess = $obj->get("message");
    $url  = $obj->get("url");
    $date = date("Y/m/d H:i:s",$obj->get("date"));
    if (!file_exists($file)){ $file = ""; }
    
    if ($mail != "") { $name = "<a href=mailto:$mail>$name</a>"; }
    if ($url  != "") { $url  = "<a href=$url>$url</a>"; }
    
    if ($title!= "") { $title = "$title - "; }
    if ($mess != "") { $mess = $mess."<br>"; }
    
    echo <<<EOM
<div class="waku_i">
<table width=95%>
<tr>
<td width=100% valign=top>
<div style="margin:8px;line-height:140%">
<strong>$title</strong><strong>$name</strong> <span style="font-size:10px;font-family:Tahoma">[$date]</span> [ <a href="$HPATH${id}.html">1件表示</a> ] [ <a href="${SELF}?m=delete&id=$id">削除</a> ]<br />
$mess
<hr>
EOM;
    // 記事レスの表示
    $resobjs = Article_Res::getObjects(APP_RES_DIR.$id.".cgi");
    
    foreach ($resobjs as $resobj) {
        $res_name = $resobj->get("name");
        $res_message = $resobj->get("message");
        $res_date = date("Y/m/d H:i:s",$resobj->get("date"));
        echo <<<EOM
$res_name <span style="font-size:10px;font-family:Tahoma">[$res_date]</span><br />
<div class="res">
$res_message
</div>
EOM;
    }
    
    echo <<<EOM
<form method="post" action="$SELF">
<input type="hidden" name="m" value="res">
<input type="hidden" name="id" value="$id">
名前: <input type="text" name="name" size="10" value="$cname">
メッセージ: <textarea name="message" rows="1" cols="60"></textarea>
<input type="submit" value="返信">
</form>
</div>
</td>
</tr>
</table>
</div>
EOM;
}

?>

<?php

// ページインデックスの出力
if ($cachemode != "yes") {
    $pageindex->draw(APP_TEMPLATE_DIR."parts/page_index.php");
} else {
    $pageindex->draw(APP_TEMPLATE_DIR."parts/page_index2.php");
}

?>

<?php require(APP_TEMPLATE_DIR."parts/footer.php");?>