<?php
//////////////////////////////////////////////////////////////////////////////
// Ǽ��ͽ���ۤξȲ�  Header�ե졼��                                       //
// Copyright (C) 2009-2010   Norihisa.Ohya  norihisa_ooya@nitto-kohki.co.jp //
// Changed history                                                          //
// 2009/11/09 Created  /order/order_schedule_Header.php���/order_money��   //
//            ή��                                                          //
// 2010/05/26 �����ȥ뤬�㤦�Τǽ���                                        //
//////////////////////////////////////////////////////////////////////////////
// ini_set('error_reporting', E_ALL);          // E_ALL='2047' debug ��
// ini_set('display_errors', '1');             // Error ɽ�� ON debug �� ��꡼���女����
ob_start('ob_gzhandler');                   // ���ϥХåե���gzip����
session_start();                            // ini_set()�μ��˻��ꤹ�뤳�� Script �Ǿ��
require_once ('../../function.php');        // TNK ������ function
require_once ('../../MenuHeader.php');      // TNK ������ menu class
access_log();                               // Script Name �ϼ�ư����

///// TNK ���ѥ�˥塼���饹�Υ��󥹥��󥹤����
$menu = new MenuHeader();                   // ǧ�ڥ����å�0=���̰ʾ� �����=���å������ �����ȥ�̤����

////////////// ����������
$menu->set_site(30, 50);                    // site_index=30(������˥塼) site_id=50(Ǽ���������ų�)999(�����Ȥ򳫤�)
////////////// target����
// $menu->set_target('application');           // �ե졼���Ǥ�������target°����ɬ��
$menu->set_target('_parent');               // �ե졼���Ǥ�������target°����ɬ��

///////// �ѥ�᡼���������å�������
if (isset($_REQUEST['div'])) {
    $div = $_REQUEST['div'];                // ������
    $_SESSION['div'] = $_REQUEST['div'];    // ���å�������¸
} else {
    if (isset($_SESSION['div'])) {
        $div = $_SESSION['div'];            // Default(���å���󤫤�)
    } else {
        $div = 'C';                         // �����(���ץ�)���ޤ��̣��̵��
    }
}
if (isset($_REQUEST['miken'])) {
    $select = 'miken';                      // ̤�����ꥹ��
    $_SESSION['select'] = 'miken';          // ���å�������¸
} elseif (isset($_REQUEST['insEnd'])) {
    $select = 'insEnd';                     // �����ѥꥹ��
    $_SESSION['select'] = 'insEnd';         // ���å�������¸
} elseif (isset($_REQUEST['graph'])) {
    $select = 'graph';                      // Ǽ��ͽ�ꥰ���
    $_SESSION['select'] = 'graph';          // ���å�������¸
} elseif (isset($_REQUEST['list'])) {
    $select = 'list';                      // Ǽ��ͽ�꽸��
    $_SESSION['select'] = 'list';          // ���å�������¸
} else {
    if (isset($_SESSION['select'])) {
        $select = $_SESSION['select'];      // Default(���å���󤫤�)
    } else {
        $select = 'graph';                  // �����(Ǽ��ͽ�ꥰ���)���ޤ��̣��̵��
    }
}

/////////// ���̲����٤μ���
if ($_SESSION['site_view'] == 'on') {
    $display = 'normal';
} else {
    $display = 'wide';
}

