<?php
//////////////////////////////////////////////////////////////////////////////
// Ǽ��ͽ��ξȲ� ��������(��ʸ��̤ȯ��ʬ) ���٤򥦥���ɥ�ɽ�� List���    //
// Copyright (C) 2004-2016 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp      //
// Changed history                                                          //
// 2004/11/24 Created  order_details_next_List.php                          //
// 2004/11/25 �鹩�������ڥ����å��� SQLʸ���ɲ� winActiveChk()��Timer���  //
// 2004/12/01 proc.delivery >= ' . date('Ymd', mktime() - (86400*124)) �ɲ� //
// 2005/07/26 �嵭�� 124������200�����ѹ� order_schedule_list.php�˹�碌�� //
// 2006/09/07 �ƽФ�ȤΣΣˣ��б���ȼ�������ʥ��롼�פˣΣˣ¤��ɲ�        //
// 2007/02/27 �����ֹ�˥�󥯤��ɲä��ƺ߸˷���ͽ��Ȳ�POPUP Window��ɽ��//
// 2007/05/11 �ǥ��쥯�ȥ�� order/ �� order/order_details/ ���ѹ�          //
//            $orderby���ɲä���Ǽ���٤�ǤϤʤ����Υꥹ�Ȥ�ȯ������ɽ��//
// 2007/12/28 PostgreSQL8.3��INTEGER��TEXT�Ȥμ�ư���㥹�Ȥ�̵���ˤʤä�����//
//            ���� NOT LIKE '%0' ���� ���� LIKE '%0' ��                     //
//            to_char(proc.order_no, 'FM9999999') NOT LIKE '%0'             //
// 2016/12/27 Ǽ���٤�򤹤٤�ɽ������褦�ѹ�                         ��ë //
//////////////////////////////////////////////////////////////////////////////
ini_set('error_reporting', E_ALL);          // E_ALL='2047' debug ��
// ini_set('display_errors', '1');             // Error ɽ�� ON debug �� ��꡼���女����
ob_start('ob_gzhandler');                   // ���ϥХåե���gzip����
session_start();                            // ini_set()�μ��˻��ꤹ�뤳�� Script �Ǿ��
require_once ('../../../function.php');     // define.php �� pgsql.php �� require_once ���Ƥ���
require_once ('../../../MenuHeader.php');   // TNK ������ menu class
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
//////////// �����ȥ�̾(�������Υ����ȥ�̾�ȥե�����Υ����ȥ�̾)
$menu->set_title('Ǽ��ͽ�� ������ ���٤ξȲ�');     // �����ȥ������ʤ���IE�ΰ����ΥС�������ɽ���Ǥ��ʤ��Զ�礢��

///////// �ѥ�᡼���������å�(����Ū�˥��å���󤫤����)
if (isset($_SESSION['div'])) {
    $div = $_SESSION['div'];                // Default(���å���󤫤�)
} else {
    $div = 'C';                             // �����(���ץ�)���ޤ��̣��̵��
}
//////// �������Υѥ�᡼������ & ����
if (isset($_REQUEST['date'])) {
    if ($_REQUEST['date'] == 'OLD') {
        $date = $_REQUEST['date'];
    } else {
        $date = $_REQUEST['date'];              // ���٤�ɽ�������������
        $date = ('20' . substr($date, 0, 2) . substr($date, 3, 2) . substr($date, 6, 2));
            // YYYYMMDD�η������Ѵ�
    }
} else {
    $date = date('Ymd');                    // �����(����)�㳰ȯ���ξ����б�
}
//////// ���������鶦�̤� where�������
switch ($div) {
case 'C':       // C����
    $where_div = "proc.parts_no LIKE 'C%' AND proc.locate != '52   '";
    break;
case 'SC':      // C����
    $where_div = "proc.parts_no LIKE 'C%' AND plan.kouji_no LIKE '%SC%' AND proc.locate != '52   '";
    break;
case 'CS':      // Cɸ��
    $where_div = "proc.parts_no LIKE 'C%' AND plan.kouji_no NOT LIKE '%SC%' AND proc.locate != '52   '";
    break;
case 'L':       // L����
    $where_div = "proc.parts_no LIKE 'L%' AND proc.locate != '52   '";
    break;
case 'T':       // T����
    $where_div = "proc.parts_no LIKE 'T%' AND proc.locate != '52   '";
    break;
case 'F':       // F����
    $where_div = "proc.parts_no LIKE 'F%' AND proc.locate != '52   '";
    break;
case 'A':       // TNK����
    $where_div = "(proc.parts_no LIKE 'C%' or proc.parts_no LIKE 'L%' or proc.parts_no LIKE 'T%' or proc.parts_no LIKE 'F%') AND proc.locate != '52   '";
    break;
case 'N':       // NK���ץ�
    $where_div = "(proc.parts_no LIKE 'C%' or proc.parts_no LIKE 'L%' or proc.parts_no LIKE 'T%' or proc.parts_no LIKE 'F%') AND proc.locate = '52   '";
    break;
case 'NKB':
    $where_div = "plan.locate = '14'";
    break;
}
////////// ���դǶ��̤� where�������
if ($date == 'OLD') {
    //$where_date = 'proc.delivery <= ' . date('Ymd', mktime() - 86400) . 'AND proc.delivery >= ' . date('Ymd', mktime() - (86400*200));
    $where_date = 'proc.delivery <= ' . date('Ymd', mktime() - 86400) . 'AND proc.delivery >= 0';
    $orderby = 'ORDER BY proc.delivery ASC, data.date_issue ASC';
} else {
    $where_date = "proc.delivery = {$date}";
    $orderby = 'ORDER BY data.vendor ASC, proc.delivery ASC, data.date_issue ASC';
}

