<?php
//////////////////////////////////////////////////////////////////////////////
// �Ұ���������� include file �������κ������                           //
// Copyright (C) 2001-2006 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp      //
// Changed history                                                          //
// 2001/07/07 Created   del_userreceive.php                                 //
// 2002/08/07 register_globals = Off �б� & ���å�������                  //
// 2005/01/17 access_log ���ѹ��� view_file_name(__FILE__) ���ɲ�           //
// 2006/02/13 delReceive()�ؿ��ΰ����� oid �� rid, begin_date, end_date ��  //
//////////////////////////////////////////////////////////////////////////////
// ini_set('error_reporting', E_ALL);          // E_ALL='2047' debug ��
// ini_set('display_errors', '1');             // Error ɽ�� ON debug �� ��꡼���女����
session_start();                            // ini_set()�μ��˻��ꤹ�뤳�� Script �Ǿ��
require_once ('../function.php');           // ���� �ؿ�
require_once ('emp_function.php');          // �Ұ���˥塼����
access_log();                               // Script Name ��ư����

$_SESSION['userid'] = $_POST['userid'];
$_SESSION['recv']   = 1;

// if ( delReceive($_POST['userid'], $_POST['oid']) ) {
if ( delReceive($_POST['userid'], $_POST['rid'], $_POST['begin_date'], $_POST['end_date']) ) {
    header('Location: ' . H_WEB_HOST . EMP . 'emp_menu.php?func=' . FUNC_ADMINUSERINFO);
    exit();
}
$_SESSION['s_sysmsg'] = '�桼�����ζ���˴ؤ����ѹ��˼��Ԥ��ޤ�����<br>�����Ԥˤ��䤤��碌����������';
header('Location: ' . H_WEB_HOST . EMP . 'emp_menu.php?func=' . FUNC_ADMINUSERINFO);
?>
