<?php
//////////////////////////////////////////////////////////////////////////////
// ��ݥҥ��ȥ�ξȲ� ���Ϲ�����ι�׶�ۤΥ��ޥ꡼�Ȳ�(ǯ��ˤ����ֻ���)//
// Copyright (C) 2005-2018 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp      //
// Changed history                                                          //
// 2005/03/28 Created                                                       //
//            payable_cstd_vendor_summary2.php��payable_vendor_summary.php  //
// 2005/05/20 db_connect() �� funcConnect() ���ѹ� pgsql.php������Τ���    //
// 2008/06/24 �ʾڰ���ˤ��ȯ������ɽ�����ɲ�                       ��ë //
// 2011/12/27 NKCT�ڤ�NKT�ξȲ���б�                                  ��ë //
// 2012/11/05 ���ɰ���ˤ���ʧ����ɽ�����ɲ�                         ��ë //
// 2013/04/09 ��ȯ���������ɽ�����ɲ�(���ܣ������ȶ���)               ��ë //
// 2015/05/21 �����������б�                                           ��ë //
// 2018/01/29 ���ץ�����ɸ����ɲ�                                   ��ë //
// 2018/06/29 ¿�����T���ʹ������б�                                  ��ë //
// 2020/12/21 ¿�����T���ʹ������б���λ                              ��ë //
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
$menu->set_site(30, 10);                    // site_index=30(������˥塼) site_id=10(��ݼ��ӾȲ�Υ��롼��)
////////////// �꥿���󥢥ɥ쥹����
// $menu->set_RetUrl(INDUST_MENU);             // �̾�ϻ��ꤹ��ɬ�פϤʤ�
//////////// �����ȥ�̾(�������Υ����ȥ�̾�ȥե�����Υ����ȥ�̾)
$menu->set_title('�� �� �� �� �� �� ��');
//////////// �ƽ����action̾�ȥ��ɥ쥹����
// $menu->set_action('�����������',   INDUST . 'material/materialCost_view.php');

$menu->set_action('��ݼ��ӾȲ�',   INDUST . 'payable/act_payable2_view.php');

//////////// ���ǤιԿ�
define('PAGE', '200');

//////////// JavaScript Stylesheet File ��ɬ���ɤ߹��ޤ���
$uniq = uniqid('target');

