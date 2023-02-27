<?php
//////////////////////////////////////////////////////////////////////////////
// ê�� ��� �ξȲ� ��(����)    ������ UKWLIB/W#MVTNPT                      //
//              ��ʿ��ñ��(�����ܷ軻) UKFLIB/SGAVE@L or USGAV@LIB/SGAVE@L  //
// ɸ���ʤΤ�����������ꤹ���ۤ����                                     //
// Copyright (C) 2017-2017 Norihisa.Ohya norihisa_ooya@nitto-kohki.co.jp    //
// Changed history                                                          //
// 2017/11/13 Created   inventory_monthly_ctoku_view_average_allo.php       //
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
$menu->set_site(20, 99);                    // site_index=20(������˥塼) site_id=35(���ץ�ê����׶�ۤ�����)
////////////// �꥿���󥢥ɥ쥹����
// $menu->set_RetUrl(INDUST_MENU);             // �̾�ϻ��ꤹ��ɬ�פϤʤ�
//////////// �����ȥ�̾(�������Υ����ȥ�̾�ȥե�����Υ����ȥ�̾)
$menu->set_title('���ץ����� ��ʿ��ê����ۤξȲ�');
//////////// �ƽ����action̾�ȥ��ɥ쥹����
$menu->set_action('�߸�ͽ��',   INDUST . 'parts/parts_stock_plan/parts_stock_plan_Main.php');
$menu->set_action('�߸˷���',   INDUST . 'parts/parts_stock_history/parts_stock_history_Main.php');

//////////// JavaScript Stylesheet File ��ɬ���ɤ߹��ޤ���
$uniq = uniqid('target');

//////////// �о�ǯ������ (ǯ��Τߤ����)
if ( isset($_SESSION['indv_ym']) ) {
    $act_ym = $_SESSION['indv_ym'];
    $s_ymd  = $act_ym . '01';   // ������
    $e_ymd  = $act_ym . '99';   // ��λ��
} else {
    $_SESSION['s_sysmsg'] = '�о�ǯ����ꤵ��Ƥ��ޤ���';
    header('Location: ' . H_WEB_HOST . $menu->out_RetUrl());    // ľ���θƽи������
    exit();
}

//////////// ����Ψǯ������
$allo_mm  = substr($act_ym, -2, 2);
$allo_yy  = substr($act_ym,  0, 4);
$allo_mm  = $allo_mm * 1;
if ($allo_mm > 9) {          // ����(10��12��)�ξ��
    $allo_ym  = $allo_yy . '09';
} elseif ($allo_mm < 4)  {   // ����(1��3��)�ξ��
    $allo_ym  = $allo_yy - 1 . '09';
    $allo_ym  = $allo_ym * 1;
} else {                    // ����ξ��
    $allo_ym  = $allo_yy . '03';
}

//////////// ���ǤιԿ�
define('PAGE', '10000');

//////////// SQL ʸ�� where ��� ���Ѥ���
// $search = "where (parts_no like 'LR%' or parts_no like 'LC%')";     // num_div 1=���� 3=��˥� 5=���ץ�
// $search = "where num_div='5' and tou_zai > 0 ";     // num_div 1=���� 3=��˥� 5=���ץ�
$search = "WHERE p.ctoku_ym={$allo_ym} and p.ctoku_allo is not NULL and inv.num_div='5' and inv.tou_zai <> 0 and a.parts_no is not NULL and t.parts_no is NULL";     // num_div 1=���� 3=��˥� 5=���ץ�

