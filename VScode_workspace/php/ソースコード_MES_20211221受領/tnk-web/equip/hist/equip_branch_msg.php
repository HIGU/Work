<?php
//////////////////////////////////////////////////////////////////////////////
// ������ʣ��ʬ���ڤ� �׻���Ǥ������Ԥ��������������������� POST��         //
// �ù���������ɽ���饰��ա�����ɽ����ʬ�������˻��Ѥ��뎡                  //
// Copyright(C) 2003-2004 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp       //
// �ѹ�����                                                                 //
// 2003/06/26 equip_branch_msg.php template�� equip_processing_msg.php      //
// 2003/06/26 �������� equip_branch_msg.php                                 //
//////////////////////////////////////////////////////////////////////////////
ini_set('error_reporting',E_ALL);   // E_ALL='2047' debug ��
ini_set('display_errors','1');      // Error ɽ�� ON debug �� ��꡼���女����
session_start();                    // ini_set()�μ��˻��ꤹ�뤳�� Script �Ǿ��
require_once ("../../function.php");
access_log();       // Script Name �ϼ�ư����

///// ǧ�ڥ����å�
if ( !isset($_SESSION["User_ID"]) || !isset($_SESSION["Password"]) || !isset($_SESSION["Auth"]) ) {
    $_SESSION["s_sysmsg"] = "�ܥ����ƥ����Ѥ��뤿��ˤϥ桼����ǧ�ڤ�ɬ�פǤ���";
    header("Location: http:" . WEB_HOST . "index1.php");
    exit();
}

///// �ƽи��򥻥å�������¸
$_SESSION['equip_referer'] = $_SERVER["HTTP_REFERER"];

///// ʬ���襹����ץȤΥ����å��ڤ�����(HTML��input name=��script�ϻȤ��ʤ��������)
///// ¾�Υѥ�᡼�����μ����ڤӥ����å�(���Ѳ���������)
if ( isset($_POST['graph_lot']) ) {                         // ��å�(�ؼ�No)ñ�̤Υ����ɽ��
    $_SESSION['mac_no']   = $_POST['mac_no'];
    $_SESSION['siji_no']  = $_POST['siji_no'];
    $_SESSION['parts_no'] = $_POST['parts_no'];
    $_SESSION['koutei']   = $_POST['koutei'];
    $script_name          = $_POST['script_graph_lot'];
} elseif ( isset($_POST['graph_24']) ) {                    // 24���֤Υ����
    $_SESSION['mac_no']   = $_POST['mac_no'];
    $_SESSION['siji_no']  = $_POST['siji_no'];
    $_SESSION['parts_no'] = $_POST['parts_no'];
    $_SESSION['koutei']   = $_POST['koutei'];
    $script_name          = $_POST['script_graph_24'];
} elseif ( isset($_POST['detail']) ) {                      // ����ɽ
    $_SESSION['mac_no']   = $_POST['mac_no'];
    $_SESSION['siji_no']  = $_POST['siji_no'];
    $_SESSION['parts_no'] = $_POST['parts_no'];
    $_SESSION['koutei']   = $_POST['koutei'];
    $script_name          = $_POST['script_detail'];
} elseif ( isset($_POST['summary']) ) {                    // ����ɽ
    $_SESSION['mac_no']   = $_POST['mac_no'];
    $_SESSION['siji_no']  = $_POST['siji_no'];
    $_SESSION['parts_no'] = $_POST['parts_no'];
    $_SESSION['koutei']   = $_POST['koutei'];
    $script_name          = $_POST['script_summary'];
} else {
    $_SESSION["s_sysmsg"] = "��˥塼����ؼ����Ʋ�������";
    header("Location: " . $_SERVER["HTTP_REFERER"]);
    exit();
}

///// ʬ���襹����ץȤΥե륢�ɥ쥹����
// $replace_name = "http:" . WEB_HOST . $script_name;
$replace_name = H_WEB_HOST . $script_name;

header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");    // ���դ����
header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT"); // ��˽�������Ƥ���
header("Cache-Control: no-store, no-cache, must-revalidate");  // HTTP/1.1
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");                          // HTTP/1.0

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
-->
</style>
</HEAD>
<BODY>
    <center>������Ǥ������Ԥ���������</center>
</BODY>
</HTML>
<script language='JavaScript'>
<!--
location.replace('<?php echo $replace_name ?>');        // ��Ū�Υ�����ץȤ�ƽФ�
// -->
</script>
