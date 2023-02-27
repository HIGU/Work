<?php
//////////////////////////////////////////////////////////////////////////////
// 届出・申請書メニュー（社外 その他の届出）                                //
// Copyright (C) 2014-2014 Norihisa.Ohya norihisa_ooya@nitto-kohki.co.jp    //
// Changed history                                                          //
// 2014/09/29 Created  out_other_appli.php                                  //
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
$menu->set_title('届出・申請書メニュー（社外 その他）');
//////////// 表題の設定
$menu->set_caption('届出・申請書メニュー（社外 その他）');

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
            <td class='layoutg' id='start' rowspan='3'>
            ●コンピューター関連の届出
            </td>
            <td class='layoutg' id='start'>
            0101
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
            <td class='layoutg' id='start'>
            0102
            </td>
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
            <td class='layoutg' id='start'>
            0103
            </td>
            <td class='layoutb' id='start'>
                <a href="download_file.php/アクセス権申請書20080407.xls">アクセス権申請書</a>
            </td>
            <td class='layout' id='start'>
            　
            </td>
            <td class='layout' id='start'>
            メール・端末 追加依頼時に提出
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
