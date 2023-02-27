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
    font-size:      0.95em;
    font-weight:    bold;
    font-family:    monospace;
}
table {
    font-size:      1.0em;
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
a {
    color: blue;
    text-decoration:none;
}
a:hover {
    background-color: blue;
    color: white;
}
.list tr.mouseOver
{
    background-color:   #ceffce;
}
-->
</style>
<script language='JavaScript'>
function init() {
    setInterval('document.reload_form.submit()', 120000);   // 120��
}
</script>
</head>
<body onLoad='init()'>
    <center>
        <?php if ($view != 'OK') { ?>
        <table border='0' class='msg'>
            <tr>
                <td>
                    <b style='color: teal;'>��ư�����оݤε���������ޤ���</b>
                </td>
            </tr>
        </table>
        <?php } else { ?>
        <!-------------- �ܺ٥ǡ���ɽ���Τ����ɽ����� -------------->
        <table width='100%' bgcolor='#d6d3ce'  border='1' cellspacing='0' cellpadding='1'>
           <tr><td> <!-- ���ߡ�(�ǥ�������) -->
        <table class='winbox_field list table_font' width='100%' align='center' border='1' cellspacing='0' cellpadding='1'>
            <!--
            -->
        <?php
            $i = 0;
            foreach ($res as $rec) {
                $i++;
                echo "<tr onMouseOver='this.className=\"mouseOver\";' onMouseOut='this.className=\"\";'>\n";
                echo "<td class='winbox' nowrap align='right' width='3%'>{$i}</td>\n";
                if ($rec['date'] != '̤�ؼ�') {
                    echo "<td class='winbox' nowrap align='left'   width='9%' title='\n{$rec['mac_no']}\n\n����դ�ɽ�����ޤ���\n'><a href='" . $menu->out_action('��ž�����') . "?mac_no={$rec['mac_no']}' target='_parent' style='text-decoration:none;'>{$rec['mac_name']}</a></td>\n";
                    echo "<td class='winbox' nowrap align='center' width='9%'>{$rec['date']}</td>\n";
                } else {
                    echo "<td class='winbox' nowrap align='left'   width='9%'><font color='gray'>{$rec['mac_name']}</font></td>\n";
                    echo "<td class='winbox' nowrap align='center' width='9%'><font color='gray'>{$rec['date']}</font></td>\n";
                }
                echo "<td class='winbox' nowrap align='center' width='8%'>{$rec['time']}</td>\n";
                if (is_numeric($rec['mac_state'])) {
                    $mac_state_txt = equip_machine_state($rec['mac_no'], $rec['mac_state'], $bg_color, $txt_color);
                    echo "<td class='winbox' nowrap align='center' width='9%' bgcolor='$bg_color' title='\n{$rec['mac_no']}\n\n���֤����٤�ɽ�����ޤ���\n'><a href='" . $menu->out_action('���߲�ưɽ') . "?mac_no={$rec['mac_no']}' target='_parent' style='color:$txt_color;'>{$mac_state_txt}</a></font></td>\n";
                } else {
                    echo "<td class='winbox' nowrap align='center' width='9%'>{$rec['mac_state']}</td>\n";
                }
                echo "<td class='winbox' nowrap align='right' width='8%'>{$rec['work_cnt']}</td>\n";
                echo "<td class='winbox' nowrap align='right' width='8%'>{$rec['plan_cnt']}</td>\n";
                if ($rec['date'] != '̤�ؼ�') {
                    //echo "<td class='winbox' nowrap align='center' width='7%' title='\n{$rec['mac_no']}\n\n������ɽ�����ޤ���\n'><a href='" . $menu->out_action('�������塼��') . "?mac_no={$rec['mac_no']}' target='_parent' style='text-decoration:none;'>{$rec['siji_no']}</a></td>\n";
                    echo "<td class='winbox' nowrap align='center' width='7%'>{$rec['siji_no']}</td>\n";
                } else {
                    echo "<td class='winbox' nowrap align='center'    width='7%'>{$rec['siji_no']}</td>\n";
                }
                echo "<td class='winbox' nowrap align='center' width='11%'>{$rec['parts_no']}</td>\n";
                echo "<td class='winbox' nowrap align='left'   width='13%'>{$rec['parts_name']}</td>\n";
                echo "<td class='winbox' nowrap align='center' width='15%'>{$rec['str_datetime']}</td>\n";
                echo "</tr>\n";
            }
        ?>
        </table> <!----- ���ߡ� End ----->
            </td></tr>
        </table>
        <?php } ?>
    </center>
</body>
<script language='JavaScript'>
<!--
// setTimeout('location.reload(true)', 10000);      // ������ѣ�����
// -->
</script>
<form name='reload_form' action='equip_work_allList.php' method='get' target='_self'>
    <input type='hidden' name='factory' value='<?php echo $factory?>'>
</form>
</html>
<?php ob_end_flush(); // ���ϥХåե���gzip���� END ?>
