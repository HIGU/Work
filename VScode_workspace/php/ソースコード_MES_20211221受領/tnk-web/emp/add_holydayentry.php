<?php
//////////////////////////////////////////////////////////////////////////////
// �Ұ���������� �ײ�ͭ�����Ͽ �¹�                                       //
// Copyright (C) 2015-2015 Norihisa.Ohya norihisa_ooya@nitto-kohki.co.jp    //
// Changed history                                                          //
// 2015/06/18 Created   add_holydayentry.php                                //
// 2015/06/19 �����μ¹Ը��¤���߷������ɲ�                                //
// 2015/06/22 ���¥��顼����                                              //
//////////////////////////////////////////////////////////////////////////////
// ini_set('error_reporting', E_ALL);          // E_ALL='2047' debug ��
// ini_set('display_errors', '1');             // Error ɽ�� ON debug �� ��꡼���女����
session_start();                            // ini_set()�μ��˻��ꤹ�뤳�� Script �Ǿ��
require_once ('../function.php');           // ���� �ؿ�
require_once ('emp_function.php');          // �Ұ���˥塼����
access_log();                               // Script Name ��ư����

if ($_SESSION['Auth'] < 2) { 
    if ($_SESSION['User_ID'] != '970227' && $_SESSION['User_ID'] != '015806') {
        $_SESSION['s_sysmsg'] = '���ʤ��ˤϸ��¤�����ޤ���<br>�����Ԥˤ��䤤��碌��������';
        header('Location: http:' . H_WEB_HOST . EMP . 'emp_menu.php?func=' . FUNC_ADDPHOLYDAY);
        exit();
    }
}

$rows = count($_REQUEST['uid']);
$i = 0; // ��Ͽ�Կ�
for ($r=0; $r<$rows; $r++) {
    if (!$_REQUEST['uid'][$r] == "") {
        if ($_REQUEST['chk_name'][$r] < 2) {
            $uid[$r] = $_REQUEST['uid'][$r];
            $uname[$r] = $_REQUEST['uname'][$r];
            if (!$uid[$r]) continue;
            $_SESSION['s_sysmsg'] .= "�Ұ��ֹ�={$uid[$r]} ";
            $_SESSION['s_sysmsg'] .= "�Ұ�̾={$uname[$r]}\\n";
            $i++;
        }
    }
}
$_REQUEST['uid'] = ""; //�����
$rows_uid = count($uid);    //��Ͽ��ǽ�Կ�
for ($r=0; $r<$rows_uid; $r++) {
    $_REQUEST['uid'][$r] = $uid[$r];
}
if ($rows_uid > 0) {
    $_SESSION['s_sysmsg'] .= "�����Ͽ�Կ���{$i}��";
    if (addHolyday($_REQUEST['uid'], $_REQUEST['acq_date'])) {
        header("Location: http:" . WEB_HOST . "emp/emp_menu.php?func=" . FUNC_ADDPHOLYDAY);
        exit();
    }
    $_SESSION['s_sysmsg'] = "�ײ�ͭ����ɲä˼��Ԥ��ޤ����������Ԥˤ��䤤��碌����������";
    header('Location: ' . H_WEB_HOST . EMP . 'emp_menu.php?func=' . FUNC_ADDPHOLYDAY);
} else {
    $_SESSION['s_sysmsg'] = "��Ͽ�Ǥ���Ұ������ޤ���Ǥ������Ұ��ֹ���ǧ���ƺ�����Ͽ���Ƥ���������";
    header('Location: ' . H_WEB_HOST . EMP . 'emp_menu.php?func=' . FUNC_ADDPHOLYDAY);
}
?>
