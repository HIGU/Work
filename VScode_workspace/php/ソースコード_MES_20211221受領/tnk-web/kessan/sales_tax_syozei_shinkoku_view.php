<?php
//////////////////////////////////////////////////////////////////////////////
// ·î¼¡Â»±×´Ø·¸ ¾ÃÈñÀÇ¿½¹ð½ñ ¾ÃÈñÀÇ¿½¹ð»ñÎÁ                                 //
// Copyright(C) 2021-2021 Norihisa.Ohya norihisa_ooya@nitto-kohki.co.jp     //
// Changed history                                                          //
// 2021/04/23 Created   sales_tax_syozei_allo_view.php                      //
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
    $menu->set_title("Âè {$ki} ´ü¡¡ËÜ·è»»¡¡¾Ã¡¡Èñ¡¡ÀÇ¡¡¿½¡¡¹ð¡¡»ñ¡¡ÎÁ");
} else {
    $menu->set_title("Âè {$ki} ´ü¡¡Âè{$hanki}»ÍÈ¾´ü¡¡¾Ã¡¡Èñ¡¡ÀÇ¡¡¿½¡¡¹ð¡¡»ñ¡¡ÎÁ");
}

$cost_ym = array();
$tuki_chk = substr($_SESSION['2ki_ym'],4,2);
if ($tuki_chk >= 1 && $tuki_chk <= 3) {           //Âè£´»ÍÈ¾´ü
    $hanki = '£´';
    $yyyy_tou = $yyyy + 1;
    $cost_ym[0]  = $yyyy . '04';
    $cost_ym[1]  = $yyyy . '05';
    $cost_ym[2]  = $yyyy . '06';
    $cost_ym[3]  = $yyyy . '07';
    $cost_ym[4]  = $yyyy . '08';
    $cost_ym[5]  = $yyyy . '09';
    $cost_ym[6]  = $yyyy . '10';
    $cost_ym[7]  = $yyyy . '11';
    $cost_ym[8]  = $yyyy . '12';
    $cost_ym[9]  = $yyyy_tou . '01';
    $cost_ym[10] = $yyyy_tou . '02';
    $cost_ym[11] = $yyyy_tou . '03';
    $cnum        = 12;
} elseif ($tuki_chk >= 4 && $tuki_chk <= 6) {     //Âè£±»ÍÈ¾´ü
    $hanki = '£±';
    $cost_ym[0]  = $yyyy . '04';
    $cost_ym[1]  = $yyyy . '05';
    $cost_ym[2]  = $yyyy . '06';
    $cnum        = 3;
} elseif ($tuki_chk >= 7 && $tuki_chk <= 9) {     //Âè£²»ÍÈ¾´ü
    $hanki = '£²';
    $cost_ym[0] = $yyyy . '04';
    $cost_ym[1] = $yyyy . '05';
    $cost_ym[2] = $yyyy . '06';
    $cost_ym[3] = $yyyy . '07';
    $cost_ym[4]  = $yyyy . '08';
    $cost_ym[5]  = $yyyy . '09';
    $cnum        = 6;
} elseif ($tuki_chk >= 10) {    //Âè£³»ÍÈ¾´ü
    $hanki = '£³';
    $cost_ym[0]  = $yyyy . '04';
    $cost_ym[1]  = $yyyy . '05';
    $cost_ym[2]  = $yyyy . '06';
    $cost_ym[3]  = $yyyy . '07';
    $cost_ym[4]  = $yyyy . '08';
    $cost_ym[5]  = $yyyy . '09';
    $cost_ym[6]  = $yyyy . '10';
    $cost_ym[7]  = $yyyy . '11';
    $cost_ym[8]  = $yyyy . '12';
    $cnum        = 9;
}

// Íâ´ü4·îÊ¬
$cost_ym_next = $yyyy + 1 . '04';

// ÆüÅì¹©´ï¾ù¼õ»ñ»º´Ø·¸
if ($nk_ki == 65) {
    $nk_kotei             = 76600469;
    $nk_kotei_kei         = 598519;
    $nk_kotei_zei         = floor($nk_kotei * 0.1*pow(10,0))/pow(10,0);
    $nk_kotei_kei_zei     = floor($nk_kotei_kei * 0.1*pow(10,0))/pow(10,0);
    $nk_kotei_zei_edp     = 7660047;
    $nk_kotei_kei_zei_edp = 59852;
}

// ÊÌ¥á¥Ë¥å¡¼¤ÇºîÀ®¤·¤¿¥Ç¡¼¥¿¤Î¼èÆÀ

///////////// ¥Ç¡¼¥¿¼èÆÀ½ç¤Ë¤è¤ê ±¦Â¦¤ÎÉ½¤«¤é¥Ç¡¼¥¿¼èÆÀ
///////////// Ì¤Ê§¡¦¼è°úÀèÊÌ¾ÃÈñÀÇ³Û·×»»É½ ¹ç·×¶â³Û¤ò¼èÆÀ
// queryÉô¤Ï¶¦ÍÑ
$query = "select
                SUM(rep_kin) as t_kin
          from
                sales_tax_calculate_list";

// ·îËè¤Î¹ç·×¶â³Û¤ò¼èÆÀ
$t_kou8_kin   = 0;     // ÀÇÈ´¹ØÆþ(·Ú8¡ó)
$t_kou10_kin  = 0;     // ÀÇÈ´¹ØÆþ(10¡ó)
$t_sumi10_kin = 0;     // ÀÇ¶â·×¾åºÑ(10¡ó)
$t_zeigai_kin = 0;     // ²ÝÀÇÂÐ¾Ý³°
$t_kari10_kin = 0;     // ²¾Ê§¾ÃÈñÀÇ(10¡ó)
$t_jido8_kin  = 0;     // ¼«Æ°·×»»³Û(·Ú8¡ó)
$t_jido10_kin = 0;     // ¼«Æ°·×»»³Û(10¡ó)

// ÀÇ¶â·×¾åºÑ(10¡ó)
for ($r=0; $r<$cnum; $r++) {
    // ÆüÉÕ¤ÎÀßÄê
    $d_ym = $cost_ym[$r];
    $search = "where rep_ki=$nk_ki and rep_ymd=$d_ym and rep_code='3333'";
    $query_s = sprintf("$query %s", $search);     // SQL query Ê¸¤Î´°À®
    $res_sum = array();
    if ($rows=getResult($query_s, $res_sum) <= 0) {
        $m_sumi10_kin[$r] = 0;
    } else {
        $m_sumi10_kin[$r] = $res_sum[0][0];
        $t_sumi10_kin += $m_sumi10_kin[$r];
    }
}

// ²ÝÀÇÂÐ¾Ý³°
for ($r=0; $r<$cnum; $r++) {
    // ÆüÉÕ¤ÎÀßÄê
    $d_ym = $cost_ym[$r];
    $search = "where rep_ki=$nk_ki and rep_ymd=$d_ym and rep_kubun='X'";
    $query_s = sprintf("$query %s", $search);     // SQL query Ê¸¤Î´°À®
    $res_sum = array();
    if ($rows=getResult($query_s, $res_sum) <= 0) {
        $m_zeigai_kin[$r] = 0;
    } else {
        $m_zeigai_kin[$r] = $res_sum[0][0];
        $t_zeigai_kin += $m_zeigai_kin[$r];
    }
}

