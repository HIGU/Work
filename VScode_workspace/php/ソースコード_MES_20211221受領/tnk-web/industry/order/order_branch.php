<?php
//////////////////////////////////////////////////////////////////////////////
// ��� ͽ�� Ǽ���ξȲ�(�����λŻ����İ�)    ʬ������ ��å���������        //
// Copyright (C) 2004-2004 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp      //
// Changed history                                                          //
// 2004/09/29 Created  order_branch.php                                     //
//////////////////////////////////////////////////////////////////////////////
ini_set('error_reporting',E_ALL);       // E_ALL='2047' debug ��
// ini_set('display_errors','1');          // Error ɽ�� ON debug �� ��꡼���女����
session_start();                        // ini_set()�μ��˻��ꤹ�뤳�� Script �Ǿ��
require_once ('../../function.php');
access_log();                           // Script Name �ϼ�ư����

if (isset($_REQUEST['script_name'])) {
    $script_name = $_REQUEST['script_name'];
} else {
    $script_name = INDUST_MENU;
}
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=EUC-JP">
<meta http-equiv="Content-Style-Type" content="text/css">
<title>ʬ������(����)(order)</title>
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
        ������Ǥ������Ԥ���������<br>
        <img src='../../img/tnk-turbine.gif' width=68 height=72>
    </center>
</body>
</html>
