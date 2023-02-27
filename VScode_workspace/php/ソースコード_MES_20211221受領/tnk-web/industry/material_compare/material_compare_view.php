<?php
//////////////////////////////////////////////////////////////////////////////
// ����������� ���ۤξȲ� ����ɽ��                                         //
// Copyright(C) 2011      Noriisa.Ohya norihisa_ooya@nitto-kohki.co.jp      //
// Changed history                                                          //
// 2011/05/16 Created   material_compare_view.php                           //
// 2011/05/17 �ǿ����ڡ���Ψ���ɲá�ɽ������˳�Ψ����ɲá����������Ĵ����//
// 2011/05/26 ��ʬ�ࡦ��ʬ����ɲá�CSV���Ϥ��ɲ�                           //
//            ������������ؤΥ�󥯤��ɲ�                                //
// 2011/05/30 ����������Ӥ��̥�˥塼�ˤޤȤ᤿��require_once�Υ���ѹ�//
// 2011/05/31 ���롼�ץ������ѹ���ȼ��SQLʸ���ѹ�                           //
//////////////////////////////////////////////////////////////////////////////
ini_set('error_reporting', E_ALL);          // E_ALL='2047' debug ��
// ini_set('display_errors', '1');             // Error ɽ�� ON debug �� ��꡼���女����
// ini_set('zend.ze1_compatibility_mode', '1');    // zend 1.X ����ѥ� php4�θߴ��⡼��
// ini_set('implicit_flush', 'off');           // echo print �� flush �����ʤ�(�٤��ʤ뤿��) CLI CGI��
// ini_set('max_execution_time', 1200);        // ����¹Ի���=20ʬ CLI CGI��
ob_start('ob_gzhandler');                   // ���ϥХåե���gzip����
session_start();                            // ini_set()�μ��˻��ꤹ�뤳�� Script �Ǿ��

require_once ('../../function.php');            // define.php �� pgsql.php �� require_once ���Ƥ���
require_once ('../../tnk_func.php');            // TNK �˰�¸������ʬ�δؿ��� require_once ���Ƥ���
require_once ('../../MenuHeader.php');          // TNK ������ menu class
require_once ('../../ControllerHTTP_Class.php');// TNK ������ MVC Controller Class
//////////// ���å����Υ��󥹥��󥹤���Ͽ
$session = new Session();
if (isset($_REQUEST['recNo'])) {
    $session->add_local('recNo', $_REQUEST['recNo']);
    exit();
}
access_log();                               // Script Name �ϼ�ư����

///// TNK ���ѥ�˥塼���饹�Υ��󥹥��󥹤����
$menu = new MenuHeader(0);                  // ǧ�ڥ����å�0=���̰ʾ� �����=TOP_MENU �����ȥ�̤����

////////////// ����������
$menu->set_site(30, 999);                   // site_index=30(������˥塼) site_id=21(����������Ͽ �ײ��ֹ�)
////////////// �꥿���󥢥ɥ쥹����
// $menu->set_RetUrl(SYS_MENU);                // �̾�ϻ��ꤹ��ɬ�פϤʤ�
//////////// �����ȥ�̾(�������Υ����ȥ�̾�ȥե�����Υ����ȥ�̾)
$menu->set_title('����������� �������پȲ�');
//////////// �ƽ����action̾�ȥ��ɥ쥹����
$menu->set_action('�������Ȳ�',   INDUST . 'material/materialCost_view.php');
$menu->set_action('ñ����Ͽ�Ȳ�',   INDUST . 'parts/parts_cost_view.php');
$menu->set_action('������������',     INDUST . 'material/materialCost_view_assy.php');

//////////// �֥饦�����Υ���å����к���
$uniq = $menu->set_useNotCache('target');

