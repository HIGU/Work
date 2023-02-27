<?php
//////////////////////////////////////////////////////////////////////////
// 月次損益関係 期末棚卸高入力及び登録                                  //
// Copyright(C) 2003-2015 K.Kobayashi tnksys@nitto-kohki.co.jp          //
// 変更経歴                                                             //
// 2003/02/19 新規作成  profit_loss_inventory_put.php                   //
// 2003/02/19 文字サイズをブラウザーで変更できなくした title-font 等    //
// 2003/02/23 date("Y/m/d H:m:s") → H:i:s のミス修正                   //
// 2013/12/02 特注棚卸高の入力を追加                               大谷 //
// 2013/12/04 特注のCC部品入力欄を追加                             大谷 //
// 2014/01/15 特注棚卸高合計は入力しないので背景をグレーに変更     大谷 //
// 2015/06/02 ツール棚卸高の入力を追加                             大谷 //
//////////////////////////////////////////////////////////////////////////
ini_set('error_reporting',E_ALL);   // E_ALL='2047' debug 用
// ini_set('display_errors','1');      // Error 表示 ON debug 用 リリース後コメント
session_start();                    // ini_set()の次に指定すること Script 最上行
require_once ("../function.php");
require_once ("../tnk_func.php");
access_log();       // Script Name は自動取得
$_SESSION["site_index"] = 10;       // 月次損益関係=10 最後のメニューは 99 を使用
$_SESSION["site_id"]    =  7;       // 下位メニュー無し (0 <=)
if (account_group_check() == FALSE) {
    $_SESSION["s_sysmsg"] = "あなたは許可されていません。<br>管理者に連絡して下さい。";
    header("Location: http:" . WEB_HOST . "menu.php");
    exit();
}
//////////// タイトルの日付・時間設定
$today = date("Y/m/d H:i:s");

///// 期・月の取得
$ki = Ym_to_tnk($_SESSION['pl_ym']);
$tuki = substr($_SESSION['pl_ym'],4,2);
///// 対象当月
$yyyymm = $_SESSION['pl_ym'];
///// 対象前月
if (substr($yyyymm,4,2)!=01) {
    $p1_ym = $yyyymm - 1;
} else {
    $p1_ym = $yyyymm - 100;
    $p1_ym = $p1_ym + 11;
}

