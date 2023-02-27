<?php
//////////////////////////////////////////////////////////////////////////////
// ·î¼¡Â»±×´Ø·¸ ´ªÄê²ÊÌÜÆâÌõÌÀºÙ½ñ                                          //
// Copyright(C) 2020-2020 Norihisa.Ohya norihisa_ooya@nitto-kohki.co.jp     //
// Changed history                                                          //
// 2020/06/12 Created   account_statement_view.php                          //
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
    $menu->set_title("Âè {$ki} ´ü¡¡ËÜ·è»»¡¡´ª¡¡Äê¡¡²Ê¡¡ÌÜ¡¡Æâ¡¡Ìõ¡¡ÌÀ¡¡ºÙ¡¡½ñ");
} else {
    $menu->set_title("Âè {$ki} ´ü¡¡Âè{$hanki}»ÍÈ¾´ü¡¡´ª¡¡Äê¡¡²Ê¡¡ÌÜ¡¡Æâ¡¡Ìõ¡¡ÌÀ¡¡ºÙ¡¡½ñ");
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

// ÍÂ¶â¾®·×¤Î·×»»
$yokin_total_kin = $touza_kin + $futsu_kin + $teiki_kin;
// ¸½¶âµÚ¤ÓÍÂ¶â¹ç·×¤Î·×»»
$genyo_total_kin = $genkin_kin + $touza_kin + $futsu_kin + $teiki_kin;

// ¸½¶â¤ª¤è¤ÓÍÂ¶â¤ÎÆâÌõ ÌÀºÙ
// ÉáÄÌÍÂ¶â 10¡§Â­Íø¡¢11¡§»°É©UFJ¡¢12¡§»°É©UFJ¿®Â÷
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

// Äê´üÍÂ¶â 10¡§Â­Íø¡¢11¡§»°É©UFJ¡¢12¡§»°É©UFJ¿®Â÷
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

// ¶ä¹ÔËèÍÂ¶â·×
$ashi_total_kin = $ashi_futu_kin + $ashi_teiki_kin;
$ufj_total_kin  = $ufj_futu_kin + $ufj_teiki_kin;
$ufjs_total_kin = $ufjs_futu_kin + $ufjs_teiki_kin;

// Çä³Ý¶â
$res   = array();
$field = array();
$rows  = array();
$urikake_kin = 0;
$note = 'Çä³Ý¶â';
$query = sprintf("SELECT kin FROM profit_loss_bs_history WHERE pl_bs_ym=%d and note='%s'", $yyyymm, $note);
if ($rows=getResultWithField2($query, $field, $res) <= 0) {
    $urikake_kin = 0;
} else {
    $urikake_kin = $res[0][0];
}

// Çä³Ý¶â ÆâÌõ¤Î¼èÆÀ
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

// Çä³Ý¶â ÆâÌõ¤Î¼èÆÀ
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

// Çä³Ý¶â ÆâÌõ¤Î¼èÆÀ
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

// ÃùÂ¢ÉÊ
$res   = array();
$field = array();
$rows  = array();
$note = 'ÃùÂ¢ÉÊ';
$query = sprintf("SELECT rep_kin FROM financial_report_data WHERE rep_ymd=%d and rep_note='%s'", $yyyymm, $note);
if ($rows=getResultWithField2($query, $field, $res) <= 0) {
    $chozo_kin = 0;
} else {
    $chozo_kin = $res[0][0];
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

// É¾²ÁÀÚ²¼¤²È´½Ð¤·
// É¾²ÁÀÚ²¼¤²ÉôÉÊ
$res   = array();
$field = array();
$rows  = array();
$note = 'É¾²ÁÀÚ²¼¤²ÉôÉÊ';
$query = sprintf("SELECT rep_kin FROM financial_report_data WHERE rep_ymd=%d and rep_note='%s'", $yyyymm, $note);
if ($rows=getResultWithField2($query, $field, $res) <= 0) {
    $hyoka_buhin_kin = 0;
} else {
    $hyoka_buhin_kin = $res[0][0];
}
// É¾²ÁÀÚ²¼¤²ºàÎÁ
$res   = array();
$field = array();
$rows  = array();
$note = 'É¾²ÁÀÚ²¼¤²ºàÎÁ';
$query = sprintf("SELECT rep_kin FROM financial_report_data WHERE rep_ymd=%d and rep_note='%s'", $yyyymm, $note);
if ($rows=getResultWithField2($query, $field, $res) <= 0) {
    $hyoka_zai_kin = 0;
} else {
    $hyoka_zai_kin = $res[0][0];
}
// ¸¡ºº»Å³ÝÌÀºÙ
$res   = array();
$field = array();
$rows  = array();
$note = '¸¡ºº»Å³ÝÌÀºÙ';
$query = sprintf("SELECT rep_kin FROM financial_report_data WHERE rep_ymd=%d and rep_note='%s'", $yyyymm, $note);
if ($rows=getResultWithField2($query, $field, $res) <= 0) {
    $tana_ken_kin = 0;
} else {
    $tana_ken_kin = $res[0][0];
}

// ¹©ºî»Å³ÝÌÀºÙ
$res   = array();
$field = array();
$rows  = array();
$note = '¹©ºî»Å³ÝÌÀºÙ';
$query = sprintf("SELECT rep_kin FROM financial_report_data WHERE rep_ymd=%d and rep_note='%s'", $yyyymm, $note);
if ($rows=getResultWithField2($query, $field, $res) <= 0) {
    $tana_kou_kin = 0;
} else {
    $tana_kou_kin = $res[0][0];
}

// ³°Ãí»Å³ÝÌÀºÙ
$res   = array();
$field = array();
$rows  = array();
$note = '³°Ãí»Å³ÝÌÀºÙ';
$query = sprintf("SELECT rep_kin FROM financial_report_data WHERE rep_ymd=%d and rep_note='%s'", $yyyymm, $note);
if ($rows=getResultWithField2($query, $field, $res) <= 0) {
    $tana_gai_kin = 0;
} else {
    $tana_gai_kin = $res[0][0];
}

// Ãª²·»ñ»º¤ÎÆâÌõ·×»»
$sei_buhin_kin  = $buhin_kin - $hyoka_buhin_kin;
$han_total_kin  = $tana_ken_kin + $tana_kou_kin + $tana_gai_kin;
$gen_sizai_kin  = $genzai_kin - $hyoka_zai_kin;
$tana_sizai_kin = $sei_buhin_kin + $tana_ken_kin + $gen_sizai_kin;
$kumi_cc_kin    = $sonotatana_kin + $hyoka_buhin_kin + $hyoka_zai_kin;
$kumi_total_kin = $kumi_cc_kin + $sikakari_total_kin;

// Á°Ê§ÈñÍÑ
$res   = array();
$field = array();
$rows  = array();
$mae_hiyo_kin = 0;
$note = 'Á°Ê§ÈñÍÑ';
$query = sprintf("SELECT kin FROM profit_loss_bs_history WHERE pl_bs_ym=%d and note='%s'", $yyyymm, $note);
if ($rows=getResultWithField2($query, $field, $res) <= 0) {
    $mae_hiyo_kin = 0;
} else {
    $mae_hiyo_kin = $res[0][0];
}

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


//// 2018/10/10 18/09¤«¤é·«±äÀÇ¶â»ñ»º¤Ï¤Þ¤È¤á¤Æ
// ·«±äÀÇ¶â»ñ»º
$res   = array();
$field = array();
$rows  = array();
$ryu_kurizei_shisan_kin = 0;
$note = 'Î®Æ°·«±äÀÇ¶â»ñ»º';
$query = sprintf("SELECT kin FROM profit_loss_bs_history WHERE pl_bs_ym=%d and note='%s'", $yyyymm, $note);
if ($rows=getResultWithField2($query, $field, $res) <= 0) {
    $ryu_kurizei_shisan_kin = 0;
} else {
    $ryu_kurizei_shisan_kin = $res[0][0];
}
// ·«±äÀÇ¶â»ñ»º
$res   = array();
$field = array();
$rows  = array();
$kotei_kuri_zei_kin = 0;
$note = '¸ÇÄê·«±äÀÇ¶â»ñ»º';
$query = sprintf("SELECT kin FROM profit_loss_bs_history WHERE pl_bs_ym=%d and note='%s'", $yyyymm, $note);
if ($rows=getResultWithField2($query, $field, $res) <= 0) {
    $kotei_kuri_zei_kin = 0;
} else {
    $kotei_kuri_zei_kin = $res[0][0];
}
$kotei_kuri_zei_kin = $kotei_kuri_zei_kin + $ryu_kurizei_shisan_kin;

// ·«±äÀÇ¶â»ñ»º Áý¸º
// Î®Æ°»ñ»º
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
// ·«±äÀÇ¶â»ñ»º Áý¸º·×»»
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

// ¸ÇÄê»ñ»º
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

// ·«±äÀÇ¶â»ñ»º Áý¸º·×»»
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

// Ä¹´üÂßÉÕ¶â
$res   = array();
$field = array();
$rows  = array();
$choki_kashi_kin = 0;
$note = 'Ä¹´üÂßÉÕ¶â';
$query = sprintf("SELECT kin FROM profit_loss_bs_history WHERE pl_bs_ym=%d and note='%s'", $yyyymm, $note);
if ($rows=getResultWithField2($query, $field, $res) <= 0) {
    $choki_kashi_kin = 0;
} else {
    $choki_kashi_kin = $res[0][0];
}

// ½¾¶È°÷Ä¹´üÂßÉÕ¶â Áý¸º
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

// Ä¹´üÁ°Ê§ÈñÍÑ
$res   = array();
$field = array();
$rows  = array();
$choki_maebara_kin = 0;
$note = 'Ä¹´üÁ°Ê§ÈñÍÑ';
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

// Ì¤Ê§¾ÃÈñÀÇÅù¤ÎÆâÌõ ·×»»
///// Á°´üËö Ç¯·î¤Î»»½Ð
$end_yyyy = substr($end_ym, 0,4);
$end_mm   = substr($end_ym, 4,2);

if ($end_mm == 3) {                     // 3·î¤Î¾ì¹ç9·î¤È¹ç»»
    // ²¾Ê§¾ÃÈñÀÇ·×»» ºÇ¸å¥Þ¥¤¥Ê¥¹
    // ²¾Ê§¾ÃÈñÀÇÅù
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
    // ²¾Ê§¾ÃÈñÀÇÅù¡ÊÍ¢Æþ¡Ë
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
    // Ì¤Ê§¾ÃÈñÀÇÅù¡ÊÃæ´ÖÇ¼ÉÕÊ¬¡Ë
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
    // ²¾Ê§¾ÃÈñÀÇ ¹ç·×
    $karibara_zei_total = -($karibara_zei_kin+$karibara_yunyu_zei_kin+$mae_sho_zei_kin);
    
    // ²¾¼õ¾ÃÈñÀÇ·×»»
    // ²¾¼õ¾ÃÈñÀÇÅù
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
    
} elseif ($end_mm == 9) {           // 9·î¤Î¾ì¹ç9·î¤Î¤ß
    // ²¾Ê§¾ÃÈñÀÇ·×»» ºÇ¸å¥Þ¥¤¥Ê¥¹
    // ²¾Ê§¾ÃÈñÀÇÅù
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
    // ²¾Ê§¾ÃÈñÀÇÅù¡ÊÍ¢Æþ¡Ë
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
    // Á°Ê§¾ÃÈñÀÇÅù¡ÊÃæ´ÖÇ¼ÉÕÊ¬¡Ë
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
    // ²¾Ê§¾ÃÈñÀÇ ¹ç·×
    $karibara_zei_total = -($karibara_zei_kin+$karibara_yunyu_zei_kin+$mae_sho_zei_kin);
    
    // ²¾¼õ¾ÃÈñÀÇ·×»»
    // ²¾¼õ¾ÃÈñÀÇÅù
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
}  elseif ($end_mm == 12) {           // 12·î¤Î¾ì¹ç 9·îÊ¬¤È10¡Á12·îÊ¬¤Î¹ç·×
    // ²¼´üÇ¯·î
    $ss_str_ym = $end_yyyy . '10';
    // ²¾Ê§¾ÃÈñÀÇ·×»» ºÇ¸å¥Þ¥¤¥Ê¥¹
    // ²¾Ê§¾ÃÈñÀÇÅù 9·îÊ¬
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
    // ²¾Ê§¾ÃÈñÀÇÅù 10¡Á12·îÊ¬
    $res   = array();
    $field = array();
    $rows  = array();
    $query = sprintf("select SUM(rep_de), SUM(rep_cr) from financial_report_month where rep_ymd>=%d and rep_ymd<=%d and rep_summary1='%s' and rep_summary2='%s'", $ss_str_ym, $end_ym, $sum1, $sum2);
    if ($rows=getResultWithField2($query, $field, $res) <= 0) {
        $karibara_zei_kin = $karibara_zei_kin;
    } else {
        $karibara_zei_kin = $karibara_zei_kin + ($res[0][0] - $res[0][1]);
    }
    // ²¾Ê§¾ÃÈñÀÇÅù¡ÊÍ¢Æþ¡Ë
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
    // ²¾Ê§¾ÃÈñÀÇÅù¡ÊÍ¢Æþ¡Ë 10¡Á12·îÊ¬
    $res   = array();
    $field = array();
    $rows  = array();
    $query = sprintf("select SUM(rep_de), SUM(rep_cr) from financial_report_month where rep_ymd>=%d and rep_ymd<=%d and rep_summary1='%s' and rep_summary2='%s'", $ss_str_ym, $end_ym, $sum1, $sum2);
    if ($rows=getResultWithField2($query, $field, $res) <= 0) {
        $karibara_yunyu_zei_kin = $karibara_yunyu_zei_kin;
    } else {
        $karibara_yunyu_zei_kin = $karibara_yunyu_zei_kin + ($res[0][0] - $res[0][1]);
    }
    // Á°Ê§¾ÃÈñÀÇÅù¡ÊÃæ´ÖÇ¼ÉÕÊ¬¡Ë
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
    // Á°Ê§¾ÃÈñÀÇÅù¡ÊÃæ´ÖÇ¼ÉÕÊ¬¡Ë 10¡Á12·îÊ¬
    $res   = array();
    $field = array();
    $rows  = array();
    $query = sprintf("select SUM(rep_de), SUM(rep_cr) from financial_report_month where rep_ymd>=%d and rep_ymd<=%d and rep_summary1='%s' and rep_summary2='%s'", $ss_str_ym, $end_ym, $sum1, $sum2);
    if ($rows=getResultWithField2($query, $field, $res) <= 0) {
        $mae_sho_zei_kin = $mae_sho_zei_kin;
    } else {
        $mae_sho_zei_kin = $mae_sho_zei_kin + ($res[0][0] - $res[0][1]);
    }
    // ²¾Ê§¾ÃÈñÀÇ ¹ç·×
    $karibara_zei_total = -($karibara_zei_kin+$karibara_yunyu_zei_kin+$mae_sho_zei_kin);
    
    // ²¾¼õ¾ÃÈñÀÇ·×»»
    // ²¾¼õ¾ÃÈñÀÇÅù
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
    // ²¾¼õ¾ÃÈñÀÇÅù 10¡Á12·îÊ¬
    $res   = array();
    $field = array();
    $rows  = array();
    $query = sprintf("select SUM(rep_de), SUM(rep_cr) from financial_report_month where rep_ymd>=%d and rep_ymd<=%d and rep_summary1='%s' and rep_summary2='%s'", $ss_str_ym, $end_ym, $sum1, $sum2);
    if ($rows=getResultWithField2($query, $field, $res) <= 0) {
        $kariuke_sho_zei_kin = $kariuke_sho_zei_kin;
    } else {
        $kariuke_sho_zei_kin = $kariuke_sho_zei_kin + ($res[0][1] - $res[0][0]);
    }
} else {                            // 6¤Ï·×»»ÊýË¡¤¬°ã¤¦
    $karibara_zei_kin       = $kari00_kin;
    //$karibara_yunyu_zei_kin = $kari20_kin;
    $mae_sho_zei_kin        = $mae_zei_kin;
    
    $karibara_zei_total     = -($karibara_zei_kin+$mae_sho_zei_kin);
    
    $kariuke_sho_zei_kin    = $kariuke_zei_kin;
}


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

// Çã³Ý¶â¤ÎÆâÌõ TOP10¤Î¼èÆÀ
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
        $kaikake_top[$i][2]  = $res_k[0][1];       // ¶â³Û
    
        $res_m   = array();
        $field_m = array();
        $rows_m  = array();
        $query_m = sprintf("select name, address1, address2 from vendor_master WHERE vendor='%s'", $kaikake_top_code);
        if ($rows_m=getResultWithField2($query_m, $field_m, $res_m) <= 0) {
            $kaikake_top[$i][0]    = '';    // È¯ÃíÀèÌ¾
            $kaikake_top[$i][1]    = '';    // ½»½ê
        } else {
            $kaikake_top[$i][0]    = $res_m[0][0];
            if ($kaikake_top_code=='01298') {
                $res_m[0][1] = 'ÆÊÌÚ¸©±§ÅÔµÜ»Ô';
            } elseif ($kaikake_top_code=='01299') {
                $res_m[0][1] = '°ñ¾ë¸©ÆüÎ©»Ô';
            } elseif ($kaikake_top_code=='00958') {
                $res_m[0][1] = 'ÅìµþÅÔÊ¸µþ¶è';
            } elseif ($kaikake_top_code=='00642') {
                $res_m[0][1] = 'ÀéÍÕ¸©Á¥¶¶»Ô';
            }
            $kaikake_top[$i][1]    = preg_replace("/( |¡¡)/", "", $res_m[0][1] . $res_m[0][2]);
        }
    }
    $kaikake_top_kin = $kaikake_top_kin + $kaikake_top[$i][2];
}

