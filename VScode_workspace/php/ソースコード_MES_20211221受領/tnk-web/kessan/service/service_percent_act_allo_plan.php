<?php
//////////////////////////////////////////////////////////////////////////////
// �����ӥ������� ���Τγ��(����Ψ)������¤��������� ͽ¬������         //
// Copyright(C) 2003-2004 2007 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp  //
// Changed history                                                          //
// 2003/10/29 Created   service_percent_act_allo_plan.php                   //
// 2003/10/29 $per���η׻���̤򾮿����ʲ�����򣵷���ѹ�100%���к�      //
//            number_format()�򣳷夫�飱����ѹ�                           //
// 2003/10/30 ͽ¬�����Ѥ��ѹ� WHERE act_yymm<=0304 and act_yymm>=0309      //
//            195���ܤ˥�ƥ��ǥ����ǥ��󥰤���Ƥ���                     //
// 2004/04/16 ��¤��������٤���������ʤ�-->��������¤��������� ��Msg�ѹ� //
//            ����ߥ졼������ϰϤ�$service_ym�򥵡��ӥ����δ��ˤ���   //
//            ��Ⱦ������¤���������Ψ�ڤ������ۤ򻻽Ф���褦���ѹ�      //
// 2004/05/12 �����ȥ�˥塼ɽ������ɽ�� �ܥ����ɲ� menu_OnOff($script)�ɲ� //
// 2007/01/24 MenuHeader���饹�б�                                          //
//////////////////////////////////////////////////////////////////////////////
// ini_set('error_reporting', E_STRICT);       // E_STRICT=2048(php5) E_ALL=2047 debug ��
ini_set('error_reporting',E_ALL);           // E_ALL='2047' debug ��
// ini_set('display_errors','1');              // Error ɽ�� ON debug �� 
ob_start('ob_gzhandler');                   // ���ϥХåե���gzip����
session_start();                            // ini_set()�μ��˻��ꤹ�뤳�� Script �Ǿ��

require_once ('../../function.php');
require_once ('../../tnk_func.php');
require_once ('../../MenuHeader.php');      // TNK ������ menu class
access_log();                               // Script Name �ϼ�ư����

///// TNK ���ѥ�˥塼���饹�Υ��󥹥��󥹤����
$menu = new MenuHeader(0);                  // ǧ�ڥ����å�0=���̰ʾ� �����=TOP_MENU �����ȥ�̤����

////////////// ����������
$menu->set_site(10,  5);                    // site_index=10(»�ץ�˥塼) site_id=5(�����ӥ�����˥塼)
////////////// �꥿���󥢥ɥ쥹����(���л��ꤹ����)
$menu->set_RetUrl($_SESSION['service_referer']);    // ʬ������������¸����Ƥ���ƽи��򥻥åȤ���
//////////// �ƽ����action̾�ȥ��ɥ쥹����
// $menu->set_action('test_template',   SYS . 'log_view/php_error_log.php');

$current_script  = $_SERVER['PHP_SELF'];        // ���߼¹���Υ�����ץ�̾����¸
$url_referer     = $_SESSION['service_referer'];    // ʬ������������¸����Ƥ���ƽи��򥻥åȤ���

////////////// ǧ�ڥ����å�
if (account_group_check() == FALSE) {
    $_SESSION["s_sysmsg"] = "���ʤ��ϵ��Ĥ���Ƥ��ޤ���<br>�����Ԥ�Ϣ���Ʋ�������";
    header("Location: $url_referer");                   // ľ���θƽи������
// if (!isset($_SESSION["User_ID"]) || !isset($_SESSION["Password"]) || !isset($_SESSION["Auth"])) {
//    $_SESSION["s_sysmsg"] = "ǧ�ڤ���Ƥ��ʤ���ǧ�ڴ��¤��ڤ�ޤ����������󤫤餪�ꤤ���ޤ���";
//    header("Location: http:" . WEB_HOST . "menu.php");
    exit();
}

