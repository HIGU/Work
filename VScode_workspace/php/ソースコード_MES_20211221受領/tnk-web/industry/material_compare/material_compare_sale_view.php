<?php
//////////////////////////////////////////////////////////////////////////////
// ��� ���� �Ȳ� ������  new version   material_compare_sale_view.php      //
// Copyright (C) 2011 - 2012 Norihisa.Ohya norihisa_ooya@nitto-kohki.co.jp  //
// Changed history                                                          //
// 2011/05/26 material_compare_sale_view.php                                //
// 2011/05/31 ���롼�ץ������ѹ���ȼ��SQLʸ���ѹ�                           //
// 2011/06/01 �Ť����å������顼�򵯤����Ƥ����Τǥ����Ȳ�              //
// 2011/06/13 �ƥǡ�����ʿ������׻�����ɽ��                                //
// 2011/06/14 ��������פ�ɽ������褦�ˤ�����                              //
// 2011/06/15 �����ܥ�������֤����ʥץ�ӥ塼ɽ����                        //
// 2011/06/22 ��Ψʿ�Ѥ��ΨȽ����($power_rate)����˿�ʬ��               //
// 2011/07/06 ���ʥ��롼�פ˥��ץ�ɸ��ȥ�˥�ɸ����ɲ�                    //
// 2012/03/29 ���ʥ��롼�פ�̾�Τ��ѹ�                                      //
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

$result  = new Result;

////////////// ����������
//$menu->set_site( 1, 11);                    // site_index=01(����˥塼) site_id=11(����������)
////////////// �꥿���󥢥ɥ쥹����
// $menu->set_RetUrl(SYS_MENU);                // �̾�ϻ��ꤹ��ɬ�פϤʤ�
//////////// �����ȥ�̾(�������Υ����ȥ�̾�ȥե�����Υ����ȥ�̾)
$menu->set_title('���ʥ��롼���� ���Ȳ�����������ӡ�');
//////////// �ƽ����action̾�ȥ��ɥ쥹����
$menu->set_action('�������Ȳ�',   INDUST . 'material/materialCost_view.php');
$menu->set_action('ñ����Ͽ�Ȳ�',   INDUST . 'parts/parts_cost_view.php');
$menu->set_action('�����������',   INDUST . 'material/materialCost_view_assy.php');
$menu->set_action('�������',       INDUST . 'material_compare/material_compare_sale_view_product.php');

//////////// �֥饦�����Υ���å����к���
$uniq = $menu->set_useNotCache('target');

/////////////// �����Ϥ��ѿ��ν����
if ( isset($_SESSION['s_uri_passwd']) ) {
    $_REQUEST['uri_passwd'] = $_SESSION['s_uri_passwd'];
} else {
    $uri_passwd = '';
}
if ( isset($_SESSION['s_d_start']) ) {
    if ( !isset($_REQUEST['d_start']) ) {
        $_REQUEST['d_start'] = $_SESSION['s_d_start'];
    }
} else {
    if ( isset($_POST['d_start']) ) {
        $d_start = $_POST['d_start'];
    } else {
        $d_start = date_offset(1);
    }
}
if ( isset($_SESSION['s_d_end']) ) {
    if ( !isset($_REQUEST['d_end']) ) {
        $_REQUEST['d_end'] = $_SESSION['s_d_end'];
    }
} else {
    if ( isset($_POST['d_end']) ) {
        $d_end = $_POST['d_end'];
    } else {
        $d_end = date_offset(1);
    }
}
if ( isset($_SESSION['s_first_ym']) ) {
    if ( !isset($_REQUEST['first_ym']) ) {
        $_REQUEST['first_ym'] = $_SESSION['s_first_ym'];
    }
} else {
    if ( isset($_POST['first_ym']) ) {
        $first_ym = $_POST['first_ym'];
    } else {
        //$first_ym = date_offset(1);
        $first_ym = '';
    }
}
if ( isset($_SESSION['s_kubun']) ) {
    $_REQUEST['kubun'] = $_SESSION['s_kubun'];
} else {
    $kubun = '';
}
if ( isset($_SESSION['s_div']) ) {
    if ( !isset($_REQUEST['div']) ) {
        $_REQUEST['div'] = $_SESSION['s_div'];
    }
} else {
    if ( isset($_POST['div']) ) {
        $div = $_POST['div'];
    } else {
        $div = 'A';
    }
}