// Çã³Ý¶â¤ÎÆâÌõ ¤½¤ÎÂ¾·×»»
$kaikake_top_sonota_kin = $kaikake_total_kin - $kaikake_top_kin;

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

// Ì¤Ê§¶â¤ÎÆâÌõ TOP10¤Î¼èÆÀ
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
    
        if ($miharai_top[$i][0]=='³ô¼°²ñ¼Ò¿­ÏÂ¾¦»ö') {
            $miharai_top[$i][1] = 'ÆÊÌÚ¸©¼¯¾Â»Ô³­ÅçÄ®£´£µ£¶';
        } elseif ($miharai_top[$i][0]=='Ã¤Ì¦»º¶È¡Ê³ô¡ËËÌ´ØÅì±Ä¶È½ê') {
            $miharai_top[$i][1] = '·²ÇÏ¸©´ÛÎÓ»Ô¿ÛË¬Ä®£±£³£·£¹';
        } elseif ($miharai_top[$i][0]=='¿¸Ë­·úÀß¡Ê³ô¡Ë') {
            $miharai_top[$i][1] = 'ÆÊÌÚ¸©±§ÅÔµÜ»Ô²°ÈÄÄ®£µ£·£¸ÈÖÃÏ£³£·£¸';
        } elseif ($miharai_top[$i][0]=='¡Ê³ô¡Ë¥¦¥¨¥Î±§ÅÔµÜ»ÙÅ¹') {
            $miharai_top[$i][1] = 'ÆÊÌÚ¸©±§ÅÔµÜ»ÔÄáÅÄÄ®£±£³£³£·¡Ý£³';
        } elseif ($miharai_top[$i][0]=='³ô¼°²ñ¼Ò»³Á±£Ó£Æ£Ó±Ä¶ÈËÜÉô') {
            $miharai_top[$i][1] = 'ÅìµþÅÔ¹Á¶è¹ÁÆî2-16-2ÂÀÍÛÀ¸Ì¿ÉÊÀî¥Ó¥ë12³¬';
        } elseif ($miharai_top[$i][0]=='³ô¼°²ñ¼Ò¥ß¥¹¥ß') {
            $miharai_top[$i][1] = 'ÅìµþÅÔÊ¸µþ¶è¸å³Ú2-5-1ÈÓÅÄ¶¶¥Õ¥¡¡¼¥¹¥È¥Ó¥ë';
        } elseif ($miharai_top[$i][0]=='»°¿®ÅÅ¹©³ô¼°²ñ¼Ò') {
            $miharai_top[$i][1] = 'ÆÊÌÚ¸©±§ÅÔµÜ»ÔÀîËóÄ®£±£°£µ£¶';
        } elseif ($miharai_top[$i][0]=='³ô¼°²ñ¼Ò¥­¡Ý¥¨¥ó¥¹') {
            $miharai_top[$i][1] = 'ÂçºåÉÜÂçºå»ÔÅìÍäÀî¶èÅìÃæÅç£±ÃúÌÜ£³ÈÖ£±£´¹æ';
        } elseif ($miharai_top[$i][0]=='¶õ°µ¹©¶È¡Ê³ô¡Ë') {
            $miharai_top[$i][1] = '¿ÀÆàÀî¸©²£ÉÍ»ÔÄá¸«¶è¶ð²¬£²¡Ý£¶¡Ý£²£·';
        } elseif ($miharai_top[$i][0]=='³ô¼°²ñ¼Ò¿å¸ÍÀßÈ÷¹©¶È') {
            $miharai_top[$i][1] = 'ÆÊÌÚ¸©±§ÅÔµÜ»Ô¸æ¹¬¥ö¸¶Ä®£±£´£³¡Ý£´£·';
        } elseif ($miharai_top[$i][0]=='¡ÊÍ­¡Ë¾®ÎÓÀÐÌý¾¦²ñ') {
            $miharai_top[$i][1] = 'ÆÊÌÚ¸©¤µ¤¯¤é»Ô»á²È£±£¸£¸£´';
        } elseif ($miharai_top[$i][0]=='»³ÅÄ¥Þ¥·¥ó¥Ä¡¼¥ë³ô¼°²ñ¼Ò') {
            $miharai_top[$i][1] = 'ÅìµþÅÔÂæÅì¶èÂæÅì£±ÃúÌÜ£²£³¡Ý£¶';
        } elseif ($miharai_top[$i][0]=='¡Ê³ô¡Ë¥Ê¥·¥ç¥Ê¥ë¥Þ¥·¥Ê¥ê¡¼¥¢¥¸¥¢') {
            $miharai_top[$i][1] = '°¦ÃÎ¸©½ÕÆü°æ»ÔËÙ¥ÎÆâÄ®£²¡Ý£±£±¡Ý£±£¶';
        } elseif ($miharai_top[$i][0]=='³ô¼°²ñ¼Ò¶¨Î©ÅÅµ¤') {
            $miharai_top[$i][1] = 'ÆÊÌÚ¸©¤µ¤¯¤é»Ô»á²È£²£µ£µ£¸';
        } elseif ($miharai_top[$i][0]=='¡ÊÍ­¡Ë²£Àî»æ¹©¶È') {
            $miharai_top[$i][1] = 'ÆÊÌÚ¸©±§ÅÔµÜ»ÔÃæÅçÄ®£´£³£µ¡Ý£±';
        } elseif ($miharai_top[$i][0]=='³ô¼°²ñ¼ÒÅì¹Ý') {
            $miharai_top[$i][1] = 'ÅìµþÅÔÊ¸µþ¶èËÜ¶¿£µÃúÌÜ£²£·ÈÖ£±£°¹æ';
        } elseif ($miharai_top[$i][0]=='Í­¸Â²ñ¼Òºù°æÇÀ±à') {
            $miharai_top[$i][0] = '³ô¼°²ñ¼Ò¥ß¥¹¥ß';
            $miharai_top[$i][1] = 'ÅìµþÅÔÊ¸µþ¶è¸å³Ú2-5-1ÈÓÅÄ¶¶¥Õ¥¡¡¼¥¹¥È¥Ó¥ë';
        }
    }
    $miharai_top_kin = $miharai_top_kin + $miharai_top[$i][2];
}

