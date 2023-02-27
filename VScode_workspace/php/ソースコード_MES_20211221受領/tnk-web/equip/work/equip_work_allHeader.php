<?php
//////////////////////////////////////////////////////////////////////////////
// ������ư���������ƥ�� ���߱�ž�����ɽ ɽ��  Header�ե졼��             //
// Copyright (C) 2004-2005 2007 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp //
// Changed history                                                          //
// 2004/09/09 Created  equip_work_allHeader.php                             //
// 2004/09/23 ��ɽ���� method �� post����get���ѹ� JavaScript��reload�б�   //
//            �쥤������(�ޥå�)ɽ�������ؤ���ܥ�����ɲ�                  //
// 2004/11/29 ����̾��width=70��72���ѹ�(20PM�������Τ���)                     //
// 2005/02/16 �ꥯ�����Ȥ򥻥å�������¸ $_SESSION['factory'] = $factory  //
// 2005/06/24 F2/F12��������뤿����б��� JavaScript�� set_focus()���ɲ�   //
// 2005/07/11 �����ʬ�򤳤Υ�˥塼����ϥ��å�������Ͽ���ʤ��褦���ѹ�  //
// 2005/07/25 �嵭�򸵤��ᤷ                                                //
// 2005/08/05 allList���ȹ�碌�뤿��ɽ������width='98.3%'��Ĵ��            //
// 2005/08/20 $menu->_parent �� $menu->out_parent() ���ѹ�                  //
// 2007/05/24 �ե졼���Ǥ��饤��饤��ե졼���Ǥ��ѹ��������⢪�ؼ������ѹ�//
//              ����¾�ǥ������ѹ� ���Ǥ� backup/ �ˤ���                    //
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
$menu->set_site(40, 9);                     // site_index=40(������˥塼) site_id=9(��ž�����)
////////////// target����
// $menu->set_target('application');           // �ե졼���Ǥ�������target°����ɬ��
$menu->set_target('_parent');               // �ե졼���Ǥ�������target°����ɬ��

//////////// �����ȥ�̾(�������Υ����ȥ�̾�ȥե�����Υ����ȥ�̾)
$menu->set_title('���߱�ž�� ����ɽ');
//////////// ɽ�������
$menu->set_caption('��������');

if (isset($_REQUEST['factory'])) {
    $factory = $_REQUEST['factory'];
    $_SESSION['factory'] = $factory;
} else {
    ///// �ꥯ�����Ȥ�̵����Х��å���󤫤鹩���ʬ��������롣(�̾�Ϥ��Υѥ�����)
    $factory = @$_SESSION['factory'];
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
<?= $menu->out_css() ?>
<?= $menu->out_site_java() ?>
<style type='text/css'>
<!--
.pt8 {
    font-size:   0.6em;
    font-weight: normal;
    font-family: monospace;
}
.pt9 {
    font-size:   0.7em;
    font-weight: normal;
    font-family: monospace;
}
.pt10 {
    font-size:   0.8em;
    font-weight: normal;
    font-family: monospace;
}
.pt10b {
    font-size:   0.8em;
    font-weight: bold;
    font-family: monospace;
}
.pt11b {
    font-size:   0.9em;
    font-weight: bold;
    font-family: monospace;
}
.pt12b {
    font-size:   1.0em;
    font-weight: bold;
    font-family: monospace;
}
.pt13b {
    font-size:   1.1em;
    font-weight: bold;
    /* font-family: monospace; */
}
.pt14b {
    font-size:   1.2em;
    font-weight: bold;
    /* font-family: monospace; */
}
select {
    background-color:   teal;
    color:              white;
}
.sub_font {
    font-size:      0.95em;
    font-weight:    bold;
    font-family:    monospace;
}
.pick_font {
    font-size:      0.75em;
    font-weight:    bold;
    font-family: monospace;
}
th {
    font-size:      1.0em;
    font-weight:    bold;
    font-family:    monospace;
    color:              blue;
    background-color:   yellow;
}
.item {
    position: absolute;
    top:    90px;
    left:    0px;
}
-->
</style>
<form name='MainForm' method='post'>
    <input type='hidden' name='select' value=''>
</form>
<script language="JavaScript">
<!--
/* ������ϥե�����Υ�����Ȥ˥ե������������� */
function set_focus() {
    // document.mac_form.factory.focus();      // �������륭���ǹ�����ư����褦�ˤ���
}
    function parts_upper(obj) {
    obj.parts_no.value = obj.parts_no.value.toUpperCase();
    return true;
}
function win_open(url) {
    var w = 800;
    var h = 600;
    var left = (screen.availWidth  - w) / 2;
    var top  = (screen.availHeight - h) / 2;
    window.open(url, 'view_win', 'width='+w+',height='+h+',scrollbars=yes,status=no,toolbar=no,location=no,menubar=no,top='+top+',left='+left);
}
// -->
</script>
</head>
<body onLoad='set_focus()'>
    <center>
        <table width='100%' bgcolor='#d6d3ce'  border='1' cellspacing='0' cellpadding='1'>
           <tr><td> <!-- ���ߡ�(�ǥ�������) -->
        <table class='winbox_field' width='100%' align='center'  border='1' cellspacing='0' cellpadding='1'>
            <th class='winbox' nowrap width=' 3%'>No</th>
            <th class='winbox' nowrap width=' 9%'>����̾</th>
            <th class='winbox' nowrap width=' 9%'>ǯ����</th>
            <th class='winbox' nowrap width=' 8%'>��ʬ��</th>
            <th class='winbox' nowrap width=' 9%'>����</th>
            <th class='winbox' nowrap width=' 8%'>�ù���</th>
            <th class='winbox' nowrap width=' 8%'>�ؼ���</th>
            <th class='winbox' nowrap width=' 7%'>�ؼ�No</th>
            <th class='winbox' nowrap width='11%'>�����ֹ�</th>
            <th class='winbox' nowrap width='13%'>����̾</th>
            <th class='winbox' nowrap width='15%'>��������</th>
        </table> <!----- ���ߡ� End ----->
            </td></tr>
        </table>
    </center>
</body>
</html>
<?php ob_end_flush(); // ���ϥХåե���gzip���� END ?>
