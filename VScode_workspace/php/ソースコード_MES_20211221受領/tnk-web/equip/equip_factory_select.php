<?php
//////////////////////////////////////////////////////////////////////////////
// 設備稼動管理システムの工場選択メニュー                                   //
// Copyright (C) 2004-2021 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp      //
// Changed history                                                          //
// 2004/09/27 Created   equip_factory_select.php                            //
// 2004/12/25 style='overflow:hidden;' (-xy両方)を追加                      //
// 2005/01/14 F2/F12キーを有効化する対応のため document.body.focus()を追加  //
// 2005/08/02 各メニュー間の<br>レイアウトを<div>&nbsp;</div>へ変更NN対応   //
// 2006/03/14 equip_menu.css を新設                                         //
// 2018/05/18 ７工場を追加。コード４の登録を強制的に7に変更            大谷 //
// 2018/12/25 ７工場を真鍮とSUSに分離。後々の為。                      大谷 //
//            ４工場と５工場も表示。後々の為。                              //
// 2021/06/22 整理の為、一旦４・５工場をコメント化。                   大谷 //
//////////////////////////////////////////////////////////////////////////////
ini_set('error_reporting', E_ALL);          // E_ALL='2047' debug 用
// ini_set('display_errors', '1');             // Error 表示 ON debug 用 リリース後コメント
session_start();                            // ini_set()の次に指定すること Script 最上行
ob_start('ob_gzhandler');                   // 出力バッファをgzip圧縮

require_once ('equip_function.php');        // 設備関係専用 (内部でfunction.phpを呼出している)
require_once ('../tnk_func.php');           // menu_bar() で使用
require_once ('../MenuHeader.php');         // TNK 全共通 menu class
access_log();                               // Script Name は自動取得

///// TNK 共用メニュークラスのインスタンスを作成
$menu = new MenuHeader(0, TOP_MENU);        // 認証チェック0=一般以上 戻り先=''TOP_MENU タイトル未設定

////////////// サイト設定
$menu->set_site(40, 0);                     // site_index=40(設備メニュー2) site_id=0(siteを開かない)
////////////// リターンアドレス設定
// $menu->set_RetUrl(TOP_MENU);
//////////// タイトル名(ソースのタイトル名とフォームのタイトル名)
$menu->set_title('設備稼動管理システム メニュー');

//////////// 表題の設定
$menu->set_caption('工場選択 メニュー');
//////////// 呼出先のaction名とアドレス設定
$menu->set_action('equip_menu', EQUIP_MENU2);
$menu->set_action('equip_menu_moni', EQUIP2.'equip_menu_moni.php');

//////////////// 各アンカーに変数でセットする 関数コールのオーバーヘッドを１回で済ませるため
$uniq = uniqid('menu');

/////////// HTML Header を出力してキャッシュを制御
$menu->out_html_header();
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=EUC-JP">
<meta http-equiv="Content-Style-Type" content="text/css">
<meta http-equiv="Content-Script-Type" content="text/javascript">
<title><?= $menu->out_title() ?></title>
<?= $menu->out_css() ?>

