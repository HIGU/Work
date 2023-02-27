<?php
//////////////////////////////////////////////////////////////////////////////
// Document Root Index File ���������Τ��ä��ۥ������򥻥å������ɲ�      //
// Copyright (C) 2001-2005 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp      //
// Changed history                                                          //
// 2001/07/07 Created  index.php                                            //
// 2002/01/18 ���å����������ɲ�                                          //
// 2004/02/02 index1.php �� authenticate.php ��̾�����ѹ�                   //
// 2004/03/10 ���饤����ȤΥ��å���̵�����к����å����ɲ�                //
// 2005/09/13 session_register('r_addr', 'r_hostname', 'web_file')���ѻ�    //
//            E_ALL �� E_STRICT                                             //
// 2005/09/21 gethostbyaddr($r_addr)��gethostbyaddr($_SERVER['REMOTE_ADDR'])//
//////////////////////////////////////////////////////////////////////////////
ini_set('error_reporting', E_STRICT);       // E_STRICT=2048(php5) E_ALL=2047 debug ��
// ini_set('display_errors', '1');             // Error ɽ�� ON debug �� ��꡼���女����
session_start();                            // ini_set()�μ��˻��ꤹ�뤳�� Script �Ǿ��
require_once ('function.php');              // ���̥ե��󥯥å����
access_log();                               // Script Name �ϼ�ư����
//  session_destroy();
$_SESSION['r_addr']     = $_SERVER['REMOTE_ADDR'];  // ���Τˤ� $_SESSION ����Ѥ������ session_register ����Ѥ��ƤϤ����ʤ�
$_SESSION['r_hostname'] = gethostbyaddr($_SERVER['REMOTE_ADDR']);
$_SESSION['web_file']   = $_SERVER['SCRIPT_NAME'];
if ( !isset($_SESSION['Counter']) ) {       // ���ξ��� Counter ����Ͽ����Ƥ��ʤ�
    $_SESSION['Counter'] = 0;
}
$_SESSION['Counter']++;
// session_id($r_hostname);
if (isset($_GET['PHPSESSID'])) {
    header('Location: http:' . WEB_HOST . 'authenticate.php?' . SID);   // SID���ղäϥ��å���̵�����к�
} else {
    header('Location: http:' . WEB_HOST . 'authenticate.php');
}
?>
