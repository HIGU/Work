<?php
//////////////////////////////////////////////////////////////////////////////
// �׻���Ǥ������Ԥ�����������ɽ�� (���ѥ�����) �ʎߎ׎Ҏ���POST̤�б�           //
// ��������ν������֤�������HTTP�ꥯ�����Ȥκݤ˴֤˳��ޤ���               //
// Copyright(C) 2002-2004 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp       //
// �ѹ�����                                                                 //
// 2002/09/22 �������� processing_msg.php                                   //
// 2002/09/22 �����ط��ճ��˻��� POST �ǡ�����ɬ�פȤ��ʤ���������˻��ѡ�  //
// 2003/11/27 tnk-turbine.gif �Υ��˥᡼�������ɲ�                        //
// 2003/12/17 access_log()�򥳥��ȥ�����                                  //
// 2004/04/27 WEB_HOST �� H_WEB_HOST ���ѹ�(http://hostname//sales)������   //
//////////////////////////////////////////////////////////////////////////////
ini_set('error_reporting',E_ALL);   // E_ALL='2047' debug ��
require_once ('./function.php');
session_start();
// access_log();       // Script Name �ϼ�ư����
if ( isset($_POST['script']) ) {
    $script_name = $_POST['script'];        // POST �ξ��θƽХ�����ץ�
} elseif ( isset($_GET['script']) ) {
    $script_name = $_GET['script'];         // GET �ξ��θƽХ�����ץ�
} else {
    $_SESSION['s_sysmsg'] = '��˥塼����ؼ����Ʋ�������';
    header('Location: ' . $_SERVER['HTTP_REFERER']);
    exit();
}
// $replace_name = 'http:' . WEB_HOST . $script_name;      // �ƽХ�����ץȤΥե륢�ɥ쥹��
$replace_name = H_WEB_HOST . $script_name;      // �ƽХ�����ץȤΥե륢�ɥ쥹��

if ( !isset($_SESSION['User_ID']) || !isset($_SESSION['Password']) || !isset($_SESSION['Auth']) ) {
    $_SESSION['s_sysmsg'] = '�ܥ����ƥ����Ѥ��뤿��ˤϥ桼����ǧ�ڤ�ɬ�פǤ���';
    header('Location: http:' . WEB_HOST . 'index1.php');
    exit();
}

header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');    // ���դ����
header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT'); // ��˽�������Ƥ���
header('Cache-Control: no-store, no-cache, must-revalidate');  // HTTP/1.1
header('Cache-Control: post-check=0, pre-check=0', false);
header('Pragma: no-cache');                          // HTTP/1.0
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<HTML>
<HEAD>
<META http-equiv="Content-Type" content="text/html; charset=EUC-JP">
<META http-equiv="Content-Style-Type" content="text/css">
<TITLE>���Ԥ�������</TITLE>
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
        �׻���Ǥ������Ԥ���������<br>
        <img src='img/tnk-turbine.gif' width=68 height=72>
    </center>
</BODY>
</HTML>
<script language='JavaScript'>
<!--
location.replace('<?php echo $replace_name ?>');        // ��Ū�Υ�����ץȤ�ƽФ�
// -->
</script>