// ²¾Ê§¾ÃÈñÀÇ(10¡ó)
for ($r=0; $r<$cnum; $r++) {
    // ÆüÉÕ¤ÎÀßÄê
    $d_ym = $cost_ym[$r];
    $search = "where rep_ki=$nk_ki and rep_ymd=$d_ym and rep_kubun='3'";
    $query_s = sprintf("$query %s", $search);     // SQL query Ê¸¤Î´°À®
    $res_sum = array();
    if ($rows=getResult($query_s, $res_sum) <= 0) {
        $m_kari10_kin[$r] = 0;
    } else {
        $m_kari10_kin[$r] = $res_sum[0][0];
        $t_kari10_kin += $m_kari10_kin[$r];
    }
}

// ÀÇÈ´¹ØÆþ(·Ú8¡ó)
for ($r=0; $r<$cnum; $r++) {
    // ÆüÉÕ¤ÎÀßÄê
    $d_ym = $cost_ym[$r];
    $search = "where rep_ki=$nk_ki and rep_ymd=$d_ym and rep_code='A108'";
    $query_s = sprintf("$query %s", $search);     // SQL query Ê¸¤Î´°À®
    $res_sum = array();
    if ($rows=getResult($query_s, $res_sum) <= 0) {
        $m_kou8_kin[$r] = 0;
        $m_jido8_kin[$r] = 0;
    } else {
        $m_kou8_kin[$r]  = $res_sum[0][0];
        $m_jido8_kin[$r] = round($m_kou8_kin[$r] * 0.08, 0);
        $t_kou8_kin     += $m_kou8_kin[$r];
        $t_jido8_kin    += $m_jido8_kin[$r];
    }
}

// ÀÇÈ´¹ØÆþ(10¡ó)
for ($r=0; $r<$cnum; $r++) {
    // ÆüÉÕ¤ÎÀßÄê
    $d_ym = $cost_ym[$r];
    $search = "where rep_ki=$nk_ki and rep_ymd=$d_ym";
    $query_s = sprintf("$query %s", $search);     // SQL query Ê¸¤Î´°À®
    $res_sum = array();
    if ($rows=getResult($query_s, $res_sum) <= 0) {
        $m_kou10_kin[$r] = 0 - $m_kari10_kin[$r] - $m_kou8_kin[$r] - $m_sumi10_kin[$r] - $m_zeigai_kin[$r];
    } else {
        $m_kou10_kin[$r] = $res_sum[0][0] - $m_kari10_kin[$r] - $m_kou8_kin[$r] - $m_sumi10_kin[$r] - $m_zeigai_kin[$r];
        $t_kou10_kin += $m_kou10_kin[$r];
    }
}


///////////// Ì¤Ê§¶â»ÙÊ§ÌÀºÙÉ½ ¹ç·×¶â³Û¤ò¼èÆÀ
// queryÉô¤Ï¶¦ÍÑ
$query = "select
                SUM(rep_buy) as t_buy,
                SUM(rep_tax) as t_tax
          from
                sales_tax_payment_list";

// ·îËè¤ÎÀÚÊ´¤Î¹ç·×¶â³Û¤ò¼èÆÀ
$t_buy_kin = 0;
$t_tax_kin = 0;
for ($r=0; $r<$cnum; $r++) {
    // ÆüÉÕ¤ÎÀßÄê
    $d_ym = $cost_ym[$r];
    $search = "where rep_ki=$nk_ki and rep_ymd=$d_ym";
    $query_s = sprintf("$query %s", $search);     // SQL query Ê¸¤Î´°À®
    $res_sum = array();
    if ($rows=getResult($query_s, $res_sum) <= 0) {
        $m_buy_kin[$r]    = 0 - $m_kou8_kin[$r];
        $m_tax_kin[$r]    = 0 - $m_jido8_kin[$r];
        $m_jido10_kin[$r] = 0 - $m_tax_kin[$r] - $m_kari10_kin[$r];
    } else {
        $m_buy_kin[$r] = $res_sum[0][0] - $m_kou8_kin[$r];
        $t_buy_kin += $m_buy_kin[$r];
        $m_tax_kin[$r] = $res_sum[0][1] - $m_jido8_kin[$r];
        $m_jido10_kin[$r] = $m_tax_kin[$r] - $m_kari10_kin[$r];
        $t_tax_kin += $m_tax_kin[$r];
        $t_jido10_kin += $m_jido10_kin[$r];
    }
}

///////// ¹àÌÜ¤È¥¤¥ó¥Ç¥Ã¥¯¥¹¤Î´ØÏ¢ÉÕ¤±
$gitem = array();
$gitem[0]   = "Åö·îÈ¯À¸¹ØÆþ³Û·Ú8";
$gitem[1]   = "Åö·îÈ¯À¸¾ÃÈñÀÇ³Û·Ú8";
$gitem[2]   = "Åö·îÈ¯À¸¹ØÆþ³Û10";
$gitem[3]   = "Åö·îÈ¯À¸¾ÃÈñÀÇ³Û10";
$gitem[4]   = "Ì¤Ê§ÅÁÉ¼ÀÇÈ´¹ØÆþ·Ú8";
$gitem[5]   = "Ì¤Ê§ÅÁÉ¼ÀÇÈ´¹ØÆþ10";
$gitem[6]   = "Ì¤Ê§ÅÁÉ¼ÀÇ¶â·×¾åºÑ10";
$gitem[7]   = "Ì¤Ê§ÅÁÉ¼²ÝÀÇÂÐ¾Ý³°";
$gitem[8]   = "Ì¤Ê§ÅÁÉ¼²¾Ê§¾ÃÈñÀÇ10";
$gitem[9]   = "²¾Ê§¾ÃÈñÀÇ¼«Æ°·×»»³Û·Ú8";
$gitem[10]  = "²¾Ê§¾ÃÈñÀÇ¼«Æ°·×»»³Û10"; 
$gitem[11]  = "²¾Ê§¾ÃÈñÀÇÅùÍ¢Æþ"; 
$gitem[12]  = "Ì¤Ê§¾ÃÈñÀÇÅùÃæ´ÖÇ¼ÉÕ"; 
$gitem[13]  = $cost_ym_next . "Ãæ´ÖÇ¼ÉÕÀÇ³Û"; 
$gitem[14]  = "²¾Ê§¾ÃÈñÀÇÅù"; 
///////// ³Æ¥Ç¡¼¥¿¤ÎÊÝ´É
$view_data = array();

$num_input = count($gitem);
for ($i = 0; $i < $num_input; $i++) {
    $query = sprintf("select rep_kin from sales_tax_create_data where rep_ki=%d and rep_note='%s'", $nk_ki, $gitem[$i]);
    $res_in = array();
    if (getResult2($query,$res_in) <= 0) {
        /////////// begin ¥È¥é¥ó¥¶¥¯¥·¥ç¥ó³«»Ï
        if ($con = db_connect()) {
            query_affected_trans($con, "begin");
        } else {
            $_SESSION["s_sysmsg"] .= "¥Ç¡¼¥¿¥Ù¡¼¥¹¤ËÀÜÂ³¤Ç¤­¤Þ¤»¤ó";
            exit();
        }
        /////////// commit ¥È¥é¥ó¥¶¥¯¥·¥ç¥ó½ªÎ»
        query_affected_trans($con, "commit");
        $view_data[0][$i] = 0;
    } else {
        /////////// begin ¥È¥é¥ó¥¶¥¯¥·¥ç¥ó³«»Ï
        if ($con = db_connect()) {
            query_affected_trans($con, "begin");
        } else {
            $_SESSION["s_sysmsg"] .= "¥Ç¡¼¥¿¥Ù¡¼¥¹¤ËÀÜÂ³¤Ç¤­¤Þ¤»¤ó";
            exit();
        }
        /////////// commit ¥È¥é¥ó¥¶¥¯¥·¥ç¥ó½ªÎ»
        query_affected_trans($con, "commit");
        $view_data[0][$i] = $res_in[0][0];
    }
}

