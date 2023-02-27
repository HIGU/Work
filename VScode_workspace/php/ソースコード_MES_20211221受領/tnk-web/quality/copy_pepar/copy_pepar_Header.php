<?php
//////////////////////////////////////////////////////////////////////////////
// Ǽ��ͽ�ꥰ��ա������ų����٤ξȲ�(�����λŻ����İ�)  Header�ե졼��     //
// Copyright (C) 2004-2017 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp      //
// Changed history                                                          //
// 2021/07/07 Created  order_schedule_Header.php -> copy_pepar_Header.php   //
//////////////////////////////////////////////////////////////////////////////
// ini_set('error_reporting', E_ALL);          // E_ALL='2047' debug ��
// ini_set('display_errors', '1');             // Error ɽ�� ON debug �� ��꡼���女����
ob_start('ob_gzhandler');                   // ���ϥХåե���gzip����
session_start();                            // ini_set()�μ��˻��ꤹ�뤳�� Script �Ǿ��
require_once ('../../function.php');        // TNK ������ function
require_once ('../../MenuHeader.php');      // TNK ������ menu class
require_once ('copy_pepar_function.php');   // copy_pepar �ط��ζ��� function
access_log();                               // Script Name �ϼ�ư����

///// TNK ���ѥ�˥塼���饹�Υ��󥹥��󥹤����
$menu = new MenuHeader();                   // ǧ�ڥ����å�0=���̰ʾ� �����=���å������ �����ȥ�̤����

////////////// ����������
$menu->set_site(70, 72);                   // site_index=70(�ʼ����Ķ���˥塼) site_id=72(�����̥��ԡ��ѻ������)
////////////// target����
// $menu->set_target('application');           // �ե졼���Ǥ�������target°����ɬ��
$menu->set_target('_parent');               // �ե졼���Ǥ�������target°����ɬ��

///////// �ѥ�᡼���������å�������
if (isset($_REQUEST['tnk_ki'])) {
    $div = $_REQUEST['tnk_ki'];                // ������
    $_SESSION['tnk_ki'] = $_REQUEST['tnk_ki'];    // ���å�������¸
} else {
    if (isset($_SESSION['tnk_ki'])) {
        $div = $_SESSION['tnk_ki'];            // Default(���å���󤫤�)
    } else {
        $div = getTnkKi();                         // �����(���ץ�)���ޤ��̣��̵��
    }
}
if (isset($_REQUEST['input_mode'])) {
    $select = 'input_mode';                      // ̤�����ꥹ��
    $_SESSION['select'] = 'input_mode';          // ���å�������¸
} elseif (isset($_REQUEST['graph'])) {
    $select = 'graph';                      // Ǽ��ͽ�ꥰ���
    $_SESSION['select'] = 'graph';          // ���å�������¸
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
    $menu->set_title('�����̥��ԡ��ѻ��������ӥ����');
} else {
    $menu->set_title('�����̥��ԡ��ѻ������');
}
//////////// ɽ�������
$menu->set_caption('�Ȳ���������');

// ���ߤδ������
$ki = getTnkKi();

// �����Ѥ�ä��ݡ��쥳���ɤ����Ĥ�ʤ��ȡ����Υꥹ�Ȥ�ȿ�Ǥ���ʤ��Τǡ�1�Ԥϼ�ưŪ�˺������롣
if( !isTnkKi($ki) ) {
    insertRecord($ki, 0);
}

// ��Ͽ����Ƥ���ơ��֥�δ����������
$ki_rows = getTableKi($ki_tbl); //�ɲø塢�����ɤ߹���

// ��������
$column_row = getColumn($column);

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
<body <?php if( $select=='input_mode' ) echo "onLoad='set_focus()'";?>>
    <center>
<?php 
    if($_SESSION['User_ID'] != '00000A') {
        if ($select == 'graph') {
            echo $menu->out_title_border();
        } else {
            echo $menu->out_title_border(1);
        } 
    } else {
        echo $menu->out_title_only_border();
    }
