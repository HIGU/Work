<?php
//////////////////////////////////////////////////////////////////////////////
// ·î¼¡Â»±×´Ø·¸ ·î¼¡ £Ã£Ì·ÐÈñ Èæ³ÓÉ½                                        //
// Copyright(C) 2008-2015 Norihisa.Ohya norihisa_ooya@nitto-kohki.co.jp     //
// Changed history                                                          //
// 2008/10/07 Created                                                       //
//            profit_loss_cl_keihi_compare.php(profit_loss_cl_keihi.php¤è¤ê)//
// 2010/01/20 ·ÐÈñ¹ç·×¤Îº¹³ÛÈæ³Ó¤òÄÉ²Ã                                      //
// 2010/02/08 ¾¦´É¤Îº¹³ÛÈæ³Ó¤òÄÉ²Ã¡¢»î½¤¤Î¿Í·ïÈñÄ´À°¤ò¥Þ¥¹¥¿¡¼¤«¤é²ÃÌ£      //
// 2012/02/08 2012Ç¯1·î ¶ÈÌ³°ÑÂ÷Èñ Ä´À° ¥ê¥Ë¥¢À½Â¤·ÐÈñ +1,156,130±ß    ÂçÃ« //
//             ¢¨ Ê¿½Ð²£ÀîÇÉ¸¯ÎÁ 2·î¤ËµÕÄ´À°¤ò¹Ô¤¦¤³¤È                      //
// 2012/02/13 ¥Ç¡¼¥¿¼èÆÀ¤òÅÔÅÙ·×»»¤Ç¤Ï¤Ê¤¯¡¢ÍúÎò¤«¤é¼èÆÀ¤ËÊÑ¹¹         ÂçÃ« //
//             ¢¨ ¾åµ­µÕÄ´À°¤ÏÉ¬Í×¤Ê¤·                                      //
// 2015/04/10 ¥¯¥ì¡¼¥àÂÐ±þÈñ¤ÎÄÉ²Ã¤ËÂÐ±þ                               ÂçÃ« //
//////////////////////////////////////////////////////////////////////////////
ini_set('error_reporting', E_STRICT);       // E_STRICT=2048(php5) E_ALL=2047 debug ÍÑ
// ini_set('error_reporting',E_ALL);           // E_ALL='2047' debug ÍÑ
// ini_set('display_errors','1');              // Error É½¼¨ ON debug ÍÑ 
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

///// ´ü¡¦·î¤Î¼èÆÀ
$ki = Ym_to_tnk($_SESSION['pl_ym']);
$tuki = substr($_SESSION['pl_ym'],4,2);

//////////// ¥¿¥¤¥È¥ëÌ¾(¥½¡¼¥¹¤Î¥¿¥¤¥È¥ëÌ¾¤È¥Õ¥©¡¼¥à¤Î¥¿¥¤¥È¥ëÌ¾)
$menu->set_title("Âè {$ki} ´ü¡¡{$tuki} ·îÅÙ¡¡£Ã £Ì ·Ð Èñ º¹ ³Û Èæ ³Ó É½");

///// ÂÐ¾ÝÅö·î
$yyyymm = $_SESSION['pl_ym'];
///// ÂÐ¾ÝÁ°·î
if (substr($yyyymm,4,2)!=01) {
    $p1_ym = $yyyymm - 1;
} else {
    $p1_ym = $yyyymm - 100;
    $p1_ym = $p1_ym + 11;
}
///// ÂÐ¾ÝÁ°¡¹·î
if (substr($p1_ym,4,2)!=01) {
    $p2_ym = $p1_ym - 1;
} else {
    $p2_ym = $p1_ym - 100;
    $p2_ym = $p2_ym + 11;
}
///// É½¼¨Ã±°Ì¤òÀßÄê¼èÆÀ
if (isset($_POST['keihi_tani'])) {
    $_SESSION['keihi_tani'] = $_POST['keihi_tani'];
    $tani = $_SESSION['keihi_tani'];
} elseif (isset($_SESSION['keihi_tani'])) {
    $tani = $_SESSION['keihi_tani'];
} else {
    $tani = 1000;        // ½é´üÃÍ É½¼¨Ã±°Ì Àé±ß
    $_SESSION['keihi_tani'] = $tani;
}
///// É½¼¨ ¾®¿ôÉô·å¿ô ÀßÄê¼èÆÀ
if (isset($_POST['keihi_keta'])) {
    $_SESSION['keihi_keta'] = $_POST['keihi_keta'];
    $keta = $_SESSION['keihi_keta'];
} elseif (isset($_SESSION['keihi_keta'])) {
    $keta = $_SESSION['keihi_keta'];
} else {
    $keta = 0;          // ½é´üÃÍ ¾®¿ôÅÀ°Ê²¼·å¿ô
    $_SESSION['keihi_keta'] = $keta;
}

///////// ¹àÌÜ¤È¥¤¥ó¥Ç¥Ã¥¯¥¹¤Î´ØÏ¢ÉÕ¤±
$item = array();
$item[0]   = "Ìò°÷Êó½·";
$item[1]   = "µëÎÁ¼êÅö";
$item[2]   = "¾ÞÍ¿¼êÅö";
$item[3]   = "¸ÜÌäÎÁ";
$item[4]   = "Ë¡ÄêÊ¡ÍøÈñ";
$item[5]   = "¸üÀ¸Ê¡ÍøÈñ";
$item[6]   = "¾ÞÍ¿°úÅö¶â·«Æþ";
$item[7]   = "Âà¿¦µëÉÕÈñÍÑ";
$item[8]   = "¿Í·ïÈñ·×";
$item[9]   = "Î¹Èñ¸òÄÌÈñ";
$item[10]  = "³¤³°½ÐÄ¥";
$item[11]  = "ÄÌ¿®Èñ";
$item[12]  = "²ñµÄÈñ";
$item[13]  = "¸òºÝÀÜÂÔÈñ";
$item[14]  = "¹­¹ðÀëÅÁÈñ";
$item[15]  = "µá¿ÍÈñ";
$item[16]  = "±¿ÄÂ²ÙÂ¤Èñ";
$item[17]  = "¿Þ½ñ¶µ°éÈñ";
$item[18]  = "¶ÈÌ³°ÑÂ÷Èñ";
$item[19]  = "»ö¶ÈÅù";
$item[20]  = "½ôÀÇ¸ø²Ý";
$item[21]  = "»î¸³¸¦µæÈñ";
$item[22]  = "»¨Èñ";
$item[23]  = "½¤Á¶Èñ";
$item[24]  = "ÊÝ¾Ú½¤ÍýÈñ";
$item[25]  = "»öÌ³ÍÑ¾ÃÌ×ÉÊÈñ";
$item[26]  = "¹©¾ì¾ÃÌ×ÉÊÈñ";
$item[27]  = "¼ÖÎ¾Èñ";
$item[28]  = "ÊÝ¸±ÎÁ";
$item[29]  = "¿åÆ»¸÷Ç®Èñ";
$item[30]  = "½ô²ñÈñ";
$item[31]  = "»ÙÊ§¼ê¿ôÎÁ";
$item[32]  = "ÃÏÂå²ÈÄÂ";
$item[33]  = "´óÉÕ¶â";
$item[34]  = "ÁÒÉßÎÁ";
$item[35]  = "ÄÂ¼ÚÎÁ";
$item[36]  = "¸º²Á½þµÑÈñ";
$item[37]  = "¥¯¥ì¡¼¥àÂÐ±þÈñ";
$item[38]  = "·ÐÈñ·×";
$item[39]  = "¹ç·×";

