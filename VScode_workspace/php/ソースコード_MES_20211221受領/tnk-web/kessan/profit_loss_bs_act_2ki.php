<?php
//////////////////////////////////////////////////////////////////////////////
// �»�״ط� �������ɽ �߼��о�ɽ                                       //
// Copyright(C) 2012-2017 Norihisa.Ohya norihisa_ooya@nitto-kohki.co.jp     //
// Changed history                                                          //
// 2012/01/24 Created   profit_loss_bs_act_2ki.php                          //
// 2012/01/26 Excel�Σ������ɽ�ˤ��碌�ƥ쥤�����Ȥ�Ĵ��                   //
// 2012/04/04 �����ΰ����ǰ㤦�ǡ�����ɽ������Ƥ����Τ��ѹ�                //
// 2012/04/18 �裴��Ⱦ���Τ�ɽ����������äƤ����Τ��б�                    //
//            Ĵ�����̣����褦�˹�פη׻���ǡ����μ������ɲ�            //
// 2012/10/09 ���������פ�Ĵ�����ǡ�������Ͽ(��ưľ��)����Ƥ��뤿��        //
//            ���������פο��������ˤʤ�ʤ��Τǡ�Ĵ��������ޤʤ��褦��  //
//            �ѹ�                                                          //
// 2017/04/13 �������ܡ�̤�������ɲ�                                        //
// 2017/08/04 �������ܡ���ʧ�������ɲ�                                      //
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


///// �о�����
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
if ($tuki_chk == 3) {
    $hanki = '��';
} elseif ($tuki_chk == 6) {
    $hanki = '��';
} elseif ($tuki_chk == 9) {
    $hanki = '��';
} elseif ($tuki_chk == 12) {
    $hanki = '��';
}

//////////// �����ȥ�̾(�������Υ����ȥ�̾�ȥե�����Υ����ȥ�̾)
if ($tuki_chk == 3) {
    $menu->set_title("�� {$ki} �����ܷ軻���ߡ��ڡ��С��ȡ�ɽ");
} else {
    $menu->set_title("�� {$ki} ������{$hanki}��Ⱦ�����ߡ��ڡ��С��ȡ�ɽ");
}

///// ɽ��ñ�̤��������
if (isset($_POST['taisyaku_tani'])) {
    $_SESSION['taisyaku_tani'] = $_POST['taisyaku_tani'];
    $tani = $_SESSION['taisyaku_tani'];
} elseif (isset($_SESSION['taisyaku_tani'])) {
    $tani = $_SESSION['taisyaku_tani'];
} else {
    $tani = 1000000;        // ����� ɽ��ñ�� ���
    $_SESSION['taisyaku_tani'] = $tani;
}
///// ɽ�� ��������� �������
if (isset($_POST['keihi_keta'])) {
    $_SESSION['keihi_keta'] = $_POST['keihi_keta'];
    $keta = $_SESSION['keihi_keta'];
} elseif (isset($_SESSION['keihi_keta'])) {
    $keta = $_SESSION['keihi_keta'];
} else {
    $keta = 0;          // ����� �������ʲ����
    $_SESSION['keihi_keta'] = $keta;
}

///// �ǡ�������
///////// ���ܤȥ���ǥå����δ�Ϣ�դ�
    $item = array();
    $item[0]   = "����ڤ��¶�";
    $item[1]   = "��ݶ�";
    $item[2]   = "ê����";
    $item[3]   = "��ʧ����";
    $item[4]   = "ήư�����Ƕ��";
    $item[5]   = "û�����ն�";
    $item[6]   = "̤������";
    $item[7]   = "̤����������";
    $item[8]   = "̤��ˡ������";
    $item[9]   = "Ω�ض�";
    $item[10]  = "��ʧ��������";
    $item[11]  = "��ʧ��";
    $item[12]  = "����¾ήư��";
    $item[13]  = "ήư���ݰ�����";
    $item[14]  = "ήư�񻺷�";
    $item[15]  = "ͭ�������";
    $item[16]  = "���߲�����";
    $item[17]  = "���������߷׳�";
    $item[18]  = "ͭ������񻺷�";
    $item[19]  = "���եȥ�����";
    $item[20]  = "���ò�����";
    $item[21]  = "�������Ѹ�";
    $item[22]  = "̵������񻺷�";
    $item[23]  = "����񻺷�";
    $item[24]  = "Ĺ�����ն�";
    $item[25]  = "Ĺ����ʧ����";
    $item[26]  = "���귫���Ƕ��";
    $item[27]  = "�����߶��ݾڶ�";
    $item[28]  = "����¾�������";
    $item[29]  = "�������ݰ�����";
    $item[30]  = "��񤽤�¾�λ񻺷�";
    $item[31]  = "�񻺤������";
    $item[32]  = "��ʧ���";
    $item[33]  = "��ݶ�";
    $item[34]  = "û��������";
    $item[35]  = "�꡼����̳(û��)";
    $item[36]  = "̤ʧ��";
    $item[37]  = "̤ʧ������";
    $item[38]  = "̤ʧˡ������";
    $item[39]  = "̤ʧ����";
    $item[40]  = "�¤��";
    $item[41]  = "������������";
    $item[42]  = "����¾��ήư���";
    $item[43]  = "��Ϳ������";
    $item[44]  = "ήư��ķ�";
    $item[45]  = "Ĺ��������";
    $item[46]  = "�꡼����̳(Ĺ��)";
    $item[47]  = "Ĺ��̤ʧ��";
    $item[48]  = "�࿦���հ�����";
    $item[49]  = "����¾�θ������";
    $item[50]  = "������ķ�";
    $item[51]  = "��Ĥ������";
    $item[52]  = "���ܶ�";
    $item[53]  = "���ܶ��";
    $item[54]  = "���ܽ�����";
    $item[55]  = "����¾���ܾ�;��";
    $item[56]  = "���ܾ�;���";
    $item[57]  = "���׽�����";
    $item[58]  = "����¾���׾�;��";
    $item[59]  = "�������׾�;��";
    $item[60]  = "���׾�;���";
    $item[61]  = "����������";
    $item[62]  = "��񻺤������";
    $item[63]  = "��ĵڤӽ�񻺤���";
    $item[64]  = "̤������";
    $item[65]  = "��ʧ������";
