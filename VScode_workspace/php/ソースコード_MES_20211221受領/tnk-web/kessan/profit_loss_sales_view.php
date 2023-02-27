<?php
//////////////////////////////////////////////////////////////////////////////
// ��� ���� �Ȳ�  profit_loss_sales_view.php                               //
// Copyright (C) 2018-2018 Norihisa.Ohya norihisa_ooya@nitto-kohki.co.jp    //
// Changed history                                                          //
// 2018/03/30 Created   profit_loss_sales_view.php��sales_view.php���ѡ�    //
//////////////////////////////////////////////////////////////////////////////
// ini_set('error_reporting', E_ALL);          // E_ALL='2047' debug ��
// ini_set('display_errors', '1');             // Error ɽ�� ON debug �� ��꡼���女����
// ini_set('zend.ze1_compatibility_mode', '1');    // zend 1.X ����ѥ� php4�θߴ��⡼��
// ini_set('implicit_flush', 'off');           // echo print �� flush �����ʤ�(�٤��ʤ뤿��) CLI CGI��
// ini_set('max_execution_time', 1200);        // ����¹Ի���=20ʬ CLI CGI��
ob_start('ob_gzhandler');                   // ���ϥХåե���gzip����
session_start();                            // ini_set()�μ��˻��ꤹ�뤳�� Script �Ǿ��

require_once ('../function.php');            // define.php �� pgsql.php �� require_once ���Ƥ���
require_once ('../tnk_func.php');            // TNK �˰�¸������ʬ�δؿ��� require_once ���Ƥ���
require_once ('../MenuHeader.php');          // TNK ������ menu class
require_once ('../ControllerHTTP_Class.php');// TNK ������ MVC Controller Class
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
///// ������μ���
$ki = Ym_to_tnk($_SESSION['pl_ym']);
$tuki = substr($_SESSION['pl_ym'],4,2);
///// �о�����
$yyyymm = $_SESSION['pl_ym'];
///// �о�����
if (substr($yyyymm,4,2)!=01) {
    $p1_ym = $yyyymm - 1;
} else {
    $p1_ym = $yyyymm - 100;
    $p1_ym = $p1_ym + 11;
}
///// �о�������
if (substr($p1_ym,4,2)!=01) {
    $p2_ym = $p1_ym - 1;
} else {
    $p2_ym = $p1_ym - 100;
    $p2_ym = $p2_ym + 11;
}
///// ����ǯ��λ���
$yyyy = substr($yyyymm, 0,4);
$mm   = substr($yyyymm, 4,2);
if (($mm >= 1) && ($mm <= 3)) {
    $yyyy = ($yyyy - 1);
}
$str_ym = $yyyy . "04";     // ����ǯ��

///// ���շ׻�
$str_ymd = $yyyymm . "01";
$end_ymd = $yyyymm . "99";

$menu->set_title("��{$ki}����{$tuki}���١��� �� �� �� �� ��");

//////////// �ƽ����action̾�ȥ��ɥ쥹����
$menu->set_action('�������Ȳ�',   INDUST . 'material/materialCost_view.php');
$menu->set_action('ñ����Ͽ�Ȳ�',   INDUST . 'parts/parts_cost_view.php');
$menu->set_action('�����������',   INDUST . 'material/materialCost_view_assy.php');

//////////// �֥饦�����Υ���å����к���
$uniq = $menu->set_useNotCache('target');

$current_script  = $_SERVER['PHP_SELF'];    // ���߼¹���Υ�����ץ�̾����¸

