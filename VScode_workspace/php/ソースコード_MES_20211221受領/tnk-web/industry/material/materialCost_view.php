<?php
//////////////////////////////////////////////////////////////////////////////
// �������ξȲ� (��������)                                                //
// Copyright (C) 2003-2016 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp      //
// Changed history                                                          //
// 2003/12/19 Created   metarialCost_view.php                               //
// 2003/12/20 ��󥯤ǸƤФ줿�����ǥ����׵�ǽ���ɲ� ?page_keep=1         //
// 2003/12/22 �����ֹ椬���ʾ�ξ��������ֹ桦����̾�����ˤ���          //
// 2004/01/06 �꥿���󥢥ɥ쥹������ǧ�ڥ����å��򤷤�NG�ʤ�TOP Index��   //
// 2004/05/12 �����ȥ�˥塼ɽ������ɽ�� �ܥ����ɲ� menu_OnOff($script)�ɲ� //
// 2004/05/25 ����ɽ��ɽ�����٥�ɽ�����б� Level5�ޤ� page=300���ѹ�      //
// 2004/06/03 �嵭��ȼ��ORDER BY par_parts ASC, parts_no ASC, pro_no ASC �� //
//         ���ֹ�����ͤ��ѹ���̵����ͭ��������ٵ롦���ֹ桦�ʤ��ν��ɽ�� //
// 2004/10/07 $search = sprintf("where plan_no='%s' and par_parts=''", $plan_no);
//            $search = sprintf("where plan_no='%s'", $plan_no); ���ѹ�     //
// 2005/02/07 MenuHeader class ����Ѥ��ƶ��̥�˥塼���ڤ�ǧ���������ѹ�   //
//            $search = sprintf("where plan_no='%s'", $plan_no) ��          //
//            "where plan_no='%s' and assy_no='%s'", $plan_no, $assy_no)��  //
//            colspan='$num+1' �� colspan='$num+2' ���ѹ�                   //
//            ������ ��������Ǥ�php�����ϻ����Բ�                        //
// 2005/05/11 ���������ѹ�������Ͽ����ɽ���ɲ�(last_date, regdate)        //
// 2005/06/13 ��ư��Ͽ�ʤ�ɽ���ɲ� $regdate ��񼰥ե����ޥå�              //
// 2005/06/17 �����ֹ楯��å�����ݼ��ӤξȲ�إ����פ��뵡ǽ�ɲ�        //
// 2006/03/15 ��٥�� && ($res[$r2-1][4] == 'NK' || $res[$r2-1][4] == 'MT')//
//            2��٥�ʲ���ȴ���Ƥ���Τ���                               //
// 2006/03/16 ���˥�٥�ɽ������å���������DB�Υ��ȥ����ɥץ������㡼��//
// 2006/05/17 material_cost_level_as()���ɲä���AS/400�Υꥹ�Ȥȹ�碌��    //
// 2006/10/06 ��ݼ��Ӥ�ƽФ��Ƥ����Τ�߸˷����ƽФ��褦���ѹ�          //
//            ���λ� $_SESSION['material_plan_no'] �򥻥åȤ���Τ�˺�줺�� //
// 2007/02/20 parts/����parts/parts_stock_history/parts_stock_view.php���ѹ�//
// 2007/03/24 �嵭��parts_stock_view.php �� parts_stock_history_Main.php �� //
//            <tr>�����˥��󥫡���Ω�ƤƤ�NN7.1�Ǥ�̵���ʤ���<td>�������ѹ� //
//            ����NN7.1�Ǥ� set_focus()�ǥ��󥫡��ؤΥ����פ�̵���ˤʤ�   //
// 2007/09/18 E_ALL | E_STRICT ���ѹ�  ZZ��25.60�ɲ�                        //
// 2007/09/28 ��Ω������Ψ�׻��򻲹ͤ����Ǥʤ�������Ψ��ȿ��(ü���˹�碌��)//
// 2007/09/29 25.60 �� 57.00 �ǥ��ߥ�졼����󤷤Ƹ����ᤷ��(�����ȥ�����//
// 2007/10/01 �������Υեå�����ʬ�����٤�rowspan='9'���ɲ�               //
// 2008/11/12 ��Ψ�ѹ��ˤ����ڲ����ѹ��ΰ���Ψ��                          //
//            ���ץ�=57.00 ��˥�=44.00 ���ѹ�                         ��ë //
// 2015/05/28 �������ʤ����������Ͽ���б�                             ��ë //
// 2016/08/08 mouseOver���ɲ�                                          ��ë //
//////////////////////////////////////////////////////////////////////////////
// ini_set('error_reporting', E_ALL | E_STRICT);     // E_ALL='2047' debug ��
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
$menu->set_site(30, 20);                    // site_index=30(������˥塼) site_id=20(�������ξȲ� �ײ��ֹ�)
////////////// �꥿���󥢥ɥ쥹����
// $menu->set_RetUrl(INDUST_MENU);             // �̾�ϻ��ꤹ��ɬ�פϤʤ�
//////////// �����ȥ�̾(�������Υ����ȥ�̾�ȥե�����Υ����ȥ�̾)
$menu->set_title('�� �� �� �� �� �� �� (��������)');
//////////// �ƽ����action̾�ȥ��ɥ쥹����
// $menu->set_action('��ݼ��ӾȲ�',   INDUST . 'payable/act_payable_view.php');
$menu->set_action('�߸˷���',   INDUST . 'parts/parts_stock_history/parts_stock_history_Main.php');
//////////// �꥿���󥢥ɥ쥹�ؤ�GET�ǡ��������å�
$menu->set_retGET('page_keep', 'on');