$view = 'OK';   // �������Ȥ�OK�ǹԤ�

////////// ����SQLʸ������
$query = "
    SELECT  proc.order_no           AS ��ʸ�ֹ�
            , substr(to_char(proc.order_date, 'FM9999/99/99'), 6, 5)          AS ȯ����
            , to_char(proc.sei_no,'FM0000000')        AS ��¤�ֹ�
            , proc.parts_no           AS �����ֹ�
            , proc.vendor             AS ȯ���襳����
            , (SELECT sub_order.order_q - sub_order.cut_siharai FROM order_process AS sub_order WHERE sub_order.sei_no=proc.sei_no AND to_char(sub_order.order_no, 'FM9999999') LIKE '%0' LIMIT 1)
                                      AS ��ʸ��
            , proc.order_price        AS ñ��
            , substr(to_char(proc.delivery, 'FM9999/99/99'), 6, 5)            AS Ǽ��
            , plan.kouji_no           AS �����ֹ�
            , proc.pro_mark           AS ����
            , proc.mtl_cond           AS �������
            , proc.pro_kubun          AS ����ñ����ʬ
            , proc.order_q            AS ����ʸ��
            , proc.locate             AS Ǽ�����
            , proc.kamoku             AS ����
            , proc.order_ku           AS ȯ���ʬ
            , proc.plan_cond          AS ȯ��ײ��ʬ
            , proc.next_pro           AS ������
            , trim(substr(mast.name, 1, 8))           AS ȯ����̾
            , trim(substr(item.midsc, 1, 13))         AS ����̾
            , CASE
                    WHEN trim(item.mzist) = '' THEN '---'         --NULL�Ǥʤ��ƥ��ڡ�������ޤäƤ�����Ϥ��졪
                    ELSE substr(item.mzist, 1, 8)
              END                     AS ���
            , CASE
                    WHEN trim(item.mepnt) = '' THEN '---'         --NULL�Ǥʤ��ƥ��ڡ�������ޤäƤ�����Ϥ��졪
                    ELSE substr(item.mepnt, 1, 8)
              END                     AS �Ƶ���
    FROM
        order_process   AS proc
    LEFT OUTER JOIN
        order_data      AS data
                                using(sei_no, order_no, vendor)
    LEFT OUTER JOIN
        order_plan      AS plan
                                using(sei_no)
    LEFT OUTER JOIN
        vendor_master   AS mast
                                on(proc.vendor = mast.vendor)
    LEFT OUTER JOIN
        miitem          AS item
                                on(proc.parts_no = item.mipn)
    WHERE
        {$where_date}
        AND
        proc.sei_no > 0                 -- ��¤�ѤǤ���
        AND
        to_char(proc.order_no, 'FM9999999') NOT LIKE '%0'     -- �鹩�������
        AND
        to_char(proc.order_no, 'FM9999999') NOT LIKE '_00000_'    -- ������֤�ʪ�����
        AND
        proc.plan_cond='R'              -- ��ʸ��ͽ��Τ��
        AND
        data.order_no IS NULL           -- ��ʸ�񤬼ºݤ�̵��ʪ
        AND
        (SELECT sub_order.order_q - sub_order.cut_siharai FROM order_process AS sub_order WHERE sub_order.sei_no=proc.sei_no AND to_char(sub_order.order_no, 'FM9999999') LIKE '%0' LIMIT 1) > 0
                                        -- �鹩�������ڤ���Ƥ��ʤ�ʪ
        AND
        {$where_div}
    {$orderby}
    OFFSET 0
    LIMIT 1000
";
$res = array();
if (($rows = getResult($query, $res)) < 1) {
    $_SESSION['s_sysmsg'] = '��ĥǡ���������ޤ���';
    $view = 'NG';
}

