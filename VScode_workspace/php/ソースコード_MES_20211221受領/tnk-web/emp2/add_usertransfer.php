<?php
//////////////////////////////////////////////////////////////////////////////
// �Ұ���������� ��°���ɲý���                                            //
// Copyright (C) 2001-2005 2007 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp //
// Changed history                                                          //
// 2001/07/07 Created   add_usertransfer.php                                //
// 2002/08/07 register_globals = Off �б� & ���å�������                  //
// 2005/01/17 access_log ���ѹ��� view_file_name(__FILE__) ���ɲ�           //
// 2007/10/17 $sid = $res[0]['sid'] �ϻ��Ѥ��Ƥ��ʤ��ΤǺ��                //
//////////////////////////////////////////////////////////////////////////////
// ini_set('error_reporting', E_ALL);          // E_ALL='2047' debug ��
// ini_set('display_errors', '1');             // Error ɽ�� ON debug �� ��꡼���女����
session_start();                            // ini_set()�μ��˻��ꤹ�뤳�� Script �Ǿ��
require_once ('../function.php');           // ���� �ؿ�
require_once ('emp_function.php');          // �Ұ���˥塼����
access_log();                               // Script Name ��ư����

$_SESSION['userid'] = $_POST['userid'];
$_SESSION['sect']   = 1;

$query = "select section_name from section_master where sid={$_POST['section']}";
$res = array();
getResult($query,$res);

$section_name = $res[0]['section_name'];
$trans_date = $_POST['trans_date_1'] . "-" . $_POST['trans_date_2'] . "-" . $_POST['trans_date_3'];

//  $userinfo = "&userid=" . $_POST['userid'];
//  $histnum=$histnum-1;
//  $lookupinfo="&lookupkind=$lookupkind&lookupkey=$lookupkey&lookupkeykind=$lookupkeykind" . 
//          "&lookupsection=$lookupsection&lookupposition=$lookupposition&lookupentry=$lookupentry&lookupcapacity=$lookupcapacity&lookupreceive=$lookupreceive&histnum=$histnum&retireflg=$retireflg";
if (addTransfer($_POST['userid'], $_POST['section'], $trans_date, $section_name)) {
//      header("Location: http:" . WEB_HOST . "emp_menu.php?func=" . FUNC_ADMINUSERINFO . "&sect=1" . $userinfo . $lookupinfo);
    header('Location: ' . H_WEB_HOST . EMP . 'emp_menu.php?func=' . FUNC_ADMINUSERINFO);
    exit();
}
$_SESSION['s_sysmsg'] = '�桼�����ν�°�˴ؤ����ѹ��˼��Ԥ��ޤ�����<br>�����Ԥˤ��䤤��碌����������';
//  header("Location: http:" . WEB_HOST . "emp_menu.php?func=" . FUNC_ADMINUSERINFO . "&sect=1&sysmsg=" . $sysmsg . $userinfo . $lookupinfo);
header('Location: ' . H_WEB_HOST . EMP . 'emp_menu.php?func=' . FUNC_ADMINUSERINFO . '&sect=1&sysmsg=');
?>
