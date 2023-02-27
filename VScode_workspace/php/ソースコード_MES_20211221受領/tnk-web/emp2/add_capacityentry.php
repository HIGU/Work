<?php
//////////////////////////////////////////////////////////////////////////////
// �Ұ���������� ��ʤ���Ͽ �¹�                                           //
// Copyright (C) 2001-2010 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp      //
// Changed history                                                          //
// 2001/07/07 Created   add_capacityentry.php                               //
// 2002/08/07 register_globals = Off �б� & ���å�������                  //
// 2005/01/17 access_log ���ѹ��� view_file_name(__FILE__) ���ɲ�           //
// 2007/02/09 �����Ͽ�Ƕ���ξ������Ф�����Ͽ�Ǥ���褦���б�            //
//            ��Ͽ��ǽ�Ԥ���ʤ��Ȥ��Υ����å����ɲ� ��ë                   //
// 2007/02/15 POST��REQUEST���ѹ�                                           //
//            �Ұ�̾��ɽ�����ɲ�    ��ë                                    //
// 2007/07/06 ��Ͽ�����̾��ɽ������褦���ѹ� ��ë                         //
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
        header('Location: http:' . H_WEB_HOST . EMP . 'emp_menu.php?func=' . FUNC_CHGCAPACITY);
        exit();
    }
}

$rows = count($_REQUEST['uid']);
$_SESSION['s_sysmsg'] = "��ʤ���Ͽ��{$_REQUEST['capacity_name']}\\n";
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
    if (addCapaentry($_REQUEST['uid'], $_REQUEST['acq_date'], $_REQUEST['capacity'])) {
        header("Location: http:" . WEB_HOST . "emp/emp_menu.php?func=" . FUNC_CHGCAPACITY);
        exit();
    }
    $_SESSION['s_sysmsg'] = "��ʤ��ɲä˼��Ԥ��ޤ����������Ԥˤ��䤤��碌����������";
    header('Location: ' . H_WEB_HOST . EMP . 'emp_menu.php?func=' . FUNC_CHGCAPACITY);
} else {
    $_SESSION['s_sysmsg'] = "��Ͽ�Ǥ���Ұ������ޤ���Ǥ������Ұ��ֹ���ǧ���ƺ�����Ͽ���Ƥ���������";
    header('Location: ' . H_WEB_HOST . EMP . 'emp_menu.php?func=' . FUNC_CHGCAPACITY);
}
?>
