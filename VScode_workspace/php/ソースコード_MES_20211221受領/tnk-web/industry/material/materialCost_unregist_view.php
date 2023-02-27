<?php
//////////////////////////////////////////////////////////////////////////////
// ������� ̤��Ͽ�ξȲ�  Ⱦ���̤�����form������ɽ�Ȳ� ɸ����               //
//             (���ץ��ɸ����/�����ʤȥ�˥�/�Х����/�ġ���/������)       //
// Copyright (C) 2004-2013 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp      //
// Changed history                                                          //
// 2004/04/08 Created   metarialCost_unregist_view.php                      //
//            ���ߤβ����ASSY�ֹ�join���Ƥ��뤿���о��ϰϳ��η���⽦�ä�  //
//            ���ޤ�����Ⱦ����������Ͽ����Ƥ���ʪ�ޤ���Ͽ�Ѥߤˤʤ롣      //
// 2004/04/09 �嵭�������SQLʸ�ιʹ��߻���ǲ�褷����®�٤��٤����ḡƤ�� //
// 2004/04/12 Ⱦ���֤δ�1�٤���Ͽ���ʤ�ʪ���о� <-- ��å��������ɲ�        //
// 2004/04/19 ��Ͽ���˷ײ��ֹ椬ɬ�פʤ���group->order���ѹ����ײ��ֹ� �ɲ� //
// 2004/05/05 JavaScript��chk_assy_entry()�ϻ��Ѥ��Ƥ��ʤ��Τ���򥳥��Ȳ�//
// 2004/05/12 �����ȥ�˥塼ɽ������ɽ�� �ܥ����ɲ� menu_OnOff($script)�ɲ� //
// 2004/05/25 �׾�����������ˤ�ɽ�����ܤ��ɲ�                              //
// 2004/10/25 ����򥰥롼��̾�Τ��ѹ���ɸ��������������Τ�ʬ����          //
// 2004/12/22 ���ץ�ɸ�������Ⱦ������ξ�狼������Ϸײ��ֹ椬���о���//
// 2004/12/22 MenuHeader class ����Ѥ��ƶ��̥�˥塼���ڤ�ǧ���������ѹ�   //
// 2005/01/07 �ƽл���&material=1 �꥿������ˤ��������å���$plan_no������//
// 2005/03/02 1�Ǥ�ɽ���Կ���default 25��20 ���ѹ�                          //
// 2005/06/07 ̤��Ͽ���̤��飱����å�����Ͽ���̤إ����׵�ǽ�ɲ�          //
//            Ⱦ���ǹʤ����Τ���������Ʊ���� ����ʪ��backup�ذ�ư     //
//            SQLʸ�ϥ����ȥ����Ȥ��Ƥ���                                 //
// 2006/08/02 C�������and mate.plan_no IS NULL �� and mate.assy_no IS NULL //
//            ��λ����˺����б� Cɸ����˥��ϸ���OK                      //
// 2007/03/24 material/allo_conf_parts_view.php ��                          //
//                           parts/allocate_config/allo_conf_parts_Main.php //
// 2007/05/10 ���̥�å����� Ⱦ���� �� ������� ���ѹ�                      //
// 2007/05/23 �����򿷤�����Ͽ���̤�materialCost_entry_main.php ��ë    //
// 2007/08/31 �����ֹ楯��å���������������Ȳ���ɲ� ���å�����ѿ���   //
//            mate_offset �ԥޡ�����������륻�å������ѹ�  ����        //
// 2007/09/04 ���̥�å�����������֢��������(2007/08/09���»�) ����     //
// 2007/09/05 materialCost_view_assy.php�˰���plan_no���ɲ� ����            //
// 2013/01/28 ����̾��Ƭʸ����DPE�Τ�Τ���Υݥ��(�Х����)�ǽ��פ���褦 //
//            ���ѹ�                                                   ��ë //
//            �Х�������Υݥ�פ��ѹ� ɽ���Τߥǡ����ϥХ����Τޤ� ��ë//
// 2013/01/31 ��˥��Τߤ�DPEȴ��SQL������                             ��ë //
//////////////////////////////////////////////////////////////////////////////
ini_set('error_reporting', E_ALL);          // E_ALL='2047' debug ��
// ini_set('display_errors', '1');             // Error ɽ�� ON debug �� ��꡼���女����
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
$menu->set_site(INDEX_INDUST, 24);          // site_index=30(������˥塼) site_id=24(��������̤��Ͽ)
// $_SESSION['site_index'] = 30;            // ������˥塼=30 �Ǹ�Υ�˥塼 = 99   �����ƥ�����Ѥϣ�����
// $_SESSION['site_id']    = 24;            // ���̥�˥塼̵�� <= 0    �ƥ�ץ졼�ȥե�����ϣ�����
////////////// �꥿���󥢥ɥ쥹����
// $menu->set_RetUrl(INDUST_MENU);             // �̾�ϻ��ꤹ��ɬ�פϤʤ�
//////////// �����ȥ�̾(�������Υ����ȥ�̾�ȥե�����Υ����ȥ�̾)
$menu->set_title('�� �� �� �� ̤ �� Ͽ �� ��');
//////////// ɽ�������
$menu->set_caption('��������򤷤Ʋ�����');
//////////// �ƽ����action̾�ȥ��ɥ쥹����
// $menu->set_action('��������ɽ��ɽ��',   INDUST . 'material/allo_conf_parts_view.php');
$menu->set_action('��������ɽ��ɽ��',   INDUST . 'parts/allocate_config/allo_conf_parts_Main.php');
$menu->set_action('��������Ѱ�������ɽ��ɽ��',   INDUST . 'parts/allocate_config_entry/allo_conf_parts_Main.php');
$menu->set_action('��������Ѱ�������ɽ��ɽ��TEST',   INDUST . 'parts/allocate_config_test/allo_conf_parts_Main.php');
$menu->set_action('����������Ͽ',     INDUST . 'material/material_entry/materialCost_entry_main.php');
$menu->set_action('������������',     INDUST . 'material/materialCost_view_assy.php');

