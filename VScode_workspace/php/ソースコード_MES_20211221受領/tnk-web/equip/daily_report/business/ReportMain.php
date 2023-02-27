<?php
//////////////////////////////////////////////////////////////////////////////
// ������Ư���������ƥ�ε�����ž���� ���󸡺��ե�����                      //
// Copyright (C) 2004-2021 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp      //
// Original by yamagishi@matehan.co.jp                                      //
// Changed history                                                          //
// 2004/07/15 Created  ReportMain.php                                       //
// 2005/02/02 MenuHeader class ����Ѥ��ƶ��̥�˥塼���ڤ�ǧ���������ѹ�   //
// 2021/03/11 ���͸����ɲäΤ���岼�Υ�������Ĵ��                     ��ë //
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
$menu->set_frame('Header', EQUIP2 . 'daily_report/business/ReportSearch.php');
$menu->set_frame('List'  , EQUIP2 . 'daily_report/business/ReportList.php');

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
<FRAMESET rows='190,*'>
    <FRAME src= 'ReportSearch.php<?=$RetUrl?>'  name='SearchFream'>
    <FRAME src= 'ReportList.php'    name='ListFream'>
</FRAMESET>
<body>
</body>
</html>
<?php ob_end_flush(); ?>
