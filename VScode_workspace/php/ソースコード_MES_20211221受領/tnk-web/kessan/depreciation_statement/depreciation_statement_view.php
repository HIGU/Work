<?php
//////////////////////////////////////////////////////////////////////////////
// ��Ⱦ�� ��������������ɽ �Ȳ�                                             //
// Copyright (C) 2020-2020 Norihisa.Ohya norihisa_ooya@nitto-kohki.co.jp    //
//                                                                          //
// Changed history                                                          //
// 2020/01/27 Created  depreciation_statement_view.php                      //
// 2020/07/01 �ǡ�����AS����������ѹ�                                      //
//////////////////////////////////////////////////////////////////////////////
// ini_set('error_reporting', E_ALL || E_STRICT);
// ini_set('error_reporting',E_ALL);           // E_ALL='2047' debug ��
// ini_set('display_errors','1');              // Error ɽ�� ON debug �� ��꡼���女����
session_start();                            // ini_set()�μ��˻��ꤹ�뤳�� Script �Ǿ��

require_once ('../../function.php');        // define.php �� pgsql.php �� require_once ���Ƥ���
require_once ('../../tnk_func.php');        // TNK �˰�¸������ʬ�δؿ��� require_once ���Ƥ���
require_once ('../../MenuHeader.php');      // TNK ������ menu class
access_log();                               // Script Name �ϼ�ư����

///// TNK ���ѥ�˥塼���饹�Υ��󥹥��󥹤����
$menu = new MenuHeader(0);                  // ǧ�ڥ����å�0=���̰ʾ� �����=TOP_MENU �����ȥ�̤����
    // �ºݤ�ǧ�ڤ�profit_loss_submit.php�ǹԤäƤ���account_group_check()�����

////////////// ����������
// $menu->set_site(10, 7);                     // site_index=10(»�ץ�˥塼) site_id=7(�»��)
//////////// ɽ�������
$menu->set_caption('�������칩��');
//////////// �ƽ����action̾�ȥ��ɥ쥹����
// $menu->set_action('��ݲ�̾',   PL . 'address.php');

///// �ƽФ�Ȥ� URL �����
$url_referer     = $_SESSION['pl_referer'];
$current_script  = $_SERVER['PHP_SELF'];        // ���߼¹���Υ�����ץ�̾����¸

/********** Logic Start **********/
///////////// �����ȥ�˥塼 On / Off 
if ($_SESSION['site_view'] == 'on') {
    $site_view = 'MenuOFF';
} else {
    $site_view = 'MenuON';
}

//////////////// �����ȥ�˥塼�Σգң����� & JavaScript����
$menu_site_url = 'http:' . WEB_HOST . 'menu_site.php';
$menu_site_script =
"<script language='JavaScript'>
<!--
    parent.menu_site.location = '$menu_site_url';
// -->
</script>";
$menu_site_script = "";         // ���˥塼�Τ���Ȥ�ʤ�

//////////// �����ȥ�����ա���������
$today = date("Y/m/d H:i:s");

//////////// JavaScript Stylesheet File ��ɬ���ɤ߹��ޤ���
$uniq = uniqid("target");

///// ������μ���
$ki = Ym_to_tnk($_SESSION['2ki_ym']);
$tuki = substr($_SESSION['2ki_ym'],4,2);
$tuki = $tuki + 1 -1;   // ���ͥǡ������Ѵ�(09��9�ˤ���������)���㥹�ȤǤ⤤���Τ���

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
$menu->set_title("�� {$ki} ������{$hanki}��Ⱦ�����������ѻ񻺤���Ӹ�������������ٽ�");

///// �о�����
$yyyymm = $_SESSION['2ki_ym'];
$ki     = Ym_to_tnk($_SESSION['2ki_ym']);
///// TNK�� �� NK�����Ѵ�
$nk_ki   = $ki + 44;
///// �о�����
if (substr($yyyymm,4,2)!=01) {
    $p1_ym = $yyyymm - 1;
} else {
    $p1_ym = $yyyymm - 100;
    $p1_ym = $p1_ym + 11;
}
///// �о������� ����ϤȤꤢ�����Ȥ�ʤ�
if (substr($p1_ym,4,2)!=01) {
    $p2_ym = $p1_ym - 1;
} else {
    $p2_ym = $p1_ym - 100;
    $p2_ym = $p2_ym + 11;
}
///// ������ ǯ��λ���
$yyyy = substr($yyyymm, 0,4);
$mm   = substr($yyyymm, 4,2);
if (($mm >= 1) && ($mm <= 3)) {
    $yyyy = ($yyyy - 1);
}
$pre_end_ym = $yyyy . "03";     // ����ǯ��

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

///// ɽ��ñ�̤��������
if (isset($_POST['state_tani'])) {
    $_SESSION['state_tani'] = $_POST['state_tani'];
    $tani = $_SESSION['state_tani'];
} elseif (isset($_SESSION['state_tani'])) {
    $tani = $_SESSION['state_tani'];
} else {
    $tani = 1;        // ����� ɽ��ñ�� ɴ����
    $_SESSION['state_tani'] = $tani;
}
///// ɽ�� ��������� �������
if (isset($_POST['state_keta'])) {
    $_SESSION['state_keta'] = $_POST['state_keta'];
    $keta = $_SESSION['state_keta'];
} elseif (isset($_SESSION['state_keta'])) {
    $keta = $_SESSION['state_keta'];
} else {
    $keta = 0;          // ����� �������ʲ����
    $_SESSION['state_keta'] = $keta;
}
// $keta = 1;              // ���ê��ɽ�ǤϾ������ʲ���1�˸��ꤷ�褦�Ȼפä������ʤ���


// �ǡ�������

// ��ʪ
$res_k   = array();
$field_k = array();
$rows_k  = array();
$sum1 = '2101';
$sum2 = '00';
$query_k = sprintf("select SUM(rep_cri), SUM(rep_de), SUM(rep_cr) from financial_report_cal where rep_ymd=%d and rep_summary1='%s' and rep_summary2='%s'", $nk_ki, $sum1, $sum2);
if ($rows_k=getResultWithField2($query_k, $field_k, $res_k) <= 0) {
    $tate_shu_kishu_kin = 0;
} else {
    $tate_shu_kishu_kin = $res_k[0][0];
}
$res_k   = array();
$field_k = array();
$rows_k  = array();
$sum1 = '2101';
$sum2 = '00';
$query_k = sprintf("select SUM(rep_de), SUM(rep_cr) from financial_report_month where rep_ymd>=%d and rep_ymd<=%d and rep_summary1='%s' and rep_summary2='%s'", $str_ym, $end_ym, $sum1, $sum2);
if ($rows_k=getResultWithField2($query_k, $field_k, $res_k) <= 0) {
    $tate_shu_zou_kin   = 0;
    $tate_shu_gen_kin   = 0;
} else {
    $tate_shu_zou_kin   = $res_k[0][0];
    $tate_shu_gen_kin   = $res_k[0][1];
}

$res_k   = array();
$field_k = array();
$rows_k  = array();
$sum1 = '3401';
$sum2 = '10';
$query_k = sprintf("select SUM(rep_cri), SUM(rep_de), SUM(rep_cr) from financial_report_cal where rep_ymd=%d and rep_summary1='%s' and rep_summary2='%s'", $nk_ki, $sum1, $sum2);
if ($rows_k=getResultWithField2($query_k, $field_k, $res_k) <= 0) {
    $tate_kishu_zan_kin = 0;
} else {
    $tate_kishu_zan_kin = $res_k[0][0];
}

$res_k   = array();
$field_k = array();
$rows_k  = array();
$sum1 = '3401';
$sum2 = '10';
$query_k = sprintf("select SUM(rep_de), SUM(rep_cr) from financial_report_month where rep_ymd>=%d and rep_ymd<=%d and rep_summary1='%s' and rep_summary2='%s'", $str_ym, $end_ym, $sum1, $sum2);
if ($rows_k=getResultWithField2($query_k, $field_k, $res_k) <= 0) {
    $tate_kishu_chou_kin = $tate_shu_kishu_kin + $tate_kishu_zan_kin;
    $tate_rui_gen_kin   = 0;
    $tate_rui_syo_kin   = 0;
} else {
    $tate_kishu_chou_kin = $tate_shu_kishu_kin + $tate_kishu_zan_kin;
    $tate_rui_gen_kin    = $res_k[0][0];
    $tate_rui_syo_kin    = $res_k[0][1];
}
$month = array();
$month[0][0] = '��ʪ�������۴���Ĺ�';
$month[0][1] = $tate_shu_kishu_kin;
$month[1][0] = '��ʪ�������۴�������';
$month[1][1] = $tate_shu_zou_kin;
$month[2][0] = '��ʪ�������۴��渺��';
$month[2][1] = $tate_shu_gen_kin;
$month[3][0] = '��ʪ����Ģ�����';
$month[3][1] = $tate_kishu_chou_kin;
$month[4][0] = '��ʪ�߷׳۴��渺��';
$month[4][1] = $tate_rui_gen_kin;
$month[5][0] = '��ʪ�߷׳��������ѳ�';
$month[5][1] = $tate_rui_syo_kin;

// ��ʪ��°����
$res_k   = array();
$field_k = array();
$rows_k  = array();
$sum1 = '2102';
$sum2 = '00';
$query_k = sprintf("select SUM(rep_cri), SUM(rep_de), SUM(rep_cr) from financial_report_cal where rep_ymd=%d and rep_summary1='%s' and rep_summary2='%s'", $nk_ki, $sum1, $sum2);
if ($rows_k=getResultWithField2($query_k, $field_k, $res_k) <= 0) {
    $tatef_shu_kishu_kin = 0;
} else {
    $tatef_shu_kishu_kin = $res_k[0][0];
}
$res_k   = array();
$field_k = array();
$rows_k  = array();
$sum1 = '2102';
$sum2 = '00';
$query_k = sprintf("select SUM(rep_de), SUM(rep_cr) from financial_report_month where rep_ymd>=%d and rep_ymd<=%d and rep_summary1='%s' and rep_summary2='%s'", $str_ym, $end_ym, $sum1, $sum2);
if ($rows_k=getResultWithField2($query_k, $field_k, $res_k) <= 0) {
    $tatef_shu_zou_kin   = 0;
    $tatef_shu_gen_kin   = 0;
} else {
    $tatef_shu_zou_kin   = $res_k[0][0];
    $tatef_shu_gen_kin   = $res_k[0][1];
}

$res_k   = array();
$field_k = array();
$rows_k  = array();
$sum1 = '3401';
$sum2 = '20';
$query_k = sprintf("select SUM(rep_cri), SUM(rep_de), SUM(rep_cr) from financial_report_cal where rep_ymd=%d and rep_summary1='%s' and rep_summary2='%s'", $nk_ki, $sum1, $sum2);
if ($rows_k=getResultWithField2($query_k, $field_k, $res_k) <= 0) {
    $tatef_kishu_zan_kin = 0;
} else {
    $tatef_kishu_zan_kin = $res_k[0][0];
}

$res_k   = array();
$field_k = array();
$rows_k  = array();
$sum1 = '3401';
$sum2 = '20';
$query_k = sprintf("select SUM(rep_de), SUM(rep_cr) from financial_report_month where rep_ymd>=%d and rep_ymd<=%d and rep_summary1='%s' and rep_summary2='%s'", $str_ym, $end_ym, $sum1, $sum2);
if ($rows_k=getResultWithField2($query_k, $field_k, $res_k) <= 0) {
    $tatef_kishu_chou_kin = $tatef_shu_kishu_kin + $tatef_kishu_zan_kin;
    $tatef_rui_gen_kin   = 0;
    $tatef_rui_syo_kin   = 0;
} else {
    $tatef_kishu_chou_kin = $tatef_shu_kishu_kin + $tatef_kishu_zan_kin;
    $tatef_rui_gen_kin    = $res_k[0][0];
    $tatef_rui_syo_kin    = $res_k[0][1];
}
$month[6][0]  = '��ʪ��°�����������۴���Ĺ�';
$month[6][1]  = $tatef_shu_kishu_kin;
$month[7][0]  = '��ʪ��°�����������۴�������';
$month[7][1]  = $tatef_shu_zou_kin;
$month[8][0]  = '��ʪ��°�����������۴��渺��';
$month[8][1]  = $tatef_shu_gen_kin;
$month[9][0]  = '��ʪ��°��������Ģ�����';
$month[9][1]  = $tatef_kishu_chou_kin;
$month[10][0] = '��ʪ��°�����߷׳۴��渺��';
$month[10][1] = $tatef_rui_gen_kin;
$month[11][0] = '��ʪ��°�����߷׳��������ѳ�';
$month[11][1] = $tatef_rui_syo_kin;