//////////// �����Υ��å����ǡ�����¸   ���ǡ����Ǥ�ڤ����뤿��
if (! (isset($_POST['forward']) || isset($_POST['backward']) || isset($_GET['page_keep'])) ) {
    $session->add_local('recNo', '-1');         // 0�쥳���ɤǥޡ�����ɽ�����Ƥ��ޤ�������б�
    $_SESSION['s_uri_passwd'] = $_POST['uri_passwd'];
    $_SESSION['s_div']        = $_POST['div'];
    $_SESSION['s_first_ym']    = $_POST['first_ym'];
    $_SESSION['s_second_ym']      = $_POST['second_ym'];
    $_SESSION['uri_assy_no']  = $_POST['assy_no'];
    $_SESSION['s_order']  = $_POST['order'];
    $uri_passwd = $_SESSION['s_uri_passwd'];
    $div        = $_SESSION['s_div'];
    $first_ym    = $_SESSION['s_first_ym'];
    $second_ym      = $_SESSION['s_second_ym'];
    $assy_no    = $_SESSION['uri_assy_no'];
    $order      = $_SESSION['s_order'];
    ////////////// �ѥ���ɥ����å�
    if ($uri_passwd != date('Ymd')) {
        $_SESSION['s_sysmsg'] = "<font color='yellow'>�ѥ���ɤ��㤤�ޤ���</font>";
        header('Location: ' . H_WEB_HOST . $menu->out_RetUrl());    // ľ���θƽи������
        exit();
    }
} 
$uri_passwd = $_SESSION['s_uri_passwd'];
$div        = $_SESSION['s_div'];
$first_ym    = $_SESSION['s_first_ym'];
$second_ym      = $_SESSION['s_second_ym'];
$assy_no    = $_SESSION['uri_assy_no'];
$order      = $_SESSION['s_order'];

$cost1_ym = $first_ym;
$cost2_ym = $second_ym;

$nen        = substr($cost1_ym, 0, 4);
$tsuki      = substr($cost1_ym, 4, 2);
$cost1_name = $nen . "/" . $tsuki;

$nen        = substr($cost2_ym, 0, 4);
$tsuki      = substr($cost2_ym, 4, 2);
$cost2_name = $nen . "/" . $tsuki;

if (substr($cost1_ym,4,2)!=12) {
    $cost1_ymd = $cost1_ym + 1;
    $cost1_ymd = $cost1_ymd . '10';
} else {
    $cost1_ymd = $cost1_ym + 100;
    $cost1_ymd = $cost1_ymd - 11;
    $cost1_ymd = $cost1_ymd . '10';
}
if (substr($cost2_ym,4,2)!=12) {
    $cost2_ymd = $cost2_ym + 1;
    $cost2_ymd = $cost2_ymd . '10';
} else {
    $cost2_ymd = $cost2_ym + 100;
    $cost2_ymd = $cost2_ymd - 11;
    $cost2_ymd = $cost2_ymd . '10';
}

$str_ymd = $second_ym - 300;
$str_ymd = $str_ymd . '01';
$end_ymd = $second_ym . '31';

if ($div == "C") {
    if ($second_ym < 200710) {
        $rate = 25.60;  // ���ץ�ɸ�� 2007/10/01���ʲ������
    } elseif ($second_ym < 201104) {
        $rate = 57.00;  // ���ץ�ɸ�� 2007/10/01���ʲ���ʹ�
    } else {
        $rate = 45.00;  // ���ץ�ɸ�� 2011/04/01���ʲ���ʹ�
    }
} elseif ($div == "L") {
    if ($second_ym < 200710) {
        $rate = 37.00;  // ��˥� 2008/10/01���ʲ������
    } elseif ($second_ym < 201104) {
        $rate = 44.00;  // ��˥� 2008/10/01���ʲ���ʹ�
    } else {
        $rate = 53.00;  // ��˥� 2011/04/01���ʲ���ʹ�
    }
} else {
    $rate = 65.00;
}

///////// ��ΨȽ����
///////// ��Ψ������ǤϤʤ��ʤä���ɽ�����Υ��å����ѹ����롣
$power_rate = 1.13;      // 2011/04/01�ܹ�

if ($order == 'assy') {
    $order_name = 'ORDER BY �����ֹ� ASC';
} elseif ($order == 'diff') {
    $order_name = 'ORDER BY ���������� DESC, Ψ�� DESC, �Ȳ�� ASC, ��ʬ��̾ ASC, �����ֹ� ASC';
} elseif ($order == 'per') {
    $order_name = 'ORDER BY Ψ�� DESC, ���������� DESC, �Ȳ�� ASC, ��ʬ��̾ ASC, �����ֹ� ASC';
} elseif ($order == 'power') {
    $order_name = 'ORDER BY ��Ψ DESC, Ψ�� DESC, ���������� DESC, �Ȳ�� ASC, ��ʬ��̾ ASC, �����ֹ� ASC';
} elseif ($order == 'sorder') {
    $order_name = 'ORDER BY �Ȳ�� ASC, ��ʬ��̾ ASC, �����ֹ� ASC';
} else {
    $order_name = 'ORDER BY �����ֹ� ASC';
}

