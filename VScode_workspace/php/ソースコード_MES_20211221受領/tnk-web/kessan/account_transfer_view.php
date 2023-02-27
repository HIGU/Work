<?php
//////////////////////////////////////////////////////////////////////////////
// �»�״ط� �����������ɽ                                              //
// Copyright(C) 2018-2021 Norihisa.Ohya norihisa_ooya@nitto-kohki.co.jp     //
// Changed history                                                          //
// 2018/06/26 Created   account_transfer_view.php                           //
// 2018/10/17 19����2��Ⱦ���軻�η�̤�����ƽ���                           //
// 2019/01/10 ��ʧ�����Ǥ�ޥ��ʥ���19����3��Ⱦ��                           //
// 2019/04/09 ��¢�ʤ�2019/03�Υǡ������ѹ�                                 //
// 2019/05/17 ���դμ�����ˡ���ѹ�                                          //
// 2019/10/07 ��¢�ʤ�2019/09�Υǡ������ѹ�                                 //
// 2020/04/06 ��¢�ʤ�2020/03�Υǡ������ѹ�                                 //
// 2020/04/13 eCA�ѤΥǡ���ȴ�Ф����ɲ�                                     //
// 2020/06/25 �����������ٽ��ѤΥǡ������ɲá�20��ʬ��                      //
// 2020/06/30 �������������ٽ��ѤΥǡ������ɲá�20��ʬ��                    //
// 2020/07/08 ��¢�ʤ�2020/06�Υǡ������ѹ�                                 //
// 2021/01/13 �Ƽ�ǡ������ɲá�21��12��ʬ��                                //
// 2021/04/08 �Ƽ�ǡ������ɲá�21��3��ʬ��                                 //
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
    $menu->set_title("�� {$ki} �����ܷ軻�������ꡡ�ʡ��ܡ��ȡ��ء�ɽ");
} else {
    $menu->set_title("�� {$ki} ������{$hanki}��Ⱦ���������ꡡ�ʡ��ܡ��ȡ��ء�ɽ");
}

///// ����ڤ��¶�
// ����1100 00
/*
$res   = array();
$field = array();
$rows  = array();
$genkin_kin = 0;
$sum1 = '1101';
$sum2 = '00';
$query = sprintf("select rep_cri, rep_de, rep_cr from financial_report_cal where rep_ymd=%d and rep_summary1='%s' and rep_summary2='%s'", $yyyymm, $sum1, $sum2);
if ($rows=getResultWithField2($query, $field, $res) <= 0) {
    $genkin_kin = 0;
} else {
    $genkin_kin = $res[0][0] + $res[0][1] - $res[0][2];
}
*/
$res_k   = array();
$field_k = array();
$rows_k  = array();
$genkin_kin = 0;
$sum1 = '1101';
$sum2 = '00';
$query_k = sprintf("select rep_cri, rep_de, rep_cr from financial_report_cal where rep_ymd=%d and rep_summary1='%s' and rep_summary2='%s'", $nk_ki, $sum1, $sum2);
if ($rows_k=getResultWithField2($query_k, $field_k, $res_k) <= 0) {
    $genkin_kishu = 0;
} else {
    $genkin_kishu = $res_k[0][0];
}
$res   = array();
$field = array();
$rows  = array();
$query = sprintf("select SUM(rep_de), SUM(rep_cr) from financial_report_month where rep_ymd>=%d and rep_ymd<=%d and rep_summary1='%s'", $str_ym, $end_ym, $sum1);
if ($rows=getResultWithField2($query, $field, $res) <= 0) {
    $genkin_kin = $genkin_kishu;
} else {
    $genkin_kin = $genkin_kishu + ($res[0][0] - $res[0][1]);
}

// ����1103 00
/*
$res   = array();
$field = array();
$rows  = array();
$touza_kin = 0;
$sum1 = '1103';
$sum2 = '00';
$query = sprintf("select rep_cri, rep_de, rep_cr from financial_report_cal where rep_ymd=%d and rep_summary1='%s' and rep_summary2='%s'", $yyyymm, $sum1, $sum2);
if ($rows=getResultWithField2($query, $field, $res) <= 0) {
    $touza_kin = 0;
} else {
    $touza_kin = $res[0][0] + $res[0][1] - $res[0][2];
}
*/
$res_k   = array();
$field_k = array();
$rows_k  = array();
$touza_kin = 0;
$sum1 = '1103';
$sum2 = '00';
$query_k = sprintf("select rep_cri, rep_de, rep_cr from financial_report_cal where rep_ymd=%d and rep_summary1='%s' and rep_summary2='%s'", $nk_ki, $sum1, $sum2);
if ($rows_k=getResultWithField2($query_k, $field_k, $res_k) <= 0) {
    $touza_kishu = 0;
} else {
    $touza_kishu = $res_k[0][0];
}
$res   = array();
$field = array();
$rows  = array();
$query = sprintf("select SUM(rep_de), SUM(rep_cr) from financial_report_month where rep_ymd>=%d and rep_ymd<=%d and rep_summary1='%s'", $str_ym, $end_ym, $sum1);
if ($rows=getResultWithField2($query, $field, $res) <= 0) {
    $touza_kin = $touza_kishu;
} else {
    $touza_kin = $touza_kishu + ($res[0][0] - $res[0][1]);
}

// �����¶�1104 00 �ι�סʶ�ԥ����ɰ㤤��
/*
$res   = array();
$field = array();
$rows  = array();
$futsu_kin = 0;
$sum1 = '1104';
$sum2 = '00';
$query = sprintf("select SUM(rep_cri), SUM(rep_de), SUM(rep_cr) from financial_report_cal where rep_ymd=%d and rep_summary1='%s' and rep_summary2='%s'", $yyyymm, $sum1, $sum2);
if ($rows=getResultWithField2($query, $field, $res) <= 0) {
    $futsu_kin = 0;
} else {
    $futsu_kin = $res[0][0] + $res[0][1] - $res[0][2];
}
*/
$res_k   = array();
$field_k = array();
$rows_k  = array();
$futsu_kin = 0;
$sum1 = '1104';
$sum2 = '00';
$query_k = sprintf("select SUM(rep_cri) from financial_report_cal where rep_ymd=%d and rep_summary1='%s' and rep_summary2='%s'", $nk_ki, $sum1, $sum2);
if ($rows_k=getResultWithField2($query_k, $field_k, $res_k) <= 0) {
    $futsu_kishu = 0;
} else {
    $futsu_kishu = $res_k[0][0];
}

$res   = array();
$field = array();
$rows  = array();
$query = sprintf("select SUM(rep_de), SUM(rep_cr) from financial_report_month where rep_ymd>=%d and rep_ymd<=%d and rep_summary1='%s'", $str_ym, $end_ym, $sum1);
if ($rows=getResultWithField2($query, $field, $res) <= 0) {
    $futsu_kin = $futsu_kishu;
} else {
    $futsu_kin = $futsu_kishu + ($res[0][0] - $res[0][1]);
}

// ����¶�1106 00
/*
$res   = array();
$field = array();
$rows  = array();
$teiki_kin = 0;
$sum1 = '1106';
$sum2 = '00';
$query = sprintf("select SUM(rep_cri), SUM(rep_de), SUM(rep_cr) from financial_report_cal where rep_ymd=%d and rep_summary1='%s' and rep_summary2='%s'", $yyyymm, $sum1, $sum2);
if ($rows=getResultWithField2($query, $field, $res) <= 0) {
    $teiki_kin = 0;
} else {
    $teiki_kin = $res[0][0] + $res[0][1] - $res[0][2];
}
*/
$res_k   = array();
$field_k = array();
$rows_k  = array();
$teiki_kin = 0;
$sum1 = '1106';
$sum2 = '00';
$query_k = sprintf("select SUM(rep_cri), SUM(rep_de), SUM(rep_cr) from financial_report_cal where rep_ymd=%d and rep_summary1='%s' and rep_summary2='%s'", $nk_ki, $sum1, $sum2);
if ($rows_k=getResultWithField2($query_k, $field_k, $res_k) <= 0) {
    $teiki_kishu = 0;
} else {
    $teiki_kishu = $res_k[0][0];
}
$res   = array();
$field = array();
$rows  = array();
$query = sprintf("select SUM(rep_de), SUM(rep_cr) from financial_report_month where rep_ymd>=%d and rep_ymd<=%d and rep_summary1='%s'", $str_ym, $end_ym, $sum1);
if ($rows=getResultWithField2($query, $field, $res) <= 0) {
    $teiki_kin = $teiki_kishu;
} else {
    $teiki_kin = $teiki_kishu + ($res[0][0] - $res[0][1]);
}

// ����ڤ��¶��פη׻�
$genyo_total_kin = $genkin_kin + $touza_kin + $futsu_kin + $teiki_kin;

///// �߸�
// ����1404 00
/*
$res   = array();
$field = array();
$rows  = array();
$seihin_kin = 0;
$sum1 = '1404';
$sum2 = '00';
$query = sprintf("select rep_cri, rep_de, rep_cr from financial_report_cal where rep_ymd=%d and rep_summary1='%s' and rep_summary2='%s'", $yyyymm, $sum1, $sum2);
if ($rows=getResultWithField2($query, $field, $res) <= 0) {
    $seihin_kin = 0;
} else {
    $seihin_kin = $res[0][0] + $res[0][1] - $res[0][2];
}
*/
$res_k   = array();
$field_k = array();
$rows_k  = array();
$seihin_kin = 0;
$sum1 = '1404';
$sum2 = '00';
$query_k = sprintf("select rep_cri, rep_de, rep_cr from financial_report_cal where rep_ymd=%d and rep_summary1='%s' and rep_summary2='%s'", $nk_ki, $sum1, $sum2);
if ($rows_k=getResultWithField2($query_k, $field_k, $res_k) <= 0) {
    $seihin_kishu = 0;
} else {
    $seihin_kishu = $res_k[0][0];
}
$res   = array();
$field = array();
$rows  = array();
$query = sprintf("select SUM(rep_de), SUM(rep_cr) from financial_report_month where rep_ymd>=%d and rep_ymd<=%d and rep_summary1='%s'", $str_ym, $end_ym, $sum1);
if ($rows=getResultWithField2($query, $field, $res) <= 0) {
    $seihin_kin = $seihin_kishu;
} else {
    $seihin_kin = $seihin_kishu + ($res[0][0] - $res[0][1]);
}

// ���ʻų���1405 00
/*
$res   = array();
$field = array();
$rows  = array();
$seihinsi_kin = 0;
$sum1 = '1405';
$sum2 = '00';
$query = sprintf("select rep_cri, rep_de, rep_cr from financial_report_cal where rep_ymd=%d and rep_summary1='%s' and rep_summary2='%s'", $yyyymm, $sum1, $sum2);
if ($rows=getResultWithField2($query, $field, $res) <= 0) {
    $seihinsi_kin = 0;
} else {
    $seihinsi_kin = $res[0][0] + $res[0][1] - $res[0][2];
}
*/
$res_k   = array();
$field_k = array();
$rows_k  = array();
$seihinsi_kin = 0;
$sum1 = '1405';
$sum2 = '00';
$query_k = sprintf("select rep_cri, rep_de, rep_cr from financial_report_cal where rep_ymd=%d and rep_summary1='%s' and rep_summary2='%s'", $nk_ki, $sum1, $sum2);
if ($rows_k=getResultWithField2($query_k, $field_k, $res_k) <= 0) {
    $seihinsi_kishu = 0;
} else {
    $seihinsi_kishu = $res_k[0][0];
}
$res   = array();
$field = array();
$rows  = array();
$query = sprintf("select SUM(rep_de), SUM(rep_cr) from financial_report_month where rep_ymd>=%d and rep_ymd<=%d and rep_summary1='%s'", $str_ym, $end_ym, $sum1);
if ($rows=getResultWithField2($query, $field, $res) <= 0) {
    $seihinsi_kin = $seihinsi_kishu;
} else {
    $seihinsi_kin = $seihinsi_kishu + ($res[0][0] - $res[0][1]);
}

// ����1406 00
/*
$res   = array();
$field = array();
$rows  = array();
$buhin_kin = 0;
$sum1 = '1406';
$sum2 = '00';
$query = sprintf("select rep_cri, rep_de, rep_cr from financial_report_cal where rep_ymd=%d and rep_summary1='%s' and rep_summary2='%s'", $yyyymm, $sum1, $sum2);
if ($rows=getResultWithField2($query, $field, $res) <= 0) {
    $buhin_kin = 0;
} else {
    $buhin_kin = $res[0][0] + $res[0][1] - $res[0][2];
}
*/
$res_k   = array();
$field_k = array();
$rows_k  = array();
$buhin_kin = 0;
$sum1 = '1406';
$sum2 = '00';
$query_k = sprintf("select rep_cri, rep_de, rep_cr from financial_report_cal where rep_ymd=%d and rep_summary1='%s' and rep_summary2='%s'", $nk_ki, $sum1, $sum2);
if ($rows_k=getResultWithField2($query_k, $field_k, $res_k) <= 0) {
    $buhin_kishu = 0;
} else {
    $buhin_kishu = $res_k[0][0];
}
$res   = array();
$field = array();
$rows  = array();
$query = sprintf("select SUM(rep_de), SUM(rep_cr) from financial_report_month where rep_ymd>=%d and rep_ymd<=%d and rep_summary1='%s'", $str_ym, $end_ym, $sum1);
if ($rows=getResultWithField2($query, $field, $res) <= 0) {
    $buhin_kin = $buhin_kishu;
} else {
    $buhin_kin = $buhin_kishu + ($res[0][0] - $res[0][1]);
}

// ���ʻų���1407 30
/*
$res   = array();
$field = array();
$rows  = array();
$buhinsi_kin = 0;
$sum1 = '1407';
$sum2 = '30';
$query = sprintf("select rep_cri, rep_de, rep_cr from financial_report_cal where rep_ymd=%d and rep_summary1='%s' and rep_summary2='%s'", $yyyymm, $sum1, $sum2);
if ($rows=getResultWithField2($query, $field, $res) <= 0) {
    $buhinsi_kin = 0;
} else {
    $buhinsi_kin = $res[0][0] + $res[0][1] - $res[0][2];
}
*/
$res_k   = array();
$field_k = array();
$rows_k  = array();
$buhinsi_kin = 0;
$sum1 = '1407';
$sum2 = '30';
$query_k = sprintf("select rep_cri, rep_de, rep_cr from financial_report_cal where rep_ymd=%d and rep_summary1='%s' and rep_summary2='%s'", $nk_ki, $sum1, $sum2);
if ($rows_k=getResultWithField2($query_k, $field_k, $res_k) <= 0) {
    $buhinsi_kishu = 0;
} else {
    $buhinsi_kishu = $res_k[0][0];
}
$res   = array();
$field = array();
$rows  = array();
$query = sprintf("select SUM(rep_de), SUM(rep_cr) from financial_report_month where rep_ymd>=%d and rep_ymd<=%d and rep_summary1='%s'", $str_ym, $end_ym, $sum1);
if ($rows=getResultWithField2($query, $field, $res) <= 0) {
    $buhinsi_kin = $buhinsi_kishu;
} else {
    $buhinsi_kin = $buhinsi_kishu + ($res[0][0] - $res[0][1]);
}

// ������1408 00
/*
$res   = array();
$field = array();
$rows  = array();
$genzai_kin = 0;
$sum1 = '1408';
$sum2 = '00';
$query = sprintf("select rep_cri, rep_de, rep_cr from financial_report_cal where rep_ymd=%d and rep_summary1='%s' and rep_summary2='%s'", $yyyymm, $sum1, $sum2);
if ($rows=getResultWithField2($query, $field, $res) <= 0) {
    $genzai_kin = 0;
} else {
    $genzai_kin = $res[0][0] + $res[0][1] - $res[0][2];
}
*/
$res_k   = array();
$field_k = array();
$rows_k  = array();
$genzai_kin = 0;
$sum1 = '1408';
$sum2 = '00';
$query_k = sprintf("select rep_cri, rep_de, rep_cr from financial_report_cal where rep_ymd=%d and rep_summary1='%s' and rep_summary2='%s'", $nk_ki, $sum1, $sum2);
if ($rows_k=getResultWithField2($query_k, $field_k, $res_k) <= 0) {
    $genzai_kishu = 0;
} else {
    $genzai_kishu = $res_k[0][0];
}
$res   = array();
$field = array();
$rows  = array();
$query = sprintf("select SUM(rep_de), SUM(rep_cr) from financial_report_month where rep_ymd>=%d and rep_ymd<=%d and rep_summary1='%s'", $str_ym, $end_ym, $sum1);
if ($rows=getResultWithField2($query, $field, $res) <= 0) {
    $genzai_kin = $genzai_kishu;
} else {
    $genzai_kin = $genzai_kishu + ($res[0][0] - $res[0][1]);
}

// ����¾��ê����1409 �ι��
/*
$res   = array();
$field = array();
$rows  = array();
$sonotatana_kin = 0;
$sum1 = '1409';
$sum2 = '00';
$query = sprintf("select SUM(rep_cri), SUM(rep_de), SUM(rep_cr) from financial_report_cal where rep_ymd=%d and rep_summary1='%s'", $yyyymm, $sum1);
if ($rows=getResultWithField2($query, $field, $res) <= 0) {
    $sonotatana_kin = 0;
} else {
    $sonotatana_kin = $res[0][0] + $res[0][1] - $res[0][2];
}
*/
$res_k   = array();
$field_k = array();
$rows_k  = array();
$sonotatana_kin = 0;
$sum1 = '1409';
$sum2 = '00';
$query_k = sprintf("select SUM(rep_cri), SUM(rep_de), SUM(rep_cr) from financial_report_cal where rep_ymd=%d and rep_summary1='%s'", $nk_ki, $sum1);
if ($rows_k=getResultWithField2($query_k, $field_k, $res_k) <= 0) {
    $sonotatana_kishu = 0;
} else {
    $sonotatana_kishu = $res_k[0][0];
}
$res   = array();
$field = array();
$rows  = array();
$query = sprintf("select SUM(rep_de), SUM(rep_cr) from financial_report_month where rep_ymd>=%d and rep_ymd<=%d and rep_summary1='%s'", $str_ym, $end_ym, $sum1);
if ($rows=getResultWithField2($query, $field, $res) <= 0) {
    $sonotatana_kin = $sonotatana_kishu;
} else {
    $sonotatana_kin = $sonotatana_kishu + ($res[0][0] - $res[0][1]);
}

// ��¢�� �ǡ����Ϥʤ��ΤǷ���߼��о�ɽ��Ʊ������ľ������
// ɾ���ڲ��������� �ǡ�����̵���ΤǷ���߼��о�ɽ��Ʊ������ľ������
// ���ʻų����٤����� ��λ������� ��פ�OK 
// ̵������񻺤μ������� ����Ĺ⡢�������á����渺�������
// ������̴����������������
$chozo_kin       = 0;
$hyoka_buhin_kin = 0;
$hyoka_zai_kin   = 0;
$tana_kou_kin    = 0;
$tana_gai_kin    = 0;
$tana_ken_kin    = 0;

$den_kishu_kin   = 0;
$shi_kishu_kin   = 0;
$sft_kishu_kin   = 0;
$den_zou_kin     = 0;
$shi_zou_kin     = 0;
$sft_zou_kin     = 0;
$den_gen_kin     = 0;
$shi_gen_kin     = 0;
$sft_gen_kin     = 0;

if ($yyyymm == 201609) {
    $chozo_kin  = 27994100;    // ���� ��¤���� 26,979,200�� ��¢�� ���� 1,014,800�ߡ�151,200�ߤ�447,000�ߤ�416,700�ߡ�
}
if ($yyyymm >= 201610 && $yyyymm <= 201610) {
    $chozo_kin  = 27994100;    // ���� ��¤���� 26,979,200�� ��¢�� ���� 1,014,800�ߡ�151,200�ߤ�447,000�ߤ�416,700�ߡ�
}
if ($yyyymm >= 201611 && $yyyymm <= 201611) {
    $chozo_kin  = 28060500;    // ���� ��¤���� 26,979,200�� ��¢�� ���� 1,081,300�ߡ�151,200�ߤ�447,000�ߤ�416,700�ߤ�66,500�ߡ�
}
if ($yyyymm >= 201612 && $yyyymm <= 201701) {
    $chozo_kin  = 28118600;    // ���� ��¤���� 26,979,200�� ��¢�� ���� 1,139,400�ߡ�151,200�ߤ�447,000�ߤ�416,700�ߤ�66,500�ߤ�58,000�ߡ�
}
if ($yyyymm >= 201702 && $yyyymm <= 201702) {
    $chozo_kin  = 27701900;    // ���� ��¤���� 26,979,200�� ��¢�� ���� 722,700�ߡ�151,200�ߤ�447,000�ߤ�66,500�ߤ�58,000�ߡ�
}
if ($yyyymm == 201703) {
    $chozo_kin  = 27170800;    // ���� ��¤���� 26,448,100�� ��¢�� ���� 722,700�ߡ�151,200�ߤ�447,000�ߤ�66,500�ߤ�58,000�ߡ�
}
if ($yyyymm >= 201704 && $yyyymm <= 201708) {
    $chozo_kin  = 27170800;    // ���� ��¤���� 26,448,100�� ��¢�� ���� 722,700�ߡ�151,200�ߤ�447,000�ߤ�66,500�ߤ�58,000�ߡ�
}
if ($yyyymm == 201709) {
    $chozo_kin  = 31331800;    // ���� ��¤���� 30,609,100�� ��¢�� ���� 722,700�ߡ�151,200�ߤ�447,000�ߤ�66,500�ߤ�58,000�ߡ�
}
if ($yyyymm >= 201710 && $yyyymm <= 201802) {
    $chozo_kin  = 31331800;    // ���� ��¤���� 30,609,100�� ��¢�� ���� 722,700�ߡ�151,200�ߤ�447,000�ߤ�66,500�ߤ�58,000�ߡ�
}
if ($yyyymm == 201803) {
    $chozo_kin  = 30723300;    // ���� ��¤���� 29,523,100�� ��¢�� ���� 1,200,200�ߡ�151,200�ߤ�447,000�ߤ�66,500�ߤ�58,000�ߤ�477,500�ߡ�
}

if ($yyyymm >= 201804 && $yyyymm <= 201808) {
    $chozo_kin  = 30723300;    // ���� ��¤���� 29,523,100�� ��¢�� ���� 1,200,200�ߡ�151,200�ߤ�447,000�ߤ�66,500�ߤ�58,000�ߤ�477,500�ߡ�
}

if ($yyyymm >= 201809 && $yyyymm <= 201902) {
    $chozo_kin  = 31076300;    // ���� ��¤���� 29,528,100�� ��¢�� ���� 1,548,200�ߡ�151,200�ߤ�447,000�ߤ�66,500�ߤ�58,000�ߤ�477,500�ߤ�348,000�ߡ�
}
if ($yyyymm == 201903) {
    $chozo_kin  = 29013600;    // ���� ��¤���� 27,332,900�� ��¢�� ������ 1,680,700�ߡ�151,200�ߤ�447,000�ߤ�58,000�ߤ�477,500�ߤ�348,000�ߤ�39,800�ߤ�5����
}
if ($yyyymm >= 201904 && $yyyymm <= 201908) {
    $chozo_kin  = 29013600;    // ���� ��¤���� 27,332,900�� ��¢�� ������ 1,680,700�ߡ�151,200�ߤ�447,000�ߤ�58,000�ߤ�477,500�ߤ�348,000�ߤ�39,800�ߤ�5����
}
if ($yyyymm >= 201909 && $yyyymm <= 202002) {
    $chozo_kin  = 30711100;    // ���� ��¤���� 29,030,400�� ��¢�� ������ 1,680,700�ߡ�151,200�ߤ�447,000�ߤ�58,000�ߤ�477,500�ߤ�348,000�ߤ�39,800�ߤ�5����
}