<!-- スタイルシートのファイル指定をコメント HTMLタグ コメントは入れ子に出来ない事に注意 -->
<link rel='stylesheet' href='equip_menu.css?<?php echo $uniq ?>' type='text/css' media='screen'>

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
        
        <table width='100%'>
            <tr>
                <td align='center'><img src='<?= IMG ?>t_nitto_logo2.gif' width=348 height=83></td>
            </tr>
            <tr>
                <td align='center' class='caption_font'>
                    <?= $menu->out_caption(), "\n" ?>
                </td>
            </tr>
        </table>
        
        <br>
        
        <table width='70%' border='0'> <!-- widthで間隔を調整 -->
        <tr>
            <!-- /////////////// left view ////////////// -->
        <td align='center' valign='top'>
            <table border='0'>
                <!--
                <tr>
                    <form method='post' action='<?= $menu->out_action('equip_menu'), '?factory=4' ?>'>
                        <td align='center'>
                            <input type='image' alt='設備・機械 管理システム ４工場 メニュー' border=0
                            src='<?php echo menu_bar('menu_tmp/menu_item_equip_menu4f.png', '  設 備 ４工場', 14) . "?$uniq" ?>'>
                            <div>&nbsp;</div>
                        </td>
                    </form>
                </tr>
                -->
                <tr>
                    <form method='post' action='<?= $menu->out_action('equip_menu_moni'), '?factory=6' ?>'>
                        <td align='center'>
                            <input type='image' alt='設備・機械 管理システム ６工場 メニュー' border=0
                            src='<?php echo menu_bar('menu_tmp/menu_item_equip_menu6f.png', '  設 備 ６工場', 14) . "?$uniq" ?>'>
                            <div>&nbsp;</div>
                        </td>
                    </form>
                </tr>
                <tr>
                    <form method='post' action='<?= $menu->out_action('equip_menu'), '?factory=7' ?>'>
                        <td align='center'>
                            <input type='image' alt='設備・機械 管理システム ７工場(真鍮) メニュー' border=0
                            src='<?php echo menu_bar('menu_tmp/menu_item_equip_menu7f1.png', '  設 備 ７工場(真鍮)', 13) . "?$uniq" ?>'>
                            <div>&nbsp;</div>
                        </td>
                    </form>
                </tr>
                <!--
                <tr>
                    <form method='post' action='<?= $menu->out_action('equip_menu'), '?factory=' ?>'>
                        <td align='center'>
                            <input type='image' alt='設備・機械 管理システム 全工場 メニュー' border=0
                            src='<?php echo menu_bar('menu_tmp/menu_item_equip_menu.png', '  設 備 全工場', 14) . "?$uniq" ?>'>
                            <div>&nbsp;</div>
                        </td>
                    </form>
                </tr>
                -->
            </table>
        </td>
            <!-- /////////////// right view ////////////// -->
        <td align='center' valign='top'>
            <table border='0'>
                <!--
                <tr>
                    <form method='post' action='<?= $menu->out_action('equip_menu'), '?factory=5' ?>'>
                        <td align='center'>
                            <input type='image' alt='設備・機械 管理システム ５工場 メニュー' border=0
                            src='<?php echo menu_bar('menu_tmp/menu_item_equip_menu5f.png', '  設 備 ５工場', 14) . "?$uniq" ?>'>
                            <div>&nbsp;</div>
                        </td>
                    </form>
                </tr>
                -->
                <tr>
                    <form method='post' action='<?= $menu->out_self() ?>'>
                        <td align='center'>
                            <input type='image' name='post' alt='空のアイテム' border=0
                            src='<?= IMG ?>menu_item.gif'>
                            <div>&nbsp;</div>
                        </td>
                    </form>
                </tr>
                
                <tr>
                    <form method='post' action='<?= $menu->out_action('equip_menu'), '?factory=8' ?>'>
                        <td align='center'>
                            <input type='image' alt='設備・機械 管理システム ７工場(SUS) メニュー' border=0
                            src='<?php echo menu_bar('menu_tmp/menu_item_equip_menu7f2.png', '  設 備 ７工場(SUS)', 13) . "?$uniq" ?>'>
                            <div>&nbsp;</div>
                        </td>
                    </form>
                </tr>
                <!--
                <tr>
                    <form method='post' action='<?= $menu->out_self() ?>'>
                        <td align='center'>
                            <input type='image' name='post' alt='空のアイテム' border=0
                            src='<?= IMG ?>menu_item.gif'>
                            <div>&nbsp;</div>
                        </td>
                    </form>
                </tr>
                -->
            </table>
        </td>
        </tr>
        </table>
    </center>
</body>
</html>
<?= $menu->out_site_java() ?>
<?php
ob_end_flush();                 // 出力バッファをgzip圧縮 END
?>
