<?php
//////////////////////////////////////////////////////////////////////////////
// »ÍÈ¾´ü ¸º²Á½şµÑÈñÌÀºÙÉ½ ¾È²ñ                                             //
// Copyright (C) 2020-2020 Norihisa.Ohya norihisa_ooya@nitto-kohki.co.jp    //
//                                                                          //
// Changed history                                                          //
// 2020/01/27 Created  depreciation_statement_view.php                      //
// 2020/07/01 ¥Ç¡¼¥¿¤òAS¤«¤é¼èÆÀ¤ËÊÑ¹¹                                      //
//////////////////////////////////////////////////////////////////////////////
// ini_set('error_reporting', E_ALL || E_STRICT);
// ini_set('error_reporting',E_ALL);           // E_ALL='2047' debug ÍÑ
// ini_set('display_errors','1');              // Error É½¼¨ ON debug ÍÑ ¥ê¥ê¡¼¥¹¸å¥³¥á¥ó¥È
session_start();                            // ini_set()¤Î¼¡¤Ë»ØÄê¤¹¤ë¤³¤È Script ºÇ¾å¹Ô

require_once ('../../function.php');        // define.php ¤È pgsql.php ¤ò require_once ¤·¤Æ¤¤¤ë
require_once ('../../tnk_func.php');        // TNK ¤Ë°ÍÂ¸¤¹¤ëÉôÊ¬¤Î´Ø¿ô¤ò require_once ¤·¤Æ¤¤¤ë
require_once ('../../MenuHeader.php');      // TNK Á´¶¦ÄÌ menu class
access_log();                               // Script Name ¤Ï¼«Æ°¼èÆÀ

///// TNK ¶¦ÍÑ¥á¥Ë¥å¡¼¥¯¥é¥¹¤Î¥¤¥ó¥¹¥¿¥ó¥¹¤òºîÀ®
$menu = new MenuHeader(0);                  // Ç§¾Ú¥Á¥§¥Ã¥¯0=°ìÈÌ°Ê¾å Ìá¤êÀè=TOP_MENU ¥¿¥¤¥È¥ëÌ¤ÀßÄê
    // ¼Âºİ¤ÎÇ§¾Ú¤Ïprofit_loss_submit.php¤Ç¹Ô¤Ã¤Æ¤¤¤ëaccount_group_check()¤ò»ÈÍÑ

////////////// ¥µ¥¤¥ÈÀßÄê
// $menu->set_site(10, 7);                     // site_index=10(Â»±×¥á¥Ë¥å¡¼) site_id=7(·î¼¡Â»±×)
//////////// É½Âê¤ÎÀßÄê
$menu->set_caption('ÆÊÌÚÆüÅì¹©´ï');
//////////// ¸Æ½ĞÀè¤ÎactionÌ¾¤È¥¢¥É¥ì¥¹ÀßÄê
// $menu->set_action('Ãê¾İ²½Ì¾',   PL . 'address.php');

///// ¸Æ½Ğ¤â¤È¤Î URL ¤ò¼èÆÀ
$url_referer     = $_SESSION['pl_referer'];
$current_script  = $_SERVER['PHP_SELF'];        // ¸½ºß¼Â¹ÔÃæ¤Î¥¹¥¯¥ê¥×¥ÈÌ¾¤òÊİÂ¸

/********** Logic Start **********/
///////////// ¥µ¥¤¥È¥á¥Ë¥å¡¼ On / Off 
if ($_SESSION['site_view'] == 'on') {
    $site_view = 'MenuOFF';
} else {
    $site_view = 'MenuON';
}

//////////////// ¥µ¥¤¥È¥á¥Ë¥å¡¼¤Î£Õ£Ò£ÌÀßÄê & JavaScriptÀ¸À®
$menu_site_url = 'http:' . WEB_HOST . 'menu_site.php';
$menu_site_script =
"<script language='JavaScript'>
<!--
    parent.menu_site.location = '$menu_site_url';
// -->
</script>";
$menu_site_script = "";         // ·î¼¡¥á¥Ë¥å¡¼¤Î¤¿¤á»È¤ï¤Ê¤¤

//////////// ¥¿¥¤¥È¥ë¤ÎÆüÉÕ¡¦»ş´ÖÀßÄê
$today = date("Y/m/d H:i:s");

//////////// JavaScript Stylesheet File ¤òÉ¬¤ºÆÉ¤ß¹ş¤Ş¤»¤ë
$uniq = uniqid("target");

///// ´ü¡¦·î¤Î¼èÆÀ
$ki = Ym_to_tnk($_SESSION['2ki_ym']);
$tuki = substr($_SESSION['2ki_ym'],4,2);
$tuki = $tuki + 1 -1;   // ¿ôÃÍ¥Ç¡¼¥¿¤ËÊÑ´¹(09¤ò9¤Ë¤·¤¿¤¤¤¿¤á)¥­¥ã¥¹¥È¤Ç¤â¤¤¤¤¤Î¤À¤¬

///// ´ü¡¦È¾´ü¤Î¼èÆÀ
$tuki_chk = substr($_SESSION['2ki_ym'],4,2);
if ($tuki_chk == 3) {
    $hanki = '£´';
} elseif ($tuki_chk == 6) {
    $hanki = '£±';
} elseif ($tuki_chk == 9) {
    $hanki = '£²';
} elseif ($tuki_chk == 12) {
    $hanki = '£³';
}

//////////// ¥¿¥¤¥È¥ëÌ¾(¥½¡¼¥¹¤Î¥¿¥¤¥È¥ëÌ¾¤È¥Õ¥©¡¼¥à¤Î¥¿¥¤¥È¥ëÌ¾)
$menu->set_title("Âè {$ki} ´ü¡¡Âè{$hanki}»ÍÈ¾´ü¡¡¸º²Á½şµÑ»ñ»º¤ª¤è¤Ó¸º²Á½şµÑÈñ¤ÎÌÀºÙ½ñ");

///// ÂĞ¾İÅö·î
$yyyymm = $_SESSION['2ki_ym'];
$ki     = Ym_to_tnk($_SESSION['2ki_ym']);
///// TNK´ü ¢ª NK´ü¤ØÊÑ´¹
$nk_ki   = $ki + 44;
///// ÂĞ¾İÁ°·î
if (substr($yyyymm,4,2)!=01) {
    $p1_ym = $yyyymm - 1;
} else {
    $p1_ym = $yyyymm - 100;
    $p1_ym = $p1_ym + 11;
}
///// ÂĞ¾İÁ°¡¹·î ¤³¤ì¤Ï¤È¤ê¤¢¤¨¤º»È¤ï¤Ê¤¤
if (substr($p1_ym,4,2)!=01) {
    $p2_ym = $p1_ym - 1;
} else {
    $p2_ym = $p1_ym - 100;
    $p2_ym = $p2_ym + 11;
}
///// Á°´üËö Ç¯·î¤Î»»½Ğ
$yyyy = substr($yyyymm, 0,4);
$mm   = substr($yyyymm, 4,2);
if (($mm >= 1) && ($mm <= 3)) {
    $yyyy = ($yyyy - 1);
}
$pre_end_ym = $yyyy . "03";     // ´ü½éÇ¯·î

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

///// É½¼¨Ã±°Ì¤òÀßÄê¼èÆÀ
if (isset($_POST['state_tani'])) {
    $_SESSION['state_tani'] = $_POST['state_tani'];
    $tani = $_SESSION['state_tani'];
} elseif (isset($_SESSION['state_tani'])) {
    $tani = $_SESSION['state_tani'];
} else {
    $tani = 1;        // ½é´üÃÍ É½¼¨Ã±°Ì É´Ëü±ß
    $_SESSION['state_tani'] = $tani;
}
///// É½¼¨ ¾®¿ôÉô·å¿ô ÀßÄê¼èÆÀ
if (isset($_POST['state_keta'])) {
    $_SESSION['state_keta'] = $_POST['state_keta'];
    $keta = $_SESSION['state_keta'];
} elseif (isset($_SESSION['state_keta'])) {
    $keta = $_SESSION['state_keta'];
} else {
    $keta = 0;          // ½é´üÃÍ ¾®¿ôÅÀ°Ê²¼·å¿ô
    $_SESSION['state_keta'] = $keta;
}
// $keta = 1;              // Èæ³ÓÃª²·É½¤Ç¤Ï¾®¿ôÅÀ°Ê²¼¤Ï1¤Ë¸ÇÄê¤·¤è¤¦¤È»×¤Ã¤¿¤¬¤·¤Ê¤¤¡£


// ¥Ç¡¼¥¿¼èÆÀ

// ·úÊª
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
$month[0][0] = '·úÊª¼èÆÀ²Á³Û´ü¼ó»Ä¹â';
$month[0][1] = $tate_shu_kishu_kin;
$month[1][0] = '·úÊª¼èÆÀ²Á³Û´üÃæÁı²Ã';
$month[1][1] = $tate_shu_zou_kin;
$month[2][0] = '·úÊª¼èÆÀ²Á³Û´üÃæ¸º¾¯';
$month[2][1] = $tate_shu_gen_kin;
$month[3][0] = '·úÊª´ü¼óÄ¢Êí²Á³Û';
$month[3][1] = $tate_kishu_chou_kin;
$month[4][0] = '·úÊªÎß·×³Û´üÃæ¸º¾¯';
$month[4][1] = $tate_rui_gen_kin;
$month[5][0] = '·úÊªÎß·×³ÛÅö´ü½şµÑ³Û';
$month[5][1] = $tate_rui_syo_kin;

// ·úÊªÉÕÂ°ÀßÈ÷
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
$month[6][0]  = '·úÊªÉíÂ°ÀßÈ÷¼èÆÀ²Á³Û´ü¼ó»Ä¹â';
$month[6][1]  = $tatef_shu_kishu_kin;
$month[7][0]  = '·úÊªÉíÂ°ÀßÈ÷¼èÆÀ²Á³Û´üÃæÁı²Ã';
$month[7][1]  = $tatef_shu_zou_kin;
$month[8][0]  = '·úÊªÉíÂ°ÀßÈ÷¼èÆÀ²Á³Û´üÃæ¸º¾¯';
$month[8][1]  = $tatef_shu_gen_kin;
$month[9][0]  = '·úÊªÉíÂ°ÀßÈ÷´ü¼óÄ¢Êí²Á³Û';
$month[9][1]  = $tatef_kishu_chou_kin;
$month[10][0] = '·úÊªÉíÂ°ÀßÈ÷Îß·×³Û´üÃæ¸º¾¯';
$month[10][1] = $tatef_rui_gen_kin;
$month[11][0] = '·úÊªÉíÂ°ÀßÈ÷Îß·×³ÛÅö´ü½şµÑ³Û';
$month[11][1] = $tatef_rui_syo_kin;

