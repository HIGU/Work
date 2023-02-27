<?php
//////////////////////////////////////////////////////////////////////////////
// Ǽ���٤����ʤξȲ� ���Ϲ�����ι�׶�ۤΥ��ޥ꡼�Ȳ�(ǯ��ˤ����ֻ���)//
// Copyright (C) 2011-     Norihisa.Ohya norihisa_ooya@nitto-kohki.co.jp    //
// Changed history                                                          //
// 2011/11/09 Created delivery_late_summary.php                             //
//////////////////////////////////////////////////////////////////////////////
ini_set('error_reporting', E_ALL);          // E_ALL='2047' debug ��
// ini_set('display_errors', '1');             // Error ɽ�� ON debug �� ��꡼���女����
// ini_set('implicit_flush', 'off');           // echo print �� flush �����ʤ�(�٤��ʤ뤿��) CLI CGI��
// ini_set('max_execution_time', 1200);        // ����¹Ի���=20ʬ CLI CGI��
ob_start('ob_gzhandler');                   // ���ϥХåե���gzip����
session_start();                            // ini_set()�μ��˻��ꤹ�뤳�� Script �Ǿ��

require_once ('../../../function.php');        // define.php �� pgsql.php �� require_once ���Ƥ���
require_once ('../../../tnk_func.php');        // TNK �˰�¸������ʬ�δؿ��� require_once ���Ƥ���
require_once ('../../../MenuHeader.php');      // TNK ������ menu class
access_log();                               // Script Name �ϼ�ư����

///// TNK ���ѥ�˥塼���饹�Υ��󥹥��󥹤����
$menu = new MenuHeader(0);                  // ǧ�ڥ����å�0=���̰ʾ� �����=TOP_MENU �����ȥ�̤����

////////////// ����������
$menu->set_site(30, 52);                    // site_index=30(������˥塼) site_id=52(Ǽ���٤����ʾȲ�Υ��롼��)
////////////// �꥿���󥢥ɥ쥹����
// $menu->set_RetUrl(INDUST_MENU);             // �̾�ϻ��ꤹ��ɬ�פϤʤ�
//////////// �����ȥ�̾(�������Υ����ȥ�̾�ȥե�����Υ����ȥ�̾)
$menu->set_title('Ǽ���٤����� �� �� ��');
//////////// �ƽ����action̾�ȥ��ɥ쥹����
// $menu->set_action('�����������',   INDUST . 'material/materialCost_view.php');
$menu->set_action('Ǽ���٤�����ɽ��',   INDUST . 'order/delivery_late/delivery_late_view.php');

//////////// ���ǤιԿ�
define('PAGE', '200');

//////////// JavaScript Stylesheet File ��ɬ���ɤ߹��ޤ���
$uniq = uniqid('target');

//////////// �о�ǯ������ (ǯ���ǯ������)
if ( isset($_SESSION['payable_s_ym']) && isset($_SESSION['payable_e_ym']) ) {
    $s_ymd = $_SESSION['payable_s_ym'] . '01';   // ������
    // $e_ymd = $_SESSION['payable_e_ym'] . '99';   // ��λ��
    $e_ym = $_SESSION['payable_e_ym'] + 1;   // ���η�����
    $Y4 = substr($e_ym, 0, 4);
    $M2 = substr($e_ym, 4, 2);
    if ($M2 > 12) {
        $Y4 += 1;
        $M2  = 1;
    }
    $e_ymd = date('Ymd', (mktime(0, 0, 0, $M2, 1, $Y4) - 1));   // ��λǯ����
    $_SESSION['test_date'] = $e_ymd;
} else {
    $_SESSION['s_sysmsg'] = '�о�ǯ����ꤵ��Ƥ��ޤ���';
    header('Location: ' . H_WEB_HOST . $menu->out_RetUrl());
    exit();
}

//////////// �о� ������
if ( isset($_SESSION['payable_div']) ) {
    $paya_div = $_SESSION['payable_div'];
} else {
    $_SESSION['s_sysmsg'] = '�о����ʥ��롼�פ����ꤵ��Ƥ��ޤ���';
    header('Location: ' . H_WEB_HOST . $menu->out_RetUrl());
    exit();
}

/////////// begin �ȥ�󥶥�����󳫻�
if ($con = funcConnect()) {
    query_affected_trans($con, 'begin');
} else {
    $_SESSION['s_sysmsg'] .= 'funcConnect() error';
    header('Location: ' . H_WEB_HOST . $menu->out_RetUrl());
    exit();
}