// Ì¤Ê§¶â¾ÃÈñÀÇ·×»»(ÀÚ¤ê¼Î¤Æ¡Ë
$miha_siire_zei10  = floor($view_data[0][5] * 0.1*pow(10,0))/pow(10,0);
$miha_siire_zei8k  = floor($view_data[0][4] * 0.08*pow(10,0))/pow(10,0);
$miha_siire_zei10d = floor($view_data[0][6] * 0.1*pow(10,0))/pow(10,0);

// ¥Æ¥¹¥È ¤¢¤È¤Çºï½ü
$view_data[0][13] = 9019600;
// ¡ÊÃæ´ÖÇ¼ÉÕÀÇ³Û¡Ë·×»»
$view_data[0][13] = $view_data[0][12] + $view_data[0][13];

// 21´ü¤Î¤ßÆÃÊÌ
if ($nk_ki==65) {
    //$miha_siire_zei10d = $miha_siire_zei10d + 15344000;
}

/// Çã³Ý¥Ç¡¼¥¿¼èÆÀ
$str_ymd = $str_ym . '00';
$end_ymd = $end_ym . '99';
$query = sprintf("SELECT SUM(round(order_price*siharai,0)) FROM act_payable WHERE act_date>=%d and act_date<=%d and vendor<>'00222' and vendor<>'01111' and vendor<>'00948' and vendor<>'05001' and vendor<>'99999' and (vendor <'03000' or vendor> '03999') ", $str_ymd, $end_ymd);
$res_kai = array();
$kai_siire = 0;
if (getResult2($query,$res_kai) <= 0) {
    $kai_siire = 0;    
} else {
    $kai_siire = $res_kai[0][0];
}
// ¾ÃÈñÀÇ·×»»(ÀÚ¤ê¼Î¤Æ¡Ë
$kai_siire_zei = floor($kai_siire * 0.1*pow(10,0))/pow(10,0);

/// »ÅÌõ¥Ç¡¼¥¿¼èÆÀ 8% BK
$query = sprintf("SELECT SUM(rep_kin) - SUM(ROUND(rep_kin/1.08)) as kin FROM sales_tax_koujyo_siwake WHERE rep_ki=%d and rep_kamoku > '7501' and rep_kamoku <= '8123' and rep_kubun='BK'", $nk_ki);
$res_siwa8bk = array();
$siwa8bk_siire = 0;
if (getResult2($query,$res_siwa8bk) <= 0) {
    $siwa8bk_siire = 0;    
} else {
    $siwa8bk_siire = $res_siwa8bk[0][0];
}

/// »ÅÌõ¥Ç¡¼¥¿¼èÆÀ 8% ZK
$query = sprintf("SELECT SUM(ROUND(rep_kin*1.08)) - SUM(rep_kin) FROM sales_tax_koujyo_siwake WHERE rep_ki=%d and rep_kamoku > '7501' and rep_kamoku <= '8123' and rep_kubun='ZK' and rep_teki='A008'", $nk_ki);
$res_siwa8zk = array();
$siwa8zk_siire = 0;
if (getResult2($query,$res_siwa8zk) <= 0) {
    $siwa8zk_siire = 0;    
} else {
    $siwa8zk_siire = $res_siwa8zk[0][0];
}
// ¾ÃÈñÀÇ·×»»(»Í¼Î¸ÞÆþ¡Ë
$siwa8_siire     = $siwa8bk_siire + $siwa8zk_siire;
$siwa8_siire_zei = floor($siwa8_siire / 0.08*pow(10,0))/pow(10,0);

/// »ÅÌõ¥Ç¡¼¥¿¼èÆÀ 8%·Ú ¥Ö¥é¥ó¥¯¤ÇA108
$query = sprintf("SELECT SUM(rep_kin) as kin FROM sales_tax_koujyo_siwake WHERE rep_ki=%d and rep_kubun='' and rep_teki='A108'", $nk_ki);
$res_siwa8d = array();
$siwa8d_siire = 0;
if (getResult2($query,$res_siwa8d) <= 0) {
    $siwa8d_siire = 0;    
} else {
    $siwa8d_siire = $res_siwa8d[0][0];
}
// ¾ÃÈñÀÇ·×»»(»Í¼Î¸ÞÆþ¡Ë
$siwa8d_siire_zei = round($siwa8d_siire / 0.08,0);

//­© ¾ÃÈñÀÇ10¡ó·× ·×»»
$syo10_9_total = $view_data[0][14] + $view_data[0][11] + $view_data[0][12] - $siwa8_siire - $siwa8d_siire;

//­ª »ÅÌõÅÁÉ¼»ÅÆþ¹â ¾ÃÈñÀÇ10¡ó ·×»»
$siwa10_siire = $syo10_9_total - $kai_siire_zei - $miha_siire_zei10 - $miha_siire_zei10d - $view_data[0][11] - $nk_kotei_zei - $nk_kotei_kei_zei - $view_data[0][12];

// ÀÇÈ´¤­¶â³Û·×»»
$siwa10_siire_zei = round($siwa10_siire / 0.1,0);

//­° ÀÇÈ´¶â³Û·× ·×»»
$zeinuki_16_total = $kai_siire + $view_data[0][5] + $view_data[0][4] + $view_data[0][6] + $siwa10_siire_zei + $siwa8d_siire_zei + $siwa8_siire_zei + $nk_kotei + $nk_kotei_kei;

//­© ¾ÃÈñÀÇ·Ú£¸¡ó·× ·×»»
$syo8_kei_total = $siwa8d_siire + $miha_siire_zei8k;

//¥¤ EDP NK¾ù¼õ»ñ»º
$edp_nk_kotei = $kai_siire_zei + $view_data[0][10] + $view_data[0][9] + $view_data[0][8] + $siwa10_siire + $siwa8d_siire + $siwa8_siire;

//EDP¾ÃÈñÀÇ·×¾å³Û ·× ·×»»
$edp_syozei_kotei = $edp_nk_kotei + $view_data[0][11] + $nk_kotei_zei_edp + $nk_kotei_kei_zei_edp + $view_data[0][12];

//ÀÇÈ´¶â³Û¡Ê²ÝÀÇÂÐ¾Ý¡Ë¹ç·×¶â³Û
// ­²10¡ó
$zeinuki_kazei_kei10 = $kai_siire + $view_data[0][5] + $view_data[0][6] + $siwa10_siire_zei + $nk_kotei + $nk_kotei_kei;
// ­³8¡ó·Ú
$zeinuki_kazei_kei8d = $view_data[0][4] + $siwa8d_siire_zei;
// ­´8¡ó
$zeinuki_kazei_kei8  = $siwa8_siire_zei;

// Ä´À°·×»»
// Çã³Ý¶â·×»»³Ûº¹°Û
$kai_siire_sai = 0;

// Ì¤Ê§¶â¾ÃÈñÀÇÄ´À°
// 10% ­¤¡Ü­¦-A-C
$miha_zei_sai10 = $miha_siire_zei10 + $miha_siire_zei10d - $view_data[0][10] - $view_data[0][8];
// 8%·Ú ­¤-B
$miha_zei_sai8d = $miha_siire_zei8k - $view_data[0][9];