for ($i = 0; $i < 66; $i++) {
    $res_in = array();
    $query = sprintf("select kin from profit_loss_bs_history where pl_bs_ym=%d and note='%s'", $pre_end_ym, $item[$i]);
    if (getUniResult($query, $res_in[$i][1]) < 1) {
        $res_in[$i][1] = 0;                 // ��������
    }
    $query = sprintf("select kin from profit_loss_bs_history where pl_bs_ym=%d and note='%s'", $yyyymm, $item[$i]);
    if (getUniResult($query, $res_in[$i][2]) < 1) {
        $res_in[$i][2] = 0;                 // ��������
    }
    $res_def  = array();
    //$item_def = $item[$i] . "Ĵ��";
    //$query = sprintf("select kin from profit_loss_bs_history where pl_bs_ym=%d and note='%s'", $pre_end_ym, $item_def);
    //if (getUniResult($query, $res_def[$i][1]) < 1) {
    //    $res_def[$i][1] = 0;                 // ��������
    //}
    $query = sprintf("select kin from profit_loss_bs_history where pl_bs_ym=%d and note='%s'", $yyyymm, $item_def);
    if (getUniResult($query, $res_def[$i][2]) < 1) {
        $res_def[$i][2] = 0;                 // ��������
    }
    $view_data[$i][1] = $res_in[$i][1] + $res_def[$i][1];
    $view_data[$i][2] = $res_in[$i][2] + $res_def[$i][2];
}

// �ƹ�פη׻���Ĵ�������줿�Ȥ��˼�ư�׻�����褦���ɲá�
if ($i == 14) {     // ήư�񻺷�
    $view_data[$i][1] = 0;
    $view_data[$i][2] = 0;
    for ($s = 1; $s < 14; $s++) {
        $view_data[$i][1] += $view_data[$s][1];  // ήư�񻺷ס�������
        $view_data[$i][2] += $view_data[$s][2];  // ήư�񻺷ס�������
    }
    // ̤�����פ��ɲ�
    $view_data[$i][1] += $view_data[64][1];  // ήư�񻺷ס�������
    $view_data[$i][2] += $view_data[64][2];  // ήư�񻺷ס�������
    // ��ʧ�����Ǥ��ɲ�
    $view_data[$i][1] += $view_data[65][1];  // ήư�񻺷ס�������
    $view_data[$i][2] += $view_data[65][2];  // ήư�񻺷ס�������
}
if ($i == 18) {     // ͭ������񻺷�
    $view_data[$i][1] = 0;
    $view_data[$i][2] = 0;
    for ($s = 15; $s < 18; $s++) {
        $view_data[$i][1] += $view_data[$s][1];  // ͭ������񻺷ס�������
        $view_data[$i][2] += $view_data[$s][2];  // ͭ������񻺷ס�������
    }
}
if ($i == 22) {     // ̵������񻺷�
    $view_data[$i][1] = 0;
    $view_data[$i][2] = 0;
    for ($s = 19; $s < 22; $s++) {
        $view_data[$i][1] += $view_data[$s][1];  // ̵������񻺷ס�������
        $view_data[$i][2] += $view_data[$s][2];  // ̵������񻺷ס�������
    }
}
if ($i == 23) {     // ����񻺷�
    $view_data[$i][1] = $view_data[18][1] + $view_data[22][1];  // ����񻺷ס�������
    $view_data[$i][2] = $view_data[18][2] + $view_data[22][2];  // ����񻺷ס�������
}
if ($i == 30) {     // ��񤽤�¾�λ񻺷�
    $view_data[$i][1] = 0;
    $view_data[$i][2] = 0;
    for ($s = 24; $s < 30; $s++) {
        $view_data[$i][1] += $view_data[$s][1];  // ��񤽤�¾�λ񻺷ס�������
        $view_data[$i][2] += $view_data[$s][2];  // ��񤽤�¾�λ񻺷ס�������
    }
}
if ($i == 31) {     // �񻺤������
    $view_data[$i][1] = $view_data[14][1] + $view_data[23][1] + $view_data[30][1];  // �񻺤�����ס�������
    $view_data[$i][2] = $view_data[14][2] + $view_data[23][2] + $view_data[30][2];  // �񻺤�����ס�������
}
if ($i == 44) {     // ήư��ķ�
    $view_data[$i][1] = 0;
    $view_data[$i][2] = 0;
    for ($s = 32; $s < 44; $s++) {
        $view_data[$i][1] += $view_data[$s][1];  // ήư��ķס�������
        $view_data[$i][2] += $view_data[$s][2];  // ήư��ķס�������
    }
}
if ($i == 50) {     // ������ķ�
    $view_data[$i][1] = 0;
    $view_data[$i][2] = 0;
    for ($s = 45; $s < 50; $s++) {
        $view_data[$i][1] += $view_data[$s][1];  // ������ķס�������
        $view_data[$i][2] += $view_data[$s][2];  // ������ķס�������
    }
}
if ($i == 51) {     // ��Ĥ������
    $view_data[$i][1] = $view_data[44][1] + $view_data[50][1];  // ��Ĥ�����ס�������
    $view_data[$i][2] = $view_data[44][2] + $view_data[50][2];  // ��Ĥ�����ס�������
}
if ($i == 53) {     // ���ܶ��
    $view_data[$i][1] = $view_data[52][1];  // ���ܶ�ס�������
    $view_data[$i][2] = $view_data[52][2];  // ���ܶ�ס�������
}
if ($i == 56) {     // ���ܾ�;���
    $view_data[$i][1] = 0;
    $view_data[$i][2] = 0;
    for ($s = 54; $s < 56; $s++) {
        $view_data[$i][1] += $view_data[$s][1];  // ���ܾ�;��ס�������
        $view_data[$i][2] += $view_data[$s][2];  // ���ܾ�;��ס�������
    }
}
if ($i == 60) {     // ���׾�;���
    $view_data[$i][1] = 0;
    $view_data[$i][2] = 0;
    for ($s = 57; $s < 60; $s++) {
        $view_data[$i][1] += $view_data[$s][1];  // ���׾�;��ס�������
        $view_data[$i][2] += $view_data[$s][2];  // ���׾�;��ס�������
    }
}
if ($i == 62) {     // ��񻺤������
    $view_data[$i][1] = $view_data[53][1] + $view_data[56][1] + $view_data[60][1];  // ��񻺤�����ס�������
    $view_data[$i][2] = $view_data[53][2] + $view_data[56][2] + $view_data[60][2];  // ��񻺤�����ס�������
}
if ($i == 63) {     // ��ĵڤӽ�񻺤���
    $view_data[$i][1] = $view_data[51][1] + $view_data[62][1];  // ��ĵڤӽ�񻺤�����������
    $view_data[$i][2] = $view_data[51][2] + $view_data[62][2];  // ��ĵڤӽ�񻺤�����������
}

