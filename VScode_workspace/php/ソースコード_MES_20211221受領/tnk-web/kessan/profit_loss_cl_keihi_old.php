<?php
//////////////////////////////////////////////////////////////////////////////
// �»�״ط� � �ã̷������ɽ                                         //
// Copyright(C) 2003-2009 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp       //
// Changed history                                                          //
// 2003/01/29 Created   profit_loss_cl_keihi.php                            //
// 2003/01/30 ���٥ե�����ɤΥǡ����׻�����λ���Ƥ���ñ��Ĵ�����ѹ�        //
// 2003/02/12 ����������̥ץ������ѹ�������ơ��֥뤫��ǡ�������      //
// 2003/02/21 font �� monospace (���ֳ�font) ���ѹ�                         //
// 2003/02/23 date("Y/m/d H:m:s") �� H:i:s �Υߥ�����                       //
// 2003/03/06 title_font today_font ������ �����ʲ��η��������ɲ�         //
// 2003/03/10 ���� ����(������) ����(��¤����) ���ɲ�                     //
// 2003/03/11 Location: http �� Location $url_referer ���ѹ�                //
//            ��å���������Ϥ��뤿�� site_index site_id �򥳥��Ȥˤ�    //
//                                            parent.menu_site.��ͭ�����ѹ� //
// 2003/05/01 ����Ĺ����λؼ���ǧ�ڤ�Account_group�����̾���ѹ�           //
// 2004/05/06 ����ɸ����Ǥ��б��Τ���������β����ɲ�(7520)B36 $r=35       //
//            ���̸ߴ����Τ��������7520�������select��7520�Τߤ�select��  //
// 2004/05/11 ��¦�Υ����ȥ�˥塼�Υ��󡦥��� �ܥ�����ɲ�                 //
// 2005/10/27 MenuHeader class ����Ѥ��ƶ��̥�˥塼���ڤ�ǧ���������ѹ�   //
// 2009/08/20 ���ʴ������ɲä�ȼ����ץ�����_old�Ȥ����̥�˥塼�� ��ë //
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
//////////// �ͷ��񡦷���Υ쥳���ɿ� �ե�����ɿ�
$rec_jin =  8;    // �ͷ���λ��Ѳ��ܿ�
$rec_kei = 28;    // ����λ��Ѳ��ܿ�       ����ɸ������б��Τ��� 27��28
$f_mei   = 13;    // ����(ɽ)�Υե�����ɿ�

//////////// ������ܤ���������
// �ͷ���� Start End ����
$str_jin = 8101;
$end_jin = 8123;
/******
    8101 = �����
    8102 = ��������
    8103 = ��Ϳ����
    8104 = ������
    8105 = ˡ��ʡ����
    8106 = ����ʡ����
    8121 = ��Ϳ�����ⷫ��
    8123 = �࿦��������  ��̾���࿦��Ϳ�����ⷫ��
******/
$jin_act = array(8101,8102,8103,8104,8105,8106,8121,8123);

// ����� Start End ����
$str_kei = 7501;
$end_kei = 8000;
/******
    7501 = ι�������
    7502 = ������ĥ
    7503 = �̿���
    7504 = �����
    7505 = ���������
    7506 = ����������
    7508 = �����
    7509 = ���²�¤��
    7510 = �޽񶵰���
    7512 = ��̳������
    7520 = ������       // ����ɸ����Ǥˤ���ɲ�
    7521 = ���Ǹ���
    7522 = �������
    7523 = ����
    7524 = ������
    7525 = �ݾڽ�����
    7526 = ��̳�Ѿ�������
    7527 = �����������
    7528 = ��ξ��
    7530 = �ݸ���
    7531 = ��ƻ��Ǯ��
    7532 = ������
    7533 = ��ʧ�����
    7536 = �������
    7537 = ���ն�
    7538 = ������
    7540 = �¼���
    8000 = ����������
******/
$kei_act = array(7501,7502,7503,7504,7505,7506,7508,7509,7510,7512,7521,7522,7523,7524,7525,7526,7527,7528,7530,7531,7532,7533,7536,7537,7538,7540,8000);
////// ���Τ�����
$actcod  = array(8101,8102,8103,8104,8105,8106,8121,8123,7501,7502,7503,7504,7505,7506,7508,7509,7510,7512,7521,7522,7523,7524,7525,7526,7527,7528,7530,7531,7532,7533,7536,7537,7538,7540,8000);

/***** ��    ��    �� *****/
$res = array();                     ///// ���η�����Ǻ��줿�ǡ��������
$query = sprintf("select ����, ���ץ�, ��˥� from wrk_uriage where ǯ��=%d", $yyyymm);
if ((getResult($query,$res)) > 0) {     ///// �ǡ���̵���Υ����å� ͥ���̤γ�̤����
    $uri   = $res[0]['����'];
    $uri_c = $res[0]['���ץ�'];
    $uri_l = $res[0]['��˥�'];
        ///// Ĵ���ǡ����μ���
    $query = sprintf("select sum(kin) from act_adjust_history where pl_bs_ym=%d and note like '%%����Ĵ��'", $yyyymm); // ����
    getUniResult($query, $adjust_all);
    $query = sprintf("select sum(kin) from act_adjust_history where pl_bs_ym=%d and note='���ץ�����Ĵ��'", $yyyymm); // ���ץ�
    getUniResult($query, $adjust_c);
    $query = sprintf("select sum(kin) from act_adjust_history where pl_bs_ym=%d and note='��˥�����Ĵ��'", $yyyymm); // ��˥�
    getUniResult($query, $adjust_l);
        ///// Ĵ�����å� END
    $uri   = ($uri + ($adjust_all));    // �ޥ��ʥ����θ����()����Ѥ���
    $uri_c = ($uri_c + ($adjust_c));
    $uri_l = ($uri_l + ($adjust_l));
    $view_uriage   = number_format(($uri / $tani), $keta);
    $view_uriage_c = number_format(($uri_c / $tani), $keta);
    $view_uriage_l = number_format(($uri_l / $tani), $keta);
        ///// ����� ����
    $uri_ritu_c = (Uround(($uri_c / $uri), 3)) * 100;
    $uri_ritu_l = (100 - $uri_ritu_c);
    $view_ritu_c = number_format($uri_ritu_c, 1) . '%';
    $view_ritu_l = number_format($uri_ritu_l, 1) . '%';
    $view_ritu   = number_format(($uri_ritu_c + $uri_ritu_l), 1) . '%';
} else {
    $view_uriage   = "̤��Ͽ";
    $view_uriage_c = "̤��Ͽ";
    $view_uriage_l = "̤��Ͽ";
    $view_ritu_c   = "̤��Ͽ";
    $view_ritu_l   = "̤��Ͽ";
    $view_ritu     = "̤��Ͽ";
}

