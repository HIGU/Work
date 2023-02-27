<?php
//////////////////////////////////////////////////////////////////////////////
// PHP ����ե��᡼����� �����ƥ����                                      //
// Copyright(C) 2001-2004 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp       //
// �ѹ�����                                                                 //
// 2001/10/01 �������� phpinfo.php                                          //
// 2002/12/03 access_log �ȸ��¤��ɲáʥ����ȥ�˥塼�����줿�����         //
// 2004/05/27 phpinfo(options) ���ץ����ѥ�᡼���ɲä���­�������ɲ�     //
//////////////////////////////////////////////////////////////////////////////
ini_set('error_reporting', E_ALL);  // E_ALL='2047' debug ��
ini_set('display_errors', '1');     // Error ɽ�� ON debug �� ��꡼���女����
session_start();                    // ini_set()�μ��˻��ꤹ�뤳�� Script �Ǿ��
require_once ('../function.php');
access_log();                       // Script Name ��ư����

///////////// �����ȥ�˥塼������
$_SESSION['site_index'] = 99;       // �Ǹ�Υ�˥塼�ˤ��뤿�� 99 �����
$_SESSION['site_id']    = 51;       // �Ȥꤢ�������̥�˥塼̵�� (0 < �Ǥ���)

///////////// ǧ�ڥ����å�
if ( (!isset($_SESSION['Auth'])) || $_SESSION['Auth'] <= 2) {
// if (!isset($_SESSION["User_ID"])||!isset($_SESSION["Password"])||!isset($_SESSION["Auth"])) {
    $_SESSION['s_sysmsg'] = 'ľ�ܥե��������ꤷ�Ƥ�����Ը��¤��ʤ��Ȼ��ѤǤ��ޤ���';
    header('Location: ' . H_WEB_HOST);                  // �����ȥȥåפ����
    exit();
}

header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");    // ���դ����
header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT"); // ��˽�������Ƥ���
header("Cache-Control: no-store, no-cache, must-revalidate");  // HTTP/1.1
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");                          // HTTP/1.0
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=EUC-JP">
<meta http-equiv="Content-Style-Type" content="text/css">
<title>PHPINFO</title>
<script language="JavaScript">
<!--
    parent.menu_site.location = 'http:<?php echo(WEB_HOST) ?>menu_site.php';
// -->
</script>
<style type="text/css">
<!--
select {
    background-color:teal;
    color:white;
}
textarea {
    background-color:black;
    color:white;
}
input.sousin {
    background-color:red;
}
input.text {
    background-color:black;
    color:white;
}
.pt14 {
    font-size:14pt;
}
.pt12b {
    font:bold 12pt;
}
.margin1 {
    margin: 1%;
}
-->
</style>
</head>
<body class='margin1'>
<table align='center' with=100% border='3' cellspacing='0' cellpadding='0'>
    <form action='system_menu.php' method='post'>
        <td><input class='pt12b' type="submit" name="free_chk" value="���" ></td>
    </form>
</table>

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