// ����ʪ
$res_k   = array();
$field_k = array();
$rows_k  = array();
$sum1 = '2103';
$sum2 = '00';
$query_k = sprintf("select SUM(rep_cri), SUM(rep_de), SUM(rep_cr) from financial_report_cal where rep_ymd=%d and rep_summary1='%s' and rep_summary2='%s'", $nk_ki, $sum1, $sum2);
if ($rows_k=getResultWithField2($query_k, $field_k, $res_k) <= 0) {
    $kou_shu_kishu_kin = 0;
} else {
    $kou_shu_kishu_kin = $res_k[0][0];
}
$res_k   = array();
$field_k = array();
$rows_k  = array();
$sum1 = '2103';
$sum2 = '00';
$query_k = sprintf("select SUM(rep_de), SUM(rep_cr) from financial_report_month where rep_ymd>=%d and rep_ymd<=%d and rep_summary1='%s' and rep_summary2='%s'", $str_ym, $end_ym, $sum1, $sum2);
if ($rows_k=getResultWithField2($query_k, $field_k, $res_k) <= 0) {
    $kou_shu_zou_kin   = 0;
    $kou_shu_gen_kin   = 0;
} else {
    $kou_shu_zou_kin   = $res_k[0][0];
    $kou_shu_gen_kin   = $res_k[0][1];
}

$res_k   = array();
$field_k = array();
$rows_k  = array();
$sum1 = '3401';
$sum2 = '30';
$query_k = sprintf("select SUM(rep_cri), SUM(rep_de), SUM(rep_cr) from financial_report_cal where rep_ymd=%d and rep_summary1='%s' and rep_summary2='%s'", $nk_ki, $sum1, $sum2);
if ($rows_k=getResultWithField2($query_k, $field_k, $res_k) <= 0) {
    $kou_kishu_zan_kin = 0;
} else {
    $kou_kishu_zan_kin = $res_k[0][0];
}

$res_k   = array();
$field_k = array();
$rows_k  = array();
$sum1 = '3401';
$sum2 = '30';
$query_k = sprintf("select SUM(rep_de), SUM(rep_cr) from financial_report_month where rep_ymd>=%d and rep_ymd<=%d and rep_summary1='%s' and rep_summary2='%s'", $str_ym, $end_ym, $sum1, $sum2);
if ($rows_k=getResultWithField2($query_k, $field_k, $res_k) <= 0) {
    $kou_kishu_chou_kin = $kou_shu_kishu_kin + $kou_kishu_zan_kin;
    $kou_rui_gen_kin   = 0;
    $kou_rui_syo_kin   = 0;
} else {
    $kou_kishu_chou_kin = $kou_shu_kishu_kin + $kou_kishu_zan_kin;
    $kou_rui_gen_kin    = $res_k[0][0];
    $kou_rui_syo_kin    = $res_k[0][1];
}
$month[12][0] = '����ʪ�������۴���Ĺ�';
$month[12][1] = $kou_shu_kishu_kin;
$month[13][0] = '����ʪ�������۴�������';
$month[13][1] = $kou_shu_zou_kin;
$month[14][0] = '����ʪ�������۴��渺��';
$month[14][1] = $kou_shu_gen_kin;
$month[15][0] = '����ʪ����Ģ�����';
$month[15][1] = $kou_kishu_chou_kin;
$month[16][0] = '����ʪ�߷׳۴��渺��';
$month[16][1] = $kou_rui_gen_kin;
$month[17][0] = '����ʪ�߷׳��������ѳ�';
$month[17][1] = $kou_rui_syo_kin;

// ��������
$res_k   = array();
$field_k = array();
$rows_k  = array();
$sum1 = '2104';
$sum2 = '00';
$query_k = sprintf("select SUM(rep_cri), SUM(rep_de), SUM(rep_cr) from financial_report_cal where rep_ymd=%d and rep_summary1='%s' and rep_summary2='%s'", $nk_ki, $sum1, $sum2);
if ($rows_k=getResultWithField2($query_k, $field_k, $res_k) <= 0) {
    $kikai_shu_kishu_kin = 0;
} else {
    $kikai_shu_kishu_kin = $res_k[0][0];
}
$res_k   = array();
$field_k = array();
$rows_k  = array();
$sum1 = '2104';
$sum2 = '00';
$query_k = sprintf("select SUM(rep_de), SUM(rep_cr) from financial_report_month where rep_ymd>=%d and rep_ymd<=%d and rep_summary1='%s' and rep_summary2='%s'", $str_ym, $end_ym, $sum1, $sum2);
if ($rows_k=getResultWithField2($query_k, $field_k, $res_k) <= 0) {
    $kikai_shu_zou_kin   = 0;
    $kikai_shu_gen_kin   = 0;
} else {
    $kikai_shu_zou_kin   = $res_k[0][0];
    $kikai_shu_gen_kin   = $res_k[0][1];
}

$res_k   = array();
$field_k = array();
$rows_k  = array();
$sum1 = '3401';
$sum2 = '40';
$query_k = sprintf("select SUM(rep_cri), SUM(rep_de), SUM(rep_cr) from financial_report_cal where rep_ymd=%d and rep_summary1='%s' and rep_summary2='%s'", $nk_ki, $sum1, $sum2);
if ($rows_k=getResultWithField2($query_k, $field_k, $res_k) <= 0) {
    $kikai_kishu_zan_kin = 0;
} else {
    $kikai_kishu_zan_kin = $res_k[0][0];
}

$res_k   = array();
$field_k = array();
$rows_k  = array();
$sum1 = '3401';
$sum2 = '40';
$query_k = sprintf("select SUM(rep_de), SUM(rep_cr) from financial_report_month where rep_ymd>=%d and rep_ymd<=%d and rep_summary1='%s' and rep_summary2='%s'", $str_ym, $end_ym, $sum1, $sum2);
if ($rows_k=getResultWithField2($query_k, $field_k, $res_k) <= 0) {
    $kikai_kishu_chou_kin = $kikai_shu_kishu_kin + $kikai_kishu_zan_kin;
    $kikai_rui_gen_kin   = 0;
    $kikai_rui_syo_kin   = 0;
} else {
    $kikai_kishu_chou_kin = $kikai_shu_kishu_kin + $kikai_kishu_zan_kin;
    $kikai_rui_gen_kin    = $res_k[0][0];
    $kikai_rui_syo_kin    = $res_k[0][1];
}
$month[18][0] = '�������ּ������۴���Ĺ�';
$month[18][1] = $kikai_shu_kishu_kin;
$month[19][0] = '�������ּ������۴�������';
$month[19][1] = $kikai_shu_zou_kin;
$month[20][0] = '�������ּ������۴��渺��';
$month[20][1] = $kikai_shu_gen_kin;
$month[21][0] = '�������ִ���Ģ�����';
$month[21][1] = $kikai_kishu_chou_kin;
$month[22][0] = '���������߷׳۴��渺��';
$month[22][1] = $kikai_rui_gen_kin;
$month[23][0] = '���������߷׳��������ѳ�';
$month[23][1] = $kikai_rui_syo_kin;

// ���ѱ��¶�
$res_k   = array();
$field_k = array();
$rows_k  = array();
$sum1 = '2105';
$sum2 = '00';
$query_k = sprintf("select SUM(rep_cri), SUM(rep_de), SUM(rep_cr) from financial_report_cal where rep_ymd=%d and rep_summary1='%s' and rep_summary2='%s'", $nk_ki, $sum1, $sum2);
if ($rows_k=getResultWithField2($query_k, $field_k, $res_k) <= 0) {
    $syaryo_shu_kishu_kin = 0;
} else {
    $syaryo_shu_kishu_kin = $res_k[0][0];
}
$res_k   = array();
$field_k = array();
$rows_k  = array();
$sum1 = '2105';
$sum2 = '00';
$query_k = sprintf("select SUM(rep_de), SUM(rep_cr) from financial_report_month where rep_ymd>=%d and rep_ymd<=%d and rep_summary1='%s' and rep_summary2='%s'", $str_ym, $end_ym, $sum1, $sum2);
if ($rows_k=getResultWithField2($query_k, $field_k, $res_k) <= 0) {
    $syaryo_shu_zou_kin   = 0;
    $syaryo_shu_gen_kin   = 0;
} else {
    $syaryo_shu_zou_kin   = $res_k[0][0];
    $syaryo_shu_gen_kin   = $res_k[0][1];
}

$res_k   = array();
$field_k = array();
$rows_k  = array();
$sum1 = '3401';
$sum2 = '50';
$query_k = sprintf("select SUM(rep_cri), SUM(rep_de), SUM(rep_cr) from financial_report_cal where rep_ymd=%d and rep_summary1='%s' and rep_summary2='%s'", $nk_ki, $sum1, $sum2);
if ($rows_k=getResultWithField2($query_k, $field_k, $res_k) <= 0) {
    $syaryo_kishu_zan_kin = 0;
} else {
    $syaryo_kishu_zan_kin = $res_k[0][0];
}

$res_k   = array();
$field_k = array();
$rows_k  = array();
$sum1 = '3401';
$sum2 = '50';
$query_k = sprintf("select SUM(rep_de), SUM(rep_cr) from financial_report_month where rep_ymd>=%d and rep_ymd<=%d and rep_summary1='%s' and rep_summary2='%s'", $str_ym, $end_ym, $sum1, $sum2);
if ($rows_k=getResultWithField2($query_k, $field_k, $res_k) <= 0) {
    $syaryo_kishu_chou_kin = $syaryo_shu_kishu_kin + $syaryo_kishu_zan_kin;
    $syaryo_rui_gen_kin   = 0;
    $syaryo_rui_syo_kin   = 0;
} else {
    $syaryo_kishu_chou_kin = $syaryo_shu_kishu_kin + $syaryo_kishu_zan_kin;
    $syaryo_rui_gen_kin    = $res_k[0][0];
    $syaryo_rui_syo_kin    = $res_k[0][1];
}
$month[24][0] = '���ѱ��¶�������۴���Ĺ�';
$month[24][1] = $syaryo_shu_kishu_kin;
$month[25][0] = '���ѱ��¶�������۴�������';
$month[25][1] = $syaryo_shu_zou_kin;
$month[26][0] = '���ѱ��¶�������۴��渺��';
$month[26][1] = $syaryo_shu_gen_kin;
$month[27][0] = '���ѱ��¶����Ģ�����';
$month[27][1] = $syaryo_kishu_chou_kin;
$month[28][0] = '���ѱ��¶��߷׳۴��渺��';
$month[28][1] = $syaryo_rui_gen_kin;
$month[29][0] = '���ѱ��¶��߷׳��������ѳ�';
$month[29][1] = $syaryo_rui_syo_kin;

// ��񹩶�
$res_k   = array();
$field_k = array();
$rows_k  = array();
$sum1 = '2106';
$sum2 = '00';
$query_k = sprintf("select SUM(rep_cri), SUM(rep_de), SUM(rep_cr) from financial_report_cal where rep_ymd=%d and rep_summary1='%s' and rep_summary2='%s'", $nk_ki, $sum1, $sum2);
if ($rows_k=getResultWithField2($query_k, $field_k, $res_k) <= 0) {
    $kigu_shu_kishu_kin = 0;
} else {
    $kigu_shu_kishu_kin = $res_k[0][0];
}
$res_k   = array();
$field_k = array();
$rows_k  = array();
$sum1 = '2106';
$sum2 = '00';
$query_k = sprintf("select SUM(rep_de), SUM(rep_cr) from financial_report_month where rep_ymd>=%d and rep_ymd<=%d and rep_summary1='%s' and rep_summary2='%s'", $str_ym, $end_ym, $sum1, $sum2);
if ($rows_k=getResultWithField2($query_k, $field_k, $res_k) <= 0) {
    $kigu_shu_zou_kin   = 0;
    $kigu_shu_gen_kin   = 0;
} else {
    $kigu_shu_zou_kin   = $res_k[0][0];
    $kigu_shu_gen_kin   = $res_k[0][1];
}

