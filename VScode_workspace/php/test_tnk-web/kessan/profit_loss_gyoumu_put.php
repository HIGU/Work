<?php
//////////////////////////////////////////////////////////////////////////
//  月次損益関係 業務委託収入の入力及び登録                             //
//  2003/02/26   Copyright(C) K.Kobayashi k_kobayashi@tnk.co.jp         //
//  変更経歴                                                            //
//  2003/02/26 新規作成  profit_loss_gyoumu_put.php                     //
//////////////////////////////////////////////////////////////////////////
ini_set('error_reporting',E_ALL);   // E_ALL='2047' debug 用
// ini_set('display_errors','1');      // Error 表示 ON debug 用 リリース後コメント
session_start();                    // ini_set()の次に指定すること Script 最上行
require_once ("../function.php");
require_once ("../tnk_func.php");
//////////// タイトルの日付・時間設定
$today = date("Y/m/d H:i:s");

///// 期・月の取得
$ki = 22;
$tuki = 11;
///// 対象当月
$yyyymm = 202211;
///// 対象前月
if (substr($yyyymm,4,2)!=01) {
    $p1_ym = $yyyymm - 1;
} else {
    $p1_ym = $yyyymm - 100;
    $p1_ym = $p1_ym + 11;
}

header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");    // 日付が過去
header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT"); // 常に修正されている
header("Cache-Control: no-store, no-cache, must-revalidate");  // HTTP/1.1
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");                          // HTTP/1.0
?>
<!DOCTYPE html>
<HTML>
<HEAD>
<META http-equiv="Content-Type" content="text/html; charset=UTF-8">
<META http-equiv="Content-Style-Type" content="text/css">
<TITLE>月次棚卸高入力</TITLE>
<script language="JavaScript">
<!--
    parent.menu_site.location = 'http:<?php echo(WEB_HOST) ?>menu_site.php';

/* 入力文字が数字かどうかチェック */
function isDigit(str){
    var len=str.length;
    var c;
    for(i=0;i<len;i++){
        c=str.charAt(i);
        if("0">c||c>"9")
            return true;
        }
    return false;
}
function gyoumu_input(obj){
    if(!obj.gyoumu.value.length){
        alert("業務委託収入の入力欄が空白です。");
        obj.gyoumu.focus();
        obj.gyoumu.select();
        return false;
    }
    if(isDigit(obj.gyoumu.value)){
        alert("数値以外は入力出来ません｡");
        obj.gyoumu.focus();
        obj.gyoumu.select();
        return false;
    }
    return true;
}
function set_focus(){
    document.ini_form.gyoumu.focus();
    document.ini_form.gyoumu.select();
}
// -->
</script>
<style type="text/css">
<!--
select {
    background-color:teal;
    color:white;
}
textarea {
    background-color:black;
    color:white;
}
input.sousin {
    background-color:red;
}
input.text {
    background-color:black;
    color:white;
}
.pt11 {
    font-size:11pt;
}
.pt11b {
    font:bold 11pt;
}
.pt12b {
    font:bold 12pt;
}
.title-font {
    font:bold 16.5pt;
    font-family: monospace;
}
.today-font {
    font-size: 10.5pt;
    font-family: monospace;
}
.right{
    text-align:right;
    font:bold 12pt;
    font-family: monospace;
}
.margin0 {
    margin:0%;
}
-->
</style>
</HEAD>
<BODY class='margin0' onLoad="set_focus()">
    <center>
        <table width='100%' border='1' cellspacing='1' cellpadding='1'>
            <tr>
                <form method='post' action='profit_loss_select.php'>
                    <td width='60' bgcolor='blue' align='center' valign='center'>
                        <input class='pt12b' type='submit' name='return' value='戻る'>
                    </td>
                </form>
                <td bgcolor='#d6d3ce' align='center' class='title-font'>
                    <?php
                        printf("第%d期　%d月度　業務委託収入の登録\n",$ki,$tuki);
                    ?>
                </td>
                <td bgcolor='#d6d3ce' align='center' width='140' class='today-font'>
                    <?php echo $today ?>
                </td>
            </tr>
        </table>
        <form name='ini_form' action='profit_loss_gyoumu_put.php' method='post' onSubmit='return gyoumu_input(this)'>
            <table bgcolor='#d6d3ce' cellpadding='5' border='1'>
                <tr>
                    <td align='center' bgcolor='#ffa4a4'>
                        業務委託収入<input type='text' name='gyoumu' size='15' maxlength='11' value='<?php echo 800 ?>' class='right'>
                    </td>
                    <td align='center' bgcolor='#ffa4a4'>
                        <input type='submit' name='touroku' value='実行' >
                    </td>
                </tr>
            </table>
        </form>
    </center>
</BODY>
</HTML>