//////////// �����ȥ�̾(�������Υ����ȥ�̾�ȥե�����Υ����ȥ�̾)
if ($select == 'graph') {
    $menu->set_title('Ǽ��ͽ���� ���ץ����');
} elseif ($select == 'list') {
    $menu->set_title('Ǽ��ͽ���� ����ɼ');
} else {
    $menu->set_title('�����ų� ����ɽ �Ȳ�');
}
//////////// ɽ�������
$menu->set_caption('�Ȳ���������');

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
<?php echo $menu->out_css() ?>
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
    top:    90px;
    left:   20px;
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
<script language="JavaScript">
<!--
/* ������ϥե�����Υ�����Ȥ˥ե������������� */
function set_focus() {
    document.form_parts.parts_no.focus();
    document.form_parts.parts_no.select();
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
<body <?php if ($select=='miken' || $select=='insEnd') echo "onLoad='set_focus()'";?>>
    <center>
<?php if($_SESSION['User_ID'] != '00000A') { if ($select == 'graph') echo $menu->out_title_border(); else echo $menu->out_title_border(1); } else echo $menu->out_title_only_border(); ?>
        
        <!----------------- ���Ф���ɽ�� ------------------------>
        <table bgcolor='#d6d3ce'  border='1' cellspacing='0' cellpadding='1'>
            <tr><td> <!-- ���ߡ�(�ǥ�������) -->
        <table class='winbox_field' width='100%' border='0' cellspacing='0' cellpadding='1'>
            <tr class='sub_font'>
                <!--
                <td class='winbox'>
                    <input style='font-size:10pt; font-weight:bold; color:blue;' type='submit' name='order_help' value='����' onClick='win_open("order_help.php")'>
                </td>
                -->
                <td class='winbox'>
                    <form action='<?php echo $menu->out_parent() ?>' method='get' target='_parent'>
                        <input style='font-size:10pt; color:black; width:60px;' type='submit' name='reload' value='���̹���'>
                    </form>
                </td>
                <td class='winbox' align='center' width='100'>
                    <form name='div_form' method='get' action='<?php echo $menu->out_parent() ?>' target='_parent'>
                        <select name='div' class='ret_font' onChange='document.div_form.submit()'>
                            <option value='C' <?php if($div=='C') echo 'selected'; ?>>���ץ�</option>
                            <option value='SC' <?php if($div=='SC') echo 'selected'; ?>>������</option>
                            <option value='CS' <?php if($div=='CS') echo 'selected'; ?>>��ɸ��</option>
                            <option value='L' <?php if($div=='L') echo 'selected'; ?>>��˥�</option>
                            <option value='T' <?php if($div=='T') echo 'selected'; ?>>�ġ���</option>
                            <option value='F' <?php if($div=='F') echo 'selected'; ?>>�ƣ�</option>
                            <option value='A' <?php if($div=='A') echo 'selected'; ?>>����</option>
                            <option value='N' <?php if($div=='N') echo 'selected'; ?>>�Σ�</option>
                            <option value='NKB' <?php if($div=='NKB') echo 'selected'; ?>>�Σˣ�</option>
                        </select>
                        <?php if ($select == 'miken') { ?>
                        <input type='hidden' name='miken' value='GO'>
                        <?php } elseif ($select == 'insEnd') { ?>
                        <input type='hidden' name='insEnd' value='GO'>
                        <?php } elseif ($select == 'graph') { ?>
                        <input type='hidden' name='graph' value='GO'>
                        <?php } elseif ($select == 'graph') { ?>
                        <input type='hidden' name='list' value='GO'>
                        <?php } ?>
                    </form>
                </td>
                <td class='winbox'>
                    <form action='<?php echo $menu->out_parent() ?>' method='get' target='_parent'>
                        <?php if ($select == 'graph') { ?>
                        <input style='font-size:11pt; font-weight:bold; color:blue; width:160px;' type='submit' name='graph' value='Ǽ��ͽ�ꥰ���(����)'>
                        <?php } else { ?>
                        <input style='font-size:11pt; font-weight:bold; color:black; width:160px;' type='submit' name='graph' value='Ǽ��ͽ�ꥰ���(����)'>
                        <?php } ?>
                        <input type='hidden' name='div' value='<?php echo $div?>'>
                    </form>
                </td>
                <td class='winbox'>
                    <form action='<?php echo $menu->out_parent() ?>' method='get' target='_parent'>
                        <?php if ($select == 'list') { ?>
                        <input style='font-size:11pt; font-weight:bold; color:blue; width:160px;' type='submit' name='list' value='Ǽ��ͽ�꽸��'>
                        <?php } else { ?>
                        <input style='font-size:11pt; font-weight:bold; color:black; width:160px;' type='submit' name='list' value='Ǽ��ͽ�꽸��'>
                        <?php } ?>
                        <input type='hidden' name='div' value='<?php echo $div?>'>
                    </form>
                </td>
                <!--
                <td class='winbox'>
                    <form action='<?php echo $menu->out_parent() ?>' method='get' target='_parent'>
                        <?php if ($select == 'miken') { ?>
                        <input style='font-size:11pt; font-weight:bold; color:blue; width:115px;' type='submit' name='miken' value='�����ųݥꥹ��'>
                        <?php } else { ?>
                        <input style='font-size:11pt; font-weight:bold; color:black; width:115px;' type='submit' name='miken' value='�����ųݥꥹ��'>
                        <?php } ?>
                        <input type='hidden' name='div' value='<?php echo $div?>'>
                    </form>
                </td>
                <?php if ($select == 'miken') { ?>
                <td class='winbox'>
                    <form action='order_schedule_List.php' method='get' target='List' name='form_parts' onSubmit='return parts_upper(this)'>
                        <input type='text' name='parts_no' onKeyUp='baseJS.keyInUpper(this);' size='9' maxlength='9' value='' style='text-align:left; font-size:12pt; font-weight:bold;'>
                    </form>
                </td>
                <?php } ?>
                <td class='winbox'>
                    <form action='<?php echo $menu->out_parent() ?>' method='get' target='_parent'>
                        <?php if ($select == 'insEnd') { ?>
                        <input style='font-size:11pt; font-weight:bold; color:blue; width:105px;' type='submit' name='insEnd' value='�����ѥꥹ��'>
                        <?php } else { ?>
                        <input style='font-size:11pt; font-weight:bold; color:black; width:105px;' type='submit' name='insEnd' value='�����ѥꥹ��'>
                        <?php } ?>
                        <input type='hidden' name='div' value='<?php echo $div?>'>
                    </form>
                </td>
                <?php if ($select == 'insEnd') { ?>
                <td class='winbox'>
                    <form action='order_schedule_List.php' method='get' target='List' name='form_parts' onSubmit='return parts_upper(this)'>
                        <input type='text' name='parts_no' onKeyUp='baseJS.keyInUpper(this);' size='9' maxlength='9' value='' style='text-align:left; font-size:12pt; font-weight:bold;'>
                    </form>
                </td>
                <?php } ?>
                <td class='winbox'>
                    <form action='inspection_recourse.php' method='get' target='_parent'>
                        <?php if ($select == 'inspc') { ?>
                        <input style='font-size:11pt; font-weight:bold; color:blue; width:115px;' type='submit' name='inspc' value='��������ꥹ��'>
                        <?php } else { ?>
                        <input style='font-size:11pt; font-weight:bold; color:black; width:115px;' type='submit' name='inspc' value='��������ꥹ��'>
                        <?php } ?>
                        <input type='hidden' name='div' value='<?php echo $div?>'>
                    </form>
                </td>
                <td class='winbox'>
                    <form action='inspectingList.php' method='get' target='_parent'>
                        <?php if ($select == 'inspecting') { ?>
                        <input style='font-size:11pt; font-weight:bold; color:blue; width:105px;' type='submit' name='inspc' value='������ꥹ��'>
                        <?php } else { ?>
                        <input style='font-size:11pt; font-weight:bold; color:black; width:105px;' type='submit' name='inspc' value='������ꥹ��'>
                        <?php } ?>
                        <input type='hidden' name='div' value='<?php echo $div?>'>
                    </form>
                </td>
                -->
            </tr>
        </table>
            </td></tr>
        </table> <!-- ���ߡ�End -->
        
        <!-- <hr color='797979'> -->
        
        <?php if ($select == 'miken' || $select == 'insEnd') { ?>
        <table class='item' bgcolor='#d6d3ce'  border='1' cellspacing='0' cellpadding='2'>
           <tr><td> <!-- ���ߡ�(�ǥ�������) -->
        <table class='winbox_field' width=100% align='center'  border='1' cellspacing='0' cellpadding='1'>
            <th class='winbox' width='30' nowrap>No</th>
            <th class='winbox' width='98' nowrap colspan='2' style='font-size:10pt;'>�������Ͻ�λ</th>
            <th class='winbox' width='70' nowrap>������</th>
            <th class='winbox' width='55' nowrap style='font-size:9.5pt;'>����No</th>
            <th class='winbox' width='90' nowrap>�����ֹ�</th>
            <th class='winbox' width='150' nowrap>����̾</th>
            <th class='winbox' width='90' nowrap style='font-size:10pt;'>���/�Ƶ���</th>
            <th class='winbox' width='70' nowrap>���տ�</th>
            <th class='winbox' width='35' nowrap style='font-size:9.5pt;'>����</th>
            <th class='winbox' width='130' nowrap>Ǽ����</th>
            <?php if ($display == 'wide') { ?>
            <th class='winbox' width='80' nowrap>�����ֹ�</th>
            <th class='winbox' width='80' nowrap>ȯ��Ϣ��</th>
            <th class='winbox' width='70' nowrap>��¤�ֹ�</th>
            <th class='winbox' width='130' nowrap>������</th>
            <?php } ?>
        </table> <!----- ���ߡ� End ----->
            </td></tr>
        </table>
        <?php } ?>
    </center>
</body>
</html>
<?php echo $menu->out_alert_java()?>
<?php ob_end_flush(); // ���ϥХåե���gzip���� END ?>
