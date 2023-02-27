<?php
//////////////////////////////////////////////////////////////////////////////
// �»�״ط� �����ǿ���� ���Ǽ���ǳۡʾ����ǡ�                         //
// Copyright(C) 2021-2021 Norihisa.Ohya norihisa_ooya@nitto-kohki.co.jp     //
// Changed history                                                          //
// 2021/04/22 Created   sales_tax_chukan_view.php                           //
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
if ($tuki_chk >= 1 && $tuki_chk <= 3) {           //�裴��Ⱦ��
    $hanki = '��';
} elseif ($tuki_chk >= 4 && $tuki_chk <= 6) {     //�裱��Ⱦ��
    $hanki = '��';
} elseif ($tuki_chk >= 7 && $tuki_chk <= 9) {     //�裲��Ⱦ��
    $hanki = '��';
} elseif ($tuki_chk >= 10) {    //�裳��Ⱦ��
    $hanki = '��';
}

///// ǯ���ϰϤμ���
if ($tuki_chk >= 1 && $tuki_chk <= 3) {           //�裴��Ⱦ��
    $str_ym = $yyyy . '04';
    $end_ym = $yyyymm;
} elseif ($tuki_chk >= 4 && $tuki_chk <= 6) {     //�裱��Ⱦ��
    $str_ym = $yyyy . '04';
    $end_ym = $yyyymm;
} elseif ($tuki_chk >= 7 && $tuki_chk <= 9) {     //�裲��Ⱦ��
    $str_ym = $yyyy . '04';
    $end_ym = $yyyymm;
} elseif ($tuki_chk >= 10) {    //�裳��Ⱦ��
    $str_ym = $yyyy . '04';
    $end_ym = $yyyymm;
}
///// TNK�� �� NK�����Ѵ�
$nk_ki   = $ki + 44;
$nk_p1ki = $p1_ki + 44;

//////////// �����ȥ�̾(�������Υ����ȥ�̾�ȥե�����Υ����ȥ�̾)
if ($tuki_chk == 3) {
    $menu->set_title("�� {$ki} �����ܷ軻���桡�֡�Ǽ���ա��ǡ��ۡ��ʾá����ǡ�");
} else {
    $menu->set_title("�� {$ki} ������{$hanki}��Ⱦ�����桡�֡�Ǽ���ա��ǡ��ۡ��ʾá����ǡ�");
}

$cost_ym = array();
$tuki_chk = substr($_SESSION['2ki_ym'],4,2);
if ($tuki_chk >= 1 && $tuki_chk <= 3) {           //�裴��Ⱦ��
    $hanki = '��';
    $yyyy_tou = $yyyy + 1;
    $cost_ym[0]  = $yyyy . '04';
    $cost_ym[1]  = $yyyy . '05';
    $cost_ym[2]  = $yyyy . '06';
    $cost_ym[3]  = $yyyy . '07';
    $cost_ym[4]  = $yyyy . '08';
    $cost_ym[5]  = $yyyy . '09';
    $cost_ym[6]  = $yyyy . '10';
    $cost_ym[7]  = $yyyy . '11';
    $cost_ym[8]  = $yyyy . '12';
    $cost_ym[9]  = $yyyy_tou . '01';
    $cost_ym[10] = $yyyy_tou . '02';
    $cost_ym[11] = $yyyy_tou . '03';
    $cnum        = 12;
} elseif ($tuki_chk >= 4 && $tuki_chk <= 6) {     //�裱��Ⱦ��
    $hanki = '��';
    $cost_ym[0]  = $yyyy . '04';
    $cost_ym[1]  = $yyyy . '05';
    $cost_ym[2]  = $yyyy . '06';
    $cnum        = 3;
} elseif ($tuki_chk >= 7 && $tuki_chk <= 9) {     //�裲��Ⱦ��
    $hanki = '��';
    $cost_ym[0] = $yyyy . '04';
    $cost_ym[1] = $yyyy . '05';
    $cost_ym[2] = $yyyy . '06';
    $cost_ym[3] = $yyyy . '07';
    $cost_ym[4]  = $yyyy . '08';
    $cost_ym[5]  = $yyyy . '09';
    $cnum        = 6;
} elseif ($tuki_chk >= 10) {    //�裳��Ⱦ��
    $hanki = '��';
    $cost_ym[0]  = $yyyy . '04';
    $cost_ym[1]  = $yyyy . '05';
    $cost_ym[2]  = $yyyy . '06';
    $cost_ym[3]  = $yyyy . '07';
    $cost_ym[4]  = $yyyy . '08';
    $cost_ym[5]  = $yyyy . '09';
    $cost_ym[6]  = $yyyy . '10';
    $cost_ym[7]  = $yyyy . '11';
    $cost_ym[8]  = $yyyy . '12';
    $cnum        = 9;
}