// Ì¤Ê§¶â¤ÎÆâÌõ ¤½¤ÎÂ¾·×»»
$miharai_top_sonota_kin = $miharai_total_kin - $miharai_top_kin;

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

// ÍÂ¤ê¶â¤Î·×»»
// ¸»Àô½êÆÀÀÇ
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

// ¸»Àô½»Ì±ÀÇ
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

// ·ò¹¯ÊÝ¸±ÎÁ
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

// ¸üÀ¸Ç¯¶âÊÝ¸±ÎÁ
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

// ÍÂ¤ê¶â ¤½¤ÎÂ¾
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

// Ë¡¿ÍÀÇ¡¦½»Ì±ÀÇµÚ¤Ó»ö¶ÈÀÇ¤ÎÆâÌõ ¹ç·×¤Î·×»»
$hojin_uchi_total_kin = $hojin_jyumin_zei_kin + $jigyo_zei_kin;

// ecaÍÑË¡¿ÍÀÇ¡¢½»Ì±ÀÇµÚ¤Ó»ö¶ÈÀÇ
$eca_hojin_zeito_total_kin = $hojin_jyumin_zei_kin + $jigyo_zei_kin;

// Ë¡¿ÍÀÇÅù¡Ê¹ñÀÇ¡ËÍÂ¶âÍøÂ©Åù¤ËÂÐ¤¹¤ë¸»Àô½êÆÀÀÇ³Û
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

// Åö´üË¡¿ÍÀÇ½»Ì±ÀÇ»ö¶ÈÀÇ°úÅö³Û
$toki_hojin_jigyo = $hojin_uchi_total_kin - $gensen_shotoku_kin;

// Ì¤Ê§Ë¡¿ÍÀÇÅù ´ü¼ó
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

// Ì¤Ê§Ë¡¿ÍÀÇÅù
$res   = array();
$field = array();
$rows  = array();
$miharai_hozei_kin = 0;
$note = 'Ì¤Ê§Ë¡¿ÍÀÇÅù';
$query = sprintf("SELECT kin FROM profit_loss_bs_history WHERE pl_bs_ym=%d and note='%s'", $yyyymm, $note);
if ($rows=getResultWithField2($query, $field, $res) <= 0) {
    $miharai_hozei_kin = 0;
} else {
    $miharai_hozei_kin = $res[0][0];
}

$miharai_hozei_settei = $toki_hojin_jigyo;
$miharai_hozei_shiha  = $miharai_hozei_kishu + $miharai_hozei_settei - $miharai_hozei_kin;

// Ì¤Ê§ÈñÍÑ
$res   = array();
$field = array();
$rows  = array();
$miharai_hiyo_kin = 0;
$note = 'Ì¤Ê§ÈñÍÑ';
$query = sprintf("SELECT kin FROM profit_loss_bs_history WHERE pl_bs_ym=%d and note='%s'", $yyyymm, $note);
if ($rows=getResultWithField2($query, $field, $res) <= 0) {
    $miharai_hiyo_kin = 0;
} else {
    $miharai_hiyo_kin = $res[0][0];
}

// ÍÂ¤ê¶â
$res   = array();
$field = array();
$rows  = array();
$azukari_kin = 0;
$note = 'ÍÂ¤ê¶â';
$query = sprintf("SELECT kin FROM profit_loss_bs_history WHERE pl_bs_ym=%d and note='%s'", $yyyymm, $note);
if ($rows=getResultWithField2($query, $field, $res) <= 0) {
    $azukari_kin = 0;
} else {
    $azukari_kin = $res[0][0];
}

// ¾ÞÍ¿°úÅö¶â
$res   = array();
$field = array();
$rows  = array();
$syoyo_hikiate_kin = 0;
$note = '¾ÞÍ¿°úÅö¶â';
$query = sprintf("SELECT kin FROM profit_loss_bs_history WHERE pl_bs_ym=%d and note='%s'", $yyyymm, $note);
if ($rows=getResultWithField2($query, $field, $res) <= 0) {
    $syoyo_hikiate_kin = 0;
} else {
    $syoyo_hikiate_kin = $res[0][0];
}

// Âà¿¦µëÉÕ°úÅö¶â
$res   = array();
$field = array();
$rows  = array();
$taisyoku_hikiate_kin = 0;
$note = 'Âà¿¦µëÉÕ°úÅö¶â';
$query = sprintf("SELECT kin FROM profit_loss_bs_history WHERE pl_bs_ym=%d and note='%s'", $yyyymm, $note);
if ($rows=getResultWithField2($query, $field, $res) <= 0) {
    $taisyoku_hikiate_kin = 0;
} else {
    $taisyoku_hikiate_kin = $res[0][0];
}

// Âà¿¦µëÉÕ°úÅö¶â Áý¸º
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

// Âà¿¦µëÉÕ°úÅö¶â ÌÜÅª»ÈÍÑ
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

// Âà¿¦µëÉÕ°úÅö¶â ¤½¤ÎÂ¾
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

// »ñËÜ¶â
$res   = array();
$field = array();
$rows  = array();
$shihon_kin = 0;
$note = '»ñËÜ¶â';
$query = sprintf("SELECT kin FROM profit_loss_bs_history WHERE pl_bs_ym=%d and note='%s'", $yyyymm, $note);
if ($rows=getResultWithField2($query, $field, $res) <= 0) {
    $shihon_kin = 0;
} else {
    $shihon_kin = $res[0][0];
}

// »ñËÜ¶â·×
$shihon_total_kin = $shihon_kin;


// »ñËÜ¶â Áý¸º
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

// »ñËÜ½àÈ÷¶â
$res   = array();
$field = array();
$rows  = array();
$shihon_jyunbi_kin = 0;
$note = '»ñËÜ½àÈ÷¶â';
$query = sprintf("SELECT kin FROM profit_loss_bs_history WHERE pl_bs_ym=%d and note='%s'", $yyyymm, $note);
if ($rows=getResultWithField2($query, $field, $res) <= 0) {
    $shihon_jyunbi_kin = 0;
} else {
    $shihon_jyunbi_kin = $res[0][0];
}


// »ñËÜ½àÈ÷¶â Áý¸º
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

// ¤½¤ÎÂ¾»ñËÜ¾êÍ¾¶â
$res   = array();
$field = array();
$rows  = array();
$sonota_shihon_jyoyo_kin = 0;
$note = '¤½¤ÎÂ¾»ñËÜ¾êÍ¾¶â';
$query = sprintf("SELECT kin FROM profit_loss_bs_history WHERE pl_bs_ym=%d and note='%s'", $yyyymm, $note);
if ($rows=getResultWithField2($query, $field, $res) <= 0) {
    $sonota_shihon_jyoyo_kin = 0;
} else {
    $sonota_shihon_jyoyo_kin = $res[0][0];
}

// ¤½¤ÎÂ¾»ñËÜ¾êÍ¾¶â Áý¸º
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

// ¤½¤ÎÂ¾Íø±×¾êÍ¾¶â
$res   = array();
$field = array();
$rows  = array();
$tai_sonota_rieki_jyoyo_kin = 0;
$note = '¤½¤ÎÂ¾Íø±×¾êÍ¾¶â';
$query = sprintf("SELECT kin FROM profit_loss_bs_history WHERE pl_bs_ym=%d and note='%s'", $yyyymm, $note);
if ($rows=getResultWithField2($query, $field, $res) <= 0) {
    $tai_sonota_rieki_jyoyo_kin = 0;
} else {
    $tai_sonota_rieki_jyoyo_kin = $res[0][0];
}

// ¤½¤ÎÂ¾Íø±×¾êÍ¾¶â Áý¸º
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

// »ñËÜ¶âµÚ¤Ó¾êÍ¾¶â¤ÎÆâÌõ ·×
$shihon_jyoyo_total = $shihon_total_kin + $shihon_jyunbi_kin + $sonota_shihon_jyoyo_kin + $tai_sonota_rieki_jyoyo_kin;
$shihon_jyoyo_kishu = $shihon_kin_kishu + $shihon_jyunbi_kishu + $sonota_shihon_jyoyo_kishu + $sonota_rieki_jyoyo_kishu;
$shihon_jyoyo_zou   = $shihon_kin_zou + $shihon_jyunbi_zou + $sonota_shihon_jyoyo_zou + $sonota_rieki_jyoyo_zou;
$shihon_jyoyo_gen   = $shihon_kin_gen + $shihon_jyunbi_gen + $sonota_shihon_jyoyo_gen + $sonota_rieki_jyoyo_gen;

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

// ½ôÀÇ¸ø²Ý¹ç·×
// »ö¶ÈÅù
$res   = array();
$field = array();
$rows  = array();
$han_jigyo_kin = 0;
$note  = '¹ç·×»ö¶ÈÅù';
$query = sprintf("select SUM(kin) from profit_loss_keihi_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='%s'", $str_ym, $end_ym, $note);
if ($rows=getResultWithField2($query, $field, $res) <= 0) {
    $all_jigyo_kin = 0;
} else {
    $all_jigyo_kin = $res[0][0];
}
// ½ôÀÇ¸ø²Ý
$res   = array();
$field = array();
$rows  = array();
$han_zeikoka_kin = 0;
$note  = '¹ç·×½ôÀÇ¸ø²Ý';
$query = sprintf("select SUM(kin) from profit_loss_keihi_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='%s'", $str_ym, $end_ym, $note);
if ($rows=getResultWithField2($query, $field, $res) <= 0) {
    $all_zeikoka_kin = 0;
} else {
    $all_zeikoka_kin = $res[0][0];
}

// ÈÎ´ÉÈñ½ôÀÇ¸ø²Ý¹ç·×¤Î·×»»
$all_zeikoka_total_kin = $all_jigyo_kin + $all_zeikoka_kin;

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

$ym4s = substr($str_ym, 2, 4);
$ym4e = substr($end_ym, 2, 4);

// ½ôÀÇ¸ø²Ý ¸ÇÄê»ñ»ºÀÇ
// À½Â¤·ÐÈñ
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
// ÈÎ´ÉÈñ
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
// ¹ç·×
$kotei_zei_total_kin = $kotei_zei_sei_kin + $kotei_zei_han_kin;

// ½ôÀÇ¸ø²Ý °õ»æÀÇ
// À½Â¤·ÐÈñ
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
// ÈÎ´ÉÈñ
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
// ¹ç·×
$inshi_zei_total_kin = $inshi_zei_sei_kin + $inshi_zei_han_kin;

// ½ôÀÇ¸ø²Ý ÅÐÏ¿ÌÈµöÀÇ¡Ê¤½¤ÎÂ¾¡Ë
// À½Â¤·ÐÈñ
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
// ÈÎ´ÉÈñ
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
// ¹ç·×
$touroku_zei_total_kin = $touroku_zei_sei_kin + $touroku_zei_han_kin;

