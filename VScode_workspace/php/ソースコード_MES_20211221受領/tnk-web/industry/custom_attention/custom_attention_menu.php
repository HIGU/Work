<?php
//////////////////////////////////////////////////////////////////////////////
// 栃木日東工器 特注カプラ冶具作業注意点 メニュー                           //
// Copyright(C) 2013-2013 Norihisa.Ohya  norihisa_ooya@nitto-kohki.co.jp    //
// Changed history                                                          //
// 2013/01/30 Created custom_attention_menu.php                             //
// 2013/01/31 作業注意点のメニューを追加                                    //
// 2013/02/12 作業注意点のメニューを公開                                    //
//////////////////////////////////////////////////////////////////////////////
// ini_set('error_reporting', E_STRICT);       // E_STRICT=2048(php5) E_ALL=2047 debug 用
// ini_set('error_reporting', E_ALL);          // E_ALL='2047' debug 用
// ini_set('display_errors', '1');             // Error 表示 ON debug 用 リリース後コメント
// ini_set('implicit_flush', 'off');           // echo print で flush させない(遅くなるため) CLI版
// ini_set('max_execution_time', 1200);        // 最大実行時間=20分 WEB CGI版
ob_start('ob_gzhandler');                   // 出力バッファをgzip圧縮
session_start();                            // ini_set()の次に指定すること Script 最上行

require_once ('../../function.php');
require_once ('../../tnk_func.php');
require_once ('../../MenuHeader.php');         // TNK 全共通 menu class
access_log();                               // Script Name は自動取得

///// TNK 共用メニュークラスのインスタンスを作成
$menu = new MenuHeader(0);                  // 認証チェック0=一般以上 戻り先=TOP_MENU タイトル未設定

////////////// サイト設定
$menu->set_site(30, 999);                    // site_index=40(売上メニュー) site_id=999(サイトメニューを開く)
////////////// リターンアドレス設定
// $menu->set_RetUrl(SYS_MENU);                // 通常は指定する必要はない
//////////// タイトル名(ソースのタイトル名とフォームのタイトル名)
$menu->set_title('特注カプラ冶具作業注意点メニュー');
//////////// 表題の設定
$menu->set_caption('特注カプラ冶具作業注意点メニュー');
//////////// 呼出先のaction名とアドレス設定
    /************ left view *************/
$menu->set_action('不適合処置連絡書照会',       INDUST . 'custom_attention/claim_disposal_form.php');
$menu->set_action('作業注意点照会',             INDUST . 'custom_attention/attention_point_form.php');
$menu->set_action('作業注意点編集',             INDUST . 'custom_attention/attention_point_add_form.php');

    /************ right view *************/
//$menu->set_action('製品部品売上グラフ', SALES . 'view_all_hiritu.php');
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
<title><?php echo $menu->out_title() ?></title>
<?php echo $menu->out_site_java() ?>
<?php echo $menu->out_css() ?>

<!-- スタイルシートのファイル指定をコメント HTMLタグ コメントは入れ子に出来ない事に注意 
<link rel='stylesheet' href='<?php echo MENU_FORM . '?' . $uniq ?>' type='text/css' media='screen'>
-->

<!-- 現在はコメント
<script type='text/javascript' src='../sales.js'></script>
-->
<script type='text/javascript'>
<!--
function set_focus()
{
    // document.body.focus();   // F2/F12キーを有効化する対応
    // document.mhForm.backwardStack.focus();  // 上記はIEのみのためNN対応
}
// -->
</script>

<style type='text/css'>
<!--
body {
    background-image:       url(<?php echo IMG ?>t_nitto_logo4.png);
    background-repeat:      no-repeat;
    background-attachment:  fixed;
    background-position:    right bottom;
    overflow-y:             hidden;
}
-->
</style>

</head>
<body onLoad='set_focus()'>
    <center>
