<?php
//////////////////////////////////////////////////////////////////////////////
// ����������� ���ۤξȲ� �������ե�����                                 //
// Copyright(C) 2011      Noriisa.Ohya norihisa_ooya@nitto-kohki.co.jp      //
// Changed history                                                          //
// 2011/05/16 Created   material_compare_form.php                           //
// 2011/05/17 ɽ������˳�Ψ����ɲ�                                        //
// 2011/05/26 ǯ��ν���ͤ������ѹ������ʥ��롼�׽���ɲ�                //
// 2011/05/30 ����������Ӥ��̥�˥塼�ˤޤȤ᤿��require_once�Υ���ѹ�//
//////////////////////////////////////////////////////////////////////////////
// ini_set('error_reporting', E_ALL);          // E_ALL='2047' debug ��
// ini_set('display_errors', '1');             // Error ɽ�� ON debug �� ��꡼���女����
// ini_set('zend.ze1_compatibility_mode', '1');    // zend 1.X ����ѥ� php4�θߴ��⡼��
// ini_set('implicit_flush', 'off');           // echo print �� flush �����ʤ�(�٤��ʤ뤿��) CLI��
// ini_set('max_execution_time', 1200);        // ����¹Ի���=20ʬ WEB CGI��
ob_start('ob_gzhandler');                   // ���ϥХåե���gzip����
session_start();                            // ini_set()�μ��˻��ꤹ�뤳�� Script �Ǿ��

require_once ('../../function.php');
require_once ('../../tnk_func.php');
require_once ('../../MenuHeader.php');      // TNK ������ menu class
access_log();                               // Script Name �ϼ�ư����

///// TNK ���ѥ�˥塼���饹�Υ��󥹥��󥹤����
$menu = new MenuHeader(0);                  // ǧ�ڥ����å�0=���̰ʾ� �����=TOP_MENU �����ȥ�̤����

////////////// ����������
$menu->set_site(30, 999);                   // site_index=30(������˥塼) site_id=21(����������Ͽ �ײ��ֹ�)
////////////// �꥿���󥢥ɥ쥹����
// $menu->set_RetUrl(SYS_MENU);                // �̾�ϻ��ꤹ��ɬ�פϤʤ�
//////////// �����ȥ�̾(�������Υ����ȥ�̾�ȥե�����Υ����ȥ�̾)
$menu->set_title('����������� �������پȲ�');
//////////// ɽ�������
$menu->set_caption('������ɬ�פʾ��������������򤷤Ʋ�������');
//////////// �ƽ����action̾�ȥ��ɥ쥹����
$menu->set_action('�����������',   INDUST . 'material_compare/material_compare_view.php');

//////////// �֥饦�����Υ���å����к���
$uniq = $menu->set_useNotCache('target');

/////////////// �����Ϥ��ѿ��ν����
if ( isset($_SESSION['s_uri_passwd']) ) {
    $uri_passwd = $_SESSION['s_uri_passwd'];
} else {
    $uri_passwd = '';
}
if ( isset($_SESSION['s_div']) ) {
    $div = $_SESSION['s_div'];
} else {
    $div = '';
}
if ( isset($_SESSION['s_first_ym']) ) {
    $first_ym = $_SESSION['s_first_ym'];
} else {
    if ( isset($_POST['first_ym']) ) {
        $first_ym = $_POST['first_ym'];
    } else {
        //$first_ym = date_offset(1);
        $first_ym = '';
    }
}
if ( isset($_SESSION['s_second_ym']) ) {
    $second_ym = $_SESSION['s_second_ym'];
} else {
    if ( isset($_POST['second_ym']) ) {
        $second_ym = $_POST['second_ym'];
    } else {
        //$second_ym = date_offset(1);
        $second_ym = '';
    }
}
if ( isset($_SESSION['uri_assy_no']) ) {
    $assy_no = $_SESSION['uri_assy_no'];
} else {
    $assy_no = '';      // �����
}
if ( isset($_SESSION['s_order']) ) {
    $order = $_SESSION['s_order'];
} else {
    $order = 'assy';      // �����
}

/////////// HTML Header ����Ϥ��ƥ���å��������
$menu->out_html_header();
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=EUC-JP">
<meta http-equiv="Content-Style-Type" content="text/css">
<meta http-equiv="Content-Script-Type" content="text/javascript">
<title><?php echo $menu->out_title() ?></title>
<?php echo $menu->out_site_java()?>
<?php echo $menu->out_css()?>
<?php echo $menu->out_jsBaseClass() ?>
<script type='text/javascript' src='./material_compare_form.js?<?php echo $uniq ?>'>
</script>

<script type='text/javascript' language='JavaScript'>
<!--
/* ������ϥե�����Υ�����Ȥ˥ե������������� */
function set_focus(){
//    document.form_name.element_name.focus();      // ������ϥե����ब������ϥ����Ȥ򳰤�
//    document.form_name.element_name.select();
}
// -->
</script>

<!-- �������륷���ȤΥե��������򥳥��� HTML���� �����Ȥ�����Ҥ˽���ʤ��������
<link rel='stylesheet' href='<?php echo MENU_FORM . '?' . $uniq ?>' type='text/css' media='screen'>
-->

