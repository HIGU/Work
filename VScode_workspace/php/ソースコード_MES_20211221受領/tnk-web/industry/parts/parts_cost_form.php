<?php
//////////////////////////////////////////////////////////////////////////////
// ñ������ξȲ� �������ե�����  ������ UKWLIB/W#HICOST                  //
// Copyright(C) 2004-2005 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp       //
// Changed history                                                          //
// 2004/05/17 Created   parts_cost_form_form.php                            //
// 2004/12/03 $lot_cost �����å�������Ͽ����Ƥ��Ƥ�ɽ�����ʤ��褦���ѹ�  //
// 2005/01/11 MenuHeader class ����Ѥ��ƶ��̥�˥塼���ڤ�ǧ���������ѹ�   //
//////////////////////////////////////////////////////////////////////////////
ini_set('error_reporting', E_ALL);          // E_ALL='2047' debug ��
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
$menu->set_site(30, 14);                    // site_index=30(������˥塼) site_id=14(ñ������Ȳ�)
////////////// �꥿���󥢥ɥ쥹����
// $menu->set_RetUrl(INDUST_MENU);             // �̾�ϻ��ꤹ��ɬ�פϤʤ�
//////////// �����ȥ�̾(�������Υ����ȥ�̾�ȥե�����Υ����ȥ�̾)
$menu->set_title('ñ �� �� �� �� �� �� (�������)');
//////////// ɽ�������
$menu->set_caption('������ɬ�פʾ��������������򤷤Ʋ�������');
//////////// �ƽ����action̾�ȥ��ɥ쥹����
$menu->set_action('ñ������Ȳ�',   INDUST . 'parts/parts_cost_view.php');

//////////// JavaScript Stylesheet File ��ɬ���ɤ߹��ޤ���
$uniq = uniqid('target');

/////////////// �����Ϥ��ѿ��ν����
if ( isset($_SESSION['cost_parts_no']) ) {
    $parts_no = $_SESSION['cost_parts_no'];
} else {
    $parts_no = '';              // �����
}
if ( isset($_SESSION['cost_lot_cost']) ) {
    $lot_cost = $_SESSION['cost_lot_cost'];
    $lot_cost = '';             // �����
} else {
    $lot_cost = '';             // �����
}
if ( isset($_SESSION['cost_page']) ) {
    $cost_page = $_SESSION['cost_page'];
} else {
    $cost_page = '25';          // �����
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
<?= $menu->out_site_java() ?>
<?= $menu->out_css() ?>

<!--    �ե��������ξ�� -->
<script language='JavaScript' src='./parts_cost_form.js?<?= $uniq ?>'></script>

<script language="JavaScript">
<!--
// -->
</script>

<!-- �������륷���ȤΥե��������򥳥��� HTML���� �����Ȥ�����Ҥ˽���ʤ��������
<link rel='stylesheet' href='<?= MENU_FORM . '?' . $uniq ?>' type='text/css' media='screen'>
-->

<style type="text/css">
<!--
.pt8 {
    font-size:      8pt;
    font-weight:    normal;
    font-family:    monospace;
}
.pt9 {
    font-size:      9pt;
    font-weight:    normal;
    font-family:    monospace;
}
.pt10 {
    font-size:      10pt;
    font-weight:    normal;
    font-family:    monospace;
}
.pt10b {
    font-size:      10pt;
    font-weight:    bold;
    font-family:    monospace;
}
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
th {
    background-color:   blue;
    color:              yellow;
    font-size:          10pt;
    font-weight:        bold;
    font-family:        monospace;
}
td {
    font-size: 10pt;
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
    /* background-color:#d6d3ce; */
}
a:hover {
    background-color:   blue;
    color:              white;
}
a {
    color:   blue;
}
-->
</style>
</head>
<body onLoad='document.parts_cost_form.parts_no.focus(); document.parts_cost_form.parts_no.select()'>
    <center>
<?= $menu->out_title_border() ?>
        <form name='parts_cost_form' action='parts_cost_view.php' method='post' onSubmit='return chk_parts_cost_form(this)'>
            <!----------------- ������ ��ʸ��ɽ������ ------------------->
            <table bgcolor='#d6d3ce' border='1' cellspacing='0' cellpadding='3'>
                <tr><td> <!----------- ���ߡ�(�ǥ�������) ------------>
            <table class='winbox_field' width='100%' bgcolor='#d6d3ce' border='1' cellspacing='0' cellpadding='3'>
                <tr>
                        <!--  bgcolor='#ffffc6' �������� --> 
                    <td class='winbox' colspan='2' align='center'>
                        <div class='caption_font'><?= $menu->out_caption(), "\n" ?></div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' align='right'>
                        �����ֹ�λ���
                    </td>
                    <td class='winbox' align='center'>
                        <input type='text' name='parts_no' class='pt12b' size='9' value='<?= $parts_no ?>' maxlength='9'>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' align='right'>
                        ñ���λ���(0���ϻ��ꤷ�ʤ����̵�뤵��ޤ�)
                    </td>
                    <td class='winbox' align='center'>
                        <input type='text' name='lot_cost' class='pt12b' size='10' value='<?= $lot_cost ?>' maxlength='10'>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' align='right'>
                        ���ڡ�����ɽ���Կ�����ꤷ�Ʋ�����
                    </td>
                    <td class='winbox' align='center'>
                        <input type='text' name='cost_page' size='4' value='<?= $cost_page ?>' maxlength='3'>
                        ����͡�25
                    </td>
                </tr>
                <tr>
                    <td class='winbox' colspan='2' align='center'>
                        <input type='submit' name='cost_view' value='�¹�' >
                    </td>
                </tr>
            </table>
                </td></tr>
            </table> <!----------------- ���ߡ�End ------------------>
        </form>
    </center>
</body>
<?= $menu->out_alert_java() ?>
</html>
<?php
ob_end_flush();                 // ���ϥХåե���gzip���� END
?>
