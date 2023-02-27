<?php
//////////////////////////////////////////////////////////////////////////////
// ·î¼¡Â»±×´Ø·¸ ´ªÄê²ÊÌÜÁÈÂØÉ½                                              //
// Copyright(C) 2018-2021 Norihisa.Ohya norihisa_ooya@nitto-kohki.co.jp     //
// Changed history                                                          //
// 2018/06/26 Created   account_transfer_view.php                           //
// 2018/10/17 19´üÂè2»ÍÈ¾´ü·è»»¤Î·ë²Ì¤ò¼õ¤±¤Æ½¤Àµ                           //
// 2019/01/10 Á°Ê§¾ÃÈñÀÇ¤ò¥Þ¥¤¥Ê¥¹¤Ë19´üÂè3»ÍÈ¾´ü                           //
// 2019/04/09 ÃùÂ¢ÉÊ¤ò2019/03¤Î¥Ç¡¼¥¿¤ËÊÑ¹¹                                 //
// 2019/05/17 ÆüÉÕ¤Î¼èÆÀÊýË¡¤ÎÊÑ¹¹                                          //
// 2019/10/07 ÃùÂ¢ÉÊ¤ò2019/09¤Î¥Ç¡¼¥¿¤ËÊÑ¹¹                                 //
// 2020/04/06 ÃùÂ¢ÉÊ¤ò2020/03¤Î¥Ç¡¼¥¿¤ËÊÑ¹¹                                 //
// 2020/04/13 eCAÍÑ¤Î¥Ç¡¼¥¿È´½Ð¤·¤òÄÉ²Ã                                     //
// 2020/06/25 ´ªÄêÆâÌõÌÀºÙ½ñÍÑ¤Î¥Ç¡¼¥¿¤òÄÉ²Ã¡Ê20´üÊ¬¡Ë                      //
// 2020/06/30 ¸º²Á½þµÑÈñÌÀºÙ½ñÍÑ¤Î¥Ç¡¼¥¿¤òÄÉ²Ã¡Ê20´üÊ¬¡Ë                    //
// 2020/07/08 ÃùÂ¢ÉÊ¤ò2020/06¤Î¥Ç¡¼¥¿¤ËÊÑ¹¹                                 //
// 2021/01/13 ³Æ¼ï¥Ç¡¼¥¿¤òÄÉ²Ã¡Ê21´ü12·îÊ¬¡Ë                                //
// 2021/04/08 ³Æ¼ï¥Ç¡¼¥¿¤òÄÉ²Ã¡Ê21´ü3·îÊ¬¡Ë                                 //
//////////////////////////////////////////////////////////////////////////////
ini_set('error_reporting', E_STRICT);       // E_STRICT=2048(php5) E_ALL=2047 debug ÍÑ
// ini_set('error_reporting',E_ALL);           // E_ALL='2047' debug ÍÑ
// ini_set('display_errors','1');              // Error É½¼¨ ON debug ÍÑ ¥ê¥ê¡¼¥¹¸å¥³¥á¥ó¥È
session_start();                            // ini_set()¤Î¼¡¤Ë»ØÄê¤¹¤ë¤³¤È Script ºÇ¾å¹Ô

require_once ('../function.php');           // define.php ¤È pgsql.php ¤ò require_once ¤·¤Æ¤¤¤ë
require_once ('../tnk_func.php');           // TNK ¤Ë°ÍÂ¸¤¹¤ëÉôÊ¬¤Î´Ø¿ô¤ò require_once ¤·¤Æ¤¤¤ë
require_once ('../MenuHeader.php');         // TNK Á´¶¦ÄÌ menu class
access_log();                               // Script Name ¤Ï¼«Æ°¼èÆÀ

///// TNK ¶¦ÍÑ¥á¥Ë¥å¡¼¥¯¥é¥¹¤Î¥¤¥ó¥¹¥¿¥ó¥¹¤òºîÀ®
$menu = new MenuHeader(0);                  // Ç§¾Ú¥Á¥§¥Ã¥¯0=°ìÈÌ°Ê¾å Ìá¤êÀè=TOP_MENU ¥¿¥¤¥È¥ëÌ¤ÀßÄê
    // ¼ÂºÝ¤ÎÇ§¾Ú¤Ïprofit_loss_submit.php¤Ç¹Ô¤Ã¤Æ¤¤¤ëaccount_group_check()¤ò»ÈÍÑ

////////////// ¥µ¥¤¥ÈÀßÄê
// $menu->set_site(10, 7);                     // site_index=10(Â»±×¥á¥Ë¥å¡¼) site_id=7(·î¼¡Â»±×)
//////////// É½Âê¤ÎÀßÄê
$menu->set_caption('ÆÊÌÚÆüÅì¹©´ï(³ô)');
//////////// ¸Æ½ÐÀè¤ÎactionÌ¾¤È¥¢¥É¥ì¥¹ÀßÄê
// $menu->set_action('Ãê¾Ý²½Ì¾',   PL . 'address.php');

$url_referer     = $_SESSION['pl_referer'];     // ¸Æ½Ð¤â¤È¤Î URL ¤ò¼èÆÀ

$menu->set_action('ÉôÉÊ»Å³Ý£Ã', PL . 'cost_parts_widget_view.php');
$menu->set_action('¸¶ºàÎÁ', PL . 'cost_material_view.php');
$menu->set_action('ÉôÉÊ', PL . 'cost_parts_view.php');
$menu->set_action('ÀÚÊ´', PL . 'cost_kiriko_view.php');

///// ÂÐ¾ÝÅö·î
$ki2_ym   = $_SESSION['2ki_ym'];
$yyyymm   = $_SESSION['2ki_ym'];
$ki       = Ym_to_tnk($_SESSION['2ki_ym']);
$b_yyyymm = $yyyymm - 100;
$p1_ki    = Ym_to_tnk($b_yyyymm);

///// Á°´üËö Ç¯·î¤Î»»½Ð
$yyyy = substr($yyyymm, 0,4);
$mm   = substr($yyyymm, 4,2);
if (($mm >= 1) && ($mm <= 3)) {
    $yyyy = ($yyyy - 1);
}
$pre_end_ym = $yyyy . "03";     // Á°´üËöÇ¯·î

///// ´ü¡¦È¾´ü¤Î¼èÆÀ
$tuki_chk = substr($_SESSION['2ki_ym'],4,2);
if ($tuki_chk >= 1 && $tuki_chk <= 3) {           //Âè£´»ÍÈ¾´ü
    $hanki = '£´';
} elseif ($tuki_chk >= 4 && $tuki_chk <= 6) {     //Âè£±»ÍÈ¾´ü
    $hanki = '£±';
} elseif ($tuki_chk >= 7 && $tuki_chk <= 9) {     //Âè£²»ÍÈ¾´ü
    $hanki = '£²';
} elseif ($tuki_chk >= 10) {    //Âè£³»ÍÈ¾´ü
    $hanki = '£³';
}

///// Ç¯·îÈÏ°Ï¤Î¼èÆÀ
if ($tuki_chk >= 1 && $tuki_chk <= 3) {           //Âè£´»ÍÈ¾´ü
    $str_ym = $yyyy . '04';
    $end_ym = $yyyymm;
} elseif ($tuki_chk >= 4 && $tuki_chk <= 6) {     //Âè£±»ÍÈ¾´ü
    $str_ym = $yyyy . '04';
    $end_ym = $yyyymm;
} elseif ($tuki_chk >= 7 && $tuki_chk <= 9) {     //Âè£²»ÍÈ¾´ü
    $str_ym = $yyyy . '04';
    $end_ym = $yyyymm;
} elseif ($tuki_chk >= 10) {    //Âè£³»ÍÈ¾´ü
    $str_ym = $yyyy . '04';
    $end_ym = $yyyymm;
}
///// TNK´ü ¢ª NK´ü¤ØÊÑ´¹
$nk_ki   = $ki + 44;
$nk_p1ki = $p1_ki + 44;

//////////// ¥¿¥¤¥È¥ëÌ¾(¥½¡¼¥¹¤Î¥¿¥¤¥È¥ëÌ¾¤È¥Õ¥©¡¼¥à¤Î¥¿¥¤¥È¥ëÌ¾)
if ($tuki_chk == 3) {
    $menu->set_title("Âè {$ki} ´ü¡¡ËÜ·è»»¡¡´ª¡¡Äê¡¡²Ê¡¡ÌÜ¡¡ÁÈ¡¡ÂØ¡¡É½");
} else {
    $menu->set_title("Âè {$ki} ´ü¡¡Âè{$hanki}»ÍÈ¾´ü¡¡´ª¡¡Äê¡¡²Ê¡¡ÌÜ¡¡ÁÈ¡¡ÂØ¡¡É½");
}

///// ¸½¶âµÚ¤ÓÍÂ¶â
// ¸½¶â1100 00
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

// ÅöºÂ1103 00
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

// ÉáÄÌÍÂ¶â1104 00 ¤Î¹ç·×¡Ê¶ä¹Ô¥³¡¼¥É°ã¤¤¡Ë
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

// Äê´üÍÂ¶â1106 00
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

// ¸½¶âµÚ¤ÓÍÂ¶â¹ç·×¤Î·×»»
$genyo_total_kin = $genkin_kin + $touza_kin + $futsu_kin + $teiki_kin;

///// ºß¸Ë
// À½ÉÊ1404 00
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

// À½ÉÊ»Å³ÝÉÊ1405 00
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

// ÉôÉÊ1406 00
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

// ÉôÉÊ»Å³ÝÉÊ1407 30
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

// ¸¶ºàÎÁ1408 00
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

// ¤½¤ÎÂ¾¤ÎÃª²·ÉÊ1409 ¤Î¹ç·×
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

// ÃùÂ¢ÉÊ ¥Ç¡¼¥¿¤Ï¤Ê¤¤¤Î¤Ç·î¼¡¤ÎÂß¼ÚÂÐ¾ÈÉ½¤ÈÆ±¤¸¼°¤ÇÄ¾ÀÜÆþÎÏ
// É¾²ÁÀÚ²¼¤²¤ÎÆþÎÏ ¥Ç¡¼¥¿¤ÏÌµ¤¤¤Î¤Ç·î¼¡¤ÎÂß¼ÚÂÐ¾ÈÉ½¤ÈÆ±¤¸¼°¤ÇÄ¾ÀÜÆþÎÏ
// ÉôÉÊ»Å³ÝÌÀºÙ¤ÎÆþÎÏ »ç¤Î»ñÎÁ¤«¤é ¹ç·×¤ÇOK 
// Ìµ·Á¸ÇÄê»ñ»º¤Î¼èÆÀ²Á³Û ´ü¼ó»Ä¹â¡¢´üÃæÁý²Ã¡¢´üÃæ¸º¾¯¤ò¼èÆÀ
// ´ü¼ó¤ÏÄÌ´ü°ìÄê¤ÇÁý¸º¤ÏÅÔÅÙ
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
    $chozo_kin  = 27994100;    // ³ÎÄê °µÂ¤¹©¶ñ 26,979,200±ß ÃùÂ¢ÉÊ £³ÅÀ 1,014,800±ß¡Ê151,200±ß¤È447,000±ß¤È416,700±ß¡Ë
}
if ($yyyymm >= 201610 && $yyyymm <= 201610) {
    $chozo_kin  = 27994100;    // ³ÎÄê °µÂ¤¹©¶ñ 26,979,200±ß ÃùÂ¢ÉÊ £³ÅÀ 1,014,800±ß¡Ê151,200±ß¤È447,000±ß¤È416,700±ß¡Ë
}
if ($yyyymm >= 201611 && $yyyymm <= 201611) {
    $chozo_kin  = 28060500;    // ³ÎÄê °µÂ¤¹©¶ñ 26,979,200±ß ÃùÂ¢ÉÊ £´ÅÀ 1,081,300±ß¡Ê151,200±ß¤È447,000±ß¤È416,700±ß¤È66,500±ß¡Ë
}
if ($yyyymm >= 201612 && $yyyymm <= 201701) {
    $chozo_kin  = 28118600;    // ³ÎÄê °µÂ¤¹©¶ñ 26,979,200±ß ÃùÂ¢ÉÊ £´ÅÀ 1,139,400±ß¡Ê151,200±ß¤È447,000±ß¤È416,700±ß¤È66,500±ß¤È58,000±ß¡Ë
}
if ($yyyymm >= 201702 && $yyyymm <= 201702) {
    $chozo_kin  = 27701900;    // ³ÎÄê °µÂ¤¹©¶ñ 26,979,200±ß ÃùÂ¢ÉÊ £´ÅÀ 722,700±ß¡Ê151,200±ß¤È447,000±ß¤È66,500±ß¤È58,000±ß¡Ë
}
if ($yyyymm == 201703) {
    $chozo_kin  = 27170800;    // ³ÎÄê °µÂ¤¹©¶ñ 26,448,100±ß ÃùÂ¢ÉÊ £´ÅÀ 722,700±ß¡Ê151,200±ß¤È447,000±ß¤È66,500±ß¤È58,000±ß¡Ë
}
if ($yyyymm >= 201704 && $yyyymm <= 201708) {
    $chozo_kin  = 27170800;    // ³ÎÄê °µÂ¤¹©¶ñ 26,448,100±ß ÃùÂ¢ÉÊ £´ÅÀ 722,700±ß¡Ê151,200±ß¤È447,000±ß¤È66,500±ß¤È58,000±ß¡Ë
}
if ($yyyymm == 201709) {
    $chozo_kin  = 31331800;    // ³ÎÄê °µÂ¤¹©¶ñ 30,609,100±ß ÃùÂ¢ÉÊ £´ÅÀ 722,700±ß¡Ê151,200±ß¤È447,000±ß¤È66,500±ß¤È58,000±ß¡Ë
}
if ($yyyymm >= 201710 && $yyyymm <= 201802) {
    $chozo_kin  = 31331800;    // ³ÎÄê °µÂ¤¹©¶ñ 30,609,100±ß ÃùÂ¢ÉÊ £´ÅÀ 722,700±ß¡Ê151,200±ß¤È447,000±ß¤È66,500±ß¤È58,000±ß¡Ë
}
if ($yyyymm == 201803) {
    $chozo_kin  = 30723300;    // ³ÎÄê °µÂ¤¹©¶ñ 29,523,100±ß ÃùÂ¢ÉÊ £µÅÀ 1,200,200±ß¡Ê151,200±ß¤È447,000±ß¤È66,500±ß¤È58,000±ß¤È477,500±ß¡Ë
}

if ($yyyymm >= 201804 && $yyyymm <= 201808) {
    $chozo_kin  = 30723300;    // ³ÎÄê °µÂ¤¹©¶ñ 29,523,100±ß ÃùÂ¢ÉÊ £µÅÀ 1,200,200±ß¡Ê151,200±ß¤È447,000±ß¤È66,500±ß¤È58,000±ß¤È477,500±ß¡Ë
}

if ($yyyymm >= 201809 && $yyyymm <= 201902) {
    $chozo_kin  = 31076300;    // ³ÎÄê °µÂ¤¹©¶ñ 29,528,100±ß ÃùÂ¢ÉÊ £µÅÀ 1,548,200±ß¡Ê151,200±ß¤È447,000±ß¤È66,500±ß¤È58,000±ß¤È477,500±ß¤È348,000±ß¡Ë
}
if ($yyyymm == 201903) {
    $chozo_kin  = 29013600;    // ³ÎÄê °µÂ¤¹©¶ñ 27,332,900±ß ÃùÂ¢ÉÊ £±£°ÅÀ 1,680,700±ß¡Ê151,200±ß¤È447,000±ß¤È58,000±ß¤È477,500±ß¤È348,000±ß¤È39,800±ß¤¬5ÅÀ¡Ë
}
if ($yyyymm >= 201904 && $yyyymm <= 201908) {
    $chozo_kin  = 29013600;    // ³ÎÄê °µÂ¤¹©¶ñ 27,332,900±ß ÃùÂ¢ÉÊ £±£°ÅÀ 1,680,700±ß¡Ê151,200±ß¤È447,000±ß¤È58,000±ß¤È477,500±ß¤È348,000±ß¤È39,800±ß¤¬5ÅÀ¡Ë
}
if ($yyyymm >= 201909 && $yyyymm <= 202002) {
    $chozo_kin  = 30711100;    // ³ÎÄê °µÂ¤¹©¶ñ 29,030,400±ß ÃùÂ¢ÉÊ £±£°ÅÀ 1,680,700±ß¡Ê151,200±ß¤È447,000±ß¤È58,000±ß¤È477,500±ß¤È348,000±ß¤È39,800±ß¤¬5ÅÀ¡Ë
}

