<?php
//////////////////////////////////////////////////////////////////////////////
// 単価経歴より販売価格(仕切単価)設定  フォーム                             //
// Copyright(C) 2004-2010 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp       //
// Changed history                                                          //
// 2004/11/19 Created  parts_sales_price_form.php                           //
// 2004/11/24 部品番号マスター未登録はalertに変更。内部条件のコメント追加   //
// 2004/12/02 デザイン統一  border='1' cellspacing='0' cellpadding='3'>     //
// 2004/12/25 style='overflow:hidden;' (-xy両方)を追加                      //
// 2010/08/25 日付の初期値を本日に変更。レートの初期値を1.1に変更     大谷  //
//////////////////////////////////////////////////////////////////////////////
ini_set('error_reporting', E_ALL);          // E_ALL='2047' debug 用
// ini_set('display_errors', '1');             // Error 表示 ON debug 用 リリース後コメント
// ini_set('implicit_flush', 'off');           // echo print で flush させない(遅くなるため) CLI CGI版
// ini_set('max_execution_time', 1200);        // 最大実行時間=20分 CLI CGI版
ob_start('ob_gzhandler');                   // 出力バッファをgzip圧縮
session_start();                            // ini_set()の次に指定すること Script 最上行

require_once ('../../function.php');
require_once ('../../tnk_func.php');
require_once ('../../MenuHeader.php');      // TNK 全共通 menu class
access_log();                               // Script Name は自動取得

///// TNK 共用メニュークラスのインスタンスを作成
$menu = new MenuHeader(0);                  // 認証チェック0=一般以上 戻り先=TOP_MENU タイトル未設定

////////////// サイト設定
$menu->set_site(30, 999);                   // site_index=30(生産メニュー) site_id=999(サイトを開く)
////////////// リターンアドレス設定
// $menu->set_RetUrl(SYS_MENU);                // 通常は指定する必要はない
//////////// タイトル名(ソースのタイトル名とフォームのタイトル名)
$menu->set_title('単価経歴より販売価格の照会');
//////////// 表題の設定
$menu->set_caption('　　検索条件入力フォーム　');
//////////// 呼出先のaction名とアドレス設定
$menu->set_action('view',   INDUST . 'parts/parts_sales_price_view.php');

//////////// JavaScript Stylesheet File を必ず読み込ませる
$uniq = uniqid('target');

/////////////// 受け渡し変数の初期化
if ( isset($_SESSION['cost_parts']) ) {
    $parts = $_SESSION['cost_parts'];
} else {
    $parts = '';                // 初期化
}
if ( isset($_SESSION['cost_regdate']) ) {
    $regdate = $_SESSION['cost_regdate'];
} else {
    //$d_start = date_offset(1);
    $regdate = date_offset(0);      // 初期化
    //$regdate = '20020331';      // 初期化
    $_SESSION['cost_regdate'] = $regdate;
}
if ( isset($_SESSION['cost_sales_rate']) ) {
    $sales_rate = $_SESSION['cost_sales_rate'];
} else {
    $sales_rate = '1.1';       // 初期化
    $_SESSION['cost_sales_rate'] = $sales_rate;
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
<?php if ($_SESSION['s_sysmsg'] == '') echo $menu->out_site_java(); ?>
<?= $menu->out_css() ?>

<!--    ファイル指定の場合 -->
<script language='JavaScript' src='./parts_sales_price_form.js?<?= $uniq ?>'></script>

<script language="JavaScript">
<!--
// -->
</script>

<!-- スタイルシートのファイル指定をコメント HTMLタグ コメントは入れ子に出来ない事に注意
<link rel='stylesheet' href='<?= MENU_FORM . '?' . $uniq ?>' type='text/css' media='screen'>
-->

<style type="text/css">
<!--
.pt12b {
    font-size:      12pt;
    font-weight:    bold;
    font-family:    monospace;
}
.pt14b {
    font-size:      14pt;
    font-weight:    bold;
    font-family:    monospace;
}
.caption_font {
    font-size:          12pt;
    font-weight:        bold;
    font-family:        monospace;
    background-color:   blue;
    color:              yellow;
}
.margin0 {
    margin:0%;
}
td {
    font-size:      12pt;
    font-weight:    bold;
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
</head>
</style>
<body style='overflow:hidden;' onLoad='document.parts_sales_price_form.parts.focus(); document.parts_sales_price_form.parts.select()'>
    <center>
<?= $menu->out_title_border() ?>
        <form name='parts_sales_price_form' action='<?= $menu->out_action('view') ?>' method='get' onSubmit='return chk_parts_sales_price_form(this)'>
            <!----------------- ここは 本文を表示する ------------------->
            <table bgcolor='#d6d3ce' border='1' cellspacing='0' cellpadding='3'>
                <tr><td> <!----------- ダミー(デザイン用) ------------>
            <table class='winbox_field' width='100%' border='1' cellspacing='0' cellpadding='3'>
                <tr>
                        <!--  bgcolor='#ffffc6' 薄い黄色 --> 
                    <td class='winbox' colspan='2' align='center'>
                        <font class='caption_font'><?= $menu->out_caption(), "\n" ?></font>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' align='right'>
                        部品番号の指定
                    </td>
                    <td class='winbox' align='center'>
                        <input type='text' name='parts' class='pt14b' size='9' value='<?= $parts ?>' maxlength='9'>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' align='right'>
                        単価登録基準日の指定(YYYYMMDD)
                    </td>
                    <td class='winbox' align='center'>
                        <input type='text' name='regdate' class='pt12b' size='8' value='<?= $regdate ?>' maxlength='8'>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' align='right'>
                        販売価格(仕切単価)レート(初期値：1.1)
                    </td>
                    <td class='winbox' align='center'>
                        <input type='text' name='sales_rate' class='pt12b' size='4' value='<?= $sales_rate ?>' maxlength='4'>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' colspan='2' align='center'>
                        <input type='submit' name='sales_price_view' value='実行' >
                        <!-- Enter Key で実行します。 -->
                    </td>
                </tr>
            </table>
                </td></tr>
            </table> <!----------------- ダミーEnd ------------------>
        </form>
        <br>
        <table style='border: 2px solid #AABBCC;'>
            <tr><td align='center' class='pt11b' id='note'>単価登録基準日 以前に継続・暫定の登録が無い場合は、最新単価とする。</td></tr>
        </table>
    </center>
</body>
<?= $menu->out_alert_java() ?>
</html>
<?php
ob_end_flush();                 // 出力バッファをgzip圧縮 END
?>
