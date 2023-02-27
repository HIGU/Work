<?php
//////////////////////////////////////////////////////////////////////////////
// 届出・申請書メニュー（社内 人事に関する届出）                            //
// Copyright (C) 2014-2020 Norihisa.Ohya norihisa_ooya@nitto-kohki.co.jp    //
// Changed history                                                          //
// 2014/09/29 Created  in_personnel_appli_menu.php                          //
// 2015/02/05 各種原紙を追加                                                //
// 2015/02/17 一部のリンク切れを訂正                                        //
// 2015/02/26 総合届の記入例を追加した。                                    //
// 2015/03/05 マイカー通勤手当（変更）届、パート通勤状況届の改定            //
// 2015/04/09 回数券使用申込書を追加（総合届とのセットも）                  //
// 2015/06/17 マイカー通勤手当（変更）届、パート通勤状況届の改定            //
// 2016/01/13 マイカー通勤手当（変更）届、パート通勤状況届の改定(16/01)     //
//            給与所得者の〜申告書を28年度へ更新                            //
// 2016/02/10 マイカー通勤手当（変更）届、パート通勤状況届の改定(16/02)     //
// 2016/04/01 総合届、記入例、総合届・回数券セットを改定（16/04）           //
// 2016/04/05 人事情報変更データシートをPDFに変更(B4→A4に縮小済み)         //
// 2016/04/22 回数券使用申込書を改定                                        //
// 2017/05/09 総合届を17.5.9版に改定                                        //
// 2017/06/05 マイカー通勤手当（変更）届、パート通勤状況届の改定(17/06)     //
// 2017/08/10 総合届・回数券セットも17.5.9版に(総合届の別シートにあり)      //
// 2018/07/09 マイカー通勤手当（変更）届、パート通勤状況届の改定(18/06)     //
// 2018/10/17 マイカー通勤手当（変更）届、パート通勤状況届の改定(18/10)     //
// 2018/11/14 マイカー通勤手当（変更）届、パート通勤状況届の改定(18/11)     //
// 2019/04/11 マイカー通勤手当（変更）届、パート通勤状況届の改定(19/04)     //
// 2019/05/13 マイカー通勤手当（変更）届、パート通勤状況届の改定(19/05)     //
// 2019/07/22 マイカー通勤手当（変更）届、パート通勤状況届の改定(19/07)     //
// 2019/09/06 マイカー通勤手当（変更）届、パート通勤状況届の改定(19/09) 和氣//
// 2019/10/07 マイカー通勤手当（変更）届、パート通勤状況届の改定(19/10) 和氣//
// 2020/01/10 マイカー通勤手当（変更）届、パート通勤状況届の改定(20/01) 大谷//
// 2020/03/04 マイカー通勤手当（変更）届、パート通勤状況届の改定(20/03) 和氣//
// 2020/04/06 マイカー通勤手当（変更）届、パート通勤状況届の改定(20/04) 和氣//
// 2020/07/03 マイカー通勤手当（変更）届、パート通勤状況届の改定(20/07) 和氣//
// 2020/09/04 マイカー通勤手当（変更）届、パート通勤状況届の改定(20/09) 和氣//
// 2021/01/08 マイカー通勤手当（変更）届、パート通勤状況届の改定(21/01) 和氣//
// 2021/02/04 マイカー通勤手当（変更）届、パート通勤状況届の改定(21/02) 和氣//
// 2021/03/08 マイカー通勤手当（変更）届、パート通勤状況届の改定(21/03) 和氣//
// 2021/03/11 教育訓練変更中止申請書 原紙を追加                         和氣//
// 2021/03/24 公的資格取得 受験申請書 兼 結果報告及び奨励金申請書(変更) 和氣//
// 2021/04/08 マイカー通勤手当（変更）届、パート通勤状況届の改定(21/04) 和氣//
// 2021/07/05 マイカー通勤手当（変更）届、パート通勤状況届の改定(21/07) 和氣//
// 2021/11/04 マイカー通勤手当（変更）届、パート通勤状況届の改定(21/11) 和氣//
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
$menu->set_title('届出・申請書メニュー（社内 人事）');
//////////// 表題の設定
$menu->set_caption('届出・申請書メニュー（社内 人事）');

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
    <BR><B>
    ・各種提出書類をクリックすると、『開く』・『保存』のメニューが表示されますので<BR>
      パソコンに保管し、使用して下さい。
    </B><BR>
     <B>
    　　　　　　　　　　　　　　　　　　　　　　　　　　　　　　　　　　　　　　　　　　　　　
    <BR>
    ※印は添付書類になるので、申請の際に写しを添付して下さい。
    <BR>
    ※下記にない申請書類は、総務課までご連絡下さい。
    </B>
    <table class='layout'>
        <tr class='layout'>
            <td class='layoutg' id='start' align='center'>
            項目
            </td>
            <td class='layoutg' id='start'>
            書類<BR>No.
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
            <td class='layoutg' id='start' rowspan='5' nowrap>
            ●勤怠に関する<BR>　届出
            </td>
            <td class='layoutg' id='start'>
            0101
            </td>
            <td class='layoutb' id='start' nowrap>
                <a href="download_file.php/17.5.9総合届（改訂2）.xls">総合届</a>
            </td>
            <td class='layout' id='start'>
            　
            </td>
            <td class='layout' id='start'>
                <a href="download_file.php/20160401総合届記入例.pdf">記入例</a>を参考に記入して下さい。<BR><B><font color='red'>'17/05更新</font></B>
            </td>
        </tr>
        <tr class='layout'>
            <td class='layoutg' id='start'>
            0102
            </td>
            <td class='layoutb' id='start' nowrap>
                <a href="download_file.php/病欠申告書.doc">病欠申告書</a>
            </td>
            <td class='layout' id='start'>
            　
            </td>
            <td class='layout' id='start'>
            　
            </td>
        </tr>
        <tr class='layout'>
            <td class='layoutg' id='start'>
            0103
            </td>
            <td class='layoutb' id='start' nowrap>
                <a href="download_file.php/定時間外作業証明書.xls">定時間外作業証明書</a>
            </td>
            <td class='layout' id='start'>
            　
            </td>
            <td class='layout' id='start'>
            　
            </td>
        </tr>
        <tr class='layout'>
            <td class='layoutg' id='start'>
            0104
            </td>
            <td class='layoutb' id='start' nowrap>
                <a href="download_file.php/121018外出許可証.xls">公用・私用外出許可証</a>
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
            <td class='layoutg' id='start'>
            0105
            </td>
            <td class='layoutb' id='start' nowrap>
                <a href="download_file.php/回数券（改訂）.xls">回数券使用申込書</a>
                <!--
                <a href="../per_appli_download_file.php?folder=service_appli&file=外出許可証.xls">公用・私用外出許可証</a>
                -->
            </td>
            <td class='layout' id='start'>
                <a href="download_file.php/17.5.9総合届（改訂2）.xls">総合届・回数券セット</a>
                <!--
                <a href="../per_appli_download_file.php?folder=service_appli&file=外出許可証.xls">公用・私用外出許可証</a>
                -->
            </td>
            <td class='layout' id='start'>
            <B><font color='red'>'17/05更新</font></B>回数券使用申込書<BR>総合届・回数券セット
            </td>
        </tr>
        <tr class='layout'>
            <td class='layoutg' id='start' rowspan='4' nowrap>
            ●給与に関する<BR>　届出
            </td>
            <td class='layoutg' id='start'>
            0201
            </td>
            <td class='layoutb' id='start' nowrap>
                <!--
                <a href="download_file.php/131004総合届（改訂2）.xls">総合届</a>
                -->
                <a href="download_file.php/給与振込口座追加・変更届原紙.xls">給与振込み口座追加・変更届</a>
            </td>
            <td class='layout' id='start'>
            　
            </td>
            <td class='layout' id='start'>
            　
            </td>
        </tr>
        <tr class='layout'>
            <td class='layoutg' id='start'>
            0202
            </td>
            <td class='layoutb' id='start' nowrap>
                <!--
                <a href="download_file.php/病欠申告書.doc">病欠申告書</a>
                -->
                <a href="download_file.php/21.11@164ﾏｲｶｰ通勤手当（変更）届.xlsx">マイカー通勤手当（変更）届</a><BR>
                　　　　　　　　　（社員用）
                <BR>
                <a href="download_file.php/21.11@164パート通勤状況届.xlsx">パート通勤状況届</a><BR>
                　　　　　　　　　（パート社員用）
            </td>
            <td class='layout' id='start'>
            全員提出
            </td>
            <td class='layout' id='start'>
            毎月単価変更の見直しにより、金額が変わります。<B><font color='red'>'21/11更新</font></B>
            </td>
        </tr>
        <tr class='layout'>
            <td class='layoutg' id='start'>
            0203
            </td>
            <td class='layoutb' id='start' nowrap>
                <!--
                <a href="download_file.php/定時間外作業証明証.pdf">定時間外作業証明書</a>
                -->
                <a href="download_file.php/ニ交替勤務実績記入表.xls">二交替勤務実績記入表</a>
            </td>
            <td class='layout' id='start'>
            　
            </td>
            <td class='layout' id='start'>
            　
            </td>
        </tr>
        <tr class='layout'>
            <td class='layoutg' id='start'>
            0204
            </td>
            <td class='layoutb' id='start' nowrap>
                <!--
                <a href="download_file.php/外出許可証.xls">公用・私用外出許可証</a>
                -->
                <a href="download_file.php/20140901一時帰省交通費.xls">一時帰省交通費</a>
            </td>
            <td class='layout' id='start'>
            　
            </td>
            <td class='layout' id='start'>
            　
            </td>
        </tr>
        <tr class='layout'>
            <td class='layoutg' id='start' rowspan='7' nowrap>
            ●住所変更に関する<BR>　届出
            </td>
            <td class='layoutg' id='start'>
            0301
            </td>
            <td class='layoutb' id='start' nowrap>
                <!--
                <a href="download_file.php/131004総合届（改訂2）.xls">総合届</a>
                -->
                <a href="download_file.php/住所変更に伴う提出書類一覧.doc">提出書類一覧表</a>
            </td>
            <td class='layout' id='start'>
            はじめにお読み下さい
            </td>
            <td class='layout' id='start'>
            　
            </td>
        </tr>
        <tr class='layout'>
            <td class='layoutg' id='start'>
            0302
            </td>
            <td class='layoutb' id='start' nowrap>
                <!--
                <a href="download_file.php/病欠申告書.doc">病欠申告書</a>
                -->
                <a href="download_file.php/人事情報変更データシート.pdf">人事情報変更データシート</a>
            </td>
            <td class='layout' id='start'>
            全員提出
            </td>
            <td class='layout' id='start'>
            　
            </td>
        </tr>
        <tr class='layout'>
            <td class='layoutg' id='start'>
            0303
            </td>
            <td class='layoutb' id='start' nowrap>
                <!--
                <a href="download_file.php/定時間外作業証明証.pdf">定時間外作業証明書</a>
                -->
                <a href="download_file.php/給与所得者の扶養控除申告書.pdf">給与所得者の扶養控除等<BR>（異動）申告書</a>
            </td>
            <td class='layout' id='start'>
            全員提出
            </td>
            <td class='layout' id='start'>
            用紙は総務課へ申出て下さい。<B><font color='red'>'16/01更新</font></B>
            </td>
        </tr>
        <tr class='layout'>
            <td class='layoutg' id='start'>
            0304
            </td>
            <td class='layoutb' id='start' nowrap>
                <!--
                <a href="download_file.php/病欠申告書.doc">病欠申告書</a>
                -->
                <a href="download_file.php/21.11@164ﾏｲｶｰ通勤手当（変更）届.xlsx">マイカー通勤手当（変更）届</a><BR>
                　　　　　　　　　（社員用）
                <BR>
                <a href="download_file.php/21.11@164パート通勤状況届.xlsx">パート通勤状況届</a><BR>
                　　　　　　　　　（パート社員用）
            </td>
            <td class='layout' id='start'>
            全員提出
            </td>
            <td class='layout' id='start'>
            毎月単価変更の見直しにより、金額が変わります。<B><font color='red'>'21/11更新</font></B>
            </td>
        </tr>
        <tr class='layout'>
            <td class='layoutg' id='start'>
            0305
            </td>
            <td class='layoutb' id='start' nowrap>
                <!--
                <a href="download_file.php/定時間外作業証明証.pdf">定時間外作業証明書</a>
                -->
                <a href="download_file.php/社宅・寮 入退居届.doc">社宅・寮 入退去届</a>
            </td>
            <td class='layout' id='start'>
            社宅・寮 入退居の場合は提出
            </td>
            <td class='layout' id='start'>
            　
            </td>
        </tr>
        <!--
        <tr class='layout'>
            <td class='layoutg' id='start'>
            0306
            </td>
            <td class='layoutb' id='start' nowrap>
                <!--
                <a href="download_file.php/定時間外作業証明証.pdf">定時間外作業証明書</a>
                -->
                <!--
                <a href="download_file.php/研修センター入居希望申請書.doc">研修センター入居希望申請書</a>
            </td>
            <td class='layout' id='start'>
            　
            </td>
            <td class='layout' id='start'>
            　
            </td>
        </tr>
        -->
        <tr class='layout'>
            <td class='layoutg' id='start'>
            0306
            </td>
            <td class='layoutb' id='start' nowrap>
                <!--
                <a href="download_file.php/定時間外作業証明証.pdf">定時間外作業証明書</a>
                -->
                <a href="download_file.php/マイカー通勤申請書1.doc">マイカー通勤兼駐車場<BR>使用許可申請書</a>
            </td>
            <td class='layout' id='start'>
            マイカー通勤者は提出
            </td>
            <td class='layout' id='start'>
            　
            </td>
        </tr>
        <tr class='layout'>
            <td class='layoutg' id='start'>
            0307
            </td>
            <td class='layoutb' id='start' nowrap>
                <!--
                <a href="download_file.php/定時間外作業証明証.pdf">定時間外作業証明書</a>
                -->
                ※配偶者の年金手帳<BR>　（基礎年金番号通知書）の写し
            </td>
            <td class='layout' id='start'>
            配偶者を扶養している場合は提出
            </td>
            <td class='layout' id='start'>
            <a href="download_file.php/国民年金第3号被保険者住所変更届.pdf">国民年金第３号被保険者住所変更届</a>
            <BR>
            <a href="download_file.php/被保険者住所変更届.pdf">厚生年金保険 被保険者住所変更届</a>
            </td>
        </tr>
        <tr class='layout'>
            <td class='layoutg' id='start' rowspan='11' nowrap>
            ●結婚に関する<BR>　届出
            </td>
            <td class='layoutg' id='start'>
            0401
            </td>
            <td class='layoutb' id='start' nowrap>
                <!--
                <a href="download_file.php/131004総合届（改訂2）.xls">総合届</a>
                -->
                <a href="download_file.php/結婚に伴う提出書類一覧.doc">提出書類一覧表</a>
            </td>
            <td class='layout' id='start'>
            はじめにお読み下さい
            </td>
            <td class='layout' id='start'>
            　
            </td>
        </tr>
        <tr class='layout'>
            <td class='layoutg' id='start'>
            0402
            </td>
            <td class='layoutb' id='start' nowrap>
                <!--
                <a href="download_file.php/病欠申告書.doc">病欠申告書</a>
                -->
                <a href="download_file.php/人事情報変更データシート.pdf">人事情報変更データシート</a>
            </td>
            <td class='layout' id='start'>
            全員提出
            </td>
            <td class='layout' id='start'>
            　
            </td>
        </tr>
        <tr class='layout'>
            <td class='layoutg' id='start'>
            0403
            </td>
            <td class='layoutb' id='start' nowrap>
                <!--
                <a href="download_file.php/定時間外作業証明証.pdf">定時間外作業証明書</a>
                -->
                <a href="download_file.php/給与所得者の扶養控除申告書.pdf">給与所得者の扶養控除等<BR>（異動）申告書</a>
            </td>
            <td class='layout' id='start'>
            全員提出
            </td>
            <td class='layout' id='start'>
            用紙は総務課へ申出て下さい。<B><font color='red'>'16/01更新</font></B>
            </td>
        </tr>
        <tr class='layout'>
            <td class='layoutg' id='start'>
            0404
            </td>
            <td class='layoutb' id='start' nowrap>
                <!--
                <a href="download_file.php/病欠申告書.doc">病欠申告書</a>
                -->
                <a href="download_file.php/健康保険被扶養者異動届.pdf">健康保険被扶養者（異動）届</a>
            </td>
            <td class='layout' id='start'>
            扶養する場合のみ提出
            </td>
            <td class='layout' id='start'>
            用紙は、２枚複写の専用用紙です。総務課へ申出て下さい。
            <BR>
            <a href="download_file.php/被扶養者異動届（記入例）.pdf">記入例</a>
            </td>
        </tr>
        <tr class='layout'>
            <td class='layoutg' id='start'>
            0405
            </td>
            <td class='layoutb' id='start' nowrap>
                <!--
                <a href="download_file.php/定時間外作業証明証.pdf">定時間外作業証明書</a>
                -->
                <a href="download_file.php/国民年金第3号被保険者資格取得届.pdf">国民年金第３号被保険者資格取得届</a>
                <BR>
                ※配偶者の年金手帳<BR>　（基礎年金番号通知書）の写し
            </td>
            <td class='layout' id='start'>
            配偶者を扶養する場合のみ提出
            </td>
            <td class='layout' id='start'>
            　
            </td>
        </tr>
        <tr class='layout'>
            <td class='layoutg' id='start'>
            0406
            </td>
            <td class='layoutb' id='start' nowrap>
                <!--
                <a href="download_file.php/定時間外作業証明証.pdf">定時間外作業証明書</a>
                -->
                雇用保険被保険者離職票−１、−２
            </td>
            <td class='layout' id='start'>
            雇用保険の加入者だった方を扶養する場合のみ提出
            </td>
            <td class='layout' id='start'>
            失業保険受給中は扶養に入れることは出来ません。
            </td>
        </tr>
        <tr class='layout'>
            <td class='layoutg' id='start'>
            0407
            </td>
            <td class='layoutb' id='start' nowrap>
                <!--
                <a href="download_file.php/定時間外作業証明証.pdf">定時間外作業証明書</a>
                -->
                ※所得を証明するもの
            </td>
            <td class='layout' id='start'>
            源泉徴収票または非課税証明書
            <BR>
            年金受給者は「年金受給通知書の写し
            </td>
            <td class='layout' id='start'>
            　
            </td>
        </tr>
        <tr class='layout'>
            <td class='layoutg' id='start'>
            0408
            </td>
            <td class='layoutb' id='start' nowrap>
                <!--
                <a href="download_file.php/定時間外作業証明証.pdf">定時間外作業証明書</a>
                -->
                ※社会保険喪失証明書
            </td>
            <td class='layout' id='start'>
            社会保険に加入していた方
            </td>
            <td class='layout' id='start'>
            　
            </td>
        </tr>
        <tr class='layout'>
            <td class='layoutg' id='start'>
            0409
            </td>
            <td class='layoutb' id='start' nowrap>
                <!--
                <a href="download_file.php/定時間外作業証明証.pdf">定時間外作業証明書</a>
                -->
                <a href="download_file.php/慶事関係届出書　原紙.xls">慶事関係届出書</a>
            </td>
            <td class='layout' id='start'>
            全員提出
            </td>
            <td class='layout' id='start'>
            お祝い金が支給されます。
            </td>
        </tr>
        <tr class='layout'>
            <td class='layoutg' id='start'>
            0410
            </td>
            <td class='layoutb' id='start' nowrap>
                <!--
                <a href="download_file.php/定時間外作業証明証.pdf">定時間外作業証明書</a>
                -->
                <a href="download_file.php/氏名変更届.pdf">健康保険・厚生年金保険 氏名変更届</a>
                <BR>
                ※新氏名の住民票、年金手帳
            </td>
            <td class='layout' id='start'>
            氏名が変わる場合のみ提出（原本２枚）
            </td>
            <td class='layout' id='start'>
            　
            </td>
        </tr>
        <tr class='layout'>
            <td class='layoutg' id='start'>
            0411
            </td>
            <td class='layoutb' id='start' nowrap>
                <!--
                <a href="download_file.php/定時間外作業証明証.pdf">定時間外作業証明書</a>
                -->
                ※その他、氏名変更・住所変更に<BR>　関する届出
            </td>
            <td class='layout' id='start'>
            ※氏名変更や住所変更があった場合
            </td>
            <td class='layout' id='start'>
            ※氏名変更や住所変更が伴う場合は、別途住所変更に関する届出書や給与振込み口座変更届が必要になりますので、総務課までご連絡下さい。
            </td>
        </tr>
        <tr class='layout'>
            <td class='layoutg' id='start' rowspan='7' nowrap>
            ●出産に関する<BR>　届出
            </td>
            <td class='layoutg' id='start'>
            0501
            </td>
            <td class='layoutb' id='start' nowrap>
                <!--
                <a href="download_file.php/131004総合届（改訂2）.xls">総合届</a>
                -->
                <a href="download_file.php/出産に伴う提出書類一覧.doc">提出書類一覧表</a>
            </td>
            <td class='layout' id='start'>
            はじめにお読み下さい
            </td>
            <td class='layout' id='start'>
            　
            </td>
        </tr>
        <tr class='layout'>
            <td class='layoutg' id='start'>
            0502
            </td>
            <td class='layoutb' id='start' nowrap>
                <!--
                <a href="download_file.php/病欠申告書.doc">病欠申告書</a>
                -->
                <a href="download_file.php/人事情報変更データシート.pdf">人事情報変更データシート</a>
            </td>
            <td class='layout' id='start'>
            全員提出
            </td>
            <td class='layout' id='start'>
            　
            </td>
        </tr>
        <tr class='layout'>
            <td class='layoutg' id='start'>
            0503
            </td>
            <td class='layoutb' id='start' nowrap>
                <!--
                <a href="download_file.php/定時間外作業証明証.pdf">定時間外作業証明書</a>
                -->
                <a href="download_file.php/給与所得者の扶養控除申告書.pdf">給与所得者の扶養控除等<BR>（異動）申告書</a>
            </td>
            <td class='layout' id='start'>
            扶養する場合のみ提出
            </td>
            <td class='layout' id='start'>
            用紙は総務課へ申出て下さい。<B><font color='red'>'16/01更新</font></B>
            </td>
        </tr>
        <tr class='layout'>
            <td class='layoutg' id='start'>
            0504
            </td>
            <td class='layoutb' id='start' nowrap>
                <!--
                <a href="download_file.php/病欠申告書.doc">病欠申告書</a>
                -->
                <a href="download_file.php/健康保険被扶養者異動届.pdf">健康保険被扶養者（異動）届</a>
            </td>
            <td class='layout' id='start'>
            扶養する場合のみ提出
            </td>
            <td class='layout' id='start'>
            用紙は、２枚複写の専用用紙です。総務課へ申出て下さい。
            <BR>
            <a href="download_file.php/被扶養者異動届（記入例）.pdf">記入例</a>
            </td>
        </tr>
        <tr class='layout'>
            <td class='layoutg' id='start'>
            0505
            </td>
            <td class='layoutb' id='start' nowrap>
                <!--
                <a href="download_file.php/定時間外作業証明証.pdf">定時間外作業証明書</a>
                -->
                <a href="download_file.php/出産育児一時金請求書.pdf">出産育児一時金請求書</a>
            </td>
            <td class='layout' id='start'>
            被保険者または扶養配偶者の出産の場合のみ提出
            </td>
            <td class='layout' id='start'>
            医師の証明が必要です。
            </td>
        </tr>
        <tr class='layout'>
            <td class='layoutg' id='start'>
            0506
            </td>
            <td class='layoutb' id='start' nowrap>
                <!--
                <a href="download_file.php/定時間外作業証明証.pdf">定時間外作業証明書</a>
                -->
                <a href="download_file.php/出産手当金請求書.pdf">出産手当金・出産手当附加金請求書</a>
            </td>
            <td class='layout' id='start'>
            被保険者の出産の場合のみ提出
            </td>
            <td class='layout' id='start'>
            医師の証明が必要です。
            </td>
        </tr>
        <tr class='layout'>
            <td class='layoutg' id='start'>
            0507
            </td>
            <td class='layoutb' id='start' nowrap>
                <!--
                <a href="download_file.php/定時間外作業証明証.pdf">定時間外作業証明書</a>
                -->
                <a href="download_file.php/慶事関係届出書　原紙.xls">慶事関係届出書</a>
            </td>
            <td class='layout' id='start'>
            全員提出
            </td>
            <td class='layout' id='start'>
            お祝い金が支給されます。
            </td>
        </tr>
        <tr class='layout'>
            <td class='layoutg' id='start' rowspan='8' nowrap>
            ●扶養者の増加に<BR>　関する届出
            <BR><B>
            　（結婚・出産<BR>　　以外の場合）
            </B>
            </td>
            <td class='layoutg' id='start'>
            0601
            </td>
            <td class='layoutb' id='start' nowrap>
                <!--
                <a href="download_file.php/131004総合届（改訂2）.xls">総合届</a>
                -->
                <a href="download_file.php/扶養増加に伴う提出書類一覧.doc">提出書類一覧表</a>
            </td>
            <td class='layout' id='start'>
            はじめにお読み下さい
            </td>
            <td class='layout' id='start'>
            　
            </td>
        </tr>
        <tr class='layout'>
            <td class='layoutg' id='start'>
            0602
            </td>
            <td class='layoutb' id='start' nowrap>
                <!--
                <a href="download_file.php/病欠申告書.doc">病欠申告書</a>
                -->
                <a href="download_file.php/人事情報変更データシート.pdf">人事情報変更データシート</a>
            </td>
            <td class='layout' id='start'>
            全員提出
            </td>
            <td class='layout' id='start'>
            　
            </td>
        </tr>
        <tr class='layout'>
            <td class='layoutg' id='start'>
            0603
            </td>
            <td class='layoutb' id='start' nowrap>
                <!--
                <a href="download_file.php/定時間外作業証明証.pdf">定時間外作業証明書</a>
                -->
                <a href="download_file.php/給与所得者の扶養控除申告書.pdf">給与所得者の扶養控除等<BR>（異動）申告書</a>
            </td>
            <td class='layout' id='start'>
            全員提出
            </td>
            <td class='layout' id='start'>
            用紙は総務課へ申出て下さい。<B><font color='red'>'16/01更新</font></B>
            </td>
        </tr>
        <tr class='layout'>
            <td class='layoutg' id='start'>
            0604
            </td>
            <td class='layoutb' id='start' nowrap>
                <!--
                <a href="download_file.php/病欠申告書.doc">病欠申告書</a>
                -->
                <a href="download_file.php/健康保険被扶養者異動届.pdf">健康保険被扶養者（異動）届</a>
            </td>
            <td class='layout' id='start'>
            扶養する場合のみ提出
            </td>
            <td class='layout' id='start'>
            用紙は、２枚複写の専用用紙です。総務課へ申出て下さい。
            <BR>
            <a href="download_file.php/扶養者異動届（記入例）.pdf">記入例</a>
            </td>
        </tr>
        <tr class='layout'>
            <td class='layoutg' id='start'>
            0605
            </td>
            <td class='layoutb' id='start' nowrap>
                <!--
                <a href="download_file.php/定時間外作業証明証.pdf">定時間外作業証明書</a>
                -->
                <a href="download_file.php/国民年金第3号被保険者資格取得届.pdf">国民年金第３号被保険者資格取得届</a>
                <BR>
                ※配偶者の年金手帳<BR>　（基礎年金番号通知書）の写し
            </td>
            <td class='layout' id='start'>
            配偶者を扶養する場合のみ提出
            </td>
            <td class='layout' id='start'>
            　
            </td>
        </tr>
        <tr class='layout'>
            <td class='layoutg' id='start'>
            0606
            </td>
            <td class='layoutb' id='start' nowrap>
                <!--
                <a href="download_file.php/定時間外作業証明証.pdf">定時間外作業証明書</a>
                -->
                雇用保険被保険者離職票−１、−２
            </td>
            <td class='layout' id='start'>
            雇用保険の加入者だった方を扶養する場合のみ提出
            </td>
            <td class='layout' id='start'>
            失業保険受給中は扶養に入れることは出来ません。
            </td>
        </tr>
        <tr class='layout'>
            <td class='layoutg' id='start'>
            0607
            </td>
            <td class='layoutb' id='start' nowrap>
                <!--
                <a href="download_file.php/定時間外作業証明証.pdf">定時間外作業証明書</a>
                -->
                ※所得を証明するもの
            </td>
            <td class='layout' id='start'>
            源泉徴収票または非課税証明書
            <BR>
            年金受給者は「年金受給通知書の写し
            </td>
            <td class='layout' id='start'>
            　
            </td>
        </tr>
        <tr class='layout'>
            <td class='layoutg' id='start'>
            0608
            </td>
            <td class='layoutb' id='start' nowrap>
                <!--
                <a href="download_file.php/定時間外作業証明証.pdf">定時間外作業証明書</a>
                -->
                ※社会保険喪失証明書
            </td>
            <td class='layout' id='start'>
            社会保険に加入していた方
            </td>
            <td class='layout' id='start'>
            　
            </td>
        </tr>
        <tr class='layout'>
            <td class='layoutg' id='start' rowspan='5' nowrap>
            ●扶養者の減少に<BR>　関する届出
            </td>
            <td class='layoutg' id='start'>
            0701
            </td>
            <td class='layoutb' id='start' nowrap>
                <!--
                <a href="download_file.php/131004総合届（改訂2）.xls">総合届</a>
                -->
                <a href="download_file.php/扶養減少に伴う提出書類一覧.doc">提出書類一覧表</a>
            </td>
            <td class='layout' id='start'>
            はじめにお読み下さい
            </td>
            <td class='layout' id='start'>
            　
            </td>
        </tr>
        <tr class='layout'>
            <td class='layoutg' id='start'>
            0702
            </td>
            <td class='layoutb' id='start' nowrap>
                <!--
                <a href="download_file.php/病欠申告書.doc">病欠申告書</a>
                -->
                <a href="download_file.php/人事情報変更データシート.pdf">人事情報変更データシート</a>
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
            <td class='layoutb' id='start' nowrap>
                <!--
                <a href="download_file.php/定時間外作業証明証.pdf">定時間外作業証明書</a>
                -->
                <a href="download_file.php/給与所得者の扶養控除申告書.pdf">給与所得者の扶養控除等<BR>（異動）申告書</a>
            </td>
            <td class='layout' id='start'>
            全員提出
            </td>
            <td class='layout' id='start'>
            用紙は総務課へ申出て下さい。<B><font color='red'>'16/01更新</font></B>
            </td>
        </tr>
        <tr class='layout'>
            <td class='layoutg' id='start'>
            0704
            </td>
            <td class='layoutb' id='start' nowrap>
                <!--
                <a href="download_file.php/病欠申告書.doc">病欠申告書</a>
                -->
                <a href="download_file.php/健康保険被扶養者異動届.pdf">健康保険被扶養者（異動）届</a>
            </td>
            <td class='layout' id='start'>
            扶養から外す方は提出
            </td>
            <td class='layout' id='start'>
            用紙は、２枚複写の専用用紙です。総務課へ申出て下さい。
            <BR>
            <a href="download_file.php/扶養者異動届（記入例）.pdf">記入例</a>
            </td>
        </tr>
        <tr class='layout'>
            <td class='layoutg' id='start'>
            0705
            </td>
            <td class='layoutb' id='start' nowrap>
                <!--
                <a href="download_file.php/定時間外作業証明証.pdf">定時間外作業証明書</a>
                -->
                健康保険被保険者証
            </td>
            <td class='layout' id='start'>
            扶養から外す方の保険証を返却して下さい。
            </td>
            <td class='layout' id='start'>
            　
            </td>
        </tr>
        <tr class='layout'>
            <td class='layoutg' id='start' rowspan='6' nowrap>
            ●弔慰に関する<BR>　届出
            </td>
            <td class='layoutg' id='start'>
            0801
            </td>
            <td class='layoutb' id='start' nowrap>
                <!--
                <a href="download_file.php/131004総合届（改訂2）.xls">総合届</a>
                -->
                <a href="download_file.php/弔慰関係に伴う提出書類一覧.doc">提出書類一覧表</a>
            </td>
            <td class='layout' id='start'>
            はじめにお読み下さい
            </td>
            <td class='layout' id='start'>
            　
            </td>
        </tr>
        <tr class='layout'>
            <td class='layoutg' id='start'>
            0802
            </td>
            <td class='layoutb' id='start' nowrap>
                <!--
                <a href="download_file.php/病欠申告書.doc">病欠申告書</a>
                -->
                <a href="download_file.php/人事情報変更データシート.pdf">人事情報変更データシート</a>
            </td>
            <td class='layout' id='start'>
            全員提出
            </td>
            <td class='layout' id='start'>
            　
            </td>
        </tr>
        <tr class='layout'>
            <td class='layoutg' id='start'>
            0803
            </td>
            <td class='layoutb' id='start' nowrap>
                <!--
                <a href="download_file.php/定時間外作業証明証.pdf">定時間外作業証明書</a>
                -->
                <a href="download_file.php/給与所得者の扶養控除申告書.pdf">給与所得者の扶養控除等<BR>（異動）申告書</a>
            </td>
            <td class='layout' id='start'>
            扶養していた場合のみ提出
            </td>
            <td class='layout' id='start'>
            用紙は総務課へ申出て下さい。<B><font color='red'>'16/01更新</font></B>
            </td>
        </tr>
        <tr class='layout'>
            <td class='layoutg' id='start'>
            0804
            </td>
            <td class='layoutb' id='start' nowrap>
                <!--
                <a href="download_file.php/病欠申告書.doc">病欠申告書</a>
                -->
                <a href="download_file.php/健康保険被扶養者異動届.pdf">健康保険被扶養者（異動）届</a>
            </td>
            <td class='layout' id='start'>
            扶養していた場合のみ提出
            </td>
            <td class='layout' id='start'>
            用紙は、２枚複写の専用用紙です。総務課へ申出て下さい。
            <BR>
            <a href="download_file.php/扶養者異動届（記入例）.pdf">記入例</a>
            </td>
        </tr>
        <tr class='layout'>
            <td class='layoutg' id='start'>
            0805
            </td>
            <td class='layoutb' id='start' nowrap>
                <!--
                <a href="download_file.php/定時間外作業証明証.pdf">定時間外作業証明書</a>
                -->
                <a href="download_file.php/埋葬料−埋葬料附加金請求書.pdf">埋葬料・埋葬料附加金請求書</a>
            </td>
            <td class='layout' id='start'>
            扶養していた場合のみ提出
            </td>
            <td class='layout' id='start'>
            　
            </td>
        </tr>
        <tr class='layout'>
            <td class='layoutg' id='start'>
            0806
            </td>
            <td class='layoutb' id='start' nowrap>
                <!--
                <a href="download_file.php/定時間外作業証明証.pdf">定時間外作業証明書</a>
                -->
                健康保険被保険者証
            </td>
            <td class='layout' id='start'>
            扶養していた場合のみ提出
            </td>
            <td class='layout' id='start'>
            　
            </td>
        </tr>
        <tr class='layout'>
            <td class='layoutg' id='start' rowspan='1' nowrap>
            ●マイカー変更に<BR>　関する届出
            </td>
            <td class='layoutg' id='start'>
            0901
            </td>
            <td class='layoutb' id='start' nowrap>
                <!--
                <a href="download_file.php/131004総合届（改訂2）.xls">総合届</a>
                -->
                <a href="download_file.php/マイカー通勤申請書1.doc">マイカー通勤兼駐車場<BR>使用許可申請書</a>
            </td>
            <td class='layout' id='start'>
            マイカー通勤者のみ提出
            <BR>
            ※車両・任意保険変更時は再提出
            </td>
            <td class='layout' id='start'>
            新規・変更の方は車検証及び任保写しを添付
            </td>
        </tr>
        <tr class='layout'>
            <td class='layoutg' id='start' rowspan='1' nowrap>
            ●公的資格取得に<BR>　関する届出
            </td>
            <td class='layoutg' id='start'>
            2101
            </td>
            <td class='layoutb' id='start' nowrap>
                <!--
                <a href="download_file.php/131004総合届（改訂2）.xls">総合届</a>
                -->
                <a href="download_file.php/受験申請書　兼　結果報告及び奨励金申請書（新）.doc">公的資格取得 受験申請書 兼<BR>　　結果報告及び奨励金申請書</a>
            </td>
            <td class='layout' id='start'>
            　
            </td>
            <td class='layout' id='start'>
            資格取得のための講習会等を受講する前に提出して下さい。
            </td>
        </tr>
        <tr class='layout'>
            <td class='layoutg' id='start' rowspan='2' nowrap>
            ●教育訓練に<BR>　関する届出
            </td>
            <td class='layoutg' id='start'>
            3101
            </td>
            <td class='layoutb' id='start' nowrap>
                <!--
                <a href="download_file.php/131004総合届（改訂2）.xls">総合届</a>
                -->
                <a href="download_file.php/教育訓練実施報告書　原紙.DOC">教育訓練実施報告書</a>
            </td>
            <td class='layout' id='start'>
            　
            </td>
            <td class='layout' id='start'>
            　
            </td>
        </tr>
        <tr class='layout'>
            <td class='layoutg' id='start'>
            3102
            </td>
            <td class='layoutb' id='start' nowrap>
                <a href="download_file.php/418-CUA-011_教育訓練変更・中止申請書（2016.07.25）.DOC">教育訓練変更中止申請書</a>
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
