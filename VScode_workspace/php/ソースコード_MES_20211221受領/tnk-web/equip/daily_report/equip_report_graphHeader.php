<?php
//////////////////////////////////////////////////////////////////////////////
// ������ư���������ƥ�� ��ž�����б� ����� ɽ��  Header�ե졼��          //
// Copyright (C) 2004-2005 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp      //
// Changed history                                                          //
// 2004/08/25 Created  equip_report_graphHeader.php                         //
// 2004/08/30 ̤������å�����($graph_endTime>"$end_date 08:30:00")���ѹ� //
// 2004/08/31 �����ܥ�����ɽ����̾�����ѹ�(���㤤���ɻ�)                  //
// 2004/11/30 ���ε����������Υǡ����б��Τ����ѹ� ��å������б�         //
// 2005/06/24 F2/F12��������뤿����б��� JavaScript�� set_focus()���ɲ�   //
// 2005/07/09 �嵭�� JavaScript ����ߤ� MenuHeader Class ���б�            //
// 2005/08/30 php5 �ذܹ�  (=& new �� = new)                                //
//////////////////////////////////////////////////////////////////////////////
ini_set('error_reporting', E_ALL);          // E_ALL='2047' debug ��
// ini_set('display_errors', '1');             // Error ɽ�� ON debug �� ��꡼���女����
ob_start('ob_gzhandler');                   // ���ϥХåե���gzip����
session_start();                            // ini_set()�μ��˻��ꤹ�뤳�� Script �Ǿ��
require_once ('../equip_function.php');     // TNK ������ function
require_once ('../../MenuHeader.php');      // TNK ������ menu class
require_once ('../EquipGraphClass_report.php');    // ������Ư���� Graph class
access_log();                               // Script Name �ϼ�ư����

///// TNK ���ѥ�˥塼���饹�Υ��󥹥��󥹤����
$menu = new MenuHeader();                   // ǧ�ڥ����å�0=���̰ʾ� �����=���å������ �����ȥ�̤����

////////////// ����������
$menu->set_site(40, 7);                     // site_index=40(������˥塼2) site_id=7(������ž����)
////////////// target����
// $menu->set_target('application');           // �ե졼���Ǥ�������target°����ɬ��
$menu->set_target('_parent');               // �ե졼���Ǥ�������target°����ɬ��

///// GET/POST�Υ����å�&����
$mac_no = @$_REQUEST['mac_no'];
if ($mac_no == '') {
    $reload = 'disabled';
} else {
    $reload = '';
    $_SESSION['mac_no'] = $mac_no;
}
if (isset($_REQUEST['str_date'])) {
    $str_date = $_REQUEST['str_date'];
    // �������˥��å�
    $end_date = date('Y/m/d', 
        mktime(0, 0, 0, substr($str_date, 5, 2), substr($str_date, 8, 2), substr($str_date, 0, 4))
         + 86400);
} else {
    $str_date = date('Y/m/d', mktime() - 86400);    // �����˥��å�
    $end_date = date('Y/m/d');
}
$_SESSION['str_date'] = $str_date;
$_SESSION['end_date'] = $end_date;

/////////// �����Τܤ������������
$mktime = (mktime() - 86400);
for ($rows_date=0; $rows_date<31; $rows_date++) {
    $set_date[$rows_date] = date('Y/m/d', $mktime);
    $mktime -= 86400;
}

/////////// ����դ�X���λ����ϰϤ����
if (isset($_REQUEST['equip_xtime'])) {
    $_SESSION['equip_xtime'] = $_REQUEST['equip_xtime'];
    // $_SESSION['equip_graph_page'] = 1;  // �����
} else {
    $_SESSION['equip_xtime'] = 24;
}
$equip_xtime = $_SESSION['equip_xtime'];

if (isset($_REQUEST['reset_page'])) {
    $_SESSION['equip_graph_page'] = 1;     // �����
}

///// �������ѿ��ν����
$mac_name   = '';
$rowspan    = 0;
$view       = 'NG';

$str_mac_state ='';
$str_work_cnt  ='';
$end_mac_state ='';
$end_work_cnt  ='';
$graph_str_mac_state = '';
$graph_str_work_cnt  = '';
$graph_end_mac_state = '';
$graph_end_work_cnt  = '';
$lotDateTime = array('strDate' => '��', 'strTime' => '��', 'endDate' => '��', 'endTime' => '��');
$graphDateTime = array('strDate' => '��', 'strTime' => '��', 'endDate' => '��', 'endTime' => '��');
$page_ctl_left  = 'disabled';
$page_ctl_right = 'disabled';

