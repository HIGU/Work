<?php
//////////////////////////////////////////////////////////////////////////////
// ��Ω������Ư���������ƥ�βù����Ӥ�굡�����֤ν���ɽ�κ������Ȳ�       //
// Copyright (C) 2021-2021 norihisa_ooya@nitto-kohki.co.jp                  //
// Changed history                                                          //
// 2021/03/26 Created  equip_chart_summary_moni.php                         //
//////////////////////////////////////////////////////////////////////////////
// ini_set('error_reporting', E_ALL | E_STRICT);
// ini_set('display_errors', '1');             // Error ɽ�� ON debug �� ��꡼���女����
// ini_set('implicit_flush', 'off');           // echo print �� flush �����ʤ�(�٤��ʤ뤿��) CLI CGI��
// ini_set('max_execution_time', 1200);        // ����¹Ի���=20ʬ CLI CGI��
session_start();                            // ini_set()�μ��˻��ꤹ�뤳�� Script �Ǿ��
ob_start('ob_gzhandler');                   // ���ϥХåե���gzip����

require_once ('../equip_function.php');     // ������ư���� ����
require_once ('../../function.php');        // define.php �� pgsql.php �� require_once ���Ƥ���
require_once ('../../tnk_func.php');        // TNK �˰�¸������ʬ�δؿ��� require_once ���Ƥ���
require_once ('../../MenuHeader.php');      // TNK ������ menu class
access_log();                               // Script Name �ϼ�ư����

///// TNK ���ѥ�˥塼���饹�Υ��󥹥��󥹤����
$menu = new MenuHeader();                   // ǧ�ڥ����å�0=���̰ʾ� �����=���å������ �����ȥ�̤����

////////////// ����������
// $menu->set_site(40, 6);                     // site_index=40(������˥塼2) site_id=999(site�򳫤�)
////////////// �꥿���󥢥ɥ쥹����
// $menu->set_RetUrl(TOP_MENU);
//////////// �����ȥ�̾(�������Υ����ȥ�̾�ȥե�����Υ����ȥ�̾)
$menu->set_title('��Ω���Ӥ�꽸��ɽ�ξȲ�');
//////////// ɽ�������
$menu->set_caption('�ײ��ֹ�ñ�̤ν���ɽ');

//////////// JavaScript Stylesheet File ��ɬ���ɤ߹��ޤ���
$uniq = uniqid("target");

//////////// �оȥǡ����ʹ����� ������ ����
$mac_no   = $_SESSION['mac_no']  ;
$plan_no  = $_SESSION['plan_no'] ;
$parts_no = $_SESSION['parts_no'];
$koutei   = $_SESSION['koutei']  ;

////////////// ������Ϥ��ѥ�᡼��������
// $menu->set_retGET('page_keep', 'on');   // name value �ν������
// $menu->set_retGET('mac_no', $mac_no);   // name value �ν������
$menu->set_retPOST('page_keep', 'on');   // name value �ν������
$menu->set_retPOST('mac_no', $mac_no);   // name value �ν������

//////////// SQL ʸ�� where ��� ���Ѥ���
$search = "where mac_no=$mac_no and plan_no='$plan_no' and koutei=$koutei";

//////////// ���ǤιԿ�
define('PAGE', '20');

//////////// ����쥳���ɿ�����     (�оȥǡ����κ������ڡ�������˻���)
$query = "select mac_no
            from
                equip_state_summary2_moni
            where
                mac_no=$mac_no and plan_no='$plan_no' and koutei=$koutei
        ";