//////////// JavaScript Stylesheet File ��ɬ���ɤ߹��ޤ���
$uniq = uniqid('target');

if (isset($_SESSION['stock_parts'])) {
    unset($_SESSION['stock_parts']);    // �����
}

//////////// �����Υ��å����ǡ�����¸(POST�ǡ����ȹ�ץ쥳���ɿ�) ���ǡ����Ǥ�ڤ����뤿��
if (! (isset($_REQUEST['forward']) || isset($_REQUEST['backward']) || isset($_REQUEST['page_keep'])) ) {
    $session->add_local('recNo', '-1');         // 0�쥳���ɤǥޡ�����ɽ�����Ƥ��ޤ�������б�
    if (isset($_REQUEST['div'])) {
        // ������ POST �ǡ�������Ϥ��� ���å�������¸��
        $span = $_REQUEST['span'];
        $tuki = date('m');
        $year = (int) date('Y');
        if ($span == 0) {       // ������Ⱦ��ʬ���о�
            if ($tuki >= 4 && $tuki <= 9) {
                $str_date = ($year . '0401');
                $end_date = ($year . '0931');
            } else if ($tuki >= 10 && $tuki <= 12) {
                $str_date = ($year . '1001');
                $end_date = ($year . '1231');       // �����0331����ɬ�פʤ��Τ�1231�ˤ�����
            } else {
                $str_date = (($year-1) . '1001');
                $end_date = ($year . '0331');
            }
        } else {                // ��Ⱦ��ʬ���о�
            if ($tuki >= 4 && $tuki <= 9) {
                $str_date = (($year-1) . '1001');
                $end_date = ($year . '0331');
            } else if ($tuki >= 10 && $tuki <= 12) {
                $str_date = ($year . '0401');
                $end_date = ($year . '0931');
            } else {
                $str_date = (($year-1) . '0401');
                $end_date = (($year-1) . '0931');
            }
        }
        // $str_date = '20031001';     // �ƥ�����
        // $end_date = '20040331';     // �ƥ�����
        $_SESSION['mate_span']  = $span;            // �ݥ��ȥǡ����򥻥å�������¸
        $_SESSION['mate_sdate'] = $str_date;        // �ݥ��ȥǡ����򥻥å�������¸
        $_SESSION['mate_edate'] = $end_date;        // �ݥ��ȥǡ����򥻥å�������¸
        $div = $_REQUEST['div'];
        $_SESSION['mate_div'] = $_REQUEST['div'];      // �ݥ��ȥǡ����򥻥å�������¸
        switch ($div) {
        case ' ':   // �����碪�����롼��
            $search_div = '';
            break;
        case 'C':   // ���ץ�
            $search_div = "and uri.������='C' and sch.note15 not like 'SC%'";
            break;
        case 'S':   // ���ץ�����
            $search_div = "and uri.������='C' and sch.note15 like 'SC%'";
            break;
        case 'L':   // ��˥�
            //$search_div = "and uri.������='L' and (uri.assyno not like 'LC%' and uri.assyno not like 'LR%')";
            $search_div = "and uri.������='L' and (uri.assyno not like 'LC%' and uri.assyno not like 'LR%') and CASE WHEN uri.assyno = '' THEN uri.������='L' ELSE item.midsc not like 'DPE%%' END";
            break;
        case 'B':   // �Х����
            //$search_div = "and uri.������='L' and (uri.assyno like 'LC%' or uri.assyno like 'LR%')";
            $search_div = "and uri.������='L' and (uri.assyno like 'LC%' or uri.assyno like 'LR%' or item.midsc like 'DPE%%')";
            break;
        case 'T':   // ����
            $search_div = "and uri.������='T'";
            break;
        default:
            $search_div = '';
        }
        if ($div != 'S' && $div != 'C') {      // ���ץ�ʳ�
            $query = "
                select count(*) from
                (
                    select uri.assyno
                    from
                        hiuuri as uri
                    left outer join
                        -- (select assy_no
                        --     from
                        --         material_cost_header
                        --     left outer join
                        --         hiuuri as uri
                        --     on(plan_no=�ײ��ֹ�)
                        --     where
                        --             �׾���>={$str_date}
                        --         and �׾���<={$end_date}
                        --         and datatype='1'
                        --         $search_div
                        -- ) as mate
                        material_cost_header as mate -- Ⱦ���ǹʤ���ि��嵭���ɲ�
                    on (uri.�ײ��ֹ� = mate.plan_no)
                    -- on (uri.assyno = mate.assy_no)
                    left outer join
                        miitem as item
                    on (uri.assyno = item.mipn)
                    where 
                        uri.�׾���>={$str_date}
                        and uri.�׾���<={$end_date}
                        and uri.datatype='1'
                        and mate.assy_no is NULL
                        $search_div
                    order by uri.assyno
                )
                as assy_no
            ";
        } elseif ($div == 'C') {    // ���ץ�ɸ��ʤ�
            $query = "
                select count(*) from
                (
                    select uri.assyno
                    from
                        hiuuri as uri
                    left outer join
                        -- (select assy_no
                        --     from
                        --         material_cost_header
                        --     left outer join
                        --         hiuuri as uri
                        --     on(plan_no=�ײ��ֹ�)
                        --     where
                        --             �׾���>={$str_date}
                        --         and �׾���<={$end_date}
                        --         and datatype='1'
                        -- ) as mate
                        material_cost_header as mate -- Ⱦ���ǹʤ���ि��嵭���ɲ�
                    on (uri.�ײ��ֹ� = mate.plan_no)
                    -- on (uri.assyno = mate.assy_no)
                    left outer join
                          assembly_schedule as sch
                    on (uri.�ײ��ֹ�=sch.plan_no)
                    left outer join
                        miitem as item
                    on (uri.assyno = item.mipn)
                    where 
                        uri.�׾���>={$str_date}
                        and uri.�׾���<={$end_date}
                        and uri.datatype='1'
                        and mate.assy_no is NULL
                        $search_div
                    order by uri.assyno
                )
                as assy_no
            ";
        } elseif ($div == 'S') {    // ���ץ�����ʤ�
            $query = "
                select count(*) from
                (
                    select uri.assyno
                    from
                        hiuuri as uri
                    left outer join
                        material_cost_header as mate -- �����Ⱦ���ǹʤ���ޤʤ�
                    on (uri.�ײ��ֹ� = mate.plan_no)
                    left outer join
                          assembly_schedule as sch
                    on (uri.�ײ��ֹ�=sch.plan_no)
                    left outer join
                        miitem as item
                    on (uri.assyno = item.mipn)
                    where 
                        uri.�׾���>={$str_date}
                        and uri.�׾���<={$end_date}
                        and uri.datatype='1'
                        and mate.assy_no is NULL
                        -- and mate.plan_no IS NULL
                        $search_div
                    order by uri.assyno
                )
                as assy_no
            ";
        }
        if (getUniResult($query, $maxrows) <= 0) {
            $_SESSION['s_sysmsg'] = '��ץ쥳���ɿ��μ����˼���';
        } else {
            $_SESSION['material_max'] = $maxrows;
        }
    }
    $plan_no = '';  // ������Τ�
} else {        // ���ǡ����ǡ�����¸ �λ���
    if (isset($_SESSION['mate_div'])) {
        $_REQUEST['div'] = $_SESSION['mate_div'];       // �ݥ��ȥǡ����򥨥ߥ�졼��
        $div      = $_REQUEST['div'];
        $maxrows  = $_SESSION['material_max'];          // ��ץ쥳���ɿ�������
        $span     = $_SESSION['mate_span'];             // �оݴ���radio�ܥ��������
        $str_date = $_SESSION['mate_sdate'];            // �������դ�����
        $end_date = $_SESSION['mate_edate'];            // ��λ���դ�����
        switch ($div) {
        case ' ':   // ������
            $search_div = '';
            break;
        case 'C':   // ���ץ�
            $search_div = "and uri.������='C' and sch.note15 not like 'SC%'";
            break;
        case 'S':   // ���ץ�����
            $search_div = "and uri.������='C' and sch.note15 like 'SC%'";
            break;
        case 'L':   // ��˥�
            //$search_div = "and uri.������='L' and (uri.assyno not like 'LC%' and uri.assyno not like 'LR%')";
            $search_div = "and uri.������='L' and (uri.assyno not like 'LC%' and uri.assyno not like 'LR%') and (item.midsc not like 'DPE%%')";
            break;
        case 'B':   // �Х����
            //$search_div = "and uri.������='L' and (uri.assyno like 'LC%' or uri.assyno like 'LR%')";
            $search_div = "and uri.������='L' and (uri.assyno like 'LC%' or uri.assyno like 'LR%' or item.midsc like 'DPE%%')";
            break;
        case 'T':   // ����
            $search_div = "and uri.������='T'";
            break;
        default:
            $search_div = '';
        }
    }
    if (isset($_SESSION['material_plan_no'])) {
        $plan_no = $_SESSION['material_plan_no'];
    } else {
        $plan_no = '';
    }
}

