<?php
//////////////////////////////////////////////////////////////////////////////
// �������ʹ���ɽ�ξȲ�  �ײ��ֹ��ɽ�� view                                //
//                              Allocated Configuration Parts ������������  //
// Copyright (C) 2004-2019 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp      //
// Changed history                                                          //
// 2004/05/28 Created  allo_conf_parts_view.php                             //
// 2004/06/07 �꥿���󥢥ɥ쥹�������ƽи����襻�å�������¸���Ƥ���    //
// 2004/12/08 CC���ʤ�TNKCC��ɽ���ɲ�                                       //
// 2004/12/28 MenuHeader class ����Ѥ��ƶ��̥�˥塼���ڤ�ǧ���������ѹ�   //
//    �ǥ��쥯�ȥ��industry��industry/material���ѹ�unregist����θƽ��б� //
// 2005/01/07 $menu->set_retGET('page_keep', $_REQUEST['material']);������  //
// 2005/01/12 ����̾��trim(substr(midsc,1,25))��trim(substr(midsc,1,21))�ѹ�//
// 2005/01/31 �����ֹ椫����ֹ�إޡ����ѹ� &row={$r} ���ɲä��б�         //
// 2005/02/07 $search = sprintf("where plan_no='%s'", $plan_no); �򢭤��ѹ� //
//            where plan_no='%s' and assy_no='%s'", $plan_no, $assy_no);    //
// 2005/05/20 db_connect() �� funcConnect() ���ѹ� pgsql.php������Τ���    //
// 2006/04/13 <a name='mark'�ˤ��ե���������ư�б��ǡ�setTimeout()���ɲ�  //
// 2006/08/01 ��ץ쥳���ɿ� �������˰�����̵����н�λ���ɲ�               //
// 2006/12/01 ���֥륯��å������פʰ����������뵡ǽ���ɲ�delParts����ɬ��//
// 2006/12/18 �嵭�ε�ǽ��Ȥä�����꥿��������ݻ����뤿��$param�ɲ�  //
// 2007/02/20 parts/����parts/parts_stock_history/parts_stock_view.php���ѹ�//
// 2007/02/22 set_caption()�˹����ֹ��ɲá������ֹ�10pt��11pt,�ٵ�����//
// 2007/03/22 parts_stock_view.php �� parts_stock_history_Main.php ���ѹ�   //
// 2007/03/24 �ǥ��쥯�ȥ�material/��parts/allocate_config/ �ե졼���Ǥ��ѹ�//
// 2007/09/03 �Ť�$_SESSION['offset']��¾�ȶ��礹�뤿��$session->add_local  //
//            �Ĥ��Ǥ�$_POST/$_GET �� $_REQUEST ���ѹ�                      //
// 2016/08/08 mouseOver���ɲ�                                          ��ë //
// 2017/06/28 A������ξȲ���б�                                      ��ë //
// 2019/05/16 mark�����ޤ�ȿ�����Ƥ��ʤ��ä��Τǽ���(tr�Ǥ�̵��No��)   ��ë //
// 2019/10/17 ���ʤ�ưŪ�˰�������Ͽ����Ʊ���¤Ӥ�ɽ��������         ���� //
// 2020/06/01 �Ȳ�ǡ�������Ͽ���̤إ��ԡ�����                         ���� //
// 2020/08/01 �����ʤϡ�IsAlternative() or IsNoSubstitute() �˿���ɲ� ���� //
//////////////////////////////////////////////////////////////////////////////
ini_set('error_reporting', E_ALL);          // E_ALL='2047' debug ��
// ini_set('display_errors', '1');             // Error ɽ�� ON debug �� ��꡼���女����
// ini_set('implicit_flush', 'off');           // echo print �� flush �����ʤ�(�٤��ʤ뤿��) CLI CGI��
// ini_set('max_execution_time', 1200);        // ����¹Ի���=20ʬ CLI CGI��
ob_start('ob_gzhandler');                   // ���ϥХåե���gzip����
session_start();                            // ini_set()�μ��˻��ꤹ�뤳�� Script �Ǿ��

require_once ('../../../function.php');     // define.php �� pgsql.php �� require_once ���Ƥ���
require_once ('../../../tnk_func.php');     // TNK �˰�¸������ʬ�δؿ��� require_once ���Ƥ���
require_once ('../../../MenuHeader.php');   // TNK ������ menu class
require_once ('../../../ControllerHTTP_Class.php');// TNK ������ MVC Controller Class
//////////// ���å����Υ��󥹥��󥹤���Ͽ
$session = new Session();
// �ʲ��Ϥޤ����Ѥ��Ƥ��ʤ�
if (isset($_REQUEST['recNo'])) {
    $session->add_local('recNo', $_REQUEST['recNo']);
    exit();
}
// access_log();                               // Script Name �ϼ�ư����

///// TNK ���ѥ�˥塼���饹�Υ��󥹥��󥹤����
$menu = new MenuHeader(0);                  // ǧ�ڥ����å�0=���̰ʾ� �����=TOP_MENU �����ȥ�̤����

////////////// ����������
$menu->set_site(INDEX_INDUST, 26);          // site_index=30(������˥塼) site_id=26(�������ʹ���ɽ�ξȲ�)
////////////// �꥿���󥢥ɥ쥹����
// $menu->set_RetUrl(INDUST_MENU);          // �̾�ϻ��ꤹ��ɬ�פϤʤ�
//////////// �����ȥ�̾(�������Υ����ȥ�̾�ȥե�����Υ����ȥ�̾)
$menu->set_title('���� ���� ����ɽ �� �Ȳ�');
////////////// target����
$menu->set_target('_parent');               // �ե졼���Ǥ�������target°����ɬ��
//////////// ��ʬ��ե졼��������Ѥ���
//$menu->set_self(INDUST . 'parts/allocate_config/allo_conf_parts_Main.php');
$menu->set_self(INDUST . 'parts/allocate_config_entry/allo_conf_parts_Main.php');
//////////// �ƽ����action̾�ȥ��ɥ쥹����
$menu->set_action('�߸˷���',   INDUST . 'parts/parts_stock_history/parts_stock_history_Main.php');
$menu->set_action('���������Ͽ',   INDUST . 'material/material_entry/materialCost_entry_main.php');
//////////// �꥿������ξ�������
if (isset($_REQUEST['plan_cond'])) {    // �ײ��ֹ�����Ͼ��֤�����å�(�ե����फ��θƽ��б�)
    $menu->set_retGET('plan', $_REQUEST['plan_cond']);
}
if (isset($_REQUEST['material'])) {     // ��������̤��Ͽ����θƽ��б�
    $menu->set_retGET('page_keep', $_REQUEST['material']);
    $parts_no = @$_SESSION['stock_parts'];
    if (isset($_REQUEST['row'])) {
        $row_no = $_REQUEST['row'];   // ����ƽФ������ֹ�
        $param  = "&material={$_REQUEST['material']}&row={$_REQUEST['row']}";
    } else {
        $row_no = -1;       // ̤��Ͽ�ꥹ�Ȥ���ƤФ줿��
        $param  = "&material={$_REQUEST['material']}";
        $inquiries_only = false; // ��Ͽ���ꥢ��ɽ��
        $_SESSION['inquiries_only'] = $inquiries_only; // ���å����˥��å�
    }
} else {
    $parts_no = '';
    $row_no   = '-1';       // ñ�ΤǾȲ񤵤줿��
    $param    = '';
    $inquiries_only = true; // �Ȳ�Τ�ɽ��
    $_SESSION['inquiries_only'] = $inquiries_only; // ���å����˥��å�
}

// ������Ͽ�ե饰
if (isset($_REQUEST['comp_regi'])) {
    define('COMPREGI', true);
} else {
    define('COMPREGI', false);
}

//////////// JavaScript Stylesheet File ��ɬ���ɤ߹��ޤ���
$uniq = uniqid('target');

//////////// ���ǤιԿ�
define('PAGE', '300');      // ���ߤ�300��ۤ�����������Ϥʤ�

//////////// �ײ��ֹ桦�����ֹ��ꥯ�����Ȥ������(�������������Ͽ�ǻ���)
if (isset($_REQUEST['plan_no'])) {
    $plan_no = $_REQUEST['plan_no'];
    $_SESSION['material_plan_no'] = $plan_no;   // ���å�������¸
    $_SESSION['plan_no'] = $plan_no;            // �ե������ѤΥǡ����ˤ���¸
    //////////// �ײ��ֹ桦�����ֹ�򥻥å���󤫤����(�ե����फ��ξȲ�ǻ���)
} elseif (isset($_SESSION['plan_no'])) {
    $plan_no = $_SESSION['plan_no'];
} else {
    $_SESSION['s_sysmsg'] .= '�ײ��ֹ椬���ꤵ��Ƥʤ���';      // .= ��å��������ɲä���
    header('Location: ' . H_WEB_HOST . $menu->out_RetUrl());    // ľ���θƽи������
    exit();
}
//////////// SC���֤�ꥯ�����Ȥ������(���A������ξȲ�ǻ��ѡ�)
if (isset($_REQUEST['sc_no'])) {
    $sc_no = $_REQUEST['sc_no'];
    $_SESSION['material_sc_no'] = $sc_no;   // ���å�������¸
    $_SESSION['sc_no'] = $sc_no;            // �ե������ѤΥǡ����ˤ���¸
    //////////// �ײ��ֹ桦�����ֹ�򥻥å���󤫤����(�ե����फ��ξȲ�ǻ���)
} elseif (isset($_SESSION['sc_no'])) {
    $sc_no = $_SESSION['sc_no'];
} else {
    $sc_no = '';
    $_SESSION['material_sc_no'] = '';   // ���å�������¸
    $_SESSION['sc_no'] = '';            // �ե������ѤΥǡ����ˤ���¸
}
///// �����ֹ桦�����ֹ�μ���
$query = "SELECT parts_no, note15 from assembly_schedule where plan_no='{$plan_no}'";
if (getResult2($query, $assy_res) <= 0) {
    // .= ��å��������ɲä���
    $_SESSION['s_sysmsg'] .= "�ײ��ֹ桧{$plan_no} �ײ�ǡ������ʤ����� Assy�ֹ���������ޤ���";
    header('Location: ' . H_WEB_HOST . $menu->out_RetUrl());    // ľ���θƽи������
    exit();
} else {
    $assy_no = $assy_res[0][0];
    $kouji_no = $assy_res[0][1];
    if (substr($assy_no, 0, 1) == 'C') {    // assy_no��Ƭ����ǻ�������Ƚ��
        define('RATE', 25.60);  // ���ץ�
    } else {
        define('RATE', 37.00);  // ��˥�(����ʳ��ϸ��ߤʤ�)
    }
}

//////////// ����̾�μ���
$query = "select midsc from miitem where mipn='{$assy_no}'";
if ( getUniResult($query, $assy_name) <= 0) {           // ����̾�μ���
    $_SESSION['s_sysmsg'] .= "����̾�μ����˼���";      // .= ��å��������ɲä���
    header('Location: ' . H_WEB_HOST . $menu->out_RetUrl());    // ľ���θƽи������
    exit();
}

//////////// ɽ�������
$menu->set_caption("�ײ��ֹ桧{$plan_no}�������ֹ桧{$assy_no}������̾��{$assy_name}��<span style='color:red;'>������{$kouji_no}</span>");

//////////// SQL ʸ�� where ��� ���Ѥ���
$search = sprintf("where plan_no='%s' and assy_no='%s'", $plan_no, $assy_no);
// $search = '';

//////////// ��ץ쥳���ɿ���������ʿ��μ���     (�оݥǡ����κ������ڡ�������˻���)
$query = sprintf("select count(*) from allocated_parts %s", $search);
if ( getUniResult($query, $maxrows) <= 0) {         // $maxrows �μ���
    $_SESSION['s_sysmsg'] .= "��ץ쥳���ɿ��μ����˼���";      // .= ��å��������ɲä���
} else {
    if ($maxrows <= 0) {
        $_SESSION['s_sysmsg'] .= "����������ޤ���";      // .= ��å��������ɲä���
        header('Location: ' . H_WEB_HOST . $menu->out_RetUrl());    // ľ���θƽи������
        exit();
    }
}


//////////// �ڡ������ե��å�����
$offset = $session->get_local('offset');
if ($offset == '') $offset = 0;         // �����
if ( isset($_REQUEST['forward']) ) {                       // ���Ǥ������줿
    $offset += PAGE;
    if ($offset >= $maxrows) {
        $offset -= PAGE;
        if ($_SESSION['s_sysmsg'] == "") {
            $_SESSION['s_sysmsg'] .= "<font color='yellow'>���ǤϤ���ޤ���</font>";
        } else {
            $_SESSION['s_sysmsg'] .= "<br><font color='yellow'>���ǤϤ���ޤ���</font>";
        }
    }
} elseif ( isset($_REQUEST['backward']) ) {                // ���Ǥ������줿
    $offset -= PAGE;
    if ($offset < 0) {
        $offset = 0;
        if ($_SESSION['s_sysmsg'] == "") {
            $_SESSION['s_sysmsg'] .= "<font color='yellow'>���ǤϤ���ޤ���</font>";
        } else {
            $_SESSION['s_sysmsg'] .= "<br><font color='yellow'>���ǤϤ���ޤ���</font>";
        }
    }
} elseif ( isset($_REQUEST['page_keep']) || isset($_REQUEST['number']) ) {   // ���ߤΥڡ�����ݻ�����
    $offset = $offset;
} else {
    $offset = 0;                            // ���ξ��ϣ��ǽ����
}
$session->add_local('offset', $offset);


//////////// �ײ��ֹ�ñ�̤ι������٤κ�ɽ

$query_basic = "
        SELECT  parts_no    as �����ֹ�                 -- 0
                ,trim(substr(midsc,1,21))
                            as ����̾                   -- 1
                ,unit_qt    as ���ѿ�                   -- 2
                ,allo_qt    as ������                   -- 3
                ,sum_qt     as �и��߷�                 -- 4
                ,allo_qt - sum_qt
                            as �и˻�                   -- 5
                ,CASE
                    WHEN cond = '2' THEN 'ͭ��'
                    WHEN cond = '3' THEN '̵��'
                    ELSE cond
                END         as ���                     -- 6 ��ϻٵ���
                ,price      as ͭ��ñ��                 -- 7
                ,Uround(allo_qt * price, 2)
                            as ͭ�����                 -- 8
        FROM
            allocated_parts
        LEFT OUTER JOIN
            miitem ON parts_no=mipn
        ";

