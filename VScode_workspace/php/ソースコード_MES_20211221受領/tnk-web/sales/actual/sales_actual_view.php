<?php
//////////////////////////////////////////////////////////////////////////////
// ��� ���� �Ȳ�  new version  salse_actual_view.php                       //
// Copyright (C) 2020-2020 Ryota.Waki tnksys@nitto-kohki.co.jp              //
// Changed history                                                          //
// 2020/12/17 Created   sales_view.php -> salse_actual_view.php             //
//////////////////////////////////////////////////////////////////////////////
// ini_set('error_reporting', E_ALL);           // E_ALL='2047' debug ��
// ini_set('display_errors', '1');              // Error ɽ�� ON debug �� ��꡼���女����
// ini_set('zend.ze1_compatibility_mode', '1'); // zend 1.X ����ѥ� php4�θߴ��⡼��
// ini_set('implicit_flush', 'off');            // echo print �� flush �����ʤ�(�٤��ʤ뤿��) CLI CGI��
// ini_set('max_execution_time', 1200);         // ����¹Ի���=20ʬ CLI CGI��
ob_start('ob_gzhandler');                       // ���ϥХåե���gzip����
session_start();                                // ini_set()�μ��˻��ꤹ�뤳�� Script �Ǿ��

require_once ('../../function.php');            // define.php �� pgsql.php �� require_once ���Ƥ���
require_once ('../../tnk_func.php');            // TNK �˰�¸������ʬ�δؿ��� require_once ���Ƥ���
require_once ('../../MenuHeader.php');          // TNK ������ menu class
require_once ('../../ControllerHTTP_Class.php');// TNK ������ MVC Controller Class
require_once ('sales_actual_func.php');
////////// ���å����Υ��󥹥��󥹤���Ͽ
$session = new Session();

access_log();                               // Script Name �ϼ�ư����

///// TNK ���ѥ�˥塼���饹�Υ��󥹥��󥹤����
$menu = new MenuHeader(0);                  // ǧ�ڥ����å�0=���̰ʾ� �����=TOP_MENU �����ȥ�̤����

////////// ����������
$menu->set_site( 1, 11);                    // site_index=01(����˥塼) site_id=11(����������)
////////// �꥿���󥢥ɥ쥹����
// $menu->set_RetUrl(SYS_MENU);                // �̾�ϻ��ꤹ��ɬ�פϤʤ�
////////// �����ȥ�̾(�������Υ����ȥ�̾�ȥե�����Υ����ȥ�̾)
$menu->set_title('�� �� �� �� �� ��');

////////// �֥饦�����Υ���å����к���
$uniq = $menu->set_useNotCache('target');

////////// ɽ���ѡʷ��������ˤ˻��Ѥ�����
define('OEM', 1);
define('HOYOU', 2);
define('OTHER', 3);
define('TOTAL', 4);
define('VIEW_RECORD_MAX', 5);
define('VIEW_FIELD_MAX', 10);

//////////// �����Υ��å����ǡ�����¸
if (! isset($_REQUEST['lineNo']) ) {
    $_SESSION['s_div']          = $_REQUEST['div'];
    $_SESSION['s_target_ym']    = $_REQUEST['target_ym'];
    $_SESSION['s_lineNo']       = 0;    // �����
} else {
    $_SESSION['s_lineNo'] = $_REQUEST['lineNo'];
}

$div        = $_SESSION['s_div'];
$target_ym  = $_SESSION['s_target_ym'];
$lineNo     = $_SESSION['s_lineNo'];
$d_start    = substr($target_ym,0,4) . substr($target_ym,5,2) . "01";
$d_end      = substr($target_ym,0,4) . substr($target_ym,5,2) . "99";

