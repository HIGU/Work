<?php
//////////////////////////////////////////////////////////////////////////////
// ������ư���������ƥ�� ���߱�ž�� ����ɽ ɽ��  List�ե졼��              //
// Copyright (C) 2004-2005 2007 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp //
// Changed history                                                          //
// 2004/09/09 Created  equip_work_allList.php                               //
// 2004/11/29 ����̾��width=70��72���ѹ�(20PM�������Τ���)����̾12ʸ����11ʸ�� //
// 2005/08/05 ɽ��nowrap�ɲä�allHeader�ȹ�碌�뤿��width='100%'����¾�ɲ� //
// 2007/05/24 �ե졼���Ǥ��饤��饤��ե졼���Ǥ��ѹ��������⢪�ؼ������ѹ�//
//              ����¾�ǥ������ѹ� ���Ǥ� backup/ �ˤ���                    //
// 2007/07/06 ���åץإ�פ˵����ֹ桦����ա����١����� ɽ���������ɲ�     //
//////////////////////////////////////////////////////////////////////////////
ini_set('error_reporting', E_ALL);          // E_ALL='2047' debug ��
// ini_set('display_errors', '1');             // Error ɽ�� ON debug �� ��꡼���女����
ob_start('ob_gzhandler');                   // ���ϥХåե���gzip����
session_start();                            // ini_set()�μ��˻��ꤹ�뤳�� Script �Ǿ��
require_once ('../equip_function.php');     // ������˥塼 ���� function (function.php��ޤ�)
require_once ('../../MenuHeader.php');      // TNK ������ menu class
require_once ('../EquipAllGraphClass.php');    // ������Ư���� Graph class
access_log();                               // Script Name �ϼ�ư����

///// TNK ���ѥ�˥塼���饹�Υ��󥹥��󥹤����
$menu = new MenuHeader();                   // ǧ�ڥ����å�0=���̰ʾ� �����=���å������ �����ȥ�̤����

////////////// ����������
$menu->set_site(40, 9);                     // site_index=40(������˥塼) site_id=9(��ž�����)
////////////// target����
$menu->set_target('_parent');               // �ե졼���Ǥ�������target°����ɬ��

//////////// ��ʬ��ե졼��������Ѥ���
$menu->set_self(EQUIP2 . 'work/equip_work_all.php');
//////////// �ƽ����action̾�ȥ��ɥ쥹����
$menu->set_action('��ž�����', EQUIP2 . 'work/equip_work_graph.php');
$menu->set_action('���߲�ưɽ', EQUIP2 . 'work/equip_work_chart.php');
$menu->set_action('�������塼��', EQUIP2 . 'plan/equip_plan_graph.php');
// $menu->set_frame('��ž�����', EQUIP2 . 'work/equip_work_graph.php');
// $menu->set_frame('���߲�ưɽ', EQUIP2 . 'work/equip_work_chart.php');

if (isset($_REQUEST['factory'])) {
    $factory = $_REQUEST['factory'];
} else {
    ///// �ꥯ�����Ȥ�̵����Х��å���󤫤鹩���ʬ��������롣(�̾�Ϥ��Υѥ�����)
    $factory = @$_SESSION['factory'];
}