//////////// �о�ǯ��Υ��å����ǡ�������
if (isset($_SESSION['service_ym'])) {
    $service_ym = $_SESSION['service_ym']; 
} else {
    $service_ym = date('Ym');        // ���å����ǡ������ʤ����ν����(����)
    if (substr($service_ym,4,2) != 01) {
        $service_ym--;
    } else {
        $service_ym = $service_ym - 100;
        $service_ym = $service_ym + 11;   // ��ǯ��12��˥��å�
    }
}

//////////// �����ȥ�����ա���������
$today = date("Y/m/d H:i:s");
//////////// �����ȥ�̾(�������Υ����ȥ�̾�ȥե�����Υ����ȥ�̾)
if (substr($service_ym,6,2) == '32') {
    $view_ym = substr($service_ym,0,6) . '�軻';
} else {
    $view_ym = $service_ym;
}
$menu_title = "$view_ym �����ӥ����ˤ����¤������ ���� ����ߥ졼�����";
//////////// �����ȥ�̾(�������Υ����ȥ�̾�ȥե�����Υ����ȥ�̾)
$menu->set_title($menu_title);
//////////// ɽ�������
$menu->set_caption('�� ¤ �� �񡡴� �� �񡡤Ρ��� �� �� �ۡ��� ��');

///// ��Ⱦ���� ǯ��λ���
$yyyy = substr($service_ym, 0,4);
$mm   = substr($service_ym, 4,2);
if (($mm >= 1) && ($mm <= 3)) {
    $yyyy = ($yyyy - 1);
    $end_zenki_ym = $yyyy . '09';   // ��Ⱦ���� ǯ��
    $str_zenki_ym = $yyyy . '04';   // ��Ⱦ���� ǯ��
} elseif (($mm >= 10) && ($mm <= 12)) {
    $end_zenki_ym = $yyyy . '09';   // ��Ⱦ���� ǯ��
    $str_zenki_ym = $yyyy . '04';   // ��Ⱦ���� ǯ��
} else {
    $end_zenki_ym = $yyyy . '03';   // ��Ⱦ���� ǯ��
    $yyyy = $yyyy - 1;
    $str_zenki_ym = $yyyy . '10';   // ��Ⱦ���� ǯ��
}       // �ʲ��� $service_ym �� $end_zenki_ym ��4�ս��ִ���

////////// �ǡ����١����ؤ���³
if ( !($con = db_connect()) ) {
    $_SESSION['s_sysmsg'] = '�ǡ����١�������³�Ǥ��ޤ���';
    header("Location: $url_referer");                   // ľ���θƽи������
    exit();
}

//////////// item(ľ������)��ι�פ�ȴ�Ф� intext=1 ���������
$query = "SELECT item_no, item, sum(percent * 100)::int2 FROM service_percent_history WHERE service_ym=$end_zenki_ym and intext=1 group by item_no, item order by item_no";
if (($rows_fld1 = getResultTrs($con, $query, $res)) <= 0) {
    $_SESSION['s_sysmsg'] = 'ľ��������ι�פ������Ǥ��ޤ���';
    header("Location: $url_referer");                   // ľ���θƽи������
    exit();
} else {
    for ($i=0; $i<$rows_fld1; $i++) {
        $field1[$i] = $res[$i][1];      // �ե������̾
    }
}
//////////// item(ľ������)��ι�פ�ȴ�Ф� intext=2 Ĵã������ 
$query = "SELECT item_no, item, sum(percent * 100)::int2 FROM service_percent_history WHERE service_ym=$end_zenki_ym and intext=2 group by item_no, item order by item_no";
if (($rows_fld2 = getResultTrs($con, $query, $res)) <= 0) {
    $_SESSION['s_sysmsg'] = 'ľ��������ι�פ������Ǥ��ޤ���';
    header("Location: $url_referer");                   // ľ���θƽи������
    exit();
} else {
    for ($i=0; $i<$rows_fld2; $i++) {
        $field2[$i] = $res[$i][1];      // �ե������̾
    }
}


//////////// ����(act_id)�ڤ� item_no ��ι�פ�ȴ�Ф�(����ɽ����)
$query = "SELECT act_id, trim(s_name), item_no, item, sum(percent * 100)::int2
         FROM service_percent_history left outer join act_table using(act_id)
         WHERE service_ym=$service_ym
         group by act_id, s_name, item_no, item, intext
         order by act_id, intext, item_no";
