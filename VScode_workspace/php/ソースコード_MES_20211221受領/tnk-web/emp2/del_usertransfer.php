<?php
//////////////////////////////////////////////////////////////////////////////
// �Ұ���������� ��° ����κ�� & ��°̾�ѹ� ����                         //
// Copyright (C) 2001-2005 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp      //
// Changed history                                                          //
// 2001/07/07 Created   del_usertransfer.php                                //
// 2002/08/07 register_globals = Off �б� & ���å�������                  //
// 2005/01/17 access_log ���ѹ��� view_file_name(__FILE__) ���ɲ�           //
// 2006/01/11 oid ���ѻߤ��� trans_date ���ѹ�                              //
// 2006/02/14 chg_Sectionname() �� oid �� trans_date, sid ���ѹ�            //
//////////////////////////////////////////////////////////////////////////////
// ini_set('error_reporting', E_ALL);          // E_ALL='2047' debug ��
// ini_set('display_errors', '1');             // Error ɽ�� ON debug �� ��꡼���女����
session_start();                            // ini_set()�μ��˻��ꤹ�뤳�� Script �Ǿ��
require_once ('../function.php');           // ���� �ؿ�
require_once ('emp_function.php');          // �Ұ���˥塼����
access_log();                               // Script Name ��ư����

$_SESSION['userid'] = $_POST['userid'];
$_SESSION['sect']   = 1;

if ( isset($_POST['del']) ) {
    // if ( delTransfer($_POST['userid'], $_POST['oid'], $_POST['sid']) ) {
    if ( delTransfer($_POST['userid'], $_POST['trans_date'], $_POST['sid']) ) {
        header('Location: ' . H_WEB_HOST . EMP . 'emp_menu.php?func=' . FUNC_ADMINUSERINFO);
        exit();
    }
    $_SESSION['s_sysmsg'] = '�桼�����ν�°�κ���˼��Ԥ��ޤ�����<br>�����Ԥˤ��䤤��碌����������';
    header('Location: ' . H_WEB_HOST . EMP . 'emp_menu.php?func=' . FUNC_ADMINUSERINFO);
} elseif ( isset($_POST['chg']) ) {
    // if ( chg_Sectionname($_POST['userid'], $_POST['oid'], $_POST['section_name']) ) {
    if ( chg_Sectionname($_POST['userid'], $_POST['trans_date'], $_POST['sid'], $_POST['section_name']) ) {
        header('Location: ' . H_WEB_HOST . EMP . 'emp_menu.php?func=' . FUNC_ADMINUSERINFO);
        exit();
    }
    $_SESSION['s_sysmsg'] = '�桼�����ν�°̾���ѹ��˼��Ԥ��ޤ�����<br>�����Ԥˤ��䤤��碌����������';
    header('Location: ' . H_WEB_HOST . EMP . 'emp_menu.php?func=' . FUNC_ADMINUSERINFO);
}
?>
