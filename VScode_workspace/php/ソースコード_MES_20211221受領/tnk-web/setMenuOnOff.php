<?php
//////////////////////////////////////////////////////////////////////////////
// Ajax��Ȥä� site_menu �� ɽ������ɽ���ξ��֤򥵡��С��˽����           //
// Copyright(C) 2005-2005 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp       //
// Changed History                                                          //
// 2005/09/05 Created   setMenuOnOff.php                                    //
// 2005/09/11 ɽ����OnOff���������base_class��menuOnOff()��Ajax()�Τ����ɲ�//
//////////////////////////////////////////////////////////////////////////////
ini_set('error_reporting', E_ALL);      // E_ALL='2047' debug ��
// ini_set('display_errors', '1');         // Error ɽ�� ON debug �� ��꡼���女����
session_start();                        // ini_set()�μ��˻��ꤹ�뤳�� Script �Ǿ��
require_once ('function.php');          // TNK ������ function
access_log();                           // Script Name �ϼ�ư����

////////////// ɽ���� On Off ��������
if (isset($_REQUEST['site'])) {
    $_SESSION['site_view'] = $_REQUEST['site'];
    exit;
}

////////////// ɽ���� On Off ��������
if (!isset($_SESSION['site_view'])) $_SESSION['site_view'] = 'off';
if ($_SESSION['site_view'] == 'on') {
    $_SESSION['site_view'] = 'off';
} else {
    $_SESSION['site_view'] = 'on';
}

?>
