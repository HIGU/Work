<?php
//////////////////////////////////////////////////////////////////////////////
// �Ұ���������� ��°(����)����̾���ɲ��ѹ�                                //
// Copyright (C) 2001-2010 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp      //
// Changed history                                                          //
// 2001/07/07 Created   addselect_section.php                               //
// 2002/08/07 register_globals = Off �б� & ���å�������                  //
// 2003/10/22 �����ƥ��å�������[��ʣ]����[�����ɲä˼���]���ѹ� .=       //
//            (addSection�ǥ�å���������¸���뤿��)  #anchor ���ɲ�        //
// 2005/01/17 access_log ���ѹ��� view_file_name(__FILE__) ���ɲ�           //
// 2010/03/11 ����Ū����޼�����970268�ˤ���Ͽ�Ǥ���褦���ѹ�         ��ë //
// 2019/01/31 ����Ū��ʿ�Ф����300551�ˤ���Ͽ�Ǥ���褦���ѹ�         ��ë //
//////////////////////////////////////////////////////////////////////////////
// ini_set('error_reporting', E_ALL);          // E_ALL='2047' debug ��
// ini_set('display_errors', '1');             // Error ɽ�� ON debug �� ��꡼���女����
session_start();                            // ini_set()�μ��˻��ꤹ�뤳�� Script �Ǿ��
require_once ('../function.php');           // ���� �ؿ�
require_once ('emp_function.php');          // �Ұ���˥塼����
access_log();                               // Script Name ��ư����

if ($_SESSION['Auth'] < 2) { 
    if ($_SESSION['User_ID'] != '970268' && $_SESSION['User_ID'] != '300551') {
        $_SESSION['s_sysmsg'] = '���ʤ��ˤϸ��¤�����ޤ���<br>�����Ԥˤ��䤤��碌��������';
        header('Location: ' . H_WEB_HOST . EMP . 'emp_menu.php');
        exit();
    }
}

if (addselectSection($_POST['sid'], $_POST['section_name'], $_POST['sflg'])) {
    header('Location: ' . H_WEB_HOST . EMP . 'emp_menu.php?func=' . FUNC_CHGINDICATE . '#section');
    exit();
}
$_SESSION['s_sysmsg'] .= '<br>��°�ι��� �ɲ��ѹ�������ޤ���Ǥ�����';  // addselectSection�ǥ��顼�ξ��˥�å����������뤿��.=���ѹ�
header('Location: ' . H_WEB_HOST . EMP . 'emp_menu.php?func=' . FUNC_CHGINDICATE . '#section');
?>
