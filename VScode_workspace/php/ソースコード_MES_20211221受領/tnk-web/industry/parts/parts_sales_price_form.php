<?php
//////////////////////////////////////////////////////////////////////////////
// ñ���������������(����ñ��)����  �ե�����                             //
// Copyright(C) 2004-2010 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp       //
// Changed history                                                          //
// 2004/11/19 Created  parts_sales_price_form.php                           //
// 2004/11/24 �����ֹ�ޥ�����̤��Ͽ��alert���ѹ����������Υ������ɲ�   //
// 2004/12/02 �ǥ���������  border='1' cellspacing='0' cellpadding='3'>     //
// 2004/12/25 style='overflow:hidden;' (-xyξ��)���ɲ�                      //
// 2010/08/25 ���դν���ͤ��������ѹ����졼�Ȥν���ͤ�1.1���ѹ�     ��ë  //
//////////////////////////////////////////////////////////////////////////////
ini_set('error_reporting', E_ALL);          // E_ALL='2047' debug ��
// ini_set('display_errors', '1');             // Error ɽ�� ON debug �� ��꡼���女����
// ini_set('implicit_flush', 'off');           // echo print �� flush �����ʤ�(�٤��ʤ뤿��) CLI CGI��
// ini_set('max_execution_time', 1200);        // ����¹Ի���=20ʬ CLI CGI��
ob_start('ob_gzhandler');                   // ���ϥХåե���gzip����
session_start();                            // ini_set()�μ��˻��ꤹ�뤳�� Script �Ǿ��

require_once ('../../function.php');
require_once ('../../tnk_func.php');
require_once ('../../MenuHeader.php');      // TNK ������ menu class
access_log();                               // Script Name �ϼ�ư����

///// TNK ���ѥ�˥塼���饹�Υ��󥹥��󥹤����
$menu = new MenuHeader(0);                  // ǧ�ڥ����å�0=���̰ʾ� �����=TOP_MENU �����ȥ�̤����

////////////// ����������
$menu->set_site(30, 999);                   // site_index=30(������˥塼) site_id=999(�����Ȥ򳫤�)
////////////// �꥿���󥢥ɥ쥹����
// $menu->set_RetUrl(SYS_MENU);                // �̾�ϻ��ꤹ��ɬ�פϤʤ�
//////////// �����ȥ�̾(�������Υ����ȥ�̾�ȥե�����Υ����ȥ�̾)
$menu->set_title('ñ��������������ʤξȲ�');
//////////// ɽ�������
$menu->set_caption('��������������ϥե����ࡡ');
//////////// �ƽ����action̾�ȥ��ɥ쥹����
$menu->set_action('view',   INDUST . 'parts/parts_sales_price_view.php');

//////////// JavaScript Stylesheet File ��ɬ���ɤ߹��ޤ���
$uniq = uniqid('target');

/////////////// �����Ϥ��ѿ��ν����
if ( isset($_SESSION['cost_parts']) ) {
    $parts = $_SESSION['cost_parts'];
} else {
    $parts = '';                // �����
}
if ( isset($_SESSION['cost_regdate']) ) {
    $regdate = $_SESSION['cost_regdate'];
} else {
    //$d_start = date_offset(1);
    $regdate = date_offset(0);      // �����
    //$regdate = '20020331';      // �����
    $_SESSION['cost_regdate'] = $regdate;
}
if ( isset($_SESSION['cost_sales_rate']) ) {
    $sales_rate = $_SESSION['cost_sales_rate'];
} else {
    $sales_rate = '1.1';       // �����
    $_SESSION['cost_sales_rate'] = $sales_rate;
}

/////////// HTML Header ����Ϥ��ƥ���å��������
$menu->out_html_header();
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=EUC-JP">
<meta http-equiv="Content-Style-Type" content="text/css">
<title><?= $menu->out_title() ?></title>
<?php if ($_SESSION['s_sysmsg'] == '') echo $menu->out_site_java(); ?>
<?= $menu->out_css() ?>

<!--    �ե��������ξ�� -->
<script language='JavaScript' src='./parts_sales_price_form.js?<?= $uniq ?>'></script>

<script language="JavaScript">
<!--
// -->
</script>

<!-- �������륷���ȤΥե��������򥳥��� HTML���� �����Ȥ�����Ҥ˽���ʤ��������
<link rel='stylesheet' href='<?= MENU_FORM . '?' . $uniq ?>' type='text/css' media='screen'>
-->

<style type="text/css">
<!--
.pt12b {
    font-size:      12pt;
    font-weight:    bold;
    font-family:    monospace;
}
.pt14b {
    font-size:      14pt;
    font-weight:    bold;
    font-family:    monospace;
}
.caption_font {
    font-size:          12pt;
    font-weight:        bold;
    font-family:        monospace;
    background-color:   blue;
    color:              yellow;
}
.margin0 {
    margin:0%;
}
td {
    font-size:      12pt;
    font-weight:    bold;
}
.winbox {
    border-style: solid;
    border-width: 1px;
    border-top-color:       #FFFFFF;
    border-left-color:      #FFFFFF;
    border-right-color:     #999999;
    border-bottom-color:    #999999;
    background-color:#d6d3ce;
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
</head>
</style>
<body style='overflow:hidden;' onLoad='document.parts_sales_price_form.parts.focus(); document.parts_sales_price_form.parts.select()'>
    <center>
<?= $menu->out_title_border() ?>
        <form name='parts_sales_price_form' action='<?= $menu->out_action('view') ?>' method='get' onSubmit='return chk_parts_sales_price_form(this)'>
            <!----------------- ������ ��ʸ��ɽ������ ------------------->
            <table bgcolor='#d6d3ce' border='1' cellspacing='0' cellpadding='3'>
                <tr><td> <!----------- ���ߡ�(�ǥ�������) ------------>
            <table class='winbox_field' width='100%' border='1' cellspacing='0' cellpadding='3'>
                <tr>
                        <!--  bgcolor='#ffffc6' �������� --> 
                    <td class='winbox' colspan='2' align='center'>
                        <font class='caption_font'><?= $menu->out_caption(), "\n" ?></font>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' align='right'>
                        �����ֹ�λ���
                    </td>
                    <td class='winbox' align='center'>
                        <input type='text' name='parts' class='pt14b' size='9' value='<?= $parts ?>' maxlength='9'>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' align='right'>
                        ñ����Ͽ������λ���(YYYYMMDD)
                    </td>
                    <td class='winbox' align='center'>
                        <input type='text' name='regdate' class='pt12b' size='8' value='<?= $regdate ?>' maxlength='8'>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' align='right'>
                        �������(����ñ��)�졼��(����͡�1.1)
                    </td>
                    <td class='winbox' align='center'>
                        <input type='text' name='sales_rate' class='pt12b' size='4' value='<?= $sales_rate ?>' maxlength='4'>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' colspan='2' align='center'>
                        <input type='submit' name='sales_price_view' value='�¹�' >
                        <!-- Enter Key �Ǽ¹Ԥ��ޤ��� -->
                    </td>
                </tr>
            </table>
                </td></tr>
            </table> <!----------------- ���ߡ�End ------------------>
        </form>
        <br>
        <table style='border: 2px solid #AABBCC;'>
            <tr><td align='center' class='pt11b' id='note'>ñ����Ͽ����� �����˷�³���������Ͽ��̵�����ϡ��ǿ�ñ���Ȥ��롣</td></tr>
        </table>
    </center>
</body>
<?= $menu->out_alert_java() ?>
</html>
<?php
ob_end_flush();                 // ���ϥХåե���gzip���� END
?>