<?php echo $menu->out_title_border() ?>
        <table width='100%'>
            <tr>
                <td align='center'><img src='<?php echo IMG ?>t_nitto_logo2.gif' width=348 height=83></td>
            </tr>
            <tr>
                <td align='center' class='caption_font'>
                    <?php echo $menu->out_caption() . "\n" ?>
                </td>
            </tr>
        </table>
        
        <br>
        
        <table width='70%' border='0'> <!-- widthで間隔を調整 -->
        <tr>
            <!-- /////////////// left view ////////////// -->
        <td align='center' valign='top'>
            <table border='0' cellspacing='0' cellpadding='5'>
                <tr>
                    <form method='post' action='<?php echo $menu->out_action('不適合処置連絡書照会') ?>'>
                        <td align='center'>
                            <input type='image' alt='不適合処置連絡書の照会を行います。' border='0' src='<?php echo menu_bar('menu_tmp/menu_item_custom_claim.png', '不適合処置連絡書 照会', 12) . "?$uniq" ?>'>
                        </td>
                    </form>
                </tr>
                <tr>
                    <form method='post' action='<?php echo $menu->out_self() ?>'>
                        <td align='center'>
                            <input type='image' border='0'
                                alt='現在、空のメニューアイテムです。'
                                src='<?php echo menu_bar('menu_tmp/menu_item_custom_empty.png', '', 14, 0), "?{$uniq}" ?>'
                            >
                        </td>
                    </form>
                </tr>
                <tr>
                    <form method='post' action='<?php echo $menu->out_self() ?>'>
                        <td align='center'>
                            <input type='image' border='0'
                                alt='現在、空のメニューアイテムです。'
                                src='<?php echo menu_bar('menu_tmp/menu_item_custom_empty.png', '', 14, 0), "?{$uniq}" ?>'
                            >
                        </td>
                    </form>
                </tr>
                <!--
                <tr>
                    <form method='post' action='<?php echo SALES_MENU ?>'>
                        <td align='center'>
                            <input type='image' name='post' alt='空のアイテム' border=0 src='<?php echo IMG ?>menu_item.gif'>
                        </td>
                    </form>
                </tr>
                -->
                
            </table>
        </td>
            <!-- /////////////// right view ////////////// -->
        <td align='center' valign='top'>
            <table border='0' cellspacing='0' cellpadding='5'>
                <tr>
                    <form method='post' action='<?php echo $menu->out_action('作業注意点照会') ?>'>
                        <td align='center'>
                            <input type='image' alt='特注カプラ冶具・作業注意点の照会を行います。' border='0' src='<?php echo menu_bar('menu_tmp/menu_item_custom_attention_point_view.png', '作業注意点 照会', 12) . "?$uniq" ?>'>
                        </td>
                    </form>
                </tr>
                <tr>
                    <form method='post' action='<?php echo $menu->out_action('作業注意点編集') ?>'>
                        <td align='center'>
                            <input type='image' alt='特注カプラ冶具・作業注意点の登録・削除を行います。' border='0' src='<?php echo menu_bar('menu_tmp/menu_item_custom_attention_point_add.png', '作業注意点 編集', 12) . "?$uniq" ?>'>
                        </td>
                    </form>
                </tr>
                <tr>
                    <form method='post' action='<?php echo $menu->out_self() ?>'>
                        <td align='center'>
                            <input type='image' border='0'
                                alt='現在、空のメニューアイテムです。'
                                src='<?php echo menu_bar('menu_tmp/menu_item_custom_empty.png', '', 14, 0), "?{$uniq}" ?>'
                            >
                        </td>
                    </form>
                </tr>
                <!--
                <tr>
                    <form method='post' action='<?php echo SALES_MENU ?>'>
                        <td align='center'>
                            <input type='image' name='post' alt='空のアイテム' border=0 src='<?php echo IMG ?>menu_item.gif'>
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
<?php echo $menu->out_alert_java()?>
</html>
<?php
ob_end_flush();                 // 出力バッファをgzip圧縮 END
?>