$res_chk = array();
if ( ($maxrows = getResult2($query, $res_chk)) <= 0) {         // $maxrows �μ���
    ////////// �ǡ���̤���פΤ��ὸ�׳���
    $query = "select EXTRACT(EPOCH FROM date_time) as date_time
                    , mac_state
                from
                    equip_work_log2_moni
                where
                    plan_no='{$plan_no}' and mac_no={$mac_no} and koutei={$koutei}
                order by
                    date_time ASC
            ";
    /*
    $query = "select EXTRACT(EPOCH FROM date_time) as date_time
                    , mac_state
                from
                    equip_work_log2_moni
                where
                    equip_index_moni(mac_no, plan_no, koutei, date_time) > '{$mac_no}{$plan_no}{$koutei}00000000000000'
                    and
                    equip_index_moni(mac_no, plan_no, koutei, date_time) < '{$mac_no}{$plan_no}{$koutei}99999999999999'
                order by
                    equip_index_moni(mac_no, plan_no, koutei, date_time) ASC
            ";
    */
    $rows = getResult($query, $res);
    $rui_state = array();                       // ������ �ѿ� �����
    for ($i=0; $i<=M_STAT_MAX_NO; $i++) {
        $rui_state[$i] = 0;                     // �����Ǥν����
    }
    for ($r=1; $r<$rows; $r++) {                // �ƾ���������ѻ��֤򻻽�
        for ($i=0; $i<=M_STAT_MAX_NO; $i++) {
            if ($res[$r-1]['mac_state'] == $i) {     // ���֤��Ѳ��������Υ쥳���ɤΰ�����Υ쥳���ɤ����
                $rui_state[$i] += ($res[$r]['date_time'] - $res[$r-1]['date_time']);
            }
        }
    }
    for ($i=0; $i<=M_STAT_MAX_NO; $i++) {
        $state_name[$i] = equip_machine_state($mac_no, $i, $tmp_bg, $tmp_txt);   // ���֤�̾�Τμ���
        if ($rui_state[$i] <= 0) {
            continue;
        }
        $rui_state[$i] = Uround($rui_state[$i]/60,0);           // �ä�ʬ���ѹ�
    }
    /////////// �����ޥ�����������֥ơ��֥������μ���
    $query = "select csv_flg from equip_machine_master2 where mac_no=$mac_no";
    if (getUniResult($query, $state_type) <= 0) {
        $_SESSION["s_sysmsg"] .= "�����ޥ�����������֥����פμ����˼���";
        header('Location: ' . H_WEB_HOST . $menu->RetUrl());                   // ľ���θƽи������
        exit();             ///// $state_type �ϰʲ��� Netmoni or �����꡼�����å������������ؤǻ���
    }
    /////////// begin �ȥ�󥶥�����󳫻�
    if ($con = funcConnect()) {
        query_affected_trans($con, 'begin');
        for ($i=0; $i<=M_STAT_MAX_NO; $i++) {
            $query = sprintf("insert into equip_state_summary2_moni
                            (mac_no, plan_no, parts_no, koutei, state, total_time, state_name, state_type)
                            values(%s, '%s', '%s', %s, %d, %d, '%s', $state_type)",
                            $mac_no, $plan_no, $parts_no, $koutei, $i, $rui_state[$i], $state_name[$i]);
            if (query_affected_trans($con, $query) <= 0) {
                $_SESSION["s_sysmsg"] .= sprintf("���׷�̤���Ͽ�˼��� �����ֹ桧%s �ײ��ֹ桧%s", $mac_no, $plan_no);
                query_affected_trans($con, 'rollback');         // transaction rollback
                header('Location: ' . H_WEB_HOST . $menu->RetUrl());               // ľ���θƽи������
                exit();
            }
        }
    } else {
        $_SESSION['s_sysmsg'] .= "�ǡ����١�������³�Ǥ��ޤ���";
        header('Location: ' . H_WEB_HOST . $menu->RetUrl());                   // ľ���θƽи������
        exit();
    }
    /////////// commit �ȥ�󥶥������λ
    query_affected_trans($con, 'commit');
}
//////////// �ڡ������ե��å�����
if ( isset($_POST['forward']) ) {                       // ���Ǥ������줿
    $_SESSION['offset'] += PAGE;
    if ($_SESSION['offset'] >= $maxrows) {
        $_SESSION['offset'] -= PAGE;
        if ($_SESSION['s_sysmsg'] == "") {
            $_SESSION['s_sysmsg'] .= "<font color='yellow'>���ǤϤ���ޤ���!</font>";
        } else {
            $_SESSION['s_sysmsg'] .= "<br><font color='yellow'>���ǤϤ���ޤ���!</font>";
        }
    }
} elseif ( isset($_POST['backward']) ) {                // ���Ǥ������줿
    $_SESSION['offset'] -= PAGE;
    if ($_SESSION['offset'] < 0) {
        $_SESSION['offset'] = 0;
        if ($_SESSION['s_sysmsg'] == "") {
            $_SESSION['s_sysmsg'] .= "<font color='yellow'>���ǤϤ���ޤ���!</font>";
        } else {
            $_SESSION['s_sysmsg'] .= "<br><font color='yellow'>���ǤϤ���ޤ���!</font>";
        }
    }
} elseif ( isset($_POST['page_keep']) ) {               // ���ߤΥڡ�����ݻ�����
    $offset = $_SESSION['offset'];
} else {
    $_SESSION['offset'] = 0;                            // ���ξ��ϣ��ǽ����
}
$offset = $_SESSION['offset'];

