<?php
//////////////////////////////////////////////////////////////////////////////
// �Ұ���������� include file ���Ȱ�����δ������                         //
// Copyright (C) 2001-2005 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp      //
// Changed history                                                          //
// 2001/07/07 Created   del_userinfo.php                                    //
// 2002/08/07 register_globals = Off �б� & ���å��������б�              //
// 2005/01/17 access_log ���ѹ��� view_file_name(__FILE__) ���ɲ�           //
//              ������˥ƥ����ȥե�����˥Хå����åפ�Ĥ�ͽ�� \copy��    //
//////////////////////////////////////////////////////////////////////////////
// ini_set('error_reporting', E_ALL);          // E_ALL='2047' debug ��
// ini_set('display_errors', '1');             // Error ɽ�� ON debug �� ��꡼���女����
session_start();                            // ini_set()�μ��˻��ꤹ�뤳�� Script �Ǿ��
require_once ('../function.php');           // ���� �ؿ�
require_once ('emp_function.php');          // �Ұ���˥塼����
access_log();                               // Script Name ��ư����

$user = trim($_POST['acount']);
if ( delUser($_POST['userid'], $_POST['photoid'], $user) ) {
    if ($_SESSION['retireflg'] == 0) {
        header('Location: ' . H_WEB_HOST . EMP . 'emp_menu.php?func=' . FUNC_LOOKUP);
        exit();
    } else {
        header('Location: ' . H_WEB_HOST . EMP . 'emp_menu.php?func=' . FUNC_RETIREINFO);
        exit();
    }
}
$_SESSION['s_sysmsg'] = '�桼������������ä˼��Ԥ��ޤ�����<br>���ξ��֤�³���褦�Ǥ���������Ԥˤ��䤤��碌����������';
if ($_SESSION['retireflg'] == 0) {
    header('Location: ' . H_WEB_HOST . EMP . 'emp_menu.php?func=' . FUNC_LOOKUP);
} else {
    header('Location: ' . H_WEB_HOST . EMP . 'emp_menu.php?func=' . FUNC_RETIREINFO);
}
?>
