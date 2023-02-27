<?php
//////////////////////////////////////////////////////////////////////////////
// Ǽ���٤����ʤξȲ� �������ե�����                                      //
// ��˥��Τߡ��Х���롦�ġ���¾�θ������б��ѡ����Ѥξ���               //
// ���Υץ����Υ����Ȥ�����������ǻ��Ѳ�ǽ                         //
// Copyright (C) 2011-     Norihisa.Ohya norihisa_ooya@nitto-kohki.co.jp    //
// Changed history                                                          //
// 2011/11/09 Created  delivery_late_form.php                               //
// 2011/11/10 focus��JAVAScript���ѹ� set_focus()                           //
// 2011/11/22 ���å����ǡ����μ����Ϥ��ǥ��顼ȯ���ΰ�isset���ѹ�         //
//////////////////////////////////////////////////////////////////////////////
ini_set('error_reporting', E_ALL);          // E_ALL='2047' debug ��
// ini_set('display_errors', '1');             // Error ɽ�� ON debug �� ��꡼���女����
// ini_set('implicit_flush', 'off');           // echo print �� flush �����ʤ�(�٤��ʤ뤿��) CLI CGI��
// ini_set('max_execution_time', 1200);     // ����¹Ի���=20ʬ CLI CGI��
ob_start('ob_gzhandler');                   // ���ϥХåե���gzip����
session_start();                            // ini_set()�μ��˻��ꤹ�뤳�� Script �Ǿ��

require_once ('../../../function.php');
require_once ('../../../tnk_func.php');
require_once ('../../../MenuHeader.php');      // TNK ������ menu class
require_once ('../../../ControllerHTTP_Class.php');    // TNK ������ MVC Controller Class
access_log();                               // Script Name �ϼ�ư����

///// TNK ���ѥ�˥塼���饹�Υ��󥹥��󥹤����
$menu = new MenuHeader(0);                  // ǧ�ڥ����å�0=���̰ʾ� �����=TOP_MENU �����ȥ�̤����

////////////// ����������
$menu->set_site(30, 52);                    // site_index=30(������˥塼) site_id=52(Ǽ���٤����ʤξȲ�)
////////////// �꥿���󥢥ɥ쥹����
// $menu->set_RetUrl(INDUST_MENU);             // �̾�ϻ��ꤹ��ɬ�פϤʤ�
//////////// �����ȥ�̾(�������Υ����ȥ�̾�ȥե�����Υ����ȥ�̾)
$menu->set_title('Ǽ���٤����� �� �� �� (�������)');
//////////// ɽ�������
$menu->set_caption('������ɬ�פʾ��������������򤷤Ʋ�������');
//////////// �ƽ����action̾�ȥ��ɥ쥹����
$menu->set_action('Ǽ���٤�����ɽ��',   INDUST . 'order/delivery_late/delivery_late_view.php');
$menu->set_action('Ǽ���٤콸��ɽ',   INDUST . 'order/delivery_late/delivery_late_summary.php');

//////////// JavaScript Stylesheet File ��ɬ���ɤ߹��ޤ���
$uniq = uniqid('target');

//////////// ���å����Υ��󥹥��󥹤�����
$session = new Session();

/////////////// �����Ϥ��ѿ��ν����
if ( isset($_SESSION['paya_parts_no']) ) {
    $parts_no = $_SESSION['paya_parts_no'];
} else {
    $parts_no = '';              // �����
    $_SESSION['paya_parts_no'] = $parts_no;
}
if ( isset($_SESSION['payable_div']) ) {
    $div = $_SESSION['payable_div'];
} else {
    $div = '';
    $_SESSION['payable_div'] = $div;
}
if ( isset($_SESSION['paya_vendor']) ) {
    $vendor = $_SESSION['paya_vendor'];
} else {
    $vendor = '';
    $_SESSION['paya_vendor'] = $vendor;
}
if ( isset($_SESSION['paya_strdate']) ) {
    $str_date = $_SESSION['paya_strdate'];
} else {
    $str_date = '';     // �����
    $_SESSION['paya_strdate'] = $str_date;
    /*************************************
    $year  = date('Y');
    $month = date('m') - 1; // ������������
    if ($month == 0) {
        $month = 12;
        $year -= 1;
    } else {
        $month = sprintf('%02d', $month);
    }
    $year  = date('Y') - 5; // ��ǯ������򥳥���
    $month = date('m');
    $str_date = $year . $month . '01';  // ����� (����Σ���������ѹ�)
    $session->add_local('paya_strdate', $str_date);
    *************************************/
}
if ( isset($_SESSION['paya_enddate']) ) {
    $end_date = $_SESSION['paya_enddate'];
} else {
    $end_date = '';     // �����
    $_SESSION['paya_enddate'] = $end_date;
}

