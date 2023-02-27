<?php
//////////////////////////////////////////////////////////////////////////////
// ��� ���� �����ץ����� �Ȳ�                                            //
// Copyright (C) 2005-2005 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp      //
// Changed history                                                          //
// 2005/01/18 Created   sales_custom_view.php                               //
// 2005/01/27 $div_name��$view_name���ѹ� ɽ�����̾��ɽ��                  //
//            ���ɽ����θƽФȥ���դ���θƽФ��Ѥ��뤿��              //
//            custom_where?��custom_condition?������                        //
//            set_retGET('sum_exec', '���ɽ�Ȳ�')��('sum_exec', 'on')���ѹ�//
//            ������������ɲõڤ��������������ʬ�����Ƹ��䤹������//
// 2005/02/01 ��������mate.sum_price��0��ʪ������ײ��ֹ�=C1261631�����б�//
//             mate.sum_price <= 0    ����Ū�ˤ����ʤϻٵ��ʤ�������Ω��Τ�//
//                     ��                                                   //
//            (mate.sum_price + Uround(mate.assy_time * assy_rate, 2)) <= 0 //
// 2005/02/08 $search = $_SESSION['custom_condition']��$search = '';���ѹ�  //
// 2005/05/27 PAGE > 25 �ˤ�� style='overflow:hidden;' ��������ɲ�        //
// 2005/06/05 �ǽ�����31�Υ�ƥ�뤫�� last_day()�Ǽ������ѹ�               //
//////////////////////////////////////////////////////////////////////////////
ini_set('error_reporting', E_ALL);          // E_ALL='2047' debug ��
// ini_set('display_errors', '1');             // Error ɽ�� ON debug �� ��꡼���女����
// ini_set('zend.ze1_compatibility_mode', '1');    // zend 1.X ����ѥ� php4�θߴ��⡼��
// ini_set('implicit_flush', 'off');           // echo print �� flush �����ʤ�(�٤��ʤ뤿��) CLI CGI��
// ini_set('max_execution_time', 1200);        // ����¹Ի���=20ʬ CLI CGI��
ob_start('ob_gzhandler');                   // ���ϥХåե���gzip����
session_start();                            // ini_set()�μ��˻��ꤹ�뤳�� Script �Ǿ��

require_once ('../function.php');           // define.php �� pgsql.php �� require_once ���Ƥ���
require_once ('../tnk_func.php');           // TNK �˰�¸������ʬ�δؿ��� require_once ���Ƥ���
require_once ('../MenuHeader.php');         // TNK ������ menu class
access_log();                               // Script Name �ϼ�ư����

///// TNK ���ѥ�˥塼���饹�Υ��󥹥��󥹤����
$menu = new MenuHeader(0);                  // ǧ�ڥ����å�0=���̰ʾ� �����=TOP_MENU �����ȥ�̤����

////////////// ����������
$menu->set_site( 1, 13);                    // site_index=01(����˥塼) site_id=11(�����ץ����)
////////////// �꥿���󥢥ɥ쥹����
// $menu->set_RetUrl(SYS_MENU);                // �̾�ϻ��ꤹ��ɬ�פϤʤ�
//////////// �����ȥ�̾(�������Υ����ȥ�̾�ȥե�����Υ����ȥ�̾)
$menu->set_title('������� �����ץ����� �Ȳ�');
//////////// �ƽ����action̾�ȥ��ɥ쥹����
$menu->set_action('�������Ȳ�',   INDUST . 'material/materialCost_view.php');
$menu->set_retGET('sum_exec', 'on');
$menu->set_retGET('page_keep', 'on');

//////////// JavaScript Stylesheet File ��ɬ���ɤ߹��ޤ���
$uniq = uniqid('target');