////////// ������SQL������Ԥ���ɬ�׾���򥻥å�������¸
if( ! $lineNo ) {

    ////////// ɽ���ѡʷ��������˽����
    $view_tbl = array(); // [0]��[1��5]ʬ�ࡦ[0��9]������ȷ��
    for($r=0; $r<VIEW_RECORD_MAX; $r++) { 
        for($f=0; $f<VIEW_FIELD_MAX; $f++) { 
            $view_tbl[$r][$f] = 0;
        }
    }

    ////////// ���ͽ��ǡ�������
    $target = substr($target_ym,0,4) . substr($target_ym,5,2);
    $search_f = "WHERE m.kanryou LIKE '{$target}%'";
    if( $div == "S" ) {
        $search_f .=  " AND plan_no LIKE 'C%' AND SUBSTRING(a.note15, 1, 2) = 'SC'";
    } else if( $div == "D" ) {
        $search_f .=  " AND plan_no LIKE 'C%' AND SUBSTRING(a.note15, 1, 2) != 'SC'";
    } else {
        $search_f .=  " AND plan_no LIKE 'L%'";
    }
    $rows_first = getFirstPlan($res_first, $field_first, $search_f);
    if( $rows_first <= 0 ) {
        $_SESSION['s_sysmsg'] .= sprintf("���ͽ��μ����˼��Ԥ��ޤ�����%s��%s", format_date($d_start), format_date($d_end) );
        header('Location: ' . H_WEB_HOST . $menu->out_RetUrl());    // ľ���θƽи������
        exit();
    }

    // ���ͽ��η������� ����
    for( $r=0; $r<$rows_first; $r++ ) {
        if( trim($res_first[$r][7]) == "" ) {
            $res_first[$r][7] = getLineNo($res_first[$r][1], $res_first[$r][2]);
        }
        if( IsOem($res_first[$r][7]) ) { // OEM
            $rec = OEM;
        } else if( IsHoyou($res_first[$r][7]) ) {  // ����
            $rec = HOYOU;
        } else {    // ����¾
            $rec = OTHER;
        }
        $view_tbl[$rec][0]++;
        $view_tbl[$rec][1] += $res_first[$r][4];
    }

    ////////// ���ͽ��ǡ�������
    $search_p = "WHERE a.kanryou>=$d_start AND a.kanryou<=$d_end AND (a.plan -a.cut_plan) > 0 AND assy_site='01111' AND a.nyuuko!=30 AND p_kubun='F' AND (a.plan -a.cut_plan - kansei) > 0";
    if ($div == 'S') {          // ������ʤ�
        $search_p .= " and a.dept='C' and a.note15 like 'SC%%'";
        $search_p .= " and (a.parts_no not like 'NKB%%') and (a.parts_no not like 'SS%%')";
        $search_p .= " and CASE WHEN a.kanryou<20130501 THEN groupm.support_group_code IS NULL ELSE a.dept='C' END";
    } elseif ($div == 'D') {    // ��ɸ��ʤ�
        $search_p .= " and a.dept='C' and (a.note15 NOT like 'SC%%' OR a.note15 IS NULL)";    // ��������ɸ��ؤ���
        $search_p .= " and (a.parts_no not like 'NKB%%') and (a.parts_no not like 'SS%%')";
        $search_p .= " and (CASE WHEN a.kanryou<20130501 THEN groupm.support_group_code IS NULL ELSE a.dept='C' END)";
    } elseif ($div == "L") {
        $search_p .= " and a.dept='$div'";
        $search_p .= " and (a.parts_no not like 'NKB%%') and (a.parts_no not like 'SS%%')";
    }
    $rows_plan = getSalsePlan($res_plan, $search_p);

    ////////// ������٥ǡ�������
    $search_m = "where �׾���>=$d_start and �׾���<=$d_end";
    if ($div == 'S') {          // ������ʤ�
        $search_m .= " and ������='C' and note15 like 'SC%%'";
        $search_m .= " and CASE WHEN �׾���>=20111101 and �׾���<20130501 THEN groupm.support_group_code IS NULL ELSE ������='C' END";
    } elseif ($div == 'D') {    // ��ɸ��ʤ�
        $search_m .= " and ������='C' and (note15 NOT like 'SC%%' OR note15 IS NULL)";    // ��������ɸ��ؤ���
        $search_m .= " and (CASE WHEN �׾���>=20111101 and �׾���<20130501 THEN groupm.support_group_code IS NULL ELSE ������='C' END)";
    } elseif ($div == "L") {
        $search_m .= " and ������='$div'";
    }
    $search_m .= " and (assyno not like 'NKB%%') and (assyno not like 'SS%%') and datatype='1'";
    $rows_details = getSalesDetails($res_details, $div, $search_m);

    ////////// ̤�����ǡ�������
    $rows_miken = getMikenSlectData($res_miken, $div, (substr($target_ym,0,4) . substr($target_ym,5,2)));

    ////////// ���ͽ������٤����
    DiffFirstDetails($res_first, $rows_first, $res_details, $rows_details);

    ////////// ���ͽ���̤���������
    DiffFirstMiken($res_first, $rows_first, $res_miken, $rows_miken);

    ////////// ���ͽ��ȸ��ߤ�ͽ������
    DiffFirstPlan($res_first, $rows_first, $res_plan, $rows_plan);

    ////////// ɽ���ѡʷ��������˽���
    $total_ken = $total_kaz = $total_kin = 0;
    for( $r=0; $r<$rows_first; $r++ ) {
        if( $res_first[$r][9] ) {
            $total_ken++;                      // ��׷��
            $total_kaz += $res_first[$r][9];    // ��״�����
            $total_kin += $res_first[$r][10];   // ��״������
        }

        if( IsOem($res_first[$r][7]) ) { // OEM
            $rec = OEM;
        } else if( IsHoyou($res_first[$r][7]) ) {  // ����
            $rec = HOYOU;
        } else {    // ����¾
            $rec = OTHER;
        }

        if( $res_first[$r][8] == "̤����" || $res_first[$r][8] == "��λ" ) {
            $view_tbl[$rec][2]++;
            $view_tbl[$rec][3] += $res_first[$r][9];
            if( $res_first[$r][4] > 0 && $res_first[$r][4] < $res_first[$r][9] ) {
//$_SESSION['s_sysmsg'] .= "�ѹ�����{$view_tbl[$rec][3]} / {$view_tbl[$rec][7]}";
//                $view_tbl[$rec][7] += ($res_first[$r][9] - $res_first[$r][4]);  // ͽ�����괰������¿�����ɲä�­����
//                $view_tbl[$rec][3] -= ($res_first[$r][9] - $res_first[$r][4]);
//$_SESSION['s_sysmsg'] .= "�ѹ��塧{$view_tbl[$rec][3]} / {$view_tbl[$rec][7]}";
            }
        } else if( $res_first[$r][8] == "ͽ�ꤢ��") {
            $view_tbl[$rec][4]++;
            $view_tbl[$rec][5] += $res_first[$r][4];
            if( $res_first[$r][9] ) {   // ʬǼ���Ƥ���ʬ�ϡ��û�
                $view_tbl[$rec][2]++;
                $view_tbl[$rec][3] += $res_first[$r][9];
            }
        } else if( $res_first[$r][8] == "�ɲ�" ) {
            $view_tbl[$rec][6]++;
            $view_tbl[$rec][7] += $res_first[$r][9];
        } else {
            $view_tbl[$rec][8]++;
            $view_tbl[$rec][9] += $res_first[$r][4];
        }
    }

    // ɽ���ѡʷ��������˹�ץ��å�
    for($r=0; $r<VIEW_RECORD_MAX-1; $r++) { 
        for($f=0; $f<VIEW_FIELD_MAX; $f++) { 
            $view_tbl[TOTAL][$f] += $view_tbl[$r][$f];
        }
    }

    // ����������
    $zenki = getPreviousSeasonSales($target_ym, $div);
    if( $zenki > 0 ) {
        // ������ �������������ǯ��Ʊ�����ˡ� 100
        $zenkihi_ken = number_format((($total_ken / $zenki[0]['t_ken']) * 100), 2) . " ��";
        $zenkihi_kaz = number_format((($total_kaz / $zenki[0]['t_kazu']) * 100), 2) . " ��";
        $zenkihi_kin = number_format((($total_kin / $zenki[0]['t_kingaku']) * 100), 2) . " ��";
    } else {
        $zenkihi = "��ǯ��Ʊ�����μ����˼���!!";
    }
//$_SESSION['s_sysmsg'] = "TEST �����" . $zenki[0]['t_ken'] . ":" . $zenki[0]['t_kazu'];

    // �ƾ���򥻥å�������¸
    $_SESSION['s_view_tbl[]']       = $view_tbl;        // ɽ���ѡʷ���������
    $_SESSION['s_res_first[]']      = $res_first;       // ���٥ꥹ��
    $_SESSION['s_rows_first']       = $rows_first;      // ���٥ꥹ�Ȥη��
    $_SESSION['s_field_first[]']    = $field_first;     // ���٥ꥹ�Ȥι���
    $_SESSION['s_total_ken']        = $total_ken;       // ��׷��
    $_SESSION['s_total_kaz']        = $total_kaz;       // ��״�����
    $_SESSION['s_total_kin']        = $total_kin;       // ��״������
    $_SESSION['s_zenkihi_ken']      = $zenkihi_ken;     // ������ʷ����
    $_SESSION['s_zenkihi_kaz']      = $zenkihi_kaz;     // ������ʿ��̡�
    $_SESSION['s_zenkihi_kin']      = $zenkihi_kin;     // ������ʶ�ۡ�
} else {    // ����
    // �ƾ���򥻥å���������
    $view_tbl       = $_SESSION['s_view_tbl[]'];        // ɽ���ѡʷ���������
    $res_first      = $_SESSION['s_res_first[]'];       // ���٥ꥹ��
    $rows_first     = $_SESSION['s_rows_first'];        // ���٥ꥹ�Ȥη��
    $field_first    = $_SESSION['s_field_first[]'];     // ���٥ꥹ�Ȥι���
    $total_ken      = $_SESSION['s_total_ken'];         // ��׷��
    $total_kaz      = $_SESSION['s_total_kaz'];         // ��״�����
    $total_kin      = $_SESSION['s_total_kin'];         // ��״������
    $zenkihi_ken    = $_SESSION['s_zenkihi_ken'];       // ������ʷ����
    $zenkihi_kaz    = $_SESSION['s_zenkihi_kaz'];       // ������ʿ��̡�
    $zenkihi_kin    = $_SESSION['s_zenkihi_kin'];       // ������ʶ�ۡ�
}