if (isset($_REQUEST['material'])) {     // �Ƶ��ƽФΥ����å�
    $menu->set_retGET('page_keep', $_REQUEST['material']);
    $parts_no = @$_SESSION['stock_parts'];
    if (isset($_REQUEST['row'])) {
        $row_no   = $_REQUEST['row'];   // ����ƽФ������ֹ�
    } else {
        $row_no = -1;       // �߸˷��򤫤�κƵ��ƽФǤʤ����
    }
} else {
    $parts_no = '';
    $row_no   = '-1';       // ñ�ΤǾȲ񤵤줿��
}

//////////// JavaScript Stylesheet File ��ɬ���ɤ߹��ޤ���
$uniq = uniqid('target');

//////////// �ѥ�᡼�����μ���
if ( !(isset($_POST['forward']) || isset($_POST['backward']) || isset($_GET['page_keep'])) ) {
    if (isset($_GET['plan_no'])) {
        $_SESSION['plan_no'] = $_GET['plan_no'];    // ���ǻȤ�������¸���Ƥ���
        $_SESSION['assy_no'] = $_GET['assy_no'];    // ���ǻȤ�������¸���Ƥ���
    }
}

//////////// ���ǤιԿ�
define('PAGE', '300');

//////////// �ײ��ֹ桦�����ֹ�򥻥å���󤫤����
if (isset($_SESSION['plan_no'])) {
    $plan_no = $_SESSION['plan_no'];
    $_SESSION['material_plan_no'] = $plan_no;   // ��������ѥ��å�������¸(�ޡ����󥰤ȥ��󥫡����åȤΤ���)
    if (substr($plan_no, 0, 1) == 'C') {
        /******** ����ɸ��ι����ɲ� *********/
        $sql2 = "
            SELECT substr(note15, 1, 2) FROM assembly_schedule WHERE plan_no='{$plan_no}'
        ";
        $sc = '';
        getUniResult($sql2, $sc);
        if ($sc == 'SC') {
            define('RATE', 25.60);  // ���ץ�����
        } else {
            $sql2 = "
                SELECT kanryou FROM assembly_schedule WHERE plan_no='{$plan_no}'
            ";
            $kan = '';
            getUniResult($sql2, $kan);
            if ($kan < 20071001) {
                define('RATE', 25.60);  // ���ץ�ɸ�� 2007/10/01���ʲ������
            } elseif ($kan < 20110401) {
                define('RATE', 57.00);  // ���ץ�ɸ�� 2007/10/01���ʲ���ʹ�
            } else {
                define('RATE', 45.00);  // ���ץ�ɸ�� 2011/04/01���ʲ���ʹ�
            }
        }
    } elseif (substr($plan_no, 0, 2) == 'ZZ') {
        if (substr($assy_no, 0, 1) == 'C') {
            if ($kan < 20110401) {
                define('RATE', 57.00);  // ���ץ�ɸ�� 2007/10/01���ʲ���ʹ�
            } else {
                define('RATE', 45.00);  // ���ץ�ɸ�� 2011/04/01���ʲ���ʹ�
            }
        } elseif (substr($assy_no, 0, 1) == 'L') {
            if ($kan < 20110401) {
                define('RATE', 44.00);  // ��˥� 2007/10/01���ʲ���ʹ�
            } else {
                define('RATE', 53.00);  // ��˥� 2011/04/01���ʲ���ʹ�
            }
        } else {
            define('RATE', 50.00);  // �ġ���
        }
    } elseif (substr($plan_no, 0, 1) == 'L') {
        $sql2 = "
            SELECT kanryou FROM assembly_schedule WHERE plan_no='{$plan_no}'
        ";
        $kan = '';
        getUniResult($sql2, $kan);
        if ($kan < 20081001) {
            define('RATE', 37.00);  // ��˥� 2008/10/01���ʲ������
        } elseif ($kan < 20110401) {
            define('RATE', 44.00);  // ��˥� 2008/10/01���ʲ���ʹ�
        } else {
            define('RATE', 53.00);  // ��˥� 2011/04/01���ʲ���ʹ�
        }
    } else {
        $sql2 = "
            SELECT kanryou FROM assembly_schedule WHERE plan_no='{$plan_no}'
        ";
        $kan = '';
        getUniResult($sql2, $kan);
        define('RATE', 50.00);  // �ġ���
    }
} else {
    $_SESSION['s_sysmsg'] .= '�ײ��ֹ椬���ꤵ��Ƥʤ���';      // .= ��å��������ɲä���
    header('Location: ' . H_WEB_HOST . $menu->out_RetUrl());      // ľ���θƽи������
    exit();
}
if (isset($_SESSION['assy_no'])) {
    $assy_no = $_SESSION['assy_no'];
} else {
    $_SESSION['s_sysmsg'] .= '�����ֹ椬���ꤵ��Ƥʤ���';      // .= ��å��������ɲä���
    header('Location: ' . H_WEB_HOST . $menu->out_RetUrl());      // ľ���θƽи������
    exit();
}

