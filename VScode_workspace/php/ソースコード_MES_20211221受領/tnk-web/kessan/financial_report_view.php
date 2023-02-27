<?php
//////////////////////////////////////////////////////////////////////////////
// �»�״ط� �軻��                                                      //
// Copyright(C) 2018-2020 Norihisa.Ohya norihisa_ooya@nitto-kohki.co.jp     //
// Changed history                                                          //
// 2018/06/26 Created   financial_report_view.php                           //
// 2018/07/05 �裱��Ⱦ���軻�ǰ���Ĵ��                                      //
// 2018/07/25 ��ǽ����ʬ��AS�׻���»�ץǡ�������Ӥ��ɲ�                    //
//            �Ķȳ��˴ؤ��Ƥϡ����غ�»�פΰ١�Ʊ��ۤκ��ۤ��Ǥ�          //
// 2018/10/05 �ǥ�����Τ��������                                        //
// 2018/10/17 19����2��Ⱦ���η�̤�����ƽ���                               //
// 2019/04/09 �δ���Υ��졼���б�����ɲ�                                  //
// 2019/05/17 ���դμ�����ˡ���ѹ�                                          //
// 2020/01/27 ��������������ɽ���ɲ�                                        //
// 2020/04/13 eCA�ѤΥǡ���ȴ���Ф����ɲ�                                   //
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
$nk_ki = $ki + 44;

//////////// �����ȥ�̾(�������Υ����ȥ�̾�ȥե�����Υ����ȥ�̾)
if ($tuki_chk == 3) {
    $menu->set_title("�� {$ki} �����ܷ軻���衡������");
} else {
    $menu->set_title("�� {$ki} ������{$hanki}��Ⱦ�����衡������");
}

//// �߼��о�ɽ
//// ήư��
// ����ڤ��¶�
$res   = array();
$field = array();
$rows  = array();
$genkin_kin = 0;
$note = '����ڤ��¶�';
$query = sprintf("SELECT rep_kin FROM financial_report_data WHERE rep_ymd=%d and rep_note='%s'", $yyyymm, $note);
if ($rows=getResultWithField2($query, $field, $res) <= 0) {
    $genkin_kin = 0;
} else {
    $genkin_kin = $res[0][0];
}
// 2020/03/26 eCA�ǡ���Ϣ���б� ����
$csv_data = array();
$csv_data[0][0] = $note;
$csv_data[0][1] = $genkin_kin;

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
// 2020/03/26 eCA�ǡ���Ϣ���б� ����
$csv_data[1][0] = $note;
$csv_data[1][1] = $urikake_kin;

// �ų���
$res   = array();
$field = array();
$rows  = array();
$tai_shikakari_kin = 0;
$note = '�߼ڻų���';
$query = sprintf("SELECT rep_kin FROM financial_report_data WHERE rep_ymd=%d and rep_note='%s'", $yyyymm, $note);
if ($rows=getResultWithField2($query, $field, $res) <= 0) {
    $tai_shikakari_kin = 0;
} else {
    $tai_shikakari_kin = $res[0][0];
}
// �������ڤ���¢��
$res   = array();
$field = array();
$rows  = array();
$tai_zairyo_kin = 0;
$note = '�߼ڸ������ڤ���¢��';
$query = sprintf("SELECT rep_kin FROM financial_report_data WHERE rep_ymd=%d and rep_note='%s'", $yyyymm, $note);
if ($rows=getResultWithField2($query, $field, $res) <= 0) {
    $tai_zairyo_kin = 0;
} else {
    $tai_zairyo_kin = $res[0][0];
}
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
// ̤������
$res   = array();
$field = array();
$rows  = array();
$mishu_kin_kin = 0;
$note = '̤������';
$query = sprintf("SELECT rep_kin FROM financial_report_data WHERE rep_ymd=%d and rep_note='%s'", $yyyymm, $note);
if ($rows=getResultWithField2($query, $field, $res) <= 0) {
    $mishu_kin_kin = 0;
} else {
    $mishu_kin_kin = $res[0][0];
}
// ̤����������
$res   = array();
$field = array();
$rows  = array();
$mishu_shozei_kin = 0;
$note = '̤����������';
$query = sprintf("SELECT kin FROM profit_loss_bs_history WHERE pl_bs_ym=%d and note='%s'", $yyyymm, $note);
if ($rows=getResultWithField2($query, $field, $res) <= 0) {
    $mishu_shozei_kin = 0;
} else {
    $mishu_shozei_kin = $res[0][0];
}
// ����¾��ήư��
$res   = array();
$field = array();
$rows  = array();
$ta_ryudo_shisan_kin = 0;
$note = '����¾��ήư��';
$query = sprintf("SELECT rep_kin FROM financial_report_data WHERE rep_ymd=%d and rep_note='%s'", $yyyymm, $note);
if ($rows=getResultWithField2($query, $field, $res) <= 0) {
    $ta_ryudo_shisan_kin = 0;
} else {
    $ta_ryudo_shisan_kin = $res[0][0];
}

// ήư�񻺹��
$ryudo_total_kin = $genkin_kin + $urikake_kin + $tai_shikakari_kin + $tai_zairyo_kin + $mae_hiyo_kin + $mishu_kin_kin + $mishu_shozei_kin + $ta_ryudo_shisan_kin;

//// �����
//// ͭ�������
// ��ʪ
$res   = array();
$field = array();
$rows  = array();
$tatemono_shisan_kin = 0;
$note = '��ʪ';
$query = sprintf("SELECT rep_kin FROM financial_report_data WHERE rep_ymd=%d and rep_note='%s'", $yyyymm, $note);
if ($rows=getResultWithField2($query, $field, $res) <= 0) {
    $tatemono_shisan_kin = 0;
} else {
    $tatemono_shisan_kin = $res[0][0];
}
// �����ڤ�����
$res   = array();
$field = array();
$rows  = array();
$kikai_shisan_kin = 0;
$note = '�����ڤ�����';
$query = sprintf("SELECT rep_kin FROM financial_report_data WHERE rep_ymd=%d and rep_note='%s'", $yyyymm, $note);
if ($rows=getResultWithField2($query, $field, $res) <= 0) {
    $kikai_shisan_kin = 0;
} else {
    $kikai_shisan_kin = $res[0][0];
}
// ���ұ��¶�
$res   = array();
$field = array();
$rows  = array();
$sharyo_shisan_kin = 0;
$note = '���ұ��¶�';
$query = sprintf("SELECT rep_kin FROM financial_report_data WHERE rep_ymd=%d and rep_note='%s'", $yyyymm, $note);
if ($rows=getResultWithField2($query, $field, $res) <= 0) {
    $sharyo_shisan_kin = 0;
} else {
    $sharyo_shisan_kin = $res[0][0];
}
// ������ڤ�����
$res   = array();
$field = array();
$rows  = array();
$kougu_shisan_kin = 0;
$note = '������ڤ�����';
$query = sprintf("SELECT rep_kin FROM financial_report_data WHERE rep_ymd=%d and rep_note='%s'", $yyyymm, $note);
if ($rows=getResultWithField2($query, $field, $res) <= 0) {
    $kougu_shisan_kin = 0;
} else {
    $kougu_shisan_kin = $res[0][0];
}
// �꡼����
$res   = array();
$field = array();
$rows  = array();
$lease_shisan_kin = 0;
$note = '�꡼����';
$query = sprintf("SELECT rep_kin FROM financial_report_data WHERE rep_ymd=%d and rep_note='%s'", $yyyymm, $note);
if ($rows=getResultWithField2($query, $field, $res) <= 0) {
    $lease_shisan_kin = 0;
} else {
    $lease_shisan_kin = $res[0][0];
}
// ���߲�����
$res   = array();
$field = array();
$rows  = array();
$kenkari_kin = 0;
$note = '���߲�����';
$query = sprintf("SELECT kin FROM profit_loss_bs_history WHERE pl_bs_ym=%d and note='%s'", $yyyymm, $note);
if ($rows=getResultWithField2($query, $field, $res) <= 0) {
    $kenkari_kin = 0;
} else {
    $kenkari_kin = $res[0][0];
}

// ͭ������񻺹��
$yukei_shisan_kin = $tatemono_shisan_kin + $kikai_shisan_kin + $sharyo_shisan_kin + $kougu_shisan_kin + $lease_shisan_kin + $kenkari_kin;

//// ̵�������
// ���ò�����
$res   = array();
$field = array();
$rows  = array();
$denwa_shisan_kin = 0;
$note = '���ò�����';
$query = sprintf("SELECT rep_kin FROM financial_report_data WHERE rep_ymd=%d and rep_note='%s'", $yyyymm, $note);
if ($rows=getResultWithField2($query, $field, $res) <= 0) {
    $denwa_shisan_kin = 0;
} else {
    $denwa_shisan_kin = $res[0][0];
}
// �������Ѹ�
$res   = array();
$field = array();
$rows  = array();
$shisetsu_shisan_kin = 0;
$note = '�������Ѹ�';
$query = sprintf("SELECT kin FROM profit_loss_bs_history WHERE pl_bs_ym=%d and note='%s'", $yyyymm, $note);
if ($rows=getResultWithField2($query, $field, $res) <= 0) {
    $shisetsu_shisan_kin = 0;
} else {
    $shisetsu_shisan_kin = $res[0][0];
}
// ���եȥ�����
$res   = array();
$field = array();
$rows  = array();
$soft_shisan_kin = 0;
$note = '���եȥ�����';
$query = sprintf("SELECT kin FROM profit_loss_bs_history WHERE pl_bs_ym=%d and note='%s'", $yyyymm, $note);
if ($rows=getResultWithField2($query, $field, $res) <= 0) {
    $soft_shisan_kin = 0;
} else {
    $soft_shisan_kin = $res[0][0];
}

// ̵������񻺹��
$mukei_shisan_kin = $denwa_shisan_kin + $shisetsu_shisan_kin + $soft_shisan_kin;

//// ��񤽤�¾�λ�
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

// ����¾�������
$res   = array();
$field = array();
$rows  = array();
$sonota_toshi_kin = 0;
$note = '����¾�������';
$query = sprintf("SELECT kin FROM profit_loss_bs_history WHERE pl_bs_ym=%d and note='%s'", $yyyymm, $note);
if ($rows=getResultWithField2($query, $field, $res) <= 0) {
    $sonota_toshi_kin = 0;
} else {
    $sonota_toshi_kin = $res[0][0];
}

// ��񤽤�¾�λ񻺹��
$toshi_sonota_kin = $choki_kashi_kin + $choki_maebara_kin + $kotei_kuri_zei_kin + $sonota_toshi_kin;

// ����񻺹��
$kotei_shisan_total_kin = $yukei_shisan_kin + $mukei_shisan_kin + $toshi_sonota_kin;

// �񻺤������
$shisan_total_kin = $ryudo_total_kin + $kotei_shisan_total_kin;

