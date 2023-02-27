<?php
//////////////////////////////////////////////////////////////////////////////
// �����׾�ξȲ� �� �����å���  ������ (��ݶ� - ͭ���ٵ���)           //
// Copyright (C) 2003-2013 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp      //
// Changed history                                                          //
// 2003/11/25 Created   act_purchase_view.php                               //
// 2003/11/19 ��ư������ǧ�ꥹ�Ȥ��͹礻��������ͤ˰ʲ��Υ��å����ɲ�    //
//            ������(1)�����ʻųݣ�(2-5) ����(6)- �ι�׶�� ���������     //
//            ��˥��θ�����1 �����                                        //
// 2003/12/09 �ơ��֥�� act_purchase_header ���ѹ����ƹ�®��               //
// 2004/01/14 $_SESSION['act_ym'] �� $_SESSION['ind_ym'] ���ѹ�             //
// 2004/05/12 �����ȥ�˥塼ɽ������ɽ�� �ܥ����ɲ� menu_OnOff($script)�ɲ� //
// 2005/02/09 MenuHeader class ����Ѥ��ƶ��̥�˥塼���ڤ�ǧ���������ѹ�   //
// 2005/02/27 ɽ�Υǥ������ѹ�bgcolor='black'cellspacing='1'��<table>���ɲ� //
// 2005/05/20 db_connect() �� funcConnect() ���ѹ� pgsql.php������Τ���    //
// 2005/08/20 set_focus()�ε�ǽ�� MenuHeader �Ǽ������Ƥ���Τ�̵��������   //
// 2013/01/28 �Х�������Υݥ�פ��ѹ� ɽ���Τߥǡ����ϥХ����Τޤ� ��ë//
// 2013/02/15 �������߷׶�ۤ��ɲ�                                      ��ë//
//////////////////////////////////////////////////////////////////////////////
ini_set('error_reporting', E_ALL);          // E_ALL='2047' debug ��
// ini_set('display_errors', '1');             // Error ɽ�� ON debug �� ��꡼���女����
// ini_set('zend.ze1_compatibility_mode', '1');    // zend 1.X ����ѥ� php4�θߴ��⡼��
// ini_set('implicit_flush', 'off');           // echo print �� flush �����ʤ�(�٤��ʤ뤿��) CLI��
// ini_set('max_execution_time', 1200);        // ����¹Ի���=20ʬ WEB CGI��
ob_start('ob_gzhandler');                   // ���ϥХåե���gzip����
session_start();                            // ini_set()�μ��˻��ꤹ�뤳�� Script �Ǿ��

require_once ('../function.php');           // define.php �� pgsql.php �� require_once ���Ƥ���
require_once ('../tnk_func.php');           // TNK �˰�¸������ʬ�δؿ��� require_once ���Ƥ���
require_once ('../MenuHeader.php');         // TNK ������ menu class
access_log();                               // Script Name �ϼ�ư����

///// TNK ���ѥ�˥塼���饹�Υ��󥹥��󥹤����
$menu = new MenuHeader(0);                  // ǧ�ڥ����å�0=���̰ʾ� �����=TOP_MENU �����ȥ�̤����

////////////// ����������
$menu->set_site(20, 31);                    // site_index=30(������˥塼) site_id=31(������۾Ȳ�)
////////////// �꥿���󥢥ɥ쥹����
// $menu->set_RetUrl(INDUST_MENU);             // �̾�ϻ��ꤹ��ɬ�פϤʤ�
//////////// �����ȥ�̾(�������Υ����ȥ�̾�ȥե�����Υ����ȥ�̾)
$menu->set_title('�����׾��� �Ȳ�');
//////////// �ƽ����action̾�ȥ��ɥ쥹����
// $menu->set_action('test_template',   SYS . 'log_view/php_error_log.php');

//////////// JavaScript Stylesheet File ��ɬ���ɤ߹��ޤ���
$uniq = uniqid('target');

//////////// �о�ǯ������ (ǯ��Τߤ����)
if ( isset($_SESSION['ind_ym']) ) {
    $act_ym = $_SESSION['ind_ym'];
    $s_ymd  = $act_ym . '01';   // ������
    $e_ymd  = $act_ym . '99';   // ��λ��
} else {
    $_SESSION['s_sysmsg'] = '��о�ǯ����ꤵ��Ƥ��ޤ���!';
    header('Location: ' . H_WEB_HOST . $menu->out_RetUrl());    // ľ���θƽи������
    exit();
}
//////////// ɽ�������
$menu->set_caption($act_ym . '��' . $menu->out_title());

