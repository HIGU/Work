<?php
//////////////////////////////////////////////////////////////////////////////
// ������ ���ʺ߸˷��� �Ȳ� ���ʻ���ե�����                                //
// Copyright(C) 2004-2007 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp       //
// Changed history                                                          //
// 2004/12/20 Created  parts_stock_form.php                                 //
// 2004/12/27 $_SESSION['stock_date_lower'] = $date_low��$date_upp ������ //
// 2006/06/02 ��ʸ���Ѵ��ѤΥ��٥�ȥϥ�ɥ顼onKeyUp���ɲ�                 //
// 2007/02/20 parts/����parts/parts_stock_history/parts_stock_form.php���ѹ�//
// 2007/03/22 parts_stock_view.php �� parts_stock_history_Main.php ���ѹ�   //
// 2007/10/19 E_ALL �� E_ALL | E_STRICT �� <meta>javascript���ɲ� ����¾    //
// 2019/06/25 ɽ�������1000����ѹ����������Ǥʤ����Ȥ������  ��ë      //
//////////////////////////////////////////////////////////////////////////////
// ini_set('error_reporting', E_ALL | E_STRICT);
// ini_set('display_errors', '1');             // Error ɽ�� ON debug �� ��꡼���女����
// ini_set('zend.ze1_compatibility_mode', '1');    // zend 1.X ����ѥ� php4�θߴ��⡼��
// ini_set('implicit_flush', 'off');           // echo print �� flush �����ʤ�(�٤��ʤ뤿��) CLI CGI��
// ini_set('max_execution_time', 1200);        // ����¹Ի���=20ʬ CLI CGI��
ob_start('ob_gzhandler');                   // ���ϥХåե���gzip����
session_start();                            // ini_set()�μ��˻��ꤹ�뤳�� Script �Ǿ��

require_once ('../../../function.php');     // define.php �� pgsql.php �� require_once ���Ƥ���
require_once ('../../../tnk_func.php');     // TNK �˰�¸������ʬ�δؿ��� require_once ���Ƥ���
require_once ('../../../MenuHeader.php');   // TNK ������ menu class
access_log();                               // Script Name �ϼ�ư����

///// TNK ���ѥ�˥塼���饹�Υ��󥹥��󥹤����
$menu = new MenuHeader(0);                  // ǧ�ڥ����å�0=���̰ʾ� �����=TOP_MENU �����ȥ�̤����

////////////// ����������
$menu->set_site(30, 40);                    // site_index=30(������˥塼) site_id=40(���ʺ߸˷���)999(�����Ȥ򳫤�)
////////////// �꥿���󥢥ɥ쥹����
// $menu->set_RetUrl(SYS_MENU);                // �̾�ϻ��ꤹ��ɬ�פϤʤ�
//////////// �����ȥ�̾(�������Υ����ȥ�̾�ȥե�����Υ����ȥ�̾)
$menu->set_title('���ʺ߸˷���ξȲ�');
//////////// ɽ�������
$menu->set_caption('���������ֹ����ϥե����ࡡ');
//////////// �ƽ����action̾�ȥ��ɥ쥹����
$menu->set_action('view',   INDUST . 'parts/parts_stock_history/parts_stock_history_Main.php');

//////////// JavaScript Stylesheet File ��ɬ���ɤ߹��ޤ���
$uniq = uniqid('target');

