<?php
//////////////////////////////////////////////////////////////////////////////
// �»�״ط� � �����������ɽ �������ɽ                              //
// Copyright(C) 2012-2015 Norihisa.Ohya norihisa_ooya@nitto-kohki.co.jp     //
// Changed history                                                          //
// 2012/01/26 Created   profit_loss_keihi_2ki.php                           //
// 2012/04/18 �裴��Ⱦ���Τ�ɽ����������äƤ����Τ��б�                    //
// 2015/02/20 ���졼���б����ɲäΤ��� ���ܤ��ɲ�                           //
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
$yyyymm = $_SESSION['2ki_ym'];
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

///// �о�����
$yyyymm   = $_SESSION['2ki_ym'];
$ki       = Ym_to_tnk($_SESSION['2ki_ym']);
$b_yyyymm = $yyyymm - 100;
$p1_ki    = Ym_to_tnk($b_yyyymm);
///// ����ǯ��λ���
$yyyy = substr($yyyymm, 0,4);
$mm   = substr($yyyymm, 4,2);
if (($mm >= 1) && ($mm <= 3)) {
    $yyyy = ($yyyy - 1);
}
$str_ym   = $yyyy . "04";   // ���� ����ǯ��
$b_str_ym = $str_ym - 100;  // ���� ����ǯ��

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
    $menu->set_title("�� {$ki} �����ܷ軻���� �� �� �� �� �� ɽ");
} else {
    $menu->set_title("�� {$ki} ������{$hanki}��Ⱦ������ �� �� �� �� �� ɽ");
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
    $head  = "��¤����";
    $item_in = array();
    $item_in[$i] = $head . $item[$i];
    $query = sprintf("select sum(kin) from profit_loss_keihi_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='%s'", $b_str_ym, $b_yyyymm, $item_in[$i]);
    if (getUniResult($query, $res_in[$i][1]) < 1) {
        $res_in[$i][1] = 0;                 // ��������
    }
    $query = sprintf("select sum(kin) from profit_loss_keihi_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='%s'", $str_ym, $yyyymm, $item_in[$i]);
    if (getUniResult($query, $res_in[$i][2]) < 1) {
        $res_in[$i][2] = 0;                 // ��������
    }
    $res_in[$i][3] = $res_in[$i][2] - $res_in[$i][1];
    
    $head  = "�δ���";
    $item_in = array();
    $item_in[$i] = $head . $item[$i];
    $query = sprintf("select sum(kin) from profit_loss_keihi_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='%s'", $b_str_ym, $b_yyyymm, $item_in[$i]);
    if (getUniResult($query, $res_in[$i][4]) < 1) {
        $res_in[$i][4] = 0;                 // ��������
    }
    $query = sprintf("select sum(kin) from profit_loss_keihi_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='%s'", $str_ym, $yyyymm, $item_in[$i]);
    if (getUniResult($query, $res_in[$i][5]) < 1) {
        $res_in[$i][5] = 0;                 // ��������
    }
    $res_in[$i][6] = $res_in[$i][5] - $res_in[$i][4];
    
    $head  = "���";
    $item_in = array();
    $item_in[$i] = $head . $item[$i];
    $query = sprintf("select sum(kin) from profit_loss_keihi_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='%s'", $b_str_ym, $b_yyyymm, $item_in[$i]);
    if (getUniResult($query, $res_in[$i][7]) < 1) {
        $res_in[$i][7] = 0;                 // ��������
    }
    $query = sprintf("select sum(kin) from profit_loss_keihi_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='%s'", $str_ym, $yyyymm, $item_in[$i]);
    if (getUniResult($query, $res_in[$i][8]) < 1) {
        $res_in[$i][8] = 0;                 // ��������
    }
    $res_in[$i][9] = $res_in[$i][8] - $res_in[$i][7];
    
    $view_data[$i][1] = number_format(($res_in[$i][1] / $tani), $keta);
    $view_data[$i][2] = number_format(($res_in[$i][2] / $tani), $keta);
    $view_data[$i][3] = number_format(($res_in[$i][3] / $tani), $keta);
    $view_data[$i][4] = number_format(($res_in[$i][4] / $tani), $keta);
    $view_data[$i][5] = number_format(($res_in[$i][5] / $tani), $keta);
    $view_data[$i][6] = number_format(($res_in[$i][6] / $tani), $keta);
    $view_data[$i][7] = number_format(($res_in[$i][7] / $tani), $keta);
    $view_data[$i][8] = number_format(($res_in[$i][8] / $tani), $keta);
    $view_data[$i][9] = number_format(($res_in[$i][9] / $tani), $keta);
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
.pt9 {
    font:normal 9pt;
    font-family: monospace;
}
.pt10 {
    font:normal 10pt;
    font-family: monospace;
}
.pt8b {
    font:bold 8pt;
    font-family: monospace;
}
.pt9b {
    font:bold 9pt;
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
        <table width='100%' bgcolor='#d6d3ce' align='center' cellspacing="0" cellpadding="3" border='1'>
            <TBODY>
                <TR>
                    <TD rowspan="2" align="center" width='10' class='pt10b' bgcolor='#ceffce'>��ʬ</TD>
                    <TD rowspan="2" align="center" nowrap class='pt10b' bgcolor='#ceffce'>�������</TD>
                    <TD colspan="3" align="center" height="20" class='pt10b' bgcolor='#ceffce'>�� ¤ ��</TD>
                    <TD colspan="3" align="center" height="20" class='pt10b' bgcolor='#ceffce'>�� �� ��</TD>
                    <TD colspan="3" align="center" height="20" class='pt10b' bgcolor='#ceffce'>�硡����</TD>
                </TR>
                <TR>
                    <TD align="center" nowrap height="20" class='pt10b' bgcolor='#ceffce'>��<?php echo $p1_ki ?>��</TD>
                    <TD align="center" nowrap height="20" class='pt10b' bgcolor='#ceffce'>��<?php echo $ki ?>��</TD>
                    <TD align="center" nowrap height="20" class='pt9b' bgcolor='#ceffce'>����������</TD>
                    <TD align="center" nowrap height="20" class='pt10b' bgcolor='#ceffce'>��<?php echo $p1_ki ?>��</TD>
                    <TD align="center" nowrap height="20" class='pt10b' bgcolor='#ceffce'>��<?php echo $ki ?>��</TD>
                    <TD align="center" nowrap height="20" class='pt9b' bgcolor='#ceffce'>����������</TD>
                    <TD align="center" nowrap height="20" class='pt10b' bgcolor='#ceffce'>��<?php echo $p1_ki ?>��</TD>
                    <TD align="center" nowrap height="20" class='pt10b' bgcolor='#ceffce'>��<?php echo $ki ?>��</TD>
                    <TD align="center" nowrap height="20" class='pt9b' bgcolor='#ceffce'>����������</TD>
                </TR>
                <TR>
                    <TD rowspan="9" align="center" width='10' class='pt10b' bgcolor='#ffffc6' style='border-right-style:none;'>�ͷ���</TD>
                    <TD nowrap class='pt10' bgcolor='#ceffce'>�����</TD>
                    <?php
                        $r = 0;     // �����쥳����
                        for ($c=1;$c<10;$c++) {
                            if ($c == 3 || $c == 6 || $c == 9) {        // ����
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ffffc6'>%s</td>\n",$view_data[$r][$c]);
                            } else {
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ffffff'>%s</td>\n",$view_data[$r][$c]);
                            }
                        }
                    ?>
                </TR>
                <TR>
                    <TD nowrap class='pt10' bgcolor='#ceffce'>��������</TD>
                    <?php
                        $r = 1;     // �����쥳����
                        for ($c=1;$c<10;$c++) {
                            if ($c == 3 || $c == 6 || $c == 9) {        // ����
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ffffc6'>%s</td>\n",$view_data[$r][$c]);
                            } else {
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ffffff'>%s</td>\n",$view_data[$r][$c]);
                            }
                        }
                    ?>
                </TR>
                <TR>
                    <TD nowrap class='pt10' bgcolor='#ceffce'>��Ϳ����</TD>
                    <?php
                        $r = 2;     // �����쥳����
                        for ($c=1;$c<10;$c++) {
                            if ($c == 3 || $c == 6 || $c == 9) {        // ����
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ffffc6'>%s</td>\n",$view_data[$r][$c]);
                            } else {
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ffffff'>%s</td>\n",$view_data[$r][$c]);
                            }
                        }
                    ?>
                </TR>
                <TR>
                    <TD nowrap class='pt10' bgcolor='#ceffce'>������</TD>
                    <?php
                        $r = 3;     // �����쥳����
                        for ($c=1;$c<10;$c++) {
                            if ($c == 3 || $c == 6 || $c == 9) {        // ����
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ffffc6'>%s</td>\n",$view_data[$r][$c]);
                            } else {
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ffffff'>%s</td>\n",$view_data[$r][$c]);
                            }
                        }
                    ?>
                </TR>
                <TR>
                    <TD nowrap class='pt10' bgcolor='#ceffce'>ˡ��ʡ����</TD>
                    <?php
                        $r = 4;     // �����쥳����
                        for ($c=1;$c<10;$c++) {
                            if ($c == 3 || $c == 6 || $c == 9) {        // ����
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ffffc6'>%s</td>\n",$view_data[$r][$c]);
                            } else {
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ffffff'>%s</td>\n",$view_data[$r][$c]);
                            }
                        }
                    ?>
                </TR>
                <TR>
                    <TD nowrap class='pt10' bgcolor='#ceffce'>����ʡ����</TD>
                    <?php
                        $r = 5;     // �����쥳����
                        for ($c=1;$c<10;$c++) {
                            if ($c == 3 || $c == 6 || $c == 9) {        // ����
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ffffc6'>%s</td>\n",$view_data[$r][$c]);
                            } else {
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ffffff'>%s</td>\n",$view_data[$r][$c]);
                            }
                        }
                    ?>
                </TR>
                <TR>
                    <TD nowrap class='pt10' bgcolor='#ceffce'>��Ϳ�����ⷫ��</TD>
                    <?php
                        $r = 6;     // �����쥳����
                        for ($c=1;$c<10;$c++) {
                            if ($c == 3 || $c == 6 || $c == 9) {        // ����
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ffffc6'>%s</td>\n",$view_data[$r][$c]);
                            } else {
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ffffff'>%s</td>\n",$view_data[$r][$c]);
                            }
                        }
                    ?>
                </TR>
                <TR>
                    <TD nowrap class='pt10' bgcolor='#ceffce'>�࿦��������</TD>
                    <?php
                        $r = 7;     // �����쥳����
                        for ($c=1;$c<10;$c++) {
                            if ($c == 3 || $c == 6 || $c == 9) {        // ����
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ffffc6'>%s</td>\n",$view_data[$r][$c]);
                            } else {
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ffffff'>%s</td>\n",$view_data[$r][$c]);
                            }
                        }
                    ?>
                </TR>
                <TR bgcolor='#ffffc6'>
                    <TD nowrap class='pt10b' align='right' style='border-left-style:none;'>�ͷ����</TD>
                    <?php
                        $r = 8;     // �����쥳����
                        for ($c=1;$c<10;$c++) {
                            printf("<td nowrap class='pt10b' align='right' bgcolor='#ffffc6'>%s</td>\n",$view_data[$r][$c]);
                        }
                    ?>
                </TR>
                <tr>
                    <TD rowspan="30" align="center" width='10' class='pt10b' bgcolor='#ffffc6' style='border-right-style:none;'>����</TD>
                    <TD nowrap class='pt10' bgcolor='#ceffce'>ι�������</TD>
                    <?php
                        $r = 9;     // �����쥳����
                        for ($c=1;$c<10;$c++) {
                            if ($c == 3 || $c == 6 || $c == 9) {        // ����
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ffffc6'>%s</td>\n",$view_data[$r][$c]);
                            } else {
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ffffff'>%s</td>\n",$view_data[$r][$c]);
                            }
                        }
                    ?>
                </tr>
                <tr>
                    <TD nowrap class='pt10' bgcolor='#ceffce'>������ĥ</TD>
                    <?php
                        $r = 10;     // �����쥳����
                        for ($c=1;$c<10;$c++) {
                            if ($c == 3 || $c == 6 || $c == 9) {        // ����
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ffffc6'>%s</td>\n",$view_data[$r][$c]);
                            } else {
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ffffff'>%s</td>\n",$view_data[$r][$c]);
                            }
                        }
                    ?>
                </tr>
                <tr>
                    <TD nowrap class='pt10' bgcolor='#ceffce'>�̡�������</TD>
                    <?php
                        $r = 11;     // �����쥳����
                        for ($c=1;$c<10;$c++) {
                            if ($c == 3 || $c == 6 || $c == 9) {        // ����
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ffffc6'>%s</td>\n",$view_data[$r][$c]);
                            } else {
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ffffff'>%s</td>\n",$view_data[$r][$c]);
                            }
                        }
                    ?>
                </tr>
                <tr>
                    <TD nowrap class='pt10' bgcolor='#ceffce'>�񡡵ġ���</TD>
                    <?php
                        $r = 12;     // �����쥳����
                        for ($c=1;$c<10;$c++) {
                            if ($c == 3 || $c == 6 || $c == 9) {        // ����
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ffffc6'>%s</td>\n",$view_data[$r][$c]);
                            } else {
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ffffff'>%s</td>\n",$view_data[$r][$c]);
                            }
                        }
                    ?>
                </tr>
                <tr>
                    <TD nowrap class='pt10' bgcolor='#ceffce'>���������</TD>
                    <?php
                        $r = 13;     // �����쥳����
                        for ($c=1;$c<10;$c++) {
                            if ($c == 3 || $c == 6 || $c == 9) {        // ����
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ffffc6'>%s</td>\n",$view_data[$r][$c]);
                            } else {
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ffffff'>%s</td>\n",$view_data[$r][$c]);
                            }
                        }
                    ?>
                </tr>
                <tr>
                    <TD nowrap class='pt10' bgcolor='#ceffce'>����������</TD>
                    <?php
                        $r = 14;     // �����쥳����
                        for ($c=1;$c<10;$c++) {
                            if ($c == 3 || $c == 6 || $c == 9) {        // ����
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ffffc6'>%s</td>\n",$view_data[$r][$c]);
                            } else {
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ffffff'>%s</td>\n",$view_data[$r][$c]);
                            }
                        }
                    ?>
                </tr>
                <tr>
                    <TD nowrap class='pt10' bgcolor='#ceffce'>�ᡡ�͡���</TD>
                    <?php
                        $r = 15;     // �����쥳����
                        for ($c=1;$c<10;$c++) {
                            if ($c == 3 || $c == 6 || $c == 9) {        // ����
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ffffc6'>%s</td>\n",$view_data[$r][$c]);
                            } else {
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ffffff'>%s</td>\n",$view_data[$r][$c]);
                            }
                        }
                    ?>
                </tr>
                <tr>
                    <TD nowrap class='pt10' bgcolor='#ceffce'>���²�¤��</TD>
                    <?php
                        $r = 16;     // �����쥳����
                        for ($c=1;$c<10;$c++) {
                            if ($c == 3 || $c == 6 || $c == 9) {        // ����
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ffffc6'>%s</td>\n",$view_data[$r][$c]);
                            } else {
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ffffff'>%s</td>\n",$view_data[$r][$c]);
                            }
                        }
                    ?>
                </tr>
                <tr>
                    <TD nowrap class='pt10' bgcolor='#ceffce'>�޽񶵰���</TD>
                    <?php
                        $r = 17;     // �����쥳����
                        for ($c=1;$c<10;$c++) {
                            if ($c == 3 || $c == 6 || $c == 9) {        // ����
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ffffc6'>%s</td>\n",$view_data[$r][$c]);
                            } else {
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ffffff'>%s</td>\n",$view_data[$r][$c]);
                            }
                        }
                    ?>
                </tr>
                <tr>
                    <TD nowrap class='pt10' bgcolor='#ceffce'>��̳������</TD>
                    <?php
                        $r = 18;     // �����쥳����
                        for ($c=1;$c<10;$c++) {
                            if ($c == 3 || $c == 6 || $c == 9) {        // ����
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ffffc6'>%s</td>\n",$view_data[$r][$c]);
                            } else {
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ffffff'>%s</td>\n",$view_data[$r][$c]);
                            }
                        }
                    ?>
                </tr>
                <tr>
                    <TD nowrap class='pt10' bgcolor='#ceffce'>�����ȡ���</TD>
                    <?php
                        $r = 19;     // �����쥳����
                        for ($c=1;$c<10;$c++) {
                            if ($c == 3 || $c == 6 || $c == 9) {        // ����
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ffffc6'>%s</td>\n",$view_data[$r][$c]);
                            } else {
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ffffff'>%s</td>\n",$view_data[$r][$c]);
                            }
                        }
                    ?>
                </tr>
                <tr>
                    <TD nowrap class='pt10' bgcolor='#ceffce'>���Ǹ���</TD>
                    <?php
                        $r = 20;     // �����쥳����
                        for ($c=1;$c<10;$c++) {
                            if ($c == 3 || $c == 6 || $c == 9) {        // ����
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ffffc6'>%s</td>\n",$view_data[$r][$c]);
                            } else {
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ffffff'>%s</td>\n",$view_data[$r][$c]);
                            }
                        }
                    ?>
                </tr>
                <tr>
                    <TD nowrap class='pt10' bgcolor='#ceffce'>�������</TD>
                    <?php
                        $r = 21;     // �����쥳����
                        for ($c=1;$c<10;$c++) {
                            if ($c == 3 || $c == 6 || $c == 9) {        // ����
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ffffc6'>%s</td>\n",$view_data[$r][$c]);
                            } else {
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ffffff'>%s</td>\n",$view_data[$r][$c]);
                            }
                        }
                    ?>
                </tr>
                <tr>
                    <TD nowrap class='pt10' bgcolor='#ceffce'>����������</TD>
                    <?php
                        $r = 22;     // �����쥳����
                        for ($c=1;$c<10;$c++) {
                            if ($c == 3 || $c == 6 || $c == 9) {        // ����
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ffffc6'>%s</td>\n",$view_data[$r][$c]);
                            } else {
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ffffff'>%s</td>\n",$view_data[$r][$c]);
                            }
                        }
                    ?>
                </tr>
                <tr>
                    <TD nowrap class='pt10' bgcolor='#ceffce'>����������</TD>
                    <?php
                        $r = 23;     // �����쥳����
                        for ($c=1;$c<10;$c++) {
                            if ($c == 3 || $c == 6 || $c == 9) {        // ����
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ffffc6'>%s</td>\n",$view_data[$r][$c]);
                            } else {
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ffffff'>%s</td>\n",$view_data[$r][$c]);
                            }
                        }
                    ?>
                </tr>
                <tr>
                    <TD nowrap class='pt10' bgcolor='#ceffce'>�ݾڽ�����</TD>
                    <?php
                        $r = 24;     // �����쥳����
                        for ($c=1;$c<10;$c++) {
                            if ($c == 3 || $c == 6 || $c == 9) {        // ����
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ffffc6'>%s</td>\n",$view_data[$r][$c]);
                            } else {
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ffffff'>%s</td>\n",$view_data[$r][$c]);
                            }
                        }
                    ?>
                </tr>
                <tr>
                    <TD nowrap class='pt10' bgcolor='#ceffce'>��̳�Ѿ�������</TD>
                    <?php
                        $r = 25;     // �����쥳����
                        for ($c=1;$c<10;$c++) {
                            if ($c == 3 || $c == 6 || $c == 9) {        // ����
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ffffc6'>%s</td>\n",$view_data[$r][$c]);
                            } else {
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ffffff'>%s</td>\n",$view_data[$r][$c]);
                            }
                        }
                    ?>
                </tr>
                <tr>
                    <TD nowrap class='pt10' bgcolor='#ceffce'>�����������</TD>
                    <?php
                        $r = 26;     // �����쥳����
                        for ($c=1;$c<10;$c++) {
                            if ($c == 3 || $c == 6 || $c == 9) {        // ����
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ffffc6'>%s</td>\n",$view_data[$r][$c]);
                            } else {
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ffffff'>%s</td>\n",$view_data[$r][$c]);
                            }
                        }
                    ?>
                </tr>
                <tr>
                    <TD nowrap class='pt10' bgcolor='#ceffce'>�֡�ξ����</TD>
                    <?php
                        $r = 27;     // �����쥳����
                        for ($c=1;$c<10;$c++) {
                            if ($c == 3 || $c == 6 || $c == 9) {        // ����
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ffffc6'>%s</td>\n",$view_data[$r][$c]);
                            } else {
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ffffff'>%s</td>\n",$view_data[$r][$c]);
                            }
                        }
                    ?>
                </tr>
                <tr>
                    <TD nowrap class='pt10' bgcolor='#ceffce'>�ݡ�������</TD>
                    <?php
                        $r = 28;     // �����쥳����
                        for ($c=1;$c<10;$c++) {
                            if ($c == 3 || $c == 6 || $c == 9) {        // ����
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ffffc6'>%s</td>\n",$view_data[$r][$c]);
                            } else {
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ffffff'>%s</td>\n",$view_data[$r][$c]);
                            }
                        }
                    ?>
                </tr>
                <tr>
                    <TD nowrap class='pt10' bgcolor='#ceffce'>��ƻ��Ǯ��</TD>
                    <?php
                        $r = 29;     // �����쥳����
                        for ($c=1;$c<10;$c++) {
                            if ($c == 3 || $c == 6 || $c == 9) {        // ����
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ffffc6'>%s</td>\n",$view_data[$r][$c]);
                            } else {
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ffffff'>%s</td>\n",$view_data[$r][$c]);
                            }
                        }
                    ?>
                </tr>
                <tr>
                    <TD nowrap class='pt10' bgcolor='#ceffce'>��������</TD>
                    <?php
                        $r = 30;     // �����쥳����
                        for ($c=1;$c<10;$c++) {
                            if ($c == 3 || $c == 6 || $c == 9) {        // ����
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ffffc6'>%s</td>\n",$view_data[$r][$c]);
                            } else {
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ffffff'>%s</td>\n",$view_data[$r][$c]);
                            }
                        }
                    ?>
                </tr>
                <tr>
                    <TD nowrap class='pt10' bgcolor='#ceffce'>��ʧ�����</TD>
                    <?php
                        $r = 31;     // �����쥳����
                        for ($c=1;$c<10;$c++) {
                            if ($c == 3 || $c == 6 || $c == 9) {        // ����
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ffffc6'>%s</td>\n",$view_data[$r][$c]);
                            } else {
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ffffff'>%s</td>\n",$view_data[$r][$c]);
                            }
                        }
                    ?>
                </tr>
                <tr>
                    <TD nowrap class='pt10' bgcolor='#ceffce'>�������</TD>
                    <?php
                        $r = 32;     // �����쥳����
                        for ($c=1;$c<10;$c++) {
                            if ($c == 3 || $c == 6 || $c == 9) {        // ����
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ffffc6'>%s</td>\n",$view_data[$r][$c]);
                            } else {
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ffffff'>%s</td>\n",$view_data[$r][$c]);
                            }
                        }
                    ?>
                </tr>
                <tr>
                    <TD nowrap class='pt10' bgcolor='#ceffce'>���ա���</TD>
                    <?php
                        $r = 33;     // �����쥳����
                        for ($c=1;$c<10;$c++) {
                            if ($c == 3 || $c == 6 || $c == 9) {        // ����
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ffffc6'>%s</td>\n",$view_data[$r][$c]);
                            } else {
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ffffff'>%s</td>\n",$view_data[$r][$c]);
                            }
                        }
                    ?>
                </tr>
                <tr>
                    <TD nowrap class='pt10' bgcolor='#ceffce'>�ҡ��ߡ���</TD>
                    <?php
                        $r = 34;     // �����쥳����
                        for ($c=1;$c<10;$c++) {
                            if ($c == 3 || $c == 6 || $c == 9) {        // ����
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ffffc6'>%s</td>\n",$view_data[$r][$c]);
                            } else {
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ffffff'>%s</td>\n",$view_data[$r][$c]);
                            }
                        }
                    ?>
                </tr>
                <tr>
                    <TD nowrap class='pt10' bgcolor='#ceffce'>�¡��ڡ���</TD>
                    <?php
                        $r = 35;     // �����쥳����
                        for ($c=1;$c<10;$c++) {
                            if ($c == 3 || $c == 6 || $c == 9) {        // ����
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ffffc6'>%s</td>\n",$view_data[$r][$c]);
                            } else {
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ffffff'>%s</td>\n",$view_data[$r][$c]);
                            }
                        }
                    ?>
                </tr>
                <tr>
                    <TD nowrap class='pt10' bgcolor='#ceffce'>����������</TD>
                    <?php
                        $r = 36;     // �����쥳����
                        for ($c=1;$c<10;$c++) {
                            if ($c == 3 || $c == 6 || $c == 9) {        // ����
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ffffc6'>%s</td>\n",$view_data[$r][$c]);
                            } else {
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ffffff'>%s</td>\n",$view_data[$r][$c]);
                            }
                        }
                    ?>
                </tr>
                <tr>
                    <TD nowrap class='pt10' bgcolor='#ceffce'>���졼���б���</TD>
                    <?php
                        $r = 37;     // �����쥳����
                        for ($c=1;$c<10;$c++) {
                            if ($c == 3 || $c == 6 || $c == 9) {        // ����
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ffffc6'>%s</td>\n",$view_data[$r][$c]);
                            } else {
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ffffff'>%s</td>\n",$view_data[$r][$c]);
                            }
                        }
                    ?>
                </tr>
                <tr bgcolor='#ffffc6'>
                    <TD nowrap class='pt10b' align='right' style='border-left-style:none;'>�����</TD>
                    <?php
                        $r = 38;     // �����쥳����
                        for ($c=1;$c<10;$c++) {
                            printf("<td nowrap class='pt10b' align='right' bgcolor='#ffffc6'>%s</td>\n",$view_data[$r][$c]);
                        }
                    ?>
                </tr>
                <tr bgcolor='#ffffc6'>
                    <TD colspan='2' nowrap class='pt10b' align='right'>�硡��</TD>
                    <?php
                        $r = 39;     // �����쥳����
                        for ($c=1;$c<10;$c++) {
                            printf("<td nowrap class='pt10b' align='right' bgcolor='#ffffc6'>%s</td>\n",$view_data[$r][$c]);
                        }
                    ?>
                </tr>
            </TBODY>
        </table>
    </center>
</body>
</html>
