<?php
//////////////////////////////////////////////////////////////////////////////
// Ǽ��ͽ��ξȲ� ��������(��ʸ��̤ȯ��ʬ) ���٤򥦥���ɥ�ɽ�� �ե졼�����//
// Copyright (C) 2004 2007 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp      //
// Changed history                                                          //
// 2004/11/25 Created  order_details_next.php                               //
// 2007/05/11 �ǥ��쥯�ȥ�� order/ �� order/order_details/ ���ѹ�          //
//////////////////////////////////////////////////////////////////////////////
ini_set('error_reporting', E_ALL);          // E_ALL='2047' debug ��
// ini_set('display_errors', '1');             // Error ɽ�� ON debug �� ��꡼���女����
// ini_set('implicit_flush', 'off');           // echo print �� flush �����ʤ�(�٤��ʤ뤿��) CLI CGI��
// ini_set('max_execution_time', 1200);        // ����¹Ի���=20ʬ CLI CGI��
ob_start('ob_gzhandler');                   // ���ϥХåե���gzip����
session_start();                            // ini_set()�μ��˻��ꤹ�뤳�� Script �Ǿ��
require_once ('../../../function.php');     // define.php �� pgsql.php �� require_once ���Ƥ���
require_once ('../../../MenuHeader.php');   // TNK ������ menu class
access_log();                               // Script Name �ϼ�ư����

///// TNK ���ѥ�˥塼���饹�Υ��󥹥��󥹤����
$menu = new MenuHeader();                   // ǧ�ڥ����å���ԤäƤ���

////////////// ����������
// $menu->set_site(30, 999);                   // site_index=30(������˥塼) site_id=999(̤��)
//////////// �ե졼��θƽ���Υ��������(frame)̾�ȥ��ɥ쥹����
$menu->set_frame('Header', INDUST . 'order/order_details/order_details_next_Header.php');
$menu->set_frame('List'  , INDUST . 'order/order_details/order_details_next_List.php');
// �ե졼���Ǥ� $menu->set_action()�ǤϤʤ�$menu->set_frame()����Ѥ���

//////// �������Υѥ�᡼������ & ����
if (isset($_REQUEST['date'])) {
    if ($_REQUEST['date'] == 'OLD') {
        $date = $_REQUEST['date'];
    } else {
        $date = $_REQUEST['date'];              // ���٤�ɽ�������������
        $date = ('20' . substr($date, 0, 2) . substr($date, 3, 2) . substr($date, 6, 2));
            // YYYYMMDD�η������Ѵ�
    }
} else {
    $date = date('Ymd');                    // �����(����)�㳰ȯ���ξ����б�
}

//////////// �����ȥ�̾(�������Υ����ȥ�̾�ȥե�����Υ����ȥ�̾)
if ($date == 'OLD') {
    $menu->set_title('������ Ǽ���٤������ �Ȳ�');    // �����ȥ������ʤ���IE�ΰ����ΥС�������ɽ���Ǥ��ʤ��Զ�礢��
} else {
    $menu->set_title("{$_REQUEST['date']} Ǽ��ͽ�� ������ ���٤ξȲ�"); // �����ȥ������ʤ���IE�ΰ����ΥС�������ɽ���Ǥ��ʤ��Զ�礢��
}

///// GET/POST�Υ����å�&����
if (isset($_REQUEST['div'])) {
    $parm = '?div=' . $_REQUEST['div'];
} else {
    if (isset($_SESSION['div'])) {
        $parm = "?div={$_SESSION['div']}";  // Default(���å���󤫤�)
    } else {
        $parm = '?div=C';                   // ����ͤϥ��ץ�
    }
}
if (isset($_REQUEST['date'])) {
    $parm .= '&date=' . $_REQUEST['date'];  // �������դ򥻥å�
}

/////////// HTML Header ����Ϥ��ƥ���å��������
$menu->out_html_header();
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Frameset//EN"
    "http://www.w3.org/TR/html4/frameset.dtd">
<html>
<head>
<title><?php echo $menu->out_title() ?></title>
</head>
<frameset rows='40,*'>
    <frame src= '<?php echo $menu->out_frame('Header') . $parm ?>' name='Header' scrolling='no'>
    <frame src= '<?php echo $menu->out_frame('List') . $parm ?>'   name='List'>
</frameset>
</html>
<?php ob_end_flush(); // ���ϥХåե���gzip���� END ?>