// 2020/03
if ($yyyymm >= 202003 && $yyyymm <= 202005) {
    $chozo_kin  = 31847700;    // ���� ��¤���� 30,225,000�� ��¢�� ���� 1,622,700�ߡ�151,200�ߤ�447,000�ߤ�477,500�ߤ�348,000�ߤ�39,800�ߤ�5����
}
// 2020/03
if ($yyyymm >= 202006 && $yyyymm <= 202008) {
    $chozo_kin  = 34437700;    // ���� ��¤���� 30,225,000�� ��¢�� ���� 4,212,700�ߡ�151,200�ߤ�447,000�ߤ�477,500�ߤ�348,000�ߤ�39,800�ߤ�5����2,590,000�ߡ�
}
// 2020/10/07 �ɲ� ��ʧ�⢪ê����
if ($yyyymm >= 202009 && $yyyymm <= 202102) {
    $chozo_kin  = 35225500;    // ���� ��¤���� 31,012,800�� ��¢�� ���� 4,212,700�ߡ�151,200�ߤ�447,000�ߤ�477,500�ߤ�348,000�ߤ�39,800�ߤ�5����2,590,000�ߡ�
}
// 2021/04/08 �ɲ� ��ʧ�⢪ê����
if ($yyyymm >= 202103 && $yyyymm <= 202105) {
    $chozo_kin  = 34201500;    // ���� ��¤���� 29,988,800�� ��¢�� ���� 4,212,700�ߡ�151,200�ߤ�447,000�ߤ�477,500�ߤ�348,000�ߤ�39,800�ߤ�5����2,590,000�ߡ�
}
// 2021/07/07 �ɲ� ��ʧ�⢪ê����
if ($yyyymm >= 202106 && $yyyymm <= 202108) {
    $chozo_kin  = 34390591;    // ���� ��¤���� 29,988,800�� ��¢�� ���� 4,401,791�ߡ�151,200�ߤ�447,000�ߤ�477,500�ߤ�348,000�ߤ�39,800�ߤ�5����2,590,000�ߤ�189,091�ߡ�
}
// 2021/07/07 �ɲ� ��ʧ�⢪ê����
if ($yyyymm >= 202109 && $yyyymm <= 202202) {
    $chozo_kin  = 35918291;    // ���� ��¤���� 31,516,500�� ��¢�� ���� 4,401,791�ߡ�151,200�ߤ�447,000�ߤ�477,500�ߤ�348,000�ߤ�39,800�ߤ�5����2,590,000�ߤ�189,091�ߡ�
}

// ê��������
if ($yyyymm >= 201806 && $yyyymm <= 201808) {
    $hyoka_buhin_kin = 24145944;    // ɾ���ڲ��� ���� 24,145,944��
    $hyoka_zai_kin   = 5054790;     // ɾ���ڲ��� ������ 5,054,790��
    $tana_kou_kin    = 58478522;    // ����ų� 2018/06
    $tana_gai_kin    = 115024519;   // ����ų� 2018/06
    $tana_ken_kin    = 6794210;     // �����ų� 2018/06
}
if ($yyyymm >= 201809 && $yyyymm <= 201811) {
    $hyoka_buhin_kin = 20982681;    // ɾ���ڲ��� ���� 20,982,681��
    $hyoka_zai_kin   = 3245692;     // ɾ���ڲ��� ������ 3,245,692��
    $tana_kou_kin    = 39056743;    // ����ų� 2018/09
    $tana_gai_kin    = 106305243;   // ����ų� 2018/09
    $tana_ken_kin    = 8507486;     // �����ų� 2018/09
}
if ($yyyymm >= 201812 && $yyyymm <= 201902) {
    $hyoka_buhin_kin = 21353386;    // ɾ���ڲ��� ���� 21,353,386��
    $hyoka_zai_kin   = 4301424;     // ɾ���ڲ��� ������ 4,301,424��
    $tana_kou_kin    = 51762589;    // ����ų� 2018/12
    $tana_gai_kin    = 120393767;   // ����ų� 2018/12
    $tana_ken_kin    = 18413966;    // �����ų� 2018/12
}

if ($yyyymm >= 201903 && $yyyymm <= 201905) {
    $hyoka_buhin_kin = 27099309;    // ɾ���ڲ��� ���� 27,099,309��
    $hyoka_zai_kin   = 3837098;     // ɾ���ڲ��� ������ 3,837,098��
    $tana_kou_kin    = 43499362;    // ����ų� 2019/03
    $tana_gai_kin    = 118595019;   // ����ų� 2019/03
    $tana_ken_kin    = 6129984;     // �����ų� 2019/03
}
if ($yyyymm >= 201906 && $yyyymm <= 201908) {
    $hyoka_buhin_kin = 27338977;    // ɾ���ڲ��� ���� 27,338,977��
    $hyoka_zai_kin   = 3632576;     // ɾ���ڲ��� ������ 3,632,576��
    $tana_kou_kin    = 44994415;    // ����ų� 2019/06
    $tana_gai_kin    = 120740158;   // ����ų� 2019/06
    $tana_ken_kin    = 7774737;     // �����ų� 2019/06
}
if ($yyyymm >= 201909 && $yyyymm <= 201911) {
    $hyoka_buhin_kin = 26052650;    // ɾ���ڲ��� ���� 26,052,650��
    $hyoka_zai_kin   = 3462809;     // ɾ���ڲ��� ������ 3,462,809��
    $tana_kou_kin    = 34775430;    // ����ų� 2019/09
    $tana_gai_kin    = 86308322;    // ����ų� 2019/09
    $tana_ken_kin    = 38770378;    // �����ų� 2019/09
}
if ($yyyymm >= 201912 && $yyyymm <= 202002) {
    $hyoka_buhin_kin = 25648144;    // ɾ���ڲ��� ���� 25,648,144��
    $hyoka_zai_kin   = 3145474;     // ɾ���ڲ��� ������ 4,301,424��
    $tana_kou_kin    = 48147770;    // ����ų� 2019/12
    $tana_gai_kin    = 144697973;   // ����ų� 2019/12
    $tana_ken_kin    = 6235679;     // �����ų� 2019/12
}
if ($yyyymm >= 202003 && $yyyymm <= 202005) {
    $hyoka_buhin_kin = 22146591;    // ɾ���ڲ��� ���� 22,146,591��
    $hyoka_zai_kin   = 2936551;     // ɾ���ڲ��� ������ 2,936,551��
    $tana_kou_kin    = 43412814;    // ����ų� 2020/03
    $tana_gai_kin    = 144834707;   // ����ų� 2020/03
    $tana_ken_kin    = 2043234;     // �����ų� 2020/03
}
if ($yyyymm >= 202006 && $yyyymm <= 202008) {
    $hyoka_buhin_kin = 29663899;    // ɾ���ڲ��� ���� 29,663,899��
    $hyoka_zai_kin   = 2541727;     // ɾ���ڲ��� ������ 2,541,727��
    $tana_kou_kin    = 33562592;    // ����ų� 2020/06
    $tana_gai_kin    = 157623908;   // ����ų� 2020/06
    $tana_ken_kin    = 4483191;     // �����ų� 2020/06
}
if ($yyyymm >= 202009 && $yyyymm <= 202011) {
    $hyoka_buhin_kin = 18889691;    // ɾ���ڲ��� ���� 18,889,691��
    $hyoka_zai_kin   = 2247663;     // ɾ���ڲ��� ������ 2,247,663��
    $tana_kou_kin    = 44194009;    // ����ų� 2020/09
    $tana_gai_kin    = 149995875;   // ����ų� 2020/09
    $tana_ken_kin    = 6145842;     // �����ų� 2020/09
}
if ($yyyymm >= 202012 && $yyyymm <= 202102) {
    $hyoka_buhin_kin = 20714112;    // ɾ���ڲ��� ���� 20,714,112��
    $hyoka_zai_kin   = 3098026;     // ɾ���ڲ��� ������ 3,098,026��
    $tana_kou_kin    = 39886443;    // ����ų� 2020/12
    $tana_gai_kin    = 180087279;   // ����ų� 2020/12
    $tana_ken_kin    = 3318087;     // �����ų� 2020/12
}
if ($yyyymm >= 202103 && $yyyymm <= 202105) {
    $hyoka_buhin_kin = 23313658;    // ɾ���ڲ��� ���� 23,313,658��
    $hyoka_zai_kin   = 3853278;     // ɾ���ڲ��� ������ 3,853,278��
    $tana_kou_kin    = 52966381;    // ����ų� 2021/03
    $tana_gai_kin    = 178610899;   // ����ų� 2021/03
    $tana_ken_kin    = 4799597;     // �����ų� 2021/03
}
if ($yyyymm >= 202106 && $yyyymm <= 202108) {
    $hyoka_buhin_kin = 29379248;    // ɾ���ڲ��� ���� 29,379,248��
    $hyoka_zai_kin   = 3226918;     // ɾ���ڲ��� ������ 3,226,918��
    $tana_kou_kin    = 53398839;    // ����ų� 2021/06
    $tana_gai_kin    = 191936510;   // ����ų� 2021/06
    $tana_ken_kin    = 3863607;     // �����ų� 2021/06
}
if ($yyyymm >= 202109 && $yyyymm <= 202111) {
    $hyoka_buhin_kin = 27697190;    // ɾ���ڲ��� ���� 27,697,190��
    $hyoka_zai_kin   = 3183503;     // ɾ���ڲ��� ������ 3,183,503��
    $tana_kou_kin    = 35205657;    // ����ų� 2021/09
    $tana_gai_kin    = 174850313;   // ����ų� 2021/09
    $tana_ken_kin    = 0;           // �����ų� 2021/09
}


// ̵������񻺤μ������� ����Ĺ⡢�������á����渺��������
if ($yyyymm >= 201803 && $yyyymm <= 201805) {
    $den_kishu_kin   = 1224000;     // ���ô���Ĺ�    1,224,000��
    $shi_kishu_kin   = 13120400;    // ���ߴ���Ĺ�   13,120,400��
    $sft_kishu_kin   = 16163122;    // ���եȴ���Ĺ� 16,163,122��
    $den_zou_kin     = 0;           // ���ô�������            0��
    $shi_zou_kin     = 0;           // ���ߴ�������            0��
    $sft_zou_kin     = 7565000;     // ���եȴ�������  7,565,000��
    $den_gen_kin     = 0;           // ���ô��渺��            0��
    $shi_gen_kin     = 0;           // ���ߴ��渺��            0��
    $sft_gen_kin     = 0;           // ���եȴ��渺��          0��
}
if ($yyyymm >= 201806 && $yyyymm <= 201808) {
    $den_kishu_kin   = 1224000;     // ���ô���Ĺ�    1,224,000��
    $shi_kishu_kin   = 13120400;    // ���ߴ���Ĺ�   13,120,400��
    $sft_kishu_kin   = 23728122;    // ���եȴ���Ĺ� 23,728,122��
    $den_zou_kin     = 0;           // ���ô�������            0��
    $shi_zou_kin     = 0;           // ���ߴ�������            0��
    $sft_zou_kin     = 0;           // ���եȴ�������          0��
    $den_gen_kin     = 0;           // ���ô��渺��            0��
    $shi_gen_kin     = 0;           // ���ߴ��渺��            0��
    $sft_gen_kin     = 0;           // ���եȴ��渺��          0��
}
if ($yyyymm >= 201809 && $yyyymm <= 201811) {
    $den_kishu_kin   = 1224000;     // ���ô���Ĺ�    1,224,000��
    $shi_kishu_kin   = 13120400;    // ���ߴ���Ĺ�   13,120,400��
    $sft_kishu_kin   = 23728122;    // ���եȴ���Ĺ� 23,728,122��
    $den_zou_kin     = 0;           // ���ô�������            0��
    $shi_zou_kin     = 0;           // ���ߴ�������            0��
    $sft_zou_kin     = 0;           // ���եȴ�������          0��
    $den_gen_kin     = 0;           // ���ô��渺��            0��
    $shi_gen_kin     = 0;           // ���ߴ��渺��            0��
    $sft_gen_kin     = 0;           // ���եȴ��渺��          0��
}
if ($yyyymm >= 201812 && $yyyymm <= 201902) {
    $den_kishu_kin   = 1224000;     // ���ô���Ĺ�    1,224,000��
    $shi_kishu_kin   = 13120400;    // ���ߴ���Ĺ�   13,120,400��
    $sft_kishu_kin   = 23728122;    // ���եȴ���Ĺ� 23,728,122��
    $den_zou_kin     = 0;           // ���ô�������            0��
    $shi_zou_kin     = 0;           // ���ߴ�������            0��
    $sft_zou_kin     = 0;           // ���եȴ�������          0��
    $den_gen_kin     = 0;           // ���ô��渺��            0��
    $shi_gen_kin     = 0;           // ���ߴ��渺��            0��
    $sft_gen_kin     = 0;           // ���եȴ��渺��          0��
}
if ($yyyymm >= 201903 && $yyyymm <= 201905) {
    $den_kishu_kin   = 1224000;     // ���ô���Ĺ�    1,224,000��
    $shi_kishu_kin   = 13120400;    // ���ߴ���Ĺ�   13,120,400��
    $sft_kishu_kin   = 23728122;    // ���եȴ���Ĺ� 23,728,122��
    $den_zou_kin     = 0;           // ���ô�������            0��
    $shi_zou_kin     = 0;           // ���ߴ�������            0��
    $sft_zou_kin     = 0;           // ���եȴ�������          0��
    $den_gen_kin     = 0;           // ���ô��渺��            0��
    $shi_gen_kin     = 0;           // ���ߴ��渺��            0��
    $sft_gen_kin     = 0;           // ���եȴ��渺��          0��
}
if ($yyyymm >= 201906 && $yyyymm <= 201908) {
    $den_kishu_kin   = 1224000;     // ���ô���Ĺ�    1,224,000��
    $shi_kishu_kin   = 13120400;    // ���ߴ���Ĺ�   13,120,400��
    $sft_kishu_kin   = 23728122;    // ���եȴ���Ĺ� 23,728,122��
    $den_zou_kin     = 0;           // ���ô�������            0��
    $shi_zou_kin     = 0;           // ���ߴ�������            0��
    $sft_zou_kin     = 0;           // ���եȴ�������          0��
    $den_gen_kin     = 0;           // ���ô��渺��            0��
    $shi_gen_kin     = 0;           // ���ߴ��渺��            0��
    $sft_gen_kin     = 0;           // ���եȴ��渺��          0��
}
if ($yyyymm >= 201909 && $yyyymm <= 201911) {
    $den_kishu_kin   = 1224000;     // ���ô���Ĺ�    1,224,000��
    $shi_kishu_kin   = 13120400;    // ���ߴ���Ĺ�   13,120,400��
    $sft_kishu_kin   = 23728122;    // ���եȴ���Ĺ� 23,728,122��
    $den_zou_kin     = 0;           // ���ô�������            0��
    $shi_zou_kin     = 0;           // ���ߴ�������            0��
    $sft_zou_kin     = 0;           // ���եȴ�������          0��
    $den_gen_kin     = 0;           // ���ô��渺��            0��
    $shi_gen_kin     = 0;           // ���ߴ��渺��            0��
    $sft_gen_kin     = 0;           // ���եȴ��渺��          0��
}
if ($yyyymm >= 201912 && $yyyymm <= 202002) {
    $den_kishu_kin   = 1224000;     // ���ô���Ĺ�    1,224,000��
    $shi_kishu_kin   = 13120400;    // ���ߴ���Ĺ�   13,120,400��
    $sft_kishu_kin   = 23728122;    // ���եȴ���Ĺ� 23,728,122��
    $den_zou_kin     = 0;           // ���ô�������            0��
    $shi_zou_kin     = 0;           // ���ߴ�������            0��
    $sft_zou_kin     = 0;           // ���եȴ�������          0��
    $den_gen_kin     = 0;           // ���ô��渺��            0��
    $shi_gen_kin     = 0;           // ���ߴ��渺��            0��
    $sft_gen_kin     = 0;           // ���եȴ��渺��          0��
}
if ($yyyymm >= 202003 && $yyyymm <= 202005) {
    $den_kishu_kin   = 1224000;     // ���ô���Ĺ�    1,224,000��
    $shi_kishu_kin   = 13120400;    // ���ߴ���Ĺ�   13,120,400��
    $sft_kishu_kin   = 23728122;    // ���եȴ���Ĺ� 23,728,122��
    $den_zou_kin     = 0;           // ���ô�������            0��
    $shi_zou_kin     = 0;           // ���ߴ�������            0��
    $sft_zou_kin     = 0;           // ���եȴ�������          0��
    $den_gen_kin     = 0;           // ���ô��渺��            0��
    $shi_gen_kin     = 0;           // ���ߴ��渺��            0��
    $sft_gen_kin     = 0;           // ���եȴ��渺��          0��
}
if ($yyyymm >= 202006 && $yyyymm <= 202008) {
    $den_kishu_kin   = 1224000;     // ���ô���Ĺ�    1,224,000��
    $shi_kishu_kin   = 13120400;    // ���ߴ���Ĺ�   13,120,400��
    $sft_kishu_kin   = 23728122;    // ���եȴ���Ĺ� 23,728,122��
    $den_zou_kin     = 0;           // ���ô�������            0��
    $shi_zou_kin     = 0;           // ���ߴ�������            0��
    $sft_zou_kin     = 17639120;    // ���եȴ������� 17,639,120��
    $den_gen_kin     = 0;           // ���ô��渺��            0��
    $shi_gen_kin     = 0;           // ���ߴ��渺��            0��
    $sft_gen_kin     = 0;           // ���եȴ��渺��          0��
}
if ($yyyymm >= 202009 && $yyyymm <= 202011) {
    $den_kishu_kin   = 1224000;     // ���ô���Ĺ�    1,224,000��
    $shi_kishu_kin   = 13120400;    // ���ߴ���Ĺ�   13,120,400��
    $sft_kishu_kin   = 23728122;    // ���եȴ���Ĺ� 23,728,122��
    $den_zou_kin     = 0;           // ���ô�������            0��
    $shi_zou_kin     = 0;           // ���ߴ�������            0��
    $sft_zou_kin     = 20537520;    // ���եȴ������� 20,537,520��
    $den_gen_kin     = 0;           // ���ô��渺��            0��
    $shi_gen_kin     = 0;           // ���ߴ��渺��            0��
    $sft_gen_kin     = 0;           // ���եȴ��渺��          0��
}
if ($yyyymm >= 202012 && $yyyymm <= 202102) {
    $den_kishu_kin   = 1224000;     // ���ô���Ĺ�    1,224,000��
    $shi_kishu_kin   = 13120400;    // ���ߴ���Ĺ�   13,120,400��
    $sft_kishu_kin   = 23728122;    // ���եȴ���Ĺ� 23,728,122��
    $den_zou_kin     = 0;           // ���ô�������            0��
    $shi_zou_kin     = 0;           // ���ߴ�������            0��
    $sft_zou_kin     = 20537520;    // ���եȴ������� 20,537,520��
    $den_gen_kin     = 0;           // ���ô��渺��            0��
    $shi_gen_kin     = 0;           // ���ߴ��渺��            0��
    $sft_gen_kin     = 0;           // ���եȴ��渺��          0��
}
if ($yyyymm >= 202103 && $yyyymm <= 202105) {
    $den_kishu_kin   = 1224000;     // ���ô���Ĺ�    1,224,000��
    $shi_kishu_kin   = 13120400;    // ���ߴ���Ĺ�   13,120,400��
    $sft_kishu_kin   = 23728122;    // ���եȴ���Ĺ� 23,728,122��
    $den_zou_kin     = 0;           // ���ô�������            0��
    $shi_zou_kin     = 0;           // ���ߴ�������            0��
    $sft_zou_kin     = 20537520;    // ���եȴ������� 20,537,520��
    $den_gen_kin     = 0;           // ���ô��渺��            0��
    $shi_gen_kin     = 0;           // ���ߴ��渺��            0��
    $sft_gen_kin     = 0;           // ���եȴ��渺��          0��
}
if ($yyyymm >= 202106 && $yyyymm <= 202108) {
    $den_kishu_kin   = 1224000;     // ���ô���Ĺ�    1,224,000��
    $shi_kishu_kin   = 13120400;    // ���ߴ���Ĺ�   13,120,400��
    $sft_kishu_kin   = 44265642;    // ���եȴ���Ĺ� 44,265,642��
    $den_zou_kin     = 0;           // ���ô�������            0��
    $shi_zou_kin     = 0;           // ���ߴ�������            0��
    $sft_zou_kin     = 547000;      // ���եȴ�������    547,000��
    $den_gen_kin     = 0;           // ���ô��渺��            0��
    $shi_gen_kin     = 0;           // ���ߴ��渺��            0��
    $sft_gen_kin     = 0;           // ���եȴ��渺��          0��
}
if ($yyyymm >= 202109 && $yyyymm <= 202111) {
    $den_kishu_kin   = 1224000;     // ���ô���Ĺ�    1,224,000��
    $shi_kishu_kin   = 13120400;    // ���ߴ���Ĺ�   13,120,400��
    $sft_kishu_kin   = 44265642;    // ���եȴ���Ĺ� 44,265,642��
    $den_zou_kin     = 0;           // ���ô�������            0��
    $shi_zou_kin     = 0;           // ���ߴ�������            0��
    $sft_zou_kin     = 547000;      // ���եȴ�������    547,000��
    $den_gen_kin     = 0;           // ���ô��渺��            0��
    $shi_gen_kin     = 0;           // ���ߴ��渺��            0��
    $sft_gen_kin     = 0;           // ���եȴ��渺��          0��
}

// �߸˹�פη׻�
$zaiko_total_kin    = $seihin_kin + $seihinsi_kin + $buhin_kin + $buhinsi_kin + $genzai_kin + $sonotatana_kin + $chozo_kin;
// ���ʹ�פη׻�
$seihin_total_kin   = $seihin_kin;
// �ų��ʹ�פη׻�
$sikakari_total_kin = $seihinsi_kin;
// �������ڤ���¢�ʹ�פη׻�
$gencho_total_kin   = $buhin_kin + $buhinsi_kin + $genzai_kin + $sonotatana_kin + $chozo_kin;
// ήư�񻺺߸˹�פη׻�
$ryudozaiko_total_kin   = $seihin_total_kin + $sikakari_total_kin + $gencho_total_kin;

///// ήư��
// ͭ���ٵ�̤������1302 00
/*
$res   = array();
$field = array();
$rows  = array();
$yumi_kin = 0;
$sum1 = '1302';
$sum2 = '00';
$query = sprintf("select rep_cri, rep_de, rep_cr from financial_report_cal where rep_ymd=%d and rep_summary1='%s' and rep_summary2='%s'", $yyyymm, $sum1, $sum2);
if ($rows=getResultWithField2($query, $field, $res) <= 0) {
    $yumi_kin = 0;
} else {
    $yumi_kin = $res[0][0] + $res[0][1] - $res[0][2];
}
*/
$res_k   = array();
$field_k = array();
$rows_k  = array();
$yumi_kin = 0;
$sum1 = '1302';
$sum2 = '00';
$query_k = sprintf("select rep_cri, rep_de, rep_cr from financial_report_cal where rep_ymd=%d and rep_summary1='%s' and rep_summary2='%s'", $nk_ki, $sum1, $sum2);
if ($rows_k=getResultWithField2($query_k, $field_k, $res_k) <= 0) {
    $yumi_kishu = 0;
} else {
    $yumi_kishu = $res_k[0][0];
}
$res   = array();
$field = array();
$rows  = array();
$query = sprintf("select SUM(rep_de), SUM(rep_cr) from financial_report_month where rep_ymd>=%d and rep_ymd<=%d and rep_summary1='%s'", $str_ym, $end_ym, $sum1);
if ($rows=getResultWithField2($query, $field, $res) <= 0) {
    $yumi_kin = $yumi_kishu;
} else {
    $yumi_kin = $yumi_kishu + ($res[0][0] - $res[0][1]);
}