////////// ���٥ꥹ�Ȥν���
$res = array();
$rec = 0;
$num = count($field_first) - 1;  // �ե�����ɿ����� (�Ǹ�����ͤϽ���)

for( $r=0; $r<$rows_first; $r++ ) {
    if( $lineNo ) {
        if( $lineNo == OEM ) {
            if( ! IsOem($res_first[$r][7]) ) continue;
        } else if( $lineNo == HOYOU ) {
            if( ! IsHoyou($res_first[$r][7]) ) continue;
        } else if( $lineNo == OTHER ) {
            if( IsOem($res_first[$r][7]) || IsHoyou($res_first[$r][7]) ) continue;
        }
    }

    for( $f=0; $f<$num; $f++ ) {
        $res[$rec][$f] = $res_first[$r][$f];
    }
    $rec++;
}
$rows= $rec;    // ���٥ꥹ��ɽ��������å�

////////// ɽ�������
///// ���ʥ��롼��(������)̾������
if ($div == "D") $div_name = "���ץ�ɸ��";
if ($div == "S") $div_name = "���ץ�����";
if ($div == "L") $div_name = "��˥�";

$f_d_start  = format_date($d_start);        // ���դ� / �ǥե����ޥå�
$f_d_end    = format_date($d_end);          // ���դ� / �ǥե����ޥå�
$menu->set_caption("<u>����=<font color='red'>{$div_name}</font>��{$f_d_start}��{$f_d_end}<u>");