//////////// ���ǤιԿ�
if (isset($_SESSION['material_page'])) {                // ���Ǥ�ɽ���Կ���ƽи�������Ǥ���褦�ˤ��뤿��
    define('PAGE', $_SESSION['material_page']);
} else {
    define('PAGE', 20);
}

//////////// �ڡ������ե��å�����
$offset = $session->get_local('offset');
if ($offset == '') $offset = 0;         // �����
if ( isset($_REQUEST['forward']) ) {                       // ���Ǥ������줿
    $offset += PAGE;
    if ($offset >= $maxrows) {
        $offset -= PAGE;
        if ($_SESSION['s_sysmsg'] == '') {
            $_SESSION['s_sysmsg'] .= "<font color='yellow'>���ǤϤ���ޤ���</font>";
        } else {
            $_SESSION['s_sysmsg'] .= "<br><font color='yellow'>���ǤϤ���ޤ���</font>";
        }
    }
} elseif ( isset($_REQUEST['backward']) ) {                 // ���Ǥ������줿
    $offset -= PAGE;
    if ($offset < 0) {
        $offset = 0;
        if ($_SESSION['s_sysmsg'] == '') {
            $_SESSION['s_sysmsg'] .= "<font color='yellow'>���ǤϤ���ޤ���</font>";
        } else {
            $_SESSION['s_sysmsg'] .= "<br><font color='yellow'>���ǤϤ���ޤ���</font>";
        }
    }
} elseif ( isset($_REQUEST['page_keep']) ) {                // ���ߤΥڡ�����ݻ����� POST & GET�ǡ���
    $offset = $offset;
} else {
    $offset = 0;                           // ���ξ��ϣ��ǽ����
}
$session->add_local('offset', $offset);

