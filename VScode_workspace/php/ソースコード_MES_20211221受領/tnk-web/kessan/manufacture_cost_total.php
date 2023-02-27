<?php
//////////////////////////////////////////////////////////////////////////////
// �»�״ط� ��¤��������                                              //
// Copyright(C) 2017-2019 Norihisa.Ohya norihisa_ooya@nitto-kohki.co.jp     //
// Changed history                                                          //
// 2017/09/11 Created   manufacture_cost_total.php                          //
// 2018/06/26 �軻���ѥǡ�����Ͽ���ɲ�                                      //
// 2018/07/05 ���ʻų�C 2018/05�θ���Ĵ����ȴ���ʤ��褦����                 //
// 2019/05/17 ���դμ�����ˡ���ѹ�                                          //
//////////////////////////////////////////////////////////////////////////////
ini_set('error_reporting', E_STRICT);       // E_STRICT=2048(php5) E_ALL=2047 debug ��
// ini_set('error_reporting',E_ALL);           // E_ALL='2047' debug ��
// ini_set('display_errors','1');              // Error ɽ�� ON debug �� ��꡼���女����
session_start();                            // ini_set()�μ��˻��ꤹ�뤳�� Script �Ǿ��

require_once ('../function.php');           // define.php �� pgsql.php �� require_once ���Ƥ���
require_once ('../tnk_func.php');           // TNK �˰�¸������ʬ�δؿ��� require_once ���Ƥ���
require_once ('../MenuHeader.php');         // TNK ������ menu class
access_log();                               // Script Name �ϼ�ư����

///// TNK ���ѥ�˥塼���饹�Υ��󥹥��󥹤����
$menu = new MenuHeader(0);                  // ǧ�ڥ����å�0=���̰ʾ� �����=TOP_MENU �����ȥ�̤����
    // �ºݤ�ǧ�ڤ�profit_loss_submit.php�ǹԤäƤ���account_group_check()�����

////////////// ����������
// $menu->set_site(10, 7);                     // site_index=10(»�ץ�˥塼) site_id=7(�»��)
//////////// ɽ�������
$menu->set_caption('�������칩��(��)');
//////////// �ƽ����action̾�ȥ��ɥ쥹����
// $menu->set_action('��ݲ�̾',   PL . 'address.php');

$url_referer     = $_SESSION['pl_referer'];     // �ƽФ�Ȥ� URL �����

$menu->set_action('���ʻųݣ�', PL . 'cost_parts_widget_view.php');
$menu->set_action('������', PL . 'cost_material_view.php');
$menu->set_action('����', PL . 'cost_parts_view.php');
$menu->set_action('��ʴ', PL . 'cost_kiriko_view.php');

///// �о�����
$ki2_ym   = $_SESSION['2ki_ym'];
$yyyymm   = $_SESSION['2ki_ym'];
$ki       = Ym_to_tnk($_SESSION['2ki_ym']);
$b_yyyymm = $yyyymm - 100;
$p1_ki    = Ym_to_tnk($b_yyyymm);

///// ������ ǯ��λ���
$yyyy = substr($yyyymm, 0,4);
$mm   = substr($yyyymm, 4,2);
if (($mm >= 1) && ($mm <= 3)) {
    $yyyy = ($yyyy - 1);
}
$pre_end_ym = $yyyy . "03";     // ������ǯ��

///// ����Ⱦ���μ���
$tuki_chk = substr($_SESSION['2ki_ym'],4,2);
if ($tuki_chk >= 1 && $tuki_chk <= 3) {
    $hanki = '��';
} elseif ($tuki_chk >= 4 && $tuki_chk <= 6) {
    $hanki = '��';
} elseif ($tuki_chk >= 7 && $tuki_chk <= 9) {
    $hanki = '��';
} elseif ($tuki_chk >= 10) {
    $hanki = '��';
}

///// ǯ���ϰϤμ���
if ($tuki_chk >= 1 && $tuki_chk <= 3) {
    $str_ym = $yyyy . '0401';
    $end_ym = $yyyymm . '99';
} elseif ($tuki_chk >= 4 && $tuki_chk <= 6) {
    $str_ym = $yyyy . '0401';
    $end_ym = $yyyymm . '99';
} elseif ($tuki_chk >= 7 && $tuki_chk <= 9) {
    $str_ym = $yyyy . '0401';
    $end_ym = $yyyymm . '99';
} elseif ($tuki_chk >= 10) {
    $str_ym = $yyyy . '0401';
    $end_ym = $yyyymm . '99';
}
///// TNK�� �� NK�����Ѵ�
$nk_ki = $ki + 44;