//////////// �إå����ե����뤫��ײ������
$query = "select plan_cnt
                , jisseki
            from
                equip_work_log2_header_moni
            where
                mac_no=$mac_no and plan_no='$plan_no' and koutei=$koutei";
if ( ($rows=getResult2($query, $res_head)) <= 0) {
    $plan_cnt = "";                                  // �ײ�������˼���
    $jisseki  = "";                                  // �ײ�������˼���
} else {
    $plan_cnt = $res_head[0][0];
    $jisseki  = $res_head[0][1];
}

/////////////// ����ɽ(�ܺ�ɽ��)�����Τ���Υǡ������� ���ϻ��Σ��쥳����
$query = "select mac_no
                , to_char(date_time AT TIME ZONE 'JST', 'YYYY/MM/DD') as date
                , to_char(date_time AT TIME ZONE 'JST', 'HH24:MI:SS') as time
                , mac_state
                , work_cnt
            from
                equip_work_log2_moni
            where
                plan_no='{$plan_no}' and mac_no={$mac_no} and koutei={$koutei}
            order by
                date_time ASC
            limit 1
        ";
/*
$query = "select mac_no
                , to_char(date_time AT TIME ZONE 'JST', 'YYYY/MM/DD') as date
                , to_char(date_time AT TIME ZONE 'JST', 'HH24:MI:SS') as time
                , mac_state
                , work_cnt
            from
                equip_work_log2_moni
            where
                equip_index_moni(mac_no, plan_no, koutei, date_time) > '{$mac_no}{$plan_no}{$koutei}00000000000000'
            order by
                equip_index_moni(mac_no, plan_no, koutei, date_time) ASC
            limit 1
        ";
*/
$res_str = array();
if ( ($rows=getResult($query, $res_str)) <= 0) {
    $_SESSION['s_sysmsg'] = "����No��$mac_no �ײ�No��$plan_no ������$koutei �����٤�����ޤ���";
    header('Location: ' . H_WEB_HOST . $menu->RetUrl());           // ľ���θƽи������
    exit();
}

/////////////// ����ɽ(�ܺ�ɽ��)�����Τ���Υǡ������� ��λ���Σ��쥳����
$query = "select mac_no
                , to_char(date_time AT TIME ZONE 'JST', 'YYYY/MM/DD') as date
                , to_char(date_time AT TIME ZONE 'JST', 'HH24:MI:SS') as time
                , mac_state
                , work_cnt
            from
                equip_work_log2_moni
            where
                plan_no='{$plan_no}' and mac_no={$mac_no} and koutei={$koutei}
            order by
                date_time DESC
            limit 1
        ";
/*
$query = "select mac_no
                , to_char(date_time AT TIME ZONE 'JST', 'YYYY/MM/DD') as date
                , to_char(date_time AT TIME ZONE 'JST', 'HH24:MI:SS') as time
                , mac_state
                , work_cnt
            from
                equip_work_log2_moni
            where
                equip_index_moni(mac_no, plan_no, koutei, date_time) < '{$mac_no}{$plan_no}{$koutei}99999999999999'
            order by
                equip_index_moni(mac_no, plan_no, koutei, date_time) DESC
            limit 1
        ";
*/
$res_end = array();
if ( ($rows=getResult($query, $res_end)) <= 0) {
    $_SESSION['s_sysmsg'] = "����No��$mac_no �ײ�No��$plan_no ������$koutei �����٤�����ޤ���";
    header('Location: ' . H_WEB_HOST . $menu->RetUrl());           // ľ���θƽи������
    exit();
} else {
    $res_end[0]['work_cnt'] = $jisseki;          // work_cnt �˼��ӿ�����Ū�������!
}

//////////////// �����ޥ��������鵡��̾�����
$query = "select trim(mac_name) as mac_name from equip_machine_master2 where mac_no={$mac_no} limit 1";
if (getUniResult($query, $mac_name) <= 0) {
    $mac_name = '��';   // error���ϵ���̾��֥��
}