/********** ������(������) **********/
$res = array();
$query = sprintf("select kin, allo from act_pl_history where pl_bs_ym=%d and note='���λ�����'", $yyyymm);
if (getResult($query, $res) > 0) {
    $shiire      = $res[0]['kin'];
    $shiire_ritu = (Uround($res[0]['allo'], 3) * 100);
    $view_shiire = number_format(($shiire / $tani), $keta);
    $view_shiire_ritu = number_format($shiire_ritu, 1) . '%';
} else {
    $view_shiire = "̤�׻�";
    $view_shiire_ritu = "̤�׻�";
}
$query = sprintf("select kin, allo from act_pl_history where pl_bs_ym=%d and note='���ץ������'", $yyyymm);
if (getResult($query, $res) > 0) {
    $shiire_c      = $res[0]['kin'];
    $shiire_ritu_c = (Uround($res[0]['allo'], 3) * 100);
    $view_shiire_c = number_format(($shiire_c / $tani), $keta);
    $view_shiire_ritu_c = number_format($shiire_ritu_c, 1) . '%';
} else {
    $view_shiire_c = "̤�׻�";
    $view_shiire_ritu_c = "̤�׻�";
}
$query = sprintf("select kin, allo from act_pl_history where pl_bs_ym=%d and note='��˥�������'", $yyyymm);
if (getResult($query, $res) > 0) {
    $shiire_l      = $res[0]['kin'];
    $shiire_ritu_l = (100 - $shiire_ritu_c);        // ��פ��碌�뤿�� 100 ���� ���ץ��������ͤˤ���
    $view_shiire_l = number_format(($shiire_l / $tani), $keta);
    $view_shiire_ritu_l = number_format($shiire_ritu_l, 1) . '%';
} else {
    $view_shiire_l = "̤�׻�";
    $view_shiire_ritu_l = "̤�׻�";
}

/********** ������(��¤����) **********/
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='���κ�����'", $yyyymm);
if (getUniResult($query, $material) < 1) {
    $view_material   = "̤�׻�";     // ��������
    $view_material_c = "̤�׻�";
    $view_material_l = "̤�׻�";
    $view_barance    = "-----";
} else {
    $view_material = number_format(($material / $tani), $keta);
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='���ץ������'", $yyyymm);
    if (getUniResult($query, $material_c) < 1) {
        $view_material_c = "̤�׻�";     // ��������
        $view_material_l = "̤�׻�";
        $view_barance    = "-----";
    } else {
        $view_material_c = number_format(($material_c / $tani), $keta);
        $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='��˥�������'", $yyyymm);
        if (getUniResult($query, $material_l) < 1) {
            $view_material_l = "̤�׻�";     // ��������
            $view_barance    = "-----";
        } else {
            $view_material_l = number_format(($material_l / $tani), $keta);
                ///// ������ ����
            $mate_ritu_c = (Uround(($material_c / $material), 3)) * 100;
            $mate_ritu_l = (100 - $mate_ritu_c);
            $view_mate_ritu_c = number_format($mate_ritu_c, 1) . '%';
            $view_mate_ritu_l = number_format($mate_ritu_l, 1) . '%';
            $view_mate_ritu   = number_format(($mate_ritu_c + $mate_ritu_l), 1) . '%';
            $balance = ($shiire - $material);
            $view_barance = number_format(($balance / $tani), $keta);
        }
    }
}

