<?php
//////////////////////////////////////////////////////////////////////////////
// �������칩�� �ե졼��(�����ȥ�˥塼)ɽ�� ON/OFF                         //
// Copyright(C) 2003-2005 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp       //
// Changed history                                                          //
// 2003/12/15 Created   menu_frame_OnOff.php                                //
// 2004/07/21 <title>TNK Web MenuOFF��TNK Web System�� ������ʸ�����ѹ�   //
// 2005/08/06 site_view�Υ��å����¸�ߥ����å����ɲ�(window�򳫤��Ƥ��ʤ�) //
// 2005/09/05 ����<frameset��name='topFrame'���ɲú����menu_frame.php������//
//////////////////////////////////////////////////////////////////////////////
ini_set('error_reporting', E_ALL);      // E_ALL='2047' debug ��
// ini_set('display_errors', '1');         // Error ɽ�� ON debug �� ��꡼���女����
session_start();                        // ini_set()�μ��˻��ꤹ�뤳�� Script �Ǿ��
require_once ('function.php');          // TNK Web ������function
access_log();                           // Script Name �ϼ�ư����

///// menu_frame_OnOff.php �Ϻ�����Ѥ��ʤ��ʤ뤿�� menu_frame.php �إ�����쥯�Ȥ���
///// 2005/09/05 �ɲ�
header('Location: ' . H_WEB_HOST . TOP . 'menu_frame.php?' . $_SERVER['QUERY_STRING']);
exit;

////////////// �¹ԥ�����ץ�̾�μ���
// if ( isset($_SERVER['HTTP_REFERER']) ) {     // �� ����ϻȤ��ʤ�
//     $exec_name = $_SERVER['HTTP_REFERER'];
if ( isset($_GET['name']) ) {
    $exec_name = $_GET['name'];
} else {
    $exec_name = TOP_MENU;
}

////////////// ɽ���� On Off ����
if (!isset($_SESSION['site_view'])) $_SESSION['site_view'] = 'off';
if ($_SESSION['site_view'] == 'on') {
    $_SESSION['site_view'] = 'off';
    $frame_cols = '0%,*';
} else {
    $_SESSION['site_view'] = 'on';
    $frame_cols = '10%,*';
}
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Frameset//EN"
    "http://www.w3.org/TR/html4/frameset.dtd">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=EUC-JP">
<meta http-equiv="Content-Style-Type" content="text/css">
<title>TNK Web System</title>
</head>
<frameset name='topFrame' cols='<?php echo $frame_cols ?>' border='0' onLoad='self.focus()'>
    <frame src='menu_site.php' name='menu_site' scrolling='no'>
    <frame src='<?php echo $exec_name ?>' name='application'>
</frameset>
<noframes>
<p>�������칩��(��)��Web�����ȤǤϥե졼���Ȥ�����ˤʤäƤ��ޤ���</p>
<p>�ե졼�����Ѥ��ʤ�����ˤ��Ƥ�������ѹ����Ʋ�������</p>
<p>̤�б��Υ֥饦�����ξ����б��֥饦�������ѹ����Ʋ�������<p>
</noframes>
</html>
