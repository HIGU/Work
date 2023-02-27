<?php
//////////////////////////////////////////////////////////////////////////////
// Ǽ��ͽ��ξȲ�(�����λŻ����İ�) ���٤򥦥���ɥ�ɽ��   Header�ե졼��   //
// Copyright (C) 2004 2007 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp      //
// Changed history                                                          //
// 2004/09/30 Created  order_details_Header.php                             //
// 2007/05/11 �ǥ��쥯�ȥ�� order/ �� order/order_details/ ���ѹ�          //
//////////////////////////////////////////////////////////////////////////////
ini_set('error_reporting', E_ALL);          // E_ALL='2047' debug ��
// ini_set('display_errors', '1');             // Error ɽ�� ON debug �� ��꡼���女����
ob_start('ob_gzhandler');                   // ���ϥХåե���gzip����
session_start();                            // ini_set()�μ��˻��ꤹ�뤳�� Script �Ǿ��
require_once ('../../../function.php');     // define.php �� pgsql.php �� require_once ���Ƥ���
require_once ('../../../MenuHeader.php');   // TNK ������ menu class
access_log();                               // Script Name �ϼ�ư����

///// TNK ���ѥ�˥塼���饹�Υ��󥹥��󥹤����
$menu = new MenuHeader();                   // ǧ�ڥ����å�0=���̰ʾ� �����=���å������ �����ȥ�̤����

////////////// ����������
// $menu->set_site(30, 999);                   // site_index=30(������˥塼) site_id=999(̤��)
////////////// target����
// $menu->set_target('application');           // �ե졼���Ǥ�������target°����ɬ��
$menu->set_target('_parent');               // �ե졼���Ǥ�������target°����ɬ��

//////////// �����ȥ�̾(�������Υ����ȥ�̾�ȥե�����Υ����ȥ�̾)
$menu->set_title('Ǽ��ͽ�����٤ξȲ�');     // �����ȥ������ʤ���IE�ΰ����ΥС�������ɽ���Ǥ��ʤ��Զ�礢��
//////////// ɽ�������
// $menu->set_caption('�Ȳ���������');

///////// �ѥ�᡼���������å�(����Ū�˥��å���󤫤����)
if (isset($_SESSION['div'])) {
    $div = $_SESSION['div'];                // Default(���å���󤫤�)
} else {
    $div = 'C';                             // �����(���ץ�)���ޤ��̣��̵��
}
//////// �������Υѥ�᡼������ & ����
if (isset($_REQUEST['date'])) {
    if ($_REQUEST['date'] == 'OLD') {
        $date = $_REQUEST['date'];
    } else {
        $date = $_REQUEST['date'];              // ���٤�ɽ�������������
        $date = ('20' . substr($date, 0, 2) . substr($date, 3, 2) . substr($date, 6, 2));
            // YYYYMMDD�η������Ѵ�
    }
} else {
    $date = date('Ymd');                    // �����(����)���ޤ��̣��̵��
}

/////////// HTML Header ����Ϥ��ƥ���å��������
$menu->out_html_header();
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=EUC-JP">
<meta http-equiv="Content-Style-Type" content="text/css">
<title><?php echo $menu->out_title() ?></title>
<?php echo $menu->out_css() ?>
<script language='JavaScript'>
<!--
function win_close() {
    alert('�����ϡ����Ф��ι��ܤǤ���');
    window.close();
}
// window.document.onclick = win_close;
// -->
</script>
<style type='text/css'>
<!--
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
<body onClick='window.parent.close()'>
    <center>
        <!----------------- ���Ф���ɽ�� ------------------------>
        <table class='item' bgcolor='#d6d3ce'  border='1' cellspacing='0' cellpadding='2'>
           <tr><td> <!-- ���ߡ�(�ǥ�������) -->
        <table class='winbox_field' width=100% align='center'  border='1' cellspacing='0' cellpadding='1'>
            <th class='winbox' nowrap width='30'>No</th>
            <?php if ($date == 'OLD') { ?>
            <th class='winbox' nowrap width='45' style='font-size:9.5pt;'>Ǽ ��</th>
            <?php } else { ?>
            <th class='winbox' nowrap width='45' style='font-size:9.5pt;'>ȯ����</th>
            <?php } ?>
            <th class='winbox' nowrap width='60' style='font-size:9.5pt;'>��¤�ֹ�</th>
            <th class='winbox' nowrap width='80'>�����ֹ�</th>
            <th class='winbox' nowrap width='145'>����̾</th>
            <th class='winbox' nowrap width='85'>��&nbsp;&nbsp;��</th>
            <th class='winbox' nowrap width='90'>�Ƶ���</th>
            <th class='winbox' nowrap width='70'>��ʸ��</th>
            <th class='winbox' nowrap width='20' style='font-size:10.5pt;'>����</th>
            <th class='winbox' nowrap width='130'>ȯ����̾</th>
        </table> <!----- ���ߡ� End ----->
            </td></tr>
        </table>
    </center>
</body>
</html>
<?php echo $menu->out_alert_java()?>
<?php ob_end_flush(); // ���ϥХåե���gzip���� END ?>