$factory = @$_SESSION['factory'];
//////////// �����ޥ��������������ֹ桦����̾�Υꥹ�Ȥ����
if ($factory == '') {
    $query = "select mac_no                 AS mac_no
                    , substr(mac_name,1,7)  AS mac_name
                from
                    equip_machine_master2
                where
                    survey='Y'
                    and
                    mac_no!=9999
                order by mac_no ASC
    ";
} else {
    $query = "select mac_no                 AS mac_no
                    , substr(mac_name,1,7)  AS mac_name
                from
                    equip_machine_master2
                where
                    survey='Y'
                    and
                    mac_no!=9999
                    and
                    factory='{$factory}'
                order by mac_no ASC
    ";
}
$res_sel = array();
if (($rows_sel = getResult($query, $res_sel)) < 1) {
    $_SESSION['s_sysmsg'] .= "<font color='yellow'>�����ޥ���������Ͽ������ޤ���</font>";
} else {
    $mac_no_name = array();
    for ($i=0; $i<$rows_sel; $i++) {
        ///// �����ֹ��̾�Τδ֤˥��ڡ����ɲ�
        $mac_no_name[$i] = $res_sel[$i]['mac_no'] . " " . trim($res_sel[$i]['mac_name']);
    }
}

while ($mac_no != '') {
    //////////////// �����ޥ��������鵡��̾�����
    $query = "select trim(mac_name) as mac_name from equip_machine_master2 where mac_no={$mac_no} limit 1";
    if (getUniResult($query, $mac_name) <= 0) {
        $mac_name = '��';   // error���ϵ���̾��֥��
    }
    //////////// �����08:30:00�γ��ϻ���(ľ��)�λؼ��ֹ桦�����Ⱦ��֡������������
    $query = "select siji_no
                    ,koutei
                    ,mac_state
                    ,work_cnt
                from
                    equip_work_log2
                where
                    equip_index2(mac_no, date_time) <= '{$mac_no}{$str_date} 08:30:00'
                order by
                    equip_index2(mac_no, date_time) DESC
                offset 0 limit 1
    ";
    $res_str = array();
    if (getResult($query, $res_str) <= 0) {
        $res_str['siji_no']   = '';
        $res_str['koutei']    = '';
    } else {
        $res_str['siji_no']   = $res_str[0]['siji_no'];
        $res_str['koutei']    = $res_str[0]['koutei'];
        $str_mac_state = $res_str[0]['mac_state'];
        $str_work_cnt  = $res_str[0]['work_cnt'];
    }
    //////////// ����꼡������08:30:00�ν�λ�����λؼ��ֹ桦�����Ⱦ��֡������������
    $query = "select siji_no
                    ,koutei
                    ,mac_state
                    ,work_cnt
                from
                    equip_work_log2
                where
                    equip_index2(mac_no, date_time) <= '{$mac_no}{$end_date} 08:30:00'
                order by
                    equip_index2(mac_no, date_time) DESC
                offset 0 limit 1
    ";
    $res_end = array();
    if (getResult($query, $res_end) <= 0) {
        $res_end['siji_no']   = '';
        $res_end['koutei']    = '';
        // $_SESSION['s_sysmsg'] .= "�ǡ����ʤ�";
    } else {
        $res_end['siji_no']   = $res_end[0]['siji_no'];
        $res_end['koutei']    = $res_end[0]['koutei'];
        $end_mac_state = $res_end[0]['mac_state'];
        $end_work_cnt  = $res_end[0]['work_cnt'];
    }
    //////////// �����ؼ��ֹ�ȹ����Υ��ޥ꡼�����
    $query = "select siji_no
                    ,koutei
                from
                    equip_work_log2
                where
                    equip_index2(mac_no, date_time) >= '{$mac_no}{$str_date} 08:30:00'
                    and
                    equip_index2(mac_no, date_time) <= '{$mac_no}{$end_date} 08:30:00'
                group by
                    siji_no, koutei
                offset 0
    ";
    $res_log = array();
    if (($rows_log=getResult($query, $res_log)) <= 0) {
        // ����Σ����֤ǥ���̵�����
        // ľ���Υǡ��������
        if ($res_str['siji_no'] == '') {    // ľ���Υǡ�����̵����� �����
            $_SESSION['s_sysmsg'] .= "<font color='yellow'>{$mac_no}��{$mac_name} {$str_date}�������Ǥϱ�ž�ǡ���������ޤ���</font>";
            break;
        }
        ///// �إå����򸫤ƴ�λ���Ƥ��뤫�����å�
        $query = "select CASE
                            WHEN end_timestamp IS NOT NULL THEN
                                end_timestamp < CAST('{$str_date} 08:30:00' AS TIMESTAMP)
                            ELSE
                                FALSE
                         END
                    from
                        equip_work_log2_header
                    where
                        mac_no = {$mac_no} and siji_no = {$res_str['siji_no']} and koutei = {$res_str['koutei']}
                    offset 0 limit 1
        ";
        if (getUniResult($query, $kanryou) <= 0) {
            $_SESSION['s_sysmsg'] .= "{$mac_no}��{$mac_name} {$res_str['siji_no']} �Ǥϥإå���������ޤ���";
            break;
        }
        // ľ���Υǡ�������λ�ʤξ���SQL�ǥ����å�����
        if ($kanryou == 't') { // FALSE='f'
            $_SESSION['s_sysmsg'] .= "{$mac_no}��{$mac_name} {$str_date}���������Ǥϱ�ž�ǡ���������ޤ���";
            break;
        }
        $res_log[0]['siji_no'] = $res_str['siji_no'];
        $res_log[0]['koutei']  = $res_str['koutei'];
        $rows_log = 1;
    } else {
        if ($res_str['siji_no'] == '') {    // ����ʤ������ǡ������б�
            // �ʲ���¹Ԥ���ȥ��顼�ˤϤʤ�ʤ��� 8:30���������Ȥ����褦�˸����뤿�ᥳ���ȤȤ���
            /***********************************
            //////////// �����08:30:00�γ��ϻ���(ľ��)�Υǡ�����̵��������������� �ؼ��ֹ桦�����Ⱦ��֡������������
            $query = "select siji_no
                    ,koutei
                    ,mac_state
                    ,work_cnt
                from
                    equip_work_log2
                where
                    equip_index2(mac_no, date_time) >= '{$mac_no}{$str_date} 08:30:00'
                order by
                    equip_index2(mac_no, date_time) ASC
                offset 0 limit 1
            ";
            $res_str = array();
            if (getResult($query, $res_str) <= 0) {
                $res_str['siji_no']   = '';
                $res_str['koutei']    = '';
            } else {
                $res_str['siji_no']   = $res_str[0]['siji_no'];
                $res_str['koutei']    = $res_str[0]['koutei'];
                $str_mac_state = $res_str[0]['mac_state'];
                $str_work_cnt  = $res_str[0]['work_cnt'];
            }
            ***********************************/
            $_SESSION['s_sysmsg'] .= "{$mac_no}��{$mac_name} {$str_date} ���ε����Ǥ��������������󳫻ϤȤʤ�ޤ���";
            break;
        }
    }
    
    //////////// �إå�����긫�Ф��Ѥ������ֹ桦�ײ������� ����ľ���Σ���ʬ
    $query = "select  parts_no
                    , plan_cnt
                from
                    equip_work_log2_header
                where
                    mac_no = {$mac_no} and siji_no = {$res_str['siji_no']} and koutei = {$res_str['koutei']}
    ";
    $res_head = array();
    if ( getResult($query, $res_head) <= 0) {
        $_SESSION['s_sysmsg'] .= "{$mac_no}��{$mac_name} {$str_date}�������Ǥϥإå���������ޤ���1";
        break;
    } else {
        $res_str['parts_no'] = $res_head[0]['parts_no'];
        $res_str['plan_cnt'] = $res_head[0]['plan_cnt'];
        $query = "select substr(midsc, 1, 12) as midsc, substr(mzist, 1, 8) as mzist from miitem where mipn='{$res_str['parts_no']}'";
        $res_mi = array();
        if ( getResult($query, $res_mi) <= 0) {
            $_SESSION['s_sysmsg'] .= "{$res_str['parts_no']}�����ʥޥ������μ����˼��ԡ�";
            break;
        } else {
            $res_str['parts_name'] = $res_mi[0]['midsc'];
            $res_str['parts_mate'] = $res_mi[0]['mzist'];
        }
    }
    //////////// �إå�����긫�Ф��Ѥ������ֹ桦�ײ������� �����ޥ꡼��̤����ʬ
    for ($r=0; $r<$rows_log; $r++) {
        if ( ($res_str['siji_no'] == $res_log[$r]['siji_no']) && ($res_str['koutei'] == $res_log[$r]['koutei']) ) {
            ///// ľ���Υǡ�����Ʊ���ʤ饳�ԡ�
            $res_log[$r]['parts_no']   = $res_str['parts_no'];
            $res_log[$r]['plan_cnt']   = $res_str['plan_cnt'];
            $res_log[$r]['parts_name'] = $res_str['parts_name'];
            $res_log[$r]['parts_mate'] = $res_str['parts_mate'];
        } else {
            $rowspan = 1;
            $query = "select  parts_no
                            , plan_cnt
                        from
                            equip_work_log2_header
                        where
                            mac_no = {$mac_no} and siji_no = {$res_log[$r]['siji_no']} and koutei = {$res_log[$r]['koutei']}
            ";
            $res_head = array();
            if ( getResult($query, $res_head) <= 0) {
                $_SESSION['s_sysmsg'] .= "{$mac_no}��{$mac_name} {$str_date}�������Ǥϥإå���������ޤ���2";
                break;
            } else {
                $res_log[$r]['parts_no'] = $res_head[0]['parts_no'];
                $res_log[$r]['plan_cnt'] = $res_head[0]['plan_cnt'];
                $query = "select substr(midsc, 1, 12) as midsc, substr(mzist, 1, 8) as mzist from miitem where mipn='{$res_log[$r]['parts_no']}'";
                $res_mi = array();
                if ( getResult($query, $res_mi) <= 0) {
                    $_SESSION['s_sysmsg'] .= "{$res_log['parts_no']}�����ʥޥ������μ����˼��ԡ�";
                    break;
                } else {
                    $res_log[$r]['parts_name'] = $res_mi[0]['midsc'];
                    $res_log[$r]['parts_mate'] = $res_mi[0]['mzist'];
                }
            }
        }
    }
    $_SESSION['work_mac_no']  = $mac_no;
    $_SESSION['work_date']    = $str_date;
    $view = 'OK';
    break;
}
while ($view == 'OK') {
    /////////// ������������դΥ��󥹥��󥹺���
    $equip_graph = new EquipGraphReport($mac_no, $str_date);
    $equip_graph->set_xtime($equip_xtime);
    if (isset($_REQUEST['forward'])) {
        $equip_graph->set_graph_page(+1);
    } elseif (isset($_REQUEST['backward'])) {
        $equip_graph->set_graph_page(-1);
    } else {
        $equip_graph->set_graph_page(0);
    }
    if ($equip_graph->out_page_ctl('backward')) $page_ctl_left = '';
    if ($equip_graph->out_page_ctl('forward')) $page_ctl_right = '';
    // ��å����Τν��� DATE TIME �μ���  ����(strDate, strTime, endDate, endTime)
    $lotDateTime = $equip_graph->out_lot_timestamp();
    // ����դ��ϰ���ν��� DATE TIME �μ���  ����(strDate, strTime, endDate, endTime)
    $graphDateTime = $equip_graph->out_graph_timestamp();
    // ������ϰϤγ��ϡ���λ�����μ���(key field)
    $graph_strTime = $equip_graph->out_graph_strTime();
    $graph_endTime = $equip_graph->out_graph_endTime();
    /////////////// ������ϰϤΥǡ������� ���ϻ��Σ��쥳����
    $query = "select  mac_state
                    , work_cnt
                from
                    equip_work_log2
                where
                    equip_index2(mac_no, date_time) <= '{$mac_no}{$graph_strTime}'
                order by
                    equip_index2(mac_no, date_time) DESC
                offset 0 limit 1
    ";
    $res = array();
    if ( ($rows=getResult($query, $res)) <= 0) {
        ///// ���ϤΥǡ�����̵�����˾��(�嵭)���Ѥ��ƥȥ饤
        $query = "select  mac_state
                    , work_cnt
                from
                    equip_work_log2
                where
                    equip_index2(mac_no, date_time) >= '{$mac_no}{$graph_strTime}'
                    and
                    equip_index2(mac_no, date_time) <= '{$mac_no}{$graph_endTime}'
                order by
                    equip_index2(mac_no, date_time) ASC
                offset 0 limit 1
        ";
        if ( ($rows=getResult($query, $res)) <= 0) {
            // ��Υ�åȾ�������ǥ����å����Ƥ��뤿�ᤳ�����¹Ԥ���뤳�Ȥ�̵��
        } else {
            $graph_str_mac_state = $res[0]['mac_state'];
            $graph_str_work_cnt  = $res[0]['work_cnt'];
        }
    } else {
        $graph_str_mac_state = $res[0]['mac_state'];
        $graph_str_work_cnt  = $res[0]['work_cnt'];
    }
    /////////////// ������ϰϤΥǡ������� ��λ���Σ��쥳����
    $query = "select  mac_state
                    , work_cnt
                from
                    equip_work_log2
                where
                    equip_index2(mac_no, date_time) <= '{$mac_no}{$graph_endTime}'
                order by
                    equip_index2(mac_no, date_time) DESC
                offset 0 limit 1
            ";
    $res = array();
    if ( ($rows=getResult($query, $res)) <= 0) {
        // ��Υ�åȾ�������ǥ����å����Ƥ��뤿�ᤳ�����¹Ԥ���뤳�Ȥ�̵��
    } else {
        $graph_end_mac_state = $res[0]['mac_state'];
        $graph_end_work_cnt  = $res[0]['work_cnt'];
    }
    if ($graph_endTime > "$end_date 08:30:00") {
        $graphDateTime['endDate'] = $lotDateTime['endDate'];
        $graphDateTime['endTime'] = $lotDateTime['endTime'];
        $graph_end_mac_state = $end_mac_state;
        $graph_end_work_cnt  = $end_work_cnt;
    }
    /********************   // �嵭���ɲä�����ˤ�ä�̤���̵��
    if ($graph_endTime > date('Y/m/d H:i:s')) {  // ̤����ä���
        $graph_end_mac_state = '';
        $graph_end_work_cnt  = '';
    }
    ********************/
    break;
}