<style type="text/css">
<!--
.pt8 {
    font-size:      8pt;
    font-weight:    normal;
    font-family:    monospace;
}
.pt9 {
    font-size:      9pt;
    font-weight:    normal;
    font-family:    monospace;
}
.pt10 {
    font-size:      10pt;
    font-weight:    normal;
    font-family:    monospace;
}
.pt10b {
    font-size:      10pt;
    font-weight:    bold;
    font-family:    monospace;
}
.pt11b {
    font-size:      11pt;
    font-weight:    bold;
    font-family:    monospace;
}
.pt12b {
    font-size:      12pt;
    font-weight:    bold;
    font-family:    monospace;
}
th {
    background-color:   yellow;
    color:              blue;
    font-size:          10pt;
    font-weight:        bold;
    font-family:        monospace;
}
td {
    font-size: 10pt;
}
.winbox {
    border-style: solid;
    border-width: 1px;
    border-top-color:       #FFFFFF;
    border-left-color:      #FFFFFF;
    border-right-color:     #bdaa90;
    border-bottom-color:    #bdaa90;
    /* background-color:#d6d3ce; */
}
.winbox_field {
    border-style: solid;
    border-width: 1px;
    border-top-color:       #bdaa90;
    border-left-color:      #bdaa90;
    border-right-color:     #FFFFFF;
    border-bottom-color:    #FFFFFF;
    /* background-color:#d6d3ce; */
}
a:hover {
    background-color:   blue;
    color:              white;
}
a {
    color:   blue;
}
body {
    background-image:url(<?php echo IMG ?>t_nitto_logo4.png);
    background-repeat:no-repeat;
    background-attachment:fixed;
    background-position:right bottom;
}
-->
</style>
</head>
</style>
<body onLoad='document.uri_form.uri_passwd.focus(); document.uri_form.uri_passwd.select()' style='overflow:hidden;'>
    <center>
<?php echo $menu->out_title_border()?>
        
        <form name='uri_form' action='<?php echo $menu->out_action('�����������')?>' method='post' onSubmit='return chk_sales_form(this)'>
            <!----------------- ������ ��ʸ��ɽ������ ------------------->
            <table bgcolor='#d6d3ce' border='1' cellspacing='0' cellpadding='5'>
                <tr><td> <!----------- ���ߡ�(�ǥ�������) ------------>
            <table class='winbox_field' width='100%' bgcolor='#d6d3ce' border='1' cellspacing='0' cellpadding='3'>
                <tr>
                        <!--  bgcolor='#ffffc6' �������� --> 
                    <td class='winbox' style='background-color:yellow; color:blue;' colspan='2' align='center'>
                        <div class='caption_font'><?php echo $menu->out_caption(), "\n"?></div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' align='right'>
                        �ѥ���ɤ�����Ʋ�����
                    </td>
                    <td class='winbox' align='center'>
                        <input type='password' name='uri_passwd' size='12' value='<?php echo("$uri_passwd"); ?>' maxlength='8'>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' align='right'>
                        ���ʥ��롼�פ����򤷤Ʋ�����
                    </td>
                    <td class='winbox' align='center'>
                        <select name="div">
                            <option value="C"<?php if($div=="C") echo("selected"); ?>>���ץ�</option>
                            <option value="L"<?php if($div=="L") echo("selected"); ?>>��˥�</option>
                        </select>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' align='right'>
                        ��Ӥ������դ���ꤷ�Ʋ�����(ɬ��)
                    </td>
                    <td class='winbox' align='center'>
                        <input type="text" name="first_ym" size="7" value="<?php echo($first_ym); ?>" maxlength="6">
                        ��
                        <input type="text" name="second_ym" size="7" value="<?php echo($second_ym); ?>" maxlength="6">
                    </td>
                </tr>
                <tr>
                    <td class='winbox' align='right'>
                        �����ֹ�λ���
                        (���ꤷ�ʤ����϶���)
                    </td>
                    <td class='winbox' align='center'>
                        <input type='text' name='assy_no' size='11' value='<?php echo $assy_no ?>' maxlength='9'>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' align='right'>
                        ɽ���������ꤷ�Ƥ���������
                    </td>
                    <td class='winbox' align='center'>
                        <select name="order">
                            <option value="assy"<?php if($order=="assy") echo("selected"); ?>>�����ֹ��</option>
                            <option value="diff"<?php if($order=="diff") echo("selected"); ?>>�����۽�</option>
                            <option value="per"<?php if($order=="per") echo("selected"); ?>>����Ψ��</option>
                            <option value="power"<?php if($order=="power") echo("selected"); ?>>��Ψ��</option>
                            <option value="sorder"<?php if($order=="sorder") echo("selected"); ?>>���ʥ��롼�׽�</option>
                        </select>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' colspan='2' align='center'>
                        <input type="submit" name="�Ȳ�" value="�¹�" >
                    </td>
                </tr>
            </table>
                </td></tr>
            </table> <!----------------- ���ߡ�End ------------------>
        </form>
    </center>
</body>
<?php echo $menu->out_alert_java()?>
</html>
<?php
ob_end_flush();                 // ���ϥХåե���gzip���� END
?>