if ( isset($_SESSION['payable_page']) ) {   // ���ڡ���ɽ���Կ�����
    $paya_page = $_SESSION['payable_page'];
} else {
    $paya_page = 25;             // Default 25
    $_SESSION['payable_page'] = $paya_page;
}

/////////// summary_view ���ɲä��줿�ѥ�᡼����
if ( isset($_SESSION['payable_s_ym']) ) {
    $s_ym = $_SESSION['payable_s_ym'];
} else {
    $s_ym = '';
}
if ( isset($_SESSION['payable_e_ym']) ) {
    $e_ym = $_SESSION['payable_e_ym'];
} else {
    $e_ym = '';
}
if ( isset($_SESSION['payable_div']) ) {
    $sum_div = $_SESSION['payable_div'];
} else {
    $sum_div = ' ';
}

/////////// summary_view �������줿
if (isset($_REQUEST['summary_view'])) {
    if (isset($_REQUEST['s_ym'])) {
        $_SESSION['payable_s_ym'] = $_REQUEST['s_ym'];
    }
    if (isset($_REQUEST['e_ym'])) {
        $_SESSION['payable_e_ym'] = $_REQUEST['e_ym'];
    }
    if (isset($_REQUEST['sum_div'])) {
        $_SESSION['payable_div'] = $_REQUEST['sum_div'];
    }
    header('Location: ' . H_WEB_HOST . $menu->out_action('Ǽ���٤콸��ɽ'));
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
<script language='JavaScript' src='./delivery_late_form.js?<?= $uniq ?>'>
</script>

<script language="JavaScript">
<!--
/* ������ϥե�����Υ�����Ȥ˥ե������������� */
function set_focus(){
    document.payable_form.parts_no.focus();
    document.payable_form.parts_no.select();
}
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
    background-color:   yellow;
    color:              blue;
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
.caption_font {
    font-size:          11pt;
    font-weight:        bold;
    font-family:        monospace;
    background-color:   blue;
    color:              white;
}
-->
</style>
</head>
</style>
<body onLoad='set_focus()' style='overflow-y:hidden;'>
    <center>
<?=$menu->out_title_border()?>
        
        <form name='payable_form' action='<?=$menu->out_action('Ǽ���٤�����ɽ��')?>' method='get' onSubmit='return chk_payable_form(this)'>
            <!----------------- ������ ��ʸ��ɽ������ ------------------->
            <table bgcolor='#d6d3ce' border='1' cellspacing='0' cellpadding='3'>
                <tr><td> <!----------- ���ߡ�(�ǥ�������) ------------>
            <table width='100%' class='winbox_field' bgcolor='#d6d3ce' border='1' cellspacing='0' cellpadding='3'>
                <tr>
                        <!--  bgcolor='#ffffc6' �������� --> 
                    <td class='winbox' bgcolor='yellow' colspan='2' align='center'>
                        <div class='caption_font'><?=$menu->out_caption(), "\n"?></div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' align='right'>
                        �����ֹ�λ���(���ꤷ�ʤ����ϲ����������ꤷ�Ʋ�����)
                    </td>
                    <td class='winbox' align='center'>
                        <input type='text' name='parts_no' class='pt12b' size='9' value='<?= $parts_no ?>' maxlength='9'>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' align='right'>
                        ��������򤷤Ʋ�����(���ʤ���ꤷ������̵�뤵��ޤ�)
                    </td>
                    <td class='winbox' align='center'>
                        <select name='div' style='font-size:11pt; font-weight:bold; font-family:monospace;'>
                            <option value=' '<?php if($div==' ') echo 'selected' ?>>������</option>
                            <option value='C'<?php if($div=='C') echo 'selected' ?>>���ץ�����</option>
                            <option value='SC'<?php if($div=='SC') echo 'selected' ?>>���ץ�����</option>
                            <option value='CS'<?php if($div=='CS') echo 'selected' ?>>���ץ�ɸ��</option>
                            <option value='L'<?php if($div=='L') echo 'selected' ?>>��˥�����</option>
                            <!-- <option value='LN'<?php if($div=='LN') echo 'selected' ?>>��˥��Τ�</option> -->
                            <!-- <option value='B'<?php if($div=='B') echo 'selected' ?>>�Х����</option> -->
                            <!-- <option value='T'<?php if($div=='T') echo 'selected' ?>>�ġ���¾</option> -->
                        </select>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' align='right'>
                        ȯ����Υ����ɤ����(���ʤ���ꤷ������̵�뤵��ޤ�)
                    </td>
                    <td class='winbox' align='center'>
                        <input type='text' name='vendor' class='pt12b' size='5' value='<?= $vendor ?>' maxlength='5'>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' align='right'>
                        Ǽ������ꤷ�Ʋ�����(ɬ��)
                    </td>
                    <td class='winbox' align='center'>
                        <input type='text' name='str_date' size='9' value='<?php echo($str_date); ?>' maxlength='8' style='font-size:11pt; font-weight:bold; font-family:monospace;'>
                        ��
                        <input type='text' name='end_date' size='9' value='<?php echo($end_date); ?>' maxlength='8' style='font-size:11pt; font-weight:bold; font-family:monospace;'>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' align='right'>
                        ���ڡ�����ɽ���Կ�����ꤷ�Ʋ�����
                    </td>
                    <td class='winbox' align='center'>
                        <input type='text' name='paya_page' size='4' value='<?= $paya_page ?>' maxlength='3' style='font-size:11pt; font-weight:bold; font-family:monospace;'>
                        ����͡�25
                    </td>
                </tr>
                <tr>
                    <td class='winbox' colspan='2' align='center'>
                        <input type='submit' name='paya_view' value='�¹�' >
                    </td>
                </tr>
            </table>
                </td></tr>
            </table> <!----------------- ���ߡ�End ------------------>
        </form>
        
        <hr>
        
        <form name='summary_form' action='<?=$menu->out_self()?>' method='get'>
            <table bgcolor='#d6d3ce' border='1' cellspacing='0' cellpadding='3'>
                <tr><td> <!----------- ���ߡ�(�ǥ�������) ------------>
            <table width='100%' class='winbox_field' bgcolor='#d6d3ce' border='1' cellspacing='0' cellpadding='3'>
                <tr>
                    <td class='winbox' bgcolor='yellow' colspan='2' align='center'>
                        <div class='caption_font'>���Ϲ�����ι�׶�ۤξȲ�</div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' align='right'>
                        ����ǯ������
                    </td>
                    <td class='winbox' align='center'>
                        <select name='s_ym' class='pt11b'>
                            <?php
                            $ym = date('Ym');
                            while(1) {
                                if ($s_ym == $ym) {
                                    printf("<option value='%d' selected>%sǯ%s��</option>\n",$ym,substr($ym,0,4),substr($ym,4,2));
                                    $init_flg = 0;
                                } else {
                                    printf("<option value='%d'>%sǯ%s��</option>\n",$ym,substr($ym,0,4),substr($ym,4,2));
                                }
                                if (substr($ym, 4, 2) != '01') {
                                    $ym--;
                                } else {
                                    $ym = $ym - 100;
                                    $ym = $ym + 11;
                                }
                                if ($ym <= 200010) {
                                    break;
                                }
                            }
                            ?>
                        </select>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' align='right'>
                        ��λǯ������
                    </td>
                    <td class='winbox' align='center'>
                        <select name='e_ym' class='pt11b'>
                            <?php
                            $ym = date('Ym');
                            while(1) {
                                if ($e_ym == $ym) {
                                    printf("<option value='%d' selected>%sǯ%s��</option>\n",$ym,substr($ym,0,4),substr($ym,4,2));
                                    $init_flg = 0;
                                } else {
                                    printf("<option value='%d'>%sǯ%s��</option>\n",$ym,substr($ym,0,4),substr($ym,4,2));
                                }
                                if ($ym <= 200010) {
                                    break;
                                }
                                if (substr($ym, 4, 2) != '01') {
                                    $ym--;
                                } else {
                                    $ym = $ym - 100;
                                    $ym = $ym + 11;
                                }
                            }
                            ?>
                        </select>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' align='right'>
                        �о�����λ���
                    </td>
                    <td class='winbox' align='center'>
                        <select name='sum_div' style='font-size:11pt; font-weight:bold; font-family:monospace;'>
                            <option value=' '<?php if($sum_div==' ') echo 'selected' ?>>������</option>
                            <option value='C'<?php if($sum_div=='C') echo 'selected' ?>>���ץ�����</option>
                            <option value='CS'<?php if($sum_div=='CS') echo 'selected' ?>>���ץ�ɸ��</option>
                            <option value='SC'<?php if($sum_div=='SC') echo 'selected' ?>>���ץ�����</option>
                            <option value='L'<?php if($sum_div=='L') echo 'selected' ?>>��˥�����</option>
                            <!-- <option value='LN'<?php if($sum_div=='LN') echo 'selected' ?>>��˥��Τ�</option> -->
                            <!-- <option value='B'<?php if($sum_div=='B') echo 'selected' ?>>�Х����</option> -->
                            <!-- <option value='T'<?php if($sum_div=='T') echo 'selected' ?>>�ġ���¾</option> -->
                        </select>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' colspan='2' align='center'>
                        <input type='submit' name='summary_view' value='�¹�' >
                    </td>
                </tr>
            </table>
                </td></tr>
            </table> <!----------------- ���ߡ�End ------------------>
        </form>
    </center>
</body>
<?=$menu->out_alert_java()?>
</html>
<?php
ob_end_flush();                 // ���ϥХåե���gzip���� END
?>
