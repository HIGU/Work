<?php
//////////////////////////////////////////////////////////////////////////////
// 月次損益関係のデータ 自動FTP Download   全データ                         //
// AS/400 ----> Web Server (PHP)                                            //
// 2003/01/17 Copyright(C) 2003-2009 K.Kobayashi tnksys@nitto-kohki.co.jp   //
// Changed history                                                          //
// 2003/01/17 Created   profit_loss_ftp_to_db_all.php                       //
// 2003/01/24 データベースへの取り込みロジックを追加                        //
// 2003/01/27 データベースへの取り込みを小分けするためファイル名変更        //
// 2003/01/28 データベースのフィールド追加 対象期(ki=3など)                 //
// 2003/02/28 データベースへの登録をトランザクションに変更                  //
// 2004/02/05 成功時のメッセージ文字色を白へ変更  機種依存文字の№ → No へ //
//            AS/400のデータ 対象年月のチェックをするようにロジック追加     //
// 2004/03/03 対象年月のチェックロジック位置を変更 EOFで引っ掛かるため      //
// 2006/04/06 fclose($fp)の記述が年月errorのロジックに２重であったので削除  //
// 2009/08/18 試験・修理の売上計算を追加しようとしたが別プログラムに        //
//            追加した為削除                                           大谷 //
//////////////////////////////////////////////////////////////////////////////
ini_set('error_reporting',E_ALL);   // E_ALL='2047' debug 用
// ini_set('display_errors','1');      // Error 表示 ON debug 用 リリース後コメント
session_start();                    // ini_set()の次に指定すること Script 最上行
require_once ("../function.php");
require_once ("../tnk_func.php");

$file_note  = "月次損益データ ALL";
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
    <center>
        <font color='blue'><?php echo "$file_note の処理 完了<br><br>" ?></font>
        科目別部門経費 データ処理中
    </center>

    <script language="JavaScript">
    </script>
</BODY>
</HTML>
