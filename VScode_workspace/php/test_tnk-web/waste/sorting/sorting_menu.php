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
//            給与所得者の～申告書を28年度へ更新                            //
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
// 2022/01/07 マイカー通勤手当（変更）届、パート通勤状況届の改定(22/01) 和氣//
// 2022/02/07 マイカー通勤手当（変更）届、パート通勤状況届の改定(22/02) 和氣//
// 2022/03/04 マイカー通勤手当（変更）届、パート通勤状況届の改定(22/03) 和氣//
// 2022/05/12 マイカー通勤手当（変更）届、パート通勤状況届の改定(22/05) 和氣//
// 2022/06/02 マイカー通勤手当（変更）届、パート通勤状況届の改定(22/06) 和氣//
// 2022/06/08 「総合届」「公用・私用外出許可証」削除。web申請の為           //
//            「回数券使用申込書」削除。回数券廃止の為                  和氣//
//                                                                          //
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
$menu->set_title('廃棄物の分別方法');
//////////// 表題の設定
$menu->set_caption('廃棄物の分別方法');

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
<script type='text/javascript' language='JavaScript' src='../waste_appli.js'></script>
<link rel='stylesheet' href='../waste_appli.css' type='text/css' media='screen'>
</head>
<body onLoad='Regu.set_focus(document.getElementById("start", ""))'>
    <center>
<?= $menu->out_title_border() ?>
    <div class='pt12b'>&nbsp;</div>
     <B>
    　　　　　　　　　　　　　　　　　　　　　　　　　　　　　　　　　　　　　　　　　　　　　
    </B>
    <BR><B>
    ・各分別方法をクリックすると、『開く』・『保存』のメニューが表示されますので<BR>
      パソコンに保管し、確認して下さい。
    </B><BR>
     <B>
    　　　　　　　　　　　　　　　　　　　　　　　　　　　　　　　　　　　　　　　　　　　　　
    <BR>
    ※廃却する際は各分別方法を確認して正しく分別・廃却を行ってください。
    <BR>
    ※分別方法で分からない点は、総務課までご連絡下さい。
    </B>
    <BR><BR><BR>
    <table class='layout'>
        <tr class='layout'>
            <td class='layoutg' id='start' align='center'>
            項目
            </td>
            <td class='layoutg' id='start'>
            添付書類
            </td>
            <td class='layoutg' id='start'>
            備考
            </td>
        </tr>
        <tr class='layout'>
            <td class='layoutg' id='start' rowspan='5' nowrap>
            ●廃棄物の<BR>　分別方法
            </td>
            <td class='layoutb' id='start' nowrap>
                <a href="download_file.php/202206機密文書廃却.doc">機密文書の分別</a>
            </td>
            <td class='layout' id='start'>
            　
            </td>
        </tr>
        <tr class='layout'>
            <td class='layoutb' id='start' nowrap>
                <a href="download_file.php/202206混合廃棄物.doc">混合廃棄物</a>
            </td>
            <td class='layout' id='start'>
            　
            </td>
        </tr>
        <tr class='layout'>
            <td class='layoutb' id='start' nowrap>
                <a href="download_file.php/202206プラスチック類.doc">プラスチック類</a>
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
