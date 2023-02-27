<?php
//////////////////////////////////////////////////////////////////////////////
// 届出・申請書メニュー（経理）                                             //
// Copyright(C) 2014-2014 Norihisa.Ohya norihisa_ooya@nitto-kohki.co.jp     //
// Changed history                                                          //
// 2014/09/19 Created  act_appli_menu.php                                   //
// 2014/09/22 captionの内容を変更                                           //
//////////////////////////////////////////////////////////////////////////////
ini_set('error_reporting', E_ALL);      // E_ALL='2047' debug 用
// ini_set('display_errors', '1');         // Error 表示 ON debug 用 リリース後コメント
session_start();                        // ini_set()の次に指定すること Script 最上行
ob_start('ob_gzhandler');               // 出力バッファをgzip圧縮

require_once ('../function.php');       // TNK 全共通 function
require_once ('../MenuHeader.php');     // TNK 全共通 menu class
require_once ('../tnk_func.php');
access_log();                           // Script Name は自動取得

///// TNK 共用メニュークラスのインスタンスを作成
$menu = new MenuHeader(0);    // 認証レベル=0, リターンアドレス, タイトルの指定なし

////////////// サイト設定
$menu->set_site(98, 999);                // site_index=4(プログラム開発) site_id=999(子メニューあり)
////////////// リターンアドレス設定
// $menu->set_RetUrl(TOP_MENU);            // 上で設定している
//////////// タイトル名(ソースのタイトル名とフォームのタイトル名)
$menu->set_title('届出・申請書メニュー（社外）');
//////////// 表題の設定
$menu->set_caption('社外への各種届出用紙をダウンロード出来ます。');
//////////// 呼出先のaction名とアドレス設定

// 社内・社外別メニュー
$menu->set_action('人事に関する届出',    PER_APPLI . 'out_personnel_appli/out_personnel_appli_menu.php');
$menu->set_action('経理に関する届出',      PER_APPLI . 'out_account_appli/out_account_appli_menu.php');
$menu->set_action('総務に関する届出',  PER_APPLI . 'out_affairs_appli/out_affairs_appli_menu.php');
$menu->set_action('営繕に関する届出',  PER_APPLI . 'out_repair_appli/out_repair_appli_menu.php');
$menu->set_action('その他の届出',  PER_APPLI . 'out_other_appli/out_other_appli_menu.php');

// 以下は旧メニュー
// 経理関係
$menu->set_action('出張に関する届出',    ACT_APPLI . 'trip_appli/trip_appli_menu.php');
// その他
$menu->set_action('育児に関する届出',      ACT_APPLI . 'child_care_appli/child_care_appli_menu.php');
$menu->set_action('介護に関する届出',  ACT_APPLI . 'nurse_appli/nurse_appli_menu.php');
$menu->set_action('業務引継ぎに関する届出',  ACT_APPLI . 'transfer_appli/transfer_appli_menu.php');
$menu->set_action('融資・貸付に関する届出',  ACT_APPLI . 'loan_appli/loan_appli_menu.php');


/////////// HTML Header を出力してキャッシュを制御
$menu->out_html_header();
?>
<!DOCTYPE html>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html;charset=UTF-8">
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
                    <form method="post" action="<?= $menu->out_action('出張に関する届出') ?>">
                        <input type='image' alt='出張に関する届出メニュー' border=0 src='<?php echo menu_bar("menu_tmp/trip_appli_menu.png","出張に関する届出",13)."?".uniqid("menu") ?>'>
                    </form>
                    <div>&nbsp;</div>
                </td>
                <td align="center">
                    <form method="post" action="<?= $menu->out_action('育児に関する届出') ?>">
                        <input type='image' alt='育児に関する届出メニュー' border=0 src='<?php echo menu_bar("menu_tmp/child_care_appli_menu.png","育児に関する届出",13)."?".uniqid("menu") ?>'>
                    </form>
                    <div>&nbsp;</div>
                </td>
            </tr>
            <tr>
                <td align='center'>
                    <input type='image' name='post' alt='空のアイテム' border=0 src='<?php echo IMG ?>menu_item.gif'>
                    <div>&nbsp;</div>
                </td>
                <td align="center">
                    <form method="post" action="<?= $menu->out_action('介護に関する届出') ?>">
                        <input type='image' alt='介護に関する届出メニュー' border=0 src='<?php echo menu_bar("menu_tmp/nurse_appli_menu.png","介護に関する届出",13)."?".uniqid("menu") ?>'>
                    </form>
                    <div>&nbsp;</div>
                </td>
            </tr>
            <tr>
                <td align='center'>
                    <input type='image' name='post' alt='空のアイテム' border=0 src='<?php echo IMG ?>menu_item.gif'>
                    <div>&nbsp;</div>
                </td>
                <td align="center">
                    <form method="post" action="<?= $menu->out_action('業務引継ぎに関する届出') ?>">
                        <input type='image' alt='業務引継ぎに関する届出メニュー' border=0 src='<?php echo menu_bar("menu_tmp/transfer_appli_menu.png","業務引継ぎに関する届出",12)."?".uniqid("menu") ?>'>
                    </form>
                    <div>&nbsp;</div>
                </td>
            </tr>
            <tr>
                <td align='center'>
                    <input type='image' name='post' alt='空のアイテム' border=0 src='<?php echo IMG ?>menu_item.gif'>
                    <div>&nbsp;</div>
                </td>
                <td align="center">
                    <form method="post" action="<?= $menu->out_action('融資・貸付に関する届出') ?>">
                        <input type='image' alt='融資・貸付に関する届出メニュー' border=0 src='<?php echo menu_bar("menu_tmp/loan_appli_menu.png","融資・貸付に関する届出",12)."?".uniqid("menu") ?>'>
                    </form>
                    <div>&nbsp;</div>
                </td>
            </tr>
            <tr>
                <td align='center'>
                    <input type='image' name='post' alt='空のアイテム' border=0 src='<?php echo IMG ?>menu_item.gif'>
                    <div>&nbsp;</div>
                </td>
                <td align='center'>
                    <input type='image' name='post' alt='空のアイテム' border=0 src='<?php echo IMG ?>menu_item.gif'>
                    <div>&nbsp;</div>
                </td>
            </tr>
            <tr>
                <td align='center'>
                    <input type='image' name='post' alt='空のアイテム' border=0 src='<?php echo IMG ?>menu_item.gif'>
                    <div>&nbsp;</div>
                </td>
                <td align='center'>
                    <input type='image' name='post' alt='空のアイテム' border=0 src='<?php echo IMG ?>menu_item.gif'>
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
