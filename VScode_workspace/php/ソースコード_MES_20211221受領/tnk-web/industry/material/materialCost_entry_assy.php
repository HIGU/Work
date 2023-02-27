<?php
//////////////////////////////////////////////////////////////////////////////
// ����������Ͽ  Assy�ֹ�(���ֹ�)��form                                   //
// Copyright(C) 2003-2020 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp       //
// �ѹ�����                                                                 //
// 2003/12/15 ��������  metarialCost_entry_assy.php                         //
// 2004/05/12 �����ȥ�˥塼ɽ������ɽ�� �ܥ����ɲ� menu_OnOff($script)�ɲ� //
// 2007/09/14 �ǿ�����������Ͽ�Ѥ� �ײ��ֹ��Ƭ����� 'ZZ' ��Ϣ����Ͽ     //
// 2007/09/18 assy_no LIKE 'ZZ%' �� plan_no LIKE 'ZZ%' �ߥ�����             //
// 2007/10/04 �ǿ���Ͽ�� �ײ��ֹ��ɽ���ɲ�                                 //
// 2008/03/12 �ǿ���Ͽ��Ʊ�����Ϥ��ǽ�ˤ���١��ײ��ֹ��̿̾��§���ѹ�    //
//            'Z'+AssyNo.����Ƭ����+AssyNo��-���ο�����ʬ+Ϣ��              //
//            ��'LA70356-0'�ʤ�'ZL703560'�Ȥʤ뢪2���ܤ���Ͽ��ZL703561��    //
//            ���ǰ㤤�����ʤξ���Ʊ�������ղ�(�����ʤˤʤ�Τ�����       //
//            �Ϥʤ����Ȼפ��ޤ���)                                    ��ë //
// 2020/03/03 9�θ��0�ˤʤ�褦���ѹ��ʷ夢�դ�ΰ١�                 ��ë //
//////////////////////////////////////////////////////////////////////////////
// ini_set('error_reporting', E_ALL | E_STRICT);// E_ALL='2047' debug ��
// ini_set('display_errors', '1');             // Error ɽ�� ON debug �� ��꡼���女����
// ini_set('implicit_flush', 'off');       // echo print �� flush �����ʤ�(�٤��ʤ뤿��) CLI CGI��
// ini_set('max_execution_time', 1200);    // ����¹Ի���=20ʬ CLI CGI��
ob_start('ob_gzhandler');                   // ���ϥХåե���gzip����
session_start();                            // ini_set()�μ��˻��ꤹ�뤳�� Script �Ǿ��

require_once ('../../function.php');        // define.php �� pgsql.php �� require_once ���Ƥ���
require_once ('../../tnk_func.php');        // TNK �˰�¸������ʬ�δؿ��� require_once ���Ƥ���
require_once ('../../MenuHeader.php');      // TNK ������ menu class
access_log();                               // Script Name �ϼ�ư����

///// TNK ���ѥ�˥塼���饹�Υ��󥹥��󥹤����
$menu = new MenuHeader(0);                  // ǧ�ڥ����å�0=���̰ʾ� �����=TOP_MENU �����ȥ�̤����

////////////// ����������
$menu->set_site(30, 999);                   // site_index=30(������˥塼) site_id=21(����������Ͽ �ײ��ֹ�)
////////////// �꥿���󥢥ɥ쥹����
// $menu->set_RetUrl(INDUST_MENU);             // �̾�ϻ��ꤹ��ɬ�פϤʤ�
//////////// �����ȥ�̾(�������Υ����ȥ�̾�ȥե�����Υ����ȥ�̾)
$menu->set_title('�� �� �� �� �� �� Ͽ (�����ֹ�)');
//////////// ɽ�������
$menu->set_caption('�����ֹ������');
//////////// �ƽ����action̾�ȥ��ɥ쥹����
$menu->set_action('���������Ͽ',   INDUST . 'material/material_entry/materialCost_entry_main.php');

//////////// JavaScript Stylesheet File ��ɬ���ɤ߹��ޤ���
$uniq = uniqid('target');

