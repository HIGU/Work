<?php
//////////////////////////////////////////////////////////////////////////////
// 届出・申請書メニュー（扶養者の増加に関する届出）                         //
// Copyright (C) 2014-2014 Norihisa.Ohya norihisa_ooya@nitto-kohki.co.jp    //
// Changed history                                                          //
// 2014/09/19 Created  support_inc_appli_menu.php                           //
// 2014/09/22 使用方法の表示を追加                                          //
//////////////////////////////////////////////////////////////////////////////
ini_set('error_reporting', E_ALL);      // E_ALL='2047' debug 用
// ini_set('display_errors', '1');         // Error 表示 ON debug 用 リリース後コメント
session_start();                        // ini_set()の次に指定すること Script 最上行
ob_start('ob_gzhandler');               // 出力バッファをgzip圧縮

require_once ('../../function.php');       // TNK 全共通 function
require_once ('../../MenuHeader.php');     // TNK 全共通 menu class
require_once ('../../tnk_func.php');
access_log();                           // Script Name は自動取得

///// TNK 共用メニュークラスのインスタンスを作成
$menu = new MenuHeader(0);    // 認証レベル=0, リターンアドレス, タイトルの指定なし

////////////// サイト設定
$menu->set_site(97, 999);                // site_index=4(プログラム開発) site_id=999(子メニューあり)
////////////// リターンアドレス設定
// $menu->set_RetUrl(TOP_MENU);            // 上で設定している
//////////// タイトル名(ソースのタイトル名とフォームのタイトル名)
$menu->set_title('届出・申請書メニュー（人事）');
//////////// 表題の設定
$menu->set_caption('届出・申請書メニュー（人事）');

?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=EUC-JP">
<meta http-equiv="Content-Style-Type" content="text/css">
<meta http-equiv="Content-Script-Type" content="text/javascript">
<title><?= $menu->out_title() ?></title>
<?= $menu->out_site_java() ?>
<?= $menu->out_css() ?>
<script type='text/javascript' language='JavaScript' src='../per_appli.js'></script>
<link rel='stylesheet' href='../per_appli.css' type='text/css' media='screen'>
</head>
<body onLoad='Regu.set_focus(document.getElementById("start", ""))'>
    <center>
<?= $menu->out_title_border() ?>
    <div class='pt12b'>&nbsp;</div>
    <B>
    　　　　　　　　　　　　　　　　　　　　　　　　　　　　　　　　　　　　　　　　　　　　　
    </B>
    <table class='layout'>
        <tr class='layout'>
            <td class='layoutt' id='start' colspan='2'>
            <B>●扶養者の増加に関する届出（結婚・出産以外の場合）</B>
            </td>
        </tr>
    </table>
     <B>
    　　　　　　　　　　　　　　　　　　　　　　　　　　　　　　　　　　　　　　　　　　　　　
    </B>
    <BR><B>
    ・各種提出書類をクリックすると、『開く』・『保存』のメニューが表示されますので<BR>
      パソコンに保管し、使用して下さい。
    </B><BR>
     <B>
    　　　　　　　　　　　　　　　　　　　　　　　　　　　　　　　　　　　　　　　　　　　　　
    </B>
    <table class='layout'>
        <tr class='layout'>
            <td class='layoutg' id='start'>
            書類No.
            </td>
            <td class='layoutg' id='start'>
            本人提出書類
            </td>
            <td class='layoutg' id='start'>
            備考
            </td>
            <td class='layoutg' id='start'>
            総務課 記入書類、注意事項
            </td>
        </tr>
        <tr class='layout'>
            <td class='layoutg' id='start'>
            0701
            </td>
            <td class='layoutb' id='start'>
                提出書類一覧表
                <!-- <a href="download_file.php/131004総合届（改訂2）.xls">提出書類一覧表</a> -->
            </td>
            <td class='layouty' id='start'>
            <font color='red'><B>はじめにお読み下さい</B></font>
            </td>
            <td class='layout' id='start'>
            　
            </td>
        </tr>
        <tr class='layout'>
            <td class='layoutg' id='start'>
            0702
            </td>
            <td class='layoutb' id='start'>
                人事情報変更データシート
                <!-- <a href="download_file.php/病欠申告書.doc">人事情報変更データシート</a> -->
            </td>
            <td class='layout' id='start'>
            全員提出
            </td>
            <td class='layout' id='start'>
            　
            </td>
        </tr>
        <tr class='layout'>
            <td class='layoutg' id='start'>
            0703
            </td>
            <td class='layoutb' id='start'>
                給与所得者の扶養控除等（異動）
                <BR>
                申告書
                <!-- <a href="download_file.php/定時間外作業証明証.pdf">給与所得者の扶養控除等（異動）<BR>申告書</a> -->
            </td>
            <td class='layout' id='start'>
            全員提出
            </td>
            <td class='layout' id='start'>
            　
            </td>
        </tr>
        <tr class='layout'>
            <td class='layoutg' id='start'>
            0704
            </td>
            <td class='layoutb' id='start'>
                健康保険被扶養者（異動）届
                <!-- <a href="download_file.php/定時間外作業証明証.pdf">健康保険被扶養者（異動）届</a> -->
            </td>
            <td class='layout' id='start'>
            扶養する場合のみ提出
            </td>
            <td class='layout' id='start'>
            用紙は、２枚複写の専用用紙
            <BR>
            です。総務課に申出て下さい。
            </td>
        </tr>
        <tr class='layout'>
            <td class='layoutg' id='start'>
            0705
            </td>
            <td class='layoutb' id='start'>
                国民年金第３号被保険者資格取得届
                <!-- <a href="download_file.php/外出許可証.xls">国民年金第３号被保険者資格取得届</a> -->
                <BR>
                ※配偶者の年金手帳
                <BR>
                　（基礎年金番号通知書）の写し
            </td>
            <td class='layout' id='start'>
            配偶者を扶養する場合のみ提出
            </td>
            <td class='layout' id='start'>
            　
            </td>
        </tr>
        <tr class='layout'>
            <td class='layoutg' id='start'>
            0706
            </td>
            <td class='layoutb' id='start'>
                雇用保険費保険者離職票−１、−２
                <!-- <a href="download_file.php/外出許可証.xls">雇用保険費保険者離職票−１、−２</a> -->
            </td>
            <td class='layout' id='start'>
            雇用保険の加入者だった方を
            <BR>
            扶養する場合のみ提出
            </td>
            <td class='layout' id='start'>
            　
            </td>
        </tr>
        <tr class='layout'>
            <td class='layoutg' id='start'>
            0707
            </td>
            <td class='layoutb' id='start'>
                ※所得を証明するもの
                <!-- <a href="download_file.php/外出許可証.xls">※所得を証明するもの</a> -->
            </td>
            <td class='layout' id='start'>
            源泉徴収票または非課税証明書
            <BR>
            年金受給者は「年金受給通知書」
            <BR>
            の写し
            </td>
            <td class='layout' id='start'>
            　
            </td>
        </tr>
    </table>
    <font color='red'><B>※印は添付書類になるので、申請の際に写しを添付して下さい。</font></B>
    </center>
</body>
<?=$menu->out_alert_java()?>
</html>
<?php
ob_end_flush();                 // 出力バッファをgzip圧縮 END
?>
