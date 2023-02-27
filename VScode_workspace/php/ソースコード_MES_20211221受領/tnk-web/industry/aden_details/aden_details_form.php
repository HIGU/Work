<?php
//////////////////////////////////////////////////////////////////////////////
// A�������ξȲ� �������ե�����  ������ UKWLIB/W#MIADIMDE                 //
// Copyright (C) 2016-2017 Norihisa.Ohya norihisa_ooya@nitto-kohki.co.jp    //
// Changed history                                                          //
// 2016/03/25 Created  aden_details_form.php                                //
// 2017/08/10 �ײ贰λ�ѡ�̤��λ�ξ����ɲ�                                //
//////////////////////////////////////////////////////////////////////////////
ini_set('error_reporting', E_ALL);          // E_ALL='2047' debug ��
// ini_set('display_errors', '1');             // Error ɽ�� ON debug �� ��꡼���女����
// ini_set('implicit_flush', 'off');           // echo print �� flush �����ʤ�(�٤��ʤ뤿��) CLI CGI��
// ini_set('max_execution_time', 1200);     // ����¹Ի���=20ʬ CLI CGI��
ob_start('ob_gzhandler');                   // ���ϥХåե���gzip����
session_start();                            // ini_set()�μ��˻��ꤹ�뤳�� Script �Ǿ��

require_once ('../../function.php');
require_once ('../../tnk_func.php');
require_once ('../../MenuHeader.php');      // TNK ������ menu class
require_once ('../../ControllerHTTP_Class.php');    // TNK ������ MVC Controller Class
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
$menu->set_action('A������ɽ��',     INDUST . 'aden_details/aden_details_view.php');

//////////// JavaScript Stylesheet File ��ɬ���ɤ߹��ޤ���
$uniq = uniqid('target');

//////////// ���å����Υ��󥹥��󥹤�����
$session = new Session();

