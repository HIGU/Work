<?php
//////////////////////////////////////////////////////////////////////////////
// Ϣ�������ɽ �ĸ���̳ ���� �Ȳ�                                        //
// Copyright (C) 2017-2017 Norihisa.Ohya norihisa_ooya@nitto-kohki.co.jp    //
// Changed history                                                          //
// 2017/10/24 Created   link_trans_obligation_view.php                      //
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
$request = new Request;
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
//$menu->set_RetUrl(SYS_MENU);                // �̾�ϻ��ꤹ��ɬ�פϤʤ�
//////////// �����ȥ�̾(�������Υ����ȥ�̾�ȥե�����Υ����ȥ�̾)
//$menu->set_title('�� �� �� �� �� ��');
//////////// �ƽ����action̾�ȥ��ɥ쥹����
//$_SESSION['link_referer'] = $_SERVER['HTTP_REFERER'];     // �ƽФ�Ȥ�URL�򥻥å�������¸
$menu->set_RetUrl($_SESSION['link_referer']);             // �̾�ϻ��ꤹ��ɬ�פϤʤ�

//////////// �֥饦�����Υ���å����к���
$uniq = $menu->set_useNotCache('target');

//////////// �о�ǯ��Υ��å����ǡ�������
if ($request->get('wage_ym') != '') {
    $wage_ym = $request->get('wage_ym'); 
} elseif(isset($_POST['wage_ym'])) {
    $wage_ym = $_POST['wage_ym'];
} elseif(isset($_SESSION['wage_ym'])) {
    $wage_ym = $_SESSION['wage_ym'];
} else {
    $wage_ym = date('Ym');           // ���å����ǡ������ʤ����ν����(����)
}

//////////// �о�ǯ��Υ��å����ǡ�������
if ($request->get('customer') != '') {
    $customer = $request->get('customer');
} elseif(isset($_POST['customer'])) {
    $customer = $_POST['customer'];
} elseif(isset($_SESSION['customer'])) {
    $customer = $_SESSION['customer'];
} else {
    $customer = '00001';           // ���å����ǡ������ʤ����ν����(00001:NK)
}

// �ƥ����ѥ��顼�ɻ�
$_SESSION['2ki_ym'] = $wage_ym;
//$_SESSION['2ki_ym'] = 201709;

// �оݷ�����
$yyyymm   = $_SESSION['2ki_ym'];
$ki       = Ym_to_tnk($_SESSION['2ki_ym']);
$yyyy     = substr($yyyymm, 0,4);
$mm       = substr($yyyymm, 4,2);
if (($mm >= 1) && ($mm <= 3)) {
    $yyyy = ($yyyy - 1);
}

if($customer=='00001') {
    $cus_name = '���칩��';
} elseif ($customer=='00004') {
    $cus_name = '��ɥƥå�';
} elseif ($customer=='00005') {
    $cus_name = '������칩��';
} elseif ($customer=='00101') {
    $cus_name = '�Σˣɣ�';
}

$end_ym = $ki * 100 + 200003;
$str_ym = $end_ym - 99;


//////////// �����ȥ�̾(�������Υ����ȥ�̾�ȥե�����Υ����ȥ�̾)
$menu->set_title("�� {$ki} ����{$cus_name}���ġ������ġ�̳��������");

///////////// �Ƽ��������
// ��ݶ��������
$query = "select
                sales_ym   as ǯ��,
                sales_kuri as ��鷫��,
                sales_kei  as ��ݶ�׾�,
                sales_kai  as ��ݶ�����,
                sales_zan  as �����Ĺ�
          from
                link_trans_sales";
$search    = "where sales_code='$customer' and sales_ym>=$str_ym and sales_ym<=$end_ym ORDER BY sales_ym ASC";
$query_s   = sprintf("$query %s", $search);     // SQL query ʸ�δ���
$res_urikake = array();
$field = array();
if ($rows=getResultWithField2($query_s, $field, $res_urikake) <= 0) {
    $urikake_num = 0;
} else {
    $urikake_num = count($res_urikake);
}
// ��ݶ��������
$query = "select
                expense_ym   as ǯ��,
                expense_kuri as ��鷫��,
                expense_kei  as ��ݶ�׾�,
                expense_kai  as ��ݶ�����,
                expense_zan  as �����Ĺ�
          from
                link_trans_expense_history";