$res_k   = array();
$field_k = array();
$rows_k  = array();
$sum1 = '3401';
$sum2 = '60';
$query_k = sprintf("select SUM(rep_cri), SUM(rep_de), SUM(rep_cr) from financial_report_cal where rep_ymd=%d and rep_summary1='%s' and rep_summary2='%s'", $nk_ki, $sum1, $sum2);
if ($rows_k=getResultWithField2($query_k, $field_k, $res_k) <= 0) {
    $kigu_kishu_zan_kin = 0;
} else {
    $kigu_kishu_zan_kin = $res_k[0][0];
}

$res_k   = array();
$field_k = array();
$rows_k  = array();
$sum1 = '3401';
$sum2 = '60';
$query_k = sprintf("select SUM(rep_de), SUM(rep_cr) from financial_report_month where rep_ymd>=%d and rep_ymd<=%d and rep_summary1='%s' and rep_summary2='%s'", $str_ym, $end_ym, $sum1, $sum2);
if ($rows_k=getResultWithField2($query_k, $field_k, $res_k) <= 0) {
    $kigu_kishu_chou_kin = $kigu_shu_kishu_kin + $kigu_kishu_zan_kin;
    $kigu_rui_gen_kin   = 0;
    $kigu_rui_syo_kin   = 0;
} else {
    $kigu_kishu_chou_kin = $kigu_shu_kishu_kin + $kigu_kishu_zan_kin;
    $kigu_rui_gen_kin    = $res_k[0][0];
    $kigu_rui_syo_kin    = $res_k[0][1];
}
$month[30][0] = '��񹩶�������۴���Ĺ�';
$month[30][1] = $kigu_shu_kishu_kin;
$month[31][0] = '��񹩶�������۴�������';
$month[31][1] = $kigu_shu_zou_kin;
$month[32][0] = '��񹩶�������۴��渺��';
$month[32][1] = $kigu_shu_gen_kin;
$month[33][0] = '��񹩶����Ģ�����';
$month[33][1] = $kigu_kishu_chou_kin;
$month[34][0] = '��񹩶��߷׳۴��渺��';
$month[34][1] = $kigu_rui_gen_kin;
$month[35][0] = '��񹩶��߷׳��������ѳ�';
$month[35][1] = $kigu_rui_syo_kin;

// ��������
$res_k   = array();
$field_k = array();
$rows_k  = array();
$sum1 = '2107';
$sum2 = '00';
$query_k = sprintf("select SUM(rep_cri), SUM(rep_de), SUM(rep_cr) from financial_report_cal where rep_ymd=%d and rep_summary1='%s' and rep_summary2='%s'", $nk_ki, $sum1, $sum2);
if ($rows_k=getResultWithField2($query_k, $field_k, $res_k) <= 0) {
    $bihin_shu_kishu_kin = 0;
} else {
    $bihin_shu_kishu_kin = $res_k[0][0];
}
$res_k   = array();
$field_k = array();
$rows_k  = array();
$sum1 = '2107';
$sum2 = '00';
$query_k = sprintf("select SUM(rep_de), SUM(rep_cr) from financial_report_month where rep_ymd>=%d and rep_ymd<=%d and rep_summary1='%s' and rep_summary2='%s'", $str_ym, $end_ym, $sum1, $sum2);
if ($rows_k=getResultWithField2($query_k, $field_k, $res_k) <= 0) {
    $bihin_shu_zou_kin   = 0;
    $bihin_shu_gen_kin   = 0;
} else {
    $bihin_shu_zou_kin   = $res_k[0][0];
    $bihin_shu_gen_kin   = $res_k[0][1];
}

$res_k   = array();
$field_k = array();
$rows_k  = array();
$sum1 = '3401';
$sum2 = '70';
$query_k = sprintf("select SUM(rep_cri), SUM(rep_de), SUM(rep_cr) from financial_report_cal where rep_ymd=%d and rep_summary1='%s' and rep_summary2='%s'", $nk_ki, $sum1, $sum2);
if ($rows_k=getResultWithField2($query_k, $field_k, $res_k) <= 0) {
    $bihin_kishu_zan_kin = 0;
} else {
    $bihin_kishu_zan_kin = $res_k[0][0];
}

$res_k   = array();
$field_k = array();
$rows_k  = array();
$sum1 = '3401';
$sum2 = '70';
$query_k = sprintf("select SUM(rep_de), SUM(rep_cr) from financial_report_month where rep_ymd>=%d and rep_ymd<=%d and rep_summary1='%s' and rep_summary2='%s'", $str_ym, $end_ym, $sum1, $sum2);
if ($rows_k=getResultWithField2($query_k, $field_k, $res_k) <= 0) {
    $bihin_kishu_chou_kin = $bihin_shu_kishu_kin + $bihin_kishu_zan_kin;
    $bihin_rui_gen_kin   = 0;
    $bihin_rui_syo_kin   = 0;
} else {
    $bihin_kishu_chou_kin = $bihin_shu_kishu_kin + $bihin_kishu_zan_kin;
    $bihin_rui_gen_kin    = $res_k[0][0];
    $bihin_rui_syo_kin    = $res_k[0][1];
}
$month[36][0] = '�������ʼ������۴���Ĺ�';
$month[36][1] = $bihin_shu_kishu_kin;
$month[37][0] = '�������ʼ������۴�������';
$month[37][1] = $bihin_shu_zou_kin;
$month[38][0] = '�������ʼ������۴��渺��';
$month[38][1] = $bihin_shu_gen_kin;
$month[39][0] = '�������ʴ���Ģ�����';
$month[39][1] = $bihin_kishu_chou_kin;
$month[40][0] = '���������߷׳۴��渺��';
$month[40][1] = $bihin_rui_gen_kin;
$month[41][0] = '���������߷׳��������ѳ�';
$month[41][1] = $bihin_rui_syo_kin;

// �꡼����
$res_k   = array();
$field_k = array();
$rows_k  = array();
$sum1 = '2110';
$sum2 = '00';
$query_k = sprintf("select SUM(rep_cri), SUM(rep_de), SUM(rep_cr) from financial_report_cal where rep_ymd=%d and rep_summary1='%s' and rep_summary2='%s'", $nk_ki, $sum1, $sum2);
if ($rows_k=getResultWithField2($query_k, $field_k, $res_k) <= 0) {
    $lease_shu_kishu_kin = 0;
} else {
    $lease_shu_kishu_kin = $res_k[0][0];
}
$res_k   = array();
$field_k = array();
$rows_k  = array();
$sum1 = '2110';
$sum2 = '00';
$query_k = sprintf("select SUM(rep_de), SUM(rep_cr) from financial_report_month where rep_ymd>=%d and rep_ymd<=%d and rep_summary1='%s' and rep_summary2='%s'", $str_ym, $end_ym, $sum1, $sum2);
if ($rows_k=getResultWithField2($query_k, $field_k, $res_k) <= 0) {
    $lease_shu_zou_kin   = 0;
    $lease_shu_gen_kin   = 0;
} else {
    $lease_shu_zou_kin   = $res_k[0][0];
    $lease_shu_gen_kin   = $res_k[0][1];
}

$res_k   = array();
$field_k = array();
$rows_k  = array();
$sum1 = '3401';
$sum2 = '80';
$query_k = sprintf("select SUM(rep_cri), SUM(rep_de), SUM(rep_cr) from financial_report_cal where rep_ymd=%d and rep_summary1='%s' and rep_summary2='%s'", $nk_ki, $sum1, $sum2);
if ($rows_k=getResultWithField2($query_k, $field_k, $res_k) <= 0) {
    $lease_kishu_zan_kin = 0;
} else {
    $lease_kishu_zan_kin = $res_k[0][0];
}

$res_k   = array();
$field_k = array();
$rows_k  = array();
$sum1 = '3401';
$sum2 = '80';
$query_k = sprintf("select SUM(rep_de), SUM(rep_cr) from financial_report_month where rep_ymd>=%d and rep_ymd<=%d and rep_summary1='%s' and rep_summary2='%s'", $str_ym, $end_ym, $sum1, $sum2);
if ($rows_k=getResultWithField2($query_k, $field_k, $res_k) <= 0) {
    $lease_kishu_chou_kin = $lease_shu_kishu_kin + $lease_kishu_zan_kin;
    $lease_rui_gen_kin   = 0;
    $lease_rui_syo_kin   = 0;
} else {
    $lease_kishu_chou_kin = $lease_shu_kishu_kin + $lease_kishu_zan_kin;
    $lease_rui_gen_kin    = $res_k[0][0];
    $lease_rui_syo_kin    = $res_k[0][1];
}
$month[42][0] = '�꡼���񻺼������۴���Ĺ�';
$month[42][1] = $lease_shu_kishu_kin;
$month[43][0] = '�꡼���񻺼������۴�������';
$month[43][1] = $lease_shu_zou_kin;
$month[44][0] = '�꡼���񻺼������۴��渺��';
$month[44][1] = $lease_shu_gen_kin;
$month[45][0] = '�꡼���񻺴���Ģ�����';
$month[45][1] = $lease_kishu_chou_kin;
$month[46][0] = '�꡼�����߷׳۴��渺��';
$month[46][1] = $lease_rui_gen_kin;
$month[47][0] = '�꡼�����߷׳��������ѳ�';
$month[47][1] = $lease_rui_syo_kin;

// ���ò�����
$res   = array();
$field = array();
$rows  = array();
$note = '���ô���Ĺ�';
$query = sprintf("SELECT rep_kin FROM financial_report_data WHERE rep_ymd=%d and rep_note='%s'", $yyyymm, $note);
if ($rows=getResultWithField2($query, $field, $res) <= 0) {
    $denwa_shu_kishu_kin = 0;
} else {
    $denwa_shu_kishu_kin = $res[0][0];
}
$res   = array();
$field = array();
$rows  = array();
$note = '���ô�������';
$query = sprintf("SELECT rep_kin FROM financial_report_data WHERE rep_ymd=%d and rep_note='%s'", $yyyymm, $note);
if ($rows=getResultWithField2($query, $field, $res) <= 0) {
    $denwa_shu_zou_kin = 0;
} else {
    $denwa_shu_zou_kin = $res[0][0];
}
$res   = array();
$field = array();
$rows  = array();
$note = '���ô��渺��';
$query = sprintf("SELECT rep_kin FROM financial_report_data WHERE rep_ymd=%d and rep_note='%s'", $yyyymm, $note);
if ($rows=getResultWithField2($query, $field, $res) <= 0) {
    $denwa_shu_gen_kin = 0;
} else {
    $denwa_shu_gen_kin = $res[0][0];
}

