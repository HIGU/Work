<?php
//////////////////////////////////////////////////////////////////////////////
// ��Ω������ư���������ƥ�� ����(����) ����� ɽ��  Header�ե졼��        //
// Copyright (C) 2021-2021 norihisa_ooya@nitto-kohki.co.jp                  //
// Changed history                                                          //
// 2021/03/26 Created  equip_hist_graph_moniHeader.php                      //
//////////////////////////////////////////////////////////////////////////////
// ini_set('error_reporting', E_ALL | E_STRICT);
// ini_set('display_errors', '1');             // Error ɽ�� ON debug �� ��꡼���女����
ob_start('ob_gzhandler');                   // ���ϥХåե���gzip����
session_start();                            // ini_set()�μ��˻��ꤹ�뤳�� Script �Ǿ��
require_once ('../equip_function.php');     // TNK ������ function
require_once ('../../MenuHeader.php');      // TNK ������ menu class
require_once ('../EquipGraphClassMoni.php');    // ������Ư���� Graph class
access_log();                               // Script Name �ϼ�ư����

///// TNK ���ѥ�˥塼���饹�Υ��󥹥��󥹤����
$menu = new MenuHeader();                   // ǧ�ڥ����å�0=���̰ʾ� �����=���å������ �����ȥ�̤����

////////////// ����������
$menu->set_site(40, 6);                     // site_index=40(������˥塼) site_id=11(��ž�����)
////////////// target����
// $menu->set_target('application');           // �ե졼���Ǥ�������target°����ɬ��
$menu->set_target('_parent');               // �ե졼���Ǥ�������target°����ɬ��

///// GET/POST�Υ����å�&����
if (isset($_REQUEST['mac_no'])) {
    $mac_no  = $_REQUEST['mac_no'];
    $plan_no = $_REQUEST['plan_no'];
    $koutei  = $_REQUEST['koutei'];
    $_SESSION['mac_no']  = $mac_no;
    $_SESSION['plan_no'] = $plan_no;
    $_SESSION['koutei']  = $koutei;
} else {
    $mac_no  = $_SESSION['mac_no'];
    $plan_no = $_SESSION['plan_no'];
    $koutei  = $_SESSION['koutei'];
}

////////////// ������Ϥ��ѥ�᡼��������
// $menu->set_retGET('page_keep', 'on');   // name value �ν������
// $menu->set_retGET('mac_no', $mac_no);   // name value �ν������
$menu->set_retPOST('page_keep', 'on');   // name value �ν������
$menu->set_retPOST('mac_no', $mac_no);   // name value �ν������

/////////// ����դ�X���λ����ϰϤ����
if (isset($_REQUEST['equip_xtime'])) {
    $_SESSION['equip_xtime'] = $_REQUEST['equip_xtime'];
    // $_SESSION['equip_graph_page'] = 1;  // �����
} else {
    if (isset($_SESSION['equip_hist_xtime'])) {
        $_SESSION['equip_xtime'] = $_SESSION['equip_hist_xtime'];
    } else {
        $_SESSION['equip_xtime'] = 24;
    }
}
$equip_xtime = $_SESSION['equip_xtime'];

if (isset($_REQUEST['reset_page'])) {
    @$_SESSION['equip_graph_page'] = 1;     // �����
}

///// �������ѿ��ν����
// $mac_no     = '';
$parts_no   = '��';
$parts_name = '��';
$parts_mate = '��';
$plan_cnt   = '��';
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