// ̤������1503 00
/*
$res   = array();
$field = array();
$rows  = array();
$mishu_kin = 0;
$sum1 = '1503';
$sum2 = '00';
$query = sprintf("select rep_cri, rep_de, rep_cr from financial_report_cal where rep_ymd=%d and rep_summary1='%s' and rep_summary2='%s'", $yyyymm, $sum1, $sum2);
if ($rows=getResultWithField2($query, $field, $res) <= 0) {
    $mishu_kin = 0;
} else {
    $mishu_kin = $res[0][0] + $res[0][1] - $res[0][2];
}
*/
$res_k   = array();
$field_k = array();
$rows_k  = array();
$mishu_kin = 0;
$sum1 = '1503';
$sum2 = '00';
$query_k = sprintf("select rep_cri, rep_de, rep_cr from financial_report_cal where rep_ymd=%d and rep_summary1='%s' and rep_summary2='%s'", $nk_ki, $sum1, $sum2);
if ($rows_k=getResultWithField2($query_k, $field_k, $res_k) <= 0) {
    $mishu_kishu = 0;
} else {
    $mishu_kishu = $res_k[0][0];
}
$res   = array();
$field = array();
$rows  = array();
$query = sprintf("select SUM(rep_de), SUM(rep_cr) from financial_report_month where rep_ymd>=%d and rep_ymd<=%d and rep_summary1='%s'", $str_ym, $end_ym, $sum1);
if ($rows=getResultWithField2($query, $field, $res) <= 0) {
    $mishu_kin = $mishu_kishu;
} else {
    $mishu_kin = $mishu_kishu + ($res[0][0] - $res[0][1]);
}

// ̤������1701 00
/*
$res   = array();
$field = array();
$rows  = array();
$mishueki_kin = 0;
$sum1 = '1701';
$sum2 = '00';
$query = sprintf("select rep_cri, rep_de, rep_cr from financial_report_cal where rep_ymd=%d and rep_summary1='%s' and rep_summary2='%s'", $yyyymm, $sum1, $sum2);
if ($rows=getResultWithField2($query, $field, $res) <= 0) {
    $mishueki_kin = 0;
} else {
    $mishueki_kin = $res[0][0] + $res[0][1] - $res[0][2];
}
*/
$res_k   = array();
$field_k = array();
$rows_k  = array();
$mishueki_kin = 0;
$sum1 = '1701';
$sum2 = '00';
$query_k = sprintf("select rep_cri, rep_de, rep_cr from financial_report_cal where rep_ymd=%d and rep_summary1='%s' and rep_summary2='%s'", $nk_ki, $sum1, $sum2);
if ($rows_k=getResultWithField2($query_k, $field_k, $res_k) <= 0) {
    $mishueki_kishu = 0;
} else {
    $mishueki_kishu = $res_k[0][0];
}
$res   = array();
$field = array();
$rows  = array();
$query = sprintf("select SUM(rep_de), SUM(rep_cr) from financial_report_month where rep_ymd>=%d and rep_ymd<=%d and rep_summary1='%s'", $str_ym, $end_ym, $sum1);
if ($rows=getResultWithField2($query, $field, $res) <= 0) {
    $mishueki_kin = $mishueki_kishu;
} else {
    $mishueki_kin = $mishueki_kishu + ($res[0][0] - $res[0][1]);
}

//// ����¾��ήư��
// Ω�ض�1505 00
/*
$res   = array();
$field = array();
$rows  = array();
$tatekae_kin = 0;
$sum1 = '1505';
$sum2 = '00';
$query = sprintf("select rep_cri, rep_de, rep_cr from financial_report_cal where rep_ymd=%d and rep_summary1='%s' and rep_summary2='%s'", $yyyymm, $sum1, $sum2);
if ($rows=getResultWithField2($query, $field, $res) <= 0) {
    $tatekae_kin = 0;
} else {
    $tatekae_kin = $res[0][0] + $res[0][1] - $res[0][2];
}
*/
$res_k   = array();
$field_k = array();
$rows_k  = array();
$tatekae_kin = 0;
$sum1 = '1505';
$sum2 = '00';
$query_k = sprintf("select rep_cri, rep_de, rep_cr from financial_report_cal where rep_ymd=%d and rep_summary1='%s' and rep_summary2='%s'", $nk_ki, $sum1, $sum2);
if ($rows_k=getResultWithField2($query_k, $field_k, $res_k) <= 0) {
    $tatekae_kishu = 0;
} else {
    $tatekae_kishu = $res_k[0][0];
}
$res   = array();
$field = array();
$rows  = array();
$query = sprintf("select SUM(rep_de), SUM(rep_cr) from financial_report_month where rep_ymd>=%d and rep_ymd<=%d and rep_summary1='%s'", $str_ym, $end_ym, $sum1);
if ($rows=getResultWithField2($query, $field, $res) <= 0) {
    $tatekae_kin = $tatekae_kishu;
} else {
    $tatekae_kin = $tatekae_kishu + ($res[0][0] - $res[0][1]);
}

// ��ʧ��1504 00
/*
$res   = array();
$field = array();
$rows  = array();
$karibara_kin = 0;
$sum1 = '1504';
$sum2 = '00';
$query = sprintf("select rep_cri, rep_de, rep_cr from financial_report_cal where rep_ymd=%d and rep_summary1='%s' and rep_summary2='%s'", $yyyymm, $sum1, $sum2);
if ($rows=getResultWithField2($query, $field, $res) <= 0) {
    $karibara_kin = 0;
} else {
    // ��ʧ�����¢��ʬ��ޥ��ʥ�
    $karibara_kin = $res[0][0] + $res[0][1] - $res[0][2] - $chozo_kin;
}
*/
$res_k   = array();
$field_k = array();
$rows_k  = array();
$karibara_kin = 0;
$sum1 = '1504';
$sum2 = '00';
$query_k = sprintf("select rep_cri, rep_de, rep_cr from financial_report_cal where rep_ymd=%d and rep_summary1='%s' and rep_summary2='%s'", $nk_ki, $sum1, $sum2);
if ($rows_k=getResultWithField2($query_k, $field_k, $res_k) <= 0) {
    $karibara_kishu = 0;
} else {
    $karibara_kishu = $res_k[0][0];
}
$res   = array();
$field = array();
$rows  = array();
$query = sprintf("select SUM(rep_de), SUM(rep_cr) from financial_report_month where rep_ymd>=%d and rep_ymd<=%d and rep_summary1='%s'", $str_ym, $end_ym, $sum1);
if ($rows=getResultWithField2($query, $field, $res) <= 0) {
    $karibara_kin = $karibara_kishu - $chozo_kin;
} else {
    $karibara_kin = $karibara_kishu + ($res[0][0] - $res[0][1]) - $chozo_kin;
}

// ����¾ήư��2000 00
/*
$res   = array();
$field = array();
$rows  = array();
$hokaryudo_kin = 0;
$sum1 = '2000';
$sum2 = '00';
$query = sprintf("select rep_cri, rep_de, rep_cr from financial_report_cal where rep_ymd=%d and rep_summary1='%s' and rep_summary2='%s'", $yyyymm, $sum1, $sum2);
if ($rows=getResultWithField2($query, $field, $res) <= 0) {
    $hokaryudo_kin = 0;
} else {
    $hokaryudo_kin = $res[0][0] + $res[0][1] - $res[0][2];
}
*/
$res_k   = array();
$field_k = array();
$rows_k  = array();
$hokaryudo_kin = 0;
$sum1 = '2000';
$sum2 = '00';
$query_k = sprintf("select rep_cri, rep_de, rep_cr from financial_report_cal where rep_ymd=%d and rep_summary1='%s' and rep_summary2='%s'", $nk_ki, $sum1, $sum2);
if ($rows_k=getResultWithField2($query_k, $field_k, $res_k) <= 0) {
    $hokaryudo_kishu = 0;
} else {
    $hokaryudo_kishu = $res_k[0][0];
}
$res   = array();
$field = array();
$rows  = array();
$query = sprintf("select SUM(rep_de), SUM(rep_cr) from financial_report_month where rep_ymd>=%d and rep_ymd<=%d and rep_summary1='%s'", $str_ym, $end_ym, $sum1);
if ($rows=getResultWithField2($query, $field, $res) <= 0) {
    $hokaryudo_kin = $hokaryudo_kishu;
} else {
    $hokaryudo_kin = $hokaryudo_kishu + ($res[0][0] - $res[0][1]);
}

// ����¾ήư�񻺤˥ץ饹���� ¾����̤�軻�ʻ��1901 20
$res_k   = array();
$field_k = array();
$rows_k  = array();
$hokaryudo_kin = 0;
$sum1 = '1901';
$sum2 = '20';
$query_k = sprintf("select rep_cri, rep_de, rep_cr from financial_report_cal where rep_ymd=%d and rep_summary1='%s' and rep_summary2='%s'", $nk_ki, $sum1, $sum2);
if ($rows_k=getResultWithField2($query_k, $field_k, $res_k) <= 0) {
    $ta_miketsu_kishu = 0;
} else {
    $ta_miketsu_kishu = $res_k[0][0];
}
$res   = array();
$field = array();
$rows  = array();
$query = sprintf("select SUM(rep_de), SUM(rep_cr) from financial_report_month where rep_ymd>=%d and rep_ymd<=%d and rep_summary1='%s'", $str_ym, $end_ym, $sum1);
if ($rows=getResultWithField2($query, $field, $res) <= 0) {
    $ta_miketsu_kin = $ta_miketsu_kishu;
} else {
    $ta_miketsu_kin = $ta_miketsu_kishu + ($res[0][0] - $res[0][1]);
}

// ����¾ήư�񻺤η׻�
$hokaryudo_kin = $hokaryudo_kin + $ta_miketsu_kin;

// ήư�� ̤������η׻�
$ryu_mishu_kin    = $yumi_kin + $mishu_kin + $mishueki_kin;
// ήư�� ̤�������פη׻�
$ryu_mishu_total_kin    = $ryu_mishu_kin;
// ήư�� ����¾ήư�񻺷פη׻�
$hokaryudo_total_kin    = $tatekae_kin + $karibara_kin + $hokaryudo_kin;
// ήư�� ̤�������פη׻�
$hokaryudo_all_kin    = $hokaryudo_total_kin;

//// ͭ�������
/*
// ��ʪ2101 00
$res   = array();
$field = array();
$rows  = array();
$tatemono_kin = 0;
$sum1 = '2101';
$sum2 = '00';
$query = sprintf("select rep_cri, rep_de, rep_cr from financial_report_cal where rep_ymd=%d and rep_summary1='%s' and rep_summary2='%s'", $yyyymm, $sum1, $sum2);
if ($rows=getResultWithField2($query, $field, $res) <= 0) {
    $tatemono_kin = 0;
} else {
    $tatemono_kin = $res[0][0] + $res[0][1] - $res[0][2];
}
*/
$res_k   = array();
$field_k = array();
$rows_k  = array();
$tatemono_kin = 0;
$sum1 = '2101';
$sum2 = '00';
$query_k = sprintf("select rep_cri, rep_de, rep_cr from financial_report_cal where rep_ymd=%d and rep_summary1='%s' and rep_summary2='%s'", $nk_ki, $sum1, $sum2);
if ($rows_k=getResultWithField2($query_k, $field_k, $res_k) <= 0) {
    $tatemono_kishu = 0;
} else {
    $tatemono_kishu = $res_k[0][0];
}
$res   = array();
$field = array();
$rows  = array();
$query = sprintf("select SUM(rep_de), SUM(rep_cr) from financial_report_month where rep_ymd>=%d and rep_ymd<=%d and rep_summary1='%s'", $str_ym, $end_ym, $sum1);
if ($rows=getResultWithField2($query, $field, $res) <= 0) {
    $tatemono_kin = $tatemono_kishu;
} else {
    $tatemono_kin = $tatemono_kishu + ($res[0][0] - $res[0][1]);
}

// ��ʪ���߳�3401 10
/*
$res   = array();
$field = array();
$rows  = array();
$tate_gen_kin = 0;
$sum1 = '3401';
$sum2 = '10';
$query = sprintf("select rep_cri, rep_de, rep_cr from financial_report_cal where rep_ymd=%d and rep_summary1='%s' and rep_summary2='%s'", $yyyymm, $sum1, $sum2);
if ($rows=getResultWithField2($query, $field, $res) <= 0) {
    $tate_gen_kin = 0;
} else {
    $tate_gen_kin = -($res[0][0] + $res[0][1] - $res[0][2]);
}
*/
$res_k   = array();
$field_k = array();
$rows_k  = array();
$tate_gen_kin = 0;
$sum1 = '3401';
$sum2 = '10';
$query_k = sprintf("select rep_cri, rep_de, rep_cr from financial_report_cal where rep_ymd=%d and rep_summary1='%s' and rep_summary2='%s'", $nk_ki, $sum1, $sum2);
if ($rows_k=getResultWithField2($query_k, $field_k, $res_k) <= 0) {
    $tate_gen_kishu = 0;
} else {
    $tate_gen_kishu = $res_k[0][0];
}
$res   = array();
$field = array();
$rows  = array();
$query = sprintf("select SUM(rep_de), SUM(rep_cr) from financial_report_month where rep_ymd>=%d and rep_ymd<=%d and rep_summary1='%s' and rep_summary2='%s'", $str_ym, $end_ym, $sum1, $sum2);
if ($rows=getResultWithField2($query, $field, $res) <= 0) {
    $tate_gen_kin = $tate_gen_kishu;
} else {
    $tate_gen_kin = -($tate_gen_kishu + ($res[0][0] - $res[0][1]));
}

// ��ʪ�񻺶��
$tate_shisan_kin = $tatemono_kin - $tate_gen_kin;
// ��ʪ��°����2102 00
/*
$res   = array();
$field = array();
$rows  = array();
$fuzoku_kin = 0;
$sum1 = '2102';
$sum2 = '00';
$query = sprintf("select rep_cri, rep_de, rep_cr from financial_report_cal where rep_ymd=%d and rep_summary1='%s' and rep_summary2='%s'", $yyyymm, $sum1, $sum2);
if ($rows=getResultWithField2($query, $field, $res) <= 0) {
    $fuzoku_kin = 0;
} else {
    $fuzoku_kin = $res[0][0] + $res[0][1] - $res[0][2];
}
*/
$res_k   = array();
$field_k = array();
$rows_k  = array();
$fuzoku_kin = 0;
$sum1 = '2102';
$sum2 = '00';
$query_k = sprintf("select rep_cri, rep_de, rep_cr from financial_report_cal where rep_ymd=%d and rep_summary1='%s' and rep_summary2='%s'", $nk_ki, $sum1, $sum2);
if ($rows_k=getResultWithField2($query_k, $field_k, $res_k) <= 0) {
    $fuzoku_kishu = 0;
} else {
    $fuzoku_kishu = $res_k[0][0];
}
$res   = array();
$field = array();
$rows  = array();
$query = sprintf("select SUM(rep_de), SUM(rep_cr) from financial_report_month where rep_ymd>=%d and rep_ymd<=%d and rep_summary1='%s'", $str_ym, $end_ym, $sum1);
if ($rows=getResultWithField2($query, $field, $res) <= 0) {
    $fuzoku_kin = $fuzoku_kishu;
} else {
    $fuzoku_kin = $fuzoku_kishu + ($res[0][0] - $res[0][1]);
}

// ��ʪ��°�������߳�3401 20
/*
$res   = array();
$field = array();
$rows  = array();
$fuzoku_gen_kin = 0;
$sum1 = '3401';
$sum2 = '20';
$query = sprintf("select rep_cri, rep_de, rep_cr from financial_report_cal where rep_ymd=%d and rep_summary1='%s' and rep_summary2='%s'", $yyyymm, $sum1, $sum2);
if ($rows=getResultWithField2($query, $field, $res) <= 0) {
    $fuzoku_gen_kin = 0;
} else {
    $fuzoku_gen_kin = -($res[0][0] + $res[0][1] - $res[0][2]);
}
*/
$res_k   = array();
$field_k = array();
$rows_k  = array();
$fuzoku_gen_kin = 0;
$sum1 = '3401';
$sum2 = '20';
$query_k = sprintf("select rep_cri, rep_de, rep_cr from financial_report_cal where rep_ymd=%d and rep_summary1='%s' and rep_summary2='%s'", $nk_ki, $sum1, $sum2);
if ($rows_k=getResultWithField2($query_k, $field_k, $res_k) <= 0) {
    $fuzoku_gen_kishu = 0;
} else {
    $fuzoku_gen_kishu = $res_k[0][0];
}
$res   = array();
$field = array();
$rows  = array();
$query = sprintf("select SUM(rep_de), SUM(rep_cr) from financial_report_month where rep_ymd>=%d and rep_ymd<=%d and rep_summary1='%s' and rep_summary2='%s'", $str_ym, $end_ym, $sum1, $sum2);
if ($rows=getResultWithField2($query, $field, $res) <= 0) {
    $fuzoku_gen_kin = $fuzoku_gen_kishu;
} else {
    $fuzoku_gen_kin = -($fuzoku_gen_kishu + ($res[0][0] - $res[0][1]));
}

// ��ʪ��°�����񻺶��
$fuzoku_shisan_kin = $fuzoku_kin - $fuzoku_gen_kin;
// ��ʪ��׻񻺶��
$tate_all_shisan_kin = $tate_shisan_kin + $fuzoku_shisan_kin;

//eca�� �񻺶�۷�ʪ
$tate_shutoku_kin = $tatemono_kin + $fuzoku_kin;
//eca�� ���������߷׳�(��ʪ)
$tate_rui_kin = -($tate_gen_kin + $fuzoku_gen_kin);

// ����ʪ2103 00
/*
$res   = array();
$field = array();
$rows  = array();
$kouchiku_kin = 0;
$sum1 = '2103';
$sum2 = '00';
$query = sprintf("select rep_cri, rep_de, rep_cr from financial_report_cal where rep_ymd=%d and rep_summary1='%s' and rep_summary2='%s'", $yyyymm, $sum1, $sum2);
if ($rows=getResultWithField2($query, $field, $res) <= 0) {
    $kouchiku_kin = 0;
} else {
    $kouchiku_kin = $res[0][0] + $res[0][1] - $res[0][2];
}
*/
$res_k   = array();
$field_k = array();
$rows_k  = array();
$kouchiku_kin = 0;
$sum1 = '2103';
$sum2 = '00';
$query_k = sprintf("select rep_cri, rep_de, rep_cr from financial_report_cal where rep_ymd=%d and rep_summary1='%s' and rep_summary2='%s'", $nk_ki, $sum1, $sum2);
if ($rows_k=getResultWithField2($query_k, $field_k, $res_k) <= 0) {
    $kouchiku_kishu = 0;
} else {
    $kouchiku_kishu = $res_k[0][0];
}
$res   = array();
$field = array();
$rows  = array();
$query = sprintf("select SUM(rep_de), SUM(rep_cr) from financial_report_month where rep_ymd>=%d and rep_ymd<=%d and rep_summary1='%s'", $str_ym, $end_ym, $sum1);
if ($rows=getResultWithField2($query, $field, $res) <= 0) {
    $kouchiku_kin = $kouchiku_kishu;
} else {
    $kouchiku_kin = $kouchiku_kishu + ($res[0][0] - $res[0][1]);
}

// ����ʪ���߳�3401 30
/*
$res   = array();
$field = array();
$rows  = array();
$kouchiku_gen_kin = 0;
$sum1 = '3401';
$sum2 = '30';
$query = sprintf("select rep_cri, rep_de, rep_cr from financial_report_cal where rep_ymd=%d and rep_summary1='%s' and rep_summary2='%s'", $yyyymm, $sum1, $sum2);
if ($rows=getResultWithField2($query, $field, $res) <= 0) {
    $kouchiku_gen_kin = 0;
} else {
    $kouchiku_gen_kin = -($res[0][0] + $res[0][1] - $res[0][2]);
}
*/
$res_k   = array();
$field_k = array();
$rows_k  = array();
$kouchiku_gen_kin = 0;
$sum1 = '3401';
$sum2 = '30';
$query_k = sprintf("select rep_cri, rep_de, rep_cr from financial_report_cal where rep_ymd=%d and rep_summary1='%s' and rep_summary2='%s'", $nk_ki, $sum1, $sum2);
if ($rows_k=getResultWithField2($query_k, $field_k, $res_k) <= 0) {
    $kouchiku_gen_kishu = 0;
} else {
    $kouchiku_gen_kishu = $res_k[0][0];
}
$res   = array();
$field = array();
$rows  = array();
$query = sprintf("select SUM(rep_de), SUM(rep_cr) from financial_report_month where rep_ymd>=%d and rep_ymd<=%d and rep_summary1='%s' and rep_summary2='%s'", $str_ym, $end_ym, $sum1, $sum2);
if ($rows=getResultWithField2($query, $field, $res) <= 0) {
    $kouchiku_gen_kin = $kouchiku_gen_kishu;
} else {
    $kouchiku_gen_kin = -($kouchiku_gen_kishu + ($res[0][0] - $res[0][1]));
}

// ����ʪ�񻺶��
$kouchiku_shisan_kin = $kouchiku_kin - $kouchiku_gen_kin;

// ��������2104 00
/*
$res   = array();
$field = array();
$rows  = array();
$kikai_kin = 0;
$sum1 = '2104';
$sum2 = '00';
$query = sprintf("select rep_cri, rep_de, rep_cr from financial_report_cal where rep_ymd=%d and rep_summary1='%s' and rep_summary2='%s'", $yyyymm, $sum1, $sum2);
if ($rows=getResultWithField2($query, $field, $res) <= 0) {
    $kikai_kin = 0;
} else {
    $kikai_kin = $res[0][0] + $res[0][1] - $res[0][2];
}
*/
$res_k   = array();
$field_k = array();
$rows_k  = array();
$kikai_kin = 0;
$sum1 = '2104';
$sum2 = '00';
$query_k = sprintf("select rep_cri, rep_de, rep_cr from financial_report_cal where rep_ymd=%d and rep_summary1='%s' and rep_summary2='%s'", $nk_ki, $sum1, $sum2);
if ($rows_k=getResultWithField2($query_k, $field_k, $res_k) <= 0) {
    $kikai_kishu = 0;
} else {
    $kikai_kishu = $res_k[0][0];
}
$res   = array();
$field = array();
$rows  = array();
$query = sprintf("select SUM(rep_de), SUM(rep_cr) from financial_report_month where rep_ymd>=%d and rep_ymd<=%d and rep_summary1='%s'", $str_ym, $end_ym, $sum1);
if ($rows=getResultWithField2($query, $field, $res) <= 0) {
    $kikai_kin = $kikai_kishu;
} else {
    $kikai_kin = $kikai_kishu + ($res[0][0] - $res[0][1]);
}

