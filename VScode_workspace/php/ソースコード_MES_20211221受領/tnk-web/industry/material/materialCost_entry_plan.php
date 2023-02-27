<?php
//////////////////////////////////////////////////////////////////////////////
// ����������Ͽ  �ײ��ֹ�����ϡ���ǧ form                                //
// Copyright (C) 2003-2007 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp      //
// Changed history                                                          //
// 2003/12/15 Created   metarialCost_entry_plan.php                         //
// 2004/05/12 �����ȥ�˥塼ɽ������ɽ�� �ܥ����ɲ� menu_OnOff($script)�ɲ� //
// 2005/02/08 MenuHeader class ����Ѥ��ƶ��̥�˥塼���ڤ�ǧ���������ѹ�   //
// 2005/03/17 alert_java()����ѤΤ��� entry_form.plan.select()������       //
// 2007/06/12 �ץ�����ѹ��ˤ��ƽ����ѹ�materialCost_entry_main.php ��ë//
// 2007/09/28 ����Ƿײ��ֹ�'Z'�Τ�Τ�miitem������å����ʤ����å����ɲ� //
//////////////////////////////////////////////////////////////////////////////
// ini_set('error_reporting', E_ALL);          // E_ALL='2047' debug ��
// ini_set('display_errors', '1');             // Error ɽ�� ON debug �� ��꡼���女����
// ini_set('implicit_flush', 'off');           // echo print �� flush �����ʤ�(�٤��ʤ뤿��) CLI CGI��
// ini_set('max_execution_time', 1200);        // ����¹Ի���=20ʬ CLI CGI��
ob_start('ob_gzhandler');                   // ���ϥХåե���gzip����
session_start();                            // ini_set()�μ��˻��ꤹ�뤳�� Script �Ǿ��

require_once ('../../function.php');        // define.php �� pgsql.php �� require_once ���Ƥ���
require_once ('../../tnk_func.php');        // TNK �˰�¸������ʬ�δؿ��� require_once ���Ƥ���
require_once ('../../MenuHeader.php');      // TNK ������ menu class
access_log();                               // Script Name �ϼ�ư����

///// TNK ���ѥ�˥塼���饹�Υ��󥹥��󥹤����
$menu = new MenuHeader(0);                  // ǧ�ڥ����å�0=���̰ʾ� �����=TOP_MENU �����ȥ�̤����

////////////// ����������
$menu->set_site(30, 21);                    // site_index=30(������˥塼) site_id=21(����������Ͽ �ײ��ֹ�)
////////////// �꥿���󥢥ɥ쥹����
// $menu->set_RetUrl(INDUST_MENU);             // �̾�ϻ��ꤹ��ɬ�פϤʤ�
//////////// �����ȥ�̾(�������Υ����ȥ�̾�ȥե�����Υ����ȥ�̾)
$menu->set_title('�� �� �� �� �� �� Ͽ (�ײ��ֹ�)');
//////////// ɽ�������
$menu->set_caption('�ײ��ֹ������');
//////////// �ƽ����action̾�ȥ��ɥ쥹����
$menu->set_action('���������Ͽ',   INDUST . 'material/material_entry/materialCost_entry_main.php');

//////////// JavaScript Stylesheet File ��ɬ���ɤ߹��ޤ���
$uniq = uniqid('target');

////////////// ��ʬ�Υݥ��ȥǡ���������å�
if (isset($_POST['plan']) && substr($_POST['plan'], 0, 1) == 'Z' ) {
    $plan = $_POST['plan'];
    $query = "
        SELECT assy_no, midsc FROM material_cost_header LEFT OUTER JOIN miitem ON (mipn=assy_no) WHERE plan_no = '{$_POST['plan']}'
    ";
    if (getResult2($query, $res) <= 0) {
        $_SESSION['s_sysmsg'] = "{$plan}���Ǥ���Ͽ����Ƥ��ޤ���";
        $parts_no  = '&nbsp;';
        $assy_name = "<font color='red'>̤ �� Ͽ</font>";
        $kansei    = '&nbsp;';
        $kouji_no  = '&nbsp;';
    } else {
        $parts_no  = $res[0][0];
        $assy_name = $res[0][1];
        $kansei    = '&nbsp;';
        $kouji_no  = '&nbsp;';
        $_SESSION['plan_no']  = $plan;       // �ײ��ֹ�γ���(entry�������줿�餳��ǽ���)
        $_SESSION['assy_no']  = $parts_no;
    } 
} elseif (isset($_POST['plan'])) {
    $plan = $_POST['plan'];
    $query = "select parts_no, midsc, kansei, note15
                from
                    assembly_schedule
                left outer join
                    miitem
                on (parts_no=mipn)
                where plan_no='{$plan}'";
    $res = array();
    if (getResult($query, $res) <= 0) {
        $_SESSION['s_sysmsg'] = "{$plan}���Ǥ���Ͽ����Ƥ��ޤ���";
        $parts_no  = '&nbsp;';
        $assy_name = "<font color='red'>̤ �� Ͽ</font>";
        $kansei    = '&nbsp;';
        $kouji_no  = '&nbsp;';
    } else {
        $parts_no  = $res[0][0];
        $assy_name = $res[0][1];
        $kansei    = $res[0][2];
        $kouji_no  = $res[0][3];
        $_SESSION['plan_no']  = $plan;       // �ײ��ֹ�γ���(entry�������줿�餳��ǽ���)
        $_SESSION['assy_no']  = $parts_no;
    }
} else {
    $plan = '';
}

