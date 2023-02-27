<?php
//////////////////////////////////////////////////////////////////////////////
// Ǽ��ͽ��ξȲ�(�����λŻ����İ�) ���٤򥦥���ɥ�ɽ��   List�ե졼��     //
// Copyright (C) 2004-2008 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp      //
// Changed history                                                          //
// 2004/09/30 Created  order_details_List.php                               //
// 2004/10/06 Ǽ���٤��ʪ��ȯ�����Ǥʤ�Ǽ����ɽ�������ڤ���Ƥ��ʤ�����ɲ�//
// 2004/10/12 Ǽ���Υǡ�����data.delivery��proc.delivery���ѹ���Ǽ���ѹ��б�//
// 2004/11/25 winActiveChk()��Timer��� (��������������ͤ˰��Ū�˲��)    //
// 2004/12/01 proc.delivery >= ' . date('Ymd', mktime() - (86400*124)) �ɲ� //
// 2004/12/28 �嵭�� (86400*124) �� (86400*200) ���ѹ�                      //
// 2005/05/18 ȯ����̾�򥯥�å������ȯ���襳���ɤ�Ȳ�Ǥ��뵡ǽ���ɲ�    //
// 2005/09/20 IE5.0�桼�����Τ����winActiveChk()��ñ�㲽window.focus()�Τ� //
// 2006/08/02 ���ʥ��롼�פˣΣˣ¤��ɲ� ���Τ��� SQL�� order_plan �ɲ�     //
// 2007/02/27 �����ֹ�˥�󥯤��ɲä��ƺ߸˷���ͽ��Ȳ�POPUP Window��ɽ��//
// 2007/04/17 JavaScript��win_open()��URL���Ϥ���#1���������ֹ椬�Ϥ�ʤ�   //
//           <a href='javascript:win_open(...)'�� <a href='javascript:void()//
//            onClick='win_open(...)'�ν񼰤��ѹ��ˤ�������ֹ��#1�����б� //
// 2007/05/08 $orderby���ɲä���Ǽ���٤�ǤϤʤ����Υꥹ�Ȥ�ȯ������ɽ��//
// 2007/05/11 �ǥ��쥯�ȥ�� order/ �� order/order_details/ ���ѹ�          //
// 2008/09/24 ���Ƥ�Ǽ���٤��ɽ��������ѹ���ɽ���帵���ᤷ��         ��ë //
// 2008/10/06 ���Ƥ�Ǽ���٤��ɽ��������ѹ���ɽ���帵���ᤷ��         ��ë //
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
$menu->set_title('Ǽ��ͽ�����٤ξȲ�');     // �����ȥ������ʤ���IE�ΰ����ΥС�������ɽ���Ǥ��ʤ��Զ�礢��

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
    $where_div = "data.parts_no like 'C%' and proc.locate != '52   '";
    break;
case 'SC':      // C����
    $where_div = "data.parts_no like 'C%' and data.kouji_no like '%SC%' and proc.locate != '52   '";
    break;
case 'CS':      // Cɸ��
    $where_div = "data.parts_no like 'C%' and data.kouji_no not like '%SC%' and proc.locate != '52   '";
    break;
case 'L':       // L����
    $where_div = "data.parts_no like 'L%' and proc.locate != '52   '";
    break;
case 'T':       // T����
    $where_div = "data.parts_no like 'T%' and proc.locate != '52   '";
    break;
case 'F':       // F����
    $where_div = "data.parts_no like 'F%' and proc.locate != '52   '";
    break;
case 'A':       // TNK����
    $where_div = "(data.parts_no like 'C%' or data.parts_no like 'L%' or data.parts_no like 'T%' or data.parts_no like 'F%') and proc.locate != '52   '";
    break;
case 'N':       // NK���ץ�
    $where_div = "(data.parts_no like 'C%' or data.parts_no like 'L%' or data.parts_no like 'T%' or data.parts_no like 'F%') and proc.locate = '52   '";
    break;
case 'NKB':     // NKB
    $where_div = "plan.locate = '14'";
    break;
}
////////// ���դǶ��̤� where�������
if ($date == 'OLD') {
    $where_date = 'proc.delivery <= ' . date('Ymd', mktime() - 86400) . 'and proc.delivery >= ' . date('Ymd', mktime() - (86400*200));
    $orderby = 'order by proc.delivery ASC, data.date_issue ASC';
} else {
    $where_date = "proc.delivery = {$date}";
    $orderby = 'order by data.vendor ASC, proc.delivery ASC, data.date_issue ASC';
}

$view = 'OK';   // �������Ȥ�OK�ǹԤ�

////////// ����SQLʸ������
$query = "select    data.order_seq          AS ȯ��Ϣ��
                  , substr(to_char(data.date_issue, 'FM9999/99/99'), 6, 5)          AS ȯ����
                  , data.pre_seq            AS ����Ϣ��
                  , to_char(data.sei_no,'FM0000000')        AS ��¤�ֹ�
                  , data.order_no           AS ��ʸ�ֹ�
                  , data.parts_no           AS �����ֹ�
                  , data.vendor             AS ȯ���襳����
                  , data.order_q            AS ��ʸ��
                  , data.order_price        AS ñ��
                  , substr(to_char(proc.delivery, 'FM9999/99/99'), 6, 5)            AS Ǽ��
                  , data.kouji_no           AS �����ֹ�
                  , proc.pro_mark           AS ����
                  , proc.mtl_cond           AS �������
                  , proc.pro_kubun          AS ����ñ����ʬ
                  , proc.order_date         AS ȯ����
                  , proc.order_q            AS ����ʸ��
                  , proc.locate             AS Ǽ�����
                  , proc.kamoku             AS ����
                  , proc.order_ku           AS ȯ���ʬ
                  , proc.plan_cond          AS ȯ��ײ��ʬ
                  , proc.next_pro           AS ������
                  , trim(substr(mast.name, 1, 8))           AS ȯ����̾
                  , trim(mast.name)                         AS vendor_name
                  , trim(substr(item.midsc, 1, 13))         AS ����̾
                  , CASE
                          WHEN trim(item.mzist) = '' THEN '---'         --NULL�Ǥʤ��ƥ��ڡ�������ޤäƤ�����Ϥ��졪
                          ELSE substr(item.mzist, 1, 8)
                    END                     AS ���
                  , CASE
                          WHEN trim(item.mepnt) = '' THEN '---'         --NULL�Ǥʤ��ƥ��ڡ�������ޤäƤ�����Ϥ��졪
                          ELSE substr(item.mepnt, 1, 8)
                    END                     AS �Ƶ���
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
            where
                {$where_date}
                and
                uke_date <= 0       -- ̤Ǽ��ʬ
                and
                ken_date <= 0       -- ̤����ʬ
                and
                data.sei_no > 0     -- ��¤�ѤǤ���
                and
                (data.order_q - data.cut_genpin) > 0  -- ���ڤ���Ƥ��ʤ�ʪ
                and
                {$where_div}
            {$orderby}
            offset 0
            limit 1000
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
    window.focus();
    return;
    /***** �ʲ��ν�����setInterval()����Ѥ������˻Ȥ� *****/
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
<form name='inspection_form' method='get' action='../inspection_recourse_regist.php' target='_self'>
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
                echo "<tr class='table_font' onDblClick='inspection_recourse(\"{$rec['ȯ��Ϣ��']}\",\"{$rec['�����ֹ�']}\",\"{$rec['����̾']}\")'>\n";
                echo "<td class='winbox' align='right'  width='30'  bgcolor='#d6d3ce'>{$i}</td>\n";
                if ($date == 'OLD') {
                    echo "<td class='winbox' align='center' width='45'  bgcolor='#d6d3ce'>{$rec['Ǽ��']}</td>\n";
                } else {
                    echo "<td class='winbox' align='center' width='45'  bgcolor='#d6d3ce'>{$rec['ȯ����']}</td>\n";
                }
                echo "<td class='winbox' align='center' width='60'  bgcolor='#d6d3ce'>{$rec['��¤�ֹ�']}</td>\n";
                echo "<td class='winbox' align='center' width='80'  bgcolor='#d6d3ce' onClick='win_open(\"{$menu->out_action('�߸�ͽ��')}?showMenu=CondForm&noMenu=yes&targetPartsNo=" . urlencode($rec['�����ֹ�']) . "\");'>\n";
                echo "    <a class='link' href='javascript:void(0);' target='_self' style='text-decoration:none;'>{$rec['�����ֹ�']}</a></td>\n";
                echo "<td class='winbox' align='left'   width='145' bgcolor='#d6d3ce'>" . mb_convert_kana($rec['����̾'], 'k') . "</td>\n";
                echo "<td class='winbox' align='left'   width='85'  bgcolor='#d6d3ce'>" . mb_convert_kana($rec['���'], 'k') . "</td>\n";
                echo "<td class='winbox' align='left'   width='90'  bgcolor='#d6d3ce'>" . mb_convert_kana($rec['�Ƶ���'], 'k') . "</td>\n";
                echo "<td class='winbox' align='right'  width='70'  bgcolor='#d6d3ce'>" . number_format($rec['��ʸ��'], 0) . "</td>\n";
                echo "<td class='winbox' align='center' width='20'  bgcolor='#d6d3ce'>{$rec['����']}</td>\n";
                echo "<td class='winbox' align='left'   width='130' bgcolor='#d6d3ce' onClick='vendor_code_view(\"{$rec['ȯ���襳����']}\",\"{$rec['vendor_name']}\")'>{$rec['ȯ����̾']}</td>\n";
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