// �������ָ��߳�3401 40
/*
$res   = array();
$field = array();
$rows  = array();
$kikai_gen_kin = 0;
$sum1 = '3401';
$sum2 = '40';
$query = sprintf("select rep_cri, rep_de, rep_cr from financial_report_cal where rep_ymd=%d and rep_summary1='%s' and rep_summary2='%s'", $yyyymm, $sum1, $sum2);
if ($rows=getResultWithField2($query, $field, $res) <= 0) {
    $kikai_gen_kin = 0;
} else {
    $kikai_gen_kin = -($res[0][0] + $res[0][1] - $res[0][2]);
}
*/
$res_k   = array();
$field_k = array();
$rows_k  = array();
$kikai_gen_kin = 0;
$sum1 = '3401';
$sum2 = '40';
$query_k = sprintf("select rep_cri, rep_de, rep_cr from financial_report_cal where rep_ymd=%d and rep_summary1='%s' and rep_summary2='%s'", $nk_ki, $sum1, $sum2);
if ($rows_k=getResultWithField2($query_k, $field_k, $res_k) <= 0) {
    $kikai_gen_kishu = 0;
} else {
    $kikai_gen_kishu = $res_k[0][0];
}
$res   = array();
$field = array();
$rows  = array();
$query = sprintf("select SUM(rep_de), SUM(rep_cr) from financial_report_month where rep_ymd>=%d and rep_ymd<=%d and rep_summary1='%s' and rep_summary2='%s'", $str_ym, $end_ym, $sum1, $sum2);
if ($rows=getResultWithField2($query, $field, $res) <= 0) {
    $kikai_gen_kin = $kikai_gen_kishu;
} else {
    $kikai_gen_kin = -($kikai_gen_kishu + ($res[0][0] - $res[0][1]));
}

// �������ֻ񻺶��
$kikai_shisan_kin = $kikai_kin - $kikai_gen_kin;

// ���ұ��¶�2105 00
/*
$res   = array();
$field = array();
$rows  = array();
$sharyo_kin = 0;
$sum1 = '2105';
$sum2 = '00';
$query = sprintf("select rep_cri, rep_de, rep_cr from financial_report_cal where rep_ymd=%d and rep_summary1='%s' and rep_summary2='%s'", $yyyymm, $sum1, $sum2);
if ($rows=getResultWithField2($query, $field, $res) <= 0) {
    $sharyo_kin = 0;
} else {
    $sharyo_kin = $res[0][0] + $res[0][1] - $res[0][2];
}
*/
$res_k   = array();
$field_k = array();
$rows_k  = array();
$sharyo_kin = 0;
$sum1 = '2105';
$sum2 = '00';
$query_k = sprintf("select rep_cri, rep_de, rep_cr from financial_report_cal where rep_ymd=%d and rep_summary1='%s' and rep_summary2='%s'", $nk_ki, $sum1, $sum2);
if ($rows_k=getResultWithField2($query_k, $field_k, $res_k) <= 0) {
    $sharyo_kishu = 0;
} else {
    $sharyo_kishu = $res_k[0][0];
}
$res   = array();
$field = array();
$rows  = array();
$query = sprintf("select SUM(rep_de), SUM(rep_cr) from financial_report_month where rep_ymd>=%d and rep_ymd<=%d and rep_summary1='%s'", $str_ym, $end_ym, $sum1);
if ($rows=getResultWithField2($query, $field, $res) <= 0) {
    $sharyo_kin = $sharyo_kishu;
} else {
    $sharyo_kin = $sharyo_kishu + ($res[0][0] - $res[0][1]);
}

// ���ұ��¶��߳�3401 50
/*
$res   = array();
$field = array();
$rows  = array();
$sharyo_gen_kin = 0;
$sum1 = '3401';
$sum2 = '50';
$query = sprintf("select rep_cri, rep_de, rep_cr from financial_report_cal where rep_ymd=%d and rep_summary1='%s' and rep_summary2='%s'", $yyyymm, $sum1, $sum2);
if ($rows=getResultWithField2($query, $field, $res) <= 0) {
    $sharyo_gen_kin = 0;
} else {
    $sharyo_gen_kin = -($res[0][0] + $res[0][1] - $res[0][2]);
}
*/
$res_k   = array();
$field_k = array();
$rows_k  = array();
$sharyo_gen_kin = 0;
$sum1 = '3401';
$sum2 = '50';
$query_k = sprintf("select rep_cri, rep_de, rep_cr from financial_report_cal where rep_ymd=%d and rep_summary1='%s' and rep_summary2='%s'", $nk_ki, $sum1, $sum2);
if ($rows_k=getResultWithField2($query_k, $field_k, $res_k) <= 0) {
    $sharyo_gen_kishu = 0;
} else {
    $sharyo_gen_kishu = $res_k[0][0];
}
$res   = array();
$field = array();
$rows  = array();
$query = sprintf("select SUM(rep_de), SUM(rep_cr) from financial_report_month where rep_ymd>=%d and rep_ymd<=%d and rep_summary1='%s' and rep_summary2='%s'", $str_ym, $end_ym, $sum1, $sum2);
if ($rows=getResultWithField2($query, $field, $res) <= 0) {
    $sharyo_gen_kin = $sharyo_gen_kishu;
} else {
    $sharyo_gen_kin = -($sharyo_gen_kishu + ($res[0][0] - $res[0][1]));
}

// ���ұ��¶�񻺶��
$sharyo_shisan_kin = $sharyo_kin - $sharyo_gen_kin;

// ��񹩶�2106 00
/*
$res   = array();
$field = array();
$rows  = array();
$kigu_kin = 0;
$sum1 = '2106';
$sum2 = '00';
$query = sprintf("select rep_cri, rep_de, rep_cr from financial_report_cal where rep_ymd=%d and rep_summary1='%s' and rep_summary2='%s'", $yyyymm, $sum1, $sum2);
if ($rows=getResultWithField2($query, $field, $res) <= 0) {
    $kigu_kin = 0;
} else {
    $kigu_kin = $res[0][0] + $res[0][1] - $res[0][2];
}
*/
$res_k   = array();
$field_k = array();
$rows_k  = array();
$kigu_kin = 0;
$sum1 = '2106';
$sum2 = '00';
$query_k = sprintf("select rep_cri, rep_de, rep_cr from financial_report_cal where rep_ymd=%d and rep_summary1='%s' and rep_summary2='%s'", $nk_ki, $sum1, $sum2);
if ($rows_k=getResultWithField2($query_k, $field_k, $res_k) <= 0) {
    $kigu_kishu = 0;
} else {
    $kigu_kishu = $res_k[0][0];
}
$res   = array();
$field = array();
$rows  = array();
$query = sprintf("select SUM(rep_de), SUM(rep_cr) from financial_report_month where rep_ymd>=%d and rep_ymd<=%d and rep_summary1='%s'", $str_ym, $end_ym, $sum1);
if ($rows=getResultWithField2($query, $field, $res) <= 0) {
    $kigu_kin = $kigu_kishu;
} else {
    $kigu_kin = $kigu_kishu + ($res[0][0] - $res[0][1]);
}

// ��񹩶��߳�3401 60
/*
$res   = array();
$field = array();
$rows  = array();
$kigu_gen_kin = 0;
$sum1 = '3401';
$sum2 = '60';
$query = sprintf("select rep_cri, rep_de, rep_cr from financial_report_cal where rep_ymd=%d and rep_summary1='%s' and rep_summary2='%s'", $yyyymm, $sum1, $sum2);
if ($rows=getResultWithField2($query, $field, $res) <= 0) {
    $kigu_gen_kin = 0;
} else {
    $kigu_gen_kin = -($res[0][0] + $res[0][1] - $res[0][2]);
}
*/
$res_k   = array();
$field_k = array();
$rows_k  = array();
$kigu_gen_kin = 0;
$sum1 = '3401';
$sum2 = '60';
$query_k = sprintf("select rep_cri, rep_de, rep_cr from financial_report_cal where rep_ymd=%d and rep_summary1='%s' and rep_summary2='%s'", $nk_ki, $sum1, $sum2);
if ($rows_k=getResultWithField2($query_k, $field_k, $res_k) <= 0) {
    $kigu_gen_kishu = 0;
} else {
    $kigu_gen_kishu = $res_k[0][0];
}
$res   = array();
$field = array();
$rows  = array();
$query = sprintf("select SUM(rep_de), SUM(rep_cr) from financial_report_month where rep_ymd>=%d and rep_ymd<=%d and rep_summary1='%s' and rep_summary2='%s'", $str_ym, $end_ym, $sum1, $sum2);
if ($rows=getResultWithField2($query, $field, $res) <= 0) {
    $kigu_gen_kin = $kigu_gen_kishu;
} else {
    $kigu_gen_kin = -($kigu_gen_kishu + ($res[0][0] - $res[0][1]));
}

// ��񹩶�񻺶��
$kigu_shisan_kin = $kigu_kin - $kigu_gen_kin;

// ��������2107 00
/*
$res   = array();
$field = array();
$rows  = array();
$jyuki_kin = 0;
$sum1 = '2107';
$sum2 = '00';
$query = sprintf("select rep_cri, rep_de, rep_cr from financial_report_cal where rep_ymd=%d and rep_summary1='%s' and rep_summary2='%s'", $yyyymm, $sum1, $sum2);
if ($rows=getResultWithField2($query, $field, $res) <= 0) {
    $jyuki_kin = 0;
} else {
    $jyuki_kin = $res[0][0] + $res[0][1] - $res[0][2];
}
*/
$res_k   = array();
$field_k = array();
$rows_k  = array();
$jyuki_kin = 0;
$sum1 = '2107';
$sum2 = '00';
$query_k = sprintf("select rep_cri, rep_de, rep_cr from financial_report_cal where rep_ymd=%d and rep_summary1='%s' and rep_summary2='%s'", $nk_ki, $sum1, $sum2);
if ($rows_k=getResultWithField2($query_k, $field_k, $res_k) <= 0) {
    $jyuki_kishu = 0;
} else {
    $jyuki_kishu = $res_k[0][0];
}
$res   = array();
$field = array();
$rows  = array();
$query = sprintf("select SUM(rep_de), SUM(rep_cr) from financial_report_month where rep_ymd>=%d and rep_ymd<=%d and rep_summary1='%s'", $str_ym, $end_ym, $sum1);
if ($rows=getResultWithField2($query, $field, $res) <= 0) {
    $jyuki_kin = $jyuki_kishu;
} else {
    $jyuki_kin = $jyuki_kishu + ($res[0][0] - $res[0][1]);
}

// �������ʸ��߳�3401 70
/*
$res   = array();
$field = array();
$rows  = array();
$jyuki_gen_kin = 0;
$sum1 = '3401';
$sum2 = '70';
$query = sprintf("select rep_cri, rep_de, rep_cr from financial_report_cal where rep_ymd=%d and rep_summary1='%s' and rep_summary2='%s'", $yyyymm, $sum1, $sum2);
if ($rows=getResultWithField2($query, $field, $res) <= 0) {
    $jyuki_gen_kin = 0;
} else {
    $jyuki_gen_kin = -($res[0][0] + $res[0][1] - $res[0][2]);
}
*/
$res_k   = array();
$field_k = array();
$rows_k  = array();
$jyuki_gen_kin = 0;
$sum1 = '3401';
$sum2 = '70';
$query_k = sprintf("select rep_cri, rep_de, rep_cr from financial_report_cal where rep_ymd=%d and rep_summary1='%s' and rep_summary2='%s'", $nk_ki, $sum1, $sum2);
if ($rows_k=getResultWithField2($query_k, $field_k, $res_k) <= 0) {
    $jyuki_gen_kishu = 0;
} else {
    $jyuki_gen_kishu = $res_k[0][0];
}
$res   = array();
$field = array();
$rows  = array();
$query = sprintf("select SUM(rep_de), SUM(rep_cr) from financial_report_month where rep_ymd>=%d and rep_ymd<=%d and rep_summary1='%s' and rep_summary2='%s'", $str_ym, $end_ym, $sum1, $sum2);
if ($rows=getResultWithField2($query, $field, $res) <= 0) {
    $jyuki_gen_kin = $jyuki_gen_kishu;
} else {
    $jyuki_gen_kin = -($jyuki_gen_kishu + ($res[0][0] - $res[0][1]));
}

// �������ʻ񻺶��
$jyuki_shisan_kin = $jyuki_kin - $jyuki_gen_kin;
// ������ڤ����ʻ񻺶��
$jyubihin_all_shisan_kin = $kigu_shisan_kin + $jyuki_shisan_kin;

//eca�� �񻺶�۹���������
$kikougu_shutoku_kin = $kigu_kin + $jyuki_kin;
//eca�� ���������߷׳�(����������)
$kikougu_rui_kin = -($kigu_gen_kin + $jyuki_gen_kin);

// �꡼����2110 00
/*
$res   = array();
$field = array();
$rows  = array();
$lease_kin = 0;
$sum1 = '2110';
$sum2 = '00';
$query = sprintf("select rep_cri, rep_de, rep_cr from financial_report_cal where rep_ymd=%d and rep_summary1='%s' and rep_summary2='%s'", $yyyymm, $sum1, $sum2);
if ($rows=getResultWithField2($query, $field, $res) <= 0) {
    $lease_kin = 0;
} else {
    $lease_kin = $res[0][0] + $res[0][1] - $res[0][2];
}
*/
$res_k   = array();
$field_k = array();
$rows_k  = array();
$lease_kin = 0;
$sum1 = '2110';
$sum2 = '00';
$query_k = sprintf("select rep_cri, rep_de, rep_cr from financial_report_cal where rep_ymd=%d and rep_summary1='%s' and rep_summary2='%s'", $nk_ki, $sum1, $sum2);
if ($rows_k=getResultWithField2($query_k, $field_k, $res_k) <= 0) {
    $lease_kishu = 0;
} else {
    $lease_kishu = $res_k[0][0];
}
$res   = array();
$field = array();
$rows  = array();
$query = sprintf("select SUM(rep_de), SUM(rep_cr) from financial_report_month where rep_ymd>=%d and rep_ymd<=%d and rep_summary1='%s'", $str_ym, $end_ym, $sum1);
if ($rows=getResultWithField2($query, $field, $res) <= 0) {
    $lease_kin = $lease_kishu;
} else {
    $lease_kin = $lease_kishu + ($res[0][0] - $res[0][1]);
}

// �꡼���񻺸��߳�3401 80
/*
$res   = array();
$field = array();
$rows  = array();
$lease_gen_kin = 0;
$sum1 = '3401';
$sum2 = '80';
$query = sprintf("select rep_cri, rep_de, rep_cr from financial_report_cal where rep_ymd=%d and rep_summary1='%s' and rep_summary2='%s'", $yyyymm, $sum1, $sum2);
if ($rows=getResultWithField2($query, $field, $res) <= 0) {
    $lease_gen_kin = 0;
} else {
    $lease_gen_kin = -($res[0][0] + $res[0][1] - $res[0][2]);
}
*/
$res_k   = array();
$field_k = array();
$rows_k  = array();
$lease_gen_kin = 0;
$sum1 = '3401';
$sum2 = '80';
$query_k = sprintf("select rep_cri, rep_de, rep_cr from financial_report_cal where rep_ymd=%d and rep_summary1='%s' and rep_summary2='%s'", $nk_ki, $sum1, $sum2);
if ($rows_k=getResultWithField2($query_k, $field_k, $res_k) <= 0) {
    $lease_gen_kishu = 0;
} else {
    $lease_gen_kishu = $res_k[0][0];
}
$res   = array();
$field = array();
$rows  = array();
$query = sprintf("select SUM(rep_de), SUM(rep_cr) from financial_report_month where rep_ymd>=%d and rep_ymd<=%d and rep_summary1='%s' and rep_summary2='%s'", $str_ym, $end_ym, $sum1, $sum2);
if ($rows=getResultWithField2($query, $field, $res) <= 0) {
    $lease_gen_kin = $lease_gen_kishu;
} else {
    $lease_gen_kin = -($lease_gen_kishu + ($res[0][0] - $res[0][1]));
}

// �꡼���񻺻񻺶��
$lease_shisan_kin = $lease_kin - $lease_gen_kin;

// ���������߷׳۹��
$gensyo_total_kin = $tate_gen_kin + $fuzoku_gen_kin + $kouchiku_gen_kin + $kikai_gen_kin + $sharyo_gen_kin + $kigu_gen_kin + $jyuki_gen_kin + $lease_gen_kin;
$gensyo_total_mi_kin = - $gensyo_total_kin;

// ����������۷�
$boka_totai_kin = $tate_shisan_kin + $fuzoku_shisan_kin + $kouchiku_shisan_kin + $kikai_shisan_kin + $sharyo_shisan_kin + $kigu_shisan_kin + $jyuki_shisan_kin + $lease_shisan_kin;

//// ̵�������
// ���ò�����2207 00
/*
$res   = array();
$field = array();
$rows  = array();
$denwa_kin = 0;
$sum1 = '2207';
$sum2 = '00';
$query = sprintf("select rep_cri, rep_de, rep_cr from financial_report_cal where rep_ymd=%d and rep_summary1='%s' and rep_summary2='%s'", $yyyymm, $sum1, $sum2);
if ($rows=getResultWithField2($query, $field, $res) <= 0) {
    $denwa_kin = 0;
} else {
    $denwa_kin = $res[0][0] + $res[0][1] - $res[0][2];
}
*/
$res_k   = array();
$field_k = array();
$rows_k  = array();
$denwa_kin = 0;
$sum1 = '2207';
$sum2 = '00';
$query_k = sprintf("select rep_cri, rep_de, rep_cr from financial_report_cal where rep_ymd=%d and rep_summary1='%s' and rep_summary2='%s'", $nk_ki, $sum1, $sum2);
if ($rows_k=getResultWithField2($query_k, $field_k, $res_k) <= 0) {
    $denwa_kishu = 0;
} else {
    $denwa_kishu = $res_k[0][0];
}
$res   = array();
$field = array();
$rows  = array();
$query = sprintf("select SUM(rep_de), SUM(rep_cr) from financial_report_month where rep_ymd>=%d and rep_ymd<=%d and rep_summary1='%s'", $str_ym, $end_ym, $sum1);
if ($rows=getResultWithField2($query, $field, $res) <= 0) {
    $denwa_kin = $denwa_kishu;
} else {
    $denwa_kin = $denwa_kishu + ($res[0][0] - $res[0][1]);
}

///// �����
/*
// �л��2301 00
$res   = array();
$field = array();
$rows  = array();
$shussi_kin = 0;
$sum1 = '2301';
$sum2 = '00';
$query = sprintf("select rep_cri, rep_de, rep_cr from financial_report_cal where rep_ymd=%d and rep_summary1='%s' and rep_summary2='%s'", $yyyymm, $sum1, $sum2);
if ($rows=getResultWithField2($query, $field, $res) <= 0) {
    $shussi_kin = 0;
} else {
    $shussi_kin = $res[0][0] + $res[0][1] - $res[0][2];
}
*/
$res_k   = array();
$field_k = array();
$rows_k  = array();
$shussi_kin = 0;
$sum1 = '2301';
$sum2 = '00';
$query_k = sprintf("select rep_cri, rep_de, rep_cr from financial_report_cal where rep_ymd=%d and rep_summary1='%s' and rep_summary2='%s'", $nk_ki, $sum1, $sum2);
if ($rows_k=getResultWithField2($query_k, $field_k, $res_k) <= 0) {
    $shussi_kishu = 0;
} else {
    $shussi_kishu = $res_k[0][0];
}
$res   = array();
$field = array();
$rows  = array();
$query = sprintf("select SUM(rep_de), SUM(rep_cr) from financial_report_month where rep_ymd>=%d and rep_ymd<=%d and rep_summary1='%s'", $str_ym, $end_ym, $sum1);
if ($rows=getResultWithField2($query, $field, $res) <= 0) {
    $shussi_kin = $shussi_kishu;
} else {
    $shussi_kin = $shussi_kishu + ($res[0][0] - $res[0][1]);
}
// �����߶��ݾڶ�2302 00
/*
$res   = array();
$field = array();
$rows  = array();
$hosyo_kin = 0;
$sum1 = '2302';
$sum2 = '00';
$query = sprintf("select rep_cri, rep_de, rep_cr from financial_report_cal where rep_ymd=%d and rep_summary1='%s' and rep_summary2='%s'", $yyyymm, $sum1, $sum2);
if ($rows=getResultWithField2($query, $field, $res) <= 0) {
    $hosyo_kin = 0;
} else {
    $hosyo_kin = $res[0][0] + $res[0][1] - $res[0][2];
}
*/
$res_k   = array();
$field_k = array();
$rows_k  = array();
$hosyo_kin = 0;
$sum1 = '2302';
$sum2 = '00';
$query_k = sprintf("select rep_cri, rep_de, rep_cr from financial_report_cal where rep_ymd=%d and rep_summary1='%s' and rep_summary2='%s'", $nk_ki, $sum1, $sum2);
if ($rows_k=getResultWithField2($query_k, $field_k, $res_k) <= 0) {
    $hosyo_kishu = 0;
} else {
    $hosyo_kishu = $res_k[0][0];
}
$res   = array();
$field = array();
$rows  = array();
$query = sprintf("select SUM(rep_de), SUM(rep_cr) from financial_report_month where rep_ymd>=%d and rep_ymd<=%d and rep_summary1='%s'", $str_ym, $end_ym, $sum1);
if ($rows=getResultWithField2($query, $field, $res) <= 0) {
    $hosyo_kin = $hosyo_kishu;
} else {
    $hosyo_kin = $hosyo_kishu + ($res[0][0] - $res[0][1]);
}

// �������פη׻�
$toushi_total_kin    = $shussi_kin + $hosyo_kin;

///// ήư��ģ�
// ��ʧ��������1508 00
/*
$res   = array();
$field = array();
$rows  = array();
$kari00_kin = 0;
$sum1 = '1508';
$sum2 = '00';
$query = sprintf("select rep_cri, rep_de, rep_cr from financial_report_cal where rep_ymd=%d and rep_summary1='%s' and rep_summary2='%s'", $yyyymm, $sum1, $sum2);
if ($rows=getResultWithField2($query, $field, $res) <= 0) {
    $kari00_kin = 0;
} else {
    $kari00_kin = $res[0][0] + $res[0][1] - $res[0][2];
}
*/
$res_k   = array();
$field_k = array();
$rows_k  = array();
$kari00_kin = 0;
$sum1 = '1508';
$sum2 = '00';
$query_k = sprintf("select rep_cri, rep_de, rep_cr from financial_report_cal where rep_ymd=%d and rep_summary1='%s' and rep_summary2='%s'", $nk_ki, $sum1, $sum2);
if ($rows_k=getResultWithField2($query_k, $field_k, $res_k) <= 0) {
    $kari00_kishu = 0;
} else {
    $kari00_kishu = $res_k[0][0];
}
$res   = array();
$field = array();
$rows  = array();
$query = sprintf("select SUM(rep_de), SUM(rep_cr) from financial_report_month where rep_ymd>=%d and rep_ymd<=%d and rep_summary1='%s'", $str_ym, $end_ym, $sum1);
if ($rows=getResultWithField2($query, $field, $res) <= 0) {
    $kari00_kin = $kari00_kishu;
} else {
    $kari00_kin = $kari00_kishu + ($res[0][0] - $res[0][1]);
}

