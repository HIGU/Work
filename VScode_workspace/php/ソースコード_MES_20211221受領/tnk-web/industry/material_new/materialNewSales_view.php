<?php
//////////////////////////////////////////////////////////////////////////////
// ����ñ���ƶ��ۤξȲ� View��                                              //
// Copyright (C) 2010-2011 Norihisa.Ohya norihisa_ooya@nitto-kohki.co.jp    //
// Changed history                                                          //
// 2010/05/13 Created   materialNewSales_view.php                           //
// 2010/05/21 ���顼����ľ���θƤӽФ�������뤬���顼�ΰ�URLľ�ܻ�����ѹ� //
// 2011/06/30 Ψ��ɽ����ݤ�Ψ�˹�碌�ơ󤸤�ʤ�����                      //
// 2011/07/11 ���ǯ�������������Ͽ���ʤ��ä���硢Ʊ���ײ�NO�Υǡ�����  //
//            ����ǯ�����κǽ���Υǡ�����ɽ������褦���ѹ�                //
//////////////////////////////////////////////////////////////////////////////
// ini_set('error_reporting', E_ALL);          // E_ALL='2047' debug ��
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
$menu->set_site(INDEX_INDUST, 999);                    // site_index=01(����˥塼) site_id=11(����������)
////////////// �꥿���󥢥ɥ쥹����
$menu->set_RetUrl('materialNewSales_form.php');                // �̾�ϻ��ꤹ��ɬ�פϤʤ�
//////////// �����ȥ�̾(�������Υ����ȥ�̾�ȥե�����Υ����ȥ�̾)
$menu->set_title('����ñ���ƶ��ۤξȲ�');
//////////// �ƽ����action̾�ȥ��ɥ쥹����
$menu->set_action('�������Ȳ�',   INDUST . 'material/materialCost_view.php');
$menu->set_action('ñ����Ͽ�Ȳ�',   INDUST . 'parts/parts_cost_view.php');
$menu->set_action('�����������',   INDUST . 'material/materialCost_view_assy.php');

//////////// �֥饦�����Υ���å����к���
$uniq = $menu->set_useNotCache('target');

