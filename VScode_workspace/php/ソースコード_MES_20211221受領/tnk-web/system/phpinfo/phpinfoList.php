<?php
//////////////////////////////////////////////////////////////////////////////
// PHP ����ե��᡼����� �����ƥ����                                      //
// Copyright(C) 2001-2004 2007 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp  //
// History                                                                  //
// 2001/10/01 Created   phpinfo.php                                         //
// 2002/12/03 access_log �ȸ��¤��ɲáʥ����ȥ�˥塼�����줿�����         //
// 2004/05/27 phpinfo(options) ���ץ����ѥ�᡼���ɲä���­�������ɲ�     //
// 2004/07/20 changed  phpinfoList.php �ե졼���б��ڤ� MenuHeader ����     //
// 2007/04/21 ��ƣ��Ҥ����Ѥ�ǧ�ڥ����å����ɲ�                            //
// 2007/09/11 zend 1����ѥ��򳰤�                                          //
// 2007/09/18 E_ALL | E_STRICT ���ѹ�                                       //
//////////////////////////////////////////////////////////////////////////////
// ini_set('error_reporting', E_ALL | E_STRICT);   // E_ALL='2047' debug ��
ini_set('display_errors', '1');                 // Error ɽ�� ON debug �� ��꡼���女����
// ini_set('zend.ze1_compatibility_mode', '1');    // zend 1.X ����ѥ�
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
$menu->set_site(99, 51);                    // site_index=99(�����ƥ������˥塼) site_id=51(phpinfo)
//////////// �����ȥ�̾(�������Υ����ȥ�̾�ȥե�����Υ����ȥ�̾)
$menu->set_title('PHP Information');

/////////// HTML Header ����Ϥ��ƥ���å��������
$menu->out_html_header();
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=EUC-JP">
<meta http-equiv="Content-Style-Type" content="text/css">
<title><?= $menu->out_title() ?></title>
<?= $menu->out_css() ?>
</head>
<body>
<?php
    phpinfo();    // Default
    /*******************************************************************************************************
    �ʲ��ˤ���constants�ӥå��ͤ�ҤȤĤޤ��� ʣ���Ĥ�û����ơ����ץ�����what�������Ϥ����Ȥˤ�ä�
        ���Ϥ򥫥����ޥ����Ǥ��ޤ��� ���줾��������ӥå��ͤ�or�黻�ҤǷ����Ϥ����Ȥ�Ǥ��ޤ���
    phpinfo() options
    ̾��(���)         �� ���� 
    INFO_GENERAL        1 The configuration line, php.ini location, build date, Web Server, System and more.  
    INFO_CREDITS        2 PHP 4 Credits. See also phpcredits().  
    INFO_CONFIGURATION  4 Current Local and Master values for php directives. See also ini_get().  
    INFO_MODULES        8 Loaded modules and their respective settings. See also get_loaded_modules().  
    INFO_ENVIRONMENT   16 Environment Variable information that's also available in $_ENV.  
    INFO_VARIABLES     32 Shows all predefined variables from EGPCS (Environment, GET, POST, Cookie, Server).  
    INFO_LICENSE       64 PHP License information. See also the license faq.  
    INFO_ALL           -1 Shows all of the above. This is the default value.  
    
    <example>
    phpinfo(32);        EGPCS����ѿ����ͤ�ɽ�� (debug��)
    phpinfo(32 | 64);   EGPCS����ѿ����ͤ�ɽ�� �� �饤����ɽ��
    phpinfo(3);         1+2=3 GENERAL �� CREDITS ��ɽ��
    *******************************************************************************************************/
?>
</body>
</html>
<?php ob_end_flush(); // ���ϥХåե���gzip���� END ?>