// ��ʧ��������(͢��)1508 20
/*
$res   = array();
$field = array();
$rows  = array();
$kari20_kin = 0;
$sum1 = '1508';
$sum2 = '20';
$query = sprintf("select rep_cri, rep_de, rep_cr from financial_report_cal where rep_ymd=%d and rep_summary1='%s' and rep_summary2='%s'", $yyyymm, $sum1, $sum2);
if ($rows=getResultWithField2($query, $field, $res) <= 0) {
    $kari20_kin = 0;
} else {
    $kari20_kin = $res[0][0] + $res[0][1] - $res[0][2];
}

$res_k   = array();
$field_k = array();
$rows_k  = array();
$kari20_kin = 0;
$sum1 = '1508';
$sum2 = '20';
$query_k = sprintf("select rep_cri, rep_de, rep_cr from financial_report_cal where rep_ymd=%d and rep_summary1='%s' and rep_summary2='%s'", $nk_ki, $sum1, $sum2);
if ($rows_k=getResultWithField2($query_k, $field_k, $res_k) <= 0) {
    $kari20_kishu = 0;
} else {
    $kari20_kishu = $res_k[0][0];
}
$res   = array();
$field = array();
$rows  = array();
$query = sprintf("select SUM(rep_de), SUM(rep_cr) from financial_report_month where rep_ymd>=%d and rep_ymd<=%d and rep_summary1='%s' and rep_summary2='%s'", $str_ym, $end_ym, $sum1, $sum2);
if ($rows=getResultWithField2($query, $field, $res) <= 0) {
    $kari20_kin = $kari20_kishu;
} else {
    $kari20_kin = $kari20_kishu + ($res[0][0] - $res[0][1]);
}
*/

// ��ʧ���������ι��(�������˹�פ��Ƥ���٤���ʤ��ä�)
$kari_zei_kin = - $kari00_kin;

// ��ʧ��������1560 00
/*
$res   = array();
$field = array();
$rows  = array();
$mae_zei_kin = 0;
$sum1 = '1560';
$sum2 = '00';
$query = sprintf("select rep_cri, rep_de, rep_cr from financial_report_cal where rep_ymd=%d and rep_summary1='%s' and rep_summary2='%s'", $yyyymm, $sum1, $sum2);
if ($rows=getResultWithField2($query, $field, $res) <= 0) {
    $mae_zei_kin = 0;
} else {
    $mae_zei_kin = $res[0][0] + $res[0][1] - $res[0][2];
}
*/
$res_k   = array();
$field_k = array();
$rows_k  = array();
$mae_zei_kin = 0;
$sum1 = '1560';
$sum2 = '00';
$query_k = sprintf("select rep_cri, rep_de, rep_cr from financial_report_cal where rep_ymd=%d and rep_summary1='%s' and rep_summary2='%s'", $nk_ki, $sum1, $sum2);
if ($rows_k=getResultWithField2($query_k, $field_k, $res_k) <= 0) {
    $mae_zei_kishu = 0;
} else {
    $mae_zei_kishu = $res_k[0][0];
}
$res   = array();
$field = array();
$rows  = array();
$query = sprintf("select SUM(rep_de), SUM(rep_cr) from financial_report_month where rep_ymd>=%d and rep_ymd<=%d and rep_summary1='%s' and rep_summary2='%s'", $str_ym, $end_ym, $sum1, $sum2);
if ($rows=getResultWithField2($query, $field, $res) <= 0) {
    $mae_zei_kin = $mae_zei_kishu;
} else {
    $mae_zei_kin = -($mae_zei_kishu + ($res[0][0] - $res[0][1]));
}

// ������������3227 00
/*
$res   = array();
$field = array();
$rows  = array();
$kariuke_zei_kin = 0;
$sum1 = '3227';
$sum2 = '00';
$query = sprintf("select rep_cri, rep_de, rep_cr from financial_report_cal where rep_ymd=%d and rep_summary1='%s' and rep_summary2='%s'", $yyyymm, $sum1, $sum2);
if ($rows=getResultWithField2($query, $field, $res) <= 0) {
    $kariuke_zei_kin = 0;
} else {
    $kariuke_zei_kin = -($res[0][0] + $res[0][1] - $res[0][2]);
}
*/
$res_k   = array();
$field_k = array();
$rows_k  = array();
$kariuke_zei_kin = 0;
$sum1 = '3227';
$sum2 = '00';
$query_k = sprintf("select rep_cri, rep_de, rep_cr from financial_report_cal where rep_ymd=%d and rep_summary1='%s' and rep_summary2='%s'", $nk_ki, $sum1, $sum2);
if ($rows_k=getResultWithField2($query_k, $field_k, $res_k) <= 0) {
    $kariuke_zei_kishu = 0;
} else {
    $kariuke_zei_kishu = $res_k[0][0];
}
$res   = array();
$field = array();
$rows  = array();
$query = sprintf("select SUM(rep_de), SUM(rep_cr) from financial_report_month where rep_ymd>=%d and rep_ymd<=%d and rep_summary1='%s' and rep_summary2='%s'", $str_ym, $end_ym, $sum1, $sum2);
if ($rows=getResultWithField2($query, $field, $res) <= 0) {
    $kariuke_zei_kin = $kariuke_zei_kishu;
} else {
    $kariuke_zei_kin = -($kariuke_zei_kishu + ($res[0][0] - $res[0][1]));
}

// ̤ʧ��������3228 00
/*
$res   = array();
$field = array();
$rows  = array();
$miharai_zei_kin = 0;
$sum1 = '3228';
$sum2 = '00';
$query = sprintf("select rep_cri, rep_de, rep_cr from financial_report_cal where rep_ymd=%d and rep_summary1='%s' and rep_summary2='%s'", $yyyymm, $sum1, $sum2);
if ($rows=getResultWithField2($query, $field, $res) <= 0) {
    $miharai_zei_kin = 0;
} else {
    $miharai_zei_kin = -($res[0][0] + $res[0][1] - $res[0][2]);
}
*/
$res_k   = array();
$field_k = array();
$rows_k  = array();
$miharai_zei_kin = 0;
$sum1 = '3228';
$sum2 = '00';
$query_k = sprintf("select rep_cri, rep_de, rep_cr from financial_report_cal where rep_ymd=%d and rep_summary1='%s' and rep_summary2='%s'", $nk_ki, $sum1, $sum2);
if ($rows_k=getResultWithField2($query_k, $field_k, $res_k) <= 0) {
    $miharai_zei_kishu = 0;
} else {
    $miharai_zei_kishu = $res_k[0][0];
}
$res   = array();
$field = array();
$rows  = array();
$query = sprintf("select SUM(rep_de), SUM(rep_cr) from financial_report_month where rep_ymd>=%d and rep_ymd<=%d and rep_summary1='%s' and rep_summary2='%s'", $str_ym, $end_ym, $sum1, $sum2);
if ($rows=getResultWithField2($query, $field, $res) <= 0) {
    $miharai_zei_kin = $miharai_zei_kishu;
} else {
    $miharai_zei_kin = -($miharai_zei_kishu + ($res[0][0] - $res[0][1]));
}

// ̤ʧ���������ι�פη׻�
$mihazei_total_kin = $kari_zei_kin + $mae_zei_kin + $kariuke_zei_kin + $miharai_zei_kin;

///// ήư��ģ�
/*
// ��ݶ�3103 00
$res   = array();
$field = array();
$rows  = array();
$kaikake_kin = 0;
$sum1 = '3103';
$sum2 = '00';
$query = sprintf("select rep_cri, rep_de, rep_cr from financial_report_cal where rep_ymd=%d and rep_summary1='%s' and rep_summary2='%s'", $yyyymm, $sum1, $sum2);
if ($rows=getResultWithField2($query, $field, $res) <= 0) {
    $kaikake_kin = 0;
} else {
    $kaikake_kin = -($res[0][0] + $res[0][1] - $res[0][2]);
}
*/
$res_k   = array();
$field_k = array();
$rows_k  = array();
$kaikake_kin = 0;
$sum1 = '3103';
$sum2 = '00';
$query_k = sprintf("select rep_cri, rep_de, rep_cr from financial_report_cal where rep_ymd=%d and rep_summary1='%s' and rep_summary2='%s'", $nk_ki, $sum1, $sum2);
if ($rows_k=getResultWithField2($query_k, $field_k, $res_k) <= 0) {
    $kaikake_kishu = 0;
} else {
    $kaikake_kishu = $res_k[0][0];
}
$res   = array();
$field = array();
$rows  = array();
$query = sprintf("select SUM(rep_de), SUM(rep_cr) from financial_report_month where rep_ymd>=%d and rep_ymd<=%d and rep_summary1='%s' and rep_summary2='%s'", $str_ym, $end_ym, $sum1, $sum2);
if ($rows=getResultWithField2($query, $field, $res) <= 0) {
    $kaikake_kin = $kaikake_kishu;
} else {
    $kaikake_kin = -($kaikake_kishu + ($res[0][0] - $res[0][1]));
}

// ��ݶ��������3102 00
/*
$res   = array();
$field = array();
$rows  = array();
$kaikake_kiji_kin = 0;
$sum1 = '3102';
$sum2 = '00';
$query = sprintf("select rep_cri, rep_de, rep_cr from financial_report_cal where rep_ymd=%d and rep_summary1='%s' and rep_summary2='%s'", $yyyymm, $sum1, $sum2);
if ($rows=getResultWithField2($query, $field, $res) <= 0) {
    $kaikake_kiji_kin = 0;
} else {
    $kaikake_kiji_kin = -($res[0][0] + $res[0][1] - $res[0][2]);
}
*/
$res_k   = array();
$field_k = array();
$rows_k  = array();
$kaikake_kiji_kin = 0;
$sum1 = '3102';
$sum2 = '00';
$query_k = sprintf("select rep_cri, rep_de, rep_cr from financial_report_cal where rep_ymd=%d and rep_summary1='%s' and rep_summary2='%s'", $nk_ki, $sum1, $sum2);
if ($rows_k=getResultWithField2($query_k, $field_k, $res_k) <= 0) {
    $kaikake_kiji_kishu = 0;
} else {
    $kaikake_kiji_kishu = $res_k[0][0];
}
$res   = array();
$field = array();
$rows  = array();
$query = sprintf("select SUM(rep_de), SUM(rep_cr) from financial_report_month where rep_ymd>=%d and rep_ymd<=%d and rep_summary1='%s' and rep_summary2='%s'", $str_ym, $end_ym, $sum1, $sum2);
if ($rows=getResultWithField2($query, $field, $res) <= 0) {
    $kaikake_kiji_kin = $kaikake_kiji_kishu;
} else {
    $kaikake_kiji_kin = -($kaikake_kiji_kishu + ($res[0][0] - $res[0][1]));
}

// ��ݶ�ι�פη׻�
$kaikake_total_kin = $kaikake_kin + $kaikake_kiji_kin;

///// ήư��ģ�
// ̤ʧ��3105 00
/*
$res   = array();
$field = array();
$rows  = array();
$miharai_kin = 0;
$sum1 = '3105';
$sum2 = '00';
$query = sprintf("select rep_cri, rep_de, rep_cr from financial_report_cal where rep_ymd=%d and rep_summary1='%s' and rep_summary2='%s'", $yyyymm, $sum1, $sum2);
if ($rows=getResultWithField2($query, $field, $res) <= 0) {
    $miharai_kin = 0;
} else {
    $miharai_kin = -($res[0][0] + $res[0][1] - $res[0][2]);
}
*/
$res_k   = array();
$field_k = array();
$rows_k  = array();
$miharai_kin = 0;
$sum1 = '3105';
$sum2 = '00';
$query_k = sprintf("select rep_cri, rep_de, rep_cr from financial_report_cal where rep_ymd=%d and rep_summary1='%s' and rep_summary2='%s'", $nk_ki, $sum1, $sum2);
if ($rows_k=getResultWithField2($query_k, $field_k, $res_k) <= 0) {
    $miharai_kishu = 0;
} else {
    $miharai_kishu = $res_k[0][0];
}
$res   = array();
$field = array();
$rows  = array();
$query = sprintf("select SUM(rep_de), SUM(rep_cr) from financial_report_month where rep_ymd>=%d and rep_ymd<=%d and rep_summary1='%s' and rep_summary2='%s'", $str_ym, $end_ym, $sum1, $sum2);
if ($rows=getResultWithField2($query, $field, $res) <= 0) {
    $miharai_kin = $miharai_kishu;
} else {
    $miharai_kin = -($miharai_kishu + ($res[0][0] - $res[0][1]));
}

// ̤ʧ���������3106 00
/*
$res   = array();
$field = array();
$rows  = array();
$miharai_kiji_kin = 0;
$sum1 = '3106';
$sum2 = '00';
$query = sprintf("select rep_cri, rep_de, rep_cr from financial_report_cal where rep_ymd=%d and rep_summary1='%s' and rep_summary2='%s'", $yyyymm, $sum1, $sum2);
if ($rows=getResultWithField2($query, $field, $res) <= 0) {
    $miharai_kiji_kin = 0;
} else {
    $miharai_kiji_kin = -($res[0][0] + $res[0][1] - $res[0][2]);
}
*/
$res_k   = array();
$field_k = array();
$rows_k  = array();
$miharai_kiji_kin = 0;
$sum1 = '3106';
$sum2 = '00';
$query_k = sprintf("select rep_cri, rep_de, rep_cr from financial_report_cal where rep_ymd=%d and rep_summary1='%s' and rep_summary2='%s'", $nk_ki, $sum1, $sum2);
if ($rows_k=getResultWithField2($query_k, $field_k, $res_k) <= 0) {
    $miharai_kiji_kishu = 0;
} else {
    $miharai_kiji_kishu = $res_k[0][0];
}
$res   = array();
$field = array();
$rows  = array();
$query = sprintf("select SUM(rep_de), SUM(rep_cr) from financial_report_month where rep_ymd>=%d and rep_ymd<=%d and rep_summary1='%s' and rep_summary2='%s'", $str_ym, $end_ym, $sum1, $sum2);
if ($rows=getResultWithField2($query, $field, $res) <= 0) {
    $miharai_kiji_kin = $miharai_kiji_kishu;
} else {
    $miharai_kiji_kin = -($miharai_kiji_kishu + ($res[0][0] - $res[0][1]));
}

// ̤ʧ��ι�פη׻�
$miharai_total_kin = $miharai_kin + $miharai_kiji_kin;

///// ήư��ģ�
// ������3221 00
/*
$res   = array();
$field = array();
$rows  = array();
$maeuke_kin = 0;
$sum1 = '3221';
$sum2 = '00';
$query = sprintf("select rep_cri, rep_de, rep_cr from financial_report_cal where rep_ymd=%d and rep_summary1='%s' and rep_summary2='%s'", $yyyymm, $sum1, $sum2);
if ($rows=getResultWithField2($query, $field, $res) <= 0) {
    $maeuke_kin = 0;
} else {
    $maeuke_kin = -($res[0][0] + $res[0][1] - $res[0][2]);
}
*/
$res_k   = array();
$field_k = array();
$rows_k  = array();
$maeuke_kin = 0;
$sum1 = '3221';
$sum2 = '00';
$query_k = sprintf("select rep_cri, rep_de, rep_cr from financial_report_cal where rep_ymd=%d and rep_summary1='%s' and rep_summary2='%s'", $nk_ki, $sum1, $sum2);
if ($rows_k=getResultWithField2($query_k, $field_k, $res_k) <= 0) {
    $maeuke_kishu = 0;
} else {
    $maeuke_kishu = $res_k[0][0];
}
$res   = array();
$field = array();
$rows  = array();
$query = sprintf("select SUM(rep_de), SUM(rep_cr) from financial_report_month where rep_ymd>=%d and rep_ymd<=%d and rep_summary1='%s' and rep_summary2='%s'", $str_ym, $end_ym, $sum1, $sum2);
if ($rows=getResultWithField2($query, $field, $res) <= 0) {
    $maeuke_kin = $maeuke_kishu;
} else {
    $maeuke_kin = -($maeuke_kishu + ($res[0][0] - $res[0][1]));
}

// ����¾ήư���3229 00
/*
$res   = array();
$field = array();
$rows  = array();
$sonota_ryudo_kin = 0;
$sum1 = '3229';
$sum2 = '00';
$query = sprintf("select rep_cri, rep_de, rep_cr from financial_report_cal where rep_ymd=%d and rep_summary1='%s' and rep_summary2='%s'", $yyyymm, $sum1, $sum2);
if ($rows=getResultWithField2($query, $field, $res) <= 0) {
    $sonota_ryudo_kin = 0;
} else {
    $sonota_ryudo_kin = -($res[0][0] + $res[0][1] - $res[0][2]);
}
*/
$res_k   = array();
$field_k = array();
$rows_k  = array();
$sonota_ryudo_kin = 0;
$sum1 = '3229';
$sum2 = '00';
$query_k = sprintf("select rep_cri, rep_de, rep_cr from financial_report_cal where rep_ymd=%d and rep_summary1='%s' and rep_summary2='%s'", $nk_ki, $sum1, $sum2);
if ($rows_k=getResultWithField2($query_k, $field_k, $res_k) <= 0) {
    $sonota_ryudo_kishu = 0;
} else {
    $sonota_ryudo_kishu = $res_k[0][0];
}
$res   = array();
$field = array();
$rows  = array();
$query = sprintf("select SUM(rep_de), SUM(rep_cr) from financial_report_month where rep_ymd>=%d and rep_ymd<=%d and rep_summary1='%s' and rep_summary2='%s'", $str_ym, $end_ym, $sum1, $sum2);
if ($rows=getResultWithField2($query, $field, $res) <= 0) {
    $sonota_ryudo_kin = $sonota_ryudo_kishu;
} else {
    $sonota_ryudo_kin = -($sonota_ryudo_kishu + ($res[0][0] - $res[0][1]));
}

// ����¾ήư��Ĥι�פη׻�
$sonota_ryudo_total_kin = $maeuke_kin + $sonota_ryudo_kin;
// ήư��Ĥι�פη׻���ήư��ģ������ι�ס�
$ryudo_fusai_total_kin = $kaikake_total_kin + $miharai_total_kin + $sonota_ryudo_total_kin;

///// »�׷׻���η׻�
///// ��¤��������
// ����ê��
// ����1404 00
/*
$res   = array();
$field = array();
$rows  = array();
$z_seihin_kin = 0;
$sum1 = '1404';
$sum2 = '00';
$query = sprintf("select rep_cri, rep_de, rep_cr from financial_report_cal where rep_ymd=%d and rep_summary1='%s' and rep_summary2='%s'", $b_yyyymm, $sum1, $sum2);
if ($rows=getResultWithField2($query, $field, $res) <= 0) {
    $z_seihin_kin = 0;
} else {
    $z_seihin_kin = $res[0][0] + $res[0][1] - $res[0][2];
}
*/
$res_k   = array();
$field_k = array();
$rows_k  = array();
$z_seihin_kin = 0;
$sum1 = '1404';
$sum2 = '00';
$query_k = sprintf("select rep_cri, rep_de, rep_cr from financial_report_cal where rep_ymd=%d and rep_summary1='%s' and rep_summary2='%s'", $nk_ki, $sum1, $sum2);
if ($rows_k=getResultWithField2($query_k, $field_k, $res_k) <= 0) {
    $z_seihin_kin = 0;
} else {
    $z_seihin_kin = $res_k[0][0];
}

// ���ʻų���1405 00
/*
$res   = array();
$field = array();
$rows  = array();
$z_seihinsi_kin = 0;
$sum1 = '1405';
$sum2 = '00';
$query = sprintf("select rep_cri, rep_de, rep_cr from financial_report_cal where rep_ymd=%d and rep_summary1='%s' and rep_summary2='%s'", $b_yyyymm, $sum1, $sum2);
if ($rows=getResultWithField2($query, $field, $res) <= 0) {
    $z_seihinsi_kin = 0;
} else {
    $z_seihinsi_kin = $res[0][0] + $res[0][1] - $res[0][2];
}
*/
$res_k   = array();
$field_k = array();
$rows_k  = array();
$z_seihinsi_kin = 0;
$sum1 = '1405';
$sum2 = '00';
$query_k = sprintf("select rep_cri, rep_de, rep_cr from financial_report_cal where rep_ymd=%d and rep_summary1='%s' and rep_summary2='%s'", $nk_ki, $sum1, $sum2);
if ($rows_k=getResultWithField2($query_k, $field_k, $res_k) <= 0) {
    $z_seihinsi_kin = 0;
} else {
    $z_seihinsi_kin = $res_k[0][0];
}

// ����1406 00
/*
$res   = array();
$field = array();
$rows  = array();
$z_buhin_kin = 0;
$sum1 = '1406';
$sum2 = '00';
$query = sprintf("select rep_cri, rep_de, rep_cr from financial_report_cal where rep_ymd=%d and rep_summary1='%s' and rep_summary2='%s'", $b_yyyymm, $sum1, $sum2);
if ($rows=getResultWithField2($query, $field, $res) <= 0) {
    $z_buhin_kin = 0;
} else {
    $z_buhin_kin = $res[0][0] + $res[0][1] - $res[0][2];
}
*/
$res_k   = array();
$field_k = array();
$rows_k  = array();
$z_buhin_kin = 0;
$sum1 = '1406';
$sum2 = '00';
$query_k = sprintf("select rep_cri, rep_de, rep_cr from financial_report_cal where rep_ymd=%d and rep_summary1='%s' and rep_summary2='%s'", $nk_ki, $sum1, $sum2);
if ($rows_k=getResultWithField2($query_k, $field_k, $res_k) <= 0) {
    $z_buhin_kin = 0;
} else {
    $z_buhin_kin = $res_k[0][0];
}

// ���ʻų���1407 30
/*
$res   = array();
$field = array();
$rows  = array();
$z_buhinsi_kin = 0;
$sum1 = '1407';
$sum2 = '30';
$query = sprintf("select rep_cri, rep_de, rep_cr from financial_report_cal where rep_ymd=%d and rep_summary1='%s' and rep_summary2='%s'", $b_yyyymm, $sum1, $sum2);
if ($rows=getResultWithField2($query, $field, $res) <= 0) {
    $z_buhinsi_kin = 0;
} else {
    $z_buhinsi_kin = $res[0][0] + $res[0][1] - $res[0][2];
}
*/
$res_k   = array();
$field_k = array();
$rows_k  = array();
$z_buhinsi_kin = 0;
$sum1 = '1407';
$sum2 = '30';
$query_k = sprintf("select rep_cri, rep_de, rep_cr from financial_report_cal where rep_ymd=%d and rep_summary1='%s' and rep_summary2='%s'", $nk_ki, $sum1, $sum2);
if ($rows_k=getResultWithField2($query_k, $field_k, $res_k) <= 0) {
    $z_buhinsi_kin = 0;
} else {
    $z_buhinsi_kin = $res_k[0][0];
}

// ������1408 00
/*
$res   = array();
$field = array();
$rows  = array();
$z_genzai_kin = 0;
$sum1 = '1408';
$sum2 = '00';
$query = sprintf("select rep_cri, rep_de, rep_cr from financial_report_cal where rep_ymd=%d and rep_summary1='%s' and rep_summary2='%s'", $b_yyyymm, $sum1, $sum2);
if ($rows=getResultWithField2($query, $field, $res) <= 0) {
    $z_genzai_kin = 0;
} else {
    $z_genzai_kin = $res[0][0] + $res[0][1] - $res[0][2];
}
*/
$res_k   = array();
$field_k = array();
$rows_k  = array();
$z_genzai_kin = 0;
$sum1 = '1408';
$sum2 = '00';
$query_k = sprintf("select rep_cri, rep_de, rep_cr from financial_report_cal where rep_ymd=%d and rep_summary1='%s' and rep_summary2='%s'", $nk_ki, $sum1, $sum2);
if ($rows_k=getResultWithField2($query_k, $field_k, $res_k) <= 0) {
    $z_genzai_kin = 0;
} else {
    $z_genzai_kin = $res_k[0][0];
}