// ¹½ÃÛÊª
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
$month[12][0] = '¹½ÃÛÊª¼èÆÀ²Á³Û´ü¼ó»Ä¹â';
$month[12][1] = $kou_shu_kishu_kin;
$month[13][0] = '¹½ÃÛÊª¼èÆÀ²Á³Û´üÃæÁı²Ã';
$month[13][1] = $kou_shu_zou_kin;
$month[14][0] = '¹½ÃÛÊª¼èÆÀ²Á³Û´üÃæ¸º¾¯';
$month[14][1] = $kou_shu_gen_kin;
$month[15][0] = '¹½ÃÛÊª´ü¼óÄ¢Êí²Á³Û';
$month[15][1] = $kou_kishu_chou_kin;
$month[16][0] = '¹½ÃÛÊªÎß·×³Û´üÃæ¸º¾¯';
$month[16][1] = $kou_rui_gen_kin;
$month[17][0] = '¹½ÃÛÊªÎß·×³ÛÅö´ü½şµÑ³Û';
$month[17][1] = $kou_rui_syo_kin;

// µ¡³£ÁõÃÖ
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
$month[18][0] = 'µ¡³£ÁõÃÖ¼èÆÀ²Á³Û´ü¼ó»Ä¹â';
$month[18][1] = $kikai_shu_kishu_kin;
$month[19][0] = 'µ¡³£ÁõÃÖ¼èÆÀ²Á³Û´üÃæÁı²Ã';
$month[19][1] = $kikai_shu_zou_kin;
$month[20][0] = 'µ¡³£ÁõÃÖ¼èÆÀ²Á³Û´üÃæ¸º¾¯';
$month[20][1] = $kikai_shu_gen_kin;
$month[21][0] = 'µ¡³£ÁõÃÖ´ü¼óÄ¢Êí²Á³Û';
$month[21][1] = $kikai_kishu_chou_kin;
$month[22][0] = 'µ¡³£ÁõÃÖÎß·×³Û´üÃæ¸º¾¯';
$month[22][1] = $kikai_rui_gen_kin;
$month[23][0] = 'µ¡³£ÁõÃÖÎß·×³ÛÅö´ü½şµÑ³Û';
$month[23][1] = $kikai_rui_syo_kin;

// ¼ÖíÑ±¿ÈÂ¶ñ
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
$month[24][0] = '¼ÖíÑ±¿ÈÂ¶ñ¼èÆÀ²Á³Û´ü¼ó»Ä¹â';
$month[24][1] = $syaryo_shu_kishu_kin;
$month[25][0] = '¼ÖíÑ±¿ÈÂ¶ñ¼èÆÀ²Á³Û´üÃæÁı²Ã';
$month[25][1] = $syaryo_shu_zou_kin;
$month[26][0] = '¼ÖíÑ±¿ÈÂ¶ñ¼èÆÀ²Á³Û´üÃæ¸º¾¯';
$month[26][1] = $syaryo_shu_gen_kin;
$month[27][0] = '¼ÖíÑ±¿ÈÂ¶ñ´ü¼óÄ¢Êí²Á³Û';
$month[27][1] = $syaryo_kishu_chou_kin;
$month[28][0] = '¼ÖíÑ±¿ÈÂ¶ñÎß·×³Û´üÃæ¸º¾¯';
$month[28][1] = $syaryo_rui_gen_kin;
$month[29][0] = '¼ÖíÑ±¿ÈÂ¶ñÎß·×³ÛÅö´ü½şµÑ³Û';
$month[29][1] = $syaryo_rui_syo_kin;

// ´ï¶ñ¹©¶ñ
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
$month[30][0] = '´ï¶ñ¹©¶ñ¼èÆÀ²Á³Û´ü¼ó»Ä¹â';
$month[30][1] = $kigu_shu_kishu_kin;
$month[31][0] = '´ï¶ñ¹©¶ñ¼èÆÀ²Á³Û´üÃæÁı²Ã';
$month[31][1] = $kigu_shu_zou_kin;
$month[32][0] = '´ï¶ñ¹©¶ñ¼èÆÀ²Á³Û´üÃæ¸º¾¯';
$month[32][1] = $kigu_shu_gen_kin;
$month[33][0] = '´ï¶ñ¹©¶ñ´ü¼óÄ¢Êí²Á³Û';
$month[33][1] = $kigu_kishu_chou_kin;
$month[34][0] = '´ï¶ñ¹©¶ñÎß·×³Û´üÃæ¸º¾¯';
$month[34][1] = $kigu_rui_gen_kin;
$month[35][0] = '´ï¶ñ¹©¶ñÎß·×³ÛÅö´ü½şµÑ³Û';
$month[35][1] = $kigu_rui_syo_kin;

// ½º´ïÈ÷ÉÊ
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
$month[36][0] = '½º´ïÈ÷ÉÊ¼èÆÀ²Á³Û´ü¼ó»Ä¹â';
$month[36][1] = $bihin_shu_kishu_kin;
$month[37][0] = '½º´ïÈ÷ÉÊ¼èÆÀ²Á³Û´üÃæÁı²Ã';
$month[37][1] = $bihin_shu_zou_kin;
$month[38][0] = '½º´ïÈ÷ÉÊ¼èÆÀ²Á³Û´üÃæ¸º¾¯';
$month[38][1] = $bihin_shu_gen_kin;
$month[39][0] = '½º´ïÈ÷ÉÊ´ü¼óÄ¢Êí²Á³Û';
$month[39][1] = $bihin_kishu_chou_kin;
$month[40][0] = '½º´ïÈ÷ÉÊÎß·×³Û´üÃæ¸º¾¯';
$month[40][1] = $bihin_rui_gen_kin;
$month[41][0] = '½º´ïÈ÷ÉÊÎß·×³ÛÅö´ü½şµÑ³Û';
$month[41][1] = $bihin_rui_syo_kin;

// ¥ê¡¼¥¹»ñ»º
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
$month[42][0] = '¥ê¡¼¥¹»ñ»º¼èÆÀ²Á³Û´ü¼ó»Ä¹â';
$month[42][1] = $lease_shu_kishu_kin;
$month[43][0] = '¥ê¡¼¥¹»ñ»º¼èÆÀ²Á³Û´üÃæÁı²Ã';
$month[43][1] = $lease_shu_zou_kin;
$month[44][0] = '¥ê¡¼¥¹»ñ»º¼èÆÀ²Á³Û´üÃæ¸º¾¯';
$month[44][1] = $lease_shu_gen_kin;
$month[45][0] = '¥ê¡¼¥¹»ñ»º´ü¼óÄ¢Êí²Á³Û';
$month[45][1] = $lease_kishu_chou_kin;
$month[46][0] = '¥ê¡¼¥¹»ñ»ºÎß·×³Û´üÃæ¸º¾¯';
$month[46][1] = $lease_rui_gen_kin;
$month[47][0] = '¥ê¡¼¥¹»ñ»ºÎß·×³ÛÅö´ü½şµÑ³Û';
$month[47][1] = $lease_rui_syo_kin;

// ÅÅÏÃ²ÃÆş¸¢
$res   = array();
$field = array();
$rows  = array();
$note = 'ÅÅÏÃ´ü¼ó»Ä¹â';
$query = sprintf("SELECT rep_kin FROM financial_report_data WHERE rep_ymd=%d and rep_note='%s'", $yyyymm, $note);
if ($rows=getResultWithField2($query, $field, $res) <= 0) {
    $denwa_shu_kishu_kin = 0;
} else {
    $denwa_shu_kishu_kin = $res[0][0];
}
$res   = array();
$field = array();
$rows  = array();
$note = 'ÅÅÏÃ´üÃæÁı²Ã';
$query = sprintf("SELECT rep_kin FROM financial_report_data WHERE rep_ymd=%d and rep_note='%s'", $yyyymm, $note);
if ($rows=getResultWithField2($query, $field, $res) <= 0) {
    $denwa_shu_zou_kin = 0;
} else {
    $denwa_shu_zou_kin = $res[0][0];
}
$res   = array();
$field = array();
$rows  = array();
$note = 'ÅÅÏÃ´üÃæ¸º¾¯';
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
$month[48][0] = 'ÅÅÏÃ²ÃÆş¸¢¼èÆÀ²Á³Û´ü¼ó»Ä¹â';
$month[48][1] = $denwa_shu_kishu_kin;
$month[49][0] = 'ÅÅÏÃ²ÃÆş¸¢¼èÆÀ²Á³Û´üÃæÁı²Ã';
$month[49][1] = $denwa_shu_zou_kin;
$month[50][0] = 'ÅÅÏÃ²ÃÆş¸¢¼èÆÀ²Á³Û´üÃæ¸º¾¯';
$month[50][1] = $denwa_shu_gen_kin;
$month[51][0] = 'ÅÅÏÃ²ÃÆş¸¢´ü¼óÄ¢Êí²Á³Û';
$month[51][1] = $denwa_kishu_chou_kin;
$month[52][0] = 'ÅÅÏÃ²ÃÆş¸¢Îß·×³Û´üÃæ¸º¾¯';
$month[52][1] = $denwa_rui_gen_kin;
$month[53][0] = 'ÅÅÏÃ²ÃÆş¸¢Îß·×³ÛÅö´ü½şµÑ³Û';
$month[53][1] = $denwa_rui_syo_kin;

// »ÜÀßÍøÍÑ¸¢
$res   = array();
$field = array();
$rows  = array();
$note = '»ÜÀß´ü¼ó»Ä¹â';
$query = sprintf("SELECT rep_kin FROM financial_report_data WHERE rep_ymd=%d and rep_note='%s'", $yyyymm, $note);
if ($rows=getResultWithField2($query, $field, $res) <= 0) {
    $sisetu_shu_kishu_kin = 0;
} else {
    $sisetu_shu_kishu_kin = $res[0][0];
}
$res   = array();
$field = array();
$rows  = array();
$note = '»ÜÀß´üÃæÁı²Ã';
$query = sprintf("SELECT rep_kin FROM financial_report_data WHERE rep_ymd=%d and rep_note='%s'", $yyyymm, $note);
if ($rows=getResultWithField2($query, $field, $res) <= 0) {
    $sisetu_shu_zou_kin = 0;
} else {
    $sisetu_shu_zou_kin = $res[0][0];
}
$res   = array();
$field = array();
$rows  = array();
$note = '»ÜÀß´üÃæ¸º¾¯';
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
$month[54][0] = '»ÜÀßÍøÍÑ¸¢¼èÆÀ²Á³Û´ü¼ó»Ä¹â';
$month[54][1] = $sisetu_shu_kishu_kin;
$month[55][0] = '»ÜÀßÍøÍÑ¸¢¼èÆÀ²Á³Û´üÃæÁı²Ã';
$month[55][1] = $sisetu_shu_zou_kin;
$month[56][0] = '»ÜÀßÍøÍÑ¸¢¼èÆÀ²Á³Û´üÃæ¸º¾¯';
$month[56][1] = $sisetu_shu_gen_kin;
$month[57][0] = '»ÜÀßÍøÍÑ¸¢´ü¼óÄ¢Êí²Á³Û';
$month[57][1] = $sisetu_kishu_chou_kin;
$month[58][0] = '»ÜÀßÍøÍÑ¸¢Îß·×³Û´üÃæ¸º¾¯';
$month[58][1] = $sisetu_rui_gen_kin;
$month[59][0] = '»ÜÀßÍøÍÑ¸¢Îß·×³ÛÅö´ü½şµÑ³Û';
$month[59][1] = $sisetu_rui_syo_kin;

