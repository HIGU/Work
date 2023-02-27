<?php
//////////////////////////////////////////////////////////////////////////////
// Ǽ��ͽ�ꥰ��ա������ų����٤ξȲ�(�����λŻ����İ�)  �ե졼�����       //
// Copyright (C) 2004-2017      Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp //
// Changed history                                                          //
// 2004/09/15 Created  order_schedule.php                                   //
// 2005/02/10 out_site_java()�� Header�ե졼�फ��ե졼������ذ�ư        //
// 2005/11/24 <link rel='shortcut icon' href='/favicon.ico'> �ɲ�           //
// 2007/09/29 E_ALL �� E_ALL | E_STRICT�� php���硼�ȥ��åȤ�ɸ�ॿ����     //
//            �����ѥꥹ�Ȥ��ɲ�                                            //
// 2009/02/26 ɽ������Ĵ��                                             ��ë //
// 2017/07/27 ����Ǽ������դ��ɲ�                                     ��ë //
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
$menu->set_frame('Header', INDUST . 'order/order_schedule_Header.php');
$menu->set_frame('List'  , INDUST . 'order/order_schedule_List.php');
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
} elseif (isset($_REQUEST['sgraph'])) {
    $parm .= '&sgraph=GO';                  // ����Ǽ�������
    $_SESSION['select'] = 'sgraph';         // ���å�������¸
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
