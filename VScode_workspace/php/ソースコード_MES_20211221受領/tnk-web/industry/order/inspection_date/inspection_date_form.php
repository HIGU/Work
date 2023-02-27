<?php
//////////////////////////////////////////////////////////////////////////////
// ���������ξȲ� �������ե�����  ������ UKWLIB/W#HIBCTR                  //
// Copyright (C) 2016-     Norihisa.Ohya norihisa_ooya@nitto-kohki.co.jp    //
// Changed history                                                          //
// 2016/01/29 Created  inspection_date_form.php                             //
//////////////////////////////////////////////////////////////////////////////
ini_set('error_reporting', E_ALL);          // E_ALL='2047' debug ��
// ini_set('display_errors', '1');             // Error ɽ�� ON debug �� ��꡼���女����
// ini_set('implicit_flush', 'off');           // echo print �� flush �����ʤ�(�٤��ʤ뤿��) CLI CGI��
// ini_set('max_execution_time', 1200);     // ����¹Ի���=20ʬ CLI CGI��
ob_start('ob_gzhandler');                   // ���ϥХåե���gzip����
session_start();                            // ini_set()�μ��˻��ꤹ�뤳�� Script �Ǿ��

require_once ('../../../function.php');
require_once ('../../../tnk_func.php');
require_once ('../../../MenuHeader.php');      // TNK ������ menu class
require_once ('../../../ControllerHTTP_Class.php');    // TNK ������ MVC Controller Class
access_log();                               // Script Name �ϼ�ư����

///// TNK ���ѥ�˥塼���饹�Υ��󥹥��󥹤����
$menu = new MenuHeader(0);                  // ǧ�ڥ����å�0=���̰ʾ� �����=TOP_MENU �����ȥ�̤����

////////////// ����������
$menu->set_site(30, 99);                    // site_index=40(������˥塼) site_id=10(��ݼ���)
////////////// �꥿���󥢥ɥ쥹����
// $menu->set_RetUrl(INDUST_MENU);             // �̾�ϻ��ꤹ��ɬ�פϤʤ�
//////////// �����ȥ�̾(�������Υ����ȥ�̾�ȥե�����Υ����ȥ�̾)
$menu->set_title('�� �� �� �� �� �� �� (�������)');
//////////// ɽ�������
$menu->set_caption('������ɬ�פʾ��������������򤷤Ʋ�������');
//////////// �ƽ����action̾�ȥ��ɥ쥹����
$menu->set_action('��ݼ���ɽ��',     INDUST . 'order/inspection_date/inspection_date_view.php');
$menu->set_action('��ݶ⽸��ɽ',     INDUST . 'order/inspection_date/inspection_date_summary.php');
$menu->set_action('��ݶ⽸��ɽ��',   INDUST . 'payable/payable_vendor_summary2.php');

//////////// JavaScript Stylesheet File ��ɬ���ɤ߹��ޤ���
$uniq = uniqid('target');

//////////// ���å����Υ��󥹥��󥹤�����
$session = new Session();

/////////////// �����Ϥ��ѿ��ν����
if (isset($_REQUEST['parts_no'])) {
    $parts_no = $_REQUEST['parts_no'];
    $_SESSION['paya_parts_no'] = $parts_no;
} else {
    if (isset($_SESSION['paya_parts_no'])) {
        $parts_no = $_SESSION['paya_parts_no'];
    } else {
        $parts_no = '';
    }
}
if ( isset($_SESSION['payable_div']) ) {
    $div = $_SESSION['payable_div'];
} else {
    $div = '';
    $_SESSION['payable_div'] = $div;
}
if ( isset($_SESSION['paya_vendor']) ) {
    $vendor = $_SESSION['paya_vendor'];
} else {
    $vendor = '';
    $_SESSION['paya_vendor'] = $vendor;
}
if ( isset($_SESSION['paya_kamoku']) ) {
    $kamoku = $_SESSION['paya_kamoku'];
} else {
    $kamoku = '';
    $_SESSION['paya_kamoku'] = $kamoku;
}
if ($session->get_local('paya_strdate') != '') {
    $str_date = $session->get_local('paya_strdate');
    $str_date = $_SESSION['paya_strdate'];
} elseif(isset($_SESSION['paya_strdate'])) {
    $str_date = $_SESSION['paya_strdate'];
} else {
    /*************************************
    $year  = date('Y');
    $month = date('m') - 1; // ������������򥳥���
    if ($month == 0) {
        $month = 12;
        $year -= 1;
    } else {
        $month = sprintf('%02d', $month);
    }
    *************************************/
    $year  = date('Y') - 5; // ��ǯ������
    $month = date('m');
    $str_date = $year . $month . '01';  // ����� (����Σ���������ѹ�)
    $session->add_local('paya_strdate', $str_date);
}
if ($session->get_local('paya_enddate') != '') {
    $end_date = $session->get_local('paya_enddate');
    $end_date = $_SESSION['paya_enddate'];
} else {
    $end_date = '99999999';     // �����
    $session->add_local('paya_enddate', $end_date);
}