//////////// �����Υ��å����ǡ�����¸   ���ǡ����Ǥ�ڤ����뤿��
if (! (isset($_POST['forward']) || isset($_POST['backward']) || isset($_REQUEST['page_keep'])) ) {
    if (isset($_REQUEST['ym_p'])) {
        $last_day = last_day(substr($_REQUEST['ym_p'], 0, 4), substr($_REQUEST['ym_p'], 4, 2));
        $_SESSION['custom_d_start'] = ($_REQUEST['ym_p'] . '01');
        $_SESSION['custom_d_end']   = ($_REQUEST['ym_p'] . $last_day);
    }
    $uri_passwd = $_SESSION['s_uri_passwd'];
    $div        = $_SESSION['custom_div'];
    $d_start    = $_SESSION['custom_d_start'];
    $d_end      = $_SESSION['custom_d_end'];
    $kubun      = $_SESSION['custom_kubun'];
    $uri_ritu   = 52;       // ��ƥ����ѹ�
    $assy_no    = $_SESSION['custom_assy_no'];
    
    ////////////// �ѥ���ɥ����å�
    if ($uri_passwd != date('Ymd')) {
        $_SESSION['s_sysmsg'] = "<font color='yellow'>�ѥ���ɤ��㤤�ޤ���</font>";
        header('Location: ' . H_WEB_HOST . $menu->out_RetUrl());    // ľ���θƽи������
        exit();
    }
    ///////////// ��׶�ۡ�����������
    $query = "select
                    count(����)                 as t_ken
                    ,
                    sum(����)                   as t_kazu
                    ,
                    sum(Uround(����*ñ��,0))    as t_kingaku
                    ,
                    sum((mate.sum_price + Uround(mate.assy_time * mate.assy_rate, 2)) * ����)
                                                as �������
              from
                    hiuuri
              left outer join
                    assembly_schedule as assem
                on �ײ��ֹ�=plan_no
              left outer join
                    aden_master as aden
                on �ײ��ֹ�=aden.plan_no
              left outer join
                    material_cost_header as mate
                on �ײ��ֹ�=mate.plan_no
    ";
    //////////// SQL where ��� ���å���󤫤����
    if (isset($_REQUEST['custom_view1'])) {
        $search = $_SESSION['custom_condition1'];       // ��
        $view_name = '������ʤΣ����� ��';
    } elseif (isset($_REQUEST['custom_view2'])) {
        $search = $_SESSION['custom_condition2'];       // ��
        $view_name = '�������Σ������� ��';
    } elseif (isset($_REQUEST['custom_view3'])) {
        $search = $_SESSION['custom_condition3'];       // ��
        $view_name = '�������Σ������� ��';
    } elseif (isset($_REQUEST['custom_view4'])) {
        $search = $_SESSION['custom_condition4'];       // ��
        $view_name = '����¾ ��';
    } elseif (isset($_REQUEST['custom_view'])) {
        // $search = $_SESSION['custom_condition'];
        $search = '';                                   // ��������
        $view_name = '�����ץ� ����';
    } else {
        // $search = $_SESSION['custom_condition'];
        $search = '';                                   // ��������
        $view_name = '�����ץ� ����';
    }
    $_SESSION['custom_view_name'] = $view_name;
    $where_assy_no = $_SESSION['custom_where_assy_no'];
    $where= "where
                    �׾���>={$d_start} and �׾���<={$d_end} and ������='C' and note15 like 'SC%' {$where_assy_no}
    ";
    $search = ($where . $search);               // ����դȶ��Ѥ��뤿���ɲ�
    $query = sprintf("$query %s", $search);     // SQL query ʸ�δ���
    $_SESSION['sales_search'] = $search;        // SQL��where�����¸
    $res_sum = array();
    if (getResult($query, $res_sum) <= 0) {
        $_SESSION['s_sysmsg'] = '��׶�ۤμ����˼��Ԥ��ޤ�����';
        header('Location: ' . H_WEB_HOST . $menu->out_RetUrl() . '?sum_exec=on&page_keep=on');    // ľ���θƽи������
        exit();
    } else {
        $t_ken     = $res_sum[0]['t_ken'];
        $t_kazu    = $res_sum[0]['t_kazu'];
        $t_kingaku = $res_sum[0]['t_kingaku'];  // �����
        $t_zai     = $res_sum[0]['�������'];
        $_SESSION['u_t_ken']  = $t_ken;
        $_SESSION['u_t_kazu'] = $t_kazu;
        $_SESSION['u_t_kin']  = $t_kingaku;
        $_SESSION['u_t_zai']  = $t_zai;
    }
} else {                                                // �ڡ������ؤʤ�
    $t_ken     = $_SESSION['u_t_ken'];
    $t_kazu    = $_SESSION['u_t_kazu'];
    $t_kingaku = $_SESSION['u_t_kin'];
    $t_zai     = $_SESSION['u_t_zai'];
}