////////////// ��ʬ�Υݥ��ȥǡ���������å�
if (isset($_REQUEST['assy'])) {
    $assy = $_REQUEST['assy'];
    $query = "SELECT midsc FROM miitem WHERE mipn='{$assy}'";
    $new_plan = substr($assy, 0, 1) . substr($assy, 2, 5);  // �ǿ��ξ��ηײ��ֹ��'Z'+AssyNo.����Ƭ����+AssyNo��-���ο�����ʬ
    $check_nplan = 'Z' . $new_plan;                         // Ʊ�������ֹ�ηײ褬��Ͽ����Ƥ��뤫�Υ����å���
    if (getUniResult($query, $assy_name) <= 0) {
        $_SESSION['s_sysmsg'] = "{$assy}���Ǥ���Ͽ����Ƥ��ޤ���";
        $assy_name = "<font color='red'>̤ �� Ͽ</font>";
        $assy = '';
    } else {
        $query = "SELECT plan_no FROM material_cost_header WHERE plan_no LIKE '{$check_nplan}%' ORDER BY last_date DESC LIMIT 1";
        if (getUniResult($query, $plan) > 0) {
            if (substr($plan, 7, 1) == 9) {
                $temp_plan = 0;
            } else {
                $temp_plan = substr($plan, 7, 1) + 1;
            }
            $plan = 'Z' . $new_plan . $temp_plan;    // �Ǹ�ηײ��ֹ�˥��󥯥����
            $_SESSION['plan_no'] = $plan;
        } else {
            $_SESSION['plan_no'] = 'Z' . $new_plan . 0;  // ���
        }
        $_SESSION['assy_no']  = $assy;
        $menu->set_retGET('assy', $assy);
    }
} else {
    $assy = '';
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
<meta http-equiv="Content-Script-Type" content="text/javascript">
<title><?php echo $menu->out_title() ?></title>
<?php echo $menu->out_site_java()?>
<?php echo $menu->out_css()?>

<!--    �ե��������ξ��
<script type='text/javascript language='JavaScript' src='template.js?<?php echo $uniq ?>'>
</script>
-->

<script type='text/javascript' language="JavaScript">
<!--
function chk_assy_entry(obj) {
    obj.assy.value = obj.assy.value.toUpperCase();
    if (obj.assy.value.length != 0) {
        if (obj.assy.value.length != 9) {
            alert("�������ֹ�η���ϣ���Ǥ���");
            obj.assy.focus();
            obj.assy.select();
            return false;
        } else {
            return true;
        }
    }
    alert('�������ֹ椬���Ϥ���Ƥ��ޤ���');
    obj.assy.focus();
    obj.assy.select();
    return false;
}

/* ������ϥե�����Υ�����Ȥ˥ե������������� */
function set_focus(){
    document.entry_form.assy.focus();      // ������ϥե����ब������ϥ����Ȥ򳰤�
    // document.entry_form.assy.select();
}
// -->
</script>

<!-- �������륷���ȤΥե��������򥳥��� HTML���� �����Ȥ�����Ҥ˽���ʤ�������� -->
<link rel='stylesheet' href='material.css?<?php echo $uniq ?>' type='text/css' media='screen'>

<style type="text/css">
<!--
-->
</style>
</head>
<body onLoad='set_focus()'>
    <center>
<?php echo $menu->out_title_border()?>
        
        <br>
        
        <table bgcolor='#d6d3ce' width='350' align='center' border='1' cellspacing='0' cellpadding='3'>
            <tr><td> <!----------- ���ߡ�(�ǥ�������) ------------>
        <table class='winbox_field' width='100%' bgcolor='#d6d3ce' align='center' border='1' cellspacing='0' cellpadding='3'>
            <form name='entry_form' method='post' action='<?php echo $menu->out_self() ?>' onSubmit='return chk_assy_entry(this)'>
                <tr>
                    <td nowrap align='center' class='caption_font'>
                        <?php echo $menu->out_caption() . "\n" ?>
                    </td>
                </tr>
                <tr>
                    <td nowrap align='center'>
                        <input type='text' class='assy_font' name='assy' value='<?php echo $assy ?>' size='9' maxlength='9' onKeyUp='baseJS.keyInUpper(this);'>
                    </td>
                </tr>
                <?php if ($assy == '') { ?>
                <tr>
                    <td nowrap align='center' class='caption_font'>
                        <input class='pt11b' type='submit' name='conf' value='��ǧ'>
                    </td>
                </tr>
                <?php } else { ?>
                <tr>
                    <td nowrap align='center' class='pt12b'>
                        <?php echo $assy_name ?>
                    </td>
                </tr>
                <tr>
                    <td nowrap align='center' class='pt12b'>
                        �ǿ���Ͽ�� �ײ��ֹ桧<?php echo $_SESSION['plan_no'] ?>
                    </td>
                </tr>
                
                <tr>
                    <td nowrap align='center' class='caption_font'>
                        <input class='pt11b' type='submit' name='entry' value='��Ͽ'>
                    </td>
                </tr>
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