//////////// ɽ�������
//////////// �о�ǯ���ɽ���ǡ����Խ�
$end_y = substr($second_ym,0,4);
$end_m = substr($second_ym,4,2);
$str_y = substr($second_ym,0,4) - 3;
$str_m = substr($second_ym,4,2);

if ($div == "C") {
    $cap_div= "���ץ�ɸ����"; 
} elseif ($div == "L") {
    $cap_div= "��˥�"; 
}
$cap_set= $cap_div . "��{$str_y}ǯ{$str_m}���{$end_y}ǯ{$end_m}��ޤǤ�������ʤ��оݡ�{$cost1_name}����Ψ�ϡ�{$cost2_name}����Ψ{$rate}�ߤ���ѡ�<br>Ψ��ϡ�������������{$cost2_name}������񡣳�Ψ�ϡ��ǿ����ڡ�{$cost2_name}���������<BR>��Ψ��{$power_rate}����礭����С�<font color='blue'>�Ļ�</font>�����������<font color='red'>�ֻ�</font>��"; 
$menu->set_caption($cap_set);

//////////// �оݥǡ����μ���
$query = "
    SELECT
        u.assyno                    AS �����ֹ� --- 0
        ,
        trim(substr(m.midsc,1,40))  AS ����̾   --- 1
        ,
        CASE
            WHEN (SELECT sum_price + Uround((m_time + g_time) * {$rate}, 2) + Uround(a_time * a_rate, 2) FROM material_cost_header WHERE to_char(regdate, 'YYYYMMDD') < {$cost1_ymd} AND assy_no = u.assyno ORDER BY assy_no DESC, regdate DESC LIMIT 1) IS NULL THEN 0
            ELSE (SELECT sum_price + Uround((m_time + g_time) * {$rate}, 2) + Uround(a_time * a_rate, 2) FROM material_cost_header WHERE to_char(regdate, 'YYYYMMDD') < {$cost1_ymd} AND assy_no = u.assyno ORDER BY assy_no DESC, regdate DESC LIMIT 1)
        END                         AS ������� --- 2
        ,
        CASE
            WHEN (SELECT to_char(regdate, 'YYYY/MM/DD') FROM material_cost_header WHERE to_char(regdate, 'YYYYMMDD') < {$cost1_ymd} AND assy_no = u.assyno ORDER BY assy_no DESC, regdate DESC LIMIT 1) IS NULL THEN '----------'
            ELSE (SELECT to_char(regdate, 'YYYY/MM/DD') FROM material_cost_header WHERE to_char(regdate, 'YYYYMMDD') < {$cost1_ymd} AND assy_no = u.assyno ORDER BY assy_no DESC, regdate DESC LIMIT 1)
        END
                                    AS ��Ͽ�� --- 3
        ,
        CASE
            WHEN (SELECT sum_price + Uround((m_time + g_time) * {$rate}, 2) + Uround(a_time * a_rate, 2) FROM material_cost_header WHERE to_char(regdate, 'YYYYMMDD') < {$cost2_ymd} AND assy_no = u.assyno ORDER BY assy_no DESC, regdate DESC LIMIT 1) IS NULL THEN 0
            ELSE (SELECT sum_price + Uround((m_time + g_time) * {$rate}, 2) + Uround(a_time * a_rate, 2) FROM material_cost_header WHERE to_char(regdate, 'YYYYMMDD') < {$cost2_ymd} AND assy_no = u.assyno ORDER BY assy_no DESC, regdate DESC LIMIT 1)
        END                         AS ������� --- 4
        ,
        CASE
            WHEN (SELECT to_char(regdate, 'YYYY/MM/DD') FROM material_cost_header WHERE to_char(regdate, 'YYYYMMDD') < {$cost2_ymd} AND assy_no = u.assyno ORDER BY assy_no DESC, regdate DESC LIMIT 1) IS NULL THEN '----------'
            ELSE (SELECT to_char(regdate, 'YYYY/MM/DD') FROM material_cost_header WHERE to_char(regdate, 'YYYYMMDD') < {$cost2_ymd} AND assy_no = u.assyno ORDER BY assy_no DESC, regdate DESC LIMIT 1)
        END
                                    AS ��Ͽ�� --- 5
        ,
        CASE
            WHEN (SELECT sum_price + Uround((m_time + g_time) * {$rate}, 2) + Uround(a_time * a_rate, 2) FROM material_cost_header WHERE to_char(regdate, 'YYYYMMDD') < {$cost1_ymd} AND assy_no = u.assyno ORDER BY assy_no DESC, regdate DESC LIMIT 1) IS NULL THEN 0
            ELSE 
                CASE
                    WHEN (SELECT sum_price + Uround((m_time + g_time) * {$rate}, 2) + Uround(a_time * a_rate, 2) FROM material_cost_header WHERE to_char(regdate, 'YYYYMMDD') < {$cost2_ymd} AND assy_no = u.assyno ORDER BY assy_no DESC, regdate DESC LIMIT 1) IS NULL THEN 0
                    ELSE (SELECT sum_price + Uround((m_time + g_time) * {$rate}, 2) + Uround(a_time * a_rate, 2) FROM material_cost_header WHERE to_char(regdate, 'YYYYMMDD') < {$cost2_ymd} AND assy_no = u.assyno ORDER BY assy_no DESC, regdate DESC LIMIT 1)
                          - (SELECT sum_price + Uround((m_time + g_time) * {$rate}, 2) + Uround(a_time * a_rate, 2) FROM material_cost_header WHERE to_char(regdate, 'YYYYMMDD') < {$cost1_ymd} AND assy_no = u.assyno ORDER BY assy_no DESC, regdate DESC LIMIT 1)
                END
        END                         AS ����������   --- 6
        ,
        CASE
            WHEN (SELECT sum_price + Uround((m_time + g_time) * {$rate}, 2) + Uround(a_time * a_rate, 2) FROM material_cost_header WHERE to_char(regdate, 'YYYYMMDD') < {$cost2_ymd} AND assy_no = u.assyno ORDER BY assy_no DESC, regdate DESC LIMIT 1) IS NULL THEN 0
            ELSE 
                 CASE
                    WHEN (SELECT sum_price + Uround((m_time + g_time) * {$rate}, 2) + Uround(a_time * a_rate, 2) FROM material_cost_header WHERE to_char(regdate, 'YYYYMMDD') < {$cost1_ymd} AND assy_no = u.assyno ORDER BY assy_no DESC, regdate DESC LIMIT 1) IS NULL THEN 0
                    ELSE Uround(((SELECT sum_price + Uround((m_time + g_time) * {$rate}, 2) + Uround(a_time * a_rate, 2) FROM material_cost_header WHERE to_char(regdate, 'YYYYMMDD') < {$cost2_ymd} AND assy_no = u.assyno ORDER BY assy_no DESC, regdate DESC LIMIT 1)
                          - (SELECT sum_price + Uround((m_time + g_time) * {$rate}, 2) + Uround(a_time * a_rate, 2) FROM material_cost_header WHERE to_char(regdate, 'YYYYMMDD') < {$cost1_ymd} AND assy_no = u.assyno ORDER BY assy_no DESC, regdate DESC LIMIT 1)) 
                          / (SELECT sum_price + Uround((m_time + g_time) * {$rate}, 2) + Uround(a_time * a_rate, 2) FROM material_cost_header WHERE to_char(regdate, 'YYYYMMDD') < {$cost2_ymd} AND assy_no = u.assyno ORDER BY assy_no DESC, regdate DESC LIMIT 1), 4) * 100
                 END
        END                         AS Ψ��         --- 7
        ,
        CASE
            WHEN (SELECT price FROM sales_price_nk WHERE parts_no = u.assyno ORDER BY parts_no DESC, regdate DESC LIMIT 1) IS NULL THEN 0
            ELSE (SELECT price FROM sales_price_nk WHERE parts_no = u.assyno ORDER BY parts_no DESC, regdate DESC LIMIT 1)
        END                         AS �ǿ�����     --- 8
        ,
        CASE
            WHEN (SELECT price FROM sales_price_nk WHERE parts_no = u.assyno ORDER BY parts_no DESC, regdate DESC LIMIT 1) IS NULL THEN 0
            ELSE 
                 CASE
                    WHEN (SELECT sum_price + Uround((m_time + g_time) * {$rate}, 2) + Uround(a_time * a_rate, 2) FROM material_cost_header WHERE to_char(regdate, 'YYYYMMDD') < {$cost2_ymd} AND assy_no = u.assyno ORDER BY assy_no DESC, regdate DESC LIMIT 1) IS NULL THEN 0
                    ELSE Uround((SELECT price FROM sales_price_nk WHERE parts_no = u.assyno ORDER BY parts_no DESC, regdate DESC LIMIT 1)
                          /(SELECT sum_price + Uround((m_time + g_time) * {$rate}, 2) + Uround(a_time * a_rate, 2) FROM material_cost_header WHERE to_char(regdate, 'YYYYMMDD') < {$cost2_ymd} AND assy_no = u.assyno ORDER BY assy_no DESC, regdate DESC LIMIT 1), 2)
                 END
        END                         AS ��Ψ         --- 9
        ,
        CASE
            WHEN tgrp.top_name IS NULL THEN '------'
            ELSE tgrp.top_name
        END                         AS ��ʬ��̾     --- 10
        ,
        CASE
            WHEN mgrp.group_name IS NULL THEN '------'
            ELSE mgrp.group_name               
        END                         AS ��ʬ��̾     --- 11
        ---------------- �ꥹ�ȳ� -----------------
        ,
        (SELECT plan_no FROM material_cost_header WHERE to_char(regdate, 'YYYYMMDD') < {$cost1_ymd} AND assy_no = u.assyno ORDER BY assy_no DESC, regdate DESC LIMIT 1)
                                    AS �裱������ײ� --- 12
        ,
        (SELECT plan_no FROM material_cost_header WHERE to_char(regdate, 'YYYYMMDD') < {$cost2_ymd} AND assy_no = u.assyno ORDER BY assy_no DESC, regdate DESC LIMIT 1)
                                    AS �裲������ײ� --- 13
        ,
        (SELECT a_rate FROM material_cost_header WHERE to_char(regdate, 'YYYYMMDD') < {$cost1_ymd} AND assy_no = u.assyno ORDER BY assy_no DESC, regdate DESC LIMIT 1)                     AS �裱��ư����Ψ,      -- 14
        (SELECT a_time FROM material_cost_header WHERE to_char(regdate, 'YYYYMMDD') < {$cost1_ymd} AND assy_no = u.assyno ORDER BY assy_no DESC, regdate DESC LIMIT 1)                     AS �裱��ư������,      -- 15
        (SELECT m_time FROM material_cost_header WHERE to_char(regdate, 'YYYYMMDD') < {$cost1_ymd} AND assy_no = u.assyno ORDER BY assy_no DESC, regdate DESC LIMIT 1)                     AS �裱���ȹ���,      -- 16
        (SELECT g_time FROM material_cost_header WHERE to_char(regdate, 'YYYYMMDD') < {$cost1_ymd} AND assy_no = u.assyno ORDER BY assy_no DESC, regdate DESC LIMIT 1)                     AS �裱������,        -- 17
        (SELECT a_rate FROM material_cost_header WHERE to_char(regdate, 'YYYYMMDD') < {$cost2_ymd} AND assy_no = u.assyno ORDER BY assy_no DESC, regdate DESC LIMIT 1)                     AS �裲��ư����Ψ,      -- 18
        (SELECT a_time FROM material_cost_header WHERE to_char(regdate, 'YYYYMMDD') < {$cost2_ymd} AND assy_no = u.assyno ORDER BY assy_no DESC, regdate DESC LIMIT 1)                     AS �裲��ư������,      -- 19
        (SELECT m_time FROM material_cost_header WHERE to_char(regdate, 'YYYYMMDD') < {$cost2_ymd} AND assy_no = u.assyno ORDER BY assy_no DESC, regdate DESC LIMIT 1)                     AS �裲���ȹ���,      -- 20
        (SELECT g_time FROM material_cost_header WHERE to_char(regdate, 'YYYYMMDD') < {$cost2_ymd} AND assy_no = u.assyno ORDER BY assy_no DESC, regdate DESC LIMIT 1)                     AS �裲������,        -- 21
        tgrp.s_order                AS �Ȳ��         -- 22
    FROM
          hiuuri AS u
    LEFT OUTER JOIN
          assembly_schedule AS a
    ON (u.�ײ��ֹ� = a.plan_no)
    LEFT OUTER JOIN
          miitem AS m
    ON (u.assyno = m.mipn)
    LEFT OUTER JOIN
          material_old_product AS mate
    ON (u.assyno = mate.assy_no)
    LEFT OUTER JOIN
          mshmas AS mas
    ON (u.assyno = mas.mipn)
    LEFT OUTER JOIN
          mshmas AS hmas
    ON (u.assyno = hmas.mipn)
    LEFT OUTER JOIN
          -- mshgnm AS gnm
          msshg3 AS gnm
    -- ON (hmas.mhjcd = gnm.mhgcd)
    ON (hmas.mhshc = gnm.mhgcd)
    LEFT OUTER JOIN
          product_serchGroup AS mgrp
    ON (gnm.mhggp = mgrp.group_no)
    LEFT OUTER JOIN
          product_top_serchgroup AS tgrp
    ON (mgrp.top_code = tgrp.top_no)
    WHERE �׾��� >= {$str_ymd} AND �׾��� <= {$end_ymd} AND ������ = '{$div}' AND (note15 NOT LIKE 'SC%%' OR note15 IS NULL) AND datatype='1'
        AND mate.assy_no IS NULL
        -- ������ɲä���м�ư������Ͽ�������� AND (SELECT a_rate FROM material_cost_header WHERE assy_no = u.assyno ORDER BY assy_no DESC, regdate DESC LIMIT 1) > 0
    GROUP BY u.assyno, m.midsc, tgrp.top_name, mgrp.group_name, tgrp.s_order
    {$order_name}
    OFFSET 0 LIMIT 10000
