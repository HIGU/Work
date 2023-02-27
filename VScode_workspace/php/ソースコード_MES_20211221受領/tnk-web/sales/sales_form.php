<?php
//////////////////////////////////////////////////////////////////////////////
// �������칩�� ��� ���� �Ȳ� �������ե�����                             //
// Copyright(C) 2001-2005 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp       //
// Changed history                                                          //
// 2001/07/07 Created   uriage.php �� sales_form.php                        //
// 2002/08/07 ���å����������ɲ�                                          //
// 2002/08/27 �ե졼���б�                                                  //
// 2003/02/14 ���ط��˥塼 �Υե���Ȥ� style �ǻ�����ѹ�                //
//                              �֥饦�����ˤ���ѹ�������ʤ��ͤˤ���      //
// 2003/02/26 body �� onLoad ���ɲä�������ϸĽ�� focus() ������          //
// 2003/06/16 �������˥Х������ɲä����ڡ�����ɽ���Կ���������ɲ�        //
// 2003/09/05 error_reporting = E_ALL �б��Τ��� �����ѿ��ν�����ɲ�       //
// 2003/10/31 �����ֹ����ȥ��ץ����������ɲ� <td>��font-size 11pt     //
// 2003/11/26 �ǥ�����ȥ��å���쿷 uriage.php �� sales_form.php ��      //
// 2003/12/12 define���줿����ǥǥ��쥯�ȥ�ȥ�˥塼����Ѥ��ƴ�������    //
// 2003/12/23 JavaScript��uriage.js��sales_form.js���ѹ� sales_page���б�   //
// 2004/11/09 ����������롼�ס����ץ����Ρ�����ɸ�ࡦ��˥���������ʬ����//
// 2005/01/14 MenuHeader class ����Ѥ��ƶ��̥�˥塼���ڤ�ǧ���������ѹ�   //
// 2005/09/06 ���롼��(������)��̵���Τ⤬����Τǥ����å������褦���ɲ�  //
//////////////////////////////////////////////////////////////////////////////
ini_set('error_reporting', E_ALL);          // E_ALL='2047' debug ��
// ini_set('display_errors', '1');             // Error ɽ�� ON debug �� ��꡼���女����
// ini_set('zend.ze1_compatibility_mode', '1');    // zend 1.X ����ѥ� php4�θߴ��⡼��
// ini_set('implicit_flush', 'off');           // echo print �� flush �����ʤ�(�٤��ʤ뤿��) CLI��
// ini_set('max_execution_time', 1200);        // ����¹Ի���=20ʬ WEB CGI��
ob_start('ob_gzhandler');                   // ���ϥХåե���gzip����
session_start();                            // ini_set()�μ��˻��ꤹ�뤳�� Script �Ǿ��

require_once ('../function.php');
require_once ('../tnk_func.php');
require_once ('../MenuHeader.php');         // TNK ������ menu class
access_log();                               // Script Name �ϼ�ư����

///// TNK ���ѥ�˥塼���饹�Υ��󥹥��󥹤����
$menu = new MenuHeader(0);                  // ǧ�ڥ����å�0=���̰ʾ� �����=TOP_MENU �����ȥ�̤����

////////////// ����������
$menu->set_site( 1, 11);                    // site_index=01(����˥塼) site_id=11(����������)
////////////// �꥿���󥢥ɥ쥹����
// $menu->set_RetUrl(SYS_MENU);                // �̾�ϻ��ꤹ��ɬ�פϤʤ�
//////////// �����ȥ�̾(�������Υ����ȥ�̾�ȥե�����Υ����ȥ�̾)
$menu->set_title('�� �� �� �� �� �� �� ��');
//////////// ɽ�������
$menu->set_caption('������ɬ�פʾ��������������򤷤Ʋ�������');
//////////// �ƽ����action̾�ȥ��ɥ쥹����
$menu->set_action('�������',   SALES . 'sales_view.php');

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
if ( isset($_SESSION['s_d_start']) ) {
    $d_start = $_SESSION['s_d_start'];
} else {
    if ( isset($_POST['d_start']) ) {
        $d_start = $_POST['d_start'];
    } else {
        $d_start = date_offset(1);
    }
}
if ( isset($_SESSION['s_d_end']) ) {
    $d_end = $_SESSION['s_d_end'];
} else {
    if ( isset($_POST['d_end']) ) {
        $d_end = $_POST['d_end'];
    } else {
        $d_end = date_offset(1);
    }
}
if ( isset($_SESSION['s_kubun']) ) {
    $kubun = $_SESSION['s_kubun'];
} else {
    $kubun = '';
}
if ( isset($_SESSION['s_uri_ritu']) ) {
    $uri_ritu = $_SESSION['s_uri_ritu'];
    $uri_ritu = '52.0';     // �����
} else {
    $uri_ritu = '52.0';     // �����
}
if ( isset($_SESSION['uri_assy_no']) ) {
    $assy_no = $_SESSION['uri_assy_no'];
} else {
    $assy_no = '';      // �����
}


// $_SESSION['s_rec_No'] = 0;  // ɽ���ѥ쥳���ɭ��0�ˤ��롣