/////////// HTML Header ����Ϥ��ƥ���å��������
$menu->out_html_header();
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=EUC-JP">
<meta http-equiv="Content-Style-Type" content="text/css">
<title><?php echo $menu->out_title() ?></title>
<?php echo $menu->out_css() ?>
<style type='text/css'>
<!--
th {
    font-size:      11.5pt;
    font-weight:    bold;
    font-family:    monospace;
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
    left: 350px;
}
.winbox {
    border-style: solid;
    border-width: 1px;
    border-top-color:       #FFFFFF;
    border-left-color:      #FFFFFF;
    border-right-color:     #999999;
    border-bottom-color:    #999999;
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
function order_no_confirm(order_no, parts_no, parts_name) {
    alert('�����ֹ桧' + parts_no + '\n\n����̾�Ρ�' + parts_name + " ��\n\n��ʸ�ֹ�� " + order_no + " �Ǥ���");
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
<form name='inspection_form' method='get' action='inspection_recourse_regist.php' target='_self'>
    <input type='hidden' name='retUrl' value='<?php echo $menu->out_self(), '?' . $_SERVER['QUERY_STRING'] ?>'>
    <input type='hidden' name='order_seq' value=''>
</form>
</head>
<body onLoad='winActiveChk()'>
    <center>
        <?php if ($view != 'OK') { ?>
        <table border='0' class='msg'>
            <tr>
                <td>
                    <b style='color: teal;'>�ǡ���������ޤ���</b>
                </td>
            </tr>
        </table>
        <?php } else { ?>
        <!-------------- �ܺ٥ǡ���ɽ���Τ����ɽ����� -------------->
        <table class='item' bgcolor='#d6d3ce'  border='1' cellspacing='0' cellpadding='2'>
           <tr><td> <!-- ���ߡ�(�ǥ�������) -->
        <table class='winbox_field' width=100% align='center' border='1' cellspacing='0' cellpadding='1'>
            <!--
            <th class='winbox' nowrap width='30'>No</th>
            <?php if ($date == 'OLD') { ?>
            <th class='winbox' nowrap width='45' style='font-size:9.5pt;'>Ǽ ��</th>
            <?php } else { ?>
            <th class='winbox' nowrap width='45' style='font-size:9.5pt;'>ȯ����</th>
            <?php } ?>
            <th class='winbox' nowrap width='60' style='font-size:9.5pt;'>��¤�ֹ�</th>
            <th class='winbox' nowrap width='80'>�����ֹ�</th>
            <th class='winbox' nowrap width='145'>����̾</th>
            <th class='winbox' nowrap width='85'>��&nbsp;&nbsp;��</th>
            <th class='winbox' nowrap width='90'>�Ƶ���</th>
            <th class='winbox' nowrap width='70'>��ʸ��</th>
            <th class='winbox' nowrap width='20' style='font-size:10.5pt;'>����</th>
            <th class='winbox' nowrap width='130'>ȯ����̾</th>
            -->
        <?php
            $i = 0;
            foreach ($res as $rec) {
                $i++;
                // echo "<tr class='table_font' onDblClick='inspection_recourse(\"{$rec['��ʸ�ֹ�']}\",\"{$rec['�����ֹ�']}\",\"{$rec['����̾']}\")'>\n";
                // echo "<tr class='table_font'>\n";
                echo "<tr class='table_font' onDblClick='order_no_confirm(\"{$rec['��ʸ�ֹ�']}\",\"{$rec['�����ֹ�']}\",\"{$rec['����̾']}\")'>\n";
                echo "<td class='winbox' align='right'  width='30'  bgcolor='#d6d3ce'>{$i}</td>\n";
                if ($date == 'OLD') {
                    echo "<td class='winbox' align='center' width='45'  bgcolor='#d6d3ce'>{$rec['Ǽ��']}</td>\n";
                } else {
                    echo "<td class='winbox' align='center' width='45'  bgcolor='#d6d3ce'>{$rec['ȯ����']}</td>\n";
                }
                echo "<td class='winbox' align='center' width='60'  bgcolor='#d6d3ce'>{$rec['��¤�ֹ�']}</td>\n";
                echo "<td class='winbox' align='center' width='80'  bgcolor='#d6d3ce'><a class='link' href='javascript:win_open(\"{$menu->out_action('�߸�ͽ��')}?showMenu=CondForm&targetPartsNo=" . urlencode($rec['�����ֹ�']) . "&noMenu=yes\");' target='_self' style='text-decoration:none;'>{$rec['�����ֹ�']}</a></td>\n";
                echo "<td class='winbox' align='left'   width='145' bgcolor='#d6d3ce'>" . mb_convert_kana($rec['����̾'], 'k') . "</td>\n";
                echo "<td class='winbox' align='left'   width='85'  bgcolor='#d6d3ce'>" . mb_convert_kana($rec['���'], 'k') . "</td>\n";
                echo "<td class='winbox' align='left'   width='90'  bgcolor='#d6d3ce'>" . mb_convert_kana($rec['�Ƶ���'], 'k') . "</td>\n";
                echo "<td class='winbox' align='right'  width='70'  bgcolor='#d6d3ce'>" . number_format($rec['��ʸ��'], 0) . "</td>\n";
                echo "<td class='winbox' align='center' width='20'  bgcolor='#d6d3ce'>{$rec['����']}</td>\n";
                echo "<td class='winbox' align='left'   width='130' bgcolor='#d6d3ce'>{$rec['ȯ����̾']}</td>\n";
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