//////////// �����ȥ�̾(�������Υ����ȥ�̾�ȥե�����Υ����ȥ�̾)
if ($tuki_chk == 3) {
    $menu->set_title("�� {$ki} �����ܷ軻�����������ࡡ�����š�������");
} else {
    $menu->set_title("�� {$ki} ������{$hanki}��Ⱦ�������������ࡡ�����š�������");
}

$buhyu_kin = array();
$note = '����ͭ���ٵ�';
$query = sprintf("select SUM(den_kin) from manufacture_cost_cal where den_ki='%s' and den_ymd>=%d and den_ymd<=%d and den_cname='%s'", $nk_ki, $str_ym, $end_ym, $note);
if (getUniResult($query, $buhyu_kin) <= 0) {
    $buhyu_kin = 0;
}

$res_bukai = array();
$note = '���ʻ���';
$query = sprintf("select SUM(den_kin) from manufacture_cost_cal where den_ki='%s' and den_ymd>=%d and den_ymd<=%d and den_cname='%s'", $nk_ki, $str_ym, $end_ym, $note);
if (getUniResult($query, $bukai_kin) <= 0) {
    $bukai_kin = 0;
}

$res_buswa = array();
$note = '���ʻŻ���';
$query = sprintf("select SUM(den_kin) from manufacture_cost_cal where den_ki='%s' and den_ymd>=%d and den_ymd<=%d and den_cname='%s'", $nk_ki, $str_ym, $end_ym, $note);
if (getUniResult($query, $buswa_kin) <= 0) {
    $buswa_kin = 0;
}

if ( $ki == 19) {
    $buswa_kin = $buswa_kin + 19064868;
}

$genka_kin = array();
$note = '��������';
$query = sprintf("select SUM(den_kin) from manufacture_cost_cal where den_ki='%s' and den_ymd>=%d and den_ymd<=%d and den_cname='%s'", $nk_ki, $str_ym, $end_ym, $note);
if (getUniResult($query, $genka_kin) <= 0) {
    $genka_kin = 0;
}

$res_gensw = array();
$note = '����������';
$query = sprintf("select SUM(den_kin) from manufacture_cost_cal where den_ki='%s' and den_ymd>=%d and den_ymd<=%d and den_cname='%s'", $nk_ki, $str_ym, $end_ym, $note);
if (getUniResult($query, $gensw_kin) <= 0) {
    $gensw_kin = 0;
}

$res_genyu = array();
$note = '������ͭ���ٵ�';
$query = sprintf("select SUM(den_kin) from manufacture_cost_cal where den_ki='%s' and den_ymd>=%d and den_ymd<=%d and den_cname='%s'", $nk_ki, $str_ym, $end_ym, $note);
if (getUniResult($query, $genyu_kin) <= 0) {
    $genyu_kin = 0;
}

$res_kiris = array();
$note = '��ʴ';
$query = sprintf("select SUM(den_kin) from manufacture_cost_cal where den_ki='%s' and den_ymd>=%d and den_ymd<=%d and den_cname='%s'", $nk_ki, $str_ym, $end_ym, $note);
if (getUniResult($query, $kiris_kin) <= 0) {
    $kiris_kin = 0;
}

// ��פη׻�
$total_kin = $buhyu_kin + $bukai_kin + $buswa_kin + $genka_kin + $gensw_kin + $genyu_kin + $kiris_kin;

