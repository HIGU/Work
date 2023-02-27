<?php
//////////////////////////////////////////////////////////////////////////////
// �»�״ط� � �ã̾��ʴ��� �������ɽ                                //
// Copyright(C) 2003-2015 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp       //
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
// 2009/08/20 ���ʴ����η������ɽ�����ɲá���ե������_old�Ȥ���          //
//            �̥�˥塼�˻Ĥ���                                       ��ë //
// 2009/10/15 �ƾ��ס���פ��������ѹ�                                 ��ë //
// 2009/11/10 ���ɵ�Ϳ����ʬ���̣����褦���ѹ��ʵ�Ϳ������           ��ë //
// 2010/01/15 200912ʬ�η����Ĵ����ݤ��褦�Ȥ�����Excel¦������ʤ���     //
//            Ĵ�����ʤ���                                             ��ë //
// 2010/02/05 ź�Ĥ���ε�Ϳ��C��L35�󡢻30��˿�ʬ����褦���ѹ�        //
//            �����ޥ��������ˤ��롣                                   ��ë //
// 2010/02/08 ���Ϳ�����ޥ����������������褦���ѹ�             ��ë //
// 2010/06/04 2010/05����������Τȥ��ץ�ˤ�+800,000Ĵ��                 //
//            2010/06���ᤷͽ�� �� ���ʤ����Ȥ˷��ꤷ���Τ��ᤷ      ��ë //
// 2012/02/08 2012ǯ1�� ��̳������ Ĵ�� ��˥���¤���� +1,156,130��    ��ë //
//             �� ʿ�в����ɸ��� 2��˵�Ĵ����Ԥ�����                      //
// 2012/02/09 ���ץ顦��˥������ɤ���¤������Ͽ���ɲ�                 ��ë //
// 2012/03/05 2012ǯ1�� ��̳������ Ĵ�� ��˥���¤���� -1,156,130�� �� ��ë //
// 2013/11/07 2013ǯ10�� ���ɶ�̳������ Ĵ�� +1,245,035��              ��ë //
//             �� �����ɸ��� 11��˵�Ĵ����Ԥ�����                         //
// 2013/11/07 2013ǯ11�� ���ɶ�̳������ Ĵ�� -1,245,035�� �ᤷ����     ��ë //
// 2015/02/20 ���졼���б���λ������β����ɲ�(7550)D37 $r=36               //
//            kin1=��¤���� kin2=�δ��� �ʤΤ� kin3��kin9��ɬ�פʤ��ΤǺ�� //
//            $rec_keihi = 28��29���ѹ� (���졼���б����ɲäˤ��)          //
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
$menu->set_title("�� {$ki} ����{$tuki} ���١��� �� ���ʴ��� �� �� �� �� �� �� ɽ");

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
$rec_kei = 29;    // ����λ��Ѳ��ܿ�       ���졼���б����б��Τ��� 28��29
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
    7550 = ���졼���б���
    8000 = ����������
******/
$kei_act = array(7501,7502,7503,7504,7505,7506,7508,7509,7510,7512,7521,7522,7523,7524,7525,7526,7527,7528,7530,7531,7532,7533,7536,7537,7538,7540,7550,8000);
////// ���Τ�����
$actcod  = array(8101,8102,8103,8104,8105,8106,8121,8123,7501,7502,7503,7504,7505,7506,7508,7509,7510,7512,7521,7522,7523,7524,7525,7526,7527,7528,7530,7531,7532,7533,7536,7537,7538,7540,7550,8000);

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
////// ��Ϳ����ۤμ���
if ($yyyymm >= 201001) {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='���Ϳ�����'", $yyyymm);
    if (getUniResult($query, $s_kyu_kei) < 1) {
        $s_kyu_kei = 0;                    // ��������
        $s_kyu_kin = 0;
    } else {
        $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='���Ϳ����Ψ'", $yyyymm);
        if (getUniResult($query, $s_kyu_kin) < 1) {
            $s_kyu_kin = 0;
        }
    }
}
if ($yyyymm >= 201001) {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='���ץ��Ϳ����Ψ'", $yyyymm);
    if (getUniResult($query, $c_kyu_kin) < 1) {
        $c_kyu_kin = 0;
    }
}
if ($yyyymm >= 201001) {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='��˥���Ϳ����Ψ'", $yyyymm);
    if (getUniResult($query, $l_kyu_kin) < 1) {
        $l_kyu_kin = 0;
    }
}