$uri_passwd = $_SESSION['s_uri_passwd'];
$div        = $_SESSION['custom_div'];
$d_start    = $_SESSION['custom_d_start'];
$d_end      = $_SESSION['custom_d_end'];
$kubun      = $_SESSION['custom_kubun'];
$uri_ritu   = 52;   // ��ƥ����ѹ�
$assy_no    = $_SESSION['custom_assy_no'];
$search     = $_SESSION['sales_search'];

///// ���̾������
$view_name = $_SESSION['custom_view_name'];

//////////// ɽ�������
$ft_kingaku = number_format($t_kingaku);                    // ���头�ȤΥ���ޤ��ղ�
$ft_zai     = number_format($t_zai);                        // ���头�ȤΥ���ޤ��ղ�
$ft_ken     = number_format($t_ken);
$ft_kazu    = number_format($t_kazu);
$f_d_start  = format_date($d_start);                        // ���դ� / �ǥե����ޥå�
$f_d_end    = format_date($d_end);
// $menu->set_caption("<u><font color='red'>{$view_name}</font>��{$f_d_start}��{$f_d_end}����׷��={$ft_ken}����׶��={$ft_kingaku}����׿���={$ft_kazu}<u>");
$menu->set_caption("<u><font color='red'>��{$view_name}</font>��{$f_d_start}��{$f_d_end}����׷��={$ft_ken}����׿���={$ft_kazu}��<br>�����<font style='color:brown;'>����={$ft_kingaku}</font>�����<font style='color:blue;'>�������={$ft_zai}��</font><u>");

//////////// ���ǤιԿ�
if (isset($_SESSION['s_sales_page'])) {
    define('PAGE', $_SESSION['s_sales_page']);
} else {
    define('PAGE', 25);
}

//////////// ��ץ쥳���ɿ�����     (�оݥơ��֥�κ������ڡ�������˻���)
$maxrows = $t_ken;

//////////// �ڡ������ե��å�����
if ( isset($_POST['forward']) ) {                       // ���Ǥ������줿
    $_SESSION['sales_offset'] += PAGE;
    if ($_SESSION['sales_offset'] >= $maxrows) {
        $_SESSION['sales_offset'] -= PAGE;
        if ($_SESSION['s_sysmsg'] == '') {
            $_SESSION['s_sysmsg'] .= "<font color='yellow'>���ǤϤ���ޤ���</font>";
        } else {
            $_SESSION['s_sysmsg'] .= "<br><font color='yellow'>���ǤϤ���ޤ���</font>";
        }
    }
} elseif ( isset($_POST['backward']) ) {                // ���Ǥ������줿
    $_SESSION['sales_offset'] -= PAGE;
    if ($_SESSION['sales_offset'] < 0) {
        $_SESSION['sales_offset'] = 0;
        if ($_SESSION['s_sysmsg'] == '') {
            $_SESSION['s_sysmsg'] .= "<font color='yellow'>���ǤϤ���ޤ���</font>";
        } else {
            $_SESSION['s_sysmsg'] .= "<br><font color='yellow'>���ǤϤ���ޤ���</font>";
        }
    }
} elseif ( isset($_GET['page_keep']) ) {                // ���ߤΥڡ�����ݻ����� GET�����
    $offset = $_SESSION['sales_offset'];
} elseif ( isset($_GET['page_keep']) ) {                // ���ߤΥڡ�����ݻ�����
    $offset = $_SESSION['sales_offset'];
} else {
    $_SESSION['sales_offset'] = 0;                            // ���ξ��ϣ��ǽ����
}
$offset = $_SESSION['sales_offset'];