if ( isset($_SESSION['paya_page']) ) {   // ���ڡ���ɽ���Կ�����
    $paya_page = $_SESSION['paya_page'];
} else {
    $paya_page = 25;             // Default 25
    $_SESSION['paya_page'] = $paya_page;
}

/////////// summary_view ���ɲä��줿�ѥ�᡼����
if ( isset($_SESSION['payable_s_ym']) ) {
    $s_ym = $_SESSION['payable_s_ym'];
} else {
    $s_ym = '';
}
if ( isset($_SESSION['payable_e_ym']) ) {
    $e_ym = $_SESSION['payable_e_ym'];
} else {
    $e_ym = '';
}
if ( isset($_SESSION['payable_div']) ) {
    $sum_div = $_SESSION['payable_div'];
} else {
    $sum_div = ' ';
}

/////////// summary2_view ���ɲä��줿�ѥ�᡼����
if ( isset($_SESSION['payable_s2_ym']) ) {
    $s2_ym = $_SESSION['payable_s2_ym'];
} else {
    $s2_ym = '';
}
if ( isset($_SESSION['payable_e2_ym']) ) {
    $e2_ym = $_SESSION['payable_e2_ym'];
} else {
    $e2_ym = '';
}
if ( isset($_SESSION['payable2_div']) ) {
    $sum2_div = $_SESSION['payable2_div'];
} else {
    $sum2_div = ' ';
}

/////////// summary_view �������줿
if (isset($_REQUEST['summary_view'])) {
    if (isset($_REQUEST['s_ym'])) {
        $_SESSION['payable_s_ym'] = $_REQUEST['s_ym'];
    }
    if (isset($_REQUEST['e_ym'])) {
        $_SESSION['payable_e_ym'] = $_REQUEST['e_ym'];
    }
    if (isset($_REQUEST['sum_div'])) {
        $_SESSION['payable_div'] = $_REQUEST['sum_div'];
    }
    header('Location: ' . H_WEB_HOST . $menu->out_action('��ݶ⽸��ɽ'));
}

/////////// summary2_view �������줿
if (isset($_REQUEST['summary2_view'])) {
    if (isset($_REQUEST['s2_ym'])) {
        $_SESSION['payable_s2_ym'] = $_REQUEST['s2_ym'];
    }
    if (isset($_REQUEST['e2_ym'])) {
        $_SESSION['payable_e2_ym'] = $_REQUEST['e2_ym'];
    }
    if (isset($_REQUEST['sum2_div'])) {
        $_SESSION['payable2_div'] = $_REQUEST['sum2_div'];
    }
    header('Location: ' . H_WEB_HOST . $menu->out_action('��ݶ⽸��ɽ��'));
}
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

<!--    �ե��������ξ�� -->
<script language='JavaScript' src='./inspection_date_form.js?<?= $uniq ?>'>
</script>