if ( isset($_SESSION['s_sales_page']) ) {   // ���ڡ���ɽ���Կ�����
    $sales_page = $_SESSION['s_sales_page'];     // ��� Default 25 �ˤʤ�褦�˥����Ȳ��
    // $sales_page = 25;             // Default 25
} else {
    $sales_page = 25;             // Default 25
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
<title><?= $menu->out_title() ?></title>
<?=$menu->out_site_java()?>
<?=$menu->out_css()?>
<?= $menu->out_jsBaseClass() ?>
<script type='text/javascript' src='./sales_form.js?<?= $uniq ?>'>
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
<link rel='stylesheet' href='<?= MENU_FORM . '?' . $uniq ?>' type='text/css' media='screen'>
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
    background-image:url(<?= IMG ?>t_nitto_logo4.png);
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
<?=$menu->out_title_border()?>
        
        <form name='uri_form' action='<?=$menu->out_action('�������')?>' method='post' onSubmit='return chk_sales_form(this)'>
            <!----------------- ������ ��ʸ��ɽ������ ------------------->
            <table bgcolor='#d6d3ce' border='1' cellspacing='0' cellpadding='5'>
                <tr><td> <!----------- ���ߡ�(�ǥ�������) ------------>
            <table class='winbox_field' width='100%' bgcolor='#d6d3ce' border='1' cellspacing='0' cellpadding='3'>
                <tr>
                        <!--  bgcolor='#ffffc6' �������� --> 
                    <td class='winbox' style='background-color:yellow; color:blue;' colspan='2' align='center'>
                        <div class='caption_font'><?=$menu->out_caption(), "\n"?></div>
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
                            <option value=" "<?php if($div==" ") echo("selected"); ?>>�����롼��</option>
                            <option value="C"<?php if($div=="C") echo("selected"); ?>>���ץ�����</option>
                            <option value="S"<?php if($div=="S") echo("selected"); ?>>���ץ�����</option>
                            <option value="D"<?php if($div=="D") echo("selected"); ?>>���ץ�ɸ��</option>
                            <option value="L"<?php if($div=="L") echo("selected"); ?>>��˥�����</option>
                            <option value="N"<?php if($div=="N") echo("selected"); ?>>��˥��Τ�</option>
                            <option value="B"<?php if($div=="B") echo("selected"); ?>>�Х����Τ�</option>
                            <option value="T"<?php if($div=="T") echo("selected"); ?>>�ġ���</option>
                            <option value="_"<?php if($div=="_") echo("selected"); ?>>�ʤ�</option>
                        </select>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' align='right'>
                        ���դ���ꤷ�Ʋ�����(ɬ��)
                    </td>
                    <td class='winbox' align='center'>
                        <input type="text" name="d_start" size="9" value="<?php echo($d_start); ?>" maxlength="8">
                        ��
                        <input type="text" name="d_end" size="9" value="<?php echo($d_end); ?>" maxlength="8">
                    </td>
                </tr>
                <tr>
                    <td class='winbox' align='right'>
                        �����ֹ�λ���
                        (���ꤷ�ʤ����϶���)
                    </td>
                    <td class='winbox' align='center'>
                        <input type='text' name='assy_no' size='11' value='<?= $assy_no ?>' maxlength='9'>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' align='right' width='400'>
                        ����ʬ=
                        �������� ��������(�̾�) �������� ����Ĵ�� ������ư ����ľǼ ������� 
                        ������ư���� �������ʼ���
                    </td>
                    <td class='winbox' align='center'>
                        <select name="kubun">
                            <option value=" "<?php if($kubun==" ") echo("selected"); ?>>����</option>
                            <option value="1"<?php if($kubun=="1") echo("selected"); ?>>1����</option>
                            <option value="2"<?php if($kubun=="2") echo("selected"); ?>>2����</option>
                            <option value="3"<?php if($kubun=="3") echo("selected"); ?>>3����</option>
                            <option value="4"<?php if($kubun=="4") echo("selected"); ?>>4Ĵ��</option>
                            <option value="5"<?php if($kubun=="5") echo("selected"); ?>>5��ư</option>
                            <option value="6"<?php if($kubun=="6") echo("selected"); ?>>6ľǼ</option>
                            <option value="7"<?php if($kubun=="7") echo("selected"); ?>>7���</option>
                            <option value="8"<?php if($kubun=="8") echo("selected"); ?>>8����</option>
                            <option value="9"<?php if($kubun=="9") echo("selected"); ?>>9����</option>
                        <select>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' align='right'>
                        ������ʤ��Ф������ñ����Ψ����ꤷ�Ʋ�������(�㡧52)<br>
                        <font color='red'>(���ץ�����ξ��)</font> ���ꤷ��Ψ ̤������<font color='red'>�ֿ�</font>��ɽ��
                    </td>
                    <td class='winbox' align='center'>
                        <input type="text" name="uri_ritu" size="4" value="<?php echo("$uri_ritu"); ?>" maxlength="4">
                        �� ̤��
                    </td>
                </tr>
            <!-- ���ߤϥ�����
                <input type='hidden' name='uri_ritu' value=''>
            -->
                <tr>
                    <td class='winbox' align='right'>
                        ���ڡ�����ɽ���Կ�����ꤷ�Ʋ�����
                    </td>
                    <td class='winbox' align='center'>
                        <input type='text' name='sales_page' size='4' value="<?php echo("$sales_page"); ?>" maxlength='4'>
                        ����͡�25
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
<?=$menu->out_alert_java()?>
</html>
<?php
ob_end_flush();                 // ���ϥХåե���gzip���� END
?>