//////////// �����Υ��å����ǡ�����¸   ���ǡ����Ǥ�ڤ����뤿��
//if (! (isset($_REQUEST['forward']) || isset($_REQUEST['backward']) || isset($_REQUEST['page_keep'])) ) {
    $session->add_local('recNo', '-1');         // 0�쥳���ɤǥޡ�����ɽ�����Ƥ��ޤ�������б�
    $_SESSION['s_uri_passwd'] = $_REQUEST['uri_passwd'];
    $_SESSION['s_d_start']    = $_REQUEST['d_start'];
    $_SESSION['s_d_end']      = $_REQUEST['d_end'];
    $_SESSION['s_first_ym']   = $_REQUEST['first_ym'];
    $_SESSION['s_kubun']      = $_REQUEST['kubun'];
    $_SESSION['s_div']        = $_REQUEST['div'];
    $uri_passwd = $_SESSION['s_uri_passwd'];
    $d_start    = $_SESSION['s_d_start'];
    $d_end      = $_SESSION['s_d_end'];
    $first_ym   = $_SESSION['s_first_ym'];
    $kubun      = $_SESSION['s_kubun'];
    $div        = $_SESSION['s_div'];
        ///// day �Υ����å�
        if (substr($d_start, 6, 2) < 1) $d_start = substr($d_start, 0, 6) . '01';
        ///// �ǽ���������å����ƥ��åȤ���
        if (!checkdate(substr($d_start, 4, 2), substr($d_start, 6, 2), substr($d_start, 0, 4))) {
            $d_start = ( substr($d_start, 0, 6) . last_day(substr($d_start, 0, 4), substr($d_start, 4, 2)) );
            if (!checkdate(substr($d_start, 4, 2), substr($d_start, 6, 2), substr($d_start, 0, 4))) {
                $_SESSION['s_sysmsg'] = '���դλ��꤬�����Ǥ�z��';
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
                $_SESSION['s_sysmsg'] = '���դλ��꤬�����Ǥ�z��';
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
    //////////// SQL where ��� ���Ѥ���
    $search = "where �׾���>=$d_start and �׾���<=$d_end";
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
    $_SESSION['sales_search'] = $search;        // SQL��where�����¸
//}

$uri_passwd = $_SESSION['s_uri_passwd'];
$div        = $_SESSION['s_div'];
$d_start    = $_SESSION['s_d_start'];
$d_end      = $_SESSION['s_d_end'];
$kubun      = $_SESSION['s_kubun'];
$search     = $_SESSION['sales_search'];
$first_ym   = $_SESSION['s_first_ym'];

///////// ��ΨȽ����
///////// ��Ψ������ǤϤʤ��ʤä���ɽ�����Υ��å����ѹ����롣
$power_rate = 1.13;      // 2011/04/01�ܹ�

///////////// ��ʬ���ۡ�����������
$query_k = sprintf("select
                        sum(Uround(����*ñ��,0)) as ���,       -- 0
                        pts.top_no as ��ʬ��̾                  -- 1
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
                  on p.mhshc=gnm.mhgcd
                  left outer join
                        product_serchGroup as psc
                  on gnm.mhggp=psc.group_no
                  left outer join
                        product_top_serchgroup as pts
                  on psc.top_code=pts.top_no
                  %s
                  group by pts.top_no
                  order by pts.top_no
                  ", $search);   // ���� $search �Ǹ���
$res_k   = array();
$field = array();
if (($rows_k = getResultWithField3($query_k, $field, $res_k)) <= 0) {
    $_SESSION['s_sysmsg'] .= sprintf("<font color='yellow'>������٤Υǡ���������ޤ���<br>%s��%s</font>", format_date($d_start), format_date($d_end) );
    header('Location: ' . H_WEB_HOST . $menu->out_RetUrl());    // ľ���θƽи������
    exit();
} else {
    $num = count($field);       // �ե�����ɿ�����
}
///////////// �Ȳ����¤��ؤ�
$query_o = sprintf("select
                        top_no as ��ʬ��No,                  -- 0
                        top_name as ��ʬ��̾,                -- 1
                        s_order as �ȹ��                    -- 2
                  from
                        product_top_serchgroup
                  order by s_order
                  ");   
$res_o   = array();
$field_o = array();
if (($rows_o = getResultWithField3($query_o, $field_o, $res_o)) <= 0) {
    $_SESSION['s_sysmsg'] .= sprintf("<font color='yellow'>�ȹ���ʬ�ब��Ͽ����Ƥ��ޤ���");
    header('Location: ' . H_WEB_HOST . $menu->out_RetUrl());    // ľ���θƽи������
    exit();
} else {
    $num_o = count($field_o);       // �ե�����ɿ�����
    $data_top_t = 0;
    $view_data = array();
    for ($i=0; $i<$rows_o; $i++) {
        $data_top[$i][0] = '';
        $data_top[$i][1] =  0;
        $data_top[$i][2] = '';
        for ($r=0; $r<$rows_k; $r++) {
            if ($res_o[$i][0] == $res_k[$r][1]) {
                $data_top[$i][0] = $res_o[$i][1];
                $data_top[$i][1] = $res_k[$r][0];
                $data_top[$i][2] = $res_k[$r][1];
                $data_top[$i][3] = $res_o[$i][0];
                $data_top_t      += $res_k[$r][0];
            }
        }
    }
}

function get_middle_data($top_code, $search_middle, $result, $data_middle_t) {
    $search_middle .= " and psc.top_code='$top_code'";
    $query_m = sprintf("select
                        sum(Uround(����*ñ��,0)) as ���,       -- 0
                        psc.group_no as ��ʬ��No                -- 1
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
                  on p.mhshc=gnm.mhgcd
                  left outer join
                        product_serchGroup as psc
                  on gnm.mhggp=psc.group_no
                  %s
                  group by psc.group_no
                  order by psc.group_no
                  ", $search_middle);   // ���� $search �Ǹ���
    $field_m = array();
    if (($rows_m = getResultWithField3($query_m, $field_m, $res_m)) <= 0) {
        //$_SESSION['s_sysmsg'] .= sprintf("<font color='yellow'>��ʬ�ब��Ͽ����Ƥ��ޤ���</font>");
        //header('Location: ' . H_WEB_HOST . $menu->out_RetUrl());    // ľ���θƽи������
        //exit();
    } else {
        $num_m = count($res_m);       // �ǡ���������
        for ($r=0; $r<$rows_m; $r++) {
            $group_no = $res_m[$r][1];
            $search_c = "where group_no='$group_no'";
            $query_c = sprintf("select
                            group_name as ��ʬ��̾                  -- 0
                    from
                            product_serchGroup
                    %s
                    LIMIT 1
                    ",  $search_c);   
            $res_c   = array();
            $field_c = array();
            if (($rows_c = getResultWithField3($query_c, $field_c, $res_c)) <= 0) {
                $group_name[$r] = '';
            } else {
                $group_name[$r] = $res_c[0][0];
            }
        }
        $data_middle_sum = 0;
        for ($r=0; $r<$rows_m; $r++) {
            $res_m[$r][2]     = $group_name[$r];
            $data_middle_sum += $res_m[$r][0];
        }
        $data_middle_t += $data_middle_sum;
        $result->add_array2('data_middle', $res_m);
        $result->add('num_m', $num_m);
        $result->add('data_middle_sum', $data_middle_sum);
        $result->add('data_middle_t', $data_middle_t);
    }
}

function get_middle_rate($section, $search_rate, $result, $cost1_ym) {
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
    $search_rate .= " and gnm.mhggp='$section'";
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
                            END                      AS ��������        --11
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
                      ", $search_rate);   // ���� $search �Ǹ���
    $res_t   = array();
    $field_t = array();
    if (($rows_t = getResultWithField3($query_t, $field_t, $res_t)) <= 0) {
        //$_SESSION['s_sysmsg'] .= sprintf("<font color='yellow'>������٤Υǡ���������ޤ���<br>%s��%s</font>", format_date($d_start), format_date($d_end) );
        //header('Location: ' . H_WEB_HOST . $menu->out_RetUrl());    // ľ���θƽи������
        //exit();
        $result->add('cost_rate', '---');
        $result->add('diff_rate', '---');
        $result->add('diff_cost_total', '---');
        $result->add('diff_cost_ave', '---');
        $result->add('diff_kin_total', '---');
    } else {
        $num_t           = count($field_t);     // �ե�����ɿ�����
        $diff_kin_total   = 0;                   // ��������ȡ�����
        $diff_cost_total  = 0;                   // ñ�������ȡ�����
        $diff_cost_ave    = 0;                   // ñ��������ʿ��
        $diff_cost_count  = 0;                   // ñ��������ʿ���ѥ����󥿡�
        $diff_cost_sum    = 0;                   // ñ��������ʿ���ѥȡ�����
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
    $f_cost_rate   = number_format($cost_rate, 2);
    $f_diff_rate   = number_format($diff_rate, 2);
    $f_diff_cost_t = number_format($diff_cost_total, 2);
    $f_diff_cost_a = number_format($diff_cost_ave, 2);
    $f_diff_kin_t  = number_format($diff_kin_total, 0);
    $result->add('cost_rate', $f_cost_rate);
    $result->add('diff_rate', $f_diff_rate);
    $result->add('diff_cost_total', $f_diff_cost_t);
    $result->add('diff_cost_ave', $f_diff_cost_a);
    $result->add('diff_kin_total', $f_diff_kin_t);
}

function get_middle_total($top_code, $search_total, $result, $cost1_ym) {
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
    $search_total .= " and psc.top_code='$top_code'";
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
                            END                      AS ��������        --11
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
                      left outer join
                        product_serchGroup as psc
                      on gnm.mhggp=psc.group_no
                      %s
                      order by �׾���, assyno
                      ", $search_total);   // ���� $search �Ǹ���
    $res_t   = array();
    $field_t = array();
    if (($rows_t = getResultWithField3($query_t, $field_t, $res_t)) <= 0) {
        //$_SESSION['s_sysmsg'] .= sprintf("<font color='yellow'>������٤Υǡ���������ޤ���<br>%s��%s</font>", format_date($d_start), format_date($d_end) );
        //header('Location: ' . H_WEB_HOST . $menu->out_RetUrl());    // ľ���θƽи������
        //exit();
        $result->add('cost_rate', '---');
        $result->add('diff_rate', '---');
        $result->add('diff_cost_total', '---');
        $result->add('diff_cost_ave', '---');
        //exit();
    } else {
        $num_t           = count($field_t);     // �ե�����ɿ�����
        $diff_kin_total  = 0;                   // ��������ȡ�����
        $diff_cost_total = 0;                   // ñ�������ȡ�����
        $diff_cost_ave   = 0;                   // ñ������ʿ��
        $diff_cost_count = 0;                   // ñ������ʿ���ѥ����󥿡�
        $diff_cost_sum   = 0;                   // ñ������ʿ���ѥȡ�����
        $cost_rate       = 0;                   // ��Ψʿ��
        $cost_rate_count = 0;                   // ��Ψʿ���ѥ����󥿡�
        $cost_rate_sum   = 0;                   // ��Ψʿ���ѥȡ�����
        $diff_rate       = 0;                   // ñ������Ψʿ��
        $diff_rate_count = 0;                   // ñ������Ψʿ���ѥ����󥿡�
        $diff_rate_sum   = 0;                   // ñ������Ψʿ���ѥȡ�����
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
        $f_cost_rate   = number_format($cost_rate, 2);
        $f_diff_rate   = number_format($diff_rate, 2);
        $f_diff_cost_t = number_format($diff_cost_total, 2);
        $f_diff_cost_a = number_format($diff_cost_ave, 2);
        $f_diff_kin_t  = number_format($diff_kin_total, 0);
        $result->add('cost_rate', $f_cost_rate);
        $result->add('diff_rate', $f_diff_rate);
        $result->add('diff_cost_total', $f_diff_cost_t);
        $result->add('diff_cost_ave', $f_diff_cost_a);
        $result->add('diff_kin_total', $f_diff_kin_t);
    }
}

function get_middle_all($search_all, $result, $cost1_ym) {
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
                            END                      AS ��������        --11
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
                      left outer join
                        product_serchGroup as psc
                      on gnm.mhggp=psc.group_no
                      %s
                      order by �׾���, assyno
                      ", $search_all);   // ���� $search �Ǹ���
    $res_t   = array();
    $field_t = array();
    if (($rows_t = getResultWithField3($query_t, $field_t, $res_t)) <= 0) {
        //$_SESSION['s_sysmsg'] .= sprintf("<font color='yellow'>������٤Υǡ���������ޤ���<br>%s��%s</font>", format_date($d_start), format_date($d_end) );
        //header('Location: ' . H_WEB_HOST . $menu->out_RetUrl());    // ľ���θƽи������
        //exit();
        $result->add('cost_rate', '---');
        $result->add('diff_rate', '---');
        $result->add('diff_cost_total', '---');
        $result->add('diff_cost_ave', '---');
        //exit();
    } else {
        $num_t           = count($field_t);     // �ե�����ɿ�����
        $diff_kin_total  = 0;                   // ��������ȡ�����
        $diff_cost_total = 0;                   // ñ�������ȡ�����
        $diff_cost_ave   = 0;                   // ñ������ʿ��
        $diff_cost_count = 0;                   // ñ������ʿ���ѥ����󥿡�
        $diff_cost_sum   = 0;                   // ñ������ʿ���ѥȡ�����
        $cost_rate       = 0;                   // ��Ψʿ��
        $cost_rate_count = 0;                   // ��Ψʿ���ѥ����󥿡�
        $cost_rate_sum   = 0;                   // ��Ψʿ���ѥȡ�����
        $diff_rate       = 0;                   // ñ������Ψʿ��
        $diff_rate_count = 0;                   // ñ������Ψʿ���ѥ����󥿡�
        $diff_rate_sum   = 0;                   // ñ������Ψʿ���ѥȡ�����
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
        $f_cost_rate  = number_format($cost_rate, 2);
        $f_diff_rate  = number_format($diff_rate, 2);
        $f_diff_cost_t = number_format($diff_cost_total, 2);
        $f_diff_cost_a = number_format($diff_cost_ave, 2);
        $f_diff_kin_t = number_format($diff_kin_total, 0);
        $result->add('cost_rate', $f_cost_rate);
        $result->add('diff_rate', $f_diff_rate);
        $result->add('diff_cost_total', $f_diff_cost_t);
        $result->add('diff_cost_ave', $f_diff_cost_a);
        $result->add('diff_kin_total', $f_diff_kin_t);
    }
}

///// ���ʥ��롼��(������)̾������
if ($div == "A") $div_name  = "�����롼��";
if ($div == "C") $div_name  = "ɸ����";
if ($div == "CC") $div_name = "���ץ�ɸ����";
if ($div == "CL") $div_name = "��˥�ɸ����";
if ($div == "S") $div_name  = "������";
//////////// ɽ�������
$ft_kingaku = number_format($data_top_t);                    // ���头�ȤΥ���ޤ��ղ�
//$ft_ken     = number_format($t_ken);
//$ft_kazu    = number_format($t_kazu);
$f_d_start  = format_date($d_start);                        // ���դ� / �ǥե����ޥå�
$f_d_end    = format_date($d_end);
$f_first_ym = format_date6($first_ym);
$menu->set_caption("���롼�� {$div_name} : �о�ǯ�� {$f_d_start}��{$f_d_end}�����ǯ�� {$f_first_ym}����׶��={$ft_kingaku}");
//$menu->set_caption("�о�ǯ�� {$f_d_start}��{$f_d_end}����׷��={$ft_ken}����׶��={$ft_kingaku}����׿���={$ft_kazu}<u>");
// SQL�Υ������������ܸ��ѻ����ѹ���'�⥨�顼�ˤʤ�Τ�/�˰���ѹ�
$csv_search = str_replace('�׾���','keidate',$search);
$csv_search = str_replace('������','jigyou',$search);
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
function framePrint() {
    //page_form.focus();
    print();
}
function PrintPreview()
{
    if(window.ActiveXObject == null || document.body.insertAdjacentHTML == null) return;
    var sWebBrowserCode = '<object width="0" height="0" classid="CLSID:8856F961-340A-11D0-A96B-00C04FD705A2"></object>'; 
    document.body.insertAdjacentHTML('beforeEnd', sWebBrowserCode);
    var objWebBrowser = document.body.lastChild;
    if(objWebBrowser == null) return;
    objWebBrowser.ExecWB(7, 1);
    document.body.removeChild(objWebBrowser);
}
-->
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
    font-weight:    bold;
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
.winboxb {
    border-style: solid;
    border-width: 1px;
    border-top-color:       #FFFFFF;
    border-left-color:      #FFFFFF;
    border-right-color:     #bdaa90;
    border-bottom-color:    #bdaa90;
    background-color:       #ccffff;
}
.winboxg {
    border-style: solid;
    border-width: 1px;
    border-top-color:       #FFFFFF;
    border-left-color:      #FFFFFF;
    border-right-color:     #bdaa90;
    border-bottom-color:    #bdaa90;
    background-color:       #ccffcc;
}
.winboxy {
    border-style: solid;
    border-width: 1px;
    border-top-color:       #FFFFFF;
    border-left-color:      #FFFFFF;
    border-right-color:     #bdaa90;
    border-bottom-color:    #bdaa90;
    background-color:       yellow;
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
<style media=print>
<!--
/*�֥饦���Τ�ɽ��*/
.dspOnly {
    display:none;
}
.footer {
    display:none;
}
// -->
</style>
</head>
    <center>
    <div class='dspOnly'>
    <?php echo $menu->out_title_border()?>
    </div>
        <!----------------- ������ ���� ���� �Υե����� ---------------->
        <table width='100%' border='0' cellspacing='0' cellpadding='0'>
            <form name='page_form' method='post' action='<?php echo $menu->out_self() ?>'>
                <tr>
                    <td nowrap align='center' class='caption_font'>
                        <?php echo $menu->out_caption(), "\n" ?>
                    </td>
                    <td align='center' class='dspOnly'>
                        <input type="button" name="print" value="����" onclick="PrintPreview()">
                    </td>
                </tr>
            </form>
        </table>
        
        <!--------------- ����������ʸ��ɽ��ɽ������ -------------------->
        <table bgcolor='#d6d3ce' align='center' border='1' cellspacing='0' cellpadding='3'>
            <tr><td> <!----------- ���ߡ�(�ǥ�������) ------------>
        <table class='winbox_field' width='100%' bgcolor='#FFFFFF' align='center' border='1' cellspacing='0' cellpadding='3'>
            <thead>
                <!-- �ơ��֥� �إå�����ɽ�� -->
                <tr>
                    <!--
                    <th class='winbox' nowrap width='10'>No.</th>        <!-- �ԥʥ�С���ɽ�� -->
                    <th class='winbox' nowrap><div class='pt10b'><?php echo $field[1] ?></div></th>
                    <th class='winbox' nowrap><div class='pt10b'><?php echo $field[0] ?></div></th>
                    <th class='winbox' nowrap><div class='pt10b'>��ʬ��̾</div></th>
                    <th class='winbox' nowrap><div class='pt10b'>���</div></th>
                    <th class='winbox' nowrap><div class='pt10b'>ñ��<BR>������</div></th>
                    <th class='winbox' nowrap><div class='pt10b'>ñ��<BR>����ʿ��</div></th>
                    <th class='winbox' nowrap><div class='pt10b'>ñ������<BR>Ψ��ʿ��</div></th>
                    <th class='winbox' nowrap><div class='pt10b'>���<BR>������</div></th>
                    <th class='winbox' nowrap><div class='pt10b'>��Ψ<BR>ʿ��</div></th>
                </tr>
            </thead>
            <tfoot>
                <!-- ���ߤϥեå����ϲ���ʤ� -->
            </tfoot>
            <tbody>
                <?php
                $data_middle_t = 0;
                for ($r=0; $r<$rows_o; $r++) {
                    $flg_gu = ' ';
                    $check_gu = $r % 2;
                    if ($check_gu == 0) {
                        $flg_gu = '1';
                    }
                    if($data_top[$r][1] != 0) {
                        get_middle_data($data_top[$r][2], $search, $result, $data_middle_t);
                        $data_middle_t = $result->get('data_middle_t');
                        $num_m           = $result->get('num_m');
                        $data_middle     = $result->get_array2('data_middle');
                        $data_middle_sum = $result->get('data_middle_sum');
                        $num_m2      = $num_m + 1;
                        $assy_no = '';
                        echo "<tr>\n";
                        //echo "  <td rowspan = '" . $num_m2 . "' class='winbox' nowrap align='right'><div class='pt10b'>" . ($r + 1) . "</div></td>    <!-- �ԥʥ�С���ɽ�� -->\n";
                        if ($flg_gu == '1') {
                            echo "  <td rowspan = '" . $num_m2 . "' class='winboxb' nowrap align='left'><div class='pt10b'>" . $data_top[$r][0] . "</div></td>\n";
                            echo "  <td rowspan = '" . $num_m2 . "' class='winboxb' nowrap align='right'><div class='pt10b'>" . number_format($data_top[$r][1], 0) . "</div></td>\n";
                        } else {
                            echo "  <td rowspan = '" . $num_m2 . "' class='winboxg' nowrap align='left'><div class='pt10b'>" . $data_top[$r][0] . "</div></td>\n";
                            echo "  <td rowspan = '" . $num_m2 . "' class='winboxg' nowrap align='right'><div class='pt10b'>" . number_format($data_top[$r][1], 0) . "</div></td>\n";
                        }
                        get_middle_rate($data_middle[0][1], $search, $result, $first_ym);
                        $cost_rate       = $result->get('cost_rate');
                        $diff_rate       = $result->get('diff_rate');
                        $diff_cost_total = $result->get('diff_cost_total');
                        $diff_cost_ave   = $result->get('diff_cost_ave');
                        $diff_kin_total  = $result->get('diff_kin_total');
                        echo "  <td class='winbox' nowrap align='left'><div class='pt9'>" . $data_middle[0][2] . "</div></td>\n";
                        echo "  <td class='winbox' nowrap align='right'><div class='pt9'>
                                <a class='pt9' href='JavaScript:baseJS.Ajax(\"{$menu->out_self()}\");location.replace(\"", $menu->out_action('�������'), "?uri_passwd={$uri_passwd}&d_start={$d_start}&d_end={$d_end}&first_ym={$first_ym}&kubun={$kubun}&section={$data_middle[0][1]}&uri_ritu=52&sales_page=25&assy_no={$assy_no}&div={$div}\")' target='application' style='text-decoration:none;'>"
                                . number_format($data_middle[0][0], 0) . "</div></td>\n";
                        echo "  <td class='winbox' nowrap align='right'><div class='pt9'>" . $diff_cost_total . "</div></td>\n";
                        echo "  <td class='winbox' nowrap align='right'><div class='pt9'>" . $diff_cost_ave . "</div></td>\n";
                        echo "  <td class='winbox' nowrap align='right'><div class='pt9'>" . $diff_rate . "</div></td>\n";
                        echo "  <td class='winbox' nowrap align='right'><div class='pt9'>" . $diff_kin_total . "</div></td>\n";
                        if ($cost_rate > $power_rate) {
                            echo "  <td class='winbox' nowrap align='right'><div class='pt9'><font color='blue'>" . $cost_rate . "</font></div></td>\n";
                        } elseif ($cost_rate < $power_rate) {
                            echo "  <td class='winbox' nowrap align='right'><div class='pt9'><font color='red'>" . $cost_rate . "</font></div></td>\n";
                        } else {
                            echo "  <td class='winbox' nowrap align='right'><div class='pt9'>" . $cost_rate . "</div></td>\n";
                        }
                        echo "</tr>\n";
                        for ($i=1; $i<$num_m; $i++) {
                            get_middle_rate($data_middle[$i][1], $search, $result, $first_ym);
                            $cost_rate       = $result->get('cost_rate');
                            $diff_rate       = $result->get('diff_rate');
                            $diff_cost_total = $result->get('diff_cost_total');
                            $diff_cost_ave   = $result->get('diff_cost_ave');
                            $diff_kin_total  = $result->get('diff_kin_total');
                            echo "<tr>\n";
                            echo "  <td class='winbox' nowrap align='left'><div class='pt9'>" . $data_middle[$i][2] . "</div></td>\n";
                            echo "  <td class='winbox' nowrap align='right'><div class='pt9'>
                                    <a class='pt9' href='JavaScript:baseJS.Ajax(\"{$menu->out_self()}\");location.replace(\"", $menu->out_action('�������'), "?uri_passwd={$uri_passwd}&d_start={$d_start}&d_end={$d_end}&first_ym={$first_ym}&kubun={$kubun}&section={$data_middle[$i][1]}&uri_ritu=52&sales_page=25&assy_no={$assy_no}&div={$div}\")' target='application' style='text-decoration:none;'>"
                                    . number_format($data_middle[$i][0], 0) . "</div></td>\n";
                            echo "  <td class='winbox' nowrap align='right'><div class='pt9'>" . $diff_cost_total . "</div></td>\n";
                            echo "  <td class='winbox' nowrap align='right'><div class='pt9'>" . $diff_cost_ave . "</div></td>\n";
                            echo "  <td class='winbox' nowrap align='right'><div class='pt9'>" . $diff_rate . "</div></td>\n";
                            echo "  <td class='winbox' nowrap align='right'><div class='pt9'>" . $diff_kin_total . "</div></td>\n";
                            if ($cost_rate > $power_rate) {
                                echo "  <td class='winbox' nowrap align='right'><div class='pt9'><font color='blue'>" . $cost_rate . "</font></div></td>\n";
                            } elseif ($cost_rate < $power_rate) {
                                echo "  <td class='winbox' nowrap align='right'><div class='pt9'><font color='red'>" . $cost_rate . "</font></div></td>\n";
                            } else {
                                echo "  <td class='winbox' nowrap align='right'><div class='pt9'>" . $cost_rate . "</div></td>\n";
                            }
                            echo "</tr>\n";
                        }
                        echo "<tr>\n";
                        if ($flg_gu == '1') {
                            get_middle_total($data_top[$r][3], $search, $result, $first_ym);
                            $cost_rate       = $result->get('cost_rate');
                            $diff_rate       = $result->get('diff_rate');
                            $diff_cost_total = $result->get('diff_cost_total');
                            $diff_cost_ave   = $result->get('diff_cost_ave');
                            $diff_kin_total  = $result->get('diff_kin_total');
                            echo "  <td class='winboxb' nowrap align='left'><div class='pt9b'>����</div></td>\n";
                            echo "  <td class='winboxb' nowrap align='right'><div class='pt9b'>" . number_format($data_middle_sum, 0) . "</div></td>\n";
                            echo "  <td class='winboxb' nowrap align='right'><div class='pt9b'>" . $diff_cost_total . "</div></td>\n";
                            echo "  <td class='winboxb' nowrap align='right'><div class='pt9b'>" . $diff_cost_ave . "</div></td>\n";
                            echo "  <td class='winboxb' nowrap align='right'><div class='pt9b'>" . $diff_rate . "</div></td>\n";
                            echo "  <td class='winboxb' nowrap align='right'><div class='pt9b'>" . $diff_kin_total . "</div></td>\n";
                            if ($cost_rate > $power_rate) {
                                echo "  <td class='winboxb' nowrap align='right'><div class='pt9b'><font color='blue'>" . $cost_rate . "</font></div></td>\n";
                            } elseif ($cost_rate < $power_rate) {
                                echo "  <td class='winboxb' nowrap align='right'><div class='pt9b'><font color='red'>" . $cost_rate . "</font></div></td>\n";
                            } else {
                                echo "  <td class='winboxb' nowrap align='right'><div class='pt9b'>" . $cost_rate . "</div></td>\n";
                            }
                        } else {
                            get_middle_total($data_top[$r][3], $search, $result, $first_ym);
                            $cost_rate       = $result->get('cost_rate');
                            $diff_rate       = $result->get('diff_rate');
                            $diff_cost_total = $result->get('diff_cost_total');
                            $diff_cost_ave   = $result->get('diff_cost_ave');
                            $diff_kin_total  = $result->get('diff_kin_total');
                            echo "  <td class='winboxg' nowrap align='left'><div class='pt9b'>����</div></td>\n";
                            echo "  <td class='winboxg' nowrap align='right'><div class='pt9b'>" . number_format($data_middle_sum, 0) . "</div></td>\n";
                            echo "  <td class='winboxg' nowrap align='right'><div class='pt9b'>" . $diff_cost_total . "</div></td>\n";
                            echo "  <td class='winboxg' nowrap align='right'><div class='pt9b'>" . $diff_cost_ave . "</div></td>\n";
                            echo "  <td class='winboxg' nowrap align='right'><div class='pt9b'>" . $diff_rate . "</div></td>\n";
                            echo "  <td class='winboxg' nowrap align='right'><div class='pt9b'>" . $diff_kin_total . "</div></td>\n";
                            if ($cost_rate > $power_rate) {
                                echo "  <td class='winboxg' nowrap align='right'><div class='pt9b'><font color='blue'>" . $cost_rate . "</font></div></td>\n";
                            } elseif ($cost_rate < $power_rate) {
                                echo "  <td class='winboxg' nowrap align='right'><div class='pt9b'><font color='red'>" . $cost_rate . "</font></div></td>\n";
                            } else {
                                echo "  <td class='winboxg' nowrap align='right'><div class='pt9b'>" . $cost_rate . "</div></td>\n";
                            }
                            
                        }
                        echo "</tr>\n";
                    }
                }
                ?>
            </tbody>
            <tr>
                <?php
                get_middle_all($search, $result, $first_ym);
                $cost_rate       = $result->get('cost_rate');
                $diff_rate       = $result->get('diff_rate');
                $diff_cost_total = $result->get('diff_cost_total');
                $diff_cost_ave   = $result->get('diff_cost_ave');
                $diff_kin_total  = $result->get('diff_kin_total');
                ?>
                <td class='winboxy' nowrap align='left'><div class='pt10b'>��ʬ���</div></td>
                <td class='winboxy' nowrap align='right'><div class='pt10b'><?php echo number_format($data_top_t, 0) ?></div></td>
                <td class='winboxy' nowrap align='left'><div class='pt10b'>��ʬ���</div></td>
                <td class='winboxy' nowrap align='right'><div class='pt10b'><?php echo number_format($data_middle_t, 0) ?></div></td>
                <td class='winboxy' nowrap align='right'><div class='pt10b'><?php echo $diff_cost_total ?></div></td>
                <td class='winboxy' nowrap align='right'><div class='pt10b'><?php echo $diff_cost_ave ?></div></td>
                <td class='winboxy' nowrap align='right'><div class='pt10b'><?php echo $diff_rate ?></div></td>
                <td class='winboxy' nowrap align='right'><div class='pt10b'><?php echo $diff_kin_total ?></div></td>
                <?php
                if ($cost_rate > $power_rate) {
                ?>
                    <td class='winboxy' nowrap align='right'><div class='pt10b'><font color='blue'><?php echo $cost_rate ?></font></div></td>
                <?php
                } elseif ($cost_rate < $power_rate) {
                ?>
                    <td class='winboxy' nowrap align='right'><div class='pt10b'><font color='red'><?php echo $cost_rate ?></font></div></td>
                <?php
                } else {
                ?>
                    <td class='winboxy' nowrap align='right'><div class='pt10b'><?php echo $cost_rate ?></div></td>
                <?php
                }
                ?>
             </tr>
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
