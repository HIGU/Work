<?php
//////////////////////////////////////////////////////////////////////////////
// �����ӥ������� ���Τγ��(����Ψ) �Ȳ�                                 //
// Copyright(C) 2003-2004 2007 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp  //
// Changed history                                                          //
// 2003/10/24 Created   service_percent_view_total.php                      //
// 2003/10/28 $per���η׻���̤򾮿����ʲ�����򣵷���ѹ�100%���к�      //
//            number_format�򣳷夫��1����ѹ�                              //
// 2003/11/12 group by item_no,item,order_no order by order_no,item_no      //
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
$menu_title = "$view_ym �����ӥ����ˤ������Ψ �Ȳ�";
//////////// �����ȥ�̾(�������Υ����ȥ�̾�ȥե�����Υ����ȥ�̾)
$menu->set_title($menu_title);
//////////// ɽ�������
$menu->set_caption('�� ¤ �� �񡡴� �� �񡡤Ρ��� �� Ψ���� ��');

///// ��Ⱦ���� ǯ��λ���
$yyyy = substr($service_ym, 0,4);
$mm   = substr($service_ym, 4,2);
if (($mm >= 1) && ($mm <= 3)) {
    $yyyy = ($yyyy - 1);
    $zenki_ym = $yyyy . '09';     // ����ǯ��
} elseif (($mm >= 10) && ($mm <= 12)) {
    $zenki_ym = $yyyy . '09';     // ����ǯ��
} else {
    $zenki_ym = $yyyy . '03';     // ����ǯ��
}

////////// �ǡ����١����ؤ���³
if ( !($con = db_connect()) ) {
    $_SESSION['s_sysmsg'] = '�ǡ����١�������³�Ǥ��ޤ���';
    header("Location: $url_referer");                   // ľ���θƽи������
    exit();
}

//////////// ����Ψ������פ�ȴ�Ф�
$query = "select sum(percent * 100)::int2 from service_percent_history where service_ym=$service_ym";
if (($rows = getUniResTrs($con, $query, $res)) <= 0) {
    $_SESSION['s_sysmsg'] = '����Ψ������פ������Ǥ��ޤ���';
    header("Location: $url_referer");                   // ľ���θƽи������
    exit();
} else {
    $point_sum = $res;
}

//////////// item(ľ������)��ι�פ�ȴ�Ф� intext=1 ���������
$query = "select item_no, item, sum(percent * 100)::int2 from service_percent_history
          where service_ym=$service_ym and intext=1 group by item_no, item, order_no
          order by order_no, item_no";
if (($rows_fld1 = getResultTrs($con, $query, $res)) <= 0) {
    $_SESSION['s_sysmsg'] = 'ľ��������ι�פ������Ǥ��ޤ���';
    header("Location: $url_referer");                   // ľ���θƽи������
    exit();
} else {
    $point1['����'] = 0;                // ���פν����
    $per1['����']   = 0;
    for ($i=0; $i<$rows_fld1; $i++) {
        $field1[$i] = $res[$i][1];      // �ե������̾
        $point1[$i] = $res[$i][2];      // �������Ψ
        $point1_f[$i] = number_format($point1[$i]);       // �������Ψɽ���Ѥ˥ե����ޥå�
        $point1['����'] += $point1[$i];
        $point1_f['����'] = number_format($point1['����']);
        $per1[$i]   = Uround($point1[$i] / $point_sum, 5);  // ����Ψ�׻�
        $per1_f[$i] = number_format($per1[$i] * 100, 1);    // % ���Ѵ�����ɽ���Ѥ˥ե����ޥå�
        $per1['����'] += $per1[$i];
    }
    $per1_f['����'] = number_format($per1['����'] * 100, 1);    // % ���Ѵ�����ɽ���Ѥ˥ե����ޥå�
}
//////////// item(ľ������)��ι�פ�ȴ�Ф� intext=2 Ĵã������ 
$query = "select item_no, item, sum(percent * 100)::int2 from service_percent_history
          where service_ym=$service_ym and intext=2 group by item_no, item, order_no
          order by order_no, item_no";