////////////// ��Ͽ�ܥ��󤬲����줿(entry�ܥ���)
if (isset($_POST['entry'])) {
    header('Location: ' . H_WEB_HOST . $menu->out_action('���������Ͽ'));  // �������ʤ���Ͽ��
    exit();
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
<?php echo $menu->out_site_java()?>
<?php echo $menu->out_css()?>

<!--    �ե��������ξ��
<script language='JavaScript' src='template.js?<?php echo $uniq ?>'></script>
-->

<script language="JavaScript">
<!--
/* ����ʸ�����������ɤ��������å� */
function isDigit(str) {
    var len=str.length;
    var c;
    for (i=1; i<len; i++) {
        c = str.charAt(i);
        if ((c < "0") || (c > "9")) {
            return true;
        }
    }
    return false;
}

function chk_plan_entry(obj) {
    obj.plan.value = obj.plan.value.toUpperCase();
    if (obj.plan.value.length != 0) {
        if (obj.plan.value.length != 8) {
            alert("�ײ��ֹ�η���ϣ���Ǥ���");
            obj.plan.focus();
            obj.plan.select();
            return false;
        } else {
            return true;
        }
    }
    alert('�ײ��ֹ椬���Ϥ���Ƥ��ޤ���');
    obj.plan.focus();
    obj.plan.select();
    return false;
}

/* ������ϥե�����Υ�����Ȥ˥ե������������� */
function set_focus() {
    document.entry_form.plan.focus();      // ������ϥե����ब������ϥ����Ȥ򳰤�
    document.entry_form.plan.select();
}
// -->
</script>

<!-- �������륷���ȤΥե��������򥳥��� HTML���� �����Ȥ�����Ҥ˽���ʤ��������
<link rel='stylesheet' href='template.css?<?php echo $uniq ?>' type='text/css' media='screen'>
-->

<style type="text/css">
<!--
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
.plan_font {
    font-size:      13pt;
    font-weight:    bold;
    text-align:     left;
    font-family:    monospace;
}
.entry_font {
    font-size:      11pt;
    font-weight:    bold;
    color:          red;
}
.winbox {
    border-style:           solid;
    border-width:           1px;
    border-top-color:       #FFFFFF;
    border-left-color:      #FFFFFF;
    border-right-color:     #bdaa90;
    border-bottom-color:    #bdaa90;
    /* background-color:#d6d3ce; */
}
.winbox_field {
    border-style:           solid;
    border-width:           1px;
    border-top-color:       #bdaa90;
    border-left-color:      #bdaa90;
    border-right-color:     #FFFFFF;
    border-bottom-color:    #FFFFFF;
    /* background-color:#d6d3ce; */
}
-->
</style>
</head>
<body onLoad='set_focus()' style='overflow:hidden;'>
    <center>
<?php echo $menu->out_title_border()?>
        
        <br>
        
        <table bgcolor='#d6d3ce' align='center' border='1' cellspacing='0' cellpadding='3'>
            <tr><td> <!----------- ���ߡ�(�ǥ�������) ------------>
        <table class='winbox_field' width='100%' bgcolor='#d6d3ce' align='center' border='1' cellspacing='0' cellpadding='3'>
            <form name='entry_form' method='post' action='<?php echo $menu->out_self() ?>' onSubmit='return chk_plan_entry(this)'>
                <tr>
                    <td class='winbox' nowrap align='center'>
                        <div class='caption_font'><?php echo $menu->out_caption() ?></div>
                    </td>
                    <td class='winbox' width='300' nowrap align='center'>
                        <input class='plan_font' type='text' name='plan' value='<?php echo $plan ?>' size='8' maxlength='8'>
                        <input class='pt11b' type='submit' name='conf' value='��ǧ'>
                    </td>
                </tr>
                <?php if ($plan != '') { ?>
                <tr>
                    <td class='winbox' nowrap align='center'>
                        <div class='caption_font'>�����ֹ�</div>
                    </td>
                    <td class='winbox' nowrap align='left'>
                        <div class='pt12b'><?php echo $parts_no ?></div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap align='center'>
                        <div class='caption_font'>�� �� ̾</div>
                    </td>
                    <td class='winbox' width='300' nowrap align='left'>
                        <div class='pt12b'><?php echo $assy_name ?></div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap align='center'>
                        <div class='caption_font'>�� �� ��</div>
                    </td>
                    <td class='winbox' nowrap align='left'>
                        <div class='pt12b'><?php echo $kansei ?></div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap align='center'>
                        <div class='caption_font'>�����ֹ�</div>
                    </td>
                    <td class='winbox' nowrap align='left'>
                        <div class='pt12b'><?php echo $kouji_no ?></div>
                    </td>
                </tr>
                    <?php if ($parts_no != '' && $parts_no != '&nbsp;') { ?>
                    <tr>
                        <td class='winbox' colspan='2' nowrap align='center'>
                            <input class='entry_font' type='submit' name='entry' value='��Ͽ'>
                        </td>
                    </tr>
                    <?php } ?>
                <?php } ?>
            </form>
        </table>
            </td></tr>
        </table> <!----------------- ���ߡ�End ------------------>
        
    </center>
</body>
<?php echo $menu->out_alert_java()?>
</html>
<?php
ob_end_flush();                 // ���ϥХåե���gzip���� END
?>