////////////// ɽ����(����ɽ)��̤��Ͽ�ǡ�����SQL�Ǽ���
if (isset($_REQUEST['div'])) {
    if ($div != 'S' && $div != 'C') {      // ���ץ�ʳ�
        $query = "
            select  uri.assyno                      as �����ֹ�         -- 0
                ,   trim(substr(item.midsc, 1, 32)) as ����̾           -- 1
                ,   uri.�ײ��ֹ�                    as �ײ��ֹ�         -- 2
                ,   uri.����                        as ����           -- 3
                ,   uri.�׾���                      as �����           -- 4
            from
                hiuuri as uri
            left outer join
                miitem as item
            on (uri.assyno = item.mipn)
            left outer join
                -- (select assy_no
                --     from
                --         material_cost_header
                --     left outer join
                --         hiuuri as uri
                --     on(plan_no=�ײ��ֹ�)
                --     where
                --             �׾���>={$str_date}
                --         and �׾���<={$end_date}
                --         and datatype='1'
                --         $search_div
                -- ) as mate
                material_cost_header as mate -- Ⱦ���ǹʤ���ि��嵭���ɲ�
            -- on (uri.assyno = mate.assy_no)
            on (uri.�ײ��ֹ� = mate.plan_no)
            where 
                uri.�׾���>={$str_date}
                and uri.�׾���<={$end_date}
                and uri.datatype='1'
                and mate.assy_no is NULL
                $search_div
            order by uri.assyno ASC
            offset $offset limit
        " . PAGE;
    } elseif ($div == 'C') {    // ���ץ�ɸ��ʤ�
        $query = "
            select  uri.assyno                      as �����ֹ�         -- 0
                ,   trim(substr(item.midsc, 1, 32)) as ����̾           -- 1
                ,   uri.�ײ��ֹ�                    as �ײ��ֹ�         -- 2
                ,   uri.����                        as ����           -- 3
                ,   uri.�׾���                      as �����           -- 4
            from
                hiuuri as uri
            left outer join
                miitem as item
            on (uri.assyno = item.mipn)
            left outer join
                -- (select assy_no
                --     from
                --         material_cost_header
                --     left outer join
                --         hiuuri as uri
                --     on(plan_no=�ײ��ֹ�)
                --     where
                --             �׾���>={$str_date}
                --         and �׾���<={$end_date}
                --         and datatype='1'
                -- ) as mate
                material_cost_header as mate -- Ⱦ���ǹʤ���ि��嵭���ɲ�
            -- on (uri.assyno = mate.assy_no)
            on (uri.�ײ��ֹ� = mate.plan_no)
            left outer join
                  assembly_schedule as sch
            on (uri.�ײ��ֹ�=sch.plan_no)
            where 
                uri.�׾���>={$str_date}
                and uri.�׾���<={$end_date}
                and uri.datatype='1'
                and mate.assy_no is NULL
                $search_div
            order by uri.assyno ASC
            offset $offset limit
        " . PAGE;
    } elseif ($div == 'S') {    // ���ץ�����ʤ�
        $query = "
            select  uri.assyno                      as �����ֹ�         -- 0
                ,   trim(substr(item.midsc, 1, 32)) as ����̾           -- 1
                ,   uri.�ײ��ֹ�                    as �ײ��ֹ�         -- 2
                ,   uri.����                        as ����           -- 3
                ,   uri.�׾���                      as �����           -- 4
            from
                hiuuri as uri
            left outer join
                miitem as item
            on (uri.assyno = item.mipn)
            left outer join
                material_cost_header as mate -- �����Ⱦ���ǹʤ���ޤʤ�(�ײ��ֹ椬���Фξ��)
            on (uri.�ײ��ֹ� = mate.plan_no)
            left outer join
                  assembly_schedule as sch
            on (uri.�ײ��ֹ�=sch.plan_no)
            where 
                uri.�׾���>={$str_date}
                and uri.�׾���<={$end_date}
                and uri.datatype='1'
                and mate.assy_no is NULL
                -- and mate.plan_no IS NULL
                $search_div
            order by uri.assyno ASC
            offset $offset limit
        " . PAGE;
    }
    $res = array();
    if (($rows = getResultWithField3($query, $field, $res)) <= 0) {
        $_SESSION['s_sysmsg'] = "̤��Ͽ�Ϥ���ޤ���";
        unset($_REQUEST['div']);      // �Ȳ�μ¹Ԥ�ꥻ�å�
    } else {
        $num = count($field);       // �ե�����ɿ�����
        for ($r=0; $r<$rows; $r++) {
            $res[$r][1] = mb_convert_kana($res[$r][1], 'ka', 'EUC-JP');   // ���ѥ��ʤ�Ⱦ�ѥ��ʤإƥ���Ū�˥���С���
        }
        $div = $_REQUEST['div'];
    }
} else {
    $div  = ' ';    // default�ͤ���¸
    $span = 0;      // default�ͤ���¸ 0=���� 1=��Ⱦ��
}