// 2020/03
if ($yyyymm >= 202003 && $yyyymm <= 202005) {
    $chozo_kin  = 31847700;    // ³ÎÄê °µÂ¤¹©¶ñ 30,225,000±ß ÃùÂ¢ÉÊ £¹ÅÀ 1,622,700±ß¡Ê151,200±ß¤È447,000±ß¤È477,500±ß¤È348,000±ß¤È39,800±ß¤¬5ÅÀ¡Ë
}
// 2020/03
if ($yyyymm >= 202006 && $yyyymm <= 202008) {
    $chozo_kin  = 34437700;    // ³ÎÄê °µÂ¤¹©¶ñ 30,225,000±ß ÃùÂ¢ÉÊ £¹ÅÀ 4,212,700±ß¡Ê151,200±ß¤È447,000±ß¤È477,500±ß¤È348,000±ß¤È39,800±ß¤¬5ÅÀ¤È2,590,000±ß¡Ë
}
// 2020/10/07 ÄÉ²Ã ²¾Ê§¶â¢ªÃª²·»ñ»º
if ($yyyymm >= 202009 && $yyyymm <= 202102) {
    $chozo_kin  = 35225500;    // ³ÎÄê °µÂ¤¹©¶ñ 31,012,800±ß ÃùÂ¢ÉÊ £¹ÅÀ 4,212,700±ß¡Ê151,200±ß¤È447,000±ß¤È477,500±ß¤È348,000±ß¤È39,800±ß¤¬5ÅÀ¤È2,590,000±ß¡Ë
}
// 2021/04/08 ÄÉ²Ã ²¾Ê§¶â¢ªÃª²·»ñ»º
if ($yyyymm >= 202103 && $yyyymm <= 202105) {
    $chozo_kin  = 34201500;    // ³ÎÄê °µÂ¤¹©¶ñ 29,988,800±ß ÃùÂ¢ÉÊ £¹ÅÀ 4,212,700±ß¡Ê151,200±ß¤È447,000±ß¤È477,500±ß¤È348,000±ß¤È39,800±ß¤¬5ÅÀ¤È2,590,000±ß¡Ë
}
// 2021/07/07 ÄÉ²Ã ²¾Ê§¶â¢ªÃª²·»ñ»º
if ($yyyymm >= 202106 && $yyyymm <= 202108) {
    $chozo_kin  = 34390591;    // ³ÎÄê °µÂ¤¹©¶ñ 29,988,800±ß ÃùÂ¢ÉÊ £¹ÅÀ 4,401,791±ß¡Ê151,200±ß¤È447,000±ß¤È477,500±ß¤È348,000±ß¤È39,800±ß¤¬5ÅÀ¤È2,590,000±ß¤È189,091±ß¡Ë
}
// 2021/07/07 ÄÉ²Ã ²¾Ê§¶â¢ªÃª²·»ñ»º
if ($yyyymm >= 202109 && $yyyymm <= 202202) {
    $chozo_kin  = 35918291;    // ³ÎÄê °µÂ¤¹©¶ñ 31,516,500±ß ÃùÂ¢ÉÊ £¹ÅÀ 4,401,791±ß¡Ê151,200±ß¤È447,000±ß¤È477,500±ß¤È348,000±ß¤È39,800±ß¤¬5ÅÀ¤È2,590,000±ß¤È189,091±ß¡Ë
}

// Ãª²·ÅùÆþÎÏ
if ($yyyymm >= 201806 && $yyyymm <= 201808) {
    $hyoka_buhin_kin = 24145944;    // É¾²ÁÀÚ²¼¤² ÉôÉÊ 24,145,944±ß
    $hyoka_zai_kin   = 5054790;     // É¾²ÁÀÚ²¼¤² ¸¶ºàÎÁ 5,054,790±ß
    $tana_kou_kin    = 58478522;    // ¹©ºî»Å³Ý 2018/06
    $tana_gai_kin    = 115024519;   // ³°Ãí»Å³Ý 2018/06
    $tana_ken_kin    = 6794210;     // ¸¡ºº»Å³Ý 2018/06
}
if ($yyyymm >= 201809 && $yyyymm <= 201811) {
    $hyoka_buhin_kin = 20982681;    // É¾²ÁÀÚ²¼¤² ÉôÉÊ 20,982,681±ß
    $hyoka_zai_kin   = 3245692;     // É¾²ÁÀÚ²¼¤² ¸¶ºàÎÁ 3,245,692±ß
    $tana_kou_kin    = 39056743;    // ¹©ºî»Å³Ý 2018/09
    $tana_gai_kin    = 106305243;   // ³°Ãí»Å³Ý 2018/09
    $tana_ken_kin    = 8507486;     // ¸¡ºº»Å³Ý 2018/09
}
if ($yyyymm >= 201812 && $yyyymm <= 201902) {
    $hyoka_buhin_kin = 21353386;    // É¾²ÁÀÚ²¼¤² ÉôÉÊ 21,353,386±ß
    $hyoka_zai_kin   = 4301424;     // É¾²ÁÀÚ²¼¤² ¸¶ºàÎÁ 4,301,424±ß
    $tana_kou_kin    = 51762589;    // ¹©ºî»Å³Ý 2018/12
    $tana_gai_kin    = 120393767;   // ³°Ãí»Å³Ý 2018/12
    $tana_ken_kin    = 18413966;    // ¸¡ºº»Å³Ý 2018/12
}

if ($yyyymm >= 201903 && $yyyymm <= 201905) {
    $hyoka_buhin_kin = 27099309;    // É¾²ÁÀÚ²¼¤² ÉôÉÊ 27,099,309±ß
    $hyoka_zai_kin   = 3837098;     // É¾²ÁÀÚ²¼¤² ¸¶ºàÎÁ 3,837,098±ß
    $tana_kou_kin    = 43499362;    // ¹©ºî»Å³Ý 2019/03
    $tana_gai_kin    = 118595019;   // ³°Ãí»Å³Ý 2019/03
    $tana_ken_kin    = 6129984;     // ¸¡ºº»Å³Ý 2019/03
}
if ($yyyymm >= 201906 && $yyyymm <= 201908) {
    $hyoka_buhin_kin = 27338977;    // É¾²ÁÀÚ²¼¤² ÉôÉÊ 27,338,977±ß
    $hyoka_zai_kin   = 3632576;     // É¾²ÁÀÚ²¼¤² ¸¶ºàÎÁ 3,632,576±ß
    $tana_kou_kin    = 44994415;    // ¹©ºî»Å³Ý 2019/06
    $tana_gai_kin    = 120740158;   // ³°Ãí»Å³Ý 2019/06
    $tana_ken_kin    = 7774737;     // ¸¡ºº»Å³Ý 2019/06
}
if ($yyyymm >= 201909 && $yyyymm <= 201911) {
    $hyoka_buhin_kin = 26052650;    // É¾²ÁÀÚ²¼¤² ÉôÉÊ 26,052,650±ß
    $hyoka_zai_kin   = 3462809;     // É¾²ÁÀÚ²¼¤² ¸¶ºàÎÁ 3,462,809±ß
    $tana_kou_kin    = 34775430;    // ¹©ºî»Å³Ý 2019/09
    $tana_gai_kin    = 86308322;    // ³°Ãí»Å³Ý 2019/09
    $tana_ken_kin    = 38770378;    // ¸¡ºº»Å³Ý 2019/09
}
if ($yyyymm >= 201912 && $yyyymm <= 202002) {
    $hyoka_buhin_kin = 25648144;    // É¾²ÁÀÚ²¼¤² ÉôÉÊ 25,648,144±ß
    $hyoka_zai_kin   = 3145474;     // É¾²ÁÀÚ²¼¤² ¸¶ºàÎÁ 4,301,424±ß
    $tana_kou_kin    = 48147770;    // ¹©ºî»Å³Ý 2019/12
    $tana_gai_kin    = 144697973;   // ³°Ãí»Å³Ý 2019/12
    $tana_ken_kin    = 6235679;     // ¸¡ºº»Å³Ý 2019/12
}
if ($yyyymm >= 202003 && $yyyymm <= 202005) {
    $hyoka_buhin_kin = 22146591;    // É¾²ÁÀÚ²¼¤² ÉôÉÊ 22,146,591±ß
    $hyoka_zai_kin   = 2936551;     // É¾²ÁÀÚ²¼¤² ¸¶ºàÎÁ 2,936,551±ß
    $tana_kou_kin    = 43412814;    // ¹©ºî»Å³Ý 2020/03
    $tana_gai_kin    = 144834707;   // ³°Ãí»Å³Ý 2020/03
    $tana_ken_kin    = 2043234;     // ¸¡ºº»Å³Ý 2020/03
}
if ($yyyymm >= 202006 && $yyyymm <= 202008) {
    $hyoka_buhin_kin = 29663899;    // É¾²ÁÀÚ²¼¤² ÉôÉÊ 29,663,899±ß
    $hyoka_zai_kin   = 2541727;     // É¾²ÁÀÚ²¼¤² ¸¶ºàÎÁ 2,541,727±ß
    $tana_kou_kin    = 33562592;    // ¹©ºî»Å³Ý 2020/06
    $tana_gai_kin    = 157623908;   // ³°Ãí»Å³Ý 2020/06
    $tana_ken_kin    = 4483191;     // ¸¡ºº»Å³Ý 2020/06
}
if ($yyyymm >= 202009 && $yyyymm <= 202011) {
    $hyoka_buhin_kin = 18889691;    // É¾²ÁÀÚ²¼¤² ÉôÉÊ 18,889,691±ß
    $hyoka_zai_kin   = 2247663;     // É¾²ÁÀÚ²¼¤² ¸¶ºàÎÁ 2,247,663±ß
    $tana_kou_kin    = 44194009;    // ¹©ºî»Å³Ý 2020/09
    $tana_gai_kin    = 149995875;   // ³°Ãí»Å³Ý 2020/09
    $tana_ken_kin    = 6145842;     // ¸¡ºº»Å³Ý 2020/09
}
if ($yyyymm >= 202012 && $yyyymm <= 202102) {
    $hyoka_buhin_kin = 20714112;    // É¾²ÁÀÚ²¼¤² ÉôÉÊ 20,714,112±ß
    $hyoka_zai_kin   = 3098026;     // É¾²ÁÀÚ²¼¤² ¸¶ºàÎÁ 3,098,026±ß
    $tana_kou_kin    = 39886443;    // ¹©ºî»Å³Ý 2020/12
    $tana_gai_kin    = 180087279;   // ³°Ãí»Å³Ý 2020/12
    $tana_ken_kin    = 3318087;     // ¸¡ºº»Å³Ý 2020/12
}
if ($yyyymm >= 202103 && $yyyymm <= 202105) {
    $hyoka_buhin_kin = 23313658;    // É¾²ÁÀÚ²¼¤² ÉôÉÊ 23,313,658±ß
    $hyoka_zai_kin   = 3853278;     // É¾²ÁÀÚ²¼¤² ¸¶ºàÎÁ 3,853,278±ß
    $tana_kou_kin    = 52966381;    // ¹©ºî»Å³Ý 2021/03
    $tana_gai_kin    = 178610899;   // ³°Ãí»Å³Ý 2021/03
    $tana_ken_kin    = 4799597;     // ¸¡ºº»Å³Ý 2021/03
}
if ($yyyymm >= 202106 && $yyyymm <= 202108) {
    $hyoka_buhin_kin = 29379248;    // É¾²ÁÀÚ²¼¤² ÉôÉÊ 29,379,248±ß
    $hyoka_zai_kin   = 3226918;     // É¾²ÁÀÚ²¼¤² ¸¶ºàÎÁ 3,226,918±ß
    $tana_kou_kin    = 53398839;    // ¹©ºî»Å³Ý 2021/06
    $tana_gai_kin    = 191936510;   // ³°Ãí»Å³Ý 2021/06
    $tana_ken_kin    = 3863607;     // ¸¡ºº»Å³Ý 2021/06
}
if ($yyyymm >= 202109 && $yyyymm <= 202111) {
    $hyoka_buhin_kin = 27697190;    // É¾²ÁÀÚ²¼¤² ÉôÉÊ 27,697,190±ß
    $hyoka_zai_kin   = 3183503;     // É¾²ÁÀÚ²¼¤² ¸¶ºàÎÁ 3,183,503±ß
    $tana_kou_kin    = 35205657;    // ¹©ºî»Å³Ý 2021/09
    $tana_gai_kin    = 174850313;   // ³°Ãí»Å³Ý 2021/09
    $tana_ken_kin    = 0;           // ¸¡ºº»Å³Ý 2021/09
}


