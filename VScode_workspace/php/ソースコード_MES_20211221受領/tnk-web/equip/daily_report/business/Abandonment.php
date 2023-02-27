<?php
//////////////////////////////////////////////////////////////////////////////
// ������Ư���������ƥ��ü���������               Client interface ��     //
//                                                  MVC View �� Parent ��   //
// Copyright (C) 2004-2006 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp      //
// Changed history                                                          //
// 2004/07/01 Created   Abandonment.php                                     //
// 2006/04/12 MenuHeader ���饹�б�                                         //
//////////////////////////////////////////////////////////////////////////////
// ini_set('error_reporting', E_STRICT);       // E_STRICT=2048(php5) E_ALL=2047 debug ��
ini_set('error_reporting', E_ALL);          // E_STRICT=2048(php5) E_ALL=2047 debug ��
// ini_set('display_errors', '1');             // Error ɽ�� ON debug �� ��꡼���女����
ob_start('ob_gzhandler');                   // ���ϥХåե���gzip����
session_start();                            // ini_set()�μ��˻��ꤹ�뤳�� Script �Ǿ��

require_once ('../../../MenuHeader.php');   // TNK ������ menu class
require_once ('../../../function.php');     // access_log()���ǻ���
require_once ('../com/define.php'); 
require_once ('../com/function.php'); 
access_log();                               // Script Name �ϼ�ư����

///// TNK ���ѥ�˥塼���饹�Υ��󥹥��󥹤����
$menu = new MenuHeader(0);                  // ǧ�ڥ����å�0=���̰ʾ� �����=TOP_MENU �����ȥ�̤����
//////////// �����ȥ�̾(�������Υ����ȥ�̾�ȥե�����Υ����ȥ�̾)
$menu->set_title('ü���������');
//////////// �ե졼��θƽ���Υ��������(frame)̾�ȥ��ɥ쥹����
$menu->set_frame('Header', EQUIP2 . 'daily_report/business/AbandonmentSearch.php');
$menu->set_frame('List'  , EQUIP2 . 'daily_report/business/AbandonmentList.php');
// �ե졼���Ǥ� $menu->set_action()�ǤϤʤ�$menu->set_frame()����Ѥ���

$AdminUser = AdminUser( FNC_REPORT );

$RetUrl = '?RetUrl='.$_REQUEST['RetUrl'];

?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
<?php require_once ('../com/PageHeader.php'); ?>
<title><?php echo $menu->out_title() ?></title>
</head>
<FRAMESET rows='170,*'>
    <FRAME src= '<?php echo $menu->out_frame('Header'), $RetUrl?>' name='SearchFream'>
    <FRAME src= '<?php echo $menu->out_frame('List') ?>' name='ListFream'>
</FRAMESET>
<body>
</body>
</html>
<?php ob_end_flush(); ?>