// ����¾��ê����1409 �ι��
/*
$res   = array();
$field = array();
$rows  = array();
$z_sonotatana_kin = 0;
$sum1 = '1409';
$sum2 = '00';
$query = sprintf("select SUM(rep_cri), SUM(rep_de), SUM(rep_cr) from financial_report_cal where rep_ymd=%d and rep_summary1='%s'", $b_yyyymm, $sum1);
if ($rows=getResultWithField2($query, $field, $res) <= 0) {
    $z_sonotatana_kin = 0;
} else {
    $z_sonotatana_kin = $res[0][0] + $res[0][1] - $res[0][2];
}
*/
$res_k   = array();
$field_k = array();
$rows_k  = array();
$z_sonotatana_kin = 0;
$sum1 = '1409';
$sum2 = '00';
$query_k = sprintf("select SUM(rep_cri), SUM(rep_de), SUM(rep_cr) from financial_report_cal where rep_ymd=%d and rep_summary1='%s'", $nk_ki, $sum1);
if ($rows_k=getResultWithField2($query_k, $field_k, $res_k) <= 0) {
    $z_sonotatana_kin = 0;
} else {
    $z_sonotatana_kin = $res_k[0][0];
}

// �߸˹�פη׻�
$z_zaiko_total_kin    = $z_seihin_kin + $z_seihinsi_kin + $z_buhin_kin + $z_buhinsi_kin + $z_genzai_kin + $z_sonotatana_kin;
// �ų��ʹ�פη׻�
$z_sikakari_total_kin = $z_seihinsi_kin;
// �������ڤ���¢�ʹ�פη׻�
$z_gencho_total_kin   = $z_buhin_kin + $z_buhinsi_kin + $z_genzai_kin + $z_sonotatana_kin;

//// ����ê����
// �߸˹�פη׻�
$kimatsu_total_kin  = $seihin_kin + $seihinsi_kin + $buhin_kin + $buhinsi_kin + $genzai_kin + $sonotatana_kin;
// �ų��ʹ�פη׻�
$kimatsu_sikakari_total_kin = $seihinsi_kin;
// �������ڤ���¢�ʹ�פη׻�
$kimatsu_gencho_total_kin   = $buhin_kin + $buhinsi_kin + $genzai_kin + $sonotatana_kin;

//// P / L   �Ķȳ�»�ס�����»�ס�¾
// �Ķȳ����פη׻�
// ������9103 �ι��
$res   = array();
$field = array();
$rows  = array();
$zatsushu_kin = 0;
$sum1 = '9103';
$sum2 = '00';
$query = sprintf("select SUM(rep_de), SUM(rep_cr) from financial_report_month where rep_ymd>=%d and rep_ymd<=%d and rep_summary1='%s'", $str_ym, $end_ym, $sum1);
if ($rows=getResultWithField2($query, $field, $res) <= 0) {
    $zatsushu_kin = 0;
} else {
    $zatsushu_kin = $res[0][1] - $res[0][0];
}
// ��̳��������9107 �ι��
$res   = array();
$field = array();
$rows  = array();
$gyomushu_kin = 0;
$sum1 = '9107';
$sum2 = '00';
$query = sprintf("select SUM(rep_de), SUM(rep_cr) from financial_report_month where rep_ymd>=%d and rep_ymd<=%d and rep_summary1='%s'", $str_ym, $end_ym, $sum1);
if ($rows=getResultWithField2($query, $field, $res) <= 0) {
    $gyomushu_kin = 0;
} else {
    $gyomushu_kin = $res[0][1] - $res[0][0];
}

// �Ķȳ����׹�פη׻�
$eigyo_shueki_total_kin = $zatsushu_kin + $gyomushu_kin;

// �Ķȳ����Ѥη׻�
// ����¾�Ķȳ�����9310 �ι��
$res   = array();
$field = array();
$rows  = array();
$sonota_eihiyo_kin = 0;
$sum1 = '9310';
$sum2 = '00';
$query = sprintf("select SUM(rep_de), SUM(rep_cr) from financial_report_month where rep_ymd>=%d and rep_ymd<=%d and rep_summary1='%s'", $str_ym, $end_ym, $sum1);
if ($rows=getResultWithField2($query, $field, $res) <= 0) {
    $sonota_eihiyo_kin = 0;
} else {
    $sonota_eihiyo_kin = -($res[0][1] - $res[0][0]);
}
// ��������»9317 �ι��
$res   = array();
$field = array();
$rows  = array();
$kotei_baison_kin = 0;
$sum1 = '9317';
$sum2 = '00';
$query = sprintf("select SUM(rep_de), SUM(rep_cr) from financial_report_month where rep_ymd>=%d and rep_ymd<=%d and rep_summary1='%s'", $str_ym, $end_ym, $sum1);
if ($rows=getResultWithField2($query, $field, $res) <= 0) {
    $kotei_baison_kin = 0;
} else {
    $kotei_baison_kin = -($res[0][1] - $res[0][0]);
}

// ����񻺽���»9311 �ι��
$res   = array();
$field = array();
$rows  = array();
$kotei_jyoson_kin = 0;
$sum1 = '9311';
$sum2 = '00';
$query = sprintf("select SUM(rep_de), SUM(rep_cr) from financial_report_month where rep_ymd>=%d and rep_ymd<=%d and rep_summary1='%s'", $str_ym, $end_ym, $sum1);
if ($rows=getResultWithField2($query, $field, $res) <= 0) {
    $kotei_jyoson_kin = 0;
} else {
    $kotei_jyoson_kin = -($res[0][1] - $res[0][0]);
}

$kotei_son_total = $kotei_baison_kin + $kotei_jyoson_kin;

// ˡ�������η׻�
// ˡ���ǵڤӽ�̱��9401 �ι��
$res   = array();
$field = array();
$rows  = array();
$hojin_jyumin_zei_kin = 0;
$sum1 = '9401';
$sum2 = '00';
$query = sprintf("select SUM(rep_de), SUM(rep_cr) from financial_report_month where rep_ymd>=%d and rep_ymd<=%d and rep_summary1='%s'", $str_ym, $end_ym, $sum1);
if ($rows=getResultWithField2($query, $field, $res) <= 0) {
    $hojin_jyumin_zei_kin = 0;
} else {
    $hojin_jyumin_zei_kin = -($res[0][1] - $res[0][0]);
}
// ������9402 �ι��
$res   = array();
$field = array();
$rows  = array();
$jigyo_zei_kin = 0;
$sum1 = '9402';
$sum2 = '00';
$query = sprintf("select SUM(rep_de), SUM(rep_cr) from financial_report_month where rep_ymd>=%d and rep_ymd<=%d and rep_summary1='%s'", $str_ym, $end_ym, $sum1);
if ($rows=getResultWithField2($query, $field, $res) <= 0) {
    $jigyo_zei_kin = 0;
} else {
    $jigyo_zei_kin = -($res[0][1] - $res[0][0]);
}

// ˡ������Ĵ����9405 �ι��
$res   = array();
$field = array();
$rows  = array();
$hojin_chosei_kin = 0;
$sum1 = '9405';
$sum2 = '00';
$query = sprintf("select SUM(rep_de), SUM(rep_cr) from financial_report_month where rep_ymd>=%d and rep_ymd<=%d and rep_summary1='%s'", $str_ym, $end_ym, $sum1);
if ($rows=getResultWithField2($query, $field, $res) <= 0) {
    $hojin_chosei_kin = 0;
} else {
    $hojin_chosei_kin = -($res[0][1] - $res[0][0]);
}

// ˡ��������פη׻�
$hojin_zeito_total_kin = $hojin_jyumin_zei_kin + $jigyo_zei_kin + $hojin_chosei_kin;

// eca��ˡ���ǡ���̱�ǵڤӻ�����
$eca_hojin_zeito_total_kin = $hojin_jyumin_zei_kin + $jigyo_zei_kin;

//// �������ٽ�η׻�
// �δ��� ι���������
// ι�������
$res   = array();
$field = array();
$rows  = array();
$han_ryohi_kin = 0;
$note  = '�δ���ι�������';
$query = sprintf("select SUM(kin) from profit_loss_keihi_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='%s'", $str_ym, $end_ym, $note);
if ($rows=getResultWithField2($query, $field, $res) <= 0) {
    $han_ryohi_kin = 0;
} else {
    $han_ryohi_kin = $res[0][0];
}
// ������ĥ��
$res   = array();
$field = array();
$rows  = array();
$han_kaigai_kin = 0;
$note  = '�δ��񳤳���ĥ��';
$query = sprintf("select SUM(kin) from profit_loss_keihi_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='%s'", $str_ym, $end_ym, $note);
if ($rows=getResultWithField2($query, $field, $res) <= 0) {
    $han_kaigai_kin = 0;
} else {
    $han_kaigai_kin = $res[0][0];
}

// �δ���ι��������פη׻�
$han_ryohi_total_kin = $han_ryohi_kin + $han_kaigai_kin;

// �δ��� ������������
// ����������
$res   = array();
$field = array();
$rows  = array();
$han_kokoku_kin = 0;
$note  = '�δ��񹭹�������';
$query = sprintf("select SUM(kin) from profit_loss_keihi_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='%s'", $str_ym, $end_ym, $note);
if ($rows=getResultWithField2($query, $field, $res) <= 0) {
    $han_kokoku_kin = 0;
} else {
    $han_kokoku_kin = $res[0][0];
}
// �����
$res   = array();
$field = array();
$rows  = array();
$han_kyujin_kin = 0;
$note  = '�δ�������';
$query = sprintf("select SUM(kin) from profit_loss_keihi_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='%s'", $str_ym, $end_ym, $note);
if ($rows=getResultWithField2($query, $field, $res) <= 0) {
    $han_kyujin_kin = 0;
} else {
    $han_kyujin_kin = $res[0][0];
}

// �δ��񹭹��������פη׻�
$han_kokoku_total_kin = $han_kokoku_kin + $han_kyujin_kin;

// �δ��� ��̳��������
// ��̳������
$res   = array();
$field = array();
$rows  = array();
$han_gyomu_kin = 0;
$note  = '�δ����̳������';
$query = sprintf("select SUM(kin) from profit_loss_keihi_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='%s'", $str_ym, $end_ym, $note);
if ($rows=getResultWithField2($query, $field, $res) <= 0) {
    $han_gyomu_kin = 0;
} else {
    $han_gyomu_kin = $res[0][0];
}
// ��ʧ�����
$res   = array();
$field = array();
$rows  = array();
$han_tesu_kin = 0;
$note  = '�δ����ʧ�����';
$query = sprintf("select SUM(kin) from profit_loss_keihi_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='%s'", $str_ym, $end_ym, $note);
if ($rows=getResultWithField2($query, $field, $res) <= 0) {
    $han_tesu_kin = 0;
} else {
    $han_tesu_kin = $res[0][0];
}

// �δ����̳�������פη׻�
$han_gyomu_total_kin = $han_gyomu_kin + $han_tesu_kin;

// �δ��� ���Ǹ��ݹ��
// ������
$res   = array();
$field = array();
$rows  = array();
$han_jigyo_kin = 0;
$note  = '�δ��������';
$query = sprintf("select SUM(kin) from profit_loss_keihi_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='%s'", $str_ym, $end_ym, $note);
if ($rows=getResultWithField2($query, $field, $res) <= 0) {
    $han_jigyo_kin = 0;
} else {
    $han_jigyo_kin = $res[0][0];
}
// ���Ǹ���
$res   = array();
$field = array();
$rows  = array();
$han_zeikoka_kin = 0;
$note  = '�δ�����Ǹ���';
$query = sprintf("select SUM(kin) from profit_loss_keihi_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='%s'", $str_ym, $end_ym, $note);
if ($rows=getResultWithField2($query, $field, $res) <= 0) {
    $han_zeikoka_kin = 0;
} else {
    $han_zeikoka_kin = $res[0][0];
}

// �δ�����Ǹ��ݹ�פη׻�
$han_zeikoka_total_kin = $han_jigyo_kin + $han_zeikoka_kin;

// �δ��� ��̳�Ѿ���������
// ��̳�Ѿ�������
$res   = array();
$field = array();
$rows  = array();
$han_jimuyo_kin = 0;
$note  = '�δ����̳�Ѿ�������';
$query = sprintf("select SUM(kin) from profit_loss_keihi_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='%s'", $str_ym, $end_ym, $note);
if ($rows=getResultWithField2($query, $field, $res) <= 0) {
    $han_jimuyo_kin = 0;
} else {
    $han_jimuyo_kin = $res[0][0];
}
// �����������
$res   = array();
$field = array();
$rows  = array();
$han_kojyo_kin = 0;
$note  = '�δ��񹩾��������';
$query = sprintf("select SUM(kin) from profit_loss_keihi_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='%s'", $str_ym, $end_ym, $note);
if ($rows=getResultWithField2($query, $field, $res) <= 0) {
    $han_kojyo_kin = 0;
} else {
    $han_kojyo_kin = $res[0][0];
}

// �δ����̳�Ѿ��������פη׻�
$han_jimuyo_total_kin = $han_jimuyo_kin + $han_kojyo_kin;

// �δ��� ������
// ����
$res   = array();
$field = array();
$rows  = array();
$han_zappi_kin = 0;
$note  = '�δ�����';
$query = sprintf("select SUM(kin) from profit_loss_keihi_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='%s'", $str_ym, $end_ym, $note);
if ($rows=getResultWithField2($query, $field, $res) <= 0) {
    $han_zappi_kin = 0;
} else {
    $han_zappi_kin = $res[0][0];
}
// �ݾڽ�����
$res   = array();
$field = array();
$rows  = array();
$han_hosyo_kin = 0;
$note  = '�δ����ݾڽ�����';
$query = sprintf("select SUM(kin) from profit_loss_keihi_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='%s'", $str_ym, $end_ym, $note);
if ($rows=getResultWithField2($query, $field, $res) <= 0) {
    $han_hosyo_kin = 0;
} else {
    $han_hosyo_kin = $res[0][0];
}
// ������
$res   = array();
$field = array();
$rows  = array();
$han_kaihi_kin = 0;
$note  = '�δ��������';
$query = sprintf("select SUM(kin) from profit_loss_keihi_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='%s'", $str_ym, $end_ym, $note);
if ($rows=getResultWithField2($query, $field, $res) <= 0) {
    $han_kaihi_kin = 0;
} else {
    $han_kaihi_kin = $res[0][0];
}

// �δ������פη׻�
$han_zappi_total_kin = $han_zappi_kin + $han_hosyo_kin + $han_kaihi_kin;

// �δ��� ������¹��
// �������
$res   = array();
$field = array();
$rows  = array();
$han_yachin_kin = 0;
$note  = '�δ����������';
$query = sprintf("select SUM(kin) from profit_loss_keihi_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='%s'", $str_ym, $end_ym, $note);
if ($rows=getResultWithField2($query, $field, $res) <= 0) {
    $han_yachin_kin = 0;
} else {
    $han_yachin_kin = $res[0][0];
}
// ������
$res   = array();
$field = array();
$rows  = array();
$han_kura_kin = 0;
$note  = '�δ���������';
$query = sprintf("select SUM(kin) from profit_loss_keihi_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='%s'", $str_ym, $end_ym, $note);
if ($rows=getResultWithField2($query, $field, $res) <= 0) {
    $han_kura_kin = 0;
} else {
    $han_kura_kin = $res[0][0];
}

// �δ���������¹�פη׻�
$han_yachin_total_kin = $han_yachin_kin + $han_kura_kin;

// �δ��� ����ʡ������
// ˡ��ʡ����
$res   = array();
$field = array();
$rows  = array();
$han_hofukuri_kin = 0;
$note  = '�δ���ˡ��ʡ����';
$query = sprintf("select SUM(kin) from profit_loss_keihi_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='%s'", $str_ym, $end_ym, $note);
if ($rows=getResultWithField2($query, $field, $res) <= 0) {
    $han_hofukuri_kin = 0;
} else {
    $han_hofukuri_kin = $res[0][0];
}
// ����ʡ����
$res   = array();
$field = array();
$rows  = array();
$han_kofukuri_kin = 0;
$note  = '�δ������ʡ����';
$query = sprintf("select SUM(kin) from profit_loss_keihi_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='%s'", $str_ym, $end_ym, $note);
if ($rows=getResultWithField2($query, $field, $res) <= 0) {
    $han_kofukuri_kin = 0;
} else {
    $han_kofukuri_kin = $res[0][0];
}

// �δ������ʡ�����פη׻�
$han_kofukuri_total_kin = $han_hofukuri_kin + $han_kofukuri_kin;

// �δ��� �࿦�������ѹ��
// �࿦��Ϳ��
$res   = array();
$field = array();
$rows  = array();
$han_taikyuyo_kin = 0;
$note  = '�δ����࿦��Ϳ��';
$query = sprintf("select SUM(kin) from profit_loss_keihi_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='%s'", $str_ym, $end_ym, $note);
if ($rows=getResultWithField2($query, $field, $res) <= 0) {
    $han_taikyuyo_kin = 0;
} else {
    $han_taikyuyo_kin = $res[0][0];
}
// �࿦��������
$res   = array();
$field = array();
$rows  = array();
$han_taikyufu_kin = 0;
$note  = '�δ����࿦��������';
$query = sprintf("select SUM(kin) from profit_loss_keihi_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='%s'", $str_ym, $end_ym, $note);
if ($rows=getResultWithField2($query, $field, $res) <= 0) {
    $han_taikyufu_kin = 0;
} else {
    $han_taikyufu_kin = $res[0][0];
}

// �δ����࿦�������ѹ�פη׻�
$han_taikyufu_total_kin = $han_taikyuyo_kin + $han_taikyufu_kin;

// ��¤���� ι���������
// ι�������
$res   = array();
$field = array();
$rows  = array();
$sei_ryohi_kin = 0;
$note  = '��¤����ι�������';
$query = sprintf("select SUM(kin) from profit_loss_keihi_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='%s'", $str_ym, $end_ym, $note);
if ($rows=getResultWithField2($query, $field, $res) <= 0) {
    $sei_ryohi_kin = 0;
} else {
    $sei_ryohi_kin = $res[0][0];
}
// ������ĥ
$res   = array();
$field = array();
$rows  = array();
$sei_kaigai_kin = 0;
$note  = '��¤���񳤳���ĥ';
$query = sprintf("select SUM(kin) from profit_loss_keihi_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='%s'", $str_ym, $end_ym, $note);
if ($rows=getResultWithField2($query, $field, $res) <= 0) {
    $sei_kaigai_kin = 0;
} else {
    $sei_kaigai_kin = $res[0][0];
}

// ��¤����ι��������פη׻�
$sei_ryohi_total_kin = $sei_ryohi_kin + $sei_kaigai_kin;

// ��¤���� ��̳��������
// ��̳������
$res   = array();
$field = array();
$rows  = array();
$sei_gyomu_kin = 0;
$note  = '��¤�����̳������';
$query = sprintf("select SUM(kin) from profit_loss_keihi_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='%s'", $str_ym, $end_ym, $note);
if ($rows=getResultWithField2($query, $field, $res) <= 0) {
    $sei_gyomu_kin = 0;
} else {
    $sei_gyomu_kin = $res[0][0];
}
// ��ʧ�����
$res   = array();
$field = array();
$rows  = array();
$sei_tesu_kin = 0;
$note  = '��¤�����ʧ�����';
$query = sprintf("select SUM(kin) from profit_loss_keihi_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='%s'", $str_ym, $end_ym, $note);
if ($rows=getResultWithField2($query, $field, $res) <= 0) {
    $sei_tesu_kin = 0;
} else {
    $sei_tesu_kin = $res[0][0];
}

// ��¤�����̳�������פη׻�
$sei_gyomu_total_kin = $sei_gyomu_kin + $sei_tesu_kin;

// ��¤���� ������
// ����
$res   = array();
$field = array();
$rows  = array();
$sei_zappi_kin = 0;
$note  = '��¤������';
$query = sprintf("select SUM(kin) from profit_loss_keihi_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='%s'", $str_ym, $end_ym, $note);
if ($rows=getResultWithField2($query, $field, $res) <= 0) {
    $sei_zappi_kin = 0;
} else {
    $sei_zappi_kin = $res[0][0];
}
// ����������
$res   = array();
$field = array();
$rows  = array();
$sei_kokoku_kin = 0;
$note  = '��¤���񹭹�������';
$query = sprintf("select SUM(kin) from profit_loss_keihi_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='%s'", $str_ym, $end_ym, $note);
if ($rows=getResultWithField2($query, $field, $res) <= 0) {
    $sei_kokoku_kin = 0;
} else {
    $sei_kokoku_kin = $res[0][0];
}
// �����
$res   = array();
$field = array();
$rows  = array();
$sei_kyujin_kin = 0;
$note  = '��¤��������';
$query = sprintf("select SUM(kin) from profit_loss_keihi_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='%s'", $str_ym, $end_ym, $note);
if ($rows=getResultWithField2($query, $field, $res) <= 0) {
    $sei_kyujin_kin = 0;
} else {
    $sei_kyujin_kin = $res[0][0];
}
// �ݾڽ�����
$res   = array();
$field = array();
$rows  = array();
$sei_hosyo_kin = 0;
$note  = '��¤�����ݾڽ�����';
$query = sprintf("select SUM(kin) from profit_loss_keihi_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='%s'", $str_ym, $end_ym, $note);
if ($rows=getResultWithField2($query, $field, $res) <= 0) {
    $sei_hosyo_kin = 0;
} else {
    $sei_hosyo_kin = $res[0][0];
}
// ������
$res   = array();
$field = array();
$rows  = array();
$sei_kaihi_kin = 0;
$note  = '��¤���������';
$query = sprintf("select SUM(kin) from profit_loss_keihi_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='%s'", $str_ym, $end_ym, $note);
if ($rows=getResultWithField2($query, $field, $res) <= 0) {
    $sei_kaihi_kin = 0;
} else {
    $sei_kaihi_kin = $res[0][0];
}

// ��¤�������פη׻�
$sei_zappi_total_kin = $sei_zappi_kin + $sei_kokoku_kin + $sei_kyujin_kin + $sei_hosyo_kin + $sei_kaihi_kin;

// ��¤���� ������¹��
// �������
$res   = array();
$field = array();
$rows  = array();
$sei_yachin_kin = 0;
$note  = '��¤�����������';
$query = sprintf("select SUM(kin) from profit_loss_keihi_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='%s'", $str_ym, $end_ym, $note);
if ($rows=getResultWithField2($query, $field, $res) <= 0) {
    $sei_yachin_kin = 0;
} else {
    $sei_yachin_kin = $res[0][0];
}
// ������
$res   = array();
$field = array();
$rows  = array();
$sei_kura_kin = 0;
$note  = '��¤����������';
$query = sprintf("select SUM(kin) from profit_loss_keihi_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='%s'", $str_ym, $end_ym, $note);
if ($rows=getResultWithField2($query, $field, $res) <= 0) {
    $sei_kura_kin = 0;
} else {
    $sei_kura_kin = $res[0][0];
}

// ��¤����������¹�פη׻�
$sei_yachin_total_kin = $sei_yachin_kin + $sei_kura_kin;

// ϫ̳�� ����ʡ������
// ˡ��ʡ����
$res   = array();
$field = array();
$rows  = array();
$sei_hofukuri_kin = 0;
$note  = '��¤����ˡ��ʡ����';
$query = sprintf("select SUM(kin) from profit_loss_keihi_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='%s'", $str_ym, $end_ym, $note);
if ($rows=getResultWithField2($query, $field, $res) <= 0) {
    $sei_hofukuri_kin = 0;
} else {
    $sei_hofukuri_kin = $res[0][0];
}
// ����ʡ����
$res   = array();
$field = array();
$rows  = array();
$sei_kofukuri_kin = 0;
$note  = '��¤�������ʡ����';
$query = sprintf("select SUM(kin) from profit_loss_keihi_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='%s'", $str_ym, $end_ym, $note);
if ($rows=getResultWithField2($query, $field, $res) <= 0) {
    $sei_kofukuri_kin = 0;
} else {
    $sei_kofukuri_kin = $res[0][0];
}