$cost_ym_next = array();
$yyyy_next    = $yyyy + 1;
$tuki_chk = substr($_SESSION['2ki_ym'],4,2);
if ($tuki_chk >= 1 && $tuki_chk <= 3) {           //�裴��Ⱦ��
    $hanki = '��';
    $yyyy_tou = $yyyy_next + 1;
    $cost_ym_next[0]  = $yyyy_next . '04';
    $cost_ym_next[1]  = $yyyy_next . '05';
    $cost_ym_next[2]  = $yyyy_next . '06';
    $cost_ym_next[3]  = $yyyy_next . '07';
    $cost_ym_next[4]  = $yyyy_next . '08';
    $cost_ym_next[5]  = $yyyy_next . '09';
    $cost_ym_next[6]  = $yyyy_next . '10';
    $cost_ym_next[7]  = $yyyy_next . '11';
    $cost_ym_next[8]  = $yyyy_next . '12';
    $cost_ym_next[9]  = $yyyy_tou . '01';
    $cost_ym_next[10] = $yyyy_tou . '02';
    $cost_ym_next[11] = $yyyy_tou . '03';
    $cnum_next        = 12;
} elseif ($tuki_chk >= 4 && $tuki_chk <= 6) {     //�裱��Ⱦ��
    $hanki = '��';
    $cost_ym_next[0]  = $yyyy_next . '04';
    $cost_ym_next[1]  = $yyyy_next . '05';
    $cost_ym_next[2]  = $yyyy_next . '06';
    $cnum_next        = 3;
} elseif ($tuki_chk >= 7 && $tuki_chk <= 9) {     //�裲��Ⱦ��
    $hanki = '��';
    $cost_ym_next[0] = $yyyy_next . '04';
    $cost_ym_next[1] = $yyyy_next . '05';
    $cost_ym_next[2] = $yyyy_next . '06';
    $cost_ym_next[3] = $yyyy_next . '07';
    $cost_ym_next[4]  = $yyyy_next . '08';
    $cost_ym_next[5]  = $yyyy_next . '09';
    $cnum_next        = 6;
} elseif ($tuki_chk >= 10) {    //�裳��Ⱦ��
    $hanki = '��';
    $cost_ym_next[0]  = $yyyy_next . '04';
    $cost_ym_next[1]  = $yyyy_next . '05';
    $cost_ym_next[2]  = $yyyy_next . '06';
    $cost_ym_next[3]  = $yyyy_next . '07';
    $cost_ym_next[4]  = $yyyy_next . '08';
    $cost_ym_next[5]  = $yyyy_next . '09';
    $cost_ym_next[6]  = $yyyy_next . '10';
    $cost_ym_next[7]  = $yyyy_next . '11';
    $cost_ym_next[8]  = $yyyy_next . '12';
    $cnum_next        = 9;
}

///////////// �ǡ���������ˤ�� ��¦��ɽ����ǡ�������
///////////// ̤ʧ��������̾����ǳ۷׻�ɽ ��׶�ۤ����
// query���϶���
$query = "select
                SUM(rep_de) as t_kin
          from
                financial_report_month";

// ����ι�׶�ۤ����
$t_sumi10_kin = 0;     // �Ƕ�׾��(10��)

// ��ʧ��������
for ($r=0; $r<$cnum; $r++) {
    // ���դ�����
    $d_ym = $cost_ym[$r];
    $search = "where rep_ymd=$d_ym and rep_summary1='1560' and rep_summary2='00'";
    $query_s = sprintf("$query %s", $search);     // SQL query ʸ�δ���
    $res_sum = array();
    if ($rows=getResult($query_s, $res_sum) <= 0) {
        $m_sumi10_kin[$r] = 0;
        $input_data[$r] = $m_sumi10_kin[$r];
        $item_name = $d_ym . "���Ǽ���ǳ�";
        $item[$r]  = $item_name;
    } else {
        $m_sumi10_kin[$r] = $res_sum[0][0];
        $input_data[$r] = $m_sumi10_kin[$r];
        $item_name = $d_ym . "���Ǽ���ǳ�";
        $item[$r]  = $item_name;
        $t_sumi10_kin += $m_sumi10_kin[$r];
    }
}

if (isset($_POST['input_data'])) {                        // ����ǡ�������Ͽ
    insert_date($item,$nk_ki,$input_data);
}