// ½ôÀÇ¸ø²Ý ¹ç·×
$shozei_sei_total = $kotei_zei_sei_kin + $inshi_zei_sei_kin + $touroku_zei_sei_kin;
$shozei_han_total = $kotei_zei_han_kin + $inshi_zei_han_kin + $touroku_zei_han_kin;
$shozei_total     = $kotei_zei_total_kin + $inshi_zei_total_kin + $touroku_zei_total_kin;

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
            //  bgcolor='#ceffce' ²«ÎÐ
            //  bgcolor='#ffffc6' Çö¤¤²«¿§
            //  bgcolor='#d6d3ce' Win ¥°¥ì¥¤
        ?>
    <!--------------- ¤³¤³¤«¤éËÜÊ¸¤ÎÉ½¤òÉ½¼¨¤¹¤ë -------------------->
        <table bgcolor='#ffffff' align='left' cellspacing='0' cellpadding='10'>
            <tr><td> <!----------- ¥À¥ß¡¼(¥Ç¥¶¥¤¥óÍÑ) ------------>
        <div class='pt11b'>1.¸½¶â¤ª¤è¤ÓÍÂ¶â¤ÎÆâÌõ</div>
        <table class='winbox_field' width='100%' align='center' bgcolor='black' cellspacing='1' cellpadding='5'>
            <THEAD>
                <!-- ¥Æ¡¼¥Ö¥ë ¥Ø¥Ã¥À¡¼¤ÎÉ½¼¨ -->
                <tr>
                    <td class='winboxt' nowrap bgcolor='#ffffff' rowspan='2' align='center'><div class='pt11b'>¸½¶â</div></td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center'><div class='pt11b'>²ÊÌÜ</div></td>
                    <td class='winbox' nowrap bgcolor='#ffffff' colspan='4'><div class='pt11b' align='center'>ÆâÍÆ</div></td>
                    <td class='winbox' nowrap bgcolor='#ffffff'><div class='pt11b' align='center'>¶â³Û</div></td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff'><div class='pt11b' align='center'>¸½¶â</div></td>
                    <td class='winbox' nowrap bgcolor='#ffffff' colspan='4'><div class='pt11b' align='center'>¼êµö»Ä¹â</div></td>
                    <td class='winbox' nowrap bgcolor='#E0FFFF' align='right'>
                        <div class='pt11b'><?= number_format($genkin_kin) ?></div>
                    </td>
                </tr>
            </THEAD>
            <TFOOT>
                <!-- ¸½ºß¤Ï¥Õ¥Ã¥¿¡¼¤Ï²¿¤â¤Ê¤¤ -->
            </TFOOT>
            <TBODY>
                <tr>
                    <td class='winboxt' nowrap bgcolor='#ffffff' rowspan='5' align='center'><div class='pt11b'>ÍÂ¶âÆâÌõ</div></td>
                    <td class='winbox' nowrap bgcolor='#ffffff'><div class='pt11b' align='center'>¶ä¹ÔÌ¾</div></td>
                    <td class='winbox' nowrap bgcolor='#ffffff'><div class='pt11b' align='center'>»ÙÅ¹Ì¾</div></td>
                    <td class='winbox' nowrap bgcolor='#ffffff'><div class='pt11b' align='center'>ÉáÄÌÍÂ¶â</div></td>
                    <td class='winbox' nowrap bgcolor='#ffffff'><div class='pt11b' align='center'>ÅöºÂÍÂ¶â</div></td>
                    <td class='winbox' nowrap bgcolor='#ffffff'><div class='pt11b' align='center'>Äê´üÍÂ¶â</div></td>
                    <td class='winbox' nowrap bgcolor='#ffffff'><div class='pt11b' align='center'>·×</div></td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff'><div class='pt11b' align='center'>»°É©UFJ</div></td>
                    <td class='winbox' nowrap bgcolor='#ffffff'><div class='pt11b' align='center'>ÃÓ¾å</div></td>
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
                    <td class='winbox' nowrap bgcolor='#ffffff'><div class='pt11b' align='center'>»°É©UFJ¿®Â÷</div></td>
                    <td class='winbox' nowrap bgcolor='#ffffff'><div class='pt11b' align='center'>ËÜÅ¹</div></td>
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
                    <td class='winbox' nowrap bgcolor='#ffffff'><div class='pt11b' align='center'>Â­Íø</div></td>
                    <td class='winbox' nowrap bgcolor='#ffffff'><div class='pt11b' align='center'>»á²È</div></td>
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
                    <td class='winbox' nowrap bgcolor='#ffffff' colspan='2'><div class='pt11b' align='center'>¾®·×</div></td>
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
                    <td class='winbox' nowrap bgcolor='#ffffff' colspan='6' align='right'><div class='pt11b'>¸½¶âÍÂ¶â¹ç·×</div></td>
                    <td class='winbox' nowrap bgcolor='#E0FFFF' align='right'>
                        <div class='pt11b'><?= number_format($genyo_total_kin) ?></div>
                    </td>
                </tr>
            </TBODY>
        </table>
            </td></tr>
        </table> <!----------------- ¥À¥ß¡¼End ------------------>
        
        <BR clear='all'>
        
        <table bgcolor='#ffffff' align='left' cellspacing='0' cellpadding='10'>
            <tr><td> <!----------- ¥À¥ß¡¼(¥Ç¥¶¥¤¥óÍÑ) ------------>
        <div class='pt11b'>2.Çä³Ý¶â¤ÎÆâÌõ</div>
        <table class='winbox_field' width='100%' align='center' bgcolor='black' cellspacing='1' cellpadding='5'>
            <THEAD>
                <!-- ¥Æ¡¼¥Ö¥ë ¥Ø¥Ã¥À¡¼¤ÎÉ½¼¨ -->
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center'><div class='pt11b'>¼ÒÌ¾</div></td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center'><div class='pt11b'>½»½ê</div></td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center'><div class='pt11b'>¶â³Û</div></td>
                </tr>
            </THEAD>
            <TFOOT>
                <!-- ¸½ºß¤Ï¥Õ¥Ã¥¿¡¼¤Ï²¿¤â¤Ê¤¤ -->
            </TFOOT>
            <TBODY>
                <?php if ($nk_uri_kin<>0) { ?>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='left'>
                        <div class='pt11b'>ÆüÅì¹©´ï³ô¼°²ñ¼Ò</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='left'>
                        <div class='pt11b'>ÅìµþÅÔÂçÅÄ¶èÃçÃÓ¾å2ÃúÌÜ9ÈÖ4¹æ</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#E0FFFF' align='right'>
                        <div class='pt11b'><?= number_format($nk_uri_kin) ?></div>
                    </td>
                </tr>
                <?php } ?>
                <?php if ($mt_uri_kin<>0) { ?>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='left'>
                        <div class='pt11b'>³ô¼°²ñ¼Ò ¥á¥É¥Æ¥Ã¥¯</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='left'>
                        <div class='pt11b'>»³·Á¸©»³·Á»Ô¼ãµÜ1-1-36</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#E0FFFF' align='right'>
                        <div class='pt11b'><?= number_format($mt_uri_kin) ?></div>
                    </td>
                </tr>
                <?php } ?>
                <?php if ($snk_uri_kin<>0) { ?>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='left'>
                        <div class='pt11b'>Çò²ÏÆüÅì¹©´ï³ô¼°²ñ¼Ò</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='left'>
                        <div class='pt11b'>Ê¡Åç¸©Çò²Ï»ÔÁÐÀÐ²£Êö12ÈÖ</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#E0FFFF' align='right'>
                        <div class='pt11b'><?= number_format($snk_uri_kin) ?></div>
                    </td>
                </tr>
                <?php } ?>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' colspan='2' align='right'><div class='pt11b'>¹ç·×</div></td>
                    <td class='winbox' nowrap bgcolor='#E0FFFF' align='right'>
                        <div class='pt11b'><?= number_format($urikake_kin) ?></div>
                    </td>
                </tr>
            </TBODY>
        </table>
            </td></tr>
        </table> <!----------------- ¥À¥ß¡¼End ------------------>
        
        <BR clear='all'>
        
        <table bgcolor='#ffffff' align='left' cellspacing='0' cellpadding='10'>
            <tr><td> <!----------- ¥À¥ß¡¼(¥Ç¥¶¥¤¥óÍÑ) ------------>
        <div class='pt11b'>3.Ãª²·»ñ»º¤ÎÆâÌõ</div>
        <table class='winbox_field' width='100%' align='center' bgcolor='black' cellspacing='1' cellpadding='5'>
            <THEAD>
                <!-- ¥Æ¡¼¥Ö¥ë ¥Ø¥Ã¥À¡¼¤ÎÉ½¼¨ -->
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' rowspan='2'><div class='pt11b'>ÆâÌõ</div></td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' colspan='6'><div class='pt11b'>¸¶ºàÎÁµÚ¤ÓÃùÂ¢ÉÊ</div></td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' rowspan='2'><div class='pt11b'>»Å³ÝÉÊ</div></td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' rowspan='2'><div class='pt11b'>¹ç·×</div></td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center'><div class='pt11b'>À¸»ºÍÑÉôÉÊ</div></td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center'><div class='pt11b'>È¾À®ÉôÉÊ</div></td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center'><div class='pt11b'>¸¶ºàÎÁ</div></td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center'><div class='pt11b'>£Ã£ÃÉôÉÊ</div></td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center'><div class='pt11b'>ÃùÂ¢ÉÊ</div></td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center'><div class='pt11b'>¾®·×</div></td>
                </tr>
            </THEAD>
            <TFOOT>
                <!-- ¸½ºß¤Ï¥Õ¥Ã¥¿¡¼¤Ï²¿¤â¤Ê¤¤ -->
            </TFOOT>
            <TBODY>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center'><div class='pt11b'>»ñºà¡¦¸¡ºº</div></td>
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
                        <div class='pt11b'>¡Ý</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#E0FFFF' align='right'>
                        <div class='pt11b'>¡Ý</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#E0FFFF' align='right'>
                        <div class='pt11b'><?= number_format($tana_sizai_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#E0FFFF' align='right'>
                        <div class='pt11b'>¡Ý</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#E0FFFF' align='right'>
                        <div class='pt11b'><?= number_format($tana_sizai_kin) ?></div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center'><div class='pt11b'>¹©ºî</div></td>
                    <td class='winbox' nowrap bgcolor='#E0FFFF' align='right'>
                        <div class='pt11b'>¡Ý</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#E0FFFF' align='right'>
                        <div class='pt11b'><?= number_format($tana_kou_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#E0FFFF' align='right'>
                        <div class='pt11b'>¡Ý</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#E0FFFF' align='right'>
                        <div class='pt11b'>¡Ý</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#E0FFFF' align='right'>
                        <div class='pt11b'>¡Ý</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#E0FFFF' align='right'>
                        <div class='pt11b'><?= number_format($tana_kou_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#E0FFFF' align='right'>
                        <div class='pt11b'>¡Ý</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#E0FFFF' align='right'>
                        <div class='pt11b'><?= number_format($tana_kou_kin) ?></div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center'><div class='pt11b'>³°Ãí</div></td>
                    <td class='winbox' nowrap bgcolor='#E0FFFF' align='right'>
                        <div class='pt11b'>¡Ý</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#E0FFFF' align='right'>
                        <div class='pt11b'><?= number_format($tana_gai_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#E0FFFF' align='right'>
                        <div class='pt11b'>¡Ý</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#E0FFFF' align='right'>
                        <div class='pt11b'>¡Ý</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#E0FFFF' align='right'>
                        <div class='pt11b'>¡Ý</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#E0FFFF' align='right'>
                        <div class='pt11b'><?= number_format($tana_gai_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#E0FFFF' align='right'>
                        <div class='pt11b'>¡Ý</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#E0FFFF' align='right'>
                        <div class='pt11b'><?= number_format($tana_gai_kin) ?></div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center'><div class='pt11b'>ÁÈÎ©</div></td>
                    <td class='winbox' nowrap bgcolor='#E0FFFF' align='right'>
                        <div class='pt11b'>¡Ý</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#E0FFFF' align='right'>
                        <div class='pt11b'>¡Ý</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#E0FFFF' align='right'>
                        <div class='pt11b'>¡Ý</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#E0FFFF' align='right'>
                        <div class='pt11b'><?= number_format($kumi_cc_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#E0FFFF' align='right'>
                        <div class='pt11b'>¡Ý</div>
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
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center'><div class='pt11b'>¤½¤ÎÂ¾</div></td>
                    <td class='winbox' nowrap bgcolor='#E0FFFF' align='right'>
                        <div class='pt11b'>¡Ý</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#E0FFFF' align='right'>
                        <div class='pt11b'>¡Ý</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#E0FFFF' align='right'>
                        <div class='pt11b'>¡Ý</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#E0FFFF' align='right'>
                        <div class='pt11b'>¡Ý</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#E0FFFF' align='right'>
                        <div class='pt11b'><?= number_format($chozo_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#E0FFFF' align='right'>
                        <div class='pt11b'><?= number_format($chozo_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#E0FFFF' align='right'>
                        <div class='pt11b'>¡Ý</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#E0FFFF' align='right'>
                        <div class='pt11b'><?= number_format($chozo_kin) ?></div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center'><div class='pt11b'>¹ç·×</div></td>
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
        </table> <!----------------- ¥À¥ß¡¼End ------------------>
        
        <BR clear='all'>
        
        <table bgcolor='#ffffff' align='left' cellspacing='0' cellpadding='10'>
            <tr><td> <!----------- ¥À¥ß¡¼(¥Ç¥¶¥¤¥óÍÑ) ------------>
        <div class='pt11b'>4.Á°Ê§ÈñÍÑ¤ÎÆâÌõ</div>
        <table class='winbox_field' width='100%' align='center' bgcolor='black' cellspacing='1' cellpadding='5'>
            <THEAD>
                <!-- ¥Æ¡¼¥Ö¥ë ¥Ø¥Ã¥À¡¼¤ÎÉ½¼¨ -->
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center'><div class='pt11b'>¼ÒÌ¾</div></td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center'><div class='pt11b'>ÆâÍÆ</div></td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center'><div class='pt11b'>¶â³Û</div></td>
                </tr>
            </THEAD>
            <TFOOT>
                <!-- ¸½ºß¤Ï¥Õ¥Ã¥¿¡¼¤Ï²¿¤â¤Ê¤¤ -->
            </TFOOT>
            <TBODY>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffe4c4' align='right'>
                        <div class='pt11b'>¡Ý</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffe4c4' align='right'>
                        <div class='pt11b'>¡Ý</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffe4c4' align='right'>
                        <div class='pt11b'>¡Ý</div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' colspan='2' align='right'><div class='pt11b'>¹ç·×</div></td>
                    <td class='winbox' nowrap bgcolor='#E0FFFF' align='right'>
                        <div class='pt11b'><?= number_format($mae_hiyo_kin) ?></div>
                    </td>
                </tr>
            </TBODY>
        </table>
            </td></tr>
        </table> <!----------------- ¥À¥ß¡¼End ------------------>
        
        <BR clear='all'>
        
        <table bgcolor='#ffffff' align='left' cellspacing='0' cellpadding='10'>
            <tr><td> <!----------- ¥À¥ß¡¼(¥Ç¥¶¥¤¥óÍÑ) ------------>
        <div class='pt11b'>5.Ì¤¼ýÆþ¶â¤ÎÆâÌõ</div>
        <table class='winbox_field' width='100%' align='center' bgcolor='black' cellspacing='1' cellpadding='5'>
            <THEAD>
                <!-- ¥Æ¡¼¥Ö¥ë ¥Ø¥Ã¥À¡¼¤ÎÉ½¼¨ -->
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center'><div class='pt11b'>¶èÊ¬</div></td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center'><div class='pt11b'>ÆâÍÆ</div></td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center'><div class='pt11b'>¶â³Û</div></td>
                </tr>
            </THEAD>
            <TFOOT>
                <!-- ¸½ºß¤Ï¥Õ¥Ã¥¿¡¼¤Ï²¿¤â¤Ê¤¤ -->
            </TFOOT>
            <TBODY>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffe4c4' align='right'>
                        <div class='pt11b'>¡Ý</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffe4c4' align='right'>
                        <div class='pt11b'>¡Ý</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffe4c4' align='right'>
                        <div class='pt11b'>¡Ý</div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' colspan='2' align='right'><div class='pt11b'>¹ç·×</div></td>
                    <td class='winbox' nowrap bgcolor='#E0FFFF' align='right'>
                        <div class='pt11b'><?= number_format($ryu_mishu_total_kin) ?></div>
                    </td>
                </tr>
            </TBODY>
        </table>
            </td></tr>
        </table> <!----------------- ¥À¥ß¡¼End ------------------>
        
        <BR clear='all'>
        
        <table bgcolor='#ffffff' align='left' cellspacing='0' cellpadding='10'>
            <tr><td> <!----------- ¥À¥ß¡¼(¥Ç¥¶¥¤¥óÍÑ) ------------>
        <div class='pt11b'>6.¤½¤ÎÂ¾Î®Æ°»ñ»º¤ÎÆâÌõ</div>
        <table class='winbox_field' width='100%' align='center' bgcolor='black' cellspacing='1' cellpadding='5'>
            <THEAD>
                <!-- ¥Æ¡¼¥Ö¥ë ¥Ø¥Ã¥À¡¼¤ÎÉ½¼¨ -->
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center'><div class='pt11b'>´ªÄê²ÊÌÜ</div></td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center'><div class='pt11b'>ÆâÍÆ</div></td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center'><div class='pt11b'>¶â³Û</div></td>
                </tr>
            </THEAD>
            <TFOOT>
                <!-- ¸½ºß¤Ï¥Õ¥Ã¥¿¡¼¤Ï²¿¤â¤Ê¤¤ -->
            </TFOOT>
            <TBODY>
                <?php if ($karibara_kin<>0) { ?>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='left'>
                        <div class='pt11b'>²¾Ê§¶â</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffe4c4' align='right'>
                        <div class='pt11b'>¡¡</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#E0FFFF' align='right'>
                        <div class='pt11b'><?= number_format($karibara_kin) ?></div>
                    </td>
                </tr>
                <?php } ?>
                <?php if ($tatekae_kin<>0) { ?>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='left'>
                        <div class='pt11b'>Î©ÂØ¶â</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffe4c4' align='right'>
                        <div class='pt11b'>¡¡</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#E0FFFF' align='right'>
                        <div class='pt11b'><?= number_format($tatekae_kin) ?></div>
                    </td>
                </tr>
                <?php } ?>
                <?php if ($hokaryudo_kin<>0) { ?>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='left'>
                        <div class='pt11b'>¤½¤ÎÂ¾</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffe4c4' align='right'>
                        <div class='pt11b'>¡¡</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#E0FFFF' align='right'>
                        <div class='pt11b'><?= number_format($hokaryudo_kin) ?></div>
                    </td>
                </tr>
                <?php } ?>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' colspan='2' align='right'><div class='pt11b'>¹ç·×</div></td>
                    <td class='winbox' nowrap bgcolor='#E0FFFF' align='right'>
                        <div class='pt11b'><?= number_format($hokaryudo_total_kin) ?></div>
                    </td>
                </tr>
            </TBODY>
        </table>
            </td></tr>
        </table> <!----------------- ¥À¥ß¡¼End ------------------>
        
        <BR clear='all'>
        
        <table bgcolor='#ffffff' align='left' cellspacing='0' cellpadding='10'>
            <tr><td> <!----------- ¥À¥ß¡¼(¥Ç¥¶¥¤¥óÍÑ) ------------>
        <div class='pt11b'>7.Í­·Á¸ÇÄê»ñ»ºµÚ¤Ó¸º²Á½þµÑÈñ¤ÎÆâÌõ¡¡¡¦¡¦¡¦¡¡ÊÌ»æÌÀºÙ½ñ»²¾È</div>
            </td></tr>
        </table> <!----------------- ¥À¥ß¡¼End ------------------>
        
        <BR clear='all'>
        
        <table bgcolor='#ffffff' align='left' cellspacing='0' cellpadding='10'>
            <tr><td> <!----------- ¥À¥ß¡¼(¥Ç¥¶¥¤¥óÍÑ) ------------>
        <div class='pt11b'>8.ÅÅÏÃ²ÃÆþ¸¢¤ÎÆâÌõ</div>
        <table class='winbox_field' width='100%' align='center' bgcolor='black' cellspacing='1' cellpadding='5'>
            <THEAD>
                <!-- ¥Æ¡¼¥Ö¥ë ¥Ø¥Ã¥À¡¼¤ÎÉ½¼¨ -->
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center'><div class='pt11b'>ÅÅÏÃÈÖ¹æ</div></td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center'><div class='pt11b'>Å¦Í×</div></td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center'><div class='pt11b'>¶â³Û</div></td>
                </tr>
            </THEAD>
            <TFOOT>
                <!-- ¸½ºß¤Ï¥Õ¥Ã¥¿¡¼¤Ï²¿¤â¤Ê¤¤ -->
            </TFOOT>
            <TBODY>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center'><div class='pt11b'>028¡Ý682¡Ý8851</div></td>
                    <td class='winbox' nowrap bgcolor='#ffffff'><div class='pt11b'>È¯ÃåÎ¾ÍÑ¡ÊÂåÉ½¡Ë</div></td>
                    <td class='winbox' nowrap bgcolor='#E0FFFF' align='right'>
                        <div class='pt11b'>72,000</div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center'><div class='pt11b'>028¡Ý682¡Ý8852</div></td>
                    <td class='winbox' nowrap bgcolor='#ffffff'><div class='pt11b'>È¯ÃåÎ¾ÍÑ</div></td>
                    <td class='winbox' nowrap bgcolor='#E0FFFF' align='right'>
                        <div class='pt11b'>72,000</div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center'><div class='pt11b'>028¡Ý682¡Ý8853</div></td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right'><div class='pt11b'>¡ÊµÙ»ß¡Ë</div></td>
                    <td class='winbox' nowrap bgcolor='#E0FFFF' align='right'>
                        <div class='pt11b'>72,000</div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center'><div class='pt11b'>028¡Ý682¡Ý9153</div></td>
                    <td class='winbox' nowrap bgcolor='#ffffff'><div class='pt11b'>¥À¥¤¥ä¥ë¥¤¥ó¡ÊÁíÌ³¡Ë</div></td>
                    <td class='winbox' nowrap bgcolor='#E0FFFF' align='right'>
                        <div class='pt11b'>72,000</div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center'><div class='pt11b'>028¡Ý682¡Ý9250</div></td>
                    <td class='winbox' nowrap bgcolor='#ffffff'><div class='pt11b'>¥À¥¤¥ä¥ë¥¤¥ó¡Ê¹ØÇã¡Ë</div></td>
                    <td class='winbox' nowrap bgcolor='#E0FFFF' align='right'>
                        <div class='pt11b'>72,000</div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center'><div class='pt11b'>028¡Ý682¡Ý7471</div></td>
                    <td class='winbox' nowrap bgcolor='#ffffff'><div class='pt11b'>¥À¥¤¥ä¥ë¥¤¥ó</div></td>
                    <td class='winbox' nowrap bgcolor='#E0FFFF' align='right'>
                        <div class='pt11b'>72,000</div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center'><div class='pt11b'>028¡Ý682¡Ý3044</div></td>
                    <td class='winbox' nowrap bgcolor='#ffffff'><div class='pt11b'>¥Ô¥ó¥¯ÅÅÏÃ¡Ê¿©Æ²¡Ë</div></td>
                    <td class='winbox' nowrap bgcolor='#E0FFFF' align='right'>
                        <div class='pt11b'>72,000</div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center'><div class='pt11b'>028¡Ý681¡Ý6481</div></td>
                    <td class='winbox' nowrap bgcolor='#ffffff'><div class='pt11b'>¾¦ÉÊ´ÉÍý</div></td>
                    <td class='winbox' nowrap bgcolor='#E0FFFF' align='right'>
                        <div class='pt11b'>72,000</div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center'><div class='pt11b'>028¡Ý681¡Ý6482</div></td>
                    <td class='winbox' nowrap bgcolor='#ffffff'><div class='pt11b'>¾¦ÉÊ´ÉÍý</div></td>
                    <td class='winbox' nowrap bgcolor='#E0FFFF' align='right'>
                        <div class='pt11b'>72,000</div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center'><div class='pt11b'>028¡Ý682¡Ý7367</div></td>
                    <td class='winbox' nowrap bgcolor='#ffffff'><div class='pt11b'>£Æ£Á£Ø¡Ê¾¦ÉÊ´ÉÍý¡Ë</div></td>
                    <td class='winbox' nowrap bgcolor='#E0FFFF' align='right'>
                        <div class='pt11b'>72,000</div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center'><div class='pt11b'>028¡Ý681¡Ý7038</div></td>
                    <td class='winbox' nowrap bgcolor='#ffffff'><div class='pt11b'>£Æ£Á£Ø¡Ê»öÌ³½êÅï¡¦£É£Ó£Ä£Î¡Ë</div></td>
                    <td class='winbox' nowrap bgcolor='#E0FFFF' align='right'>
                        <div class='pt11b'>72,000</div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center'><div class='pt11b'>028¡Ý682¡Ý1324</div></td>
                    <td class='winbox' nowrap bgcolor='#ffffff'><div class='pt11b'>£Æ£Á£Ø¡ÊÂè6¹©¾ì1³¬»öÌ³½ê¡Ë</div></td>
                    <td class='winbox' nowrap bgcolor='#E0FFFF' align='right'>
                        <div class='pt11b'>72,000</div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center'><div class='pt11b'>028¡Ý681¡Ý7652</div></td>
                    <td class='winbox' nowrap bgcolor='#ffffff'><div class='pt11b'>»î¸³½¤ÍýÄ¾ÄÌ</div></td>
                    <td class='winbox' nowrap bgcolor='#E0FFFF' align='right'>
                        <div class='pt11b'>72,000</div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center'><div class='pt11b'>028¡Ý681¡Ý5105</div></td>
                    <td class='winbox' nowrap bgcolor='#ffffff'><div class='pt11b'>¸ò´¹µ¡</div></td>
                    <td class='winbox' nowrap bgcolor='#E0FFFF' align='right'>
                        <div class='pt11b'>72,000</div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center'><div class='pt11b'>028¡Ý681¡Ý7011</div></td>
                    <td class='winbox' nowrap bgcolor='#ffffff'><div class='pt11b'>£Ô£Ö²ñµÄÍÑ£É£Ó£Ä£Î</div></td>
                    <td class='winbox' nowrap bgcolor='#E0FFFF' align='right'>
                        <div class='pt11b'>72,000</div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center'><div class='pt11b'>028¡Ý681¡Ý7735</div></td>
                    <td class='winbox' nowrap bgcolor='#ffffff'><div class='pt11b'>¥µ¡¼¥Ð¡¼¼¼ÍÑ</div></td>
                    <td class='winbox' nowrap bgcolor='#E0FFFF' align='right'>
                        <div class='pt11b'>72,000</div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center'><div class='pt11b'>028¡Ý682¡Ý8853</div></td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right'><div class='pt11b'>¡ÊµÙ»ß¡Ë</div></td>
                    <td class='winbox' nowrap bgcolor='#E0FFFF' align='right'>
                        <div class='pt11b'>72,000</div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' colspan='2' align='right'><div class='pt11b'>¹ç·×</div></td>
                    <td class='winbox' nowrap bgcolor='#E0FFFF' align='right'>
                        <div class='pt11b'><?= number_format($denwa_kin) ?></div>
                    </td>
                </tr>
            </TBODY>
        </table>
            </td></tr>
        </table> <!----------------- ¥À¥ß¡¼End ------------------>
        
        <BR clear='all'>
        
        <table bgcolor='#ffffff' align='left' cellspacing='0' cellpadding='10'>
            <tr><td> <!----------- ¥À¥ß¡¼(¥Ç¥¶¥¤¥óÍÑ) ------------>
        <div class='pt11b'>9.¥½¥Õ¥È¥¦¥§¥¢¤ÎÆâÌõ¡¡¡¦¡¦¡¦¡¡ÊÌ»æÌÀºÙ½ñ»²¾È</div>
            </td></tr>
        </table> <!----------------- ¥À¥ß¡¼End ------------------>
        
        <BR clear='all'>
        
        <table bgcolor='#ffffff' align='left' cellspacing='0' cellpadding='10'>
            <tr><td> <!----------- ¥À¥ß¡¼(¥Ç¥¶¥¤¥óÍÑ) ------------>
        <div class='pt11b'>10.·«±äÀÇ¶â»ñ»º¤ÎÆâÌõ</div>
        <table class='winbox_field' width='100%' align='center' bgcolor='black' cellspacing='1' cellpadding='5'>
            <THEAD>
                <!-- ¥Æ¡¼¥Ö¥ë ¥Ø¥Ã¥À¡¼¤ÎÉ½¼¨ -->
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center'><div class='pt11b'>Å¦Í×</div></td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center'><div class='pt11b'>´ü¼ó»Ä¹â</div></td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center'><div class='pt11b'>Åö´üÁý²Ã³Û</div></td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center'><div class='pt11b'>Åö´ü¸º¾¯³Û</div></td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center'><div class='pt11b'>´üËö»Ä¹â</div></td>
                </tr>
            </THEAD>
            <TFOOT>
                <!-- ¸½ºß¤Ï¥Õ¥Ã¥¿¡¼¤Ï²¿¤â¤Ê¤¤ -->
            </TFOOT>
            <TBODY>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='left'><div class='pt11b'>¸ÇÄê»ñ»º</div></td>
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
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right'><div class='pt11b'>¹ç·×</div></td>
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
        </table> <!----------------- ¥À¥ß¡¼End ------------------>
        
        <BR clear='all'>
        
        <table bgcolor='#ffffff' align='left' cellspacing='0' cellpadding='10'>
            <tr><td> <!----------- ¥À¥ß¡¼(¥Ç¥¶¥¤¥óÍÑ) ------------>
        <div class='pt11b'>11.Ä¹´üÂßÉÕ¶â¤ÎÆâÌõ</div>
        <table class='winbox_field' width='100%' align='center' bgcolor='black' cellspacing='1' cellpadding='5'>
            <THEAD>
                <!-- ¥Æ¡¼¥Ö¥ë ¥Ø¥Ã¥À¡¼¤ÎÉ½¼¨ -->
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center'><div class='pt11b'>Å¦Í×</div></td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center'><div class='pt11b'>´ü¼ó»Ä¹â</div></td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center'><div class='pt11b'>Åö´üÁý²Ã³Û</div></td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center'><div class='pt11b'>Åö´ü¸º¾¯³Û</div></td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center'><div class='pt11b'>´üËö»Ä¹â</div></td>
                </tr>
            </THEAD>
            <TFOOT>
                <!-- ¸½ºß¤Ï¥Õ¥Ã¥¿¡¼¤Ï²¿¤â¤Ê¤¤ -->
            </TFOOT>
            <TBODY>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='left'><div class='pt11b'>½¾¶È°÷ÂßÉÕ¶â</div></td>
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
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right'><div class='pt11b'>¹ç·×</div></td>
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
        </table> <!----------------- ¥À¥ß¡¼End ------------------>
        
        <BR clear='all'>
        
        <table bgcolor='#ffffff' align='left' cellspacing='0' cellpadding='10'>
            <tr><td> <!----------- ¥À¥ß¡¼(¥Ç¥¶¥¤¥óÍÑ) ------------>
        <div class='pt11b'>12.Ä¹´üÁ°Ê§ÈñÍÑ¤ÎÆâÌõ</div>
        <table class='winbox_field' width='100%' align='center' bgcolor='black' cellspacing='1' cellpadding='5'>
            <THEAD>
                <!-- ¥Æ¡¼¥Ö¥ë ¥Ø¥Ã¥À¡¼¤ÎÉ½¼¨ -->
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center'><div class='pt11b'>Å¦Í×</div></td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center'><div class='pt11b'>´ü¼ó»Ä¹â</div></td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center'><div class='pt11b'>Åö´üÁý²Ã³Û</div></td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center'><div class='pt11b'>Åö´ü¸º¾¯³Û</div></td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center'><div class='pt11b'>´üËö»Ä¹â</div></td>
                </tr>
            </THEAD>
            <TFOOT>
                <!-- ¸½ºß¤Ï¥Õ¥Ã¥¿¡¼¤Ï²¿¤â¤Ê¤¤ -->
            </TFOOT>
            <TBODY>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffe4c4' align='right'>
                        <div class='pt11b'>¡Ý</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffe4c4' align='right'>
                        <div class='pt11b'>¡Ý</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffe4c4' align='right'>
                        <div class='pt11b'>¡Ý</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffe4c4' align='right'>
                        <div class='pt11b'>¡Ý</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffe4c4' align='right'>
                        <div class='pt11b'>¡Ý</div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right'><div class='pt11b'>¹ç·×</div></td>
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
        </table> <!----------------- ¥À¥ß¡¼End ------------------>
        
        <BR clear='all'>
        
        <table bgcolor='#ffffff' align='left' cellspacing='0' cellpadding='10'>
            <tr><td> <!----------- ¥À¥ß¡¼(¥Ç¥¶¥¤¥óÍÑ) ------------>
        <div class='pt11b'>13.¤½¤ÎÂ¾Åê»ñÅù¤ÎÆâÌõ</div>
        <table class='winbox_field' width='100%' align='center' bgcolor='black' cellspacing='1' cellpadding='5'>
            <THEAD>
                <!-- ¥Æ¡¼¥Ö¥ë ¥Ø¥Ã¥À¡¼¤ÎÉ½¼¨ -->
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center'><div class='pt11b'>¶èÊ¬</div></td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center'><div class='pt11b'>»ÙÊ§Àè</div></td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center'><div class='pt11b'>¶â³Û</div></td>
                </tr>
            </THEAD>
            <TFOOT>
                <!-- ¸½ºß¤Ï¥Õ¥Ã¥¿¡¼¤Ï²¿¤â¤Ê¤¤ -->
            </TFOOT>
            <TBODY>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='left'><div class='pt11b'>½Ð»ñ¶â</div></td>
                    <td class='winbox' nowrap bgcolor='#ffffff'><div class='pt11b'>¾ðÊóÄÌ¿®¥·¥¹¥Æ¥à¶¨Æ±ÁÈ¹ç</div></td>
                    <td class='winbox' nowrap bgcolor='#E0FFFF' align='right'>
                        <div class='pt11b'>10,000</div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' colspan='2'><div class='pt11b'>¹ç·×</div></td>
                    <td class='winbox' nowrap bgcolor='#E0FFFF' align='right'>
                        <div class='pt11b'>10,000</div>
                    </td>
                </tr>
            </TBODY>
        </table>
            </td></tr>
        </table> <!----------------- ¥À¥ß¡¼End ------------------>
        
        <BR clear='all'>
        
        <table bgcolor='#ffffff' align='left' cellspacing='0' cellpadding='10'>
            <tr><td> <!----------- ¥À¥ß¡¼(¥Ç¥¶¥¤¥óÍÑ) ------------>
        <div class='pt11b'>14.Çã³Ý¶â¤ÎÆâÌõ</div>
        <table class='winbox_field' width='100%' align='center' bgcolor='black' cellspacing='1' cellpadding='5'>
            <THEAD>
                <!-- ¥Æ¡¼¥Ö¥ë ¥Ø¥Ã¥À¡¼¤ÎÉ½¼¨ -->
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center'><div class='pt11b'>²ñ¼ÒÌ¾</div></td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center'><div class='pt11b'>ËÜ¼Ò½»½ê</div></td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center'><div class='pt11b'>¶â³Û</div></td>
                </tr>
            </THEAD>
            <TFOOT>
                <!-- ¸½ºß¤Ï¥Õ¥Ã¥¿¡¼¤Ï²¿¤â¤Ê¤¤ -->
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
                        <div class='pt11b'>¤½¤ÎÂ¾</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='left'>
                        <div class='pt11b'>¡¡</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#E0FFFF' align='right'>
                        <div class='pt11b'><?= number_format($kaikake_top_sonota_kin) ?></div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' colspan='2'><div class='pt11b'>¹ç·×</div></td>
                    <td class='winbox' nowrap bgcolor='#E0FFFF' align='right'>
                        <div class='pt11b'><?= number_format($kaikake_total_kin) ?></div>
                    </td>
                </tr>
            </TBODY>
        </table>
            </td></tr>
        </table> <!----------------- ¥À¥ß¡¼End ------------------>
        
        <BR clear='all'>
        
        <table bgcolor='#ffffff' align='left' cellspacing='0' cellpadding='10'>
            <tr><td> <!----------- ¥À¥ß¡¼(¥Ç¥¶¥¤¥óÍÑ) ------------>
        <div class='pt11b'>15.Ì¤Ê§¶â¤ÎÆâÌõ</div>
        <table class='winbox_field' width='100%' align='center' bgcolor='black' cellspacing='1' cellpadding='5'>
            <THEAD>
                <!-- ¥Æ¡¼¥Ö¥ë ¥Ø¥Ã¥À¡¼¤ÎÉ½¼¨ -->
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center'><div class='pt11b'>²ñ¼ÒÌ¾¡¦¶èÊ¬</div></td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center'><div class='pt11b'>ËÜ¼Ò½»½ê¡¦ÆâÍÆ</div></td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center'><div class='pt11b'>¶â³Û</div></td>
                </tr>
            </THEAD>
            <TFOOT>
                <!-- ¸½ºß¤Ï¥Õ¥Ã¥¿¡¼¤Ï²¿¤â¤Ê¤¤ -->
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
                        <div class='pt11b'>¤½¤ÎÂ¾</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='left'>
                        <div class='pt11b'>¡¡</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#E0FFFF' align='right'>
                        <div class='pt11b'><?= number_format($miharai_top_sonota_kin) ?></div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' colspan='2'><div class='pt11b'>¹ç·×</div></td>
                    <td class='winbox' nowrap bgcolor='#E0FFFF' align='right'>
                        <div class='pt11b'><?= number_format($miharai_total_kin) ?></div>
                    </td>
                </tr>
            </TBODY>
        </table>
            </td></tr>
        </table> <!----------------- ¥À¥ß¡¼End ------------------>
        
        <BR clear='all'>
        
        <table bgcolor='#ffffff' align='left' cellspacing='0' cellpadding='10'>
            <tr><td> <!----------- ¥À¥ß¡¼(¥Ç¥¶¥¤¥óÍÑ) ------------>
        <div class='pt11b'>16.Ì¤Ê§¾ÃÈñÀÇÅù¤ÎÆâÌõ</div>
        <table class='winbox_field' width='100%' align='center' bgcolor='black' cellspacing='1' cellpadding='5'>
            <THEAD>
                <!-- ¥Æ¡¼¥Ö¥ë ¥Ø¥Ã¥À¡¼¤ÎÉ½¼¨ -->
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center'><div class='pt11b'>¶èÊ¬</div></td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center'><div class='pt11b'>ÆâÍÆ</div></td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center'><div class='pt11b'>¶â³Û</div></td>
                </tr>
            </THEAD>
            <TFOOT>
                <!-- ¸½ºß¤Ï¥Õ¥Ã¥¿¡¼¤Ï²¿¤â¤Ê¤¤ -->
            </TFOOT>
            <TBODY>
                <tr>
                    <td class='winbox' nowrap bgcolor='#FFFFFF' align='left'>
                        <div class='pt11b'>²¾Ê§¾ÃÈñÀÇ</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#E0FFFF' align='left'>
                        <div class='pt11b'>¡ÊÍ½ÄêÇ¼ÉÕ³Û<?= number_format($mae_sho_zei_kin) ?>±ß´Þ¤à¡Ë</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#E0FFFF' align='right'>
                        <div class='pt11b'><?= number_format($karibara_zei_total) ?></div>
                        <!--
                        <div class='pt11b'><?= mb_ereg_replace('-', '¢¤', number_format($karibara_zei_total)) ?></div>
                        -->
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#FFFFFF' align='left'>
                        <div class='pt11b'>²¾¼õ¾ÃÈñÀÇ</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#FFFFFF' align='right'>
                        <div class='pt11b'>¡¡</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#E0FFFF' align='right'>
                        <div class='pt11b'><?= number_format($kariuke_sho_zei_kin) ?></div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' colspan='2'><div class='pt11b'>¹ç·×</div></td>
                    <td class='winbox' nowrap bgcolor='#E0FFFF' align='right'>
                        <div class='pt11b'><?= number_format($mihazei_total_kin) ?></div>
                    </td>
                </tr>
            </TBODY>
        </table>
            </td></tr>
        </table> <!----------------- ¥À¥ß¡¼End ------------------>
        
        <BR clear='all'>
        
        <table bgcolor='#ffffff' align='left' cellspacing='0' cellpadding='10'>
            <tr><td> <!----------- ¥À¥ß¡¼(¥Ç¥¶¥¤¥óÍÑ) ------------>
        <div class='pt11b'>17.Ì¤Ê§Ë¡¿ÍÀÇÅù¤ÎÆâÌõ</div>
        <table class='winbox_field' width='100%' align='center' bgcolor='black' cellspacing='1' cellpadding='5'>
            <THEAD>
                <!-- ¥Æ¡¼¥Ö¥ë ¥Ø¥Ã¥À¡¼¤ÎÉ½¼¨ -->
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center'><div class='pt11b'>¶èÊ¬</div></td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center'><div class='pt11b'>´ü¼ó»Ä¹â</div></td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center'><div class='pt11b'>Åö´ü»ÙÊ§³Û</div></td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center'><div class='pt11b'>Åö´üÌáÆþ³Û</div></td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center'><div class='pt11b'>Åö´üÀßÄê³Û</div></td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center'><div class='pt11b'>´üËö»Ä¹â</div></td>
                </tr>
            </THEAD>
            <TFOOT>
                <!-- ¸½ºß¤Ï¥Õ¥Ã¥¿¡¼¤Ï²¿¤â¤Ê¤¤ -->
            </TFOOT>
            <TBODY>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='left'>
                        <div class='pt11b'>Ì¤Ê§Ë¡¿ÍÀÇÅù</div>
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
        </table> <!----------------- ¥À¥ß¡¼End ------------------>
        
        <BR clear='all'>
        
        <table bgcolor='#ffffff' align='left' cellspacing='0' cellpadding='10'>
            <tr><td> <!----------- ¥À¥ß¡¼(¥Ç¥¶¥¤¥óÍÑ) ------------>
        <div class='pt11b'>18.Ì¤Ê§ÈñÍÑ¤ÎÆâÌõ</div>
        <table class='winbox_field' width='100%' align='center' bgcolor='black' cellspacing='1' cellpadding='5'>
            <THEAD>
                <!-- ¥Æ¡¼¥Ö¥ë ¥Ø¥Ã¥À¡¼¤ÎÉ½¼¨ -->
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center'><div class='pt11b'>²ñ¼ÒÌ¾¡¦¶èÊ¬</div></td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center'><div class='pt11b'>ËÜ¼Ò½»½ê¡¦ÆâÍÆ</div></td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center'><div class='pt11b'>¶â³Û</div></td>
                </tr>
            </THEAD>
            <TFOOT>
                <!-- ¸½ºß¤Ï¥Õ¥Ã¥¿¡¼¤Ï²¿¤â¤Ê¤¤ -->
            </TFOOT>
            <TBODY>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffe4c4' align='right'>
                        <div class='pt11b'>¡Ý</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffe4c4' align='right'>
                        <div class='pt11b'>¡Ý</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffe4c4' align='right'>
                        <div class='pt11b'>¡Ý</div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' colspan='2'><div class='pt11b'>¹ç·×</div></td>
                    <td class='winbox' nowrap bgcolor='#E0FFFF' align='right'>
                        <div class='pt11b'><?= number_format($miharai_hiyo_kin) ?></div>
                    </td>
                </tr>
            </TBODY>
        </table>
            </td></tr>
        </table> <!----------------- ¥À¥ß¡¼End ------------------>
        
        <BR clear='all'>
        
        <table bgcolor='#ffffff' align='left' cellspacing='0' cellpadding='10'>
            <tr><td> <!----------- ¥À¥ß¡¼(¥Ç¥¶¥¤¥óÍÑ) ------------>
        <div class='pt11b'>19.ÍÂ¤ê¶â¤ÎÆâÌõ</div>
        <table class='winbox_field' width='100%' align='center' bgcolor='black' cellspacing='1' cellpadding='5'>
            <THEAD>
                <!-- ¥Æ¡¼¥Ö¥ë ¥Ø¥Ã¥À¡¼¤ÎÉ½¼¨ -->
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center'><div class='pt11b'>¶èÊ¬</div></td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center'><div class='pt11b'>ËÜ¼Ò½»½ê¡¦ÆâÍÆ</div></td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center'><div class='pt11b'>¶â³Û</div></td>
                </tr>
            </THEAD>
            <TFOOT>
                <!-- ¸½ºß¤Ï¥Õ¥Ã¥¿¡¼¤Ï²¿¤â¤Ê¤¤ -->
            </TFOOT>
            <TBODY>
                <?php if ($gen_shotoku_kin<>0) { ?>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='left'>
                        <div class='pt11b'>¸»Àô½êÆÀÀÇ</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffe4c4' align='right'>
                        <div class='pt11b'>¡¡</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#E0FFFF' align='right'>
                        <div class='pt11b'><?= number_format($gen_shotoku_kin) ?></div>
                    </td>
                </tr>
                <?php } ?>
                <?php if ($gen_jyu_kin<>0) { ?>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='left'>
                        <div class='pt11b'>¸»Àô½»Ì±ÀÇ</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffe4c4' align='right'>
                        <div class='pt11b'>¡¡</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#E0FFFF' align='right'>
                        <div class='pt11b'><?= number_format($gen_jyu_kin) ?></div>
                    </td>
                </tr>
                <?php } ?>
                <?php if ($ken_hoken_kin<>0) { ?>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='left'>
                        <div class='pt11b'>·ò¹¯ÊÝ¸±ÎÁ</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffe4c4' align='right'>
                        <div class='pt11b'>¡¡</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#E0FFFF' align='right'>
                        <div class='pt11b'><?= number_format($ken_hoken_kin) ?></div>
                    </td>
                </tr>
                <?php } ?>
                <?php if ($kou_hoken_kin<>0) { ?>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='left'>
                        <div class='pt11b'>¸üÀ¸Ç¯¶âÊÝ¸±ÎÁ</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffe4c4' align='right'>
                        <div class='pt11b'>¡¡</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#E0FFFF' align='right'>
                        <div class='pt11b'><?= number_format($kou_hoken_kin) ?></div>
                    </td>
                </tr>
                <?php } ?>
                <?php if ($azu_sonota_kin<>0) { ?>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='left'>
                        <div class='pt11b'>¤½¤ÎÂ¾</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right'>
                        <div class='pt11b'>¡¡</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#E0FFFF' align='right'>
                        <div class='pt11b'><?= number_format($azu_sonota_kin) ?></div>
                    </td>
                </tr>
                <?php } ?>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' colspan='2'><div class='pt11b'>¹ç·×</div></td>
                    <td class='winbox' nowrap bgcolor='#E0FFFF' align='right'>
                        <div class='pt11b'><?= number_format($azukari_kin) ?></div>
                    </td>
                </tr>
            </TBODY>
        </table>
            </td></tr>
        </table> <!----------------- ¥À¥ß¡¼End ------------------>
        
        <BR clear='all'>
        
        <table bgcolor='#ffffff' align='left' cellspacing='0' cellpadding='10'>
            <tr><td> <!----------- ¥À¥ß¡¼(¥Ç¥¶¥¤¥óÍÑ) ------------>
        <div class='pt11b'>20.°úÅö¶â¤ÎÆâÌõ</div>
        <table class='winbox_field' width='100%' align='center' bgcolor='black' cellspacing='1' cellpadding='5'>
            <THEAD>
                <!-- ¥Æ¡¼¥Ö¥ë ¥Ø¥Ã¥À¡¼¤ÎÉ½¼¨ -->
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' rowspan='2'><div class='pt11b'>¶èÊ¬</div></td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' rowspan='2'><div class='pt11b'>´ü¼ó»Ä¹â</div></td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' rowspan='2'><div class='pt11b'>Åö´üÁý²Ã³Û</div></td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' colspan='2'><div class='pt11b'>Åö´ü¸º¾¯³Û</div></td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' rowspan='2'><div class='pt11b'>´üËö»Ä¹â</div></td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center'><div class='pt11b'>ÌÜÅª»ÈÍÑ</div></td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center'><div class='pt11b'>¤½¤ÎÂ¾</div></td>
                </tr>
            </THEAD>
            <TFOOT>
                <!-- ¸½ºß¤Ï¥Õ¥Ã¥¿¡¼¤Ï²¿¤â¤Ê¤¤ -->
            </TFOOT>
            <TBODY>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='left'>
                        <div class='pt11b'>¾ÞÍ¿°úÅö¶â</div>
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
                        <div class='pt11b'>Âà¿¦µëÉÕ°úÅö¶â</div>
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
        </table> <!----------------- ¥À¥ß¡¼End ------------------>
        
        <BR clear='all'>
        
        <table bgcolor='#ffffff' align='left' cellspacing='0' cellpadding='10'>
            <tr><td> <!----------- ¥À¥ß¡¼(¥Ç¥¶¥¤¥óÍÑ) ------------>
        <div class='pt11b'>21.»ñËÜ¶âµÚ¤Ó¾êÍ¾¶â¤ÎÆâÌõ</div>
        <table class='winbox_field' width='100%' align='center' bgcolor='black' cellspacing='1' cellpadding='5'>
            <THEAD>
                <!-- ¥Æ¡¼¥Ö¥ë ¥Ø¥Ã¥À¡¼¤ÎÉ½¼¨ -->
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center'><div class='pt11b'>¶èÊ¬</div></td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center'><div class='pt11b'>¼ïÎà</div></td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center'><div class='pt11b'>´ü¼ó»Ä¹â</div></td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center'><div class='pt11b'>Åö´üÁý²Ã³Û</div></td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center'><div class='pt11b'>Åö´ü¸º¾¯³Û</div></td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center'><div class='pt11b'>´üËö»Ä¹â</div></td>
                </tr>
            </THEAD>
            <TFOOT>
                <!-- ¸½ºß¤Ï¥Õ¥Ã¥¿¡¼¤Ï²¿¤â¤Ê¤¤ -->
            </TFOOT>
            <TBODY>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='left'>
                        <div class='pt11b'>»ñËÜ¶â</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center'>
                        <div class='pt11b'>ÉáÄÌ³ô¼°</div>
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
                        <div class='pt11b'>»ñËÜ½àÈ÷¶â</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right'>
                        <div class='pt11b'>¡¡</div>
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
                        <div class='pt11b'>¤½¤ÎÂ¾»ñËÜ¾êÍ¾¶â</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right'>
                        <div class='pt11b'>¡¡</div>
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
                        <div class='pt11b'>¤½¤ÎÂ¾Íø±×¾êÍ¾¶â</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right'>
                        <div class='pt11b'>¡¡</div>
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
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' colspan='2'><div class='pt11b'>¹ç·×</div></td>
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
        </table> <!----------------- ¥À¥ß¡¼End ------------------>
        
        <BR clear='all'>
        
        <table bgcolor='#ffffff' align='left' cellspacing='0' cellpadding='10'>
            <tr><td> <!----------- ¥À¥ß¡¼(¥Ç¥¶¥¤¥óÍÑ) ------------>
        <div class='pt11b'>22.½ôÀÇ¸ø²Ý¤ÎÆâÌõ</div>
        <table class='winbox_field' width='100%' align='center' bgcolor='black' cellspacing='1' cellpadding='5'>
            <THEAD>
                <!-- ¥Æ¡¼¥Ö¥ë ¥Ø¥Ã¥À¡¼¤ÎÉ½¼¨ -->
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' rowspan='2'><div class='pt11b'>Å¦Í×</div></td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center'><div class='pt11b'>À½Â¤ÍÑ</div></td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center'><div class='pt11b'>ÈÎ´ÉÈñµÚ¤Ó°ìÈÌ´ÉÍýÈñ</div></td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' rowspan='2'><div class='pt11b'>¹ç·×¶â³Û</div></td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' rowspan='2'><div class='pt11b'>È÷¹Í</div></td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center'><div class='pt11b'>¶â³Û</div></td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center'><div class='pt11b'>¶â³Û</div></td>
                </tr>
            </THEAD>
            <TFOOT>
                <!-- ¸½ºß¤Ï¥Õ¥Ã¥¿¡¼¤Ï²¿¤â¤Ê¤¤ -->
            </TFOOT>
            <TBODY>
                <?php if ($kotei_zei_total_kin<>0) { ?>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='left'>
                        <div class='pt11b'>¸ÇÄê»ñ»ºÀÇ</div>
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
                        <div class='pt11b'>¡¡</div>
                    </td>
                </tr>
                <?php } ?>
                <?php if ($inshi_zei_total_kin<>0) { ?>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='left'>
                        <div class='pt11b'>°õ»æÀÇ</div>
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
                        <div class='pt11b'>¡¡</div>
                    </td>
                </tr>
                <?php } ?>
                <?php if ($touroku_zei_total_kin<>0) { ?>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='left'>
                        <div class='pt11b'>ÅÐÏ¿ÌÈµöÀÇ</div>
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
                        <div class='pt11b'>¡¡</div>
                    </td>
                </tr>
                <?php } ?>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right'><div class='pt11b'>¹ç·×</div></td>
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
                        <div class='pt11b'>¡¡</div>
                    </td>
                </tr>
            </TBODY>
        </table>
            </td></tr>
        </table> <!----------------- ¥À¥ß¡¼End ------------------>
        
        <BR clear='all'>
        
        <table bgcolor='#ffffff' align='left' cellspacing='0' cellpadding='10'>
            <tr><td> <!----------- ¥À¥ß¡¼(¥Ç¥¶¥¤¥óÍÑ) ------------>
        <div class='pt11b'>23.»¨¼ýÆþ¤ÎÆâÌõ</div>
        <table class='winbox_field' width='100%' align='center' bgcolor='black' cellspacing='1' cellpadding='5'>
            <THEAD>
                <!-- ¥Æ¡¼¥Ö¥ë ¥Ø¥Ã¥À¡¼¤ÎÉ½¼¨ -->
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center'><div class='pt11b'>Å¦Í×</div></td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center'><div class='pt11b'>¶â³Û</div></td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center'><div class='pt11b'>È÷¹Í</div></td>
                </tr>
            </THEAD>
            <TFOOT>
                <!-- ¸½ºß¤Ï¥Õ¥Ã¥¿¡¼¤Ï²¿¤â¤Ê¤¤ -->
            </TFOOT>
            <TBODY>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffe4c4' align='right'>
                        <div class='pt11b'>¡Ý</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffe4c4' align='right'>
                        <div class='pt11b'>¡Ý</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right'>
                        <div class='pt11b'>¡¡</div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right'><div class='pt11b'>¹ç·×</div></td>
                    <td class='winbox' nowrap bgcolor='#E0FFFF' align='right'>
                        <div class='pt11b'><?= number_format($eigyo_shueki_total_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right'>
                        <div class='pt11b'>¡¡</div>
                    </td>
                </tr>
            </TBODY>
        </table>
            </td></tr>
        </table> <!----------------- ¥À¥ß¡¼End ------------------>
        
        <BR clear='all'>
        
        <table bgcolor='#ffffff' align='left' cellspacing='0' cellpadding='10'>
            <tr><td> <!----------- ¥À¥ß¡¼(¥Ç¥¶¥¤¥óÍÑ) ------------>
        <div class='pt11b'>24.Ë¡¿ÍÀÇ¡¦½»Ì±ÀÇµÚ¤Ó»ö¶ÈÀÇ¤ÎÆâÌõ</div>
        <table class='winbox_field' width='100%' align='center' bgcolor='black' cellspacing='1' cellpadding='5'>
            <THEAD>
                <!-- ¥Æ¡¼¥Ö¥ë ¥Ø¥Ã¥À¡¼¤ÎÉ½¼¨ -->
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center'><div class='pt11b'>Å¦Í×</div></td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center'><div class='pt11b'>¶â³Û</div></td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center'><div class='pt11b'>È÷¹Í</div></td>
                </tr>
            </THEAD>
            <TFOOT>
                <!-- ¸½ºß¤Ï¥Õ¥Ã¥¿¡¼¤Ï²¿¤â¤Ê¤¤ -->
            </TFOOT>
            <TBODY>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='left'>
                        <div class='pt11b'>Åö´üË¡¿ÍÀÇ½»Ì±ÀÇ»ö¶ÈÀÇ°úÅö³Û</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffe4c4' align='right'>
                        <div class='pt11b'><?= number_format($toki_hojin_jigyo) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right'>
                        <div class='pt11b'>¡¡</div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='left'>
                        <div class='pt11b'>ÍÂ¶âÍøÂ©Åù¤ËÂÐ¤¹¤ë¸»Àô½êÆÀÀÇ³Û</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#E0FFFF' align='right'>
                        <div class='pt11b'><?= number_format($gensen_shotoku_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right'>
                        <div class='pt11b'>¡¡</div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right'><div class='pt11b'>¹ç·×</div></td>
                    <td class='winbox' nowrap bgcolor='#E0FFFF' align='right'>
                        <div class='pt11b'><?= number_format($hojin_uchi_total_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right'>
                        <div class='pt11b'>¡¡</div>
                    </td>
                </tr>
            </TBODY>
        </table>
            </td></tr>
        </table> <!----------------- ¥À¥ß¡¼End ------------------>
        <!--
        <form method='post' action='<?php echo $menu->out_self() ?>'>
            <input class='pt10b' type='submit' name='input_data' value='ÅÐÏ¿' onClick='return data_input_click(this)'>
        </form>
        -->
</body>
</html>