$res_k   = array();
$field_k = array();
$rows_k  = array();
$sum1 = '2207';
$sum2 = '00';
$query_k = sprintf("select SUM(rep_cri), SUM(rep_de), SUM(rep_cr) from financial_report_cal where rep_ymd=%d and rep_summary1='%s' and rep_summary2='%s'", $nk_ki, $sum1, $sum2);
if ($rows_k=getResultWithField2($query_k, $field_k, $res_k) <= 0) {
    $denwa_kishu_chou_kin = 0;
} else {
    $denwa_kishu_chou_kin = $res_k[0][0];
}
$res_k   = array();
$field_k = array();
$rows_k  = array();
$sum1 = '2207';
$sum2 = '00';
$query_k = sprintf("select SUM(rep_de), SUM(rep_cr) from financial_report_month where rep_ymd>=%d and rep_ymd<=%d and rep_summary1='%s' and rep_summary2='%s'", $str_ym, $end_ym, $sum1, $sum2);
if ($rows_k=getResultWithField2($query_k, $field_k, $res_k) <= 0) {
    $denwa_rui_gen_kin   = 0;
    $denwa_rui_syo_kin   = 0;
} else {
    $denwa_rui_gen_kin   = $res_k[0][0];
    $denwa_rui_syo_kin   = $res_k[0][1];
}
/*
$res_k   = array();
$field_k = array();
$rows_k  = array();
$sum1 = '2207';
$sum2 = '00';
$query_k = sprintf("select rep_cri, rep_de, rep_cr from financial_report_cal where rep_ymd=%d and rep_summary1='%s' and rep_summary2='%s'", $nk_ki, $sum1, $sum2);
if ($rows_k=getResultWithField2($query_k, $field_k, $res_k) <= 0) {
    $denwa_kishu_chou_kin = 0;
    $denwa_rui_gen_kin    = 0;
    $denwa_rui_syo_kin    = 0;
} else {
    $denwa_kishu_chou_kin = $res_k[0][0];
    $denwa_rui_gen_kin    = $res_k[0][1];
    $denwa_rui_syo_kin    = $res_k[0][2];
}
*/
$month[48][0] = '���ò������������۴���Ĺ�';
$month[48][1] = $denwa_shu_kishu_kin;
$month[49][0] = '���ò������������۴�������';
$month[49][1] = $denwa_shu_zou_kin;
$month[50][0] = '���ò������������۴��渺��';
$month[50][1] = $denwa_shu_gen_kin;
$month[51][0] = '���ò���������Ģ�����';
$month[51][1] = $denwa_kishu_chou_kin;
$month[52][0] = '���ò������߷׳۴��渺��';
$month[52][1] = $denwa_rui_gen_kin;
$month[53][0] = '���ò������߷׳��������ѳ�';
$month[53][1] = $denwa_rui_syo_kin;

// �������Ѹ�
$res   = array();
$field = array();
$rows  = array();
$note = '���ߴ���Ĺ�';
$query = sprintf("SELECT rep_kin FROM financial_report_data WHERE rep_ymd=%d and rep_note='%s'", $yyyymm, $note);
if ($rows=getResultWithField2($query, $field, $res) <= 0) {
    $sisetu_shu_kishu_kin = 0;
} else {
    $sisetu_shu_kishu_kin = $res[0][0];
}
$res   = array();
$field = array();
$rows  = array();
$note = '���ߴ�������';
$query = sprintf("SELECT rep_kin FROM financial_report_data WHERE rep_ymd=%d and rep_note='%s'", $yyyymm, $note);
if ($rows=getResultWithField2($query, $field, $res) <= 0) {
    $sisetu_shu_zou_kin = 0;
} else {
    $sisetu_shu_zou_kin = $res[0][0];
}
$res   = array();
$field = array();
$rows  = array();
$note = '���ߴ��渺��';
$query = sprintf("SELECT rep_kin FROM financial_report_data WHERE rep_ymd=%d and rep_note='%s'", $yyyymm, $note);
if ($rows=getResultWithField2($query, $field, $res) <= 0) {
    $sisetu_shu_gen_kin = 0;
} else {
    $sisetu_shu_gen_kin = $res[0][0];
}

$res_k   = array();
$field_k = array();
$rows_k  = array();
$sum1 = '2208';
$sum2 = '00';
$query_k = sprintf("select SUM(rep_cri), SUM(rep_de), SUM(rep_cr) from financial_report_cal where rep_ymd=%d and rep_summary1='%s' and rep_summary2='%s'", $nk_ki, $sum1, $sum2);
if ($rows_k=getResultWithField2($query_k, $field_k, $res_k) <= 0) {
    $sisetu_kishu_chou_kin = 0;
} else {
    $sisetu_kishu_chou_kin = $res_k[0][0];
}
$res_k   = array();
$field_k = array();
$rows_k  = array();
$sum1 = '2208';
$sum2 = '00';
$query_k = sprintf("select SUM(rep_de), SUM(rep_cr) from financial_report_month where rep_ymd>=%d and rep_ymd<=%d and rep_summary1='%s' and rep_summary2='%s'", $str_ym, $end_ym, $sum1, $sum2);
if ($rows_k=getResultWithField2($query_k, $field_k, $res_k) <= 0) {
    $sisetu_rui_gen_kin   = 0;
    $sisetu_rui_syo_kin   = 0;
} else {
    $sisetu_rui_gen_kin   = $res_k[0][0];
    $sisetu_rui_syo_kin   = $res_k[0][1];
}
/*
$res_k   = array();
$field_k = array();
$rows_k  = array();
$sum1 = '2208';
$sum2 = '00';
$query_k = sprintf("select rep_cri, rep_de, rep_cr from financial_report_cal where rep_ymd=%d and rep_summary1='%s' and rep_summary2='%s'", $nk_ki, $sum1, $sum2);
if ($rows_k=getResultWithField2($query_k, $field_k, $res_k) <= 0) {
    $sisetu_kishu_chou_kin = 0;
    $sisetu_rui_gen_kin    = 0;
    $sisetu_rui_syo_kin    = 0;
} else {
    $sisetu_kishu_chou_kin = $res_k[0][0];
    $sisetu_rui_gen_kin    = 0;                 //$res_k[0][1];
    $sisetu_rui_syo_kin    = $res_k[0][2];
}
*/
$month[54][0] = '�������Ѹ��������۴���Ĺ�';
$month[54][1] = $sisetu_shu_kishu_kin;
$month[55][0] = '�������Ѹ��������۴�������';
$month[55][1] = $sisetu_shu_zou_kin;
$month[56][0] = '�������Ѹ��������۴��渺��';
$month[56][1] = $sisetu_shu_gen_kin;
$month[57][0] = '�������Ѹ�����Ģ�����';
$month[57][1] = $sisetu_kishu_chou_kin;
$month[58][0] = '�������Ѹ��߷׳۴��渺��';
$month[58][1] = $sisetu_rui_gen_kin;
$month[59][0] = '�������Ѹ��߷׳��������ѳ�';
$month[59][1] = $sisetu_rui_syo_kin;

// ���եȥ�����
$res   = array();
$field = array();
$rows  = array();
$note = '���եȴ���Ĺ�';
$query = sprintf("SELECT rep_kin FROM financial_report_data WHERE rep_ymd=%d and rep_note='%s'", $yyyymm, $note);
if ($rows=getResultWithField2($query, $field, $res) <= 0) {
    $soft_shu_kishu_kin = 0;
} else {
    $soft_shu_kishu_kin = $res[0][0];
}
$res   = array();
$field = array();
$rows  = array();
$note = '���եȴ�������';
$query = sprintf("SELECT rep_kin FROM financial_report_data WHERE rep_ymd=%d and rep_note='%s'", $yyyymm, $note);
if ($rows=getResultWithField2($query, $field, $res) <= 0) {
    $soft_shu_zou_kin = 0;
} else {
    $soft_shu_zou_kin = $res[0][0];
}
$res   = array();
$field = array();
$rows  = array();
$note = '���եȴ��渺��';
$query = sprintf("SELECT rep_kin FROM financial_report_data WHERE rep_ymd=%d and rep_note='%s'", $yyyymm, $note);
if ($rows=getResultWithField2($query, $field, $res) <= 0) {
    $soft_shu_gen_kin = 0;
} else {
    $soft_shu_gen_kin = $res[0][0];
}

$res_k   = array();
$field_k = array();
$rows_k  = array();
$sum1 = '2212';
$sum2 = '00';
$query_k = sprintf("select SUM(rep_cri), SUM(rep_de), SUM(rep_cr) from financial_report_cal where rep_ymd=%d and rep_summary1='%s' and rep_summary2='%s'", $nk_ki, $sum1, $sum2);
if ($rows_k=getResultWithField2($query_k, $field_k, $res_k) <= 0) {
    $soft_kishu_chou_kin = 0;
} else {
    $soft_kishu_chou_kin = $res_k[0][0];
}
$res_k   = array();
$field_k = array();
$rows_k  = array();
$sum1 = '2212';
$sum2 = '00';
$query_k = sprintf("select SUM(rep_de), SUM(rep_cr) from financial_report_month where rep_ymd>=%d and rep_ymd<=%d and rep_summary1='%s' and rep_summary2='%s'", $str_ym, $end_ym, $sum1, $sum2);
if ($rows_k=getResultWithField2($query_k, $field_k, $res_k) <= 0) {
    $soft_rui_gen_kin   = 0;
    $soft_rui_syo_kin   = 0;
} else {
    $soft_rui_gen_kin   = $res_k[0][0] - $soft_shu_zou_kin;
    $soft_rui_syo_kin   = $res_k[0][1];
}
/*
$res_k   = array();
$field_k = array();
$rows_k  = array();
$sum1 = '2212';
$sum2 = '00';
$query_k = sprintf("select rep_cri, rep_de, rep_cr from financial_report_cal where rep_ymd=%d and rep_summary1='%s' and rep_summary2='%s'", $nk_ki, $sum1, $sum2);
if ($rows_k=getResultWithField2($query_k, $field_k, $res_k) <= 0) {
    $soft_kishu_chou_kin = 0;
    $soft_rui_gen_kin    = 0;
    $soft_rui_syo_kin    = 0;
} else {
    $soft_kishu_chou_kin = $res_k[0][0];
    $soft_rui_gen_kin    = 0;               //$res_k[0][1];
    $soft_rui_syo_kin    = $res_k[0][2];
}
*/
$month[60][0] = '���եȥ������������۴���Ĺ�';
$month[60][1] = $soft_shu_kishu_kin;
$month[61][0] = '���եȥ������������۴�������';
$month[61][1] = $soft_shu_zou_kin;
$month[62][0] = '���եȥ������������۴��渺��';
$month[62][1] = $soft_shu_gen_kin;
$month[63][0] = '���եȥ���������Ģ�����';
$month[63][1] = $soft_kishu_chou_kin;
$month[64][0] = '���եȥ������߷׳۴��渺��';
$month[64][1] = $soft_rui_gen_kin;
$month[65][0] = '���եȥ������߷׳��������ѳ�';
$month[65][1] = $soft_rui_syo_kin;

///// act_comp_invent_history ���ǡ�������
    ///// ����
