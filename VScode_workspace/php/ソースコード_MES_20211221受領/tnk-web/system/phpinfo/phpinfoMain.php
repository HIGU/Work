<?php
//////////////////////////////////////////////////////////////////////////////
// PHP ����ե��᡼����� �����ƥ����                                      //
// Copyright(C) 2001-2004 2007 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp  //
// History                                                                  //
// 2001/10/01 Created   phpinfo.php                                         //
// 2002/12/03 access_log �ȸ��¤��ɲáʥ����ȥ�˥塼�����줿�����         //
// 2004/05/27 phpinfo(options) ���ץ����ѥ�᡼���ɲä���­�������ɲ�     //
// 2004/07/20 changed  phpinfoMain.php �ե졼���б��ڤ� MenuHeader ����     //
// 2004/07/22 NN7.1�б��Τ���frame��Header�����scrolling='no'���ɲ�        //
// 2004/08/10 out_action() �� out_frame()���ѹ�                             //
// 2005/09/10 out_site_java()��phpinfoHeader�ذ�ư IE��JS��ʸ���顼�к�     //
// 2007/04/21 ��ƣ��Ҥ����Ѥ�ǧ�ڥ����å����ɲ�                            //
//////////////////////////////////////////////////////////////////////////////
ini_set('error_reporting', E_ALL);              // E_ALL='2047' debug ��
ini_set('display_errors', '1');                 // Error ɽ�� ON debug �� ��꡼���女����
ob_start('ob_gzhandler');                       // ���ϥХåե���gzip����
session_start();                                // ini_set()�μ��˻��ꤹ�뤳�� Script �Ǿ��
require_once ('../../function.php');            // TNK ������ function
require_once ('../../MenuHeader.php');          // TNK ������ menu class
access_log();                                   // Script Name �ϼ�ư����

///// TNK ���ѥ�˥塼���饹�Υ��󥹥��󥹤����
if ($_SESSION['User_ID'] == '300161') {     // ��ƣ��Ҥ���ξ��ϥƥ��ȴĶ�������Τǰ��̥桼������
    $menu = new MenuHeader(0);                  // ǧ�ڥ����å�3=admin�ʾ� �����=���å������ �����ȥ�̤����
} else {
    $menu = new MenuHeader(3);                  // ǧ�ڥ����å�3=admin�ʾ� �����=���å������ �����ȥ�̤����
}

////////////// ����������
$menu->set_site(99, 51);                // site_index=99(�����ƥ������˥塼) site_id=51(phpinfo)
//////////// �����ȥ�̾(�������Υ����ȥ�̾�ȥե�����Υ����ȥ�̾)
$menu->set_title('PHP Information Main');
//////////// �ե졼��θƽ���Υ��������(frame)̾�ȥ��ɥ쥹����
$menu->set_frame('Header', SYS . 'phpinfo/phpinfoHeader.php');
$menu->set_frame('List'  , SYS . 'phpinfo/phpinfoList.php');
// �ե졼���Ǥ� $menu->set_action()�ǤϤʤ�$menu->set_frame()����Ѥ���

/////////// HTML Header ����Ϥ��ƥ���å��������
$menu->out_html_header();
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Frameset//EN"
    "http://www.w3.org/TR/html4/frameset.dtd">
<html>
<head>
<title><?= $menu->out_title() ?></title>
</head>
<frameset rows='55,*' name='phpinfoMain'>
    <frame src= '<?= $menu->out_frame('Header') ?>' name='Header' scrolling='no'>
    <frame src= '<?= $menu->out_frame('List') ?>'   name='List'>
</frameset>
</html>
<?php ob_end_flush(); // ���ϥХåե���gzip���� END ?>
