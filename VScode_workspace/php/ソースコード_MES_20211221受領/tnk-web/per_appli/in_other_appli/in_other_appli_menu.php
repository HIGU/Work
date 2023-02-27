<?php
//////////////////////////////////////////////////////////////////////////////
// 届出・申請書メニュー（社内 その他の届出）                                //
// Copyright (C) 2014-2020 Norihisa.Ohya norihisa_ooya@nitto-kohki.co.jp    //
// Changed history                                                          //
// 2014/09/29 Created  in_other_appli.php                                   //
// 2015/02/06 一部の申請書を追加                                            //
// 2015/06/17 報告書・災害発生報告書を追加                                  //
// 2016/02/22 パワーポイントテンプレートを追加                              //
// 2016/04/22 カレンダーを2016年度へ更新                                    //
// 2016/06/08 ホームページ掲載依頼書を追加                                  //
// 2016/07/07 事故報告書（労災・物損事故用）を更新                          //
// 2017/02/08 借入金の記入例を追加                                          //
// 2017/06/09 パワーポイントテンプレートの使用方法を追加                    //
// 2017/06/20 稟議書原紙を追加                                              //
// 2017/06/23 テンプレートを更新                                            //
// 2017/06/30 稟議書原紙を更新 備考追加                                     //
// 2017/07/18 テンプレートを更新(201707)                                    //
// 2017/11/09 カレンダーを18期に更新                                        //
// 2018/04/13 持株会の一部引出申請書を18年4月版へ更新                       //
// 2019/05/29 持株会の一部引出申請書を19年5月版へ更新                       //
// 2019/07/17 事故報告書（車両事故用）を追加                                //
// 2020/07/27 カレンダーを21期に更新                                        //
// 2020/09/24 その他にテンプレートをいくつか追加                            //
// 2020/11/10 22期のカレンダーを追加                                        //
// 2021/03/15 22期のカレンダーを更新                                   和氣 //
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
$menu->set_title('届出・申請書メニュー（社内 その他）');
//////////// 表題の設定
$menu->set_caption('届出・申請書メニュー（社内 その他）');

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
            <td class='layoutg' id='start'>
            ●稟議書
            </td>
            <td class='layoutb' id='start'>
                <a href="download_file.php/稟議書原紙20210528.xls">稟議書</a>
            </td>
            <td class='layout' id='start'>
            保存して直接入力してください。
            </td>
            <td class='layout' id='start'>
            <B><font color='red'>'21年5月28日〜</font></B>
            </td>
        </tr>
        <tr class='layout'>
            <td class='layoutg' id='start' rowspan='4'>
            ●育児に関する<BR>　届出
            </td>
            <td class='layoutb' id='start'>
                <a href="download_file.php/育児休業申請書.doc">育児休業申請書</a>
            </td>
            <td class='layout' id='start'>
            　
            </td>
            <td class='layout' id='start'>
            　
            </td>
        </tr>
        <tr class='layout'>
            <td class='layoutb' id='start'>
                <a href="download_file.php/育児休業延長短縮申出書.xls">育児休業延長・短縮申出書</a>
            </td>
            <td class='layout' id='start'>
            　
            </td>
            <td class='layout' id='start'>
            　
            </td>
        </tr>
        <tr class='layout'>
            <td class='layoutb' id='start'>
                <a href="download_file.php/育児短時間勤務申請書.doc">育児短時間勤務申請書</a>
            </td>
            <td class='layout' id='start'>
            　
            </td>
            <td class='layout' id='start'>
            　
            </td>
        </tr>
        <tr class='layout'>
            <td class='layoutb' id='start'>
                <a href="download_file.php/130501子の看護休暇請求書.xls">子の看護のための休暇請求書</a>
            </td>
            <td class='layout' id='start'>
            　
            </td>
            <td class='layout' id='start'>
            　
            </td>
        </tr>
        <tr class='layout'>
            <td class='layoutg' id='start' rowspan='4'>
            ●介護に関する<BR>　届出
            </td>
            <td class='layoutb' id='start'>
                <a href="download_file.php/介護休業申請書.doc">介護休業申請書</a>
            </td>
            <td class='layout' id='start'>
            　
            </td>
            <td class='layout' id='start'>
            　
            </td>
        </tr>
        <tr class='layout'>
            <td class='layoutb' id='start'>
                <a href="download_file.php/介護休業延長短縮申出書.xls">介護休業延長・短縮申出書</a>
            </td>
            <td class='layout' id='start'>
            　
            </td>
            <td class='layout' id='start'>
            　
            </td>
        </tr>
        <tr class='layout'>
            <td class='layoutb' id='start'>
                <a href="download_file.php/介護短時間勤務申請書.doc">介護短時間勤務申請書</a>
            </td>
            <td class='layout' id='start'>
            　
            </td>
            <td class='layout' id='start'>
            　
            </td>
        </tr>
        <tr class='layout'>
            <td class='layoutb' id='start'>
                <a href="download_file.php/家族の介護のための休暇請求書.doc">家族の介護のための休暇請求書</a>
            </td>
            <td class='layout' id='start'>
            　
            </td>
            <td class='layout' id='start'>
            　
            </td>
        </tr>
        <tr class='layout'>
            <td class='layoutg' id='start' rowspan='2'>
            ●業務引継ぎに<BR>　関する届出
            </td>
            <td class='layoutb' id='start'>
                <a href="download_file.php/業務引継完了報告書.doc">引継完了報告書</a>
            </td>
            <td class='layout' id='start'>
            　
            </td>
            <td class='layout' id='start'>
            　
            </td>
        </tr>
        <tr class='layout'>
            <td class='layoutb' id='start'>
                <a href="download_file.php/決裁権限一部委譲許可申請書(原紙).doc">決裁権限一部委譲許可申請書</a>
            </td>
            <td class='layout' id='start'>
            　
            </td>
            <td class='layout' id='start'>
            　
            </td>
        </tr>
        <tr class='layout'>
            <td class='layoutg' id='start' rowspan='7'>
            ●融資・貸付に<BR>　関する届出
            </td>
            <td class='layoutb' id='start'>
                <a href="download_file.php/住宅資金借入れ念書.doc">住宅資金借入れ念書</a>
            </td>
            <td class='layout' id='start'>
            　
            </td>
            <td class='layout' id='start'>
            　
            </td>
        </tr>
        <tr class='layout'>
            <td class='layoutb' id='start'>
                <a href="download_file.php/住宅資金借入申請書.doc">住宅資金借入申請書</a>
            </td>
            <td class='layout' id='start'>
            　
            </td>
            <td class='layout' id='start'>
            　
            </td>
        </tr>
        <tr class='layout'>
            <td class='layoutb' id='start'>
                <a href="download_file.php/住宅資金借用証書.xls">住宅資金借用証書</a>
            </td>
            <td class='layout' id='start'>
                <a href="download_file.php/借用証書記入例（住宅資金）.pdf">記入例</a>
            </td>
            <td class='layout' id='start'>
            借入金の入金を確認後、提出してください。
            </td>
        </tr>
        <tr class='layout'>
            <td class='layoutb' id='start'>
                <a href="download_file.php/教育資金借入申請書.doc">教育資金借入申請書</a>
            </td>
            <td class='layout' id='start'>
                <a href="download_file.php/教育資金借入申請書記入例.pdf">記入例</a>
            </td>
            <td class='layout' id='start'>
            　
            </td>
        </tr>
        <tr class='layout'>
            <td class='layoutb' id='start'>
                <a href="download_file.php/教育資金借用証書.doc">教育資金借用証書</a>
            </td>
            <td class='layout' id='start'>
                <a href="download_file.php/借用証書記入例（教育資金）.pdf">記入例</a>
            </td>
            <td class='layout' id='start'>
            借入金の入金を確認後、提出してください。
            </td>
        </tr>
        <tr class='layout'>
            <td class='layoutb' id='start'>
                <a href="download_file.php/一般貸付金借入申請書原紙.xls">一般貸付金借入申請書</a>
            </td>
            <td class='layout' id='start'>
                <a href="download_file.php/一般貸付金借入申請書記入例.pdf">記入例</a>
            </td>
            <td class='layout' id='start'>
            　
            </td>
        </tr>
        <tr class='layout'>
            <td class='layoutb' id='start'>
                <a href="download_file.php/一般貸付金借用証書.xls">一般貸付金借用証書</a>
            </td>
            <td class='layout' id='start'>
                <a href="download_file.php/借用証書記入例（一般貸付金）.pdf">記入例</a>
            </td>
            <td class='layout' id='start'>
            借入金の入金を確認後、提出してください。
            </td>
        </tr>
        <tr class='layout'>
            <td class='layoutg' id='start' rowspan='3'>
            ●事故報告に関する<BR>　届出
            </td>
            <td class='layoutb' id='start'>
                <a href="download_file.php/事故報告書（物損事故用）　原紙.xls">事故報告書（物損事故用）</a>
            </td>
            <td class='layout' id='start'>
            物損による営繕依頼を行う場合は<BR>営繕依頼書と合わせて提出して下さい
            </td>
            <td class='layout' id='start'>
            　
            </td>
        </tr>
        <tr class='layout'>
            <td class='layoutb' id='start'>
                <a href="download_file.php/事故報告書（労災用）　原紙.xls">事故報告書（労災用）</a>
            </td>
            <td class='layout' id='start'>
            労災発生時速やかに提出して下さい。
            </td>
            <td class='layout' id='start'>
            　
            </td>
        </tr>
        <tr class='layout'>
            <td class='layoutb' id='start'>
                <a href="download_file.php/事故報告書（車両事故用）　原紙.xls">事故報告書（車両事故用）</a>
            </td>
            <td class='layout' id='start'>
            事故発生時速やかに提出して下さい。
            </td>
            <td class='layout' id='start'>
            　
            </td>
        </tr>
        <tr class='layout'>
            <td class='layoutg' id='start' rowspan='4'>
            ●コンピューター<BR>　関連の届出
            </td>
            <td class='layoutb' id='start'>
                <a href="download_file.php/開発依頼書20080710.doc">開発依頼書</a>
            </td>
            <td class='layout' id='start'>
                <a href="download_file.php/開発依頼書（棚卸記入表）.doc">記入例</a>
            </td>
            <td class='layout' id='start'>
            AS/400 プログラム開発依頼時に提出
            </td>
        </tr>
        <tr class='layout'>
            <td class='layoutb' id='start'>
                <a href="download_file.php/データ直接修正申請書20080409.xls">データ直接修正申請書</a>
            </td>
            <td class='layout' id='start'>
                <a href="download_file.php/データ直接修正申請書(記入例).xls">記入例</a>
            </td>
            <td class='layout' id='start'>
            AS/400 データ直接修正依頼時に提出
            </td>
        </tr>
        <tr class='layout'>
            <td class='layoutb' id='start'>
                <a href="download_file.php/アクセス権申請書20080407.xls">アクセス権申請書</a>
            </td>
            <td class='layout' id='start'>
            　
            </td>
            <td class='layout' id='start'>
            メール・端末 追加依頼時に提出
            </td>
        </tr>
        <tr class='layout'>
            <td class='layoutb' id='start'>
                <a href="download_file.php/ホームページ掲載依頼書 経-HP-01-v01.pdf">ホームページ掲載依頼書</a>
            </td>
            <td class='layout' id='start'>
            　
            </td>
            <td class='layout' id='start'>
            　
            </td>
        </tr>
        <tr class='layout'>
            <td class='layoutg' id='start' rowspan='3'>
            ●健康保険組合に<BR>　関する届出
            </td>
            <td class='layoutb' id='start'>
                <a href="download_file.php/健康保険限度額適用認定申請書.pdf">健康保険限度額適用認定申請書</a>
            </td>
            <td class='layout' id='start'>
            　
            </td>
            <td class='layout' id='start'>
            　
            </td>
        </tr>
        <tr class='layout'>
            <td class='layoutb' id='start'>
                <a href="download_file.php/高額療養費支給申請書.pdf">本人・家族高額療養費支給申請書</a>
            </td>
            <td class='layout' id='start'>
            用紙は総務に申出て下さい。
            <BR>
            領収証の写しを添付して下さい。
            </td>
            <td class='layout' id='start'>
            　
            </td>
        </tr>
        <tr class='layout'>
            <td class='layoutb' id='start'>
                <a href="download_file.php/傷病手当金請求書.pdf">傷病手当金請求書</a>
            </td>
            <td class='layout' id='start'>
            用紙は総務に申出て下さい。
            <BR>
            医師の証明が必要です。
            </td>
            <td class='layout' id='start'>
            　
            </td>
        </tr>
        <tr class='layout'>
            <td class='layoutg' id='start' rowspan='4'>
            ●持株会に関する<BR>　届出
            </td>
            <td class='layoutb' id='start'>
                <a href="download_file.php/持株会入会申込書 .xls">入会申込書</a>
            </td>
            <td class='layout' id='start'>
            年１回７月
            <BR>
            １口1,000円、給与の10％まで
            </td>
            <td class='layout' id='start'>
            　
            </td>
        </tr>
        <tr class='layout'>
            <td class='layoutb' id='start'>
                <a href="download_file.php/休止．再開．口数変更申請書.xls">休止、再開、口数変更申請書</a>
            </td>
            <td class='layout' id='start'>
            年１回７月
            </td>
            <td class='layout' id='start'>
            　
            </td>
        </tr>
        <tr class='layout'>
            <td class='layoutb' id='start'>
                <a href="download_file.php/19.5一部引出申請書.xlsx">一部引出申請書</a>
            </td>
            <td class='layout' id='start'>
            　
            </td>
            <td class='layout' id='start'>
            <font color='red'><B>19年5月更新</B></font>
            </td>
        </tr>
        <tr class='layout'>
            <td class='layoutb' id='start'>
                <a href="download_file.php/持株会退会届出書.xls">退会届</a>
            </td>
            <td class='layout' id='start'>
            　
            </td>
            <td class='layout' id='start'>
            　
            </td>
        </tr>
        <tr class='layout'>
            <td class='layoutg' id='start' rowspan='4'>
            ●セコムに関する<BR>　資料
            </td>
            <td class='layoutb' id='start'>
                <a href="download_file.php/緊急連絡先（新規登録・変更届）.xls">メールアドレス変更申請</a>
            </td>
            <td class='layout' id='start'>
            　
            </td>
            <td class='layout' id='start'>
            　
            </td>
        </tr>
        <tr class='layout'>
            <td class='layoutb' id='start'>
                <a href="download_file.php/安否報告画面.ppt">安否確認画面、操作方法</a>
            </td>
            <td class='layout' id='start'>
            　
            </td>
            <td class='layout' id='start'>
            　
            </td>
        </tr>
        <tr class='layout'>
            <td class='layoutb' id='start'>
                <a href="download_file.php/本人情報画面.ppt">本人情報画面、操作方法</a>
            </td>
            <td class='layout' id='start'>
            　
            </td>
            <td class='layout' id='start'>
            　
            </td>
        </tr>
        <tr class='layout'>
            <td class='layoutb' id='start'>
                <a href="download_file.php/迷惑メール対策.pdf">迷惑メール対策方法</a>
            </td>
            <td class='layout' id='start'>
            　
            </td>
            <td class='layout' id='start'>
            　
            </td>
        </tr>
        <tr class='layout'>
            <td class='layoutg' id='start' rowspan='2'>
            ●カレンダー
            </td>
            <td class='layoutb' id='start'>
                <a href="download_file.php/第22期栃木日東工器就労カレンダー.xlsx">第22期<BR>栃木日東工器カレンダー</a>
            </td>
            <td class='layout' id='start'>
            　
            </td>
            <td class='layout' id='start'>
            　
            </td>
        </tr>
        <tr class='layout'>
            <td class='layoutb' id='start'>
                <a href="download_file.php/第23期栃木日東工器就労カレンダー.xlsx">第23期<BR>栃木日東工器カレンダー</a>
            </td>
            <td class='layout' id='start'>
            　
            </td>
            <td class='layout' id='start'>
            　
            </td>
        </tr>
        <!--
        <tr class='layout'>
            <td class='layoutb' id='start'>
                <a href="download_file.php/2020第21期 機密文書回収カレンダー.xlsx">第21期<BR>機密文書回収カレンダー</a>
            </td>
            <td class='layout' id='start'>
            　
            </td>
            <td class='layout' id='start'>
            　
            </td>
        </tr>
        -->
        <tr class='layout'>
            <td class='layoutg' id='start' rowspan='5'>
            ●その他
            </td>
            <td class='layoutb' id='start'>
                <a href="download_file.php/TNK_J_201707.potx">パワーポイント<BR>テンプレート</a>
            </td>
            <td class='layout' id='start'>
                <a href="download_file.php/テンプレートの使用方法.xls">使用方法</a>
            </td>
            <td class='layout' id='start'>
            <font color='red'><B>使用方法をよく読んでご使用ください。</B></font>
            <BR>
            <font color='red'><B>17年7月更新</B></font>
            </td>
        </tr>
        <tr class='layout'>
            <td class='layoutb' id='start'>
                TNK FaxSheet
            </td>
            <td class='layout' id='start'>
            <a href="download_file.php/1.TNK_FaxSheet.xlsx">Excel</a>
            　　
            <a href="download_file.php/1.TNK_FaxSheet.dotx">Word</a>
            </td>
            <td class='layout' id='start'>
            　
            </td>
        </tr>
        <tr class='layout'>
            <td class='layoutb' id='start'>
                <a href="download_file.php/2.TNK_書類送付案内_2017.dotx">TNK書類送付案内</a>
            </td>
            <td class='layout' id='start'>
            　
            </td>
            <td class='layout' id='start'>
            　
            </td>
        </tr>
        <tr class='layout'>
            <td class='layoutb' id='start'>
                TNKレターヘッド
            </td>
            <td class='layout' id='start'>
            <a href="download_file.php/3.TNK和文レターヘッド_2017.dotx">和文</a>
            　　
            <a href="download_file.php/3.TNK英文レターヘッド_2017.dotx">英文</a>
            </td>
            <td class='layout' id='start'>
            　
            </td>
        </tr>
        <tr class='layout'>
            <td class='layoutb' id='start'>
                栃木日東工器外部配信用
            </td>
            <td class='layout' id='start'>
            <a href="download_file.php/4.栃木日東工器外部配信用（和文）.dotx">和文</a>
            　　
            <a href="download_file.php/4.栃木日東工器外部配信用（英文）.dotx">英文</a>
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
