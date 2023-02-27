<?php
//////////////////////////////////////////////////////////////////////////////
// 月次損益関係 自動FTP Download  科目別・部門経費 *** 明細 ***             //
// AS/400 ----> Web Server (PHP) TNKACT → 77 → 77 → 31 → 4              //
// 2003/01/31 Copyright(C) 2003-2004 K.Kobayashi tnksys@nitto-kohki.co.jp   //
// 変更経歴                                                                 //
// 2003/01/31 新規作成  profit_loss_ftp_to_db_B1.php                        //
//              データは B 表と同時期に作成される                           //
//              select sum(kin) from bm_km_summary where actcod=8103        //
//              and act_id<>900 and k_kubun='1' and                         //
//              (act_id=173 or act_id=174 or act_id=500)                    //
// 2003/02/28 データベースへの登録をトランザクションに変更                  //
// 2003/06/06 部門マスター等が変更された場合に既に登録されているか          //
//            登録されていないか１レコード毎にチェックするように変更        //
// 2004/02/05 成功時のメッセージ文字色を白へ変更  機種依存文字の№ → No へ //
//            現在は AS/400 の WCBMKMP に対象年月をチェックする項目はない   //
//////////////////////////////////////////////////////////////////////////////
ini_set('error_reporting',E_ALL);   // E_ALL='2047' debug 用
// ini_set('display_errors','1');      // Error 表示 ON debug 用 リリース後コメント
session_start();                    // ini_set()の次に指定すること Script 最上行
require_once ("../function.php");
require_once ("../tnk_func.php");
access_log();       // Script Name は自動取得
if (account_group_check() == FALSE) {
    $_SESSION["s_sysmsg"] = "あなたは許可されていません。<br>管理者に連絡して下さい。";
    header("Location: http:" . WEB_HOST . "kessan/kessan_menu.php");
    exit();
}

    ///// 対象年月の取得
$yyyymm = 202211;
    ///// 期の取得
$ki = 22;


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
        location = 'http:<?php echo(WEB_HOST) . "kessan/profit_loss_select.php" ?>';
    // -->
    </script>
</BODY>
</HTML>