if (($rows_fld2 = getResultTrs($con, $query, $res)) <= 0) {
    $_SESSION['s_sysmsg'] = 'ľ��������ι�פ������Ǥ��ޤ���';
    header("Location: $url_referer");                   // ľ���θƽи������
    exit();
} else {
    $point2['����'] = 0;                // ���פν����
    $per2['����']   = 0;
    for ($i=0; $i<$rows_fld2; $i++) {
        $field2[$i] = $res[$i][1];      // �ե������̾
        $point2[$i] = $res[$i][2];      // �������Ψ
        $point2_f[$i] = number_format($point2[$i]);       // �������Ψɽ���Ѥ˥ե����ޥå�
        $point2['����'] += $point2[$i];
        $point2_f['����'] = number_format($point2['����']);
        $per2[$i]   = Uround($point2[$i] / $point_sum, 5);  // ����Ψ�׻�
        $per2_f[$i] = number_format($per2[$i] * 100, 1);    // % ���Ѵ�����ɽ���Ѥ˥ե����ޥå�
        $per2['����'] += $per2[$i];
    }
    $per2_f['����'] = number_format($per2['����'] * 100, 1);    // % ���Ѵ�����ɽ���Ѥ˥ե����ޥå�
}
//////////// ��ץѡ�����Ȥθ����Ѥη׻�
$per_sum = $per1['����'] + $per2['����'];
$per_sum_f = number_format($per_sum * 100, 1);
$point_sum_f = number_format($point_sum);


//////////// ����(act_id)�ڤ� item_no ��ι�פ�ȴ�Ф�(����ɽ����)
$query = "select act_id, trim(s_name), item_no, item, sum(percent * 100)::int2
         from service_percent_history left outer join act_table using(act_id)
         where service_ym=$service_ym
         group by act_id, s_name, item_no, item, intext, order_no
         order by act_id, intext, order_no, item_no";
