<?php
//////////////////////////////////////////////////////////////////////////////
// ��� ����(����������) �Ȳ� ������                                      //
// Copyright (C) 2011 Norihisa.Ohya norihisa_ooya@nitto-kohki.co.jp         //
// Changed history                                                          //
// 2011/05/30 Created   material_compare_sale_view_product.php              //
// 2011/05/31 ���롼�ץ������ѹ���ȼ��SQLʸ���ѹ�                           //
// 2011/06/01 ��ʬ����Ū��1=�������ѹ��������������ɲ�                    //
// 2011/06/07 ɽ���Ԥ�������SQLʸ������                                     //
// 2011/06/13 �ƥǡ�����ʿ������׻�����ɽ��                                //
// 2011/06/14 ��������פ�ɽ������褦�ˤ�����                              //
// 2011/06/20 ��Ψʿ�Ѥ��ΨȽ����($power_rate)����˿�ʬ��               //
//            CSV���Ϥ��ɲ�(���ʷ�-����ǯ��-��λǯ��-����-���롼��)       //
// 2011/07/06 ���ʥ��롼�פ˥��ץ�ɸ��ȥ�˥�ɸ����ɲ�                    //
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
//$menu->set_site( 1, 11);                    // site_index=01(����˥塼) site_id=11(����������)
////////////// �꥿���󥢥ɥ쥹����
// $menu->set_RetUrl(SYS_MENU);                // �̾�ϻ��ꤹ��ɬ�פϤʤ�
//////////// �����ȥ�̾(�������Υ����ȥ�̾�ȥե�����Υ����ȥ�̾)
$menu->set_title('���ʥ��롼���� ���Ȳ�(����������)');
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
    $_SESSION['s_section']    = $_REQUEST['section'];
    $_SESSION['s_div']        = $_REQUEST['div'];
    $_SESSION['s_d_start']    = $_REQUEST['d_start'];
    $_SESSION['s_d_end']      = $_REQUEST['d_end'];
    $_SESSION['s_first_ym']   = $_REQUEST['first_ym'];
    $_SESSION['s_kubun']      = $_REQUEST['kubun'];
    $_SESSION['s_uri_ritu']   = $_REQUEST['uri_ritu'];
    $_SESSION['s_sales_page'] = $_REQUEST['sales_page'];
    $_SESSION['uri_assy_no']  = $_REQUEST['assy_no'];
    $uri_passwd = $_SESSION['s_uri_passwd'];
    $section    = $_SESSION['s_section'];
    $div        = $_SESSION['s_div'];
    $d_start    = $_SESSION['s_d_start'];
    $d_end      = $_SESSION['s_d_end'];
    $first_ym   = $_SESSION['s_first_ym'];
    $kubun      = $_SESSION['s_kubun'];
    $uri_ritu   = $_SESSION['s_uri_ritu'];
    $assy_no    = $_SESSION['uri_assy_no'];
        ///// day �Υ����å�
        if (substr($d_start, 6, 2) < 1) $d_start = substr($d_start, 0, 6) . '01';
        ///// �ǽ���������å����ƥ��åȤ���
        if (!checkdate(substr($d_start, 4, 2), substr($d_start, 6, 2), substr($d_start, 0, 4))) {
            $d_start = ( substr($d_start, 0, 6) . last_day(substr($d_start, 0, 4), substr($d_start, 4, 2)) );
            if (!checkdate(substr($d_start, 4, 2), substr($d_start, 6, 2), substr($d_start, 0, 4))) {
                $_SESSION['s_sysmsg'] = '���դλ��꤬�����Ǥ���';
                header('Location: ' . H_WEB_HOST . $menu->out_RetUrl());    // ľ���θƽи������
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
                header('Location: ' . H_WEB_HOST . $menu->out_RetUrl());    // ľ���θƽи������
                exit();
            }
        }
    $_SESSION['s_d_start'] = $d_start;
    $_SESSION['s_d_end']   = $d_end  ;
    
    ////////////// �ѥ���ɥ����å�
    if ($uri_passwd != date('Ymd')) {
        $_SESSION['s_sysmsg'] = "<font color='yellow'>�ѥ���ɤ��㤤�ޤ���</font>";
        header('Location: ' . H_WEB_HOST . $menu->out_RetUrl());    // ľ���θƽи������
        exit();
    }
    ///////////// ��׶�ۡ�����������
        $query = "select
                        count(����) as t_ken,
                        sum(����) as t_kazu,
                        sum(Uround(����*ñ��,0)) as t_kingaku
                  from
                        hiuuri
                  left outer join
                        assembly_schedule as a
                  on �ײ��ֹ�=plan_no
                  left outer join
                        mshmas as p
                  on assyno=p.mipn
                  left outer join
                        -- mshgnm as gnm
                        msshg3 as gnm
                  -- on p.mhjcd=gnm.mhgcd
                  on p.mhshc=gnm.mhgcd";
    //////////// SQL where ��� ���Ѥ���
    $search = "where �׾���>=$d_start and �׾���<=$d_end";
    if ($assy_no != '') {       // �����ֹ椬���ꤵ�줿���
        $search .= " and assyno like '{$assy_no}%%'";
    } elseif ($section != " ") {    // ���ʥ��롼�ץ����ɤǹʹ��ߡ������롼�������Ϥ������Τǥ֥�󥯤Ϥʤ��Ϥ���
        $search .= " and gnm.mhggp='$section'";
    }
    if ($div == 'S') {    // ����Τߤʤ�
        $search .= " and note15 like 'SC%%'";
    } elseif ($div == 'C') {    // ɸ��Τߤʤ�
        $search .= " and (note15 NOT like 'SC%%' OR note15 IS NULL)";    // ��������ɸ��ؤ���
    } elseif ($div == 'CC') {    // ���ץ�ɸ��Τߤʤ�
        $search .= " and ������='C' and (note15 NOT like 'SC%%' OR note15 IS NULL)";    // ��������ɸ��ؤ���
    } elseif ($div == 'CL') {    // ��˥�ɸ��Τߤʤ�
        $search .= " and ������='L' and (note15 NOT like 'SC%%' OR note15 IS NULL)";    // ��������ɸ��ؤ���
    }
    $search .= " and datatype='1'";
    $query = sprintf("$query %s", $search);     // SQL query ʸ�δ���
    $_SESSION['sales_search'] = $search;        // SQL��where�����¸
    $res_sum = array();
    if (getResult($query, $res_sum) <= 0) {
        $_SESSION['s_sysmsg'] = '��׶�ۤμ����˼��Ԥ��ޤ�����';
        header('Location: ' . H_WEB_HOST . $menu->out_RetUrl());    // ľ���θƽи������
        exit();
    } else {
        $t_ken     = $res_sum[0]['t_ken'];
        $t_kazu    = $res_sum[0]['t_kazu'];
        $t_kingaku = $res_sum[0]['t_kingaku'];
        $_SESSION['u_t_ken']  = $t_ken;
        $_SESSION['u_t_kazu'] = $t_kazu;
        $_SESSION['u_t_kin']  = $t_kingaku;
    }
} else {                                                // �ڡ������ؤʤ�
    $t_ken     = $_SESSION['u_t_ken'];
    $t_kazu    = $_SESSION['u_t_kazu'];
    $t_kingaku = $_SESSION['u_t_kin'];
}