// ϫ̳�����ʡ�����פη׻�
$sei_kofukuri_total_kin = $sei_hofukuri_kin + $sei_kofukuri_kin;

// ϫ̳�� �࿦�������ѹ��
// �࿦��Ϳ��
$res   = array();
$field = array();
$rows  = array();
$sei_taikyuyo_kin = 0;
$note  = '��¤�����࿦��Ϳ��';
$query = sprintf("select SUM(kin) from profit_loss_keihi_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='%s'", $str_ym, $end_ym, $note);
if ($rows=getResultWithField2($query, $field, $res) <= 0) {
    $sei_taikyuyo_kin = 0;
} else {
    $sei_taikyuyo_kin = $res[0][0];
}
// �࿦��������
$res   = array();
$field = array();
$rows  = array();
$sei_taikyufu_kin = 0;
$note  = '��¤�����࿦��������';
$query = sprintf("select SUM(kin) from profit_loss_keihi_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='%s'", $str_ym, $end_ym, $note);
if ($rows=getResultWithField2($query, $field, $res) <= 0) {
    $sei_taikyufu_kin = 0;
} else {
    $sei_taikyufu_kin = $res[0][0];
}

// ϫ̳���࿦�������ѹ�פη׻�
$sei_taikyufu_total_kin = $sei_taikyuyo_kin + $sei_taikyufu_kin;

if (isset($_POST['input_data'])) {                        // ����ǡ�������Ͽ
    ///////// ���ܤȥ���ǥå����δ�Ϣ�դ�
    $item = array();
    $item[0]   = "����ڤ��¶�";
    $item[1]   = "�߼�����";
    $item[2]   = "�߼ڻų���";
    $item[3]   = "�߼ڸ������ڤ���¢��";
    $item[4]   = "̤������";
    $item[5]   = "����¾��ήư��";
    $item[6]   = "��ʪ";
    $item[7]   = "����ʪ";
    $item[8]   = "�����ڤ�����";
    $item[9]   = "���ұ��¶�";
    $item[10]  = "������ڤ�����";
    $item[11]  = "�꡼����";
    $item[12]  = "���������߷׳�";
    $item[13]  = "���ò�����";
    $item[14]  = "����¾�������";
    $item[15]  = "̤ʧ��������";
    $item[16]  = "��ݶ�";
    $item[17]  = "̤ʧ��";
    $item[18]  = "����¾��ήư���";
    $item[19]  = "����ų���";
    $item[20]  = "���󸶺����ڤ���¢��";
    $item[21]  = "�����ų���";
    $item[22]  = "�����������ڤ���¢��";
    $item[23]  = "������";
    $item[24]  = "����¾�αĶȳ�����";
    $item[25]  = "����񻺽���»";
    $item[26]  = "ecaˡ���ǡ���̱�ǵڤӻ�����";
    $item[27]  = "�δ���ι�������";
    $item[28]  = "�δ��񹭹�������";
    $item[29]  = "�δ����̳������";
    $item[30]  = "�δ�����Ǹ���";
    $item[31]  = "�δ����̳�Ѿ�������";
    $item[32]  = "�δ�����";
    $item[33]  = "�δ����������";
    $item[34]  = "�δ������ʡ����";
    $item[35]  = "�δ����࿦��������";
    $item[36]  = "��¤����ι�������";
    $item[37]  = "��¤�����̳������";
    $item[38]  = "��¤������";
    $item[39]  = "��¤�����������";
    $item[40]  = "��¤�������ʡ����";
    $item[41]  = "��¤�����࿦��������";
    $item[42]  = "��������»";
    $item[43]  = "ͭ���ٵ�̤������";
    $item[44]  = "Ω�ض�";
    $item[45]  = "����̤������";
    $item[46]  = "��ʧ��";
    $item[47]  = "���٤���¾ήư��";
    $item[48]  = "�񻺶�۷�ʪ";
    $item[49]  = "���������߷׳�(��ʪ)";
    $item[50]  = "�񻺶�۵����ڤ�����";
    $item[51]  = "���������߷׳�(�����ڤ�����)";
    $item[52]  = "�񻺶�ۼ��ұ��¶�";
    $item[53]  = "���������߷׳�(���ұ��¶�)";
    $item[54]  = "�񻺶�۹���������";
    $item[55]  = "���������߷׳�(����������)";
    $item[56]  = "�񻺶�ۥ꡼����";
    $item[57]  = "���������߷׳�(�꡼����)";
    $item[58]  = "ecaˡ��ʡ����";                       // �δ���
    $item[59]  = "ecaʡ��������";                       // �δ���
    $item[60]  = "eca������";                           // �δ���
    $item[61]  = "eca�������";                         // �δ���
    $item[62]  = "eca��̳������";                       // �δ���
    $item[63]  = "eca��ʧ�����";                       // �δ���
    $item[64]  = "eca�����";                           // �δ���
    $item[65]  = "eca������";                           // �δ���
    $item[66]  = "eca����";                             // �δ���
    $item[67]  = "eca̤������";                         // �δ���
    $item[68]  = "�񻺶�۹���ʪ";
    $item[69]  = "���������߷׳�(����ʪ)";
    $item[70]  = "eca����������";                       // �δ���
    $item[71]  = "��¢��";
    $item[72]  = "ɾ���ڲ�������";
    $item[73]  = "ɾ���ڲ�������";
    $item[74]  = "����ų�����";
    $item[75]  = "����ų�����";
    $item[76]  = "�����ų�����";
    $item[77]  = "���ô���Ĺ�";
    $item[78]  = "���ߴ���Ĺ�";
    $item[79]  = "���եȴ���Ĺ�";
    $item[80]  = "���ô�������";
    $item[81]  = "���ߴ�������";
    $item[82]  = "���եȴ�������";
    $item[83]  = "���ô��渺��";
    $item[84]  = "���ߴ��渺��";
    $item[85]  = "���եȴ��渺��";
    ///////// �ƥǡ������ݴ�
    $input_data = array();
    $input_data[0]   = $genyo_total_kin;
    $input_data[1]   = $seihin_total_kin;
    $input_data[2]   = $sikakari_total_kin;
    $input_data[3]   = $gencho_total_kin;
    $input_data[4]   = $ryu_mishu_kin;
    $input_data[5]   = $hokaryudo_total_kin;
    $input_data[6]   = $tate_all_shisan_kin;
    $input_data[7]   = $kouchiku_shisan_kin;
    $input_data[8]   = $kikai_shisan_kin;
    $input_data[9]   = $sharyo_shisan_kin;
    $input_data[10]  = $jyubihin_all_shisan_kin;
    $input_data[11]  = $lease_shisan_kin;
    $input_data[12]  = $gensyo_total_mi_kin;
    $input_data[13]  = $denwa_kin;
    $input_data[14]  = $toushi_total_kin;
    $input_data[15]  = $mihazei_total_kin;
    $input_data[16]  = $kaikake_total_kin;
    $input_data[17]  = $miharai_total_kin;
    $input_data[18]  = $sonota_ryudo_total_kin;
    $input_data[19]  = $z_sikakari_total_kin;
    $input_data[20]  = $z_gencho_total_kin;
    $input_data[21]  = $kimatsu_sikakari_total_kin;
    $input_data[22]  = $kimatsu_gencho_total_kin;
    $input_data[23]  = $eigyo_shueki_total_kin;
    $input_data[24]  = $sonota_eihiyo_kin;
    $input_data[25]  = $kotei_jyoson_kin;
    $input_data[26]  = $eca_hojin_zeito_total_kin;
    $input_data[27]  = $han_ryohi_total_kin;
    $input_data[28]  = $han_kokoku_total_kin;
    $input_data[29]  = $han_gyomu_total_kin;
    $input_data[30]  = $han_zeikoka_total_kin;
    $input_data[31]  = $han_jimuyo_total_kin;
    $input_data[32]  = $han_zappi_total_kin;
    $input_data[33]  = $han_yachin_total_kin;
    $input_data[34]  = $han_kofukuri_total_kin;
    $input_data[35]  = $han_taikyufu_total_kin;
    $input_data[36]  = $sei_ryohi_total_kin;
    $input_data[37]  = $sei_gyomu_total_kin;
    $input_data[38]  = $sei_zappi_total_kin;
    $input_data[39]  = $sei_yachin_total_kin;
    $input_data[40]  = $sei_kofukuri_total_kin;
    $input_data[41]  = $sei_taikyufu_total_kin;
    $input_data[42]  = $kotei_baison_kin;
    $input_data[43]  = $yumi_kin;
    $input_data[44]  = $tatekae_kin;
    $input_data[45]  = $mishu_kin;
    $input_data[46]  = $karibara_kin;
    $input_data[47]  = $hokaryudo_kin;
    $input_data[48]  = $tate_shutoku_kin;
    $input_data[49]  = $tate_rui_kin;
    $input_data[50]  = $kikai_kin;
    $input_data[51]  = -$kikai_gen_kin;
    $input_data[52]  = $sharyo_kin;
    $input_data[53]  = -$sharyo_gen_kin;
    $input_data[54]  = $kikougu_shutoku_kin;
    $input_data[55]  = $kikougu_rui_kin;
    $input_data[56]  = $lease_kin;
    $input_data[57]  = -$lease_gen_kin;
    $input_data[58]  = $han_hofukuri_kin;
    $input_data[59]  = $han_kofukuri_kin;
    $input_data[60]  = $han_kura_kin;
    $input_data[61]  = $han_yachin_kin;
    $input_data[62]  = $han_gyomu_kin;
    $input_data[63]  = $han_tesu_kin;
    $input_data[64]  = $han_kyujin_kin;
    $input_data[65]  = $han_kaihi_kin;
    $input_data[66]  = $han_zappi_kin;
    $input_data[67]  = $mishueki_kin;
    $input_data[68]  = $kouchiku_kin;
    $input_data[69]  = -$kouchiku_gen_kin;
    $input_data[70]  = $han_kokoku_kin;
    $input_data[71]  = $chozo_kin;
    $input_data[72]  = $hyoka_buhin_kin;
    $input_data[73]  = $hyoka_zai_kin;
    $input_data[74]  = $tana_kou_kin;
    $input_data[75]  = $tana_gai_kin;
    $input_data[76]  = $tana_ken_kin;
    $input_data[77]  = $den_kishu_kin;
    $input_data[78]  = $shi_kishu_kin;
    $input_data[79]  = $sft_kishu_kin;
    $input_data[80]  = $den_zou_kin;
    $input_data[81]  = $shi_zou_kin;
    $input_data[82]  = $sft_zou_kin;
    $input_data[83]  = $den_gen_kin;
    $input_data[84]  = $shi_gen_kin;
    $input_data[85]  = $sft_gen_kin;
    ///////// �ƥǡ�������Ͽ
    insert_date($item,$yyyymm,$input_data);
}