// ¥½¥Õ¥È¥¦¥§¥¢
$res   = array();
$field = array();
$rows  = array();
$note = '¥½¥Õ¥È´ü¼ó»Ä¹â';
$query = sprintf("SELECT rep_kin FROM financial_report_data WHERE rep_ymd=%d and rep_note='%s'", $yyyymm, $note);
if ($rows=getResultWithField2($query, $field, $res) <= 0) {
    $soft_shu_kishu_kin = 0;
} else {
    $soft_shu_kishu_kin = $res[0][0];
}
$res   = array();
$field = array();
$rows  = array();
$note = '¥½¥Õ¥È´üÃæÁı²Ã';
$query = sprintf("SELECT rep_kin FROM financial_report_data WHERE rep_ymd=%d and rep_note='%s'", $yyyymm, $note);
if ($rows=getResultWithField2($query, $field, $res) <= 0) {
    $soft_shu_zou_kin = 0;
} else {
    $soft_shu_zou_kin = $res[0][0];
}
$res   = array();
$field = array();
$rows  = array();
$note = '¥½¥Õ¥È´üÃæ¸º¾¯';
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
$month[60][0] = '¥½¥Õ¥È¥¦¥§¥¢¼èÆÀ²Á³Û´ü¼ó»Ä¹â';
$month[60][1] = $soft_shu_kishu_kin;
$month[61][0] = '¥½¥Õ¥È¥¦¥§¥¢¼èÆÀ²Á³Û´üÃæÁı²Ã';
$month[61][1] = $soft_shu_zou_kin;
$month[62][0] = '¥½¥Õ¥È¥¦¥§¥¢¼èÆÀ²Á³Û´üÃæ¸º¾¯';
$month[62][1] = $soft_shu_gen_kin;
$month[63][0] = '¥½¥Õ¥È¥¦¥§¥¢´ü¼óÄ¢Êí²Á³Û';
$month[63][1] = $soft_kishu_chou_kin;
$month[64][0] = '¥½¥Õ¥È¥¦¥§¥¢Îß·×³Û´üÃæ¸º¾¯';
$month[64][1] = $soft_rui_gen_kin;
$month[65][0] = '¥½¥Õ¥È¥¦¥§¥¢Îß·×³ÛÅö´ü½şµÑ³Û';
$month[65][1] = $soft_rui_syo_kin;

///// act_comp_invent_history ¤è¤ê¥Ç¡¼¥¿¼èÆÀ
    ///// Åö·î
