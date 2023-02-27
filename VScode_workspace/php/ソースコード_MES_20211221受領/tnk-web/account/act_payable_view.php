<?php
//////////////////////////////////////////////////////////////////////////////
// ��ݥҥ��ȥ�ξȲ� �� �����å���  ������ UKWLIB/W#HIBCTR                 //
// Copyright (C) 2003-2005 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp      //
// Changed history                                                          //
// 2003/11/18 Created   act_payable_view.php                                //
// 2003/11/19 ��ư������ǧ�ꥹ�Ȥ��͹礻��������ͤ˰ʲ��Υ��å����ɲ�    //
//            ������(1)�����ʻųݣ�(2-5) ����(6)- �ι�׶�� ���������     //
//            ��˥��θ�����1 �����                                        //
// 2004/05/12 �����ȥ�˥塼ɽ������ɽ�� �ܥ����ɲ� menu_OnOff($script)�ɲ� //
// 2005/02/15 MenuHeader class ����Ѥ��ƶ��̥�˥塼���ڤ�ǧ���������ѹ�   //
// 2005/08/20 set_focus()�ε�ǽ�� MenuHeader �Ǽ������Ƥ���Τ�̵��������   //
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
$menu->set_site(20, 10);                    // site_index=20(������˥塼) site_id=10(��ݶ����������å��ꥹ��)
////////////// �꥿���󥢥ɥ쥹����
// $menu->set_RetUrl(SYS_MENU);                // �̾�ϻ��ꤹ��ɬ�פϤʤ�
//////////// �����ȥ�̾(�������Υ����ȥ�̾�ȥե�����Υ����ȥ�̾)
$menu->set_title('��ݶ�׾���� �����å��ꥹ��');
//////////// ɽ�������
// $menu->set_caption('����ץ�ǥ����ƥ�ޥ�������ɽ�����Ƥ��ޤ�');
//////////// �ƽ����action̾�ȥ��ɥ쥹����
// $menu->set_action('test_template',   SYS . 'log_view/php_error_log.php');

//////////// JavaScript Stylesheet File ��ɬ���ɤ߹��ޤ���
$uniq = uniqid('target');

//////////// �о�ǯ���������
$act_ymd = $_SESSION['act_ymd'];
if ($act_ymd == '') {
    $act_ymd = date_offset(2);
}

//////////// ���ǤιԿ�
define('PAGE', '22');

//////////// SQL ʸ�� where ��� ���Ѥ���
$search = sprintf('where act_date=%d', $act_ymd);

//////////// ��ץ쥳���ɿ�����     (�оݥǡ����κ������ڡ�������˻���)
$query = sprintf('select count(*) from act_payable %s', $search);
if ( getUniResult($query, $maxrows) <= 0) {         // $maxrows �μ���
    $_SESSION['s_sysmsg'] .= "��ץ쥳���ɿ��μ����˼���";      // .= ��å��������ɲä���
}

//////////// SQL ʸ�� where ��� ���Ѥ��� 01111=�������칩�� 00222=��¤������ 99999=����
$search_kin = sprintf("where act_date=%d and vendor !='01111' and vendor !='00222' and vendor !='99999'", $act_ymd);

//////////// ���������׶��
$query = sprintf("select sum(Uround(order_price * siharai,0)) from act_payable %s", $search_kin);
if ( getUniResult($query, $sum_kin) <= 0) {
    $_SESSION['s_sysmsg'] .= '��׶�ۤμ����˼���';      // .= ��å��������ɲä���
}

//////////// ���������׶�� ����1
$query = sprintf("select sum(Uround(order_price * siharai,0)) from act_payable %s and kamoku=1", $search_kin);
getUniResult($query, $kamoku1_kin);

//////////// ���������׶�� ����1�ǥ�˥�  ��������2-5�ؿ�ʬ����
$query = sprintf("select sum(Uround(order_price * siharai,0)) from act_payable %s and kamoku=1 and div='L'", $search_kin);
getUniResult($query, $kamoku1L_kin);
$kamoku1_kin = ($kamoku1_kin - $kamoku1L_kin);