//////////// �����Υ��å����ǡ�����¸   ���ǡ����Ǥ�ڤ����뤿��
if (! (isset($_REQUEST['forward']) || isset($_REQUEST['backward']) || isset($_REQUEST['page_keep'])) ) {
    $session->add_local('recNo', '-1');         // 0�쥳���ɤǥޡ�����ɽ�����Ƥ��ޤ�������б�
    $_SESSION['s_uri_passwd'] = $_REQUEST['uri_passwd'];
    $_SESSION['s_div']        = $_REQUEST['div'];
    $_SESSION['s_d_start']    = $_REQUEST['d_start'];
    $_SESSION['s_d_end']      = $_REQUEST['d_end'];
    $_SESSION['s_kubun']      = $_REQUEST['kubun'];
    $_SESSION['s_uri_ritu']   = $_REQUEST['uri_ritu'];
    $_SESSION['s_sales_page'] = $_REQUEST['sales_page'];
    $_SESSION['uri_assy_no']  = $_REQUEST['assy_no'];
    $_SESSION['target_ym']    = $_REQUEST['target_ym'];
    $uri_passwd = $_SESSION['s_uri_passwd'];
    $div        = $_SESSION['s_div'];
    $d_start    = $_SESSION['s_d_start'];
    $d_end      = $_SESSION['s_d_end'];
    $kubun      = $_SESSION['s_kubun'];
    $uri_ritu   = $_SESSION['s_uri_ritu'];
    $assy_no    = $_SESSION['uri_assy_no'];
    $target_ym  = $_SESSION['target_ym'];
        ///// day �Υ����å�
        if (substr($d_start, 6, 2) < 1) $d_start = substr($d_start, 0, 6) . '01';
        ///// �ǽ���������å����ƥ��åȤ���
        if (!checkdate(substr($d_start, 4, 2), substr($d_start, 6, 2), substr($d_start, 0, 4))) {
            $d_start = ( substr($d_start, 0, 6) . last_day(substr($d_start, 0, 4), substr($d_start, 4, 2)) );
            if (!checkdate(substr($d_start, 4, 2), substr($d_start, 6, 2), substr($d_start, 0, 4))) {
                $_SESSION['s_sysmsg'] = '���դλ��꤬�����Ǥ���';
                header('Location:http://10.1.3.252/industry/material_new/materialNewSales_form.php');    // ľ���θƽи������
                exit();
            }
        }
        ///// day �Υ����å�
        if (substr($d_end, 6, 2) < 1) $d_end = substr($d_end, 0, 6) . '01';
        ///// �ǽ���������å����ƥ��åȤ���
        if (!checkdate(substr($d_end, 4, 2), substr($d_end, 6, 2), substr($d_end, 0, 4))) {
            $d_end = ( substr($d_end, 0, 6) . last_day(substr($d_end, 0, 4), substr($d_end, 4, 2)) );
            if (!checkdate(substr($d_start, 4, 2), substr($d_start, 6, 2), substr($d_start, 0, 4))) {
                $_SESSION['s_sysmsg'] = '���դλ��꤬�����Ǥ���';
                header('Location:http://10.1.3.252/industry/material_new/materialNewSales_form.php');    // ľ���θƽи������
                exit();
            }
        }
    $_SESSION['s_d_start'] = $d_start;
    $_SESSION['s_d_end']   = $d_end  ;
    
    ////////////// �ѥ���ɥ����å�
    if ($uri_passwd != date('Ymd')) {
        $_SESSION['s_sysmsg'] = "<font color='yellow'>�ѥ���ɤ��㤤�ޤ���</font>";
        header('Location:http://10.1.3.252/industry/material_new/materialNewSales_form.php');    // ľ���θƽи������
        exit();
    }
    ///////////// ��׶�ۡ�����������
    if ( ($div != 'S') && ($div != 'D') ) {      // �������ɸ�� �ʳ��ʤ�
        $query = "select
                        count(����) as t_ken,
                        sum(����) as t_kazu,
                        sum(Uround(����*ñ��,0)) as t_kingaku,
                        sum(Uround(����*(SELECT new_price FROM sales_price_new WHERE parts_no=assyno AND cost_ym={$target_ym} limit 1),0))
                                  as n_kingaku
                  from
                        hiuuri";
    } else {
        $query = "select
                        count(����) as t_ken,
                        sum(����) as t_kazu,
                        sum(Uround(����*ñ��,0)) as t_kingaku,
                        sum(Uround(����*(SELECT new_price FROM sales_price_new WHERE parts_no=assyno AND cost_ym={$target_ym} limit 1),0))
                                    as n_kingaku
                  from
                        hiuuri
                  left outer join
                        assembly_schedule as a
                  on �ײ��ֹ�=plan_no";
    }
    //////////// SQL where ��� ���Ѥ���
    $search = "where �׾���>=$d_start and �׾���<=$d_end";
    if ($assy_no != '') {       // �����ֹ椬���ꤵ�줿���
        $search .= " and assyno like '{$assy_no}%%'";
    } elseif ($div == 'S') {    // ������ʤ�
        $search .= " and ������='C' and note15 like 'SC%%'";
        $search .= " and (assyno not like 'NKB%%')";
        $search .= " and (assyno not like 'SS%%')";
    } elseif ($div == 'D') {    // ��ɸ��ʤ�
        $search .= " and ������='C' and (note15 NOT like 'SC%%' OR note15 IS NULL)";    // ��������ɸ��ؤ���
        $search .= " and (assyno not like 'NKB%%')";
        $search .= " and (assyno not like 'SS%%')";
    } elseif ($div == "N") {    // ��˥��ΥХ���롦���������� assyno �ǥ����å�
        $search .= " and ������='L' and (assyno NOT like 'LC%%' AND assyno NOT like 'LR%%')";
        $search .= " and (assyno not like 'SS%%')";
    } elseif ($div == "B") {    // �Х����ξ��� assyno �ǥ����å�
        $search .= " and (assyno like 'LC%%' or assyno like 'LR%%')";
    } elseif ($div == "SSC") {   // ���ץ��������ξ��� assyno �ǥ����å�
        $search .= " and ������='C' and (assyno like 'SS%%')";
    } elseif ($div == "SSL") {   // ��˥���������ξ��� assyno �ǥ����å�
        $search .= " and ������='L' and (assyno like 'SS%%')";
    } elseif ($div == "NKB") {  // ���ʴ����ξ��� assyno �ǥ����å�
        $search .= " and (assyno like 'NKB%%')";
    } elseif ($div == "_") {    // �������ʤ�
        $search .= " and ������=' '";
    } elseif ($div == "C") {
        $search .= " and ������='$div'";
        $search .= " and (assyno not like 'NKB%%')";
        $search .= " and (assyno not like 'SS%%')";
    } elseif ($div == "L") {
        $search .= " and ������='$div'";
        $search .= " and (assyno not like 'SS%%')";
    } elseif ($div != " ") {
        $search .= " and ������='$div'";
    }
    if ($kubun != " ") {
        $search .= " and datatype='$kubun'";
    }
    $query = sprintf("$query %s", $search);     // SQL query ʸ�δ���
    $_SESSION['sales_search'] = $search;        // SQL��where�����¸
    $res_sum = array();
    if (getResult($query, $res_sum) <= 0) {
        $_SESSION['s_sysmsg'] = '��׶�ۤμ����˼��Ԥ��ޤ�����';
        header('Location:http://10.1.3.252/industry/material_new/materialNewSales_form.php');    // ľ���θƽи������
        exit();
    } else {
        $t_ken     = $res_sum[0]['t_ken'];
        $t_kazu    = $res_sum[0]['t_kazu'];
        $t_kingaku = $res_sum[0]['t_kingaku'];
        $n_kingaku = $res_sum[0]['n_kingaku'];
        $d_kingaku = $n_kingaku - $t_kingaku;
        $_SESSION['u_t_ken']  = $t_ken;
        $_SESSION['u_t_kazu'] = $t_kazu;
        $_SESSION['u_t_kin']  = $t_kingaku;
        $_SESSION['u_n_kin']  = $n_kingaku;
        $_SESSION['u_d_kin']  = $d_kingaku;
    }
} else {                                                // �ڡ������ؤʤ�
    $t_ken     = $_SESSION['u_t_ken'];
    $t_kazu    = $_SESSION['u_t_kazu'];
    $t_kingaku = $_SESSION['u_t_kin'];
    $n_kingaku = $_SESSION['u_n_kin'];
    $d_kingaku = $_SESSION['u_d_kin'];
}

$uri_passwd = $_SESSION['s_uri_passwd'];
$div        = $_SESSION['s_div'];
$d_start    = $_SESSION['s_d_start'];
$d_end      = $_SESSION['s_d_end'];
$kubun      = $_SESSION['s_kubun'];
$uri_ritu   = $_SESSION['s_uri_ritu'];
$assy_no    = $_SESSION['uri_assy_no'];
$search     = $_SESSION['sales_search'];
$target_ym  = $_SESSION['target_ym'];

$second_ym  = substr($d_end, 0, 6);

