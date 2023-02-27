<?php
//////////////////////////////////////////////////////////////////////////////
// 生産支援関連 メニュー                                                    //
// Copyright(C) 2011 Norihisa.Ohya norihisa_ooya@nitto-kohki.co.jp          //
// Changed history                                                          //
// 2011/12/21 Created  product_support_menu.php                             //
// 2011/12/27 平出のみの仕入高照会を追加                                    //
//            月別在庫金額の照会を追加                                      //
// 2011/12/28 仕入高照会と在庫金額照会の表示限定解除                        //
///////////////////////////////////////////////////////////////////////////////
ini_set('error_reporting', E_ALL);      // E_ALL='2047' debug 用
// ini_set('display_errors', '1');         // Error 表示 ON debug 用 リリース後コメント
session_start();                        // ini_set()の次に指定すること Script 最上行
ob_start('ob_gzhandler');               // 出力バッファをgzip圧縮

require_once ('../../function.php');       // TNK 全共通 function
require_once ('../../MenuHeader.php');     // TNK 全共通 menu class
require_once ('../../tnk_func.php');
access_log();                           // Script Name は自動取得

///// TNK 共用メニュークラスのインスタンスを作成
$menu = new MenuHeader(0);    // 認証レベル=0, リターンアドレス, タイトルの指定なし

////////////// サイト設定
$menu->set_site(30, 999);                   // site_index=30(生産メニュー) site_id=999(サイトメニューを開く)
////////////// リターンアドレス設定
// $menu->set_RetUrl(TOP_MENU);            // 上で設定している
//////////// タイトル名(ソースのタイトル名とフォームのタイトル名)
$menu->set_title('生産支援関連 メニュー');
//////////// 表題の設定
$menu->set_caption('生産支援関連 メニュー');
//////////// 呼出先のaction名とアドレス設定
$menu->set_action('平出仕入高照会(月別)', INDUST . 'product_support/hiraide_act_payable_form.php');
$menu->set_action('平出棚卸高照会(月別)', INDUST . 'product_support/hiraide_invent_form.php');
$menu->set_action('平出棚卸用データ照会', INDUST . 'product_support/hiraide_stocktaking_view.php');
$menu->set_action('平出最新単価金額照会', INDUST . 'product_support/hiraide_stocktaking_saishin_view.php');
$menu->set_action('生産支援品マスター',   INDUST . 'product_support/product_support_master/product_support_master_menu.php');
/////////// HTML Header を出力してキャッシュを制御
$menu->out_html_header();
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html;charset=euc-jp">
<meta http-equiv="Content-Style-Type" content="text/css">
<meta http-equiv="Content-Script-Type" content="text/javascript">
<title><?= $menu->out_title() ?></title>
<style type="text/css">
<!--
.test_font {
    font-size: 12pt;
    font-weight: bold;
    font-family: monospace;
}
-->
</style>
<?= $menu->out_css() ?>
<script type='text/javascript'>
<!--
function set_focus()
{
    // document.body.focus();   // F2/F12キーを有効化する対応
    // document.mhForm.backwardStack.focus();  // 上記はIEのみのためNN対応
}
// -->
</script>
</head>
<body style='overflow:hidden;' onLoad='set_focus()'>
    <center>
<?= $menu->out_title_border() ?>
        <table border='0'>
            <tr>
                <td>
                    <p><img src='<?php echo IMG ?>t_nitto_logo3.gif' width='348' height='83'></p>
                </td>
            </tr>
            <tr>
                <td align='center' class='caption_font'>
                    <?= $menu->out_caption() . "\n" ?>
                </td>
            </tr>
        </table>
        <table border='0'>
            <tr>
                <td align='center'>
                    <img src='<?php echo IMG ?>tnk-turbine.gif' width='68' height='72'>
                </td>
            </tr>
        </table>
        <table width='80%' border='0'>
            <tr>
                <td align="center">
                    <form method="post" action="<?= $menu->out_action('平出仕入高照会(月別)') ?>">
                        <input type='image' alt='平出工場の仕入高を照会できます。' border=0 src='<?php echo menu_bar("../../menu_tmp/hiraide_act_payable.png","平出仕入高照会(月別)",13)."?".uniqid("menu") ?>'>
                    </form>
                    <div>&nbsp;</div>
                </td>
                <td align="center">
                    <form method="post" action="<?= $menu->out_action('平出棚卸高照会(月別)') ?>">
                        <input type='image' alt='平出工場の棚卸高照会できます。' border=0 src='<?php echo menu_bar("../../menu_tmp/hiraide_invent.png","平出棚卸高照会(月別)",13)."?".uniqid("menu") ?>'>
                    </form>
                    <div>&nbsp;</div>
                </td>
            </tr>
            <tr>
                <td align="center">
                    <form method="post" action="<?= DEV_MENU ?>">
                        <input type='image' alt='空のアイテム' border=0 src='<?php echo IMG ?>menu_item.gif'>
                    </form>
                    <div>&nbsp;</div>
                </td>
                <td align="center">
                    <form method="post" action="<?= $menu->out_action('平出棚卸用データ照会') ?>">
                        <input type='image' alt='平出工場の棚卸用データ照会を行います。' border=0 src='<?php echo menu_bar("../../menu_tmp/hiraide_stocktaking.png","平出棚卸用データ照会",13)."?".uniqid("menu") ?>'>
                    </form>
                    <div>&nbsp;</div>
                </td>
            </tr>
            <tr>
                <td align="center">
                    <form method="post" action="<?= DEV_MENU ?>">
                        <input type='image' alt='空のアイテム' border=0 src='<?php echo IMG ?>menu_item.gif'>
                    </form>
                    <div>&nbsp;</div>
                </td>
                <td align="center">
                    <form method="post" action="<?= $menu->out_action('平出最新単価金額照会') ?>">
                        <input type='image' alt='平出工場の最新単価金額照会を行います。' border=0 src='<?php echo menu_bar("../../menu_tmp/hiraide_stocktaking_saishin.png","平出最新単価金額照会",13)."?".uniqid("menu") ?>'>
                    </form>
                    <div>&nbsp;</div>
                </td>
            </tr>
            <tr>
                <td align="center">
                    <form method="post" action="<?= DEV_MENU ?>">
                        <input type='image' alt='空のアイテム' border=0 src='<?php echo IMG ?>menu_item.gif'>
                    </form>
                    <div>&nbsp;</div>
                </td>
                <td align="center">
                    <form method="post" action="<?= $menu->out_action('生産支援品マスター') ?>">
                        <input type='image' alt='生産支援品のマスターを編集します。' border=0 src='<?php echo menu_bar("../../menu_tmp/product_support_master.png","生産支援品マスター",13)."?".uniqid("menu") ?>'>
                    </form>
                    <div>&nbsp;</div>
                </td>
            </tr>
        </table>
    </center>
</body>
<?= $menu->out_site_java() ?>
</html>
<?php
ob_end_flush();                 // 出力バッファをgzip圧縮 END
?>
