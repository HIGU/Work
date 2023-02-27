<?php
//////////////////////////////////////////////////////////////////////////////
// 処理の複数分岐及び 計算中です。お待ち下さい。設備管理専用 POST型         //
// 加工実績等の表からグラフ・集計表等の分岐処理に使用する｡                  //
// Copyright(C) 2003-2004 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp       //
// 変更経歴                                                                 //
// 2003/06/26 equip_branch_msg.php templateは equip_processing_msg.php      //
// 2003/06/26 新規作成 equip_branch_msg.php                                 //
//////////////////////////////////////////////////////////////////////////////
ini_set('error_reporting',E_ALL);   // E_ALL='2047' debug 用
ini_set('display_errors','1');      // Error 表示 ON debug 用 リリース後コメント
session_start();                    // ini_set()の次に指定すること Script 最上行
require_once ("../../function.php");
access_log();       // Script Name は自動取得

///// 認証チェック
if ( !isset($_SESSION["User_ID"]) || !isset($_SESSION["Password"]) || !isset($_SESSION["Auth"]) ) {
    $_SESSION["s_sysmsg"] = "本システムを使用するためにはユーザー認証が必要です。";
    header("Location: http:" . WEB_HOST . "index1.php");
    exit();
}

///// 呼出元をセッションに保存
$_SESSION['equip_referer'] = $_SERVER["HTTP_REFERER"];

///// 分岐先スクリプトのチェック及び設定(HTMLのinput name=でscriptは使えない事に注意)
///// 他のパラメーターの取得及びチェック(汎用化したため)
if ( isset($_POST['graph_lot']) ) {                         // ロット(指示No)単位のグラフ表示
    $_SESSION['mac_no']   = $_POST['mac_no'];
    $_SESSION['siji_no']  = $_POST['siji_no'];
    $_SESSION['parts_no'] = $_POST['parts_no'];
    $_SESSION['koutei']   = $_POST['koutei'];
    $script_name          = $_POST['script_graph_lot'];
} elseif ( isset($_POST['graph_24']) ) {                    // 24時間のグラフ
    $_SESSION['mac_no']   = $_POST['mac_no'];
    $_SESSION['siji_no']  = $_POST['siji_no'];
    $_SESSION['parts_no'] = $_POST['parts_no'];
    $_SESSION['koutei']   = $_POST['koutei'];
    $script_name          = $_POST['script_graph_24'];
} elseif ( isset($_POST['detail']) ) {                      // 明細表
    $_SESSION['mac_no']   = $_POST['mac_no'];
    $_SESSION['siji_no']  = $_POST['siji_no'];
    $_SESSION['parts_no'] = $_POST['parts_no'];
    $_SESSION['koutei']   = $_POST['koutei'];
    $script_name          = $_POST['script_detail'];
} elseif ( isset($_POST['summary']) ) {                    // 集計表
    $_SESSION['mac_no']   = $_POST['mac_no'];
    $_SESSION['siji_no']  = $_POST['siji_no'];
    $_SESSION['parts_no'] = $_POST['parts_no'];
    $_SESSION['koutei']   = $_POST['koutei'];
    $script_name          = $_POST['script_summary'];
} else {
    $_SESSION["s_sysmsg"] = "メニューから指示して下さい。";
    header("Location: " . $_SERVER["HTTP_REFERER"]);
    exit();
}

///// 分岐先スクリプトのフルアドレス設定
// $replace_name = "http:" . WEB_HOST . $script_name;
$replace_name = H_WEB_HOST . $script_name;

header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");    // 日付が過去
header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT"); // 常に修正されている
header("Cache-Control: no-store, no-cache, must-revalidate");  // HTTP/1.1
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");                          // HTTP/1.0

?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<HTML>
<HEAD>
<META http-equiv="Content-Type" content="text/html; charset=EUC-JP">
<META http-equiv="Content-Style-Type" content="text/css">
<TITLE>お待ち下さい</TITLE>
<style type="text/css">
<!--
body {
    margin:20%;
    font-size:24pt;
}
-->
</style>
</HEAD>
<BODY>
    <center>処理中です。お待ち下さい。</center>
</BODY>
</HTML>
<script language='JavaScript'>
<!--
location.replace('<?php echo $replace_name ?>');        // 目的のスクリプトを呼出す
// -->
</script>
