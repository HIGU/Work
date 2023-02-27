<?php
//////////////////////////////////////////////////////////////////////////////
// 損益 予測 メニュー                                                       //
// Copyright (C) 2011-     Norihisa.Ohya norihisa_ooya@nitto-kohki.co.jp    //
// Changed history                                                          //
// 2011/07/14 Created  profit_loss_estimate_menu.php                        //
// 2011/07/19 予測照会を都度と照会のみに分解（都度照会は自分のみ）          //
//////////////////////////////////////////////////////////////////////////////
// ini_set('error_reporting', E_ALL);          // E_ALL='2047' debug 用
// ini_set('display_errors', '1');             // Error 表示 ON debug 用 リリース後コメント
session_start();                            // ini_set()の次に指定すること Script 最上行
ob_start('ob_gzhandler');                   // 出力バッファをgzip圧縮

require_once ('../../function.php');           // define.php と pgsql.php を require_once している
require_once ('../../tnk_func.php');           // TNK に依存する部分の関数を require_once している menu_bar()で使用
require_once ('../../MenuHeader.php');         // TNK 全共通 menu class
access_log();                               // Script Name は自動取得

///// TNK 共用メニュークラスのインスタンスを作成
$menu = new MenuHeader(0);                  // 認証チェック0=一般以上 戻り先=TOP_MENU タイトル未設定

////////////// サイト設定
$menu->set_site(10, 999);                   // site_index=10(損益メニュー) site_id=999(サイトを開く)
////////////// リターンアドレス設定
// $menu->set_RetUrl(MENU);                 // 通常は指定する必要はない(トップメニュー)
//////////// タイトル名(ソースのタイトル名とフォームのタイトル名)
$menu->set_title('損益予測メニュー');
//////////// 表題の設定
$menu->set_caption('損益予測の照会');
//////////// 呼出先のaction名とアドレス設定
$menu->set_action('損益予測照会都度',   PL . '/pl_estimate/profit_loss_estimate_Main.php');
$menu->set_action('損益予測照会',   PL . '/pl_estimate/profit_loss_estimate_view_Main.php');

//////////////// 各アンカーに変数でセットする 関数コールのオーバーヘッドを１回で済ませるため
$uniq = uniqid('menu');

unset($_SESSION['act_offset']);     // 部門コードテーブルで使用するoffset値を削除
unset($_SESSION['cd_offset']);      // コードテーブルで使用するoffset値を削除

/////////// HTML Header を出力してキャッシュを制御
$menu->out_html_header();
?>
<!DOCTYPE html>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<meta http-equiv="Content-Style-Type" content="text/css">
<meta http-equiv="Content-Script-Type" content="text/javascript">
<title><?php echo $menu->out_title() ?></title>
<?php echo $menu->out_site_java()?>
<?php echo $menu->out_css()?>

<!-- スタイルシートのファイル指定をコメント HTMLタグ コメントは入れ子に出来ない事に注意 
<link rel='stylesheet' href='<?php echo MENU_FORM . '?' . $uniq ?>' type='text/css' media='screen'>
-->

<style type="text/css">
<!--
-->
</style>
</head>
<body onLoad='document.mhForm.backwardStack.focus()' style='overflow:hidden;'>
    <center>