//////////// ɽ�����Υǡ���ɽ���ѤΥ���ץ� Query & �����
$query = sprintf("select
                        u.�׾���        as �׾���,                  -- 0
                        CASE
                            WHEN u.datatype=1 THEN '����'
                            WHEN u.datatype=2 THEN '����'
                            WHEN u.datatype=3 THEN '����'
                            WHEN u.datatype=4 THEN 'Ĵ��'
                            WHEN u.datatype=5 THEN '��ư'
                            WHEN u.datatype=6 THEN 'ľǼ'
                            WHEN u.datatype=7 THEN '���'
                            WHEN u.datatype=8 THEN '����'
                            WHEN u.datatype=9 THEN '����'
                            ELSE u.datatype
                        END             as ��ʬ,                    -- 1
                        CASE
                            WHEN trim(u.�ײ��ֹ�)='' THEN '---'        --NULL�Ǥʤ��ƥ��ڡ�������ޤäƤ�����Ϥ��졪
                            ELSE u.�ײ��ֹ�
                        END                     as �ײ��ֹ�,        -- 2
                        u.assyno        as �����ֹ�,                -- 3
                        substr(m.midsc,1,18)    as ����̾,          -- 4
                        CASE
                            WHEN trim(u.���˾��)='' THEN '--'         --NULL�Ǥʤ��ƥ��ڡ�������ޤäƤ�����Ϥ��졪
                            ELSE u.���˾��
                        END                     as ����,            -- 5
                        u.����          as ����,                    -- 6
                        u.ñ��          as ����ñ��,                -- 7
                        Uround(u.���� * u.ñ��, 0) as �����,     -- 8
                        trim(assem.note15)  as �����ֹ�,            -- 9
                        aden.order_price  as ����ñ��,              --10
                        CASE
                            WHEN aden.order_price <= 0 THEN '0'
                            ELSE Uround(u.ñ�� / aden.order_price, 3) * 100
                        END                     as Ψ��,            --11
                        mate.sum_price + Uround(mate.assy_time * assy_rate, 2)
                                                as �������,        --12
                        CASE
                            WHEN (mate.sum_price + Uround(mate.assy_time * assy_rate, 2)) <= 0 THEN 0
                            ELSE Uround(u.ñ�� / (mate.sum_price + Uround(mate.assy_time * assy_rate, 2)), 3) * 100
                        END                     as Ψ��             --13
                  from
                        (hiuuri as u left outer join miitem as m on u.assyno=m.mipn)
                  left outer join
                        assembly_schedule as assem
                  on �ײ��ֹ�=assem.plan_no
                  left outer join
                        aden_master as aden
                  on �ײ��ֹ�=aden.plan_no
                  left outer join
                        material_cost_header as mate
                  on �ײ��ֹ�=mate.plan_no
                  %s
                  order by �׾���, assyno
                  offset %d limit %d
                  ", $search, $offset, PAGE);   // ���� $search �Ǹ���

$res   = array();
$field = array();
if (($rows = getResultWithField3($query, $field, $res)) <= 0) {
    $_SESSION['s_sysmsg'] .= sprintf("<font color='yellow'>������٤Υǡ���������ޤ���<br>%s��%s</font>", format_date($d_start), format_date($d_end) );
    header('Location: ' . H_WEB_HOST . $menu->out_RetUrl() . '?sum_exec=on');    // ľ���θƽи������
    exit();
} else {
    $num = count($field);       // �ե�����ɿ�����
    for ($r=0; $r<$rows; $r++) {
        $res[$r][4] = mb_convert_kana($res[$r][4], 'ka', 'EUC-JP');   // ���ѥ��ʤ�Ⱦ�ѥ��ʤإƥ���Ū�˥���С���
    }
    $_SESSION['SALES_TEST'] = sprintf("order by �׾��� offset %d limit %d", $offset, PAGE);
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
<?=$menu->out_site_java()?>
<?=$menu->out_css()?>
<!--    �ե��������ξ��
<script language='JavaScript' src='template.js?<?= $uniq ?>'>
</script>
-->

<script language="JavaScript">
<!--
/* ����ʸ�����������ɤ��������å� */
function isDigit(str) {
    var len=str.length;
    var c;
    for (i=0; i<len; i++) {
        c = str.charAt(i);
        if ((c < "0") || (c > "9")) {
            return true;
        }
    }
    return false;
}
/* ������ϥե�����Υ�����Ȥ˥ե������������� */
function set_focus(){
    document.body.focus();                          // F2/F12��������뤿����б�
//    document.form_name.element_name.select();
}
// -->
</script>

<!-- �������륷���ȤΥե��������򥳥��� HTML���� �����Ȥ�����Ҥ˽���ʤ��������
<link rel='stylesheet' href='<?= MENU_FORM . '?' . $uniq ?>' type='text/css' media='screen'>
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
a:hover {
    background-color:   blue;
    color:              white;
}
a {
    color:   blue;
}
body {
    background-image:url(<?= IMG ?>t_nitto_logo4.png);
    background-repeat:no-repeat;
    background-attachment:fixed;
    background-position:right bottom;
}
-->
</style>
</head>
<?php if (PAGE > 25) { ?>
<body onLoad='set_focus()'>
<?php } else { ?>
<body onLoad='set_focus()' style='overflow:hidden;'>
<?php } ?>
    <center>
<?=$menu->out_title_border()?>
        
        <!----------------- ������ ���� ���� �Υե����� ---------------->
        <table width='100%' border='0' cellspacing='0' cellpadding='0'>
            <form name='page_form' method='post' action='<?= $menu->out_self() ?>'>
                <tr>
                    <td align='left'>
                        <table align='left' border='3' cellspacing='0' cellpadding='0'>
                            <td align='left'>
                                <input class='pt10b' type='submit' name='backward' value='����'>
                            </td>
                        </table>
                    </td>
                    <td nowrap align='center' class='caption_font'>
                        <?= $menu->out_caption(), "\n" ?>
                    </td>
                    <td align='right'>
                        <table align='right' border='3' cellspacing='0' cellpadding='0'>
                            <td align='right'>
                                <input class='pt10b' type='submit' name='forward' value='����'>
                            </td>
                        </table>
                    </td>
                </tr>
            </form>
        </table>
        
        <!--------------- ����������ʸ��ɽ��ɽ������ -------------------->
        <table bgcolor='#d6d3ce' align='center' border='1' cellspacing='0' cellpadding='3'>
            <tr><td> <!----------- ���ߡ�(�ǥ�������) ------------>
        <table class='winbox_field' width='100%' bgcolor='#d6d3ce' align='center' border='1' cellspacing='0' cellpadding='3'>
            <thead>
                <!-- �ơ��֥� �إå�����ɽ�� -->
                <tr>
                    <th class='winbox' nowrap width='10'>No.</th>        <!-- �ԥʥ�С���ɽ�� -->
                <?php
                for ($i=0; $i<$num; $i++) {             // �ե�����ɿ�ʬ���֤�
                    if ($i >= 11) if ($div != 'S') break;
                ?>
                    <th class='winbox' nowrap><?= $field[$i] ?></th>
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
                    echo "<tr>\n";
                    echo "    <td class='winbox' nowrap align='right'><div class='pt10b'>" . ($r + $offset + 1) . "</div></td>    <!-- �ԥʥ�С���ɽ�� -->\n";
                    for ($i=0; $i<$num; $i++) {         // �쥳���ɿ�ʬ���֤�
                        if ($i >= 11) if ($div != 'S') break;
                        // <!--  bgcolor='#ffffc6' �������� --> 
                        if ($div != 'S') { // ������ �ʳ��ʤ�
                            switch ($i) {
                            case 0:     // �׾���
                                echo "<td class='winbox' nowrap align='center'><div class='pt9'>" . format_date($res[$r][$i]) . "</div></td>\n";
                                break;
                            case 4:     // ����̾
                                echo "<td class='winbox' nowrap width='270' align='left'><div class='pt9'>" . $res[$r][$i] . "</div></td>\n";
                                break;
                            case 6:     // ����
                                echo "<td class='winbox' nowrap width='45' align='right'><div class='pt9'>" . number_format($res[$r][$i], 0) . "</div></td>\n";
                                break;
                            case 7:     // ����ñ��
                                echo "<td class='winbox' nowrap width='60' align='right'><div class='pt9'>" . number_format($res[$r][$i], 2) . "</div></td>\n";
                                break;
                            case 8:     // ���
                                echo "<td class='winbox' nowrap width='70' align='right'><div class='pt9'>" . number_format($res[$r][$i], 0) . "</div></td>\n";
                                break;
                            case 9:     // �������
                                if ($res[$r][$i] == 0) {
                                    if ($res[$r][11]) {
                                        echo "<td class='winbox' nowrap width='60' align='right'>
                                                <a class='pt9' href='", $menu->out_action('�������Ȳ�'), "?plan_no={$res[$r][13]}&assy_no={$res[$r][3]}'target='application' style='text-decoration:none; color:brown;'>"
                                                , number_format($res[$r][11], 2), "</a></td>\n";
                                    } else {
                                        echo "<td class='winbox' nowrap width='60' align='right'><div class='pt9'>-</div></td>\n";
                                    }
                                } else {
                                    echo "<td class='winbox' nowrap width='60' align='right'>
                                            <a class='pt9' href='", $menu->out_action('�������Ȳ�'), "?plan_no={$res[$r][2]}&assy_no={$res[$r][3]}'target='application' style='text-decoration:none;'>"
                                            , number_format($res[$r][$i], 2), "</a></td>\n";
                                }
                                break;
                            case 10:    // Ψ(�������)
                                if ($res[$r][$i] > 0 && ($res[$r][$i] < 100.0)) {
                                    echo "<td class='winbox' nowrap width='40' align='right'><font class='pt9' color='red'>" . number_format($res[$r][$i], 1) . "</font></td>\n";
                                } elseif ($res[$r][$i] <= 0) {
                                    if ($res[$r][12]) {
                                        echo "<td class='winbox' nowrap width='40' align='right'><div class='pt9'>" . number_format($res[$r][12], 1) . "</div></td>\n";
                                    } else {
                                        echo "<td class='winbox' nowrap width='40' align='right'><div class='pt9'>-</div></td>\n";
                                    }
                                } else {
                                    echo "<td class='winbox' nowrap width='40' align='right'><div class='pt9'>" . number_format($res[$r][$i], 1) . "</div></td>\n";
                                }
                                break;
                            default:    // ����¾
                                echo "<td class='winbox' nowrap align='center'><div class='pt9'>" . $res[$r][$i] . "</div></td>\n";
                            }
                        } else {        // ������ʤ�
                            switch ($i) {
                            case 0:     // �׾���
                                echo "<td class='winbox' nowrap align='center'><div class='pt9'>" . format_date($res[$r][$i]) . "</div></td>\n";
                                break;
                            case 4:     // ����̾
                                echo "<td class='winbox' nowrap width='130' align='left'><div class='pt9'>" . $res[$r][$i] . "</div></td>\n";
                                break;
                            case 6:     // ����
                                echo "<td class='winbox' nowrap width='45' align='right'><div class='pt9'>" . number_format($res[$r][$i], 0) . "</div></td>\n";
                                break;
                            case 7:     // ����ñ��
                                echo "<td class='winbox' nowrap width='60' align='right'><div class='pt9'>" . number_format($res[$r][$i], 2) . "</div></td>\n";
                                break;
                            case 8:     // �����
                                echo "<td class='winbox' nowrap width='70' align='right'><div class='pt9' style='color:brown;'>" . number_format($res[$r][$i], 0) . "</div></td>\n";
                                break;
                            case 10:    // ����ñ��
                                if ($res[$r][$i] == 0) {
                                    echo "<td class='winbox' nowrap width='55' align='right'><div class='pt9'>-</div></td>\n";
                                } else {
                                    echo "<td class='winbox' nowrap width='55' align='right'><div class='pt9'>" . number_format($res[$r][$i], 0) . "</div></td>\n";
                                }
                                break;
                            case 11:    // Ψ
                                if ($res[$r][$i] > 0 && $res[$r][$i] < $uri_ritu) {
                                    echo "<td class='winbox' nowrap width='40' align='right'><font class='pt9' color='red'>" . number_format($res[$r][$i], 1) . "</font></td>\n";
                                } elseif ($res[$r][$i] <= 0) {
                                    echo "<td class='winbox' nowrap width='40' align='right'><div class='pt9'>-</div></td>\n";
                                } else {
                                    echo "<td class='winbox' nowrap width='40' align='right'><div class='pt9'>" . number_format($res[$r][$i], 1) . "</div></td>\n";
                                }
                                break;
                            case 12:    // �������
                                if ($res[$r][$i] == 0) {
                                    // echo "<td nowrap width='60' align='right' class='pt9'>" . number_format($res[$r][$i], 2) . "</td>\n";
                                    echo "<td class='winbox' nowrap width='60' align='right'><div class='pt9'>-</div></td>\n";
                                } else {
                                    echo "<td class='winbox' nowrap width='60' align='right'>
                                            <a class='pt9' href='", $menu->out_action('�������Ȳ�'), "?plan_no={$res[$r][2]}&assy_no={$res[$r][3]}'target='application' style='text-decoration:none;'>"
                                            , number_format($res[$r][$i], 2), "</a></td>\n";
                                }
                                break;
                            case 13:    // Ψ(�������)
                                if ($res[$r][$i] > 0 && ($res[$r][$i] < 100.0)) {
                                    echo "<td class='winbox' nowrap width='40' align='right'><font class='pt9' color='red'>" . number_format($res[$r][$i], 1) . "</font></td>\n";
                                } elseif ($res[$r][$i] <= 0) {
                                    echo "<td class='winbox' nowrap width='40' align='right'><div class='pt9'>-</div></td>\n";
                                } else {
                                    echo "<td class='winbox' nowrap width='40' align='right'><div class='pt9'>" . number_format($res[$r][$i], 1) . "</div></td>\n";
                                }
                                break;
                            default:    // ����¾
                                echo "<td class='winbox' nowrap align='center'><div class='pt9'>" . $res[$r][$i] . "</div></td>\n";
                            }
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
        <table style='border: 2px solid #0A0;'>
            <tr><td align='center' class='pt11b' tabindex='1' id='note'>���������Ŀ�ɽ����Ʊ�ײ��ֹ����Ͽ������ʪ�ǡ��㿧��Ʊ�ײ�Ǥ�̵��������������Ǻǿ�����Ͽ��ɽ��</td></tr>
        </table>
    </center>
</body>
<?=$menu->out_alert_java()?>
</html>
<?php
// ob_end_flush();                 // ���ϥХåե���gzip���� END
?>