///////////// �ǡ���������ˤ�� ��¦��ɽ����ǡ�������
///////////// ̤ʧ��������̾����ǳ۷׻�ɽ ��׶�ۤ����
// query���϶���
$query = "select
                SUM(rep_de) as t_kin
          from
                financial_report_month";

// ����ι�׶�ۤ����
$t_sumi10_next_kin = 0;     // �Ƕ�׾��(10��)

// ��ʧ��������
for ($r=0; $r<$cnum_next; $r++) {
    // ���դ�����
    $d_ym = $cost_ym_next[$r];
    $search = "where rep_ymd=$d_ym and rep_summary1='1560' and rep_summary2='00'";
    $query_s = sprintf("$query %s", $search);     // SQL query ʸ�δ���
    $res_sum = array();
    if ($rows=getResult($query_s, $res_sum) <= 0) {
        $m_sumi10_next_kin[$r] = 0;
        $input_data_next[$r] = $m_sumi10_next_kin[$r];
        $item_name_next = $d_ym . "���Ǽ���ǳ�";
        $item_next[$r]  = $item_name_next;
    } else {
        $m_sumi10_next_kin[$r] = $res_sum[0][0];
        $input_data_next[$r] = $m_sumi10_next_kin[$r];
        $item_name_next = $d_ym . "���Ǽ���ǳ�";
        $item_next[$r]  = $item_name_next;
        $t_sumi10_next_kin += $m_sumi10_next_kin[$r];
    }
}

if (isset($_POST['input_data'])) {                        // ����ǡ�������Ͽ
    insert_date($item,$nk_ki,$input_data);
    insert_date_next($item_next,$nk_ki,$input_data_next);
}

function insert_date($item,$nk_ki,$input_data) 
{
    $num_input = count($input_data);
    for ($i = 0; $i < $num_input; $i++) {
        $query = sprintf("select rep_kin from sales_tax_create_data where rep_ki=%d and rep_note='%s'", $nk_ki, $item[$i]);
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
            $query = sprintf("insert into sales_tax_create_data (rep_ki, rep_kin, rep_note, last_date, last_user) values (%d, %d, '%s', CURRENT_TIMESTAMP, '%s')", $nk_ki, $input_data[$i], $item[$i], $_SESSION['User_ID']);
            if (query_affected_trans($con, $query) <= 0) {
                $_SESSION["s_sysmsg"] .= sprintf("%s�ο�����Ͽ�˼���<br> %d", $item[$i], $yyyymm);
                query_affected_trans($con, "rollback");     // transaction rollback
                exit();
            }
            /////////// commit �ȥ�󥶥������λ
            query_affected_trans($con, "commit");
            $_SESSION["s_sysmsg"] = sprintf("<font color='yellow'>%d �����ǿ����ǡ��� ���� ��Ͽ��λ</font>",$yyyymm);
        } else {
            /////////// begin �ȥ�󥶥�����󳫻�
            if ($con = db_connect()) {
                query_affected_trans($con, "begin");
            } else {
                $_SESSION["s_sysmsg"] .= "�ǡ����١�������³�Ǥ��ޤ���";
                exit();
            }
            ////////// UPDATE Start
            $query = sprintf("update sales_tax_create_data set rep_kin=%d, last_date=CURRENT_TIMESTAMP, last_user='%s' where rep_ki=%d and rep_note='%s'", $input_data[$i], $_SESSION['User_ID'], $nk_ki, $item[$i]);
            if (query_affected_trans($con, $query) <= 0) {
                $_SESSION["s_sysmsg"] .= sprintf("%s��UPDATE�˼���<br> %d", $item[$i], $yyyymm);
                query_affected_trans($con, "rollback");     // transaction rollback
                exit();
            }
            /////////// commit �ȥ�󥶥������λ
            query_affected_trans($con, "commit");
            $_SESSION["s_sysmsg"] = sprintf("<font color='yellow'>%d �����ǿ����ǡ��� �ѹ� ��λ</font>",$yyyymm);
        }
    }
    $_SESSION["s_sysmsg"] .= "�����ǿ����Υǡ�������Ͽ���ޤ�����";
}