$uri_passwd = $_SESSION['s_uri_passwd'];
$section    = $_SESSION['s_section'];
$div        = $_SESSION['s_div'];
$d_start    = $_SESSION['s_d_start'];
$d_end      = $_SESSION['s_d_end'];
$first_ym   = $_SESSION['s_first_ym'];
$kubun      = $_SESSION['s_kubun'];
$uri_ritu   = $_SESSION['s_uri_ritu'];
$assy_no    = $_SESSION['uri_assy_no'];
$search     = $_SESSION['sales_search'];

$cost1_ym = $first_ym;

$nen        = substr($cost1_ym, 0, 4);
$tsuki      = substr($cost1_ym, 4, 2);
$cost1_name = $nen . "/" . $tsuki;

if (substr($cost1_ym,4,2)!=12) {
    $cost1_ymd = $cost1_ym + 1;
    $cost1_ymd = $cost1_ymd . '10';
} else {
    $cost1_ymd = $cost1_ym + 100;
    $cost1_ymd = $cost1_ymd - 11;
    $cost1_ymd = $cost1_ymd . '10';
}

///////// ��ΨȽ����
///////// ��Ψ������ǤϤʤ��ʤä���ɽ�����Υ��å����ѹ����롣
$power_rate = 1.13;      // 2011/04/01�ܹ�

///// ���ʥ��롼��̾������
if ($section != " ") {
    $query_s = "
            SELECT  groupm.group_no                AS ���롼���ֹ�     -- 0
                ,   groupm.group_name              AS ���롼��̾       -- 1
            FROM
                product_serchGroup AS groupm
            WHERE
                groupm.group_no = {$section}
            ORDER BY
                group_name
        ";

    $res_s = array();
    if (($rows_s = getResultWithField2($query_s, $field_s, $res_s)) <= 0) {
        $_SESSION['s_sysmsg'] = "���롼�פ���Ͽ������ޤ���";
        $field[0]   = "���롼���ֹ�";
        $field[1]   = "���롼��̾";
        $_SESSION['s_sysmsg'] = "��Ͽ������ޤ���";
        //$result->add_array2('res_s', '');
        //$result->add_array2('field_s', '');
        //$result->add('num_s', 2);
        //$result->add('rows_s', '');
        $section_name = '';
    } else {
        $num_s = count($field_s);
        //$result->add_array2('res_s', $res_s);
        //$result->add_array2('field_s', $field_s);
        //$result->add('num_s', $num_s);
        //$result->add('rows_s', $rows_s);
        $section_name = $res_s[0][1];
    }
}
///// ���ʥ��롼��̾������
if ($section == " ") $section_name = "�����롼��";                  // �����롼������ϳ������ΤǻȤäƤ��ʤ�

//////////// ɽ�����Υǡ���ɽ���ѤΥ���ץ� Query & �����
    $query_t = sprintf("select
                            u.�׾���        as �׾���,                  -- 0
                            CASE
                                WHEN trim(u.�ײ��ֹ�)='' THEN '---'         --NULL�Ǥʤ��ƥ��ڡ�������ޤäƤ�����Ϥ��졪
                                ELSE u.�ײ��ֹ�
                            END                     as �ײ��ֹ�,        -- 1
                            CASE
                                WHEN trim(u.assyno) = '' THEN '---'
                                ELSE u.assyno
                            END                     as �����ֹ�,        -- 2
                            CASE
                                WHEN trim(substr(m.midsc,1,38)) = '' THEN '&nbsp;'
                                WHEN m.midsc IS NULL THEN '&nbsp;'
                                ELSE substr(m.midsc,1,38)
                            END             as ����̾,                  -- 3
                            u.����          as ����,                    -- 4
                            u.ñ��          as ����ñ��,                -- 5
                            Uround(u.���� * u.ñ��, 0) as ���,         -- 6
                            CASE
                                WHEN sum_price + Uround((m_time + g_time) * assy_rate, 2) + Uround(a_time * a_rate, 2) IS NULL THEN 0
                                ELSE sum_price + Uround((m_time + g_time) * assy_rate, 2) + Uround(a_time * a_rate, 2)
                            END                     as �������,        -- 7
                            CASE
                                WHEN sum_price + Uround((m_time + g_time) * assy_rate, 2) + Uround(a_time * a_rate, 2) IS NULL THEN '-----'
                                ELSE to_char(mate.regdate, 'YYYY/MM/DD')
                            END                     AS ��Ͽ��,          -- 8
                            CASE
                                WHEN (SELECT sum_price + Uround((m_time + g_time) * mate.assy_rate, 2) + Uround(a_time * a_rate, 2) FROM material_cost_header WHERE to_char(regdate, 'YYYYMMDD') < {$cost1_ymd} AND assy_no = u.assyno ORDER BY assy_no DESC, regdate DESC LIMIT 1) 
                                    IS NULL THEN    CASE 
                                                        WHEN (SELECT sum_price + Uround((m_time + g_time) * assy_rate, 2) + Uround(a_time * a_rate, 2) FROM material_cost_header WHERE to_char(regdate, 'YYYYMMDD') < {$cost1_ymd} AND assy_no = u.assyno ORDER BY assy_no DESC, regdate DESC LIMIT 1) IS NULL THEN 0
                                                        ELSE (SELECT sum_price + Uround((m_time + g_time) * assy_rate, 2) + Uround(a_time * a_rate, 2) FROM material_cost_header WHERE to_char(regdate, 'YYYYMMDD') < {$cost1_ymd} AND assy_no = u.assyno ORDER BY assy_no DESC, regdate DESC LIMIT 1)
                                                    END
                                ELSE (SELECT sum_price + Uround((m_time + g_time) * mate.assy_rate, 2) + Uround(a_time * a_rate, 2) FROM material_cost_header WHERE to_char(regdate, 'YYYYMMDD') < {$cost1_ymd} AND assy_no = u.assyno ORDER BY assy_no DESC, regdate DESC LIMIT 1)
                            END                     AS ����������     -- 9
                            ,
                            CASE
                                WHEN (SELECT to_char(regdate, 'YYYY/MM/DD') FROM material_cost_header WHERE to_char(regdate, 'YYYYMMDD') < {$cost1_ymd} AND assy_no = u.assyno ORDER BY assy_no DESC, regdate DESC LIMIT 1) IS NULL THEN '----------'
                                ELSE (SELECT to_char(regdate, 'YYYY/MM/DD') FROM material_cost_header WHERE to_char(regdate, 'YYYYMMDD') < {$cost1_ymd} AND assy_no = u.assyno ORDER BY assy_no DESC, regdate DESC LIMIT 1)
                            END                     AS �����Ͽ��       --10
                            ,
                            CASE
                                WHEN sum_price + Uround((m_time + g_time) * assy_rate, 2) + Uround(a_time * a_rate, 2) IS NULL THEN 0
                                ELSE
                                CASE
                                    WHEN (SELECT sum_price + Uround((m_time + g_time) * mate.assy_rate, 2) + Uround(a_time * a_rate, 2) FROM material_cost_header WHERE to_char(regdate, 'YYYYMMDD') < {$cost1_ymd} AND assy_no = u.assyno ORDER BY assy_no DESC, regdate DESC LIMIT 1) 
                                    IS NULL THEN    CASE
                                                        WHEN (SELECT sum_price + Uround((m_time + g_time) * assy_rate, 2) + Uround(a_time * a_rate, 2) FROM material_cost_header WHERE to_char(regdate, 'YYYYMMDD') < {$cost1_ymd} AND assy_no = u.assyno ORDER BY assy_no DESC, regdate DESC LIMIT 1) IS NULL THEN 0
                                                        ELSE (sum_price + Uround((m_time + g_time) * assy_rate, 2) + Uround(a_time * a_rate, 2)) - (SELECT sum_price + Uround((m_time + g_time) * assy_rate, 2) + Uround(a_time * a_rate, 2) FROM material_cost_header WHERE to_char(regdate, 'YYYYMMDD') < {$cost1_ymd} AND assy_no = u.assyno ORDER BY assy_no DESC, regdate DESC LIMIT 1)
                                                    END
                                    ELSE (sum_price + Uround((m_time + g_time) * assy_rate, 2) + Uround(a_time * a_rate, 2)) - (SELECT sum_price + Uround((m_time + g_time) * mate.assy_rate, 2) + Uround(a_time * a_rate, 2) FROM material_cost_header WHERE to_char(regdate, 'YYYYMMDD') < {$cost1_ymd} AND assy_no = u.assyno ORDER BY assy_no DESC, regdate DESC LIMIT 1)
                                END
                            END                      AS ñ������        --11
                            ,
                            CASE
                                WHEN sum_price + Uround((m_time + g_time) * assy_rate, 2) + Uround(a_time * a_rate, 2) IS NULL THEN 0
                                ELSE
                                CASE
                                    WHEN sum_price + Uround((m_time + g_time) * assy_rate, 2) + Uround(a_time * a_rate, 2) IS NULL THEN 0
                                    ELSE
                                    CASE
                                        WHEN (SELECT sum_price + Uround((m_time + g_time) * mate.assy_rate, 2) + Uround(a_time * a_rate, 2) FROM material_cost_header WHERE to_char(regdate, 'YYYYMMDD') < {$cost1_ymd} AND assy_no = u.assyno ORDER BY assy_no DESC, regdate DESC LIMIT 1) 
                                        IS NULL THEN    CASE
                                                            WHEN (SELECT sum_price + Uround((m_time + g_time) * assy_rate, 2) + Uround(a_time * a_rate, 2) FROM material_cost_header WHERE to_char(regdate, 'YYYYMMDD') < {$cost1_ymd} AND assy_no = u.assyno ORDER BY assy_no DESC, regdate DESC LIMIT 1) IS NULL THEN 0
                                                            ELSE Uround(((sum_price + Uround((m_time + g_time) * assy_rate, 2) + Uround(a_time * a_rate, 2)) - (SELECT sum_price + Uround((m_time + g_time) * assy_rate, 2) + Uround(a_time * a_rate, 2) FROM material_cost_header WHERE to_char(regdate, 'YYYYMMDD') < {$cost1_ymd} AND assy_no = u.assyno ORDER BY assy_no DESC, regdate DESC LIMIT 1)) / (sum_price + Uround((m_time + g_time) * assy_rate, 2) + Uround(a_time * a_rate, 2)) * 100, 2)
                                                        END
                                        ELSE Uround(((sum_price + Uround((m_time + g_time) * assy_rate, 2) + Uround(a_time * a_rate, 2)) - (SELECT sum_price + Uround((m_time + g_time) * mate.assy_rate, 2) + Uround(a_time * a_rate, 2) FROM material_cost_header WHERE to_char(regdate, 'YYYYMMDD') < {$cost1_ymd} AND assy_no = u.assyno ORDER BY assy_no DESC, regdate DESC LIMIT 1)) / (sum_price + Uround((m_time + g_time) * assy_rate, 2) + Uround(a_time * a_rate, 2)) * 100, 2)
                                    END
                                END
                            END                      AS ����Ψ          --12
                            ,
                            CASE
                                WHEN sum_price + Uround((m_time + g_time) * assy_rate, 2) + Uround(a_time * a_rate, 2) IS NULL THEN 0
                                ELSE
                                CASE
                                    WHEN (SELECT sum_price + Uround((m_time + g_time) * mate.assy_rate, 2) + Uround(a_time * a_rate, 2) FROM material_cost_header WHERE to_char(regdate, 'YYYYMMDD') < {$cost1_ymd} AND assy_no = u.assyno ORDER BY assy_no DESC, regdate DESC LIMIT 1) 
                                    IS NULL THEN    CASE
                                                        WHEN (SELECT sum_price + Uround((m_time + g_time) * assy_rate, 2) + Uround(a_time * a_rate, 2) FROM material_cost_header WHERE to_char(regdate, 'YYYYMMDD') < {$cost1_ymd} AND assy_no = u.assyno ORDER BY assy_no DESC, regdate DESC LIMIT 1) IS NULL THEN 0
                                                        ELSE ((sum_price + Uround((m_time + g_time) * assy_rate, 2) + Uround(a_time * a_rate, 2)) - (SELECT sum_price + Uround((m_time + g_time) * assy_rate, 2) + Uround(a_time * a_rate, 2) FROM material_cost_header WHERE to_char(regdate, 'YYYYMMDD') < {$cost1_ymd} AND assy_no = u.assyno ORDER BY assy_no DESC, regdate DESC LIMIT 1)) * u.����
                                                    END
                                    ELSE ((sum_price + Uround((m_time + g_time) * assy_rate, 2) + Uround(a_time * a_rate, 2)) - (SELECT sum_price + Uround((m_time + g_time) * mate.assy_rate, 2) + Uround(a_time * a_rate, 2) FROM material_cost_header WHERE to_char(regdate, 'YYYYMMDD') < {$cost1_ymd} AND assy_no = u.assyno ORDER BY assy_no DESC, regdate DESC LIMIT 1)) * u.����
                                END
                            END                      AS �������        --13
                            ,
                            CASE
                                WHEN sum_price + Uround((m_time + g_time) * assy_rate, 2) + Uround(a_time * a_rate, 2) IS NULL THEN 0
                                ELSE 
                                CASE
                                    WHEN u.ñ�� IS NULL THEN 0
                                    ELSE Uround(u.ñ�� / (sum_price + Uround((m_time + g_time) * assy_rate, 2) + Uround(a_time * a_rate, 2)), 2)
                                END
                            END                      AS ��Ψ            --14
                            ---------------- �ꥹ�ȳ� -----------------
                            ,
                            (SELECT plan_no FROM material_cost_header WHERE to_char(regdate, 'YYYYMMDD') < {$cost1_ymd} AND assy_no = u.assyno ORDER BY assy_no DESC, regdate DESC LIMIT 1)
                                                     AS ���������ײ�  --15
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
                      left outer join
                        mshmas as p
                      on u.assyno=p.mipn
                      left outer join
                        -- mshgnm as gnm
                        msshg3 as gnm
                      -- on p.mhjcd=gnm.mhgcd
                      on p.mhshc=gnm.mhgcd
                      %s
                      order by �׾���, assyno
                      ", $search);   // ���� $search �Ǹ���
$res_t   = array();
$field_t = array();
if (($rows_t = getResultWithField3($query_t, $field_t, $res_t)) <= 0) {
    $_SESSION['s_sysmsg'] .= sprintf("<font color='yellow'>������٤Υǡ���������ޤ���<br>%s��%s</font>", format_date($d_start), format_date($d_end) );
    header('Location: ' . H_WEB_HOST . $menu->out_RetUrl());    // ľ���θƽи������
    exit();
} else {
    $num_t           = count($field_t);     // �ե�����ɿ�����
    $diff_kin_total   = 0;                   // ��������ȡ�����
    $diff_cost_total  = 0;                   // ñ�������ȡ�����
    $diff_cost_ave    = 0;                   // ñ������ʿ��
    $diff_cost_count  = 0;                   // ñ������ʿ���ѥ����󥿡�
    $diff_cost_sum    = 0;                   // ñ������ʿ���ѥȡ�����
    $cost_rate        = 0;                   // ��Ψʿ��
    $cost_rate_count  = 0;                   // ��Ψʿ���ѥ����󥿡�
    $cost_rate_sum    = 0;                   // ��Ψʿ���ѥȡ�����
    $diff_rate        = 0;                   // ñ������Ψʿ��
    $diff_rate_count  = 0;                   // ñ������Ψʿ���ѥ����󥿡�
    $diff_rate_sum    = 0;                   // ñ������Ψʿ���ѥȡ�����
    for ($r=0; $r<$rows_t; $r++) {
        $res_t[$r][4] = mb_convert_kana($res_t[$r][4], 'ka', 'EUC-JP');   // ���ѥ��ʤ�Ⱦ�ѥ��ʤإƥ���Ū�˥���С���
        if( $res_t[$r][14] != 0 ) {
            $cost_rate_sum += $res_t[$r][14];
            $cost_rate_count++;
        }
        if($res_t[$r][7] != 0 && $res_t[$r][9] != 0) {
            $diff_rate_sum += $res_t[$r][12];
            $diff_rate_count++;
        }
        $diff_cost_total += $res_t[$r][11];
        if ($res_t[$r][7] != 0 && $res_t[$r][9] != 0) {
            $diff_cost_sum += $res_t[$r][11];
            $diff_cost_count++;
        }
        $diff_kin_total += $res_t[$r][13];
    }
    if ($cost_rate_sum != 0 && $cost_rate_count != 0) {
        $cost_rate = $cost_rate_sum / $cost_rate_count;
    }
    if ($diff_rate_sum != 0 && $diff_rate_count != 0) {
        $diff_rate = $diff_rate_sum / $diff_rate_count;
    }
    if ($diff_cost_sum != 0 && $diff_cost_count != 0) {
        $diff_cost_ave = $diff_cost_sum / $diff_cost_count;
    }
}

//////////// ɽ�������
$ft_kingaku    = number_format($t_kingaku);                    // ���头�ȤΥ���ޤ��ղ�
$ft_ken        = number_format($t_ken);
$ft_kazu       = number_format($t_kazu);
$f_cost_rate   = number_format($cost_rate, 2);
$f_diff_rate   = number_format($diff_rate, 2);
$f_diff_cost_t = number_format($diff_cost_total, 2);
$f_diff_cost_a = number_format($diff_cost_ave, 2);
$f_diff_kin_t  = number_format($diff_kin_total, 0);
$f_d_start     = format_date($d_start);                        // ���դ� / �ǥե����ޥå�
$f_d_end       = format_date($d_end);
$menu->set_caption("<u>����=<font color='red'>{$section_name}</font>��{$f_d_start}��{$f_d_end}����׷��={$ft_ken}����׶��={$ft_kingaku}����׿���={$ft_kazu}<u>");
$menu->set_caption2("<u>ñ��������{$f_diff_cost_t}��ñ������ʿ��{$f_diff_cost_a}��ñ������Ψ��ʿ��={$f_diff_rate}�󡧶��������{$f_diff_kin_t}����Ψʿ��={$f_cost_rate}<u>");

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

//////////// ɽ�����Υǡ���ɽ���ѤΥ���ץ� Query & �����
    $query = sprintf("select
                            u.�׾���        as �׾���,                  -- 0
                            CASE
                                WHEN trim(u.�ײ��ֹ�)='' THEN '---'         --NULL�Ǥʤ��ƥ��ڡ�������ޤäƤ�����Ϥ��졪
                                ELSE u.�ײ��ֹ�
                            END                     as �ײ��ֹ�,        -- 1
                            CASE
                                WHEN trim(u.assyno) = '' THEN '---'
                                ELSE u.assyno
                            END                     as �����ֹ�,        -- 2
                            CASE
                                WHEN trim(substr(m.midsc,1,38)) = '' THEN '&nbsp;'
                                WHEN m.midsc IS NULL THEN '&nbsp;'
                                ELSE substr(m.midsc,1,38)
                            END             as ����̾,                  -- 3
                            u.����          as ����,                    -- 4
                            u.ñ��          as ����ñ��,                -- 5
                            Uround(u.���� * u.ñ��, 0) as ���,         -- 6
                            CASE
                                WHEN sum_price + Uround((m_time + g_time) * assy_rate, 2) + Uround(a_time * a_rate, 2) IS NULL THEN 0
                                ELSE sum_price + Uround((m_time + g_time) * assy_rate, 2) + Uround(a_time * a_rate, 2)
                            END                     as �������,        -- 7
                            CASE
                                WHEN sum_price + Uround((m_time + g_time) * assy_rate, 2) + Uround(a_time * a_rate, 2) IS NULL THEN '-----'
                                ELSE to_char(mate.regdate, 'YYYY/MM/DD')
                            END                     AS ��Ͽ��,          -- 8
                            CASE
                                WHEN (SELECT sum_price + Uround((m_time + g_time) * mate.assy_rate, 2) + Uround(a_time * a_rate, 2) FROM material_cost_header WHERE to_char(regdate, 'YYYYMMDD') < {$cost1_ymd} AND assy_no = u.assyno ORDER BY assy_no DESC, regdate DESC LIMIT 1) 
                                    IS NULL THEN    CASE 
                                                        WHEN (SELECT sum_price + Uround((m_time + g_time) * assy_rate, 2) + Uround(a_time * a_rate, 2) FROM material_cost_header WHERE to_char(regdate, 'YYYYMMDD') < {$cost1_ymd} AND assy_no = u.assyno ORDER BY assy_no DESC, regdate DESC LIMIT 1) IS NULL THEN 0
                                                        ELSE (SELECT sum_price + Uround((m_time + g_time) * assy_rate, 2) + Uround(a_time * a_rate, 2) FROM material_cost_header WHERE to_char(regdate, 'YYYYMMDD') < {$cost1_ymd} AND assy_no = u.assyno ORDER BY assy_no DESC, regdate DESC LIMIT 1)
                                                    END
                                ELSE (SELECT sum_price + Uround((m_time + g_time) * mate.assy_rate, 2) + Uround(a_time * a_rate, 2) FROM material_cost_header WHERE to_char(regdate, 'YYYYMMDD') < {$cost1_ymd} AND assy_no = u.assyno ORDER BY assy_no DESC, regdate DESC LIMIT 1)
                            END                     AS ����������     -- 9
                            ,
                            CASE
                                WHEN (SELECT to_char(regdate, 'YYYY/MM/DD') FROM material_cost_header WHERE to_char(regdate, 'YYYYMMDD') < {$cost1_ymd} AND assy_no = u.assyno ORDER BY assy_no DESC, regdate DESC LIMIT 1) IS NULL THEN '----------'
                                ELSE (SELECT to_char(regdate, 'YYYY/MM/DD') FROM material_cost_header WHERE to_char(regdate, 'YYYYMMDD') < {$cost1_ymd} AND assy_no = u.assyno ORDER BY assy_no DESC, regdate DESC LIMIT 1)
                            END                     AS �����Ͽ��       --10
                            ,
                            CASE
                                WHEN sum_price + Uround((m_time + g_time) * assy_rate, 2) + Uround(a_time * a_rate, 2) IS NULL THEN 0
                                ELSE
                                CASE
                                    WHEN (SELECT sum_price + Uround((m_time + g_time) * mate.assy_rate, 2) + Uround(a_time * a_rate, 2) FROM material_cost_header WHERE to_char(regdate, 'YYYYMMDD') < {$cost1_ymd} AND assy_no = u.assyno ORDER BY assy_no DESC, regdate DESC LIMIT 1) 
                                    IS NULL THEN    CASE
                                                        WHEN (SELECT sum_price + Uround((m_time + g_time) * assy_rate, 2) + Uround(a_time * a_rate, 2) FROM material_cost_header WHERE to_char(regdate, 'YYYYMMDD') < {$cost1_ymd} AND assy_no = u.assyno ORDER BY assy_no DESC, regdate DESC LIMIT 1) IS NULL THEN 0
                                                        ELSE (sum_price + Uround((m_time + g_time) * assy_rate, 2) + Uround(a_time * a_rate, 2)) - (SELECT sum_price + Uround((m_time + g_time) * assy_rate, 2) + Uround(a_time * a_rate, 2) FROM material_cost_header WHERE to_char(regdate, 'YYYYMMDD') < {$cost1_ymd} AND assy_no = u.assyno ORDER BY assy_no DESC, regdate DESC LIMIT 1)
                                                    END
                                    ELSE (sum_price + Uround((m_time + g_time) * assy_rate, 2) + Uround(a_time * a_rate, 2)) - (SELECT sum_price + Uround((m_time + g_time) * mate.assy_rate, 2) + Uround(a_time * a_rate, 2) FROM material_cost_header WHERE to_char(regdate, 'YYYYMMDD') < {$cost1_ymd} AND assy_no = u.assyno ORDER BY assy_no DESC, regdate DESC LIMIT 1)
                                END
                            END                      AS ñ������        --11
                            ,
                            CASE
                                WHEN sum_price + Uround((m_time + g_time) * assy_rate, 2) + Uround(a_time * a_rate, 2) IS NULL THEN 0
                                ELSE
                                CASE
                                    WHEN sum_price + Uround((m_time + g_time) * assy_rate, 2) + Uround(a_time * a_rate, 2) IS NULL THEN 0
                                    ELSE
                                    CASE
                                        WHEN (SELECT sum_price + Uround((m_time + g_time) * mate.assy_rate, 2) + Uround(a_time * a_rate, 2) FROM material_cost_header WHERE to_char(regdate, 'YYYYMMDD') < {$cost1_ymd} AND assy_no = u.assyno ORDER BY assy_no DESC, regdate DESC LIMIT 1) 
                                        IS NULL THEN    CASE
                                                            WHEN (SELECT sum_price + Uround((m_time + g_time) * assy_rate, 2) + Uround(a_time * a_rate, 2) FROM material_cost_header WHERE to_char(regdate, 'YYYYMMDD') < {$cost1_ymd} AND assy_no = u.assyno ORDER BY assy_no DESC, regdate DESC LIMIT 1) IS NULL THEN 0
                                                            ELSE Uround(((sum_price + Uround((m_time + g_time) * assy_rate, 2) + Uround(a_time * a_rate, 2)) - (SELECT sum_price + Uround((m_time + g_time) * assy_rate, 2) + Uround(a_time * a_rate, 2) FROM material_cost_header WHERE to_char(regdate, 'YYYYMMDD') < {$cost1_ymd} AND assy_no = u.assyno ORDER BY assy_no DESC, regdate DESC LIMIT 1)) / (sum_price + Uround((m_time + g_time) * assy_rate, 2) + Uround(a_time * a_rate, 2)) * 100, 2)
                                                        END
                                        ELSE Uround(((sum_price + Uround((m_time + g_time) * assy_rate, 2) + Uround(a_time * a_rate, 2)) - (SELECT sum_price + Uround((m_time + g_time) * mate.assy_rate, 2) + Uround(a_time * a_rate, 2) FROM material_cost_header WHERE to_char(regdate, 'YYYYMMDD') < {$cost1_ymd} AND assy_no = u.assyno ORDER BY assy_no DESC, regdate DESC LIMIT 1)) / (sum_price + Uround((m_time + g_time) * assy_rate, 2) + Uround(a_time * a_rate, 2)) * 100, 2)
                                    END
                                END
                            END                      AS ����Ψ          --12
                            ,
                            CASE
                                WHEN sum_price + Uround((m_time + g_time) * assy_rate, 2) + Uround(a_time * a_rate, 2) IS NULL THEN 0
                                ELSE
                                CASE
                                    WHEN (SELECT sum_price + Uround((m_time + g_time) * mate.assy_rate, 2) + Uround(a_time * a_rate, 2) FROM material_cost_header WHERE to_char(regdate, 'YYYYMMDD') < {$cost1_ymd} AND assy_no = u.assyno ORDER BY assy_no DESC, regdate DESC LIMIT 1) 
                                    IS NULL THEN    CASE
                                                        WHEN (SELECT sum_price + Uround((m_time + g_time) * assy_rate, 2) + Uround(a_time * a_rate, 2) FROM material_cost_header WHERE to_char(regdate, 'YYYYMMDD') < {$cost1_ymd} AND assy_no = u.assyno ORDER BY assy_no DESC, regdate DESC LIMIT 1) IS NULL THEN 0
                                                        ELSE ((sum_price + Uround((m_time + g_time) * assy_rate, 2) + Uround(a_time * a_rate, 2)) - (SELECT sum_price + Uround((m_time + g_time) * assy_rate, 2) + Uround(a_time * a_rate, 2) FROM material_cost_header WHERE to_char(regdate, 'YYYYMMDD') < {$cost1_ymd} AND assy_no = u.assyno ORDER BY assy_no DESC, regdate DESC LIMIT 1)) * u.����
                                                    END
                                    ELSE ((sum_price + Uround((m_time + g_time) * assy_rate, 2) + Uround(a_time * a_rate, 2)) - (SELECT sum_price + Uround((m_time + g_time) * mate.assy_rate, 2) + Uround(a_time * a_rate, 2) FROM material_cost_header WHERE to_char(regdate, 'YYYYMMDD') < {$cost1_ymd} AND assy_no = u.assyno ORDER BY assy_no DESC, regdate DESC LIMIT 1)) * u.����
                                END
                            END                      AS �������        --13
                            ,
                            CASE
                                WHEN sum_price + Uround((m_time + g_time) * assy_rate, 2) + Uround(a_time * a_rate, 2) IS NULL THEN 0
                                ELSE 
                                CASE
                                    WHEN u.ñ�� IS NULL THEN 0
                                    ELSE Uround(u.ñ�� / (sum_price + Uround((m_time + g_time) * assy_rate, 2) + Uround(a_time * a_rate, 2)), 2)
                                END
                            END                      AS ��Ψ            --14
                            ---------------- �ꥹ�ȳ� -----------------
                            ,
                            (SELECT plan_no FROM material_cost_header WHERE to_char(regdate, 'YYYYMMDD') < {$cost1_ymd} AND assy_no = u.assyno ORDER BY assy_no DESC, regdate DESC LIMIT 1)
                                                     AS ���������ײ�  --15
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
                      left outer join
                        mshmas as p
                      on u.assyno=p.mipn
                      left outer join
                        -- mshgnm as gnm
                        msshg3 as gnm
                      -- on p.mhjcd=gnm.mhgcd
                      on p.mhshc=gnm.mhgcd
                      %s
                      order by �׾���, assyno
                      offset %d limit %d
                      ", $search, $offset, PAGE);   // ���� $search �Ǹ���
$res   = array();
$field = array();
if (($rows = getResultWithField3($query, $field, $res)) <= 0) {
    $_SESSION['s_sysmsg'] .= sprintf("<font color='yellow'>������٤Υǡ���������ޤ���<br>%s��%s</font>", format_date($d_start), format_date($d_end) );
    header('Location: ' . H_WEB_HOST . $menu->out_RetUrl());    // ľ���θƽи������
    exit();
} else {
    $num = count($field);       // �ե�����ɿ�����
    for ($r=0; $r<$rows; $r++) {
        $res[$r][4] = mb_convert_kana($res[$r][4], 'ka', 'EUC-JP');   // ���ѥ��ʤ�Ⱦ�ѥ��ʤإƥ���Ū�˥���С���
    }
    $_SESSION['SALES_TEST'] = sprintf("order by �׾��� offset %d limit %d", $offset, PAGE);
}

//////////////////// ������񥫥ץ�ɸ����Ψ57���ִ���
//$query_i = sprintf("select
//                            CASE
//                                WHEN trim(u.�ײ��ֹ�)='' THEN '---'         --NULL�Ǥʤ��ƥ��ڡ�������ޤäƤ�����Ϥ��졪
//                                ELSE u.�ײ��ֹ�
//                            END                     as �ײ��ֹ�        -- 0
//                      from
//                            hiuuri as u
//                      left outer join
//                            assembly_schedule as a
//                      on u.�ײ��ֹ�=a.plan_no
//                      left outer join
//                            miitem as m
//                      on u.assyno=m.mipn
//                      left outer join
//                            material_cost_header as mate
//                      on u.�ײ��ֹ�=mate.plan_no
//                      LEFT OUTER JOIN
//                            sales_parts_material_history AS pmate
//                      ON (u.assyno=pmate.parts_no AND u.�׾���=pmate.sales_date)
//                      WHERE �׾���>=20071001 and �׾���<=20080331
//                      AND ������='C' and (note15 NOT like 'SC%%' OR note15 IS NULL)
//                      AND datatype=1
//                      order by �ײ��ֹ�
//                        ");   // ���� $search �Ǹ���
//$res_i   = array();
//$field_i = array();
//if (($rows_i = getResultWithField3($query_i, $field_i, $res_i)) <= 0) {
//    $_SESSION['s_sysmsg'] .= sprintf("<font color='yellow'>������٤Υǡ���������ޤ���<br>%s��%s</font>", format_date($d_start), format_date($d_end) );
//    header('Location: ' . H_WEB_HOST . $menu->out_RetUrl());    // ľ���θƽи������
//    exit();
//} else {
//    for ($r=0; $r<$rows_i; $r++) {
//        $query_c = sprintf("UPDATE material_cost_header SET assy_rate = 57.00 WHERE plan_no='{$res_i[$r][0]}'");
//        $res_c   = array();
//        if (getResult($query_c, $res_c) <= 0) {
//        } else {
//        }
//    }
//}
// SQL�Υ������������ܸ��ѻ����ѹ���'�⥨�顼�ˤʤ�Τ�/�˰���ѹ�
$csv_search = str_replace('�׾���','keidate',$search);
$csv_search = str_replace('\'','/',$csv_search);
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
<body onLoad='set_focus()'>
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
        <table width='100%' border='0' cellspacing='0' cellpadding='0'>
            <tr>
                <td nowrap align='center' class='caption_font'>
                    <?php echo $menu->out_caption2(), "\n" ?>
                </td>
                <td align='center' class='caption_font'>
                    <a href='material_compare_sale_view_csv.php?csvdiv=<?php echo $div ?>&csvd_start=<?php echo $d_start ?>&csvd_end=<?php echo $d_end ?>&csvfirst_ym=<?php echo $first_ym ?>&csvsearch=<?php echo $csv_search ?>&csvsection=<?php echo $section ?>&csvdiv=<?php echo $div ?>'>
                        <B>CSV����<B>
                    </a>
                <td>
            </tr>
        </table>
        
        <!--------------- ����������ʸ��ɽ��ɽ������ -------------------->
        <table bgcolor='#d6d3ce' align='center' border='1' cellspacing='0' cellpadding='3'>
            <tr><td> <!----------- ���ߡ�(�ǥ�������) ------------>
        <table class='winbox_field' width='100%' bgcolor='#d6d3ce' align='center' border='1' cellspacing='0' cellpadding='3'>
            <thead>
                <!-- �ơ��֥� �إå�����ɽ�� -->
                <tr>
                    <th class='winbox' nowrap>No.</th>        <!-- �ԥʥ�С���ɽ�� -->
                <?php
                for ($i=0; $i<$num; $i++) {             // �ե�����ɿ�ʬ���֤�
                    if ($i <= 14) {
                        if ($i == 1) {
                ?>
                        <th class='winbox' nowrap>�ײ�<BR>�ֹ�</th>
                <?php
                        } elseif ($i == 2) {
                ?>
                        <th class='winbox' nowrap>����<BR>�ֹ�</th>
                <?php
                        } elseif ($i == 5) {
                ?>
                        <th class='winbox' nowrap>����<BR>ñ��</th>
                <?php
                        } elseif ($i == 7) {
                ?>
                        <th class='winbox' nowrap>���<BR>����</th>
                <?php
                        } elseif ($i == 9) {
                ?>
                        <th class='winbox' nowrap>�����<BR>������</th>
                <?php
                        } elseif ($i == 10) {
                ?>
                        <th class='winbox' nowrap>���<BR>��Ͽ��</th>
                <?php
                        } elseif ($i == 11) {
                ?>
                        <th class='winbox' nowrap>ñ��<BR>����</th>
                <?php
                        } elseif ($i == 12) {
                ?>
                        <th class='winbox' nowrap>ñ����<BR>��Ψ��</th>
                <?php
                        } elseif ($i == 13) {
                ?>
                        <th class='winbox' nowrap>���<BR>����</th>
                <?php
                        } elseif ($i == 14) {
                ?>
                        <th class='winbox' nowrap>��<BR>Ψ</th>
                <?php
                        } else {
                ?>
                        <th class='winbox' nowrap><?php echo $field[$i] ?></th>
                <?php
                        }
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
                    $recNo = ($offset + $r);
                    if ($session->get_local('recNo') == $recNo) {
                        echo "<tr style='background-color:#ffffc6;'>\n";
                    } else {
                        echo "<tr>\n";
                    }
                    echo "    <td class='winbox' nowrap align='right'><div class='pt10b'>" . ($r + $offset + 1) . "</div></td>    <!-- �ԥʥ�С���ɽ�� -->\n";
                    for ($i=0; $i<$num; $i++) {         // �쥳���ɿ�ʬ���֤�
                        // <!--  bgcolor='#ffffc6' �������� --> 
                        switch ($i) {
                            case 0:     // �׾���
                                echo "<td class='winbox' nowrap align='center'><div class='pt9'>" . format_date($res[$r][$i]) . "</div></td>\n";
                                break;
                            case 2:
                                echo "<td class='winbox' nowrap align='center'><a class='pt9' href='JavaScript:baseJS.Ajax(\"{$menu->out_self()}?recNo={$recNo}\");location.replace(\"{$menu->out_action('�����������')}?assy=", urlencode($res[$r][$i]), "&material=1&plan_no=", urlencode($res[$r][1]), "\")' target='application' style='text-decoration:none;'>{$res[$r][$i]}</a></td>\n";
                                break;
                            case 3:     // ����̾
                                echo "<td class='winbox' nowrap width='270' align='left'><div class='pt9'>" . $res[$r][$i] . "</div></td>\n";
                                break;
                            case 4:     // ����
                                echo "<td class='winbox' nowrap nowrap align='right'><div class='pt9'>" . number_format($res[$r][$i], 0) . "</div></td>\n";
                                break;
                            case 5:     // ����ñ��
                                echo "<td class='winbox' nowrap nowrap align='right'><div class='pt9'>" . number_format($res[$r][$i], 2) . "</div></td>\n";
                                break;
                            case 6:     // ���
                                echo "<td class='winbox' nowrap nowrap align='right'><div class='pt9'>" . number_format($res[$r][$i], 0) . "</div></td>\n";
                                break;
                            case 7:     // �������
                                if ($res[$r][$i] == 0) {
                                    echo "<td class='winbox' nowrap nowrap align='right'><div class='pt9'>-----</div></td>\n";
                                } else {
                                    echo "<td class='winbox' nowrap nowrap align='right'>
                                            <a class='pt9' href='JavaScript:baseJS.Ajax(\"{$menu->out_self()}?recNo={$recNo}\");location.replace(\"", $menu->out_action('�������Ȳ�'), "?plan_no={$res[$r][1]}&assy_no={$res[$r][2]}\")' target='application' style='text-decoration:none;'>"
                                            , number_format($res[$r][$i], 2), "</a></td>\n";
                                }
                                break;
                            case 9:     // ����������
                                if ($res[$r][$i] != 0) {
                                    echo "<td class='winbox' nowrap nowrap align='right'>
                                                    <a class='pt9' href='JavaScript:baseJS.Ajax(\"{$menu->out_self()}?recNo={$recNo}\");location.replace(\"", $menu->out_action('�������Ȳ�'), "?plan_no={$res[$r][15]}&assy_no={$res[$r][2]}\")' target='application' style='text-decoration:none;'>"
                                                    , number_format($res[$r][$i], 2), "</a></td>\n";
                                } else {
                                    echo "<td class='winbox' nowrap nowrap align='right'><div class='pt9'>----</div></td>\n";
                                }
                                break;
                            case 11:     // ñ������
                                if ($res[$r][$i] != 0) {
                                    echo "<td class='winbox' nowrap nowrap align='right'><div class='pt9'>" . number_format($res[$r][$i], 2) . "</div></td>\n";
                                } elseif (($res[$r][7] == $res[$r][9]) && ($res[$r][7] != 0 || $res[$r][9] != 0)) {
                                    echo "<td class='winbox' nowrap nowrap align='right'><div class='pt9'>" . number_format($res[$r][$i], 2) . "</div></td>\n";
                                } else {
                                    echo "<td class='winbox' nowrap nowrap align='right'><div class='pt9'>----</div></td>\n";
                                }
                                break;
                            case 12:     // ����Ψ��
                                if ($res[$r][7] != 0 && $res[$r][11] != 0) {
                                    echo "<td class='winbox' nowrap nowrap align='right'><div class='pt9'>" . number_format($res[$r][$i], 2) . "</div></td>\n";
                                } elseif ($res[$r][7] != 0 && $res[$r][11] == 0) {
                                    echo "<td class='winbox' nowrap nowrap align='right'><div class='pt9'>0.00</div></td>\n";
                                } else {
                                    echo "<td class='winbox' nowrap nowrap align='right'><div class='pt9'>----</div></td>\n";
                                }
                                break;
                            case 13:     // �������
                                if ($res[$r][$i] != 0) {
                                    echo "<td class='winbox' nowrap nowrap align='right'><div class='pt9'>" . number_format($res[$r][$i], 0) . "</div></td>\n";
                                } elseif (($res[$r][7] == $res[$r][9]) && ($res[$r][7] != 0 || $res[$r][9] != 0)) {
                                    echo "<td class='winbox' nowrap nowrap align='right'><div class='pt9'>" . number_format($res[$r][$i], 0) . "</div></td>\n";
                                } else {
                                    echo "<td class='winbox' nowrap nowrap align='right'><div class='pt9'>----</div></td>\n";
                                }
                                break;
                            case 14:     // ��Ψ
                                if ($res[$r][5] != 0 && $res[$r][7] != 0) {
                                    if ($res[$r][$i] > $power_rate) {
                                        echo "<td class='winbox' nowrap nowrap align='right'><div class='pt9'><font color='blue'>" . number_format($res[$r][$i], 2) . "</font></div></td>\n";
                                    } elseif ($res[$r][$i] < $power_rate) {
                                        echo "<td class='winbox' nowrap nowrap align='right'><div class='pt9'><font color='red'>" . number_format($res[$r][$i], 2) . "</font></div></td>\n";
                                    } else {
                                        echo "<td class='winbox' nowrap nowrap align='right'><div class='pt9'>" . number_format($res[$r][$i], 2) . "</div></td>\n";
                                    }
                                } else {
                                    echo "<td class='winbox' nowrap nowrap align='right'><div class='pt9'>----</div></td>\n";
                                }
                                break;
                            case 15:     // ���������ײ�
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
        <!--
        <table style='border: 2px solid #0A0;'>
            <tr><td align='center' class='pt11b' tabindex='1' id='note'>���������Ŀ�ɽ����Ʊ�ײ��ֹ����Ͽ������ʪ�ǡ��㿧��Ʊ�ײ�Ǥ�̵��������������Ǻǿ�����Ͽ��ɽ��</td></tr>
        </table>
        -->
    </center>
</body>
<?php echo $menu->out_alert_java()?>
</html>
<?php
// ob_end_flush();                 // ���ϥХåե���gzip���� END
?>