$search    = "where expense_code='$customer' and expense_kamoku='��ݶ�' and expense_ym>=$str_ym and expense_ym<=$end_ym ORDER BY expense_ym ASC";
$query_s   = sprintf("$query %s", $search);     // SQL query ʸ�δ���
$res_kaikake = array();
$field = array();
if ($rows=getResultWithField2($query_s, $field, $res_kaikake) <= 0) {
    $kaikake_num = 0;
} else {
    $kaikake_num = count($res_kaikake);
}

// ̤�������������
$query = "select
                expense_ym   as ǯ��,
                expense_kuri as ���۹�,
                expense_kei  as ����ȯ����,
                expense_kai  as �����ù�,
                expense_zan  as �Ĺ�
          from
                link_trans_expense_history";
$search    = "where expense_code='$customer' and expense_kamoku='̤������' and expense_ym>=$str_ym and expense_ym<=$end_ym ORDER BY expense_ym ASC";
$query_s   = sprintf("$query %s", $search);     // SQL query ʸ�δ���
$res_mishu = array();
$field = array();
if ($rows=getResultWithField2($query_s, $field, $res_mishu) <= 0) {
    $mishu_num = 0;
} else {
    $mishu_num = count($res_mishu);
}
// Ω�ض��������
$query = "select
                expense_ym   as ǯ��,
                expense_kuri as ���۹�,
                expense_kei  as ����ȯ����,
                expense_kai  as �����ù�,
                expense_zan  as �Ĺ�
          from
                link_trans_expense_history";
$search    = "where expense_code='$customer' and expense_kamoku='Ω�ض�' and expense_ym>=$str_ym and expense_ym<=$end_ym ORDER BY expense_ym ASC";
$query_s   = sprintf("$query %s", $search);     // SQL query ʸ�δ���
$res_tatekae = array();
$field = array();
if ($rows=getResultWithField2($query_s, $field, $res_tatekae) <= 0) {
    $tatekae_num = 0;
} else {
    $tatekae_num = count($res_tatekae);
}
// ̤ʧ���������
$query = "select
                expense_ym   as ǯ��,
                expense_kuri as ���۹�,
                expense_kei  as ����ȯ����,
                expense_kai  as �����ù�,
                expense_zan  as �Ĺ�
          from
                link_trans_expense_history";
$search    = "where expense_code='$customer' and expense_kamoku='̤ʧ��' and expense_ym>=$str_ym and expense_ym<=$end_ym ORDER BY expense_ym ASC";
$query_s   = sprintf("$query %s", $search);     // SQL query ʸ�δ���
$res_mihakin = array();
$field = array();
if ($rows=getResultWithField2($query_s, $field, $res_mihakin) <= 0) {
    $mihakin_num = 0;
} else {
    $mihakin_num = count($res_mihakin);
}
// ̤ʧ���ѷ�������
$query = "select
                expense_ym   as ǯ��,
                expense_kuri as ���۹�,
                expense_kei  as ����ȯ����,
                expense_kai  as �����ù�,
                expense_zan  as �Ĺ�
          from
                link_trans_expense_history";