/*
$month = array();
$query = "select item, kin from act_state_depreciation_history where state_ym=$yyyymm";
if (($rows = getResult2($query, $month)) <= 0) {
    $_SESSION['s_sysmsg'] = sprintf("��������������ɽ�Υǡ����ʤ���<br>�� %d�� ��%s��Ⱦ��",$ki,$hanki);
    header("Location: $url_referer");
    exit();
} else {
*/
    $rows = count($month);
    ///// item ��̾���ȶ�ۤ�����ñ�̤Ⱦ�������ǥϥå��������
    for ($r=0; $r<$rows; $r++) {
        $month["{$month[$r][0]}"] = Uround($month[$r][1] / $tani, $keta);
    }
    /////////////////////////////////////////////////////////////////////// �������۴���Ĺ� START
    ///// �ƶ�ۤ򣳷奫��ޤǥϥå��������
    $tbody['tbody_shutoku_kishu_tate']   = number_format($month['��ʪ�������۴���Ĺ�'], $keta);
    $tbody['tbody_shutoku_kishu_fuzoku'] = number_format($month['��ʪ��°�����������۴���Ĺ�'], $keta);
    $tbody['tbody_shutoku_kishu_kouti']  = number_format($month['����ʪ�������۴���Ĺ�']  , $keta);
    $tbody['tbody_shutoku_kishu_kikai']  = number_format($month['�������ּ������۴���Ĺ�'], $keta);
    $tbody['tbody_shutoku_kishu_sharyo'] = number_format($month['���ѱ��¶�������۴���Ĺ�'], $keta);
    $tbody['tbody_shutoku_kishu_kigu']   = number_format($month['��񹩶�������۴���Ĺ�'], $keta);
    $tbody['tbody_shutoku_kishu_jyuki']  = number_format($month['�������ʼ������۴���Ĺ�'], $keta);
    $tbody['tbody_shutoku_kishu_lease']  = number_format($month['�꡼���񻺼������۴���Ĺ�'], $keta);
    $tbody['tbody_shutoku_kishu_denwa']  = number_format($month['���ò������������۴���Ĺ�'], $keta);
    $tbody['tbody_shutoku_kishu_shise']  = number_format($month['�������Ѹ��������۴���Ĺ�'], $keta);
    $tbody['tbody_shutoku_kishu_soft']   = number_format($month['���եȥ������������۴���Ĺ�'], $keta);
    ///// ��ʪ��ס����������ʷס�ͭ����ס�̵����ס����פ�׻�
    $total_shutoku_kishu_tate  = $month['��ʪ�������۴���Ĺ�'] + $month['��ʪ��°�����������۴���Ĺ�'];
    $total_shutoku_kishu_kougu = $month['��񹩶�������۴���Ĺ�'] + $month['�������ʼ������۴���Ĺ�'];
    $total_shutoku_kishu_yukei = $total_shutoku_kishu_tate + $month['����ʪ�������۴���Ĺ�'] + $month['�������ּ������۴���Ĺ�'] + 
                                 $month['���ѱ��¶�������۴���Ĺ�'] + $total_shutoku_kishu_kougu + $month['�꡼���񻺼������۴���Ĺ�'];
    $total_shutoku_kishu_mukei = $month['���ò������������۴���Ĺ�'] + $month['�������Ѹ��������۴���Ĺ�'] + $month['���եȥ������������۴���Ĺ�'];
    $total_shutoku_kishu_all   = $total_shutoku_kishu_yukei + $total_shutoku_kishu_mukei;
    ///// �׻���̤�ϥå��������
    $tbody['tbody_shutoku_kishu_tate_total']  = number_format($total_shutoku_kishu_tate, $keta);
    $tbody['tbody_shutoku_kishu_kougu_total'] = number_format($total_shutoku_kishu_kougu, $keta);
    $tbody['tbody_shutoku_kishu_yukei_total'] = number_format($total_shutoku_kishu_yukei, $keta);
    $tbody['tbody_shutoku_kishu_mukei_total'] = number_format($total_shutoku_kishu_mukei, $keta);
    $tbody['tbody_shutoku_kishu_all']         = number_format($total_shutoku_kishu_all, $keta);
    /////////////////////////////////////////////////////////////////////// �������۴���Ĺ� END
    
    /////////////////////////////////////////////////////////////////////// �������۴������� START
    ///// �ƶ�ۤ򣳷奫��ޤǥϥå��������
    $tbody['tbody_shutoku_zou_tate']   = number_format($month['��ʪ�������۴�������'], $keta);
    $tbody['tbody_shutoku_zou_fuzoku'] = number_format($month['��ʪ��°�����������۴�������'], $keta);
    $tbody['tbody_shutoku_zou_kouti']  = number_format($month['����ʪ�������۴�������']  , $keta);
    $tbody['tbody_shutoku_zou_kikai']  = number_format($month['�������ּ������۴�������'], $keta);
    $tbody['tbody_shutoku_zou_sharyo'] = number_format($month['���ѱ��¶�������۴�������'], $keta);
    $tbody['tbody_shutoku_zou_kigu']   = number_format($month['��񹩶�������۴�������'], $keta);
    $tbody['tbody_shutoku_zou_jyuki']  = number_format($month['�������ʼ������۴�������'], $keta);
    $tbody['tbody_shutoku_zou_lease']  = number_format($month['�꡼���񻺼������۴�������'], $keta);
    $tbody['tbody_shutoku_zou_denwa']  = number_format($month['���ò������������۴�������'], $keta);
    $tbody['tbody_shutoku_zou_shise']  = number_format($month['�������Ѹ��������۴�������'], $keta);
    $tbody['tbody_shutoku_zou_soft']   = number_format($month['���եȥ������������۴�������'], $keta);
    ///// ��ʪ��ס����������ʷס�ͭ����ס�̵����ס����פ�׻�
    $total_shutoku_zou_tate  = $month['��ʪ�������۴�������'] + $month['��ʪ��°�����������۴�������'];
    $total_shutoku_zou_kougu = $month['��񹩶�������۴�������'] + $month['�������ʼ������۴�������'];
    $total_shutoku_zou_yukei = $total_shutoku_zou_tate + $month['����ʪ�������۴�������'] + $month['�������ּ������۴�������'] + 
                               $month['���ѱ��¶�������۴�������'] + $total_shutoku_zou_kougu + $month['�꡼���񻺼������۴�������'];
    $total_shutoku_zou_mukei = $month['���ò������������۴�������'] + $month['�������Ѹ��������۴�������'] + $month['���եȥ������������۴�������'];
    $total_shutoku_zou_all   = $total_shutoku_zou_yukei + $total_shutoku_zou_mukei;
    ///// �׻���̤�ϥå��������
    $tbody['tbody_shutoku_zou_tate_total']  = number_format($total_shutoku_zou_tate, $keta);
    $tbody['tbody_shutoku_zou_kougu_total'] = number_format($total_shutoku_zou_kougu, $keta);
    $tbody['tbody_shutoku_zou_yukei_total'] = number_format($total_shutoku_zou_yukei, $keta);
    $tbody['tbody_shutoku_zou_mukei_total'] = number_format($total_shutoku_zou_mukei, $keta);
    $tbody['tbody_shutoku_zou_all']         = number_format($total_shutoku_zou_all, $keta);
    /////////////////////////////////////////////////////////////////////// �������۴������� END
    
    /////////////////////////////////////////////////////////////////////// �������۴��渺�� START
    ///// �ƶ�ۤ򣳷奫��ޤǥϥå��������
    $tbody['tbody_shutoku_gen_tate']   = number_format($month['��ʪ�������۴��渺��'], $keta);
    $tbody['tbody_shutoku_gen_fuzoku'] = number_format($month['��ʪ��°�����������۴��渺��'], $keta);
    $tbody['tbody_shutoku_gen_kouti']  = number_format($month['����ʪ�������۴��渺��']  , $keta);
    $tbody['tbody_shutoku_gen_kikai']  = number_format($month['�������ּ������۴��渺��'], $keta);
    $tbody['tbody_shutoku_gen_sharyo'] = number_format($month['���ѱ��¶�������۴��渺��'], $keta);
    $tbody['tbody_shutoku_gen_kigu']   = number_format($month['��񹩶�������۴��渺��'], $keta);
    $tbody['tbody_shutoku_gen_jyuki']  = number_format($month['�������ʼ������۴��渺��'], $keta);
    $tbody['tbody_shutoku_gen_lease']  = number_format($month['�꡼���񻺼������۴��渺��'], $keta);
    $tbody['tbody_shutoku_gen_denwa']  = number_format($month['���ò������������۴��渺��'], $keta);
    $tbody['tbody_shutoku_gen_shise']  = number_format($month['�������Ѹ��������۴��渺��'], $keta);
    $tbody['tbody_shutoku_gen_soft']   = number_format($month['���եȥ������������۴��渺��'], $keta);
    ///// ��ʪ��ס����������ʷס�ͭ����ס�̵����ס����פ�׻�
    $total_shutoku_gen_tate  = $month['��ʪ�������۴��渺��'] + $month['��ʪ��°�����������۴��渺��'];
    $total_shutoku_gen_kougu = $month['��񹩶�������۴��渺��'] + $month['�������ʼ������۴��渺��'];
    $total_shutoku_gen_yukei = $total_shutoku_gen_tate + $month['����ʪ�������۴��渺��'] + $month['�������ּ������۴��渺��'] + 
                               $month['���ѱ��¶�������۴��渺��'] + $total_shutoku_gen_kougu + $month['�꡼���񻺼������۴��渺��'];
    $total_shutoku_gen_mukei = $month['���ò������������۴��渺��'] + $month['�������Ѹ��������۴��渺��'] + $month['���եȥ������������۴��渺��'];
    $total_shutoku_gen_all   = $total_shutoku_gen_yukei + $total_shutoku_gen_mukei;
    ///// �׻���̤�ϥå��������
    $tbody['tbody_shutoku_gen_tate_total']  = number_format($total_shutoku_gen_tate, $keta);
    $tbody['tbody_shutoku_gen_kougu_total'] = number_format($total_shutoku_gen_kougu, $keta);
    $tbody['tbody_shutoku_gen_yukei_total'] = number_format($total_shutoku_gen_yukei, $keta);
    $tbody['tbody_shutoku_gen_mukei_total'] = number_format($total_shutoku_gen_mukei, $keta);
    $tbody['tbody_shutoku_gen_all']         = number_format($total_shutoku_gen_all, $keta);
    /////////////////////////////////////////////////////////////////////// �������۴��渺�� END
    
    /////////////////////////////////////////////////////////////////////// �������۴����Ĺ� START
    ///// �ƴ����Ĺ��׻�
    // ��ʪ����ʪ��°����
    $tbody_shutoku_kima_tate   = $month['��ʪ�������۴���Ĺ�'] + $month['��ʪ�������۴�������'] - $month['��ʪ�������۴��渺��'];
    $tbody_shutoku_kima_fuzoku = $month['��ʪ��°�����������۴���Ĺ�'] + $month['��ʪ��°�����������۴�������'] - $month['��ʪ��°�����������۴��渺��'];
    // ��ʪ���
    $total_shutoku_kima_tate   = $tbody_shutoku_kima_tate + $tbody_shutoku_kima_fuzoku;
    // ����ʪ���������֡����ѱ��¶񡢴�񹩶񡢽�������
    $tbody_shutoku_kima_kouti  = $month['����ʪ�������۴���Ĺ�'] + $month['����ʪ�������۴�������'] - $month['����ʪ�������۴��渺��'];
    $tbody_shutoku_kima_kikai  = $month['�������ּ������۴���Ĺ�'] + $month['�������ּ������۴�������'] - $month['�������ּ������۴��渺��'];
    $tbody_shutoku_kima_sharyo = $month['���ѱ��¶�������۴���Ĺ�'] + $month['���ѱ��¶�������۴�������'] - $month['���ѱ��¶�������۴��渺��'];
    $tbody_shutoku_kima_kigu   = $month['��񹩶�������۴���Ĺ�'] + $month['��񹩶�������۴�������'] - $month['��񹩶�������۴��渺��'];
    $tbody_shutoku_kima_jyuki  = $month['�������ʼ������۴���Ĺ�'] + $month['�������ʼ������۴�������'] - $month['�������ʼ������۴��渺��'];
    // ��񹩶񡢽������ʹ��
    $total_shutoku_kima_kougu  = $tbody_shutoku_kima_kigu + $tbody_shutoku_kima_jyuki;
    // �꡼����
    $tbody_shutoku_kima_lease  = $month['�꡼���񻺼������۴���Ĺ�'] + $month['�꡼���񻺼������۴�������'] - $month['�꡼���񻺼������۴��渺��'];
    // ͭ�����
    $total_shutoku_kima_yukei  = $total_shutoku_kima_tate + $tbody_shutoku_kima_kouti + $tbody_shutoku_kima_kikai + 
                                 $tbody_shutoku_kima_sharyo + $total_shutoku_kima_kougu + $tbody_shutoku_kima_lease;
    // ���ò��������������Ѹ������եȥ�����
    $tbody_shutoku_kima_denwa  = $month['���ò������������۴���Ĺ�'] + $month['���ò������������۴�������'] - $month['���ò������������۴��渺��'];
    $tbody_shutoku_kima_shise  = $month['�������Ѹ��������۴���Ĺ�'] + $month['�������Ѹ��������۴�������'] - $month['�������Ѹ��������۴��渺��'];
    $tbody_shutoku_kima_soft   = $month['���եȥ������������۴���Ĺ�'] + $month['���եȥ������������۴�������'] - $month['���եȥ������������۴��渺��'];
    // ̵�����
    $total_shutoku_kima_mukei  = $tbody_shutoku_kima_denwa + $tbody_shutoku_kima_shise + $tbody_shutoku_kima_soft;
    // ����
    $total_shutoku_kima_all    = $total_shutoku_kima_yukei + $total_shutoku_kima_mukei;
    ///// �׻���̤�ϥå��������
    $tbody['tbody_shutoku_kima_tate']        = number_format($tbody_shutoku_kima_tate, $keta);
    $tbody['tbody_shutoku_kima_fuzoku']      = number_format($tbody_shutoku_kima_fuzoku, $keta);
    $tbody['tbody_shutoku_kima_tate_total']  = number_format($total_shutoku_kima_tate, $keta);
    $tbody['tbody_shutoku_kima_kouti']       = number_format($tbody_shutoku_kima_kouti, $keta);
    $tbody['tbody_shutoku_kima_kikai']       = number_format($tbody_shutoku_kima_kikai, $keta);
    $tbody['tbody_shutoku_kima_sharyo']      = number_format($tbody_shutoku_kima_sharyo, $keta);
    $tbody['tbody_shutoku_kima_kigu']        = number_format($tbody_shutoku_kima_kigu, $keta);
    $tbody['tbody_shutoku_kima_jyuki']       = number_format($tbody_shutoku_kima_jyuki, $keta);
    $tbody['tbody_shutoku_kima_kougu_total'] = number_format($total_shutoku_kima_kougu, $keta);
    $tbody['tbody_shutoku_kima_lease']       = number_format($tbody_shutoku_kima_lease, $keta);
    $tbody['tbody_shutoku_kima_yukei_total'] = number_format($total_shutoku_kima_yukei, $keta);
    $tbody['tbody_shutoku_kima_denwa']       = number_format($tbody_shutoku_kima_denwa, $keta);
    $tbody['tbody_shutoku_kima_shise']       = number_format($tbody_shutoku_kima_shise, $keta);
    $tbody['tbody_shutoku_kima_soft']        = number_format($tbody_shutoku_kima_soft, $keta);
    $tbody['tbody_shutoku_kima_mukei_total'] = number_format($total_shutoku_kima_mukei, $keta);
    $tbody['tbody_shutoku_kima_all']         = number_format($total_shutoku_kima_all, $keta);
    /////////////////////////////////////////////////////////////////////// �������۴����Ĺ� END
    
    /////////////////////////////////////////////////////////////////////// ����Ģ����� START
    ///// �ƶ�ۤ򣳷奫��ޤǥϥå��������
    $tbody['tbody_kishu_cho_tate']   = number_format($month['��ʪ����Ģ�����'], $keta);
    $tbody['tbody_kishu_cho_fuzoku'] = number_format($month['��ʪ��°��������Ģ�����'], $keta);
    $tbody['tbody_kishu_cho_kouti']  = number_format($month['����ʪ����Ģ�����']  , $keta);
    $tbody['tbody_kishu_cho_kikai']  = number_format($month['�������ִ���Ģ�����'], $keta);
    $tbody['tbody_kishu_cho_sharyo'] = number_format($month['���ѱ��¶����Ģ�����'], $keta);
    $tbody['tbody_kishu_cho_kigu']   = number_format($month['��񹩶����Ģ�����'], $keta);
    $tbody['tbody_kishu_cho_jyuki']  = number_format($month['�������ʴ���Ģ�����'], $keta);
    $tbody['tbody_kishu_cho_lease']  = number_format($month['�꡼���񻺴���Ģ�����'], $keta);
    $tbody['tbody_kishu_cho_denwa']  = number_format($month['���ò���������Ģ�����'], $keta);
    $tbody['tbody_kishu_cho_shise']  = number_format($month['�������Ѹ�����Ģ�����'], $keta);
    $tbody['tbody_kishu_cho_soft']   = number_format($month['���եȥ���������Ģ�����'], $keta);
    ///// ��ʪ��ס����������ʷס�ͭ����ס�̵����ס����פ�׻�
    $total_kishu_cho_tate  = $month['��ʪ����Ģ�����'] + $month['��ʪ��°��������Ģ�����'];
    $total_kishu_cho_kougu = $month['��񹩶����Ģ�����'] + $month['�������ʴ���Ģ�����'];
    $total_kishu_cho_yukei = $total_kishu_cho_tate + $month['����ʪ����Ģ�����'] + $month['�������ִ���Ģ�����'] + 
                             $month['���ѱ��¶����Ģ�����'] + $total_kishu_cho_kougu + $month['�꡼���񻺴���Ģ�����'];
    $total_kishu_cho_mukei = $month['���ò���������Ģ�����'] + $month['�������Ѹ�����Ģ�����'] + $month['���եȥ���������Ģ�����'];
    $total_kishu_cho_all   = $total_kishu_cho_yukei + $total_kishu_cho_mukei;
    ///// �׻���̤�ϥå��������
    $tbody['tbody_kishu_cho_tate_total']  = number_format($total_kishu_cho_tate, $keta);
    $tbody['tbody_kishu_cho_kougu_total'] = number_format($total_kishu_cho_kougu, $keta);
    $tbody['tbody_kishu_cho_yukei_total'] = number_format($total_kishu_cho_yukei, $keta);
    $tbody['tbody_kishu_cho_mukei_total'] = number_format($total_kishu_cho_mukei, $keta);
    $tbody['tbody_kishu_cho_all']         = number_format($total_kishu_cho_all, $keta);
    /////////////////////////////////////////////////////////////////////// ����Ģ����� END
    
    /////////////////////////////////////////////////////////////////////// ���������߷׳۴���Ĺ� START
    ///// �ƴ����Ĺ��׻�
    // ��ʪ����ʪ��°����
    $tbody_rui_kishu_tate   = $month['��ʪ�������۴���Ĺ�'] - $month['��ʪ����Ģ�����'];
    $tbody_rui_kishu_fuzoku = $month['��ʪ��°�����������۴���Ĺ�'] - $month['��ʪ��°��������Ģ�����'];
    // ��ʪ���
    $total_rui_kishu_tate   = $tbody_rui_kishu_tate + $tbody_rui_kishu_fuzoku;
    // ����ʪ���������֡����ѱ��¶񡢴�񹩶񡢽�������
    $tbody_rui_kishu_kouti  = $month['����ʪ�������۴���Ĺ�'] - $month['����ʪ����Ģ�����'];
    $tbody_rui_kishu_kikai  = $month['�������ּ������۴���Ĺ�'] - $month['�������ִ���Ģ�����'];
    $tbody_rui_kishu_sharyo = $month['���ѱ��¶�������۴���Ĺ�'] - $month['���ѱ��¶����Ģ�����'];
    $tbody_rui_kishu_kigu   = $month['��񹩶�������۴���Ĺ�'] - $month['��񹩶����Ģ�����'];
    $tbody_rui_kishu_jyuki  = $month['�������ʼ������۴���Ĺ�'] - $month['�������ʴ���Ģ�����'];
    // ��񹩶񡢽������ʹ��
    $total_rui_kishu_kougu  = $tbody_rui_kishu_kigu + $tbody_rui_kishu_jyuki;
    // �꡼����
    $tbody_rui_kishu_lease  = $month['�꡼���񻺼������۴���Ĺ�'] - $month['�꡼���񻺴���Ģ�����'];
    // ͭ�����
    $total_rui_kishu_yukei  = $total_rui_kishu_tate + $tbody_rui_kishu_kouti + $tbody_rui_kishu_kikai + 
                              $tbody_rui_kishu_sharyo + $total_rui_kishu_kougu + $tbody_rui_kishu_lease;
    // ���ò��������������Ѹ������եȥ�����
    $tbody_rui_kishu_denwa  = $month['���ò������������۴���Ĺ�'] - $month['���ò���������Ģ�����'];
    $tbody_rui_kishu_shise  = $month['�������Ѹ��������۴���Ĺ�'] - $month['�������Ѹ�����Ģ�����'];
    $tbody_rui_kishu_soft   = $month['���եȥ������������۴���Ĺ�'] - $month['���եȥ���������Ģ�����'];
    // ̵�����
    $total_rui_kishu_mukei  = $tbody_rui_kishu_denwa + $tbody_rui_kishu_shise + $tbody_rui_kishu_soft;
    // ����
    $total_rui_kishu_all    = $total_rui_kishu_yukei + $total_rui_kishu_mukei;
    ///// �׻���̤�ϥå��������
    $tbody['tbody_rui_kishu_tate']        = number_format($tbody_rui_kishu_tate, $keta);
    $tbody['tbody_rui_kishu_fuzoku']      = number_format($tbody_rui_kishu_fuzoku, $keta);
    $tbody['tbody_rui_kishu_tate_total']  = number_format($total_rui_kishu_tate, $keta);
    $tbody['tbody_rui_kishu_kouti']       = number_format($tbody_rui_kishu_kouti, $keta);
    $tbody['tbody_rui_kishu_kikai']       = number_format($tbody_rui_kishu_kikai, $keta);
    $tbody['tbody_rui_kishu_sharyo']      = number_format($tbody_rui_kishu_sharyo, $keta);
    $tbody['tbody_rui_kishu_kigu']        = number_format($tbody_rui_kishu_kigu, $keta);
    $tbody['tbody_rui_kishu_jyuki']       = number_format($tbody_rui_kishu_jyuki, $keta);
    $tbody['tbody_rui_kishu_kougu_total'] = number_format($total_rui_kishu_kougu, $keta);
    $tbody['tbody_rui_kishu_lease']       = number_format($tbody_rui_kishu_lease, $keta);
    $tbody['tbody_rui_kishu_yukei_total'] = number_format($total_rui_kishu_yukei, $keta);
    $tbody['tbody_rui_kishu_denwa']       = number_format($tbody_rui_kishu_denwa, $keta);
    $tbody['tbody_rui_kishu_shise']       = number_format($tbody_rui_kishu_shise, $keta);
    $tbody['tbody_rui_kishu_soft']        = number_format($tbody_rui_kishu_soft, $keta);
    $tbody['tbody_rui_kishu_mukei_total'] = number_format($total_rui_kishu_mukei, $keta);
    $tbody['tbody_rui_kishu_all']         = number_format($total_rui_kishu_all, $keta);
    /////////////////////////////////////////////////////////////////////// ���������߷׳۴���Ĺ� END
    
    /////////////////////////////////////////////////////////////////////// ���������߷׳۴��渺�� START
    ///// �ƶ�ۤ򣳷奫��ޤǥϥå��������
    $tbody['tbody_rui_gen_tate']   = number_format($month['��ʪ�߷׳۴��渺��'], $keta);
    $tbody['tbody_rui_gen_fuzoku'] = number_format($month['��ʪ��°�����߷׳۴��渺��'], $keta);
    $tbody['tbody_rui_gen_kouti']  = number_format($month['����ʪ�߷׳۴��渺��']  , $keta);
    $tbody['tbody_rui_gen_kikai']  = number_format($month['���������߷׳۴��渺��'], $keta);
    $tbody['tbody_rui_gen_sharyo'] = number_format($month['���ѱ��¶��߷׳۴��渺��'], $keta);
    $tbody['tbody_rui_gen_kigu']   = number_format($month['��񹩶��߷׳۴��渺��'], $keta);
    $tbody['tbody_rui_gen_jyuki']  = number_format($month['���������߷׳۴��渺��'], $keta);
    $tbody['tbody_rui_gen_lease']  = number_format($month['�꡼�����߷׳۴��渺��'], $keta);
    $tbody['tbody_rui_gen_denwa']  = number_format($month['���ò������߷׳۴��渺��'], $keta);
    $tbody['tbody_rui_gen_shise']  = number_format($month['�������Ѹ��߷׳۴��渺��'], $keta);
    $tbody['tbody_rui_gen_soft']   = number_format($month['���եȥ������߷׳۴��渺��'], $keta);
    ///// ��ʪ��ס����������ʷס�ͭ����ס�̵����ס����פ�׻�
    $total_rui_gen_tate  = $month['��ʪ�߷׳۴��渺��'] + $month['��ʪ��°�����߷׳۴��渺��'];
    $total_rui_gen_kougu = $month['��񹩶��߷׳۴��渺��'] + $month['���������߷׳۴��渺��'];
    $total_rui_gen_yukei = $total_rui_gen_tate + $month['����ʪ�߷׳۴��渺��'] + $month['���������߷׳۴��渺��'] + 
                           $month['���ѱ��¶��߷׳۴��渺��'] + $total_rui_gen_kougu + $month['�꡼�����߷׳۴��渺��'];
    $total_rui_gen_mukei = $month['���ò������߷׳۴��渺��'] + $month['�������Ѹ��߷׳۴��渺��'] + $month['���եȥ������߷׳۴��渺��'];
    $total_rui_gen_all   = $total_rui_gen_yukei + $total_rui_gen_mukei;
    ///// �׻���̤�ϥå��������
    $tbody['tbody_rui_gen_tate_total']  = number_format($total_rui_gen_tate, $keta);
    $tbody['tbody_rui_gen_kougu_total'] = number_format($total_rui_gen_kougu, $keta);
    $tbody['tbody_rui_gen_yukei_total'] = number_format($total_rui_gen_yukei, $keta);
    $tbody['tbody_rui_gen_mukei_total'] = number_format($total_rui_gen_mukei, $keta);
    $tbody['tbody_rui_gen_all']         = number_format($total_rui_gen_all, $keta);
    /////////////////////////////////////////////////////////////////////// ���������߷׳۴��渺�� END
    
    /////////////////////////////////////////////////////////////////////// ���������߷׳��������ѳ� START
    ///// �ƶ�ۤ򣳷奫��ޤǥϥå��������
    $tbody['tbody_rui_syo_tate']   = number_format($month['��ʪ�߷׳��������ѳ�'], $keta);
    $tbody['tbody_rui_syo_fuzoku'] = number_format($month['��ʪ��°�����߷׳��������ѳ�'], $keta);
    $tbody['tbody_rui_syo_kouti']  = number_format($month['����ʪ�߷׳��������ѳ�']  , $keta);
    $tbody['tbody_rui_syo_kikai']  = number_format($month['���������߷׳��������ѳ�'], $keta);
    $tbody['tbody_rui_syo_sharyo'] = number_format($month['���ѱ��¶��߷׳��������ѳ�'], $keta);
    $tbody['tbody_rui_syo_kigu']   = number_format($month['��񹩶��߷׳��������ѳ�'], $keta);
    $tbody['tbody_rui_syo_jyuki']  = number_format($month['���������߷׳��������ѳ�'], $keta);
    $tbody['tbody_rui_syo_lease']  = number_format($month['�꡼�����߷׳��������ѳ�'], $keta);
    $tbody['tbody_rui_syo_denwa']  = number_format($month['���ò������߷׳��������ѳ�'], $keta);
    $tbody['tbody_rui_syo_shise']  = number_format($month['�������Ѹ��߷׳��������ѳ�'], $keta);
    $tbody['tbody_rui_syo_soft']   = number_format($month['���եȥ������߷׳��������ѳ�'], $keta);
    ///// ��ʪ��ס����������ʷס�ͭ����ס�̵����ס����פ�׻�
    $total_rui_syo_tate  = $month['��ʪ�߷׳��������ѳ�'] + $month['��ʪ��°�����߷׳��������ѳ�'];
    $total_rui_syo_kougu = $month['��񹩶��߷׳��������ѳ�'] + $month['���������߷׳��������ѳ�'];
    $total_rui_syo_yukei = $total_rui_syo_tate + $month['����ʪ�߷׳��������ѳ�'] + $month['���������߷׳��������ѳ�'] + 
                           $month['���ѱ��¶��߷׳��������ѳ�'] + $total_rui_syo_kougu + $month['�꡼�����߷׳��������ѳ�'];
    $total_rui_syo_mukei = $month['���ò������߷׳��������ѳ�'] + $month['�������Ѹ��߷׳��������ѳ�'] + $month['���եȥ������߷׳��������ѳ�'];
    $total_rui_syo_all   = $total_rui_syo_yukei + $total_rui_syo_mukei;
    ///// �׻���̤�ϥå��������
    $tbody['tbody_rui_syo_tate_total']  = number_format($total_rui_syo_tate, $keta);
    $tbody['tbody_rui_syo_kougu_total'] = number_format($total_rui_syo_kougu, $keta);
    $tbody['tbody_rui_syo_yukei_total'] = number_format($total_rui_syo_yukei, $keta);
    $tbody['tbody_rui_syo_mukei_total'] = number_format($total_rui_syo_mukei, $keta);
    $tbody['tbody_rui_syo_all']         = number_format($total_rui_syo_all, $keta);
    /////////////////////////////////////////////////////////////////////// ���������߷׳��������ѳ� END
    
    /////////////////////////////////////////////////////////////////////// ���������߷׳۴����Ĺ� START
    ///// �ƴ����Ĺ��׻�
    // ��ʪ����ʪ��°����
    $tbody_rui_kima_tate   = $tbody_rui_kishu_tate - $month['��ʪ�߷׳۴��渺��'] + $month['��ʪ�߷׳��������ѳ�'];
    $tbody_rui_kima_fuzoku = $tbody_rui_kishu_fuzoku - $month['��ʪ��°�����߷׳۴��渺��'] + $month['��ʪ��°�����߷׳��������ѳ�'];
    // ��ʪ���
    $total_rui_kima_tate   = $tbody_rui_kima_tate + $tbody_rui_kima_fuzoku;
    // ����ʪ���������֡����ѱ��¶񡢴�񹩶񡢽�������
    $tbody_rui_kima_kouti  = $tbody_rui_kishu_kouti - $month['����ʪ�߷׳۴��渺��'] + $month['����ʪ�߷׳��������ѳ�'];
    $tbody_rui_kima_kikai  = $tbody_rui_kishu_kikai - $month['���������߷׳۴��渺��'] + $month['���������߷׳��������ѳ�'];
    $tbody_rui_kima_sharyo = $tbody_rui_kishu_sharyo - $month['���ѱ��¶��߷׳۴��渺��'] + $month['���ѱ��¶��߷׳��������ѳ�'];
    $tbody_rui_kima_kigu   = $tbody_rui_kishu_kigu - $month['��񹩶��߷׳۴��渺��'] + $month['��񹩶��߷׳��������ѳ�'];
    $tbody_rui_kima_jyuki  = $tbody_rui_kishu_jyuki - $month['���������߷׳۴��渺��'] + $month['���������߷׳��������ѳ�'];
    // ��񹩶񡢽������ʹ��
    $total_rui_kima_kougu  = $tbody_rui_kima_kigu + $tbody_rui_kima_jyuki;
    // �꡼����
    $tbody_rui_kima_lease  = $tbody_rui_kishu_lease - $month['�꡼�����߷׳۴��渺��'] + $month['�꡼�����߷׳��������ѳ�'];
    // ͭ�����
    $total_rui_kima_yukei  = $total_rui_kima_tate + $tbody_rui_kima_kouti + $tbody_rui_kima_kikai + 
                             $tbody_rui_kima_sharyo + $total_rui_kima_kougu + $tbody_rui_kima_lease;
    // ���ò��������������Ѹ������եȥ�����
    $tbody_rui_kima_denwa  = $tbody_rui_kishu_denwa - $month['���ò������߷׳۴��渺��'] + $month['���ò������߷׳��������ѳ�'];
    $tbody_rui_kima_shise  = $tbody_rui_kishu_shise - $month['�������Ѹ��߷׳۴��渺��'] + $month['�������Ѹ��߷׳��������ѳ�'];
    $tbody_rui_kima_soft   = $tbody_rui_kishu_soft - $month['���եȥ������߷׳۴��渺��'] + $month['���եȥ������߷׳��������ѳ�'];
    // ̵�����
    $total_rui_kima_mukei  = $tbody_rui_kima_denwa + $tbody_rui_kima_shise + $tbody_rui_kima_soft;
    // ����
    $total_rui_kima_all    = $total_rui_kima_yukei + $total_rui_kima_mukei;
    ///// �׻���̤�ϥå��������
    $tbody['tbody_rui_kima_tate']        = number_format($tbody_rui_kima_tate, $keta);
    $tbody['tbody_rui_kima_fuzoku']      = number_format($tbody_rui_kima_fuzoku, $keta);
    $tbody['tbody_rui_kima_tate_total']  = number_format($total_rui_kima_tate, $keta);
    $tbody['tbody_rui_kima_kouti']       = number_format($tbody_rui_kima_kouti, $keta);
    $tbody['tbody_rui_kima_kikai']       = number_format($tbody_rui_kima_kikai, $keta);
    $tbody['tbody_rui_kima_sharyo']      = number_format($tbody_rui_kima_sharyo, $keta);
    $tbody['tbody_rui_kima_kigu']        = number_format($tbody_rui_kima_kigu, $keta);
    $tbody['tbody_rui_kima_jyuki']       = number_format($tbody_rui_kima_jyuki, $keta);
    $tbody['tbody_rui_kima_kougu_total'] = number_format($total_rui_kima_kougu, $keta);
    $tbody['tbody_rui_kima_lease']       = number_format($tbody_rui_kima_lease, $keta);
    $tbody['tbody_rui_kima_yukei_total'] = number_format($total_rui_kima_yukei, $keta);
    $tbody['tbody_rui_kima_denwa']       = number_format($tbody_rui_kima_denwa, $keta);
    $tbody['tbody_rui_kima_shise']       = number_format($tbody_rui_kima_shise, $keta);
    $tbody['tbody_rui_kima_soft']        = number_format($tbody_rui_kima_soft, $keta);
    $tbody['tbody_rui_kima_mukei_total'] = number_format($total_rui_kima_mukei, $keta);
    $tbody['tbody_rui_kima_all']         = number_format($total_rui_kima_all, $keta);
    /////////////////////////////////////////////////////////////////////// ���������߷׳۴����Ĺ� END
    
    /////////////////////////////////////////////////////////////////////// ���ѻ�����Ģ����� START
    ///// �ƽ��ѻ�����Ģ����ۤ�׻�
    // ��ʪ����ʪ��°����
    $tbody_jyo_cho_tate   = $month['��ʪ�������۴��渺��'] - $month['��ʪ�߷׳۴��渺��'];
    $tbody_jyo_cho_fuzoku = $month['��ʪ��°�����������۴��渺��'] - $month['��ʪ��°�����߷׳۴��渺��'];
    // ��ʪ���
    $total_jyo_cho_tate   = $tbody_jyo_cho_tate + $tbody_jyo_cho_fuzoku;
    // ����ʪ���������֡����ѱ��¶񡢴�񹩶񡢽�������
    $tbody_jyo_cho_kouti  = $month['����ʪ�������۴��渺��'] - $month['����ʪ�߷׳۴��渺��'];
    $tbody_jyo_cho_kikai  = $month['�������ּ������۴��渺��'] - $month['���������߷׳۴��渺��'];
    $tbody_jyo_cho_sharyo = $month['���ѱ��¶�������۴��渺��'] - $month['���ѱ��¶��߷׳۴��渺��'];
    $tbody_jyo_cho_kigu   = $month['��񹩶�������۴��渺��'] - $month['��񹩶��߷׳۴��渺��'];
    $tbody_jyo_cho_jyuki  = $month['�������ʼ������۴��渺��'] - $month['���������߷׳۴��渺��'];
    // ��񹩶񡢽������ʹ��
    $total_jyo_cho_kougu  = $tbody_jyo_cho_kigu + $tbody_jyo_cho_jyuki;
    // �꡼����
    $tbody_jyo_cho_lease  = $month['�꡼���񻺼������۴��渺��'] - $month['�꡼�����߷׳۴��渺��'];
    // ͭ�����
    $total_jyo_cho_yukei  = $total_jyo_cho_tate + $tbody_jyo_cho_kouti + $tbody_jyo_cho_kikai + 
                            $tbody_jyo_cho_sharyo + $total_jyo_cho_kougu + $tbody_jyo_cho_lease;
    // ���ò��������������Ѹ������եȥ�����
    $tbody_jyo_cho_denwa  = $month['���ò������������۴��渺��'] - $month['���ò������߷׳۴��渺��'];
    $tbody_jyo_cho_shise  = $month['�������Ѹ��������۴��渺��'] - $month['�������Ѹ��߷׳۴��渺��'];
    $tbody_jyo_cho_soft   = $month['���եȥ������������۴��渺��'] - $month['���եȥ������߷׳۴��渺��'];
    // ̵�����
    $total_jyo_cho_mukei  = $tbody_jyo_cho_denwa + $tbody_jyo_cho_shise + $tbody_jyo_cho_soft;
    // ����
    $total_jyo_cho_all    = $total_jyo_cho_yukei + $total_jyo_cho_mukei;
    ///// �׻���̤�ϥå��������
    $tbody['tbody_jyo_cho_tate']        = number_format($tbody_jyo_cho_tate, $keta);
    $tbody['tbody_jyo_cho_fuzoku']      = number_format($tbody_jyo_cho_fuzoku, $keta);
    $tbody['tbody_jyo_cho_tate_total']  = number_format($total_jyo_cho_tate, $keta);
    $tbody['tbody_jyo_cho_kouti']       = number_format($tbody_jyo_cho_kouti, $keta);
    $tbody['tbody_jyo_cho_kikai']       = number_format($tbody_jyo_cho_kikai, $keta);
    $tbody['tbody_jyo_cho_sharyo']      = number_format($tbody_jyo_cho_sharyo, $keta);
    $tbody['tbody_jyo_cho_kigu']        = number_format($tbody_jyo_cho_kigu, $keta);
    $tbody['tbody_jyo_cho_jyuki']       = number_format($tbody_jyo_cho_jyuki, $keta);
    $tbody['tbody_jyo_cho_kougu_total'] = number_format($total_jyo_cho_kougu, $keta);
    $tbody['tbody_jyo_cho_lease']       = number_format($tbody_jyo_cho_lease, $keta);
    $tbody['tbody_jyo_cho_yukei_total'] = number_format($total_jyo_cho_yukei, $keta);
    $tbody['tbody_jyo_cho_denwa']       = number_format($tbody_jyo_cho_denwa, $keta);
    $tbody['tbody_jyo_cho_shise']       = number_format($tbody_jyo_cho_shise, $keta);
    $tbody['tbody_jyo_cho_soft']        = number_format($tbody_jyo_cho_soft, $keta);
    $tbody['tbody_jyo_cho_mukei_total'] = number_format($total_jyo_cho_mukei, $keta);
    $tbody['tbody_jyo_cho_all']         = number_format($total_jyo_cho_all, $keta);
    /////////////////////////////////////////////////////////////////////// ���ѻ�����Ģ����� END
    
    /////////////////////////////////////////////////////////////////////// ����Ģ��Ĺ� START
    ///// �ƴ���Ģ��Ĺ��׻�
    // ��ʪ����ʪ��°����
    $tbody_kima_cho_tate   = $tbody_shutoku_kima_tate - $tbody_rui_kima_tate;
    $tbody_kima_cho_fuzoku = $tbody_shutoku_kima_fuzoku - $tbody_rui_kima_fuzoku;
    // ��ʪ���
    $total_kima_cho_tate   = $tbody_kima_cho_tate + $tbody_kima_cho_fuzoku;
    // ����ʪ���������֡����ѱ��¶񡢴�񹩶񡢽�������
    $tbody_kima_cho_kouti  = $tbody_shutoku_kima_kouti - $tbody_rui_kima_kouti;
    $tbody_kima_cho_kikai  = $tbody_shutoku_kima_kikai - $tbody_rui_kima_kikai;
    $tbody_kima_cho_sharyo = $tbody_shutoku_kima_sharyo - $tbody_rui_kima_sharyo;
    $tbody_kima_cho_kigu   = $tbody_shutoku_kima_kigu - $tbody_rui_kima_kigu;
    $tbody_kima_cho_jyuki  = $tbody_shutoku_kima_jyuki - $tbody_rui_kima_jyuki;
    // ��񹩶񡢽������ʹ��
    $total_kima_cho_kougu  = $tbody_kima_cho_kigu + $tbody_kima_cho_jyuki;
    // �꡼����
    $tbody_kima_cho_lease  = $tbody_shutoku_kima_lease - $tbody_rui_kima_lease;
    // ͭ�����
    $total_kima_cho_yukei  = $total_kima_cho_tate + $tbody_kima_cho_kouti + $tbody_kima_cho_kikai + 
                             $tbody_kima_cho_sharyo + $total_kima_cho_kougu + $tbody_kima_cho_lease;
    // ���ò��������������Ѹ������եȥ�����
    $tbody_kima_cho_denwa  = $tbody_shutoku_kima_denwa - $tbody_rui_kima_denwa;
    $tbody_kima_cho_shise  = $tbody_shutoku_kima_shise - $tbody_rui_kima_shise;
    $tbody_kima_cho_soft   = $tbody_shutoku_kima_soft - $tbody_rui_kima_soft;
    // ̵�����
    $total_kima_cho_mukei  = $tbody_kima_cho_denwa + $tbody_kima_cho_shise + $tbody_kima_cho_soft;
    // ����
    $total_kima_cho_all    = $total_kima_cho_yukei + $total_kima_cho_mukei;
    ///// �׻���̤�ϥå��������
    $tbody['tbody_kima_cho_tate']        = number_format($tbody_kima_cho_tate, $keta);
    $tbody['tbody_kima_cho_fuzoku']      = number_format($tbody_kima_cho_fuzoku, $keta);
    $tbody['tbody_kima_cho_tate_total']  = number_format($total_kima_cho_tate, $keta);
    $tbody['tbody_kima_cho_kouti']       = number_format($tbody_kima_cho_kouti, $keta);
    $tbody['tbody_kima_cho_kikai']       = number_format($tbody_kima_cho_kikai, $keta);
    $tbody['tbody_kima_cho_sharyo']      = number_format($tbody_kima_cho_sharyo, $keta);
    $tbody['tbody_kima_cho_kigu']        = number_format($tbody_kima_cho_kigu, $keta);
    $tbody['tbody_kima_cho_jyuki']       = number_format($tbody_kima_cho_jyuki, $keta);
    $tbody['tbody_kima_cho_kougu_total'] = number_format($total_kima_cho_kougu, $keta);
    $tbody['tbody_kima_cho_lease']       = number_format($tbody_kima_cho_lease, $keta);
    $tbody['tbody_kima_cho_yukei_total'] = number_format($total_kima_cho_yukei, $keta);
    $tbody['tbody_kima_cho_denwa']       = number_format($tbody_kima_cho_denwa, $keta);
    $tbody['tbody_kima_cho_shise']       = number_format($tbody_kima_cho_shise, $keta);
    $tbody['tbody_kima_cho_soft']        = number_format($tbody_kima_cho_soft, $keta);
    $tbody['tbody_kima_cho_mukei_total'] = number_format($total_kima_cho_mukei, $keta);
    $tbody['tbody_kima_cho_all']         = number_format($total_kima_cho_all, $keta);
    /////////////////////////////////////////////////////////////////////// ����Ģ��Ĺ� END
