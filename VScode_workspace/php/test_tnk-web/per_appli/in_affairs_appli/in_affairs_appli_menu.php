<?php
//////////////////////////////////////////////////////////////////////////////
// 届出・申請書メニュー（社内 総務に関する届出）                            //
// Copyright (C) 2014-2021 Norihisa.Ohya norihisa_ooya@nitto-kohki.co.jp    //
// Changed history                                                          //
// 2014/09/29 Created  in_affairs_appli.php                                 //
// 2018/03/08 来客予定表を改4へ変更                                    大谷 //
// 2019/05/30 貸与申請書を19年版へ変更                                 大谷 //
// 2021/11/24 マイカーの業務使用の申請書を追加                         大谷 //
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
$menu->set_title('届出・申請書メニュー（社内 総務）');
//////////// 表題の設定
$menu->set_caption('届出・申請書メニュー（社内 総務）');

?>
<!DOCTYPE html>
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
            ●慶弔に関する届出
            </td>
            <td class='layoutb' id='start'>
                <a href="download_file.php/社内用弔慰関係届出書用紙.doc">弔慰関係届出書（社内）</a>
                <!--
                <a href="download_file.php/131004総合届（改訂2）.xls">総合届</a>
                -->
            </td>
            <td class='layout' id='start'>
            　
            </td>
            <td class='layout' id='start'>
            　
            </td>
        </tr>
        <tr class='layout'>
            <td class='layoutb' id='start'>
                <a href="download_file.php/慶事関係届出書　原紙.xls">慶事関係届出書（社内）</a>
                <!--
                <a href="download_file.php/病欠申告書.doc">病欠申告書</a>
                -->
            </td>
            <td class='layout' id='start'>
            　
            </td>
            <td class='layout' id='start'>
            　
            </td>
        </tr>
        <tr class='layout'>
            <td class='layoutb' id='start'>
                <a href="download_file.php/社外用弔慰関係届出書用紙.doc">弔慰関係届出書（社外）</a>
                <!--
                <a href="download_file.php/定時間外作業証明証.pdf">定時間外作業証明書</a>
                -->
            </td>
            <td class='layout' id='start'>
            　
            </td>
            <td class='layout' id='start'>
            　
            </td>
        </tr>
        <tr class='layout'>
            <td class='layoutb' id='start'>
                慶事関係届出書（社外）
                <BR>
                <a href="download_file.php/慶事関係届出書(社外用記念式典)原紙.xls">社外用記念式典</a>
                <BR>
                <a href="download_file.php/慶事関係届出書(社外用結婚)原紙.xls">社外用結婚</a>
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
                <a href="download_file.php/祝電依頼書.doc">祝電依頼書</a>
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
                <a href="download_file.php/お見舞関係届出書原紙.xls">お見舞関係届出書</a>
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
            <td class='layoutg' id='start' rowspan='2'>
            ●作業服・制服に関する届出
            </td>
            <td class='layoutb' id='start'>
                <a href="download_file.php/貸与申請書原紙19年改訂.xls">貸与申請書
                <BR>
                （作業服・安全靴・女子制服）</a>
                <!--
                <a href="download_file.php/131004総合届（改訂2）.xls">総合届</a>
                -->
            </td>
            <td class='layout' id='start'>
            　
            </td>
            <td class='layout' id='start'>
            <B><font color='red'>'19年5月更新</font></B>
            </td>
        </tr>
        <tr class='layout'>
            <td class='layoutb' id='start'>
                <a href="download_file.php/作業服通勤申請書.doc">作業服通勤申請書</a>
                <!--
                <a href="download_file.php/病欠申告書.doc">病欠申告書</a>
                -->
            </td>
            <td class='layout' id='start'>
            　
            </td>
            <td class='layout' id='start'>
            　
            </td>
        </tr>
        <tr class='layout'>
            <td class='layoutg' id='start' rowspan='1'>
            ●営繕に関する届出
            </td>
            <td class='layoutb' id='start'>
                <a href="download_file.php/営繕依頼書-原紙.xls">営繕依頼書</a>
                <!--
                <a href="download_file.php/131004総合届（改訂2）.xls">総合届</a>
                -->
            </td>
            <td class='layout' id='start'>
            　
            </td>
            <td class='layout' id='start'>
            　
            </td>
        </tr>
        <tr class='layout'>
            <td class='layoutg' id='start' rowspan='3'>
            ●重要文書に関する届出
            </td>
            <td class='layoutb' id='start'>
                重要文書保管依頼書
                <!--
                <a href="download_file.php/131004総合届（改訂2）.xls">総合届</a>
                -->
            </td>
            <td class='layout' id='start'>
            　
            </td>
            <td class='layout' id='start'>
            　
            </td>
        </tr>
        <tr class='layout'>
            <td class='layoutb' id='start'>
                <a href="download_file.php/印章押捺申請書（様式－１）.doc">印章押捺申請書</a>
                <!--
                <a href="download_file.php/病欠申告書.doc">病欠申告書</a>
                -->
            </td>
            <td class='layout' id='start'>
            　
            </td>
            <td class='layout' id='start'>
            ※社長名の場合は一般印、会社名のみの場合は角印
            </td>
        </tr>
        <tr class='layout'>
            <td class='layoutb' id='start'>
                決裁権限委譲申請書
                <!--
                <a href="download_file.php/病欠申告書.doc">病欠申告書</a>
                -->
            </td>
            <td class='layout' id='start'>
            　
            </td>
            <td class='layout' id='start'>
            　
            </td>
        </tr>
        <tr class='layout'>
            <td class='layoutg' id='start' rowspan='4'>
            ●自動車・バイク・自転車に<BR>　関する届出
            </td>
            <td class='layoutb' id='start'>
                <a href="download_file.php/外出許可証.xls">公用・私用外出許可証</a>
                <!--
                <a href="download_file.php/131004総合届（改訂2）.xls">総合届</a>
                -->
            </td>
            <td class='layout' id='start'>
            　
            </td>
            <td class='layout' id='start'>
            　
            </td>
        </tr>
        <tr class='layout'>
            <td class='layoutb' id='start'>
                <a href="download_file.php/社用車私用借用願い.doc">社用車私用借用願</a>
                <!--
                <a href="download_file.php/病欠申告書.doc">病欠申告書</a>
                -->
            </td>
            <td class='layout' id='start'>
            　
            </td>
            <td class='layout' id='start'>
            　
            </td>
        </tr>
        <tr class='layout'>
            <td class='layoutb' id='start'>
                <a href="download_file.php/公用時間外車用車使用願.doc">公用時間外社用車使用願</a>
                <!--
                <a href="download_file.php/病欠申告書.doc">病欠申告書</a>
                -->
            </td>
            <td class='layout' id='start'>
            　
            </td>
            <td class='layout' id='start'>
            　
            </td>
        </tr>
        <tr class='layout'>
            <td class='layoutb' id='start'>
                <a href="download_file.php/マイカーの業務使用の申請書.doc">マイカーの業務使用の申請書</a>
            </td>
            <td class='layout' id='start'>
            　
            </td>
            <td class='layout' id='start'>
            　
            </td>
        </tr>
        <tr class='layout'>
            <td class='layoutg' id='start' rowspan='1'>
            ●来客に関する届出
            </td>
            <td class='layoutb' id='start'>
                <a href="download_file.php/来客予定連絡票　原紙改4.xls">来客予定連絡票</a>
                <!--
                <a href="download_file.php/131004総合届（改訂2）.xls">総合届</a>
                -->
            </td>
            <td class='layout' id='start'>
            　
            </td>
            <td class='layout' id='start'>
            <B><font color='red'>'18年3月更新</font></B>
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
