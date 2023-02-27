<?php
//////////////////////////////////////////////////////////////////////////////
// �������칩�� ǧ�ڥե����� authenticate.php                               //
// Copyright (C) 2001-2006 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp      //
// Changed history                                                          //
// 2001/07/07 Created  index1.php --> authenticate.php                      //
// 2002/08/01 ���å����������ɲ� & register_globals off �б�              //
// 2002/08/07 ����header �θ�� exit() ���ɲ�                               //
//                           s_sysmsg = "" �ޤǼ¹Ԥ��Ƥ��ޤ�����           //
// 2002/09/20 �Ұ���ϣ����������� & right view ������ʸ���ѹ�              //
// 2003/02/20 ��ư���˼Ұ������ select ����褦���ѹ� focus() �� NG        //
//          login ��ˤ� Window �����̤˽ФƤ��ޤ����� next_focus()���б�   //
// 2003/02/26 ʸ����������֥饦�������ѹ��Ǥ��ʤ����� title-font ��        //
// 2003/12/15 ob_start('ob_gzhandler') ���ɲ� confirm.js��login.js���ѹ�    //
// 2004/01/28 [�Ұ�No]�򣶷�̤���ʤ鼫ư���ͤ���褦���ѹ���������          //
//            6���������� �� Ƭ��0�Ͼ�ά��ǽ �ڤ� style sheet�θ�꽤��     //
// 2004/02/02 index1.php �� authenticate.php ��̾�����ѹ�                   //
// 2004/03/10 ���饤����ȤΥ��å���̵�����к����å����ɲ�                //
// 2005/01/14 ��Ͽ����Ƥ��ʤ���祻�å�����s_sysmsg����Ͽ����            //
// 2005/01/24 �ǥ������쿷(�֥饤��ɥ��᡼����)���authenticate_bak1.php //
//            userid��onChange='next_focus()'����REQUEST�Ǽ����������椹��//
//            onLoad='window.blur(); �� ini_focus(); �ν��֤��뤳��       //
// 2005/09/21 E_ALL �� E_STRICT     body��onFocus='ini_focus()'���ɲ�       //
// 2005/10/13 �ѥ���ɤΥե���Ȥ�auto_font �� pass_font ���ѹ�           //
// 2005/11/24 <link rel='shortcut icon' href='/favicon.ico'>�ɲ�            //
// 2006/07/07 ���硼�ȥ��åȥ�����JSP/ASP������ɸ�ॿ�����ѹ�               //
//////////////////////////////////////////////////////////////////////////////
// ini_set('error_reporting', E_STRICT);       // E_STRICT=2048(php5) E_ALL=2047(php4) debug ��
ini_set('error_reporting', E_ALL);          // E_STRICT=2048(php5) E_ALL=2047(php4) debug ��
// ini_set('display_errors', '1');             // Error ɽ�� ON debug �� ��꡼���女����
ob_start('ob_gzhandler');                   // ���ϥХåե���gzip����
session_start();                            // ini_set()�μ��˻��ꤹ�뤳�� Script �Ǿ��
require_once ('function.php');              // ���̥ե��󥯥å����
access_log();                               // Script Name �ϼ�ư����
if ( !isset($_SESSION['r_addr']) ) {        // URL��authenticate.php������쥯�Ȥ˻��ꤷ�������к���
    header('Location: http:' . WEB_HOST . 'index.php?' . SID);  // SID���ղäϥ��å���̵�����к�
    exit();
}
if ( isset($_SESSION['s_sysmsg']) ) {
    $sysmsg = $_SESSION['s_sysmsg'];
    $_SESSION['s_sysmsg'] = '';
} else {
    if (isset($_GET['PHPSESSID'])) {
        $sysmsg = "<font color='yellow'>���å�����̵���ˤʤäƤ��ޤ���ͭ���ˤ��Ƥ�������󤷤Ʋ�������</font>";
    } else {
        $sysmsg = '';
        $_SESSION['s_sysmsg'] = '';         // ���å�����s_sysmsg����Ͽ����
    }
}
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
<meta http-equiv="Content-Type" content="text/php;charset=euc-jp">
<meta http-equiv="Content-Style-Type" content="text/css">
<title>�������칩��</title>
<script language='JavaScript' src='<?php echo ROOT ?>login.js'>
</script>
<script language='JavaScript'>
<!--
function ini_focus(){
    <?php if (!isset($_REQUEST['background'])) { ?>
    document.login_form.userid.focus();
    <?php } ?>
    document.login_form.userid.select();
}
function next_focus(){
    document.login_form.passwd.focus();
    document.login_form.passwd.select();
    //  onChange='next_focus()'
}
//-->
</script>
<style type="text/css">
<!--
body {
    margin:         10%;
}
form {
    margin:         0%;
}
.auth_font {
    font-size:      14.0pt;
    font-weight:    bold;
    font-family:    monospace;
    color:          blue;
}
.pass_font {
    font-size:      11.0pt;
    font-weight:    bold;
    font-family:    monospace;
    color:          blue;
}
.sysmsg_title {
    font-size:      8.7pt;
    font-weight:    normal;
    color:          #000000;
}
.sysmsg_body {
    font-size:      11.0pt;
    font-weight:    bold;
    font-family:    monospace;
    /* color:          #ff7e50; */
    color:          teal;
}
.winbox_field {
    border-style:           solid;
    border-width:           1px;
    border-top-color:       #bdaa90;
    border-left-color:      #bdaa90;
    border-right-color:     #FFFFFF;
    border-bottom-color:    #FFFFFF;
    /* background-color:    #d6d3ce; */
    background-image:       url(<?php echo IMG ?>blind_silver.gif);
    background-repeat:      repeat;
}
-->
</style>
<link rel='shortcut icon' href='/favicon.ico?=<?php echo time() ?>'>
</head>

