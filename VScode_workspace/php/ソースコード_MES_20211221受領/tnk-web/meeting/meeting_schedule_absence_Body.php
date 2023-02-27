<?php
////////////////////////////////////////////////////////////////////////////////////////////
// ����� �Ժ߼Ԥ򥦥���ɥ�ɽ��   List�ե졼��                                           //
// Copyright (C) 2019-2019 Norihisa.Ohya norihisa_ooya@nitto-kohki.co.jp                  //
// Changed history                                                                        //
// 2019/03/15 Created  meeting_schedule_absence_Body.php                                  //
// 2020/08/07 ���ռ�Ĺ������ô����Ĺ��ɽ�����ʤ��褦���ɲá�SQL���                       //
////////////////////////////////////////////////////////////////////////////////////////////
ini_set('error_reporting', E_ALL);          // E_ALL='2047' debug ��
// ini_set('display_errors', '1');             // Error ɽ�� ON debug �� ��꡼���女����
ini_set('max_execution_time', 60);          // ����¹Ի���=60�� WEB CGI��
ob_start('ob_gzhandler');                   // ���ϥХåե���gzip����
session_start();                            // ini_set()�μ��˻��ꤹ�뤳�� Script �Ǿ��
require_once ('../function.php');     // define.php �� pgsql.php �� require_once ���Ƥ���
require_once ('../MenuHeader.php');   // TNK ������ menu class
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
//$menu->set_action('�߸�ͽ��',   INDUST . 'parts/parts_stock_plan/parts_stock_plan_Main.php');
//$menu->set_action('�߸˷���',   INDUST . 'parts/parts_stock_history/parts_stock_view.php');
//////////// �����ȥ�̾(�������Υ����ȥ�̾�ȥե�����Υ����ȥ�̾)
$menu->set_title('�Ժ߼ԾȲ�');     // �����ȥ������ʤ���IE�ΰ����ΥС�������ɽ���Ǥ��ʤ��Զ�礢��

$date = date('Ymd');                    // �����(����)�㳰ȯ���ξ����б�

$view = 'OK';   // �������Ȥ�OK�ǹԤ�

////////// ����SQLʸ������ �Ժ߼ԡʽж��ǹ�ʤ���
$query = "select 
                ud.name AS ��̾
                ,
                sm.section_name AS ��°
                ,
                substr(timepro, 33, 4) AS �жл���
                ,
                substr(timepro, 41, 4) AS ��л���
                ,
                ud.pid AS ���̥�����
                ,
                substr(timepro, 173, 2) AS �Ժ���ͳ
            from user_detailes ud
            LEFT OUTER JOIN     cd_table        AS ct    USING(uid)
            left outer join timepro_daily_data on uid=substr(timepro, 3, 6) and {$date}=substr(timepro, 17, 8),section_master sm,position_master pm
            where (substr(timepro, 33, 4)='0000' or substr(timepro, 33, 4) IS NULL or substr(timepro, 41, 4) !='0000') and ud.sid=sm.sid and ud.sid!=90 and ud.sid!=95 and ud.sid!=31 and ud.retire_date is null and ud.uid!='000000' and ud.uid!='002321' and ud.uid!='010367' and ud.uid!='012866' and ud.uid!='014699' and ud.uid!='023856' and ud.pid=pm.pid and ud.pid!=15 and ud.pid!=130
            ORDER BY ct.orga_id ASC, ud.pid DESC, ud.name ASC

