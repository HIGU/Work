<?php
//////////////////////////////////////////////////////////////////////////////
// �Ұ���������� ���鎥������ͭ����̵�� ����                                 //
// Copyright (C) 2001-2005 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp      //
// Changed history                                                          //
// 2001/07/07 Created  select_receive.php                                   //
// 2002/08/07 register_globals = Off �б� & ���å�������                  //
// 2004/03/31 ���󥫡� #receive �� �ɲ�                                     //
// 2005/01/17 access_log ���ѹ��� view_file_name(__FILE__) ���ɲ�           //
//////////////////////////////////////////////////////////////////////////////
// ini_set('error_reporting', E_ALL);          // E_ALL='2047' debug ��
// ini_set('display_errors', '1');             // Error ɽ�� ON debug �� ��꡼���女����
session_start();                            // ini_set()�μ��˻��ꤹ�뤳�� Script �Ǿ��
require_once ('../function.php');           // ���� �ؿ�
require_once ('emp_function.php');          // �Ұ���˥塼����
access_log();                               // Script Name ��ư����

if($_SESSION['Auth'] < 2){ 
    $_SESSION['s_sysmsg'] = '���ʤ��ˤϸ��¤�����ޤ���<br>�����Ԥˤ��䤤��碌��������';
    header('Location: ' . H_WEB_HOST . EMP .'emp_menu.php');
    exit();
}

if(indReceive($_POST['rid'],$_POST['rflg'])){
    header('Location: ' . H_WEB_HOST . EMP . 'emp_menu.php?func=' . FUNC_CHGINDICATE . '#receive');
    exit();
}
$_SESSION['s_sysmsg'] .= '������ɲ��ѹ��˼��Ԥ��ޤ�����<br>����̾����ʣ���Ƥ��ޤ���';
header('Location: ' . H_WEB_HOST . EMP . 'emp_menu.php?func=' . FUNC_CHGINDICATE . '#receive');
?>