//////////// ���������׶�� ����2-5
$query = sprintf("select sum(Uround(order_price * siharai,0)) from act_payable %s 
                  and kamoku>=2 and kamoku<=5", $search_kin);
getUniResult($query, $kamoku2_5_kin);
$kamoku2_5_kin = ($kamoku2_5_kin + $kamoku1L_kin);

//////////// ���������׶�� ����6�ʾ�
$query = sprintf("select sum(Uround(order_price * siharai,0)) from act_payable %s and kamoku>=6", $search_kin);
getUniResult($query, $kamoku6__kin);

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

//////////// ɽ�����Υǡ���ɽ���ѤΥ���ץ� Query & �����
$query = sprintf("
        select
            act_date    as ������,
            type_no     as \"T\",
            uke_no      as �����ֹ�,
            uke_date    as ������,
            ken_date    as ������,
            vendor      as ȯ����,
            name        as ȯ����̾,
            parts_no    as �����ֹ�,
            order_no    as ��ʸ�ֹ�,
            koutei      as ��������,
            mtl_cond    as ���,
            order_price as ȯ��ñ��,
            genpin      as ���ʿ�,
            siharai     as ��ʧ��,
            Uround(order_price * siharai,0) as ȯ����,
            div         as ������,
            kamoku      as ����,
            sei_no      as ��¤�ֹ�
        from
            act_payable left outer join vendor_master using(vendor)
        %s 
        ORDER BY vendor, uke_no, type_no, seq ASC
        offset %d limit %d
    ", $search, $offset, PAGE);       // ���� $search �Ǹ���
$res   = array();
$field = array();
if (($rows = getResultWithField2($query, $field, $res)) <= 0) {
    $_SESSION['s_sysmsg'] .= sprintf("��ݶ�η׾���:%s ��<br>�ǡ���������ޤ���", format_date($act_ymd) );
    header('Location: ' . H_WEB_HOST . $menu->out_RetUrl());    // ľ���θƽи������
    exit();
} else {
    $num = count($field);       // �ե�����ɿ�����
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
function set_focus(){
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
.pt9 {
    font-size:      9pt;
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
th {
    background-color:   yellow;
    color:              blue;
    font-size:          10pt;
    font-weight:        bold;
    font-family:        monospace;
}
-->
</style>
</head>
<body onLoad='set_focus()' style='overflow-y:hidden;'>
    <center>
<?=$menu->out_title_border()?>
        
        <table width='250' border='0' cellspacing='0' cellpadding='0'>
            <tr>
                <td nowrap align='left' class='pt10b'>
                    �硡�ס��⡡��
                </td>
                <td nowrap align='right' class='pt11b'>
                    <?= number_format($sum_kin) . "\n" ?>
                </td>
            </tr>
            <tr>
                <td nowrap align='left' class='pt10b'>
                    ���ʻųݣ�2��5
                </td>
                <td nowrap align='right' class='pt11b'>
                    <?= number_format($kamoku2_5_kin) . "\n" ?>
                </td>
            </tr>
            <tr>
                <td nowrap align='left' class='pt10b'>
                    �� �� �� 1
                </td>
                <td nowrap align='right' class='pt11b'>
                    <?= number_format($kamoku1_kin) . "\n" ?>
                </td>
            </tr>
            <tr>
                <td nowrap align='left' class='pt10b'>
                    ��ݲ��� 6��
                </td>
                <td nowrap align='right' class='pt11b'>
                    <?= number_format($kamoku6__kin) . "\n" ?>
                </td>
            </tr>
        </table>
        
        <!----------------- ������ ���� ���� �Υե����� ---------------->
        <table width='100%' border='0' cellspacing='0' cellpadding='0'>
            <form name='page_form' method='post' action='<?= $menu->out_self() ?>'>
                <tr>
                    <td align='left'>
                        <table align='left' border='3' cellspacing='0' cellpadding='0'>
                            <td align='left'>
                                <input class='pt10b' type='submit' name='backward' value='����'>
                            </td>
                        </table>
                    </td>
                    <td nowrap align='center' class='pt11b'>
                        <?= format_date($act_ymd) . '��' . $menu->out_title() . "\n" ?>
                    </td>
                    <td align='right'>
                        <table align='right' border='3' cellspacing='0' cellpadding='0'>
                            <td align='right'>
                                <input class='pt10b' type='submit' name='forward' value='����'>
                            </td>
                        </table>
                    </td>
                </tr>
            </form>
        </table>
        
        <!--------------- ����������ʸ��ɽ��ɽ������ -------------------->
        <table bgcolor='#d6d3ce' align='center' border='1' cellspacing='0' cellpadding='3'>
            <tr><td> <!----------- ���ߡ�(�ǥ�������) ------------>
        <table width='100%'class='winbox_field' bgcolor='#d6d3ce' align='center' border='1' cellspacing='0' cellpadding='3'>
            <THEAD>
                <!-- �ơ��֥� �إå�����ɽ�� -->
                <tr>
                    <th class='winbox' nowrap width='10'>No</th>        <!-- �ԥʥ�С���ɽ�� -->
                <?php
                for ($i=0; $i<$num; $i++) {             // �ե�����ɿ�ʬ���֤�
                ?>
                    <th class='winbox' nowrap><?= $field[$i] ?></th>
                <?php
                }
                ?>
                </tr>
            </THEAD>
            <TFOOT>
                <!-- ���ߤϥեå����ϲ���ʤ� -->
            </TFOOT>
            <TBODY>
                        <!--  bgcolor='#ffffc6' �������� -->
                        <!-- ����ץ�<td rowspan='2' colspan='3' width='200' align='center' class='pt10b' bgcolor='#ffffc6'>  </td> -->
                <?php
                for ($r=0; $r<$rows; $r++) {
                ?>
                    <tr>
                        <td class='winbox' nowrap align='right'><span class='pt10b'><?= ($r + $offset + 1) ?></span></td>    <!-- �ԥʥ�С���ɽ�� -->
                    <?php
                    for ($i=0; $i<$num; $i++) {         // �쥳���ɿ�ʬ���֤�
                        switch ($i) {
                        case 6:
                            echo "<td class='winbox' nowrap align='left'><span class='pt9'>{$res[$r][$i]}</span></td>\n";
                            break;
                        case 11:
                        case 12:
                        case 13:
                            echo "<td class='winbox' nowrap align='right'><span class='pt9'>" . number_format($res[$r][$i], 2) . "</span></td>\n";
                            break;
                        case 14:
                            echo "<td class='winbox' nowrap align='right'><span class='pt9'>" . number_format($res[$r][$i]) . "</span></td>\n";
                            break;
                        default:
                            echo "<td class='winbox' nowrap align='center'><span class='pt9'>{$res[$r][$i]}</span></td>\n";
                        }
                    }
                    ?>
                    </tr>
                <?php
                }
                ?>
            </TBODY>
        </table>
            </td></tr>
        </table> <!----------------- ���ߡ�End ------------------>
    </center>
</body>
<?=$menu->out_alert_java()?>
</html>
<?php
ob_end_flush();     // gzip���� END
?>