/*
$month = array();
$query = "select item, kin from act_state_depreciation_history where state_ym=$yyyymm";
if (($rows = getResult2($query, $month)) <= 0) {
    $_SESSION['s_sysmsg'] = sprintf("¸º²Á½şµÑÈñÌÀºÙÉ½¤Î¥Ç¡¼¥¿¤Ê¤·¡ª<br>Âè %d´ü Âè%s»ÍÈ¾´ü",$ki,$hanki);
    header("Location: $url_referer");
    exit();
} else {
*/
    $rows = count($month);
    ///// item ¤ÎÌ¾Á°¤È¶â³Û¤ò»ØÄê¤ÎÃ±°Ì¤È¾¯¿ô·å¿ô¤Ç¥Ï¥Ã¥·¥å¤ØÂåÆş
    for ($r=0; $r<$rows; $r++) {
        $month["{$month[$r][0]}"] = Uround($month[$r][1] / $tani, $keta);
    }
    /////////////////////////////////////////////////////////////////////// ¼èÆÀ²Á³Û´ü¼ó»Ä¹â START
    ///// ³Æ¶â³Û¤ò£³·å¥«¥ó¥Ş¤Ç¥Ï¥Ã¥·¥å¤ØÂåÆş
    $tbody['tbody_shutoku_kishu_tate']   = number_format($month['·úÊª¼èÆÀ²Á³Û´ü¼ó»Ä¹â'], $keta);
    $tbody['tbody_shutoku_kishu_fuzoku'] = number_format($month['·úÊªÉíÂ°ÀßÈ÷¼èÆÀ²Á³Û´ü¼ó»Ä¹â'], $keta);
    $tbody['tbody_shutoku_kishu_kouti']  = number_format($month['¹½ÃÛÊª¼èÆÀ²Á³Û´ü¼ó»Ä¹â']  , $keta);
    $tbody['tbody_shutoku_kishu_kikai']  = number_format($month['µ¡³£ÁõÃÖ¼èÆÀ²Á³Û´ü¼ó»Ä¹â'], $keta);
    $tbody['tbody_shutoku_kishu_sharyo'] = number_format($month['¼ÖíÑ±¿ÈÂ¶ñ¼èÆÀ²Á³Û´ü¼ó»Ä¹â'], $keta);
    $tbody['tbody_shutoku_kishu_kigu']   = number_format($month['´ï¶ñ¹©¶ñ¼èÆÀ²Á³Û´ü¼ó»Ä¹â'], $keta);
    $tbody['tbody_shutoku_kishu_jyuki']  = number_format($month['½º´ïÈ÷ÉÊ¼èÆÀ²Á³Û´ü¼ó»Ä¹â'], $keta);
    $tbody['tbody_shutoku_kishu_lease']  = number_format($month['¥ê¡¼¥¹»ñ»º¼èÆÀ²Á³Û´ü¼ó»Ä¹â'], $keta);
    $tbody['tbody_shutoku_kishu_denwa']  = number_format($month['ÅÅÏÃ²ÃÆş¸¢¼èÆÀ²Á³Û´ü¼ó»Ä¹â'], $keta);
    $tbody['tbody_shutoku_kishu_shise']  = number_format($month['»ÜÀßÍøÍÑ¸¢¼èÆÀ²Á³Û´ü¼ó»Ä¹â'], $keta);
    $tbody['tbody_shutoku_kishu_soft']   = number_format($month['¥½¥Õ¥È¥¦¥§¥¢¼èÆÀ²Á³Û´ü¼ó»Ä¹â'], $keta);
    ///// ·úÊª¹ç·×¡¢¹©¶ñ´ï¶ñÈ÷ÉÊ·×¡¢Í­·Á¹ç·×¡¢Ìµ·Á¹ç·×¡¢Áí¹ç·×¤ò·×»»
    $total_shutoku_kishu_tate  = $month['·úÊª¼èÆÀ²Á³Û´ü¼ó»Ä¹â'] + $month['·úÊªÉíÂ°ÀßÈ÷¼èÆÀ²Á³Û´ü¼ó»Ä¹â'];
    $total_shutoku_kishu_kougu = $month['´ï¶ñ¹©¶ñ¼èÆÀ²Á³Û´ü¼ó»Ä¹â'] + $month['½º´ïÈ÷ÉÊ¼èÆÀ²Á³Û´ü¼ó»Ä¹â'];
    $total_shutoku_kishu_yukei = $total_shutoku_kishu_tate + $month['¹½ÃÛÊª¼èÆÀ²Á³Û´ü¼ó»Ä¹â'] + $month['µ¡³£ÁõÃÖ¼èÆÀ²Á³Û´ü¼ó»Ä¹â'] + 
                                 $month['¼ÖíÑ±¿ÈÂ¶ñ¼èÆÀ²Á³Û´ü¼ó»Ä¹â'] + $total_shutoku_kishu_kougu + $month['¥ê¡¼¥¹»ñ»º¼èÆÀ²Á³Û´ü¼ó»Ä¹â'];
    $total_shutoku_kishu_mukei = $month['ÅÅÏÃ²ÃÆş¸¢¼èÆÀ²Á³Û´ü¼ó»Ä¹â'] + $month['»ÜÀßÍøÍÑ¸¢¼èÆÀ²Á³Û´ü¼ó»Ä¹â'] + $month['¥½¥Õ¥È¥¦¥§¥¢¼èÆÀ²Á³Û´ü¼ó»Ä¹â'];
    $total_shutoku_kishu_all   = $total_shutoku_kishu_yukei + $total_shutoku_kishu_mukei;
    ///// ·×»»·ë²Ì¤ò¥Ï¥Ã¥·¥å¤ØÂåÆş
    $tbody['tbody_shutoku_kishu_tate_total']  = number_format($total_shutoku_kishu_tate, $keta);
    $tbody['tbody_shutoku_kishu_kougu_total'] = number_format($total_shutoku_kishu_kougu, $keta);
    $tbody['tbody_shutoku_kishu_yukei_total'] = number_format($total_shutoku_kishu_yukei, $keta);
    $tbody['tbody_shutoku_kishu_mukei_total'] = number_format($total_shutoku_kishu_mukei, $keta);
    $tbody['tbody_shutoku_kishu_all']         = number_format($total_shutoku_kishu_all, $keta);
    /////////////////////////////////////////////////////////////////////// ¼èÆÀ²Á³Û´ü¼ó»Ä¹â END
    
    /////////////////////////////////////////////////////////////////////// ¼èÆÀ²Á³Û´üÃæÁı²Ã START
    ///// ³Æ¶â³Û¤ò£³·å¥«¥ó¥Ş¤Ç¥Ï¥Ã¥·¥å¤ØÂåÆş
    $tbody['tbody_shutoku_zou_tate']   = number_format($month['·úÊª¼èÆÀ²Á³Û´üÃæÁı²Ã'], $keta);
    $tbody['tbody_shutoku_zou_fuzoku'] = number_format($month['·úÊªÉíÂ°ÀßÈ÷¼èÆÀ²Á³Û´üÃæÁı²Ã'], $keta);
    $tbody['tbody_shutoku_zou_kouti']  = number_format($month['¹½ÃÛÊª¼èÆÀ²Á³Û´üÃæÁı²Ã']  , $keta);
    $tbody['tbody_shutoku_zou_kikai']  = number_format($month['µ¡³£ÁõÃÖ¼èÆÀ²Á³Û´üÃæÁı²Ã'], $keta);
    $tbody['tbody_shutoku_zou_sharyo'] = number_format($month['¼ÖíÑ±¿ÈÂ¶ñ¼èÆÀ²Á³Û´üÃæÁı²Ã'], $keta);
    $tbody['tbody_shutoku_zou_kigu']   = number_format($month['´ï¶ñ¹©¶ñ¼èÆÀ²Á³Û´üÃæÁı²Ã'], $keta);
    $tbody['tbody_shutoku_zou_jyuki']  = number_format($month['½º´ïÈ÷ÉÊ¼èÆÀ²Á³Û´üÃæÁı²Ã'], $keta);
    $tbody['tbody_shutoku_zou_lease']  = number_format($month['¥ê¡¼¥¹»ñ»º¼èÆÀ²Á³Û´üÃæÁı²Ã'], $keta);
    $tbody['tbody_shutoku_zou_denwa']  = number_format($month['ÅÅÏÃ²ÃÆş¸¢¼èÆÀ²Á³Û´üÃæÁı²Ã'], $keta);
    $tbody['tbody_shutoku_zou_shise']  = number_format($month['»ÜÀßÍøÍÑ¸¢¼èÆÀ²Á³Û´üÃæÁı²Ã'], $keta);
    $tbody['tbody_shutoku_zou_soft']   = number_format($month['¥½¥Õ¥È¥¦¥§¥¢¼èÆÀ²Á³Û´üÃæÁı²Ã'], $keta);
    ///// ·úÊª¹ç·×¡¢¹©¶ñ´ï¶ñÈ÷ÉÊ·×¡¢Í­·Á¹ç·×¡¢Ìµ·Á¹ç·×¡¢Áí¹ç·×¤ò·×»»
    $total_shutoku_zou_tate  = $month['·úÊª¼èÆÀ²Á³Û´üÃæÁı²Ã'] + $month['·úÊªÉíÂ°ÀßÈ÷¼èÆÀ²Á³Û´üÃæÁı²Ã'];
    $total_shutoku_zou_kougu = $month['´ï¶ñ¹©¶ñ¼èÆÀ²Á³Û´üÃæÁı²Ã'] + $month['½º´ïÈ÷ÉÊ¼èÆÀ²Á³Û´üÃæÁı²Ã'];
    $total_shutoku_zou_yukei = $total_shutoku_zou_tate + $month['¹½ÃÛÊª¼èÆÀ²Á³Û´üÃæÁı²Ã'] + $month['µ¡³£ÁõÃÖ¼èÆÀ²Á³Û´üÃæÁı²Ã'] + 
                               $month['¼ÖíÑ±¿ÈÂ¶ñ¼èÆÀ²Á³Û´üÃæÁı²Ã'] + $total_shutoku_zou_kougu + $month['¥ê¡¼¥¹»ñ»º¼èÆÀ²Á³Û´üÃæÁı²Ã'];
    $total_shutoku_zou_mukei = $month['ÅÅÏÃ²ÃÆş¸¢¼èÆÀ²Á³Û´üÃæÁı²Ã'] + $month['»ÜÀßÍøÍÑ¸¢¼èÆÀ²Á³Û´üÃæÁı²Ã'] + $month['¥½¥Õ¥È¥¦¥§¥¢¼èÆÀ²Á³Û´üÃæÁı²Ã'];
    $total_shutoku_zou_all   = $total_shutoku_zou_yukei + $total_shutoku_zou_mukei;
    ///// ·×»»·ë²Ì¤ò¥Ï¥Ã¥·¥å¤ØÂåÆş
    $tbody['tbody_shutoku_zou_tate_total']  = number_format($total_shutoku_zou_tate, $keta);
    $tbody['tbody_shutoku_zou_kougu_total'] = number_format($total_shutoku_zou_kougu, $keta);
    $tbody['tbody_shutoku_zou_yukei_total'] = number_format($total_shutoku_zou_yukei, $keta);
    $tbody['tbody_shutoku_zou_mukei_total'] = number_format($total_shutoku_zou_mukei, $keta);
    $tbody['tbody_shutoku_zou_all']         = number_format($total_shutoku_zou_all, $keta);
    /////////////////////////////////////////////////////////////////////// ¼èÆÀ²Á³Û´üÃæÁı²Ã END
    
    /////////////////////////////////////////////////////////////////////// ¼èÆÀ²Á³Û´üÃæ¸º¾¯ START
    ///// ³Æ¶â³Û¤ò£³·å¥«¥ó¥Ş¤Ç¥Ï¥Ã¥·¥å¤ØÂåÆş
    $tbody['tbody_shutoku_gen_tate']   = number_format($month['·úÊª¼èÆÀ²Á³Û´üÃæ¸º¾¯'], $keta);
    $tbody['tbody_shutoku_gen_fuzoku'] = number_format($month['·úÊªÉíÂ°ÀßÈ÷¼èÆÀ²Á³Û´üÃæ¸º¾¯'], $keta);
    $tbody['tbody_shutoku_gen_kouti']  = number_format($month['¹½ÃÛÊª¼èÆÀ²Á³Û´üÃæ¸º¾¯']  , $keta);
    $tbody['tbody_shutoku_gen_kikai']  = number_format($month['µ¡³£ÁõÃÖ¼èÆÀ²Á³Û´üÃæ¸º¾¯'], $keta);
    $tbody['tbody_shutoku_gen_sharyo'] = number_format($month['¼ÖíÑ±¿ÈÂ¶ñ¼èÆÀ²Á³Û´üÃæ¸º¾¯'], $keta);
    $tbody['tbody_shutoku_gen_kigu']   = number_format($month['´ï¶ñ¹©¶ñ¼èÆÀ²Á³Û´üÃæ¸º¾¯'], $keta);
    $tbody['tbody_shutoku_gen_jyuki']  = number_format($month['½º´ïÈ÷ÉÊ¼èÆÀ²Á³Û´üÃæ¸º¾¯'], $keta);
    $tbody['tbody_shutoku_gen_lease']  = number_format($month['¥ê¡¼¥¹»ñ»º¼èÆÀ²Á³Û´üÃæ¸º¾¯'], $keta);
    $tbody['tbody_shutoku_gen_denwa']  = number_format($month['ÅÅÏÃ²ÃÆş¸¢¼èÆÀ²Á³Û´üÃæ¸º¾¯'], $keta);
    $tbody['tbody_shutoku_gen_shise']  = number_format($month['»ÜÀßÍøÍÑ¸¢¼èÆÀ²Á³Û´üÃæ¸º¾¯'], $keta);
    $tbody['tbody_shutoku_gen_soft']   = number_format($month['¥½¥Õ¥È¥¦¥§¥¢¼èÆÀ²Á³Û´üÃæ¸º¾¯'], $keta);
    ///// ·úÊª¹ç·×¡¢¹©¶ñ´ï¶ñÈ÷ÉÊ·×¡¢Í­·Á¹ç·×¡¢Ìµ·Á¹ç·×¡¢Áí¹ç·×¤ò·×»»
    $total_shutoku_gen_tate  = $month['·úÊª¼èÆÀ²Á³Û´üÃæ¸º¾¯'] + $month['·úÊªÉíÂ°ÀßÈ÷¼èÆÀ²Á³Û´üÃæ¸º¾¯'];
    $total_shutoku_gen_kougu = $month['´ï¶ñ¹©¶ñ¼èÆÀ²Á³Û´üÃæ¸º¾¯'] + $month['½º´ïÈ÷ÉÊ¼èÆÀ²Á³Û´üÃæ¸º¾¯'];
    $total_shutoku_gen_yukei = $total_shutoku_gen_tate + $month['¹½ÃÛÊª¼èÆÀ²Á³Û´üÃæ¸º¾¯'] + $month['µ¡³£ÁõÃÖ¼èÆÀ²Á³Û´üÃæ¸º¾¯'] + 
                               $month['¼ÖíÑ±¿ÈÂ¶ñ¼èÆÀ²Á³Û´üÃæ¸º¾¯'] + $total_shutoku_gen_kougu + $month['¥ê¡¼¥¹»ñ»º¼èÆÀ²Á³Û´üÃæ¸º¾¯'];
    $total_shutoku_gen_mukei = $month['ÅÅÏÃ²ÃÆş¸¢¼èÆÀ²Á³Û´üÃæ¸º¾¯'] + $month['»ÜÀßÍøÍÑ¸¢¼èÆÀ²Á³Û´üÃæ¸º¾¯'] + $month['¥½¥Õ¥È¥¦¥§¥¢¼èÆÀ²Á³Û´üÃæ¸º¾¯'];
    $total_shutoku_gen_all   = $total_shutoku_gen_yukei + $total_shutoku_gen_mukei;
    ///// ·×»»·ë²Ì¤ò¥Ï¥Ã¥·¥å¤ØÂåÆş
    $tbody['tbody_shutoku_gen_tate_total']  = number_format($total_shutoku_gen_tate, $keta);
    $tbody['tbody_shutoku_gen_kougu_total'] = number_format($total_shutoku_gen_kougu, $keta);
    $tbody['tbody_shutoku_gen_yukei_total'] = number_format($total_shutoku_gen_yukei, $keta);
    $tbody['tbody_shutoku_gen_mukei_total'] = number_format($total_shutoku_gen_mukei, $keta);
    $tbody['tbody_shutoku_gen_all']         = number_format($total_shutoku_gen_all, $keta);
    /////////////////////////////////////////////////////////////////////// ¼èÆÀ²Á³Û´üÃæ¸º¾¯ END
    
    /////////////////////////////////////////////////////////////////////// ¼èÆÀ²Á³Û´üËö»Ä¹â START
    ///// ³Æ´üËö»Ä¹â¤ò·×»»
    // ·úÊª¡¢·úÊªÉíÂ°ÀßÈ÷
    $tbody_shutoku_kima_tate   = $month['·úÊª¼èÆÀ²Á³Û´ü¼ó»Ä¹â'] + $month['·úÊª¼èÆÀ²Á³Û´üÃæÁı²Ã'] - $month['·úÊª¼èÆÀ²Á³Û´üÃæ¸º¾¯'];
    $tbody_shutoku_kima_fuzoku = $month['·úÊªÉíÂ°ÀßÈ÷¼èÆÀ²Á³Û´ü¼ó»Ä¹â'] + $month['·úÊªÉíÂ°ÀßÈ÷¼èÆÀ²Á³Û´üÃæÁı²Ã'] - $month['·úÊªÉíÂ°ÀßÈ÷¼èÆÀ²Á³Û´üÃæ¸º¾¯'];
    // ·úÊª¹ç·×
    $total_shutoku_kima_tate   = $tbody_shutoku_kima_tate + $tbody_shutoku_kima_fuzoku;
    // ¹½ÃÛÊª¡¢µ¡³£ÁõÃÖ¡¢¼ÖíÑ±¿ÈÂ¶ñ¡¢´ï¶ñ¹©¶ñ¡¢½º´ïÈ÷ÉÊ
    $tbody_shutoku_kima_kouti  = $month['¹½ÃÛÊª¼èÆÀ²Á³Û´ü¼ó»Ä¹â'] + $month['¹½ÃÛÊª¼èÆÀ²Á³Û´üÃæÁı²Ã'] - $month['¹½ÃÛÊª¼èÆÀ²Á³Û´üÃæ¸º¾¯'];
    $tbody_shutoku_kima_kikai  = $month['µ¡³£ÁõÃÖ¼èÆÀ²Á³Û´ü¼ó»Ä¹â'] + $month['µ¡³£ÁõÃÖ¼èÆÀ²Á³Û´üÃæÁı²Ã'] - $month['µ¡³£ÁõÃÖ¼èÆÀ²Á³Û´üÃæ¸º¾¯'];
    $tbody_shutoku_kima_sharyo = $month['¼ÖíÑ±¿ÈÂ¶ñ¼èÆÀ²Á³Û´ü¼ó»Ä¹â'] + $month['¼ÖíÑ±¿ÈÂ¶ñ¼èÆÀ²Á³Û´üÃæÁı²Ã'] - $month['¼ÖíÑ±¿ÈÂ¶ñ¼èÆÀ²Á³Û´üÃæ¸º¾¯'];
    $tbody_shutoku_kima_kigu   = $month['´ï¶ñ¹©¶ñ¼èÆÀ²Á³Û´ü¼ó»Ä¹â'] + $month['´ï¶ñ¹©¶ñ¼èÆÀ²Á³Û´üÃæÁı²Ã'] - $month['´ï¶ñ¹©¶ñ¼èÆÀ²Á³Û´üÃæ¸º¾¯'];
    $tbody_shutoku_kima_jyuki  = $month['½º´ïÈ÷ÉÊ¼èÆÀ²Á³Û´ü¼ó»Ä¹â'] + $month['½º´ïÈ÷ÉÊ¼èÆÀ²Á³Û´üÃæÁı²Ã'] - $month['½º´ïÈ÷ÉÊ¼èÆÀ²Á³Û´üÃæ¸º¾¯'];
    // ´ï¶ñ¹©¶ñ¡¢½º´ïÈ÷ÉÊ¹ç·×
    $total_shutoku_kima_kougu  = $tbody_shutoku_kima_kigu + $tbody_shutoku_kima_jyuki;
    // ¥ê¡¼¥¹»ñ»º
    $tbody_shutoku_kima_lease  = $month['¥ê¡¼¥¹»ñ»º¼èÆÀ²Á³Û´ü¼ó»Ä¹â'] + $month['¥ê¡¼¥¹»ñ»º¼èÆÀ²Á³Û´üÃæÁı²Ã'] - $month['¥ê¡¼¥¹»ñ»º¼èÆÀ²Á³Û´üÃæ¸º¾¯'];
    // Í­·Á¹ç·×
    $total_shutoku_kima_yukei  = $total_shutoku_kima_tate + $tbody_shutoku_kima_kouti + $tbody_shutoku_kima_kikai + 
                                 $tbody_shutoku_kima_sharyo + $total_shutoku_kima_kougu + $tbody_shutoku_kima_lease;
    // ÅÅÏÃ²ÃÆş¸¢¡¢»ÜÀßÍøÍÑ¸¢¡¢¥½¥Õ¥È¥¦¥§¥¢
    $tbody_shutoku_kima_denwa  = $month['ÅÅÏÃ²ÃÆş¸¢¼èÆÀ²Á³Û´ü¼ó»Ä¹â'] + $month['ÅÅÏÃ²ÃÆş¸¢¼èÆÀ²Á³Û´üÃæÁı²Ã'] - $month['ÅÅÏÃ²ÃÆş¸¢¼èÆÀ²Á³Û´üÃæ¸º¾¯'];
    $tbody_shutoku_kima_shise  = $month['»ÜÀßÍøÍÑ¸¢¼èÆÀ²Á³Û´ü¼ó»Ä¹â'] + $month['»ÜÀßÍøÍÑ¸¢¼èÆÀ²Á³Û´üÃæÁı²Ã'] - $month['»ÜÀßÍøÍÑ¸¢¼èÆÀ²Á³Û´üÃæ¸º¾¯'];
    $tbody_shutoku_kima_soft   = $month['¥½¥Õ¥È¥¦¥§¥¢¼èÆÀ²Á³Û´ü¼ó»Ä¹â'] + $month['¥½¥Õ¥È¥¦¥§¥¢¼èÆÀ²Á³Û´üÃæÁı²Ã'] - $month['¥½¥Õ¥È¥¦¥§¥¢¼èÆÀ²Á³Û´üÃæ¸º¾¯'];
    // Ìµ·Á¹ç·×
    $total_shutoku_kima_mukei  = $tbody_shutoku_kima_denwa + $tbody_shutoku_kima_shise + $tbody_shutoku_kima_soft;
    // Áí¹ç·×
    $total_shutoku_kima_all    = $total_shutoku_kima_yukei + $total_shutoku_kima_mukei;
    ///// ·×»»·ë²Ì¤ò¥Ï¥Ã¥·¥å¤ØÂåÆş
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
    /////////////////////////////////////////////////////////////////////// ¼èÆÀ²Á³Û´üËö»Ä¹â END
    
    /////////////////////////////////////////////////////////////////////// ´ü¼óÄ¢Êí²Á³Û START
    ///// ³Æ¶â³Û¤ò£³·å¥«¥ó¥Ş¤Ç¥Ï¥Ã¥·¥å¤ØÂåÆş
    $tbody['tbody_kishu_cho_tate']   = number_format($month['·úÊª´ü¼óÄ¢Êí²Á³Û'], $keta);
    $tbody['tbody_kishu_cho_fuzoku'] = number_format($month['·úÊªÉíÂ°ÀßÈ÷´ü¼óÄ¢Êí²Á³Û'], $keta);
    $tbody['tbody_kishu_cho_kouti']  = number_format($month['¹½ÃÛÊª´ü¼óÄ¢Êí²Á³Û']  , $keta);
    $tbody['tbody_kishu_cho_kikai']  = number_format($month['µ¡³£ÁõÃÖ´ü¼óÄ¢Êí²Á³Û'], $keta);
    $tbody['tbody_kishu_cho_sharyo'] = number_format($month['¼ÖíÑ±¿ÈÂ¶ñ´ü¼óÄ¢Êí²Á³Û'], $keta);
    $tbody['tbody_kishu_cho_kigu']   = number_format($month['´ï¶ñ¹©¶ñ´ü¼óÄ¢Êí²Á³Û'], $keta);
    $tbody['tbody_kishu_cho_jyuki']  = number_format($month['½º´ïÈ÷ÉÊ´ü¼óÄ¢Êí²Á³Û'], $keta);
    $tbody['tbody_kishu_cho_lease']  = number_format($month['¥ê¡¼¥¹»ñ»º´ü¼óÄ¢Êí²Á³Û'], $keta);
    $tbody['tbody_kishu_cho_denwa']  = number_format($month['ÅÅÏÃ²ÃÆş¸¢´ü¼óÄ¢Êí²Á³Û'], $keta);
    $tbody['tbody_kishu_cho_shise']  = number_format($month['»ÜÀßÍøÍÑ¸¢´ü¼óÄ¢Êí²Á³Û'], $keta);
    $tbody['tbody_kishu_cho_soft']   = number_format($month['¥½¥Õ¥È¥¦¥§¥¢´ü¼óÄ¢Êí²Á³Û'], $keta);
    ///// ·úÊª¹ç·×¡¢¹©¶ñ´ï¶ñÈ÷ÉÊ·×¡¢Í­·Á¹ç·×¡¢Ìµ·Á¹ç·×¡¢Áí¹ç·×¤ò·×»»
    $total_kishu_cho_tate  = $month['·úÊª´ü¼óÄ¢Êí²Á³Û'] + $month['·úÊªÉíÂ°ÀßÈ÷´ü¼óÄ¢Êí²Á³Û'];
    $total_kishu_cho_kougu = $month['´ï¶ñ¹©¶ñ´ü¼óÄ¢Êí²Á³Û'] + $month['½º´ïÈ÷ÉÊ´ü¼óÄ¢Êí²Á³Û'];
    $total_kishu_cho_yukei = $total_kishu_cho_tate + $month['¹½ÃÛÊª´ü¼óÄ¢Êí²Á³Û'] + $month['µ¡³£ÁõÃÖ´ü¼óÄ¢Êí²Á³Û'] + 
                             $month['¼ÖíÑ±¿ÈÂ¶ñ´ü¼óÄ¢Êí²Á³Û'] + $total_kishu_cho_kougu + $month['¥ê¡¼¥¹»ñ»º´ü¼óÄ¢Êí²Á³Û'];
    $total_kishu_cho_mukei = $month['ÅÅÏÃ²ÃÆş¸¢´ü¼óÄ¢Êí²Á³Û'] + $month['»ÜÀßÍøÍÑ¸¢´ü¼óÄ¢Êí²Á³Û'] + $month['¥½¥Õ¥È¥¦¥§¥¢´ü¼óÄ¢Êí²Á³Û'];
    $total_kishu_cho_all   = $total_kishu_cho_yukei + $total_kishu_cho_mukei;
    ///// ·×»»·ë²Ì¤ò¥Ï¥Ã¥·¥å¤ØÂåÆş
    $tbody['tbody_kishu_cho_tate_total']  = number_format($total_kishu_cho_tate, $keta);
    $tbody['tbody_kishu_cho_kougu_total'] = number_format($total_kishu_cho_kougu, $keta);
    $tbody['tbody_kishu_cho_yukei_total'] = number_format($total_kishu_cho_yukei, $keta);
    $tbody['tbody_kishu_cho_mukei_total'] = number_format($total_kishu_cho_mukei, $keta);
    $tbody['tbody_kishu_cho_all']         = number_format($total_kishu_cho_all, $keta);
    /////////////////////////////////////////////////////////////////////// ´ü¼óÄ¢Êí²Á³Û END
    
    /////////////////////////////////////////////////////////////////////// ¸º²Á½şµÑÎß·×³Û´ü¼ó»Ä¹â START
    ///// ³Æ´üËö»Ä¹â¤ò·×»»
    // ·úÊª¡¢·úÊªÉíÂ°ÀßÈ÷
    $tbody_rui_kishu_tate   = $month['·úÊª¼èÆÀ²Á³Û´ü¼ó»Ä¹â'] - $month['·úÊª´ü¼óÄ¢Êí²Á³Û'];
    $tbody_rui_kishu_fuzoku = $month['·úÊªÉíÂ°ÀßÈ÷¼èÆÀ²Á³Û´ü¼ó»Ä¹â'] - $month['·úÊªÉíÂ°ÀßÈ÷´ü¼óÄ¢Êí²Á³Û'];
    // ·úÊª¹ç·×
    $total_rui_kishu_tate   = $tbody_rui_kishu_tate + $tbody_rui_kishu_fuzoku;
    // ¹½ÃÛÊª¡¢µ¡³£ÁõÃÖ¡¢¼ÖíÑ±¿ÈÂ¶ñ¡¢´ï¶ñ¹©¶ñ¡¢½º´ïÈ÷ÉÊ
    $tbody_rui_kishu_kouti  = $month['¹½ÃÛÊª¼èÆÀ²Á³Û´ü¼ó»Ä¹â'] - $month['¹½ÃÛÊª´ü¼óÄ¢Êí²Á³Û'];
    $tbody_rui_kishu_kikai  = $month['µ¡³£ÁõÃÖ¼èÆÀ²Á³Û´ü¼ó»Ä¹â'] - $month['µ¡³£ÁõÃÖ´ü¼óÄ¢Êí²Á³Û'];
    $tbody_rui_kishu_sharyo = $month['¼ÖíÑ±¿ÈÂ¶ñ¼èÆÀ²Á³Û´ü¼ó»Ä¹â'] - $month['¼ÖíÑ±¿ÈÂ¶ñ´ü¼óÄ¢Êí²Á³Û'];
    $tbody_rui_kishu_kigu   = $month['´ï¶ñ¹©¶ñ¼èÆÀ²Á³Û´ü¼ó»Ä¹â'] - $month['´ï¶ñ¹©¶ñ´ü¼óÄ¢Êí²Á³Û'];
    $tbody_rui_kishu_jyuki  = $month['½º´ïÈ÷ÉÊ¼èÆÀ²Á³Û´ü¼ó»Ä¹â'] - $month['½º´ïÈ÷ÉÊ´ü¼óÄ¢Êí²Á³Û'];
    // ´ï¶ñ¹©¶ñ¡¢½º´ïÈ÷ÉÊ¹ç·×
    $total_rui_kishu_kougu  = $tbody_rui_kishu_kigu + $tbody_rui_kishu_jyuki;
    // ¥ê¡¼¥¹»ñ»º
    $tbody_rui_kishu_lease  = $month['¥ê¡¼¥¹»ñ»º¼èÆÀ²Á³Û´ü¼ó»Ä¹â'] - $month['¥ê¡¼¥¹»ñ»º´ü¼óÄ¢Êí²Á³Û'];
    // Í­·Á¹ç·×
    $total_rui_kishu_yukei  = $total_rui_kishu_tate + $tbody_rui_kishu_kouti + $tbody_rui_kishu_kikai + 
                              $tbody_rui_kishu_sharyo + $total_rui_kishu_kougu + $tbody_rui_kishu_lease;
    // ÅÅÏÃ²ÃÆş¸¢¡¢»ÜÀßÍøÍÑ¸¢¡¢¥½¥Õ¥È¥¦¥§¥¢
    $tbody_rui_kishu_denwa  = $month['ÅÅÏÃ²ÃÆş¸¢¼èÆÀ²Á³Û´ü¼ó»Ä¹â'] - $month['ÅÅÏÃ²ÃÆş¸¢´ü¼óÄ¢Êí²Á³Û'];
    $tbody_rui_kishu_shise  = $month['»ÜÀßÍøÍÑ¸¢¼èÆÀ²Á³Û´ü¼ó»Ä¹â'] - $month['»ÜÀßÍøÍÑ¸¢´ü¼óÄ¢Êí²Á³Û'];
    $tbody_rui_kishu_soft   = $month['¥½¥Õ¥È¥¦¥§¥¢¼èÆÀ²Á³Û´ü¼ó»Ä¹â'] - $month['¥½¥Õ¥È¥¦¥§¥¢´ü¼óÄ¢Êí²Á³Û'];
    // Ìµ·Á¹ç·×
    $total_rui_kishu_mukei  = $tbody_rui_kishu_denwa + $tbody_rui_kishu_shise + $tbody_rui_kishu_soft;
    // Áí¹ç·×
    $total_rui_kishu_all    = $total_rui_kishu_yukei + $total_rui_kishu_mukei;
    ///// ·×»»·ë²Ì¤ò¥Ï¥Ã¥·¥å¤ØÂåÆş
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
    /////////////////////////////////////////////////////////////////////// ¸º²Á½şµÑÎß·×³Û´ü¼ó»Ä¹â END
    
    /////////////////////////////////////////////////////////////////////// ¸º²Á½şµÑÎß·×³Û´üÃæ¸º¾¯ START
    ///// ³Æ¶â³Û¤ò£³·å¥«¥ó¥Ş¤Ç¥Ï¥Ã¥·¥å¤ØÂåÆş
    $tbody['tbody_rui_gen_tate']   = number_format($month['·úÊªÎß·×³Û´üÃæ¸º¾¯'], $keta);
    $tbody['tbody_rui_gen_fuzoku'] = number_format($month['·úÊªÉíÂ°ÀßÈ÷Îß·×³Û´üÃæ¸º¾¯'], $keta);
    $tbody['tbody_rui_gen_kouti']  = number_format($month['¹½ÃÛÊªÎß·×³Û´üÃæ¸º¾¯']  , $keta);
    $tbody['tbody_rui_gen_kikai']  = number_format($month['µ¡³£ÁõÃÖÎß·×³Û´üÃæ¸º¾¯'], $keta);
    $tbody['tbody_rui_gen_sharyo'] = number_format($month['¼ÖíÑ±¿ÈÂ¶ñÎß·×³Û´üÃæ¸º¾¯'], $keta);
    $tbody['tbody_rui_gen_kigu']   = number_format($month['´ï¶ñ¹©¶ñÎß·×³Û´üÃæ¸º¾¯'], $keta);
    $tbody['tbody_rui_gen_jyuki']  = number_format($month['½º´ïÈ÷ÉÊÎß·×³Û´üÃæ¸º¾¯'], $keta);
    $tbody['tbody_rui_gen_lease']  = number_format($month['¥ê¡¼¥¹»ñ»ºÎß·×³Û´üÃæ¸º¾¯'], $keta);
    $tbody['tbody_rui_gen_denwa']  = number_format($month['ÅÅÏÃ²ÃÆş¸¢Îß·×³Û´üÃæ¸º¾¯'], $keta);
    $tbody['tbody_rui_gen_shise']  = number_format($month['»ÜÀßÍøÍÑ¸¢Îß·×³Û´üÃæ¸º¾¯'], $keta);
    $tbody['tbody_rui_gen_soft']   = number_format($month['¥½¥Õ¥È¥¦¥§¥¢Îß·×³Û´üÃæ¸º¾¯'], $keta);
    ///// ·úÊª¹ç·×¡¢¹©¶ñ´ï¶ñÈ÷ÉÊ·×¡¢Í­·Á¹ç·×¡¢Ìµ·Á¹ç·×¡¢Áí¹ç·×¤ò·×»»
    $total_rui_gen_tate  = $month['·úÊªÎß·×³Û´üÃæ¸º¾¯'] + $month['·úÊªÉíÂ°ÀßÈ÷Îß·×³Û´üÃæ¸º¾¯'];
    $total_rui_gen_kougu = $month['´ï¶ñ¹©¶ñÎß·×³Û´üÃæ¸º¾¯'] + $month['½º´ïÈ÷ÉÊÎß·×³Û´üÃæ¸º¾¯'];
    $total_rui_gen_yukei = $total_rui_gen_tate + $month['¹½ÃÛÊªÎß·×³Û´üÃæ¸º¾¯'] + $month['µ¡³£ÁõÃÖÎß·×³Û´üÃæ¸º¾¯'] + 
                           $month['¼ÖíÑ±¿ÈÂ¶ñÎß·×³Û´üÃæ¸º¾¯'] + $total_rui_gen_kougu + $month['¥ê¡¼¥¹»ñ»ºÎß·×³Û´üÃæ¸º¾¯'];
    $total_rui_gen_mukei = $month['ÅÅÏÃ²ÃÆş¸¢Îß·×³Û´üÃæ¸º¾¯'] + $month['»ÜÀßÍøÍÑ¸¢Îß·×³Û´üÃæ¸º¾¯'] + $month['¥½¥Õ¥È¥¦¥§¥¢Îß·×³Û´üÃæ¸º¾¯'];
    $total_rui_gen_all   = $total_rui_gen_yukei + $total_rui_gen_mukei;
    ///// ·×»»·ë²Ì¤ò¥Ï¥Ã¥·¥å¤ØÂåÆş
    $tbody['tbody_rui_gen_tate_total']  = number_format($total_rui_gen_tate, $keta);
    $tbody['tbody_rui_gen_kougu_total'] = number_format($total_rui_gen_kougu, $keta);
    $tbody['tbody_rui_gen_yukei_total'] = number_format($total_rui_gen_yukei, $keta);
    $tbody['tbody_rui_gen_mukei_total'] = number_format($total_rui_gen_mukei, $keta);
    $tbody['tbody_rui_gen_all']         = number_format($total_rui_gen_all, $keta);
    /////////////////////////////////////////////////////////////////////// ¸º²Á½şµÑÎß·×³Û´üÃæ¸º¾¯ END
    
    /////////////////////////////////////////////////////////////////////// ¸º²Á½şµÑÎß·×³ÛÅö´ü½şµÑ³Û START
    ///// ³Æ¶â³Û¤ò£³·å¥«¥ó¥Ş¤Ç¥Ï¥Ã¥·¥å¤ØÂåÆş
    $tbody['tbody_rui_syo_tate']   = number_format($month['·úÊªÎß·×³ÛÅö´ü½şµÑ³Û'], $keta);
    $tbody['tbody_rui_syo_fuzoku'] = number_format($month['·úÊªÉíÂ°ÀßÈ÷Îß·×³ÛÅö´ü½şµÑ³Û'], $keta);
    $tbody['tbody_rui_syo_kouti']  = number_format($month['¹½ÃÛÊªÎß·×³ÛÅö´ü½şµÑ³Û']  , $keta);
    $tbody['tbody_rui_syo_kikai']  = number_format($month['µ¡³£ÁõÃÖÎß·×³ÛÅö´ü½şµÑ³Û'], $keta);
    $tbody['tbody_rui_syo_sharyo'] = number_format($month['¼ÖíÑ±¿ÈÂ¶ñÎß·×³ÛÅö´ü½şµÑ³Û'], $keta);
    $tbody['tbody_rui_syo_kigu']   = number_format($month['´ï¶ñ¹©¶ñÎß·×³ÛÅö´ü½şµÑ³Û'], $keta);
    $tbody['tbody_rui_syo_jyuki']  = number_format($month['½º´ïÈ÷ÉÊÎß·×³ÛÅö´ü½şµÑ³Û'], $keta);
    $tbody['tbody_rui_syo_lease']  = number_format($month['¥ê¡¼¥¹»ñ»ºÎß·×³ÛÅö´ü½şµÑ³Û'], $keta);
    $tbody['tbody_rui_syo_denwa']  = number_format($month['ÅÅÏÃ²ÃÆş¸¢Îß·×³ÛÅö´ü½şµÑ³Û'], $keta);
    $tbody['tbody_rui_syo_shise']  = number_format($month['»ÜÀßÍøÍÑ¸¢Îß·×³ÛÅö´ü½şµÑ³Û'], $keta);
    $tbody['tbody_rui_syo_soft']   = number_format($month['¥½¥Õ¥È¥¦¥§¥¢Îß·×³ÛÅö´ü½şµÑ³Û'], $keta);
    ///// ·úÊª¹ç·×¡¢¹©¶ñ´ï¶ñÈ÷ÉÊ·×¡¢Í­·Á¹ç·×¡¢Ìµ·Á¹ç·×¡¢Áí¹ç·×¤ò·×»»
    $total_rui_syo_tate  = $month['·úÊªÎß·×³ÛÅö´ü½şµÑ³Û'] + $month['·úÊªÉíÂ°ÀßÈ÷Îß·×³ÛÅö´ü½şµÑ³Û'];
    $total_rui_syo_kougu = $month['´ï¶ñ¹©¶ñÎß·×³ÛÅö´ü½şµÑ³Û'] + $month['½º´ïÈ÷ÉÊÎß·×³ÛÅö´ü½şµÑ³Û'];
    $total_rui_syo_yukei = $total_rui_syo_tate + $month['¹½ÃÛÊªÎß·×³ÛÅö´ü½şµÑ³Û'] + $month['µ¡³£ÁõÃÖÎß·×³ÛÅö´ü½şµÑ³Û'] + 
                           $month['¼ÖíÑ±¿ÈÂ¶ñÎß·×³ÛÅö´ü½şµÑ³Û'] + $total_rui_syo_kougu + $month['¥ê¡¼¥¹»ñ»ºÎß·×³ÛÅö´ü½şµÑ³Û'];
    $total_rui_syo_mukei = $month['ÅÅÏÃ²ÃÆş¸¢Îß·×³ÛÅö´ü½şµÑ³Û'] + $month['»ÜÀßÍøÍÑ¸¢Îß·×³ÛÅö´ü½şµÑ³Û'] + $month['¥½¥Õ¥È¥¦¥§¥¢Îß·×³ÛÅö´ü½şµÑ³Û'];
    $total_rui_syo_all   = $total_rui_syo_yukei + $total_rui_syo_mukei;
    ///// ·×»»·ë²Ì¤ò¥Ï¥Ã¥·¥å¤ØÂåÆş
    $tbody['tbody_rui_syo_tate_total']  = number_format($total_rui_syo_tate, $keta);
    $tbody['tbody_rui_syo_kougu_total'] = number_format($total_rui_syo_kougu, $keta);
    $tbody['tbody_rui_syo_yukei_total'] = number_format($total_rui_syo_yukei, $keta);
    $tbody['tbody_rui_syo_mukei_total'] = number_format($total_rui_syo_mukei, $keta);
    $tbody['tbody_rui_syo_all']         = number_format($total_rui_syo_all, $keta);
    /////////////////////////////////////////////////////////////////////// ¸º²Á½şµÑÎß·×³ÛÅö´ü½şµÑ³Û END
    
    /////////////////////////////////////////////////////////////////////// ¸º²Á½şµÑÎß·×³Û´üËö»Ä¹â START
    ///// ³Æ´üËö»Ä¹â¤ò·×»»
    // ·úÊª¡¢·úÊªÉíÂ°ÀßÈ÷
    $tbody_rui_kima_tate   = $tbody_rui_kishu_tate - $month['·úÊªÎß·×³Û´üÃæ¸º¾¯'] + $month['·úÊªÎß·×³ÛÅö´ü½şµÑ³Û'];
    $tbody_rui_kima_fuzoku = $tbody_rui_kishu_fuzoku - $month['·úÊªÉíÂ°ÀßÈ÷Îß·×³Û´üÃæ¸º¾¯'] + $month['·úÊªÉíÂ°ÀßÈ÷Îß·×³ÛÅö´ü½şµÑ³Û'];
    // ·úÊª¹ç·×
    $total_rui_kima_tate   = $tbody_rui_kima_tate + $tbody_rui_kima_fuzoku;
    // ¹½ÃÛÊª¡¢µ¡³£ÁõÃÖ¡¢¼ÖíÑ±¿ÈÂ¶ñ¡¢´ï¶ñ¹©¶ñ¡¢½º´ïÈ÷ÉÊ
    $tbody_rui_kima_kouti  = $tbody_rui_kishu_kouti - $month['¹½ÃÛÊªÎß·×³Û´üÃæ¸º¾¯'] + $month['¹½ÃÛÊªÎß·×³ÛÅö´ü½şµÑ³Û'];
    $tbody_rui_kima_kikai  = $tbody_rui_kishu_kikai - $month['µ¡³£ÁõÃÖÎß·×³Û´üÃæ¸º¾¯'] + $month['µ¡³£ÁõÃÖÎß·×³ÛÅö´ü½şµÑ³Û'];
    $tbody_rui_kima_sharyo = $tbody_rui_kishu_sharyo - $month['¼ÖíÑ±¿ÈÂ¶ñÎß·×³Û´üÃæ¸º¾¯'] + $month['¼ÖíÑ±¿ÈÂ¶ñÎß·×³ÛÅö´ü½şµÑ³Û'];
    $tbody_rui_kima_kigu   = $tbody_rui_kishu_kigu - $month['´ï¶ñ¹©¶ñÎß·×³Û´üÃæ¸º¾¯'] + $month['´ï¶ñ¹©¶ñÎß·×³ÛÅö´ü½şµÑ³Û'];
    $tbody_rui_kima_jyuki  = $tbody_rui_kishu_jyuki - $month['½º´ïÈ÷ÉÊÎß·×³Û´üÃæ¸º¾¯'] + $month['½º´ïÈ÷ÉÊÎß·×³ÛÅö´ü½şµÑ³Û'];
    // ´ï¶ñ¹©¶ñ¡¢½º´ïÈ÷ÉÊ¹ç·×
    $total_rui_kima_kougu  = $tbody_rui_kima_kigu + $tbody_rui_kima_jyuki;
    // ¥ê¡¼¥¹»ñ»º
    $tbody_rui_kima_lease  = $tbody_rui_kishu_lease - $month['¥ê¡¼¥¹»ñ»ºÎß·×³Û´üÃæ¸º¾¯'] + $month['¥ê¡¼¥¹»ñ»ºÎß·×³ÛÅö´ü½şµÑ³Û'];
    // Í­·Á¹ç·×
    $total_rui_kima_yukei  = $total_rui_kima_tate + $tbody_rui_kima_kouti + $tbody_rui_kima_kikai + 
                             $tbody_rui_kima_sharyo + $total_rui_kima_kougu + $tbody_rui_kima_lease;
    // ÅÅÏÃ²ÃÆş¸¢¡¢»ÜÀßÍøÍÑ¸¢¡¢¥½¥Õ¥È¥¦¥§¥¢
    $tbody_rui_kima_denwa  = $tbody_rui_kishu_denwa - $month['ÅÅÏÃ²ÃÆş¸¢Îß·×³Û´üÃæ¸º¾¯'] + $month['ÅÅÏÃ²ÃÆş¸¢Îß·×³ÛÅö´ü½şµÑ³Û'];
    $tbody_rui_kima_shise  = $tbody_rui_kishu_shise - $month['»ÜÀßÍøÍÑ¸¢Îß·×³Û´üÃæ¸º¾¯'] + $month['»ÜÀßÍøÍÑ¸¢Îß·×³ÛÅö´ü½şµÑ³Û'];
    $tbody_rui_kima_soft   = $tbody_rui_kishu_soft - $month['¥½¥Õ¥È¥¦¥§¥¢Îß·×³Û´üÃæ¸º¾¯'] + $month['¥½¥Õ¥È¥¦¥§¥¢Îß·×³ÛÅö´ü½şµÑ³Û'];
    // Ìµ·Á¹ç·×
    $total_rui_kima_mukei  = $tbody_rui_kima_denwa + $tbody_rui_kima_shise + $tbody_rui_kima_soft;
    // Áí¹ç·×
    $total_rui_kima_all    = $total_rui_kima_yukei + $total_rui_kima_mukei;
    ///// ·×»»·ë²Ì¤ò¥Ï¥Ã¥·¥å¤ØÂåÆş
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
    /////////////////////////////////////////////////////////////////////// ¸º²Á½şµÑÎß·×³Û´üËö»Ä¹â END
    
    /////////////////////////////////////////////////////////////////////// ½üµÑ»ñ»ºÅù¤ÎÄ¢Êí²Á³Û START
    ///// ³Æ½üµÑ»ñ»ºÅù¤ÎÄ¢Êí²Á³Û¤ò·×»»
    // ·úÊª¡¢·úÊªÉíÂ°ÀßÈ÷
    $tbody_jyo_cho_tate   = $month['·úÊª¼èÆÀ²Á³Û´üÃæ¸º¾¯'] - $month['·úÊªÎß·×³Û´üÃæ¸º¾¯'];
    $tbody_jyo_cho_fuzoku = $month['·úÊªÉíÂ°ÀßÈ÷¼èÆÀ²Á³Û´üÃæ¸º¾¯'] - $month['·úÊªÉíÂ°ÀßÈ÷Îß·×³Û´üÃæ¸º¾¯'];
    // ·úÊª¹ç·×
    $total_jyo_cho_tate   = $tbody_jyo_cho_tate + $tbody_jyo_cho_fuzoku;
    // ¹½ÃÛÊª¡¢µ¡³£ÁõÃÖ¡¢¼ÖíÑ±¿ÈÂ¶ñ¡¢´ï¶ñ¹©¶ñ¡¢½º´ïÈ÷ÉÊ
    $tbody_jyo_cho_kouti  = $month['¹½ÃÛÊª¼èÆÀ²Á³Û´üÃæ¸º¾¯'] - $month['¹½ÃÛÊªÎß·×³Û´üÃæ¸º¾¯'];
    $tbody_jyo_cho_kikai  = $month['µ¡³£ÁõÃÖ¼èÆÀ²Á³Û´üÃæ¸º¾¯'] - $month['µ¡³£ÁõÃÖÎß·×³Û´üÃæ¸º¾¯'];
    $tbody_jyo_cho_sharyo = $month['¼ÖíÑ±¿ÈÂ¶ñ¼èÆÀ²Á³Û´üÃæ¸º¾¯'] - $month['¼ÖíÑ±¿ÈÂ¶ñÎß·×³Û´üÃæ¸º¾¯'];
    $tbody_jyo_cho_kigu   = $month['´ï¶ñ¹©¶ñ¼èÆÀ²Á³Û´üÃæ¸º¾¯'] - $month['´ï¶ñ¹©¶ñÎß·×³Û´üÃæ¸º¾¯'];
    $tbody_jyo_cho_jyuki  = $month['½º´ïÈ÷ÉÊ¼èÆÀ²Á³Û´üÃæ¸º¾¯'] - $month['½º´ïÈ÷ÉÊÎß·×³Û´üÃæ¸º¾¯'];
    // ´ï¶ñ¹©¶ñ¡¢½º´ïÈ÷ÉÊ¹ç·×
    $total_jyo_cho_kougu  = $tbody_jyo_cho_kigu + $tbody_jyo_cho_jyuki;
    // ¥ê¡¼¥¹»ñ»º
    $tbody_jyo_cho_lease  = $month['¥ê¡¼¥¹»ñ»º¼èÆÀ²Á³Û´üÃæ¸º¾¯'] - $month['¥ê¡¼¥¹»ñ»ºÎß·×³Û´üÃæ¸º¾¯'];
    // Í­·Á¹ç·×
    $total_jyo_cho_yukei  = $total_jyo_cho_tate + $tbody_jyo_cho_kouti + $tbody_jyo_cho_kikai + 
                            $tbody_jyo_cho_sharyo + $total_jyo_cho_kougu + $tbody_jyo_cho_lease;
    // ÅÅÏÃ²ÃÆş¸¢¡¢»ÜÀßÍøÍÑ¸¢¡¢¥½¥Õ¥È¥¦¥§¥¢
    $tbody_jyo_cho_denwa  = $month['ÅÅÏÃ²ÃÆş¸¢¼èÆÀ²Á³Û´üÃæ¸º¾¯'] - $month['ÅÅÏÃ²ÃÆş¸¢Îß·×³Û´üÃæ¸º¾¯'];
    $tbody_jyo_cho_shise  = $month['»ÜÀßÍøÍÑ¸¢¼èÆÀ²Á³Û´üÃæ¸º¾¯'] - $month['»ÜÀßÍøÍÑ¸¢Îß·×³Û´üÃæ¸º¾¯'];
    $tbody_jyo_cho_soft   = $month['¥½¥Õ¥È¥¦¥§¥¢¼èÆÀ²Á³Û´üÃæ¸º¾¯'] - $month['¥½¥Õ¥È¥¦¥§¥¢Îß·×³Û´üÃæ¸º¾¯'];
    // Ìµ·Á¹ç·×
    $total_jyo_cho_mukei  = $tbody_jyo_cho_denwa + $tbody_jyo_cho_shise + $tbody_jyo_cho_soft;
    // Áí¹ç·×
    $total_jyo_cho_all    = $total_jyo_cho_yukei + $total_jyo_cho_mukei;
    ///// ·×»»·ë²Ì¤ò¥Ï¥Ã¥·¥å¤ØÂåÆş
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
    /////////////////////////////////////////////////////////////////////// ½üµÑ»ñ»ºÅù¤ÎÄ¢Êí²Á³Û END
    
    /////////////////////////////////////////////////////////////////////// ´üËöÄ¢Êí»Ä¹â START
    ///// ³Æ´üËöÄ¢Êí»Ä¹â¤ò·×»»
    // ·úÊª¡¢·úÊªÉíÂ°ÀßÈ÷
    $tbody_kima_cho_tate   = $tbody_shutoku_kima_tate - $tbody_rui_kima_tate;
    $tbody_kima_cho_fuzoku = $tbody_shutoku_kima_fuzoku - $tbody_rui_kima_fuzoku;
    // ·úÊª¹ç·×
    $total_kima_cho_tate   = $tbody_kima_cho_tate + $tbody_kima_cho_fuzoku;
    // ¹½ÃÛÊª¡¢µ¡³£ÁõÃÖ¡¢¼ÖíÑ±¿ÈÂ¶ñ¡¢´ï¶ñ¹©¶ñ¡¢½º´ïÈ÷ÉÊ
    $tbody_kima_cho_kouti  = $tbody_shutoku_kima_kouti - $tbody_rui_kima_kouti;
    $tbody_kima_cho_kikai  = $tbody_shutoku_kima_kikai - $tbody_rui_kima_kikai;
    $tbody_kima_cho_sharyo = $tbody_shutoku_kima_sharyo - $tbody_rui_kima_sharyo;
    $tbody_kima_cho_kigu   = $tbody_shutoku_kima_kigu - $tbody_rui_kima_kigu;
    $tbody_kima_cho_jyuki  = $tbody_shutoku_kima_jyuki - $tbody_rui_kima_jyuki;
    // ´ï¶ñ¹©¶ñ¡¢½º´ïÈ÷ÉÊ¹ç·×
    $total_kima_cho_kougu  = $tbody_kima_cho_kigu + $tbody_kima_cho_jyuki;
    // ¥ê¡¼¥¹»ñ»º
    $tbody_kima_cho_lease  = $tbody_shutoku_kima_lease - $tbody_rui_kima_lease;
    // Í­·Á¹ç·×
    $total_kima_cho_yukei  = $total_kima_cho_tate + $tbody_kima_cho_kouti + $tbody_kima_cho_kikai + 
                             $tbody_kima_cho_sharyo + $total_kima_cho_kougu + $tbody_kima_cho_lease;
    // ÅÅÏÃ²ÃÆş¸¢¡¢»ÜÀßÍøÍÑ¸¢¡¢¥½¥Õ¥È¥¦¥§¥¢
    $tbody_kima_cho_denwa  = $tbody_shutoku_kima_denwa - $tbody_rui_kima_denwa;
    $tbody_kima_cho_shise  = $tbody_shutoku_kima_shise - $tbody_rui_kima_shise;
    $tbody_kima_cho_soft   = $tbody_shutoku_kima_soft - $tbody_rui_kima_soft;
    // Ìµ·Á¹ç·×
    $total_kima_cho_mukei  = $tbody_kima_cho_denwa + $tbody_kima_cho_shise + $tbody_kima_cho_soft;
    // Áí¹ç·×
    $total_kima_cho_all    = $total_kima_cho_yukei + $total_kima_cho_mukei;
    ///// ·×»»·ë²Ì¤ò¥Ï¥Ã¥·¥å¤ØÂåÆş
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
    /////////////////////////////////////////////////////////////////////// ´üËöÄ¢Êí»Ä¹â END
