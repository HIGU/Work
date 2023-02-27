<?php
//////////////////////////////////////////////////////////////////////////////
// ��� ͽ�� �Ȳ�                                                           //
// Copyright (C) 2011-2018 Norihisa.Ohya norihisa_ooya@nitto-kohki.co.jp    //
// Changed history                                                          //
// 2011/11/21 Created   sales_plan_view.php                                 //
// 2011/11/30 ���ץ�ɸ��ȥ��ץ�����ˤ�NKCT��ޤޤʤ��褦���ѹ�            //
//            �����������ץ����Τˤϴޤࡣ�ޤ���˥��ΤߤȥХ�����        //
//            Ʊ�ͤ�NKT��ޤޤʤ��褦�ѹ�����������˥����Τˤϴޤ�         //
// 2011/12/13 ���դ�����å����ƺǽ�����ư��Ĵ�����뵡ǽ�򥳥��Ȳ�      //
//            ͽ��ײ�ξ������Ǥ�Ǽ���˻��ꤷ�Ƥ����ٽ��פǤ��ʤ��ä��١�//
// 2012/01/05 �¤ӽ��AS�ˤ��碌��١�parts_no����ײ�No.��5����ѹ�        //
// 2012/03/28 �Σˣ����ʽи�ʬ(NKTB)�ξȲ���ɲ�                            //
// 2012/12/17 �Σˣ����ʽи�ʬ������̾���̤Τ�Τ�������Ƥ����Τ�����      //
// 2013/01/29 ����̾��Ƭʸ����DPE�Τ�Τ���Υݥ��(�Х����)�ǽ��פ���褦 //
//            ���ѹ�                                                        //
// 2013/01/29 �Х�������Υݥ�פ��ѹ� ɽ���Τߥǡ����ϥХ����Τޤ�     //
// 2013/01/31 ��˥��Τߤ�DPEȴ��SQL������                             ��ë //
// 2013/05/28 2013/05���NKCT/NKT����夲��ȴ���Ф��ʤ��褦�˽���      ��ë //
// 2015/05/12 �����������б�                                           ��ë //
// 2018/06/08 ����A������ñ�����б����褦�Ȥ�������52������ǤϤʤ��Τ���α //
// 2018/06/22 ����Υ��顼��å��������ְ�äƤ����Τ�����             ��ë //
// 2018/07/31 shikiri�����顼�ˤʤäƤ����ΤǶ���                      ��ë //
// 2018/08/21 ����A������ñ��52����б�                                ��ë //
//            ����ξ��Ϻǿ����ڤ�A��52%ñ�������ɽ����ϤǤ���褦��    //
// 2020/12/07 ���ͽ��Ȳ��ã��Ψ�ɲäˤ���ѹ�                       ���� //
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
$menu->set_site( 1, 18);                    // site_index=01(����˥塼) site_id=18(���ͽ������)
////////////// �꥿���󥢥ɥ쥹����
// $menu->set_RetUrl(SYS_MENU);                // �̾�ϻ��ꤹ��ɬ�פϤʤ�
//////////// �����ȥ�̾(�������Υ����ȥ�̾�ȥե�����Υ����ȥ�̾)
$menu->set_title('�� �� ͽ �� �� ��');
//////////// �ƽ����action̾�ȥ��ɥ쥹����
$menu->set_action('�������Ȳ�',   INDUST . 'material/materialCost_view.php');
$menu->set_action('ñ����Ͽ�Ȳ�',   INDUST . 'parts/parts_cost_view.php');
$menu->set_action('�����������',   INDUST . 'material/materialCost_view_assy.php');

//////////// �֥饦�����Υ���å����к���
$uniq = $menu->set_useNotCache('target');

// ����A������ñ���б���α �����ǿ�����'S'
//$_REQUEST['shikiri'] = 'S';
//$shikiri    = 'S';

