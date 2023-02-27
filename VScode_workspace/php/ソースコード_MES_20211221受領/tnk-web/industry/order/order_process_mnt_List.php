<?php
//////////////////////////////////////////////////////////////////////////////
// ȯ�������ƥʥ�(ȯ������ݼ�)   List�ե졼��                      //
// Copyright (C) 2004-2006 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp      //
// Changed history                                                          //
// 2004/11/27 Created  order_process_mnt_List.php                           //
// 2004/11/30 �� JavaScript�� delete ������ɤϻȤ��ʤ���                 //
// 2005/02/10 $_REQUEST['sei_no']��Ŭ�������å����ɲ�                       //
// 2005/07/26 $menu->out_alert_java()��</html>�γ��ˤ��ä��Τ���¦�ؽ���    //
//            ��ʸ�ֹ�Ƭ6/7�����б� order by �˼����ɲ�                     //
// 2006/10/06 �ƹ�������ʸ��ȯ�ԺѤߤΤ�Τ����ڤ�����å��򤷤�ɽ��        //
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
// $menu->set_site(30, 999);                // site_index=30(������˥塼) site_id=999(̤��)
////////////// target����
$menu->set_target('_parent');               // �ե졼���Ǥ�������target°����ɬ��

//////////// ��ʬ��ե졼��������Ѥ���
// $menu->set_self(INDUST . 'order/order_process_mnt.php');
//////////// �ƽ����action̾�ȥ��ɥ쥹����
// $menu->set_action('��ž�����', EQUIP2 . 'work/equip_work_graph.php');
// $menu->set_action('���߲�ưɽ', EQUIP2 . 'work/equip_work_chart.php');
// $menu->set_action('�������塼��', EQUIP2 . 'plan/equip_plan_graph.php');
//////////// �����ȥ�̾(�������Υ����ȥ�̾�ȥե�����Υ����ȥ�̾)
$menu->set_title('ȯ�������ƥʥ�List');   // �����ȥ������ʤ���IE�ΰ����ΥС�������ɽ���Ǥ��ʤ��Զ�礢��

///////// �ѥ�᡼���������å�(����Ū�˥��å���󤫤����)
if (isset($_REQUEST['sei_no'])) {
    $sei_no = $_REQUEST['sei_no'];
    if (is_numeric($sei_no)) {
        if (strlen($sei_no) == 7) {
            $view = 'OK';
        } else {
            $view = 'NG';
        }
    } else {
        $view = 'NG';
    }
} else {
    $sei_no = '';                           // �����(���ץ�)���ޤ��̣��̵��
    $view = 'NG';
}
if (isset($_REQUEST['del_exec'])) {
    $order_no = $_REQUEST['order_no'];
    $vendor   = $_REQUEST['vendor'];
    $delete   = $_REQUEST['del_exec'];      // �� JavaScript�� delete ������ɤϻȤ��ʤ���
} else {
    $delete = '';
}

while ($delete) {
    $query = "select order_no from order_process where sei_no={$sei_no} and order_no={$order_no} and vendor='{$vendor}'";
    $res = array();
    if (($rows = getResult2($query, $res)) == 1) {
        $sql = "delete from order_process where sei_no={$sei_no} and order_no={$order_no} and vendor='{$vendor}'";
        if (query_affected($sql) <= 0) {
            $_SESSION['s_sysmsg'] = '�������ޤ���Ǥ����� ����ô���Ԥ�Ϣ���Ʋ�������';
        }
    } else {
        $_SESSION['s_sysmsg'] = "��¤�ֹ桧{$sei_no} ��ʸ�ֹ桧{$order_no} ȯ���襳���ɡ�{$vendor} �Ǻ���ǡ��������Ĥ���ޤ���";
    }
    break;
}

