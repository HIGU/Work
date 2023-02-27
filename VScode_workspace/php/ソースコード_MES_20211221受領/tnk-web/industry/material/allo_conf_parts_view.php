<?php
//////////////////////////////////////////////////////////////////////////////
// �������ʹ���ɽ�ξȲ�  �ײ��ֹ��ɽ�� view                                //
//                              Allocated Configuration Parts ������������  //
// Copyright (C) 2004-2007 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp      //
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
access_log();                               // Script Name �ϼ�ư����

///// TNK ���ѥ�˥塼���饹�Υ��󥹥��󥹤����
$menu = new MenuHeader(0);                  // ǧ�ڥ����å�0=���̰ʾ� �����=TOP_MENU �����ȥ�̤����

////////////// ����������
$menu->set_site(30, 26);                    // site_index=30(������˥塼) site_id=26(�������ʹ���ɽ�ξȲ�)
////////////// �꥿���󥢥ɥ쥹����
// $menu->set_RetUrl(INDUST_MENU);          // �̾�ϻ��ꤹ��ɬ�פϤʤ�
//////////// �����ȥ�̾(�������Υ����ȥ�̾�ȥե�����Υ����ȥ�̾)
$menu->set_title('���� ���� ����ɽ �� �Ȳ�');
//////////// �ƽ����action̾�ȥ��ɥ쥹����
// $menu->set_action('��������ɽ��ɽ��',   INDUST . 'material/allo_conf_parts_view.php');
$menu->set_action('�߸˷���',   INDUST . 'parts/parts_stock_history/parts_stock_history_Main.php');
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
    }
} else {
    $parts_no = '';
    $row_no   = '-1';       // ñ�ΤǾȲ񤵤줿��
    $param    = '';
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
if ( isset($_POST['forward']) ) {                       // ���Ǥ������줿
    $_SESSION['offset'] += PAGE;
    if ($_SESSION['offset'] >= $maxrows) {
        $_SESSION['offset'] -= PAGE;
        if ($_SESSION['s_sysmsg'] == "") {
            $_SESSION['s_sysmsg'] .= "<font color='yellow'>���ǤϤ���ޤ���</font>";
        } else {
            $_SESSION['s_sysmsg'] .= "<br><font color='yellow'>���ǤϤ���ޤ���</font>";
        }
    }
} elseif ( isset($_POST['backward']) ) {                // ���Ǥ������줿
    $_SESSION['offset'] -= PAGE;
    if ($_SESSION['offset'] < 0) {
        $_SESSION['offset'] = 0;
        if ($_SESSION['s_sysmsg'] == "") {
            $_SESSION['s_sysmsg'] .= "<font color='yellow'>���ǤϤ���ޤ���</font>";
        } else {
            $_SESSION['s_sysmsg'] .= "<br><font color='yellow'>���ǤϤ���ޤ���</font>";
        }
    }
} elseif ( isset($_GET['page_keep']) || isset($_GET['number']) ) {   // ���ߤΥڡ�����ݻ�����
    $offset = $_SESSION['offset'];
} else {
    $_SESSION['offset'] = 0;                            // ���ξ��ϣ��ǽ����
}
$offset = $_SESSION['offset'];


//////////// ���פʰ������ʤκ������ 2006/12/01 ADD
if (isset($_REQUEST['delParts'])) {
    if (getCheckAuthority(23)) {
        $sql = "
            DELETE FROM allocated_parts WHERE plan_no='{$plan_no}' AND parts_no='{$_REQUEST['delParts']}'
        ";
        if (query_affected($sql) <= 0) {
            $_SESSION['s_sysmsg'] = "{$_REQUEST['delParts']} �κ���˼��Ԥ��ޤ�����";
        } else {
            $_SESSION['s_sysmsg'] = "{$_REQUEST['delParts']} �������ޤ�����";
        }
    } else {
        $_SESSION['s_sysmsg'] = '������븢�¤�����ޤ���';
    }
}

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
    $_SESSION['s_sysmsg'] .= "<font color='yellow'><br>����̤��Ͽ�Ǥ���</font>";
    header('Location: ' . H_WEB_HOST . $menu->out_RetUrl());    // ľ���θƽи������
    exit();
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
                        , trim(substr(midsc,1,25))
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
                ,trim(substr(midsc,1,25))
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
    ";
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
    /////////// commit �ȥ�󥶥������λ
    // query_affected_trans($con, 'commit');
    // pg_close($con); ��ɬ�פʤ�
}

$menu->out_html_header();
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=EUC-JP">
<meta http-equiv="Content-Style-Type" content="text/css">
<title><?= $menu->out_title() ?></title>
<?= $menu->out_site_java() ?>
<?= $menu->out_css() ?>