<body onLoad='<?php if (isset($_REQUEST['background'])) echo 'window.blur(); ' ?>ini_focus();' onFocus='ini_focus()'>
    <center>
        <table bgcolor='#d6d3ce' align='center' border='1' cellspacing='0' cellpadding='10' width='400' height='300'>
            <tr><td> <!----------- ���ߡ�(�ǥ�������) ------------>
        <table class='winbox_field' width='100%' height='100%' border='0'>
            <tr>
                <td align='center' colspan='2'>
                    <img src='<?php echo IMG ?>t_nitto_logo3.gif' border=0>
                </td>
            </tr>
            <form name='login_form' method='post' action='login.php' onSubmit='return inpConf(this)'>
            <tr>
                <td width='40%' align='right'>
                    <font class='auth_font'>�Ұ�No.</font>
                </td>
                <td align='left' class='auth_font'>
                    <?php if (!isset($_REQUEST['background'])) { ?>
                    <input class='auth_font' type='text' name='userid' size='6' maxlength='6' tabindex='2'>
                    <?php } else { ?>
                    <input class='auth_font' type='text' name='userid' size='6' maxlength='6' tabindex='2' onChange='next_focus()'>
                    <?php } ?>
                    <br>
                    Ƭ��0�Ͼ�ά��ǽ
                </td>
            </tr>
            <tr>
                <td width='40%' align='right'>
                    <font class='auth_font'>�ѥ����</font>
                </td>
                <td align='left'>
                    <input class='pass_font' type='password' name='passwd' size='15' maxlength='8' tabindex='3'>
                </td>
            </tr>
            <tr>
                <td colspan='2' align='center'>
                    <input type='submit' value='������' tabindex='4' onFocus='ini_focus()'>
                </td>
            </form>
            </tr>
            <tr>
                <td colspan='2'>
                    <font class='sysmsg_title' tabindex='1' onFocus='ini_focus()'>[ �����ƥ��å����� ]</font><br>
                    <font class='sysmsg_body'><?php echo $sysmsg ?></font>
                    <noscript>
                        <font class='sysmsg_body' color='#ff7e00'>JavaScript��̵���ˤʤäƤ��ޤ���ͭ���ˤ��Ƥ�������󤷤Ʋ�������</font>
                    </noscript>
                </td>
            </tr>
        </table>
            </td></tr>
        </table> <!----------------- ���ߡ�End ------------------>
    </center>
</body>
<?php
if ($sysmsg != '') {
    echo "<script language='JavaScript'>\n";
    echo "<!--\n";
    echo "var count = 0;\n";
    echo "var ax = new Array(-6,-0, 6, 0);\n";
    echo "var ay = new Array(0, -6, 0, 6);\n";
    echo "function shake ( ) {\n";
    echo "    window.moveBy(ax[count % 4], ay[count % 4]);\n";
    echo "    count++;\n";
    echo "    if (count < 40) setTimeout('shake();', 10);\n";
    echo "}\n";
    echo "shake();\n";
    echo "// -->\n";
    echo "</script>\n";
}
?>
</html>
<?php
ob_end_flush();                 // ���ϥХåե���gzip���� END
?>
