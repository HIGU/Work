<?php
//////////////////////////////////////////////////////////////////////////////
// 注残 予定 納期の照会(検査の仕事量把握)    分岐処理 メッセージ出力        //
// Copyright (C) 2004-2004 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp      //
// Changed history                                                          //
// 2004/09/29 Created  order_branch.php                                     //
//////////////////////////////////////////////////////////////////////////////
ini_set('error_reporting',E_ALL);       // E_ALL='2047' debug 用
// ini_set('display_errors','1');          // Error 表示 ON debug 用 リリース後コメント
session_start();                        // ini_set()の次に指定すること Script 最上行
require_once ('../../function.php');
access_log();                           // Script Name は自動取得

if (isset($_REQUEST['script_name'])) {
    $script_name = $_REQUEST['script_name'];
} else {
    $script_name = INDUST_MENU;
}
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<meta http-equiv="Content-Style-Type" content="text/css">
<title>分岐処理(生産)(order)</title>
<style type="text/css">
<!--
body {
    margin:     20%;
    font-size:  24pt;
}
-->
</style>
<form name='branch_form' method='post' action='<?=$script_name?>'>
</form>
</head>
<body onLoad='document.branch_form.submit()'>
    <center>
        処理中です。お待ち下さい。<br>
        <img src='../../img/tnk-turbine.gif' width=68 height=72>
    </center>
</body>
</html>
