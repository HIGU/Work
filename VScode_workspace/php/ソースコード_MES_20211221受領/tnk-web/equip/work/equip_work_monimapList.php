<?php
//////////////////////////////////////////////////////////////////////////////
// ������ư���������ƥ�θ��߱�ž���������ޥå�ɽ��(�쥤������)List�ե졼�� //
// ��������Ω������                                                         //
// Copyright (C) 2021-2021 Norihisa.Ohya norhisa_ooya@nitto-kohki.co.jp     //
// Changed history                                                          //
// 2021/06/22 Created  equip_work_monimapList.php                           //
//////////////////////////////////////////////////////////////////////////////
ini_set('error_reporting', E_ALL);          // E_ALL='2047' debug ��
// ini_set('display_errors', '1');             // Error ɽ�� ON debug �� ��꡼���女����
// ini_set('implicit_flush', 'off');           // echo print �� flush �����ʤ�(�٤��ʤ뤿��) CLI CGI��
// ini_set('max_execution_time', 1200);        // ����¹Ի���=20ʬ CLI CGI��
ob_start('ob_gzhandler');                   // ���ϥХåե���gzip����
session_start();                            // ini_set()�μ��˻��ꤹ�뤳�� Script �Ǿ��
require_once ('../equip_function.php');     // ������˥塼 ���� function (function.php��ޤ�)
require_once ('../../MenuHeader.php');      // TNK ������ menu class
access_log();                               // Script Name �ϼ�ư����

///// TNK ���ѥ�˥塼���饹�Υ��󥹥��󥹤����
$menu = new MenuHeader();                   // ǧ�ڥ����å�0=���̰ʾ� �����=���å������ �����ȥ�̤����

////////////// ����������
$menu->set_site(40, 12);                    // site_index=40(������˥塼) site_id=12(�ޥåװ���)

//////////// �����ȥ�̾(�������Υ����ȥ�̾�ȥե�����Υ����ȥ�̾)
$menu->set_title('��ư���� �쥤������ ɽ��');
//////////// ɽ�������
// $menu->set_caption('��������');

////////////// target����
$menu->set_target('_parent');               // �ե졼���Ǥ�������target°����ɬ��
//////////// ��ʬ��ե졼��������Ѥ���
$menu->set_self(EQUIP2 . 'work/equip_work_monimap.php');
//////////// �ƽ����action̾�ȥ��ɥ쥹����
$menu->set_action('��ž�����', EQUIP2 . 'work/equip_work_monigraph.php');
$menu->set_action('���߲�ưɽ', EQUIP2 . 'work/equip_work_monichart.php');
$menu->set_action('�������塼��', EQUIP2 . 'plan/equip_plan_monigraph.php');
// $menu->set_frame('��ž�����', EQUIP2 . 'work/equip_work_graph.php');
// $menu->set_frame('���߲�ưɽ', EQUIP2 . 'work/equip_work_chart.php');

if (isset($_REQUEST['factory'])) {
    $factory = $_REQUEST['factory'];
} else {
    ///// �ꥯ�����Ȥ�̵����Х��å���󤫤鹩���ʬ��������롣(�̾�Ϥ��Υѥ�����)
    $factory = @$_SESSION['factory'];
}

$reload_java = "onLoad=\"setInterval('document.reload_form.submit()', 10000)\"";

//////////// �����ޥ��������������ֹ桦����̾�Υꥹ�Ȥ����(�ƻ����ꤵ��Ƥ���ʪ)
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
                        , substr(midsc, 1, 12)      AS parts_name
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
            $res[$r]['plan_cnt']        = $hed[0]['plan_cnt'];
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