$total_ken = number_format($total_ken);     // ���头�ȤΥ���ޤ��ղ�
$total_kin = number_format($total_kin);     // ���头�ȤΥ���ޤ��ղ�
$total_kaz = number_format($total_kaz);     // ���头�ȤΥ���ޤ��ղ�
$menu->set_caption2("<u>��׷��={$total_ken}����׶��={$total_kin}����׿���={$total_kaz}<u>");

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
<?php echo $menu->out_site_java()?>
<?php echo $menu->out_css()?>
<?php echo $menu->out_jsBaseClass() ?>

<script type='text/javascript' language='JavaScript'>
<!--
/* ������ϥե�����Υ�����Ȥ˥ե������������� */
function set_focus(){
    // document.body.focus();                          // F2/F12��������뤿����б�
    // document.form_name.element_name.select();
}
// -->
</script>

<!-- �������륷���ȤΥե��������򥳥��� HTML���� �����Ȥ�����Ҥ˽���ʤ��������
<link rel='stylesheet' href='<?php echo MENU_FORM . '?' . $uniq ?>' type='text/css' media='screen'>
-->

<style type="text/css">
<!--
.pt8 {
    font-size:      8pt;
    font-family:    monospace;
}
.pt9 {
    font-size:      9pt;
    font-family:    monospace;
}
.pt10 {
    font-size:l     10pt;
    font-family:    monospace;
}
.pt10b {
    font-size:      10pt;
    font-weight:    bold;
    font-family:    monospace;
}
.pt11b {
    font-size:      11pt;
    font-weight:    bold;
    font-family:    monospace;
}
.pt12b {
    font-size:      12pt;
    font-weight:    bold;
    font-family:    monospace;
}
th {
    background-color:   yellow;
    color:              blue;
    font-size:          10pt;
    font-weight:        bold;
    font-family:        monospace;
}
.winbox {
    border-style: solid;
    border-width: 1px;
    border-top-color:       #FFFFFF;
    border-left-color:      #FFFFFF;
    border-right-color:     #bdaa90;
    border-bottom-color:    #bdaa90;
    /* background-color:#d6d3ce; */
}
.winbox_field {
    border-style: solid;
    border-width: 1px;
    border-top-color:       #bdaa90;
    border-left-color:      #bdaa90;
    border-right-color:     #FFFFFF;
    border-bottom-color:    #FFFFFF;
    /* background-color:#d6d3ce; */
}
.winboxy {
    border-style: solid;
    border-width: 1px;
    border-top-color:       #FFFFFF;
    border-left-color:      #FFFFFF;
    border-right-color:     #bdaa90;
    border-bottom-color:    #bdaa90;
    background-color:   yellow;
    color:              blue;
    font-size:          12pt;
    font-weight:        bold;
    font-family:        monospace;
}
a:hover {
    background-color:   blue;
    color:              white;
}
a {
    color:   blue;
}
body {
    background-image:url(<?php echo IMG ?>t_nitto_logo4.png);
    background-repeat:no-repeat;
    background-attachment:fixed;
    background-position:right bottom;
}
-->
</style>
</head>
<body onLoad='set_focus()'>
    <center>