$query = sprintf("{$query_basic}
        %s 
        ORDER BY parts_no ASC OFFSET %d LIMIT %d
    ", $search, $offset, PAGE);       // ���� $search �Ǹ���
$res   = array();
$field = array();
if (($rows = getResultWithField2($query, $field, $res)) <= 0) {
    $_SESSION['s_sysmsg'] .= "<font color='yellow'><br>�����ǡ���������ޤ���</font>";
    $rows_view = $rows;
    // header('Location: ' . H_WEB_HOST . $menu->out_RetUrl());    // ľ���θƽи������
    // exit();
} else {
    $num = count($field);       // �ե�����ɿ�����
    /////////////// ɽ���Ѥ�����ǡ��������� view_data (�����ʤ�����ʤκǸ���¤��ؤ�����)
    $res_view   = array();
    $field_view = array();
    $rows_view  = 0;
    $num_view   = 0;
    $rec        = 0;
    $col        = 0;
    $query_basic = "SELECT parts_no
                        , trim(substr(midsc,1,21))
                        , unit_qt
                        , '-'
                        , '-'
                        , '-'
                        ,CASE
                            WHEN mtl_cond = '1' THEN '����'
                            WHEN mtl_cond = '2' THEN 'ͭ��'
                            WHEN mtl_cond = '3' THEN '̵��'
                            ELSE mtl_cond
                        END
                        , '-'
                        , '-'
                    FROM
                        parts_configuration
                    LEFT OUTER JOIN
                        miitem
                    ON parts_no=mipn
                    WHERE p_parts_no='%s' AND mtl_cond!='1' ORDER BY parts_no ASC";
    //////// Level1 Start
    for ($r=0; $r<$rows; $r++) {
        for ($c=0; $c<$num; $c++) {
            if ($c == 0) {
                $res_view[$rec][$col] = '.1';   // L1=��٥룱
                $col++;
                $res_view[$rec][$col] = $res[$r][$c];
                $col++;
            } else {
                $res_view[$rec][$col] = $res[$r][$c];
                $col++;
            }
        }
        $col = 0;
        $rec++;
        ////////// Level2 �����ʥǡ��������å�
        $query = sprintf($query_basic, $res[$r][0]);
        $res2 = array();
        if ( ($rows2=getResult2($query, $res2)) > 0) {         // ������ �μ���
            ////////// Level2 Start �����ʥǡ�������
            for ($r2=0; $r2<$rows2; $r2++) {
                for ($c2=0; $c2<$num; $c2++) {
                    if ($c2 == 0) {
                        $res_view[$rec][$col] = '..2';   // L2=��٥룲
                        $col++;
                        $res_view[$rec][$col] = $res2[$r2][$c2];
                        $col++;
                    } else {
                        $res_view[$rec][$col] = $res2[$r2][$c2];
                        $col++;
                    }
                }
                $col = 0;
                $rec++;
                ////////// Level3 �����ʥǡ��������å�
                $query = sprintf($query_basic, $res2[$r2][0]);
                $res3 = array();
                if ( ($rows3=getResult2($query, $res3)) > 0) {         // ������ �μ���
                    ////////// Level3 Start �����ʥǡ�������
                    for ($r3=0; $r3<$rows3; $r3++) {
                        for ($c3=0; $c3<$num; $c3++) {
                            if ($c3 == 0) {
                                $res_view[$rec][$col] = '...3';   // L3=��٥룳
                                $col++;
                                $res_view[$rec][$col] = $res3[$r3][$c3];
                                $col++;
                            } else {
                                $res_view[$rec][$col] = $res3[$r3][$c3];
                                $col++;
                            }
                        }
                        $col = 0;
                        $rec++;
                        ////////// Level4 �����ʥǡ��������å�
                        $query = sprintf($query_basic, $res3[$r3][0]);
                        $res4 = array();
                        if ( ($rows4=getResult2($query, $res4)) > 0) {         // ������ �μ���
                            ////////// Level4 Start �����ʥǡ�������
                            for ($r4=0; $r4<$rows4; $r4++) {
                                for ($c4=0; $c4<$num; $c4++) {
                                    if ($c4 == 0) {
                                        $res_view[$rec][$col] = '....4';   // L4=��٥룴
                                        $col++;
                                        $res_view[$rec][$col] = $res4[$r4][$c4];
                                        $col++;
                                    } else {
                                        $res_view[$rec][$col] = $res4[$r4][$c4];
                                        $col++;
                                    }
                                }
                                $col = 0;
                                $rec++;
                                ////////// Level5 �����ʥǡ��������å�
                                $query = sprintf($query_basic, $res4[$r4][0]);
                                $res5 = array();
                                if ( ($rows5=getResult2($query, $res5)) > 0) {         // ������ �μ���
                                    ////////// Level5 Start �����ʥǡ�������
                                    for ($r5=0; $r5<$rows5; $r5++) {
                                        for ($c5=0; $c5<$num; $c5++) {
                                            if ($c5 == 0) {
                                                $res_view[$rec][$col] = '.....5';   // L5=��٥룵
                                                $col++;
                                                $res_view[$rec][$col] = $res5[$r5][$c5];
                                                $col++;
                                            } else {
                                                $res_view[$rec][$col] = $res5[$r5][$c5];
                                                $col++;
                                            }
                                        }
                                        $col = 0;
                                        $rec++;
                                    }
                                }
                                ////////// Level5 End
                            }
                        }
                        ////////// Level4 End
                    }
                }
                ////////// Level3 End
            }
        }
        /////////// Level2 End
    }
    ///////// Level1 End
    
    ////// �쥳���ɿ�������
    $rows_view = $rec;
    ////// �ե������̾���ɲ�
    for ($i=0; $i<$num; $i++) {
        if ($i == 0) {
            $field_view[0] = '��٥�';
            $field_view[$i+1] = $field[0];
        } else {
            $field_view[$i+1] = $field[$i];
        }
    }
    ////// �ե�����ɿ�������
    $num_view = count($field_view);       // �ե�����ɿ�����
    
    /**************** TNKCC CC���� ɽ���ɲ� *********************/
    /////////// begin �ȥ�󥶥�����󳫻�
    if ($con = funcConnect()) {
        // query_affected_trans($con, 'begin');
    } else {
        $_SESSION['s_sysmsg'] = '�ǡ����١�������³�Ǥ��ޤ���';
    }
    ////// TNKCC���ʤμ����ȳ��إ�٥�(��٥룲�ʲ�)��CC���ʤ�ɽ��
    for ($r=0; $r<$rows_view; $r++) {
        $query_tnkcc = "SELECT
                            CASE
                                WHEN miccc='E' THEN 'TNKCC'
                                WHEN miccc='D' THEN 'CC����'
                                ELSE '&nbsp;'
                            END
                        FROM miccc WHERE mipn='{$res_view[$r][1]}'
        ";
        if (getUniResTrs($con, $query_tnkcc, $res_tnkcc) > 0) {
            // �ǡ�������
            $res_view[$r][$num_view] = $res_tnkcc;
        } else {
            // �ǡ����ʤ�
            $res_view[$r][$num_view] = '&nbsp;';
        }
    }
    $field_view[$num_view] = 'CC����';
    ////// �ե�����ɿ�������
    $num_view = count($field_view);       // �ե�����ɿ�����
    ////// CC���ʤμ���
    $query_cc = "
        SELECT  '.1'        as ��٥�                   -- 0
                ,parts_no   as �����ֹ�                 -- 1
                ,trim(substr(midsc,1,21))
                            as ����̾                   -- 2
                ,unit_qt    as ���ѿ�                   -- 3
                ,'-'        as ������                   -- 4
                ,'-'        as �и��߷�                 -- 5
                ,'-'        as �и˻�                   -- 6
                ,CASE
                    WHEN mtl_cond = '1' THEN '����'     -- ���ꤨ�ʤ�����
                    WHEN mtl_cond = '2' THEN 'ͭ��'
                    WHEN mtl_cond = '3' THEN '̵��'
                    ELSE mtl_cond
                END         as ���                     -- 7 ��ϻٵ���
                ,'-'        as ͭ��ñ��                 -- 8
                ,'-'        as ͭ�����                 -- 9
                ,'CC����'   as CC����                   -- 10
        FROM
            parts_configuration
        LEFT OUTER JOIN
            miccc
        ON parts_no=miccc.mipn
        LEFT OUTER JOIN
             miitem
        ON parts_no=miitem.mipn
        WHERE
            p_parts_no='{$assy_no}'
            and
            miccc.miccc='D'
        ORDER BY parts_no ASC
    ";
/*
    if ( ($rows_cc=getResultTrs($con, $query_cc, $res_cc)) > 0) {
        // CC���ʤ���
        $num_cc = count($res_cc[0]);
        for ($r=0; $r<$rows_cc; $r++) {
            for ($i=0; $i<$num_cc; $i++) {
                $res_view[$rows_view+$r][$i] = $res_cc[$r][$i];
            }
        }
        // �쥳���ɿ��Υ��å�
        $rows_view = ($rows_view + $rows_cc);
    }
/**/

    // ��Ͽ���̤�Ʊ���ˤʤ�褦CC���ʤ����֤���
    if( ($rows_cc=getResultTrs($con, $query_cc, $res_cc)) > 0 ) {
        $num_cc = count($res_cc[0]);
        $sort_view = array();
        $r=$c=0;
        for ($s=0; $s<($rows_view + $rows_cc); $s++) {
            if( $c >= $rows_cc || $r < $rows_view && ($res_view[$r][0] != '.1' || strcmp($res_view[$r][1], $res_cc[$c][1]) < 0)) {
                for ($i=0; $i<$num_cc; $i++) {
                        $sort_view[$s][$i] = $res_view[$r][$i];
                }
                $r++;
            } else {
                /* �����б���ñ���ְ㤤�������̤ˣ������λ��ֹ���ѹ� -----> */
                if( strcmp($res_cc[$c][1], "CP00873-2") == 0 ) {
//                    $_SESSION['s_sysmsg'] .= "��CP00873-2 ñ���ְ㤤�β�ǽ�����ꡢCP00873-1 ���ѹ����Ƥޤ���";
                    $res_cc[$c][1] = "CP00873-1";
                }
                /* <-------------------------------------------------------- */
                for ($i=0; $i<$num_cc; $i++) {
                        $sort_view[$s][$i] = $res_cc[$c][$i];
                }
                $c++;
            }
        }
        // �쥳���ɿ��Υ��å�
        $rows_view = ($rows_view + $rows_cc);
        $res_view = array();
        $res_view = $sort_view;
    }

    $num_view = $num_view + 2; // �������ȡ��и˿�ʬ���ɲ�

    // ��¸��ɽ�������󤫤���Ͽ��Ʊ�ͤ�ɽ��������Ѵ����롣
    $res_view2 = array(); /* [0]��٥�[1]�����ֹ�[2]����̾[3]���ѿ�[4]������[5]�и˿�[6]����
                             [7]����̾[8]����ñ��[9]�������[10]�⳰��[11]���ֹ�[12]CC���� */

    $d_data = array(); //  [0]����[1]����̾[2]����ñ��[3]�⳰��[4]���ֹ�
    $sei_no = $parts = array();
    $out_no = -1;
    $parts_idx = array();
    $level_one = -1;
    $reserveflg = -1;

    SetCompleteInfo( $plan_no, $assy_no );

    /* �ü졧��������ɽ������ʥե饰��------------------------------------> */
    $rabelflg = false;   // ��٥��ѥե饰
    $konpoflg = false;   // ����ݥ��Х��ѥե饰
    $poriflg = false;    // �ݥ�֥��� 80X160 �ѥե饰

    /* �ü졧��������ɽ������ʰʲ��ϡ�ɽ���������ʤΥ����å���------------> */
    for( $c=0; $c<$rows_view; $c++ ) {
        if( !$rabelflg ) {
            if( strncmp($res_view[$c][1], 'CP25730-', 8) == 0 ) {
                $rabelflg = true; // ��٥� ɽ��
            }
        }
        if( !$konpoflg ) {
            if( strncmp($res_view[$c][1], 'CP08807-', 8) == 0 ) {
                $konpoflg = true; // ����ݥ��Х� ���楦 2��-�� ɽ��
            }
        }
        if( !$poriflg ) {
            if( strncmp($res_view[$c][1], 'TP08441-', 8) == 0 ) {
                $poriflg = true; // �ݥ�֥��� 80X160 ɽ��
            }
        }
    }
    /* <-------------------------------------------------------------------- */

    // �Կ�ʬ�����֤�
    for( $r=0,$a=0; $r<$rows_view; $r++,$a++ ) {

        /* �ü졧��������ɽ�������ɽ���������ʤ����ä����ɽ�������ʤ���--> */
        if( $rabelflg ) {
            if( strncmp($res_view[$r][1], 'CQ30241-', 8) == 0 ) {
                $a--;
                continue; // ����ݥ��襦��٥� ���襦 ɽ�������ʤ�
            }
        }
        if( $konpoflg ) {
            if( strncmp($res_view[$r][1], 'CQ20823-', 8) == 0 ) {
                $a--;
                continue; // ����ݥ��Х� ���楦�ȥ���ݥ��Х� ���楦 1��-�� ɽ�������ʤ�
            }
        }
        if( $poriflg ) {
            if( strncmp($res_view[$r][1], 'CP20447-', 8) == 0 ) {
                $a--;
                continue; // �ݥꥨ�����֥��� 80X160 ɽ�������ʤ�
            }
        }
        /* <---------------------------------------------------------------- */

        IniDetailData( $d_data ); // ����ǡ������å�

        $d_drow = -1;

        $idx = strlen($res_view[$r][0]) - 2;

        $sei_no[$idx] = "";
        if( $idx == 0 ) { // ��٥�.1������
            $reserveflg = 0;
            if( $res_view[$r][10] == "CC����" ) {
                $d_drow = GetCCDetail( $res_view, $rows_view, $d_data, $r );
                $res_view[$r][4] = 0; // �ޤ�ˡ��ͤ����äƤ��뤬CC���ʤʤΤǣ��ˤ��Ƥ���
                $res_view[$r][5] = 0; // �ޤ�ˡ��ͤ����äƤ��뤬CC���ʤʤΤǣ��ˤ��Ƥ���
            } else {
                $parts_idx[$idx] = $a;
                $d_drow = GetLevelDetail( $res_view, $rows_view, $plan_no, $d_data, $sei_no[$idx], $r );
                $level_one = $d_drow;
                $count = $res_view[$r][3];       // ��٥�..2�ʲ��ο��̤ˤ�����١����̤򥻥å�
                $parts[$idx] = $res_view[$r][1]; // �����ʤ˿��ֹ�򥻥åȤ���١������ֹ�򥻥å�
                $reserveflg = $res_view[$r][4] - $res_view[$r][5]; // ������ - �и˿��򥻥å�
                if( $reserveflg <= 0 || $res_view[$r][5] > 0 ) {
                    $reserveflg = 0;
                } else {
//                  $_SESSION['s_sysmsg'] .= "{$res_view[$r][1]} : �иˤ���Ƥ��ޤ��󡪢����֤���������٤����������";
                    $reserveflg = -1;
                    if( $res_view[$r][6] > 0 && COMPLETE ) {
                        $_SESSION['s_sysmsg'] .= "{$res_view[$r][1]} : {$res_view[$r][6]} �Ĥνи˻Ĥ��ꡣ�����֤�����Ƽ¹Ԥ��Ʋ�������";
                        $reserveflg = 1;          // �и˻Ĥ���
                        $d_drow = 0;              // ���顼��ɽ������
                        IniDetailData( $d_data ); // ����ǡ������å�
                    }
                }
            }
        } else { // ��٥�.1�ʳ�������
            // ��٥�.1�Υǡ����������Ǥ��ơ������������ʤ����ϥ�٥�..2�ʲ���ɽ�����ʤ���
            if( $res_view2[$a-1][0] == ".1" && $res_view2[$a-1][6] == 1 && $res_view2[$a-1][7] != "--" ) {
                $a--;
                continue; // ���פʰ١����ιԤ�
            }

            // �����ʤȥ�٥뤬��2�ʾ�Υ��Ƥ�����ɽ�����ʤ�
            if( COMPLETE && (strlen($res_view2[$a-1][0])) < ($idx+1) ) {
                $a--;
                continue; // ���פʰ١����ιԤ�
            }

            $d_data[0][4] = $parts[$idx-1]; // �����ʤʤΤǡ����ֹ楻�å�

            if( $res_view[$r][10] == "CC����" ) {
                $d_drow = GetCCDetail( $res_view, $rows_view, $d_data, $r );
            } else {
                $parts_idx[$idx] = $a;
                $d_drow = GetLevelDetail( $res_view, $rows_view, $sei_no[$idx-1], $d_data, $sei_no[$idx], $r );

                /* �����б��� CQ16739-3 �� CQ19736-0 ----------------------> */
                if( $d_drow == 0 && strncmp($res_view[$r][1], 'CQ16739-', 8) == 0 ) {
                    $res_view[$r][1] = 'CQ19736-0';
                    $d_drow = GetLevelDetail( $res_view, $rows_view, $sei_no[$idx-1], $d_data, $sei_no[$idx], $r );
                }
                /* <-------------------------------------------------------- */

                // GetLevelDetail()�⡢�ǿ��λ��֤ǥǡ��������ԲĤξ�硢�Ť����֤⸡�����Ƥ���١�
                // ���Υǡ�����������ɽ��������˳�Ǽ����Ƥ����礬����Τǥ����å���Ԥ�
                if( $d_drow > 0 ) {
                    for( $i=$a-1; 0<=$i; $i-- ) {
                        if( 1 > strlen($res_view2[$i][0])-2 ) break; // ��٥�.1�ˤʤä���ȴ����

                        if( $res_view[$r][0] != $res_view2[$i][0] ) continue; // ��٥뤬�㤦�ʤ鼡�ιԤ�

                        if( $res_view[$r][1] == $res_view2[$i][1] ) {
                            $d_drow = -1; // Ʊ�����ʤ򸫤Ĥ����饨�顼�ˤ���
                            break;
                        }
                    }
                }

                $parts[$idx] = $res_view[$r][1]; // �����ʤ˿��ֹ�򥻥åȤ���١������ֹ�򥻥å�

                if( empty($sei_no[$idx]) == true && $level_one != 0 ) {
                    // �����ֹ����¤�ֹ椬�����Ǥ��ʤ����ġ���٥�.1�ξ���ϼ����Ǥ��Ƥ�Ȥ�
                    if( IsSerial($res_view[$r][1], GetPlanNo($res_view[$r][1], $sei_no[$idx-1])) == true ) {
                        $d_drow = 0;  // ���ꥢ���ֹ������ǽ�ʤ�ɽ��
                    } else {
                        if( COMPLETE ) {
                            $d_drow = -1; // �׾�ѷײ�ΰ١����Ѥ��Ƥʤ���Ƚ�Ǥ���ɽ��
                        } else {
//                          $_SESSION['s_sysmsg'] .= "*** {$res_view[$r][1]} : ̤�׾�ΰ١����Ѥ�̵ͭ�����ꤷ�Ƥ��ޤ��� ***";
                            $d_drow = 0;  // ̤�׾�ײ�ΰ١�ɽ��������
                        }
                    }
                }
            }

            if( $reserveflg != 0 ) {  // �и˿�������������ã���Ƥ��ʤ�
                $d_data[0][1] = "--"; // ���顼ɽ���ˤ����
                $d_drow = 1;          // ���顼ɽ���ѣ�����ʬ����ɽ��������
            }
        }

        if( $d_drow != -1 && $reserveflg == 0 ) {
            // Ʊ���٥�ˡ����ֹ椬ʣ������Ȥ�
            // ���Ѥ��Ƥ��ʤ���������CC���ʤ�����ɽ�������ʤ��٤ν���
            for( $i=$a-1; $i>=0; $i-- ) {
                if( $res_view[$r][0] != $res_view2[$i][0] ) {
                    break; // ��٥뤬�Ѥ�ä���ȴ����
                }
                if( strncmp($res_view[$r][1], $res_view2[$i][1], 8) != 0 ) {
                    continue; // ���ֹ�������㤦���ϡ����ιԤ�
                }
                if( $res_view2[$i][7] != "--" && $res_view2[$i][12] != "CC����" ) {
                    if( $d_data[0][1] == "--" ) {
                        $d_drow = -1; // ���Ǥ˰㤦���ֹ����Ͽ����Ƥ���١�ɽ�������ʤ�
                        break;
                    } else {
                        $res_view2[$i][3] = sprintf( "%0.04f", $res_view2[$i][3] );
                        $res_view[$r][3]  = sprintf( "%0.04f", $res_view[$r][3] );
                        if( $res_view2[$i][3] == $res_view[$r][3] && $res_view2[$i][3] > 1 && $res_view[$r][3] > 1 ) {
                            $a= $i;      // ���ѿ���Ʊ����硢ξ�����ʾ�ʤ�ǿ��λ��ֹ����Ͽ����
                            $d_drow = 1; // ����Ͽ�����
                            break;
                        }
                    }
                }
                if( $res_view2[$i][12] == "CC����" ) {
                    $a= $i;      // CC���ʤȤ�����Ͽ����Ƥ�����߸��ʤ��֤�������
                    $d_drow = 1; // ����Ͽ�����
                    break;
                }
                if( strcmp($res_view[$r][1], $res_view2[$i][1]) == 0 ) {
                    $d_drow = -1; // Ʊ�����ʤΰ١�ɽ�������ʤ�
                    break;
                }
            }
        }

        if( $d_drow < 0 || ($reserveflg == -1 && COMPLETE) ) { // �׾�Ѥߤǡ��и˿�����ɽ�����ʤ�
            $a--;
            continue; // ���פʰ١����ιԤ�
        } else if( $d_drow < 1 ) {
            $d_drow = 1; // �ǡ��������˼��Ի������顼ɽ�����뤿��
        }

        if( $idx > 0 ) {
            if( $idx == 1 ) { // ��٥�.2
                $count2 = $res_view[$r][3];
                $count3 = $count4 = $count5 = 1;
            } else if( $idx == 2 ) { // ��٥�.3
                $count3 = $res_view[$r][3];
                $count4 = $count5 = 1;
            } else if( $idx == 3 ) { // ��٥�.4
                $count4 = $res_view[$r][3];
                $count5 = 1;
            } else if( $idx == 4 ) { // ��٥�.5
                $count5 = $res_view[$r][3];
            }
//            $res_view[$r][3] *= $count; // ��٥�.1�ʳ��ʤ顢��٥�.1�λ��ѿ��򤫤���
            $res_view[$r][3] = $count * $count2 * $count3 * $count4 * $count5 ; // ��٥�.1�ʳ��ʤ顢���ѿ��򤫤���
            if( $d_data[0][1] != "--" ) {
                if( $res_view[$r][7] != "ͭ��" ) {
                    $res_view2[$parts_idx[$idx-1]][8] = 0; // ����ñ���򥼥�ˤ���
                    $res_view2[$parts_idx[$idx-1]][9] = 0; // ������ۤ򥼥�ˤ���
                }
                $res_view2[$parts_idx[$idx-1]][12] = $res_view[$r][7]; // ͭ��or̵���򥻥å�
            }
        } else {
            $count2 = $count3 = $count4 = $count5 = 1; // ��٥�.1 �λ� ���ѿ������
        }

        if( !empty($d_data[0][5]) ) {
            $res_view[$r][10] = $d_data[0][5];
            if( $res_view[$r][7] != "ͭ��" ) {
                $d_data[0][1] = "--";
                $d_data[0][2] = 0;
                if( $idx > 0 ) {
                    $res_view2[$parts_idx[$idx-1]][8] = 0; // ����ñ���򥼥�ˤ���
                    $res_view2[$parts_idx[$idx-1]][9] = 0; // ������ۤ򥼥�ˤ���
                    $res_view2[$parts_idx[$idx-1]][12] = $res_view[$r][7]; // ͭ��or̵���򥻥å�
                }
            }
        }

        // ɽ���ѥ쥳���ɤؼ�������ɽ���ǡ�������ñ�̤ǥ��å�
        for( $i=0; $i<$d_drow; $i++ ) {
            if( $i > 0 ) {
                $a++;    // ������ʣ������Ȥ����̤�
            }
            for( $c=0; $c<$num_view; $c++ ) {
                switch ($c) {
                    case 0:    // ��٥�
                    case 1:    // �����ֹ�
                    case 2:    // ����̾
                    case 3:    // ���ѿ�
                    case 4:    // ������
                    case 5:    // �и˿�
                        $res_view2[$a][$c] = $res_view[$r][$c];
                        break;
                    case 6:    // ����
                        $res_view2[$a][$c] = $d_data[$i][0];
                        break;
                    case 7:    // ����̾
                        $res_view2[$a][$c] = $d_data[$i][1];
                        break;
                    case 8:    // ����ñ��
                        $res_view2[$a][$c] = $d_data[$i][2];
                        break;
                    case 9:    // �������
                        $res_view2[$a][$c] = Uround($res_view[$r][3] * $d_data[$i][2], 2);
                        break;
                    case 10:    // �⳰��
                        $res_view2[$a][$c] = $d_data[$i][3];
                        break;
                    case 11:    // ���ֹ�
                        $res_view2[$a][$c] = $d_data[0][4];
                        break;
                    default:   // 12 CC����
                        $res_view2[$a][$c] = $res_view[$r][10];
                }
            }
            if( !empty($d_data[0][6]) ) {
                $res_view2[$a][$c] = $d_data[0][6]; // ɽ���ǡ����ǤϤʤ�����13�˿��إե饰
            }
        }
    }
    $rows_view = $a;

    /////////// commit �ȥ�󥶥������λ
    // query_affected_trans($con, 'commit');
    // pg_close($con); ��ɬ�פʤ�
}

// ��Ω��������򥻥å�
function SetCompleteInfo( $plan_no, $parts_no )
{
    $query = "
                SELECT   comp_date
                FROM     assembly_completion_history
                WHERE    plan_no = '$plan_no' AND assy_no = '$parts_no'
             ";
    if( getResultWithField2($query, $field, $res) <= 0 ) {
        if( COMPREGI ) {
            define('COMPLETE', true);
        } else {
            define('COMPLETE', false);
        }
        define('COMPDATE', date('Ymd'));
    } else {
        define('COMPLETE', true);
        define('COMPDATE', $res[0][0]);
    }
}

// �ǥե�����ͥ��å�
function IniDetailData( &$d_data )
{
    $d_data = array();
    $d_data[0][0] = '1';         // ����
    $d_data[0][1] = "--";        // ��������
    $d_data[0][2] = 0;           // ñ��
    $d_data[0][3] = "����";      // �⳰��
    $d_data[0][4] = "---------"; // ���ֹ�
}

// ���ʺ߸˷���Ȳ�˥ǡ��������뤫�����å�
function IsSerial( $parts_no, $plan_no )
{
    $query = $field = $res = array();

    // ���ʺ߸˷���Ȳ������ֹ�ȷײ��ֹ��ꥷ�ꥢ���ֹ����
    $query = "
                SELECT   *
                FROM     parts_stock_history
                WHERE    parts_no = '$parts_no' AND plan_no = '$plan_no'
                ORDER BY stock_mv DESC, upd_date ASC LIMIT 3
             ";
    if( getResultWithField2($query, $field, $res) <= 0 ) {
//        $_SESSION['s_sysmsg'] .= "$parts_no : $plan_no : ���ꥢ���ֹ�������ԡ�";
        return false;
    }

    return true;
}

function GetPlanNo( $level, $sei_no )
{
    $plan_no = $sei_no;

    // ��٥�.1�ʳ��ϡ�����Ƭ���դ�����¤�ֹ��ײ�̾�ˤ���
    if( $level != ".1" ) {
        $plan_no = "@";
        $plan_no .= $sei_no;
    }

    return $plan_no;
}

// ��ĸ�λ��ֹ�����
function GetNewNo( $parts_no )
{
    $old_no = hexdec( substr($parts_no, -1, 1) );

    if( $old_no >= 15 ) {
        return $parts_no;
    }

    $new_no = dechex( $old_no + 1 );
    if( $old_no >= 9 ) {
        $new_no = strtoupper( $new_no );
    }

    return str_replace(('-'.+$old_no), ('-'.+$new_no), $parts_no);
}

// ������λ��ֹ�����
function GetOldNo( $parts_no )
{
    $new_no = hexdec( substr($parts_no, -1, 1) );

    if( $new_no <= 0 ) {
        return $parts_no;
    }

    $old_no = dechex( $new_no - 1 );
    if( $new_no >= 11 ) {
        $old_no = strtoupper( $old_no );
    }

    return str_replace(('-'.+$new_no), ('-'.+$old_no), $parts_no);
}

// �ٵ��ʤ������å� (������ˤƻٵ��ʤ����åȤ��Ƥ���)
function IsProvide( $parts_no, $reg_ymd )
{
    $query = $field = $res = array();

    $kei_ym = substr(COMPDATE, 0, 6);
    $now_ym = date('Ym');
    $reg_ym = substr($reg_ymd, 0, 6);

    // �׾��ȾȲ���Ͽ�Ʊ����硢�ޤ��ٵ��ʤ���Ͽ������Ƥʤ��Τ�����ξ��֤�ߤ�
    if( $kei_ym == $now_ym && $kei_ym == $reg_ym ) {
        $year = substr($reg_ymd, 0, 4);
        $month = substr($reg_ymd, 4, 2);
        if( $month == '01' ) {
            $year -= 1;
            $month = '12';
        } else {
            $month -= 1;
        }
        $reg_ym = sprintf( "%04s%02s", $year, $month );
    }

    $query = "
                SELECT   *
                FROM     provide_item
                WHERE    parts_no = '$parts_no' AND reg_ym = $reg_ym
                ORDER BY reg_ym DESC LIMIT 1
             ";
    if( getResultWithField2($query, $field, $res) <= 0 ) {
//        $_SESSION['s_sysmsg'] .= "$parts_no : �ٵ��ʤǤϤ���ޤ���<br>";
          return false;
    }

    return true;
}

// CC���ʤΥǡ�������
function GetCCDetail( &$res_view, $rows_view, &$d_data, &$r )
{
    $query = $field = $res = array();
    $cc_count = 0;

    // ���ֹ椬ʣ������Ȥ����ǿ��ιԤ�����ʾ�������äƤ���Ϥ���
    for( ; $r < $rows_view; $r++ ) {
        $cc_count++;
        if( $r+1 == $rows_view || $res_view[$r+1][10] != "CC����" || strncmp($res_view[$r][1],$res_view[$r+1][1], 7) ) {
            break;  // �Ǹ�ιԡ��ޤ��ϼ��ιԤ�CC���ʰʳ��������ʤ��Ѥ�ä���ȴ����
        }
    }
    $parts_no = $res_view[$r][1]; // ������������̾�򥻥å�

    $compdate = COMPDATE;

    // �ǿ��λ��ֹ椫��ñ����������롢����ʤ����λ��ֹ�Ǹ���
    for( ; ; ) {
        // �ٵ��ʤ��Υ����å��򤹤�
        if( IsProvide($parts_no, $compdate) ) {
            $d_data[0][5] = "�ٵ���";
        } else {
            $d_data[0][5] = "";
        }

        $query = "
                    SELECT   *
                    FROM     act_payable
                    WHERE    parts_no = '$parts_no' AND act_date <= '$compdate' AND koutei != ''
                    ORDER BY ken_date DESC LIMIT 1
                 ";
        $res   = array();
        if( getResultWithField2($query, $field, $res) > 0 ) {
            // ��ݼ��Ӥ��ꡢ��ݼ��Ӥ�ñ���򥻥å�
            $d_data[0][1] = $res[0][10]; // ��������
            $d_data[0][2] = $res[0][12]; // ȯ��ñ��
        } else {
            $query = "
                        SELECT   *
                        FROM     parts_cost_history
                        WHERE    parts_no = '$parts_no' AND vendor != 88888 AND as_regdate <= '$compdate'
                        ORDER BY reg_no DESC, pro_no ASC LIMIT 1
                     ";
            $res   = array();
            if( getResultWithField2($query, $field, $res) > 0 ) {
                // ��ݼ��Ӥʤ���ñ�������ñ���򥻥å�
                $d_data[0][1] = $res[0][2];  // ��������
                $d_data[0][2] = $res[0][11]; // ñ��
            }
        }

        if( $d_data[0][1] == "--" ) {
            $old_no = GetOldNo( $parts_no );
            $cc_count--;
            if( $parts_no == $old_no || $d_data[0][5] == "�ٵ���" || $cc_count == 0 ) {
                return 0;
            }
            $parts_no = $old_no;
            continue;
        }

        $res_view[$r][1] = $parts_no;
        break;
    }

    return 1;
}

function GetLevelDetail( &$res_view, $rows_view, $parent_sei_no, &$d_data, &$child_sei_no, &$r )
{
    $plan_no = array();
    $d_drow  = $cnt = 0;
    $plan_no = $parent_sei_no;

    // ��٥�.1�ʳ��ϡ�����Ƭ���դ�����¤�ֹ��ײ�̾�ˤ���
    if( $res_view[$r][0] != ".1" ) {
        $plan_no = "@";
        $plan_no .= $parent_sei_no;
    }

    // ���ֹ椬ʣ������Ȥ������Ѥ����ԤΤߤ�ɽ������٤ν���
    for( $i=$r; $i<$rows_view; $i++ ) {
        if( strncmp($res_view[$r][0], $res_view[$i][0], strlen($res_view[$r][0])) !=0 ) {
            break; // ��٥뤬�Ѥ�ä���ȴ����
        }
        if( strncmp($res_view[$r][1], $res_view[$i][1], 8) !=0 ) {
            continue; // ����̾�����פ��ʤ���м��˹Ԥ�
        }
        if( $res_view[$i][10] == "CC����" ) {
            continue; // ���פ�������̾��CC���ʤʤ鼡�˹Ԥ�
        }
        $d_drow = GetDetailData( $res_view[$i][1], $plan_no, $d_data, $child_sei_no );

        $cnt++; // �Ť����ָ����������롼�Ƥ����޿��򥫥����
        $r = $i;

        if( $d_drow > 0 ) {
//            $r = $i;
            break;
        }
    }

    // �Ť����ֹ����Ѥ��Ƥʤ����������Ƥߤ�
    if( $d_drow == 0 ) {
        $parts_no = $res_view[$r][1];
        $org_no   = $res_view[$r][1];
        for( $i=$cnt; $i>1; $i-- ) { // ���˸����������֤ϥ��롼���롣
            $parts_no = GetOldNo( $parts_no );
        }
        for( ; ; ) {
            $old_no = GetOldNo( $parts_no );
            if( $parts_no == $old_no || $res_view[$r][0] == ".1" || strcmp($res_view[$r-1][1], $old_no) == 0 ) {
                $res_view[$r][1] = $org_no;
                break;
            }
            $res_view[$r][1] = $old_no;
            $d_drow = GetDetailData( $res_view[$r][1], $plan_no, $d_data, $child_sei_no );
            if( $d_drow == 0 ) {
                $parts_no = $old_no;
                continue;
            }
            break;
        }
    }

    if( $res_view[$r][7] == 'ͭ��' ) {
        $d_data[0][2] = 0;
    }

    return $d_drow;
}

// �׾��������׻�
function AccrualDateCalc( $res2, $rows2, $stock, $out_stock, $flag )
{
    $max = -1;
    if( $flag == "after" ) {
        $stock -= $out_stock;
        for( $r=0; $r<$rows2; $r++ ) {
            if( ctype_space($res2[$r][3])==true || $res2[$r][3]!=2  || $res2[$r][2] < 0 ) {
                continue; // �ޥ��ʥ��Ԥ����Ф�
            }
            $stock += $res2[$r][2]; // �߸ˤ˼��ΰ�ư����­��
            if( $max == -1 ) {
                $max = $r;
            }
            if( $res2[$max][2] < $res2[$r][2] ) {
                $max = $r;
            }
            if( $stock >= 0 ) {
                $r = $max;
                break;
            }
        }
    } else if( $flag == "before" ) {
        if( $stock - $out_stock < 0 ) {
            $out_stock -= $stock;
        }
        $max = -1;
        for( $r=0; $r<$rows2; $r++ ) {
            if( ctype_space($res2[$r][3])==true || $res2[$r][3]!=2 || $res2[$r][2] < 0 ) {
                continue; // �ޥ��ʥ��Ԥ����Ф�
            }
            $stock -= $res2[$r][2]; // �߸ˤ���ư�������
            if( $out_stock < $stock ) {
                continue; // �и��̤��ޤ��߸��̤�¿����缡�ΰ�ư����
            }
            if( $max == -1 ) {
                $max = $r; // ���Τ�
                $res2[$max][2] = $out_stock - $stock;
            }
            if( $res2[$max][2] <= $res2[$r][2] ) {
                $max = $r;
            }
            if( $stock <= 0 || ($stock < ($out_stock/2) && $stock < $res2[$max][2]) ) {
                if( ($stock+$res2[$r][2]) >= $res2[$max][2] ) {
                    if( $res2[$r][2] >= $res2[$max][2] ) {
                        $max = $r;
                    }
                }
                $r = $max;
                break;
            }
        }
    } else { // other
        for( $r=0; $r<$rows2; $r++ ) {
            if( $res2[$r][3]!=2 || $res2[$r][2] < 0 ) {
                continue; // �ޥ��ʥ��Ԥ����Ф�
            }
            if( ($res2[$r][2]+$res2[$r][9]) >= $out_stock ) {
                if( $res2[$r][2] > ($out_stock/2) ) {
                    break;
                } else {
                    $out_stock -= $res2[$r][2];
                }
            }
        }
    }
    return $r;
}

// ���ʤ������椫�����å�
function IsInspection( $parts_no )
{
    $query = $field = $res = array();

    $query = "
            SELECT
                ''                                     AS ���ֹ� -- 0
                ,
                to_char(data.uke_date, 'FM0000/00/00') AS �׾��� -- 1 (������)
                ,
                '������'                               AS Ŧ��   -- 2
            FROM
                order_plan      AS plan
                LEFT OUTER JOIN
                order_data      AS data
                    USING(sei_no)
                LEFT OUTER JOIN
                order_process   AS proc
                    USING(sei_no, order_no)
            WHERE
                plan.parts_no = '$parts_no'
                AND plan.zan_q > 0
                AND data.uke_q > 0
                AND data.ken_date = 0
                AND proc.next_pro = 'END..'
             ";
    if( getResultWithField2($query, $field, $res) <= 0 ) {
//        $_SESSION['s_sysmsg'] .= "$parts_no : ������ǤϤ���ޤ���";
          return false;
    }

    return true;
}

// ���ʺ߸˷���Ȳ��оݤη׾����쥳���ɤ����
function GetPartsStokHistory( $parts_no, $plan_no, $den_no, &$res, &$r, &$fstock_mv )
{
    $query = $field = $res = array();
    $rows = 0;

    // ���ʺ߸˷���Ȳ������ֹ�ȷײ��ֹ��ꥷ�ꥢ���ֹ����
    if( !$den_no ) {
        $query = "
                    SELECT   *
                    FROM     parts_stock_history
                    WHERE    parts_no = '$parts_no' AND plan_no = '$plan_no'
                    ORDER BY stock_mv DESC, upd_date ASC, serial_no ASC LIMIT 5
                 ";
        if( ($rows = getResultWithField2($query, $field, $res)) <= 0 ) {
//        $_SESSION['s_sysmsg'] .= "$parts_no : $plan_no : ���ꥢ���ֹ�������ԡ�";
            return 0;
        }
        $fstock_mv = $res[0][2]; // �߸˰�ư��
    } else {
        $query = "
                    SELECT   *
                    FROM     parts_stock_history
                    WHERE    parts_no = '$parts_no' AND den_no = '$den_no'
                    ORDER BY stock_mv DESC, upd_date ASC LIMIT 5
                 ";
        if( ($rows = getResultWithField2($query, $field, $res)) <= 0 ) {
//        $_SESSION['s_sysmsg'] .= "$parts_no : $den_no : ���ꥢ���ֹ�������ԡ�";
            return 0;
        }
    }

    $serial = $res[0][13];   // ��ưϢ��
    $stock = $res[0][9];     // TNK�κ߸˿�
    $out_stock = $fstock_mv; // �߸˰�ư��

    // ���ʺ߸˷���Ȳ񡧺���ȺǾ���­�����Ȥ��ˣ��ˤʤä���硢���ιԤ򥻥å�
    if( $rows > 2 && (($res[0][2] + $res[$rows-1][2]) == 0) ) {
        $serial = $res[1][13];   // ��ưϢ��
        $stock = $res[1][9];     // TNK�κ߸˿�
        if( $fstock_mv == 0 ) {
            $fstock_mv = $res[1][2]; // �߸˰�ư��
        }
        $out_stock = $fstock_mv; // �߸˰�ư��
    }

    // ���ʺ߸˷���Ȳ񡧽и˺߸ˤ��������ˤ�����Ƚ�Ǥ��׾�������
    if( $stock <= 0 || $stock < ($out_stock/2) ) {
        // �߸ˤ����ʲ�����ư�����߸ˤ�Ⱦʬ���¿��
        $query = "
                    SELECT   *
                    FROM     parts_stock_history
                    WHERE    parts_no = '$parts_no' AND serial_no >= $serial
                    ORDER BY serial_no ASC LIMIT 20
                 ";
        $res   = array();
        $rows2 = getResultWithField2($query, $field, $res);

        for( $i=1; $i<$rows2; $i++ ) {
            if( ctype_space($res[$i][3]) && !ctype_space($res[$i][4]) ) {
                if( abs($res[$i][2]) >= 0 ) {
                    $c = (int)($res[$i][9] - $res[$i][2]);
                } else {
                    $c = (int)($res[$i][9] + $res[$i][2]);
                }
            } else {
                $c = (int)($res[$i][2] + $res[$i][9]);
            }
            if( $c < 0 ) {
                continue; // ���ιԤ�
            }
            if( !ctype_space($res[$i][3]) || $res[$i][6] == "" ) {
                // �̾������
                $r = AccrualDateCalc( $res, $rows2, $stock, $out_stock, "after" );
                if( $r == $rows2 ) {
                    if( COMPLETE ) {
                        $_SESSION['s_sysmsg'] .= "$parts_no : ��ݥǡ���������Ͽ����Ƥ��ʤ���ǽ��������ޤ���";
                    }
                    return 0;
                }
                break;
            }
            if( $res[$i][2] < 0 ) {
                // �����ˤ�߸��ʤΰ�ư
                $den_no = $res[$i][6]; // �����˻�����ɼ�ֹ�򥭡��˸�������٥��å�
                $query = "
                            SELECT   *
                            FROM     parts_stock_history
                            WHERE    parts_no = '$parts_no' AND den_no = '$den_no'
                            ORDER BY serial_no ASC LIMIT 1
                         ";
                $res   = array();
                if( getResultWithField2($query, $field, $res) <= 0 ) {
                    $_SESSION['s_sysmsg'] .= "$parts_no : $plan_no ��ݥǡ��������Բġ��ʺ����ˤ�߸��ʡ�";
                    return 0;
                }

                $serial = $res[0][13]; // �����ˤνи˻��μ�ưϢ�֤򥭡��˸�������٥��å�

                $query = "
                           SELECT   *
                           FROM     parts_stock_history
                           WHERE    parts_no = '$parts_no' AND serial_no <= $serial
                           ORDER BY serial_no DESC LIMIT 100
                         ";
                $res   = array();
                if( ($rows = getResultWithField2($query, $field, $res)) <= 0 ) {
                    $_SESSION['s_sysmsg'] .= "$parts_no : $plan_no ��ݥǡ��������Բġ��ʺ����ˤ�߸���:100��";
                    return 0;
                }

                $r = AccrualDateCalc( $res, $rows, $stock, $out_stock, "before" );
                if( $r == $rows ) {
                    $_SESSION['s_sysmsg'] .= "$parts_no : $plan_no ��ݥǡ��������Բġ��ʺ����ˡ��߸��ʤη׻���";
                    return 0;
                }
            }
            break;
        }
        if( $i == $rows2 ) {
            if( COMPLETE ) {
                if( IsInspection($parts_no) ) {
                    $_SESSION['s_sysmsg'] .= "$parts_no : �������޸�����Ǥ��������֤���������٤����������";
                } else {
                    /* 2021.08.24 Add. ------------------------------------> */
                    if( IsProvide($parts_no, date('Ym-d')) > 0 ) {
                        // ���ʺ߸˷���Ȳ�ǥ��顼�ˤʤ뤬���ٵ��ʤΰ١�����Ū���ͤ򥻥å�
                        $res[0][5]  = '6';          // ��ɼ��ʬ
                        $res[0][11] = date('Ym-d'); // �ǡ�������
                        return 1;
                    }
                    /* <---------------------------------------------------- */
                    $_SESSION['s_sysmsg'] .= "$parts_no : ���˥ǡ��������Ǥ��������֤���������٤����������";
                }
            }
            return 0;
        }
    } else {
        // �߸ˤ����ʾ塢��ư�����߸ˤ�¿��
        $query = "
                    SELECT   *
                    FROM     parts_stock_history
                    WHERE    parts_no = '$parts_no' AND serial_no <= $serial AND upd_date <= '{$res[0][12]}'
                    ORDER BY serial_no DESC LIMIT 200
                 ";
        $res   = array();
        if( ($rows = getResultWithField2($query, $field, $res)) <= 0 ) {
            $_SESSION['s_sysmsg'] .= "$parts_no : ��ݥǡ������Ĥ���ޤ���(200)";
            return 0;
        }

        $r = AccrualDateCalc( $res, $rows, $stock, $out_stock, "before" );

        // ���ꥢ��ι߽�ǥҥåȤ��ʤ���硢�������ι߽�Ǥ�õ���Ƥߤ롣
        if( $r == $rows ) {
            $query = "
                        SELECT   *
                        FROM     parts_stock_history
                        WHERE    parts_no = '$parts_no' AND upd_date <= '{$res[0][12]}'
                        ORDER BY upd_date DESC LIMIT 200
                     ";
            $res   = array();
            if( ($rows = getResultWithField2($query, $field, $res)) <= 0 ) {
                $_SESSION['s_sysmsg'] .= "$parts_no : ��ݥǡ������Ĥ���ޤ���(200:upd)";
                return 0;
            }
    
            $r = AccrualDateCalc( $res, $rows, $stock, $out_stock, "before" );
        }

        // �����ǡ����ǡ��׾�����ʬ����ʤ��ä�����ߥåȤ����䤷���ٸ���
        if( $r == $rows ) {
            $query = "
                        SELECT   *
                        FROM     parts_stock_history
                        WHERE    parts_no = '$parts_no' AND serial_no <= $serial
                             AND in_id != '' AND out_id = ''
                        ORDER BY serial_no DESC LIMIT 100
                     ";
            $res   = array();
            if( ($rows = getResultWithField2($query, $field, $res)) <= 0 ) {
                $_SESSION['s_sysmsg'] .= "$parts_no : ��ݥǡ������Ĥ���ޤ���(100)\t\t2000/04/01 �����ξ��ϡ�ACS����Ѥ���ǧ���Ʋ�������";
                return 0;
            }
            $r = AccrualDateCalc( $res, $rows, $stock, $out_stock, "before" );
            if( $r == $rows ) {
                $_SESSION['s_sysmsg'] .= "$parts_no : ���פ�����ݥǡ���������ޤ���(before)";
                return 0;
            }
        }
    }

    // �߸�Ĵ����ñ�������Ǥ��ʤ��Τǡ�1����������ñ�����ѹ����롣
    if( $res[$r][5] == '9' ) {
        $serial = $res[$r][13];
        $query = "
                    SELECT   *
                    FROM     parts_stock_history
                    WHERE    parts_no = '$parts_no' AND serial_no < $serial
                    ORDER BY serial_no DESC LIMIT 100
                 ";
        $res2   = array();
        if( ($rows2 = getResultWithField2($query, $field, $res2)) <= 0 ) {
            $_SESSION['s_sysmsg'] .= "$parts_no : ��ݥǡ������Ĥ���ޤ���(�߸�Ĵ��)";
            return 0;
        }

        for( $i=0; $i< $rows2; $i++ ){
            if( $res2[$i][3] == 2 ) {
                $r = $r + $i + 1;
                break;
            }
        }
    }

    return $rows;
}

// ACS���̡�Top �� 02 �� [F10] �ǡ�ɽ������������ʤ���
function IsAlternative( &$parts_no, &$tomodori )
{
    // ������� LQ06578-0 LQ06578-1 (11/11/21��)��LQ06588-0 LQ06588-1 (11/11/21��)
    if( strncmp($parts_no, 'LQ06578-', 8) == 0 || strncmp($parts_no, 'LQ06588-', 8) == 0 ) {
        $parts_no = 'LB08177-0';
        $tomodori = true;

    // �������ʤ��ǧ�Ǥ�������
    } else if( strcmp($parts_no, 'CQ15722-2') == 0 ) { // CQ15722-2 (16/08/02��)
        $parts_no = 'CQ41839-1';
    } else if( strcmp($parts_no, 'CQ39443-1') == 0 ) { // CQ39443-1 (20/07/28��)
        $parts_no = 'CQ44424-1';
    } else if( strcmp($parts_no, 'CQ39563-1') == 0 ) { // CQ39563-1 (17/10/02��)
        $parts_no = 'CQ35214-1';
    } else if( strcmp($parts_no, 'CQ39888-0') == 0 ) { // CQ39888-0 (10/06/21��)
        $parts_no = 'CP00950-0';
    } else if( strcmp($parts_no, 'CQ40154-0') == 0 ) { // CQ40154-0 (10/11/09��)
        $parts_no = 'CP00999-0';
    } else if( strcmp($parts_no, 'CQ43263-0') == 0 ) { // CQ43263-0 (14/12/23��)
        $parts_no = 'CQ41598-0';
    } else if( strcmp($parts_no, 'CQ43264-0') == 0 ) { // CQ43264-0 (14/12/25��)
        $parts_no = 'CQ35226-1';
    } else if( strcmp($parts_no, 'CQ43820-0') == 0 ) { // CQ43820-0 (20/05/27��)
        $parts_no = 'CP04899-3';
    } else if( strcmp($parts_no, 'CQ45636-0') == 0 ) { // CQ45636-0 (20/04/22��)
        $parts_no = 'CQ35225-1';
    } else if( strcmp($parts_no, 'CQ45711-1') == 0 ) { // CQ45711-1 (21/01/15��)
        $parts_no = 'CQ35247-3';
    } else if( strcmp($parts_no, 'CQ45713-1') == 0 ) { // CQ45713-1 (21/01/15��)
        $parts_no = 'CQ35257-3';
    } else if( strcmp($parts_no, 'CQ46057-0') == 0 ) { // CQ46057-0 (20/04/07��)
        $parts_no = 'CP00999-0';
    } else if( strcmp($parts_no, 'CQ46058-1') == 0 ) { // CQ46058-1 (21/01/13��)
        $parts_no = 'CP01048-0';
    } else if( strcmp($parts_no, 'CQ46218-0') == 0 ) { // CQ46218-0 (15/11/09��)
        $parts_no = 'CQ35200-2';
    } else if( strcmp($parts_no, 'CQ46218-1') == 0 ) { // CQ46218-1 (20/04/10��)
        $parts_no = 'CQ35200-3';
    } else if( strcmp($parts_no, 'CQ46219-0') == 0 ) { // CQ46219-0 (15/11/09��)
        $parts_no = 'CQ35203-1';
    } else if( strcmp($parts_no, 'CQ46220-0') == 0 ) { // CQ46220-0 (15/11/10)
        $parts_no = 'CQ35208-2';
    } else if( strcmp($parts_no, 'CQ46220-1') == 0 ) { // CQ46220-1 (20/04/06��)
        $parts_no = 'CQ35208-3';
    } else if( strcmp($parts_no, 'CQ46977-0') == 0 ) { // CQ46977-0 (16/11/24��)
        $parts_no = 'CP00955-1';
    } else if( strcmp($parts_no, 'CQ48288-0') == 0 ) { // CQ48288-0 (21/03/10��)
        $parts_no = 'CQ35203-1';
    } else if( strcmp($parts_no, 'CQ48697-0') == 0 ) { // CQ48697-0 (19/11/11��)
        $parts_no = 'CQ35222-2';
    } else if( strcmp($parts_no, 'CQ48698-0') == 0 ) { // CQ48698-0 (19/11/11��)
        $parts_no = 'CQ35226-1';
    } else if( strcmp($parts_no, 'CQ48713-0') == 0 ) { // CQ48713-0 (19/11/20��19/11/20)
        $parts_no = 'CQ35211-4';
    } else if( strcmp($parts_no, 'CQ48714-0') == 0 ) { // CQ48714-0 (19/11/20��)
        $parts_no = 'CQ35214-1';
    } else if( strcmp($parts_no, 'CQ48715-0') == 0 ) { // CQ48715-0 (19/11/11��20/01/08)
        $parts_no = 'CQ35219-3';
    } else if( strcmp($parts_no, 'CQ48812-0') == 0 ) { // CQ48812-0 (20/09/15��)
        $parts_no = 'CQ03291-1';
    } else if( strcmp($parts_no, 'CQ48969-0') == 0 ) { // CQ48969-0 (20/05/18��)
        $parts_no = 'CQ42507-0';
    } else if( strcmp($parts_no, 'CQ49040-0') == 0 ) { // CQ48969-0 (20/09/10��)
        $parts_no = 'CQ42495-0';
    } else if( strcmp($parts_no, 'CQ49059-0') == 0 ) { // CQ49059-0 (20/09/10��)
        $parts_no = 'CQ42489-0';
    } else if( strcmp($parts_no, 'CQ49102-0') == 0 ) { // CQ49102-0 (20/12/17��)
        $parts_no = 'CP05420-0';
    } else if( strcmp($parts_no, 'CQ49103-0') == 0 ) { // CQ49103-0 (20/12/17��)
        $parts_no = 'CP21944-0';

    } else if( strcmp($parts_no, 'LB02450-1') == 0 ) { // LB02450-1 (21/06/30��)
        $parts_no = 'LB02450-0';
    } else if( strcmp($parts_no, 'LB02527-2') == 0 ) { // LB09324-2 (21/06/18��)
        $parts_no = 'LB02527-1';
    } else if( strcmp($parts_no, 'LB09324-3') == 0 ) { // LB09324-3 (20/06/22��)
        $parts_no = 'LB09324-0';

    } else if( strcmp($parts_no, 'LP10359-3') == 0 ) { // LP10359-3 (19/10/18��)
        $parts_no = 'LP10359-1';
    } else if( strcmp($parts_no, 'LP13866-6') == 0 ) { // LP13866-6 (21/06/18��)
        $parts_no = 'LP13866-5';
    } else if( strcmp($parts_no, 'LP13867-5') == 0 ) { // LP13867-5 (21/06/18��)
        $parts_no = 'LP13867-4';
    } else if( strcmp($parts_no, 'LP30920-3') == 0 ) { // LP30920-3 (20/06/22��)
        $parts_no = 'LP30920-2';

    } else if( strcmp($parts_no, 'LQ03998-3') == 0 ) { // LQ03998-3 (20/03/09��)
        $parts_no = 'LQ03998-2';
    } else if( strcmp($parts_no, 'LQ04189-4') == 0 ) { // LQ04189-4 (20/04/13��)
        $parts_no = 'LQ04189-3';
    } else if( strcmp($parts_no, 'LQ06004-1') == 0 ) { // LQ06004-1 (17/04/04��)
        $parts_no = 'LQ07266-0';
    } else if( strcmp($parts_no, 'LQ08075-1') == 0 ) { // LQ08075-1 (21/06/12��)
        $parts_no = 'LQ08075-0';
    } else {
        return false;
    }

    return true;
}

// ACS���̡�Top �� 02 �� [F10] �ǡ�ɽ��������������ʤʤ�
function IsNoSubstitute( $parts_no )
{
    if( // ��ǧ���������������ʤ��ʤ��ä�����
        strcmp($parts_no, 'CA91348-1') == 0

     || strcmp($parts_no, 'CB64153-3') == 0
     || strcmp($parts_no, 'CB66523-0') == 0

     || strcmp($parts_no, 'CP00950-0') == 0
     || strcmp($parts_no, 'CP00996-1') == 0
     || strcmp($parts_no, 'CP02057-7') == 0
     || strcmp($parts_no, 'CP03269-0') == 0
     || strcmp($parts_no, 'CP11554-1') == 0
     || strcmp($parts_no, 'CP11557-0') == 0
     || strcmp($parts_no, 'CP20083-4') == 0
     || strcmp($parts_no, 'CP22066-E') == 0
     || strcmp($parts_no, 'CP22854-0') == 0
     || strcmp($parts_no, 'CP24459-2') == 0

     || strcmp($parts_no, 'CQ01259-0') == 0
     || strcmp($parts_no, 'CQ10337-1') == 0
     || strcmp($parts_no, 'CQ12177-1') == 0
     || strcmp($parts_no, 'CQ15087-5') == 0
     || strcmp($parts_no, 'CQ15403-0') == 0
     || strcmp($parts_no, 'CQ18883-2') == 0
     || strcmp($parts_no, 'CQ18936-1') == 0
     || strcmp($parts_no, 'CQ19809-0') == 0
     || strcmp($parts_no, 'CQ20711-1') == 0
     || strcmp($parts_no, 'CQ21458-1') == 0
     || strcmp($parts_no, 'CQ23525-1') == 0
     || strcmp($parts_no, 'CQ23781-1') == 0
     || strcmp($parts_no, 'CQ23813-1') == 0
     || strcmp($parts_no, 'CQ24758-1') == 0
     || strcmp($parts_no, 'CQ29437-1') == 0
     || strcmp($parts_no, 'CQ30923-1') == 0
     || strcmp($parts_no, 'CQ31278-0') == 0
     || strcmp($parts_no, 'CQ31279-0') == 0
     || strcmp($parts_no, 'CQ31280-0') == 0
     || strcmp($parts_no, 'CQ32206-0') == 0
     || strcmp($parts_no, 'CQ33066-1') == 0
     || strcmp($parts_no, 'CQ33072-1') == 0
     || strcmp($parts_no, 'CQ35226-1') == 0
     || strcmp($parts_no, 'CQ40653-0') == 0
     || strcmp($parts_no, 'CQ41583-0') == 0
     || strcmp($parts_no, 'CQ44406-2') == 0
     || strcmp($parts_no, 'CQ46802-0') == 0
     || strcmp($parts_no, 'CQ46803-0') == 0
     || strcmp($parts_no, 'CQ46804-0') == 0
     || strcmp($parts_no, 'CQ47700-0') == 0
     || strcmp($parts_no, 'CQ47901-0') == 0
     || strcmp($parts_no, 'CQ48239-0') == 0
     || strcmp($parts_no, 'CQ48264-0') == 0
     || strcmp($parts_no, 'CQ48276-0') == 0
     || strcmp($parts_no, 'CQ48344-0') == 0
     || strcmp($parts_no, 'CQ48719-0') == 0
     || strcmp($parts_no, 'CQ48723-0') == 0
     || strcmp($parts_no, 'CQ48724-0') == 0
     || strcmp($parts_no, 'CQ48950-0') == 0
     || strcmp($parts_no, 'CQ49106-0') == 0
     || strcmp($parts_no, 'CQ53406-0') == 0

     || strcmp($parts_no, 'LB07397-2') == 0
     || strcmp($parts_no, 'LB07403-2') == 0
     || strcmp($parts_no, 'LB09324-1') == 0
     || strcmp($parts_no, 'LB09324-2') == 0
     || strcmp($parts_no, 'LB09333-1') == 0
     || strcmp($parts_no, 'LB09603-0') == 0

     || strcmp($parts_no, 'LP14069-B') == 0
     || strcmp($parts_no, 'LP30939-8') == 0
     || strcmp($parts_no, 'LP31351-1') == 0

     || strcmp($parts_no, 'LQ01456-0') == 0
     || strcmp($parts_no, 'LQ01457-0') == 0
     || strcmp($parts_no, 'LQ01478-0') == 0
     || strcmp($parts_no, 'LQ01979-2') == 0
     || strcmp($parts_no, 'LQ01982-1') == 0
     || strcmp($parts_no, 'LQ01983-2') == 0
     || strcmp($parts_no, 'LQ01986-1') == 0
     || strcmp($parts_no, 'LQ02097-0') == 0
     || strcmp($parts_no, 'LQ02098-0') == 0
     || strcmp($parts_no, 'LQ02259-7') == 0
     || strcmp($parts_no, 'LQ02882-0') == 0
     || strcmp($parts_no, 'LQ02883-0') == 0
     || strcmp($parts_no, 'LQ03501-1') == 0
     || strcmp($parts_no, 'LQ03846-0') == 0
     || strcmp($parts_no, 'LQ04994-0') == 0
     || strcmp($parts_no, 'LQ05085-5') == 0
     || strcmp($parts_no, 'LQ05130-5') == 0
     || strcmp($parts_no, 'LQ05329-0') == 0
     || strcmp($parts_no, 'LQ05687-4') == 0
     || strcmp($parts_no, 'LQ05787-3') == 0
     || strcmp($parts_no, 'LQ06165-0') == 0
     || strcmp($parts_no, 'LQ07112-1') == 0
     || strcmp($parts_no, 'LQ07529-0') == 0
     || strcmp($parts_no, 'LQ07785-0') == 0
     || strcmp($parts_no, 'LQ07786-0') == 0
     || strcmp($parts_no, 'LQ07990-0') == 0
     || strcmp($parts_no, 'LQ07994-0') == 0
     || strcmp($parts_no, 'LQ07996-0') == 0
     || strcmp($parts_no, 'LQ08159-0') == 0
     || strcmp($parts_no, 'LQ08188-0') == 0
     || strcmp($parts_no, 'LQ08190-0') == 0
    ) {
        return true;
    } else {
        return false;
    }
}

// ��ݼ��ӤξȲ��оݤΥ쥳���ɤ����
function GetActPayable( $parts_no, $kei_date, $flag, $uke_no, $genpin, &$res )
{
    $query = $field = $res = array();
    $rows = 0;

    // ��ݼ��ӤξȲ񡧷׾����ȼ����ֹ���ȯ��ñ������
    $query = "
                 SELECT   *
                 FROM     act_payable
                 WHERE    act_date = $kei_date AND uke_no = '$uke_no'
                 ORDER BY regdate DESC LIMIT 3
             ";
    $res  = array();
    $rows = getResultWithField2( $query, $field, $res );

    if( $rows > 0 ) {
        return $rows;
    }

    // �����ֹ椬�㤦�Ȥ����б�
    if( $flag == "after" ) {
        $query = "
                    SELECT   *
                    FROM     act_payable
                    WHERE    act_date >= $kei_date AND parts_no = '$parts_no' AND genpin = $genpin
                    ORDER BY act_date ASC LIMIT 3
                 ";
    } else {
        if( $genpin != 0 ) {
            $query = "
                        SELECT   *
                        FROM     act_payable
                        WHERE    act_date < $kei_date AND parts_no = '$parts_no' AND genpin = $genpin
                        ORDER BY act_date DESC LIMIT 3
                     ";
        } else {
            $query = "
                        SELECT   *
                        FROM     act_payable
                        WHERE    act_date < $kei_date AND parts_no = '$parts_no'
                        ORDER BY act_date DESC LIMIT 3
                     ";
        }
    }
    $res  = array();
    $rows = getResultWithField2( $query, $field, $res );

    if( $rows <= 0 ) {
        if( $flag == "after" ) {
            $query = "
                        SELECT   *
                        FROM     act_payable
                        WHERE    act_date >= $kei_date AND parts_no = '$parts_no'
                        ORDER BY act_date ASC LIMIT 3
                     ";
        } else {
            $query = "
                        SELECT   *
                        FROM     act_payable
                        WHERE    act_date < $kei_date AND parts_no = '$parts_no'
                        ORDER BY act_date DESC LIMIT 3
                     ";
        }
        $res  = array();
        $rows = getResultWithField2( $query, $field, $res );
    }

    if( $rows > 0 && ctype_space($res[0][10]) ) {
        $res[0][10] = '--';
        $res[0][12] = 0;
        return 0;
    }

    return $rows;
}

// ñ������ξȲ���ñ�����������
// ��$sei_no�����ʤ���ݼ��ӤξȲ�Υǡ������ʤ����Ȥ�����å���
function GetPartsCost( &$parts_no, &$sei_no, $kei_date, &$res )
{
    $query = $field = array();

    if( empty($sei_no) ) {
        // ��ݼ��ӤξȲ������ֹ����ݥǡ��������뤫�����å�
        $query = "
                     SELECT   *
                     FROM     act_payable
                     WHERE    parts_no = '$parts_no'
                     ORDER BY regdate DESC LIMIT 1
                 ";
        $res  = array();
        if( getResultWithField2($query, $field, $res) > 0 ) {
            return 0; // ��ݼ��Ӥ�������ϡ���Ͽ�����ޤ��Ԥ�ɬ�פ����뤿�ᥨ�顼�ˤ��롣
        }
    }

    $parts_org = $parts_no;
    // ���֤򤵤��Τܤä�ñ������Ͽ����Ƥ���Ȥ����õ����
    for( ; ; ) {
        // ñ������ξȲ������ֹ�ȷ׾�����ȯ��ñ�������Ͽ�ֹ桦��å��ֹ����
        $query = "
                    SELECT   *
                    FROM     parts_cost_history
                    WHERE    parts_no = '$parts_no' AND vendor != 88888 AND as_regdate < $kei_date
                    ORDER BY reg_no DESC, lot_no ASC, pro_no ASC LIMIT 5
                 ";
        $res   = array();
        if( getResultWithField2($query, $field, $res) > 0 ) {
            break;
        }

        $parts_no2 = GetOldNo( $parts_no );
        if( $parts_no2 != $parts_no ) {
            $parts_no = $parts_no2;
            continue;
        }

        $parts_no = $parts_org;
        // �׾����ʸ�˥ǡ������ʤ������ǧ���Ƥߤ�
        $query = "
                    SELECT   *
                    FROM     parts_cost_history
                    WHERE    parts_no = '$parts_no' AND vendor != 88888 AND as_regdate >= $kei_date
                    ORDER BY reg_no ASC, lot_no ASC, pro_no ASC LIMIT 5
                 ";
        $res   = array();
        if( getResultWithField2($query, $field, $res) > 0 ) {
            break;
        }

        return 0;
    }

    if( $sei_no == "cost_only" ) {
        $sei_no = "";
        $query = "
                     SELECT   *
                     FROM     act_payable
                     WHERE    parts_no = '$parts_no' AND act_date < $kei_date
                     ORDER BY regdate DESC LIMIT 1
                 ";
        $res2  = array();
        if( getResultWithField2($query, $field, $res2) > 0 ) {
            $sei_no = $res2[0][17];
        }
    }

    return 1;
}

// ���ʤ�ñ���ѹ��ֹ�(��ϿNo) ����
function GetTanNo( $parts_no, $sei_no )
{
    $query = $field = $res = array();

    $query = "
                 SELECT   *
                 FROM     order_plan
                 WHERE    parts_no = '$parts_no' AND sei_no = $sei_no
                 ORDER BY regdate ASC LIMIT 1
             ";
    $res  = array();
    $rows = getResultWithField2( $query, $field, $res );

    if( $rows < 1 ) {
        return "";
    }

    return $res[0][15];
}

// ���ʤ���ϿNo������ñ���ѹ��ֹ�򥭡��ˤ����
function GetRegNo( $parts_no, $tan_no, &$res )
{
    $query = $field = $res = array();

    $query = "
                SELECT *
                FROM     parts_cost_history
                WHERE    parts_no = '$parts_no' AND vendor != 88888 AND reg_no = $tan_no
                ORDER BY lot_no ASC, pro_no ASC LIMIT 10
             ";
    $res   = array();
    if( ($rows = getResultWithField2($query, $field, $res)) <= 0 ) {
        return 0;
    }

    return $rows;
}

// ñ������ξȲ��оݤΥ쥳���ɤ����
function GetPartsCostHistory( $pro_con, $parts_no, $vendor, $kei_date, $sei_no, $price, &$res )
{
    $query = $field = $res = array();
    $rows = $reg_no = $total = 0;

    $price = sprintf( "%0.02f", $price );

    // ñ������ξȲ񡧺ǽ�ˡ���ϿNo�ǥ����å����롣(��̵���Τ�)
    if( !empty($sei_no) ) {
        $tan_no = GetTanNo( $parts_no, $sei_no );
        if( !empty($tan_no) ) {
            $rows = GetRegNo( $parts_no, $tan_no, $res );
            if( $rows > 0 && $pro_con != '2' ) {
                for( $r = 0; $r < $rows; $r++ ) {
                    // ȯ��ñ����Ʊ��ñ��������ޤǷ����֤�
                    $res[$r][11] = sprintf( "%0.02f", $res[$r][11] );
                    if( $price == $res[$r][11] ) {
                        break;
                    }
                }
                if( $r == $rows ) {
                    $rows = 0;
                }
            }
        }
    }

    // ñ������ξȲ������ֹ�ȷ׾�����ȯ��ñ�������Ͽ�ֹ桦��å��ֹ����
    if( $pro_con != '2' ) {
        // ̵������³
        if( $rows <= 0 ) {
            $query = "
                        SELECT   *
                        FROM     parts_cost_history
                        WHERE    parts_no = '$parts_no' AND vendor != 88888 AND vendor = '$vendor'
                             AND as_regdate <= $kei_date AND lot_cost = $price
                        ORDER BY reg_no DESC, lot_no ASC, pro_no ASC LIMIT 10
                     ";
            $res   = array();
            $rows = getResultWithField2( $query, $field, $res );
        }

        // ʣ����å��ֹ椬����Ȥ�
        for( $r = 0; $r < $rows; $r++ ) {
            // ȯ��ñ����Ʊ��ñ��������ޤǷ����֤�
            $res[$r][11] = sprintf( "%0.02f", $res[$r][11] );
            if( $price == $res[$r][11] ) {
                $reg_no = $res[$r][1]; // ��Ͽ�ֹ�
                $lot_no = $res[$r][8]; // ��å��ֹ�
                break;
            }
        }

        if( $r == $rows ) {
            if( $pro_con != '1' && $vendor != 91111 ) {
                if( COMPLETE ) {
                    $_SESSION['s_sysmsg'] .= "$parts_no : ñ������Ȳ�(̵��)���ԡ�AS/400ü�����׳�ǧ��";
                }
                return 0; // ʣ����������١�NG��Ƚ��
            }
            return -1; // ȯ��ñ���ޤǤϼ����Ǥ��Ƥ���Τ�OK��Ƚ��
        }
    } else {
        // ͭ��
        if( $rows <= 0 ) {
            $query = "
                        SELECT   *
                        FROM     parts_cost_history
                        WHERE    parts_no = '$parts_no' AND vendor != 88888
                             AND as_regdate <= $kei_date
                        ORDER BY reg_no DESC, lot_no ASC, pro_no ASC LIMIT 30
                     ";
            $res   = array();
            $rows = getResultWithField2( $query, $field, $res );
        }

        // ȯ��ñ���ȥȡ�����ñ����Ʊ���ˤʤ���Ͽ�ֹ��õ��
        for( $r = 0; $r < $rows; $r++ ) {
            $total = $total + $res[$r][11];
            if( ($r+1==$rows) || ($res[$r][1] != $res[$r+1][1]) || ($res[$r][8] != $res[$r+1][8]) ) {
                $total = sprintf( "%0.02f", $total );
                if( $total == $price ) {
                    $reg_no = $res[$r][1]; // ��Ͽ�ֹ�
                    $lot_no = $res[$r][8]; // ��å��ֹ�
                    break;
                }
                $total = 0.00;
            }
        }

        if( $r == $rows ) {
            if( COMPLETE ) {
                $_SESSION['s_sysmsg'] .= "$parts_no : ñ������Ȳ�(1-1)���ԡ� ��AS/400ü�����׳�ǧ��";
                return 0;
            }
        }
    }

    // ñ������ξȲ������ֹ�ȷ׾�������Ͽ�ֹ��깩��������̾��ñ�����⳰�����
    $query = "
                SELECT *
                FROM     parts_cost_history
                WHERE    parts_no = '$parts_no' AND vendor != 88888
                     AND reg_no = $reg_no AND lot_no = $lot_no
                ORDER BY reg_no DESC, pro_no ASC, lot_no ASC LIMIT 10
             ";
    $res   = array();
    if( ($rows = getResultWithField2($query, $field, $res)) <= 0 ) {
        $_SESSION['s_sysmsg'] .= "$parts_no : ñ������ξȲ�(2)���ԡ�";
        return 0;
    }

    return $rows;
}

// �о����ʤι���������̾��ñ�����⳰��ǡ��������
function GetDetailData( $parts_no2, $plan_no2, &$d_data, &$sei_no )
{
    $query2 = $field2 = $res2 = array();
    $rows2 = $r = $fstock_mv = 0;

    $rows2 = GetPartsStokHistory( $parts_no2, $plan_no2, false, $res2, $r, $fstock_mv );
    if( $rows2 <= 0 ) {
        return 0;
    } else if( $res2[$r][10] == "PC���ގ��� �̎ގ�" ){
        $_SESSION['s_sysmsg'] .= "$parts_no2 : {$res2[$r][10]}�ΰ١��̻� Excel ����!!";
        return 0;
    }

    $den_kubun = $res2[$r][5]; // ��ɼ��ʬ
    $ent_date = $res2[$r][11]; // �ǡ�������

    if( IsProvide($parts_no2, $ent_date) ) {
        $d_data[0][5] = "�ٵ���";
    }

    /* �ü졧�����ʻ��� + ����� ------------------------------------------> */
    $tomodori = false;
    if( $den_kubun == '6' ) {
        if( !empty($d_data[0][5]) ) {
            return 1;
        }
        $ent_date = date( 'Y/m/d', strtotime($ent_date) );
        $d_data[0][6] = "����";
        if( IsAlternative($parts_no2, $tomodori) ) {
            $den_no = $res2[$r][6];
            $len = strlen( $den_no );
            $work = $den_no - 1;
            $den_no = str_pad( $work, $len, 0, STR_PAD_LEFT );
            $rows2 = GetPartsStokHistory( $parts_no2, false, $den_no, $res2, $r, $fstock_mv );
            if( $rows2 <= 0 ) {
                $work = $den_no + 2;
                $den_no = str_pad( $work, $len, 0, STR_PAD_LEFT );
                $rows2 = GetPartsStokHistory( $parts_no2, false, $den_no, $res2, $r, $fstock_mv );
                if( $rows2 <= 0 ) {
                    return 0;
                }
            }
            $den_kubun = $res2[$r][5]; // ��ɼ��ʬ
        } else {
            if( IsNoSubstitute($parts_no2) ) {
                ; // ��å������Ф��ʤ���
            } else {
                $_SESSION['s_sysmsg'] .= "------------------------------------------------------------------------------- ";
                $_SESSION['s_sysmsg'] .= " $parts_no2 : $ent_date �����ʤΰ١���ưɼ�ʤɤǳ�ǧ���Ʋ�������";
                $_SESSION['s_sysmsg'] .= "��ư�� = {$res2[$r][2]} ����ɼ�ֹ� = {$res2[$r][6]} : ���������ֹ� = ��̤��ǧ��";
                $_SESSION['s_sysmsg'] .= "�������ֹ��ô���Ԥ�Ϣ���ơ��ץ�������ɲä��Ƥ�餤�ޤ��礦��";
                $_SESSION['s_sysmsg'] .= " --------------------------------------------------------------------------------";
            }
        }
    }
    /* <-------------------------------------------------------------------- */

    if( $den_kubun == '6' ) {
        $genpin = 0;            // ��ư�� 0 �ˤ��롣
    } else {
        $genpin = $res2[$r][2]; // ��ư��
    }
    $uke_no = $res2[$r][6];     // �����ֹ�
    $plan_no = $res2[$r][7];    // Ŧ��
    $kei_date = $res2[$r][12];  // �׾���
/**
if( $parts_no2 == 'CB24772-0' ) {
$_SESSION['s_sysmsg'] .= "$parts_no2 : kei_date=$kei_date : uke_no=$uke_no : plan_no = $plan_no";
}
/**/
    if( $den_kubun != '6' ) {
        $rows2 = GetActPayable( $parts_no2, $kei_date, "after", $uke_no, $genpin, $res2 );
    } else {
        $rows2 = GetActPayable( $parts_no2, $kei_date, "before", $uke_no, $genpin, $res2 );
    }

    if( $rows2 > 0 && substr($plan_no, 0 ,1) != '@' ) {
        $d_data[0][1] = $res2[0][10]; // ��������
        $d_data[0][2] = $res2[0][12]; // ȯ��ñ��

        $vendor = $res2[0][6];   // ȯ�����ֹ�
        $pro_con = $res2[0][11]; // �������
        $price = $res2[0][12];   // ȯ��ñ��
        $sei_no = $res2[0][17];  // ��¤�ֹ�
    } else {
        if( substr($plan_no, 0 ,1) == '@' ) {
            // Ŧ�פΡ��ޡ�����������������ʤ���¤�ֹ���Ϥ�
            $sei_no = substr($plan_no, 1);
        }

        if( $den_kubun == '6' ) {
            $sei_no = "cost_only";
            if( GetPartsCost($parts_no2, $sei_no, $kei_date, $res2) <= 0 ) {
                if( $sei_no == "cost_only" ) {
                    $sei_no = "";
                }
                return 0;
            }
        } else {
            if( GetPartsCost($parts_no2, $sei_no, $kei_date, $res2) <= 0 ) {
                if( COMPLETE && $den_kubun != '6' ) {
                    $_SESSION['s_sysmsg'] .= "$parts_no2 : ñ�����򤬸��Ĥ���ޤ��󡪢���ư�Ǹ������Ʋ�������";
                }
                return 0;
            }
        }

        $d_data[0][1] = $res2[0][2];  // ��������
        $d_data[0][2] = $res2[0][11]; // ñ��

        $vendor = $res2[0][3];    // ȯ�����ֹ�
        $pro_con = $res2[0][5];   // �������
        $price = $res2[0][11];    // ��å�ñ��
        $kei_date = $res2[0][12]; // AS400��Ͽ��
    }

/**
if( $parts_no2 == 'CB63941-0' )
$_SESSION['s_sysmsg'] .= "$parts_no2 : price=$price : {$res2[0][11]} : kei_date = $kei_date : pro_con=$pro_con";
/**/

    $rows2 = GetPartsCostHistory( $pro_con, $parts_no2, $vendor, $kei_date, $sei_no, $price, $res2 );

    if( $rows2 < 0 ) {
        return 1; // ȯ��ñ���ޤǤϼ����Ǥ��Ƥ���Τ�OK��Ƚ��
    } else if( $rows2 == 0 ) {
        IniDetailData( $d_data ); // ���ʾ������äƤ����礬����Τǽ�������Ƥ���
        return 0;
    }

    // ɽ���ѥǡ������åȡ�[0]����[1]����̾[2]ñ��[3]�⳰��
    for( $r=0; $r < $rows2; $r++ ) {
        $d_data[$r][0] = $res2[$r][4];  // ����
        $d_data[$r][1] = $res2[$r][2];  // ����̾
        $d_data[$r][2] = $res2[$r][11]; // ñ��

        if( $res2[$r][3] == "01111" || $res2[$r][3] == "00222" ) {
            $d_data[$r][3] = "���"; // �Ď����ގƎ��Ď����������������ގ��� �Ď�������
        } else {
            $d_data[$r][3] = "����"; // ����ʳ�
        }
    }

    /* �ü졧�����ʻ��� + ����� ------------------------------------------> */
    if( $tomodori ) {
        $d_data[0][2] = $d_data[0][2] / 2 ; // �����ΰ١�ñ����Ⱦʬ�ˤ���
    }
    /* <-------------------------------------------------------------------- */

    return $r;
}

// ����������Ͽ�ǡ��������뤫�����å�
function IsMaterial( $plan_no, $assy_no )
{
    $query = $res_chk = array();

    $query = "
                SELECT   *
                FROM     material_cost_history
                WHERE    plan_no = '$plan_no' AND assy_no = '$assy_no'
             ";
    if( getResult2($query, $res_chk) <= 0 ) {
        return false;
    }

    return true;
}

// �Ұ�̾����
function GetName( $uid )
{
    $query = $res_chk = array();

    $query = "
                SELECT   name
                FROM     user_detailes 
                WHERE    uid = '$uid'
             ";
    if( getResult2($query, $res_chk) <= 0 ) {
        return $uid;
    }

    return $res_chk[0][0];
}

// ��Ͽ�������
function GteRegDate( $plan_no, $assy_no )
{
    $query = $res = array();

    $query = "
                SELECT TO_CHAR(last_date, 'YYYY/MM/DD')
                FROM material_cost_header
                WHERE plan_no = '$plan_no' AND assy_no= '$assy_no'
             ";
    if( getResult2($query, $res) <= 0 ) {
        return "----/--/--";
    }

    return $res[0][0];
}

// ��Ͽ��̾�����
function GteRegUser( $plan_no, $assy_no )
{
    $query = $res = array();

    $query = "
                SELECT last_user
                FROM material_cost_header
                WHERE plan_no = '$plan_no' AND assy_no= '$assy_no'
             ";
    if( getResult2($query, $res) <= 0 ) {
        return "---- ----";
    }

    return GetName($res[0][0]);
}





$menu->out_html_header();
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=EUC-JP">
<meta http-equiv="Content-Style-Type" content="text/css">
<meta http-equiv="Content-Script-Type" content="text/javascript">
<title><?php echo $menu->out_title() ?></title>
<?php echo $menu->out_site_java() ?>
<?php echo $menu->out_css() ?>

<!--    �ե��������ξ��
<script type='text/javascript' language='JavaScript' src='template.js?<?php echo $uniq ?>'>
</script>
-->

<script type='text/javascript' language='JavaScript'>
<!--
/* ����ʸ�����������ɤ��������å�(ASCII code check) */
function isDigit(str) {
    var len = str.length;
    var c;
    for (i=0; i<len; i++) {
        c = str.charAt(i);
        if ((c < '0') || (c > '9')) {
            return false;
        }
    }
    return true;
}

/* ����ʸ��������ե��٥åȤ��ɤ��������å� isDigit()�ε� */
function isABC(str) {
    var len = str.length;
    var c;
    for (i=0; i<len; i++) {
        c = str.charAt(i);
        if ((c < 'A') || (c > 'Z')) {
            if (c == ' ') continue; // ���ڡ�����OK
            return false;
        }
    }
    return true;
}

/* ����ʸ�����������ɤ��������å� �������б� */
function isDigitDot(str) {
    var len = str.length;
    var c;
    var cnt_dot = 0;
    for (i=0; i<len; i++) {
        c = str.charAt(i);
        if (c == '.') {
            if (cnt_dot == 0) {     // 1���ܤ������å�
                cnt_dot++;
            } else {
                return false;       // 2���ܤ� false
            }
        } else {
            if (('0' > c) || (c > '9')) {
                return false;
            }
        }
    }
    return true;
}

function checkDelete(url, delParts, sumQT)
{
    if (sumQT == 0) {
        if (confirm(delParts + "�����ʤ������ޤ���\n\n�������Ǥ�����")) {
            parent.location.replace(url<?php echo "+\"{$param}\""?>);
        }
    } else {
        if (confirm(delParts + "�ϴ��˽и˺ѤߤǤ�������Ǥ������ޤ�����\n\n����������ϸ����᤻�ޤ���\n\n�������Ǥ�����")) {
            parent.location.replace(url<?php echo "+\"{$param}\""?>);
        }
    }
}

/* ������ϥե�����Υ�����Ȥ˥ե������������� */
function set_focus(){
    // <a name='mark' �ǥե����������ܤ뤿��0.1�ä��餷�ƥե��������򥻥åȤ��롣
    // �ե졼����ڤäƤ��ʤ�����ե����������Ѥ����mark�ؤ����ʤ����ᥳ����
    // setTimeout("document.mhForm.backwardStack.focus()", 100);  //��������ѹ���NN�б�
}
// -->
</script>

<!-- �������륷���ȤΥե��������򥳥��� HTML���� �����Ȥ�����Ҥ˽���ʤ��������
<link rel='stylesheet' href='template.css?<?php echo $uniq ?>' type='text/css' media='screen'>
-->

<style type="text/css">
<!--
.pt8 {
    font-size:      8pt;
    font-weight:    normal;
    font-family:    monospace;
}
.pt9 {
    font-size:      9pt;
    font-weight:    normal;
    font-family:    monospace;
}
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
.pt11b {
    font-size:      11pt;
    font-weight:    bold;
}
.pt12b {
    font-size:      12pt;
    font-weight:    bold;
    font-family: monospace;
}
th {
    background-color:   yellow;
    color:              blue;
    font-size:          10pt;
    font-wieght:        bold;
    font-family:        monospace;
}
a:hover {
    background-color:   gold;
    color:              darkblue;
}
a {
    font-size:          11pt;
    font-weight:        bold;
    color:              blue;
    text-decoration:    none;
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
-->
</style>
</head>
<body onLoad='set_focus()'>
    <center>
        
        <!--------------- ����������ʸ��ɽ��ɽ������ -------------------->
        <table width='100%' bgcolor='#d6d3ce' align='center' border='1' cellspacing='0' cellpadding='1'>
            <tr><td> <!----------- ���ߡ�(�ǥ�������) ------------>
        <table width='100%' class='winbox_field' bgcolor='#d6d3ce' align='center' border='1' cellspacing='0' cellpadding='3'>
            <?php
            $ok_color = "#d6d3ce"; // �Ȳ��������Υ��顼������
            $ng_color = "#F5A9F2"; // �Ȳ��Ի��Υ��顼������
            $cc_color = "#e6e6e6"; // CC����ɽ�����Υ��顼������
            $color = $entry_data = array();
            $entry_ok = true; // �Ȳ���

            /* �����ˡ��������׶�ۤ�ɽ������� -------------------> */
            $int_kin = 0;        // ��������
            $ext_kin = 0;        // ���������
            $assy_int_price = 0; // ��Ω��
            $m_time = 0;         // ���ȹ���
            $m_rate = 0;         // ������Ψ
            $a_time = 0;         // ��ư������
            $a_rate = 0;         // ��ư����Ψ
            $g_time = 0;         // ������
            $g_rate = 0;         // ������Ψ
            $last_date = "�ǡ���̵��";
            $last_user = "------";
            $query = "
                        SELECT m_time, m_rate, a_time, a_rate, g_time, g_rate,
                               assy_time, assy_rate, TO_CHAR(last_date, 'YYYY/MM/DD'), last_user
                        FROM material_cost_header
                        WHERE assy_no='$assy_no' AND LENGTH(last_user) = 6
                        ORDER BY last_date DESC LIMIT 1
                     ";
            $res_time = array();
            if ( getResult2($query, $res_time) > 0 ) {
                $m_time = $res_time[0][0];
                $m_rate = $res_time[0][1];
                $a_time = $res_time[0][2];
                $a_rate = $res_time[0][3];
                $g_time = $res_time[0][4];
                $g_rate = $res_time[0][5];
                ///// ��� ��Ω��(������)
                $assy_int_price = ( (Uround($m_time * $m_rate, 2)) + 
                                    (Uround($a_time * $a_rate, 2)) + 
                                    (Uround($g_time * $g_rate, 2)) );
                $last_date = $res_time[0][8];
                $last_user = $res_time[0][9];
            }
            /* <------------------------------------------------------------ */

            for( $r=0, $e=0; $r<$rows_view; $r++ ) {
                if( $row_no == $r ) {
                    if(  $res_view2[$r][12] != "CC����" ) { // �������Υե�����ɤ�����å����ư������ʤʤ�
                        echo "<tr style='background-color:#ffffc6;' onDblClick='checkDelete(\"", $menu->out_self(), '?delParts=', urlencode($res_view2[$r][1]), "\", \"{$res_view2[$r][1]}\", \"{$res_view2[$r][7]}\")'>\n";
                    } else {
                        echo "<tr style='background-color:#ffffc6;'>\n";
                    }
                    echo "    <td class='winbox' width=' 4%' nowrap style='font-size:10pt; font-weight:bold; font-family:monospace;' align='right'><a name='mark'>\n";
                    echo "            ", ($r + $offset + 1), "\n";
                    echo "    </a></td>    <!-- �ԥʥ�С���ɽ�� -->\n";
                } else {
                    if(  $res_view2[$r][12] != "CC����" ) { // �������Υե�����ɤ�����å����ư������ʤʤ�
                        echo "<tr onMouseOver=\"style.background='#ceffce'\" onMouseOut=\"style.background='#d6d3ce'\" onDblClick='checkDelete(\"", $menu->out_self(), '?delParts=', urlencode($res_view2[$r][1]), "\", \"{$res_view2[$r][1]}\", \"{$res_view2[$r][7]}\")'>\n";
                    } else {
                        echo "<tr onMouseOver=\"style.background='#ceffce'\" onMouseOut=\"style.background='#d6d3ce'\">\n";
                    }
                    echo "    <td class='winbox' width=' 4%' nowrap style='font-size:10pt; font-weight:bold; font-family:monospace;' align='right'>\n";
                    echo "            ", ($r + $offset + 1), "\n";
                    echo "    </td>    <!-- �ԥʥ�С���ɽ�� -->\n";
                }

                if( $res_view2[$r][12] == "CC����" || $res_view2[$r][12] == "�ٵ���" ) {
                    $color = $cc_color; // CC���ʻ��Υ��顼�����ɥ��å�
                } else if( $res_view2[$r][7] != "--" && !ctype_space($res_view2[$r][7]) ) {
                    $color = $ok_color; // �Ȳ��������Υ��顼�����ɥ��å�
                } else if( $res_view2[$r][7] == "--" || ctype_space($res_view2[$r][7]) ) {
                    $color = $ng_color; // �Ȳ��Ի��Υ��顼�����ɥ��åȡʥԥ󥯡�
                }

                if( $r != 0 && $res_view2[$r][1] == $res_view2[$r-1][1] && $res_view2[$r][0] == $res_view2[$r-1][0]) {
                    $part_no_view = "��";
                    $part_na_view = "��";
                } else {
                    $part_no_view = $res_view2[$r][1];
                    $part_na_view = $res_view2[$r][2];
                }

                /* �������Ƚи˿������פ��ʤ� -----------------------------> */
                if( $res_view2[$r][4] != $res_view2[$r][5] ) {
                    if( $res_view2[$r][5] > $res_view2[$r][4]/2 ) {
                        $color = "#A9F5F2"; // �ʿ忧��
                    } else {
                        $color = $ng_color; // �Ȳ��Ի��Υ��顼�����ɥ��åȡʥԥ󥯡�
                    }
                }
                /* <-------------------------------------------------------- */

                /* �������� -----------------------------------------------> */
                if( !empty($res_view2[$r][13]) && $res_view2[$r][13] == "����" ) {
                    $color = "#00FF00"; // ���п���
                }
                /* <-------------------------------------------------------- */

                /* NG��������(CC���ʤǡ�NG�Ȥʤä���) ---------------------> */
                if( $color == $ng_color && $res_view2[$r][12] == "CC����" ) {
                    $color = $cc_color; // CC���ʻ��Υ��顼�����ɥ��å�
                }
                /* <-------------------------------------------------------- */

                /* �����ˡ��������׶�ۤ�ɽ������� ---------------> */
                if( $res_view2[$r][10] == "���" ) {
                    $int_kin += $res_view2[$r][9];
                } else {
                    $ext_kin += $res_view2[$r][9];
                }
                /* <-------------------------------------------------------- */

                if( $color == $ng_color ) {
                    $entry_ok = false; // �Ȳ�����ˤǤ��Ƥ��ʤ����
                }

                /* �ü졧CB09209- CB09212- ���� ����Ͽ�Ǥ���褦�Խ���-----> */
                $sp_conver = false;
                if( strncmp($assy_no, 'CB09209-', 8)==0 || strncmp($assy_no, 'CB09212-', 8)==0 ) {
                    $sp_conver = true;
                }
                /* <-------------------------------------------------------- */

                for( $i=0; $i<$num_view; $i++ ) { // �ե�����ɿ�ʬ���֤�
                    /* ��Ͽ���ˡ�ɬ�פʺ����ǡ�����ޤȤ�� ---------------> */
                    // entry_data[0]�����ֹ�[1]����[2]����̾[3]���ֹ�[4]����ñ��[5]���ѿ�[6]�⳰��
                    if( $entry_ok || (!$entry_ok && COMPLETE) ) {
                        switch ($i) {
                            case  1: // �����ֹ�
                                $entry_data[$r][0] = $res_view2[$r][$i];
                                break;
                            case  6: // ����
                                $entry_data[$r][1] = $res_view2[$r][$i];
                                break;
                            case  7: // ����̾
                                // ��Ƭ�˶��򤬤������Ͽ�إ��ԡ����˥��顼�ˤʤ�١�trim()�ɲ�
                                $entry_data[$r][2] = trim($res_view2[$r][$i]);
                                break;
                            case 11: // ���ֹ�
                                if( strcmp($res_view2[$r][$i], "---------") == 0 ) {
                                    $entry_data[$r][3] = '';
                                } else {
                                    $entry_data[$r][3] = $res_view2[$r][$i];
                                }
                                break;
                            case  8: // ����ñ��
                                $entry_data[$r][4] = $res_view2[$r][$i];
                                break;
                            case  3: // ���ѿ�
                                $entry_data[$r][5] = $res_view2[$r][$i];
                                break;
                            case 10: // �⳰��
                                if( strcmp($res_view2[$r][$i], "����") == 0 ) {
                                    $entry_data[$r][6] = '0';
                                } else {
                                    $entry_data[$r][6] = '1';
                                }
                                break;
                            default: // ����¾�ǡ���ɬ�פʤ�
                                break;
                        }
                    }
                    /* <---------------------------------------------------- */

                    if( $color == $ok_color ) {
                        switch ($i) {
                            case  0: // ��٥�
                                echo "<td class='winbox' width=' 7%' nowrap align='left' style='font-size:10pt; font-weight:bold; font-family:monospace;'>" . $res_view2[$r][$i] . "</td>\n";
                                break;
                            case  1: // �����ֹ�
                                echo "<td class='winbox' width='10%' nowrap align='center' style='font-size:9pt; font-family:monospace;'><a href='", $menu->out_action('�߸˷���'), "?parts_no=", urlencode($part_no_view), "&plan_no=", urlencode($plan_no), "&material=1&row={$r}' target='_parent' style='text-decoration:none;'>{$part_no_view}</a></td>\n";
                                break;
                            case  2: // ����̾
                                echo "<td class='winbox' width='25%' nowrap align='left' style='font-size:9pt; font-family:monospace;'>{$part_na_view}</td>\n";
                                break;
                            case  3: // ���ѿ�
                                echo "<td class='winbox' width=' 6%' nowrap align='right' style='font-size:9pt; font-family:monospace;'>" . number_format($res_view2[$r][$i], 4) . "</td>\n";
                                break;
                            case  4: // ������
                                echo "<td class='winbox' width=' 5%' nowrap align='right' style='font-size:9pt; font-family:monospace;'>" . number_format($res_view2[$r][$i], 0) . "</td>\n";
                                break;
                            case  5: // �и˿�
                                echo "<td class='winbox' width=' 5%' nowrap align='right' style='font-size:9pt; font-family:monospace;'>" . number_format($res_view2[$r][$i], 0) . "</td>\n";
                                break;
                            case  6: // ����
                                echo "<td class='winbox' width=' 3%' nowrap align='center' style='font-size:9pt; font-family:monospace;'>" . number_format($res_view2[$r][$i], 0) . "</td>\n";
                                break;
                            case  7: // ����̾
                                echo "<td class='winbox' width=' 4%' nowrap align='center' style='font-size:9pt; font-family:monospace;'>{$res_view2[$r][$i]}</td>\n";
                                break;
                            case  8: // ����ñ��
                                echo "<td class='winbox' width=' 7%' nowrap align='right' style='font-size:9pt; font-family:monospace;'>" . number_format($res_view2[$r][$i], 2) . "</td>\n";
                                break;
                            case  9: // �������
                                echo "<td class='winbox' width=' 7%' nowrap align='right' style='font-size:9pt; font-family:monospace;'>" . number_format($res_view2[$r][$i], 2) . "</td>\n";
                                break;
                            case 10: // �⳰��
                                echo "<td class='winbox' width=' 5%' nowrap align='center' style='font-size:9pt; font-family:monospace;'>{$res_view2[$r][$i]}</td>\n";
                                break;
                            case 11: // ���ֹ�
                                echo "<td class='winbox' width=' 7%' nowrap align='right' style='font-size:9pt; font-family:monospace;'>{$res_view2[$r][$i]}</td>\n";
                                break;
                            default: // 12 CC����
                                echo "<td class='winbox' width=' 5%' nowrap align='center' style='font-size:9pt; font-family:monospace;'>{$res_view2[$r][$i]}</td>\n";
                        }
                    } else {
                        switch ($i) {
                            case  0: // ��٥�
                                echo "<td class='winbox' width=' 7%' nowrap align='left' style='font-size:10pt; font-weight:bold; font-family:monospace;'>" . $res_view2[$r][$i] . "</td>\n";
                                break;
                            case  1: // �����ֹ�
                                echo "<td class='winbox' width=' 10%' bgcolor=$color nowrap align='center' style='font-size:9pt; font-family:monospace;'><a href='", $menu->out_action('�߸˷���'), "?parts_no=", urlencode($part_no_view), "&plan_no=", urlencode($plan_no), "&material=1&row={$r}' target='_parent' style='text-decoration:none;'>{$part_no_view}</a></td>\n";
                                break;
                            case  2: // ����̾
                                echo "<td class='winbox' width='25%' bgcolor=$color nowrap align='left' style='font-size:9pt; font-family:monospace;'>{$part_na_view}</td>\n";
                                break;
                            case  3: // ���ѿ�
                                echo "<td class='winbox' width=' 6%' bgcolor=$color nowrap align='right' style='font-size:9pt; font-family:monospace;'>" . number_format($res_view2[$r][$i], 4) . "</td>\n";
                                break;
                            case  4: // ������
                                echo "<td class='winbox' width=' 5%' bgcolor=$color nowrap align='right' style='font-size:9pt; font-family:monospace;'>" . number_format($res_view2[$r][$i], 0) . "</td>\n";
                                break;
                            case  5: // �и˿�
                                echo "<td class='winbox' width=' 5%' bgcolor=$color nowrap align='right' style='font-size:9pt; font-family:monospace;'>" . number_format($res_view2[$r][$i], 0) . "</td>\n";
                                break;
                            case  6: // ����
                                echo "<td class='winbox' width=' 3%' bgcolor=$color nowrap align='center' style='font-size:9pt; font-family:monospace;'>" . number_format($res_view2[$r][$i], 0) . "</td>\n";
                                break;
                            case  7: // ����̾
                                echo "<td class='winbox' width=' 4%' bgcolor=$color nowrap align='center' style='font-size:9pt; font-family:monospace;'>{$res_view2[$r][$i]}</td>\n";
                                break;
                            case  8: // ����ñ��
                                echo "<td class='winbox' width=' 7%' bgcolor=$color nowrap align='right' style='font-size:9pt; font-family:monospace;'>" . number_format($res_view2[$r][$i], 2) . "</td>\n";
                                break;
                            case  9: // �������
                                echo "<td class='winbox' width=' 7%' bgcolor=$color nowrap align='right' style='font-size:9pt; font-family:monospace;'>" . number_format($res_view2[$r][$i], 2) . "</td>\n";
                                break;
                            case 10: // �⳰��
                                echo "<td class='winbox' width=' 5%' bgcolor=$color nowrap align='center' style='font-size:9pt; font-family:monospace;'>{$res_view2[$r][$i]}</td>\n";
                                break;
                            case 11: // ���ֹ�
                                echo "<td class='winbox' width=' 7%' bgcolor=$color nowrap align='right' style='font-size:9pt; font-family:monospace;'>{$res_view2[$r][$i]}</td>\n";
                                break;
                            default: // 12 CC����
                                echo "<td class='winbox' width=' 5%' bgcolor=$color nowrap align='center' style='font-size:9pt; font-family:monospace;'>{$res_view2[$r][$i]}</td>\n";
                        }
                    }
                }
                echo "</tr>\n";

                /* �ü졧CB09209- CB09212- ���� ����Ͽ�Ǥ���褦�Խ���-----> */
                if( $entry_ok || (!$entry_ok && COMPLETE) ) {
                    if( $sp_conver ) {
                        $add_on = false;
                        if( $res_view2[$r][0] != ".1" && $r+1<$rows_view ) { // ��٥�.1�ʳ����ġ��ǽ��ԤǤϤʤ�

                            for( $w=$r+1; $w<$rows_view; $w++ ) {
                                if( $res_view2[$r][0] == $res_view2[$w][0] ) {
                                    continue; // ��٥뤬Ʊ���֤ϼ��ιԤ�
                                }
                                if( $res_view2[$r][1] == $res_view2[$w][11]) {
                                    $add_on = true; // ��Ͽ�Ԥ����ֹ�ʤ��ɲåե饰ON
                                }
                                break;
                            }

                            for( $x=$r-strlen($res_view2[$r][6]); $x>=0; $x-- ) { // ��Ͽ�Ԥ����򸡺�
                                if( $res_view2[$r][0] != $res_view2[$x][0]     // ��٥�
                                 || $res_view2[$r][1] != $res_view2[$x][1]     // �����ֹ�
                                 || $res_view2[$r][6] != $res_view2[$x][6] ) { // ����
                                    continue;
                                }
                                // ��٥롢�����ֹ桢���������פ����Ԥ�ȯ������
                                if( $add_on ) {
                                    $add_on = false; // �ե饰�ꥻ�å�
                                    for( $w=$x+1; $w<$rows_view; $w++ ) {
                                        if( $res_view2[$r][0] == $res_view2[$w][0] ) {
                                            continue; // ��٥뤬Ʊ���֤ϼ��ιԤ�
                                        }
                                        if( $res_view2[$r][1] == $res_view2[$w][11] ) {
                                            $add_on = true; // ��Ͽ�Ԥ����ֹ�ʤ��ɲåե饰ON
                                        }
                                        break;
                                    }
                                } else {
                                    if( $res_view2[$r][0] != '..2' ) {
                                        $add_on = true; // ��٥�...3�ʹߤǻ����ʤ��ʤ���Τ��ɲ�
                                    }
                                }
                                break;
                            }
                            if( $x == -1 ) $add_on = false;
                        }
    
                        if( $add_on ) {
                            $entry_data[$x][5] += $res_view2[$r][3]; // Ʊ������ʤΰ١����̤�­��
                        } else {
                            $entry_data[$e][0] = $res_view2[$r][1]; // �����ֹ�
                            $entry_data[$e][1] = $res_view2[$r][6]; // ����
                            $entry_data[$e][2] = $res_view2[$r][7]; // ����̾
                            if( strcmp($res_view2[$r][11], "---------") == 0 ) {
                                $entry_data[$e][3] = '';
                            } else {
                                $entry_data[$e][3] = $res_view2[$r][11]; // ���ֹ�
                            }
                            $entry_data[$e][4] = $res_view2[$r][8]; // ����ñ��
                            $entry_data[$e][5] = $res_view2[$r][3]; // ���ѿ�
                            if( strcmp($res_view2[$r][10], "����") == 0 ) {
                                $entry_data[$e][6] = '0'; // ����
                            } else {
                                $entry_data[$e][6] = '1'; // ���
                            }
                            $e++;
                            $entry_data[$e] = array();
                        }
                    }
                }
                /* <-------------------------------------------------------- */
            }
            ?>
        </table>
            </td></tr>
        </table> <!----------------- ���ߡ�End ------------------>
        <?php
        $inquiries_only = $_SESSION['inquiries_only'];
        if( !$inquiries_only && $sp_conver ) {
            echo "<p class='pt10b' rel='nofollow'> �����ʤΡ�����������Ͽ�ʹ������١ˡ۲��̤ϡ����Ѥ��Ѵ��������äƤ��ޤ�����׺����������Ϥ���ޤ���!!</p>";
        }
        ?>

        <!------- �����ˡ��������׶�ۤ�ɽ������� ------>
        <table width='100%' bgcolor='#d6d3ce' align='center' border='1' cellspacing='0' cellpadding='3'>
            <tr><td> <!----------- ���ߡ�(�ǥ�������) ------------>
        <table width='100%' class='winbox_field' bgcolor='#d6d3ce' align='center' border='1' cellspacing='0' cellpadding='3'>
            <tr>
                <td class='winbox' align='right'>
                    <div class='pt10'>
                    ��������<?php echo number_format($int_kin, 2) ."\n" ?>
                    ���������<?php echo number_format($ext_kin, 2) ."\n" ?>
                    <?php $total = number_format($int_kin + $ext_kin, 2); ?>
                    ��׺�����<?php echo $total ."\n" ?>
                    <?php if( !$inquiries_only ) { // �Ȳ񤸤�ʤ��� ?>
                        <input type="button" class='pt9' value="���ԡ�" onMouseout="document.body.style.cursor='auto';" title='��׺�����򥳥ԡ����ޤ���' onclick='if(window.clipboardData){window.clipboardData.setData("text","<?php echo $total ?>");}else if(navigator.clipboard){navigator.clipboard.writeText("<?php echo $total ?>");}else{alert("���Υ֥饦���Ǥϡ����ԡ���ǽ�����Բġ�\n\n��׺������ʬ�ǥ��ԡ����Ʋ�������");}'>
                    <?php } ?>
                    <?php
                    $reg_date = GteRegDate( $plan_no, $assy_no );
                    if( $reg_date == "----/--/--" && $inquiries_only && !COMPREGI && ($_SESSION['User_ID'] == '300667' || $_SESSION['User_ID'] == '970352') ) {
                        echo "<a class='pt10' href='", $menu->out_self(), "?plan_no=", urlencode($plan_no), "&comp_regi=true", "&material=1\")' target='application' style='text-decoration:none;'>.</a>";
                    } else{
                        echo "<br>";
                    }

                    if( $inquiries_only ) { // �Ȳ�Τ�
                        unset( $_SESSION['entry_data'] );
                        unset( $_SESSION['assy_reg_data'] );

                        if( COMPLETE ) {    // ������
//                            echo "<br>";
                            $compdate = date( 'Y/m/d', strtotime(COMPDATE) );
                            $reg_date = GteRegDate( $plan_no, $assy_no );
                            if( $reg_date == "----/--/--" ) { // ̤��Ͽ
//                                echo "(��������$compdate / ̤��Ͽ)";
                            } else {
                                echo "<br>";
//                                if( $_SESSION['User_ID'] == '300667' ) {
                                    $user = trim(GteRegUser($plan_no, $assy_no));
                                    echo "(��������$compdate / ��Ͽ����$reg_date [$user])";
//                                } else {
//                                    echo "(��������$compdate / ��Ͽ����$reg_date)";
//                                }
                            }
//                        } else {
//                            echo "(̤����)";
                        }
                    } else { // ��Ͽ���ꥢ��ɽ��
                    ?>
                        �������ޤǻ��ͤˡ��ʲ�������(<?php echo $last_date?>)��Ͽ���ι�������Ψ�ǡ���
                        <br>
                        <table class='pt10' border="1" cellspacing="0">
                            <tr>
                                <td>���ȹ���</td>
                                <td>������Ψ</td>
                                <td>��ư������</td>
                                <td>��ư����Ψ</td>
                                <td>������</td>
                                <td>������Ψ</td>
                            </tr>
                            <tr align='right'>
                                <td><?php echo number_format($m_time, 3) ."\n" ?></td>
                                <td><?php echo number_format($m_rate, 2) ."\n" ?></td>
                                <td><?php echo number_format($a_time, 3) ."\n" ?></td>
                                <!-- ��ư����Ψ�������硢���Ū��ACS���Ĵ�٤뤳�� -->
                                <?php if( $a_rate != 0 ) { ?>
                                <td style='background-color:yellow; color:red;' >
                                <?php } else { ?>
                                <td>
                                <?php } ?>
                                <!----------------------------------------------------->
                                    <?php echo number_format($a_rate, 2) ."\n" ?></td>
                                <td><?php echo number_format($g_time, 3) ."\n" ?></td>
                                <td><?php echo number_format($g_rate, 2) ."\n" ?></td>
                            </tr>
                        </table>
                        ��Ͽ�ԡ�<?php echo GetName($last_user) ."\n" ?>
                        ����Ω��<?php echo number_format($assy_int_price, 2) ."\n" ?>
                        ���������<?php echo number_format($int_kin + $ext_kin + $assy_int_price, 2) ."\n" ?>
                        <br>
                        ���������������Ͽ��λ����ˤϡ��ǿ��ι�������Ψ���ǧ���Ʋ�������
                        <br>
                        <?php
                        echo "<td class='winbox' nowrap align='center'>";
                        if( IsMaterial($plan_no, $assy_no) == true ) { // ��Ͽ���̤ˡ���Ͽ�ǡ�������
                            unset( $_SESSION['entry_data'] );
                            unset( $_SESSION['assy_reg_data'] );
                            if( COMPDATE < date('Ymd', strtotime("today -30 day")) ) {
                                $reg_date = GteRegDate( $plan_no, $assy_no );
                                echo "<p class='pt10' rel='nofollow'> $reg_date<br>��������<br>��Ͽ�ϴ�λ<br>���Ƥ��ޤ�</p>";
                            } else {
                                echo "<p class='pt10' rel='nofollow'> ��������<br>��Ͽ���̤�<br>�ǡ�������<br>���ԡ��Բ�</p>";
                                if( COMPLETE ) {
                                    echo "<a class='pt10' href='", $menu->out_action('���������Ͽ'), "?plan_no=", urlencode($plan_no), "&assy_no=", urlencode($assy_no), "&data_copy=on ' target='_parent' style='text-decoration:none;'>��Ͽ���̤ذ�ư</a>";
                                }
                            }
                        } else { // ��Ͽ���̤ˡ���Ͽ�ǡ����ʤ�
                            // ��Ͽ���ˡ�ɬ�פ���Ω��ǡ�����ޤȤ��
                            $assy_reg_data[0] = $m_time;
                            $assy_reg_data[1] = $m_rate;
                            $assy_reg_data[2] = $a_time;
                            $assy_reg_data[3] = $a_rate;
                            $assy_reg_data[4] = $g_time;
                            $assy_reg_data[5] = $g_rate;
                            if( strcmp($last_date, "�ǡ���̵��") == 0 ) {
                                unset( $_SESSION['assy_reg_data'] );
                            } else {
                                $_SESSION['assy_reg_data'] = $assy_reg_data; // ��Ω��ǡ����򥻥å����˥��å�
                            }
                            $_SESSION['entry_data'] = $entry_data;           // �����ǡ����򥻥å����˥��å�

                            if( $entry_ok ) { // �Ȳ�ϣ�
                                echo "<p class='pt10b' rel='nofollow'> �Ȳ�ϣ�<br></p>";
                                if( COMPLETE ) { // ������
                                    echo "<a class='pt10' href='", $menu->out_action('���������Ͽ'), "?plan_no=", urlencode($plan_no), "&assy_no=", urlencode($assy_no), "&data_copy=on ' target='_parent' style='text-decoration:none;'>��Ͽ���̤�<br>���ԡ�</a>";
                                } else {
                                    unset( $_SESSION['entry_data'] );
                                    unset( $_SESSION['assy_reg_data'] );
                                }
                            } else { // �Ȳ�Σ�
                                echo "<p class='pt10b' rel='nofollow'> �Ȳ�Σ�<br></p>";
                                if( COMPLETE ) { // ������
                                    echo "<a class='pt10' href='", $menu->out_action('���������Ͽ'), "?plan_no=", urlencode($plan_no), "&assy_no=", urlencode($assy_no), "&data_copy=on ' target='_parent' style='text-decoration:none;'>�ΣǤ�ޤ�<br>��Ͽ���̤�<br>���ԡ�</a>";
                                } else {
                                    unset( $_SESSION['entry_data'] );
                                    unset( $_SESSION['assy_reg_data'] );
                                }
                            }
                        }
                        echo "</td>";
                    } // $inquiries_only
                    ?>
                    </div>
                </td>
            </tr>
        </table>
            </td></tr>
        </table> <!----------------- ���ߡ�End --------------------->
        <!------------------------------------------------------------------>

    </center>
</body>
<?php echo $menu->out_alert_java()?>
</html>
<?php
ob_end_flush();                 // ���ϥХåե���gzip���� END
?>