$menu->set_caption2('�����߷ס�' . $menu->out_title());

//////////// ���ǤιԿ�
define('PAGE', '25');

/////////// begin �ȥ�󥶥�����󳫻�
if ($con = funcConnect()) {
    query_affected_trans($con, 'begin');
} else {
    $_SESSION['s_sysmsg'] .= 'funcConnect() error';
    exit();
}

$act_yy = substr($act_ym, 0, 4);
$act_mm = substr($act_ym, 4, 2);
if ($act_mm >= 4 && $act_mm < 13) {
    $act_mm = '04';
} else {
    $act_yy -= 1;
    $act_mm  = '04';
}

$str_ym = $act_yy . $act_mm;

//////////// �ǡ�����إå����ե����뤫���ɹ���
// ���� ñ��
$query = "select sum_payable, sum_provide, cnt_payable, cnt_provide
            from act_purchase_header
            where purchase_ym={$act_ym} and item='����'";
$res = array();     // �����
if ( getResultTrs($con, $query, $res) <= 0) {
    $_SESSION['s_sysmsg'] .= '���Τζ�ۤμ����˼���';      // .= ��å��������ɲä���
    query_affected_trans($con, 'rollback');         // transaction rollback
    header('Location: ' . H_WEB_HOST . $menu->out_RetUrl());    // ľ���θƽи������
    exit();
} else {
    $paya_sum_kin = $res[0][0];         // ���
    $prov_sum_kin = $res[0][1];         // ͭ���ٵ�
}
// ���� �����߷�
$query_t = "select sum(sum_payable), sum(sum_provide), sum(cnt_payable), sum(cnt_provide)
              from act_purchase_header
              where purchase_ym>={$str_ym} and purchase_ym<={$act_ym} and item='����'";
$res_t = array();     // �����
if ( getResultTrs($con, $query_t, $res_t) <= 0) {
    $_SESSION['s_sysmsg'] .= '���Τζ�ۤμ����˼���';      // .= ��å��������ɲä���
    query_affected_trans($con, 'rollback');         // transaction rollback
    header('Location: ' . H_WEB_HOST . $menu->out_RetUrl());    // ľ���θƽи������
    exit();
} else {
    $paya_sum_kin_t = $res_t[0][0];         // ���
    $prov_sum_kin_t = $res_t[0][1];         // ͭ���ٵ�
}
// ���ץ�
$query = "select sum_payable, sum_provide, cnt_payable, cnt_provide
            from act_purchase_header
            where purchase_ym={$act_ym} and item='���ץ�'";
$res = array();     // �����
if ( getResultTrs($con, $query, $res) <= 0) {
    $_SESSION['s_sysmsg'] .= '���ץ�ζ�ۤμ����˼���';      // .= ��å��������ɲä���
    query_affected_trans($con, 'rollback');         // transaction rollback
    header('Location: ' . H_WEB_HOST . $menu->out_RetUrl());    // ľ���θƽи������
    exit();
} else {
    $paya_c_kin = $res[0][0];         // ���
    $prov_c_kin = $res[0][1];         // ͭ���ٵ�
}
// ���ץ� �����߷�
$query_t = "select sum(sum_payable), sum(sum_provide), sum(cnt_payable), sum(cnt_provide)
              from act_purchase_header
              where purchase_ym>={$str_ym} and purchase_ym<={$act_ym} and item='���ץ�'";
$res_t = array();     // �����
if ( getResultTrs($con, $query_t, $res_t) <= 0) {
    $_SESSION['s_sysmsg'] .= '���Τζ�ۤμ����˼���';      // .= ��å��������ɲä���
    query_affected_trans($con, 'rollback');         // transaction rollback
    header('Location: ' . H_WEB_HOST . $menu->out_RetUrl());    // ľ���θƽи������
    exit();
} else {
    $paya_c_kin_t = $res_t[0][0];         // ���
    $prov_c_kin_t = $res_t[0][1];         // ͭ���ٵ�
}

// ���ץ�����
$query = "select sum_payable, sum_provide, cnt_payable, cnt_provide
            from act_purchase_header
            where purchase_ym={$act_ym} and item='���ץ�����'";
