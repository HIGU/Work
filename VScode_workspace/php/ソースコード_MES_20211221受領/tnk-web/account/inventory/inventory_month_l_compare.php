<?php
//////////////////////////////////////////////////////////////////////////////
// ê���ǡ������������ ��˥�       UKWLIB/W#MVTNPT                      //
//              ��ʿ��ñ��(�����ܷ軻) UKFLIB/SGAVE@L or USGAV@LIB/SGAVE@L  //
// Copyright (C) 2010-2012 Norihisa.Ohya norihisa_ooya@nitto-kohki.co.jp    //
// Changed history                                                          //
// 2010/11/10 Created   inventory_month_l_compare.php                       //
// 2011/05/24 �����ê����ۤȤκ��ۤ�߸˿����������ɲ�                    //
// 2012/12/05 ���ۤξ�������Τκ߸˷���Υ�����Զ�����              //
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
$menu->set_site(20, 99);                    // site_index=20(������˥塼) site_id=36(��˥�ê����׶�ۤ�����)
////////////// �꥿���󥢥ɥ쥹����
// $menu->set_RetUrl(INDUST_MENU);             // �̾�ϻ��ꤹ��ɬ�פϤʤ�
//////////// �����ȥ�̾(�������Υ����ȥ�̾�ȥե�����Υ����ȥ�̾)
$menu->set_title('��˥����� ��ʿ��ê����ۤ����');
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

//////////// ���ǤιԿ�
define('PAGE', '50');

//////////// SQL ʸ�� where ��� ���Ѥ���
// $search = "where (parts_no like 'LR%' or parts_no like 'LC%')";     // num_div 1=���� 3=��˥� 5=���ץ�
// $search = "where num_div='3' and tou_zai > 0 ";     // num_div 1=���� 3=��˥� 5=���ץ�
$search = "where invent_ym={$act_ym} and num_div='3' and pro.type is null";     // num_div 1=���� 3=��˥� 5=���ץ�

//////////// ��ץ쥳���ɿ�����     (�оݥǡ����κ������ڡ�������˻���)
$query = sprintf('select
                    count(*),
                    sum(Uround(tou_zai * gai_tan, 0) + Uround(tou_zai * nai_tan, 0)) as ���
                  from
                    inventory_monthly as inv
                  left outer join
                    provide_item as pro
                  on (inv.invent_ym=pro.reg_ym and inv.parts_no=pro.parts_no)
                  %s', $search);
if ( getResult($query, $res_sum) <= 0) {         // $maxrows �μ���
    $_SESSION['s_sysmsg'] .= "��ץ쥳���ɿ��μ����˼���";      // .= ��å��������ɲä���
}
$maxrows = $res_sum[0][0];  // ��ץ쥳���ɿ�
$sum_kin = $res_sum[0][1];  // ��� ê�� ���

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
//////////// ���۷׻��ʺ��ۤ��礭�����
$query = sprintf("
        select
            o.parts_no           as �����ֹ�,               --- 0
            substr(m.midsc,1,12) as ����̾,                 --- 1
            zen_zai              as ����߸�,               --- 2
            tou_zai              as ����߸�,               --- 3
            tou_zai - zen_zai    as �߸�����,               --- 4
            a.average_cost       as ��ʿ��ñ��,             --- 5
            Uround(zen_zai * average_cost, 0) as ������,  --- 6
            Uround(tou_zai * average_cost, 0) as ������,  --- 7
            Uround(tou_zai * average_cost, 0) - Uround(zen_zai * average_cost, 0) as ����,  --- 8
            num_div              as ������                  --- 9
        from
            inventory_monthly as o left outer join miitem as m on o.parts_no = m.mipn 
            left outer join periodic_average_cost_history2 as a on o.parts_no = a.parts_no and a.period_ym={$act_ym}
            left outer join provide_item as pro on (o.invent_ym=pro.reg_ym and o.parts_no=pro.parts_no)
        %s 
        and a.average_cost > 0.01
        -- and Uround(tou_zai * average_cost, 0) - Uround(zen_zai * average_cost, 0) >= 500000
        -- order by serial_no ASC
        order by ���� DESC
        offset %d limit %d
    ", $search, $offset, PAGE);       // ���� $search �ϻ��Ѥ��ʤ�
$res   = array();
$field = array();
if (($rows = getResultWithField2($query, $field, $res)) <= 0) {
    $_SESSION['s_sysmsg'] .= 'ê���Υǡ����������Ǥ��ޤ���!';
    header('Location: ' . H_WEB_HOST . $menu->out_RetUrl());    // ľ���θƽи������
    exit();
} else {
    $num = count($field);       // �ե�����ɿ�����
}
//////////// ���۷׻��ʺ��ۤξ��������
$query_asc = sprintf("
        select
            o.parts_no           as �����ֹ�,               --- 0
            substr(m.midsc,1,12) as ����̾,                 --- 1
            zen_zai              as ����߸�,               --- 2
            tou_zai              as ����߸�,               --- 3
            tou_zai - zen_zai    as �߸�����,               --- 4
            a.average_cost       as ��ʿ��ñ��,             --- 5
            Uround(zen_zai * average_cost, 0) as ������,  --- 6
            Uround(tou_zai * average_cost, 0) as ������,  --- 7
            Uround(tou_zai * average_cost, 0) - Uround(zen_zai * average_cost, 0) as ����,  --- 8
            num_div              as ������                  --- 9
        from
            inventory_monthly as o left outer join miitem as m on o.parts_no = m.mipn 
            left outer join periodic_average_cost_history2 as a on o.parts_no = a.parts_no and a.period_ym={$act_ym}
            left outer join provide_item as pro on (o.invent_ym=pro.reg_ym and o.parts_no=pro.parts_no)
        %s 
        and a.average_cost > 0.01
        -- and Uround(tou_zai * average_cost, 0) - Uround(zen_zai * average_cost, 0) <= -500000
        -- order by serial_no ASC
        order by ���� ASC
        offset %d limit %d
    ", $search, $offset, PAGE);       // ���� $search �ϻ��Ѥ��ʤ�
$res_asc   = array();
$field_asc = array();
if (($rows_asc = getResultWithField2($query_asc, $field_asc, $res_asc)) <= 0) {
    $_SESSION['s_sysmsg'] .= 'ê���Υǡ����������Ǥ��ޤ���!';
    header('Location: ' . H_WEB_HOST . $menu->out_RetUrl());    // ľ���θƽи������
    exit();
} else {
    $num_asc = count($field_asc);       // �ե�����ɿ�����
}
// ��׶�ۤη׻�
$query_t = sprintf("
        select
            o.parts_no           as �����ֹ�,               --- 0
            substr(m.midsc,1,12) as ����̾,                 --- 1
            tou_zai              as ����߸�,               --- 2
            a.average_cost       as ��ʿ��ñ��,             --- 3
            a.mate_cost          as ������,                 --- 4
            a.out_cost           as ����,                   --- 5
            a.manu_cost          as ����,                   --- 6
            a.assem_cost         as ��Ω,                   --- 7
            a.other_cost         as ����¾,                 --- 8
            a.indirect_cost      as ������,                 --- 9
            Uround(tou_zai * average_cost, 0) as �߸˶��,  --- 10
            num_div              as ������                  --- 11
        from
            inventory_monthly as o left outer join miitem as m on o.parts_no = m.mipn 
            left outer join periodic_average_cost_history2 as a on o.parts_no = a.parts_no and a.period_ym={$act_ym}
            left outer join provide_item as pro on (o.invent_ym=pro.reg_ym and o.parts_no=pro.parts_no)
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
    $sum_kin += $res_t[$r][10];
}

// ����ι�׶�ۤη׻�
$b_year  = substr($act_ym, 0, 4);
$b_month = (substr($act_ym, 4, 2) - 1);
if ($b_month < 1) {
    $b_year -= 1;
    $b_month = 12;
}
$before_ym = sprintf("%d%02d", $b_year, $b_month);
$search_b = "where invent_ym={$before_ym} and num_div='3' and pro.type is null";     // num_div 1=���� 3=��˥� 5=���ץ�
$query_b = sprintf("
        select
            o.parts_no           as �����ֹ�,               --- 0
            substr(m.midsc,1,12) as ����̾,                 --- 1
            tou_zai              as ����߸�,               --- 2
            a.average_cost       as ��ʿ��ñ��,             --- 3
            a.mate_cost          as ������,                 --- 4
            a.out_cost           as ����,                   --- 5
            a.manu_cost          as ����,                   --- 6
            a.assem_cost         as ��Ω,                   --- 7
            a.other_cost         as ����¾,                 --- 8
            a.indirect_cost      as ������,                 --- 9
            Uround(tou_zai * average_cost, 0) as ������,  --- 10
            num_div              as ������                  --- 11
        from
            inventory_monthly as o left outer join miitem as m on o.parts_no = m.mipn 
            left outer join periodic_average_cost_history2 as a on o.parts_no = a.parts_no and a.period_ym={$before_ym}
            left outer join provide_item as pro on (o.invent_ym=pro.reg_ym and o.parts_no=pro.parts_no)
        %s
        -- order by serial_no ASC
        -- order by ��� DESC
    ", $search_b);       // ���� $search_b
$res_b   = array();
$field_b = array();
if (($rows_b = getResultWithField2($query_b, $field_b, $res_b)) <= 0) {
    $_SESSION['s_sysmsg'] .= '�����ê���ǡ����������Ǥ��ޤ���!';
    header('Location: ' . H_WEB_HOST . $menu->out_RetUrl());    // ľ���θƽи������
    exit();
} else {
    $num_b = count($field_b);       // �ե�����ɿ�����
}
$sum_kin_b = 0;
for ($r=0; $r<$rows_b; $r++) {
    $sum_kin_b += $res_b[$r][10];
}

//////////// ����ι�ץ쥳���ɿ�����
$query_b = sprintf('select
                    count(*),
                    sum(Uround(tou_zai * gai_tan, 0) + Uround(tou_zai * nai_tan, 0)) as ���
                  from
                    inventory_monthly as inv
                  left outer join
                    provide_item as pro
                  on (inv.invent_ym=pro.reg_ym and inv.parts_no=pro.parts_no)
                  %s', $search_b);
$res_sum_b = array();         // �����
if ( getResult($query_b, $res_sum_b) <= 0) {         // $maxrows �μ���
    $_SESSION['s_sysmsg'] .= "��ץ쥳���ɿ��μ����˼���";      // .= ��å��������ɲä���
}
$maxrows_b   = $res_sum_b[0][0];  // �����ץ쥳���ɿ�

$dif_kin   = $sum_kin - $sum_kin_b;   // ê������
$maxrows_d = $maxrows - $maxrows_b;   // ��������

//////////// ɽ�������
$caption = "{$act_ym}��" . $menu->out_title() . "<BR>������=" . number_format($sum_kin) . '�ߡ���' . number_format($maxrows) . "�� <BR>������=" . number_format($sum_kin_b) . '�ߡ���' . number_format($maxrows_b) . "�� <BR>���ۡ����=" . number_format($dif_kin) . '�ߡ���' . number_format($maxrows_d) . "�� \n";
$menu->set_caption($caption);

//////////////// HTML Header ����Ϥ��ƥ֥饦�����Υ���å��������
$menu->out_html_header();
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=EUC-JP">
<meta http-equiv="Content-Style-Type" content="text/css">
<title><?php echo $menu->out_title() ?></title>
<?php echo $menu->out_site_java()?>
<?php echo $menu->out_css()?>

<!--    �ե��������ξ��
<script language='JavaScript' src='template.js?<?php echo $uniq ?>'></script>
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
<link rel='stylesheet' href='template.css?<?php echo $uniq ?>' type='text/css' media='screen'>
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
<?php echo $menu->out_title_border()?>
        
        <!----------------- ������ ���� ���� �Υե����� ---------------->
        <table width='100%' border='0' cellspacing='0' cellpadding='0'>
            <form name='page_form' method='post' action='<?php echo $menu->out_self() ?>'>
                <tr>
                    <td align='left'>
                        <table align='left' border='3' cellspacing='0' cellpadding='0'>
                            <td align='left'>
                                <input class='pt10b' type='submit' name='backward' value='����'>
                            </td>
                        </table>
                    </td>
                    <td nowrap align='center' class='pt11b'>
                        <?php echo $menu->out_caption() ?>
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
        
        <!--------------- ����������ʸ��ɽ��ɽ������ --------------------><!--------------- ���ۤ��ܤΤ�Τ�ɽ�� -------------------->
        <br>
        <?php
        $caption = "���ۤ��礭��ʪ TOP50 \n";
        $menu->set_caption($caption);
        ?>
        <table width='100%' border='0' cellspacing='0' cellpadding='0'>
            <tr>
                <td nowrap align='center' class='pt11b'>
                    <?php echo $menu->out_caption() ?>
                </td>
            </tr>
        </table>
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
                    <th class='winbox' nowrap><?php echo $field[$i] ?></th>
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
                        <td class='winbox' nowrap align='right'><div class='pt10b'><?php echo ($r + $offset + 1) ?></div></td>    <!-- �ԥʥ�С���ɽ�� -->
                    <?php
                    for ($i=0; $i<$num; $i++) {         // �쥳���ɿ�ʬ���֤�
                        switch ($i) {
                        case 0:     // �����ֹ�˥�󥯤��ɲ�
                            echo "<td class='winbox' nowrap align='center'><div class='pt9'><a class='link' href='javascript:void(0)' onClick='win_open(\"{$menu->out_action('�߸˷���')}?targetPartsNo=" . urlencode($res[$r][$i]) . "&noMenu=yes\");' target='_self' style='text-decoration:none;'>{$res[$r][$i]}</a></div></td>\n";
                            break;
                        case 1:
                            echo "<td class='winbox' nowrap align='left'><div class='pt9'>{$res[$r][$i]}</div></td>\n";
                            break;
                        case 2:
                            echo "<td class='winbox' nowrap align='right'><div class='pt9'>", number_format($res[$r][$i], 0), "</div></td>\n";
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
                            echo "<td class='winbox' nowrap align='right'><div class='pt9'>", number_format($res[$r][$i], 0), "</div></td>\n";
                            break;
                        case 9:
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
        
        <!--------------- ���ۤ��ݤΤ�Τ�ɽ�� -------------------->
        <br>
        <?php
        $caption = "���ۤξ�����ʪ TOP50 \n";
        $menu->set_caption($caption);
        ?>
        <table width='100%' border='0' cellspacing='0' cellpadding='0'>
            <tr>
                <td nowrap align='center' class='pt11b'>
                    <?php echo $menu->out_caption() ?>
                </td>
            </tr>
        </table>
        <table bgcolor='#d6d3ce' align='center' border='1' cellspacing='0' cellpadding='3'>
            <tr><td> <!----------- ���ߡ�(�ǥ�������) ------------>
        <table class='winbox_field' width='100%' bgcolor='#d6d3ce' align='center' border='1' cellspacing='0' cellpadding='3'>
            <THEAD>
                <!-- �ơ��֥� �إå�����ɽ�� -->
                <tr>
                    <th class='winbox' nowrap width='10'>No</th>        <!-- �ԥʥ�С���ɽ�� -->
                <?php
                for ($i=0; $i<$num_asc; $i++) {             // �ե�����ɿ�ʬ���֤�
                ?>
                    <th class='winbox' nowrap><?php echo $field_asc[$i] ?></th>
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
                for ($r=0; $r<$rows_asc; $r++) {
                ?>
                    <tr>
                        <td class='winbox' nowrap align='right'><div class='pt10b'><?php echo ($r + $offset + 1) ?></div></td>    <!-- �ԥʥ�С���ɽ�� -->
                    <?php
                    for ($i=0; $i<$num_asc; $i++) {         // �쥳���ɿ�ʬ���֤�
                        switch ($i) {
                        case 0:     // �����ֹ�˥�󥯤��ɲ�
                            echo "<td class='winbox' nowrap align='center'><div class='pt9'><a class='link' href='javascript:void(0)' onClick='win_open(\"{$menu->out_action('�߸˷���')}?targetPartsNo=" . urlencode($res_asc[$r][$i]) . "&noMenu=yes\");' target='_self' style='text-decoration:none;'>{$res_asc[$r][$i]}</a></div></td>\n";
                            break;
                        case 1:
                            echo "<td class='winbox' nowrap align='left'><div class='pt9'>{$res_asc[$r][$i]}</div></td>\n";
                            break;
                        case 2:
                            echo "<td class='winbox' nowrap align='right'><div class='pt9'>", number_format($res_asc[$r][$i], 0), "</div></td>\n";
                            break;
                        case 3:
                            echo "<td class='winbox' nowrap align='right'><div class='pt9'>", number_format($res_asc[$r][$i], 0), "</div></td>\n";
                            break;
                        case 4:
                            echo "<td class='winbox' nowrap align='right'><div class='pt9'>", number_format($res_asc[$r][$i], 0), "</div></td>\n";
                            break;
                        case 6:
                            echo "<td class='winbox' nowrap align='right'><div class='pt9'>", number_format($res_asc[$r][$i], 0), "</div></td>\n";
                            break;
                        case 7:
                            echo "<td class='winbox' nowrap align='right'><div class='pt9'>", number_format($res_asc[$r][$i], 0), "</div></td>\n";
                            break;
                        case 8:
                            echo "<td class='winbox' nowrap align='right'><div class='pt9'>", number_format($res_asc[$r][$i], 0), "</div></td>\n";
                            break;
                        case 9:
                            echo "<td class='winbox' nowrap align='left'><div class='pt9'><center>{$res_asc[$r][$i]}</center></div></td>\n";
                            break;
                        default:
                            echo "<td class='winbox' nowrap align='right'><div class='pt9'>", number_format($res_asc[$r][$i], 2), "</div></td>\n";
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
<?php echo $menu->out_alert_java()?>
</html>
<?php
ob_end_flush();                 // ���ϥХåե���gzip���� END
?>