///// ������ξ��ֽ��ϴؿ�
function mac_state_view($mac_no)
{
    global $res, $menu;
    foreach ($res as $rec) {
        if ($rec['mac_no'] == $mac_no) {
            if (is_numeric($rec['mac_state'])) {
                $mac_state_txt = equip_machine_state($rec['mac_no'], $rec['mac_state'], $bg_color, $txt_color);
            } else {
                $mac_state_txt = '̤�ؼ�'; $bg_color = 'white'; $txt_color = 'black';
            }
            echo "<table style='margin:0%;' border='1'>\n";
            echo "    <tr>\n";
            if ($mac_state_txt == '̤�ؼ�') {
                echo "        <td style='background-color:{$bg_color}; color:{$txt_color}; font-size:9.4pt; font-weight:normal;' align='center' width='55'>{$mac_state_txt}</td>\n";
            } else {
                echo "        <td style='background-color:{$bg_color}; color:{$txt_color}; font-size:9.4pt; font-weight:normal;' align='center' width='55'><a href='" . $menu->out_action('���߲�ưɽ') . "?mac_no={$rec['mac_no']}' target='_parent' style='text-decoration:none; color:{$txt_color}; background-color:{$bg_color};'>{$mac_state_txt}</a></td>\n";
            }
            echo "    </tr>\n";
            echo "    <tr>\n";
            if ($mac_state_txt == '̤�ؼ�') {
                echo "        <td style='font-size:9.4pt; font-weight:normal;' align='center' width='55'>{$rec['mac_no']}</td>\n";
            } else {
                echo "        <td style='font-size:9.4pt; font-weight:normal;' align='center' width='55'><a href='" . $menu->out_action('��ž�����') . "?mac_no={$rec['mac_no']}' target='_parent' style='text-decoration:none;'>{$rec['mac_no']}</a></td>\n";
            }
            echo "    </tr>\n";
            echo "    <tr>\n";
            if ($mac_state_txt == '̤�ؼ�') {
                echo "        <td style='font-size:9.4pt; font-weight:normal;' align='center' width='55'>{$rec['mac_name']}</td>\n";
            } else {
                //echo "        <td style='font-size:9.4pt; font-weight:normal;' align='center' width='55'><a href='" . $menu->out_action('�������塼��') . "?mac_no={$rec['mac_no']}' target='_parent' style='text-decoration:none;'>{$rec['mac_name']}</a></td>\n";
                echo "        <td style='font-size:9.4pt; font-weight:normal;' align='center' width='55'>{$rec['mac_name']}</td>\n";
            }
            echo "    </tr>\n";
            echo "</table>\n";
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
<?php if ($_SESSION['s_sysmsg'] != '') echo $menu->out_site_java() ?>
<?= $menu->out_css() ?>
<style type='text/css'>
<!--
th {
    font-size:      11.5pt;
    font-weight:    bold;
    font-family:    monospace;
}
table {
    font-size:      12pt;
    font-weight:    bold;
    /* font-family:    monospace; */
}
.item {
    position: absolute;
    /* top: 100px; */
    left:  5px;
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
var FLAG = 1;
var ID;
function reload_switch() {
    if (FLAG == 1) {
        FLAG = 0;
        clearInterval(ID);
        alert('��ư��������ߤ��ޤ�����');
    } else {
        FLAG = 1;
        ID = setInterval('document.reload_form.submit()', 10000);
        alert('��ư�����򳫻Ϥ��ޤ�����');
    }
}
// window.document.onclick = reload_switch;
function init() {
    ID = setInterval('document.reload_form.submit()', 10000);
}
function win_open(img_src, img_alt) {
    var w = 640;
    var h = 480;
    var left = (screen.availWidth  - w) / 2;
    var top  = (screen.availHeight - h) / 2;
    window.open('photo_view.php?img_src='+img_src+'&img_alt='+img_alt, 'view_win', 'width='+w+',height='+h+',scrollbars=no,status=no,toolbar=no,location=no,menubar=no,top='+top+',left='+left);
}
// -->
</script>
</head>
<body onLoad="init()">
    <center>
        <a href='JavaScript:reload_switch();' style='font-size:12pt; font-weight:bold; text-decoration:none;'>ɽ����������</a>
<?php
switch ($factory) {
case 1:
    require_once ('equip_work_map1List.php');
    break;
/*
case 4:
    require_once ('equip_work_map4List.php');
    break;
*/
case 5:
    require_once ('equip_work_map5List.php');
    break;
case 7:
    require_once ('equip_work_map7cList.php');
    break;
case 8:
    require_once ('equip_work_map7susList.php');
    break;
default:
    echo "        <table border='0' class='msg'>\n";
    echo "            <tr>\n";
    echo "                <td>\n";
    echo "                    <b style='color: blue;'>����������ޤ��󡣸��ߺ�����Ǥ���</b>\n";
    echo "                </td>\n";
    echo "            </tr>\n";
    echo "        </table>\n";
    break;
}
?>
    </center>
</body>
<script language='JavaScript'>
<!--
// setTimeout('location.reload(true)', 10000);     // ������ѣ�����
// -->
</script>
<form name='reload_form' action='equip_work_mapList.php' method='get' target='_self'>
    <input type='hidden' name='factory' value='<?=$factory?>'>
</form>
</html>
<?php ob_end_flush(); // ���ϥХåե���gzip���� END ?>