/////////////// �����Ϥ��ѿ��ν����
if ($session->get('paya_parts_no') != '') {
    $parts_no = $session->get('paya_parts_no');
} else {
    $parts_no = '';              // �����
}
if ($session->get('paya_kamoku') != '') {
    $kamoku = $session->get('paya_kamoku');
} else {
    $kamoku = '';              // �����
}
if ( isset($_SESSION['payable_div']) ) {
    $div = $_SESSION['payable_div'];
} else {
    $div = '';
    $_SESSION['payable_div'] = $div;
}
if ( isset($_SESSION['payable_finishdel']) ) {
    $finish_del = $_SESSION['payable_finishdel'];
} else {
    $finish_del = ' ';
    $_SESSION['payable_finishdel'] = $finish_del;
}
if ( isset($_SESSION['payable_delicom']) ) {
    $deli_com = $_SESSION['payable_delicom'];
} else {
    $deli_com = ' ';
    $_SESSION['payable_delicom'] = $deli_com;
}
if ( isset($_SESSION['payable_answer']) ) {
    $answer = $_SESSION['payable_answer'];
} else {
    $answer = ' ';
    $_SESSION['payable_answer'] = $answer;
}
if ( isset($_SESSION['payable_finish']) ) {
    $finish = $_SESSION['payable_finish'];
} else {
    $finish = ' ';
    $_SESSION['payable_finish'] = $finish;
}
if ( isset($_SESSION['payable_koujino']) ) {
    $kouji_no = $_SESSION['payable_koujino'];
} else {
    $kouji_no = ' ';
    $_SESSION['payable_koujino'] = $kouji_no;
}
if ( isset($_SESSION['payable_order']) ) {
    $order = $_SESSION['payable_order'];
} else {
    $order = ' ';
    $_SESSION['payable_order'] = $order;
}
if ( isset($_SESSION['paya_vendor']) ) {
    $vendor = $_SESSION['paya_vendor'];
} else {
    $vendor = '';
    $_SESSION['paya_vendor'] = $vendor;
}
if ($session->get_local('paya_ltstrdate') != '') {
    $lt_str_date = $session->get_local('paya_ltstrdate');
    $lt_str_date = $_SESSION['paya_ltstrdate'];
} elseif(isset($_SESSION['paya_ltstrdate'])) {
    $lt_str_date = $_SESSION['paya_ltstrdate'];
} else {
    $lt_str_date = '';  // �����
    $session->add_local('paya_ltstrdate', $lt_str_date);
}
if ($session->get_local('paya_ltenddate') != '') {
    $lt_end_date = $session->get_local('paya_ltenddate');
    $lt_end_date = $_SESSION['paya_ltenddate'];
} elseif(isset($_SESSION['paya_ltenddate'])) {
    $lt_end_date = $_SESSION['paya_ltenddate'];
} else {
    $lt_end_date = '';     // �����
    $session->add_local('paya_ltenddate', $lt_end_date);
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

if ( isset($_SESSION['payable_page']) ) {   // ���ڡ���ɽ���Կ�����
    $paya_page = $_SESSION['payable_page'];
} else {
    $paya_page = 25;             // Default 25
    $_SESSION['payable_page'] = $paya_page;
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
<script language='JavaScript' src='./aden_details_form.js?<?= $uniq ?>'>
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
        
        <form name='payable_form' action='<?=$menu->out_action('A������ɽ��')?>' method='get' onSubmit='return chk_payable_form(this)'>
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
                        ASSY No.�λ���
                    </td>
                    <td class='winbox' align='center'>
                        <input type='text' name='parts_no' class='pt12b' size='9' value='<?= $parts_no ?>' maxlength='9'>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' align='right'>
                        ����(A��������)����ꤷ�Ʋ�����(ɬ��)
                    </td>
                    <td class='winbox' align='center'>
                        <input type='text' name='str_date' size='9' value='<?php echo($str_date); ?>' maxlength='8' style='font-size:11pt; font-weight:bold; font-family:monospace;'>
                        ��
                        <input type='text' name='end_date' size='9' value='<?php echo($end_date); ?>' maxlength='8' style='font-size:11pt; font-weight:bold; font-family:monospace;'>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' align='right'>
                        A���������������򤷤Ʋ�����
                    </td>
                    <td class='winbox' align='center'>
                        <select name='answer' style='font-size:11pt; font-weight:bold; font-family:monospace;'>
                            <option value=' '<?php if($answer==' ') echo 'selected' ?>>���٤�</option>
                            <option value='Y'<?php if($answer=='Y') echo 'selected' ?>>������</option>
                            <option value='N'<?php if($answer=='N') echo 'selected' ?>>̤����</option>
                        </select>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' align='right'>
                        �ײ贰λ���������򤷤Ʋ�����
                    </td>
                    <td class='winbox' align='center'>
                        <select name='finish' style='font-size:11pt; font-weight:bold; font-family:monospace;'>
                            <option value=' '<?php if($finish==' ') echo 'selected' ?>>���٤�</option>
                            <option value='Y'<?php if($finish=='Y') echo 'selected' ?>>��λ��</option>
                            <option value='N'<?php if($finish=='N') echo 'selected' ?>>̤��λ</option>
                        </select>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' align='right'>
                        Ǽ�������Ȥ����򤷤Ʋ�����
                    </td>
                    <td class='winbox' align='center'>
                        <select name='deli_com' style='font-size:11pt; font-weight:bold; font-family:monospace;'>
                            <option value=' '<?php if($deli_com==' ') echo 'selected' ?>>���٤�</option>
                            <option value='Y'<?php if($deli_com=='Y') echo 'selected' ?>>��˾�̤�</option>
                            <option value=1<?php if($deli_com==1) echo 'selected' ?>>�����٤�</option>
                            <option value=2<?php if($deli_com==2) echo 'selected' ?>>�߷��ѹ�</option>
                            <option value=3<?php if($deli_com==3) echo 'selected' ?>>L/T��­</option>
                            <option value=4<?php if($deli_com==4) echo 'selected' ?>>�����٤�</option>
                            <option value=5<?php if($deli_com==5) echo 'selected' ?>>����¾</option>
                            <option value='N'<?php if($deli_com=='N') echo 'selected' ?>>̤����</option>
                        </select>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' align='right'>
                        ���֤����򤷤Ʋ�����
                    </td>
                    <td class='winbox' align='center'>
                        <select name='kouji_no' style='font-size:11pt; font-weight:bold; font-family:monospace;'>
                            <option value=' '<?php if($kouji_no==' ') echo 'selected' ?>>���٤�</option>
                            <option value='S'<?php if($kouji_no=='S') echo 'selected' ?>>SC�Τ�</option>
                            <option value='C'<?php if($kouji_no=='C') echo 'selected' ?>>CQ�Τ�</option>
                            <option value='SCQ'<?php if($kouji_no=='SCQ') echo 'selected' ?>>SC+CQ</option>
                            <option value='N'<?php if($kouji_no=='N') echo 'selected' ?>>���֤ʤ�</option>
                        </select>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' align='right'>
                        Ǽ��L/T������ꤷ�Ƥ�������
                    </td>
                    <td class='winbox' align='center'>
                        <input type='text' name='lt_str_date' size='5' value='<?php echo($lt_str_date); ?>' maxlength='4' style='font-size:11pt; font-weight:bold; font-family:monospace;'>
                        ��
                        <input type='text' name='lt_end_date' size='5' value='<?php echo($lt_end_date); ?>' maxlength='4' style='font-size:11pt; font-weight:bold; font-family:monospace;'>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' align='right'>
                        �����٤�����򤷤Ʋ�����
                    </td>
                    <td class='winbox' align='center'>
                        <select name='finish_del' style='font-size:11pt; font-weight:bold; font-family:monospace;'>
                            <option value=' '<?php if($finish_del==' ') echo 'selected' ?>>���٤�</option>
                            <option value='D'<?php if($finish_del=='D') echo 'selected' ?>>Ǽ���٤�</option>
                            <option value='Y'<?php if($finish_del=='Y') echo 'selected' ?>>Ǽ���̤�</option>
                            <option value='A'<?php if($finish_del=='A') echo 'selected' ?>>Ǽ������</option>
                        </select>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' align='right'>
                        ����������򤷤Ʋ�����
                    </td>
                    <td class='winbox' align='center'>
                        <select name='order' style='font-size:11pt; font-weight:bold; font-family:monospace;'>
                            <option value=' '<?php if($order==' ') echo 'selected' ?>>��������</option>
                            <option value='1'<?php if($order=='1') echo 'selected' ?>>��˾Ǽ����</option>
                            <option value='2'<?php if($order=='2') echo 'selected' ?>>L/T����</option>
                            <option value='3'<?php if($order=='3') echo 'selected' ?>>�����٤��</option>
                        </select>
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
