<?php
//////////////////////////////////////////////////////////////////////////////
// �裵������ɲä���������������򲾤�ɽ�����뤿��                         //
//   FwServer6 �ε�ǽ�����ѡ� /cgi-bin/mon.cgi?argHtmlFile=stsmon6.html     //
// 2005/05/12 Copyright(C) 2005 N.Ooya usoumu@nitto-kohki.co.jp             //
// �ѹ�����                                                                 //
// 2005/05/12 ��������  equip_FWS6.php                                      //
//////////////////////////////////////////////////////////////////////////////
ini_set('error_reporting',E_ALL);       // E_ALL='2047' debug ��
ini_set('display_errors','1');          // Error ɽ�� ON debug �� ��꡼���女����
// ini_set('implicit_flush', 'off');       // echo print �� flush �����ʤ�(�٤��ʤ뤿��) CLI CGI��
// ini_set('max_execution_time', 1200);    // ����¹Ի���=20ʬ CLI CGI��
// ob_start('ob_gzhandler');               // ���ϥХåե���gzip����
session_start();                        // ini_set()�μ��˻��ꤹ�뤳�� Script �Ǿ��

require_once ('../../function.php');       // define.php �� pgsql.php �� require_once ���Ƥ���
require_once ('../../tnk_func.php');       // TNK �˰�¸������ʬ�δؿ��� require_once ���Ƥ���
access_log();                           // Script Name �ϼ�ư����

////////////// �����ȥ�˥塼����
$_SESSION['site_index'] = 40;           // �Ǹ�Υ�˥塼    = 99   �����ƥ�����Ѥϣ�����
$_SESSION['site_id']    = 94;           // ���̥�˥塼̵�� <= 0    �ƥ�ץ졼�ȥե�����ϣ�����


////////////// �꥿���󥢥ɥ쥹����
$current_script  = $_SERVER['PHP_SELF'];        // ���߼¹���Υ�����ץ�̾����¸
if ( !(isset($_POST['forward']) || isset($_POST['backward'])) ) {
    $url_referer = $_SERVER['HTTP_REFERER'];    // �ƽФ�Ȥ�URL����¸ ���Υ�����ץȤ�ʬ��������
    $_SESSION['ret_addr'] = $url_referer;       // ���Ƥ�����ϻ��Ѥ��ʤ� site_menu����θƽФǤϻ��ѤǤ��ʤ�
} else {
    $url_referer = $_SESSION['ret_addr'];       // ���ǡ����Ǥλ��ϥ��å���󤫤��ɹ���
}
// $url_referer     = $_SESSION['pl_referer'];     // ʬ������������¸����Ƥ���ƽи��򥻥åȤ���

//////////////// ǧ�ڥ����å�
if ( !isset($_SESSION['User_ID']) || !isset($_SESSION['Password']) || !isset($_SESSION['Auth']) ) {
// if ($_SESSION['Auth'] <= 2) {                // ���¥�٥뤬���ʲ��ϵ���
// if (account_group_check() == FALSE) {        // ����Υ��롼�װʳ��ϵ���
    $_SESSION['s_sysmsg'] = 'ǧ�ڤ���Ƥ��ʤ���ǧ�ڴ��¤��ڤ�ޤ����������󤫤餪�ꤤ���ޤ���';
    // header('Location: http:' . WEB_HOST . 'menu.php');   // ����ƽи������
    header("Location: $url_referer");                   // ľ���θƽи������
    exit();
}

/********** Logic Start **********/
//////////// �����ȥ�����ա���������
$today = date('Y/m/d H:i:s');
//////////// �����ȥ�̾(�������Υ����ȥ�̾�ȥե�����Υ����ȥ�̾)
$menu_title = 'FWServer6�����å�';
//////////// ɽ�������
$caption    = '�����å��ѤΥ���ե��å�ɽ��';

//////////// JavaScript Stylesheet File ��ɬ���ɤ߹��ޤ���
$uniq = uniqid('target');

?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=EUC-JP">
<meta http-equiv="Content-Style-Type" content="text/css">
<title><?= $menu_title ?></title>
<script language="JavaScript">
<!--
    parent.menu_site.location = '<?= H_WEB_HOST . SITE_MENU ?>';
// -->
</script>
</head>
<body>
</body>
</html>
<script language='JavaScript'>
<!--
location.replace('http://fwserver6.tnk.co.jp/cgi-bin/mon.cgi?argHtmlFile=stsmon6.html');        // ��Ū�Υ�����ץȤ�ƽФ�
// -->
</script>
