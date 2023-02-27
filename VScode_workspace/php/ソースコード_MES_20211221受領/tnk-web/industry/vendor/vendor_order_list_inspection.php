<?php
//////////////////////////////////////////////////////////////////////////////
// ���Ϲ�������ĥꥹ�ȤξȲ� (�ݥåץ��åץ�����ɥ��ǡ�                   //
// Copyright (C) 2005-2015 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp      //
// Changed history                                                          //
// 2005/04/26 Created   vendor_order_list.php                               //
// 2005/05/06 SQL�ξ���ľ�� �إå��������٤�where��� �� ��������ɲ�   //
// 2005/05/09 order_data�ξ���and ken_date<=0���ɲ� ��ʸ������Ŀ����ѹ�  //
// 2005/05/11 menu_form.css ����Ѥ��ʤ��褦���ѹ� �᡼���ź�դǤ���褦�� //
// 2005/05/12 order by plan_cond ASC ��ͥ����ɲ� ���Ǽ����153����200����  //
//            �� ��ʸ���⼨�桦ͽ�ꢪO,R,P �� order by ȯ��ײ��ʬ ASC�� //
// 2005/05/17 sei_no and vendor and ken_data��sei_no and order_no and vendor//
// 2005/05/23 �����梪����/�Ѥ��ѹ�SQLʸ��subquery���ѹ��������Ϥλ��ֺ��б�//
// 2005/05/25 �嵭����Ū��Ʊ�������б��������ˤ�� �����ᤷ����           //
// 2005/05/26 �������ɤ��б����뤿�� ken_date �� where����ɲ�              //
//                                          ɬ��Ū�˸�����Ʊ����Ȥ���ˤʤ�//
// 2006/08/31 ��ĥꥹ�Ȥ��鸡�������ͽ�󤬽����褦�˵�ǽ����(��ʸ��Τ�)//
//            ʬǼ��ɼ����ʸ�ǡ�����2�Ť˸����Ƥ��ޤ����ḡ�������ѤȤ���   //
//            �ե�����̾�ѹ� vendor_order_list_inspection.php               //
//            (data.ken_date IS NULL OR data.ken_date = 0)�����Ѥߤ��оݳ�  //
// 2007/03/05 ���ܤ���¤�ֹ椬wrap���Ƥ��ޤ�����font-size:9.5pt;��9.0pt��   //
//            �����ֹ楯��å��Ǻ߸�ͽ��Ȳ񡦷���Ȳ�˥�󥯤��ɲ�        //
// 2015/10/19 ���ʥ��롼�פ�T=�ġ�����ɲá�����No.��ʸ���ܤ�T��            //
//            ���ɾ�������ˤ�ꡢL�����T��������ʤ�(T���ʤ�L������) ��ë //
//////////////////////////////////////////////////////////////////////////////
ini_set('error_reporting', E_ALL);          // E_ALL='2047' debug ��
// ini_set('display_errors', '1');             // Error ɽ�� ON debug �� ��꡼���女����
ob_start('ob_gzhandler');                   // ���ϥХåե���gzip����
session_start();                            // ini_set()�μ��˻��ꤹ�뤳�� Script �Ǿ��
require_once ('../../function.php');        // TNK ������ function (define.php��ޤ�)
require_once ('../../MenuHeader.php');      // TNK ������ menu class
access_log();                               // Script Name �ϼ�ư����

///// TNK ���ѥ�˥塼���饹�Υ��󥹥��󥹤����
$menu = new MenuHeader();                   // ǧ�ڥ����å�0=���̰ʾ� �����=���å������ �����ȥ�̤����

////////////// ����������
// $menu->set_site(30, 999);                   // site_index=30(������˥塼) site_id=999(̤��)
////////////// target����
$menu->set_target('_parent');               // �ե졼���Ǥ�������target°����ɬ��

//////////// ��ʬ��ե졼��������Ѥ���
// $menu->set_self(INDUST . 'order/order_schedule.php');
//////////// �ƽ����action̾�ȥ��ɥ쥹����
$menu->set_action('�߸�ͽ��',   INDUST . 'parts/parts_stock_plan/parts_stock_plan_Main.php');
$menu->set_action('�߸˷���',   INDUST . 'parts/parts_stock_history/parts_stock_view.php');