$res = array();
if (($rows = getResultTrs($con, $query, $res)) <= 0) {
    $_SESSION['s_sysmsg'] = "ǯ�$service_ym <br>�����ӥ���礬���Ϥ���Ƥ��ޤ���";
    header("Location: $url_referer");                   // ľ���θƽи������
    exit();
} else {
    $_SESSION['s_sysmsg'] = '';     // �����
    ///// ľ������Υե�����ɿ���˥쥳���ɤ����
    $res[-1][0] = '';   // �������Ȼ��ν����
    $j = -1;            // ����ǥå����ν����
    for ($i=0; $i<$rows; $i++) {
        if ($res[$i-1][0] != $res[$i][0]) {
            $j++;
            $act_id[$j]   = $res[$i][0];
            $act_name[$j] = $res[$i][1];
            $k = 0;
            $act_poi[$j][$k]  = $res[$i][4];
        } else {
            $k++;
            $act_poi[$j][$k]  = $res[$i][4];
        }
    }
    $rows_mei = ($j + 1);
    ////////// ����������ι�ץݥ���ȿ��η׻�
    $act_poisum1 = 0;
    $act_poisum2 = 0;
    $act_poisum  = 0;
    for ($r=0; $r<$rows_mei; $r++) {
        $act_poi[$r]['sum1'] = 0;
        for ($f=0; $f<$rows_fld1; $f++) {
            $act_poi[$r]['sum1'] += $act_poi[$r][$f];
        }
        $act_poi[$r]['sum2'] = 0;
        for ($f=$rows_fld1; $f<($rows_fld1+$rows_fld2); $f++) {
            $act_poi[$r]['sum2'] += $act_poi[$r][$f];
        }
        $act_poi_sum[$r] = $act_poi[$r]['sum1'] + $act_poi[$r]['sum2'];
        $act_poisum1 += $act_poi[$r]['sum1'];
        $act_poisum2 += $act_poi[$r]['sum2'];
        $act_poisum  += $act_poi_sum[$r];
    }
    ////////// ���������������Ψ�η׻�
    $act_persum1 = 0;   // ���Τι�������� ��� �����
    $act_persum2 = 0;   // ���Τ�Ĵã������ ��� �����
    $act_persum  = 0;   // ���Τ� ��� �����
    for ($r=0; $r<$rows_mei; $r++) {
            ////////// ���������
        $act_per[$r]['sum1'] = 0;
        for ($f=0; $f<$rows_fld1; $f++) {
            if ($act_poi[$r][$f] != 0) {
                $act_per[$r][$f] = Uround($act_poi[$r][$f] / $act_poi_sum[$r], 4);
            } else {
                $act_per[$r][$f] = 0;
            }
            $act_per[$r]['sum1'] += $act_per[$r][$f];
            $act_per_f[$r][$f] = number_format($act_per[$r][$f] * 100, 2);
        }
        $act_per_f[$r]['sum1'] = number_format($act_per[$r]['sum1'] * 100, 2);
            ////////// Ĵã������
        $act_per[$r]['sum2'] = 0;
        for ($f=$rows_fld1; $f<($rows_fld1+$rows_fld2); $f++) {
            if ($act_poi[$r][$f] != 0) {
                $act_per[$r][$f] = Uround($act_poi[$r][$f] / $act_poi_sum[$r], 4);
            } else {
                $act_per[$r][$f] = 0;
            }
            $act_per[$r]['sum2'] += $act_per[$r][$f];
            $act_per_f[$r][$f] = number_format($act_per[$r][$f] * 100, 2);
        }
        $act_per_f[$r]['sum2'] = number_format($act_per[$r]['sum2'] * 100, 2);
            ////////// act_id ��ι��
        $act_per_sum[$r] = $act_per[$r]['sum1'] + $act_per[$r]['sum2'];
        $act_per_sum_f[$r] = number_format($act_per_sum[$r] * 100, 2);
            ////////// ���Τι�� �׻�
        $act_persum1 += $act_per[$r]['sum1'];
        $act_persum2 += $act_per[$r]['sum2'];
        $act_persum  += $act_per_sum[$r];
        $act_persum1_f = number_format($act_persum1 * 100, 2);
        $act_persum2_f = number_format($act_persum2 * 100, 2);
        $act_persum_f  = number_format($act_persum * 100, 2);
    }
}