///// ���ʥ��롼��(������)̾������
if ($div == " ") $div_name = "�����롼��";
if ($div == "C") $div_name = "���ץ�����";
if ($div == "D") $div_name = "���ץ�ɸ��";
if ($div == "S") $div_name = "���ץ�����";
if ($div == "L") $div_name = "��˥�����";
if ($div == "N") $div_name = "��˥��Τ�";
if ($div == "B") $div_name = "�Х����Τ�";
if ($div == "SSC") $div_name = "���ץ�";
if ($div == "SSL") $div_name = "��˥��";
if ($div == "NKB") $div_name = "���ʴ���";
if ($div == "T") $div_name = "�ġ���";
if ($div == "_") $div_name = "�ʤ�";

//////////// ���ǤιԿ�
if (isset($_SESSION['s_sales_page'])) {
    define('PAGE', $_SESSION['s_sales_page']);
} else {
    define('PAGE', 25);
}

//////////// ��ץ쥳���ɿ�����     (�оݥơ��֥�κ������ڡ�������˻���)
$maxrows = $t_ken;

//////////// �ڡ������ե��å�����
if ( isset($_REQUEST['forward']) ) {                       // ���Ǥ������줿
    $_SESSION['sales_offset'] += PAGE;
    if ($_SESSION['sales_offset'] >= $maxrows) {
        $_SESSION['sales_offset'] -= PAGE;
        if ($_SESSION['s_sysmsg'] == '') {
            $_SESSION['s_sysmsg'] .= "<font color='yellow'>���ǤϤ���ޤ���</font>";
        } else {
            $_SESSION['s_sysmsg'] .= "<br><font color='yellow'>���ǤϤ���ޤ���</font>";
        }
    }
} elseif ( isset($_REQUEST['backward']) ) {                // ���Ǥ������줿
    $_SESSION['sales_offset'] -= PAGE;
    if ($_SESSION['sales_offset'] < 0) {
        $_SESSION['sales_offset'] = 0;
        if ($_SESSION['s_sysmsg'] == '') {
            $_SESSION['s_sysmsg'] .= "<font color='yellow'>���ǤϤ���ޤ���</font>";
        } else {
            $_SESSION['s_sysmsg'] .= "<br><font color='yellow'>���ǤϤ���ޤ���</font>";
        }
    }
} elseif ( isset($_REQUEST['page_keep']) ) {                // ���ߤΥڡ�����ݻ����� GET�����
    $offset = $_SESSION['sales_offset'];
} elseif ( isset($_REQUEST['page_keep']) ) {                // ���ߤΥڡ�����ݻ�����
    $offset = $_SESSION['sales_offset'];
} else {
    $_SESSION['sales_offset'] = 0;                            // ���ξ��ϣ��ǽ����
}
$offset = $_SESSION['sales_offset'];