$view = 'OK';   // �������Ȥ�OK�ǹԤ�

///////// �ѥ�᡼���������å�
if (isset($_REQUEST['vendor'])) {
    $vendor = $_REQUEST['vendor'];
} else {
    $vendor = '00485';                           // Default(����)���ꤨ�ʤ���
    // $view = 'NG';
}
if (isset($_REQUEST['div'])) {
    $div = $_REQUEST['div'];
} else {
    $div = 'L';                              // Default(����)
}
if (isset($_REQUEST['plan_cond'])) {
    $plan_cond = $_REQUEST['plan_cond'];
} else {
    $plan_cond = '';                        // Default(����)
}

//////// ���Ϲ���̾�μ���
$query = "select name from vendor_master where vendor='{$vendor}'";
if (getUniResult($query, $vendor_name) < 1) {
    $_SESSION['s_sysmsg'] = "ȯ���襳���ɤ�̵���Ǥ���";
    $vendor_name = '̤��Ͽ';
    $view = 'NG';
}

//////////// �����ȥ�̾(�������Υ����ȥ�̾�ȥե�����Υ����ȥ�̾)
$menu->set_title("{$vendor_name} ��ĥꥹ��");  // �����ȥ������ʤ���IE�ΰ����ΥС�������ɽ���Ǥ��ʤ��Զ�礢��

//////// ɽ�������
if ($div == '') $div_name = '����'; else $div_name = $div;
if ($plan_cond == '') $cond_name = '����'; else $cond_name = $plan_cond;
$menu->set_caption("�����ɡ�{$vendor}���٥����̾��{$vendor_name}�����ʥ��롼�ס�{$div_name}��ȯ���ʬ��{$cond_name}");

////////// ���դǶ��̤� where�������
// ����200��������153(������)��184��(������)��ޤǢ�200�����ѹ�
$where_date = 'proc.delivery <= ' . date('Ymd', mktime() + (86400*300)) . ' and proc.delivery >= ' . date('Ymd', mktime() - (86400*300));

//////// ���������鶦�̤� where�������
switch ($div) {
case 'C':       // C����
    $where_div = "proc.vendor='{$vendor}' and plan.div='C' and proc.locate != '52   '";
    break;
case 'SC':      // C����
    $where_div = "proc.vendor='{$vendor}' and plan.div='C' and plan.kouji_no like '%SC%' and proc.locate != '52   '";
    break;
case 'CS':      // Cɸ��
    $where_div = "proc.vendor='{$vendor}' and plan.div='C' and plan.kouji_no not like '%SC%' and proc.locate != '52   '";
    break;
case 'L':       // L����
    $where_div = "proc.vendor='{$vendor}' and plan.div='L' and proc.locate != '52   '";
    break;
case 'T':       // T����
    $where_div = "proc.vendor='{$vendor}' and proc.parts_no like 'T%' and proc.locate != '52   '";
    break;
case 'F':       // F����
    $where_div = "proc.vendor='{$vendor}' and plan.div='F' and proc.locate != '52   '";
    break;
case 'A':       // TNK����
    $where_div = "(proc.vendor='{$vendor}' and plan.div='C' or plan.div='L' or plan.div='T' or plan.div='F') and proc.locate != '52   '";
    break;
case 'N':       // NK���ץ�
    $where_div = "(proc.vendor='{$vendor}' and plan.div='C' or plan.div='L' or plan.div='T' or plan.div='F') and proc.locate = '52   '";
    break;
default:        // �����ʥ��롼�� '' ' ' �ΰ㤤�����ä����� default ���ѹ�
    $where_div = "proc.vendor='{$vendor}' and proc.locate != '52   '";
    break;
}
//////// ȯ��ײ��ʬ���鶦�̤� where�������
switch ($plan_cond) {
case 'P':       // ͽ��
case 'R':       // �⼨��(��꡼��)
case 'O':       // ��ʸ��ȯ�ԺѤ�
    $where_cond = "proc.plan_cond='{$plan_cond}'";
    break;
default:
    $where_cond = "proc.plan_cond != '{$plan_cond}'";
    break;
}

