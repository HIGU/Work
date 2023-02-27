<?php
//////////////////////////////////////////////////////////////////////////////
// ·î¼¡Â»±×´Ø·¸ ·è»»½ñ                                                      //
// Copyright(C) 2018-2020 Norihisa.Ohya norihisa_ooya@nitto-kohki.co.jp     //
// Changed history                                                          //
// 2018/06/26 Created   financial_report_view.php                           //
// 2018/07/05 Âè£±»ÍÈ¾´ü·è»»¤Ç°ìÉôÄ´À°                                      //
// 2018/07/25 ²ÄÇ½¤ÊÉôÊ¬¤ÇAS·×»»¤ÎÂ»±×¥Ç¡¼¥¿¤ÈÈæ³Ó¤òÄÉ²Ã                    //
//            ±Ä¶È³°¤Ë´Ø¤·¤Æ¤Ï¡¢°ÙÂØº¹Â»±×¤Î°Ù¡¢Æ±¶â³Û¤Îº¹°Û¤¬¤Ç¤ë          //
// 2018/10/05 ¥Ç¥¶¥¤¥ó¤Î¤¯¤º¤ì¤ò½¤Àµ                                        //
// 2018/10/17 19´üÂè2»ÍÈ¾´ü¤Î·ë²Ì¤ò¼õ¤±¤Æ½¤Àµ                               //
// 2019/04/09 ÈÎ´ÉÈñ¤Î¥¯¥ì¡¼¥àÂÐ±þÈñ¤òÄÉ²Ã                                  //
// 2019/05/17 ÆüÉÕ¤Î¼èÆÀÊýË¡¤ÎÊÑ¹¹                                          //
// 2020/01/27 ¸º²Á½þµÑÈñÌÀºÙÉ½¤òÄÉ²Ã                                        //
// 2020/04/13 eCAÍÑ¤Î¥Ç¡¼¥¿È´¤­½Ð¤·¤òÄÉ²Ã                                   //
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
$nk_ki = $ki + 44;

//////////// ¥¿¥¤¥È¥ëÌ¾(¥½¡¼¥¹¤Î¥¿¥¤¥È¥ëÌ¾¤È¥Õ¥©¡¼¥à¤Î¥¿¥¤¥È¥ëÌ¾)
if ($tuki_chk == 3) {
    $menu->set_title("Âè {$ki} ´ü¡¡ËÜ·è»»¡¡·è¡¡»»¡¡½ñ");
} else {
    $menu->set_title("Âè {$ki} ´ü¡¡Âè{$hanki}»ÍÈ¾´ü¡¡·è¡¡»»¡¡½ñ");
}

//// Âß¼ÚÂÐ¾ÈÉ½
//// Î®Æ°»ñ»º
// ¸½¶âµÚ¤ÓÍÂ¶â
$res   = array();
$field = array();
$rows  = array();
$genkin_kin = 0;
$note = '¸½¶âµÚ¤ÓÍÂ¶â';
$query = sprintf("SELECT rep_kin FROM financial_report_data WHERE rep_ymd=%d and rep_note='%s'", $yyyymm, $note);
if ($rows=getResultWithField2($query, $field, $res) <= 0) {
    $genkin_kin = 0;
} else {
    $genkin_kin = $res[0][0];
}
// 2020/03/26 eCA¥Ç¡¼¥¿Ï¢·ÈÂÐ±þ ½ÐÎÏ
$csv_data = array();
$csv_data[0][0] = $note;
$csv_data[0][1] = $genkin_kin;

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
// 2020/03/26 eCA¥Ç¡¼¥¿Ï¢·ÈÂÐ±þ ½ÐÎÏ
$csv_data[1][0] = $note;
$csv_data[1][1] = $urikake_kin;

// »Å³ÝÉÊ
$res   = array();
$field = array();
$rows  = array();
$tai_shikakari_kin = 0;
$note = 'Âß¼Ú»Å³ÝÉÊ';
$query = sprintf("SELECT rep_kin FROM financial_report_data WHERE rep_ymd=%d and rep_note='%s'", $yyyymm, $note);
if ($rows=getResultWithField2($query, $field, $res) <= 0) {
    $tai_shikakari_kin = 0;
} else {
    $tai_shikakari_kin = $res[0][0];
}
// ¸¶ºàÎÁµÚ¤ÓÃùÂ¢ÉÊ
$res   = array();
$field = array();
$rows  = array();
$tai_zairyo_kin = 0;
$note = 'Âß¼Ú¸¶ºàÎÁµÚ¤ÓÃùÂ¢ÉÊ';
$query = sprintf("SELECT rep_kin FROM financial_report_data WHERE rep_ymd=%d and rep_note='%s'", $yyyymm, $note);
if ($rows=getResultWithField2($query, $field, $res) <= 0) {
    $tai_zairyo_kin = 0;
} else {
    $tai_zairyo_kin = $res[0][0];
}
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
// Ì¤¼ýÆþ¶â
$res   = array();
$field = array();
$rows  = array();
$mishu_kin_kin = 0;
$note = 'Ì¤¼ýÆþ¶â';
$query = sprintf("SELECT rep_kin FROM financial_report_data WHERE rep_ymd=%d and rep_note='%s'", $yyyymm, $note);
if ($rows=getResultWithField2($query, $field, $res) <= 0) {
    $mishu_kin_kin = 0;
} else {
    $mishu_kin_kin = $res[0][0];
}
// Ì¤¼ý¾ÃÈñÀÇÅù
$res   = array();
$field = array();
$rows  = array();
$mishu_shozei_kin = 0;
$note = 'Ì¤¼ý¾ÃÈñÀÇÅù';
$query = sprintf("SELECT kin FROM profit_loss_bs_history WHERE pl_bs_ym=%d and note='%s'", $yyyymm, $note);
if ($rows=getResultWithField2($query, $field, $res) <= 0) {
    $mishu_shozei_kin = 0;
} else {
    $mishu_shozei_kin = $res[0][0];
}
// ¤½¤ÎÂ¾¤ÎÎ®Æ°»ñ»º
$res   = array();
$field = array();
$rows  = array();
$ta_ryudo_shisan_kin = 0;
$note = '¤½¤ÎÂ¾¤ÎÎ®Æ°»ñ»º';
$query = sprintf("SELECT rep_kin FROM financial_report_data WHERE rep_ymd=%d and rep_note='%s'", $yyyymm, $note);
if ($rows=getResultWithField2($query, $field, $res) <= 0) {
    $ta_ryudo_shisan_kin = 0;
} else {
    $ta_ryudo_shisan_kin = $res[0][0];
}

// Î®Æ°»ñ»º¹ç·×
$ryudo_total_kin = $genkin_kin + $urikake_kin + $tai_shikakari_kin + $tai_zairyo_kin + $mae_hiyo_kin + $mishu_kin_kin + $mishu_shozei_kin + $ta_ryudo_shisan_kin;

//// ¸ÇÄê»ñ»º
//// Í­·Á¸ÇÄê»ñ»º
// ·úÊª
$res   = array();
$field = array();
$rows  = array();
$tatemono_shisan_kin = 0;
$note = '·úÊª';
$query = sprintf("SELECT rep_kin FROM financial_report_data WHERE rep_ymd=%d and rep_note='%s'", $yyyymm, $note);
if ($rows=getResultWithField2($query, $field, $res) <= 0) {
    $tatemono_shisan_kin = 0;
} else {
    $tatemono_shisan_kin = $res[0][0];
}
// µ¡³£µÚ¤ÓÁõÃÖ
$res   = array();
$field = array();
$rows  = array();
$kikai_shisan_kin = 0;
$note = 'µ¡³£µÚ¤ÓÁõÃÖ';
$query = sprintf("SELECT rep_kin FROM financial_report_data WHERE rep_ymd=%d and rep_note='%s'", $yyyymm, $note);
if ($rows=getResultWithField2($query, $field, $res) <= 0) {
    $kikai_shisan_kin = 0;
} else {
    $kikai_shisan_kin = $res[0][0];
}
// ¼ÖíÒ±¿ÈÂ¶ñ
$res   = array();
$field = array();
$rows  = array();
$sharyo_shisan_kin = 0;
$note = '¼ÖíÒ±¿ÈÂ¶ñ';
$query = sprintf("SELECT rep_kin FROM financial_report_data WHERE rep_ymd=%d and rep_note='%s'", $yyyymm, $note);
if ($rows=getResultWithField2($query, $field, $res) <= 0) {
    $sharyo_shisan_kin = 0;
} else {
    $sharyo_shisan_kin = $res[0][0];
}
// ¹©¶ñ´ï¶ñµÚ¤ÓÈ÷ÉÊ
$res   = array();
$field = array();
$rows  = array();
$kougu_shisan_kin = 0;
$note = '¹©¶ñ´ï¶ñµÚ¤ÓÈ÷ÉÊ';
$query = sprintf("SELECT rep_kin FROM financial_report_data WHERE rep_ymd=%d and rep_note='%s'", $yyyymm, $note);
if ($rows=getResultWithField2($query, $field, $res) <= 0) {
    $kougu_shisan_kin = 0;
} else {
    $kougu_shisan_kin = $res[0][0];
}
// ¥ê¡¼¥¹»ñ»º
$res   = array();
$field = array();
$rows  = array();
$lease_shisan_kin = 0;
$note = '¥ê¡¼¥¹»ñ»º';
$query = sprintf("SELECT rep_kin FROM financial_report_data WHERE rep_ymd=%d and rep_note='%s'", $yyyymm, $note);
if ($rows=getResultWithField2($query, $field, $res) <= 0) {
    $lease_shisan_kin = 0;
} else {
    $lease_shisan_kin = $res[0][0];
}
// ·úÀß²¾´ªÄê
$res   = array();
$field = array();
$rows  = array();
$kenkari_kin = 0;
$note = '·úÀß²¾´ªÄê';
$query = sprintf("SELECT kin FROM profit_loss_bs_history WHERE pl_bs_ym=%d and note='%s'", $yyyymm, $note);
if ($rows=getResultWithField2($query, $field, $res) <= 0) {
    $kenkari_kin = 0;
} else {
    $kenkari_kin = $res[0][0];
}

// Í­·Á¸ÇÄê»ñ»º¹ç·×
$yukei_shisan_kin = $tatemono_shisan_kin + $kikai_shisan_kin + $sharyo_shisan_kin + $kougu_shisan_kin + $lease_shisan_kin + $kenkari_kin;

//// Ìµ·Á¸ÇÄê»ñ»º
// ÅÅÏÃ²ÃÆþ¸¢
$res   = array();
$field = array();
$rows  = array();
$denwa_shisan_kin = 0;
$note = 'ÅÅÏÃ²ÃÆþ¸¢';
$query = sprintf("SELECT rep_kin FROM financial_report_data WHERE rep_ymd=%d and rep_note='%s'", $yyyymm, $note);
if ($rows=getResultWithField2($query, $field, $res) <= 0) {
    $denwa_shisan_kin = 0;
} else {
    $denwa_shisan_kin = $res[0][0];
}
// »ÜÀßÍøÍÑ¸¢
$res   = array();
$field = array();
$rows  = array();
$shisetsu_shisan_kin = 0;
$note = '»ÜÀßÍøÍÑ¸¢';
$query = sprintf("SELECT kin FROM profit_loss_bs_history WHERE pl_bs_ym=%d and note='%s'", $yyyymm, $note);
if ($rows=getResultWithField2($query, $field, $res) <= 0) {
    $shisetsu_shisan_kin = 0;
} else {
    $shisetsu_shisan_kin = $res[0][0];
}
// ¥½¥Õ¥È¥¦¥§¥¢
$res   = array();
$field = array();
$rows  = array();
$soft_shisan_kin = 0;
$note = '¥½¥Õ¥È¥¦¥§¥¢';
$query = sprintf("SELECT kin FROM profit_loss_bs_history WHERE pl_bs_ym=%d and note='%s'", $yyyymm, $note);
if ($rows=getResultWithField2($query, $field, $res) <= 0) {
    $soft_shisan_kin = 0;
} else {
    $soft_shisan_kin = $res[0][0];
}

// Ìµ·Á¸ÇÄê»ñ»º¹ç·×
$mukei_shisan_kin = $denwa_shisan_kin + $shisetsu_shisan_kin + $soft_shisan_kin;

//// Åê»ñ¤½¤ÎÂ¾¤Î»ñ»º
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

// ¤½¤ÎÂ¾¤ÎÅê»ñÅù
$res   = array();
$field = array();
$rows  = array();
$sonota_toshi_kin = 0;
$note = '¤½¤ÎÂ¾¤ÎÅê»ñÅù';
$query = sprintf("SELECT kin FROM profit_loss_bs_history WHERE pl_bs_ym=%d and note='%s'", $yyyymm, $note);
if ($rows=getResultWithField2($query, $field, $res) <= 0) {
    $sonota_toshi_kin = 0;
} else {
    $sonota_toshi_kin = $res[0][0];
}

// Åê»ñ¤½¤ÎÂ¾¤Î»ñ»º¹ç·×
$toshi_sonota_kin = $choki_kashi_kin + $choki_maebara_kin + $kotei_kuri_zei_kin + $sonota_toshi_kin;

// ¸ÇÄê»ñ»º¹ç·×
$kotei_shisan_total_kin = $yukei_shisan_kin + $mukei_shisan_kin + $toshi_sonota_kin;

// »ñ»º¤ÎÉô¹ç·×
$shisan_total_kin = $ryudo_total_kin + $kotei_shisan_total_kin;

