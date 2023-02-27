<?php
//////////////////////////////////////////////////////////////////////////////
// �»�״ط� ��������������ٽ�                                          //
// Copyright(C) 2020-2020 Norihisa.Ohya norihisa_ooya@nitto-kohki.co.jp     //
// Changed history                                                          //
// 2020/06/12 Created   account_statement_view.php                          //
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
    $menu->set_title("�� {$ki} �����ܷ軻�������ꡡ�ʡ��ܡ��⡡���������١���");
} else {
    $menu->set_title("�� {$ki} ������{$hanki}��Ⱦ���������ꡡ�ʡ��ܡ��⡡���������١���");
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

// �¶⾮�פη׻�
$yokin_total_kin = $touza_kin + $futsu_kin + $teiki_kin;
// ����ڤ��¶��פη׻�
$genyo_total_kin = $genkin_kin + $touza_kin + $futsu_kin + $teiki_kin;

// ���⤪����¶������ ����
// �����¶� 10��­����11����ɩUFJ��12����ɩUFJ����
$res_k   = array();
$field_k = array();
$rows_k  = array();
$sum1 = '1104';
$sum2 = '00';
$sum3 = '10';
$query_k = sprintf("select rep_cri, rep_de, rep_cr from financial_report_cal where rep_ymd=%d and rep_summary1='%s' and rep_summary2='%s' and rep_gin='%s'", $nk_ki, $sum1, $sum2, $sum3);
if ($rows_k=getResultWithField2($query_k, $field_k, $res_k) <= 0) {
    $ashi_futu_kishu = 0;
} else {
    $ashi_futu_kishu = $res_k[0][0];
}
$res   = array();
$field = array();
$rows  = array();
$query = sprintf("select SUM(rep_de), SUM(rep_cr) from financial_report_month where rep_ymd>=%d and rep_ymd<=%d and rep_summary1='%s' and rep_gin='%s'", $str_ym, $end_ym, $sum1, $sum3);
if ($rows=getResultWithField2($query, $field, $res) <= 0) {
    $ashi_futu_kin = $ashi_futu_kishu;
} else {
    $ashi_futu_kin = $ashi_futu_kishu + ($res[0][0] - $res[0][1]);
}
$res_k   = array();
$field_k = array();
$rows_k  = array();
$sum1 = '1104';
$sum2 = '00';
$sum3 = '11';
$query_k = sprintf("select rep_cri, rep_de, rep_cr from financial_report_cal where rep_ymd=%d and rep_summary1='%s' and rep_summary2='%s' and rep_gin='%s'", $nk_ki, $sum1, $sum2, $sum3);
if ($rows_k=getResultWithField2($query_k, $field_k, $res_k) <= 0) {
    $ufj_futu_kishu = 0;
} else {
    $ufj_futu_kishu = $res_k[0][0];
}
$res   = array();
$field = array();
$rows  = array();
$query = sprintf("select SUM(rep_de), SUM(rep_cr) from financial_report_month where rep_ymd>=%d and rep_ymd<=%d and rep_summary1='%s' and rep_gin='%s'", $str_ym, $end_ym, $sum1, $sum3);
if ($rows=getResultWithField2($query, $field, $res) <= 0) {
    $ufj_futu_kin = $ufj_futu_kishu;
} else {
    $ufj_futu_kin = $ufj_futu_kishu + ($res[0][0] - $res[0][1]);
}
$res_k   = array();
$field_k = array();
$rows_k  = array();
$sum1 = '1104';
$sum2 = '00';
$sum3 = '12';
$query_k = sprintf("select rep_cri, rep_de, rep_cr from financial_report_cal where rep_ymd=%d and rep_summary1='%s' and rep_summary2='%s' and rep_gin='%s'", $nk_ki, $sum1, $sum2, $sum3);
if ($rows_k=getResultWithField2($query_k, $field_k, $res_k) <= 0) {
    $ufjs_futu_kishu = 0;
} else {
    $ufjs_futu_kishu = $res_k[0][0];
}
$res   = array();
$field = array();
$rows  = array();
$query = sprintf("select SUM(rep_de), SUM(rep_cr) from financial_report_month where rep_ymd>=%d and rep_ymd<=%d and rep_summary1='%s' and rep_gin='%s'", $str_ym, $end_ym, $sum1, $sum3);
if ($rows=getResultWithField2($query, $field, $res) <= 0) {
    $ufjs_futu_kin = $ufjs_futu_kishu;
} else {
    $ufjs_futu_kin = $ufjs_futu_kishu + ($res[0][0] - $res[0][1]);
}

// ����¶� 10��­����11����ɩUFJ��12����ɩUFJ����
$res_k   = array();
$field_k = array();
$rows_k  = array();
$sum1 = '1106';
$sum2 = '00';
$sum3 = '10';
$query_k = sprintf("select rep_cri, rep_de, rep_cr from financial_report_cal where rep_ymd=%d and rep_summary1='%s' and rep_summary2='%s' and rep_gin='%s'", $nk_ki, $sum1, $sum2, $sum3);
if ($rows_k=getResultWithField2($query_k, $field_k, $res_k) <= 0) {
    $ashi_teiki_kishu = 0;
} else {
    $ashi_teiki_kishu = $res_k[0][0];
}
$res   = array();
$field = array();
$rows  = array();
$query = sprintf("select SUM(rep_de), SUM(rep_cr) from financial_report_month where rep_ymd>=%d and rep_ymd<=%d and rep_summary1='%s' and rep_gin='%s'", $str_ym, $end_ym, $sum1, $sum3);
if ($rows=getResultWithField2($query, $field, $res) <= 0) {
    $ashi_teiki_kin = $ashi_teiki_kishu;
} else {
    $ashi_teiki_kin = $ashi_teiki_kishu + ($res[0][0] - $res[0][1]);
}
$res_k   = array();
$field_k = array();
$rows_k  = array();
$sum1 = '1106';
$sum2 = '00';
$sum3 = '11';
$query_k = sprintf("select rep_cri, rep_de, rep_cr from financial_report_cal where rep_ymd=%d and rep_summary1='%s' and rep_summary2='%s' and rep_gin='%s'", $nk_ki, $sum1, $sum2, $sum3);
if ($rows_k=getResultWithField2($query_k, $field_k, $res_k) <= 0) {
    $ufj_teiki_kishu = 0;
} else {
    $ufj_teiki_kishu = $res_k[0][0];
}
$res   = array();
$field = array();
$rows  = array();
$query = sprintf("select SUM(rep_de), SUM(rep_cr) from financial_report_month where rep_ymd>=%d and rep_ymd<=%d and rep_summary1='%s' and rep_gin='%s'", $str_ym, $end_ym, $sum1, $sum3);
if ($rows=getResultWithField2($query, $field, $res) <= 0) {
    $ufj_teiki_kin = $ufj_teiki_kishu;
} else {
    $ufj_teiki_kin = $ufj_teiki_kishu + ($res[0][0] - $res[0][1]);
}
$res_k   = array();
$field_k = array();
$rows_k  = array();
$sum1 = '1106';
$sum2 = '00';
$sum3 = '12';
$query_k = sprintf("select rep_cri, rep_de, rep_cr from financial_report_cal where rep_ymd=%d and rep_summary1='%s' and rep_summary2='%s' and rep_gin='%s'", $nk_ki, $sum1, $sum2, $sum3);
if ($rows_k=getResultWithField2($query_k, $field_k, $res_k) <= 0) {
    $ufjs_teiki_kishu = 0;
} else {
    $ufjs_teiki_kishu = $res_k[0][0];
}
$res   = array();
$field = array();
$rows  = array();
$query = sprintf("select SUM(rep_de), SUM(rep_cr) from financial_report_month where rep_ymd>=%d and rep_ymd<=%d and rep_summary1='%s' and rep_gin='%s'", $str_ym, $end_ym, $sum1, $sum3);
if ($rows=getResultWithField2($query, $field, $res) <= 0) {
    $ufjs_teiki_kin = $ufjs_teiki_kishu;
} else {
    $ufjs_teiki_kin = $ufjs_teiki_kishu + ($res[0][0] - $res[0][1]);
}

// ������¶��
$ashi_total_kin = $ashi_futu_kin + $ashi_teiki_kin;
$ufj_total_kin  = $ufj_futu_kin + $ufj_teiki_kin;
$ufjs_total_kin = $ufjs_futu_kin + $ufjs_teiki_kin;

// ��ݶ�
$res   = array();
$field = array();
$rows  = array();
$urikake_kin = 0;
$note = '��ݶ�';
$query = sprintf("SELECT kin FROM profit_loss_bs_history WHERE pl_bs_ym=%d and note='%s'", $yyyymm, $note);
if ($rows=getResultWithField2($query, $field, $res) <= 0) {
    $urikake_kin = 0;
} else {
    $urikake_kin = $res[0][0];
}

// ��ݶ� �����μ���
// NK 
$res_k   = array();
$field_k = array();
$rows_k  = array();
$sum1 = '00001';
$sum2 = '00';
$query_k = sprintf("select SUM(rep_cri), SUM(rep_de), SUM(rep_cr) from financial_report_cal where rep_ymd=%d and rep_summary1='%s'", $end_ym, $sum1);
if ($rows_k=getResultWithField2($query_k, $field_k, $res_k) <= 0) {
    $nk_uri_kishu = 0;
} else {
    $nk_uri_kishu = $res_k[0][0];
}
$res   = array();
$field = array();
$rows  = array();
$query = sprintf("select SUM(rep_de), SUM(rep_cr) from financial_report_cal where rep_ymd=%d and rep_summary1='%s'", $end_ym, $sum1);
if ($rows=getResultWithField2($query, $field, $res) <= 0) {
    $nk_uri_kin = $nk_uri_kishu;
} else {
    $nk_uri_kin = $nk_uri_kishu + ($res[0][0] - $res[0][1]);
    if ($end_ym==202006) {
        $nk_uri_kin = 470191600;
    }
}

// ��ݶ� �����μ���
// MT 
$res_k   = array();
$field_k = array();
$rows_k  = array();
$sum1 = '00004';
$sum2 = '00';
$query_k = sprintf("select SUM(rep_cri), SUM(rep_de), SUM(rep_cr) from financial_report_cal where rep_ymd=%d and rep_summary1='%s'", $end_ym, $sum1);
if ($rows_k=getResultWithField2($query_k, $field_k, $res_k) <= 0) {
    $mt_uri_kishu = 0;
} else {
    $mt_uri_kishu = $res_k[0][0];
}
$res   = array();
$field = array();
$rows  = array();
$query = sprintf("select SUM(rep_de), SUM(rep_cr) from financial_report_cal where rep_ymd=%d and rep_summary1='%s'", $end_ym, $sum1);
if ($rows=getResultWithField2($query, $field, $res) <= 0) {
    $mt_uri_kin = $mt_uri_kishu;
} else {
    $mt_uri_kin = $mt_uri_kishu + ($res[0][0] - $res[0][1]);
}

// ��ݶ� �����μ���
// SNK
$res_k   = array();
$field_k = array();
$rows_k  = array();
$sum1 = '00005';
$sum2 = '00';
$query_k = sprintf("select SUM(rep_cri), SUM(rep_de), SUM(rep_cr) from financial_report_cal where rep_ymd=%d and rep_summary1='%s'", $end_ym, $sum1);
if ($rows_k=getResultWithField2($query_k, $field_k, $res_k) <= 0) {
    $snk_uri_kishu = 0;
} else {
    $snk_uri_kishu = $res_k[0][0];
}
$res   = array();
$field = array();
$rows  = array();
$query = sprintf("select SUM(rep_de), SUM(rep_cr) from financial_report_cal where rep_ymd=%d and rep_summary1='%s'", $end_ym, $sum1);
if ($rows=getResultWithField2($query, $field, $res) <= 0) {
    $snk_uri_kin = $snk_uri_kishu;
} else {
    $snk_uri_kin = $snk_uri_kishu + ($res[0][0] - $res[0][1]);
}

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

// ��¢��
$res   = array();
$field = array();
$rows  = array();
$note = '��¢��';
$query = sprintf("SELECT rep_kin FROM financial_report_data WHERE rep_ymd=%d and rep_note='%s'", $yyyymm, $note);
if ($rows=getResultWithField2($query, $field, $res) <= 0) {
    $chozo_kin = 0;
} else {
    $chozo_kin = $res[0][0];
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

// ɾ���ڲ���ȴ�Ф�
// ɾ���ڲ�������
$res   = array();
$field = array();
$rows  = array();
$note = 'ɾ���ڲ�������';
$query = sprintf("SELECT rep_kin FROM financial_report_data WHERE rep_ymd=%d and rep_note='%s'", $yyyymm, $note);
if ($rows=getResultWithField2($query, $field, $res) <= 0) {
    $hyoka_buhin_kin = 0;
} else {
    $hyoka_buhin_kin = $res[0][0];
}
// ɾ���ڲ�������
$res   = array();
$field = array();
$rows  = array();
$note = 'ɾ���ڲ�������';
$query = sprintf("SELECT rep_kin FROM financial_report_data WHERE rep_ymd=%d and rep_note='%s'", $yyyymm, $note);
if ($rows=getResultWithField2($query, $field, $res) <= 0) {
    $hyoka_zai_kin = 0;
} else {
    $hyoka_zai_kin = $res[0][0];
}
// �����ų�����
$res   = array();
$field = array();
$rows  = array();
$note = '�����ų�����';
$query = sprintf("SELECT rep_kin FROM financial_report_data WHERE rep_ymd=%d and rep_note='%s'", $yyyymm, $note);
if ($rows=getResultWithField2($query, $field, $res) <= 0) {
    $tana_ken_kin = 0;
} else {
    $tana_ken_kin = $res[0][0];
}

// ����ų�����
$res   = array();
$field = array();
$rows  = array();
$note = '����ų�����';
$query = sprintf("SELECT rep_kin FROM financial_report_data WHERE rep_ymd=%d and rep_note='%s'", $yyyymm, $note);
if ($rows=getResultWithField2($query, $field, $res) <= 0) {
    $tana_kou_kin = 0;
} else {
    $tana_kou_kin = $res[0][0];
}

// ����ų�����
$res   = array();
$field = array();
$rows  = array();
$note = '����ų�����';
$query = sprintf("SELECT rep_kin FROM financial_report_data WHERE rep_ymd=%d and rep_note='%s'", $yyyymm, $note);
if ($rows=getResultWithField2($query, $field, $res) <= 0) {
    $tana_gai_kin = 0;
} else {
    $tana_gai_kin = $res[0][0];
}

// ê���񻺤������׻�
$sei_buhin_kin  = $buhin_kin - $hyoka_buhin_kin;
$han_total_kin  = $tana_ken_kin + $tana_kou_kin + $tana_gai_kin;
$gen_sizai_kin  = $genzai_kin - $hyoka_zai_kin;
$tana_sizai_kin = $sei_buhin_kin + $tana_ken_kin + $gen_sizai_kin;
$kumi_cc_kin    = $sonotatana_kin + $hyoka_buhin_kin + $hyoka_zai_kin;
$kumi_total_kin = $kumi_cc_kin + $sikakari_total_kin;

// ��ʧ����
$res   = array();
$field = array();
$rows  = array();
$mae_hiyo_kin = 0;
$note = '��ʧ����';
$query = sprintf("SELECT kin FROM profit_loss_bs_history WHERE pl_bs_ym=%d and note='%s'", $yyyymm, $note);
if ($rows=getResultWithField2($query, $field, $res) <= 0) {
    $mae_hiyo_kin = 0;
} else {
    $mae_hiyo_kin = $res[0][0];
}

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


//// 2018/10/10 18/09���鷫���Ƕ�񻺤ϤޤȤ��
// �����Ƕ��
$res   = array();
$field = array();
$rows  = array();
$ryu_kurizei_shisan_kin = 0;
$note = 'ήư�����Ƕ��';
$query = sprintf("SELECT kin FROM profit_loss_bs_history WHERE pl_bs_ym=%d and note='%s'", $yyyymm, $note);
if ($rows=getResultWithField2($query, $field, $res) <= 0) {
    $ryu_kurizei_shisan_kin = 0;
} else {
    $ryu_kurizei_shisan_kin = $res[0][0];
}
// �����Ƕ��
$res   = array();
$field = array();
$rows  = array();
$kotei_kuri_zei_kin = 0;
$note = '���귫���Ƕ��';
$query = sprintf("SELECT kin FROM profit_loss_bs_history WHERE pl_bs_ym=%d and note='%s'", $yyyymm, $note);
if ($rows=getResultWithField2($query, $field, $res) <= 0) {
    $kotei_kuri_zei_kin = 0;
} else {
    $kotei_kuri_zei_kin = $res[0][0];
}
$kotei_kuri_zei_kin = $kotei_kuri_zei_kin + $ryu_kurizei_shisan_kin;

// �����Ƕ�� ����
// ήư��
$res_k   = array();
$field_k = array();
$rows_k  = array();
$sum1 = '1702';
$sum2 = '00';
$query_k = sprintf("select rep_cri, rep_de, rep_cr from financial_report_cal where rep_ymd=%d and rep_summary1='%s' and rep_summary2='%s'", $nk_ki, $sum1, $sum2);
if ($rows_k=getResultWithField2($query_k, $field_k, $res_k) <= 0) {
    $ryudo_kurizei_kishu = 0;
} else {
    $ryudo_kurizei_kishu = $res_k[0][0];
}
// �����Ƕ�� �����׻�
$res   = array();
$field = array();
$rows  = array();
$query = sprintf("select SUM(rep_de), SUM(rep_cr) from financial_report_month where rep_ymd>=%d and rep_ymd<=%d and rep_summary1='%s'", $str_ym, $end_ym, $sum1);
if ($rows=getResultWithField2($query, $field, $res) <= 0) {
    $ryudo_kurizei_kin_zou = 0;
    $ryudo_kurizei_kin_gen = 0;
} else {
    $ryudo_kurizei_kin_zou = $res[0][0];
    $ryudo_kurizei_kin_gen = $res[0][1];
    
    if ($ryudo_kurizei_kin_zou >= $ryudo_kurizei_kin_gen) {
        $ryudo_kurizei_kin_zou = $ryudo_kurizei_kin_zou - $ryudo_kurizei_kin_gen;
        $ryudo_kurizei_kin_gen = 0;
    } else {
        $ryudo_kurizei_kin_gen = $ryudo_kurizei_kin_gen - $ryudo_kurizei_kin_zou;
        $ryudo_kurizei_kin_zou = 0;
    }
}

// �����
$res_k   = array();
$field_k = array();
$rows_k  = array();
$sum1 = '2312';
$sum2 = '00';
$query_k = sprintf("select rep_cri, rep_de, rep_cr from financial_report_cal where rep_ymd=%d and rep_summary1='%s' and rep_summary2='%s'", $nk_ki, $sum1, $sum2);
if ($rows_k=getResultWithField2($query_k, $field_k, $res_k) <= 0) {
    $kotei_kurizei_kishu = 0;
} else {
    $kotei_kurizei_kishu = $res_k[0][0];
}

$kotei_kurizei_kishu = $kotei_kurizei_kishu + $ryudo_kurizei_kishu;

// �����Ƕ�� �����׻�
$res   = array();
$field = array();
$rows  = array();
$query = sprintf("select SUM(rep_de), SUM(rep_cr) from financial_report_month where rep_ymd>=%d and rep_ymd<=%d and rep_summary1='%s'", $str_ym, $end_ym, $sum1);
if ($rows=getResultWithField2($query, $field, $res) <= 0) {
    $kotei_kurizei_kin_zou = 0;
    $kotei_kurizei_kin_gen = 0;
} else {
    $kotei_kurizei_kin_zou = $res[0][0];
    $kotei_kurizei_kin_gen = $res[0][1];
    
    if ($kotei_kurizei_kin_zou >= $kotei_kurizei_kin_gen) {
        $kotei_kurizei_kin_zou = $kotei_kurizei_kin_zou - $kotei_kurizei_kin_gen;
        $kotei_kurizei_kin_gen = 0;
    } else {
        $kotei_kurizei_kin_gen = $kotei_kurizei_kin_gen - $kotei_kurizei_kin_zou;
        $kotei_kurizei_kin_zou = 0;
    }
}

$kotei_kurizei_kin_zou = $kotei_kurizei_kin_zou + $ryudo_kurizei_kin_zou;
$kotei_kurizei_kin_gen = $kotei_kurizei_kin_gen + $ryudo_kurizei_kin_gen;

// Ĺ�����ն�
$res   = array();
$field = array();
$rows  = array();
$choki_kashi_kin = 0;
$note = 'Ĺ�����ն�';
$query = sprintf("SELECT kin FROM profit_loss_bs_history WHERE pl_bs_ym=%d and note='%s'", $yyyymm, $note);
if ($rows=getResultWithField2($query, $field, $res) <= 0) {
    $choki_kashi_kin = 0;
} else {
    $choki_kashi_kin = $res[0][0];
}

// ���Ȱ�Ĺ�����ն� ����
$res_k   = array();
$field_k = array();
$rows_k  = array();
$sum1 = '2303';
$sum2 = '20';
$query_k = sprintf("select rep_cri, rep_de, rep_cr from financial_report_cal where rep_ymd=%d and rep_summary1='%s' and rep_summary2='%s'", $nk_ki, $sum1, $sum2);
if ($rows_k=getResultWithField2($query_k, $field_k, $res_k) <= 0) {
    $jyu_kashi_kishu = 0;
} else {
    $jyu_kashi_kishu = $res_k[0][0];
}
$res   = array();
$field = array();
$rows  = array();
$query = sprintf("select SUM(rep_de), SUM(rep_cr) from financial_report_month where rep_ymd>=%d and rep_ymd<=%d and rep_summary1='%s'", $str_ym, $end_ym, $sum1);
if ($rows=getResultWithField2($query, $field, $res) <= 0) {
    $jyu_kashi_kin_zou = 0;
    $jyu_kashi_kin_gen = 0;
} else {
    $jyu_kashi_kin_zou = $res[0][0];
    $jyu_kashi_kin_gen = $res[0][1];
}

// Ĺ����ʧ����
$res   = array();
$field = array();
$rows  = array();
$choki_maebara_kin = 0;
$note = 'Ĺ����ʧ����';
$query = sprintf("SELECT kin FROM profit_loss_bs_history WHERE pl_bs_ym=%d and note='%s'", $yyyymm, $note);
if ($rows=getResultWithField2($query, $field, $res) <= 0) {
    $choki_maebara_kin = 0;
} else {
    $choki_maebara_kin = $res[0][0];
}
$res_k   = array();
$field_k = array();
$rows_k  = array();
$sum1 = '2308';
$query_k = sprintf("select SUM(rep_cri), SUM(rep_de), SUM(rep_cr) from financial_report_cal where rep_ymd=%d and rep_summary1='%s'", $nk_ki, $sum1);
if ($rows_k=getResultWithField2($query_k, $field_k, $res_k) <= 0) {
    $choki_maebara_kishu = 0;
} else {
    $choki_maebara_kishu = $res_k[0][0];
}
$res   = array();
$field = array();
$rows  = array();
$query = sprintf("select SUM(rep_de), SUM(rep_cr) from financial_report_month where rep_ymd>=%d and rep_ymd<=%d and rep_summary1='%s'", $str_ym, $end_ym, $sum1);
if ($rows=getResultWithField2($query, $field, $res) <= 0) {
    $choki_maebara_kin_zou = 0;
    $choki_maebara_kin_gen = 0;
} else {
    $choki_maebara_kin_zou = $res[0][0];
    $choki_maebara_kin_gen = $res[0][1];
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
*/
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

// ̤ʧ�������������� �׻�
///// ������ ǯ��λ���
$end_yyyy = substr($end_ym, 0,4);
$end_mm   = substr($end_ym, 4,2);

if ($end_mm == 3) {                     // 3��ξ��9��ȹ绻
    // ��ʧ�����Ƿ׻� �Ǹ�ޥ��ʥ�
    // ��ʧ��������
    $res_k   = array();
    $field_k = array();
    $rows_k  = array();
    $sum1 = '1508';
    $sum2 = '00';
    $sum3 = '34';
    $query_k = sprintf("select SUM(rep_cri) from financial_report_cal where rep_ymd>=%d and rep_ymd<=%d and rep_summary1='%s' and rep_summary2='%s' and rep_gin='%s'", $str_ym, $end_ym, $sum1, $sum2, $sum3);
    if ($rows_k=getResultWithField2($query_k, $field_k, $res_k) <= 0) {
        $karibara_zei_kin = 0;
    } else {
        $karibara_zei_kin = $res_k[0][0];
    }
    // ��ʧ����������͢����
    $res_k   = array();
    $field_k = array();
    $rows_k  = array();
    $sum1 = '1508';
    $sum2 = '20';
    $sum3 = '34';
    $query_k = sprintf("select SUM(rep_cri) from financial_report_cal where rep_ymd>=%d and rep_ymd<=%d and rep_summary1='%s' and rep_summary2='%s' and rep_gin='%s'", $str_ym, $end_ym, $sum1, $sum2, $sum3);
    if ($rows_k=getResultWithField2($query_k, $field_k, $res_k) <= 0) {
        $karibara_yunyu_zei_kin = 0;
    } else {
        $karibara_yunyu_zei_kin = $res_k[0][0];
    }
    // ̤ʧ�������������Ǽ��ʬ��
    $res_k   = array();
    $field_k = array();
    $rows_k  = array();
    $sum1 = '1560';
    $sum2 = '00';
    $sum3 = '34';
    $query_k = sprintf("select SUM(rep_cri) from financial_report_cal where rep_ymd>=%d and rep_ymd<=%d and rep_summary1='%s' and rep_summary2='%s' and rep_gin='%s'", $str_ym, $end_ym, $sum1, $sum2, $sum3);
    if ($rows_k=getResultWithField2($query_k, $field_k, $res_k) <= 0) {
        $mae_sho_zei_kin = 0;
    } else {
        $mae_sho_zei_kin = $res_k[0][0];
    }
    // ��ʧ������ ���
    $karibara_zei_total = -($karibara_zei_kin+$karibara_yunyu_zei_kin+$mae_sho_zei_kin);
    
    // ���������Ƿ׻�
    // ������������
    $res_k   = array();
    $field_k = array();
    $rows_k  = array();
    $sum1 = '3227';
    $sum2 = '00';
    $sum3 = '34';
    $query_k = sprintf("select SUM(rep_cri) from financial_report_cal where rep_ymd>=%d and rep_ymd<=%d and rep_summary1='%s' and rep_summary2='%s' and rep_gin='%s'", $str_ym, $end_ym, $sum1, $sum2, $sum3);
    if ($rows_k=getResultWithField2($query_k, $field_k, $res_k) <= 0) {
        $kariuke_sho_zei_kin = 0;
    } else {
        $kariuke_sho_zei_kin = $res_k[0][0];
    }
    
} elseif ($end_mm == 9) {           // 9��ξ��9��Τ�
    // ��ʧ�����Ƿ׻� �Ǹ�ޥ��ʥ�
    // ��ʧ��������
    $res_k   = array();
    $field_k = array();
    $rows_k  = array();
    $sum1 = '1508';
    $sum2 = '00';
    $sum3 = '34';
    $query_k = sprintf("select SUM(rep_cri) from financial_report_cal where rep_ymd>=%d and rep_ymd<=%d and rep_summary1='%s' and rep_summary2='%s' and rep_gin='%s'", $str_ym, $end_ym, $sum1, $sum2, $sum3);
    if ($rows_k=getResultWithField2($query_k, $field_k, $res_k) <= 0) {
        $karibara_zei_kin = 0;
    } else {
        $karibara_zei_kin = $res_k[0][0];
    }
    // ��ʧ����������͢����
    $res_k   = array();
    $field_k = array();
    $rows_k  = array();
    $sum1 = '1508';
    $sum2 = '20';
    $sum3 = '34';
    $query_k = sprintf("select SUM(rep_cri) from financial_report_cal where rep_ymd>=%d and rep_ymd<=%d and rep_summary1='%s' and rep_summary2='%s' and rep_gin='%s'", $str_ym, $end_ym, $sum1, $sum2, $sum3);
    if ($rows_k=getResultWithField2($query_k, $field_k, $res_k) <= 0) {
        $karibara_yunyu_zei_kin = 0;
    } else {
        $karibara_yunyu_zei_kin = $res_k[0][0];
    }
    // ��ʧ�������������Ǽ��ʬ��
    $res_k   = array();
    $field_k = array();
    $rows_k  = array();
    $sum1 = '1560';
    $sum2 = '00';
    $sum3 = '34';
    $query_k = sprintf("select SUM(rep_cri) from financial_report_cal where rep_ymd>=%d and rep_ymd<=%d and rep_summary1='%s' and rep_summary2='%s' and rep_gin='%s'", $str_ym, $end_ym, $sum1, $sum2, $sum3);
    if ($rows_k=getResultWithField2($query_k, $field_k, $res_k) <= 0) {
        $mae_sho_zei_kin = 0;
    } else {
        $mae_sho_zei_kin = $res_k[0][0];
    }
    // ��ʧ������ ���
    $karibara_zei_total = -($karibara_zei_kin+$karibara_yunyu_zei_kin+$mae_sho_zei_kin);
    
    // ���������Ƿ׻�
    // ������������
    $res_k   = array();
    $field_k = array();
    $rows_k  = array();
    $sum1 = '3227';
    $sum2 = '00';
    $sum3 = '34';
    $query_k = sprintf("select SUM(rep_cri) from financial_report_cal where rep_ymd>=%d and rep_ymd<=%d and rep_summary1='%s' and rep_summary2='%s' and rep_gin='%s'", $str_ym, $end_ym, $sum1, $sum2, $sum3);
    if ($rows_k=getResultWithField2($query_k, $field_k, $res_k) <= 0) {
        $kariuke_sho_zei_kin = 0;
    } else {
        $kariuke_sho_zei_kin = $res_k[0][0];
    }
}  elseif ($end_mm == 12) {           // 12��ξ�� 9��ʬ��10��12��ʬ�ι��
    // ����ǯ��
    $ss_str_ym = $end_yyyy . '10';
    // ��ʧ�����Ƿ׻� �Ǹ�ޥ��ʥ�
    // ��ʧ�������� 9��ʬ
    $res_k   = array();
    $field_k = array();
    $rows_k  = array();
    $sum1 = '1508';
    $sum2 = '00';
    $sum3 = '34';
    $query_k = sprintf("select SUM(rep_cri) from financial_report_cal where rep_ymd>=%d and rep_ymd<=%d and rep_summary1='%s' and rep_summary2='%s' and rep_gin='%s'", $str_ym, $end_ym, $sum1, $sum2, $sum3);
    if ($rows_k=getResultWithField2($query_k, $field_k, $res_k) <= 0) {
        $karibara_zei_kin = 0;
    } else {
        $karibara_zei_kin = $res_k[0][0];
    }
    // ��ʧ�������� 10��12��ʬ
    $res   = array();
    $field = array();
    $rows  = array();
    $query = sprintf("select SUM(rep_de), SUM(rep_cr) from financial_report_month where rep_ymd>=%d and rep_ymd<=%d and rep_summary1='%s' and rep_summary2='%s'", $ss_str_ym, $end_ym, $sum1, $sum2);
    if ($rows=getResultWithField2($query, $field, $res) <= 0) {
        $karibara_zei_kin = $karibara_zei_kin;
    } else {
        $karibara_zei_kin = $karibara_zei_kin + ($res[0][0] - $res[0][1]);
    }
    // ��ʧ����������͢����
    $res_k   = array();
    $field_k = array();
    $rows_k  = array();
    $sum1 = '1508';
    $sum2 = '20';
    $sum3 = '34';
    $query_k = sprintf("select SUM(rep_cri) from financial_report_cal where rep_ymd>=%d and rep_ymd<=%d and rep_summary1='%s' and rep_summary2='%s' and rep_gin='%s'", $str_ym, $end_ym, $sum1, $sum2, $sum3);
    if ($rows_k=getResultWithField2($query_k, $field_k, $res_k) <= 0) {
        $karibara_yunyu_zei_kin = 0;
    } else {
        $karibara_yunyu_zei_kin = $res_k[0][0];
    }
    // ��ʧ����������͢���� 10��12��ʬ
    $res   = array();
    $field = array();
    $rows  = array();
    $query = sprintf("select SUM(rep_de), SUM(rep_cr) from financial_report_month where rep_ymd>=%d and rep_ymd<=%d and rep_summary1='%s' and rep_summary2='%s'", $ss_str_ym, $end_ym, $sum1, $sum2);
    if ($rows=getResultWithField2($query, $field, $res) <= 0) {
        $karibara_yunyu_zei_kin = $karibara_yunyu_zei_kin;
    } else {
        $karibara_yunyu_zei_kin = $karibara_yunyu_zei_kin + ($res[0][0] - $res[0][1]);
    }
    // ��ʧ�������������Ǽ��ʬ��
    $res_k   = array();
    $field_k = array();
    $rows_k  = array();
    $sum1 = '1560';
    $sum2 = '00';
    $sum3 = '34';
    $query_k = sprintf("select SUM(rep_cri) from financial_report_cal where rep_ymd>=%d and rep_ymd<=%d and rep_summary1='%s' and rep_summary2='%s' and rep_gin='%s'", $str_ym, $end_ym, $sum1, $sum2, $sum3);
    if ($rows_k=getResultWithField2($query_k, $field_k, $res_k) <= 0) {
        $mae_sho_zei_kin = 0;
    } else {
        $mae_sho_zei_kin = $res_k[0][0];
    }
    // ��ʧ�������������Ǽ��ʬ�� 10��12��ʬ
    $res   = array();
    $field = array();
    $rows  = array();
    $query = sprintf("select SUM(rep_de), SUM(rep_cr) from financial_report_month where rep_ymd>=%d and rep_ymd<=%d and rep_summary1='%s' and rep_summary2='%s'", $ss_str_ym, $end_ym, $sum1, $sum2);
    if ($rows=getResultWithField2($query, $field, $res) <= 0) {
        $mae_sho_zei_kin = $mae_sho_zei_kin;
    } else {
        $mae_sho_zei_kin = $mae_sho_zei_kin + ($res[0][0] - $res[0][1]);
    }
    // ��ʧ������ ���
    $karibara_zei_total = -($karibara_zei_kin+$karibara_yunyu_zei_kin+$mae_sho_zei_kin);
    
    // ���������Ƿ׻�
    // ������������
    $res_k   = array();
    $field_k = array();
    $rows_k  = array();
    $sum1 = '3227';
    $sum2 = '00';
    $sum3 = '34';
    $query_k = sprintf("select SUM(rep_cri) from financial_report_cal where rep_ymd>=%d and rep_ymd<=%d and rep_summary1='%s' and rep_summary2='%s' and rep_gin='%s'", $str_ym, $end_ym, $sum1, $sum2, $sum3);
    if ($rows_k=getResultWithField2($query_k, $field_k, $res_k) <= 0) {
        $kariuke_sho_zei_kin = 0;
    } else {
        $kariuke_sho_zei_kin = $res_k[0][0];
    }
    // ������������ 10��12��ʬ
    $res   = array();
    $field = array();
    $rows  = array();
    $query = sprintf("select SUM(rep_de), SUM(rep_cr) from financial_report_month where rep_ymd>=%d and rep_ymd<=%d and rep_summary1='%s' and rep_summary2='%s'", $ss_str_ym, $end_ym, $sum1, $sum2);
    if ($rows=getResultWithField2($query, $field, $res) <= 0) {
        $kariuke_sho_zei_kin = $kariuke_sho_zei_kin;
    } else {
        $kariuke_sho_zei_kin = $kariuke_sho_zei_kin + ($res[0][1] - $res[0][0]);
    }
} else {                            // 6�Ϸ׻���ˡ���㤦
    $karibara_zei_kin       = $kari00_kin;
    //$karibara_yunyu_zei_kin = $kari20_kin;
    $mae_sho_zei_kin        = $mae_zei_kin;
    
    $karibara_zei_total     = -($karibara_zei_kin+$mae_sho_zei_kin);
    
    $kariuke_sho_zei_kin    = $kariuke_zei_kin;
}


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

// ��ݶ������ TOP10�μ���
$kaikake_top     = array();
$kaikake_top_kin = 0;
for ($i = 1; $i < 11; $i++) {
    $res_k   = array();
    $field_k = array();
    $rows_k  = array();
    $sum1 = '01';
    $sum2 = $i;
    $query_k = sprintf("select rep_summary1,rep_cri from financial_report_cal where rep_ymd=%d and rep_summary2='%s' and rep_gin='%s'", $end_ym, $sum1, $sum2);
    if ($rows_k=getResultWithField2($query_k, $field_k, $res_k) <= 0) {
        $kaikake_top_code = 0;
    } else {
        $kaikake_top_code    = $res_k[0][0];
        $kaikake_top[$i][2]  = $res_k[0][1];       // ���
    
        $res_m   = array();
        $field_m = array();
        $rows_m  = array();
        $query_m = sprintf("select name, address1, address2 from vendor_master WHERE vendor='%s'", $kaikake_top_code);
        if ($rows_m=getResultWithField2($query_m, $field_m, $res_m) <= 0) {
            $kaikake_top[$i][0]    = '';    // ȯ����̾
            $kaikake_top[$i][1]    = '';    // ����
        } else {
            $kaikake_top[$i][0]    = $res_m[0][0];
            if ($kaikake_top_code=='01298') {
                $res_m[0][1] = '���ڸ����Եܻ�';
            } elseif ($kaikake_top_code=='01299') {
                $res_m[0][1] = '��븩��Ω��';
            } elseif ($kaikake_top_code=='00958') {
                $res_m[0][1] = '�����ʸ����';
            } elseif ($kaikake_top_code=='00642') {
                $res_m[0][1] = '���ո�������';
            }
            $kaikake_top[$i][1]    = preg_replace("/( |��)/", "", $res_m[0][1] . $res_m[0][2]);
        }
    }
    $kaikake_top_kin = $kaikake_top_kin + $kaikake_top[$i][2];
}

// ��ݶ������ ����¾�׻�
$kaikake_top_sonota_kin = $kaikake_total_kin - $kaikake_top_kin;

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

// ̤ʧ������� TOP10�μ���
$miharai_top     = array();
$miharai_top_kin = 0;
for ($i = 1; $i < 11; $i++) {
    $res_k   = array();
    $field_k = array();
    $rows_k  = array();
    $sum1 = 'MIHAR';
    $sum2 = $i;
    $query_k = sprintf("select rep_summary2,rep_gin,rep_cri from financial_report_cal where rep_ymd=%d and rep_summary1='%s' and rep_de=%d", $end_ym, $sum1, $sum2);
    if ($rows_k=getResultWithField2($query_k, $field_k, $res_k) <= 0) {
        $miharai_top[$i][0] = '';
        $miharai_top[$i][1] = '';
        $miharai_top[$i][2] = 0;
        
    } else {
        $miharai_top[$i][0]  = $res_k[0][0];
        $miharai_top[$i][1]  = $res_k[0][1];
        $miharai_top[$i][2]  = $res_k[0][2];
    
        if ($miharai_top[$i][0]=='������ҿ��¾���') {
            $miharai_top[$i][1] = '���ڸ����»Գ���Į������';
        } elseif ($miharai_top[$i][0]=='ä̦���ȡʳ����̴���ĶȽ�') {
            $miharai_top[$i][1] = '���ϸ����ӻԿ�ˬĮ��������';
        } elseif ($miharai_top[$i][0]=='��˭���ߡʳ���') {
            $miharai_top[$i][1] = '���ڸ����ԵܻԲ���Į���������ϣ�����';
        } elseif ($miharai_top[$i][0]=='�ʳ��˥����α��Եܻ�Ź') {
            $miharai_top[$i][1] = '���ڸ����Եܻ�����Į���������ݣ�';
        } elseif ($miharai_top[$i][0]=='������һ����ӣƣӱĶ�����') {
            $miharai_top[$i][1] = '����Թ������2-16-2������̿����ӥ�12��';
        } elseif ($miharai_top[$i][0]=='������ҥߥ���') {
            $miharai_top[$i][1] = '�����ʸ������2-5-1���Ķ��ե������ȥӥ�';
        } elseif ($miharai_top[$i][0]=='�����Ź��������') {
            $miharai_top[$i][1] = '���ڸ����Եܻ�����Į��������';
        } elseif ($miharai_top[$i][0]=='������ҥ��ݥ���') {
            $miharai_top[$i][1] = '���������������������磱���ܣ��֣�����';
        } elseif ($miharai_top[$i][0]=='�������ȡʳ���') {
            $miharai_top[$i][1] = '��������ͻ��ḫ��𲬣��ݣ��ݣ���';
        } elseif ($miharai_top[$i][0]=='������ҿ����������') {
            $miharai_top[$i][1] = '���ڸ����ԵܻԸ湬����Į�������ݣ���';
        } elseif ($miharai_top[$i][0]=='��ͭ�˾�����������') {
            $miharai_top[$i][1] = '���ڸ�������Ի�ȣ�������';
        } elseif ($miharai_top[$i][0]=='���ĥޥ���ġ���������') {
            $miharai_top[$i][1] = '�������������죱���ܣ����ݣ�';
        } elseif ($miharai_top[$i][0]=='�ʳ��˥ʥ���ʥ�ޥ��ʥ꡼������') {
            $miharai_top[$i][1] = '���θ���������٥���Į���ݣ����ݣ���';
        } elseif ($miharai_top[$i][0]=='������Ҷ�Ω�ŵ�') {
            $miharai_top[$i][1] = '���ڸ�������Ի�ȣ�������';
        } elseif ($miharai_top[$i][0]=='��ͭ�˲���湩��') {
            $miharai_top[$i][1] = '���ڸ����Եܻ�����Į�������ݣ�';
        } elseif ($miharai_top[$i][0]=='����������') {
            $miharai_top[$i][1] = '�����ʸ�����ܶ������ܣ����֣�����';
        } elseif ($miharai_top[$i][0]=='ͭ�²�Һ�������') {
            $miharai_top[$i][0] = '������ҥߥ���';
            $miharai_top[$i][1] = '�����ʸ������2-5-1���Ķ��ե������ȥӥ�';
        }
    }
    $miharai_top_kin = $miharai_top_kin + $miharai_top[$i][2];
}

// ̤ʧ������� ����¾�׻�
$miharai_top_sonota_kin = $miharai_total_kin - $miharai_top_kin;

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

// �¤��η׻�
// ����������
$res_k   = array();
$field_k = array();
$rows_k  = array();
$sum1 = '3222';
$sum2 = '11';
$query_k = sprintf("select rep_cri, rep_de, rep_cr from financial_report_cal where rep_ymd=%d and rep_summary1='%s' and rep_summary2='%s'", $nk_ki, $sum1, $sum2);
if ($rows_k=getResultWithField2($query_k, $field_k, $res_k) <= 0) {
    $gen_shotoku_kishu = 0;
} else {
    $gen_shotoku_kishu = $res_k[0][0];
}
$res   = array();
$field = array();
$rows  = array();
$query = sprintf("select SUM(rep_de), SUM(rep_cr) from financial_report_month where rep_ymd>=%d and rep_ymd<=%d and rep_summary1='%s' and rep_summary2='%s'", $str_ym, $end_ym, $sum1, $sum2);
if ($rows=getResultWithField2($query, $field, $res) <= 0) {
    $gen_shotoku_kin = $gen_shotoku_kishu;
} else {
    $gen_shotoku_kin = -($gen_shotoku_kishu + ($res[0][0] - $res[0][1]));
}

// ������̱��
$res_k   = array();
$field_k = array();
$rows_k  = array();
$sum1 = '3222';
$sum2 = '12';
$query_k = sprintf("select rep_cri, rep_de, rep_cr from financial_report_cal where rep_ymd=%d and rep_summary1='%s' and rep_summary2='%s'", $nk_ki, $sum1, $sum2);
if ($rows_k=getResultWithField2($query_k, $field_k, $res_k) <= 0) {
    $gen_jyu_kishu = 0;
} else {
    $gen_jyu_kishu = $res_k[0][0];
}
$res   = array();
$field = array();
$rows  = array();
$query = sprintf("select SUM(rep_de), SUM(rep_cr) from financial_report_month where rep_ymd>=%d and rep_ymd<=%d and rep_summary1='%s' and rep_summary2='%s'", $str_ym, $end_ym, $sum1, $sum2);
if ($rows=getResultWithField2($query, $field, $res) <= 0) {
    $gen_jyu_kin = $gen_jyu_kishu;
} else {
    $gen_jyu_kin = -($gen_jyu_kishu + ($res[0][0] - $res[0][1]));
}

// ���ݸ���
$res_k   = array();
$field_k = array();
$rows_k  = array();
$sum1 = '3222';
$sum2 = '21';
$query_k = sprintf("select rep_cri, rep_de, rep_cr from financial_report_cal where rep_ymd=%d and rep_summary1='%s' and rep_summary2='%s'", $nk_ki, $sum1, $sum2);
if ($rows_k=getResultWithField2($query_k, $field_k, $res_k) <= 0) {
    $ken_hoken_kishu = 0;
} else {
    $ken_hoken_kishu = $res_k[0][0];
}
$res   = array();
$field = array();
$rows  = array();
$query = sprintf("select SUM(rep_de), SUM(rep_cr) from financial_report_month where rep_ymd>=%d and rep_ymd<=%d and rep_summary1='%s' and rep_summary2='%s'", $str_ym, $end_ym, $sum1, $sum2);
if ($rows=getResultWithField2($query, $field, $res) <= 0) {
    $ken_hoken_kin = $ken_hoken_kishu;
} else {
    $ken_hoken_kin = -($ken_hoken_kishu + ($res[0][0] - $res[0][1]));
}

// ����ǯ���ݸ���
$res_k   = array();
$field_k = array();
$rows_k  = array();
$sum1 = '3222';
$sum2 = '22';
$query_k = sprintf("select rep_cri, rep_de, rep_cr from financial_report_cal where rep_ymd=%d and rep_summary1='%s' and rep_summary2='%s'", $nk_ki, $sum1, $sum2);
if ($rows_k=getResultWithField2($query_k, $field_k, $res_k) <= 0) {
    $kou_hoken_kishu = 0;
} else {
    $kou_hoken_kishu = $res_k[0][0];
}
$res   = array();
$field = array();
$rows  = array();
$query = sprintf("select SUM(rep_de), SUM(rep_cr) from financial_report_month where rep_ymd>=%d and rep_ymd<=%d and rep_summary1='%s' and rep_summary2='%s'", $str_ym, $end_ym, $sum1, $sum2);
if ($rows=getResultWithField2($query, $field, $res) <= 0) {
    $kou_hoken_kin = $kou_hoken_kishu;
} else {
    $kou_hoken_kin = -($kou_hoken_kishu + ($res[0][0] - $res[0][1]));
}

// �¤�� ����¾
$res_k   = array();
$field_k = array();
$rows_k  = array();
$sum1 = '3222';
$sum2 = '90';
$query_k = sprintf("select rep_cri, rep_de, rep_cr from financial_report_cal where rep_ymd=%d and rep_summary1='%s' and rep_summary2='%s'", $nk_ki, $sum1, $sum2);
if ($rows_k=getResultWithField2($query_k, $field_k, $res_k) <= 0) {
    $azu_sonota_kishu = 0;
} else {
    $azu_sonota_kishu = $res_k[0][0];
}
$res   = array();
$field = array();
$rows  = array();
$query = sprintf("select SUM(rep_de), SUM(rep_cr) from financial_report_month where rep_ymd>=%d and rep_ymd<=%d and rep_summary1='%s' and rep_summary2='%s'", $str_ym, $end_ym, $sum1, $sum2);
if ($rows=getResultWithField2($query, $field, $res) <= 0) {
    $azu_sonota_kin = $azu_sonota_kishu;
} else {
    $azu_sonota_kin = -($azu_sonota_kishu + ($res[0][0] - $res[0][1]));
}

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

// ˡ���ǡ���̱�ǵڤӻ����Ǥ����� ��פη׻�
$hojin_uchi_total_kin = $hojin_jyumin_zei_kin + $jigyo_zei_kin;

// eca��ˡ���ǡ���̱�ǵڤӻ�����
$eca_hojin_zeito_total_kin = $hojin_jyumin_zei_kin + $jigyo_zei_kin;

// ˡ�������ʹ��ǡ��¶���©�����Ф��븻�������ǳ�
$res_k   = array();
$field_k = array();
$rows_k  = array();
$sum1 = '9401';
$sum2 = '20';
$query_k = sprintf("select rep_cri, rep_de, rep_cr from financial_report_cal where rep_ymd=%d and rep_summary1='%s' and rep_summary2='%s'", $nk_ki, $sum1, $sum2);
if ($rows_k=getResultWithField2($query_k, $field_k, $res_k) <= 0) {
    $gensen_shotoku_kishu = 0;
} else {
    $gensen_shotoku_kishu = $res_k[0][0];
}
$res   = array();
$field = array();
$rows  = array();
$query = sprintf("select SUM(rep_de), SUM(rep_cr) from financial_report_month where rep_ymd>=%d and rep_ymd<=%d and rep_summary1='%s' and rep_summary2='%s'", $str_ym, $end_ym, $sum1, $sum2);
if ($rows=getResultWithField2($query, $field, $res) <= 0) {
    $gensen_shotoku_kin = $gensen_shotoku_kishu;
} else {
    $gensen_shotoku_kin = $gensen_shotoku_kishu + ($res[0][0] - $res[0][1]);
}

// ����ˡ���ǽ�̱�ǻ����ǰ�����
$toki_hojin_jigyo = $hojin_uchi_total_kin - $gensen_shotoku_kin;

// ̤ʧˡ������ ����
$res_k   = array();
$field_k = array();
$rows_k  = array();
$sum1 = '3211';
$sum2 = '00';
$query_k = sprintf("select rep_cri, rep_de, rep_cr from financial_report_cal where rep_ymd=%d and rep_summary1='%s' and rep_summary2='%s'", $nk_ki, $sum1, $sum2);
if ($rows_k=getResultWithField2($query_k, $field_k, $res_k) <= 0) {
    $miharai_hozei_kishu = 0;
} else {
    $miharai_hozei_kishu = -$res_k[0][0];
}

// ̤ʧˡ������
$res   = array();
$field = array();
$rows  = array();
$miharai_hozei_kin = 0;
$note = '̤ʧˡ������';
$query = sprintf("SELECT kin FROM profit_loss_bs_history WHERE pl_bs_ym=%d and note='%s'", $yyyymm, $note);
if ($rows=getResultWithField2($query, $field, $res) <= 0) {
    $miharai_hozei_kin = 0;
} else {
    $miharai_hozei_kin = $res[0][0];
}

$miharai_hozei_settei = $toki_hojin_jigyo;
$miharai_hozei_shiha  = $miharai_hozei_kishu + $miharai_hozei_settei - $miharai_hozei_kin;

// ̤ʧ����
$res   = array();
$field = array();
$rows  = array();
$miharai_hiyo_kin = 0;
$note = '̤ʧ����';
$query = sprintf("SELECT kin FROM profit_loss_bs_history WHERE pl_bs_ym=%d and note='%s'", $yyyymm, $note);
if ($rows=getResultWithField2($query, $field, $res) <= 0) {
    $miharai_hiyo_kin = 0;
} else {
    $miharai_hiyo_kin = $res[0][0];
}

// �¤��
$res   = array();
$field = array();
$rows  = array();
$azukari_kin = 0;
$note = '�¤��';
$query = sprintf("SELECT kin FROM profit_loss_bs_history WHERE pl_bs_ym=%d and note='%s'", $yyyymm, $note);
if ($rows=getResultWithField2($query, $field, $res) <= 0) {
    $azukari_kin = 0;
} else {
    $azukari_kin = $res[0][0];
}

// ��Ϳ������
$res   = array();
$field = array();
$rows  = array();
$syoyo_hikiate_kin = 0;
$note = '��Ϳ������';
$query = sprintf("SELECT kin FROM profit_loss_bs_history WHERE pl_bs_ym=%d and note='%s'", $yyyymm, $note);
if ($rows=getResultWithField2($query, $field, $res) <= 0) {
    $syoyo_hikiate_kin = 0;
} else {
    $syoyo_hikiate_kin = $res[0][0];
}

// �࿦���հ�����
$res   = array();
$field = array();
$rows  = array();
$taisyoku_hikiate_kin = 0;
$note = '�࿦���հ�����';
$query = sprintf("SELECT kin FROM profit_loss_bs_history WHERE pl_bs_ym=%d and note='%s'", $yyyymm, $note);
if ($rows=getResultWithField2($query, $field, $res) <= 0) {
    $taisyoku_hikiate_kin = 0;
} else {
    $taisyoku_hikiate_kin = $res[0][0];
}

// �࿦���հ����� ����
$res_k   = array();
$field_k = array();
$rows_k  = array();
$sum1 = '3302';
$sum2 = '00';
$query_k = sprintf("select rep_cri, rep_de, rep_cr from financial_report_cal where rep_ymd=%d and rep_summary1='%s' and rep_summary2='%s'", $nk_ki, $sum1, $sum2);
if ($rows_k=getResultWithField2($query_k, $field_k, $res_k) <= 0) {
    $tai_hiki_kishu = 0;
} else {
    $tai_hiki_kishu = -$res_k[0][0];
}
$res   = array();
$field = array();
$rows  = array();
$query = sprintf("select SUM(rep_de), SUM(rep_cr) from financial_report_month where rep_ymd>=%d and rep_ymd<=%d and rep_summary1='%s'", $str_ym, $end_ym, $sum1);
if ($rows=getResultWithField2($query, $field, $res) <= 0) {
    $tai_hiki_kin_zou = 0;
    $tai_hiki_kin_gen = 0;
} else {
    $tai_hiki_kin_zou = $res[0][0];
    $tai_hiki_kin_gen = $res[0][1];
}

// �࿦���հ����� ��Ū����
$res   = array();
$field = array();
$rows  = array();
$sum3 = '12';
$query = sprintf("select SUM(rep_cri) from financial_report_cal where rep_ymd>=%d and rep_ymd<=%d and rep_summary1='%s' and rep_gin='%s'", $str_ym, $end_ym, $sum1, $sum3);
if ($rows=getResultWithField2($query, $field, $res) <= 0) {
    $tai_hiki_kin_moku = 0;
} else {
    $tai_hiki_kin_moku = $res[0][0];
}

// �࿦���հ����� ����¾
$res   = array();
$field = array();
$rows  = array();
$sum3 = '12';
$query = sprintf("select SUM(rep_cri) from financial_report_cal where rep_ymd>=%d and rep_ymd<=%d and rep_summary1='%s' and rep_gin<>'%s'", $str_ym, $end_ym, $sum1, $sum3);
if ($rows=getResultWithField2($query, $field, $res) <= 0) {
    $tai_hiki_kin_sonota = 0;
} else {
    $tai_hiki_kin_sonota = $res[0][0];
}

// ���ܶ�
$res   = array();
$field = array();
$rows  = array();
$shihon_kin = 0;
$note = '���ܶ�';
$query = sprintf("SELECT kin FROM profit_loss_bs_history WHERE pl_bs_ym=%d and note='%s'", $yyyymm, $note);
if ($rows=getResultWithField2($query, $field, $res) <= 0) {
    $shihon_kin = 0;
} else {
    $shihon_kin = $res[0][0];
}

// ���ܶ��
$shihon_total_kin = $shihon_kin;


// ���ܶ� ����
$res_k   = array();
$field_k = array();
$rows_k  = array();
$sum1 = '4101';
$sum2 = '00';
$query_k = sprintf("select rep_cri, rep_de, rep_cr from financial_report_cal where rep_ymd=%d and rep_summary1='%s' and rep_summary2='%s'", $nk_ki, $sum1, $sum2);
if ($rows_k=getResultWithField2($query_k, $field_k, $res_k) <= 0) {
    $shihon_kin_kishu = 0;
} else {
    $shihon_kin_kishu = -$res_k[0][0];
}
$res   = array();
$field = array();
$rows  = array();
$query = sprintf("select SUM(rep_de), SUM(rep_cr) from financial_report_month where rep_ymd>=%d and rep_ymd<=%d and rep_summary1='%s'", $str_ym, $end_ym, $sum1);
if ($rows=getResultWithField2($query, $field, $res) <= 0) {
    $shihon_kin_zou = 0;
    $shihon_kin_gen = 0;
} else {
    $shihon_kin_zou = $res[0][0];
    $shihon_kin_gen = $res[0][1];
}

// ���ܽ�����
$res   = array();
$field = array();
$rows  = array();
$shihon_jyunbi_kin = 0;
$note = '���ܽ�����';
$query = sprintf("SELECT kin FROM profit_loss_bs_history WHERE pl_bs_ym=%d and note='%s'", $yyyymm, $note);
if ($rows=getResultWithField2($query, $field, $res) <= 0) {
    $shihon_jyunbi_kin = 0;
} else {
    $shihon_jyunbi_kin = $res[0][0];
}


// ���ܽ����� ����
$res_k   = array();
$field_k = array();
$rows_k  = array();
$sum1 = '4102';
$sum2 = '00';
$query_k = sprintf("select rep_cri, rep_de, rep_cr from financial_report_cal where rep_ymd=%d and rep_summary1='%s' and rep_summary2='%s'", $nk_ki, $sum1, $sum2);
if ($rows_k=getResultWithField2($query_k, $field_k, $res_k) <= 0) {
    $shihon_jyunbi_kishu = 0;
} else {
    $shihon_jyunbi_kishu = -$res_k[0][0];
}
$res   = array();
$field = array();
$rows  = array();
$query = sprintf("select SUM(rep_de), SUM(rep_cr) from financial_report_month where rep_ymd>=%d and rep_ymd<=%d and rep_summary1='%s'", $str_ym, $end_ym, $sum1);
if ($rows=getResultWithField2($query, $field, $res) <= 0) {
    $shihon_jyunbi_zou = 0;
    $shihon_jyunbi_gen = 0;
} else {
    $shihon_jyunbi_zou = $res[0][0];
    $shihon_jyunbi_gen = $res[0][1];
}

// ����¾���ܾ�;��
$res   = array();
$field = array();
$rows  = array();
$sonota_shihon_jyoyo_kin = 0;
$note = '����¾���ܾ�;��';
$query = sprintf("SELECT kin FROM profit_loss_bs_history WHERE pl_bs_ym=%d and note='%s'", $yyyymm, $note);
if ($rows=getResultWithField2($query, $field, $res) <= 0) {
    $sonota_shihon_jyoyo_kin = 0;
} else {
    $sonota_shihon_jyoyo_kin = $res[0][0];
}

// ����¾���ܾ�;�� ����
$res_k   = array();
$field_k = array();
$rows_k  = array();
$sum1 = '4103';
$sum2 = '00';
$query_k = sprintf("select rep_cri, rep_de, rep_cr from financial_report_cal where rep_ymd=%d and rep_summary1='%s' and rep_summary2='%s'", $nk_ki, $sum1, $sum2);
if ($rows_k=getResultWithField2($query_k, $field_k, $res_k) <= 0) {
    $sonota_shihon_jyoyo_kishu = 0;
} else {
    $sonota_shihon_jyoyo_kishu = -$res_k[0][0];
}
$res   = array();
$field = array();
$rows  = array();
$query = sprintf("select SUM(rep_de), SUM(rep_cr) from financial_report_month where rep_ymd>=%d and rep_ymd<=%d and rep_summary1='%s'", $str_ym, $end_ym, $sum1);
if ($rows=getResultWithField2($query, $field, $res) <= 0) {
    $sonota_shihon_jyoyo_zou = 0;
    $sonota_shihon_jyoyo_gen = 0;
} else {
    $sonota_shihon_jyoyo_zou = $res[0][0];
    $sonota_shihon_jyoyo_gen = $res[0][1];
}

// ����¾���׾�;��
$res   = array();
$field = array();
$rows  = array();
$tai_sonota_rieki_jyoyo_kin = 0;
$note = '����¾���׾�;��';
$query = sprintf("SELECT kin FROM profit_loss_bs_history WHERE pl_bs_ym=%d and note='%s'", $yyyymm, $note);
if ($rows=getResultWithField2($query, $field, $res) <= 0) {
    $tai_sonota_rieki_jyoyo_kin = 0;
} else {
    $tai_sonota_rieki_jyoyo_kin = $res[0][0];
}

// ����¾���׾�;�� ����
$res_k   = array();
$field_k = array();
$rows_k  = array();
$sum1 = '4213';
$sum2 = '00';
$query_k = sprintf("select rep_cri, rep_de, rep_cr from financial_report_cal where rep_ymd=%d and rep_summary1='%s' and rep_summary2='%s'", $nk_ki, $sum1, $sum2);
if ($rows_k=getResultWithField2($query_k, $field_k, $res_k) <= 0) {
    $sonota_rieki_jyoyo_kishu = 0;
} else {
    $sonota_rieki_jyoyo_kishu = -$res_k[0][0];
}
$res   = array();
$field = array();
$rows  = array();
$query = sprintf("select SUM(rep_de), SUM(rep_cr) from financial_report_month where rep_ymd>=%d and rep_ymd<=%d and rep_summary1='%s'", $str_ym, $end_ym, $sum1);
if ($rows=getResultWithField2($query, $field, $res) <= 0) {
    $sonota_rieki_jyoyo_zou = 0;
    $sonota_rieki_jyoyo_gen = 0;
} else {
    $sonota_rieki_jyoyo_zou = $res[0][0];
    $sonota_rieki_jyoyo_gen = $res[0][1];
}

// ���ܶ�ڤӾ�;������� ��
$shihon_jyoyo_total = $shihon_total_kin + $shihon_jyunbi_kin + $sonota_shihon_jyoyo_kin + $tai_sonota_rieki_jyoyo_kin;
$shihon_jyoyo_kishu = $shihon_kin_kishu + $shihon_jyunbi_kishu + $sonota_shihon_jyoyo_kishu + $sonota_rieki_jyoyo_kishu;
$shihon_jyoyo_zou   = $shihon_kin_zou + $shihon_jyunbi_zou + $sonota_shihon_jyoyo_zou + $sonota_rieki_jyoyo_zou;
$shihon_jyoyo_gen   = $shihon_kin_gen + $shihon_jyunbi_gen + $sonota_shihon_jyoyo_gen + $sonota_rieki_jyoyo_gen;

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

// ���Ǹ��ݹ��
// ������
$res   = array();
$field = array();
$rows  = array();
$han_jigyo_kin = 0;
$note  = '��׻�����';
$query = sprintf("select SUM(kin) from profit_loss_keihi_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='%s'", $str_ym, $end_ym, $note);
if ($rows=getResultWithField2($query, $field, $res) <= 0) {
    $all_jigyo_kin = 0;
} else {
    $all_jigyo_kin = $res[0][0];
}
// ���Ǹ���
$res   = array();
$field = array();
$rows  = array();
$han_zeikoka_kin = 0;
$note  = '��׽��Ǹ���';
$query = sprintf("select SUM(kin) from profit_loss_keihi_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='%s'", $str_ym, $end_ym, $note);
if ($rows=getResultWithField2($query, $field, $res) <= 0) {
    $all_zeikoka_kin = 0;
} else {
    $all_zeikoka_kin = $res[0][0];
}

// �δ�����Ǹ��ݹ�פη׻�
$all_zeikoka_total_kin = $all_jigyo_kin + $all_zeikoka_kin;

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

$ym4s = substr($str_ym, 2, 4);
$ym4e = substr($end_ym, 2, 4);

// ���Ǹ��� �������
// ��¤����
$res   = array();
$field = array();
$rows  = array();
$sum1 = '7521';
$sum2 = '20';
$query = sprintf("SELECT CASE WHEN sum(act_monthly) IS NULL THEN 0 WHEN sum(act_monthly) = 0 THEN 0 ELSE sum(act_monthly) END FROM act_summary where act_yymm>=%d and act_yymm<=%d and actcod='%s' and aucod='%s'", $ym4s, $ym4e, $sum1, $sum2);
if ($rows=getResultWithField2($query, $field, $res) <= 0) {
    $kotei_zei_sei_kin = 0;
} else {
    $kotei_zei_sei_kin = $res[0][0];
}
// �δ���
$res   = array();
$field = array();
$rows  = array();
$sum1 = '7521';
$sum2 = '20';
$query = sprintf("SELECT CASE WHEN sum(act_monthly) IS NULL THEN 0 WHEN sum(act_monthly) = 0 THEN 0 ELSE sum(act_monthly) END FROM act_sga_summary where act_yymm>=%d and act_yymm<=%d and actcod='%s' and aucod='%s'", $ym4s, $ym4e, $sum1, $sum2);
if ($rows=getResultWithField2($query, $field, $res) <= 0) {
    $kotei_zei_han_kin = 0;
} else {
    $kotei_zei_han_kin = $res[0][0];
}
// ���
$kotei_zei_total_kin = $kotei_zei_sei_kin + $kotei_zei_han_kin;

// ���Ǹ��� ������
// ��¤����
$res   = array();
$field = array();
$rows  = array();
$sum1 = '7521';
$sum2 = '10';
$query = sprintf("SELECT CASE WHEN sum(act_monthly) IS NULL THEN 0 WHEN sum(act_monthly) = 0 THEN 0 ELSE sum(act_monthly) END FROM act_summary where act_yymm>=%d and act_yymm<=%d and actcod='%s' and aucod='%s'", $ym4s, $ym4e, $sum1, $sum2);
if ($rows=getResultWithField2($query, $field, $res) <= 0) {
    $inshi_zei_sei_kin = 0;
} else {
    $inshi_zei_sei_kin = $res[0][0];
}
// �δ���
$res   = array();
$field = array();
$rows  = array();
$sum1 = '7521';
$sum2 = '10';
$query = sprintf("SELECT CASE WHEN sum(act_monthly) IS NULL THEN 0 WHEN sum(act_monthly) = 0 THEN 0 ELSE sum(act_monthly) END FROM act_sga_summary where act_yymm>=%d and act_yymm<=%d and actcod='%s' and aucod='%s'", $ym4s, $ym4e, $sum1, $sum2);
if ($rows=getResultWithField2($query, $field, $res) <= 0) {
    $inshi_zei_han_kin = 0;
} else {
    $inshi_zei_han_kin = $res[0][0];
}
// ���
$inshi_zei_total_kin = $inshi_zei_sei_kin + $inshi_zei_han_kin;

// ���Ǹ��� ��Ͽ�ȵ��ǡʤ���¾��
// ��¤����
$res   = array();
$field = array();
$rows  = array();
$sum1 = '7521';
$sum2 = '90';
$query = sprintf("SELECT CASE WHEN sum(act_monthly) IS NULL THEN 0 WHEN sum(act_monthly) = 0 THEN 0 ELSE sum(act_monthly) END FROM act_summary where act_yymm>=%d and act_yymm<=%d and actcod='%s' and aucod='%s'", $ym4s, $ym4e, $sum1, $sum2);
if ($rows=getResultWithField2($query, $field, $res) <= 0) {
    $touroku_zei_sei_kin = 0;
} else {
    $touroku_zei_sei_kin = $res[0][0];
}
// �δ���
$res   = array();
$field = array();
$rows  = array();
$sum1 = '7521';
$sum2 = '90';
$query = sprintf("SELECT CASE WHEN sum(act_monthly) IS NULL THEN 0 WHEN sum(act_monthly) = 0 THEN 0 ELSE sum(act_monthly) END FROM act_sga_summary where act_yymm>=%d and act_yymm<=%d and actcod='%s' and aucod='%s'", $ym4s, $ym4e, $sum1, $sum2);
if ($rows=getResultWithField2($query, $field, $res) <= 0) {
    $touroku_zei_han_kin = 0;
} else {
    $touroku_zei_han_kin = $res[0][0];
}
// ���
$touroku_zei_total_kin = $touroku_zei_sei_kin + $touroku_zei_han_kin;

// ���Ǹ��� ���
$shozei_sei_total = $kotei_zei_sei_kin + $inshi_zei_sei_kin + $touroku_zei_sei_kin;
$shozei_han_total = $kotei_zei_han_kin + $inshi_zei_han_kin + $touroku_zei_han_kin;
$shozei_total     = $kotei_zei_total_kin + $inshi_zei_total_kin + $touroku_zei_total_kin;

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
td.winboxt {
    border-style:           solid;
    border-width:           1px;
    border-top-color:       #FFFFFF;
    border-left-color:      #FFFFFF;
    border-right-color:     #bdaa90;
    border-bottom-color:    #bdaa90;
    writing-mode       :    tb-rl;
}
-->
</style>
</head>
<body>
<?= $menu->out_title_border() ?>
        <?php
            //  bgcolor='#ceffce' ����
            //  bgcolor='#ffffc6' ��������
            //  bgcolor='#d6d3ce' Win ���쥤
        ?>
    <!--------------- ����������ʸ��ɽ��ɽ������ -------------------->
        <table bgcolor='#ffffff' align='left' cellspacing='0' cellpadding='10'>
            <tr><td> <!----------- ���ߡ�(�ǥ�������) ------------>
        <div class='pt11b'>1.���⤪����¶������</div>
        <table class='winbox_field' width='100%' align='center' bgcolor='black' cellspacing='1' cellpadding='5'>
            <THEAD>
                <!-- �ơ��֥� �إå�����ɽ�� -->
                <tr>
                    <td class='winboxt' nowrap bgcolor='#ffffff' rowspan='2' align='center'><div class='pt11b'>����</div></td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center'><div class='pt11b'>����</div></td>
                    <td class='winbox' nowrap bgcolor='#ffffff' colspan='4'><div class='pt11b' align='center'>����</div></td>
                    <td class='winbox' nowrap bgcolor='#ffffff'><div class='pt11b' align='center'>���</div></td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff'><div class='pt11b' align='center'>����</div></td>
                    <td class='winbox' nowrap bgcolor='#ffffff' colspan='4'><div class='pt11b' align='center'>����Ĺ�</div></td>
                    <td class='winbox' nowrap bgcolor='#E0FFFF' align='right'>
                        <div class='pt11b'><?= number_format($genkin_kin) ?></div>
                    </td>
                </tr>
            </THEAD>
            <TFOOT>
                <!-- ���ߤϥեå����ϲ���ʤ� -->
            </TFOOT>
            <TBODY>
                <tr>
                    <td class='winboxt' nowrap bgcolor='#ffffff' rowspan='5' align='center'><div class='pt11b'>�¶�����</div></td>
                    <td class='winbox' nowrap bgcolor='#ffffff'><div class='pt11b' align='center'>���̾</div></td>
                    <td class='winbox' nowrap bgcolor='#ffffff'><div class='pt11b' align='center'>��Ź̾</div></td>
                    <td class='winbox' nowrap bgcolor='#ffffff'><div class='pt11b' align='center'>�����¶�</div></td>
                    <td class='winbox' nowrap bgcolor='#ffffff'><div class='pt11b' align='center'>�����¶�</div></td>
                    <td class='winbox' nowrap bgcolor='#ffffff'><div class='pt11b' align='center'>����¶�</div></td>
                    <td class='winbox' nowrap bgcolor='#ffffff'><div class='pt11b' align='center'>��</div></td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff'><div class='pt11b' align='center'>��ɩUFJ</div></td>
                    <td class='winbox' nowrap bgcolor='#ffffff'><div class='pt11b' align='center'>�Ӿ�</div></td>
                    <td class='winbox' nowrap bgcolor='#E0FFFF' align='right'>
                        <div class='pt11b'><?= number_format($ufj_futu_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#f5f5f5' align='right'>
                        <div class='pt11b'>-</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#E0FFFF' align='right'>
                        <div class='pt11b'><?= number_format($ufj_teiki_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#E0FFFF' align='right'>
                        <div class='pt11b'><?= number_format($ufj_total_kin) ?></div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff'><div class='pt11b' align='center'>��ɩUFJ����</div></td>
                    <td class='winbox' nowrap bgcolor='#ffffff'><div class='pt11b' align='center'>��Ź</div></td>
                    <td class='winbox' nowrap bgcolor='#E0FFFF' align='right'>
                        <div class='pt11b'><?= number_format($ufjs_futu_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#f5f5f5' align='right'>
                        <div class='pt11b'>-</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#E0FFFF' align='right'>
                        <div class='pt11b'><?= number_format($ufjs_teiki_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#E0FFFF' align='right'>
                        <div class='pt11b'><?= number_format($ufjs_total_kin) ?></div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff'><div class='pt11b' align='center'>­��</div></td>
                    <td class='winbox' nowrap bgcolor='#ffffff'><div class='pt11b' align='center'>���</div></td>
                    <td class='winbox' nowrap bgcolor='#E0FFFF' align='right'>
                        <div class='pt11b'><?= number_format($ashi_futu_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#f5f5f5' align='right'>
                        <div class='pt11b'>-</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#E0FFFF' align='right'>
                        <div class='pt11b'><?= number_format($ashi_teiki_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#E0FFFF' align='right'>
                        <div class='pt11b'><?= number_format($ashi_total_kin) ?></div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' colspan='2'><div class='pt11b' align='center'>����</div></td>
                    <td class='winbox' nowrap bgcolor='#E0FFFF' align='right'>
                        <div class='pt11b'><?= number_format($futsu_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#E0FFFF' align='right'>
                        <div class='pt11b'><?= number_format($touza_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#E0FFFF' align='right'>
                        <div class='pt11b'><?= number_format($teiki_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#E0FFFF' align='right'>
                        <div class='pt11b'><?= number_format($yokin_total_kin) ?></div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' colspan='6' align='right'><div class='pt11b'>�����¶���</div></td>
                    <td class='winbox' nowrap bgcolor='#E0FFFF' align='right'>
                        <div class='pt11b'><?= number_format($genyo_total_kin) ?></div>
                    </td>
                </tr>
            </TBODY>
        </table>
            </td></tr>
        </table> <!----------------- ���ߡ�End ------------------>
        
        <BR clear='all'>
        
        <table bgcolor='#ffffff' align='left' cellspacing='0' cellpadding='10'>
            <tr><td> <!----------- ���ߡ�(�ǥ�������) ------------>
        <div class='pt11b'>2.��ݶ������</div>
        <table class='winbox_field' width='100%' align='center' bgcolor='black' cellspacing='1' cellpadding='5'>
            <THEAD>
                <!-- �ơ��֥� �إå�����ɽ�� -->
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center'><div class='pt11b'>��̾</div></td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center'><div class='pt11b'>����</div></td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center'><div class='pt11b'>���</div></td>
                </tr>
            </THEAD>
            <TFOOT>
                <!-- ���ߤϥեå����ϲ���ʤ� -->
            </TFOOT>
            <TBODY>
                <?php if ($nk_uri_kin<>0) { ?>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='left'>
                        <div class='pt11b'>���칩��������</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='left'>
                        <div class='pt11b'>��������Ķ����Ӿ�2����9��4��</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#E0FFFF' align='right'>
                        <div class='pt11b'><?= number_format($nk_uri_kin) ?></div>
                    </td>
                </tr>
                <?php } ?>
                <?php if ($mt_uri_kin<>0) { ?>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='left'>
                        <div class='pt11b'>������� ��ɥƥå�</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='left'>
                        <div class='pt11b'>�����������Լ��1-1-36</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#E0FFFF' align='right'>
                        <div class='pt11b'><?= number_format($mt_uri_kin) ?></div>
                    </td>
                </tr>
                <?php } ?>
                <?php if ($snk_uri_kin<>0) { ?>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='left'>
                        <div class='pt11b'>������칩��������</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='left'>
                        <div class='pt11b'>ʡ�縩��ϻ����в���12��</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#E0FFFF' align='right'>
                        <div class='pt11b'><?= number_format($snk_uri_kin) ?></div>
                    </td>
                </tr>
                <?php } ?>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' colspan='2' align='right'><div class='pt11b'>���</div></td>
                    <td class='winbox' nowrap bgcolor='#E0FFFF' align='right'>
                        <div class='pt11b'><?= number_format($urikake_kin) ?></div>
                    </td>
                </tr>
            </TBODY>
        </table>
            </td></tr>
        </table> <!----------------- ���ߡ�End ------------------>
        
        <BR clear='all'>
        
        <table bgcolor='#ffffff' align='left' cellspacing='0' cellpadding='10'>
            <tr><td> <!----------- ���ߡ�(�ǥ�������) ------------>
        <div class='pt11b'>3.ê���񻺤�����</div>
        <table class='winbox_field' width='100%' align='center' bgcolor='black' cellspacing='1' cellpadding='5'>
            <THEAD>
                <!-- �ơ��֥� �إå�����ɽ�� -->
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' rowspan='2'><div class='pt11b'>����</div></td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' colspan='6'><div class='pt11b'>�������ڤ���¢��</div></td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' rowspan='2'><div class='pt11b'>�ų���</div></td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' rowspan='2'><div class='pt11b'>���</div></td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center'><div class='pt11b'>����������</div></td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center'><div class='pt11b'>Ⱦ������</div></td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center'><div class='pt11b'>������</div></td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center'><div class='pt11b'>�ã�����</div></td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center'><div class='pt11b'>��¢��</div></td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center'><div class='pt11b'>����</div></td>
                </tr>
            </THEAD>
            <TFOOT>
                <!-- ���ߤϥեå����ϲ���ʤ� -->
            </TFOOT>
            <TBODY>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center'><div class='pt11b'>��ࡦ����</div></td>
                    <td class='winbox' nowrap bgcolor='#E0FFFF' align='right'>
                        <div class='pt11b'><?= number_format($sei_buhin_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#E0FFFF' align='right'>
                        <div class='pt11b'><?= number_format($tana_ken_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#E0FFFF' align='right'>
                        <div class='pt11b'><?= number_format($gen_sizai_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#E0FFFF' align='right'>
                        <div class='pt11b'>��</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#E0FFFF' align='right'>
                        <div class='pt11b'>��</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#E0FFFF' align='right'>
                        <div class='pt11b'><?= number_format($tana_sizai_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#E0FFFF' align='right'>
                        <div class='pt11b'>��</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#E0FFFF' align='right'>
                        <div class='pt11b'><?= number_format($tana_sizai_kin) ?></div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center'><div class='pt11b'>����</div></td>
                    <td class='winbox' nowrap bgcolor='#E0FFFF' align='right'>
                        <div class='pt11b'>��</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#E0FFFF' align='right'>
                        <div class='pt11b'><?= number_format($tana_kou_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#E0FFFF' align='right'>
                        <div class='pt11b'>��</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#E0FFFF' align='right'>
                        <div class='pt11b'>��</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#E0FFFF' align='right'>
                        <div class='pt11b'>��</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#E0FFFF' align='right'>
                        <div class='pt11b'><?= number_format($tana_kou_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#E0FFFF' align='right'>
                        <div class='pt11b'>��</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#E0FFFF' align='right'>
                        <div class='pt11b'><?= number_format($tana_kou_kin) ?></div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center'><div class='pt11b'>����</div></td>
                    <td class='winbox' nowrap bgcolor='#E0FFFF' align='right'>
                        <div class='pt11b'>��</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#E0FFFF' align='right'>
                        <div class='pt11b'><?= number_format($tana_gai_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#E0FFFF' align='right'>
                        <div class='pt11b'>��</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#E0FFFF' align='right'>
                        <div class='pt11b'>��</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#E0FFFF' align='right'>
                        <div class='pt11b'>��</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#E0FFFF' align='right'>
                        <div class='pt11b'><?= number_format($tana_gai_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#E0FFFF' align='right'>
                        <div class='pt11b'>��</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#E0FFFF' align='right'>
                        <div class='pt11b'><?= number_format($tana_gai_kin) ?></div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center'><div class='pt11b'>��Ω</div></td>
                    <td class='winbox' nowrap bgcolor='#E0FFFF' align='right'>
                        <div class='pt11b'>��</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#E0FFFF' align='right'>
                        <div class='pt11b'>��</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#E0FFFF' align='right'>
                        <div class='pt11b'>��</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#E0FFFF' align='right'>
                        <div class='pt11b'><?= number_format($kumi_cc_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#E0FFFF' align='right'>
                        <div class='pt11b'>��</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#E0FFFF' align='right'>
                        <div class='pt11b'><?= number_format($kumi_cc_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#E0FFFF' align='right'>
                        <div class='pt11b'><?= number_format($sikakari_total_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#E0FFFF' align='right'>
                        <div class='pt11b'><?= number_format($kumi_total_kin) ?></div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center'><div class='pt11b'>����¾</div></td>
                    <td class='winbox' nowrap bgcolor='#E0FFFF' align='right'>
                        <div class='pt11b'>��</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#E0FFFF' align='right'>
                        <div class='pt11b'>��</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#E0FFFF' align='right'>
                        <div class='pt11b'>��</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#E0FFFF' align='right'>
                        <div class='pt11b'>��</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#E0FFFF' align='right'>
                        <div class='pt11b'><?= number_format($chozo_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#E0FFFF' align='right'>
                        <div class='pt11b'><?= number_format($chozo_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#E0FFFF' align='right'>
                        <div class='pt11b'>��</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#E0FFFF' align='right'>
                        <div class='pt11b'><?= number_format($chozo_kin) ?></div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center'><div class='pt11b'>���</div></td>
                    <td class='winbox' nowrap bgcolor='#E0FFFF' align='right'>
                        <div class='pt11b'><?= number_format($sei_buhin_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#E0FFFF' align='right'>
                        <div class='pt11b'><?= number_format($han_total_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#E0FFFF' align='right'>
                        <div class='pt11b'><?= number_format($gen_sizai_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#E0FFFF' align='right'>
                        <div class='pt11b'><?= number_format($kumi_cc_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#E0FFFF' align='right'>
                        <div class='pt11b'><?= number_format($chozo_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#E0FFFF' align='right'>
                        <div class='pt11b'><?= number_format($gencho_total_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#E0FFFF' align='right'>
                        <div class='pt11b'><?= number_format($sikakari_total_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#E0FFFF' align='right'>
                        <div class='pt11b'><?= number_format($ryudozaiko_total_kin) ?></div>
                    </td>
                </tr>
            </TBODY>
        </table>
            </td></tr>
        </table> <!----------------- ���ߡ�End ------------------>
        
        <BR clear='all'>
        
        <table bgcolor='#ffffff' align='left' cellspacing='0' cellpadding='10'>
            <tr><td> <!----------- ���ߡ�(�ǥ�������) ------------>
        <div class='pt11b'>4.��ʧ���Ѥ�����</div>
        <table class='winbox_field' width='100%' align='center' bgcolor='black' cellspacing='1' cellpadding='5'>
            <THEAD>
                <!-- �ơ��֥� �إå�����ɽ�� -->
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center'><div class='pt11b'>��̾</div></td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center'><div class='pt11b'>����</div></td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center'><div class='pt11b'>���</div></td>
                </tr>
            </THEAD>
            <TFOOT>
                <!-- ���ߤϥեå����ϲ���ʤ� -->
            </TFOOT>
            <TBODY>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffe4c4' align='right'>
                        <div class='pt11b'>��</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffe4c4' align='right'>
                        <div class='pt11b'>��</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffe4c4' align='right'>
                        <div class='pt11b'>��</div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' colspan='2' align='right'><div class='pt11b'>���</div></td>
                    <td class='winbox' nowrap bgcolor='#E0FFFF' align='right'>
                        <div class='pt11b'><?= number_format($mae_hiyo_kin) ?></div>
                    </td>
                </tr>
            </TBODY>
        </table>
            </td></tr>
        </table> <!----------------- ���ߡ�End ------------------>
        
        <BR clear='all'>
        
        <table bgcolor='#ffffff' align='left' cellspacing='0' cellpadding='10'>
            <tr><td> <!----------- ���ߡ�(�ǥ�������) ------------>
        <div class='pt11b'>5.̤�����������</div>
        <table class='winbox_field' width='100%' align='center' bgcolor='black' cellspacing='1' cellpadding='5'>
            <THEAD>
                <!-- �ơ��֥� �إå�����ɽ�� -->
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center'><div class='pt11b'>��ʬ</div></td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center'><div class='pt11b'>����</div></td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center'><div class='pt11b'>���</div></td>
                </tr>
            </THEAD>
            <TFOOT>
                <!-- ���ߤϥեå����ϲ���ʤ� -->
            </TFOOT>
            <TBODY>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffe4c4' align='right'>
                        <div class='pt11b'>��</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffe4c4' align='right'>
                        <div class='pt11b'>��</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffe4c4' align='right'>
                        <div class='pt11b'>��</div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' colspan='2' align='right'><div class='pt11b'>���</div></td>
                    <td class='winbox' nowrap bgcolor='#E0FFFF' align='right'>
                        <div class='pt11b'><?= number_format($ryu_mishu_total_kin) ?></div>
                    </td>
                </tr>
            </TBODY>
        </table>
            </td></tr>
        </table> <!----------------- ���ߡ�End ------------------>
        
        <BR clear='all'>
        
        <table bgcolor='#ffffff' align='left' cellspacing='0' cellpadding='10'>
            <tr><td> <!----------- ���ߡ�(�ǥ�������) ------------>
        <div class='pt11b'>6.����¾ήư�񻺤�����</div>
        <table class='winbox_field' width='100%' align='center' bgcolor='black' cellspacing='1' cellpadding='5'>
            <THEAD>
                <!-- �ơ��֥� �إå�����ɽ�� -->
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center'><div class='pt11b'>�������</div></td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center'><div class='pt11b'>����</div></td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center'><div class='pt11b'>���</div></td>
                </tr>
            </THEAD>
            <TFOOT>
                <!-- ���ߤϥեå����ϲ���ʤ� -->
            </TFOOT>
            <TBODY>
                <?php if ($karibara_kin<>0) { ?>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='left'>
                        <div class='pt11b'>��ʧ��</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffe4c4' align='right'>
                        <div class='pt11b'>��</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#E0FFFF' align='right'>
                        <div class='pt11b'><?= number_format($karibara_kin) ?></div>
                    </td>
                </tr>
                <?php } ?>
                <?php if ($tatekae_kin<>0) { ?>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='left'>
                        <div class='pt11b'>Ω�ض�</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffe4c4' align='right'>
                        <div class='pt11b'>��</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#E0FFFF' align='right'>
                        <div class='pt11b'><?= number_format($tatekae_kin) ?></div>
                    </td>
                </tr>
                <?php } ?>
                <?php if ($hokaryudo_kin<>0) { ?>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='left'>
                        <div class='pt11b'>����¾</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffe4c4' align='right'>
                        <div class='pt11b'>��</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#E0FFFF' align='right'>
                        <div class='pt11b'><?= number_format($hokaryudo_kin) ?></div>
                    </td>
                </tr>
                <?php } ?>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' colspan='2' align='right'><div class='pt11b'>���</div></td>
                    <td class='winbox' nowrap bgcolor='#E0FFFF' align='right'>
                        <div class='pt11b'><?= number_format($hokaryudo_total_kin) ?></div>
                    </td>
                </tr>
            </TBODY>
        </table>
            </td></tr>
        </table> <!----------------- ���ߡ�End ------------------>
        
        <BR clear='all'>
        
        <table bgcolor='#ffffff' align='left' cellspacing='0' cellpadding='10'>
            <tr><td> <!----------- ���ߡ�(�ǥ�������) ------------>
        <div class='pt11b'>7.ͭ������񻺵ڤӸ�������������������������̻����ٽ񻲾�</div>
            </td></tr>
        </table> <!----------------- ���ߡ�End ------------------>
        
        <BR clear='all'>
        
        <table bgcolor='#ffffff' align='left' cellspacing='0' cellpadding='10'>
            <tr><td> <!----------- ���ߡ�(�ǥ�������) ------------>
        <div class='pt11b'>8.���ò�����������</div>
        <table class='winbox_field' width='100%' align='center' bgcolor='black' cellspacing='1' cellpadding='5'>
            <THEAD>
                <!-- �ơ��֥� �إå�����ɽ�� -->
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center'><div class='pt11b'>�����ֹ�</div></td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center'><div class='pt11b'>Ŧ��</div></td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center'><div class='pt11b'>���</div></td>
                </tr>
            </THEAD>
            <TFOOT>
                <!-- ���ߤϥեå����ϲ���ʤ� -->
            </TFOOT>
            <TBODY>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center'><div class='pt11b'>028��682��8851</div></td>
                    <td class='winbox' nowrap bgcolor='#ffffff'><div class='pt11b'>ȯ��ξ�ѡ���ɽ��</div></td>
                    <td class='winbox' nowrap bgcolor='#E0FFFF' align='right'>
                        <div class='pt11b'>72,000</div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center'><div class='pt11b'>028��682��8852</div></td>
                    <td class='winbox' nowrap bgcolor='#ffffff'><div class='pt11b'>ȯ��ξ��</div></td>
                    <td class='winbox' nowrap bgcolor='#E0FFFF' align='right'>
                        <div class='pt11b'>72,000</div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center'><div class='pt11b'>028��682��8853</div></td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right'><div class='pt11b'>�ʵٻߡ�</div></td>
                    <td class='winbox' nowrap bgcolor='#E0FFFF' align='right'>
                        <div class='pt11b'>72,000</div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center'><div class='pt11b'>028��682��9153</div></td>
                    <td class='winbox' nowrap bgcolor='#ffffff'><div class='pt11b'>������륤�����̳��</div></td>
                    <td class='winbox' nowrap bgcolor='#E0FFFF' align='right'>
                        <div class='pt11b'>72,000</div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center'><div class='pt11b'>028��682��9250</div></td>
                    <td class='winbox' nowrap bgcolor='#ffffff'><div class='pt11b'>������륤��ʹ����</div></td>
                    <td class='winbox' nowrap bgcolor='#E0FFFF' align='right'>
                        <div class='pt11b'>72,000</div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center'><div class='pt11b'>028��682��7471</div></td>
                    <td class='winbox' nowrap bgcolor='#ffffff'><div class='pt11b'>������륤��</div></td>
                    <td class='winbox' nowrap bgcolor='#E0FFFF' align='right'>
                        <div class='pt11b'>72,000</div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center'><div class='pt11b'>028��682��3044</div></td>
                    <td class='winbox' nowrap bgcolor='#ffffff'><div class='pt11b'>�ԥ����áʿ�Ʋ��</div></td>
                    <td class='winbox' nowrap bgcolor='#E0FFFF' align='right'>
                        <div class='pt11b'>72,000</div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center'><div class='pt11b'>028��681��6481</div></td>
                    <td class='winbox' nowrap bgcolor='#ffffff'><div class='pt11b'>���ʴ���</div></td>
                    <td class='winbox' nowrap bgcolor='#E0FFFF' align='right'>
                        <div class='pt11b'>72,000</div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center'><div class='pt11b'>028��681��6482</div></td>
                    <td class='winbox' nowrap bgcolor='#ffffff'><div class='pt11b'>���ʴ���</div></td>
                    <td class='winbox' nowrap bgcolor='#E0FFFF' align='right'>
                        <div class='pt11b'>72,000</div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center'><div class='pt11b'>028��682��7367</div></td>
                    <td class='winbox' nowrap bgcolor='#ffffff'><div class='pt11b'>�ƣ��ءʾ��ʴ�����</div></td>
                    <td class='winbox' nowrap bgcolor='#E0FFFF' align='right'>
                        <div class='pt11b'>72,000</div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center'><div class='pt11b'>028��681��7038</div></td>
                    <td class='winbox' nowrap bgcolor='#ffffff'><div class='pt11b'>�ƣ��ءʻ�̳����ɣӣģΡ�</div></td>
                    <td class='winbox' nowrap bgcolor='#E0FFFF' align='right'>
                        <div class='pt11b'>72,000</div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center'><div class='pt11b'>028��682��1324</div></td>
                    <td class='winbox' nowrap bgcolor='#ffffff'><div class='pt11b'>�ƣ��ء���6����1����̳���</div></td>
                    <td class='winbox' nowrap bgcolor='#E0FFFF' align='right'>
                        <div class='pt11b'>72,000</div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center'><div class='pt11b'>028��681��7652</div></td>
                    <td class='winbox' nowrap bgcolor='#ffffff'><div class='pt11b'>�����ľ��</div></td>
                    <td class='winbox' nowrap bgcolor='#E0FFFF' align='right'>
                        <div class='pt11b'>72,000</div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center'><div class='pt11b'>028��681��5105</div></td>
                    <td class='winbox' nowrap bgcolor='#ffffff'><div class='pt11b'>�򴹵�</div></td>
                    <td class='winbox' nowrap bgcolor='#E0FFFF' align='right'>
                        <div class='pt11b'>72,000</div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center'><div class='pt11b'>028��681��7011</div></td>
                    <td class='winbox' nowrap bgcolor='#ffffff'><div class='pt11b'>�ԣֲ���ѣɣӣģ�</div></td>
                    <td class='winbox' nowrap bgcolor='#E0FFFF' align='right'>
                        <div class='pt11b'>72,000</div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center'><div class='pt11b'>028��681��7735</div></td>
                    <td class='winbox' nowrap bgcolor='#ffffff'><div class='pt11b'>�����С�����</div></td>
                    <td class='winbox' nowrap bgcolor='#E0FFFF' align='right'>
                        <div class='pt11b'>72,000</div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center'><div class='pt11b'>028��682��8853</div></td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right'><div class='pt11b'>�ʵٻߡ�</div></td>
                    <td class='winbox' nowrap bgcolor='#E0FFFF' align='right'>
                        <div class='pt11b'>72,000</div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' colspan='2' align='right'><div class='pt11b'>���</div></td>
                    <td class='winbox' nowrap bgcolor='#E0FFFF' align='right'>
                        <div class='pt11b'><?= number_format($denwa_kin) ?></div>
                    </td>
                </tr>
            </TBODY>
        </table>
            </td></tr>
        </table> <!----------------- ���ߡ�End ------------------>
        
        <BR clear='all'>
        
        <table bgcolor='#ffffff' align='left' cellspacing='0' cellpadding='10'>
            <tr><td> <!----------- ���ߡ�(�ǥ�������) ------------>
        <div class='pt11b'>9.���եȥ����������������������̻����ٽ񻲾�</div>
            </td></tr>
        </table> <!----------------- ���ߡ�End ------------------>
        
        <BR clear='all'>
        
        <table bgcolor='#ffffff' align='left' cellspacing='0' cellpadding='10'>
            <tr><td> <!----------- ���ߡ�(�ǥ�������) ------------>
        <div class='pt11b'>10.�����Ƕ�񻺤�����</div>
        <table class='winbox_field' width='100%' align='center' bgcolor='black' cellspacing='1' cellpadding='5'>
            <THEAD>
                <!-- �ơ��֥� �إå�����ɽ�� -->
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center'><div class='pt11b'>Ŧ��</div></td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center'><div class='pt11b'>����Ĺ�</div></td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center'><div class='pt11b'>�������ó�</div></td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center'><div class='pt11b'>����������</div></td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center'><div class='pt11b'>�����Ĺ�</div></td>
                </tr>
            </THEAD>
            <TFOOT>
                <!-- ���ߤϥեå����ϲ���ʤ� -->
            </TFOOT>
            <TBODY>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='left'><div class='pt11b'>�����</div></td>
                    <td class='winbox' nowrap bgcolor='#E0FFFF' align='right'>
                        <div class='pt11b'><?= number_format($kotei_kurizei_kishu) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#E0FFFF' align='right'>
                        <div class='pt11b'><?= number_format($kotei_kurizei_kin_zou) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#E0FFFF' align='right'>
                        <div class='pt11b'><?= number_format($kotei_kurizei_kin_gen) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#E0FFFF' align='right'>
                        <div class='pt11b'><?= number_format($kotei_kuri_zei_kin) ?></div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right'><div class='pt11b'>���</div></td>
                    <td class='winbox' nowrap bgcolor='#E0FFFF' align='right'>
                        <div class='pt11b'><?= number_format($kotei_kurizei_kishu) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#E0FFFF' align='right'>
                        <div class='pt11b'><?= number_format($kotei_kurizei_kin_zou) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#E0FFFF' align='right'>
                        <div class='pt11b'><?= number_format($kotei_kurizei_kin_gen) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#E0FFFF' align='right'>
                        <div class='pt11b'><?= number_format($kotei_kuri_zei_kin) ?></div>
                    </td>
                </tr>
            </TBODY>
        </table>
            </td></tr>
        </table> <!----------------- ���ߡ�End ------------------>
        
        <BR clear='all'>
        
        <table bgcolor='#ffffff' align='left' cellspacing='0' cellpadding='10'>
            <tr><td> <!----------- ���ߡ�(�ǥ�������) ------------>
        <div class='pt11b'>11.Ĺ�����ն������</div>
        <table class='winbox_field' width='100%' align='center' bgcolor='black' cellspacing='1' cellpadding='5'>
            <THEAD>
                <!-- �ơ��֥� �إå�����ɽ�� -->
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center'><div class='pt11b'>Ŧ��</div></td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center'><div class='pt11b'>����Ĺ�</div></td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center'><div class='pt11b'>�������ó�</div></td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center'><div class='pt11b'>����������</div></td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center'><div class='pt11b'>�����Ĺ�</div></td>
                </tr>
            </THEAD>
            <TFOOT>
                <!-- ���ߤϥեå����ϲ���ʤ� -->
            </TFOOT>
            <TBODY>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='left'><div class='pt11b'>���Ȱ����ն�</div></td>
                    <td class='winbox' nowrap bgcolor='#E0FFFF' align='right'>
                        <div class='pt11b'><?= number_format($jyu_kashi_kishu) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#E0FFFF' align='right'>
                        <div class='pt11b'><?= number_format($jyu_kashi_kin_zou) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#E0FFFF' align='right'>
                        <div class='pt11b'><?= number_format($jyu_kashi_kin_gen) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#E0FFFF' align='right'>
                        <div class='pt11b'><?= number_format($choki_kashi_kin) ?></div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right'><div class='pt11b'>���</div></td>
                    <td class='winbox' nowrap bgcolor='#E0FFFF' align='right'>
                        <div class='pt11b'><?= number_format($jyu_kashi_kishu) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#E0FFFF' align='right'>
                        <div class='pt11b'><?= number_format($jyu_kashi_kin_zou) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#E0FFFF' align='right'>
                        <div class='pt11b'><?= number_format($jyu_kashi_kin_gen) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#E0FFFF' align='right'>
                        <div class='pt11b'><?= number_format($choki_kashi_kin) ?></div>
                    </td>
                </tr>
            </TBODY>
        </table>
            </td></tr>
        </table> <!----------------- ���ߡ�End ------------------>
        
        <BR clear='all'>
        
        <table bgcolor='#ffffff' align='left' cellspacing='0' cellpadding='10'>
            <tr><td> <!----------- ���ߡ�(�ǥ�������) ------------>
        <div class='pt11b'>12.Ĺ����ʧ���Ѥ�����</div>
        <table class='winbox_field' width='100%' align='center' bgcolor='black' cellspacing='1' cellpadding='5'>
            <THEAD>
                <!-- �ơ��֥� �إå�����ɽ�� -->
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center'><div class='pt11b'>Ŧ��</div></td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center'><div class='pt11b'>����Ĺ�</div></td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center'><div class='pt11b'>�������ó�</div></td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center'><div class='pt11b'>����������</div></td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center'><div class='pt11b'>�����Ĺ�</div></td>
                </tr>
            </THEAD>
            <TFOOT>
                <!-- ���ߤϥեå����ϲ���ʤ� -->
            </TFOOT>
            <TBODY>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffe4c4' align='right'>
                        <div class='pt11b'>��</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffe4c4' align='right'>
                        <div class='pt11b'>��</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffe4c4' align='right'>
                        <div class='pt11b'>��</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffe4c4' align='right'>
                        <div class='pt11b'>��</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffe4c4' align='right'>
                        <div class='pt11b'>��</div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right'><div class='pt11b'>���</div></td>
                    <td class='winbox' nowrap bgcolor='#E0FFFF' align='right'>
                        <div class='pt11b'><?= number_format($choki_maebara_kishu) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#E0FFFF' align='right'>
                        <div class='pt11b'><?= number_format($choki_maebara_kin_zou) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#E0FFFF' align='right'>
                        <div class='pt11b'><?= number_format($choki_maebara_kin_gen) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#E0FFFF' align='right'>
                        <div class='pt11b'><?= number_format($choki_maebara_kin) ?></div>
                    </td>
                </tr>
            </TBODY>
        </table>
            </td></tr>
        </table> <!----------------- ���ߡ�End ------------------>
        
        <BR clear='all'>
        
        <table bgcolor='#ffffff' align='left' cellspacing='0' cellpadding='10'>
            <tr><td> <!----------- ���ߡ�(�ǥ�������) ------------>
        <div class='pt11b'>13.����¾�����������</div>
        <table class='winbox_field' width='100%' align='center' bgcolor='black' cellspacing='1' cellpadding='5'>
            <THEAD>
                <!-- �ơ��֥� �إå�����ɽ�� -->
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center'><div class='pt11b'>��ʬ</div></td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center'><div class='pt11b'>��ʧ��</div></td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center'><div class='pt11b'>���</div></td>
                </tr>
            </THEAD>
            <TFOOT>
                <!-- ���ߤϥեå����ϲ���ʤ� -->
            </TFOOT>
            <TBODY>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='left'><div class='pt11b'>�л��</div></td>
                    <td class='winbox' nowrap bgcolor='#ffffff'><div class='pt11b'>�����̿������ƥඨƱ�ȹ�</div></td>
                    <td class='winbox' nowrap bgcolor='#E0FFFF' align='right'>
                        <div class='pt11b'>10,000</div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' colspan='2'><div class='pt11b'>���</div></td>
                    <td class='winbox' nowrap bgcolor='#E0FFFF' align='right'>
                        <div class='pt11b'>10,000</div>
                    </td>
                </tr>
            </TBODY>
        </table>
            </td></tr>
        </table> <!----------------- ���ߡ�End ------------------>
        
        <BR clear='all'>
        
        <table bgcolor='#ffffff' align='left' cellspacing='0' cellpadding='10'>
            <tr><td> <!----------- ���ߡ�(�ǥ�������) ------------>
        <div class='pt11b'>14.��ݶ������</div>
        <table class='winbox_field' width='100%' align='center' bgcolor='black' cellspacing='1' cellpadding='5'>
            <THEAD>
                <!-- �ơ��֥� �إå�����ɽ�� -->
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center'><div class='pt11b'>���̾</div></td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center'><div class='pt11b'>�ܼҽ���</div></td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center'><div class='pt11b'>���</div></td>
                </tr>
            </THEAD>
            <TFOOT>
                <!-- ���ߤϥեå����ϲ���ʤ� -->
            </TFOOT>
            <TBODY>
                <?php for ($i = 1; $i < 11; $i++) { ?>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='left'>
                        <div class='pt11b'><?= $kaikake_top[$i][0] ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='left'>
                        <div class='pt11b'><?= $kaikake_top[$i][1] ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#E0FFFF' align='right'>
                        <div class='pt11b'><?= number_format($kaikake_top[$i][2]) ?></div>
                    </td>
                </tr>
                <?php } ?>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='left'>
                        <div class='pt11b'>����¾</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='left'>
                        <div class='pt11b'>��</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#E0FFFF' align='right'>
                        <div class='pt11b'><?= number_format($kaikake_top_sonota_kin) ?></div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' colspan='2'><div class='pt11b'>���</div></td>
                    <td class='winbox' nowrap bgcolor='#E0FFFF' align='right'>
                        <div class='pt11b'><?= number_format($kaikake_total_kin) ?></div>
                    </td>
                </tr>
            </TBODY>
        </table>
            </td></tr>
        </table> <!----------------- ���ߡ�End ------------------>
        
        <BR clear='all'>
        
        <table bgcolor='#ffffff' align='left' cellspacing='0' cellpadding='10'>
            <tr><td> <!----------- ���ߡ�(�ǥ�������) ------------>
        <div class='pt11b'>15.̤ʧ�������</div>
        <table class='winbox_field' width='100%' align='center' bgcolor='black' cellspacing='1' cellpadding='5'>
            <THEAD>
                <!-- �ơ��֥� �إå�����ɽ�� -->
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center'><div class='pt11b'>���̾����ʬ</div></td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center'><div class='pt11b'>�ܼҽ��ꡦ����</div></td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center'><div class='pt11b'>���</div></td>
                </tr>
            </THEAD>
            <TFOOT>
                <!-- ���ߤϥեå����ϲ���ʤ� -->
            </TFOOT>
            <TBODY>
                <?php for ($i = 1; $i < 11; $i++) { ?>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='left'>
                        <div class='pt11b'><?= $miharai_top[$i][0] ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='left'>
                        <div class='pt11b'><?= $miharai_top[$i][1] ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#E0FFFF' align='right'>
                        <div class='pt11b'><?= number_format($miharai_top[$i][2]) ?></div>
                    </td>
                </tr>
                <?php } ?>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='left'>
                        <div class='pt11b'>����¾</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='left'>
                        <div class='pt11b'>��</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#E0FFFF' align='right'>
                        <div class='pt11b'><?= number_format($miharai_top_sonota_kin) ?></div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' colspan='2'><div class='pt11b'>���</div></td>
                    <td class='winbox' nowrap bgcolor='#E0FFFF' align='right'>
                        <div class='pt11b'><?= number_format($miharai_total_kin) ?></div>
                    </td>
                </tr>
            </TBODY>
        </table>
            </td></tr>
        </table> <!----------------- ���ߡ�End ------------------>
        
        <BR clear='all'>
        
        <table bgcolor='#ffffff' align='left' cellspacing='0' cellpadding='10'>
            <tr><td> <!----------- ���ߡ�(�ǥ�������) ------------>
        <div class='pt11b'>16.̤ʧ��������������</div>
        <table class='winbox_field' width='100%' align='center' bgcolor='black' cellspacing='1' cellpadding='5'>
            <THEAD>
                <!-- �ơ��֥� �إå�����ɽ�� -->
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center'><div class='pt11b'>��ʬ</div></td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center'><div class='pt11b'>����</div></td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center'><div class='pt11b'>���</div></td>
                </tr>
            </THEAD>
            <TFOOT>
                <!-- ���ߤϥեå����ϲ���ʤ� -->
            </TFOOT>
            <TBODY>
                <tr>
                    <td class='winbox' nowrap bgcolor='#FFFFFF' align='left'>
                        <div class='pt11b'>��ʧ������</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#E0FFFF' align='left'>
                        <div class='pt11b'>��ͽ��Ǽ�ճ�<?= number_format($mae_sho_zei_kin) ?>�ߴޤ��</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#E0FFFF' align='right'>
                        <div class='pt11b'><?= number_format($karibara_zei_total) ?></div>
                        <!--
                        <div class='pt11b'><?= mb_ereg_replace('-', '��', number_format($karibara_zei_total)) ?></div>
                        -->
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#FFFFFF' align='left'>
                        <div class='pt11b'>����������</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#FFFFFF' align='right'>
                        <div class='pt11b'>��</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#E0FFFF' align='right'>
                        <div class='pt11b'><?= number_format($kariuke_sho_zei_kin) ?></div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' colspan='2'><div class='pt11b'>���</div></td>
                    <td class='winbox' nowrap bgcolor='#E0FFFF' align='right'>
                        <div class='pt11b'><?= number_format($mihazei_total_kin) ?></div>
                    </td>
                </tr>
            </TBODY>
        </table>
            </td></tr>
        </table> <!----------------- ���ߡ�End ------------------>
        
        <BR clear='all'>
        
        <table bgcolor='#ffffff' align='left' cellspacing='0' cellpadding='10'>
            <tr><td> <!----------- ���ߡ�(�ǥ�������) ------------>
        <div class='pt11b'>17.̤ʧˡ������������</div>
        <table class='winbox_field' width='100%' align='center' bgcolor='black' cellspacing='1' cellpadding='5'>
            <THEAD>
                <!-- �ơ��֥� �إå�����ɽ�� -->
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center'><div class='pt11b'>��ʬ</div></td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center'><div class='pt11b'>����Ĺ�</div></td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center'><div class='pt11b'>������ʧ��</div></td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center'><div class='pt11b'>����������</div></td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center'><div class='pt11b'>���������</div></td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center'><div class='pt11b'>�����Ĺ�</div></td>
                </tr>
            </THEAD>
            <TFOOT>
                <!-- ���ߤϥեå����ϲ���ʤ� -->
            </TFOOT>
            <TBODY>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='left'>
                        <div class='pt11b'>̤ʧˡ������</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#E0FFFF' align='right'>
                        <div class='pt11b'><?= number_format($miharai_hozei_kishu) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffe4c4' align='right'>
                        <div class='pt11b'><?= number_format($miharai_hozei_shiha) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#E0FFFF' align='right'>
                        <div class='pt11b'>0</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffe4c4' align='right'>
                        <div class='pt11b'><?= number_format($miharai_hozei_settei) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#E0FFFF' align='right'>
                        <div class='pt11b'><?= number_format($miharai_hozei_kin) ?></div>
                    </td>
                </tr>
            </TBODY>
        </table>
            </td></tr>
        </table> <!----------------- ���ߡ�End ------------------>
        
        <BR clear='all'>
        
        <table bgcolor='#ffffff' align='left' cellspacing='0' cellpadding='10'>
            <tr><td> <!----------- ���ߡ�(�ǥ�������) ------------>
        <div class='pt11b'>18.̤ʧ���Ѥ�����</div>
        <table class='winbox_field' width='100%' align='center' bgcolor='black' cellspacing='1' cellpadding='5'>
            <THEAD>
                <!-- �ơ��֥� �إå�����ɽ�� -->
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center'><div class='pt11b'>���̾����ʬ</div></td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center'><div class='pt11b'>�ܼҽ��ꡦ����</div></td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center'><div class='pt11b'>���</div></td>
                </tr>
            </THEAD>
            <TFOOT>
                <!-- ���ߤϥեå����ϲ���ʤ� -->
            </TFOOT>
            <TBODY>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffe4c4' align='right'>
                        <div class='pt11b'>��</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffe4c4' align='right'>
                        <div class='pt11b'>��</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffe4c4' align='right'>
                        <div class='pt11b'>��</div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' colspan='2'><div class='pt11b'>���</div></td>
                    <td class='winbox' nowrap bgcolor='#E0FFFF' align='right'>
                        <div class='pt11b'><?= number_format($miharai_hiyo_kin) ?></div>
                    </td>
                </tr>
            </TBODY>
        </table>
            </td></tr>
        </table> <!----------------- ���ߡ�End ------------------>
        
        <BR clear='all'>
        
        <table bgcolor='#ffffff' align='left' cellspacing='0' cellpadding='10'>
            <tr><td> <!----------- ���ߡ�(�ǥ�������) ------------>
        <div class='pt11b'>19.�¤�������</div>
        <table class='winbox_field' width='100%' align='center' bgcolor='black' cellspacing='1' cellpadding='5'>
            <THEAD>
                <!-- �ơ��֥� �إå�����ɽ�� -->
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center'><div class='pt11b'>��ʬ</div></td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center'><div class='pt11b'>�ܼҽ��ꡦ����</div></td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center'><div class='pt11b'>���</div></td>
                </tr>
            </THEAD>
            <TFOOT>
                <!-- ���ߤϥեå����ϲ���ʤ� -->
            </TFOOT>
            <TBODY>
                <?php if ($gen_shotoku_kin<>0) { ?>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='left'>
                        <div class='pt11b'>����������</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffe4c4' align='right'>
                        <div class='pt11b'>��</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#E0FFFF' align='right'>
                        <div class='pt11b'><?= number_format($gen_shotoku_kin) ?></div>
                    </td>
                </tr>
                <?php } ?>
                <?php if ($gen_jyu_kin<>0) { ?>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='left'>
                        <div class='pt11b'>������̱��</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffe4c4' align='right'>
                        <div class='pt11b'>��</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#E0FFFF' align='right'>
                        <div class='pt11b'><?= number_format($gen_jyu_kin) ?></div>
                    </td>
                </tr>
                <?php } ?>
                <?php if ($ken_hoken_kin<>0) { ?>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='left'>
                        <div class='pt11b'>���ݸ���</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffe4c4' align='right'>
                        <div class='pt11b'>��</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#E0FFFF' align='right'>
                        <div class='pt11b'><?= number_format($ken_hoken_kin) ?></div>
                    </td>
                </tr>
                <?php } ?>
                <?php if ($kou_hoken_kin<>0) { ?>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='left'>
                        <div class='pt11b'>����ǯ���ݸ���</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffe4c4' align='right'>
                        <div class='pt11b'>��</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#E0FFFF' align='right'>
                        <div class='pt11b'><?= number_format($kou_hoken_kin) ?></div>
                    </td>
                </tr>
                <?php } ?>
                <?php if ($azu_sonota_kin<>0) { ?>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='left'>
                        <div class='pt11b'>����¾</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right'>
                        <div class='pt11b'>��</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#E0FFFF' align='right'>
                        <div class='pt11b'><?= number_format($azu_sonota_kin) ?></div>
                    </td>
                </tr>
                <?php } ?>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' colspan='2'><div class='pt11b'>���</div></td>
                    <td class='winbox' nowrap bgcolor='#E0FFFF' align='right'>
                        <div class='pt11b'><?= number_format($azukari_kin) ?></div>
                    </td>
                </tr>
            </TBODY>
        </table>
            </td></tr>
        </table> <!----------------- ���ߡ�End ------------------>
        
        <BR clear='all'>
        
        <table bgcolor='#ffffff' align='left' cellspacing='0' cellpadding='10'>
            <tr><td> <!----------- ���ߡ�(�ǥ�������) ------------>
        <div class='pt11b'>20.�����������</div>
        <table class='winbox_field' width='100%' align='center' bgcolor='black' cellspacing='1' cellpadding='5'>
            <THEAD>
                <!-- �ơ��֥� �إå�����ɽ�� -->
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' rowspan='2'><div class='pt11b'>��ʬ</div></td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' rowspan='2'><div class='pt11b'>����Ĺ�</div></td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' rowspan='2'><div class='pt11b'>�������ó�</div></td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' colspan='2'><div class='pt11b'>����������</div></td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' rowspan='2'><div class='pt11b'>�����Ĺ�</div></td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center'><div class='pt11b'>��Ū����</div></td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center'><div class='pt11b'>����¾</div></td>
                </tr>
            </THEAD>
            <TFOOT>
                <!-- ���ߤϥեå����ϲ���ʤ� -->
            </TFOOT>
            <TBODY>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='left'>
                        <div class='pt11b'>��Ϳ������</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffe4c4' align='right'>
                        <div class='pt11b'>-</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffe4c4' align='right'>
                        <div class='pt11b'>-</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffe4c4' align='right'>
                        <div class='pt11b'>-</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffe4c4' align='right'>
                        <div class='pt11b'>-</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#E0FFFF' align='right'>
                        <div class='pt11b'><?= number_format($syoyo_hikiate_kin) ?></div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='left'>
                        <div class='pt11b'>�࿦���հ�����</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#E0FFFF' align='right'>
                        <div class='pt11b'><?= number_format($tai_hiki_kishu) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#E0FFFF' align='right'>
                        <div class='pt11b'><?= number_format($tai_hiki_kin_gen) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffe4c4' align='right'>
                        <div class='pt11b'><?= number_format($tai_hiki_kin_moku) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffe4c4' align='right'>
                        <div class='pt11b'><?= number_format($tai_hiki_kin_sonota) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#E0FFFF' align='right'>
                        <div class='pt11b'><?= number_format($taisyoku_hikiate_kin) ?></div>
                    </td>
                </tr>
        </table>
            </td></tr>
        </table> <!----------------- ���ߡ�End ------------------>
        
        <BR clear='all'>
        
        <table bgcolor='#ffffff' align='left' cellspacing='0' cellpadding='10'>
            <tr><td> <!----------- ���ߡ�(�ǥ�������) ------------>
        <div class='pt11b'>21.���ܶ�ڤӾ�;�������</div>
        <table class='winbox_field' width='100%' align='center' bgcolor='black' cellspacing='1' cellpadding='5'>
            <THEAD>
                <!-- �ơ��֥� �إå�����ɽ�� -->
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center'><div class='pt11b'>��ʬ</div></td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center'><div class='pt11b'>����</div></td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center'><div class='pt11b'>����Ĺ�</div></td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center'><div class='pt11b'>�������ó�</div></td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center'><div class='pt11b'>����������</div></td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center'><div class='pt11b'>�����Ĺ�</div></td>
                </tr>
            </THEAD>
            <TFOOT>
                <!-- ���ߤϥեå����ϲ���ʤ� -->
            </TFOOT>
            <TBODY>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='left'>
                        <div class='pt11b'>���ܶ�</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center'>
                        <div class='pt11b'>���̳���</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#E0FFFF' align='right'>
                        <div class='pt11b'><?= number_format($shihon_kin_kishu) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#E0FFFF' align='right'>
                        <div class='pt11b'><?= number_format($shihon_kin_gen) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#E0FFFF' align='right'>
                        <div class='pt11b'><?= number_format($shihon_kin_zou) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#E0FFFF' align='right'>
                        <div class='pt11b'><?= number_format($shihon_total_kin) ?></div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='left'>
                        <div class='pt11b'>���ܽ�����</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right'>
                        <div class='pt11b'>��</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#E0FFFF' align='right'>
                        <div class='pt11b'><?= number_format($shihon_jyunbi_kishu) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#E0FFFF' align='right'>
                        <div class='pt11b'><?= number_format($shihon_jyunbi_gen) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#E0FFFF' align='right'>
                        <div class='pt11b'><?= number_format($shihon_jyunbi_zou) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#E0FFFF' align='right'>
                        <div class='pt11b'><?= number_format($shihon_jyunbi_kin) ?></div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='left'>
                        <div class='pt11b'>����¾���ܾ�;��</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right'>
                        <div class='pt11b'>��</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#E0FFFF' align='right'>
                        <div class='pt11b'><?= number_format($sonota_shihon_jyoyo_kishu) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#E0FFFF' align='right'>
                        <div class='pt11b'><?= number_format($sonota_shihon_jyoyo_gen) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#E0FFFF' align='right'>
                        <div class='pt11b'><?= number_format($sonota_shihon_jyoyo_zou) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#E0FFFF' align='right'>
                        <div class='pt11b'><?= number_format($sonota_shihon_jyoyo_kin) ?></div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='left'>
                        <div class='pt11b'>����¾���׾�;��</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right'>
                        <div class='pt11b'>��</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#E0FFFF' align='right'>
                        <div class='pt11b'><?= number_format($sonota_rieki_jyoyo_kishu) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#E0FFFF' align='right'>
                        <div class='pt11b'><?= number_format($sonota_rieki_jyoyo_gen) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#E0FFFF' align='right'>
                        <div class='pt11b'><?= number_format($sonota_rieki_jyoyo_zou) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#E0FFFF' align='right'>
                        <div class='pt11b'><?= number_format($tai_sonota_rieki_jyoyo_kin) ?></div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' colspan='2'><div class='pt11b'>���</div></td>
                    <td class='winbox' nowrap bgcolor='#E0FFFF' align='right'>
                        <div class='pt11b'><?= number_format($shihon_jyoyo_kishu) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#E0FFFF' align='right'>
                        <div class='pt11b'><?= number_format($shihon_jyoyo_gen) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#E0FFFF' align='right'>
                        <div class='pt11b'><?= number_format($shihon_jyoyo_zou) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#E0FFFF' align='right'>
                        <div class='pt11b'><?= number_format($shihon_jyoyo_total) ?></div>
                    </td>
                </tr>
            </TBODY>
        </table>
            </td></tr>
        </table> <!----------------- ���ߡ�End ------------------>
        
        <BR clear='all'>
        
        <table bgcolor='#ffffff' align='left' cellspacing='0' cellpadding='10'>
            <tr><td> <!----------- ���ߡ�(�ǥ�������) ------------>
        <div class='pt11b'>22.���Ǹ��ݤ�����</div>
        <table class='winbox_field' width='100%' align='center' bgcolor='black' cellspacing='1' cellpadding='5'>
            <THEAD>
                <!-- �ơ��֥� �إå�����ɽ�� -->
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' rowspan='2'><div class='pt11b'>Ŧ��</div></td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center'><div class='pt11b'>��¤��</div></td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center'><div class='pt11b'>�δ���ڤӰ��̴�����</div></td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' rowspan='2'><div class='pt11b'>��׶��</div></td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' rowspan='2'><div class='pt11b'>����</div></td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center'><div class='pt11b'>���</div></td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center'><div class='pt11b'>���</div></td>
                </tr>
            </THEAD>
            <TFOOT>
                <!-- ���ߤϥեå����ϲ���ʤ� -->
            </TFOOT>
            <TBODY>
                <?php if ($kotei_zei_total_kin<>0) { ?>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='left'>
                        <div class='pt11b'>�������</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#E0FFFF' align='right'>
                        <div class='pt11b'><?= number_format($kotei_zei_sei_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#E0FFFF' align='right'>
                        <div class='pt11b'><?= number_format($kotei_zei_han_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#E0FFFF' align='right'>
                        <div class='pt11b'><?= number_format($kotei_zei_total_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right'>
                        <div class='pt11b'>��</div>
                    </td>
                </tr>
                <?php } ?>
                <?php if ($inshi_zei_total_kin<>0) { ?>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='left'>
                        <div class='pt11b'>������</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#E0FFFF' align='right'>
                        <div class='pt11b'><?= number_format($inshi_zei_sei_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#E0FFFF' align='right'>
                        <div class='pt11b'><?= number_format($inshi_zei_han_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#E0FFFF' align='right'>
                        <div class='pt11b'><?= number_format($inshi_zei_total_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right'>
                        <div class='pt11b'>��</div>
                    </td>
                </tr>
                <?php } ?>
                <?php if ($touroku_zei_total_kin<>0) { ?>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='left'>
                        <div class='pt11b'>��Ͽ�ȵ���</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#E0FFFF' align='right'>
                        <div class='pt11b'><?= number_format($touroku_zei_sei_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#E0FFFF' align='right'>
                        <div class='pt11b'><?= number_format($touroku_zei_han_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#E0FFFF' align='right'>
                        <div class='pt11b'><?= number_format($touroku_zei_total_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right'>
                        <div class='pt11b'>��</div>
                    </td>
                </tr>
                <?php } ?>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right'><div class='pt11b'>���</div></td>
                    <td class='winbox' nowrap bgcolor='#E0FFFF' align='right'>
                        <div class='pt11b'><?= number_format($shozei_sei_total) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#E0FFFF' align='right'>
                        <div class='pt11b'><?= number_format($shozei_han_total) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#E0FFFF' align='right'>
                        <div class='pt11b'><?= number_format($all_zeikoka_total_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right'>
                        <div class='pt11b'>��</div>
                    </td>
                </tr>
            </TBODY>
        </table>
            </td></tr>
        </table> <!----------------- ���ߡ�End ------------------>
        
        <BR clear='all'>
        
        <table bgcolor='#ffffff' align='left' cellspacing='0' cellpadding='10'>
            <tr><td> <!----------- ���ߡ�(�ǥ�������) ------------>
        <div class='pt11b'>23.������������</div>
        <table class='winbox_field' width='100%' align='center' bgcolor='black' cellspacing='1' cellpadding='5'>
            <THEAD>
                <!-- �ơ��֥� �إå�����ɽ�� -->
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center'><div class='pt11b'>Ŧ��</div></td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center'><div class='pt11b'>���</div></td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center'><div class='pt11b'>����</div></td>
                </tr>
            </THEAD>
            <TFOOT>
                <!-- ���ߤϥեå����ϲ���ʤ� -->
            </TFOOT>
            <TBODY>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffe4c4' align='right'>
                        <div class='pt11b'>��</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffe4c4' align='right'>
                        <div class='pt11b'>��</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right'>
                        <div class='pt11b'>��</div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right'><div class='pt11b'>���</div></td>
                    <td class='winbox' nowrap bgcolor='#E0FFFF' align='right'>
                        <div class='pt11b'><?= number_format($eigyo_shueki_total_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right'>
                        <div class='pt11b'>��</div>
                    </td>
                </tr>
            </TBODY>
        </table>
            </td></tr>
        </table> <!----------------- ���ߡ�End ------------------>
        
        <BR clear='all'>
        
        <table bgcolor='#ffffff' align='left' cellspacing='0' cellpadding='10'>
            <tr><td> <!----------- ���ߡ�(�ǥ�������) ------------>
        <div class='pt11b'>24.ˡ���ǡ���̱�ǵڤӻ����Ǥ�����</div>
        <table class='winbox_field' width='100%' align='center' bgcolor='black' cellspacing='1' cellpadding='5'>
            <THEAD>
                <!-- �ơ��֥� �إå�����ɽ�� -->
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center'><div class='pt11b'>Ŧ��</div></td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center'><div class='pt11b'>���</div></td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center'><div class='pt11b'>����</div></td>
                </tr>
            </THEAD>
            <TFOOT>
                <!-- ���ߤϥեå����ϲ���ʤ� -->
            </TFOOT>
            <TBODY>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='left'>
                        <div class='pt11b'>����ˡ���ǽ�̱�ǻ����ǰ�����</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffe4c4' align='right'>
                        <div class='pt11b'><?= number_format($toki_hojin_jigyo) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right'>
                        <div class='pt11b'>��</div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='left'>
                        <div class='pt11b'>�¶���©�����Ф��븻�������ǳ�</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#E0FFFF' align='right'>
                        <div class='pt11b'><?= number_format($gensen_shotoku_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right'>
                        <div class='pt11b'>��</div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right'><div class='pt11b'>���</div></td>
                    <td class='winbox' nowrap bgcolor='#E0FFFF' align='right'>
                        <div class='pt11b'><?= number_format($hojin_uchi_total_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right'>
                        <div class='pt11b'>��</div>
                    </td>
                </tr>
            </TBODY>
        </table>
            </td></tr>
        </table> <!----------------- ���ߡ�End ------------------>
        <!--
        <form method='post' action='<?php echo $menu->out_self() ?>'>
            <input class='pt10b' type='submit' name='input_data' value='��Ͽ' onClick='return data_input_click(this)'>
        </form>
        -->
</body>
</html>
