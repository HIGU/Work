<?php
//////////////////////////////////////////////////////////////////////////////
// ��� ���� �Ȳ�  new version   sales_view.php                             //
// Copyright (C) 2001-2007 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp      //
// Changed history                                                          //
// 2001/07/07 Created   sales_view.php                                      //
// 2002/08/07 ���å����������ɲ�                                          //
// 2002/09/26 selectʸ�� left outer join on u.assyno=m.mipn ���ѹ�          //
// 2003/01/10 substr($res[$r][$n],0,38)��mb_substr($res[$r][$n],0,12)       //
//                   �ޥ���Х��Ȥ��б�������X���β�����˼����            //
// 2003/06/16 ��׶�ۡ���������̤� SQL �Ǽ��� ���٤ϣ��ڡ���ʬ�Τ�        //
//              ������ Logic ���������ѹ�   �������˥Х������ɲ�          //
// 2003/09/05 ����ñ������Ͽ�����ξ����θ�������å����ѹ�              //
//            ����������Ͽ�����ξ���Ʊ��(�����б��Ѥ�)                  //
//            error_reporting = E_ALL �б��Τ��� �����ѿ��ν�����ɲ�       //
// 2003/10/31 ���� �����ֹ� ���� �ɲ�  �������˥��ץ�������ɲ�             //
// 2003/11/26 �ǥ�����ȥ��å���쿷 view_uriage.php �� sales_view.php    //
// 2003/11/28 �������������ʤ��ɲ� left outer��assymbly���Ф���join��     //
//            on���� plan_no�����ǹԤ� index�� plan_no �������ѹ�         //
// 2003/12/11 ������ξ�������̾ width='150' �� width='170' ���ѹ�        //
// 2003/12/12 define���줿����ǥǥ��쥯�ȥ�ȥ�˥塼����Ѥ���������      //
// 2003/12/17 ��������������Υ����å����å����ɲ� (�������������)     //
// 2003/12/19 �������Ȳ�Υ�󥯥��å������ ���ߤϣ�����Τ�           //
//            $_SESSION['offset']��$_SESSION['sales_offset']��  �����Ǥ����//
// 2003/12/22 ����̾�����ѥ��ʱѿ�����Ⱦ�ѥ��ʱѿ�����testŪ�˥���С���    //
//            ������ʳ����������Ψ �Ȳ�Υ�󥯥��å������           //
// 2003/12/23 ����ñ����Ψ �ڤ� �������Ψ �����ξ��� '-'���Ѵ�����ɽ�� //
// 2003/12/24 ob_gzhandler��� ���Ѥ���ȣ��ǣ�������λ���GET�����ʤ�����//
//            order by �׾��� �� , assyno���ɲ� ���ǤιԿ����ѹ����Ƥ� OK   //
// 2004/05/12 �����ȥ�˥塼ɽ������ɽ�� �ܥ����ɲ� menu_OnOff($script)�ɲ� //
// 2004/11/01 ����ʳ�����������ײ��ֹ����Ͽ���ʤ���кǸ����Ͽ��Ȥ�  //
// 2004/11/09 ����������롼�ס����ץ����Ρ�����ɸ�ࡦ��˥���������ʬ����//
// 2005/01/14 MenuHeader class ����Ѥ��ƶ��̥�˥塼���ڤ�ǧ���������ѹ�   //
//              set_focus()�� document.focus()��Ȥ� F2/F12������ͭ���ˤ��� //
// 2005/02/01 ��������mate.sum_price��0��ʪ������ײ��ֹ�=C1261631�����б�//
//             mate.sum_price <= 0    ����Ū�ˤ����ʤϻٵ��ʤ�������Ω��Τ�//
//                     ��                                                   //
//            (mate.sum_price + Uround(mate.assy_time * assy_rate, 2)) <= 0 //
// 2005/05/27 PAGE > 25 �ˤ�� style='overflow:hidden;' ��������ɲ�        //
// 2005/06/03 regdate DESC �� assy_no DESC, regdate DESC ��index�ѹ��ˤ��  //
// 2005/09/06 ���롼��(������)��̵���Τ⤬����Τǥ����å������褦���ɲ�  //
// 2005/09/21 ���ե����å��θ����Ѥ�checkdate(month, day, year)�����       //
// 2006/01/24 WHEN m.midsc IS NULL THEN '&nbsp;' ���ɲ�                     //
// 2006/02/01 ����ʳ��ξȲ�������ʤκ������ɽ����Ψ���ɲ� 105̤�����ֻ�  //
//            parts_cost_history ������ ��³�Τߤˤ������kubun=1���ɲ� //
// 2006/02/02 �嵭�Υ�����ñ����Ͽ�Ȳ��ɲ� &reg_no��ʸ��������& reg_no  //
// 2006/02/12 ���ʤκ��������SQLʸ�� SUB��JOIN ���ѹ������ԡ��ɥ��å�      //
// 2006/03/22 ����������Υ�󥯤򥯥�å�������ä����˹ԥޡ������ɲ�      //
// 2006/09/21 sales/details �ǥ��쥯�ȥ�β��˺�����                        //
// 2007/04/18 2007/04/02�����Ǥ��������ɽ��(�������ñ�����åפΤ���)      //
// 2007/09/20 ɸ���ʤκǿ���������ɽ���Ѥ� sales_view_simulate4.php �����//
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
$menu->set_site( 1, 11);                    // site_index=01(����˥塼) site_id=11(����������)
////////////// �꥿���󥢥ɥ쥹����
// $menu->set_RetUrl(SYS_MENU);                // �̾�ϻ��ꤹ��ɬ�פϤʤ�
//////////// �����ȥ�̾(�������Υ����ȥ�̾�ȥե�����Υ����ȥ�̾)
$menu->set_title('������� ɸ���ʤκǿ�����������奷�ߥ�졼�����');
//////////// �ƽ����action̾�ȥ��ɥ쥹����
$menu->set_action('�������Ȳ�',   INDUST . 'material/materialCost_view.php');
$menu->set_action('ñ����Ͽ�Ȳ�',   INDUST . 'parts/parts_cost_view.php');

