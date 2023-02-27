<?php
////////////////////////////////////////////////////////////////////////////////
// ������Ư�����ؼ����ƥʥ�                                               //
//                                             MVC View �� �ꥹ��ɽ��(Header) //
// Copyright (C) 2021-2021 Ryota.waki ryota_waki@nitto-kohki.co.jp            //
// Changed history                                                            //
// 2021/03/24 Created monitoring_ViewHeader.php                               //
// 2021/03/24 Release.                                                        //
////////////////////////////////////////////////////////////////////////////////
ini_set('error_reporting', E_ALL);              // E_ALL='2047' debug ��
// ini_set('display_errors', '1');                 // Error ɽ�� ON debug �� ��꡼���女����
ob_start('ob_gzhandler');                       // ���ϥХåե���gzip����
session_start();                                // ini_set()�μ��˻��ꤹ�뤳�� Script �Ǿ��
require_once ('../../MenuHeader.php');          // TNK ������ menu class
require_once ('../../function.php');            // TNK ������ function
require_once ('../EquipControllerHTTP.php');    // TNK ������ MVC Controller Class
access_log();                                   // Script Name �ϼ�ư����

///// TNK ���ѥ�˥塼���饹�Υ��󥹥��󥹤����
$menu = new MenuHeader();   // ǧ�ڥ����å�0=���̰ʾ� �����=���å������ �����ȥ�̤����

///// �������ѥ��å���󥯥饹�Υ��󥹥��󥹤����
$equipSession = new equipSession();

$request = new Request();

$menu->set_target('_parent');   // �ե졼���Ǥ�������target°����ɬ��

$menu->set_title('������Ư���� �ؼ����ƥʥ� ������');

//$menu->set_caption("��ȶ�ʬ�����򤷤Ʋ����� <input type='button' value='HELP'>");
$menu->set_caption("��ȶ�ʬ�����򤷤Ʋ�������");

if (isset($_REQUEST['selectMode'])) {
    $s_mode = $_REQUEST['selectMode'];
} else {
    $s_mode = 'start';
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
<?php echo $menu->out_site_java() ?>
<?php echo $menu->out_css() ?>
<?php echo $menu->out_jsBaseClass() ?>

<link rel='stylesheet' href='monitoring.css' type='text/css' media='screen'>
<script type='text/javascript' language='JavaScript' src='monitoring.js'></script>

</head>

<center>
    <?= $menu->out_title_border() ?>

    <table class='pt12b' border="1" cellspacing="0">
    <tr><td> <!----------- ���ߡ�(�ǥ�������) ------------>
        <table width='100%' class='pt12' bgcolor='#d6d3ce' align='center' border='1' cellspacing='0' cellpadding='3'>
            <tr>
                    <!--  bgcolor='#ffffc6' �������� --> 
                <td class='winbox' style='background-color:yellow; color:blue;' colspan='3' align='center'>
                    <div class='caption_font'><?php echo $menu->out_caption(), "\n"?></div>
                </td>
            </tr>

    <form name="header_form" method="post" target="List" action='monitoring_ViewList.php' onSubmit='return true;'>
        <input type='hidden' name='select_mode' id='id_select_mode'>
        <input type='hidden' name='state' id='id_state' value='init'>

        <tr>
            <td nowrap align='center'>
                <input type='radio' name='h_radio' id='id_h_start' value='start' onClick='setSelectMode(this);' <?php if($s_mode=='start') echo ' checked'?>><label for='id_h_start'>��ž����
            </td>
            <td nowrap align='center'>
                <input type='radio' name='h_radio' id='id_h_break' value='break' onClick='setSelectMode(this);' <?php if($s_mode=='break') echo ' checked'?>><label for='id_h_break'>���Ƿײ�
            </td>
            <td nowrap align='center'>
                <input type='radio' name='h_radio' id='id_h_change' value='change' onClick='setSelectMode(this);' <?php if($s_mode=='change') echo ' checked'?>><label for='id_h_change'>�ؼ��ѹ�
            </td>
        </tr>

        <tr>
            <td nowrap align='center'>
                <label for='id_h_start'>(�ǡ�������)
            </td>
            <td nowrap align='center'>
                <label for='id_h_break'>(�ǡ������)
            </td>
            <td nowrap align='center'>
                <label for='id_h_change'>(�ǡ����ѹ�)
            </td>
        </tr>
    </form>

        </table>
    </td></tr>
    </table> <!----------------- ���ߡ�End --------------------->
</center>
</body>
<?php echo $menu->out_alert_java()?>
</html>
