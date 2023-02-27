<?php
//////////////////////////////////////////////////////////////////////////////
// 機械稼動管理システムの 運転日報対応 グラフ 表示  Graph本体フレーム       //
// Copyright (C) 2004-2005 2007 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp //
// Changed history                                                          //
// 2004/08/24 Created  equip_report_graphList.php                           //
// 2004/08/30 表示ページ番号を追加 $graph_page=$_SESSION['equip_graph_page']//
// 2005/06/24 グラフの高さを変更default値=350→370へset_graphWH()Y軸の目盛り//
// 2005/09/30 グラフ名をsession()ID→equipReport_' . $_SESSION['User_ID']へ //
// 2005/09/30 日報用を雛型にした月次グラフ equip_MonthReport_graphList.php  //
// 2007/09/26 E_ALL → E_ALL | E_STRICT  へ変更                             //
//////////////////////////////////////////////////////////////////////////////
// ini_set('error_reporting', E_ALL | E_STRICT);
// ini_set('error_reporting', E_ALL);          // E_ALL='2047' debug 用
// ini_set('display_errors', '1');             // Error 表示 ON debug 用 リリース後コメント
ob_start('ob_gzhandler');                   // 出力バッファをgzip圧縮
session_start();                            // ini_set()の次に指定すること Script 最上行
require_once ('../equip_function.php');     // 設備メニュー 共通 function (function.phpを含む)
require_once ('../../MenuHeader.php');      // TNK 全共通 menu class
require_once ('../EquipGraphClass_MonthReport.php');    // 設備稼働管理 Graph class
access_log();                               // Script Name は自動取得

///// TNK 共用メニュークラスのインスタンスを作成
$menu = new MenuHeader();                   // 認証チェック0=一般以上 戻り先=セッションより タイトル未設定

////////////// サイト設定
$menu->set_site(40, 999);                   // site_index=40(設備メニュー2) site_id=999(サイトを開く)
////////////// target設定
$menu->set_target('_parent');               // フレーム版の戻り先はtarget属性が必須

$mac_no   = @$_SESSION['work_mac_no'];
$str_date = @$_SESSION['work_sdate'];
$end_date = @$_SESSION['work_edate'];
if (isset($_POST['select'])) {
    $select = $_POST['select'];
} else {
    $select = 'NG';
}
if ($mac_no == '') {
    $select = 'NG';
}
if (isset($_REQUEST['equip_xtime'])) {
    $_SESSION['equip_xtime'] = $_REQUEST['equip_xtime'];
}

if ($select == 'GO') {
    if (isset($_SESSION['equip_xtime'])) {
        $equip_xtime = $_SESSION['equip_xtime'];
        unset($_SESSION['equip_xtime']);
    } else {
        $equip_xtime = 744;     // (24時間 X 31日)
    }
    /////////// 設備管理グラフのインスタンス作成 解像度180(高解像度は360)
    $equip_graph = new EquipGraphReport($mac_no, $str_date, $end_date);
    $equip_graph->set_xtime($equip_xtime);
    // グラフの範囲内の書式付 DATE TIME の取得  配列(strDate, strTime, endDate, endTime)
    $graphDateTime = $equip_graph->out_graph_timestamp();
    $equip_graph->set_graphWH(670, 370);    // default=670, 350
    $graph_name = ('graph/equipMonthReport_' . $_SESSION['User_ID'] . '.png');
    $equip_graph->out_graph($graph_name, 'yes');
    $graph_page = $_SESSION['equip_graph_page'];    // 表示ページ数の取得
}

/////////// HTML Header を出力してキャッシュを制御
$menu->out_html_header();
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<meta http-equiv="Content-Style-Type" content="text/css">
<title><?= $menu->out_title() ?></title>
<?php if ($_SESSION['s_sysmsg'] != '') echo $menu->out_site_java() ?>
<?= $menu->out_css() ?>
<style type='text/css'>
<!--
th {
    font-size:      11.5pt;
    font-weight:    bold;
    font-family:    monospace;
}
.item {
    position: absolute;
    /* top: 100px; */
    left: 90px;
}
.msg {
    position: absolute;
    top:  100px;
    left: 350px;
}
.winbox {
    border-style: solid;
    border-width: 1px;
    border-top-color:       #FFFFFF;
    border-left-color:      #FFFFFF;
    border-right-color:     #999999;
    border-bottom-color:    #999999;
    background-color:#d6d3ce;
}
.winbox_field {
    border-style: solid;
    border-width: 1px;
    border-top-color:       #999999;
    border-left-color:      #999999;
    border-right-color:     #FFFFFF;
    border-bottom-color:    #FFFFFF;
    background-color:#d6d3ce;
}
-->
</style>
<script language='JavaScript'>
function init() {
<?php if ($select == 'OK') { ?>
    document.MainForm.submit();
<?php } ?>
}
</script>
<?php if ($select == 'OK') { ?>
<form name='MainForm' action='<?= $menu->out_self() ?>' method='post'>
    <input type='hidden' name='select' value='GO'>
</form>
<?php } ?>
</head>
<body onLoad='init()'>
    <center>
        <?php if ($select == 'NG') { ?>
        <table border='0' class='msg'>
            <tr>
                <td>
                    <b style='color: teal;'>機械を選択して下さい！</b>
                </td>
            </tr>
        </table>
        <?php } elseif ($select == 'OK') { ?>
        <table border='0' class='msg'>
            <tr>
                <td>
                    <b style='color: blue;'>処理中です。お待ち下さい。</b>
                </td>
            </tr>
        </table>
        <?php } else { ?>
        <!-------------- グラフ表示のページコントロール作成 -------------->
        <table bgcolor='#d6d3ce'  border='1' cellspacing='0' cellpadding='1'>
           <tr><td> <!-- ダミー(デザイン用) -->
        <table class='winbox_field' width='100%' border='0' cellspacing='0' cellpadding='2'>
            <tr>
                <td class='winbox' style='font-size:11pt;'><?=$graphDateTime['strDate'], ' ', $graphDateTime['strTime']?></td>
                <td class='winbox' style='font-size:11pt;'>～</td>
                <td class='winbox' style='font-size:11pt;'><?=$graphDateTime['endDate'], ' ', $graphDateTime['endTime']?></td>
                <td class='winbox' style='font-size:11pt;'>
                    <?php if ($equip_xtime<=24) echo $equip_xtime, '時間'; else echo ($equip_xtime/24), '日間';?>
                    の範囲を表示
                </td>
                <td class='winbox' style='font-size:11pt;'>表示ページ番号：<?=$graph_page?></td>
            <!--
                <td>
                <form name='page_ctl' method='post' action='<?=$menu->out_self()?>' target='_self'>
                    <input class='pt11b' type='submit' name='backward' value='前ページ' disabled>
                    <select name='equip_xtime' class='ret_font'>
                        <?=$equip_graph->out_select_xtime($equip_xtime)?>
                    </select>
                    <input class='pt11b' type='submit' name='forward' value='次ページ' disabled>
                    <input type='hidden' name='select' value='OK'>
                </form>
                </td>
            -->
            </tr>
        </table> <!----- ダミー End ----->
            </td></tr>
        </table>
        <table width='100%' border='0'>
            <tr><td align='center'>
            <?= "<img src='", $graph_name, "?", uniqid(rand(),1), "' alt='加工数 状態 グラフ' border='0'>\n"; ?>
            </td></tr>
        </table>
        <?=$equip_graph->out_state_summary()?>
        <?php } ?>
    </center>
</body>
</html>
<?php ob_end_flush(); // 出力バッファをgzip圧縮 END ?>