/*
}
*/

/********** patTemplate ��Ф� ************/
include_once ( '../../../patTemplate/include/patTemplate.php' );
$tmpl = new patTemplate();

//  In diesem Verzeichnis liegen die Templates
$tmpl->setBasedir( 'templates' );

$tmpl->readTemplatesFromFile( 'shihanki_depreciation_statement_202001.templ.html' );

$tmpl->addVar('page', 'PAGE_TITLE'         , '�������ѻ񻺤���Ӹ��������������');
$tmpl->addVar('page', 'PAGE_MENU_SITE_URL' , $menu_site_script);
$tmpl->addVar('page', 'PAGE_UNIQUE'        , $uniq);
$tmpl->addVar('page', 'PAGE_RETURN_URL'    , $url_referer);
$tmpl->addVar('page', 'PAGE_CURRENT_URL'   , $current_script);
$tmpl->addVar('page', 'PAGE_SITE_VIEW'     , $site_view);
$tmpl->addVar('page', 'PAGE_HEADER_TITLE'  , "��{$ki}�� ��{$hanki}��Ⱦ�� �������ѻ񻺤���Ӹ��������������");
$tmpl->addVar('page', 'PAGE_HEADER_TODAY'  , $today);
$tmpl->addVar('page', 'OUT_CSS'            , $menu->out_css());
$tmpl->addVar('page', 'OUT_JSBASE'         , $menu->out_jsBaseClass());
$tmpl->addVar('page', 'OUT_TITLE_BORDER'   , $menu->out_title_border());