//////////// �����ƥ�ޥ�������������̾����
$query = "select midsc,mzist from miitem where mipn='$parts_no'";
$res = array();
if ( ($rows=getResult2($query,$res)) >= 1) {        // ����̾����
    $buhin_name    = mb_substr($res[0][0],0,10);
    $buhin_zaisitu = mb_substr($res[0][1],0,7);
} else {
    $buhin_name    = "";
    $buhin_zaisitu = "";
}

//////////// equip_state_summary �ơ��֥뤫�齸�׷�̼���
$query = "select state as �����ֹ�
                , state_name as ��ž����
                , total_time as ���֡�ʬ��
                , state_type as ���֥ơ��֥�
            from
                equip_state_summary2_moni
            where
                mac_no=$mac_no and plan_no='$plan_no' and koutei=$koutei
            order by
                state ASC
        ";
$res   = array();
$field = array();
if (($rows = getResultWithField2($query, $field, $res)) <= 0) {
    $_SESSION['s_sysmsg'] .= sprintf("���ץե�����˥ǡ���������ޤ��󡣵����ֹ桧%s �ײ��ֹ桧%s ������%s", $mac_no, $plan_no, $koutei);
    header('Location: ' . H_WEB_HOST . $menu->RetUrl());                   // ľ���θƽи������
    exit();
} else {
    $num = count($field);       // �ե�����ɿ�����
    /////////////// ��׻������η׻�
    $time_all_sum = 0;                  // ���׻���
    $time_ope_sum = 0;                  // �Ÿ�OFF�������׻���
    $time_act_sum = 0;                  // �ʼ���ܼ�ư��ž��̵�ͱ�ž
    $time_sto_sum = 0;                  // �Ÿ�OFF�ʳ�����߻���
    for ($r=0; $r<$rows; $r++) {
        if ($res[$r]['��ž����'] == "�� ��") {
            continue;       ///// ���Ǥ����ƥ��åȤ���
        }
        $time_all_sum += $res[$r]['���֡�ʬ��'];
        if ($res[$r]['��ž����'] != "�Ÿ�OFF") {
            $time_ope_sum += $res[$r]['���֡�ʬ��'];
        }
        if ( ($res[$r]['��ž����'] == "�ʼ���") || ($res[$r]['��ž����'] == "��ư��ž") || ($res[$r]['��ž����'] == "̵�ͱ�ž") ) {
            $time_act_sum += $res[$r]['���֡�ʬ��'];
        }
        if ( ($res[$r]['��ž����'] != "�ʼ���") && ($res[$r]['��ž����'] != "��ư��ž") && ($res[$r]['��ž����'] != "̵�ͱ�ž") && ($res[$r]['��ž����'] != "�Ÿ�OFF") ) {
            $time_sto_sum += $res[$r]['���֡�ʬ��'];
        }
    }
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
<?= $menu->out_site_java() ?>
<?= $menu->out_css() ?>
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
    document.mhForm.backwardStack.focus();  // �嵭��IE�ΤߤΤ���NN�б�
}
// -->
</script>

    <!-- �ե��������ξ�� -->
<link rel='stylesheet' href='../equipment.css?<?= $uniq ?>' type='text/css' media='screen'>

<style type="text/css">     <!-- ��������� -->
<!--
th {
    background-color:yellow;
    color:blue;
    font:bold 11pt;
    font-family: monospace;
}
.table_font {
    font: 11.5pt;
    font-family: monospace;
}
.ext_font {
    background-color:blue;
    color:yellow;
    font:bold 12.0pt;
    font-family: monospace;
}
-->
</style>
</head>
<body onLoad='set_focus()'>
    <center>