/////////// HTML Header ����Ϥ��ƥ���å��������
$menu->out_html_header();
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=EUC-JP">
<meta http-equiv="Content-Style-Type" content="text/css">
<meta http-equiv='Content-Script-Type' content='text/javascript'>
<title><?php echo $menu->out_title() ?></title>
<?php echo $menu->out_site_java() ?>
<?php echo $menu->out_css() ?>
<!--    �ե��������ξ��
<script type='text/javascript' language='JavaScript' src='template.js?<?php echo $uniq ?>'>
</script>
-->

<script language="JavaScript">
<!--
/* ����ʸ�����������ɤ��������å� */
function isDigit(str) {
    var len=str.length;
    var c;
    for (i=1; i<len; i++) {
        c = str.charAt(i);
        if ((c < "0") || (c > "9")) {
            return true;
        }
    }
    return false;
}

function chk_assy_entry(obj) {
    // obj.assy.value = obj.assy.value.toUpperCase();
    // ���ߤϻ��Ѥ��Ƥ��ʤ�
    return true;
}

/* ������ϥե�����Υ�����Ȥ˥ե������������� */
<?php if (!isset($_REQUEST['div'])) { ?>
function set_focus(){
    document.entry_form.div.focus();      // ������ϥե����ब������ϥ����Ȥ򳰤�
    // document.entry_form.div.select();
}
<?php } else { ?>
function set_focus(){
    document.page_form.confirm.focus();      // ������ϥե����ब������ϥ����Ȥ򳰤�
    // document.entry_form.div.select();
}
<?php } ?>

