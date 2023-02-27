<?php
//////////////////////////////////////////////////////////////////////////////
// EquipGraph Class内でのエラー処理ページ status によって条件分岐           //
// Copyright(C) 2005-2005 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp       //
// Changed History                                                          //
// 2005/07/12 Created   ErrorComTableMntClass.php                           //
//////////////////////////////////////////////////////////////////////////////
ini_set('error_reporting', E_ALL);      // E_ALL='2047' debug 用
// ini_set('display_errors', '1');         // Error 表示 ON debug 用 リリース後コメント
session_start();                        // ini_set()の次に指定すること Script 最上行
require_once ('../function.php');       // TNK 全共通 function
require_once ('../MenuHeader.php');     // TNK 全共通 menu class
access_log();                           // Script Name は自動取得

///// TNK 共用メニュークラスのインスタンスを作成
$menu = new MenuHeader(0, TOP_MENU);    // 認証チェック1=一般以上 戻り先=TOP_MENU タイトル未設定

////////////// サイト設定
$menu->set_site(0, 0);                  // site_index=0(未設定) site_id=0(未設定)
////////////// リターンアドレス設定
// $menu->set_RetUrl(TOP_MENU);
//////////// タイトル名(ソースのタイトル名とフォームのタイトル名)
$menu->set_title('ComTableMntClassのClass 内部エラー');

$status = $_REQUEST['status'];
//////////// 表題の設定
switch ($status) {
case 1:
    $menu->set_caption("ログファイルをオープンできません！");
    break;
case 2:
    $menu->set_caption($_SESSION['s_sysmsg']);
    break;
case 3:
case 4:
default:
    $menu->set_caption('ComTableMntClass 内で その他のエラーがありました。<br>管理担当者へ連絡して下さい。');
}
/////////// HTML Header を出力してキャッシュを制御
$menu->out_html_header();
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<meta http-equiv="Content-Style-Type" content="text/css">
<title><?= $menu->out_title() ?></title>
<?= $menu->out_css() ?>
<style type='text/css'>
<!--
.pt12b {
    font-size:      12pt;
    font-weight:    bold;
}
.pt10 {
    font-size:      10pt;
}
-->
</style>
</head>
<body>
    <center>
<?= $menu->out_title_border() ?>
        <table align='center' width ='80%' height='30%' border='0'>
            <tr>
                <td valign='middle' align='center' class='caption_font'>
                    <hr>
                    <?= $menu->out_caption(), "\n" ?>
                    <hr>
                    <form action='<?= $menu->out_RetUrl() ?>' method='post' name='error_ret_form'>
                        <input type='submit' name='error_ret' value='戻る' class='ret_font'>
                    </form>
                </td>
            </tr>
        </table>
    </center>
</body>
<?=$menu->out_alert_java()?>
</html>
