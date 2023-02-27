<?php
//////////////////////////////////////////////////////////////////////////////
// 損益 メニュー   (旧 月次・中間・決算 メニュー)                           //
// Copyright (C) 2002-2016 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp      //
// Changed history                                                          //
// 2002/03/22 Created  pl_menu.php                                          //
// 2003/12/10 kessan_menu.php → pl_menu.php へ新規作成                     //
// 2003/12/12 defineされた定数でディレクトリとメニューを使用して管理する    //
//            ob_start('ob_gzhandler') を追加                               //
// 2004/02/13 index1.php→index.phpへ変更(index1はauthenticateに変更のため) //
// 2004/06/10 view_user($_SESSION['User_ID']) をメニューヘッダーの下に追加  //
// 2004/12/25 style='overflow:hidden;' (-xy両方)を追加                      //
// 2005/01/18 MenuHeader class を使用して共通メニュー化及び認証方式へ変更   //
//            onLoad='document.mhForm.backwardStack.focus()'に変更しNN対応  //
// 2005/08/02 各メニュー間の<br>レイアウトを<div>&nbsp;</div>へ変更NN対応   //
// 2006/09/28 組立自動機賃率メニュー(新規)を現在までのものと置換え          //
// 2007/10/05 組立自動機賃率メニューリンク先変更ooyaを削除 大谷             //
// 2007/10/06 グラフ作成メニューを追加。E_ALL|E_STRICTへ ショートカット廃止 //
// 2016/03/08 損益予測メニューを野澤課長代理も照会できるよう変更            //
//////////////////////////////////////////////////////////////////////////////
// ini_set('error_reporting', E_ALL);          // E_ALL='2047' debug 用
// ini_set('display_errors', '1');             // Error 表示 ON debug 用 リリース後コメント
session_start();                            // ini_set()の次に指定すること Script 最上行
ob_start('ob_gzhandler');                   // 出力バッファをgzip圧縮

require_once ('../function.php');           // define.php と pgsql.php を require_once している
require_once ('../tnk_func.php');           // TNK に依存する部分の関数を require_once している menu_bar()で使用
require_once ('../MenuHeader.php');         // TNK 全共通 menu class
access_log();                               // Script Name は自動取得

///// TNK 共用メニュークラスのインスタンスを作成
$menu = new MenuHeader(0);                  // 認証チェック0=一般以上 戻り先=TOP_MENU タイトル未設定