function insert_date_next($item,$nk_ki,$input_data) 
{
    $num_input = count($input_data);
    $nk_ki     = $nk_ki + 1;
    for ($i = 0; $i < $num_input; $i++) {
        $query = sprintf("select rep_kin from sales_tax_create_data where rep_ki=%d and rep_note='%s'", $nk_ki, $item[$i]);
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
            $query = sprintf("insert into sales_tax_create_data (rep_ki, rep_kin, rep_note, last_date, last_user) values (%d, %d, '%s', CURRENT_TIMESTAMP, '%s')", $nk_ki, $input_data[$i], $item[$i], $_SESSION['User_ID']);
            if (query_affected_trans($con, $query) <= 0) {
                $_SESSION["s_sysmsg"] .= sprintf("%s�ο�����Ͽ�˼���<br> %d", $item[$i], $yyyymm);
                query_affected_trans($con, "rollback");     // transaction rollback
                exit();
            }
            /////////// commit �ȥ�󥶥������λ
            query_affected_trans($con, "commit");
            $_SESSION["s_sysmsg"] = sprintf("<font color='yellow'>%d �����ǿ����ǡ��� ���� ��Ͽ��λ</font>",$yyyymm);
        } else {
            /////////// begin �ȥ�󥶥�����󳫻�
            if ($con = db_connect()) {
                query_affected_trans($con, "begin");
            } else {
                $_SESSION["s_sysmsg"] .= "�ǡ����١�������³�Ǥ��ޤ���";
                exit();
            }
            ////////// UPDATE Start
            $query = sprintf("update sales_tax_create_data set rep_kin=%d, last_date=CURRENT_TIMESTAMP, last_user='%s' where rep_ki=%d and rep_note='%s'", $input_data[$i], $_SESSION['User_ID'], $nk_ki, $item[$i]);
            if (query_affected_trans($con, $query) <= 0) {
                $_SESSION["s_sysmsg"] .= sprintf("%s��UPDATE�˼���<br> %d", $item[$i], $yyyymm);
                query_affected_trans($con, "rollback");     // transaction rollback
                exit();
            }
            /////////// commit �ȥ�󥶥������λ
            query_affected_trans($con, "commit");
            $_SESSION["s_sysmsg"] = sprintf("<font color='yellow'>%d �����ǿ����ǡ��� �ѹ� ��λ</font>",$yyyymm);
        }
    }
    $_SESSION["s_sysmsg"] .= "�����ǿ����Υǡ�������Ͽ���ޤ�����";
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
    color:          black;
}
.pt11b {
    font-size:      12pt;
    font-weight:    bold;
    font-family:    monospace;
}
th {
    background-color:   #ffffff;
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
        <table bgcolor='#d6d3ce' align='center' border='1' cellspacing='0' cellpadding='3'>
            <tr><td> <!----------- ���ߡ�(�ǥ�������) ------------>
        <table class='winbox_field' width='100%' bgcolor='#d6d3ce' align='center' border='1' cellspacing='0' cellpadding='3'>
            <thead>
                <!-- �ơ��֥� �إå�����ɽ�� -->
                <tr>
                    <th class='winbox' nowrap>��</th>
                    <th class='winbox' nowrap><?php echo $ki ?>��</th>
                    <th class='winbox' nowrap>��</th>
                    <th class='winbox' nowrap><?php echo $ki + 1 ?>��</th>
                </tr>
            </thead>
            <tfoot>
                <!-- ���ߤϥեå����ϲ���ʤ� -->
            </tfoot>
            <tbody>
            <?php
            for ($i=0; $i<$cnum; $i++) {
            
            echo "<tr>\n";
            // ǯ��
            echo "<td class='winbox' nowrap bgcolor='white' align='center'><div class='pt10b'>" . format_date6($cost_ym[$i]) . "</div></td>\n";
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>" . number_format($m_sumi10_kin[$i]) . "</span></td>\n";
            echo "<td class='winbox' nowrap bgcolor='white' align='center'><div class='pt10b'>" . format_date6($cost_ym_next[$i]) . "</div></td>\n";
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>" . number_format($m_sumi10_next_kin[$i]) . "</span></td>\n";
            echo "</tr>\n";
            }
            
            echo "<tr>\n";
            // ǯ��
            echo "<td class='winbox' nowrap bgcolor='white' align='center'><div class='pt10b'>���</div></td>\n";
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>" . number_format($t_sumi10_kin) . "</span></td>\n";
            echo "<td class='winbox' nowrap bgcolor='white' align='center'><div class='pt10b'>���</div></td>\n";
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>" . number_format($t_sumi10_next_kin) . "</span></td>\n";
            echo "</tr>\n";
            ?>
            </tbody>
        </table>
            </td></tr>
        </table> <!----------------- ���ߡ�End ------------------>
        
        <form method='post' action='<?php echo $menu->out_self() ?>'>
            <input class='pt10b' type='submit' name='input_data' value='��Ͽ' onClick='return data_input_click(this)'>
        </form>
    </center>
</body>
</html>
