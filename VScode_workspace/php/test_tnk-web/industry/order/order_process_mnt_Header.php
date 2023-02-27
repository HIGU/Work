<?php
//////////////////////////////////////////////////////////////////////////////
// 発注工程メンテナンス(発注手順の保守)   Headerフレーム                    //
// Copyright (C) 2004-2005 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp      //
// Changed history                                                          //
// 2004/11/27 Created  order_process_mnt_Header.php                         //
// 2004/12/01 デザイン統一 border='1' cellspacing='0' cellpadding='3'>      //
// 2005/02/10 JavaScriptで 'sei_no'の適正チェックを追加                     //
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
// $menu->set_site(30, 999);                   // site_index=30(生産メニュー) site_id=999(未定)
////////////// target設定
// $menu->set_target('application');           // フレーム版の戻り先はtarget属性が必須
$menu->set_target('_parent');               // フレーム版の戻り先はtarget属性が必須

//////////// タイトル名(ソースのタイトル名とフォームのタイトル名)
$menu->set_title('発注工程メンテナンス');     // タイトルを入れないとIEの一部のバージョンで表示できない不具合あり
//////////// 表題の設定
$menu->set_caption('製造番号を指定して下さい。');
//////////// 呼出先のaction名とアドレス設定
$menu->set_action('リスト',   INDUST . 'order/order_process_mnt_List.php');

//////////// パラメータの取得
if ($_SESSION['order_sei_no'] != '') {
    $sei_no = $_SESSION['order_sei_no'];
} else {
    $sei_no = '';
}
$uniq = ('id=' . uniqid('target') );

/////////// HTML Header を出力してキャッシュを制御
$menu->out_html_header();
?>
<!DOCTYPE html>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<meta http-equiv="Content-Style-Type" content="text/css">
<title><?= $menu->out_title() ?></title>
<?= $menu->out_css() ?>
<script language='JavaScript'>
<!--
/* 入力文字が数字かどうかチェック(ASCII code check) */
function isDigit(str) {
    var len = str.length;
    var c;
    for (i=0; i<len; i++) {
        c = str.charAt(i);
        if ((c < '0') || (c > '9')) {
            return false;
        }
    }
    return true;
}

function set_focus() {
    document.sei_no_form.sei_no.select();
    document.sei_no_form.sei_no.focus();
}
function chk_sei_no(obj) {
    var sei_no = obj.sei_no.value;
    if (sei_no.length != 7) {
        alert('製造番号の桁数は７桁です。\n\n入力された桁数は [' + sei_no.length + '] 桁です。');
        obj.sei_no.focus();
        obj.sei_no.select();
        return false;
    }
    if (!isDigit(sei_no)) {
        alert('製造番号に数字以外が入力されました。\n\n入力されたのは [' + sei_no + '] です。');
        obj.sei_no.focus();
        obj.sei_no.select();
        return false;
    }
    return true;
}
// -->
</script>
<style type='text/css'>
<!--
.pt14b {
    font-size:      14pt;
    font-weight:    bold;
    font-family:    monospace;
}
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
<body onLoad='set_focus()' style='orverflow-y:hidden;'>
    <center>
<?= $menu->out_title_border() ?>
        <form name='sei_no_form' action='<?= $menu->out_action('リスト'), '?', $uniq ?>' method='get' target='List' onSubmit='return chk_sei_no(this)'>
            <!----------------- ここは 本文を表示する ------------------->
            <table bgcolor='#d6d3ce'  border='1' cellspacing='0' cellpadding='3'>
               <tr><td> <!-- ダミー(デザイン用) -->
            <table class='winbox_field' width=100% align='center' border='1' cellspacing='0' cellpadding='3'>
                <tr>
                        <!--  bgcolor='#ffffc6' 薄い黄色 --> 
                    <td class='winbox' colspan='2' align='center'>
                        <font class='caption_font'><?= $menu->out_caption(), "\n" ?></font>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' align='right'>
                        製造番号の指定
                    </td>
                    <td class='winbox' align='center'>
                        <input type='text' name='sei_no' class='pt14b' size='7' value='<?= $sei_no ?>' maxlength='7'>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' colspan='2' align='center'>
                        <!-- <input type='submit' name='sei_no_view' value='実行' > -->
                        Enter Key で実行します。
                    </td>
                </tr>
            </table>
                </td></tr>
            </table> <!----------------- ダミーEnd ------------------>
        </form>
    </center>
</body>
</html>
<?= $menu->out_alert_java()?>
<?php ob_end_flush(); // 出力バッファをgzip圧縮 END ?>