///// ɽ��ñ�̤�ƥ�ץ졼���ѿ��ؤ���Ͽ
if ($tani == 1) {
    $tmpl->addVar('page', 'en'       , 'selected');
    $tmpl->addVar('page', 'sen'      , '');
    $tmpl->addVar('page', 'jyuman'   , '');
    $tmpl->addVar('page', 'million'  , '');
} elseif ($tani == 1000) {
    $tmpl->addVar('page', 'en'       , '');
    $tmpl->addVar('page', 'sen'      , 'selected');
    $tmpl->addVar('page', 'jyuman'   , '');
    $tmpl->addVar('page', 'million'  , '');
} elseif ($tani == 100000) {
    $tmpl->addVar('page', 'en'       , '');
    $tmpl->addVar('page', 'sen'      , '');
    $tmpl->addVar('page', 'jyuman'   , 'selected');
    $tmpl->addVar('page', 'million'  , '');
} elseif ($tani == 1000000) {
    $tmpl->addVar('page', 'en'       , '');
    $tmpl->addVar('page', 'sen'      , '');
    $tmpl->addVar('page', 'jyuman'   , '');
    $tmpl->addVar('page', 'million'  , 'selected');
} else {
    $tmpl->addVar('page', 'en'       , '');
    $tmpl->addVar('page', 'sen'      , '');
    $tmpl->addVar('page', 'jyuman'   , '');
    $tmpl->addVar('page', 'million'  , 'selected');
}
///// �������ʲ��η��
if ($keta == 0) {
    $tmpl->addVar('page', 'zero' , 'selected');
    $tmpl->addVar('page', 'ichi' , '');
    $tmpl->addVar('page', 'san'  , '');
    $tmpl->addVar('page', 'roku' , '');
} elseif ($keta == 1) {
    $tmpl->addVar('page', 'zero' , '');
    $tmpl->addVar('page', 'ichi' , 'selected');
    $tmpl->addVar('page', 'san'  , '');
    $tmpl->addVar('page', 'roku' , '');
} elseif ($keta == 3) {
    $tmpl->addVar('page', 'zero' , '');
    $tmpl->addVar('page', 'ichi' , '');
    $tmpl->addVar('page', 'san'  , 'selected');
    $tmpl->addVar('page', 'roku' , '');
} elseif ($keta == 6) {
    $tmpl->addVar('page', 'zero' , '');
    $tmpl->addVar('page', 'ichi' , '');
    $tmpl->addVar('page', 'san'  , '');
    $tmpl->addVar('page', 'roku' , 'selected');
} else {
    $tmpl->addVar('page', 'zero' , '');
    $tmpl->addVar('page', 'ichi' , 'selected');
    $tmpl->addVar('page', 'san'  , '');
    $tmpl->addVar('page', 'roku' , '');
}

///// �ϥå�������� patTemplate ��Ÿ�� ���ץ顦��˥������Τ� tbody[]����������Ƥ���
$tmpl->addVars('tbody', $tbody);

//$tmpl->addVars( 'tbody_rows', array('TBODY_DSP_NUM' => $dsp_num) );
//$tmpl->addVars( 'tbody_rows', array('TBODY_FIELD0'  => $field0) );
//$tmpl->addVars( 'tbody_rows', array('TBODY_FIELD1'  => $field1) );


/********** Logic End   **********/

/////////// HTML Header ����Ϥ��ƥ���å��������
$menu->out_html_header();

//  Alle Templates ausgeben
$tmpl->displayParsedTemplate();
/************* patTemplate ��λ *****************/

?>
