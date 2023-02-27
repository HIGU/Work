<?php
//////////////////////////////////////////////////////////////////////////////
// 届出・申請書メニュー（社内 経理に関する届出）                            //
// Copyright (C) 2014-2017 Norihisa.Ohya norihisa_ooya@nitto-kohki.co.jp    //
// Changed history                                                          //
// 2014/09/29 Created  in_account_appli.php                                 //
// 2015/02/06 一部の申請書を追加                                            //
// 2015/02/12 一部の申請書を追加                                            //
// 2015/02/17 買掛・未払の書類を分割                                        //
// 2016/04/22 回数券使用申込書を改定                                        //
// 2017/02/10 買掛購入先通知書の書式を変更                                  //
// 2022/05/27 出張報告書の書式を変更                                  //
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
$menu->set_title('届出・申請書メニュー（社内 経理）');
//////////// 表題の設定
$menu->set_caption('届出・申請書メニュー（社内 経理）');

?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
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
    <BR><B>
    ・各種提出書類をクリックすると、『開く』・『保存』のメニューが表示されますので<BR>
      パソコンに保管し、使用して下さい。
    </B><BR>
     <B>
    　　　　　　　　　　　　　　　　　　　　　　　　　　　　　　　　　　　　　　　　　　　　　
    </B>
    <table class='layout'>
        <tr class='layout'>
            <td class='layoutg' id='start' align='center'>
            項目
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
            <td class='layoutg' id='start' rowspan='6'>
            ●出張に関する届出
            </td>
            <td class='layoutb' id='start'>
                <a href="download_file.php/出張報告書.xlsx">出張報告書</a>
                <!--
                <a href="download_file.php/131004総合届（改訂2）.xls">総合届</a>
                -->
            </td>
            <td class='layout' id='start'>
            　
            </td>
            <td class='layout' id='start'>
            <B><font color='red'>'22/05更新</font></B>
            </td>
        </tr>
        <tr class='layout'>
            <td class='layoutb' id='start'>
                <a href="download_file.php/海外出張報告書.xls">海外出張報告書（円換算）</a>
                <!--
                <a href="download_file.php/病欠申告書.doc">病欠申告書</a>
                -->
            </td>
            <td class='layout' id='start'>
            出発日のレートは総務課に確認すること。
            </td>
            <td class='layout' id='start'>
            　
            </td>
        </tr>
        <tr class='layout'>
            <td class='layoutb' id='start'>
                <a href="download_file.php/回数券（改訂）.xls">回数券使用申込書</a>
                <!--
                <a href="download_file.php/定時間外作業証明証.pdf">定時間外作業証明書</a>
                -->
            </td>
            <td class='layout' id='start'>
            　
            </td>
            <td class='layout' id='start'>
            <B><font color='red'>'16/04更新</font></B>回数券使用申込書
            </td>
        </tr>
        <tr class='layout'>
            <td class='layoutb' id='start'>
                <a href="download_file.php/切符購入依頼書(原).xls">切符購入依頼書（ＪＲ）</a>
                <!--
                <a href="download_file.php/外出許可証.xls">公用・私用外出許可証</a>
                -->
                <!--
                <a href="../per_appli_download_file.php?folder=service_appli&file=外出許可証.xls">公用・私用外出許可証</a>
                -->
            </td>
            <td class='layout' id='start'>
            　
            </td>
            <td class='layout' id='start'>
            　
            </td>
        </tr>
        <tr class='layout'>
            <td class='layoutb' id='start'>
                <a href="download_file.php/航空券購入依頼書.pdf">航空券購入依頼書</a>
                <!--
                <a href="download_file.php/外出許可証.xls">公用・私用外出許可証</a>
                -->
                <!--
                <a href="../per_appli_download_file.php?folder=service_appli&file=外出許可証.xls">公用・私用外出許可証</a>
                -->
            </td>
            <td class='layout' id='start'>
            　
            </td>
            <td class='layout' id='start'>
            　
            </td>
        </tr>
        <tr class='layout'>
            <td class='layoutb' id='start'>
                <a href="download_file.php/新-交際費経費申請兼精算書-12.06.xls">交際費経費精算書</a>
                <!--
                <a href="../per_appli_download_file.php?folder=service_appli&file=外出許可証.xls">公用・私用外出許可証</a>
                -->
            </td>
            <td class='layout' id='start'>
            　
            </td>
            <td class='layout' id='start'>
            　
            </td>
        </tr>
        <tr class='layout'>
            <td class='layoutg' id='start' rowspan='2'>
            ●支払変更に関する届出
            </td>
            <td class='layoutb' id='start'>
                <a href="download_file.php/支払依頼書（未払伝票）.xls">支払依頼書（未払伝票関係）</a>
            </td>
            <td class='layout' id='start'>
            　
            </td>
            <td class='layout' id='start'>
            　
            </td>
        </tr>
        <tr class='layout'>
            <td class='layoutb' id='start'>
                <a href="download_file.php/支払変更依頼書.xls">支払変更依頼書</a>
                <!--
                <a href="../per_appli_download_file.php?folder=service_appli&file=外出許可証.xls">公用・私用外出許可証</a>
                -->
            </td>
            <td class='layout' id='start'>
            　
            </td>
            <td class='layout' id='start'>
            　
            </td>
        </tr>
        <tr class='layout'>
            <td class='layoutg' id='start' rowspan='3'>
            ●取引先登録に関する届出
            </td>
            <td class='layoutb' id='start'>
                <a href="download_file.php/支払変更-未払.xls">未払伝票購入先通知書</a>
            </td>
            <td class='layout' id='start'>
            　
            </td>
            <td class='layout' id='start'>
            　
            </td>
        </tr>
        <tr class='layout'>
            <td class='layoutb' id='start'>
                <a href="download_file.php/買掛購入先通知書_20160713.xls">買掛購入先通知書</a>
                <!--
                <a href="../per_appli_download_file.php?folder=service_appli&file=外出許可証.xls">公用・私用外出許可証</a>
                -->
            </td>
            <td class='layout' id='start'>
            　
            </td>
            <td class='layout' id='start'>
            　
            </td>
        </tr>
        <tr class='layout'>
            <td class='layoutb' id='start'>
                <a href="download_file.php/銀行振込依頼書.doc">銀行口座振込依頼書</a>
                <!--
                <a href="../per_appli_download_file.php?folder=service_appli&file=外出許可証.xls">公用・私用外出許可証</a>
                -->
            </td>
            <td class='layout' id='start'>
            　
            </td>
            <td class='layout' id='start'>
            　
            </td>
        </tr>
    </table>
    </center>
</body>
<?=$menu->out_alert_java()?>
</html>
<?php
ob_end_flush();                 // 出力バッファをgzip圧縮 END
?>