<script language="JavaScript">
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
    border-right-color:     #999999;
    border-bottom-color:    #999999;
    background-color:#d6d3ce;
}
.winbox_field {
    border-style: solid;
    border-width: 1px;
    border-top-color:       #999999;
    border-left-color:      #999999;
    border-right-color:     #FFFFFF;
    border-bottom-color:    #FFFFFF;
    background-color:#d6d3ce;
}
.caption_font {
    font-size:          11pt;
    font-weight:        bold;
    font-family:        monospace;
    background-color:   blue;
    color:              white;
}
-->
</style>
</head>
</style>
<body onLoad='document.payable_form.parts_no.focus(); document.payable_form.parts_no.select()'>
    <center>
<?=$menu->out_title_border()?>
        
        <form name='payable_form' action='<?=$menu->out_action('��ݶ⽸��ɽ')?>' method='get' onSubmit='return chk_payable_form(this)'>
            <!----------------- ������ ��ʸ��ɽ������ ------------------->
            <table bgcolor='#d6d3ce' border='1' cellspacing='0' cellpadding='3'>
                <tr><td> <!----------- ���ߡ�(�ǥ�������) ------------>
            <table width='100%' class='winbox_field' bgcolor='#d6d3ce' border='1' cellspacing='0' cellpadding='3'>
                <tr>
                        <!--  bgcolor='#ffffc6' �������� --> 
                    <td class='winbox' bgcolor='yellow' colspan='2' align='center'>
                        <div class='caption_font'><?=$menu->out_caption(), "\n"?></div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' align='right'>
                        �����ֹ�λ���(���ꤷ�ʤ����ϲ����������ꤷ�Ʋ�����)
                    </td>
                    <td class='winbox' align='center'>
                        <input type='text' name='parts_no' class='pt12b' size='9' value='<?= $parts_no ?>' maxlength='9'>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' align='right'>
                        ��������򤷤Ʋ�����(���ʤ���ꤷ������̵�뤵��ޤ�)
                    </td>
                    <td class='winbox' align='center'>
                        <select name='div' style='font-size:11pt; font-weight:bold; font-family:monospace;'>
                            <option value=' '<?php if($div==' ') echo 'selected' ?>>������</option>
                            <option value='C'<?php if($div=='C') echo 'selected' ?>>���ץ�����</option>
                            <!-- <option value='S'<?php if($div=='S') echo 'selected' ?>>������</option> -->
                            <option value='D'<?php if($div=='D') echo 'selected' ?>>���ץ�ɸ��</option>
                            <option value='S'<?php if($div=='S') echo 'selected' ?>>���ץ�����</option>
                            <option value='L'<?php if($div=='L') echo 'selected' ?>>��˥�</option>
                            <!-- <option value='B'<?php if($div=='B') echo 'selected' ?>>�Х����</option> -->
                            <option value='T'<?php if($div=='T') echo 'selected' ?>>�ġ���</option>
                        </select>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' align='right'>
                        ȯ����Υ����ɤ����(���ʤ���ꤷ������̵�뤵��ޤ�)
                    </td>
                    <td class='winbox' align='center'>
                        <input type='text' name='vendor' class='pt12b' size='5' value='<?= $vendor ?>' maxlength='5'>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' align='right'>
                        ���դ���ꤷ�Ʋ�����(ɬ��)
                    </td>
                    <td class='winbox' align='center'>
                        <input type='text' name='str_date' size='9' value='<?php echo($str_date); ?>' maxlength='8' style='font-size:11pt; font-weight:bold; font-family:monospace;'>
                        ��
                        <input type='text' name='end_date' size='9' value='<?php echo($end_date); ?>' maxlength='8' style='font-size:11pt; font-weight:bold; font-family:monospace;'>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' align='right'>
                        ���ڡ�����ɽ���Կ�����ꤷ�Ʋ�����
                    </td>
                    <td class='winbox' align='center'>
                        <input type='text' name='paya_page' size='4' value='<?= $paya_page ?>' maxlength='4' style='font-size:11pt; font-weight:bold; font-family:monospace;'>
                        ����͡�25
                    </td>
                </tr>
                <tr>
                    <td class='winbox' colspan='2' align='center'>
                        <input type='submit' name='paya_view' value='�¹�' >
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