//////////// ��ץ쥳���ɿ�����     (�оݥǡ����κ������ڡ�������˻���)
$query = sprintf("select
                    count(*)
                  from 
                    inventory_ctoku_par as p
                    left outer join miitem as m on p.parts_no = m.mipn
                    left outer join inventory_monthly as inv on p.parts_no = inv.parts_no and inv.invent_ym=%d
                    left outer join inventory_monthly_ctoku as t on p.parts_no = t.parts_no and t.invent_ym=%d
                    left outer join periodic_average_cost_history2 as a on p.parts_no = a.parts_no and a.period_ym=%d
                  %s", $act_ym, $act_ym, $act_ym, $search);
if ( getResult($query, $res_sum) <= 0) {         // $maxrows �μ���
    $_SESSION['s_sysmsg'] .= "��ץ쥳���ɿ��μ����˼���";      // .= ��å��������ɲä���
}
$maxrows = $res_sum[0][0];  // ��ץ쥳���ɿ�

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
            SELECT p.parts_no           as �����ֹ�,                                                    --- 0
                    substr(m.midsc,1,12) as ����̾,                                                      --- 1
                    p.ctoku_allo         as ����Ψ,                                                      --- 2
                    inv.tou_zai          as ����߸�,                                                    --- 3
                    Uround(cast(inv.tou_zai * p.ctoku_allo as numeric), 0) as ����߸�,                                   --- 4
                    a.average_cost       as ��ʿ��ñ��,                                                  --- 5
                    Uround(inv.tou_zai * a.average_cost, 0) as �߸˶��,                                 --- 6
                    Uround(Uround(cast(inv.tou_zai * p.ctoku_allo as numeric), 0) * a.average_cost, 0) as ����߸˶��,   --- 7
                    inv.num_div              as ������                                                   --- 8
            FROM 
                    inventory_ctoku_par as p
                    left outer join miitem as m on p.parts_no = m.mipn
                    left outer join inventory_monthly as inv on p.parts_no = inv.parts_no and inv.invent_ym={$act_ym}
                    left outer join inventory_monthly_ctoku as t on p.parts_no = t.parts_no and t.invent_ym={$act_ym}
                    left outer join periodic_average_cost_history2 as a on p.parts_no = a.parts_no and a.period_ym={$act_ym}
            %s
            and a.average_cost > 0.01
            -- order by serial_no ASC
            order by ����߸˶�� DESC
            offset %d limit %d
        ", $search, $offset, PAGE);       // ���� $search �ϻ��Ѥ��ʤ�
$res   = array();
$field = array();
if (($rows = getResultWithField3($query, $field, $res)) <= 0) {
    $_SESSION['s_sysmsg'] .= 'ê���Υǡ����������Ǥ��ޤ���!';
    header('Location: ' . H_WEB_HOST . $menu->out_RetUrl());    // ľ���θƽи������
    exit();
} else {
    $num = count($field);       // �ե�����ɿ�����
}
// ��׶�ۤη׻�
$query_t = sprintf("
        SELECT p.parts_no           as �����ֹ�,                                                                        --- 0
                    substr(m.midsc,1,12) as ����̾,                                                                     --- 1
                    p.ctoku_allo         as ����Ψ,                                                                     --- 2
                    inv.tou_zai          as ����߸�,                                                                   --- 3
                    Uround(cast(inv.tou_zai * p.ctoku_allo as numeric), 0) as ����߸�,                                 --- 4
                    a.average_cost       as ��ʿ��ñ��,                                                                 --- 5
                    Uround(inv.tou_zai * a.average_cost, 0) as �߸˶��,                                                --- 6
                    Uround(Uround(cast(inv.tou_zai * p.ctoku_allo as numeric), 0) * a.average_cost, 0) as ����߸˶��, --- 7
                    inv.num_div              as ������                                                                  --- 8
            FROM 
                    inventory_ctoku_par as p
                    left outer join miitem as m on p.parts_no = m.mipn
                    left outer join inventory_monthly as inv on p.parts_no = inv.parts_no and inv.invent_ym={$act_ym}
                    left outer join inventory_monthly_ctoku as t on p.parts_no = t.parts_no and t.invent_ym={$act_ym}
                    left outer join periodic_average_cost_history2 as a on p.parts_no = a.parts_no and a.period_ym={$act_ym}
        %s
        -- order by serial_no ASC
        -- order by ��� DESC
    ", $search);       // ���� $search
$res_t   = array();
$field_t = array();
if (($rows_t = getResultWithField2($query_t, $field_t, $res_t)) <= 0) {
    $_SESSION['s_sysmsg'] .= 'ê���Υǡ����������Ǥ��ޤ���!';
    header('Location: ' . H_WEB_HOST . $menu->out_RetUrl());    // ľ���θƽи������
    exit();
} else {
    $num_t = count($field_t);       // �ե�����ɿ�����
}
$sum_kin = 0;
for ($r=0; $r<$rows_t; $r++) {
    $sum_kin += $res_t[$r][7];
}
//////////// ɽ�������
$caption = "{$act_ym}��" . $menu->out_title() . "�����=" . number_format($sum_kin) . '�ߡ���۽硡' . number_format($maxrows) . "�� \n";
$menu->set_caption($caption);

/////////// HTML Header ����Ϥ��ƥ���å��������
$menu->out_html_header();
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=EUC-JP">
<meta http-equiv="Content-Style-Type" content="text/css">
<title><?= $menu->out_title() ?></title>
<?= $menu->out_site_java()?>
<?= $menu->out_css()?>

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
function win_open(url) {
    var w = 900;
    var h = 600;
    var left = (screen.availWidth  - w) / 2;
    var top  = (screen.availHeight - h) / 2;
    // window.open(url, 'view_win2', 'width='+w+',height='+h+',scrollbars=no,status=no,toolbar=no,location=no,menubar=no,top='+top+',left='+left);
    window.open(url, '', 'width='+w+',height='+h+',scrollbars=no,status=no,toolbar=no,location=no,menubar=no,top='+top+',left='+left);
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
    font-family: monospace;
}
.pt10b {
    font-size:      10pt;
    font-weight:    bold;
    font-family: monospace;
}
.pt11b {
    font-size:      11pt;
    font-weight:    bold;
    color:          blue;
}
th {
    background-color:yellow;
    color:blue;
    font:bold 10pt;
    font-family: monospace;
}
a {
    color: red;
}
a.link {
    color: blue;
}
a:hover {
    background-color: blue;
    color: white;
}
-->
</style>
</head>
<body onLoad='set_focus()'>
    <center>
<?= $menu->out_title_border()?>
        
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
                        <?= $menu->out_caption() ?>
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
                        case 0:     // �����ֹ�˥�󥯤��ɲ�
                            echo "<td class='winbox' nowrap align='center'><div class='pt9'><a class='link' href='javascript:void(0)' onClick='win_open(\"{$menu->out_action('�߸˷���')}?targetPartsNo=" . urlencode($res[$r][$i]) . "&noMenu=yes\");' target='_self' style='text-decoration:none;'>{$res[$r][$i]}</a></div></td>\n";
                            break;
                        case 1:     // ����̾
                            echo "<td class='winbox' nowrap align='left'><div class='pt9'>{$res[$r][$i]}</div></td>\n";
                            break;
                        case 2:     // ����Ψ
                            echo "<td class='winbox' nowrap align='right'><div class='pt9'>", number_format($res[$r][$i], 4), "</div></td>\n";
                            break;
                        case 3:
                            echo "<td class='winbox' nowrap align='right'><div class='pt9'>", number_format($res[$r][$i], 0), "</div></td>\n";
                            break;
                        case 4:
                            echo "<td class='winbox' nowrap align='right'><div class='pt9'>", number_format($res[$r][$i], 0), "</div></td>\n";
                            break;
                        case 6:
                            echo "<td class='winbox' nowrap align='right'><div class='pt9'>", number_format($res[$r][$i], 0), "</div></td>\n";
                            break;
                        case 7:
                            echo "<td class='winbox' nowrap align='right'><div class='pt9'>", number_format($res[$r][$i], 0), "</div></td>\n";
                            break;
                        case 8:
                            echo "<td class='winbox' nowrap align='left'><div class='pt9'><center>{$res[$r][$i]}</center></div></td>\n";
                            break;
                        default:
                            echo "<td class='winbox' nowrap align='right'><div class='pt9'>", number_format($res[$r][$i], 2), "</div></td>\n";
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
<?= $menu->out_alert_java()?>
</html>
<?php
ob_end_flush();                 // ���ϥХåե���gzip���� END
?>
