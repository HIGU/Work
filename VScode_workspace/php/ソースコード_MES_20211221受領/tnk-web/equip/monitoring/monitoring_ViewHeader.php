<?php
////////////////////////////////////////////////////////////////////////////////
// 機械稼働管理指示メンテナンス                                               //
//                                             MVC View 部 リスト表示(Header) //
// Copyright (C) 2021-2021 Ryota.waki ryota_waki@nitto-kohki.co.jp            //
// Changed history                                                            //
// 2021/03/24 Created monitoring_ViewHeader.php                               //
// 2021/03/24 Release.                                                        //
////////////////////////////////////////////////////////////////////////////////
ini_set('error_reporting', E_ALL);              // E_ALL='2047' debug 用
// ini_set('display_errors', '1');                 // Error 表示 ON debug 用 リリース後コメント
ob_start('ob_gzhandler');                       // 出力バッファをgzip圧縮
session_start();                                // ini_set()の次に指定すること Script 最上行
require_once ('../../MenuHeader.php');          // TNK 全共通 menu class
require_once ('../../function.php');            // TNK 全共通 function
require_once ('../EquipControllerHTTP.php');    // TNK 全共通 MVC Controller Class
access_log();                                   // Script Name は自動取得

///// TNK 共用メニュークラスのインスタンスを作成
$menu = new MenuHeader();   // 認証チェック0=一般以上 戻り先=セッションより タイトル未設定

///// 設備専用セッションクラスのインスタンスを作成
$equipSession = new equipSession();

$request = new Request();

$menu->set_target('_parent');   // フレーム版の戻り先はtarget属性が必須

$menu->set_title('機械稼働管理 指示メンテナンス ６工場');

//$menu->set_caption("作業区分を選択して下さい <input type='button' value='HELP'>");
$menu->set_caption("作業区分を選択して下さい。");

if (isset($_REQUEST['selectMode'])) {
    $s_mode = $_REQUEST['selectMode'];
} else {
    $s_mode = 'start';
}

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
<?php echo $menu->out_jsBaseClass() ?>

<link rel='stylesheet' href='monitoring.css' type='text/css' media='screen'>
<script type='text/javascript' language='JavaScript' src='monitoring.js'></script>

</head>

<center>
    <?= $menu->out_title_border() ?>

    <table class='pt12b' border="1" cellspacing="0">
    <tr><td> <!----------- ダミー(デザイン用) ------------>
        <table width='100%' class='pt12' bgcolor='#d6d3ce' align='center' border='1' cellspacing='0' cellpadding='3'>
            <tr>
                    <!--  bgcolor='#ffffc6' 薄い黄色 --> 
                <td class='winbox' style='background-color:yellow; color:blue;' colspan='3' align='center'>
                    <div class='caption_font'><?php echo $menu->out_caption(), "\n"?></div>
                </td>
            </tr>

    <form name="header_form" method="post" target="List" action='monitoring_ViewList.php' onSubmit='return true;'>
        <input type='hidden' name='select_mode' id='id_select_mode'>
        <input type='hidden' name='state' id='id_state' value='init'>

        <tr>
            <td nowrap align='center'>
                <input type='radio' name='h_radio' id='id_h_start' value='start' onClick='setSelectMode(this);' <?php if($s_mode=='start') echo ' checked'?>><label for='id_h_start'>運転開始
            </td>
            <td nowrap align='center'>
                <input type='radio' name='h_radio' id='id_h_break' value='break' onClick='setSelectMode(this);' <?php if($s_mode=='break') echo ' checked'?>><label for='id_h_break'>中断計画
            </td>
            <td nowrap align='center'>
                <input type='radio' name='h_radio' id='id_h_change' value='change' onClick='setSelectMode(this);' <?php if($s_mode=='change') echo ' checked'?>><label for='id_h_change'>指示変更
            </td>
        </tr>

        <tr>
            <td nowrap align='center'>
                <label for='id_h_start'>(データ入力)
            </td>
            <td nowrap align='center'>
                <label for='id_h_break'>(データ削除)
            </td>
            <td nowrap align='center'>
                <label for='id_h_change'>(データ変更)
            </td>
        </tr>
    </form>

        </table>
    </td></tr>
    </table> <!----------------- ダミーEnd --------------------->
</center>
</body>
<?php echo $menu->out_alert_java()?>
</html>