//////////// �о�ǯ������ (ǯ���ǯ������)
if ( isset($_SESSION['payable_s_ym']) && isset($_SESSION['payable_e_ym']) ) {
    $s_ym = $_SESSION['payable_s_ym'];
    $e_ym = $_SESSION['payable_e_ym'];
    $s_ymd = $_SESSION['payable_s_ym'] . '01';   // ������
    $e_ymd = $_SESSION['payable_e_ym'] + 1;   // ���η�����
    $Y4 = substr($e_ymd, 0, 4);
    $M2 = substr($e_ymd, 4, 2);
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
$search = sprintf("where act_date>=%d and act_date<=%d", $s_ymd, $e_ymd);

//////////// SQL ʸ�� where ��� ���Ѥ���
switch ($paya_div) {
case ' ';    // ����
    $search_kin = sprintf("%s and kamoku<=5", $search);
    $caption_div = '����(������)�����ڤӽ�����ޤ�';
    break;
case 'C';    // ���ץ� ����
    $search_kin = sprintf("%s and kamoku<=5 and paya.div='C'", $search);
    $caption_div = '���ץ�����(������)�����ڤӽ�����ޤ�';
    break;
case 'D';    // ���ץ� ɸ��
    $search_kin = sprintf("%s and kamoku<=5 and paya.div='C' and ((kouji_no NOT like 'SC%%') or (kouji_no IS NULL))", $search);
    $caption_div = '���ץ�ɸ����(������)�����ڤӽ�����ޤ�';
    break;
case 'S';    // ���ץ� ����
    $search_kin = sprintf("%s and kamoku<=5 and paya.div='C' and kouji_no like 'SC%%'", $search);
    $caption_div = '���ץ�������(������)�����ڤӽ�����ޤ�';
    break;
case 'L';    // ��˥� ����
    //$search_kin = sprintf("%s and kamoku<=5 and paya.div='L' and pay.parts_no not like '%s'", $search, 'T%');
    $search_kin = sprintf("%s and kamoku<=5 and paya.div='L'", $search);
    $caption_div = '��˥�����(������)�����ڤӽ�����ޤ�';
    break;
case 'T';    // �ġ��� ����
    //$search_kin = sprintf("%s and kamoku<=5 and (paya.div='T' or (paya.div<>'T' and paya.div<>'C' and paya.parts_no like '%s'))", $search, 'T%');
    $search_kin = sprintf("%s and kamoku<=5 and paya.div='T'", $search);
    $caption_div = '�ġ�������(������)�����ڤӽ�����ޤ�';
    break;
case 'NKCT';    // NKCT
    $search_kin = sprintf("%s and kamoku<=5 and paya.div='C' AND (m.tnk_tana LIKE '%s' OR uke_no LIKE '%s' OR uke_no LIKE '%s')", $search, '8%', 'Z%', 'H%');
    $caption_div = '�Σˣã�(������)�����ڤӽ�����ޤ�';
    break;
case 'NKT';    // NKT
    $search_kin = sprintf("%s and kamoku<=5 and paya.div='L' and (m.tnk_tana LIKE '%s' OR uke_no LIKE '%s' OR uke_no LIKE '%s')", $search, '8%', 'Z%', 'H%');
    $caption_div = '�Σˣ�(������)�����ڤӽ�����ޤ�';
    break;
}
//////////// ���������׶�� (����1��5)����6�ʾ�����
$query = sprintf("select
                            count(*)
                    from
                            (act_payable as paya left outer join vendor_master using(vendor))
                    left outer join
                            order_plan
                    using(sei_no)
                    LEFT OUTER JOIN
                            parts_stock_master AS m ON (m.parts_no=paya.parts_no)
                    %s
                    GROUP BY vendor, name
                    ", $search_kin);
if (($maxrows = getResultTrs($con, $query, $paya_ctoku)) <= 0) {
    $_SESSION['s_sysmsg'] .= sprintf("��ݶ�η׾���:%s��%s��<br>�ǡ���������ޤ���", $s_ymd, $e_ymd );
    $_SESSION['s_sysmsg'] .= '��� ����μ���������ޤ���';      // .= ��å��������ɲä���
    query_affected_trans($con, 'rollback');         // transaction rollback
    header('Location: ' . H_WEB_HOST . $menu->out_RetUrl());
    exit();
} else {
    // $sum_kin = $paya_ctoku[0][0];
    // $maxrows = $paya_ctoku[0][1];    // GROUP BY �λ��Ͻ���ؿ��ϻȤ��ʤ�
}

$query = sprintf("select
                        sum(Uround(order_price * siharai,0))
                    from
                            (act_payable as paya left outer join vendor_master using(vendor))
                    left outer join
                            order_plan
                    using(sei_no)
                    LEFT OUTER JOIN
                            parts_stock_master AS m ON (m.parts_no=paya.parts_no)
                    %s
                    ", $search_kin);
if (getUniResTrs($con, $query, $sum_kin) <= 0) {
    $_SESSION['s_sysmsg'] .= '��ݹ�׶�ۤμ���������ޤ���';      // .= ��å��������ɲä���
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
            vendor              as ȯ����,                      -- 00
            name                as ȯ����̾,                    -- 01
            SUM(Uround(order_price * siharai,0)) as ȯ����,   -- 02
            count(*)     as ȯ����,                           -- 03
            SUM(siharai) as ��ʧ��                              -- 04
        from
            (act_payable as paya left outer join vendor_master using(vendor))
        left outer join
                order_plan
        using(sei_no)
        LEFT OUTER JOIN
                parts_stock_master AS m ON (m.parts_no=paya.parts_no)
        %s 
        GROUP BY vendor, name
        ORDER BY ȯ���� DESC, vendor ASC
        offset %d limit %d
    ", $search_kin, $offset, PAGE);       // ���� $search �Ǹ���
$res   = array();
$field = array();
if (($rows = getResWithFieldTrs($con, $query, $field, $res)) <= 0) {
    $_SESSION['s_sysmsg'] .= sprintf("��ݶ�η׾���:%s��%s��<br>�ǡ���������ޤ���", $s_ymd, $e_ymd );
    query_affected_trans($con, 'rollback');         // transaction rollback
    header('Location: ' . H_WEB_HOST . $menu->out_RetUrl());
    exit();
} else {
    query_affected_trans($con, 'commit');         // transaction commit
    $num = count($field);       // �ե�����ɿ�����
}

$paya_code             = 'off';
$_SESSION['paya_code'] = 'off';
$payable_code = 'summary1';
if ($paya_div ==' ') {
    $paya_div = 'A';
}

//////////// ɽ�������
$caption = "$s_ymd �� $e_ymd" . '����׶�ۡ�' . number_format($sum_kin) . '����׷����' . number_format($maxrows);
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
                        case 0:
                            echo "<td class='winbox' nowrap align='center'><div class='pt9'><a href='", $menu->out_action('��ݼ��ӾȲ�'), "?paya_code={$paya_code}&payable_code={$payable_code}&payable_s_ym={$s_ym}&payable_e_ym={$e_ym}&payable_div={$paya_div}&payable_vendor={$res[$r][0]}#mark'>{$res[$r][$i]}</a></div></td>\n";
                            break;
                        case 1:
                            echo "<td class='winbox' nowrap align='left'><div class='pt9'>{$res[$r][$i]}</div></td>\n";
                            break;
                        case 2:
                            echo "<td class='winbox' nowrap align='right'><div class='pt9'>", number_format($res[$r][$i]), "</div></td>\n";
                            break;
                        case 3:
                            echo "<td class='winbox' nowrap align='right'><div class='pt9'>", number_format($res[$r][$i]), "</div></td>\n";
                            break;
                        case 4:
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