if ($mac_no != '') {
    //////////////// �����ޥ��������鵡��̾�����
    $query = "select trim(mac_name) as mac_name from equip_machine_master2 where mac_no={$mac_no} limit 1";
    if (getUniResult($query, $mac_name) <= 0) {
        $mac_name = '��';   // error���ϵ���̾��֥��
    }
    //////////// �إå�����긫�Ф��Ѥ������ֹ桦�ײ�������
    $query = "select  to_char(str_timestamp AT TIME ZONE 'JST', 'YYYY/MM/DD HH24:MI:SS') as str_timestamp
                    , to_char(end_timestamp AT TIME ZONE 'JST', 'YYYY/MM/DD HH24:MI:SS') as end_timestamp
                    -- , to_char(CURRENT_TIMESTAMP AT TIME ZONE 'JST', 'YYYY/MM/DD HH24:MI:SS') as end_timestamp
                    , plan_no
                    , koutei
                    , parts_no
                    , plan_cnt
            from
                equip_work_log2_header_moni
            where
                mac_no={$mac_no} and plan_no='{$plan_no}' and koutei={$koutei}
                and work_flg IS FALSE and end_timestamp IS NOT NULL
    ";
    $res_head = array();
    if ( getResult($query, $res_head) <= 0) {
        $_SESSION['s_sysmsg'] .= "<font color='yellow'>{$mac_name}��{$mac_no}��{$plan_no}��{$koutei}�Ǥϼ��ӥǡ���������ޤ���</font>";
        // header('Location: ' . H_WEB_HOST . $menu->out_RetUrl());
        // exit;
    } else {
        $str_timestamp = $res_head[0]['str_timestamp'];
        $end_timestamp = $res_head[0]['end_timestamp'];
        $plan_no   = $res_head[0]['plan_no'];
        $koutei    = $res_head[0]['koutei'];
        $parts_no  = $res_head[0]['parts_no'];
        $plan_cnt  = $res_head[0]['plan_cnt'];
        $query = "select substr(midsc, 1, 12) as midsc, mzist from miitem where mipn='{$parts_no}'";
        $res_mi = array();
        if ( getResult($query, $res_mi) <= 0) {
            $_SESSION['s_sysmsg'] .= "{$parts_no}�����ʥޥ������μ����˼��ԡ�";
        } else {
            $parts_name = $res_mi[0]['midsc'];
            $parts_mate = $res_mi[0]['mzist'];
            $_SESSION['work_mac_no']  = $mac_no;
            $_SESSION['work_plan_no'] = $plan_no;
            $_SESSION['work_koutei']  = $koutei;
            $view = 'OK';
        }
    }
}
if ($view == 'OK') {
    /////////////// ��å����ΤΥǡ������� ���ϻ��Σ��쥳����
    $query = "select mac_no
                    -- , to_char(date_time AT TIME ZONE 'JST', 'YYYY/MM/DD') as date
                    -- , to_char(date_time AT TIME ZONE 'JST', 'HH24:MI:SS') as time
                    ,mac_state
                    ,work_cnt
                from
                    equip_work_log2_moni
                where
                    plan_no='{$plan_no}' and mac_no={$mac_no} and koutei={$koutei}
                order by
                    date_time ASC
                offset 0 limit 1
            ";
    /*
    $query = "select mac_no
                    -- , to_char(date_time AT TIME ZONE 'JST', 'YYYY/MM/DD') as date
                    -- , to_char(date_time AT TIME ZONE 'JST', 'HH24:MI:SS') as time
                    ,mac_state
                    ,work_cnt
                from
                    equip_work_log2_moni
                where
                    equip_index_moni(mac_no, plan_no, koutei, date_time) > '{$mac_no}{$plan_no}{$koutei}00000000000000'
                and
                    equip_index_moni(mac_no, plan_no, koutei, date_time) < '{$mac_no}{$plan_no}{$koutei}99999999999999'
                order by
                    equip_index_moni(mac_no, plan_no, koutei, date_time) ASC
                offset 0 limit 1
            ";
    */
    $res = array();
    if ( ($rows=getResult($query, $res)) <= 0) {
        $_SESSION['s_sysmsg'] = "����No��$mac_no �ؼ�No��$plan_no ������$koutei �����٤�����ޤ���";
    } else {
        $str_mac_state = $res[0]['mac_state'];
        $str_work_cnt  = $res[0]['work_cnt'];
    }
    
    /////////////// ��å����ΤΥǡ������� ��λ���Σ��쥳����
    $query = "select mac_no
                    ,mac_state
                    ,work_cnt
                from
                    equip_work_log2_moni
                where
                    plan_no='{$plan_no}' and mac_no={$mac_no} and koutei={$koutei}
                order by
                    date_time DESC
                offset 0 limit 1
            ";
    /*
    $query = "select mac_no
                    ,mac_state
                    ,work_cnt
                from
                    equip_work_log2_moni
                where
                    equip_index_moni(mac_no, plan_no, koutei, date_time) > '{$mac_no}{$plan_no}{$koutei}00000000000000'
                and
                    equip_index_moni(mac_no, plan_no, koutei, date_time) < '{$mac_no}{$plan_no}{$koutei}99999999999999'
                order by
                    equip_index_moni(mac_no, plan_no, koutei, date_time) DESC
                offset 0 limit 1
            ";
    */
    $res = array();
    if ( ($rows=getResult($query, $res)) <= 0) {
        $_SESSION['s_sysmsg'] = "����No��$mac_no �ؼ�No��$plan_no ������$koutei �����٤�����ޤ���";
    } else {
        $end_mac_state = $res[0]['mac_state'];
        $end_work_cnt  = $res[0]['work_cnt'];
    }
    /////////// ������������դΥ��󥹥��󥹺���
    $equip_graph = new EquipGraph($mac_no, $plan_no, $koutei);
    $equip_graph->set_xtime($equip_xtime);      // ����դδ�˾�λ��ּ�������
    $equip_xtime = $equip_graph->out_xtime();   // ����դλ��ּ��Υ������������ͤ����
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
                    equip_work_log2_moni
                where
                    plan_no='{$plan_no}' and mac_no={$mac_no} and koutei={$koutei} and to_char((CAST(date_time AS TIMESTAMP)), 'YYYYMMDDHH24MISS') <={$graph_strTime}
                order by
                    date_time DESC
                offset 0 limit 1
    ";
    /*
    $query = "select  mac_state
                    , work_cnt
                from
                    equip_work_log2_moni
                where
                    equip_index_moni(mac_no, plan_no, koutei, date_time) > '{$mac_no}{$plan_no}{$koutei}00000000000000'
                and
                    equip_index_moni(mac_no, plan_no, koutei, date_time) <= '{$mac_no}{$plan_no}{$koutei}{$graph_strTime}'
                order by
                    equip_index_moni(mac_no, plan_no, koutei, date_time) DESC
                offset 0 limit 1
    ";
    */
    $res = array();
    if ( ($rows=getResult($query, $res)) <= 0) {
        $query = "select  mac_state
                    , work_cnt
                from
                    equip_work_log2_moni
                where
                    plan_no='{$plan_no}' and mac_no={$mac_no} and koutei={$koutei} and to_char((CAST(date_time AS TIMESTAMP)), 'YYYYMMDDHH24MISS') >={$graph_strTime}
                order by
                    date_time ASC
                offset 0 limit 1
        ";
        /*
        $query = "select  mac_state
                    , work_cnt
                from
                    equip_work_log2_moni
                where
                    equip_index_moni(mac_no, plan_no, koutei, date_time) >= '{$mac_no}{$plan_no}{$koutei}{$graph_strTime}'
                and
                    equip_index_moni(mac_no, plan_no, koutei, date_time) < '{$mac_no}{$plan_no}{$koutei}99999999999999'
                order by
                    equip_index_moni(mac_no, plan_no, koutei, date_time) ASC
                offset 0 limit 1
        ";
        */
        if ( ($rows=getResult($query, $res)) <= 0) {    // ���ϤΥǡ�����̵�����˾��(�嵭)���Ѥ��ƥȥ饤
            $_SESSION['s_sysmsg'] = "����No��$mac_no �ؼ�No��$plan_no ������$koutei ���ϡ�{$graph_strTime} �Υ���եǡ���������ޤ���";
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
                    equip_work_log2_moni
                where
                    plan_no='{$plan_no}' and mac_no={$mac_no} and koutei={$koutei} and to_char((CAST(date_time AS TIMESTAMP)), 'YYYYMMDDHH24MISS') <={$graph_endTime}
                order by
                    date_time DESC
                offset 0 limit 1
            ";
    /*
    $query = "select  mac_state
                    , work_cnt
                from
                    equip_work_log2_moni
                where
                    equip_index_moni(mac_no, plan_no, koutei, date_time) > '{$mac_no}{$plan_no}{$koutei}00000000000000'
                and
                    equip_index_moni(mac_no, plan_no, koutei, date_time) <= '{$mac_no}{$plan_no}{$koutei}{$graph_endTime}'
                order by
                    equip_index_moni(mac_no, plan_no, koutei, date_time) DESC
                offset 0 limit 1
            ";
    */
    $res = array();
    if ( ($rows=getResult($query, $res)) <= 0) {
        $_SESSION['s_sysmsg'] = "����No��$mac_no �ؼ�No��$plan_no ������$koutei ��λ��{$graph_endTime} �Υ���եǡ���������ޤ���";
    } else {
        $graph_end_mac_state = $res[0]['mac_state'];
        $graph_end_work_cnt  = $res[0]['work_cnt'];
    }
    ///// ��åȤν�λ��Ķ������
    if ("{$graphDateTime['endDate']} {$graphDateTime['endTime']}" > "{$lotDateTime['endDate']} {$lotDateTime['endTime']}") {
        $graph_end_mac_state = '';
        $graph_end_work_cnt  = '';
    }
    /*************************
    if ($graph_endTime > date('YmdHis')) {  // ̤����ä���
        $graph_end_mac_state = '';
        $graph_end_work_cnt  = '';
    }
    *************************/
}

//////////// �����ȥ�̾(�������Υ����ȥ�̾�ȥե�����Υ����ȥ�̾)
$menu->set_title("{$mac_no}��{$mac_name}������ ����� ɽ��");

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
    document.mhForm.backwardStack.focus();  // �嵭��IE�ΤߤΤ���NN�б�
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
            <tr class='sub_font'>
                <td align='center' nowrap width='65'>����No</td>
                <td align='center' nowrap width='85'><?= $parts_no ?></td>
                <td align='center' nowrap width='65'>����̾</td>
                <td class='pick_font' align='center' nowrap width='180'><?= $parts_name ?></td>
                <td align='center' nowrap width='50'>���</td>
                <td class='pick_font' align='center' nowrap width='70'><?= $parts_mate ?></td>
                <td align='center' nowrap width='65'>�ײ�No</td>
                <td align='center' nowrap width='50'><?= $plan_no ?></td>
                <td align='center' nowrap width='40'>����</td>
                <td align='center' nowrap width='20'><?= $koutei ?></td>
                <td align='center' nowrap width='60'>�ײ��</td>
                <td align='right'  nowrap width='60'><?= number_format($plan_cnt) ?></td>
            </tr>
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
                        <input type='hidden' name='equip_xtime' value='<?=$equip_xtime?>' >
                    </td>
                </tr>
                </form>
                <form name='xtime_ctl_left' method='post' action='<?=$menu->out_self()?>' target='_self'>
                <tr>
                    <td>
                        <select name='equip_xtime' class='ret_font' onChange='document.xtime_ctl_left.submit()'>
                            <?=$equip_graph->out_select_xtime($equip_xtime)?>
                        </select>
                        <input type='hidden' name='reset_page' value=''>
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
                        <input style='font-size:10pt; color:blue;' type='submit' name='reload' value='��ɽ��'>
                        <input type='hidden' name='equip_xtime' value='<?=$equip_xtime?>' >
                        </form>
                    </th>
                    <th nowrap>��</th>
                    <th nowrap>ǯ����</th><th nowrap>��ʬ��</th><th nowrap>����</th><th nowrap>�ù���</th>
                    <th nowrap>��</th>
                    <th nowrap>ǯ����</th><th nowrap>��ʬ��</th><th nowrap>����</th><th nowrap>�ù���</th>
                </tr>
                <tr class='table_font'>
                    <td align='center' nowrap>��å�����</td>
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
                        <input type='hidden' name='equip_xtime' value='<?=$equip_xtime?>' >
                    </td>
                </tr>
                </form>
                <form name='xtime_ctl_right' method='post' action='<?=$menu->out_self()?>' target='_self'>
                <tr>
                    <td>
                        <select name='equip_xtime' class='ret_font' onChange='document.xtime_ctl_right.submit()'>
                            <?=$equip_graph->out_select_xtime($equip_xtime)?>
                        </select>
                        <input type='hidden' name='reset_page' value=''>
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
    document.MainForm.action = 'equip_hist_graph_moniList.php';
    document.MainForm.submit();
</Script>
<?php ob_end_flush(); // ���ϥХåե���gzip���� END ?>
