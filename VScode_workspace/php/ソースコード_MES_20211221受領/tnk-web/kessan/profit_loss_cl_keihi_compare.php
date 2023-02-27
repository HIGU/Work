<?php
//////////////////////////////////////////////////////////////////////////////
// �»�״ط� � �ã̷��� ���ɽ                                        //
// Copyright(C) 2008-2015 Norihisa.Ohya norihisa_ooya@nitto-kohki.co.jp     //
// Changed history                                                          //
// 2008/10/07 Created                                                       //
//            profit_loss_cl_keihi_compare.php(profit_loss_cl_keihi.php���)//
// 2010/01/20 �����פκ�����Ӥ��ɲ�                                      //
// 2010/02/08 ���ɤκ�����Ӥ��ɲá���οͷ���Ĵ����ޥ����������̣      //
// 2012/02/08 2012ǯ1�� ��̳������ Ĵ�� ��˥���¤���� +1,156,130��    ��ë //
//             �� ʿ�в����ɸ��� 2��˵�Ĵ����Ԥ�����                      //
// 2012/02/13 �ǡ������������ٷ׻��ǤϤʤ������򤫤�������ѹ�         ��ë //
//             �� �嵭��Ĵ����ɬ�פʤ�                                      //
// 2015/04/10 ���졼���б�����ɲä��б�                               ��ë //
//////////////////////////////////////////////////////////////////////////////
ini_set('error_reporting', E_STRICT);       // E_STRICT=2048(php5) E_ALL=2047 debug ��
// ini_set('error_reporting',E_ALL);           // E_ALL='2047' debug ��
// ini_set('display_errors','1');              // Error ɽ�� ON debug �� 
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

///// ������μ���
$ki = Ym_to_tnk($_SESSION['pl_ym']);
$tuki = substr($_SESSION['pl_ym'],4,2);

