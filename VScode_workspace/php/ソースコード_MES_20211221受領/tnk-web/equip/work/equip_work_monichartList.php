<?php
//////////////////////////////////////////////////////////////////////////////
// ��Ω������ư���������ƥ�� ��ž����ɽ ɽ��  List�ե졼��                 //
// Copyright (C) 2021-2021 norihisa_ooya@nitto-kohki.co.jp                  //
// Changed history                                                          //
// 2021/03/26 Created  equip_work_monichartList.php                         //
//////////////////////////////////////////////////////////////////////////////
// ini_set('error_reporting', E_ALL | E_STRICT);   // E_ALL='2047' debug ��
// ini_set('display_errors', '1');             // Error ɽ�� ON debug �� ��꡼���女����
ob_start('ob_gzhandler');                   // ���ϥХåե���gzip����
session_start();                            // ini_set()�μ��˻��ꤹ�뤳�� Script �Ǿ��
require_once ('../equip_function.php');     // ������˥塼 ���� function (function.php��ޤ�)
require_once ('../../MenuHeader.php');      // TNK ������ menu class
require_once ('../../tnk_func.php');        // TNK ������ tnk_func
access_log();                               // Script Name �ϼ�ư����

///// TNK ���ѥ�˥塼���饹�Υ��󥹥��󥹤����
$menu = new MenuHeader();                   // ǧ�ڥ����å�0=���̰ʾ� �����=���å������ �����ȥ�̤����

////////////// ����������
$menu->set_site(40, 10);                    // site_index=40(������˥塼) site_id=10(����ɽ)
////////////// target����
$menu->set_target('_parent');               // �ե졼���Ǥ�������target°����ɬ��

$mac_no  = @$_SESSION['work_mac_no'];
$plan_no = @$_SESSION['work_plan_no'];
$koutei  = @$_SESSION['work_koutei'];
if (isset($_REQUEST['select'])) {
    $select = $_REQUEST['select'];
} else {
    $select = 'NG';
}
if ($mac_no == '') {
    $select = 'NG';
}

///////////// �ʰץڡ�������
$limit = 2000;      // ����
if ($select != 'GO') {
    if (isset($_REQUEST['forward'])) {
        $_SESSION['equip_work_chart_offset'] += $limit;
        $offset = $_SESSION['equip_work_chart_offset'];
    } elseif (isset($_REQUEST['backward'])) {
        $_SESSION['equip_work_chart_offset'] -= $limit;
        if ($_SESSION['equip_work_chart_offset'] < 0) {
            $_SESSION['equip_work_chart_offset'] = 0;
            $_SESSION['s_sysmsg'] = '���ǤϤ���ޤ���';
        }
        $offset = $_SESSION['equip_work_chart_offset'];
    } else {
        $offset = 0;    // �����
        $_SESSION['equip_work_chart_offset'] = $offset;
    }
} else {
    $offset = $_SESSION['equip_work_chart_offset'];
}

