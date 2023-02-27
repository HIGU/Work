<?php
//////////////////////////////////////////////////////////////////////////////
// ����� �Ժ߼Ԥ򥦥���ɥ�ɽ��   �ե졼�����                             //
// Copyright (C) 2019-2019 Norihisa.Ohya norihisa_ooya@nitto-kohki.co.jp    //
// Changed history                                                          //
// 2019/03/15 Created  meeting_schedule_absence_Main                        //
//////////////////////////////////////////////////////////////////////////////
ini_set('error_reporting', E_ALL);          // E_ALL='2047' debug ��
// ini_set('display_errors', '1');             // Error ɽ�� ON debug �� ��꡼���女����
// ini_set('implicit_flush', 'off');           // echo print �� flush �����ʤ�(�٤��ʤ뤿��) CLI CGI��
// ini_set('max_execution_time', 1200);        // ����¹Ի���=20ʬ CLI CGI��
ob_start('ob_gzhandler');                   // ���ϥХåե���gzip����
session_start();                            // ini_set()�μ��˻��ꤹ�뤳�� Script �Ǿ��
require_once ('../function.php');     // define.php �� pgsql.php �� require_once ���Ƥ���
require_once ('../MenuHeader.php');   // TNK ������ menu class
access_log();                               // Script Name �ϼ�ư����

///// TNK ���ѥ�˥塼���饹�Υ��󥹥��󥹤����
$menu = new MenuHeader();                   // ǧ�ڥ����å���ԤäƤ���

////////////// ����������
// $menu->set_site(30, 999);                   // site_index=30(������˥塼) site_id=999(̤��)
//////////// �ե졼��θƽ���Υ��������(frame)̾�ȥ��ɥ쥹����
$menu->set_frame('Header', 'meeting_schedule_absence_Header.php');
$menu->set_frame('List'  , 'meeting_schedule_absence_Body.php');
// �ե졼���Ǥ� $menu->set_action()�ǤϤʤ�$menu->set_frame()����Ѥ���

$date = date('Ymd');                    // �����(����)�㳰ȯ���ξ����б�

$menu->set_title('�Ժ߼ԾȲ�');    // �����ȥ������ʤ���IE�ΰ����ΥС�������ɽ���Ǥ��ʤ��Զ�礢��

/////////// HTML Header ����Ϥ��ƥ���å��������
$menu->out_html_header();
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=EUC-JP">
<meta http-equiv="Content-Style-Type" content="text/css">
<title><?php echo $menu->out_title() ?></title>
<?php echo $menu->out_css() ?>
</head>
<body>
<center>
    <iframe hspace='0' vspace='0' frameborder='0' scrolling='yes' src='<?php echo $menu->out_frame('Header') ?>' name='header' align='center' width='100%' height='15%' title='����'>
        ���ܤ�ɽ�����Ƥ��ޤ���\n";
    </iframe>
    <iframe hspace='0' vspace='0' frameborder='0' scrolling='yes' src='<?php echo $menu->out_frame('List') ?>' name='list' align='center' width='100%' height='85%' title='����'>
        ������ɽ�����Ƥ��ޤ���
    </iframe>
    <!--
    <iframe hspace='0' vspace='0' frameborder='0' scrolling='yes' src='' name='footer' align='center' width='100%' height='32' title='�եå���'>
        �եå�����ɽ�����Ƥ��ޤ���
    </iframe>
    -->
</center>
</body>
</html>
<?php ob_end_flush(); // ���ϥХåե���gzip���� END ?>