$res = array();
if (($rows = getResultTrs($con, $query, $res)) <= 0) {
    $_SESSION['s_sysmsg'] = '�����������٤������Ǥ��ޤ���';
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
    font:bold 9pt;
}
.ok_button {
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
                        <td align='right' class='ok_button'>
                            <input class='ok_button' type='submit' name='save' value=' �ϣ� '>��ñ�̡���
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
                        <td align='right' class='pt11b' bgcolor='#ffcf9c'><?php echo $per1_f[$i] ?></td>
                    <?php } ?>
                    <td align='right' class='pt11b' bgcolor='#ffcf9c'><?php echo $per1_f['����'] ?></td>
                    <td width='10' align='center' class='pt10' bgcolor='#ceceff'>Ψ</td>
                    <?php for ($i=0; $i<$rows_fld2; $i++) { ?>
                        <td align='right' class='pt11b' bgcolor='#ceceff'><?php echo $per2_f[$i] ?></td>
                    <?php } ?>
                    <td align='right' class='pt11b' bgcolor='#ceceff'><?php echo $per2_f['����'] ?></td>
                    <td align='right' class='pt11b' bgcolor='#ffffbe'><?php echo $per_sum_f ?></td>
                </tr>
                <tr>
                    <td width='10' align='center' class='pt10' bgcolor='#ffcf9c'>����</td>
                    <?php for ($i=0; $i<$rows_fld1; $i++) { ?>
                        <td align='right' class='pt11b' bgcolor='#ffcf9c'><?php echo $point1_f[$i] ?></td>
                    <?php } ?>
                    <td align='right' class='pt11b' bgcolor='#ffcf9c'><?php echo $point1_f['����'] ?></td>
                    <td width='10' align='center' class='pt10' bgcolor='#ceceff'>����</td>
                    <?php for ($i=0; $i<$rows_fld2; $i++) { ?>
                        <td align='right' class='pt11b' bgcolor='#ceceff'><?php echo $point2_f[$i] ?></td>
                    <?php } ?>
                    <td align='right' class='pt11b' bgcolor='#ceceff'><?php echo $point2_f['����'] ?></td>
                    <td align='right' class='pt11b' bgcolor='#ffffbe'><?php echo $point_sum_f ?></td>
                </tr>
            </TBODY>
        </table>
            </td></tr>
        </table> <!----------------- ���ߡ�End ------------------>
        
        <br>
        
        <!--------------- ��������������祳����������٤�ɽ������ -------------------->
        <table bgcolor='#d6d3ce' align='center' cellspacing="0" cellpadding="3" border='1'>
            <tr><td> <!----------- ���ߡ�(�ǥ�������) ------------>
        <table bordercolordark='white' bordercolorlight='#bdaa90' bgcolor='#d6d3ce' align='center' cellspacing="0" cellpadding="3" border='1'>
            <THEAD>
                <!-- �ơ��֥� �إå�����ɽ�� -->
                <tr align='center' bgcolor='#beffbe'>
                    <td colspan='<?php echo $rows_fld1+$rows_fld2+7 ?>' class='pt11b'> <!-- colspan����20�ˤ��Ƥ��� -->
                        �� ¤ �� �񡡴� �� �񡡤Ρ��� �� Ψ �� �ס����� ��
                    </td>
                </tr>
                <tr>
                    <td align='center' class='pt10' bgcolor='#ffffbe'>No</td>
                    <td align='center' class='pt10' bgcolor='#ffffbe'>������</td>
                    <td align='center' class='pt10' bgcolor='#ffffbe'>����̾</td>
                    <td align='center' class='pt10' bgcolor='#ffffbe'>��</td>
                    <?php for ($i=0; $i<$rows_fld1; $i++) { ?>
                        <td align='center' class='pt11b' bgcolor='#ffcf9c'><?php echo $field1[$i] ?></td>
                    <?php } ?>
                    <td align='center' class='pt10' bgcolor='#ffcf9c'>������</td>
                    <?php for ($i=0; $i<$rows_fld2; $i++) { ?>
                        <td align='center' class='pt11b' bgcolor='#ceceff'><?php echo $field2[$i] ?></td>
                    <?php } ?>
                    <td align='center' class='pt10' bgcolor='#ceceff'>������</td>
                    <td align='center' class='pt10' bgcolor='#ffffbe'>�硡��</td>
                </tr>
            </THEAD>
            <TFOOT>
                <!-- �եå����Ϲ�פ�ɽ�� -->
                <tr>
                    <td colspan='4' align='right' class='pt10' bgcolor='#ffffbe'>Ψ����</td>
                    <td colspan='<?php echo $rows_fld1+1 ?>' align='right' class='pt11b' bgcolor='#ffcf9c'><?php echo $act_persum1_f ?></td>
                    <td colspan='<?php echo $rows_fld2+1 ?>' align='right' class='pt11b' bgcolor='#ceceff'><?php echo $act_persum2_f ?></td>
                    <td align='right' class='pt11b' bgcolor='#ffffbe'><?php echo $act_persum_f ?></td>
                </tr>
                <tr>
                    <td colspan='4' align='right' class='pt10' bgcolor='#ffffbe'>�硡��</td>
                    <td colspan='<?php echo $rows_fld1+1 ?>' align='right' class='pt11b' bgcolor='#ffcf9c'><?php echo $act_poisum1 ?></td>
                    <td colspan='<?php echo $rows_fld2+1 ?>' align='right' class='pt11b' bgcolor='#ceceff'><?php echo $act_poisum2 ?></td>
                    <td align='right' class='pt11b' bgcolor='#ffffbe'><?php echo $act_poisum ?></td>
                </tr>
            </TFOOT>
            <TBODY>
                <?php for ($r=0; $r<$rows_mei; $r++) { ?>
                <tr>
                    <td rowspan='2' nowrap align='center' class='pt10' bgcolor='#ffffbe'><?php echo ($r+1) ?></td>
                    <td rowspan='2' nowrap align='center' class='pt10' bgcolor='#ffffbe'><?php echo $act_id[$r] ?></td>
                    <td rowspan='2' nowrap align='center' class='pt10' bgcolor='#ffffbe'><?php echo $act_name[$r] ?></td>
                    <td width='10' align='center' class='pt10' bgcolor='#ffffbe'>Ψ</td>
                    <?php for ($i=0; $i<$rows_fld1; $i++) { ?>
                        <td align='right' class='pt11b' bgcolor='#ffcf9c'><?php echo $act_per_f[$r][$i] ?></td>
                    <?php } ?>
                    <td align='right' class='pt11b' bgcolor='#ffcf9c'><?php echo $act_per_f[$r]['sum1'] ?></td>
                    <?php for ($i=$rows_fld1; $i<($rows_fld1+$rows_fld2); $i++) { ?>
                        <td align='right' class='pt11b' bgcolor='#ceceff'><?php echo $act_per_f[$r][$i] ?></td>
                    <?php } ?>
                    <td align='right' class='pt11b' bgcolor='#ceceff'><?php echo $act_per_f[$r]['sum2'] ?></td>
                    <td align='right' class='pt11b' bgcolor='#ffffbe'><?php echo $act_per_sum_f[$r] ?></td>
                </tr>
                <tr>
                    <td width='10' align='center' class='pt10' bgcolor='#ffffbe'>��</td>
                    <?php for ($i=0; $i<$rows_fld1; $i++) { ?>
                        <td align='right' class='pt11b' bgcolor='#ffcf9c'><?php echo $act_poi[$r][$i] ?></td>
                    <?php } ?>
                    <td align='right' class='pt11b' bgcolor='#ffcf9c'><?php echo $act_poi[$r]['sum1'] ?></td>
                    <?php for ($i=$rows_fld1; $i<($rows_fld1+$rows_fld2); $i++) { ?>
                        <td align='right' class='pt11b' bgcolor='#ceceff'><?php echo $act_poi[$r][$i] ?></td>
                    <?php } ?>
                    <td align='right' class='pt11b' bgcolor='#ceceff'><?php echo $act_poi[$r]['sum2'] ?></td>
                    <td align='right' class='pt11b' bgcolor='#ffffbe'><?php echo $act_poi_sum[$r] ?></td>
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