// ���ۤ�ñ���ѹ�
for ($i = 0; $i < 66; $i++) {
    $view_data[$i][3] = $view_data[$i][2] - $view_data[$i][1];
    $view_data[$i][1] = number_format(($view_data[$i][1] / $tani), $keta);
    $view_data[$i][2] = number_format(($view_data[$i][2] / $tani), $keta);
    $view_data[$i][3] = number_format(($view_data[$i][3] / $tani), $keta);
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

<style type='text/css'>
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
.pt8 {
    font:normal 8pt;
    font-family: monospace;
}
.pt10 {
    font:normal 10pt;
    font-family: monospace;
}
.pt10b {
    font:bold 10pt;
    font-family: monospace;
}
.pt12b {
    font:bold 12pt;
    font-family: monospace;
}
.title_font {
    font:bold 13.5pt;
    font-family: monospace;
}
.today_font {
    font-size: 10.5pt;
    font-family: monospace;
}
.corporate_name {
    font:bold 10pt;
    font-family: monospace;
}
.margin0 {
    margin:0%;
}
.OnOff_font {
    font-size:     8.5pt;
    font-family:   monospace;
}
-->
</style>
</head>
<body>
    <center>
<?= $menu->out_title_border() ?>
        <table width='100%' border='1' cellspacing='1' cellpadding='1'>
            <tr>
                <td colspan='3' bgcolor='#d6d3ce' align='center' class='corporate_name'>
                    <?=$menu->out_caption(), "\n"?>
                </td>
                <form method='post' action='<?php echo $menu->out_self() ?>'>
                    <td colspan='15' bgcolor='#d6d3ce' align='right' class='pt10'>
                        ñ��
                        <select name='taisyaku_tani' class='pt10'>
                        <?php
                            if ($tani == 1000)
                                echo "<option value='1000' selected>�����</option>\n";
                            else
                                echo "<option value='1000'>�����</option>\n";
                            if ($tani == 1)
                                echo "<option value='1' selected>������</option>\n";
                            else
                                echo "<option value='1'>������</option>\n";
                            if ($tani == 1000000)
                                echo "<option value='1000000' selected>ɴ����</option>\n";
                            else
                                echo "<option value='1000000'>ɴ����</option>\n";
                            if ($tani == 10000)
                                echo "<option value='10000' selected>������</option>\n";
                            else
                                echo "<option value='10000'>������</option>\n";
                            if($tani == 100000)
                                echo "<option value='100000' selected>������</option>\n";
                            else
                                echo "<option value='100000'>������</option>\n";
                        ?>
                        </select>
                        ������
                        <select name='keihi_keta' class='pt10'>
                        <?php
                            if ($keta == 0)
                                echo "<option value='0' selected>����</option>\n";
                            else
                                echo "<option value='0'>����</option>\n";
                            if ($keta == 3)
                                echo "<option value='3' selected>����</option>\n";
                            else
                                echo "<option value='3'>����</option>\n";
                            if ($keta == 6)
                                echo "<option value='6' selected>����</option>\n";
                            else
                                echo "<option value='6'>����</option>\n";
                            if ($keta == 1)
                                echo "<option value='1' selected>����</option>\n";
                            else
                                echo "<option value='1'>����</option>\n";
                            if ($keta == 2)
                                echo "<option value='2' selected>����</option>\n";
                            else
                                echo "<option value='2'>����</option>\n";
                            if ($keta == 4)
                                echo "<option value='4' selected>����</option>\n";
                            else
                                echo "<option value='4'>����</option>\n";
                            if ($keta == 5)
                                echo "<option value='5' selected>����</option>\n";
                            else
                                echo "<option value='5'>����</option>\n";
                        ?>
                        </select>
                        <input class='pt10b' type='submit' name='return' value='ñ���ѹ�'>
                    </td>
                </form>
            </tr>
        </table>
        <?php
            //  bgcolor='#ceffce' ����
            //  bgcolor='#ffffc6' ��������
            //  bgcolor='#d6d3ce' Win ���쥤
        ?>
    <table width='81%' bgcolor='#d6d3ce' align='left' cellspacing="0" cellpadding="3" border='1'>
        <tr>
        <td>
        <table width='50%' bgcolor='#d6d3ce' align='left' cellspacing="0" cellpadding="3" border='1'>
            <TBODY>
                <tr>
                    <td colspan='3' width='200' align='center' class='pt10b' bgcolor='#ceffce'>�ʡ�������</td>
                    <td nowrap align='center' class='pt10b' bgcolor='#ceffce'>��<?php echo $p1_ki ?>��</td>
                    <td nowrap align='center' class='pt10b' bgcolor='#ceffce'>��<?php echo $ki ?>��</td>
                    <td nowrap align='center' class='pt8'   bgcolor='#ceffce'>����������</td>
                </tr>
                <tr>
                    <td rowspan='35' width='10' align='center' valign='middle' class='pt10b' bgcolor='#ceffce' style='border-right-style:none;'>�񻺤���</td>
                    <td rowspan='18' width='10' align='center' valign='middle' class='pt10b' bgcolor='#ffffc6' style='border-right-style:none;'>ήư��</td>
                    <td nowrap align='left' class='pt10b' bgcolor='white'>������ڤ��¶�</td>
                    <td nowrap align='right'  class='pt10b' bgcolor='white'><?php echo $view_data[0][1] ?></td>
                    <td nowrap align='right'  class='pt10b' bgcolor='white'><?php echo $view_data[0][2] ?></td>
                    <td nowrap align='right'  class='pt10b' bgcolor='white'><?php echo $view_data[0][3] ?></td>
                </tr>
                <tr>
                    <td nowrap align='left' class='pt10b' bgcolor='white'>���䡡 �� ����</td>
                    <td nowrap align='right'  class='pt10b' bgcolor='white'><?php echo $view_data[1][1] ?></td>
                    <td nowrap align='right'  class='pt10b' bgcolor='white'><?php echo $view_data[1][2] ?></td>
                    <td nowrap align='right'  class='pt10b' bgcolor='white'><?php echo $view_data[1][3] ?></td>
                </tr>
                <tr>
                    <td nowrap align='left' class='pt10b' bgcolor='white'>��ê �� �� ��</td>
                    <td nowrap align='right'  class='pt10b' bgcolor='white'><?php echo $view_data[2][1] ?></td>
                    <td nowrap align='right'  class='pt10b' bgcolor='white'><?php echo $view_data[2][2] ?></td>
                    <td nowrap align='right'  class='pt10b' bgcolor='white'><?php echo $view_data[2][3] ?></td>
                </tr>
                <tr>
                    <td nowrap align='left' class='pt10b' bgcolor='white'>���� ʧ �� ��</td>
                    <td nowrap align='right'  class='pt10b' bgcolor='white'><?php echo $view_data[3][1] ?></td>
                    <td nowrap align='right'  class='pt10b' bgcolor='white'><?php echo $view_data[3][2] ?></td>
                    <td nowrap align='right'  class='pt10b' bgcolor='white'><?php echo $view_data[3][3] ?></td>
                </tr>
                <tr>
                    <td nowrap align='left' class='pt10b' bgcolor='white'>�������Ƕ��</td>
                    <td nowrap align='right'  class='pt10b' bgcolor='white'><?php echo $view_data[4][1] ?></td>
                    <td nowrap align='right'  class='pt10b' bgcolor='white'><?php echo $view_data[4][2] ?></td>
                    <td nowrap align='right'  class='pt10b' bgcolor='white'><?php echo $view_data[4][3] ?></td>
                </tr>
                <tr>
                    <td nowrap align='left' class='pt10b' bgcolor='white'>��̤ �� �� ��</td>
                    <td nowrap align='right'  class='pt10b' bgcolor='white'><?php echo $view_data[64][1] ?></td>
                    <td nowrap align='right'  class='pt10b' bgcolor='white'><?php echo $view_data[64][2] ?></td>
                    <td nowrap align='right'  class='pt10b' bgcolor='white'><?php echo $view_data[64][3] ?></td>
                </tr>
                <tr>
                    <td nowrap align='left' class='pt10b' bgcolor='white'>��û�����ն�</td>
                    <td nowrap align='right'  class='pt10b' bgcolor='white'>��</td> <!-- $view_data[5][1] -->
                    <td nowrap align='right'  class='pt10b' bgcolor='white'>��</td>
                    <td nowrap align='right'  class='pt10b' bgcolor='white'>��</td>
                </tr>
                <tr>
                    <td nowrap align='left' class='pt10b' bgcolor='white'>��̤ �� �� ��</td>
                    <td nowrap align='right'  class='pt10b' bgcolor='white'><?php echo $view_data[6][1] ?></td>
                    <td nowrap align='right'  class='pt10b' bgcolor='white'><?php echo $view_data[6][2] ?></td>
                    <td nowrap align='right'  class='pt10b' bgcolor='white'><?php echo $view_data[6][3] ?></td>
                </tr>
                <tr>
                    <td nowrap align='left' class='pt10b' bgcolor='white'>��̤����������</td>
                    <td nowrap align='right'  class='pt10b' bgcolor='white'><?php echo $view_data[7][1] ?></td> <!-- ;�� -->
                    <td nowrap align='right'  class='pt10b' bgcolor='white'><?php echo $view_data[7][2] ?></td>
                    <td nowrap align='right'  class='pt10b' bgcolor='white'><?php echo $view_data[7][3] ?></td>
                </tr>
                <tr>
                    <td nowrap align='left' class='pt10b' bgcolor='white'>��̤��ˡ������</td>
                    <td nowrap align='right'  class='pt10b' bgcolor='white'><?php echo $view_data[8][1] ?></td>
                    <td nowrap align='right'  class='pt10b' bgcolor='white'><?php echo $view_data[8][2] ?></td>
                    <td nowrap align='right'  class='pt10b' bgcolor='white'><?php echo $view_data[8][3] ?></td>
                </tr>
                <tr>
                    <td nowrap align='left' class='pt10b' bgcolor='white'>��Ω�� �� ����</td>
                    <td nowrap align='right'  class='pt10b' bgcolor='white'><?php echo $view_data[9][1] ?></td>
                    <td nowrap align='right'  class='pt10b' bgcolor='white'><?php echo $view_data[9][2] ?></td>
                    <td nowrap align='right'  class='pt10b' bgcolor='white'><?php echo $view_data[9][3] ?></td>
                </tr>
                <tr>
                    <td nowrap align='left' class='pt10b' bgcolor='white'>����ʧ��������</td>
                    <td nowrap align='right'  class='pt10b' bgcolor='white'><?php echo $view_data[10][1] ?></td>
                    <td nowrap align='right'  class='pt10b' bgcolor='white'><?php echo $view_data[10][2] ?></td>
                    <td nowrap align='right'  class='pt10b' bgcolor='white'><?php echo $view_data[10][3] ?></td>
                </tr>
                <tr>
                    <td nowrap align='left' class='pt10b' bgcolor='white'>����ʧ������</td>
                    <td nowrap align='right'  class='pt10b' bgcolor='white'><?php echo $view_data[65][1] ?></td>
                    <td nowrap align='right'  class='pt10b' bgcolor='white'><?php echo $view_data[65][2] ?></td>
                    <td nowrap align='right'  class='pt10b' bgcolor='white'><?php echo $view_data[65][3] ?></td>
                </tr>
                <tr>
                    <td nowrap align='left' class='pt10b' bgcolor='white'>���� ��ʧ�� ��</td>
                    <td nowrap align='right'  class='pt10b' bgcolor='white'><?php echo $view_data[11][1] ?></td>
                    <td nowrap align='right'  class='pt10b' bgcolor='white'><?php echo $view_data[11][2] ?></td>
                    <td nowrap align='right'  class='pt10b' bgcolor='white'><?php echo $view_data[11][3] ?></td>
                </tr>
                <tr>
                    <td nowrap align='left' class='pt10b' bgcolor='white'>������¾ήư��</td>
                    <td nowrap align='right'  class='pt10b' bgcolor='white'><?php echo $view_data[12][1] ?></td>
                    <td nowrap align='right'  class='pt10b' bgcolor='white'><?php echo $view_data[12][2] ?></td>
                    <td nowrap align='right'  class='pt10b' bgcolor='white'><?php echo $view_data[12][3] ?></td>
                </tr>
                <tr>
                    <td nowrap align='left' class='pt10b' bgcolor='white'>�����ݰ�����</td>
                    <td nowrap align='right'  class='pt10b' bgcolor='white'>��</td> <!-- $view_data[13][1] -->
                    <td nowrap align='right'  class='pt10b' bgcolor='white'>��</td>
                    <td nowrap align='right'  class='pt10b' bgcolor='white'>��</td>
                </tr>
                <tr>
                    <td nowrap align='left' class='pt10b' bgcolor='white'>��</td>
                    <td nowrap align='right'  class='pt10b' bgcolor='white'>��</td> <!-- ;�� -->
                    <td nowrap align='right'  class='pt10b' bgcolor='white'>��</td>
                    <td nowrap align='right'  class='pt10b' bgcolor='white'>��</td>
                </tr>
                <tr>
                    <td nowrap align='center' class='pt10b' bgcolor='#ffffc6' style='border-left-style:none;'>��</td>
                    <td nowrap align='right'  class='pt10b' bgcolor='#ffffc6'><?php echo $view_data[14][1] ?></td>
                    <td nowrap align='right'  class='pt10b' bgcolor='#ffffc6'><?php echo $view_data[14][2] ?></td>
                    <td nowrap align='right'  class='pt10b' bgcolor='#ffffc6'><?php echo $view_data[14][3] ?></td>
                </tr>
                <tr>
                    <td rowspan='9' width='10' align='center' valign='middle' class='pt10b' bgcolor='#ffffc6' style='border-right-style:none;'>�����</td>
                    <td nowrap align='left' class='pt10b' bgcolor='white'>��ͭ�������</td>
                    <td nowrap align='right'  class='pt10b' bgcolor='white'><?php echo $view_data[15][1] ?></td>
                    <td nowrap align='right'  class='pt10b' bgcolor='white'><?php echo $view_data[15][2] ?></td>
                    <td nowrap align='right'  class='pt10b' bgcolor='white'><?php echo $view_data[15][3] ?></td>
                </tr>
                <tr>
                    <td nowrap align='left' class='pt10b' bgcolor='white'>�����߲�����</td>
                    <td nowrap align='right'  class='pt10b' bgcolor='white'><?php echo $view_data[16][1] ?></td>
                    <td nowrap align='right'  class='pt10b' bgcolor='white'><?php echo $view_data[16][2] ?></td>
                    <td nowrap align='right'  class='pt10b' bgcolor='white'><?php echo $view_data[16][3] ?></td>
                </tr>
                <tr>
                    <td nowrap align='left' class='pt10b' bgcolor='white'>�����������߷׳�</td>
                    <td nowrap align='right'  class='pt10b' bgcolor='white'><?php echo $view_data[17][1] ?></td>
                    <td nowrap align='right'  class='pt10b' bgcolor='white'><?php echo $view_data[17][2] ?></td>
                    <td nowrap align='right'  class='pt10b' bgcolor='white'><?php echo $view_data[17][3] ?></td>
                </tr>
                <tr>
                    <td nowrap align='center' class='pt10b' bgcolor='#ffffc6'>ͭ������� ��</td>
                    <td nowrap align='right'  class='pt10b' bgcolor='#ffffc6'><?php echo $view_data[18][1] ?></td>
                    <td nowrap align='right'  class='pt10b' bgcolor='#ffffc6'><?php echo $view_data[18][2] ?></td>
                    <td nowrap align='right'  class='pt10b' bgcolor='#ffffc6'><?php echo $view_data[18][3] ?></td>
                </tr>
                <tr>
                    <td nowrap align='left' class='pt10b' bgcolor='white'>�����եȥ�����</td>
                    <td nowrap align='right'  class='pt10b' bgcolor='white'><?php echo $view_data[19][1] ?></td>
                    <td nowrap align='right'  class='pt10b' bgcolor='white'><?php echo $view_data[19][2] ?></td>
                    <td nowrap align='right'  class='pt10b' bgcolor='white'><?php echo $view_data[19][3] ?></td>
                </tr>
                <tr>
                    <td nowrap align='left' class='pt10b' bgcolor='white'>�����ò�����</td>
                    <td nowrap align='right'  class='pt10b' bgcolor='white'><?php echo $view_data[20][1] ?></td>
                    <td nowrap align='right'  class='pt10b' bgcolor='white'><?php echo $view_data[20][2] ?></td>
                    <td nowrap align='right'  class='pt10b' bgcolor='white'><?php echo $view_data[20][3] ?></td>
                </tr>
                <tr>
                    <td nowrap align='left' class='pt10b' bgcolor='white'>���������Ѹ�</td>
                    <td nowrap align='right'  class='pt10b' bgcolor='white'><?php echo $view_data[21][1] ?></td>
                    <td nowrap align='right'  class='pt10b' bgcolor='white'><?php echo $view_data[21][2] ?></td>
                    <td nowrap align='right'  class='pt10b' bgcolor='white'><?php echo $view_data[21][3] ?></td>
                </tr>
                <tr>
                    <td nowrap align='center' class='pt10b' bgcolor='#ffffc6'>̵������� ��</td>
                    <td nowrap align='right'  class='pt10b' bgcolor='#ffffc6'><?php echo $view_data[22][1] ?></td>
                    <td nowrap align='right'  class='pt10b' bgcolor='#ffffc6'><?php echo $view_data[22][2] ?></td>
                    <td nowrap align='right'  class='pt10b' bgcolor='#ffffc6'><?php echo $view_data[22][3] ?></td>
                </tr>
                <tr>
                    <td nowrap align='center' class='pt10b' bgcolor='#ffffc6' style='border-left-style:none;'>��</td>
                    <td nowrap align='right'  class='pt10b' bgcolor='#ffffc6'><?php echo $view_data[23][1] ?></td>
                    <td nowrap align='right'  class='pt10b' bgcolor='#ffffc6'><?php echo $view_data[23][2] ?></td>
                    <td nowrap align='right'  class='pt10b' bgcolor='#ffffc6'><?php echo $view_data[23][3] ?></td>
                </tr>
                <tr>
                    <td rowspan='7' width='10' align='center' valign='middle' class='pt10b' bgcolor='#ffffc6' style='border-right-style:none;'>��񤽤�¾�λ�</td>
                    <td nowrap align='left' class='pt10b' bgcolor='white'>��Ĺ�����ն�</td>
                    <td nowrap align='right'  class='pt10b' bgcolor='white'><?php echo $view_data[24][1] ?></td>
                    <td nowrap align='right'  class='pt10b' bgcolor='white'><?php echo $view_data[24][2] ?></td>
                    <td nowrap align='right'  class='pt10b' bgcolor='white'><?php echo $view_data[24][3] ?></td>
                </tr>
                <tr>
                    <td nowrap align='left' class='pt10b' bgcolor='white'>��Ĺ����ʧ����</td>
                    <td nowrap align='right'  class='pt10b' bgcolor='white'><?php echo $view_data[25][1] ?></td>
                    <td nowrap align='right'  class='pt10b' bgcolor='white'><?php echo $view_data[25][2] ?></td>
                    <td nowrap align='right'  class='pt10b' bgcolor='white'><?php echo $view_data[25][3] ?></td>
                </tr>
                <tr>
                    <td nowrap align='left' class='pt10b' bgcolor='white'>�������Ƕ��</td>
                    <td nowrap align='right'  class='pt10b' bgcolor='white'><?php echo $view_data[26][1] ?></td>
                    <td nowrap align='right'  class='pt10b' bgcolor='white'><?php echo $view_data[26][2] ?></td>
                    <td nowrap align='right'  class='pt10b' bgcolor='white'><?php echo $view_data[26][3] ?></td>
                </tr>
                <tr>
                    <td nowrap align='left' class='pt10b' bgcolor='white'>�������߶��ݾڶ�</td>
                    <td nowrap align='right'  class='pt10b' bgcolor='white'><?php echo $view_data[27][1] ?></td>
                    <td nowrap align='right'  class='pt10b' bgcolor='white'><?php echo $view_data[27][2] ?></td>
                    <td nowrap align='right'  class='pt10b' bgcolor='white'><?php echo $view_data[27][3] ?></td>
                </tr>
                <tr>
                    <td nowrap align='left' class='pt10b' bgcolor='white'>������¾�������</td>
                    <td nowrap align='right'  class='pt10b' bgcolor='white'><?php echo $view_data[28][1] ?></td>
                    <td nowrap align='right'  class='pt10b' bgcolor='white'><?php echo $view_data[28][2] ?></td>
                    <td nowrap align='right'  class='pt10b' bgcolor='white'><?php echo $view_data[28][3] ?></td>
                </tr>
                <tr>
                    <td nowrap align='left' class='pt10b' bgcolor='white'>�����ݰ�����</td>
                    <td nowrap align='right'  class='pt10b' bgcolor='white'>��</td> <!-- $view_data[29][1] -->
                    <td nowrap align='right'  class='pt10b' bgcolor='white'>��</td>
                    <td nowrap align='right'  class='pt10b' bgcolor='white'>��</td>
                </tr>
                <tr>
                    <td nowrap align='center' class='pt10b' bgcolor='#ffffc6' style='border-left-style:none;'>��</td>
                    <td nowrap align='right'  class='pt10b' bgcolor='#ffffc6'><?php echo $view_data[30][1] ?></td>
                    <td nowrap align='right'  class='pt10b' bgcolor='#ffffc6'><?php echo $view_data[30][2] ?></td>
                    <td nowrap align='right'  class='pt10b' bgcolor='#ffffc6'><?php echo $view_data[30][3] ?></td>
                </tr>
                <tr>
                    <td colspan='2' align='center' class='pt10b' bgcolor='#ceffce' style='border-left-style:none;'>�񻺤��� ���</td>
                    <td nowrap align='right'  class='pt10b' bgcolor='#ceffce'><?php echo $view_data[31][1] ?></td>
                    <td nowrap align='right'  class='pt10b' bgcolor='#ceffce'><?php echo $view_data[31][2] ?></td>
                    <td nowrap align='right'  class='pt10b' bgcolor='#ceffce'><?php echo $view_data[31][3] ?></td>
                </tr>
            </TBODY>
        </table>
        </td>
        <td>
        <table width='50%' bgcolor='#d6d3ce' align='right' cellspacing="0" cellpadding="3" border='1'>
            <TBODY>
                <tr>
                    <td colspan='3' width='200' align='center' class='pt10b' bgcolor='#ceffce'>�ʡ�������</td>
                    <td nowrap align='center' class='pt10b' bgcolor='#ceffce'>��<?php echo $p1_ki ?>��</td>
                    <td nowrap align='center' class='pt10b' bgcolor='#ceffce'>��<?php echo $ki ?>��</td>
                    <td nowrap align='center' class='pt8'   bgcolor='#ceffce'>����������</td>
                </tr>
                <tr>
                    <td rowspan='21' width='10' align='center' valign='middle' class='pt10b' bgcolor='#ceffce' style='border-right-style:none;'>��Ĥ���</td>
                    <td rowspan='14' width='10' align='center' valign='middle' class='pt10b' bgcolor='#ffffc6' style='border-right-style:none;'>ήư���</td>
                    <td nowrap align='center' class='pt10b' bgcolor='white'>�١�ʧ���ꡡ��</td>
                    <td nowrap align='right'  class='pt10b' bgcolor='white'><?php echo $view_data[32][1] ?></td>
                    <td nowrap align='right'  class='pt10b' bgcolor='white'><?php echo $view_data[32][2] ?></td>
                    <td nowrap align='right'  class='pt10b' bgcolor='white'><?php echo $view_data[32][3] ?></td>
                </tr>
                <tr>
                    <td nowrap align='center' class='pt10b' bgcolor='white'>�㡡���ݡ�����</td>
                    <td nowrap align='right'  class='pt10b' bgcolor='white'><?php echo $view_data[33][1] ?></td>
                    <td nowrap align='right'  class='pt10b' bgcolor='white'><?php echo $view_data[33][2] ?></td>
                    <td nowrap align='right'  class='pt10b' bgcolor='white'><?php echo $view_data[33][3] ?></td>
                </tr>
                <tr>
                    <td nowrap align='center' class='pt10b' bgcolor='white'>û �� �� �� ��</td>
                    <td nowrap align='right'  class='pt10b' bgcolor='white'><?php echo $view_data[34][1] ?></td>
                    <td nowrap align='right'  class='pt10b' bgcolor='white'><?php echo $view_data[34][2] ?></td>
                    <td nowrap align='right'  class='pt10b' bgcolor='white'><?php echo $view_data[34][3] ?></td>
                </tr>
                <tr>
                    <td nowrap align='center' class='pt10b' bgcolor='white'>�꡼����̳(û��)</td>
                    <td nowrap align='right'  class='pt10b' bgcolor='white'><?php echo $view_data[35][1] ?></td>
                    <td nowrap align='right'  class='pt10b' bgcolor='white'><?php echo $view_data[35][2] ?></td>
                    <td nowrap align='right'  class='pt10b' bgcolor='white'><?php echo $view_data[35][3] ?></td>
                </tr>
                <tr>
                    <td nowrap align='center' class='pt10b' bgcolor='white'>̤����ʧ������</td>
                    <td nowrap align='right'  class='pt10b' bgcolor='white'><?php echo $view_data[36][1] ?></td>
                    <td nowrap align='right'  class='pt10b' bgcolor='white'><?php echo $view_data[36][2] ?></td>
                    <td nowrap align='right'  class='pt10b' bgcolor='white'><?php echo $view_data[36][3] ?></td>
                </tr>
                <tr>
                    <td nowrap align='center' class='pt10b' bgcolor='white'>̤ ʧ �� �� ��</td>
                    <td nowrap align='right'  class='pt10b' bgcolor='white'><?php echo $view_data[37][1] ?></td>
                    <td nowrap align='right'  class='pt10b' bgcolor='white'><?php echo $view_data[37][2] ?></td>
                    <td nowrap align='right'  class='pt10b' bgcolor='white'><?php echo $view_data[37][3] ?></td>
                </tr>
                <tr>
                    <td nowrap align='center' class='pt10b' bgcolor='white'>̤ʧˡ������</td>
                    <td nowrap align='right'  class='pt10b' bgcolor='white'><?php echo $view_data[38][1] ?></td>
                    <td nowrap align='right'  class='pt10b' bgcolor='white'><?php echo $view_data[38][2] ?></td>
                    <td nowrap align='right'  class='pt10b' bgcolor='white'><?php echo $view_data[38][3] ?></td>
                </tr>
                <tr>
                    <td nowrap align='center' class='pt10b' bgcolor='white'>̤��ʧ������</td>
                    <td nowrap align='right'  class='pt10b' bgcolor='white'><?php echo $view_data[39][1] ?></td>
                    <td nowrap align='right'  class='pt10b' bgcolor='white'><?php echo $view_data[39][2] ?></td>
                    <td nowrap align='right'  class='pt10b' bgcolor='white'><?php echo $view_data[39][3] ?></td>
                </tr>
                <tr>
                    <td nowrap align='center' class='pt10b' bgcolor='white'>�¡����ꡡ����</td>
                    <td nowrap align='right'  class='pt10b' bgcolor='white'><?php echo $view_data[40][1] ?></td>
                    <td nowrap align='right'  class='pt10b' bgcolor='white'><?php echo $view_data[40][2] ?></td>
                    <td nowrap align='right'  class='pt10b' bgcolor='white'><?php echo $view_data[40][3] ?></td>
                </tr>
                <tr>
                    <td nowrap align='center' class='pt10b' bgcolor='white'>������������</td>
                    <td nowrap align='right'  class='pt10b' bgcolor='white'><?php echo $view_data[41][1] ?></td>
                    <td nowrap align='right'  class='pt10b' bgcolor='white'><?php echo $view_data[41][2] ?></td>
                    <td nowrap align='right'  class='pt10b' bgcolor='white'><?php echo $view_data[41][3] ?></td>
                </tr>
                <tr>
                    <td nowrap align='center' class='pt10b' bgcolor='white'>����¾��ήư���</td>
                    <td nowrap align='right'  class='pt10b' bgcolor='white'><?php echo $view_data[42][1] ?></td>
                    <td nowrap align='right'  class='pt10b' bgcolor='white'><?php echo $view_data[42][2] ?></td>
                    <td nowrap align='right'  class='pt10b' bgcolor='white'><?php echo $view_data[42][3] ?></td>
                </tr>
                <tr>
                    <td nowrap align='center' class='pt10b' bgcolor='white'>�� Ϳ �� �� ��</td>
                    <td nowrap align='right'  class='pt10b' bgcolor='white'><?php echo $view_data[43][1] ?></td>
                    <td nowrap align='right'  class='pt10b' bgcolor='white'><?php echo $view_data[43][2] ?></td>
                    <td nowrap align='right'  class='pt10b' bgcolor='white'><?php echo $view_data[43][3] ?></td>
                </tr>
                <tr>
                    <td nowrap align='center' class='pt10b' bgcolor='white'>��</td>
                    <td nowrap align='right'  class='pt10b' bgcolor='white'>��</td>
                    <td nowrap align='right'  class='pt10b' bgcolor='white'>��</td>
                    <td nowrap align='right'  class='pt10b' bgcolor='white'>��</td>
                </tr>
                <tr>
                    <td nowrap align='center' class='pt10b' bgcolor='#ffffc6' style='border-left-style:none;'>��</td>
                    <td nowrap align='right'  class='pt10b' bgcolor='#ffffc6'><?php echo $view_data[44][1] ?></td>
                    <td nowrap align='right'  class='pt10b' bgcolor='#ffffc6'><?php echo $view_data[44][2] ?></td>
                    <td nowrap align='right'  class='pt10b' bgcolor='#ffffc6'><?php echo $view_data[44][3] ?></td>
                </tr>
                <tr>
                    <td rowspan='6' width='10' align='center' valign='middle' class='pt10b' bgcolor='#ffffc6' style='border-right-style:none;'>�������</td>
                    <td nowrap align='center' class='pt10b' bgcolor='white'>Ĺ �� �� �� ��</td>
                    <td nowrap align='right'  class='pt10b' bgcolor='white'><?php echo $view_data[45][1] ?></td>
                    <td nowrap align='right'  class='pt10b' bgcolor='white'><?php echo $view_data[45][2] ?></td>
                    <td nowrap align='right'  class='pt10b' bgcolor='white'><?php echo $view_data[45][3] ?></td>
                </tr>
                <tr>
                    <td nowrap align='center' class='pt10b' bgcolor='white'>�꡼����̳(Ĺ��)</td>
                    <td nowrap align='right'  class='pt10b' bgcolor='white'><?php echo $view_data[46][1] ?></td>
                    <td nowrap align='right'  class='pt10b' bgcolor='white'><?php echo $view_data[46][2] ?></td>
                    <td nowrap align='right'  class='pt10b' bgcolor='white'><?php echo $view_data[46][3] ?></td>
                </tr>
                <tr>
                    <td nowrap align='center' class='pt10b' bgcolor='white'>Ĺ �� ̤ ʧ ��</td>
                    <td nowrap align='right'  class='pt10b' bgcolor='white'><?php echo $view_data[47][1] ?></td>
                    <td nowrap align='right'  class='pt10b' bgcolor='white'><?php echo $view_data[47][2] ?></td>
                    <td nowrap align='right'  class='pt10b' bgcolor='white'><?php echo $view_data[47][3] ?></td>
                </tr>
                <tr>
                    <td nowrap align='center' class='pt10b' bgcolor='white'>�࿦���հ�����</td>
                    <td nowrap align='right'  class='pt10b' bgcolor='white'><?php echo $view_data[48][1] ?></td>
                    <td nowrap align='right'  class='pt10b' bgcolor='white'><?php echo $view_data[48][2] ?></td>
                    <td nowrap align='right'  class='pt10b' bgcolor='white'><?php echo $view_data[48][3] ?></td>
                </tr>
                <tr>
                    <td nowrap align='center' class='pt10b' bgcolor='white'>����¾�θ������</td>
                    <td nowrap align='right'  class='pt10b' bgcolor='white'>��</td> <!-- $view_data[49][1] -->
                    <td nowrap align='right'  class='pt10b' bgcolor='white'>��</td>
                    <td nowrap align='right'  class='pt10b' bgcolor='white'>��</td>
                </tr>
                <tr>
                    <td nowrap align='center' class='pt10b' bgcolor='#ffffc6' style='border-left-style:none;'>��</td>
                    <td nowrap align='right'  class='pt10b' bgcolor='#ffffc6'><?php echo $view_data[50][1] ?></td>
                    <td nowrap align='right'  class='pt10b' bgcolor='#ffffc6'><?php echo $view_data[50][2] ?></td>
                    <td nowrap align='right'  class='pt10b' bgcolor='#ffffc6'><?php echo $view_data[50][3] ?></td>
                </tr>
                <tr>
                    <td colspan='2' align='center' class='pt10b' bgcolor='#ceffce' style='border-left-style:none;'>��Ĥ��� ���</td>
                    <td nowrap align='right'  class='pt10b' bgcolor='#ceffce'><?php echo $view_data[51][1] ?></td>
                    <td nowrap align='right'  class='pt10b' bgcolor='#ceffce'><?php echo $view_data[51][2] ?></td>
                    <td nowrap align='right'  class='pt10b' bgcolor='#ceffce'><?php echo $view_data[51][3] ?></td>
                </tr>
                <tr>
                    <td rowspan='13' width='10' align='center' valign='middle' class='pt10b' bgcolor='#ceffce' style='border-right-style:none;'>��񻺤���</td><!--���ܤ���-->
                    <td rowspan='3'  width='10' align='center' valign='middle' class='pt10b' bgcolor='#ffffc6' style='border-right-style:none;'>���ܶ�</td>
                    <td nowrap align='center' class='pt10b' bgcolor='white'>�񡡡��ܡ�����</td>
                    <td nowrap align='right'  class='pt10b' bgcolor='white'><?php echo $view_data[52][1] ?></td>
                    <td nowrap align='right'  class='pt10b' bgcolor='white'><?php echo $view_data[52][2] ?></td>
                    <td nowrap align='right'  class='pt10b' bgcolor='white'><?php echo $view_data[52][3] ?></td>
                </tr>
                <tr>
                    <td nowrap align='center' class='pt10b' bgcolor='white'>��</td> <!-- ;�� -->
                    <td nowrap align='right'  class='pt10b' bgcolor='white'>��</td>
                    <td nowrap align='right'  class='pt10b' bgcolor='white'>��</td>
                    <td nowrap align='right'  class='pt10b' bgcolor='white'>��</td>
                </tr>
                <tr>
                    <td nowrap align='center' class='pt10b' bgcolor='#ffffc6' style='border-left-style:none;'>���ܶ� ��</td>
                    <td nowrap align='right'  class='pt10b' bgcolor='#ffffc6'><?php echo $view_data[53][1] ?></td>
                    <td nowrap align='right'  class='pt10b' bgcolor='#ffffc6'><?php echo $view_data[53][2] ?></td>
                    <td nowrap align='right'  class='pt10b' bgcolor='#ffffc6'><?php echo $view_data[53][3] ?></td>
                </tr>
                <tr>
                    <td rowspan='4' width='10' align='center' valign='middle' class='pt10b' bgcolor='#ffffc6' style='border-right-style:none;'>���ܾ�;��</td>
                    <td nowrap align='center' class='pt10b' bgcolor='white'>�� �� �� �� ��</td>
                    <td nowrap align='right'  class='pt10b' bgcolor='white'><?php echo $view_data[54][1] ?></td>
                    <td nowrap align='right'  class='pt10b' bgcolor='white'><?php echo $view_data[54][2] ?></td>
                    <td nowrap align='right'  class='pt10b' bgcolor='white'><?php echo $view_data[54][3] ?></td>
                </tr>
                <tr>
                    <td nowrap align='center' class='pt10b' bgcolor='white'>����¾���ܾ�;��</td>
                    <td nowrap align='right'  class='pt10b' bgcolor='white'><?php echo $view_data[55][1] ?></td>
                    <td nowrap align='right'  class='pt10b' bgcolor='white'><?php echo $view_data[55][2] ?></td>
                    <td nowrap align='right'  class='pt10b' bgcolor='white'><?php echo $view_data[55][3] ?></td>
                </tr>
                <tr>
                    <td nowrap align='center' class='pt10b' bgcolor='white'>��</td> <!-- ;�� -->
                    <td nowrap align='right'  class='pt10b' bgcolor='white'>��</td>
                    <td nowrap align='right'  class='pt10b' bgcolor='white'>��</td>
                    <td nowrap align='right'  class='pt10b' bgcolor='white'>��</td>
                </tr>
                <tr>
                    <td nowrap align='center' class='pt10b' bgcolor='#ffffc6' style='border-left-style:none;'>���ܾ�;�� ��</td>
                    <td nowrap align='right'  class='pt10b' bgcolor='#ffffc6'><?php echo $view_data[56][1] ?></td>
                    <td nowrap align='right'  class='pt10b' bgcolor='#ffffc6'><?php echo $view_data[56][2] ?></td>
                    <td nowrap align='right'  class='pt10b' bgcolor='#ffffc6'><?php echo $view_data[56][3] ?></td>
                </tr>
                <tr>
                    <td rowspan='4' width='10' align='center' valign='middle' class='pt10b' bgcolor='#ffffc6' style='border-right-style:none;'>���׾�;��</td>
                    <td nowrap align='center' class='pt10b' bgcolor='white'>�� �� �� �� ��</td>
                    <td nowrap align='right'  class='pt10b' bgcolor='white'>��</td> <!-- $view_data[57][1] -->
                    <td nowrap align='right'  class='pt10b' bgcolor='white'>��</td>
                    <td nowrap align='right'  class='pt10b' bgcolor='white'>��</td>
                </tr>
                <tr>
                    <td nowrap align='center' class='pt10b' bgcolor='white'>����¾���׾�;��</td>
                    <td nowrap align='right'  class='pt10b' bgcolor='white'><?php echo $view_data[58][1] ?></td>
                    <td nowrap align='right'  class='pt10b' bgcolor='white'><?php echo $view_data[58][2] ?></td>
                    <td nowrap align='right'  class='pt10b' bgcolor='white'><?php echo $view_data[58][3] ?></td>
                </tr>
                <tr>
                    <td nowrap align='center' class='pt10b' bgcolor='white'>�������׾�;��</td>
                    <td nowrap align='right'  class='pt10b' bgcolor='white'><?php echo $view_data[59][1] ?></td>
                    <td nowrap align='right'  class='pt10b' bgcolor='white'><?php echo $view_data[59][2] ?></td>
                    <td nowrap align='right'  class='pt10b' bgcolor='white'><?php echo $view_data[59][3] ?></td>
                </tr>
                <tr>
                    <td nowrap align='center' class='pt10b' bgcolor='#ffffc6' style='border-left-style:none;'>���׾�;�� ��</td>
                    <td nowrap align='right'  class='pt10b' bgcolor='#ffffc6'><?php echo $view_data[60][1] ?></td>
                    <td nowrap align='right'  class='pt10b' bgcolor='#ffffc6'><?php echo $view_data[60][2] ?></td>
                    <td nowrap align='right'  class='pt10b' bgcolor='#ffffc6'><?php echo $view_data[60][3] ?></td>
                </tr>
                <tr>
                    <td colspan='2' nowrap align='center' class='pt10b' bgcolor='#ffffc6'>�� �� �� �� ��</td>
                    <td nowrap align='right'  class='pt10b' bgcolor='#ffffc6'><?php echo $view_data[61][1] ?></td>
                    <td nowrap align='right'  class='pt10b' bgcolor='#ffffc6'><?php echo $view_data[61][2] ?></td>
                    <td nowrap align='right'  class='pt10b' bgcolor='#ffffc6'><?php echo $view_data[61][3] ?></td>
                </tr>
                <tr>
                    <td colspan='2' align='center' class='pt10b' bgcolor='#ceffce' style='border-left-style:none;'>��񻺤��� ���</td>
                    <td nowrap align='right'  class='pt10b' bgcolor='#ceffce'><?php echo $view_data[62][1] ?></td>
                    <td nowrap align='right'  class='pt10b' bgcolor='#ceffce'><?php echo $view_data[62][2] ?></td>
                    <td nowrap align='right'  class='pt10b' bgcolor='#ceffce'><?php echo $view_data[62][3] ?></td>
                </tr>
                <tr>
                    <td colspan='3' align='center' class='pt10b' bgcolor='#ceffce'>��ĵڤӽ�񻺤���</td>
                    <td nowrap align='right'  class='pt10b' bgcolor='#ceffce'><?php echo $view_data[63][1] ?></td>
                    <td nowrap align='right'  class='pt10b' bgcolor='#ceffce'><?php echo $view_data[63][2] ?></td>
                    <td nowrap align='right'  class='pt10b' bgcolor='#ceffce'><?php echo $view_data[63][3] ?></td>
                </tr>
            </TBODY>
        </table>
        </td>
        </tr>
    </table>
    </center>
</body>
</html>