if ($select == 'GO') {
    //////////// �إå�����곫�������Ƚ�λ�����μ���
    $query = "select to_char(str_timestamp AT TIME ZONE 'JST', 'YYYY-MM-DD HH24:MI:SS') as str_timestamp
                    , to_char(end_timestamp AT TIME ZONE 'JST', 'YYYY-MM-DD HH24:MI:SS') as end_timestamp
                    , plan_no
                    , koutei
                from
                    equip_work_log2_header_moni
                where
                    mac_no={$mac_no} and plan_no='{$plan_no}' and koutei={$koutei}
    ";
    $res = array();
    if ( getResult($query, $res) <= 0) {
        $_SESSION['s_sysmsg'] = "{$mac_no}�ϱ�ž���Ϥ���Ƥ��ޤ���";
    } else {
        $str_timestamp = $res[0]['str_timestamp'];
        $end_timestamp = $res[0]['end_timestamp'];
    }
    
    ////////////// ���٥ǡ����μ���
    //                -- date_time >= (CURRENT_TIMESTAMP - interval '168 hours')      -- �ƥ����Ѥ˻Ĥ�(168=7���˷��Ѵ������)
    //                -- and date_time <= (CURRENT_TIMESTAMP - interval '0 hours')
    // TIMESTAMP���ξ��� CAST ���ʤ��� Seq Scan �Ȥʤ�Τ����  index��Ȥ���������Ū�˷��Ѵ���ɬ��
    $query = "select to_char(date_time AT TIME ZONE 'JST', 'YYYY/MM/DD') as date
                    ,to_char(date_time AT TIME ZONE 'JST', 'HH24:MI:SS') as time
                    ,mac_state
                    ,work_cnt
                from
                    equip_work_log2_moni
                WHERE   plan_no='{$plan_no}' and mac_no={$mac_no} and koutei={$koutei}
                ORDER BY date_time DESC
                limit
                    $limit
                offset
                    $offset
    ";
    /*
    $query = "select to_char(date_time AT TIME ZONE 'JST', 'YYYY/MM/DD') as date
                    ,to_char(date_time AT TIME ZONE 'JST', 'HH24:MI:SS') as time
                    ,mac_state
                    ,work_cnt
                from
                    equip_work_log2_moni
                where
                    equip_index_moni(mac_no, plan_no, koutei, date_time) >= '{$mac_no}{$plan_no}{$koutei}00000000000000'
                and
                    equip_index_moni(mac_no, plan_no, koutei, date_time) <= '{$mac_no}{$plan_no}{$koutei}99999999999999'
                    -- date_time >= CAST('$str_timestamp' as TIMESTAMP)
                    -- and date_time <= CURRENT_TIMESTAMP
                    -- and mac_no={$mac_no} and plan_no='{$plan_no}' and koutei={$koutei}
                order by
                    equip_index_moni(mac_no, plan_no, koutei, date_time) DESC
                    -- date_time DESC
                limit
                    $limit
                offset
                    $offset
    ";
    */
    $res = array();
    if ( ($rows=getResult2($query, $res)) <= 0) {
        if ($offset == 0) {
            $_SESSION['s_sysmsg'] = "<font color='yellow'>�����ֹ桧{$mac_no}�β�ư�ǡ���������ޤ���</font>";
        } else {
            $_SESSION['s_sysmsg'] = "<font color='yellow'>���ǤϤ���ޤ���</font>";
            header('Location: ' . H_WEB_HOST . $menu->out_self() . '?backward=1&select=OK');
            exit();
        }
        $res[0][2] = 0;     // 0�ǽ����
        $res[0][3] = 0;
    } else {
        $num = count($res[0]);
        for ($i=0; $i<$rows; $i++) {
            $hour   = substr($res[$i][1], 0, 2);
            $minute = substr($res[$i][1], 3, 2);
            $second = substr($res[$i][1], 6, 2);
            $month  = substr($res[$i][0], 5, 2);
            $day    = substr($res[$i][0], 8, 2);
            $year   = substr($res[$i][0], 0, 4);
            $timestamp = mktime($hour, $minute, $second, $month, $day, $year);
            if (isset($timestamp_pre)) {
                $res[$i-1]['cycle'] = $timestamp - $timestamp_pre;
                if ($res[$i-1]['cycle'] < 0) $res[$i-1]['cycle'] = $res[$i-1]['cycle'] * (-1);
                $res[$i-1]['cycle_m'] = number_format((int)($res[$i-1]['cycle'] / 60)); // ʬ��������
                $odd = sprintf('%02d', $res[$i-1]['cycle'] % 60);                     // �ä�;��
                $res[$i-1]['cycle_m'] = ($res[$i-1]['cycle_m'] . ':' . $odd);           // ʬ���äη�����
                $res[$i-1]['cycle'] = number_format($res[$i-1]['cycle']);
            }
            $timestamp_pre = $timestamp;
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
<meta http-equiv="Content-Script-Type" content="text/javascript">
<title><?php echo $menu->out_title() ?></title>
<?php if ($_SESSION['s_sysmsg'] != '') echo $menu->out_site_java() ?>
<?php echo $menu->out_css() ?>
<style type='text/css'>
<!--
th {
    font-size:      11.5pt;
    font-weight:    bold;
    font-family:    monospace;
}
.item {
    position: absolute;
    /* top: 100px; */
    left: 90px;
}
.msg {
    position: absolute;
    top:  100px;
    left: 350px;
}
.mark {
    background-color:   #ceffce;
}
a {
    text-decoration:    none;
}
a:hover {
    text-decoration:    underline;
    background-color:   #ceffce;
}
-->
</style>
<script type='text/javascript' language='JavaScript'>
function init() {
<?php if ($select == 'OK') { ?>
    document.MainForm.submit();
<?php } ?>
}
function setMarks(obj) {
    if (obj.className == "") {
        obj.className = "mark";
    } else {
        obj.className = "";
    }
}
</script>
<?php if ($select == 'OK') { ?>
<form name='MainForm' action='<?php echo $menu->out_self() ?>' method='post'>
    <input type='hidden' name='select' value='GO'>
</form>
<?php } ?>
</head>
<body onLoad='init()'>
    <center>
        <?php if ($select == 'NG') { ?>
        <table border='0' class='msg'>
            <tr>
                <td>
                    <b style='color: teal;'>���������򤷤Ʋ�������</b>
                </td>
            </tr>
        </table>
        <?php } elseif ($select == 'OK') { ?>
        <table border='0' class='msg'>
            <tr>
                <td>
                    <b style='color: blue;'>������Ǥ������Ԥ���������</b>
                </td>
            </tr>
        </table>
        <?php } else { ?>
        <!-------------- �ܺ٥ǡ���ɽ���Τ����ɽ����� -------------->
        <table class='item' width='80.0%' bgcolor='#d6d3ce'  border='1' cellspacing='0' cellpadding='2'>
           <tr><td> <!-- ���ߡ�(�ǥ�������) -->
        <table width=100% class='winbox_field' border='1' cellspacing='1' cellpadding='1'>
        <?php
            /////////////// ���߻���κǿ�������ư�ǤΤ���
            if ($offset == 0) {
                echo "<tr>\n";
                echo " <td class='winbox' align='center' nowrap width='10%' bgcolor='blue'><font color='yellow'><b>�ǿ�</b></font></td>\n";
                echo " <td class='winbox' align='center' nowrap width='15%'>", date("Y/m/d",time()), "</td>\n";
                echo " <td class='winbox' align='center' nowrap width='15%'>", date("H:i:s",time()), "</td>\n";
                $mac_state_txt = equip_machine_state($mac_no, $res[0][2], $bg_color, $txt_color);
                echo " <td class='winbox' align='center' nowrap width='15%' bgcolor='$bg_color'><font color='$txt_color'>{$mac_state_txt}</font></td>\n";
                echo " <td class='winbox' align='right' nowrap width='15%'>", number_format($res[0][3]), "</td>\n";
                echo " <td class='winbox' align='center' nowrap width='20%'>-</td>\n";
                echo " <td class='winbox' align='center' nowrap width='10%'>-</td>\n";
                echo "</tr>\n";
            }                       // �ǿ����� END
            for ($i=0; $i<$rows; $i++) {    // �ʲ��ξ��ϥƥ����� 2007/08/21
                if ($res[$i][0] <= '2007/09/14' && $res[$i][1] <= '09:46:58') {
                    if ($mac_no == 4604 && isset($res[$i]['cycle']) && !($res[$i]['cycle'] == 10 || $res[$i]['cycle'] == 11 || $res[$i]['cycle'] == 9) ) {
                        echo "<tr onClick='setMarks(this);' style='background-color:red; color:white;'>\n";
                    } else {
                        echo "<tr onClick='setMarks(this);'>\n";
                    }
                } else {                    // �ʲ��ξ���2007/09/14���ɲ�
                    if ($mac_no == 4604 && isset($res[$i]['cycle']) && !($res[$i]['cycle'] == 30 || $res[$i]['cycle'] == 31 || $res[$i]['cycle'] == 29) ) {
                        echo "<tr onClick='setMarks(this);' style='background-color:red; color:white;'>\n";
                    } else {
                        echo "<tr onClick='setMarks(this);'>\n";
                    }
                }
                echo "<td class='winbox' align='center' nowrap width='10%'>" . ($i+1+$offset) . "</td>\n";
                for ($j=0; $j<$num; $j++) {
                    switch ($j) {
                    case 0:     // ǯ����
                        echo " <td class='winbox' align='center' nowrap width='15%'>" . $res[$i][$j] . "</td>\n";
                        break;
                    case 1:     // ��ʬ��
                        echo " <td class='winbox' align='center' nowrap width='15%'>" . $res[$i][$j] . "</td>\n";
                        break;
                    case 2:     // ����
                        $mac_state_txt = equip_machine_state($mac_no, $res[$i][$j], $bg_color, $txt_color);
                        echo " <td class='winbox' align='center' nowrap width='15%' bgcolor='$bg_color'><font color='$txt_color'>" . $mac_state_txt . "</font></td>\n";
                        break;
                    case 3:     // �ù���
                        echo " <td class='winbox' align='right' nowrap width='15%'>" . number_format($res[$i][$j]) . "</td>\n";
                        break;
                    default:
                        if ($res[$i][$j] == '') {
                            echo " <td class='winbox' align='center' nowrap>-</td>\n";
                        } else {
                            echo " <td class='winbox' align='center' nowrap>" . $res[$i][$j] . "</td>\n";
                        }
                    }
                }
                if (isset($res[$i]['cycle'])) {
                    echo " <td class='winbox' align='right' nowrap width='20%'>{$res[$i]['cycle']}</td>\n";
                } else {
                    echo " <td class='winbox' align='center' nowrap width='20%'>-</td>\n";
                }
                if (isset($res[$i]['cycle_m'])) {
                    echo " <td class='winbox' align='right' nowrap width='10%'>{$res[$i]['cycle_m']}</td>\n";
                } else {
                    echo " <td class='winbox' align='center' nowrap width='10%'>-</td>\n";
                }
                echo "</tr>\n";
            }
        ?>
        </table> <!----- ���ߡ� End ----->
            </td></tr>
        </table>
        <?php } ?>
    </center>
</body>
<?php echo $menu->out_alert_java() ?>
</html>
<?php ob_end_flush(); // ���ϥХåե���gzip���� END ?>