//////////// SQL ʸ�� where ��� ���Ѥ��� 01111=�������칩�� 00222=��¤������ 99999=����
$search = "where proc.delivery <= {$e_ymd} and proc.delivery >= {$s_ymd} and uke_date >= 0 and uke_date > proc.delivery and data.sei_no > 0 and (data.order_q - data.cut_genpin) > 0";

//////////// SQL ʸ�� where ��� ���Ѥ���
switch ($paya_div) {
case ' ';   // ����
    $search_kin = $search . " and (data.parts_no like 'C%' or data.parts_no like 'L%' or data.parts_no like 'T%' or data.parts_no like 'F%') and proc.locate != '52   '";
    $caption_div = '���硧����(������)';
    break;
case 'C';   // ���ץ� ����
    $search_kin = $search . " and data.parts_no like 'C%' and proc.locate != '52   '";
    $caption_div = '���硧���ץ�����(������)';
    break;
case 'CS';  // ���ץ� ɸ��
    $search_kin = $search . " and data.parts_no like 'C%' and data.kouji_no not like '%SC%' and proc.locate != '52   '";
    $caption_div = '���硧���ץ�ɸ��(������)';
    break;
case 'SC';  // ���ץ� ����
    $search_kin = $search . " and data.parts_no like 'C%' and data.kouji_no like '%SC%' and proc.locate != '52   '";
    $caption_div = '���硧���ץ�����(������)';
    break;
case 'L';   // ��˥� ����
    $search_kin = $search . " and data.parts_no like 'L%' and proc.locate != '52   '";
    $caption_div = '���硧��˥�����(������)';
    break;
case 'LN';  // ��˥� �Τ�
    $search_kin = $search . " and (data.parts_no like 'L%' and data.parts_no NOT like 'LC%%' AND data.parts_no NOT like 'LR%%') and proc.locate != '52   '";
    $caption_div = '���硧��˥��Τ�(������)';
    break;
case 'B';   // �Х����
    $search_kin = $search . " and (data.parts_no like 'LC%%' OR data.parts_no like 'LR%%') and proc.locate != '52   '";
    $caption_div = '���硧�Х����(������)';
    break;
case 'T';   // �ġ���¾
    $search_kin = $search . " and (data.parts_no NOT like 'C%%' AND data.parts_no NOT like 'L%%') and proc.locate != '52   '";
    $caption_div = '���硧�ġ���¾(������)';
    break;
}
//////////// ���������׶�� (����1��5)����6�ʾ�����
$query = sprintf("select count(*) 
                    from order_data      AS data
                    left outer join
                        order_process   AS proc
                                        using(sei_no, order_no, vendor)
                    LEFT OUTER JOIN
                        order_plan      AS plan     USING (sei_no)
                    left outer join
                        vendor_master   AS mast
                                        on(data.vendor = mast.vendor)
                    left outer join
                        miitem          AS item
                                        on(data.parts_no = item.mipn)
                    %s
                    ", $search_kin);
if (getUniResTrs($con, $query, $maxrows) <= 0) {
    $_SESSION['s_sysmsg'] .= sprintf("Ǽ��:%s��%s��<br>�ǡ���������ޤ���", $s_ymd, $e_ymd );
    $_SESSION['s_sysmsg'] .= 'Ǽ���٤����μ���������ޤ���';      // .= ��å��������ɲä���
    query_affected_trans($con, 'rollback');         // transaction rollback
    header('Location: ' . H_WEB_HOST . $menu->out_RetUrl());
    exit();
} else {
    // $sum_kin = $paya_ctoku[0][0];
    // $maxrows = $paya_ctoku[0][1];    // GROUP BY �λ��Ͻ���ؿ��ϻȤ��ʤ�
}

$query = sprintf("select
                        sum(Uround(data.order_q * data.order_price,0))
                    from
                            order_data      AS data
                        left outer join
                            order_process   AS proc
                                        using(sei_no, order_no, vendor)
                        LEFT OUTER JOIN
                            order_plan      AS plan     USING (sei_no)
                        left outer join
                            vendor_master   AS mast
                                        on(data.vendor = mast.vendor)
                        left outer join
                            miitem          AS item
                                        on(data.parts_no = item.mipn)
                    %s
                    ", $search_kin);
if (getUniResTrs($con, $query, $sum_kin) <= 0) {
    $_SESSION['s_sysmsg'] .= 'Ǽ���٤��׶�ۤμ���������ޤ���';      // .= ��å��������ɲä���
    query_affected_trans($con, 'rollback');         // transaction rollback
    header('Location: ' . H_WEB_HOST . $menu->out_RetUrl());
    exit();
}

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
            data.vendor              as ȯ����,                            -- 00
            mast.name                as ȯ����̾,                          -- 01
            count(*)                 as Ǽ���٤���,                      -- 02
            sum(Uround(data.order_q * data.order_price,0)) as Ǽ���٤��� -- 03
        from
                order_data      AS data
            left outer join
                order_process   AS proc
                                        using(sei_no, order_no, vendor)
            LEFT OUTER JOIN
                order_plan      AS plan     USING (sei_no)
            left outer join
                vendor_master   AS mast
                                        on(data.vendor = mast.vendor)
            left outer join
                miitem          AS item
                                        on(data.parts_no = item.mipn)
        %s 
        GROUP BY data.vendor, mast.name
        ORDER BY Ǽ���٤��� DESC, Ǽ���٤��� DESC, data.vendor ASC
        offset %d limit %d
    ", $search_kin, $offset, PAGE);       // ���� $search �Ǹ���
$res   = array();
$field = array();
if (($rows = getResWithFieldTrs($con, $query, $field, $res)) <= 0) {
    $_SESSION['s_sysmsg'] .= sprintf("Ǽ��:%s��%s��<br>�ǡ���������ޤ���", $s_ymd, $e_ymd );
    query_affected_trans($con, 'rollback');         // transaction rollback
    header('Location: ' . H_WEB_HOST . $menu->out_RetUrl());
    exit();
} else {
    query_affected_trans($con, 'commit');         // transaction commit
    $num = count($field);       // �ե�����ɿ�����
}

//////////// ɽ�������
$caption = "ǯ�" . format_date($s_ymd) . '��' . format_date($e_ymd) . '����׶�ۡ�' . number_format($sum_kin) . '����׷����' . number_format($maxrows);
$menu->set_caption("{$caption_div}����{$caption}");

/////////// HTML Header ����Ϥ��ƥ���å��������
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
function set_focus(){
    // document.body.focus();   // F2/F12������ͭ���������б�
    document.mhForm.backwardStack.focus();  // �嵭��IE�ΤߤΤ���NN�б�
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
.pt9b {
    font-size:      9pt;
    font-weight:    bold;
    font-family:    monospace;
}
.pt10b {
    font:           10pt;
    font-weight:    bold;
    font-family:    monospace;
}
.pt11b {
    font:           11pt;
    font-weight:    bold;
    color:          blue;
}
th {
    background-color:   yellow;
    color:              blue;
    font:               10pt;
    font-weight:        bold;
    font-family:        monospace;
}
a:hover {
    background-color:   blue;
    color:              white;
}
a {
    color:              blue;
    text-decoration:    none;
}
-->
</style>
</head>
<body onLoad='set_focus()'>
    <center>
<?=$menu->out_title_border()?>
        
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
                        <?= $menu->out_caption(), "\n" ?>
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
        <table class='winbox_field' width='100%' bgcolor='#d6d3ce' align='center' border='1' cellspacing='0' cellpadding='3'>
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
                        <td class='winbox' nowrap align='right'><div class='pt10b'><?= ($r + $offset + 1) ?></div></td>    <!-- �ԥʥ�С���ɽ�� -->
                    <?php
                    for ($i=0; $i<$num; $i++) {         // �쥳���ɿ�ʬ���֤�
                        switch ($i) {
                        case 1:
                            echo "<td class='winbox' nowrap align='left'><div class='pt9'>{$res[$r][$i]}</div></td>\n";
                            break;
                        case 2:
                            echo "<td class='winbox' nowrap align='right'><div class='pt9b'><a href='", $menu->out_action('Ǽ���٤�����ɽ��'), "?str_date={$s_ymd}&end_date={$e_ymd}&parts_no=&div={$paya_div}&vendor=", urlencode("{$res[$r][0]}"), "'>", number_format($res[$r][$i]), "</a></div></td>\n";
                            break;
                        case 3:
                            echo "<td class='winbox' nowrap align='right'><div class='pt9'>", number_format($res[$r][$i]), "</div></td>\n";
                            break;
                        default:
                            echo "<td class='winbox' nowrap align='center'><div class='pt9'>{$res[$r][$i]}</div></td>\n";
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
ob_end_flush();     // ���ϥХåե�����gzip���� END
?>