if (!isset($_POST['touroku'])) {     // データ入力
    ////////// 登録済みならば棚卸金額取得
    $query = sprintf("select kin from act_invent_history where pl_bs_ym=%d and note='カプラ'", $yyyymm);
    getUniResult($query,$c_kin);
    $query = sprintf("select kin from act_invent_history where pl_bs_ym=%d and note='リニア'", $yyyymm);
    getUniResult($query,$l_kin);
    $query = sprintf("select kin from act_invent_history where pl_bs_ym=%d and note='特注材料'", $yyyymm);
    getUniResult($query,$zai_kin);
    $query = sprintf("select kin from act_invent_history where pl_bs_ym=%d and note='特注部品'", $yyyymm);
    getUniResult($query,$buhin_kin);
    $query = sprintf("select kin from act_invent_history where pl_bs_ym=%d and note='特注外注'", $yyyymm);
    getUniResult($query,$gai_kin);
    $query = sprintf("select kin from act_invent_history where pl_bs_ym=%d and note='特注工作'", $yyyymm);
    getUniResult($query,$kou_kin);
    $query = sprintf("select kin from act_invent_history where pl_bs_ym=%d and note='特注組立'", $yyyymm);
    getUniResult($query,$kumi_kin);
    $query = sprintf("select kin from act_invent_history where pl_bs_ym=%d and note='特注検査'", $yyyymm);
    getUniResult($query,$ken_kin);
    $query = sprintf("select kin from act_invent_history where pl_bs_ym=%d and note='特注ＣＣ'", $yyyymm);
    getUniResult($query,$cc_kin);
    $query = sprintf("select kin from act_invent_history where pl_bs_ym=%d and note='特注合計'", $yyyymm);
    getUniResult($query,$ctokut_kin);
    $query = sprintf("select kin from act_invent_history where pl_bs_ym=%d and note='ツール材料'", $yyyymm);
    getUniResult($query,$tzai_kin);
    $query = sprintf("select kin from act_invent_history where pl_bs_ym=%d and note='ツール部品'", $yyyymm);
    getUniResult($query,$tbuhin_kin);
    $query = sprintf("select kin from act_invent_history where pl_bs_ym=%d and note='ツール外注'", $yyyymm);
    getUniResult($query,$tgai_kin);
    $query = sprintf("select kin from act_invent_history where pl_bs_ym=%d and note='ツール工作'", $yyyymm);
    getUniResult($query,$tkou_kin);
    $query = sprintf("select kin from act_invent_history where pl_bs_ym=%d and note='ツール組立'", $yyyymm);
    getUniResult($query,$tkumi_kin);
    $query = sprintf("select kin from act_invent_history where pl_bs_ym=%d and note='ツール検査'", $yyyymm);
    getUniResult($query,$tken_kin);
    $query = sprintf("select kin from act_invent_history where pl_bs_ym=%d and note='ツールＣＣ'", $yyyymm);
    getUniResult($query,$tcc_kin);
    $query = sprintf("select kin from act_invent_history where pl_bs_ym=%d and note='ツール合計'", $yyyymm);
    getUniResult($query,$toolt_kin);
} else {                            // 登録処理
    $query = sprintf("select kin from act_invent_history where pl_bs_ym=%d and note='カプラ'", $yyyymm);
    if (getUniResult($query,$c_kin) <= 0) {
        $query = sprintf("insert into act_invent_history (pl_bs_ym, kin, note) values (%d, %d, 'カプラ')", $yyyymm, $_POST['invent_c']);
        query_affected($query);
    } else {
        $query = sprintf("update act_invent_history set kin=%d where pl_bs_ym=%d and note='カプラ'", $_POST['invent_c'], $yyyymm);
        query_affected($query);
    }
    $query = sprintf("select kin from act_invent_history where pl_bs_ym=%d and note='リニア'", $yyyymm);
    if (getUniResult($query,$l_kin) <= 0) { //$c_kinから直した
        $query = sprintf("insert into act_invent_history (pl_bs_ym, kin, note) values (%d, %d, 'リニア')", $yyyymm, $_POST['invent_l']);
        query_affected($query);
    } else {
        $query = sprintf("update act_invent_history set kin=%d where pl_bs_ym=%d and note='リニア'", $_POST['invent_l'], $yyyymm);
        query_affected($query);
    }
    $query = sprintf("select kin from act_invent_history where pl_bs_ym=%d and note='特注材料'", $yyyymm);
    if (getUniResult($query,$zai_kin) <= 0) {
        $query = sprintf("insert into act_invent_history (pl_bs_ym, kin, note) values (%d, %d, '特注材料')", $yyyymm, $_POST['invent_zai']);
        query_affected($query);
    } else {
        $query = sprintf("update act_invent_history set kin=%d where pl_bs_ym=%d and note='特注材料'", $_POST['invent_zai'], $yyyymm);
        query_affected($query);
    }
    $query = sprintf("select kin from act_invent_history where pl_bs_ym=%d and note='特注部品'", $yyyymm);
    if (getUniResult($query,$buhin_kin) <= 0) {
        $query = sprintf("insert into act_invent_history (pl_bs_ym, kin, note) values (%d, %d, '特注部品')", $yyyymm, $_POST['invent_buhin']);
        query_affected($query);
    } else {
        $query = sprintf("update act_invent_history set kin=%d where pl_bs_ym=%d and note='特注部品'", $_POST['invent_buhin'], $yyyymm);
        query_affected($query);
    }
    $query = sprintf("select kin from act_invent_history where pl_bs_ym=%d and note='特注外注'", $yyyymm);
    if (getUniResult($query,$gai_kin) <= 0) {
        $query = sprintf("insert into act_invent_history (pl_bs_ym, kin, note) values (%d, %d, '特注外注')", $yyyymm, $_POST['invent_gai']);
        query_affected($query);
    } else {
        $query = sprintf("update act_invent_history set kin=%d where pl_bs_ym=%d and note='特注外注'", $_POST['invent_gai'], $yyyymm);
        query_affected($query);
    }
    $query = sprintf("select kin from act_invent_history where pl_bs_ym=%d and note='特注工作'", $yyyymm);
    if (getUniResult($query,$kou_kin) <= 0) {
        $query = sprintf("insert into act_invent_history (pl_bs_ym, kin, note) values (%d, %d, '特注工作')", $yyyymm, $_POST['invent_kou']);
        query_affected($query);
    } else {
        $query = sprintf("update act_invent_history set kin=%d where pl_bs_ym=%d and note='特注工作'", $_POST['invent_kou'], $yyyymm);
        query_affected($query);
    }
    $query = sprintf("select kin from act_invent_history where pl_bs_ym=%d and note='特注組立'", $yyyymm);
    if (getUniResult($query,$kumi_kin) <= 0) {
        $query = sprintf("insert into act_invent_history (pl_bs_ym, kin, note) values (%d, %d, '特注組立')", $yyyymm, $_POST['invent_kumi']);
        query_affected($query);
    } else {
        $query = sprintf("update act_invent_history set kin=%d where pl_bs_ym=%d and note='特注組立'", $_POST['invent_kumi'], $yyyymm);
        query_affected($query);
    }
    $query = sprintf("select kin from act_invent_history where pl_bs_ym=%d and note='特注検査'", $yyyymm);
    if (getUniResult($query,$ken_kin) <= 0) {
        $query = sprintf("insert into act_invent_history (pl_bs_ym, kin, note) values (%d, %d, '特注検査')", $yyyymm, $_POST['invent_ken']);
        query_affected($query);
    } else {
        $query = sprintf("update act_invent_history set kin=%d where pl_bs_ym=%d and note='特注検査'", $_POST['invent_ken'], $yyyymm);
        query_affected($query);
    }
    $query = sprintf("select kin from act_invent_history where pl_bs_ym=%d and note='特注ＣＣ'", $yyyymm);
    if (getUniResult($query,$cc_kin) <= 0) {
        $query = sprintf("insert into act_invent_history (pl_bs_ym, kin, note) values (%d, %d, '特注ＣＣ')", $yyyymm, $_POST['invent_cc']);
        query_affected($query);
    } else {
        $query = sprintf("update act_invent_history set kin=%d where pl_bs_ym=%d and note='特注ＣＣ'", $_POST['invent_cc'], $yyyymm);
        query_affected($query);
    }
    // 特注棚卸高の合計を計算
    $ctoku_kin = $_POST['invent_zai'] + $_POST['invent_buhin'] + $_POST['invent_gai'] + $_POST['invent_kou'] + $_POST['invent_kumi'] + $_POST['invent_ken'] + $_POST['invent_cc'];
    $query = sprintf("select kin from act_invent_history where pl_bs_ym=%d and note='特注合計'", $yyyymm);
    if (getUniResult($query,$ctoku_total) <= 0) {
        $query = sprintf("insert into act_invent_history (pl_bs_ym, kin, note) values (%d, %d, '特注合計')", $yyyymm, $ctoku_kin);
        query_affected($query);
    } else {
        $query = sprintf("update act_invent_history set kin=%d where pl_bs_ym=%d and note='特注合計'", $ctoku_kin, $yyyymm);
        query_affected($query);
    }
    $query = sprintf("select kin from act_invent_history where pl_bs_ym=%d and note='ツール材料'", $yyyymm);
    if (getUniResult($query,$tzai_kin) <= 0) {
        $query = sprintf("insert into act_invent_history (pl_bs_ym, kin, note) values (%d, %d, 'ツール材料')", $yyyymm, $_POST['invent_tzai']);
        query_affected($query);
    } else {
        $query = sprintf("update act_invent_history set kin=%d where pl_bs_ym=%d and note='ツール材料'", $_POST['invent_tzai'], $yyyymm);
        query_affected($query);
    }
    $query = sprintf("select kin from act_invent_history where pl_bs_ym=%d and note='ツール部品'", $yyyymm);
    if (getUniResult($query,$tbuhin_kin) <= 0) {
        $query = sprintf("insert into act_invent_history (pl_bs_ym, kin, note) values (%d, %d, 'ツール部品')", $yyyymm, $_POST['invent_tbuhin']);
        query_affected($query);
    } else {
        $query = sprintf("update act_invent_history set kin=%d where pl_bs_ym=%d and note='ツール部品'", $_POST['invent_tbuhin'], $yyyymm);
        query_affected($query);
    }
    $query = sprintf("select kin from act_invent_history where pl_bs_ym=%d and note='ツール外注'", $yyyymm);
    if (getUniResult($query,$tgai_kin) <= 0) {
        $query = sprintf("insert into act_invent_history (pl_bs_ym, kin, note) values (%d, %d, 'ツール外注')", $yyyymm, $_POST['invent_tgai']);
        query_affected($query);
    } else {
        $query = sprintf("update act_invent_history set kin=%d where pl_bs_ym=%d and note='ツール外注'", $_POST['invent_tgai'], $yyyymm);
        query_affected($query);
    }
    $query = sprintf("select kin from act_invent_history where pl_bs_ym=%d and note='ツール工作'", $yyyymm);
    if (getUniResult($query,$tkou_kin) <= 0) {
        $query = sprintf("insert into act_invent_history (pl_bs_ym, kin, note) values (%d, %d, 'ツール工作')", $yyyymm, $_POST['invent_tkou']);
        query_affected($query);
    } else {
        $query = sprintf("update act_invent_history set kin=%d where pl_bs_ym=%d and note='ツール工作'", $_POST['invent_tkou'], $yyyymm);
        query_affected($query);
    }
    $query = sprintf("select kin from act_invent_history where pl_bs_ym=%d and note='ツール組立'", $yyyymm);
    if (getUniResult($query,$tkumi_kin) <= 0) {
        $query = sprintf("insert into act_invent_history (pl_bs_ym, kin, note) values (%d, %d, 'ツール組立')", $yyyymm, $_POST['invent_tkumi']);
        query_affected($query);
    } else {
        $query = sprintf("update act_invent_history set kin=%d where pl_bs_ym=%d and note='ツール組立'", $_POST['invent_tkumi'], $yyyymm);
        query_affected($query);
    }
    $query = sprintf("select kin from act_invent_history where pl_bs_ym=%d and note='ツール検査'", $yyyymm);
    if (getUniResult($query,$tken_kin) <= 0) {
        $query = sprintf("insert into act_invent_history (pl_bs_ym, kin, note) values (%d, %d, 'ツール検査')", $yyyymm, $_POST['invent_tken']);
        query_affected($query);
    } else {
        $query = sprintf("update act_invent_history set kin=%d where pl_bs_ym=%d and note='ツール検査'", $_POST['invent_tken'], $yyyymm);
        query_affected($query);
    }
    $query = sprintf("select kin from act_invent_history where pl_bs_ym=%d and note='ツールＣＣ'", $yyyymm);
    if (getUniResult($query,$tcc_kin) <= 0) {
        $query = sprintf("insert into act_invent_history (pl_bs_ym, kin, note) values (%d, %d, 'ツールＣＣ')", $yyyymm, $_POST['invent_tcc']);
        query_affected($query);
    } else {
        $query = sprintf("update act_invent_history set kin=%d where pl_bs_ym=%d and note='ツールＣＣ'", $_POST['invent_tcc'], $yyyymm);
        query_affected($query);
    }
    // ツール棚卸高の合計を計算
    $tool_kin = $_POST['invent_tzai'] + $_POST['invent_tbuhin'] + $_POST['invent_tgai'] + $_POST['invent_tkou'] + $_POST['invent_tkumi'] + $_POST['invent_tken'] + $_POST['invent_tcc'];
    $query = sprintf("select kin from act_invent_history where pl_bs_ym=%d and note='ツール合計'", $yyyymm);
    if (getUniResult($query,$tool_total) <= 0) {
        $query = sprintf("insert into act_invent_history (pl_bs_ym, kin, note) values (%d, %d, 'ツール合計')", $yyyymm, $tool_kin);
        query_affected($query);
    } else {
        $query = sprintf("update act_invent_history set kin=%d where pl_bs_ym=%d and note='ツール合計'", $tool_kin, $yyyymm);
        query_affected($query);
    }
    $_SESSION["s_sysmsg"] .= sprintf("<font color='yellow'>棚卸高登録完了<br>第 %d期 %d月</font>",$ki,$tuki);
    header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
    exit();
}

