<?php
//////////////////////////////////////////////////////////////////////////////
// ���(�ǹ礻)�Υ������塼��ɽ��ɽ���桼����ID�ξ�ǧ�Ԥ�����Ϸ����ɽ��   //
// Copyright (C) 2021-2021 Ryota.Waki ryota_waki@nitto-kohki.co.jp          //
// Changed history                                                          //
// 2021/11/17 Created   meeting_schedule_sougou_admit_output.php            //
//////////////////////////////////////////////////////////////////////////////
ini_set('error_reporting', E_ALL);          // E_ALL='2047' debug ��
// ini_set('display_errors', '1');             // Error ɽ�� ON debug �� ��꡼���女����
ini_set('max_execution_time', 60);          // ����¹Ի���=60�� WEB CGI��
//ob_start('ob_gzhandler');                   // ���ϥХåե���gzip����
//session_start();                            // ini_set()�μ��˻��ꤹ�뤳�� Script �Ǿ��
require_once ('../function.php');     // define.php �� pgsql.php �� require_once ���Ƥ���
require_once ('../MenuHeader.php');   // TNK ������ menu class
require_once ('../ControllerHTTP_Class.php');       // TNK ������ MVC Controller Class
access_log();                               // Script Name �ϼ�ư����

//////////// ���å���� ���֥������Ȥμ���
$session = new Session();

// ��ǧ�Ԥ����ǧ����桼����
$login_uid = $_SESSION['User_ID'];
if( $login_uid == '300667' ) $debug = true; else $debug = false; 
if($debug){
//$login_uid = '300144';// ��Ĺ
//$login_uid = '017507';// ��Ĺ
//$login_uid = '016713';// ��Ĺ
//$login_uid = '300055';// ��̳��Ĺ
//$login_uid = '017850';// ������Ĺ
//$login_uid = '011061';// ����Ĺ
}

// ��ǧ�Ԥ��������
$query = "SELECT count(*) FROM sougou_deteils where admit_status='$login_uid'";
$res2 = array();
$cnt = getResult2($query, $res2);
if( $cnt > 0 ) $cnt = $res2[0][0];

echo $cnt;  // ���ʳ��ʤ龵ǧ�Ԥ����󥦥���ɥ���ɽ��
?>
