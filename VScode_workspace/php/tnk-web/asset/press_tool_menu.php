<?php
//////////////////////////////////////////////////////////////////////////////
// 圧造工具管理メニュー                                                     //
// Copyright(C) 2011 Norihisa.Ohya norihisa_ooya@nitto-kohki.co.jp          //
// Changed history                                                          //
// 2011/09/28 Created  press_tool_menu.php                                  //
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
$menu->set_site(82, 999);                // site_index=4(プログラム開発) site_id=999(子メニューあり)
////////////// リターンアドレス設定
// $menu->set_RetUrl(TOP_MENU);            // 上で設定している
//////////// タイトル名(ソースのタイトル名とフォームのタイトル名)
$menu->set_title('圧造工具管理メニュー');
//////////// 表題の設定
$menu->set_caption('圧造工具管理 メニュー');
//////////// 呼出先のaction名とアドレス設定
$menu->set_action('新規購入',    ASSET . 'press_tool/pressTool_input_Main.php');
$menu->set_action('明細照会',    ASSET . 'press_tool/pressTool_details_view_Main.php');
$menu->set_action('集計照会',    ASSET . 'press_tool/pressTool_total_view_Main.php');
$menu->set_action('データ作成',    ASSET . 'press_tool/pressTool_total_create_Main.php');
$menu->set_action('工具マスター',    ASSET . 'press_tool/pressTool_master_Main.php');
$menu->set_action('機械マスター',    ASSET . 'press_tool/pressTool_machine_master_Main.php');

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
                    <form method="post" action="<?= $menu->out_action('新規購入') ?>">
                        <input type='image' alt='圧造工具の購入/使用画面' border=0 src='<?php echo menu_bar("../menu_tmp/press_tool_input.png","圧造工具購入/使用",13)."?".uniqid("menu") ?>'>
                    </form>
                    <div>&nbsp;</div>
                </td>
                <td align="center">
                    <form method="post" action="<?= $menu->out_action('明細照会') ?>">
                        <input type='image' alt='圧造工具在庫明細の照会' border=0 src='<?php echo menu_bar("../menu_tmp/press_tool_details_view.png","圧造工具在庫明細照会",13)."?".uniqid("menu") ?>'>
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
                <td align="center">
                    <form method="post" action="<?= $menu->out_action('集計照会') ?>">
                        <input type='image' alt='圧造工具在庫集計の照会' border=0 src='<?php echo menu_bar("../menu_tmp/press_tool_total_view.png","圧造工具在庫集計照会",13)."?".uniqid("menu") ?>'>
                    </form>
                    <div>&nbsp;</div>
                </td>
            </tr>
            <tr>
                <td align="center">
                    <form method="post" action="<?= $menu->out_action('データ作成') ?>">
                        <input type='image' alt='圧造工具の在庫集計データを登録します' border=0 src='<?php echo menu_bar("../menu_tmp/press_tool_total_create.png","在庫集計データ作成",13)."?".uniqid("menu") ?>'>
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
                    <form method="post" action="<?= $menu->out_action('工具マスター') ?>">
                        <input type='image' alt='圧造工具のマスター登録' border=0 src='<?php echo menu_bar("../menu_tmp/press_tool_master_input.png","圧造工具マスター登録",13)."?".uniqid("menu") ?>'>
                    </form>
                    <div>&nbsp;</div>
                </td>
                <td align="center">
                    <form method="post" action="<?= $menu->out_action('機械マスター') ?>">
                        <input type='image' alt='圧造工具を使用する機械のマスター登録' border=0 src='<?php echo menu_bar("../menu_tmp/machine_master_input.png","機械マスター登録",13)."?".uniqid("menu") ?>'>
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