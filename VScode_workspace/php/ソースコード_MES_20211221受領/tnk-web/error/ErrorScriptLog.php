<?php
//////////////////////////////////////////////////////////////////////////////
// base_class.js ��������������줿 JavaScript ���顼��                 //
// Copyright(C) 2005-2005 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp       //
// Changed History                                                          //
// 2005/08/30 Created   ErrorScriptLog.php                                  //
// 2005/09/03 ǧ�ڤ��ڤ줿(���Ϥʤ�)��礬����Τǥ��顼���޻ߤ��ɲ�        //
//////////////////////////////////////////////////////////////////////////////
ini_set('error_reporting', E_ALL);      // E_ALL='2047' debug ��
// ini_set('display_errors', '1');         // Error ɽ�� ON debug �� ��꡼���女����
session_start();                        // ini_set()�μ��˻��ꤹ�뤳�� Script �Ǿ��
require_once ('../function.php');       // TNK ������ function
access_log();                           // Script Name �ϼ�ư����

// ǧ�ڤ��ڤ줿(���Ϥʤ�)��礬����Τǥ��顼���޻�
$User_ID = @$_SESSION['User_ID'];
$Auth    = @$_SESSION['Auth'];

$error   = mb_convert_encoding(stripslashes($_REQUEST['error']), 'EUC-JP', 'SJIS');
$file    = $_REQUEST['file'];
$line    = $_REQUEST['line'];
$browser = $_REQUEST['browser'];

if ( ($fp=fopen('ErrorScriptLog.log', 'a')) ) {
    $log  = date('Y-m-d H:i:m') . " IP_ADDRES = {$_SERVER['REMOTE_ADDR']}  User = {$User_ID}  Auth = {$Auth}\n";
    $log .= "                    Error���� = {$error}\n";
    $log .= "                    �ե�����  = {$file}\n";
    $log .= "                    �饤��    = {$line}\n";
    $log .= "                    Browser   = {$browser}\n";
    fwrite($fp, $log);
    fclose($fp);
}

?>
