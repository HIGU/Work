<?php
//////////////////////////////////////////////////////////////////////////////
// ������ư���������ƥ�� �������塼�� ����ȥ���� ����  �ե졼�����    //
// Copyright (C) 2004 2007 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp      //
// Changed history                                                          //
// 2004/09/02 Created  equip_plan_graph.php                                 //
// 2007/09/16 =& new �� = new ��(��ե���󥹱黻�Ҥκ��)                  //
//////////////////////////////////////////////////////////////////////////////
ini_set('error_reporting', E_ALL);          // E_ALL='2047' debug ��
// ini_set('display_errors', '1');             // Error ɽ�� ON debug �� ��꡼���女����
// ini_set('implicit_flush', 'off');           // echo print �� flush �����ʤ�(�٤��ʤ뤿��) CLI CGI��
// ini_set('max_execution_time', 1200);        // ����¹Ի���=20ʬ CLI CGI��
ob_start('ob_gzhandler');                   // ���ϥХåե���gzip����
session_start();                            // ini_set()�μ��˻��ꤹ�뤳�� Script �Ǿ��
require_once ('../../function.php');        // define.php �� pgsql.php �� require_once ���Ƥ���
require_once ('../../MenuHeader.php');      // TNK ������ menu class
access_log();                               // Script Name �ϼ�ư����

///// TNK ���ѥ�˥塼���饹�Υ��󥹥��󥹤����
$menu = new MenuHeader();                   // ǧ�ڥ����å���ԤäƤ���

////////////// ����������
$menu->set_site(40, 8);                     // site_index=40(������˥塼) site_id=8(�������塼�顼)
//////////// �����ȥ�̾(�������Υ����ȥ�̾�ȥե�����Υ����ȥ�̾)
$menu->set_title('�������塼�顼�ξȲ�ڤӥ��ƥʥ�');
//////////// �ե졼��θƽ���Υ��������(frame)̾�ȥ��ɥ쥹����
$menu->set_frame('Header', EQUIP2 . 'plan/equip_plan_graphHeader.php');
$menu->set_frame('List'  , EQUIP2 . 'plan/equip_plan_graphList.php');
// �ե졼���Ǥ� $menu->set_action()�ǤϤʤ�$menu->set_frame()����Ѥ���

///// GET/POST�Υ����å�&����
if (isset($_REQUEST['page_keep'])) {
    $mac_no = '?mac_no=' . @$_SESSION['mac_no'];
} else {
    $mac_no = '?mac_no=' . @$_REQUEST['mac_no'];
}

///// �ڡ�������������
$_SESSION['equip_graph_page'] = 1;

/////////// HTML Header ����Ϥ��ƥ���å��������
$menu->out_html_header();
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Frameset//EN"
    "http://www.w3.org/TR/html4/frameset.dtd">
<html>
<head>
<title><?php echo $menu->out_title() ?></title>
</head>
<frameset rows='110,*'>
    <frame src= '<?php echo $menu->out_frame('Header'), $mac_no ?>' name='Header' scrolling='no'>
    <frame src= '<?php echo $menu->out_frame('List') ?>'   name='List'>
</frameset>
</html>
<?php ob_end_flush(); // ���ϥХåե���gzip���� END ?>