for ($i = 0; $i < 40; $i++) {
    $head  = "¥«¥×¥éÀ½Â¤·ÐÈñ";
    $item_in = array();
    $item_in[$i] = $head . $item[$i];
    $query = sprintf("select kin from profit_loss_keihi_history where pl_bs_ym=%d and note='%s'", $p1_ym, $item_in[$i]);
    if (getUniResult($query, $res_in[$i][1]) < 1) {
        $res_in[$i][1] = 0;                 // ¸¡º÷¼ºÇÔ
    }
    $query = sprintf("select kin from profit_loss_keihi_history where pl_bs_ym=%d and note='%s'", $yyyymm, $item_in[$i]);
    if (getUniResult($query, $res_in[$i][2]) < 1) {
        $res_in[$i][2] = 0;                 // ¸¡º÷¼ºÇÔ
    }
    $res_in[$i][3] = $res_in[$i][2] - $res_in[$i][1];
    
    $head  = "¥ê¥Ë¥¢À½Â¤·ÐÈñ";
    $item_in = array();
    $item_in[$i] = $head . $item[$i];
    $query = sprintf("select kin from profit_loss_keihi_history where pl_bs_ym=%d and note='%s'", $p1_ym, $item_in[$i]);
    if (getUniResult($query, $res_in[$i][4]) < 1) {
        $res_in[$i][4] = 0;                 // ¸¡º÷¼ºÇÔ
    }
    $query = sprintf("select kin from profit_loss_keihi_history where pl_bs_ym=%d and note='%s'", $yyyymm, $item_in[$i]);
    if (getUniResult($query, $res_in[$i][5]) < 1) {
        $res_in[$i][5] = 0;                 // ¸¡º÷¼ºÇÔ
    }
    $res_in[$i][6] = $res_in[$i][5] - $res_in[$i][4];
    
    $head  = "¾¦´ÉÀ½Â¤·ÐÈñ";
    $item_in = array();
    $item_in[$i] = $head . $item[$i];
    $query = sprintf("select kin from profit_loss_keihi_history where pl_bs_ym=%d and note='%s'", $p1_ym, $item_in[$i]);
    if (getUniResult($query, $res_in[$i][7]) < 1) {
        $res_in[$i][7] = 0;                 // ¸¡º÷¼ºÇÔ
    }
    $query = sprintf("select kin from profit_loss_keihi_history where pl_bs_ym=%d and note='%s'", $yyyymm, $item_in[$i]);
    if (getUniResult($query, $res_in[$i][8]) < 1) {
        $res_in[$i][8] = 0;                 // ¸¡º÷¼ºÇÔ
    }
    $res_in[$i][9] = $res_in[$i][8] - $res_in[$i][7];
    
    // À½Â¤·ÐÈñ¹ç·×·×»»
    $res_in[$i][10] = $res_in[$i][1] + $res_in[$i][4] + $res_in[$i][7];
    $res_in[$i][11] = $res_in[$i][2] + $res_in[$i][5] + $res_in[$i][8];
    $res_in[$i][12] = $res_in[$i][3] + $res_in[$i][6] + $res_in[$i][9];
    
    $head  = "¥«¥×¥éÈÎ´ÉÈñ";
    $item_in = array();
    $item_in[$i] = $head . $item[$i];
    $query = sprintf("select kin from profit_loss_keihi_history where pl_bs_ym=%d and note='%s'", $p1_ym, $item_in[$i]);
    if (getUniResult($query, $res_in[$i][13]) < 1) {
        $res_in[$i][13] = 0;                 // ¸¡º÷¼ºÇÔ
    }
    $query = sprintf("select kin from profit_loss_keihi_history where pl_bs_ym=%d and note='%s'", $yyyymm, $item_in[$i]);
    if (getUniResult($query, $res_in[$i][14]) < 1) {
        $res_in[$i][14] = 0;                 // ¸¡º÷¼ºÇÔ
    }
    $res_in[$i][15] = $res_in[$i][14] - $res_in[$i][13];
    
    $head  = "¥ê¥Ë¥¢ÈÎ´ÉÈñ";
    $item_in = array();
    $item_in[$i] = $head . $item[$i];
    $query = sprintf("select kin from profit_loss_keihi_history where pl_bs_ym=%d and note='%s'", $p1_ym, $item_in[$i]);
    if (getUniResult($query, $res_in[$i][16]) < 1) {
        $res_in[$i][16] = 0;                 // ¸¡º÷¼ºÇÔ
    }
    $query = sprintf("select kin from profit_loss_keihi_history where pl_bs_ym=%d and note='%s'", $yyyymm, $item_in[$i]);
    if (getUniResult($query, $res_in[$i][17]) < 1) {
        $res_in[$i][17] = 0;                 // ¸¡º÷¼ºÇÔ
    }
    $res_in[$i][18] = $res_in[$i][17] - $res_in[$i][16];
    
    $head  = "¾¦´ÉÈÎ´ÉÈñ";
    $item_in = array();
    $item_in[$i] = $head . $item[$i];
    $query = sprintf("select kin from profit_loss_keihi_history where pl_bs_ym=%d and note='%s'", $p1_ym, $item_in[$i]);
    if (getUniResult($query, $res_in[$i][19]) < 1) {
        $res_in[$i][19] = 0;                 // ¸¡º÷¼ºÇÔ
    }
    $query = sprintf("select kin from profit_loss_keihi_history where pl_bs_ym=%d and note='%s'", $yyyymm, $item_in[$i]);
    if (getUniResult($query, $res_in[$i][20]) < 1) {
        $res_in[$i][20] = 0;                 // ¸¡º÷¼ºÇÔ
    }
    $res_in[$i][21] = $res_in[$i][20] - $res_in[$i][19];
    
    // ÈÎ´ÉÈñ¹ç·×·×»»
    $res_in[$i][22] = $res_in[$i][13] + $res_in[$i][16] + $res_in[$i][19];
    $res_in[$i][23] = $res_in[$i][14] + $res_in[$i][17] + $res_in[$i][20];
    $res_in[$i][24] = $res_in[$i][15] + $res_in[$i][18] + $res_in[$i][21];
    
    // ¥«¥×¥é·ÐÈñ¹ç·×·×»»
    $res_in[$i][25] = $res_in[$i][1] + $res_in[$i][13];
    $res_in[$i][26] = $res_in[$i][2] + $res_in[$i][14];
    $res_in[$i][27] = $res_in[$i][3] + $res_in[$i][15];
    
    // ¥ê¥Ë¥¢·ÐÈñ¹ç·×·×»»
    $res_in[$i][28] = $res_in[$i][4] + $res_in[$i][16];
    $res_in[$i][29] = $res_in[$i][5] + $res_in[$i][17];
    $res_in[$i][30] = $res_in[$i][6] + $res_in[$i][18];
    
    // ¾¦´É·ÐÈñ¹ç·×·×»»
    $res_in[$i][31] = $res_in[$i][7] + $res_in[$i][19];
    $res_in[$i][32] = $res_in[$i][8] + $res_in[$i][20];
    $res_in[$i][33] = $res_in[$i][9] + $res_in[$i][21];
    
    // ·ÐÈñÁí¹ç·×·×»»
    $res_in[$i][34] = $res_in[$i][25] + $res_in[$i][28] + $res_in[$i][31];
    $res_in[$i][35] = $res_in[$i][26] + $res_in[$i][29] + $res_in[$i][32];
    $res_in[$i][36] = $res_in[$i][27] + $res_in[$i][30] + $res_in[$i][33];
    
    $view_data[$i][1]  = number_format(($res_in[$i][1] / $tani), $keta);
    $view_data[$i][2]  = number_format(($res_in[$i][2] / $tani), $keta);
    $view_data[$i][3]  = number_format(($res_in[$i][3] / $tani), $keta);
    $view_data[$i][4]  = number_format(($res_in[$i][4] / $tani), $keta);
    $view_data[$i][5]  = number_format(($res_in[$i][5] / $tani), $keta);
    $view_data[$i][6]  = number_format(($res_in[$i][6] / $tani), $keta);
    $view_data[$i][7]  = number_format(($res_in[$i][7] / $tani), $keta);
    $view_data[$i][8]  = number_format(($res_in[$i][8] / $tani), $keta);
    $view_data[$i][9]  = number_format(($res_in[$i][9] / $tani), $keta);
    $view_data[$i][10] = number_format(($res_in[$i][10] / $tani), $keta);
    $view_data[$i][11] = number_format(($res_in[$i][11] / $tani), $keta);
    $view_data[$i][12] = number_format(($res_in[$i][12] / $tani), $keta);
    $view_data[$i][13] = number_format(($res_in[$i][13] / $tani), $keta);
    $view_data[$i][14] = number_format(($res_in[$i][14] / $tani), $keta);
    $view_data[$i][15] = number_format(($res_in[$i][15] / $tani), $keta);
    $view_data[$i][16] = number_format(($res_in[$i][16] / $tani), $keta);
    $view_data[$i][17] = number_format(($res_in[$i][17] / $tani), $keta);
    $view_data[$i][18] = number_format(($res_in[$i][18] / $tani), $keta);
    $view_data[$i][19] = number_format(($res_in[$i][19] / $tani), $keta);
    $view_data[$i][20] = number_format(($res_in[$i][20] / $tani), $keta);
    $view_data[$i][21] = number_format(($res_in[$i][21] / $tani), $keta);
    $view_data[$i][22] = number_format(($res_in[$i][22] / $tani), $keta);
    $view_data[$i][23] = number_format(($res_in[$i][23] / $tani), $keta);
    $view_data[$i][24] = number_format(($res_in[$i][24] / $tani), $keta);
    $view_data[$i][25] = number_format(($res_in[$i][25] / $tani), $keta);
    $view_data[$i][26] = number_format(($res_in[$i][26] / $tani), $keta);
    $view_data[$i][27] = number_format(($res_in[$i][27] / $tani), $keta);
    $view_data[$i][28] = number_format(($res_in[$i][28] / $tani), $keta);
    $view_data[$i][29] = number_format(($res_in[$i][29] / $tani), $keta);
    $view_data[$i][30] = number_format(($res_in[$i][30] / $tani), $keta);
    $view_data[$i][31] = number_format(($res_in[$i][31] / $tani), $keta);
    $view_data[$i][32] = number_format(($res_in[$i][32] / $tani), $keta);
    $view_data[$i][33] = number_format(($res_in[$i][33] / $tani), $keta);
    $view_data[$i][34] = number_format(($res_in[$i][34] / $tani), $keta);
    $view_data[$i][35] = number_format(($res_in[$i][35] / $tani), $keta);
    $view_data[$i][36] = number_format(($res_in[$i][36] / $tani), $keta);
}

