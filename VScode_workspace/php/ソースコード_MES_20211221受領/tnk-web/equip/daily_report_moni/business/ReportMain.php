<?php
//////////////////////////////////////////////////////////////////////////////
// ��Ω������Ư���������ƥ�ε�����ž���� ���󸡺��ե�����                  //
// Copyright (C) 2021-2021 norihisa_ooya@nitto-kohki.co.jp                  //
// Original by yamagishi@matehan.co.jp                                      //
// Changed history                                                          //
// 2021/03/26 Created  ReportMain.php                                       //
//////////////////////////////////////////////////////////////////////////////
ini_set('error_reporting', E_ALL);          // E_ALL='2047' debug ��
require_once ('../../../function.php');     // TNK ������ function
require_once ('../../../MenuHeader.php');   // TNK ������ menu class
require_once ('../com/define.php'); 
require_once ('../com/function.php'); 
require_once ('MakeReport.php'); 
access_log();                               // Script Name �ϼ�ư����

///// TNK ���ѥ�˥塼���饹�Υ��󥹥��󥹤����
$menu = new MenuHeader();                   // ǧ�ڥ����å���ԤäƤ���

//////////// �ե졼��θƽ���Υ��������(frame)̾�ȥ��ɥ쥹����
$menu->set_frame('Header', EQUIP2 . 'daily_report_moni/business/ReportSearch.php');
$menu->set_frame('List'  , EQUIP2 . 'daily_report_moni/business/ReportList.php');

// �����ԥ⡼�ɤμ���
$AdminUser = AdminUser( FNC_REPORT );

// �����μ���
$RetUrl = '?RetUrl='.@$_REQUEST['RetUrl'];

// ���ͥ������μ���
$con = getConnection();

// ������ž���ν���
MakeReport();

ob_start('ob_gzhandler');
?>
<!DOCTYPE HTML PUBLIC '-//W3C//DTD HTML 4.01 Transitional//EN'>
<html>
<head>
<?php require_once ('../com/PageHeader.php'); ?>
<title>������ž����</title>
</head>
<FRAMESET rows='220,*'>
    <FRAME src= 'ReportSearch.php<?=$RetUrl?>'  name='SearchFream'>
    <FRAME src= 'ReportList.php'    name='ListFream'>
</FRAMESET>
<body>
</body>
</html>
<?php ob_end_flush(); ?>