/////////////// �����Ϥ��ѿ��ν����
if ( isset($_SESSION['stock_parts']) ) {
    $parts_no = $_SESSION['stock_parts'];
} else {
    $parts_no = '';             // �����
}
if ( isset($_SESSION['stock_date_lower']) ) {
    $date_low = $_SESSION['stock_date_lower'];
} else {
    $date_low = '20000401';     // �����
    $_SESSION['stock_date_lower'] = $date_low;
}
if ( isset($_SESSION['stock_date_upper']) ) {
    $date_upp = $_SESSION['stock_date_upper'];
} else {
    $date_upp = date('Ymd');    // �����
    $_SESSION['stock_date_upper'] = $date_upp;
}
if ( isset($_SESSION['stock_view_rec']) ) {
    $view_rec = $_SESSION['stock_view_rec'];
} else {
    //$view_rec = '500';          // �����
    $view_rec = '1000';          // �����
    $_SESSION['stock_view_rec'] = $view_rec;
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
<?php if ($_SESSION['s_sysmsg'] == '') echo $menu->out_site_java(); ?>
<?php echo $menu->out_css() ?>

<!--    �ե��������ξ�� -->
<script language='JavaScript' src='./parts_stock_form.js?<?php echo $uniq ?>'></script>

<script type='text/javascript' language='JavaScript'>
<!--
// -->
</script>

<!-- �������륷���ȤΥե��������򥳥��� HTML���� �����Ȥ�����Ҥ˽���ʤ��������
<link rel='stylesheet' href='<?php echo MENU_FORM . '?' . $uniq ?>' type='text/css' media='screen'>
-->

<style type='text/css'>
<!--
.pt12b {
    font-size:          12pt;
    font-weight:        bold;
    font-family:        monospace;
}
.pt14b {
    font-size:          14pt;
    font-weight:        bold;
    font-family:        monospace;
}
.caption_font {
    font-size:          12pt;
    font-weight:        bold;
    font-family:        monospace;
    background-color:   blue;
    color:              yellow;
}
.margin0 {
    margin:             0%;
}
td {
    font-size:          12pt;
    font-weight:        bold;
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
<body style='overflow:hidden;' onLoad='document.parts_stock_form.parts_no.focus(); document.parts_stock_form.parts_no.select()'>
    <center>
<?php echo $menu->out_title_border() ?>
        <form name='parts_stock_form' action='<?php echo $menu->out_action('view') ?>' method='get' onSubmit='return chk_parts_stock_form(this)'>
            <!----------------- ������ ��ʸ��ɽ������ ------------------->
            <table bgcolor='#d6d3ce' border='1' cellspacing='0' cellpadding='3'>
                <tr><td> <!----------- ���ߡ�(�ǥ�������) ------------>
            <table class='winbox_field' width='100%' border='1' cellspacing='0' cellpadding='3'>
                <tr>
                        <!--  bgcolor='#ffffc6' �������� --> 
                    <td class='winbox' colspan='2' align='center'>
                        <font class='caption_font'><?php echo $menu->out_caption(), "\n" ?></font>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' align='right'>
                        �����ֹ�λ���
                    </td>
                    <td class='winbox' align='center'>
                        <input type='text' name='parts_no' class='pt14b' size='9' value='<?php echo $parts_no ?>' maxlength='9'
                            onKeyUp='baseJS.keyInUpper(this);'
                        >
                    </td>
                </tr>
                <tr>
                    <td class='winbox' align='right' style='font-size:11pt; font-weight:normal;'>
                        �����ϰϻ���(����)(YYYYMMDD)
                    </td>
                    <td class='winbox' align='center'>
                        <input type='text' name='date_low' class='pt12b' size='8' value='<?php echo $date_low ?>' maxlength='8'>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' align='right' style='font-size:11pt; font-weight:normal;'>
                        �����ϰϻ���(���)(YYYYMMDD)
                    </td>
                    <td class='winbox' align='center'>
                        <input type='text' name='date_upp' class='pt12b' size='8' value='<?php echo $date_upp ?>' maxlength='8'>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' align='right' style='font-size:11pt; font-weight:normal;'>
                        ����ɽ�����(����)
                    </td>
                    <td class='winbox' align='center'>
                        <select name='view_rec' class='ret_font'>
                            <option value= '500'>&nbsp;500</option>
                            <option value='1000'>1000</option>
                            <option value='2000'>2000</option>
                            <option value='4000'>4000</option>
                            <option value='6000'>6000</option>
                        </select>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' colspan='2' align='center'>
                        <input type='submit' name='parts_stock_view' value='�¹�' >
                        <!-- Enter Key �Ǽ¹Ԥ��ޤ��� -->
                    </td>
                </tr>
            </table>
                </td></tr>
            </table> <!----------------- ���ߡ�End ------------------>
        </form>
        <!--
        <br>
        <table style='border: 2px solid #CCBBAA;'>
            <tr><td align='center' class='pt11b' id='note'>�����ϰϤ�����ʤ�����ѹ�����ɬ�פϤ���ޤ���</td></tr>
        </table>
        -->
    </center>
</body>
<?php echo $menu->out_alert_java() ?>
</html>
<?php
ob_end_flush();                 // ���ϥХåե���gzip���� END
?>
