<?php
//////////////////////////////////////////////////////////////////////////////
// ������ư���������ƥ�� �ù��ؼ�(�ؼ����ƥʥ�)  �ե졼��إå������  //
// Copyright (C) 2004-2005 2007 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp //
// Changed history                                                          //
// 2004/07/27 Created  equip_workMnt_Header.php                             //
// 2004/08/08 �ե졼���Ǥ�������application��_parent���ѹ�(FRAME̵���б�) //
// 2004/11/16 �����̤��б� $factory($_SESSION['factory'])                   //
// 2004/12/09 HELPɽ�����ɲ�                                                //
// 2005/06/24 F2/F12��������뤿����б��� JavaScript�� set_focus()���ɲ�   //
// 2007/03/27 set_site()�᥽�åɤ�INDEX_EQUIP���ѹ� equipment_select�ν����//
// 2007/09/18 E_ALL | E_STRICT ���ѹ�                                       //
//////////////////////////////////////////////////////////////////////////////
// ini_set('error_reporting', E_ALL | E_STRICT);
// ini_set('display_errors', '1');             // Error ɽ�� ON debug �� ��꡼���女����
ob_start('ob_gzhandler');                   // ���ϥХåե���gzip����
session_start();                            // ini_set()�μ��˻��ꤹ�뤳�� Script �Ǿ��
require_once ('../../function.php');                // TNK ������ function
require_once ('../EquipControllerHTTP.php');        // TNK ������ MVC Controller Class
require_once ('../../MenuHeader.php');              // TNK ������ menu class
access_log();                                       // Script Name �ϼ�ư����

///// TNK ���ѥ�˥塼���饹�Υ��󥹥��󥹤����
$menu = new MenuHeader();                   // ǧ�ڥ����å�0=���̰ʾ� �����=���å������ �����ȥ�̤����

///// �������ѥ��å���󥯥饹�Υ��󥹥��󥹤����
$equipSession = new equipSession();

$request = new Request();

////////////// ����������
$menu->set_site(INDEX_EQUIP, 23);           // site_index=40(������˥塼) site_id=23(�ؼ����ƥʥ�)
////////////// target����
// $menu->set_target('application');           // �ե졼���Ǥ�������target°����ɬ��
$menu->set_target('_parent');               // �ե졼���Ǥ�������target°����ɬ��

/////////// �����ʬ���������
$factory = $equipSession->getFactory();
$fact_name = $equipSession->getFactoryName($factory);

/////////// ��ž�ؼ���˥塼����������
$equipment_select = $request->get('equipment_select');

//////////// �����ȥ�̾(�������Υ����ȥ�̾�ȥե�����Υ����ȥ�̾)
$menu->set_title("������ư���� �ؼ����ƥʥ�&nbsp;&nbsp;{$fact_name}");
//////////// ɽ�������
$menu->set_caption('��ȶ�ʬ�����򤷤Ʋ�����');

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
.item {
    position: absolute;
    top:    90px;
    left:   90px;
}
.s_radio {
    color:              white;
    background-color:   blue;
    font-weight:        bold;
    /***
    ***/
    font-size: 11pt;
}
.u_radio {
    font-size: 11pt;
}
radio {
    outline: 0px none black;
}
label {
    outline: 0px none black;
}
-->
</style>
<script type='text/javascript'>
<!--
function radio_select(num) {
    // document.radioForm.elements[num].checked = true;
    if (document.getElementById) {                                      // IE5.5-, NN6- NN7.1-
        document.getElementById('radio0').className = 'u_radio';
        document.getElementById('radio1').className = 'u_radio';
        document.getElementById('radio2').className = 'u_radio';
        document.getElementById('radio3').className = 'u_radio';
        document.getElementById('radio4').className = 'u_radio';
        document.getElementById('radio5').className = 'u_radio';
        document.getElementById('radio6').className = 'u_radio';
        if (num == 0) {
            document.getElementById('radio0').className = 's_radio';
        } else if (num == 1) {
            document.getElementById('radio1').className = 's_radio';
        } else if (num == 2) {
            document.getElementById('radio2').className = 's_radio';
        } else if (num == 3) {
            document.getElementById('radio3').className = 's_radio';
        } else if (num == 4) {
            document.getElementById('radio4').className = 's_radio';
        } else if (num == 5) {
            document.getElementById('radio5').className = 's_radio';
        } else if (num == 6) {
            document.getElementById('radio6').className = 's_radio';
        }
    } else if (document.all) {                                          // IE4-
        document.all['radio0'].className = 'u_radio';
        document.all['radio1'].className = 'u_radio';
        document.all['radio2'].className = 'u_radio';
        document.all['radio3'].className = 'u_radio';
        document.all['radio4'].className = 'u_radio';
        document.all['radio5'].className = 'u_radio';
        document.all['radio6'].className = 'u_radio';
        if (num == 0) {
            document.all['radio0'].className = 's_radio';
        } else if (num == 1) {
            document.all['radio1'].className = 's_radio';
        } else if (num == 2) {
            document.all['radio2'].className = 's_radio';
        } else if (num == 3) {
            document.all['radio3'].className = 's_radio';
        } else if (num == 4) {
            document.all['radio4'].className = 's_radio';
        } else if (num == 5) {
            document.all['radio5'].className = 's_radio';
        } else if (num == 6) {
            document.all['radio6'].className = 's_radio';
        }
    }
    document.radioForm.submit();
    // document.body.focus();   // outline ���б�����ʤ��ä�
    document.mhForm.backwardStack.focus();  // �嵭��IE�ΤߤΤ���NN�б�
}
function win_open(url) {
    var w = 800;
    var h = 600;
    var left = (screen.availWidth  - w) / 2;
    var top  = (screen.availHeight - h) / 2;
    window.open(url, 'help_win', 'width='+w+',height='+h+',scrollbars=yes,status=no,toolbar=no,location=no,menubar=no,top='+top+',left='+left);
}
/* ������ϥե�����Υ�����Ȥ˥ե������������� */
function set_focus() {
    // document.body.focus();   // F2/F12������ͭ���������б�
    document.mhForm.backwardStack.focus();  // �嵭��IE�ΤߤΤ���NN�б�
}
// -->
</script>
</head>
<body onLoad='set_focus()'>
    <center>