if ($view == 'OK') {
////////// ����SQLʸ������
$query = "select    to_char(proc.sei_no,'FM0000000')        AS ��¤�ֹ�
                  , to_char(proc.order_no,'FM000000-0')     AS ��ʸ�ֹ�
                  , proc.parts_no                           AS �����ֹ�
                  , proc.vendor                             AS ȯ���襳����
                  , CASE
                        WHEN data.order_q IS NOT NULL THEN to_char(data.order_q, 'FM9,999,999')
                        WHEN proc.order_q = 0 THEN to_char((plan.order_q - plan.utikiri - plan.nyuko), 'FM9,999,999')
                        ELSE to_char((proc.order_q - proc.siharai - proc.cut_siharai), 'FM9,999,999')
                    END                                     AS ��Ŀ�
                  , CASE
                        WHEN (data.uke_q - data.siharai) IS NULL THEN '0'
                        ELSE to_char(data.uke_q - data.siharai, 'FM9,999,999')    --����
                    END                                     AS ������
                  , proc.order_price                        AS ñ��
                  , substr(to_char(proc.delivery, 'FM9999/99/99'), 6, 5)
                                                            AS Ǽ��
                  , plan.kouji_no                           AS �����ֹ�
                  , proc.pro_mark                           AS ����
                  , proc.mtl_cond                           AS �������
                  , proc.pro_kubun                          AS ����ñ����ʬ
                  , proc.order_date                         AS ȯ����
                  , proc.order_q                            AS ����ʸ��
                  , proc.locate                             AS Ǽ�����
                  , proc.kamoku                             AS ����
                  , proc.order_ku                           AS ȯ���ʬ
                  , CASE
                        WHEN proc.plan_cond = 'P' THEN 'ͽ����'
                        WHEN proc.plan_cond = 'O' THEN '��ʸ��'
                        WHEN proc.plan_cond = 'R' THEN '�⼨��'
                        ELSE proc.plan_cond
                    END                                     AS ȯ��ײ��ʬ
                  , proc.next_pro                           AS ������
                  , CASE
                        WHEN proc.next_pro != 'END..' THEN
                            (select substr(name, 1, 6) from vendor_master where vendor=proc.next_pro limit 1)
                        ELSE proc.next_pro
                    END                                     AS ������̾
                  , trim(substr(mast.name, 1, 8))           AS ȯ����̾
                  , trim(substr(item.midsc, 1, 11))         AS ����̾
                  , CASE
                          WHEN trim(item.mzist) = '' THEN '---'         --NULL�Ǥʤ��ƥ��ڡ�������ޤäƤ�����Ϥ��졪
                          ELSE substr(item.mzist, 1, 8)
                    END                                     AS ���
                  , CASE
                          WHEN trim(item.mepnt) = '' THEN '---'         --NULL�Ǥʤ��ƥ��ڡ�������ޤäƤ�����Ϥ��졪
                          ELSE substr(item.mepnt, 1, 8)
                    END                                     AS �Ƶ���
                  , data.order_seq                          AS ȯ��Ϣ��
            from
                order_process   AS proc
            left outer join
                order_data      AS data
                                        using(sei_no, order_no, vendor)
            left outer join
                order_plan      AS plan
                                        using(sei_no)
            left outer join
                vendor_master   AS mast
                                        on(proc.vendor = mast.vendor)
            left outer join
                miitem          AS item
                                        on(proc.parts_no = item.mipn)
            where
                {$where_date}
                and
                {$where_div}
                and
                (plan.order_q - plan.utikiri - plan.nyuko) > 0
                    -- �إå�������Ĥ�����ʪ��
                and
                ( (proc.order_q = 0) OR ((proc.order_q - proc.siharai - proc.cut_siharai > 0)) )
                    -- ���������� ���ϼ�ʬ�ι�������Ĥ�����ʪ
                and
                {$where_cond}
                and
                (data.ken_date IS NULL OR data.ken_date = 0)
            order by ȯ��ײ��ʬ ASC, proc.delivery ASC, proc.parts_no ASC, data.order_seq ASC
            offset 0
            limit 1000
";
$res = array();
if (($rows = getResult($query, $res)) < 1) {
    $_SESSION['s_sysmsg'] .= '��ĥǡ���������ޤ���';
    $view = 'NG';
}
} // if end

