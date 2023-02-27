<?php
//////////////////////////////////////////////////////////////////////////////
// 設備稼動管理システムの 現在運転中一覧表 表示  Headerフレーム             //
// Copyright (C) 2004-2005 2007 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp //
// Changed history                                                          //
// 2004/09/09 Created  equip_work_allHeader.php                             //
// 2004/09/23 再表示の method を postからgetへ変更 JavaScriptのreload対応   //
//            レイアウト(マップ)表示に切替えるボタンを追加                  //
// 2004/11/29 機械名のwidth=70→72へ変更(20PMｺｸｲのため)                     //
// 2005/02/16 リクエストをセッションに保存 $_SESSION['factory'] = $factory  //
// 2005/06/24 F2/F12キーで戻るための対応で JavaScriptの set_focus()を追加   //
// 2005/07/11 工場区分をこのメニューからはセッションに登録しないように変更  //
// 2005/07/25 上記を元へ戻し                                                //
// 2005/08/05 allList部と合わせるため表の幅をwidth='98.3%'で調整            //
// 2005/08/20 $menu->_parent → $menu->out_parent() へ変更                  //
// 2007/05/24 フレーム版からインラインフレーム版へ変更。機械№→指示数へ変更//
//              その他デザイン変更 旧版は backup/ にあり                    //
//////////////////////////////////////////////////////////////////////////////
ini_set('error_reporting', E_ALL);          // E_ALL='2047' debug 用
// ini_set('display_errors', '1');             // Error 表示 ON debug 用 リリース後コメント
ob_start('ob_gzhandler');                   // 出力バッファをgzip圧縮
session_start();                            // ini_set()の次に指定すること Script 最上行
require_once ('../../function.php');        // TNK 全共通 function
require_once ('../../MenuHeader.php');      // TNK 全共通 menu class
access_log();                               // Script Name は自動取得

///// TNK 共用メニュークラスのインスタンスを作成
$menu = new MenuHeader();                   // 認証チェック0=一般以上 戻り先=セッションより タイトル未設定

////////////// サイト設定
$menu->set_site(40, 9);                     // site_index=40(設備メニュー) site_id=9(運転中一覧)
////////////// target設定
// $menu->set_target('application');           // フレーム版の戻り先はtarget属性が必須
$menu->set_target('_parent');               // フレーム版の戻り先はtarget属性が必須

//////////// タイトル名(ソースのタイトル名とフォームのタイトル名)
$menu->set_title('現在運転中 一覧表');
//////////// 表題の設定
$menu->set_caption('工場選択');

if (isset($_REQUEST['factory'])) {
    $factory = $_REQUEST['factory'];
    $_SESSION['factory'] = $factory;
} else {
    ///// リクエストが無ければセッションから工場区分を取得する。(通常はこのパターン)
    $factory = @$_SESSION['factory'];
}

/////////// HTML Header を出力してキャッシュを制御
$menu->out_html_header();
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<meta http-equiv="Content-Style-Type" content="text/css">
<meta http-equiv="Content-Script-Type" content="text/javascript">
<title><?= $menu->out_title() ?></title>
<?= $menu->out_css() ?>
<?= $menu->out_site_java() ?>
<style type='text/css'>
<!--
.pt8 {
    font-size:   0.6em;
    font-weight: normal;
    font-family: monospace;
}
.pt9 {
    font-size:   0.7em;
    font-weight: normal;
    font-family: monospace;
}
.pt10 {
    font-size:   0.8em;
    font-weight: normal;
    font-family: monospace;
}
.pt10b {
    font-size:   0.8em;
    font-weight: bold;
    font-family: monospace;
}
.pt11b {
    font-size:   0.9em;
    font-weight: bold;
    font-family: monospace;
}
.pt12b {
    font-size:   1.0em;
    font-weight: bold;
    font-family: monospace;
}
.pt13b {
    font-size:   1.1em;
    font-weight: bold;
    /* font-family: monospace; */
}
.pt14b {
    font-size:   1.2em;
    font-weight: bold;
    /* font-family: monospace; */
}
select {
    background-color:   teal;
    color:              white;
}
.sub_font {
    font-size:      0.95em;
    font-weight:    bold;
    font-family:    monospace;
}
.pick_font {
    font-size:      0.75em;
    font-weight:    bold;
    font-family: monospace;
}
th {
    font-size:      1.0em;
    font-weight:    bold;
    font-family:    monospace;
    color:              blue;
    background-color:   yellow;
}
.item {
    position: absolute;
    top:    90px;
    left:    0px;
}
-->
</style>
<form name='MainForm' method='post'>
    <input type='hidden' name='select' value=''>
</form>
<script language="JavaScript">
<!--
/* 初期入力フォームのエレメントにフォーカスさせる */
function set_focus() {
    // document.mac_form.factory.focus();      // カーソルキーで工場を移動きるようにする
}
    function parts_upper(obj) {
    obj.parts_no.value = obj.parts_no.value.toUpperCase();
    return true;
}
function win_open(url) {
    var w = 800;
    var h = 600;
    var left = (screen.availWidth  - w) / 2;
    var top  = (screen.availHeight - h) / 2;
    window.open(url, 'view_win', 'width='+w+',height='+h+',scrollbars=yes,status=no,toolbar=no,location=no,menubar=no,top='+top+',left='+left);
}
// -->
</script>
</head>
<body onLoad='set_focus()'>
    <center>
        <table width='100%' bgcolor='#d6d3ce'  border='1' cellspacing='0' cellpadding='1'>
           <tr><td> <!-- ダミー(デザイン用) -->
        <table class='winbox_field' width='100%' align='center'  border='1' cellspacing='0' cellpadding='1'>
            <th class='winbox' nowrap width=' 3%'>No</th>
            <th class='winbox' nowrap width=' 9%'>機械名</th>
            <th class='winbox' nowrap width=' 9%'>年月日</th>
            <th class='winbox' nowrap width=' 8%'>時分秒</th>
            <th class='winbox' nowrap width=' 9%'>状態</th>
            <th class='winbox' nowrap width=' 8%'>加工数</th>
            <th class='winbox' nowrap width=' 8%'>指示数</th>
            <th class='winbox' nowrap width=' 7%'>指示No</th>
            <th class='winbox' nowrap width='11%'>部品番号</th>
            <th class='winbox' nowrap width='13%'>部品名</th>
            <th class='winbox' nowrap width='15%'>開始日時</th>
        </table> <!----- ダミー End ----->
            </td></tr>
        </table>
    </center>
</body>
</html>
<?php ob_end_flush(); // 出力バッファをgzip圧縮 END ?>