";
$res = array();
if (($rows = getResult($query, $res)) < 1) {
    
    $res[0]['��̾'] = '�ʤ�';
    $res[0]['��°'] = '�ʤ�';
    $res[0]['�Ժ���ͳ'] = '��';
    $num_res = count($res);
    
    //$_SESSION['s_sysmsg'] = '�Ժ߼Ԥ����ޤ���';
    //$view = 'NG';
} else {
    $num_res = count($res);
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
function input_details(comment) {
        alert('�ƥ���' + comment + '\n\n');
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
        <table class='item' width=100% bgcolor='#d6d3ce'  border='1' cellspacing='0' cellpadding='1'>
           <tr><td> <!-- ���ߡ�(�ǥ�������) -->
        <table class='winbox_field' width=100% align='center' border='1' cellspacing='0' cellpadding='3'>
        <?php
            $i = 0;
            foreach ($res as $rec) {
                $i++;
                //echo "<tr class='table_font' onDblClick='inspection_recourse(\"{$rec['ȯ��Ϣ��']}\",\"{$rec['�����ֹ�']}\",\"{$rec['����̾']}\")'>\n";
                /*
                if ($date == 'OLD') {
                */
                    // ��¤No.�������ֹ��ꥳ���Ȥ����
                    echo "<tr>\n";
                    echo "<td class='winbox' align='right'  width=' 10%'  bgcolor='#d6d3ce'>{$i}</td>\n";
                    echo "<td class='winbox' align='center' width=' 30%'  bgcolor='#d6d3ce'>{$rec['��̾']}</td>\n";
                    echo "<td class='winbox' align='center' width=' 45%'  bgcolor='#d6d3ce'>{$rec['��°']}</td>\n";
                    //echo "<td class='winbox' align='center' width=' 45%'  bgcolor='#d6d3ce'>{$rec['�Ժ���ͳ']}</td>\n";
                    
                    if($rec['��л���'] != '0000') {
                        echo "<td class='winbox' align='center' width='15%'  bgcolor='#d6d3ce'>���</td>\n";
                    } elseif($rec['�Ժ���ͳ'] == '11') {
                        echo "<td class='winbox' align='center' width='15%'  bgcolor='#d6d3ce'>ͭ��</td>\n";
                    } elseif($rec['�Ժ���ͳ'] == '12') {
                        echo "<td class='winbox' align='center' width='15%'  bgcolor='#d6d3ce'>���</td>\n";
                    } elseif($rec['�Ժ���ͳ'] == '13') {
                        echo "<td class='winbox' align='center' width='15%'  bgcolor='#d6d3ce'>̵��</td>\n";
                    } elseif($rec['�Ժ���ͳ'] == '14') {
                        echo "<td class='winbox' align='center' width='15%'  bgcolor='#d6d3ce'>��ĥ</td>\n";
                    } elseif($rec['�Ժ���ͳ'] == '15') {
                        echo "<td class='winbox' align='center' width='15%'  bgcolor='#d6d3ce'>����</td>\n";
                    } elseif($rec['�Ժ���ͳ'] == '16') {
                        echo "<td class='winbox' align='center' width='15%'  bgcolor='#d6d3ce'>�õ�</td>\n";
                    } elseif($rec['�Ժ���ͳ'] == '17') {
                        echo "<td class='winbox' align='center' width='15%'  bgcolor='#d6d3ce'>�Ļ�</td>\n";
                    } elseif($rec['�Ժ���ͳ'] == '18') {
                        echo "<td class='winbox' align='center' width='15%'  bgcolor='#d6d3ce'>Ĥ��</td>\n";
                    } elseif($rec['�Ժ���ͳ'] == '19') {
                        echo "<td class='winbox' align='center' width='15%'  bgcolor='#d6d3ce'>����</td>\n";
                    } elseif($rec['�Ժ���ͳ'] == '20') {
                        echo "<td class='winbox' align='center' width='15%'  bgcolor='#d6d3ce'>���</td>\n";
                    } elseif($rec['�Ժ���ͳ'] == '21') {
                        echo "<td class='winbox' align='center' width='15%'  bgcolor='#d6d3ce'>����</td>\n";
                    } elseif($rec['�Ժ���ͳ'] == '22') {
                        echo "<td class='winbox' align='center' width='15%'  bgcolor='#d6d3ce'>�ٿ�</td>\n";
                    } elseif($rec['�Ժ���ͳ'] == '23') {
                        echo "<td class='winbox' align='center' width='15%'  bgcolor='#d6d3ce'>ϫ��</td>\n";
                    } elseif($rec['�жл���'] == '0000') {
                        echo "<td class='winbox' align='center' width='15%'  bgcolor='#d6d3ce'>�Ժ�</td>\n";
                    } else {
                        echo "<td class='winbox' align='center' width='15%'  bgcolor='#d6d3ce'>��</td>\n";
                    }
                    
                    echo "</tr>\n";
                /*
                } else {
                    echo "<td class='winbox' align='right'  width=' 5%'  bgcolor='#d6d3ce'>{$i}</td>\n";
                    echo "<td class='winbox' align='center' width=' 7%'  bgcolor='#d6d3ce'>{$rec['ȯ����']}</td>\n";
                    echo "<td class='winbox' align='center' width='10%'  bgcolor='#d6d3ce'>{$rec['��¤�ֹ�']}</td>\n";
                    echo "<td class='winbox' align='center' width='13%'  bgcolor='#d6d3ce' onClick='win_open(\"{$menu->out_action('�߸�ͽ��')}?showMenu=CondForm&noMenu=yes&targetPartsNo=" . urlencode($rec['�����ֹ�']) . "\");'>\n";
                    echo "    <a class='link' href='javascript:void(0);' target='_self' style='text-decoration:none;'>{$rec['�����ֹ�']}</a></td>\n";
                    echo "<td class='winbox' align='left'   width='16%' bgcolor='#d6d3ce'>" . mb_convert_kana($rec['����̾'], 'k') . "</td>\n";
                    echo "<td class='winbox' align='left'   width='10%'  bgcolor='#d6d3ce'>" . mb_convert_kana($rec['���'], 'k') . "</td>\n";
                    echo "<td class='winbox' align='left'   width='10%'  bgcolor='#d6d3ce'>" . mb_convert_kana($rec['�Ƶ���'], 'k') . "</td>\n";
                    echo "<td class='winbox' align='right'  width='10%'  bgcolor='#d6d3ce'>" . number_format($rec['��ʸ��'], 0) . "</td>\n";
                    echo "<td class='winbox' align='center' width=' 4%'  bgcolor='#d6d3ce'>{$rec['����']}</td>\n";
                    echo "<td class='winbox' align='left'   width='15%' bgcolor='#d6d3ce' onClick='vendor_code_view(\"{$rec['ȯ���襳����']}\",\"{$rec['vendor_name']}\")'>{$rec['ȯ����̾']}</td>\n";
                    echo "</tr>\n";
                }
                */
            }
        /*
        if ($date == 'OLD') {       // Ǽ���٤�ξ�祳������Ͽ�������ܥ����ɽ������
        */
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