//////////// �����ȥ�̾(�������Υ����ȥ�̾�ȥե�����Υ����ȥ�̾)
$menu->set_title("{$mac_no}��{$mac_name}����ž ���� �б� ����� ɽ��");

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
<?= $menu->out_site_java() ?>
<style type='text/css'>
<!--
select {
    background-color:   teal;
    color:              white;
}
.sub_font {
    font-size:      11.5pt;
    font-weight:    bold;
    font-family:    monospace;
}
.pick_font {
    font-size:      9.5pt;
    font-weight:    bold;
    font-family:    monospace;
}
th {
    font-size:          10.5pt;
    font-weight:        bold;
    font-family:        monospace;
    color:              blue;
    /* background-color:   yellow; */
}
.item {
    position:       absolute;
    top:            90px;
    left:           90px;
}
.table_font {
    font-size:      11.5pt;
    font-family:    monospace;
}
.ext_font {
    /* background-color:   yellow; */
    color:              blue;
    font-size:          10.5pt;
    font-weight:        bold;
    font-family:        monospace;
}
.winbox {
    border-style: solid;
    border-width: 1px;
    border-top-color: #FFFFFF;
    border-left-color: #FFFFFF;
    border-right-color: #DFDFDF;
    border-bottom-color: #DFDFDF;
}
-->
</style>
<form name='MainForm' method='post'>
    <input type='hidden' name='select' value=''>