if (isset($_POST['input_data'])) {                        // ����ǡ�������Ͽ
    ///////// ���ܤȥ���ǥå����δ�Ϣ�դ�
    $item = array();
    $item[0]   = "�����ɼ�׾帶����";
    $item[1]   = "�����ɼ�׾����ʻų�C";
    $item[2]   = "������ɼ�׾����ʻų�C";
    $item[3]   = "������ɼ�׾帶����";
    $item[4]   = "ͭ���ٵ븺�۸�����";
    $item[5]   = "ͭ���ٵ븺������";
    $item[6]   = "��ʴ��Ѹ���";
    $item[7]   = "��������������";
    ///////// �ƥǡ������ݴ�
    $input_data = array();
    $input_data[0]   = $genka_kin;
    $input_data[1]   = $bukai_kin;
    $input_data[2]   = $buswa_kin;
    $input_data[3]   = $gensw_kin;
    $input_data[4]   = $genyu_kin;
    $input_data[5]   = $buhyu_kin;
    $input_data[6]   = $kiris_kin;
    $input_data[7]   = $total_kin;
    ///////// �ƥǡ�������Ͽ
    insert_date($item,$yyyymm,$input_data);
}
function insert_date($item,$yyyymm,$input_data) 
{
    for ($i = 0; $i < 8; $i++) {
        $query = sprintf("select rep_kin from financial_report_data where rep_ymd=%d and rep_note='%s'", $yyyymm, $item[$i]);
        $res_in = array();
        if (getResult2($query,$res_in) <= 0) {
            /////////// begin �ȥ�󥶥�����󳫻�
            if ($con = db_connect()) {
                query_affected_trans($con, "begin");
            } else {
                $_SESSION["s_sysmsg"] .= "�ǡ����١�������³�Ǥ��ޤ���";
                exit();
            }
            ////////// Insert Start
            $query = sprintf("insert into financial_report_data (rep_ymd, rep_kin, rep_note, last_date, last_user) values (%d, %d, '%s', CURRENT_TIMESTAMP, '%s')", $yyyymm, $input_data[$i], $item[$i], $_SESSION['User_ID']);
            if (query_affected_trans($con, $query) <= 0) {
                $_SESSION["s_sysmsg"] .= sprintf("%s�ο�����Ͽ�˼���<br> %d", $item[$i], $yyyymm);
                query_affected_trans($con, "rollback");     // transaction rollback
                exit();
            }
            /////////// commit �ȥ�󥶥������λ
            query_affected_trans($con, "commit");
            $_SESSION["s_sysmsg"] = sprintf("<font color='yellow'>%d �軻��ǡ��� ���� ��Ͽ��λ</font>",$yyyymm);
        } else {
            /////////// begin �ȥ�󥶥�����󳫻�
            if ($con = db_connect()) {
                query_affected_trans($con, "begin");
            } else {
                $_SESSION["s_sysmsg"] .= "�ǡ����١�������³�Ǥ��ޤ���";
                exit();
            }
            ////////// UPDATE Start
            $query = sprintf("update financial_report_data set rep_kin=%d, last_date=CURRENT_TIMESTAMP, last_user='%s' where rep_ymd=%d and rep_note='%s'", $input_data[$i], $_SESSION['User_ID'], $yyyymm, $item[$i]);
            if (query_affected_trans($con, $query) <= 0) {
                $_SESSION["s_sysmsg"] .= sprintf("%s��UPDATE�˼���<br> %d", $item[$i], $yyyymm);
                query_affected_trans($con, "rollback");     // transaction rollback
                exit();
            }
            /////////// commit �ȥ�󥶥������λ
            query_affected_trans($con, "commit");
            $_SESSION["s_sysmsg"] = sprintf("<font color='yellow'>%d �軻��ǡ��� �ѹ� ��λ</font>",$yyyymm);
        }
    }
    $_SESSION["s_sysmsg"] .= "�軻��Υǡ�������Ͽ���ޤ�����";
}
/////////// HTML Header ����Ϥ��ƥ���å��������
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
<?= $menu->out_jsBaseClass() ?>
<script type=text/javascript language='JavaScript'>
<!--
function data_input_click(obj) {
    return confirm("����Υǡ�������Ͽ���ޤ���\n���˥ǡ�����������Ͼ�񤭤���ޤ���");
}
// -->
</script>
<style type='text/css'>
<!--
.pt10b {
    font-size:      11pt;
    font-weight:    bold;
    font-family:    monospace;
    color:          teal;
}
.pt11b {
    font-size:      12pt;
    font-weight:    bold;
    font-family:    monospace;
}
th {
    background-color:   yellow;
    color:              blue;
    font:bold           12pt;
    font-family:        monospace;
}
-->
</style>
</head>
<body>
    <center>