//////////// ����̾�μ���
$query = "select midsc from miitem where mipn='{$assy_no}'";
if ( getUniResult($query, $assy_name) <= 0) {           // ����̾�μ���
    $_SESSION['s_sysmsg'] .= "����̾�μ����˼���";      // .= ��å��������ɲä���
    header('Location: ' . H_WEB_HOST . $menu->out_RetUrl());      // ľ���θƽи������
    exit();
}

//////////// ɽ�������
$menu->set_caption("�ײ��ֹ桧{$plan_no}  �����ֹ桧{$assy_no}  ����̾��{$assy_name}");

//////////// SQL ʸ�� where ��� ���Ѥ���
$search = sprintf("where plan_no='%s' and assy_no='%s'", $plan_no, $assy_no);  // 2004/10/07  and par_parts=''����
// $search = '';

//////////// ��ץ쥳���ɿ����������μ���     (�оݥǡ����κ������ڡ�������˻���)
$query = sprintf("select count(*), sum(Uround(pro_price * pro_num, 2)) from material_cost_history %s", $search);
$res_sum = array();
if ( getResult2($query, $res_sum) <= 0) {         // $maxrows �μ���
    $_SESSION['s_sysmsg'] .= "��ץ쥳���ɿ��μ����˼���";      // .= ��å��������ɲä���
}
$maxrows = $res_sum[0][0];
$sum_kin = $res_sum[0][1];

