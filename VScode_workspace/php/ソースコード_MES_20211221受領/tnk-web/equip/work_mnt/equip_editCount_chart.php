<?php
//////////////////////////////////////////////////////////////////////////////
// ������ư���������ƥ�� �ؼ��ѹ��ڤӥ��Խ�  �ե졼�����                //
// Copyright (C) 2004-2005 2007 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp //
// Changed history                                                          //
// 2004/07/28 Created  equip_edit_chart.php                                 //
// 2004/08/10 out_action() �� out_frame()���ѹ�                             //
// 2005/03/04 ���顼��λ���Υ��å����˻ĤäƤ����ѿ��������å����ɲ�  //
// 2005/09/30 work_cnt �Τߤ��ѹ��Ѥ˿�������                               //
//            Created  equip_editCount_chartHeader.php                      //
// 2007/09/18 E_ALL | E_STRICT ���ѹ�                                       //
//////////////////////////////////////////////////////////////////////////////
// ini_set('error_reporting', E_ALL | E_STRICT);
// ini_set('error_reporting', E_ALL);          // E_ALL='2047' debug ��
// ini_set('display_errors', '1');             // Error ɽ�� ON debug �� ��꡼���女����
// ini_set('implicit_flush', 'off');           // echo print �� flush �����ʤ�(�٤��ʤ뤿��) CLI CGI��
// ini_set('max_execution_time', 1200);        // ����¹Ի���=20ʬ CLI CGI��
ob_start('ob_gzhandler');                   // ���ϥХåե���gzip����
session_start();                            // ini_set()�μ��˻��ꤹ�뤳�� Script �Ǿ��
require_once ('../../function.php');        // define.php �� pgsql.php �� require_once ���Ƥ���
require_once ('../../MenuHeader.php');      // TNK ������ menu class
access_log();                               // Script Name �ϼ�ư����

///// TNK ���ѥ�˥塼���饹�Υ��󥹥��󥹤����
$menu =& new MenuHeader();                  // ǧ�ڥ����å���ԤäƤ���

////////////// ����������
$menu->set_site(40, 11);                    // site_index=40(������˥塼) site_id=11(�ؼ��ѹ�)
//////////// �����ȥ�̾(�������Υ����ȥ�̾�ȥե�����Υ����ȥ�̾)
$menu->set_title('�ؼ����Ƥ��Խ�');
//////////// �ե졼��θƽ���Υ��������(frame)̾�ȥ��ɥ쥹����
$menu->set_frame('Header', EQUIP2 . 'work_mnt/equip_edit_chartHeader.php');
$menu->set_frame('List'  , EQUIP2 . 'work_mnt/equip_edit_chartList.php');
// �ե졼���Ǥ� $menu->set_action()�ǤϤʤ�$menu->set_frame()����Ѥ���

///// GET/POST�Υ����å�&��¸
if (isset($_REQUEST['mac_no'])) {
    $_SESSION['mac_no']  = $_REQUEST['mac_no'];
    $_SESSION['siji_no'] = $_REQUEST['siji_no'];
    $_SESSION['koutei']  = $_REQUEST['koutei'];
}
///// ���顼��λ���Υ��å����˻ĤäƤ����ѿ�����
if (isset($_SESSION['chg_time'])) {
    unset($_SESSION['chg_time']);
}
if (isset($_SESSION['cnt_chg_time'])) {
    unset($_SESSION['cnt_chg_time']);
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
<frameset rows='150,*'>
    <frame src= '<?= $menu->out_frame('Header') ?>' name='Header' scrolling='no'>
    <frame src= '<?= $menu->out_frame('List') ?>'   name='List'>
</frameset>
</html>
<?php ob_end_flush(); // ���ϥХåե���gzip���� END ?>