//////////// �֥饦�����Υ���å����к���
$uniq = $menu->set_useNotCache('target');

//////////// �����Υ��å����ǡ�����¸   ���ǡ����Ǥ�ڤ����뤿��
if (! (isset($_POST['forward']) || isset($_POST['backward']) || isset($_GET['page_keep'])) ) {
    $session->add_local('recNo', '-1');         // 0�쥳���ɤǥޡ�����ɽ�����Ƥ��ޤ�������б�
    $_SESSION['s_uri_passwd'] = $_POST['uri_passwd'];
    $_SESSION['s_div']        = $_POST['div'];
    $_SESSION['s_d_start']    = $_POST['d_start'];
    $_SESSION['s_d_end']      = $_POST['d_end'];
    $_SESSION['s_kubun']      = $_POST['kubun'];
    $_SESSION['s_uri_ritu']   = $_POST['uri_ritu'];
    $_SESSION['s_sales_page'] = $_POST['sales_page'];
    $_SESSION['uri_assy_no']  = $_POST['assy_no'];
    $uri_passwd = $_SESSION['s_uri_passwd'];
    $div        = $_SESSION['s_div'];
    $d_start    = $_SESSION['s_d_start'];
    $d_end      = $_SESSION['s_d_end'];
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
    if ( ($div != 'S') && ($div != 'D') ) {      // �������ɸ�� �ʳ��ʤ�
        $query = "select
                        count(����) as t_ken,
                        sum(����) as t_kazu,
                        sum(Uround(����*ñ��,0)) as t_kingaku
                  from
                        hiuuri";
    } else {
        $query = "select
                        count(����) as t_ken,
                        sum(����) as t_kazu,
                        sum(Uround(����*ñ��,0)) as t_kingaku
                  from
                        hiuuri
                  left outer join
                        assembly_schedule as a
                  on �ײ��ֹ�=plan_no";
                  //left outer join
                  //      aden_master as aden
                  //on (a.parts_no=aden.parts_no and a.plan_no=aden.plan_no)";
    }
    //////////// SQL where ��� ���Ѥ���
    $search = "where �׾���>=$d_start and �׾���<=$d_end";
    if ($assy_no != '') {       // �����ֹ椬���ꤵ�줿���
        $search .= " and assyno like '{$assy_no}%%'";
    } elseif ($div == 'S') {    // ������ʤ�
        $search .= " and ������='C' and note15 like 'SC%%'";
    } elseif ($div == 'D') {    // ��ɸ��ʤ�
        $search .= " and ������='C' and (note15 NOT like 'SC%%' OR note15 IS NULL)";    // ��������ɸ��ؤ���
    } elseif ($div == "N") {    // ��˥��ΥХ�������� assyno �ǥ����å�
        $search .= " and ������='L' and (assyno NOT like 'LC%%' AND assyno NOT like 'LR%%')";
    } elseif ($div == "B") {    // �Х����ξ��� assyno �ǥ����å�
        $search .= " and (assyno like 'LC%%' or assyno like 'LR%%')";
    } elseif ($div == "_") {    // �������ʤ�
        $search .= " and ������=' '";
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
$div        = $_SESSION['s_div'];
$d_start    = $_SESSION['s_d_start'];
$d_end      = $_SESSION['s_d_end'];
$kubun      = $_SESSION['s_kubun'];
$uri_ritu   = $_SESSION['s_uri_ritu'];
$assy_no    = $_SESSION['uri_assy_no'];
$search     = $_SESSION['sales_search'];

///// ���ʥ��롼��(������)̾������
if ($div == " ") $div_name = "�����롼��";
if ($div == "C") $div_name = "���ץ�����";
if ($div == "D") $div_name = "���ץ�ɸ��";
if ($div == "S") $div_name = "���ץ�����";
if ($div == "L") $div_name = "��˥�����";
if ($div == "N") $div_name = "��˥��Τ�";
if ($div == "B") $div_name = "�Х����Τ�";
if ($div == "T") $div_name = "�ġ���";
if ($div == "_") $div_name = "�ʤ�";

//////////// ɽ�������
$ft_kingaku = number_format($t_kingaku);                    // ���头�ȤΥ���ޤ��ղ�
$ft_ken     = number_format($t_ken);
$ft_kazu    = number_format($t_kazu);
$f_d_start  = format_date($d_start);                        // ���դ� / �ǥե����ޥå�
$f_d_end    = format_date($d_end);
$menu->set_caption("<u>����=<font color='red'>{$div_name}</font>��{$f_d_start}��{$f_d_end}����׷��={$ft_ken}����׶��={$ft_kingaku}����׿���={$ft_kazu}<u>");

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
if ($div != 'S') {      // ������ �ʳ��ʤ�
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
                            (SELECT sum_price + Uround(assy_time * assy_rate, 2) FROM material_cost_header WHERE assy_no = u.assyno ORDER BY assy_no DESC, regdate DESC LIMIT 1)
                                                    as �������,        -- 9
                            CASE
                                WHEN (SELECT sum_price + Uround(assy_time * assy_rate, 2) FROM material_cost_header WHERE assy_no = u.assyno order by assy_no DESC, regdate DESC LIMIT 1) <= 0 THEN 0
                                ELSE Uround(u.ñ�� / (SELECT sum_price + Uround(assy_time * assy_rate, 2) FROM material_cost_header WHERE assy_no = u.assyno order by assy_no DESC, regdate DESC LIMIT 1), 3) * 100
                            END                     as Ψ��,            --10
                            (select sum_price + Uround(assy_time * assy_rate, 2) from material_cost_header where assy_no=u.assyno AND regdate<=�׾��� order by assy_no DESC, regdate DESC limit 1)
                                                    AS �������2,       --11
                            (select Uround(u.ñ�� / (sum_price + Uround(assy_time * assy_rate, 2)), 3) * 100 from material_cost_header where assy_no=u.assyno AND regdate<=�׾��� order by assy_no DESC, regdate DESC limit 1)
                                                    AS Ψ��,            --12
                            (select plan_no from material_cost_header WHERE assy_no=u.assyno ORDER BY assy_no DESC, regdate DESC limit 1)
                                                    AS �ײ��ֹ�2,       --13
                            CASE
                                WHEN u.datatype <> 1 THEN
                                -- (SELECT sum(lot_cost) FROM parts_cost_history WHERE parts_no=u.assyno AND as_regdate<�׾��� AND lot_no=1 AND vendor!='88888' GROUP BY as_regdate, reg_no ORDER BY as_regdate DESC, reg_no DESC limit 1)
                                pmate.unit_cost
                                ELSE 0
                            END                     AS ���ʺ�����,      --14
                            CASE
                                WHEN u.datatype <> 1 THEN
                                -- (SELECT reg_no FROM parts_cost_history WHERE parts_no=u.assyno AND as_regdate<�׾��� AND lot_no=1 AND vendor!='88888' GROUP BY as_regdate, reg_no ORDER BY as_regdate DESC, reg_no DESC limit 1)
                                pmate.cost_reg
                                ELSE 0
                            END                     AS ñ����Ͽ�ֹ�     --15
                      FROM
                            hiuuri as u
                      LEFT OUTER JOIN
                            assembly_schedule as a
                      ON u.�ײ��ֹ�=a.plan_no
                      LEFT OUTER JOIN
                            miitem as m
                      ON u.assyno=m.mipn
                      LEFT OUTER JOIN
                            sales_parts_material_history AS pmate
                      ON (u.assyno=pmate.parts_no AND u.�׾���=pmate.sales_date)
                      %s
                      ORDER BY �׾���, assyno
                      OFFSET %d limit %d
                      ", $search, $offset, PAGE);   // ���� $search �Ǹ���
} else {    ////////////////////////////////////////// ������ξ��
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
                            CASE
                                WHEN m.midsc IS NULL THEN '&nbsp;'
                                ELSE substr(m.midsc,1,18)
                            END                     as ����̾,          -- 4
                            CASE
                                WHEN trim(u.���˾��)='' THEN '--'         --NULL�Ǥʤ��ƥ��ڡ�������ޤäƤ�����Ϥ��졪
                                ELSE u.���˾��
                            END                     as ����,            -- 5
                            u.����          as ����,                    -- 6
                            u.ñ��          as ����ñ��,                -- 7
                            Uround(u.���� * u.ñ��, 0) as ���,         -- 8
                            trim(a.note15)  as �����ֹ�,                -- 9
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
                            assembly_schedule as a
                      on u.�ײ��ֹ�=a.plan_no
                      left outer join
                            aden_master as aden
                      -- on (a.parts_no=aden.parts_no and a.plan_no=aden.plan_no)
                      on (a.plan_no=aden.plan_no)
                      left outer join
                            material_cost_header as mate
                      on u.�ײ��ֹ�=mate.plan_no
                      %s
                      order by �׾���, assyno
                      offset %d limit %d
                      ", $search, $offset, PAGE);   // ���� $search �Ǹ���
}
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
<body onLoad='set_focus()' style='overflow:hidden;'>
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
                                if ($res[$r][9]) {
                                    echo "<td class='winbox' nowrap width='60' align='right'>
                                            <a class='pt9' href='JavaScript:baseJS.Ajax(\"{$menu->out_self()}?recNo={$recNo}\");location.replace(\"", $menu->out_action('�������Ȳ�'), "?plan_no={$res[$r][13]}&assy_no={$res[$r][3]}\")' target='application' style='text-decoration:none; color:brown;'>"
                                            , number_format($res[$r][9], 2), "</a></td>\n";
                                } elseif ($res[$r][14]) {   // ���ʤκ����������å�����ɽ������
                                    echo "<td class='winbox' nowrap width='60' align='right'>
                                            <a class='pt9' href='JavaScript:baseJS.Ajax(\"{$menu->out_self()}?recNo={$recNo}\");location.replace(\"", $menu->out_action('ñ����Ͽ�Ȳ�'), "?parts_no=", urlencode($res[$r][3]), "& reg_no={$res[$r][15]}\")' target='application' style='text-decoration:none;'>"
                                            , number_format($res[$r][14], 2), "</a></td>\n";
                                } else {
                                    echo "<td class='winbox' nowrap width='60' align='right'><div class='pt9'>-</div></td>\n";
                                }
                                break;
                            case 10:    // Ψ(�������)
                                if ($res[$r][10]) {
                                    if ($res[$r][10] < 100.00) {   // �ֻ�ɽ����ʬ��
                                        echo "<td class='winbox' nowrap width='40' align='right'><div class='pt9' style='color:red;'>", number_format($res[$r][10], 1), "</div></td>\n";
                                    } else {
                                        echo "<td class='winbox' nowrap width='40' align='right'><div class='pt9'>", number_format($res[$r][10], 1), "</div></td>\n";
                                    }
                                } else {
                                    echo "<td class='winbox' nowrap width='40' align='right'><div class='pt9'>-</div></td>\n";
                                }
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
// ob_end_flush();                 // ���ϥХåե���gzip���� END
?>