// ������ ���۹�פη׻�
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
                                WHEN trim(u.�ײ��ֹ�)='' THEN '---'         --NULL�Ǥʤ��ƥ��ڡ�������ޤäƤ�����Ϥ��졪
                                ELSE u.�ײ��ֹ�
                            END                     as �ײ��ֹ�,        -- 2
                            CASE
                                WHEN trim(u.assyno) = '' THEN '---'
                                ELSE u.assyno
                            END                     as �����ֹ�,        -- 3
                            CASE
                                WHEN trim(substr(m.midsc,1,38)) = '' THEN '&nbsp;'
                                WHEN m.midsc IS NULL THEN '&nbsp;'
                                ELSE substr(m.midsc,1,38)
                            END             as ����̾,                  -- 4
                            CASE
                                WHEN trim(u.���˾��)='' THEN '--'         --NULL�Ǥʤ��ƥ��ڡ�������ޤäƤ�����Ϥ��졪
                                ELSE u.���˾��
                            END                     as ����,            -- 5
                            u.����          as ����,                    -- 6
                            u.ñ��          as ����ñ��,                -- 7
                            Uround(u.���� * u.ñ��, 0) as ���,         -- 8
                            sum_price + Uround((m_time + g_time) * assy_rate, 2) + Uround(a_time * a_rate, 2)
                                                    as �������,        -- 9
                            CASE
                                WHEN (sum_price + Uround((m_time + g_time) * assy_rate, 2) + Uround(a_time * a_rate, 2)) <= 0 THEN 0
                                ELSE Uround(u.ñ�� / (sum_price + Uround((m_time + g_time) * assy_rate, 2) + Uround(a_time * a_rate, 2)), 2)
                            END                     as ����Ψ,            --10
                            (select sum_price + Uround((m_time + g_time) * assy_rate, 2) + Uround(a_time * a_rate, 2) from material_cost_header where assy_no=u.assyno AND regdate<=�׾��� order by assy_no DESC, regdate DESC limit 1)
                                                    AS �������2,       --11
                            (select Uround(u.ñ�� / (sum_price + Uround((m_time + g_time) * assy_rate, 2) + Uround(a_time * a_rate, 2)), 2) from material_cost_header where assy_no=u.assyno AND regdate<=�׾��� order by assy_no DESC, regdate DESC limit 1)
                                                    AS ����Ψ,            --12
                            (select plan_no from material_cost_header where assy_no=u.assyno AND regdate<=�׾��� order by assy_no DESC, regdate DESC limit 1)
                                                    AS �ײ��ֹ�2,       --13
                            (SELECT sum(lot_cost) FROM parts_cost_history WHERE parts_no=u.assyno AND as_regdate<�׾��� AND lot_no=1 AND vendor!='88888' GROUP BY as_regdate, reg_no ORDER BY as_regdate DESC, reg_no DESC limit 1)
                                                    AS ���ʺ�����,      --14
                            (SELECT reg_no FROM parts_cost_history WHERE parts_no=u.assyno AND as_regdate<�׾��� AND lot_no=1 AND vendor!='88888' GROUP BY as_regdate, reg_no ORDER BY as_regdate DESC, reg_no DESC limit 1)
                                                    AS ñ����Ͽ�ֹ�,    --15
                            (SELECT cost_new FROM sales_price_new WHERE parts_no=u.assyno AND cost_ym={$target_ym} limit 1)
                                                    AS ������,        --16
                            (SELECT credit_per FROM sales_price_new WHERE parts_no=u.assyno AND cost_ym={$target_ym} limit 1)
                                                    AS ��Ψ,            --17
                            (SELECT new_price FROM sales_price_new WHERE parts_no=u.assyno AND cost_ym={$target_ym} limit 1)
                                                    AS �������,        --18
                            (SELECT plan_no FROM sales_price_new WHERE parts_no=u.assyno AND cost_ym={$target_ym} limit 1)
                                                    AS ������ײ�,    --19
                            CASE
                                WHEN (SELECT cost_new FROM sales_price_new WHERE parts_no=u.assyno AND plan_no=u.�ײ��ֹ� limit 1) IS NULL THEN (SELECT cost_new FROM sales_price_new WHERE parts_no=u.assyno AND cost_ym={$second_ym} limit 1)
                                ELSE (SELECT cost_new FROM sales_price_new WHERE parts_no=u.assyno AND plan_no=u.�ײ��ֹ� limit 1)
                            END                     AS ������2,       -- 20
                            CASE
                                WHEN (SELECT credit_per FROM sales_price_new WHERE parts_no=u.assyno AND plan_no=u.�ײ��ֹ� limit 1) IS NULL THEN (SELECT credit_per FROM sales_price_new WHERE parts_no=u.assyno AND cost_ym={$second_ym} limit 1)
                                ELSE (SELECT credit_per FROM sales_price_new WHERE parts_no=u.assyno AND plan_no=u.�ײ��ֹ� limit 1)
                            END                     AS ��Ψ2,           -- 21
                            CASE
                                WHEN (SELECT new_price FROM sales_price_new WHERE parts_no=u.assyno AND plan_no=u.�ײ��ֹ� limit 1) IS NULL THEN (SELECT new_price FROM sales_price_new WHERE parts_no=u.assyno AND cost_ym={$second_ym} limit 1)
                                ELSE (SELECT new_price FROM sales_price_new WHERE parts_no=u.assyno AND plan_no=u.�ײ��ֹ� limit 1)
                            END                     AS �������2,       -- 22
                            CASE
                                WHEN (SELECT plan_no FROM sales_price_new WHERE parts_no=u.assyno AND plan_no=u.�ײ��ֹ� limit 1) IS NULL THEN (SELECT plan_no FROM sales_price_new WHERE parts_no=u.assyno AND cost_ym={$second_ym} limit 1)
                                ELSE (SELECT plan_no FROM sales_price_new WHERE parts_no=u.assyno AND plan_no=u.�ײ��ֹ� limit 1)
                            END                     AS ������ײ�2    -- 23
                      from
                            hiuuri as u
                      left outer join
                            assembly_schedule as a
                      on u.�ײ��ֹ�=a.plan_no
                      left outer join
                            miitem as m
                      on u.assyno=m.mipn
                      left outer join
                            material_cost_header as mate
                      on u.�ײ��ֹ�=mate.plan_no
                      LEFT OUTER JOIN
                            sales_parts_material_history AS pmate
                      ON (u.assyno=pmate.parts_no AND u.�׾���=pmate.sales_date)
                      %s
                      order by �׾���, assyno
                      ", $search);   // ���� $search �Ǹ���
                      