////////// ��¤�����act_id������ټ����ڤӹ�׷׻�
$act_yymm = substr($end_zenki_ym, 2, 4);
$str_zen_yymm = substr($str_zenki_ym, 2, 4);        // ��Ⱦ���������
$end_zen_yymm = substr($end_zenki_ym, 2, 4);        // ��Ⱦ���������
$act_kin = array();
$act_kin['sum'] = 0;    // ��פΤ���ν����
for ($i=0; $i<$rows_mei; $i++) {            // �ʲ��ϥ���ߥ졼������Ѥ������ǡ������ƥ��ǻ��ꤹ��
    $query = sprintf("SELECT  sum(act_monthly) FROM act_summary
                      WHERE act_yymm<={$end_zen_yymm} and act_yymm>={$str_zen_yymm} and act_id=%d group by act_id",
                      $act_id[$i]);         // �嵭�������ǡ��������Ϥ���
    if (($rows = getUniResTrs($con, $query, $act_kin[$i])) <= 0) {
        $_SESSION['s_sysmsg'] .= "<font color='yellow'>{$act_yymm}��{$act_id[$i]}��{$act_name[$i]}����������¤��������٤������Ǥ��ޤ���</font>";
        $act_kin[$i] = 0;       // ���������礬�ʤ���礬����Τǣ��ߤ������
    }
    $act_kin['sum'] += $act_kin[$i];    // ��¤���� ��פη׻�
    $act_kin_f[$i] = number_format($act_kin[$i]);   // ���祳������ι�׶�� ɽ��
    $act_allo[$i]['sum1'] = 0;      // ��פΤ���ν����(�쥳����)
    $act_allo[$i]['sum2'] = 0;      // ��פΤ���ν����
    $act_allo[$i]['sum']  = 0;      // ��פΤ���ν����
    for ($f=0; $f<$rows_fld1; $f++) {
        $act_allo[$i][$f] = Uround($act_per[$i][$f] * $act_kin[$i], 0);     // ���祳������������� ���������
        $act_allo_f[$i][$f] = number_format($act_allo[$i][$f]);             // ɽ����
        $act_allo[$i]['sum1'] += $act_allo[$i][$f];
    }
    for ($f=$rows_fld1; $f<($rows_fld1+$rows_fld2); $f++) {
        $act_allo[$i][$f] = Uround($act_per[$i][$f] * $act_kin[$i], 0);     // ���祳������������� Ĵã������
        $act_allo_f[$i][$f] = number_format($act_allo[$i][$f]);             // ɽ����
        $act_allo[$i]['sum2'] += $act_allo[$i][$f];
    }
    $act_allo[$i]['sum']  = $act_allo[$i]['sum1'] + $act_allo[$i]['sum2'];
    $act_allo_f[$i]['sum1'] = number_format($act_allo[$i]['sum1']);
    $act_allo_f[$i]['sum2'] = number_format($act_allo[$i]['sum2']);
    $act_allo_f[$i]['sum']  = number_format($act_allo[$i]['sum']);
}
$act_kin_sum_f = number_format($act_kin['sum']);