$res = array();     // �����
if ( getResultTrs($con, $query, $res) <= 0) {
    $_SESSION['s_sysmsg'] .= '���ץ�����ζ�ۤμ����˼���';      // .= ��å��������ɲä���
    query_affected_trans($con, 'rollback');         // transaction rollback
    header('Location: ' . H_WEB_HOST . $menu->out_RetUrl());    // ľ���θƽи������
    exit();
} else {
    $paya_c_toku_kin = $res[0][0];         // ���
    $prov_c_toku_kin = $res[0][1];         // ͭ���ٵ�
}
// ���ץ����� �����߷�
$query_t = "select sum(sum_payable), sum(sum_provide), sum(cnt_payable), sum(cnt_provide)
              from act_purchase_header
              where purchase_ym>={$str_ym} and purchase_ym<={$act_ym} and item='���ץ�����'";
$res_t = array();     // �����
if ( getResultTrs($con, $query_t, $res_t) <= 0) {
    $_SESSION['s_sysmsg'] .= '���Τζ�ۤμ����˼���';      // .= ��å��������ɲä���
    query_affected_trans($con, 'rollback');         // transaction rollback
    header('Location: ' . H_WEB_HOST . $menu->out_RetUrl());    // ľ���θƽи������
    exit();
} else {
    $paya_c_toku_kin_t = $res_t[0][0];         // ���
    $prov_c_toku_kin_t = $res_t[0][1];         // ͭ���ٵ�
}

// ��˥�
$query = "select sum_payable, sum_provide, cnt_payable, cnt_provide
            from act_purchase_header
            where purchase_ym={$act_ym} and item='��˥�'";
$res = array();     // �����
if ( getResultTrs($con, $query, $res) <= 0) {
    $_SESSION['s_sysmsg'] .= '��˥��ζ�ۤμ����˼���';      // .= ��å��������ɲä���
    query_affected_trans($con, 'rollback');         // transaction rollback
    header('Location: ' . H_WEB_HOST . $menu->out_RetUrl());    // ľ���θƽи������
    exit();
} else {
    $paya_l_kin = $res[0][0];         // ���
    $prov_l_kin = $res[0][1];         // ͭ���ٵ�
}
// ��˥� �����߷�
$query_t = "select sum(sum_payable), sum(sum_provide), sum(cnt_payable), sum(cnt_provide)
              from act_purchase_header
              where purchase_ym>={$str_ym} and purchase_ym<={$act_ym} and item='��˥�'";
$res_t = array();     // �����
if ( getResultTrs($con, $query_t, $res_t) <= 0) {
    $_SESSION['s_sysmsg'] .= '���Τζ�ۤμ����˼���';      // .= ��å��������ɲä���
    query_affected_trans($con, 'rollback');         // transaction rollback
    header('Location: ' . H_WEB_HOST . $menu->out_RetUrl());    // ľ���θƽи������
    exit();
} else {
    $paya_l_kin_t = $res_t[0][0];         // ���
    $prov_l_kin_t = $res_t[0][1];         // ͭ���ٵ�
}

// �Х����
$query = "select sum_payable, sum_provide, cnt_payable, cnt_provide
            from act_purchase_header
            where purchase_ym={$act_ym} and item='�Х����'";
$res = array();     // �����
if ( getResultTrs($con, $query, $res) <= 0) {
    $_SESSION['s_sysmsg'] .= '�Х����ζ�ۤμ����˼���';      // .= ��å��������ɲä���
    query_affected_trans($con, 'rollback');         // transaction rollback
    header('Location: ' . H_WEB_HOST . $menu->out_RetUrl());    // ľ���θƽи������
    exit();
} else {
    $paya_l_bimor_kin = $res[0][0];         // ���
    $prov_l_bimor_kin = $res[0][1];         // ͭ���ٵ�
}
// �Х���� �����߷�
$query_t = "select sum(sum_payable), sum(sum_provide), sum(cnt_payable), sum(cnt_provide)
              from act_purchase_header
              where purchase_ym>={$str_ym} and purchase_ym<={$act_ym} and item='�Х����'";
$res_t = array();     // �����
if ( getResultTrs($con, $query_t, $res_t) <= 0) {
    $_SESSION['s_sysmsg'] .= '���Τζ�ۤμ����˼���';      // .= ��å��������ɲä���
    query_affected_trans($con, 'rollback');         // transaction rollback
    header('Location: ' . H_WEB_HOST . $menu->out_RetUrl());    // ľ���θƽи������
    exit();
} else {
    $paya_l_bimor_kin_t = $res_t[0][0];         // ���
    $prov_l_bimor_kin_t = $res_t[0][1];         // ͭ���ٵ�
}