//////////// �����ޥ��������������ֹ桦����̾�Υꥹ�Ȥ����(�ƻ����ꤵ��Ƥ���ʪ)
if ($factory == '') {
    $query = "select mac_no                     AS mac_no
                    , substr(mac_name, 1, 7)    AS mac_name
                from
                    equip_machine_master2
                where
                    survey='Y'
                    and
                    mac_no!=9999
                order by mac_no ASC
    ";
} else {
    $query = "select mac_no                     AS mac_no
                    , substr(mac_name, 1, 7)    AS mac_name
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
$res = array();
if (($rows = getResult($query, $res)) < 1) {
    $_SESSION['s_sysmsg'] .= "<font color='yellow'>�����ޥ���������Ͽ������ޤ���</font>";
    $view = 'NG';
} else {
    $view = 'OK';
}

if ($view == 'OK') {
    for ($r=0; $r<$rows; $r++) {
        ////////// ��ư�椫�إå���������å�
        $query = "select  siji_no
                        , koutei
                        , parts_no
                        , substr(midsc, 1, 11)      AS parts_name
                        , plan_cnt
                        -- , to_char(str_timestamp AT TIME ZONE 'JST', 'YYYY-MM-DD HH24:MI:SS') as str_datetime
                        , to_char(str_timestamp AT TIME ZONE 'JST', 'YY/MM/DD HH24:MI') as str_datetime
                    from
                        equip_work_log2_header
                    left outer join
                        miitem
                    on
                        (parts_no=mipn)
                    where
                        mac_no={$res[$r]['mac_no']}
                        and
                        work_flg IS TRUE
                    offset 0 limit 1
        ";
        $hed = array();
        if (getResult($query, $hed) > 0) {
            $res[$r]['siji_no']         = $hed[0]['siji_no'];
            $res[$r]['koutei']          = $hed[0]['koutei'];
            $res[$r]['parts_no']        = $hed[0]['parts_no'];
            $res[$r]['parts_name']      = mb_convert_kana($hed[0]['parts_name'], 'k');  // Ⱦ�ѥ��ʤ��Ѵ�
            $res[$r]['plan_cnt']        = number_format($hed[0]['plan_cnt']);
            $res[$r]['str_datetime']    = $hed[0]['str_datetime'];
            // �ǿ������٥ǡ�������
            $query = "select to_char(date_time AT TIME ZONE 'JST', 'YY/MM/DD') as date
                            ,to_char(date_time AT TIME ZONE 'JST', 'HH24:MI:SS') as time
                            ,mac_state
                            ,work_cnt
                        from
                            equip_work_log2
                        where
                            equip_index(mac_no, siji_no, koutei, date_time) <= '{$res[$r]['mac_no']}{$res[$r]['siji_no']}{$res[$r]['koutei']}99999999999999'
                            and
                            equip_index(mac_no, siji_no, koutei, date_time) >= '{$res[$r]['mac_no']}{$res[$r]['siji_no']}{$res[$r]['koutei']}00000000000000'
                        order by
                            equip_index(mac_no, siji_no, koutei, date_time) DESC
                        offset 0 limit 1
            ";
            $log = array();
            if (getResult($query, $log) > 0) {
                $res[$r]['date']        = $log[0]['date'];
                $res[$r]['time']        = $log[0]['time'];
                $res[$r]['mac_state']   = $log[0]['mac_state'];
                $res[$r]['work_cnt']    = number_format($log[0]['work_cnt']);
            } else {
                $res[$r]['date']        = '&nbsp;';
                $res[$r]['time']        = '&nbsp;';
                $res[$r]['mac_state']   = '&nbsp;';
                $res[$r]['work_cnt']    = '&nbsp;';
            }
        } else {
                $res[$r]['date']        = '̤�ؼ�';
                $res[$r]['time']        = '&nbsp;';
                $res[$r]['mac_state']   = '&nbsp;';
                $res[$r]['work_cnt']    = '&nbsp;';
            $res[$r]['siji_no']         = '&nbsp;';
            $res[$r]['koutei']          = '&nbsp;';
            $res[$r]['parts_no']        = '&nbsp;';
            $res[$r]['parts_name']      = '&nbsp;';
            $res[$r]['plan_cnt']        = '&nbsp;';
            $res[$r]['str_datetime']    = '&nbsp;';
        }
    }
    $num = count($res[0]);
}

// ����պ���
$mac_no  = $res[2]['mac_no'];
$siji_no = $res[2]['siji_no'];
$koutei  = $res[2]['koutei'];

$mac_no  = '1346';
$siji_no = '72587';
$koutei  = '1';

if (isset($_REQUEST['select'])) {
    $select = $_REQUEST['select'];
} else {
    $select = 'NG';
}
if ($mac_no == '') {
    $select = 'NG';
} else {
    $select = '';
}
if (isset($_REQUEST['equip_xtime'])) {
    $_SESSION['equip_xtime'] = $_REQUEST['equip_xtime'];
}

if ($select == '') {
    if (isset($_SESSION['equip_xtime'])) {
        $equip_xtime = $_SESSION['equip_xtime'];
        unset($_SESSION['equip_xtime']);
    } else {
        $equip_xtime = 12;
    }
    /////////// ������������դΥ��󥹥��󥹺��� ������180(������٤�360)
    $equip_graph = new EquipGraph($mac_no, $siji_no, $koutei, 180);
    $equip_graph->set_xtime($equip_xtime);      // ����դδ�˾�λ��ּ�������
    $equip_xtime = $equip_graph->out_xtime();   // ����դλ��ּ��Υ������������ͤ����
    // ����դ��ϰ���ν��� DATE TIME �μ���  ����(strDate, strTime, endDate, endTime)
    $graphDateTime = $equip_graph->out_graph_timestamp();
    // $graph_name = ('graph/equip' . session_id() . '.png');
    $graph_name = 'graph/equip_work_graph.png';
    $equip_graph->out_graph($graph_name);
    $graph_page = $_SESSION['equip_graph_page'];    // ɽ���ڡ������μ���
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
<?php if ($_SESSION['s_sysmsg'] != '') echo $menu->out_site_java() ?>
<?= $menu->out_css() ?>
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
.winbox {
    border-style: solid;
    border-width: 1px;
    border-top-color:       #FFFFFF;
    border-left-color:      #FFFFFF;
    border-right-color:     #999999;
    border-bottom-color:    #999999;
    background-color:#d6d3ce;
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
-->
</style>
<script language='JavaScript'>
function init() {
<?php if ($select == 'OK') { ?>
    document.MainForm.submit();
<?php } ?>
}
</script>
<!--
<?php if ($select == 'OK') { ?>
<form name='MainForm' action='<?= $menu->out_self() ?>' method='post'>
    <input type='hidden' name='select' value='GO'>
</form>
<?php } ?>
-->
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
        <!-------------- �����ɽ���Υڡ�������ȥ������ -------------->
        <table width='100%' border='0'>
            <tr><td align='center'>
            <?= "<img src='", $graph_name, "?", uniqid(rand(),1), "' alt='�ù��� ���� �����' border='0'>\n"; ?>
            </td></tr>
        </table>
        <?php } ?>
    </center>
</body>
<?php if ($select == 'OK') { ?>
<script language='JavaScript'>
<!--
setTimeout('location.replace("equip_work_allgraphList.php?select=<?=$select?>&equip_xtime=<?=$equip_xtime?>")',10000);      // ������ѣ�����
// -->
</script>
<? } ?>
</html>
<?php ob_end_flush(); // ���ϥХåե���gzip���� END ?>