?>
        
        <!----------------- ���Ф���ɽ�� ------------------------>
        <table bgcolor='#d6d3ce'  border='1' cellspacing='0' cellpadding='1'>
            <tr><td> <!-- ���ߡ�(�ǥ�������) -->
        <table class='winbox_field' width='100%' border='0' cellspacing='0' cellpadding='1'>
            <tr class='sub_font'>
                <td class='winbox' align='center' width='100'> <!-- [��]����ɥ�åץꥹ��-->
                    <form name='div_form' method='get' action='<?php echo $menu->out_parent() ?>' target='_parent'>
                        <select name='tnk_ki' class='ret_font' onChange='document.div_form.submit()'>
                        <?php
                        for( $k=0; $k<$ki_rows; $k++){
                            if( $div == $ki_tbl[$k][0] ) {
                                echo "<option value='{$ki_tbl[$k][0]}' selected>{$ki_tbl[$k][0]}��</option>";
                            } else {
                                echo "<option value='{$ki_tbl[$k][0]}'>{$ki_tbl[$k][0]}��</option>";
                            }
                        }
                        ?>
                        </select>
                        <?php if ($select == 'input_mode') { ?>
                        <input type='hidden' name='input_mode' value='GO'>
                        <?php } elseif ($select == 'graph') { ?>
                        <input type='hidden' name='graph' value='GO'>
                        <?php } ?>
                    </form>
                </td>
                <td class='winbox'> <!-- [�����]�ܥ���-->
                    <form action='<?php echo $menu->out_parent() ?>' method='get' target='_parent'>
                        <?php if ($select == 'graph') { ?>
                        <input style='font-size:11pt; font-weight:bold; color:blue; width:115px;' type='submit' name='graph' value='�����'>
                        <?php } else { ?>
                        <input style='font-size:11pt; font-weight:bold; color:black; width:115px;' type='submit' name='graph' value='�����'>
                        <?php } ?>
                        <input type='hidden' name='tnk_ki' value='<?php echo $div?>'>
                    </form>
                </td>
                <?php
                if (getCheckAuthority(69)) { // 69����̳�ݰ��μҰ��ֹ�ʴ���������̳�ݡ�
                ?>
                <td class='winbox'> <!-- [����]�ܥ���-->
                    <form action='<?php echo $menu->out_parent() ?>' method='get' target='_parent'>
                        <?php if ($select == 'input_mode') { ?>
                        <input style='font-size:11pt; font-weight:bold; color:blue; width:115px;' type='submit' name='input_mode' value='����'>
                        <?php } else { ?>
                        <input style='font-size:11pt; font-weight:bold; color:black; width:115px;' type='submit' name='input_mode' value='����'>
                        <?php } ?>
                        <input type='hidden' name='tnk_ki' value='<?php echo $div?>'>
                    </form>
                </td>
                <?php
                }
                ?>
            </tr>
        </table>
            </td></tr>
        </table> <!-- ���ߡ�End -->
        <!-- <hr color='797979'> -->
        
        <?php if ($select == 'input_mode') { ?>
        <table class='item' bgcolor='#d6d3ce'  border='1' cellspacing='0' cellpadding='2'>
           <tr><td> <!-- ���ߡ�(�ǥ�������) -->
        <table class='winbox_field' width=100% align='center'  border='1' cellspacing='0' cellpadding='1'>
            <?php
            echo "<th class='winbox' width='88' nowrap>{$column[0][0]}</th>";   // ����
            for( $c=1; $c<$column_row; $c++ ) {
                echo "<th class='winbox' width='61' nowrap>{$column[$c][0]}</th>";  // �Ʒ�
            }
            ?>
        </table> <!----- ���ߡ� End ----->
            </td></tr>
        </table>
        <?php } ?>
    </center>
</body>
</html>
<?php echo $menu->out_alert_java()?>
<?php ob_end_flush(); // ���ϥХåե���gzip���� END ?>