$res   = array();
$field = array();
if (($rows = getResultWithField3($query, $field, $res)) <= 0) {
    $_SESSION['s_sysmsg'] .= sprintf("<font color='yellow'>������٤Υǡ���������ޤ���<br>%s��%s</font>", format_date($d_start), format_date($d_end) );
    header('Location:http://10.1.3.252/industry/material_new/materialNewSales_form.php');    // ľ���θƽи������
    exit();
} else {
    $num = count($field);       // �ե�����ɿ�����
    $n_kingaku = 0;
    $d_kingaku = 0;
    for ($r=0; $r<$rows; $r++) {
        if ($res[$r][18] != 0) {
            $new_sales = UROUND(($res[$r][6] * $res[$r][18]), 0);
        } elseif ($res[$r][22] != 0) {
            $new_sales = UROUND(($res[$r][6] * $res[$r][22]), 0);
        } else {
            $new_sales = 0;
        }
        $n_kingaku += $new_sales;
    }
    $d_kingaku = $n_kingaku - $t_kingaku;
}
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
                                WHEN trim(u.�ײ��ֹ�)='' THEN '---'         --NULL�Ǥʤ��ƥ��ڡ�������ޤäƤ�����Ϥ��졪
                                ELSE u.�ײ��ֹ�
                            END                     as �ײ��ֹ�,        -- 2
                            CASE
                                WHEN trim(u.assyno) = '' THEN '---'
                                ELSE u.assyno
                            END                     as �����ֹ�,        -- 3
                            CASE
                                WHEN trim(substr(m.midsc,1,38)) = '' THEN '&nbsp;'
                                WHEN m.midsc IS NULL THEN '&nbsp;'
                                ELSE substr(m.midsc,1,38)
                            END             as ����̾,                  -- 4
                            CASE
                                WHEN trim(u.���˾��)='' THEN '--'         --NULL�Ǥʤ��ƥ��ڡ�������ޤäƤ�����Ϥ��졪
                                ELSE u.���˾��
                            END                     as ����,            -- 5
                            u.����          as ����,                    -- 6
                            u.ñ��          as ����ñ��,                -- 7
                            Uround(u.���� * u.ñ��, 0) as ���,         -- 8
                            sum_price + Uround((m_time + g_time) * assy_rate, 2) + Uround(a_time * a_rate, 2)
                                                    as �������,        -- 9
                            CASE
                                WHEN (sum_price + Uround((m_time + g_time) * assy_rate, 2) + Uround(a_time * a_rate, 2)) <= 0 THEN 0
                                ELSE Uround(u.ñ�� / (sum_price + Uround((m_time + g_time) * assy_rate, 2) + Uround(a_time * a_rate, 2)), 2)
                            END                     as ����Ψ,            --10
                            (select sum_price + Uround((m_time + g_time) * assy_rate, 2) + Uround(a_time * a_rate, 2) from material_cost_header where assy_no=u.assyno AND regdate<=�׾��� order by assy_no DESC, regdate DESC limit 1)
                                                    AS �������2,       --11
                            (select Uround(u.ñ�� / (sum_price + Uround((m_time + g_time) * assy_rate, 2) + Uround(a_time * a_rate, 2)), 2) from material_cost_header where assy_no=u.assyno AND regdate<=�׾��� order by assy_no DESC, regdate DESC limit 1)
                                                    AS ����Ψ,            --12
                            (select plan_no from material_cost_header where assy_no=u.assyno AND regdate<=�׾��� order by assy_no DESC, regdate DESC limit 1)
                                                    AS �ײ��ֹ�2,       --13
                            (SELECT sum(lot_cost) FROM parts_cost_history WHERE parts_no=u.assyno AND as_regdate<�׾��� AND lot_no=1 AND vendor!='88888' GROUP BY as_regdate, reg_no ORDER BY as_regdate DESC, reg_no DESC limit 1)
                                                    AS ���ʺ�����,      --14
                            (SELECT reg_no FROM parts_cost_history WHERE parts_no=u.assyno AND as_regdate<�׾��� AND lot_no=1 AND vendor!='88888' GROUP BY as_regdate, reg_no ORDER BY as_regdate DESC, reg_no DESC limit 1)
                                                    AS ñ����Ͽ�ֹ�,    --15
                            (SELECT cost_new FROM sales_price_new WHERE parts_no=u.assyno AND cost_ym={$target_ym} limit 1)
                                                    AS ������,        --16
                            (SELECT credit_per FROM sales_price_new WHERE parts_no=u.assyno AND cost_ym={$target_ym} limit 1)
                                                    AS ��Ψ,            --17
                            (SELECT new_price FROM sales_price_new WHERE parts_no=u.assyno AND cost_ym={$target_ym} limit 1)
                                                    AS �������,        --18
                            (SELECT plan_no FROM sales_price_new WHERE parts_no=u.assyno AND cost_ym={$target_ym} limit 1)
                                                    AS ������ײ�,    --19
                            CASE
                                WHEN (SELECT cost_new FROM sales_price_new WHERE parts_no=u.assyno AND plan_no=u.�ײ��ֹ� limit 1) IS NULL THEN (SELECT cost_new FROM sales_price_new WHERE parts_no=u.assyno AND cost_ym={$second_ym} limit 1)
                                ELSE (SELECT cost_new FROM sales_price_new WHERE parts_no=u.assyno AND plan_no=u.�ײ��ֹ� limit 1)
                            END                     AS ������2,       -- 20
                            CASE
                                WHEN (SELECT credit_per FROM sales_price_new WHERE parts_no=u.assyno AND plan_no=u.�ײ��ֹ� limit 1) IS NULL THEN (SELECT credit_per FROM sales_price_new WHERE parts_no=u.assyno AND cost_ym={$second_ym} limit 1)
                                ELSE (SELECT credit_per FROM sales_price_new WHERE parts_no=u.assyno AND plan_no=u.�ײ��ֹ� limit 1)
                            END                     AS ��Ψ2,           -- 21
                            CASE
                                WHEN (SELECT new_price FROM sales_price_new WHERE parts_no=u.assyno AND plan_no=u.�ײ��ֹ� limit 1) IS NULL THEN (SELECT new_price FROM sales_price_new WHERE parts_no=u.assyno AND cost_ym={$second_ym} limit 1)
                                ELSE (SELECT new_price FROM sales_price_new WHERE parts_no=u.assyno AND plan_no=u.�ײ��ֹ� limit 1)
                            END                     AS �������2,       -- 22
                            CASE
                                WHEN (SELECT plan_no FROM sales_price_new WHERE parts_no=u.assyno AND plan_no=u.�ײ��ֹ� limit 1) IS NULL THEN (SELECT plan_no FROM sales_price_new WHERE parts_no=u.assyno AND cost_ym={$second_ym} limit 1)
                                ELSE (SELECT plan_no FROM sales_price_new WHERE parts_no=u.assyno AND plan_no=u.�ײ��ֹ� limit 1)
                            END                     AS ������ײ�2    -- 23
                      from
                            hiuuri as u
                      left outer join
                            assembly_schedule as a
                      on u.�ײ��ֹ�=a.plan_no
                      left outer join
                            miitem as m
                      on u.assyno=m.mipn
                      left outer join
                            material_cost_header as mate
                      on u.�ײ��ֹ�=mate.plan_no
                      LEFT OUTER JOIN
                            sales_parts_material_history AS pmate
                      ON (u.assyno=pmate.parts_no AND u.�׾���=pmate.sales_date)
                      %s
                      order by �׾���, assyno
                      offset %d limit %d
                      ", $search, $offset, PAGE);   // ���� $search �Ǹ���
$res   = array();
$field = array();
if (($rows = getResultWithField3($query, $field, $res)) <= 0) {
    $_SESSION['s_sysmsg'] .= sprintf("<font color='yellow'>������٤Υǡ���������ޤ���<br>%s��%s</font>", format_date($d_start), format_date($d_end) );
    header('Location:http://10.1.3.252/industry/material_new/materialNewSales_form.php');    // ľ���θƽи������
    exit();
} else {
    $num = count($field);       // �ե�����ɿ�����
    for ($r=0; $r<$rows; $r++) {
        $res[$r][4] = mb_convert_kana($res[$r][4], 'ka', 'EUC-JP');   // ���ѥ��ʤ�Ⱦ�ѥ��ʤإƥ���Ū�˥���С���
    }
}
//////////// ɽ�������
$ft_kingaku = number_format($t_kingaku);                    // ���头�ȤΥ���ޤ��ղ�
$ft_ken     = number_format($t_ken);
$ft_kazu    = number_format($t_kazu);
$f_d_start  = format_date($d_start);                        // ���դ� / �ǥե����ޥå�
$f_d_end    = format_date($d_end);
$fn_kingaku = number_format($n_kingaku);                    // ���头�ȤΥ���ޤ��ղ�
$fd_kingaku = number_format($d_kingaku);                    // ���头�ȤΥ���ޤ��ղ�
$menu->set_caption("<u>����=<font color='red'>{$div_name}</font>��{$f_d_start}��{$f_d_end}����׷��={$ft_ken}����׶��={$ft_kingaku}����׿���={$ft_kazu}<u>��<font color='red'>������={$fn_kingaku}</font>��<font color='red'>����={$fd_kingaku}</font>");

// ��������CSV�����Ѥν������
// �ե�����̾�����ܸ��Ĥ���ȼ����Ϥ��ǥ��顼�ˤʤ�Τǰ���ѻ����ѹ�
if ($div == 'D') {                  // ���ץ�ɸ����
    $act_name = 'C-hyou';
} elseif ($div == 'L') {            // ��˥�����
    $act_name = 'L-all';
} elseif ($div == 'N') {            // ��˥��Τ�
    $act_name = 'L-hyou';
} elseif ($div == 'B') {            // �Х����
    $act_name = 'L-bimor';
} elseif ($div == 'T') {            // �ġ���
    $act_name = 'Tool';
}

// SQL�Υ������������ܸ��ѻ����ѹ���'�⥨�顼�ˤʤ�Τ�/�˰���ѹ�
$csv_search = str_replace('�׾���','keidate',$search);
$csv_search = str_replace('������','jigyou',$csv_search);
$csv_search = str_replace('\'','/',$csv_search);

// CSV�ե�����̾������ʳ���ǯ��-��λǯ��-��������
$outputFile = $d_start . '-' . $d_end . '-' . $act_name . '.csv';

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
<?php if (PAGE > 25) { ?>
<body onLoad='set_focus()'>
<?php } else { ?>
<body onLoad='set_focus()' style='overflow-y:hidden;'>
<?php } ?>
    <center>