//// ÉéºÄµÚ¤Ó½ã»ñ»º¤ÎÉô
//// ÉéºÄ¤ÎÉô
//// Î®Æ°ÉéºÄ
// Çã³Ý¶â
$res   = array();
$field = array();
$rows  = array();
$kaikake_kin = 0;
$note = 'Çã³Ý¶â';
$query = sprintf("SELECT rep_kin FROM financial_report_data WHERE rep_ymd=%d and rep_note='%s'", $yyyymm, $note);
if ($rows=getResultWithField2($query, $field, $res) <= 0) {
    $kaikake_kin = 0;
} else {
    $kaikake_kin = $res[0][0];
}
// ¥ê¡¼¥¹ºÄÌ³¡ÊÃ»´ü¡Ë
$res   = array();
$field = array();
$rows  = array();
$lease_tanki_kin = 0;
$note = '¥ê¡¼¥¹ºÄÌ³(Ã»´ü)';
$query = sprintf("SELECT kin FROM profit_loss_bs_history WHERE pl_bs_ym=%d and note='%s'", $yyyymm, $note);
if ($rows=getResultWithField2($query, $field, $res) <= 0) {
    $lease_tanki_kin = 0;
} else {
    $lease_tanki_kin = $res[0][0];
}
// Ì¤Ê§¶â
$res   = array();
$field = array();
$rows  = array();
$miharai_kin = 0;
$note = 'Ì¤Ê§¶â';
$query = sprintf("SELECT rep_kin FROM financial_report_data WHERE rep_ymd=%d and rep_note='%s'", $yyyymm, $note);
if ($rows=getResultWithField2($query, $field, $res) <= 0) {
    $miharai_kin = 0;
} else {
    $miharai_kin = $res[0][0];
}
// Ì¤Ê§¾ÃÈñÀÇÅù
$res   = array();
$field = array();
$rows  = array();
$miharai_shozei_kin = 0;
$note = 'Ì¤Ê§¾ÃÈñÀÇÅù';
$query = sprintf("SELECT rep_kin FROM financial_report_data WHERE rep_ymd=%d and rep_note='%s'", $yyyymm, $note);
if ($rows=getResultWithField2($query, $field, $res) <= 0) {
    $miharai_shozei_kin = 0;
} else {
    $miharai_shozei_kin = $res[0][0];
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

// Î®Æ°ÉéºÄ¹ç·×
$ryudo_fusai_total_kin = $kaikake_kin + $lease_tanki_kin + $miharai_kin + $miharai_shozei_kin + $miharai_hozei_kin + $miharai_hiyo_kin + $azukari_kin + $syoyo_hikiate_kin;

//// ¸ÇÄêÉéºÄ
// ¥ê¡¼¥¹ºÄÌ³¡ÊÄ¹´ü¡Ë
$res   = array();
$field = array();
$rows  = array();
$lease_choki_kin = 0;
$note = '¥ê¡¼¥¹ºÄÌ³(Ä¹´ü)';
$query = sprintf("SELECT kin FROM profit_loss_bs_history WHERE pl_bs_ym=%d and note='%s'", $yyyymm, $note);
if ($rows=getResultWithField2($query, $field, $res) <= 0) {
    $lease_choki_kin = 0;
} else {
    $lease_choki_kin = $res[0][0];
}
// Ä¹´üÌ¤Ê§¶â
$res   = array();
$field = array();
$rows  = array();
$choki_miharai_kin = 0;
$note = 'Ä¹´üÌ¤Ê§¶â';
$query = sprintf("SELECT kin FROM profit_loss_bs_history WHERE pl_bs_ym=%d and note='%s'", $yyyymm, $note);
if ($rows=getResultWithField2($query, $field, $res) <= 0) {
    $choki_miharai_kin = 0;
} else {
    $choki_miharai_kin = $res[0][0];
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

// ¸ÇÄêÉéºÄ¹ç·×
$kotei_fusai_kin = $lease_choki_kin + $choki_miharai_kin + $taisyoku_hikiate_kin;

// ÉéºÄ¹ç·×
$fusai_total_kin = $ryudo_fusai_total_kin + $kotei_fusai_kin;

//// ½ã»ñ»º¤ÎÉô
//// »ñËÜ¶â
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

//// »ñËÜ¾êÍ¾¶â
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

// »ñËÜ¾êÍ¾¶â¹ç·×
$tai_shihon_jyoyo_total_kin = $shihon_jyunbi_kin + $sonota_shihon_jyoyo_kin;

//// Íø±×¾êÍ¾¶â
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
// ·«±ÛÍø±×¾êÍ¾¶â
$res   = array();
$field = array();
$rows  = array();
$tai_kuri_rieki_jyoyo_kin = 0;
$note = '·«±ÛÍø±×¾êÍ¾¶â';
$query = sprintf("SELECT kin FROM profit_loss_bs_history WHERE pl_bs_ym=%d and note='%s'", $yyyymm, $note);
if ($rows=getResultWithField2($query, $field, $res) <= 0) {
    $tai_kuri_rieki_jyoyo_kin = 0;
} else {
    $tai_kuri_rieki_jyoyo_kin = $res[0][0];
}
// Åö´ü½ãÍø±×¡Ê·«±ÛÍø±×¾êÍ¾¶â¤Ë¹ç·×¤¹¤ë»ÍÈ¾´üÂÐ±þ¡Ë
$res   = array();
$field = array();
$rows  = array();
$tai_toujyun = 0;
$note = 'Åö´ü½ãÍø±×';
$query = sprintf("SELECT kin FROM profit_loss_bs_history WHERE pl_bs_ym=%d and note='%s'", $yyyymm, $note);
if ($rows=getResultWithField2($query, $field, $res) <= 0) {
    $tai_toujyun = 0;
} else {
    $tai_toujyun = $res[0][0];
}

// Âß¼ÚÂÐ¾ÈÉ½ÍÑ ·«±ÛÍø±×¾êÍ¾¶â¤Î·×»»
$tai_kuri_rieki_jyoyo_kin = $tai_kuri_rieki_jyoyo_kin + $tai_toujyun;

// Íø±×¾êÍ¾¶â¹ç·×
$tai_rieki_jyoyo_total_kin = $tai_sonota_rieki_jyoyo_kin + $tai_kuri_rieki_jyoyo_kin;

// ½ã»ñ»º¹ç·×
$tai_jyun_shisan_total_kin = $shihon_total_kin + $tai_shihon_jyoyo_total_kin + $tai_rieki_jyoyo_total_kin;

// ÉéºÄµÚ¤Ó½ã»ñ»º¹ç·×
$fusai_jyunshi_total_kin = $fusai_total_kin + $tai_jyun_shisan_total_kin;

// Âß¼Úº¹³Û·×»»
$tai_sagaku_kin = $shisan_total_kin - $fusai_jyunshi_total_kin;

//// ·ÐÈñÌÀºÙ½ñ
//// Ìò°÷Êó½·
// À½Â¤ÈñÍÑ
$res   = array();
$field = array();
$rows  = array();
$yakuin_seizo_kin = 0;
$note = 'À½Â¤·ÐÈñÌò°÷Êó½·';
$query = sprintf("SELECT SUM(kin) FROM profit_loss_keihi_history WHERE pl_bs_ym>=%d and pl_bs_ym<=%d and note='%s'", $str_ym, $end_ym, $note);
if ($rows=getResultWithField2($query, $field, $res) <= 0) {
    $yakuin_seizo_kin = 0;
} else {
    $yakuin_seizo_kin = $res[0][0];
}

// ÈÎ´ÉÈñ
$res   = array();
$field = array();
$rows  = array();
$yakuin_han_kin = 0;
$note = 'ÈÎ´ÉÈñÌò°÷Êó½·';
$query = sprintf("SELECT SUM(kin) FROM profit_loss_keihi_history WHERE pl_bs_ym>=%d and pl_bs_ym<=%d and note='%s'", $str_ym, $end_ym, $note);
if ($rows=getResultWithField2($query, $field, $res) <= 0) {
    $yakuin_han_kin = 0;
} else {
    $yakuin_han_kin = $res[0][0];
}

// Ìò°÷Êó½·¹ç·×
$yakuin_total_kin = $yakuin_seizo_kin + $yakuin_han_kin;

//// µëÎÁ¼êÅö
// À½Â¤ÈñÍÑ
$res   = array();
$field = array();
$rows  = array();
$kyuryo_seizo_kin = 0;
$note = 'À½Â¤·ÐÈñµëÎÁ¼êÅö';
$query = sprintf("SELECT SUM(kin) FROM profit_loss_keihi_history WHERE pl_bs_ym>=%d and pl_bs_ym<=%d and note='%s'", $str_ym, $end_ym, $note);
if ($rows=getResultWithField2($query, $field, $res) <= 0) {
    $kyuryo_seizo_kin = 0;
} else {
    $kyuryo_seizo_kin = $res[0][0];
}
// ÈÎ´ÉÈñ
$res   = array();
$field = array();
$rows  = array();
$kyuryo_han_kin = 0;
$note = 'ÈÎ´ÉÈñµëÎÁ¼êÅö';
$query = sprintf("SELECT SUM(kin) FROM profit_loss_keihi_history WHERE pl_bs_ym>=%d and pl_bs_ym<=%d and note='%s'", $str_ym, $end_ym, $note);
if ($rows=getResultWithField2($query, $field, $res) <= 0) {
    $kyuryo_han_kin = 0;
} else {
    $kyuryo_han_kin = $res[0][0];
}

// µëÎÁ¼êÅö¹ç·×
$kyuryo_total_kin = $kyuryo_seizo_kin + $kyuryo_han_kin;

//// ¾ÞÍ¿¼êÅö
// À½Â¤ÈñÍÑ
$res   = array();
$field = array();
$rows  = array();
$syoyo_teate_seizo_kin = 0;
$note = 'À½Â¤·ÐÈñ¾ÞÍ¿¼êÅö';
$query = sprintf("SELECT SUM(kin) FROM profit_loss_keihi_history WHERE pl_bs_ym>=%d and pl_bs_ym<=%d and note='%s'", $str_ym, $end_ym, $note);
if ($rows=getResultWithField2($query, $field, $res) <= 0) {
    $syoyo_teate_seizo_kin = 0;
} else {
    $syoyo_teate_seizo_kin = $res[0][0];
}
// ÈÎ´ÉÈñ
$res   = array();
$field = array();
$rows  = array();
$syoyo_teate_han_kin = 0;
$note = 'ÈÎ´ÉÈñ¾ÞÍ¿¼êÅö';
$query = sprintf("SELECT SUM(kin) FROM profit_loss_keihi_history WHERE pl_bs_ym>=%d and pl_bs_ym<=%d and note='%s'", $str_ym, $end_ym, $note);
if ($rows=getResultWithField2($query, $field, $res) <= 0) {
    $syoyo_teate_han_kin = 0;
} else {
    $syoyo_teate_han_kin = $res[0][0];
}

// ¾ÞÍ¿¼êÅö¹ç·×
$syoyo_teate_total_kin = $syoyo_teate_seizo_kin + $syoyo_teate_han_kin;

//// ¸ÜÌäÎÁ
// À½Â¤ÈñÍÑ
$komon_seizo_kin = 0;
// ÈÎ´ÉÈñ
$res   = array();
$field = array();
$rows  = array();
$komon_han_kin = 0;
$note = 'ÈÎ´ÉÈñ¸ÜÌäÎÁ';
$query = sprintf("SELECT SUM(kin) FROM profit_loss_keihi_history WHERE pl_bs_ym>=%d and pl_bs_ym<=%d and note='%s'", $str_ym, $end_ym, $note);
if ($rows=getResultWithField2($query, $field, $res) <= 0) {
    $komon_han_kin = 0;
} else {
    $komon_han_kin = $res[0][0];
}

// ¸ÜÌäÎÁ¹ç·×
$komon_total_kin = $komon_seizo_kin + $komon_han_kin;

//// ¸üÀ¸Ê¡ÍøÈñ
// À½Â¤ÈñÍÑ
$res   = array();
$field = array();
$rows  = array();
$fukuri_seizo_kin = 0;
$note = 'À½Â¤·ÐÈñ¸üÀ¸Ê¡ÍøÈñ';
$query = sprintf("SELECT rep_kin FROM financial_report_data WHERE rep_ymd=%d and rep_note='%s'", $yyyymm, $note);
if ($rows=getResultWithField2($query, $field, $res) <= 0) {
    $fukuri_seizo_kin = 0;
} else {
    $fukuri_seizo_kin = $res[0][0];
}
// ÈÎ´ÉÈñ
$res   = array();
$field = array();
$rows  = array();
$fukuri_han_kin = 0;
$note = 'ÈÎ´ÉÈñ¸üÀ¸Ê¡ÍøÈñ';
$query = sprintf("SELECT rep_kin FROM financial_report_data WHERE rep_ymd=%d and rep_note='%s'", $yyyymm, $note);
if ($rows=getResultWithField2($query, $field, $res) <= 0) {
    $fukuri_han_kin = 0;
} else {
    $fukuri_han_kin = $res[0][0];
}

// ¸üÀ¸Ê¡ÍøÈñ¹ç·×
$fukuri_total_kin = $fukuri_seizo_kin + $fukuri_han_kin;

//// ¾ÞÍ¿°úÅö¶â·«Æþ³Û
// À½Â¤ÈñÍÑ
$res   = array();
$field = array();
$rows  = array();
$syoyo_hikiate_seizo_kin = 0;
$note = 'À½Â¤·ÐÈñ¾ÞÍ¿°úÅö¶â·«Æþ';
$query = sprintf("SELECT SUM(kin) FROM profit_loss_keihi_history WHERE pl_bs_ym>=%d and pl_bs_ym<=%d and note='%s'", $str_ym, $end_ym, $note);
if ($rows=getResultWithField2($query, $field, $res) <= 0) {
    $syoyo_hikiate_seizo_kin = 0;
} else {
    $syoyo_hikiate_seizo_kin = $res[0][0];
}
// ÈÎ´ÉÈñ
$res   = array();
$field = array();
$rows  = array();
$syoyo_hikiate_han_kin = 0;
$note = 'ÈÎ´ÉÈñ¾ÞÍ¿°úÅö¶â·«Æþ';
$query = sprintf("SELECT SUM(kin) FROM profit_loss_keihi_history WHERE pl_bs_ym>=%d and pl_bs_ym<=%d and note='%s'", $str_ym, $end_ym, $note);
if ($rows=getResultWithField2($query, $field, $res) <= 0) {
    $syoyo_hikiate_han_kin = 0;
} else {
    $syoyo_hikiate_han_kin = $res[0][0];
}

// ¾ÞÍ¿°úÅö¶â·«Æþ¹ç·×
$syoyo_hikiate_total_kin = $syoyo_hikiate_seizo_kin + $syoyo_hikiate_han_kin;

//// Âà¿¦µëÉÕÈñÍÑ
// À½Â¤ÈñÍÑ
$res   = array();
$field = array();
$rows  = array();
$tai_kyufu_seizo_kin = 0;
$note = 'À½Â¤·ÐÈñÂà¿¦µëÉÕÈñÍÑ';
$query = sprintf("SELECT rep_kin FROM financial_report_data WHERE rep_ymd=%d and rep_note='%s'", $yyyymm, $note);
if ($rows=getResultWithField2($query, $field, $res) <= 0) {
    $tai_kyufu_seizo_kin = 0;
} else {
    $tai_kyufu_seizo_kin = $res[0][0];
}
// ÈÎ´ÉÈñ
$res   = array();
$field = array();
$rows  = array();
$tai_kyufu_han_kin = 0;
$note = 'ÈÎ´ÉÈñÂà¿¦µëÉÕÈñÍÑ';
$query = sprintf("SELECT rep_kin FROM financial_report_data WHERE rep_ymd=%d and rep_note='%s'", $yyyymm, $note);
if ($rows=getResultWithField2($query, $field, $res) <= 0) {
    $tai_kyufu_han_kin = 0;
} else {
    $tai_kyufu_han_kin = $res[0][0];
}

// Âà¿¦µëÉÕÈñÍÑ¹ç·×
$tai_kyufu_total_kin = $tai_kyufu_seizo_kin + $tai_kyufu_han_kin;

// Ï«Ì³Èñ¹ç·×
$roumu_total_kin = $yakuin_seizo_kin + $kyuryo_seizo_kin + $syoyo_teate_seizo_kin + $komon_seizo_kin + $fukuri_seizo_kin + $syoyo_hikiate_seizo_kin + $tai_kyufu_seizo_kin;
// ¿Í·ïÈñ¹ç·×
$jin_total_kin   = $yakuin_han_kin + $kyuryo_han_kin + $syoyo_teate_han_kin + $komon_han_kin + $fukuri_han_kin + $syoyo_hikiate_han_kin + $tai_kyufu_han_kin;
// Ï«Ì³Èñ¿Í·ïÈñ¹ç·×
$roumu_jin_total_kin = $roumu_total_kin + $jin_total_kin;

// Ï«Ì³Èñ¡¦¿Í·ïÈñº¹³Û·×»»
// Á´ÂÎÏ«Ì³Èñ
$res   = array();
$field = array();
$rows  = array();
$roumu_as_kin = 0;
$sum1 = 'Á´ÂÎÏ«Ì³Èñ';
$query = sprintf("select SUM(kin) from profit_loss_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='%s'", $str_ym, $end_ym, $sum1);
if ($rows=getResultWithField2($query, $field, $res) <= 0) {
    $roumu_as_kin = 0;
} else {
    $roumu_as_kin = $res[0][0];
}
$roumu_as_sagaku = $roumu_total_kin - $roumu_as_kin;

// Á´ÂÎ¿Í·ïÈñ
$res   = array();
$field = array();
$rows  = array();
$jin_as_kin = 0;
$sum1 = 'Á´ÂÎ¿Í·ïÈñ';
$query = sprintf("select SUM(kin) from profit_loss_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='%s'", $str_ym, $end_ym, $sum1);
if ($rows=getResultWithField2($query, $field, $res) <= 0) {
    $jin_as_kin = 0;
} else {
    $jin_as_kin = $res[0][0];
}
$jin_as_sagaku = $jin_total_kin - $jin_as_kin;


//// À½Â¤·ÐÈñ¡¦·ÐÈñ
//// Î¹Èñ¸òÄÌÈñ
// À½Â¤ÈñÍÑ
$res   = array();
$field = array();
$rows  = array();
$ryohi_seizo_kin = 0;
$note = 'À½Â¤·ÐÈñÎ¹Èñ¸òÄÌÈñ';
$query = sprintf("SELECT rep_kin FROM financial_report_data WHERE rep_ymd=%d and rep_note='%s'", $yyyymm, $note);
if ($rows=getResultWithField2($query, $field, $res) <= 0) {
    $ryohi_seizo_kin = 0;
} else {
    $ryohi_seizo_kin = $res[0][0];
}
// ÈÎ´ÉÈñ
$res   = array();
$field = array();
$rows  = array();
$ryohi_han_kin = 0;
$note = 'ÈÎ´ÉÈñÎ¹Èñ¸òÄÌÈñ';
$query = sprintf("SELECT rep_kin FROM financial_report_data WHERE rep_ymd=%d and rep_note='%s'", $yyyymm, $note);
if ($rows=getResultWithField2($query, $field, $res) <= 0) {
    $ryohi_han_kin = 0;
} else {
    $ryohi_han_kin = $res[0][0];
}

// Î¹Èñ¸òÄÌÈñ¹ç·×
$ryohi_total_kin = $ryohi_seizo_kin + $ryohi_han_kin;

//// ÄÌ¿®Èñ
// À½Â¤ÈñÍÑ
$res   = array();
$field = array();
$rows  = array();
$tsushin_seizo_kin = 0;
$note = 'À½Â¤·ÐÈñÄÌ¿®Èñ';
$query = sprintf("SELECT SUM(kin) FROM profit_loss_keihi_history WHERE pl_bs_ym>=%d and pl_bs_ym<=%d and note='%s'", $str_ym, $end_ym, $note);
if ($rows=getResultWithField2($query, $field, $res) <= 0) {
    $tsushin_seizo_kin = 0;
} else {
    $tsushin_seizo_kin = $res[0][0];
}
// ÈÎ´ÉÈñ
$res   = array();
$field = array();
$rows  = array();
$tsushin_han_kin = 0;
$note = 'ÈÎ´ÉÈñÄÌ¿®Èñ';
$query = sprintf("SELECT SUM(kin) FROM profit_loss_keihi_history WHERE pl_bs_ym>=%d and pl_bs_ym<=%d and note='%s'", $str_ym, $end_ym, $note);
if ($rows=getResultWithField2($query, $field, $res) <= 0) {
    $tsushin_han_kin = 0;
} else {
    $tsushin_han_kin = $res[0][0];
}

// ÄÌ¿®Èñ¹ç·×
$tsushin_total_kin = $tsushin_seizo_kin + $tsushin_han_kin;

//// ²ñµÄÈñ
// À½Â¤ÈñÍÑ
$res   = array();
$field = array();
$rows  = array();
$kaigi_seizo_kin = 0;
$note = 'À½Â¤·ÐÈñ²ñµÄÈñ';
$query = sprintf("SELECT SUM(kin) FROM profit_loss_keihi_history WHERE pl_bs_ym>=%d and pl_bs_ym<=%d and note='%s'", $str_ym, $end_ym, $note);
if ($rows=getResultWithField2($query, $field, $res) <= 0) {
    $kaigi_seizo_kin = 0;
} else {
    $kaigi_seizo_kin = $res[0][0];
}
// ÈÎ´ÉÈñ
$res   = array();
$field = array();
$rows  = array();
$kaigi_han_kin = 0;
$note = 'ÈÎ´ÉÈñ²ñµÄÈñ';
$query = sprintf("SELECT SUM(kin) FROM profit_loss_keihi_history WHERE pl_bs_ym>=%d and pl_bs_ym<=%d and note='%s'", $str_ym, $end_ym, $note);
if ($rows=getResultWithField2($query, $field, $res) <= 0) {
    $kaigi_han_kin = 0;
} else {
    $kaigi_han_kin = $res[0][0];
}

// ²ñµÄÈñ¹ç·×
$kaigi_total_kin = $kaigi_seizo_kin + $kaigi_han_kin;

//// ¸òºÝÀÜÂÔÈñ
// À½Â¤ÈñÍÑ
$res   = array();
$field = array();
$rows  = array();
$kosai_seizo_kin = 0;
$note = 'À½Â¤·ÐÈñ¸òºÝÀÜÂÔÈñ';
$query = sprintf("SELECT SUM(kin) FROM profit_loss_keihi_history WHERE pl_bs_ym>=%d and pl_bs_ym<=%d and note='%s'", $str_ym, $end_ym, $note);
if ($rows=getResultWithField2($query, $field, $res) <= 0) {
    $kosai_seizo_kin = 0;
} else {
    $kosai_seizo_kin = $res[0][0];
}
// ÈÎ´ÉÈñ
$res   = array();
$field = array();
$rows  = array();
$kosai_han_kin = 0;
$note = 'ÈÎ´ÉÈñ¸òºÝÀÜÂÔÈñ';
$query = sprintf("SELECT SUM(kin) FROM profit_loss_keihi_history WHERE pl_bs_ym>=%d and pl_bs_ym<=%d and note='%s'", $str_ym, $end_ym, $note);
if ($rows=getResultWithField2($query, $field, $res) <= 0) {
    $kosai_han_kin = 0;
} else {
    $kosai_han_kin = $res[0][0];
}

// ¸òºÝÀÜÂÔÈñ¹ç·×
$kosai_total_kin = $kosai_seizo_kin + $kosai_han_kin;

//// ¹­¹ðÀëÅÁÈñ
$senden_seizo_kin = 0;
// ÈÎ´ÉÈñ
$res   = array();
$field = array();
$rows  = array();
$senden_han_kin = 0;
$note = 'ÈÎ´ÉÈñ¹­¹ðÀëÅÁÈñ';
$query = sprintf("SELECT rep_kin FROM financial_report_data WHERE rep_ymd=%d and rep_note='%s'", $yyyymm, $note);
if ($rows=getResultWithField2($query, $field, $res) <= 0) {
    $senden_han_kin = 0;
} else {
    $senden_han_kin = $res[0][0];
}

// ¹­¹ðÀëÅÁÈñ¹ç·×
$senden_total_kin = $senden_seizo_kin + $senden_han_kin;

//// ±¿ÄÂ²ÙÂ¤Èñ
// À½Â¤ÈñÍÑ
$res   = array();
$field = array();
$rows  = array();
$nizukuri_seizo_kin = 0;
$note = 'À½Â¤·ÐÈñ±¿ÄÂ²ÙÂ¤Èñ';
$query = sprintf("SELECT SUM(kin) FROM profit_loss_keihi_history WHERE pl_bs_ym>=%d and pl_bs_ym<=%d and note='%s'", $str_ym, $end_ym, $note);
if ($rows=getResultWithField2($query, $field, $res) <= 0) {
    $nizukuri_seizo_kin = 0;
} else {
    $nizukuri_seizo_kin = $res[0][0];
}
// ÈÎ´ÉÈñ
$res   = array();
$field = array();
$rows  = array();
$nizukuri_han_kin = 0;
$note = 'ÈÎ´ÉÈñ±¿ÄÂ²ÙÂ¤Èñ';
$query = sprintf("SELECT SUM(kin) FROM profit_loss_keihi_history WHERE pl_bs_ym>=%d and pl_bs_ym<=%d and note='%s'", $str_ym, $end_ym, $note);
if ($rows=getResultWithField2($query, $field, $res) <= 0) {
    $nizukuri_han_kin = 0;
} else {
    $nizukuri_han_kin = $res[0][0];
}

// ±¿ÄÂ²ÙÂ¤Èñ¹ç·×
$nizukuri_total_kin = $nizukuri_seizo_kin + $nizukuri_han_kin;

//// ¿Þ½ñ¶µ°éÈñ
// À½Â¤ÈñÍÑ
$res   = array();
$field = array();
$rows  = array();
$tosyo_seizo_kin = 0;
$note = 'À½Â¤·ÐÈñ¿Þ½ñ¶µ°éÈñ';
$query = sprintf("SELECT SUM(kin) FROM profit_loss_keihi_history WHERE pl_bs_ym>=%d and pl_bs_ym<=%d and note='%s'", $str_ym, $end_ym, $note);
if ($rows=getResultWithField2($query, $field, $res) <= 0) {
    $tosyo_seizo_kin = 0;
} else {
    $tosyo_seizo_kin = $res[0][0];
}
// ÈÎ´ÉÈñ
$res   = array();
$field = array();
$rows  = array();
$tosyo_han_kin = 0;
$note = 'ÈÎ´ÉÈñ¿Þ½ñ¶µ°éÈñ';
$query = sprintf("SELECT SUM(kin) FROM profit_loss_keihi_history WHERE pl_bs_ym>=%d and pl_bs_ym<=%d and note='%s'", $str_ym, $end_ym, $note);
if ($rows=getResultWithField2($query, $field, $res) <= 0) {
    $tosyo_han_kin = 0;
} else {
    $tosyo_han_kin = $res[0][0];
}

// ¿Þ½ñ¶µ°éÈñ¹ç·×
$tosyo_total_kin = $tosyo_seizo_kin + $tosyo_han_kin;

//// ¶ÈÌ³°ÑÂ÷Èñ
// À½Â¤ÈñÍÑ
$res   = array();
$field = array();
$rows  = array();
$gyomu_seizo_kin = 0;
$note = 'À½Â¤·ÐÈñ¶ÈÌ³°ÑÂ÷Èñ';
$query = sprintf("SELECT rep_kin FROM financial_report_data WHERE rep_ymd=%d and rep_note='%s'", $yyyymm, $note);
if ($rows=getResultWithField2($query, $field, $res) <= 0) {
    $gyomu_seizo_kin = 0;
} else {
    $gyomu_seizo_kin = $res[0][0];
}
// ÈÎ´ÉÈñ
$res   = array();
$field = array();
$rows  = array();
$gyomu_han_kin = 0;
$note = 'ÈÎ´ÉÈñ¶ÈÌ³°ÑÂ÷Èñ';
$query = sprintf("SELECT rep_kin FROM financial_report_data WHERE rep_ymd=%d and rep_note='%s'", $yyyymm, $note);
if ($rows=getResultWithField2($query, $field, $res) <= 0) {
    $gyomu_han_kin = 0;
} else {
    $gyomu_han_kin = $res[0][0];
}

// ¶ÈÌ³°ÑÂ÷Èñ¹ç·×
$gyomu_total_kin = $gyomu_seizo_kin + $gyomu_han_kin;

//// ½ôÀÇ¸ø²Ý
// À½Â¤ÈñÍÑ
$res   = array();
$field = array();
$rows  = array();
$syozei_seizo_kin = 0;
$note = 'À½Â¤·ÐÈñ½ôÀÇ¸ø²Ý';
$query = sprintf("SELECT SUM(kin) FROM profit_loss_keihi_history WHERE pl_bs_ym>=%d and pl_bs_ym<=%d and note='%s'", $str_ym, $end_ym, $note);
if ($rows=getResultWithField2($query, $field, $res) <= 0) {
    $syozei_seizo_kin = 0;
} else {
    $syozei_seizo_kin = $res[0][0];
}
// ÈÎ´ÉÈñ
$res   = array();
$field = array();
$rows  = array();
$syozei_han_kin = 0;
$note = 'ÈÎ´ÉÈñ½ôÀÇ¸ø²Ý';
$query = sprintf("SELECT rep_kin FROM financial_report_data WHERE rep_ymd=%d and rep_note='%s'", $yyyymm, $note);
if ($rows=getResultWithField2($query, $field, $res) <= 0) {
    $syozei_han_kin = 0;
} else {
    $syozei_han_kin = $res[0][0];
}

// ½ôÀÇ¸ø²Ý¹ç·×
$syozei_total_kin = $syozei_seizo_kin + $syozei_han_kin;

//// »î¸³¸¦µæÈñ
// À½Â¤ÈñÍÑ
$res   = array();
$field = array();
$rows  = array();
$shiken_seizo_kin = 0;
$note = 'À½Â¤·ÐÈñ»î¸³¸¦µæÈñ';
$query = sprintf("SELECT SUM(kin) FROM profit_loss_keihi_history WHERE pl_bs_ym>=%d and pl_bs_ym<=%d and note='%s'", $str_ym, $end_ym, $note);
if ($rows=getResultWithField2($query, $field, $res) <= 0) {
    $shiken_seizo_kin = 0;
} else {
    $shiken_seizo_kin = $res[0][0];
}
// ÈÎ´ÉÈñ
$shiken_han_kin = 0;

// »î¸³¸¦µæÈñ¹ç·×
$shiken_total_kin = $shiken_seizo_kin + $shiken_han_kin;

//// ½¤Á¶Èñ
// À½Â¤ÈñÍÑ
$res   = array();
$field = array();
$rows  = array();
$syuzen_seizo_kin = 0;
$note = 'À½Â¤·ÐÈñ½¤Á¶Èñ';
$query = sprintf("SELECT SUM(kin) FROM profit_loss_keihi_history WHERE pl_bs_ym>=%d and pl_bs_ym<=%d and note='%s'", $str_ym, $end_ym, $note);
if ($rows=getResultWithField2($query, $field, $res) <= 0) {
    $syuzen_seizo_kin = 0;
} else {
    $syuzen_seizo_kin = $res[0][0];
}
// ÈÎ´ÉÈñ
$res   = array();
$field = array();
$rows  = array();
$syuzen_han_kin = 0;
$note = 'ÈÎ´ÉÈñ½¤Á¶Èñ';
$query = sprintf("SELECT SUM(kin) FROM profit_loss_keihi_history WHERE pl_bs_ym>=%d and pl_bs_ym<=%d and note='%s'", $str_ym, $end_ym, $note);
if ($rows=getResultWithField2($query, $field, $res) <= 0) {
    $syuzen_han_kin = 0;
} else {
    $syuzen_han_kin = $res[0][0];
}

// ½¤Á¶Èñ¹ç·×
$syuzen_total_kin = $syuzen_seizo_kin + $syuzen_han_kin;

//// »öÌ³ÍÑ¾ÃÌ×ÉÊÈñ
// À½Â¤ÈñÍÑ
$res   = array();
$field = array();
$rows  = array();
$jimu_seizo_kin = 0;
$note = 'À½Â¤·ÐÈñ»öÌ³ÍÑ¾ÃÌ×ÉÊÈñ';
$query = sprintf("SELECT SUM(kin) FROM profit_loss_keihi_history WHERE pl_bs_ym>=%d and pl_bs_ym<=%d and note='%s'", $str_ym, $end_ym, $note);
if ($rows=getResultWithField2($query, $field, $res) <= 0) {
    $jimu_seizo_kin = 0;
} else {
    $jimu_seizo_kin = $res[0][0];
}
// ÈÎ´ÉÈñ
$res   = array();
$field = array();
$rows  = array();
$jimu_han_kin = 0;
$note = 'ÈÎ´ÉÈñ»öÌ³ÍÑ¾ÃÌ×ÉÊÈñ';
$query = sprintf("SELECT rep_kin FROM financial_report_data WHERE rep_ymd=%d and rep_note='%s'", $yyyymm, $note);
if ($rows=getResultWithField2($query, $field, $res) <= 0) {
    $jimu_han_kin = 0;
} else {
    $jimu_han_kin = $res[0][0];
}

// »öÌ³ÍÑ¾ÃÌ×ÉÊÈñ¹ç·×
$jimu_total_kin = $jimu_seizo_kin + $jimu_han_kin;

//// ¹©¾ìÍÑ¾ÃÌ×ÉÊÈñ
// À½Â¤ÈñÍÑ
$res   = array();
$field = array();
$rows  = array();
$kojyo_seizo_kin = 0;
$note = 'À½Â¤·ÐÈñ¹©¾ì¾ÃÌ×ÉÊÈñ';
$query = sprintf("SELECT SUM(kin) FROM profit_loss_keihi_history WHERE pl_bs_ym>=%d and pl_bs_ym<=%d and note='%s'", $str_ym, $end_ym, $note);
if ($rows=getResultWithField2($query, $field, $res) <= 0) {
    $kojyo_seizo_kin = 0;
} else {
    $kojyo_seizo_kin = $res[0][0];
}
// ÈÎ´ÉÈñ
$kojyo_han_kin = 0;

// ¹©¾ìÍÑ¾ÃÌ×ÉÊÈñ¹ç·×
$kojyo_total_kin = $kojyo_seizo_kin + $kojyo_han_kin;

//// ¼ÖíÒÈñ
// À½Â¤ÈñÍÑ
$res   = array();
$field = array();
$rows  = array();
$syaryo_seizo_kin = 0;
$note = 'À½Â¤·ÐÈñ¼ÖÎ¾Èñ';
$query = sprintf("SELECT SUM(kin) FROM profit_loss_keihi_history WHERE pl_bs_ym>=%d and pl_bs_ym<=%d and note='%s'", $str_ym, $end_ym, $note);
if ($rows=getResultWithField2($query, $field, $res) <= 0) {
    $syaryo_seizo_kin = 0;
} else {
    $syaryo_seizo_kin = $res[0][0];
}
// ÈÎ´ÉÈñ
$res   = array();
$field = array();
$rows  = array();
$syaryo_han_kin = 0;
$note = 'ÈÎ´ÉÈñ¼ÖÎ¾Èñ';
$query = sprintf("SELECT SUM(kin) FROM profit_loss_keihi_history WHERE pl_bs_ym>=%d and pl_bs_ym<=%d and note='%s'", $str_ym, $end_ym, $note);
if ($rows=getResultWithField2($query, $field, $res) <= 0) {
    $syaryo_han_kin = 0;
} else {
    $syaryo_han_kin = $res[0][0];
}

// ¼ÖíÒÈñ¹ç·×
$syaryo_total_kin = $syaryo_seizo_kin + $syaryo_han_kin;

//// ÊÝ¸±ÎÁ
// À½Â¤ÈñÍÑ
$res   = array();
$field = array();
$rows  = array();
$hoken_seizo_kin = 0;
$note = 'À½Â¤·ÐÈñÊÝ¸±ÎÁ';
$query = sprintf("SELECT SUM(kin) FROM profit_loss_keihi_history WHERE pl_bs_ym>=%d and pl_bs_ym<=%d and note='%s'", $str_ym, $end_ym, $note);
if ($rows=getResultWithField2($query, $field, $res) <= 0) {
    $hoken_seizo_kin = 0;
} else {
    $hoken_seizo_kin = $res[0][0];
}
// ÈÎ´ÉÈñ
$res   = array();
$field = array();
$rows  = array();
$hoken_han_kin = 0;
$note = 'ÈÎ´ÉÈñÊÝ¸±ÎÁ';
$query = sprintf("SELECT SUM(kin) FROM profit_loss_keihi_history WHERE pl_bs_ym>=%d and pl_bs_ym<=%d and note='%s'", $str_ym, $end_ym, $note);
if ($rows=getResultWithField2($query, $field, $res) <= 0) {
    $hoken_han_kin = 0;
} else {
    $hoken_han_kin = $res[0][0];
}

// ÊÝ¸±ÎÁ¹ç·×
$hoken_total_kin = $hoken_seizo_kin + $hoken_han_kin;

//// ¿åÆ»¸÷Ç®Èñ
// À½Â¤ÈñÍÑ
$res   = array();
$field = array();
$rows  = array();
$suido_seizo_kin = 0;
$note = 'À½Â¤·ÐÈñ¿åÆ»¸÷Ç®Èñ';
$query = sprintf("SELECT SUM(kin) FROM profit_loss_keihi_history WHERE pl_bs_ym>=%d and pl_bs_ym<=%d and note='%s'", $str_ym, $end_ym, $note);
if ($rows=getResultWithField2($query, $field, $res) <= 0) {
    $suido_seizo_kin = 0;
} else {
    $suido_seizo_kin = $res[0][0];
}
// ÈÎ´ÉÈñ
$res   = array();
$field = array();
$rows  = array();
$suido_han_kin = 0;
$note = 'ÈÎ´ÉÈñ¿åÆ»¸÷Ç®Èñ';
$query = sprintf("SELECT SUM(kin) FROM profit_loss_keihi_history WHERE pl_bs_ym>=%d and pl_bs_ym<=%d and note='%s'", $str_ym, $end_ym, $note);
if ($rows=getResultWithField2($query, $field, $res) <= 0) {
    $suido_han_kin = 0;
} else {
    $suido_han_kin = $res[0][0];
}

// ¿åÆ»¸÷Ç®Èñ¹ç·×
$suido_total_kin = $suido_seizo_kin + $suido_han_kin;

//// ÃÏÂå²ÈÄÂ
// À½Â¤ÈñÍÑ
$res   = array();
$field = array();
$rows  = array();
$yachin_seizo_kin = 0;
$note = 'À½Â¤·ÐÈñÃÏÂå²ÈÄÂ';
$query = sprintf("SELECT rep_kin FROM financial_report_data WHERE rep_ymd=%d and rep_note='%s'", $yyyymm, $note);
if ($rows=getResultWithField2($query, $field, $res) <= 0) {
    $yachin_seizo_kin = 0;
} else {
    $yachin_seizo_kin = $res[0][0];
}
// ÈÎ´ÉÈñ
$res   = array();
$field = array();
$rows  = array();
$yachin_han_kin = 0;
$note = 'ÈÎ´ÉÈñÃÏÂå²ÈÄÂ';
$query = sprintf("SELECT rep_kin FROM financial_report_data WHERE rep_ymd=%d and rep_note='%s'", $yyyymm, $note);
if ($rows=getResultWithField2($query, $field, $res) <= 0) {
    $yachin_han_kin = 0;
} else {
    $yachin_han_kin = $res[0][0];
}

// ÃÏÂå²ÈÄÂ¹ç·×
$yachin_total_kin = $yachin_seizo_kin + $yachin_han_kin;

//// ´óÉÕ¶â
// À½Â¤ÈñÍÑ
$kifu_seizo_kin = 0;
// ÈÎ´ÉÈñ
$res   = array();
$field = array();
$rows  = array();
$kifu_han_kin = 0;
$note = 'ÈÎ´ÉÈñ´óÉÕ¶â';
$query = sprintf("SELECT SUM(kin) FROM profit_loss_keihi_history WHERE pl_bs_ym>=%d and pl_bs_ym<=%d and note='%s'", $str_ym, $end_ym, $note);
if ($rows=getResultWithField2($query, $field, $res) <= 0) {
    $kifu_han_kin = 0;
} else {
    $kifu_han_kin = $res[0][0];
}

// ´óÉÕ¶â¹ç·×
$kifu_total_kin = $kifu_seizo_kin + $kifu_han_kin;

//// ÄÂ¼ÚÎÁ
// À½Â¤ÈñÍÑ
$res   = array();
$field = array();
$rows  = array();
$chin_seizo_kin = 0;
$note = 'À½Â¤·ÐÈñÄÂ¼ÚÎÁ';
$query = sprintf("SELECT SUM(kin) FROM profit_loss_keihi_history WHERE pl_bs_ym>=%d and pl_bs_ym<=%d and note='%s'", $str_ym, $end_ym, $note);
if ($rows=getResultWithField2($query, $field, $res) <= 0) {
    $chin_seizo_kin = 0;
} else {
    $chin_seizo_kin = $res[0][0];
}
// ÈÎ´ÉÈñ
$res   = array();
$field = array();
$rows  = array();
$chin_han_kin = 0;
$note = 'ÈÎ´ÉÈñÄÂ¼ÚÎÁ';
$query = sprintf("SELECT SUM(kin) FROM profit_loss_keihi_history WHERE pl_bs_ym>=%d and pl_bs_ym<=%d and note='%s'", $str_ym, $end_ym, $note);
if ($rows=getResultWithField2($query, $field, $res) <= 0) {
    $chin_han_kin = 0;
} else {
    $chin_han_kin = $res[0][0];
}

// ÄÂ¼ÚÎÁ¹ç·×
$chin_total_kin = $chin_seizo_kin + $chin_han_kin;

//// »¨Èñ
// À½Â¤ÈñÍÑ
$res   = array();
$field = array();
$rows  = array();
$zappi_seizo_kin = 0;
$note = 'À½Â¤·ÐÈñ»¨Èñ';
$query = sprintf("SELECT rep_kin FROM financial_report_data WHERE rep_ymd=%d and rep_note='%s'", $yyyymm, $note);
if ($rows=getResultWithField2($query, $field, $res) <= 0) {
    $zappi_seizo_kin = 0;
} else {
    $zappi_seizo_kin = $res[0][0];
}
// ÈÎ´ÉÈñ
$res   = array();
$field = array();
$rows  = array();
$zappi_han_kin = 0;
$note = 'ÈÎ´ÉÈñ»¨Èñ';
$query = sprintf("SELECT rep_kin FROM financial_report_data WHERE rep_ymd=%d and rep_note='%s'", $yyyymm, $note);
if ($rows=getResultWithField2($query, $field, $res) <= 0) {
    $zappi_han_kin = 0;
} else {
    $zappi_han_kin = $res[0][0];
}

// »¨Èñ¹ç·×
$zappi_total_kin = $zappi_seizo_kin + $zappi_han_kin;

//// ¥¯¥ì¡¼¥àÂÐ±þÈñ
// À½Â¤ÈñÍÑ
$res   = array();
$field = array();
$rows  = array();
$clame_seizo_kin = 0;
$note = 'À½Â¤·ÐÈñ¥¯¥ì¡¼¥àÂÐ±þÈñ';
$query = sprintf("SELECT SUM(kin) FROM profit_loss_keihi_history WHERE pl_bs_ym>=%d and pl_bs_ym<=%d and note='%s'", $str_ym, $end_ym, $note);
if ($rows=getResultWithField2($query, $field, $res) <= 0) {
    $clame_seizo_kin = 0;
} else {
    $clame_seizo_kin = $res[0][0];
}
// ÈÎ´ÉÈñ
// À½Â¤ÈñÍÑ
$res   = array();
$field = array();
$rows  = array();
$clame_han_kin = 0;
$note = 'ÈÎ´ÉÈñ¥¯¥ì¡¼¥àÂÐ±þÈñ';
$query = sprintf("SELECT SUM(kin) FROM profit_loss_keihi_history WHERE pl_bs_ym>=%d and pl_bs_ym<=%d and note='%s'", $str_ym, $end_ym, $note);
if ($rows=getResultWithField2($query, $field, $res) <= 0) {
    $clame_han_kin = 0;
} else {
    $clame_han_kin = $res[0][0];
};

// ¥¯¥ì¡¼¥àÂÐ±þÈñ¹ç·×
$clame_total_kin = $clame_seizo_kin + $clame_han_kin;

//// ¸º²Á½þµÑÈñ
// À½Â¤ÈñÍÑ
$res   = array();
$field = array();
$rows  = array();
$genkasyo_seizo_kin = 0;
$note = 'À½Â¤·ÐÈñ¸º²Á½þµÑÈñ';
$query = sprintf("SELECT SUM(kin) FROM profit_loss_keihi_history WHERE pl_bs_ym>=%d and pl_bs_ym<=%d and note='%s'", $str_ym, $end_ym, $note);
if ($rows=getResultWithField2($query, $field, $res) <= 0) {
    $genkasyo_seizo_kin = 0;
} else {
    $genkasyo_seizo_kin = $res[0][0];
}
// ÈÎ´ÉÈñ
$res   = array();
$field = array();
$rows  = array();
$genkasyo_han_kin = 0;
$note = 'ÈÎ´ÉÈñ¸º²Á½þµÑÈñ';
$query = sprintf("SELECT SUM(kin) FROM profit_loss_keihi_history WHERE pl_bs_ym>=%d and pl_bs_ym<=%d and note='%s'", $str_ym, $end_ym, $note);
if ($rows=getResultWithField2($query, $field, $res) <= 0) {
    $genkasyo_han_kin = 0;
} else {
    $genkasyo_han_kin = $res[0][0];
}

// ¸º²Á½þµÑÈñ¹ç·×
$genkasyo_total_kin = $genkasyo_seizo_kin + $genkasyo_han_kin;

// À½Â¤·ÐÈñ¹ç·×
$seizo_keihi_total_kin = $ryohi_seizo_kin + $tsushin_seizo_kin + $kaigi_seizo_kin + $kosai_seizo_kin + $senden_seizo_kin + $nizukuri_seizo_kin + $tosyo_seizo_kin + $gyomu_seizo_kin + $syozei_seizo_kin + $shiken_seizo_kin + $syuzen_seizo_kin + $jimu_seizo_kin + $kojyo_seizo_kin + $syaryo_seizo_kin + $hoken_seizo_kin + $suido_seizo_kin + $yachin_seizo_kin + $kifu_seizo_kin + $chin_seizo_kin + $zappi_seizo_kin + $clame_seizo_kin + $genkasyo_seizo_kin;
// ·ÐÈñ¹ç·×
$han_keihi_total_kin   = $ryohi_han_kin + $tsushin_han_kin + $kaigi_han_kin + $kosai_han_kin + $senden_han_kin + $nizukuri_han_kin + $tosyo_han_kin + $gyomu_han_kin + $syozei_han_kin + $shiken_han_kin + $syuzen_han_kin + $jimu_han_kin + $kojyo_han_kin + $syaryo_han_kin + $hoken_han_kin + $suido_han_kin + $yachin_han_kin + $kifu_han_kin + $chin_han_kin + $zappi_han_kin + $clame_han_kin + $genkasyo_han_kin;
// Ï«Ì³Èñ¿Í·ïÈñ¹ç·×
$keihi_total_kin = $seizo_keihi_total_kin + $han_keihi_total_kin;

// À½Â¤·ÐÈñ¡¦·ÐÈñ¡ÊÈÎ´ÉÈñ¡Ëº¹³Û·×»»
// Á´ÂÎÀ½Â¤·ÐÈñ
$res   = array();
$field = array();
$rows  = array();
$seikei_as_kin = 0;
$sum1 = 'Á´ÂÎÀ½Â¤·ÐÈñ';
$query = sprintf("select SUM(kin) from profit_loss_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='%s'", $str_ym, $end_ym, $sum1);
if ($rows=getResultWithField2($query, $field, $res) <= 0) {
    $seikei_as_kin = 0;
} else {
    $seikei_as_kin = $res[0][0];
}
$seikei_as_sagaku = $seizo_keihi_total_kin - $seikei_as_kin;

// Á´ÂÎ·ÐÈñ¡ÊÈÎ´ÉÈñ¡Ë
$res   = array();
$field = array();
$rows  = array();
$hankei_as_kin = 0;
$sum1 = 'Á´ÂÎ·ÐÈñ';
$query = sprintf("select SUM(kin) from profit_loss_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='%s'", $str_ym, $end_ym, $sum1);
if ($rows=getResultWithField2($query, $field, $res) <= 0) {
    $hankei_as_kin = 0;
} else {
    $hankei_as_kin = $res[0][0];
}
$hankei_as_sagaku = $han_keihi_total_kin - $hankei_as_kin;


//// À½Â¤ÈñÍÑ¹ç·×
$seizo_hiyo_total_kin = $roumu_total_kin + $seizo_keihi_total_kin;
//// ÈÎ´ÉÈñ¹ç·×
$han_all_total_kin    = $jin_total_kin + $han_keihi_total_kin;
//// Áí·ÐÈñ¹ç·×
$all_keihi_total_kin  = $seizo_hiyo_total_kin+ $han_all_total_kin;

//// À½Â¤¸¶²ÁÊó¹ð½ñ
// ´ü¼óºàÎÁÃª²·¹â
$res   = array();
$field = array();
$rows  = array();
$kishu_zairyo_kin = 0;
$note = '´ü¼ó¸¶ºàÎÁµÚ¤ÓÃùÂ¢ÉÊ';
$query = sprintf("SELECT rep_kin FROM financial_report_data WHERE rep_ymd=%d and rep_note='%s'", $yyyymm, $note);
if ($rows=getResultWithField2($query, $field, $res) <= 0) {
    $kishu_zairyo_kin = 0;
} else {
    $kishu_zairyo_kin = $res[0][0];
}

// Åö´üºàÎÁ»ÅÆþ¹â
$res   = array();
$field = array();
$rows  = array();
$touki_shiire_kin = 0;
$note = 'Åö´üºàÎÁ»ÅÆþ¹â';
$query = sprintf("SELECT rep_kin FROM financial_report_data WHERE rep_ymd=%d and rep_note='%s'", $yyyymm, $note);
if ($rows=getResultWithField2($query, $field, $res) <= 0) {
    $touki_shiire_kin = 0;
} else {
    $touki_shiire_kin = $res[0][0];
}

// ºàÎÁ¹ç·×£± ´ü¼óºàÎÁ¡ÜÅö´üºàÎÁ»ÅÆþ
$zai_total_1 = $kishu_zairyo_kin + $touki_shiire_kin;

// ´üËöºàÎÁÃª²·¹â
$res   = array();
$field = array();
$rows  = array();
$kimatsu_zairyo_kin = 0;
$note = '´üËö¸¶ºàÎÁµÚ¤ÓÃùÂ¢ÉÊ';
$query = sprintf("SELECT rep_kin FROM financial_report_data WHERE rep_ymd=%d and rep_note='%s'", $yyyymm, $note);
if ($rows=getResultWithField2($query, $field, $res) <= 0) {
    $kimatsu_zairyo_kin = 0;
} else {
    $kimatsu_zairyo_kin = $res[0][0];
}

// ºàÎÁ¹ç·×£² ºàÎÁ¹ç·×£±¡Ý ´üËöºàÎÁ
$zai_total_2 = $zai_total_1 - $kimatsu_zairyo_kin;

//// Â¾´ªÄê¿¶ÂØ¹â·×»»
// Â¾´ªÄê¿¶ÂØ¹â¡Ê»ñ¡Ë6100 00 ¤ÈÂ¾´ªÄê¿¶ÂØ¹â¡ÊÀ½¡Ë6400 00 ¤È ¸¶²Áº¹°Û¡Ê£Ð£Ì¡Ë 6420 00 ¤Î¹ç·×¡ÊÉä¹æµÕ¡Ë
// Â¾´ªÄê¿¶ÂØ¹â¡Ê»ñ¡Ë6100 00
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

// Â¾´ªÄê¿¶ÂØ¹â¡ÊÀ½¡Ë6400 00
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

// ¸¶²Áº¹°Û¡Ê£Ð£Ì¡Ë 6420 00
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

// Â¾´ªÄê¿¶ÂØ¹â ·×
$takan_total_kin = $takan_shizai_kin + $takan_sei_kin + $gensai_pl_kin;

// Åö´üºàÎÁÈñ ·×
$touki_zairyo_total = $zai_total_2 - $takan_total_kin;

// Åö´üÁíÀ½Â¤ÈñÍÑ
$touki_total_seizo_hiyo = $touki_zairyo_total + $roumu_total_kin + $seizo_keihi_total_kin;

// ´ü¼ó»Å³ÝÉÊÃª²·¹â
$res   = array();
$field = array();
$rows  = array();
$kishu_shikakari_kin = 0;
$note = '´ü¼ó»Å³ÝÉÊ';
$query = sprintf("SELECT rep_kin FROM financial_report_data WHERE rep_ymd=%d and rep_note='%s'", $yyyymm, $note);
if ($rows=getResultWithField2($query, $field, $res) <= 0) {
    $kishu_shikakari_kin = 0;
} else {
    $kishu_shikakari_kin = $res[0][0];
}

// Åö´üÀ½Â¤·ÐÈñ¹ç·×
$toki_seizo_keihi_total = $touki_total_seizo_hiyo + $kishu_shikakari_kin;

// ´üËö»Å³ÝÉÊÃª²·¹â
$res   = array();
$field = array();
$rows  = array();
$kimatsu_shikakari_kin = 0;
$note = '´üËö»Å³ÝÉÊ';
$query = sprintf("SELECT rep_kin FROM financial_report_data WHERE rep_ymd=%d and rep_note='%s'", $yyyymm, $note);
if ($rows=getResultWithField2($query, $field, $res) <= 0) {
    $kimatsu_shikakari_kin = 0;
} else {
    $kimatsu_shikakari_kin = $res[0][0];
}

// Åö´üÀ½ÉÊÀ½Â¤¸¶²Á
$touki_seihin_seizo_genka = $toki_seizo_keihi_total - $kimatsu_shikakari_kin;

// Ãª²·»ñ»ºÉ¾²ÁÂ»¡ÊCR¡Ë6090 00
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

// Åö´üÀ½ÉÊÀ½Â¤¸¶²Áº¹³Û·×»»
// Á´ÂÎÇä¾å¸¶²Á AS¸¶²Á¤Î½¸·×
$res   = array();
$field = array();
$rows  = array();
$genka_as_kin = 0;
$sum1 = 'Á´ÂÎÇä¾å¸¶²Á';
$query = sprintf("select SUM(kin) from profit_loss_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='%s'", $str_ym, $end_ym, $sum1);
if ($rows=getResultWithField2($query, $field, $res) <= 0) {
    $genka_as_kin = 0;
} else {
    $genka_as_kin = $res[0][0];
}

$urigen_as_sagaku = $touki_seihin_seizo_genka - $genka_as_kin;


//// Â»±×·×»»½ñ
// Çä¾å¹â
// Á´ÂÎÇä¾å¹â
$res   = array();
$field = array();
$rows  = array();
$uriage_kin = 0;
$sum1 = 'Á´ÂÎÇä¾å¹â';
$query = sprintf("select SUM(kin) from profit_loss_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='%s'", $str_ym, $end_ym, $sum1);
if ($rows=getResultWithField2($query, $field, $res) <= 0) {
    $uriage_kin = 0;
} else {
    $uriage_kin = $res[0][0];
}

// Çä¾åÁíÍø±×¶â³Û
$uriage_sourieki_kin = $uriage_kin - $touki_seihin_seizo_genka;

// Çä¾åÁíÍø±×º¹³Û¡Ê¾å¤Î·è»»½ñÆâ¤Ç¤Î·×»»¤ÈASÄ¾ÀÜ¤Î¿ô»ú¤ÎÈæ³Ó¡Ë
// Á´ÂÎÇä¾åÁíÍø±×¡ÊASÄ¾ÀÜ¤Î¿ô»ú¡Ë
$res   = array();
$field = array();
$rows  = array();
$sourieki_as_kin = 0;
$sum1 = 'Á´ÂÎÇä¾åÁíÍø±×';
$query = sprintf("select SUM(kin) from profit_loss_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='%s'", $str_ym, $end_ym, $sum1);
if ($rows=getResultWithField2($query, $field, $res) <= 0) {
    $sourieki_as_kin = 0;
} else {
    $sourieki_as_kin = $res[0][0];
}
$sourieki_as_sagaku = $uriage_sourieki_kin - $sourieki_as_kin;

// ±Ä¶ÈÍø±×¶â³Û
$eigyo_rieki_kin = $uriage_sourieki_kin - $han_all_total_kin;

// ±Ä¶ÈÍø±×º¹³Û¡Ê¾å¤Î·è»»½ñÆâ¤Ç¤Î·×»»¤ÈASÄ¾ÀÜ¤Î¿ô»ú¤ÎÈæ³Ó¡Ë
// Á´ÂÎ±Ä¶ÈÍø±×¡ÊASÄ¾ÀÜ¤Î¿ô»ú¡Ë
$res   = array();
$field = array();
$rows  = array();
$eirieki_as_kin = 0;
$sum1 = 'Á´ÂÎ±Ä¶ÈÍø±×';
$query = sprintf("select SUM(kin) from profit_loss_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='%s'", $str_ym, $end_ym, $sum1);
if ($rows=getResultWithField2($query, $field, $res) <= 0) {
    $eirieki_as_kin = 0;
} else {
    $eirieki_as_kin = $res[0][0];
}
$eirieki_as_sagaku = $eigyo_rieki_kin - $eirieki_as_kin;

// ¼õ¼èÍøÂ© 9101 00
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

// °ÙÂØº¹±× ¢Í °ÙÂØº¹±× 9206 00¡ÊÉä¹æµÕ¡Ë¡Ý °ÙÂØº¹Â» 9303 00
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

// °ÙÂØº¹Â»±×·×»»
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

// ¸ÇÄê»ñ»ºÇäµÑ±× 9201 00
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

// »¨¼ýÆþ
$res   = array();
$field = array();
$rows  = array();
$zatsu_syu_kin = 0;
$note = '»¨¼ýÆþ';
$query = sprintf("SELECT rep_kin FROM financial_report_data WHERE rep_ymd=%d and rep_note='%s'", $yyyymm, $note);
if ($rows=getResultWithField2($query, $field, $res) <= 0) {
    $zatsu_syu_kin = 0;
} else {
    $zatsu_syu_kin = $res[0][0];
}

// ±Ä¶È³°¼ý±× ·×
$eigai_syueki_kin = $uketori_risoku_kin + $kawase_saeki_kin + $kotei_baieki_kin + $zatsu_syu_kin;

// Á´ÂÎ±Ä¶È³°¼ý±×·×º¹³Û¡Ê¾å¤Î·è»»½ñÆâ¤Ç¤Î·×»»¤ÈASÄ¾ÀÜ¤Î¿ô»ú¤ÎÈæ³Ó¡Ë
// Á´ÂÎ±Ä¶È³°¼ý±×·×¡ÊASÄ¾ÀÜ¤Î¿ô»ú¡Ë
$res   = array();
$field = array();
$rows  = array();
$gaisyu_as_kin = 0;
$sum1 = 'Á´ÂÎ±Ä¶È³°¼ý±×·×';
$query = sprintf("select SUM(kin) from profit_loss_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='%s'", $str_ym, $end_ym, $sum1);
if ($rows=getResultWithField2($query, $field, $res) <= 0) {
    $gaisyu_as_kin = 0;
} else {
    $gaisyu_as_kin = $res[0][0];
}
$gaisyu_as_sagaku = $eigai_syueki_kin - $gaisyu_as_kin;

// »ÙÊ§ÍøÂ© 8201
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

// ¸ÇÄê»ñ»º½üµÑÂ»
$res   = array();
$field = array();
$rows  = array();
$kotei_jyoson_kin = 0;
$note = '¸ÇÄê»ñ»º½üµÑÂ»';
$query = sprintf("SELECT rep_kin FROM financial_report_data WHERE rep_ymd=%d and rep_note='%s'", $yyyymm, $note);
if ($rows=getResultWithField2($query, $field, $res) <= 0) {
    $kotei_jyoson_kin = 0;
} else {
    $kotei_jyoson_kin = $res[0][0];
}
// ¸ÇÄê»ñ»ºÇäµÑÂ»
$res   = array();
$field = array();
$rows  = array();
$kotei_baison_kin = 0;
$note = '¸ÇÄê»ñ»ºÇäµÑÂ»';
$query = sprintf("SELECT rep_kin FROM financial_report_data WHERE rep_ymd=%d and rep_note='%s'", $yyyymm, $note);
if ($rows=getResultWithField2($query, $field, $res) <= 0) {
    $kotei_baison_kin = 0;
} else {
    $kotei_baison_kin = $res[0][0];
}

// ±Ä¶È³°ÈñÍÑ ·×
$eigai_hiyo_kin = $shiharai_risoku_kin + $kotei_jyoson_kin + $kotei_baison_kin + $kawase_sason_kin;

// Á´ÂÎ±Ä¶È³°ÈñÍÑ·×º¹³Û¡Ê¾å¤Î·è»»½ñÆâ¤Ç¤Î·×»»¤ÈASÄ¾ÀÜ¤Î¿ô»ú¤ÎÈæ³Ó¡Ë
// Á´ÂÎ±Ä¶È³°ÈñÍÑ·×¡ÊASÄ¾ÀÜ¤Î¿ô»ú¡Ë
$res   = array();
$field = array();
$rows  = array();
$gaihiyo_as_kin = 0;
$sum1 = 'Á´ÂÎ±Ä¶È³°ÈñÍÑ·×';
$query = sprintf("select SUM(kin) from profit_loss_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='%s'", $str_ym, $end_ym, $sum1);
if ($rows=getResultWithField2($query, $field, $res) <= 0) {
    $gaihiyo_as_kin = 0;
} else {
    $gaihiyo_as_kin = $res[0][0];
}
$gaihiyo_as_sagaku = $eigai_hiyo_kin - $gaihiyo_as_kin;

// ·Ð¾ïÍø±×¶â³Û
$keijyo_rieki_kin = $eigyo_rieki_kin + $eigai_syueki_kin - $eigai_hiyo_kin;

// Á´ÂÎ·Ð¾ïÍø±×º¹³Û¡Ê¾å¤Î·è»»½ñÆâ¤Ç¤Î·×»»¤ÈASÄ¾ÀÜ¤Î¿ô»ú¤ÎÈæ³Ó¡Ë
// Á´ÂÎ·Ð¾ïÍø±×¡ÊASÄ¾ÀÜ¤Î¿ô»ú¡Ë
$res   = array();
$field = array();
$rows  = array();
$keirieki_as_kin = 0;
$sum1 = 'Á´ÂÎ·Ð¾ïÍø±×';
$query = sprintf("select SUM(kin) from profit_loss_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='%s'", $str_ym, $end_ym, $sum1);
if ($rows=getResultWithField2($query, $field, $res) <= 0) {
    $keirieki_as_kin = 0;
} else {
    $keirieki_as_kin = $res[0][0];
}
$keirieki_as_sagaku = $keijyo_rieki_kin - $keirieki_as_kin;

// ÀÇ°úÁ°Åö´ü½ãÍø±×¶â³Û
$zeimae_jyunrieki_kin = $keijyo_rieki_kin;

// Á´ÂÎÀÇ°úÁ°½ãÍø±×¶â³Ûº¹³Û¡Ê¾å¤Î·è»»½ñÆâ¤Ç¤Î·×»»¤ÈASÄ¾ÀÜ¤Î¿ô»ú¤ÎÈæ³Ó¡Ë
// Á´ÂÎÀÇ°úÁ°½ãÍø±×¶â³Û¡ÊASÄ¾ÀÜ¤Î¿ô»ú¡Ë
$res   = array();
$field = array();
$rows  = array();
$zeimaerieki_as_kin = 0;
$sum1 = 'Á´ÂÎÀÇ°úÁ°½ãÍø±×¶â³Û';
$query = sprintf("select SUM(kin) from profit_loss_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='%s'", $str_ym, $end_ym, $sum1);
if ($rows=getResultWithField2($query, $field, $res) <= 0) {
    $zeimaerieki_as_kin = 0;
} else {
    $zeimaerieki_as_kin = $res[0][0];
}
$zeimaerieki_as_sagaku = $zeimae_jyunrieki_kin - $zeimaerieki_as_kin;

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

// Ë¡¿ÍÀÇ¡¢½»Ì±ÀÇµÚ¤Ó»ö¶ÈÀÇ
$hojin_jyumin_jigyo_kin = $hojin_jyumin_zei_kin + $jigyo_zei_kin;

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

// ÀÇ¶â¹ç·×¤Î·×»»
$hojin_zeito_total_kin = $hojin_jyumin_zei_kin + $jigyo_zei_kin + $hojin_chosei_kin;

// Åö´ü½ãÍø±×¶â³Û
$toki_jyunrieki_kin = $zeimae_jyunrieki_kin - $hojin_zeito_total_kin;

// Á´ÂÎÅö´ü½ãÍø±×¶â³Ûº¹³Û¡Ê¾å¤Î·è»»½ñÆâ¤Ç¤Î·×»»¤ÈASÄ¾ÀÜ¤Î¿ô»ú¤ÎÈæ³Ó¡Ë
// Á´ÂÎÅö´ü½ãÍø±×¶â³Û¡ÊASÄ¾ÀÜ¤Î¿ô»ú¡Ë
$res   = array();
$field = array();
$rows  = array();
$tokijyunrieki_as_kin = 0;
$sum1 = 'Á´ÂÎÅö´ü½ãÍø±×¶â³Û';
$query = sprintf("select SUM(kin) from profit_loss_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='%s'", $str_ym, $end_ym, $sum1);
if ($rows=getResultWithField2($query, $field, $res) <= 0) {
    $tokijyunrieki_as_kin = 0;
} else {
    $tokijyunrieki_as_kin = $res[0][0];
}
$tokijyunrieki_as_sagaku = $toki_jyunrieki_kin - $tokijyunrieki_as_kin;

//// ³ô¼ç»ñËÜÅùÊÑÆ°·×»»½ñ
// »ñËÜ¶â
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

// »ñËÜ¶â»Ä¹â
$shihon_kin = $shihon_kishu - $shihon_hendo;

//// »ñËÜ¾êÍ¾¶â
// »ñËÜ½àÈ÷¶â
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

// »ñËÜ½àÈ÷¶â»Ä¹â
$shihon_jyunbi_kin = $shihon_jyunbi_kishu - $shihon_jyunbi_hendo;

// ¤½¤ÎÂ¾»ñËÜ¾êÍ¾¶â
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

// ¤½¤ÎÂ¾»ñËÜ¾êÍ¾¶â»Ä¹â
$sonota_shihon_jyoyo_kin = $sonota_shihon_jyoyo_kishu - $sonota_shihon_jyoyo_hendo;

//¡Ú»ñËÜ¾êÍ¾¶â¡Û¹ç·×
$shihon_jyoyo_total_kishu = $shihon_jyunbi_kishu + $sonota_shihon_jyoyo_kishu;
$shihon_jyoyo_total_hendo = $shihon_jyunbi_hendo + $sonota_shihon_jyoyo_hendo;
$shihon_jyoyo_total_kin   = $shihon_jyunbi_kin + $sonota_shihon_jyoyo_kin;

//// Íø±×¾êÍ¾¶â
// Íø±×½àÈ÷¶â 4201 00
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

// ¤½¤ÎÂ¾Íø±×¾êÍ¾¶â 4213 00
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

// ¤½¤ÎÂ¾Íø±×¾êÍ¾¶â»Ä¹â
$sonota_rieki_jyoyo_kin = $sonota_rieki_jyoyo_kishu - $sonota_rieki_jyoyo_hendo;

// ·«±ÛÍø±×¾êÍ¾¶â 4204 00
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

// ·«±ÛÍø±×¾êÍ¾¶â»Ä¹â
$kuri_rieki_jyoyo_kin = $kuri_rieki_jyoyo_kishu + $kuri_rieki_jyoyo_hendo;

////¡ÚÍø±×¾êÍ¾¶â¡Û¹ç·×
$rieki_jyoyo_total_kishu = $sonota_rieki_jyoyo_kishu + $kuri_rieki_jyoyo_kishu;
$rieki_jyoyo_total_hendo = $sonota_rieki_jyoyo_hendo + $kuri_rieki_jyoyo_hendo;
$rieki_jyoyo_total_kin   = $sonota_rieki_jyoyo_kin + $kuri_rieki_jyoyo_kin;

////¡Ô½ã»ñ»º¹ç·×¡Õ
$jyun_shisan_total_kishu = $shihon_kishu + $shihon_jyoyo_total_kishu + $rieki_jyoyo_total_kishu;
$jyun_shisan_total_hendo = $shihon_hendo + $shihon_jyoyo_total_hendo + $rieki_jyoyo_total_hendo;
$jyun_shisan_total_kin   = $shihon_kin + $shihon_jyoyo_total_kin + $rieki_jyoyo_total_kin;

if (isset($_POST['input_data'])) {                          // Åö·î¥Ç¡¼¥¿¤ÎÅÐÏ¿
    ///////// ¹àÌÜ¤È¥¤¥ó¥Ç¥Ã¥¯¥¹¤Î´ØÏ¢ÉÕ¤±
    $item = array();
    $item[0]  = "Çä³Ý¶â";
    $item[1]  = "Á°Ê§ÈñÍÑ";
    $item[2]  = "·úÀß²¾´ªÄê";
    $item[3]  = "¥½¥Õ¥È¥¦¥¨¥¢¡¼";
    $item[4]  = "¤½¤ÎÂ¾Ìµ·Á¸ÇÄê»ñ»º";                       // »ÜÀßÍøÍÑ¸¢
    $item[5]  = "½¾¶È°÷Ä¹´üÂßÉÕ¶â";                         // Ä¹´üÂßÉÕ¶â
    $item[6]  = "Ä¹´üÁ°Ê§ÈñÍÑ";
    $item[7]  = "·«±äÀÇ¶â»ñ»º¡Ê¸ÇÄê¡Ë";
    $item[8]  = "Ì¤Ê§ÈñÍÑ";
    $item[9]  = "Ì¤Ê§Ë¡¿ÍÀÇÅù";
    $item[10] = "ÍÂ¤ê¶â";
    $item[11] = "¥ê¡¼¥¹ºÄÌ³¡ÊÃ»´ü¡Ë";
    $item[12] = "¥ê¡¼¥¹ºÄÌ³¡ÊÄ¹´ü¡Ë";
    $item[13] = "¾ÞÍ¿°úÅö¶â";
    $item[14] = "Ä¹´üÌ¤Ê§¶â";
    $item[15] = "Âà¿¦µëÉÕ°úÅö¶â";
    $item[16] = "»ñËÜ¶â";
    $item[17] = "»ñËÜ¾êÍ¾¶â";
    $item[18] = "Íø±×¾êÍ¾¶â";
    $item[19] = "Çä¾å¹â";
    $item[20] = "Åö´üÀ½ÉÊÀ½Â¤¸¶²Á";
    $item[21] = "eca±¿ÄÂ²ÙÂ¤Èñ¡Ê²ÙÂ¤È¯Á÷Èñ¡Ë";              // ÈÎ´ÉÈñ
    $item[22] = "ecaÌò°÷Êó½·";                              // ÈÎ´ÉÈñ
    $item[23] = "ecaµëÎÁ";                                  // ÈÎ´ÉÈñ
    $item[24] = "eca¾ÞÍ¿";                                  // ÈÎ´ÉÈñ¾ÞÍ¿¼êÅö
    $item[25] = "eca¾ÞÍ¿°úÅö¶â·«Æþ";                        // ÈÎ´ÉÈñ¾ÞÍ¿°úÅö¶â·«Æþ
    $item[26] = "eca¸ÜÌäÎÁ";                                // ÈÎ´ÉÈñ
    $item[27] = "ecaÂà¿¦µëÉÕÈñÍÑ";                          // ÈÎ´ÉÈñ
    $item[28] = "ecaÄÌ¿®Èñ";                                // ÈÎ´ÉÈñ
    $item[29] = "eca¸òÄÌÈñ";                                // ÈÎ´ÉÈñÎ¹Èñ¸òÄÌÈñ¤È³¤³°½ÐÄ¥Èñ
    $item[30] = "eca¸º²Á½þµÑÈñ";                            // ÈÎ´ÉÈñ
    $item[31] = "ecaÁÅÀÇ¸ø²Ý";                              // ÈÎ´ÉÈñ½ôÀÇ¸ø²Ý
    $item[32] = "ecaÄÂ¼ÚÎÁ";                                // ÈÎ´ÉÈñ
    $item[33] = "eca½¤Á¶Èñ";                                // ÈÎ´ÉÈñ
    $item[34] = "eca¸òºÝÀÜÂÔÈñ";                            // ÈÎ´ÉÈñ
    $item[35] = "eca»öÌ³ÍÑ¾ÃÌ×ÉÊÈñ";                        // ÈÎ´ÉÈñ
    $item[36] = "ecaÊÝ¸±ÎÁ";                                // ÈÎ´ÉÈñ
    $item[37] = "eca¿åÆ»¸÷Ç®Èñ";                            // ÈÎ´ÉÈñ
    $item[38] = "eca¼ÖÎ¾Èñ";                                // ÈÎ´ÉÈñ
    $item[39] = "eca¿Þ½ñ¶µ°éÈñ";                            // ÈÎ´ÉÈñ
    $item[40] = "eca¹ØÆÉÎÁ¡¦´óÉÕ¶â";                        // ÈÎ´ÉÈñ
    $item[41] = "eca²ñµÄÈñ";                                // ÈÎ´ÉÈñ
    $item[42] = "eca¼õ¼èÍøÂ©µÚ¤Ó³ä°úÎÁ";                    // ÈÎ´ÉÈñ
    $item[43] = "eca°ÙÂØº¹±×";                              // ÈÎ´ÉÈñ
    $item[44] = "eca¸ÇÄê»ñ»ºÇäµÑ±×";                        // ÈÎ´ÉÈñ
    $item[45] = "ecaÅö´ü½ãÍø±×";                            // ÈÎ´ÉÈñ
    $item[46] = "eca°ÙÂØº¹Â»";                              // ÈÎ´ÉÈñ
    $item[47] = "Ë¡¿ÍÀÇÅùÄ´À°³Û";                           // ÈÎ´ÉÈñ
    $item[48] = "Íø±×¾êÍ¾¶â´ü¼ó¹â¹ç·×";                     // ÈÎ´ÉÈñ
    ///////// ³Æ¥Ç¡¼¥¿¤ÎÊÝ´É
    $input_data = array();
    $input_data[0]  = $urikake_kin;
    $input_data[1]  = $mae_hiyo_kin;
    $input_data[2]  = $kenkari_kin;
    $input_data[3]  = $soft_shisan_kin;
    $input_data[4]  = $shisetsu_shisan_kin;                 // »ÜÀßÍøÍÑ¸¢
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
    ///////// ³Æ¥Ç¡¼¥¿¤ÎÅÐÏ¿
    
    insert_date($item,$yyyymm,$input_data);
}

$csv_num = count($csv_data);
for ($r=0; $r<$csv_num; $r++) {
    $csv_data[$r][0] = mb_convert_encoding($csv_data[$r][0], 'SJIS', 'auto');   // CSVÍÑ¤ËEUC¤«¤éSJIS¤ØÊ¸»ú¥³¡¼¥ÉÊÑ´¹
}
/*
// eCAÍÑ CSV¥Ç¡¼¥¿¤Î½ÐÎÏ
// ¤³¤³¤«¤é¤¬CSV¥Õ¥¡¥¤¥ë¤ÎºîÀ®¡Ê°ì»þ¥Õ¥¡¥¤¥ë¤ò¥µ¡¼¥Ð¡¼¤ËºîÀ®¡Ë
$outputFile = 'eca_data.csv';
$fp = fopen($outputFile, "w");
foreach($csv_data as $line){
    fputcsv($fp,$line);         // ¤³¤³¤ÇCSV¥Õ¥¡¥¤¥ë¤Ë½ñ¤­½Ð¤·
}
fclose($fp);
// ¤³¤³¤«¤é¤¬CSV¥Õ¥¡¥¤¥ë¤Î¥À¥¦¥ó¥í¡¼¥É¡Ê¥µ¡¼¥Ð¡¼¢ª¥¯¥é¥¤¥¢¥ó¥È¡Ë
touch($outputFile);
header("Content-Type: application/csv");
header("Content-Disposition: attachment; filename=".$outputFile);
header("Content-Length:".filesize($outputFile));
readfile($outputFile);
unlink("{$outputFile}");         // ¥À¥¦¥ó¥í¡¼¥É¸å¥Õ¥¡¥¤¥ë¤òºï½ü
*/

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
        <table class='winbox_field' width='100%' align='center' bgcolor='black' cellspacing='0' cellpadding='5'>
            <THEAD>
                <!-- ¥Æ¡¼¥Ö¥ë ¥Ø¥Ã¥À¡¼¤ÎÉ½¼¨ -->
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' colspan='3' align='center'>
                        <div class='pt10b'>»ñ»º¤ÎÉô</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' colspan='3' align='center'>
                        <div class='pt10b'>ÉéºÄµÚ¤Ó½ã»ñ»º¤ÎÉô</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none;border-right:none;border-top:none'>
                        <div class='pt11b'>¡¡</div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' colspan='2' align='center'>
                        <div class='pt10b'>²ÊÌÜ</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center'>
                        <div class='pt10b'>¶â³Û</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' colspan='2' align='center'>
                        <div class='pt10b'>²ÊÌÜ</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center'>
                        <div class='pt10b'>¶â³Û</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none;border-right:none'>
                        <div class='pt11b'>¡¡</div>
                    </td>
                </tr>
            </THEAD>
            <TFOOT>
                <!-- ¸½ºß¤Ï¥Õ¥Ã¥¿¡¼¤Ï²¿¤â¤Ê¤¤ -->
            </TFOOT>
            <TBODY>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' colspan='2' style='border-bottom:none'>
                        <div class='pt10b'>Î®Æ°»ñ»º</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none'>
                        <div class='pt11b'>(<?= number_format($ryudo_total_kin) ?>)</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' colspan='2' style='border-bottom:none'>
                        <div class='pt10b'>ÉéºÄ¤ÎÉô</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none'>
                        <div class='pt11b'>¡¡</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none;border-right:none'>
                        <div class='pt11b'>¡¡</div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none;border-right:none'>
                        <div class='pt11b'>¡¡</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' style='border-bottom:none'>
                        <div class='pt10b'>¸½¶âµÚ¤ÓÍÂ¶â</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none'>
                        <div class='pt11b'><?= number_format($genkin_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' colspan='2' style='border-bottom:none'>
                        <div class='pt10b'>Î®Æ°ÉéºÄ</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none'>
                        <div class='pt11b'>(<?= number_format($ryudo_fusai_total_kin) ?>)</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none;border-right:none'>
                        <div class='pt11b'>¡¡</div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none;border-right:none'>
                        <div class='pt11b'>¡¡</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' style='border-bottom:none'>
                        <div class='pt10b'>Çä³Ý¶â</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none'>
                        <div class='pt11b'><?= number_format($urikake_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none;border-right:none'>
                        <div class='pt11b'>¡¡</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' style='border-bottom:none'>
                        <div class='pt10b'>Çã³Ý¶â</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none'>
                        <div class='pt11b'><?= number_format($kaikake_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none;border-right:none'>
                        <div class='pt11b'>¡¡</div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none;border-right:none'>
                        <div class='pt11b'>¡¡</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' style='border-bottom:none'>
                        <div class='pt10b'>»Å³ÝÉÊ</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none'>
                        <div class='pt11b'><?= number_format($tai_shikakari_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none;border-right:none'>
                        <div class='pt11b'>¡¡</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' style='border-bottom:none'>
                        <div class='pt10b'>¥ê¡¼¥¹ºÄÌ³</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none'>
                        <div class='pt11b'><?= number_format($lease_tanki_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none;border-right:none'>
                        <div class='pt11b'>¡¡</div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none;border-right:none'>
                        <div class='pt11b'>¡¡</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' style='border-bottom:none'>
                        <div class='pt10b'>¸¶ºàÎÁµÚ¤ÓÃùÂ¢ÉÊ</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none'>
                        <div class='pt11b'><?= number_format($tai_zairyo_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none;border-right:none'>
                        <div class='pt11b'>¡¡</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' style='border-bottom:none'>
                        <div class='pt10b'>Ì¤Ê§¶â</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none'>
                        <div class='pt11b'><?= number_format($miharai_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none;border-right:none'>
                        <div class='pt11b'>¡¡</div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none;border-right:none'>
                        <div class='pt11b'>¡¡</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' style='border-bottom:none'>
                        <div class='pt10b'>Á°Ê§ÈñÍÑ</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none'>
                        <div class='pt11b'><?= number_format($mae_hiyo_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none;border-right:none'>
                        <div class='pt11b'>¡¡</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' style='border-bottom:none'>
                        <div class='pt10b'>Ì¤Ê§¾ÃÈñÀÇÅù</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none'>
                        <div class='pt11b'><?= number_format($miharai_shozei_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none;border-right:none'>
                        <div class='pt11b'>¡¡</div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none;border-right:none'>
                        <div class='pt11b'>¡¡</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' style='border-bottom:none'>
                        <div class='pt10b'>Ì¤¼ýÆþ¶â</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none'>
                        <div class='pt11b'><?= number_format($mishu_kin_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none;border-right:none'>
                        <div class='pt11b'>¡¡</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' style='border-bottom:none'>
                        <div class='pt10b'>Ì¤Ê§Ë¡¿ÍÀÇÅù</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none'>
                        <div class='pt11b'><?= number_format($miharai_hozei_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none;border-right:none'>
                        <div class='pt11b'>¡¡</div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none;border-right:none'>
                        <div class='pt11b'>¡¡</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' style='border-bottom:none'>
                        <div class='pt10b'>Ì¤¼ý¾ÃÈñÀÇÅù</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none'>
                        <div class='pt11b'><?= number_format($mishu_shozei_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' colspan='2' style='border-bottom:none'>
                        <div class='pt11b'>¡¡</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none'>
                        <div class='pt11b'>¡¡</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none;border-right:none'>
                        <div class='pt11b'>¡¡</div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none;border-right:none'>
                        <div class='pt11b'>¡¡</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' style='border-bottom:none'>
                        <div class='pt10b'>¤½¤ÎÂ¾¤ÎÎ®Æ°»ñ»º</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none'>
                        <div class='pt11b'><?= number_format($ta_ryudo_shisan_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none;border-right:none'>
                        <div class='pt11b'>¡¡</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' style='border-bottom:none'>
                        <div class='pt10b'>Ì¤Ê§ÈñÍÑ</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none'>
                        <div class='pt11b'><?= number_format($miharai_hiyo_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none;border-right:none'>
                        <div class='pt11b'>¡¡</div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' colspan='2' style='border-bottom:none'>
                        <div class='pt10b'>¡¡</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none'>
                        <div class='pt11b'>¡¡</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none;border-right:none'>
                        <div class='pt11b'>¡¡</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' style='border-bottom:none'>
                        <div class='pt10b'>ÍÂ¤ê¶â</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none'>
                        <div class='pt11b'><?= number_format($azukari_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none;border-right:none'>
                        <div class='pt11b'>¡¡</div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' colspan='2' style='border-bottom:none'>
                        <div class='pt10b'>¡¡</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none'>
                        <div class='pt11b'>¡¡</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none;border-right:none'>
                        <div class='pt11b'>¡¡</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' style='border-bottom:none'>
                        <div class='pt10b'>¾ÞÍ¿°úÅö¶â</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none'>
                        <div class='pt11b'><?= number_format($syoyo_hikiate_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none;border-right:none'>
                        <div class='pt11b'>¡¡</div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' colspan='2' style='border-bottom:none'>
                        <div class='pt10b'>¡¡</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none'>
                        <div class='pt11b'>¡¡</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' colspan='2' style='border-bottom:none'>
                        <div class='pt10b'>¡¡</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none'>
                        <div class='pt11b'>¡¡</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none;border-right:none'>
                        <div class='pt11b'>¡¡</div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' colspan='2' style='border-bottom:none'>
                        <div class='pt10b'>¸ÇÄê»ñ»º</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none'>
                        <div class='pt11b'>(<?= number_format($kotei_shisan_total_kin) ?>)</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' colspan='2' style='border-bottom:none'>
                        <div class='pt10b'>¸ÇÄêÉéºÄ</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none'>
                        <div class='pt11b'>(<?= number_format($kotei_fusai_kin) ?>)</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none;border-right:none'>
                        <div class='pt11b'>¡¡</div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' colspan='2' style='border-bottom:none'>
                        <div class='pt10b'>Í­·Á¸ÇÄê»ñ»º</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none'>
                        <div class='pt11b'>(<?= number_format($yukei_shisan_kin) ?>)</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none;border-right:none'>
                        <div class='pt11b'>¡¡</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' style='border-bottom:none'>
                        <div class='pt10b'>¥ê¡¼¥¹ºÄÌ³</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none'>
                        <div class='pt11b'><?= number_format($lease_choki_kin) ?>    </div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none;border-right:none'>
                        <div class='pt11b'>¡¡</div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none;border-right:none'>
                        <div class='pt11b'>¡¡</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' style='border-bottom:none'>
                        <div class='pt10b'>·úÊª</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none'>
                        <div class='pt11b'><?= number_format($tatemono_shisan_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none;border-right:none'>
                        <div class='pt11b'>¡¡</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' style='border-bottom:none'>
                        <div class='pt10b'>Ä¹´üÌ¤Ê§¶â</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none'>
                        <div class='pt11b'><?= number_format($choki_miharai_kin) ?>    </div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none;border-right:none'>
                        <div class='pt11b'>¡¡</div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none;border-right:none'>
                        <div class='pt11b'>¡¡</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' style='border-bottom:none'>
                        <div class='pt10b'>µ¡³£µÚ¤ÓÁõÃÖ</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none'>
                        <div class='pt11b'><?= number_format($kikai_shisan_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none;border-right:none'>
                        <div class='pt11b'>¡¡</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' style='border-bottom:none'>
                        <div class='pt10b'>Âà¿¦µëÉÕ°úÅö¶â</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none'>
                        <div class='pt11b'><?= number_format($taisyoku_hikiate_kin) ?>    </div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none;border-right:none'>
                        <div class='pt11b'>¡¡</div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none;border-right:none'>
                        <div class='pt11b'>¡¡</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' style='border-bottom:none'>
                        <div class='pt10b'>¼ÖíÒ±¿ÈÂ¶ñ</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none'>
                        <div class='pt11b'><?= number_format($sharyo_shisan_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' colspan='2' style='border-bottom:none'>
                        <div class='pt11b'>¡¡</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none'>
                        <div class='pt11b'>¡¡</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none;border-right:none'>
                        <div class='pt11b'>¡¡</div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none;border-right:none'>
                        <div class='pt11b'>¡¡</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' style='border-bottom:none'>
                        <div class='pt10b'>¹©¶ñ´ï¶ñµÚ¤ÓÈ÷ÉÊ</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none'>
                        <div class='pt11b'><?= number_format($kougu_shisan_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none;border-right:none'>
                        <div class='pt11b'>¡¡</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' style='border-bottom:none'>
                        <div class='pt10b'>ÉéºÄ¹ç·×</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none'>
                        <div class='pt11b'><?= number_format($fusai_total_kin) ?>    </div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none;border-right:none'>
                        <div class='pt11b'>¡¡</div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none;border-right:none'>
                        <div class='pt11b'>¡¡</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' style='border-bottom:none'>
                        <div class='pt10b'>¥ê¡¼¥¹»ñ»º</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none'>
                        <div class='pt11b'><?= number_format($lease_shisan_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' colspan='2' style='border-bottom:none'>
                        <div class='pt11b'>¡¡</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none'>
                        <div class='pt11b'>¡¡</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none;border-right:none'>
                        <div class='pt11b'>¡¡</div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none;border-right:none'>
                        <div class='pt11b'>¡¡</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' style='border-bottom:none'>
                        <div class='pt10b'>·úÀß²¾´ªÄê</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none'>
                        <div class='pt11b'><?= number_format($kenkari_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' colspan='2' style='border-bottom:none'>
                        <div class='pt11b'>¡¡</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none'>
                        <div class='pt11b'>¡¡</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none;border-right:none'>
                        <div class='pt11b'>¡¡</div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' colspan='2' style='border-bottom:none'>
                        <div class='pt10b'>¡¡</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none'>
                        <div class='pt11b'>¡¡</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' colspan='2' style='border-bottom:none'>
                        <div class='pt10b'>¡¡</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none'>
                        <div class='pt11b'>¡¡</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none;border-right:none'>
                        <div class='pt11b'>¡¡</div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' colspan='2' style='border-bottom:none'>
                        <div class='pt10b'>Ìµ·Á¸ÇÄê»ñ»º</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none'>
                        <div class='pt11b'>(<?= number_format($mukei_shisan_kin) ?>)</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' colspan='2' style='border-bottom:none'>
                        <div class='pt10b'>½ã»ñ»º¤ÎÉô</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none'>
                        <div class='pt11b'>¡¡</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none;border-right:none'>
                        <div class='pt11b'>¡¡</div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none;border-right:none'>
                        <div class='pt11b'>¡¡</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' style='border-bottom:none'>
                        <div class='pt10b'>ÅÅÏÃ²ÃÆþ¸¢</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none'>
                        <div class='pt11b'><?= number_format($denwa_shisan_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' colspan='2' style='border-bottom:none'>
                        <div class='pt10b'>³ô¼ç»ñËÜ</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none'>
                        <div class='pt11b'>¡¡</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none;border-right:none'>
                        <div class='pt11b'>¡¡</div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none;border-right:none'>
                        <div class='pt11b'>¡¡</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' style='border-bottom:none'>
                        <div class='pt10b'>»ÜÀßÍøÍÑ¸¢</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none'>
                        <div class='pt11b'><?= number_format($shisetsu_shisan_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' colspan='2' style='border-bottom:none'>
                        <div class='pt10b'>»ñËÜ¶â</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none'>
                        <div class='pt11b'>(<?= number_format($shihon_total_kin) ?>)</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none;border-right:none'>
                        <div class='pt11b'>¡¡</div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none;border-right:none'>
                        <div class='pt11b'>¡¡</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' style='border-bottom:none'>
                        <div class='pt10b'>¥½¥Õ¥È¥¦¥§¥¢</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none'>
                        <div class='pt11b'><?= number_format($soft_shisan_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none;border-right:none'>
                        <div class='pt11b'>¡¡</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' style='border-bottom:none'>
                        <div class='pt10b'>»ñËÜ¶â</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none'>
                        <div class='pt11b'><?= number_format($shihon_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none;border-right:none'>
                        <div class='pt11b'>¡¡</div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' colspan='2' style='border-bottom:none'>
                        <div class='pt11b'>¡¡</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none'>
                        <div class='pt11b'>¡¡</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' colspan='2' style='border-bottom:none'>
                        <div class='pt10b'>»ñËÜ¾êÍ¾¶â</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none'>
                        <div class='pt11b'>(<?= number_format($tai_shihon_jyoyo_total_kin) ?>)</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none;border-right:none'>
                        <div class='pt11b'>¡¡</div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' colspan='2' style='border-bottom:none'>
                        <div class='pt10b'>Åê»ñ¤½¤ÎÂ¾¤Î»ñ»º</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none'>
                        <div class='pt11b'>(<?= number_format($toshi_sonota_kin) ?>)</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none;border-right:none'>
                        <div class='pt11b'>¡¡</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' style='border-bottom:none'>
                        <div class='pt10b'>»ñËÜ½àÈ÷¶â</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none'>
                        <div class='pt11b'><?= number_format($shihon_jyunbi_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none;border-right:none'>
                        <div class='pt11b'>¡¡</div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none;border-right:none'>
                        <div class='pt11b'>¡¡</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' style='border-bottom:none'>
                        <div class='pt10b'>Ä¹´üÂßÉÕ¶â</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none'>
                        <div class='pt11b'><?= number_format($choki_kashi_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none;border-right:none'>
                        <div class='pt11b'>¡¡</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' style='border-bottom:none'>
                        <div class='pt10b'>¤½¤ÎÂ¾»ñËÜ¾êÍ¾¶â</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none'>
                        <div class='pt11b'><?= number_format($sonota_shihon_jyoyo_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none;border-right:none'>
                        <div class='pt11b'>¡¡</div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none;border-right:none'>
                        <div class='pt11b'>¡¡</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' style='border-bottom:none'>
                        <div class='pt10b'>Ä¹´üÁ°Ê§ÈñÍÑ</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none'>
                        <div class='pt11b'><?= number_format($choki_maebara_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' colspan='2' style='border-bottom:none'>
                        <div class='pt10b'>Íø±×¾êÍ¾¶â</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none'>
                        <div class='pt11b'>(<?= number_format($tai_rieki_jyoyo_total_kin) ?>)</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none;border-right:none'>
                        <div class='pt11b'>¡¡</div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none;border-right:none'>
                        <div class='pt11b'>¡¡</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' style='border-bottom:none'>
                        <div class='pt10b'>·«±äÀÇ¶â»ñ»º</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none'>
                        <div class='pt11b'><?= number_format($kotei_kuri_zei_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none;border-right:none'>
                        <div class='pt11b'>¡¡</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' style='border-bottom:none'>
                        <div class='pt10b'>¤½¤ÎÂ¾Íø±×¾êÍ¾¶â</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none'>
                        <div class='pt11b'><?= number_format($tai_sonota_rieki_jyoyo_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none;border-right:none'>
                        <div class='pt11b'>¡¡</div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none;border-right:none'>
                        <div class='pt11b'>¡¡</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' style='border-bottom:none'>
                        <div class='pt10b'>¤½¤ÎÂ¾¤ÎÅê»ñÅù</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none'>
                        <div class='pt11b'><?= number_format($sonota_toshi_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none;border-right:none'>
                        <div class='pt11b'>¡¡</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' style='border-bottom:none'>
                        <div class='pt10b'>·«±ÛÍø±×¾êÍ¾¶â</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none'>
                        <div class='pt11b'><?= number_format($tai_kuri_rieki_jyoyo_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none;border-right:none'>
                        <div class='pt11b'>¡¡</div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' colspan='2' style='border-bottom:none'>
                        <div class='pt10b'>¡¡</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none'>
                        <div class='pt11b'>¡¡</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' colspan='2' style='border-bottom:none'>
                        <div class='pt10b'>¡¡</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none'>
                        <div class='pt11b'>¡¡</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none;border-right:none'>
                        <div class='pt11b'>¡¡</div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' colspan='2' style='border-bottom:none'>
                        <div class='pt11b'>¡¡</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none'>
                        <div class='pt11b'>¡¡</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none;border-right:none'>
                        <div class='pt11b'>¡¡</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' style='border-bottom:none'>
                        <div class='pt10b'>½ã»ñ»º¹ç·×</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none'>
                        <div class='pt11b'><?= number_format($tai_jyun_shisan_total_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none;border-right:none'>
                        <div class='pt11b'>¡¡</div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' colspan='2'>
                        <div class='pt10b'>¡¡</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right'>
                        <div class='pt11b'>¡¡</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' colspan='2'>
                        <div class='pt10b'>¡¡</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right'>
                        <div class='pt11b'>¡¡</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-top:none;border-right:none'>
                        º¹³Û
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' colspan='2'>
                        <div class='pt10b'>»ñ»º¹ç·×</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right'>
                        <div class='pt11b'><?= number_format($shisan_total_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' colspan='2'>
                        <div class='pt10b'>ÉéºÄµÚ¤Ó½ã»ñ»º¹ç·×</div>
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
        </table> <!----------------- ¥À¥ß¡¼End ------------------>
        
        <table bgcolor='#ffffff' align='center' cellspacing='0' cellpadding='10'>
            <tr><td> <!----------- ¥À¥ß¡¼(¥Ç¥¶¥¤¥óÍÑ) ------------>
        <center>¡ÊÂ»±×·×»»½ñ¡Ë</center>
        <table class='winbox_field' width='100%' align='center' bgcolor='black' cellspacing='0' cellpadding='5'>
            <THEAD>
                <!-- ¥Æ¡¼¥Ö¥ë ¥Ø¥Ã¥À¡¼¤ÎÉ½¼¨ -->
            </THEAD>
            <TFOOT>
                <!-- ¸½ºß¤Ï¥Õ¥Ã¥¿¡¼¤Ï²¿¤â¤Ê¤¤ -->
            </TFOOT>
            <TBODY>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='left' colspan='5' style='border-bottom:none'>
                        <div class='pt10b'>­µ¡¥±Ä  ¶È  ¼ý  ±×</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none;border-right:none;border-top:none'>
                        <div class='pt11b'>¡¡</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none;border-right:none;border-top:none'>
                        <div class='pt11b'>¡¡</div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none;border-right:none'>
                        <div class='pt10b'>¡¡</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='left' style='border-bottom:none;border-right:none'>
                        <div class='pt10b'>¡¡Çä  ¾å  ¹â</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none;border-right:none'>
                        <div class='pt10b'>¡¡</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none;border-right:none'>
                        <div class='pt10b'>¡¡</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none'>
                        <div class='pt11b'><?= number_format($uriage_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none;border-right:none;border-top:none'>
                        <div class='pt11b'>¡¡</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none;border-right:none;border-top:none'>
                        <div class='pt11b'>¡¡</div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='left' colspan='5' style='border-bottom:none'>
                        <div class='pt10b'>­¶¡¥±Ä  ¶È  Èñ  ÍÑ</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none;border-right:none;border-top:none'>
                        <div class='pt11b'>¡¡</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none;border-right:none;border-top:none'>
                        <div class='pt11b'>¡¡</div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none;border-right:none'>
                        <div class='pt10b'>¡¡</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='left' style='border-bottom:none;border-right:none'>
                        <div class='pt10b'>£±¡¥Çä  ¾å  ¸¶  ²Á</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none;border-right:none'>
                        <div class='pt10b'>¡¡</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none;border-right:none'>
                        <div class='pt10b'>¡¡</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none'>
                        <div class='pt10b'>¡¡</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none;border-right:none;border-top:none'>
                        <div class='pt11b'>¡¡</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none;border-right:none;border-top:none'>
                        <div class='pt11b'>¡¡</div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none;border-right:none'>
                        <div class='pt10b'>¡¡</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none;border-right:none'>
                        <div class='pt10b'>¡¡Åö´üÀ½ÉÊÀ½Â¤¸¶²Á</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-right:none;border-bottom:1pt solid windowtext'>
                        <div class='pt11b'><?= number_format($touki_seihin_seizo_genka) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none;border-right:none'>
                        <div class='pt10b'>¡¡</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:1pt solid windowtext'>
                        <div class='pt11b'><?= number_format($touki_seihin_seizo_genka) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-right:none;border-top:none'>
                        <div class='pt11b'>¡¡</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none;border-right:none;border-top:none'>
                        <div class='pt11b'>¡¡</div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none;border-right:none'>
                        <div class='pt10b'>¡¡</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none;border-right:none'>
                        <div class='pt10b'>¡¡Çä¾åÁíÍø±×¶â³Û</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none;border-right:none'>
                        <div class='pt10b'>¡¡</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none;border-right:none'>
                        <div class='pt10b'>¡¡</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none'>
                        <div class='pt11b'><?= number_format($uriage_sourieki_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right'>
                        <div class='pt11b'><?= number_format($sourieki_as_sagaku) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none;border-right:none;border-top:none'>
                        <div class='pt11b'>¡¡</div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none;border-right:none'>
                        <div class='pt10b'>¡¡</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='left' style='border-bottom:none;border-right:none'>
                        <div class='pt10b'>£²¡¥ÈÎÇäÈñµÚ¤Ó°ìÈÌ´ÉÍýÈñ</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none;border-right:none'>
                        <div class='pt10b'>¡¡</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none;border-right:none'>
                        <div class='pt10b'>¡¡</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:1pt solid windowtext'>
                        <div class='pt11b'><?= number_format($han_all_total_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-right:none'>
                        <div class='pt11b'>¡¡</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none;border-right:none;border-top:none'>
                        <div class='pt11b'>¡¡</div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none;border-right:none'>
                        <div class='pt10b'>¡¡</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none;border-right:none'>
                        <div class='pt10b'>¡¡±Ä ¶È Íø ±× ¶â ³Û</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none;border-right:none'>
                        <div class='pt10b'>¡¡</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none;border-right:none'>
                        <div class='pt10b'>¡¡</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none'>
                        <div class='pt11b'><?= number_format($eigyo_rieki_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right'>
                        <div class='pt11b'><?= number_format($eirieki_as_sagaku) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none;border-right:none;border-top:none'>
                        <div class='pt11b'>¡¡</div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='left' colspan='5' style='border-bottom:none'>
                        <div class='pt10b'>­·¡¥±Ä  ¶È  ³°  ¼ý  ±×</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none;border-right:none;border-top:none'>
                        <div class='pt11b'>¡¡</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none;border-right:none;border-top:none'>
                        <div class='pt11b'>¡¡</div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none;border-right:none'>
                        <div class='pt10b'>¡¡</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='left' style='border-bottom:none;border-right:none'>
                        <div class='pt10b'>¼õ  ¼è  Íø  Â©</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none;border-right:none'>
                        <div class='pt11b'><?= number_format($uketori_risoku_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none;border-right:none'>
                        <div class='pt10b'>¡¡</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none'>
                        <div class='pt10b'>¡¡</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-right:none;border-top:none'>
                        <div class='pt11b'>¡¡</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none;border-right:none;border-top:none'>
                        <div class='pt11b'>¡¡</div>
                    </td>
                </tr>
                <?php
                if ($kawase_saeki_kin <> 0) {
                ?>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none;border-right:none'>
                        <div class='pt10b'>¡¡</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='left' style='border-bottom:none;border-right:none'>
                        <div class='pt10b'>°Ù¡¡ÂØ¡¡º¹¡¡±×</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none;border-right:none'>
                        <div class='pt11b'><?= number_format($kawase_saeki_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none;border-right:none'>
                        <div class='pt10b'>¡¡</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none'>
                        <div class='pt10b'>¡¡</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-right:none;border-top:none'>
                        <div class='pt10b'>¡¡</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none;border-right:none'>
                        <div class='pt10b'>¡¡</div>
                    </td>
                </tr>
                <?php
                }
                ?>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none;border-right:none'>
                        <div class='pt10b'>¡¡</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='left' style='border-bottom:none;border-right:none'>
                        <div class='pt10b'>¸ÇÄê»ñ»ºÇäµÑ±×</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none;border-right:none'>
                        <div class='pt11b'><?= number_format($kotei_baieki_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none;border-right:none'>
                        <div class='pt10b'>¡¡</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none'>
                        <div class='pt10b'>¡¡</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-right:none;border-top:none'>
                        <div class='pt10b'>¡¡</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none;border-right:none'>
                        <div class='pt10b'>¡¡</div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none;border-right:none'>
                        <div class='pt10b'>¡¡</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='left' style='border-bottom:none;border-right:none'>
                        <div class='pt10b'>»¨    ¼ý    Æþ</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-right:none;border-bottom:1pt solid windowtext'>
                        <div class='pt11b'><?= number_format($zatsu_syu_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none;border-right:none'>
                        <div class='pt10b'>¡¡</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none'>
                        <div class='pt11b'><?= number_format($eigai_syueki_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right'>
                        <div class='pt11b'><?= number_format($gaisyu_as_sagaku) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='left' style='border-bottom:none;border-right:none'>
                        ¢¨£±
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='left' colspan='5' style='border-bottom:none'>
                        <div class='pt10b'>­¸¡¥±Ä  ¶È  ³°  Èñ  ÍÑ</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none;border-right:none'>
                        <div class='pt11b'>¡¡</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none;border-right:none'>
                        <div class='pt11b'>¡¡</div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none;border-right:none'>
                        <div class='pt10b'>¡¡</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='left' style='border-bottom:none;border-right:none'>
                        <div class='pt10b'>»Ù¡¡Ê§¡¡Íø¡¡Â©</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none;border-right:none'>
                        <div class='pt11b'><?= number_format($shiharai_risoku_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none;border-right:none'>
                        <div class='pt10b'>¡¡</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none'>
                        <div class='pt10b'>¡¡</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none;border-right:none'>
                        <div class='pt11b'>¡¡</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none;border-right:none'>
                        <div class='pt11b'>¡¡</div>
                    </td>
                </tr>
                <?php
                if ($kawase_sason_kin <> 0) {
                ?>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none;border-right:none'>
                        <div class='pt10b'>¡¡</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='left' style='border-bottom:none;border-right:none'>
                        <div class='pt10b'>°Ù¡¡ÂØ¡¡º¹¡¡Â»</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none;border-right:none'>
                        <div class='pt11b'><?= number_format($kawase_sason_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none;border-right:none'>
                        <div class='pt10b'>¡¡</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none'>
                        <div class='pt10b'>¡¡</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none;border-right:none'>
                        <div class='pt11b'>¡¡</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none;border-right:none'>
                        <div class='pt11b'>¡¡</div>
                    </td>
                </tr>
                <?php
                }
                ?>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none;border-right:none'>
                        <div class='pt10b'>¡¡</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='left' style='border-bottom:none;border-right:none'>
                        <div class='pt10b'>¸ÇÄê»ñ»ºÇäµÑÂ»</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none;border-right:none'>
                        <div class='pt11b'><?= number_format($kotei_baison_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none;border-right:none'>
                        <div class='pt10b'>¡¡</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none'>
                        <div class='pt11b'>¡¡</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-right:none'>
                        <div class='pt11b'>¡¡</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-right:none;border-bottom:none'>
                        <div class='pt11b'>¡¡</div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none;border-right:none'>
                        <div class='pt10b'>¡¡</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='left' style='border-bottom:none;border-right:none'>
                        <div class='pt10b'>¸ÇÄê»ñ»º½üµÑÂ»</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-right:none;border-bottom:1pt solid windowtext'>
                        <div class='pt11b'><?= number_format($kotei_jyoson_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none;border-right:none'>
                        <div class='pt10b'>¡¡</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:1pt solid windowtext'>
                        <div class='pt11b'><?= number_format($eigai_hiyo_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right'>
                        <div class='pt11b'><?= number_format($gaihiyo_as_sagaku) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='left' style='border-bottom:none;border-right:none'>
                        ¢¨£±¤È°ìÃ×
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none;border-right:none'>
                        <div class='pt10b'>¡¡</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none;border-right:none'>
                        <div class='pt10b'>¡¡·Ð ¾ï Íø ±× ¶â ³Û</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none;border-right:none'>
                        <div class='pt10b'>¡¡</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none;border-right:none'>
                        <div class='pt10b'>¡¡</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:1pt solid windowtext'>
                        <div class='pt11b'><?= number_format($keijyo_rieki_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right'>
                        <div class='pt11b'><?= number_format($keirieki_as_sagaku) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-right:none;border-bottom:none'>
                        <div class='pt11b'>¡¡</div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='left' colspan='2' style='border-bottom:none;border-right:none'>
                        <div class='pt10b'>¡¡ÀÇ°úÁ°Åö´ü½ãÍø±×¶â³Û</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none;border-right:none'>
                        <div class='pt10b'>¡¡</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none;border-right:none'>
                        <div class='pt10b'>¡¡</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none'>
                        <div class='pt11b'><?= number_format($zeimae_jyunrieki_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right'>
                        <div class='pt11b'><?= number_format($zeimaerieki_as_sagaku) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-right:none;border-bottom:none'>
                        <div class='pt11b'>¡¡</div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='left' colspan='2' style='border-bottom:none;border-right:none'>
                        <div class='pt10b'>¡¡Ë¡¿ÍÀÇ¡¢½»Ì±ÀÇµÚ¤Ó»ö¶ÈÀÇ</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none;border-right:none'>
                        <div class='pt11b'><?= number_format($hojin_jyumin_jigyo_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none;border-right:none'>
                        <div class='pt10b'>¡¡</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none'>
                        <div class='pt10b'>¡¡</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-right:none;border-bottom:none'>
                        <div class='pt11b'>¡¡</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-right:none;border-bottom:none'>
                        <div class='pt11b'>¡¡</div>
                    </td>
                </tr>
                <!--
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='left' colspan='2'>
                        <div class='pt10b'>¡¡²áÇ¯ÅÙË¡¿ÍÀÇÅù</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right'>
                        <div class='pt11b'><?= number_format($kishu_zairyo_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center'>
                        <div class='pt10b'>¡¡</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center'>
                        <div class='pt10b'>¡¡</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-right:none'>
                        <div class='pt11b'>¡¡</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-right:none;border-bottom:none'>
                        <div class='pt11b'>¡¡</div>
                    </td>
                </tr>
                -->
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='left' colspan='2' style='border-bottom:none;border-right:none'>
                        <div class='pt10b'>¡¡Ë¡¿ÍÀÇÅùÄ´À°³Û</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-right:none;border-bottom:1pt solid windowtext'>
                        <div class='pt11b'><?= number_format($hojin_chosei_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none;border-right:none'>
                        <div class='pt10b'>¡¡</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:1pt solid windowtext'>
                        <div class='pt11b'><?= number_format($hojin_zeito_total_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-right:none'>
                        <div class='pt11b'>¡¡</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-right:none;border-bottom:none'>
                        <div class='pt11b'>¡¡</div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='left' colspan='2' style='border-right:none'>
                        <div class='pt10b'>¡¡Åö´ü½ãÍø±×¶â³Û</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-right:none'>
                        <div class='pt10b'>¡¡</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-right:none'>
                        <div class='pt10b'>¡¡</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right'>
                        <div class='pt11b'><?= number_format($toki_jyunrieki_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right'>
                        <div class='pt11b'><?= number_format($tokijyunrieki_as_sagaku) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-right:none;border-bottom:none'>
                        <div class='pt11b'>¡¡</div>
                    </td>
                </tr>
            </TBODY>
        </table>
            </td></tr>
        </table> <!----------------- ¥À¥ß¡¼End ------------------>
                
        <table bgcolor='#ffffff' align='center' cellspacing='0' cellpadding='10'>
            <tr><td> <!----------- ¥À¥ß¡¼(¥Ç¥¶¥¤¥óÍÑ) ------------>
        <center>¡ÊÀ½Â¤¸¶²ÁÊó¹ð½ñ¡Ë</center>
        <table class='winbox_field' width='100%' align='center' bgcolor='black' cellspacing='0' cellpadding='5'>
            <THEAD>
                <!-- ¥Æ¡¼¥Ö¥ë ¥Ø¥Ã¥À¡¼¤ÎÉ½¼¨ -->
            </THEAD>
            <TFOOT>
                <!-- ¸½ºß¤Ï¥Õ¥Ã¥¿¡¼¤Ï²¿¤â¤Ê¤¤ -->
            </TFOOT>
            <TBODY>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none;border-right:none'>
                        <div class='pt10b'>­µ¡¥ºà    ÎÁ    Èñ</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none;border-right:none'>
                        <div class='pt10b'>¡¡</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none;border-right:none'>
                        <div class='pt10b'>¡¡</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none'>
                        <div class='pt10b'>¡¡</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none;border-right:none'>
                        <div class='pt10b'>¡¡</div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none;border-right:none'>
                        <div class='pt10b'>¡¡¡¡´ü¼óºàÎÁÃª²·¹â</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none;border-right:none'>
                        <div class='pt10b'>¡¡</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none;border-right:none'>
                        <div class='pt11b'><?= number_format($kishu_zairyo_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none'>
                        <div class='pt10b'>¡¡</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none;border-right:none'>
                        <div class='pt10b'>¡¡</div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none;border-right:none'>
                        <div class='pt10b'>¡¡¡¡Åö´üºàÎÁ»ÅÆþ¹â</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none;border-right:none'>
                        <div class='pt10b'>¡¡</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-right:none;border-bottom:1pt solid windowtext'>
                        <div class='pt11b'><?= number_format($touki_shiire_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none'>
                        <div class='pt10b'>¡¡</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none;border-right:none'>
                        <div class='pt10b'>¡¡</div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none;border-right:none'>
                        <div class='pt10b'>¡¡¡¡¹ç      ·×</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none;border-right:none'>
                        <div class='pt10b'>¡¡</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none;border-right:none'>
                        <div class='pt11b'><?= number_format($zai_total_1) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none'>
                        <div class='pt10b'>¡¡</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none;border-right:none'>
                        <div class='pt10b'>¡¡</div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none;border-right:none'>
                        <div class='pt10b'>¡¡¡¡´üËöºàÎÁÃª²·¹â</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none;border-right:none'>
                        <div class='pt10b'>¡¡</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-right:none;border-bottom:1pt solid windowtext'>
                        <div class='pt11b'><?= number_format($kimatsu_zairyo_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none'>
                        <div class='pt10b'>¡¡</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none;border-right:none'>
                        <div class='pt10b'>¡¡</div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none;border-right:none'>
                        <div class='pt10b'>¡¡¡¡¹ç      ·×</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none;border-right:none'>
                        <div class='pt10b'>¡¡</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none;border-right:none'>
                        <div class='pt11b'><?= number_format($zai_total_2) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none'>
                        <div class='pt10b'>¡¡</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none;border-right:none'>
                        <div class='pt10b'>¡¡</div>
                    </td>
                </tr>
                <!--
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center'>
                        <div class='pt10b'>¡¡¡¡Ãª²·»ñ»ºÉ¾²ÁÂ»</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center'>
                        <div class='pt10b'>¡¡</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right'>
                        <div class='pt11b'>¡¡</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center'>
                        <div class='pt10b'>¡¡</div>
                    </td>
                </tr>
                -->
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none;border-right:none'>
                        <div class='pt10b'>¡¡¡¡Â¾´ªÄê¿¶ÂØ¹â</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none;border-right:none'>
                        <div class='pt10b'>¡¡</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-right:none;border-bottom:1pt solid windowtext'>
                        <div class='pt11b'><?= number_format($takan_total_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none'>
                        <div class='pt10b'>¡¡</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none;border-right:none'>
                        <div class='pt10b'>¡¡</div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none;border-right:none'>
                        <div class='pt10b'>¡¡¡¡Åö´üºàÎÁÈñ</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none;border-right:none'>
                        <div class='pt10b'>¡¡</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none;border-right:none'>
                        <div class='pt10b'>¢¨</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none'>
                        <div class='pt11b'><?= number_format($touki_zairyo_total) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none;border-right:none'>
                        <div class='pt10b'>¡¡</div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' colspan='4' style='border-bottom:none'>
                        <div class='pt10b'>¡¡</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none;border-right:none'>
                        <div class='pt10b'>¡¡</div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none;border-right:none'>
                        <div class='pt10b'>­¶¡¥Ï«    Ì³    Èñ</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none;border-right:none'>
                        <div class='pt10b'>¡¡</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none;border-right:none'>
                        <div class='pt10b'>¡¡</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none'>
                        <div class='pt11b'>¡¡</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none;border-right:none'>
                        <div class='pt10b'>¡¡</div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none;border-right:none'>
                        <div class='pt10b'>¡¡Åö´üÏ«Ì³Èñ</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none;border-right:none'>
                        <div class='pt10b'>¡¡</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none;border-right:none'>
                        <div class='pt10b'>¡¡</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none'>
                        <div class='pt11b'><?= number_format($roumu_total_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none;border-right:none'>
                        <div class='pt10b'>¡¡</div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' colspan='4' style='border-bottom:none'>
                        <div class='pt10b'>¡¡</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none;border-right:none'>
                        <div class='pt10b'>¡¡</div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none;border-right:none'>
                        <div class='pt10b'>­·¡¥À½  Â¤  ·Ð  Èñ</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none;border-right:none'>
                        <div class='pt10b'>¡¡</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none;border-right:none'>
                        <div class='pt10b'>¡¡</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none'>
                        <div class='pt11b'>¡¡</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none;border-right:none'>
                        <div class='pt10b'>¡¡</div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none;border-right:none'>
                        <div class='pt10b'>¡¡Åö´üÀ½Â¤·ÐÈñ</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none;border-right:none'>
                        <div class='pt10b'>¡¡</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none;border-right:none'>
                        <div class='pt10b'>¡¡</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:1pt solid windowtext'>
                        <div class='pt11b'><?= number_format($seizo_keihi_total_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none;border-right:none'>
                        <div class='pt10b'>¡¡</div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none;border-right:none'>
                        <div class='pt10b'>¡¡Åö´üÁíÀ½Â¤ÈñÍÑ</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none;border-right:none'>
                        <div class='pt10b'>¡¡</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none;border-right:none'>
                        <div class='pt10b'>¡¡</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none'>
                        <div class='pt11b'><?= number_format($touki_total_seizo_hiyo) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none;border-right:none'>
                        <div class='pt10b'>¡¡</div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none;border-right:none'>
                        <div class='pt10b'>¡¡´ü¼ó»Å³ÝÉÊÃª²·¹â</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none;border-right:none'>
                        <div class='pt10b'>¡¡</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none;border-right:none'>
                        <div class='pt10b'>¡¡</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:1pt solid windowtext'>
                        <div class='pt11b'><?= number_format($kishu_shikakari_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none;border-right:none'>
                        <div class='pt10b'>¡¡</div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none;border-right:none'>
                        <div class='pt10b'>¡¡¹ç      ·×</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none;border-right:none'>
                        <div class='pt10b'>¡¡</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none;border-right:none'>
                        <div class='pt10b'>¡¡</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none'>
                        <div class='pt11b'><?= number_format($toki_seizo_keihi_total) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none;border-right:none'>
                        <div class='pt10b'>¡¡</div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none;border-right:none'>
                        <div class='pt10b'>¡¡´üËö»Å³ÝÉÊÃª²·¹â</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none;border-right:none'>
                        <div class='pt10b'>¡¡</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none;border-right:none'>
                        <div class='pt10b'>¡¡</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:1pt solid windowtext'>
                        <div class='pt11b'><?= number_format($kimatsu_shikakari_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-right:none'>
                        <div class='pt10b'>¡¡</div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none;border-right:none'>
                        <div class='pt10b'>¡¡Åö´üÀ½ÉÊÀ½Â¤¸¶²Á</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none;border-right:none'>
                        <div class='pt10b'>¡¡</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none;border-right:none'>
                        <div class='pt10b'>¡¡</div>
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
                        <div class='pt10b'>¡¡</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none;border-right:none'>
                        <div class='pt10b'>¡¡</div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' colspan='2' style='border-right:none'>
                        <div class='pt10b'>´üËöºàÎÁÃª²·¹â¤Ë¤Ï¡¢Ãª²·»ñ»ºÉ¾²ÁÂ»</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-right:none'>
                        <div class='pt11b'><?= number_format($hyokason_cr_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='left'>
                        <div class='pt10b'>±ß¤¬´Þ¤Þ¤ì¤Æ¤ª¤ê¤Þ¤¹¡£</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none;border-right:none'>
                        <div class='pt10b'>¡¡</div>
                    </td>
                </tr>
            </TBODY>
        </table>
            </td></tr>
        </table> <!----------------- ¥À¥ß¡¼End ------------------>
        
        <table bgcolor='#ffffff' align='center' cellspacing='0' cellpadding='10'>
            <tr><td> <!----------- ¥À¥ß¡¼(¥Ç¥¶¥¤¥óÍÑ) ------------>
        <center>¡Ê·ÐÈñÌÀºÙ½ñ¡Ë</center>
        <table class='winbox_field' width='100%' align='center' bgcolor='black' cellspacing='0' cellpadding='5'>
            <THEAD>
                <!-- ¥Æ¡¼¥Ö¥ë ¥Ø¥Ã¥À¡¼¤ÎÉ½¼¨ -->
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center'>
                        <div class='pt10b'>²ÊÌÜ</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center'>
                        <div class='pt10b'>À½Â¤ÈñÍÑ</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center'>
                        <div class='pt10b'>ÈÎ´ÉÈñµÚ¤Ó°ìÈÌ´ÉÍýÈñ</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center'>
                        <div class='pt10b'>¹ç·×</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none;border-right:none;border-top:none'>
                        <div class='pt10b'>¡¡</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none;border-right:none;border-top:none'>
                        <div class='pt10b'>¡¡</div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none'>
                        <div class='pt10b'>¡¡</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none'>
                        <div class='pt10b'>¡ÊÏ«Ì³Èñ¡Ë</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none'>
                        <div class='pt10b'>¡Ê¿Í·ïÈñ¡Ë</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none'>
                        <div class='pt10b'>¡¡</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none;border-right:none'>
                        <div class='pt10b'>¡¡</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none;border-right:none'>
                        <div class='pt10b'>¡¡</div>
                    </td>
                </tr>
            </THEAD>
            <TFOOT>
                <!-- ¸½ºß¤Ï¥Õ¥Ã¥¿¡¼¤Ï²¿¤â¤Ê¤¤ -->
            </TFOOT>
            <TBODY>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none'>
                        <div class='pt10b'>Ìò°÷Êó½·</div>
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
                        <div class='pt10b'>¡¡</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none;border-right:none'>
                        <div class='pt10b'>¡¡</div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none'>
                        <div class='pt10b'>µëÎÁ¼êÅö</div>
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
                        <div class='pt10b'>¡¡</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none;border-right:none;border-top:none'>
                        <div class='pt10b'>¡¡</div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none'>
                        <div class='pt10b'>¾ÞÍ¿¼êÅö</div>
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
                        <div class='pt10b'>¡¡</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none;border-right:none;border-top:none'>
                        <div class='pt10b'>¡¡</div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none'>
                        <div class='pt10b'>¸ÜÌäÎÁ</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none'>
                        <div class='pt11b'>¡Ý</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none'>
                        <div class='pt11b'><?= number_format($komon_han_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none'>
                        <div class='pt11b'><?= number_format($komon_total_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none;border-right:none;border-top:none'>
                        <div class='pt10b'>¡¡</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none;border-right:none;border-top:none'>
                        <div class='pt10b'>¡¡</div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none'>
                        <div class='pt10b'>¸üÀ¸Ê¡ÍøÈñ</div>
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
                        <div class='pt10b'>¡¡</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none;border-right:none;border-top:none'>
                        <div class='pt10b'>¡¡</div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none'>
                        <div class='pt10b'>¾ÞÍ¿°úÅö¶â·«Æþ³Û</div>
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
                        <div class='pt10b'>¡¡</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none;border-right:none;border-top:none'>
                        <div class='pt10b'>¡¡</div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center'>
                        <div class='pt10b'>Âà¿¦µëÉÕÈñÍÑ</div>
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
                        <div class='pt10b'>¡¡</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-right:none;border-top:none'>
                        <div class='pt10b'>¡¡</div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right'>
                        <div class='pt10b'>¾®·×</div>
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
                        <div class='pt10b'>¡¡</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none'>
                        <div class='pt10b'>¡ÊÀ½Â¤·ÐÈñ¡Ë</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none'>
                        <div class='pt10b'>¡Ê·ÐÈñ¡Ë</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none'>
                        <div class='pt10b'>¡¡</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none;border-right:none;border-top:none'>
                        <div class='pt10b'>¡¡</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none;border-right:none;border-top:none'>
                        <div class='pt10b'>¡¡</div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none'>
                        <div class='pt10b'>Î¹Èñ¸òÄÌÈñ</div>
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
                        <div class='pt10b'>¡¡</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none;border-right:none;border-top:none'>
                        <div class='pt10b'>¡¡</div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none'>
                        <div class='pt10b'>ÄÌ¿®Èñ</div>
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
                        <div class='pt10b'>¡¡</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none;border-right:none;border-top:none'>
                        <div class='pt10b'>¡¡</div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none'>
                        <div class='pt10b'>²ñµÄÈñ</div>
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
                        <div class='pt10b'>¡¡</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none;border-right:none;border-top:none'>
                        <div class='pt10b'>¡¡</div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none'>
                        <div class='pt10b'>¸òºÝÀÜÂÔÈñ</div>
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
                        <div class='pt10b'>¡¡</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none;border-right:none;border-top:none'>
                        <div class='pt10b'>¡¡</div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none'>
                        <div class='pt10b'>¹­¹ðÀëÅÁÈñ</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none'>
                        <div class='pt11b'>¡Ý</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none'>
                        <div class='pt11b'><?= number_format($senden_han_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none'>
                        <div class='pt11b'><?= number_format($senden_total_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none;border-right:none;border-top:none'>
                        <div class='pt10b'>¡¡</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none;border-right:none;border-top:none'>
                        <div class='pt10b'>¡¡</div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none'>
                        <div class='pt10b'>±¿ÄÂ²ÙÂ¤Èñ</div>
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
                        <div class='pt10b'>¡¡</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none;border-right:none;border-top:none'>
                        <div class='pt10b'>¡¡</div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none'>
                        <div class='pt10b'>¿Þ½ñ¶µ°éÈñ</div>
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
                        <div class='pt10b'>¡¡</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none;border-right:none;border-top:none'>
                        <div class='pt10b'>¡¡</div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none'>
                        <div class='pt10b'>¶ÈÌ³°ÑÂ÷Èñ</div>
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
                        <div class='pt10b'>¡¡</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none;border-right:none;border-top:none'>
                        <div class='pt10b'>¡¡</div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none'>
                        <div class='pt10b'>½ôÀÇ¸ø²Ý</div>
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
                        <div class='pt10b'>¡¡</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none;border-right:none;border-top:none'>
                        <div class='pt10b'>¡¡</div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none'>
                        <div class='pt10b'>»î¸³¸¦µæÈñ</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none'>
                        <div class='pt11b'><?= number_format($shiken_seizo_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none'>
                        <div class='pt11b'>¡Ý</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none'>
                        <div class='pt11b'><?= number_format($shiken_total_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none;border-right:none;border-top:none'>
                        <div class='pt10b'>¡¡</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none;border-right:none;border-top:none'>
                        <div class='pt10b'>¡¡</div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none'>
                        <div class='pt10b'>½¤Á¶Èñ</div>
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
                        <div class='pt10b'>¡¡</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none;border-right:none;border-top:none'>
                        <div class='pt10b'>¡¡</div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none'>
                        <div class='pt10b'>»öÌ³ÍÑ¾ÃÌ×ÉÊÈñ</div>
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
                        <div class='pt10b'>¡¡</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none;border-right:none;border-top:none'>
                        <div class='pt10b'>¡¡</div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none'>
                        <div class='pt10b'>¹©¾ìÍÑ¾ÃÌ×ÉÊÈñ</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none'>
                        <div class='pt11b'><?= number_format($kojyo_seizo_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none'>
                        <div class='pt11b'>¡Ý</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none'>
                        <div class='pt11b'><?= number_format($kojyo_total_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none;border-right:none;border-top:none'>
                        <div class='pt10b'>¡¡</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none;border-right:none;border-top:none'>
                        <div class='pt10b'>¡¡</div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none'>
                        <div class='pt10b'>¼ÖíÒÈñ</div>
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
                        <div class='pt10b'>¡¡</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none;border-right:none;border-top:none'>
                        <div class='pt10b'>¡¡</div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none'>
                        <div class='pt10b'>ÊÝ¸±ÎÁ</div>
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
                        <div class='pt10b'>¡¡</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none;border-right:none;border-top:none'>
                        <div class='pt10b'>¡¡</div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none'>
                        <div class='pt10b'>¿åÆ»¸÷Ç®Èñ</div>
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
                        <div class='pt10b'>¡¡</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none;border-right:none;border-top:none'>
                        <div class='pt10b'>¡¡</div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none'>
                        <div class='pt10b'>ÃÏÂå²ÈÄÂ</div>
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
                        <div class='pt10b'>¡¡</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none;border-right:none;border-top:none'>
                        <div class='pt10b'>¡¡</div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none'>
                        <div class='pt10b'>´óÉÕ¶â</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none'>
                        <div class='pt11b'>¡Ý</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none'>
                        <div class='pt11b'><?= number_format($kifu_han_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none'>
                        <div class='pt11b'><?= number_format($kifu_total_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none;border-right:none;border-top:none'>
                        <div class='pt10b'>¡¡</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none;border-right:none;border-top:none'>
                        <div class='pt10b'>¡¡</div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none'>
                        <div class='pt10b'>ÄÂ¼ÚÎÁ</div>
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
                        <div class='pt10b'>¡¡</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none;border-right:none;border-top:none'>
                        <div class='pt10b'>¡¡</div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none'>
                        <div class='pt10b'>»¨Èñ</div>
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
                        <div class='pt10b'>¡¡</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none;border-right:none;border-top:none'>
                        <div class='pt10b'>¡¡</div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none'>
                        <div class='pt10b'>¥¯¥ì¡¼¥àÂÐ±þÈñ</div>
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
                        <div class='pt10b'>¡¡</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none;border-right:none;border-top:none'>
                        <div class='pt10b'>¡¡</div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center'>
                        <div class='pt10b'>¸º²Á½þµÑÈñ</div>
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
                        <div class='pt10b'>¡¡</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-right:none;border-top:none'>
                        <div class='pt10b'>¡¡</div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right'>
                        <div class='pt10b'>¾®·×</div>
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
                        <div class='pt10b'>¹ç·×</div>
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
                        <div class='pt10b'>¡¡</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none;border-right:none;border-top:none'>
                        <div class='pt10b'>¡¡</div>
                    </td>
                </tr>
            </TBODY>
        </table>
            </td></tr>
        </table> <!----------------- ¥À¥ß¡¼End ------------------>
        
        <table bgcolor='#ffffff' align='center' cellspacing='0' cellpadding='10'>
            <tr><td> <!----------- ¥À¥ß¡¼(¥Ç¥¶¥¤¥óÍÑ) ------------>
        <center>¡Ê³ô¼ç»ñËÜÅùÊÑÆ°·×»»½ñ¡Ë</center>
        <table class='winbox_field' width='100%' align='center' bgcolor='black' cellspacing='0' cellpadding='5'>
            <THEAD>
            </THEAD>
            <TFOOT>
                <!-- ¸½ºß¤Ï¥Õ¥Ã¥¿¡¼¤Ï²¿¤â¤Ê¤¤ -->
            </TFOOT>
            <TBODY>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='left' colspan='5' style='border-bottom:none'>
                        <div class='pt10b'>¡Ê³ô¼ç»ñËÜ¡Ë</div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='left' style='border-bottom:none;border-right:none'>
                        <div class='pt10b'>¡¡¡Ú»ñËÜ¶â¡Û</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='left' style='border-bottom:none;border-right:none'>
                        <div class='pt10b'>Åö´ü¼ó»Ä¹â</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='left' style='border-bottom:none;border-right:none'>
                        <div class='pt10b'>¡¡</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none'>
                        <div class='pt11b'><?= number_format($shihon_kishu) ?></div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='left' style='border-bottom:none;border-right:none'>
                        <div class='pt10b'>¡¡</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='left' style='border-bottom:none;border-right:none'>
                        <div class='pt10b'>Åö´üÊÑÆ°³Û</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='left' style='border-bottom:none;border-right:none'>
                        <div class='pt10b'>¡¡</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:1pt solid windowtext'>
                        <div class='pt11b'><?= number_format($shihon_hendo) ?></div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='left' style='border-bottom:none;border-right:none'>
                        <div class='pt10b'>¡¡</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='left' style='border-bottom:none;border-right:none'>
                        <div class='pt10b'>Åö´üËö»Ä¹â</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='left' style='border-bottom:none;border-right:none'>
                        <div class='pt10b'>¡¡</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:1.5pt solid windowtext'>
                        <div class='pt11b'><?= number_format($shihon_kin) ?></div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='left' colspan='5' style='border-bottom:none'>
                        <div class='pt10b'>¡¡¡Ú»ñËÜ¾êÍ¾¶â¡Û</div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='left' style='border-bottom:none;border-right:none'>
                        <div class='pt10b'>¡¡»ñËÜ½àÈ÷¶â</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='left' style='border-bottom:none;border-right:none'>
                        <div class='pt10b'>Åö´ü¼ó»Ä¹â</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='left' style='border-bottom:none;border-right:none'>
                        <div class='pt10b'>¡¡</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none'>
                        <div class='pt11b'><?= number_format($shihon_jyunbi_kishu) ?></div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='left' style='border-bottom:none;border-right:none'>
                        <div class='pt10b'>¡¡</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='left' style='border-bottom:none;border-right:none'>
                        <div class='pt10b'>Åö´üÊÑÆ°³Û</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='left' style='border-bottom:none;border-right:none'>
                        <div class='pt10b'>¡¡</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:1pt solid windowtext'>
                        <div class='pt11b'><?= number_format($shihon_jyunbi_hendo) ?></div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='left' style='border-bottom:none;border-right:none'>
                        <div class='pt10b'>¡¡</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='left' style='border-bottom:none;border-right:none'>
                        <div class='pt10b'>Åö´üËö»Ä¹â</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='left' style='border-bottom:none;border-right:none'>
                        <div class='pt10b'>¡¡</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:1.5pt solid windowtext'>
                        <div class='pt11b'><?= number_format($shihon_jyunbi_kin) ?></div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='left' style='border-bottom:none;border-right:none'>
                        <div class='pt10b'>¡¡¤½¤ÎÂ¾»ñËÜ¾êÍ¾¶â</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='left' style='border-bottom:none;border-right:none'>
                        <div class='pt10b'>Åö´ü¼ó»Ä¹â</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='left' style='border-bottom:none;border-right:none'>
                        <div class='pt10b'>¡¡</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none'>
                        <div class='pt11b'><?= number_format($sonota_shihon_jyoyo_kishu) ?></div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='left' style='border-bottom:none;border-right:none'>
                        <div class='pt10b'>¡¡</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='left' style='border-bottom:none;border-right:none'>
                        <div class='pt10b'>Åö´üÊÑÆ°³Û</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='left' style='border-bottom:none;border-right:none'>
                        <div class='pt10b'>¡¡</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:1pt solid windowtext'>
                        <div class='pt11b'><?= number_format($sonota_shihon_jyoyo_hendo) ?></div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='left' style='border-bottom:none;border-right:none'>
                        <div class='pt10b'>¡¡</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='left' style='border-bottom:none;border-right:none'>
                        <div class='pt10b'>Åö´üËö»Ä¹â</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='left' style='border-bottom:none;border-right:none'>
                        <div class='pt10b'>¡¡</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:1.5pt solid windowtext'>
                        <div class='pt11b'><?= number_format($sonota_shihon_jyoyo_kin) ?></div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='left' style='border-bottom:none;border-right:none'>
                        <div class='pt10b'>¡Ú»ñËÜ¾êÍ¾¶â¡Û¹ç·×</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='left' style='border-bottom:none;border-right:none'>
                        <div class='pt10b'>Åö´ü¼ó»Ä¹â</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='left' style='border-bottom:none;border-right:none'>
                        <div class='pt10b'>¡¡</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none'>
                        <div class='pt11b'><?= number_format($shihon_jyoyo_total_kishu) ?></div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='left' style='border-bottom:none;border-right:none'>
                        <div class='pt10b'>¡¡</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='left' style='border-bottom:none;border-right:none'>
                        <div class='pt10b'>Åö´üÊÑÆ°³Û</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='left' style='border-bottom:none;border-right:none'>
                        <div class='pt10b'>¡¡</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:1pt solid windowtext'>
                        <div class='pt11b'><?= number_format($shihon_jyoyo_total_hendo) ?></div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='left' style='border-bottom:none;border-right:none'>
                        <div class='pt10b'>¡¡</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='left' style='border-bottom:none;border-right:none'>
                        <div class='pt10b'>Åö´üËö»Ä¹â</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='left' style='border-bottom:none;border-right:none'>
                        <div class='pt10b'>¡¡</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:1.5pt solid windowtext'>
                        <div class='pt11b'><?= number_format($shihon_jyoyo_total_kin) ?></div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='left' colspan='5' style='border-bottom:none'>
                        <div class='pt10b'>¡¡¡ÚÍø±×¾êÍ¾¶â¡Û</div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='left' style='border-bottom:none;border-right:none'>
                        <div class='pt10b'>¡¡Íø±×½àÈ÷¶â</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='left' style='border-bottom:none;border-right:none'>
                        <div class='pt10b'>Åö´ü¼ó»Ä¹âµÚ¤ÓÅö´üËö»Ä¹â</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='left' style='border-bottom:none;border-right:none'>
                        <div class='pt10b'>¡¡</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none'>
                        <div class='pt11b'><?= number_format($rieki_jyunbi_kin) ?></div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='left' style='border-bottom:none;border-right:none'>
                        <div class='pt10b'>¡¡¤½¤ÎÂ¾Íø±×¾êÍ¾¶â</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='left' style='border-bottom:none;border-right:none'>
                        <div class='pt10b'>Åö´ü¼ó»Ä¹â</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='left' style='border-bottom:none;border-right:none'>
                        <div class='pt10b'>¡¡</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none'>
                        <div class='pt11b'><?= number_format($sonota_rieki_jyoyo_kishu) ?></div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='left' style='border-bottom:none;border-right:none'>
                        <div class='pt10b'>¡¡</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='left' style='border-bottom:none;border-right:none'>
                        <div class='pt10b'>Åö´üÊÑÆ°³Û</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='left' style='border-bottom:none;border-right:none'>
                        <div class='pt10b'>¡¡</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:1pt solid windowtext'>
                        <div class='pt11b'><?= number_format($sonota_rieki_jyoyo_hendo) ?></div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='left' style='border-bottom:none;border-right:none'>
                        <div class='pt10b'>¡¡</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='left' style='border-bottom:none;border-right:none'>
                        <div class='pt10b'>Åö´üËö»Ä¹â</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='left' style='border-bottom:none;border-right:none'>
                        <div class='pt10b'>¡¡</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:1.5pt solid windowtext'>
                        <div class='pt11b'><?= number_format($sonota_rieki_jyoyo_kin) ?></div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='left' style='border-bottom:none;border-right:none'>
                        <div class='pt10b'>¡¡·«±ÛÍø±×¾êÍ¾¶â</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='left' style='border-bottom:none;border-right:none'>
                        <div class='pt10b'>Åö´ü¼ó»Ä¹â</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='left' style='border-bottom:none;border-right:none'>
                        <div class='pt10b'>¡¡</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none'>
                        <div class='pt11b'><?= number_format($kuri_rieki_jyoyo_kishu) ?></div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='left' style='border-bottom:none;border-right:none'>
                        <div class='pt10b'>¡¡</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='left' style='border-bottom:none;border-right:none'>
                        <div class='pt10b'>Åö´üÊÑÆ°³Û</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none;border-right:none'>
                        <div class='pt10b'>¡ÊÅö´ü½ãÍø±×¶â³Û¡Ë</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:1pt solid windowtext'>
                        <div class='pt11b'><?= number_format($kuri_rieki_jyoyo_hendo) ?></div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='left' style='border-bottom:none;border-right:none'>
                        <div class='pt10b'>¡¡</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='left' style='border-bottom:none;border-right:none'>
                        <div class='pt10b'>Åö´üËö»Ä¹â</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='left' style='border-bottom:none;border-right:none'>
                        <div class='pt10b'>¡¡</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:1.5pt solid windowtext'>
                        <div class='pt11b'><?= number_format($kuri_rieki_jyoyo_kin) ?></div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='left' style='border-bottom:none;border-right:none'>
                        <div class='pt10b'>¡ÚÍø±×¾êÍ¾¶â¡Û¹ç·×</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='left' style='border-bottom:none;border-right:none'>
                        <div class='pt10b'>Åö´ü¼ó»Ä¹â</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='left' style='border-bottom:none;border-right:none'>
                        <div class='pt10b'>¡¡</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none'>
                        <div class='pt11b'><?= number_format($rieki_jyoyo_total_kishu) ?></div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='left' style='border-bottom:none;border-right:none'>
                        <div class='pt10b'>¡¡</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='left' style='border-bottom:none;border-right:none'>
                        <div class='pt10b'>Åö´üÊÑÆ°³Û</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='left' style='border-bottom:none;border-right:none'>
                        <div class='pt10b'>¡¡</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:1pt solid windowtext'>
                        <div class='pt11b'><?= number_format($rieki_jyoyo_total_hendo) ?></div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='left' style='border-bottom:none;border-right:none'>
                        <div class='pt10b'>¡¡</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='left' style='border-bottom:none;border-right:none'>
                        <div class='pt10b'>Åö´üËö»Ä¹â</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='left' style='border-bottom:none;border-right:none'>
                        <div class='pt10b'>¡¡</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:1.5pt solid windowtext'>
                        <div class='pt11b'><?= number_format($rieki_jyoyo_total_kin) ?></div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='left' style='border-bottom:none;border-right:none'>
                        <div class='pt10b'>¡Ô½ã»ñ»º¹ç·×¡Õ</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='left' style='border-bottom:none;border-right:none'>
                        <div class='pt10b'>Åö´ü¼ó»Ä¹â</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='left' style='border-bottom:none;border-right:none'>
                        <div class='pt10b'>¡¡</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none'>
                        <div class='pt11b'><?= number_format($jyun_shisan_total_kishu) ?></div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='left' style='border-bottom:none;border-right:none'>
                        <div class='pt10b'>¡¡</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='left' style='border-bottom:none;border-right:none'>
                        <div class='pt10b'>Åö´üÊÑÆ°³Û</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='left' style='border-bottom:none;border-right:none'>
                        <div class='pt10b'>¡¡</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:1pt solid windowtext'>
                        <div class='pt11b'><?= number_format($jyun_shisan_total_hendo) ?></div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='left' style='border-right:none'>
                        <div class='pt10b'>¡¡</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='left' style='border-right:none'>
                        <div class='pt10b'>Åö´üËö»Ä¹â</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='left' style='border-right:none'>
                        <div class='pt10b'>¡¡</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:1.5pt solid windowtext'>
                        <div class='pt11b'><?= number_format($jyun_shisan_total_kin) ?></div>
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
