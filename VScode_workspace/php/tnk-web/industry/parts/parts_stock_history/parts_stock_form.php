<?php
//////////////////////////////////////////////////////////////////////////////
// 生産用 部品在庫経歴 照会 部品指定フォーム                                //
// Copyright(C) 2004-2007 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp       //
// Changed history                                                          //
// 2004/12/20 Created  parts_stock_form.php                                 //
// 2004/12/27 $_SESSION['stock_date_lower'] = $date_low→$date_upp 誤記訂正 //
// 2006/06/02 大文字変換用のイベントハンドラーonKeyUpを追加                 //
// 2007/02/20 parts/からparts/parts_stock_history/parts_stock_form.phpへ変更//
// 2007/03/22 parts_stock_view.php → parts_stock_history_Main.php へ変更   //
// 2007/10/19 E_ALL → E_ALL | E_STRICT へ <meta>javascriptを追加 その他    //
// 2019/06/25 表示件数を1000件に変更。総材料費でないことがある為  大谷      //
//////////////////////////////////////////////////////////////////////////////
// ini_set('error_reporting', E_ALL | E_STRICT);
// ini_set('display_errors', '1');             // Error 表示 ON debug 用 リリース後コメント
// ini_set('zend.ze1_compatibility_mode', '1');    // zend 1.X コンパチ php4の互換モード
// ini_set('implicit_flush', 'off');           // echo print で flush させない(遅くなるため) CLI CGI版
// ini_set('max_execution_time', 1200);        // 最大実行時間=20分 CLI CGI版
ob_start('ob_gzhandler');                   // 出力バッファをgzip圧縮
session_start();                            // ini_set()の次に指定すること Script 最上行

require_once ('../../../function.php');     // define.php と pgsql.php を require_once している
require_once ('../../../tnk_func.php');     // TNK に依存する部分の関数を require_once している
require_once ('../../../MenuHeader.php');   // TNK 全共通 menu class
access_log();                               // Script Name は自動取得

///// TNK 共用メニュークラスのインスタンスを作成
$menu = new MenuHeader(0);                  // 認証チェック0=一般以上 戻り先=TOP_MENU タイトル未設定

////////////// サイト設定
$menu->set_site(30, 40);                    // site_index=30(生産メニュー) site_id=40(部品在庫経歴)999(サイトを開く)
////////////// リターンアドレス設定
// $menu->set_RetUrl(SYS_MENU);                // 通常は指定する必要はない
//////////// タイトル名(ソースのタイトル名とフォームのタイトル名)
$menu->set_title('部品在庫経歴の照会');
//////////// 表題の設定
$menu->set_caption('　　部品番号入力フォーム　');
//////////// 呼出先のaction名とアドレス設定
$menu->set_action('view',   INDUST . 'parts/parts_stock_history/parts_stock_history_Main.php');

//////////// JavaScript Stylesheet File を必ず読み込ませる
$uniq = uniqid('target');

