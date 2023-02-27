<?php
//////////////////////////////////////////////////////////////////////////////
// 組立設備稼働管理システムの機械運転日報 メインメニュー                    //
// Copyright (C) 2021-2021 norihisa_ooya@nitto-kohki.co.jp                  //
// Original by yamagishi@matehan.co.jp                                      //
// Changed history                                                          //
// 2021/03/26 Created  EquipMenu.php                                        //
//////////////////////////////////////////////////////////////////////////////
ini_set('error_reporting', E_ALL);          // E_ALL='2047' debug 用
require_once ('../../function.php');        // TNK 全共通 function
require_once ('../../MenuHeader.php');      // TNK 全共通 menu class
require_once ('com/define.php');
require_once ('com/function.php');

///// TNK 共用メニュークラスのインスタンスを作成
$menu = new MenuHeader(0, EQUIP_MENU3);     // 認証レベル=0, リターンアドレス, タイトルの指定なし
access_log();                               // Script Name は自動取得

// 機械運転日報の管理者権限ユーザ
$AccountAdmin = AdminUser( FNC_ACCOUNT );

////////////// サイト設定
$menu->set_site(40, 7);                // site_index=40(設備メニュー2) site_id=7(機械運転日報)
////////////// リターンアドレス設定 (インスタンス生成時に指定しなければここで設定)
// $menu->set_RetUrl(EQUIP_MENU2);
//////////// 呼出先のaction名とアドレス設定
$menu->set_action('機械運転日報サーチ',   EQUIP2 . 'daily_report_moni/business/ReportMain.php');
//$menu->set_action('端材一覧表',   EQUIP2 . 'daily_report_moni/business/Abandonment.php');
//$menu->set_action('部品マスター',   EQUIP2 . 'daily_report_moni/master/Parts.php');
//$menu->set_action('材料マスター',   EQUIP2 . 'daily_report_moni/master/Materials.php');
$menu->set_action('権限マスター',   EQUIP2 . 'daily_report_moni/master/Account/Account.php');

//////////// タイトル名(ソースのタイトル名とフォームのタイトル名)
//////////// 表題の設定
if (isset($_SESSION['factory'])) $factory = $_SESSION['factory']; else $factory = '';
switch ($factory) {
case 1:
    $menu->set_title('機械 運転 日報 管理システム １工場');
    $menu->set_caption('運転日報 １工場 メイン メニュー');
    break;
case 2:
    $menu->set_title('機械 運転 日報 管理システム ２工場');
    $menu->set_caption('運転日報 ２工場 メイン メニュー');
    break;
case 3:
    $menu->set_title('機械 運転 日報 管理システム ３工場');
    $menu->set_caption('運転日報 ３工場 メイン メニュー');
    break;
case 4:
    $menu->set_title('機械 運転 日報 管理システム ４工場');
    $menu->set_caption('運転日報 ４工場 メイン メニュー');
    break;
case 5:
    $menu->set_title('機械 運転 日報 管理システム ５工場');
    $menu->set_caption('運転日報 ５工場 メイン メニュー');
    break;
case 6:
    $menu->set_title('機械 運転 日報 管理システム ６工場');
    $menu->set_caption('運転日報 ６工場 メイン メニュー');
    break;
case 7:
    $menu->set_title('機械 運転 日報 管理システム ７工場(真鍮)');
    $menu->set_caption('運転日報 ７工場(真鍮) メイン メニュー');
    break;
case 8:
    $menu->set_title('機械 運転 日報 管理システム ７工場(SUS)');
    $menu->set_caption('運転日報 ７工場(SUS) メイン メニュー');
    break;
default:
    $menu->set_title('機械 運転 日報 管理システム 全工場');
    $menu->set_caption('運転日報 全工場 メイン メニュー');
    break;
}

/////////// HTML Header を出力してキャッシュを制御
// $menu->out_html_header();    // 以下で行っているためコメント
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
<meta http-equiv="Content-Language" content="ja">
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<meta http-equiv="Content-Style-Type" content="text/css">
<meta http-equiv="pragma" content="no-cache">
<meta http-equiv="cache-control" content="no-cache">
<meta http-equiv="Expires" content="0">
<title><?= $menu->out_title() ?></title>
<script Language='JavaScript'>
<!--
function doSubmit(url) {
    document.MainForm.action = url;
    document.MainForm.submit();
}
function win_open(url) {
    var w = 800;
    var h = 600;
    var left = (screen.availWidth  - w) / 2;
    var top  = (screen.availHeight - h) / 2;
    window.open(url, 'help_win', 'width='+w+',height='+h+',resizable=yes,scrollbars=yes,status=no,toolbar=no,location=no,menubar=no,top='+top+',left='+left);
}
/* 初期入力フォームのエレメントにフォーカスさせる */
function set_focus() {
    // document.body.focus();   // F2/F12キーを有効化する対応
    document.mhForm.backwardStack.focus();  // 上記はIEのみのためNN対応
}
// -->
</script>
<?= $menu->out_site_java() ?>
<?= $menu->out_css() ?>
<style>
.TITLE {
        font-size : 24px;
        background-color : blue;
        color : white;
        text-align: center;
        width : 100%;
}
</style>
</head>
<body onLoad='set_focus()' link='#0000FF' vlink='#0000FF' style='overflow-y:hidden;'>
<form name='MainForm' method='post' action=''>
<input type='hidden' name='RetUrl' value='<?=$_SERVER{'PHP_SELF'}?>'>
</form>
<!--
<div class='TITLE'><?= $menu->out_title() ?></div>
<br>
-->
<center>
<?= $menu->out_title_border() ?>
        <table border='0'>
            <tr>
                <td align='center' class='caption_font'>
                    <?= $menu->out_caption() . "\n" ?>
                </td>
            </tr>
        </table>
    
    <table border='0' cellpadding='30'>
        <tr>
            <td align='center' valign='top'>
                <table border='1' bordercolor='#0000FF' bgcolor='#CCFFFF' cellpadding='10' cellspacing=='0' width='200'>
                    <tr>
                        <td>
                            機械運転日報管理<br>
                            <br>
                            <br>
                            <a href="JavaScript:doSubmit('<?=BUSINESS_PATH?>Report.php')">機械運転日報<br>
                            <br>
<!--
                            <input style='font-size:10pt; font-weight:bold; color:blue;' type='button' name='work_mnt_help' value='HELP' onClick='win_open("help/EquipMenu_help.html")'>
-->
                        </td>
                     </tr>
                 </table>
             </td>
            <td align='center' valign='top'>
                <table border='1' bordercolor='#FF00FF' bgcolor='#FFCCFF' cellpadding='10' cellspacing=='0' width='200'>
                    <tr>
                        <td>
                            マスター管理<br>
                            <br>
                            <br>
                            <?php if ($AccountAdmin) { ?>
                            <a href="JavaScript:doSubmit('<?=$menu->out_action('権限マスター')?>')">権限マスタ<br>
                            <?php } ?>
                            <br>
                        </td>
                     </tr>
                 </table>
             </td>
         <tr>
    </table>
</center>
</body>
</html>
