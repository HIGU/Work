<?php
//////////////////////////////////////////////////////////////////////////////
// ���߸����� �����ܤη�ʿ�ѽи˿�����ͭ������Ȳ�           MVC View ��  //
// Copyright (C) 2007      Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp      //
// Changed history                                                          //
// 2007/06/13 Created   inventory_average_EditFactorForm.php                //
// 2007/06/14 ��ä��ɲä�visibility:hidden;�ǽ���������åץإ�פβ�������//
//////////////////////////////////////////////////////////////////////////////
// ini_set('error_reporting', E_STRICT);       // E_STRICT=2048(php5) E_ALL=2047 debug ��
ini_set('error_reporting', E_ALL);          // E_STRICT=2048(php5) E_ALL=2047 debug ��
ob_start('ob_gzhandler');                   // ���ϥХåե���gzip����
session_start();                            // ini_set()�μ��˻��ꤹ�뤳�� Script �Ǿ��
require_once ('../../../MenuHeader.php');   // TNK ������ menu class
///// TNK ���ѥ�˥塼���饹�Υ��󥹥��󥹤����
$menu = new MenuHeader(0);                  // ǧ�ڥ����å�0=���̰ʾ� �����=TOP_MENU �����ȥ�̤����
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=EUC-JP">
<meta http-equiv="Content-Style-Type" content="text/css">
<meta http-equiv="Content-Script-Type" content="text/javascript">
<title>�װ��ޥ��������Խ��ե�����</title>
<link rel='stylesheet' href='/menu_form.css' type='text/css' media='screen'>
<link rel='stylesheet' href='inventory_average.css' type='text/css' media='screen'>
<style type='text/css'>
<!--
body {
    overflow:           hidden;
    background-image:   none;
    background-color:   #d6d3ce;
}
-->
</style>
<script type='text/javascript'>
</script>
</head>
<body
    onLoad='
        parent.InventoryAverage.set_focus(document.EditFactorForm.targetFactorName, "select");
    '
>
<center>
<form name='EditFactorForm' action='' method='post' onSubmit='return parent.InventoryAverage.checkEditFactorForm(this);'>
<table width='100%' bgcolor='#d6d3ce' border='1' cellspacing='0' cellpadding='1'>
    <tr><td> <!----------- ���ߡ�(�ǥ�������) ------------>
<table class='winbox_field list' width='100%' border='1' cellspacing='0' cellpadding='3'>
    <tr>
        <th class='winbox' nowrap>�װ�����</th>
        <td class='winbox' align='center'>
            <input type='text' name='targetFactorName' value='' size='8' maxlength='8'
                title='
ɽ��������¤���Ͽʸ����������ʸ���ǣ�ʸ����������¤��Ƥ��ޤ���
                '
            >
        </td>
        <th class='winbox' nowrap>�װ�����</th>
        <td class='winbox' align='left'>
            <input type='text' name='targetFactorExplanation' value='' size='58' maxlength='40'
                title='
��Ͽʸ����������ʸ���ǣ���ʸ����������¤��Ƥ��ޤ���
                '
            >
        </td>
        <td class='winbox' align='center'>
            <input type='submit' name='editButton' value='��Ͽ' class='editButton'>
        </td>
        <td class='winbox' align='center''>
            <input type='button' name='cancelButton' value='���' class='cancelButton' style='visibility:hidden;'
                onclick='parent.InventoryAverage.AjaxLoadTable("FactorMnt", "showAjax");'
            >
        </td>
        <input type='hidden' name='targetFactor' value=''>
    </tr>
</table>
    </td></tr>
</table> <!----------------- ���ߡ�End ------------------>
</form>
</center>
</body>
</html>
<?php echo $menu->out_alert_java(false)?>
<?php
ob_end_flush();                 // ���ϥХåե���gzip���� END
?>
