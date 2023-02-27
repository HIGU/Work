<?php
//////////////////////////////////////////////////////////////////////////////
// 設備稼動管理システムの運転状況一覧マップ表示(レイアウト)Headerフレーム   //
// Copyright (C) 2004-2021 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp      //
// Changed history                                                          //
// 2004/09/23 Created  equip_work_mapHeader.php                             //
//            再表示の method を postからgetへ変更 JavaScriptのreload対応   //
//            一覧表示に切替えるボタンを追加                                //
// 2005/02/16 リクエストをセッションに保存 $_SESSION['factory'] = $factory  //
// 2005/06/24 F2/F12キーで戻るための対応で JavaScriptの set_focus()を追加   //
// 2005/07/11 工場区分をこのメニューからはセッションに登録しないように変更  //
// 2005/07/25 上記を元へ戻し                                                //
// 2005/08/20 $menu->_parent → $menu->out_parent() へ変更                  //
// 2018/05/18 ７工場を追加。コード４の登録を強制的に7に変更            大谷 //
// 2021/06/22 ７工場を真鍮とSUSに分離                                  大谷 //
//////////////////////////////////////////////////////////////////////////////
ini_set('error_reporting', E_ALL);          // E_ALL='2047' debug 用
// ini_set('display_errors', '1');             // Error 表示 ON debug 用 リリース後コメント
// ini_set('implicit_flush', 'off');           // echo print で flush させない(遅くなるため) CLI CGI版
// ini_set('max_execution_time', 1200);        // 最大実行時間=20分 CLI CGI版
ob_start('ob_gzhandler');                   // 出力バッファをgzip圧縮
session_start();                            // ini_set()の次に指定すること Script 最上行
require_once ('../../function.php');        // TNK 全共通 function
require_once ('../../MenuHeader.php');      // TNK 全共通 menu class
access_log();                               // Script Name は自動取得

///// TNK 共用メニュークラスのインスタンスを作成
$menu = new MenuHeader();                   // 認証チェック0=一般以上 戻り先=セッションより タイトル未設定

////////////// サイト設定
$menu->set_site(40, 12);                    // site_index=40(設備メニュー) site_id=12(マップ一覧)
////////////// target設定
// $menu->set_target('application');           // フレーム版の戻り先はtarget属性が必須
$menu->set_target('_parent');               // フレーム版の戻り先はtarget属性が必須

//////////// タイトル名(ソースのタイトル名とフォームのタイトル名)
$menu->set_title('稼動状況 レイアウト 表示');
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
<title><?= $menu->out_title() ?></title>
<?= $menu->out_css() ?>
<?= $menu->out_site_java() ?>
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
    top:    90px;
    left:    5px;
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
    var w = 430;
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
<?= $menu->out_title_border() ?>
        
        <!----------------- 見出しを表示 ------------------------>
        <table bgcolor='#d6d3ce'  border='1' cellspacing='0' cellpadding='1'>
            <tr><td> <!-- ダミー(デザイン用) -->
        <table class='winbox_field' width='100%' border='0' cellspacing='0' cellpadding='1'>
            <tr class='sub_font'>
                <td class='winbox'>
                    <input style='font-size:10pt; font-weight:bold; color:blue;' type='submit' name='map_help' value='説明' onClick='win_open("map_help.html")'>
                </td>
                <td class='winbox' align='center' width='100'>
                    <form name='mac_form' method='post' action='<?= $menu->out_parent() ?>' target='_parent'>
                    <select name='factory' class='ret_font' onChange='document.mac_form.submit()'>
                        <!--
                        <option value='' <?php if($factory=='') echo 'selected'; ?>>全工場</option>
                        <option value='1' <?php if($factory==1) echo 'selected'; ?>>１工場</option>
                        <option value='2' <?php if($factory==2) echo 'selected'; ?>>２工場</option>
                        <option value='4' <?php if($factory==4) echo 'selected'; ?>>４工場</option>
                        <option value='5' <?php if($factory==5) echo 'selected'; ?>>５工場</option>
                        <option value='6' <?php if($factory==6) echo 'selected'; ?>>６工場</option>
                        -->
                        <option value='7' <?php if($factory==7) echo 'selected'; ?>>７工場(真鍮)</option>
                        <option value='8' <?php if($factory==8) echo 'selected'; ?>>７工場(SUS)</option>
                    </select>
                    </form>
                </td>
                <td class='winbox'>
                    <form name='reload_form' action='equip_work_mapList.php' method='get' target='List'>
                        <input style='font-size:10pt; color:blue;' type='submit' name='reload' value='再表示'>
                        <input type='hidden' name='factory' value='<?=$factory?>'>
                    </form>
                </td>
                <td class='winbox'>
                    <form action='equip_work_all.php' method='get' target='_parent'>
                        <input style='font-size:10pt; color:blue;' type='submit' name='all_view' value='一覧表示'>
                        <input type='hidden' name='factory' value='<?=$factory?>'>
                    </form>
                </td>
            </tr>
        </table>
            </td></tr>
        </table> <!-- ダミーEnd -->
    </center>
</body>
</html>
<?php ob_end_flush(); // 出力バッファをgzip圧縮 END ?>