<!--    �ե��������ξ��
<script language='JavaScript' src='template.js?<?= $uniq ?>'>
</script>
-->

<script language="JavaScript">
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
            location.replace(url<?php echo "+\"{$param}\""?>);
        }
    } else {
        if (confirm(delParts + "�ϴ��˽и˺ѤߤǤ�������Ǥ������ޤ�����\n\n����������ϸ����᤻�ޤ���\n\n�������Ǥ�����")) {
            location.replace(url<?php echo "+\"{$param}\""?>);
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
<link rel='stylesheet' href='template.css?<?= $uniq ?>' type='text/css' media='screen'>
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
<?= $menu->out_title_border() ?>
        
        <!----------------- ������ ���� ���� �Υե����� ---------------->
        <table width='100%' cellspacing="0" cellpadding="0" border='0'>
            <form name='page_form' method='post' action='<?= $menu->out_self() ?>'>
                <tr>
                    <td align='left'>
                        <!--
                        <table align='left' border='3' cellspacing='0' cellpadding='0'>
                            <td align='left'>
                                <input class='pt10b' type='submit' name='backward' value='����'>
                            </td>
                        </table>
                        -->
                    </td>
                    <td nowrap align='center' class='caption_font'>
                        <?= $menu->out_caption() . "\n" ?>
                    </td>
                    <td align='right'>
                        <!--
                        <table align='right' border='3' cellspacing='0' cellpadding='0'>
                            <td align='right'>
                                <input class='pt10b' type='submit' name='forward' value='����'>
                            </td>
                        </table>
                        -->
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
                    <th class='winbox' nowrap width='10'>No</th>        <!-- �ԥʥ�С���ɽ�� -->
                <?php
                for ($i=0; $i<$num_view; $i++) {             // �ե�����ɿ�ʬ���֤�
                ?>
                    <th class='winbox' nowrap><?= $field_view[$i] ?></th>
                <?php
                }
                ?>
                </tr>
            </thead>
            <tfoot>
                <!-- ���ߤϥեå����ϲ���ʤ� -->
            </tfoot>
            <tbody>
                        <!--  bgcolor='#ffffc6' �������� -->
                        <!-- ����ץ�<td rowspan='2' colspan='3' width='200' align='center' class='pt10b' bgcolor='#ffffc6'>  </td> -->
                <?php
                for ($r=0; $r<$rows_view; $r++) {
                    // if ($parts_no == $res_view[$r][1]) {
                    if ($row_no == $r) {
                        if ($res_view[$r][4] != '-') {   // �������Υե�����ɤ�����å����ư������ʤʤ�
                            echo "<tr style='background-color:#ffffc6;' onDblClick='checkDelete(\"", $menu->out_self(), '?delParts=', urlencode($res_view[$r][1]), "\", \"{$res_view[$r][1]}\", \"{$res_view[$r][5]}\")'><a name='mark'></a>\n";
                        } else {
                            echo "<tr style='background-color:#ffffc6;'><a name='mark'></a>\n";
                        }
                    } else {
                        if ($res_view[$r][4] != '-') {   // �������Υե�����ɤ�����å����ư������ʤʤ�
                            echo "<tr onDblClick='checkDelete(\"", $menu->out_self(), '?delParts=', urlencode($res_view[$r][1]), "\", \"{$res_view[$r][1]}\", \"{$res_view[$r][5]}\")'>\n";
                        } else {
                            echo "<tr>\n";
                        }
                    }
                    echo "    <td class='winbox' nowrap style='font-size:10pt; font-weight:bold; font-family:monospace;' align='right'>\n";
                    echo "            ", ($r + $offset + 1), "\n";
                    echo "    </td>    <!-- �ԥʥ�С���ɽ�� -->\n";
                    for ($i=0; $i<$num_view; $i++) {         // �쥳���ɿ�ʬ���֤�
                        if ($res_view[$r][4] != '-') {   // �������Υե�����ɤ�����å����ư������ʤʤ�
                            switch ($i) {
                            case 0:    // ��٥�
                                echo "<td class='winbox' nowrap align='left' style='font-size:10pt; font-weight:bold; font-family:monospace;'>" . $res_view[$r][$i] . "</td>\n";
                                break;
                            case 1:     // �����ֹ�
                                if ($r != 0 && $res_view[$r][1] == $res_view[$r-1][1]) {
                                    echo "<td class='winbox' nowrap align='center' style='font-size:9pt; font-family:monospace;'>��</td>\n";
                                } else {
                                    echo "<td class='winbox' nowrap align='center' style='font-size:9pt; font-family:monospace;'><a href='", $menu->out_action('�߸˷���'), "?parts_no=", urlencode($res_view[$r][$i]), "&plan_no=", urlencode($plan_no), "&material=1&row={$r}' target='application' style='text-decoration:none;'>{$res_view[$r][$i]}</a></td>\n";
                                }
                                break;
                            case 2:     // ����̾
                                if ($r != 0 && $res_view[$r][1] == $res_view[$r-1][1]) {
                                    echo "<td class='winbox' nowrap width='240' align='left' style='font-size:9pt; font-family:monospace;'>��</td>\n";
                                } else {
                                    echo "<td class='winbox' nowrap width='240' align='left' style='font-size:9pt; font-family:monospace;'>{$res_view[$r][$i]}</td>\n";
                                }
                                break;
                            case  3:    // ���ѿ�
                                echo "<td class='winbox' nowrap align='right' style='font-size:9pt; font-family:monospace;'>" . number_format($res_view[$r][$i], 4) . "</td>\n";
                                break;
                            case  4:    // ������
                            case  5:    // �и��߷�
                            case  6:    // �и˻�
                                echo "<td class='winbox' nowrap align='right' style='font-size:9pt; font-family:monospace;'>" . number_format($res_view[$r][$i], 0) . "</td>\n";
                                break;
                            case  8:    // ͭ��ñ��
                            case  9:    // ͭ�����
                                echo "<td class='winbox' nowrap align='right' style='font-size:9pt; font-family:monospace;'>" . number_format($res_view[$r][$i], 2) . "</td>\n";
                                break;
                            default:    // 7 �ٵ���
                                echo "<td class='winbox' nowrap align='center' style='font-size:9pt; font-family:monospace;'>{$res_view[$r][$i]}</td>\n";
                            }
                        } else {            // ��¤������ɽ����������������ʤʤ�
                            switch ($i) {
                            case 0:    // ��٥�
                                echo "<td class='winbox' nowrap align='left' style='font-size:10pt; font-weight:bold; font-family:monospace;'>" . $res_view[$r][$i] . "</td>\n";
                                break;
                            case 1:     // �����ֹ�
                                if ($r != 0 && $res_view[$r][1] == $res_view[$r-1][1]) {
                                    echo "<td class='winbox' bgcolor='#e6e6e6' nowrap align='center' style='font-size:9pt; font-family:monospace;'>��</td>\n";
                                } else {
                                    echo "<td class='winbox' bgcolor='#e6e6e6' nowrap align='center' style='font-size:9pt; font-family:monospace;'><a href='", $menu->out_action('�߸˷���'), "?parts_no=", urlencode($res_view[$r][$i]), "&plan_no=", urlencode($plan_no), "&material=1&row={$r}' target='application' style='text-decoration:none;'>{$res_view[$r][$i]}</a></td>\n";
                                }
                                break;
                            case 2:     // ����̾
                                if ($r != 0 && $res_view[$r][1] == $res_view[$r-1][1]) {
                                    echo "<td class='winbox' bgcolor='#e6e6e6' nowrap width='240' align='left' style='font-size:9pt; font-family:monospace;'>��</td>\n";
                                } else {
                                    echo "<td class='winbox' bgcolor='#e6e6e6' nowrap width='240' align='left' style='font-size:9pt; font-family:monospace;'>{$res_view[$r][$i]}</td>\n";
                                }
                                break;
                            case  3:    // ���ѿ�
                                echo "<td class='winbox' bgcolor='#e6e6e6' nowrap align='right' style='font-size:9pt; font-family:monospace;'>" . number_format($res_view[$r][$i], 4) . "</td>\n";
                                break;
                            case  4:    // ������
                            case  5:    // �и��߷�
                            case  6:    // �и˻�
                                echo "<td class='winbox' bgcolor='#e6e6e6' nowrap align='right' style='font-size:9pt; font-family:monospace;'>" . number_format($res_view[$r][$i], 0) . "</td>\n";
                                break;
                            case  8:    // ͭ��ñ��
                            case  9:    // ͭ�����
                                echo "<td class='winbox' bgcolor='#e6e6e6' nowrap align='right' style='font-size:9pt; font-family:monospace;'>" . number_format($res_view[$r][$i], 2) . "</td>\n";
                                break;
                            default:    // 7 �ٵ���
                                echo "<td class='winbox' bgcolor='#e6e6e6' nowrap align='center' style='font-size:9pt; font-family:monospace;'>{$res_view[$r][$i]}</td>\n";
                            }
                        }
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
<?=$menu->out_alert_java()?>
</html>
<?php
ob_end_flush();                 // ���ϥХåե���gzip���� END
?>