/////////////// 受け渡し変数の初期化
if ( isset($_SESSION['stock_parts']) ) {
    $parts_no = $_SESSION['stock_parts'];
} else {
    $parts_no = '';             // 初期化
}
if ( isset($_SESSION['stock_date_lower']) ) {
    $date_low = $_SESSION['stock_date_lower'];
} else {
    $date_low = '20000401';     // 初期化
    $_SESSION['stock_date_lower'] = $date_low;
}
if ( isset($_SESSION['stock_date_upper']) ) {
    $date_upp = $_SESSION['stock_date_upper'];
} else {
    $date_upp = date('Ymd');    // 初期化
    $_SESSION['stock_date_upper'] = $date_upp;
}
if ( isset($_SESSION['stock_view_rec']) ) {
    $view_rec = $_SESSION['stock_view_rec'];
} else {
    //$view_rec = '500';          // 初期化
    $view_rec = '1000';          // 初期化
    $_SESSION['stock_view_rec'] = $view_rec;
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
<title><?php echo $menu->out_title() ?></title>
<?php if ($_SESSION['s_sysmsg'] == '') echo $menu->out_site_java(); ?>
<?php echo $menu->out_css() ?>

<!--    ファイル指定の場合 -->
<script language='JavaScript' src='./parts_stock_form.js?<?php echo $uniq ?>'></script>

<script type='text/javascript' language='JavaScript'>
<!--
// -->
</script>

<!-- スタイルシートのファイル指定をコメント HTMLタグ コメントは入れ子に出来ない事に注意
<link rel='stylesheet' href='<?php echo MENU_FORM . '?' . $uniq ?>' type='text/css' media='screen'>
-->

<style type='text/css'>
<!--
.pt12b {
    font-size:          12pt;
    font-weight:        bold;
    font-family:        monospace;
}
.pt14b {
    font-size:          14pt;
    font-weight:        bold;
    font-family:        monospace;
}
.caption_font {
    font-size:          12pt;
    font-weight:        bold;
    font-family:        monospace;
    background-color:   blue;
    color:              yellow;
}
.margin0 {
    margin:             0%;
}
td {
    font-size:          12pt;
    font-weight:        bold;
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
<body style='overflow:hidden;' onLoad='document.parts_stock_form.parts_no.focus(); document.parts_stock_form.parts_no.select()'>
    <center>
<?php echo $menu->out_title_border() ?>
        <form name='parts_stock_form' action='<?php echo $menu->out_action('view') ?>' method='get' onSubmit='return chk_parts_stock_form(this)'>
            <!----------------- ここは 本文を表示する ------------------->
            <table bgcolor='#d6d3ce' border='1' cellspacing='0' cellpadding='3'>
                <tr><td> <!----------- ダミー(デザイン用) ------------>
            <table class='winbox_field' width='100%' border='1' cellspacing='0' cellpadding='3'>
                <tr>
                        <!--  bgcolor='#ffffc6' 薄い黄色 --> 
                    <td class='winbox' colspan='2' align='center'>
                        <font class='caption_font'><?php echo $menu->out_caption(), "\n" ?></font>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' align='right'>
                        部品番号の指定
                    </td>
                    <td class='winbox' align='center'>
                        <input type='text' name='parts_no' class='pt14b' size='9' value='<?php echo $parts_no ?>' maxlength='9'
                            onKeyUp='baseJS.keyInUpper(this);'
                        >
                    </td>
                </tr>
                <tr>
                    <td class='winbox' align='right' style='font-size:11pt; font-weight:normal;'>
                        日付範囲指定(下限)(YYYYMMDD)
                    </td>
                    <td class='winbox' align='center'>
                        <input type='text' name='date_low' class='pt12b' size='8' value='<?php echo $date_low ?>' maxlength='8'>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' align='right' style='font-size:11pt; font-weight:normal;'>
                        日付範囲指定(上限)(YYYYMMDD)
                    </td>
                    <td class='winbox' align='center'>
                        <input type='text' name='date_upp' class='pt12b' size='8' value='<?php echo $date_upp ?>' maxlength='8'>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' align='right' style='font-size:11pt; font-weight:normal;'>
                        最大表示件数(／頁)
                    </td>
                    <td class='winbox' align='center'>
                        <select name='view_rec' class='ret_font'>
                            <option value= '500'>&nbsp;500</option>
                            <option value='1000'>1000</option>
                            <option value='2000'>2000</option>
                            <option value='4000'>4000</option>
                            <option value='6000'>6000</option>
                        </select>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' colspan='2' align='center'>
                        <input type='submit' name='parts_stock_view' value='実行' >
                        <!-- Enter Key で実行します。 -->
                    </td>
                </tr>
            </table>
                </td></tr>
            </table> <!----------------- ダミーEnd ------------------>
        </form>
        <!--
        <br>
        <table style='border: 2px solid #CCBBAA;'>
            <tr><td align='center' class='pt11b' id='note'>日付範囲は問題なければ変更する必要はありません。</td></tr>
        </table>
        -->
    </center>
</body>
<?php echo $menu->out_alert_java() ?>
</html>
<?php
ob_end_flush();                 // 出力バッファをgzip圧縮 END
?>