$search    = "where expense_code='$customer' and expense_kamoku='̤ʧ����' and expense_ym>=$str_ym and expense_ym<=$end_ym ORDER BY expense_ym ASC";
$query_s   = sprintf("$query %s", $search);     // SQL query ʸ�δ���
$res_mihahiyo = array();
$field = array();
if ($rows=getResultWithField2($query_s, $field, $res_mihahiyo) <= 0) {
    $mihahiyo_num = 0;
} else {
    $mihahiyo_num = count($res_mihahiyo);
}
// NKIT�Τ�ͭ���ٵ�̤�������������
if ($customer == '00101') {
    $query = "select
                    expense_ym   as ǯ��,
                    expense_kuri as ���۹�,
                    expense_kei  as ����ȯ����,
                    expense_kai  as �����ù�,
                    expense_zan  as �Ĺ�
              from
                    link_trans_expense_history";
    $search    = "where expense_code='$customer' and expense_kamoku='ͭ���ٵ�̤������' and expense_ym>=$str_ym and expense_ym<=$end_ym ORDER BY expense_ym ASC";
    $query_s   = sprintf("$query %s", $search);     // SQL query ʸ�δ���
    $res_yumishu = array();
    $field = array();
    if ($rows=getResultWithField2($query_s, $field, $res_yumishu) <= 0) {
        $yumishu_num = 0;
    } else {
        $yumishu_num = count($res_yumishu);
    }
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
<body onLoad='set_focus()'>
    <center>
<?php echo $menu->out_title_border()?>
        
        <!--------------- ����������ʸ��ɽ��ɽ������ -------------------->
        <table bgcolor='#d6d3ce' align='center' border='1' cellspacing='0' cellpadding='3'>
            <tr><td> <!----------- ���ߡ�(�ǥ�������) ------------>
        <table class='winbox_field' width='100%' bgcolor='#d6d3ce' align='center' border='1' cellspacing='0' cellpadding='3'>
            <thead>
                <!-- �ơ��֥� �إå�����ɽ�� -->
                <tr>
                    <th class='winbox' nowrap rowspan='2'>��</th>
                    <th class='winbox' nowrap colspan='4'>��ݶ�</th>
                    <th class='winbox' nowrap colspan='4'>��ݶ�</th>
                </tr>
                <tr>
                    <th class='winbox' nowrap>��鷫��</th>
                    <th class='winbox' nowrap>��ݶ�׾�</th>
                    <th class='winbox' nowrap>��ݶ�����</th>
                    <th class='winbox' nowrap>�����Ĺ�</th>
                    <th class='winbox' nowrap>��鷫��</th>
                    <th class='winbox' nowrap>��ݶ�׾�</th>
                    <th class='winbox' nowrap>��ݶ��껦���</th>
                    <th class='winbox' nowrap>�����Ĺ�</th>
                </tr>
            </thead>
            <tfoot>
                <!-- ���ߤϥեå����ϲ���ʤ� -->
            </tfoot>
            <tbody>
            <?php
            $mm = 4;
            for ($r=0; $r<12; $r++) {   // ��ǯ�֤���ɽ��
                echo "<tr>\n";
                echo "  <th class='winbox' nowrap align='right'><span class='pt9'>{$mm}��</span></td>\n";
                // ��ݶ� ���� �׾� ��� �Ĺ�ν�
                if ($r >= $urikake_num) {
                    echo "  <td class='winbox' nowrap align='right'><span class='pt9'>��</span></td>\n";
                    echo "  <td class='winbox' nowrap align='right'><span class='pt9'>��</span></td>\n";
                    echo "  <td class='winbox' nowrap align='right'><span class='pt9'>��</span></td>\n";
                    echo "  <td class='winbox' nowrap align='right'><span class='pt9'>��</span></td>\n";
                } else {
                    echo "  <td class='winbox' nowrap align='right'><span class='pt9'>" . number_format($res_urikake[$r][1]) ."</span></td>\n";
                    echo "  <td class='winbox' nowrap align='right'><span class='pt9'>" . number_format($res_urikake[$r][2]) ."</span></td>\n";
                    echo "  <td class='winbox' nowrap align='right'><span class='pt9'>" . number_format($res_urikake[$r][3]) ."</span></td>\n";
                    echo "  <td class='winbox' nowrap align='right'><span class='pt9'>" . number_format($res_urikake[$r][4]) ."</span></td>\n";
                }
                // ��ݶ� ���� �׾� ��� �Ĺ�ν�
                if ($r >= $kaikake_num) {
                    echo "  <td class='winbox' nowrap align='right'><span class='pt9'>��</span></td>\n";
                    echo "  <td class='winbox' nowrap align='right'><span class='pt9'>��</span></td>\n";
                    echo "  <td class='winbox' nowrap align='right'><span class='pt9'>��</span></td>\n";
                    echo "  <td class='winbox' nowrap align='right'><span class='pt9'>��</span></td>\n";
                } else {
                    echo "  <td class='winbox' nowrap align='right'><span class='pt9'>" . number_format($res_kaikake[$r][1]) ."</span></td>\n";
                    echo "  <td class='winbox' nowrap align='right'><span class='pt9'>" . number_format($res_kaikake[$r][2]) ."</span></td>\n";
                    echo "  <td class='winbox' nowrap align='right'><span class='pt9'>" . number_format($res_kaikake[$r][3]) ."</span></td>\n";
                    echo "  <td class='winbox' nowrap align='right'><span class='pt9'>" . number_format($res_kaikake[$r][4]) ."</span></td>\n";
                }
                echo "</tr>\n";
                if($mm == 12){
                    $mm = 1;
                } else {
                    $mm += 1;
                }
            }
            ?>
            
            </tbody>
        </table>
            </td></tr>
        </table> <!----------------- ���ߡ�End ------------------>
        <BR>
        <!--------------- ����������ʸ��ɽ��ɽ������ -------------------->
        <table bgcolor='#d6d3ce' align='center' border='1' cellspacing='0' cellpadding='3'>
            <tr><td> <!----------- ���ߡ�(�ǥ�������) ------------>
        <table class='winbox_field' width='100%' bgcolor='#d6d3ce' align='center' border='1' cellspacing='0' cellpadding='3'>
            <thead>
                <!-- �ơ��֥� �إå�����ɽ�� -->
                <tr>
                    <th class='winbox' nowrap rowspan='2'>��</th>
                    <th class='winbox' nowrap colspan='4'>̤������</th>
                    <th class='winbox' nowrap colspan='4'>Ω�ض�</th>
                </tr>
                <tr>
                    <th class='winbox' nowrap>���۹�</th>
                    <th class='winbox' nowrap>����ȯ����</th>
                    <th class='winbox' nowrap>�����ù�</th>
                    <th class='winbox' nowrap>�Ĺ�</th>
                    <th class='winbox' nowrap>���۹�</th>
                    <th class='winbox' nowrap>����ȯ����</th>
                    <th class='winbox' nowrap>�����ù�</th>
                    <th class='winbox' nowrap>�Ĺ�</th>
                </tr>
            </thead>
            <tfoot>
                <!-- ���ߤϥեå����ϲ���ʤ� -->
            </tfoot>
            <tbody>
            <?php
            $mm = 4;
            for ($r=0; $r<12; $r++) {   // ��ǯ�֤���ɽ��
                echo "<tr>\n";
                echo "  <th class='winbox' nowrap align='right'><span class='pt9'>{$mm}��</span></td>\n";
                // ̤������ ���� �׾� ��� �Ĺ�ν�
                if ($r >= $mishu_num) {
                    echo "  <td class='winbox' nowrap align='right'><span class='pt9'>��</span></td>\n";
                    echo "  <td class='winbox' nowrap align='right'><span class='pt9'>��</span></td>\n";
                    echo "  <td class='winbox' nowrap align='right'><span class='pt9'>��</span></td>\n";
                    echo "  <td class='winbox' nowrap align='right'><span class='pt9'>��</span></td>\n";
                } else {
                    echo "  <td class='winbox' nowrap align='right'><span class='pt9'>" . number_format($res_mishu[$r][1]) ."</span></td>\n";
                    echo "  <td class='winbox' nowrap align='right'><span class='pt9'>" . number_format($res_mishu[$r][2]) ."</span></td>\n";
                    echo "  <td class='winbox' nowrap align='right'><span class='pt9'>" . number_format($res_mishu[$r][3]) ."</span></td>\n";
                    echo "  <td class='winbox' nowrap align='right'><span class='pt9'>" . number_format($res_mishu[$r][4]) ."</span></td>\n";
                }
                // Ω�ض� ���� �׾� ��� �Ĺ�ν�
                if ($r >= $tatekae_num) {
                    echo "  <td class='winbox' nowrap align='right'><span class='pt9'>��</span></td>\n";
                    echo "  <td class='winbox' nowrap align='right'><span class='pt9'>��</span></td>\n";
                    echo "  <td class='winbox' nowrap align='right'><span class='pt9'>��</span></td>\n";
                    echo "  <td class='winbox' nowrap align='right'><span class='pt9'>��</span></td>\n";
                } else {
                    echo "  <td class='winbox' nowrap align='right'><span class='pt9'>" . number_format($res_tatekae[$r][1]) ."</span></td>\n";
                    echo "  <td class='winbox' nowrap align='right'><span class='pt9'>" . number_format($res_tatekae[$r][2]) ."</span></td>\n";
                    echo "  <td class='winbox' nowrap align='right'><span class='pt9'>" . number_format($res_tatekae[$r][3]) ."</span></td>\n";
                    echo "  <td class='winbox' nowrap align='right'><span class='pt9'>" . number_format($res_tatekae[$r][4]) ."</span></td>\n";
                }
                echo "</tr>\n";
                if($mm == 12){
                    $mm = 1;
                } else {
                    $mm += 1;
                }
            }
            ?>
            
            </tbody>
        </table>
            </td></tr>
        </table> <!----------------- ���ߡ�End ------------------>
        <BR>
        <!--------------- ����������ʸ��ɽ��ɽ������ -------------------->
        <table bgcolor='#d6d3ce' align='center' border='1' cellspacing='0' cellpadding='3'>
            <tr><td> <!----------- ���ߡ�(�ǥ�������) ------------>
        <table class='winbox_field' width='100%' bgcolor='#d6d3ce' align='center' border='1' cellspacing='0' cellpadding='3'>
            <thead>
                <!-- �ơ��֥� �إå�����ɽ�� -->
                <tr>
                    <th class='winbox' nowrap rowspan='2'>��</th>
                    <th class='winbox' nowrap colspan='4'>̤ʧ��</th>
                    <th class='winbox' nowrap colspan='4'>̤ʧ����</th>
                </tr>
                <tr>
                    <th class='winbox' nowrap>���۹�</th>
                    <th class='winbox' nowrap>����ȯ����</th>
                    <th class='winbox' nowrap>�����ù�</th>
                    <th class='winbox' nowrap>�Ĺ�</th>
                    <th class='winbox' nowrap>���۹�</th>
                    <th class='winbox' nowrap>����ȯ����</th>
                    <th class='winbox' nowrap>�����ù�</th>
                    <th class='winbox' nowrap>�Ĺ�</th>
                </tr>
            </thead>
            <tfoot>
                <!-- ���ߤϥեå����ϲ���ʤ� -->
            </tfoot>
            <tbody>
            <?php
            $mm = 4;
            for ($r=0; $r<12; $r++) {   // ��ǯ�֤���ɽ��
                echo "<tr>\n";
                echo "  <th class='winbox' nowrap align='right'><span class='pt9'>{$mm}��</span></td>\n";
                // ̤ʧ�� ���� �׾� ��� �Ĺ�ν�
                if ($r >= $mihakin_num) {
                    echo "  <td class='winbox' nowrap align='right'><span class='pt9'>��</span></td>\n";
                    echo "  <td class='winbox' nowrap align='right'><span class='pt9'>��</span></td>\n";
                    echo "  <td class='winbox' nowrap align='right'><span class='pt9'>��</span></td>\n";
                    echo "  <td class='winbox' nowrap align='right'><span class='pt9'>��</span></td>\n";
                } else {
                    echo "  <td class='winbox' nowrap align='right'><span class='pt9'>" . number_format($res_mihakin[$r][1]) ."</span></td>\n";
                    echo "  <td class='winbox' nowrap align='right'><span class='pt9'>" . number_format($res_mihakin[$r][2]) ."</span></td>\n";
                    echo "  <td class='winbox' nowrap align='right'><span class='pt9'>" . number_format($res_mihakin[$r][3]) ."</span></td>\n";
                    echo "  <td class='winbox' nowrap align='right'><span class='pt9'>" . number_format($res_mihakin[$r][4]) ."</span></td>\n";
                }
                // ̤ʧ���� ���� �׾� ��� �Ĺ�ν�
                if ($r >= $mihahiyo_num) {
                    echo "  <td class='winbox' nowrap align='right'><span class='pt9'>��</span></td>\n";
                    echo "  <td class='winbox' nowrap align='right'><span class='pt9'>��</span></td>\n";
                    echo "  <td class='winbox' nowrap align='right'><span class='pt9'>��</span></td>\n";
                    echo "  <td class='winbox' nowrap align='right'><span class='pt9'>��</span></td>\n";
                } else {
                    echo "  <td class='winbox' nowrap align='right'><span class='pt9'>" . number_format($res_mihahiyo[$r][1]) ."</span></td>\n";
                    echo "  <td class='winbox' nowrap align='right'><span class='pt9'>" . number_format($res_mihahiyo[$r][2]) ."</span></td>\n";
                    echo "  <td class='winbox' nowrap align='right'><span class='pt9'>" . number_format($res_mihahiyo[$r][3]) ."</span></td>\n";
                    echo "  <td class='winbox' nowrap align='right'><span class='pt9'>" . number_format($res_mihahiyo[$r][4]) ."</span></td>\n";
                }
                echo "</tr>\n";
                if($mm == 12){
                    $mm = 1;
                } else {
                    $mm += 1;
                }
            }
            ?>
            </tbody>
        </table>
            </td></tr>
        </table> <!----------------- ���ߡ�End ------------------>
        <?php
        if ($customer == '00101') {
        ?>
        <BR>
        <!--------------- ����������ʸ��ɽ��ɽ������ -------------------->
        <table bgcolor='#d6d3ce' align='center' border='1' cellspacing='0' cellpadding='3'>
            <tr><td> <!----------- ���ߡ�(�ǥ�������) ------------>
        <table class='winbox_field' width='100%' bgcolor='#d6d3ce' align='center' border='1' cellspacing='0' cellpadding='3'>
            <thead>
                <!-- �ơ��֥� �إå�����ɽ�� -->
                <tr>
                    <th class='winbox' nowrap rowspan='2'>��</th>
                    <th class='winbox' nowrap colspan='4'>ͭ���ٵ�̤������</th>
                </tr>
                <tr>
                    <th class='winbox' nowrap>���۹�</th>
                    <th class='winbox' nowrap>����ȯ����</th>
                    <th class='winbox' nowrap>�����ù�</th>
                    <th class='winbox' nowrap>�Ĺ�</th>
                </tr>
            </thead>
            <tfoot>
                <!-- ���ߤϥեå����ϲ���ʤ� -->
            </tfoot>
            <tbody>
            <?php
            $mm = 4;
            for ($r=0; $r<12; $r++) {   // ��ǯ�֤���ɽ��
                echo "<tr>\n";
                echo "  <th class='winbox' nowrap align='right'><span class='pt9'>{$mm}��</span></td>\n";
                // ͭ���ٵ�̤������ ���� �׾� ��� �Ĺ�ν�
                if ($r >= $yumishu_num) {
                    echo "  <td class='winbox' nowrap align='right'><span class='pt9'>��</span></td>\n";
                    echo "  <td class='winbox' nowrap align='right'><span class='pt9'>��</span></td>\n";
                    echo "  <td class='winbox' nowrap align='right'><span class='pt9'>��</span></td>\n";
                    echo "  <td class='winbox' nowrap align='right'><span class='pt9'>��</span></td>\n";
                } else {
                    echo "  <td class='winbox' nowrap align='right'><span class='pt9'>" . number_format($res_yumishu[$r][1]) ."</span></td>\n";
                    echo "  <td class='winbox' nowrap align='right'><span class='pt9'>" . number_format($res_yumishu[$r][2]) ."</span></td>\n";
                    echo "  <td class='winbox' nowrap align='right'><span class='pt9'>" . number_format($res_yumishu[$r][3]) ."</span></td>\n";
                    echo "  <td class='winbox' nowrap align='right'><span class='pt9'>" . number_format($res_yumishu[$r][4]) ."</span></td>\n";
                }
                echo "</tr>\n";
                if($mm == 12){
                    $mm = 1;
                } else {
                    $mm += 1;
                }
            }
            ?>
            </tbody>
        </table>
            </td></tr>
        </table> <!----------------- ���ߡ�End ------------------>
        <?php
        }
        ?>
    </center>
</body>
<?php echo $menu->out_alert_java()?>
</html>
<?php
// ob_end_flush();                 // ���ϥХåե���gzip���� END
?>
