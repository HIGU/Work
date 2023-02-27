<?php
//////////////////////////////////////////////////////////////////////////////
// 納入予定の照会(検査の仕事量把握) 明細をウィンドウ表示   Headerフレーム   //
// Copyright (C) 2004 2007 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp      //
// Changed history                                                          //
// 2004/09/30 Created  order_details_Header.php                             //
// 2007/05/11 ディレクトリを order/ → order/order_details/ へ変更          //
//////////////////////////////////////////////////////////////////////////////
ini_set('error_reporting', E_ALL);          // E_ALL='2047' debug 用
// ini_set('display_errors', '1');             // Error 表示 ON debug 用 リリース後コメント
ob_start('ob_gzhandler');                   // 出力バッファをgzip圧縮
session_start();                            // ini_set()の次に指定すること Script 最上行
require_once ('../../../function.php');     // define.php と pgsql.php を require_once している
require_once ('../../../MenuHeader.php');   // TNK 全共通 menu class
access_log();                               // Script Name は自動取得

///// TNK 共用メニュークラスのインスタンスを作成
$menu = new MenuHeader();                   // 認証チェック0=一般以上 戻り先=セッションより タイトル未設定

////////////// サイト設定
// $menu->set_site(30, 999);                   // site_index=30(生産メニュー) site_id=999(未定)
////////////// target設定
// $menu->set_target('application');           // フレーム版の戻り先はtarget属性が必須
$menu->set_target('_parent');               // フレーム版の戻り先はtarget属性が必須

//////////// タイトル名(ソースのタイトル名とフォームのタイトル名)
$menu->set_title('納入予定明細の照会');     // タイトルを入れないとIEの一部のバージョンで表示できない不具合あり
//////////// 表題の設定
// $menu->set_caption('照会内容選択');

///////// パラメーターチェック(基本的にセッションから取得)
if (isset($_SESSION['div'])) {
    $div = $_SESSION['div'];                // Default(セッションから)
} else {
    $div = 'C';                             // 初期値(カプラ)あまり意味は無い
}
//////// 指定日のパラメータ取得 & 設定
if (isset($_REQUEST['date'])) {
    if ($_REQUEST['date'] == 'OLD') {
        $date = $_REQUEST['date'];
    } else {
        $date = $_REQUEST['date'];              // 明細を表示する指定日付
        $date = ('20' . substr($date, 0, 2) . substr($date, 3, 2) . substr($date, 6, 2));
            // YYYYMMDDの形式に変換
    }
} else {
    $date = date('Ymd');                    // 初期値(当日)あまり意味は無い
}

/////////// HTML Header を出力してキャッシュを制御
$menu->out_html_header();
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=EUC-JP">
<meta http-equiv="Content-Style-Type" content="text/css">
<title><?php echo $menu->out_title() ?></title>
<?php echo $menu->out_css() ?>
<script language='JavaScript'>
<!--
function win_close() {
    alert('ここは、見出しの項目です。');
    window.close();
}
// window.document.onclick = win_close;
// -->
</script>
<style type='text/css'>
<!--
select {
    background-color:   teal;
    color:              white;
}
.sub_font {
    font-size:      11.5pt;
    font-weight:    bold;
    font-family:    monospace;
}
.pick_font {
    font-size:      9.5pt;
    font-weight:    bold;
    font-family: monospace;
}
th {
    font-size:      11.5pt;
    font-weight:    bold;
    font-family:    monospace;
    color:              blue;
    background-color:   yellow;
}
.item {
    position: absolute;
    top:    0px;
    left:   0px;
}
.winbox {
    border-style: solid;
    border-width: 1px;
    border-top-color:       #FFFFFF;
    border-left-color:      #FFFFFF;
    border-right-color:     #999999;
    border-bottom-color:    #999999;
    /* background-color:#d6d3ce; */
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
<form name='MainForm' method='post'>
    <input type='hidden' name='select' value=''>
</form>
</head>
<body onClick='window.parent.close()'>
    <center>
        <!----------------- 見出しを表示 ------------------------>
        <table class='item' bgcolor='#d6d3ce'  border='1' cellspacing='0' cellpadding='2'>
           <tr><td> <!-- ダミー(デザイン用) -->
        <table class='winbox_field' width=100% align='center'  border='1' cellspacing='0' cellpadding='1'>
            <th class='winbox' nowrap width='30'>No</th>
            <?php if ($date == 'OLD') { ?>
            <th class='winbox' nowrap width='45' style='font-size:9.5pt;'>納 期</th>
            <?php } else { ?>
            <th class='winbox' nowrap width='45' style='font-size:9.5pt;'>発行日</th>
            <?php } ?>
            <th class='winbox' nowrap width='60' style='font-size:9.5pt;'>製造番号</th>
            <th class='winbox' nowrap width='80'>部品番号</th>
            <th class='winbox' nowrap width='145'>部品名</th>
            <th class='winbox' nowrap width='85'>材&nbsp;&nbsp;質</th>
            <th class='winbox' nowrap width='90'>親機種</th>
            <th class='winbox' nowrap width='70'>注文数</th>
            <th class='winbox' nowrap width='20' style='font-size:10.5pt;'>工程</th>
            <th class='winbox' nowrap width='130'>発注先名</th>
        </table> <!----- ダミー End ----->
            </td></tr>
        </table>
    </center>
</body>
</html>
<?php echo $menu->out_alert_java()?>
<?php ob_end_flush(); // 出力バッファをgzip圧縮 END ?>