<?= $menu->out_title_border() ?>
        <?php
            //  bgcolor='#ceffce' ����
            //  bgcolor='#ffffc6' ��������
            //  bgcolor='#d6d3ce' Win ���쥤
        ?>
    <!--------------- ����������ʸ��ɽ��ɽ������ -------------------->
        <table bgcolor='#d6d3ce' align='center' border='1' cellspacing='0' cellpadding='10'>
            <tr><td> <!----------- ���ߡ�(�ǥ�������) ------------>
        <table class='winbox_field' width='100%' align='center' bgcolor='black' border='1' cellspacing='1' cellpadding='15'>
            <THEAD>
                <!-- �ơ��֥� �إå�����ɽ�� -->
                <tr>
                    <th class='winbox' nowrap>��</th>
                    <th class='winbox' nowrap>�������</th>
                    <th class='winbox' nowrap>�⡡����</th>
                </tr>
            </THEAD>
            <TFOOT>
                <!-- ���ߤϥեå����ϲ���ʤ� -->
            </TFOOT>
            <TBODY>
                <tr>
                    <td class='winbox' nowrap bgcolor='#d6d3ce' rowspan='2'>
                        <div class='pt10b'>�����ɼ�׾�</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#d6d3ce'>
                        <div class='pt11b'><a href='cost_material_view.php?nk_ki=<?php echo $nk_ki ?>&str_ym=<?php echo $str_ym ?>&end_ym=<?php echo $end_ym ?>&2ki_ym=<?php echo $ki2_ym ?>#mark'>�����ࡡ��</a></div>
                    </td>
                    <td class='winbox' nowrap align='right' bgcolor='#d6d3ce'>
                        <div class='pt11b'><?= number_format($genka_kin) ?></div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#d6d3ce'>
                        <div class='pt11b'><a href='cost_parts_widget_view.php?nk_ki=<?php echo $nk_ki ?>&str_ym=<?php echo $str_ym ?>&end_ym=<?php echo $end_ym ?>&2ki_ym=<?php echo $ki2_ym ?>#mark'>���ʻųݣ�</a></div>
                    </td>
                    <td class='winbox' nowrap align='right' bgcolor='#d6d3ce'>
                        <div class='pt11b'><?= number_format($bukai_kin) ?></div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#d6d3ce' rowspan='2'>
                        <div class='pt10b'>������ɼ�׾�</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#d6d3ce'>
                        <div class='pt11b'><a href='cost_parts_widget_view.php?nk_ki=<?php echo $nk_ki ?>&str_ym=<?php echo $str_ym ?>&end_ym=<?php echo $end_ym ?>&2ki_ym=<?php echo $ki2_ym ?>#mark'>���ʻųݣ�</a></div>
                    </td>
                    <td class='winbox' nowrap align='right' bgcolor='#d6d3ce'>
                        <div class='pt11b'><?= number_format($buswa_kin) ?></div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#d6d3ce'>
                        <div class='pt11b'><a href='cost_material_view.php?nk_ki=<?php echo $nk_ki ?>&str_ym=<?php echo $str_ym ?>&end_ym=<?php echo $end_ym ?>&2ki_ym=<?php echo $ki2_ym ?>#mark'>�����ࡡ��</a></div>
                    </td>
                    <td class='winbox' nowrap align='right' bgcolor='#d6d3ce'>
                        <div class='pt11b'><?= number_format($gensw_kin) ?></div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#d6d3ce' rowspan='2'>
                        <div class='pt10b'>ͭ���ٵ븺��</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#d6d3ce'>
                        <div class='pt11b'><a href='cost_material_view.php?nk_ki=<?php echo $nk_ki ?>&str_ym=<?php echo $str_ym ?>&end_ym=<?php echo $end_ym ?>&2ki_ym=<?php echo $ki2_ym ?>#mark'>�����ࡡ��</a></div>
                    </td>
                    <td class='winbox' nowrap align='right' bgcolor='#d6d3ce'>
                        <div class='pt11b'><?= number_format($genyu_kin) ?></div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#d6d3ce'>
                        <div class='pt11b'><a href='cost_parts_view.php?nk_ki=<?php echo $nk_ki ?>&str_ym=<?php echo $str_ym ?>&end_ym=<?php echo $end_ym ?>&2ki_ym=<?php echo $ki2_ym ?>#mark'>����������</a></div>
                    </td>
                    <td class='winbox' nowrap align='right' bgcolor='#d6d3ce'>
                        <div class='pt11b'><?= number_format($buhyu_kin) ?></div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#d6d3ce'>
                        <div class='pt10b'>��ʴ��Ѹ���</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#d6d3ce'>
                        <div class='pt11b'><a href='cost_kiriko_view.php?nk_ki=<?php echo $nk_ki ?>&str_ym=<?php echo $str_ym ?>&end_ym=<?php echo $end_ym ?>&2ki_ym=<?php echo $ki2_ym ?>#mark'>�ڡ�����ʴ</a></div>
                    </td>
                    <td class='winbox' nowrap align='right' bgcolor='#d6d3ce'>
                        <div class='pt11b'><?= number_format($kiris_kin) ?></div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap align='center' bgcolor='#d6d3ce' colspan='2'>
                        <div class='pt11b'>�硡������</div>
                    </td>
                    <td class='winbox' nowrap align='right' bgcolor='#d6d3ce'>
                        <div class='pt11b'><?= number_format($total_kin) ?></div>
                    </td>
                </tr>
            </TBODY>
        </table>
            </td></tr>
        </table> <!----------------- ���ߡ�End ------------------>
        <form method='post' action='<?php echo $menu->out_self() ?>'>
            <input class='pt10b' type='submit' name='input_data' value='��Ͽ' onClick='return data_input_click(this)'>
        </form>
    </center>
</body>
</html>
