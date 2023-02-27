<?php
//////////////////////////////////////////////////////////////////////////////
// 計算中です。お待ち下さい。の表示 (共用タイプ) ﾊﾟﾗﾒｰﾀPOST未対応           //
// グラフ等の処理時間がかかるHTTPリクエストの際に間に噛ます。               //
// Copyright(C) 2002-2004 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp       //
// 変更経歴                                                                 //
// 2002/09/22 新規作成 processing_msg.php                                   //
// 2002/09/22 設備関係意外に使用 POST データを必要としないグラフ等に使用。  //
// 2003/11/27 tnk-turbine.gif のアニメーションを追加                        //
// 2003/12/17 access_log()をコメントアウト                                  //
// 2004/04/27 WEB_HOST → H_WEB_HOST へ変更(http://hostname//sales)を訂正   //
//////////////////////////////////////////////////////////////////////////////
ini_set('error_reporting',E_ALL);   // E_ALL='2047' debug 用
require_once ('./function.php');
session_start();
// access_log();       // Script Name は自動取得
if ( isset($_POST['script']) ) {
    $script_name = $_POST['script'];        // POST の場合の呼出スクリプト
} elseif ( isset($_GET['script']) ) {
    $script_name = $_GET['script'];         // GET の場合の呼出スクリプト
} else {
    $_SESSION['s_sysmsg'] = 'メニューから指示して下さい。';
    header('Location: ' . $_SERVER['HTTP_REFERER']);
    exit();
}
// $replace_name = 'http:' . WEB_HOST . $script_name;      // 呼出スクリプトのフルアドレス｡
$replace_name = H_WEB_HOST . $script_name;      // 呼出スクリプトのフルアドレス｡

if ( !isset($_SESSION['User_ID']) || !isset($_SESSION['Password']) || !isset($_SESSION['Auth']) ) {
    $_SESSION['s_sysmsg'] = '本システムを使用するためにはユーザー認証が必要です。';
    header('Location: http:' . WEB_HOST . 'index1.php');
    exit();
}

header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');    // 日付が過去
header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT'); // 常に修正されている
header('Cache-Control: no-store, no-cache, must-revalidate');  // HTTP/1.1
header('Cache-Control: post-check=0, pre-check=0', false);
header('Pragma: no-cache');                          // HTTP/1.0
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<HTML>
<HEAD>
<META http-equiv="Content-Type" content="text/html; charset=UTF-8">
<META http-equiv="Content-Style-Type" content="text/css">
<TITLE>お待ち下さい</TITLE>
<style type="text/css">
<!--
body {
    margin:20%;
    font-size:24pt;
}
select {
    background-color:teal;
    color:white;
}
textarea {
    background-color:black;
    color:white;
}
input.sousin {
    background-color:red;
}
input.text {
    background-color:black;
    color:white;
}
.pt11 {
    font-size:11pt;
}
.margin1 {
    margin:1%;
}
-->
</style>
</HEAD>
<BODY>
    <center>
        計算中です。お待ち下さい。<br>
        <img src='img/tnk-turbine.gif' width=68 height=72>
    </center>
</BODY>
</HTML>
<script language='JavaScript'>
<!--
location.replace('<?php echo $replace_name ?>');        // 目的のスクリプトを呼出す
// -->
</script>
