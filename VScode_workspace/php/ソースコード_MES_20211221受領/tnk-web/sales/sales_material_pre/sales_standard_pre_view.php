<?php
//////////////////////////////////////////////////////////////////////////////
// ��� ���� ɸ�������� �Ȳ�                                                //
// Copyright (C) 2005-2007 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp      //
// Changed history                                                          //
// 2005/06/03 Created   sales_standard_pre_view.php                         //
//////////////////////////////////////////////////////////////////////////////
// ini_set('error_reporting', E_ALL);          // E_ALL='2047' debug ��
// ini_set('display_errors', '1');             // Error ɽ�� ON debug �� ��꡼���女����
// ini_set('zend.ze1_compatibility_mode', '1');    // zend 1.X ����ѥ� php4�θߴ��⡼��
// ini_set('implicit_flush', 'off');           // echo print �� flush �����ʤ�(�٤��ʤ뤿��) CLI CGI��
// ini_set('max_execution_time', 1200);        // ����¹Ի���=20ʬ CLI CGI��
ob_start('ob_gzhandler');                   // ���ϥХåե���gzip����
session_start();                            // ini_set()�μ��˻��ꤹ�뤳�� Script �Ǿ��

require_once ('../../function.php');        // define.php �� pgsql.php �� require_once ���Ƥ���
require_once ('../../tnk_func.php');        // TNK �˰�¸������ʬ�δؿ��� require_once ���Ƥ���
require_once ('../../MenuHeader.php');      // TNK ������ menu class
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
$menu->set_site( 1, 14);                    // site_index=01(����˥塼) site_id=14(ɸ�������Ȳ�)
////////////// �꥿���󥢥ɥ쥹����
// $menu->set_RetUrl(SYS_MENU);                // �̾�ϻ��ꤹ��ɬ�פϤʤ�
//////////// �����ȥ�̾(�������Υ����ȥ�̾�ȥե�����Υ����ȥ�̾)
$div = $_SESSION['standard_div'];
if ($div == 'A') {                  // ����
    $menu->set_title('������� ���� �Ȳ�');
} elseif ($div == 'C') {            // ���ץ�����
    $menu->set_title('������� ���ץ����� �Ȳ�');
} elseif ($div == 'CH') {           // ���ץ�ɸ����
    $menu->set_title('������� ���ץ�ɸ���� �Ȳ�');
} elseif ($div == 'CS') {           // ���ץ�����
    $menu->set_title('������� ����������� �Ȳ�');
} elseif ($div == 'L') {            // ��˥�ɸ����
    $menu->set_title('������� ��˥����� �Ȳ�');
} elseif ($div == 'LL') {           // ���ץ�ɸ����
    $menu->set_title('������� ��˥��Τ� �Ȳ�');
} elseif ($div == 'LB') {           // ���ץ�����
    $menu->set_title('������� �Х���� �Ȳ�');
} else {
    $menu->set_title('������� ����Ψʬ������ �Ȳ�');
}
//////////// �ƽ����action̾�ȥ��ɥ쥹����
$menu->set_action('�������Ȳ�',   INDUST . 'material/materialCost_view.php');
$menu->set_action('���ӹ����Ȳ�',   INDUST . 'assembly/assembly_time_show/assembly_time_show_Main.php');
$menu->set_action('������������', INDUST . 'material/materialCost_view_assy.php');
$menu->set_retGET('sum_exec', 'on');
$menu->set_retGET('page_keep', 'on');

//////////// JavaScript Stylesheet File ��ɬ���ɤ߹��ޤ���
$uniq = uniqid('target');