////////// field(ľ������)��˶�ۤ�׻�����
for ($f=0; $f<($rows_fld1+$rows_fld2); $f++) {
    $act_fld[$f] = 0;   // �����
}
for ($i=0; $i<$rows_mei; $i++) {
    for ($f=0; $f<($rows_fld1+$rows_fld2); $f++) {
        $act_fld[$f]  += $act_allo[$i][$f];     // �ե�����ɤ����쥳���ɹ�׻���
    }
}
$act_fld_sum1 = 0;   // �����
$act_fld_sum2 = 0;   // �����
for ($f=0; $f<$rows_fld1; $f++) {
    $act_fld_sum1 += $act_fld[$f];          // ����(���������)
}
for ($f=$rows_fld1; $f<($rows_fld1+$rows_fld2); $f++) {
    $act_fld_sum2 += $act_fld[$f];          // ����(Ĵã������)
}
$act_fld_sum1_f = number_format($act_fld_sum1);     // ɽ����(����)���������
$act_fld_sum2_f = number_format($act_fld_sum2);     // ɽ����(����)Ĵã������
$act_fld_sum = 0;   // �����
for ($f=0; $f<($rows_fld1+$rows_fld2); $f++) {
    $act_fld_sum += $act_fld[$f];                   // ���׻���
    $act_fld_f[$f] = number_format($act_fld[$f]);   // ɽ����(�ե������ñ��)
}
$act_fld_sum_f = number_format($act_fld_sum);       // ɽ����(����)

////////// ��ۤ���ľ�������������Ψ�򻻽�
$act_per_all_sum1 = 0;
$act_per_all_sum2 = 0;
for ($f=0; $f<$rows_fld1; $f++) {                                   // ���������
    $act_per_all[$f] = Uround($act_fld[$f] / $act_fld_sum, 5);          // ľ����������Ψ
    $act_per_all_f[$f] = number_format($act_per_all[$f] * 100, 1);      // ľ����������Ψ(ɽ����)
    $act_per_all_sum1 += $act_per_all[$f];                              // ����
}
for ($f=$rows_fld1; $f<($rows_fld1+$rows_fld2); $f++) {             // Ĵã������
    $act_per_all[$f] = Uround($act_fld[$f] / $act_fld_sum, 5);          // ľ����������Ψ
    $act_per_all_f[$f] = number_format($act_per_all[$f] * 100, 1);      // ľ����������Ψ(ɽ����)
    $act_per_all_sum2 += $act_per_all[$f];                              // ����
}
$act_per_all_sum1_f = number_format($act_per_all_sum1 * 100, 1);        // ����(ɽ����)���������
$act_per_all_sum2_f = number_format($act_per_all_sum2 * 100, 1);        // ����(ɽ����)Ĵã������
$act_per_all_sum   = $act_per_all_sum1 + $act_per_all_sum2;             // ���
$act_per_all_sum_f = number_format($act_per_all_sum * 100, 1);          // ���(ɽ����)


/////////// HTML Header ����Ϥ��ƥ���å��������
$menu->out_html_header();
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=EUC-JP">
<meta http-equiv="Content-Style-Type" content="text/css">
<title><?php echo $menu->out_title() ?></title>
<?php echo $menu->out_site_java() ?>
<?php echo $menu->out_css() ?>
<?php echo $menu->out_jsBaseClass() ?>
<style type="text/css">
<!--
select {
    background-color:teal;
    color:white;
}
textarea {
    background-color:black;
    color:white;
}
input.sousin {
    background-color:red;
}
input.text {
    background-color:black;
    color:white;
}
.pt10 {
    font:10pt;
}
.pt10b {
    font:bold 10pt;
}
.pt11bR {
    font:bold 11pt;
    color: red;
    font-family: monospace;
}
.pt11b {
    font:bold 11pt;
}
.pt12b {
    font:bold 12pt;
    font-family: monospace;
}
th {
    font:bold 11pt;
}
.title-font {
    font:bold 13.5pt;
    font-family: monospace;
    border-top:1.0pt solid windowtext;
    border-right:none;
    border-bottom:1.0pt solid windowtext;
    border-left:0.5pt solid windowtext;
}
.today-font {
    font-size: 10.5pt;
    font-family: monospace;
    border-top:1.0pt solid windowtext;
    border-right:1.0pt solid windowtext;
    border-bottom:1.0pt solid windowtext;
    border-left:0.5pt solid windowtext;
}
.explain_font {
    font-size: 8.5pt;
    font-family: monospace;
}
.margin0 {
    margin:0%;
}
.right{
    text-align:right;
    font:bold 12pt;
    font-family: monospace;
}
-->
</style>
</head>
<body>
    <center>
