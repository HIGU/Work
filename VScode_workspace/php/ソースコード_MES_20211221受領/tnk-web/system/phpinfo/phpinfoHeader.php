<?php
//////////////////////////////////////////////////////////////////////////////
// PHP ����ե��᡼����� �����ƥ����                                      //
// Copyright(C) 2001-2004 2007 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp  //
// History                                                                  //
// 2001/10/01 Created   phpinfo.php                                         //
// 2002/12/03 access_log �ȸ��¤��ɲáʥ����ȥ�˥塼�����줿�����         //
// 2004/05/27 phpinfo(options) ���ץ����ѥ�᡼���ɲä���­�������ɲ�     //
// 2004/07/20 changed  phpinfoHeader.php �ե졼���б��ڤ� MenuHeader ����   //
// 2004/07/21 mhForm.target��document.mhForm.target���ѹ� NN7.1�Ǿ�ά�Բ�   //
// 2005/09/10 phpinfoMain��out_site_java()������˰�ư IE��JS��ʸ���顼�к� //
// 2007/04/21 ��ƣ��Ҥ����Ѥ�ǧ�ڥ����å����ɲ�                            //
//////////////////////////////////////////////////////////////////////////////
ini_set('error_reporting', E_ALL);          // E_ALL='2047' debug ��
ini_set('display_errors', '1');             // Error ɽ�� ON debug �� ��꡼���女����
session_start();                            // ini_set()�μ��˻��ꤹ�뤳�� Script �Ǿ��
require_once ('../../function.php');        // TNK ������ function
require_once ('../../MenuHeader.php');      // TNK ������ menu class
access_log();                               // Script Name �ϼ�ư����

///// TNK ���ѥ�˥塼���饹�Υ��󥹥��󥹤����
if ($_SESSION['User_ID'] == '300161') {     // ��ƣ��Ҥ���ξ��ϥƥ��ȴĶ�������Τǰ��̥桼������
    $menu = new MenuHeader(0);                  // ǧ�ڥ����å�3=admin�ʾ� �����=���å������ �����ȥ�̤����
} else {
    $menu = new MenuHeader(3);                  // ǧ�ڥ����å�3=admin�ʾ� �����=���å������ �����ȥ�̤����
}

////////////// ����������
$menu->set_site(99, 51);                // site_index=99(�����ƥ������˥塼) site_id=51(phpinfo)
//////////// �����ȥ�̾(�������Υ����ȥ�̾�ȥե�����Υ����ȥ�̾)
$menu->set_title('PHP Information');
////////////// target����
// $menu->set_target('application');       // �ե졼���Ǥ�������target°����ɬ��
$menu->set_target('_parent');           // �ե졼���Ǥ�������target°����ɬ��

// ����JavaScript��function�ǻ��Ѥ��Ƥ���ե�����̾ mhForm ��MenuHeader class��default�������Ƥ�

/////////// HTML Header ����Ϥ��ƥ���å��������
$menu->out_html_header();
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=EUC-JP">
<meta http-equiv="Content-Style-Type" content="text/css">
<title><?= $menu->out_title() ?></title>
<?= $menu->out_site_java() ?>
<?= $menu->out_css() ?>
<Script Language='JavaScript'>
<!--
function setTarget() {
    document.mhForm.target = 'application';
    // document.mhForm.target = '_parent'; //����Window(frame)̾�����Window̾���ѹ�
    // NV7.1�Ǥ�document���ά�Ǥ��ʤ��������
    // ���Ѥ���Ȼ��� <body onLoad='setTarget()'>
}
-->
</Script>
</head>
<body>
    <center>
<?= $menu->out_title_border() ?>
    </center>
</body>
</html>