<?= $menu->out_title_border() ?>
        
        <!----------- ������ ����̾��������θ��Ф� ------------->
        <table width=100% bordercolordark='white' bordercolorlight='#bdaa90' bgcolor='#d6d3ce' align='center' cellspacing='0' cellpadding='1' border='1'>
            <tr class='sub_font'>
                <!--
                <form method='post' action='<?php echo $menu->out_self() ?>'>
                    <td width='52' bgcolor='green'align='center' valign='center'>
                        <input class='pt11b' type='submit' name='backward' value='����'>
                    </td>
                </form>
                <form method='post' action='<?php echo $menu->out_self() ?>'>
                    <td width='52' bgcolor='green'align='center' valign='center'>
                        <input class='pt11b' type='submit' name='forward' value='����'>
                    </td>
                </form>
                -->
                <td align='center' nowrap>����No</td>
                <td align='center' nowrap><?php echo $parts_no ?></td>
                <td align='center' nowrap>����̾</td>
                <td class='pick_font' align='center' nowrap><?php echo $buhin_name ?></td>
                <td align='center' nowrap>���</td>
                <td class='pick_font' align='center' nowrap><?php echo $buhin_zaisitu ?></td>
                <td align='center' nowrap>�ײ�No</td>
                <td align='center' nowrap><?php echo $plan_no ?></td>
                <td align='center' nowrap>����</td>
                <td align='center' nowrap><?php echo $koutei ?></td>
                <td align='center' nowrap>�ײ��</td>
                <td align='right'  nowrap><?php echo number_format($plan_cnt) ?></td>
            </tr>
        </table>
        
        <!-- <hr color='797979'> -->
        
        <!--------------- ��������ܺ�ɽ ���Ф� ���Ԥ�ɽ������ -------------------->
        <table width=100% bordercolordark='white' bordercolorlight='#bdaa90' bgcolor='#d6d3ce' align='center' border='3' cellspacing='0' cellpadding='2'>
            <tr>
                <th nowrap>��</th>
                <th nowrap>����No</th><th nowrap>ǯ����</th><th nowrap>��ʬ��</th><th nowrap>�� ��</th>
                <th nowrap>����</th><th nowrap>�ù���</th><th nowrap>����1</th><th nowrap>����2</th>
                <th nowrap>����3</th><th nowrap>����4</th><th nowrap>����5</th>
            </tr>
            <tr class='table_font'>
                <td class='ext_font' align='center' nowrap>����</td>
                <td align='center' nowrap><?php echo $res_str[0]['mac_no'] ?></td>
                <td align='center' nowrap><?php echo $res_str[0]['date'] ?></td>
                <td align='center' nowrap><?php echo $res_str[0]['time'] ?></td>
                <td align='center' nowrap><?php echo $mac_name ?></td>
                <?php
                $mac_state_txt = equip_machine_state($mac_no, $res_str[0]['mac_state'],$bg_color,$txt_color);
                print(" <td align='center' nowrap bgcolor='$bg_color'><font color='$txt_color'>" . $mac_state_txt . "</font></td>\n");
                ?>
                <td align='right' nowrap><?php echo number_format($res_str[0]['work_cnt']) ?></td>
                <?php
                for ($i=5; $i<=9; $i++) {
                    echo " <td align='center' nowrap>-</td>\n";
                }
                ?>
            </tr>
            <tr class='table_font'>
                <td class='ext_font' align='center' nowrap>��λ</td>
                <td align='center' nowrap><?php echo $res_end[0]['mac_no'] ?></td>
                <td align='center' nowrap><?php echo $res_end[0]['date'] ?></td>
                <td align='center' nowrap><?php echo $res_end[0]['time'] ?></td>
                <td align='center' nowrap><?php echo $mac_name ?></td>
                <?php
                $mac_state_txt = equip_machine_state($mac_no, $res_end[0]['mac_state'],$bg_color,$txt_color);
                print(" <td align='center' nowrap bgcolor='$bg_color'><font color='$txt_color'>" . $mac_state_txt . "</font></td>\n");
                ?>
                <td align='right' nowrap><?php echo number_format($res_end[0]['work_cnt']) ?></td>
                <?php
                for ($i=5; $i<=9; $i++) {
                    echo " <td align='center' nowrap>-</td>\n";
                }
                ?>
            </tr>
        </table>
        
        <hr color='797979'>
        
        <!--------------- ����������ʸ��ɽ��ɽ������ -------------------->
        <table bgcolor='#d6d3ce' align='center' cellspacing="0" cellpadding="3" border='1'>
            <tr><td> <!----------- ���ߡ�(�ǥ�������) ------------>
        <table bordercolordark='white' bordercolorlight='#bdaa90' bgcolor='#d6d3ce' align='center' cellspacing="0" cellpadding="3" border='1'>
            <THEAD>
                <!-- �ơ��֥� �إå�����ɽ�� -->
                <tr>
                    <th width='10'>No.</th>        <!-- �ԥʥ�С���ɽ�� -->
                <?php
                for ($i=0; $i<$num; $i++) {             // �ե�����ɿ�ʬ���֤�
                ?>
                    <th><?php echo $field[$i] ?></th>
                <?php
                }
                ?>
                </tr>
            </THEAD>
            <TFOOT>
                <!-- ���ߤϥեå����ϲ���ʤ� -->
            </TFOOT>
            <TBODY>
                <?php
                for ($r=0; $r<$rows; $r++) {
                ?>
                    <tr>
                        <td class='pt11b' align='right'><?php echo ($r + $offset + 1) ?></td>    <!-- �ԥʥ�С���ɽ�� -->
                    <?php
                    for ($i=0; $i<$num; $i++) {         // �쥳���ɿ�ʬ���֤�
                        if ($i == 0) {                      // �����ֹ�
                            echo "<td align='center' class='pt12b' bgcolor='#ffffc6'>{$res[$r][$i]}</td>\n";
                        } elseif ($i == 1) {                // ��ž����
                            $tmp = equip_machine_state($mac_no, $r, $bg_color, $txt_color);
                            echo "<td width='100' align='center' class='pt12b' bgcolor='$bg_color'><font color='$txt_color'>{$res[$r][$i]}</font></td>\n";
                        } elseif ($i == 2) {                // ��ž����
                            echo "<td width='100' align='right' class='pt12b' bgcolor='#ffffc6'>" . number_format($res[$r][$i]) . "</td>\n";
                        } elseif ($i == 3) {                // state_type
                            if ($res[$r][$i] == 1) {
                                if ( ($res[$r][0] >= 0) && ($res[$r][0] <= 5) ) {
                                    echo "<td align='center' class='pt10'>�ͥåȥ��(��α��)</td>\n";
                                } else {
                                    echo "<td align='center' class='pt10'>��������ޥ���#500</td>\n";
                                }
                            } elseif ($res[$r][$i] >= 101 && $i <= 200) {  // �ͥåȥ�ˤȥ����꡼�����å��Υ����
                                if ( ($res[$r][0] >= 0) && ($res[$r][0] <= 3) ) {
                                    echo "<td align='center' class='pt10'>�ͥåȥ��(��α��)</td>\n";
                                } else {
                                    echo "<td align='center' class='pt10'>�����꡼�����å�</td>\n";
                                }
                            } else {
                                if ( ($res[$r][0] == 0) || ($res[$r][0] == 1) || ($res[$r][0] == 3) ) {
                                    echo "<td align='center' class='pt10'>�ƣףӥơ��֥�</td>\n";
                                } else {
                                    echo "<td align='center' class='pt10'>�����꡼�����å�</td>\n";
                                }
                            }
                        } else {                            // ����¾(�������Ǥ����곰)
                            echo "<td align='center' class='pt12b' bgcolor='#ffffc6'>{$res[$r][$i]}</td>\n";
                        }
                        // <!-- ����ץ�<td rowspan='2' colspan='3' width='200' align='center' class='pt10b' bgcolor='#ffffc6'>��</td> -->
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
        
        <hr color='797979'>
        
        <!--------------- ����������ɽ��ɽ������ -------------------->
        <table bgcolor='#d6d3ce' align='center' cellspacing="0" cellpadding="3" border='1'>
            <tr><td> <!----------- ���ߡ�(�ǥ�������) ------------>
        <table bordercolordark='white' bordercolorlight='#bdaa90' bgcolor='#d6d3ce' align='center' cellspacing="0" cellpadding="3" border='1'>
            <thead>
                <tr>
                    <th>�� �� �� �� ��</th><th>�Ÿ�OFF�������׻���</th><th>�ʼ���ܼ�ư��ž��̵�ͱ�ž</th>
                    <th>�Ÿ�OFF�ʳ�����߻���</th><th>ñ��</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td align='center' nowrap class='pt12b'><?php echo number_format($time_all_sum) ?></td>
                    <td align='center' nowrap class='pt12b'><?php echo number_format($time_ope_sum) ?></td>
                    <td align='center' nowrap class='pt12b'><?php echo number_format($time_act_sum) ?></td>
                    <td align='center' nowrap class='pt12b'><?php echo number_format($time_sto_sum) ?></td>
                    <td align='center' nowrap class='pt12b'>ʬ</td>
                </tr>
            </tbody>
        </table>
            </td></tr>
        </table> <!----------------- ���ߡ�End ------------------>
    </center>
</body>
</html>
<?php
ob_end_flush();                 // ���ϥХåե���gzip���� END
?>