//// ��ĵڤӽ�񻺤���
//// ��Ĥ���
//// ήư���
// ��ݶ�
$res   = array();
$field = array();
$rows  = array();
$kaikake_kin = 0;
$note = '��ݶ�';
$query = sprintf("SELECT rep_kin FROM financial_report_data WHERE rep_ymd=%d and rep_note='%s'", $yyyymm, $note);
if ($rows=getResultWithField2($query, $field, $res) <= 0) {
    $kaikake_kin = 0;
} else {
    $kaikake_kin = $res[0][0];
}
// �꡼����̳��û����
$res   = array();
$field = array();
$rows  = array();
$lease_tanki_kin = 0;
$note = '�꡼����̳(û��)';
$query = sprintf("SELECT kin FROM profit_loss_bs_history WHERE pl_bs_ym=%d and note='%s'", $yyyymm, $note);
if ($rows=getResultWithField2($query, $field, $res) <= 0) {
    $lease_tanki_kin = 0;
} else {
    $lease_tanki_kin = $res[0][0];
}
// ̤ʧ��
$res   = array();
$field = array();
$rows  = array();
$miharai_kin = 0;
$note = '̤ʧ��';
$query = sprintf("SELECT rep_kin FROM financial_report_data WHERE rep_ymd=%d and rep_note='%s'", $yyyymm, $note);
if ($rows=getResultWithField2($query, $field, $res) <= 0) {
    $miharai_kin = 0;
} else {
    $miharai_kin = $res[0][0];
}
// ̤ʧ��������
$res   = array();
$field = array();
$rows  = array();
$miharai_shozei_kin = 0;
$note = '̤ʧ��������';
$query = sprintf("SELECT rep_kin FROM financial_report_data WHERE rep_ymd=%d and rep_note='%s'", $yyyymm, $note);
if ($rows=getResultWithField2($query, $field, $res) <= 0) {
    $miharai_shozei_kin = 0;
} else {
    $miharai_shozei_kin = $res[0][0];
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

// ήư��Ĺ��
$ryudo_fusai_total_kin = $kaikake_kin + $lease_tanki_kin + $miharai_kin + $miharai_shozei_kin + $miharai_hozei_kin + $miharai_hiyo_kin + $azukari_kin + $syoyo_hikiate_kin;

//// �������
// �꡼����̳��Ĺ����
$res   = array();
$field = array();
$rows  = array();
$lease_choki_kin = 0;
$note = '�꡼����̳(Ĺ��)';
$query = sprintf("SELECT kin FROM profit_loss_bs_history WHERE pl_bs_ym=%d and note='%s'", $yyyymm, $note);
if ($rows=getResultWithField2($query, $field, $res) <= 0) {
    $lease_choki_kin = 0;
} else {
    $lease_choki_kin = $res[0][0];
}
// Ĺ��̤ʧ��
$res   = array();
$field = array();
$rows  = array();
$choki_miharai_kin = 0;
$note = 'Ĺ��̤ʧ��';
$query = sprintf("SELECT kin FROM profit_loss_bs_history WHERE pl_bs_ym=%d and note='%s'", $yyyymm, $note);
if ($rows=getResultWithField2($query, $field, $res) <= 0) {
    $choki_miharai_kin = 0;
} else {
    $choki_miharai_kin = $res[0][0];
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

// ������Ĺ��
$kotei_fusai_kin = $lease_choki_kin + $choki_miharai_kin + $taisyoku_hikiate_kin;

// ��Ĺ��
$fusai_total_kin = $ryudo_fusai_total_kin + $kotei_fusai_kin;

//// ��񻺤���
//// ���ܶ�
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

//// ���ܾ�;��
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

// ���ܾ�;����
$tai_shihon_jyoyo_total_kin = $shihon_jyunbi_kin + $sonota_shihon_jyoyo_kin;

//// ���׾�;��
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
// �������׾�;��
$res   = array();
$field = array();
$rows  = array();
$tai_kuri_rieki_jyoyo_kin = 0;
$note = '�������׾�;��';
$query = sprintf("SELECT kin FROM profit_loss_bs_history WHERE pl_bs_ym=%d and note='%s'", $yyyymm, $note);
if ($rows=getResultWithField2($query, $field, $res) <= 0) {
    $tai_kuri_rieki_jyoyo_kin = 0;
} else {
    $tai_kuri_rieki_jyoyo_kin = $res[0][0];
}
// ���������סʷ������׾�;��˹�פ����Ⱦ���б���
$res   = array();
$field = array();
$rows  = array();
$tai_toujyun = 0;
$note = '����������';
$query = sprintf("SELECT kin FROM profit_loss_bs_history WHERE pl_bs_ym=%d and note='%s'", $yyyymm, $note);
if ($rows=getResultWithField2($query, $field, $res) <= 0) {
    $tai_toujyun = 0;
} else {
    $tai_toujyun = $res[0][0];
}

// �߼��о�ɽ�� �������׾�;��η׻�
$tai_kuri_rieki_jyoyo_kin = $tai_kuri_rieki_jyoyo_kin + $tai_toujyun;

// ���׾�;����
$tai_rieki_jyoyo_total_kin = $tai_sonota_rieki_jyoyo_kin + $tai_kuri_rieki_jyoyo_kin;

// ��񻺹��
$tai_jyun_shisan_total_kin = $shihon_total_kin + $tai_shihon_jyoyo_total_kin + $tai_rieki_jyoyo_total_kin;

// ��ĵڤӽ�񻺹��
$fusai_jyunshi_total_kin = $fusai_total_kin + $tai_jyun_shisan_total_kin;

// �߼ں��۷׻�
$tai_sagaku_kin = $shisan_total_kin - $fusai_jyunshi_total_kin;

//// �������ٽ�
//// �����
// ��¤����
$res   = array();
$field = array();
$rows  = array();
$yakuin_seizo_kin = 0;
$note = '��¤���������';
$query = sprintf("SELECT SUM(kin) FROM profit_loss_keihi_history WHERE pl_bs_ym>=%d and pl_bs_ym<=%d and note='%s'", $str_ym, $end_ym, $note);
if ($rows=getResultWithField2($query, $field, $res) <= 0) {
    $yakuin_seizo_kin = 0;
} else {
    $yakuin_seizo_kin = $res[0][0];
}

// �δ���
$res   = array();
$field = array();
$rows  = array();
$yakuin_han_kin = 0;
$note = '�δ��������';
$query = sprintf("SELECT SUM(kin) FROM profit_loss_keihi_history WHERE pl_bs_ym>=%d and pl_bs_ym<=%d and note='%s'", $str_ym, $end_ym, $note);
if ($rows=getResultWithField2($query, $field, $res) <= 0) {
    $yakuin_han_kin = 0;
} else {
    $yakuin_han_kin = $res[0][0];
}

// ����󽷹��
$yakuin_total_kin = $yakuin_seizo_kin + $yakuin_han_kin;

//// ��������
// ��¤����
$res   = array();
$field = array();
$rows  = array();
$kyuryo_seizo_kin = 0;
$note = '��¤�����������';
$query = sprintf("SELECT SUM(kin) FROM profit_loss_keihi_history WHERE pl_bs_ym>=%d and pl_bs_ym<=%d and note='%s'", $str_ym, $end_ym, $note);
if ($rows=getResultWithField2($query, $field, $res) <= 0) {
    $kyuryo_seizo_kin = 0;
} else {
    $kyuryo_seizo_kin = $res[0][0];
}
// �δ���
$res   = array();
$field = array();
$rows  = array();
$kyuryo_han_kin = 0;
$note = '�δ����������';
$query = sprintf("SELECT SUM(kin) FROM profit_loss_keihi_history WHERE pl_bs_ym>=%d and pl_bs_ym<=%d and note='%s'", $str_ym, $end_ym, $note);
if ($rows=getResultWithField2($query, $field, $res) <= 0) {
    $kyuryo_han_kin = 0;
} else {
    $kyuryo_han_kin = $res[0][0];
}

// �����������
$kyuryo_total_kin = $kyuryo_seizo_kin + $kyuryo_han_kin;

//// ��Ϳ����
// ��¤����
$res   = array();
$field = array();
$rows  = array();
$syoyo_teate_seizo_kin = 0;
$note = '��¤�����Ϳ����';
$query = sprintf("SELECT SUM(kin) FROM profit_loss_keihi_history WHERE pl_bs_ym>=%d and pl_bs_ym<=%d and note='%s'", $str_ym, $end_ym, $note);
if ($rows=getResultWithField2($query, $field, $res) <= 0) {
    $syoyo_teate_seizo_kin = 0;
} else {
    $syoyo_teate_seizo_kin = $res[0][0];
}
// �δ���
$res   = array();
$field = array();
$rows  = array();
$syoyo_teate_han_kin = 0;
$note = '�δ����Ϳ����';
$query = sprintf("SELECT SUM(kin) FROM profit_loss_keihi_history WHERE pl_bs_ym>=%d and pl_bs_ym<=%d and note='%s'", $str_ym, $end_ym, $note);
if ($rows=getResultWithField2($query, $field, $res) <= 0) {
    $syoyo_teate_han_kin = 0;
} else {
    $syoyo_teate_han_kin = $res[0][0];
}

// ��Ϳ�������
$syoyo_teate_total_kin = $syoyo_teate_seizo_kin + $syoyo_teate_han_kin;

//// ������
// ��¤����
$komon_seizo_kin = 0;
// �δ���
$res   = array();
$field = array();
$rows  = array();
$komon_han_kin = 0;
$note = '�δ��������';
$query = sprintf("SELECT SUM(kin) FROM profit_loss_keihi_history WHERE pl_bs_ym>=%d and pl_bs_ym<=%d and note='%s'", $str_ym, $end_ym, $note);
if ($rows=getResultWithField2($query, $field, $res) <= 0) {
    $komon_han_kin = 0;
} else {
    $komon_han_kin = $res[0][0];
}

// ���������
$komon_total_kin = $komon_seizo_kin + $komon_han_kin;

//// ����ʡ����
// ��¤����
$res   = array();
$field = array();
$rows  = array();
$fukuri_seizo_kin = 0;
$note = '��¤�������ʡ����';
$query = sprintf("SELECT rep_kin FROM financial_report_data WHERE rep_ymd=%d and rep_note='%s'", $yyyymm, $note);
if ($rows=getResultWithField2($query, $field, $res) <= 0) {
    $fukuri_seizo_kin = 0;
} else {
    $fukuri_seizo_kin = $res[0][0];
}
// �δ���
$res   = array();
$field = array();
$rows  = array();
$fukuri_han_kin = 0;
$note = '�δ������ʡ����';
$query = sprintf("SELECT rep_kin FROM financial_report_data WHERE rep_ymd=%d and rep_note='%s'", $yyyymm, $note);
if ($rows=getResultWithField2($query, $field, $res) <= 0) {
    $fukuri_han_kin = 0;
} else {
    $fukuri_han_kin = $res[0][0];
}

// ����ʡ������
$fukuri_total_kin = $fukuri_seizo_kin + $fukuri_han_kin;

//// ��Ϳ�����ⷫ����
// ��¤����
$res   = array();
$field = array();
$rows  = array();
$syoyo_hikiate_seizo_kin = 0;
$note = '��¤�����Ϳ�����ⷫ��';
$query = sprintf("SELECT SUM(kin) FROM profit_loss_keihi_history WHERE pl_bs_ym>=%d and pl_bs_ym<=%d and note='%s'", $str_ym, $end_ym, $note);
if ($rows=getResultWithField2($query, $field, $res) <= 0) {
    $syoyo_hikiate_seizo_kin = 0;
} else {
    $syoyo_hikiate_seizo_kin = $res[0][0];
}
// �δ���
$res   = array();
$field = array();
$rows  = array();
$syoyo_hikiate_han_kin = 0;
$note = '�δ����Ϳ�����ⷫ��';
$query = sprintf("SELECT SUM(kin) FROM profit_loss_keihi_history WHERE pl_bs_ym>=%d and pl_bs_ym<=%d and note='%s'", $str_ym, $end_ym, $note);
if ($rows=getResultWithField2($query, $field, $res) <= 0) {
    $syoyo_hikiate_han_kin = 0;
} else {
    $syoyo_hikiate_han_kin = $res[0][0];
}

// ��Ϳ�����ⷫ�����
$syoyo_hikiate_total_kin = $syoyo_hikiate_seizo_kin + $syoyo_hikiate_han_kin;

//// �࿦��������
// ��¤����
$res   = array();
$field = array();
$rows  = array();
$tai_kyufu_seizo_kin = 0;
$note = '��¤�����࿦��������';
$query = sprintf("SELECT rep_kin FROM financial_report_data WHERE rep_ymd=%d and rep_note='%s'", $yyyymm, $note);
if ($rows=getResultWithField2($query, $field, $res) <= 0) {
    $tai_kyufu_seizo_kin = 0;
} else {
    $tai_kyufu_seizo_kin = $res[0][0];
}
// �δ���
$res   = array();
$field = array();
$rows  = array();
$tai_kyufu_han_kin = 0;
$note = '�δ����࿦��������';
$query = sprintf("SELECT rep_kin FROM financial_report_data WHERE rep_ymd=%d and rep_note='%s'", $yyyymm, $note);
if ($rows=getResultWithField2($query, $field, $res) <= 0) {
    $tai_kyufu_han_kin = 0;
} else {
    $tai_kyufu_han_kin = $res[0][0];
}

// �࿦�������ѹ��
$tai_kyufu_total_kin = $tai_kyufu_seizo_kin + $tai_kyufu_han_kin;

// ϫ̳����
$roumu_total_kin = $yakuin_seizo_kin + $kyuryo_seizo_kin + $syoyo_teate_seizo_kin + $komon_seizo_kin + $fukuri_seizo_kin + $syoyo_hikiate_seizo_kin + $tai_kyufu_seizo_kin;
// �ͷ�����
$jin_total_kin   = $yakuin_han_kin + $kyuryo_han_kin + $syoyo_teate_han_kin + $komon_han_kin + $fukuri_han_kin + $syoyo_hikiate_han_kin + $tai_kyufu_han_kin;
// ϫ̳��ͷ�����
$roumu_jin_total_kin = $roumu_total_kin + $jin_total_kin;

// ϫ̳�񡦿ͷ��񺹳۷׻�
// ����ϫ̳��
$res   = array();
$field = array();
$rows  = array();
$roumu_as_kin = 0;
$sum1 = '����ϫ̳��';
$query = sprintf("select SUM(kin) from profit_loss_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='%s'", $str_ym, $end_ym, $sum1);
if ($rows=getResultWithField2($query, $field, $res) <= 0) {
    $roumu_as_kin = 0;
} else {
    $roumu_as_kin = $res[0][0];
}
$roumu_as_sagaku = $roumu_total_kin - $roumu_as_kin;

// ���οͷ���
$res   = array();
$field = array();
$rows  = array();
$jin_as_kin = 0;
$sum1 = '���οͷ���';
$query = sprintf("select SUM(kin) from profit_loss_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='%s'", $str_ym, $end_ym, $sum1);
if ($rows=getResultWithField2($query, $field, $res) <= 0) {
    $jin_as_kin = 0;
} else {
    $jin_as_kin = $res[0][0];
}
$jin_as_sagaku = $jin_total_kin - $jin_as_kin;


//// ��¤���񡦷���
//// ι�������
// ��¤����
$res   = array();
$field = array();
$rows  = array();
$ryohi_seizo_kin = 0;
$note = '��¤����ι�������';
$query = sprintf("SELECT rep_kin FROM financial_report_data WHERE rep_ymd=%d and rep_note='%s'", $yyyymm, $note);
if ($rows=getResultWithField2($query, $field, $res) <= 0) {
    $ryohi_seizo_kin = 0;
} else {
    $ryohi_seizo_kin = $res[0][0];
}
// �δ���
$res   = array();
$field = array();
$rows  = array();
$ryohi_han_kin = 0;
$note = '�δ���ι�������';
$query = sprintf("SELECT rep_kin FROM financial_report_data WHERE rep_ymd=%d and rep_note='%s'", $yyyymm, $note);
if ($rows=getResultWithField2($query, $field, $res) <= 0) {
    $ryohi_han_kin = 0;
} else {
    $ryohi_han_kin = $res[0][0];
}

// ι���������
$ryohi_total_kin = $ryohi_seizo_kin + $ryohi_han_kin;

//// �̿���
// ��¤����
$res   = array();
$field = array();
$rows  = array();
$tsushin_seizo_kin = 0;
$note = '��¤�����̿���';
$query = sprintf("SELECT SUM(kin) FROM profit_loss_keihi_history WHERE pl_bs_ym>=%d and pl_bs_ym<=%d and note='%s'", $str_ym, $end_ym, $note);
if ($rows=getResultWithField2($query, $field, $res) <= 0) {
    $tsushin_seizo_kin = 0;
} else {
    $tsushin_seizo_kin = $res[0][0];
}
// �δ���
$res   = array();
$field = array();
$rows  = array();
$tsushin_han_kin = 0;
$note = '�δ����̿���';
$query = sprintf("SELECT SUM(kin) FROM profit_loss_keihi_history WHERE pl_bs_ym>=%d and pl_bs_ym<=%d and note='%s'", $str_ym, $end_ym, $note);
if ($rows=getResultWithField2($query, $field, $res) <= 0) {
    $tsushin_han_kin = 0;
} else {
    $tsushin_han_kin = $res[0][0];
}

// �̿�����
$tsushin_total_kin = $tsushin_seizo_kin + $tsushin_han_kin;

//// �����
// ��¤����
$res   = array();
$field = array();
$rows  = array();
$kaigi_seizo_kin = 0;
$note = '��¤��������';
$query = sprintf("SELECT SUM(kin) FROM profit_loss_keihi_history WHERE pl_bs_ym>=%d and pl_bs_ym<=%d and note='%s'", $str_ym, $end_ym, $note);
if ($rows=getResultWithField2($query, $field, $res) <= 0) {
    $kaigi_seizo_kin = 0;
} else {
    $kaigi_seizo_kin = $res[0][0];
}
// �δ���
$res   = array();
$field = array();
$rows  = array();
$kaigi_han_kin = 0;
$note = '�δ�������';
$query = sprintf("SELECT SUM(kin) FROM profit_loss_keihi_history WHERE pl_bs_ym>=%d and pl_bs_ym<=%d and note='%s'", $str_ym, $end_ym, $note);
if ($rows=getResultWithField2($query, $field, $res) <= 0) {
    $kaigi_han_kin = 0;
} else {
    $kaigi_han_kin = $res[0][0];
}

// �������
$kaigi_total_kin = $kaigi_seizo_kin + $kaigi_han_kin;

//// ���������
// ��¤����
$res   = array();
$field = array();
$rows  = array();
$kosai_seizo_kin = 0;
$note = '��¤������������';
$query = sprintf("SELECT SUM(kin) FROM profit_loss_keihi_history WHERE pl_bs_ym>=%d and pl_bs_ym<=%d and note='%s'", $str_ym, $end_ym, $note);
if ($rows=getResultWithField2($query, $field, $res) <= 0) {
    $kosai_seizo_kin = 0;
} else {
    $kosai_seizo_kin = $res[0][0];
}
// �δ���
$res   = array();
$field = array();
$rows  = array();
$kosai_han_kin = 0;
$note = '�δ�����������';
$query = sprintf("SELECT SUM(kin) FROM profit_loss_keihi_history WHERE pl_bs_ym>=%d and pl_bs_ym<=%d and note='%s'", $str_ym, $end_ym, $note);
if ($rows=getResultWithField2($query, $field, $res) <= 0) {
    $kosai_han_kin = 0;
} else {
    $kosai_han_kin = $res[0][0];
}

// �����������
$kosai_total_kin = $kosai_seizo_kin + $kosai_han_kin;

//// ����������
$senden_seizo_kin = 0;
// �δ���
$res   = array();
$field = array();
$rows  = array();
$senden_han_kin = 0;
$note = '�δ��񹭹�������';
$query = sprintf("SELECT rep_kin FROM financial_report_data WHERE rep_ymd=%d and rep_note='%s'", $yyyymm, $note);
if ($rows=getResultWithField2($query, $field, $res) <= 0) {
    $senden_han_kin = 0;
} else {
    $senden_han_kin = $res[0][0];
}

// ������������
$senden_total_kin = $senden_seizo_kin + $senden_han_kin;

//// ���²�¤��
// ��¤����
$res   = array();
$field = array();
$rows  = array();
$nizukuri_seizo_kin = 0;
$note = '��¤�����²�¤��';
$query = sprintf("SELECT SUM(kin) FROM profit_loss_keihi_history WHERE pl_bs_ym>=%d and pl_bs_ym<=%d and note='%s'", $str_ym, $end_ym, $note);
if ($rows=getResultWithField2($query, $field, $res) <= 0) {
    $nizukuri_seizo_kin = 0;
} else {
    $nizukuri_seizo_kin = $res[0][0];
}
// �δ���
$res   = array();
$field = array();
$rows  = array();
$nizukuri_han_kin = 0;
$note = '�δ����²�¤��';
$query = sprintf("SELECT SUM(kin) FROM profit_loss_keihi_history WHERE pl_bs_ym>=%d and pl_bs_ym<=%d and note='%s'", $str_ym, $end_ym, $note);
if ($rows=getResultWithField2($query, $field, $res) <= 0) {
    $nizukuri_han_kin = 0;
} else {
    $nizukuri_han_kin = $res[0][0];
}

// ���²�¤����
$nizukuri_total_kin = $nizukuri_seizo_kin + $nizukuri_han_kin;

//// �޽񶵰���
// ��¤����
$res   = array();
$field = array();
$rows  = array();
$tosyo_seizo_kin = 0;
$note = '��¤����޽񶵰���';
$query = sprintf("SELECT SUM(kin) FROM profit_loss_keihi_history WHERE pl_bs_ym>=%d and pl_bs_ym<=%d and note='%s'", $str_ym, $end_ym, $note);
if ($rows=getResultWithField2($query, $field, $res) <= 0) {
    $tosyo_seizo_kin = 0;
} else {
    $tosyo_seizo_kin = $res[0][0];
}
// �δ���
$res   = array();
$field = array();
$rows  = array();
$tosyo_han_kin = 0;
$note = '�δ���޽񶵰���';
$query = sprintf("SELECT SUM(kin) FROM profit_loss_keihi_history WHERE pl_bs_ym>=%d and pl_bs_ym<=%d and note='%s'", $str_ym, $end_ym, $note);
if ($rows=getResultWithField2($query, $field, $res) <= 0) {
    $tosyo_han_kin = 0;
} else {
    $tosyo_han_kin = $res[0][0];
}

// �޽񶵰�����
$tosyo_total_kin = $tosyo_seizo_kin + $tosyo_han_kin;

//// ��̳������
// ��¤����
$res   = array();
$field = array();
$rows  = array();
$gyomu_seizo_kin = 0;
$note = '��¤�����̳������';
$query = sprintf("SELECT rep_kin FROM financial_report_data WHERE rep_ymd=%d and rep_note='%s'", $yyyymm, $note);
if ($rows=getResultWithField2($query, $field, $res) <= 0) {
    $gyomu_seizo_kin = 0;
} else {
    $gyomu_seizo_kin = $res[0][0];
}
// �δ���
$res   = array();
$field = array();
$rows  = array();
$gyomu_han_kin = 0;
$note = '�δ����̳������';
$query = sprintf("SELECT rep_kin FROM financial_report_data WHERE rep_ymd=%d and rep_note='%s'", $yyyymm, $note);
if ($rows=getResultWithField2($query, $field, $res) <= 0) {
    $gyomu_han_kin = 0;
} else {
    $gyomu_han_kin = $res[0][0];
}

// ��̳��������
$gyomu_total_kin = $gyomu_seizo_kin + $gyomu_han_kin;

//// ���Ǹ���
// ��¤����
$res   = array();
$field = array();
$rows  = array();
$syozei_seizo_kin = 0;
$note = '��¤������Ǹ���';
$query = sprintf("SELECT SUM(kin) FROM profit_loss_keihi_history WHERE pl_bs_ym>=%d and pl_bs_ym<=%d and note='%s'", $str_ym, $end_ym, $note);
if ($rows=getResultWithField2($query, $field, $res) <= 0) {
    $syozei_seizo_kin = 0;
} else {
    $syozei_seizo_kin = $res[0][0];
}
// �δ���
$res   = array();
$field = array();
$rows  = array();
$syozei_han_kin = 0;
$note = '�δ�����Ǹ���';
$query = sprintf("SELECT rep_kin FROM financial_report_data WHERE rep_ymd=%d and rep_note='%s'", $yyyymm, $note);
if ($rows=getResultWithField2($query, $field, $res) <= 0) {
    $syozei_han_kin = 0;
} else {
    $syozei_han_kin = $res[0][0];
}

// ���Ǹ��ݹ��
$syozei_total_kin = $syozei_seizo_kin + $syozei_han_kin;

//// �������
// ��¤����
$res   = array();
$field = array();
$rows  = array();
$shiken_seizo_kin = 0;
$note = '��¤����������';
$query = sprintf("SELECT SUM(kin) FROM profit_loss_keihi_history WHERE pl_bs_ym>=%d and pl_bs_ym<=%d and note='%s'", $str_ym, $end_ym, $note);
if ($rows=getResultWithField2($query, $field, $res) <= 0) {
    $shiken_seizo_kin = 0;
} else {
    $shiken_seizo_kin = $res[0][0];
}
// �δ���
$shiken_han_kin = 0;

// ���������
$shiken_total_kin = $shiken_seizo_kin + $shiken_han_kin;

//// ������
// ��¤����
$res   = array();
$field = array();
$rows  = array();
$syuzen_seizo_kin = 0;
$note = '��¤��������';
$query = sprintf("SELECT SUM(kin) FROM profit_loss_keihi_history WHERE pl_bs_ym>=%d and pl_bs_ym<=%d and note='%s'", $str_ym, $end_ym, $note);
if ($rows=getResultWithField2($query, $field, $res) <= 0) {
    $syuzen_seizo_kin = 0;
} else {
    $syuzen_seizo_kin = $res[0][0];
}
// �δ���
$res   = array();
$field = array();
$rows  = array();
$syuzen_han_kin = 0;
$note = '�δ�������';
$query = sprintf("SELECT SUM(kin) FROM profit_loss_keihi_history WHERE pl_bs_ym>=%d and pl_bs_ym<=%d and note='%s'", $str_ym, $end_ym, $note);
if ($rows=getResultWithField2($query, $field, $res) <= 0) {
    $syuzen_han_kin = 0;
} else {
    $syuzen_han_kin = $res[0][0];
}

// ��������
$syuzen_total_kin = $syuzen_seizo_kin + $syuzen_han_kin;

//// ��̳�Ѿ�������
// ��¤����
$res   = array();
$field = array();
$rows  = array();
$jimu_seizo_kin = 0;
$note = '��¤�����̳�Ѿ�������';
$query = sprintf("SELECT SUM(kin) FROM profit_loss_keihi_history WHERE pl_bs_ym>=%d and pl_bs_ym<=%d and note='%s'", $str_ym, $end_ym, $note);
if ($rows=getResultWithField2($query, $field, $res) <= 0) {
    $jimu_seizo_kin = 0;
} else {
    $jimu_seizo_kin = $res[0][0];
}
// �δ���
$res   = array();
$field = array();
$rows  = array();
$jimu_han_kin = 0;
$note = '�δ����̳�Ѿ�������';
$query = sprintf("SELECT rep_kin FROM financial_report_data WHERE rep_ymd=%d and rep_note='%s'", $yyyymm, $note);
if ($rows=getResultWithField2($query, $field, $res) <= 0) {
    $jimu_han_kin = 0;
} else {
    $jimu_han_kin = $res[0][0];
}

// ��̳�Ѿ���������
$jimu_total_kin = $jimu_seizo_kin + $jimu_han_kin;

//// �����Ѿ�������
// ��¤����
$res   = array();
$field = array();
$rows  = array();
$kojyo_seizo_kin = 0;
$note = '��¤���񹩾��������';
$query = sprintf("SELECT SUM(kin) FROM profit_loss_keihi_history WHERE pl_bs_ym>=%d and pl_bs_ym<=%d and note='%s'", $str_ym, $end_ym, $note);
if ($rows=getResultWithField2($query, $field, $res) <= 0) {
    $kojyo_seizo_kin = 0;
} else {
    $kojyo_seizo_kin = $res[0][0];
}
// �δ���
$kojyo_han_kin = 0;

// �����Ѿ���������
$kojyo_total_kin = $kojyo_seizo_kin + $kojyo_han_kin;

//// ������
// ��¤����
$res   = array();
$field = array();
$rows  = array();
$syaryo_seizo_kin = 0;
$note = '��¤�����ξ��';
$query = sprintf("SELECT SUM(kin) FROM profit_loss_keihi_history WHERE pl_bs_ym>=%d and pl_bs_ym<=%d and note='%s'", $str_ym, $end_ym, $note);
if ($rows=getResultWithField2($query, $field, $res) <= 0) {
    $syaryo_seizo_kin = 0;
} else {
    $syaryo_seizo_kin = $res[0][0];
}
// �δ���
$res   = array();
$field = array();
$rows  = array();
$syaryo_han_kin = 0;
$note = '�δ����ξ��';
$query = sprintf("SELECT SUM(kin) FROM profit_loss_keihi_history WHERE pl_bs_ym>=%d and pl_bs_ym<=%d and note='%s'", $str_ym, $end_ym, $note);
if ($rows=getResultWithField2($query, $field, $res) <= 0) {
    $syaryo_han_kin = 0;
} else {
    $syaryo_han_kin = $res[0][0];
}

// ��������
$syaryo_total_kin = $syaryo_seizo_kin + $syaryo_han_kin;

//// �ݸ���
// ��¤����
$res   = array();
$field = array();
$rows  = array();
$hoken_seizo_kin = 0;
$note = '��¤�����ݸ���';
$query = sprintf("SELECT SUM(kin) FROM profit_loss_keihi_history WHERE pl_bs_ym>=%d and pl_bs_ym<=%d and note='%s'", $str_ym, $end_ym, $note);
if ($rows=getResultWithField2($query, $field, $res) <= 0) {
    $hoken_seizo_kin = 0;
} else {
    $hoken_seizo_kin = $res[0][0];
}
// �δ���
$res   = array();
$field = array();
$rows  = array();
$hoken_han_kin = 0;
$note = '�δ����ݸ���';
$query = sprintf("SELECT SUM(kin) FROM profit_loss_keihi_history WHERE pl_bs_ym>=%d and pl_bs_ym<=%d and note='%s'", $str_ym, $end_ym, $note);
if ($rows=getResultWithField2($query, $field, $res) <= 0) {
    $hoken_han_kin = 0;
} else {
    $hoken_han_kin = $res[0][0];
}

// �ݸ������
$hoken_total_kin = $hoken_seizo_kin + $hoken_han_kin;

//// ��ƻ��Ǯ��
// ��¤����
$res   = array();
$field = array();
$rows  = array();
$suido_seizo_kin = 0;
$note = '��¤�����ƻ��Ǯ��';
$query = sprintf("SELECT SUM(kin) FROM profit_loss_keihi_history WHERE pl_bs_ym>=%d and pl_bs_ym<=%d and note='%s'", $str_ym, $end_ym, $note);
if ($rows=getResultWithField2($query, $field, $res) <= 0) {
    $suido_seizo_kin = 0;
} else {
    $suido_seizo_kin = $res[0][0];
}
// �δ���
$res   = array();
$field = array();
$rows  = array();
$suido_han_kin = 0;
$note = '�δ����ƻ��Ǯ��';
$query = sprintf("SELECT SUM(kin) FROM profit_loss_keihi_history WHERE pl_bs_ym>=%d and pl_bs_ym<=%d and note='%s'", $str_ym, $end_ym, $note);
if ($rows=getResultWithField2($query, $field, $res) <= 0) {
    $suido_han_kin = 0;
} else {
    $suido_han_kin = $res[0][0];
}

// ��ƻ��Ǯ����
$suido_total_kin = $suido_seizo_kin + $suido_han_kin;

//// �������
// ��¤����
$res   = array();
$field = array();
$rows  = array();
$yachin_seizo_kin = 0;
$note = '��¤�����������';
$query = sprintf("SELECT rep_kin FROM financial_report_data WHERE rep_ymd=%d and rep_note='%s'", $yyyymm, $note);
if ($rows=getResultWithField2($query, $field, $res) <= 0) {
    $yachin_seizo_kin = 0;
} else {
    $yachin_seizo_kin = $res[0][0];
}
// �δ���
$res   = array();
$field = array();
$rows  = array();
$yachin_han_kin = 0;
$note = '�δ����������';
$query = sprintf("SELECT rep_kin FROM financial_report_data WHERE rep_ymd=%d and rep_note='%s'", $yyyymm, $note);
if ($rows=getResultWithField2($query, $field, $res) <= 0) {
    $yachin_han_kin = 0;
} else {
    $yachin_han_kin = $res[0][0];
}

// ������¹��
$yachin_total_kin = $yachin_seizo_kin + $yachin_han_kin;

//// ���ն�
// ��¤����
$kifu_seizo_kin = 0;
// �δ���
$res   = array();
$field = array();
$rows  = array();
$kifu_han_kin = 0;
$note = '�δ�����ն�';
$query = sprintf("SELECT SUM(kin) FROM profit_loss_keihi_history WHERE pl_bs_ym>=%d and pl_bs_ym<=%d and note='%s'", $str_ym, $end_ym, $note);
if ($rows=getResultWithField2($query, $field, $res) <= 0) {
    $kifu_han_kin = 0;
} else {
    $kifu_han_kin = $res[0][0];
}

// ���ն���
$kifu_total_kin = $kifu_seizo_kin + $kifu_han_kin;

//// �¼���
// ��¤����
$res   = array();
$field = array();
$rows  = array();
$chin_seizo_kin = 0;
$note = '��¤�����¼���';
$query = sprintf("SELECT SUM(kin) FROM profit_loss_keihi_history WHERE pl_bs_ym>=%d and pl_bs_ym<=%d and note='%s'", $str_ym, $end_ym, $note);
if ($rows=getResultWithField2($query, $field, $res) <= 0) {
    $chin_seizo_kin = 0;
} else {
    $chin_seizo_kin = $res[0][0];
}
// �δ���
$res   = array();
$field = array();
$rows  = array();
$chin_han_kin = 0;
$note = '�δ����¼���';
$query = sprintf("SELECT SUM(kin) FROM profit_loss_keihi_history WHERE pl_bs_ym>=%d and pl_bs_ym<=%d and note='%s'", $str_ym, $end_ym, $note);
if ($rows=getResultWithField2($query, $field, $res) <= 0) {
    $chin_han_kin = 0;
} else {
    $chin_han_kin = $res[0][0];
}

// �¼������
$chin_total_kin = $chin_seizo_kin + $chin_han_kin;

//// ����
// ��¤����
$res   = array();
$field = array();
$rows  = array();
$zappi_seizo_kin = 0;
$note = '��¤������';
$query = sprintf("SELECT rep_kin FROM financial_report_data WHERE rep_ymd=%d and rep_note='%s'", $yyyymm, $note);
if ($rows=getResultWithField2($query, $field, $res) <= 0) {
    $zappi_seizo_kin = 0;
} else {
    $zappi_seizo_kin = $res[0][0];
}
// �δ���
$res   = array();
$field = array();
$rows  = array();
$zappi_han_kin = 0;
$note = '�δ�����';
$query = sprintf("SELECT rep_kin FROM financial_report_data WHERE rep_ymd=%d and rep_note='%s'", $yyyymm, $note);
if ($rows=getResultWithField2($query, $field, $res) <= 0) {
    $zappi_han_kin = 0;
} else {
    $zappi_han_kin = $res[0][0];
}

// ������
$zappi_total_kin = $zappi_seizo_kin + $zappi_han_kin;

//// ���졼���б���
// ��¤����
$res   = array();
$field = array();
$rows  = array();
$clame_seizo_kin = 0;
$note = '��¤���񥯥졼���б���';
$query = sprintf("SELECT SUM(kin) FROM profit_loss_keihi_history WHERE pl_bs_ym>=%d and pl_bs_ym<=%d and note='%s'", $str_ym, $end_ym, $note);
if ($rows=getResultWithField2($query, $field, $res) <= 0) {
    $clame_seizo_kin = 0;
} else {
    $clame_seizo_kin = $res[0][0];
}
// �δ���
// ��¤����
$res   = array();
$field = array();
$rows  = array();
$clame_han_kin = 0;
$note = '�δ��񥯥졼���б���';
$query = sprintf("SELECT SUM(kin) FROM profit_loss_keihi_history WHERE pl_bs_ym>=%d and pl_bs_ym<=%d and note='%s'", $str_ym, $end_ym, $note);
if ($rows=getResultWithField2($query, $field, $res) <= 0) {
    $clame_han_kin = 0;
} else {
    $clame_han_kin = $res[0][0];
};

// ���졼���б�����
$clame_total_kin = $clame_seizo_kin + $clame_han_kin;

//// ����������
// ��¤����
$res   = array();
$field = array();
$rows  = array();
$genkasyo_seizo_kin = 0;
$note = '��¤���񸺲�������';
$query = sprintf("SELECT SUM(kin) FROM profit_loss_keihi_history WHERE pl_bs_ym>=%d and pl_bs_ym<=%d and note='%s'", $str_ym, $end_ym, $note);
if ($rows=getResultWithField2($query, $field, $res) <= 0) {
    $genkasyo_seizo_kin = 0;
} else {
    $genkasyo_seizo_kin = $res[0][0];
}
// �δ���
$res   = array();
$field = array();
$rows  = array();
$genkasyo_han_kin = 0;
$note = '�δ��񸺲�������';
$query = sprintf("SELECT SUM(kin) FROM profit_loss_keihi_history WHERE pl_bs_ym>=%d and pl_bs_ym<=%d and note='%s'", $str_ym, $end_ym, $note);
if ($rows=getResultWithField2($query, $field, $res) <= 0) {
    $genkasyo_han_kin = 0;
} else {
    $genkasyo_han_kin = $res[0][0];
}

// ������������
$genkasyo_total_kin = $genkasyo_seizo_kin + $genkasyo_han_kin;

// ��¤������
$seizo_keihi_total_kin = $ryohi_seizo_kin + $tsushin_seizo_kin + $kaigi_seizo_kin + $kosai_seizo_kin + $senden_seizo_kin + $nizukuri_seizo_kin + $tosyo_seizo_kin + $gyomu_seizo_kin + $syozei_seizo_kin + $shiken_seizo_kin + $syuzen_seizo_kin + $jimu_seizo_kin + $kojyo_seizo_kin + $syaryo_seizo_kin + $hoken_seizo_kin + $suido_seizo_kin + $yachin_seizo_kin + $kifu_seizo_kin + $chin_seizo_kin + $zappi_seizo_kin + $clame_seizo_kin + $genkasyo_seizo_kin;
// ������
$han_keihi_total_kin   = $ryohi_han_kin + $tsushin_han_kin + $kaigi_han_kin + $kosai_han_kin + $senden_han_kin + $nizukuri_han_kin + $tosyo_han_kin + $gyomu_han_kin + $syozei_han_kin + $shiken_han_kin + $syuzen_han_kin + $jimu_han_kin + $kojyo_han_kin + $syaryo_han_kin + $hoken_han_kin + $suido_han_kin + $yachin_han_kin + $kifu_han_kin + $chin_han_kin + $zappi_han_kin + $clame_han_kin + $genkasyo_han_kin;
// ϫ̳��ͷ�����
$keihi_total_kin = $seizo_keihi_total_kin + $han_keihi_total_kin;

// ��¤���񡦷�����δ���˺��۷׻�
// ������¤����
$res   = array();
$field = array();
$rows  = array();
$seikei_as_kin = 0;
$sum1 = '������¤����';
$query = sprintf("select SUM(kin) from profit_loss_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='%s'", $str_ym, $end_ym, $sum1);
if ($rows=getResultWithField2($query, $field, $res) <= 0) {
    $seikei_as_kin = 0;
} else {
    $seikei_as_kin = $res[0][0];
}
$seikei_as_sagaku = $seizo_keihi_total_kin - $seikei_as_kin;

// ���η�����δ����
$res   = array();
$field = array();
$rows  = array();
$hankei_as_kin = 0;
$sum1 = '���η���';
$query = sprintf("select SUM(kin) from profit_loss_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='%s'", $str_ym, $end_ym, $sum1);
if ($rows=getResultWithField2($query, $field, $res) <= 0) {
    $hankei_as_kin = 0;
} else {
    $hankei_as_kin = $res[0][0];
}
$hankei_as_sagaku = $han_keihi_total_kin - $hankei_as_kin;


//// ��¤���ѹ��
$seizo_hiyo_total_kin = $roumu_total_kin + $seizo_keihi_total_kin;
//// �δ�����
$han_all_total_kin    = $jin_total_kin + $han_keihi_total_kin;
//// �������
$all_keihi_total_kin  = $seizo_hiyo_total_kin+ $han_all_total_kin;

//// ��¤��������
// �������ê����
$res   = array();
$field = array();
$rows  = array();
$kishu_zairyo_kin = 0;
$note = '���󸶺����ڤ���¢��';
$query = sprintf("SELECT rep_kin FROM financial_report_data WHERE rep_ymd=%d and rep_note='%s'", $yyyymm, $note);
if ($rows=getResultWithField2($query, $field, $res) <= 0) {
    $kishu_zairyo_kin = 0;
} else {
    $kishu_zairyo_kin = $res[0][0];
}

// ��������������
$res   = array();
$field = array();
$rows  = array();
$touki_shiire_kin = 0;
$note = '��������������';
$query = sprintf("SELECT rep_kin FROM financial_report_data WHERE rep_ymd=%d and rep_note='%s'", $yyyymm, $note);
if ($rows=getResultWithField2($query, $field, $res) <= 0) {
    $touki_shiire_kin = 0;
} else {
    $touki_shiire_kin = $res[0][0];
}

// ������ף� ���������������������
$zai_total_1 = $kishu_zairyo_kin + $touki_shiire_kin;

// ��������ê����
$res   = array();
$field = array();
$rows  = array();
$kimatsu_zairyo_kin = 0;
$note = '�����������ڤ���¢��';
$query = sprintf("SELECT rep_kin FROM financial_report_data WHERE rep_ymd=%d and rep_note='%s'", $yyyymm, $note);
if ($rows=getResultWithField2($query, $field, $res) <= 0) {
    $kimatsu_zairyo_kin = 0;
} else {
    $kimatsu_zairyo_kin = $res[0][0];
}

// ������ף� ������ף��� ��������
$zai_total_2 = $zai_total_1 - $kimatsu_zairyo_kin;

//// ¾���꿶�ع�׻�
// ¾���꿶�ع�ʻ��6100 00 ��¾���꿶�ع������6400 00 �� �������ۡʣУ̡� 6420 00 �ι�ס����ա�
// ¾���꿶�ع�ʻ��6100 00
$res   = array();
$field = array();
$rows  = array();
$takan_shizai_kin = 0;
$sum1 = '6100';
$sum2 = '00';
$query = sprintf("select SUM(rep_de), SUM(rep_cr) from financial_report_month where rep_ymd>=%d and rep_ymd<=%d and rep_summary1='%s'", $str_ym, $end_ym, $sum1);
if ($rows=getResultWithField2($query, $field, $res) <= 0) {
    $takan_shizai_kin = 0;
} else {
    $takan_shizai_kin = -($res[0][0] - $res[0][1]);
}

// ¾���꿶�ع������6400 00
$res   = array();
$field = array();
$rows  = array();
$takan_sei_kin = 0;
$sum1 = '6400';
$sum2 = '00';
$query = sprintf("select SUM(rep_de), SUM(rep_cr) from financial_report_month where rep_ymd>=%d and rep_ymd<=%d and rep_summary1='%s'", $str_ym, $end_ym, $sum1);
if ($rows=getResultWithField2($query, $field, $res) <= 0) {
    $takan_sei_kin = 0;
} else {
    $takan_sei_kin = -($res[0][0] - $res[0][1]);
}

// �������ۡʣУ̡� 6420 00
$res   = array();
$field = array();
$rows  = array();
$gensai_pl_kin = 0;
$sum1 = '6420';
$sum2 = '00';
$query = sprintf("select SUM(rep_de), SUM(rep_cr) from financial_report_month where rep_ymd>=%d and rep_ymd<=%d and rep_summary1='%s'", $str_ym, $end_ym, $sum1);
if ($rows=getResultWithField2($query, $field, $res) <= 0) {
    $gensai_pl_kin = 0;
} else {
    $gensai_pl_kin = -($res[0][0] - $res[0][1]);
}

// ¾���꿶�ع� ��
$takan_total_kin = $takan_shizai_kin + $takan_sei_kin + $gensai_pl_kin;

// ���������� ��
$touki_zairyo_total = $zai_total_2 - $takan_total_kin;

// ��������¤����
$touki_total_seizo_hiyo = $touki_zairyo_total + $roumu_total_kin + $seizo_keihi_total_kin;

// ����ų���ê����
$res   = array();
$field = array();
$rows  = array();
$kishu_shikakari_kin = 0;
$note = '����ų���';
$query = sprintf("SELECT rep_kin FROM financial_report_data WHERE rep_ymd=%d and rep_note='%s'", $yyyymm, $note);
if ($rows=getResultWithField2($query, $field, $res) <= 0) {
    $kishu_shikakari_kin = 0;
} else {
    $kishu_shikakari_kin = $res[0][0];
}

// ������¤������
$toki_seizo_keihi_total = $touki_total_seizo_hiyo + $kishu_shikakari_kin;

// �����ų���ê����
$res   = array();
$field = array();
$rows  = array();
$kimatsu_shikakari_kin = 0;
$note = '�����ų���';
$query = sprintf("SELECT rep_kin FROM financial_report_data WHERE rep_ymd=%d and rep_note='%s'", $yyyymm, $note);
if ($rows=getResultWithField2($query, $field, $res) <= 0) {
    $kimatsu_shikakari_kin = 0;
} else {
    $kimatsu_shikakari_kin = $res[0][0];
}

// ����������¤����
$touki_seihin_seizo_genka = $toki_seizo_keihi_total - $kimatsu_shikakari_kin;

// ê����ɾ��»��CR��6090 00
$res   = array();
$field = array();
$rows  = array();
$hyokason_cr_kin = 0;
$sum1 = '6090';
$sum2 = '00';
$query = sprintf("select SUM(rep_de), SUM(rep_cr) from financial_report_month where rep_ymd>=%d and rep_ymd<=%d and rep_summary1='%s'", $str_ym, $end_ym, $sum1);
if ($rows=getResultWithField2($query, $field, $res) <= 0) {
    $hyokason_cr_kin = 0;
} else {
    $hyokason_cr_kin = $res[0][0] - $res[0][1];
}

// ����������¤�������۷׻�
// ������帶�� AS�����ν���
$res   = array();
$field = array();
$rows  = array();
$genka_as_kin = 0;
$sum1 = '������帶��';
$query = sprintf("select SUM(kin) from profit_loss_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='%s'", $str_ym, $end_ym, $sum1);
if ($rows=getResultWithField2($query, $field, $res) <= 0) {
    $genka_as_kin = 0;
} else {
    $genka_as_kin = $res[0][0];
}

$urigen_as_sagaku = $touki_seihin_seizo_genka - $genka_as_kin;


//// »�׷׻���
// ����
// ��������
$res   = array();
$field = array();
$rows  = array();
$uriage_kin = 0;
$sum1 = '��������';
$query = sprintf("select SUM(kin) from profit_loss_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='%s'", $str_ym, $end_ym, $sum1);
if ($rows=getResultWithField2($query, $field, $res) <= 0) {
    $uriage_kin = 0;
} else {
    $uriage_kin = $res[0][0];
}

// ��������׶��
$uriage_sourieki_kin = $uriage_kin - $touki_seihin_seizo_genka;

// ��������׺��ۡʾ�η軻����Ǥη׻���ASľ�ܤο�������ӡ�
// ������������ס�ASľ�ܤο�����
$res   = array();
$field = array();
$rows  = array();
$sourieki_as_kin = 0;
$sum1 = '�������������';
$query = sprintf("select SUM(kin) from profit_loss_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='%s'", $str_ym, $end_ym, $sum1);
if ($rows=getResultWithField2($query, $field, $res) <= 0) {
    $sourieki_as_kin = 0;
} else {
    $sourieki_as_kin = $res[0][0];
}
$sourieki_as_sagaku = $uriage_sourieki_kin - $sourieki_as_kin;

// �Ķ����׶��
$eigyo_rieki_kin = $uriage_sourieki_kin - $han_all_total_kin;

// �Ķ����׺��ۡʾ�η軻����Ǥη׻���ASľ�ܤο�������ӡ�
// ���αĶ����ס�ASľ�ܤο�����
$res   = array();
$field = array();
$rows  = array();
$eirieki_as_kin = 0;
$sum1 = '���αĶ�����';
$query = sprintf("select SUM(kin) from profit_loss_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='%s'", $str_ym, $end_ym, $sum1);
if ($rows=getResultWithField2($query, $field, $res) <= 0) {
    $eirieki_as_kin = 0;
} else {
    $eirieki_as_kin = $res[0][0];
}
$eirieki_as_sagaku = $eigyo_rieki_kin - $eirieki_as_kin;

// ������© 9101 00
$res   = array();
$field = array();
$rows  = array();
$uketori_risoku_kin = 0;
$sum1 = '9101';
$sum2 = '00';
$query = sprintf("select SUM(rep_de), SUM(rep_cr) from financial_report_month where rep_ymd>=%d and rep_ymd<=%d and rep_summary1='%s'", $str_ym, $end_ym, $sum1);
if ($rows=getResultWithField2($query, $field, $res) <= 0) {
    $uketori_risoku_kin = 0;
} else {
    $uketori_risoku_kin = -($res[0][0] - $res[0][1]);
}

// ���غ��� �� ���غ��� 9206 00�����աˡ� ���غ�» 9303 00
$res   = array();
$field = array();
$rows  = array();
$saeki_temp = 0;
$sum1 = '9206';
$sum2 = '00';
$query = sprintf("select SUM(rep_de), SUM(rep_cr) from financial_report_month where rep_ymd>=%d and rep_ymd<=%d and rep_summary1='%s'", $str_ym, $end_ym, $sum1);
if ($rows=getResultWithField2($query, $field, $res) <= 0) {
    $saeki_temp = 0;
} else {
    $saeki_temp = -($res[0][0] - $res[0][1]);
}
$res   = array();
$field = array();
$rows  = array();
$sason_temp = 0;
$sum1 = '9303';
$sum2 = '00';
$query = sprintf("select SUM(rep_de), SUM(rep_cr) from financial_report_month where rep_ymd>=%d and rep_ymd<=%d and rep_summary1='%s'", $str_ym, $end_ym, $sum1);
if ($rows=getResultWithField2($query, $field, $res) <= 0) {
    $sason_temp = 0;
} else {
    $sason_temp = $res[0][0] - $res[0][1];
}

// ���غ�»�׷׻�
if ($saeki_temp > $sason_temp) {
    $kawase_saeki_kin = $saeki_temp - $sason_temp;
    $kawase_sason_kin = 0;
} elseif($saeki_temp < $sason_temp) {
    $kawase_saeki_kin = 0;
    $kawase_sason_kin = $sason_temp - $saeki_temp;
} else {
    $kawase_saeki_kin = 0;
    $kawase_sason_kin = 0;
}

// �������ѱ� 9201 00
$res   = array();
$field = array();
$rows  = array();
$kotei_baieki_kin = 0;
$sum1 = '9201';
$sum2 = '00';
$query = sprintf("select SUM(rep_de), SUM(rep_cr) from financial_report_month where rep_ymd>=%d and rep_ymd<=%d and rep_summary1='%s'", $str_ym, $end_ym, $sum1);
if ($rows=getResultWithField2($query, $field, $res) <= 0) {
    $kotei_baieki_kin = 0;
} else {
    $kotei_baieki_kin = -($res[0][0] - $res[0][1]);
}

// ������
$res   = array();
$field = array();
$rows  = array();
$zatsu_syu_kin = 0;
$note = '������';
$query = sprintf("SELECT rep_kin FROM financial_report_data WHERE rep_ymd=%d and rep_note='%s'", $yyyymm, $note);
if ($rows=getResultWithField2($query, $field, $res) <= 0) {
    $zatsu_syu_kin = 0;
} else {
    $zatsu_syu_kin = $res[0][0];
}

// �Ķȳ����� ��
$eigai_syueki_kin = $uketori_risoku_kin + $kawase_saeki_kin + $kotei_baieki_kin + $zatsu_syu_kin;

// ���αĶȳ����׷׺��ۡʾ�η軻����Ǥη׻���ASľ�ܤο�������ӡ�
// ���αĶȳ����׷ס�ASľ�ܤο�����
$res   = array();
$field = array();
$rows  = array();
$gaisyu_as_kin = 0;
$sum1 = '���αĶȳ����׷�';
$query = sprintf("select SUM(kin) from profit_loss_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='%s'", $str_ym, $end_ym, $sum1);
if ($rows=getResultWithField2($query, $field, $res) <= 0) {
    $gaisyu_as_kin = 0;
} else {
    $gaisyu_as_kin = $res[0][0];
}
$gaisyu_as_sagaku = $eigai_syueki_kin - $gaisyu_as_kin;

// ��ʧ��© 8201
$res   = array();
$field = array();
$rows  = array();
$shiharai_risoku_kin = 0;
$sum1 = '8201';
$sum2 = '00';
$query = sprintf("select SUM(rep_de), SUM(rep_cr) from financial_report_month where rep_ymd>=%d and rep_ymd<=%d and rep_summary1='%s'", $str_ym, $end_ym, $sum1);
if ($rows=getResultWithField2($query, $field, $res) <= 0) {
    $shiharai_risoku_kin = 0;
} else {
    $shiharai_risoku_kin = $res[0][0] - $res[0][1];
}

// ����񻺽���»
$res   = array();
$field = array();
$rows  = array();
$kotei_jyoson_kin = 0;
$note = '����񻺽���»';
$query = sprintf("SELECT rep_kin FROM financial_report_data WHERE rep_ymd=%d and rep_note='%s'", $yyyymm, $note);
if ($rows=getResultWithField2($query, $field, $res) <= 0) {
    $kotei_jyoson_kin = 0;
} else {
    $kotei_jyoson_kin = $res[0][0];
}
// ��������»
$res   = array();
$field = array();
$rows  = array();
$kotei_baison_kin = 0;
$note = '��������»';
$query = sprintf("SELECT rep_kin FROM financial_report_data WHERE rep_ymd=%d and rep_note='%s'", $yyyymm, $note);
if ($rows=getResultWithField2($query, $field, $res) <= 0) {
    $kotei_baison_kin = 0;
} else {
    $kotei_baison_kin = $res[0][0];
}

// �Ķȳ����� ��
$eigai_hiyo_kin = $shiharai_risoku_kin + $kotei_jyoson_kin + $kotei_baison_kin + $kawase_sason_kin;

// ���αĶȳ����ѷ׺��ۡʾ�η軻����Ǥη׻���ASľ�ܤο�������ӡ�
// ���αĶȳ����ѷס�ASľ�ܤο�����
$res   = array();
$field = array();
$rows  = array();
$gaihiyo_as_kin = 0;
$sum1 = '���αĶȳ����ѷ�';
$query = sprintf("select SUM(kin) from profit_loss_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='%s'", $str_ym, $end_ym, $sum1);
if ($rows=getResultWithField2($query, $field, $res) <= 0) {
    $gaihiyo_as_kin = 0;
} else {
    $gaihiyo_as_kin = $res[0][0];
}
$gaihiyo_as_sagaku = $eigai_hiyo_kin - $gaihiyo_as_kin;

// �о����׶��
$keijyo_rieki_kin = $eigyo_rieki_kin + $eigai_syueki_kin - $eigai_hiyo_kin;

// ���ηо����׺��ۡʾ�η軻����Ǥη׻���ASľ�ܤο�������ӡ�
// ���ηо����ס�ASľ�ܤο�����
$res   = array();
$field = array();
$rows  = array();
$keirieki_as_kin = 0;
$sum1 = '���ηо�����';
$query = sprintf("select SUM(kin) from profit_loss_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='%s'", $str_ym, $end_ym, $sum1);
if ($rows=getResultWithField2($query, $field, $res) <= 0) {
    $keirieki_as_kin = 0;
} else {
    $keirieki_as_kin = $res[0][0];
}
$keirieki_as_sagaku = $keijyo_rieki_kin - $keirieki_as_kin;

// �ǰ������������׶��
$zeimae_jyunrieki_kin = $keijyo_rieki_kin;

// �����ǰ��������׶�ۺ��ۡʾ�η軻����Ǥη׻���ASľ�ܤο�������ӡ�
// �����ǰ��������׶�ۡ�ASľ�ܤο�����
$res   = array();
$field = array();
$rows  = array();
$zeimaerieki_as_kin = 0;
$sum1 = '�����ǰ��������׶��';
$query = sprintf("select SUM(kin) from profit_loss_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='%s'", $str_ym, $end_ym, $sum1);
if ($rows=getResultWithField2($query, $field, $res) <= 0) {
    $zeimaerieki_as_kin = 0;
} else {
    $zeimaerieki_as_kin = $res[0][0];
}
$zeimaerieki_as_sagaku = $zeimae_jyunrieki_kin - $zeimaerieki_as_kin;

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

// ˡ���ǡ���̱�ǵڤӻ�����
$hojin_jyumin_jigyo_kin = $hojin_jyumin_zei_kin + $jigyo_zei_kin;

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

// �Ƕ��פη׻�
$hojin_zeito_total_kin = $hojin_jyumin_zei_kin + $jigyo_zei_kin + $hojin_chosei_kin;

// ���������׶��
$toki_jyunrieki_kin = $zeimae_jyunrieki_kin - $hojin_zeito_total_kin;

// �������������׶�ۺ��ۡʾ�η軻����Ǥη׻���ASľ�ܤο�������ӡ�
// �������������׶�ۡ�ASľ�ܤο�����
$res   = array();
$field = array();
$rows  = array();
$tokijyunrieki_as_kin = 0;
$sum1 = '�������������׶��';
$query = sprintf("select SUM(kin) from profit_loss_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='%s'", $str_ym, $end_ym, $sum1);
if ($rows=getResultWithField2($query, $field, $res) <= 0) {
    $tokijyunrieki_as_kin = 0;
} else {
    $tokijyunrieki_as_kin = $res[0][0];
}
$tokijyunrieki_as_sagaku = $toki_jyunrieki_kin - $tokijyunrieki_as_kin;

//// �����������ư�׻���
// ���ܶ�
$res_k   = array();
$field_k = array();
$rows_k  = array();
$shihon_kin = 0;
$sum1 = '4101';
$sum2 = '00';
$query_k = sprintf("select rep_cri, rep_de, rep_cr from financial_report_cal where rep_ymd=%d and rep_summary1='%s' and rep_summary2='%s'", $nk_ki, $sum1, $sum2);
if ($rows_k=getResultWithField2($query_k, $field_k, $res_k) <= 0) {
    $shihon_kishu = 0;
} else {
    $shihon_kishu = -$res_k[0][0];
}
$res   = array();
$field = array();
$rows  = array();
$query = sprintf("select SUM(rep_de), SUM(rep_cr) from financial_report_month where rep_ymd>=%d and rep_ymd<=%d and rep_summary1='%s' and rep_summary2='%s'", $str_ym, $end_ym, $sum1, $sum2);
if ($rows=getResultWithField2($query, $field, $res) <= 0) {
    $shihon_hendo = 0;
} else {
    $shihon_hendo = $res[0][0] - $res[0][1];
}

// ���ܶ�Ĺ�
$shihon_kin = $shihon_kishu - $shihon_hendo;

//// ���ܾ�;��
// ���ܽ�����
$res_k   = array();
$field_k = array();
$rows_k  = array();
$shihon_jyunbi_kin = 0;
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
$query = sprintf("select SUM(rep_de), SUM(rep_cr) from financial_report_month where rep_ymd>=%d and rep_ymd<=%d and rep_summary1='%s' and rep_summary2='%s'", $str_ym, $end_ym, $sum1, $sum2);
if ($rows=getResultWithField2($query, $field, $res) <= 0) {
    $shihon_jyunbi_hendo = 0;
} else {
    $shihon_jyunbi_hendo = $res[0][0] - $res[0][1];
}

// ���ܽ�����Ĺ�
$shihon_jyunbi_kin = $shihon_jyunbi_kishu - $shihon_jyunbi_hendo;

// ����¾���ܾ�;��
$res_k   = array();
$field_k = array();
$rows_k  = array();
$sonota_shihon_jyoyo_kin = 0;
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
$query = sprintf("select SUM(rep_de), SUM(rep_cr) from financial_report_month where rep_ymd>=%d and rep_ymd<=%d and rep_summary1='%s' and rep_summary2='%s'", $str_ym, $end_ym, $sum1, $sum2);
if ($rows=getResultWithField2($query, $field, $res) <= 0) {
    $sonota_shihon_jyoyo_hendo = 0;
} else {
    $sonota_shihon_jyoyo_hendo = $res[0][0] - $res[0][1];
}

// ����¾���ܾ�;��Ĺ�
$sonota_shihon_jyoyo_kin = $sonota_shihon_jyoyo_kishu - $sonota_shihon_jyoyo_hendo;

//�ڻ��ܾ�;��۹��
$shihon_jyoyo_total_kishu = $shihon_jyunbi_kishu + $sonota_shihon_jyoyo_kishu;
$shihon_jyoyo_total_hendo = $shihon_jyunbi_hendo + $sonota_shihon_jyoyo_hendo;
$shihon_jyoyo_total_kin   = $shihon_jyunbi_kin + $sonota_shihon_jyoyo_kin;

//// ���׾�;��
// ���׽����� 4201 00
$res_k   = array();
$field_k = array();
$rows_k  = array();
$rieki_jyunbi_kin = 0;
$sum1 = '4201';
$sum2 = '00';
$query_k = sprintf("select rep_cri, rep_de, rep_cr from financial_report_cal where rep_ymd=%d and rep_summary1='%s' and rep_summary2='%s'", $nk_ki, $sum1, $sum2);
if ($rows_k=getResultWithField2($query_k, $field_k, $res_k) <= 0) {
    $rieki_jyunbi_kishu = 0;
} else {
    $rieki_jyunbi_kishu = -$res_k[0][0];
}
$res   = array();
$field = array();
$rows  = array();
$query = sprintf("select SUM(rep_de), SUM(rep_cr) from financial_report_month where rep_ymd>=%d and rep_ymd<=%d and rep_summary1='%s' and rep_summary2='%s'", $str_ym, $end_ym, $sum1, $sum2);
if ($rows=getResultWithField2($query, $field, $res) <= 0) {
    $rieki_jyunbi_kin = $rieki_jyunbi_kishu;
} else {
    $rieki_jyunbi_kin = $rieki_jyunbi_kishu + ($res[0][0] - $res[0][1]);
}

// ����¾���׾�;�� 4213 00
$res_k   = array();
$field_k = array();
$rows_k  = array();
$sonota_rieki_jyoyo_kin = 0;
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
$query = sprintf("select SUM(rep_de), SUM(rep_cr) from financial_report_month where rep_ymd>=%d and rep_ymd<=%d and rep_summary1='%s' and rep_summary2='%s'", $str_ym, $end_ym, $sum1, $sum2);
if ($rows=getResultWithField2($query, $field, $res) <= 0) {
    $sonota_rieki_jyoyo_hendo = 0;
} else {
    $sonota_rieki_jyoyo_hendo = $res[0][0] - $res[0][1];
}

// ����¾���׾�;��Ĺ�
$sonota_rieki_jyoyo_kin = $sonota_rieki_jyoyo_kishu - $sonota_rieki_jyoyo_hendo;

// �������׾�;�� 4204 00
$res_k   = array();
$field_k = array();
$rows_k  = array();
$kuri_rieki_jyoyo_kin = 0;
$sum1 = '4204';
$sum2 = '00';
$query_k = sprintf("select rep_cri, rep_de, rep_cr from financial_report_cal where rep_ymd=%d and rep_summary1='%s' and rep_summary2='%s'", $nk_ki, $sum1, $sum2);
if ($rows_k=getResultWithField2($query_k, $field_k, $res_k) <= 0) {
    $kuri_rieki_jyoyo_kishu = 0;
} else {
    $kuri_rieki_jyoyo_kishu = -$res_k[0][0];
}
$res   = array();
$field = array();
$rows  = array();
$query = sprintf("select SUM(rep_de), SUM(rep_cr) from financial_report_month where rep_ymd>=%d and rep_ymd<=%d and rep_summary1='%s' and rep_summary2='%s'", $str_ym, $end_ym, $sum1, $sum2);
if ($rows=getResultWithField2($query, $field, $res) <= 0) {
    $kuri_rieki_jyoyo_hendo = 0;
} else {
    $kuri_rieki_jyoyo_hendo = -($res[0][0] - $res[0][1]);
}

if ($mm != '03') {
    $kuri_rieki_jyoyo_hendo = $toki_jyunrieki_kin;
}

// �������׾�;��Ĺ�
$kuri_rieki_jyoyo_kin = $kuri_rieki_jyoyo_kishu + $kuri_rieki_jyoyo_hendo;

////�����׾�;��۹��
$rieki_jyoyo_total_kishu = $sonota_rieki_jyoyo_kishu + $kuri_rieki_jyoyo_kishu;
$rieki_jyoyo_total_hendo = $sonota_rieki_jyoyo_hendo + $kuri_rieki_jyoyo_hendo;
$rieki_jyoyo_total_kin   = $sonota_rieki_jyoyo_kin + $kuri_rieki_jyoyo_kin;

////�Խ�񻺹�ס�
$jyun_shisan_total_kishu = $shihon_kishu + $shihon_jyoyo_total_kishu + $rieki_jyoyo_total_kishu;
$jyun_shisan_total_hendo = $shihon_hendo + $shihon_jyoyo_total_hendo + $rieki_jyoyo_total_hendo;
$jyun_shisan_total_kin   = $shihon_kin + $shihon_jyoyo_total_kin + $rieki_jyoyo_total_kin;

if (isset($_POST['input_data'])) {                          // ����ǡ�������Ͽ
    ///////// ���ܤȥ���ǥå����δ�Ϣ�դ�
    $item = array();
    $item[0]  = "��ݶ�";
    $item[1]  = "��ʧ����";
    $item[2]  = "���߲�����";
    $item[3]  = "���եȥ�������";
    $item[4]  = "����¾̵�������";                       // �������Ѹ�
    $item[5]  = "���Ȱ�Ĺ�����ն�";                         // Ĺ�����ն�
    $item[6]  = "Ĺ����ʧ����";
    $item[7]  = "�����Ƕ�񻺡ʸ����";
    $item[8]  = "̤ʧ����";
    $item[9]  = "̤ʧˡ������";
    $item[10] = "�¤��";
    $item[11] = "�꡼����̳��û����";
    $item[12] = "�꡼����̳��Ĺ����";
    $item[13] = "��Ϳ������";
    $item[14] = "Ĺ��̤ʧ��";
    $item[15] = "�࿦���հ�����";
    $item[16] = "���ܶ�";
    $item[17] = "���ܾ�;��";
    $item[18] = "���׾�;��";
    $item[19] = "����";
    $item[20] = "����������¤����";
    $item[21] = "eca���²�¤��ʲ�¤ȯ�����";              // �δ���
    $item[22] = "eca�����";                              // �δ���
    $item[23] = "eca����";                                  // �δ���
    $item[24] = "eca��Ϳ";                                  // �δ����Ϳ����
    $item[25] = "eca��Ϳ�����ⷫ��";                        // �δ����Ϳ�����ⷫ��
    $item[26] = "eca������";                                // �δ���
    $item[27] = "eca�࿦��������";                          // �δ���
    $item[28] = "eca�̿���";                                // �δ���
    $item[29] = "eca������";                                // �δ���ι�������ȳ�����ĥ��
    $item[30] = "eca����������";                            // �δ���
    $item[31] = "eca���Ǹ���";                              // �δ�����Ǹ���
    $item[32] = "eca�¼���";                                // �δ���
    $item[33] = "eca������";                                // �δ���
    $item[34] = "eca���������";                            // �δ���
    $item[35] = "eca��̳�Ѿ�������";                        // �δ���
    $item[36] = "eca�ݸ���";                                // �δ���
    $item[37] = "eca��ƻ��Ǯ��";                            // �δ���
    $item[38] = "eca��ξ��";                                // �δ���
    $item[39] = "eca�޽񶵰���";                            // �δ���
    $item[40] = "eca�����������ն�";                        // �δ���
    $item[41] = "eca�����";                                // �δ���
    $item[42] = "eca������©�ڤӳ����";                    // �δ���
    $item[43] = "eca���غ���";                              // �δ���
    $item[44] = "eca�������ѱ�";                        // �δ���
    $item[45] = "eca����������";                            // �δ���
    $item[46] = "eca���غ�»";                              // �δ���
    $item[47] = "ˡ������Ĵ����";                           // �δ���
    $item[48] = "���׾�;��������";                     // �δ���
    ///////// �ƥǡ������ݴ�
    $input_data = array();
    $input_data[0]  = $urikake_kin;
    $input_data[1]  = $mae_hiyo_kin;
    $input_data[2]  = $kenkari_kin;
    $input_data[3]  = $soft_shisan_kin;
    $input_data[4]  = $shisetsu_shisan_kin;                 // �������Ѹ�
    $input_data[5]  = $choki_kashi_kin;
    $input_data[6]  = $choki_maebara_kin;
    $input_data[7]  = $kotei_kuri_zei_kin;
    $input_data[8]  = $miharai_hiyo_kin;
    $input_data[9]  = $miharai_hozei_kin;
    $input_data[10] = $azukari_kin;
    $input_data[11] = $lease_tanki_kin;
    $input_data[12] = $lease_choki_kin;
    $input_data[13] = $syoyo_hikiate_kin;
    $input_data[14] = $choki_miharai_kin;
    $input_data[15] = $taisyoku_hikiate_kin;
    $input_data[16] = $shihon_kin;
    $input_data[17] = $shihon_jyoyo_total_kin;
    $input_data[18] = $rieki_jyoyo_total_kin;
    $input_data[19] = $uriage_kin;
    $input_data[20] = $touki_seihin_seizo_genka;
    $input_data[21] = $nizukuri_han_kin;
    $input_data[22] = $yakuin_han_kin;
    $input_data[23] = $kyuryo_han_kin;
    $input_data[24] = $syoyo_teate_han_kin;
    $input_data[25] = $syoyo_hikiate_han_kin;
    $input_data[26] = $komon_han_kin;
    $input_data[27] = $tai_kyufu_han_kin;
    $input_data[28] = $tsushin_han_kin;
    $input_data[29] = $ryohi_han_kin;
    $input_data[30] = $genkasyo_han_kin;
    $input_data[31] = $syozei_han_kin;
    $input_data[32] = $chin_han_kin;
    $input_data[33] = $syuzen_han_kin;
    $input_data[34] = $kosai_han_kin;
    $input_data[35] = $jimu_han_kin;
    $input_data[36] = $hoken_han_kin;
    $input_data[37] = $suido_han_kin;
    $input_data[38] = $syaryo_han_kin;
    $input_data[39] = $tosyo_han_kin;
    $input_data[40] = $kifu_han_kin;
    $input_data[41] = $kaigi_han_kin;
    $input_data[42] = $uketori_risoku_kin;
    $input_data[43] = $kawase_saeki_kin;
    $input_data[44] = $kotei_baieki_kin;
    $input_data[45] = $toki_jyunrieki_kin;
    $input_data[46] = $kawase_sason_kin;
    $input_data[47] = $hojin_chosei_kin;
    $input_data[48] = $rieki_jyoyo_total_kishu;
    ///////// �ƥǡ�������Ͽ
    
    insert_date($item,$yyyymm,$input_data);
}

$csv_num = count($csv_data);
for ($r=0; $r<$csv_num; $r++) {
    $csv_data[$r][0] = mb_convert_encoding($csv_data[$r][0], 'SJIS', 'auto');   // CSV�Ѥ�EUC����SJIS��ʸ���������Ѵ�
}
/*
// eCA�� CSV�ǡ����ν���
// �������餬CSV�ե�����κ����ʰ���ե�����򥵡��С��˺�����
$outputFile = 'eca_data.csv';
$fp = fopen($outputFile, "w");
foreach($csv_data as $line){
    fputcsv($fp,$line);         // ������CSV�ե�����˽񤭽Ф�
}
fclose($fp);
// �������餬CSV�ե�����Υ�������ɡʥ����С������饤����ȡ�
touch($outputFile);
header("Content-Type: application/csv");
header("Content-Disposition: attachment; filename=".$outputFile);
header("Content-Length:".filesize($outputFile));
readfile($outputFile);
unlink("{$outputFile}");         // ��������ɸ�ե��������
*/

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
        <table class='winbox_field' width='100%' align='center' bgcolor='black' cellspacing='0' cellpadding='5'>
            <THEAD>
                <!-- �ơ��֥� �إå�����ɽ�� -->
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' colspan='3' align='center'>
                        <div class='pt10b'>�񻺤���</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' colspan='3' align='center'>
                        <div class='pt10b'>��ĵڤӽ�񻺤���</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none;border-right:none;border-top:none'>
                        <div class='pt11b'>��</div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' colspan='2' align='center'>
                        <div class='pt10b'>����</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center'>
                        <div class='pt10b'>���</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' colspan='2' align='center'>
                        <div class='pt10b'>����</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center'>
                        <div class='pt10b'>���</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none;border-right:none'>
                        <div class='pt11b'>��</div>
                    </td>
                </tr>
            </THEAD>
            <TFOOT>
                <!-- ���ߤϥեå����ϲ���ʤ� -->
            </TFOOT>
            <TBODY>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' colspan='2' style='border-bottom:none'>
                        <div class='pt10b'>ήư��</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none'>
                        <div class='pt11b'>(<?= number_format($ryudo_total_kin) ?>)</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' colspan='2' style='border-bottom:none'>
                        <div class='pt10b'>��Ĥ���</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none'>
                        <div class='pt11b'>��</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none;border-right:none'>
                        <div class='pt11b'>��</div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none;border-right:none'>
                        <div class='pt11b'>��</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' style='border-bottom:none'>
                        <div class='pt10b'>����ڤ��¶�</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none'>
                        <div class='pt11b'><?= number_format($genkin_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' colspan='2' style='border-bottom:none'>
                        <div class='pt10b'>ήư���</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none'>
                        <div class='pt11b'>(<?= number_format($ryudo_fusai_total_kin) ?>)</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none;border-right:none'>
                        <div class='pt11b'>��</div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none;border-right:none'>
                        <div class='pt11b'>��</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' style='border-bottom:none'>
                        <div class='pt10b'>��ݶ�</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none'>
                        <div class='pt11b'><?= number_format($urikake_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none;border-right:none'>
                        <div class='pt11b'>��</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' style='border-bottom:none'>
                        <div class='pt10b'>��ݶ�</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none'>
                        <div class='pt11b'><?= number_format($kaikake_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none;border-right:none'>
                        <div class='pt11b'>��</div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none;border-right:none'>
                        <div class='pt11b'>��</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' style='border-bottom:none'>
                        <div class='pt10b'>�ų���</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none'>
                        <div class='pt11b'><?= number_format($tai_shikakari_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none;border-right:none'>
                        <div class='pt11b'>��</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' style='border-bottom:none'>
                        <div class='pt10b'>�꡼����̳</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none'>
                        <div class='pt11b'><?= number_format($lease_tanki_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none;border-right:none'>
                        <div class='pt11b'>��</div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none;border-right:none'>
                        <div class='pt11b'>��</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' style='border-bottom:none'>
                        <div class='pt10b'>�������ڤ���¢��</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none'>
                        <div class='pt11b'><?= number_format($tai_zairyo_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none;border-right:none'>
                        <div class='pt11b'>��</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' style='border-bottom:none'>
                        <div class='pt10b'>̤ʧ��</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none'>
                        <div class='pt11b'><?= number_format($miharai_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none;border-right:none'>
                        <div class='pt11b'>��</div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none;border-right:none'>
                        <div class='pt11b'>��</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' style='border-bottom:none'>
                        <div class='pt10b'>��ʧ����</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none'>
                        <div class='pt11b'><?= number_format($mae_hiyo_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none;border-right:none'>
                        <div class='pt11b'>��</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' style='border-bottom:none'>
                        <div class='pt10b'>̤ʧ��������</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none'>
                        <div class='pt11b'><?= number_format($miharai_shozei_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none;border-right:none'>
                        <div class='pt11b'>��</div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none;border-right:none'>
                        <div class='pt11b'>��</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' style='border-bottom:none'>
                        <div class='pt10b'>̤������</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none'>
                        <div class='pt11b'><?= number_format($mishu_kin_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none;border-right:none'>
                        <div class='pt11b'>��</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' style='border-bottom:none'>
                        <div class='pt10b'>̤ʧˡ������</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none'>
                        <div class='pt11b'><?= number_format($miharai_hozei_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none;border-right:none'>
                        <div class='pt11b'>��</div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none;border-right:none'>
                        <div class='pt11b'>��</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' style='border-bottom:none'>
                        <div class='pt10b'>̤����������</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none'>
                        <div class='pt11b'><?= number_format($mishu_shozei_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' colspan='2' style='border-bottom:none'>
                        <div class='pt11b'>��</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none'>
                        <div class='pt11b'>��</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none;border-right:none'>
                        <div class='pt11b'>��</div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none;border-right:none'>
                        <div class='pt11b'>��</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' style='border-bottom:none'>
                        <div class='pt10b'>����¾��ήư��</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none'>
                        <div class='pt11b'><?= number_format($ta_ryudo_shisan_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none;border-right:none'>
                        <div class='pt11b'>��</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' style='border-bottom:none'>
                        <div class='pt10b'>̤ʧ����</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none'>
                        <div class='pt11b'><?= number_format($miharai_hiyo_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none;border-right:none'>
                        <div class='pt11b'>��</div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' colspan='2' style='border-bottom:none'>
                        <div class='pt10b'>��</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none'>
                        <div class='pt11b'>��</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none;border-right:none'>
                        <div class='pt11b'>��</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' style='border-bottom:none'>
                        <div class='pt10b'>�¤��</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none'>
                        <div class='pt11b'><?= number_format($azukari_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none;border-right:none'>
                        <div class='pt11b'>��</div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' colspan='2' style='border-bottom:none'>
                        <div class='pt10b'>��</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none'>
                        <div class='pt11b'>��</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none;border-right:none'>
                        <div class='pt11b'>��</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' style='border-bottom:none'>
                        <div class='pt10b'>��Ϳ������</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none'>
                        <div class='pt11b'><?= number_format($syoyo_hikiate_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none;border-right:none'>
                        <div class='pt11b'>��</div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' colspan='2' style='border-bottom:none'>
                        <div class='pt10b'>��</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none'>
                        <div class='pt11b'>��</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' colspan='2' style='border-bottom:none'>
                        <div class='pt10b'>��</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none'>
                        <div class='pt11b'>��</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none;border-right:none'>
                        <div class='pt11b'>��</div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' colspan='2' style='border-bottom:none'>
                        <div class='pt10b'>�����</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none'>
                        <div class='pt11b'>(<?= number_format($kotei_shisan_total_kin) ?>)</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' colspan='2' style='border-bottom:none'>
                        <div class='pt10b'>�������</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none'>
                        <div class='pt11b'>(<?= number_format($kotei_fusai_kin) ?>)</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none;border-right:none'>
                        <div class='pt11b'>��</div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' colspan='2' style='border-bottom:none'>
                        <div class='pt10b'>ͭ�������</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none'>
                        <div class='pt11b'>(<?= number_format($yukei_shisan_kin) ?>)</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none;border-right:none'>
                        <div class='pt11b'>��</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' style='border-bottom:none'>
                        <div class='pt10b'>�꡼����̳</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none'>
                        <div class='pt11b'><?= number_format($lease_choki_kin) ?>    </div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none;border-right:none'>
                        <div class='pt11b'>��</div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none;border-right:none'>
                        <div class='pt11b'>��</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' style='border-bottom:none'>
                        <div class='pt10b'>��ʪ</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none'>
                        <div class='pt11b'><?= number_format($tatemono_shisan_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none;border-right:none'>
                        <div class='pt11b'>��</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' style='border-bottom:none'>
                        <div class='pt10b'>Ĺ��̤ʧ��</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none'>
                        <div class='pt11b'><?= number_format($choki_miharai_kin) ?>    </div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none;border-right:none'>
                        <div class='pt11b'>��</div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none;border-right:none'>
                        <div class='pt11b'>��</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' style='border-bottom:none'>
                        <div class='pt10b'>�����ڤ�����</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none'>
                        <div class='pt11b'><?= number_format($kikai_shisan_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none;border-right:none'>
                        <div class='pt11b'>��</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' style='border-bottom:none'>
                        <div class='pt10b'>�࿦���հ�����</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none'>
                        <div class='pt11b'><?= number_format($taisyoku_hikiate_kin) ?>    </div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none;border-right:none'>
                        <div class='pt11b'>��</div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none;border-right:none'>
                        <div class='pt11b'>��</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' style='border-bottom:none'>
                        <div class='pt10b'>���ұ��¶�</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none'>
                        <div class='pt11b'><?= number_format($sharyo_shisan_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' colspan='2' style='border-bottom:none'>
                        <div class='pt11b'>��</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none'>
                        <div class='pt11b'>��</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none;border-right:none'>
                        <div class='pt11b'>��</div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none;border-right:none'>
                        <div class='pt11b'>��</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' style='border-bottom:none'>
                        <div class='pt10b'>������ڤ�����</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none'>
                        <div class='pt11b'><?= number_format($kougu_shisan_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none;border-right:none'>
                        <div class='pt11b'>��</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' style='border-bottom:none'>
                        <div class='pt10b'>��Ĺ��</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none'>
                        <div class='pt11b'><?= number_format($fusai_total_kin) ?>    </div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none;border-right:none'>
                        <div class='pt11b'>��</div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none;border-right:none'>
                        <div class='pt11b'>��</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' style='border-bottom:none'>
                        <div class='pt10b'>�꡼����</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none'>
                        <div class='pt11b'><?= number_format($lease_shisan_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' colspan='2' style='border-bottom:none'>
                        <div class='pt11b'>��</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none'>
                        <div class='pt11b'>��</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none;border-right:none'>
                        <div class='pt11b'>��</div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none;border-right:none'>
                        <div class='pt11b'>��</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' style='border-bottom:none'>
                        <div class='pt10b'>���߲�����</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none'>
                        <div class='pt11b'><?= number_format($kenkari_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' colspan='2' style='border-bottom:none'>
                        <div class='pt11b'>��</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none'>
                        <div class='pt11b'>��</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none;border-right:none'>
                        <div class='pt11b'>��</div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' colspan='2' style='border-bottom:none'>
                        <div class='pt10b'>��</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none'>
                        <div class='pt11b'>��</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' colspan='2' style='border-bottom:none'>
                        <div class='pt10b'>��</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none'>
                        <div class='pt11b'>��</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none;border-right:none'>
                        <div class='pt11b'>��</div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' colspan='2' style='border-bottom:none'>
                        <div class='pt10b'>̵�������</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none'>
                        <div class='pt11b'>(<?= number_format($mukei_shisan_kin) ?>)</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' colspan='2' style='border-bottom:none'>
                        <div class='pt10b'>��񻺤���</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none'>
                        <div class='pt11b'>��</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none;border-right:none'>
                        <div class='pt11b'>��</div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none;border-right:none'>
                        <div class='pt11b'>��</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' style='border-bottom:none'>
                        <div class='pt10b'>���ò�����</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none'>
                        <div class='pt11b'><?= number_format($denwa_shisan_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' colspan='2' style='border-bottom:none'>
                        <div class='pt10b'>�������</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none'>
                        <div class='pt11b'>��</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none;border-right:none'>
                        <div class='pt11b'>��</div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none;border-right:none'>
                        <div class='pt11b'>��</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' style='border-bottom:none'>
                        <div class='pt10b'>�������Ѹ�</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none'>
                        <div class='pt11b'><?= number_format($shisetsu_shisan_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' colspan='2' style='border-bottom:none'>
                        <div class='pt10b'>���ܶ�</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none'>
                        <div class='pt11b'>(<?= number_format($shihon_total_kin) ?>)</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none;border-right:none'>
                        <div class='pt11b'>��</div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none;border-right:none'>
                        <div class='pt11b'>��</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' style='border-bottom:none'>
                        <div class='pt10b'>���եȥ�����</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none'>
                        <div class='pt11b'><?= number_format($soft_shisan_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none;border-right:none'>
                        <div class='pt11b'>��</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' style='border-bottom:none'>
                        <div class='pt10b'>���ܶ�</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none'>
                        <div class='pt11b'><?= number_format($shihon_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none;border-right:none'>
                        <div class='pt11b'>��</div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' colspan='2' style='border-bottom:none'>
                        <div class='pt11b'>��</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none'>
                        <div class='pt11b'>��</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' colspan='2' style='border-bottom:none'>
                        <div class='pt10b'>���ܾ�;��</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none'>
                        <div class='pt11b'>(<?= number_format($tai_shihon_jyoyo_total_kin) ?>)</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none;border-right:none'>
                        <div class='pt11b'>��</div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' colspan='2' style='border-bottom:none'>
                        <div class='pt10b'>��񤽤�¾�λ�</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none'>
                        <div class='pt11b'>(<?= number_format($toshi_sonota_kin) ?>)</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none;border-right:none'>
                        <div class='pt11b'>��</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' style='border-bottom:none'>
                        <div class='pt10b'>���ܽ�����</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none'>
                        <div class='pt11b'><?= number_format($shihon_jyunbi_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none;border-right:none'>
                        <div class='pt11b'>��</div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none;border-right:none'>
                        <div class='pt11b'>��</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' style='border-bottom:none'>
                        <div class='pt10b'>Ĺ�����ն�</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none'>
                        <div class='pt11b'><?= number_format($choki_kashi_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none;border-right:none'>
                        <div class='pt11b'>��</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' style='border-bottom:none'>
                        <div class='pt10b'>����¾���ܾ�;��</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none'>
                        <div class='pt11b'><?= number_format($sonota_shihon_jyoyo_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none;border-right:none'>
                        <div class='pt11b'>��</div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none;border-right:none'>
                        <div class='pt11b'>��</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' style='border-bottom:none'>
                        <div class='pt10b'>Ĺ����ʧ����</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none'>
                        <div class='pt11b'><?= number_format($choki_maebara_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' colspan='2' style='border-bottom:none'>
                        <div class='pt10b'>���׾�;��</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none'>
                        <div class='pt11b'>(<?= number_format($tai_rieki_jyoyo_total_kin) ?>)</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none;border-right:none'>
                        <div class='pt11b'>��</div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none;border-right:none'>
                        <div class='pt11b'>��</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' style='border-bottom:none'>
                        <div class='pt10b'>�����Ƕ��</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none'>
                        <div class='pt11b'><?= number_format($kotei_kuri_zei_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none;border-right:none'>
                        <div class='pt11b'>��</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' style='border-bottom:none'>
                        <div class='pt10b'>����¾���׾�;��</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none'>
                        <div class='pt11b'><?= number_format($tai_sonota_rieki_jyoyo_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none;border-right:none'>
                        <div class='pt11b'>��</div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none;border-right:none'>
                        <div class='pt11b'>��</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' style='border-bottom:none'>
                        <div class='pt10b'>����¾�������</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none'>
                        <div class='pt11b'><?= number_format($sonota_toshi_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none;border-right:none'>
                        <div class='pt11b'>��</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' style='border-bottom:none'>
                        <div class='pt10b'>�������׾�;��</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none'>
                        <div class='pt11b'><?= number_format($tai_kuri_rieki_jyoyo_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none;border-right:none'>
                        <div class='pt11b'>��</div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' colspan='2' style='border-bottom:none'>
                        <div class='pt10b'>��</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none'>
                        <div class='pt11b'>��</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' colspan='2' style='border-bottom:none'>
                        <div class='pt10b'>��</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none'>
                        <div class='pt11b'>��</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none;border-right:none'>
                        <div class='pt11b'>��</div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' colspan='2' style='border-bottom:none'>
                        <div class='pt11b'>��</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none'>
                        <div class='pt11b'>��</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none;border-right:none'>
                        <div class='pt11b'>��</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' style='border-bottom:none'>
                        <div class='pt10b'>��񻺹��</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none'>
                        <div class='pt11b'><?= number_format($tai_jyun_shisan_total_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none;border-right:none'>
                        <div class='pt11b'>��</div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' colspan='2'>
                        <div class='pt10b'>��</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right'>
                        <div class='pt11b'>��</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' colspan='2'>
                        <div class='pt10b'>��</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right'>
                        <div class='pt11b'>��</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-top:none;border-right:none'>
                        ����
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' colspan='2'>
                        <div class='pt10b'>�񻺹��</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right'>
                        <div class='pt11b'><?= number_format($shisan_total_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' colspan='2'>
                        <div class='pt10b'>��ĵڤӽ�񻺹��</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right'>
                        <div class='pt11b'><?= number_format($fusai_jyunshi_total_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right'>
                        <div class='pt11b'><?= number_format($tai_sagaku_kin) ?></div>
                    </td>
                </tr>
                
                
            </TBODY>
        </table>
            </td></tr>
        </table> <!----------------- ���ߡ�End ------------------>
        
        <table bgcolor='#ffffff' align='center' cellspacing='0' cellpadding='10'>
            <tr><td> <!----------- ���ߡ�(�ǥ�������) ------------>
        <center>��»�׷׻����</center>
        <table class='winbox_field' width='100%' align='center' bgcolor='black' cellspacing='0' cellpadding='5'>
            <THEAD>
                <!-- �ơ��֥� �إå�����ɽ�� -->
            </THEAD>
            <TFOOT>
                <!-- ���ߤϥեå����ϲ���ʤ� -->
            </TFOOT>
            <TBODY>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='left' colspan='5' style='border-bottom:none'>
                        <div class='pt10b'>������  ��  ��  ��</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none;border-right:none;border-top:none'>
                        <div class='pt11b'>��</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none;border-right:none;border-top:none'>
                        <div class='pt11b'>��</div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none;border-right:none'>
                        <div class='pt10b'>��</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='left' style='border-bottom:none;border-right:none'>
                        <div class='pt10b'>����  ��  ��</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none;border-right:none'>
                        <div class='pt10b'>��</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none;border-right:none'>
                        <div class='pt10b'>��</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none'>
                        <div class='pt11b'><?= number_format($uriage_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none;border-right:none;border-top:none'>
                        <div class='pt11b'>��</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none;border-right:none;border-top:none'>
                        <div class='pt11b'>��</div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='left' colspan='5' style='border-bottom:none'>
                        <div class='pt10b'>������  ��  ��  ��</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none;border-right:none;border-top:none'>
                        <div class='pt11b'>��</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none;border-right:none;border-top:none'>
                        <div class='pt11b'>��</div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none;border-right:none'>
                        <div class='pt10b'>��</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='left' style='border-bottom:none;border-right:none'>
                        <div class='pt10b'>������  ��  ��  ��</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none;border-right:none'>
                        <div class='pt10b'>��</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none;border-right:none'>
                        <div class='pt10b'>��</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none'>
                        <div class='pt10b'>��</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none;border-right:none;border-top:none'>
                        <div class='pt11b'>��</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none;border-right:none;border-top:none'>
                        <div class='pt11b'>��</div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none;border-right:none'>
                        <div class='pt10b'>��</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none;border-right:none'>
                        <div class='pt10b'>������������¤����</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-right:none;border-bottom:1pt solid windowtext'>
                        <div class='pt11b'><?= number_format($touki_seihin_seizo_genka) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none;border-right:none'>
                        <div class='pt10b'>��</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:1pt solid windowtext'>
                        <div class='pt11b'><?= number_format($touki_seihin_seizo_genka) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-right:none;border-top:none'>
                        <div class='pt11b'>��</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none;border-right:none;border-top:none'>
                        <div class='pt11b'>��</div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none;border-right:none'>
                        <div class='pt10b'>��</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none;border-right:none'>
                        <div class='pt10b'>����������׶��</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none;border-right:none'>
                        <div class='pt10b'>��</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none;border-right:none'>
                        <div class='pt10b'>��</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none'>
                        <div class='pt11b'><?= number_format($uriage_sourieki_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right'>
                        <div class='pt11b'><?= number_format($sourieki_as_sagaku) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none;border-right:none;border-top:none'>
                        <div class='pt11b'>��</div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none;border-right:none'>
                        <div class='pt10b'>��</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='left' style='border-bottom:none;border-right:none'>
                        <div class='pt10b'>����������ڤӰ��̴�����</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none;border-right:none'>
                        <div class='pt10b'>��</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none;border-right:none'>
                        <div class='pt10b'>��</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:1pt solid windowtext'>
                        <div class='pt11b'><?= number_format($han_all_total_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-right:none'>
                        <div class='pt11b'>��</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none;border-right:none;border-top:none'>
                        <div class='pt11b'>��</div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none;border-right:none'>
                        <div class='pt10b'>��</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none;border-right:none'>
                        <div class='pt10b'>���� �� �� �� �� ��</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none;border-right:none'>
                        <div class='pt10b'>��</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none;border-right:none'>
                        <div class='pt10b'>��</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none'>
                        <div class='pt11b'><?= number_format($eigyo_rieki_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right'>
                        <div class='pt11b'><?= number_format($eirieki_as_sagaku) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none;border-right:none;border-top:none'>
                        <div class='pt11b'>��</div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='left' colspan='5' style='border-bottom:none'>
                        <div class='pt10b'>������  ��  ��  ��  ��</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none;border-right:none;border-top:none'>
                        <div class='pt11b'>��</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none;border-right:none;border-top:none'>
                        <div class='pt11b'>��</div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none;border-right:none'>
                        <div class='pt10b'>��</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='left' style='border-bottom:none;border-right:none'>
                        <div class='pt10b'>��  ��  ��  ©</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none;border-right:none'>
                        <div class='pt11b'><?= number_format($uketori_risoku_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none;border-right:none'>
                        <div class='pt10b'>��</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none'>
                        <div class='pt10b'>��</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-right:none;border-top:none'>
                        <div class='pt11b'>��</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none;border-right:none;border-top:none'>
                        <div class='pt11b'>��</div>
                    </td>
                </tr>
                <?php
                if ($kawase_saeki_kin <> 0) {
                ?>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none;border-right:none'>
                        <div class='pt10b'>��</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='left' style='border-bottom:none;border-right:none'>
                        <div class='pt10b'>�١��ء�������</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none;border-right:none'>
                        <div class='pt11b'><?= number_format($kawase_saeki_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none;border-right:none'>
                        <div class='pt10b'>��</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none'>
                        <div class='pt10b'>��</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-right:none;border-top:none'>
                        <div class='pt10b'>��</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none;border-right:none'>
                        <div class='pt10b'>��</div>
                    </td>
                </tr>
                <?php
                }
                ?>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none;border-right:none'>
                        <div class='pt10b'>��</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='left' style='border-bottom:none;border-right:none'>
                        <div class='pt10b'>�������ѱ�</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none;border-right:none'>
                        <div class='pt11b'><?= number_format($kotei_baieki_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none;border-right:none'>
                        <div class='pt10b'>��</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none'>
                        <div class='pt10b'>��</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-right:none;border-top:none'>
                        <div class='pt10b'>��</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none;border-right:none'>
                        <div class='pt10b'>��</div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none;border-right:none'>
                        <div class='pt10b'>��</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='left' style='border-bottom:none;border-right:none'>
                        <div class='pt10b'>��    ��    ��</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-right:none;border-bottom:1pt solid windowtext'>
                        <div class='pt11b'><?= number_format($zatsu_syu_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none;border-right:none'>
                        <div class='pt10b'>��</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none'>
                        <div class='pt11b'><?= number_format($eigai_syueki_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right'>
                        <div class='pt11b'><?= number_format($gaisyu_as_sagaku) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='left' style='border-bottom:none;border-right:none'>
                        ����
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='left' colspan='5' style='border-bottom:none'>
                        <div class='pt10b'>������  ��  ��  ��  ��</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none;border-right:none'>
                        <div class='pt11b'>��</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none;border-right:none'>
                        <div class='pt11b'>��</div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none;border-right:none'>
                        <div class='pt10b'>��</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='left' style='border-bottom:none;border-right:none'>
                        <div class='pt10b'>�١�ʧ������©</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none;border-right:none'>
                        <div class='pt11b'><?= number_format($shiharai_risoku_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none;border-right:none'>
                        <div class='pt10b'>��</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none'>
                        <div class='pt10b'>��</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none;border-right:none'>
                        <div class='pt11b'>��</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none;border-right:none'>
                        <div class='pt11b'>��</div>
                    </td>
                </tr>
                <?php
                if ($kawase_sason_kin <> 0) {
                ?>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none;border-right:none'>
                        <div class='pt10b'>��</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='left' style='border-bottom:none;border-right:none'>
                        <div class='pt10b'>�١��ء�����»</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none;border-right:none'>
                        <div class='pt11b'><?= number_format($kawase_sason_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none;border-right:none'>
                        <div class='pt10b'>��</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none'>
                        <div class='pt10b'>��</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none;border-right:none'>
                        <div class='pt11b'>��</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none;border-right:none'>
                        <div class='pt11b'>��</div>
                    </td>
                </tr>
                <?php
                }
                ?>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none;border-right:none'>
                        <div class='pt10b'>��</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='left' style='border-bottom:none;border-right:none'>
                        <div class='pt10b'>��������»</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none;border-right:none'>
                        <div class='pt11b'><?= number_format($kotei_baison_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none;border-right:none'>
                        <div class='pt10b'>��</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none'>
                        <div class='pt11b'>��</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-right:none'>
                        <div class='pt11b'>��</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-right:none;border-bottom:none'>
                        <div class='pt11b'>��</div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none;border-right:none'>
                        <div class='pt10b'>��</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='left' style='border-bottom:none;border-right:none'>
                        <div class='pt10b'>����񻺽���»</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-right:none;border-bottom:1pt solid windowtext'>
                        <div class='pt11b'><?= number_format($kotei_jyoson_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none;border-right:none'>
                        <div class='pt10b'>��</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:1pt solid windowtext'>
                        <div class='pt11b'><?= number_format($eigai_hiyo_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right'>
                        <div class='pt11b'><?= number_format($gaihiyo_as_sagaku) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='left' style='border-bottom:none;border-right:none'>
                        �����Ȱ���
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none;border-right:none'>
                        <div class='pt10b'>��</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none;border-right:none'>
                        <div class='pt10b'>���� �� �� �� �� ��</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none;border-right:none'>
                        <div class='pt10b'>��</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none;border-right:none'>
                        <div class='pt10b'>��</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:1pt solid windowtext'>
                        <div class='pt11b'><?= number_format($keijyo_rieki_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right'>
                        <div class='pt11b'><?= number_format($keirieki_as_sagaku) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-right:none;border-bottom:none'>
                        <div class='pt11b'>��</div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='left' colspan='2' style='border-bottom:none;border-right:none'>
                        <div class='pt10b'>���ǰ������������׶��</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none;border-right:none'>
                        <div class='pt10b'>��</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none;border-right:none'>
                        <div class='pt10b'>��</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none'>
                        <div class='pt11b'><?= number_format($zeimae_jyunrieki_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right'>
                        <div class='pt11b'><?= number_format($zeimaerieki_as_sagaku) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-right:none;border-bottom:none'>
                        <div class='pt11b'>��</div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='left' colspan='2' style='border-bottom:none;border-right:none'>
                        <div class='pt10b'>��ˡ���ǡ���̱�ǵڤӻ�����</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none;border-right:none'>
                        <div class='pt11b'><?= number_format($hojin_jyumin_jigyo_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none;border-right:none'>
                        <div class='pt10b'>��</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none'>
                        <div class='pt10b'>��</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-right:none;border-bottom:none'>
                        <div class='pt11b'>��</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-right:none;border-bottom:none'>
                        <div class='pt11b'>��</div>
                    </td>
                </tr>
                <!--
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='left' colspan='2'>
                        <div class='pt10b'>����ǯ��ˡ������</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right'>
                        <div class='pt11b'><?= number_format($kishu_zairyo_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center'>
                        <div class='pt10b'>��</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center'>
                        <div class='pt10b'>��</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-right:none'>
                        <div class='pt11b'>��</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-right:none;border-bottom:none'>
                        <div class='pt11b'>��</div>
                    </td>
                </tr>
                -->
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='left' colspan='2' style='border-bottom:none;border-right:none'>
                        <div class='pt10b'>��ˡ������Ĵ����</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-right:none;border-bottom:1pt solid windowtext'>
                        <div class='pt11b'><?= number_format($hojin_chosei_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none;border-right:none'>
                        <div class='pt10b'>��</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:1pt solid windowtext'>
                        <div class='pt11b'><?= number_format($hojin_zeito_total_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-right:none'>
                        <div class='pt11b'>��</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-right:none;border-bottom:none'>
                        <div class='pt11b'>��</div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='left' colspan='2' style='border-right:none'>
                        <div class='pt10b'>�����������׶��</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-right:none'>
                        <div class='pt10b'>��</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-right:none'>
                        <div class='pt10b'>��</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right'>
                        <div class='pt11b'><?= number_format($toki_jyunrieki_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right'>
                        <div class='pt11b'><?= number_format($tokijyunrieki_as_sagaku) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-right:none;border-bottom:none'>
                        <div class='pt11b'>��</div>
                    </td>
                </tr>
            </TBODY>
        </table>
            </td></tr>
        </table> <!----------------- ���ߡ�End ------------------>
                
        <table bgcolor='#ffffff' align='center' cellspacing='0' cellpadding='10'>
            <tr><td> <!----------- ���ߡ�(�ǥ�������) ------------>
        <center>����¤���������</center>
        <table class='winbox_field' width='100%' align='center' bgcolor='black' cellspacing='0' cellpadding='5'>
            <THEAD>
                <!-- �ơ��֥� �إå�����ɽ�� -->
            </THEAD>
            <TFOOT>
                <!-- ���ߤϥեå����ϲ���ʤ� -->
            </TFOOT>
            <TBODY>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none;border-right:none'>
                        <div class='pt10b'>������    ��    ��</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none;border-right:none'>
                        <div class='pt10b'>��</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none;border-right:none'>
                        <div class='pt10b'>��</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none'>
                        <div class='pt10b'>��</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none;border-right:none'>
                        <div class='pt10b'>��</div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none;border-right:none'>
                        <div class='pt10b'>�����������ê����</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none;border-right:none'>
                        <div class='pt10b'>��</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none;border-right:none'>
                        <div class='pt11b'><?= number_format($kishu_zairyo_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none'>
                        <div class='pt10b'>��</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none;border-right:none'>
                        <div class='pt10b'>��</div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none;border-right:none'>
                        <div class='pt10b'>������������������</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none;border-right:none'>
                        <div class='pt10b'>��</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-right:none;border-bottom:1pt solid windowtext'>
                        <div class='pt11b'><?= number_format($touki_shiire_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none'>
                        <div class='pt10b'>��</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none;border-right:none'>
                        <div class='pt10b'>��</div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none;border-right:none'>
                        <div class='pt10b'>������      ��</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none;border-right:none'>
                        <div class='pt10b'>��</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none;border-right:none'>
                        <div class='pt11b'><?= number_format($zai_total_1) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none'>
                        <div class='pt10b'>��</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none;border-right:none'>
                        <div class='pt10b'>��</div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none;border-right:none'>
                        <div class='pt10b'>������������ê����</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none;border-right:none'>
                        <div class='pt10b'>��</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-right:none;border-bottom:1pt solid windowtext'>
                        <div class='pt11b'><?= number_format($kimatsu_zairyo_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none'>
                        <div class='pt10b'>��</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none;border-right:none'>
                        <div class='pt10b'>��</div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none;border-right:none'>
                        <div class='pt10b'>������      ��</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none;border-right:none'>
                        <div class='pt10b'>��</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none;border-right:none'>
                        <div class='pt11b'><?= number_format($zai_total_2) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none'>
                        <div class='pt10b'>��</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none;border-right:none'>
                        <div class='pt10b'>��</div>
                    </td>
                </tr>
                <!--
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center'>
                        <div class='pt10b'>����ê����ɾ��»</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center'>
                        <div class='pt10b'>��</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right'>
                        <div class='pt11b'>��</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center'>
                        <div class='pt10b'>��</div>
                    </td>
                </tr>
                -->
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none;border-right:none'>
                        <div class='pt10b'>����¾���꿶�ع�</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none;border-right:none'>
                        <div class='pt10b'>��</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-right:none;border-bottom:1pt solid windowtext'>
                        <div class='pt11b'><?= number_format($takan_total_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none'>
                        <div class='pt10b'>��</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none;border-right:none'>
                        <div class='pt10b'>��</div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none;border-right:none'>
                        <div class='pt10b'>��������������</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none;border-right:none'>
                        <div class='pt10b'>��</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none;border-right:none'>
                        <div class='pt10b'>��</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none'>
                        <div class='pt11b'><?= number_format($touki_zairyo_total) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none;border-right:none'>
                        <div class='pt10b'>��</div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' colspan='4' style='border-bottom:none'>
                        <div class='pt10b'>��</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none;border-right:none'>
                        <div class='pt10b'>��</div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none;border-right:none'>
                        <div class='pt10b'>����ϫ    ̳    ��</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none;border-right:none'>
                        <div class='pt10b'>��</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none;border-right:none'>
                        <div class='pt10b'>��</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none'>
                        <div class='pt11b'>��</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none;border-right:none'>
                        <div class='pt10b'>��</div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none;border-right:none'>
                        <div class='pt10b'>������ϫ̳��</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none;border-right:none'>
                        <div class='pt10b'>��</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none;border-right:none'>
                        <div class='pt10b'>��</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none'>
                        <div class='pt11b'><?= number_format($roumu_total_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none;border-right:none'>
                        <div class='pt10b'>��</div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' colspan='4' style='border-bottom:none'>
                        <div class='pt10b'>��</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none;border-right:none'>
                        <div class='pt10b'>��</div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none;border-right:none'>
                        <div class='pt10b'>������  ¤  ��  ��</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none;border-right:none'>
                        <div class='pt10b'>��</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none;border-right:none'>
                        <div class='pt10b'>��</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none'>
                        <div class='pt11b'>��</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none;border-right:none'>
                        <div class='pt10b'>��</div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none;border-right:none'>
                        <div class='pt10b'>��������¤����</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none;border-right:none'>
                        <div class='pt10b'>��</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none;border-right:none'>
                        <div class='pt10b'>��</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:1pt solid windowtext'>
                        <div class='pt11b'><?= number_format($seizo_keihi_total_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none;border-right:none'>
                        <div class='pt10b'>��</div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none;border-right:none'>
                        <div class='pt10b'>����������¤����</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none;border-right:none'>
                        <div class='pt10b'>��</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none;border-right:none'>
                        <div class='pt10b'>��</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none'>
                        <div class='pt11b'><?= number_format($touki_total_seizo_hiyo) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none;border-right:none'>
                        <div class='pt10b'>��</div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none;border-right:none'>
                        <div class='pt10b'>������ų���ê����</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none;border-right:none'>
                        <div class='pt10b'>��</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none;border-right:none'>
                        <div class='pt10b'>��</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:1pt solid windowtext'>
                        <div class='pt11b'><?= number_format($kishu_shikakari_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none;border-right:none'>
                        <div class='pt10b'>��</div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none;border-right:none'>
                        <div class='pt10b'>����      ��</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none;border-right:none'>
                        <div class='pt10b'>��</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none;border-right:none'>
                        <div class='pt10b'>��</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none'>
                        <div class='pt11b'><?= number_format($toki_seizo_keihi_total) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none;border-right:none'>
                        <div class='pt10b'>��</div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none;border-right:none'>
                        <div class='pt10b'>�������ų���ê����</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none;border-right:none'>
                        <div class='pt10b'>��</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none;border-right:none'>
                        <div class='pt10b'>��</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:1pt solid windowtext'>
                        <div class='pt11b'><?= number_format($kimatsu_shikakari_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-right:none'>
                        <div class='pt10b'>��</div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none;border-right:none'>
                        <div class='pt10b'>������������¤����</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none;border-right:none'>
                        <div class='pt10b'>��</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none;border-right:none'>
                        <div class='pt10b'>��</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:1pt solid windowtext'>
                        <div class='pt11b'><?= number_format($touki_seihin_seizo_genka) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right'>
                        <div class='pt11b'><?= number_format($urigen_as_sagaku) ?></div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' colspan='4' style='border-bottom:none'>
                        <div class='pt10b'>��</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none;border-right:none'>
                        <div class='pt10b'>��</div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' colspan='2' style='border-right:none'>
                        <div class='pt10b'>��������ê����ˤϡ�ê����ɾ��»</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-right:none'>
                        <div class='pt11b'><?= number_format($hyokason_cr_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='left'>
                        <div class='pt10b'>�ߤ��ޤޤ�Ƥ���ޤ���</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none;border-right:none'>
                        <div class='pt10b'>��</div>
                    </td>
                </tr>
            </TBODY>
        </table>
            </td></tr>
        </table> <!----------------- ���ߡ�End ------------------>
        
        <table bgcolor='#ffffff' align='center' cellspacing='0' cellpadding='10'>
            <tr><td> <!----------- ���ߡ�(�ǥ�������) ------------>
        <center>�ʷ������ٽ��</center>
        <table class='winbox_field' width='100%' align='center' bgcolor='black' cellspacing='0' cellpadding='5'>
            <THEAD>
                <!-- �ơ��֥� �إå�����ɽ�� -->
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center'>
                        <div class='pt10b'>����</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center'>
                        <div class='pt10b'>��¤����</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center'>
                        <div class='pt10b'>�δ���ڤӰ��̴�����</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center'>
                        <div class='pt10b'>���</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none;border-right:none;border-top:none'>
                        <div class='pt10b'>��</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none;border-right:none;border-top:none'>
                        <div class='pt10b'>��</div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none'>
                        <div class='pt10b'>��</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none'>
                        <div class='pt10b'>��ϫ̳���</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none'>
                        <div class='pt10b'>�ʿͷ����</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none'>
                        <div class='pt10b'>��</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none;border-right:none'>
                        <div class='pt10b'>��</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none;border-right:none'>
                        <div class='pt10b'>��</div>
                    </td>
                </tr>
            </THEAD>
            <TFOOT>
                <!-- ���ߤϥեå����ϲ���ʤ� -->
            </TFOOT>
            <TBODY>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none'>
                        <div class='pt10b'>�����</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none'>
                        <div class='pt11b'><?= number_format($yakuin_seizo_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none'>
                        <div class='pt11b'><?= number_format($yakuin_han_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none'>
                        <div class='pt11b'><?= number_format($yakuin_total_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none;border-right:none'>
                        <div class='pt10b'>��</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none;border-right:none'>
                        <div class='pt10b'>��</div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none'>
                        <div class='pt10b'>��������</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none'>
                        <div class='pt11b'><?= number_format($kyuryo_seizo_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none'>
                        <div class='pt11b'><?= number_format($kyuryo_han_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none'>
                        <div class='pt11b'><?= number_format($kyuryo_total_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none;border-right:none;border-top:none'>
                        <div class='pt10b'>��</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none;border-right:none;border-top:none'>
                        <div class='pt10b'>��</div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none'>
                        <div class='pt10b'>��Ϳ����</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none'>
                        <div class='pt11b'><?= number_format($syoyo_teate_seizo_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none'>
                        <div class='pt11b'><?= number_format($syoyo_teate_han_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none'>
                        <div class='pt11b'><?= number_format($syoyo_teate_total_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none;border-right:none;border-top:none'>
                        <div class='pt10b'>��</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none;border-right:none;border-top:none'>
                        <div class='pt10b'>��</div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none'>
                        <div class='pt10b'>������</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none'>
                        <div class='pt11b'>��</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none'>
                        <div class='pt11b'><?= number_format($komon_han_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none'>
                        <div class='pt11b'><?= number_format($komon_total_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none;border-right:none;border-top:none'>
                        <div class='pt10b'>��</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none;border-right:none;border-top:none'>
                        <div class='pt10b'>��</div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none'>
                        <div class='pt10b'>����ʡ����</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none'>
                        <div class='pt11b'><?= number_format($fukuri_seizo_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none'>
                        <div class='pt11b'><?= number_format($fukuri_han_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none'>
                        <div class='pt11b'><?= number_format($fukuri_total_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none;border-right:none;border-top:none'>
                        <div class='pt10b'>��</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none;border-right:none;border-top:none'>
                        <div class='pt10b'>��</div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none'>
                        <div class='pt10b'>��Ϳ�����ⷫ����</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none'>
                        <div class='pt11b'><?= number_format($syoyo_hikiate_seizo_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none'>
                        <div class='pt11b'><?= number_format($syoyo_hikiate_han_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none'>
                        <div class='pt11b'><?= number_format($syoyo_hikiate_total_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none;border-right:none;border-top:none'>
                        <div class='pt10b'>��</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none;border-right:none;border-top:none'>
                        <div class='pt10b'>��</div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center'>
                        <div class='pt10b'>�࿦��������</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right'>
                        <div class='pt11b'><?= number_format($tai_kyufu_seizo_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right'>
                        <div class='pt11b'><?= number_format($tai_kyufu_han_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right'>
                        <div class='pt11b'><?= number_format($tai_kyufu_total_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-right:none;border-top:none'>
                        <div class='pt10b'>��</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-right:none;border-top:none'>
                        <div class='pt10b'>��</div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right'>
                        <div class='pt10b'>����</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right'>
                        <div class='pt11b'><?= number_format($roumu_total_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right'>
                        <div class='pt11b'><?= number_format($jin_total_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right'>
                        <div class='pt11b'><?= number_format($roumu_jin_total_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right'>
                        <div class='pt11b'><?= number_format($roumu_as_sagaku) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right'>
                        <div class='pt11b'><?= number_format($jin_as_sagaku) ?></div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none'>
                        <div class='pt10b'>��</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none'>
                        <div class='pt10b'>����¤�����</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none'>
                        <div class='pt10b'>�ʷ����</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none'>
                        <div class='pt10b'>��</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none;border-right:none;border-top:none'>
                        <div class='pt10b'>��</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none;border-right:none;border-top:none'>
                        <div class='pt10b'>��</div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none'>
                        <div class='pt10b'>ι�������</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none'>
                        <div class='pt11b'><?= number_format($ryohi_seizo_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none'>
                        <div class='pt11b'><?= number_format($ryohi_han_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none'>
                        <div class='pt11b'><?= number_format($ryohi_total_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none;border-right:none;border-top:none'>
                        <div class='pt10b'>��</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none;border-right:none;border-top:none'>
                        <div class='pt10b'>��</div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none'>
                        <div class='pt10b'>�̿���</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none'>
                        <div class='pt11b'><?= number_format($tsushin_seizo_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none'>
                        <div class='pt11b'><?= number_format($tsushin_han_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none'>
                        <div class='pt11b'><?= number_format($tsushin_total_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none;border-right:none;border-top:none'>
                        <div class='pt10b'>��</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none;border-right:none;border-top:none'>
                        <div class='pt10b'>��</div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none'>
                        <div class='pt10b'>�����</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none'>
                        <div class='pt11b'><?= number_format($kaigi_seizo_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none'>
                        <div class='pt11b'><?= number_format($kaigi_han_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none'>
                        <div class='pt11b'><?= number_format($kaigi_total_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none;border-right:none;border-top:none'>
                        <div class='pt10b'>��</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none;border-right:none;border-top:none'>
                        <div class='pt10b'>��</div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none'>
                        <div class='pt10b'>���������</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none'>
                        <div class='pt11b'><?= number_format($kosai_seizo_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none'>
                        <div class='pt11b'><?= number_format($kosai_han_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none'>
                        <div class='pt11b'><?= number_format($kosai_total_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none;border-right:none;border-top:none'>
                        <div class='pt10b'>��</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none;border-right:none;border-top:none'>
                        <div class='pt10b'>��</div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none'>
                        <div class='pt10b'>����������</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none'>
                        <div class='pt11b'>��</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none'>
                        <div class='pt11b'><?= number_format($senden_han_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none'>
                        <div class='pt11b'><?= number_format($senden_total_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none;border-right:none;border-top:none'>
                        <div class='pt10b'>��</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none;border-right:none;border-top:none'>
                        <div class='pt10b'>��</div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none'>
                        <div class='pt10b'>���²�¤��</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none'>
                        <div class='pt11b'><?= number_format($nizukuri_seizo_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none'>
                        <div class='pt11b'><?= number_format($nizukuri_han_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none'>
                        <div class='pt11b'><?= number_format($nizukuri_total_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none;border-right:none;border-top:none'>
                        <div class='pt10b'>��</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none;border-right:none;border-top:none'>
                        <div class='pt10b'>��</div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none'>
                        <div class='pt10b'>�޽񶵰���</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none'>
                        <div class='pt11b'><?= number_format($tosyo_seizo_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none'>
                        <div class='pt11b'><?= number_format($tosyo_han_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none'>
                        <div class='pt11b'><?= number_format($tosyo_total_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none;border-right:none;border-top:none'>
                        <div class='pt10b'>��</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none;border-right:none;border-top:none'>
                        <div class='pt10b'>��</div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none'>
                        <div class='pt10b'>��̳������</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none'>
                        <div class='pt11b'><?= number_format($gyomu_seizo_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none'>
                        <div class='pt11b'><?= number_format($gyomu_han_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none'>
                        <div class='pt11b'><?= number_format($gyomu_total_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none;border-right:none;border-top:none'>
                        <div class='pt10b'>��</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none;border-right:none;border-top:none'>
                        <div class='pt10b'>��</div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none'>
                        <div class='pt10b'>���Ǹ���</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none'>
                        <div class='pt11b'><?= number_format($syozei_seizo_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none'>
                        <div class='pt11b'><?= number_format($syozei_han_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none'>
                        <div class='pt11b'><?= number_format($syozei_total_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none;border-right:none;border-top:none'>
                        <div class='pt10b'>��</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none;border-right:none;border-top:none'>
                        <div class='pt10b'>��</div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none'>
                        <div class='pt10b'>�������</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none'>
                        <div class='pt11b'><?= number_format($shiken_seizo_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none'>
                        <div class='pt11b'>��</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none'>
                        <div class='pt11b'><?= number_format($shiken_total_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none;border-right:none;border-top:none'>
                        <div class='pt10b'>��</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none;border-right:none;border-top:none'>
                        <div class='pt10b'>��</div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none'>
                        <div class='pt10b'>������</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none'>
                        <div class='pt11b'><?= number_format($syuzen_seizo_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none'>
                        <div class='pt11b'><?= number_format($syuzen_han_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none'>
                        <div class='pt11b'><?= number_format($syuzen_total_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none;border-right:none;border-top:none'>
                        <div class='pt10b'>��</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none;border-right:none;border-top:none'>
                        <div class='pt10b'>��</div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none'>
                        <div class='pt10b'>��̳�Ѿ�������</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none'>
                        <div class='pt11b'><?= number_format($jimu_seizo_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none'>
                        <div class='pt11b'><?= number_format($jimu_han_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none'>
                        <div class='pt11b'><?= number_format($jimu_total_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none;border-right:none;border-top:none'>
                        <div class='pt10b'>��</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none;border-right:none;border-top:none'>
                        <div class='pt10b'>��</div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none'>
                        <div class='pt10b'>�����Ѿ�������</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none'>
                        <div class='pt11b'><?= number_format($kojyo_seizo_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none'>
                        <div class='pt11b'>��</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none'>
                        <div class='pt11b'><?= number_format($kojyo_total_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none;border-right:none;border-top:none'>
                        <div class='pt10b'>��</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none;border-right:none;border-top:none'>
                        <div class='pt10b'>��</div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none'>
                        <div class='pt10b'>������</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none'>
                        <div class='pt11b'><?= number_format($syaryo_seizo_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none'>
                        <div class='pt11b'><?= number_format($syaryo_han_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none'>
                        <div class='pt11b'><?= number_format($syaryo_total_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none;border-right:none;border-top:none'>
                        <div class='pt10b'>��</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none;border-right:none;border-top:none'>
                        <div class='pt10b'>��</div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none'>
                        <div class='pt10b'>�ݸ���</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none'>
                        <div class='pt11b'><?= number_format($hoken_seizo_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none'>
                        <div class='pt11b'><?= number_format($hoken_han_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none'>
                        <div class='pt11b'><?= number_format($hoken_total_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none;border-right:none;border-top:none'>
                        <div class='pt10b'>��</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none;border-right:none;border-top:none'>
                        <div class='pt10b'>��</div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none'>
                        <div class='pt10b'>��ƻ��Ǯ��</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none'>
                        <div class='pt11b'><?= number_format($suido_seizo_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none'>
                        <div class='pt11b'><?= number_format($suido_han_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none'>
                        <div class='pt11b'><?= number_format($suido_total_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none;border-right:none;border-top:none'>
                        <div class='pt10b'>��</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none;border-right:none;border-top:none'>
                        <div class='pt10b'>��</div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none'>
                        <div class='pt10b'>�������</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none'>
                        <div class='pt11b'><?= number_format($yachin_seizo_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none'>
                        <div class='pt11b'><?= number_format($yachin_han_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none'>
                        <div class='pt11b'><?= number_format($yachin_total_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none;border-right:none;border-top:none'>
                        <div class='pt10b'>��</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none;border-right:none;border-top:none'>
                        <div class='pt10b'>��</div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none'>
                        <div class='pt10b'>���ն�</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none'>
                        <div class='pt11b'>��</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none'>
                        <div class='pt11b'><?= number_format($kifu_han_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none'>
                        <div class='pt11b'><?= number_format($kifu_total_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none;border-right:none;border-top:none'>
                        <div class='pt10b'>��</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none;border-right:none;border-top:none'>
                        <div class='pt10b'>��</div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none'>
                        <div class='pt10b'>�¼���</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none'>
                        <div class='pt11b'><?= number_format($chin_seizo_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none'>
                        <div class='pt11b'><?= number_format($chin_han_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none'>
                        <div class='pt11b'><?= number_format($chin_total_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none;border-right:none;border-top:none'>
                        <div class='pt10b'>��</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none;border-right:none;border-top:none'>
                        <div class='pt10b'>��</div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none'>
                        <div class='pt10b'>����</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none'>
                        <div class='pt11b'><?= number_format($zappi_seizo_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none'>
                        <div class='pt11b'><?= number_format($zappi_han_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none'>
                        <div class='pt11b'><?= number_format($zappi_total_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none;border-right:none;border-top:none'>
                        <div class='pt10b'>��</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none;border-right:none;border-top:none'>
                        <div class='pt10b'>��</div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none'>
                        <div class='pt10b'>���졼���б���</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none'>
                        <div class='pt11b'><?= number_format($clame_seizo_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none'>
                        <div class='pt11b'><?= number_format($clame_han_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none'>
                        <div class='pt11b'><?= number_format($clame_total_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none;border-right:none;border-top:none'>
                        <div class='pt10b'>��</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none;border-right:none;border-top:none'>
                        <div class='pt10b'>��</div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center'>
                        <div class='pt10b'>����������</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right'>
                        <div class='pt11b'><?= number_format($genkasyo_seizo_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right'>
                        <div class='pt11b'><?= number_format($genkasyo_han_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right'>
                        <div class='pt11b'><?= number_format($genkasyo_total_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-right:none;border-top:none'>
                        <div class='pt10b'>��</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-right:none;border-top:none'>
                        <div class='pt10b'>��</div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right'>
                        <div class='pt10b'>����</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right'>
                        <div class='pt11b'><?= number_format($seizo_keihi_total_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right'>
                        <div class='pt11b'><?= number_format($han_keihi_total_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right'>
                        <div class='pt11b'><?= number_format($keihi_total_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right'>
                        <div class='pt11b'><?= number_format($seikei_as_sagaku) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right'>
                        <div class='pt11b'><?= number_format($hankei_as_sagaku) ?></div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right'>
                        <div class='pt10b'>���</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right'>
                        <div class='pt11b'><?= number_format($seizo_hiyo_total_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right'>
                        <div class='pt11b'><?= number_format($han_all_total_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right'>
                        <div class='pt11b'><?= number_format($all_keihi_total_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none;border-right:none;border-top:none'>
                        <div class='pt10b'>��</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none;border-right:none;border-top:none'>
                        <div class='pt10b'>��</div>
                    </td>
                </tr>
            </TBODY>
        </table>
            </td></tr>
        </table> <!----------------- ���ߡ�End ------------------>
        
        <table bgcolor='#ffffff' align='center' cellspacing='0' cellpadding='10'>
            <tr><td> <!----------- ���ߡ�(�ǥ�������) ------------>
        <center>�ʳ����������ư�׻����</center>
        <table class='winbox_field' width='100%' align='center' bgcolor='black' cellspacing='0' cellpadding='5'>
            <THEAD>
            </THEAD>
            <TFOOT>
                <!-- ���ߤϥեå����ϲ���ʤ� -->
            </TFOOT>
            <TBODY>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='left' colspan='5' style='border-bottom:none'>
                        <div class='pt10b'>�ʳ�����ܡ�</div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='left' style='border-bottom:none;border-right:none'>
                        <div class='pt10b'>���ڻ��ܶ��</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='left' style='border-bottom:none;border-right:none'>
                        <div class='pt10b'>������Ĺ�</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='left' style='border-bottom:none;border-right:none'>
                        <div class='pt10b'>��</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none'>
                        <div class='pt11b'><?= number_format($shihon_kishu) ?></div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='left' style='border-bottom:none;border-right:none'>
                        <div class='pt10b'>��</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='left' style='border-bottom:none;border-right:none'>
                        <div class='pt10b'>������ư��</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='left' style='border-bottom:none;border-right:none'>
                        <div class='pt10b'>��</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:1pt solid windowtext'>
                        <div class='pt11b'><?= number_format($shihon_hendo) ?></div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='left' style='border-bottom:none;border-right:none'>
                        <div class='pt10b'>��</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='left' style='border-bottom:none;border-right:none'>
                        <div class='pt10b'>�������Ĺ�</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='left' style='border-bottom:none;border-right:none'>
                        <div class='pt10b'>��</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:1.5pt solid windowtext'>
                        <div class='pt11b'><?= number_format($shihon_kin) ?></div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='left' colspan='5' style='border-bottom:none'>
                        <div class='pt10b'>���ڻ��ܾ�;���</div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='left' style='border-bottom:none;border-right:none'>
                        <div class='pt10b'>�����ܽ�����</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='left' style='border-bottom:none;border-right:none'>
                        <div class='pt10b'>������Ĺ�</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='left' style='border-bottom:none;border-right:none'>
                        <div class='pt10b'>��</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none'>
                        <div class='pt11b'><?= number_format($shihon_jyunbi_kishu) ?></div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='left' style='border-bottom:none;border-right:none'>
                        <div class='pt10b'>��</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='left' style='border-bottom:none;border-right:none'>
                        <div class='pt10b'>������ư��</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='left' style='border-bottom:none;border-right:none'>
                        <div class='pt10b'>��</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:1pt solid windowtext'>
                        <div class='pt11b'><?= number_format($shihon_jyunbi_hendo) ?></div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='left' style='border-bottom:none;border-right:none'>
                        <div class='pt10b'>��</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='left' style='border-bottom:none;border-right:none'>
                        <div class='pt10b'>�������Ĺ�</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='left' style='border-bottom:none;border-right:none'>
                        <div class='pt10b'>��</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:1.5pt solid windowtext'>
                        <div class='pt11b'><?= number_format($shihon_jyunbi_kin) ?></div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='left' style='border-bottom:none;border-right:none'>
                        <div class='pt10b'>������¾���ܾ�;��</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='left' style='border-bottom:none;border-right:none'>
                        <div class='pt10b'>������Ĺ�</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='left' style='border-bottom:none;border-right:none'>
                        <div class='pt10b'>��</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none'>
                        <div class='pt11b'><?= number_format($sonota_shihon_jyoyo_kishu) ?></div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='left' style='border-bottom:none;border-right:none'>
                        <div class='pt10b'>��</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='left' style='border-bottom:none;border-right:none'>
                        <div class='pt10b'>������ư��</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='left' style='border-bottom:none;border-right:none'>
                        <div class='pt10b'>��</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:1pt solid windowtext'>
                        <div class='pt11b'><?= number_format($sonota_shihon_jyoyo_hendo) ?></div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='left' style='border-bottom:none;border-right:none'>
                        <div class='pt10b'>��</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='left' style='border-bottom:none;border-right:none'>
                        <div class='pt10b'>�������Ĺ�</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='left' style='border-bottom:none;border-right:none'>
                        <div class='pt10b'>��</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:1.5pt solid windowtext'>
                        <div class='pt11b'><?= number_format($sonota_shihon_jyoyo_kin) ?></div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='left' style='border-bottom:none;border-right:none'>
                        <div class='pt10b'>�ڻ��ܾ�;��۹��</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='left' style='border-bottom:none;border-right:none'>
                        <div class='pt10b'>������Ĺ�</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='left' style='border-bottom:none;border-right:none'>
                        <div class='pt10b'>��</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none'>
                        <div class='pt11b'><?= number_format($shihon_jyoyo_total_kishu) ?></div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='left' style='border-bottom:none;border-right:none'>
                        <div class='pt10b'>��</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='left' style='border-bottom:none;border-right:none'>
                        <div class='pt10b'>������ư��</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='left' style='border-bottom:none;border-right:none'>
                        <div class='pt10b'>��</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:1pt solid windowtext'>
                        <div class='pt11b'><?= number_format($shihon_jyoyo_total_hendo) ?></div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='left' style='border-bottom:none;border-right:none'>
                        <div class='pt10b'>��</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='left' style='border-bottom:none;border-right:none'>
                        <div class='pt10b'>�������Ĺ�</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='left' style='border-bottom:none;border-right:none'>
                        <div class='pt10b'>��</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:1.5pt solid windowtext'>
                        <div class='pt11b'><?= number_format($shihon_jyoyo_total_kin) ?></div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='left' colspan='5' style='border-bottom:none'>
                        <div class='pt10b'>�������׾�;���</div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='left' style='border-bottom:none;border-right:none'>
                        <div class='pt10b'>�����׽�����</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='left' style='border-bottom:none;border-right:none'>
                        <div class='pt10b'>������Ĺ�ڤ��������Ĺ�</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='left' style='border-bottom:none;border-right:none'>
                        <div class='pt10b'>��</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none'>
                        <div class='pt11b'><?= number_format($rieki_jyunbi_kin) ?></div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='left' style='border-bottom:none;border-right:none'>
                        <div class='pt10b'>������¾���׾�;��</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='left' style='border-bottom:none;border-right:none'>
                        <div class='pt10b'>������Ĺ�</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='left' style='border-bottom:none;border-right:none'>
                        <div class='pt10b'>��</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none'>
                        <div class='pt11b'><?= number_format($sonota_rieki_jyoyo_kishu) ?></div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='left' style='border-bottom:none;border-right:none'>
                        <div class='pt10b'>��</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='left' style='border-bottom:none;border-right:none'>
                        <div class='pt10b'>������ư��</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='left' style='border-bottom:none;border-right:none'>
                        <div class='pt10b'>��</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:1pt solid windowtext'>
                        <div class='pt11b'><?= number_format($sonota_rieki_jyoyo_hendo) ?></div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='left' style='border-bottom:none;border-right:none'>
                        <div class='pt10b'>��</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='left' style='border-bottom:none;border-right:none'>
                        <div class='pt10b'>�������Ĺ�</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='left' style='border-bottom:none;border-right:none'>
                        <div class='pt10b'>��</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:1.5pt solid windowtext'>
                        <div class='pt11b'><?= number_format($sonota_rieki_jyoyo_kin) ?></div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='left' style='border-bottom:none;border-right:none'>
                        <div class='pt10b'>���������׾�;��</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='left' style='border-bottom:none;border-right:none'>
                        <div class='pt10b'>������Ĺ�</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='left' style='border-bottom:none;border-right:none'>
                        <div class='pt10b'>��</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none'>
                        <div class='pt11b'><?= number_format($kuri_rieki_jyoyo_kishu) ?></div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='left' style='border-bottom:none;border-right:none'>
                        <div class='pt10b'>��</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='left' style='border-bottom:none;border-right:none'>
                        <div class='pt10b'>������ư��</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none;border-right:none'>
                        <div class='pt10b'>�����������׶�ۡ�</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:1pt solid windowtext'>
                        <div class='pt11b'><?= number_format($kuri_rieki_jyoyo_hendo) ?></div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='left' style='border-bottom:none;border-right:none'>
                        <div class='pt10b'>��</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='left' style='border-bottom:none;border-right:none'>
                        <div class='pt10b'>�������Ĺ�</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='left' style='border-bottom:none;border-right:none'>
                        <div class='pt10b'>��</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:1.5pt solid windowtext'>
                        <div class='pt11b'><?= number_format($kuri_rieki_jyoyo_kin) ?></div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='left' style='border-bottom:none;border-right:none'>
                        <div class='pt10b'>�����׾�;��۹��</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='left' style='border-bottom:none;border-right:none'>
                        <div class='pt10b'>������Ĺ�</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='left' style='border-bottom:none;border-right:none'>
                        <div class='pt10b'>��</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none'>
                        <div class='pt11b'><?= number_format($rieki_jyoyo_total_kishu) ?></div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='left' style='border-bottom:none;border-right:none'>
                        <div class='pt10b'>��</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='left' style='border-bottom:none;border-right:none'>
                        <div class='pt10b'>������ư��</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='left' style='border-bottom:none;border-right:none'>
                        <div class='pt10b'>��</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:1pt solid windowtext'>
                        <div class='pt11b'><?= number_format($rieki_jyoyo_total_hendo) ?></div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='left' style='border-bottom:none;border-right:none'>
                        <div class='pt10b'>��</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='left' style='border-bottom:none;border-right:none'>
                        <div class='pt10b'>�������Ĺ�</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='left' style='border-bottom:none;border-right:none'>
                        <div class='pt10b'>��</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:1.5pt solid windowtext'>
                        <div class='pt11b'><?= number_format($rieki_jyoyo_total_kin) ?></div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='left' style='border-bottom:none;border-right:none'>
                        <div class='pt10b'>�Խ�񻺹�ס�</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='left' style='border-bottom:none;border-right:none'>
                        <div class='pt10b'>������Ĺ�</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='left' style='border-bottom:none;border-right:none'>
                        <div class='pt10b'>��</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none'>
                        <div class='pt11b'><?= number_format($jyun_shisan_total_kishu) ?></div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='left' style='border-bottom:none;border-right:none'>
                        <div class='pt10b'>��</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='left' style='border-bottom:none;border-right:none'>
                        <div class='pt10b'>������ư��</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='left' style='border-bottom:none;border-right:none'>
                        <div class='pt10b'>��</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:1pt solid windowtext'>
                        <div class='pt11b'><?= number_format($jyun_shisan_total_hendo) ?></div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='left' style='border-right:none'>
                        <div class='pt10b'>��</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='left' style='border-right:none'>
                        <div class='pt10b'>�������Ĺ�</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='left' style='border-right:none'>
                        <div class='pt10b'>��</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:1.5pt solid windowtext'>
                        <div class='pt11b'><?= number_format($jyun_shisan_total_kin) ?></div>
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