////// ����ơ��֥���ǡ���������
$res_jin = array();     /*** ����Υǡ������� ***/
$query = sprintf("select kin00, kin01, kin02, kin03, kin04, kin05, kin06, kin07, kin08, kin09, kin10, kin11, kin12, actcod from act_cl_history where pl_bs_ym=%d and (actcod>=%d and actcod<=%d) order by actcod ASC", $yyyymm, $str_jin, $end_jin);
if (($rows_jin = getResult2($query,$res_jin)) > 0) {     ///// �ǡ���̵���Υ����å� ͥ���̤γ�̤����
    $res_kei = array();                                             // �ߴ����Τ��� actcod=7520��7550 ��ǽ�Ͻ�������
    $query = sprintf("select kin00, kin01, kin02, kin03, kin04, kin05, kin06, kin07, kin08, kin09, kin10, kin11, kin12, actcod from act_cl_history where pl_bs_ym=%d and (actcod>=%d and actcod<=%d) and actcod!=7520 and actcod!=7550 order by actcod ASC", $yyyymm, $str_kei, $end_kei);
    if (($rows_kei = getResult2($query,$res_kei)) > 0) {     ///// �ǡ���̵���Υ����å� ͥ���̤γ�̤����
        // ���ʴ���580����򥫥ץ��������ޥ��ʥ�
        $bkan_jin = array();
        $view_bkan_jin = array();
        for ($r=0; $r < $rows_jin; $r++) {
            $res_580_jin = array();
            $query = sprintf("select * from act_allo_history where pl_bs_ym=%d and actcod=%d and orign_id = 580 order by actcod ASC", $yyyymm, $res_jin[$r][13]);
            if (($rows_580_jin = getResult2($query,$res_580_jin)) > 0) {
                // 200907�Τ߾��ʴ�����ʻ�Ĥ��ˤ���Ϳ������Ĵ��
                if (($r == 1) && ($yyyymm == 200907)) {
                    $res_580_jin[0][3] = $res_580_jin[0][3] + 2338178;
                }
                if (($r == 1) && ($yyyymm == 200912)) {
                    $res_jin[1][6] = $res_jin[1][6] + 1227429;
                    $res_jin[1][7] = $res_jin[1][7] - 1409708 + 182279;
                    //$res_jin[1][8] = $res_jin[1][8] + 1409708;
                    //$res_jin[1][9] = $res_jin[1][9] + 1409708;
                    $res_jin[1][0] = $res_jin[1][0] + 1227429;
                    $res_jin[1][1] = $res_jin[1][1] - 1409708 + 182279;
                    //$res_jin[1][2] = $res_jin[1][2] + 1409708;
                }
                if (($r == 1) && ($yyyymm >= 201001)) {
                    $res_jin[1][6] = $res_jin[1][6] + $c_kyu_kin;
                    $res_jin[1][7] = $res_jin[1][7] - $s_kyu_kei + $s_kyu_kin + $l_kyu_kin;
                    //$res_jin[1][8] = $res_jin[1][8] + 302626;
                    //$res_jin[1][9] = $res_jin[1][9] + 302626;
                    $res_jin[1][0] = $res_jin[1][0] + $c_kyu_kin;
                    $res_jin[1][1] = $res_jin[1][1] - $s_kyu_kei + $s_kyu_kin + $l_kyu_kin;
                    //$res_jin[1][2] = $res_jin[1][2] + 302626;
                }
                $res_jin[$r][6] = $res_jin[$r][6] - $res_580_jin[0][3];
                $res_jin[$r][0] = $res_jin[$r][0] - $res_580_jin[0][3];
                if (($r == 4) && ($yyyymm == 201408)) {
                    // �������¤����ʰ��ֺ���
                    $res_jin[4][0] = $res_jin[4][0] + 93951;    // ���ץ�ˡ��ʡ��
                    $res_jin[4][1] = $res_jin[4][1] + 35232;    // ��˥�ˡ��ʡ��
                    // ���ܷ���Ĵ��
                    $res_jin[4][6] = $res_jin[4][6] + 93951;    // ���ץ�ˡ��ʡ��
                    $res_jin[4][7] = $res_jin[4][7] + 35232;    // ��˥�ˡ��ʡ��
                    // ����Ĵ��
                    $res_580_jin[0][3] = $res_580_jin[0][3] - 129183;
                }
                if (($r == 6) && ($yyyymm == 201408)) {
                    // �������¤����ʰ��ֺ���
                    $res_jin[6][0] = $res_jin[6][0] + 519590;    // ���ץ��Ϳ����
                    $res_jin[6][1] = $res_jin[6][1] + 194846;    // ��˥���Ϳ����
                    // ���ܷ���Ĵ��
                    $res_jin[6][6] = $res_jin[6][6] + 519590;    // ���ץ��Ϳ����
                    $res_jin[6][7] = $res_jin[6][7] + 194846;    // ��˥���Ϳ����
                    // ����Ĵ��
                    $res_580_jin[0][3] = $res_580_jin[0][3] - 714436;
                }
                if (($r == 7) && ($yyyymm == 201408)) {
                    // �������¤����ʰ��ֺ���
                    $res_jin[7][0] = $res_jin[7][0] - 1637;     // ���ץ��࿦���հ���
                    $res_jin[7][1] = $res_jin[7][1] - 614;      // ��˥��࿦���հ���
                    // ���ܷ���Ĵ��
                    $res_jin[7][6] = $res_jin[7][6] - 1637;     // ���ץ��࿦���հ���
                    $res_jin[7][7] = $res_jin[7][7] - 614;      // ��˥��࿦���հ���
                    // ����Ĵ��
                    $res_580_jin[0][3] = $res_580_jin[0][3] + 2251;
                }
                $bkan_jin[$r] = $res_580_jin[0][3];
                $bkan_jin_all += $bkan_jin[$r];
                $view_bkan_jin[$r] = number_format(($bkan_jin[$r] / $tani),$keta);
                $view_bkan_jin_all = number_format(($bkan_jin_all / $tani),$keta);
            } else {
                $bkan_jin[$r] = 0;
                $view_bkan_jin[$r] = number_format(($bkan_jin[$r] / $tani),$keta);
                $view_bkan_jin_all = number_format(($bkan_jin_all / $tani),$keta);
            }
        }
        $bkan_kei = array();
        $view_bkan_kei = array();
        $s = 8;     // $view_data�η�����ʬ��8����Ϥޤ뤿��
        for ($r=0; $r < $rows_kei; $r++) {
            $res_580_kei = array();
            $query = sprintf("select * from act_allo_history where pl_bs_ym=%d and actcod=%d and orign_id = 580 and actcod!=7520 and actcod!=7550 order by actcod ASC", $yyyymm, $res_kei[$r][13]);
            if (($rows_580_kei = getResult2($query,$res_580_kei)) > 0) {
                $res_kei[$r][6] = $res_kei[$r][6] - $res_580_kei[0][3];
                $res_kei[$r][0] = $res_kei[$r][0] - $res_580_kei[0][3];
                $bkan_kei[$s] = $res_580_kei[0][3];
                // 2013/11/07 �ɲ� 2013ǯ10���� ��̳������ʲ����ɸ�����Ĵ��
                if ($yyyymm == 201310) {
                    $bkan_kei[17] += 1245035;
                }
                if ($yyyymm == 201311) {
                    $bkan_kei[17] -= 1245035;
                }
                $bkan_kei_all += $bkan_kei[$s];
                $view_bkan_kei[$s] = number_format(($bkan_kei[$s] / $tani),$keta);
                $view_bkan_kei_all = number_format(($bkan_kei_all / $tani),$keta);
            } else {
                $bkan_kei[$s] = 0;
                $view_bkan_kei[$s] = number_format(($bkan_kei[$s] / $tani),$keta);
                $view_bkan_kei_all = number_format(($bkan_kei_all / $tani),$keta);
            }
            $s += 1;
        }
        // 09/11/10�ɲ�
        // �����δ����Ϳ�����������
        $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='���ץ龦�ɼҰ���ʬ��Ϳ'", $yyyymm);
        if (getUniResult($query, $c_allo_kin) < 1) {
            $c_allo_kin = 0;     // ��������
        } else {
            //$c_han_jin = number_format(($c_han_jin / $tani), $keta);
        }
        $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='��˥����ɼҰ���ʬ��Ϳ'", $yyyymm);
        if (getUniResult($query, $l_allo_kin) < 1) {
            $l_allo_kin = 0;     // ��������
        } else {
            //$c_han_jin = number_format(($c_han_jin / $tani), $keta);
        }
        
        // ���ʴ���670����򥫥ץ��δ�����ޥ��ʥ�
        $bhan_jin = array();
        $view_bhan_jin = array();
        for ($r=0; $r < $rows_jin; $r++) {
            $res_670_jin = array();     /*** ����Υǡ������� ***/
            $query = sprintf("select * from act_allo_history where pl_bs_ym=%d and actcod=%d and orign_id = 670 order by actcod ASC", $yyyymm, $res_jin[$r][13]);
            if (($rows_670_jin = getResult2($query,$res_670_jin)) > 0) {
                // 200907�Τ߾��ʴ�����ʻ�Ĥ��ˤ���Ϳ������Ĵ��
                if (($r == 1) && ($yyyymm == 200907)) {
                    $res_670_jin[0][3] = $res_670_jin[0][3] + 180298;
                }
                // 09/11/10�ɲ�
                // �����δ����Ϳ���������̣
                if ($r == 1) {
                    $res_670_jin[0][3] = $res_670_jin[0][3] + $c_allo_kin;
                    $res_jin[$r][10] = $res_jin[$r][10] - $res_670_jin[0][3];
                    $res_jin[$r][11] = $res_jin[$r][11] - $l_allo_kin;
                    $bhan_jin[$r] = $res_670_jin[0][3] + $l_allo_kin;
                } else {
                    $res_jin[$r][10] = $res_jin[$r][10] - $res_670_jin[0][3];
                    $bhan_jin[$r] = $res_670_jin[0][3];
                }
                $bhan_jin_all += $bhan_jin[$r];
                $view_bhan_jin[$r] = number_format(($bhan_jin[$r] / $tani),$keta);
                $view_bhan_jin_all = number_format(($bhan_jin_all / $tani),$keta);
            } else {
                $bhan_jin[$r] = 0;
                $view_bhan_jin[$r] = number_format(($bhan_jin[$r] / $tani),$keta);
                $view_bhan_jin_all = number_format(($bhan_jin_all / $tani),$keta);
            }
        }
        $bhan_kei = array();
        $view_bhan_kei = array();
        $s = 8;     // $view_data�η�����ʬ��8����Ϥޤ뤿��
        for ($r=0; $r < $rows_kei; $r++) {
            $res_670_kei = array();
            $query = sprintf("select * from act_allo_history where pl_bs_ym=%d and actcod=%d and orign_id = 670 and actcod!=7520 and actcod!=7550 order by actcod ASC", $yyyymm, $res_kei[$r][13]);
            if (($rows_670_kei = getResult2($query,$res_670_kei)) > 0) {
                $res_kei[$r][10] = $res_kei[$r][10] - $res_670_kei[0][3];
                $bhan_kei[$s] = $res_670_kei[0][3];
                $bhan_kei_all += $bhan_kei[$s];
                $view_bhan_kei[$s] = number_format(($bhan_kei[$s] / $tani),$keta);
                $view_bhan_kei_all = number_format(($bhan_kei_all / $tani),$keta);
            } else {
                $bhan_kei[$s] = 0;
                $view_bhan_kei[$s] = number_format(($bhan_kei[$s] / $tani),$keta);
                $view_bhan_kei_all = number_format(($bhan_kei_all / $tani),$keta);
            }
            $s += 1;
        }
        ///// �ͷ���ȷ����������
        // 2012/02/08 �ɲ� 2012ǯ1���� ��̳�������ʿ�в����ɸ�����Ĵ��
        if ($yyyymm == 201201) {
            $res_kei[9][1] += 1156130;
            $res_kei[9][2] += 1156130;
            $res_kei[9][4] += 1156130;
            $res_kei[9][5] += 1156130;
            $res_kei[9][9] += 1156130;
        }
        if ($yyyymm == 201202) {
            $res_kei[9][1] -= 1156130;
            $res_kei[9][2] -= 1156130;
            $res_kei[9][4] -= 1156130;
            $res_kei[9][5] -= 1156130;
            $res_kei[9][9] -= 1156130;
        }
        // 2013/11/07 �ɲ� 2013ǯ10���� ���ɶ�̳������ʲ����ɸ�����Ĵ���ʷפΤߡ�
        if ($yyyymm == 201310) {
            $res_kei[9][2] += 1245035;
            $res_kei[9][8] += 1245035;
            $res_kei[9][9] += 1245035;
        }
        if ($yyyymm == 201311) {
            $res_kei[9][2] -= 1245035;
            $res_kei[9][8] -= 1245035;
            $res_kei[9][9] -= 1245035;
        }
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
        $res_580_gai = array();
        $query = sprintf("select * from act_allo_history where pl_bs_ym=%d and orign_id = 580 and actcod=7520 order by actcod ASC", $yyyymm);
        if (($rows_580_gai = getResult2($query,$res_580_gai)) > 0) {
            $bkan_kei[35] = $res_580_gai[0][3];
            $bkan_kei_all += $bkan_kei[35];
            $view_bkan_kei[35] = number_format(($bkan_kei[35] / $tani),$keta);
            $view_bkan_kei_all = number_format(($bkan_kei_all / $tani),$keta);
        } else {
            $bkan_kei[35] = 0;
            $view_bkan_kei[35] = number_format(($bkan_kei[35] / $tani),$keta);
            $view_bkan_kei_all = number_format(($bkan_kei_all / $tani),$keta);
        }
        $res_670_gai = array();
        $query = sprintf("select * from act_allo_history where pl_bs_ym=%d and orign_id = 670 and actcod=7520 order by actcod ASC", $yyyymm);
        if (($rows_670_gai = getResult2($query,$res_670_gai)) > 0) {
            $bhan_kei[35] = $res_670_gai[0][3];
            $bhan_kei_all += $bhan_kei[35];
            $view_bhan_kei[35] = number_format(($bhan_kei[35] / $tani),$keta);
            $view_bhan_kei_all = number_format(($bhan_kei_all / $tani),$keta);
        } else {
            $bhan_kei[35] = 0;
            $view_bhan_kei[35] = number_format(($bhan_kei[35] / $tani),$keta);
            $view_bhan_kei_all = number_format(($bhan_kei_all / $tani),$keta);
        }
        $res_gai = array();
        $query = sprintf("select kin00, kin01, kin02, kin03, kin04, kin05, kin06, kin07, kin08, kin09, kin10, kin11, kin12 from act_cl_history where pl_bs_ym=%d and actcod=7520", $yyyymm);
        if (($rows_gai = getResult2($query,$res_gai)) > 0) {     // �ǡ���̵���Υ����å� ͥ���̤γ�̤����
            for ($c = 0; $c < $f_mei; $c++) {
                if ($c == 6) {
                    $data[35][$c]      = ($res_gai[0][$c] - $bkan_kei[35]) / $tani;
                    $view_data[35][$c] = number_format($data[35][$c], $keta);
                } elseif ($c == 10) {
                    $data[35][$c]      = ($res_gai[0][$c] - $bkan_kei[35]) / $tani;
                    $view_data[35][$c] = number_format($data[35][$c], $keta);
                } else {
                    $data[35][$c]      = $res_gai[0][$c] / $tani;
                    $view_data[35][$c] = number_format($data[35][$c], $keta);
                }
            }
        } else {
            for ($c = 0; $c < $f_mei; $c++) {   // ������(7520)��̵�����0�ǽ����
                if ($c == 6) {
                    $data[35][$c]      = 0 - $bkan_kei[35];
                    $view_data[35][$c] = number_format(($data[35][$c] / $tani), $keta);
                } elseif ($c == 10) {
                    $data[35][$c]      = 0 - $bkan_kei[35];
                    $view_data[35][$c] = number_format(($data[35][$c] / $tani), $keta);
                } else {
                    $data[35][$c]      = 0;
                    $view_data[35][$c] = 0;
                }
            }
        }
        ///// ���졼���б��� �ɲ�ʬ
        $res_580_gai = array();
        $query = sprintf("select * from act_allo_history where pl_bs_ym=%d and orign_id = 580 and actcod=7550 order by actcod ASC", $yyyymm);
        if (($rows_580_gai = getResult2($query,$res_580_gai)) > 0) {
            $bkan_kei[36] = $res_580_gai[0][3];
            $bkan_kei_all += $bkan_kei[36];
            $view_bkan_kei[36] = number_format(($bkan_kei[36] / $tani),$keta);
            $view_bkan_kei_all = number_format(($bkan_kei_all / $tani),$keta);
        } else {
            $bkan_kei[36] = 0;
            $view_bkan_kei[36] = number_format(($bkan_kei[36] / $tani),$keta);
            $view_bkan_kei_all = number_format(($bkan_kei_all / $tani),$keta);
        }
        $res_670_gai = array();
        $query = sprintf("select * from act_allo_history where pl_bs_ym=%d and orign_id = 670 and actcod=7550 order by actcod ASC", $yyyymm);
        if (($rows_670_gai = getResult2($query,$res_670_gai)) > 0) {
            $bhan_kei[36] = $res_670_gai[0][3];
            $bhan_kei_all += $bhan_kei[36];
            $view_bhan_kei[36] = number_format(($bhan_kei[36] / $tani),$keta);
            $view_bhan_kei_all = number_format(($bhan_kei_all / $tani),$keta);
        } else {
            $bhan_kei[36] = 0;
            $view_bhan_kei[36] = number_format(($bhan_kei[36] / $tani),$keta);
            $view_bhan_kei_all = number_format(($bhan_kei_all / $tani),$keta);
        }
        $res_gai = array();
        $query = sprintf("select kin00, kin01, kin02, kin03, kin04, kin05, kin06, kin07, kin08, kin09, kin10, kin11, kin12 from act_cl_history where pl_bs_ym=%d and actcod=7550", $yyyymm);
        if (($rows_gai = getResult2($query,$res_gai)) > 0) {     // �ǡ���̵���Υ����å� ͥ���̤γ�̤����
            for ($c = 0; $c < $f_mei; $c++) {
                if ($c == 6) {
                    $data[36][$c]      = ($res_gai[0][$c] - $bkan_kei[36]) / $tani;
                    $view_data[36][$c] = number_format($data[36][$c], $keta);
                } elseif ($c == 10) {
                    $data[36][$c]      = ($res_gai[0][$c] - $bkan_kei[36]) / $tani;
                    $view_data[36][$c] = number_format($data[36][$c], $keta);
                } else {
                    $data[36][$c]      = $res_gai[0][$c] / $tani;
                    $view_data[36][$c] = number_format($data[36][$c], $keta);
                }
            }
        } else {
            for ($c = 0; $c < $f_mei; $c++) {   // ������(7550)��̵�����0�ǽ����
                if ($c == 6) {
                    $data[36][$c]      = 0 - $bkan_kei[36];
                    $view_data[36][$c] = number_format(($data[36][$c] / $tani), $keta);
                } elseif ($c == 10) {
                    $data[36][$c]      = 0 - $bkan_kei[36];
                    $view_data[36][$c] = number_format(($data[36][$c] / $tani), $keta);
                } else {
                    $data[36][$c]      = 0;
                    $view_data[36][$c] = 0;
                }
            }
        }
        ///// ���ʴ���ʬ �ͷ��񡦷�����
        $bkan_sum = $bkan_jin_all + $bkan_kei_all;
        $bhan_sum = $bhan_jin_all + $bhan_kei_all;
        $view_bkan_sum = number_format(($bkan_sum / $tani), $keta);
        $view_bhan_sum = number_format(($bhan_sum / $tani), $keta);
        ///// 
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

    //// ����ǡ�������Ͽ
if (isset($_POST['input_data'])) {
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
    ///////// �ƥǡ������ݴ�
    ///// ɽ���ǡ������饫��ޤ�������
    ///// $number = str_replace(',','',$english_format_number);
    for ($i = 0; $i < 6; $i++) {
        if ($i == 2 || $i == 5) {
            if ($i == 2) {          // ������¤����
                $input_data = array();
                $input_data[0]   = str_replace(',','',$view_bkan_jin[0]);   // �����
                $input_data[1]   = str_replace(',','',$view_bkan_jin[1]);   // ��������
                $input_data[2]   = str_replace(',','',$view_bkan_jin[2]);   // ��Ϳ����
                $input_data[3]   = str_replace(',','',$view_bkan_jin[3]);   // ������
                $input_data[4]   = str_replace(',','',$view_bkan_jin[4]);   // ˡ��ʡ��
                $input_data[5]   = str_replace(',','',$view_bkan_jin[5]);   // ����ʡ����
                $input_data[6]   = str_replace(',','',$view_bkan_jin[6]);   // ��Ϳ�����ⷫ��
                $input_data[7]   = str_replace(',','',$view_bkan_jin[7]);   // �࿦��������
                $input_data[8]   = str_replace(',','',$view_bkan_jin_all);   // �ͷ����
                $input_data[9]   = str_replace(',','',$view_bkan_kei[8]);   // ι�������
                $input_data[10]  = str_replace(',','',$view_bkan_kei[9]);   // ������ĥ
                $input_data[11]  = str_replace(',','',$view_bkan_kei[10]);   // �̿���
                $input_data[12]  = str_replace(',','',$view_bkan_kei[11]);   // �����
                $input_data[13]  = str_replace(',','',$view_bkan_kei[12]);   // ���������
                $input_data[14]  = str_replace(',','',$view_bkan_kei[13]);   // ����������
                $input_data[15]  = str_replace(',','',$view_bkan_kei[14]);   // �����
                $input_data[16]  = str_replace(',','',$view_bkan_kei[15]);   // ���²�¤��
                $input_data[17]  = str_replace(',','',$view_bkan_kei[16]);   // �޽񶵰���
                $input_data[18]  = str_replace(',','',$view_bkan_kei[17]);   // ��̳������
                $input_data[19]  = str_replace(',','',$view_bkan_kei[35]);   // ������
                $input_data[20]  = str_replace(',','',$view_bkan_kei[18]);   // ���Ǹ���
                $input_data[21]  = str_replace(',','',$view_bkan_kei[19]);   // �������
                $input_data[22]  = str_replace(',','',$view_bkan_kei[20]);   // ����
                $input_data[23]  = str_replace(',','',$view_bkan_kei[21]);   // ������
                $input_data[24]  = str_replace(',','',$view_bkan_kei[22]);   // �ݾڽ�����
                $input_data[25]  = str_replace(',','',$view_bkan_kei[23]);   // ��̳�Ѿ�������
                $input_data[26]  = str_replace(',','',$view_bkan_kei[24]);   // �����������
                $input_data[27]  = str_replace(',','',$view_bkan_kei[25]);   // ��ξ��
                $input_data[28]  = str_replace(',','',$view_bkan_kei[26]);   // �ݸ���
                $input_data[29]  = str_replace(',','',$view_bkan_kei[27]);   // ��ƻ��Ǯ��
                $input_data[30]  = str_replace(',','',$view_bkan_kei[28]);   // ������
                $input_data[31]  = str_replace(',','',$view_bkan_kei[29]);   // ��ʧ�����
                $input_data[32]  = str_replace(',','',$view_bkan_kei[30]);   // �������
                $input_data[33]  = str_replace(',','',$view_bkan_kei[31]);   // ���ն�
                $input_data[34]  = str_replace(',','',$view_bkan_kei[32]);   // ������
                $input_data[35]  = str_replace(',','',$view_bkan_kei[33]);   // �¼���
                $input_data[36]  = str_replace(',','',$view_bkan_kei[34]);   // ����������
                $input_data[37]  = str_replace(',','',$view_bkan_kei[36]);   // ���졼���б���
                $input_data[38]  = str_replace(',','',$view_bkan_kei_all);   // �����
                $input_data[39]  = str_replace(',','',$view_bkan_sum);   // ���
                
                $head  = "������¤����";
                
            } elseif ($i == 5) {        // �����δ���
                $input_data = array();
                $input_data[0]   = str_replace(',','',$view_bhan_jin[0]);   // �����
                $input_data[1]   = str_replace(',','',$view_bhan_jin[1]);   // ��������
                $input_data[2]   = str_replace(',','',$view_bhan_jin[2]);   // ��Ϳ����
                $input_data[3]   = str_replace(',','',$view_bhan_jin[3]);   // ������
                $input_data[4]   = str_replace(',','',$view_bhan_jin[4]);   // ˡ��ʡ��
                $input_data[5]   = str_replace(',','',$view_bhan_jin[5]);   // ����ʡ����
                $input_data[6]   = str_replace(',','',$view_bhan_jin[6]);   // ��Ϳ�����ⷫ��
                $input_data[7]   = str_replace(',','',$view_bhan_jin[7]);   // �࿦��������
                $input_data[8]   = str_replace(',','',$view_bhan_jin_all);   // �ͷ����
                $input_data[9]   = str_replace(',','',$view_bhan_kei[8]);   // ι�������
                $input_data[10]  = str_replace(',','',$view_bhan_kei[9]);   // ������ĥ
                $input_data[11]  = str_replace(',','',$view_bhan_kei[10]);   // �̿���
                $input_data[12]  = str_replace(',','',$view_bhan_kei[11]);   // �����
                $input_data[13]  = str_replace(',','',$view_bhan_kei[12]);   // ���������
                $input_data[14]  = str_replace(',','',$view_bhan_kei[13]);   // ����������
                $input_data[15]  = str_replace(',','',$view_bhan_kei[14]);   // �����
                $input_data[16]  = str_replace(',','',$view_bhan_kei[15]);   // ���²�¤��
                $input_data[17]  = str_replace(',','',$view_bhan_kei[16]);   // �޽񶵰���
                $input_data[18]  = str_replace(',','',$view_bhan_kei[17]);   // ��̳������
                $input_data[19]  = str_replace(',','',$view_bhan_kei[35]);   // ������
                $input_data[20]  = str_replace(',','',$view_bhan_kei[18]);   // ���Ǹ���
                $input_data[21]  = str_replace(',','',$view_bhan_kei[19]);   // �������
                $input_data[22]  = str_replace(',','',$view_bhan_kei[20]);   // ����
                $input_data[23]  = str_replace(',','',$view_bhan_kei[21]);   // ������
                $input_data[24]  = str_replace(',','',$view_bhan_kei[22]);   // �ݾڽ�����
                $input_data[25]  = str_replace(',','',$view_bhan_kei[23]);   // ��̳�Ѿ�������
                $input_data[26]  = str_replace(',','',$view_bhan_kei[24]);   // �����������
                $input_data[27]  = str_replace(',','',$view_bhan_kei[25]);   // ��ξ��
                $input_data[28]  = str_replace(',','',$view_bhan_kei[26]);   // �ݸ���
                $input_data[29]  = str_replace(',','',$view_bhan_kei[27]);   // ��ƻ��Ǯ��
                $input_data[30]  = str_replace(',','',$view_bhan_kei[28]);   // ������
                $input_data[31]  = str_replace(',','',$view_bhan_kei[29]);   // ��ʧ�����
                $input_data[32]  = str_replace(',','',$view_bhan_kei[30]);   // �������
                $input_data[33]  = str_replace(',','',$view_bhan_kei[31]);   // ���ն�
                $input_data[34]  = str_replace(',','',$view_bhan_kei[32]);   // ������
                $input_data[35]  = str_replace(',','',$view_bhan_kei[33]);   // �¼���
                $input_data[36]  = str_replace(',','',$view_bhan_kei[34]);   // ����������
                $input_data[37]  = str_replace(',','',$view_bhan_kei[36]);   // ���졼���б���
                $input_data[38]  = str_replace(',','',$view_bhan_kei_all);   // �����
                $input_data[39]  = str_replace(',','',$view_bhan_sum);   // ���
                
                $head  = "�����δ���";
                
            }
        } else {
            if ($i == 0) {
                $c = 0;             // ���ץ���¤����
            } elseif ($i == 1) {
                $c = 1;             // ��˥���¤����
            } elseif ($i == 3) {
                $c = 10;            // ���ץ��δ���
            } elseif ($i == 4) {
                $c = 11;            // ��˥��δ���
            }
            $input_data = array();
            $input_data[0]   = str_replace(',','',$view_data[0][$c]);   // �����
            $input_data[1]   = str_replace(',','',$view_data[1][$c]);   // ��������
            $input_data[2]   = str_replace(',','',$view_data[2][$c]);   // ��Ϳ����
            $input_data[3]   = str_replace(',','',$view_data[3][$c]);   // ������
            $input_data[4]   = str_replace(',','',$view_data[4][$c]);   // ˡ��ʡ��
            $input_data[5]   = str_replace(',','',$view_data[5][$c]);   // ����ʡ����
            $input_data[6]   = str_replace(',','',$view_data[6][$c]);   // ��Ϳ�����ⷫ��
            $input_data[7]   = str_replace(',','',$view_data[7][$c]);   // �࿦��������
            $input_data[8]   = str_replace(',','',$view_jin_sum[$c]);   // �ͷ����
            $input_data[9]   = str_replace(',','',$view_data[8][$c]);   // ι�������
            $input_data[10]  = str_replace(',','',$view_data[9][$c]);   // ������ĥ
            $input_data[11]  = str_replace(',','',$view_data[10][$c]);   // �̿���
            $input_data[12]  = str_replace(',','',$view_data[11][$c]);   // �����
            $input_data[13]  = str_replace(',','',$view_data[12][$c]);   // ���������
            $input_data[14]  = str_replace(',','',$view_data[13][$c]);   // ����������
            $input_data[15]  = str_replace(',','',$view_data[14][$c]);   // �����
            $input_data[16]  = str_replace(',','',$view_data[15][$c]);   // ���²�¤��
            $input_data[17]  = str_replace(',','',$view_data[16][$c]);   // �޽񶵰���
            $input_data[18]  = str_replace(',','',$view_data[17][$c]);   // ��̳������
            $input_data[19]  = str_replace(',','',$view_data[35][$c]);   // ������
            $input_data[20]  = str_replace(',','',$view_data[18][$c]);   // ���Ǹ���
            $input_data[21]  = str_replace(',','',$view_data[19][$c]);   // �������
            $input_data[22]  = str_replace(',','',$view_data[20][$c]);   // ����
            $input_data[23]  = str_replace(',','',$view_data[21][$c]);   // ������
            $input_data[24]  = str_replace(',','',$view_data[22][$c]);   // �ݾڽ�����
            $input_data[25]  = str_replace(',','',$view_data[23][$c]);   // ��̳�Ѿ�������
            $input_data[26]  = str_replace(',','',$view_data[24][$c]);   // �����������
            $input_data[27]  = str_replace(',','',$view_data[25][$c]);   // ��ξ��
            $input_data[28]  = str_replace(',','',$view_data[26][$c]);   // �ݸ���
            $input_data[29]  = str_replace(',','',$view_data[27][$c]);   // ��ƻ��Ǯ��
            $input_data[30]  = str_replace(',','',$view_data[28][$c]);   // ������
            $input_data[31]  = str_replace(',','',$view_data[29][$c]);   // ��ʧ�����
            $input_data[32]  = str_replace(',','',$view_data[30][$c]);   // �������
            $input_data[33]  = str_replace(',','',$view_data[31][$c]);   // ���ն�
            $input_data[34]  = str_replace(',','',$view_data[32][$c]);   // ������
            $input_data[35]  = str_replace(',','',$view_data[33][$c]);   // �¼���
            $input_data[36]  = str_replace(',','',$view_data[34][$c]);   // ����������
            $input_data[37]  = str_replace(',','',$view_data[36][$c]);   // ���졼���б���
            $input_data[38]  = str_replace(',','',$view_kei_sum[$c]);   // �����
            $input_data[39]  = str_replace(',','',$view_all_sum[$c]);   // ���
            if ($i == 0) {
                $head  = "���ץ���¤����";    // ���ץ���¤����
            } elseif ($i == 1) {
                $head  = "��˥���¤����";    // ��˥���¤����
            } elseif ($i == 3) {
                $head  = "���ץ��δ���";      // ���ץ��δ���
            } elseif ($i == 4) {
                $head  = "��˥��δ���";      // ��˥��δ���
            }
        }
        insert_date($item,$head,$yyyymm,$input_data);
    }
}
function insert_date($item,$head,$yyyymm,$input_data) 
{
    for ($i = 0; $i < 40; $i++) {
        //$item_in     = array();
        //$item_in[$i] = $item[$i];
        //$input_data[$i][$sec] = str_replace(',','',$input_data[$i][$sec]);
        $item_in[$i] = $head . $item[$i];
        $query = sprintf("select kin from profit_loss_keihi_history where pl_bs_ym=%d and note='%s'", $yyyymm, $item_in[$i]);
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
            $query = sprintf("insert into profit_loss_keihi_history (pl_bs_ym, kin, note, last_date, last_user) values (%d, %d, '%s', CURRENT_TIMESTAMP, '%s')", $yyyymm, $input_data[$i], $item_in[$i], $_SESSION['User_ID']);
            if (query_affected_trans($con, $query) <= 0) {
                $_SESSION["s_sysmsg"] .= sprintf("%s�ο�����Ͽ�˼���<br> %d", $item_in[$i], $yyyymm);
                query_affected_trans($con, "rollback");     // transaction rollback
                exit();
            }
            /////////// commit �ȥ�󥶥������λ
            query_affected_trans($con, "commit");
            $_SESSION["s_sysmsg"] = sprintf("<font color='yellow'>%d �߼��о�ɽ�ǡ��� ���� ��Ͽ��λ</font>",$yyyymm);
        } else {
            /////////// begin �ȥ�󥶥�����󳫻�
            if ($con = db_connect()) {
                query_affected_trans($con, "begin");
            } else {
                $_SESSION["s_sysmsg"] .= "�ǡ����١�������³�Ǥ��ޤ���";
                exit();
            }
            ////////// UPDATE Start
            $query = sprintf("update profit_loss_keihi_history set kin=%d, last_date=CURRENT_TIMESTAMP, last_user='%s' where pl_bs_ym=%d and note='%s'", $input_data[$i], $_SESSION['User_ID'], $yyyymm, $item_in[$i]);
            if (query_affected_trans($con, $query) <= 0) {
                $_SESSION["s_sysmsg"] .= sprintf("%s��UPDATE�˼���<br> %d", $item_in[$i], $yyyymm);
                query_affected_trans($con, "rollback");     // transaction rollback
                exit();
            }
            /////////// commit �ȥ�󥶥������λ
            query_affected_trans($con, "commit");
            $_SESSION["s_sysmsg"] = sprintf("<font color='yellow'>%d �߼��о�ɽ�ǡ��� �ѹ� ��λ</font>",$yyyymm);
        }
    }
    $_SESSION["s_sysmsg"] .= "����Υǡ�������Ͽ���ޤ�����";
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
                        <?php
                        if ($_SESSION['User_ID'] == '300144') {
                            if ($keta == 0 && $tani == 1) {
                        ?>
                            &nbsp;
                            <input class='pt10b' type='submit' name='input_data' value='����ǡ�����Ͽ' onClick='return data_input_click(this)'>
                        <?php
                            } else {
                        ?>
                            <input class='pt10b' type='submit' name='input_data' value='����ǡ�����Ͽ' onClick='return data_input_click(this)' disabled>
                        <?php
                            }
                        }
                        ?>
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
                    <td colspan='12' nowrap align='center' class='pt10b' bgcolor='#ffffc6'>������Ρ�����¤���С���</td>
                    <td colspan='4' rowspan='2' nowrap align='center' class='pt10b' bgcolor='#ffffc6'>������ڤӰ��̴�����</td>
                </tr>
                <tr>
                    <td colspan='4' nowrap align='center' class='pt10b' bgcolor='#ffffc6'>�硡������</td>
                    <td colspan='3' nowrap align='center' class='pt10b'>ľ�ܷ���</td>
                    <td colspan='4' nowrap align='center' class='pt10b'>���ܷ���</td>
                    <td rowspan='2' nowrap align='center' class='pt10b' bgcolor='#ffffc6'>�硡��</td>
                </tr>
                <tr>
                    <td nowrap align='center' class='pt10b' bgcolor='#ffffc6'>���ץ�</td>
                    <td nowrap align='center' class='pt10b' bgcolor='#ffffc6'>��˥�</td>
                    <td nowrap align='center' class='pt10b' bgcolor='#ffffc6'>������</td>
                    <td nowrap align='center' class='pt10b' bgcolor='#ffffc6'>�硡��</td>
                    <td nowrap align='center' class='pt10b'>���ץ�</td>
                    <td nowrap align='center' class='pt10b'>��˥�</td>
                    <td nowrap align='center' class='pt10b'>�硡��</td>
                    <td nowrap align='center' class='pt10b'>���ץ�</td>
                    <td nowrap align='center' class='pt10b'>��˥�</td>
                    <td nowrap align='center' class='pt10b'>������</td>
                    <td nowrap align='center' class='pt10b'>�硡��</td>
                    <td nowrap align='center' class='pt10b' bgcolor='#ceffce'>���ץ�</td>
                    <td nowrap align='center' class='pt10b' bgcolor='#ceffce'>��˥�</td>
                    <td nowrap align='center' class='pt10b' bgcolor='#ceffce'>������</td>
                    <td nowrap align='center' class='pt10b' bgcolor='#ceffce'>�硡��</td>
                </tr>
                <tr>
                    <td width='10' rowspan='4' align='center' class='pt10b' bgcolor='#ffffc6'>���</td>
                    <td nowrap class='pt10'>���ץ�</td>
                    <td nowrap class='pt10' align='right' bgcolor='#ffffc6'><?php echo $view_uriage_c ?></td>   <!-- ���ץ� -->
                    <td nowrap class='pt10' align='right' bgcolor='#ffffc6'>��</td>       <!-- ��˥� -->
                    <td nowrap class='pt10' align='right' bgcolor='#ffffc6'>��</td>       <!-- ��  �� -->
                    <td nowrap class='pt10' align='right' bgcolor='#ffffc6'><?php echo $view_uriage_c ?></td>   <!-- ��� -->
                    <td nowrap class='pt10' align='right'><?php echo $view_uriage_c ?></td>   <!-- ���ץ� -->
                    <td nowrap class='pt10' align='right'>��</td>       <!-- ��˥� -->
                    <td nowrap class='pt10' align='right'><?php echo $view_uriage_c ?></td>   <!-- ��� -->
                    <td nowrap class='pt10' align='right'>��</td>       <!-- ;�� ������ -->
                    <td nowrap class='pt10' align='right'>��</td>       <!-- ;�� ������ -->
                    <td nowrap class='pt10' align='right'>��</td>       <!-- ;�� ������ -->
                    <td nowrap class='pt10' align='right'>��</td>       <!-- ;�� ������ -->
                    <td nowrap class='pt10' align='right' bgcolor='#ffffc6'><?php echo $view_uriage_c ?></td>   <!-- ��� -->
                    <td nowrap class='pt10' align='right' bgcolor='#ceffce'>��</td>       <!-- ;�� �δ��� -->
                    <td nowrap class='pt10' align='right' bgcolor='#ceffce'>��</td>       <!-- ;�� �δ��� -->
                    <td nowrap class='pt10' align='right' bgcolor='#ceffce'>��</td>       <!-- ;�� �δ��� -->
                    <td nowrap class='pt10' align='right' bgcolor='#ceffce'>��</td>       <!-- ;�� �δ��� -->
                </tr>
                <tr>
                    <td nowrap class='pt10'>��˥�</td>
                    <td nowrap class='pt10' align='right' bgcolor='#ffffc6'>��</td>         <!-- ���ץ� -->
                    <td nowrap class='pt10' align='right' bgcolor='#ffffc6'><?php echo $view_uriage_l ?></td>     <!-- ��˥� -->
                    <td nowrap class='pt10' align='right' bgcolor='#ffffc6'>��</td>         <!-- ��  �� -->
                    <td nowrap class='pt10' align='right' bgcolor='#ffffc6'><?php echo $view_uriage_l ?></td>     <!-- ��� -->
                    <td nowrap class='pt10' align='right'>��</td>       <!-- ���ץ� -->
                    <td nowrap class='pt10' align='right'><?php echo $view_uriage_l ?></td>   <!-- ��˥� -->
                    <td nowrap class='pt10' align='right'><?php echo $view_uriage_l ?></td>   <!-- ��� -->
                    <td nowrap class='pt10' align='right'>��</td>       <!-- ;�� ������ -->
                    <td nowrap class='pt10' align='right'>��</td>       <!-- ;�� ������ -->
                    <td nowrap class='pt10' align='right'>��</td>       <!-- ;�� ������ -->
                    <td nowrap class='pt10' align='right'>��</td>       <!-- ;�� ������ -->
                    <td nowrap class='pt10' align='right' bgcolor='#ffffc6'><?php echo $view_uriage_l ?></td>   <!-- ��� -->
                    <td nowrap class='pt10' align='right' bgcolor='#ceffce'>��</td>       <!-- ;�� �δ��� -->
                    <td nowrap class='pt10' align='right' bgcolor='#ceffce'>��</td>       <!-- ;�� �δ��� -->
                    <td nowrap class='pt10' align='right' bgcolor='#ceffce'>��</td>       <!-- ;�� �δ��� -->
                    <td nowrap class='pt10' align='right' bgcolor='#ceffce'>��</td>       <!-- ;�� �δ��� -->
                </tr>
                <tr>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'>�����</td>
                    <td nowrap class='pt10' align='right' bgcolor='#ffffc6'>��</td>     <!-- ���ץ� -->
                    <td nowrap class='pt10' align='right' bgcolor='#ffffc6'>��</td>     <!-- ��˥� -->
                    <td nowrap class='pt10' align='right' bgcolor='#ffffc6'>��</td>     <!-- ��  �� -->
                    <td nowrap class='pt10' align='right' bgcolor='#ffffc6'>��</td>     <!-- ��� -->
                    <td nowrap class='pt10' align='right' bgcolor='#e6e6e6'><?php echo $view_ritu_c ?></td>   <!-- ���ץ� -->
                    <td nowrap class='pt10' align='right' bgcolor='#e6e6e6'><?php echo $view_ritu_l ?></td>   <!-- ��˥� -->
                    <td nowrap class='pt10' align='right' bgcolor='#e6e6e6'><?php echo $view_ritu ?>  </td>   <!-- ��� -->
                    <td nowrap class='pt10' align='right'>��</td>       <!-- ;�� ������ -->
                    <td nowrap class='pt10' align='right'>��</td>       <!-- ;�� ������ -->
                    <td nowrap class='pt10' align='right'>��</td>       <!-- ;�� ������ -->
                    <td nowrap class='pt10' align='right'>��</td>       <!-- ;�� ������ -->
                    <td nowrap class='pt10' align='right' bgcolor='#ffffc6'>��</td>   <!-- ��� -->
                    <td nowrap class='pt10' align='right' bgcolor='#ceffce'>��</td>       <!-- ;�� �δ��� -->
                    <td nowrap class='pt10' align='right' bgcolor='#ceffce'>��</td>       <!-- ;�� �δ��� -->
                    <td nowrap class='pt10' align='right' bgcolor='#ceffce'>��</td>       <!-- ;�� �δ��� -->
                    <td nowrap class='pt10' align='right' bgcolor='#ceffce'>��</td>       <!-- ;�� �δ��� -->
                </tr>
                <tr>
                    <td nowrap align='right' class='pt10b' bgcolor='#ffffc6'>����</td>
                    <td nowrap class='pt10b' align='right' bgcolor='#ffffc6'><?php echo $view_uriage_c ?></td>     <!-- ���ץ� -->
                    <td nowrap class='pt10b' align='right' bgcolor='#ffffc6'><?php echo $view_uriage_l ?></td>     <!-- ��˥� -->
                    <td nowrap class='pt10' align='right' bgcolor='#ffffc6'>��</td>       <!-- ��  �� -->
                    <td nowrap class='pt10b' align='right' bgcolor='#ffffc6'><?php echo $view_uriage ?></td>     <!-- ��� -->
                    <td nowrap class='pt10b' align='right' bgcolor='#ffffc6'><?php echo $view_uriage_c ?></td>   <!-- ���ץ� -->
                    <td nowrap class='pt10b' align='right' bgcolor='#ffffc6'><?php echo $view_uriage_l ?></td>   <!-- ��˥� -->
                    <td nowrap class='pt10b' align='right' bgcolor='#ffffc6'><?php echo $view_uriage ?></td>   <!-- ��� -->
                    <td nowrap class='pt10' align='right' bgcolor='#ffffc6'>��</td>       <!-- ;�� ������ -->
                    <td nowrap class='pt10' align='right' bgcolor='#ffffc6'>��</td>       <!-- ;�� ������ -->
                    <td nowrap class='pt10' align='right' bgcolor='#ffffc6'>��</td>       <!-- ;�� ������ -->
                    <td nowrap class='pt10' align='right' bgcolor='#ffffc6'>��</td>       <!-- ;�� ������ -->
                    <td nowrap class='pt10b' align='right' bgcolor='#ffffc6'><?php echo $view_uriage ?></td>   <!-- ��� -->
                    <td nowrap class='pt10' align='right' bgcolor='#ffffc6'>��</td>       <!-- ;�� �δ��� -->
                    <td nowrap class='pt10' align='right' bgcolor='#ffffc6'>��</td>       <!-- ;�� �δ��� -->
                    <td nowrap class='pt10' align='right' bgcolor='#ffffc6'>��</td>       <!-- ;�� �δ��� -->
                    <td nowrap class='pt10' align='right' bgcolor='#ffffc6'>��</td>       <!-- ;�� �δ��� -->
                </tr>
                <tr>
                    <td width='10' rowspan='4' align='center' class='pt10b' bgcolor='#ffffc6'>����</td>
                    <td nowrap class='pt10'>��������</td>
                    <td nowrap class='pt10' align='right' bgcolor='#ffffc6'>��</td>   <!-- ���ץ� -->
                    <td nowrap class='pt10' align='right' bgcolor='#ffffc6'>��</td>   <!-- ��˥� -->
                    <td nowrap class='pt10' align='right' bgcolor='#ffffc6'>��</td>   <!-- ��  �� -->
                    <td nowrap class='pt10' align='right' bgcolor='#ffffc6'>��</td>   <!-- ��� -->
                    <td nowrap class='pt10' align='right'><?php echo $view_shiire_c ?></td>   <!-- ���ץ� -->
                    <td nowrap class='pt10' align='right'><?php echo $view_shiire_l ?></td>   <!-- ��˥� -->
                    <td nowrap class='pt10' align='right'><?php echo $view_shiire ?></td>   <!-- ��� -->
                    <td nowrap class='pt10' align='right'>��</td>       <!-- ;�� ������ -->
                    <td nowrap class='pt10' align='right'>��</td>       <!-- ;�� ������ -->
                    <td nowrap class='pt10' align='right'>��</td>       <!-- ;�� ������ -->
                    <td nowrap class='pt10' align='right'>��</td>       <!-- ;�� ������ -->
                    <td nowrap class='pt10' align='right' bgcolor='#ffffc6'><?php echo $view_shiire ?></td>   <!-- ��� -->
                    <td nowrap class='pt10' align='right' bgcolor='#ceffce'>��</td>       <!-- ;�� �δ��� -->
                    <td nowrap class='pt10' align='right' bgcolor='#ceffce'>��</td>       <!-- ;�� �δ��� -->
                    <td nowrap class='pt10' align='right' bgcolor='#ceffce'>��</td>       <!-- ;�� �δ��� -->
                    <td nowrap class='pt10' align='right' bgcolor='#ceffce'>��</td>       <!-- ;�� �δ��� -->
                </tr>
                <tr>
                    <td nowrap class='pt10'>��¤��������</td>
                    <td nowrap class='pt10' align='right' bgcolor='#ffffc6'><?php echo $view_material_c ?></td>   <!-- ���ץ� -->
                    <td nowrap class='pt10' align='right' bgcolor='#ffffc6'><?php echo $view_material_l ?></td>   <!-- ��˥� -->
                    <td nowrap class='pt10' align='right' bgcolor='#ffffc6'>��</td>       <!-- ��  �� -->
                    <td nowrap class='pt10' align='right' bgcolor='#ffffc6'><?php echo $view_material ?></td>   <!-- ��� -->
                    <td nowrap class='pt10' align='right'>��</td>   <!-- ���ץ� -->
                    <td nowrap class='pt10' align='right'>��</td>   <!-- ��˥� -->
                    <td nowrap class='pt10' align='right'>��</td>   <!-- ��� -->
                    <td nowrap class='pt10' align='right'>��</td>       <!-- ;�� ������ -->
                    <td nowrap class='pt10' align='right'>��</td>       <!-- ;�� ������ -->
                    <td nowrap class='pt10' align='right'>��</td>       <!-- ;�� ������ -->
                    <td nowrap class='pt10' align='right'>��</td>       <!-- ;�� ������ -->
                    <td nowrap class='pt10' align='right' bgcolor='#ffffc6'><?php echo $view_material ?></td>   <!-- ��� -->
                    <td nowrap class='pt10' align='right' bgcolor='#ceffce'>��</td>       <!-- ;�� �δ��� -->
                    <td nowrap class='pt10' align='right' bgcolor='#ceffce'>��</td>       <!-- ;�� �δ��� -->
                    <td nowrap class='pt10' align='right' bgcolor='#ceffce'>��</td>       <!-- ;�� �δ��� -->
                    <td nowrap class='pt10' align='right' bgcolor='#ceffce'>��</td>       <!-- ;�� �δ��� -->
                </tr>
                <tr>
                    <td nowrap class='pt10' align='right' bgcolor='#e6e6e6'>������Ψ</td>
                    <td nowrap class='pt10' align='right' bgcolor='#ffffc6'>��</td>   <!-- ���ץ� -->
                    <td nowrap class='pt10' align='right' bgcolor='#ffffc6'>��</td>   <!-- ��˥� -->
                    <td nowrap class='pt10' align='right' bgcolor='#ffffc6'>��</td>   <!-- ��  �� -->
                    <td nowrap class='pt10' align='right' bgcolor='#ffffc6'>��</td>   <!-- ��� -->
                    <td nowrap class='pt10' align='right' bgcolor='#e6e6e6'><?php echo $view_shiire_ritu_c ?></td>   <!-- ���ץ� -->
                    <td nowrap class='pt10' align='right' bgcolor='#e6e6e6'><?php echo $view_shiire_ritu_l ?></td>   <!-- ��˥� -->
                    <td nowrap class='pt10' align='right' bgcolor='#e6e6e6'><?php echo $view_shiire_ritu ?></td>   <!-- ��� -->
                    <td nowrap class='pt10' align='right'>��</td>       <!-- ;�� ������ -->
                    <td nowrap class='pt10' align='right'>��</td>       <!-- ;�� ������ -->
                    <td nowrap class='pt10' align='right'>��</td>       <!-- ;�� ������ -->
                    <td nowrap class='pt10' align='right' bgcolor='#e6e6e6'>����</td>       <!-- ;�� ������ -->
                    <td nowrap class='pt10' align='right' bgcolor='#e6e6e6'><?php echo $view_barance ?></td>   <!-- ��� -->
                    <td nowrap class='pt10' align='right' bgcolor='#ceffce'>��</td>       <!-- ;�� �δ��� -->
                    <td nowrap class='pt10' align='right' bgcolor='#ceffce'>��</td>       <!-- ;�� �δ��� -->
                    <td nowrap class='pt10' align='right' bgcolor='#ceffce'>��</td>       <!-- ;�� �δ��� -->
                    <td nowrap class='pt10' align='right' bgcolor='#ceffce'>��</td>       <!-- ;�� �δ��� -->
                </tr>
                <tr>
                    <td nowrap class='pt10b' align='right' bgcolor='#ffffc6'>������</td>
                    <td nowrap class='pt10b' align='right' bgcolor='#ffffc6'><?php echo $view_material_c ?></td>   <!-- ���ץ� -->
                    <td nowrap class='pt10b' align='right' bgcolor='#ffffc6'><?php echo $view_material_l ?></td>   <!-- ��˥� -->
                    <td nowrap class='pt10' align='right' bgcolor='#ffffc6'>��</td>   <!-- ��  �� -->
                    <td nowrap class='pt10b' align='right' bgcolor='#ffffc6'><?php echo $view_material ?></td>   <!-- ��� -->
                    <td nowrap class='pt10' align='right' bgcolor='#ffffc6'>��</td>   <!-- ���ץ� -->
                    <td nowrap class='pt10' align='right' bgcolor='#ffffc6'>��</td>   <!-- ��˥� -->
                    <td nowrap class='pt10' align='right' bgcolor='#ffffc6'>��</td>   <!-- ��� -->
                    <td nowrap class='pt10' align='right' bgcolor='#ffffc6'>��</td>       <!-- ;�� ������ -->
                    <td nowrap class='pt10' align='right' bgcolor='#ffffc6'>��</td>       <!-- ;�� ������ -->
                    <td nowrap class='pt10' align='right' bgcolor='#ffffc6'>��</td>       <!-- ;�� ������ -->
                    <td nowrap class='pt10' align='right' bgcolor='#ffffc6'>��</td>       <!-- ;�� ������ -->
                    <td nowrap class='pt10b' align='right' bgcolor='#ffffc6'><?php echo $view_material ?></td>   <!-- ��� -->
                    <td nowrap class='pt10' align='right' bgcolor='#ffffc6'>��</td>       <!-- ;�� �δ��� -->
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
                            if ($c == 0 || $c == 1 || $c == 9) {  // ��¤���� ���ץ� ��˥� ���
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ffffc6'>%s</td>\n",$view_data[$r][$c]);
                            } elseif ($c == 2) {                                // ���ʴ���ʬ�������
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ffffc6'>%s</td>\n",$view_bkan_jin[$r]);
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ffffc6'>%s</td>\n",$view_data[$r][$c]);
                            } elseif ($c == 8) {                                // ���ʴ���ʬ�������
                                printf("<td nowrap class='pt10' align='right'>%s</td>\n",$view_bkan_jin[$r]);
                                printf("<td nowrap class='pt10' align='right'>%s</td>\n",$view_data[$r][$c]);
                            } elseif ($c == 10 || $c == 11) {      // �δ��� ���ץ� ��˥�
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ceffce'>%s</td>\n",$view_data[$r][$c]);
                            } elseif ($c == 12) {      // �δ��� ���ʴ��� ���
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ceffce'>%s</td>\n",$view_bhan_jin[$r]);
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ceffce'>%s</td>\n",$view_data[$r][$c]);
                            } else {
                                printf("<td nowrap class='pt10' align='right'>%s</td>\n",$view_data[$r][$c]);
                            }
                        }
                    ?>
                </tr>
                <TR>
                    <TD nowrap class='pt10'>��������</TD>
                    <?php
                        $r = 1;     // �����쥳����
                        for ($c=0;$c<$f_mei;$c++) {
                            if ($c == 0 || $c == 1 || $c == 9) {  // ��¤���� ���ץ� ��˥� ���
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ffffc6'>%s</td>\n",$view_data[$r][$c]);
                            } elseif ($c == 2) {                                // ���ʴ���ʬ�������
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ffffc6'>%s</td>\n",$view_bkan_jin[$r]);
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ffffc6'>%s</td>\n",$view_data[$r][$c]);
                            } elseif ($c == 8) {                                // ���ʴ���ʬ�������
                                printf("<td nowrap class='pt10' align='right'>%s</td>\n",$view_bkan_jin[$r]);
                                printf("<td nowrap class='pt10' align='right'>%s</td>\n",$view_data[$r][$c]);
                            } elseif ($c == 10 || $c == 11) {      // �δ��� ���ץ� ��˥�
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ceffce'>%s</td>\n",$view_data[$r][$c]);
                            } elseif ($c == 12) {      // �δ��� ���ʴ��� ���
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ceffce'>%s</td>\n",$view_bhan_jin[$r]);
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ceffce'>%s</td>\n",$view_data[$r][$c]);
                            } else {
                                printf("<td nowrap class='pt10' align='right'>%s</td>\n",$view_data[$r][$c]);
                            }
                        }
                    ?>
                </TR>
                <TR>
                    <TD nowrap class='pt10'>��Ϳ����</TD>
                    <?php
                        $r = 2;     // �����쥳����
                        for ($c=0;$c<$f_mei;$c++) {
                            if ($c == 0 || $c == 1 || $c == 9) {  // ��¤���� ���ץ� ��˥� ���
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ffffc6'>%s</td>\n",$view_data[$r][$c]);
                            } elseif ($c == 2) {                                // ���ʴ���ʬ�������
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ffffc6'>%s</td>\n",$view_bkan_jin[$r]);
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ffffc6'>%s</td>\n",$view_data[$r][$c]);
                            } elseif ($c == 8) {                                // ���ʴ���ʬ�������
                                printf("<td nowrap class='pt10' align='right'>%s</td>\n",$view_bkan_jin[$r]);
                                printf("<td nowrap class='pt10' align='right'>%s</td>\n",$view_data[$r][$c]);
                            } elseif ($c == 10 || $c == 11) {      // �δ��� ���ץ� ��˥�
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ceffce'>%s</td>\n",$view_data[$r][$c]);
                            } elseif ($c == 12) {      // �δ��� ���ʴ��� ���
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ceffce'>%s</td>\n",$view_bhan_jin[$r]);
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ceffce'>%s</td>\n",$view_data[$r][$c]);
                            } else {
                                printf("<td nowrap class='pt10' align='right'>%s</td>\n",$view_data[$r][$c]);
                            }
                        }
                    ?>
                </TR>
                <TR>
                    <TD nowrap class='pt10'>������</TD>
                    <?php
                        $r = 3;     // �����쥳����
                        for ($c=0;$c<$f_mei;$c++) {
                            if ($c == 0 || $c == 1 || $c == 9) {  // ��¤���� ���ץ� ��˥� ���
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ffffc6'>%s</td>\n",$view_data[$r][$c]);
                            } elseif ($c == 2) {                                // ���ʴ���ʬ�������
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ffffc6'>%s</td>\n",$view_bkan_jin[$r]);
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ffffc6'>%s</td>\n",$view_data[$r][$c]);
                            } elseif ($c == 8) {                                // ���ʴ���ʬ�������
                                printf("<td nowrap class='pt10' align='right'>%s</td>\n",$view_bkan_jin[$r]);
                                printf("<td nowrap class='pt10' align='right'>%s</td>\n",$view_data[$r][$c]);
                            } elseif ($c == 10 || $c == 11) {      // �δ��� ���ץ� ��˥�
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ceffce'>%s</td>\n",$view_data[$r][$c]);
                            } elseif ($c == 12) {      // �δ��� ���ʴ��� ���
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ceffce'>%s</td>\n",$view_bhan_jin[$r]);
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ceffce'>%s</td>\n",$view_data[$r][$c]);
                            } else {
                                printf("<td nowrap class='pt10' align='right'>%s</td>\n",$view_data[$r][$c]);
                            }
                        }
                    ?>
                </TR>
                <TR>
                    <TD nowrap class='pt10'>ˡ��ʡ����</TD>
                    <?php
                        $r = 4;     // �����쥳����
                        for ($c=0;$c<$f_mei;$c++) {
                            if ($c == 0 || $c == 1 || $c == 9) {  // ��¤���� ���ץ� ��˥� ���
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ffffc6'>%s</td>\n",$view_data[$r][$c]);
                            } elseif ($c == 2) {                                // ���ʴ���ʬ�������
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ffffc6'>%s</td>\n",$view_bkan_jin[$r]);
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ffffc6'>%s</td>\n",$view_data[$r][$c]);
                            } elseif ($c == 8) {                                // ���ʴ���ʬ�������
                                printf("<td nowrap class='pt10' align='right'>%s</td>\n",$view_bkan_jin[$r]);
                                printf("<td nowrap class='pt10' align='right'>%s</td>\n",$view_data[$r][$c]);
                            } elseif ($c == 10 || $c == 11) {      // �δ��� ���ץ� ��˥�
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ceffce'>%s</td>\n",$view_data[$r][$c]);
                            } elseif ($c == 12) {      // �δ��� ���ʴ��� ���
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ceffce'>%s</td>\n",$view_bhan_jin[$r]);
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ceffce'>%s</td>\n",$view_data[$r][$c]);
                            } else {
                                printf("<td nowrap class='pt10' align='right'>%s</td>\n",$view_data[$r][$c]);
                            }
                        }
                    ?>
                </TR>
                <TR>
                    <TD nowrap class='pt10'>����ʡ����</TD>
                    <?php
                        $r = 5;     // �����쥳����
                        for ($c=0;$c<$f_mei;$c++) {
                            if ($c == 0 || $c == 1 || $c == 9) {  // ��¤���� ���ץ� ��˥� ���
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ffffc6'>%s</td>\n",$view_data[$r][$c]);
                            } elseif ($c == 2) {                                // ���ʴ���ʬ�������
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ffffc6'>%s</td>\n",$view_bkan_jin[$r]);
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ffffc6'>%s</td>\n",$view_data[$r][$c]);
                            } elseif ($c == 8) {                                // ���ʴ���ʬ�������
                                printf("<td nowrap class='pt10' align='right'>%s</td>\n",$view_bkan_jin[$r]);
                                printf("<td nowrap class='pt10' align='right'>%s</td>\n",$view_data[$r][$c]);
                            } elseif ($c == 10 || $c == 11) {      // �δ��� ���ץ� ��˥�
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ceffce'>%s</td>\n",$view_data[$r][$c]);
                            } elseif ($c == 12) {      // �δ��� ���ʴ��� ���
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ceffce'>%s</td>\n",$view_bhan_jin[$r]);
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ceffce'>%s</td>\n",$view_data[$r][$c]);
                            } else {
                                printf("<td nowrap class='pt10' align='right'>%s</td>\n",$view_data[$r][$c]);
                            }
                        }
                    ?>
                </TR>
                <TR>
                    <TD nowrap class='pt10'>��Ϳ�����ⷫ��</TD>
                    <?php
                        $r = 6;     // �����쥳����
                        for ($c=0;$c<$f_mei;$c++) {
                            if ($c == 0 || $c == 1 || $c == 9) {  // ��¤���� ���ץ� ��˥� ���
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ffffc6'>%s</td>\n",$view_data[$r][$c]);
                            } elseif ($c == 2) {                                // ���ʴ���ʬ�������
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ffffc6'>%s</td>\n",$view_bkan_jin[$r]);
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ffffc6'>%s</td>\n",$view_data[$r][$c]);
                            } elseif ($c == 8) {                                // ���ʴ���ʬ�������
                                printf("<td nowrap class='pt10' align='right'>%s</td>\n",$view_bkan_jin[$r]);
                                printf("<td nowrap class='pt10' align='right'>%s</td>\n",$view_data[$r][$c]);
                            } elseif ($c == 10 || $c == 11) {      // �δ��� ���ץ� ��˥�
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ceffce'>%s</td>\n",$view_data[$r][$c]);
                            } elseif ($c == 12) {      // �δ��� ���ʴ��� ���
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ceffce'>%s</td>\n",$view_bhan_jin[$r]);
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ceffce'>%s</td>\n",$view_data[$r][$c]);
                            } else {
                                printf("<td nowrap class='pt10' align='right'>%s</td>\n",$view_data[$r][$c]);
                            }
                        }
                    ?>
                </TR>
                <TR>
                    <TD nowrap class='pt10'>�࿦��������</TD>
                    <?php
                        $r = 7;     // �����쥳����
                        for ($c=0;$c<$f_mei;$c++) {
                            if ($c == 0 || $c == 1 || $c == 9) {  // ��¤���� ���ץ� ��˥� ���
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ffffc6'>%s</td>\n",$view_data[$r][$c]);
                            } elseif ($c == 2) {                                // ���ʴ���ʬ�������
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ffffc6'>%s</td>\n",$view_bkan_jin[$r]);
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ffffc6'>%s</td>\n",$view_data[$r][$c]);
                            } elseif ($c == 8) {                                // ���ʴ���ʬ�������
                                printf("<td nowrap class='pt10' align='right'>%s</td>\n",$view_bkan_jin[$r]);
                                printf("<td nowrap class='pt10' align='right'>%s</td>\n",$view_data[$r][$c]);
                            } elseif ($c == 10 || $c == 11) {      // �δ��� ���ץ� ��˥�
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ceffce'>%s</td>\n",$view_data[$r][$c]);
                            } elseif ($c == 12) {      // �δ��� ���ʴ��� ���
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ceffce'>%s</td>\n",$view_bhan_jin[$r]);
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ceffce'>%s</td>\n",$view_data[$r][$c]);
                            } else {
                                printf("<td nowrap class='pt10' align='right'>%s</td>\n",$view_data[$r][$c]);
                            }
                        }
                    ?>
                </TR>
                <TR bgcolor='#ffffc6'>
                    <TD nowrap class='pt10b' align='right'>�ͷ����</TD>
                    <?php
                        for ($c=0;$c<$f_mei;$c++) {
                            if ($c == 2) {                                // ���ʴ���ʬ�������
                                printf("<td nowrap class='pt10b' align='right' bgcolor='#ffffc6'>%s</td>\n",$view_bkan_jin_all);
                                printf("<td nowrap class='pt10b' align='right' bgcolor='#ffffc6'>%s</td>\n",$view_jin_sum[$c]);
                            } elseif ($c == 8) {
                                printf("<td nowrap class='pt10b' align='right'>%s</td>\n",$view_bkan_jin_all);
                                printf("<td nowrap class='pt10b' align='right'>%s</td>\n",$view_jin_sum[$c]);
                            } elseif ($c == 12) {
                                printf("<td nowrap class='pt10b' align='right'>%s</td>\n",$view_bhan_jin_all);
                                printf("<td nowrap class='pt10b' align='right'>%s</td>\n",$view_jin_sum[$c]);
                            } else {
                                printf("<td nowrap class='pt10b' align='right'>%s</td>\n",$view_jin_sum[$c]);
                            }
                        }
                    ?>
                </TR>
                <tr>
                    <td width='10' rowspan='<?= $rec_kei+1 ?>' align='center' class='pt10b' bgcolor='#ffffc6'>����</td>
                    <TD nowrap class='pt10'>ι�������</TD>
                    <?php
                        $r = 8;     // �����쥳����
                        for ($c=0;$c<$f_mei;$c++) {
                            if ($c == 0 || $c == 1 || $c == 9) {  // ��¤���� ���ץ� ��˥� ���
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ffffc6'>%s</td>\n",$view_data[$r][$c]);
                            } elseif ($c == 2) {                                // ���ʴ���ʬ�������
                                    printf("<td nowrap class='pt10' align='right' bgcolor='#ffffc6'>%s</td>\n",$view_bkan_kei[$r]);
                                    printf("<td nowrap class='pt10' align='right' bgcolor='#ffffc6'>%s</td>\n",$view_data[$r][$c]);
                            } elseif ($c == 8) {                                // ���ʴ���ʬ�������
                                    printf("<td nowrap class='pt10' align='right'>%s</td>\n",$view_bkan_kei[$r]);
                                    printf("<td nowrap class='pt10' align='right'>%s</td>\n",$view_data[$r][$c]);
                            } elseif ($c == 10 || $c == 11) {      // �δ��� ���ץ� ��˥�
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ceffce'>%s</td>\n",$view_data[$r][$c]);
                            } elseif ($c == 12) {      // �δ��� ���ʴ��� ���
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ceffce'>%s</td>\n",$view_bhan_kei[$r]);
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ceffce'>%s</td>\n",$view_data[$r][$c]);
                            } else {
                                printf("<td nowrap class='pt10' align='right'>%s</td>\n",$view_data[$r][$c]);
                            }
                        }
                    ?>
                </tr>
                <tr>
                    <TD nowrap class='pt10'>������ĥ</TD>
                    <?php
                    $r = 9;     // �����쥳����
                        for ($c=0;$c<$f_mei;$c++) {
                            if ($c == 0 || $c == 1 || $c == 9) {  // ��¤���� ���ץ� ��˥� ���
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ffffc6'>%s</td>\n",$view_data[$r][$c]);
                            } elseif ($c == 2) {                                // ���ʴ���ʬ�������
                                    printf("<td nowrap class='pt10' align='right' bgcolor='#ffffc6'>%s</td>\n",$view_bkan_kei[$r]);
                                    printf("<td nowrap class='pt10' align='right' bgcolor='#ffffc6'>%s</td>\n",$view_data[$r][$c]);
                            } elseif ($c == 8) {                                // ���ʴ���ʬ�������
                                    printf("<td nowrap class='pt10' align='right'>%s</td>\n",$view_bkan_kei[$r]);
                                    printf("<td nowrap class='pt10' align='right'>%s</td>\n",$view_data[$r][$c]);
                            } elseif ($c == 10 || $c == 11) {      // �δ��� ���ץ� ��˥�
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ceffce'>%s</td>\n",$view_data[$r][$c]);
                            } elseif ($c == 12) {      // �δ��� ���ʴ��� ���
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ceffce'>%s</td>\n",$view_bhan_kei[$r]);
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ceffce'>%s</td>\n",$view_data[$r][$c]);
                            } else {
                                printf("<td nowrap class='pt10' align='right'>%s</td>\n",$view_data[$r][$c]);
                            }
                        }
                    ?>
                </tr>
                <tr>
                    <TD nowrap class='pt10'>�̡�������</TD>
                    <?php
                    $r = 10;     // �����쥳����
                        for ($c=0;$c<$f_mei;$c++) {
                            if ($c == 0 || $c == 1 || $c == 9) {  // ��¤���� ���ץ� ��˥� ���
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ffffc6'>%s</td>\n",$view_data[$r][$c]);
                            } elseif ($c == 2) {                                // ���ʴ���ʬ�������
                                    printf("<td nowrap class='pt10' align='right' bgcolor='#ffffc6'>%s</td>\n",$view_bkan_kei[$r]);
                                    printf("<td nowrap class='pt10' align='right' bgcolor='#ffffc6'>%s</td>\n",$view_data[$r][$c]);
                            } elseif ($c == 8) {                                // ���ʴ���ʬ�������
                                    printf("<td nowrap class='pt10' align='right'>%s</td>\n",$view_bkan_kei[$r]);
                                    printf("<td nowrap class='pt10' align='right'>%s</td>\n",$view_data[$r][$c]);
                            } elseif ($c == 10 || $c == 11) {      // �δ��� ���ץ� ��˥�
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ceffce'>%s</td>\n",$view_data[$r][$c]);
                            } elseif ($c == 12) {      // �δ��� ���ʴ��� ���
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ceffce'>%s</td>\n",$view_bhan_kei[$r]);
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ceffce'>%s</td>\n",$view_data[$r][$c]);
                            } else {
                                printf("<td nowrap class='pt10' align='right'>%s</td>\n",$view_data[$r][$c]);
                            }
                        }
                    ?>
                </tr>
                <tr>
                    <TD nowrap class='pt10'>�񡡵ġ���</TD>
                    <?php
                    $r = 11;     // �����쥳����
                        for ($c=0;$c<$f_mei;$c++) {
                            if ($c == 0 || $c == 1 || $c == 9) {  // ��¤���� ���ץ� ��˥� ���
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ffffc6'>%s</td>\n",$view_data[$r][$c]);
                            } elseif ($c == 2) {                                // ���ʴ���ʬ�������
                                    printf("<td nowrap class='pt10' align='right' bgcolor='#ffffc6'>%s</td>\n",$view_bkan_kei[$r]);
                                    printf("<td nowrap class='pt10' align='right' bgcolor='#ffffc6'>%s</td>\n",$view_data[$r][$c]);
                            } elseif ($c == 8) {                                // ���ʴ���ʬ�������
                                    printf("<td nowrap class='pt10' align='right'>%s</td>\n",$view_bkan_kei[$r]);
                                    printf("<td nowrap class='pt10' align='right'>%s</td>\n",$view_data[$r][$c]);
                            } elseif ($c == 10 || $c == 11) {      // �δ��� ���ץ� ��˥�
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ceffce'>%s</td>\n",$view_data[$r][$c]);
                            } elseif ($c == 12) {      // �δ��� ���ʴ��� ���
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ceffce'>%s</td>\n",$view_bhan_kei[$r]);
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ceffce'>%s</td>\n",$view_data[$r][$c]);
                            } else {
                                printf("<td nowrap class='pt10' align='right'>%s</td>\n",$view_data[$r][$c]);
                            }
                        }
                    ?>
                </tr>
                <tr>
                    <TD nowrap class='pt10'>���������</TD>
                    <?php
                    $r = 12;     // �����쥳����
                        for ($c=0;$c<$f_mei;$c++) {
                            if ($c == 0 || $c == 1 || $c == 9) {  // ��¤���� ���ץ� ��˥� ���
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ffffc6'>%s</td>\n",$view_data[$r][$c]);
                            } elseif ($c == 2) {                                // ���ʴ���ʬ�������
                                    printf("<td nowrap class='pt10' align='right' bgcolor='#ffffc6'>%s</td>\n",$view_bkan_kei[$r]);
                                    printf("<td nowrap class='pt10' align='right' bgcolor='#ffffc6'>%s</td>\n",$view_data[$r][$c]);
                            } elseif ($c == 8) {                                // ���ʴ���ʬ�������
                                    printf("<td nowrap class='pt10' align='right'>%s</td>\n",$view_bkan_kei[$r]);
                                    printf("<td nowrap class='pt10' align='right'>%s</td>\n",$view_data[$r][$c]);
                            } elseif ($c == 10 || $c == 11) {      // �δ��� ���ץ� ��˥�
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ceffce'>%s</td>\n",$view_data[$r][$c]);
                            } elseif ($c == 12) {      // �δ��� ���ʴ��� ���
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ceffce'>%s</td>\n",$view_bhan_kei[$r]);
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ceffce'>%s</td>\n",$view_data[$r][$c]);
                            } else {
                                printf("<td nowrap class='pt10' align='right'>%s</td>\n",$view_data[$r][$c]);
                            }
                        }
                    ?>
                </tr>
                <tr>
                    <TD nowrap class='pt10'>����������</TD>
                    <?php
                    $r = 13;     // �����쥳����
                        for ($c=0;$c<$f_mei;$c++) {
                            if ($c == 0 || $c == 1 || $c == 9) {  // ��¤���� ���ץ� ��˥� ���
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ffffc6'>%s</td>\n",$view_data[$r][$c]);
                            } elseif ($c == 2) {                                // ���ʴ���ʬ�������
                                    printf("<td nowrap class='pt10' align='right' bgcolor='#ffffc6'>%s</td>\n",$view_bkan_kei[$r]);
                                    printf("<td nowrap class='pt10' align='right' bgcolor='#ffffc6'>%s</td>\n",$view_data[$r][$c]);
                            } elseif ($c == 8) {                                // ���ʴ���ʬ�������
                                    printf("<td nowrap class='pt10' align='right'>%s</td>\n",$view_bkan_kei[$r]);
                                    printf("<td nowrap class='pt10' align='right'>%s</td>\n",$view_data[$r][$c]);
                            } elseif ($c == 10 || $c == 11) {      // �δ��� ���ץ� ��˥�
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ceffce'>%s</td>\n",$view_data[$r][$c]);
                            } elseif ($c == 12) {      // �δ��� ���ʴ��� ���
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ceffce'>%s</td>\n",$view_bhan_kei[$r]);
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ceffce'>%s</td>\n",$view_data[$r][$c]);
                            } else {
                                printf("<td nowrap class='pt10' align='right'>%s</td>\n",$view_data[$r][$c]);
                            }
                        }
                    ?>
                </tr>
                <tr>
                    <TD nowrap class='pt10'>�ᡡ�͡���</TD>
                    <?php
                    $r = 14;     // �����쥳����
                        for ($c=0;$c<$f_mei;$c++) {
                            if ($c == 0 || $c == 1 || $c == 9) {  // ��¤���� ���ץ� ��˥� ���
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ffffc6'>%s</td>\n",$view_data[$r][$c]);
                            } elseif ($c == 2) {                                // ���ʴ���ʬ�������
                                    printf("<td nowrap class='pt10' align='right' bgcolor='#ffffc6'>%s</td>\n",$view_bkan_kei[$r]);
                                    printf("<td nowrap class='pt10' align='right' bgcolor='#ffffc6'>%s</td>\n",$view_data[$r][$c]);
                            } elseif ($c == 8) {                                // ���ʴ���ʬ�������
                                    printf("<td nowrap class='pt10' align='right'>%s</td>\n",$view_bkan_kei[$r]);
                                    printf("<td nowrap class='pt10' align='right'>%s</td>\n",$view_data[$r][$c]);
                            } elseif ($c == 10 || $c == 11) {      // �δ��� ���ץ� ��˥�
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ceffce'>%s</td>\n",$view_data[$r][$c]);
                            } elseif ($c == 12) {      // �δ��� ���ʴ��� ���
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ceffce'>%s</td>\n",$view_bhan_kei[$r]);
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ceffce'>%s</td>\n",$view_data[$r][$c]);
                            } else {
                                printf("<td nowrap class='pt10' align='right'>%s</td>\n",$view_data[$r][$c]);
                            }
                        }
                    ?>
                </tr>
                <tr>
                    <TD nowrap class='pt10'>���²�¤��</TD>
                    <?php
                    $r = 15;     // �����쥳����
                        for ($c=0;$c<$f_mei;$c++) {
                            if ($c == 0 || $c == 1 || $c == 9) {  // ��¤���� ���ץ� ��˥� ���
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ffffc6'>%s</td>\n",$view_data[$r][$c]);
                            } elseif ($c == 2) {                                // ���ʴ���ʬ�������
                                    printf("<td nowrap class='pt10' align='right' bgcolor='#ffffc6'>%s</td>\n",$view_bkan_kei[$r]);
                                    printf("<td nowrap class='pt10' align='right' bgcolor='#ffffc6'>%s</td>\n",$view_data[$r][$c]);
                            } elseif ($c == 8) {                                // ���ʴ���ʬ�������
                                    printf("<td nowrap class='pt10' align='right'>%s</td>\n",$view_bkan_kei[$r]);
                                    printf("<td nowrap class='pt10' align='right'>%s</td>\n",$view_data[$r][$c]);
                            } elseif ($c == 10 || $c == 11) {      // �δ��� ���ץ� ��˥�
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ceffce'>%s</td>\n",$view_data[$r][$c]);
                            } elseif ($c == 12) {      // �δ��� ���ʴ��� ���
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ceffce'>%s</td>\n",$view_bhan_kei[$r]);
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ceffce'>%s</td>\n",$view_data[$r][$c]);
                            } else {
                                printf("<td nowrap class='pt10' align='right'>%s</td>\n",$view_data[$r][$c]);
                            }
                        }
                    ?>
                </tr>
                <tr>
                    <TD nowrap class='pt10'>�޽񶵰���</TD>
                    <?php
                    $r = 16;     // �����쥳����
                        for ($c=0;$c<$f_mei;$c++) {
                            if ($c == 0 || $c == 1 || $c == 9) {  // ��¤���� ���ץ� ��˥� ���
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ffffc6'>%s</td>\n",$view_data[$r][$c]);
                            } elseif ($c == 2) {                                // ���ʴ���ʬ�������
                                    printf("<td nowrap class='pt10' align='right' bgcolor='#ffffc6'>%s</td>\n",$view_bkan_kei[$r]);
                                    printf("<td nowrap class='pt10' align='right' bgcolor='#ffffc6'>%s</td>\n",$view_data[$r][$c]);
                            } elseif ($c == 8) {                                // ���ʴ���ʬ�������
                                    printf("<td nowrap class='pt10' align='right'>%s</td>\n",$view_bkan_kei[$r]);
                                    printf("<td nowrap class='pt10' align='right'>%s</td>\n",$view_data[$r][$c]);
                            } elseif ($c == 10 || $c == 11) {      // �δ��� ���ץ� ��˥�
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ceffce'>%s</td>\n",$view_data[$r][$c]);
                            } elseif ($c == 12) {      // �δ��� ���ʴ��� ���
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ceffce'>%s</td>\n",$view_bhan_kei[$r]);
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ceffce'>%s</td>\n",$view_data[$r][$c]);
                            } else {
                                printf("<td nowrap class='pt10' align='right'>%s</td>\n",$view_data[$r][$c]);
                            }
                        }
                    ?>
                </tr>
                <tr>
                    <TD nowrap class='pt10'>��̳������</TD>
                    <?php
                    $r = 17;     // �����쥳����
                        for ($c=0;$c<$f_mei;$c++) {
                            if ($c == 0 || $c == 1 || $c == 9) {  // ��¤���� ���ץ� ��˥� ���
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ffffc6'>%s</td>\n",$view_data[$r][$c]);
                            } elseif ($c == 2) {                                // ���ʴ���ʬ�������
                                    printf("<td nowrap class='pt10' align='right' bgcolor='#ffffc6'>%s</td>\n",$view_bkan_kei[$r]);
                                    printf("<td nowrap class='pt10' align='right' bgcolor='#ffffc6'>%s</td>\n",$view_data[$r][$c]);
                            } elseif ($c == 8) {                                // ���ʴ���ʬ�������
                                    printf("<td nowrap class='pt10' align='right'>%s</td>\n",$view_bkan_kei[$r]);
                                    printf("<td nowrap class='pt10' align='right'>%s</td>\n",$view_data[$r][$c]);
                            } elseif ($c == 10 || $c == 11) {      // �δ��� ���ץ� ��˥�
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ceffce'>%s</td>\n",$view_data[$r][$c]);
                            } elseif ($c == 12) {      // �δ��� ���ʴ��� ���
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ceffce'>%s</td>\n",$view_bhan_kei[$r]);
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ceffce'>%s</td>\n",$view_data[$r][$c]);
                            } else {
                                printf("<td nowrap class='pt10' align='right'>%s</td>\n",$view_data[$r][$c]);
                            }
                        }
                    ?>
                </tr>
                <tr>
                    <td nowrap class='pt10'>�����ȡ���</td>
                    <?php
                    $r = 35;     // �����쥳����
                        for ($c=0;$c<$f_mei;$c++) {
                            if ($c == 0 || $c == 1 || $c == 9) {  // ��¤���� ���ץ� ��˥� ���
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ffffc6'>%s</td>\n",$view_data[$r][$c]);
                            } elseif ($c == 2) {                                // ���ʴ���ʬ�������
                                    printf("<td nowrap class='pt10' align='right' bgcolor='#ffffc6'>%s</td>\n",$view_bkan_kei[$r]);
                                    printf("<td nowrap class='pt10' align='right' bgcolor='#ffffc6'>%s</td>\n",$view_data[$r][$c]);
                            } elseif ($c == 8) {                                // ���ʴ���ʬ�������
                                    printf("<td nowrap class='pt10' align='right'>%s</td>\n",$view_bkan_kei[$r]);
                                    printf("<td nowrap class='pt10' align='right'>%s</td>\n",$view_data[$r][$c]);
                            } elseif ($c == 10 || $c == 11) {      // �δ��� ���ץ� ��˥�
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ceffce'>%s</td>\n",$view_data[$r][$c]);
                            } elseif ($c == 12) {      // �δ��� ���ʴ��� ���
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ceffce'>%s</td>\n",$view_bhan_kei[$r]);
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ceffce'>%s</td>\n",$view_data[$r][$c]);
                            } else {
                                printf("<td nowrap class='pt10' align='right'>%s</td>\n",$view_data[$r][$c]);
                            }
                        }
                    ?>
                </tr>
                <tr>
                    <TD nowrap class='pt10'>���Ǹ���</TD>
                    <?php
                    $r = 18;     // �����쥳����
                        for ($c=0;$c<$f_mei;$c++) {
                            if ($c == 0 || $c == 1 || $c == 9) {  // ��¤���� ���ץ� ��˥� ���
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ffffc6'>%s</td>\n",$view_data[$r][$c]);
                            } elseif ($c == 2) {                                // ���ʴ���ʬ�������
                                    printf("<td nowrap class='pt10' align='right' bgcolor='#ffffc6'>%s</td>\n",$view_bkan_kei[$r]);
                                    printf("<td nowrap class='pt10' align='right' bgcolor='#ffffc6'>%s</td>\n",$view_data[$r][$c]);
                            } elseif ($c == 8) {                                // ���ʴ���ʬ�������
                                    printf("<td nowrap class='pt10' align='right'>%s</td>\n",$view_bkan_kei[$r]);
                                    printf("<td nowrap class='pt10' align='right'>%s</td>\n",$view_data[$r][$c]);
                            } elseif ($c == 10 || $c == 11) {      // �δ��� ���ץ� ��˥�
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ceffce'>%s</td>\n",$view_data[$r][$c]);
                            } elseif ($c == 12) {      // �δ��� ���ʴ��� ���
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ceffce'>%s</td>\n",$view_bhan_kei[$r]);
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ceffce'>%s</td>\n",$view_data[$r][$c]);
                            } else {
                                printf("<td nowrap class='pt10' align='right'>%s</td>\n",$view_data[$r][$c]);
                            }
                        }
                    ?>
                </tr>
                <tr>
                    <TD nowrap class='pt10'>�������</TD>
                    <?php
                    $r = 19;     // �����쥳����
                        for ($c=0;$c<$f_mei;$c++) {
                            if ($c == 0 || $c == 1 || $c == 9) {  // ��¤���� ���ץ� ��˥� ���
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ffffc6'>%s</td>\n",$view_data[$r][$c]);
                            } elseif ($c == 2) {                                // ���ʴ���ʬ�������
                                    printf("<td nowrap class='pt10' align='right' bgcolor='#ffffc6'>%s</td>\n",$view_bkan_kei[$r]);
                                    printf("<td nowrap class='pt10' align='right' bgcolor='#ffffc6'>%s</td>\n",$view_data[$r][$c]);
                            } elseif ($c == 8) {                                // ���ʴ���ʬ�������
                                    printf("<td nowrap class='pt10' align='right'>%s</td>\n",$view_bkan_kei[$r]);
                                    printf("<td nowrap class='pt10' align='right'>%s</td>\n",$view_data[$r][$c]);
                            } elseif ($c == 10 || $c == 11) {      // �δ��� ���ץ� ��˥�
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ceffce'>%s</td>\n",$view_data[$r][$c]);
                            } elseif ($c == 12) {      // �δ��� ���ʴ��� ���
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ceffce'>%s</td>\n",$view_bhan_kei[$r]);
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ceffce'>%s</td>\n",$view_data[$r][$c]);
                            } else {
                                printf("<td nowrap class='pt10' align='right'>%s</td>\n",$view_data[$r][$c]);
                            }
                        }
                    ?>
                </tr>
                <tr>
                    <TD nowrap class='pt10'>����������</TD>
                    <?php
                    $r = 20;     // �����쥳����
                        for ($c=0;$c<$f_mei;$c++) {
                            if ($c == 0 || $c == 1 || $c == 9) {  // ��¤���� ���ץ� ��˥� ���
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ffffc6'>%s</td>\n",$view_data[$r][$c]);
                            } elseif ($c == 2) {                                // ���ʴ���ʬ�������
                                    printf("<td nowrap class='pt10' align='right' bgcolor='#ffffc6'>%s</td>\n",$view_bkan_kei[$r]);
                                    printf("<td nowrap class='pt10' align='right' bgcolor='#ffffc6'>%s</td>\n",$view_data[$r][$c]);
                            } elseif ($c == 8) {                                // ���ʴ���ʬ�������
                                    printf("<td nowrap class='pt10' align='right'>%s</td>\n",$view_bkan_kei[$r]);
                                    printf("<td nowrap class='pt10' align='right'>%s</td>\n",$view_data[$r][$c]);
                            } elseif ($c == 10 || $c == 11) {      // �δ��� ���ץ� ��˥�
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ceffce'>%s</td>\n",$view_data[$r][$c]);
                            } elseif ($c == 12) {      // �δ��� ���ʴ��� ���
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ceffce'>%s</td>\n",$view_bhan_kei[$r]);
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ceffce'>%s</td>\n",$view_data[$r][$c]);
                            } else {
                                printf("<td nowrap class='pt10' align='right'>%s</td>\n",$view_data[$r][$c]);
                            }
                        }
                    ?>
                </tr>
                <tr>
                    <TD nowrap class='pt10'>����������</TD>
                    <?php
                    $r = 21;     // �����쥳����
                        for ($c=0;$c<$f_mei;$c++) {
                            if ($c == 0 || $c == 1 || $c == 9) {  // ��¤���� ���ץ� ��˥� ���
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ffffc6'>%s</td>\n",$view_data[$r][$c]);
                            } elseif ($c == 2) {                                // ���ʴ���ʬ�������
                                    printf("<td nowrap class='pt10' align='right' bgcolor='#ffffc6'>%s</td>\n",$view_bkan_kei[$r]);
                                    printf("<td nowrap class='pt10' align='right' bgcolor='#ffffc6'>%s</td>\n",$view_data[$r][$c]);
                            } elseif ($c == 8) {                                // ���ʴ���ʬ�������
                                    printf("<td nowrap class='pt10' align='right'>%s</td>\n",$view_bkan_kei[$r]);
                                    printf("<td nowrap class='pt10' align='right'>%s</td>\n",$view_data[$r][$c]);
                            } elseif ($c == 10 || $c == 11) {      // �δ��� ���ץ� ��˥�
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ceffce'>%s</td>\n",$view_data[$r][$c]);
                            } elseif ($c == 12) {      // �δ��� ���ʴ��� ���
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ceffce'>%s</td>\n",$view_bhan_kei[$r]);
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ceffce'>%s</td>\n",$view_data[$r][$c]);
                            } else {
                                printf("<td nowrap class='pt10' align='right'>%s</td>\n",$view_data[$r][$c]);
                            }
                        }
                    ?>
                </tr>
                <tr>
                    <TD nowrap class='pt10'>�ݾڽ�����</TD>
                    <?php
                    $r = 22;     // �����쥳����
                        for ($c=0;$c<$f_mei;$c++) {
                            if ($c == 0 || $c == 1 || $c == 9) {  // ��¤���� ���ץ� ��˥� ���
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ffffc6'>%s</td>\n",$view_data[$r][$c]);
                            } elseif ($c == 2) {                                // ���ʴ���ʬ�������
                                    printf("<td nowrap class='pt10' align='right' bgcolor='#ffffc6'>%s</td>\n",$view_bkan_kei[$r]);
                                    printf("<td nowrap class='pt10' align='right' bgcolor='#ffffc6'>%s</td>\n",$view_data[$r][$c]);
                            } elseif ($c == 8) {                                // ���ʴ���ʬ�������
                                    printf("<td nowrap class='pt10' align='right'>%s</td>\n",$view_bkan_kei[$r]);
                                    printf("<td nowrap class='pt10' align='right'>%s</td>\n",$view_data[$r][$c]);
                            } elseif ($c == 10 || $c == 11) {      // �δ��� ���ץ� ��˥�
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ceffce'>%s</td>\n",$view_data[$r][$c]);
                            } elseif ($c == 12) {      // �δ��� ���ʴ��� ���
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ceffce'>%s</td>\n",$view_bhan_kei[$r]);
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ceffce'>%s</td>\n",$view_data[$r][$c]);
                            } else {
                                printf("<td nowrap class='pt10' align='right'>%s</td>\n",$view_data[$r][$c]);
                            }
                        }
                    ?>
                </tr>
                <tr>
                    <TD nowrap class='pt10'>��̳�Ѿ�������</TD>
                    <?php
                    $r = 23;     // �����쥳����
                        for ($c=0;$c<$f_mei;$c++) {
                            if ($c == 0 || $c == 1 || $c == 9) {  // ��¤���� ���ץ� ��˥� ���
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ffffc6'>%s</td>\n",$view_data[$r][$c]);
                            } elseif ($c == 2) {                                // ���ʴ���ʬ�������
                                    printf("<td nowrap class='pt10' align='right' bgcolor='#ffffc6'>%s</td>\n",$view_bkan_kei[$r]);
                                    printf("<td nowrap class='pt10' align='right' bgcolor='#ffffc6'>%s</td>\n",$view_data[$r][$c]);
                            } elseif ($c == 8) {                                // ���ʴ���ʬ�������
                                    printf("<td nowrap class='pt10' align='right'>%s</td>\n",$view_bkan_kei[$r]);
                                    printf("<td nowrap class='pt10' align='right'>%s</td>\n",$view_data[$r][$c]);
                            } elseif ($c == 10 || $c == 11) {      // �δ��� ���ץ� ��˥�
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ceffce'>%s</td>\n",$view_data[$r][$c]);
                            } elseif ($c == 12) {      // �δ��� ���ʴ��� ���
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ceffce'>%s</td>\n",$view_bhan_kei[$r]);
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ceffce'>%s</td>\n",$view_data[$r][$c]);
                            } else {
                                printf("<td nowrap class='pt10' align='right'>%s</td>\n",$view_data[$r][$c]);
                            }
                        }
                    ?>
                </tr>
                <tr>
                    <TD nowrap class='pt10'>�����������</TD>
                    <?php
                    $r = 24;     // �����쥳����
                        for ($c=0;$c<$f_mei;$c++) {
                            if ($c == 0 || $c == 1 || $c == 9) {  // ��¤���� ���ץ� ��˥� ���
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ffffc6'>%s</td>\n",$view_data[$r][$c]);
                            } elseif ($c == 2) {                                // ���ʴ���ʬ�������
                                    printf("<td nowrap class='pt10' align='right' bgcolor='#ffffc6'>%s</td>\n",$view_bkan_kei[$r]);
                                    printf("<td nowrap class='pt10' align='right' bgcolor='#ffffc6'>%s</td>\n",$view_data[$r][$c]);
                            } elseif ($c == 8) {                                // ���ʴ���ʬ�������
                                    printf("<td nowrap class='pt10' align='right'>%s</td>\n",$view_bkan_kei[$r]);
                                    printf("<td nowrap class='pt10' align='right'>%s</td>\n",$view_data[$r][$c]);
                            } elseif ($c == 10 || $c == 11) {      // �δ��� ���ץ� ��˥�
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ceffce'>%s</td>\n",$view_data[$r][$c]);
                            } elseif ($c == 12) {      // �δ��� ���ʴ��� ���
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ceffce'>%s</td>\n",$view_bhan_kei[$r]);
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ceffce'>%s</td>\n",$view_data[$r][$c]);
                            } else {
                                printf("<td nowrap class='pt10' align='right'>%s</td>\n",$view_data[$r][$c]);
                            }
                        }
                    ?>
                </tr>
                <tr>
                    <TD nowrap class='pt10'>�֡�ξ����</TD>
                    <?php
                    $r = 25;     // �����쥳����
                        for ($c=0;$c<$f_mei;$c++) {
                            if ($c == 0 || $c == 1 || $c == 9) {  // ��¤���� ���ץ� ��˥� ���
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ffffc6'>%s</td>\n",$view_data[$r][$c]);
                            } elseif ($c == 2) {                                // ���ʴ���ʬ�������
                                    printf("<td nowrap class='pt10' align='right' bgcolor='#ffffc6'>%s</td>\n",$view_bkan_kei[$r]);
                                    printf("<td nowrap class='pt10' align='right' bgcolor='#ffffc6'>%s</td>\n",$view_data[$r][$c]);
                            } elseif ($c == 8) {                                // ���ʴ���ʬ�������
                                    printf("<td nowrap class='pt10' align='right'>%s</td>\n",$view_bkan_kei[$r]);
                                    printf("<td nowrap class='pt10' align='right'>%s</td>\n",$view_data[$r][$c]);
                            } elseif ($c == 10 || $c == 11) {      // �δ��� ���ץ� ��˥�
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ceffce'>%s</td>\n",$view_data[$r][$c]);
                            } elseif ($c == 12) {      // �δ��� ���ʴ��� ���
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ceffce'>%s</td>\n",$view_bhan_kei[$r]);
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ceffce'>%s</td>\n",$view_data[$r][$c]);
                            } else {
                                printf("<td nowrap class='pt10' align='right'>%s</td>\n",$view_data[$r][$c]);
                            }
                        }
                    ?>
                </tr>
                <tr>
                    <TD nowrap class='pt10'>�ݡ�������</TD>
                    <?php
                    $r = 26;     // �����쥳����
                        for ($c=0;$c<$f_mei;$c++) {
                            if ($c == 0 || $c == 1 || $c == 9) {  // ��¤���� ���ץ� ��˥� ���
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ffffc6'>%s</td>\n",$view_data[$r][$c]);
                            } elseif ($c == 2) {                                // ���ʴ���ʬ�������
                                    printf("<td nowrap class='pt10' align='right' bgcolor='#ffffc6'>%s</td>\n",$view_bkan_kei[$r]);
                                    printf("<td nowrap class='pt10' align='right' bgcolor='#ffffc6'>%s</td>\n",$view_data[$r][$c]);
                            } elseif ($c == 8) {                                // ���ʴ���ʬ�������
                                    printf("<td nowrap class='pt10' align='right'>%s</td>\n",$view_bkan_kei[$r]);
                                    printf("<td nowrap class='pt10' align='right'>%s</td>\n",$view_data[$r][$c]);
                            } elseif ($c == 10 || $c == 11) {      // �δ��� ���ץ� ��˥�
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ceffce'>%s</td>\n",$view_data[$r][$c]);
                            } elseif ($c == 12) {      // �δ��� ���ʴ��� ���
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ceffce'>%s</td>\n",$view_bhan_kei[$r]);
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ceffce'>%s</td>\n",$view_data[$r][$c]);
                            } else {
                                printf("<td nowrap class='pt10' align='right'>%s</td>\n",$view_data[$r][$c]);
                            }
                        }
                    ?>
                </tr>
                <tr>
                    <TD nowrap class='pt10'>��ƻ��Ǯ��</TD>
                    <?php
                    $r = 27;     // �����쥳����
                        for ($c=0;$c<$f_mei;$c++) {
                            if ($c == 0 || $c == 1 || $c == 9) {  // ��¤���� ���ץ� ��˥� ���
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ffffc6'>%s</td>\n",$view_data[$r][$c]);
                            } elseif ($c == 2) {                                // ���ʴ���ʬ�������
                                    printf("<td nowrap class='pt10' align='right' bgcolor='#ffffc6'>%s</td>\n",$view_bkan_kei[$r]);
                                    printf("<td nowrap class='pt10' align='right' bgcolor='#ffffc6'>%s</td>\n",$view_data[$r][$c]);
                            } elseif ($c == 8) {                                // ���ʴ���ʬ�������
                                    printf("<td nowrap class='pt10' align='right'>%s</td>\n",$view_bkan_kei[$r]);
                                    printf("<td nowrap class='pt10' align='right'>%s</td>\n",$view_data[$r][$c]);
                            } elseif ($c == 10 || $c == 11) {      // �δ��� ���ץ� ��˥�
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ceffce'>%s</td>\n",$view_data[$r][$c]);
                            } elseif ($c == 12) {      // �δ��� ���ʴ��� ���
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ceffce'>%s</td>\n",$view_bhan_kei[$r]);
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ceffce'>%s</td>\n",$view_data[$r][$c]);
                            } else {
                                printf("<td nowrap class='pt10' align='right'>%s</td>\n",$view_data[$r][$c]);
                            }
                        }
                    ?>
                </tr>
                <tr>
                    <TD nowrap class='pt10'>��������</TD>
                    <?php
                    $r = 28;     // �����쥳����
                        for ($c=0;$c<$f_mei;$c++) {
                            if ($c == 0 || $c == 1 || $c == 9) {  // ��¤���� ���ץ� ��˥� ���
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ffffc6'>%s</td>\n",$view_data[$r][$c]);
                            } elseif ($c == 2) {                                // ���ʴ���ʬ�������
                                    printf("<td nowrap class='pt10' align='right' bgcolor='#ffffc6'>%s</td>\n",$view_bkan_kei[$r]);
                                    printf("<td nowrap class='pt10' align='right' bgcolor='#ffffc6'>%s</td>\n",$view_data[$r][$c]);
                            } elseif ($c == 8) {                                // ���ʴ���ʬ�������
                                    printf("<td nowrap class='pt10' align='right'>%s</td>\n",$view_bkan_kei[$r]);
                                    printf("<td nowrap class='pt10' align='right'>%s</td>\n",$view_data[$r][$c]);
                            } elseif ($c == 10 || $c == 11) {      // �δ��� ���ץ� ��˥�
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ceffce'>%s</td>\n",$view_data[$r][$c]);
                            } elseif ($c == 12) {      // �δ��� ���ʴ��� ���
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ceffce'>%s</td>\n",$view_bhan_kei[$r]);
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ceffce'>%s</td>\n",$view_data[$r][$c]);
                            } else {
                                printf("<td nowrap class='pt10' align='right'>%s</td>\n",$view_data[$r][$c]);
                            }
                        }
                    ?>
                </tr>
                <tr>
                    <TD nowrap class='pt10'>��ʧ�����</TD>
                    <?php
                    $r = 29;     // �����쥳����
                        for ($c=0;$c<$f_mei;$c++) {
                            if ($c == 0 || $c == 1 || $c == 9) {  // ��¤���� ���ץ� ��˥� ���
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ffffc6'>%s</td>\n",$view_data[$r][$c]);
                            } elseif ($c == 2) {                                // ���ʴ���ʬ�������
                                    printf("<td nowrap class='pt10' align='right' bgcolor='#ffffc6'>%s</td>\n",$view_bkan_kei[$r]);
                                    printf("<td nowrap class='pt10' align='right' bgcolor='#ffffc6'>%s</td>\n",$view_data[$r][$c]);
                            } elseif ($c == 8) {                                // ���ʴ���ʬ�������
                                    printf("<td nowrap class='pt10' align='right'>%s</td>\n",$view_bkan_kei[$r]);
                                    printf("<td nowrap class='pt10' align='right'>%s</td>\n",$view_data[$r][$c]);
                            } elseif ($c == 10 || $c == 11) {      // �δ��� ���ץ� ��˥�
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ceffce'>%s</td>\n",$view_data[$r][$c]);
                            } elseif ($c == 12) {      // �δ��� ���ʴ��� ���
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ceffce'>%s</td>\n",$view_bhan_kei[$r]);
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ceffce'>%s</td>\n",$view_data[$r][$c]);
                            } else {
                                printf("<td nowrap class='pt10' align='right'>%s</td>\n",$view_data[$r][$c]);
                            }
                        }
                    ?>
                </tr>
                <tr>
                    <TD nowrap class='pt10'>�������</TD>
                    <?php
                    $r = 30;     // �����쥳����
                        for ($c=0;$c<$f_mei;$c++) {
                            if ($c == 0 || $c == 1 || $c == 9) {  // ��¤���� ���ץ� ��˥� ���
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ffffc6'>%s</td>\n",$view_data[$r][$c]);
                            } elseif ($c == 2) {                                // ���ʴ���ʬ�������
                                    printf("<td nowrap class='pt10' align='right' bgcolor='#ffffc6'>%s</td>\n",$view_bkan_kei[$r]);
                                    printf("<td nowrap class='pt10' align='right' bgcolor='#ffffc6'>%s</td>\n",$view_data[$r][$c]);
                            } elseif ($c == 8) {                                // ���ʴ���ʬ�������
                                    printf("<td nowrap class='pt10' align='right'>%s</td>\n",$view_bkan_kei[$r]);
                                    printf("<td nowrap class='pt10' align='right'>%s</td>\n",$view_data[$r][$c]);
                            } elseif ($c == 10 || $c == 11) {      // �δ��� ���ץ� ��˥�
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ceffce'>%s</td>\n",$view_data[$r][$c]);
                            } elseif ($c == 12) {      // �δ��� ���ʴ��� ���
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ceffce'>%s</td>\n",$view_bhan_kei[$r]);
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ceffce'>%s</td>\n",$view_data[$r][$c]);
                            } else {
                                printf("<td nowrap class='pt10' align='right'>%s</td>\n",$view_data[$r][$c]);
                            }
                        }
                    ?>
                </tr>
                <tr>
                    <TD nowrap class='pt10'>���ա���</TD>
                    <?php
                    $r = 31;     // �����쥳����
                        for ($c=0;$c<$f_mei;$c++) {
                            if ($c == 0 || $c == 1 || $c == 9) {  // ��¤���� ���ץ� ��˥� ���
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ffffc6'>%s</td>\n",$view_data[$r][$c]);
                            } elseif ($c == 2) {                                // ���ʴ���ʬ�������
                                    printf("<td nowrap class='pt10' align='right' bgcolor='#ffffc6'>%s</td>\n",$view_bkan_kei[$r]);
                                    printf("<td nowrap class='pt10' align='right' bgcolor='#ffffc6'>%s</td>\n",$view_data[$r][$c]);
                            } elseif ($c == 8) {                                // ���ʴ���ʬ�������
                                    printf("<td nowrap class='pt10' align='right'>%s</td>\n",$view_bkan_kei[$r]);
                                    printf("<td nowrap class='pt10' align='right'>%s</td>\n",$view_data[$r][$c]);
                            } elseif ($c == 10 || $c == 11) {      // �δ��� ���ץ� ��˥�
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ceffce'>%s</td>\n",$view_data[$r][$c]);
                            } elseif ($c == 12) {      // �δ��� ���ʴ��� ���
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ceffce'>%s</td>\n",$view_bhan_kei[$r]);
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ceffce'>%s</td>\n",$view_data[$r][$c]);
                            } else {
                                printf("<td nowrap class='pt10' align='right'>%s</td>\n",$view_data[$r][$c]);
                            }
                        }
                    ?>
                </tr>
                <tr>
                    <TD nowrap class='pt10'>�ҡ��ߡ���</TD>
                    <?php
                    $r = 32;     // �����쥳����
                        for ($c=0;$c<$f_mei;$c++) {
                            if ($c == 0 || $c == 1 || $c == 9) {  // ��¤���� ���ץ� ��˥� ���
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ffffc6'>%s</td>\n",$view_data[$r][$c]);
                            } elseif ($c == 2) {                                // ���ʴ���ʬ�������
                                    printf("<td nowrap class='pt10' align='right' bgcolor='#ffffc6'>%s</td>\n",$view_bkan_kei[$r]);
                                    printf("<td nowrap class='pt10' align='right' bgcolor='#ffffc6'>%s</td>\n",$view_data[$r][$c]);
                            } elseif ($c == 8) {                                // ���ʴ���ʬ�������
                                    printf("<td nowrap class='pt10' align='right'>%s</td>\n",$view_bkan_kei[$r]);
                                    printf("<td nowrap class='pt10' align='right'>%s</td>\n",$view_data[$r][$c]);
                            } elseif ($c == 10 || $c == 11) {      // �δ��� ���ץ� ��˥�
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ceffce'>%s</td>\n",$view_data[$r][$c]);
                            } elseif ($c == 12) {      // �δ��� ���ʴ��� ���
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ceffce'>%s</td>\n",$view_bhan_kei[$r]);
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ceffce'>%s</td>\n",$view_data[$r][$c]);
                            } else {
                                printf("<td nowrap class='pt10' align='right'>%s</td>\n",$view_data[$r][$c]);
                            }
                        }
                    ?>
                </tr>
                <tr>
                    <TD nowrap class='pt10'>�¡��ڡ���</TD>
                    <?php
                    $r = 33;     // �����쥳����
                        for ($c=0;$c<$f_mei;$c++) {
                            if ($c == 0 || $c == 1 || $c == 9) {  // ��¤���� ���ץ� ��˥� ���
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ffffc6'>%s</td>\n",$view_data[$r][$c]);
                            } elseif ($c == 2) {                                // ���ʴ���ʬ�������
                                    printf("<td nowrap class='pt10' align='right' bgcolor='#ffffc6'>%s</td>\n",$view_bkan_kei[$r]);
                                    printf("<td nowrap class='pt10' align='right' bgcolor='#ffffc6'>%s</td>\n",$view_data[$r][$c]);
                            } elseif ($c == 8) {                                // ���ʴ���ʬ�������
                                    printf("<td nowrap class='pt10' align='right'>%s</td>\n",$view_bkan_kei[$r]);
                                    printf("<td nowrap class='pt10' align='right'>%s</td>\n",$view_data[$r][$c]);
                            } elseif ($c == 10 || $c == 11) {      // �δ��� ���ץ� ��˥�
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ceffce'>%s</td>\n",$view_data[$r][$c]);
                            } elseif ($c == 12) {      // �δ��� ���ʴ��� ���
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ceffce'>%s</td>\n",$view_bhan_kei[$r]);
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ceffce'>%s</td>\n",$view_data[$r][$c]);
                            } else {
                                printf("<td nowrap class='pt10' align='right'>%s</td>\n",$view_data[$r][$c]);
                            }
                        }
                    ?>
                </tr>
                <tr>
                    <TD nowrap class='pt10'>����������</TD>
                    <?php
                    $r = 34;     // �����쥳����
                        for ($c=0;$c<$f_mei;$c++) {
                            if ($c == 0 || $c == 1 || $c == 9) {  // ��¤���� ���ץ� ��˥� ���
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ffffc6'>%s</td>\n",$view_data[$r][$c]);
                            } elseif ($c == 2) {                                // ���ʴ���ʬ�������
                                    printf("<td nowrap class='pt10' align='right' bgcolor='#ffffc6'>%s</td>\n",$view_bkan_kei[$r]);
                                    printf("<td nowrap class='pt10' align='right' bgcolor='#ffffc6'>%s</td>\n",$view_data[$r][$c]);
                            } elseif ($c == 8) {                                // ���ʴ���ʬ�������
                                    printf("<td nowrap class='pt10' align='right'>%s</td>\n",$view_bkan_kei[$r]);
                                    printf("<td nowrap class='pt10' align='right'>%s</td>\n",$view_data[$r][$c]);
                            } elseif ($c == 10 || $c == 11) {      // �δ��� ���ץ� ��˥�
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ceffce'>%s</td>\n",$view_data[$r][$c]);
                            } elseif ($c == 12) {      // �δ��� ���ʴ��� ���
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ceffce'>%s</td>\n",$view_bhan_kei[$r]);
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ceffce'>%s</td>\n",$view_data[$r][$c]);
                            } else {
                                printf("<td nowrap class='pt10' align='right'>%s</td>\n",$view_data[$r][$c]);
                            }
                        }
                    ?>
                </tr>
                <tr>
                    <TD nowrap class='pt10'>���졼���б���</TD>
                    <?php
                    $r = 36;     // �����쥳����
                        for ($c=0;$c<$f_mei;$c++) {
                            if ($c == 0 || $c == 1 || $c == 9) {  // ��¤���� ���ץ� ��˥� ���
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ffffc6'>%s</td>\n",$view_data[$r][$c]);
                            } elseif ($c == 2) {                                // ���ʴ���ʬ�������
                                    printf("<td nowrap class='pt10' align='right' bgcolor='#ffffc6'>%s</td>\n",$view_bkan_kei[$r]);
                                    printf("<td nowrap class='pt10' align='right' bgcolor='#ffffc6'>%s</td>\n",$view_data[$r][$c]);
                            } elseif ($c == 8) {                                // ���ʴ���ʬ�������
                                    printf("<td nowrap class='pt10' align='right'>%s</td>\n",$view_bkan_kei[$r]);
                                    printf("<td nowrap class='pt10' align='right'>%s</td>\n",$view_data[$r][$c]);
                            } elseif ($c == 10 || $c == 11) {      // �δ��� ���ץ� ��˥�
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ceffce'>%s</td>\n",$view_data[$r][$c]);
                            } elseif ($c == 12) {      // �δ��� ���ʴ��� ���
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ceffce'>%s</td>\n",$view_bhan_kei[$r]);
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ceffce'>%s</td>\n",$view_data[$r][$c]);
                            } else {
                                printf("<td nowrap class='pt10' align='right'>%s</td>\n",$view_data[$r][$c]);
                            }
                        }
                    ?>
                </tr>
                <tr bgcolor='#ffffc6'>
                    <TD nowrap class='pt10b' align='right'>�����</TD>
                    <?php
                        for ($c=0;$c<$f_mei;$c++) {
                            if ($c == 2) {
                                printf("<td nowrap class='pt10b' align='right'>%s</td>\n",$view_bkan_kei_all);
                                printf("<td nowrap class='pt10b' align='right'>%s</td>\n",$view_kei_sum[$c]);
                            } elseif ($c == 8) {
                                printf("<td nowrap class='pt10b' align='right'>%s</td>\n",$view_bkan_kei_all);
                                printf("<td nowrap class='pt10b' align='right'>%s</td>\n",$view_kei_sum[$c]);
                            } elseif ($c == 12) {
                                printf("<td nowrap class='pt10b' align='right'>%s</td>\n",$view_bhan_kei_all);
                                printf("<td nowrap class='pt10b' align='right'>%s</td>\n",$view_kei_sum[$c]);
                            } else {
                                printf("<td nowrap class='pt10b' align='right'>%s</td>\n",$view_kei_sum[$c]);
                            }
                        }
                    ?>
                </tr>
                <tr bgcolor='#ffffc6'>
                    <TD colspan='2' nowrap class='pt10b' align='right'>�硡��</TD>
                    <?php
                        for ($c=0;$c<$f_mei;$c++) {
                            if ($c == 2) {
                                printf("<td nowrap class='pt10b' align='right'>%s</td>\n",$view_bkan_sum);
                                printf("<td nowrap class='pt10b' align='right'>%s</td>\n",$view_all_sum[$c]);
                            } elseif ($c == 8) {
                                printf("<td nowrap class='pt10b' align='right'>%s</td>\n",$view_bkan_sum);
                                printf("<td nowrap class='pt10b' align='right'>%s</td>\n",$view_all_sum[$c]);
                            } elseif ($c == 12) {
                                printf("<td nowrap class='pt10b' align='right'>%s</td>\n",$view_bhan_sum);
                                printf("<td nowrap class='pt10b' align='right'>%s</td>\n",$view_all_sum[$c]);
                            } else {
                                printf("<td nowrap class='pt10b' align='right'>%s</td>\n",$view_all_sum[$c]);
                            }
                        }
                    ?>
                </tr>
            </TBODY>
        </table>
    </center>
</body>
</html>