";
$res   = array();
$field = array();
if (($rows = getResultWithField3($query, $field, $res)) <= 0) {
    $_SESSION['s_sysmsg'] .= sprintf("<font color='yellow'>������٤Υǡ���������ޤ���<br>%s��%s</font>", format_date($first_ym), format_date($second_ym) );
    header('Location: ' . H_WEB_HOST . $menu->out_RetUrl());    // ľ���θƽи������
    exit();
} else {
    $num = count($field);       // �ե�����ɿ�����
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
.pt9b {
    font-size:      9pt;
    font-family:    monospace;
    color:          blue;
}
.pt9r {
    font-size:      9pt;
    font-family:    monospace;
    color:          red;
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
    background-image:url(<?php echo IMG ?>t_nitto_logo4.png);
    background-repeat:no-repeat;
    background-attachment:fixed;
    background-position:right bottom;
}
-->
</style>
</head>
    <center>
<?php echo $menu->out_title_border()?>
        
        <!----------------- ������ ���� ���� �Υե����� ---------------->
        <table width='100%' border='0' cellspacing='0' cellpadding='0'>
            <form name='page_form' method='post' action='<?php echo $menu->out_self() ?>'>
                <tr>
                    <!------
                    <td align='left'>
                        <table align='left' border='3' cellspacing='0' cellpadding='0'>
                            <td align='left'>
                                <input class='pt10b' type='submit' name='backward' value='����'>
                            </td>
                        </table>
                    </td>
                    -------->
                    <td nowrap align='center' class='caption_font'>
                        <?php echo $menu->out_caption(), "\n" ?>
                        <a href='material_compare_csv.php?csv_div=<?php echo $div ?>&csv_first_ym=<?php echo $first_ym ?>&csv_second_ym=<?php echo $second_ym ?>&csv_assy_no=<?php echo $assy_no ?>&csv_order=<?php echo $order ?>'>
                        CSV����
                        </a>
                    </td>
                    <!------
                    <td align='right'>
                        <table align='right' border='3' cellspacing='0' cellpadding='0'>
                            <td align='right'>
                                <input class='pt10b' type='submit' name='forward' value='����'>
                            </td>
                        </table>
                    </td>
                    -------->
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
                    //if ($i == 5) continue;
                    if ($i >= 12) continue;
                    if ($i == 2) {
                ?>
                    <th class='winbox' nowrap><?php echo $cost1_name ?><BR><?php echo $field[$i] ?></th>
                <?php
                    } elseif ($i == 4) {
                ?>
                    <th class='winbox' nowrap><?php echo $cost2_name ?><BR><?php echo $field[$i] ?></th>
                <?php
                    } elseif ($i == 6) {
                ?>
                    <th class='winbox' nowrap>������<BR>����</th>
                <?php
                    } elseif ($i == 8) {
                ?>
                    <th class='winbox' nowrap>�ǿ�<BR>����</th>
                <?php
                    } else {
                ?>
                    <th class='winbox' nowrap><?php echo $field[$i] ?></th>
                <?php
                    }
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
                    $recNo = $r;
                    if ($session->get_local('recNo') == $recNo) {
                        echo "<tr style='background-color:#ffffc6;'>\n";
                    } else {
                        echo "<tr>\n";
                    }
                    echo "    <td class='winbox' nowrap align='right'><div class='pt10b'>" . ($r + 1) . "</div></td>    <!-- �ԥʥ�С���ɽ�� -->\n";
                    for ($i=0; $i<$num; $i++) {         // �쥳���ɿ�ʬ���֤�
                        if ($i >= 12) continue;
                        // <!--  bgcolor='#ffffc6' �������� --> 
                        switch ($i) {
                        case 0:     // �����ֹ�
                            echo "<td class='winbox' nowrap align='center'><div class='pt9'>
                                                <a class='pt9' href='JavaScript:baseJS.Ajax(\"{$menu->out_self()}?recNo={$recNo}\");location.replace(\"", $menu->out_action('������������'), "?assy=", urlencode($res[$r][$i]), "&material=1\")' target='application' style='text-decoration:none;'>"
                                                , $res[$r][$i], "</a></div></td>\n";
                            break;
                        case 1:     // ����̾
                            echo "<td class='winbox' nowrap width='230' align='left'><div class='pt9'>" . $res[$r][$i] . "</div></td>\n";
                            break;
                        case 2:     // �裱�������
                            if ($res[$r][$i] != 0) {
                                echo "<td class='winbox' nowrap width='60' align='right'>
                                                <a class='pt9' href='JavaScript:baseJS.Ajax(\"{$menu->out_self()}?recNo={$recNo}\");location.replace(\"", $menu->out_action('�������Ȳ�'), "?plan_no={$res[$r][12]}&assy_no={$res[$r][0]}\")' target='application' style='text-decoration:none;'>"
                                                , number_format($res[$r][$i], 2), "</a></td>\n";
                            } else {
                                echo "<td class='winbox' nowrap width='60' align='right'><div class='pt9'>" . number_format($res[$r][$i], 2) . "</div></td>\n";
                            }
                            break;
                        case 4:     // �裲�������
                            if ($res[$r][$i] != 0) {
                                echo "<td class='winbox' nowrap width='60' align='right'>
                                                <a class='pt9' href='JavaScript:baseJS.Ajax(\"{$menu->out_self()}?recNo={$recNo}\");location.replace(\"", $menu->out_action('�������Ȳ�'), "?plan_no={$res[$r][13]}&assy_no={$res[$r][0]}\")' target='application' style='text-decoration:none;'>"
                                                , number_format($res[$r][$i], 2), "</a></td>\n";
                            } else {
                                echo "<td class='winbox' nowrap width='60' align='right'><div class='pt9'>" . number_format($res[$r][$i], 2) . "</div></td>\n";
                            }
                            break;
                        case 6:    // �����������
                            echo "<td class='winbox' nowrap width='70' align='right'><div class='pt9'>" . number_format($res[$r][$i], 2) . "</div></td>\n";
                            break;
                        case 7:    // Ψ��
                            echo "<td class='winbox' nowrap width='50' align='right'><div class='pt9'>" . number_format($res[$r][$i], 2) . "</div></td>\n";
                            break;
                        case 8:    // �ǿ�����
                            echo "<td class='winbox' nowrap width='60' align='right'><div class='pt9'>" . number_format($res[$r][$i], 2) . "</div></td>\n";
                            break;
                        case 9:   // ��Ψ
                            if ($res[$r][$i] < $power_rate) {
                                echo "<td class='winbox' nowrap width='30' align='right'><div class='pt9r'>" . number_format($res[$r][$i], 2) . "</div></td>\n";
                            } elseif ($res[$r][$i] > $power_rate) {
                                echo "<td class='winbox' nowrap width='30' align='right'><div class='pt9b'>" . number_format($res[$r][$i], 2) . "</div></td>\n";
                            } elseif ($res[$r][$i] == $power_rate) {
                                echo "<td class='winbox' nowrap width='30' align='right'><div class='pt9'>" . number_format($res[$r][$i], 2) . "</div></td>\n";
                            }
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
ob_end_flush();                 // ���ϥХåե���gzip���� END
?>