// Ì¤Ê§¶â¾ÃÈñÀÇÄ´À°
// ¸ÇÄê»ñ»ºÄ´À° (d - ²£¡Ë¡Ü¡Êe - ²£¡Ë
$kotei_cho_sai = ($nk_kotei_zei - $nk_kotei_zei_edp) + ($nk_kotei_kei_zei - $nk_kotei_kei_zei_edp);

// Áí·×·×»»
// 10¡ó
$zeinuki_total_10 = $zeinuki_kazei_kei10;    // 10% ÀÇÈ´¶â³Û¡Ê²ÝÀÇÂÐ¾Ý¡Ë
$zei10_total_10   = $syo10_9_total;          // 10% ¾ÃÈñÀÇ£±£°¡ó ËÜÍè¤Ï­©¤ÈÄ´À°´Ø·¸¤Î¹ç·×
$edp_total_10     = $kai_siire_zei + $view_data[0][10] + $view_data[0][8] + $siwa10_siire + $view_data[0][11] + $nk_kotei_zei_edp + $nk_kotei_kei_zei_edp + $view_data[0][12] + $miha_zei_sai10 + $kotei_cho_sai; // 10% £Å£Ä£Ð¾ÃÈñÀÇ·×¾å³Û
$zei4_total_10    = floor($zeinuki_total_10 * 0.078*pow(10,0))/pow(10,0); // 10% ¾ÃÈñÀÇ£´¡ó 10% ÀÇÈ´¶â³Û¡Ê²ÝÀÇÂÐ¾Ý¡Ë¤Î0.078ÇÜ ÀÚ¤ê¼Î¤Æ
$zeikomi_total_10 = floor($zeinuki_total_10 * 1.1*pow(10,0))/pow(10,0); // 10% ÀÇ¹þ¶â³Û 10% ÀÇÈ´¶â³Û¡Ê²ÝÀÇÂÐ¾Ý¡Ë¤Î1.1ÇÜ ÀÚ¤ê¼Î¤Æ

// 8¡ó·Ú
$zeinuki_total_8d = $zeinuki_kazei_kei8d; // 8%·Ú ÀÇÈ´¶â³Û¡Ê²ÝÀÇÂÐ¾Ý¡Ë
$zei8d_total_8d   = $syo8_kei_total;      // 8%·Ú ¾ÃÈñÀÇ·Ú£¸¡ó ËÜÍè¤Ï­©¤ÈÄ´À°´Ø·¸¤Î¹ç·×
$edp_total_8d     = $view_data[0][9] + $siwa8d_siire + $miha_zei_sai8d; // 8%·Ú £Å£Ä£Ð¾ÃÈñÀÇ·×¾å³Û
$zei4_total_8d    = floor($zeinuki_total_8d * 0.0624*pow(10,0))/pow(10,0); // 8%·Ú ¾ÃÈñÀÇ£´¡ó 8%·Ú ÀÇÈ´¶â³Û¡Ê²ÝÀÇÂÐ¾Ý¡Ë¤Î0.0624ÇÜ ÀÚ¤ê¼Î¤Æ
$zeikomi_total_8d = floor($zeinuki_total_8d * 1.08*pow(10,0))/pow(10,0); // 8%·Ú ÀÇ¹þ¶â³Û 8%·Ú ÀÇÈ´¶â³Û¡Ê²ÝÀÇÂÐ¾Ý¡Ë¤Î1.08ÇÜ ÀÚ¤ê¼Î¤Æ

// 8¡ó
$zeinuki_total_8  = $siwa8_siire_zei;   // 8% ÀÇÈ´¶â³Û¡Ê²ÝÀÇÂÐ¾Ý¡Ë ËÜÍè¤Ï­´¤ÈÄ´À°´Ø·¸¤Î¹ç·×
$zei8_total_8     = $siwa8_siire;       // 8% ¾ÃÈñÀÇ£¸¡ó ËÜÍè¤Ï­©¤ÈÄ´À°´Ø·¸¤Î¹ç·×
$edp_total_8      = $siwa8_siire;       // 8% £Å£Ä£Ð¾ÃÈñÀÇ·×¾å³Û
$zei4_total_8     = floor($zeinuki_total_8 * 0.063*pow(10,0))/pow(10,0); // 8% ¾ÃÈñÀÇ£´¡ó 8% ÀÇÈ´¶â³Û¡Ê²ÝÀÇÂÐ¾Ý¡Ë¤Î0.063ÇÜ ÀÚ¤ê¼Î¤Æ
$zeikomi_total_8  = floor($zeinuki_total_8 * 1.08*pow(10,0))/pow(10,0); // 8% ÀÇ¹þ¶â³Û 8% ÀÇÈ´¶â³Û¡Ê²ÝÀÇÂÐ¾Ý¡Ë¤Î1.08ÇÜ ÀÚ¤ê¼Î¤Æ

// Áí¹ç·×·×»»
$zeinuki_total_all = $zeinuki_total_10 + $zeinuki_total_8d + $zeinuki_total_8;
$zei8_total_all    = $zei8_total_8;
$zei8d_total_all   = $zei8d_total_8d;
$zei10_total_all   = $zei10_total_10;
$edp_total_all     = $edp_total_10 + $edp_total_8d + $edp_total_8;
$zei4_total_all    = $zei4_total_10 + $zei4_total_8d + $zei4_total_8;
$zeikomi_total_all = $zeikomi_total_10 + $zeikomi_total_8d + $zeikomi_total_8;