<?= $menu->out_title_border() ?>
        
        <table bgcolor='#d6d3ce' align='center' cellspacing='0' cellpadding='3' border='1'>
            <tr><td> <!----------- ���ߡ�(�ǥ�������) ------------>
        <table bordercolordark='white' bordercolorlight='#bdaa90' bgcolor='#d6d3ce' align='center' cellspacing='0' cellpadding='3' border='1'>
            <tr>
                <td align='center' nowrap class='caption_font'>
                    <?= $menu->out_caption() ?>
                    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                    <input style='font-size:10pt; font-weight:bold; color:blue;' type='button' name='work_mnt_help' value='HELP' onClick='win_open("help/Mnt_top_help.html")'>
                    <br>
                    <table width='100%' bordercolordark='white' bordercolorlight='#bdaa90' align='center' border='1' cellspacing='0' cellpadding='3'>
                        <form name='radioForm' action='equip_workMnt_List.php' method='post' target='List'>
                            <tr align='center'>
                                <td nowrap id='radio0' class='<?php if ($equipment_select == 'init_data_input') echo 's_radio'; else echo 'u_radio'; ?>'>
                                    <input type='radio' name='equipment_select' value='init_data_input' id='input' onClick='radio_select(0)'<?php if ($equipment_select == 'init_data_input') echo ' checked'?>>
                                    <label for='input'>��ž����
                                </td>
                                <td nowrap id='radio1' class='<?php if ($equipment_select == 'init_data_end') echo 's_radio'; else echo 'u_radio'; ?>'>
                                    <input type='radio' name='equipment_select' value='init_data_end' id='end' onClick='radio_select(1)'<?php if ($equipment_select == 'init_data_end') echo ' checked'?>>
                                    <label for='end'>�ù���λ
                                </td>
                                <td nowrap id='radio2' class='<?php if ($equipment_select == 'init_data_cut') echo 's_radio'; else echo 'u_radio'; ?>'>
                                    <input type='radio' name='equipment_select' value='init_data_cut' id='cut' onClick='radio_select(2)'<?php if ($equipment_select == 'init_data_cut') echo ' checked'?>>
                                    <label for='cut'>��ž����
                                </td>
                                <td nowrap id='radio3' class='<?php if ($equipment_select == 'break_data') echo 's_radio'; else echo 'u_radio'; ?>'>
                                    <input type='radio' name='equipment_select' value='break_data' id='break' onClick='radio_select(3)'<?php if ($equipment_select == 'break_data') echo ' checked'?>>
                                    <label for='break'>���Ƿײ�
                                </td>
                                <td nowrap id='radio4' class='<?php if ($equipment_select == 'init_data_edit') echo 's_radio'; else echo 'u_radio'; ?>'>
                                    <input type='radio' name='equipment_select' value='init_data_edit' id='edit' onClick='radio_select(4)'<?php if ($equipment_select == 'init_data_edit') echo ' checked'?>>
                                    <label for='edit'>�ؼ��ѹ�
                                </td>
                                <td nowrap id='radio5' class='<?php if ($equipment_select == 'plan_data') echo 's_radio'; else echo 'u_radio'; ?>'>
                                    <input type='radio' name='equipment_select' value='plan_data' id='plan' onClick='radio_select(5)'<?php if ($equipment_select == 'plan_data') echo ' checked'?>>
                                    <label for='plan'>ͽ��ײ�
                                </td>
                                <td nowrap id='radio6' class='<?php if ($equipment_select == '') echo 's_radio'; else echo 'u_radio'; ?>'>
                                    <input type='radio' name='equipment_select' value='working' id='working' onClick='radio_select(6)'<?php if ($equipment_select == '') echo ' checked'?>>
                                    <label for='working'>���ù���
                                </td>
                            </tr>
                            <tr align='center' class='u_radio'>
                                <td nowrap>(�ǡ�������)</td><td>��</td><td>��</td><td nowrap>(�ǡ������)</td><td nowrap>(�ǡ�������)</td></td><td>��</td><td>��</td>
                                <input type='hidden' name='select_submit' value='�¹�'>
                            </tr>
                        </form>
                    </table>
                </td>
            </tr>
        </table>
            </td></tr>
        </table> <!----------------- ���ߡ�End ------------------>
        
        <!-- <hr color='797979'> -->
        
    </center>
</body>
<script type='text/javascript'>
<!--
/***** default ���� 
if (document.getElementById) {                                      // IE5.5-, NN6- NN7.1-
    document.getElementById('radio6').className = 's_radio';
} else if (document.all) {                                          // IE4-
    document.all['radio6'].className = 's_radio';
}
*****/
// -->
</script>
</html>
<?php ob_end_flush(); // ���ϥХåե���gzip���� END ?>