/* select���ѹ������Ȥ���¨�¹� */
function select_send(obj)
{
    document.mac_form.submit();
}
// -->
</script>

<!-- �������륷���ȤΥե��������򥳥��� HTML���� �����Ȥ�����Ҥ˽���ʤ��������
<link rel='stylesheet' href='template.css?<?php echo $uniq ?>' type='text/css' media='screen'>
-->

<style type="text/css">
<!--
.pt10 {
    font-size:      10pt;
    font-weight:    normal;
    font-family:    monospace;
}
.pt10b {
    font-size:      10pt;
    font-weight:    bold;
    font-family:    monospace;
}
.ki_non {
    font-size:      11pt;
    font-weight:    normal;
    font-family:    monospace;
}
.ki_chk {
    font-size:      11pt;
    font-weight:    bold;
    font-family:    monospace;
    color:          blue;
}
.pt11b {
    font-size:      11pt;
    font-weight:    bold;
    font-family:    monospace;
}
.assy_font {
    font-size:      13pt;
    font-weight:    bold;
    text-align:     left;
    font-family:    monospace;
}
th {
    background-color:   blue;
    color:              yellow;
    font-size:          10pt;
    font-weight:        bold;
    font-family:        monospace;
}
a:hover {
    background-color:   blue;
    color:              white;
}
a:active {
    background-color:   gold;
    color:              black;
}
a {
    color:   blue;
}
p {
    font-size:          11pt;
    font-weight:        bold;
    font-family:        monospace;
}
-->
</style>
</head>
<body onLoad='set_focus()' style='overflow-y:hidden;'>
    <center>