<?php echo $menu->out_title_border() ?>
        
        <form name='page_form' method='post' action='<?php echo $menu->out_retUrl() ?>'>
        <!----------------- ������ ���� ���� �Υե����� ---------------->
        <table width='100%' cellspacing="0" cellpadding="0" border='0'>
            <tr>
                <td align='center'>
                    <!-- <?php echo $menu->out_caption() . "��ñ�̡���\n" ?> -->
                    <table align='center' border='3' cellspacing='0' cellpadding='0'>
                        <td align='center' valign='middle' class='pt11b'>
                            <input class='pt11b' type='submit' name='save' value=' �ϣ� '>
                            ñ�̡�Ψ=��,��=��
                        </td>
                    </table>
                </td>
            </tr>
        </table>
        
        <!--------------- �����������Τ�����Ψ��ɽ������ -------------------->
        <table bgcolor='#d6d3ce' align='center' cellspacing="0" cellpadding="3" border='1'>
            <tr><td> <!----------- ���ߡ�(�ǥ�������) ------------>
        <table bordercolordark='white' bordercolorlight='#bdaa90' bgcolor='#d6d3ce' align='center' cellspacing="0" cellpadding="3" border='1'>
            <THEAD>
                <!-- �ơ��֥� �إå�����ɽ�� -->
                <tr align='center' bgcolor='#beffbe'>
                    <td colspan='<?php echo $rows_fld1+$rows_fld2+7 ?>' class='pt11b'> <!-- colspan����20�ˤ��Ƥ��� -->
                        <?php echo $menu->out_caption(), "\n" ?>
                    </td>
                </tr>
            </THEAD>
            <TFOOT>
                <!-- ���ߤϥեå����ϲ���ʤ� -->
            </TFOOT>
            <TBODY>
                <tr>
                    <td rowspan='3' width='10' align='center' class='pt10' bgcolor='#ffcf9c'>���������</td>
                    <td width='10' align='center' class='pt10' bgcolor='#ffcf9c'>ľ��</td>
                    <?php for ($i=0; $i<$rows_fld1; $i++) { ?>
                        <td align='center' class='pt11b' bgcolor='#ffcf9c' nowrap><?php echo $field1[$i] ?></td>
                    <?php } ?>
                    <td align='center' class='pt10' bgcolor='#ffcf9c' nowrap>������</td>
                    <td rowspan='3' width='10' align='center' class='pt10' bgcolor='#ceceff'>Ĵã������</td>
                    <td width='10' align='center' class='pt10' bgcolor='#ceceff'>ľ��</td>
                    <?php for ($i=0; $i<$rows_fld2; $i++) { ?>
                        <td align='center' class='pt11b' bgcolor='#ceceff' nowrap><?php echo $field2[$i] ?></td>
                    <?php } ?>
                    <td align='center' class='pt10' bgcolor='#ceceff' nowrap>������</td>
                    <td align='center' class='pt10' bgcolor='#ffffbe' nowrap>�硡��</td>
                </tr>
                <tr>
                    <td width='10' align='center' class='pt10' bgcolor='#ffcf9c'>Ψ</td>
                    <?php for ($i=0; $i<$rows_fld1; $i++) { ?>
                        <td align='right' class='pt11b' bgcolor='#ffcf9c'><?php echo $act_per_all_f[$i] ?></td>
                    <?php } ?>
                    <td align='right' class='pt11b' bgcolor='#ffcf9c'><?php echo $act_per_all_sum1_f ?></td>
                    <td width='10' align='center' class='pt10' bgcolor='#ceceff'>Ψ</td>
                    <?php for ($i=$rows_fld1; $i<($rows_fld1+$rows_fld2); $i++) { ?>
                        <td align='right' class='pt11b' bgcolor='#ceceff'><?php echo $act_per_all_f[$i] ?></td>
                    <?php } ?>
                    <td align='right' class='pt11b' bgcolor='#ceceff'><?php echo $act_per_all_sum2_f ?></td>
                    <td align='right' class='pt11b' bgcolor='#ffffbe'><?php echo $act_per_all_sum_f ?></td>
                </tr>
                <tr>
                    <td width='10' align='center' class='pt10' bgcolor='#ffcf9c'>���</td>
                    <?php for ($i=0; $i<$rows_fld1; $i++) { ?>
                        <td align='right' class='pt11b' bgcolor='#ffcf9c'><?php echo $act_fld_f[$i] ?></td>
                    <?php } ?>
                    <td align='right' class='pt11b' bgcolor='#ffcf9c'><?php echo $act_fld_sum1_f ?></td>
                    <td width='10' align='center' class='pt10' bgcolor='#ceceff'>���</td>
                    <?php for ($i=$rows_fld1; $i<($rows_fld1+$rows_fld2); $i++) { ?>
                        <td align='right' class='pt11b' bgcolor='#ceceff'><?php echo $act_fld_f[$i] ?></td>
                    <?php } ?>
                    <td align='right' class='pt11b' bgcolor='#ceceff'><?php echo $act_fld_sum2_f ?></td>
                    <td align='right' class='pt11b' bgcolor='#ffffbe'><?php echo $act_fld_sum_f ?></td>
                </tr>
            </TBODY>
        </table>
            </td></tr>
        </table> <!----------------- ���ߡ�End ------------------>
        
        <hr>
        
        <!--------------- ��������������祳����������٤�ɽ������ -------------------->
        <table bgcolor='#d6d3ce' align='center' cellspacing="0" cellpadding="3" border='1'>
            <tr><td> <!----------- ���ߡ�(�ǥ�������) ------------>
        <table bordercolordark='white' bordercolorlight='#bdaa90' bgcolor='#d6d3ce' align='center' cellspacing="0" cellpadding="3" border='1'>
            <THEAD>
                <!-- �ơ��֥� �إå�����ɽ�� -->
                <tr align='center' bgcolor='#beffbe'>
                    <td colspan='<?php echo $rows_fld1+$rows_fld2+8 ?>' class='pt11b'> <!-- colspan����20�ˤ��Ƥ��� -->
                        �� ¤ �� �񡡴� �� �񡡤Ρ��� �� �� �ۡ����� ��
                    </td>
                </tr>
                <tr>
                    <td align='center' class='pt10' bgcolor='#ffffbe' nowrap>No</td>
                    <td align='center' class='pt10' bgcolor='#ffffbe' nowrap>������</td>
                    <td align='center' class='pt10' bgcolor='#ffffbe' nowrap>����̾</td>
                    <td align='center' class='pt10' bgcolor='#ffffbe'>��</td>
                    <?php for ($i=0; $i<$rows_fld1; $i++) { ?>
                        <td align='center' class='pt11b' bgcolor='#ffcf9c' nowrap><?php echo $field1[$i] ?></td>
                    <?php } ?>
                    <td align='center' class='pt10' bgcolor='#ffcf9c' nowrap>������</td>
                    <?php for ($i=0; $i<$rows_fld2; $i++) { ?>
                        <td align='center' class='pt11b' bgcolor='#ceceff' nowrap><?php echo $field2[$i] ?></td>
                    <?php } ?>
                    <td align='center' class='pt10' bgcolor='#ceceff' nowrap>������</td>
                    <td align='center' class='pt10' bgcolor='#ffffbe' nowrap>�硡��</td>
                    <td align='center' class='pt10' bgcolor='#ffffbe' nowrap>�������</td>
                </tr>
            </THEAD>
            <TFOOT>
                <!-- �եå����Ϲ�פ�ɽ�� -->
                <tr>
                    <td colspan='4' align='right' class='pt10' bgcolor='#ffffbe'>Ψ����</td>
                    <td colspan='<?php echo $rows_fld1+1 ?>' align='right' class='pt10' bgcolor='#ffcf9c'><?php echo $act_persum1_f ?></td>
                    <td colspan='<?php echo $rows_fld2+1 ?>' align='right' class='pt10' bgcolor='#ceceff'><?php echo $act_persum2_f ?></td>
                    <td align='right' class='pt10' bgcolor='#ffffbe'><?php echo $act_persum_f ?></td>
                    <td rowspan='2' align='right' class='pt10' bgcolor='#ffffbe'><?php echo $act_kin_sum_f ?></td>
                </tr>
                <tr>
                    <td colspan='4' align='right' class='pt11b' bgcolor='#ffffbe'>�硡��</td>
                    <?php for ($i=0; $i<$rows_fld1; $i++) { ?>
                        <td align='right' class='pt10' bgcolor='#ffcf9c'><?php echo $act_fld_f[$i] ?></td>
                    <?php } ?>
                    <td align='right' class='pt10' bgcolor='#ffcf9c'><?php echo $act_fld_sum1_f ?></td>
                    <?php for ($i=$rows_fld1; $i<($rows_fld1+$rows_fld2); $i++) { ?>
                        <td align='right' class='pt10' bgcolor='#ceceff'><?php echo $act_fld_f[$i] ?></td>
                    <?php } ?>
                    <td align='right' class='pt10' bgcolor='#ceceff'><?php echo $act_fld_sum2_f ?></td>
                    <td align='right' class='pt10' bgcolor='#ffffbe'><?php echo $act_fld_sum_f ?></td>
                </tr>
            </TFOOT>
            <TBODY>
                <?php for ($r=0; $r<$rows_mei; $r++) { ?>
                <tr>
                    <td rowspan='2' align='center' class='pt10' bgcolor='#ffffbe'><?php echo ($r+1) ?></td>
                    <td rowspan='2' align='center' class='pt10' bgcolor='#ffffbe'><?php echo $act_id[$r] ?></td>
                    <td rowspan='2' align='center' class='pt10' bgcolor='#ffffbe'><?php echo $act_name[$r] ?></td>
                    <td width='10' align='center' class='pt10' bgcolor='#ffffbe'>Ψ</td>
                    <?php for ($i=0; $i<$rows_fld1; $i++) { ?>
                        <td align='right' class='pt10' bgcolor='#ffcf9c'><?php echo $act_per_f[$r][$i] ?></td>
                    <?php } ?>
                    <td align='right' class='pt10' bgcolor='#ffcf9c'><?php echo $act_per_f[$r]['sum1'] ?></td>
                    <?php for ($i=$rows_fld1; $i<($rows_fld1+$rows_fld2); $i++) { ?>
                        <td align='right' class='pt10' bgcolor='#ceceff'><?php echo $act_per_f[$r][$i] ?></td>
                    <?php } ?>
                    <td align='right' class='pt10' bgcolor='#ceceff'><?php echo $act_per_f[$r]['sum2'] ?></td>
                    <td align='right' class='pt10' bgcolor='#ffffbe'><?php echo $act_per_sum_f[$r] ?></td>
                    <td rowspan='2' align='right' class='pt10' bgcolor='#ffffbe'><?php echo $act_kin_f[$r] ?></td>
                </tr>
                <tr>
                    <td width='10' align='center' class='pt10' bgcolor='#ffffbe'>��</td>
                    <?php for ($i=0; $i<$rows_fld1; $i++) { ?>
                        <td align='right' class='pt10' bgcolor='#ffcf9c'><?php echo $act_allo_f[$r][$i] ?></td>
                    <?php } ?>
                    <td align='right' class='pt10' bgcolor='#ffcf9c'><?php echo $act_allo_f[$r]['sum1'] ?></td>
                    <?php for ($i=$rows_fld1; $i<($rows_fld1+$rows_fld2); $i++) { ?>
                        <td align='right' class='pt10' bgcolor='#ceceff'><?php echo $act_allo_f[$r][$i] ?></td>
                    <?php } ?>
                    <td align='right' class='pt10' bgcolor='#ceceff'><?php echo $act_allo_f[$r]['sum2'] ?></td>
                    <td align='right' class='pt10' bgcolor='#ffffbe'><?php echo $act_allo_f[$r]['sum'] ?></td>
                </tr>
                <?php } ?>
            </TBODY>
        </table>
            </td></tr>
        </table> <!----------------- ���ߡ�End ------------------>
        
        </form>
    </center>
</body>
</html>
<?php ob_end_flush(); // ���ϥХåե���gzip���� END ?>
