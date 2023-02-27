<?php
////////////////////////////////////////////////////////////////////////////////////////////
// 会議帯 不在者をウィンドウ表示   Headerフレーム                                         //
// Copyright (C) 2019-2019 Norihisa.Ohya norihisa_ooya@nitto-kohki.co.jp                  //
// Changed history                                                                        //
// 2019/03/15 Created  meeting_schedule_absence_Header.php                                //
////////////////////////////////////////////////////////////////////////////////////////////
ini_set('error_reporting', E_ALL);          // E_ALL='2047' debug 用
// ini_set('display_errors', '1');             // Error 表示 ON debug 用 リリース後コメント
ob_start('ob_gzhandler');                   // 出力バッファをgzip圧縮
session_start();                            // ini_set()の次に指定すること Script 最上行
require_once ('../function.php');     // define.php と pgsql.php を require_once している
require_once ('../MenuHeader.php');   // TNK 全共通 menu class
access_log();                               // Script Name は自動取得

///// TNK 共用メニュークラスのインスタンスを作成
$menu = new MenuHeader();                   // 認証チェック0=一般以上 戻り先=セッションより タイトル未設定

////////////// サイト設定
// $menu->set_site(30, 999);                   // site_index=30(生産メニュー) site_id=999(未定)
////////////// target設定
// $menu->set_target('application');           // フレーム版の戻り先はtarget属性が必須
$menu->set_target('_parent');               // フレーム版の戻り先はtarget属性が必須

//////////// タイトル名(ソースのタイトル名とフォームのタイトル名)
$menu->set_title('不在者照会');     // タイトルを入れないとIEの一部のバージョンで表示できない不具合あり
//////////// 表題の設定
// $menu->set_caption('照会内容選択');

$date = date('Ymd');                    // 初期値(当日)あまり意味は無い

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
        <table class='item' width='100%' bgcolor='#d6d3ce'  border='1' cellspacing='0' cellpadding='1'>
           <tr><td> <!-- ダミー(デザイン用) -->
        <table class='winbox_field' width=100% align='center'  border='1' cellspacing='0' cellpadding='3'>
            <th class='winbox' nowrap width=' 10%'>No</th>
            <th class='winbox' nowrap width=' 30%' style='font-size:9.5pt;'>名前</th>
            <!--
            <th class='winbox' nowrap width=' 6%' style='font-size:9.5pt;'>最低<br>必要日</th>
            -->
            <th class='winbox' nowrap width=' 45%' style='font-size:9.5pt;'>所属</th>
            <th class='winbox' nowrap width=' 15%' style='font-size:9.5pt;'>不在<br>理由</th>
            <!--
            <th class='winbox' nowrap width=' 8%'>部品番号</th>
            <th class='winbox' nowrap width='13%'>部品名</th>
            <th class='winbox' nowrap width=' 8%'>材&nbsp;&nbsp;質</th>
            <th class='winbox' nowrap width=' 8%'>親機種</th>
            <th class='winbox' nowrap width=' 6%'>注文数</th>
            <th class='winbox' nowrap width=' 3%' style='font-size:10.5pt;'>工<br>程</th>
            <th class='winbox' nowrap width='13%'>発注先名</th>
            <th class='winbox' nowrap width='25%'>コメント</th>
            -->
        </table> <!----- ダミー End ----->
            </td></tr>
        </table>
    </center>
</body>
</html>
<?php echo $menu->out_alert_java()?>
<?php ob_end_flush(); // 出力バッファをgzip圧縮 END ?>