/*
}
*/

/********** patTemplate ½ñ½Ğ¤· ************/
include_once ( '../../../patTemplate/include/patTemplate.php' );
$tmpl = new patTemplate();

//  In diesem Verzeichnis liegen die Templates
$tmpl->setBasedir( 'templates' );

$tmpl->readTemplatesFromFile( 'shihanki_depreciation_statement_202001.templ.html' );

$tmpl->addVar('page', 'PAGE_TITLE'         , '¸º²Á½şµÑ»ñ»º¤ª¤è¤Ó¸º²Á½şµÑÈñ¤ÎÌÀºÙ');
$tmpl->addVar('page', 'PAGE_MENU_SITE_URL' , $menu_site_script);
$tmpl->addVar('page', 'PAGE_UNIQUE'        , $uniq);
$tmpl->addVar('page', 'PAGE_RETURN_URL'    , $url_referer);
$tmpl->addVar('page', 'PAGE_CURRENT_URL'   , $current_script);
$tmpl->addVar('page', 'PAGE_SITE_VIEW'     , $site_view);
$tmpl->addVar('page', 'PAGE_HEADER_TITLE'  , "Âè{$ki}´ü Âè{$hanki}»ÍÈ¾´ü ¸º²Á½şµÑ»ñ»º¤ª¤è¤Ó¸º²Á½şµÑÈñ¤ÎÌÀºÙ");
$tmpl->addVar('page', 'PAGE_HEADER_TODAY'  , $today);
$tmpl->addVar('page', 'OUT_CSS'            , $menu->out_css());
$tmpl->addVar('page', 'OUT_JSBASE'         , $menu->out_jsBaseClass());
$tmpl->addVar('page', 'OUT_TITLE_BORDER'   , $menu->out_title_border());

