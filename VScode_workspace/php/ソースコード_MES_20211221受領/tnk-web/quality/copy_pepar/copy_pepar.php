<?php
//////////////////////////////////////////////////////////////////////////////
// Ǽ��ͽ�ꥰ��ա������ų����٤ξȲ�(�����λŻ����İ�)  �ե졼�����       //
// Copyright (C) 2004-2017      Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp //
// Changed history                                                          //
// 2021/07/07 Created  order_schedule.php -> copy_pepar.php                 //
//////////////////////////////////////////////////////////////////////////////
// ini_set('error_reporting', E_ALL);          // E_ALL='2047' debug ��
// ini_set('display_errors', '1');             // Error ɽ�� ON debug �� ��꡼���女����
// ini_set('implicit_flush', 'off');           // echo print �� flush �����ʤ�(�٤��ʤ뤿��) CLI CGI��
// ini_set('max_execution_time', 1200);        // ����¹Ի���=20ʬ CLI CGI��
ob_start('ob_gzhandler');                   // ���ϥХåե���gzip����
session_start();                            // ini_set()�μ��˻��ꤹ�뤳�� Script �Ǿ��
require_once ('../../function.php');        // define.php �� pgsql.php �� require_once ���Ƥ���
require_once ('../../MenuHeader.php');      // TNK ������ menu class
require_once ('copy_pepar_function.php');   // copy_pepar �ط��ζ��� function
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
$menu->set_site(70, 72);                   // site_index=70(�ʼ����Ķ���˥塼) site_id=72(�����̥��ԡ��ѻ������)
//////////// �����ȥ�̾(�������Υ����ȥ�̾�ȥե�����Υ����ȥ�̾)
$menu->set_title('Ǽ��ͽ��ȸ����ųݤξȲ�');
//////////// �ե졼��θƽ���Υ��������(frame)̾�ȥ��ɥ쥹����
$menu->set_frame('Header', QUALITY . 'copy_pepar/copy_pepar_Header.php');
$menu->set_frame('List'  , QUALITY . 'copy_pepar/copy_pepar_List.php');
// �ե졼���Ǥ� $menu->set_action()�ǤϤʤ�$menu->set_frame()����Ѥ���

///// GET/POST�Υ����å�&����
if (isset($_REQUEST['tnk_ki'])) {
    $parm = '?div=' . $_REQUEST['tnk_ki'];
    $_SESSION['tnk_ki'] = $_REQUEST['tnk_ki'];    // ���å�������¸
} else {
    if (isset($_SESSION['tnk_ki'])) {
        $parm = "?div={$_SESSION['tnk_ki']}";  // Default(���å���󤫤�)
    } else {
        $parm = '?div=' . getTnkKi();                   // ����ͤϥ��ץ�
    }
}

if (isset($_REQUEST['input_mode'])) {
    $parm .= '&input_mode=GO';                   // ̤�����ꥹ��
    $_SESSION['select'] = 'input_mode';          // ���å�������¸
} elseif (isset($_REQUEST['graph'])) {
    $parm .= '&graph=GO';                   // Ǽ��ͽ�ꥰ���
    $_SESSION['select'] = 'graph';          // ���å�������¸
} else {
    if (isset($_SESSION['select'])) {
        if( $_SESSION['select'] == 'input_mode' || $_SESSION['select'] == 'graph') {
        $parm .= "&{$_SESSION['select']}=GO";   // Default(���å���󤫤�)
        } else {
            $parm .= '&graph=GO';               // ����ͤ�Ǽ��ͽ�ꥰ���
        }
    } else {
        $parm .= '&graph=GO';               // ����ͤ�Ǽ��ͽ�ꥰ���
    }
}

//$_SESSION['s_sysmsg'] .= "TEST:parm=" . $parm;

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
