<?php
//////////////////////////////////////////////////////////////////////////////
// 単価経歴の照会 条件選択フォーム  更新元 UKWLIB/W#HICOST                  //
// Copyright(C) 2004-2005 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp       //
// Changed history                                                          //
// 2004/05/17 Created   parts_cost_form_form.php                            //
// 2004/12/03 $lot_cost がセッションに登録されていても表示しないように変更  //
// 2005/01/11 MenuHeader class を使用して共通メニュー化及び認証方式へ変更   //
//////////////////////////////////////////////////////////////////////////////
ini_set('error_reporting', E_ALL);          // E_ALL='2047' debug 用
// ini_set('display_errors', '1');             // Error 表示 ON debug 用 リリース後コメント
// ini_set('implicit_flush', 'off');           // echo print で flush させない(遅くなるため) CLI CGI版
// ini_set('max_execution_time', 1200);        // 最大実行時間=20分 CLI CGI版
ob_start('ob_gzhandler');                   // 出力バッファをgzip圧縮
session_start();                            // ini_set()の次に指定すること Script 最上行

require_once ('../../function.php');        // define.php と pgsql.php を require_once している
require_once ('../../tnk_func.php');        // TNK に依存する部分の関数を require_once している
require_once ('../../MenuHeader.php');      // TNK 全共通 menu class
access_log();                               // Script Name は自動取得

///// TNK 共用メニュークラスのインスタンスを作成
$menu = new MenuHeader(0);                  // 認証チェック0=一般以上 戻り先=TOP_MENU タイトル未設定

////////////// サイト設定
$menu->set_site(30, 14);                    // site_index=30(生産メニュー) site_id=14(単価経歴照会)
////////////// リターンアドレス設定
// $menu->set_RetUrl(INDUST_MENU);             // 通常は指定する必要はない
//////////// タイトル名(ソースのタイトル名とフォームのタイトル名)
$menu->set_title('単 価 経 歴 の 照 会 (条件選択)');
//////////// 表題の設定
$menu->set_caption('下記の必要な条件を入力又は選択して下さい。');
//////////// 呼出先のaction名とアドレス設定
$menu->set_action('単価経歴照会',   INDUST . 'parts/parts_cost_view.php');

//////////// JavaScript Stylesheet File を必ず読み込ませる
$uniq = uniqid('target');

/////////////// 受け渡し変数の初期化
if ( isset($_SESSION['cost_parts_no']) ) {
    $parts_no = $_SESSION['cost_parts_no'];
} else {
    $parts_no = '';              // 初期化
}
if ( isset($_SESSION['cost_lot_cost']) ) {
    $lot_cost = $_SESSION['cost_lot_cost'];
    $lot_cost = '';             // 初期化
} else {
    $lot_cost = '';             // 初期化
}
if ( isset($_SESSION['cost_page']) ) {
    $cost_page = $_SESSION['cost_page'];
} else {
    $cost_page = '25';          // 初期化
}

/////////// HTML Header を出力してキャッシュを制御
$menu->out_html_header();
?>
<!DOCTYPE html>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<meta http-equiv="Content-Style-Type" content="text/css">
<title><?= $menu->out_title() ?></title>
<?= $menu->out_site_java() ?>
<?= $menu->out_css() ?>

<!--    ファイル指定の場合 -->
<script language='JavaScript' src='./parts_cost_form.js?<?= $uniq ?>'></script>

<script language="JavaScript">
<!--
// -->
</script>

<!-- スタイルシートのファイル指定をコメント HTMLタグ コメントは入れ子に出来ない事に注意
<link rel='stylesheet' href='<?= MENU_FORM . '?' . $uniq ?>' type='text/css' media='screen'>
-->

<style type="text/css">
<!--
.pt8 {
    font-size:      8pt;
    font-weight:    normal;
    font-family:    monospace;
}
.pt9 {
    font-size:      9pt;
    font-weight:    normal;
    font-family:    monospace;
}
.pt10 {
    font-size:      10pt;
    font-weight:    normal;
    font-family:    monospace;
}
.pt10b {
    font-size:      10pt;
    font-weight:    bold;
    font-family:    monospace;
}
.pt11b {
    font-size:      11pt;
    font-weight:    bold;
    font-family:    monospace;
}
.pt12b {
    font-size:      12pt;
    font-weight:    bold;
    font-family:    monospace;
}
th {
    background-color:   blue;
    color:              yellow;
    font-size:          10pt;
    font-weight:        bold;
    font-family:        monospace;
}
td {
    font-size: 10pt;
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
    /* background-color:#d6d3ce; */
}
a:hover {
    background-color:   blue;
    color:              white;
}
a {
    color:   blue;
}
-->
</style>
</head>
<body onLoad='document.parts_cost_form.parts_no.focus(); document.parts_cost_form.parts_no.select()'>
    <center>
<?= $menu->out_title_border() ?>
        <form name='parts_cost_form' action='parts_cost_view.php' method='post' onSubmit='return chk_parts_cost_form(this)'>
            <!----------------- ここは 本文を表示する ------------------->
            <table bgcolor='#d6d3ce' border='1' cellspacing='0' cellpadding='3'>
                <tr><td> <!----------- ダミー(デザイン用) ------------>
            <table class='winbox_field' width='100%' bgcolor='#d6d3ce' border='1' cellspacing='0' cellpadding='3'>
                <tr>
                        <!--  bgcolor='#ffffc6' 薄い黄色 --> 
                    <td class='winbox' colspan='2' align='center'>
                        <div class='caption_font'><?= $menu->out_caption(), "\n" ?></div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' align='right'>
                        部品番号の指定
                    </td>
                    <td class='winbox' align='center'>
                        <input type='text' name='parts_no' class='pt12b' size='9' value='<?= $parts_no ?>' maxlength='9'>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' align='right'>
                        単価の指定(0又は指定しなければ無視されます)
                    </td>
                    <td class='winbox' align='center'>
                        <input type='text' name='lot_cost' class='pt12b' size='10' value='<?= $lot_cost ?>' maxlength='10'>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' align='right'>
                        １ページの表示行数を指定して下さい
                    </td>
                    <td class='winbox' align='center'>
                        <input type='text' name='cost_page' size='4' value='<?= $cost_page ?>' maxlength='3'>
                        初期値：25
                    </td>
                </tr>
                <tr>
                    <td class='winbox' colspan='2' align='center'>
                        <input type='submit' name='cost_view' value='実行' >
                    </td>
                </tr>
            </table>
                </td></tr>
            </table> <!----------------- ダミーEnd ------------------>
        </form>
    </center>
</body>
<?= $menu->out_alert_java() ?>
</html>
<?php
ob_end_flush();                 // 出力バッファをgzip圧縮 END
?>
