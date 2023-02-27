<?php
//////////////////////////////////////////////////////////////////////////
//  月次損益関係 売上原価の調整入力及び登録                             //
//  2003/02/24   Copyright(C) K.Kobayashi k_kobayashi@tnk.co.jp         //
//  変更経歴                                                            //
//  2003/02/24 新規作成  profit_loss_adjust_ugenka.php                  //
//  2003/02/24 登録済みのAS/400 売上原価を参考として表示(照会)させる    //
//  2003/03/10 調整後の売上原価 照会を追加                              //
//////////////////////////////////////////////////////////////////////////
ini_set('error_reporting',E_ALL);   // E_ALL='2047' debug 用
// ini_set('display_errors','1');      // Error 表示 ON debug 用 リリース後コメント
session_start();                    // ini_set()の次に指定すること Script 最上行
require_once ("../function.php");
require_once ("../tnk_func.php");
access_log();       // Script Name は自動取得
$_SESSION["site_index"] = 10;       // 月次損益関係=10 最後のメニューは 99 を使用
$_SESSION["site_id"] = 7;           // 下位メニュー無し (0 <=)
if (account_group_check() == FALSE) {
    $_SESSION["s_sysmsg"] = "あなたは許可されていません｡<br>管理者に連絡して下さい｡";
    header("Location: http:" . WEB_HOST . "menu.php");
    exit();
}
//////////// タイトルの日付・時間設定
$today = date("Y/m/d H:m:s");

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
<TITLE>月次棚卸高調整入力</TITLE>
<script language="JavaScript">
<!--
    parent.menu_site.location = 'http:<?php echo(WEB_HOST) ?>menu_site.php';

/* 入力文字頭１桁の '+' '-' '0' チェック */
function isPlusMinus(str) {
    var c = str.charAt(0);
    if ((c == '+') || (c == '-') || (c == '0')) {
        return true;
    }
    return false;
}
/* 入力文字が数字かどうかチェック */
function isDigit(str) {
    var len=str.length;
    var c;
    for (i=1; i<len; i++) {
        c = str.charAt(i);
        if((c < "0") || (c > "9")) {
            return true;
        }
    }
    return false;
}
function adjust_input(obj){
    if(!obj.adjust.value.length){
        alert("売上原価の調整額が空白です。");
        obj.adjust.focus();
        obj.adjust.select();
        return false;
    }
    if(!isPlusMinus(obj.adjust.value)){
        alert("頭に＋－の符号を付けて下さい｡\n調整しない場合は０を入れて下さい｡");
        obj.adjust.focus();
        obj.adjust.select();
        return false;
    }
    if(isDigit(obj.adjust.value)){
        alert("数値以外は入力出来ません｡");
        obj.adjust.focus();
        obj.adjust.select();
        return false;
    }
    return true;
}
function set_focus(){
    document.adjust_form.adjust.focus();
    document.adjust_form.adjust.select();
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
    font-family: monospace;
}
.pt11b {
    font:bold 11pt;
    font-family: monospace;
}
.pt12b {
    font:bold 12pt;
    font-family: monospace;
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
                        printf("第%d期　%d月度　期末棚卸高 調整額の入力\n",$ki,$tuki);
                    ?>
                </td>
                <td bgcolor='#d6d3ce' align='center' width='140' class='today-font'>
                    <?php echo $today ?>
                </td>
            </tr>
        </table>
        <form name='adjust_form' action='profit_loss_adjust_ugenka.php' method='post' onSubmit='return adjust_input(this)'>
            <table bgcolor='#d6d3ce' cellpadding='5' border='1'>
                <tr>
                    <td colspan='3' align='center' class='pt11'>
                        調整金額は頭に＋－をつけて入力して下さい｡
                    </td>
                </tr>
                <tr>
                    <td colspan='3' align='center' class='pt11'>
                        調整しない場合は０を入力して下さい｡
                    </td>
                </tr>
                <tr>
                    <td align='center' bgcolor='#ffa4a4' class='pt12b'>
                        全体の売上原価 調整額<input type='text' name='adjust' size='15' maxlength='11' value='<?php echo 700 ?>' class='right'>
                    </td>
                    <td align='center'>　</td> <!-- 余白 -->
                </tr>
                <tr>
                    <td align='left' class='pt11'>
                        調整理由<input type='text' name='reason' size='100' maxlength='100' value='<?php echo 700 ?>' class='pt9'>
                    </td>
                    <td align='center' bgcolor='#ffa4a4'>
                        <input type='submit' name='touroku' value='実行' class='pt11b'>
                    </td>
                </tr>
            </table>
        </form>
        <table bgcolor='#d6d3ce' cellpadding='5' border='1'>
            <caption class='pt12b'>AS/400 売上原価 照会</caption>
            <tr>
                <td bgcolor='#ffff94' width='300' align='center' class='pt12b'>全体 売上原価</td><!-- 薄い黄色 -->
                <td  width='300' align='right' class='pt12b'><?php echo 700 ?></td>
            </tr>
        </table>
        <br>
        <table bgcolor='#d6d3ce' cellpadding='5' border='1'>
            <caption class='pt12b'>調整後の売上原価 照会</caption>
            <tr>
                <td bgcolor='#ffff94' width='300' align='center' class='pt12b'>全体 売上原価</td><!-- 薄い黄色 -->
                <td  width='300' align='right' class='pt12b'><?php echo 700 ?></td>
            </tr>
        </table>
    </center>
</BODY>
</HTML>