if (isset($_POST['input_data'])) {                        // Åö·î¥Ç¡¼¥¿¤ÎÅÐÏ¿
    ///////// ¹àÌÜ¤È¥¤¥ó¥Ç¥Ã¥¯¥¹¤Î´ØÏ¢ÉÕ¤±
    ///////// ¹àÌÜ¤È¥¤¥ó¥Ç¥Ã¥¯¥¹¤Î´ØÏ¢ÉÕ¤±
    $item = array();
    $item[0]   = "¹µ½üÀÇ³ÛÀÇÈ´¶â³Û²ÝÀÇ10";
    $item[1]   = "¹µ½üÀÇ³ÛÀÇÈ´¶â³Û²ÝÀÇ·Ú8";
    $item[2]   = "¹µ½üÀÇ³ÛÀÇÈ´¶â³Û²ÝÀÇ8";
    $item[3]   = "¹µ½üÀÇ³ÛÀÇÈ´¶â³Û²ÝÀÇ¹ç·×";
    $item[4]   = "¹µ½üÀÇ³ÛÀÇÈ´¶â³ÛÀÇ8";
    $item[5]   = "¹µ½üÀÇ³ÛÀÇÈ´¶â³ÛÀÇ8¹ç·×";
    $item[6]   = "¹µ½üÀÇ³ÛÀÇÈ´¶â³ÛÀÇ·Ú8";
    $item[7]   = "¹µ½üÀÇ³ÛÀÇÈ´¶â³ÛÀÇ·Ú8¹ç·×";
    $item[8]   = "¹µ½üÀÇ³ÛÀÇÈ´¶â³ÛÀÇ10";
    $item[9]   = "¹µ½üÀÇ³ÛÀÇÈ´¶â³ÛÀÇ10¹ç·×";
    $item[10]  = "¹µ½üÀÇ³ÛÀÇÈ´¶â³ÛEDP10"; 
    $item[11]  = "¹µ½üÀÇ³ÛÀÇÈ´¶â³ÛEDP·Ú8"; 
    $item[12]  = "¹µ½üÀÇ³ÛÀÇÈ´¶â³ÛEDP8"; 
    $item[13]  = "¹µ½üÀÇ³ÛÀÇÈ´¶â³ÛEDP¹ç·×"; 
    $item[14]  = "¹µ½üÀÇ³ÛÀÇÈ´¶â³ÛÀÇ410";
    $item[15]  = "¹µ½üÀÇ³ÛÀÇÈ´¶â³ÛÀÇ4·Ú8";
    $item[16]  = "¹µ½üÀÇ³ÛÀÇÈ´¶â³ÛÀÇ48";
    $item[17]  = "¹µ½üÀÇ³ÛÀÇÈ´¶â³ÛÀÇ4¹ç·×";
    $item[18]  = "¹µ½üÀÇ³ÛÀÇÈ´¶â³ÛÀÇ¹þ10";
    $item[19]  = "¹µ½üÀÇ³ÛÀÇÈ´¶â³ÛÀÇ¹þ·Ú8";
    $item[20]  = "¹µ½üÀÇ³ÛÀÇÈ´¶â³ÛÀÇ¹þ8";
    $item[21]  = "¹µ½üÀÇ³ÛÀÇÈ´¶â³ÛÀÇ¹þ¹ç·×";
    ///////// ³Æ¥Ç¡¼¥¿¤ÎÊÝ´É
    $input_data = array();
    $input_data[0]   = $zeinuki_total_10;
    $input_data[1]   = $zeinuki_total_8d;
    $input_data[2]   = $zeinuki_total_8;
    $input_data[3]   = $zeinuki_total_all;
    $input_data[4]   = $zei8_total_8;
    $input_data[5]   = $zei8_total_all;
    $input_data[6]   = $zei8d_total_8d;
    $input_data[7]   = $zei8d_total_all;
    $input_data[8]   = $zei10_total_10;
    $input_data[9]   = $zei10_total_all;
    $input_data[10]  = $edp_total_10;
    $input_data[11]  = $edp_total_8d;
    $input_data[12]  = $edp_total_8;
    $input_data[13]  = $edp_total_all;
    $input_data[14]  = $zei4_total_10;
    $input_data[15]  = $zei4_total_8d;
    $input_data[16]  = $zei4_total_8;
    $input_data[17]  = $zei4_total_all;
    $input_data[18]  = $zeikomi_total_10;
    $input_data[19]  = $zeikomi_total_8d;
    $input_data[20]  = $zeikomi_total_8;
    $input_data[21]  = $zeikomi_total_all;
    ///////// ³Æ¥Ç¡¼¥¿¤ÎÅÐÏ¿
    //insert_date($item,$nk_ki,$input_data);
}