//////////// �����Υ��å����ǡ�����¸   ���ǡ����Ǥ�ڤ����뤿��
if (! (isset($_POST['forward']) || isset($_POST['backward']) || isset($_REQUEST['page_keep'])) ) {
    $session->add_local('recNo', '-1');         // 0�쥳���ɤǥޡ�����ɽ�����Ƥ��ޤ�������б�
    if (isset($_REQUEST['ym_p'])) {
        $last_day = last_day(substr($_REQUEST['ym_p'], 0, 4), substr($_REQUEST['ym_p'], 4, 2));
        $d_start = ($_REQUEST['ym_p'] . '01');
        $d_end   = ($_REQUEST['ym_p'] . $last_day);
    } else {
        $d_start = $_SESSION['standard_d_start'];
        $d_end   = $_SESSION['standard_d_end'];
    }
    $_SESSION['st_view_d_start'] = $d_start;
    $_SESSION['st_view_d_end']   = $d_end;
    $uri_passwd = $_SESSION['s_uri_passwd'];
    $div        = $_SESSION['standard_div'];
    $where_div  = $_SESSION['standard_where_div'];
    $kubun      = $_SESSION['standard_kubun'];
    $uri_ritu   = 52;       // ��ƥ����ѹ�
    $assy_no    = $_SESSION['standard_assy_no'];
    
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
                    sum((sum_price + Uround((m_time + g_time) * assy_rate, 2) + Uround(a_time * a_rate, 2)) * ����)
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
    if (isset($_REQUEST['standard_view1'])) {
        $search = $_SESSION['standard_condition1'];       // ��
        $view_name = "��={$_SESSION['standard_lower_uri_ritu']}% �� {$_SESSION['standard_upper_uri_ritu']}%";
    } elseif (isset($_REQUEST['standard_view2'])) {
        $search = $_SESSION['standard_condition2'];       // ��
        $view_name = "��={$_SESSION['standard_lower_mate_ritu']}% �� {$_SESSION['standard_upper_mate_ritu']}%";
    } elseif (isset($_REQUEST['standard_view3'])) {
        $search = $_SESSION['standard_condition3'];       // ��
        $view_name = "��={$_SESSION['standard_lower_equal_ritu']}% �� {$_SESSION['standard_upper_equal_ritu']}%";
    } elseif (isset($_REQUEST['standard_view4'])) {
        $search = $_SESSION['standard_condition4'];       // ��
        $view_name = '����¾(�������̤��Ͽ��)';
    } elseif (isset($_REQUEST['standard_view'])) {
        // $search = $_SESSION['standard_condition'];
        $search = '';                                   // ɸ��������
        $view_name = '����';
    } else {
        // $search = $_SESSION['standard_condition'];
        $search = '';                                   // ɸ��������
        $view_name = '����';
    }
    $_SESSION['standard_view_name'] = $view_name;
    $where_assy_no = $_SESSION['standard_where_assy_no'];
    // $where = $_SESSION['standard_where'];
    if ($div == 'CH') { // ɸ���ʤʤ�
        $where = "
            where
            kanryou>={$d_start} and kanryou<={$d_end} and {$where_div}
            and
            note15 not like 'SC%' {$where_assy_no}
        ";
        //  �׾���>={$d_start} and �׾���<={$d_end} and datatype={$kubun} and {$where_div}
    } elseif ($div == 'CS') { // ������ʤ�
        $where = "
            where
            kanryou>={$d_start} and kanryou<={$d_end} and {$where_div}
            and
            note15 like 'SC%' {$where_assy_no}
        ";
        //  �׾���>={$d_start} and �׾���<={$d_end} and datatype={$kubun} and {$where_div}
    } else {            // ���Ρ���˥����Ρ���˥��Τߡ��Х����
        $where = "
            where
            kanryou>={$d_start} and kanryou<={$d_end} and {$where_div}
            {$where_assy_no}
        ";
        //  �׾���>={$d_start} and �׾���<={$d_end} and datatype={$kubun} and {$where_div}
    }
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
$div        = $_SESSION['standard_div'];
$where_div  = $_SESSION['standard_where_div'];
$d_start = $_SESSION['st_view_d_start'];
$d_end   = $_SESSION['st_view_d_end'];
// $d_start    = $_SESSION['standard_d_start'];
// $d_end      = $_SESSION['standard_d_end'];
$kubun      = $_SESSION['standard_kubun'];
$uri_ritu   = 52;   // ��ƥ����ѹ�
$assy_no    = $_SESSION['standard_assy_no'];
$search     = $_SESSION['sales_search'];

///// ���̾������
$view_name = $_SESSION['standard_view_name'];

//////////// ɽ�����Υǡ���ɽ���ѤΥ���ץ� Query & �����
$query = sprintf("select
                            a.kanryou         as ��λͽ����,                  -- 0
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
                                WHEN trim(a.plan_no)='' THEN '---'         --NULL�Ǥʤ��ƥ��ڡ�������ޤäƤ�����Ϥ��졪
                                ELSE a.plan_no
                            END                     as �ײ��ֹ�,        -- 2
                            CASE
                                WHEN trim(a.parts_no) = '' THEN '---'
                                ELSE a.parts_no
                            END                     as �����ֹ�,        -- 3
                            CASE
                                WHEN trim(substr(m.midsc,1,25)) = '' THEN '-----'
                                ELSE substr(m.midsc,1,38)
                            END             as ����̾,                  -- 4
                            CASE
                                WHEN trim(u.���˾��)='' THEN '--'         --NULL�Ǥʤ��ƥ��ڡ�������ޤäƤ�����Ϥ��졪
                                ELSE u.���˾��
                            END                     as ����,            -- 5
                            (a.plan - a.cut_plan)   as ����,            -- 6
                            u.ñ��          as ����ñ��,                -- 7
                            Uround((a.plan - a.cut_plan) * u.ñ��, 0) as ���,         -- 8
                            sum_price + Uround((m_time + g_time) * assy_rate, 2) + Uround(a_time * a_rate, 2)
                                                    as �������,        -- 9
                            CASE
                                WHEN (sum_price + Uround((m_time + g_time) * assy_rate, 2) + Uround(a_time * a_rate, 2)) <= 0 THEN 0
                                ELSE Uround(u.ñ�� / (sum_price + Uround((m_time + g_time) * assy_rate, 2) + Uround(a_time * a_rate, 2)), 3) * 100
                            END                     as Ψ��,            --10
                            (select sum_price + Uround((m_time + g_time) * assy_rate, 2) + Uround(a_time * a_rate, 2) from material_cost_header where assy_no=a.parts_no order by assy_no DESC, regdate DESC limit 1)
                                                    AS �������2,       --11
                            (select Uround(s.price / (sum_price + Uround((m_time + g_time) * assy_rate, 2) + Uround(a_time * a_rate, 2)), 3) * 100 from material_cost_header where assy_no=a.parts_no order by assy_no DESC, regdate DESC limit 1)
                                                    AS Ψ��,            --12
                            (select plan_no from material_cost_header where assy_no=u.assyno order by assy_no DESC, regdate DESC limit 1)
                                                    AS �ײ��ֹ�2,       --13
                            s.price                 AS ����ñ��,        --14
                            Uround((a.plan - a.cut_plan) * s.price, 0) AS ���2            --15
                      from
                            assembly_schedule as a
                      left outer join
                            hiuuri as u
                      on u.�ײ��ֹ�=a.plan_no
                      left outer join
                            miitem as m
                      on a.parts_no=m.mipn
                      left outer join
                            material_cost_header as mate
                      on a.plan_no=mate.plan_no
                      left outer join
                           sales_price_nk as s
                      on  a.parts_no=s.parts_no
                      %s AND a.parts_no NOT LIKE '999999999' AND (a.plan - a.cut_plan) != 0
                      AND assy_site = '01111'
                      AND ((Uround(u.ñ�� / (sum_price + Uround((m_time + g_time) * assy_rate, 2) + Uround(a_time * a_rate, 2)), 3) * 100 != 0)
                      OR (select Uround(s.price / (sum_price + Uround((m_time + g_time) * assy_rate, 2) + Uround(a_time * a_rate, 2)), 3) * 100 from material_cost_header where assy_no=a.parts_no order by assy_no DESC, regdate DESC limit 1) !=0)
                      order by a.kanryou, a.parts_no
                      ", $search);   // ���� $search �Ǹ���
$res_sum   = array();
$field_sum = array();
if (($rows_sum = getResultWithField3($query, $field_sum, $res_sum)) <= 0) {
    $_SESSION['s_sysmsg'] = '��׶�ۤμ����˼��Ԥ��ޤ�����';
    header('Location: ' . H_WEB_HOST . $menu->out_RetUrl() . '?sum_exec=on&page_keep=on');    // ľ���θƽи������
    exit();
} else {
    $t_kazu    = 0;
    $t_kingaku = 0;  // �����
    $t_zai     = 0;
    $t_ken     = $rows_sum;
    for ($r=0; $r<$rows_sum; $r++) {
        $t_kazu    += $res_sum[$r][6];
        if ($res_sum[$r][8] == 0) {
            $t_kingaku += $res_sum[$r][15];           // �����2
        } else {
            $t_kingaku += $res_sum[$r][8];            // �����
        }
        if ($res_sum[$r][9] == 0) {
            $t_zai     += $res_sum[$r][11] * $res_sum[$r][6];           // �������2 * ����
        } else {
            $t_zai     += $res_sum[$r][9] * $res_sum[$r][6];            // ������� * ����
        }
    }
}

//////////// ɽ�������
$ft_kingaku = number_format($t_kingaku);                    // ���头�ȤΥ���ޤ��ղ�
$ft_zai     = number_format($t_zai);                        // ���头�ȤΥ���ޤ��ղ�
$ft_ken     = number_format($t_ken);
$ft_kazu    = number_format($t_kazu);
$f_d_start  = format_date($d_start);                        // ���դ� / �ǥե����ޥå�
$f_d_end    = format_date($d_end);
if ($t_kingaku == 0) {
    $zai_ritu = '0.0';
} else {
    $zai_ritu   = number_format(Uround($t_zai / $t_kingaku * 100, 1), 1);   // ��� ����� ��Ψ
}
if ($t_zai == 0) {
    $kin_ritu = '0.0';
} else {
    $kin_ritu = number_format(Uround($t_kingaku / $t_zai * 100, 1), 1);   // ��� ���� ��Ψ
}
// $menu->set_caption("<u><font color='red'>{$view_name}</font>��{$f_d_start}��{$f_d_end}����׷��={$ft_ken}����׶��={$ft_kingaku}����׿���={$ft_kazu}<u>");
$menu->set_caption("
    <u><font color='red'>��{$view_name}</font>��{$f_d_start}��{$f_d_end}����׷��={$ft_ken}����׿���={$ft_kazu}��<br>��
    ���<font style='color:brown;'>����={$ft_kingaku}</font>�����<font style='color:blue;'>�������={$ft_zai}���������Ψ={$zai_ritu}%������ñ����Ψ={$kin_ritu}%��</font><u>
");

//////////// ���ǤιԿ�
if (isset($_SESSION['standard_sales_page'])) {
    define('PAGE', $_SESSION['standard_sales_page']);
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
                            a.kanryou         as ��λͽ����,                  -- 0
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
                                WHEN trim(a.plan_no)='' THEN '---'         --NULL�Ǥʤ��ƥ��ڡ�������ޤäƤ�����Ϥ��졪
                                ELSE a.plan_no
                            END                     as �ײ��ֹ�,        -- 2
                            CASE
                                WHEN trim(a.parts_no) = '' THEN '---'
                                ELSE a.parts_no
                            END                     as �����ֹ�,        -- 3
                            CASE
                                WHEN trim(substr(m.midsc,1,25)) = '' THEN '-----'
                                ELSE substr(m.midsc,1,38)
                            END             as ����̾,                  -- 4
                            CASE
                                WHEN trim(u.���˾��)='' THEN '--'         --NULL�Ǥʤ��ƥ��ڡ�������ޤäƤ�����Ϥ��졪
                                ELSE u.���˾��
                            END                     as ����,            -- 5
                            (a.plan - a.cut_plan)   as ����,            -- 6
                            u.ñ��          as ����ñ��,                -- 7
                            Uround((a.plan - a.cut_plan) * u.ñ��, 0) as ���,         -- 8
                            sum_price + Uround((m_time + g_time) * assy_rate, 2) + Uround(a_time * a_rate, 2)
                                                    as �������,        -- 9
                            CASE
                                WHEN (sum_price + Uround((m_time + g_time) * assy_rate, 2) + Uround(a_time * a_rate, 2)) <= 0 THEN 0
                                ELSE Uround(u.ñ�� / (sum_price + Uround((m_time + g_time) * assy_rate, 2) + Uround(a_time * a_rate, 2)), 3) * 100
                            END                     as Ψ��,            --10
                            (select sum_price + Uround((m_time + g_time) * assy_rate, 2) + Uround(a_time * a_rate, 2) from material_cost_header where assy_no=a.parts_no order by assy_no DESC, regdate DESC limit 1)
                                                    AS �������2,       --11
                            (select Uround(s.price / (sum_price + Uround((m_time + g_time) * assy_rate, 2) + Uround(a_time * a_rate, 2)), 3) * 100 from material_cost_header where assy_no=a.parts_no order by assy_no DESC, regdate DESC limit 1)
                                                    AS Ψ��,            --12
                            (select plan_no from material_cost_header where assy_no=u.assyno order by assy_no DESC, regdate DESC limit 1)
                                                    AS �ײ��ֹ�2,       --13
                            s.price                 AS ����ñ��,        --14
                            Uround((a.plan - a.cut_plan) * s.price, 0) AS ���2            --15
                      from
                            assembly_schedule as a
                      left outer join
                            hiuuri as u
                      on u.�ײ��ֹ�=a.plan_no
                      left outer join
                            miitem as m
                      on a.parts_no=m.mipn
                      left outer join
                            material_cost_header as mate
                      on a.plan_no=mate.plan_no
                      left outer join
                           sales_price_nk as s
                      on  a.parts_no=s.parts_no
                      %s AND a.parts_no NOT LIKE '999999999' AND (a.plan - a.cut_plan) != 0
                      AND assy_site = '01111'
                      AND ((Uround(u.ñ�� / (sum_price + Uround((m_time + g_time) * assy_rate, 2) + Uround(a_time * a_rate, 2)), 3) * 100 != 0)
                      OR (select Uround(s.price / (sum_price + Uround((m_time + g_time) * assy_rate, 2) + Uround(a_time * a_rate, 2)), 3) * 100 from material_cost_header where assy_no=a.parts_no order by assy_no DESC, regdate DESC limit 1) !=0)
                      order by a.kanryou, a.parts_no
                      offset %d limit %d
                      ", $search, $offset, PAGE);   // ���� $search �Ǹ���

$res   = array();
$field = array();
if (($rows = getResultWithField3($query, $field, $res)) <= 0) {
    $_SESSION['s_sysmsg'] .= sprintf("<font color='yellow'>������٤Υǡ���������ޤ���<br>%s��%s</font>", format_date($d_start), format_date($d_end) );
    header('Location: ' . H_WEB_HOST . $menu->out_RetUrl() . '?sum_exec=on');    // ľ���θƽи������
    exit();
} else {
    //$t_kazu    = $res_sum[0]['t_kazu'];
    //$t_kingaku = $res_sum[0]['t_kingaku'];  // �����
    //$t_zai     = $res_sum[0]['�������'];
    $num = count($field);       // �ե�����ɿ�����
    $t_ken     = $rows;
    for ($r=0; $r<$rows; $r++) {
        $res[$r][4] = mb_convert_kana($res[$r][4], 'ka', 'EUC-JP');   // ���ѥ��ʤ�Ⱦ�ѥ��ʤإƥ���Ū�˥���С���
        //$t_kazu    = $res_sum[0]['t_kazu'];
        //$t_kingaku = $res_sum[0]['t_kingaku'];  // �����
        //$t_zai     = $res_sum[0]['�������'];
    }
    $_SESSION['SALES_TEST'] = sprintf("order by a.kanryou offset %d limit %d", $offset, PAGE);
}

/////////// HTML Header ����Ϥ��ƥ���å��������
$menu->out_html_header();
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=EUC-JP">
<meta http-equiv="Content-Style-Type" content="text/css">
<title><?php echo $menu->out_title() ?></title>
<?php echo $menu->out_site_java()?>
<?php echo $menu->out_css()?>
<!--    �ե��������ξ��
<script language='JavaScript' src='template.js?<?php echo $uniq ?>'>
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
    // document.form_name.element_name.select();
}
/* ����������ɥ��ǳ��� */
function win_open(url, w, h)
{
    if (!w) w = 800;     // �����
    if (!h) h = 600;     // �����
    var left = (screen.availWidth  - w) / 2;
    var top  = (screen.availHeight - h) / 2;
    w -= 10; h -= 30;   // ��Ĵ����ɬ��
    window.open(url, '', 'width='+w+',height='+h+',resizable=yes,scrollbars=yes,status=no,toolbar=no,location=no,menubar=no,top='+top+',left='+left);
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
    color:              blue;
    text-decoration:    none;
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
<body onLoad='set_focus()' <?php if (PAGE <= 25) echo "style='overflow:hidden;'"?>>
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
                        if ($i >= 11) if ($div != 'S') break;
                        // <!--  bgcolor='#ffffc6' �������� --> 
                        if ($div != 'S') { // ������ �ʳ��ʤ�
                            switch ($i) {
                            case 0:     // �׾���
                                echo "<td class='winbox' nowrap align='center'><div class='pt9'>" . format_date($res[$r][$i]) . "</div></td>\n";
                                break;
                            case 2:    // �ײ��ֹ�
                                echo "<td class='winbox' nowrap align='center'><a class='pt9' href='javascript:win_open(\"{$menu->out_action('���ӹ����Ȳ�')}?targetPlanNo={$res[$r][$i]}&noMenu=yes\", 900, 600)'>" . $res[$r][$i] . "</a></td>\n";
                                break;
                            case 3:    // �����ֹ�
                                echo "<td class='winbox' nowrap align='center'><a class='pt9' href='JavaScript:baseJS.Ajax(\"{$menu->out_self()}?recNo={$recNo}\");location.replace(\"{$menu->out_action('������������')}?assy=", urlencode($res[$r][$i]), "&material=1&plan_no=", urlencode($res[$r][2]), "\")' target='_self'>" . $res[$r][$i] . "</a></td>\n";
                                break;
                            case 4:     // ����̾
                                echo "<td class='winbox' nowrap width='270' align='left'><div class='pt9'>" . $res[$r][$i] . "</div></td>\n";
                                break;
                            case 6:     // ����
                                echo "<td class='winbox' nowrap width='45' align='right'><div class='pt9'>" . number_format($res[$r][$i], 0) . "</div></td>\n";
                                break;
                            case 7:     // ����ñ��
                                if ($res[$r][$i] == 0) {
                                    echo "<td class='winbox' nowrap width='60' align='right'><div class='pt9'><font color='brown'>" . number_format($res[$r][14], 2) . "</font></div></td>\n";
                                } else {
                                    echo "<td class='winbox' nowrap width='60' align='right'><div class='pt9'>" . number_format($res[$r][$i], 2) . "</div></td>\n";
                                }
                                break;
                            case 8:     // ���
                                if ($res[$r][$i] == 0) {
                                    echo "<td class='winbox' nowrap width='70' align='right'><div class='pt9'><font color='brown'>" . number_format($res[$r][15], 0) . "</font></div></td>\n";
                                } else {
                                    echo "<td class='winbox' nowrap width='70' align='right'><div class='pt9'>" . number_format($res[$r][$i], 0) . "</div></td>\n";
                                }
                                break;
                            case 9:     // �������
                                if ($res[$r][$i] == 0) {
                                    if ($res[$r][11]) {
                                        echo "<td class='winbox' nowrap width='60' align='right'>
                                                <a class='pt9' href='JavaScript:baseJS.Ajax(\"{$menu->out_self()}?recNo={$recNo}\");location.replace(\"", $menu->out_action('�������Ȳ�'), "?plan_no={$res[$r][13]}&assy_no={$res[$r][3]}\")' target='application' style='text-decoration:none; color:brown;'>"
                                                , number_format($res[$r][11], 2), "</a></td>\n";
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
                                            <a class='pt9' href='JavaScript:baseJS.Ajax(\"{$menu->out_self()}?recNo={$recNo}\");location.replace(\"", $menu->out_action('�������Ȳ�'), "?plan_no={$res[$r][2]}&assy_no={$res[$r][3]}\")' target='application' style='text-decoration:none;'>"
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
<?php echo $menu->out_alert_java()?>
</html>
<?php
ob_end_flush();                 // ���ϥХåե���gzip���� END
?>