/////////// commit �ȥ�󥶥������λ
query_affected_trans($con, 'commit');

//////////// ������ۤη׻� (��ݶ� - ͭ���ٵ���)
// ����
$sum_kin         = ($paya_sum_kin - $prov_sum_kin);
// ���ץ�
$c_sum_kin       = ($paya_c_kin - $prov_c_kin);
// ���ץ�����
$c_toku_sum_kin  = ($paya_c_toku_kin - $prov_c_toku_kin);
// ��˥�
$l_sum_kin       = ($paya_l_kin - $prov_l_kin);
// ��˥�BIMOR
$l_bimor_sum_kin = ($paya_l_bimor_kin - $prov_l_bimor_kin);

// ���� �����߷�
$sum_kin_t         = ($paya_sum_kin_t - $prov_sum_kin_t);
// ���ץ� �����߷�
$c_sum_kin_t       = ($paya_c_kin_t - $prov_c_kin_t);
// ���ץ����� �����߷�
$c_toku_sum_kin_t  = ($paya_c_toku_kin_t - $prov_c_toku_kin_t);
// ��˥� �����߷�
$l_sum_kin_t       = ($paya_l_kin_t - $prov_l_kin_t);
// ��˥�BIMOR �����߷�
$l_bimor_sum_kin_t = ($paya_l_bimor_kin_t - $prov_l_bimor_kin_t);

//////////// �ڡ������ե��å�����
if ( isset($_POST['forward']) ) {                       // ���Ǥ������줿
    $_SESSION['offset'] += PAGE;
    if ($_SESSION['offset'] >= $maxrows) {
        $_SESSION['offset'] -= PAGE;
        if ($_SESSION['s_sysmsg'] == "") {
            $_SESSION['s_sysmsg'] .= "<font color='yellow'>���ǤϤ���ޤ���</font>";
        } else {
            $_SESSION['s_sysmsg'] .= "<br><font color='yellow'>���ǤϤ���ޤ���</font>";
        }
    }
} elseif ( isset($_POST['backward']) ) {                // ���Ǥ������줿
    $_SESSION['offset'] -= PAGE;
    if ($_SESSION['offset'] < 0) {
        $_SESSION['offset'] = 0;
        if ($_SESSION['s_sysmsg'] == "") {
            $_SESSION['s_sysmsg'] .= "<font color='yellow'>���ǤϤ���ޤ���</font>";
        } else {
            $_SESSION['s_sysmsg'] .= "<br><font color='yellow'>���ǤϤ���ޤ���</font>";
        }
    }
} elseif ( isset($_GET['page_keep']) ) {               // ���ߤΥڡ�����ݻ�����
    $offset = $_SESSION['offset'];
} else {
    $_SESSION['offset'] = 0;                            // ���ξ��ϣ��ǽ����
}
$offset = $_SESSION['offset'];

///////////// HTML Header ����Ϥ��ƥ���å��������
$menu->out_html_header();
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=EUC-JP">
<meta http-equiv="Content-Style-Type" content="text/css">
<title><?= $menu->out_title() ?></title>
<?=$menu->out_site_java()?>
<?=$menu->out_css()?>

<!--    �ե��������ξ��
<script language='JavaScript' src='template.js?<?= $uniq ?>'></script>
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
/* ������ϥե�����Υ�����Ȥ˥ե������������� */
function set_focus() {
    // document.body.focus();   // F2/F12������ͭ���������б�
    // document.mhForm.backwardStack.focus();  // �嵭��IE�ΤߤΤ���NN�б�
}
// -->
</script>

<!-- �������륷���ȤΥե��������򥳥��� HTML���� �����Ȥ�����Ҥ˽���ʤ��������
<link rel='stylesheet' href='template.css?<?= $uniq ?>' type='text/css' media='screen'>
-->

<style type="text/css">
<!--
.pt10b {
    font-size:      11pt;
    font-weight:    bold;
    font-family:    monospace;
    color:          teal;
}
.pt11b {
    font-size:      12pt;
    font-weight:    bold;
    font-family:    monospace;
}
th {
    background-color:   yellow;
    color:              blue;
    font:bold           12pt;
    font-family:        monospace;
}
-->
</style>
</head>
<body onLoad='set_focus()'>
    <center>
