<?php
//////////////////////////////////////////////////////////////////////////////
// ȯ�������ƥʥ�(ȯ������ݼ�)   �ե졼�����                      //
// Copyright (C) 2004-2004 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp      //
// Changed history                                                          //
// 2004/11/27 Created  order_process_mnt.php                                //
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
// $menu->set_site(30, 999);                   // site_index=30(������˥塼) site_id=999(̤��)
//////////// �����ȥ�̾(�������Υ����ȥ�̾�ȥե�����Υ����ȥ�̾)
$menu->set_title('ȯ�����Υ��ƥʥ�');     // �����ȥ������ʤ���IE�ΰ����ΥС�������ɽ���Ǥ��ʤ��Զ�礢��
//////////// �ե졼��θƽ���Υ��������(frame)̾�ȥ��ɥ쥹����
$menu->set_frame('Header', INDUST . 'order/order_process_mnt_Header.php');
$menu->set_frame('List'  , INDUST . 'order/order_process_mnt_List.php');
// �ե졼���Ǥ� $menu->set_action()�ǤϤʤ�$menu->set_frame()����Ѥ���

/////// �ѥ�᡼�����μ���
if (isset($_REQUEST['sei_no'])) {
    $parm = "?sei_no={$_REQUEST['sei_no']}";
    if ($_REQUEST['sei_no'] != '') {
        $_SESSION['order_sei_no'] = $_REQUEST['sei_no'];
    } else {
        $_SESSION['order_sei_no'] = '';
    }
} else {
    $parm = '';
    $_SESSION['order_sei_no'] = '';
}

/////////// HTML Header ����Ϥ��ƥ���å��������
$menu->out_html_header();
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Frameset//EN"
    "http://www.w3.org/TR/html4/frameset.dtd">
<html>
<head>
<title><?= $menu->out_title() ?></title>
</head>
<frameset rows='160,*'>
    <frame src= '<?= $menu->out_frame('Header') . $parm ?>' name='Header' scrolling='no'>
    <frame src= '<?= $menu->out_frame('List') . $parm ?>'   name='List'>
</frameset>
</html>
<?php ob_end_flush(); // ���ϥХåե���gzip���� END ?>