</form>
<script language='JavaScript'>
<!--
/* ������ϥե�����Υ�����Ȥ˥ե������������� */
function set_focus() {
    // document.body.focus();   // F2/F12������ͭ���������б�
    // document.mhForm.backwardStack.focus();  // �嵭��IE�ΤߤΤ���NN�б�
    // document.mac_form.mac_no.focus();  // �������륭���ǵ���������Ǥ���褦�ˤ���
}
// -->
</script>
</head>
<body onLoad='set_focus()'>
    <center>
<?= $menu->out_title_border() ?>
        
        <!----------------- ���Ф���ɽ�� ------------------------>
        <table width='100%' bgcolor='#d6d3ce'  border='1' cellspacing='0' cellpadding='1'>
            <tr><td> <!-- ���ߡ�(�ǥ�������) -->
        <table width=100% bordercolordark='white' bordercolorlight='#bdaa90' bgcolor='#d6d3ce' align='center' border='1' cellspacing='0' cellpadding='1'>
            <?php if ($view != 'OK') {?>
            <tr class='sub_font'>
                <td align='center' width='100'>
                    <form name='mac_form' method='post' action='<?= $menu->out_self() ?>'>
                        <select name='mac_no' class='ret_font' onChange='document.mac_form.submit()'>
                        <?php if ($mac_no == '') echo "<option value=''>��������</option>\n" ?>
                        <?php
                        for ($j=0; $j<$rows_sel; $j++) {
                            if ($mac_no == $res_sel[$j]['mac_no']) {
                                printf("<option value='%s' selected>%s</option>\n", $res_sel[$j]['mac_no'], $mac_no_name[$j]);
                            } else {
                                printf("<option value='%s'>%s</option>\n", $res_sel[$j]['mac_no'], $mac_no_name[$j]);
                            }
                        }
                        ?>
                        </select>
                        <input type='hidden' name='equip_xtime' value='<?=$equip_xtime?>'>
                        <input type='hidden' name='reset_page' value=''>
                        <input type='hidden' name='str_date' value='<?=$str_date?>'>
                    </form>
                </td>
            </tr>
            <?php } else {?>
            <?php for ($r=0; $r<$rows_log; $r++) { ?>
            <tr class='sub_font'>
                <?php if ($r == 0) { ?>
                <td rowspan='<?=($rows_log+1-$rowspan)?>' align='center' width='100'>
                    <form name='mac_form' method='post' action='<?= $menu->out_self() ?>'>
                        <select name='mac_no' class='ret_font' onChange='document.mac_form.submit()'>
                        <?php if ($mac_no == '') echo "<option value=''>��������</option>\n" ?>
                        <?php
                        for ($j=0; $j<$rows_sel; $j++) {
                            if ($mac_no == $res_sel[$j]['mac_no']) {
                                printf("<option value='%s' selected>%s</option>\n", $res_sel[$j]['mac_no'], $mac_no_name[$j]);
                            } else {
                                printf("<option value='%s'>%s</option>\n", $res_sel[$j]['mac_no'], $mac_no_name[$j]);
                            }
                        }
                        ?>
                        </select>
                        <input type='hidden' name='equip_xtime' value='<?=$equip_xtime?>'>
                        <input type='hidden' name='reset_page' value=''>
                        <input type='hidden' name='str_date' value='<?=$str_date?>'>
                    </form>
                </td>
                <?php } ?>
                <td align='center' nowrap width='65'>����No</td>
                <td align='center' nowrap width='85'><?= $res_log[$r]['parts_no'] ?></td>
                <td align='center' nowrap width='65'>����̾</td>
                <td class='pick_font' align='center' nowrap width='130'><?= $res_log[$r]['parts_name'] ?></td>
                <td align='center' nowrap width='50'>���</td>
                <td class='pick_font' align='center' nowrap width='70'><?= $res_log[$r]['parts_mate'] ?></td>
                <td align='center' nowrap width='65'>�ؼ�No</td>
                <td align='center' nowrap width='50'><?= $res_log[$r]['siji_no'] ?></td>
                <td align='center' nowrap width='40'>����</td>
                <td align='center' nowrap width='20'><?= $res_log[$r]['koutei'] ?></td>
                <td align='center' nowrap width='60'>�ײ��</td>
                <td align='right'  nowrap width='60'><?= number_format($res_log[$r]['plan_cnt']) ?></td>
            </tr>
            <?php } ?>
            <?php } ?>
        </table>
            </td></tr>
        </table> <!-- ���ߡ�End -->
        
        <!-- <hr color='797979'> -->
        
        <table width='100%' border='0'>
        <tr>
        <td>
            <!-------------- �����ɽ���Υڡ�������ȥ������ �� -------------->
            <table bgcolor='#d6d3ce' align='center' border='1' cellspacing='0' cellpadding='3'>
               <tr><td> <!-- ���ߡ�(�ǥ�������) -->
            <table class='winbox' width=100% bgcolor='#d6d3ce'>
                <form name='page_ctl_left' method='post' action='<?=$menu->out_self()?>' target='_self'>
                <tr>
                    <td>
                        <input class='pt11b' type='submit' name='backward' value='���ڡ���' <?=$page_ctl_left?>>
                        <input type='hidden' name='mac_no' value='<?=$mac_no?>'>
                        <input type='hidden' name='equip_xtime' value='<?=$equip_xtime?>' >
                        <input type='hidden' name='str_date' value='<?=$str_date?>'>
                    </td>
                </tr>
                </form>
                <form name='xtime_ctl_left' method='post' action='<?=$menu->out_self()?>' target='_self'>
                <tr>
                    <td>
                        <select name='equip_xtime' class='ret_font' onChange='document.xtime_ctl_left.submit()'>
                            <?php if ($view=='OK') { echo $equip_graph->out_select_xtime($equip_xtime); ?>
                            <?php } else { ?>
                            <option value='2'  <?php if ($equip_xtime==2) echo 'selected';?>>&nbsp;2����</option>
                            <option value='4' <?php if ($equip_xtime==4) echo 'selected';?>>&nbsp;4����</option>
                            <option value='6' <?php if ($equip_xtime==6) echo 'selected';?>>&nbsp;6����</option>
                            <option value='8' <?php if ($equip_xtime==8) echo 'selected';?>>&nbsp;8����</option>
                            <option value='10' <?php if ($equip_xtime==10) echo 'selected';?>>10����</option>
                            <option value='12' <?php if ($equip_xtime==12) echo 'selected';?>>12����</option>
                            <option value='14' <?php if ($equip_xtime==14) echo 'selected';?>>14����</option>
                            <option value='16' <?php if ($equip_xtime==16) echo 'selected';?>>16����</option>
                            <option value='18' <?php if ($equip_xtime==18) echo 'selected';?>>18����</option>
                            <option value='20' <?php if ($equip_xtime==20) echo 'selected';?>>20����</option>
                            <option value='22' <?php if ($equip_xtime==22) echo 'selected';?>>22����</option>
                            <option value='24' <?php if ($equip_xtime==24) echo 'selected';?>>24����</option>
                            <?php } ?>
                        </select>
                        <input type='hidden' name='mac_no' value='<?=$mac_no?>'>
                        <input type='hidden' name='reset_page' value=''>
                        <input type='hidden' name='str_date' value='<?=$str_date?>'>
                    </td>
                </tr>
                </form>
            </table> <!----- ���ߡ� End ----->
                </td></tr>
            </table>
        </td>
        <td>
            <!--------------- �������鸫�Ф�ɽ(���Ϥȸ���)���Ԥ�ɽ������ -------------------->
            <table bgcolor='#d6d3ce' align='center' border='1' cellspacing='0' cellpadding='1'>
                <tr><td> <!-- ���ߡ�(�ǥ�������) -->
            <table width=100% bordercolordark='white' bordercolorlight='#bdaa90' bgcolor='#d6d3ce' align='center' border='1' cellspacing='0' cellpadding='2'>
                <tr>
                    <th nowrap>
                        <form name='reload_form' method='post' action='<?=$menu->out_self()?>' target='_self'>
                        <input style='font-size:10pt; color:blue;' type='submit' name='reload' value='��ɽ��' <?=$reload?>>
                        <input type='hidden' name='mac_no' value='<?=$mac_no?>'>
                        <input type='hidden' name='equip_xtime' value='<?=$equip_xtime?>' >
                        <input type='hidden' name='str_date' value='<?=$str_date?>'>
                        </form>
                    </th>
                    <th nowrap>��</th>
                    <th nowrap>ǯ����</th><th nowrap>��ʬ��</th><th nowrap>����</th><th nowrap>�ù���</th>
                    <th nowrap>��</th>
                    <th nowrap>ǯ����</th><th nowrap>��ʬ��</th><th nowrap>����</th><th nowrap>�ù���</th>
                </tr>
                <tr class='table_font'>
                    <td align='center' nowrap>�� �� �ϰ�</td>
                    <td class='ext_font' align='center' nowrap>����</td>
                    <td align='center' nowrap><?php echo $lotDateTime['strDate'] ?></td>
                    <td align='center' nowrap><?php echo $lotDateTime['strTime'] ?></td>
                    <?php if ($str_mac_state != '') { $mac_state_txt = equip_machine_state($mac_no, $str_mac_state, $bg_color, $txt_color); ?>
                    <td align='center' nowrap bgcolor='<?=$bg_color?>'><font color='<?=$txt_color?>'><?=$mac_state_txt?></font></td>
                    <?php } else { ?>
                    <td align='center' nowrap>��</td>
                    <?php } ?>
                    <td align='right' nowrap><?php echo number_format($str_work_cnt) ?></td>
                    
                    <td class='ext_font' align='center' nowrap>��λ</td>
                    <td align='center' nowrap><?php echo $lotDateTime['endDate'] ?></td>
                    <td align='center' nowrap><?php echo $lotDateTime['endTime'] ?></td>
                    <?php if ($end_mac_state != '') { $mac_state_txt = equip_machine_state($mac_no, $end_mac_state, $bg_color, $txt_color); ?>
                    <td align='center' nowrap bgcolor='<?=$bg_color?>'><font color='<?=$txt_color?>'><?=$mac_state_txt?></font></td>
                    <?php } else { ?>
                    <td align='center' nowrap>��</td>
                    <?php } ?>
                    <td align='right' nowrap><?php echo number_format($end_work_cnt) ?></td>
                </tr>
                <tr class='table_font'>
                    <td align='center' nowrap>������ϰ�</td>
                    <td class='ext_font' align='center' nowrap>����</td>
                    <td align='center' nowrap><?php echo $graphDateTime['strDate'] ?></td>
                    <td align='center' nowrap><?php echo $graphDateTime['strTime'] ?></td>
                    <?php if ($graph_str_mac_state != '') { $mac_state_txt = equip_machine_state($mac_no, $graph_str_mac_state, $bg_color, $txt_color); ?>
                    <td align='center' nowrap bgcolor='<?=$bg_color?>'><font color='<?=$txt_color?>'><?=$mac_state_txt?></font></td>
                    <?php } else { ?>
                    <td align='center' nowrap>��</td>
                    <?php } ?>
                    <td align='right' nowrap><?php echo number_format($graph_str_work_cnt) ?></td>
                    
                    <td class='ext_font' align='center' nowrap>��λ</td>
                    <td align='center' nowrap><?php echo $graphDateTime['endDate'] ?></td>
                    <td align='center' nowrap><?php echo $graphDateTime['endTime'] ?></td>
                    <?php if ($graph_end_mac_state != '') { $mac_state_txt = equip_machine_state($mac_no, $graph_end_mac_state, $bg_color, $txt_color); ?>
                    <td align='center' nowrap bgcolor='<?=$bg_color?>'><font color='<?=$txt_color?>'><?=$mac_state_txt?></font></td>
                    <?php } else { ?>
                    <td align='center' nowrap>��</td>
                    <?php } ?>
                    <td align='right' nowrap><?php if ($graph_end_work_cnt != '') echo number_format($graph_end_work_cnt); else echo '��'; ?></td>
                </tr>
            </table>
                </td></tr>
            </table> <!-- ���ߡ�End -->
            
        </td>
        <td>
            <!-------------- �����ɽ���Υڡ�������ȥ������ �� -------------->
            <table bgcolor='#d6d3ce' align='center' border='1' cellspacing='0' cellpadding='3'>
               <tr><td> <!-- ���ߡ�(�ǥ�������) -->
            <table class='winbox' width=100% bgcolor='#d6d3ce'>
                <form name='page_ctl_right' method='post' action='<?=$menu->out_self()?>' target='_self'>
                <tr>
                    <td>
                        <input class='pt11b' type='submit' name='forward' value='���ڡ���' <?=$page_ctl_right?>>
                        <input type='hidden' name='mac_no' value='<?=$mac_no?>'>
                        <input type='hidden' name='equip_xtime' value='<?=$equip_xtime?>' >
                        <input type='hidden' name='str_date' value='<?=$str_date?>'>
                    </td>
                </tr>
                </form>
                <form name='xtime_ctl_right' method='post' action='<?=$menu->out_self()?>' target='_self'>
                <tr>
                    <td>
                        <select name='str_date' class='pt11b' onChange='document.xtime_ctl_right.submit()'>
                            <?php for ($i=0; $i<$rows_date; $i++) { ?>
                            <option value='<?=$set_date[$i]?>'<?php if ($str_date==$set_date[$i]) echo 'selected';?>><?=$set_date[$i]?></option>
                            <?php } ?>
                        </select>
                        <input type='hidden' name='mac_no' value='<?=$mac_no?>'>
                        <input type='hidden' name='reset_page' value=''>
                        <input type='hidden' name='equip_xtime' value='<?=$equip_xtime?>' >
                    </td>
                </tr>
                </form>
            </table> <!----- ���ߡ� End ----->
                </td></tr>
            </table>
        </td>
        </tr>
        </table>
    </center>
</body>
</html>
<Script Language='JavaScript'>
    document.MainForm.select.value = '<?=$view?>';
    document.MainForm.target = 'List';
    document.MainForm.action = 'equip_report_graphList.php';
    document.MainForm.submit();
</Script>
<?=$menu->out_alert_java()?>
<?php ob_end_flush(); // ���ϥХåե���gzip���� END ?>