// Ìµ·Á¸ÇÄê»ñ»º¤Î¼èÆÀ²Á³Û ´ü¼ó»Ä¹â¡¢´üÃæÁý²Ã¡¢´üÃæ¸º¾¯¤òÆþÎÏ
if ($yyyymm >= 201803 && $yyyymm <= 201805) {
    $den_kishu_kin   = 1224000;     // ÅÅÏÃ´ü¼ó»Ä¹â    1,224,000±ß
    $shi_kishu_kin   = 13120400;    // »ÜÀß´ü¼ó»Ä¹â   13,120,400±ß
    $sft_kishu_kin   = 16163122;    // ¥½¥Õ¥È´ü¼ó»Ä¹â 16,163,122±ß
    $den_zou_kin     = 0;           // ÅÅÏÃ´üÃæÁý²Ã            0±ß
    $shi_zou_kin     = 0;           // »ÜÀß´üÃæÁý²Ã            0±ß
    $sft_zou_kin     = 7565000;     // ¥½¥Õ¥È´üÃæÁý²Ã  7,565,000±ß
    $den_gen_kin     = 0;           // ÅÅÏÃ´üÃæ¸º¾¯            0±ß
    $shi_gen_kin     = 0;           // »ÜÀß´üÃæ¸º¾¯            0±ß
    $sft_gen_kin     = 0;           // ¥½¥Õ¥È´üÃæ¸º¾¯          0±ß
}
if ($yyyymm >= 201806 && $yyyymm <= 201808) {
    $den_kishu_kin   = 1224000;     // ÅÅÏÃ´ü¼ó»Ä¹â    1,224,000±ß
    $shi_kishu_kin   = 13120400;    // »ÜÀß´ü¼ó»Ä¹â   13,120,400±ß
    $sft_kishu_kin   = 23728122;    // ¥½¥Õ¥È´ü¼ó»Ä¹â 23,728,122±ß
    $den_zou_kin     = 0;           // ÅÅÏÃ´üÃæÁý²Ã            0±ß
    $shi_zou_kin     = 0;           // »ÜÀß´üÃæÁý²Ã            0±ß
    $sft_zou_kin     = 0;           // ¥½¥Õ¥È´üÃæÁý²Ã          0±ß
    $den_gen_kin     = 0;           // ÅÅÏÃ´üÃæ¸º¾¯            0±ß
    $shi_gen_kin     = 0;           // »ÜÀß´üÃæ¸º¾¯            0±ß
    $sft_gen_kin     = 0;           // ¥½¥Õ¥È´üÃæ¸º¾¯          0±ß
}
if ($yyyymm >= 201809 && $yyyymm <= 201811) {
    $den_kishu_kin   = 1224000;     // ÅÅÏÃ´ü¼ó»Ä¹â    1,224,000±ß
    $shi_kishu_kin   = 13120400;    // »ÜÀß´ü¼ó»Ä¹â   13,120,400±ß
    $sft_kishu_kin   = 23728122;    // ¥½¥Õ¥È´ü¼ó»Ä¹â 23,728,122±ß
    $den_zou_kin     = 0;           // ÅÅÏÃ´üÃæÁý²Ã            0±ß
    $shi_zou_kin     = 0;           // »ÜÀß´üÃæÁý²Ã            0±ß
    $sft_zou_kin     = 0;           // ¥½¥Õ¥È´üÃæÁý²Ã          0±ß
    $den_gen_kin     = 0;           // ÅÅÏÃ´üÃæ¸º¾¯            0±ß
    $shi_gen_kin     = 0;           // »ÜÀß´üÃæ¸º¾¯            0±ß
    $sft_gen_kin     = 0;           // ¥½¥Õ¥È´üÃæ¸º¾¯          0±ß
}
if ($yyyymm >= 201812 && $yyyymm <= 201902) {
    $den_kishu_kin   = 1224000;     // ÅÅÏÃ´ü¼ó»Ä¹â    1,224,000±ß
    $shi_kishu_kin   = 13120400;    // »ÜÀß´ü¼ó»Ä¹â   13,120,400±ß
    $sft_kishu_kin   = 23728122;    // ¥½¥Õ¥È´ü¼ó»Ä¹â 23,728,122±ß
    $den_zou_kin     = 0;           // ÅÅÏÃ´üÃæÁý²Ã            0±ß
    $shi_zou_kin     = 0;           // »ÜÀß´üÃæÁý²Ã            0±ß
    $sft_zou_kin     = 0;           // ¥½¥Õ¥È´üÃæÁý²Ã          0±ß
    $den_gen_kin     = 0;           // ÅÅÏÃ´üÃæ¸º¾¯            0±ß
    $shi_gen_kin     = 0;           // »ÜÀß´üÃæ¸º¾¯            0±ß
    $sft_gen_kin     = 0;           // ¥½¥Õ¥È´üÃæ¸º¾¯          0±ß
}
if ($yyyymm >= 201903 && $yyyymm <= 201905) {
    $den_kishu_kin   = 1224000;     // ÅÅÏÃ´ü¼ó»Ä¹â    1,224,000±ß
    $shi_kishu_kin   = 13120400;    // »ÜÀß´ü¼ó»Ä¹â   13,120,400±ß
    $sft_kishu_kin   = 23728122;    // ¥½¥Õ¥È´ü¼ó»Ä¹â 23,728,122±ß
    $den_zou_kin     = 0;           // ÅÅÏÃ´üÃæÁý²Ã            0±ß
    $shi_zou_kin     = 0;           // »ÜÀß´üÃæÁý²Ã            0±ß
    $sft_zou_kin     = 0;           // ¥½¥Õ¥È´üÃæÁý²Ã          0±ß
    $den_gen_kin     = 0;           // ÅÅÏÃ´üÃæ¸º¾¯            0±ß
    $shi_gen_kin     = 0;           // »ÜÀß´üÃæ¸º¾¯            0±ß
    $sft_gen_kin     = 0;           // ¥½¥Õ¥È´üÃæ¸º¾¯          0±ß
}
if ($yyyymm >= 201906 && $yyyymm <= 201908) {
    $den_kishu_kin   = 1224000;     // ÅÅÏÃ´ü¼ó»Ä¹â    1,224,000±ß
    $shi_kishu_kin   = 13120400;    // »ÜÀß´ü¼ó»Ä¹â   13,120,400±ß
    $sft_kishu_kin   = 23728122;    // ¥½¥Õ¥È´ü¼ó»Ä¹â 23,728,122±ß
    $den_zou_kin     = 0;           // ÅÅÏÃ´üÃæÁý²Ã            0±ß
    $shi_zou_kin     = 0;           // »ÜÀß´üÃæÁý²Ã            0±ß
    $sft_zou_kin     = 0;           // ¥½¥Õ¥È´üÃæÁý²Ã          0±ß
    $den_gen_kin     = 0;           // ÅÅÏÃ´üÃæ¸º¾¯            0±ß
    $shi_gen_kin     = 0;           // »ÜÀß´üÃæ¸º¾¯            0±ß
    $sft_gen_kin     = 0;           // ¥½¥Õ¥È´üÃæ¸º¾¯          0±ß
}
if ($yyyymm >= 201909 && $yyyymm <= 201911) {
    $den_kishu_kin   = 1224000;     // ÅÅÏÃ´ü¼ó»Ä¹â    1,224,000±ß
    $shi_kishu_kin   = 13120400;    // »ÜÀß´ü¼ó»Ä¹â   13,120,400±ß
    $sft_kishu_kin   = 23728122;    // ¥½¥Õ¥È´ü¼ó»Ä¹â 23,728,122±ß
    $den_zou_kin     = 0;           // ÅÅÏÃ´üÃæÁý²Ã            0±ß
    $shi_zou_kin     = 0;           // »ÜÀß´üÃæÁý²Ã            0±ß
    $sft_zou_kin     = 0;           // ¥½¥Õ¥È´üÃæÁý²Ã          0±ß
    $den_gen_kin     = 0;           // ÅÅÏÃ´üÃæ¸º¾¯            0±ß
    $shi_gen_kin     = 0;           // »ÜÀß´üÃæ¸º¾¯            0±ß
    $sft_gen_kin     = 0;           // ¥½¥Õ¥È´üÃæ¸º¾¯          0±ß
}
if ($yyyymm >= 201912 && $yyyymm <= 202002) {
    $den_kishu_kin   = 1224000;     // ÅÅÏÃ´ü¼ó»Ä¹â    1,224,000±ß
    $shi_kishu_kin   = 13120400;    // »ÜÀß´ü¼ó»Ä¹â   13,120,400±ß
    $sft_kishu_kin   = 23728122;    // ¥½¥Õ¥È´ü¼ó»Ä¹â 23,728,122±ß
    $den_zou_kin     = 0;           // ÅÅÏÃ´üÃæÁý²Ã            0±ß
    $shi_zou_kin     = 0;           // »ÜÀß´üÃæÁý²Ã            0±ß
    $sft_zou_kin     = 0;           // ¥½¥Õ¥È´üÃæÁý²Ã          0±ß
    $den_gen_kin     = 0;           // ÅÅÏÃ´üÃæ¸º¾¯            0±ß
    $shi_gen_kin     = 0;           // »ÜÀß´üÃæ¸º¾¯            0±ß
    $sft_gen_kin     = 0;           // ¥½¥Õ¥È´üÃæ¸º¾¯          0±ß
}
if ($yyyymm >= 202003 && $yyyymm <= 202005) {
    $den_kishu_kin   = 1224000;     // ÅÅÏÃ´ü¼ó»Ä¹â    1,224,000±ß
    $shi_kishu_kin   = 13120400;    // »ÜÀß´ü¼ó»Ä¹â   13,120,400±ß
    $sft_kishu_kin   = 23728122;    // ¥½¥Õ¥È´ü¼ó»Ä¹â 23,728,122±ß
    $den_zou_kin     = 0;           // ÅÅÏÃ´üÃæÁý²Ã            0±ß
    $shi_zou_kin     = 0;           // »ÜÀß´üÃæÁý²Ã            0±ß
    $sft_zou_kin     = 0;           // ¥½¥Õ¥È´üÃæÁý²Ã          0±ß
    $den_gen_kin     = 0;           // ÅÅÏÃ´üÃæ¸º¾¯            0±ß
    $shi_gen_kin     = 0;           // »ÜÀß´üÃæ¸º¾¯            0±ß
    $sft_gen_kin     = 0;           // ¥½¥Õ¥È´üÃæ¸º¾¯          0±ß
}
if ($yyyymm >= 202006 && $yyyymm <= 202008) {
    $den_kishu_kin   = 1224000;     // ÅÅÏÃ´ü¼ó»Ä¹â    1,224,000±ß
    $shi_kishu_kin   = 13120400;    // »ÜÀß´ü¼ó»Ä¹â   13,120,400±ß
    $sft_kishu_kin   = 23728122;    // ¥½¥Õ¥È´ü¼ó»Ä¹â 23,728,122±ß
    $den_zou_kin     = 0;           // ÅÅÏÃ´üÃæÁý²Ã            0±ß
    $shi_zou_kin     = 0;           // »ÜÀß´üÃæÁý²Ã            0±ß
    $sft_zou_kin     = 17639120;    // ¥½¥Õ¥È´üÃæÁý²Ã 17,639,120±ß
    $den_gen_kin     = 0;           // ÅÅÏÃ´üÃæ¸º¾¯            0±ß
    $shi_gen_kin     = 0;           // »ÜÀß´üÃæ¸º¾¯            0±ß
    $sft_gen_kin     = 0;           // ¥½¥Õ¥È´üÃæ¸º¾¯          0±ß
}
if ($yyyymm >= 202009 && $yyyymm <= 202011) {
    $den_kishu_kin   = 1224000;     // ÅÅÏÃ´ü¼ó»Ä¹â    1,224,000±ß
    $shi_kishu_kin   = 13120400;    // »ÜÀß´ü¼ó»Ä¹â   13,120,400±ß
    $sft_kishu_kin   = 23728122;    // ¥½¥Õ¥È´ü¼ó»Ä¹â 23,728,122±ß
    $den_zou_kin     = 0;           // ÅÅÏÃ´üÃæÁý²Ã            0±ß
    $shi_zou_kin     = 0;           // »ÜÀß´üÃæÁý²Ã            0±ß
    $sft_zou_kin     = 20537520;    // ¥½¥Õ¥È´üÃæÁý²Ã 20,537,520±ß
    $den_gen_kin     = 0;           // ÅÅÏÃ´üÃæ¸º¾¯            0±ß
    $shi_gen_kin     = 0;           // »ÜÀß´üÃæ¸º¾¯            0±ß
    $sft_gen_kin     = 0;           // ¥½¥Õ¥È´üÃæ¸º¾¯          0±ß
}
if ($yyyymm >= 202012 && $yyyymm <= 202102) {
    $den_kishu_kin   = 1224000;     // ÅÅÏÃ´ü¼ó»Ä¹â    1,224,000±ß
    $shi_kishu_kin   = 13120400;    // »ÜÀß´ü¼ó»Ä¹â   13,120,400±ß
    $sft_kishu_kin   = 23728122;    // ¥½¥Õ¥È´ü¼ó»Ä¹â 23,728,122±ß
    $den_zou_kin     = 0;           // ÅÅÏÃ´üÃæÁý²Ã            0±ß
    $shi_zou_kin     = 0;           // »ÜÀß´üÃæÁý²Ã            0±ß
    $sft_zou_kin     = 20537520;    // ¥½¥Õ¥È´üÃæÁý²Ã 20,537,520±ß
    $den_gen_kin     = 0;           // ÅÅÏÃ´üÃæ¸º¾¯            0±ß
    $shi_gen_kin     = 0;           // »ÜÀß´üÃæ¸º¾¯            0±ß
    $sft_gen_kin     = 0;           // ¥½¥Õ¥È´üÃæ¸º¾¯          0±ß
}
if ($yyyymm >= 202103 && $yyyymm <= 202105) {
    $den_kishu_kin   = 1224000;     // ÅÅÏÃ´ü¼ó»Ä¹â    1,224,000±ß
    $shi_kishu_kin   = 13120400;    // »ÜÀß´ü¼ó»Ä¹â   13,120,400±ß
    $sft_kishu_kin   = 23728122;    // ¥½¥Õ¥È´ü¼ó»Ä¹â 23,728,122±ß
    $den_zou_kin     = 0;           // ÅÅÏÃ´üÃæÁý²Ã            0±ß
    $shi_zou_kin     = 0;           // »ÜÀß´üÃæÁý²Ã            0±ß
    $sft_zou_kin     = 20537520;    // ¥½¥Õ¥È´üÃæÁý²Ã 20,537,520±ß
    $den_gen_kin     = 0;           // ÅÅÏÃ´üÃæ¸º¾¯            0±ß
    $shi_gen_kin     = 0;           // »ÜÀß´üÃæ¸º¾¯            0±ß
    $sft_gen_kin     = 0;           // ¥½¥Õ¥È´üÃæ¸º¾¯          0±ß
}
if ($yyyymm >= 202106 && $yyyymm <= 202108) {
    $den_kishu_kin   = 1224000;     // ÅÅÏÃ´ü¼ó»Ä¹â    1,224,000±ß
    $shi_kishu_kin   = 13120400;    // »ÜÀß´ü¼ó»Ä¹â   13,120,400±ß
    $sft_kishu_kin   = 44265642;    // ¥½¥Õ¥È´ü¼ó»Ä¹â 44,265,642±ß
    $den_zou_kin     = 0;           // ÅÅÏÃ´üÃæÁý²Ã            0±ß
    $shi_zou_kin     = 0;           // »ÜÀß´üÃæÁý²Ã            0±ß
    $sft_zou_kin     = 547000;      // ¥½¥Õ¥È´üÃæÁý²Ã    547,000±ß
    $den_gen_kin     = 0;           // ÅÅÏÃ´üÃæ¸º¾¯            0±ß
    $shi_gen_kin     = 0;           // »ÜÀß´üÃæ¸º¾¯            0±ß
    $sft_gen_kin     = 0;           // ¥½¥Õ¥È´üÃæ¸º¾¯          0±ß
}
if ($yyyymm >= 202109 && $yyyymm <= 202111) {
    $den_kishu_kin   = 1224000;     // ÅÅÏÃ´ü¼ó»Ä¹â    1,224,000±ß
    $shi_kishu_kin   = 13120400;    // »ÜÀß´ü¼ó»Ä¹â   13,120,400±ß
    $sft_kishu_kin   = 44265642;    // ¥½¥Õ¥È´ü¼ó»Ä¹â 44,265,642±ß
    $den_zou_kin     = 0;           // ÅÅÏÃ´üÃæÁý²Ã            0±ß
    $shi_zou_kin     = 0;           // »ÜÀß´üÃæÁý²Ã            0±ß
    $sft_zou_kin     = 547000;      // ¥½¥Õ¥È´üÃæÁý²Ã    547,000±ß
    $den_gen_kin     = 0;           // ÅÅÏÃ´üÃæ¸º¾¯            0±ß
    $shi_gen_kin     = 0;           // »ÜÀß´üÃæ¸º¾¯            0±ß
    $sft_gen_kin     = 0;           // ¥½¥Õ¥È´üÃæ¸º¾¯          0±ß
}

// ºß¸Ë¹ç·×¤Î·×»»
$zaiko_total_kin    = $seihin_kin + $seihinsi_kin + $buhin_kin + $buhinsi_kin + $genzai_kin + $sonotatana_kin + $chozo_kin;
// À½ÉÊ¹ç·×¤Î·×»»
$seihin_total_kin   = $seihin_kin;
// »Å³ÝÉÊ¹ç·×¤Î·×»»
$sikakari_total_kin = $seihinsi_kin;
// ¸¶ºàÎÁµÚ¤ÓÃùÂ¢ÉÊ¹ç·×¤Î·×»»
$gencho_total_kin   = $buhin_kin + $buhinsi_kin + $genzai_kin + $sonotatana_kin + $chozo_kin;
// Î®Æ°»ñ»ººß¸Ë¹ç·×¤Î·×»»
$ryudozaiko_total_kin   = $seihin_total_kin + $sikakari_total_kin + $gencho_total_kin;

///// Î®Æ°»ñ»º
// Í­½þ»ÙµëÌ¤¼ýÆþ¶â1302 00
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

// Ì¤¼ýÆþ¶â1503 00
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

// Ì¤¼ý¼ý±×1701 00
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

//// ¤½¤ÎÂ¾¤ÎÎ®Æ°»ñ»º
// Î©ÂØ¶â1505 00
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

// ²¾Ê§¶â1504 00
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
    // ²¾Ê§¶â¤ÏÃùÂ¢ÉÊÊ¬¤ò¥Þ¥¤¥Ê¥¹
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

// ¤½¤ÎÂ¾Î®Æ°»ñ»º2000 00
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

// ¤½¤ÎÂ¾Î®Æ°»ñ»º¤Ë¥×¥é¥¹¤¹¤ë Â¾´ªÄêÌ¤·è»»¡Ê»ñ¡Ë1901 20
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

// ¤½¤ÎÂ¾Î®Æ°»ñ»º¤Î·×»»
$hokaryudo_kin = $hokaryudo_kin + $ta_miketsu_kin;

// Î®Æ°»ñ»º Ì¤¼ýÆþ¶â¤Î·×»»
$ryu_mishu_kin    = $yumi_kin + $mishu_kin + $mishueki_kin;
// Î®Æ°»ñ»º Ì¤¼ýÆþ¶â¹ç·×¤Î·×»»
$ryu_mishu_total_kin    = $ryu_mishu_kin;
// Î®Æ°»ñ»º ¤½¤ÎÂ¾Î®Æ°»ñ»º·×¤Î·×»»
$hokaryudo_total_kin    = $tatekae_kin + $karibara_kin + $hokaryudo_kin;
// Î®Æ°»ñ»º Ì¤¼ýÆþ¶â¹ç·×¤Î·×»»
$hokaryudo_all_kin    = $hokaryudo_total_kin;