<?php echo $menu->out_title_border() ?>
        
        <table bgcolor='#d6d3ce' align='center' border='1' cellspacing='0' cellpadding='3'>
            <tr><td> <!----------- ���ߡ�(�ǥ�������) ------------>
        <table width='100%' class='winbox_field' align='center' border='1' cellspacing='0' cellpadding='3'>
            <form name='entry_form' method='post' action='<?php echo $menu->out_self() ?>' onSubmit='return chk_assy_entry(this)'>
                <tr>
                    <td class='winbox' nowrap align='center'>
                        <div class='caption_font'>��������򤷤Ʋ�����</div>
                    </td>
                    <td class='winbox' align='center'>
                        <select name='div' class='assy_font' onChange='document.entry_form.submit()'>
                            <!-- <option value=" "<?php if($div==" ") echo("selected"); ?>>�����롼��</option> -->
                            <option value="C"<?php if($div=="C") echo("selected"); ?>>���ץ�ɸ��</option>
                            <option value="S"<?php if($div=="S") echo("selected"); ?>>���ץ�����</option>
                            <option value="L"<?php if($div=="L") echo("selected"); ?>>��˥�ɸ��</option>
                            <option value="B"<?php if($div=="B") echo("selected"); ?>>���Υݥ��</option>
                            <option value="T"<?php if($div=="T") echo("selected"); ?>>�ġ���</option>
                        </select>
                        <input class='pt11b' type='submit' name='execute' value='�¹�'>
                    </td>
                    <?php if ($span == 0) { ?>
                    <td class='winbox' nowrap>
                        <div class='ki_chk'>
                        <input type='radio' name='span' value='0' id='konki' checked><label for='konki'>����ʬ
                        </div>
                    </td>
                    <td class='winbox' nowrap>
                        <div class='ki_non'>
                        <input type='radio' name='span' value='1' id='zenki'><label for='zenki'>��Ⱦ��
                        </div>
                    </td>
                    <?php } else { ?>
                    <td class='winbox' nowrap>
                        <div class='ki_non'>
                        <input type='radio' name='span' value='0' id='konki'><label for='konki'>����ʬ
                        </div>
                    </td>
                    <td class='winbox' nowrap>
                        <div class='ki_chk'>
                        <input type='radio' name='span' value='1' id='zenki' checked><label for='zenki'>��Ⱦ��
                        </div>
                    </td>
                    <?php } ?>
                </tr>
            </form>
        </table>
            </td></tr>
        </table> <!----------------- ���ߡ�End ------------------>
        
        <?php if (!isset($_REQUEST['div'])) { ?>
        <br>
        <table style='border: 2px solid #CCBBAA;'>
            <tr>
                <td align='center'>
                    <p>�����ˤ�¿�����֤�������ޤ��ΤǤ��Ԥ���������</p>
                </td>
            </tr>
            <tr>
                <td align='left'>
                    <p>
                        ���ξȲ��ɸ���ʤ�ᥤ��ˤ��Ƥ��ޤ��Τ���������Ⱦ���١����Ǥ���Ͽ�����Ǥ���
                        <br>
                        �ʣ�����֤δ֣��٤���Ͽ���ʤ�ʪ��ɽ������ޤ�����
                        <br>
                        �����ץ�����˴ؤ��ƤϾ嵭��Ŭ�Ѥ��줺���ηײ��ֹ�����������Ͽ����Ƥʤ�
                        <br>
                        �����Ƥ�ʪ��ɽ������ޤ���
                    </p>
                </td>
            </tr>
        </table>
        <?php } ?>
        
        <?php if (isset($_REQUEST['div'])) { ?>
        
        <!----------------- ������ ���� ���� �Υե����� ---------------->
        <table width='100%' cellspacing="0" cellpadding="0" border='0'>
            <form name='page_form' method='post' action='<?php echo $menu->out_self() ?>'>
                <tr>
                    <td align='left'>
                        <table align='left' border='3' cellspacing='0' cellpadding='0'>
                            <td align='left'>
                                <input class='pt10b' type='submit' name='backward' value='����'>
                            </td>
                        </table>
                    </td>
                    <td align='center'>
                        <table align='center' border='3' cellspacing='0' cellpadding='0'>
                            <td align='center'>
                                <input class='pt10b' type='submit' name='confirm' value=' O K '>
                            </td>
                        </table>
                    </td>
                    <!--
                    <td nowrap align='center' class='caption_font'>
                    </td>
                    -->
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
        <table width='100%' class='winbox_field' bgcolor='#d6d3ce' align='center' border='1' cellspacing='0' cellpadding='3'>
            <thead>
                <!-- �ơ��֥� �إå�����ɽ�� -->
                <tr>
                    <th class='winbox' nowrap width='10'>No.</th>        <!-- �ԥʥ�С���ɽ�� -->
                <?php
                for ($i=0; $i<$num; $i++) {             // �ե�����ɿ�ʬ���֤�
                    echo "<th class='winbox' nowrap>{$field[$i]}</th>\n";
                }
                ?>
                    <th class='winbox' nowrap>��Ͽ���̤�</th>
                </tr>
            </thead>
            <tfoot>
                <!-- ���ߤϥեå����ϲ���ʤ� -->
            </tfoot>
            <tbody>
                <?php
                $res[-1][0] = '';  // ���ߡ�
                for ($r=0; $r<$rows; $r++) {
                    $recNo = ($offset + $r);
                    if ($session->get_local('recNo') == $recNo) {
                        echo "<tr style='background-color:#ffffc6;'>\n";
                    } else {
                        echo "<tr onMouseOver=\"style.background='#ceffce'\" onMouseOut=\"style.background='#d6d3ce'\">\n";
                    }
                    echo "    <td class='winbox' nowrap align='right'><div class='pt10b'>", ($r + $offset + 1), "</div></td>    <!-- �ԥʥ�С���ɽ�� -->\n";
                    for ($i=0; $i<$num; $i++) {         // �쥳���ɿ�ʬ���֤�
                        // <!--  bgcolor='#ffffc6' �������� --> 
                        switch ($i) {
                        case 0:     // �����ֹ�
                            if ($res[$r][$i] != $res[$r-1][$i]) {
                                echo "<td class='winbox' nowrap width='80' align='center'><a class='pt10' href='JavaScript:baseJS.Ajax(\"{$menu->out_self()}?recNo={$recNo}\");location.replace(\"", $menu->out_action('������������'), "?assy=", urlencode($res[$r][$i]), "&material=1&plan_no=", urlencode($res[$r][2]), "\")' target='application' style='text-decoration:none;'>{$res[$r][$i]}</a></td>\n";
                            } else {
                                echo "<td class='winbox' nowrap width='80' align='center'><div class='pt10'>��</div></td>\n";
                            }
                            break;
                        case 1:     // ����̾
                            if ($res[$r][0] != $res[$r-1][0]) {
                                echo "<td class='winbox' nowrap width='270' align='left'><div class='pt10'>", $res[$r][$i], "</div></td>\n";
                            } else {
                                echo "<td class='winbox' nowrap width='270' align='center'><div class='pt10'>��</div></td>\n";
                            }
                            break;
                        case 2:     // �ײ��ֹ�
                            if ($_SESSION['User_ID'] == '300667') {
                                echo "<td class='winbox' nowrap width='80' align='center'><a class='pt10' href='JavaScript:baseJS.Ajax(\"{$menu->out_self()}?recNo={$recNo}\");location.replace(\"", $menu->out_action('��������Ѱ�������ɽ��ɽ��'), "?plan_no=", urlencode($res[$r][$i]), "&material=1\")' target='application' style='text-decoration:none;'>{$res[$r][$i]}</a></td>\n";
//                                echo "<td class='winbox' nowrap width='80' align='center'><a class='pt10' href='JavaScript:baseJS.Ajax(\"{$menu->out_self()}?recNo={$recNo}\");location.replace(\"", $menu->out_action('��������Ѱ�������ɽ��ɽ��TEST'), "?plan_no=", urlencode($res[$r][$i]), "&material=1\")' target='application' style='text-decoration:none;'>{$res[$r][$i]}</a></td>\n";
/**
                            } else if( $_SESSION['User_ID'] == '300144' || $_SESSION['User_ID'] == '970352' ) {
                                echo "<td class='winbox' nowrap width='80' align='center'><a class='pt10' href='JavaScript:baseJS.Ajax(\"{$menu->out_self()}?recNo={$recNo}\");location.replace(\"", $menu->out_action('��������Ѱ�������ɽ��ɽ��'), "?plan_no=", urlencode($res[$r][$i]), "&material=1\")' target='application' style='text-decoration:none;'>{$res[$r][$i]}</a></td>\n";
/**/
                            } else {
                                echo "<td class='winbox' nowrap width='80' align='center'><a class='pt10' href='JavaScript:baseJS.Ajax(\"{$menu->out_self()}?recNo={$recNo}\");location.replace(\"", $menu->out_action('��������Ѱ�������ɽ��ɽ��'), "?plan_no=", urlencode($res[$r][$i]), "&material=1\")' target='application' style='text-decoration:none;'>{$res[$r][$i]}</a></td>\n";
//                                echo "<td class='winbox' nowrap width='80' align='center'><a class='pt10' href='JavaScript:baseJS.Ajax(\"{$menu->out_self()}?recNo={$recNo}\");location.replace(\"", $menu->out_action('��������ɽ��ɽ��'), "?plan_no=", urlencode($res[$r][$i]), "&material=1\")' target='application' style='text-decoration:none;'>{$res[$r][$i]}</a></td>\n";
                            }
                            break;
                        case 3:     // ����
                            echo "<td class='winbox' nowrap width='60' align='right'><div class='pt10'>", number_format($res[$r][$i], 0), "</div></td>\n";
                            break;
                        case 4:     // �����
                            echo "<td class='winbox' nowrap width='80' align='center'><div class='pt10'>", format_date($res[$r][$i]), "</div></td>\n";
                            break;
                        default:    // ����¾
                            echo "<td class='winbox' nowrap align='center'><div class='pt10'>", $res[$r][$i], "</div></td>\n";
                        }
                        // <!-- ����ץ�<td rowspan='2' colspan='3' width='200' align='center' class='pt10b' bgcolor='#ffffc6'>  </td> -->
                    }
                        echo "<td class='winbox' nowrap align='center'><a class='pt10' href='JavaScript:baseJS.Ajax(\"{$menu->out_self()}?recNo={$recNo}\");location.replace(\"", $menu->out_action('����������Ͽ'), "?plan_no=", urlencode($res[$r][2]), "&assy_no=", urlencode($res[$r][0]), "\")' target='application' style='text-decoration:none;'>��Ͽ</a></td>\n";
                    echo "</tr>\n";
                }
                ?>
            </tbody>
        </table>
            </td></tr>
        </table> <!----------------- ���ߡ�End ------------------>
        <table width='100%' cellspacing="0" cellpadding="0" border='0'> <!-- ���ߡ�Start -->
            <form name='confirm_form' method='post' action='<?php echo $menu->out_self() ?>'>
                <table align='center' border='3' cellspacing='0' cellpadding='0'>
                    <tr>
                    <td align='right'>
                        <input class='pt10b' type='submit' name='confirm' value=' O K '>
                    </td>
                    </tr>
                </table>
            </form>
        </table> <!----------------- ���ߡ�End ------------------>
        <?php } ?>
        
    </center>
</body>
<?php echo $menu->out_alert_java()?>
</html>
<?php
ob_end_flush();                 // ���ϥХåե���gzip���� END
?>
