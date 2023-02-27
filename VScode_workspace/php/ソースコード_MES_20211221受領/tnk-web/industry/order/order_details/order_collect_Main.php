<?php
//////////////////////////////////////////////////////////////////////////////
// ����Ǽ����Ǽ��ͽ��ξȲ� ���٤򥦥���ɥ�ɽ��   �ե졼�����             //
// Copyright (C) 2017-2017 Norihisa.Ohya norihisa_ooya@nitto-kohki.co.jp    //
// Changed history                                                          //
// 2017/07/27 Created  order_collect_Main.php(order_details_Main.php���¤) //
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
$menu->set_frame('Header', INDUST . 'order/order_details/order_collect_Main_Header.php');
$menu->set_frame('List'  , INDUST . 'order/order_details/order_collect_Main_Body.php');
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
    $menu->set_title('����Ǽ�������� �Ȳ�');    // �����ȥ������ʤ���IE�ΰ����ΥС�������ɽ���Ǥ��ʤ��Զ�礢��
} else {
    $menu->set_title("{$_REQUEST['date']} ����Ǽ�������� �Ȳ�");
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
    <iframe hspace='0' vspace='0' frameborder='0' scrolling='yes' src='<?php echo $menu->out_frame('Header') . $parm ?>' name='header' align='center' width='100%' height='40' title='����'>
        ���ܤ�ɽ�����Ƥ��ޤ���\n";
    </iframe>
    <iframe hspace='0' vspace='0' frameborder='0' scrolling='yes' src='<?php echo $menu->out_frame('List') . $parm ?>' name='list' align='center' width='100%' height='94%' title='����'>
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