<?php echo $menu->out_title_border()?>
        
        <table width='100%'>
            <tr>
                <td align='center'><img src='<?php echo IMG ?>t_nitto_logo2.gif' width=348 height=83></td>
            </tr>
            <tr>
                <td align='center' class='caption_font'>
                    <?php echo $menu->out_caption(), "\n" ?>
                </td>
            </tr>
        </table>
        
        <br>
        
        <table width='70%' border='0'> <!-- widthで間隔を調整 -->
        <tr>
            <!-- /////////////// left view ////////////// -->
        <td align='center' valign='top'>
            <table border='0'>
                <tr>
                    <form method='post' action='<?php echo $menu->out_action('損益予測照会')?>'>
                        <td align='center'>
                            <input type='image' alt='月次損益の予測照会(照会のみ)' border=0 src='<?php echo menu_bar('../menu_tmp/menu_item_pl_estimate_view.png', ' 損益 予測 照会', 14) . "?id=$uniq" ?>'>
                            <div>&nbsp;</div>
                        </td>
                    </form>
                </tr>
                
                <tr>
                    <form method='post' action='<?php echo $menu->out_self()?>'>
                        <td align='center'>
                            <input type='image' alt='空のアイテム' border=0 src='<?php echo IMG ?>menu_item.gif'>
                            <div>&nbsp;</div>
                        </td>
                    </form>
                </tr>
                
                <tr>
                    <form method='post' action='<?php echo $menu->out_self()?>'>
                        <td align='center'>
                            <input type='image' alt='空のアイテム' border=0 src='<?php echo IMG ?>menu_item.gif'>
                            <div>&nbsp;</div>
                        </td>
                    </form>
                </tr>
                
                <tr>
                    <form method='post' action='<?php echo $menu->out_self()?>'>
                        <td align='center'>
                            <input type='image' alt='空のアイテム' border=0 src='<?php echo IMG ?>menu_item.gif'>
                            <div>&nbsp;</div>
                        </td>
                    </form>
                </tr>
                
                <tr>
                    <form method='post' action='<?php echo $menu->out_self()?>'>
                        <td align='center'>
                            <input type='image' alt='空のアイテム' border=0 src='<?php echo IMG ?>menu_item.gif'>
                            <div>&nbsp;</div>
                        </td>
                    </form>
                </tr>
                
                <tr>
                    <form method='post' action='<?php echo $menu->out_self()?>'>
                        <td align='center'>
                            <input type='image' alt='空のアイテム' border=0 src='<?php echo IMG ?>menu_item.gif'>
                            <div>&nbsp;</div>
                        </td>
                    </form>
                </tr>
                
                <!--
                <tr>
                    <form method='post' action='<?php echo $menu->out_self()?>'>
                        <td align='center'>
                            <input type='image' alt='空のアイテム' border=0 src='<?php echo IMG ?>menu_item.gif'>
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
                <tr>
                    <form method='post' action='<?php echo $menu->out_self()?>'>
                        <td align='center'>
                            <input type='image' alt='空のアイテム' border=0 src='<?php echo IMG ?>menu_item.gif'>
                            <div>&nbsp;</div>
                        </td>
                    </form>
                </tr>
                
                <tr>
                    <form method='post' action='<?php echo $menu->out_self()?>'>
                        <td align='center'>
                            <input type='image' alt='空のアイテム' border=0 src='<?php echo IMG ?>menu_item.gif'>
                            <div>&nbsp;</div>
                        </td>
                    </form>
                </tr>
                
                <tr>
                    <form method='post' action='<?php echo $menu->out_self()?>'>
                        <td align='center'>
                            <input type='image' alt='空のアイテム' border=0 src='<?php echo IMG ?>menu_item.gif'>
                            <div>&nbsp;</div>
                        </td>
                    </form>
                </tr>
                
                <tr>
                    <form method='post' action='<?php echo $menu->out_self()?>'>
                        <td align='center'>
                            <input type='image' alt='空のアイテム' border=0 src='<?php echo IMG ?>menu_item.gif'>
                            <div>&nbsp;</div>
                        </td>
                    </form>
                </tr>
                
                <tr>
                    <form method='post' action='<?php echo $menu->out_self()?>'>
                        <td align='center'>
                            <input type='image' alt='空のアイテム' border=0 src='<?php echo IMG ?>menu_item.gif'>
                            <div>&nbsp;</div>
                        </td>
                    </form>
                </tr>
                <?php if ($_SESSION['User_ID'] == '300144') { ?>
                <tr>
                    <form method='post' action='<?php echo $menu->out_action('損益予測照会都度')?>'>
                        <td align='center'>
                            <input type='image' alt='月次損益の予測照会(都度計算)' border=0 src='<?php echo menu_bar('../menu_tmp/menu_item_pl_estimate_once_view.png', ' 損益 予測 照会(都度)', 14) . "?id=$uniq" ?>'>
                            <div>&nbsp;</div>
                        </td>
                    </form>
                </tr>
                <?php } else { ?>
                <tr>
                    <form method='post' action='<?php echo $menu->out_self()?>'>
                        <td align='center'>
                            <input type='image' alt='空のアイテム' border=0 src='<?php echo IMG ?>menu_item.gif'>
                            <div>&nbsp;</div>
                        </td>
                    </form>
                </tr>
                <?php } ?>
            </table>
        </td>
        </tr>
        </table>
    </center>
</body>
<?php echo $menu->out_alert_java()?>
</html>
<?php
ob_end_flush();                 // 出力バッファをgzip圧縮 END
?>
