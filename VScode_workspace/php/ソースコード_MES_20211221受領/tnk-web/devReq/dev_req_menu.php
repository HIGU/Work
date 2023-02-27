<?php
//////////////////////////////////////////////////////////////////////////////
// プログラム開発依頼 メニュー                                              //
// Copyright(C) 2002-2010 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp       //
// Changed history                                                          //
// 2002/02/12 Created  dev_req_menu.php                                     //
// 2002/08/09 register_globals = Off 対応                                   //
// 2002/08/27 フレーム対応 & フレームサイトメニュー                         //
// 2002/12/25 function menu_bar() によるメニュー画像自動生成                //
// 2003/02/14 メニューのフォントをstyleで指定。ブラウザーによる変更を不可   //
// 2003/12/12 defineされた定数でディレクトリとメニュー名を使用する          //
//            ob_start('ob_gzhandler') を追加                               //
// 2004/02/13 index1.php→index.phpへ変更(index1はauthenticateに変更のため) //
// 2004/07/17 MenuHeader()クラスを新規作成しデザイン・認証等のロジック統一  //
// 2004/12/25 style='overflow:hidden;' (-xy両方)を追加                      //
// 2005/01/14 F2/F12キーを有効化する対応のため document.body.focus()を追加  //
// 2005/08/02 各メニュー間の<br>レイアウトを<div>&nbsp;</div>へ変更NN対応   //
// 2010/06/18 棚卸テストの呼び出し解除                                 大谷 //
// 2010/06/21 ファイル更新日時取得テスト                               大谷 //
// 2010/09/30 資産管理メニューを正式に移動の為リンク解除               大谷 //
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
$menu = new MenuHeader(0, TOP_MENU);    // 認証レベル=0, リターンアドレス, タイトルの指定なし

////////////// サイト設定
$menu->set_site(4, 999);                // site_index=4(プログラム開発) site_id=999(子メニューあり)
////////////// リターンアドレス設定
// $menu->set_RetUrl(TOP_MENU);            // 上で設定している
//////////// タイトル名(ソースのタイトル名とフォームのタイトル名)
$menu->set_title('プログラム開発依頼');
//////////// 表題の設定
$menu->set_caption('プログラム開発依頼 メニュー');
//////////// 呼出先のaction名とアドレス設定
$menu->set_action('開発依頼照会', DEV . 'dev_req_select.php');
$menu->set_action('開発依頼送信', DEV . 'dev_req_submit.php');
$menu->set_action('開発実績グラフ', DEV . 'dev_req_graph_jisseki.php');
$menu->set_action('開発未完了グラフ', DEV . 'dev_req_graph2.php');
$menu->set_action('color',    DEV . 'color_check_input.php');
$menu->set_action('更新日時テスト',    DEV . '/test/get_chg_ym.php');
//$menu->set_action('資産管理メニュー',    DEV . '/test/smallsum_assets/assets_menu.php');

$menu->set_action('プログラム管理メニュー', DEV . '/prog_master/prog_menu.php');
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
                    <form method="post" action="<?= $menu->out_action('開発依頼送信') ?>"><?php echo "\n"; // 旧ファイル../img/menu_item_dev_req.gif ?>
                        <input type='image' alt='依頼書作成・送信' border=0 src='<?php echo menu_bar("menu_tmp/develop_submit.png","開発依頼書作成/送信",13)."?".uniqid("menu") ?>'>
                    </form>
                    <div>&nbsp;</div>
                </td>
                <?php
                if (getCheckAuthority(32)) {
                ?>
                <td align="center">
                    <form method="post" action="<?= $menu->out_action('プログラム管理メニュー') ?>">
                        <input type='image' alt='プログラム管理メニューの表示' border=0 src='<?php echo menu_bar("menu_tmp/prog_menu.png","プログラム管理",13)."?".uniqid("menu") ?>'>
                    </form>
                    <div>&nbsp;</div>
                </td>
                <?php
                } else {
                ?>
                <td align="center">
                    <form method="post" action="<?= DEV_MENU ?>">
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
                    <form method="post" action="<?= $menu->out_action('開発依頼照会') ?>"><?php echo "\n"; // 旧ファイル../img/menu_item_dev_qry.gif ?>
                        <input type='image' alt='開発状況照会' border=0 src='<?php echo menu_bar("menu_tmp/develop_query.png","プログラム開発状況",13)."?".uniqid("menu") ?>'>
                    </form>
                    <div>&nbsp;</div>
                </td>
                <td align="center">
                    <form method="post" action="<?= DEV_MENU ?>">
                        <input type='image' alt='空のアイテム' border=0 src='<?php echo IMG ?>menu_item.gif'>
                    </form>
                    <div>&nbsp;</div>
                </td>
            </tr>
            <tr>
                <td align="center">
                    <form method="post" action="/processing_msg.php?script=<?= DEV ?>dev_req_graph_jisseki.php"><?php echo "\n"; // 旧ファイル../img/menu_item_dev_qry_graph.gif ?>
                        <input type='image' alt='開発実績照会グラフ' border=0 src='<?php echo menu_bar("menu_tmp/graph_jisseki.png","開発 件数/工数グラフ",13)."?".uniqid("menu") ?>'>
                    </form>
                    <div>&nbsp;</div>
                </td>
                <td align="center">
                    <form method="post" action="<?= DEV_MENU ?>">
                        <input type='image' alt='空のアイテム' border=0 src='<?php echo IMG ?>menu_item.gif'>
                    </form>
                    <div>&nbsp;</div>
                </td>
            </tr>
            <tr>
                <td align="center">         <!-- 開発 受付・完了・未完了 件数グラフ -->
                    <form method="post" action="/processing_msg.php?script=<?= DEV ?>dev_req_graph2.php"><?php echo "\n"; // 旧ファイル../img/menu_item_dev_req_graph2.gif ?>
                        <input type='image' alt='開発 受付・完了・未完了 件数グラフ' border=0 src='<?php echo menu_bar("menu_tmp/graph_uketuke.png","受付/完了/未完了ｸﾞﾗﾌ",13)."?".uniqid("menu") ?>'>
                    </form>
                    <div>&nbsp;</div>
                </td>
                <td align="center">
                    <form method="post" action="<?= $menu->out_action('更新日時テスト') ?>">
                        <input type='image' alt='更新日時取得テスト' border=0 src='<?php echo menu_bar("menu_tmp/test_chgym.png","更新日時取得テスト",13)."?".uniqid("menu") ?>'>
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