<?=$menu->out_title_border()?>
        
        <div class='pt10b'><?=$menu->out_caption()?></div>
        
        <!--------------- ����������ʸ��ɽ��ɽ������ -------------------->
        <table bgcolor='#d6d3ce' align='center' border='1' cellspacing='0' cellpadding='10'>
            <tr><td> <!----------- ���ߡ�(�ǥ�������) ------------>
        <table class='winbox_field' width='100%' align='center' bgcolor='black' border='1' cellspacing='1' cellpadding='15'>
            <THEAD>
                <!-- �ơ��֥� �إå�����ɽ�� -->
                <tr>
                    <th class='winbox' nowrap>�ࡡ��</th>
                    <th class='winbox' nowrap>��ݶ��</th>
                    <th class='winbox' nowrap>ͭ���ٵ�</th>
                    <th class='winbox' nowrap>�������</th>
                </tr>
            </THEAD>
            <TFOOT>
                <!-- ���ߤϥեå����ϲ���ʤ� -->
            </TFOOT>
            <TBODY>
                <tr>
                    <td class='winbox' nowrap align='right' bgcolor='#d6d3ce'>
                        <div class='pt10b'>��׶��(����1��5)</div>
                    </td>
                    <td class='winbox' nowrap align='right' bgcolor='#d6d3ce'>
                        <div class='pt11b'>��<?= number_format($paya_sum_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap align='right' bgcolor='#d6d3ce'>
                        <div class='pt11b'>��<?= number_format($prov_sum_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap align='right' bgcolor='#ffffc6'>
                        <div class='pt11b'>��<?= number_format($sum_kin) ?></div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap align='right' bgcolor='#d6d3ce'>
                        <div class='pt10b'>���ץ�(����1��5)</div>
                    </td>
                    <td class='winbox' nowrap align='right' bgcolor='#d6d3ce'>
                        <div class='pt11b'>��<?= number_format($paya_c_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap align='right' bgcolor='#d6d3ce'>
                        <div class='pt11b'>��<?= number_format($prov_c_kin) . "\n" ?></div>
                    </td>
                    <td class='winbox' nowrap align='right' bgcolor='#ffffc6'>
                        <div class='pt11b'>��<?= number_format($c_sum_kin) ?></div.
                    </td>
                <tr>
                    <td class='winbox' nowrap align='right' bgcolor='#d6d3ce'>
                        <div class='pt10b'>���ץ�����</div>
                    </td>
                    <td class='winbox' nowrap align='right' bgcolor='#d6d3ce'>
                        <div class='pt11b'>��<?= number_format($paya_c_toku_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap align='right' bgcolor='#d6d3ce'>
                        <div class='pt11b'>��<?= number_format($prov_c_toku_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap align='right' bgcolor='#ffffc6'>
                        <div class='pt11b'>��<?= number_format($c_toku_sum_kin) ?></div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap align='right' bgcolor='#d6d3ce'>
                        <div class='pt10b'>��˥�(����1��5)</div>
                    </td>
                    <td class='winbox' nowrap align='right' bgcolor='#d6d3ce'>
                        <div class='pt11b'>��<?= number_format($paya_l_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap align='right' bgcolor='#d6d3ce'>
                        <div class='pt11b'>��<?= number_format($prov_l_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap align='right' bgcolor='#ffffc6'>
                        <div class='pt11b'>��<?= number_format($l_sum_kin) ?></div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap align='right' bgcolor='#d6d3ce'>
                        <div class='pt10b'>��˥� ���Υݥ��</div>
                    </td>
                    <td class='winbox' nowrap align='right' bgcolor='#d6d3ce'>
                        <div class='pt11b'>��<?= number_format($paya_l_bimor_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap align='right' bgcolor='#d6d3ce'>
                        <div class='pt11b'>��<?= number_format($prov_l_bimor_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap align='right' bgcolor='#ffffc6'>
                        <div class='pt11b'>��<?= number_format($l_bimor_sum_kin) ?></div>
                    </td>
                </tr>
            </TBODY>
        </table>
            </td></tr>
        </table> <!----------------- ���ߡ�End ------------------>
        
        <BR><BR>
        
        <div class='pt10b'><?=$menu->out_caption2()?></div>
        
        <!--------------- ����������ʸ��ɽ��ɽ������ -------------------->
        <table bgcolor='#d6d3ce' align='center' border='1' cellspacing='0' cellpadding='10'>
            <tr><td> <!----------- ���ߡ�(�ǥ�������) ------------>
        <table class='winbox_field' width='100%' align='center' bgcolor='black' border='1' cellspacing='1' cellpadding='15'>
            <THEAD>
                <!-- �ơ��֥� �إå�����ɽ�� -->
                <tr>
                    <th class='winbox' nowrap>�ࡡ��</th>
                    <th class='winbox' nowrap>��ݶ��</th>
                    <th class='winbox' nowrap>ͭ���ٵ�</th>
                    <th class='winbox' nowrap>�������</th>
                </tr>
            </THEAD>
            <TFOOT>
                <!-- ���ߤϥեå����ϲ���ʤ� -->
            </TFOOT>
            <TBODY>
                <tr>
                    <td class='winbox' nowrap align='right' bgcolor='#d6d3ce'>
                        <div class='pt10b'>��׶��(����1��5)</div>
                    </td>
                    <td class='winbox' nowrap align='right' bgcolor='#d6d3ce'>
                        <div class='pt11b'>��<?= number_format($paya_sum_kin_t) ?></div>
                    </td>
                    <td class='winbox' nowrap align='right' bgcolor='#d6d3ce'>
                        <div class='pt11b'>��<?= number_format($prov_sum_kin_t) ?></div>
                    </td>
                    <td class='winbox' nowrap align='right' bgcolor='#ffffc6'>
                        <div class='pt11b'>��<?= number_format($sum_kin_t) ?></div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap align='right' bgcolor='#d6d3ce'>
                        <div class='pt10b'>���ץ�(����1��5)</div>
                    </td>
                    <td class='winbox' nowrap align='right' bgcolor='#d6d3ce'>
                        <div class='pt11b'>��<?= number_format($paya_c_kin_t) ?></div>
                    </td>
                    <td class='winbox' nowrap align='right' bgcolor='#d6d3ce'>
                        <div class='pt11b'>��<?= number_format($prov_c_kin_t) . "\n" ?></div>
                    </td>
                    <td class='winbox' nowrap align='right' bgcolor='#ffffc6'>
                        <div class='pt11b'>��<?= number_format($c_sum_kin_t) ?></div.
                    </td>
                <tr>
                    <td class='winbox' nowrap align='right' bgcolor='#d6d3ce'>
                        <div class='pt10b'>���ץ�����</div>
                    </td>
                    <td class='winbox' nowrap align='right' bgcolor='#d6d3ce'>
                        <div class='pt11b'>��<?= number_format($paya_c_toku_kin_t) ?></div>
                    </td>
                    <td class='winbox' nowrap align='right' bgcolor='#d6d3ce'>
                        <div class='pt11b'>��<?= number_format($prov_c_toku_kin_t) ?></div>
                    </td>
                    <td class='winbox' nowrap align='right' bgcolor='#ffffc6'>
                        <div class='pt11b'>��<?= number_format($c_toku_sum_kin_t) ?></div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap align='right' bgcolor='#d6d3ce'>
                        <div class='pt10b'>��˥�(����1��5)</div>
                    </td>
                    <td class='winbox' nowrap align='right' bgcolor='#d6d3ce'>
                        <div class='pt11b'>��<?= number_format($paya_l_kin_t) ?></div>
                    </td>
                    <td class='winbox' nowrap align='right' bgcolor='#d6d3ce'>
                        <div class='pt11b'>��<?= number_format($prov_l_kin_t) ?></div>
                    </td>
                    <td class='winbox' nowrap align='right' bgcolor='#ffffc6'>
                        <div class='pt11b'>��<?= number_format($l_sum_kin_t) ?></div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap align='right' bgcolor='#d6d3ce'>
                        <div class='pt10b'>��˥� ���Υݥ��</div>
                    </td>
                    <td class='winbox' nowrap align='right' bgcolor='#d6d3ce'>
                        <div class='pt11b'>��<?= number_format($paya_l_bimor_kin_t) ?></div>
                    </td>
                    <td class='winbox' nowrap align='right' bgcolor='#d6d3ce'>
                        <div class='pt11b'>��<?= number_format($prov_l_bimor_kin_t) ?></div>
                    </td>
                    <td class='winbox' nowrap align='right' bgcolor='#ffffc6'>
                        <div class='pt11b'>��<?= number_format($l_bimor_sum_kin_t) ?></div>
                    </td>
                </tr>
            </TBODY>
        </table>
            </td></tr>
        </table> <!----------------- ���ߡ�End ------------------>
    </center>
</body>
<?=$menu->out_alert_java()?>
</html>
<?php
ob_end_flush();             // ���ϥХåե�����gzip���� END
?>