header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");    // 日付が過去
header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT"); // 常に修正されている
header("Cache-Control: no-store, no-cache, must-revalidate");  // HTTP/1.1
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");                          // HTTP/1.0
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<HTML>
<HEAD>
<META http-equiv="Content-Type" content="text/html; charset=EUC-JP">
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
function invent_input(obj){
    if(!obj.invent_c.value.length){
        alert("カプラ棚卸高の入力欄が空白です。");
        obj.invent_c.focus();
        obj.invent_c.select();
        return false;
    }
    if(isDigit(obj.invent_c.value)){
        alert("数値以外は入力出来ません｡");
        obj.invent_c.focus();
        obj.invent_c.select();
        return false;
    }
    if(!obj.invent_l.value.length){
        alert("リニア棚卸高の入力欄が空白です。");
        obj.invent_l.focus();
        obj.invent_l.select();
        return false;
    }
    if(isDigit(obj.invent_l.value)){
        alert("数値以外は入力出来ません｡");
        obj.invent_l.focus();
        obj.invent_l.select();
        return false;
    }
    return true;
}
function set_focus(){
    document.invent.invent_c.focus();
    document.invent.invent_c.select();
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
.rightg{
    text-align:right;
    font:bold 12pt;
    font-family: monospace;
    background-color:LightGrey;
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
                        printf("第%d期　%d月度　棚卸高(財務会計評価額)の登録\n",$ki,$tuki);
                    ?>
                </td>
                <td bgcolor='#d6d3ce' align='center' width='140' class='today-font'>
                    <?php echo $today ?>
                </td>
            </tr>
        </table>
        <form name='invent' action='profit_loss_inventory_put.php' method='post' onSubmit='return invent_input(this)'>
            <table bgcolor='#d6d3ce' cellpadding='5' border='1'>
                <tr>
                    <td align='center' bgcolor='#ffa4a4'>
                        カプラ棚卸高<input type='text' name='invent_c' size='15' maxlength='11' value='<?php echo $c_kin ?>' class='right'>
                    </td>
                    <td align='center' bgcolor='#ffa4a4'>
                        リニア棚卸高<input type='text' name='invent_l' size='15' maxlength='11' value='<?php echo $l_kin ?>' class='right'>
                    </td>
                </tr>
            </table>
            <BR><BR>
            <table bgcolor='#d6d3ce' cellpadding='5' border='1'>
                <tr>
                    <td align='center' bgcolor='#ffffb3'>
                        特注材料棚卸高<input type='text' name='invent_zai' size='15' maxlength='11' value='<?php echo $zai_kin ?>' class='right'>
                    </td>
                    <td align='center' bgcolor='#ffffb3'>
                        特注部品棚卸高<input type='text' name='invent_buhin' size='15' maxlength='11' value='<?php echo $buhin_kin ?>' class='right'>
                    </td>
                </tr>
            </table>
            <table bgcolor='#d6d3ce' cellpadding='5' border='1'>
                <tr>
                    <td align='center' bgcolor='#ffffb3'>
                        特注工作棚卸高<input type='text' name='invent_kou' size='15' maxlength='11' value='<?php echo $kou_kin ?>' class='right'>
                    </td>
                    <td align='center' bgcolor='#ffffb3'>
                        特注外注棚卸高<input type='text' name='invent_gai' size='15' maxlength='11' value='<?php echo $gai_kin ?>' class='right'>
                    </td>
                </tr>
            </table>
            <table bgcolor='#d6d3ce' cellpadding='5' border='1'>
                <tr>
                    <td align='center' bgcolor='#ffffb3'>
                        特注検査棚卸高<input type='text' name='invent_ken' size='15' maxlength='11' value='<?php echo $ken_kin ?>' class='right'>
                    </td>
                    <td align='center' bgcolor='#ffffb3'>
                        特注ＣＣ棚卸高<input type='text' name='invent_cc' size='15' maxlength='11' value='<?php echo $cc_kin ?>' class='right'>
                    </td>
                </tr>
            </table>
            <table bgcolor='#d6d3ce' cellpadding='5' border='1'>
                <tr>
                    <td align='center' bgcolor='#ffffb3'>
                        特注組立棚卸高<input type='text' name='invent_kumi' size='15' maxlength='11' value='<?php echo $kumi_kin ?>' class='right'>
                    </td>
                    <td align='center' bgcolor='#ffffb3'>
                        特注棚卸高合計<input type='text' name='invent_ctokut' size='15' maxlength='11' value='<?php echo $ctokut_kin ?>' class='rightg' readonly>
                    </td>
                </tr>
            </table>
            <BR><BR>
            <table bgcolor='#d6d3ce' cellpadding='5' border='1'>
                <tr>
                    <td align='center' bgcolor='#ffffb3'>
                        ツール材料棚卸高<input type='text' name='invent_tzai' size='15' maxlength='11' value='<?php echo $tzai_kin ?>' class='right'>
                    </td>
                    <td align='center' bgcolor='#ffffb3'>
                        ツール部品棚卸高<input type='text' name='invent_tbuhin' size='15' maxlength='11' value='<?php echo $tbuhin_kin ?>' class='right'>
                    </td>
                </tr>
            </table>
            <table bgcolor='#d6d3ce' cellpadding='5' border='1'>
                <tr>
                    <td align='center' bgcolor='#ffffb3'>
                        ツール工作棚卸高<input type='text' name='invent_tkou' size='15' maxlength='11' value='<?php echo $tkou_kin ?>' class='right'>
                    </td>
                    <td align='center' bgcolor='#ffffb3'>
                        ツール外注棚卸高<input type='text' name='invent_tgai' size='15' maxlength='11' value='<?php echo $tgai_kin ?>' class='right'>
                    </td>
                </tr>
            </table>
            <table bgcolor='#d6d3ce' cellpadding='5' border='1'>
                <tr>
                    <td align='center' bgcolor='#ffffb3'>
                        ツール検査棚卸高<input type='text' name='invent_tken' size='15' maxlength='11' value='<?php echo $tken_kin ?>' class='right'>
                    </td>
                    <td align='center' bgcolor='#ffffb3'>
                        ツールＣＣ棚卸高<input type='text' name='invent_tcc' size='15' maxlength='11' value='<?php echo $tcc_kin ?>' class='right'>
                    </td>
                </tr>
            </table>
            <table bgcolor='#d6d3ce' cellpadding='5' border='1'>
                <tr>
                    <td align='center' bgcolor='#ffffb3'>
                        ツール組立棚卸高<input type='text' name='invent_tkumi' size='15' maxlength='11' value='<?php echo $tkumi_kin ?>' class='right'>
                    </td>
                    <td align='center' bgcolor='#ffffb3'>
                        ツール棚卸高合計<input type='text' name='invent_toolt' size='15' maxlength='11' value='<?php echo $toolt_kin ?>' class='rightg' readonly>
                    </td>
                    <td align='center' bgcolor='#ffffb3'>
                        <input type='submit' name='touroku' value='実行' >
                    </td>
                </tr>
            </table>
        </form>
    </center>
</BODY>
</HTML>
