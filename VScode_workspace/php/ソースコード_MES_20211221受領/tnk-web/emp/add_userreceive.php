<?php
//////////////////////////////////////////////////////////////////////////////
// �Ұ���������� ������ɲý���                                            //
// Copyright (C) 2001-2005 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp      //
// Changed history                                                          //
// 2001/07/07 Created   add_userreceive.php                                 //
// 2002/08/07 register_globals = Off �б� & ���å�������                  //
// 2005/01/17 access_log ���ѹ��� view_file_name(__FILE__) ���ɲ�           //
//////////////////////////////////////////////////////////////////////////////
// ini_set('error_reporting', E_ALL);          // E_ALL='2047' debug ��
// ini_set('display_errors', '1');             // Error ɽ�� ON debug �� ��꡼���女����
session_start();                            // ini_set()�μ��˻��ꤹ�뤳�� Script �Ǿ��
require_once ('../function.php');           // ���� �ؿ�
require_once ('emp_function.php');          // �Ұ���˥塼����
access_log();                               // Script Name ��ư����

$_SESSION['userid'] = $_POST['userid'];
$_SESSION['recv']   = 1;

$begin_date = $_POST['begin_date_1'] . "-" . $_POST['begin_date_2'] . "-" . $_POST['begin_date_3'];
$end_date   = $_POST['end_date_1'] . "-" . $_POST['end_date_2'] . "-" . $_POST['end_date_3'];
if (addReceive($_POST['userid'], $_POST['receive'], $begin_date,$end_date)) {
    header('Location: ' . H_WEB_HOST . EMP . 'emp_menu.php?func=' . FUNC_ADMINUSERINFO);
    exit();
}
$_SESSION['s_sysmsg'] = '�桼�����ζ���˴ؤ����ѹ��˼��Ԥ��ޤ�����<br>�����Ԥˤ��䤤��碌����������';
header('Location: ' . H_WEB_HOST . EMP . 'emp_menu.php?func=' . FUNC_ADMINUSERINFO);
?>