$div        = " ";
$d_start    = $str_ymd;
$d_end      = $end_ymd;
$kubun      = "";
///////////// ��׶�ۡ�����������
if ( ($div != 'S') && ($div != 'D') ) {      // �������ɸ�� �ʳ��ʤ�
    $query = "select
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
$search = "where �׾���>=$d_start and �׾���<=$d_end";
/*
if ($div == 'S') {    // ������ʤ�
    $search .= " and ������='C' and note15 like 'SC%%'";
    $search .= " and (assyno not like 'NKB%%')";
    $search .= " and (assyno not like 'SS%%')";
    $search .= " and CASE WHEN �׾���<20130501 THEN groupm.support_group_code IS NULL ELSE ������='C' END";
    //$search .= " and groupm.support_group_code IS NULL";
} elseif ($div == 'D') {    // ��ɸ��ʤ�
    $search .= " and ������='C' and (note15 NOT like 'SC%%' OR note15 IS NULL)";    // ��������ɸ��ؤ���
    $search .= " and (assyno not like 'NKB%%')";
    $search .= " and (assyno not like 'SS%%')";
    $search .= " and (CASE WHEN �׾���<20130501 THEN groupm.support_group_code IS NULL ELSE ������='C' END)";
    //$search .= " and groupm.support_group_code IS NULL";
} elseif ($div == "N") {    // ��˥��ΥХ���롦���������� assyno �ǥ����å�
    $search .= " and ������='L' and (assyno NOT like 'LC%%' AND assyno NOT like 'LR%%')";
    $search .= " and (assyno not like 'SS%%')";
    $search .= " and CASE WHEN assyno = '' THEN ������='L' ELSE CASE WHEN m.midsc IS NULL THEN ������='L' ELSE m.midsc not like 'DPE%%' END END";
    $search .= " and CASE WHEN �׾���<20130501 THEN groupm.support_group_code IS NULL ELSE ������='L' END";
    //$search .= " and groupm.support_group_code IS NULL";
} elseif ($div == "B") {    // �Х����ξ��� assyno �ǥ����å�
    //$search .= " and (assyno like 'LC%%' or assyno like 'LR%%')";
    $search .= " and (assyno like 'LC%%' or assyno like 'LR%%' or m.midsc like 'DPE%%')";
    $search .= " and CASE WHEN �׾���<20130501 THEN groupm.support_group_code IS NULL ELSE ������='L' END";
    //$search .= " and groupm.support_group_code IS NULL";
} elseif ($div == "SSC") {   // ���ץ��������ξ��� assyno �ǥ����å�
    $search .= " and ������='C' and (assyno like 'SS%%')";
} elseif ($div == "SSL") {   // ��˥���������ξ��� assyno �ǥ����å�
    $search .= " and ������='L' and (assyno like 'SS%%')";
} elseif ($div == "NKB") {  // ���ʴ����ξ��� assyno �ǥ����å�
    $search .= " and (assyno like 'NKB%%')";
} elseif ($div == "TRI") {  // ���ξ��ϻ�����������ʬ����ɼ�ֹ�ǥ����å�
    $search .= " and ������='C'";
    $search .= " and ( datatype='3' or datatype='7' )";
    $search .= " and ��ɼ�ֹ�='00222'";
} elseif ($div == "NKCT") { // NKCT�ξ��ϻٱ��襳����(1)�ǥ����å�
    $search .= " and CASE WHEN �׾���<20130501 THEN groupm.support_group_code=1 END";
    //$search .= " and groupm.support_group_code=1";
} elseif ($div == "NKT") {  // NKT�ξ��ϻٱ��襳����(2)�ǥ����å�
    $search .= " and CASE WHEN �׾���<20130501 THEN groupm.support_group_code=2 END";
    //$search .= " and groupm.support_group_code=2";
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
*/
$query = sprintf("$query %s", $search);     // SQL query ʸ�δ���

//////////// ɽ�����Υǡ���ɽ���ѤΥ���ץ� Query & �����
// ���׶�ۤμ���
$s_div       = array('C','L','T','SSL','NKB','');
$sdiv_num    = count($s_div);
$s_kingaku   = array();
$s_kingaku_t = array();
for ($r=0; $r<$sdiv_num; $r++) {   // ���������Ȥ˼���
    $s_kingaku_t[$r] = 0;
    for ($i=1; $i<10; $i++) {   // ����ʬ�������ޤǤ����
        if ($s_div[$r] == "C") {
            $search  = " and ������='$s_div[$r]'";
            $search .= " and (assyno not like 'NKB%%')";
            $search .= " and (assyno not like 'SS%%')";
            $search .= " and datatype='$i'";
        } elseif ($s_div[$r] == "L") {
            $search  = " and ������='$s_div[$r]'";
            $search .= " and (assyno not like 'SS%%')";
            $search .= " and datatype='$i'";
        } elseif ($s_div[$r] == "SSL") {   // ��˥���������ξ��� assyno �ǥ����å�
            $search  = " and ������='L' and (assyno like 'SS%%')";
            $search .= " and datatype='$i'";
        } elseif ($s_div[$r] == "NKB") {  // ���ʴ����ξ��� assyno �ǥ����å�
            $search  = " and (assyno like 'NKB%%')";
            $search .= " and datatype='$i'";
        } elseif ($s_div[$r] == "T") {
            $search  = " and ������='T'";
            $search .= " and datatype='$i'";
        } else {
            $search  = " and datatype='$i'";
        }
        $query_s  = sprintf("$query %s", $search);     // SQL query ʸ�δ���
        $res_syu  = array();
        if (getResult($query_s, $res_syu) <= 0) {
            $s_kingaku[$r][$i] = 0;
        } else {
            $s_kingaku[$r][$i] = $res_syu[0]['t_kingaku'];
            $s_kingaku_t[$r]  += $s_kingaku[$r][$i];
        }
    }
}
$item = array();
$item[0]   = "���������ץ齤��";
$item[1]   = "����������Ĵ��";
$item[2]   = "����������Ĵ��";
$item[3]   = "���������ץ���ɸ";
$item[4]   = "��������˥���ɸ";
$item[5]   = "�������ġ�����ɸ";
$item[6]   = "���������ɸ";
$item[7]   = "������������ɸ";
$item[8]   = "������������ɸ";
///////// ����text �ѿ� �����
$invent = array();
for ($i = 0; $i < 9; $i++) {
    if (isset($_POST['invent'][$i])) {
        $invent[$i] = $_POST['invent'][$i];
    } else {
        $invent[$i] = 0;
    }
}
if (!isset($_POST['entry'])) {     // �ǡ�������
    ////////// ��Ͽ�Ѥߤʤ�ж�ۼ���
    for ($i = 0; $i < 9; $i++) {
        $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='%s'", $yyyymm, $item[$i]);
        $res = array();
        if (getResult2($query,$res) > 0) {
            $invent[$i] = $res[0][0];
        }
    }
} else {
    // ������ɸ�׻�
    $invent[8] = $invent[3] + $invent[4] + $invent[5] + $invent[6] + $invent[7];
    for ($i = 0; $i < 9; $i++) {
        $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='%s'", $yyyymm, $item[$i]);
        $res = array();
        if (getResult2($query,$res) <= 0) {
            /////////// begin �ȥ�󥶥�����󳫻�
            if ($con = db_connect()) {
                query_affected_trans($con, "begin");
            } else {
                $_SESSION["s_sysmsg"] .= "�ǡ����١�������³�Ǥ��ޤ���";
                header("Location: $current_script");
                exit();
            }
            ////////// Insert Start
            $query = sprintf("insert into act_pl_history (pl_bs_ym, kin, note) values (%d, %d, '%s')", $yyyymm, $invent[$i], $item[$i]);
            if (query_affected_trans($con, $query) <= 0) {
                $_SESSION["s_sysmsg"] .= sprintf("%s�ο�����Ͽ�˼���<br>�� %d�� %d��", $item[$i], $ki, $tuki);
                query_affected_trans($con, "rollback");     // transaction rollback
                header("Location: $current_script");
                exit();
            }
            /////////// commit �ȥ�󥶥������λ
            query_affected_trans($con, "commit");
            $_SESSION["s_sysmsg"] = sprintf("<font color='yellow'>��%d�� %d�� �������Ȳ�ǡ��� ���� ��Ͽ��λ</font>",$ki,$tuki);
        } else {
            /////////// begin �ȥ�󥶥�����󳫻�
            if ($con = db_connect()) {
                query_affected_trans($con, "begin");
            } else {
                $_SESSION["s_sysmsg"] .= "�ǡ����١�������³�Ǥ��ޤ���";
                header("Location: $current_script");
                exit();
            }
            ////////// UPDATE Start
            $query = sprintf("update act_pl_history set kin=%d where pl_bs_ym=%d and note='%s'", $invent[$i], $yyyymm, $item[$i]);
            if (query_affected_trans($con, $query) <= 0) {
                $_SESSION["s_sysmsg"] .= sprintf("%s��UPDATE�˼���<br>�� %d�� %d��", $item[$i], $ki, $tuki);
                query_affected_trans($con, "rollback");     // transaction rollback
                header("Location: $current_script");
                exit();
            }
            /////////// commit �ȥ�󥶥������λ
            query_affected_trans($con, "commit");
            $_SESSION["s_sysmsg"] = sprintf("<font color='yellow'>��%d�� %d�� �������Ȳ�ǡ��� �ѹ� ��λ</font>",$ki,$tuki);
        }
    }
}

// ���ǡ����׻�
// �¼��� ���ǡݽ���
$c_jitsute = $s_kingaku[0][3] - $invent[0];
$a_jitsute = $c_jitsute;
// ����¾��� ���ץ顧���� �� �¼��� �� Ĵ����LT�ϼ¼��Ǥ����Ǥˡ�
$c_sonota  = $s_kingaku[0][2] + $c_jitsute - $s_kingaku[0][4];
$l_sonota  = $s_kingaku[1][2] + $s_kingaku[1][3] - $s_kingaku[1][4];
$t_sonota  = $s_kingaku[2][2] + $s_kingaku[2][3] - $s_kingaku[2][4];
$a_sonota  = $c_sonota + $l_sonota + $t_sonota;
// ������� ��ư������ι��
$c_buhin   = $s_kingaku[0][5] + $s_kingaku[0][6] + $s_kingaku[0][7] + $s_kingaku[0][8] + $s_kingaku[0][9];
$l_buhin   = $s_kingaku[1][5] + $s_kingaku[1][6] + $s_kingaku[1][7] + $s_kingaku[1][8] + $s_kingaku[1][9];
$t_buhin   = $s_kingaku[2][5] + $s_kingaku[2][6] + $s_kingaku[2][7] + $s_kingaku[2][8] + $s_kingaku[2][9];
$a_buhin   = $c_buhin + $l_buhin + $t_buhin;
// �����
$c_souuri  = $s_kingaku_t[0] - $invent[0];
$l_souuri  = $s_kingaku_t[1] - $invent[1];
$t_souuri  = $s_kingaku_t[2];
$s_souuri  = $s_kingaku_t[3] + $invent[0] + $invent[1];
$b_souuri  = $s_kingaku_t[4] + $invent[2];
$a_souuri  = $c_souuri + $l_souuri + $t_souuri + $s_souuri + $b_souuri;

// ���ñ��
// �������
$c_buhin_s = $c_buhin / 1000;
$l_buhin_s = $l_buhin / 1000;
$t_buhin_s = $t_buhin / 1000;
$a_buhin_s = $a_buhin / 1000;
// ����¾���
$c_sonota_s = $c_sonota / 1000;
$l_sonota_s = $l_sonota / 1000;
$t_sonota_s = $t_sonota / 1000;
$a_sonota_s = $a_sonota / 1000;
// �����ʡʥ��ץ�Τߡ�
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
                    product_support_master AS groupm
              on assyno=groupm.assy_no
              left outer join
                    miitem as m
              on assyno=m.mipn";
$search = "where �׾���>=$d_start and �׾���<=$d_end";
$search .= " and ������='C' and note15 like 'SC%%'";
$search .= " and (assyno not like 'NKB%%')";
$search .= " and (assyno not like 'SS%%')";
$search .= " and CASE WHEN �׾���<20130501 THEN groupm.support_group_code IS NULL ELSE ������='C' END";
$query_s  = sprintf("$query %s", $search);     // SQL query ʸ�δ���
$res_toku  = array();
if (getResult($query_s, $res_toku) <= 0) {
    $c_toku   = 0;
    $c_toku_s = 0;
} else {
    $c_toku   = $res_toku[0]['t_kingaku'];
    $c_toku_s = $c_toku / 1000;
}
// ɸ��ʤ��줾�����Τ����������ʤ���¾�������
$c_hyo = $c_souuri - $c_toku - $c_buhin - $c_sonota;
$l_hyo = $l_souuri - $l_buhin - $l_sonota;
$t_hyo = $t_souuri - $t_buhin - $t_sonota;
$s_hyo = $s_souuri;
$b_hyo = $b_souuri;

$c_hyo_s = $c_hyo / 1000;
$l_hyo_s = $l_hyo / 1000;
$t_hyo_s = $t_hyo / 1000;
$s_hyo_s = $s_hyo / 1000;
$b_hyo_s = $b_hyo / 1000;

// ���ʷ� �����ɸ��
$c_sei_t = $c_toku_s + $c_hyo_s;
$l_sei_t = $l_hyo_s;
$t_sei_t = $t_hyo_s;

// ���ʷ� ���ʡܤ���¾
$c_buhin_t = $c_buhin_s + $c_sonota_s;
$l_buhin_t = $l_buhin_s + $l_sonota_s;
$t_buhin_t = $t_buhin_s + $t_sonota_s;

// ���� ������ñ�����
$c_jisseki = $c_souuri / 1000;
$l_jisseki = $l_souuri / 1000;
$t_jisseki = $t_souuri / 1000;
$s_jisseki = $s_souuri / 1000;
$b_jisseki = $b_souuri / 1000;
$a_jisseki = $a_souuri / 1000;

// ã��Ψ ���� �� ��ɸ
if ($invent[3] <> 0) {
    $c_ritsu = $c_jisseki / $invent[3] * 100;
} else {
    $c_ritsu = 0;
}
if ($invent[4] <> 0) {
    $l_ritsu = $l_jisseki / $invent[4] * 100;
} else {
    $l_ritsu = 0;
}
if ($invent[5] <> 0) {
    $t_ritsu = $t_jisseki / $invent[5] * 100;
} else {
    $t_ritsu = 0;
}
if ($invent[6] <> 0) {
    $s_ritsu = $s_jisseki / $invent[6] * 100;
} else {
    $s_ritsu = 0;
}
if ($invent[7] <> 0) {
    $b_ritsu = $b_jisseki / $invent[7] * 100;
} else {
    $b_ritsu = 0;
}
if ($invent[8] <> 0) {
    $a_ritsu = $a_jisseki / $invent[8] * 100;
} else {
    $a_ritsu = 0;
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
.winboxy {
    border-style: solid;
    border-width: 1px;
    border-top-color:       #FFFFFF;
    border-left-color:      #FFFFFF;
    border-right-color:     #bdaa90;
    border-bottom-color:    #bdaa90;
    background-color:   yellow;
    color:              blue;
    font-size:          12pt;
    font-weight:        bold;
    font-family:        monospace;
}
.right{
    text-align:right;
    font:bold 12pt;
    font-family: monospace;
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
<body onLoad='set_focus()'>
    <center>
<?php echo $menu->out_title_border()?>
        <BR>
        <!--------------- ����������ʸ��ɽ��ɽ������ -------------------->
        <form name='invent' action='<?php echo $menu->out_self() ?>' method='post'>
        <table bgcolor='#d6d3ce' align='center' border='1' cellspacing='0' cellpadding='3'>
            <tr><td> <!----------- ���ߡ�(�ǥ�������) ------------>
        <table class='winbox_field' width='100%' bgcolor='#d6d3ce' align='center' border='1' cellspacing='0' cellpadding='3'>
            <div class='pt11b' align='right'><ñ�̡���></div>
            <thead>
                <!-- �ơ��֥� �إå�����ɽ�� -->
                <tr>
                    <th class='winbox' nowrap>����ʬ</th>
                    <th class='winbox' nowrap>���ץ�</th>
                    <th class='winbox' nowrap>��˥�</th>
                    <th class='winbox' nowrap>�ġ���</th>
                    <th class='winbox' nowrap>�����</th>
                    <th class='winbox' nowrap>���ʴ���</th>
                    <th class='winbox' nowrap>���</th>
                </tr>
            </thead>
            <tfoot>
                <!-- ���ߤϥեå����ϲ���ʤ� -->
            </tfoot>
            <tbody>
                <tr>
                    <th class='winbox' nowrap>����</th>
                    <?php
                    echo "<td class='winbox' nowrap align='right'><div class='pt10'>" . number_format($s_kingaku[0][1], 0) . "</div></td>\n";
                    echo "<td class='winbox' nowrap align='right'><div class='pt10'>" . number_format($s_kingaku[1][1], 0) . "</div></td>\n";
                    echo "<td class='winbox' nowrap align='right'><div class='pt10'>" . number_format($s_kingaku[2][1], 0) . "</div></td>\n";
                    echo "<td class='winbox' nowrap align='right'><div class='pt10'>" . number_format($s_kingaku[3][1], 0) . "</div></td>\n";
                    echo "<td class='winbox' nowrap align='right'><div class='pt10'>" . number_format($s_kingaku[4][1], 0) . "</div></td>\n";
                    echo "<td class='winbox' nowrap align='right'><div class='pt10'>" . number_format($s_kingaku[5][1], 0) . "</div></td>\n";
                    ?>
                </tr>
                <tr>
                    <th class='winbox' nowrap>����</th>
                    <?php
                    echo "<td class='winbox' nowrap align='right'><div class='pt10'>" . number_format($s_kingaku[0][2], 0) . "</div></td>\n";
                    echo "<td class='winbox' nowrap align='right'><div class='pt10'>" . number_format($s_kingaku[1][2], 0) . "</div></td>\n";
                    echo "<td class='winbox' nowrap align='right'><div class='pt10'>" . number_format($s_kingaku[2][2], 0) . "</div></td>\n";
                    echo "<td class='winbox' nowrap align='right'><div class='pt10'>" . number_format($s_kingaku[3][2], 0) . "</div></td>\n";
                    echo "<td class='winbox' nowrap align='right'><div class='pt10'>" . number_format($s_kingaku[4][2], 0) . "</div></td>\n";
                    echo "<td class='winbox' nowrap align='right'><div class='pt10'>" . number_format($s_kingaku[5][2], 0) . "</div></td>\n";
                    ?>
                </tr>
                <tr>
                    <th class='winbox' nowrap>����</th>
                    <?php
                    echo "<td class='winbox' nowrap align='right'><div class='pt10'>" . number_format($s_kingaku[0][3], 0) . "</div></td>\n";
                    echo "<td class='winbox' nowrap align='right'><div class='pt10'>" . number_format($s_kingaku[1][3], 0) . "</div></td>\n";
                    echo "<td class='winbox' nowrap align='right'><div class='pt10'>" . number_format($s_kingaku[2][3], 0) . "</div></td>\n";
                    echo "<td class='winbox' nowrap align='right'><div class='pt10'>" . number_format($s_kingaku[3][3], 0) . "</div></td>\n";
                    echo "<td class='winbox' nowrap align='right'><div class='pt10'>" . number_format($s_kingaku[4][3], 0) . "</div></td>\n";
                    echo "<td class='winbox' nowrap align='right'><div class='pt10'>" . number_format($s_kingaku[5][3], 0) . "</div></td>\n";
                    ?>
                </tr>
                <tr>
                    <th class='winbox' nowrap>Ĵ��</th>
                    <?php
                    echo "<td class='winbox' nowrap align='right'><div class='pt10'>" . number_format($s_kingaku[0][4], 0) . "</div></td>\n";
                    echo "<td class='winbox' nowrap align='right'><div class='pt10'>" . number_format($s_kingaku[1][4], 0) . "</div></td>\n";
                    echo "<td class='winbox' nowrap align='right'><div class='pt10'>" . number_format($s_kingaku[2][4], 0) . "</div></td>\n";
                    echo "<td class='winbox' nowrap align='right'><div class='pt10'>" . number_format($s_kingaku[3][4], 0) . "</div></td>\n";
                    echo "<td class='winbox' nowrap align='right'><div class='pt10'>" . number_format($s_kingaku[4][4], 0) . "</div></td>\n";
                    echo "<td class='winbox' nowrap align='right'><div class='pt10'>" . number_format($s_kingaku[5][4], 0) . "</div></td>\n";
                    ?>
                </tr>
                <tr>
                    <th class='winbox' nowrap>��ư</th>
                    <?php
                    echo "<td class='winbox' nowrap align='right'><div class='pt10'>" . number_format($s_kingaku[0][5], 0) . "</div></td>\n";
                    echo "<td class='winbox' nowrap align='right'><div class='pt10'>" . number_format($s_kingaku[1][5], 0) . "</div></td>\n";
                    echo "<td class='winbox' nowrap align='right'><div class='pt10'>" . number_format($s_kingaku[2][5], 0) . "</div></td>\n";
                    echo "<td class='winbox' nowrap align='right'><div class='pt10'>" . number_format($s_kingaku[3][5], 0) . "</div></td>\n";
                    echo "<td class='winbox' nowrap align='right'><div class='pt10'>" . number_format($s_kingaku[4][5], 0) . "</div></td>\n";
                    echo "<td class='winbox' nowrap align='right'><div class='pt10'>" . number_format($s_kingaku[5][5], 0) . "</div></td>\n";
                    ?>
                </tr>
                <tr>
                    <th class='winbox' nowrap>ľǼ</th>
                    <?php
                    echo "<td class='winbox' nowrap align='right'><div class='pt10'>" . number_format($s_kingaku[0][6], 0) . "</div></td>\n";
                    echo "<td class='winbox' nowrap align='right'><div class='pt10'>" . number_format($s_kingaku[1][6], 0) . "</div></td>\n";
                    echo "<td class='winbox' nowrap align='right'><div class='pt10'>" . number_format($s_kingaku[2][6], 0) . "</div></td>\n";
                    echo "<td class='winbox' nowrap align='right'><div class='pt10'>" . number_format($s_kingaku[3][6], 0) . "</div></td>\n";
                    echo "<td class='winbox' nowrap align='right'><div class='pt10'>" . number_format($s_kingaku[4][6], 0) . "</div></td>\n";
                    echo "<td class='winbox' nowrap align='right'><div class='pt10'>" . number_format($s_kingaku[5][6], 0) . "</div></td>\n";
                    ?>
                </tr>
                <tr>
                    <th class='winbox' nowrap>���</th>
                    <?php
                    echo "<td class='winbox' nowrap align='right'><div class='pt10'>" . number_format($s_kingaku[0][7], 0) . "</div></td>\n";
                    echo "<td class='winbox' nowrap align='right'><div class='pt10'>" . number_format($s_kingaku[1][7], 0) . "</div></td>\n";
                    echo "<td class='winbox' nowrap align='right'><div class='pt10'>" . number_format($s_kingaku[2][7], 0) . "</div></td>\n";
                    echo "<td class='winbox' nowrap align='right'><div class='pt10'>" . number_format($s_kingaku[3][7], 0) . "</div></td>\n";
                    echo "<td class='winbox' nowrap align='right'><div class='pt10'>" . number_format($s_kingaku[4][7], 0) . "</div></td>\n";
                    echo "<td class='winbox' nowrap align='right'><div class='pt10'>" . number_format($s_kingaku[5][7], 0) . "</div></td>\n";
                    ?>
                </tr>
                <tr>
                    <th class='winbox' nowrap>����</th>
                    <?php
                    echo "<td class='winbox' nowrap align='right'><div class='pt10'>" . number_format($s_kingaku[0][8], 0) . "</div></td>\n";
                    echo "<td class='winbox' nowrap align='right'><div class='pt10'>" . number_format($s_kingaku[1][8], 0) . "</div></td>\n";
                    echo "<td class='winbox' nowrap align='right'><div class='pt10'>" . number_format($s_kingaku[2][8], 0) . "</div></td>\n";
                    echo "<td class='winbox' nowrap align='right'><div class='pt10'>" . number_format($s_kingaku[3][8], 0) . "</div></td>\n";
                    echo "<td class='winbox' nowrap align='right'><div class='pt10'>" . number_format($s_kingaku[4][8], 0) . "</div></td>\n";
                    echo "<td class='winbox' nowrap align='right'><div class='pt10'>" . number_format($s_kingaku[5][8], 0) . "</div></td>\n";
                    ?>
                </tr>
                <tr>
                    <th class='winbox' nowrap>����</th>
                    <?php
                    echo "<td class='winbox' nowrap align='right'><div class='pt10'>" . number_format($s_kingaku[0][9], 0) . "</div></td>\n";
                    echo "<td class='winbox' nowrap align='right'><div class='pt10'>" . number_format($s_kingaku[1][9], 0) . "</div></td>\n";
                    echo "<td class='winbox' nowrap align='right'><div class='pt10'>" . number_format($s_kingaku[2][9], 0) . "</div></td>\n";
                    echo "<td class='winbox' nowrap align='right'><div class='pt10'>" . number_format($s_kingaku[3][9], 0) . "</div></td>\n";
                    echo "<td class='winbox' nowrap align='right'><div class='pt10'>" . number_format($s_kingaku[4][9], 0) . "</div></td>\n";
                    echo "<td class='winbox' nowrap align='right'><div class='pt10'>" . number_format($s_kingaku[5][9], 0) . "</div></td>\n";
                    ?>
                </tr>
                <tr>
                    <td class='winboxy' nowrap align='center'>���</th>
                    <?php
                    echo "<td class='winboxy' nowrap align='right'>" . number_format($s_kingaku_t[0], 0) . "</td>\n";
                    echo "<td class='winboxy' nowrap align='right'>" . number_format($s_kingaku_t[1], 0) . "</td>\n";
                    echo "<td class='winboxy' nowrap align='right'>" . number_format($s_kingaku_t[2], 0) . "</td>\n";
                    echo "<td class='winboxy' nowrap align='right'>" . number_format($s_kingaku_t[3], 0) . "</td>\n";
                    echo "<td class='winboxy' nowrap align='right'>" . number_format($s_kingaku_t[4], 0) . "</td>\n";
                    echo "<td class='winboxy' nowrap align='right'>" . number_format($s_kingaku_t[5], 0) . "</td>\n";
                    ?>
                </tr>
            </tbody>
        </table>
            </td></tr>
        </table> <!----------------- ���ߡ�End ------------------>
        <BR>
        <table bgcolor='#d6d3ce' align='center' border='1' cellspacing='0' cellpadding='3'>
            <tr><td> <!----------- ���ߡ�(�ǥ�������) ------------>
        <table class='winbox_field' width='100%' bgcolor='#d6d3ce' align='center' border='1' cellspacing='0' cellpadding='3'>
            <div class='pt11b' align='right'><ñ�̡���></div>
            <thead>
                <!-- �ơ��֥� �إå�����ɽ�� -->
                <tr>
                    <th class='winbox' nowrap>�ý���</th>
                    <th class='winbox' nowrap>����Ĵ��</th>
                    <th class='winbox' nowrap>����Ĵ��</th>
                    <th class='winbox' nowrap>��</th>
                </tr>
            </thead>
            <tfoot>
                <!-- ���ߤϥեå����ϲ���ʤ� -->
            </tfoot>
            <tbody>
                
                <tr>
                    <td align='center' bgcolor='white'>
                        <input type='text' name='invent[]' size='12' maxlength='11' value='<?php echo $invent[0] ?>' class='right'>
                    </td>
                    <td align='center' bgcolor='white'>
                        <input type='text' name='invent[]' size='12' maxlength='11' value='<?php echo $invent[1] ?>' class='right'>
                    </td>
                    <td align='center' bgcolor='white'>
                        <input type='text' name='invent[]' size='12' maxlength='11' value='<?php echo $invent[2] ?>' class='right'>
                    </td>
                    <td colspan='4' align='center'>
                        <input type='submit' name='entry' value='��Ͽ' >
                    </td>
                </tr>
            </tbody>
        </table>
            </td></tr>
        </table> <!----------------- ���ߡ�End ------------------>
        <BR>
        <table bgcolor='#d6d3ce' align='center' border='1' cellspacing='0' cellpadding='3'>
            <tr><td> <!----------- ���ߡ�(�ǥ�������) ------------>
        <table class='winbox_field' width='100%' bgcolor='#d6d3ce' align='center' border='1' cellspacing='0' cellpadding='3'>
            <div class='pt11b' align='right'><ñ�̡���></div>
            <thead>
                <!-- �ơ��֥� �إå�����ɽ�� -->
                <tr>
                    <th class='winbox' nowrap>��</th>
                    <th class='winbox' nowrap>���ץ�</th>
                    <th class='winbox' nowrap>��˥�</th>
                    <th class='winbox' nowrap>�ġ���</th>
                    <th class='winbox' nowrap>�����</th>
                    <th class='winbox' nowrap>���ʴ���</th>
                    <th class='winbox' nowrap>���</th>
                </tr>
            </thead>
            <tfoot>
                <!-- ���ߤϥեå����ϲ���ʤ� -->
            </tfoot>
            <tbody>
                <tr>
                    <th class='winbox' nowrap>�¼���</th>
                    <?php
                    echo "<td class='winbox' nowrap align='right'><div class='pt10'>" . number_format($c_jitsute, 0) . "</div></td>\n";
                    ?>
                    <td class='winbox' nowrap align='right'><div class='pt10'>��</div></td>
                    <td class='winbox' nowrap align='right'><div class='pt10'>��</div></td>
                    <td class='winbox' nowrap align='right'><div class='pt10'>��</div></td>
                    <td class='winbox' nowrap align='right'><div class='pt10'>��</div></td>
                    <?php
                    echo "<td class='winbox' nowrap align='right'><div class='pt10'>" . number_format($a_jitsute, 0) . "</div></td>\n";
                    ?>
                </tr>
                <tr>
                    <th class='winbox' nowrap>����¾���</th>
                    <?php
                    echo "<td class='winbox' nowrap align='right'><div class='pt10'>" . number_format($c_sonota, 0) . "</div></td>\n";
                    echo "<td class='winbox' nowrap align='right'><div class='pt10'>" . number_format($l_sonota, 0) . "</div></td>\n";
                    echo "<td class='winbox' nowrap align='right'><div class='pt10'>" . number_format($t_sonota, 0) . "</div></td>\n";
                    ?>
                    <td class='winbox' nowrap align='right'><div class='pt10'>��</div></td>
                    <td class='winbox' nowrap align='right'><div class='pt10'>��</div></td>
                    <?php
                    echo "<td class='winbox' nowrap align='right'><div class='pt10'>" . number_format($a_sonota, 0) . "</div></td>\n";
                    ?>
                </tr>
                <tr>
                    <th class='winbox' nowrap>�������</th>
                    <?php
                    echo "<td class='winbox' nowrap align='right'><div class='pt10'>" . number_format($c_buhin, 0) . "</div></td>\n";
                    echo "<td class='winbox' nowrap align='right'><div class='pt10'>" . number_format($l_buhin, 0) . "</div></td>\n";
                    echo "<td class='winbox' nowrap align='right'><div class='pt10'>" . number_format($t_buhin, 0) . "</div></td>\n";
                    ?>
                    <td class='winbox' nowrap align='right'><div class='pt10'>��</div></td>
                    <td class='winbox' nowrap align='right'><div class='pt10'>��</div></td>
                    <?php
                    echo "<td class='winbox' nowrap align='right'><div class='pt10'>" . number_format($a_buhin, 0) . "</div></td>\n";
                    ?>
                </tr>
                <tr>
                    <th class='winbox' nowrap>�����</th>
                    <?php
                    echo "<td class='winboxy' nowrap align='right'>" . number_format($c_souuri, 0) . "</td>\n";
                    echo "<td class='winboxy' nowrap align='right'>" . number_format($l_souuri, 0) . "</td>\n";
                    echo "<td class='winboxy' nowrap align='right'>" . number_format($t_souuri, 0) . "</td>\n";
                    echo "<td class='winboxy' nowrap align='right'>" . number_format($s_souuri, 0) . "</td>\n";
                    echo "<td class='winboxy' nowrap align='right'>" . number_format($b_souuri, 0) . "</td>\n";
                    echo "<td class='winboxy' nowrap align='right'>" . number_format($a_souuri, 0) . "</td>\n";
                    ?>
                </tr>
            </tbody>
        </table>
            </td></tr>
        </table> <!----------------- ���ߡ�End ------------------>
        <BR>
        <table bgcolor='#d6d3ce' align='center' border='1' cellspacing='0' cellpadding='3'>
            <tr><td> <!----------- ���ߡ�(�ǥ�������) ------------>
        <table class='winbox_field' width='100%' bgcolor='#d6d3ce' align='center' border='1' cellspacing='0' cellpadding='3'>
            <div class='pt11b' align='right'><ñ�̡����></div>
            <thead>
                <!-- �ơ��֥� �إå�����ɽ�� -->
                <tr>
                    <th class='winbox' nowrap>���ץ���ɸ</th>
                    <th class='winbox' nowrap>��˥���ɸ</th>
                    <th class='winbox' nowrap>�ġ�����ɸ</th>
                    <th class='winbox' nowrap>���ɸ</th>
                    <th class='winbox' nowrap>������ɸ</th>
                    <th class='winbox' nowrap>������ɸ</th>
                    <th class='winbox' nowrap>��</th>
                </tr>
            </thead>
            <tfoot>
                <!-- ���ߤϥեå����ϲ���ʤ� -->
            </tfoot>
            <tbody>
                
                <tr>
                    <td align='center' bgcolor='white'>
                        <input type='text' name='invent[]' size='12' maxlength='11' value='<?php echo $invent[3] ?>' class='right'>
                    </td>
                    <td align='center' bgcolor='white'>
                        <input type='text' name='invent[]' size='12' maxlength='11' value='<?php echo $invent[4] ?>' class='right'>
                    </td>
                    <td align='center' bgcolor='white'>
                        <input type='text' name='invent[]' size='12' maxlength='11' value='<?php echo $invent[5] ?>' class='right'>
                    </td>
                    <td align='center' bgcolor='white'>
                        <input type='text' name='invent[]' size='12' maxlength='11' value='<?php echo $invent[6] ?>' class='right'>
                    </td>
                    <td align='center' bgcolor='white'>
                        <input type='text' name='invent[]' size='12' maxlength='11' value='<?php echo $invent[7] ?>' class='right'>
                    </td>
                    <?php
                    echo "<td class='winbox' nowrap align='right'><div class='pt10'>" . number_format($invent[8], 0) . "</div></td>\n";
                    ?>
                    <td colspan='4' align='center'>
                        <input type='submit' name='entry' value='��Ͽ' >
                    </td>
                </tr>
            </tbody>
        </table>
            </td></tr>
        </table> <!----------------- ���ߡ�End ------------------>
        <BR>
        <table bgcolor='#d6d3ce' align='center' border='1' cellspacing='0' cellpadding='3'>
            <tr><td> <!----------- ���ߡ�(�ǥ�������) ------------>
        <table class='winbox_field' width='100%' bgcolor='#d6d3ce' align='center' border='1' cellspacing='0' cellpadding='3'>
            <div class='pt11b' align='right'><ñ�̡����></div>
            <thead>
                <!-- �ơ��֥� �إå�����ɽ�� -->
                <tr>
                    <th class='winbox' nowrap>��</th>
                    <th class='winbox' nowrap>���ץ�</th>
                    <th class='winbox' nowrap>��˥�</th>
                    <th class='winbox' nowrap>�ġ���</th>
                    <th class='winbox' nowrap>�����</th>
                    <th class='winbox' nowrap>���ʴ���</th>
                    <th class='winbox' nowrap>���</th>
                </tr>
            </thead>
            <tfoot>
                <!-- ���ߤϥեå����ϲ���ʤ� -->
            </tfoot>
            <tbody>
                <tr>
                    <th class='winbox' nowrap>ɸ����</th>
                    <?php
                    echo "<td class='winbox' nowrap align='right'><div class='pt10'>" . number_format($c_hyo_s, 0) . "</div></td>\n";
                    echo "<td class='winbox' nowrap align='right'><div class='pt10'>" . number_format($l_hyo_s, 0) . "</div></td>\n";
                    echo "<td class='winbox' nowrap align='right'><div class='pt10'>" . number_format($t_hyo_s, 0) . "</div></td>\n";
                    echo "<td class='winbox' nowrap align='right'><div class='pt10'>" . number_format($s_hyo_s, 0) . "</div></td>\n";
                    echo "<td class='winbox' nowrap align='right'><div class='pt10'>" . number_format($b_hyo_s, 0) . "</div></td>\n";
                    ?>
                    <td class='winbox' nowrap align='right'><div class='pt10'>��</div></td>
                </tr>
                <tr>
                    <th class='winbox' nowrap>������</th>
                    <?php
                    echo "<td class='winbox' nowrap align='right'><div class='pt10'>" . number_format($c_toku_s, 0) . "</div></td>\n";
                    ?>
                    <td class='winbox' nowrap align='right'><div class='pt10'>��</div></td>
                    <td class='winbox' nowrap align='right'><div class='pt10'>��</div></td>
                    <td class='winbox' nowrap align='right'><div class='pt10'>��</div></td>
                    <td class='winbox' nowrap align='right'><div class='pt10'>��</div></td>
                    <td class='winbox' nowrap align='right'><div class='pt10'>��</div></td>
                </tr>
                <tr>
                    <th class='winbox' nowrap>���ʷ�</th>
                    <?php
                    echo "<td class='winboxy' nowrap align='right'>" . number_format($c_sei_t, 0) . "</td>\n";
                    echo "<td class='winboxy' nowrap align='right'>" . number_format($l_sei_t, 0) . "</td>\n";
                    echo "<td class='winboxy' nowrap align='right'>" . number_format($t_sei_t, 0) . "</td>\n";
                    echo "<td class='winboxy' nowrap align='right'>��</td>\n";
                    echo "<td class='winboxy' nowrap align='right'>��</td>\n";
                    echo "<td class='winboxy' nowrap align='right'>��</td>\n";
                    ?>
                </tr>
                <tr>
                    <th class='winbox' nowrap>����</th>
                    <?php
                    echo "<td class='winbox' nowrap align='right'><div class='pt10'>" . number_format($c_buhin_s, 0) . "</div></td>\n";
                    echo "<td class='winbox' nowrap align='right'><div class='pt10'>" . number_format($l_buhin_s, 0) . "</div></td>\n";
                    echo "<td class='winbox' nowrap align='right'><div class='pt10'>" . number_format($t_buhin_s, 0) . "</div></td>\n";
                    ?>
                    <td class='winbox' nowrap align='right'><div class='pt10'>��</div></td>
                    <td class='winbox' nowrap align='right'><div class='pt10'>��</div></td>
                    <?php
                    echo "<td class='winbox' nowrap align='right'><div class='pt10'>" . number_format($a_buhin_s, 0) . "</div></td>\n";
                    ?>
                </tr>
                <tr>
                    <th class='winbox' nowrap>����¾</th>
                    <?php
                    echo "<td class='winbox' nowrap align='right'><div class='pt10'>" . number_format($c_sonota_s, 0) . "</div></td>\n";
                    echo "<td class='winbox' nowrap align='right'><div class='pt10'>" . number_format($l_sonota_s, 0) . "</div></td>\n";
                    echo "<td class='winbox' nowrap align='right'><div class='pt10'>" . number_format($t_sonota_s, 0) . "</div></td>\n";
                    ?>
                    <td class='winbox' nowrap align='right'><div class='pt10'>��</div></td>
                    <td class='winbox' nowrap align='right'><div class='pt10'>��</div></td>
                    <?php
                    echo "<td class='winbox' nowrap align='right'><div class='pt10'>" . number_format($a_sonota_s, 0) . "</div></td>\n";
                    ?>
                </tr>
                <tr>
                    <th class='winbox' nowrap>���ʡ�����¾��</th>
                    <?php
                    echo "<td class='winboxy' nowrap align='right'>" . number_format($c_buhin_t, 0) . "</td>\n";
                    echo "<td class='winboxy' nowrap align='right'>" . number_format($l_buhin_t, 0) . "</td>\n";
                    echo "<td class='winboxy' nowrap align='right'>" . number_format($t_buhin_t, 0) . "</td>\n";
                    echo "<td class='winboxy' nowrap align='right'>��</td>\n";
                    echo "<td class='winboxy' nowrap align='right'>��</td>\n";
                    echo "<td class='winboxy' nowrap align='right'>��</td>\n";
                    ?>
                </tr>
                <tr>
                    <th class='winbox' nowrap>����</th>
                    <?php
                    echo "<td class='winboxy' nowrap align='right'>" . number_format($c_jisseki, 0) . "</td>\n";
                    echo "<td class='winboxy' nowrap align='right'>" . number_format($l_jisseki, 0) . "</td>\n";
                    echo "<td class='winboxy' nowrap align='right'>" . number_format($t_jisseki, 0) . "</td>\n";
                    echo "<td class='winboxy' nowrap align='right'>" . number_format($s_jisseki, 0) . "</td>\n";
                    echo "<td class='winboxy' nowrap align='right'>" . number_format($b_jisseki, 0) . "</td>\n";
                    echo "<td class='winboxy' nowrap align='right'>" . number_format($a_jisseki, 0) . "</td>\n";
                    ?>
                </tr>
                <tr>
                    <th class='winbox' nowrap>ã���١�</th>
                    <?php
                    echo "<td class='winboxy' nowrap align='right'>" . number_format($c_ritsu, 1) . "</td>\n";
                    echo "<td class='winboxy' nowrap align='right'>" . number_format($l_ritsu, 1) . "</td>\n";
                    echo "<td class='winboxy' nowrap align='right'>" . number_format($t_ritsu, 1) . "</td>\n";
                    echo "<td class='winboxy' nowrap align='right'>" . number_format($s_ritsu, 1) . "</td>\n";
                    echo "<td class='winboxy' nowrap align='right'>" . number_format($b_ritsu, 1) . "</td>\n";
                    echo "<td class='winboxy' nowrap align='right'>" . number_format($a_ritsu, 1) . "</td>\n";
                    ?>
                </tr>
            </tbody>
        </table>
            </td></tr>
        </table> <!----------------- ���ߡ�End ------------------>
        
        </form>
    </center>
</body>
<?php echo $menu->out_alert_java()?>
</html>
<?php
// ob_end_flush();                 // ���ϥХåե���gzip���� END
?>