<?php echo $menu->out_title_border()?>
        
        <!----------------- ������ ���� ���� �Υե����� ---------------->
        <table width='100%' border='0' cellspacing='0' cellpadding='0'>
            <form name='page_form' method='post' action='<?php echo $menu->out_self() ?>'>
                <tr>
                    <td align='left'>
                        <table align='left' border='3' cellspacing='0' cellpadding='0'>
                            <td align='left'>
                                <input class='pt10b' type='submit' name='backward' value='����'>
                            </td>
                        </table>
                    </td>
                    <td nowrap align='center' class='caption_font'>
                        <?php echo $menu->out_caption(), "\n" ?>
                        <a href='materialNewSales_csv.php?csvname=<?php echo $outputFile ?>&targetym=<?php echo $target_ym ?>&csvsearch=<?php echo $csv_search ?>&secondym=<?php echo $second_ym ?>'>
                        CSV����
                        </a>
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
                    <th class='winbox' nowrap><?php echo $field[$i] ?></th>
                <?php
                }
                ?>
                    <th class='winbox' nowrap><?php echo $field[16] ?></th>
                    <th class='winbox' nowrap><?php echo $field[17] ?></th>
                    <th class='winbox' nowrap><?php echo $field[18] ?></th>
                    <th class='winbox' nowrap>������</th>
                    <th class='winbox' nowrap>����</th>
                </tr>
            </thead>
            <tfoot>
                <!-- ���ߤϥեå����ϲ���ʤ� -->
            </tfoot>
            <tbody>
                <?php
                for ($r=0; $r<$rows; $r++) {
                    $recNo = ($offset + $r);
                    if ($session->get_local('recNo') == $recNo) {
                        echo "<tr style='background-color:#ffffc6;'>\n";
                    } else {
                        echo "<tr>\n";
                    }
                    echo "    <td class='winbox' nowrap align='right'><div class='pt10b'>" . ($r + $offset + 1) . "</div></td>    <!-- �ԥʥ�С���ɽ�� -->\n";
                    for ($i=0; $i<$num; $i++) {         // �쥳���ɿ�ʬ���֤�
                        //if ($i >= 11) if ($div != 'S') break;
                        // <!--  bgcolor='#ffffc6' �������� --> 
                        if ($div != 'S') { // ������ �ʳ��ʤ�
                            switch ($i) {
                            case 0:     // �׾���
                                echo "<td class='winbox' nowrap align='center'><div class='pt9'>" . format_date($res[$r][$i]) . "</div></td>\n";
                                break;
                            case 3:
                                if ($res[$r][1] == '����') {
                                    echo "<td class='winbox' nowrap align='center'><a class='pt9' href='JavaScript:baseJS.Ajax(\"{$menu->out_self()}?recNo={$recNo}\");location.replace(\"{$menu->out_action('�����������')}?assy=", urlencode($res[$r][$i]), "&material=1&plan_no=", urlencode($res[$r][2]), "\")' target='application' style='text-decoration:none;'>{$res[$r][$i]}</a></td>\n";
                                } else {
                                    echo "<td class='winbox' nowrap align='center'><div class='pt9'>", $res[$r][$i], "</div></td>\n";
                                }
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
                                                <a class='pt9' href='JavaScript:baseJS.Ajax(\"{$menu->out_self()}?recNo={$recNo}\");location.replace(\"", $menu->out_action('�������Ȳ�'), "?plan_no={$res[$r][13]}&assy_no={$res[$r][3]}\")' target='application' style='text-decoration:none; color:brown;'>"
                                                , number_format($res[$r][11], 2), "</a></td>\n";
                                    } elseif ($res[$r][14]) {   // ���ʤκ����������å�����ɽ������
                                        echo "<td class='winbox' nowrap width='60' align='right'>
                                                <a class='pt9' href='JavaScript:baseJS.Ajax(\"{$menu->out_self()}?recNo={$recNo}\");location.replace(\"", $menu->out_action('ñ����Ͽ�Ȳ�'), "?parts_no=", urlencode($res[$r][3]), "& reg_no={$res[$r][15]}\")' target='application' style='text-decoration:none;'>"
                                                , number_format($res[$r][14], 2), "</a></td>\n";
                                    } else {
                                        echo "<td class='winbox' nowrap width='60' align='right'><div class='pt9'>-</div></td>\n";
                                    }
                                } else {
                                    echo "<td class='winbox' nowrap width='60' align='right'>
                                            <a class='pt9' href='JavaScript:baseJS.Ajax(\"{$menu->out_self()}?recNo={$recNo}\");location.replace(\"", $menu->out_action('�������Ȳ�'), "?plan_no={$res[$r][2]}&assy_no={$res[$r][3]}\")' target='application' style='text-decoration:none;'>"
                                            , number_format($res[$r][$i], 2), "</a></td>\n";
                                }
                                break;
                            case 10:    // Ψ(�������)
                                if ($res[$r][$i] > 0 && ($res[$r][$i] < 1.13)) {
                                    echo "<td class='winbox' nowrap width='40' align='right'><font class='pt9' color='red'>", number_format($res[$r][$i], 2), "</font></td>\n";
                                } elseif ($res[$r][$i] <= 0) {
                                    if ($res[$r][12]) {
                                        echo "<td class='winbox' nowrap width='40' align='right'><div class='pt9'>", number_format($res[$r][12], 2), "</div></td>\n";
                                    } elseif ($res[$r][14]) {
                                        if ( UROUND(($res[$r][7]/$res[$r][14]), 2) < 1.13 ) {   // �ֻ�ɽ����ʬ��
                                            echo "<td class='winbox' nowrap width='40' align='right'><div class='pt9' style='color:red;'>", number_format($res[$r][7]/$res[$r][14]*100, 2), "</div></td>\n";
                                        } elseif ( UROUND(($res[$r][7]/$res[$r][14]), 2) > 1.13 ) {
                                            echo "<td class='winbox' nowrap width='40' align='right'><div class='pt9' style='color:blue;'>", number_format($res[$r][7]/$res[$r][14]*100, 2), "</div></td>\n";
                                        } else {
                                            echo "<td class='winbox' nowrap width='40' align='right'><div class='pt9'>", number_format($res[$r][7]/$res[$r][14]*100, 2), "</div></td>\n";
                                        }
                                    } else {
                                        echo "<td class='winbox' nowrap width='40' align='right'><div class='pt9'>-</div></td>\n";
                                    }
                                } elseif ($res[$r][$i] > 1.13 ){
                                    echo "<td class='winbox' nowrap width='40' align='right'><font class='pt9' color='blue'>", number_format($res[$r][$i], 2), "</font></td>\n";
                                } else {
                                    echo "<td class='winbox' nowrap width='40' align='right'><div class='pt9'>", number_format($res[$r][$i], 2), "</div></td>\n";
                                }
                                break;
                            case 11:
                                break;
                            case 12:
                                break;
                            case 13:
                                break;
                            case 14:
                                break;
                            case 15:
                                break;
                            case 16:    // ����������
                                if ($res[$r][$i] != 0) {
                                    echo "<td class='winbox' nowrap width='60' align='right'>
                                            <a class='pt9' href='JavaScript:baseJS.Ajax(\"{$menu->out_self()}?recNo={$recNo}\");location.replace(\"", $menu->out_action('�������Ȳ�'), "?plan_no={$res[$r][19]}&assy_no={$res[$r][3]}\")' target='application' style='text-decoration:none;'>"
                                            , number_format($res[$r][$i], 2), "</a></td>\n";
                                } elseif($res[$r][20] != 0) {
                                    echo "<td class='winbox' nowrap width='60' align='right'>
                                            <a class='pt9' href='JavaScript:baseJS.Ajax(\"{$menu->out_self()}?recNo={$recNo}\");location.replace(\"", $menu->out_action('�������Ȳ�'), "?plan_no={$res[$r][23]}&assy_no={$res[$r][3]}\")' target='application' style='text-decoration:none; color:brown;'>"
                                            , number_format($res[$r][20], 2), "</a></td>\n";
                                } else {
                                    echo "<td class='winbox' nowrap width='60' align='right'><div class='pt9'>-</div></td>\n";
                                }
                                break;
                            case 17:    // ��Ψ
                                if ($res[$r][$i] != '') {
                                    echo "<td class='winbox' nowrap align='center'><div class='pt9'>", $res[$r][$i], "</div></td>\n";
                                } elseif ($res[$r][21] != '') {
                                    echo "<td class='winbox' nowrap align='center'><div class='pt9'>", $res[$r][21], "</div></td>\n";
                                } else {
                                    echo "<td class='winbox' nowrap align='center'><div class='pt9'>-</div></td>\n";
                                }
                                break;
                            case 18:    // �������
                                if ($res[$r][$i] != 0) {
                                    echo "<td class='winbox' nowrap width='60' align='right'><div class='pt9'>" . number_format($res[$r][$i], 2) . "</div></td>\n";
                                } elseif ($res[$r][22] != 0) {
                                    echo "<td class='winbox' nowrap width='60' align='right'><div class='pt9'>" . number_format($res[$r][22], 2) . "</div></td>\n";
                                } else {
                                    echo "<td class='winbox' nowrap align='center'><div class='pt9'>-</div></td>\n";
                                }
                                break;
                            case 19:
                                break;
                            case 20:
                                break;
                            case 21:
                                break;
                            case 22:
                                break;
                            case 23:
                                break;
                            default:    // ����¾
                                echo "<td class='winbox' nowrap align='center'><div class='pt9'>", $res[$r][$i], "</div></td>\n";
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
                            case 8:     // ���
                                echo "<td class='winbox' nowrap width='70' align='right'><div class='pt9'>" . number_format($res[$r][$i], 0) . "</div></td>\n";
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
                                    echo "<td class='winbox' nowrap width='40' align='right'><font class='pt9' color='red'>" . number_format($res[$r][$i], 2) . "</font></td>\n";
                                } elseif ($res[$r][$i] <= 0) {
                                    echo "<td class='winbox' nowrap width='40' align='right'><div class='pt9'>-</div></td>\n";
                                } else {
                                    echo "<td class='winbox' nowrap width='40' align='right'><div class='pt9'>" . number_format($res[$r][$i], 2) . "</div></td>\n";
                                }
                                break;
                            case 12:    // �������
                                if ($res[$r][$i] == 0) {
                                    // echo "<td nowrap width='60' align='right' class='pt9'>" . number_format($res[$r][$i], 2) . "</td>\n";
                                    echo "<td class='winbox' nowrap width='60' align='right'><div class='pt9'>-</div></td>\n";
                                } else {
                                    echo "<td class='winbox' nowrap width='60' align='right'>
                                            <a class='pt9' href='JavaScript:baseJS.Ajax(\"{$menu->out_self()}?recNo={$recNo}\");location.replace(\"", $menu->out_action('�������Ȳ�'), "?plan_no={$res[$r][2]}&assy_no={$res[$r][3]}\")' target='application' style='text-decoration:none;'>"
                                            , number_format($res[$r][$i], 2), "</a></td>\n";
                                }
                                break;
                            case 13:    // Ψ(�������)
                                if ($res[$r][$i] > 0 && ($res[$r][$i] < 1.13)) {
                                    echo "<td class='winbox' nowrap width='40' align='right'><font class='pt9' color='red'>" . number_format($res[$r][$i], 2) . "</font></td>\n";
                                } elseif (($res[$r][$i] > 1.13)) {
                                    echo "<td class='winbox' nowrap width='40' align='right'><font class='pt9' color='blue'>" . number_format($res[$r][$i], 2) . "</font></td>\n";
                                } elseif ($res[$r][$i] <= 0) {
                                    echo "<td class='winbox' nowrap width='40' align='right'><div class='pt9'>-</div></td>\n";
                                } else {
                                    echo "<td class='winbox' nowrap width='40' align='right'><div class='pt9'>" . number_format($res[$r][$i], 2) . "</div></td>\n";
                                }
                                break;
                            default:    // ����¾
                                echo "<td class='winbox' nowrap align='center'><div class='pt9'>" . $res[$r][$i] . "</div></td>\n";
                            }
                        }
                        // <!-- ����ץ�<td rowspan='2' colspan='3' width='200' align='center' class='pt10b' bgcolor='#ffffc6'>  </td> -->
                    }
                    if ($res[$r][18] != 0) {
                        $new_sales = UROUND(($res[$r][6] * $res[$r][18]), 0);
                    } elseif ($res[$r][22] != 0) {
                        $new_sales = UROUND(($res[$r][6] * $res[$r][22]), 0);
                    } else {
                        $new_sales = 0;
                    }
                    $sales_dif = $new_sales - $res[$r][8];
                    // ������ڶ��
                    echo "<td class='winbox' nowrap width='70' align='right'><div class='pt9'>" . number_format($new_sales, 0) . "</div></td>\n";
                    // ����
                    echo "<td class='winbox' nowrap width='70' align='right'><div class='pt9'>" . number_format($sales_dif, 0) . "</div></td>\n";
                    echo "</tr>\n";
                }
                ?>
            </tbody>
        </table>
            </td></tr>
        </table> <!----------------- ���ߡ�End ------------------>
        <table style='border: 2px solid #0A0;'>
            <tr><td align='center' class='pt11b' tabindex='1' id='note'>��������Ŀ�ɽ���ϴ��ǯ�����Ͽ������ʪ�ǡ��㿧�ϴ��ǯ��Ǥ�̵����������ǯ�����κǽ�ǯ�����Ͽ��ɽ��</td></tr>
        </table>
    </center>
</body>
<?php echo $menu->out_alert_java()?>
</html>
<?php
// ob_end_flush();                 // ���ϥХåե���gzip���� END
?>