//////////// �����ȥ�̾(�������Υ����ȥ�̾�ȥե�����Υ����ȥ�̾)
$menu->set_title("�� {$ki} ����{$tuki} ���١��� �� �� �� �� �� �� �� ɽ");

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
///// ɽ��ñ�̤��������
if (isset($_POST['keihi_tani'])) {
    $_SESSION['keihi_tani'] = $_POST['keihi_tani'];
    $tani = $_SESSION['keihi_tani'];
} elseif (isset($_SESSION['keihi_tani'])) {
    $tani = $_SESSION['keihi_tani'];
} else {
    $tani = 1000;        // ����� ɽ��ñ�� ���
    $_SESSION['keihi_tani'] = $tani;
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

///////// ���ܤȥ���ǥå����δ�Ϣ�դ�
$item = array();
$item[0]   = "�����";
$item[1]   = "��������";
$item[2]   = "��Ϳ����";
$item[3]   = "������";
$item[4]   = "ˡ��ʡ����";
$item[5]   = "����ʡ����";
$item[6]   = "��Ϳ�����ⷫ��";
$item[7]   = "�࿦��������";
$item[8]   = "�ͷ����";
$item[9]   = "ι�������";
$item[10]  = "������ĥ";
$item[11]  = "�̿���";
$item[12]  = "�����";
$item[13]  = "���������";
$item[14]  = "����������";
$item[15]  = "�����";
$item[16]  = "���²�¤��";
$item[17]  = "�޽񶵰���";
$item[18]  = "��̳������";
$item[19]  = "������";
$item[20]  = "���Ǹ���";
$item[21]  = "�������";
$item[22]  = "����";
$item[23]  = "������";
$item[24]  = "�ݾڽ�����";
$item[25]  = "��̳�Ѿ�������";
$item[26]  = "�����������";
$item[27]  = "��ξ��";
$item[28]  = "�ݸ���";
$item[29]  = "��ƻ��Ǯ��";
$item[30]  = "������";
$item[31]  = "��ʧ�����";
$item[32]  = "�������";
$item[33]  = "���ն�";
$item[34]  = "������";
$item[35]  = "�¼���";
$item[36]  = "����������";
$item[37]  = "���졼���б���";
$item[38]  = "�����";
$item[39]  = "���";

for ($i = 0; $i < 40; $i++) {
    $head  = "���ץ���¤����";
    $item_in = array();
    $item_in[$i] = $head . $item[$i];
    $query = sprintf("select kin from profit_loss_keihi_history where pl_bs_ym=%d and note='%s'", $p1_ym, $item_in[$i]);
    if (getUniResult($query, $res_in[$i][1]) < 1) {
        $res_in[$i][1] = 0;                 // ��������
    }
    $query = sprintf("select kin from profit_loss_keihi_history where pl_bs_ym=%d and note='%s'", $yyyymm, $item_in[$i]);
    if (getUniResult($query, $res_in[$i][2]) < 1) {
        $res_in[$i][2] = 0;                 // ��������
    }
    $res_in[$i][3] = $res_in[$i][2] - $res_in[$i][1];
    
    $head  = "��˥���¤����";
    $item_in = array();
    $item_in[$i] = $head . $item[$i];
    $query = sprintf("select kin from profit_loss_keihi_history where pl_bs_ym=%d and note='%s'", $p1_ym, $item_in[$i]);
    if (getUniResult($query, $res_in[$i][4]) < 1) {
        $res_in[$i][4] = 0;                 // ��������
    }
    $query = sprintf("select kin from profit_loss_keihi_history where pl_bs_ym=%d and note='%s'", $yyyymm, $item_in[$i]);
    if (getUniResult($query, $res_in[$i][5]) < 1) {
        $res_in[$i][5] = 0;                 // ��������
    }
    $res_in[$i][6] = $res_in[$i][5] - $res_in[$i][4];
    
    $head  = "������¤����";
    $item_in = array();
    $item_in[$i] = $head . $item[$i];
    $query = sprintf("select kin from profit_loss_keihi_history where pl_bs_ym=%d and note='%s'", $p1_ym, $item_in[$i]);
    if (getUniResult($query, $res_in[$i][7]) < 1) {
        $res_in[$i][7] = 0;                 // ��������
    }
    $query = sprintf("select kin from profit_loss_keihi_history where pl_bs_ym=%d and note='%s'", $yyyymm, $item_in[$i]);
    if (getUniResult($query, $res_in[$i][8]) < 1) {
        $res_in[$i][8] = 0;                 // ��������
    }
    $res_in[$i][9] = $res_in[$i][8] - $res_in[$i][7];
    
    // ��¤�����׷׻�
    $res_in[$i][10] = $res_in[$i][1] + $res_in[$i][4] + $res_in[$i][7];
    $res_in[$i][11] = $res_in[$i][2] + $res_in[$i][5] + $res_in[$i][8];
    $res_in[$i][12] = $res_in[$i][3] + $res_in[$i][6] + $res_in[$i][9];
    
    $head  = "���ץ��δ���";
    $item_in = array();
    $item_in[$i] = $head . $item[$i];
    $query = sprintf("select kin from profit_loss_keihi_history where pl_bs_ym=%d and note='%s'", $p1_ym, $item_in[$i]);
    if (getUniResult($query, $res_in[$i][13]) < 1) {
        $res_in[$i][13] = 0;                 // ��������
    }
    $query = sprintf("select kin from profit_loss_keihi_history where pl_bs_ym=%d and note='%s'", $yyyymm, $item_in[$i]);
    if (getUniResult($query, $res_in[$i][14]) < 1) {
        $res_in[$i][14] = 0;                 // ��������
    }
    $res_in[$i][15] = $res_in[$i][14] - $res_in[$i][13];
    
    $head  = "��˥��δ���";
    $item_in = array();
    $item_in[$i] = $head . $item[$i];
    $query = sprintf("select kin from profit_loss_keihi_history where pl_bs_ym=%d and note='%s'", $p1_ym, $item_in[$i]);
    if (getUniResult($query, $res_in[$i][16]) < 1) {
        $res_in[$i][16] = 0;                 // ��������
    }
    $query = sprintf("select kin from profit_loss_keihi_history where pl_bs_ym=%d and note='%s'", $yyyymm, $item_in[$i]);
    if (getUniResult($query, $res_in[$i][17]) < 1) {
        $res_in[$i][17] = 0;                 // ��������
    }
    $res_in[$i][18] = $res_in[$i][17] - $res_in[$i][16];
    
    $head  = "�����δ���";
    $item_in = array();
    $item_in[$i] = $head . $item[$i];
    $query = sprintf("select kin from profit_loss_keihi_history where pl_bs_ym=%d and note='%s'", $p1_ym, $item_in[$i]);
    if (getUniResult($query, $res_in[$i][19]) < 1) {
        $res_in[$i][19] = 0;                 // ��������
    }
    $query = sprintf("select kin from profit_loss_keihi_history where pl_bs_ym=%d and note='%s'", $yyyymm, $item_in[$i]);
    if (getUniResult($query, $res_in[$i][20]) < 1) {
        $res_in[$i][20] = 0;                 // ��������
    }
    $res_in[$i][21] = $res_in[$i][20] - $res_in[$i][19];
    
    // �δ����׷׻�
    $res_in[$i][22] = $res_in[$i][13] + $res_in[$i][16] + $res_in[$i][19];
    $res_in[$i][23] = $res_in[$i][14] + $res_in[$i][17] + $res_in[$i][20];
    $res_in[$i][24] = $res_in[$i][15] + $res_in[$i][18] + $res_in[$i][21];
    
    // ���ץ�����׷׻�
    $res_in[$i][25] = $res_in[$i][1] + $res_in[$i][13];
    $res_in[$i][26] = $res_in[$i][2] + $res_in[$i][14];
    $res_in[$i][27] = $res_in[$i][3] + $res_in[$i][15];
    
    // ��˥������׷׻�
    $res_in[$i][28] = $res_in[$i][4] + $res_in[$i][16];
    $res_in[$i][29] = $res_in[$i][5] + $res_in[$i][17];
    $res_in[$i][30] = $res_in[$i][6] + $res_in[$i][18];
    
    // ���ɷ����׷׻�
    $res_in[$i][31] = $res_in[$i][7] + $res_in[$i][19];
    $res_in[$i][32] = $res_in[$i][8] + $res_in[$i][20];
    $res_in[$i][33] = $res_in[$i][9] + $res_in[$i][21];
    
    // �������׷׻�
    $res_in[$i][34] = $res_in[$i][25] + $res_in[$i][28] + $res_in[$i][31];
    $res_in[$i][35] = $res_in[$i][26] + $res_in[$i][29] + $res_in[$i][32];
    $res_in[$i][36] = $res_in[$i][27] + $res_in[$i][30] + $res_in[$i][33];
    
    $view_data[$i][1]  = number_format(($res_in[$i][1] / $tani), $keta);
    $view_data[$i][2]  = number_format(($res_in[$i][2] / $tani), $keta);
    $view_data[$i][3]  = number_format(($res_in[$i][3] / $tani), $keta);
    $view_data[$i][4]  = number_format(($res_in[$i][4] / $tani), $keta);
    $view_data[$i][5]  = number_format(($res_in[$i][5] / $tani), $keta);
    $view_data[$i][6]  = number_format(($res_in[$i][6] / $tani), $keta);
    $view_data[$i][7]  = number_format(($res_in[$i][7] / $tani), $keta);
    $view_data[$i][8]  = number_format(($res_in[$i][8] / $tani), $keta);
    $view_data[$i][9]  = number_format(($res_in[$i][9] / $tani), $keta);
    $view_data[$i][10] = number_format(($res_in[$i][10] / $tani), $keta);
    $view_data[$i][11] = number_format(($res_in[$i][11] / $tani), $keta);
    $view_data[$i][12] = number_format(($res_in[$i][12] / $tani), $keta);
    $view_data[$i][13] = number_format(($res_in[$i][13] / $tani), $keta);
    $view_data[$i][14] = number_format(($res_in[$i][14] / $tani), $keta);
    $view_data[$i][15] = number_format(($res_in[$i][15] / $tani), $keta);
    $view_data[$i][16] = number_format(($res_in[$i][16] / $tani), $keta);
    $view_data[$i][17] = number_format(($res_in[$i][17] / $tani), $keta);
    $view_data[$i][18] = number_format(($res_in[$i][18] / $tani), $keta);
    $view_data[$i][19] = number_format(($res_in[$i][19] / $tani), $keta);
    $view_data[$i][20] = number_format(($res_in[$i][20] / $tani), $keta);
    $view_data[$i][21] = number_format(($res_in[$i][21] / $tani), $keta);
    $view_data[$i][22] = number_format(($res_in[$i][22] / $tani), $keta);
    $view_data[$i][23] = number_format(($res_in[$i][23] / $tani), $keta);
    $view_data[$i][24] = number_format(($res_in[$i][24] / $tani), $keta);
    $view_data[$i][25] = number_format(($res_in[$i][25] / $tani), $keta);
    $view_data[$i][26] = number_format(($res_in[$i][26] / $tani), $keta);
    $view_data[$i][27] = number_format(($res_in[$i][27] / $tani), $keta);
    $view_data[$i][28] = number_format(($res_in[$i][28] / $tani), $keta);
    $view_data[$i][29] = number_format(($res_in[$i][29] / $tani), $keta);
    $view_data[$i][30] = number_format(($res_in[$i][30] / $tani), $keta);
    $view_data[$i][31] = number_format(($res_in[$i][31] / $tani), $keta);
    $view_data[$i][32] = number_format(($res_in[$i][32] / $tani), $keta);
    $view_data[$i][33] = number_format(($res_in[$i][33] / $tani), $keta);
    $view_data[$i][34] = number_format(($res_in[$i][34] / $tani), $keta);
    $view_data[$i][35] = number_format(($res_in[$i][35] / $tani), $keta);
    $view_data[$i][36] = number_format(($res_in[$i][36] / $tani), $keta);
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
                <td colspan='2' bgcolor='#d6d3ce' align='center' class='corporate_name'>
                    <?=$menu->out_caption(), "\n"?>
                </td>
                <form method='post' action='<?php echo $menu->out_self() ?>'>
                    <td colspan='13' bgcolor='#d6d3ce' align='right' class='pt10'>
                        ñ��
                        <select name='keihi_tani' class='pt10'>
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
        <!-- win_gray='#d6d3ce' -->
        <table width='100%' bgcolor='white' align='center' cellspacing="0" cellpadding="3" border='1'>
            <TBODY>
                <tr>
                    <td width='10' rowspan='3' align='center' class='pt10' bgcolor='#ccffff'>��ʬ</td>
                    <td rowspan='3' nowrap align='center' class='pt10b' bgcolor='#ccffff'>�������</td>
                    <td colspan='12' nowrap align='center' class='pt10b' bgcolor='#ffffc6'>����¤���С���</td>
                    <td colspan='12' nowrap align='center' class='pt10b' bgcolor='#ceffce'>������ڤӰ��̴�����</td>
                    <td colspan='12' nowrap align='center' class='pt10b' bgcolor='#ccffff'>�С��񡡹硡��</td>
                </tr>
                <tr>
                    <td colspan='3' nowrap align='center' class='pt10b' bgcolor='#ffffc6'>���ץ�</td>
                    <td colspan='3' nowrap align='center' class='pt10b' bgcolor='#ffffc6'>��˥�</td>
                    <td colspan='3' nowrap align='center' class='pt10b' bgcolor='#ffffc6'>����</td>
                    <td colspan='3' nowrap align='center' class='pt10b' bgcolor='#ffffc6'>���</td>
                    <td colspan='3' nowrap align='center' class='pt10b' bgcolor='#ceffce'>���ץ�</td>
                    <td colspan='3' nowrap align='center' class='pt10b' bgcolor='#ceffce'>��˥�</td>
                    <td colspan='3' nowrap align='center' class='pt10b' bgcolor='#ceffce'>����</td>
                    <td colspan='3' nowrap align='center' class='pt10b' bgcolor='#ceffce'>���</td>
                    <td colspan='3' nowrap align='center' class='pt10b' bgcolor='#ccffff'>���ץ�</td>
                    <td colspan='3' nowrap align='center' class='pt10b' bgcolor='#ccffff'>��˥�</td>
                    <td colspan='3' nowrap align='center' class='pt10b' bgcolor='#ccffff'>����</td>
                    <td colspan='3' nowrap align='center' class='pt10b' bgcolor='#ccffff'>���</td>
                </tr>
                <tr>
                    <td nowrap align='center' class='pt10b' bgcolor='#ffffc6'>����</td>
                    <td nowrap align='center' class='pt10b' bgcolor='#ffffc6'>����</td>
                    <td nowrap align='center' class='pt10b' bgcolor='#ffffc6'>����</td>
                    <td nowrap align='center' class='pt10b' bgcolor='#ffffc6'>����</td>
                    <td nowrap align='center' class='pt10b' bgcolor='#ffffc6'>����</td>
                    <td nowrap align='center' class='pt10b' bgcolor='#ffffc6'>����</td>
                    <td nowrap align='center' class='pt10b' bgcolor='#ffffc6'>����</td>
                    <td nowrap align='center' class='pt10b' bgcolor='#ffffc6'>����</td>
                    <td nowrap align='center' class='pt10b' bgcolor='#ffffc6'>����</td>
                    <td nowrap align='center' class='pt10b' bgcolor='#ffffc6'>����</td>
                    <td nowrap align='center' class='pt10b' bgcolor='#ffffc6'>����</td>
                    <td nowrap align='center' class='pt10b' bgcolor='#ffffc6'>����</td>
                    <td nowrap align='center' class='pt10b' bgcolor='#ceffce'>����</td>
                    <td nowrap align='center' class='pt10b' bgcolor='#ceffce'>����</td>
                    <td nowrap align='center' class='pt10b' bgcolor='#ceffce'>����</td>
                    <td nowrap align='center' class='pt10b' bgcolor='#ceffce'>����</td>
                    <td nowrap align='center' class='pt10b' bgcolor='#ceffce'>����</td>
                    <td nowrap align='center' class='pt10b' bgcolor='#ceffce'>����</td>
                    <td nowrap align='center' class='pt10b' bgcolor='#ceffce'>����</td>
                    <td nowrap align='center' class='pt10b' bgcolor='#ceffce'>����</td>
                    <td nowrap align='center' class='pt10b' bgcolor='#ceffce'>����</td>
                    <td nowrap align='center' class='pt10b' bgcolor='#ceffce'>����</td>
                    <td nowrap align='center' class='pt10b' bgcolor='#ceffce'>����</td>
                    <td nowrap align='center' class='pt10b' bgcolor='#ceffce'>����</td>
                    <td nowrap align='center' class='pt10b' bgcolor='#ccffff'>����</td>
                    <td nowrap align='center' class='pt10b' bgcolor='#ccffff'>����</td>
                    <td nowrap align='center' class='pt10b' bgcolor='#ccffff'>����</td>
                    <td nowrap align='center' class='pt10b' bgcolor='#ccffff'>����</td>
                    <td nowrap align='center' class='pt10b' bgcolor='#ccffff'>����</td>
                    <td nowrap align='center' class='pt10b' bgcolor='#ccffff'>����</td>
                    <td nowrap align='center' class='pt10b' bgcolor='#ccffff'>����</td>
                    <td nowrap align='center' class='pt10b' bgcolor='#ccffff'>����</td>
                    <td nowrap align='center' class='pt10b' bgcolor='#ccffff'>����</td>
                    <td nowrap align='center' class='pt10b' bgcolor='#ccffff'>����</td>
                    <td nowrap align='center' class='pt10b' bgcolor='#ccffff'>����</td>
                    <td nowrap align='center' class='pt10b' bgcolor='#ccffff'>����</td>
                </tr>
                <tr>
                    <td width='10' rowspan='9' align='center' class='pt10b' bgcolor='#ccffff'>�ͷ���</td>
                    <TD nowrap class='pt10'>�����</TD>
                    <?php
                        $r = 0;     // �����쥳����
                        for ($c=1;$c<37;$c++) {
                            if ($c == 3 || $c == 6 || $c == 9 || $c == 12) {                // ��¤��������
                                printf("<td nowrap class='pt10b' align='right' bgcolor='#ffffc6'>%s</td>\n",$view_data[$r][$c]);
                            } elseif ($c == 15 || $c == 18 || $c == 21 || $c == 24) {       // �δ�������
                                printf("<td nowrap class='pt10b' align='right' bgcolor='#ceffce'>%s</td>\n",$view_data[$r][$c]);
                            } elseif ($c == 27 || $c == 30 || $c == 33 || $c == 36) {       // ����������
                                printf("<td nowrap class='pt10b' align='right' bgcolor='#ccffff'>%s</td>\n",$view_data[$r][$c]);
                            } else {
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ffffff'>%s</td>\n",$view_data[$r][$c]);
                            }
                        }
                    ?>
                </tr>
                <TR>
                    <TD nowrap class='pt10'>��������</TD>
                    <?php
                        $r = 1;     // �����쥳����
                        for ($c=1;$c<37;$c++) {
                            if ($c == 3 || $c == 6 || $c == 9 || $c == 12) {                // ��¤��������
                                printf("<td nowrap class='pt10b' align='right' bgcolor='#ffffc6'>%s</td>\n",$view_data[$r][$c]);
                            } elseif ($c == 15 || $c == 18 || $c == 21 || $c == 24) {       // �δ�������
                                printf("<td nowrap class='pt10b' align='right' bgcolor='#ceffce'>%s</td>\n",$view_data[$r][$c]);
                            } elseif ($c == 27 || $c == 30 || $c == 33 || $c == 36) {       // ����������
                                printf("<td nowrap class='pt10b' align='right' bgcolor='#ccffff'>%s</td>\n",$view_data[$r][$c]);
                            } else {
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ffffff'>%s</td>\n",$view_data[$r][$c]);
                            }
                        }
                    ?>
                </TR>
                <TR>
                    <TD nowrap class='pt10'>��Ϳ����</TD>
                    <?php
                        $r = 2;     // �����쥳����
                        for ($c=1;$c<37;$c++) {
                            if ($c == 3 || $c == 6 || $c == 9 || $c == 12) {                // ��¤��������
                                printf("<td nowrap class='pt10b' align='right' bgcolor='#ffffc6'>%s</td>\n",$view_data[$r][$c]);
                            } elseif ($c == 15 || $c == 18 || $c == 21 || $c == 24) {       // �δ�������
                                printf("<td nowrap class='pt10b' align='right' bgcolor='#ceffce'>%s</td>\n",$view_data[$r][$c]);
                            } elseif ($c == 27 || $c == 30 || $c == 33 || $c == 36) {       // ����������
                                printf("<td nowrap class='pt10b' align='right' bgcolor='#ccffff'>%s</td>\n",$view_data[$r][$c]);
                            } else {
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ffffff'>%s</td>\n",$view_data[$r][$c]);
                            }
                        }
                    ?>
                </TR>
                <TR>
                    <TD nowrap class='pt10'>������</TD>
                    <?php
                        $r = 3;     // �����쥳����
                        for ($c=1;$c<37;$c++) {
                            if ($c == 3 || $c == 6 || $c == 9 || $c == 12) {                // ��¤��������
                                printf("<td nowrap class='pt10b' align='right' bgcolor='#ffffc6'>%s</td>\n",$view_data[$r][$c]);
                            } elseif ($c == 15 || $c == 18 || $c == 21 || $c == 24) {       // �δ�������
                                printf("<td nowrap class='pt10b' align='right' bgcolor='#ceffce'>%s</td>\n",$view_data[$r][$c]);
                            } elseif ($c == 27 || $c == 30 || $c == 33 || $c == 36) {       // ����������
                                printf("<td nowrap class='pt10b' align='right' bgcolor='#ccffff'>%s</td>\n",$view_data[$r][$c]);
                            } else {
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ffffff'>%s</td>\n",$view_data[$r][$c]);
                            }
                        }
                    ?>
                </TR>
                <TR>
                    <TD nowrap class='pt10'>ˡ��ʡ����</TD>
                    <?php
                        $r = 4;     // �����쥳����
                        for ($c=1;$c<37;$c++) {
                            if ($c == 3 || $c == 6 || $c == 9 || $c == 12) {                // ��¤��������
                                printf("<td nowrap class='pt10b' align='right' bgcolor='#ffffc6'>%s</td>\n",$view_data[$r][$c]);
                            } elseif ($c == 15 || $c == 18 || $c == 21 || $c == 24) {       // �δ�������
                                printf("<td nowrap class='pt10b' align='right' bgcolor='#ceffce'>%s</td>\n",$view_data[$r][$c]);
                            } elseif ($c == 27 || $c == 30 || $c == 33 || $c == 36) {       // ����������
                                printf("<td nowrap class='pt10b' align='right' bgcolor='#ccffff'>%s</td>\n",$view_data[$r][$c]);
                            } else {
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ffffff'>%s</td>\n",$view_data[$r][$c]);
                            }
                        }
                    ?>
                </TR>
                <TR>
                    <TD nowrap class='pt10'>����ʡ����</TD>
                    <?php
                        $r = 5;     // �����쥳����
                        for ($c=1;$c<37;$c++) {
                            if ($c == 3 || $c == 6 || $c == 9 || $c == 12) {                // ��¤��������
                                printf("<td nowrap class='pt10b' align='right' bgcolor='#ffffc6'>%s</td>\n",$view_data[$r][$c]);
                            } elseif ($c == 15 || $c == 18 || $c == 21 || $c == 24) {       // �δ�������
                                printf("<td nowrap class='pt10b' align='right' bgcolor='#ceffce'>%s</td>\n",$view_data[$r][$c]);
                            } elseif ($c == 27 || $c == 30 || $c == 33 || $c == 36) {       // ����������
                                printf("<td nowrap class='pt10b' align='right' bgcolor='#ccffff'>%s</td>\n",$view_data[$r][$c]);
                            } else {
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ffffff'>%s</td>\n",$view_data[$r][$c]);
                            }
                        }
                    ?>
                </TR>
                <TR>
                    <TD nowrap class='pt10'>��Ϳ�����ⷫ��</TD>
                    <?php
                        $r = 6;     // �����쥳����
                        for ($c=1;$c<37;$c++) {
                            if ($c == 3 || $c == 6 || $c == 9 || $c == 12) {                // ��¤��������
                                printf("<td nowrap class='pt10b' align='right' bgcolor='#ffffc6'>%s</td>\n",$view_data[$r][$c]);
                            } elseif ($c == 15 || $c == 18 || $c == 21 || $c == 24) {       // �δ�������
                                printf("<td nowrap class='pt10b' align='right' bgcolor='#ceffce'>%s</td>\n",$view_data[$r][$c]);
                            } elseif ($c == 27 || $c == 30 || $c == 33 || $c == 36) {       // ����������
                                printf("<td nowrap class='pt10b' align='right' bgcolor='#ccffff'>%s</td>\n",$view_data[$r][$c]);
                            } else {
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ffffff'>%s</td>\n",$view_data[$r][$c]);
                            }
                        }
                    ?>
                </TR>
                <TR>
                    <TD nowrap class='pt10'>�࿦��������</TD>
                    <?php
                        $r = 7;     // �����쥳����
                        for ($c=1;$c<37;$c++) {
                            if ($c == 3 || $c == 6 || $c == 9 || $c == 12) {                // ��¤��������
                                printf("<td nowrap class='pt10b' align='right' bgcolor='#ffffc6'>%s</td>\n",$view_data[$r][$c]);
                            } elseif ($c == 15 || $c == 18 || $c == 21 || $c == 24) {       // �δ�������
                                printf("<td nowrap class='pt10b' align='right' bgcolor='#ceffce'>%s</td>\n",$view_data[$r][$c]);
                            } elseif ($c == 27 || $c == 30 || $c == 33 || $c == 36) {       // ����������
                                printf("<td nowrap class='pt10b' align='right' bgcolor='#ccffff'>%s</td>\n",$view_data[$r][$c]);
                            } else {
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ffffff'>%s</td>\n",$view_data[$r][$c]);
                            }
                        }
                    ?>
                </TR>
                <TR bgcolor='#ccffff'>
                    <TD nowrap class='pt10b' align='right'>�ͷ����</TD>
                    <?php
                        $r = 8;     // �����쥳����
                        for ($c=1;$c<37;$c++) {
                            printf("<td nowrap class='pt10b' align='right' bgcolor='#ccffff'>%s</td>\n",$view_data[$r][$c]);
                        }
                    ?>
                </TR>
                <tr>
                    <td width='10' rowspan='30' align='center' class='pt10b' bgcolor='#ccffff'>����</td>
                    <TD nowrap class='pt10'>ι�������</TD>
                    <?php
                        $r = 9;     // �����쥳����
                        for ($c=1;$c<37;$c++) {
                            if ($c == 3 || $c == 6 || $c == 9 || $c == 12) {                // ��¤��������
                                printf("<td nowrap class='pt10b' align='right' bgcolor='#ffffc6'>%s</td>\n",$view_data[$r][$c]);
                            } elseif ($c == 15 || $c == 18 || $c == 21 || $c == 24) {       // �δ�������
                                printf("<td nowrap class='pt10b' align='right' bgcolor='#ceffce'>%s</td>\n",$view_data[$r][$c]);
                            } elseif ($c == 27 || $c == 30 || $c == 33 || $c == 36) {       // ����������
                                printf("<td nowrap class='pt10b' align='right' bgcolor='#ccffff'>%s</td>\n",$view_data[$r][$c]);
                            } else {
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ffffff'>%s</td>\n",$view_data[$r][$c]);
                            }
                        }
                    ?>
                </tr>
                <tr>
                    <TD nowrap class='pt10'>������ĥ</TD>
                    <?php
                        $r = 10;     // �����쥳����
                        for ($c=1;$c<37;$c++) {
                            if ($c == 3 || $c == 6 || $c == 9 || $c == 12) {                // ��¤��������
                                printf("<td nowrap class='pt10b' align='right' bgcolor='#ffffc6'>%s</td>\n",$view_data[$r][$c]);
                            } elseif ($c == 15 || $c == 18 || $c == 21 || $c == 24) {       // �δ�������
                                printf("<td nowrap class='pt10b' align='right' bgcolor='#ceffce'>%s</td>\n",$view_data[$r][$c]);
                            } elseif ($c == 27 || $c == 30 || $c == 33 || $c == 36) {       // ����������
                                printf("<td nowrap class='pt10b' align='right' bgcolor='#ccffff'>%s</td>\n",$view_data[$r][$c]);
                            } else {
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ffffff'>%s</td>\n",$view_data[$r][$c]);
                            }
                        }
                    ?>
                </tr>
                <tr>
                    <TD nowrap class='pt10'>�̡�������</TD>
                    <?php
                        $r = 11;     // �����쥳����
                        for ($c=1;$c<37;$c++) {
                            if ($c == 3 || $c == 6 || $c == 9 || $c == 12) {                // ��¤��������
                                printf("<td nowrap class='pt10b' align='right' bgcolor='#ffffc6'>%s</td>\n",$view_data[$r][$c]);
                            } elseif ($c == 15 || $c == 18 || $c == 21 || $c == 24) {       // �δ�������
                                printf("<td nowrap class='pt10b' align='right' bgcolor='#ceffce'>%s</td>\n",$view_data[$r][$c]);
                            } elseif ($c == 27 || $c == 30 || $c == 33 || $c == 36) {       // ����������
                                printf("<td nowrap class='pt10b' align='right' bgcolor='#ccffff'>%s</td>\n",$view_data[$r][$c]);
                            } else {
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ffffff'>%s</td>\n",$view_data[$r][$c]);
                            }
                        }
                    ?>
                </tr>
                <tr>
                    <TD nowrap class='pt10'>�񡡵ġ���</TD>
                    <?php
                        $r = 12;     // �����쥳����
                        for ($c=1;$c<37;$c++) {
                            if ($c == 3 || $c == 6 || $c == 9 || $c == 12) {                // ��¤��������
                                printf("<td nowrap class='pt10b' align='right' bgcolor='#ffffc6'>%s</td>\n",$view_data[$r][$c]);
                            } elseif ($c == 15 || $c == 18 || $c == 21 || $c == 24) {       // �δ�������
                                printf("<td nowrap class='pt10b' align='right' bgcolor='#ceffce'>%s</td>\n",$view_data[$r][$c]);
                            } elseif ($c == 27 || $c == 30 || $c == 33 || $c == 36) {       // ����������
                                printf("<td nowrap class='pt10b' align='right' bgcolor='#ccffff'>%s</td>\n",$view_data[$r][$c]);
                            } else {
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ffffff'>%s</td>\n",$view_data[$r][$c]);
                            }
                        }
                    ?>
                </tr>
                <tr>
                    <TD nowrap class='pt10'>���������</TD>
                    <?php
                        $r = 13;     // �����쥳����
                        for ($c=1;$c<37;$c++) {
                            if ($c == 3 || $c == 6 || $c == 9 || $c == 12) {                // ��¤��������
                                printf("<td nowrap class='pt10b' align='right' bgcolor='#ffffc6'>%s</td>\n",$view_data[$r][$c]);
                            } elseif ($c == 15 || $c == 18 || $c == 21 || $c == 24) {       // �δ�������
                                printf("<td nowrap class='pt10b' align='right' bgcolor='#ceffce'>%s</td>\n",$view_data[$r][$c]);
                            } elseif ($c == 27 || $c == 30 || $c == 33 || $c == 36) {       // ����������
                                printf("<td nowrap class='pt10b' align='right' bgcolor='#ccffff'>%s</td>\n",$view_data[$r][$c]);
                            } else {
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ffffff'>%s</td>\n",$view_data[$r][$c]);
                            }
                        }
                    ?>
                </tr>
                <tr>
                    <TD nowrap class='pt10'>����������</TD>
                    <?php
                        $r = 14;     // �����쥳����
                        for ($c=1;$c<37;$c++) {
                            if ($c == 3 || $c == 6 || $c == 9 || $c == 12) {                // ��¤��������
                                printf("<td nowrap class='pt10b' align='right' bgcolor='#ffffc6'>%s</td>\n",$view_data[$r][$c]);
                            } elseif ($c == 15 || $c == 18 || $c == 21 || $c == 24) {       // �δ�������
                                printf("<td nowrap class='pt10b' align='right' bgcolor='#ceffce'>%s</td>\n",$view_data[$r][$c]);
                            } elseif ($c == 27 || $c == 30 || $c == 33 || $c == 36) {       // ����������
                                printf("<td nowrap class='pt10b' align='right' bgcolor='#ccffff'>%s</td>\n",$view_data[$r][$c]);
                            } else {
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ffffff'>%s</td>\n",$view_data[$r][$c]);
                            }
                        }
                    ?>
                </tr>
                <tr>
                    <TD nowrap class='pt10'>�ᡡ�͡���</TD>
                    <?php
                        $r = 15;     // �����쥳����
                        for ($c=1;$c<37;$c++) {
                            if ($c == 3 || $c == 6 || $c == 9 || $c == 12) {                // ��¤��������
                                printf("<td nowrap class='pt10b' align='right' bgcolor='#ffffc6'>%s</td>\n",$view_data[$r][$c]);
                            } elseif ($c == 15 || $c == 18 || $c == 21 || $c == 24) {       // �δ�������
                                printf("<td nowrap class='pt10b' align='right' bgcolor='#ceffce'>%s</td>\n",$view_data[$r][$c]);
                            } elseif ($c == 27 || $c == 30 || $c == 33 || $c == 36) {       // ����������
                                printf("<td nowrap class='pt10b' align='right' bgcolor='#ccffff'>%s</td>\n",$view_data[$r][$c]);
                            } else {
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ffffff'>%s</td>\n",$view_data[$r][$c]);
                            }
                        }
                    ?>
                </tr>
                <tr>
                    <TD nowrap class='pt10'>���²�¤��</TD>
                    <?php
                        $r = 16;     // �����쥳����
                        for ($c=1;$c<37;$c++) {
                            if ($c == 3 || $c == 6 || $c == 9 || $c == 12) {                // ��¤��������
                                printf("<td nowrap class='pt10b' align='right' bgcolor='#ffffc6'>%s</td>\n",$view_data[$r][$c]);
                            } elseif ($c == 15 || $c == 18 || $c == 21 || $c == 24) {       // �δ�������
                                printf("<td nowrap class='pt10b' align='right' bgcolor='#ceffce'>%s</td>\n",$view_data[$r][$c]);
                            } elseif ($c == 27 || $c == 30 || $c == 33 || $c == 36) {       // ����������
                                printf("<td nowrap class='pt10b' align='right' bgcolor='#ccffff'>%s</td>\n",$view_data[$r][$c]);
                            } else {
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ffffff'>%s</td>\n",$view_data[$r][$c]);
                            }
                        }
                    ?>
                </tr>
                <tr>
                    <TD nowrap class='pt10'>�޽񶵰���</TD>
                    <?php
                        $r = 17;     // �����쥳����
                        for ($c=1;$c<37;$c++) {
                            if ($c == 3 || $c == 6 || $c == 9 || $c == 12) {                // ��¤��������
                                printf("<td nowrap class='pt10b' align='right' bgcolor='#ffffc6'>%s</td>\n",$view_data[$r][$c]);
                            } elseif ($c == 15 || $c == 18 || $c == 21 || $c == 24) {       // �δ�������
                                printf("<td nowrap class='pt10b' align='right' bgcolor='#ceffce'>%s</td>\n",$view_data[$r][$c]);
                            } elseif ($c == 27 || $c == 30 || $c == 33 || $c == 36) {       // ����������
                                printf("<td nowrap class='pt10b' align='right' bgcolor='#ccffff'>%s</td>\n",$view_data[$r][$c]);
                            } else {
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ffffff'>%s</td>\n",$view_data[$r][$c]);
                            }
                        }
                    ?>
                </tr>
                <tr>
                    <TD nowrap class='pt10'>��̳������</TD>
                    <?php
                        $r = 18;     // �����쥳����
                        for ($c=1;$c<37;$c++) {
                            if ($c == 3 || $c == 6 || $c == 9 || $c == 12) {                // ��¤��������
                                printf("<td nowrap class='pt10b' align='right' bgcolor='#ffffc6'>%s</td>\n",$view_data[$r][$c]);
                            } elseif ($c == 15 || $c == 18 || $c == 21 || $c == 24) {       // �δ�������
                                printf("<td nowrap class='pt10b' align='right' bgcolor='#ceffce'>%s</td>\n",$view_data[$r][$c]);
                            } elseif ($c == 27 || $c == 30 || $c == 33 || $c == 36) {       // ����������
                                printf("<td nowrap class='pt10b' align='right' bgcolor='#ccffff'>%s</td>\n",$view_data[$r][$c]);
                            } else {
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ffffff'>%s</td>\n",$view_data[$r][$c]);
                            }
                        }
                    ?>
                </tr>
                <tr>
                    <td nowrap class='pt10'>�����ȡ���</td>
                    <?php
                        $r = 19;     // �����쥳����
                        for ($c=1;$c<37;$c++) {
                            if ($c == 3 || $c == 6 || $c == 9 || $c == 12) {                // ��¤��������
                                printf("<td nowrap class='pt10b' align='right' bgcolor='#ffffc6'>%s</td>\n",$view_data[$r][$c]);
                            } elseif ($c == 15 || $c == 18 || $c == 21 || $c == 24) {       // �δ�������
                                printf("<td nowrap class='pt10b' align='right' bgcolor='#ceffce'>%s</td>\n",$view_data[$r][$c]);
                            } elseif ($c == 27 || $c == 30 || $c == 33 || $c == 36) {       // ����������
                                printf("<td nowrap class='pt10b' align='right' bgcolor='#ccffff'>%s</td>\n",$view_data[$r][$c]);
                            } else {
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ffffff'>%s</td>\n",$view_data[$r][$c]);
                            }
                        }
                    ?>
                </tr>
                <tr>
                    <TD nowrap class='pt10'>���Ǹ���</TD>
                    <?php
                        $r = 20;     // �����쥳����
                        for ($c=1;$c<37;$c++) {
                            if ($c == 3 || $c == 6 || $c == 9 || $c == 12) {                // ��¤��������
                                printf("<td nowrap class='pt10b' align='right' bgcolor='#ffffc6'>%s</td>\n",$view_data[$r][$c]);
                            } elseif ($c == 15 || $c == 18 || $c == 21 || $c == 24) {       // �δ�������
                                printf("<td nowrap class='pt10b' align='right' bgcolor='#ceffce'>%s</td>\n",$view_data[$r][$c]);
                            } elseif ($c == 27 || $c == 30 || $c == 33 || $c == 36) {       // ����������
                                printf("<td nowrap class='pt10b' align='right' bgcolor='#ccffff'>%s</td>\n",$view_data[$r][$c]);
                            } else {
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ffffff'>%s</td>\n",$view_data[$r][$c]);
                            }
                        }
                    ?>
                </tr>
                <tr>
                    <TD nowrap class='pt10'>�������</TD>
                    <?php
                        $r = 21;     // �����쥳����
                        for ($c=1;$c<37;$c++) {
                            if ($c == 3 || $c == 6 || $c == 9 || $c == 12) {                // ��¤��������
                                printf("<td nowrap class='pt10b' align='right' bgcolor='#ffffc6'>%s</td>\n",$view_data[$r][$c]);
                            } elseif ($c == 15 || $c == 18 || $c == 21 || $c == 24) {       // �δ�������
                                printf("<td nowrap class='pt10b' align='right' bgcolor='#ceffce'>%s</td>\n",$view_data[$r][$c]);
                            } elseif ($c == 27 || $c == 30 || $c == 33 || $c == 36) {       // ����������
                                printf("<td nowrap class='pt10b' align='right' bgcolor='#ccffff'>%s</td>\n",$view_data[$r][$c]);
                            } else {
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ffffff'>%s</td>\n",$view_data[$r][$c]);
                            }
                        }
                    ?>
                </tr>
                <tr>
                    <TD nowrap class='pt10'>����������</TD>
                    <?php
                        $r = 22;     // �����쥳����
                        for ($c=1;$c<37;$c++) {
                            if ($c == 3 || $c == 6 || $c == 9 || $c == 12) {                // ��¤��������
                                printf("<td nowrap class='pt10b' align='right' bgcolor='#ffffc6'>%s</td>\n",$view_data[$r][$c]);
                            } elseif ($c == 15 || $c == 18 || $c == 21 || $c == 24) {       // �δ�������
                                printf("<td nowrap class='pt10b' align='right' bgcolor='#ceffce'>%s</td>\n",$view_data[$r][$c]);
                            } elseif ($c == 27 || $c == 30 || $c == 33 || $c == 36) {       // ����������
                                printf("<td nowrap class='pt10b' align='right' bgcolor='#ccffff'>%s</td>\n",$view_data[$r][$c]);
                            } else {
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ffffff'>%s</td>\n",$view_data[$r][$c]);
                            }
                        }
                    ?>
                </tr>
                <tr>
                    <TD nowrap class='pt10'>����������</TD>
                    <?php
                        $r = 23;     // �����쥳����
                        for ($c=1;$c<37;$c++) {
                            if ($c == 3 || $c == 6 || $c == 9 || $c == 12) {                // ��¤��������
                                printf("<td nowrap class='pt10b' align='right' bgcolor='#ffffc6'>%s</td>\n",$view_data[$r][$c]);
                            } elseif ($c == 15 || $c == 18 || $c == 21 || $c == 24) {       // �δ�������
                                printf("<td nowrap class='pt10b' align='right' bgcolor='#ceffce'>%s</td>\n",$view_data[$r][$c]);
                            } elseif ($c == 27 || $c == 30 || $c == 33 || $c == 36) {       // ����������
                                printf("<td nowrap class='pt10b' align='right' bgcolor='#ccffff'>%s</td>\n",$view_data[$r][$c]);
                            } else {
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ffffff'>%s</td>\n",$view_data[$r][$c]);
                            }
                        }
                    ?>
                </tr>
                <tr>
                    <TD nowrap class='pt10'>�ݾڽ�����</TD>
                    <?php
                        $r = 24;     // �����쥳����
                        for ($c=1;$c<37;$c++) {
                            if ($c == 3 || $c == 6 || $c == 9 || $c == 12) {                // ��¤��������
                                printf("<td nowrap class='pt10b' align='right' bgcolor='#ffffc6'>%s</td>\n",$view_data[$r][$c]);
                            } elseif ($c == 15 || $c == 18 || $c == 21 || $c == 24) {       // �δ�������
                                printf("<td nowrap class='pt10b' align='right' bgcolor='#ceffce'>%s</td>\n",$view_data[$r][$c]);
                            } elseif ($c == 27 || $c == 30 || $c == 33 || $c == 36) {       // ����������
                                printf("<td nowrap class='pt10b' align='right' bgcolor='#ccffff'>%s</td>\n",$view_data[$r][$c]);
                            } else {
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ffffff'>%s</td>\n",$view_data[$r][$c]);
                            }
                        }
                    ?>
                </tr>
                <tr>
                    <TD nowrap class='pt10'>��̳�Ѿ�������</TD>
                    <?php
                        $r = 25;     // �����쥳����
                        for ($c=1;$c<37;$c++) {
                            if ($c == 3 || $c == 6 || $c == 9 || $c == 12) {                // ��¤��������
                                printf("<td nowrap class='pt10b' align='right' bgcolor='#ffffc6'>%s</td>\n",$view_data[$r][$c]);
                            } elseif ($c == 15 || $c == 18 || $c == 21 || $c == 24) {       // �δ�������
                                printf("<td nowrap class='pt10b' align='right' bgcolor='#ceffce'>%s</td>\n",$view_data[$r][$c]);
                            } elseif ($c == 27 || $c == 30 || $c == 33 || $c == 36) {       // ����������
                                printf("<td nowrap class='pt10b' align='right' bgcolor='#ccffff'>%s</td>\n",$view_data[$r][$c]);
                            } else {
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ffffff'>%s</td>\n",$view_data[$r][$c]);
                            }
                        }
                    ?>
                </tr>
                <tr>
                    <TD nowrap class='pt10'>�����������</TD>
                    <?php
                        $r = 26;     // �����쥳����
                        for ($c=1;$c<37;$c++) {
                            if ($c == 3 || $c == 6 || $c == 9 || $c == 12) {                // ��¤��������
                                printf("<td nowrap class='pt10b' align='right' bgcolor='#ffffc6'>%s</td>\n",$view_data[$r][$c]);
                            } elseif ($c == 15 || $c == 18 || $c == 21 || $c == 24) {       // �δ�������
                                printf("<td nowrap class='pt10b' align='right' bgcolor='#ceffce'>%s</td>\n",$view_data[$r][$c]);
                            } elseif ($c == 27 || $c == 30 || $c == 33 || $c == 36) {       // ����������
                                printf("<td nowrap class='pt10b' align='right' bgcolor='#ccffff'>%s</td>\n",$view_data[$r][$c]);
                            } else {
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ffffff'>%s</td>\n",$view_data[$r][$c]);
                            }
                        }
                    ?>
                </tr>
                <tr>
                    <TD nowrap class='pt10'>�֡�ξ����</TD>
                    <?php
                        $r = 27;     // �����쥳����
                        for ($c=1;$c<37;$c++) {
                            if ($c == 3 || $c == 6 || $c == 9 || $c == 12) {                // ��¤��������
                                printf("<td nowrap class='pt10b' align='right' bgcolor='#ffffc6'>%s</td>\n",$view_data[$r][$c]);
                            } elseif ($c == 15 || $c == 18 || $c == 21 || $c == 24) {       // �δ�������
                                printf("<td nowrap class='pt10b' align='right' bgcolor='#ceffce'>%s</td>\n",$view_data[$r][$c]);
                            } elseif ($c == 27 || $c == 30 || $c == 33 || $c == 36) {       // ����������
                                printf("<td nowrap class='pt10b' align='right' bgcolor='#ccffff'>%s</td>\n",$view_data[$r][$c]);
                            } else {
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ffffff'>%s</td>\n",$view_data[$r][$c]);
                            }
                        }
                    ?>
                </tr>
                <tr>
                    <TD nowrap class='pt10'>�ݡ�������</TD>
                    <?php
                        $r = 28;     // �����쥳����
                        for ($c=1;$c<37;$c++) {
                            if ($c == 3 || $c == 6 || $c == 9 || $c == 12) {                // ��¤��������
                                printf("<td nowrap class='pt10b' align='right' bgcolor='#ffffc6'>%s</td>\n",$view_data[$r][$c]);
                            } elseif ($c == 15 || $c == 18 || $c == 21 || $c == 24) {       // �δ�������
                                printf("<td nowrap class='pt10b' align='right' bgcolor='#ceffce'>%s</td>\n",$view_data[$r][$c]);
                            } elseif ($c == 27 || $c == 30 || $c == 33 || $c == 36) {       // ����������
                                printf("<td nowrap class='pt10b' align='right' bgcolor='#ccffff'>%s</td>\n",$view_data[$r][$c]);
                            } else {
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ffffff'>%s</td>\n",$view_data[$r][$c]);
                            }
                        }
                    ?>
                </tr>
                <tr>
                    <TD nowrap class='pt10'>��ƻ��Ǯ��</TD>
                    <?php
                        $r = 29;     // �����쥳����
                        for ($c=1;$c<37;$c++) {
                            if ($c == 3 || $c == 6 || $c == 9 || $c == 12) {                // ��¤��������
                                printf("<td nowrap class='pt10b' align='right' bgcolor='#ffffc6'>%s</td>\n",$view_data[$r][$c]);
                            } elseif ($c == 15 || $c == 18 || $c == 21 || $c == 24) {       // �δ�������
                                printf("<td nowrap class='pt10b' align='right' bgcolor='#ceffce'>%s</td>\n",$view_data[$r][$c]);
                            } elseif ($c == 27 || $c == 30 || $c == 33 || $c == 36) {       // ����������
                                printf("<td nowrap class='pt10b' align='right' bgcolor='#ccffff'>%s</td>\n",$view_data[$r][$c]);
                            } else {
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ffffff'>%s</td>\n",$view_data[$r][$c]);
                            }
                        }
                    ?>
                </tr>
                <tr>
                    <TD nowrap class='pt10'>��������</TD>
                    <?php
                        $r = 30;     // �����쥳����
                        for ($c=1;$c<37;$c++) {
                            if ($c == 3 || $c == 6 || $c == 9 || $c == 12) {                // ��¤��������
                                printf("<td nowrap class='pt10b' align='right' bgcolor='#ffffc6'>%s</td>\n",$view_data[$r][$c]);
                            } elseif ($c == 15 || $c == 18 || $c == 21 || $c == 24) {       // �δ�������
                                printf("<td nowrap class='pt10b' align='right' bgcolor='#ceffce'>%s</td>\n",$view_data[$r][$c]);
                            } elseif ($c == 27 || $c == 30 || $c == 33 || $c == 36) {       // ����������
                                printf("<td nowrap class='pt10b' align='right' bgcolor='#ccffff'>%s</td>\n",$view_data[$r][$c]);
                            } else {
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ffffff'>%s</td>\n",$view_data[$r][$c]);
                            }
                        }
                    ?>
                </tr>
                <tr>
                    <TD nowrap class='pt10'>��ʧ�����</TD>
                    <?php
                        $r = 31;     // �����쥳����
                        for ($c=1;$c<37;$c++) {
                            if ($c == 3 || $c == 6 || $c == 9 || $c == 12) {                // ��¤��������
                                printf("<td nowrap class='pt10b' align='right' bgcolor='#ffffc6'>%s</td>\n",$view_data[$r][$c]);
                            } elseif ($c == 15 || $c == 18 || $c == 21 || $c == 24) {       // �δ�������
                                printf("<td nowrap class='pt10b' align='right' bgcolor='#ceffce'>%s</td>\n",$view_data[$r][$c]);
                            } elseif ($c == 27 || $c == 30 || $c == 33 || $c == 36) {       // ����������
                                printf("<td nowrap class='pt10b' align='right' bgcolor='#ccffff'>%s</td>\n",$view_data[$r][$c]);
                            } else {
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ffffff'>%s</td>\n",$view_data[$r][$c]);
                            }
                        }
                    ?>
                </tr>
                <tr>
                    <TD nowrap class='pt10'>�������</TD>
                    <?php
                        $r = 32;     // �����쥳����
                        for ($c=1;$c<37;$c++) {
                            if ($c == 3 || $c == 6 || $c == 9 || $c == 12) {                // ��¤��������
                                printf("<td nowrap class='pt10b' align='right' bgcolor='#ffffc6'>%s</td>\n",$view_data[$r][$c]);
                            } elseif ($c == 15 || $c == 18 || $c == 21 || $c == 24) {       // �δ�������
                                printf("<td nowrap class='pt10b' align='right' bgcolor='#ceffce'>%s</td>\n",$view_data[$r][$c]);
                            } elseif ($c == 27 || $c == 30 || $c == 33 || $c == 36) {       // ����������
                                printf("<td nowrap class='pt10b' align='right' bgcolor='#ccffff'>%s</td>\n",$view_data[$r][$c]);
                            } else {
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ffffff'>%s</td>\n",$view_data[$r][$c]);
                            }
                        }
                    ?>
                </tr>
                <tr>
                    <TD nowrap class='pt10'>���ա���</TD>
                    <?php
                        $r = 33;     // �����쥳����
                        for ($c=1;$c<37;$c++) {
                            if ($c == 3 || $c == 6 || $c == 9 || $c == 12) {                // ��¤��������
                                printf("<td nowrap class='pt10b' align='right' bgcolor='#ffffc6'>%s</td>\n",$view_data[$r][$c]);
                            } elseif ($c == 15 || $c == 18 || $c == 21 || $c == 24) {       // �δ�������
                                printf("<td nowrap class='pt10b' align='right' bgcolor='#ceffce'>%s</td>\n",$view_data[$r][$c]);
                            } elseif ($c == 27 || $c == 30 || $c == 33 || $c == 36) {       // ����������
                                printf("<td nowrap class='pt10b' align='right' bgcolor='#ccffff'>%s</td>\n",$view_data[$r][$c]);
                            } else {
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ffffff'>%s</td>\n",$view_data[$r][$c]);
                            }
                        }
                    ?>
                </tr>
                <tr>
                    <TD nowrap class='pt10'>�ҡ��ߡ���</TD>
                    <?php
                        $r = 34;     // �����쥳����
                        for ($c=1;$c<37;$c++) {
                            if ($c == 3 || $c == 6 || $c == 9 || $c == 12) {                // ��¤��������
                                printf("<td nowrap class='pt10b' align='right' bgcolor='#ffffc6'>%s</td>\n",$view_data[$r][$c]);
                            } elseif ($c == 15 || $c == 18 || $c == 21 || $c == 24) {       // �δ�������
                                printf("<td nowrap class='pt10b' align='right' bgcolor='#ceffce'>%s</td>\n",$view_data[$r][$c]);
                            } elseif ($c == 27 || $c == 30 || $c == 33 || $c == 36) {       // ����������
                                printf("<td nowrap class='pt10b' align='right' bgcolor='#ccffff'>%s</td>\n",$view_data[$r][$c]);
                            } else {
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ffffff'>%s</td>\n",$view_data[$r][$c]);
                            }
                        }
                    ?>
                </tr>
                <tr>
                    <TD nowrap class='pt10'>�¡��ڡ���</TD>
                    <?php
                        $r = 35;     // �����쥳����
                        for ($c=1;$c<37;$c++) {
                            if ($c == 3 || $c == 6 || $c == 9 || $c == 12) {                // ��¤��������
                                printf("<td nowrap class='pt10b' align='right' bgcolor='#ffffc6'>%s</td>\n",$view_data[$r][$c]);
                            } elseif ($c == 15 || $c == 18 || $c == 21 || $c == 24) {       // �δ�������
                                printf("<td nowrap class='pt10b' align='right' bgcolor='#ceffce'>%s</td>\n",$view_data[$r][$c]);
                            } elseif ($c == 27 || $c == 30 || $c == 33 || $c == 36) {       // ����������
                                printf("<td nowrap class='pt10b' align='right' bgcolor='#ccffff'>%s</td>\n",$view_data[$r][$c]);
                            } else {
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ffffff'>%s</td>\n",$view_data[$r][$c]);
                            }
                        }
                    ?>
                </tr>
                <tr>
                    <TD nowrap class='pt10'>����������</TD>
                    <?php
                        $r = 36;     // �����쥳����
                        for ($c=1;$c<37;$c++) {
                            if ($c == 3 || $c == 6 || $c == 9 || $c == 12) {                // ��¤��������
                                printf("<td nowrap class='pt10b' align='right' bgcolor='#ffffc6'>%s</td>\n",$view_data[$r][$c]);
                            } elseif ($c == 15 || $c == 18 || $c == 21 || $c == 24) {       // �δ�������
                                printf("<td nowrap class='pt10b' align='right' bgcolor='#ceffce'>%s</td>\n",$view_data[$r][$c]);
                            } elseif ($c == 27 || $c == 30 || $c == 33 || $c == 36) {       // ����������
                                printf("<td nowrap class='pt10b' align='right' bgcolor='#ccffff'>%s</td>\n",$view_data[$r][$c]);
                            } else {
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ffffff'>%s</td>\n",$view_data[$r][$c]);
                            }
                        }
                    ?>
                </tr>
                <tr>
                    <TD nowrap class='pt10'>���졼���б���</TD>
                    <?php
                        $r = 37;     // �����쥳����
                        for ($c=1;$c<37;$c++) {
                            if ($c == 3 || $c == 6 || $c == 9 || $c == 12) {                // ��¤��������
                                printf("<td nowrap class='pt10b' align='right' bgcolor='#ffffc6'>%s</td>\n",$view_data[$r][$c]);
                            } elseif ($c == 15 || $c == 18 || $c == 21 || $c == 24) {       // �δ�������
                                printf("<td nowrap class='pt10b' align='right' bgcolor='#ceffce'>%s</td>\n",$view_data[$r][$c]);
                            } elseif ($c == 27 || $c == 30 || $c == 33 || $c == 36) {       // ����������
                                printf("<td nowrap class='pt10b' align='right' bgcolor='#ccffff'>%s</td>\n",$view_data[$r][$c]);
                            } else {
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ffffff'>%s</td>\n",$view_data[$r][$c]);
                            }
                        }
                    ?>
                </tr>
                <tr bgcolor='#ccffff'>
                    <TD nowrap class='pt10b' align='right'>�����</TD>
                    <?php
                        $r = 38;     // �����쥳����
                        for ($c=1;$c<37;$c++) {
                            printf("<td nowrap class='pt10b' align='right' bgcolor='#ccffff'>%s</td>\n",$view_data[$r][$c]);
                        }
                    ?>
                </tr>
                <tr bgcolor='#ccffff'>
                    <TD colspan='2' nowrap class='pt10b' align='right'>�硡��</TD>
                    <?php
                        $r = 39;     // �����쥳����
                        for ($c=1;$c<37;$c++) {
                            printf("<td nowrap class='pt10b' align='right' bgcolor='#ccffff'>%s</td>\n",$view_data[$r][$c]);
                        }
                    ?>
                </tr>
            </TBODY>
        </table>
    </center>
</body>
</html>