function insert_date($item,$nk_ki,$input_data) 
{
    $num_input = count($input_data);
    for ($i = 0; $i < $num_input; $i++) {
        $query = sprintf("select rep_kin from sales_tax_create_data where rep_ki=%d and rep_note='%s'", $nk_ki, $item[$i]);
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
            $query = sprintf("insert into sales_tax_create_data (rep_ki, rep_kin, rep_note, last_date, last_user) values (%d, %d, '%s', CURRENT_TIMESTAMP, '%s')", $nk_ki, $input_data[$i], $item[$i], $_SESSION['User_ID']);
            if (query_affected_trans($con, $query) <= 0) {
                $_SESSION["s_sysmsg"] .= sprintf("%s¤Î¿·µ¬ÅÐÏ¿¤Ë¼ºÇÔ<br> %d", $item[$i], $yyyymm);
                query_affected_trans($con, "rollback");     // transaction rollback
                exit();
            }
            /////////// commit ¥È¥é¥ó¥¶¥¯¥·¥ç¥ó½ªÎ»
            query_affected_trans($con, "commit");
            $_SESSION["s_sysmsg"] = sprintf("<font color='yellow'>%d ¾ÃÈñÀÇÅù·×»»É½¥Ç¡¼¥¿ ¿·µ¬ ÅÐÏ¿´°Î»</font>",$yyyymm);
        } else {
            /////////// begin ¥È¥é¥ó¥¶¥¯¥·¥ç¥ó³«»Ï
            if ($con = db_connect()) {
                query_affected_trans($con, "begin");
            } else {
                $_SESSION["s_sysmsg"] .= "¥Ç¡¼¥¿¥Ù¡¼¥¹¤ËÀÜÂ³¤Ç¤­¤Þ¤»¤ó";
                exit();
            }
            ////////// UPDATE Start
            $query = sprintf("update sales_tax_create_data set rep_kin=%d, last_date=CURRENT_TIMESTAMP, last_user='%s' where rep_ki=%d and rep_note='%s'", $input_data[$i], $_SESSION['User_ID'], $nk_ki, $item[$i]);
            if (query_affected_trans($con, $query) <= 0) {
                $_SESSION["s_sysmsg"] .= sprintf("%s¤ÎUPDATE¤Ë¼ºÇÔ<br> %d", $item[$i], $yyyymm);
                query_affected_trans($con, "rollback");     // transaction rollback
                exit();
            }
            /////////// commit ¥È¥é¥ó¥¶¥¯¥·¥ç¥ó½ªÎ»
            query_affected_trans($con, "commit");
            $_SESSION["s_sysmsg"] = sprintf("<font color='yellow'>%d ¾ÃÈñÀÇÅù·×»»É½¥Ç¡¼¥¿ ÊÑ¹¹ ´°Î»</font>",$yyyymm);
        }
    }
    $_SESSION["s_sysmsg"] .= "¾ÃÈñÀÇÅù·×»»É½¤Î¥Ç¡¼¥¿¤òÅÐÏ¿¤·¤Þ¤·¤¿¡£";
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
<?= $menu->out_title_border() ?>
        <?php
            //  bgcolor='#ceffce' ²«ÎÐ
            //  bgcolor='#ffffc6' Çö¤¤²«¿§
            //  bgcolor='#d6d3ce' Win ¥°¥ì¥¤
        ?>
        <!--------------- ¤³¤³¤«¤éËÜÊ¸¤ÎÉ½¤òÉ½¼¨¤¹¤ë -------------------->
        <BR><BR>
        <center>
        <table bgcolor='#d6d3ce' align='center' border='1' cellspacing='0' cellpadding='3'>
            <tr><td> <!----------- ¥À¥ß¡¼(¥Ç¥¶¥¤¥óÍÑ) ------------>
        <table class='winbox_field' width='100%' bgcolor='#d6d3ce' align='center' border='1' cellspacing='0' cellpadding='3'>
            <thead>
            </thead>
            <tfoot>
                <!-- ¸½ºß¤Ï¥Õ¥Ã¥¿¡¼¤Ï²¿¤â¤Ê¤¤ -->
            </tfoot>
            <tbody>
            <?php
            // £Å£Ä£ÐÇã³Ý¶â·×¾å»ÅÆþ³Û 2¹ÔÌÜÉ½¼¨¤Ê¤·
            echo "<tr>\n";
            // ¥¿¥¤¥È¥ë
            echo "<td class='winbox' nowrap bgcolor='white' align='left' colspan='8'><div class='pt10b'>£±¡¥Ì¤Ê§¾ÃÈñÀÇÅù¤Î¸¡¾Ú</div></td>\n";
            echo "</tr>\n";
            
            // £Å£Ä£ÐÇã³Ý¶â·×¾å»ÅÆþ³Û 1¹ÔÌÜÉ½¼¨¤Ê¤·
            echo "<tr>\n";
            // ÀÇ¹þ¶â³Û
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>¡¡</span></td>\n";
            // ¾ÃÈñÀÇ·Ú£¸¡ó
            echo "  <th class='winbox' nowrap align='left'><span class='pt9'>­¡¡¡Åö´üËöÌ¤Ê§¾ÃÈñÀÇÅù»Ä¹â(B/S)</span></td>\n";
            // ¾ÃÈñÀÇ£±£°¡ó
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>¡¡</span></td>\n";
            // EDP¾ÃÈñÀÇ·×¾å³Û
            echo "  <th class='winbox' nowrap align='right'<span class='pt9'>¡¡</span></td>\n";
            // ¾ÃÈñÀÇ£´¡ó
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>¡¡</span></td>\n";
            // ÀÇ¹þ¶â³Û
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>¡¡</span></td>\n";
            // È÷¹Í
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>" . number_format(33007207) . "</span></td>\n";
            // È÷¹Í
            echo "  <th class='winbox' nowrap align='left'><span class='pt9'>±ß</span></td>\n";
            echo "</tr>\n";
            
            // £Å£Ä£ÐÇã³Ý¶â·×¾å»ÅÆþ³Û 3¹ÔÌÜ¿ô»ú¤¢¤ê
            echo "<tr>\n";
            // ÀÇ¹þ¶â³Û
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>¡¡</span></td>\n";
            // ¾ÃÈñÀÇ·Ú£¸¡ó
            echo "  <th class='winbox' nowrap align='left'><span class='pt9'>­¢¡¡¾ÃÈñÀÇÃæ´Ö¿½¹ðÇ¼ÀÇ³Û(£±£±²óÌÜ)</span></td>\n";
            // ¾ÃÈñÀÇ£±£°¡ó
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>¡¡</span></td>\n";
            // EDP¾ÃÈñÀÇ·×¾å³Û
            echo "  <th class='winbox' nowrap align='right'<span class='pt9'>¡¡</span></td>\n";
            // ¾ÃÈñÀÇ£´¡ó
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>¡¡</span></td>\n";
            // ÀÇ¹þ¶â³Û
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>¡¡</span></td>\n";
            // È÷¹Í
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>" . number_format(9019600) . "</span></td>\n";
            // È÷¹Í
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>¡¡</span></td>\n";
            echo "</tr>\n";
            
            // £Å£Ä£ÐÌ¤Ê§¶â·×¾å»ÅÆþ³Û 1¹ÔÌÜ
            echo "<tr>\n";
            // ÀÇ¹þ¶â³Û
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>¡¡</span></td>\n";
            // ¾ÃÈñÀÇ·Ú£¸¡ó
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>¡¡</span></td>\n";
            // ¾ÃÈñÀÇ£±£°¡ó
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>¡¡</span></td>\n";
            // EDP¾ÃÈñÀÇ·×¾å³Û
            echo "  <th class='winbox' nowrap align='right'<span class='pt9'>¡¡</span></td>\n";
            // ¾ÃÈñÀÇ£´¡ó
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>¡¡</span></td>\n";
            // ÀÇ¹þ¶â³Û
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>¡¡</span></td>\n";
            // È÷¹Í
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>" . number_format(42026807) . "</span></td>\n";
            // È÷¹Í
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>¡¡</span></td>\n";
            echo "</tr>\n";
            
            // £Å£Ä£ÐÌ¤Ê§¶â·×¾å»ÅÆþ³Û 2¹ÔÌÜ
            echo "<tr>\n";
            // ÀÇ¹þ¶â³Û
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>¡¡</span></td>\n";
            // ¾ÃÈñÀÇ·Ú£¸¡ó
            echo "  <th class='winbox' nowrap align='left'><span class='pt9'>­£¡¡³ÎÄê¿½¹ð½ñÇ¼ÉÕÀÇ³Û(¿½¹ð½ñ26)</span></td>\n";
            // ¾ÃÈñÀÇ£±£°¡ó
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>¡¡</span></td>\n";
            // EDP¾ÃÈñÀÇ·×¾å³Û
            echo "  <th class='winbox' nowrap align='right'<span class='pt9'>¡¡</span></td>\n";
            // ¾ÃÈñÀÇ£´¡ó
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>¡¡</span></td>\n";
            // ÀÇ¹þ¶â³Û
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>¡¡</span></td>\n";
            // È÷¹Í
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>" . number_format(42001700) . "</span></td>\n";
            // È÷¹Í
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>¡¡</span></td>\n";
            echo "</tr>\n";
            
            // £Å£Ä£ÐÌ¤Ê§¶â·×¾å»ÅÆþ³Û 3¹ÔÌÜ
            echo "<tr>\n";
            // ÀÇ¹þ¶â³Û
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>¡¡</span></td>\n";
            // ¾ÃÈñÀÇ·Ú£¸¡ó
            echo "  <th class='winbox' nowrap align='left'><span class='pt9'>­¤¡¡º¹°ú¹µ½üÉÔÇ½ÀÇ³Û</span></td>\n";
            // ¾ÃÈñÀÇ£±£°¡ó
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>¡¡</span></td>\n";
            // EDP¾ÃÈñÀÇ·×¾å³Û
            echo "  <th class='winbox' nowrap align='right'<span class='pt9'>¡¡</span></td>\n";
            // ¾ÃÈñÀÇ£´¡ó
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>¡¡</span></td>\n";
            // ÀÇ¹þ¶â³Û
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>¡¡</span></td>\n";
            // È÷¹Í
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>" . number_format(25107) . "</span></td>\n";
            // È÷¹Í
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>¡¡</span></td>\n";
            echo "</tr>\n";
            
            // »ÅÌõÅÁÉ¼»ÅÆþ¹â 1¹ÔÌÜ
            echo "<tr>\n";
            // ¥¿¥¤¥È¥ë
            echo "<td class='winbox' nowrap bgcolor='white' align='left' colspan='8'><div class='pt10b'>¡¡</div></td>\n";
            echo "</tr>\n";
            
            
            // »ÅÌõÅÁÉ¼»ÅÆþ¹â 2¹ÔÌÜ
            echo "<tr>\n";
            // ¥¿¥¤¥È¥ë
            echo "<td class='winbox' nowrap bgcolor='white' align='left' colspan='8'><div class='pt10b'>¾åµ­¶â³Û¤Ï¡¢¿½¹ð½ñÊÌÉ½£´¤Ç¸º»»¤¹¤ë¡£</div></td>\n";
            echo "</tr>\n";
            
            // »ÅÌõÅÁÉ¼»ÅÆþ¹â 3¹ÔÌÜ
            echo "<tr>\n";
            // ¥¿¥¤¥È¥ë
            echo "<td class='winbox' nowrap bgcolor='white' align='left' colspan='8'><div class='pt10b'>¡¡</div></td>\n";
            echo "</tr>\n";
            
            // »ÅÆþ³ä°ú
            echo "<tr>\n";
            // ¥¿¥¤¥È¥ë
            echo "<td class='winbox' nowrap bgcolor='white' align='left' colspan='8'><div class='pt10b'>£²¡¥¾ÃÈñÀÇÅù²ñ·×½èÍý³Û¤È¤Îº¹³Û¸¡¾Ú</div></td>\n";
            echo "</tr>\n";
            
            // Í¢Æþ¼è°ú¤Ë·¸¤ë¾ÃÈñÀÇÅù
            echo "<tr>\n";
            // ÀÇ¹þ¶â³Û
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>¡¡</span></td>\n";
            // ¾ÃÈñÀÇ·Ú£¸¡ó
            echo "  <th class='winbox' nowrap align='left'><span class='pt9'>­¡¡¡²¾¼õ¾ÃÈñÀÇÅù¤Îº¹³Û</span></td>\n";
            // ¾ÃÈñÀÇ£±£°¡ó
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>¡¡</span></td>\n";
            // EDP¾ÃÈñÀÇ·×¾å³Û
            echo "  <th class='winbox' nowrap align='right'<span class='pt9'>¡¡</span></td>\n";
            // ¾ÃÈñÀÇ£´¡ó
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>¡¡</span></td>\n";
            // ÀÇ¹þ¶â³Û
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>¡¡</span></td>\n";
            // È÷¹Í
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>" . number_format(16) . "</span></td>\n";
            // È÷¹Í
            echo "  <th class='winbox' nowrap align='left'><span class='pt9'>±ß</span></td>\n";
            echo "</tr>\n";
            
            // ÆüÅì¹©´ï¾ù¼õ»ñ»º´Ø·¸
            echo "<tr>\n";
            // ÀÇ¹þ¶â³Û
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>¡¡</span></td>\n";
            // ¾ÃÈñÀÇ·Ú£¸¡ó
            echo "  <th class='winbox' nowrap align='left'><span class='pt9'>¡¡¡¡ÍýÏÀ¾å¤Î¾ÃÈñÀÇ³Û</span></td>\n";
            // ¾ÃÈñÀÇ£±£°¡ó
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>¡¡</span></td>\n";
            // EDP¾ÃÈñÀÇ·×¾å³Û
            echo "  <th class='winbox' nowrap align='right'<span class='pt9'>¡¡</span></td>\n";
            // ¾ÃÈñÀÇ£´¡ó
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>¡¡</span></td>\n";
            // ÀÇ¹þ¶â³Û
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>¡¡</span></td>\n";
            // È÷¹Í
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>" . number_format(515943198) . "</span></td>\n";
            // È÷¹Í
            echo "  <th class='winbox' nowrap align='left'><span class='pt9'>¡¡</span></td>\n";
            echo "</tr>\n";
            
            // ¸ÇÄê»ñ»º
            echo "<tr>\n";
            // ÀÇ¹þ¶â³Û
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>¡¡</span></td>\n";
            // ¾ÃÈñÀÇ·Ú£¸¡ó
            echo "  <th class='winbox' nowrap align='left'><span class='pt9'>¡¡¡¡»î»»É½»Ä¹â</span></td>\n";
            // ¾ÃÈñÀÇ£±£°¡ó
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>¡¡</span></td>\n";
            // EDP¾ÃÈñÀÇ·×¾å³Û
            echo "  <th class='winbox' nowrap align='right'<span class='pt9'>¡¡</span></td>\n";
            // ¾ÃÈñÀÇ£´¡ó
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>¡¡</span></td>\n";
            // ÀÇ¹þ¶â³Û
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>¡¡</span></td>\n";
            // È÷¹Í
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>" . number_format(515943182) . "</span></td>\n";
            // È÷¹Í
            echo "  <th class='winbox' nowrap align='left'><span class='pt9'>¡¡</span></td>\n";
            echo "</tr>\n";
            
            // ¸ÇÄê»ñ»º·ÐÈñÊ¬
            echo "<tr>\n";
            // ¥¿¥¤¥È¥ë
            echo "<td class='winbox' nowrap bgcolor='white' align='left' colspan='8'><div class='pt10b'>¡¡</div></td>\n";
            echo "</tr>\n";
            
            // Ãª²·»ñ»º
            echo "<tr>\n";
            // ¥¿¥¤¥È¥ë
            echo "<td class='winbox' nowrap bgcolor='white' align='left' colspan='8'><div class='pt10b'>­¢¡¡¿½¹ð½ñÉÕÉ½2¡Ý(2)¤ÎÈó²ÝÀÇÇä¾å³ä¹ç¤è¤ê·×»»¹µ½üÉÔÇ½³Û»»½Ð</div></td>\n";
            echo "</tr>\n";
            
            // Ãæ´ÖÇ¼ÉÕ·×¾å³Û
            echo "<tr>\n";
            // ÀÇ¹þ¶â³Û
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>¡¡</span></td>\n";
            // ¾ÃÈñÀÇ·Ú£¸¡ó
            echo "  <th class='winbox' nowrap align='left'><span class='pt9'>¡¡¡¡Èó²ÝÀÇÇä¾å</span></td>\n";
            // ¾ÃÈñÀÇ£±£°¡ó
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>" . number_format(285023) . "</span></td>\n";
            // EDP¾ÃÈñÀÇ·×¾å³Û
            echo "  <th class='winbox' nowrap align='right'<span class='pt9'>¡à</span></td>\n";
            // ¾ÃÈñÀÇ£´¡ó
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>" . number_format(5230995387) . "</span></td>\n";
            // ÀÇ¹þ¶â³Û
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>¡á</span></td>\n";
            // È÷¹Í
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>0.00005448733</span></td>\n";
            // È÷¹Í
            echo "  <th class='winbox' nowrap align='left'><span class='pt9'>¡¡</span></td>\n";
            echo "</tr>\n";
            
            // ¡ÊÃæ´ÖÇ¼ÉÕ³Û¡Ë
            echo "<tr>\n";
            // ÀÇ¹þ¶â³Û
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>¡¡</span></td>\n";
            // ¾ÃÈñÀÇ·Ú£¸¡ó
            echo "  <th class='winbox' nowrap align='left'><span class='pt9'>¡¡¡¡²¾Ê§¾ÃÈñÀÇ¡¡8¡ó</span></td>\n";
            // ¾ÃÈñÀÇ£±£°¡ó
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>¡¡</span></td>\n";
            // EDP¾ÃÈñÀÇ·×¾å³Û
            echo "  <th class='winbox' nowrap align='right'<span class='pt9'>¡¡</span></td>\n";
            // ¾ÃÈñÀÇ£´¡ó
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>¡¡</span></td>\n";
            // ÀÇ¹þ¶â³Û
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>¡¡</span></td>\n";
            // È÷¹Í
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>" . number_format(1542329) . "</span></td>\n";
            // È÷¹Í
            echo "  <th class='winbox' nowrap align='left'><span class='pt9'>±ß</span></td>\n";
            echo "</tr>\n";
            
            // ·×
            echo "<tr>\n";
            // ÀÇ¹þ¶â³Û
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>¡¡</span></td>\n";
            // ¾ÃÈñÀÇ·Ú£¸¡ó
            echo "  <th class='winbox' nowrap align='left'><span class='pt9'>¡¡¡¡²¾Ê§¾ÃÈñÀÇ¡¡8¡ó·Ú</span></td>\n";
            // ¾ÃÈñÀÇ£±£°¡ó
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>¡¡</span></td>\n";
            // EDP¾ÃÈñÀÇ·×¾å³Û
            echo "  <th class='winbox' nowrap align='right'<span class='pt9'>¡¡</span></td>\n";
            // ¾ÃÈñÀÇ£´¡ó
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>¡¡</span></td>\n";
            // ÀÇ¹þ¶â³Û
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>¡¡</span></td>\n";
            // È÷¹Í
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>" . number_format(11528) . "</span></td>\n";
            // È÷¹Í
            echo "  <th class='winbox' nowrap align='left'><span class='pt9'>¡¡</span></td>\n";
            echo "</tr>\n";
            
            // ¥¿¥¤¥È¥ë¤Ê¤·
            echo "<tr>\n";
            // ÀÇ¹þ¶â³Û
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>¡¡</span></td>\n";
            // ¾ÃÈñÀÇ·Ú£¸¡ó
            echo "  <th class='winbox' nowrap align='left'><span class='pt9'>¡¡¡¡²¾Ê§¾ÃÈñÀÇ¡¡10¡ó</span></td>\n";
            // ¾ÃÈñÀÇ£±£°¡ó
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>¡¡</span></td>\n";
            // EDP¾ÃÈñÀÇ·×¾å³Û
            echo "  <th class='winbox' nowrap align='right'<span class='pt9'>¡¡</span></td>\n";
            // ¾ÃÈñÀÇ£´¡ó
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>¡¡</span></td>\n";
            // ÀÇ¹þ¶â³Û
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>¡¡</span></td>\n";
            // È÷¹Í
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>" . number_format(453211332) . "</span></td>\n";
            // È÷¹Í
            echo "  <th class='winbox' nowrap align='left'><span class='pt9'>¡¡</span></td>\n";
            echo "</tr>\n";
            
            // ¥¿¥¤¥È¥ë¤Ê¤·
            echo "<tr>\n";
            // ÀÇ¹þ¶â³Û
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>¡¡</span></td>\n";
            // ¾ÃÈñÀÇ·Ú£¸¡ó
            echo "  <th class='winbox' nowrap align='left'><span class='pt9'>¡¡¡¡Í¢Æþ¾ÃÈñÀÇ</span></td>\n";
            // ¾ÃÈñÀÇ£±£°¡ó
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>¡¡</span></td>\n";
            // EDP¾ÃÈñÀÇ·×¾å³Û
            echo "  <th class='winbox' nowrap align='right'<span class='pt9'>¡¡</span></td>\n";
            // ¾ÃÈñÀÇ£´¡ó
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>¡¡</span></td>\n";
            // ÀÇ¹þ¶â³Û
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>¡¡</span></td>\n";
            // È÷¹Í
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>" . number_format(3989200) . "</span></td>\n";
            // È÷¹Í
            echo "  <th class='winbox' nowrap align='left'><span class='pt9'>¡¡</span></td>\n";
            echo "</tr>\n";
            
            // ¥¿¥¤¥È¥ë¤Ê¤·
            echo "<tr>\n";
            // ÀÇ¹þ¶â³Û
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>¡¡</span></td>\n";
            // ¾ÃÈñÀÇ·Ú£¸¡ó
            echo "  <th class='winbox' nowrap align='left'><span class='pt9'>¡¡¡¡¹ç¡¡¡¡¡¡¡¡¡¡·×</span></td>\n";
            // ¾ÃÈñÀÇ£±£°¡ó
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>¡¡</span></td>\n";
            // EDP¾ÃÈñÀÇ·×¾å³Û
            echo "  <th class='winbox' nowrap align='right'<span class='pt9'>¡¡</span></td>\n";
            // ¾ÃÈñÀÇ£´¡ó
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>¡¡</span></td>\n";
            // ÀÇ¹þ¶â³Û
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>¡¡</span></td>\n";
            // È÷¹Í
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>" . number_format(458754389) . "</span></td>\n";
            // È÷¹Í
            echo "  <th class='winbox' nowrap align='left'><span class='pt9'>¡¡</span></td>\n";
            echo "</tr>\n";
            
            // ¥¿¥¤¥È¥ë¤Ê¤·
            echo "<tr>\n";
            // ÀÇ¹þ¶â³Û
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>¡¡</span></td>\n";
            // ¾ÃÈñÀÇ·Ú£¸¡ó
            echo "  <th class='winbox' nowrap align='left' colspan='2'><span class='pt9'>¡¡¡¡¾åµ­¶â³Û¤ËÈó²ÝÀÇÇä¾å³ä¹ç¤ò¾è¤¸¤¿³Û</span></td>\n";
            // EDP¾ÃÈñÀÇ·×¾å³Û
            echo "  <th class='winbox' nowrap align='right'<span class='pt9'>¡¡</span></td>\n";
            // ¾ÃÈñÀÇ£´¡ó
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>¡¡</span></td>\n";
            // ÀÇ¹þ¶â³Û
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>¡¡</span></td>\n";
            // È÷¹Í
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>" . number_format(24996) . "</span></td>\n";
            // È÷¹Í
            echo "  <th class='winbox' nowrap align='left'><span class='pt9'>¡¡</span></td>\n";
            echo "</tr>\n";
            
            // ¥¿¥¤¥È¥ë¤Ê¤·
            echo "<tr>\n";
            // ¥¿¥¤¥È¥ë
            echo "<td class='winbox' nowrap bgcolor='white' align='left' colspan='8'><div class='pt10b'>¡¡</div></td>\n";
            echo "</tr>\n";
            
            // Ãª²·»ñ»º
            echo "<tr>\n";
            // ¥¿¥¤¥È¥ë
            echo "<td class='winbox' nowrap bgcolor='white' align='left' colspan='8'><div class='pt10b'>£³¡¥·ë²Ì</div></td>\n";
            echo "</tr>\n";
            
            // ¥¿¥¤¥È¥ë¤Ê¤·
            echo "<tr>\n";
            // ÀÇ¹þ¶â³Û
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>¡¡</span></td>\n";
            // ¾ÃÈñÀÇ·Ú£¸¡ó
            echo "  <th class='winbox' nowrap align='left'><span class='pt9'>¡¡¡¡£²¡Ý­¡</span></td>\n";
            // ¾ÃÈñÀÇ£±£°¡ó
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>" . number_format(16) . "</span></td>\n";
            // EDP¾ÃÈñÀÇ·×¾å³Û
            echo "  <th class='winbox' nowrap align='right'<span class='pt9'>±ß¡¡¡Ü¡¡£²¡Ý­¢</span></td>\n";
            // ¾ÃÈñÀÇ£´¡ó
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>" . number_format(24996) . "</span></td>\n";
            // ÀÇ¹þ¶â³Û
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>±ß¡¡¡á¡¡</span></td>\n";
            // È÷¹Í
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>" . number_format(25012) . "</span></td>\n";
            // È÷¹Í
            echo "  <th class='winbox' nowrap align='left'><span class='pt9'>±ß</span></td>\n";
            echo "</tr>\n";
            
            // ¥¿¥¤¥È¥ë¤Ê¤·
            echo "<tr>\n";
            // ÀÇ¹þ¶â³Û
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>¡¡</span></td>\n";
            // ¾ÃÈñÀÇ·Ú£¸¡ó
            echo "  <th class='winbox' nowrap align='left'><span class='pt9'>¡¡¡¡½¾¤Ã¤Æ¡¢¾åµ­£±¤Î±ß¤È¤Îº¹³Û¤Ï¡¢</span></td>\n";
            // ¾ÃÈñÀÇ£±£°¡ó
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>" . number_format(95) . "</span></td>\n";
            // EDP¾ÃÈñÀÇ·×¾å³Û
            echo "  <th class='winbox' nowrap align='left' colspan='4'><span class='pt9'>±ß¤Ç¤¢¤ê¡¢ÂÅÅö¤Ç¤¢¤ë¤³¤È¤ò³ÎÇ§¤·¤¿¡£</span></td>\n";
            // È÷¹Í
            echo "  <th class='winbox' nowrap align='left'><span class='pt9'>¡¡</span></td>\n";
            echo "</tr>\n";
            ?>
            </tbody>
        </table>
            </td></tr>
        </table> <!----------------- ¥À¥ß¡¼End ------------------>
        
        <form method='post' action='<?php echo $menu->out_self() ?>'>
            <input class='pt10b' type='submit' name='input_data' value='ÅÐÏ¿' onClick='return data_input_click(this)'>
        </form>
    </center>
</body>
</html>
