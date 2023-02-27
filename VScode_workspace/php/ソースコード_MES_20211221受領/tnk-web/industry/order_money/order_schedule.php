<?php
//////////////////////////////////////////////////////////////////////////////
// Ǽ��ͽ���ۤξȲ�  �ե졼�����                                         //
// Copyright (C) 2009-2010   Norihisa.Ohya  norihisa_ooya@nitto-kohki.co.jp //
// Changed history                                                          //
// 2009/11/09 Created  /order/order_schedule.php ��� /order_money/��ή��   //
// 2010/05/26 �����ȥ뤬�㤦�Τǽ���                                        //
//////////////////////////////////////////////////////////////////////////////
// ini_set('error_reporting', E_ALL);          // E_ALL='2047' debug ��
// ini_set('display_errors', '1');             // Error ɽ�� ON debug �� ��꡼���女����
// ini_set('implicit_flush', 'off');           // echo print �� flush �����ʤ�(�٤��ʤ뤿��) CLI CGI��
// ini_set('max_execution_time', 1200);        // ����¹Ի���=20ʬ CLI CGI��
ob_start('ob_gzhandler');                   // ���ϥХåե���gzip����
session_start();                            // ini_set()�μ��˻��ꤹ�뤳�� Script �Ǿ��
require_once ('../../function.php');        // define.php �� pgsql.php �� require_once ���Ƥ���
require_once ('../../MenuHeader.php');      // TNK ������ menu class
access_log();                               // Script Name �ϼ�ư����

if (!isset($_SESSION['Auth'])) {
    $_SESSION['Auth'] = 0;
    $_SESSION['User_ID'] = '00000A';
    $_SESSION['site_view'] = 'off';
    $_SESSION['s_sysmsg'] = '';
}

///// TNK ���ѥ�˥塼���饹�Υ��󥹥��󥹤����
$menu = new MenuHeader();                   // ǧ�ڥ����å���ԤäƤ���

////////////// ����������
$menu->set_site(30, 50);                    // site_index=30(������˥塼) site_id=50(Ǽ���������ų�)999(�����Ȥ򳫤�)
//////////// �����ȥ�̾(�������Υ����ȥ�̾�ȥե�����Υ����ȥ�̾)
$menu->set_title('Ǽ��ͽ��ȸ����ųݤξȲ�');
//////////// �ե졼��θƽ���Υ��������(frame)̾�ȥ��ɥ쥹����
$menu->set_frame('Header', INDUST . 'order_money/order_schedule_Header.php');
$menu->set_frame('List'  , INDUST . 'order_money/order_schedule_List.php');
// �ե졼���Ǥ� $menu->set_action()�ǤϤʤ�$menu->set_frame()����Ѥ���

///// GET/POST�Υ����å�&����
if (isset($_REQUEST['div'])) {
    $parm = '?div=' . $_REQUEST['div'];
    $_SESSION['div'] = $_REQUEST['div'];    // ���å�������¸
} else {
    if (isset($_SESSION['div'])) {
        $parm = "?div={$_SESSION['div']}";  // Default(���å���󤫤�)
    } else {
        $parm = '?div=C';                   // ����ͤϥ��ץ�
    }
}
if (isset($_REQUEST['miken'])) {
    $parm .= '&miken=GO';                   // ̤�����ꥹ��
    $_SESSION['select'] = 'miken';          // ���å�������¸
} elseif (isset($_REQUEST['insEnd'])) {
    $parm .= '&insEnd=GO';                  // Ǽ��ͽ�ꥰ���
    $_SESSION['select'] = 'insEnd';         // ���å�������¸
} elseif (isset($_REQUEST['graph'])) {
    $parm .= '&graph=GO';                   // Ǽ��ͽ�ꥰ���
    $_SESSION['select'] = 'graph';          // ���å�������¸
} elseif (isset($_REQUEST['list'])) {
    $parm .= '&list=GO';                    // Ǽ��ͽ�꽸��
    $_SESSION['select'] = 'list';           // ���å�������¸
} else {
    if (isset($_SESSION['select'])) {
        $parm .= "&{$_SESSION['select']}=GO";   // Default(���å���󤫤�)
    } else {
        $parm .= '&graph=GO';               // ����ͤ�Ǽ��ͽ�ꥰ���
    }
}

/////////// HTML Header ����Ϥ��ƥ���å��������
$menu->out_html_header();
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Frameset//EN"
    "http://www.w3.org/TR/html4/frameset.dtd">
<html>
<head>
<title><?php echo $menu->out_title() ?></title>
<?php if($_SESSION['User_ID'] != '00000A') echo $menu->out_site_java(); ?>
<link rel='shortcut icon' href='/favicon.ico?=<?php echo time() ?>'>
</head>
<frameset rows='120,*'>
    <frame src= '<?php echo $menu->out_frame('Header') . $parm ?>' name='Header' scrolling='no'>
    <frame src= '<?php echo $menu->out_frame('List') . $parm ?>'   name='List'>
</frameset>
</html>
<?php ob_end_flush(); // ���ϥХåե���gzip���� END ?>
