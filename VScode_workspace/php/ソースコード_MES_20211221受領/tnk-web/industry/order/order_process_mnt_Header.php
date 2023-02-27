<?php
//////////////////////////////////////////////////////////////////////////////
// ȯ�������ƥʥ�(ȯ������ݼ�)   Header�ե졼��                    //
// Copyright (C) 2004-2005 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp      //
// Changed history                                                          //
// 2004/11/27 Created  order_process_mnt_Header.php                         //
// 2004/12/01 �ǥ��������� border='1' cellspacing='0' cellpadding='3'>      //
// 2005/02/10 JavaScript�� 'sei_no'��Ŭ�������å����ɲ�                     //
//////////////////////////////////////////////////////////////////////////////
ini_set('error_reporting', E_ALL);          // E_ALL='2047' debug ��
// ini_set('display_errors', '1');             // Error ɽ�� ON debug �� ��꡼���女����
ob_start('ob_gzhandler');                   // ���ϥХåե���gzip����
session_start();                            // ini_set()�μ��˻��ꤹ�뤳�� Script �Ǿ��
require_once ('../../function.php');        // TNK ������ function
require_once ('../../MenuHeader.php');      // TNK ������ menu class
access_log();                               // Script Name �ϼ�ư����

///// TNK ���ѥ�˥塼���饹�Υ��󥹥��󥹤����
$menu = new MenuHeader();                   // ǧ�ڥ����å�0=���̰ʾ� �����=���å������ �����ȥ�̤����

////////////// ����������
// $menu->set_site(30, 999);                   // site_index=30(������˥塼) site_id=999(̤��)
////////////// target����
// $menu->set_target('application');           // �ե졼���Ǥ�������target°����ɬ��
$menu->set_target('_parent');               // �ե졼���Ǥ�������target°����ɬ��

//////////// �����ȥ�̾(�������Υ����ȥ�̾�ȥե�����Υ����ȥ�̾)
$menu->set_title('ȯ�������ƥʥ�');     // �����ȥ������ʤ���IE�ΰ����ΥС�������ɽ���Ǥ��ʤ��Զ�礢��
//////////// ɽ�������
$menu->set_caption('��¤�ֹ����ꤷ�Ʋ�������');
//////////// �ƽ����action̾�ȥ��ɥ쥹����
$menu->set_action('�ꥹ��',   INDUST . 'order/order_process_mnt_List.php');

//////////// �ѥ�᡼���μ���
if ($_SESSION['order_sei_no'] != '') {
    $sei_no = $_SESSION['order_sei_no'];
} else {
    $sei_no = '';
}
$uniq = ('id=' . uniqid('target') );

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
<script language='JavaScript'>
<!--
/* ����ʸ�����������ɤ��������å�(ASCII code check) */
function isDigit(str) {
    var len = str.length;
    var c;
    for (i=0; i<len; i++) {
        c = str.charAt(i);
        if ((c < '0') || (c > '9')) {
            return false;
        }
    }
    return true;
}

function set_focus() {
    document.sei_no_form.sei_no.select();
    document.sei_no_form.sei_no.focus();
}
function chk_sei_no(obj) {
    var sei_no = obj.sei_no.value;
    if (sei_no.length != 7) {
        alert('��¤�ֹ�η���ϣ���Ǥ���\n\n���Ϥ��줿����� [' + sei_no.length + '] ��Ǥ���');
        obj.sei_no.focus();
        obj.sei_no.select();
        return false;
    }
    if (!isDigit(sei_no)) {
        alert('��¤�ֹ�˿����ʳ������Ϥ���ޤ�����\n\n���Ϥ��줿�Τ� [' + sei_no + '] �Ǥ���');
        obj.sei_no.focus();
        obj.sei_no.select();
        return false;
    }
    return true;
}
// -->
</script>
<style type='text/css'>
<!--
.pt14b {
    font-size:      14pt;
    font-weight:    bold;
    font-family:    monospace;
}
select {
    background-color:   teal;
    color:              white;
}
.sub_font {
    font-size:      11.5pt;
    font-weight:    bold;
    font-family:    monospace;
}
.pick_font {
    font-size:      9.5pt;
    font-weight:    bold;
    font-family: monospace;
}
th {
    font-size:      11.5pt;
    font-weight:    bold;
    font-family:    monospace;
    color:              blue;
    background-color:   yellow;
}
.item {
    position: absolute;
    top:    0px;
    left:   0px;
}
.winbox {
    border-style: solid;
    border-width: 1px;
    border-top-color:       #FFFFFF;
    border-left-color:      #FFFFFF;
    border-right-color:     #999999;
    border-bottom-color:    #999999;
    /* background-color:#d6d3ce; */
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
-->
</style>
<form name='MainForm' method='post'>
    <input type='hidden' name='select' value=''>
</form>
</head>
<body onLoad='set_focus()' style='orverflow-y:hidden;'>
    <center>
<?= $menu->out_title_border() ?>
        <form name='sei_no_form' action='<?= $menu->out_action('�ꥹ��'), '?', $uniq ?>' method='get' target='List' onSubmit='return chk_sei_no(this)'>
            <!----------------- ������ ��ʸ��ɽ������ ------------------->
            <table bgcolor='#d6d3ce'  border='1' cellspacing='0' cellpadding='3'>
               <tr><td> <!-- ���ߡ�(�ǥ�������) -->
            <table class='winbox_field' width=100% align='center' border='1' cellspacing='0' cellpadding='3'>
                <tr>
                        <!--  bgcolor='#ffffc6' �������� --> 
                    <td class='winbox' colspan='2' align='center'>
                        <font class='caption_font'><?= $menu->out_caption(), "\n" ?></font>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' align='right'>
                        ��¤�ֹ�λ���
                    </td>
                    <td class='winbox' align='center'>
                        <input type='text' name='sei_no' class='pt14b' size='7' value='<?= $sei_no ?>' maxlength='7'>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' colspan='2' align='center'>
                        <!-- <input type='submit' name='sei_no_view' value='�¹�' > -->
                        Enter Key �Ǽ¹Ԥ��ޤ���
                    </td>
                </tr>
            </table>
                </td></tr>
            </table> <!----------------- ���ߡ�End ------------------>
        </form>
    </center>
</body>
</html>
<?= $menu->out_alert_java()?>
<?php ob_end_flush(); // ���ϥХåե���gzip���� END ?>