////// ����ơ��֥���ǡ���������
$res_jin = array();     /*** ����Υǡ������� ***/
$query = sprintf("select kin00, kin01, kin02, kin03, kin04, kin05, kin06, kin07, kin08, kin09, kin10, kin11, kin12 from act_cl_history where pl_bs_ym=%d and (actcod>=%d and actcod<=%d) order by actcod ASC", $yyyymm, $str_jin, $end_jin);
if (($rows_jin = getResult2($query,$res_jin)) > 0) {     ///// �ǡ���̵���Υ����å� ͥ���̤γ�̤����
    $res_kei = array();                                             // �ߴ����Τ��� actcod=7520 ��ǽ�Ͻ�������
    $query = sprintf("select kin00, kin01, kin02, kin03, kin04, kin05, kin06, kin07, kin08, kin09, kin10, kin11, kin12 from act_cl_history where pl_bs_ym=%d and (actcod>=%d and actcod<=%d) and actcod!=7520 order by actcod ASC", $yyyymm, $str_kei, $end_kei);
    if (($rows_kei = getResult2($query,$res_kei)) > 0) {     ///// �ǡ���̵���Υ����å� ͥ���̤γ�̤����
        ///// �ͷ���ȷ����������
        $data      = array();       // �׻����ѿ� ����ǽ����
        $view_data = array();       // ɽ�����ѿ� ����ǽ����
        ///////// ɽ���ѥǡ��������� (���̤�ɽ�ǡ������᡼��)
        ///// ���٤� ñ��Ĵ��
        $r = 0;
        $c = 0;
        foreach ($res_jin as $row) {    // �ͷ���
            foreach ($row as $col) {
                $data[$r][$c] = $col / $tani;
                $view_data[$r][$c] = number_format($data[$r][$c],$keta);
                $c++;
            }
            $r++;
            $c = 0;
        }
        foreach ($res_kei as $row) {    // ����
            foreach ($row as $col) {
                $data[$r][$c] = $col / $tani;
                $view_data[$r][$c] = number_format($data[$r][$c],$keta);
                $c++;
            }
            $r++;
            $c = 0;
        }
        ///// ����ɸ����Ǥλ����� �ɲ�ʬ
        $res_gai = array();
        $query = sprintf("select kin00, kin01, kin02, kin03, kin04, kin05, kin06, kin07, kin08, kin09, kin10, kin11, kin12 from act_cl_history where pl_bs_ym=%d and actcod=7520", $yyyymm);
        if (($rows_gai = getResult2($query,$res_gai)) > 0) {     // �ǡ���̵���Υ����å� ͥ���̤γ�̤����
            for ($c = 0; $c < $f_mei; $c++) {
                $data[35][$c]      = $res_gai[0][$c] / $tani;
                $view_data[35][$c] = number_format($data[35][$c], $keta);
            }
        } else {
            for ($c = 0; $c < $f_mei; $c++) {   // ������(7520)��̵�����0�ǽ����
                $data[35][$c]      = 0;
                $view_data[35][$c] = 0;
            }
        }
        ///// ����¾(9999)�β��ܤ����뤫�����å�
        $query = sprintf("select (kin00+kin01+kin02+kin03+kin04+kin05+kin06+kin07+kin08+kin09+kin10+kin11+kin12) as other from act_cl_history where pl_bs_ym=%d and actcod=9999", $yyyymm);
        if (getUniResult($query, $res_oth) > 0) {
            if ($res_oth > 0) {
                $_SESSION['s_sysmsg'] = sprintf("����¾�˶�ۤ�����ޤ���<br>��%d��%d�%d", $ki, $tuki, $res_oth);
            }
        }
        
        ///// ���פη׻� �ͷ���
        $jin_sum = array();
        for ($c=0; $c < $f_mei; $c++) {
            $jin_sum[$c] = 0;       // �ʲ��� += ��Ȥ���������
        }
        for ($r=0; $r < $rec_jin; $r++) {
            for ($c=0; $c < $f_mei; $c++) {
                $jin_sum[$c] += $data[$r][$c];
            }
        }
        ///// ���פη׻� ����
        $kei_sum = array();
        for ($c=0; $c < $f_mei; $c++) {
            $kei_sum[$c] = 0;       // �ʲ��� += ��Ȥ���������
        }
        for ($r=0; $r<$rec_kei; $r++) {
            for ($c=0; $c < $f_mei; $c++) {
                $kei_sum[$c] += $data[$r+8][$c];
            }
        }
        ///// ��פη׻�   ///// ���ס���פ�ɽ���ѥǡ�������
        $all_sum = array();
        $view_jin_sum = array();
        $view_kei_sum = array();
        $view_all_sum = array();
        for ($c=0;$c<$f_mei;$c++) {
            $all_sum[$c]  = $jin_sum[$c] + $kei_sum[$c];             // ��פη׻�
            $view_jin_sum[$c] = number_format($jin_sum[$c],$keta);   // ɽ���� �ͷ����
            $view_kei_sum[$c] = number_format($kei_sum[$c],$keta);   // ɽ���� �����
            $view_all_sum[$c] = number_format($all_sum[$c],$keta);   // ɽ���� �硡��
        }
    } else {
        $_SESSION['s_sysmsg'] = sprintf("������оݥǡ���������ޤ���<br>��%d��%d��",$ki,$tuki);
        header("Location: $url_referer");
        exit();
    }
} else {
    $_SESSION['s_sysmsg'] = sprintf("�оݥǡ���������ޤ���<br>��%d��%d��",$ki,$tuki);
    header("Location: $url_referer");
    exit();
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
                    <td width='10' rowspan='3' align='center' class='pt10' bgcolor='#ffffc6'>��ʬ</td>
                    <td rowspan='3' nowrap align='center' class='pt10b' bgcolor='#ffffc6'>�������</td>
                    <td colspan='10' nowrap align='center' class='pt10b' bgcolor='#ffffc6'>������Ρ�����¤���С���</td>
                    <td colspan='3' rowspan='2' nowrap align='center' class='pt10b' bgcolor='#ffffc6'>������ڤӰ��̴�����</td>
                </tr>
                <tr>
                    <td colspan='3' nowrap align='center' class='pt10b' bgcolor='#ffffc6'>�硡������</td>
                    <td colspan='3' nowrap align='center' class='pt10b'>ľ�ܷ���</td>
                    <td colspan='3' nowrap align='center' class='pt10b'>���ܷ���</td>
                    <td rowspan='2' nowrap align='center' class='pt10b' bgcolor='#ffffc6'>�硡��</td>
                </tr>
                <tr>
                    <td nowrap align='center' class='pt10b' bgcolor='#ffffc6'>���ץ�</td>
                    <td nowrap align='center' class='pt10b' bgcolor='#ffffc6'>��˥�</td>
                    <td nowrap align='center' class='pt10b' bgcolor='#ffffc6'>���</td>
                    <td nowrap align='center' class='pt10b'>���ץ�</td>
                    <td nowrap align='center' class='pt10b'>��˥�</td>
                    <td nowrap align='center' class='pt10b'>���</td>
                    <td nowrap align='center' class='pt10b'>���ץ�</td>
                    <td nowrap align='center' class='pt10b'>��˥�</td>
                    <td nowrap align='center' class='pt10b'>���</td>
                    <td nowrap align='center' class='pt10b' bgcolor='#ceffce'>���ץ�</td>
                    <td nowrap align='center' class='pt10b' bgcolor='#ceffce'>��˥�</td>
                    <td nowrap align='center' class='pt10b' bgcolor='#ceffce'>���</td>
                </tr>
                <tr>
                    <td width='10' rowspan='4' align='center' class='pt10b' bgcolor='#ffffc6'>���</td>
                    <td nowrap class='pt10'>���ץ�</td>
                    <td nowrap class='pt10' align='right' bgcolor='#ffffc6'><?php echo $view_uriage_c ?></td>   <!-- ���ץ� -->
                    <td nowrap class='pt10' align='right' bgcolor='#ffffc6'>��</td>       <!-- ��˥� -->
                    <td nowrap class='pt10' align='right' bgcolor='#ffffc6'><?php echo $view_uriage_c ?></td>   <!-- ��� -->
                    <td nowrap class='pt10' align='right'><?php echo $view_uriage_c ?></td>   <!-- ���ץ� -->
                    <td nowrap class='pt10' align='right'>��</td>       <!-- ��˥� -->
                    <td nowrap class='pt10' align='right'><?php echo $view_uriage_c ?></td>   <!-- ��� -->
                    <td nowrap class='pt10' align='right'>��</td>       <!-- ;�� ������ -->
                    <td nowrap class='pt10' align='right'>��</td>       <!-- ;�� ������ -->
                    <td nowrap class='pt10' align='right'>��</td>       <!-- ;�� ������ -->
                    <td nowrap class='pt10' align='right' bgcolor='#ffffc6'><?php echo $view_uriage_c ?></td>   <!-- ��� -->
                    <td nowrap class='pt10' align='right' bgcolor='#ceffce'>��</td>       <!-- ;�� �δ��� -->
                    <td nowrap class='pt10' align='right' bgcolor='#ceffce'>��</td>       <!-- ;�� �δ��� -->
                    <td nowrap class='pt10' align='right' bgcolor='#ceffce'>��</td>       <!-- ;�� �δ��� -->
                </tr>
                <tr>
                    <td nowrap class='pt10'>��˥�</td>
                    <td nowrap class='pt10' align='right' bgcolor='#ffffc6'>��</td>         <!-- ���ץ� -->
                    <td nowrap class='pt10' align='right' bgcolor='#ffffc6'><?php echo $view_uriage_l ?></td>     <!-- ��˥� -->
                    <td nowrap class='pt10' align='right' bgcolor='#ffffc6'><?php echo $view_uriage_l ?></td>     <!-- ��� -->
                    <td nowrap class='pt10' align='right'>��</td>       <!-- ���ץ� -->
                    <td nowrap class='pt10' align='right'><?php echo $view_uriage_l ?></td>   <!-- ��˥� -->
                    <td nowrap class='pt10' align='right'><?php echo $view_uriage_l ?></td>   <!-- ��� -->
                    <td nowrap class='pt10' align='right'>��</td>       <!-- ;�� ������ -->
                    <td nowrap class='pt10' align='right'>��</td>       <!-- ;�� ������ -->
                    <td nowrap class='pt10' align='right'>��</td>       <!-- ;�� ������ -->
                    <td nowrap class='pt10' align='right' bgcolor='#ffffc6'><?php echo $view_uriage_l ?></td>   <!-- ��� -->
                    <td nowrap class='pt10' align='right' bgcolor='#ceffce'>��</td>       <!-- ;�� �δ��� -->
                    <td nowrap class='pt10' align='right' bgcolor='#ceffce'>��</td>       <!-- ;�� �δ��� -->
                    <td nowrap class='pt10' align='right' bgcolor='#ceffce'>��</td>       <!-- ;�� �δ��� -->
                </tr>
                <tr>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'>�����</td>
                    <td nowrap class='pt10' align='right' bgcolor='#ffffc6'>��</td>     <!-- ���ץ� -->
                    <td nowrap class='pt10' align='right' bgcolor='#ffffc6'>��</td>     <!-- ��˥� -->
                    <td nowrap class='pt10' align='right' bgcolor='#ffffc6'>��</td>     <!-- ��� -->
                    <td nowrap class='pt10' align='right' bgcolor='#e6e6e6'><?php echo $view_ritu_c ?></td>   <!-- ���ץ� -->
                    <td nowrap class='pt10' align='right' bgcolor='#e6e6e6'><?php echo $view_ritu_l ?></td>   <!-- ��˥� -->
                    <td nowrap class='pt10' align='right' bgcolor='#e6e6e6'><?php echo $view_ritu ?>  </td>   <!-- ��� -->
                    <td nowrap class='pt10' align='right'>��</td>       <!-- ;�� ������ -->
                    <td nowrap class='pt10' align='right'>��</td>       <!-- ;�� ������ -->
                    <td nowrap class='pt10' align='right'>��</td>       <!-- ;�� ������ -->
                    <td nowrap class='pt10' align='right' bgcolor='#ffffc6'>��</td>   <!-- ��� -->
                    <td nowrap class='pt10' align='right' bgcolor='#ceffce'>��</td>       <!-- ;�� �δ��� -->
                    <td nowrap class='pt10' align='right' bgcolor='#ceffce'>��</td>       <!-- ;�� �δ��� -->
                    <td nowrap class='pt10' align='right' bgcolor='#ceffce'>��</td>       <!-- ;�� �δ��� -->
                </tr>
                <tr>
                    <td nowrap align='right' class='pt10b' bgcolor='#ffffc6'>����</td>
                    <td nowrap class='pt10' align='right' bgcolor='#ffffc6'><?php echo $view_uriage_c ?></td>     <!-- ���ץ� -->
                    <td nowrap class='pt10' align='right' bgcolor='#ffffc6'><?php echo $view_uriage_l ?></td>     <!-- ��˥� -->
                    <td nowrap class='pt10' align='right' bgcolor='#ffffc6'><?php echo $view_uriage ?></td>     <!-- ��� -->
                    <td nowrap class='pt10' align='right' bgcolor='#ffffc6'><?php echo $view_uriage_c ?></td>   <!-- ���ץ� -->
                    <td nowrap class='pt10' align='right' bgcolor='#ffffc6'><?php echo $view_uriage_l ?></td>   <!-- ��˥� -->
                    <td nowrap class='pt10' align='right' bgcolor='#ffffc6'><?php echo $view_uriage ?></td>   <!-- ��� -->
                    <td nowrap class='pt10' align='right' bgcolor='#ffffc6'>��</td>       <!-- ;�� ������ -->
                    <td nowrap class='pt10' align='right' bgcolor='#ffffc6'>��</td>       <!-- ;�� ������ -->
                    <td nowrap class='pt10' align='right' bgcolor='#ffffc6'>��</td>       <!-- ;�� ������ -->
                    <td nowrap class='pt10' align='right' bgcolor='#ffffc6'><?php echo $view_uriage ?></td>   <!-- ��� -->
                    <td nowrap class='pt10' align='right' bgcolor='#ffffc6'>��</td>       <!-- ;�� �δ��� -->
                    <td nowrap class='pt10' align='right' bgcolor='#ffffc6'>��</td>       <!-- ;�� �δ��� -->
                    <td nowrap class='pt10' align='right' bgcolor='#ffffc6'>��</td>       <!-- ;�� �δ��� -->
                </tr>
                <tr>
                    <td width='10' rowspan='4' align='center' class='pt10b' bgcolor='#ffffc6'>����</td>
                    <td nowrap class='pt10'>��������</td>
                    <td nowrap class='pt10' align='right' bgcolor='#ffffc6'>��</td>   <!-- ���ץ� -->
                    <td nowrap class='pt10' align='right' bgcolor='#ffffc6'>��</td>   <!-- ��˥� -->
                    <td nowrap class='pt10' align='right' bgcolor='#ffffc6'>��</td>   <!-- ��� -->
                    <td nowrap class='pt10' align='right'><?php echo $view_shiire_c ?></td>   <!-- ���ץ� -->
                    <td nowrap class='pt10' align='right'><?php echo $view_shiire_l ?></td>   <!-- ��˥� -->
                    <td nowrap class='pt10' align='right'><?php echo $view_shiire ?></td>   <!-- ��� -->
                    <td nowrap class='pt10' align='right'>��</td>       <!-- ;�� ������ -->
                    <td nowrap class='pt10' align='right'>��</td>       <!-- ;�� ������ -->
                    <td nowrap class='pt10' align='right'>��</td>       <!-- ;�� ������ -->
                    <td nowrap class='pt10' align='right' bgcolor='#ffffc6'><?php echo $view_shiire ?></td>   <!-- ��� -->
                    <td nowrap class='pt10' align='right' bgcolor='#ceffce'>��</td>       <!-- ;�� �δ��� -->
                    <td nowrap class='pt10' align='right' bgcolor='#ceffce'>��</td>       <!-- ;�� �δ��� -->
                    <td nowrap class='pt10' align='right' bgcolor='#ceffce'>��</td>       <!-- ;�� �δ��� -->
                </tr>
                <tr>
                    <td nowrap class='pt10'>��¤��������</td>
                    <td nowrap class='pt10' align='right' bgcolor='#ffffc6'><?php echo $view_material_c ?></td>   <!-- ���ץ� -->
                    <td nowrap class='pt10' align='right' bgcolor='#ffffc6'><?php echo $view_material_l ?></td>   <!-- ��˥� -->
                    <td nowrap class='pt10' align='right' bgcolor='#ffffc6'><?php echo $view_material ?></td>   <!-- ��� -->
                    <td nowrap class='pt10' align='right'>��</td>   <!-- ���ץ� -->
                    <td nowrap class='pt10' align='right'>��</td>   <!-- ��˥� -->
                    <td nowrap class='pt10' align='right'>��</td>   <!-- ��� -->
                    <td nowrap class='pt10' align='right'>��</td>       <!-- ;�� ������ -->
                    <td nowrap class='pt10' align='right'>��</td>       <!-- ;�� ������ -->
                    <td nowrap class='pt10' align='right'>��</td>       <!-- ;�� ������ -->
                    <td nowrap class='pt10' align='right' bgcolor='#ffffc6'><?php echo $view_material ?></td>   <!-- ��� -->
                    <td nowrap class='pt10' align='right' bgcolor='#ceffce'>��</td>       <!-- ;�� �δ��� -->
                    <td nowrap class='pt10' align='right' bgcolor='#ceffce'>��</td>       <!-- ;�� �δ��� -->
                    <td nowrap class='pt10' align='right' bgcolor='#ceffce'>��</td>       <!-- ;�� �δ��� -->
                </tr>
                <tr>
                    <td nowrap class='pt10' align='right' bgcolor='#e6e6e6'>������Ψ</td>
                    <td nowrap class='pt10' align='right' bgcolor='#ffffc6'>��</td>   <!-- ���ץ� -->
                    <td nowrap class='pt10' align='right' bgcolor='#ffffc6'>��</td>   <!-- ��˥� -->
                    <td nowrap class='pt10' align='right' bgcolor='#ffffc6'>��</td>   <!-- ��� -->
                    <td nowrap class='pt10' align='right' bgcolor='#e6e6e6'><?php echo $view_shiire_ritu_c ?></td>   <!-- ���ץ� -->
                    <td nowrap class='pt10' align='right' bgcolor='#e6e6e6'><?php echo $view_shiire_ritu_l ?></td>   <!-- ��˥� -->
                    <td nowrap class='pt10' align='right' bgcolor='#e6e6e6'><?php echo $view_shiire_ritu ?></td>   <!-- ��� -->
                    <td nowrap class='pt10' align='right'>��</td>       <!-- ;�� ������ -->
                    <td nowrap class='pt10' align='right'>��</td>       <!-- ;�� ������ -->
                    <td nowrap class='pt10' align='right' bgcolor='#e6e6e6'>����</td>       <!-- ;�� ������ -->
                    <td nowrap class='pt10' align='right' bgcolor='#e6e6e6'><?php echo $view_barance ?></td>   <!-- ��� -->
                    <td nowrap class='pt10' align='right' bgcolor='#ceffce'>��</td>       <!-- ;�� �δ��� -->
                    <td nowrap class='pt10' align='right' bgcolor='#ceffce'>��</td>       <!-- ;�� �δ��� -->
                    <td nowrap class='pt10' align='right' bgcolor='#ceffce'>��</td>       <!-- ;�� �δ��� -->
                </tr>
                <tr>
                    <td nowrap class='pt10b' align='right' bgcolor='#ffffc6'>������</td>
                    <td nowrap class='pt10' align='right' bgcolor='#ffffc6'><?php echo $view_material_c ?></td>   <!-- ���ץ� -->
                    <td nowrap class='pt10' align='right' bgcolor='#ffffc6'><?php echo $view_material_l ?></td>   <!-- ��˥� -->
                    <td nowrap class='pt10' align='right' bgcolor='#ffffc6'><?php echo $view_material ?></td>   <!-- ��� -->
                    <td nowrap class='pt10' align='right' bgcolor='#ffffc6'>��</td>   <!-- ���ץ� -->
                    <td nowrap class='pt10' align='right' bgcolor='#ffffc6'>��</td>   <!-- ��˥� -->
                    <td nowrap class='pt10' align='right' bgcolor='#ffffc6'>��</td>   <!-- ��� -->
                    <td nowrap class='pt10' align='right' bgcolor='#ffffc6'>��</td>       <!-- ;�� ������ -->
                    <td nowrap class='pt10' align='right' bgcolor='#ffffc6'>��</td>       <!-- ;�� ������ -->
                    <td nowrap class='pt10' align='right' bgcolor='#ffffc6'>��</td>       <!-- ;�� ������ -->
                    <td nowrap class='pt10' align='right' bgcolor='#ffffc6'><?php echo $view_material ?></td>   <!-- ��� -->
                    <td nowrap class='pt10' align='right' bgcolor='#ffffc6'>��</td>       <!-- ;�� �δ��� -->
                    <td nowrap class='pt10' align='right' bgcolor='#ffffc6'>��</td>       <!-- ;�� �δ��� -->
                    <td nowrap class='pt10' align='right' bgcolor='#ffffc6'>��</td>       <!-- ;�� �δ��� -->
                </tr>
                <tr>
                    <td width='10' rowspan='<?= $rec_jin+1 ?>' align='center' class='pt10b' bgcolor='#ffffc6'>�ͷ���</td>
                    <TD nowrap class='pt10'>�����</TD>
                    <?php
                        $r = 0;     // �����쥳���� �忧 #b4ffff
                        for ($c=0;$c<$f_mei;$c++) {
                            if ($c == 0 || $c == 1 || $c == 2 || $c == 9)   // ��¤���� ���ץ� ��˥� ���
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ffffc6'>%s</td>\n",$view_data[$r][$c]);
                            elseif ($c == 10 || $c == 11 || $c == 12)       // �δ��� ���ץ� ��˥� ���
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ceffce'>%s</td>\n",$view_data[$r][$c]);
                            else
                                printf("<td nowrap class='pt10' align='right'>%s</td>\n",$view_data[$r][$c]);
                        }
                    ?>
                </tr>
                <TR>
                    <TD nowrap class='pt10'>��������</TD>
                    <?php
                        $r = 1;     // �����쥳����
                        for ($c=0;$c<$f_mei;$c++) {
                            if ($c == 0 || $c == 1 || $c == 2 || $c == 9)   // ��¤���� ���ץ� ��˥� ���
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ffffc6'>%s</td>\n",$view_data[$r][$c]);
                            elseif ($c == 10 || $c == 11 || $c == 12)       // �δ��� ���ץ� ��˥� ���
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ceffce'>%s</td>\n",$view_data[$r][$c]);
                            else
                                printf("<td nowrap class='pt10' align='right'>%s</td>\n",$view_data[$r][$c]);
                        }
                    ?>
                </TR>
                <TR>
                    <TD nowrap class='pt10'>��Ϳ����</TD>
                    <?php
                        $r = 2;     // �����쥳����
                        for ($c=0;$c<$f_mei;$c++) {
                            if ($c == 0 || $c == 1 || $c == 2 || $c == 9)   // ��¤���� ���ץ� ��˥� ���
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ffffc6'>%s</td>\n",$view_data[$r][$c]);
                            elseif ($c == 10 || $c == 11 || $c == 12)       // �δ��� ���ץ� ��˥� ���
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ceffce'>%s</td>\n",$view_data[$r][$c]);
                            else
                                printf("<td nowrap class='pt10' align='right'>%s</td>\n",$view_data[$r][$c]);
                        }
                    ?>
                </TR>
                <TR>
                    <TD nowrap class='pt10'>������</TD>
                    <?php
                        $r = 3;     // �����쥳����
                        for ($c=0;$c<$f_mei;$c++) {
                            if ($c == 0 || $c == 1 || $c == 2 || $c == 9)   // ��¤���� ���ץ� ��˥� ���
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ffffc6'>%s</td>\n",$view_data[$r][$c]);
                            elseif ($c == 10 || $c == 11 || $c == 12)       // �δ��� ���ץ� ��˥� ���
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ceffce'>%s</td>\n",$view_data[$r][$c]);
                            else
                                printf("<td nowrap class='pt10' align='right'>%s</td>\n",$view_data[$r][$c]);
                        }
                    ?>
                </TR>
                <TR>
                    <TD nowrap class='pt10'>ˡ��ʡ����</TD>
                    <?php
                        $r = 4;     // �����쥳����
                        for ($c=0;$c<$f_mei;$c++) {
                            if ($c == 0 || $c == 1 || $c == 2 || $c == 9)   // ��¤���� ���ץ� ��˥� ���
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ffffc6'>%s</td>\n",$view_data[$r][$c]);
                            elseif ($c == 10 || $c == 11 || $c == 12)       // �δ��� ���ץ� ��˥� ���
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ceffce'>%s</td>\n",$view_data[$r][$c]);
                            else
                                printf("<td nowrap class='pt10' align='right'>%s</td>\n",$view_data[$r][$c]);
                        }
                    ?>
                </TR>
                <TR>
                    <TD nowrap class='pt10'>����ʡ����</TD>
                    <?php
                        $r = 5;     // �����쥳����
                        for ($c=0;$c<$f_mei;$c++) {
                            if ($c == 0 || $c == 1 || $c == 2 || $c == 9)   // ��¤���� ���ץ� ��˥� ���
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ffffc6'>%s</td>\n",$view_data[$r][$c]);
                            elseif ($c == 10 || $c == 11 || $c == 12)       // �δ��� ���ץ� ��˥� ���
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ceffce'>%s</td>\n",$view_data[$r][$c]);
                            else
                                printf("<td nowrap class='pt10' align='right'>%s</td>\n",$view_data[$r][$c]);
                        }
                    ?>
                </TR>
                <TR>
                    <TD nowrap class='pt10'>��Ϳ�����ⷫ��</TD>
                    <?php
                        $r = 6;     // �����쥳����
                        for ($c=0;$c<$f_mei;$c++) {
                            if ($c == 0 || $c == 1 || $c == 2 || $c == 9)   // ��¤���� ���ץ� ��˥� ���
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ffffc6'>%s</td>\n",$view_data[$r][$c]);
                            elseif ($c == 10 || $c == 11 || $c == 12)       // �δ��� ���ץ� ��˥� ���
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ceffce'>%s</td>\n",$view_data[$r][$c]);
                            else
                                printf("<td nowrap class='pt10' align='right'>%s</td>\n",$view_data[$r][$c]);
                        }
                    ?>
                </TR>
                <TR>
                    <TD nowrap class='pt10'>�࿦��������</TD>
                    <?php
                        $r = 7;     // �����쥳����
                        for ($c=0;$c<$f_mei;$c++) {
                            if ($c == 0 || $c == 1 || $c == 2 || $c == 9)   // ��¤���� ���ץ� ��˥� ���
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ffffc6'>%s</td>\n",$view_data[$r][$c]);
                            elseif ($c == 10 || $c == 11 || $c == 12)       // �δ��� ���ץ� ��˥� ���
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ceffce'>%s</td>\n",$view_data[$r][$c]);
                            else
                                printf("<td nowrap class='pt10' align='right'>%s</td>\n",$view_data[$r][$c]);
                        }
                    ?>
                </TR>
                <TR bgcolor='#ffffc6'>
                    <TD nowrap class='pt10b' align='right'>�ͷ����</TD>
                    <?php
                        for ($c=0;$c<$f_mei;$c++) {
                            printf("<td nowrap class='pt10' align='right'>%s</td>\n",$view_jin_sum[$c]);
                        }
                    ?>
                </TR>
                <tr>
                    <td width='10' rowspan='<?= $rec_kei+1 ?>' align='center' class='pt10b' bgcolor='#ffffc6'>����</td>
                    <TD nowrap class='pt10'>ι�������</TD>
                    <?php
                        $r = 8;     // �����쥳����
                        for ($c=0;$c<$f_mei;$c++) {
                            if ($c == 0 || $c == 1 || $c == 2 || $c == 9)   // ��¤���� ���ץ� ��˥� ���
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ffffc6'>%s</td>\n",$view_data[$r][$c]);
                            elseif ($c == 10 || $c == 11 || $c == 12)       // �δ��� ���ץ� ��˥� ���
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ceffce'>%s</td>\n",$view_data[$r][$c]);
                            else
                                printf("<td nowrap class='pt10' align='right'>%s</td>\n",$view_data[$r][$c]);
                        }
                    ?>
                </tr>
                <tr>
                    <TD nowrap class='pt10'>������ĥ</TD>
                    <?php
                    $r = 9;     // �����쥳����
                        for ($c=0;$c<$f_mei;$c++) {
                            if ($c == 0 || $c == 1 || $c == 2 || $c == 9)   // ��¤���� ���ץ� ��˥� ���
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ffffc6'>%s</td>\n",$view_data[$r][$c]);
                            elseif ($c == 10 || $c == 11 || $c == 12)       // �δ��� ���ץ� ��˥� ���
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ceffce'>%s</td>\n",$view_data[$r][$c]);
                            else
                                printf("<td nowrap class='pt10' align='right'>%s</td>\n",$view_data[$r][$c]);
                        }
                    ?>
                </tr>
                <tr>
                    <TD nowrap class='pt10'>�̡�������</TD>
                    <?php
                    $r = 10;     // �����쥳����
                        for ($c=0;$c<$f_mei;$c++) {
                            if ($c == 0 || $c == 1 || $c == 2 || $c == 9)   // ��¤���� ���ץ� ��˥� ���
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ffffc6'>%s</td>\n",$view_data[$r][$c]);
                            elseif ($c == 10 || $c == 11 || $c == 12)       // �δ��� ���ץ� ��˥� ���
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ceffce'>%s</td>\n",$view_data[$r][$c]);
                            else
                                printf("<td nowrap class='pt10' align='right'>%s</td>\n",$view_data[$r][$c]);
                        }
                    ?>
                </tr>
                <tr>
                    <TD nowrap class='pt10'>�񡡵ġ���</TD>
                    <?php
                    $r = 11;     // �����쥳����
                        for ($c=0;$c<$f_mei;$c++) {
                            if ($c == 0 || $c == 1 || $c == 2 || $c == 9)   // ��¤���� ���ץ� ��˥� ���
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ffffc6'>%s</td>\n",$view_data[$r][$c]);
                            elseif ($c == 10 || $c == 11 || $c == 12)       // �δ��� ���ץ� ��˥� ���
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ceffce'>%s</td>\n",$view_data[$r][$c]);
                            else
                                printf("<td nowrap class='pt10' align='right'>%s</td>\n",$view_data[$r][$c]);
                        }
                    ?>
                </tr>
                <tr>
                    <TD nowrap class='pt10'>���������</TD>
                    <?php
                    $r = 12;     // �����쥳����
                        for ($c=0;$c<$f_mei;$c++) {
                            if ($c == 0 || $c == 1 || $c == 2 || $c == 9)   // ��¤���� ���ץ� ��˥� ���
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ffffc6'>%s</td>\n",$view_data[$r][$c]);
                            elseif ($c == 10 || $c == 11 || $c == 12)       // �δ��� ���ץ� ��˥� ���
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ceffce'>%s</td>\n",$view_data[$r][$c]);
                            else
                                printf("<td nowrap class='pt10' align='right'>%s</td>\n",$view_data[$r][$c]);
                        }
                    ?>
                </tr>
                <tr>
                    <TD nowrap class='pt10'>����������</TD>
                    <?php
                    $r = 13;     // �����쥳����
                        for ($c=0;$c<$f_mei;$c++) {
                            if ($c == 0 || $c == 1 || $c == 2 || $c == 9)   // ��¤���� ���ץ� ��˥� ���
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ffffc6'>%s</td>\n",$view_data[$r][$c]);
                            elseif ($c == 10 || $c == 11 || $c == 12)       // �δ��� ���ץ� ��˥� ���
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ceffce'>%s</td>\n",$view_data[$r][$c]);
                            else
                                printf("<td nowrap class='pt10' align='right'>%s</td>\n",$view_data[$r][$c]);
                        }
                    ?>
                </tr>
                <tr>
                    <TD nowrap class='pt10'>�ᡡ�͡���</TD>
                    <?php
                    $r = 14;     // �����쥳����
                        for ($c=0;$c<$f_mei;$c++) {
                            if ($c == 0 || $c == 1 || $c == 2 || $c == 9)   // ��¤���� ���ץ� ��˥� ���
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ffffc6'>%s</td>\n",$view_data[$r][$c]);
                            elseif ($c == 10 || $c == 11 || $c == 12)       // �δ��� ���ץ� ��˥� ���
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ceffce'>%s</td>\n",$view_data[$r][$c]);
                            else
                                printf("<td nowrap class='pt10' align='right'>%s</td>\n",$view_data[$r][$c]);
                        }
                    ?>
                </tr>
                <tr>
                    <TD nowrap class='pt10'>���²�¤��</TD>
                    <?php
                    $r = 15;     // �����쥳����
                        for ($c=0;$c<$f_mei;$c++) {
                            if ($c == 0 || $c == 1 || $c == 2 || $c == 9)   // ��¤���� ���ץ� ��˥� ���
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ffffc6'>%s</td>\n",$view_data[$r][$c]);
                            elseif ($c == 10 || $c == 11 || $c == 12)       // �δ��� ���ץ� ��˥� ���
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ceffce'>%s</td>\n",$view_data[$r][$c]);
                            else
                                printf("<td nowrap class='pt10' align='right'>%s</td>\n",$view_data[$r][$c]);
                        }
                    ?>
                </tr>
                <tr>
                    <TD nowrap class='pt10'>�޽񶵰���</TD>
                    <?php
                    $r = 16;     // �����쥳����
                        for ($c=0;$c<$f_mei;$c++) {
                            if ($c == 0 || $c == 1 || $c == 2 || $c == 9)   // ��¤���� ���ץ� ��˥� ���
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ffffc6'>%s</td>\n",$view_data[$r][$c]);
                            elseif ($c == 10 || $c == 11 || $c == 12)       // �δ��� ���ץ� ��˥� ���
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ceffce'>%s</td>\n",$view_data[$r][$c]);
                            else
                                printf("<td nowrap class='pt10' align='right'>%s</td>\n",$view_data[$r][$c]);
                        }
                    ?>
                </tr>
                <tr>
                    <TD nowrap class='pt10'>��̳������</TD>
                    <?php
                    $r = 17;     // �����쥳����
                        for ($c=0;$c<$f_mei;$c++) {
                            if ($c == 0 || $c == 1 || $c == 2 || $c == 9)   // ��¤���� ���ץ� ��˥� ���
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ffffc6'>%s</td>\n",$view_data[$r][$c]);
                            elseif ($c == 10 || $c == 11 || $c == 12)       // �δ��� ���ץ� ��˥� ���
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ceffce'>%s</td>\n",$view_data[$r][$c]);
                            else
                                printf("<td nowrap class='pt10' align='right'>%s</td>\n",$view_data[$r][$c]);
                        }
                    ?>
                </tr>
                <tr>
                    <td nowrap class='pt10'>�����ȡ���</td>
                    <?php
                    $r = 35;     // �����쥳����
                        for ($c=0;$c<$f_mei;$c++) {
                            if ($c == 0 || $c == 1 || $c == 2 || $c == 9)   // ��¤���� ���ץ� ��˥� ���
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ffffc6'>%s</td>\n",$view_data[$r][$c]);
                            elseif ($c == 10 || $c == 11 || $c == 12)       // �δ��� ���ץ� ��˥� ���
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ceffce'>%s</td>\n",$view_data[$r][$c]);
                            else
                                printf("<td nowrap class='pt10' align='right'>%s</td>\n",$view_data[$r][$c]);
                        }
                    ?>
                </tr>
                <tr>
                    <TD nowrap class='pt10'>���Ǹ���</TD>
                    <?php
                    $r = 18;     // �����쥳����
                        for ($c=0;$c<$f_mei;$c++) {
                            if ($c == 0 || $c == 1 || $c == 2 || $c == 9)   // ��¤���� ���ץ� ��˥� ���
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ffffc6'>%s</td>\n",$view_data[$r][$c]);
                            elseif ($c == 10 || $c == 11 || $c == 12)       // �δ��� ���ץ� ��˥� ���
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ceffce'>%s</td>\n",$view_data[$r][$c]);
                            else
                                printf("<td nowrap class='pt10' align='right'>%s</td>\n",$view_data[$r][$c]);
                        }
                    ?>
                </tr>
                <tr>
                    <TD nowrap class='pt10'>�������</TD>
                    <?php
                    $r = 19;     // �����쥳����
                        for ($c=0;$c<$f_mei;$c++) {
                            if ($c == 0 || $c == 1 || $c == 2 || $c == 9)   // ��¤���� ���ץ� ��˥� ���
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ffffc6'>%s</td>\n",$view_data[$r][$c]);
                            elseif ($c == 10 || $c == 11 || $c == 12)       // �δ��� ���ץ� ��˥� ���
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ceffce'>%s</td>\n",$view_data[$r][$c]);
                            else
                                printf("<td nowrap class='pt10' align='right'>%s</td>\n",$view_data[$r][$c]);
                        }
                    ?>
                </tr>
                <tr>
                    <TD nowrap class='pt10'>����������</TD>
                    <?php
                    $r = 20;     // �����쥳����
                        for ($c=0;$c<$f_mei;$c++) {
                            if ($c == 0 || $c == 1 || $c == 2 || $c == 9)   // ��¤���� ���ץ� ��˥� ���
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ffffc6'>%s</td>\n",$view_data[$r][$c]);
                            elseif ($c == 10 || $c == 11 || $c == 12)       // �δ��� ���ץ� ��˥� ���
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ceffce'>%s</td>\n",$view_data[$r][$c]);
                            else
                                printf("<td nowrap class='pt10' align='right'>%s</td>\n",$view_data[$r][$c]);
                        }
                    ?>
                </tr>
                <tr>
                    <TD nowrap class='pt10'>����������</TD>
                    <?php
                    $r = 21;     // �����쥳����
                        for ($c=0;$c<$f_mei;$c++) {
                            if ($c == 0 || $c == 1 || $c == 2 || $c == 9)   // ��¤���� ���ץ� ��˥� ���
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ffffc6'>%s</td>\n",$view_data[$r][$c]);
                            elseif ($c == 10 || $c == 11 || $c == 12)       // �δ��� ���ץ� ��˥� ���
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ceffce'>%s</td>\n",$view_data[$r][$c]);
                            else
                                printf("<td nowrap class='pt10' align='right'>%s</td>\n",$view_data[$r][$c]);
                        }
                    ?>
                </tr>
                <tr>
                    <TD nowrap class='pt10'>�ݾڽ�����</TD>
                    <?php
                    $r = 22;     // �����쥳����
                        for ($c=0;$c<$f_mei;$c++) {
                            if ($c == 0 || $c == 1 || $c == 2 || $c == 9)   // ��¤���� ���ץ� ��˥� ���
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ffffc6'>%s</td>\n",$view_data[$r][$c]);
                            elseif ($c == 10 || $c == 11 || $c == 12)       // �δ��� ���ץ� ��˥� ���
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ceffce'>%s</td>\n",$view_data[$r][$c]);
                            else
                                printf("<td nowrap class='pt10' align='right'>%s</td>\n",$view_data[$r][$c]);
                        }
                    ?>
                </tr>
                <tr>
                    <TD nowrap class='pt10'>��̳�Ѿ�������</TD>
                    <?php
                    $r = 23;     // �����쥳����
                        for ($c=0;$c<$f_mei;$c++) {
                            if ($c == 0 || $c == 1 || $c == 2 || $c == 9)   // ��¤���� ���ץ� ��˥� ���
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ffffc6'>%s</td>\n",$view_data[$r][$c]);
                            elseif ($c == 10 || $c == 11 || $c == 12)       // �δ��� ���ץ� ��˥� ���
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ceffce'>%s</td>\n",$view_data[$r][$c]);
                            else
                                printf("<td nowrap class='pt10' align='right'>%s</td>\n",$view_data[$r][$c]);
                        }
                    ?>
                </tr>
                <tr>
                    <TD nowrap class='pt10'>�����������</TD>
                    <?php
                    $r = 24;     // �����쥳����
                        for ($c=0;$c<$f_mei;$c++) {
                            if ($c == 0 || $c == 1 || $c == 2 || $c == 9)   // ��¤���� ���ץ� ��˥� ���
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ffffc6'>%s</td>\n",$view_data[$r][$c]);
                            elseif ($c == 10 || $c == 11 || $c == 12)       // �δ��� ���ץ� ��˥� ���
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ceffce'>%s</td>\n",$view_data[$r][$c]);
                            else
                                printf("<td nowrap class='pt10' align='right'>%s</td>\n",$view_data[$r][$c]);
                        }
                    ?>
                </tr>
                <tr>
                    <TD nowrap class='pt10'>�֡�ξ����</TD>
                    <?php
                    $r = 25;     // �����쥳����
                        for ($c=0;$c<$f_mei;$c++) {
                            if ($c == 0 || $c == 1 || $c == 2 || $c == 9)   // ��¤���� ���ץ� ��˥� ���
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ffffc6'>%s</td>\n",$view_data[$r][$c]);
                            elseif ($c == 10 || $c == 11 || $c == 12)       // �δ��� ���ץ� ��˥� ���
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ceffce'>%s</td>\n",$view_data[$r][$c]);
                            else
                                printf("<td nowrap class='pt10' align='right'>%s</td>\n",$view_data[$r][$c]);
                        }
                    ?>
                </tr>
                <tr>
                    <TD nowrap class='pt10'>�ݡ�������</TD>
                    <?php
                    $r = 26;     // �����쥳����
                        for ($c=0;$c<$f_mei;$c++) {
                            if ($c == 0 || $c == 1 || $c == 2 || $c == 9)   // ��¤���� ���ץ� ��˥� ���
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ffffc6'>%s</td>\n",$view_data[$r][$c]);
                            elseif ($c == 10 || $c == 11 || $c == 12)       // �δ��� ���ץ� ��˥� ���
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ceffce'>%s</td>\n",$view_data[$r][$c]);
                            else
                                printf("<td nowrap class='pt10' align='right'>%s</td>\n",$view_data[$r][$c]);
                        }
                    ?>
                </tr>
                <tr>
                    <TD nowrap class='pt10'>��ƻ��Ǯ��</TD>
                    <?php
                    $r = 27;     // �����쥳����
                        for ($c=0;$c<$f_mei;$c++) {
                            if ($c == 0 || $c == 1 || $c == 2 || $c == 9)   // ��¤���� ���ץ� ��˥� ���
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ffffc6'>%s</td>\n",$view_data[$r][$c]);
                            elseif ($c == 10 || $c == 11 || $c == 12)       // �δ��� ���ץ� ��˥� ���
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ceffce'>%s</td>\n",$view_data[$r][$c]);
                            else
                                printf("<td nowrap class='pt10' align='right'>%s</td>\n",$view_data[$r][$c]);
                        }
                    ?>
                </tr>
                <tr>
                    <TD nowrap class='pt10'>��������</TD>
                    <?php
                    $r = 28;     // �����쥳����
                        for ($c=0;$c<$f_mei;$c++) {
                            if ($c == 0 || $c == 1 || $c == 2 || $c == 9)   // ��¤���� ���ץ� ��˥� ���
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ffffc6'>%s</td>\n",$view_data[$r][$c]);
                            elseif ($c == 10 || $c == 11 || $c == 12)       // �δ��� ���ץ� ��˥� ���
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ceffce'>%s</td>\n",$view_data[$r][$c]);
                            else
                                printf("<td nowrap class='pt10' align='right'>%s</td>\n",$view_data[$r][$c]);
                        }
                    ?>
                </tr>
                <tr>
                    <TD nowrap class='pt10'>��ʧ�����</TD>
                    <?php
                    $r = 29;     // �����쥳����
                        for ($c=0;$c<$f_mei;$c++) {
                            if ($c == 0 || $c == 1 || $c == 2 || $c == 9)   // ��¤���� ���ץ� ��˥� ���
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ffffc6'>%s</td>\n",$view_data[$r][$c]);
                            elseif ($c == 10 || $c == 11 || $c == 12)       // �δ��� ���ץ� ��˥� ���
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ceffce'>%s</td>\n",$view_data[$r][$c]);
                            else
                                printf("<td nowrap class='pt10' align='right'>%s</td>\n",$view_data[$r][$c]);
                        }
                    ?>
                </tr>
                <tr>
                    <TD nowrap class='pt10'>�������</TD>
                    <?php
                    $r = 30;     // �����쥳����
                        for ($c=0;$c<$f_mei;$c++) {
                            if ($c == 0 || $c == 1 || $c == 2 || $c == 9)   // ��¤���� ���ץ� ��˥� ���
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ffffc6'>%s</td>\n",$view_data[$r][$c]);
                            elseif ($c == 10 || $c == 11 || $c == 12)       // �δ��� ���ץ� ��˥� ���
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ceffce'>%s</td>\n",$view_data[$r][$c]);
                            else
                                printf("<td nowrap class='pt10' align='right'>%s</td>\n",$view_data[$r][$c]);
                        }
                    ?>
                </tr>
                <tr>
                    <TD nowrap class='pt10'>���ա���</TD>
                    <?php
                    $r = 31;     // �����쥳����
                        for ($c=0;$c<$f_mei;$c++) {
                            if ($c == 0 || $c == 1 || $c == 2 || $c == 9)   // ��¤���� ���ץ� ��˥� ���
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ffffc6'>%s</td>\n",$view_data[$r][$c]);
                            elseif ($c == 10 || $c == 11 || $c == 12)       // �δ��� ���ץ� ��˥� ���
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ceffce'>%s</td>\n",$view_data[$r][$c]);
                            else
                                printf("<td nowrap class='pt10' align='right'>%s</td>\n",$view_data[$r][$c]);
                        }
                    ?>
                </tr>
                <tr>
                    <TD nowrap class='pt10'>�ҡ��ߡ���</TD>
                    <?php
                    $r = 32;     // �����쥳����
                        for ($c=0;$c<$f_mei;$c++) {
                            if ($c == 0 || $c == 1 || $c == 2 || $c == 9)   // ��¤���� ���ץ� ��˥� ���
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ffffc6'>%s</td>\n",$view_data[$r][$c]);
                            elseif ($c == 10 || $c == 11 || $c == 12)       // �δ��� ���ץ� ��˥� ���
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ceffce'>%s</td>\n",$view_data[$r][$c]);
                            else
                                printf("<td nowrap class='pt10' align='right'>%s</td>\n",$view_data[$r][$c]);
                        }
                    ?>
                </tr>
                <tr>
                    <TD nowrap class='pt10'>�¡��ڡ���</TD>
                    <?php
                    $r = 33;     // �����쥳����
                        for ($c=0;$c<$f_mei;$c++) {
                            if ($c == 0 || $c == 1 || $c == 2 || $c == 9)   // ��¤���� ���ץ� ��˥� ���
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ffffc6'>%s</td>\n",$view_data[$r][$c]);
                            elseif ($c == 10 || $c == 11 || $c == 12)       // �δ��� ���ץ� ��˥� ���
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ceffce'>%s</td>\n",$view_data[$r][$c]);
                            else
                                printf("<td nowrap class='pt10' align='right'>%s</td>\n",$view_data[$r][$c]);
                        }
                    ?>
                </tr>
                <tr>
                    <TD nowrap class='pt10'>����������</TD>
                    <?php
                    $r = 34;     // �����쥳����
                        for ($c=0;$c<$f_mei;$c++) {
                            if ($c == 0 || $c == 1 || $c == 2 || $c == 9)   // ��¤���� ���ץ� ��˥� ���
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ffffc6'>%s</td>\n",$view_data[$r][$c]);
                            elseif ($c == 10 || $c == 11 || $c == 12)       // �δ��� ���ץ� ��˥� ���
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ceffce'>%s</td>\n",$view_data[$r][$c]);
                            else
                                printf("<td nowrap class='pt10' align='right'>%s</td>\n",$view_data[$r][$c]);
                        }
                    ?>
                </tr>
                <tr bgcolor='#ffffc6'>
                    <TD nowrap class='pt10b' align='right'>�����</TD>
                    <?php
                        for ($c=0;$c<$f_mei;$c++) {
                            printf("<td nowrap class='pt10' align='right'>%s</td>\n",$view_kei_sum[$c]);
                        }
                    ?>
                </tr>
                <tr bgcolor='#ffffc6'>
                    <TD colspan='2' nowrap class='pt10b' align='right'>�硡��</TD>
                    <?php
                        for ($c=0;$c<$f_mei;$c++) {
                            printf("<td nowrap class='pt10' align='right'>%s</td>\n",$view_all_sum[$c]);
                        }
                    ?>
                </tr>
            </TBODY>
        </table>
    </center>
</body>
</html>