///// É½¼¨Ã±°Ì¤ò¥Æ¥ó¥×¥ì¡¼¥ÈÊÑ¿ô¤Ø¤ÎÅĞÏ¿
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
///// ¾®¿ôÅÀ°Ê²¼¤Î·å¿ô
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

///// ¥Ï¥Ã¥·¥åÇÛÎó¤Ç patTemplate ¤ËÅ¸³« ¥«¥×¥é¡¦¥ê¥Ë¥¢¡¦Á´ÂÎ¤¬ tbody[]¤ËÂåÆş¤µ¤ì¤Æ¤¤¤ë
$tmpl->addVars('tbody', $tbody);

//$tmpl->addVars( 'tbody_rows', array('TBODY_DSP_NUM' => $dsp_num) );
//$tmpl->addVars( 'tbody_rows', array('TBODY_FIELD0'  => $field0) );
//$tmpl->addVars( 'tbody_rows', array('TBODY_FIELD1'  => $field1) );


/********** Logic End   **********/

/////////// HTML Header ¤ò½ĞÎÏ¤·¤Æ¥­¥ã¥Ã¥·¥å¤òÀ©¸æ
$menu->out_html_header();

//  Alle Templates ausgeben
$tmpl->displayParsedTemplate();
/************* patTemplate ½ªÎ» *****************/

?>