/////////// HTML Header ����Ϥ��ƥ���å��������
$menu->out_html_header();
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=EUC-JP">
<meta http-equiv="Content-Style-Type" content="text/css">
<title><?php echo $menu->out_title() ?></title>
<?php // $menu->out_css() ?>
<style type='text/css'>
<!--
body {
    margin:        0%;
}
form {
    margin:        0%;
}
.caption_font {
    font-size:              11.5pt;
    font-weight:            bold;
}
th {
    font-size:      11.5pt;
    font-weight:    bold;
    font-family:    monospace;
    color:          blue;
}
table {
    font-size:      11pt;
    font-weight:    normal;
    /* font-family:    monospace; */
}
.item {
    position: absolute;
    /* top:   0px; */
    left:     0px;
}
.msg {
    position: absolute;
    top:  100px;
    left:   0px;
}
.winbox {
    border-style: solid;
    border-width: 1px;
    border-top-color:       #FFFFFF;
    border-left-color:      #FFFFFF;
    border-right-color:     #bdaa90;
    border-bottom-color:    #bdaa90;
}
.winbox_field {
    border-style: solid;
    border-width: 1px;
    border-top-color:       #bdaa90;
    border-left-color:      #bdaa90;
    border-right-color:     #FFFFFF;
    border-bottom-color:    #FFFFFF;
    background-color:#d6d3ce;
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
<script language='JavaScript'>
<!--
function init() {
}
function winActiveChk() {
    if (document.all) {     // IE�ʤ�
        if (document.hasFocus() == false) {     // IE5.5�ʾ�ǻȤ���
            window.focus();
            return;
        }
        return;
    } else {                // NN �ʤ�ȥ�ꥭ�å�
        window.focus();
        return;
    }
    // ����ˡ <body onLoad="setInterval('winActiveChk()',100)">
    // <input type='button' value='TEST' onClick="window.opener.location.reload()">
    // parent.Header.�ؿ�̾() or ���֥�������;
}
function inspection_recourse(order_seq, parts_no, parts_name) {
    if (confirm('�����ֹ桧' + parts_no + '\n\n����̾�Ρ�' + parts_name + " ��\n\n�۵����� ���������ͽ��򤷤ޤ���\n\n�������Ǥ�����")) {
        // �¹Ԥ��ޤ���
        document.inspection_form.order_seq.value = order_seq;
        document.inspection_form.submit();
    } else {
        alert('��ä��ޤ�����');
    }
}
function vendor_code_view(vendor, vendor_name) {
    alert('ȯ���襳���ɡ�' + vendor + '\n\nȯ����̾��' + vendor_name + '\n\n');
}
function win_open(url) {
    var w = 900;
    var h = 680;
    var left = (screen.availWidth  - w) / 2;
    var top  = (screen.availHeight - h) / 2;
    window.open(url, 'view_win2', 'width='+w+',height='+h+',scrollbars=no,status=no,toolbar=no,location=no,menubar=no,top='+top+',left='+left);
}
// -->
</script>
<form name='inspection_form' method='get' action='<?php echo INDUST ?>order/inspection_recourse_regist.php' target='_self'>
    <input type='hidden' name='retUrl' value='<?php echo $menu->out_self(), '?' . $_SERVER['QUERY_STRING'] ?>'>
    <input type='hidden' name='order_seq' value=''>
</form>
</head>
<body onLoad='winActiveChk()'>
    <center>
        <?php if ($view != 'OK') { ?>
        <table border='0' class='msg' width='100%'>
            <tr>
                <td align='center'>
                    <b style='color: teal;'>�ǡ���������ޤ���</b>
                    <br>��<br>
                    ȯ���襳���ɡ�<?php echo $vendor, "�����ʥ��롼�ס�{$div_name}��ȯ���ʬ��{$cond_name}"?>
                </td>
            </tr>
            <tr>
                <td>��</td>
            </tr>
            <tr>
                <td align='center'>
                    <input type='button' name='close' value='�Ĥ���' onClick='JavaScript:window.close()'>
                </td>
            </tr>
        </table>
        <?php } else { ?>
        <!-------------- �ܺ٥ǡ���ɽ���Τ����ɽ����� -------------->
        <table class='item' bgcolor='#d6d3ce'  border='1' cellspacing='0' cellpadding='2'>
           <tr><td> <!-- ���ߡ�(�ǥ�������) -->
        <table class='winbox_field' width=100% align='center' border='1' cellspacing='0' cellpadding='1'>
            <div class='caption_font' align='right'><?php echo $menu->out_caption()?></div>
            <th class='winbox' nowrap width='30'>No</th>
            <th class='winbox' nowrap width='45' style='font-size:9.5pt;'>Ǽ ��</th>
            <th class='winbox' nowrap width='60' style='font-size:9.0pt;'>��¤�ֹ�</th>
            <th class='winbox' nowrap width='80'>�����ֹ�</th>
            <th class='winbox' nowrap width='105'>����̾</th><!-- �ޥ��ʥ�40 -->
            <th class='winbox' nowrap width='85'>��&nbsp;&nbsp;��</th>
            <th class='winbox' nowrap width='90'>�Ƶ���</th>
            <th class='winbox' nowrap width='60'>��Ŀ�</th>
            <th class='winbox' nowrap width='60'>������</th>
            <th class='winbox' nowrap width='20' style='font-size:10.5pt;'>����</th>
            <th class='winbox' nowrap width='50' style='font-size:10.5pt;'>��ʬ</th>
            <th class='winbox' nowrap width='80'>������̾</th>
        <?php
            $i = 0;
            foreach ($res as $rec) {
                $i++;
                if ($rec['ȯ��Ϣ��']) {
                    echo "<tr class='table_font' onDblClick='inspection_recourse(\"{$rec['ȯ��Ϣ��']}\",\"{$rec['�����ֹ�']}\",\"{$rec['����̾']}\")'>\n";
                } else {
                    echo "<tr class='table_font'>\n";
                }
                echo "<td class='winbox' align='right'  width='30'  bgcolor='#d6d3ce'>{$i}</td>\n";
                echo "<td class='winbox' align='center' width='45'  bgcolor='#d6d3ce'>{$rec['Ǽ��']}</td>\n";
                echo "<td class='winbox' align='center' width='60'  bgcolor='#d6d3ce'>{$rec['��¤�ֹ�']}</td>\n";
                echo "<td class='winbox' align='center' width='80'  bgcolor='#d6d3ce'><a class='link' href='javascript:void(0)' onClick='win_open(\"{$menu->out_action('�߸�ͽ��')}?showMenu=CondForm&targetPartsNo=" . urlencode($rec['�����ֹ�']) . "&noMenu=yes\");' target='_self' style='text-decoration:none;'>{$rec['�����ֹ�']}</a></td>\n";
                echo "<td class='winbox' align='left'   width='105' bgcolor='#d6d3ce'>" . mb_convert_kana($rec['����̾'], 'k') . "</td>\n";
                echo "<td class='winbox' align='left'   width='85'  bgcolor='#d6d3ce'>" . mb_convert_kana($rec['���'], 'k') . "</td>\n";
                echo "<td class='winbox' align='left'   width='90'  bgcolor='#d6d3ce'>" . mb_convert_kana($rec['�Ƶ���'], 'k') . "</td>\n";
                echo "<td class='winbox' align='right'  width='60'  bgcolor='#d6d3ce'>{$rec['��Ŀ�']}</td>\n";
                echo "<td class='winbox' align='right'  width='60'  bgcolor='#d6d3ce'>{$rec['������']}</td>\n";
                echo "<td class='winbox' align='center' width='20'  bgcolor='#d6d3ce'>{$rec['����']}</td>\n";
                echo "<td class='winbox' align='center' width='50'  bgcolor='#d6d3ce'>{$rec['ȯ��ײ��ʬ']}</td>\n";
                echo "<td class='winbox' align='left'   width='80' bgcolor='#d6d3ce'>{$rec['������̾']}</td>\n";
                echo "</tr>\n";
            }
        ?>
        </table> <!----- ���ߡ� End ----->
            </td></tr>
        </table>
        <?php } ?>
    </center>
</body>
</html>
<?php echo $menu->out_alert_java()?>
<?php $_SESSION['s_sysmsg'] = ''; ?>
<?php ob_end_flush(); // ���ϥХåե���gzip���� END ?>