<?php echo $menu->out_title_border()?>
        
        <table width='100%' border='0' cellspacing='0' cellpadding='0'>
                <tr>
                    <td nowrap align='center' class='caption_font'>
                        <?php echo $menu->out_caption(), "\n" ?>
                        <BR>
                        <?php echo $menu->out_caption2(), "\n" ?>
                    </td>
                </tr>
        </table>
        <br>

            <!----------------- ������ ��ʸ��ɽ������ ------------------->
            <table bgcolor='#d6d3ce' border='1' cellspacing='0' cellpadding='5'>
                <caption class='caption_font' align='right'><?php echo "��������۷����{$zenkihi_ken} / ���̡�{$zenkihi_kaz}" ?></caption>
                <tr><td> <!----------- ���ߡ�(�ǥ�������) ------------>
            <table class='winbox_field' width='100%' bgcolor='#d6d3ce' border='1' cellspacing='0' cellpadding='3'>
                <tr class='winbox' style='background-color:yellow; color:blue;' align='center'>
                    <div class='caption_font'>
                        <td rowspan="2" style='color:red;'><?php echo $div_name ?></td>
                        <td colspan="2">���ͽ��</td>

                        <td colspan="2">��λ</td>

                        <td colspan="2">�Ĥ�</td>

                        <td colspan="2">�ɲô�λ</td>

                        <td colspan="2">ͽ��ʤ�</td>

                    </div>
                </tr>
                <tr class='winbox' style='background-color:yellow; color:blue;' align='center'>
                    <div class='caption_font'>
                        <td>���</td>
                        <td>���</td>
                        <td>���</td>
                        <td>���</td>
                        <td>���</td>
                        <td>���</td>
                        <td>���</td>
                        <td>���</td>
                        <td>���</td>
                        <td>���</td>
                    </div>
                </tr>
                <?php
                if( $div == "L" ) {
                ?>
                <tr align='right'>
                    <form name='oem_form' action='<?php echo $menu->out_self() . "?lineNo=1" ?>' method='post'>
                    <td style='background-color:yellow;'><a href="javascript:oem_form.submit()">�ϣţ�</a></td>
                    </form>
                    <?php
                    for( $r=0; $r<VIEW_FIELD_MAX; $r=$r+2 ) {
                    ?>
                    <td><?php echo number_format($view_tbl[OEM][$r],0) ?> ��</td>
                    <td><?php echo number_format($view_tbl[OEM][$r+1],0) ?> ��</td>
                    <?php
                    }
                    ?>
                </tr>
                <tr align='right'>
                    <form name='hoyou_form' action='<?php echo $menu->out_self() . "?lineNo=2" ?>' method='post'>
                    <td style='background-color:yellow;'><a href="javascript:hoyou_form.submit()">�ۥ襦</a></td>
                    </form>
                    <?php
                    for( $r=0; $r<VIEW_FIELD_MAX; $r=$r+2 ) {
                    ?>
                    <td><?php echo number_format($view_tbl[HOYOU][$r],0) ?> ��</td>
                    <td><?php echo number_format($view_tbl[HOYOU][$r+1],0) ?> ��</td>
                    <?php
                    }
                    ?>
                <?php
                if( $view_tbl[OTHER][0] != 0 || $view_tbl[OTHER][2] != 0 || $view_tbl[OTHER][4] != 0 || $view_tbl[OTHER][6] != 0 || $view_tbl[OTHER][8] != 0) {
                ?>
                </tr>
                <tr align='right'>
                    <form name='other_form' action='<?php echo $menu->out_self() . "?lineNo=3" ?>' method='post'>
                    <td style='background-color:yellow;'><a href="javascript:other_form.submit()">����</a></td>
                    </form>
                    <?php
                    for( $r=0; $r<VIEW_FIELD_MAX; $r=$r+2 ) {
                    ?>
                    <td><?php echo number_format($view_tbl[OTHER][$r],0) ?> ��</td>
                    <td><?php echo number_format($view_tbl[OTHER][$r+1],0) ?> ��</td>
                    <?php
                    }
                    ?>
                </tr>
                <?php
                }
                ?>
                <?
                }
                ?>
                <tr align='right'>
                    <form name='all_form' action='<?php echo $menu->out_self() . "?lineNo=-1" ?>' method='post'>
                    <td style='background-color:yellow;'><a href="javascript:all_form.submit()">���٤�</a></td>
                    </form>
                    <?php
                    for( $r=0; $r<VIEW_FIELD_MAX; $r=$r+2 ) {
                    ?>
                    <td><?php echo number_format($view_tbl[TOTAL][$r],0) ?> ��</td>
                    <td><?php echo number_format($view_tbl[TOTAL][$r+1],0) ?> ��</td>
                    <?php
                    }
                    ?>
                </tr>
            </table>
                </td></tr>
            </table> <!----------------- ���ߡ�End ------------------>
        <BR>
        <!--------------- ����������ʸ��ɽ��ɽ������ -------------------->
        <table bgcolor='#d6d3ce' align='center' border='1' cellspacing='0' cellpadding='3'>
            <caption class='caption_font' align='left' style='color:Red;'>����λͽ�������ֻ��Τ�Τϡ��ɲ�ʬ�Ρڴ�������</caption>
            <tr><td> <!----------- ���ߡ�(�ǥ�������) ------------>
        <table class='winbox_field' width='100%' bgcolor='#d6d3ce' align='center' border='1' cellspacing='0' cellpadding='3'>
            <thead>
                <!-- �ơ��֥� �إå�����ɽ�� -->
                <tr>
                    <th class='winbox' nowrap width='10'>No.</th>        <!-- �ԥʥ�С���ɽ�� -->
                <?php
                for ($i=0; $i<$num; $i++) {             // �ե�����ɿ�ʬ���֤�
                ?>
                    <th class='winbox' nowrap><?php echo $field_first[$i] ?></th>
                <?php
                }
                ?>
                </tr>
            </thead>
            <tfoot>
                <!-- ���ߤϥեå����ϲ���ʤ� -->
            </tfoot>
            <tbody>
                <?php
                for ($r=0; $r<$rows; $r++) {
                    echo "<tr onMouseOver=\"style.background='#ceffce'\" onMouseOut=\"style.background='#d6d3ce'\">\n";
                    echo "    <td class='winbox' nowrap align='right'><div class='pt10b'>" . ($r + 1) . "</div></td>    <!-- �ԥʥ�С���ɽ�� -->\n";
                    for ($i=0; $i<$num; $i++) {         // �쥳���ɿ�ʬ���֤�
                        // <!--  bgcolor='#ffffc6' �������� --> 
                            switch ($i) {
                            case 0:     // �׾���
                                if( $res[$r][8] == "�ɲ�" ) {
                                    echo "<td class='winbox' nowrap align='center'><div class='pt9' style='color:Red;'>" . format_date($res[$r][$i]) . "</div></td>\n";
                                } else {
                                    echo "<td class='winbox' nowrap align='center'><div class='pt9'>" . format_date($res[$r][$i]) . "</div></td>\n";
                                }
                                break;
                            case 1:
                                if( $res[$r][8] == "�ɲ�" ) {
                                    echo "<td class='winbox' nowrap align='center' style='background-color:LightPink;'><div class='pt9'>{$res[$r][$i]}</div></td>\n";
                                } else if( ($res[$r][8] == "��λ" || $res[$r][8] == "̤����" ) && $res[$r][4] != $res[$r][9] ) {
                                    echo "<td class='winbox' nowrap align='center' style='background-color:SkyBlue;'><div class='pt9'>{$res[$r][$i]}</div></td>\n";
                                } else {
                                    echo "<td class='winbox' nowrap align='center'><div class='pt9'>", $res[$r][$i], "</div></td>\n";
                                }
                                break;
                            case 2:     // �����ֹ�
                                echo "<td class='winbox' nowrap align='center'><div class='pt9'>", $res[$r][$i], "</div></td>\n";
                                break;
                            case 3:     // ����̾
                                echo "<td class='winbox' nowrap width='270' align='left'><div class='pt9'>" . $res[$r][$i] . "</div></td>\n";
                                break;
                            case 4:     // ����
                                echo "<td class='winbox' nowrap width='45' align='right'><div class='pt9'>" . number_format($res[$r][$i], 0) . "</div></td>\n";
                                break;
                            case 5:     // ����ñ��
                                if ($res[$r][$i] == 0) {
                                    echo "<td class='winbox' nowrap width='60' align='right'><div class='pt9'>-</div></td>\n";
                                } else {
                                    echo "<td class='winbox' nowrap width='60' align='right'><div class='pt9'>" . number_format($res[$r][$i], 2) . "</div></td>\n";
                                }
                                break;
                            case 6:     // ���
                                echo "<td class='winbox' nowrap width='70' align='right'><div class='pt9'>" . number_format($res[$r][$i], 0) . "</div></td>\n";
                                break;
                            case 7:     // �饤��No.
                                echo "<td class='winbox' nowrap width='70' align='right'><div class='pt9'>" . $res[$r][$i] . "</div></td>\n";
                                break;
                            case 8:    // ��ʬ
                                if( $res[$r][8] == "" ) {
                                    $res[$r][8] = "---";
                                }
                                echo "<td class='winbox' nowrap align='center'><div class='pt9'>{$res[$r][$i]}</div></td>\n";
                                break;
                            case 9:    // ���͡ʿ��̤��ѹ������ä�����
                                if( $res[$r][9] == "" ) {
                                    $res[$r][9] = "��";
                                    $res[$r][10] = "��";
                                }
                                echo "<td class='winbox' nowrap width='70' align='right'><div class='pt9'>" . number_format($res[$r][$i], 0) . "</div></td>\n";
                                break;
                            case 10:     // ���
                                echo "<td class='winbox' nowrap width='70' align='right'><div class='pt9'>" . number_format($res[$r][$i], 0) . "</div></td>\n";
                                break;
                            default:    // ����¾
                                echo "<td class='winbox' nowrap align='center'><div class='pt9'>", $res[$r][$i], "</div></td>\n";
                            }
                        // <!-- ����ץ�<td rowspan='2' colspan='3' width='200' align='center' class='pt10b' bgcolor='#ffffc6'>  </td> -->
                    }
                    echo "</tr>\n";
                }
                ?>
            </tbody>
        </table>
            </td></tr>
        </table> <!----------------- ���ߡ�End ------------------>

    </center>
</body>
<?php echo $menu->out_alert_java()?>
</html>
<?php
// ob_end_flush();                 // ���ϥХåե���gzip���� END
?>
