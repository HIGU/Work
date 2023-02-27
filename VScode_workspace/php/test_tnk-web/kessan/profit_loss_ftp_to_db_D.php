<?php
//////////////////////////////////////////////////////////////////////////////
// 月次損益関係のデータ 自動FTP Download                                    //
// AS/400 ----> Web Server (PHP)                                            //
// 2003/01/17 Copyright(C) 2003-2004 K.Kobayashi tnksys@nitto-kohki.co.jp   //
// 変更経歴                                                                 //
// 2003/01/17 新規作成  profit_loss_ftp_to_db_D.php                         //
// 2003/01/24 データベースへの取り込みロジックを追加                        //
// 2003/01/27 データベースへの取り込みを小分けするためファイル名変更        //
//            経費実績内訳表用のデータ取り込み 表ID=D                       //
// 2003/01/28 データベースのフィールド追加 対象期(ki=3など)                 //
// 2004/02/05 AS/400 の対象年月のチェック機能追加 kin9 != $yyyymm           //
//////////////////////////////////////////////////////////////////////////////
ini_set('error_reporting',E_ALL);   // E_ALL='2047' debug 用
// ini_set('display_errors','1');      // Error 表示 ON debug 用 リリース後コメント
session_start();                    // ini_set()の次に指定すること Script 最上行
require_once("../function.php");
require_once("../tnk_func.php");

    ///// 対象年月の取得
$yyyymm = 202211;
    ///// 期の取得
$ki = 22;

    ///// AS/400 の ライブラリとファイル名設定
$as_lib_file = "UKFLIB/WCPLBSP";
    ///// Dounload File Name 設定
$file_orign = "WCPLBSP.TXT";
    ///// Dounload file 内容説明
$file_note  = "経費明細表 内訳科目 D";

$ok_row = 20;

$_SESSION['s_sysmsg'] .= sprintf("<br>%d %s %d 件 取り込み完了", $yyyymm, $file_note, $ok_row);

?>
<!DOCTYPE html>
<HTML>
<HEAD>
<META http-equiv="Content-Type" content="text/html; charset=UTF-8">
<META http-equiv="Content-Style-Type" content="text/css">
<TITLE>月次損益 FTP Download </TITLE>
<style type="text/css">
<!--
body        {margin:20%;font-size:24pt;}
-->
</style>
</HEAD>
<BODY>
    <center>AS/400 と データリンク 完了</center>

    <script language="JavaScript">
    <!--
        //location = 'http:<?php echo(WEB_HOST) . "kessan/profit_loss_select.php" ?>';
    -->
    </script>
</BODY>
</HTML>