//// Í­·Á¸ÇÄê»ñ»º
/*
// ·úÊª2101 00
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

// ·úÊª¸ºÎß³Û3401 10
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

// ·úÊª»ñ»º¶â³Û
$tate_shisan_kin = $tatemono_kin - $tate_gen_kin;
// ·úÊªÉÕÂ°ÀßÈ÷2102 00
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

// ·úÊªÉÕÂ°ÀßÈ÷¸ºÎß³Û3401 20
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

// ·úÊªÉÕÂ°ÀßÈ÷»ñ»º¶â³Û
$fuzoku_shisan_kin = $fuzoku_kin - $fuzoku_gen_kin;
// ·úÊª¹ç·×»ñ»º¶â³Û
$tate_all_shisan_kin = $tate_shisan_kin + $fuzoku_shisan_kin;

//ecaÍÑ »ñ»º¶â³Û·úÊª
$tate_shutoku_kin = $tatemono_kin + $fuzoku_kin;
//ecaÍÑ ¸º²Á½þµÑÎß·×³Û(·úÊª)
$tate_rui_kin = -($tate_gen_kin + $fuzoku_gen_kin);

// ¹½ÃÛÊª2103 00
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

// ¹½ÃÛÊª¸ºÎß³Û3401 30
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

// ¹½ÃÛÊª»ñ»º¶â³Û
$kouchiku_shisan_kin = $kouchiku_kin - $kouchiku_gen_kin;

// µ¡³£ÁõÃÖ2104 00
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

// µ¡³£ÁõÃÖ¸ºÎß³Û3401 40
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

// µ¡³£ÁõÃÖ»ñ»º¶â³Û
$kikai_shisan_kin = $kikai_kin - $kikai_gen_kin;

// ¼ÖíÒ±¿ÈÂ¶ñ2105 00
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

// ¼ÖíÒ±¿ÈÂ¶ñ¸ºÎß³Û3401 50
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

// ¼ÖíÒ±¿ÈÂ¶ñ»ñ»º¶â³Û
$sharyo_shisan_kin = $sharyo_kin - $sharyo_gen_kin;

// ´ï¶ñ¹©¶ñ2106 00
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

// ´ï¶ñ¹©¶ñ¸ºÎß³Û3401 60
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

// ´ï¶ñ¹©¶ñ»ñ»º¶â³Û
$kigu_shisan_kin = $kigu_kin - $kigu_gen_kin;

// ½º´ïÈ÷ÉÊ2107 00
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

// ½º´ïÈ÷ÉÊ¸ºÎß³Û3401 70
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

// ½º´ïÈ÷ÉÊ»ñ»º¶â³Û
$jyuki_shisan_kin = $jyuki_kin - $jyuki_gen_kin;
// ¹©¶ñ´ï¶ñµÚ¤ÓÈ÷ÉÊ»ñ»º¶â³Û
$jyubihin_all_shisan_kin = $kigu_shisan_kin + $jyuki_shisan_kin;

//ecaÍÑ »ñ»º¶â³Û¹©¶ñ´ï¶ñÈ÷ÉÊ
$kikougu_shutoku_kin = $kigu_kin + $jyuki_kin;
//ecaÍÑ ¸º²Á½þµÑÎß·×³Û(¹©¶ñ´ï¶ñÈ÷ÉÊ)
$kikougu_rui_kin = -($kigu_gen_kin + $jyuki_gen_kin);

// ¥ê¡¼¥¹»ñ»º2110 00
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

// ¥ê¡¼¥¹»ñ»º¸ºÎß³Û3401 80
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

// ¥ê¡¼¥¹»ñ»º»ñ»º¶â³Û
$lease_shisan_kin = $lease_kin - $lease_gen_kin;

// ¸º²Á½þµÑÎß·×³Û¹ç·×
$gensyo_total_kin = $tate_gen_kin + $fuzoku_gen_kin + $kouchiku_gen_kin + $kikai_gen_kin + $sharyo_gen_kin + $kigu_gen_kin + $jyuki_gen_kin + $lease_gen_kin;
$gensyo_total_mi_kin = - $gensyo_total_kin;

// ¸ÇÄê»ñ»ºÊí²Á¶â³Û·×
$boka_totai_kin = $tate_shisan_kin + $fuzoku_shisan_kin + $kouchiku_shisan_kin + $kikai_shisan_kin + $sharyo_shisan_kin + $kigu_shisan_kin + $jyuki_shisan_kin + $lease_shisan_kin;

//// Ìµ·Á¸ÇÄê»ñ»º
// ÅÅÏÃ²ÃÆþ¸¢2207 00
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

///// Åê»ñÅù
/*
// ½Ð»ñ¶â2301 00
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
// º¹ÆþÉß¶âÊÝ¾Ú¶â2302 00
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

// Åê»ñÅù¹ç·×¤Î·×»»
$toushi_total_kin    = $shussi_kin + $hosyo_kin;

///// Î®Æ°ÉéºÄ£±
// ²¾Ê§¾ÃÈñÀÇÅù1508 00
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

// ²¾Ê§¾ÃÈñÀÇÅù(Í¢Æþ)1508 20
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

// ²¾Ê§¾ÃÈñÀÇÅù¤Î¹ç·×(¼èÆÀ»þ¤Ë¹ç·×¤·¤Æ¤¤¤ë°Ù¤¤¤é¤Ê¤«¤Ã¤¿)
$kari_zei_kin = - $kari00_kin;

// Á°Ê§¾ÃÈñÀÇÅù1560 00
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

// ²¾¼õ¾ÃÈñÀÇÅù3227 00
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

// Ì¤Ê§¾ÃÈñÀÇÅù3228 00
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

// Ì¤Ê§¾ÃÈñÀÇÅù¤Î¹ç·×¤Î·×»»
$mihazei_total_kin = $kari_zei_kin + $mae_zei_kin + $kariuke_zei_kin + $miharai_zei_kin;

///// Î®Æ°ÉéºÄ£²
/*
// Çã³Ý¶â3103 00
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

// Çã³Ý¶â´üÆü¿¶¹þ3102 00
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

// Çã³Ý¶â¤Î¹ç·×¤Î·×»»
$kaikake_total_kin = $kaikake_kin + $kaikake_kiji_kin;

///// Î®Æ°ÉéºÄ£³
// Ì¤Ê§¶â3105 00
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

// Ì¤Ê§¶â´üÆü»ØÄê3106 00
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

// Ì¤Ê§¶â¤Î¹ç·×¤Î·×»»
$miharai_total_kin = $miharai_kin + $miharai_kiji_kin;

///// Î®Æ°ÉéºÄ£´
// Á°¼õ¶â3221 00
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

// ¤½¤ÎÂ¾Î®Æ°ÉéºÄ3229 00
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

// ¤½¤ÎÂ¾Î®Æ°ÉéºÄ¤Î¹ç·×¤Î·×»»
$sonota_ryudo_total_kin = $maeuke_kin + $sonota_ryudo_kin;
// Î®Æ°ÉéºÄ¤Î¹ç·×¤Î·×»»¡ÊÎ®Æ°ÉéºÄ£²¡Á£´¤Î¹ç·×¡Ë
$ryudo_fusai_total_kin = $kaikake_total_kin + $miharai_total_kin + $sonota_ryudo_total_kin;

///// Â»±×·×»»½ñ¤Î·×»»
///// À½Â¤¸¶²ÁÊó¹ð½ñ
// ´ü¼óÃª²·
// À½ÉÊ1404 00
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

// À½ÉÊ»Å³ÝÉÊ1405 00
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

// ÉôÉÊ1406 00
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

// ÉôÉÊ»Å³ÝÉÊ1407 30
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

// ¸¶ºàÎÁ1408 00
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

// ¤½¤ÎÂ¾¤ÎÃª²·ÉÊ1409 ¤Î¹ç·×
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

// ºß¸Ë¹ç·×¤Î·×»»
$z_zaiko_total_kin    = $z_seihin_kin + $z_seihinsi_kin + $z_buhin_kin + $z_buhinsi_kin + $z_genzai_kin + $z_sonotatana_kin;
// »Å³ÝÉÊ¹ç·×¤Î·×»»
$z_sikakari_total_kin = $z_seihinsi_kin;
// ¸¶ºàÎÁµÚ¤ÓÃùÂ¢ÉÊ¹ç·×¤Î·×»»
$z_gencho_total_kin   = $z_buhin_kin + $z_buhinsi_kin + $z_genzai_kin + $z_sonotatana_kin;

//// ´üËöÃª²·¹â
// ºß¸Ë¹ç·×¤Î·×»»
$kimatsu_total_kin  = $seihin_kin + $seihinsi_kin + $buhin_kin + $buhinsi_kin + $genzai_kin + $sonotatana_kin;
// »Å³ÝÉÊ¹ç·×¤Î·×»»
$kimatsu_sikakari_total_kin = $seihinsi_kin;
// ¸¶ºàÎÁµÚ¤ÓÃùÂ¢ÉÊ¹ç·×¤Î·×»»
$kimatsu_gencho_total_kin   = $buhin_kin + $buhinsi_kin + $genzai_kin + $sonotatana_kin;

//// P / L   ±Ä¶È³°Â»±×¡¢ÆÃÊÌÂ»±×¡¢Â¾
// ±Ä¶È³°¼ý±×¤Î·×»»
// »¨¼ýÆþ9103 ¤Î¹ç·×
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
// ¶ÈÌ³°ÑÂ÷¼ýÆþ9107 ¤Î¹ç·×
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

// ±Ä¶È³°¼ý±×¹ç·×¤Î·×»»
$eigyo_shueki_total_kin = $zatsushu_kin + $gyomushu_kin;

// ±Ä¶È³°ÈñÍÑ¤Î·×»»
// ¤½¤ÎÂ¾±Ä¶È³°ÈñÍÑ9310 ¤Î¹ç·×
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
// ¸ÇÄê»ñ»ºÇäµÑÂ»9317 ¤Î¹ç·×
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

// ¸ÇÄê»ñ»º½üµÑÂ»9311 ¤Î¹ç·×
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

// Ë¡¿ÍÀÇÅù¤Î·×»»
// Ë¡¿ÍÀÇµÚ¤Ó½»Ì±ÀÇ9401 ¤Î¹ç·×
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
// »ö¶ÈÀÇ9402 ¤Î¹ç·×
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

// Ë¡¿ÍÀÇÅùÄ´À°³Û9405 ¤Î¹ç·×
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

// Ë¡¿ÍÀÇÅù¹ç·×¤Î·×»»
$hojin_zeito_total_kin = $hojin_jyumin_zei_kin + $jigyo_zei_kin + $hojin_chosei_kin;

// ecaÍÑË¡¿ÍÀÇ¡¢½»Ì±ÀÇµÚ¤Ó»ö¶ÈÀÇ
$eca_hojin_zeito_total_kin = $hojin_jyumin_zei_kin + $jigyo_zei_kin;

//// ·ÐÈñÌÀºÙ½ñ¤Î·×»»
// ÈÎ´ÉÈñ Î¹Èñ¸òÄÌÈñ¹ç·×
// Î¹Èñ¸òÄÌÈñ
$res   = array();
$field = array();
$rows  = array();
$han_ryohi_kin = 0;
$note  = 'ÈÎ´ÉÈñÎ¹Èñ¸òÄÌÈñ';
$query = sprintf("select SUM(kin) from profit_loss_keihi_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='%s'", $str_ym, $end_ym, $note);
if ($rows=getResultWithField2($query, $field, $res) <= 0) {
    $han_ryohi_kin = 0;
} else {
    $han_ryohi_kin = $res[0][0];
}
// ³¤³°½ÐÄ¥Èñ
$res   = array();
$field = array();
$rows  = array();
$han_kaigai_kin = 0;
$note  = 'ÈÎ´ÉÈñ³¤³°½ÐÄ¥Èñ';
$query = sprintf("select SUM(kin) from profit_loss_keihi_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='%s'", $str_ym, $end_ym, $note);
if ($rows=getResultWithField2($query, $field, $res) <= 0) {
    $han_kaigai_kin = 0;
} else {
    $han_kaigai_kin = $res[0][0];
}

// ÈÎ´ÉÈñÎ¹Èñ¸òÄÌÈñ¹ç·×¤Î·×»»
$han_ryohi_total_kin = $han_ryohi_kin + $han_kaigai_kin;

// ÈÎ´ÉÈñ ¹­¹ðÀëÅÁÈñ¹ç·×
// ¹­¹ðÀëÅÁÈñ
$res   = array();
$field = array();
$rows  = array();
$han_kokoku_kin = 0;
$note  = 'ÈÎ´ÉÈñ¹­¹ðÀëÅÁÈñ';
$query = sprintf("select SUM(kin) from profit_loss_keihi_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='%s'", $str_ym, $end_ym, $note);
if ($rows=getResultWithField2($query, $field, $res) <= 0) {
    $han_kokoku_kin = 0;
} else {
    $han_kokoku_kin = $res[0][0];
}
// µá¿ÍÈñ
$res   = array();
$field = array();
$rows  = array();
$han_kyujin_kin = 0;
$note  = 'ÈÎ´ÉÈñµá¿ÍÈñ';
$query = sprintf("select SUM(kin) from profit_loss_keihi_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='%s'", $str_ym, $end_ym, $note);
if ($rows=getResultWithField2($query, $field, $res) <= 0) {
    $han_kyujin_kin = 0;
} else {
    $han_kyujin_kin = $res[0][0];
}

// ÈÎ´ÉÈñ¹­¹ðÀëÅÁÈñ¹ç·×¤Î·×»»
$han_kokoku_total_kin = $han_kokoku_kin + $han_kyujin_kin;

// ÈÎ´ÉÈñ ¶ÈÌ³°ÑÂ÷Èñ¹ç·×
// ¶ÈÌ³°ÑÂ÷Èñ
$res   = array();
$field = array();
$rows  = array();
$han_gyomu_kin = 0;
$note  = 'ÈÎ´ÉÈñ¶ÈÌ³°ÑÂ÷Èñ';
$query = sprintf("select SUM(kin) from profit_loss_keihi_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='%s'", $str_ym, $end_ym, $note);
if ($rows=getResultWithField2($query, $field, $res) <= 0) {
    $han_gyomu_kin = 0;
} else {
    $han_gyomu_kin = $res[0][0];
}
// »ÙÊ§¼ê¿ôÎÁ
$res   = array();
$field = array();
$rows  = array();
$han_tesu_kin = 0;
$note  = 'ÈÎ´ÉÈñ»ÙÊ§¼ê¿ôÎÁ';
$query = sprintf("select SUM(kin) from profit_loss_keihi_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='%s'", $str_ym, $end_ym, $note);
if ($rows=getResultWithField2($query, $field, $res) <= 0) {
    $han_tesu_kin = 0;
} else {
    $han_tesu_kin = $res[0][0];
}

// ÈÎ´ÉÈñ¶ÈÌ³°ÑÂ÷Èñ¹ç·×¤Î·×»»
$han_gyomu_total_kin = $han_gyomu_kin + $han_tesu_kin;

// ÈÎ´ÉÈñ ½ôÀÇ¸ø²Ý¹ç·×
// »ö¶ÈÅù
$res   = array();
$field = array();
$rows  = array();
$han_jigyo_kin = 0;
$note  = 'ÈÎ´ÉÈñ»ö¶ÈÅù';
$query = sprintf("select SUM(kin) from profit_loss_keihi_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='%s'", $str_ym, $end_ym, $note);
if ($rows=getResultWithField2($query, $field, $res) <= 0) {
    $han_jigyo_kin = 0;
} else {
    $han_jigyo_kin = $res[0][0];
}
// ½ôÀÇ¸ø²Ý
$res   = array();
$field = array();
$rows  = array();
$han_zeikoka_kin = 0;
$note  = 'ÈÎ´ÉÈñ½ôÀÇ¸ø²Ý';
$query = sprintf("select SUM(kin) from profit_loss_keihi_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='%s'", $str_ym, $end_ym, $note);
if ($rows=getResultWithField2($query, $field, $res) <= 0) {
    $han_zeikoka_kin = 0;
} else {
    $han_zeikoka_kin = $res[0][0];
}

// ÈÎ´ÉÈñ½ôÀÇ¸ø²Ý¹ç·×¤Î·×»»
$han_zeikoka_total_kin = $han_jigyo_kin + $han_zeikoka_kin;

// ÈÎ´ÉÈñ »öÌ³ÍÑ¾ÃÌ×ÉÊÈñ¹ç·×
// »öÌ³ÍÑ¾ÃÌ×ÉÊÈñ
$res   = array();
$field = array();
$rows  = array();
$han_jimuyo_kin = 0;
$note  = 'ÈÎ´ÉÈñ»öÌ³ÍÑ¾ÃÌ×ÉÊÈñ';
$query = sprintf("select SUM(kin) from profit_loss_keihi_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='%s'", $str_ym, $end_ym, $note);
if ($rows=getResultWithField2($query, $field, $res) <= 0) {
    $han_jimuyo_kin = 0;
} else {
    $han_jimuyo_kin = $res[0][0];
}
// ¹©¾ì¾ÃÌ×ÉÊÈñ
$res   = array();
$field = array();
$rows  = array();
$han_kojyo_kin = 0;
$note  = 'ÈÎ´ÉÈñ¹©¾ì¾ÃÌ×ÉÊÈñ';
$query = sprintf("select SUM(kin) from profit_loss_keihi_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='%s'", $str_ym, $end_ym, $note);
if ($rows=getResultWithField2($query, $field, $res) <= 0) {
    $han_kojyo_kin = 0;
} else {
    $han_kojyo_kin = $res[0][0];
}

// ÈÎ´ÉÈñ»öÌ³ÍÑ¾ÃÌ×ÉÊÈñ¹ç·×¤Î·×»»
$han_jimuyo_total_kin = $han_jimuyo_kin + $han_kojyo_kin;

// ÈÎ´ÉÈñ »¨Èñ¹ç·×
// »¨Èñ
$res   = array();
$field = array();
$rows  = array();
$han_zappi_kin = 0;
$note  = 'ÈÎ´ÉÈñ»¨Èñ';
$query = sprintf("select SUM(kin) from profit_loss_keihi_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='%s'", $str_ym, $end_ym, $note);
if ($rows=getResultWithField2($query, $field, $res) <= 0) {
    $han_zappi_kin = 0;
} else {
    $han_zappi_kin = $res[0][0];
}
// ÊÝ¾Ú½¤ÍýÈñ
$res   = array();
$field = array();
$rows  = array();
$han_hosyo_kin = 0;
$note  = 'ÈÎ´ÉÈñÊÝ¾Ú½¤ÍýÈñ';
$query = sprintf("select SUM(kin) from profit_loss_keihi_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='%s'", $str_ym, $end_ym, $note);
if ($rows=getResultWithField2($query, $field, $res) <= 0) {
    $han_hosyo_kin = 0;
} else {
    $han_hosyo_kin = $res[0][0];
}
// ½ô²ñÈñ
$res   = array();
$field = array();
$rows  = array();
$han_kaihi_kin = 0;
$note  = 'ÈÎ´ÉÈñ½ô²ñÈñ';
$query = sprintf("select SUM(kin) from profit_loss_keihi_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='%s'", $str_ym, $end_ym, $note);
if ($rows=getResultWithField2($query, $field, $res) <= 0) {
    $han_kaihi_kin = 0;
} else {
    $han_kaihi_kin = $res[0][0];
}

// ÈÎ´ÉÈñ»¨Èñ¹ç·×¤Î·×»»
$han_zappi_total_kin = $han_zappi_kin + $han_hosyo_kin + $han_kaihi_kin;

// ÈÎ´ÉÈñ ÃÏÂå²ÈÄÂ¹ç·×
// ÃÏÂå²ÈÄÂ
$res   = array();
$field = array();
$rows  = array();
$han_yachin_kin = 0;
$note  = 'ÈÎ´ÉÈñÃÏÂå²ÈÄÂ';
$query = sprintf("select SUM(kin) from profit_loss_keihi_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='%s'", $str_ym, $end_ym, $note);
if ($rows=getResultWithField2($query, $field, $res) <= 0) {
    $han_yachin_kin = 0;
} else {
    $han_yachin_kin = $res[0][0];
}
// ÁÒÉßÎÁ
$res   = array();
$field = array();
$rows  = array();
$han_kura_kin = 0;
$note  = 'ÈÎ´ÉÈñÁÒÉßÎÁ';
$query = sprintf("select SUM(kin) from profit_loss_keihi_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='%s'", $str_ym, $end_ym, $note);
if ($rows=getResultWithField2($query, $field, $res) <= 0) {
    $han_kura_kin = 0;
} else {
    $han_kura_kin = $res[0][0];
}

// ÈÎ´ÉÈñÃÏÂå²ÈÄÂ¹ç·×¤Î·×»»
$han_yachin_total_kin = $han_yachin_kin + $han_kura_kin;

// ÈÎ´ÉÈñ ¸üÀ¸Ê¡ÍøÈñ¹ç·×
// Ë¡ÄêÊ¡ÍøÈñ
$res   = array();
$field = array();
$rows  = array();
$han_hofukuri_kin = 0;
$note  = 'ÈÎ´ÉÈñË¡ÄêÊ¡ÍøÈñ';
$query = sprintf("select SUM(kin) from profit_loss_keihi_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='%s'", $str_ym, $end_ym, $note);
if ($rows=getResultWithField2($query, $field, $res) <= 0) {
    $han_hofukuri_kin = 0;
} else {
    $han_hofukuri_kin = $res[0][0];
}
// ¸üÀ¸Ê¡ÍøÈñ
$res   = array();
$field = array();
$rows  = array();
$han_kofukuri_kin = 0;
$note  = 'ÈÎ´ÉÈñ¸üÀ¸Ê¡ÍøÈñ';
$query = sprintf("select SUM(kin) from profit_loss_keihi_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='%s'", $str_ym, $end_ym, $note);
if ($rows=getResultWithField2($query, $field, $res) <= 0) {
    $han_kofukuri_kin = 0;
} else {
    $han_kofukuri_kin = $res[0][0];
}

// ÈÎ´ÉÈñ¸üÀ¸Ê¡ÍøÈñ¹ç·×¤Î·×»»
$han_kofukuri_total_kin = $han_hofukuri_kin + $han_kofukuri_kin;

// ÈÎ´ÉÈñ Âà¿¦µëÉÕÈñÍÑ¹ç·×
// Âà¿¦µëÍ¿¶â
$res   = array();
$field = array();
$rows  = array();
$han_taikyuyo_kin = 0;
$note  = 'ÈÎ´ÉÈñÂà¿¦µëÍ¿¶â';
$query = sprintf("select SUM(kin) from profit_loss_keihi_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='%s'", $str_ym, $end_ym, $note);
if ($rows=getResultWithField2($query, $field, $res) <= 0) {
    $han_taikyuyo_kin = 0;
} else {
    $han_taikyuyo_kin = $res[0][0];
}
// Âà¿¦µëÉÕÈñÍÑ
$res   = array();
$field = array();
$rows  = array();
$han_taikyufu_kin = 0;
$note  = 'ÈÎ´ÉÈñÂà¿¦µëÉÕÈñÍÑ';
$query = sprintf("select SUM(kin) from profit_loss_keihi_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='%s'", $str_ym, $end_ym, $note);
if ($rows=getResultWithField2($query, $field, $res) <= 0) {
    $han_taikyufu_kin = 0;
} else {
    $han_taikyufu_kin = $res[0][0];
}

// ÈÎ´ÉÈñÂà¿¦µëÉÕÈñÍÑ¹ç·×¤Î·×»»
$han_taikyufu_total_kin = $han_taikyuyo_kin + $han_taikyufu_kin;

// À½Â¤·ÐÈñ Î¹Èñ¸òÄÌÈñ¹ç·×
// Î¹Èñ¸òÄÌÈñ
$res   = array();
$field = array();
$rows  = array();
$sei_ryohi_kin = 0;
$note  = 'À½Â¤·ÐÈñÎ¹Èñ¸òÄÌÈñ';
$query = sprintf("select SUM(kin) from profit_loss_keihi_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='%s'", $str_ym, $end_ym, $note);
if ($rows=getResultWithField2($query, $field, $res) <= 0) {
    $sei_ryohi_kin = 0;
} else {
    $sei_ryohi_kin = $res[0][0];
}
// ³¤³°½ÐÄ¥
$res   = array();
$field = array();
$rows  = array();
$sei_kaigai_kin = 0;
$note  = 'À½Â¤·ÐÈñ³¤³°½ÐÄ¥';
$query = sprintf("select SUM(kin) from profit_loss_keihi_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='%s'", $str_ym, $end_ym, $note);
if ($rows=getResultWithField2($query, $field, $res) <= 0) {
    $sei_kaigai_kin = 0;
} else {
    $sei_kaigai_kin = $res[0][0];
}

// À½Â¤·ÐÈñÎ¹Èñ¸òÄÌÈñ¹ç·×¤Î·×»»
$sei_ryohi_total_kin = $sei_ryohi_kin + $sei_kaigai_kin;

// À½Â¤·ÐÈñ ¶ÈÌ³°ÑÂ÷Èñ¹ç·×
// ¶ÈÌ³°ÑÂ÷Èñ
$res   = array();
$field = array();
$rows  = array();
$sei_gyomu_kin = 0;
$note  = 'À½Â¤·ÐÈñ¶ÈÌ³°ÑÂ÷Èñ';
$query = sprintf("select SUM(kin) from profit_loss_keihi_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='%s'", $str_ym, $end_ym, $note);
if ($rows=getResultWithField2($query, $field, $res) <= 0) {
    $sei_gyomu_kin = 0;
} else {
    $sei_gyomu_kin = $res[0][0];
}
// »ÙÊ§¼ê¿ôÎÁ
$res   = array();
$field = array();
$rows  = array();
$sei_tesu_kin = 0;
$note  = 'À½Â¤·ÐÈñ»ÙÊ§¼ê¿ôÎÁ';
$query = sprintf("select SUM(kin) from profit_loss_keihi_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='%s'", $str_ym, $end_ym, $note);
if ($rows=getResultWithField2($query, $field, $res) <= 0) {
    $sei_tesu_kin = 0;
} else {
    $sei_tesu_kin = $res[0][0];
}

// À½Â¤·ÐÈñ¶ÈÌ³°ÑÂ÷Èñ¹ç·×¤Î·×»»
$sei_gyomu_total_kin = $sei_gyomu_kin + $sei_tesu_kin;

// À½Â¤·ÐÈñ »¨Èñ¹ç·×
// »¨Èñ
$res   = array();
$field = array();
$rows  = array();
$sei_zappi_kin = 0;
$note  = 'À½Â¤·ÐÈñ»¨Èñ';
$query = sprintf("select SUM(kin) from profit_loss_keihi_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='%s'", $str_ym, $end_ym, $note);
if ($rows=getResultWithField2($query, $field, $res) <= 0) {
    $sei_zappi_kin = 0;
} else {
    $sei_zappi_kin = $res[0][0];
}
// ¹­¹ðÀëÅÁÈñ
$res   = array();
$field = array();
$rows  = array();
$sei_kokoku_kin = 0;
$note  = 'À½Â¤·ÐÈñ¹­¹ðÀëÅÁÈñ';
$query = sprintf("select SUM(kin) from profit_loss_keihi_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='%s'", $str_ym, $end_ym, $note);
if ($rows=getResultWithField2($query, $field, $res) <= 0) {
    $sei_kokoku_kin = 0;
} else {
    $sei_kokoku_kin = $res[0][0];
}
// µá¿ÍÈñ
$res   = array();
$field = array();
$rows  = array();
$sei_kyujin_kin = 0;
$note  = 'À½Â¤·ÐÈñµá¿ÍÈñ';
$query = sprintf("select SUM(kin) from profit_loss_keihi_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='%s'", $str_ym, $end_ym, $note);
if ($rows=getResultWithField2($query, $field, $res) <= 0) {
    $sei_kyujin_kin = 0;
} else {
    $sei_kyujin_kin = $res[0][0];
}
// ÊÝ¾Ú½¤ÍýÈñ
$res   = array();
$field = array();
$rows  = array();
$sei_hosyo_kin = 0;
$note  = 'À½Â¤·ÐÈñÊÝ¾Ú½¤ÍýÈñ';
$query = sprintf("select SUM(kin) from profit_loss_keihi_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='%s'", $str_ym, $end_ym, $note);
if ($rows=getResultWithField2($query, $field, $res) <= 0) {
    $sei_hosyo_kin = 0;
} else {
    $sei_hosyo_kin = $res[0][0];
}
// ½ô²ñÈñ
$res   = array();
$field = array();
$rows  = array();
$sei_kaihi_kin = 0;
$note  = 'À½Â¤·ÐÈñ½ô²ñÈñ';
$query = sprintf("select SUM(kin) from profit_loss_keihi_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='%s'", $str_ym, $end_ym, $note);
if ($rows=getResultWithField2($query, $field, $res) <= 0) {
    $sei_kaihi_kin = 0;
} else {
    $sei_kaihi_kin = $res[0][0];
}

// À½Â¤·ÐÈñ»¨Èñ¹ç·×¤Î·×»»
$sei_zappi_total_kin = $sei_zappi_kin + $sei_kokoku_kin + $sei_kyujin_kin + $sei_hosyo_kin + $sei_kaihi_kin;

// À½Â¤·ÐÈñ ÃÏÂå²ÈÄÂ¹ç·×
// ÃÏÂå²ÈÄÂ
$res   = array();
$field = array();
$rows  = array();
$sei_yachin_kin = 0;
$note  = 'À½Â¤·ÐÈñÃÏÂå²ÈÄÂ';
$query = sprintf("select SUM(kin) from profit_loss_keihi_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='%s'", $str_ym, $end_ym, $note);
if ($rows=getResultWithField2($query, $field, $res) <= 0) {
    $sei_yachin_kin = 0;
} else {
    $sei_yachin_kin = $res[0][0];
}
// ÁÒÉßÎÁ
$res   = array();
$field = array();
$rows  = array();
$sei_kura_kin = 0;
$note  = 'À½Â¤·ÐÈñÁÒÉßÎÁ';
$query = sprintf("select SUM(kin) from profit_loss_keihi_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='%s'", $str_ym, $end_ym, $note);
if ($rows=getResultWithField2($query, $field, $res) <= 0) {
    $sei_kura_kin = 0;
} else {
    $sei_kura_kin = $res[0][0];
}

// À½Â¤·ÐÈñÃÏÂå²ÈÄÂ¹ç·×¤Î·×»»
$sei_yachin_total_kin = $sei_yachin_kin + $sei_kura_kin;

// Ï«Ì³Èñ ¸üÀ¸Ê¡ÍøÈñ¹ç·×
// Ë¡ÄêÊ¡ÍøÈñ
$res   = array();
$field = array();
$rows  = array();
$sei_hofukuri_kin = 0;
$note  = 'À½Â¤·ÐÈñË¡ÄêÊ¡ÍøÈñ';
$query = sprintf("select SUM(kin) from profit_loss_keihi_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='%s'", $str_ym, $end_ym, $note);
if ($rows=getResultWithField2($query, $field, $res) <= 0) {
    $sei_hofukuri_kin = 0;
} else {
    $sei_hofukuri_kin = $res[0][0];
}
// ¸üÀ¸Ê¡ÍøÈñ
$res   = array();
$field = array();
$rows  = array();
$sei_kofukuri_kin = 0;
$note  = 'À½Â¤·ÐÈñ¸üÀ¸Ê¡ÍøÈñ';
$query = sprintf("select SUM(kin) from profit_loss_keihi_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='%s'", $str_ym, $end_ym, $note);
if ($rows=getResultWithField2($query, $field, $res) <= 0) {
    $sei_kofukuri_kin = 0;
} else {
    $sei_kofukuri_kin = $res[0][0];
}

// Ï«Ì³Èñ¸üÀ¸Ê¡ÍøÈñ¹ç·×¤Î·×»»
$sei_kofukuri_total_kin = $sei_hofukuri_kin + $sei_kofukuri_kin;

// Ï«Ì³Èñ Âà¿¦µëÉÕÈñÍÑ¹ç·×
// Âà¿¦µëÍ¿¶â
$res   = array();
$field = array();
$rows  = array();
$sei_taikyuyo_kin = 0;
$note  = 'À½Â¤·ÐÈñÂà¿¦µëÍ¿¶â';
$query = sprintf("select SUM(kin) from profit_loss_keihi_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='%s'", $str_ym, $end_ym, $note);
if ($rows=getResultWithField2($query, $field, $res) <= 0) {
    $sei_taikyuyo_kin = 0;
} else {
    $sei_taikyuyo_kin = $res[0][0];
}
// Âà¿¦µëÉÕÈñÍÑ
$res   = array();
$field = array();
$rows  = array();
$sei_taikyufu_kin = 0;
$note  = 'À½Â¤·ÐÈñÂà¿¦µëÉÕÈñÍÑ';
$query = sprintf("select SUM(kin) from profit_loss_keihi_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='%s'", $str_ym, $end_ym, $note);
if ($rows=getResultWithField2($query, $field, $res) <= 0) {
    $sei_taikyufu_kin = 0;
} else {
    $sei_taikyufu_kin = $res[0][0];
}

// Ï«Ì³ÈñÂà¿¦µëÉÕÈñÍÑ¹ç·×¤Î·×»»
$sei_taikyufu_total_kin = $sei_taikyuyo_kin + $sei_taikyufu_kin;

if (isset($_POST['input_data'])) {                        // Åö·î¥Ç¡¼¥¿¤ÎÅÐÏ¿
    ///////// ¹àÌÜ¤È¥¤¥ó¥Ç¥Ã¥¯¥¹¤Î´ØÏ¢ÉÕ¤±
    $item = array();
    $item[0]   = "¸½¶âµÚ¤ÓÍÂ¶â";
    $item[1]   = "Âß¼ÚÀ½ÉÊ";
    $item[2]   = "Âß¼Ú»Å³ÝÉÊ";
    $item[3]   = "Âß¼Ú¸¶ºàÎÁµÚ¤ÓÃùÂ¢ÉÊ";
    $item[4]   = "Ì¤¼ýÆþ¶â";
    $item[5]   = "¤½¤ÎÂ¾¤ÎÎ®Æ°»ñ»º";
    $item[6]   = "·úÊª";
    $item[7]   = "¹½ÃÛÊª";
    $item[8]   = "µ¡³£µÚ¤ÓÁõÃÖ";
    $item[9]   = "¼ÖíÒ±¿ÈÂ¶ñ";
    $item[10]  = "¹©¶ñ´ï¶ñµÚ¤ÓÈ÷ÉÊ";
    $item[11]  = "¥ê¡¼¥¹»ñ»º";
    $item[12]  = "¸º²Á½þµÑÎß·×³Û";
    $item[13]  = "ÅÅÏÃ²ÃÆþ¸¢";
    $item[14]  = "¤½¤ÎÂ¾¤ÎÅê»ñÅù";
    $item[15]  = "Ì¤Ê§¾ÃÈñÀÇÅù";
    $item[16]  = "Çã³Ý¶â";
    $item[17]  = "Ì¤Ê§¶â";
    $item[18]  = "¤½¤ÎÂ¾¤ÎÎ®Æ°ÉéºÄ";
    $item[19]  = "´ü¼ó»Å³ÝÉÊ";
    $item[20]  = "´ü¼ó¸¶ºàÎÁµÚ¤ÓÃùÂ¢ÉÊ";
    $item[21]  = "´üËö»Å³ÝÉÊ";
    $item[22]  = "´üËö¸¶ºàÎÁµÚ¤ÓÃùÂ¢ÉÊ";
    $item[23]  = "»¨¼ýÆþ";
    $item[24]  = "¤½¤ÎÂ¾¤Î±Ä¶È³°ÈñÍÑ";
    $item[25]  = "¸ÇÄê»ñ»º½üµÑÂ»";
    $item[26]  = "ecaË¡¿ÍÀÇ¡¢½»Ì±ÀÇµÚ¤Ó»ö¶ÈÀÇ";
    $item[27]  = "ÈÎ´ÉÈñÎ¹Èñ¸òÄÌÈñ";
    $item[28]  = "ÈÎ´ÉÈñ¹­¹ðÀëÅÁÈñ";
    $item[29]  = "ÈÎ´ÉÈñ¶ÈÌ³°ÑÂ÷Èñ";
    $item[30]  = "ÈÎ´ÉÈñ½ôÀÇ¸ø²Ý";
    $item[31]  = "ÈÎ´ÉÈñ»öÌ³ÍÑ¾ÃÌ×ÉÊÈñ";
    $item[32]  = "ÈÎ´ÉÈñ»¨Èñ";
    $item[33]  = "ÈÎ´ÉÈñÃÏÂå²ÈÄÂ";
    $item[34]  = "ÈÎ´ÉÈñ¸üÀ¸Ê¡ÍøÈñ";
    $item[35]  = "ÈÎ´ÉÈñÂà¿¦µëÉÕÈñÍÑ";
    $item[36]  = "À½Â¤·ÐÈñÎ¹Èñ¸òÄÌÈñ";
    $item[37]  = "À½Â¤·ÐÈñ¶ÈÌ³°ÑÂ÷Èñ";
    $item[38]  = "À½Â¤·ÐÈñ»¨Èñ";
    $item[39]  = "À½Â¤·ÐÈñÃÏÂå²ÈÄÂ";
    $item[40]  = "À½Â¤·ÐÈñ¸üÀ¸Ê¡ÍøÈñ";
    $item[41]  = "À½Â¤·ÐÈñÂà¿¦µëÉÕÈñÍÑ";
    $item[42]  = "¸ÇÄê»ñ»ºÇäµÑÂ»";
    $item[43]  = "Í­½þ»ÙµëÌ¤¼ýÆþ¶â";
    $item[44]  = "Î©ÂØ¶â";
    $item[45]  = "ÌÀºÙÌ¤¼ýÆþ¶â";
    $item[46]  = "²¾Ê§¶â";
    $item[47]  = "ÌÀºÙ¤½¤ÎÂ¾Î®Æ°»ñ»º";
    $item[48]  = "»ñ»º¶â³Û·úÊª";
    $item[49]  = "¸º²Á½þµÑÎß·×³Û(·úÊª)";
    $item[50]  = "»ñ»º¶â³Ûµ¡³£µÚ¤ÓÁõÃÖ";
    $item[51]  = "¸º²Á½þµÑÎß·×³Û(µ¡³£µÚ¤ÓÁõÃÖ)";
    $item[52]  = "»ñ»º¶â³Û¼ÖíÒ±¿ÈÂ¶ñ";
    $item[53]  = "¸º²Á½þµÑÎß·×³Û(¼ÖíÒ±¿ÈÂ¶ñ)";
    $item[54]  = "»ñ»º¶â³Û¹©¶ñ´ï¶ñÈ÷ÉÊ";
    $item[55]  = "¸º²Á½þµÑÎß·×³Û(¹©¶ñ´ï¶ñÈ÷ÉÊ)";
    $item[56]  = "»ñ»º¶â³Û¥ê¡¼¥¹»ñ»º";
    $item[57]  = "¸º²Á½þµÑÎß·×³Û(¥ê¡¼¥¹»ñ»º)";
    $item[58]  = "ecaË¡ÄêÊ¡ÍøÈñ";                       // ÈÎ´ÉÈñ
    $item[59]  = "ecaÊ¡Íø¸üÀ¸Èñ";                       // ÈÎ´ÉÈñ
    $item[60]  = "ecaÁÒÉßÎÁ";                           // ÈÎ´ÉÈñ
    $item[61]  = "ecaÃÏÂå²ÈÄÂ";                         // ÈÎ´ÉÈñ
    $item[62]  = "eca¶ÈÌ³°ÑÂ÷Èñ";                       // ÈÎ´ÉÈñ
    $item[63]  = "eca»ÙÊ§¼ê¿ôÎÁ";                       // ÈÎ´ÉÈñ
    $item[64]  = "ecaµá¿ÍÈñ";                           // ÈÎ´ÉÈñ
    $item[65]  = "eca½ô²ñÈñ";                           // ÈÎ´ÉÈñ
    $item[66]  = "eca»¨Èñ";                             // ÈÎ´ÉÈñ
    $item[67]  = "ecaÌ¤¼ý¼ý±×";                         // ÈÎ´ÉÈñ
    $item[68]  = "»ñ»º¶â³Û¹½ÃÛÊª";
    $item[69]  = "¸º²Á½þµÑÎß·×³Û(¹½ÃÛÊª)";
    $item[70]  = "eca¹­¹ðÀëÅÁÈñ";                       // ÈÎ´ÉÈñ
    $item[71]  = "ÃùÂ¢ÉÊ";
    $item[72]  = "É¾²ÁÀÚ²¼¤²ÉôÉÊ";
    $item[73]  = "É¾²ÁÀÚ²¼¤²ºàÎÁ";
    $item[74]  = "¹©ºî»Å³ÝÌÀºÙ";
    $item[75]  = "³°Ãí»Å³ÝÌÀºÙ";
    $item[76]  = "¸¡ºº»Å³ÝÌÀºÙ";
    $item[77]  = "ÅÅÏÃ´ü¼ó»Ä¹â";
    $item[78]  = "»ÜÀß´ü¼ó»Ä¹â";
    $item[79]  = "¥½¥Õ¥È´ü¼ó»Ä¹â";
    $item[80]  = "ÅÅÏÃ´üÃæÁý²Ã";
    $item[81]  = "»ÜÀß´üÃæÁý²Ã";
    $item[82]  = "¥½¥Õ¥È´üÃæÁý²Ã";
    $item[83]  = "ÅÅÏÃ´üÃæ¸º¾¯";
    $item[84]  = "»ÜÀß´üÃæ¸º¾¯";
    $item[85]  = "¥½¥Õ¥È´üÃæ¸º¾¯";
    ///////// ³Æ¥Ç¡¼¥¿¤ÎÊÝ´É
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
    ///////// ³Æ¥Ç¡¼¥¿¤ÎÅÐÏ¿
    insert_date($item,$yyyymm,$input_data);
}


function insert_date($item,$yyyymm,$input_data) 
{
    $num_input = count($input_data);
    for ($i = 0; $i < $num_input; $i++) {
        $query = sprintf("select rep_kin from financial_report_data where rep_ymd=%d and rep_note='%s'", $yyyymm, $item[$i]);
        $res_in = array();
        if (getResult2($query,$res_in) <= 0) {
            /////////// begin ¥È¥é¥ó¥¶¥¯¥·¥ç¥ó³«»Ï
            if ($con = db_connect()) {
                query_affected_trans($con, "begin");
            } else {
                $_SESSION["s_sysmsg"] .= "¥Ç¡¼¥¿¥Ù¡¼¥¹¤ËÀÜÂ³¤Ç¤­¤Þ¤»¤ó";
                exit();
            }
            ////////// Insert Start
            $query = sprintf("insert into financial_report_data (rep_ymd, rep_kin, rep_note, last_date, last_user) values (%d, %d, '%s', CURRENT_TIMESTAMP, '%s')", $yyyymm, $input_data[$i], $item[$i], $_SESSION['User_ID']);
            if (query_affected_trans($con, $query) <= 0) {
                $_SESSION["s_sysmsg"] .= sprintf("%s¤Î¿·µ¬ÅÐÏ¿¤Ë¼ºÇÔ<br> %d", $item[$i], $yyyymm);
                query_affected_trans($con, "rollback");     // transaction rollback
                exit();
            }
            /////////// commit ¥È¥é¥ó¥¶¥¯¥·¥ç¥ó½ªÎ»
            query_affected_trans($con, "commit");
            $_SESSION["s_sysmsg"] = sprintf("<font color='yellow'>%d ·è»»½ñ¥Ç¡¼¥¿ ¿·µ¬ ÅÐÏ¿´°Î»</font>",$yyyymm);
        } else {
            /////////// begin ¥È¥é¥ó¥¶¥¯¥·¥ç¥ó³«»Ï
            if ($con = db_connect()) {
                query_affected_trans($con, "begin");
            } else {
                $_SESSION["s_sysmsg"] .= "¥Ç¡¼¥¿¥Ù¡¼¥¹¤ËÀÜÂ³¤Ç¤­¤Þ¤»¤ó";
                exit();
            }
            ////////// UPDATE Start
            $query = sprintf("update financial_report_data set rep_kin=%d, last_date=CURRENT_TIMESTAMP, last_user='%s' where rep_ymd=%d and rep_note='%s'", $input_data[$i], $_SESSION['User_ID'], $yyyymm, $item[$i]);
            if (query_affected_trans($con, $query) <= 0) {
                $_SESSION["s_sysmsg"] .= sprintf("%s¤ÎUPDATE¤Ë¼ºÇÔ<br> %d", $item[$i], $yyyymm);
                query_affected_trans($con, "rollback");     // transaction rollback
                exit();
            }
            /////////// commit ¥È¥é¥ó¥¶¥¯¥·¥ç¥ó½ªÎ»
            query_affected_trans($con, "commit");
            $_SESSION["s_sysmsg"] = sprintf("<font color='yellow'>%d ·è»»½ñ¥Ç¡¼¥¿ ÊÑ¹¹ ´°Î»</font>",$yyyymm);
        }
    }
    $_SESSION["s_sysmsg"] .= "·è»»½ñ¤Î¥Ç¡¼¥¿¤òÅÐÏ¿¤·¤Þ¤·¤¿¡£";
}

/////////// HTML Header ¤ò½ÐÎÏ¤·¤Æ¥­¥ã¥Ã¥·¥å¤òÀ©¸æ
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
    return confirm("Åö·î¤Î¥Ç¡¼¥¿¤òÅÐÏ¿¤·¤Þ¤¹¡£\n´û¤Ë¥Ç¡¼¥¿¤¬¤¢¤ë¾ì¹ç¤Ï¾å½ñ¤­¤µ¤ì¤Þ¤¹¡£");
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
            //  bgcolor='#ceffce' ²«ÎÐ
            //  bgcolor='#ffffc6' Çö¤¤²«¿§
            //  bgcolor='#d6d3ce' Win ¥°¥ì¥¤
        ?>
    <!--------------- ¤³¤³¤«¤éËÜÊ¸¤ÎÉ½¤òÉ½¼¨¤¹¤ë -------------------->
        <table bgcolor='#ffffff' align='center' cellspacing='0' cellpadding='10'>
            <tr><td> <!----------- ¥À¥ß¡¼(¥Ç¥¶¥¤¥óÍÑ) ------------>
        <center>¡ÊÂß¼ÚÂÐ¾ÈÉ½¡Ë</center>
        <table class='winbox_field' width='100%' align='center' bgcolor='black' cellspacing='1' cellpadding='5'>
            <THEAD>
                <!-- ¥Æ¡¼¥Ö¥ë ¥Ø¥Ã¥À¡¼¤ÎÉ½¼¨ -->
                <tr>
                    <th class='winbox' nowrap rowspan='2'>¡¡</th>
                    <th class='winbox' nowrap colspan='4'>»î»»É½</th>
                    <th class='winbox' nowrap colspan='3'>·è»»½ñ(B/S)</th>
                </tr>
                <tr>
                    <th class='winbox' nowrap colspan='2'>´ªÄê²ÊÌÜ</th>
                    <th class='winbox' nowrap>¶â³Û</th>
                    <th class='winbox' nowrap>È÷¹Í</th>
                    <th class='winbox' nowrap colspan='2'>´ªÄê²ÊÌÜ</th>
                    <th class='winbox' nowrap>¶â³Û</th>
                </tr>
            </THEAD>
            <TFOOT>
                <!-- ¸½ºß¤Ï¥Õ¥Ã¥¿¡¼¤Ï²¿¤â¤Ê¤¤ -->
            </TFOOT>
            <TBODY>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' rowspan='5' valign='top'>
                        <div class='pt10b'>£±</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' rowspan='5' valign='top'>
                        <div class='pt10b'>¸½¶â</div><BR>
                        <div class='pt10b'>ÍÂ¶â</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#fde9d9'>
                        <div class='pt10b'>¸½¶â</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ccffcc' align='right'>
                        <div class='pt11b'><?= number_format($genkin_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>¡¡</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' rowspan='5' valign='top'>
                        <div class='pt10b'>Î®Æ°»ñ»º</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>¸½¶âµÚ¤ÓÍÂ¶â</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffc6' align='right'>
                        <div class='pt11b'><?= number_format($genyo_total_kin) ?></div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#fde9d9'>
                        <div class='pt10b'>ÅöºÂÍÂ¶â</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ccffcc' align='right'>
                        <div class='pt11b'><?= number_format($touza_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>¡¡</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>¡¡</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>¡¡</div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#fde9d9'>
                        <div class='pt10b'>ÉáÄÌÍÂ¶â</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ccffcc' align='right'>
                        <div class='pt11b'><?= number_format($futsu_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>¡¡</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>¡¡</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>¡¡</div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#fde9d9'>
                        <div class='pt10b'>Äê´üÍÂ¶â</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ccffcc' align='right'>
                        <div class='pt11b'><?= number_format($teiki_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>Âç¸ýÄê´ü</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>¡¡</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>¡¡</div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>¹ç·×</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right'>
                        <div class='pt11b'><?= number_format($genyo_total_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>¡¡</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>¹ç·×</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right'>
                        <div class='pt11b'><?= number_format($genyo_total_kin) ?></div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' rowspan='8' valign='top'>
                        <div class='pt10b'>£²</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' rowspan='8' valign='top'>
                        <div class='pt10b'>ºß¸Ë</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>À½ÉÊ</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right'>
                        <div class='pt11b'><?= number_format($seihin_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>¡¡</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' rowspan='8' valign='top'>
                        <div class='pt10b'>Î®Æ°»ñ»º</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>À½ÉÊ</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right'>
                        <div class='pt11b'><?= number_format($seihin_total_kin) ?></div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>À½ÉÊ»Å³ÝÉÊ</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ccffcc' align='right'>
                        <div class='pt11b'><?= number_format($seihinsi_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>¡¡</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>»Å³ÝÉÊ</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right'>
                        <div class='pt11b'><?= number_format($sikakari_total_kin) ?></div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>ÉôÉÊ</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ccffcc' align='right'>
                        <div class='pt11b'><?= number_format($buhin_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>¡¡</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>¸¶ºàÎÁµÚ¤ÓÃùÂ¢ÉÊ</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right'>
                        <div class='pt11b'><?= number_format($gencho_total_kin) ?></div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>ÉôÉÊ»Å³ÝÉÊ</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ccffcc' align='right'>
                        <div class='pt11b'><?= number_format($buhinsi_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>¡¡</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>¡¡</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>¡¡</div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>¸¶ºàÎÁ</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ccffcc' align='right'>
                        <div class='pt11b'><?= number_format($genzai_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>¡¡</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>¡¡</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>¡¡</div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>¤½¤ÎÂ¾¤ÎÃª²·ÉÊ</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ccffcc' align='right'>
                        <div class='pt11b'><?= number_format($sonotatana_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>¡¡</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>¡¡</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>¡¡</div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>ÃùÂ¢ÉÊ</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ccffcc' align='right'>
                        <div class='pt11b'><?= number_format($chozo_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>¡¡</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>¡¡</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>¡¡</div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>¹ç·×</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right'>
                        <div class='pt11b'><?= number_format($zaiko_total_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>¡¡</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>¹ç·×</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right'>
                        <div class='pt11b'><?= number_format($ryudozaiko_total_kin) ?></div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' rowspan='8' valign='top'>
                        <div class='pt10b'>£³</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' rowspan='8' valign='top'>
                        <div class='pt10b'>Î®Æ°»ñ»º</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#fde9d9'>
                        <div class='pt10b'>Í­½þ»ÙµëÌ¤¼ýÆþ¶â</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ccffcc' align='right'>
                        <div class='pt11b'><?= number_format($yumi_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>¡¡</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' rowspan='8' valign='top'>
                        <div class='pt10b'>Î®Æ°»ñ»º</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>Ì¤¼ýÆþ¶â</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffc6' align='right'>
                        <div class='pt11b'><?= number_format($ryu_mishu_kin) ?></div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#fde9d9'>
                        <div class='pt10b'>Ì¤¼ýÆþ¶â</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ccffcc' align='right'>
                        <div class='pt11b'><?= number_format($mishu_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>¡¡</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>¡¡</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right'>
                        <div class='pt11b'>¡¡</div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#fde9d9'>
                        <div class='pt10b'>Ì¤¼ý¼ý±×</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ccffcc' align='right'>
                        <div class='pt11b'><?= number_format($mishueki_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>¡¡</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>¡¡</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right'>
                        <div class='pt11b'>¡¡</div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>¹ç·×</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right'>
                        <div class='pt11b'><?= number_format($ryu_mishu_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>¡¡</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>¹ç·×</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right'>
                        <div class='pt11b'><?= number_format($ryu_mishu_total_kin) ?></div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#fde9d9'>
                        <div class='pt10b'>Î©ÂØ¶â</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ccffcc' align='right'>
                        <div class='pt11b'><?= number_format($tatekae_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>¡¡</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>¤½¤ÎÂ¾Î®Æ°»ñ»º</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffc6' align='right'>
                        <div class='pt11b'><?= number_format($hokaryudo_total_kin) ?></div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#fde9d9'>
                        <div class='pt10b'>²¾Ê§¶â</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ccffcc' align='right'>
                        <div class='pt11b'><?= number_format($karibara_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>¡¡</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>¡¡</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>¡¡</div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>¤½¤ÎÂ¾¤ÎÎ®Æ°»ñ»º</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ccffcc' align='right'>
                        <div class='pt11b'><?= number_format($hokaryudo_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>¡¡</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>¡¡</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>¡¡</div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>¹ç·×</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right'>
                        <div class='pt11b'><?= number_format($hokaryudo_total_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>¡¡</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>¹ç·×</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right'>
                        <div class='pt11b'><?= number_format($hokaryudo_all_kin) ?></div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' rowspan='10' valign='top'>
                        <div class='pt10b'>£´</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' rowspan='10' valign='top'>
                        <div class='pt10b'>Í­·Á</div><BR>
                        <div class='pt10b'>¸ÇÄê»ñ»º</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>·úÊª</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ccffcc' align='right'>
                        <div class='pt11b'><?= number_format($tatemono_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#cc99ff' align='right'>
                        <div class='pt11b'><?= number_format($tate_gen_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' rowspan='10' valign='top'>
                        <div class='pt10b'>Í­·Á</div><BR>
                        <div class='pt10b'>¸ÇÄê»ñ»º</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>·úÊª</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffc6' align='right'>
                        <div class='pt11b'><?= number_format($tate_all_shisan_kin) ?></div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>ÀßÈ÷</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ccffcc' align='right'>
                        <div class='pt11b'><?= number_format($fuzoku_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#cc99ff' align='right'>
                        <div class='pt11b'><?= number_format($fuzoku_gen_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>¡ÊÀßÈ÷²Ã»»¡Ë</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>¡¡</div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>¹½ÃÛÊª</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ccffcc' align='right'>
                        <div class='pt11b'><?= number_format($kouchiku_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#cc99ff' align='right'>
                        <div class='pt11b'><?= number_format($kouchiku_gen_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>¹½ÃÛÊª</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffc6' align='right'>
                        <div class='pt11b'><?= number_format($kouchiku_shisan_kin) ?></div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>µ¡³£ÁõÃÖ</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ccffcc' align='right'>
                        <div class='pt11b'><?= number_format($kikai_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#cc99ff' align='right'>
                        <div class='pt11b'><?= number_format($kikai_gen_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>µ¡³£µÚ¤ÓÁõÃÖ</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffc6' align='right'>
                        <div class='pt11b'><?= number_format($kikai_shisan_kin) ?></div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>¼ÖíÒ±¿ÈÂ¶ñ</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ccffcc' align='right'>
                        <div class='pt11b'><?= number_format($sharyo_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#cc99ff' align='right'>
                        <div class='pt11b'><?= number_format($sharyo_gen_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>¼ÖíÒ±¿ÈÂ¶ñ</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffc6' align='right'>
                        <div class='pt11b'><?= number_format($sharyo_shisan_kin) ?></div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>´ï¶ñ¹©¶ñ</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ccffcc' align='right'>
                        <div class='pt11b'><?= number_format($kigu_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#cc99ff' align='right'>
                        <div class='pt11b'><?= number_format($kigu_gen_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>¹©¶ñ´ï¶ñµÚ¤ÓÈ÷ÉÊ</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffc6' align='right'>
                        <div class='pt11b'><?= number_format($jyubihin_all_shisan_kin) ?></div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>½º´ïÈ÷ÉÊ</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ccffcc' align='right'>
                        <div class='pt11b'><?= number_format($jyuki_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#cc99ff' align='right'>
                        <div class='pt11b'><?= number_format($jyuki_gen_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>¥ê¡¼¥¹»ñ»º</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffc6' align='right'>
                        <div class='pt11b'><?= number_format($lease_shisan_kin) ?></div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>¥ê¡¼¥¹»ñ»º</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ccffcc' align='right'>
                        <div class='pt11b'><?= number_format($lease_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#cc99ff' align='right'>
                        <div class='pt11b'><?= number_format($lease_gen_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>¹ç·×</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right'>
                        <div class='pt11b'><?= number_format($boka_totai_kin) ?></div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>¸º²Á½þµÑÎß·×³Û</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ccffcc' align='right'>
                        <div class='pt11b'><?= number_format($gensyo_total_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>¡¡</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>¡¡</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>¡¡</div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>Êí²Á</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right'>
                        <div class='pt11b'><?= number_format($boka_totai_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>¡¡</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>¸º²Á½þµÑÎß·×³Û</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right'>
                        <div class='pt11b'><?= number_format($gensyo_total_mi_kin) ?></div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' rowspan='3' valign='top'>
                        <div class='pt10b'>£µ</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' rowspan='3' valign='top'>
                        <div class='pt10b'>Ìµ·Á</div><BR>
                        <div class='pt10b'>¸ÇÄê»ñ»º</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#fde9d9'>
                        <div class='pt10b'>ÅÅÏÃ²ÃÆþ¸¢</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ccffcc' align='right'>
                        <div class='pt11b'><?= number_format($denwa_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>¡¡</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' rowspan='3' valign='top'>
                        <div class='pt10b'>Ìµ·Á</div><BR>
                        <div class='pt10b'>¸ÇÄê»ñ»º</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>ÅÅÏÃ²ÃÆþ¸¢</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right'>
                        <div class='pt11b'><?= number_format($denwa_kin) ?></div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>¡¡</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right'>
                        <div class='pt11b'>¡¡</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>¡¡</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>¡¡</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>¡¡</div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>¹ç·×</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right'>
                        <div class='pt11b'><?= number_format($denwa_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>¡¡</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>¹ç·×</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right'>
                        <div class='pt11b'><?= number_format($denwa_kin) ?></div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' rowspan='3' valign='top'>
                        <div class='pt10b'>£¶</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' rowspan='3' valign='top'>
                        <div class='pt10b'>Åê»ñÅù</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#fde9d9'>
                        <div class='pt10b'>½Ð»ñ¶â</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ccffcc' align='right'>
                        <div class='pt11b'><?= number_format($shussi_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>¡¡</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' rowspan='3' valign='top'>
                        <div class='pt10b'>Åê»ñÅù</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>¤½¤ÎÂ¾¤ÎÅê»ñÅù</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right'>
                        <div class='pt11b'><?= number_format($toushi_total_kin) ?></div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>º¹ÆþÉß¶âÊÝ¾Ú¶â</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ccffcc' align='right'>
                        <div class='pt11b'><?= number_format($hosyo_kin) ?></div>
                    </td>   
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>¡¡</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>¡¡</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>¡¡</div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>¹ç·×</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right'>
                        <div class='pt11b'><?= number_format($toushi_total_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>¡¡</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>¹ç·×</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right'>
                        <div class='pt11b'><?= number_format($toushi_total_kin) ?></div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' rowspan='15' valign='top'>
                        <div class='pt10b'>£·</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' rowspan='5' valign='top'>
                        <div class='pt10b'>Î®Æ°ÉéºÄ</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>²¾Ê§¾ÃÈñÀÇÅù</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ccffcc' align='right'>
                        <div class='pt11b'><?= number_format($kari_zei_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>(²¾¾Ã Í¢Æþ´Þ¤à)</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' rowspan='5' valign='top'>
                        <div class='pt10b'>Î®Æ°ÉéºÄ</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>Ì¤Ê§¾ÃÈñÀÇÅù</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffc6' align='right'>
                        <div class='pt11b'><?= number_format($mihazei_total_kin) ?></div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>Á°Ê§¾ÃÈñÀÇ</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ccffcc' align='right'>
                        <div class='pt11b'><?= number_format($mae_zei_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>¡¡</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>¡¡</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right'>
                        <div class='pt11b'>¡¡</div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>²¾¼õ¾ÃÈñÀÇÅù</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ccffcc' align='right'>
                        <div class='pt11b'><?= number_format($kariuke_zei_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>¡¡</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>¡¡</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right'>
                        <div class='pt11b'>¡¡</div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>Ì¤Ê§¾ÃÈñÀÇÅù</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ccffcc' align='right'>
                        <div class='pt11b'><?= number_format($miharai_zei_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>(»ÍÈ¾´ü·×¾åÊ¬)</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>¡¡</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right'>
                        <div class='pt11b'>¡¡</div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>¹ç·×</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right'>
                        <div class='pt11b'><?= number_format($mihazei_total_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>¡¡</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>¹ç·×</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right'>
                        <div class='pt11b'><?= number_format($mihazei_total_kin) ?></div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' rowspan='10' valign='top'>
                        <div class='pt10b'>Î®Æ°ÉéºÄ</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#fde9d9'>
                        <div class='pt10b'>Çã³Ý¶â</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ccffcc' align='right'>
                        <div class='pt11b'><?= number_format($kaikake_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>¡¡</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' rowspan='3' valign='top'>
                        <div class='pt10b'>Î®Æ°ÉéºÄ</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>Çã³Ý¶â</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffc6' align='right'>
                        <div class='pt11b'><?= number_format($kaikake_total_kin) ?></div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>Çã³Ý¶â´üÆü¿¶¹þ</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ccffcc' align='right'>
                        <div class='pt11b'><?= number_format($kaikake_kiji_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>¡¡</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>¡¡</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>¡¡</div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>¹ç·×</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right'>
                        <div class='pt11b'><?= number_format($kaikake_total_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>¡¡</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>¹ç·×</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right'>
                        <div class='pt11b'><?= number_format($kaikake_total_kin) ?></div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#fde9d9'>
                        <div class='pt10b'>Ì¤Ê§¶â</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ccffcc' align='right'>
                        <div class='pt11b'><?= number_format($miharai_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>¡¡</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' rowspan='3' valign='top'>
                        <div class='pt10b'>Î®Æ°ÉéºÄ</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>Ì¤Ê§¶â</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffc6' align='right'>
                        <div class='pt11b'><?= number_format($miharai_total_kin) ?></div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>Ì¤Ê§¶â´üÆü»ØÄê</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ccffcc' align='right'>
                        <div class='pt11b'><?= number_format($miharai_kiji_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>¡¡</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>¡¡</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>¡¡</div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>¹ç·×</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right'>
                        <div class='pt11b'><?= number_format($miharai_total_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>¡¡</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>¹ç·×</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right'>
                        <div class='pt11b'><?= number_format($miharai_total_kin) ?></div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>Á°¼õ¶â</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ccffcc' align='right'>
                        <div class='pt11b'><?= number_format($maeuke_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>¡¡</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' rowspan='3' valign='top'>
                        <div class='pt10b'>Î®Æ°ÉéºÄ</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>¤½¤ÎÂ¾¤ÎÎ®Æ°ÉéºÄ</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffc6' align='right'>
                        <div class='pt11b'><?= number_format($sonota_ryudo_total_kin) ?></div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>¤½¤ÎÂ¾¤ÎÎ®Æ°ÉéºÄ</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ccffcc' align='right'>
                        <div class='pt11b'><?= number_format($sonota_ryudo_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>¡¡</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>¡¡</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>¡¡</div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>¹ç·×</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right'>
                        <div class='pt11b'><?= number_format($sonota_ryudo_total_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>¡¡</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>¹ç·×</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right'>
                        <div class='pt11b'><?= number_format($sonota_ryudo_total_kin) ?></div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>¹ç·×</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right'>
                        <div class='pt11b'><?= number_format($ryudo_fusai_total_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>¡¡</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>¡¡</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>¹ç·×</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right'>
                        <div class='pt11b'><?= number_format($ryudo_fusai_total_kin) ?></div>
                    </td>
                </tr>
            </TBODY>
        </table>
            </td></tr>
        </table> <!----------------- ¥À¥ß¡¼End ------------------>
        
        <table bgcolor='#ffffff' align='center' cellspacing='0' cellpadding='10'>
            <tr><td> <!----------- ¥À¥ß¡¼(¥Ç¥¶¥¤¥óÍÑ) ------------>
        <center>¡ÊÂ»±×·×»»½ñ¡Ë</center>
        <table class='winbox_field' width='100%' align='center' bgcolor='black' cellspacing='1' cellpadding='5'>
            <THEAD>
                <!-- ¥Æ¡¼¥Ö¥ë ¥Ø¥Ã¥À¡¼¤ÎÉ½¼¨ -->
                <tr>
                    <th class='winbox' nowrap rowspan='2'>¡¡</th>
                    <th class='winbox' nowrap colspan='4'>»î»»É½</th>
                    <th class='winbox' nowrap colspan='3'>·è»»½ñ(P/L,À½Â¤¸¶²ÁÊó¹ð½ñ¡¢·ÐÈñÌÀºÙ½ñ¡Ë</th>
                </tr>
                <tr>
                    <th class='winbox' nowrap colspan='2'>´ªÄê²ÊÌÜ</th>
                    <th class='winbox' nowrap>¶â³Û</th>
                    <th class='winbox' nowrap>È÷¹Í</th>
                    <th class='winbox' nowrap colspan='2'>´ªÄê²ÊÌÜ</th>
                    <th class='winbox' nowrap>¶â³Û</th>
                </tr>
                <tr>
                    <th class='winbox' nowrap>No.</th>
                    <th class='winbox' nowrap colspan='7'>À½Â¤¸¶²ÁÊó¹ð½ñ</th>
                </tr>
            </THEAD>
            <TFOOT>
                <!-- ¸½ºß¤Ï¥Õ¥Ã¥¿¡¼¤Ï²¿¤â¤Ê¤¤ -->
            </TFOOT>
            <TBODY>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' rowspan='5' valign='top'>
                        <div class='pt10b'>£±</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' rowspan='5' valign='top'>
                        <div class='pt10b'>ºàÎÁÈñ</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>´ü¼óÃª²·¹â</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right'>
                        <div class='pt11b'><?= number_format($z_zaiko_total_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>¡¡</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' rowspan='5' valign='top'>
                        <div class='pt10b'>Â»±×·×»»½ñ</div><BR>
                        <div class='pt10b'>À½Â¤¸¶²Á</div><BR>
                        <div class='pt10b'>Êó¹ð½ñ</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>´ü¼óÀ½ÉÊÃª²·¹â</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ccffcc' align='right'>
                        <div class='pt10b'>¡¡</div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>¡¡</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>¡¡</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>¡¡</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>»Å³ÝÉÊ</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ccffcc' align='right'>
                        <div class='pt11b'><?= number_format($z_sikakari_total_kin) ?></div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>¡¡</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>¡¡</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>¡¡</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>¸¶ºàÎÁµÚ¤ÓÃùÂ¢ÉÊ</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ccffcc' align='right'>
                        <div class='pt11b'><?= number_format($z_gencho_total_kin) ?></div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>¡¡</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>¡¡</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>¡¡</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>¡¡</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ccffcc' align='right'>
                        <div class='pt11b'>¡¡</div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>¹ç·×</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right'>
                        <div class='pt11b'><?= number_format($z_zaiko_total_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>¡¡</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>¹ç·×</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right'>
                        <div class='pt11b'><?= number_format($z_zaiko_total_kin) ?></div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' rowspan='5' valign='top'>
                        <div class='pt10b'>£²</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' rowspan='5' valign='top'>
                        <div class='pt10b'>ºàÎÁÈñ</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>´üËöÃª²·¹â</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right'>
                        <div class='pt11b'><?= number_format($kimatsu_total_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>¡¡</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' rowspan='5' valign='top'>
                        <div class='pt10b'>Â»±×·×»»½ñ</div><BR>
                        <div class='pt10b'>À½Â¤¸¶²Á</div><BR>
                        <div class='pt10b'>Êó¹ð½ñ</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>´üËöÀ½ÉÊÃª²·¹â</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ccffcc' align='right'>
                        <div class='pt10b'>¡¡</div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>¡¡</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>¡¡</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>¡¡</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>»Å³ÝÉÊ</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ccffcc' align='right'>
                        <div class='pt11b'><?= number_format($kimatsu_sikakari_total_kin) ?></div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>¡¡</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>¡¡</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>¡¡</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>¸¶ºàÎÁµÚ¤ÓÃùÂ¢ÉÊ</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ccffcc' align='right'>
                        <div class='pt11b'><?= number_format($kimatsu_gencho_total_kin) ?></div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>¡¡</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>¡¡</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>¡¡</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>¡¡</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ccffcc' align='right'>
                        <div class='pt11b'>¡¡</div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>¹ç·×</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right'>
                        <div class='pt11b'><?= number_format($kimatsu_total_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>¡¡</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>¹ç·×</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right'>
                        <div class='pt11b'><?= number_format($kimatsu_total_kin) ?></div>
                    </td>
                </tr>
                <tr>
                    <th class='winbox' nowrap>No.</th>
                    <th class='winbox' nowrap colspan='7'>P / L   ±Ä¶È³°Â»±×¡¢ÆÃÊÌÂ»±×¡¢Â¾</th>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' rowspan='3' valign='top'>
                        <div class='pt10b'>£±</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' rowspan='3' valign='top'>
                        <div class='pt10b'>±Ä¶È³°¼ý±×</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>»¨¼ýÆþ</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ccffcc' align='right'>
                        <div class='pt11b'><?= number_format($zatsushu_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>¡¡</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' rowspan='3' valign='top'>
                        <div class='pt10b'>±Ä¶È³°¼ý±×</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>»¨¼ýÆþ</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right'>
                        <div class='pt11b'><?= number_format($eigyo_shueki_total_kin) ?></div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>¶ÈÌ³°ÑÂ÷¼ýÆþ</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ccffcc' align='right'>
                        <div class='pt11b'><?= number_format($gyomushu_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>¡¡</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>¡¡</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>¡¡</div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>¹ç·×</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right'>
                        <div class='pt11b'><?= number_format($eigyo_shueki_total_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>¡¡</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>¹ç·×</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right'>
                        <div class='pt11b'><?= number_format($eigyo_shueki_total_kin) ?></div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' rowspan='5' valign='top'>
                        <div class='pt10b'>£²</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' rowspan='5' valign='top'>
                        <div class='pt10b'>±Ä¶È³°ÈñÍÑ</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>¤½¤ÎÂ¾¤Î±Ä¶È³°ÈñÍÑ</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ccffcc' align='right'>
                        <div class='pt11b'><?= number_format($sonota_eihiyo_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>¡¡</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' rowspan='2' valign='top'>
                        <div class='pt10b'>±Ä¶È³°ÈñÍÑ</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>¤½¤ÎÂ¾¤Î±Ä¶È³°ÈñÍÑ</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right'>
                        <div class='pt11b'><?= number_format($sonota_eihiyo_kin) ?></div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>¹ç·×</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right'>
                        <div class='pt11b'><?= number_format($sonota_eihiyo_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>¡¡</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>¹ç·×</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right'>
                        <div class='pt11b'><?= number_format($sonota_eihiyo_kin) ?></div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>¸ÇÄê»ñ»ºÇäµÑÂ»</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ccffcc' align='right'>
                        <div class='pt11b'><?= number_format($kotei_baison_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>¡¡</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' rowspan='3' valign='top'>
                        <div class='pt10b'>±Ä¶È³°ÈñÍÑ</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>¸ÇÄê»ñ»ºÇäµÑÂ»</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right'>
                        <div class='pt11b'><?= number_format($kotei_baison_kin) ?></div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>¸ÇÄê»ñ»º½üµÑÂ»</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ccffcc' align='right'>
                        <div class='pt11b'><?= number_format($kotei_jyoson_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>¡¡</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>¸ÇÄê»ñ»º½üµÑÂ»</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right'>
                        <div class='pt11b'><?= number_format($kotei_jyoson_kin) ?></div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>¹ç·×</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right'>
                        <div class='pt11b'><?= number_format($kotei_son_total) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>¡¡</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>¹ç·×</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right'>
                        <div class='pt11b'><?= number_format($kotei_son_total) ?></div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' rowspan='4' valign='top'>
                        <div class='pt10b'>£³</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' rowspan='4' valign='top'>
                        <div class='pt10b'>Ë¡¿ÍÀÇÅù</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>Ë¡¿ÍÀÇµÚ¤Ó½»Ì±ÀÇ</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ccffcc' align='right'>
                        <div class='pt11b'><?= number_format($hojin_jyumin_zei_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>¡¡</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' rowspan='4' valign='top'>
                        <div class='pt10b'>Ë¡¿ÍÀÇÅù</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' rowspan='2' valign='top'>
                        <div class='pt10b'>Ë¡¿ÍÀÇ¡¢½»Ì±ÀÇ</div><BR>
                        <div class='pt10b'>µÚ¤Ó»ö¶ÈÀÇ</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right'  rowspan='2'>
                        <div class='pt11b'><?= number_format($hojin_zeito_total_kin) ?></div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>»ö¶ÈÀÇ</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ccffcc' align='right'>
                        <div class='pt11b'><?= number_format($jigyo_zei_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>¡¡</div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>Ë¡¿ÍÀÇÅùÄ´À°³Û</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ccffcc' align='right'>
                        <div class='pt11b'><?= number_format($hojin_chosei_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>¡¡</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>¡¡</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>¡¡</div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>¹ç·×</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right'>
                        <div class='pt11b'><?= number_format($hojin_zeito_total_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>¡¡</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>¹ç·×</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right'>
                        <div class='pt11b'><?= number_format($hojin_zeito_total_kin) ?></div>
                    </td>
                </tr>
                <tr>
                    <th class='winbox' nowrap>No.</th>
                    <th class='winbox' nowrap colspan='7'>·Ð    Èñ    ÌÀ   ºÙ    ½ñ</th>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' rowspan='3' valign='top'>
                        <div class='pt10b'>£±</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' rowspan='3' valign='top'>
                        <div class='pt10b'>ÈÎ´ÉÈñ</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>Î¹Èñ¸òÄÌÈñ</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ccffcc' align='right'>
                        <div class='pt11b'><?= number_format($han_ryohi_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>¡¡</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' rowspan='3' valign='top'>
                        <div class='pt10b'>ÈÎ´ÉÈñ</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>Î¹Èñ¸òÄÌÈñ</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffc6' align='right'>
                        <div class='pt11b'><?= number_format($han_ryohi_total_kin) ?></div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>³¤³°½ÐÄ¥Èñ</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ccffcc' align='right'>
                        <div class='pt11b'><?= number_format($han_kaigai_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>¡¡</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>¡¡</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right'>
                        <div class='pt11b'>¡¡</div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>¹ç·×</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right'>
                        <div class='pt11b'><?= number_format($han_ryohi_total_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>¡¡</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>¹ç·×</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right'>
                        <div class='pt11b'><?= number_format($han_ryohi_total_kin) ?></div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' rowspan='3' valign='top'>
                        <div class='pt10b'>£²</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' rowspan='3' valign='top'>
                        <div class='pt10b'>ÈÎ´ÉÈñ</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>¹­¹ðÀëÅÁÈñ</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ccffcc' align='right'>
                        <div class='pt11b'><?= number_format($han_kokoku_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>¡¡</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' rowspan='3' valign='top'>
                        <div class='pt10b'>ÈÎ´ÉÈñ</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>¹­¹ðÀëÅÁÈñ</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffc6' align='right'>
                        <div class='pt11b'><?= number_format($han_kokoku_total_kin) ?></div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>µá¿ÍÈñ</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ccffcc' align='right'>
                        <div class='pt11b'><?= number_format($han_kyujin_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>¡¡</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>¡¡</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right'>
                        <div class='pt11b'>¡¡</div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>¹ç·×</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right'>
                        <div class='pt11b'><?= number_format($han_kokoku_total_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>¡¡</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>¹ç·×</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right'>
                        <div class='pt11b'><?= number_format($han_kokoku_total_kin) ?></div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' rowspan='3' valign='top'>
                        <div class='pt10b'>£³</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' rowspan='3' valign='top'>
                        <div class='pt10b'>ÈÎ´ÉÈñ</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>¶ÈÌ³°ÑÂ÷Èñ</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ccffcc' align='right'>
                        <div class='pt11b'><?= number_format($han_gyomu_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>¡¡</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' rowspan='3' valign='top'>
                        <div class='pt10b'>ÈÎ´ÉÈñ</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>¶ÈÌ³°ÑÂ÷Èñ</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffc6' align='right'>
                        <div class='pt11b'><?= number_format($han_gyomu_total_kin) ?></div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>»ÙÊ§¼ê¿ôÎÁ</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ccffcc' align='right'>
                        <div class='pt11b'><?= number_format($han_tesu_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>¡¡</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>¡¡</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right'>
                        <div class='pt11b'>¡¡</div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>¹ç·×</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right'>
                        <div class='pt11b'><?= number_format($han_gyomu_total_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>¡¡</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>¹ç·×</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right'>
                        <div class='pt11b'><?= number_format($han_gyomu_total_kin) ?></div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' rowspan='3' valign='top'>
                        <div class='pt10b'>£´</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' rowspan='3' valign='top'>
                        <div class='pt10b'>ÈÎ´ÉÈñ</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>»ö¶ÈÅù</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ccffcc' align='right'>
                        <div class='pt11b'><?= number_format($han_jigyo_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>¡¡</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' rowspan='3' valign='top'>
                        <div class='pt10b'>ÈÎ´ÉÈñ</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>½ôÀÇ¸ø²Ý</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffc6' align='right'>
                        <div class='pt11b'><?= number_format($han_zeikoka_total_kin) ?></div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>½ôÀÇ¸ø²Ý</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ccffcc' align='right'>
                        <div class='pt11b'><?= number_format($han_zeikoka_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>¡¡</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>¡¡</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right'>
                        <div class='pt11b'>¡¡</div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>¹ç·×</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right'>
                        <div class='pt11b'><?= number_format($han_zeikoka_total_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>¡¡</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>¹ç·×</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right'>
                        <div class='pt11b'><?= number_format($han_zeikoka_total_kin) ?></div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' rowspan='3' valign='top'>
                        <div class='pt10b'>£µ</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' rowspan='3' valign='top'>
                        <div class='pt10b'>ÈÎ´ÉÈñ</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>»öÌ³ÍÑ¾ÃÌ×ÉÊÈñ</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ccffcc' align='right'>
                        <div class='pt11b'><?= number_format($han_jimuyo_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>¡¡</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' rowspan='3' valign='top'>
                        <div class='pt10b'>ÈÎ´ÉÈñ</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>»öÌ³ÍÑ¾ÃÌ×ÉÊÈñ</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffc6' align='right'>
                        <div class='pt11b'><?= number_format($han_jimuyo_total_kin) ?></div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>¹©¾ì¾ÃÌ×ÉÊÈñ</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ccffcc' align='right'>
                        <div class='pt11b'><?= number_format($han_kojyo_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>¡¡</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>¡¡</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right'>
                        <div class='pt11b'>¡¡</div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>¹ç·×</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right'>
                        <div class='pt11b'><?= number_format($han_jimuyo_total_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>¡¡</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>¹ç·×</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right'>
                        <div class='pt11b'><?= number_format($han_jimuyo_total_kin) ?></div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' rowspan='4' valign='top'>
                        <div class='pt10b'>£¶</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' rowspan='4' valign='top'>
                        <div class='pt10b'>ÈÎ´ÉÈñ</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>»¨Èñ</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ccffcc' align='right'>
                        <div class='pt11b'><?= number_format($han_zappi_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>¡¡</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' rowspan='4' valign='top'>
                        <div class='pt10b'>ÈÎ´ÉÈñ</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>»¨Èñ</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffc6' align='right'>
                        <div class='pt11b'><?= number_format($han_zappi_total_kin) ?></div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>ÊÝ¾Ú½¤ÍýÈñ</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ccffcc' align='right'>
                        <div class='pt11b'><?= number_format($han_hosyo_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>¡¡</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>¡¡</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right'>
                        <div class='pt11b'>¡¡</div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>½ô²ñÈñ</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ccffcc' align='right'>
                        <div class='pt11b'><?= number_format($han_kaihi_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>¡¡</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>¡¡</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right'>
                        <div class='pt11b'>¡¡</div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>¹ç·×</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right'>
                        <div class='pt11b'><?= number_format($han_zappi_total_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>¡¡</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>¹ç·×</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right'>
                        <div class='pt11b'><?= number_format($han_zappi_total_kin) ?></div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' rowspan='3' valign='top'>
                        <div class='pt10b'>£·</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' rowspan='3' valign='top'>
                        <div class='pt10b'>ÈÎ´ÉÈñ</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>ÃÏÂå²ÈÄÂ</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ccffcc' align='right'>
                        <div class='pt11b'><?= number_format($han_yachin_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>¡¡</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' rowspan='3' valign='top'>
                        <div class='pt10b'>ÈÎ´ÉÈñ</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>ÃÏÂå²ÈÄÂ</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffc6' align='right'>
                        <div class='pt11b'><?= number_format($han_yachin_total_kin) ?></div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>ÁÒÉßÎÁ</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ccffcc' align='right'>
                        <div class='pt11b'><?= number_format($han_kura_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>¡¡</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>¡¡</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right'>
                        <div class='pt11b'>¡¡</div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>¹ç·×</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right'>
                        <div class='pt11b'><?= number_format($han_yachin_total_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>¡¡</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>¹ç·×</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right'>
                        <div class='pt11b'><?= number_format($han_yachin_total_kin) ?></div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' rowspan='3' valign='top'>
                        <div class='pt10b'>£¸</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' rowspan='3' valign='top'>
                        <div class='pt10b'>ÈÎ´ÉÈñ</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>Ë¡ÄêÊ¡ÍøÈñ</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ccffcc' align='right'>
                        <div class='pt11b'><?= number_format($han_hofukuri_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>¡¡</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' rowspan='3' valign='top'>
                        <div class='pt10b'>ÈÎ´ÉÈñ</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>¸üÀ¸Ê¡ÍøÈñ</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffc6' align='right'>
                        <div class='pt11b'><?= number_format($han_kofukuri_total_kin) ?></div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>¸üÀ¸Ê¡ÍøÈñ</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ccffcc' align='right'>
                        <div class='pt11b'><?= number_format($han_kofukuri_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>¡¡</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>¡¡</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right'>
                        <div class='pt11b'>¡¡</div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>¹ç·×</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right'>
                        <div class='pt11b'><?= number_format($han_kofukuri_total_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>¡¡</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>¹ç·×</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right'>
                        <div class='pt11b'><?= number_format($han_kofukuri_total_kin) ?></div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' rowspan='3' valign='top'>
                        <div class='pt10b'>£¹</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' rowspan='3' valign='top'>
                        <div class='pt10b'>ÈÎ´ÉÈñ</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>Âà¿¦µëÍ¿¶â</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ccffcc' align='right'>
                        <div class='pt11b'><?= number_format($han_taikyuyo_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>¡¡</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' rowspan='3' valign='top'>
                        <div class='pt10b'>ÈÎ´ÉÈñ</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>Âà¿¦µëÉÕÈñÍÑ</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffc6' align='right'>
                        <div class='pt11b'><?= number_format($han_taikyufu_total_kin) ?></div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>Âà¿¦µëÉÕÈñÍÑ</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ccffcc' align='right'>
                        <div class='pt11b'><?= number_format($han_taikyufu_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>¡¡</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>¡¡</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right'>
                        <div class='pt11b'>¡¡</div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>¹ç·×</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right'>
                        <div class='pt11b'><?= number_format($han_taikyufu_total_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>¡¡</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>¹ç·×</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right'>
                        <div class='pt11b'><?= number_format($han_taikyufu_total_kin) ?></div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' rowspan='3' valign='top'>
                        <div class='pt10b'>£±£°</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' rowspan='3' valign='top'>
                        <div class='pt10b'>À½Â¤·ÐÈñ</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>Î¹Èñ¸òÄÌÈñ</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ccffcc' align='right'>
                        <div class='pt11b'><?= number_format($sei_ryohi_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>¡¡</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' rowspan='3' valign='top'>
                        <div class='pt10b'>À½Â¤·ÐÈñ</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>Î¹Èñ¸òÄÌÈñ</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffc6' align='right'>
                        <div class='pt11b'><?= number_format($sei_ryohi_total_kin) ?></div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>³¤³°½ÐÄ¥Èñ</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ccffcc' align='right'>
                        <div class='pt11b'><?= number_format($sei_kaigai_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>¡¡</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>¡¡</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right'>
                        <div class='pt11b'>¡¡</div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>¹ç·×</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right'>
                        <div class='pt11b'><?= number_format($sei_ryohi_total_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>¡¡</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>¹ç·×</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right'>
                        <div class='pt11b'><?= number_format($sei_ryohi_total_kin) ?></div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' rowspan='3' valign='top'>
                        <div class='pt10b'>£±£±</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' rowspan='3' valign='top'>
                        <div class='pt10b'>À½Â¤·ÐÈñ</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>¶ÈÌ³°ÑÂ÷Èñ</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ccffcc' align='right'>
                        <div class='pt11b'><?= number_format($sei_gyomu_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>¡¡</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' rowspan='3' valign='top'>
                        <div class='pt10b'>À½Â¤·ÐÈñ</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>¶ÈÌ³°ÑÂ÷Èñ</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffc6' align='right'>
                        <div class='pt11b'><?= number_format($sei_gyomu_total_kin) ?></div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>»ÙÊ§¼ê¿ôÎÁ</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ccffcc' align='right'>
                        <div class='pt11b'><?= number_format($sei_tesu_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>¡¡</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>¡¡</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right'>
                        <div class='pt11b'>¡¡</div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>¹ç·×</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right'>
                        <div class='pt11b'><?= number_format($sei_gyomu_total_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>¡¡</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>¹ç·×</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right'>
                        <div class='pt11b'><?= number_format($sei_gyomu_total_kin) ?></div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' rowspan='6' valign='top'>
                        <div class='pt10b'>£±£²</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' rowspan='6' valign='top'>
                        <div class='pt10b'>ÈÎ´ÉÈñ</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>»¨Èñ</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ccffcc' align='right'>
                        <div class='pt11b'><?= number_format($sei_zappi_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>¡¡</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' rowspan='6' valign='top'>
                        <div class='pt10b'>ÈÎ´ÉÈñ</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>»¨Èñ</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffc6' align='right'>
                        <div class='pt11b'><?= number_format($sei_zappi_total_kin) ?></div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>¹­¹ðÀëÅÁÈñ</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ccffcc' align='right'>
                        <div class='pt11b'><?= number_format($sei_kokoku_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>¡¡</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>¡¡</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right'>
                        <div class='pt11b'>¡¡</div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>µá¿ÍÈñ</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ccffcc' align='right'>
                        <div class='pt11b'><?= number_format($sei_kyujin_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>¡¡</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>¡¡</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right'>
                        <div class='pt11b'>¡¡</div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>ÊÝ¾Ú½¤ÍýÈñ</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ccffcc' align='right'>
                        <div class='pt11b'><?= number_format($sei_hosyo_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>¡¡</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>¡¡</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right'>
                        <div class='pt11b'>¡¡</div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>½ô²ñÈñ</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ccffcc' align='right'>
                        <div class='pt11b'><?= number_format($sei_kaihi_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>¡¡</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>¡¡</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right'>
                        <div class='pt11b'>¡¡</div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>¹ç·×</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right'>
                        <div class='pt11b'><?= number_format($sei_zappi_total_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>¡¡</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>¹ç·×</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right'>
                        <div class='pt11b'><?= number_format($sei_zappi_total_kin) ?></div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' rowspan='3' valign='top'>
                        <div class='pt10b'>£±£³</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' rowspan='3' valign='top'>
                        <div class='pt10b'>À½Â¤·ÐÈñ</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>ÃÏÂå²ÈÄÂ</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ccffcc' align='right'>
                        <div class='pt11b'><?= number_format($sei_yachin_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>¡¡</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' rowspan='3' valign='top'>
                        <div class='pt10b'>À½Â¤·ÐÈñ</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>ÃÏÂå²ÈÄÂ</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffc6' align='right'>
                        <div class='pt11b'><?= number_format($sei_yachin_total_kin) ?></div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>ÁÒÉßÎÁ</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ccffcc' align='right'>
                        <div class='pt11b'><?= number_format($sei_kura_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>¡¡</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>¡¡</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right'>
                        <div class='pt11b'>¡¡</div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>¹ç·×</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right'>
                        <div class='pt11b'><?= number_format($sei_yachin_total_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>¡¡</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>¹ç·×</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right'>
                        <div class='pt11b'><?= number_format($sei_yachin_total_kin) ?></div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' rowspan='3' valign='top'>
                        <div class='pt10b'>£±£´</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' rowspan='3' valign='top'>
                        <div class='pt10b'>Ï«Ì³Èñ</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>Ë¡ÄêÊ¡ÍøÈñ</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ccffcc' align='right'>
                        <div class='pt11b'><?= number_format($sei_hofukuri_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>¡¡</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' rowspan='3' valign='top'>
                        <div class='pt10b'>Ï«Ì³Èñ</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>¸üÀ¸Ê¡ÍøÈñ</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffc6' align='right'>
                        <div class='pt11b'><?= number_format($sei_kofukuri_total_kin) ?></div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>¸üÀ¸Ê¡ÍøÈñ</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ccffcc' align='right'>
                        <div class='pt11b'><?= number_format($sei_kofukuri_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>¡¡</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>¡¡</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right'>
                        <div class='pt11b'>¡¡</div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>¹ç·×</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right'>
                        <div class='pt11b'><?= number_format($sei_kofukuri_total_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>¡¡</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>¹ç·×</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right'>
                        <div class='pt11b'><?= number_format($sei_kofukuri_total_kin) ?></div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' rowspan='3' valign='top'>
                        <div class='pt10b'>£±£µ</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' rowspan='3' valign='top'>
                        <div class='pt10b'>Ï«Ì³Èñ</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>Âà¿¦µëÍ¿¶â</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ccffcc' align='right'>
                        <div class='pt11b'><?= number_format($sei_taikyuyo_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>¡¡</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' rowspan='3' valign='top'>
                        <div class='pt10b'>Ï«Ì³Èñ</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>Âà¿¦µëÉÕÈñÍÑ</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffc6' align='right'>
                        <div class='pt11b'><?= number_format($sei_taikyufu_total_kin) ?></div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>Âà¿¦µëÉÕÈñÍÑ</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ccffcc' align='right'>
                        <div class='pt11b'><?= number_format($sei_taikyufu_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>¡¡</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>¡¡</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right'>
                        <div class='pt11b'>¡¡</div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>¹ç·×</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right'>
                        <div class='pt11b'><?= number_format($sei_taikyufu_total_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>¡¡</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>¹ç·×</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right'>
                        <div class='pt11b'><?= number_format($sei_taikyufu_total_kin) ?></div>
                    </td>
                </tr>
            </TBODY>
        </table>
            </td></tr>
        </table> <!----------------- ¥À¥ß¡¼End ------------------>
        <form method='post' action='<?php echo $menu->out_self() ?>'>
            <input class='pt10b' type='submit' name='input_data' value='ÅÐÏ¿' onClick='return data_input_click(this)'>
        </form>
    </center>
</body>
</html>