$query = sprintf("select sum(Uround(pro_num * pro_price, 2)) from material_cost_history
                    %s and intext='0'", $search);
if ( getUniResult($query, $ext_kin) <= 0) {  // �����������
    $_SESSION['s_sysmsg'] .= "�����������μ����˼���";      // .= ��å��������ɲä���
}
$query = sprintf("select sum(Uround(pro_num * pro_price, 2)) from material_cost_history
                    %s and intext='1'", $search);
if ( getUniResult($query, $int_kin) <= 0) {  // ������������
    $_SESSION['s_sysmsg'] .= "����������μ����˼���";      // .= ��å��������ɲä���
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


//////////// SQL ʸ�� where ��� ���Ѥ���
$search = sprintf("where plan_no='%s' and par_parts=''", $plan_no);  // 2004/10/07 $search��ͭ����ʤ��Τ��ɲ�
//////////// �ײ��ֹ�ñ�̤ι������٤κ�ɽ
$query = "
    SELECT  
        mate.last_user
                    AS  \"Level\"               -- 0
        ,parts_no   as �����ֹ�                 -- 1
        ,trim(substr(midsc,1,25))
                    as ����̾                   -- 2
        ,pro_num    as ���ѿ�                   -- 3
        ,pro_no     as ����                     -- 4
        ,pro_mark   as ����̾                   -- 5
        ,pro_price  as ����ñ��                 -- 6
        ,Uround(pro_num * pro_price, 2)
                    as �������                 -- 7
        ,CASE
            WHEN intext = '0' THEN '����'
            WHEN intext = '1' THEN '���'
            ELSE intext
        END         as �⳰��                   -- 8
        ,CASE
            WHEN pro_mark = 'NK'
                AND pro_price = 0 THEN '̵��'
            WHEN pro_mark = 'NK'
                AND pro_price > 0 THEN 'ͭ��'
            WHEN par_parts = ''
                AND pro_price = 0 THEN '����ٵ�'
            WHEN par_parts != ''  THEN par_parts
            ELSE par_parts
        END         as ������                   -- 9 ���ֹ梪���ͤ��ѹ�
    FROM
        material_cost_level_as('{$plan_no}') AS mate
    LEFT OUTER JOIN
         miitem ON parts_no=mipn 
    OFFSET {$offset} LIMIT 300
";
$res_view   = array();
$field_view = array();
if (($rows_view = getResultWithField2($query, $field_view, $res_view)) <= 0) {
    $_SESSION['s_sysmsg'] .= "<font color='yellow'><br>����̤��Ͽ�Ǥ���</font>";
    header('Location: ' . H_WEB_HOST . $menu->out_RetUrl());      // ľ���θƽи������
    exit();
} else {
    ////// �ե�����ɿ�������
    $num_view = count($field_view);       // �ե�����ɿ�����
}

/////////// ��Ω�� & ��׶�� & �ѹ�������Ͽ���μ���
                                // 'YYYY-MM-DD HH24:MI:SS'
$query = "SELECT m_time, m_rate, a_time, a_rate, g_time, g_rate, assy_time, assy_rate
                , to_char(last_date AT TIME ZONE 'JST', 'YYYY-MM-DD HH24:MI')   AS �ѹ���
                , to_char(regdate AT TIME ZONE 'JST', 'YYYY-MM-DD HH24:MI:SS')     AS ��Ͽ��
                , m_time + g_time AS ma_time -- ���ȹ������ 2007/09/28 ADD
            FROM material_cost_header WHERE plan_no='{$plan_no}'";
$res_time = array();
if ( getResult2($query, $res_time) > 0 ) {
    $m_time = $res_time[0][0];
    $m_rate = $res_time[0][1];
    $a_time = $res_time[0][2];
    $a_rate = $res_time[0][3];
    $g_time = $res_time[0][4];
    $g_rate = $res_time[0][5];
    ///// ��� ��Ω��(������)
    $m_price = Uround($m_time * $m_rate, 2);
    $a_price = Uround($a_time * $a_rate, 2);
    $g_price = Uround($g_time * $g_rate, 2);
    $assy_int_price = ( $m_price + 
                        $a_price + 
                        $g_price );
    ///// �����칩�� ������Ψ����Ω��
    $assy_time  = $res_time[0][10];     // 2007/09/28 ���Ȥȳ���ι�פ��ѹ�(m_time + a_time)
    $assy_rate  = $res_time[0][7];
    // $assy_rate  = 57.00;                // 2007/09/29 25.60��57.00�ǥ��ߥ�졼�����
    $assy_price = Uround($assy_time * $assy_rate, 2);
    $auto_price = Uround($a_time * $a_rate, 2);     // 2007/09/28 ��ư��Ω������Ω����ɲ�
    ///// �ѹ�������Ͽ��
    $last_date = $res_time[0][8];
    $regdate   = $res_time[0][9];
    if (substr($regdate, 11, 8) == '00:00:00') {
        $regdate = "<span style='color:red;'>��ư��Ͽ</span>(" . substr($regdate, 0, 10) . ')';
    }
} else {
    $m_time = 0;
    $m_rate = 0;
    $a_time = 0;
    $a_rate = 0;
    $g_time = 0;
    $g_rate = 0;
    $assy_int_price = 0;
    $assy_time  = 0;
    $assy_rate  = RATE;
    $assy_price = 0;
    $auto_price = 0;    // 2007/09/28 ADD
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
<script language='JavaScript' src='template.js?<?php echo $uniq ?>'></script>
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

/* ������ϥե�����Υ�����Ȥ˥ե������������� */
function set_focus(){
    // document.mhForm.backwardStack.focus();  // IE/NN ξ�б�
    // document.entry_form.parts_no.focus();      // ������ϥե����ब������ϥ����Ȥ򳰤�
    // document.entry_form.parts_no.select();
}
// -->
</script>

<!-- �������륷���ȤΥե��������򥳥��� HTML���� �����Ȥ�����Ҥ˽���ʤ��������
<link rel='stylesheet' href='template.css?<?php echo $uniq ?>' type='text/css' media='screen'>
-->

<style type="text/css">
<!--
.pt9 {
    font:normal     9pt;
    font-family:    monospace;
}
.pt10 {
    font:normal     10pt;
    font-family:    monospace;
}
.pt10b {
    font-size:      10pt;
    font-weight:    bold;
    font-family:    monospace;
}
.caption_font {
    font-size:      11pt;
    color:          blue;
}
th {
    background-color:   yellow;
    color:              blue;
    font-size:          10pt;
    font-wieght:        bold;
    font-family:        monospace;
}
.parts_font {
    font-size:      12pt;
    font-weight:    bold;
    font-family:    monospace;
    text-align:     left;
}
.pro_num_font {
    font-size:      12pt;
    font-weight:    bold;
    font-family:    monospace;
    text-align:     center;
}
.price_font {
    font-size:      12pt;
    font-weight:    bold;
    font-family:    monospace;
    text-align:     right;
}
.entry_font {
    font-size:      11pt;
    font-weight:    normal;
    color:          red;
}
a:hover {
    background-color: gold;
}
a {
    font-size:   10pt;
    font-weight: bold;
    color:       blue;
}
.winbox {
    border-style:           solid;
    border-width:           1px;
    border-top-color:       #FFFFFF;
    border-left-color:      #FFFFFF;
    border-right-color:     #bdaa90;
    border-bottom-color:    #bdaa90;
    /* background-color:#d6d3ce; */
}
.winbox_field {
    border-style:           solid;
    border-width:           1px;
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
<?php echo $menu->out_title_border()?>
        
        <div>
        <span class='entry_font'>�������<?php echo number_format($sum_kin + $assy_price + $auto_price, 2) ."\n" ?></span>
        <span class='pt10' style='color:gray;'>(���ߤη�����Ψ��<?php echo number_format(RATE, 2) ?>)</span>
        <span class='entry_font'>�������� �������<?php echo number_format($sum_kin + $assy_int_price, 2) ."\n" ?></span>
        <span class='pt10' style='color:gray;'>���ѹ�����<?php echo $last_date ?></span>
        <span class='pt10' style='color:gray;'>����Ͽ����<?php echo $regdate ?></span>
        </div>
        
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
                        <?php echo $menu->out_caption() . "\n" ?>
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
        <table width='98%' bgcolor='#d6d3ce' align='center' border='1' cellspacing='0' cellpadding='3'>
            <tr><td> <!----------- ���ߡ�(�ǥ�������) ------------>
        <table class='winbox_field' width='100%' bgcolor='#d6d3ce' align='center' border='1' cellspacing='0' cellpadding='3'>
            <!-- �ơ��֥� �إå�����ɽ�� -->
            <tr>
                <th class='winbox' nowrap width='10'>No</th>        <!-- �ԥʥ�С���ɽ�� -->
            <?php
            for ($i=0; $i<$num_view; $i++) {             // �ե�����ɿ�ʬ���֤�
            ?>
                <th class='winbox' nowrap><?php echo $field_view[$i] ?></th>
            <?php
            }
            ?>
            </tr>
                    <!--  bgcolor='#ffffc6' �������� -->
                    <!-- ����ץ�<td rowspan='2' colspan='3' width='200' align='center' class='pt10b' bgcolor='#ffffc6'>  </td> -->
            <?php
            for ($r=0; $r<$rows_view; $r++) {
                if ($row_no == $r) {
                    echo "<tr style='background-color:#ffffc6;'>\n";
                    echo "<td class='winbox' nowrap align='right'>\n";
                    echo "    <a name='mark'><div class='pt10b'>", ($r + $offset + 1), "</div></a>\n";
                    echo "</td>    <!-- �ԥʥ�С���ɽ�� -->\n";
                } else {
                    echo "<tr onMouseOver=\"style.background='#ceffce'\" onMouseOut=\"style.background='#d6d3ce'\">\n";
                    echo "<td class='winbox' nowrap align='right'>\n";
                    echo "    <div class='pt10b'>", ($r + $offset + 1), "</div>\n";
                    echo "</td>    <!-- �ԥʥ�С���ɽ�� -->\n";
                }
                for ($i=0; $i<$num_view; $i++) {         // �쥳���ɿ�ʬ���֤�
                    if ($res_view[$r][9] == '') {
                        switch ($i) {   // �����ʤʤ�
                        case 0:    // ��٥�
                            echo "<td class='winbox' nowrap align='left'><div class='pt10b'>" . $res_view[$r][$i] . "</div></td>\n";
                            break;
                        case 1:     // �����ֹ�
                            if ($r != 0 && $res_view[$r][1] == $res_view[$r-1][1]) {
                                echo "<td class='winbox' nowrap align='center'><div class='pt9'>&nbsp;</div></td>\n";
                            } else {
                                echo "<td class='winbox' nowrap align='center'><div class='pt9'><a href='", $menu->out_action('�߸˷���'), "?parts_no=", urlencode($res_view[$r][$i]), "&plan_no=", urlencode($plan_no), "&material=1&row={$r}' target='application' style='text-decoration:none;'>{$res_view[$r][$i]}</a></div></td>\n";
                            }
                            break;
                        case 2:     // ����̾
                            if ($r != 0 && $res_view[$r][1] == $res_view[$r-1][1]) {
                                echo "<td class='winbox' nowrap width='300 align='left'><div class='pt9'>&nbsp;</div></td>\n";
                            } else {
                                echo "<td class='winbox' nowrap width='300 align='left'><div class='pt9'>{$res_view[$r][$i]}</div></td>\n";
                            }
                            break;
                        case  3:    // ���ѿ�
                            echo "<td class='winbox' nowrap align='right'><div class='pt9'>" . number_format($res_view[$r][$i], 4) . "</div></td>\n";
                            break;
                        case  6:    // ����ñ��
                        case  7:    // �������
                            echo "<td class='winbox' nowrap align='right'><div class='pt9'>" . number_format($res_view[$r][$i], 2) . "</div></td>\n";
                            break;
                        case  9:    // ����
                            echo "<td class='winbox' nowrap align='center'><div class='pt9'>&nbsp;</div></td>\n";
                            break;
                        default:    // ����������̾���⳰��
                            echo "<td class='winbox' nowrap align='center'><div class='pt9'>{$res_view[$r][$i]}</div></td>\n";
                        }
                    } else {            // �����ʤ����ꤵ��Ƥ�������ʤʤ�
                        switch ($i) {
                        case 0:    // ��٥�
                            echo "<td class='winbox' nowrap align='left'><div class='pt10b'>" . $res_view[$r][$i] . "</div></td>\n";
                            break;
                        case 1:     // �����ֹ�
                            if ($r != 0 && $res_view[$r][1] == $res_view[$r-1][1]) {
                                echo "<td class='winbox' bgcolor='#e6e6e6' nowrap align='center'><div class='pt9'>&nbsp;</div></td>\n";
                            } else {
                                echo "<td class='winbox' bgcolor='#e6e6e6' nowrap align='center'><div class='pt9'>
                                        <a href='", $menu->out_action('�߸˷���'), "?parts_no=", urlencode($res_view[$r][$i]), "&plan_no=", urlencode($plan_no), "&material=1&row={$r}' target='application' style='text-decoration:none;'>
                                            {$res_view[$r][$i]}
                                        </a></div></td>\n";
                            }
                            break;
                        case 2:     // ����̾
                            if ($r != 0 && $res_view[$r][1] == $res_view[$r-1][1]) {
                                echo "<td class='winbox' bgcolor='#e6e6e6' nowrap width='300 align='left'><div class='pt9'>&nbsp;</div></td>\n";
                            } else {
                                echo "<td class='winbox' bgcolor='#e6e6e6' nowrap width='300 align='left'><div class='pt9'>{$res_view[$r][$i]}</div></td>\n";
                            }
                            break;
                        case  3:    // ���ѿ�
                            echo "<td class='winbox' bgcolor='#e6e6e6' nowrap align='right'><div class='pt9'>" . number_format($res_view[$r][$i], 4) . "</div></td>\n";
                            break;
                        case  6:    // ����ñ��
                        case  7:    // �������
                            echo "<td class='winbox' bgcolor='#e6e6e6' nowrap align='right'><div class='pt9'>" . number_format($res_view[$r][$i], 2) . "</div></td>\n";
                            break;
                        default:    // ����������̾���⳰��
                            echo "<td class='winbox' bgcolor='#e6e6e6' nowrap align='center'><div class='pt9'>{$res_view[$r][$i]}</div></td>\n";
                        }
                    }
                }
                echo "</tr>\n";
            }
            ?>
        </table>
            </td></tr>
        </table> <!----------------- ���ߡ�End ------------------>
        <table width='98%' bgcolor='#d6d3ce' align='center' border='1' cellspacing='0' cellpadding='3'>
            <tr><td> <!----------- ���ߡ�(�ǥ�������) ------------>
        <table class='winbox_field' width='100%' bgcolor='#d6d3ce' align='center' border='1' cellspacing='0' cellpadding='0'>
            <tr onMouseOver="style.background='#ceffce'" onMouseOut="style.background='#d6d3ce'">
                <td rowspan='9' class='winbox pt10' width='45%'>
                    &nbsp;
                </td>
                <td class='winbox pt10' nowrap align='right'>
                    ��������<?php echo number_format($int_kin, 2) ."\n" ?>  
                </td>
                <td class='winbox pt10' nowrap align='right'>
                    ���������<?php echo number_format($ext_kin, 2) ."\n" ?>  
                </td>
                <td class='winbox pt10' nowrap align='right'>
                    ��׺�����<?php echo number_format($sum_kin, 2) ."\n" ?>
                </td>
            </tr>
            <tr onMouseOver="style.background='#ceffce'" onMouseOut="style.background='#d6d3ce'">
                <td class='winbox pt10' nowrap align='right'>
                    ��׼��ȹ�����<?php echo number_format($assy_time, 3) ."\n" ?>
                </td>
                <td class='winbox pt10' nowrap align='right'>
                    �� ������Ψ��<?php echo number_format($assy_rate, 2) ."\n" ?>
                </td>
                <td class='winbox pt10' nowrap align='right'>
                    ����Ω��<?php echo number_format($assy_price, 2) ."\n" ?>
                </td>
            </tr>
            <tr onMouseOver="style.background='#ceffce'" onMouseOut="style.background='#d6d3ce'">
                <td class='winbox pt10' nowrap align='right'>
                    ��ư��������<?php echo number_format($a_time, 3) ."\n" ?>
                </td>
                <td class='winbox pt10' nowrap align='right'>
                    ��ư����Ψ��<?php echo number_format($a_rate, 2) ."\n" ?>
                </td>
                <td class='winbox pt10' nowrap align='right'>
                    ����Ω��<?php echo number_format($auto_price, 2) ."\n" ?>
                </td>
            </tr>
            <tr onMouseOver="style.background='#ceffce'" onMouseOut="style.background='#d6d3ce'">
                <td colspan='3' class='winbox pt10' nowrap align='right' style='color:red;'>
                    �������<?php echo number_format($sum_kin + $assy_price + $auto_price, 2) ."\n" ?>
                </td>
            </tr>
            <tr>
                <td colspan='3' class='winbox pt10' nowrap align='right'>
                    &nbsp;
                </td>
            </tr>
            <tr onMouseOver="style.background='#ceffce'" onMouseOut="style.background='#d6d3ce'">
                <td class='winbox pt10' nowrap align='right'>
                    (���͡�������Ψ)��
                </td>
                <td class='winbox pt10' nowrap align='right'>
                    ��Ω��<?php echo number_format($assy_int_price, 2) ."\n" ?>
                </td>
                <td class='winbox pt10' nowrap align='right'>
                    ���������<?php echo number_format($sum_kin + $assy_int_price, 2) ."\n" ?>
                </td>
            </tr>
            <tr onMouseOver="style.background='#ceffce'" onMouseOut="style.background='#d6d3ce'">
                <td class='winbox pt10' nowrap align='right'>
                    ���ȹ�����<?php echo number_format($m_time, 3), "\n" ?>
                </td>
                <td class='winbox pt10' nowrap align='right'>
                    ������Ψ��<?php echo number_format($m_rate, 2), "\n" ?>
                </td>
                <td class='winbox pt10' nowrap align='right'>
                    ���ȶ�ۡ�<?php echo number_format($m_price, 2), "\n" ?>
                </td>
            </tr>
            <tr onMouseOver="style.background='#ceffce'" onMouseOut="style.background='#d6d3ce'">
                <td class='winbox pt10' nowrap align='right'>
                    ��ư��������<?php echo number_format($a_time, 3), "\n" ?>
                </td>
                <td class='winbox pt10' nowrap align='right'>
                    ��ư����Ψ��<?php echo number_format($a_rate, 2), "\n" ?>
                </td>
                <td class='winbox pt10' nowrap align='right'>
                    ��ư����ۡ�<?php echo number_format($a_price, 2), "\n" ?>
                </td>
            </tr>
            <tr onMouseOver="style.background='#ceffce'" onMouseOut="style.background='#d6d3ce'">
                <td class='winbox pt10' nowrap align='right'>
                    ��������<?php echo number_format($g_time, 3), "\n" ?>
                </td>
                <td class='winbox pt10' nowrap align='right'>
                    ������Ψ��<?php echo number_format($g_rate, 2), "\n" ?>
                </td>
                <td class='winbox pt10' nowrap align='right'>
                    �����ۡ�<?php echo number_format($g_price, 2), "\n" ?>
                </td>
            </tr>
        </table>
            </td></tr>
        </table> <!----------------- ���ߡ�End ------------------>
        
    </center>
</body>
<?php echo $menu->out_alert_java()?>
</html>
<?php
ob_end_flush();                 // ���ϥХåե���gzip���� END
?>