//////////// �����Υ��å����ǡ�����¸   ���ǡ����Ǥ�ڤ����뤿��
if (! (isset($_REQUEST['forward']) || isset($_REQUEST['backward']) || isset($_REQUEST['page_keep'])) ) {
    $session->add_local('recNo', '-1');         // 0�쥳���ɤǥޡ�����ɽ�����Ƥ��ޤ�������б�
    $_SESSION['s_uri_passwd'] = $_REQUEST['uri_passwd'];
    $_SESSION['s_div']        = $_REQUEST['div'];
    $_SESSION['s_d_start']    = $_REQUEST['d_start'];
    $_SESSION['s_d_end']      = $_REQUEST['d_end'];
    $_SESSION['s_uri_ritu']   = $_REQUEST['uri_ritu'];
    $_SESSION['s_shikiri']    = $_REQUEST['shikiri'];
    $_SESSION['s_sales_page'] = $_REQUEST['sales_page'];
    $_SESSION['uri_assy_no']  = $_REQUEST['assy_no'];
    $_SESSION['s_tassei']       = $_REQUEST['tassei']; // 2020/12/07 add.
    $uri_passwd = $_SESSION['s_uri_passwd'];
    $div        = $_SESSION['s_div'];
    $d_start    = $_SESSION['s_d_start'];
    $d_end      = $_SESSION['s_d_end'];
    $uri_ritu   = $_SESSION['s_uri_ritu'];
    $shikiri    = $_SESSION['s_shikiri'];
    $tassei     = $_SESSION['s_tassei']; // 2020/12/07 add.
    // �����ǿ�����
    //$shikiri    = 'S';
    $assy_no    = $_SESSION['uri_assy_no'];
        ///// day �Υ����å�
        /* if (substr($d_start, 6, 2) < 1) $d_start = substr($d_start, 0, 6) . '01';
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
        } */
    $_SESSION['s_d_start'] = $d_start;
    $_SESSION['s_d_end']   = $d_end  ;
    
    ////////////// �ѥ���ɥ����å�
    if ($uri_passwd != date('Ymd')) {
        $_SESSION['s_sysmsg'] = "<font color='yellow'>�ѥ���ɤ��㤤�ޤ���</font>";
        header('Location: ' . H_WEB_HOST . $menu->out_RetUrl());    // ľ���θƽи������
        exit();
    }
    if ($div == "NKTB") {  // NKT���ʽи�ʬ�ξ�����SQL�ǽ���
        ///////////// ��׶�ۡ�����������
        $query = "select
                        count(a.chaku)                     AS t_ken,
                        sum((allo.allo_qt - allo.sum_qt))  AS t_kazu,
                        sum(Uround((allo.allo_qt - allo.sum_qt) * (SELECT price FROM sales_price_nk WHERE parts_no = allo.parts_no LIMIT 1), 0)) AS t_kingaku
                    FROM
                        assembly_schedule as a
                    LEFT OUTER JOIN
                        allocated_parts as allo
                    on a.plan_no=allo.plan_no
                    left outer join
                        miitem as m
                    on a.parts_no=m.mipn";
    } else {
        ///////////// ��׶�ۡ�����������
        $query = "select
                        count((a.plan -a.cut_plan - a.kansei)) as t_ken,
                        sum((a.plan -a.cut_plan - a.kansei)) as t_kazu,
                        sum(Uround((a.plan -a.cut_plan - a.kansei)*(SELECT price FROM sales_price_nk WHERE parts_no = a.parts_no LIMIT 1),0)) as t_kingaku
                    from
                        assembly_schedule as a
                    left outer join
                        product_support_master AS groupm
                    on a.parts_no=groupm.assy_no
                    left outer join
                        miitem as m
                    on a.parts_no=m.mipn";
    }
    //////////// SQL where ��� ���Ѥ���
    if ($div == "NKTB") {  // NKT���ʽи�ʬ�ξ��� assy_site �ǥ����å�
        $search = "WHERE a.chaku>=$d_start AND a.chaku<=$d_end AND (a.plan -a.cut_plan) > 0 AND assy_site='05001' AND (a.plan -a.cut_plan - kansei) > 0 AND (allo.allo_qt - allo.sum_qt) > 0";
    } else {
        $search = "WHERE a.kanryou>=$d_start AND a.kanryou<=$d_end AND (a.plan -a.cut_plan) > 0 AND assy_site='01111' AND a.nyuuko!=30 AND p_kubun='F' AND (a.plan -a.cut_plan - kansei) > 0";
    }
    if ($assy_no != '') {       // �����ֹ椬���ꤵ�줿���
        $search .= " and a.parts_no like '{$assy_no}%%'";
    } elseif ($div == 'S') {    // ������ʤ�
        $search .= " and a.dept='C' and a.note15 like 'SC%%'";
        $search .= " and (a.parts_no not like 'NKB%%')";
        $search .= " and (a.parts_no not like 'SS%%')";
        //$search .= " and groupm.support_group_code IS NULL";
        $search .= " and CASE WHEN a.kanryou<20130501 THEN groupm.support_group_code IS NULL ELSE a.dept='C' END";
    } elseif ($div == 'D') {    // ��ɸ��ʤ�
        $search .= " and a.dept='C' and (a.note15 NOT like 'SC%%' OR a.note15 IS NULL)";    // ��������ɸ��ؤ���
        $search .= " and (a.parts_no not like 'NKB%%')";
        $search .= " and (a.parts_no not like 'SS%%')";
        //$search .= " and groupm.support_group_code IS NULL";
        $search .= " and (CASE WHEN a.kanryou<20130501 THEN groupm.support_group_code IS NULL ELSE a.dept='C' END)";
    } elseif ($div == "N") {    // ��˥��ΥХ���롦���������� assyno �ǥ����å�
        $search .= " and a.dept='L' and (a.parts_no NOT like 'LC%%' AND a.parts_no NOT like 'LR%%')";
        $search .= " and (a.parts_no not like 'SS%%')";
        //$search .= " and CASE WHEN a.parts_no = '' THEN a.dept='L' ELSE m.midsc not like 'DPE%%' END";
        $search .= " and CASE WHEN a.parts_no = '' THEN a.dept='L' ELSE CASE WHEN m.midsc IS NULL THEN a.dept='L' ELSE m.midsc not like 'DPE%%' END END";
        //$search .= " and groupm.support_group_code IS NULL";
        $search .= " and CASE WHEN a.kanryou<20130501 THEN groupm.support_group_code IS NULL ELSE a.dept='L' END";
    } elseif ($div == "B") {    // �Х����ξ��� assyno �ǥ����å�
        //$search .= " and (a.parts_no like 'LC%%' or a.parts_no like 'LR%%')";
        $search .= " and (a.parts_no like 'LC%%' or a.parts_no like 'LR%%' or m.midsc like 'DPE%%')";
        $search .= " and CASE WHEN a.kanryou<20130501 THEN groupm.support_group_code IS NULL ELSE a.dept='L' END";
        //$search .= " and groupm.support_group_code IS NULL";
    } elseif ($div == "NKCT") { // NKCT�ξ��ϻٱ��襳����(1)�ǥ����å�
        $search .= " and CASE WHEN a.kanryou<20130501 THEN groupm.support_group_code=1 END";
        //$search .= " and groupm.support_group_code=1";
    } elseif ($div == "NKT") {  // NKT�ξ��ϻٱ��襳����(2)�ǥ����å�
        $search .= " and CASE WHEN a.kanryou<20130501 THEN groupm.support_group_code=2 END";
        //$search .= " and groupm.support_group_code=2";
    } elseif ($div == "C") {
        $search .= " and a.dept='$div'";
        $search .= " and (a.parts_no not like 'NKB%%')";
        $search .= " and (a.parts_no not like 'SS%%')";
    } elseif ($div == "L") {
        $search .= " and a.dept='$div'";
        $search .= " and (a.parts_no not like 'SS%%')";
    } elseif ($div == "T") {
        $search .= " and a.dept='$div'";
        $search .= " and (a.parts_no not like 'NKB%%')";
        $search .= " and (a.parts_no not like 'SS%%')";
    } elseif ($div == "NKTB") {
    } elseif ($div != " ") {
        $search .= " and a.dept='$div'";
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
    $_SESSION['s_yotei']      = "";
} else {                                                // �ڡ������ؤʤ�
    $t_ken     = $_SESSION['u_t_ken'];
    $t_kazu    = $_SESSION['u_t_kazu'];
    $t_kingaku = $_SESSION['u_t_kin'];

    // 2020/12/07 add. ------------------------------------------------------->
    if( isset($_REQUEST['tassei']) ) {
        $_SESSION['s_tassei']       = $_REQUEST['tassei'];
    }

    if( isset($_REQUEST['yotei']) ) {
        $_SESSION['s_yotei']       = $_REQUEST['yotei'];
    }
    if( $_SESSION['s_yotei'] == "on" ) {
        $menu->set_RetUrl(SALES . 'sales_plan/sales_plan_view.php?page_keep=1&yotei=');
    } else {
        $menu->set_RetUrl($menu->out_RetUrl());
    }
    // <-----------------------------------------------------------------------
}

$uri_passwd = $_SESSION['s_uri_passwd'];
$div        = $_SESSION['s_div'];
$d_start    = $_SESSION['s_d_start'];
$d_end      = $_SESSION['s_d_end'];
$uri_ritu   = $_SESSION['s_uri_ritu'];
$assy_no    = $_SESSION['uri_assy_no'];
$search     = $_SESSION['sales_search'];
$shikiri    = $_SESSION['s_shikiri'];
$tassei     = $_SESSION['s_tassei']; // 2020/12/07 add.
$yotei      = $_SESSION['s_yotei'];  // 2020/12/07 add.
/*
if( isset($_REQUEST['yotei']) ) {
    $menu->set_RetUrl(SALES . 'sales_plan/sales_plan_view.php?page_keep=1&yotei=');
} else {
    $menu->set_RetUrl($menu->out_RetUrl());
}
*/
///// ���ʥ��롼��(������)̾������
if ($div == " ") $div_name = "�����롼��";
if ($div == "C") $div_name = "���ץ�����";
if ($div == "D") $div_name = "���ץ�ɸ��";
if ($div == "S") $div_name = "���ץ�����";
if ($div == "L") $div_name = "��˥�����";
if ($div == "N") $div_name = "��˥��Τ�";
if ($div == "B") $div_name = "���Υݥ��";
if ($div == "SSC") $div_name = "���ץ�";
if ($div == "SSL") $div_name = "��˥��";
if ($div == "NKB") $div_name = "���ʴ���";
if ($div == "T") $div_name = "�ġ���";
if ($div == "TRI") $div_name = "���";
if ($div == "NKCT") $div_name = "�Σˣã�";
if ($div == "NKT") $div_name = "�Σˣ�";
if ($div == "NKTB") $div_name = "NKT���ʽи�ʬ";
if ($div == "_") $div_name = "�ʤ�";

//////////// ɽ�������
$ft_kingaku = number_format($t_kingaku);                    // ���头�ȤΥ���ޤ��ղ�
$ft_ken     = number_format($t_ken);
$ft_kazu    = number_format($t_kazu);
$f_d_start  = format_date($d_start);                        // ���դ� / �ǥե����ޥå�
$f_d_end    = format_date($d_end);
$menu->set_caption("<u>����=<font color='red'>{$div_name}</font>��{$f_d_start}��{$f_d_end}����׷��={$ft_ken}����׶��={$ft_kingaku}����׿���={$ft_kazu}<u>");

// ã��Ψ�׻��ѥǡ����������� 2020/12/07 add. Start -------------------------->
// /masterst/TNK-WEB/tnk-web/sales/details/sales_view.php ��ꥳ�ԡ�
if( $tassei == 'tassei' || $yotei != 'on' ) {

    $file_orign     = '../..' . SYS . 'backup/W#TIUKSL.TXT';
    $res            = array();
    $total_price    = 0;    // ���
    $total_ken      = 0;    // ���
    $total_count    = 0;    // ����
    $rec            = 0;    // �쥳���ɭ�
    if (file_exists($file_orign)) {         // �ե������¸�ߥ����å�
        $fp = fopen($file_orign, 'r');
        while (!(feof($fp))) {
            $data = fgetcsv($fp, 130, '_');     // �¥쥳���ɤ�103�Х��ȤʤΤǤ���ä�;͵��ǥ�ߥ���'_'�����
            if (feof($fp)) {
                break;
            }
            $num  = count($data);       // �ե�����ɿ��μ���
            if ($num != 14) {   // AS¦�κ���쥳���ɤ� php-4.3.5��0�֤� php-4.3.6��1���֤����ͤˤʤä���fgetcsv�λ����ѹ��ˤ��
               continue;
            }
            for ($f=0; $f<$num; $f++) {
                $res[$rec][$f] = mb_convert_encoding($data[$f], 'EUC-JP', 'SJIS');       // SJIS��EUC-JP���Ѵ�
                $res[$rec][$f] = addslashes($res[$rec][$f]);    // "'"�����ǡ����ˤ������\�ǥ��������פ���
                // $data_KV[$f] = mb_convert_kana($data[$f]);   // Ⱦ�ѥ��ʤ����ѥ��ʤ��Ѵ�
            }
            if($res[$rec][5] !='C8385407') {
                $query = sprintf("select midsc from miitem where mipn='%s' limit 1", $res[$rec][3]);
                getUniResult($query, $res[$rec][4]);       // ����̾�μ��� (���ʥ����ɤ��񤭤���)
                /******** ����������Ͽ�Ѥߤι����ɲ� *********/
                $sql = "
                    SELECT plan_no FROM material_cost_header WHERE plan_no='{$res[$rec][5]}'
                ";
                if (getUniResult($sql, $temp) <= 0) {
                    $res[$rec][13] = '��Ͽ';
                    $sql_c = "
                        SELECT sum_price + Uround((m_time + g_time) * assy_rate, 2) + Uround(a_time * a_rate, 2) FROM material_cost_header WHERE assy_no = '{$res[$rec][3]}' ORDER BY assy_no DESC, regdate DESC LIMIT 1
                    ";
                    if (($rows_c = getResultWithField3($sql_c, $field_c, $res_c)) <= 0) {
                    } else {
                    }
                } else {
                    $res[$rec][13] = '��Ͽ��';
                    $sql_c = "
                        SELECT sum_price + Uround((m_time + g_time) * assy_rate, 2) + Uround(a_time * a_rate, 2) FROM material_cost_header WHERE plan_no='{$res[$rec][5]}' AND assy_no = '{$res[$rec][3]}' ORDER BY assy_no DESC, regdate DESC LIMIT 1
                    ";
                    if (($rows_c = getResultWithField3($sql_c, $field_c, $res_c)) <= 0) {
                    } else {
                    }
                }
                /******** ����ɸ��ι����ɲ� *********/
                $sql2 = "
                    SELECT substr(note15, 1, 2) FROM assembly_schedule WHERE plan_no='{$res[$rec][5]}'
                ";
                $sc = '';
                getUniResult($sql2, $sc);
                if ($sc == 'SC') {
                    $res[$rec][15] = '����';
                } else {
                    $res[$rec][15] = 'ɸ��';
                }
                /******** ����ñ�������ǡ����ˤʤ����ξ�񤭽��� *********/
                if ($res[$rec][12] == 0) {                                  // ���ǡ����˻��ڤ����뤫�ɤ���
                    $res[$rec][14] = '1';
                    $sql = "
                        SELECT price FROM sales_price_nk WHERE parts_no='{$res[$rec][3]}'
                    ";
                    if (getUniResult($sql, $sales_price) <= 0) {            // �ǿ����ڤ���Ͽ����Ƥ��뤫
                        $sql = "
                            SELECT sum_price + Uround((m_time + g_time) * assy_rate, 2) + Uround(a_time * a_rate, 2) FROM material_cost_header WHERE plan_no='{$res[$rec][5]}' AND assy_no = '{$res[$rec][3]}' ORDER BY assy_no DESC, regdate DESC LIMIT 1
                        ";
                        if (getUniResult($sql, $sales_price) <= 0) {        // �ײ�����������Ͽ����Ƥ��뤫
                            $sql_c = "
                                SELECT sum_price + Uround((m_time + g_time) * assy_rate, 2) + Uround(a_time * a_rate, 2) FROM material_cost_header WHERE assy_no = '{$res[$rec][3]}' ORDER BY assy_no DESC, regdate DESC LIMIT 1
                            ";
                            if (getUniResult($sql, $sales_price) <= 0) {    // ���ʤ����������Ͽ����Ƥ��뤫
                                $res[$rec][12] = 0;
                            } else {
                                if ($res[$rec][15] == '����') {
                                    $res[$rec][12] = round(($sales_price * 1.27), 2);   // ����ΤȤ�����Ψ��
                                } else {
                                    $res[$rec][12] = round(($sales_price * 1.13), 2);
                                }
                            }
                        } else {
                            if ($res[$rec][15] == '����') {
                                $res[$rec][12] = round(($sales_price * 1.27), 2);       // ����ΤȤ�����Ψ��
                            } else {
                                $res[$rec][12] = round(($sales_price * 1.13), 2);
                            }
                        }
                    } else {
                        $res[$rec][12] = $sales_price;
                    }
                } else {
                    $res[$rec][14] = '0';
                }
                /******** ���� �׻� *********/
                $res[$rec][16] = round(($res[$rec][11] * $res[$rec][12]), 0);
                if( $div == " ") {  // ���롼������
                    $total_price  += $res[$rec][16];
                    $total_ken++;
                    $total_count  += $res[$rec][11];
                } else if( $div == "C") {  // ���ץ�����
                    if( $res[$rec][0] == 'C' ) {
                        $total_price  += $res[$rec][16];
                        $total_ken++;
                        $total_count  += $res[$rec][11];
                    }
                } else if( $div == "D") {  // ���ץ�ɸ��
                    if( $res[$rec][0] == 'C' && $res[$rec][15] == 'ɸ��' ) {
                        $total_price  += $res[$rec][16];
                        $total_ken++;
                        $total_count  += $res[$rec][11];
                    }
                } else if( $div == "S") {  // ���ץ�����
                    if( $res[$rec][0] == 'C' && $res[$rec][15] == '����' ) {
                        $total_price  += $res[$rec][16];
                        $total_ken++;
                        $total_count  += $res[$rec][11];
                    }
                } else if( $div == "L" || $div == "N" ) {  // ��˥����� ��˥��Τ�
                    if( $res[$rec][0] == 'L' ) {
                        $total_price  += $res[$rec][16];
                        $total_ken++;
                        $total_count  += $res[$rec][11];
                    }
                }

                $rec++;
            }
        }
        // 0=>'������', 1=>'������', 3=>'�����ֹ�', 4=>'����̾', 5=>'�ײ��ֹ�', 11=>'������', 12=>'����ñ��'
    }
    $t_kingaku3 = $total_price;
    $t_ken3     = $total_ken;
    $t_kazu3    = $total_count;

    ///////////// ��׶�ۡ�����������
    if ( ($div != 'S') && ($div != 'D') ) {      // �������ɸ�� �ʳ��ʤ�
        $query2 = "select
                        count(����) as t_ken,
                        sum(����) as t_kazu,
                        sum(Uround(����*ñ��,0)) as t_kingaku
                  from
                        hiuuri
                  left outer join
                        product_support_master AS groupm
                  on assyno=groupm.assy_no
                  left outer join
                        miitem as m
                  on assyno=m.mipn";
    } else {
        $query2 = "select
                        count(����) as t_ken,
                        sum(����) as t_kazu,
                        sum(Uround(����*ñ��,0)) as t_kingaku
                  from
                        hiuuri
                  left outer join
                        assembly_schedule as a
                  on �ײ��ֹ�=plan_no
                  left outer join
                        product_support_master AS groupm
                  on assyno=groupm.assy_no
                  left outer join
                        miitem as m
                  on assyno=m.mipn";
                  //left outer join
                  //      aden_master as aden
                  //on (a.parts_no=aden.parts_no and a.plan_no=aden.plan_no)";
    }
    //////////// SQL where ��� ���Ѥ���
    $search2 = "where �׾���>=$d_start and �׾���<=$d_end";
    if ($assy_no != '') {       // �����ֹ椬���ꤵ�줿���
        $search2 .= " and assyno like '{$assy_no}%%'";
    }
    if ($div == 'S') {    // ������ʤ�
        $search2 .= " and ������='C' and note15 like 'SC%%'";
        $search2 .= " and (assyno not like 'NKB%%')";
        $search2 .= " and (assyno not like 'SS%%')";
        $search2 .= " and CASE WHEN �׾���>=20111101 and �׾���<20130501 THEN groupm.support_group_code IS NULL ELSE ������='C' END";
        //$search2 .= " and groupm.support_group_code IS NULL";
    } elseif ($div == 'D') {    // ��ɸ��ʤ�
        $search2 .= " and ������='C' and (note15 NOT like 'SC%%' OR note15 IS NULL)";    // ��������ɸ��ؤ���
        $search2 .= " and (assyno not like 'NKB%%')";
        $search2 .= " and (assyno not like 'SS%%')";
        $search2 .= " and (CASE WHEN �׾���>=20111101 and �׾���<20130501 THEN groupm.support_group_code IS NULL ELSE ������='C' END)";
        //$search2 .= " and groupm.support_group_code IS NULL";
    } elseif ($div == "N") {    // ��˥��ΥХ���롦���������� assyno �ǥ����å�
        $search2 .= " and ������='L' and (assyno NOT like 'LC%%' AND assyno NOT like 'LR%%')";
        $search2 .= " and (assyno not like 'SS%%')";
        $search2 .= " and (assyno not like 'NKB%%')";
        $search2 .= " and CASE WHEN assyno = '' THEN ������='L' ELSE CASE WHEN m.midsc IS NULL THEN ������='L' ELSE m.midsc not like 'DPE%%' END END";
        $search2 .= " and CASE WHEN �׾���>=20111101 and �׾���<20130501 THEN groupm.support_group_code IS NULL ELSE ������='L' END";
        //$search2 .= " and groupm.support_group_code IS NULL";
    } elseif ($div == "B") {    // �Х����ξ��� assyno �ǥ����å�
        //$search2 .= " and (assyno like 'LC%%' or assyno like 'LR%%')";
        $search2 .= " and (assyno like 'LC%%' or assyno like 'LR%%' or m.midsc like 'DPE%%')";
        $search2 .= " and (assyno not like 'SS%%')";
        $search2 .= " and (assyno not like 'NKB%%')";
        $search2 .= " and CASE WHEN �׾���>=20111101 and �׾���<20130501 THEN groupm.support_group_code IS NULL ELSE ������='L' END";
        //$search2 .= " and groupm.support_group_code IS NULL";
    } elseif ($div == "SSC") {   // ���ץ��������ξ��� assyno �ǥ����å�
        $search2 .= " and ������='C' and (assyno like 'SS%%')";
    } elseif ($div == "SSL") {   // ��˥���������ξ��� assyno �ǥ����å�
        // ���ץ����ʤ��ʤä��Τǻ�����L�Ͼʤ�
        //$search2 .= " and ������='L' and (assyno like 'SS%%')";
        $search2 .= " and (assyno like 'SS%%')";
    } elseif ($div == "NKB") {  // ���ʴ����ξ��� assyno �ǥ����å�
        $search2 .= " and (assyno like 'NKB%%')";
    } elseif ($div == "TRI") {  // ���ξ��ϻ�����������ʬ����ɼ�ֹ�ǥ����å�
        $search2 .= " and ������='C'";
        $search2 .= " and ( datatype='3' or datatype='7' )";
        $search2 .= " and ��ɼ�ֹ�='00222'";
    } elseif ($div == "NKCT") { // NKCT�ξ��ϻٱ��襳����(1)�ǥ����å�
        $search2 .= " and CASE WHEN �׾���>=20111101 and �׾���<20130501 THEN groupm.support_group_code=1 END";
        //$search2 .= " and groupm.support_group_code=1";
    } elseif ($div == "NKT") {  // NKT�ξ��ϻٱ��襳����(2)�ǥ����å�
        $search2 .= " and CASE WHEN �׾���>=20111101 and �׾���<20130501 THEN groupm.support_group_code=2 END";
        //$search2 .= " and groupm.support_group_code=2";
    } elseif ($div == "_") {    // �������ʤ�
        $search2 .= " and ������=' '";
    } elseif ($div == "C") {
        $search2 .= " and ������='$div'";
        $search2 .= " and (assyno not like 'NKB%%')";
        $search2 .= " and (assyno not like 'SS%%')";
    } elseif ($div == "L") {
        $search2 .= " and ������='$div'";
        $search2 .= " and (assyno not like 'SS%%')";
        $search2 .= " and (assyno not like 'NKB%%')";
    } elseif ($div != " ") {
        $search2 .= " and ������='$div'";
    }
    $search2 .= " and datatype='1'"; // 1������ ����

    $query2 = sprintf("$query2 %s", $search2);     // SQL query ʸ�δ���
    $res_sum = array();
    if (getResult($query2, $res_sum) <= 0) {
        $_SESSION['s_sysmsg'] = '��׶�ۤμ����˼��Ԥ��ޤ�����';
        header('Location: ' . H_WEB_HOST . $menu->out_RetUrl());    // ľ���θƽи������
        exit();
    } else {
        $t_ken2     = $res_sum[0]['t_ken'];
        $t_kazu2    = $res_sum[0]['t_kazu'];
        $t_kingaku2 = $res_sum[0]['t_kingaku'];
    }

    // ̤���� ɽ����
    $ft_kingaku3 = number_format($t_kingaku3);  // ���头�ȤΥ���ޤ��ղ�
    $ft_ken3     = number_format($t_ken3);
    $ft_kazu3    = number_format($t_kazu3);

    // ��λ ɽ����
    $ft_kingaku2 = number_format($t_kingaku2);  // ���头�ȤΥ���ޤ��ղ�
    $ft_ken2     = number_format($t_ken2);
    $ft_kazu2    = number_format($t_kazu2);

    // ��� �׻���
    $a_kingaku = $t_kingaku + $t_kingaku2 + $t_kingaku3;
    $a_ken = $t_ken + $t_ken2 + $t_ken3;
    $a_kazu = $t_kazu + $t_kazu2 + $t_kazu3;

    // ��� ɽ����
    $at_kingaku = number_format($a_kingaku);    // ���头�ȤΥ���ޤ��ղ�
    $at_ken     = number_format($a_ken);
    $at_kazu    = number_format($a_kazu);

    // ã��Ψ
    if( $at_kingaku == 0 ) {
        $ri_kingaku = 0;
    } else {
        $ri_kingaku = round(($t_kingaku2 + $t_kingaku3) / $a_kingaku * 100, 2);
    }
    if( $at_ken == 0 ) {
        $ri_ken = 0;
    } else {
        $ri_ken = round(($t_ken2 + $t_ken3) / $a_ken * 100, 2);
    }
    if( $at_kazu == 0 ) {
        $ri_kazu = 0;
    } else {
        $ri_kazu = round(($t_kazu2 + $t_kazu3) / $a_kazu * 100, 2);
    }
}
// <--------------------------------------------------------2020/12/07 add. End

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

if ($div == "S") {    // ����ξ�� ��׶�ۼ����ΰ١�����SQL��ή����׶�ۤ�׻�
    if ($shikiri == "A") {    // A������ñ��52��ξ��
        $query = sprintf("select
                                a.kanryou                     AS ��λͽ����,  -- 0
                                CASE
                                    WHEN trim(a.plan_no)='' THEN '---'        --NULL�Ǥʤ��ƥ��ڡ�������ޤäƤ�����Ϥ��졪
                                    ELSE a.plan_no
                                END                           AS �ײ��ֹ�,    -- 1
                                CASE
                                    WHEN trim(a.parts_no) = '' THEN '---'
                                    ELSE a.parts_no
                                END                           AS �����ֹ�,    -- 2
                                CASE
                                    WHEN trim(substr(m.midsc,1,38)) = '' THEN '&nbsp;'
                                    WHEN m.midsc IS NULL THEN '&nbsp;'
                                    ELSE substr(m.midsc,1,38)
                                END                           AS ����̾,      -- 3
                                a.plan -a.cut_plan - a.kansei AS ����,        -- 4
                                (SELECT Uround(order_price*0.52,2) FROM aden_details_master WHERE plan_no = a.plan_no LIMIT 1)
                                                              AS ����ñ��,    -- 5
                                Uround((a.plan -a.cut_plan - kansei) * (SELECT Uround(order_price*0.52,2) FROM aden_details_master WHERE plan_no = a.plan_no LIMIT 1), 0)
                                                              AS ���,        -- 6
                                sum_price + Uround((m_time + g_time) * assy_rate, 2) + Uround(a_time * a_rate, 2)
                                                              AS �������,    -- 7
                                CASE
                                    WHEN (sum_price + Uround((m_time + g_time) * assy_rate, 2) + Uround(a_time * a_rate, 2)) <= 0 THEN 0
                                    ELSE Uround((SELECT price FROM sales_price_nk WHERE parts_no = a.parts_no LIMIT 1) / (sum_price + Uround((m_time + g_time) * assy_rate, 2) + Uround(a_time * a_rate, 2)), 3) * 100
                                END                           AS Ψ��,        -- 8
                                (select sum_price + Uround((m_time + g_time) * assy_rate, 2) + Uround(a_time * a_rate, 2) from material_cost_header where assy_no=a.parts_no AND regdate<=a.kanryou order by assy_no DESC, regdate DESC limit 1)
                                                              AS �������2,   -- 9
                                (select Uround((SELECT price FROM sales_price_nk WHERE parts_no = a.parts_no LIMIT 1) / (sum_price + Uround((m_time + g_time) * assy_rate, 2) + Uround(a_time * a_rate, 2)), 3) * 100 from material_cost_header where assy_no=a.parts_no AND regdate<=a.kanryou order by assy_no DESC, regdate DESC limit 1)
                                                              AS Ψ��,        --10
                                (select plan_no from material_cost_header where assy_no=a.parts_no AND regdate<=a.kanryou order by assy_no DESC, regdate DESC limit 1)
                                                              AS �ײ��ֹ�2,   --11
                                (SELECT sum(lot_cost) FROM parts_cost_history WHERE parts_no=a.parts_no AND as_regdate<a.kanryou AND lot_no=1 AND vendor!='88888' GROUP BY as_regdate, reg_no ORDER BY as_regdate DESC, reg_no DESC limit 1)
                                                              AS ���ʺ�����,  --12
                                (SELECT reg_no FROM parts_cost_history WHERE parts_no=a.parts_no AND as_regdate<a.kanryou AND lot_no=1 AND vendor!='88888' GROUP BY as_regdate, reg_no ORDER BY as_regdate DESC, reg_no DESC limit 1)
                                                              AS ñ����Ͽ�ֹ�, --13
                                CASE
                                    WHEN trim(a.plan_no)='' THEN '&nbsp;'        --NULL�Ǥʤ��ƥ��ڡ�������ޤäƤ�����Ϥ��졪
                                    ELSE substr(a.plan_no,4,5)
                                END                           AS �ײ��ֹ�3    -- 14
                          FROM
                                assembly_schedule as a
                          left outer join
                                miitem as m
                          on a.parts_no=m.mipn
                          left outer join
                                material_cost_header as mate
                          on a.plan_no=mate.plan_no
                          LEFT OUTER JOIN
                                sales_parts_material_history AS pmate
                          ON (a.parts_no=pmate.parts_no AND a.plan_no=pmate.sales_date)
                          left outer join
                                product_support_master AS groupm
                          on a.parts_no=groupm.assy_no
                          %s
                          order by a.kanryou, �ײ��ֹ�3
                          ", $search);   // ���� $search �Ǹ���
    } elseif ($shikiri == "AS") {    // A������ñ��52���ǿ����ڤξ��
        $query = sprintf("select
                                a.kanryou                     AS ��λͽ����,  -- 0
                                CASE
                                    WHEN trim(a.plan_no)='' THEN '---'        --NULL�Ǥʤ��ƥ��ڡ�������ޤäƤ�����Ϥ��졪
                                    ELSE a.plan_no
                                END                           AS �ײ��ֹ�,    -- 1
                                CASE
                                    WHEN trim(a.parts_no) = '' THEN '---'
                                    ELSE a.parts_no
                                END                           AS �����ֹ�,    -- 2
                                CASE
                                    WHEN trim(substr(m.midsc,1,38)) = '' THEN '&nbsp;'
                                    WHEN m.midsc IS NULL THEN '&nbsp;'
                                    ELSE substr(m.midsc,1,38)
                                END                           AS ����̾,      -- 3
                                a.plan -a.cut_plan - a.kansei AS ����,        -- 4
                                CASE
                                    WHEN (SELECT Uround(order_price*0.52,2) FROM aden_details_master WHERE plan_no = a.plan_no LIMIT 1) = 0 THEN (SELECT price FROM sales_price_nk WHERE parts_no = a.parts_no LIMIT 1)
                                    WHEN (SELECT Uround(order_price*0.52,2) FROM aden_details_master WHERE plan_no = a.plan_no LIMIT 1) IS NULL THEN (SELECT price FROM sales_price_nk WHERE parts_no = a.parts_no LIMIT 1)
                                    ELSE (SELECT Uround(order_price*0.52,2) FROM aden_details_master WHERE plan_no = a.plan_no LIMIT 1)
                                END                           AS ����ñ��,    -- 5
                                CASE
                                    WHEN (SELECT Uround(order_price*0.52,2) FROM aden_details_master WHERE plan_no = a.plan_no LIMIT 1) = 0 THEN Uround((a.plan -a.cut_plan - kansei) * (SELECT price FROM sales_price_nk WHERE parts_no = a.parts_no LIMIT 1), 0)
                                    WHEN (SELECT Uround(order_price*0.52,2) FROM aden_details_master WHERE plan_no = a.plan_no LIMIT 1) IS NULL THEN Uround((a.plan -a.cut_plan - kansei) * (SELECT price FROM sales_price_nk WHERE parts_no = a.parts_no LIMIT 1), 0)
                                    ELSE Uround((a.plan -a.cut_plan - kansei) * (SELECT Uround(order_price*0.52,2) FROM aden_details_master WHERE plan_no = a.plan_no LIMIT 1), 0)
                                END
                                                              AS ���,        -- 6
                                sum_price + Uround((m_time + g_time) * assy_rate, 2) + Uround(a_time * a_rate, 2)
                                                              AS �������,    -- 7
                                CASE
                                    WHEN (sum_price + Uround((m_time + g_time) * assy_rate, 2) + Uround(a_time * a_rate, 2)) <= 0 THEN 0
                                    ELSE Uround((SELECT price FROM sales_price_nk WHERE parts_no = a.parts_no LIMIT 1) / (sum_price + Uround((m_time + g_time) * assy_rate, 2) + Uround(a_time * a_rate, 2)), 3) * 100
                                END                           AS Ψ��,        -- 8
                                (select sum_price + Uround((m_time + g_time) * assy_rate, 2) + Uround(a_time * a_rate, 2) from material_cost_header where assy_no=a.parts_no AND regdate<=a.kanryou order by assy_no DESC, regdate DESC limit 1)
                                                              AS �������2,   -- 9
                                (select Uround((SELECT price FROM sales_price_nk WHERE parts_no = a.parts_no LIMIT 1) / (sum_price + Uround((m_time + g_time) * assy_rate, 2) + Uround(a_time * a_rate, 2)), 3) * 100 from material_cost_header where assy_no=a.parts_no AND regdate<=a.kanryou order by assy_no DESC, regdate DESC limit 1)
                                                              AS Ψ��,        --10
                                (select plan_no from material_cost_header where assy_no=a.parts_no AND regdate<=a.kanryou order by assy_no DESC, regdate DESC limit 1)
                                                              AS �ײ��ֹ�2,   --11
                                (SELECT sum(lot_cost) FROM parts_cost_history WHERE parts_no=a.parts_no AND as_regdate<a.kanryou AND lot_no=1 AND vendor!='88888' GROUP BY as_regdate, reg_no ORDER BY as_regdate DESC, reg_no DESC limit 1)
                                                              AS ���ʺ�����,  --12
                                (SELECT reg_no FROM parts_cost_history WHERE parts_no=a.parts_no AND as_regdate<a.kanryou AND lot_no=1 AND vendor!='88888' GROUP BY as_regdate, reg_no ORDER BY as_regdate DESC, reg_no DESC limit 1)
                                                              AS ñ����Ͽ�ֹ�, --13
                                CASE
                                    WHEN trim(a.plan_no)='' THEN '&nbsp;'        --NULL�Ǥʤ��ƥ��ڡ�������ޤäƤ�����Ϥ��졪
                                    ELSE substr(a.plan_no,4,5)
                                END                           AS �ײ��ֹ�3    -- 14
                          FROM
                                assembly_schedule as a
                          left outer join
                                miitem as m
                          on a.parts_no=m.mipn
                          left outer join
                                material_cost_header as mate
                          on a.plan_no=mate.plan_no
                          LEFT OUTER JOIN
                                sales_parts_material_history AS pmate
                          ON (a.parts_no=pmate.parts_no AND a.plan_no=pmate.sales_date)
                          left outer join
                                product_support_master AS groupm
                          on a.parts_no=groupm.assy_no
                          %s
                          order by a.kanryou, �ײ��ֹ�3
                          ", $search);   // ���� $search �Ǹ���
    } else {    // �ǿ����ڤξ��
        $query = sprintf("select
                                a.kanryou                     AS ��λͽ����,  -- 0
                                CASE
                                    WHEN trim(a.plan_no)='' THEN '---'        --NULL�Ǥʤ��ƥ��ڡ�������ޤäƤ�����Ϥ��졪
                                    ELSE a.plan_no
                                END                           AS �ײ��ֹ�,    -- 1
                                CASE
                                    WHEN trim(a.parts_no) = '' THEN '---'
                                    ELSE a.parts_no
                                END                           AS �����ֹ�,    -- 2
                                CASE
                                    WHEN trim(substr(m.midsc,1,38)) = '' THEN '&nbsp;'
                                    WHEN m.midsc IS NULL THEN '&nbsp;'
                                    ELSE substr(m.midsc,1,38)
                                END                           AS ����̾,      -- 3
                                a.plan -a.cut_plan - a.kansei AS ����,        -- 4
                                (SELECT price FROM sales_price_nk WHERE parts_no = a.parts_no LIMIT 1)
                                                              AS ����ñ��,    -- 5
                                Uround((a.plan -a.cut_plan - kansei) * (SELECT price FROM sales_price_nk WHERE parts_no = a.parts_no LIMIT 1), 0)
                                                              AS ���,        -- 6
                                sum_price + Uround((m_time + g_time) * assy_rate, 2) + Uround(a_time * a_rate, 2)
                                                              AS �������,    -- 7
                                CASE
                                    WHEN (sum_price + Uround((m_time + g_time) * assy_rate, 2) + Uround(a_time * a_rate, 2)) <= 0 THEN 0
                                    ELSE Uround((SELECT price FROM sales_price_nk WHERE parts_no = a.parts_no LIMIT 1) / (sum_price + Uround((m_time + g_time) * assy_rate, 2) + Uround(a_time * a_rate, 2)), 3) * 100
                                END                           AS Ψ��,        -- 8
                                (select sum_price + Uround((m_time + g_time) * assy_rate, 2) + Uround(a_time * a_rate, 2) from material_cost_header where assy_no=a.parts_no AND regdate<=a.kanryou order by assy_no DESC, regdate DESC limit 1)
                                                              AS �������2,   -- 9
                                (select Uround((SELECT price FROM sales_price_nk WHERE parts_no = a.parts_no LIMIT 1) / (sum_price + Uround((m_time + g_time) * assy_rate, 2) + Uround(a_time * a_rate, 2)), 3) * 100 from material_cost_header where assy_no=a.parts_no AND regdate<=a.kanryou order by assy_no DESC, regdate DESC limit 1)
                                                              AS Ψ��,        --10
                                (select plan_no from material_cost_header where assy_no=a.parts_no AND regdate<=a.kanryou order by assy_no DESC, regdate DESC limit 1)
                                                              AS �ײ��ֹ�2,   --11
                                (SELECT sum(lot_cost) FROM parts_cost_history WHERE parts_no=a.parts_no AND as_regdate<a.kanryou AND lot_no=1 AND vendor!='88888' GROUP BY as_regdate, reg_no ORDER BY as_regdate DESC, reg_no DESC limit 1)
                                                              AS ���ʺ�����,  --12
                                (SELECT reg_no FROM parts_cost_history WHERE parts_no=a.parts_no AND as_regdate<a.kanryou AND lot_no=1 AND vendor!='88888' GROUP BY as_regdate, reg_no ORDER BY as_regdate DESC, reg_no DESC limit 1)
                                                              AS ñ����Ͽ�ֹ�, --13
                                CASE
                                    WHEN trim(a.plan_no)='' THEN '&nbsp;'        --NULL�Ǥʤ��ƥ��ڡ�������ޤäƤ�����Ϥ��졪
                                    ELSE substr(a.plan_no,4,5)
                                END                           AS �ײ��ֹ�3    -- 14
                          FROM
                                assembly_schedule as a
                          left outer join
                                miitem as m
                          on a.parts_no=m.mipn
                          left outer join
                                material_cost_header as mate
                          on a.plan_no=mate.plan_no
                          LEFT OUTER JOIN
                                sales_parts_material_history AS pmate
                          ON (a.parts_no=pmate.parts_no AND a.plan_no=pmate.sales_date)
                          left outer join
                                product_support_master AS groupm
                          on a.parts_no=groupm.assy_no
                          %s
                          order by a.kanryou, �ײ��ֹ�3
                          ", $search);   // ���� $search �Ǹ���
    }
    $res   = array();
    $field = array();
    if (($rows = getResultWithField3($query, $field, $res)) <= 0) {
        $_SESSION['s_sysmsg'] .= sprintf("<font color='yellow'>���ͽ��Υǡ���������ޤ���<br>%s��%s</font><BR>", format_date($d_start), format_date($d_end));
        header('Location: ' . H_WEB_HOST . $menu->out_RetUrl());    // ľ���θƽи������
        exit();
    } else {
        $t_kingaku = 0;
        for ($r=0; $r<$rows; $r++) {
            $t_kingaku += $res[$r][6];
        }
        $ft_kingaku = number_format($t_kingaku);                    // ���头�ȤΥ���ޤ��ղ�
        $ft_ken     = number_format($t_ken);
        $ft_kazu    = number_format($t_kazu);
        $f_d_start  = format_date($d_start);                        // ���դ� / �ǥե����ޥå�
        $f_d_end    = format_date($d_end);
        $menu->set_caption("<u>����=<font color='red'>{$div_name}</font>��{$f_d_start}��{$f_d_end}����׷��={$ft_ken}����׶��={$ft_kingaku}����׿���={$ft_kazu}<u>");
    }
}

//////////// ɽ�����Υǡ���ɽ���ѤΥ���ץ� Query & �����
if ($div == "NKTB") {  // NKT���ʽи�ʬ�ξ�����SQL�ǽ���
    $query = sprintf("select
                        a.chaku                     AS �и�ͽ��,  -- 0
                        CASE
                            WHEN trim(a.plan_no)='' THEN '---'        --NULL�Ǥʤ��ƥ��ڡ�������ޤäƤ�����Ϥ��졪
                            ELSE a.plan_no
                        END                           AS �ײ��ֹ�,    -- 1
                        CASE
                            WHEN trim(allo.parts_no) = '' THEN '---'
                            ELSE allo.parts_no
                        END                           AS �����ֹ�,    -- 2
                        CASE
                            WHEN trim(substr(m.midsc,1,38)) = '' THEN ''
                            WHEN m.midsc IS NULL THEN ''
                            ELSE substr(m.midsc,1,38)
                        END                           AS ����̾,      -- 3
                        (allo.allo_qt - allo.sum_qt) AS ����,        -- 4
                        (SELECT price FROM sales_price_nk WHERE parts_no = allo.parts_no LIMIT 1)
                                                      AS ����ñ��,    -- 5
                        Uround((allo.allo_qt - allo.sum_qt) * (SELECT price FROM sales_price_nk WHERE parts_no = allo.parts_no LIMIT 1), 0)
                                                      AS ���        -- 6
                  FROM
                        assembly_schedule as a
                  LEFT OUTER JOIN
                        allocated_parts as allo
                  on a.plan_no=allo.plan_no
                  left outer join
                        miitem as m
                  on allo.parts_no=m.mipn
                  %s
                  order by a.chaku, a.plan_no, allo.parts_no
                  offset %d limit %d
                  ", $search, $offset, PAGE);   // ���� $search �Ǹ���
} elseif ($div == "S") {    // ����ξ��
    if ($shikiri == "A") {    // A������ñ��52��ξ��
        $query = sprintf("select
                                a.kanryou                     AS ��λͽ����,  -- 0
                                CASE
                                    WHEN trim(a.plan_no)='' THEN '---'        --NULL�Ǥʤ��ƥ��ڡ�������ޤäƤ�����Ϥ��졪
                                    ELSE a.plan_no
                                END                           AS �ײ��ֹ�,    -- 1
                                CASE
                                    WHEN trim(a.parts_no) = '' THEN '---'
                                    ELSE a.parts_no
                                END                           AS �����ֹ�,    -- 2
                                CASE
                                    WHEN trim(substr(m.midsc,1,38)) = '' THEN '&nbsp;'
                                    WHEN m.midsc IS NULL THEN '&nbsp;'
                                    ELSE substr(m.midsc,1,38)
                                END                           AS ����̾,      -- 3
                                a.plan -a.cut_plan - a.kansei AS ����,        -- 4
                                (SELECT Uround(order_price*0.52,2) FROM aden_details_master WHERE plan_no = a.plan_no LIMIT 1)
                                                              AS ����ñ��,    -- 5
                                Uround((a.plan -a.cut_plan - kansei) * (SELECT Uround(order_price*0.52,2) FROM aden_details_master WHERE plan_no = a.plan_no LIMIT 1), 0)
                                                              AS ���,        -- 6
                                sum_price + Uround((m_time + g_time) * assy_rate, 2) + Uround(a_time * a_rate, 2)
                                                              AS �������,    -- 7
                                CASE
                                    WHEN (sum_price + Uround((m_time + g_time) * assy_rate, 2) + Uround(a_time * a_rate, 2)) <= 0 THEN 0
                                    ELSE Uround((SELECT price FROM sales_price_nk WHERE parts_no = a.parts_no LIMIT 1) / (sum_price + Uround((m_time + g_time) * assy_rate, 2) + Uround(a_time * a_rate, 2)), 3) * 100
                                END                           AS Ψ��,        -- 8
                                (select sum_price + Uround((m_time + g_time) * assy_rate, 2) + Uround(a_time * a_rate, 2) from material_cost_header where assy_no=a.parts_no AND regdate<=a.kanryou order by assy_no DESC, regdate DESC limit 1)
                                                              AS �������2,   -- 9
                                (select Uround((SELECT price FROM sales_price_nk WHERE parts_no = a.parts_no LIMIT 1) / (sum_price + Uround((m_time + g_time) * assy_rate, 2) + Uround(a_time * a_rate, 2)), 3) * 100 from material_cost_header where assy_no=a.parts_no AND regdate<=a.kanryou order by assy_no DESC, regdate DESC limit 1)
                                                              AS Ψ��,        --10
                                (select plan_no from material_cost_header where assy_no=a.parts_no AND regdate<=a.kanryou order by assy_no DESC, regdate DESC limit 1)
                                                              AS �ײ��ֹ�2,   --11
                                (SELECT sum(lot_cost) FROM parts_cost_history WHERE parts_no=a.parts_no AND as_regdate<a.kanryou AND lot_no=1 AND vendor!='88888' GROUP BY as_regdate, reg_no ORDER BY as_regdate DESC, reg_no DESC limit 1)
                                                              AS ���ʺ�����,  --12
                                (SELECT reg_no FROM parts_cost_history WHERE parts_no=a.parts_no AND as_regdate<a.kanryou AND lot_no=1 AND vendor!='88888' GROUP BY as_regdate, reg_no ORDER BY as_regdate DESC, reg_no DESC limit 1)
                                                              AS ñ����Ͽ�ֹ�, --13
                                CASE
                                    WHEN trim(a.plan_no)='' THEN '&nbsp;'        --NULL�Ǥʤ��ƥ��ڡ�������ޤäƤ�����Ϥ��졪
                                    ELSE substr(a.plan_no,4,5)
                                END                           AS �ײ��ֹ�3    -- 14
                          FROM
                                assembly_schedule as a
                          left outer join
                                miitem as m
                          on a.parts_no=m.mipn
                          left outer join
                                material_cost_header as mate
                          on a.plan_no=mate.plan_no
                          LEFT OUTER JOIN
                                sales_parts_material_history AS pmate
                          ON (a.parts_no=pmate.parts_no AND a.plan_no=pmate.sales_date)
                          left outer join
                                product_support_master AS groupm
                          on a.parts_no=groupm.assy_no
                          %s
                          order by a.kanryou, �ײ��ֹ�3
                          offset %d limit %d
                          ", $search, $offset, PAGE);   // ���� $search �Ǹ���
    } elseif ($shikiri == "AS") {    // A������ñ��52���ǿ����ڤξ��
        $query = sprintf("select
                                a.kanryou                     AS ��λͽ����,  -- 0
                                CASE
                                    WHEN trim(a.plan_no)='' THEN '---'        --NULL�Ǥʤ��ƥ��ڡ�������ޤäƤ�����Ϥ��졪
                                    ELSE a.plan_no
                                END                           AS �ײ��ֹ�,    -- 1
                                CASE
                                    WHEN trim(a.parts_no) = '' THEN '---'
                                    ELSE a.parts_no
                                END                           AS �����ֹ�,    -- 2
                                CASE
                                    WHEN trim(substr(m.midsc,1,38)) = '' THEN '&nbsp;'
                                    WHEN m.midsc IS NULL THEN '&nbsp;'
                                    ELSE substr(m.midsc,1,38)
                                END                           AS ����̾,      -- 3
                                a.plan -a.cut_plan - a.kansei AS ����,        -- 4
                                CASE
                                    WHEN (SELECT Uround(order_price*0.52,2) FROM aden_details_master WHERE plan_no = a.plan_no LIMIT 1) = 0 THEN (SELECT price FROM sales_price_nk WHERE parts_no = a.parts_no LIMIT 1)
                                    WHEN (SELECT Uround(order_price*0.52,2) FROM aden_details_master WHERE plan_no = a.plan_no LIMIT 1) IS NULL THEN (SELECT price FROM sales_price_nk WHERE parts_no = a.parts_no LIMIT 1)
                                    ELSE (SELECT Uround(order_price*0.52,2) FROM aden_details_master WHERE plan_no = a.plan_no LIMIT 1)
                                END                           AS ����ñ��,    -- 5
                                CASE
                                    WHEN (SELECT Uround(order_price*0.52,2) FROM aden_details_master WHERE plan_no = a.plan_no LIMIT 1) = 0 THEN Uround((a.plan -a.cut_plan - kansei) * (SELECT price FROM sales_price_nk WHERE parts_no = a.parts_no LIMIT 1), 0)
                                    WHEN (SELECT Uround(order_price*0.52,2) FROM aden_details_master WHERE plan_no = a.plan_no LIMIT 1) IS NULL THEN Uround((a.plan -a.cut_plan - kansei) * (SELECT price FROM sales_price_nk WHERE parts_no = a.parts_no LIMIT 1), 0)
                                    ELSE Uround((a.plan -a.cut_plan - kansei) * (SELECT Uround(order_price*0.52,2) FROM aden_details_master WHERE plan_no = a.plan_no LIMIT 1), 0)
                                END
                                                              AS ���,        -- 6
                                sum_price + Uround((m_time + g_time) * assy_rate, 2) + Uround(a_time * a_rate, 2)
                                                              AS �������,    -- 7
                                CASE
                                    WHEN (sum_price + Uround((m_time + g_time) * assy_rate, 2) + Uround(a_time * a_rate, 2)) <= 0 THEN 0
                                    ELSE Uround((SELECT price FROM sales_price_nk WHERE parts_no = a.parts_no LIMIT 1) / (sum_price + Uround((m_time + g_time) * assy_rate, 2) + Uround(a_time * a_rate, 2)), 3) * 100
                                END                           AS Ψ��,        -- 8
                                (select sum_price + Uround((m_time + g_time) * assy_rate, 2) + Uround(a_time * a_rate, 2) from material_cost_header where assy_no=a.parts_no AND regdate<=a.kanryou order by assy_no DESC, regdate DESC limit 1)
                                                              AS �������2,   -- 9
                                (select Uround((SELECT price FROM sales_price_nk WHERE parts_no = a.parts_no LIMIT 1) / (sum_price + Uround((m_time + g_time) * assy_rate, 2) + Uround(a_time * a_rate, 2)), 3) * 100 from material_cost_header where assy_no=a.parts_no AND regdate<=a.kanryou order by assy_no DESC, regdate DESC limit 1)
                                                              AS Ψ��,        --10
                                (select plan_no from material_cost_header where assy_no=a.parts_no AND regdate<=a.kanryou order by assy_no DESC, regdate DESC limit 1)
                                                              AS �ײ��ֹ�2,   --11
                                (SELECT sum(lot_cost) FROM parts_cost_history WHERE parts_no=a.parts_no AND as_regdate<a.kanryou AND lot_no=1 AND vendor!='88888' GROUP BY as_regdate, reg_no ORDER BY as_regdate DESC, reg_no DESC limit 1)
                                                              AS ���ʺ�����,  --12
                                (SELECT reg_no FROM parts_cost_history WHERE parts_no=a.parts_no AND as_regdate<a.kanryou AND lot_no=1 AND vendor!='88888' GROUP BY as_regdate, reg_no ORDER BY as_regdate DESC, reg_no DESC limit 1)
                                                              AS ñ����Ͽ�ֹ�, --13
                                CASE
                                    WHEN trim(a.plan_no)='' THEN '&nbsp;'        --NULL�Ǥʤ��ƥ��ڡ�������ޤäƤ�����Ϥ��졪
                                    ELSE substr(a.plan_no,4,5)
                                END                           AS �ײ��ֹ�3    -- 14
                          FROM
                                assembly_schedule as a
                          left outer join
                                miitem as m
                          on a.parts_no=m.mipn
                          left outer join
                                material_cost_header as mate
                          on a.plan_no=mate.plan_no
                          LEFT OUTER JOIN
                                sales_parts_material_history AS pmate
                          ON (a.parts_no=pmate.parts_no AND a.plan_no=pmate.sales_date)
                          left outer join
                                product_support_master AS groupm
                          on a.parts_no=groupm.assy_no
                          %s
                          order by a.kanryou, �ײ��ֹ�3
                          offset %d limit %d
                          ", $search, $offset, PAGE);   // ���� $search �Ǹ���
    } else {    // �ǿ����ڤξ��
        $query = sprintf("select
                                a.kanryou                     AS ��λͽ����,  -- 0
                                CASE
                                    WHEN trim(a.plan_no)='' THEN '---'        --NULL�Ǥʤ��ƥ��ڡ�������ޤäƤ�����Ϥ��졪
                                    ELSE a.plan_no
                                END                           AS �ײ��ֹ�,    -- 1
                                CASE
                                    WHEN trim(a.parts_no) = '' THEN '---'
                                    ELSE a.parts_no
                                END                           AS �����ֹ�,    -- 2
                                CASE
                                    WHEN trim(substr(m.midsc,1,38)) = '' THEN '&nbsp;'
                                    WHEN m.midsc IS NULL THEN '&nbsp;'
                                    ELSE substr(m.midsc,1,38)
                                END                           AS ����̾,      -- 3
                                a.plan -a.cut_plan - a.kansei AS ����,        -- 4
                                (SELECT price FROM sales_price_nk WHERE parts_no = a.parts_no LIMIT 1)
                                                              AS ����ñ��,    -- 5
                                Uround((a.plan -a.cut_plan - kansei) * (SELECT price FROM sales_price_nk WHERE parts_no = a.parts_no LIMIT 1), 0)
                                                              AS ���,        -- 6
                                sum_price + Uround((m_time + g_time) * assy_rate, 2) + Uround(a_time * a_rate, 2)
                                                              AS �������,    -- 7
                                CASE
                                    WHEN (sum_price + Uround((m_time + g_time) * assy_rate, 2) + Uround(a_time * a_rate, 2)) <= 0 THEN 0
                                    ELSE Uround((SELECT price FROM sales_price_nk WHERE parts_no = a.parts_no LIMIT 1) / (sum_price + Uround((m_time + g_time) * assy_rate, 2) + Uround(a_time * a_rate, 2)), 3) * 100
                                END                           AS Ψ��,        -- 8
                                (select sum_price + Uround((m_time + g_time) * assy_rate, 2) + Uround(a_time * a_rate, 2) from material_cost_header where assy_no=a.parts_no AND regdate<=a.kanryou order by assy_no DESC, regdate DESC limit 1)
                                                              AS �������2,   -- 9
                                (select Uround((SELECT price FROM sales_price_nk WHERE parts_no = a.parts_no LIMIT 1) / (sum_price + Uround((m_time + g_time) * assy_rate, 2) + Uround(a_time * a_rate, 2)), 3) * 100 from material_cost_header where assy_no=a.parts_no AND regdate<=a.kanryou order by assy_no DESC, regdate DESC limit 1)
                                                              AS Ψ��,        --10
                                (select plan_no from material_cost_header where assy_no=a.parts_no AND regdate<=a.kanryou order by assy_no DESC, regdate DESC limit 1)
                                                              AS �ײ��ֹ�2,   --11
                                (SELECT sum(lot_cost) FROM parts_cost_history WHERE parts_no=a.parts_no AND as_regdate<a.kanryou AND lot_no=1 AND vendor!='88888' GROUP BY as_regdate, reg_no ORDER BY as_regdate DESC, reg_no DESC limit 1)
                                                              AS ���ʺ�����,  --12
                                (SELECT reg_no FROM parts_cost_history WHERE parts_no=a.parts_no AND as_regdate<a.kanryou AND lot_no=1 AND vendor!='88888' GROUP BY as_regdate, reg_no ORDER BY as_regdate DESC, reg_no DESC limit 1)
                                                              AS ñ����Ͽ�ֹ�, --13
                                CASE
                                    WHEN trim(a.plan_no)='' THEN '&nbsp;'        --NULL�Ǥʤ��ƥ��ڡ�������ޤäƤ�����Ϥ��졪
                                    ELSE substr(a.plan_no,4,5)
                                END                           AS �ײ��ֹ�3    -- 14
                          FROM
                                assembly_schedule as a
                          left outer join
                                miitem as m
                          on a.parts_no=m.mipn
                          left outer join
                                material_cost_header as mate
                          on a.plan_no=mate.plan_no
                          LEFT OUTER JOIN
                                sales_parts_material_history AS pmate
                          ON (a.parts_no=pmate.parts_no AND a.plan_no=pmate.sales_date)
                          left outer join
                                product_support_master AS groupm
                          on a.parts_no=groupm.assy_no
                          %s
                          order by a.kanryou, �ײ��ֹ�3
                          offset %d limit %d
                          ", $search, $offset, PAGE);   // ���� $search �Ǹ���
    }
} else {
    $query = sprintf("select
                            a.kanryou                     AS ��λͽ����,  -- 0
                            CASE
                                WHEN trim(a.plan_no)='' THEN '---'        --NULL�Ǥʤ��ƥ��ڡ�������ޤäƤ�����Ϥ��졪
                                ELSE a.plan_no
                            END                           AS �ײ��ֹ�,    -- 1
                            CASE
                                WHEN trim(a.parts_no) = '' THEN '---'
                                ELSE a.parts_no
                            END                           AS �����ֹ�,    -- 2
                            CASE
                                WHEN trim(substr(m.midsc,1,38)) = '' THEN '&nbsp;'
                                WHEN m.midsc IS NULL THEN '&nbsp;'
                                ELSE substr(m.midsc,1,38)
                            END                           AS ����̾,      -- 3
                            a.plan -a.cut_plan - a.kansei AS ����,        -- 4
                            (SELECT price FROM sales_price_nk WHERE parts_no = a.parts_no LIMIT 1)
                                                          AS ����ñ��,    -- 5
                            Uround((a.plan -a.cut_plan - kansei) * (SELECT price FROM sales_price_nk WHERE parts_no = a.parts_no LIMIT 1), 0)
                                                          AS ���,        -- 6
                            sum_price + Uround((m_time + g_time) * assy_rate, 2) + Uround(a_time * a_rate, 2)
                                                          AS �������,    -- 7
                            CASE
                                WHEN (sum_price + Uround((m_time + g_time) * assy_rate, 2) + Uround(a_time * a_rate, 2)) <= 0 THEN 0
                                ELSE Uround((SELECT price FROM sales_price_nk WHERE parts_no = a.parts_no LIMIT 1) / (sum_price + Uround((m_time + g_time) * assy_rate, 2) + Uround(a_time * a_rate, 2)), 3) * 100
                            END                           AS Ψ��,        -- 8
                            (select sum_price + Uround((m_time + g_time) * assy_rate, 2) + Uround(a_time * a_rate, 2) from material_cost_header where assy_no=a.parts_no AND regdate<=a.kanryou order by assy_no DESC, regdate DESC limit 1)
                                                          AS �������2,   -- 9
                            (select Uround((SELECT price FROM sales_price_nk WHERE parts_no = a.parts_no LIMIT 1) / (sum_price + Uround((m_time + g_time) * assy_rate, 2) + Uround(a_time * a_rate, 2)), 3) * 100 from material_cost_header where assy_no=a.parts_no AND regdate<=a.kanryou order by assy_no DESC, regdate DESC limit 1)
                                                          AS Ψ��,        --10
                            (select plan_no from material_cost_header where assy_no=a.parts_no AND regdate<=a.kanryou order by assy_no DESC, regdate DESC limit 1)
                                                          AS �ײ��ֹ�2,   --11
                            (SELECT sum(lot_cost) FROM parts_cost_history WHERE parts_no=a.parts_no AND as_regdate<a.kanryou AND lot_no=1 AND vendor!='88888' GROUP BY as_regdate, reg_no ORDER BY as_regdate DESC, reg_no DESC limit 1)
                                                          AS ���ʺ�����,  --12
                            (SELECT reg_no FROM parts_cost_history WHERE parts_no=a.parts_no AND as_regdate<a.kanryou AND lot_no=1 AND vendor!='88888' GROUP BY as_regdate, reg_no ORDER BY as_regdate DESC, reg_no DESC limit 1)
                                                          AS ñ����Ͽ�ֹ�, --13
                            CASE
                                WHEN trim(a.plan_no)='' THEN '&nbsp;'        --NULL�Ǥʤ��ƥ��ڡ�������ޤäƤ�����Ϥ��졪
                                ELSE substr(a.plan_no,4,5)
                            END                           AS �ײ��ֹ�3    -- 14
                      FROM
                            assembly_schedule as a
                      left outer join
                            miitem as m
                      on a.parts_no=m.mipn
                      left outer join
                            material_cost_header as mate
                      on a.plan_no=mate.plan_no
                      LEFT OUTER JOIN
                            sales_parts_material_history AS pmate
                      ON (a.parts_no=pmate.parts_no AND a.plan_no=pmate.sales_date)
                      left outer join
                            product_support_master AS groupm
                      on a.parts_no=groupm.assy_no
                      %s
                      order by a.kanryou, �ײ��ֹ�3
                      offset %d limit %d
                      ", $search, $offset, PAGE);   // ���� $search �Ǹ���
}
$res   = array();
$field = array();
if (($rows = getResultWithField3($query, $field, $res)) <= 0) {
    $_SESSION['s_sysmsg'] .= sprintf("<font color='yellow'>���ͽ��Υǡ���������ޤ���<br>%s��%s</font><BR>", format_date($d_start), format_date($d_end));
    header('Location: ' . H_WEB_HOST . $menu->out_RetUrl());    // ľ���θƽи������
    exit();
} else {
    $num = count($field);       // �ե�����ɿ�����
    for ($r=0; $r<$rows; $r++) {
        $res[$r][3] = mb_convert_kana($res[$r][3], 'ka', 'EUC-JP');   // ���ѥ��ʤ�Ⱦ�ѥ��ʤإƥ���Ū�˥���С���
    }
/* �ƥ��ȡ�ͽ����������� *
    for ($r=0; $r<$rows; $r++) {
        $query = "SELECT * FROM month_sales_plan WHERE plan_no='{$res[$r][1]}' AND parts_no='{$res[$r][2]}'";
        if( getResult2($query, $res_chk) > 0 ) continue;

        if( empty($res[$r][6]) ) {
            $insert_qry = "INSERT INTO month_sales_plan (kanryou, plan_no, parts_no, midsc, plan ) VALUES ('{$res[$r][0]}', '{$res[$r][1]}', '{$res[$r][2]}', '{$res[$r][3]}', '{$res[$r][4]}');";
        } else {
            $insert_qry = "INSERT INTO month_sales_plan (kanryou, plan_no, parts_no, midsc, plan, partition_price, price, materials_price, rate) VALUES ('{$res[$r][0]}', '{$res[$r][1]}', '{$res[$r][2]}', '{$res[$r][3]}', '{$res[$r][4]}', '{$res[$r][5]}', '{$res[$r][6]}', '{$res[$r][9]}', '{$res[$r][10]}');";
        }

        if( query_affected($insert_qry) <= 0 ) {
            $_SESSION['s_sysmsg'] .= "���ͽ����Ͽ���ԡ�({$r}){$res[$r][6]}";
            $_SESSION['s_sysmsg'] .= $insert_qry;
        }
    }
/**/
}

// ��������CSV�����Ѥν������
// �ե�����̾�����ܸ��Ĥ���ȼ����Ϥ��ǥ��顼�ˤʤ�Τǰ���ѻ����ѹ�
if ($div == " ") $act_name = "ALL";
if ($div == "C") $act_name = "C-all";
if ($div == "D") $act_name = "C-hyou";
if ($div == "S") $act_name = "C-toku";
if ($div == "L") $act_name = "L-all";
if ($div == "N") $act_name = "L-hyou";
if ($div == "B") $act_name = "L-bimor";
if ($div == "SSC") $act_name = "C-shuri";
if ($div == "SSL") $act_name = "L-shuri";
if ($div == "NKB") $act_name = "NKB";
if ($div == "T") $act_name = "TOOL";
if ($div == "TRI") $act_name = "SHISAKU";
if ($div == "NKCT") $act_name = "NKCT";
if ($div == "NKT") $act_name = "NKT";
if ($div == "NKTB") $act_name = "NKTB";
if ($div == "_") $act_name = "NONE";

// SQL�Υ������������ܸ��ѻ����ѹ���'�⥨�顼�ˤʤ�Τ�/�˰���ѹ�
$csv_search = str_replace('\'','/',$search);

// CSV�ե�����̾������ʳ���ǯ��-��λǯ��-��������
$outputFile = $d_start . '-' . $d_end . '-' . $act_name;

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
    <?php
    if( $tassei != 'tassei' || $yotei == 'on' ) { // 2020/12/07 add.
    ?>
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
                        
                        <a href='sales_plan_csv.php?csvname=<?php echo $outputFile ?>&csvsearch=<?php echo $csv_search ?>&div=<?php echo $act_name ?>&shikiri=<?php echo $shikiri ?>'>
                        CSV�ǡ���
                        </a>
                        <?php
                        if ($div == "S") {
                        ?>
                        ��
                        <a href='sales_plan_com_csv.php?csvname=<?php echo $outputFile ?>&csvsearch=<?php echo $csv_search ?>&div=<?php echo $act_name ?>&shikiri=<?php echo $shikiri ?>'>
                        ���ɽ
                        </a>
                        <?php
                        }
                        ?>
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
                    if ($i >= 9) break;
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
                        if ($i >= 9) break;
                        // <!--  bgcolor='#ffffc6' �������� --> 
                            switch ($i) {
                            case 0:     // �׾���
                                echo "<td class='winbox' nowrap align='center'><div class='pt9'>" . format_date($res[$r][$i]) . "</div></td>\n";
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
                            case 7:     // �������
                                if ($res[$r][$i] == 0) {
                                    if ($res[$r][9]) {
                                        echo "<td class='winbox' nowrap width='60' align='right'>
                                                <a class='pt9' href='JavaScript:baseJS.Ajax(\"{$menu->out_self()}?recNo={$recNo}\");location.replace(\"", $menu->out_action('�������Ȳ�'), "?plan_no={$res[$r][11]}&assy_no={$res[$r][2]}\")' target='application' style='text-decoration:none; color:brown;'>"
                                                , number_format($res[$r][9], 2), "</a></td>\n";
                                    } elseif ($res[$r][12]) {   // ���ʤκ����������å�����ɽ������
                                        echo "<td class='winbox' nowrap width='60' align='right'>
                                                <a class='pt9' href='JavaScript:baseJS.Ajax(\"{$menu->out_self()}?recNo={$recNo}\");location.replace(\"", $menu->out_action('ñ����Ͽ�Ȳ�'), "?parts_no=", urlencode($res[$r][2]), "& reg_no={$res[$r][13]}\")' target='application' style='text-decoration:none;'>"
                                                , number_format($res[$r][12], 2), "</a></td>\n";
                                    } else {
                                        echo "<td class='winbox' nowrap width='60' align='right'><div class='pt9'>-</div></td>\n";
                                    }
                                } else {
                                    echo "<td class='winbox' nowrap width='60' align='right'>
                                            <a class='pt9' href='JavaScript:baseJS.Ajax(\"{$menu->out_self()}?recNo={$recNo}\");location.replace(\"", $menu->out_action('�������Ȳ�'), "?plan_no={$res[$r][1]}&assy_no={$res[$r][2]}\")' target='application' style='text-decoration:none;'>"
                                            , number_format($res[$r][$i], 2), "</a></td>\n";
                                }
                                break;
                            case 8:    // Ψ(�������)
                                if ($res[$r][$i] > 0 && ($res[$r][$i] < 100.0)) {
                                    echo "<td class='winbox' nowrap width='40' align='right'><font class='pt9' color='red'>", number_format($res[$r][$i], 1), "</font></td>\n";
                                } elseif ($res[$r][$i] <= 0) {
                                    if ($res[$r][10]) {
                                        echo "<td class='winbox' nowrap width='40' align='right'><div class='pt9'>", number_format($res[$r][10], 1), "</div></td>\n";
                                    } elseif ($res[$r][12]) {
                                        if ( ($res[$r][5]/$res[$r][12]) < 1.049 ) {   // �ֻ�ɽ����ʬ��
                                            echo "<td class='winbox' nowrap width='40' align='right'><div class='pt9' style='color:red;'>", number_format($res[$r][5]/$res[$r][12]*100, 1), "</div></td>\n";
                                        } else {
                                            echo "<td class='winbox' nowrap width='40' align='right'><div class='pt9'>", number_format($res[$r][5]/$res[$r][12]*100, 1), "</div></td>\n";
                                        }
                                    } else {
                                        echo "<td class='winbox' nowrap width='40' align='right'><div class='pt9'>-</div></td>\n";
                                    }
                                } else {
                                    echo "<td class='winbox' nowrap width='40' align='right'><div class='pt9'>", number_format($res[$r][$i], 1), "</div></td>\n";
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
        <table style='border: 2px solid #0A0;'>
            <tr><td align='center' class='pt11b' tabindex='1' id='note'>���������Ŀ�ɽ����Ʊ�ײ��ֹ����Ͽ������ʪ�ǡ��㿧��Ʊ�ײ�Ǥ�̵��������������Ǻǿ�����Ͽ��ɽ��</td></tr>
        </table>
    <?php
    } else { // ã��Ψɽ���ν��� 2020/12/07 add. Start ----------------------->
    ?>
        <!--------------- ����������ʸ��ɽ��ɽ������ -------------------->
        <BR>
        <table bgcolor='#d6d3ce' align='center' border='1' cellspacing='0' cellpadding='3'>
            <caption><?php echo "���֡�{$f_d_start} �� {$f_d_end}" ?></caption>
            <tr><td> <!----------- ���ߡ�(�ǥ�������) ------------>
        <table class='winbox_field' width='100%' bgcolor='#d6d3ce' align='center' border='1' cellspacing='0' cellpadding='3'>

                <tr align='center' style='background-color:yellow; color:blue;'>
                    <td><font color='red'><?php echo $div_name ?></font></td>
<!-- page_keep -->
                    <form name='tassei_form' action='<?php echo $menu->out_self() ?>?page_keep=1&yotei=on' method='post'>
                    <td><a href="javascript:tassei_form.submit()">ͽ ��</a></td>
                    </form>
                    <form name='miken_form' action='<?php echo INDUST . "sales_miken/sales_miken_Main.php" ?>?tassei=on' method='post'>
                    <td><a href="javascript:miken_form.submit()">̤����</a></td>
                    </form>
                    <form name='meisai_form' action='<?php echo SALES . "details/sales_view.php?uri_passwd={$uri_passwd}&div={$div}&d_start={$d_start}&d_end={$d_end}&kubun=1&uri_ritu={$uri_ritu}&sales_page={$_SESSION['s_sales_page']}&assy_no=&customer=" . " " . "&syukei=meisai&yotei=on"?>' method='post'>
                    <td><a href="javascript:meisai_form.submit()">�� λ</td>
                    </form>
                    <td>�� ��</td>
                    <td>ã��Ψ</td>
                </tr>
                <tr align='right'>
                    <td align='center' style='background-color:yellow; color:blue;'>�� ��</td>
                    <td><?php echo "{$ft_ken} ��" ?></td>
                    <td><?php echo "{$ft_ken3} ��" ?></td>
                    <td><?php echo "{$ft_ken2} ��" ?></td>
                    <td><?php echo "{$at_ken} ��" ?></td>
                    <td><?php echo "{$ri_ken} ��" ?></td>
                </tr>
                <tr align='right'>
                    <td align='center' style='background-color:yellow; color:blue;'>�� ��</td>
                    <td><?php echo "{$ft_kingaku} ��" ?></td>
                    <td><?php echo "{$ft_kingaku3} ��" ?></td>
                    <td><?php echo "{$ft_kingaku2} ��" ?></td>
                    <td><?php echo "{$at_kingaku} ��" ?></td>
                    <td><?php echo "{$ri_kingaku} ��" ?></td>
                </tr>
                <tr align='right'>
                    <td align='center' style='background-color:yellow; color:blue;'>�� ��</td>
                    <td><?php echo "{$ft_kazu} ��" ?></td>
                    <td><?php echo "{$ft_kazu3} ��" ?></td>
                    <td><?php echo "{$ft_kazu2} ��" ?></td>
                    <td><?php echo "{$at_kazu} ��" ?></td>
                    <td><?php echo "{$ri_kazu} ��" ?></td>
                </tr>

        </table>
            </td></tr>
        </table> <!----------------- ���ߡ�End ------------------>
    <?php
    } // <------------------------------------------------- 2020/12/07 add. End
    ?>
    </center>
</body>
<?php echo $menu->out_alert_java()?>
</html>
<?php
// ob_end_flush();                 // ���ϥХåե���gzip���� END
?>
