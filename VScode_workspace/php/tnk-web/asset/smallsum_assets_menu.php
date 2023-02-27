<?php
//////////////////////////////////////////////////////////////////////////////
// 小額資産管理メニュー                                                     //
// Copyright(C) 2011 Norihisa.Ohya norihisa_ooya@nitto-kohki.co.jp          //
// Changed history                                                          //
// 2011/09/28 Created  smallsum_assets_menu.php(assets_menu.phpをそのまま   //
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
$menu->set_site(81, 999);                // site_index=4(プログラム開発) site_id=999(子メニューあり)
////////////// リターンアドレス設定
// $menu->set_RetUrl(TOP_MENU);            // 上で設定している
//////////// タイトル名(ソースのタイトル名とフォームのタイトル名)
$menu->set_title('小額資産管理メニュー');
//////////// 表題の設定
$menu->set_caption('小額資産管理 メニュー');
//////////// 呼出先のaction名とアドレス設定
$menu->set_action('少額資産登録',    ASSET . 'smallsum_assets/smallSum_assets_Main.php');
$menu->set_action('少額資産台帳照会',    ASSET . 'smallsum_assets/smallSum_assetsView_Main.php');
$menu->set_action('各種マスターメンテ',    ASSET . 'master/assets_master_menu.php');
$menu->set_action('少額資産リスト除却無',    ASSET . 'smallsum_assets/smallSum_assetsList_delno.php');
$menu->set_action('少額資産リスト除却含',    ASSET . 'smallsum_assets/smallSum_assetsList_delyes.php');

/////////// HTML Header を出力してキャッシュを制御
$menu->out_html_header();
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
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
                    <form method="post" action="<?= $menu->out_action('少額資産台帳照会') ?>">
                        <input type='image' alt='少額資産台帳の照会' border=0 src='<?php echo menu_bar("../menu_tmp/smallsum_view.png","少額資産台帳照会",13)."?".uniqid("menu") ?>'>
                    </form>
                    <div>&nbsp;</div>
                </td>
                <td align="center">
                    <form method="post" action="<?= $menu->out_action('少額資産登録') ?>">
                        <input type='image' alt='少額資産登録画面の表示' border=0 src='<?php echo menu_bar("../menu_tmp/smallsum_input.png","少額資産登録",13)."?".uniqid("menu") ?>'>
                    </form>
                    <div>&nbsp;</div>
                </td>
            </tr>
            <tr>
                <?php 
                if ($_SESSION['User_ID'] == '300144') {
                ?>
                <td align="center">
                    <form method="post" action="<?= $menu->out_action('少額資産リスト除却無') ?>">
                        <input type='image' alt='少額資産台帳のリスト作成(除却を含まない)' border=0 src='<?php echo menu_bar("../menu_tmp/smallsum_listdelno.png","少額資産リスト作成",13)."?".uniqid("menu") ?>'>
                    </form>
                    <div>&nbsp;</div>
                </td>
                <td align="center">
                    <form method="post" action="<?= $menu->out_action('少額資産リスト除却含') ?>">
                        <input type='image' alt='少額資産台帳のリスト作成(除却を含む)' border=0 src='<?php echo menu_bar("../menu_tmp/smallsum_listdelyes.png","少額資産リスト作成(除却含)",11)."?".uniqid("menu") ?>'>
                    </form>
                    <div>&nbsp;</div>
                </td>
                <?php
                } else {
                ?>
                <td align="center">
                    <form method="post" action="">
                        <input type='image' alt='空のアイテム' border=0 src='<?php echo IMG ?>menu_item.gif'>
                    </form>
                    <div>&nbsp;</div>
                </td>
                <td align="center">
                    <form method="post" action="">
                        <input type='image' alt='空のアイテム' border=0 src='<?php echo IMG ?>menu_item.gif'>
                    </form>
                    <div>&nbsp;</div>
                </td>
                <?php
                }
                ?>
            </tr>
            <tr>
                <td align="center">
                    <form method="post" action="">
                        <input type='image' alt='空のアイテム' border=0 src='<?php echo IMG ?>menu_item.gif'>
                    </form>
                    <div>&nbsp;</div>
                </td>
                <td align="center">
                    <form method="post" action="">
                        <input type='image' alt='空のアイテム' border=0 src='<?php echo IMG ?>menu_item.gif'>
                    </form>
                    <div>&nbsp;</div>
                </td>
            </tr>
            <tr>
                <td align="center">
                    <form method="post" action="">
                        <input type='image' alt='空のアイテム' border=0 src='<?php echo IMG ?>menu_item.gif'>
                    </form>
                    <div>&nbsp;</div>
                </td>
                <?php
                if (getCheckAuthority(36)) {
                ?>
                <td align="center">
                    <form method="post" action="<?= $menu->out_action('各種マスターメンテ') ?>">
                        <input type='image' alt='各種マスターのメンテナンス画面' border=0 src='<?php echo menu_bar("../menu_tmp/smallsum_mainte.png","各種マスターメンテ",13)."?".uniqid("menu") ?>'>
                    </form>
                    <div>&nbsp;</div>
                </td>
                <?php
                } else {
                ?>
                <td align="center">
                    <form method="post" action="">
                        <input type='image' alt='空のアイテム' border=0 src='<?php echo IMG ?>menu_item.gif'>
                    </form>
                    <div>&nbsp;</div>
                </td>
                <?php
                }
                ?>
            </tr>
        </table>
    </center>
</body>
<?= $menu->out_site_java() ?>
</html>
<?php
ob_end_flush();                 // 出力バッファをgzip圧縮 END
?>