function insert_date($item,$yyyymm,$input_data) 
{
    $num_input = count($input_data);
    for ($i = 0; $i < $num_input; $i++) {
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
        <table bgcolor='#ffffff' align='center' cellspacing='0' cellpadding='10'>
            <tr><td> <!----------- ���ߡ�(�ǥ�������) ------------>
        <center>���߼��о�ɽ��</center>
        <table class='winbox_field' width='100%' align='center' bgcolor='black' cellspacing='1' cellpadding='5'>
            <THEAD>
                <!-- �ơ��֥� �إå�����ɽ�� -->
                <tr>
                    <th class='winbox' nowrap rowspan='2'>��</th>
                    <th class='winbox' nowrap colspan='4'>�ɽ</th>
                    <th class='winbox' nowrap colspan='3'>�軻��(B/S)</th>
                </tr>
                <tr>
                    <th class='winbox' nowrap colspan='2'>�������</th>
                    <th class='winbox' nowrap>���</th>
                    <th class='winbox' nowrap>����</th>
                    <th class='winbox' nowrap colspan='2'>�������</th>
                    <th class='winbox' nowrap>���</th>
                </tr>
            </THEAD>
            <TFOOT>
                <!-- ���ߤϥեå����ϲ���ʤ� -->
            </TFOOT>
            <TBODY>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' rowspan='5' valign='top'>
                        <div class='pt10b'>��</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' rowspan='5' valign='top'>
                        <div class='pt10b'>����</div><BR>
                        <div class='pt10b'>�¶�</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#fde9d9'>
                        <div class='pt10b'>����</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ccffcc' align='right'>
                        <div class='pt11b'><?= number_format($genkin_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>��</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' rowspan='5' valign='top'>
                        <div class='pt10b'>ήư��</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>����ڤ��¶�</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffc6' align='right'>
                        <div class='pt11b'><?= number_format($genyo_total_kin) ?></div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#fde9d9'>
                        <div class='pt10b'>�����¶�</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ccffcc' align='right'>
                        <div class='pt11b'><?= number_format($touza_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>��</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>��</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>��</div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#fde9d9'>
                        <div class='pt10b'>�����¶�</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ccffcc' align='right'>
                        <div class='pt11b'><?= number_format($futsu_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>��</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>��</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>��</div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#fde9d9'>
                        <div class='pt10b'>����¶�</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ccffcc' align='right'>
                        <div class='pt11b'><?= number_format($teiki_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>������</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>��</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>��</div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>���</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right'>
                        <div class='pt11b'><?= number_format($genyo_total_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>��</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>���</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right'>
                        <div class='pt11b'><?= number_format($genyo_total_kin) ?></div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' rowspan='8' valign='top'>
                        <div class='pt10b'>��</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' rowspan='8' valign='top'>
                        <div class='pt10b'>�߸�</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>����</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right'>
                        <div class='pt11b'><?= number_format($seihin_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>��</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' rowspan='8' valign='top'>
                        <div class='pt10b'>ήư��</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>����</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right'>
                        <div class='pt11b'><?= number_format($seihin_total_kin) ?></div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>���ʻų���</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ccffcc' align='right'>
                        <div class='pt11b'><?= number_format($seihinsi_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>��</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>�ų���</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right'>
                        <div class='pt11b'><?= number_format($sikakari_total_kin) ?></div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>����</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ccffcc' align='right'>
                        <div class='pt11b'><?= number_format($buhin_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>��</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>�������ڤ���¢��</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right'>
                        <div class='pt11b'><?= number_format($gencho_total_kin) ?></div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>���ʻų���</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ccffcc' align='right'>
                        <div class='pt11b'><?= number_format($buhinsi_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>��</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>��</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>��</div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>������</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ccffcc' align='right'>
                        <div class='pt11b'><?= number_format($genzai_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>��</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>��</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>��</div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>����¾��ê����</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ccffcc' align='right'>
                        <div class='pt11b'><?= number_format($sonotatana_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>��</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>��</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>��</div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>��¢��</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ccffcc' align='right'>
                        <div class='pt11b'><?= number_format($chozo_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>��</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>��</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>��</div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>���</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right'>
                        <div class='pt11b'><?= number_format($zaiko_total_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>��</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>���</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right'>
                        <div class='pt11b'><?= number_format($ryudozaiko_total_kin) ?></div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' rowspan='8' valign='top'>
                        <div class='pt10b'>��</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' rowspan='8' valign='top'>
                        <div class='pt10b'>ήư��</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#fde9d9'>
                        <div class='pt10b'>ͭ���ٵ�̤������</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ccffcc' align='right'>
                        <div class='pt11b'><?= number_format($yumi_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>��</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' rowspan='8' valign='top'>
                        <div class='pt10b'>ήư��</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>̤������</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffc6' align='right'>
                        <div class='pt11b'><?= number_format($ryu_mishu_kin) ?></div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#fde9d9'>
                        <div class='pt10b'>̤������</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ccffcc' align='right'>
                        <div class='pt11b'><?= number_format($mishu_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>��</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>��</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right'>
                        <div class='pt11b'>��</div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#fde9d9'>
                        <div class='pt10b'>̤������</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ccffcc' align='right'>
                        <div class='pt11b'><?= number_format($mishueki_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>��</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>��</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right'>
                        <div class='pt11b'>��</div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>���</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right'>
                        <div class='pt11b'><?= number_format($ryu_mishu_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>��</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>���</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right'>
                        <div class='pt11b'><?= number_format($ryu_mishu_total_kin) ?></div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#fde9d9'>
                        <div class='pt10b'>Ω�ض�</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ccffcc' align='right'>
                        <div class='pt11b'><?= number_format($tatekae_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>��</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>����¾ήư��</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffc6' align='right'>
                        <div class='pt11b'><?= number_format($hokaryudo_total_kin) ?></div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#fde9d9'>
                        <div class='pt10b'>��ʧ��</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ccffcc' align='right'>
                        <div class='pt11b'><?= number_format($karibara_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>��</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>��</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>��</div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>����¾��ήư��</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ccffcc' align='right'>
                        <div class='pt11b'><?= number_format($hokaryudo_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>��</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>��</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>��</div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>���</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right'>
                        <div class='pt11b'><?= number_format($hokaryudo_total_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>��</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>���</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right'>
                        <div class='pt11b'><?= number_format($hokaryudo_all_kin) ?></div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' rowspan='10' valign='top'>
                        <div class='pt10b'>��</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' rowspan='10' valign='top'>
                        <div class='pt10b'>ͭ��</div><BR>
                        <div class='pt10b'>�����</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>��ʪ</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ccffcc' align='right'>
                        <div class='pt11b'><?= number_format($tatemono_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#cc99ff' align='right'>
                        <div class='pt11b'><?= number_format($tate_gen_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' rowspan='10' valign='top'>
                        <div class='pt10b'>ͭ��</div><BR>
                        <div class='pt10b'>�����</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>��ʪ</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffc6' align='right'>
                        <div class='pt11b'><?= number_format($tate_all_shisan_kin) ?></div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>����</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ccffcc' align='right'>
                        <div class='pt11b'><?= number_format($fuzoku_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#cc99ff' align='right'>
                        <div class='pt11b'><?= number_format($fuzoku_gen_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>�������û���</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>��</div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>����ʪ</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ccffcc' align='right'>
                        <div class='pt11b'><?= number_format($kouchiku_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#cc99ff' align='right'>
                        <div class='pt11b'><?= number_format($kouchiku_gen_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>����ʪ</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffc6' align='right'>
                        <div class='pt11b'><?= number_format($kouchiku_shisan_kin) ?></div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>��������</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ccffcc' align='right'>
                        <div class='pt11b'><?= number_format($kikai_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#cc99ff' align='right'>
                        <div class='pt11b'><?= number_format($kikai_gen_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>�����ڤ�����</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffc6' align='right'>
                        <div class='pt11b'><?= number_format($kikai_shisan_kin) ?></div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>���ұ��¶�</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ccffcc' align='right'>
                        <div class='pt11b'><?= number_format($sharyo_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#cc99ff' align='right'>
                        <div class='pt11b'><?= number_format($sharyo_gen_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>���ұ��¶�</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffc6' align='right'>
                        <div class='pt11b'><?= number_format($sharyo_shisan_kin) ?></div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>��񹩶�</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ccffcc' align='right'>
                        <div class='pt11b'><?= number_format($kigu_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#cc99ff' align='right'>
                        <div class='pt11b'><?= number_format($kigu_gen_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>������ڤ�����</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffc6' align='right'>
                        <div class='pt11b'><?= number_format($jyubihin_all_shisan_kin) ?></div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>��������</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ccffcc' align='right'>
                        <div class='pt11b'><?= number_format($jyuki_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#cc99ff' align='right'>
                        <div class='pt11b'><?= number_format($jyuki_gen_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>�꡼����</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffc6' align='right'>
                        <div class='pt11b'><?= number_format($lease_shisan_kin) ?></div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>�꡼����</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ccffcc' align='right'>
                        <div class='pt11b'><?= number_format($lease_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#cc99ff' align='right'>
                        <div class='pt11b'><?= number_format($lease_gen_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>���</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right'>
                        <div class='pt11b'><?= number_format($boka_totai_kin) ?></div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>���������߷׳�</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ccffcc' align='right'>
                        <div class='pt11b'><?= number_format($gensyo_total_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>��</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>��</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>��</div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>���</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right'>
                        <div class='pt11b'><?= number_format($boka_totai_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>��</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>���������߷׳�</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right'>
                        <div class='pt11b'><?= number_format($gensyo_total_mi_kin) ?></div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' rowspan='3' valign='top'>
                        <div class='pt10b'>��</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' rowspan='3' valign='top'>
                        <div class='pt10b'>̵��</div><BR>
                        <div class='pt10b'>�����</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#fde9d9'>
                        <div class='pt10b'>���ò�����</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ccffcc' align='right'>
                        <div class='pt11b'><?= number_format($denwa_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>��</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' rowspan='3' valign='top'>
                        <div class='pt10b'>̵��</div><BR>
                        <div class='pt10b'>�����</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>���ò�����</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right'>
                        <div class='pt11b'><?= number_format($denwa_kin) ?></div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>��</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right'>
                        <div class='pt11b'>��</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>��</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>��</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>��</div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>���</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right'>
                        <div class='pt11b'><?= number_format($denwa_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>��</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>���</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right'>
                        <div class='pt11b'><?= number_format($denwa_kin) ?></div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' rowspan='3' valign='top'>
                        <div class='pt10b'>��</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' rowspan='3' valign='top'>
                        <div class='pt10b'>�����</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#fde9d9'>
                        <div class='pt10b'>�л��</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ccffcc' align='right'>
                        <div class='pt11b'><?= number_format($shussi_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>��</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' rowspan='3' valign='top'>
                        <div class='pt10b'>�����</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>����¾�������</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right'>
                        <div class='pt11b'><?= number_format($toushi_total_kin) ?></div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>�����߶��ݾڶ�</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ccffcc' align='right'>
                        <div class='pt11b'><?= number_format($hosyo_kin) ?></div>
                    </td>   
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>��</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>��</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>��</div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>���</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right'>
                        <div class='pt11b'><?= number_format($toushi_total_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>��</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>���</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right'>
                        <div class='pt11b'><?= number_format($toushi_total_kin) ?></div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' rowspan='15' valign='top'>
                        <div class='pt10b'>��</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' rowspan='5' valign='top'>
                        <div class='pt10b'>ήư���</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>��ʧ��������</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ccffcc' align='right'>
                        <div class='pt11b'><?= number_format($kari_zei_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>(���� ͢���ޤ�)</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' rowspan='5' valign='top'>
                        <div class='pt10b'>ήư���</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>̤ʧ��������</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffc6' align='right'>
                        <div class='pt11b'><?= number_format($mihazei_total_kin) ?></div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>��ʧ������</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ccffcc' align='right'>
                        <div class='pt11b'><?= number_format($mae_zei_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>��</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>��</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right'>
                        <div class='pt11b'>��</div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>������������</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ccffcc' align='right'>
                        <div class='pt11b'><?= number_format($kariuke_zei_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>��</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>��</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right'>
                        <div class='pt11b'>��</div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>̤ʧ��������</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ccffcc' align='right'>
                        <div class='pt11b'><?= number_format($miharai_zei_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>(��Ⱦ���׾�ʬ)</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>��</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right'>
                        <div class='pt11b'>��</div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>���</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right'>
                        <div class='pt11b'><?= number_format($mihazei_total_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>��</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>���</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right'>
                        <div class='pt11b'><?= number_format($mihazei_total_kin) ?></div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' rowspan='10' valign='top'>
                        <div class='pt10b'>ήư���</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#fde9d9'>
                        <div class='pt10b'>��ݶ�</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ccffcc' align='right'>
                        <div class='pt11b'><?= number_format($kaikake_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>��</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' rowspan='3' valign='top'>
                        <div class='pt10b'>ήư���</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>��ݶ�</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffc6' align='right'>
                        <div class='pt11b'><?= number_format($kaikake_total_kin) ?></div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>��ݶ��������</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ccffcc' align='right'>
                        <div class='pt11b'><?= number_format($kaikake_kiji_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>��</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>��</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>��</div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>���</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right'>
                        <div class='pt11b'><?= number_format($kaikake_total_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>��</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>���</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right'>
                        <div class='pt11b'><?= number_format($kaikake_total_kin) ?></div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#fde9d9'>
                        <div class='pt10b'>̤ʧ��</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ccffcc' align='right'>
                        <div class='pt11b'><?= number_format($miharai_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>��</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' rowspan='3' valign='top'>
                        <div class='pt10b'>ήư���</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>̤ʧ��</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffc6' align='right'>
                        <div class='pt11b'><?= number_format($miharai_total_kin) ?></div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>̤ʧ���������</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ccffcc' align='right'>
                        <div class='pt11b'><?= number_format($miharai_kiji_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>��</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>��</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>��</div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>���</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right'>
                        <div class='pt11b'><?= number_format($miharai_total_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>��</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>���</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right'>
                        <div class='pt11b'><?= number_format($miharai_total_kin) ?></div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>������</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ccffcc' align='right'>
                        <div class='pt11b'><?= number_format($maeuke_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>��</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' rowspan='3' valign='top'>
                        <div class='pt10b'>ήư���</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>����¾��ήư���</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffc6' align='right'>
                        <div class='pt11b'><?= number_format($sonota_ryudo_total_kin) ?></div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>����¾��ήư���</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ccffcc' align='right'>
                        <div class='pt11b'><?= number_format($sonota_ryudo_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>��</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>��</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>��</div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>���</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right'>
                        <div class='pt11b'><?= number_format($sonota_ryudo_total_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>��</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>���</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right'>
                        <div class='pt11b'><?= number_format($sonota_ryudo_total_kin) ?></div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>���</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right'>
                        <div class='pt11b'><?= number_format($ryudo_fusai_total_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>��</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>��</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>���</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right'>
                        <div class='pt11b'><?= number_format($ryudo_fusai_total_kin) ?></div>
                    </td>
                </tr>
            </TBODY>
        </table>
            </td></tr>
        </table> <!----------------- ���ߡ�End ------------------>
        
        <table bgcolor='#ffffff' align='center' cellspacing='0' cellpadding='10'>
            <tr><td> <!----------- ���ߡ�(�ǥ�������) ------------>
        <center>��»�׷׻����</center>
        <table class='winbox_field' width='100%' align='center' bgcolor='black' cellspacing='1' cellpadding='5'>
            <THEAD>
                <!-- �ơ��֥� �إå�����ɽ�� -->
                <tr>
                    <th class='winbox' nowrap rowspan='2'>��</th>
                    <th class='winbox' nowrap colspan='4'>�ɽ</th>
                    <th class='winbox' nowrap colspan='3'>�軻��(P/L,��¤�������񡢷������ٽ��</th>
                </tr>
                <tr>
                    <th class='winbox' nowrap colspan='2'>�������</th>
                    <th class='winbox' nowrap>���</th>
                    <th class='winbox' nowrap>����</th>
                    <th class='winbox' nowrap colspan='2'>�������</th>
                    <th class='winbox' nowrap>���</th>
                </tr>
                <tr>
                    <th class='winbox' nowrap>No.</th>
                    <th class='winbox' nowrap colspan='7'>��¤��������</th>
                </tr>
            </THEAD>
            <TFOOT>
                <!-- ���ߤϥեå����ϲ���ʤ� -->
            </TFOOT>
            <TBODY>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' rowspan='5' valign='top'>
                        <div class='pt10b'>��</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' rowspan='5' valign='top'>
                        <div class='pt10b'>������</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>����ê����</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right'>
                        <div class='pt11b'><?= number_format($z_zaiko_total_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>��</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' rowspan='5' valign='top'>
                        <div class='pt10b'>»�׷׻���</div><BR>
                        <div class='pt10b'>��¤����</div><BR>
                        <div class='pt10b'>����</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>��������ê����</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ccffcc' align='right'>
                        <div class='pt10b'>��</div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>��</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>��</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>��</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>�ų���</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ccffcc' align='right'>
                        <div class='pt11b'><?= number_format($z_sikakari_total_kin) ?></div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>��</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>��</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>��</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>�������ڤ���¢��</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ccffcc' align='right'>
                        <div class='pt11b'><?= number_format($z_gencho_total_kin) ?></div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>��</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>��</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>��</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>��</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ccffcc' align='right'>
                        <div class='pt11b'>��</div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>���</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right'>
                        <div class='pt11b'><?= number_format($z_zaiko_total_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>��</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>���</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right'>
                        <div class='pt11b'><?= number_format($z_zaiko_total_kin) ?></div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' rowspan='5' valign='top'>
                        <div class='pt10b'>��</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' rowspan='5' valign='top'>
                        <div class='pt10b'>������</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>����ê����</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right'>
                        <div class='pt11b'><?= number_format($kimatsu_total_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>��</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' rowspan='5' valign='top'>
                        <div class='pt10b'>»�׷׻���</div><BR>
                        <div class='pt10b'>��¤����</div><BR>
                        <div class='pt10b'>����</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>��������ê����</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ccffcc' align='right'>
                        <div class='pt10b'>��</div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>��</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>��</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>��</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>�ų���</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ccffcc' align='right'>
                        <div class='pt11b'><?= number_format($kimatsu_sikakari_total_kin) ?></div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>��</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>��</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>��</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>�������ڤ���¢��</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ccffcc' align='right'>
                        <div class='pt11b'><?= number_format($kimatsu_gencho_total_kin) ?></div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>��</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>��</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>��</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>��</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ccffcc' align='right'>
                        <div class='pt11b'>��</div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>���</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right'>
                        <div class='pt11b'><?= number_format($kimatsu_total_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>��</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>���</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right'>
                        <div class='pt11b'><?= number_format($kimatsu_total_kin) ?></div>
                    </td>
                </tr>
                <tr>
                    <th class='winbox' nowrap>No.</th>
                    <th class='winbox' nowrap colspan='7'>P / L   �Ķȳ�»�ס�����»�ס�¾</th>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' rowspan='3' valign='top'>
                        <div class='pt10b'>��</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' rowspan='3' valign='top'>
                        <div class='pt10b'>�Ķȳ�����</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>������</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ccffcc' align='right'>
                        <div class='pt11b'><?= number_format($zatsushu_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>��</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' rowspan='3' valign='top'>
                        <div class='pt10b'>�Ķȳ�����</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>������</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right'>
                        <div class='pt11b'><?= number_format($eigyo_shueki_total_kin) ?></div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>��̳��������</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ccffcc' align='right'>
                        <div class='pt11b'><?= number_format($gyomushu_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>��</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>��</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>��</div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>���</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right'>
                        <div class='pt11b'><?= number_format($eigyo_shueki_total_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>��</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>���</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right'>
                        <div class='pt11b'><?= number_format($eigyo_shueki_total_kin) ?></div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' rowspan='5' valign='top'>
                        <div class='pt10b'>��</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' rowspan='5' valign='top'>
                        <div class='pt10b'>�Ķȳ�����</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>����¾�αĶȳ�����</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ccffcc' align='right'>
                        <div class='pt11b'><?= number_format($sonota_eihiyo_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>��</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' rowspan='2' valign='top'>
                        <div class='pt10b'>�Ķȳ�����</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>����¾�αĶȳ�����</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right'>
                        <div class='pt11b'><?= number_format($sonota_eihiyo_kin) ?></div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>���</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right'>
                        <div class='pt11b'><?= number_format($sonota_eihiyo_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>��</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>���</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right'>
                        <div class='pt11b'><?= number_format($sonota_eihiyo_kin) ?></div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>��������»</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ccffcc' align='right'>
                        <div class='pt11b'><?= number_format($kotei_baison_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>��</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' rowspan='3' valign='top'>
                        <div class='pt10b'>�Ķȳ�����</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>��������»</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right'>
                        <div class='pt11b'><?= number_format($kotei_baison_kin) ?></div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>����񻺽���»</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ccffcc' align='right'>
                        <div class='pt11b'><?= number_format($kotei_jyoson_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>��</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>����񻺽���»</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right'>
                        <div class='pt11b'><?= number_format($kotei_jyoson_kin) ?></div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>���</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right'>
                        <div class='pt11b'><?= number_format($kotei_son_total) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>��</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>���</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right'>
                        <div class='pt11b'><?= number_format($kotei_son_total) ?></div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' rowspan='4' valign='top'>
                        <div class='pt10b'>��</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' rowspan='4' valign='top'>
                        <div class='pt10b'>ˡ������</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>ˡ���ǵڤӽ�̱��</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ccffcc' align='right'>
                        <div class='pt11b'><?= number_format($hojin_jyumin_zei_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>��</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' rowspan='4' valign='top'>
                        <div class='pt10b'>ˡ������</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' rowspan='2' valign='top'>
                        <div class='pt10b'>ˡ���ǡ���̱��</div><BR>
                        <div class='pt10b'>�ڤӻ�����</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right'  rowspan='2'>
                        <div class='pt11b'><?= number_format($hojin_zeito_total_kin) ?></div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>������</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ccffcc' align='right'>
                        <div class='pt11b'><?= number_format($jigyo_zei_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>��</div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>ˡ������Ĵ����</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ccffcc' align='right'>
                        <div class='pt11b'><?= number_format($hojin_chosei_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>��</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>��</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>��</div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>���</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right'>
                        <div class='pt11b'><?= number_format($hojin_zeito_total_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>��</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>���</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right'>
                        <div class='pt11b'><?= number_format($hojin_zeito_total_kin) ?></div>
                    </td>
                </tr>
                <tr>
                    <th class='winbox' nowrap>No.</th>
                    <th class='winbox' nowrap colspan='7'>��    ��    ��   ��    ��</th>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' rowspan='3' valign='top'>
                        <div class='pt10b'>��</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' rowspan='3' valign='top'>
                        <div class='pt10b'>�δ���</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>ι�������</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ccffcc' align='right'>
                        <div class='pt11b'><?= number_format($han_ryohi_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>��</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' rowspan='3' valign='top'>
                        <div class='pt10b'>�δ���</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>ι�������</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffc6' align='right'>
                        <div class='pt11b'><?= number_format($han_ryohi_total_kin) ?></div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>������ĥ��</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ccffcc' align='right'>
                        <div class='pt11b'><?= number_format($han_kaigai_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>��</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>��</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right'>
                        <div class='pt11b'>��</div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>���</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right'>
                        <div class='pt11b'><?= number_format($han_ryohi_total_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>��</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>���</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right'>
                        <div class='pt11b'><?= number_format($han_ryohi_total_kin) ?></div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' rowspan='3' valign='top'>
                        <div class='pt10b'>��</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' rowspan='3' valign='top'>
                        <div class='pt10b'>�δ���</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>����������</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ccffcc' align='right'>
                        <div class='pt11b'><?= number_format($han_kokoku_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>��</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' rowspan='3' valign='top'>
                        <div class='pt10b'>�δ���</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>����������</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffc6' align='right'>
                        <div class='pt11b'><?= number_format($han_kokoku_total_kin) ?></div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>�����</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ccffcc' align='right'>
                        <div class='pt11b'><?= number_format($han_kyujin_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>��</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>��</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right'>
                        <div class='pt11b'>��</div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>���</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right'>
                        <div class='pt11b'><?= number_format($han_kokoku_total_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>��</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>���</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right'>
                        <div class='pt11b'><?= number_format($han_kokoku_total_kin) ?></div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' rowspan='3' valign='top'>
                        <div class='pt10b'>��</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' rowspan='3' valign='top'>
                        <div class='pt10b'>�δ���</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>��̳������</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ccffcc' align='right'>
                        <div class='pt11b'><?= number_format($han_gyomu_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>��</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' rowspan='3' valign='top'>
                        <div class='pt10b'>�δ���</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>��̳������</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffc6' align='right'>
                        <div class='pt11b'><?= number_format($han_gyomu_total_kin) ?></div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>��ʧ�����</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ccffcc' align='right'>
                        <div class='pt11b'><?= number_format($han_tesu_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>��</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>��</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right'>
                        <div class='pt11b'>��</div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>���</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right'>
                        <div class='pt11b'><?= number_format($han_gyomu_total_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>��</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>���</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right'>
                        <div class='pt11b'><?= number_format($han_gyomu_total_kin) ?></div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' rowspan='3' valign='top'>
                        <div class='pt10b'>��</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' rowspan='3' valign='top'>
                        <div class='pt10b'>�δ���</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>������</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ccffcc' align='right'>
                        <div class='pt11b'><?= number_format($han_jigyo_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>��</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' rowspan='3' valign='top'>
                        <div class='pt10b'>�δ���</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>���Ǹ���</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffc6' align='right'>
                        <div class='pt11b'><?= number_format($han_zeikoka_total_kin) ?></div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>���Ǹ���</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ccffcc' align='right'>
                        <div class='pt11b'><?= number_format($han_zeikoka_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>��</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>��</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right'>
                        <div class='pt11b'>��</div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>���</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right'>
                        <div class='pt11b'><?= number_format($han_zeikoka_total_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>��</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>���</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right'>
                        <div class='pt11b'><?= number_format($han_zeikoka_total_kin) ?></div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' rowspan='3' valign='top'>
                        <div class='pt10b'>��</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' rowspan='3' valign='top'>
                        <div class='pt10b'>�δ���</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>��̳�Ѿ�������</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ccffcc' align='right'>
                        <div class='pt11b'><?= number_format($han_jimuyo_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>��</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' rowspan='3' valign='top'>
                        <div class='pt10b'>�δ���</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>��̳�Ѿ�������</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffc6' align='right'>
                        <div class='pt11b'><?= number_format($han_jimuyo_total_kin) ?></div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>�����������</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ccffcc' align='right'>
                        <div class='pt11b'><?= number_format($han_kojyo_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>��</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>��</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right'>
                        <div class='pt11b'>��</div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>���</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right'>
                        <div class='pt11b'><?= number_format($han_jimuyo_total_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>��</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>���</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right'>
                        <div class='pt11b'><?= number_format($han_jimuyo_total_kin) ?></div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' rowspan='4' valign='top'>
                        <div class='pt10b'>��</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' rowspan='4' valign='top'>
                        <div class='pt10b'>�δ���</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>����</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ccffcc' align='right'>
                        <div class='pt11b'><?= number_format($han_zappi_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>��</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' rowspan='4' valign='top'>
                        <div class='pt10b'>�δ���</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>����</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffc6' align='right'>
                        <div class='pt11b'><?= number_format($han_zappi_total_kin) ?></div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>�ݾڽ�����</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ccffcc' align='right'>
                        <div class='pt11b'><?= number_format($han_hosyo_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>��</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>��</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right'>
                        <div class='pt11b'>��</div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>������</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ccffcc' align='right'>
                        <div class='pt11b'><?= number_format($han_kaihi_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>��</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>��</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right'>
                        <div class='pt11b'>��</div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>���</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right'>
                        <div class='pt11b'><?= number_format($han_zappi_total_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>��</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>���</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right'>
                        <div class='pt11b'><?= number_format($han_zappi_total_kin) ?></div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' rowspan='3' valign='top'>
                        <div class='pt10b'>��</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' rowspan='3' valign='top'>
                        <div class='pt10b'>�δ���</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>�������</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ccffcc' align='right'>
                        <div class='pt11b'><?= number_format($han_yachin_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>��</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' rowspan='3' valign='top'>
                        <div class='pt10b'>�δ���</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>�������</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffc6' align='right'>
                        <div class='pt11b'><?= number_format($han_yachin_total_kin) ?></div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>������</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ccffcc' align='right'>
                        <div class='pt11b'><?= number_format($han_kura_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>��</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>��</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right'>
                        <div class='pt11b'>��</div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>���</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right'>
                        <div class='pt11b'><?= number_format($han_yachin_total_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>��</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>���</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right'>
                        <div class='pt11b'><?= number_format($han_yachin_total_kin) ?></div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' rowspan='3' valign='top'>
                        <div class='pt10b'>��</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' rowspan='3' valign='top'>
                        <div class='pt10b'>�δ���</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>ˡ��ʡ����</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ccffcc' align='right'>
                        <div class='pt11b'><?= number_format($han_hofukuri_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>��</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' rowspan='3' valign='top'>
                        <div class='pt10b'>�δ���</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>����ʡ����</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffc6' align='right'>
                        <div class='pt11b'><?= number_format($han_kofukuri_total_kin) ?></div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>����ʡ����</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ccffcc' align='right'>
                        <div class='pt11b'><?= number_format($han_kofukuri_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>��</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>��</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right'>
                        <div class='pt11b'>��</div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>���</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right'>
                        <div class='pt11b'><?= number_format($han_kofukuri_total_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>��</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>���</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right'>
                        <div class='pt11b'><?= number_format($han_kofukuri_total_kin) ?></div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' rowspan='3' valign='top'>
                        <div class='pt10b'>��</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' rowspan='3' valign='top'>
                        <div class='pt10b'>�δ���</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>�࿦��Ϳ��</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ccffcc' align='right'>
                        <div class='pt11b'><?= number_format($han_taikyuyo_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>��</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' rowspan='3' valign='top'>
                        <div class='pt10b'>�δ���</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>�࿦��������</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffc6' align='right'>
                        <div class='pt11b'><?= number_format($han_taikyufu_total_kin) ?></div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>�࿦��������</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ccffcc' align='right'>
                        <div class='pt11b'><?= number_format($han_taikyufu_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>��</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>��</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right'>
                        <div class='pt11b'>��</div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>���</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right'>
                        <div class='pt11b'><?= number_format($han_taikyufu_total_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>��</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>���</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right'>
                        <div class='pt11b'><?= number_format($han_taikyufu_total_kin) ?></div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' rowspan='3' valign='top'>
                        <div class='pt10b'>����</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' rowspan='3' valign='top'>
                        <div class='pt10b'>��¤����</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>ι�������</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ccffcc' align='right'>
                        <div class='pt11b'><?= number_format($sei_ryohi_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>��</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' rowspan='3' valign='top'>
                        <div class='pt10b'>��¤����</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>ι�������</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffc6' align='right'>
                        <div class='pt11b'><?= number_format($sei_ryohi_total_kin) ?></div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>������ĥ��</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ccffcc' align='right'>
                        <div class='pt11b'><?= number_format($sei_kaigai_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>��</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>��</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right'>
                        <div class='pt11b'>��</div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>���</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right'>
                        <div class='pt11b'><?= number_format($sei_ryohi_total_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>��</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>���</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right'>
                        <div class='pt11b'><?= number_format($sei_ryohi_total_kin) ?></div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' rowspan='3' valign='top'>
                        <div class='pt10b'>����</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' rowspan='3' valign='top'>
                        <div class='pt10b'>��¤����</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>��̳������</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ccffcc' align='right'>
                        <div class='pt11b'><?= number_format($sei_gyomu_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>��</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' rowspan='3' valign='top'>
                        <div class='pt10b'>��¤����</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>��̳������</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffc6' align='right'>
                        <div class='pt11b'><?= number_format($sei_gyomu_total_kin) ?></div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>��ʧ�����</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ccffcc' align='right'>
                        <div class='pt11b'><?= number_format($sei_tesu_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>��</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>��</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right'>
                        <div class='pt11b'>��</div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>���</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right'>
                        <div class='pt11b'><?= number_format($sei_gyomu_total_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>��</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>���</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right'>
                        <div class='pt11b'><?= number_format($sei_gyomu_total_kin) ?></div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' rowspan='6' valign='top'>
                        <div class='pt10b'>����</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' rowspan='6' valign='top'>
                        <div class='pt10b'>�δ���</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>����</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ccffcc' align='right'>
                        <div class='pt11b'><?= number_format($sei_zappi_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>��</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' rowspan='6' valign='top'>
                        <div class='pt10b'>�δ���</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>����</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffc6' align='right'>
                        <div class='pt11b'><?= number_format($sei_zappi_total_kin) ?></div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>����������</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ccffcc' align='right'>
                        <div class='pt11b'><?= number_format($sei_kokoku_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>��</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>��</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right'>
                        <div class='pt11b'>��</div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>�����</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ccffcc' align='right'>
                        <div class='pt11b'><?= number_format($sei_kyujin_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>��</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>��</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right'>
                        <div class='pt11b'>��</div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>�ݾڽ�����</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ccffcc' align='right'>
                        <div class='pt11b'><?= number_format($sei_hosyo_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>��</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>��</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right'>
                        <div class='pt11b'>��</div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>������</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ccffcc' align='right'>
                        <div class='pt11b'><?= number_format($sei_kaihi_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>��</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>��</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right'>
                        <div class='pt11b'>��</div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>���</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right'>
                        <div class='pt11b'><?= number_format($sei_zappi_total_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>��</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>���</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right'>
                        <div class='pt11b'><?= number_format($sei_zappi_total_kin) ?></div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' rowspan='3' valign='top'>
                        <div class='pt10b'>����</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' rowspan='3' valign='top'>
                        <div class='pt10b'>��¤����</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>�������</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ccffcc' align='right'>
                        <div class='pt11b'><?= number_format($sei_yachin_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>��</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' rowspan='3' valign='top'>
                        <div class='pt10b'>��¤����</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>�������</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffc6' align='right'>
                        <div class='pt11b'><?= number_format($sei_yachin_total_kin) ?></div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>������</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ccffcc' align='right'>
                        <div class='pt11b'><?= number_format($sei_kura_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>��</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>��</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right'>
                        <div class='pt11b'>��</div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>���</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right'>
                        <div class='pt11b'><?= number_format($sei_yachin_total_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>��</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>���</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right'>
                        <div class='pt11b'><?= number_format($sei_yachin_total_kin) ?></div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' rowspan='3' valign='top'>
                        <div class='pt10b'>����</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' rowspan='3' valign='top'>
                        <div class='pt10b'>ϫ̳��</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>ˡ��ʡ����</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ccffcc' align='right'>
                        <div class='pt11b'><?= number_format($sei_hofukuri_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>��</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' rowspan='3' valign='top'>
                        <div class='pt10b'>ϫ̳��</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>����ʡ����</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffc6' align='right'>
                        <div class='pt11b'><?= number_format($sei_kofukuri_total_kin) ?></div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>����ʡ����</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ccffcc' align='right'>
                        <div class='pt11b'><?= number_format($sei_kofukuri_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>��</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>��</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right'>
                        <div class='pt11b'>��</div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>���</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right'>
                        <div class='pt11b'><?= number_format($sei_kofukuri_total_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>��</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>���</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right'>
                        <div class='pt11b'><?= number_format($sei_kofukuri_total_kin) ?></div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' rowspan='3' valign='top'>
                        <div class='pt10b'>����</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' rowspan='3' valign='top'>
                        <div class='pt10b'>ϫ̳��</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>�࿦��Ϳ��</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ccffcc' align='right'>
                        <div class='pt11b'><?= number_format($sei_taikyuyo_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>��</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' rowspan='3' valign='top'>
                        <div class='pt10b'>ϫ̳��</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>�࿦��������</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffc6' align='right'>
                        <div class='pt11b'><?= number_format($sei_taikyufu_total_kin) ?></div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>�࿦��������</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ccffcc' align='right'>
                        <div class='pt11b'><?= number_format($sei_taikyufu_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>��</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>��</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right'>
                        <div class='pt11b'>��</div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>���</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right'>
                        <div class='pt11b'><?= number_format($sei_taikyufu_total_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>��</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>���</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right'>
                        <div class='pt11b'><?= number_format($sei_taikyufu_total_kin) ?></div>
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