while ($view == 'OK') {
    ////////// ����SQLʸ������
    $query = "select proc.order_no          AS ��ʸ�ֹ�
                  , proc.vendor             AS ȯ���襳����
                  , trim(substr(mast.name, 1, 8))
                                            AS ȯ����̾
                  , substr(to_char(proc.delivery, 'FM9999/99/99'), 3, 8)
                                            AS Ǽ��
                  , proc.mtl_cond           AS �������         -- 1=���� 2=ͭ�� 3=̵��
                  , proc.pro_kubun          AS ����ñ����ʬ     -- 1=��³ 2=���� 3=����Τ� 4=̤��
                  , proc.pro_mark           AS ����
                  , proc.order_price        AS ñ��
                  , proc.locate             AS Ǽ�����
                  , proc.next_pro           AS ������
                  , proc.plan_cond          AS ��ʸ��
                  , CASE
                        WHEN proc.plan_cond = 'O' AND (proc.order_q - proc.cut_siharai) <= 0 THEN '����'
                        ELSE '&nbsp;'
                    END                     AS ����
                  
                  , CASE
                          WHEN trim(plan.kouji_no) = '' THEN '&nbsp;'
                          ELSE trim(plan.kouji_no)
                    END                     AS �����ֹ�
                  , plan.order_q            AS ȯ���
                  , proc.order_date         AS ȯ����
                  , proc.kamoku             AS ����             -- ����(��ݲ���1--9)
                  , proc.order_ku           AS ȯ���ʬ         -- (1=���� 2=�ɲù� 3=����/���� 4=�Ƿ��� 5=������)
                  , proc.parts_no           AS �����ֹ�
                  , trim(substr(item.midsc, 1, 13))
                                            AS ����̾
                  , CASE
                          WHEN trim(item.mzist) = '' THEN '---'         --NULL�Ǥʤ��ƥ��ڡ�������ޤäƤ�����Ϥ��졪
                          ELSE substr(item.mzist, 1, 8)
                    END                     AS ���
                  , CASE
                          WHEN trim(item.mepnt) = '' THEN '---'         --NULL�Ǥʤ��ƥ��ڡ�������ޤäƤ�����Ϥ��졪
                          ELSE substr(item.mepnt, 1, 8)
                    END                     AS �Ƶ���
            from
                order_process   AS proc
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
                proc.sei_no = {$sei_no}
            order by
                substr(to_char(proc.order_no, 'FM0000000'), 2, 7) ASC, proc.delivery ASC
            offset 0
            limit 20
    ";
    $res = array();
    if (($rows = getResult($query, $res)) < 1) {
        $_SESSION['s_sysmsg'] = "��¤�ֹ桧{$sei_no} �Ǥϥǡ���������ޤ���";
        $view = 'NG';
    }
    break;
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
<?= $menu->out_css() ?>
<style type='text/css'>
<!--
th {
    font-size:          11.5pt;
    font-weight:        bold;
    font-family:        monospace;
    background-color:   #ffffc6;
    color:              blue;
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
    // ����ˡ <body onLoad="setInterval('winActiveChk()',100)"> ���� <body onLoad='winActiveChk()'>
    // <input type='button' value='TEST' onClick="window.opener.location.reload()">
    // parent.Header.�ؿ�̾() or ���֥�������;
}
function del_confirm(sei_no, order_no, vendor, vendor_name) {
    if (confirm('��ʸ�ֹ桧' + order_no + '\n\nȯ��̾��' + vendor_name + " ��\n\nȯ�����������ޤ���\n\n�������Ǥ�����")) {
        // �¹Ԥ��ޤ���
        document.del_form.sei_no.value   = sei_no;
        document.del_form.order_no.value = order_no;
        document.del_form.vendor.value   = vendor;
        document.del_form.del_exec.value = 'Execute';
        document.del_form.submit();
    } else {
        alert('��ä��ޤ�����');
    }
}
// -->
</script>
<form name='del_form' method='post' action='<?=$menu->out_self()?>' target='_self'>
    <input type='hidden' name='sei_no'   value=''>
    <input type='hidden' name='order_no' value=''>
    <input type='hidden' name='vendor'   value=''>
    <input type='hidden' name='del_exec' value=''>
</form>
</head>
<body>
    <center>
        <?php if ($view != 'OK') { ?>
        <table border='0' class='msg'>
            <tr>
                <td>
                    <b style='color: teal;'>��¤�ֹ����ꤷ�Ʋ�������</b>
                </td>
            </tr>
        </table>
        <?php } else { ?>
        <!-------------- �ܺ٥ǡ���ɽ���Τ����ɽ����� -------------->
        <table bgcolor='#d6d3ce'  border='1' cellspacing='0' cellpadding='3'>
           <tr><td> <!-- ���ߡ�(�ǥ�������) -->
        <table class='winbox_field' width=100% align='center' border='1' cellspacing='0' cellpadding='2'>
            <th class='winbox' nowrap width='30'>No</th>
            <th class='winbox' nowrap width='40'>���</th>
            <th class='winbox' nowrap width='60' style='font-size:9.5pt;'>��ʸ�ֹ�</th>
            <th class='winbox' nowrap width='45' style='font-size:9.5pt;'>������</th>
            <th class='winbox' nowrap width='145'>ȯ����̾</th>
            <th class='winbox' nowrap width='45' style='font-size:9.5pt;'>Ǽ ��</th>
            <th class='winbox' nowrap width='30' style='font-size:10.5pt;'>�ٵ�</th>
            <th class='winbox' nowrap width='30' style='font-size:10.5pt;'>��ʬ</th>
            <th class='winbox' nowrap width='30' style='font-size:10.5pt;'>����</th>
            <th class='winbox' nowrap width='60' style='font-size:10.5pt;'>ñ��</th>
            <th class='winbox' nowrap width='30' style='font-size:10.5pt;'>Ǽ��</th>
            <th class='winbox' nowrap width='70'>������</th>
            <th class='winbox' nowrap width='70'>��ʸ��</th>
            <th class='winbox' nowrap width='60'>����</th>
            <!--
            <th class='winbox' nowrap width='85'>��&nbsp;&nbsp;��</th>
            <th class='winbox' nowrap width='90'>�Ƶ���</th>
            <th class='winbox' nowrap width='20' style='font-size:10.5pt;'>����</th>
            <th class='winbox' nowrap width='130'>ȯ����̾</th>
            -->
        <?php
            $i = 0;
            foreach ($res as $rec) {
                $i++;
                if ($rec['����'] != '����') {
                    echo "<tr class='table_font'>\n";
                } else {
                    echo "<tr class='table_font' style='color:gray;'>\n";
                }
                echo "<td class='winbox' align='right'  width='30'  bgcolor='#d6d3ce'>{$i}</td>\n";
                echo "<td class='winbox' align='center' width='40'>\n";
                echo "  <input type='button' name='delete_chk' value='���' style='color:red;' onClick='del_confirm(\"{$sei_no}\", \"{$rec['��ʸ�ֹ�']}\", \"{$rec['ȯ���襳����']}\", \"{$rec['ȯ����̾']}\")'>\n";
                echo "</td>\n";
                echo "<td class='winbox' align='center' width='60'  bgcolor='#d6d3ce'>{$rec['��ʸ�ֹ�']}</td>\n";
                echo "<td class='winbox' align='center' width='45'  bgcolor='#d6d3ce'>{$rec['ȯ���襳����']}</td>\n";
                echo "<td class='winbox' align='center' width='145' bgcolor='#d6d3ce'>{$rec['ȯ����̾']}</td>\n";
                echo "<td class='winbox' align='center' width='45'  bgcolor='#d6d3ce'>{$rec['Ǽ��']}</td>\n";
                echo "<td class='winbox' align='center' width='30'  bgcolor='#d6d3ce'>{$rec['�������']}</td>\n";
                echo "<td class='winbox' align='center' width='30'  bgcolor='#d6d3ce'>{$rec['����ñ����ʬ']}</td>\n";
                echo "<td class='winbox' align='center' width='30'  bgcolor='#d6d3ce'>{$rec['����']}</td>\n";
                echo "<td class='winbox' align='right'  width='60'  bgcolor='#d6d3ce'>", number_format($rec['ñ��'], 2), "</td>\n";
                echo "<td class='winbox' align='center' width='30'  bgcolor='#d6d3ce'>{$rec['Ǽ�����']}</td>\n";
                echo "<td class='winbox' align='center' width='70'  bgcolor='#d6d3ce'>{$rec['������']}</td>\n";
                echo "<td class='winbox' align='center' width='70'  bgcolor='#d6d3ce'>{$rec['��ʸ��']}</td>\n";
                echo "<td class='winbox' align='center' width='60'  bgcolor='#d6d3ce'>{$rec['����']}</td>\n";
                echo "</tr>\n";
            }
        ?>
        </table> <!----- ���ߡ� End ----->
            </td></tr>
        </table>
        
        <br>
        
        <table bgcolor='#d6d3ce'  border='1' cellspacing='0' cellpadding='3'>
           <tr><td> <!-- ���ߡ�(�ǥ�������) -->
        <table class='winbox_field' width=100% align='center' border='1' cellspacing='0' cellpadding='2'>
            <tr>
                <td class='winbox' align='center' width='100'  bgcolor='#d6d3ce'>�����ֹ�</td>
                <td class='winbox' align='center' width='120'  bgcolor='#d6d3ce'><?=$res[0]['�����ֹ�']?></td>
            </tr>
            <tr>
                <td class='winbox' align='center' width='100'  bgcolor='#d6d3ce'>����̾</td>
                <td class='winbox' align='center' width='150'  bgcolor='#d6d3ce'><?=$res[0]['����̾']?></td>
            </tr>
            <tr>
                <td class='winbox' align='center' width='100'  bgcolor='#d6d3ce'>�ࡡ��</td>
                <td class='winbox' align='center' width='100'  bgcolor='#d6d3ce'><?=$res[0]['���']?></td>
            </tr>
            <tr>
                <td class='winbox' align='center' width='100'  bgcolor='#d6d3ce'>�Ƶ���</td>
                <td class='winbox' align='center' width='100'  bgcolor='#d6d3ce'><?=$res[0]['�Ƶ���']?></td>
            </tr>
            <tr>
                <td class='winbox' align='center' width='100'  bgcolor='#d6d3ce'>�����ֹ�</td>
                <td class='winbox' align='center' width='100'  bgcolor='#d6d3ce'><?=$res[0]['�����ֹ�']?></td>
            </tr>
            <tr>
                <td class='winbox' align='center' width='100'  bgcolor='#d6d3ce'>ȯ���</td>
                <td class='winbox' align='center' width='100'  bgcolor='#d6d3ce'><?php echo number_format($res[0]['ȯ���']) ?></td>
            </tr>
        </table> <!----- ���ߡ� End ----->
            </td></tr>
        </table>
        <?php } ?>
    </center>
</body>
<?=$menu->out_alert_java()?>
</html>
<?php
ob_end_flush(); // ���ϥХåե���gzip���� END
?>