////////////// サイト設定
$menu->set_site(10, 999);                   // site_index=10(損益メニュー) site_id=999(サイトを開く)
////////////// リターンアドレス設定
// $menu->set_RetUrl(MENU);                 // 通常は指定する必要はない(トップメニュー)
//////////// タイトル名(ソースのタイトル名とフォームのタイトル名)
$menu->set_title('損 益 メニュー');
//////////// 表題の設定
$menu->set_caption('損益関係の照会・更新');
//////////// 呼出先のaction名とアドレス設定
$menu->set_action('損益照会メニュー',   PL . 'profit_loss_query_menu.php');
$menu->set_action('損益作成メニュー',   PL . 'profit_loss_select.php');
$menu->set_action('経理部門コード保守', PL . 'act_table_mnt_new.php');
$menu->set_action('大分類配賦率保守',   PL . 'category_mnt.php');
$menu->set_action('小分類配賦率保守',   PL . 'allocation_mnt.php');
$menu->set_action('コードテーブル保守', PL . 'cd_table_mnt.php');
$menu->set_action('機械賃率照会更新',   PL . 'machine_labor_rate_mnt.php');
// $menu->set_action('組立賃率自動機賃率', PL . 'wage_rate.php');
$menu->set_action('組立賃率自動機賃率', PL . 'wage_rate/wage_rate_menu.php');
$menu->set_action('サービス割合メニュー', PL . 'service/service_percentage_menu.php');
// $menu->set_action('作業応援月報入力',   PL . '');
$menu->set_action('グラフ作成メニュー', PL . 'graphCreate/graphCreate_Form.php');
$menu->set_action('損益予測メニュー',   PL . '/pl_estimate/profit_loss_estimate_menu.php');

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
                    <form method='post' action='<?php echo $menu->out_action('損益照会メニュー')?>'>
                        <td align='center'>
                            <input type='image' alt='月次損益の照会メニュー' border=0 src='<?php echo menu_bar('menu_tmp/menu_item_pl_query_menu.png', ' 損益 照会 メニュー', 14) . "?id=$uniq" ?>'>
                            <div>&nbsp;</div>
                        </td>
                    </form>
                </tr>
                
                <tr>
                    <form method='post' action='<?php echo $menu->out_action('損益作成メニュー')?>'>
                        <td align='center'>
                            <input type='image' alt='月次 損益 作成 メニュー' border=0 src='<?php echo menu_bar('menu_tmp/menu_item_pl_update_menu.png', ' 損益 作成 メニュー', 14) . "?id=$uniq" ?>'>
                            <div>&nbsp;</div>
                        </td>
                    </form>
                </tr>
                
                <tr>
                    <form method='post' action='<?php echo $menu->out_action('経理部門コード保守')?>'>
                        <td align='center'>
                            <input type='image' alt='経理部門コードの保守メニュー' border=0 src='<?php echo menu_bar('menu_tmp/menu_item_act_table_menu.png', ' 経理部門コード保守', 14, 0) . "?id=$uniq" ?>'>
                            <div>&nbsp;</div>
                        </td>
                    </form>
                </tr>
                
                <tr>
                    <form method='post' action='<?php echo $menu->out_action('大分類配賦率保守')?>'>
                        <td align='center'>
                            <input type='image' alt='損益関係の大分類配賦率等の保守メニュー' border=0 src='<?php echo menu_bar('menu_tmp/menu_item_category_mnt.png', ' 大分類 配賦率 保守', 14, 0) . "?id=$uniq" ?>'>
                            <div>&nbsp;</div>
                        </td>
                    </form>
                </tr>
                
                <tr>
                    <form method='post' action='<?php echo $menu->out_action('小分類配賦率保守')?>'>
                        <td align='center'>
                            <input type='image' alt='損益関係の小分類配賦率保守メニュー' border=0 src='<?php echo menu_bar('menu_tmp/menu_item_allocation_mnt.png', ' 小分類 配賦率 保守', 14, 0) . "?id=$uniq" ?>'>
                            <!-- <input type='image' value='空のアイテム' border=0 src='./img/menu_item.gif'> -->
                            <div>&nbsp;</div>
                        </td>
                    </form>
                </tr>
                
                <tr>
                    <form method='post' action='<?php echo $menu->out_action('グラフ作成メニュー')?>'>
                        <td align='center'>
                            <input type='image' alt='損益関係のグラフ作成メニュー' border=0 src='<?php echo menu_bar('menu_tmp/menu_item_graphCreate.png', '損益グラフ作成メニュー', 13, 0) . "?id=$uniq" ?>'>
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
                <?php if (($_SESSION['User_ID'] == '300144') || ($_SESSION['User_ID'] == '015806')) { ?>
                <tr>
                    <form method='post' action='<?php echo $menu->out_action('損益予測メニュー')?>'>
                        <td align='center'>
                             <input type='image' alt='月次損益の予測メニュー' border=0 src='<?php echo menu_bar('menu_tmp/menu_item_pl_estimate_menu.png', ' 損益 予測 メニュー', 14, 0) . "?id=$uniq" ?>'>
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
                <tr>
                    <form method='post' action='<?php echo $menu->out_action('コードテーブル保守')?>'>
                        <td align='center'>
                             <input type='image' alt='経理・組織・人事コードテーブル保守' border=0 src='<?php echo menu_bar('menu_tmp/menu_item_cd_table_mnt.png', ' コードテーブル保守', 14, 0) . "?id=$uniq" ?>'>
                            <div>&nbsp;</div>
                        </td>
                    </form>
                </tr>
                
                <tr>
                    <form method='post' action='<?php echo $menu->out_action('機械賃率照会更新')?>'>
                        <td align='center'>
                             <input type='image' alt='製造課の機械 賃率 計算表 処理メニュー' border=0 src='<?php echo menu_bar('menu_tmp/menu_item_machine_rate.png', ' 機械賃率 照会 更新', 14, 0) . "?id=$uniq" ?>'>
                            <div>&nbsp;</div>
                        </td>
                    </form>
                </tr>
                
                <tr>
                    <form method='post' action='<?php echo $menu->out_action('組立賃率自動機賃率')?>'>
                        <td align='center'>
                            <input type='image' alt='組立賃率・自動機賃率の照会' border=0 src='<?php echo menu_bar('menu_tmp/menu_item_wage_rate.png', '組立賃率・自動機賃率', 14, 0) . "?id=$uniq" ?>'>
                            <div>&nbsp;</div>
                        </td>
                    </form>
                </tr>
                
                <tr>
                    <form method='post' action='<?php echo $menu->out_action('サービス割合メニュー')?>'>
                        <td align='center'>
                            <input type='image' alt='サービス割合の入力・照会・配賦 処理メニュー' border=0 src='<?php echo menu_bar('menu_tmp/menu_item_service_menu.png', 'サービス割合メニュー', 14, 0) . "?id=$uniq" ?>'>
                            <div>&nbsp;</div>
                        </td>
                    </form>
                </tr>
                
                <tr>
                    <form method='post' action='<?php echo $menu->out_self()?>'>
                        <td align='center'>
                            <input type='image' alt='作業応援月報の入力メニューを作成中です' border=0 src='<?php echo menu_bar('menu_tmp/menu_item_logout.png', '作業応援月報の入力', 14, 0) . "?id=$uniq" ?>'>
                            <!-- <input type='image' value='空のアイテム' border=0 src='./img/menu_item.gif'> -->
                            <div>&nbsp;</div>
                        </td>
                    </form>
                </tr>
                
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