/////////// HTML Header ¤ò½ÐÎÏ¤·¤Æ¥­¥ã¥Ã¥·¥å¤òÀ©¸æ
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
                        Ã±°Ì
                        <select name='keihi_tani' class='pt10'>
                        <?php
                            if ($tani == 1000)
                                echo "<option value='1000' selected>¡¡Àé±ß</option>\n";
                            else
                                echo "<option value='1000'>¡¡Àé±ß</option>\n";
                            if ($tani == 1)
                                echo "<option value='1' selected>¡¡¡¡±ß</option>\n";
                            else
                                echo "<option value='1'>¡¡¡¡±ß</option>\n";
                            if ($tani == 1000000)
                                echo "<option value='1000000' selected>É´Ëü±ß</option>\n";
                            else
                                echo "<option value='1000000'>É´Ëü±ß</option>\n";
                            if ($tani == 10000)
                                echo "<option value='10000' selected>¡¡Ëü±ß</option>\n";
                            else
                                echo "<option value='10000'>¡¡Ëü±ß</option>\n";
                            if($tani == 100000)
                                echo "<option value='100000' selected>½½Ëü±ß</option>\n";
                            else
                                echo "<option value='100000'>½½Ëü±ß</option>\n";
                        ?>
                        </select>
                        ¾¯¿ô·å
                        <select name='keihi_keta' class='pt10'>
                        <?php
                            if ($keta == 0)
                                echo "<option value='0' selected>£°·å</option>\n";
                            else
                                echo "<option value='0'>£°·å</option>\n";
                            if ($keta == 3)
                                echo "<option value='3' selected>£³·å</option>\n";
                            else
                                echo "<option value='3'>£³·å</option>\n";
                            if ($keta == 6)
                                echo "<option value='6' selected>£¶·å</option>\n";
                            else
                                echo "<option value='6'>£¶·å</option>\n";
                            if ($keta == 1)
                                echo "<option value='1' selected>£±·å</option>\n";
                            else
                                echo "<option value='1'>£±·å</option>\n";
                            if ($keta == 2)
                                echo "<option value='2' selected>£²·å</option>\n";
                            else
                                echo "<option value='2'>£²·å</option>\n";
                            if ($keta == 4)
                                echo "<option value='4' selected>£´·å</option>\n";
                            else
                                echo "<option value='4'>£´·å</option>\n";
                            if ($keta == 5)
                                echo "<option value='5' selected>£µ·å</option>\n";
                            else
                                echo "<option value='5'>£µ·å</option>\n";
                        ?>
                        </select>
                        <input class='pt10b' type='submit' name='return' value='Ã±°ÌÊÑ¹¹'>
                    </td>
                </form>
            </tr>
        </table>
        <!-- win_gray='#d6d3ce' -->
        <table width='100%' bgcolor='white' align='center' cellspacing="0" cellpadding="3" border='1'>
            <TBODY>
                <tr>
                    <td width='10' rowspan='3' align='center' class='pt10' bgcolor='#ccffff'>¶èÊ¬</td>
                    <td rowspan='3' nowrap align='center' class='pt10b' bgcolor='#ccffff'>´ªÄê²ÊÌÜ</td>
                    <td colspan='12' nowrap align='center' class='pt10b' bgcolor='#ffffc6'>À½¡¡Â¤¡¡·Ð¡¡Èñ</td>
                    <td colspan='12' nowrap align='center' class='pt10b' bgcolor='#ceffce'>ÈÎÇäÈñµÚ¤Ó°ìÈÌ´ÉÍýÈñ</td>
                    <td colspan='12' nowrap align='center' class='pt10b' bgcolor='#ccffff'>·Ð¡¡Èñ¡¡¹ç¡¡·×</td>
                </tr>
                <tr>
                    <td colspan='3' nowrap align='center' class='pt10b' bgcolor='#ffffc6'>¥«¥×¥é</td>
                    <td colspan='3' nowrap align='center' class='pt10b' bgcolor='#ffffc6'>¥ê¥Ë¥¢</td>
                    <td colspan='3' nowrap align='center' class='pt10b' bgcolor='#ffffc6'>¾¦´É</td>
                    <td colspan='3' nowrap align='center' class='pt10b' bgcolor='#ffffc6'>¹ç·×</td>
                    <td colspan='3' nowrap align='center' class='pt10b' bgcolor='#ceffce'>¥«¥×¥é</td>
                    <td colspan='3' nowrap align='center' class='pt10b' bgcolor='#ceffce'>¥ê¥Ë¥¢</td>
                    <td colspan='3' nowrap align='center' class='pt10b' bgcolor='#ceffce'>¾¦´É</td>
                    <td colspan='3' nowrap align='center' class='pt10b' bgcolor='#ceffce'>¹ç·×</td>
                    <td colspan='3' nowrap align='center' class='pt10b' bgcolor='#ccffff'>¥«¥×¥é</td>
                    <td colspan='3' nowrap align='center' class='pt10b' bgcolor='#ccffff'>¥ê¥Ë¥¢</td>
                    <td colspan='3' nowrap align='center' class='pt10b' bgcolor='#ccffff'>¾¦´É</td>
                    <td colspan='3' nowrap align='center' class='pt10b' bgcolor='#ccffff'>¹ç·×</td>
                </tr>
                <tr>
                    <td nowrap align='center' class='pt10b' bgcolor='#ffffc6'>Á°·î</td>
                    <td nowrap align='center' class='pt10b' bgcolor='#ffffc6'>Åö·î</td>
                    <td nowrap align='center' class='pt10b' bgcolor='#ffffc6'>º¹³Û</td>
                    <td nowrap align='center' class='pt10b' bgcolor='#ffffc6'>Á°·î</td>
                    <td nowrap align='center' class='pt10b' bgcolor='#ffffc6'>Åö·î</td>
                    <td nowrap align='center' class='pt10b' bgcolor='#ffffc6'>º¹³Û</td>
                    <td nowrap align='center' class='pt10b' bgcolor='#ffffc6'>Á°·î</td>
                    <td nowrap align='center' class='pt10b' bgcolor='#ffffc6'>Åö·î</td>
                    <td nowrap align='center' class='pt10b' bgcolor='#ffffc6'>º¹³Û</td>
                    <td nowrap align='center' class='pt10b' bgcolor='#ffffc6'>Á°·î</td>
                    <td nowrap align='center' class='pt10b' bgcolor='#ffffc6'>Åö·î</td>
                    <td nowrap align='center' class='pt10b' bgcolor='#ffffc6'>º¹³Û</td>
                    <td nowrap align='center' class='pt10b' bgcolor='#ceffce'>Á°·î</td>
                    <td nowrap align='center' class='pt10b' bgcolor='#ceffce'>Åö·î</td>
                    <td nowrap align='center' class='pt10b' bgcolor='#ceffce'>º¹³Û</td>
                    <td nowrap align='center' class='pt10b' bgcolor='#ceffce'>Á°·î</td>
                    <td nowrap align='center' class='pt10b' bgcolor='#ceffce'>Åö·î</td>
                    <td nowrap align='center' class='pt10b' bgcolor='#ceffce'>º¹³Û</td>
                    <td nowrap align='center' class='pt10b' bgcolor='#ceffce'>Á°·î</td>
                    <td nowrap align='center' class='pt10b' bgcolor='#ceffce'>Åö·î</td>
                    <td nowrap align='center' class='pt10b' bgcolor='#ceffce'>º¹³Û</td>
                    <td nowrap align='center' class='pt10b' bgcolor='#ceffce'>Á°·î</td>
                    <td nowrap align='center' class='pt10b' bgcolor='#ceffce'>Åö·î</td>
                    <td nowrap align='center' class='pt10b' bgcolor='#ceffce'>º¹³Û</td>
                    <td nowrap align='center' class='pt10b' bgcolor='#ccffff'>Á°·î</td>
                    <td nowrap align='center' class='pt10b' bgcolor='#ccffff'>Åö·î</td>
                    <td nowrap align='center' class='pt10b' bgcolor='#ccffff'>º¹³Û</td>
                    <td nowrap align='center' class='pt10b' bgcolor='#ccffff'>Á°·î</td>
                    <td nowrap align='center' class='pt10b' bgcolor='#ccffff'>Åö·î</td>
                    <td nowrap align='center' class='pt10b' bgcolor='#ccffff'>º¹³Û</td>
                    <td nowrap align='center' class='pt10b' bgcolor='#ccffff'>Á°·î</td>
                    <td nowrap align='center' class='pt10b' bgcolor='#ccffff'>Åö·î</td>
                    <td nowrap align='center' class='pt10b' bgcolor='#ccffff'>º¹³Û</td>
                    <td nowrap align='center' class='pt10b' bgcolor='#ccffff'>Á°·î</td>
                    <td nowrap align='center' class='pt10b' bgcolor='#ccffff'>Åö·î</td>
                    <td nowrap align='center' class='pt10b' bgcolor='#ccffff'>º¹³Û</td>
                </tr>
                <tr>
                    <td width='10' rowspan='9' align='center' class='pt10b' bgcolor='#ccffff'>¿Í·ïÈñ</td>
                    <TD nowrap class='pt10'>Ìò°÷Êó½·</TD>
                    <?php
                        $r = 0;     // ³ºÅö¥ì¥³¡¼¥É
                        for ($c=1;$c<37;$c++) {
                            if ($c == 3 || $c == 6 || $c == 9 || $c == 12) {                // À½Â¤·ÐÈñÁý¸º
                                printf("<td nowrap class='pt10b' align='right' bgcolor='#ffffc6'>%s</td>\n",$view_data[$r][$c]);
                            } elseif ($c == 15 || $c == 18 || $c == 21 || $c == 24) {       // ÈÎ´ÉÈñÁý¸º
                                printf("<td nowrap class='pt10b' align='right' bgcolor='#ceffce'>%s</td>\n",$view_data[$r][$c]);
                            } elseif ($c == 27 || $c == 30 || $c == 33 || $c == 36) {       // ·ÐÈñ¹ç·×Áý¸º
                                printf("<td nowrap class='pt10b' align='right' bgcolor='#ccffff'>%s</td>\n",$view_data[$r][$c]);
                            } else {
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ffffff'>%s</td>\n",$view_data[$r][$c]);
                            }
                        }
                    ?>
                </tr>
                <TR>
                    <TD nowrap class='pt10'>µëÎÁ¼êÅö</TD>
                    <?php
                        $r = 1;     // ³ºÅö¥ì¥³¡¼¥É
                        for ($c=1;$c<37;$c++) {
                            if ($c == 3 || $c == 6 || $c == 9 || $c == 12) {                // À½Â¤·ÐÈñÁý¸º
                                printf("<td nowrap class='pt10b' align='right' bgcolor='#ffffc6'>%s</td>\n",$view_data[$r][$c]);
                            } elseif ($c == 15 || $c == 18 || $c == 21 || $c == 24) {       // ÈÎ´ÉÈñÁý¸º
                                printf("<td nowrap class='pt10b' align='right' bgcolor='#ceffce'>%s</td>\n",$view_data[$r][$c]);
                            } elseif ($c == 27 || $c == 30 || $c == 33 || $c == 36) {       // ·ÐÈñ¹ç·×Áý¸º
                                printf("<td nowrap class='pt10b' align='right' bgcolor='#ccffff'>%s</td>\n",$view_data[$r][$c]);
                            } else {
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ffffff'>%s</td>\n",$view_data[$r][$c]);
                            }
                        }
                    ?>
                </TR>
                <TR>
                    <TD nowrap class='pt10'>¾ÞÍ¿¼êÅö</TD>
                    <?php
                        $r = 2;     // ³ºÅö¥ì¥³¡¼¥É
                        for ($c=1;$c<37;$c++) {
                            if ($c == 3 || $c == 6 || $c == 9 || $c == 12) {                // À½Â¤·ÐÈñÁý¸º
                                printf("<td nowrap class='pt10b' align='right' bgcolor='#ffffc6'>%s</td>\n",$view_data[$r][$c]);
                            } elseif ($c == 15 || $c == 18 || $c == 21 || $c == 24) {       // ÈÎ´ÉÈñÁý¸º
                                printf("<td nowrap class='pt10b' align='right' bgcolor='#ceffce'>%s</td>\n",$view_data[$r][$c]);
                            } elseif ($c == 27 || $c == 30 || $c == 33 || $c == 36) {       // ·ÐÈñ¹ç·×Áý¸º
                                printf("<td nowrap class='pt10b' align='right' bgcolor='#ccffff'>%s</td>\n",$view_data[$r][$c]);
                            } else {
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ffffff'>%s</td>\n",$view_data[$r][$c]);
                            }
                        }
                    ?>
                </TR>
                <TR>
                    <TD nowrap class='pt10'>¸ÜÌäÎÁ</TD>
                    <?php
                        $r = 3;     // ³ºÅö¥ì¥³¡¼¥É
                        for ($c=1;$c<37;$c++) {
                            if ($c == 3 || $c == 6 || $c == 9 || $c == 12) {                // À½Â¤·ÐÈñÁý¸º
                                printf("<td nowrap class='pt10b' align='right' bgcolor='#ffffc6'>%s</td>\n",$view_data[$r][$c]);
                            } elseif ($c == 15 || $c == 18 || $c == 21 || $c == 24) {       // ÈÎ´ÉÈñÁý¸º
                                printf("<td nowrap class='pt10b' align='right' bgcolor='#ceffce'>%s</td>\n",$view_data[$r][$c]);
                            } elseif ($c == 27 || $c == 30 || $c == 33 || $c == 36) {       // ·ÐÈñ¹ç·×Áý¸º
                                printf("<td nowrap class='pt10b' align='right' bgcolor='#ccffff'>%s</td>\n",$view_data[$r][$c]);
                            } else {
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ffffff'>%s</td>\n",$view_data[$r][$c]);
                            }
                        }
                    ?>
                </TR>
                <TR>
                    <TD nowrap class='pt10'>Ë¡ÄêÊ¡ÍøÈñ</TD>
                    <?php
                        $r = 4;     // ³ºÅö¥ì¥³¡¼¥É
                        for ($c=1;$c<37;$c++) {
                            if ($c == 3 || $c == 6 || $c == 9 || $c == 12) {                // À½Â¤·ÐÈñÁý¸º
                                printf("<td nowrap class='pt10b' align='right' bgcolor='#ffffc6'>%s</td>\n",$view_data[$r][$c]);
                            } elseif ($c == 15 || $c == 18 || $c == 21 || $c == 24) {       // ÈÎ´ÉÈñÁý¸º
                                printf("<td nowrap class='pt10b' align='right' bgcolor='#ceffce'>%s</td>\n",$view_data[$r][$c]);
                            } elseif ($c == 27 || $c == 30 || $c == 33 || $c == 36) {       // ·ÐÈñ¹ç·×Áý¸º
                                printf("<td nowrap class='pt10b' align='right' bgcolor='#ccffff'>%s</td>\n",$view_data[$r][$c]);
                            } else {
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ffffff'>%s</td>\n",$view_data[$r][$c]);
                            }
                        }
                    ?>
                </TR>
                <TR>
                    <TD nowrap class='pt10'>¸üÀ¸Ê¡ÍøÈñ</TD>
                    <?php
                        $r = 5;     // ³ºÅö¥ì¥³¡¼¥É
                        for ($c=1;$c<37;$c++) {
                            if ($c == 3 || $c == 6 || $c == 9 || $c == 12) {                // À½Â¤·ÐÈñÁý¸º
                                printf("<td nowrap class='pt10b' align='right' bgcolor='#ffffc6'>%s</td>\n",$view_data[$r][$c]);
                            } elseif ($c == 15 || $c == 18 || $c == 21 || $c == 24) {       // ÈÎ´ÉÈñÁý¸º
                                printf("<td nowrap class='pt10b' align='right' bgcolor='#ceffce'>%s</td>\n",$view_data[$r][$c]);
                            } elseif ($c == 27 || $c == 30 || $c == 33 || $c == 36) {       // ·ÐÈñ¹ç·×Áý¸º
                                printf("<td nowrap class='pt10b' align='right' bgcolor='#ccffff'>%s</td>\n",$view_data[$r][$c]);
                            } else {
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ffffff'>%s</td>\n",$view_data[$r][$c]);
                            }
                        }
                    ?>
                </TR>
                <TR>
                    <TD nowrap class='pt10'>¾ÞÍ¿°úÅö¶â·«Æþ</TD>
                    <?php
                        $r = 6;     // ³ºÅö¥ì¥³¡¼¥É
                        for ($c=1;$c<37;$c++) {
                            if ($c == 3 || $c == 6 || $c == 9 || $c == 12) {                // À½Â¤·ÐÈñÁý¸º
                                printf("<td nowrap class='pt10b' align='right' bgcolor='#ffffc6'>%s</td>\n",$view_data[$r][$c]);
                            } elseif ($c == 15 || $c == 18 || $c == 21 || $c == 24) {       // ÈÎ´ÉÈñÁý¸º
                                printf("<td nowrap class='pt10b' align='right' bgcolor='#ceffce'>%s</td>\n",$view_data[$r][$c]);
                            } elseif ($c == 27 || $c == 30 || $c == 33 || $c == 36) {       // ·ÐÈñ¹ç·×Áý¸º
                                printf("<td nowrap class='pt10b' align='right' bgcolor='#ccffff'>%s</td>\n",$view_data[$r][$c]);
                            } else {
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ffffff'>%s</td>\n",$view_data[$r][$c]);
                            }
                        }
                    ?>
                </TR>
                <TR>
                    <TD nowrap class='pt10'>Âà¿¦µëÉÕÈñÍÑ</TD>
                    <?php
                        $r = 7;     // ³ºÅö¥ì¥³¡¼¥É
                        for ($c=1;$c<37;$c++) {
                            if ($c == 3 || $c == 6 || $c == 9 || $c == 12) {                // À½Â¤·ÐÈñÁý¸º
                                printf("<td nowrap class='pt10b' align='right' bgcolor='#ffffc6'>%s</td>\n",$view_data[$r][$c]);
                            } elseif ($c == 15 || $c == 18 || $c == 21 || $c == 24) {       // ÈÎ´ÉÈñÁý¸º
                                printf("<td nowrap class='pt10b' align='right' bgcolor='#ceffce'>%s</td>\n",$view_data[$r][$c]);
                            } elseif ($c == 27 || $c == 30 || $c == 33 || $c == 36) {       // ·ÐÈñ¹ç·×Áý¸º
                                printf("<td nowrap class='pt10b' align='right' bgcolor='#ccffff'>%s</td>\n",$view_data[$r][$c]);
                            } else {
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ffffff'>%s</td>\n",$view_data[$r][$c]);
                            }
                        }
                    ?>
                </TR>
                <TR bgcolor='#ccffff'>
                    <TD nowrap class='pt10b' align='right'>¿Í·ïÈñ·×</TD>
                    <?php
                        $r = 8;     // ³ºÅö¥ì¥³¡¼¥É
                        for ($c=1;$c<37;$c++) {
                            printf("<td nowrap class='pt10b' align='right' bgcolor='#ccffff'>%s</td>\n",$view_data[$r][$c]);
                        }
                    ?>
                </TR>
                <tr>
                    <td width='10' rowspan='30' align='center' class='pt10b' bgcolor='#ccffff'>·ÐÈñ</td>
                    <TD nowrap class='pt10'>Î¹Èñ¸òÄÌÈñ</TD>
                    <?php
                        $r = 9;     // ³ºÅö¥ì¥³¡¼¥É
                        for ($c=1;$c<37;$c++) {
                            if ($c == 3 || $c == 6 || $c == 9 || $c == 12) {                // À½Â¤·ÐÈñÁý¸º
                                printf("<td nowrap class='pt10b' align='right' bgcolor='#ffffc6'>%s</td>\n",$view_data[$r][$c]);
                            } elseif ($c == 15 || $c == 18 || $c == 21 || $c == 24) {       // ÈÎ´ÉÈñÁý¸º
                                printf("<td nowrap class='pt10b' align='right' bgcolor='#ceffce'>%s</td>\n",$view_data[$r][$c]);
                            } elseif ($c == 27 || $c == 30 || $c == 33 || $c == 36) {       // ·ÐÈñ¹ç·×Áý¸º
                                printf("<td nowrap class='pt10b' align='right' bgcolor='#ccffff'>%s</td>\n",$view_data[$r][$c]);
                            } else {
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ffffff'>%s</td>\n",$view_data[$r][$c]);
                            }
                        }
                    ?>
                </tr>
                <tr>
                    <TD nowrap class='pt10'>³¤³°½ÐÄ¥</TD>
                    <?php
                        $r = 10;     // ³ºÅö¥ì¥³¡¼¥É
                        for ($c=1;$c<37;$c++) {
                            if ($c == 3 || $c == 6 || $c == 9 || $c == 12) {                // À½Â¤·ÐÈñÁý¸º
                                printf("<td nowrap class='pt10b' align='right' bgcolor='#ffffc6'>%s</td>\n",$view_data[$r][$c]);
                            } elseif ($c == 15 || $c == 18 || $c == 21 || $c == 24) {       // ÈÎ´ÉÈñÁý¸º
                                printf("<td nowrap class='pt10b' align='right' bgcolor='#ceffce'>%s</td>\n",$view_data[$r][$c]);
                            } elseif ($c == 27 || $c == 30 || $c == 33 || $c == 36) {       // ·ÐÈñ¹ç·×Áý¸º
                                printf("<td nowrap class='pt10b' align='right' bgcolor='#ccffff'>%s</td>\n",$view_data[$r][$c]);
                            } else {
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ffffff'>%s</td>\n",$view_data[$r][$c]);
                            }
                        }
                    ?>
                </tr>
                <tr>
                    <TD nowrap class='pt10'>ÄÌ¡¡¿®¡¡Èñ</TD>
                    <?php
                        $r = 11;     // ³ºÅö¥ì¥³¡¼¥É
                        for ($c=1;$c<37;$c++) {
                            if ($c == 3 || $c == 6 || $c == 9 || $c == 12) {                // À½Â¤·ÐÈñÁý¸º
                                printf("<td nowrap class='pt10b' align='right' bgcolor='#ffffc6'>%s</td>\n",$view_data[$r][$c]);
                            } elseif ($c == 15 || $c == 18 || $c == 21 || $c == 24) {       // ÈÎ´ÉÈñÁý¸º
                                printf("<td nowrap class='pt10b' align='right' bgcolor='#ceffce'>%s</td>\n",$view_data[$r][$c]);
                            } elseif ($c == 27 || $c == 30 || $c == 33 || $c == 36) {       // ·ÐÈñ¹ç·×Áý¸º
                                printf("<td nowrap class='pt10b' align='right' bgcolor='#ccffff'>%s</td>\n",$view_data[$r][$c]);
                            } else {
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ffffff'>%s</td>\n",$view_data[$r][$c]);
                            }
                        }
                    ?>
                </tr>
                <tr>
                    <TD nowrap class='pt10'>²ñ¡¡µÄ¡¡Èñ</TD>
                    <?php
                        $r = 12;     // ³ºÅö¥ì¥³¡¼¥É
                        for ($c=1;$c<37;$c++) {
                            if ($c == 3 || $c == 6 || $c == 9 || $c == 12) {                // À½Â¤·ÐÈñÁý¸º
                                printf("<td nowrap class='pt10b' align='right' bgcolor='#ffffc6'>%s</td>\n",$view_data[$r][$c]);
                            } elseif ($c == 15 || $c == 18 || $c == 21 || $c == 24) {       // ÈÎ´ÉÈñÁý¸º
                                printf("<td nowrap class='pt10b' align='right' bgcolor='#ceffce'>%s</td>\n",$view_data[$r][$c]);
                            } elseif ($c == 27 || $c == 30 || $c == 33 || $c == 36) {       // ·ÐÈñ¹ç·×Áý¸º
                                printf("<td nowrap class='pt10b' align='right' bgcolor='#ccffff'>%s</td>\n",$view_data[$r][$c]);
                            } else {
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ffffff'>%s</td>\n",$view_data[$r][$c]);
                            }
                        }
                    ?>
                </tr>
                <tr>
                    <TD nowrap class='pt10'>¸òºÝÀÜÂÔÈñ</TD>
                    <?php
                        $r = 13;     // ³ºÅö¥ì¥³¡¼¥É
                        for ($c=1;$c<37;$c++) {
                            if ($c == 3 || $c == 6 || $c == 9 || $c == 12) {                // À½Â¤·ÐÈñÁý¸º
                                printf("<td nowrap class='pt10b' align='right' bgcolor='#ffffc6'>%s</td>\n",$view_data[$r][$c]);
                            } elseif ($c == 15 || $c == 18 || $c == 21 || $c == 24) {       // ÈÎ´ÉÈñÁý¸º
                                printf("<td nowrap class='pt10b' align='right' bgcolor='#ceffce'>%s</td>\n",$view_data[$r][$c]);
                            } elseif ($c == 27 || $c == 30 || $c == 33 || $c == 36) {       // ·ÐÈñ¹ç·×Áý¸º
                                printf("<td nowrap class='pt10b' align='right' bgcolor='#ccffff'>%s</td>\n",$view_data[$r][$c]);
                            } else {
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ffffff'>%s</td>\n",$view_data[$r][$c]);
                            }
                        }
                    ?>
                </tr>
                <tr>
                    <TD nowrap class='pt10'>¹­¹ðÀëÅÁÈñ</TD>
                    <?php
                        $r = 14;     // ³ºÅö¥ì¥³¡¼¥É
                        for ($c=1;$c<37;$c++) {
                            if ($c == 3 || $c == 6 || $c == 9 || $c == 12) {                // À½Â¤·ÐÈñÁý¸º
                                printf("<td nowrap class='pt10b' align='right' bgcolor='#ffffc6'>%s</td>\n",$view_data[$r][$c]);
                            } elseif ($c == 15 || $c == 18 || $c == 21 || $c == 24) {       // ÈÎ´ÉÈñÁý¸º
                                printf("<td nowrap class='pt10b' align='right' bgcolor='#ceffce'>%s</td>\n",$view_data[$r][$c]);
                            } elseif ($c == 27 || $c == 30 || $c == 33 || $c == 36) {       // ·ÐÈñ¹ç·×Áý¸º
                                printf("<td nowrap class='pt10b' align='right' bgcolor='#ccffff'>%s</td>\n",$view_data[$r][$c]);
                            } else {
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ffffff'>%s</td>\n",$view_data[$r][$c]);
                            }
                        }
                    ?>
                </tr>
                <tr>
                    <TD nowrap class='pt10'>µá¡¡¿Í¡¡Èñ</TD>
                    <?php
                        $r = 15;     // ³ºÅö¥ì¥³¡¼¥É
                        for ($c=1;$c<37;$c++) {
                            if ($c == 3 || $c == 6 || $c == 9 || $c == 12) {                // À½Â¤·ÐÈñÁý¸º
                                printf("<td nowrap class='pt10b' align='right' bgcolor='#ffffc6'>%s</td>\n",$view_data[$r][$c]);
                            } elseif ($c == 15 || $c == 18 || $c == 21 || $c == 24) {       // ÈÎ´ÉÈñÁý¸º
                                printf("<td nowrap class='pt10b' align='right' bgcolor='#ceffce'>%s</td>\n",$view_data[$r][$c]);
                            } elseif ($c == 27 || $c == 30 || $c == 33 || $c == 36) {       // ·ÐÈñ¹ç·×Áý¸º
                                printf("<td nowrap class='pt10b' align='right' bgcolor='#ccffff'>%s</td>\n",$view_data[$r][$c]);
                            } else {
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ffffff'>%s</td>\n",$view_data[$r][$c]);
                            }
                        }
                    ?>
                </tr>
                <tr>
                    <TD nowrap class='pt10'>±¿ÄÂ²ÙÂ¤Èñ</TD>
                    <?php
                        $r = 16;     // ³ºÅö¥ì¥³¡¼¥É
                        for ($c=1;$c<37;$c++) {
                            if ($c == 3 || $c == 6 || $c == 9 || $c == 12) {                // À½Â¤·ÐÈñÁý¸º
                                printf("<td nowrap class='pt10b' align='right' bgcolor='#ffffc6'>%s</td>\n",$view_data[$r][$c]);
                            } elseif ($c == 15 || $c == 18 || $c == 21 || $c == 24) {       // ÈÎ´ÉÈñÁý¸º
                                printf("<td nowrap class='pt10b' align='right' bgcolor='#ceffce'>%s</td>\n",$view_data[$r][$c]);
                            } elseif ($c == 27 || $c == 30 || $c == 33 || $c == 36) {       // ·ÐÈñ¹ç·×Áý¸º
                                printf("<td nowrap class='pt10b' align='right' bgcolor='#ccffff'>%s</td>\n",$view_data[$r][$c]);
                            } else {
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ffffff'>%s</td>\n",$view_data[$r][$c]);
                            }
                        }
                    ?>
                </tr>
                <tr>
                    <TD nowrap class='pt10'>¿Þ½ñ¶µ°éÈñ</TD>
                    <?php
                        $r = 17;     // ³ºÅö¥ì¥³¡¼¥É
                        for ($c=1;$c<37;$c++) {
                            if ($c == 3 || $c == 6 || $c == 9 || $c == 12) {                // À½Â¤·ÐÈñÁý¸º
                                printf("<td nowrap class='pt10b' align='right' bgcolor='#ffffc6'>%s</td>\n",$view_data[$r][$c]);
                            } elseif ($c == 15 || $c == 18 || $c == 21 || $c == 24) {       // ÈÎ´ÉÈñÁý¸º
                                printf("<td nowrap class='pt10b' align='right' bgcolor='#ceffce'>%s</td>\n",$view_data[$r][$c]);
                            } elseif ($c == 27 || $c == 30 || $c == 33 || $c == 36) {       // ·ÐÈñ¹ç·×Áý¸º
                                printf("<td nowrap class='pt10b' align='right' bgcolor='#ccffff'>%s</td>\n",$view_data[$r][$c]);
                            } else {
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ffffff'>%s</td>\n",$view_data[$r][$c]);
                            }
                        }
                    ?>
                </tr>
                <tr>
                    <TD nowrap class='pt10'>¶ÈÌ³°ÑÂ÷Èñ</TD>
                    <?php
                        $r = 18;     // ³ºÅö¥ì¥³¡¼¥É
                        for ($c=1;$c<37;$c++) {
                            if ($c == 3 || $c == 6 || $c == 9 || $c == 12) {                // À½Â¤·ÐÈñÁý¸º
                                printf("<td nowrap class='pt10b' align='right' bgcolor='#ffffc6'>%s</td>\n",$view_data[$r][$c]);
                            } elseif ($c == 15 || $c == 18 || $c == 21 || $c == 24) {       // ÈÎ´ÉÈñÁý¸º
                                printf("<td nowrap class='pt10b' align='right' bgcolor='#ceffce'>%s</td>\n",$view_data[$r][$c]);
                            } elseif ($c == 27 || $c == 30 || $c == 33 || $c == 36) {       // ·ÐÈñ¹ç·×Áý¸º
                                printf("<td nowrap class='pt10b' align='right' bgcolor='#ccffff'>%s</td>\n",$view_data[$r][$c]);
                            } else {
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ffffff'>%s</td>\n",$view_data[$r][$c]);
                            }
                        }
                    ?>
                </tr>
                <tr>
                    <td nowrap class='pt10'>»ö¡¡¶È¡¡Åù</td>
                    <?php
                        $r = 19;     // ³ºÅö¥ì¥³¡¼¥É
                        for ($c=1;$c<37;$c++) {
                            if ($c == 3 || $c == 6 || $c == 9 || $c == 12) {                // À½Â¤·ÐÈñÁý¸º
                                printf("<td nowrap class='pt10b' align='right' bgcolor='#ffffc6'>%s</td>\n",$view_data[$r][$c]);
                            } elseif ($c == 15 || $c == 18 || $c == 21 || $c == 24) {       // ÈÎ´ÉÈñÁý¸º
                                printf("<td nowrap class='pt10b' align='right' bgcolor='#ceffce'>%s</td>\n",$view_data[$r][$c]);
                            } elseif ($c == 27 || $c == 30 || $c == 33 || $c == 36) {       // ·ÐÈñ¹ç·×Áý¸º
                                printf("<td nowrap class='pt10b' align='right' bgcolor='#ccffff'>%s</td>\n",$view_data[$r][$c]);
                            } else {
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ffffff'>%s</td>\n",$view_data[$r][$c]);
                            }
                        }
                    ?>
                </tr>
                <tr>
                    <TD nowrap class='pt10'>½ôÀÇ¸ø²Ý</TD>
                    <?php
                        $r = 20;     // ³ºÅö¥ì¥³¡¼¥É
                        for ($c=1;$c<37;$c++) {
                            if ($c == 3 || $c == 6 || $c == 9 || $c == 12) {                // À½Â¤·ÐÈñÁý¸º
                                printf("<td nowrap class='pt10b' align='right' bgcolor='#ffffc6'>%s</td>\n",$view_data[$r][$c]);
                            } elseif ($c == 15 || $c == 18 || $c == 21 || $c == 24) {       // ÈÎ´ÉÈñÁý¸º
                                printf("<td nowrap class='pt10b' align='right' bgcolor='#ceffce'>%s</td>\n",$view_data[$r][$c]);
                            } elseif ($c == 27 || $c == 30 || $c == 33 || $c == 36) {       // ·ÐÈñ¹ç·×Áý¸º
                                printf("<td nowrap class='pt10b' align='right' bgcolor='#ccffff'>%s</td>\n",$view_data[$r][$c]);
                            } else {
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ffffff'>%s</td>\n",$view_data[$r][$c]);
                            }
                        }
                    ?>
                </tr>
                <tr>
                    <TD nowrap class='pt10'>»î¸³¸¦µæÈñ</TD>
                    <?php
                        $r = 21;     // ³ºÅö¥ì¥³¡¼¥É
                        for ($c=1;$c<37;$c++) {
                            if ($c == 3 || $c == 6 || $c == 9 || $c == 12) {                // À½Â¤·ÐÈñÁý¸º
                                printf("<td nowrap class='pt10b' align='right' bgcolor='#ffffc6'>%s</td>\n",$view_data[$r][$c]);
                            } elseif ($c == 15 || $c == 18 || $c == 21 || $c == 24) {       // ÈÎ´ÉÈñÁý¸º
                                printf("<td nowrap class='pt10b' align='right' bgcolor='#ceffce'>%s</td>\n",$view_data[$r][$c]);
                            } elseif ($c == 27 || $c == 30 || $c == 33 || $c == 36) {       // ·ÐÈñ¹ç·×Áý¸º
                                printf("<td nowrap class='pt10b' align='right' bgcolor='#ccffff'>%s</td>\n",$view_data[$r][$c]);
                            } else {
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ffffff'>%s</td>\n",$view_data[$r][$c]);
                            }
                        }
                    ?>
                </tr>
                <tr>
                    <TD nowrap class='pt10'>»¨¡¡¡¡¡¡Èñ</TD>
                    <?php
                        $r = 22;     // ³ºÅö¥ì¥³¡¼¥É
                        for ($c=1;$c<37;$c++) {
                            if ($c == 3 || $c == 6 || $c == 9 || $c == 12) {                // À½Â¤·ÐÈñÁý¸º
                                printf("<td nowrap class='pt10b' align='right' bgcolor='#ffffc6'>%s</td>\n",$view_data[$r][$c]);
                            } elseif ($c == 15 || $c == 18 || $c == 21 || $c == 24) {       // ÈÎ´ÉÈñÁý¸º
                                printf("<td nowrap class='pt10b' align='right' bgcolor='#ceffce'>%s</td>\n",$view_data[$r][$c]);
                            } elseif ($c == 27 || $c == 30 || $c == 33 || $c == 36) {       // ·ÐÈñ¹ç·×Áý¸º
                                printf("<td nowrap class='pt10b' align='right' bgcolor='#ccffff'>%s</td>\n",$view_data[$r][$c]);
                            } else {
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ffffff'>%s</td>\n",$view_data[$r][$c]);
                            }
                        }
                    ?>
                </tr>
                <tr>
                    <TD nowrap class='pt10'>½¤¡¡Á¶¡¡Èñ</TD>
                    <?php
                        $r = 23;     // ³ºÅö¥ì¥³¡¼¥É
                        for ($c=1;$c<37;$c++) {
                            if ($c == 3 || $c == 6 || $c == 9 || $c == 12) {                // À½Â¤·ÐÈñÁý¸º
                                printf("<td nowrap class='pt10b' align='right' bgcolor='#ffffc6'>%s</td>\n",$view_data[$r][$c]);
                            } elseif ($c == 15 || $c == 18 || $c == 21 || $c == 24) {       // ÈÎ´ÉÈñÁý¸º
                                printf("<td nowrap class='pt10b' align='right' bgcolor='#ceffce'>%s</td>\n",$view_data[$r][$c]);
                            } elseif ($c == 27 || $c == 30 || $c == 33 || $c == 36) {       // ·ÐÈñ¹ç·×Áý¸º
                                printf("<td nowrap class='pt10b' align='right' bgcolor='#ccffff'>%s</td>\n",$view_data[$r][$c]);
                            } else {
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ffffff'>%s</td>\n",$view_data[$r][$c]);
                            }
                        }
                    ?>
                </tr>
                <tr>
                    <TD nowrap class='pt10'>ÊÝ¾Ú½¤ÍýÈñ</TD>
                    <?php
                        $r = 24;     // ³ºÅö¥ì¥³¡¼¥É
                        for ($c=1;$c<37;$c++) {
                            if ($c == 3 || $c == 6 || $c == 9 || $c == 12) {                // À½Â¤·ÐÈñÁý¸º
                                printf("<td nowrap class='pt10b' align='right' bgcolor='#ffffc6'>%s</td>\n",$view_data[$r][$c]);
                            } elseif ($c == 15 || $c == 18 || $c == 21 || $c == 24) {       // ÈÎ´ÉÈñÁý¸º
                                printf("<td nowrap class='pt10b' align='right' bgcolor='#ceffce'>%s</td>\n",$view_data[$r][$c]);
                            } elseif ($c == 27 || $c == 30 || $c == 33 || $c == 36) {       // ·ÐÈñ¹ç·×Áý¸º
                                printf("<td nowrap class='pt10b' align='right' bgcolor='#ccffff'>%s</td>\n",$view_data[$r][$c]);
                            } else {
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ffffff'>%s</td>\n",$view_data[$r][$c]);
                            }
                        }
                    ?>
                </tr>
                <tr>
                    <TD nowrap class='pt10'>»öÌ³ÍÑ¾ÃÌ×ÉÊÈñ</TD>
                    <?php
                        $r = 25;     // ³ºÅö¥ì¥³¡¼¥É
                        for ($c=1;$c<37;$c++) {
                            if ($c == 3 || $c == 6 || $c == 9 || $c == 12) {                // À½Â¤·ÐÈñÁý¸º
                                printf("<td nowrap class='pt10b' align='right' bgcolor='#ffffc6'>%s</td>\n",$view_data[$r][$c]);
                            } elseif ($c == 15 || $c == 18 || $c == 21 || $c == 24) {       // ÈÎ´ÉÈñÁý¸º
                                printf("<td nowrap class='pt10b' align='right' bgcolor='#ceffce'>%s</td>\n",$view_data[$r][$c]);
                            } elseif ($c == 27 || $c == 30 || $c == 33 || $c == 36) {       // ·ÐÈñ¹ç·×Áý¸º
                                printf("<td nowrap class='pt10b' align='right' bgcolor='#ccffff'>%s</td>\n",$view_data[$r][$c]);
                            } else {
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ffffff'>%s</td>\n",$view_data[$r][$c]);
                            }
                        }
                    ?>
                </tr>
                <tr>
                    <TD nowrap class='pt10'>¹©¾ì¾ÃÌ×ÉÊÈñ</TD>
                    <?php
                        $r = 26;     // ³ºÅö¥ì¥³¡¼¥É
                        for ($c=1;$c<37;$c++) {
                            if ($c == 3 || $c == 6 || $c == 9 || $c == 12) {                // À½Â¤·ÐÈñÁý¸º
                                printf("<td nowrap class='pt10b' align='right' bgcolor='#ffffc6'>%s</td>\n",$view_data[$r][$c]);
                            } elseif ($c == 15 || $c == 18 || $c == 21 || $c == 24) {       // ÈÎ´ÉÈñÁý¸º
                                printf("<td nowrap class='pt10b' align='right' bgcolor='#ceffce'>%s</td>\n",$view_data[$r][$c]);
                            } elseif ($c == 27 || $c == 30 || $c == 33 || $c == 36) {       // ·ÐÈñ¹ç·×Áý¸º
                                printf("<td nowrap class='pt10b' align='right' bgcolor='#ccffff'>%s</td>\n",$view_data[$r][$c]);
                            } else {
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ffffff'>%s</td>\n",$view_data[$r][$c]);
                            }
                        }
                    ?>
                </tr>
                <tr>
                    <TD nowrap class='pt10'>¼Ö¡¡Î¾¡¡Èñ</TD>
                    <?php
                        $r = 27;     // ³ºÅö¥ì¥³¡¼¥É
                        for ($c=1;$c<37;$c++) {
                            if ($c == 3 || $c == 6 || $c == 9 || $c == 12) {                // À½Â¤·ÐÈñÁý¸º
                                printf("<td nowrap class='pt10b' align='right' bgcolor='#ffffc6'>%s</td>\n",$view_data[$r][$c]);
                            } elseif ($c == 15 || $c == 18 || $c == 21 || $c == 24) {       // ÈÎ´ÉÈñÁý¸º
                                printf("<td nowrap class='pt10b' align='right' bgcolor='#ceffce'>%s</td>\n",$view_data[$r][$c]);
                            } elseif ($c == 27 || $c == 30 || $c == 33 || $c == 36) {       // ·ÐÈñ¹ç·×Áý¸º
                                printf("<td nowrap class='pt10b' align='right' bgcolor='#ccffff'>%s</td>\n",$view_data[$r][$c]);
                            } else {
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ffffff'>%s</td>\n",$view_data[$r][$c]);
                            }
                        }
                    ?>
                </tr>
                <tr>
                    <TD nowrap class='pt10'>ÊÝ¡¡¸±¡¡ÎÁ</TD>
                    <?php
                        $r = 28;     // ³ºÅö¥ì¥³¡¼¥É
                        for ($c=1;$c<37;$c++) {
                            if ($c == 3 || $c == 6 || $c == 9 || $c == 12) {                // À½Â¤·ÐÈñÁý¸º
                                printf("<td nowrap class='pt10b' align='right' bgcolor='#ffffc6'>%s</td>\n",$view_data[$r][$c]);
                            } elseif ($c == 15 || $c == 18 || $c == 21 || $c == 24) {       // ÈÎ´ÉÈñÁý¸º
                                printf("<td nowrap class='pt10b' align='right' bgcolor='#ceffce'>%s</td>\n",$view_data[$r][$c]);
                            } elseif ($c == 27 || $c == 30 || $c == 33 || $c == 36) {       // ·ÐÈñ¹ç·×Áý¸º
                                printf("<td nowrap class='pt10b' align='right' bgcolor='#ccffff'>%s</td>\n",$view_data[$r][$c]);
                            } else {
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ffffff'>%s</td>\n",$view_data[$r][$c]);
                            }
                        }
                    ?>
                </tr>
                <tr>
                    <TD nowrap class='pt10'>¿åÆ»¸÷Ç®Èñ</TD>
                    <?php
                        $r = 29;     // ³ºÅö¥ì¥³¡¼¥É
                        for ($c=1;$c<37;$c++) {
                            if ($c == 3 || $c == 6 || $c == 9 || $c == 12) {                // À½Â¤·ÐÈñÁý¸º
                                printf("<td nowrap class='pt10b' align='right' bgcolor='#ffffc6'>%s</td>\n",$view_data[$r][$c]);
                            } elseif ($c == 15 || $c == 18 || $c == 21 || $c == 24) {       // ÈÎ´ÉÈñÁý¸º
                                printf("<td nowrap class='pt10b' align='right' bgcolor='#ceffce'>%s</td>\n",$view_data[$r][$c]);
                            } elseif ($c == 27 || $c == 30 || $c == 33 || $c == 36) {       // ·ÐÈñ¹ç·×Áý¸º
                                printf("<td nowrap class='pt10b' align='right' bgcolor='#ccffff'>%s</td>\n",$view_data[$r][$c]);
                            } else {
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ffffff'>%s</td>\n",$view_data[$r][$c]);
                            }
                        }
                    ?>
                </tr>
                <tr>
                    <TD nowrap class='pt10'>½ô¡¡²ñ¡¡Èñ</TD>
                    <?php
                        $r = 30;     // ³ºÅö¥ì¥³¡¼¥É
                        for ($c=1;$c<37;$c++) {
                            if ($c == 3 || $c == 6 || $c == 9 || $c == 12) {                // À½Â¤·ÐÈñÁý¸º
                                printf("<td nowrap class='pt10b' align='right' bgcolor='#ffffc6'>%s</td>\n",$view_data[$r][$c]);
                            } elseif ($c == 15 || $c == 18 || $c == 21 || $c == 24) {       // ÈÎ´ÉÈñÁý¸º
                                printf("<td nowrap class='pt10b' align='right' bgcolor='#ceffce'>%s</td>\n",$view_data[$r][$c]);
                            } elseif ($c == 27 || $c == 30 || $c == 33 || $c == 36) {       // ·ÐÈñ¹ç·×Áý¸º
                                printf("<td nowrap class='pt10b' align='right' bgcolor='#ccffff'>%s</td>\n",$view_data[$r][$c]);
                            } else {
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ffffff'>%s</td>\n",$view_data[$r][$c]);
                            }
                        }
                    ?>
                </tr>
                <tr>
                    <TD nowrap class='pt10'>»ÙÊ§¼ê¿ôÎÁ</TD>
                    <?php
                        $r = 31;     // ³ºÅö¥ì¥³¡¼¥É
                        for ($c=1;$c<37;$c++) {
                            if ($c == 3 || $c == 6 || $c == 9 || $c == 12) {                // À½Â¤·ÐÈñÁý¸º
                                printf("<td nowrap class='pt10b' align='right' bgcolor='#ffffc6'>%s</td>\n",$view_data[$r][$c]);
                            } elseif ($c == 15 || $c == 18 || $c == 21 || $c == 24) {       // ÈÎ´ÉÈñÁý¸º
                                printf("<td nowrap class='pt10b' align='right' bgcolor='#ceffce'>%s</td>\n",$view_data[$r][$c]);
                            } elseif ($c == 27 || $c == 30 || $c == 33 || $c == 36) {       // ·ÐÈñ¹ç·×Áý¸º
                                printf("<td nowrap class='pt10b' align='right' bgcolor='#ccffff'>%s</td>\n",$view_data[$r][$c]);
                            } else {
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ffffff'>%s</td>\n",$view_data[$r][$c]);
                            }
                        }
                    ?>
                </tr>
                <tr>
                    <TD nowrap class='pt10'>ÃÏÂå²ÈÄÂ</TD>
                    <?php
                        $r = 32;     // ³ºÅö¥ì¥³¡¼¥É
                        for ($c=1;$c<37;$c++) {
                            if ($c == 3 || $c == 6 || $c == 9 || $c == 12) {                // À½Â¤·ÐÈñÁý¸º
                                printf("<td nowrap class='pt10b' align='right' bgcolor='#ffffc6'>%s</td>\n",$view_data[$r][$c]);
                            } elseif ($c == 15 || $c == 18 || $c == 21 || $c == 24) {       // ÈÎ´ÉÈñÁý¸º
                                printf("<td nowrap class='pt10b' align='right' bgcolor='#ceffce'>%s</td>\n",$view_data[$r][$c]);
                            } elseif ($c == 27 || $c == 30 || $c == 33 || $c == 36) {       // ·ÐÈñ¹ç·×Áý¸º
                                printf("<td nowrap class='pt10b' align='right' bgcolor='#ccffff'>%s</td>\n",$view_data[$r][$c]);
                            } else {
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ffffff'>%s</td>\n",$view_data[$r][$c]);
                            }
                        }
                    ?>
                </tr>
                <tr>
                    <TD nowrap class='pt10'>´ó¡¡ÉÕ¡¡¶â</TD>
                    <?php
                        $r = 33;     // ³ºÅö¥ì¥³¡¼¥É
                        for ($c=1;$c<37;$c++) {
                            if ($c == 3 || $c == 6 || $c == 9 || $c == 12) {                // À½Â¤·ÐÈñÁý¸º
                                printf("<td nowrap class='pt10b' align='right' bgcolor='#ffffc6'>%s</td>\n",$view_data[$r][$c]);
                            } elseif ($c == 15 || $c == 18 || $c == 21 || $c == 24) {       // ÈÎ´ÉÈñÁý¸º
                                printf("<td nowrap class='pt10b' align='right' bgcolor='#ceffce'>%s</td>\n",$view_data[$r][$c]);
                            } elseif ($c == 27 || $c == 30 || $c == 33 || $c == 36) {       // ·ÐÈñ¹ç·×Áý¸º
                                printf("<td nowrap class='pt10b' align='right' bgcolor='#ccffff'>%s</td>\n",$view_data[$r][$c]);
                            } else {
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ffffff'>%s</td>\n",$view_data[$r][$c]);
                            }
                        }
                    ?>
                </tr>
                <tr>
                    <TD nowrap class='pt10'>ÁÒ¡¡Éß¡¡ÎÁ</TD>
                    <?php
                        $r = 34;     // ³ºÅö¥ì¥³¡¼¥É
                        for ($c=1;$c<37;$c++) {
                            if ($c == 3 || $c == 6 || $c == 9 || $c == 12) {                // À½Â¤·ÐÈñÁý¸º
                                printf("<td nowrap class='pt10b' align='right' bgcolor='#ffffc6'>%s</td>\n",$view_data[$r][$c]);
                            } elseif ($c == 15 || $c == 18 || $c == 21 || $c == 24) {       // ÈÎ´ÉÈñÁý¸º
                                printf("<td nowrap class='pt10b' align='right' bgcolor='#ceffce'>%s</td>\n",$view_data[$r][$c]);
                            } elseif ($c == 27 || $c == 30 || $c == 33 || $c == 36) {       // ·ÐÈñ¹ç·×Áý¸º
                                printf("<td nowrap class='pt10b' align='right' bgcolor='#ccffff'>%s</td>\n",$view_data[$r][$c]);
                            } else {
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ffffff'>%s</td>\n",$view_data[$r][$c]);
                            }
                        }
                    ?>
                </tr>
                <tr>
                    <TD nowrap class='pt10'>ÄÂ¡¡¼Ú¡¡ÎÁ</TD>
                    <?php
                        $r = 35;     // ³ºÅö¥ì¥³¡¼¥É
                        for ($c=1;$c<37;$c++) {
                            if ($c == 3 || $c == 6 || $c == 9 || $c == 12) {                // À½Â¤·ÐÈñÁý¸º
                                printf("<td nowrap class='pt10b' align='right' bgcolor='#ffffc6'>%s</td>\n",$view_data[$r][$c]);
                            } elseif ($c == 15 || $c == 18 || $c == 21 || $c == 24) {       // ÈÎ´ÉÈñÁý¸º
                                printf("<td nowrap class='pt10b' align='right' bgcolor='#ceffce'>%s</td>\n",$view_data[$r][$c]);
                            } elseif ($c == 27 || $c == 30 || $c == 33 || $c == 36) {       // ·ÐÈñ¹ç·×Áý¸º
                                printf("<td nowrap class='pt10b' align='right' bgcolor='#ccffff'>%s</td>\n",$view_data[$r][$c]);
                            } else {
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ffffff'>%s</td>\n",$view_data[$r][$c]);
                            }
                        }
                    ?>
                </tr>
                <tr>
                    <TD nowrap class='pt10'>¸º²Á½þµÑÈñ</TD>
                    <?php
                        $r = 36;     // ³ºÅö¥ì¥³¡¼¥É
                        for ($c=1;$c<37;$c++) {
                            if ($c == 3 || $c == 6 || $c == 9 || $c == 12) {                // À½Â¤·ÐÈñÁý¸º
                                printf("<td nowrap class='pt10b' align='right' bgcolor='#ffffc6'>%s</td>\n",$view_data[$r][$c]);
                            } elseif ($c == 15 || $c == 18 || $c == 21 || $c == 24) {       // ÈÎ´ÉÈñÁý¸º
                                printf("<td nowrap class='pt10b' align='right' bgcolor='#ceffce'>%s</td>\n",$view_data[$r][$c]);
                            } elseif ($c == 27 || $c == 30 || $c == 33 || $c == 36) {       // ·ÐÈñ¹ç·×Áý¸º
                                printf("<td nowrap class='pt10b' align='right' bgcolor='#ccffff'>%s</td>\n",$view_data[$r][$c]);
                            } else {
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ffffff'>%s</td>\n",$view_data[$r][$c]);
                            }
                        }
                    ?>
                </tr>
                <tr>
                    <TD nowrap class='pt10'>¥¯¥ì¡¼¥àÂÐ±þÈñ</TD>
                    <?php
                        $r = 37;     // ³ºÅö¥ì¥³¡¼¥É
                        for ($c=1;$c<37;$c++) {
                            if ($c == 3 || $c == 6 || $c == 9 || $c == 12) {                // À½Â¤·ÐÈñÁý¸º
                                printf("<td nowrap class='pt10b' align='right' bgcolor='#ffffc6'>%s</td>\n",$view_data[$r][$c]);
                            } elseif ($c == 15 || $c == 18 || $c == 21 || $c == 24) {       // ÈÎ´ÉÈñÁý¸º
                                printf("<td nowrap class='pt10b' align='right' bgcolor='#ceffce'>%s</td>\n",$view_data[$r][$c]);
                            } elseif ($c == 27 || $c == 30 || $c == 33 || $c == 36) {       // ·ÐÈñ¹ç·×Áý¸º
                                printf("<td nowrap class='pt10b' align='right' bgcolor='#ccffff'>%s</td>\n",$view_data[$r][$c]);
                            } else {
                                printf("<td nowrap class='pt10' align='right' bgcolor='#ffffff'>%s</td>\n",$view_data[$r][$c]);
                            }
                        }
                    ?>
                </tr>
                <tr bgcolor='#ccffff'>
                    <TD nowrap class='pt10b' align='right'>·ÐÈñ·×</TD>
                    <?php
                        $r = 38;     // ³ºÅö¥ì¥³¡¼¥É
                        for ($c=1;$c<37;$c++) {
                            printf("<td nowrap class='pt10b' align='right' bgcolor='#ccffff'>%s</td>\n",$view_data[$r][$c]);
                        }
                    ?>
                </tr>
                <tr bgcolor='#ccffff'>
                    <TD colspan='2' nowrap class='pt10b' align='right'>¹ç¡¡·×</TD>
                    <?php
                        $r = 39;     // ³ºÅö¥ì¥³¡¼¥É
                        for ($c=1;$c<37;$c++) {
                            printf("<td nowrap class='pt10b' align='right' bgcolor='#ccffff'>%s</td>\n",$view_data[$r][$c]);
                        }
                    ?>
                </tr>
            </TBODY>
        </table>
    </center>
</body>
</html>
