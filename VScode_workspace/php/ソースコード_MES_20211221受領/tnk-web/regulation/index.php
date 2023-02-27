<?php
//////////////////////////////////////////////////////////////////////////////
// ������˥塼���� Document Root Index File                                //
// Copyright (C) 2006-2006 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp      //
// Changed history                                                          //
// 2006/04/17 Created  regulation/index.php                                 //
//////////////////////////////////////////////////////////////////////////////
ini_set('error_reporting', E_STRICT);       // E_STRICT=2048(php5) E_ALL=2047 debug ��
// ini_set('display_errors', '1');             // Error ɽ�� ON debug �� ��꡼���女����
session_start();                            // ini_set()�μ��˻��ꤹ�뤳�� Script �Ǿ��
require_once ('../function.php');           // ���̥ե��󥯥å����
access_log();                               // Script Name �ϼ�ư����

$_SESSION['r_addr']     = $_SERVER['REMOTE_ADDR'];  // ���Τˤ� $_SESSION ����Ѥ������ session_register ����Ѥ��ƤϤ����ʤ�
$_SESSION['r_hostname'] = gethostbyaddr($_SERVER['REMOTE_ADDR']);
$_SESSION['web_file']   = $_SERVER['SCRIPT_NAME'];

if ( !isset($_SESSION['Counter']) ) {       // ���ξ��� Counter ����Ͽ����Ƥ��ʤ�
    $_SESSION['Counter'] = 0;
}
$_SESSION['Counter']++;
if (isset($_GET['PHPSESSID'])) {
    header('Location: http:' . WEB_HOST . 'regulation/authenticate.php?' . SID);   // SID���ղäϥ��å���̵�����к�
} else {
    header('Location: http:' . WEB_HOST . 'regulation/authenticate.php');
}
?>
