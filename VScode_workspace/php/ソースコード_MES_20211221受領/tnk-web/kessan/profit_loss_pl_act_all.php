<?php
//////////////////////////////////////////////////////////////////////////////
// ·î¼¡Â»±×´Ø·¸ ·î¼¡ £Ã£Ì¡¦¾¦ÉÊ´ÉÍý¡¦»î¸³½¤Íý Â»±×·×»»½ñ                    //
// Copyright (C) 2003-2016 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp      //
// Changed history                                                          //
// 2003/02/12 Created   profit_loss_pl_act.php                              //
// 2003/02/23 date("Y/m/d H:m:s") ¢ª H:i:s ¤Î¥ß¥¹½¤Àµ                       //
// 2003/03/04 Ê¸»ú¥µ¥¤¥º¤ò¥Ö¥é¥¦¥¶¡¼¤ÇÊÑ¹¹¤Ç¤­¤Ê¤¯¤·¤¿ title_font Åù        //
//            ÆÃµ­»ö¹à¤ò¥«¥×¥é¡¦¥ê¥Ë¥¢°Ê³°¤ËÁ´ÂÎ¤È¤½¤ÎÂ¾¤òÄÉ²Ã              //
// 2003/03/06 title_font today_font ¤òÀßÄê ¾¯¿ô°Ê²¼¤Î·å¿ô£¶·å¤òÄÉ²Ã         //
// 2003/03/11 Location: http ¢ª Location $url_referer ¤ËÊÑ¹¹                //
//            ¥á¥Ã¥»¡¼¥¸¤ò½ÐÎÏ¤¹¤ë¤¿¤á site_index site_id ¤ò¥³¥á¥ó¥È¤Ë¤·    //
//            parent.menu_site.¤òÍ­¸ú¤ËÊÑ¹¹                                 //
// 2003/05/01 ¹©¾ìÄ¹¤«¤é¤Î»Ø¼¨¤ÇÇ§¾Ú¤òAccount_group¤«¤éÄÌ¾ï¤ØÊÑ¹¹           //
// 2003/08/05 $p1_c_srisoku ¢ª $p1_l_srisoku ¤Ë¤Ê¤Ã¤Æ¤¤¤¿¤Î¤ò½¤Àµ           //
// 2003/12/15 ÈÎ´ÉÈñµÚ¤Ó  °ìÈÌ´ÉÍýÈñ ·× ¢ª °ìÈÌ´ÉÍýÈñ·× (¥¹¥Ú¡¼¥¹¤òºï½ü)    //
// 2004/05/11 º¸Â¦¤Î¥µ¥¤¥È¥á¥Ë¥å¡¼¤Î¥ª¥ó¡¦¥ª¥Õ ¥Ü¥¿¥ó¤òÄÉ²Ã                 //
// 2005/10/26 MenuHeader class ¤ò»ÈÍÑ¤·¤Æ¶¦ÄÌ¥á¥Ë¥å¡¼²½µÚ¤ÓÇ§¾ÚÊý¼°¤ØÊÑ¹¹   //
// 2005/11/08 $menu->out_action('ÆÃµ­»ö¹àÆþÎÏ')¤ò<a href=¤ËÄÉ²Ã             //
// 2006/03/07 Á°²ó style='overflow-y:hidden;' ¤ò¤¦¤Ã¤«¤êÉÕ¤±¤¿¤¿¤á¥³¥á¥ó¥È  //
// 2007/11/08 Á°·î¤Î»ÅÆþ³ä°ú¤ÎÉ½¼¨¤¬$p1_l_swari ¢ª $p1_c_swari ¤ØÄûÀµ       //
// 2009/08/17 ÊªÎ®¤ÎÂ»±×É½¼¨¤òÄÉ²Ã¡Ê»ÃÄê¡Ë                             ÂçÃ« //
// 2009/08/18 »î¸³¡¦½¤ÍýÉôÌç¤ÎÂ»±×É½¼¨¤òÄÉ²Ã¡Ê»ÃÄê¡Ë                   ÂçÃ« //
// 2009/08/19 ÊªÎ®¤ò¾¦ÉÊ´ÉÍý¤ËÌ¾¾ÎÊÑ¹¹                                 ÂçÃ« //
// 2009/08/20 ¥³¥á¥ó¥È¤òÊÔ½¸                                           ÂçÃ« //
// 2009/08/21 Â»±×¤òExcel¤Ë¤¢¤ï¤»¤Æ200904¡Á200906¤ËÄ´À°¤òÆþ¤ì¤¿        ÂçÃ« //
// 2009/10/06 ¾¦´É¤ÎÇä¾å¹â¤¬AS¤ËÅÐÏ¿¤µ¤ì¤¿¤Î¤Ç¤½¤ÎÂÐ±þ200909¤è¤ê       ÂçÃ« //
//            ÆþÎÏ²èÌÌ¤ÇÄ´À°¶â³Û¤òÆþÎÏ¤·¤³¤Ã¤Á¤Ç¥Þ¥¤¥Ê¥¹¤¹¤ë           ÂçÃ« //
// 2009/10/15 Çä¾å¹â¡¦Çä¾åÁíÍø±×¡¦±Ä¶ÈÍø±×¡¦·Ð¾ïÍø±×¤òÂÀ»ú¤ËÊÑ¹¹       ÂçÃ« //
// 2009/10/29 ¾¦´É¤Ø¤Î¼Ò°÷µëÍ¿°ÄÊ¬²Ã»»¤ËÂÐ±þ$¡Á_allo_kin               ÂçÃ« //
// 2009/11/09 ¾¦´É¤ÎÇä¾åÄ´À°¤¬Á°·î¡¦Á°¡¹·î¤ËÆþ¤Ã¤Æ¤¤¤Ê¤«¤Ã¤¿¤Î¤Ç½¤Àµ   ÂçÃ« //
// 2009/11/12 ¥ê¥Ë¥¢¤ÎÄ´À°¶â³Û¤¬¤¦¤Þ¤¯¼è¹þ¤á¤Ê¤«¤Ã¤¿¤Î¤ò½¤Àµ           ÂçÃ« //
// 2009/12/07 ¥«¥×¥é»î¸³½¤Íý¤ÎÇä¾å¹â¤ò²ÃÌ£¤¹¤ë¤è¤¦ÊÑ¹¹                 ÂçÃ« //
// 2009/12/10 ÃÊÍî¤òÄ´À°                                               ÂçÃ« //
// 2010/01/15 200912ÅÙ¤ÎÅºÅÄ¤µ¤ó¤ÎÏ«Ì³Èñ¤òÄ´À°                         ÂçÃ« //
// 2010/01/19 200912ÅÙ¤Î¶ÈÌ³°ÑÂ÷¼ýÆþ¤È¤½¤ÎÂ¾¤òÄ´À°¡Ê1·îÅÙÌá¤·¤ÎÊ¬¤â¡Ë  ÂçÃ« //
// 2010/02/01 201001ÅÙ¤è¤ê±Ä¶È³°¤ò¿Í°÷ÈæÎ¨¤ÇºÆ·×»»¤·¤¿ÃÍ¤ËÃÖ¤­´¹¤¨     ÂçÃ« //
// 2010/02/04 201001ÅÙ¤ÎÅºÅÄ¤µ¤ó¤ÎÏ«Ì³Èñ¤òÄ´À°                              //
//            Ï«Ì³Èñ¤òÆþÎÏ¤·¤ÆÇÛÉê¤¹¤ë¤è¤¦¥×¥í¥°¥é¥à¤òºîÀ®Í½Äê         ÂçÃ« //
// 2010/02/08 201001ÅÙ¤«¤éÇÛÉê¤·¤¿Ï«Ì³Èñ¤ò²ÃÌ£¤¹¤ë¤è¤¦¤ËÊÑ¹¹           ÂçÃ« //
// 2010/03/04 201002ÅÙ±Ä¶È³°¼ý±×¤½¤ÎÂ¾¤ÎÄ´À°¤òÄÉ²Ã¡£201003¤Ë¤ÏÌá¤·     ÂçÃ« //
// 2010/04/08 $p2_l_kyu_kin¤Î2¤¬È´¤±¤Æ¤¤¤¿¤¿¤áÄûÀµ                     ÂçÃ« //
// 2010/04/12 Åö·î¤Î¥ê¥Ë¥¢·Ð¾ïÍø±×¤Ç·å¶èÀÚ¤ê¤¬¤µ¤ì¤Æ¤¤¤Ê¤«¤Ã¤¿¤Î¤òÄûÀµ ÂçÃ« //
// 2010/05/11 201004ÅÙ¥ê¥Ë¥¢¤ÎÇä¾å(¥Ä¡¼¥ë)255,240±ß¤ò¾¦´É¤Ë°ÜÆ°             //
//            ¤Þ¤¿¡¢Îß·×¤¬Àµ¤·¤¯¤È¤ì¤Æ¤¤¤Ê¤«¤Ã¤¿ÅÀ¤ò½¤Àµ               ÂçÃ« //
// 2010/10/08 ¥°¥é¥ÕºîÀ®ÍÑ¤Î¥Ç¡¼¥¿ÅÐÏ¿¤òÄÉ²Ã                           ÂçÃ« //
// 2011/05/10 ¾¦´É¤ÎÇä¾åÄ´À°¤¬£²½Å¤ÇÆþ¤Ã¤Æ¤¤¤¿°ÙÄûÀµ                   ÂçÃ« //
// 2011/07/14 ¥Ç¡¼¥¿ÅÐÏ¿¤ÇÏ«Ì³Èñ¤È·ÐÈñ¤Î¥Ç¡¼¥¿¤¬Æ±¤¸¤À¤Ã¤¿¤Î¤ò½¤Àµ     ÂçÃ« //
// 2011/10/08 ·Ð¾ïÍø±×°Ê²¼(Åö´ü½ãÍø±×Åù)¤òÄÉ²Ã(¥Ç¡¼¥¿ÅÐÏ¿¤Ï¤Ê¤·)       ÂçÃ« //
// 2012/01/16 ·Ð¾ïÍø±×°Ê²¼¤Î¥Ç¡¼¥¿ÅÐÏ¿¤òÄÉ²Ã(£²´üÈæ³ÓÉ½ÍÑ)             ÂçÃ« //
// 2012/02/03 ¥¨¥é¡¼È¯À¸Éô¤ò½¤Àµ¡Ê¸¡º÷¼ºÇÔ»þ_t¤¬»ØÄê¤µ¤ì¤Æ¤¤¤Ê¤«¤Ã¤¿¡Ë ÂçÃ« //
// 2012/02/28 2012Ç¯1·î ¶ÈÌ³°ÑÂ÷Èñ Ä´À° ¥ê¥Ë¥¢À½Â¤·ÐÈñ +1,156,130±ß    ÂçÃ« //
//             ¢¨ Ê¿½Ð²£ÀîÇÉ¸¯ÎÁ 2·î¤ËµÕÄ´À°¤ò¹Ô¤¦¤³¤È                      //
// 2012/03/05 2012Ç¯1·î ¶ÈÌ³°ÑÂ÷Èñ Ä´À° ¥ê¥Ë¥¢À½Â¤·ÐÈñ -1,156,130±ß Ìá ÂçÃ« //
// 2012/07/07 2012Ç¯6·î ±Ä¶È³°ÈñÍÑ¤ÎÄ´À°¤ò¤³¤Ã¤Á¤Ç¤·¤è¤¦¤È»×¤Ã¤¿¤¬          //
//            ºÆ·×»»¤ÎÊý¤ÇÄ´À°¤¹¤ë¤¿¤áÊÑ¹¹¤Ê¤·                         ÂçÃ« //
// 2013/11/07 2013Ç¯10·î ¾¦´É¶ÈÌ³°ÑÂ÷Èñ Ä´À°                                //
//            ¥«¥×¥éºàÎÁÈñ -1,245,035±ß¡¢¾¦´ÉÀ½Â¤·ÐÈñ +1,245,035±ß     ÂçÃ« //
//             ¢¨ ²£ÀîÇÉ¸¯ÎÁ 11·î¤ËµÕÄ´À°¤ò¹Ô¤¦¤³¤È                         //
// 2013/11/07 2013Ç¯11·î ¾¦´É¶ÈÌ³°ÑÂ÷Èñ Ä´À°                                //
//            ¥«¥×¥éºàÎÁÈñ +1,245,035±ß¡¢¾¦´ÉÀ½Â¤·ÐÈñ -1,245,035±ß     ÂçÃ« //
// 2014/09/04 ¾¦´É¤ÎÀ½Â¤·ÐÈñÏ«Ì³Èñ¤ò³Æ¥»¥°¥á¥ó¥ÈÇÛÉê¤Î°ÙÄ´À°           ÂçÃ« //
// 2016/07/08 µ¡¹©¤Î¾È²ñ¤òÄÉ²Ã¡¢¾¦´É¤ò£â_¤«¤é£î_¤ØÊÑ¹¹
//            lt¤Î¥×¥í¥°¥é¥à¤è¤êµ¡¹©¤òÄÉ²Ã¤·¡¢¥ê¥Ë¥¢¤òÃÖ´¹¤¨
//            lt¤Î¥×¥í¥°¥é¥à¤è¤êµ¡¹©¤òÄÉ²Ã¤·¡¢¥ê¥Ë¥¢¤òÃÖ¤­´¹¤¨¤¿¤¬
//            ¾¦´É¤¬£â¤Ç·×»»¤·¤Æ¤¤¤ë¤¿¤á¡¢µ¡¹©¤È¤«¤Ö¤Ã¤¿¡£¡Ê½é´ü²½¤·¤¿¡Ë
//            ¾¦´É¤òÀè¤ËÄ¾¤·¤Æ¤«¤é¡¢µ¡¹©¤òÄÉ²Ã¤·¤¿Êý¤¬³Ú
//////////////////////////////////////////////////////////////////////////////
// ini_set('error_reporting', E_STRICT);    // E_STRICT=2048(php5) E_ALL=2047 debug ÍÑ
// ini_set('error_reporting', E_ALL);       // E_ALL='2047' debug ÍÑ
// ini_set('display_errors', '1');          // Error É½¼¨ ON debug ÍÑ ¥ê¥ê¡¼¥¹¸å¥³¥á¥ó¥È
session_start();                            // ini_set()¤Î¼¡¤Ë»ØÄê¤¹¤ë¤³¤È Script ºÇ¾å¹Ô

require_once ('../function.php');           // define.php ¤È pgsql.php ¤ò require_once ¤·¤Æ¤¤¤ë
require_once ('../tnk_func.php');           // TNK ¤Ë°ÍÂ¸¤¹¤ëÉôÊ¬¤Î´Ø¿ô¤ò require_once ¤·¤Æ¤¤¤ë
require_once ('../MenuHeader.php');         // TNK Á´¶¦ÄÌ menu class
access_log();                               // Script Name ¤Ï¼«Æ°¼èÆÀ

///// TNK ¶¦ÍÑ¥á¥Ë¥å¡¼¥¯¥é¥¹¤Î¥¤¥ó¥¹¥¿¥ó¥¹¤òºîÀ®
$menu = new MenuHeader(0);                  // Ç§¾Ú¥Á¥§¥Ã¥¯0=°ìÈÌ°Ê¾å Ìá¤êÀè=TOP_MENU ¥¿¥¤¥È¥ëÌ¤ÀßÄê
   // ¼ÂºÝ¤ÎÇ§¾Ú¤Ïprofit_loss_submit.php¤Ç¹Ô¤Ã¤Æ¤¤¤ëaccount_group_check()¤ò»ÈÍÑ

///// ¥µ¥¤¥ÈÀßÄê
// $menu->set_site(10, 7);                  // site_index=10(Â»±×¥á¥Ë¥å¡¼) site_id=7(·î¼¡Â»±×)
///// É½Âê¤ÎÀßÄê
$menu->set_caption('ÆÊÌÚÆüÅì¹©´ï(³ô)');
///// ¸Æ½ÐÀè¤ÎactionÌ¾¤È¥¢¥É¥ì¥¹ÀßÄê
$menu->set_action('ÆÃµ­»ö¹àÆþÎÏ',   PL . 'profit_loss_comment_put.php');

///// ´ü¡¦·î¤Î¼èÆÀ
$ki = Ym_to_tnk($_SESSION['pl_ym']);
$tuki = substr($_SESSION['pl_ym'],4,2);

///// ¥¿¥¤¥È¥ëÌ¾(¥½¡¼¥¹¤Î¥¿¥¤¥È¥ëÌ¾¤È¥Õ¥©¡¼¥à¤Î¥¿¥¤¥È¥ëÌ¾)
$menu->set_title("Âè {$ki} ´ü¡¡{$tuki} ·îÅÙ¡¡£Ã £Ì £Ô »î½¤ ¾¦´É ¾¦ ÉÊ ÊÌ Â» ±× ·× »» ½ñ");

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
///// ´ü½éÇ¯·î¤Î»»½Ð
$yyyy = substr($yyyymm, 0,4);
$mm   = substr($yyyymm, 4,2);
if (($mm >= 1) && ($mm <= 3)) {
    $yyyy = ($yyyy - 1);
}
$str_ym = $yyyy . "04";     // ´ü½éÇ¯·î

///// É½¼¨Ã±°Ì¤òÀßÄê¼èÆÀ
if (isset($_POST['keihi_tani'])) {
    $_SESSION['keihi_tani'] = $_POST['keihi_tani'];
    $tani = $_SESSION['keihi_tani'];
} elseif (isset($_SESSION['keihi_tani'])) {
    $tani = $_SESSION['keihi_tani'];
} else {
    $tani = 1000;           // ½é´üÃÍ É½¼¨Ã±°Ì Àé±ß
    $_SESSION['keihi_tani'] = $tani;
}
///// É½¼¨ ¾®¿ôÉô·å¿ô ÀßÄê¼èÆÀ
if (isset($_POST['keihi_keta'])) {
    $_SESSION['keihi_keta'] = $_POST['keihi_keta'];
    $keta = $_SESSION['keihi_keta'];
} elseif (isset($_SESSION['keihi_keta'])) {
    $keta = $_SESSION['keihi_keta'];
} else {
    $keta = 0;              // ½é´üÃÍ ¾®¿ôÅÀ°Ê²¼·å¿ô
    $_SESSION['keihi_keta'] = $keta;
}

/********** Çä¾å¹â **********/
    ///// Åö·î
if ( $yyyymm >= 200909) {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='¾¦´ÉÇä¾å¹â'", $yyyymm);
    if (getUniResult($query, $n_uri) < 1) {
        $n_uri        = 0;      // ¸¡º÷¼ºÇÔ
        $n_uri_sagaku = 0;
    } else {
        if ($yyyymm == 201004) {
            $n_uri = $n_uri + 255240;
        }
        $n_uri_sagaku = 0;
    }
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='¾¦´ÉÇä¾åÄ´À°³Û'", $yyyymm);
    if (getUniResult($query, $n_uri_cho) < 1) {
        // ¸¡º÷¼ºÇÔ Ä´À°¤¬Ìµ¤¤¤Î¤Ç²¿¤â¤·¤Ê¤¤
        $n_uri_sagaku = 0;
    } else {
        $n_uri        = $n_uri + $n_uri_cho;
        $n_uri_sagaku = $n_uri_cho;
    }
} else {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='¾¦´ÉÇä¾å¹â'", $yyyymm);
    if (getUniResult($query, $n_uri) < 1) {
        $n_uri        = 0;      // ¸¡º÷¼ºÇÔ
        $n_uri_sagaku = 0;
    } else {
        $n_uri_sagaku = $n_uri;
    }
}
if ( $yyyymm >= 200911) {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='¥«¥×¥é»î½¤Çä¾å¹â'", $yyyymm);
    if (getUniResult($query, $sc_uri) < 1) {
        $sc_uri        = 0;     // ¸¡º÷¼ºÇÔ
        $sc_uri_sagaku = 0;
        $sc_uri_temp   = 0;
    } else {
        $sc_uri_temp   = $sc_uri;
        $sc_uri_sagaku = $sc_uri;
        $sc_uri        = number_format(($sc_uri / $tani), $keta);
    }
} else{
    $sc_uri        = 0;         // ¸¡º÷¼ºÇÔ
    $sc_uri_sagaku = 0;
    $sc_uri_temp   = 0;
}
if ( $yyyymm >= 200909) {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='»î½¤Çä¾å¹â'", $yyyymm);
    if (getUniResult($query, $s_uri) < 1) {
        $s_uri        = 0;      // ¸¡º÷¼ºÇÔ
        $s_uri_sagaku = 0;
        $s_uri_temp   = 0;
    } else {
        $s_uri_temp = $s_uri;
        if ($yyyymm == 200906) {
            $s_uri  = $s_uri - 3100900;
        } elseif ($yyyymm == 200905) {
            $s_uri  = $s_uri + 1550450;
        } elseif ($yyyymm == 200904) {
            $s_uri  = $s_uri + 1550450;
        }
        $s_uri_sagaku = $s_uri;
    }
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='»î½¤Çä¾åÄ´À°³Û'", $yyyymm);
    if (getUniResult($query, $s_uri_cho) < 1) {
        // ¸¡º÷¼ºÇÔ
        $s_uri = $s_uri + $sc_uri_sagaku;       // ¥«¥×¥é»î¸³½¤Íý¤ò²ÃÌ£
        $s_uri = number_format(($s_uri / $tani), $keta);
    } else {
        $s_uri_sagaku = $s_uri_sagaku + $s_uri_cho;
        $s_uri_temp   = $s_uri_sagaku;
        $s_uri        = $s_uri_sagaku + $sc_uri_sagaku;         // ¥«¥×¥é»î¸³½¤Íý¤ò²ÃÌ£¡Êtemp¤Î¸å¡Ý¥ê¥Ë¥¢¤«¤é¥Þ¥¤¥Ê¥¹¤·¤Æ¤·¤Þ¤¦°Ù¡Ë
        $s_uri        = number_format(($s_uri / $tani), $keta);
    }
} else {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='»î½¤Çä¾å¹â'", $yyyymm);
    if (getUniResult($query, $s_uri) < 1) {
        $s_uri        = 0;      // ¸¡º÷¼ºÇÔ
        $s_uri_sagaku = 0;
        $s_uri_temp   = 0;
    } else {
        $s_uri_temp = $s_uri;
        if ($yyyymm == 200906) {
            $s_uri  = $s_uri - 3100900;
        } elseif ($yyyymm == 200905) {
            $s_uri  = $s_uri + 1550450;
        } elseif ($yyyymm == 200904) {
            $s_uri  = $s_uri + 1550450;
        }
        $s_uri_sagaku = $s_uri;
        $s_uri        = number_format(($s_uri / $tani), $keta);
    }
}
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='Á´ÂÎÇä¾å¹â'", $yyyymm);
if (getUniResult($query, $all_uri) < 1) {
    $all_uri = 0;               // ¸¡º÷¼ºÇÔ
} else {
    if ($yyyymm == 200906) {
        $all_uri = $all_uri + $n_uri_sagaku - 3100900;
    } elseif ($yyyymm == 200905) {
        $all_uri = $all_uri + $n_uri_sagaku + 1550450;
    } elseif ($yyyymm == 200904) {
        $all_uri = $all_uri + $n_uri_sagaku + 1550450;
    } else {
        $all_uri = $all_uri + $n_uri_sagaku;
    }
    $all_uri = number_format(($all_uri / $tani), $keta);
}
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='¥«¥×¥éÇä¾å¹â'", $yyyymm);
if (getUniResult($query, $c_uri) < 1) {
    $c_uri = 0;                 // ¸¡º÷¼ºÇÔ
} else {
    $c_uri = $c_uri - $sc_uri_sagaku;                   // ¥«¥×¥é»î¸³½¤Íý¤ò²ÃÌ£
    $c_uri = number_format(($c_uri / $tani), $keta);
}
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='µ¡¹©Çä¾å¹â'", $yyyymm);
if (getUniResult($query, $b_uri) < 1) {
    $b_uri        = 0;          // ¸¡º÷¼ºÇÔ
    $b_uri_sagaku = 0;
} else {
    $b_uri_sagaku = $b_uri;
    $b_uri        = number_format(($b_uri / $tani), $keta);
}
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='¥ê¥Ë¥¢Çä¾å¹â'", $yyyymm);
if (getUniResult($query, $l_uri) < 1) {
    $l_uri         = 0;         // ¸¡º÷¼ºÇÔ
    $lh_uri        = 0;
    $lh_uri_sagaku = 0;
} else {
    if ($yyyymm == 200906) {
        $l_uri = $l_uri - 3100900;
    } elseif ($yyyymm == 200905) {
        $l_uri = $l_uri + 1550450;
    } elseif ($yyyymm == 200904) {
        $l_uri = $l_uri + 1550450;
    }
    if ($yyyymm == 201004) {
        $l_uri = $l_uri - 255240;
    }
    $lh_uri        = $l_uri - $s_uri_sagaku - $b_uri_sagaku;
    $lh_uri_sagaku = $lh_uri;
    $l_uri         = $l_uri - $s_uri_sagaku;                   // »î¸³½¤ÍýÇä¾å¹â¤ò¥ê¥Ë¥¢¤ÎÇä¾å¤è¤ê¥Þ¥¤¥Ê¥¹
    $lh_uri        = number_format(($lh_uri / $tani), $keta);
    $l_uri         = number_format(($l_uri / $tani), $keta);
}
    ///// Á°·î
if ($yyyymm >= 200910) {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='¾¦´ÉÇä¾å¹â'", $p1_ym);
    if (getUniResult($query, $p1_n_uri) < 1) {
        $p1_n_uri        = 0;   // ¸¡º÷¼ºÇÔ
        $p1_n_uri_sagaku = 0;
    } else {
        if ($p1_ym == 201004) {
            $p1_n_uri = $p1_n_uri + 255240;
        }
        $p1_n_uri_sagaku = 0;
    }
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='¾¦´ÉÇä¾åÄ´À°³Û'", $p1_ym);
    if (getUniResult($query, $p1_n_uri_cho) < 1) {
        // ¸¡º÷¼ºÇÔ Ä´À°¤¬Ìµ¤¤¤Î¤Ç²¿¤â¤·¤Ê¤¤
    } else {
        $p1_n_uri        = $p1_n_uri + $p1_n_uri_cho;
        $p1_n_uri_sagaku = $p1_n_uri_cho;
    }
} else {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='¾¦´ÉÇä¾å¹â'", $p1_ym);
    if (getUniResult($query, $p1_n_uri) < 1) {
        $p1_n_uri        = 0;   // ¸¡º÷¼ºÇÔ
        $p1_n_uri_sagaku = 0;
    } else {
        $p1_n_uri_sagaku = $p1_n_uri;
    }
}
if ( $yyyymm >= 200911) {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='¥«¥×¥é»î½¤Çä¾å¹â'", $p1_ym);
    if (getUniResult($query, $p1_sc_uri) < 1) {
        $p1_sc_uri        = 0;      // ¸¡º÷¼ºÇÔ
        $p1_sc_uri_sagaku = 0;
        $p1_sc_uri_temp   = 0;
    } else {
        $p1_sc_uri_temp   = $p1_sc_uri;
        $p1_sc_uri_sagaku = $p1_sc_uri;
        $p1_sc_uri        = number_format(($p1_sc_uri / $tani), $keta);
    }
} else{
    $p1_sc_uri        = 0;          // ¸¡º÷¼ºÇÔ
    $p1_sc_uri_sagaku = 0;
    $p1_sc_uri_temp   = 0;
}
if ($yyyymm >= 200910) {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='»î½¤Çä¾å¹â'", $p1_ym);
    if (getUniResult($query, $p1_s_uri) < 1) {
        $p1_s_uri        = 0;       // ¸¡º÷¼ºÇÔ
        $p1_s_uri_sagaku = 0;
        $p1_s_uri_temp   = 0;
    } else {
        $p1_s_uri_temp = $p1_s_uri;
        if ($p1_ym == 200906) {
            $p1_s_uri  = $p1_s_uri - 3100900;
        } elseif ($p1_ym == 200905) {
            $p1_s_uri  = $p1_s_uri + 1550450;
        } elseif ($p1_ym == 200904) {
            $p1_s_uri  = $p1_s_uri + 1550450;
        }
        $p1_s_uri_sagaku = $p1_s_uri;
    }
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='»î½¤Çä¾åÄ´À°³Û'", $p1_ym);
    if (getUniResult($query, $p1_s_uri_cho) < 1) {
        // ¸¡º÷¼ºÇÔ
        $p1_s_uri = $p1_s_uri + $p1_sc_uri_sagaku;                  // ¥«¥×¥é»î¸³½¤Íý¤ò²ÃÌ£
        $p1_s_uri = number_format(($p1_s_uri / $tani), $keta);
    } else {
        $p1_s_uri_sagaku = $p1_s_uri_sagaku + $p1_s_uri_cho;
        $p1_s_uri_temp   = $p1_s_uri_sagaku;
        $p1_s_uri        = $p1_s_uri_sagaku + $p1_sc_uri_sagaku;    // ¥«¥×¥é»î¸³½¤Íý¤ò²ÃÌ£¡Êtemp¤Î¸å¡Ý¥ê¥Ë¥¢¤«¤é¥Þ¥¤¥Ê¥¹¤·¤Æ¤·¤Þ¤¦°Ù¡Ë
        $p1_s_uri        = number_format(($p1_s_uri / $tani), $keta);
    }
} else {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='»î½¤Çä¾å¹â'", $p1_ym);
    if (getUniResult($query, $p1_s_uri) < 1) {
        $p1_s_uri        = 0;           // ¸¡º÷¼ºÇÔ
        $p1_s_uri_sagaku = 0;
        $p1_s_uri_temp   = 0;
    } else {
        $p1_s_uri_temp = $p1_s_uri;
        if ($p1_ym == 200906) {
            $p1_s_uri  = $p1_s_uri - 3100900;
        } elseif ($p1_ym == 200905) {
            $p1_s_uri  = $p1_s_uri + 1550450;
        } elseif ($p1_ym == 200904) {
            $p1_s_uri  = $p1_s_uri + 1550450;
        }
        $p1_s_uri_sagaku = $p1_s_uri;
        $p1_s_uri        = number_format(($p1_s_uri / $tani), $keta);
    }
}
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='Á´ÂÎÇä¾å¹â'", $p1_ym);
if (getUniResult($query, $p1_all_uri) < 1) {
    $p1_all_uri = 0;                    // ¸¡º÷¼ºÇÔ
} else {
    if ($p1_ym == 200906) {
        $p1_all_uri = $p1_all_uri + $p1_n_uri_sagaku - 3100900;
    } elseif ($p1_ym == 200905) {
        $p1_all_uri = $p1_all_uri + $p1_n_uri_sagaku + 1550450;
    } elseif ($p1_ym == 200904) {
        $p1_all_uri = $p1_all_uri + $p1_n_uri_sagaku + 1550450;
    } else {
        $p1_all_uri = $p1_all_uri + $p1_n_uri_sagaku;
    }
    $p1_all_uri = number_format(($p1_all_uri / $tani), $keta);
}
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='¥«¥×¥éÇä¾å¹â'", $p1_ym);
if (getUniResult($query, $p1_c_uri) < 1) {
    $p1_c_uri = 0;                      // ¸¡º÷¼ºÇÔ
} else {
    $p1_c_uri = $p1_c_uri - $p1_sc_uri_sagaku;                  // ¥«¥×¥é»î¸³½¤Íý¤ò²ÃÌ£
    $p1_c_uri = number_format(($p1_c_uri / $tani), $keta);
}
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='µ¡¹©Çä¾å¹â'", $p1_ym);
if (getUniResult($query, $p1_b_uri) < 1) {
    $p1_b_uri        = 0;     // ¸¡º÷¼ºÇÔ
    $p1_b_uri_sagaku = 0;
} else {
    $p1_b_uri_sagaku = $p1_b_uri;
    $p1_b_uri        = number_format(($p1_b_uri / $tani), $keta);
}
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='¥ê¥Ë¥¢Çä¾å¹â'", $p1_ym);
if (getUniResult($query, $p1_l_uri) < 1) {
    $p1_l_uri         = 0;    // ¸¡º÷¼ºÇÔ
    $p1_lh_uri        = 0;
    $p1_lh_uri_sagaku = 0;
} else {
    if ($p1_ym == 200906) {
        $p1_l_uri = $p1_l_uri - 3100900;
    } elseif ($p1_ym == 200905) {
        $p1_l_uri = $p1_l_uri + 1550450;
    } elseif ($p1_ym == 200904) {
        $p1_l_uri = $p1_l_uri + 1550450;
    }
    if ($p1_ym == 201004) {
        $p1_l_uri = $p1_l_uri - 255240;
    }
    $p1_lh_uri        = $p1_l_uri - $p1_s_uri_sagaku - $p1_b_uri_sagaku;
    $p1_lh_uri_sagaku = $p1_lh_uri;
    $p1_l_uri         = $p1_l_uri - $p1_s_uri_sagaku;                   // »î¸³½¤ÍýÇä¾å¹â¤ò¥ê¥Ë¥¢¤ÎÇä¾å¤è¤ê¥Þ¥¤¥Ê¥¹
    $p1_lh_uri        = number_format(($p1_lh_uri / $tani), $keta);
    $p1_l_uri         = number_format(($p1_l_uri / $tani), $keta);
}
    ///// Á°Á°·î
if ($yyyymm >= 200911) {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='¾¦´ÉÇä¾å¹â'", $p2_ym);
    if (getUniResult($query, $p2_n_uri) < 1) {
        $p2_n_uri        = 0;           // ¸¡º÷¼ºÇÔ
        $p2_n_uri_sagaku = 0;
    } else {
        if ($p2_ym == 201004) {
            $p2_n_uri = $p2_n_uri + 255240;
        }
        $p2_n_uri_sagaku = 0;
    }
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='¾¦´ÉÇä¾åÄ´À°³Û'", $p2_ym);
    if (getUniResult($query, $p2_n_uri_cho) < 1) {
        // ¸¡º÷¼ºÇÔ Ä´À°¤¬Ìµ¤¤¤Î¤Ç²¿¤â¤·¤Ê¤¤
    } else {
        $p2_n_uri        = $p2_n_uri + $p2_n_uri_cho;
        $p2_n_uri_sagaku = $p2_n_uri_cho;
    }
} else {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='¾¦´ÉÇä¾å¹â'", $p2_ym);
    if (getUniResult($query, $p2_n_uri) < 1) {
        $p2_n_uri        = 0;           // ¸¡º÷¼ºÇÔ
        $p2_n_uri_sagaku = 0;
    } else {
        $p2_n_uri_sagaku = $p2_n_uri;
    }
}
if ( $yyyymm >= 200911) {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='¥«¥×¥é»î½¤Çä¾å¹â'", $p2_ym);
    if (getUniResult($query, $p2_sc_uri) < 1) {
        $p2_sc_uri        = 0;          // ¸¡º÷¼ºÇÔ
        $p2_sc_uri_sagaku = 0;
        $p2_sc_uri_temp   = 0;
    } else {
        $p2_sc_uri_temp   = $p2_sc_uri;
        $p2_sc_uri_sagaku = $p2_sc_uri;
        $p2_sc_uri        = number_format(($p2_sc_uri / $tani), $keta);
    }
} else{
    $p2_sc_uri        = 0;              // ¸¡º÷¼ºÇÔ
    $p2_sc_uri_sagaku = 0;
    $p2_sc_uri_temp   = 0;
}
if ($yyyymm >= 200911) {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='»î½¤Çä¾å¹â'", $p2_ym);
    if (getUniResult($query, $p2_s_uri) < 1) {
        $p2_s_uri        = 0;           // ¸¡º÷¼ºÇÔ
        $p2_s_uri_sagaku = 0;
        $p2_s_uri_temp   = 0;
    } else {
        $p2_s_uri_temp = $p2_s_uri;
        if ($p2_ym == 200906) {
            $p2_s_uri  = $p2_s_uri - 3100900;
        } elseif ($p2_ym == 200905) {
            $p2_s_uri  = $p2_s_uri + 1550450;
        } elseif ($p2_ym == 200904) {
            $p2_s_uri  = $p2_s_uri + 1550450;
        }
        $p2_s_uri_sagaku = $p2_s_uri;
    }
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='»î½¤Çä¾åÄ´À°³Û'", $p2_ym);
    if (getUniResult($query, $p2_s_uri_cho) < 1) {
        // ¸¡º÷¼ºÇÔ
        $p2_s_uri = $p2_s_uri + $p2_sc_uri_sagaku;                  // ¥«¥×¥é»î¸³½¤Íý¤ò²ÃÌ£
        $p2_s_uri = number_format(($p2_s_uri / $tani), $keta);
    } else {
        $p2_s_uri_sagaku = $p2_s_uri_sagaku + $p2_s_uri_cho;
        $p2_s_uri_temp   = $p2_s_uri_sagaku;
        $p2_s_uri        = $p2_s_uri_sagaku + $p2_sc_uri_sagaku;    // ¥«¥×¥é»î¸³½¤Íý¤ò²ÃÌ£¡Êtemp¤Î¸å¡Ý¥ê¥Ë¥¢¤«¤é¥Þ¥¤¥Ê¥¹¤·¤Æ¤·¤Þ¤¦°Ù¡Ë
        $p2_s_uri        = number_format(($p2_s_uri / $tani), $keta);
    }
} else {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='»î½¤Çä¾å¹â'", $p2_ym);
    if (getUniResult($query, $p2_s_uri) < 1) {
        $p2_s_uri        = 0;           // ¸¡º÷¼ºÇÔ
        $p2_s_uri_sagaku = 0;
        $p2_s_uri_temp   = 0;
    } else {
        $p2_s_uri_temp = $p2_s_uri;
        if ($p2_ym == 200906) {
            $p2_s_uri  = $p2_s_uri - 3100900;
        } elseif ($p2_ym == 200905) {
            $p2_s_uri  = $p2_s_uri + 1550450;
        } elseif ($p2_ym == 200904) {
            $p2_s_uri  = $p2_s_uri + 1550450;
        }
        $p2_s_uri_sagaku = $p2_s_uri;
        $p2_s_uri        = number_format(($p2_s_uri / $tani), $keta);
    }
}

$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='Á´ÂÎÇä¾å¹â'", $p2_ym);
if (getUniResult($query, $p2_all_uri) < 1) {
    $p2_all_uri = 0;                    // ¸¡º÷¼ºÇÔ
} else {
    if ($p2_ym == 200906) {
        $p2_all_uri = $p2_all_uri + $p2_n_uri_sagaku - 3100900;
    } elseif ($p2_ym == 200905) {
        $p2_all_uri = $p2_all_uri + $p2_n_uri_sagaku + 1550450;
    } elseif ($p2_ym == 200904) {
        $p2_all_uri = $p2_all_uri + $p2_n_uri_sagaku + 1550450;
    } else {
        $p2_all_uri = $p2_all_uri + $p2_n_uri_sagaku;
    }
    $p2_all_uri = number_format(($p2_all_uri / $tani), $keta);
}
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='¥«¥×¥éÇä¾å¹â'", $p2_ym);
if (getUniResult($query, $p2_c_uri) < 1) {
    $p2_c_uri = 0;                      // ¸¡º÷¼ºÇÔ
} else {
    $p2_c_uri = $p2_c_uri - $p2_sc_uri_sagaku;                  // ¥«¥×¥é»î¸³½¤Íý¤ò²ÃÌ£
    $p2_c_uri = number_format(($p2_c_uri / $tani), $keta);
}
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='µ¡¹©Çä¾å¹â'", $p2_ym);
if (getUniResult($query, $p2_b_uri) < 1) {
    $p2_b_uri        = 0;     // ¸¡º÷¼ºÇÔ
    $p2_b_uri_sagaku = 0;
} else {
    $p2_b_uri_sagaku = $p2_b_uri;
    $p2_b_uri        = number_format(($p2_b_uri / $tani), $keta);
}
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='¥ê¥Ë¥¢Çä¾å¹â'", $p2_ym);
if (getUniResult($query, $p2_l_uri) < 1) {
    $p2_l_uri         = 0;    // ¸¡º÷¼ºÇÔ
    $p2_lh_uri        = 0;
    $p2_lh_uri_sagaku = 0;
} else {
    if ($p2_ym == 200906) {
        $p2_l_uri = $p2_l_uri - 3100900;
    } elseif ($p2_ym == 200905) {
        $p2_l_uri = $p2_l_uri + 1550450;
    } elseif ($p2_ym == 200904) {
        $p2_l_uri = $p2_l_uri + 1550450;
    }
    if ($p2_ym == 201004) {
        $p2_l_uri = $p2_l_uri - 255240;
    }
    $p2_lh_uri        = $p2_l_uri - $p2_s_uri_sagaku - $p2_b_uri_sagaku;
    $p2_lh_uri_sagaku = $p2_lh_uri;
    $p2_l_uri         = $p2_l_uri - $p2_s_uri_sagaku;                   // »î¸³½¤ÍýÇä¾å¹â¤ò¥ê¥Ë¥¢¤ÎÇä¾å¤è¤ê¥Þ¥¤¥Ê¥¹
    $p2_lh_uri        = number_format(($p2_lh_uri / $tani), $keta);
    $p2_l_uri         = number_format(($p2_l_uri / $tani), $keta);
}
    ///// º£´üÎß·×
if($yyyymm >= 201004) {
    $query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='¾¦´ÉÇä¾å¹â'", $str_ym, $yyyymm);
    if (getUniResult($query, $rui_n_uri) < 1) {
        $rui_n_uri        = 0;          // ¸¡º÷¼ºÇÔ
        $rui_n_uri_sagaku = 0;
    } else {
        if ($yyyymm >= 201004 && $yyyymm <= 201103) {
            $rui_n_uri = $rui_n_uri + 255240;
        }
        $rui_n_uri_sagaku = 0;
    }
    $query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='¾¦´ÉÇä¾åÄ´À°³Û'", $str_ym, $yyyymm);
    if (getUniResult($query, $rui_n_uri_cho) < 1) {
        // ¸¡º÷¼ºÇÔ Ä´À°¤¬Ìµ¤¤¤Î¤Ç²¿¤â¤·¤Ê¤¤
        $rui_n_uri_sagaku = 0;
    } else {
        $rui_n_uri        = $rui_n_uri + $rui_n_uri_cho;
        $rui_n_uri_sagaku = $rui_n_uri_cho;
    }
} else if($yyyymm >= 200909 && $yyyymm <= 201003) {
    $query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='¾¦´ÉÇä¾å¹â'", $str_ym, $yyyymm);
    if (getUniResult($query, $rui_n_uri) < 1) {
        $rui_n_uri        = 0;          // ¸¡º÷¼ºÇÔ
        $rui_n_uri_sagaku = 0;
    } else {
        $rui_n_uri_sagaku = 0;
    }
    $query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='¾¦´ÉÇä¾åÄ´À°³Û'", $str_ym, $yyyymm);
    if (getUniResult($query, $rui_n_uri_cho) < 1) {
        // ¸¡º÷¼ºÇÔ Ä´À°¤¬Ìµ¤¤¤Î¤Ç²¿¤â¤·¤Ê¤¤
        $rui_n_uri_sagaku = 0;
    } else {
        $rui_n_uri        = $rui_n_uri + $rui_n_uri_cho;
        $rui_n_uri_sagaku = $rui_n_uri_cho + 25354300;      // 7·î8·îÊ¬¤ÎÄ´À°¤ò9·î¤ËÆþ¤ì¤¿Ê¬¤ÎÌá¤·
    }
} else {
    $query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='¾¦´ÉÇä¾å¹â'", $str_ym, $yyyymm);
    if (getUniResult($query, $rui_n_uri) < 1) {
        $rui_n_uri        = 0;          // ¸¡º÷¼ºÇÔ
        $rui_n_uri_sagaku = 0;
    } else {
        $rui_n_uri_sagaku = $rui_n_uri;
    }
}
if ( $yyyymm >= 200911) {
    $query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='¥«¥×¥é»î½¤Çä¾å¹â'", $str_ym, $yyyymm);
    if (getUniResult($query, $rui_sc_uri) < 1) {
        $rui_sc_uri        = 0;         // ¸¡º÷¼ºÇÔ
        $rui_sc_uri_sagaku = 0;
        $rui_sc_uri_temp   = 0;
    } else {
        $rui_sc_uri_temp   = $rui_sc_uri;
        $rui_sc_uri_sagaku = $rui_sc_uri;
        $rui_sc_uri        = number_format(($rui_sc_uri / $tani), $keta);
    }
} else{
    $rui_sc_uri        = 0;             // ¸¡º÷¼ºÇÔ
    $rui_sc_uri_sagaku = 0;
    $rui_sc_uri_temp   = 0;
}
if ($yyyymm >= 200909) {
    $query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='»î½¤Çä¾å¹â'", $str_ym, $yyyymm);
    if (getUniResult($query, $rui_s_uri) < 1) {
        $rui_s_uri        = 0;          // ¸¡º÷¼ºÇÔ
        $rui_s_uri_sagaku = 0;
    } else {
        $rui_s_uri_sagaku = $rui_s_uri;
    }
    $query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='»î½¤Çä¾åÄ´À°³Û'", $str_ym, $yyyymm);
    if (getUniResult($query, $rui_s_uri_cho) < 1) {
        // ¸¡º÷¼ºÇÔ
        $rui_s_uri = $rui_s_uri + $rui_sc_uri_sagaku;                   // ¥«¥×¥é»î¸³½¤Íý¤ò²ÃÌ£
        $rui_s_uri = number_format(($rui_s_uri / $tani), $keta);
    } else {
        $rui_s_uri_sagaku = $rui_s_uri_sagaku + $rui_s_uri_cho;
        $rui_s_uri        = $rui_s_uri_sagaku + $rui_sc_uri_sagaku;     // ¥«¥×¥é»î¸³½¤Íý¤ò²ÃÌ£¡Êtemp¤Î¸å¡Ý¥ê¥Ë¥¢¤«¤é¥Þ¥¤¥Ê¥¹¤·¤Æ¤·¤Þ¤¦°Ù¡Ë
        $rui_s_uri        = number_format(($rui_s_uri / $tani), $keta);
    }
} else {
    $query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='»î½¤Çä¾å¹â'", $str_ym, $yyyymm);
    if (getUniResult($query, $rui_s_uri) < 1) {
        $rui_s_uri        = 0;          // ¸¡º÷¼ºÇÔ
        $rui_s_uri_sagaku = 0;
    } else {
        if ($yyyymm == 200905) {
            $rui_s_uri = $rui_s_uri + 3100900;
        } elseif ($yyyymm == 200904) {
            $rui_s_uri = $rui_s_uri + 1550450;
        }
        $rui_s_uri_sagaku = $rui_s_uri;
        $rui_s_uri        = number_format(($rui_s_uri / $tani), $keta);
    }
}
$query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='Á´ÂÎÇä¾å¹â'", $str_ym, $yyyymm);
if (getUniResult($query, $rui_all_uri) < 1) {
    $rui_all_uri = 0;                   // ¸¡º÷¼ºÇÔ
} else {
    if ($yyyymm == 200905) {
        $rui_all_uri = $rui_all_uri + 3100900;
    } elseif ($yyyymm == 200904) {
        $rui_all_uri = $rui_all_uri + 1550450;
    }
    $rui_all_uri = $rui_all_uri + $rui_n_uri_sagaku;
    $rui_all_uri = number_format(($rui_all_uri / $tani), $keta);
}
$query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='¥«¥×¥éÇä¾å¹â'", $str_ym, $yyyymm);
if (getUniResult($query, $rui_c_uri) < 1) {
    $rui_c_uri = 0;                     // ¸¡º÷¼ºÇÔ
} else {
    $rui_c_uri = $rui_c_uri - $rui_sc_uri_sagaku;                   // ¥«¥×¥é»î¸³½¤Íý¤ò²ÃÌ£
    $rui_c_uri = number_format(($rui_c_uri / $tani), $keta);
}
$query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='µ¡¹©Çä¾å¹â'", $str_ym, $yyyymm);
if (getUniResult($query, $rui_b_uri) < 1) {
    $rui_b_uri        = 0;         // ¸¡º÷¼ºÇÔ
    $rui_b_uri_sagaku = 0;
} else {
    $rui_b_uri_sagaku = $rui_b_uri;
    $rui_b_uri        = number_format(($rui_b_uri / $tani), $keta);
}
$query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='¥ê¥Ë¥¢Çä¾å¹â'", $str_ym, $yyyymm);
if (getUniResult($query, $rui_l_uri) < 1) {
    $rui_l_uri         = 0;        // ¸¡º÷¼ºÇÔ
    $rui_lh_uri        = 0;
    $rui_lh_uri_sagaku = 0;
} else {
    if ($yyyymm == 200905) {
        $rui_l_uri     = $rui_l_uri + 3100900;
    } elseif ($yyyymm == 200904) {
        $rui_l_uri     = $rui_l_uri + 1550450;
    }
    if ($yyyymm >= 201004 && $yyyymm <= 201103) {
        $rui_l_uri = $rui_l_uri - 255240;
    }
    $rui_lh_uri        = $rui_l_uri - $rui_s_uri_sagaku - $rui_b_uri_sagaku;
    $rui_lh_uri_sagaku = $rui_lh_uri;
    $rui_l_uri         = $rui_l_uri - $rui_s_uri_sagaku;                   // »î¸³½¤ÍýÇä¾å¹â¤ò¥ê¥Ë¥¢¤ÎÇä¾å¤è¤ê¥Þ¥¤¥Ê¥¹
    $rui_lh_uri        = number_format(($rui_lh_uri / $tani), $keta);
    $rui_l_uri         = number_format(($rui_l_uri / $tani), $keta);
}

/********** ´ü¼óºàÎÁ»Å³ÝÉÊÃª²·¹â **********/
    ///// ¾¦´É
$p2_n_invent  = 0;
$p1_n_invent  = 0;
$n_invent     = 0;
$rui_n_invent = 0;
    ///// »î¸³¡¦½¤Íý
$p2_s_invent  = 0;
$p1_s_invent  = 0;
$s_invent     = 0;
$rui_s_invent = 0;
    ///// Åö·î
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='Á´ÂÎ´ü¼óÃª²·¹â'", $yyyymm);
if (getUniResult($query, $all_invent) < 1) {
    $all_invent = 0;                        // ¸¡º÷¼ºÇÔ
} else {
    $all_invent = number_format(($all_invent / $tani), $keta);
}
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='¥«¥×¥é´ü¼óÃª²·¹â'", $yyyymm);
if (getUniResult($query, $c_invent) < 1) {
    $c_invent = 0;                          // ¸¡º÷¼ºÇÔ
} else {
    $c_invent = number_format(($c_invent / $tani), $keta);
}
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='µ¡¹©´ü¼óÃª²·¹â'", $yyyymm);
if (getUniResult($query, $b_invent) < 1) {
    $b_invent = 0;              // ¸¡º÷¼ºÇÔ
    $b_invent_sagaku = 0;
} else {
    $b_invent_sagaku = $b_invent;
    $b_invent        = number_format(($b_invent / $tani), $keta);
}
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='¥ê¥Ë¥¢´ü¼óÃª²·¹â'", $yyyymm);
if (getUniResult($query, $l_invent) < 1) {
    $l_invent         = 0;      // ¸¡º÷¼ºÇÔ
    $lh_invent        = 0;
    $lh_invent_sagaku = 0;
} else {
    $lh_invent        = $l_invent - $s_invent - $b_invent_sagaku;
    $lh_invent_sagaku = $lh_invent;
    $lh_invent        = number_format(($lh_invent / $tani), $keta);
    $l_invent         = number_format(($l_invent / $tani), $keta);
}
    ///// Á°·î
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='Á´ÂÎ´ü¼óÃª²·¹â'", $p1_ym);
if (getUniResult($query, $p1_all_invent) < 1) {
    $p1_all_invent = 0;                     // ¸¡º÷¼ºÇÔ
} else {
    $p1_all_invent = number_format(($p1_all_invent / $tani), $keta);
}
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='¥«¥×¥é´ü¼óÃª²·¹â'", $p1_ym);
if (getUniResult($query, $p1_c_invent) < 1) {
    $p1_c_invent = 0;                       // ¸¡º÷¼ºÇÔ
} else {
    $p1_c_invent = number_format(($p1_c_invent / $tani), $keta);
}
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='µ¡¹©´ü¼óÃª²·¹â'", $p1_ym);
if (getUniResult($query, $p1_b_invent) < 1) {
    $p1_b_invent        = 0;    // ¸¡º÷¼ºÇÔ
    $p1_b_invent_sagaku = 0;
} else {
    $p1_b_invent_sagaku = $p1_b_invent;
    $p1_b_invent        = number_format(($p1_b_invent / $tani), $keta);
}
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='¥ê¥Ë¥¢´ü¼óÃª²·¹â'", $p1_ym);
if (getUniResult($query, $p1_l_invent) < 1) {
    $p1_l_invent         = 0;   // ¸¡º÷¼ºÇÔ
    $p1_lh_invent        = 0;
    $p1_lh_invent_sagaku = 0;
} else {
    $p1_lh_invent        = $p1_l_invent - $p1_s_invent - $p1_b_invent_sagaku;
    $p1_lh_invent_sagaku = $p1_lh_invent;
    $p1_lh_invent        = number_format(($p1_lh_invent / $tani), $keta);
    $p1_l_invent         = number_format(($p1_l_invent / $tani), $keta);
}
    ///// Á°Á°·î
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='Á´ÂÎ´ü¼óÃª²·¹â'", $p2_ym);
if (getUniResult($query, $p2_all_invent) < 1) {
    $p2_all_invent = 0;                     // ¸¡º÷¼ºÇÔ
} else {
    $p2_all_invent = number_format(($p2_all_invent / $tani), $keta);
}
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='¥«¥×¥é´ü¼óÃª²·¹â'", $p2_ym);
if (getUniResult($query, $p2_c_invent) < 1) {
    $p2_c_invent = 0;                       // ¸¡º÷¼ºÇÔ
} else {
    $p2_c_invent = number_format(($p2_c_invent / $tani), $keta);
}
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='µ¡¹©´ü¼óÃª²·¹â'", $p2_ym);
if (getUniResult($query, $p2_b_invent) < 1) {
    $p2_b_invent        = 0;    // ¸¡º÷¼ºÇÔ
    $p2_b_invent_sagaku = 0;
} else {
    $p2_b_invent_sagaku = $p2_b_invent;
    $p2_b_invent        = number_format(($p2_b_invent / $tani), $keta);
}
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='¥ê¥Ë¥¢´ü¼óÃª²·¹â'", $p2_ym);
if (getUniResult($query, $p2_l_invent) < 1) {
    $p2_l_invent         = 0;   // ¸¡º÷¼ºÇÔ
    $p2_lh_invent        = 0;
    $p2_lh_invent_sagaku = 0;
} else {
    $p2_lh_invent        = $p2_l_invent - $p2_s_invent - $p2_b_invent_sagaku;
    $p2_lh_invent_sagaku = $p2_lh_invent;
    $p2_lh_invent        = number_format(($p2_lh_invent / $tani), $keta);
    $p2_l_invent         = number_format(($p2_l_invent / $tani), $keta);
}
    ///// º£´üÎß·×
    /////   ´ü¼óÃª²·¹â¤ÎÎß·×¤Ï ´ü½éÇ¯·î¤Î´ü¼óÃª²·¹â¤Ë¤Ê¤ë
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='Á´ÂÎ´ü¼óÃª²·¹â'", $str_ym);
if (getUniResult($query, $rui_all_invent) < 1) {
    $rui_all_invent = 0;                    // ¸¡º÷¼ºÇÔ
} else {
    $rui_all_invent = number_format(($rui_all_invent / $tani), $keta);
}
$query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym=%d and note='¥«¥×¥é´ü¼óÃª²·¹â'", $str_ym);
if (getUniResult($query, $rui_c_invent) < 1) {
    $rui_c_invent = 0;                      // ¸¡º÷¼ºÇÔ
} else {
    $rui_c_invent = number_format(($rui_c_invent / $tani), $keta);
}
$query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym=%d and note='µ¡¹©´ü¼óÃª²·¹â'", $str_ym);
if (getUniResult($query, $rui_b_invent) < 1) {
    $rui_b_invent        = 0;   // ¸¡º÷¼ºÇÔ
    $rui_b_invent_sagaku = 0;
} else {
    $rui_b_invent_sagaku = $rui_b_invent;
    $rui_b_invent        = number_format(($rui_b_invent / $tani), $keta);
}
$query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym=%d and note='¥ê¥Ë¥¢´ü¼óÃª²·¹â'", $str_ym);
if (getUniResult($query, $rui_l_invent) < 1) {
    $rui_l_invent         = 0;  // ¸¡º÷¼ºÇÔ
    $rui_lh_invent        = 0;
    $rui_lh_invent_sagaku = 0;
} else {
    $rui_lh_invent        = $rui_l_invent - $rui_s_invent - $rui_b_invent_sagaku;
    $rui_lh_invent_sagaku = $rui_lh_invent;
    $rui_lh_invent        = number_format(($rui_lh_invent / $tani), $keta);
    $rui_l_invent         = number_format(($rui_l_invent / $tani), $keta);
}

/********** ºàÎÁÈñ(»ÅÆþ¹â) **********/
    ///// ¾¦´É
$p2_n_metarial  = 0;
$p1_n_metarial  = 0;
$n_metarial     = 0;
$rui_n_metarial = 0;
    ///// Åö·î
if ( $yyyymm >= 200911) {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='¥«¥×¥é»î½¤ºàÎÁÈñ'", $yyyymm);
    if (getUniResult($query, $sc_metarial) < 1) {
        $sc_metarial        = 0;            // ¸¡º÷¼ºÇÔ
        $sc_metarial_sagaku = 0;
        $sc_metarial_temp   = 0;
    } else {
        $sc_metarial_temp   = $sc_metarial;
        $sc_metarial_sagaku = $sc_metarial;
        $sc_metarial        = number_format(($sc_metarial / $tani), $keta);
    }
} else{
    $sc_metarial        = 0;                // ¸¡º÷¼ºÇÔ
    $sc_metarial_sagaku = 0;
    $sc_metarial_temp   = 0;
}
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='»î½¤ºàÎÁÈñ'", $yyyymm);
if (getUniResult($query, $s_metarial) < 1) {
    $s_metarial        = 0;                 // ¸¡º÷¼ºÇÔ
    $s_metarial_sagaku = 0;
} else {
    $s_metarial_sagaku = $s_metarial;
    $s_metarial        = $s_metarial + $sc_metarial_sagaku;             // ¥«¥×¥é»î½¤ºàÎÁÈñ¤ò²ÃÌ£¡Êsagaku¤Î²¼ ¥ê¥Ë¥¢¤«¤é¥Þ¥¤¥Ê¥¹¤·¤Æ¤·¤Þ¤¦°Ù¡Ë
    $s_metarial        = number_format(($s_metarial / $tani), $keta);
}
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='Á´ÂÎºàÎÁÈñ'", $yyyymm);
if (getUniResult($query, $all_metarial) < 1) {
    $all_metarial = 0;                      // ¸¡º÷¼ºÇÔ
} else {
    // 2013/11/07 ÄÉ²Ã 2013Ç¯10·îÅÙ ¶ÈÌ³°ÑÂ÷Èñ¡Ê²£ÀîÇÉ¸¯ÎÁ¡ËÄ´À°
    if ($yyyymm == 201310) {
        $all_metarial -= 1245035;
    }
    if ($yyyymm == 201311) {
        $all_metarial += 1245035;
    }
    $all_metarial = number_format(($all_metarial / $tani), $keta);
}
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='¥«¥×¥éºàÎÁÈñ'", $yyyymm);
if (getUniResult($query, $c_metarial) < 1) {
    $c_metarial = 0;                        // ¸¡º÷¼ºÇÔ
} else {
    $c_metarial = $c_metarial - $sc_metarial_sagaku;                    // ¥«¥×¥é»î½¤ºàÎÁÈñ¤ò²ÃÌ£
    // 2013/11/07 ÄÉ²Ã 2013Ç¯10·îÅÙ ¶ÈÌ³°ÑÂ÷Èñ¡Ê²£ÀîÇÉ¸¯ÎÁ¡ËÄ´À°
    if ($yyyymm == 201310) {
        $c_metarial -= 1245035;
    }
    if ($yyyymm == 201311) {
        $c_metarial += 1245035;
    }
    $c_metarial = number_format(($c_metarial / $tani), $keta);
}
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='µ¡¹©»ÅÆþ¹â'", $yyyymm);
if (getUniResult($query, $b_metarial) < 1) {
    $b_metarial        = 0;          // ¸¡º÷¼ºÇÔ
    $b_metarial_sagaku = 0;
} else {
    $b_metarial_sagaku = $b_metarial;
    $b_metarial        = number_format(($b_metarial / $tani), $keta);
}
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='¥ê¥Ë¥¢ºàÎÁÈñ'", $yyyymm);
if (getUniResult($query, $l_metarial) < 1) {
    $l_metarial         = 0 - $s_metarial_sagaku;     // ¸¡º÷¼ºÇÔ
    $lh_metarial        = 0;
    $lh_metarial_sagaku = 0;
} else {
    $lh_metarial        = $l_metarial - $s_metarial_sagaku - $b_metarial_sagaku;
    $lh_metarial_sagaku = $lh_metarial;
    $l_metarial         = $l_metarial - $s_metarial_sagaku;        // »î¸³½¤ÍýºàÎÁÈñ¤ò¥ê¥Ë¥¢¤ÎºàÎÁÈñ¤è¤ê¥Þ¥¤¥Ê¥¹
    $lh_metarial        = number_format(($lh_metarial / $tani), $keta);
    $l_metarial         = number_format(($l_metarial / $tani), $keta);
}
    ///// Á°·î
if ( $yyyymm >= 200911) {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='¥«¥×¥é»î½¤ºàÎÁÈñ'", $p1_ym);
    if (getUniResult($query, $p1_sc_metarial) < 1) {
        $p1_sc_metarial        = 0;         // ¸¡º÷¼ºÇÔ
        $p1_sc_metarial_sagaku = 0;
        $p1_sc_metarial_temp   = 0;
    } else {
        $p1_sc_metarial_temp   = $p1_sc_metarial;
        $p1_sc_metarial_sagaku = $p1_sc_metarial;
        $p1_sc_metarial        = number_format(($p1_sc_metarial / $tani), $keta);
    }
} else{
    $p1_sc_metarial        = 0;             // ¸¡º÷¼ºÇÔ
    $p1_sc_metarial_sagaku = 0;
    $p1_sc_metarial_temp   = 0;
}
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='»î½¤ºàÎÁÈñ'", $p1_ym);
if (getUniResult($query, $p1_s_metarial) < 1) {
    $p1_s_metarial        = 0;              // ¸¡º÷¼ºÇÔ
    $p1_s_metarial_sagaku = 0;
} else {
    $p1_s_metarial_sagaku = $p1_s_metarial;
    $p1_s_metarial        = $p1_s_metarial + $p1_sc_metarial_sagaku;            // ¥«¥×¥é»î½¤ºàÎÁÈñ¤ò²ÃÌ£¡Êsagaku¤Î²¼ ¥ê¥Ë¥¢¤«¤é¥Þ¥¤¥Ê¥¹¤·¤Æ¤·¤Þ¤¦°Ù¡Ë
    $p1_s_metarial        = number_format(($p1_s_metarial / $tani), $keta);
}
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='Á´ÂÎºàÎÁÈñ'", $p1_ym);
if (getUniResult($query, $p1_all_metarial) < 1) {
    $p1_all_metarial = 0;                   // ¸¡º÷¼ºÇÔ
} else {
    $p1_all_metarial = number_format(($p1_all_metarial / $tani), $keta);
}
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='¥«¥×¥éºàÎÁÈñ'", $p1_ym);
if (getUniResult($query, $p1_c_metarial) < 1) {
    $p1_c_metarial = 0;                     // ¸¡º÷¼ºÇÔ
} else {
    $p1_c_metarial = $p1_c_metarial - $p1_sc_metarial_sagaku;                   // ¥«¥×¥é»î½¤ºàÎÁÈñ¤ò²ÃÌ£
    // 2013/11/07 ÄÉ²Ã 2013Ç¯10·îÅÙ ¶ÈÌ³°ÑÂ÷Èñ¡Ê²£ÀîÇÉ¸¯ÎÁ¡ËÄ´À°
    if ($p1_ym == 201310) {
        $p1_c_metarial -= 1245035;
    }
    if ($p1_ym == 201311) {
        $p1_c_metarial += 1245035;
    }
    $p1_c_metarial = number_format(($p1_c_metarial / $tani), $keta);
}
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='µ¡¹©»ÅÆþ¹â'", $p1_ym);
if (getUniResult($query, $p1_b_metarial) < 1) {
    $p1_b_metarial        = 0;          // ¸¡º÷¼ºÇÔ
    $p1_b_metarial_sagaku = 0;
} else {
    $p1_b_metarial_sagaku = $p1_b_metarial;
    $p1_b_metarial        = number_format(($p1_b_metarial / $tani), $keta);
}
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='¥ê¥Ë¥¢ºàÎÁÈñ'", $p1_ym);
if (getUniResult($query, $p1_l_metarial) < 1) {
    $p1_l_metarial         = 0 - $p1_s_metarial_sagaku;     // ¸¡º÷¼ºÇÔ
    $p1_lh_metarial        = 0;
    $p1_lh_metarial_sagaku = 0;
} else {
    $p1_lh_metarial        = $p1_l_metarial - $p1_s_metarial_sagaku - $p1_b_metarial_sagaku;
    $p1_lh_metarial_sagaku = $p1_lh_metarial;
    $p1_l_metarial         = $p1_l_metarial - $p1_s_metarial_sagaku;        // »î¸³½¤ÍýºàÎÁÈñ¤ò¥ê¥Ë¥¢¤ÎºàÎÁÈñ¤è¤ê¥Þ¥¤¥Ê¥¹
    $p1_lh_metarial        = number_format(($p1_lh_metarial / $tani), $keta);
    $p1_l_metarial         = number_format(($p1_l_metarial / $tani), $keta);
}
    ///// Á°Á°·î
if ( $yyyymm >= 200911) {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='¥«¥×¥é»î½¤ºàÎÁÈñ'", $p2_ym);
    if (getUniResult($query, $p2_sc_metarial) < 1) {
        $p2_sc_metarial        = 0;         // ¸¡º÷¼ºÇÔ
        $p2_sc_metarial_sagaku = 0;
        $p2_sc_metarial_temp   = 0;
    } else {
        $p2_sc_metarial_temp   = $p2_sc_metarial;
        $p2_sc_metarial_sagaku = $p2_sc_metarial;
        $p2_sc_metarial        = number_format(($p2_sc_metarial / $tani), $keta);
    }
} else{
    $p2_sc_metarial        = 0;             // ¸¡º÷¼ºÇÔ
    $p2_sc_metarial_sagaku = 0;
    $p2_sc_metarial_temp   = 0;
}
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='»î½¤ºàÎÁÈñ'", $p2_ym);
if (getUniResult($query, $p2_s_metarial) < 1) {
    $p2_s_metarial        = 0;              // ¸¡º÷¼ºÇÔ
    $p2_s_metarial_sagaku = 0;
} else {
    $p2_s_metarial_sagaku = $p2_s_metarial;
    $p2_s_metarial        = $p2_s_metarial + $p2_sc_metarial_sagaku;        // ¥«¥×¥é»î½¤ºàÎÁÈñ¤ò²ÃÌ£¡Êsagaku¤Î²¼ ¥ê¥Ë¥¢¤«¤é¥Þ¥¤¥Ê¥¹¤·¤Æ¤·¤Þ¤¦°Ù¡Ë
    $p2_s_metarial        = number_format(($p2_s_metarial / $tani), $keta);
}
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='Á´ÂÎºàÎÁÈñ'", $p2_ym);
if (getUniResult($query, $p2_all_metarial) < 1) {
    $p2_all_metarial = 0;                   // ¸¡º÷¼ºÇÔ
} else {
    $p2_all_metarial = number_format(($p2_all_metarial / $tani), $keta);
}
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='¥«¥×¥éºàÎÁÈñ'", $p2_ym);
if (getUniResult($query, $p2_c_metarial) < 1) {
    $p2_c_metarial = 0;                     // ¸¡º÷¼ºÇÔ
} else {
    $p2_c_metarial = $p2_c_metarial - $p2_sc_metarial_sagaku;               // ¥«¥×¥é»î½¤ºàÎÁÈñ¤ò²ÃÌ£
    // 2013/11/07 ÄÉ²Ã 2013Ç¯10·îÅÙ ¶ÈÌ³°ÑÂ÷Èñ¡Ê²£ÀîÇÉ¸¯ÎÁ¡ËÄ´À°
    if ($p2_ym == 201310) {
        $p2_c_metarial -= 1245035;
    }
    if ($p2_ym == 201311) {
        $p2_c_metarial += 1245035;
    }
    $p2_c_metarial = number_format(($p2_c_metarial / $tani), $keta);
}
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='µ¡¹©»ÅÆþ¹â'", $p2_ym);
if (getUniResult($query, $p2_b_metarial) < 1) {
    $p2_b_metarial        = 0;          // ¸¡º÷¼ºÇÔ
    $p2_b_metarial_sagaku = 0;
} else {
    $p2_b_metarial_sagaku = $p2_b_metarial;
    $p2_b_metarial        = number_format(($p2_b_metarial / $tani), $keta);
}
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='¥ê¥Ë¥¢ºàÎÁÈñ'", $p2_ym);
if (getUniResult($query, $p2_l_metarial) < 1) {
    $p2_l_metarial         = 0 - $p2_s_metarial_sagaku;     // ¸¡º÷¼ºÇÔ
    $p2_lh_metarial        = 0;
    $p2_lh_metarial_sagaku = 0;
} else {
    $p2_lh_metarial        = $p2_l_metarial - $p2_s_metarial_sagaku - $p2_b_metarial_sagaku;
    $p2_lh_metarial_sagaku = $p2_lh_metarial;
    $p2_l_metarial         = $p2_l_metarial - $p2_s_metarial_sagaku;        // »î¸³½¤ÍýºàÎÁÈñ¤ò¥ê¥Ë¥¢¤ÎºàÎÁÈñ¤è¤ê¥Þ¥¤¥Ê¥¹
    $p2_lh_metarial        = number_format(($p2_lh_metarial / $tani), $keta);
    $p2_l_metarial         = number_format(($p2_l_metarial / $tani), $keta);
}
    ///// º£´üÎß·×
if ( $yyyymm >= 200911) {
    $query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='¥«¥×¥é»î½¤ºàÎÁÈñ'", $str_ym, $yyyymm);
    if (getUniResult($query, $rui_sc_metarial) < 1) {
        $rui_sc_metarial        = 0;        // ¸¡º÷¼ºÇÔ
        $rui_sc_metarial_sagaku = 0;
        $rui_sc_metarial_temp   = 0;
    } else {
        $rui_sc_metarial_temp   = $rui_sc_metarial;
        $rui_sc_metarial_sagaku = $rui_sc_metarial;
        $rui_sc_metarial        = number_format(($rui_sc_metarial / $tani), $keta);
    }
} else{
    $rui_sc_metarial        = 0;            // ¸¡º÷¼ºÇÔ
    $rui_sc_metarial_sagaku = 0;
    $rui_sc_metarial_temp   = 0;
}
$query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='»î½¤ºàÎÁÈñ'", $str_ym, $yyyymm);
if (getUniResult($query, $rui_s_metarial) < 1) {
    $rui_s_metarial        = 0;             // ¸¡º÷¼ºÇÔ
    $rui_s_metarial_sagaku = 0;
} else {
    $rui_s_metarial_sagaku = $rui_s_metarial;
    $rui_s_metarial        = $rui_s_metarial + $rui_sc_metarial_sagaku;         // ¥«¥×¥é»î½¤ºàÎÁÈñ¤ò²ÃÌ£¡Êsagaku¤Î²¼ ¥ê¥Ë¥¢¤«¤é¥Þ¥¤¥Ê¥¹¤·¤Æ¤·¤Þ¤¦°Ù¡Ë
    $rui_s_metarial        = number_format(($rui_s_metarial / $tani), $keta);
}
$query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='Á´ÂÎºàÎÁÈñ'", $str_ym, $yyyymm);
if (getUniResult($query, $rui_all_metarial) < 1) {
    $rui_all_metarial = 0;                  // ¸¡º÷¼ºÇÔ
} else {
    // 2013/11/07 ÄÉ²Ã 2013Ç¯10·îÅÙ ¶ÈÌ³°ÑÂ÷Èñ¡Ê²£ÀîÇÉ¸¯ÎÁ¡ËÄ´À°
    if ($yyyymm >= 201310 && $yyyymm <= 201403) {
        $rui_all_metarial -= 1245035;
    }
    if ($yyyymm >= 201311 && $yyyymm <= 201403) {
        $rui_all_metarial += 1245035;
    }
    $rui_all_metarial = number_format(($rui_all_metarial / $tani), $keta);
}
$query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='¥«¥×¥éºàÎÁÈñ'", $str_ym, $yyyymm);
if (getUniResult($query, $rui_c_metarial) < 1) {
    $rui_c_metarial = 0;                    // ¸¡º÷¼ºÇÔ
} else {
    // 2013/11/07 ÄÉ²Ã 2013Ç¯10·îÅÙ ¶ÈÌ³°ÑÂ÷Èñ¡Ê²£ÀîÇÉ¸¯ÎÁ¡ËÄ´À°
    if ($yyyymm >= 201310 && $yyyymm <= 201403) {
        $rui_c_metarial -= 1245035;
    }
    if ($yyyymm >= 201311 && $yyyymm <= 201403) {
        $rui_c_metarial += 1245035;
    }
    $rui_c_metarial = $rui_c_metarial - $rui_sc_metarial_sagaku;                // ¥«¥×¥é»î½¤ºàÎÁÈñ¤ò²ÃÌ£
    $rui_c_metarial = number_format(($rui_c_metarial / $tani), $keta);
}
$query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='µ¡¹©»ÅÆþ¹â'", $str_ym, $yyyymm);
if (getUniResult($query, $rui_b_metarial) < 1) {
    $rui_b_metarial        = 0;          // ¸¡º÷¼ºÇÔ
    $rui_b_metarial_sagaku = 0;
} else {
    $rui_b_metarial_sagaku = $rui_b_metarial;
    $rui_b_metarial        = number_format(($rui_b_metarial / $tani), $keta);
}
$query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='¥ê¥Ë¥¢ºàÎÁÈñ'", $str_ym, $yyyymm);
if (getUniResult($query, $rui_l_metarial) < 1) {
    $rui_l_metarial         = 0 - $rui_s_metarial_sagaku;   // ¸¡º÷¼ºÇÔ
    $rui_lh_metarial        = 0;
    $rui_lh_metarial_sagaku = 0;
} else {
    $rui_lh_metarial        = $rui_l_metarial - $rui_s_metarial_sagaku - $rui_b_metarial_sagaku;
    $rui_lh_metarial_sagaku = $rui_lh_metarial;
    $rui_l_metarial         = $rui_l_metarial - $rui_s_metarial_sagaku;        // »î¸³½¤ÍýºàÎÁÈñ¤ò¥ê¥Ë¥¢¤ÎºàÎÁÈñ¤è¤ê¥Þ¥¤¥Ê¥¹
    $rui_lh_metarial        = number_format(($rui_lh_metarial / $tani), $keta);
    $rui_l_metarial         = number_format(($rui_l_metarial / $tani), $keta);
}

/********** Ï«Ì³Èñ **********/
    ///// Åö·î
if ($yyyymm >= 201001) {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='»î½¤µëÍ¿ÇÛÉê³Û'", $yyyymm);
    if (getUniResult($query, $s_kyu_kei) < 1) {
        $s_kyu_kei = 0;                    // ¸¡º÷¼ºÇÔ
        $s_kyu_kin = 0;
    } else {
        $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='»î½¤µëÍ¿ÇÛÉêÎ¨'", $yyyymm);
        if (getUniResult($query, $s_kyu_kin) < 1) {
            $s_kyu_kin = 0;
        }
    }
}
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='»î½¤Ï«Ì³Èñ'", $yyyymm);
if (getUniResult($query, $s_roumu) < 1) {
    $s_roumu        = 0;                    // ¸¡º÷¼ºÇÔ
    $s_roumu_sagaku = 0;
} else {
    $s_roumu_sagaku = $s_roumu;
    if ($yyyymm == 200912) {
        $s_roumu = $s_roumu - 1409708;
    }
    if ($yyyymm >= 201001) {
        $s_roumu = $s_roumu - $s_kyu_kei + $s_kyu_kin;    // »î½¤ÇÛÉêµëÍ¿¤ò²ÃÌ£
        //$s_roumu = $s_roumu - 432323 + 129697;  // ÅºÅÄ¤µ¤ó¤ÎµëÍ¿¤òC¡¦L¤Ï35%¡£»î¸³½¤Íý¤Ë30%¿¶Ê¬
    }
    $s_roumu        = number_format(($s_roumu / $tani), $keta);
}
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='Á´ÂÎÏ«Ì³Èñ'", $yyyymm);
if (getUniResult($query, $all_roumu) < 1) {
    $all_roumu = 0;                         // ¸¡º÷¼ºÇÔ
} else {
    $all_roumu = number_format(($all_roumu / $tani), $keta);
}
if ($yyyymm >= 201001) {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='¥«¥×¥éµëÍ¿ÇÛÉêÎ¨'", $yyyymm);
    if (getUniResult($query, $c_kyu_kin) < 1) {
        $c_kyu_kin = 0;
    }
}
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='¥«¥×¥éÏ«Ì³Èñ'", $yyyymm);
if (getUniResult($query, $c_roumu) < 1) {
    $c_roumu = 0;                           // ¸¡º÷¼ºÇÔ
} else {
    if ($yyyymm == 200912) {
        $c_roumu = $c_roumu + 1227429;
    }
    if ($yyyymm >= 201001) {
        $c_roumu = $c_roumu + $c_kyu_kin;   // ¥«¥×¥éÇÛÉêµëÍ¿¤ò²ÃÌ£
        //$c_roumu = $c_roumu + 151313;   // ÅºÅÄ¤µ¤ó¤ÎµëÍ¿¤òC¡¦L¤Ï35%¡£»î¸³½¤Íý¤Ë30%¿¶Ê¬
    }
    //$c_roumu = number_format(($c_roumu / $tani), $keta);
}
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='µ¡¹©Ï«Ì³Èñ'", $yyyymm);
if (getUniResult($query, $b_roumu) < 1) {
    $b_roumu        = 0;    // ¸¡º÷¼ºÇÔ
    $b_roumu_sagaku = 0;
} else {
    $b_roumu_sagaku = $b_roumu;
    $b_roumu        = number_format(($b_roumu / $tani), $keta);
}
if ($yyyymm >= 201001) {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='¥ê¥Ë¥¢µëÍ¿ÇÛÉêÎ¨'", $yyyymm);
    if (getUniResult($query, $l_kyu_kin) < 1) {
        $l_kyu_kin = 0;
    }
}
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='¥ê¥Ë¥¢Ï«Ì³Èñ'", $yyyymm);
if (getUniResult($query, $l_roumu) < 1) {
    $l_roumu         = 0 - $s_roumu_sagaku;     // ¸¡º÷¼ºÇÔ]
    $lh_roumu        = 0;
    $lh_roumu_sagaku = 0;
} else {
    if ($yyyymm == 200912) {
        $l_roumu = $l_roumu + 182279;
    }
    if ($yyyymm >= 201001) {
        $l_roumu = $l_roumu + $l_kyu_kin;   // ¥ê¥Ë¥¢ÇÛÉêµëÍ¿¤ò²ÃÌ£(Á´¤ÆÉ¸½à)
        //$l_roumu = $l_roumu + 151313;   // ÅºÅÄ¤µ¤ó¤ÎµëÍ¿¤òC¡¦L¤Ï35%¡£»î¸³½¤Íý¤Ë30%¿¶Ê¬
    }
    if ($yyyymm == 201408) {
        $l_roumu = $l_roumu + 229464;
    }
    $lh_roumu        = $l_roumu - $s_roumu_sagaku - $b_roumu_sagaku;
    $lh_roumu_sagaku = $lh_roumu;
    $l_roumu         = $l_roumu - $s_roumu_sagaku;               // »î¸³½¤ÍýÏ«Ì³Èñ¤ò¥ê¥Ë¥¢¤ÎÏ«Ì³Èñ¤è¤ê¥Þ¥¤¥Ê¥¹
    $lh_roumu        = number_format(($lh_roumu / $tani), $keta);
    $l_roumu         = number_format(($l_roumu / $tani), $keta);
}
// ²¼¤Ï7·îÌ¤Ê§¤¤µëÍ¿Ê¬
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='¾¦´ÉÏ«Ì³Èñ'", $yyyymm);
if (getUniResult($query, $n_roumu_sagaku) < 1) {
    $n_roumu_sagaku = 0;                    // ¸¡º÷¼ºÇÔ
}
    ///// Åö·î ¾¦´É
$query = sprintf("select sum(orign_kin) from act_allo_history where pl_bs_ym=%d and actcod>=8101 and orign_id=580", $yyyymm);
if (getUniResult($query, $n_roumu) < 1) {
    $n_roumu  = 0 + $n_roumu_sagaku;        // ¸¡º÷¼ºÇÔ
    $n_urigen = $n_roumu;
    $n_sagaku = $n_roumu;                   // ¥«¥×¥éº¹³Û·×»»ÍÑ
} else {
    $n_roumu  = $n_roumu + $n_roumu_sagaku;
    $n_urigen = $n_roumu;
    $c_roumu  = $c_roumu - $n_roumu;        // ¥«¥×¥éÏ«Ì³Èñ¡Ý¾¦´ÉÏ«Ì³Èñ
    if ($yyyymm == 201408) {
        $c_roumu = $c_roumu + 611904;
        $n_roumu = $n_roumu - 841368;
    }
    $n_sagaku = $n_roumu;                   // ¥«¥×¥éº¹³Û·×»»ÍÑ
    $c_roumu  = number_format(($c_roumu / $tani), $keta);
    $n_roumu  = number_format(($n_roumu / $tani), $keta);
}

    ///// Á°·î
if ($p1_ym >= 201001) {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='»î½¤µëÍ¿ÇÛÉê³Û'", $p1_ym);
    if (getUniResult($query, $p1_s_kyu_kei) < 1) {
        $p1_s_kyu_kei = 0;                    // ¸¡º÷¼ºÇÔ
        $p1_s_kyu_kin = 0;
    } else {
        $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='»î½¤µëÍ¿ÇÛÉêÎ¨'", $p1_ym);
        if (getUniResult($query, $p1_s_kyu_kin) < 1) {
            $p1_s_kyu_kin = 0;
        }
    }
}
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='»î½¤Ï«Ì³Èñ'", $p1_ym);
if (getUniResult($query, $p1_s_roumu) < 1) {
    $p1_s_roumu        = 0;                 // ¸¡º÷¼ºÇÔ
    $p1_s_roumu_sagaku = 0;
} else {
    $p1_s_roumu_sagaku = $p1_s_roumu;
    if ($p1_ym == 200912) {
        $p1_s_roumu = $p1_s_roumu - 1409708;
    }
    if ($p1_ym >= 201001) {
        $p1_s_roumu = $p1_s_roumu - $p1_s_kyu_kei + $p1_s_kyu_kin;    // »î½¤ÇÛÉêµëÍ¿¤ò²ÃÌ£
        //$p1_s_roumu = $p1_s_roumu - 432323 + 129697;    // ÅºÅÄ¤µ¤ó¤ÎµëÍ¿¤òC¡¦L¤Ï35%¡£»î¸³½¤Íý¤Ë30%¿¶Ê¬
    }
    $p1_s_roumu        = number_format(($p1_s_roumu / $tani), $keta);
}
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='¾¦´ÉÏ«Ì³Èñ'", $p1_ym);
if (getUniResult($query, $p1_n_roumu_sagaku) < 1) {
    $p1_n_roumu_sagaku = 0;                 // ¸¡º÷¼ºÇÔ
}
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='Á´ÂÎÏ«Ì³Èñ'", $p1_ym);
if (getUniResult($query, $p1_all_roumu) < 1) {
    $p1_all_roumu = 0;                      // ¸¡º÷¼ºÇÔ
} else {
    $p1_all_roumu = number_format(($p1_all_roumu / $tani), $keta);
}
if ($p1_ym >= 201001) {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='¥«¥×¥éµëÍ¿ÇÛÉêÎ¨'", $p1_ym);
    if (getUniResult($query, $p1_c_kyu_kin) < 1) {
        $p1_c_kyu_kin = 0;
    }
}
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='¥«¥×¥éÏ«Ì³Èñ'", $p1_ym);
if (getUniResult($query, $p1_c_roumu) < 1) {
    $p1_c_roumu = 0;                        // ¸¡º÷¼ºÇÔ
} else {
    if ($p1_ym == 200912) {
        $p1_c_roumu = $p1_c_roumu + 1227429;
    }
    if ($p1_ym >= 201001) {
        $p1_c_roumu = $p1_c_roumu + $p1_c_kyu_kin;   // ¥«¥×¥éÇÛÉêµëÍ¿¤ò²ÃÌ£
        //$p1_c_roumu = $p1_c_roumu + 151313; // ÅºÅÄ¤µ¤ó¤ÎµëÍ¿¤òC¡¦L¤Ï35%¡£»î¸³½¤Íý¤Ë30%¿¶Ê¬
    }
    //$p1_c_roumu = number_format(($p1_c_roumu / $tani), $keta);
}
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='µ¡¹©Ï«Ì³Èñ'", $p1_ym);
if (getUniResult($query, $p1_b_roumu) < 1) {
    $p1_b_roumu        = 0;    // ¸¡º÷¼ºÇÔ
    $p1_b_roumu_sagaku = 0;
} else {
    $p1_b_roumu_sagaku = $p1_b_roumu;
    $p1_b_roumu        = number_format(($p1_b_roumu / $tani), $keta);
}
if ($p1_ym >= 201001) {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='¥ê¥Ë¥¢µëÍ¿ÇÛÉêÎ¨'", $p1_ym);
    if (getUniResult($query, $p1_l_kyu_kin) < 1) {
        $p1_l_kyu_kin = 0;
    }
}
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='¥ê¥Ë¥¢Ï«Ì³Èñ'", $p1_ym);
if (getUniResult($query, $p1_l_roumu) < 1) {
    $p1_l_roumu         = 0 - $p1_s_roumu_sagaku;     // ¸¡º÷¼ºÇÔ
    $p1_lh_roumu        = 0;
    $p1_lh_roumu_sagaku = 0;
} else {
    if ($p1_ym == 200912) {
        $p1_l_roumu = $p1_l_roumu + 182279;
    }
    if ($p1_ym >= 201001) {
        $p1_l_roumu = $p1_l_roumu + $p1_l_kyu_kin;   // ¥ê¥Ë¥¢ÇÛÉêµëÍ¿¤ò²ÃÌ£(Á´¤ÆÉ¸½à)
        //$p1_l_roumu = $p1_l_roumu + 151313; // ÅºÅÄ¤µ¤ó¤ÎµëÍ¿¤òC¡¦L¤Ï35%¡£»î¸³½¤Íý¤Ë30%¿¶Ê¬
    }
    if ($p1_ym == 201408) {
        $p1_l_roumu = $p1_l_roumu + 229464;
    }
    $p1_lh_roumu        = $p1_l_roumu - $p1_s_roumu_sagaku - $p1_b_roumu_sagaku;
    $p1_lh_roumu_sagaku = $p1_lh_roumu;
    $p1_l_roumu         = $p1_l_roumu - $p1_s_roumu_sagaku;               // »î¸³½¤ÍýÏ«Ì³Èñ¤ò¥ê¥Ë¥¢¤ÎÏ«Ì³Èñ¤è¤ê¥Þ¥¤¥Ê¥¹
    $p1_lh_roumu        = number_format(($p1_lh_roumu / $tani), $keta);
    $p1_l_roumu         = number_format(($p1_l_roumu / $tani), $keta);
}
    ///// Á°·î ¾¦´É
$query = sprintf("select sum(orign_kin) from act_allo_history where pl_bs_ym=%d and actcod>=8101 and orign_id=580", $p1_ym);
if (getUniResult($query, $p1_n_roumu) < 1) {
    $p1_n_roumu  = 0 + $p1_n_roumu_sagaku;      // ¸¡º÷¼ºÇÔ
    $p1_n_urigen = $p1_n_roumu;
    $p1_n_sagaku = $p1_n_roumu;                 // ¥«¥×¥éº¹³Û·×»»ÍÑ
} else {
    $p1_n_roumu  = $p1_n_roumu + $p1_n_roumu_sagaku;
    $p1_n_urigen = $p1_n_roumu;
    $p1_c_roumu  = $p1_c_roumu - $p1_n_roumu;   // ¥«¥×¥éÏ«Ì³Èñ¡Ý¾¦´ÉÏ«Ì³Èñ
    if ($p1_ym == 201408) {
        $p1_c_roumu = $p1_c_roumu + 611904;
        $p1_n_roumu = $p1_n_roumu - 841368;
    }
    $p1_n_sagaku = $p1_n_roumu;                 // ¥«¥×¥éº¹³Û·×»»ÍÑ
    $p1_c_roumu  = number_format(($p1_c_roumu / $tani), $keta);
    $p1_n_roumu  = number_format(($p1_n_roumu / $tani), $keta);
}

    ///// Á°Á°·î
if ($p2_ym >= 201001) {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='»î½¤µëÍ¿ÇÛÉê³Û'", $p2_ym);
    if (getUniResult($query, $p2_s_kyu_kei) < 1) {
        $p2_s_kyu_kei = 0;                    // ¸¡º÷¼ºÇÔ
        $p2_s_kyu_kin = 0;
    } else {
        $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='»î½¤µëÍ¿ÇÛÉêÎ¨'", $p2_ym);
        if (getUniResult($query, $p2_s_kyu_kin) < 1) {
            $p2_s_kyu_kin = 0;
        }
    }
}
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='»î½¤Ï«Ì³Èñ'", $p2_ym);
if (getUniResult($query, $p2_s_roumu) < 1) {
    $p2_s_roumu        = 0;                     // ¸¡º÷¼ºÇÔ
    $p2_s_roumu_sagaku = 0;
} else {
    $p2_s_roumu_sagaku = $p2_s_roumu;
    if ($p2_ym == 200912) {
        $p2_s_roumu = $p2_s_roumu - 1409708;
    }
    if ($p2_ym >= 201001) {
        $p2_s_roumu = $p2_s_roumu - $p2_s_kyu_kei + $p2_s_kyu_kin;    // »î½¤ÇÛÉêµëÍ¿¤ò²ÃÌ£
        //$p2_s_roumu = $p2_s_roumu - 432323 + 129697;    // ÅºÅÄ¤µ¤ó¤ÎµëÍ¿¤òC¡¦L¤Ï35%¡£»î¸³½¤Íý¤Ë30%¿¶Ê¬
    }
    $p2_s_roumu        = number_format(($p2_s_roumu / $tani), $keta);
}
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='¾¦´ÉÏ«Ì³Èñ'", $p2_ym);
if (getUniResult($query, $p2_n_roumu_sagaku) < 1) {
    $p2_n_roumu_sagaku = 0;                     // ¸¡º÷¼ºÇÔ
}
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='Á´ÂÎÏ«Ì³Èñ'", $p2_ym);
if (getUniResult($query, $p2_all_roumu) < 1) {
    $p2_all_roumu = 0;                          // ¸¡º÷¼ºÇÔ
} else {
    $p2_all_roumu = number_format(($p2_all_roumu / $tani), $keta);
}
if ($p2_ym >= 201001) {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='¥«¥×¥éµëÍ¿ÇÛÉêÎ¨'", $p2_ym);
    if (getUniResult($query, $p2_c_kyu_kin) < 1) {
        $p2_c_kyu_kin = 0;
    }
}
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='¥«¥×¥éÏ«Ì³Èñ'", $p2_ym);
if (getUniResult($query, $p2_c_roumu) < 1) {
    $p2_c_roumu = 0;                            // ¸¡º÷¼ºÇÔ
} else {
    if ($p2_ym == 200912) {
        $p2_c_roumu = $p2_c_roumu + 1227429;
    }
    if ($p2_ym >= 201001) {
        $p2_c_roumu = $p2_c_roumu + $p2_c_kyu_kin;   // ¥«¥×¥éÇÛÉêµëÍ¿¤ò²ÃÌ£
        //$p2_c_roumu = $p2_c_roumu + 151313;    // ÅºÅÄ¤µ¤ó¤ÎµëÍ¿¤òC¡¦L¤Ï35%¡£»î¸³½¤Íý¤Ë30%¿¶Ê¬
    }
    //$p2_c_roumu = number_format(($p2_c_roumu / $tani), $keta);
}
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='µ¡¹©Ï«Ì³Èñ'", $p2_ym);
if (getUniResult($query, $p2_b_roumu) < 1) {
    $p2_b_roumu        = 0;    // ¸¡º÷¼ºÇÔ
    $p2_b_roumu_sagaku = 0;
} else {
    $p2_b_roumu_sagaku = $p2_b_roumu;
    $p2_b_roumu        = number_format(($p2_b_roumu / $tani), $keta);
}
if ($p2_ym >= 201001) {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='¥ê¥Ë¥¢µëÍ¿ÇÛÉêÎ¨'", $p2_ym);
    if (getUniResult($query, $p2_l_kyu_kin) < 1) {
        $p2_l_kyu_kin = 0;
    }
}
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='¥ê¥Ë¥¢Ï«Ì³Èñ'", $p2_ym);
if (getUniResult($query, $p2_l_roumu) < 1) {
    $p2_l_roumu         = 0 - $p2_s_roumu_sagaku;     // ¸¡º÷¼ºÇÔ
    $p2_lh_roumu        = 0;
    $p2_lh_roumu_sagaku = 0;
} else {
    if ($p2_ym == 200912) {
        $p2_l_roumu = $p2_l_roumu + 182279;
    }
    if ($p2_ym >= 201001) {
        $p2_l_roumu = $p2_l_roumu + $p2_l_kyu_kin;   // ¥ê¥Ë¥¢ÇÛÉêµëÍ¿¤ò²ÃÌ£(Á´¤ÆÉ¸½à)
        //$p2_l_roumu = $p2_l_roumu + 151313;     // ÅºÅÄ¤µ¤ó¤ÎµëÍ¿¤òC¡¦L¤Ï35%¡£»î¸³½¤Íý¤Ë30%¿¶Ê¬
    }
    if ($p2_ym == 201408) {
        $p2_l_roumu = $p2_l_roumu + 229464;
    }
    $p2_lh_roumu        = $p2_l_roumu - $p2_s_roumu_sagaku - $p2_b_roumu_sagaku;
    $p2_lh_roumu_sagaku = $p2_lh_roumu;
    $p2_l_roumu         = $p2_l_roumu - $p2_s_roumu_sagaku;               // »î¸³½¤ÍýÏ«Ì³Èñ¤ò¥ê¥Ë¥¢¤ÎÏ«Ì³Èñ¤è¤ê¥Þ¥¤¥Ê¥¹
    $p2_lh_roumu        = number_format(($p2_lh_roumu / $tani), $keta);
    $p2_l_roumu         = number_format(($p2_l_roumu / $tani), $keta);
}
    ///// Á°Á°·î ¾¦´É
$query = sprintf("select sum(orign_kin) from act_allo_history where pl_bs_ym=%d and actcod>=8101 and orign_id=580", $p2_ym);
if (getUniResult($query, $p2_n_roumu) < 1) {
    $p2_n_roumu  = 0 + $p2_n_roumu_sagaku;      // ¸¡º÷¼ºÇÔ
    $p2_n_urigen = $p2_n_roumu;
    $p2_n_sagaku = $p2_n_roumu;                 // ¥«¥×¥éº¹³Û·×»»ÍÑ
} else {
    $p2_n_roumu  = $p2_n_roumu + $p2_n_roumu_sagaku;
    $p2_n_urigen = $p2_n_roumu;
    $p2_c_roumu  = $p2_c_roumu - $p2_n_roumu;   // ¥«¥×¥éÏ«Ì³Èñ¡Ý¾¦´ÉÏ«Ì³Èñ
    if ($p2_ym == 201408) {
        $p2_c_roumu = $p2_c_roumu + 611904;
        $p2_n_roumu = $p2_n_roumu - 841368;
    }
    $p2_n_sagaku = $p2_n_roumu;                 // ¥«¥×¥éº¹³Û·×»»ÍÑ
    $p2_c_roumu  = number_format(($p2_c_roumu / $tani), $keta);
    $p2_n_roumu  = number_format(($p2_n_roumu / $tani), $keta);
}

    ///// º£´üÎß·×
if ($yyyymm >= 201001) {
    $query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='»î½¤µëÍ¿ÇÛÉê³Û'", $str_ym, $yyyymm);
    if (getUniResult($query, $rui_s_kyu_kei) < 1) {
        $rui_s_kyu_kei = 0;                    // ¸¡º÷¼ºÇÔ
        $rui_s_kyu_kin = 0;
    } else {
        $query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='»î½¤µëÍ¿ÇÛÉêÎ¨'", $str_ym, $yyyymm);
        if (getUniResult($query, $rui_s_kyu_kin) < 1) {
            $rui_s_kyu_kin = 0;
        }
    }
}
$query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='»î½¤Ï«Ì³Èñ'", $str_ym, $yyyymm);
if (getUniResult($query, $rui_s_roumu) < 1) {
    $rui_s_roumu        = 0;                    // ¸¡º÷¼ºÇÔ
    $rui_s_roumu_sagaku = 0;
} else {
    $rui_s_roumu_sagaku = $rui_s_roumu;
    if ($yyyymm >= 200912 && $yyyymm <= 201003) {
        $rui_s_roumu = $rui_s_roumu - 1409708;
    }
    if ($yyyymm >= 201001) {
        $rui_s_roumu = $rui_s_roumu - $rui_s_kyu_kei + $rui_s_kyu_kin;    // »î½¤ÇÛÉêµëÍ¿¤ò²ÃÌ£
        //$rui_s_roumu = $rui_s_roumu - 432323 + 129697;  // ÅºÅÄ¤µ¤ó¤ÎµëÍ¿¤òC¡¦L¤Ï35%¡£»î¸³½¤Íý¤Ë30%¿¶Ê¬
    }
    $rui_s_roumu        = number_format(($rui_s_roumu / $tani), $keta);
}
$query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='¾¦´ÉÏ«Ì³Èñ'", $str_ym, $yyyymm);
if (getUniResult($query, $rui_n_roumu_sagaku) < 1) {
    $rui_n_roumu_sagaku = 0;                    // ¸¡º÷¼ºÇÔ
}
$query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='Á´ÂÎÏ«Ì³Èñ'", $str_ym, $yyyymm);
if (getUniResult($query, $rui_all_roumu) < 1) {
    $rui_all_roumu = 0;                         // ¸¡º÷¼ºÇÔ
} else {
    $rui_all_roumu = number_format(($rui_all_roumu / $tani), $keta);
}
if ($yyyymm >= 201001) {
    $query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='¥«¥×¥éµëÍ¿ÇÛÉêÎ¨'", $str_ym, $yyyymm);
    if (getUniResult($query, $rui_c_kyu_kin) < 1) {
        $rui_c_kyu_kin = 0;
    }
}

$query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='¥«¥×¥éÏ«Ì³Èñ'", $str_ym, $yyyymm);
if (getUniResult($query, $rui_c_roumu) < 1) {
    $rui_c_roumu = 0;                           // ¸¡º÷¼ºÇÔ
} else {
    if ($yyyymm >= 200912 && $yyyymm <= 201003) {
        $rui_c_roumu = $rui_c_roumu + 1227429;
    }
    if ($yyyymm >= 201001) {
        $rui_c_roumu = $rui_c_roumu + $rui_c_kyu_kin;   // ¥«¥×¥éÇÛÉêµëÍ¿¤ò²ÃÌ£
        //$rui_c_roumu = $rui_c_roumu + 151313;   // ÅºÅÄ¤µ¤ó¤ÎµëÍ¿¤òC¡¦L¤Ï35%¡£»î¸³½¤Íý¤Ë30%¿¶Ê¬
    }
    //$rui_c_roumu = number_format(($rui_c_roumu / $tani), $keta);
}
$query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='µ¡¹©Ï«Ì³Èñ'", $str_ym, $yyyymm);
if (getUniResult($query, $rui_b_roumu) < 1) {
    $rui_b_roumu        = 0;    // ¸¡º÷¼ºÇÔ
    $rui_b_roumu_sagaku = 0;
} else {
    $rui_b_roumu_sagaku = $rui_b_roumu;
    $rui_b_roumu        = number_format(($rui_b_roumu / $tani), $keta);
}
if ($yyyymm >= 201001) {
    $query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='¥ê¥Ë¥¢µëÍ¿ÇÛÉêÎ¨'", $str_ym, $yyyymm);
    if (getUniResult($query, $rui_l_kyu_kin) < 1) {
        $rui_l_kyu_kin = 0;
    }
}
$query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='¥ê¥Ë¥¢Ï«Ì³Èñ'", $str_ym, $yyyymm);
if (getUniResult($query, $rui_l_roumu) < 1) {
    $rui_l_roumu         = 0 - $rui_s_roumu_sagaku;   // ¸¡º÷¼ºÇÔ
    $rui_lh_roumu        = 0;
    $rui_lh_roumu_sagaku = 0;
} else {
    if ($yyyymm >= 200912 && $yyyymm <= 201003) {
        $rui_l_roumu = $rui_l_roumu + 182279;
    }
    if ($yyyymm >= 201001) {
        $rui_l_roumu = $rui_l_roumu + $rui_l_kyu_kin;   // ¥ê¥Ë¥¢ÇÛÉêµëÍ¿¤ò²ÃÌ£(Á´¤ÆÉ¸½à)
        //$rui_l_roumu = $rui_l_roumu + 151313;   // ÅºÅÄ¤µ¤ó¤ÎµëÍ¿¤òC¡¦L¤Ï35%¡£»î¸³½¤Íý¤Ë30%¿¶Ê¬
    }
    if ($yyyymm >= 201408 && $yyyymm <= 201503) {
        $rui_l_roumu = $rui_l_roumu + 229464;
    }
    $rui_lh_roumu        = $rui_l_roumu - $rui_s_roumu_sagaku - $rui_b_roumu_sagaku;
    $rui_lh_roumu_sagaku = $rui_lh_roumu;
    $rui_l_roumu         = $rui_l_roumu - $rui_s_roumu_sagaku;               // »î¸³½¤ÍýÏ«Ì³Èñ¤ò¥ê¥Ë¥¢¤ÎÏ«Ì³Èñ¤è¤ê¥Þ¥¤¥Ê¥¹
    $rui_lh_roumu        = number_format(($rui_lh_roumu / $tani), $keta);
    $rui_l_roumu         = number_format(($rui_l_roumu / $tani), $keta);
}
    ///// º£´üÎß·× ¾¦´É
$query = sprintf("select sum(orign_kin) from act_allo_history where pl_bs_ym>=%d and pl_bs_ym<=%d and actcod>=8101 and orign_id=580", $str_ym, $yyyymm);
if (getUniResult($query, $rui_n_roumu) < 1) {
    $rui_n_roumu  = 0 + $rui_n_roumu_sagaku;        // ¸¡º÷¼ºÇÔ
    $rui_n_urigen = $rui_n_roumu;
} else {
    // ²¼¤Ï7·îÌ¤Ê§¤¤µìÍ¾Ê¬ÄÉ²Ã ¥Æ¥¹¥ÈÍÑ
    $rui_n_roumu  = $rui_n_roumu + $rui_n_roumu_sagaku;
    $rui_n_urigen = $rui_n_roumu;
    $rui_c_roumu  = $rui_c_roumu - $rui_n_roumu;    // ¥«¥×¥éÏ«Ì³Èñ¡Ý¾¦´ÉÏ«Ì³Èñ
    if ($yyyymm >= 201408 && $yyyymm <= 201503) {
        $rui_c_roumu = $rui_c_roumu + 611904;
        $rui_n_roumu = $rui_n_roumu - 841368;
    }
    $rui_n_sagaku = $rui_n_roumu;                   // ¥«¥×¥éº¹³Û·×»»ÍÑ
    $rui_c_roumu  = number_format(($rui_c_roumu / $tani), $keta);
    $rui_n_roumu  = number_format(($rui_n_roumu / $tani), $keta);
}

/********** ·ÐÈñ(À½Â¤·ÐÈñ) **********/
    ///// Åö·î
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='»î½¤À½Â¤·ÐÈñ'", $yyyymm);
if (getUniResult($query, $s_expense) < 1) {
    $s_expense        = 0;                          // ¸¡º÷¼ºÇÔ
    $s_expense_sagaku = 0;
} else {
    $s_expense_sagaku = $s_expense;
    $s_expense        = number_format(($s_expense / $tani), $keta);
}
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='¾¦´ÉÀ½Â¤·ÐÈñ'", $yyyymm);
if (getUniResult($query, $n_expense_sagaku) < 1) {
    $n_expense_sagaku = 0;                          // ¸¡º÷¼ºÇÔ
}
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='Á´ÂÎÀ½Â¤·ÐÈñ'", $yyyymm);
if (getUniResult($query, $all_expense) < 1) {
    $all_expense = 0;                               // ¸¡º÷¼ºÇÔ
} else {
    // 2012/02/08 ÄÉ²Ã 2012Ç¯1·îÅÙ ¶ÈÌ³°ÑÂ÷Èñ¡ÊÊ¿½Ð²£ÀîÇÉ¸¯ÎÁ¡ËÄ´À°
    if ($yyyymm == 201201) {
        $all_expense +=1156130;
    }
    // 2012/03/05 ÄÉ²Ã 2012Ç¯1·îÅÙ ¶ÈÌ³°ÑÂ÷Èñ¡ÊÊ¿½Ð²£ÀîÇÉ¸¯ÎÁ¡ËÄ´À° Ìá¤·
    if ($yyyymm == 201202) {
        $all_expense -=1156130;
    }
    // 2013/11/07 ÄÉ²Ã 2013Ç¯10·îÅÙ ¶ÈÌ³°ÑÂ÷Èñ¡Ê²£ÀîÇÉ¸¯ÎÁ¡ËÄ´À°
    if ($yyyymm == 201310) {
        $all_expense += 1245035;
    }
    if ($yyyymm == 201311) {
        $all_expense -= 1245035;
    }
    $all_expense = number_format(($all_expense / $tani), $keta);
}
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='¥«¥×¥éÀ½Â¤·ÐÈñ'", $yyyymm);
if (getUniResult($query, $c_expense) < 1) {
    $c_expense = 0;                                 // ¸¡º÷¼ºÇÔ
} else {
    //$c_expense = number_format(($c_expense / $tani), $keta);
}
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='µ¡¹©À½Â¤·ÐÈñ'", $yyyymm);
if (getUniResult($query, $b_expense) < 1) {
    $b_expense        = 0;    // ¸¡º÷¼ºÇÔ
    $b_expense_sagaku = 0;
} else {
    $b_expense_sagaku = $b_expense;
    $b_expense        = number_format(($b_expense / $tani), $keta);
}
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='¥ê¥Ë¥¢À½Â¤·ÐÈñ'", $yyyymm);
if (getUniResult($query, $l_expense) < 1) {
    $l_expense         = 0 - $s_expense_sagaku;     // ¸¡º÷¼ºÇÔ
    $lh_expense        = 0;
    $lh_expense_sagaku = 0;
} else {
    // 2012/02/08 ÄÉ²Ã 2012Ç¯1·îÅÙ ¶ÈÌ³°ÑÂ÷Èñ¡ÊÊ¿½Ð²£ÀîÇÉ¸¯ÎÁ¡ËÄ´À°
    if ($yyyymm == 201201) {
        $l_expense +=1156130;
    }
    // 2012/03/05 ÄÉ²Ã 2012Ç¯1·îÅÙ ¶ÈÌ³°ÑÂ÷Èñ¡ÊÊ¿½Ð²£ÀîÇÉ¸¯ÎÁ¡ËÄ´À° Ìá¤·
    if ($yyyymm == 201202) {
        $l_expense -=1156130;
    }
    $lh_expense        = $l_expense - $s_expense_sagaku - $b_expense_sagaku;
    $lh_expense_sagaku = $lh_expense;
    $l_expense         = $l_expense - $s_expense_sagaku;               // »î¸³½¤ÍýÀ½Â¤·ÐÈñ¤ò¥ê¥Ë¥¢¤ÎÀ½Â¤·ÐÈñ¤è¤ê¥Þ¥¤¥Ê¥¹
    $lh_expense        = number_format(($lh_expense / $tani), $keta);
    $l_expense         = number_format(($l_expense / $tani), $keta);
}
    ///// Åö·î ¾¦´É
$query = sprintf("select sum(orign_kin) from act_allo_history where pl_bs_ym=%d and actcod>=7501 and actcod<=8000 and orign_id=580", $yyyymm);
if (getUniResult($query, $n_expense) < 1) {
    $n_expense = 0 + $n_roumu_sagaku;               // ¸¡º÷¼ºÇÔ
    $n_urigen  = $n_urigen + $n_expense;
    $n_sagaku  = $n_sagaku + $n_expense;            // ¥«¥×¥éº¹³Û·×»»ÍÑ
} else {
    $n_expense = $n_expense + $n_expense_sagaku;
    $n_urigen  = $n_urigen + $n_expense;
    $c_expense = $c_expense - $n_expense;           // ¥«¥×¥éÀ½Â¤·ÐÈñ¡Ý¾¦´ÉÀ½Â¤·ÐÈñ
    $n_sagaku  = $n_sagaku + $n_expense;            // ¥«¥×¥éº¹³Û·×»»ÍÑ
    // 2013/11/07 ÄÉ²Ã 2013Ç¯10·îÅÙ ¶ÈÌ³°ÑÂ÷Èñ¡Ê²£ÀîÇÉ¸¯ÎÁ¡ËÄ´À°
    if ($yyyymm == 201310) {
        $n_expense += 1245035;
    }
    if ($yyyymm == 201311) {
        $n_expense -= 1245035;
    }
    $c_expense = number_format(($c_expense / $tani), $keta);
    $n_expense = number_format(($n_expense / $tani), $keta);
}

    ///// Á°·î
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='»î½¤À½Â¤·ÐÈñ'", $p1_ym);
if (getUniResult($query, $p1_s_expense) < 1) {
    $p1_s_expense        = 0;                       // ¸¡º÷¼ºÇÔ
    $p1_s_expense_sagaku = 0;
} else {
    $p1_s_expense_sagaku = $p1_s_expense;
    $p1_s_expense        = number_format(($p1_s_expense / $tani), $keta);
}
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='¾¦´ÉÀ½Â¤·ÐÈñ'", $p1_ym);
if (getUniResult($query, $p1_n_expense_sagaku) < 1) {
    $p1_n_expense_sagaku = 0;                       // ¸¡º÷¼ºÇÔ
}
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='Á´ÂÎÀ½Â¤·ÐÈñ'", $p1_ym);
if (getUniResult($query, $p1_all_expense) < 1) {
    $p1_all_expense = 0;                            // ¸¡º÷¼ºÇÔ
} else {
    // 2012/02/08 ÄÉ²Ã 2012Ç¯1·îÅÙ ¶ÈÌ³°ÑÂ÷Èñ¡ÊÊ¿½Ð²£ÀîÇÉ¸¯ÎÁ¡ËÄ´À°
    if ($p1_ym == 201201) {
        $p1_all_expense +=1156130;
    }
    // 2012/03/05 ÄÉ²Ã 2012Ç¯1·îÅÙ ¶ÈÌ³°ÑÂ÷Èñ¡ÊÊ¿½Ð²£ÀîÇÉ¸¯ÎÁ¡ËÄ´À° Ìá¤·
    if ($p1_ym == 201202) {
        $p1_all_expense -=1156130;
    }
    $p1_all_expense = number_format(($p1_all_expense / $tani), $keta);
}
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='¥«¥×¥éÀ½Â¤·ÐÈñ'", $p1_ym);
if (getUniResult($query, $p1_c_expense) < 1) {
    $p1_c_expense = 0;                              // ¸¡º÷¼ºÇÔ
} else {
    //$p1_c_expense = number_format(($p1_c_expense / $tani), $keta);
}
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='µ¡¹©À½Â¤·ÐÈñ'", $p1_ym);
if (getUniResult($query, $p1_b_expense) < 1) {
    $p1_b_expense        = 0;    // ¸¡º÷¼ºÇÔ
    $p1_b_expense_sagaku = 0;
} else {
    $p1_b_expense_sagaku = $p1_b_expense;
    $p1_b_expense        = number_format(($p1_b_expense / $tani), $keta);
}
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='¥ê¥Ë¥¢À½Â¤·ÐÈñ'", $p1_ym);
if (getUniResult($query, $p1_l_expense) < 1) {
    $p1_l_expense         = 0 - $p1_s_expense_sagaku;     // ¸¡º÷¼ºÇÔ
    $p1_lh_expense        = 0;
    $p1_lh_expense_sagaku = 0;
} else {
    // 2012/02/08 ÄÉ²Ã 2012Ç¯1·îÅÙ ¶ÈÌ³°ÑÂ÷Èñ¡ÊÊ¿½Ð²£ÀîÇÉ¸¯ÎÁ¡ËÄ´À°
    if ($p1_ym == 201201) {
        $p1_l_expense +=1156130;
    }
    // 2012/03/05 ÄÉ²Ã 2012Ç¯1·îÅÙ ¶ÈÌ³°ÑÂ÷Èñ¡ÊÊ¿½Ð²£ÀîÇÉ¸¯ÎÁ¡ËÄ´À° Ìá¤·
    if ($p1_ym == 201202) {
        $p1_l_expense -=1156130;
    }
    $p1_lh_expense        = $p1_l_expense - $p1_s_expense_sagaku - $p1_b_expense_sagaku;
    $p1_lh_expense_sagaku = $p1_lh_expense;
    $p1_l_expense         = $p1_l_expense - $p1_s_expense_sagaku;               // »î¸³½¤ÍýÀ½Â¤·ÐÈñ¤ò¥ê¥Ë¥¢¤ÎÀ½Â¤·ÐÈñ¤è¤ê¥Þ¥¤¥Ê¥¹
    $p1_lh_expense        = number_format(($p1_lh_expense / $tani), $keta);
    $p1_l_expense         = number_format(($p1_l_expense / $tani), $keta);
}
    ///// Á°·î ¾¦´É
$query = sprintf("select sum(orign_kin) from act_allo_history where pl_bs_ym=%d and actcod>=7501 and actcod<=8000 and orign_id=580", $p1_ym);
if (getUniResult($query, $p1_n_expense) < 1) {
    $p1_n_expense = 0 + $p1_n_expense_sagaku;       // ¸¡º÷¼ºÇÔ
    $p1_n_urigen  = $p1_n_urigen + $p1_n_expense;
    $p1_n_sagaku  = $p1_n_sagaku + $p1_n_expense;   // ¥«¥×¥éº¹³Û·×»»ÍÑ
} else {
    $p1_n_expense = $p1_n_expense + $p1_n_expense_sagaku;
    $p1_n_urigen  = $p1_n_urigen + $p1_n_expense;
    $p1_c_expense = $p1_c_expense - $p1_n_expense;  // ¥«¥×¥éÀ½Â¤·ÐÈñ¡Ý¾¦´ÉÀ½Â¤·ÐÈñ
    $p1_n_sagaku  = $p1_n_sagaku + $p1_n_expense;   // ¥«¥×¥éº¹³Û·×»»ÍÑ
    // 2013/11/07 ÄÉ²Ã 2013Ç¯10·îÅÙ ¶ÈÌ³°ÑÂ÷Èñ¡Ê²£ÀîÇÉ¸¯ÎÁ¡ËÄ´À°
    if ($p1_ym == 201310) {
        $p1_n_expense += 1245035;
    }
    if ($p1_ym == 201311) {
        $p1_n_expense -= 1245035;
    }
    $p1_c_expense = number_format(($p1_c_expense / $tani), $keta);
    $p1_n_expense = number_format(($p1_n_expense / $tani), $keta);
}

    ///// Á°Á°·î
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='»î½¤À½Â¤·ÐÈñ'", $p2_ym);
if (getUniResult($query, $p2_s_expense) < 1) {
    $p2_s_expense        = 0;                       // ¸¡º÷¼ºÇÔ
    $p2_s_expense_sagaku = 0;
} else {
    $p2_s_expense_sagaku = $p2_s_expense;
    $p2_s_expense        = number_format(($p2_s_expense / $tani), $keta);
}
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='¾¦´ÉÀ½Â¤·ÐÈñ'", $p2_ym);
if (getUniResult($query, $p2_n_expense_sagaku) < 1) {
    $p2_n_expense_sagaku = 0;                       // ¸¡º÷¼ºÇÔ
}
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='Á´ÂÎÀ½Â¤·ÐÈñ'", $p2_ym);
if (getUniResult($query, $p2_all_expense) < 1) {
    $p2_all_expense = 0;                            // ¸¡º÷¼ºÇÔ
} else {
    // 2012/02/08 ÄÉ²Ã 2012Ç¯1·îÅÙ ¶ÈÌ³°ÑÂ÷Èñ¡ÊÊ¿½Ð²£ÀîÇÉ¸¯ÎÁ¡ËÄ´À°
    if ($p2_ym == 201201) {
        $p2_all_expense +=1156130;
    }
    // 2012/03/05 ÄÉ²Ã 2012Ç¯1·îÅÙ ¶ÈÌ³°ÑÂ÷Èñ¡ÊÊ¿½Ð²£ÀîÇÉ¸¯ÎÁ¡ËÄ´À° Ìá¤·
    if ($p2_ym == 201202) {
        $p2_all_expense -=1156130;
    }
    $p2_all_expense = number_format(($p2_all_expense / $tani), $keta);
}
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='¥«¥×¥éÀ½Â¤·ÐÈñ'", $p2_ym);
if (getUniResult($query, $p2_c_expense) < 1) {
    $p2_c_expense = 0;                              // ¸¡º÷¼ºÇÔ
} else {
    //$p2_c_expense = number_format(($p2_c_expense / $tani), $keta);
}
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='µ¡¹©À½Â¤·ÐÈñ'", $p2_ym);
if (getUniResult($query, $p2_b_expense) < 1) {
    $p2_b_expense        = 0;    // ¸¡º÷¼ºÇÔ
    $p2_b_expense_sagaku = 0;
} else {
    $p2_b_expense_sagaku = $p2_b_expense;
    $p2_b_expense        = number_format(($p2_b_expense / $tani), $keta);
}
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='¥ê¥Ë¥¢À½Â¤·ÐÈñ'", $p2_ym);
if (getUniResult($query, $p2_l_expense) < 1) {
    $p2_l_expense         = 0 - $p2_s_expense_sagaku;     // ¸¡º÷¼ºÇÔ
    $p2_lh_expense        = 0;
    $p2_lh_expense_sagaku = 0;
} else {
    // 2012/02/08 ÄÉ²Ã 2012Ç¯1·îÅÙ ¶ÈÌ³°ÑÂ÷Èñ¡ÊÊ¿½Ð²£ÀîÇÉ¸¯ÎÁ¡ËÄ´À°
    if ($p2_ym == 201201) {
        $p2_l_expense +=1156130;
    }
    // 2012/03/05 ÄÉ²Ã 2012Ç¯1·îÅÙ ¶ÈÌ³°ÑÂ÷Èñ¡ÊÊ¿½Ð²£ÀîÇÉ¸¯ÎÁ¡ËÄ´À° Ìá¤·
    if ($p2_ym == 201202) {
        $p2_l_expense -=1156130;
    }
    $p2_lh_expense        = $p2_l_expense - $p2_s_expense_sagaku - $p2_b_expense_sagaku;
    $p2_lh_expense_sagaku = $p2_lh_expense;
    $p2_l_expense         = $p2_l_expense - $p2_s_expense_sagaku;               // »î¸³½¤ÍýÀ½Â¤·ÐÈñ¤ò¥ê¥Ë¥¢¤ÎÀ½Â¤·ÐÈñ¤è¤ê¥Þ¥¤¥Ê¥¹
    $p2_lh_expense        = number_format(($p2_lh_expense / $tani), $keta);
    $p2_l_expense         = number_format(($p2_l_expense / $tani), $keta);
}
    ///// Á°Á°·î ¾¦´É
$query = sprintf("select sum(orign_kin) from act_allo_history where pl_bs_ym=%d and actcod>=7501 and actcod<=8000 and orign_id=580", $p2_ym);
if (getUniResult($query, $p2_n_expense) < 1) {
    $p2_n_expense = 0 + $p2_n_expense_sagaku;       // ¸¡º÷¼ºÇÔ
    $p2_n_urigen  = $p2_n_urigen + $p2_n_expense;
    $p2_n_sagaku  = $p2_n_sagaku + $p2_n_expense;   // ¥«¥×¥éº¹³Û·×»»ÍÑ
} else {
    $p2_n_expense = $p2_n_expense + $p2_n_expense_sagaku;
    $p2_n_urigen  = $p2_n_urigen + $p2_n_expense;
    $p2_c_expense = $p2_c_expense - $p2_n_expense;  // ¥«¥×¥éÀ½Â¤·ÐÈñ¡Ý¾¦´ÉÀ½Â¤·ÐÈñ
    $p2_n_sagaku  = $p2_n_sagaku + $p2_n_expense;   // ¥«¥×¥éº¹³Û·×»»ÍÑ
    // 2013/11/07 ÄÉ²Ã 2013Ç¯10·îÅÙ ¶ÈÌ³°ÑÂ÷Èñ¡Ê²£ÀîÇÉ¸¯ÎÁ¡ËÄ´À°
    if ($p2_ym == 201310) {
        $p2_n_expense += 1245035;
    }
    if ($p2_ym == 201311) {
        $p2_n_expense -= 1245035;
    }
    $p2_c_expense = number_format(($p2_c_expense / $tani), $keta);
    $p2_n_expense = number_format(($p2_n_expense / $tani), $keta);
}

    ///// º£´üÎß·×
$query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='»î½¤À½Â¤·ÐÈñ'", $str_ym, $yyyymm);
if (getUniResult($query, $rui_s_expense) < 1) {
    $rui_s_expense        = 0;                      // ¸¡º÷¼ºÇÔ
    $rui_s_expense_sagaku = 0;
} else {
    $rui_s_expense_sagaku = $rui_s_expense;
    $rui_s_expense        = number_format(($rui_s_expense / $tani), $keta);
}
$query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='¾¦´ÉÀ½Â¤·ÐÈñ'", $str_ym, $yyyymm);
if (getUniResult($query, $rui_n_expense_sagaku) < 1) {
    $rui_n_expense_sagaku = 0;                      // ¸¡º÷¼ºÇÔ
}
$query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='Á´ÂÎÀ½Â¤·ÐÈñ'", $str_ym, $yyyymm);
if (getUniResult($query, $rui_all_expense) < 1) {
    $rui_all_expense = 0;                           // ¸¡º÷¼ºÇÔ
} else {
    // 2012/02/08 ÄÉ²Ã 2012Ç¯1·îÅÙ ¶ÈÌ³°ÑÂ÷Èñ¡ÊÊ¿½Ð²£ÀîÇÉ¸¯ÎÁ¡ËÄ´À°
    if ($yyyymm >= 201201 && $yyyymm <= 201203) {
        $rui_all_expense +=1156130;
    }
    // 2012/03/05 ÄÉ²Ã 2012Ç¯1·îÅÙ ¶ÈÌ³°ÑÂ÷Èñ¡ÊÊ¿½Ð²£ÀîÇÉ¸¯ÎÁ¡ËÄ´À° Ìá¤·
    if ($yyyymm >= 201202 && $yyyymm <= 201203) {
        $rui_all_expense -=1156130;
    }
    // 2013/11/07 ÄÉ²Ã 2013Ç¯10·îÅÙ ¶ÈÌ³°ÑÂ÷Èñ¡Ê²£ÀîÇÉ¸¯ÎÁ¡ËÄ´À°
    if ($yyyymm >= 201310 && $yyyymm <= 201403) {
        $rui_all_expense += 1245035;
    }
    if ($yyyymm >= 201311 && $yyyymm <= 201403) {
        $rui_all_expense -= 1245035;
    }
    $rui_all_expense = number_format(($rui_all_expense / $tani), $keta);
}
$query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='¥«¥×¥éÀ½Â¤·ÐÈñ'", $str_ym, $yyyymm);
if (getUniResult($query, $rui_c_expense) < 1) {
    $rui_c_expense = 0;                             // ¸¡º÷¼ºÇÔ
} else {
    //$rui_c_expense = number_format(($rui_c_expense / $tani), $keta);
}
$query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='µ¡¹©À½Â¤·ÐÈñ'", $str_ym, $yyyymm);
if (getUniResult($query, $rui_b_expense) < 1) {
    $rui_b_expense        = 0;    // ¸¡º÷¼ºÇÔ
    $rui_b_expense_sagaku = 0;
} else {
    $rui_b_expense_sagaku = $rui_b_expense;
    $rui_b_expense        = number_format(($rui_b_expense / $tani), $keta);
}
$query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='¥ê¥Ë¥¢À½Â¤·ÐÈñ'", $str_ym, $yyyymm);
if (getUniResult($query, $rui_l_expense) < 1) {
    $rui_l_expense         = 0 - $rui_s_expense_sagaku;   // ¸¡º÷¼ºÇÔ
    $rui_lh_expense        = 0;
    $rui_lh_expense_sagaku = 0;
} else {
    // 2012/02/08 ÄÉ²Ã 2012Ç¯1·îÅÙ ¶ÈÌ³°ÑÂ÷Èñ¡ÊÊ¿½Ð²£ÀîÇÉ¸¯ÎÁ¡ËÄ´À°
    if ($yyyymm >= 201201 && $yyyymm <= 201203) {
        $rui_l_expense +=1156130;
    }
    // 2012/03/05 ÄÉ²Ã 2012Ç¯1·îÅÙ ¶ÈÌ³°ÑÂ÷Èñ¡ÊÊ¿½Ð²£ÀîÇÉ¸¯ÎÁ¡ËÄ´À° Ìá¤·
    if ($yyyymm >= 201202 && $yyyymm <= 201203) {
        $rui_l_expense -=1156130;
    }
    $rui_lh_expense        = $rui_l_expense - $rui_s_expense_sagaku - $rui_b_expense_sagaku;
    $rui_lh_expense_sagaku = $rui_lh_expense;
    $rui_l_expense         = $rui_l_expense - $rui_s_expense_sagaku;               // »î¸³½¤ÍýÀ½Â¤·ÐÈñ¤ò¥ê¥Ë¥¢¤ÎÀ½Â¤·ÐÈñ¤è¤ê¥Þ¥¤¥Ê¥¹
    $rui_lh_expense        = number_format(($rui_lh_expense / $tani), $keta);
    $rui_l_expense         = number_format(($rui_l_expense / $tani), $keta);
}
    ///// º£´üÎß·× ¾¦´É
$query = sprintf("select sum(orign_kin) from act_allo_history where pl_bs_ym>=%d and pl_bs_ym<=%d and actcod>=7501 and actcod<=8000 and orign_id=580", $str_ym, $yyyymm);
if (getUniResult($query, $rui_n_expense) < 1) {
    $rui_n_expense = 0 + $rui_n_expense_sagaku;     // ¸¡º÷¼ºÇÔ
    $rui_n_urigen  = $rui_n_urigen + $rui_n_expense;
    $rui_n_sagaku  = $rui_n_sagaku + $rui_n_expense;    // ¥«¥×¥éº¹³Û·×»»ÍÑ
} else {
    $rui_n_expense = $rui_n_expense + $rui_n_expense_sagaku;
    $rui_n_urigen  = $rui_n_urigen + $rui_n_expense;
    $rui_c_expense = $rui_c_expense - $rui_n_expense;   // ¥«¥×¥éÀ½Â¤·ÐÈñ¡Ý¾¦´ÉÀ½Â¤·ÐÈñ
    $rui_n_sagaku  = $rui_n_sagaku + $rui_n_expense;    // ¥«¥×¥éº¹³Û·×»»ÍÑ
    // 2013/11/07 ÄÉ²Ã 2013Ç¯10·îÅÙ ¶ÈÌ³°ÑÂ÷Èñ¡Ê²£ÀîÇÉ¸¯ÎÁ¡ËÄ´À°
    if ($yyyymm >= 201310 && $yyyymm <= 201403) {
        $rui_n_expense += 1245035;
    }
    if ($yyyymm >= 201311 && $yyyymm <= 201403) {
        $rui_n_expense -= 1245035;
    }
    $rui_c_expense = number_format(($rui_c_expense / $tani), $keta);
    $rui_n_expense = number_format(($rui_n_expense / $tani), $keta);
}

/********** ´üËöºàÎÁ»Å³ÝÉÊÃª²·¹â **********/
    ///// ¾¦´É
$p2_n_endinv = 0;
$p1_n_endinv = 0;
$n_endinv    = 0;
    ///// »î¸³¡¦½¤Íý
$p2_s_endinv = 0;
$p1_s_endinv = 0;
$s_endinv    = 0;
    ///// Åö·î
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='Á´ÂÎ´üËöÃª²·¹â'", $yyyymm);
if (getUniResult($query, $all_endinv) < 1) {
    $all_endinv = 0;                                // ¸¡º÷¼ºÇÔ
} else {
    $all_endinv = ($all_endinv * (-1));             // Éä¹æÈ¿Å¾
    $all_endinv = number_format(($all_endinv / $tani), $keta);
}
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='¥«¥×¥é´üËöÃª²·¹â'", $yyyymm);
if (getUniResult($query, $c_endinv) < 1) {
    $c_endinv = 0;                                  // ¸¡º÷¼ºÇÔ
} else {
    $c_endinv = ($c_endinv * (-1));                 // Éä¹æÈ¿Å¾
    $c_endinv = number_format(($c_endinv / $tani), $keta);
}
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='µ¡¹©´üËöÃª²·¹â'", $yyyymm);
if (getUniResult($query, $b_endinv) < 1) {
    $b_endinv        = 0;                      // ¸¡º÷¼ºÇÔ
    $b_endinv_sagaku = 0;
} else {
    $b_endinv_sagaku = $b_endinv;
    $b_endinv        = ($b_endinv * (-1));     // Éä¹æÈ¿Å¾
    $b_endinv        = number_format(($b_endinv / $tani), $keta);
}
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='¥ê¥Ë¥¢´üËöÃª²·¹â'", $yyyymm);
if (getUniResult($query, $l_endinv) < 1) {
    $l_endinv         = 0;                     // ¸¡º÷¼ºÇÔ
    $lh_endinv        = 0;
    $lh_endinv_sagaku = 0;
} else {
    $lh_endinv        = $l_endinv - $s_endinv - $b_endinv_sagaku;
    $lh_endinv        = ($lh_endinv * (-1));
    $l_endinv         = ($l_endinv * (-1));    // Éä¹æÈ¿Å¾
    $lh_endinv_sagaku = $lh_endinv;
    $lh_endinv        = number_format(($lh_endinv / $tani), $keta);
    $l_endinv         = number_format(($l_endinv / $tani), $keta);
}
    ///// Á°·î
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='Á´ÂÎ´üËöÃª²·¹â'", $p1_ym);
if (getUniResult($query, $p1_all_endinv) < 1) {
    $p1_all_endinv = 0;                             // ¸¡º÷¼ºÇÔ
} else {
    $p1_all_endinv = ($p1_all_endinv * (-1));       // Éä¹æÈ¿Å¾
    $p1_all_endinv = number_format(($p1_all_endinv / $tani), $keta);
}
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='¥«¥×¥é´üËöÃª²·¹â'", $p1_ym);
if (getUniResult($query, $p1_c_endinv) < 1) {
    $p1_c_endinv = 0;                               // ¸¡º÷¼ºÇÔ
} else {
    $p1_c_endinv = ($p1_c_endinv * (-1));           // Éä¹æÈ¿Å¾
    $p1_c_endinv = number_format(($p1_c_endinv / $tani), $keta);
}
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='µ¡¹©´üËöÃª²·¹â'", $p1_ym);
if (getUniResult($query, $p1_b_endinv) < 1) {
    $p1_b_endinv        = 0;                         // ¸¡º÷¼ºÇÔ
    $p1_b_endinv_sagaku = 0;
} else {
    $p1_b_endinv_sagaku = $p1_b_endinv;
    $p1_b_endinv        = ($p1_b_endinv * (-1));     // Éä¹æÈ¿Å¾
    $p1_b_endinv        = number_format(($p1_b_endinv / $tani), $keta);
}
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='¥ê¥Ë¥¢´üËöÃª²·¹â'", $p1_ym);
if (getUniResult($query, $p1_l_endinv) < 1) {
    $p1_l_endinv         = 0;                        // ¸¡º÷¼ºÇÔ
    $p1_lh_endinv        = 0;
    $p1_lh_endinv_sagaku = 0;
} else {
    $p1_lh_endinv        = $p1_l_endinv - $p1_s_endinv - $p1_b_endinv_sagaku;
    $p1_lh_endinv        = ($p1_lh_endinv * (-1));
    $p1_l_endinv         = ($p1_l_endinv * (-1));    // Éä¹æÈ¿Å¾
    $p1_lh_endinv_sagaku = $p1_lh_endinv;
    $p1_lh_endinv        = number_format(($p1_lh_endinv / $tani), $keta);
    $p1_l_endinv         = number_format(($p1_l_endinv / $tani), $keta);
}
    ///// Á°Á°·î
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='Á´ÂÎ´üËöÃª²·¹â'", $p2_ym);
if (getUniResult($query, $p2_all_endinv) < 1) {
    $p2_all_endinv = 0;                             // ¸¡º÷¼ºÇÔ
} else {
    $p2_all_endinv = ($p2_all_endinv * (-1));       // Éä¹æÈ¿Å¾
    $p2_all_endinv = number_format(($p2_all_endinv / $tani), $keta);
}
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='¥«¥×¥é´üËöÃª²·¹â'", $p2_ym);
if (getUniResult($query, $p2_c_endinv) < 1) {
    $p2_c_endinv = 0;                               // ¸¡º÷¼ºÇÔ
} else {
    $p2_c_endinv = ($p2_c_endinv * (-1));           // Éä¹æÈ¿Å¾
    $p2_c_endinv = number_format(($p2_c_endinv / $tani), $keta);
}
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='µ¡¹©´üËöÃª²·¹â'", $p2_ym);
if (getUniResult($query, $p2_b_endinv) < 1) {
    $p2_b_endinv        = 0;                         // ¸¡º÷¼ºÇÔ
    $p2_b_endinv_sagaku = 0;
} else {
    $p2_b_endinv_sagaku = $p2_b_endinv;
    $p2_b_endinv        = ($p2_b_endinv * (-1));     // Éä¹æÈ¿Å¾
    $p2_b_endinv        = number_format(($p2_b_endinv / $tani), $keta);
}
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='¥ê¥Ë¥¢´üËöÃª²·¹â'", $p2_ym);
if (getUniResult($query, $p2_l_endinv) < 1) {
    $p2_l_endinv         = 0;                        // ¸¡º÷¼ºÇÔ
    $p2_lh_endinv        = 0;
    $p2_lh_endinv_sagaku = 0;
} else {
    $p2_lh_endinv        = $p2_l_endinv - $p2_s_endinv - $p2_b_endinv_sagaku;
    $p2_lh_endinv        = ($p2_lh_endinv * (-1));
    $p2_l_endinv         = ($p2_l_endinv * (-1));    // Éä¹æÈ¿Å¾
    $p2_lh_endinv_sagaku = $p2_lh_endinv;
    $p2_lh_endinv        = number_format(($p2_lh_endinv / $tani), $keta);
    $p2_l_endinv         = number_format(($p2_l_endinv / $tani), $keta);
}
    ///// º£´üÎß·×
    ///// ´üËöÃª²·¹â¤ÎÎß·×¤ÏÅö·î¤ÈÆ±¤¸

/********** Çä¾å¸¶²Á **********/
    ///// Åö·î
    ///// »î¸³¡¦½¤Íý
    $s_urigen        = $s_invent + $s_metarial_sagaku + $s_roumu_sagaku + $s_expense_sagaku + $s_endinv;
    $s_urigen_sagaku = $s_urigen;
    $s_urigen        = $s_urigen + $sc_metarial_sagaku;         // ¥«¥×¥é»î½¤ºàÎÁÈñ¤ò²ÃÌ£¡Êsagaku¤Î²¼ ¥ê¥Ë¥¢¤«¤é¥Þ¥¤¥Ê¥¹¤·¤Æ¤·¤Þ¤¦°Ù¡Ë
    if ($yyyymm == 200912) {
        $s_urigen = $s_urigen - 1409708;
    }
    if ($yyyymm >= 201001) {
        $s_urigen = $s_urigen - $s_kyu_kei + $s_kyu_kin;    // ÅºÅÄ¤µ¤ó¤ÎµëÍ¿¤òC¡¦L¤Ï35%¡£»î¸³½¤Íý¤Ë30%¿¶Ê¬
        //$s_urigen = $s_urigen - 432323 + 129697;    // ÅºÅÄ¤µ¤ó¤ÎµëÍ¿¤òC¡¦L¤Ï35%¡£»î¸³½¤Íý¤Ë30%¿¶Ê¬
    }
    $s_urigen        = number_format(($s_urigen / $tani), $keta);
    ///// µ¡¹©
    $b_urigen        = $b_invent_sagaku + $b_metarial_sagaku + $b_roumu_sagaku + $b_expense_sagaku - $b_endinv_sagaku;
    $b_urigen_sagaku = $b_urigen;
    $b_urigen        = number_format(($b_urigen / $tani), $keta);
    ///// CL
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='Á´ÂÎÇä¾å¸¶²Á'", $yyyymm);
if (getUniResult($query, $all_urigen) < 1) {
    $all_urigen = 0;                                // ¸¡º÷¼ºÇÔ
} else {
    // 2012/02/08 ÄÉ²Ã 2012Ç¯1·îÅÙ ¶ÈÌ³°ÑÂ÷Èñ¡ÊÊ¿½Ð²£ÀîÇÉ¸¯ÎÁ¡ËÄ´À°
    if ($yyyymm == 201201) {
        $all_urigen +=1156130;
    }
    // 2012/03/05 ÄÉ²Ã 2012Ç¯1·îÅÙ ¶ÈÌ³°ÑÂ÷Èñ¡ÊÊ¿½Ð²£ÀîÇÉ¸¯ÎÁ¡ËÄ´À° Ìá¤·
    if ($yyyymm == 201202) {
        $all_urigen -=1156130;
    }
    $all_urigen = number_format(($all_urigen / $tani), $keta);
}
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='¥«¥×¥éÇä¾å¸¶²Á'", $yyyymm);
if (getUniResult($query, $c_urigen) < 1) {
    $c_urigen = 0;                                  // ¸¡º÷¼ºÇÔ
} else {
    $c_urigen = $c_urigen - $n_urigen - $sc_metarial_sagaku;    // ¥«¥×¥é»î½¤ºàÎÁÈñ¤â²ÃÌ£
    if ($yyyymm == 200912) {
        $c_urigen = $c_urigen + 1227429;
    }
    if ($yyyymm >= 201001) {
        $c_urigen = $c_urigen + $c_kyu_kin;     // ÅºÅÄ¤µ¤ó¤ÎµëÍ¿¤òC¡¦L¤Ï35%¡£»î¸³½¤Íý¤Ë30%¿¶Ê¬
        //$c_urigen = $c_urigen + 151313;     // ÅºÅÄ¤µ¤ó¤ÎµëÍ¿¤òC¡¦L¤Ï35%¡£»î¸³½¤Íý¤Ë30%¿¶Ê¬
    }
    // 2013/11/07 ÄÉ²Ã 2013Ç¯10·îÅÙ ¶ÈÌ³°ÑÂ÷Èñ¡Ê²£ÀîÇÉ¸¯ÎÁ¡ËÄ´À°
    if ($yyyymm == 201310) {
        $c_urigen -= 1245035;
    }
    if ($yyyymm == 201311) {
        $c_urigen += 1245035;
    }
    if ($yyyymm == 201408) {
        $c_urigen += 611904;
    }
    $c_urigen = number_format(($c_urigen / $tani), $keta);
}
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='¥ê¥Ë¥¢Çä¾å¸¶²Á'", $yyyymm);
if (getUniResult($query, $l_urigen) < 1) {
    $l_urigen         = 0 - $s_urigen_sagaku;     // ¸¡º÷¼ºÇÔ
    $lh_urigen        = 0;                        // ¸¡º÷¼ºÇÔ
    $lh_urigen_sagaku = 0;                        // ¸¡º÷¼ºÇÔ
} else {
    if ($yyyymm == 200912) {
        $l_urigen = $l_urigen + 182279;
    }
    if ($yyyymm >= 201001) {
        $l_urigen = $l_urigen + $l_kyu_kin;     // ÅºÅÄ¤µ¤ó¤ÎµëÍ¿¤òC¡¦L¤Ï35%¡£»î¸³½¤Íý¤Ë30%¿¶Ê¬
        //$l_urigen = $l_urigen + 151313;     // ÅºÅÄ¤µ¤ó¤ÎµëÍ¿¤òC¡¦L¤Ï35%¡£»î¸³½¤Íý¤Ë30%¿¶Ê¬
    }
    // 2012/02/08 ÄÉ²Ã 2012Ç¯1·îÅÙ ¶ÈÌ³°ÑÂ÷Èñ¡ÊÊ¿½Ð²£ÀîÇÉ¸¯ÎÁ¡ËÄ´À°
    if ($yyyymm == 201201) {
        $l_urigen +=1156130;
    }
    // 2012/03/05 ÄÉ²Ã 2012Ç¯1·îÅÙ ¶ÈÌ³°ÑÂ÷Èñ¡ÊÊ¿½Ð²£ÀîÇÉ¸¯ÎÁ¡ËÄ´À° Ìá¤·
    if ($yyyymm == 201202) {
        $l_urigen -=1156130;
    }
    if ($yyyymm == 201408) {
        $l_urigen +=229464;
    }
    $lh_urigen        = $l_urigen - $s_urigen_sagaku - $b_urigen_sagaku;
    $lh_urigen_sagaku = $lh_urigen;
    $l_urigen         = $l_urigen - $s_urigen_sagaku;        // »î¸³½¤ÍýÇä¾å¸¶²Á¤ò¥ê¥Ë¥¢¤ÎÇä¾å¸¶²Á¤è¤ê¥Þ¥¤¥Ê¥¹
    $lh_urigen        = number_format(($lh_urigen / $tani), $keta);
    $l_urigen         = number_format(($l_urigen / $tani), $keta);
}

    ///// Á°·î
    ///// »î¸³¡¦½¤Íý
    $p1_s_urigen        = $p1_s_invent + $p1_s_metarial_sagaku + $p1_s_roumu_sagaku + $p1_s_expense_sagaku + $p1_s_endinv;
    $p1_s_urigen_sagaku = $p1_s_urigen;
    $p1_s_urigen        = $p1_s_urigen + $p1_sc_metarial_sagaku;    // ¥«¥×¥é»î½¤ºàÎÁÈñ¤ò²ÃÌ£¡Êsagaku¤Î²¼ ¥ê¥Ë¥¢¤«¤é¥Þ¥¤¥Ê¥¹¤·¤Æ¤·¤Þ¤¦°Ù¡Ë
    if ($p1_ym == 200912) {
        $p1_s_urigen = $p1_s_urigen - 1409708;
    }
    if ($p1_ym >= 201001) {
        $p1_s_urigen = $p1_s_urigen - $p1_s_kyu_kei + $p1_s_kyu_kin;  // ÅºÅÄ¤µ¤ó¤ÎµëÍ¿¤òC¡¦L¤Ï35%¡£»î¸³½¤Íý¤Ë30%¿¶Ê¬
        //$p1_s_urigen = $p1_s_urigen - 432323 + 129697;  // ÅºÅÄ¤µ¤ó¤ÎµëÍ¿¤òC¡¦L¤Ï35%¡£»î¸³½¤Íý¤Ë30%¿¶Ê¬
    }
    $p1_s_urigen        = number_format(($p1_s_urigen / $tani), $keta);
    ///// µ¡¹©
    $p1_b_urigen        = $p1_b_invent_sagaku + $p1_b_metarial_sagaku + $p1_b_roumu_sagaku + $p1_b_expense_sagaku - $p1_b_endinv_sagaku;
    $p1_b_urigen_sagaku = $p1_b_urigen;
    $p1_b_urigen        = number_format(($p1_b_urigen / $tani), $keta);
    ///// CL
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='Á´ÂÎÇä¾å¸¶²Á'", $p1_ym);
if (getUniResult($query, $p1_all_urigen) < 1) {
    $p1_all_urigen = 0;                             // ¸¡º÷¼ºÇÔ
} else {
    // 2012/02/08 ÄÉ²Ã 2012Ç¯1·îÅÙ ¶ÈÌ³°ÑÂ÷Èñ¡ÊÊ¿½Ð²£ÀîÇÉ¸¯ÎÁ¡ËÄ´À°
    if ($p1_ym == 201201) {
        $p1_all_urigen +=1156130;
    }
    // 2012/03/05 ÄÉ²Ã 2012Ç¯1·îÅÙ ¶ÈÌ³°ÑÂ÷Èñ¡ÊÊ¿½Ð²£ÀîÇÉ¸¯ÎÁ¡ËÄ´À° Ìá¤·
    if ($p1_ym == 201202) {
        $p1_all_urigen -=1156130;
    }
    $p1_all_urigen = number_format(($p1_all_urigen / $tani), $keta);
}
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='¥«¥×¥éÇä¾å¸¶²Á'", $p1_ym);
if (getUniResult($query, $p1_c_urigen) < 1) {
    $p1_c_urigen = 0;                               // ¸¡º÷¼ºÇÔ
} else {
    $p1_c_urigen = $p1_c_urigen - $p1_n_urigen - $p1_sc_metarial_sagaku;    // ¥«¥×¥é»î½¤ºàÎÁÈñ¤â²ÃÌ£
    if ($p1_ym == 200912) {
        $p1_c_urigen = $p1_c_urigen + 1227429;
    }
    if ($p1_ym >= 201001) {
        $p1_c_urigen = $p1_c_urigen + $p1_c_kyu_kin;   // ÅºÅÄ¤µ¤ó¤ÎµëÍ¿¤òC¡¦L¤Ï35%¡£»î¸³½¤Íý¤Ë30%¿¶Ê¬
        //$p1_c_urigen = $p1_c_urigen + 151313;   // ÅºÅÄ¤µ¤ó¤ÎµëÍ¿¤òC¡¦L¤Ï35%¡£»î¸³½¤Íý¤Ë30%¿¶Ê¬
    }
    // 2013/11/07 ÄÉ²Ã 2013Ç¯10·îÅÙ ¶ÈÌ³°ÑÂ÷Èñ¡Ê²£ÀîÇÉ¸¯ÎÁ¡ËÄ´À°
    if ($p1_ym == 201310) {
        $p1_c_urigen -= 1245035;
    }
    if ($p1_ym == 201311) {
        $p1_c_urigen += 1245035;
    }
    if ($p1_ym == 201408) {
        $p1_c_urigen += 611904;
    }
    $p1_c_urigen = number_format(($p1_c_urigen / $tani), $keta);
}
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='¥ê¥Ë¥¢Çä¾å¸¶²Á'", $p1_ym);
if (getUniResult($query, $p1_l_urigen) < 1) {
    $p1_l_urigen         = 0 - $p1_s_urigen_sagaku;     // ¸¡º÷¼ºÇÔ
    $p1_lh_urigen        = 0;                           // ¸¡º÷¼ºÇÔ
    $p1_lh_urigen_sagaku = 0;                           // ¸¡º÷¼ºÇÔ
} else {
    if ($p1_ym == 200912) {
        $p1_l_urigen = $p1_l_urigen + 182279;
    }
    if ($p1_ym >= 201001) {
        $p1_l_urigen = $p1_l_urigen + $p1_l_kyu_kin;   // ÅºÅÄ¤µ¤ó¤ÎµëÍ¿¤òC¡¦L¤Ï35%¡£»î¸³½¤Íý¤Ë30%¿¶Ê¬
        //$p1_l_urigen = $p1_l_urigen + 151313;   // ÅºÅÄ¤µ¤ó¤ÎµëÍ¿¤òC¡¦L¤Ï35%¡£»î¸³½¤Íý¤Ë30%¿¶Ê¬
    }
    // 2012/02/08 ÄÉ²Ã 2012Ç¯1·îÅÙ ¶ÈÌ³°ÑÂ÷Èñ¡ÊÊ¿½Ð²£ÀîÇÉ¸¯ÎÁ¡ËÄ´À°
    if ($p1_ym == 201201) {
        $p1_l_urigen +=1156130;
    }
    // 2012/03/05 ÄÉ²Ã 2012Ç¯1·îÅÙ ¶ÈÌ³°ÑÂ÷Èñ¡ÊÊ¿½Ð²£ÀîÇÉ¸¯ÎÁ¡ËÄ´À° Ìá¤·
    if ($p1_ym == 201202) {
        $p1_l_urigen -=1156130;
    }
    if ($p1_ym == 201408) {
        $p1_l_urigen +=229464;
    }
    $p1_lh_urigen        = $p1_l_urigen - $p1_s_urigen_sagaku - $p1_b_urigen_sagaku;
    $p1_lh_urigen_sagaku = $p1_lh_urigen;
    $p1_l_urigen         = $p1_l_urigen - $p1_s_urigen_sagaku;        // »î¸³½¤ÍýÇä¾å¸¶²Á¤ò¥ê¥Ë¥¢¤ÎÇä¾å¸¶²Á¤è¤ê¥Þ¥¤¥Ê¥¹
    $p1_lh_urigen        = number_format(($p1_lh_urigen / $tani), $keta);
    $p1_l_urigen         = number_format(($p1_l_urigen / $tani), $keta);
}

    ///// Á°Á°·î
    ///// »î¸³¡¦½¤Íý
    $p2_s_urigen        = $p2_s_invent + $p2_s_metarial_sagaku + $p2_s_roumu_sagaku + $p2_s_expense_sagaku + $p2_s_endinv;
    $p2_s_urigen_sagaku = $p2_s_urigen;
    $p2_s_urigen        = $p2_s_urigen + $p2_sc_metarial_sagaku;    // ¥«¥×¥é»î½¤ºàÎÁÈñ¤ò²ÃÌ£¡Êsagaku¤Î²¼ ¥ê¥Ë¥¢¤«¤é¥Þ¥¤¥Ê¥¹¤·¤Æ¤·¤Þ¤¦°Ù¡Ë
    if ($p2_ym == 200912) {
        $p2_s_urigen = $p2_s_urigen - 1409708;
    }
    if ($p2_ym >= 201001) {
        $p2_s_urigen = $p2_s_urigen - $p2_s_kyu_kei + $p2_s_kyu_kin;   // ÅºÅÄ¤µ¤ó¤ÎµëÍ¿¤òC¡¦L¤Ï35%¡£»î¸³½¤Íý¤Ë30%¿¶Ê¬
        //$p2_s_urigen = $p2_s_urigen - 432323 + 129697;   // ÅºÅÄ¤µ¤ó¤ÎµëÍ¿¤òC¡¦L¤Ï35%¡£»î¸³½¤Íý¤Ë30%¿¶Ê¬
    }
    $p2_s_urigen        = number_format(($p2_s_urigen / $tani), $keta);
    ///// µ¡¹©
    $p2_b_urigen        = $p2_b_invent_sagaku + $p2_b_metarial_sagaku + $p2_b_roumu_sagaku + $p2_b_expense_sagaku - $p2_b_endinv_sagaku;
    $p2_b_urigen_sagaku = $p2_b_urigen;
    $p2_b_urigen        = number_format(($p2_b_urigen / $tani), $keta);
    ///// CL
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='Á´ÂÎÇä¾å¸¶²Á'", $p2_ym);
if (getUniResult($query, $p2_all_urigen) < 1) {
    $p2_all_urigen = 0;                             // ¸¡º÷¼ºÇÔ
} else {
    // 2012/02/08 ÄÉ²Ã 2012Ç¯1·îÅÙ ¶ÈÌ³°ÑÂ÷Èñ¡ÊÊ¿½Ð²£ÀîÇÉ¸¯ÎÁ¡ËÄ´À°
    if ($p2_ym == 201201) {
        $p2_all_urigen +=1156130;
    }
    // 2012/03/05 ÄÉ²Ã 2012Ç¯1·îÅÙ ¶ÈÌ³°ÑÂ÷Èñ¡ÊÊ¿½Ð²£ÀîÇÉ¸¯ÎÁ¡ËÄ´À° Ìá¤·
    if ($p2_ym == 201202) {
        $p2_all_urigen -=1156130;
    }
    $p2_all_urigen = number_format(($p2_all_urigen / $tani), $keta);
}
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='¥«¥×¥éÇä¾å¸¶²Á'", $p2_ym);
if (getUniResult($query, $p2_c_urigen) < 1) {
    $p2_c_urigen = 0;                               // ¸¡º÷¼ºÇÔ
} else {
    $p2_c_urigen = $p2_c_urigen - $p2_n_urigen - $p2_sc_metarial_sagaku;    // ¥«¥×¥é»î½¤ºàÎÁÈñ¤â²ÃÌ£
    if ($p2_ym == 200912) {
        $p2_c_urigen = $p2_c_urigen + 1227429;
    }
    if ($p2_ym >= 201001) {
        $p2_c_urigen = $p2_c_urigen + $p2_c_kyu_kin;   // ÅºÅÄ¤µ¤ó¤ÎµëÍ¿¤òC¡¦L¤Ï35%¡£»î¸³½¤Íý¤Ë30%¿¶Ê¬
        //$p2_c_urigen = $p2_c_urigen + 151313;   // ÅºÅÄ¤µ¤ó¤ÎµëÍ¿¤òC¡¦L¤Ï35%¡£»î¸³½¤Íý¤Ë30%¿¶Ê¬
    }
    // 2013/11/07 ÄÉ²Ã 2013Ç¯10·îÅÙ ¶ÈÌ³°ÑÂ÷Èñ¡Ê²£ÀîÇÉ¸¯ÎÁ¡ËÄ´À°
    if ($p2_ym == 201310) {
        $p2_c_urigen -= 1245035;
    }
    if ($p2_ym == 201311) {
        $p2_c_urigen += 1245035;
    }
    if ($p2_ym == 201408) {
        $p2_c_urigen += 611904;
    }
    $p2_c_urigen = number_format(($p2_c_urigen / $tani), $keta);
}
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='¥ê¥Ë¥¢Çä¾å¸¶²Á'", $p2_ym);
if (getUniResult($query, $p2_l_urigen) < 1) {
    $p2_l_urigen         = 0 - $p2_s_urigen_sagaku;     // ¸¡º÷¼ºÇÔ
    $p2_lh_urigen        = 0;                           // ¸¡º÷¼ºÇÔ
    $p2_lh_urigen_sagaku = 0;                           // ¸¡º÷¼ºÇÔ
} else {
    if ($p2_ym == 200912) {
        $p2_l_urigen = $p2_l_urigen + 182279;
    }
    if ($p2_ym >= 201001) {
        $p2_l_urigen = $p2_l_urigen + $p2_l_kyu_kin;   // ÅºÅÄ¤µ¤ó¤ÎµëÍ¿¤òC¡¦L¤Ï35%¡£»î¸³½¤Íý¤Ë30%¿¶Ê¬
        //$p2_l_urigen = $p2_l_urigen + 151313;   // ÅºÅÄ¤µ¤ó¤ÎµëÍ¿¤òC¡¦L¤Ï35%¡£»î¸³½¤Íý¤Ë30%¿¶Ê¬
    }
    // 2012/02/08 ÄÉ²Ã 2012Ç¯1·îÅÙ ¶ÈÌ³°ÑÂ÷Èñ¡ÊÊ¿½Ð²£ÀîÇÉ¸¯ÎÁ¡ËÄ´À°
    if ($p2_ym == 201201) {
        $p2_l_urigen +=1156130;
    }
    // 2012/03/05 ÄÉ²Ã 2012Ç¯1·îÅÙ ¶ÈÌ³°ÑÂ÷Èñ¡ÊÊ¿½Ð²£ÀîÇÉ¸¯ÎÁ¡ËÄ´À° Ìá¤·
    if ($p2_ym == 201202) {
        $p2_l_urigen -=1156130;
    }
    if ($p2_ym == 201408) {
        $p2_l_urigen +=229464;
    }
    $p2_lh_urigen        = $p2_l_urigen - $p2_s_urigen_sagaku - $p2_b_urigen_sagaku;
    $p2_lh_urigen_sagaku = $p2_lh_urigen;
    $p2_l_urigen         = $p2_l_urigen - $p2_s_urigen_sagaku;        // »î¸³½¤ÍýÇä¾å¸¶²Á¤ò¥ê¥Ë¥¢¤ÎÇä¾å¸¶²Á¤è¤ê¥Þ¥¤¥Ê¥¹
    $p2_lh_urigen        = number_format(($p2_lh_urigen / $tani), $keta);
    $p2_l_urigen = number_format(($p2_l_urigen / $tani), $keta);
}

    ///// º£´üÎß·×
    ///// »î¸³¡¦½¤Íý
    $rui_s_urigen        = $rui_s_invent + $rui_s_metarial_sagaku + $rui_s_roumu_sagaku + $rui_s_expense_sagaku + $s_endinv;
    $rui_s_urigen_sagaku = $rui_s_urigen;
    $rui_s_urigen        = $rui_s_urigen + $rui_sc_metarial_sagaku; // ¥«¥×¥é»î½¤ºàÎÁÈñ¤ò²ÃÌ£¡Êsagaku¤Î²¼ ¥ê¥Ë¥¢¤«¤é¥Þ¥¤¥Ê¥¹¤·¤Æ¤·¤Þ¤¦°Ù¡Ë
    if ($yyyymm >= 200912 && $yyyymm <= 201003) {
        $rui_s_urigen = $rui_s_urigen - 1409708;
    }
    if ($yyyymm >= 201001) {
        $rui_s_urigen = $rui_s_urigen - $rui_s_kyu_kei + $rui_s_kyu_kin;    // ÅºÅÄ¤µ¤ó¤ÎµëÍ¿¤òC¡¦L¤Ï35%¡£»î¸³½¤Íý¤Ë30%¿¶Ê¬
        //$rui_s_urigen = $rui_s_urigen - 432323 + 129697;    // ÅºÅÄ¤µ¤ó¤ÎµëÍ¿¤òC¡¦L¤Ï35%¡£»î¸³½¤Íý¤Ë30%¿¶Ê¬
    }
    $rui_s_urigen        = number_format(($rui_s_urigen / $tani), $keta);
    ///// µ¡¹©
    $rui_b_urigen        = $rui_b_invent_sagaku + $rui_b_metarial_sagaku + $rui_b_roumu_sagaku + $rui_b_expense_sagaku - $b_endinv_sagaku;
    $rui_b_urigen_sagaku = $rui_b_urigen;
    $rui_b_urigen        = number_format(($rui_b_urigen / $tani), $keta);
    ///// CL
$query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='Á´ÂÎÇä¾å¸¶²Á'", $str_ym, $yyyymm);
if (getUniResult($query, $rui_all_urigen) < 1) {
    $rui_all_urigen = 0;                            // ¸¡º÷¼ºÇÔ
} else {
    // 2012/02/08 ÄÉ²Ã 2012Ç¯1·îÅÙ ¶ÈÌ³°ÑÂ÷Èñ¡ÊÊ¿½Ð²£ÀîÇÉ¸¯ÎÁ¡ËÄ´À°
    if ($yyyymm >= 201201 && $yyyymm <= 201203) {
        $rui_all_urigen +=1156130;
    }
    // 2012/03/05 ÄÉ²Ã 2012Ç¯1·îÅÙ ¶ÈÌ³°ÑÂ÷Èñ¡ÊÊ¿½Ð²£ÀîÇÉ¸¯ÎÁ¡ËÄ´À° Ìá¤·
    if ($yyyymm >= 201202 && $yyyymm <= 201203) {
        $rui_all_urigen -=1156130;
    }
    $rui_all_urigen = number_format(($rui_all_urigen / $tani), $keta);
}
$query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='¥«¥×¥éÇä¾å¸¶²Á'", $str_ym, $yyyymm);
if (getUniResult($query, $rui_c_urigen) < 1) {
    $rui_c_urigen = 0;                              // ¸¡º÷¼ºÇÔ
} else {
    $rui_c_urigen = $rui_c_urigen - $rui_n_urigen - $rui_sc_metarial_sagaku;    // ¥«¥×¥é»î½¤ºàÎÁÈñ¤â²ÃÌ£
    if ($yyyymm >= 200912 && $yyyymm <= 201003) {
        $rui_c_urigen = $rui_c_urigen + 1227429;
    }
    if ($yyyymm >= 201001) {
        $rui_c_urigen = $rui_c_urigen + $rui_c_kyu_kin;     // ÅºÅÄ¤µ¤ó¤ÎµëÍ¿¤òC¡¦L¤Ï35%¡£»î¸³½¤Íý¤Ë30%¿¶Ê¬
    }
    // 2013/11/07 ÄÉ²Ã 2013Ç¯10·îÅÙ ¶ÈÌ³°ÑÂ÷Èñ¡Ê²£ÀîÇÉ¸¯ÎÁ¡ËÄ´À°
    if ($yyyymm >= 201310 && $yyyymm <= 201403) {
        $rui_c_urigen -= 1245035;
    }
    if ($yyyymm >= 201311 && $yyyymm <= 201403) {
        $rui_c_urigen += 1245035;
    }
    if ($yyyymm >= 201408 && $yyyymm <= 201503) {
        $rui_c_urigen += 611904;
    }
    $rui_c_urigen = number_format(($rui_c_urigen / $tani), $keta);
}
$query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='¥ê¥Ë¥¢Çä¾å¸¶²Á'", $str_ym, $yyyymm);
if (getUniResult($query, $rui_l_urigen) < 1) {
    $rui_l_urigen         = 0 - $rui_s_urigen_sagaku;   // ¸¡º÷¼ºÇÔ
    $rui_lh_urigen        = 0;                          // ¸¡º÷¼ºÇÔ
    $rui_lh_urigen_sagaku = 0;                          // ¸¡º÷¼ºÇÔ
} else {
    if ($yyyymm >= 200912 && $yyyymm <= 201003) {
        $rui_l_urigen = $rui_l_urigen + 182279;
    }
    if ($yyyymm >= 201001) {
        $rui_l_urigen = $rui_l_urigen + $rui_l_kyu_kin;     // ÅºÅÄ¤µ¤ó¤ÎµëÍ¿¤òC¡¦L¤Ï35%¡£»î¸³½¤Íý¤Ë30%¿¶Ê¬
        //$rui_l_urigen = $rui_l_urigen + 151313;     // ÅºÅÄ¤µ¤ó¤ÎµëÍ¿¤òC¡¦L¤Ï35%¡£»î¸³½¤Íý¤Ë30%¿¶Ê¬
    }
    // 2012/02/08 ÄÉ²Ã 2012Ç¯1·îÅÙ ¶ÈÌ³°ÑÂ÷Èñ¡ÊÊ¿½Ð²£ÀîÇÉ¸¯ÎÁ¡ËÄ´À°
    if ($yyyymm >= 201201 && $yyyymm <= 201203) {
        $rui_l_urigen +=1156130;
    }
    // 2012/03/05 ÄÉ²Ã 2012Ç¯1·îÅÙ ¶ÈÌ³°ÑÂ÷Èñ¡ÊÊ¿½Ð²£ÀîÇÉ¸¯ÎÁ¡ËÄ´À° Ìá¤·
    if ($yyyymm >= 201202 && $yyyymm <= 201203) {
        $rui_l_urigen -=1156130;
    }
    if ($yyyymm >= 201408 && $yyyymm <= 201503) {
        $rui_l_urigen = $rui_l_urigen + 229464;
    }
    $rui_lh_urigen        = $rui_l_urigen - $rui_s_urigen_sagaku - $rui_b_urigen_sagaku;
    $rui_lh_urigen_sagaku = $rui_lh_urigen;
    $rui_l_urigen         = $rui_l_urigen - $rui_s_urigen_sagaku;        // »î¸³½¤ÍýÇä¾å¸¶²Á¤ò¥ê¥Ë¥¢¤ÎÇä¾å¸¶²Á¤è¤ê¥Þ¥¤¥Ê¥¹
    $rui_lh_urigen        = number_format(($rui_lh_urigen / $tani), $keta);
    $rui_l_urigen         = number_format(($rui_l_urigen / $tani), $keta);
}

/********** Çä¾åÁíÍø±× **********/
    ///// ¾¦´É
$p2_n_gross_profit  = $p2_n_uri - $p2_n_urigen;
$p2_n_uri           = number_format(($p2_n_uri / $tani), $keta);
$p2_n_invent        = number_format(($p2_n_invent / $tani), $keta);
$p2_n_metarial      = number_format(($p2_n_metarial / $tani), $keta);
$p2_n_endinv        = number_format(($p2_n_endinv / $tani), $keta);

$p1_n_gross_profit  = $p1_n_uri - $p1_n_urigen;
$p1_n_uri           = number_format(($p1_n_uri / $tani), $keta);
$p1_n_invent        = number_format(($p1_n_invent / $tani), $keta);
$p1_n_metarial      = number_format(($p1_n_metarial / $tani), $keta);
$p1_n_endinv        = number_format(($p1_n_endinv / $tani), $keta);

$n_gross_profit     = $n_uri - $n_urigen;
$n_uri              = number_format(($n_uri / $tani), $keta);
$n_invent           = number_format(($n_invent / $tani), $keta);
$n_metarial         = number_format(($n_metarial / $tani), $keta);
$n_endinv           = number_format(($n_endinv / $tani), $keta);

$rui_n_gross_profit = $rui_n_uri - $rui_n_urigen;
$rui_n_uri          = number_format(($rui_n_uri / $tani), $keta);
$rui_n_invent       = number_format(($rui_n_invent / $tani), $keta);
$rui_n_metarial     = number_format(($rui_n_metarial / $tani), $keta);
    
    ///// »î¸³¡¦½¤Íý
$p2_s_gross_profit         = $p2_s_uri_sagaku - $p2_s_urigen_sagaku;
$p2_s_gross_profit_sagaku  = $p2_s_gross_profit;
$p2_s_gross_profit         = $p2_s_gross_profit + $p2_sc_uri_sagaku - $p2_sc_metarial_sagaku;    // ¥«¥×¥é»î¸³½¤Íý¤ò²ÃÌ£¡Êsagaku¤Î¸å¡Ý¥ê¥Ë¥¢¤«¤é¥Þ¥¤¥Ê¥¹¤·¤Æ¤·¤Þ¤¦°Ù¡Ë
if ($p2_ym == 200912) {
    $p2_s_gross_profit = $p2_s_gross_profit + 1409708;
}
if ($p2_ym >= 201001) {
    $p2_s_gross_profit = $p2_s_gross_profit + $p2_s_kyu_kei - $p2_s_kyu_kin;  // ÅºÅÄ¤µ¤ó¤ÎµëÍ¿¤òC¡¦L¤Ï35%¡£»î¸³½¤Íý¤Ë30%¿¶Ê¬
    //$p2_s_gross_profit = $p2_s_gross_profit + 432323 - 129697;  // ÅºÅÄ¤µ¤ó¤ÎµëÍ¿¤òC¡¦L¤Ï35%¡£»î¸³½¤Íý¤Ë30%¿¶Ê¬
}
$p2_s_gross_profit         = number_format(($p2_s_gross_profit / $tani), $keta);

$p1_s_gross_profit         = $p1_s_uri_sagaku - $p1_s_urigen_sagaku;
$p1_s_gross_profit_sagaku  = $p1_s_gross_profit;
$p1_s_gross_profit         = $p1_s_gross_profit + $p1_sc_uri_sagaku - $p1_sc_metarial_sagaku;    // ¥«¥×¥é»î¸³½¤Íý¤ò²ÃÌ£¡Êsagaku¤Î¸å¡Ý¥ê¥Ë¥¢¤«¤é¥Þ¥¤¥Ê¥¹¤·¤Æ¤·¤Þ¤¦°Ù¡Ë
if ($p1_ym == 200912) {
    $p1_s_gross_profit = $p1_s_gross_profit + 1409708;
}
if ($p1_ym >= 201001) {
    $p1_s_gross_profit = $p1_s_gross_profit + $p1_s_kyu_kei - $p1_s_kyu_kin;  // ÅºÅÄ¤µ¤ó¤ÎµëÍ¿¤òC¡¦L¤Ï35%¡£»î¸³½¤Íý¤Ë30%¿¶Ê¬
    //$p1_s_gross_profit = $p1_s_gross_profit + 432323 - 129697;  // ÅºÅÄ¤µ¤ó¤ÎµëÍ¿¤òC¡¦L¤Ï35%¡£»î¸³½¤Íý¤Ë30%¿¶Ê¬
}
$p1_s_gross_profit         = number_format(($p1_s_gross_profit / $tani), $keta);

$s_gross_profit            = $s_uri_sagaku - $s_urigen_sagaku;
$s_gross_profit_sagaku     = $s_gross_profit;
$s_gross_profit            = $s_gross_profit + $sc_uri_sagaku - $sc_metarial_sagaku;             // ¥«¥×¥é»î¸³½¤Íý¤ò²ÃÌ£¡Êsagaku¤Î¸å¡Ý¥ê¥Ë¥¢¤«¤é¥Þ¥¤¥Ê¥¹¤·¤Æ¤·¤Þ¤¦°Ù¡Ë
if ($yyyymm == 200912) {
    $s_gross_profit = $s_gross_profit + 1409708;
}
if ($yyyymm >= 201001) {
    $s_gross_profit = $s_gross_profit + $s_kyu_kei - $s_kyu_kin;  // ÅºÅÄ¤µ¤ó¤ÎµëÍ¿¤òC¡¦L¤Ï35%¡£»î¸³½¤Íý¤Ë30%¿¶Ê¬
    //$s_gross_profit = $s_gross_profit + 432323 - 129697;  // ÅºÅÄ¤µ¤ó¤ÎµëÍ¿¤òC¡¦L¤Ï35%¡£»î¸³½¤Íý¤Ë30%¿¶Ê¬
}
$s_gross_profit            = number_format(($s_gross_profit / $tani), $keta);

$rui_s_gross_profit        = $rui_s_uri_sagaku - $rui_s_urigen_sagaku;
$rui_s_gross_profit_sagaku = $rui_s_gross_profit;
$rui_s_gross_profit        = $rui_s_gross_profit + $rui_sc_uri_sagaku - $rui_sc_metarial_sagaku; // ¥«¥×¥é»î¸³½¤Íý¤ò²ÃÌ£¡Êsagaku¤Î¸å¡Ý¥ê¥Ë¥¢¤«¤é¥Þ¥¤¥Ê¥¹¤·¤Æ¤·¤Þ¤¦°Ù¡Ë
if ($yyyymm >= 200912 && $yyyymm <= 201003) {
    $rui_s_gross_profit = $rui_s_gross_profit + 1409708;
}
if ($yyyymm >= 201001) {
    $rui_s_gross_profit = $rui_s_gross_profit + $rui_s_kyu_kei - $rui_s_kyu_kin;  // ÅºÅÄ¤µ¤ó¤ÎµëÍ¿¤òC¡¦L¤Ï35%¡£»î¸³½¤Íý¤Ë30%¿¶Ê¬
    //$rui_s_gross_profit = $rui_s_gross_profit + 432323 - 129697;  // ÅºÅÄ¤µ¤ó¤ÎµëÍ¿¤òC¡¦L¤Ï35%¡£»î¸³½¤Íý¤Ë30%¿¶Ê¬
}
$rui_s_gross_profit        = number_format(($rui_s_gross_profit / $tani), $keta);
    ///// µ¡¹©
$p2_b_gross_profit         = $p2_b_uri_sagaku - $p2_b_urigen_sagaku;
$p2_b_gross_profit_sagaku  = $p2_b_gross_profit;
$p2_b_gross_profit         = number_format(($p2_b_gross_profit / $tani), $keta);

$p1_b_gross_profit         = $p1_b_uri_sagaku - $p1_b_urigen_sagaku;
$p1_b_gross_profit_sagaku  = $p1_b_gross_profit;
$p1_b_gross_profit         = number_format(($p1_b_gross_profit / $tani), $keta);

$b_gross_profit            = $b_uri_sagaku - $b_urigen_sagaku;
$b_gross_profit_sagaku     = $b_gross_profit;
$b_gross_profit            = number_format(($b_gross_profit / $tani), $keta);

$rui_b_gross_profit        = $rui_b_uri_sagaku - $rui_b_urigen_sagaku;
$rui_b_gross_profit_sagaku = $rui_b_gross_profit;
$rui_b_gross_profit        = number_format(($rui_b_gross_profit / $tani), $keta);

    ///// Åö·î
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='Á´ÂÎÁíÍø±×'", $yyyymm);
if (getUniResult($query, $all_gross_profit) < 1) {
    $all_gross_profit = 0;                      // ¸¡º÷¼ºÇÔ
} else {
    if ($yyyymm == 200906) {
        $all_gross_profit = $all_gross_profit + $n_uri_sagaku - 3100900;
    } elseif ($yyyymm == 200905) {
        $all_gross_profit = $all_gross_profit + $n_uri_sagaku + 1550450;
    } elseif ($yyyymm == 200904) {
        $all_gross_profit = $all_gross_profit + $n_uri_sagaku + 1550450;
    } else {
        $all_gross_profit = $all_gross_profit + $n_uri_sagaku;
    }
    // 2012/02/08 ÄÉ²Ã 2012Ç¯1·îÅÙ ¶ÈÌ³°ÑÂ÷Èñ¡ÊÊ¿½Ð²£ÀîÇÉ¸¯ÎÁ¡ËÄ´À°
    if ($yyyymm == 201201) {
        $all_gross_profit -=1156130;
    }
    // 2012/03/05 ÄÉ²Ã 2012Ç¯1·îÅÙ ¶ÈÌ³°ÑÂ÷Èñ¡ÊÊ¿½Ð²£ÀîÇÉ¸¯ÎÁ¡ËÄ´À° Ìá¤·
    if ($yyyymm == 201202) {
        $all_gross_profit +=1156130;
    }
    $all_gross_profit = number_format(($all_gross_profit / $tani), $keta);
}
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='¥«¥×¥éÁíÍø±×'", $yyyymm);
if (getUniResult($query, $c_gross_profit) < 1) {
    $c_gross_profit = 0;                        // ¸¡º÷¼ºÇÔ
} else {
    $c_gross_profit = $c_gross_profit + $n_urigen - $sc_uri_sagaku + $sc_metarial_sagaku;   // ¥«¥×¥é»î¸³½¤Íý¤ò²ÃÌ£
    if ($yyyymm == 200912) {
        $c_gross_profit = $c_gross_profit - 1227429;
    }
    if ($yyyymm >= 201001) {
        $c_gross_profit = $c_gross_profit - $c_kyu_kin;     // ÅºÅÄ¤µ¤ó¤ÎµëÍ¿¤òC¡¦L¤Ï35%¡£»î¸³½¤Íý¤Ë30%¿¶Ê¬
        //$c_gross_profit = $c_gross_profit - 151313;     // ÅºÅÄ¤µ¤ó¤ÎµëÍ¿¤òC¡¦L¤Ï35%¡£»î¸³½¤Íý¤Ë30%¿¶Ê¬
    }
    // 2013/11/07 ÄÉ²Ã 2013Ç¯10·îÅÙ ¶ÈÌ³°ÑÂ÷Èñ¡Ê²£ÀîÇÉ¸¯ÎÁ¡ËÄ´À°
    if ($yyyymm == 201310) {
        $c_gross_profit += 1245035;
    }
    if ($yyyymm == 201311) {
        $c_gross_profit -= 1245035;
    }
    if ($yyyymm == 201408) {
        $c_gross_profit -= 611904;
    }
    // 2013/11/07 ÄÉ²Ã 2013Ç¯10·îÅÙ ¶ÈÌ³°ÑÂ÷Èñ¡Ê²£ÀîÇÉ¸¯ÎÁ¡ËÄ´À°
    if ($yyyymm == 201310) {
        $n_urigen += 1245035;
    }
    if ($yyyymm == 201311) {
        $n_urigen -= 1245035;
    }
    if ($yyyymm == 201408) {
        $n_urigen -= 841368;
    }
    $n_urigen       = number_format(($n_urigen / $tani), $keta);
    $c_gross_profit = number_format(($c_gross_profit / $tani), $keta);
}
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='¥ê¥Ë¥¢ÁíÍø±×'", $yyyymm);
if (getUniResult($query, $l_gross_profit) < 1) {
    $l_gross_profit         = 0 - $s_gross_profit_sagaku;     // ¸¡º÷¼ºÇÔ
    $lh_gross_profit        = 0;                              // ¸¡º÷¼ºÇÔ
    $lh_gross_profit_sagaku = 0;                              // ¸¡º÷¼ºÇÔ
} else {
    if ($yyyymm == 200906) {
        $l_gross_profit = $l_gross_profit - 3100900;
    } elseif ($yyyymm == 200905) {
        $l_gross_profit = $l_gross_profit + 1550450;
    } elseif ($yyyymm == 200904) {
        $l_gross_profit = $l_gross_profit + 1550450;
    }
    if ($yyyymm == 200912) {
        $l_gross_profit = $l_gross_profit - 182279;
    }
    if ($yyyymm >= 201001) {
        $l_gross_profit = $l_gross_profit - $l_kyu_kin;     // ÅºÅÄ¤µ¤ó¤ÎµëÍ¿¤òC¡¦L¤Ï35%¡£»î¸³½¤Íý¤Ë30%¿¶Ê¬
        //$l_gross_profit = $l_gross_profit - 151313;     // ÅºÅÄ¤µ¤ó¤ÎµëÍ¿¤òC¡¦L¤Ï35%¡£»î¸³½¤Íý¤Ë30%¿¶Ê¬
    }
    if ($yyyymm == 201004) {
        $l_gross_profit = $l_gross_profit - 255240;
    }
    // 2012/02/08 ÄÉ²Ã 2012Ç¯1·îÅÙ ¶ÈÌ³°ÑÂ÷Èñ¡ÊÊ¿½Ð²£ÀîÇÉ¸¯ÎÁ¡ËÄ´À°
    if ($yyyymm == 201201) {
        $l_gross_profit -=1156130;
    }
    // 2012/03/05 ÄÉ²Ã 2012Ç¯1·îÅÙ ¶ÈÌ³°ÑÂ÷Èñ¡ÊÊ¿½Ð²£ÀîÇÉ¸¯ÎÁ¡ËÄ´À° Ìá¤·
    if ($yyyymm == 201202) {
        $l_gross_profit +=1156130;
    }
    if ($yyyymm == 201408) {
        $l_gross_profit -=229464;
    }
    $lh_gross_profit        = $l_gross_profit - $s_gross_profit_sagaku - $b_gross_profit_sagaku;
    $lh_gross_profit_sagaku = $lh_gross_profit;
    $l_gross_profit         = $l_gross_profit - $s_gross_profit_sagaku;     // »î¸³½¤ÍýÇä¾åÁíÍø±×¤ò¥ê¥Ë¥¢¤ÎÇä¾åÁíÍø±×¤è¤ê¥Þ¥¤¥Ê¥¹
    $lh_gross_profit        = number_format(($lh_gross_profit / $tani), $keta);
    $l_gross_profit         = number_format(($l_gross_profit / $tani), $keta);
}
    ///// Á°·î
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='Á´ÂÎÁíÍø±×'", $p1_ym);
if (getUniResult($query, $p1_all_gross_profit) < 1) {
    $p1_all_gross_profit = 0;                   // ¸¡º÷¼ºÇÔ
} else {
    if ($p1_ym == 200906) {
        $p1_all_gross_profit = $p1_all_gross_profit + $p1_n_uri_sagaku - 3100900;
    } elseif ($p1_ym == 200905) {
        $p1_all_gross_profit = $p1_all_gross_profit + $p1_n_uri_sagaku + 1550450;
    } elseif ($p1_ym == 200904) {
        $p1_all_gross_profit = $p1_all_gross_profit + $p1_n_uri_sagaku + 1550450;
    } else {
        $p1_all_gross_profit = $p1_all_gross_profit + $p1_n_uri_sagaku;
    }
    // 2012/02/08 ÄÉ²Ã 2012Ç¯1·îÅÙ ¶ÈÌ³°ÑÂ÷Èñ¡ÊÊ¿½Ð²£ÀîÇÉ¸¯ÎÁ¡ËÄ´À°
    if ($p1_ym == 201201) {
        $p1_all_gross_profit -=1156130;
    }
    // 2012/03/05 ÄÉ²Ã 2012Ç¯1·îÅÙ ¶ÈÌ³°ÑÂ÷Èñ¡ÊÊ¿½Ð²£ÀîÇÉ¸¯ÎÁ¡ËÄ´À° Ìá¤·
    if ($p1_ym == 201202) {
        $p1_all_gross_profit +=1156130;
    }
    $p1_all_gross_profit = number_format(($p1_all_gross_profit / $tani), $keta);
}
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='¥«¥×¥éÁíÍø±×'", $p1_ym);
if (getUniResult($query, $p1_c_gross_profit) < 1) {
    $p1_c_gross_profit = 0;                     // ¸¡º÷¼ºÇÔ
} else {
    $p1_c_gross_profit = $p1_c_gross_profit + $p1_n_urigen - $p1_sc_uri_sagaku + $p1_sc_metarial_sagaku;    // ¥«¥×¥é»î¸³½¤Íý¤ò²ÃÌ£
    if ($p1_ym == 200912) {
        $p1_c_gross_profit = $p1_c_gross_profit - 1227429;
    }
    if ($p1_ym >= 201001) {
        $p1_c_gross_profit = $p1_c_gross_profit - $p1_c_kyu_kin;   // ÅºÅÄ¤µ¤ó¤ÎµëÍ¿¤òC¡¦L¤Ï35%¡£»î¸³½¤Íý¤Ë30%¿¶Ê¬
        //$p1_c_gross_profit = $p1_c_gross_profit - 151313;   // ÅºÅÄ¤µ¤ó¤ÎµëÍ¿¤òC¡¦L¤Ï35%¡£»î¸³½¤Íý¤Ë30%¿¶Ê¬
    }
    // 2013/11/07 ÄÉ²Ã 2013Ç¯10·îÅÙ ¶ÈÌ³°ÑÂ÷Èñ¡Ê²£ÀîÇÉ¸¯ÎÁ¡ËÄ´À°
    if ($p1_ym == 201310) {
        $p1_c_gross_profit += 1245035;
    }
    if ($p1_ym == 201311) {
        $p1_c_gross_profit -= 1245035;
    }
    if ($p1_ym == 201408) {
        $p1_c_gross_profit -= 611904;
    }
    // 2013/11/07 ÄÉ²Ã 2013Ç¯10·îÅÙ ¶ÈÌ³°ÑÂ÷Èñ¡Ê²£ÀîÇÉ¸¯ÎÁ¡ËÄ´À°
    if ($p1_ym == 201310) {
        $p1_n_urigen += 1245035;
    }
    if ($p1_ym == 201311) {
        $p1_n_urigen -= 1245035;
    }
    if ($p1_ym == 201408) {
        $p1_n_urigen -= 841368;
    }
    $p1_n_urigen       = number_format(($p1_n_urigen / $tani), $keta);
    $p1_c_gross_profit = number_format(($p1_c_gross_profit / $tani), $keta);
}
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='¥ê¥Ë¥¢ÁíÍø±×'", $p1_ym);
if (getUniResult($query, $p1_l_gross_profit) < 1) {
    $p1_l_gross_profit         = 0 - $p1_s_gross_profit_sagaku;     // ¸¡º÷¼ºÇÔ
    $p1_lh_gross_profit        = 0;                                 // ¸¡º÷¼ºÇÔ
    $p1_lh_gross_profit_sagaku = 0;                                 // ¸¡º÷¼ºÇÔ
} else {
    if ($p1_ym == 200906) {
        $p1_l_gross_profit = $p1_l_gross_profit - 3100900;
    } elseif ($p1_ym == 200905) {
        $p1_l_gross_profit = $p1_l_gross_profit + 1550450;
    } elseif ($p1_ym == 200904) {
        $p1_l_gross_profit = $p1_l_gross_profit + 1550450;
    }
    if ($p1_ym == 200912) {
        $p1_l_gross_profit = $p1_l_gross_profit - 182279;
    }
    if ($p1_ym >= 201001) {
        $p1_l_gross_profit = $p1_l_gross_profit - $p1_l_kyu_kin;   // ÅºÅÄ¤µ¤ó¤ÎµëÍ¿¤òC¡¦L¤Ï35%¡£»î¸³½¤Íý¤Ë30%¿¶Ê¬
        //$p1_l_gross_profit = $p1_l_gross_profit - 151313;   // ÅºÅÄ¤µ¤ó¤ÎµëÍ¿¤òC¡¦L¤Ï35%¡£»î¸³½¤Íý¤Ë30%¿¶Ê¬
    }
    if ($p1_ym == 201004) {
        $p1_l_gross_profit = $p1_l_gross_profit - 255240;
    }
    // 2012/02/08 ÄÉ²Ã 2012Ç¯1·îÅÙ ¶ÈÌ³°ÑÂ÷Èñ¡ÊÊ¿½Ð²£ÀîÇÉ¸¯ÎÁ¡ËÄ´À°
    if ($p1_ym == 201201) {
        $p1_l_gross_profit -=1156130;
    }
    // 2012/03/05 ÄÉ²Ã 2012Ç¯1·îÅÙ ¶ÈÌ³°ÑÂ÷Èñ¡ÊÊ¿½Ð²£ÀîÇÉ¸¯ÎÁ¡ËÄ´À° Ìá¤·
    if ($p1_ym == 201202) {
        $p1_l_gross_profit +=1156130;
    }
    if ($p1_ym == 201408) {
        $p1_l_gross_profit -=229464;
    }
    $p1_lh_gross_profit        = $p1_l_gross_profit - $p1_s_gross_profit_sagaku - $p1_b_gross_profit_sagaku;
    $p1_lh_gross_profit_sagaku = $p1_lh_gross_profit;
    $p1_l_gross_profit         = $p1_l_gross_profit - $p1_s_gross_profit_sagaku;     // »î¸³½¤ÍýÇä¾åÁíÍø±×¤ò¥ê¥Ë¥¢¤ÎÇä¾åÁíÍø±×¤è¤ê¥Þ¥¤¥Ê¥¹
    $p1_lh_gross_profit        = number_format(($p1_lh_gross_profit / $tani), $keta);
    $p1_l_gross_profit         = number_format(($p1_l_gross_profit / $tani), $keta);
}
    ///// Á°Á°·î
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='Á´ÂÎÁíÍø±×'", $p2_ym);
if (getUniResult($query, $p2_all_gross_profit) < 1) {
    $p2_all_gross_profit = 0;                   // ¸¡º÷¼ºÇÔ
} else {
    if ($p2_ym == 200906) {
        $p2_all_gross_profit = $p2_all_gross_profit + $p2_n_uri_sagaku - 3100900;
    } elseif ($p2_ym == 200905) {
        $p2_all_gross_profit = $p2_all_gross_profit + $p2_n_uri_sagaku + 1550450;
    } elseif ($p2_ym == 200904) {
        $p2_all_gross_profit = $p2_all_gross_profit + $p2_n_uri_sagaku + 1550450;
    } else {
        $p2_all_gross_profit = $p2_all_gross_profit + $p2_n_uri_sagaku;
    }
    // 2012/02/08 ÄÉ²Ã 2012Ç¯1·îÅÙ ¶ÈÌ³°ÑÂ÷Èñ¡ÊÊ¿½Ð²£ÀîÇÉ¸¯ÎÁ¡ËÄ´À°
    if ($p2_ym == 201201) {
        $p2_all_gross_profit -=1156130;
    }
    // 2012/03/05 ÄÉ²Ã 2012Ç¯1·îÅÙ ¶ÈÌ³°ÑÂ÷Èñ¡ÊÊ¿½Ð²£ÀîÇÉ¸¯ÎÁ¡ËÄ´À° Ìá¤·
    if ($p2_ym == 201202) {
        $p2_all_gross_profit +=1156130;
    }
    $p2_all_gross_profit = number_format(($p2_all_gross_profit / $tani), $keta);
}
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='¥«¥×¥éÁíÍø±×'", $p2_ym);
if (getUniResult($query, $p2_c_gross_profit) < 1) {
    $p2_c_gross_profit = 0;                     // ¸¡º÷¼ºÇÔ
} else {
    $p2_c_gross_profit = $p2_c_gross_profit + $p2_n_urigen - $p2_sc_uri_sagaku + $p2_sc_metarial_sagaku;    // ¥«¥×¥é»î¸³½¤Íý¤ò²ÃÌ£
    if ($p2_ym == 200912) {
        $p2_c_gross_profit = $p2_c_gross_profit - 1227429;
    }
    if ($p2_ym >= 201001) {
        $p2_c_gross_profit = $p2_c_gross_profit - $p2_c_kyu_kin;   // ÅºÅÄ¤µ¤ó¤ÎµëÍ¿¤òC¡¦L¤Ï35%¡£»î¸³½¤Íý¤Ë30%¿¶Ê¬
        //$p2_c_gross_profit = $p2_c_gross_profit - 151313;   // ÅºÅÄ¤µ¤ó¤ÎµëÍ¿¤òC¡¦L¤Ï35%¡£»î¸³½¤Íý¤Ë30%¿¶Ê¬
    }
    // 2013/11/07 ÄÉ²Ã 2013Ç¯10·îÅÙ ¶ÈÌ³°ÑÂ÷Èñ¡Ê²£ÀîÇÉ¸¯ÎÁ¡ËÄ´À°
    if ($p2_ym == 201310) {
        $p2_c_gross_profit += 1245035;
    }
    if ($p2_ym == 201311) {
        $p2_c_gross_profit -= 1245035;
    }
    if ($p2_ym == 201408) {
        $p2_c_gross_profit -= 611904;
    }
    // 2013/11/07 ÄÉ²Ã 2013Ç¯10·îÅÙ ¶ÈÌ³°ÑÂ÷Èñ¡Ê²£ÀîÇÉ¸¯ÎÁ¡ËÄ´À°
    if ($p2_ym == 201310) {
        $p2_n_urigen += 1245035;
    }
    if ($p2_ym == 201311) {
        $p2_n_urigen -= 1245035;
    }
    if ($p2_ym == 201408) {
        $p2_n_urigen -= 841368;
    }
    $p2_n_urigen       = number_format(($p2_n_urigen / $tani), $keta);
    $p2_c_gross_profit = number_format(($p2_c_gross_profit / $tani), $keta);
}
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='¥ê¥Ë¥¢ÁíÍø±×'", $p2_ym);
if (getUniResult($query, $p2_l_gross_profit) < 1) {
    $p2_l_gross_profit         = 0 - $p2_s_gross_profit_sagaku;     // ¸¡º÷¼ºÇÔ
    $p2_lh_gross_profit        = 0;                                 // ¸¡º÷¼ºÇÔ
    $p2_lh_gross_profit_sagaku = 0;                                 // ¸¡º÷¼ºÇÔ
} else {
    if ($p2_ym == 200906) {
        $p2_l_gross_profit = $p2_l_gross_profit - 3100900;
    } elseif ($p2_ym == 200905) {
        $p2_l_gross_profit = $p2_l_gross_profit + 1550450;
    } elseif ($p2_ym == 200904) {
        $p2_l_gross_profit = $p2_l_gross_profit + 1550450;
    }
    if ($p2_ym == 200912) {
        $p2_l_gross_profit = $p2_l_gross_profit - 182279;
    }
    if ($p2_ym >= 201001) {
        $p2_l_gross_profit = $p2_l_gross_profit - $p2_l_kyu_kin;   // ÅºÅÄ¤µ¤ó¤ÎµëÍ¿¤òC¡¦L¤Ï35%¡£»î¸³½¤Íý¤Ë30%¿¶Ê¬
        //$p2_l_gross_profit = $p2_l_gross_profit - 151313;   // ÅºÅÄ¤µ¤ó¤ÎµëÍ¿¤òC¡¦L¤Ï35%¡£»î¸³½¤Íý¤Ë30%¿¶Ê¬
    }
    if ($p2_ym == 201004) {
        $p2_l_gross_profit = $p2_l_gross_profit - 255240;
    }
    // 2012/02/08 ÄÉ²Ã 2012Ç¯1·îÅÙ ¶ÈÌ³°ÑÂ÷Èñ¡ÊÊ¿½Ð²£ÀîÇÉ¸¯ÎÁ¡ËÄ´À°
    if ($p2_ym == 201201) {
        $p2_l_gross_profit -=1156130;
    }
    // 2012/03/05 ÄÉ²Ã 2012Ç¯1·îÅÙ ¶ÈÌ³°ÑÂ÷Èñ¡ÊÊ¿½Ð²£ÀîÇÉ¸¯ÎÁ¡ËÄ´À° Ìá¤·
    if ($p2_ym == 201202) {
        $p2_l_gross_profit +=1156130;
    }
    if ($p2_ym == 201408) {
        $p2_l_gross_profit -=229464;
    }
    $p2_lh_gross_profit        = $p2_l_gross_profit - $p2_s_gross_profit_sagaku - $p2_b_gross_profit_sagaku;
    $p2_lh_gross_profit_sagaku = $p2_lh_gross_profit;
    $p2_l_gross_profit         = $p2_l_gross_profit - $p2_s_gross_profit_sagaku;     // »î¸³½¤ÍýÇä¾åÁíÍø±×¤ò¥ê¥Ë¥¢¤ÎÇä¾åÁíÍø±×¤è¤ê¥Þ¥¤¥Ê¥¹
    $p2_lh_gross_profit        = number_format(($p2_lh_gross_profit / $tani), $keta);
    $p2_l_gross_profit         = number_format(($p2_l_gross_profit / $tani), $keta);
}
    ///// º£´üÎß·×
$query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='Á´ÂÎÁíÍø±×'", $str_ym, $yyyymm);
if (getUniResult($query, $rui_all_gross_profit) < 1) {
    $rui_all_gross_profit = 0;                  // ¸¡º÷¼ºÇÔ
} else {
    if ($yyyymm == 200905) {
        $rui_all_gross_profit = $rui_all_gross_profit + 3100900;
    } elseif ($yyyymm == 200904) {
        $rui_all_gross_profit = $rui_all_gross_profit + 1550450;
    }
    // 2012/02/08 ÄÉ²Ã 2012Ç¯1·îÅÙ ¶ÈÌ³°ÑÂ÷Èñ¡ÊÊ¿½Ð²£ÀîÇÉ¸¯ÎÁ¡ËÄ´À°
    if ($yyyymm >= 201201 && $yyyymm <= 201203) {
        $rui_all_gross_profit -=1156130;
    }
    // 2012/03/05 ÄÉ²Ã 2012Ç¯1·îÅÙ ¶ÈÌ³°ÑÂ÷Èñ¡ÊÊ¿½Ð²£ÀîÇÉ¸¯ÎÁ¡ËÄ´À° Ìá¤·
    if ($yyyymm >= 201202 && $yyyymm <= 201203) {
        $rui_all_gross_profit +=1156130;
    }
    $rui_all_gross_profit = $rui_all_gross_profit + $rui_n_uri_sagaku;
    $rui_all_gross_profit = number_format(($rui_all_gross_profit / $tani), $keta);
}
$query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='¥«¥×¥éÁíÍø±×'", $str_ym, $yyyymm);
if (getUniResult($query, $rui_c_gross_profit) < 1) {
    $rui_c_gross_profit = 0;                    // ¸¡º÷¼ºÇÔ
} else {
    $rui_c_gross_profit = $rui_c_gross_profit + $rui_n_urigen - $rui_sc_uri_sagaku + $rui_sc_metarial_sagaku;   // ¥«¥×¥é»î¸³½¤Íý¤ò²ÃÌ£
    if ($yyyymm >= 200912 && $yyyymm <= 201003) {
        $rui_c_gross_profit = $rui_c_gross_profit - 1227429;
    }
    if ($yyyymm >= 201001) {
        $rui_c_gross_profit = $rui_c_gross_profit - $rui_c_kyu_kin;     // ÅºÅÄ¤µ¤ó¤ÎµëÍ¿¤òC¡¦L¤Ï35%¡£»î¸³½¤Íý¤Ë30%¿¶Ê¬
        //$rui_c_gross_profit = $rui_c_gross_profit - 151313;     // ÅºÅÄ¤µ¤ó¤ÎµëÍ¿¤òC¡¦L¤Ï35%¡£»î¸³½¤Íý¤Ë30%¿¶Ê¬
    }
    // 2013/11/07 ÄÉ²Ã 2013Ç¯10·îÅÙ ¶ÈÌ³°ÑÂ÷Èñ¡Ê²£ÀîÇÉ¸¯ÎÁ¡ËÄ´À°
    if ($yyyymm >= 201310 && $yyyymm <= 201403) {
        $rui_c_gross_profit += 1245035;
    }
    if ($yyyymm >= 201311 && $yyyymm <= 201403) {
        $rui_c_gross_profit -= 1245035;
    }
    if ($yyyymm >= 201408 && $yyyymm <= 201503) {
        $rui_c_gross_profit -= 611904;
    }
    // 2013/11/07 ÄÉ²Ã 2013Ç¯10·îÅÙ ¶ÈÌ³°ÑÂ÷Èñ¡Ê²£ÀîÇÉ¸¯ÎÁ¡ËÄ´À°
    if ($yyyymm >= 201310 && $yyyymm <= 201403) {
        $rui_n_urigen += 1245035;
    }
    if ($yyyymm >= 201311 && $yyyymm <= 201403) {
        $rui_n_urigen -= 1245035;
    }
    if ($yyyymm >= 201408 && $yyyymm <= 201503) {
        $rui_n_urigen -= 841368;
    }
    $rui_n_urigen       = number_format(($rui_n_urigen / $tani), $keta);
    $rui_c_gross_profit = number_format(($rui_c_gross_profit / $tani), $keta);
}
$query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='¥ê¥Ë¥¢ÁíÍø±×'", $str_ym, $yyyymm);
if (getUniResult($query, $rui_l_gross_profit) < 1) {
    $rui_l_gross_profit         = 0 - $rui_s_gross_profit_sagaku;   // ¸¡º÷¼ºÇÔ
    $rui_lh_gross_profit        = 0;                                // ¸¡º÷¼ºÇÔ
    $rui_lh_gross_profit_sagaku = 0;                                // ¸¡º÷¼ºÇÔ
} else {
    if ($yyyymm == 200905) {
        $rui_l_gross_profit = $rui_l_gross_profit + 3100900;
    } elseif ($yyyymm == 200904) {
        $rui_l_gross_profit = $rui_l_gross_profit + 1550450;
    }
    if ($yyyymm >= 200912 && $yyyymm <= 201003) {
        $rui_l_gross_profit = $rui_l_gross_profit - 182279;
    }
    if ($yyyymm >= 201001) {
        $rui_l_gross_profit = $rui_l_gross_profit - $rui_l_kyu_kin;
        //$rui_l_gross_profit = $rui_l_gross_profit - 151313;
    }
    if ($yyyymm >= 201004 && $yyyymm <= 201103) {
        $rui_l_gross_profit = $rui_l_gross_profit - 255240;
    }
    // 2012/02/08 ÄÉ²Ã 2012Ç¯1·îÅÙ ¶ÈÌ³°ÑÂ÷Èñ¡ÊÊ¿½Ð²£ÀîÇÉ¸¯ÎÁ¡ËÄ´À°
    if ($yyyymm >= 201201 && $yyyymm <= 201203) {
        $rui_l_gross_profit -=1156130;
    }
    // 2012/03/05 ÄÉ²Ã 2012Ç¯1·îÅÙ ¶ÈÌ³°ÑÂ÷Èñ¡ÊÊ¿½Ð²£ÀîÇÉ¸¯ÎÁ¡ËÄ´À° Ìá¤·
    if ($yyyymm >= 201202 && $yyyymm <= 201203) {
        $rui_l_gross_profit +=1156130;
    }
    if ($yyyymm >= 201408 && $yyyymm <= 201503) {
        $rui_l_gross_profit = $rui_l_gross_profit - 229464;
    }
    $rui_lh_gross_profit        = $rui_l_gross_profit - $rui_s_gross_profit_sagaku - $rui_b_gross_profit_sagaku;
    $rui_lh_gross_profit_sagaku = $rui_lh_gross_profit;
    $rui_l_gross_profit         = $rui_l_gross_profit - $rui_s_gross_profit_sagaku;     // »î¸³½¤ÍýÇä¾åÁíÍø±×¤ò¥ê¥Ë¥¢¤ÎÇä¾åÁíÍø±×¤è¤ê¥Þ¥¤¥Ê¥¹
    $rui_lh_gross_profit        = number_format(($rui_lh_gross_profit / $tani), $keta);
    $rui_l_gross_profit         = number_format(($rui_l_gross_profit / $tani), $keta);
}

/********** ÈÎ´ÉÈñ¤Î¿Í·ïÈñ **********/
    ///// Åö·î
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='»î½¤¿Í·ïÈñ'", $yyyymm);
if (getUniResult($query, $s_han_jin) < 1) {
    $s_han_jin        = 0;                      // ¸¡º÷¼ºÇÔ
    $s_han_jin_sagaku = 0;
} else {
    $s_han_jin_sagaku = $s_han_jin;
    $s_han_jin        = number_format(($s_han_jin / $tani), $keta);
}
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='¾¦´É¿Í·ïÈñ'", $yyyymm);
if (getUniResult($query, $n_han_jin_sagaku) < 1) {
    $n_han_jin_sagaku = 0;                      // ¸¡º÷¼ºÇÔ
}
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='Á´ÂÎ¿Í·ïÈñ'", $yyyymm);
if (getUniResult($query, $all_han_jin) < 1) {
    $all_han_jin = 0;                           // ¸¡º÷¼ºÇÔ
} else {
    $all_han_jin = number_format(($all_han_jin / $tani), $keta);
}
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='¥«¥×¥é¾¦´É¼Ò°÷°ÄÊ¬µëÍ¿'", $yyyymm);
if (getUniResult($query, $c_allo_kin) < 1) {
    $c_allo_kin = 0;                            // ¸¡º÷¼ºÇÔ
} else {
    //$c_han_jin = number_format(($c_han_jin / $tani), $keta);
}
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='¥«¥×¥é¿Í·ïÈñ'", $yyyymm);
if (getUniResult($query, $c_han_jin) < 1) {
    $c_han_jin = 0;                             // ¸¡º÷¼ºÇÔ
} else {
    $c_han_jin = $c_han_jin - $c_allo_kin;
}
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='µ¡¹©¿Í·ïÈñ'", $yyyymm);
if (getUniResult($query, $b_han_jin) < 1) {
    $b_han_jin        = 0;    // ¸¡º÷¼ºÇÔ
    $b_han_jin_sagaku = 0;
} else {
    $b_han_jin_sagaku = $b_han_jin;
    $b_han_jin        = number_format(($b_han_jin / $tani), $keta);
}
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='¥ê¥Ë¥¢¾¦´É¼Ò°÷°ÄÊ¬µëÍ¿'", $yyyymm);
if (getUniResult($query, $l_allo_kin) < 1) {
    $l_allo_kin = 0;     // ¸¡º÷¼ºÇÔ
} else {
    //$c_han_jin = number_format(($c_han_jin / $tani), $keta);
}
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='¥ê¥Ë¥¢¿Í·ïÈñ'", $yyyymm);
if (getUniResult($query, $l_han_jin) < 1) {
    $l_han_jin         = 0 - $s_han_jin_sagaku;     // ¸¡º÷¼ºÇÔ
    $lh_han_jin        = 0;                         // ¸¡º÷¼ºÇÔ
    $lh_han_jin_sagaku = 0;                         // ¸¡º÷¼ºÇÔ
} else {
    $l_han_jin         = $l_han_jin - $l_allo_kin;
    $lh_han_jin        = $l_han_jin - $s_han_jin_sagaku - $b_han_jin_sagaku;
    $lh_han_jin_sagaku = $lh_han_jin;
    $l_han_jin         = $l_han_jin - $s_han_jin_sagaku;     // »î¸³½¤Íý¿Í·ïÈñ¤ò¥ê¥Ë¥¢¤Î¿Í·ïÈñ¤è¤ê¥Þ¥¤¥Ê¥¹
    $lh_han_jin        = number_format(($lh_han_jin / $tani), $keta);
    $l_han_jin         = number_format(($l_han_jin / $tani), $keta);
}
    ///// Åö·î ¾¦´É
$query = sprintf("select sum(orign_kin) from act_allo_history where pl_bs_ym=%d and actcod>=8101 and orign_id=670", $yyyymm);
if (getUniResult($query, $n_han_jin) < 1) {
    $n_han_jin = 0 + $n_han_jin_sagaku;         // ¸¡º÷¼ºÇÔ
    $n_han_all = $n_han_jin;
    $n_sagaku  = $n_sagaku + $n_han_jin;        // ¥«¥×¥éº¹³Û·×»»ÍÑ
} else {
    // ²¼¤Ï7·îÌ¤Ê§¤¤µìÍ¾Ê¬ÄÉ²Ã ¥Æ¥¹¥ÈÍÑ
    $n_han_jin = $n_han_jin + $n_han_jin_sagaku;
    $c_han_jin = $c_han_jin - $n_han_jin;
    $n_sagaku  = $n_sagaku + $n_han_jin;        // ¥«¥×¥éº¹³Û·×»»ÍÑ
    $n_han_jin = $n_han_jin + $c_allo_kin + $l_allo_kin;
    $n_han_all = $n_han_jin;
    $c_han_jin = number_format(($c_han_jin / $tani), $keta);
    $n_han_jin = number_format(($n_han_jin / $tani), $keta);
}

    ///// Á°·î
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='»î½¤¿Í·ïÈñ'", $p1_ym);
if (getUniResult($query, $p1_s_han_jin) < 1) {
    $p1_s_han_jin        = 0;                   // ¸¡º÷¼ºÇÔ
    $p1_s_han_jin_sagaku = 0;
} else {
    $p1_s_han_jin_sagaku = $p1_s_han_jin;
    $p1_s_han_jin        = number_format(($p1_s_han_jin / $tani), $keta);
}
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='¾¦´É¿Í·ïÈñ'", $p1_ym);
if (getUniResult($query, $p1_n_han_jin_sagaku) < 1) {
    $p1_n_han_jin_sagaku = 0;                   // ¸¡º÷¼ºÇÔ
}
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='Á´ÂÎ¿Í·ïÈñ'", $p1_ym);
if (getUniResult($query, $p1_all_han_jin) < 1) {
    $p1_all_han_jin = 0;                        // ¸¡º÷¼ºÇÔ
} else {
    $p1_all_han_jin = number_format(($p1_all_han_jin / $tani), $keta);
}
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='¥«¥×¥é¾¦´É¼Ò°÷°ÄÊ¬µëÍ¿'", $p1_ym);
if (getUniResult($query, $p1_c_allo_kin) < 1) {
    $p1_c_allo_kin = 0;                         // ¸¡º÷¼ºÇÔ
} else {
    //$c_han_jin = number_format(($c_han_jin / $tani), $keta);
}
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='¥«¥×¥é¿Í·ïÈñ'", $p1_ym);
if (getUniResult($query, $p1_c_han_jin) < 1) {
    $p1_c_han_jin = 0 - $p1_c_allo_kin;         // ¸¡º÷¼ºÇÔ
} else {
    $p1_c_han_jin = $p1_c_han_jin - $p1_c_allo_kin;
}
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='µ¡¹©¿Í·ïÈñ'", $p1_ym);
if (getUniResult($query, $p1_b_han_jin) < 1) {
    $p1_b_han_jin        = 0;    // ¸¡º÷¼ºÇÔ
    $p1_b_han_jin_sagaku = 0;
} else {
    $p1_b_han_jin_sagaku = $p1_b_han_jin;
    $p1_b_han_jin        = number_format(($p1_b_han_jin / $tani), $keta);
}
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='¥ê¥Ë¥¢¾¦´É¼Ò°÷°ÄÊ¬µëÍ¿'", $p1_ym);
if (getUniResult($query, $p1_l_allo_kin) < 1) {
    $p1_l_allo_kin = 0;                         // ¸¡º÷¼ºÇÔ
} else {
    //$c_han_jin = number_format(($c_han_jin / $tani), $keta);
}
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='¥ê¥Ë¥¢¿Í·ïÈñ'", $p1_ym);
if (getUniResult($query, $p1_l_han_jin) < 1) {
    $p1_l_han_jin         = 0 - $p1_s_han_jin_sagaku;     // ¸¡º÷¼ºÇÔ
    $p1_lh_han_jin        = 0;                            // ¸¡º÷¼ºÇÔ
    $p1_lh_han_jin_sagaku = 0;                            // ¸¡º÷¼ºÇÔ
} else {
    $p1_l_han_jin         = $p1_l_han_jin - $p1_l_allo_kin;
    $p1_lh_han_jin        = $p1_l_han_jin - $p1_s_han_jin_sagaku - $p1_b_han_jin_sagaku;
    $p1_lh_han_jin_sagaku = $p1_lh_han_jin;
    $p1_l_han_jin         = $p1_l_han_jin - $p1_s_han_jin_sagaku;     // »î¸³½¤Íý¿Í·ïÈñ¤ò¥ê¥Ë¥¢¤Î¿Í·ïÈñ¤è¤ê¥Þ¥¤¥Ê¥¹
    $p1_lh_han_jin        = number_format(($p1_lh_han_jin / $tani), $keta);
    $p1_l_han_jin         = number_format(($p1_l_han_jin / $tani), $keta);
}
    ///// Á°·î ¾¦´É
$query = sprintf("select sum(orign_kin) from act_allo_history where pl_bs_ym=%d and actcod>=8101 and orign_id=670", $p1_ym);
if (getUniResult($query, $p1_n_han_jin) < 1) {
    $p1_n_han_jin = 0 + $p1_n_han_jin_sagaku + $p1_c_allo_kin + $p1_l_allo_kin;     // ¸¡º÷¼ºÇÔ
    $p1_n_han_all = $p1_n_han_jin;
    $p1_n_sagaku  = $p1_n_sagaku + $p1_n_han_jin;       // ¥«¥×¥éº¹³Û·×»»ÍÑ
} else {
    $p1_n_han_jin = $p1_n_han_jin + $p1_n_han_jin_sagaku;
    $p1_c_han_jin = $p1_c_han_jin - $p1_n_han_jin;
    $p1_n_sagaku  = $p1_n_sagaku + $p1_n_han_jin;       // ¥«¥×¥éº¹³Û·×»»ÍÑ
    $p1_n_han_jin = $p1_n_han_jin + $p1_c_allo_kin + $p1_l_allo_kin;
    $p1_n_han_all = $p1_n_han_jin;
    $p1_c_han_jin = number_format(($p1_c_han_jin / $tani), $keta);
    $p1_n_han_jin = number_format(($p1_n_han_jin / $tani), $keta);
}

    ///// Á°Á°·î
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='»î½¤¿Í·ïÈñ'", $p2_ym);
if (getUniResult($query, $p2_s_han_jin) < 1) {
    $p2_s_han_jin        = 0;                   // ¸¡º÷¼ºÇÔ
    $p2_s_han_jin_sagaku = 0;
} else {
    $p2_s_han_jin_sagaku = $p2_s_han_jin;
    $p2_s_han_jin        = number_format(($p2_s_han_jin / $tani), $keta);
}
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='¾¦´É¿Í·ïÈñ'", $p2_ym);
if (getUniResult($query, $p2_n_han_jin_sagaku) < 1) {
    $p2_n_han_jin_sagaku = 0;                   // ¸¡º÷¼ºÇÔ
}
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='Á´ÂÎ¿Í·ïÈñ'", $p2_ym);
if (getUniResult($query, $p2_all_han_jin) < 1) {
    $p2_all_han_jin = 0;                        // ¸¡º÷¼ºÇÔ
} else {
    $p2_all_han_jin = number_format(($p2_all_han_jin / $tani), $keta);
}
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='¥«¥×¥é¾¦´É¼Ò°÷°ÄÊ¬µëÍ¿'", $p2_ym);
if (getUniResult($query, $p2_c_allo_kin) < 1) {
    $p2_c_allo_kin = 0;                         // ¸¡º÷¼ºÇÔ
} else {
    //$c_han_jin = number_format(($c_han_jin / $tani), $keta);
}
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='¥«¥×¥é¿Í·ïÈñ'", $p2_ym);
if (getUniResult($query, $p2_c_han_jin) < 1) {
    $p2_c_han_jin = 0 - $p2_c_allo_kin;         // ¸¡º÷¼ºÇÔ
} else {
    $p2_c_han_jin = $p2_c_han_jin - $p2_c_allo_kin;
}
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='µ¡¹©¿Í·ïÈñ'", $p2_ym);
if (getUniResult($query, $p2_b_han_jin) < 1) {
    $p2_b_han_jin        = 0;    // ¸¡º÷¼ºÇÔ
    $p2_b_han_jin_sagaku = 0;
} else {
    $p2_b_han_jin_sagaku = $p2_b_han_jin;
    $p2_b_han_jin        = number_format(($p2_b_han_jin / $tani), $keta);
}
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='¥ê¥Ë¥¢¾¦´É¼Ò°÷°ÄÊ¬µëÍ¿'", $p2_ym);
if (getUniResult($query, $p2_l_allo_kin) < 1) {
    $p2_l_allo_kin = 0;     // ¸¡º÷¼ºÇÔ
} else {
    //$c_han_jin = number_format(($c_han_jin / $tani), $keta);
}
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='¥ê¥Ë¥¢¿Í·ïÈñ'", $p2_ym);
if (getUniResult($query, $p2_l_han_jin) < 1) {
    $p2_l_han_jin         = 0 - $p2_s_han_jin_sagaku;     // ¸¡º÷¼ºÇÔ
    $p2_lh_han_jin        = 0;                            // ¸¡º÷¼ºÇÔ
    $p2_lh_han_jin_sagaku = 0;                            // ¸¡º÷¼ºÇÔ
} else {
    $p2_l_han_jin         = $p2_l_han_jin - $p2_l_allo_kin;
    $p2_lh_han_jin        = $p2_l_han_jin - $p2_s_han_jin_sagaku - $p2_b_han_jin_sagaku;
    $p2_lh_han_jin_sagaku = $p2_lh_han_jin;
    $p2_l_han_jin         = $p2_l_han_jin - $p2_s_han_jin_sagaku;     // »î¸³½¤Íý¿Í·ïÈñ¤ò¥ê¥Ë¥¢¤Î¿Í·ïÈñ¤è¤ê¥Þ¥¤¥Ê¥¹
    $p2_lh_han_jin        = number_format(($p2_lh_han_jin / $tani), $keta);
    $p2_l_han_jin         = number_format(($p2_l_han_jin / $tani), $keta);
}
    ///// Á°Á°·î ¾¦´É
$query = sprintf("select sum(orign_kin) from act_allo_history where pl_bs_ym=%d and actcod>=8101 and orign_id=670", $p2_ym);
if (getUniResult($query, $p2_n_han_jin) < 1) {
    $p2_n_han_jin = 0 + $p2_n_han_jin_sagaku + $p2_c_allo_kin + $p2_l_allo_kin;     // ¸¡º÷¼ºÇÔ
    $p2_n_han_all = $p2_n_han_jin;
    $p2_n_sagaku  = $p2_n_sagaku + $p2_n_han_jin;                   // ¥«¥×¥éº¹³Û·×»»ÍÑ
} else {
    $p2_n_han_jin = $p2_n_han_jin + $p2_n_han_jin_sagaku;
    $p2_c_han_jin = $p2_c_han_jin - $p2_n_han_jin;
    $p2_n_sagaku  = $p2_n_sagaku + $p2_n_han_jin;                   // ¥«¥×¥éº¹³Û·×»»ÍÑ
    $p2_n_han_jin = $p2_n_han_jin + $p2_c_allo_kin + $p2_l_allo_kin;
    $p2_n_han_all = $p2_n_han_jin;
    $p2_c_han_jin = number_format(($p2_c_han_jin / $tani), $keta);
    $p2_n_han_jin = number_format(($p2_n_han_jin / $tani), $keta);
}

    ///// º£´üÎß·×
$query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='»î½¤¿Í·ïÈñ'", $str_ym, $yyyymm);
if (getUniResult($query, $rui_s_han_jin) < 1) {
    $rui_s_han_jin        = 0;                  // ¸¡º÷¼ºÇÔ
    $rui_s_han_jin_sagaku = 0;
} else {
    $rui_s_han_jin_sagaku = $rui_s_han_jin;
    $rui_s_han_jin        = number_format(($rui_s_han_jin / $tani), $keta);
}
$query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='¾¦´É¿Í·ïÈñ'", $str_ym, $yyyymm);
if (getUniResult($query, $rui_n_han_jin_sagaku) < 1) {
    $rui_n_han_jin_sagaku = 0;                  // ¸¡º÷¼ºÇÔ
}
$query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='Á´ÂÎ¿Í·ïÈñ'", $str_ym, $yyyymm);
if (getUniResult($query, $rui_all_han_jin) < 1) {
    $rui_all_han_jin = 0;                       // ¸¡º÷¼ºÇÔ
} else {
    $rui_all_han_jin = number_format(($rui_all_han_jin / $tani), $keta);
}
$query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='¥«¥×¥é¾¦´É¼Ò°÷°ÄÊ¬µëÍ¿'", $str_ym, $yyyymm);
if (getUniResult($query, $rui_c_allo_kin) < 1) {
    $rui_c_allo_kin = 0;                        // ¸¡º÷¼ºÇÔ
} else {
    //$c_han_jin = number_format(($c_han_jin / $tani), $keta);
}
$query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='¥«¥×¥é¿Í·ïÈñ'", $str_ym, $yyyymm);
if (getUniResult($query, $rui_c_han_jin) < 1) {
    $rui_c_han_jin = 0 - $rui_c_allo_kin;       // ¸¡º÷¼ºÇÔ
} else {
    $rui_c_han_jin = $rui_c_han_jin - $rui_c_allo_kin;
}
$query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='µ¡¹©¿Í·ïÈñ'", $str_ym, $yyyymm);
if (getUniResult($query, $rui_b_han_jin) < 1) {
    $rui_b_han_jin        = 0;    // ¸¡º÷¼ºÇÔ
    $rui_b_han_jin_sagaku = 0;
} else {
    $rui_b_han_jin_sagaku = $rui_b_han_jin;
    $rui_b_han_jin        = number_format(($rui_b_han_jin / $tani), $keta);
}
$query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='¥ê¥Ë¥¢¾¦´É¼Ò°÷°ÄÊ¬µëÍ¿'", $str_ym, $yyyymm);
if (getUniResult($query, $rui_l_allo_kin) < 1) {
    $rui_l_allo_kin = 0;     // ¸¡º÷¼ºÇÔ
} else {
    //$c_han_jin = number_format(($c_han_jin / $tani), $keta);
}
$query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='¥ê¥Ë¥¢¿Í·ïÈñ'", $str_ym, $yyyymm);
if (getUniResult($query, $rui_l_han_jin) < 1) {
    $rui_l_han_jin         = 0 - $rui_s_han_jin_sagaku;   // ¸¡º÷¼ºÇÔ
    $rui_lh_han_jin        = 0;                           // ¸¡º÷¼ºÇÔ
    $rui_lh_han_jin_sagaku = 0;                           // ¸¡º÷¼ºÇÔ
} else {
    $rui_l_han_jin         = $rui_l_han_jin - $rui_l_allo_kin;
    $rui_lh_han_jin        = $rui_l_han_jin - $rui_s_han_jin_sagaku - $rui_b_han_jin_sagaku;
    $rui_lh_han_jin_sagaku = $rui_lh_han_jin;
    $rui_l_han_jin         = $rui_l_han_jin - $rui_s_han_jin_sagaku;     // »î¸³½¤Íý¿Í·ïÈñ¤ò¥ê¥Ë¥¢¤Î¿Í·ïÈñ¤è¤ê¥Þ¥¤¥Ê¥¹
    $rui_lh_han_jin        = number_format(($rui_lh_han_jin / $tani), $keta);
    $rui_l_han_jin         = number_format(($rui_l_han_jin / $tani), $keta);
}
    ///// º£´üÎß·× ¾¦´É
$query = sprintf("select sum(orign_kin) from act_allo_history where pl_bs_ym>=%d and pl_bs_ym<=%d and actcod>=8101 and orign_id=670", $str_ym, $yyyymm);
if (getUniResult($query, $rui_n_han_jin) < 1) {
    $rui_n_han_jin = 0 + $rui_n_han_jin_sagaku + $rui_c_allo_kin + $rui_l_allo_kin;     // ¸¡º÷¼ºÇÔ
    $rui_n_han_all = $rui_n_han_jin;
    $rui_n_sagaku  = $rui_n_sagaku + $rui_n_han_jin;                // ¥«¥×¥éº¹³Û·×»»ÍÑ
} else {
    // ²¼¤Ï7·îÌ¤Ê§¤¤µìÍ¾Ê¬ÄÉ²Ã ¥Æ¥¹¥ÈÍÑ
    $rui_n_han_jin = $rui_n_han_jin + $rui_n_han_jin_sagaku;
    $rui_c_han_jin = $rui_c_han_jin - $rui_n_han_jin;
    $rui_n_sagaku  = $rui_n_sagaku + $rui_n_han_jin;                // ¥«¥×¥éº¹³Û·×»»ÍÑ
    $rui_n_han_jin = $rui_n_han_jin + $rui_c_allo_kin + $rui_l_allo_kin;
    $rui_n_han_all = $rui_n_han_jin;
    $rui_c_han_jin = number_format(($rui_c_han_jin / $tani), $keta);
    $rui_n_han_jin = number_format(($rui_n_han_jin / $tani), $keta);
}

/********** ÈÎ´ÉÈñ¤Î·ÐÈñ **********/
    ///// Åö·î
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='»î½¤ÈÎ´ÉÈñ·ÐÈñ'", $yyyymm);
if (getUniResult($query, $s_han_kei) < 1) {
    $s_han_kei        = 0;                  // ¸¡º÷¼ºÇÔ
    $s_han_kei_sagaku = 0;
} else {
    $s_han_kei_sagaku = $s_han_kei;
    $s_han_kei        = number_format(($s_han_kei / $tani), $keta);
}
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='¾¦´ÉÈÎ´ÉÈñ·ÐÈñ'", $yyyymm);
if (getUniResult($query, $n_han_kei_sagaku) < 1) {
    $n_han_kei_sagaku = 0;                  // ¸¡º÷¼ºÇÔ
}
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='Á´ÂÎ·ÐÈñ'", $yyyymm);
if (getUniResult($query, $all_han_kei) < 1) {
    $all_han_kei = 0;                       // ¸¡º÷¼ºÇÔ
} else {
    $all_han_kei = number_format(($all_han_kei / $tani), $keta);
}
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='¥«¥×¥é·ÐÈñ'", $yyyymm);
if (getUniResult($query, $c_han_kei) < 1) {
    $c_han_kei = 0;                         // ¸¡º÷¼ºÇÔ
} else {
    //$c_han_kei = number_format(($c_han_kei / $tani), $keta);
}
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='µ¡¹©ÈÎ´ÉÈñ·ÐÈñ'", $yyyymm);
if (getUniResult($query, $b_han_kei) < 1) {
    $b_han_kei        = 0;    // ¸¡º÷¼ºÇÔ
    $b_han_kei_sagaku = 0;
} else {
    $b_han_kei_sagaku = $b_han_kei;
    $b_han_kei        = number_format(($b_han_kei / $tani), $keta);
}
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='¥ê¥Ë¥¢·ÐÈñ'", $yyyymm);
if (getUniResult($query, $l_han_kei) < 1) {
    $l_han_kei         = 0 - $s_han_kei_sagaku;     // ¸¡º÷¼ºÇÔ
    $lh_han_kei        = 0;                         // ¸¡º÷¼ºÇÔ
    $lh_han_kei_sagaku = 0;                         // ¸¡º÷¼ºÇÔ
} else {
    $lh_han_kei        = $l_han_kei - $s_han_kei_sagaku - $b_han_kei_sagaku;
    $lh_han_kei_sagaku = $lh_han_kei;
    $l_han_kei         = $l_han_kei - $s_han_kei_sagaku;     // »î¸³½¤ÍýÈÎ´ÉÈñ·ÐÈñ¤ò¥ê¥Ë¥¢¤ÎÈÎ´ÉÈñ·ÐÈñ¤è¤ê¥Þ¥¤¥Ê¥¹
    $lh_han_kei        = number_format(($lh_han_kei / $tani), $keta);
    $l_han_kei         = number_format(($l_han_kei / $tani), $keta);
}
    ///// Åö·î ¾¦´É
$query = sprintf("select sum(orign_kin) from act_allo_history where pl_bs_ym=%d and actcod>=7501 and actcod<=8000 and orign_id=670", $yyyymm);
if (getUniResult($query, $n_han_kei) < 1) {
    $n_han_kei = 0 + $n_han_kei_sagaku;     // ¸¡º÷¼ºÇÔ
    $n_han_all = $n_han_all + $n_han_kei;
    $n_sagaku  = $n_sagaku + $n_han_kei;    // ¥«¥×¥éº¹³Û·×»»ÍÑ
} else {
    $n_han_kei = $n_han_kei + $n_han_kei_sagaku;
    $n_han_all = $n_han_all + $n_han_kei;
    $c_han_kei = $c_han_kei - $n_han_kei;
    $n_sagaku  = $n_sagaku + $n_han_kei;    // ¥«¥×¥éº¹³Û·×»»ÍÑ
    $c_han_kei = number_format(($c_han_kei / $tani), $keta);
    $n_han_kei = number_format(($n_han_kei / $tani), $keta);
}

    ///// Á°·î
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='»î½¤ÈÎ´ÉÈñ·ÐÈñ'", $p1_ym);
if (getUniResult($query, $p1_s_han_kei) < 1) {
    $p1_s_han_kei        = 0;               // ¸¡º÷¼ºÇÔ
    $p1_s_han_kei_sagaku = 0;
} else {
    $p1_s_han_kei_sagaku = $p1_s_han_kei;
    $p1_s_han_kei        = number_format(($p1_s_han_kei / $tani), $keta);
}
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='¾¦´ÉÈÎ´ÉÈñ·ÐÈñ'", $p1_ym);
if (getUniResult($query, $p1_n_han_kei_sagaku) < 1) {
    $p1_n_han_kei_sagaku = 0;               // ¸¡º÷¼ºÇÔ
}
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='Á´ÂÎ·ÐÈñ'", $p1_ym);
if (getUniResult($query, $p1_all_han_kei) < 1) {
    $p1_all_han_kei = 0;                    // ¸¡º÷¼ºÇÔ
} else {
    $p1_all_han_kei = number_format(($p1_all_han_kei / $tani), $keta);
}
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='¥«¥×¥é·ÐÈñ'", $p1_ym);
if (getUniResult($query, $p1_c_han_kei) < 1) {
    $p1_c_han_kei = 0;                      // ¸¡º÷¼ºÇÔ
} else {
    //$p1_c_han_kei = number_format(($p1_c_han_kei / $tani), $keta);
}
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='µ¡¹©ÈÎ´ÉÈñ·ÐÈñ'", $p1_ym);
if (getUniResult($query, $p1_b_han_kei) < 1) {
    $p1_b_han_kei        = 0;    // ¸¡º÷¼ºÇÔ
    $p1_b_han_kei_sagaku = 0;
} else {
    $p1_b_han_kei_sagaku = $p1_b_han_kei;
    $p1_b_han_kei        = number_format(($p1_b_han_kei / $tani), $keta);
}
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='¥ê¥Ë¥¢·ÐÈñ'", $p1_ym);
if (getUniResult($query, $p1_l_han_kei) < 1) {
    $p1_l_han_kei         = 0 - $p1_s_han_kei_sagaku;     // ¸¡º÷¼ºÇÔ
    $p1_lh_han_kei        = 0;                            // ¸¡º÷¼ºÇÔ
    $p1_lh_han_kei_sagaku = 0;                            // ¸¡º÷¼ºÇÔ
} else {
    $p1_lh_han_kei        = $p1_l_han_kei - $p1_s_han_kei_sagaku - $p1_b_han_kei_sagaku;
    $p1_lh_han_kei_sagaku = $p1_lh_han_kei;
    $p1_l_han_kei         = $p1_l_han_kei - $p1_s_han_kei_sagaku;     // »î¸³½¤ÍýÈÎ´ÉÈñ·ÐÈñ¤ò¥ê¥Ë¥¢¤ÎÈÎ´ÉÈñ·ÐÈñ¤è¤ê¥Þ¥¤¥Ê¥¹
    $p1_lh_han_kei        = number_format(($p1_lh_han_kei / $tani), $keta);
    $p1_l_han_kei         = number_format(($p1_l_han_kei / $tani), $keta);
}
    ///// Á°·î ¾¦´É
$query = sprintf("select sum(orign_kin) from act_allo_history where pl_bs_ym=%d and actcod>=7501 and actcod<=8000 and orign_id=670", $p1_ym);
if (getUniResult($query, $p1_n_han_kei) < 1) {
    $p1_n_han_kei = 0 + $p1_n_han_kei_sagaku;       // ¸¡º÷¼ºÇÔ
    $p1_n_han_all = $p1_n_han_all + $p1_n_han_kei;
    $p1_n_sagaku  = $p1_n_sagaku + $p1_n_han_kei;   // ¥«¥×¥éº¹³Û·×»»ÍÑ
} else {
    $p1_n_han_kei = $p1_n_han_kei + $p1_n_han_kei_sagaku;
    $p1_n_han_all = $p1_n_han_all + $p1_n_han_kei;
    $p1_c_han_kei = $p1_c_han_kei - $p1_n_han_kei;
    $p1_n_sagaku  = $p1_n_sagaku + $p1_n_han_kei;   // ¥«¥×¥éº¹³Û·×»»ÍÑ
    $p1_c_han_kei = number_format(($p1_c_han_kei / $tani), $keta);
    $p1_n_han_kei = number_format(($p1_n_han_kei / $tani), $keta);
}

    ///// Á°Á°·î
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='»î½¤ÈÎ´ÉÈñ·ÐÈñ'", $p2_ym);
if (getUniResult($query, $p2_s_han_kei) < 1) {
    $p2_s_han_kei        = 0;               // ¸¡º÷¼ºÇÔ
    $p2_s_han_kei_sagaku = 0;
} else {
    $p2_s_han_kei_sagaku = $p2_s_han_kei;
    $p2_s_han_kei        = number_format(($p2_s_han_kei / $tani), $keta);
}
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='¾¦´ÉÈÎ´ÉÈñ·ÐÈñ'", $p2_ym);
if (getUniResult($query, $p2_n_han_kei_sagaku) < 1) {
    $p2_n_han_kei_sagaku = 0;               // ¸¡º÷¼ºÇÔ
}
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='Á´ÂÎ·ÐÈñ'", $p2_ym);
if (getUniResult($query, $p2_all_han_kei) < 1) {
    $p2_all_han_kei = 0;                    // ¸¡º÷¼ºÇÔ
} else {
    $p2_all_han_kei = number_format(($p2_all_han_kei / $tani), $keta);
}
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='¥«¥×¥é·ÐÈñ'", $p2_ym);
if (getUniResult($query, $p2_c_han_kei) < 1) {
    $p2_c_han_kei = 0;                      // ¸¡º÷¼ºÇÔ
} else {
    //$p2_c_han_kei = number_format(($p2_c_han_kei / $tani), $keta);
}
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='µ¡¹©ÈÎ´ÉÈñ·ÐÈñ'", $p2_ym);
if (getUniResult($query, $p2_b_han_kei) < 1) {
    $p2_b_han_kei        = 0;    // ¸¡º÷¼ºÇÔ
    $p2_b_han_kei_sagaku = 0;
} else {
    $p2_b_han_kei_sagaku = $p2_b_han_kei;
    $p2_b_han_kei        = number_format(($p2_b_han_kei / $tani), $keta);
}
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='¥ê¥Ë¥¢·ÐÈñ'", $p2_ym);
if (getUniResult($query, $p2_l_han_kei) < 1) {
    $p2_l_han_kei         = 0 - $p2_s_han_kei_sagaku;     // ¸¡º÷¼ºÇÔ
    $p2_lh_han_kei        = 0;                            // ¸¡º÷¼ºÇÔ
    $p2_lh_han_kei_sagaku = 0;                            // ¸¡º÷¼ºÇÔ
} else {
    $p2_lh_han_kei        = $p2_l_han_kei - $p2_s_han_kei_sagaku - $p2_b_han_kei_sagaku;
    $p2_lh_han_kei_sagaku = $p2_lh_han_kei;
    $p2_l_han_kei         = $p2_l_han_kei - $p2_s_han_kei_sagaku;     // »î¸³½¤ÍýÈÎ´ÉÈñ·ÐÈñ¤ò¥ê¥Ë¥¢¤ÎÈÎ´ÉÈñ·ÐÈñ¤è¤ê¥Þ¥¤¥Ê¥¹
    $p2_lh_han_kei        = number_format(($p2_lh_han_kei / $tani), $keta);
    $p2_l_han_kei         = number_format(($p2_l_han_kei / $tani), $keta);
}
    ///// Á°Á°·î ¾¦´É
$query = sprintf("select sum(orign_kin) from act_allo_history where pl_bs_ym=%d and actcod>=7501 and actcod<=8000 and orign_id=670", $p2_ym);
if (getUniResult($query, $p2_n_han_kei) < 1) {
    $p2_n_han_kei = 0 + $p2_n_han_kei_sagaku;       // ¸¡º÷¼ºÇÔ
    $p2_n_han_all = $p2_n_han_all + $p2_n_han_kei;
    $p2_n_sagaku  = $p2_n_sagaku + $p2_n_han_kei;   // ¥«¥×¥éº¹³Û·×»»ÍÑ
} else {
    $p2_n_han_kei = $p2_n_han_kei + $p2_n_han_kei_sagaku;
    $p2_n_han_all = $p2_n_han_all + $p2_n_han_kei;
    $p2_c_han_kei = $p2_c_han_kei - $p2_n_han_kei;
    $p2_n_sagaku  = $p2_n_sagaku + $p2_n_han_kei;   // ¥«¥×¥éº¹³Û·×»»ÍÑ
    $p2_c_han_kei = number_format(($p2_c_han_kei / $tani), $keta);
    $p2_n_han_kei = number_format(($p2_n_han_kei / $tani), $keta);
}

    ///// º£´üÎß·×
$query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='»î½¤ÈÎ´ÉÈñ·ÐÈñ'", $str_ym, $yyyymm);
if (getUniResult($query, $rui_s_han_kei) < 1) {
    $rui_s_han_kei        = 0;                      // ¸¡º÷¼ºÇÔ
    $rui_s_han_kei_sagaku = 0;
} else {
    $rui_s_han_kei_sagaku = $rui_s_han_kei;
    $rui_s_han_kei        = number_format(($rui_s_han_kei / $tani), $keta);
}
$query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='¾¦´ÉÈÎ´ÉÈñ·ÐÈñ'", $str_ym, $yyyymm);
if (getUniResult($query, $rui_n_han_kei_sagaku) < 1) {
    $rui_n_han_kei_sagaku = 0;                      // ¸¡º÷¼ºÇÔ
}
$query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='Á´ÂÎ·ÐÈñ'", $str_ym, $yyyymm);
if (getUniResult($query, $rui_all_han_kei) < 1) {
    $rui_all_han_kei = 0;                           // ¸¡º÷¼ºÇÔ
} else {
    $rui_all_han_kei = number_format(($rui_all_han_kei / $tani), $keta);
}
$query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='¥«¥×¥é·ÐÈñ'", $str_ym, $yyyymm);
if (getUniResult($query, $rui_c_han_kei) < 1) {
    $rui_c_han_kei = 0;                             // ¸¡º÷¼ºÇÔ
} else {
    //$rui_c_han_kei = number_format(($rui_c_han_kei / $tani), $keta);
}
$query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='µ¡¹©ÈÎ´ÉÈñ·ÐÈñ'", $str_ym, $yyyymm);
if (getUniResult($query, $rui_b_han_kei) < 1) {
    $rui_b_han_kei        = 0;    // ¸¡º÷¼ºÇÔ
    $rui_b_han_kei_sagaku = 0;
} else {
    $rui_b_han_kei_sagaku = $rui_b_han_kei;
    $rui_b_han_kei        = number_format(($rui_b_han_kei / $tani), $keta);
}
$query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='¥ê¥Ë¥¢·ÐÈñ'", $str_ym, $yyyymm);
if (getUniResult($query, $rui_l_han_kei) < 1) {
    $rui_l_han_kei         = 0 - $rui_s_han_kei_sagaku;   // ¸¡º÷¼ºÇÔ
    $rui_lh_han_kei        = 0;                           // ¸¡º÷¼ºÇÔ
    $rui_lh_han_kei_sagaku = 0;                           // ¸¡º÷¼ºÇÔ
} else {
    $rui_lh_han_kei        = $rui_l_han_kei - $rui_s_han_kei_sagaku - $rui_b_han_kei_sagaku;
    $rui_lh_han_kei_sagaku = $rui_lh_han_kei;
    $rui_l_han_kei         = $rui_l_han_kei - $rui_s_han_kei_sagaku;     // »î¸³½¤ÍýÈÎ´ÉÈñ·ÐÈñ¤ò¥ê¥Ë¥¢¤ÎÈÎ´ÉÈñ·ÐÈñ¤è¤ê¥Þ¥¤¥Ê¥¹
    $rui_lh_han_kei        = number_format(($rui_lh_han_kei / $tani), $keta);
    $rui_l_han_kei         = number_format(($rui_l_han_kei / $tani), $keta);
}
    ///// º£´üÎß·× ¾¦´É
$query = sprintf("select sum(orign_kin) from act_allo_history where pl_bs_ym>=%d and pl_bs_ym<=%d and actcod>=7501 and actcod<=8000 and orign_id=670", $str_ym, $yyyymm);
if (getUniResult($query, $rui_n_han_kei) < 1) {
    $rui_n_han_kei = 0 + $rui_n_han_kei_sagaku;         // ¸¡º÷¼ºÇÔ
    $rui_n_han_all = $rui_n_han_all + $rui_n_han_kei;
    $rui_n_sagaku  = $rui_n_sagaku + $rui_n_han_kei;    // ¥«¥×¥éº¹³Û·×»»ÍÑ
} else {
    $rui_n_han_kei = $rui_n_han_kei + $rui_n_han_kei_sagaku;
    $rui_n_han_all = $rui_n_han_all + $rui_n_han_kei;
    $rui_c_han_kei = $rui_c_han_kei - $rui_n_han_kei;
    $rui_n_sagaku  = $rui_n_sagaku + $rui_n_han_kei;    // ¥«¥×¥éº¹³Û·×»»ÍÑ
    $rui_c_han_kei = number_format(($rui_c_han_kei / $tani), $keta);
    $rui_n_han_kei = number_format(($rui_n_han_kei / $tani), $keta);
}

/********** ÈÎ´ÉÈñ¤Î¹ç·× **********/
    ///// Åö·î
    ///// »î¸³¡¦½¤Íý
    $s_han_all        = $s_han_jin_sagaku + $s_han_kei_sagaku;
    $s_han_all_sagaku = $s_han_all;
    $s_han_all        = number_format(($s_han_all / $tani), $keta);
    ///// µ¡¹©
    $b_han_all        = $b_han_jin_sagaku + $b_han_kei_sagaku;
    $b_han_all_sagaku = $b_han_all;
    $b_han_all        = number_format(($b_han_all / $tani), $keta);
    ///// CL
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='Á´ÂÎÈÎ´ÉÈñ'", $yyyymm);
if (getUniResult($query, $all_han_all) < 1) {
    $all_han_all = 0;                           // ¸¡º÷¼ºÇÔ
} else {
    $all_han_all = number_format(($all_han_all / $tani), $keta);
}
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='¥«¥×¥éÈÎ´ÉÈñ'", $yyyymm);
if (getUniResult($query, $c_han_all) < 1) {
    $c_han_all = 0;                             // ¸¡º÷¼ºÇÔ
} else {
    $c_han_all = $c_han_all - $n_han_all + $c_allo_kin + $l_allo_kin - $c_allo_kin;
    $c_han_all = number_format(($c_han_all / $tani), $keta);
}
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='¥ê¥Ë¥¢ÈÎ´ÉÈñ'", $yyyymm);
if (getUniResult($query, $l_han_all) < 1) {
    $l_han_all         = 0 - $s_han_all_sagaku;     // ¸¡º÷¼ºÇÔ
    $lh_han_all        = 0;                         // ¸¡º÷¼ºÇÔ
    $lh_han_all_sagaku = 0;                         // ¸¡º÷¼ºÇÔ
} else {
    $l_han_all         = $l_han_all - $l_allo_kin;
    $lh_han_all        = $l_han_all - $s_han_all_sagaku - $b_han_all_sagaku;
    $lh_han_all_sagaku = $lh_han_all;
    $l_han_all         = $l_han_all - $s_han_all_sagaku;     // »î¸³½¤ÍýÈÎ´ÉÈñ¹ç·×¤ò¥ê¥Ë¥¢¤ÎÈÎ´ÉÈñ¹ç·×¤è¤ê¥Þ¥¤¥Ê¥¹
    $lh_han_all        = number_format(($lh_han_all / $tani), $keta);
    $l_han_all         = number_format(($l_han_all / $tani), $keta);
}

    ///// Á°·î
    ///// »î¸³¡¦½¤Íý
    $p1_s_han_all        = $p1_s_han_jin_sagaku + $p1_s_han_kei_sagaku;
    $p1_s_han_all_sagaku = $p1_s_han_all;
    $p1_s_han_all        = number_format(($p1_s_han_all / $tani), $keta);
    ///// µ¡¹©
    $p1_b_han_all        = $p1_b_han_jin_sagaku + $p1_b_han_kei_sagaku;
    $p1_b_han_all_sagaku = $p1_b_han_all;
    $p1_b_han_all        = number_format(($p1_b_han_all / $tani), $keta);
    ///// CL
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='Á´ÂÎÈÎ´ÉÈñ'", $p1_ym);
if (getUniResult($query, $p1_all_han_all) < 1) {
    $p1_all_han_all = 0;                        // ¸¡º÷¼ºÇÔ
} else {
    $p1_all_han_all = number_format(($p1_all_han_all / $tani), $keta);
}
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='¥«¥×¥éÈÎ´ÉÈñ'", $p1_ym);
if (getUniResult($query, $p1_c_han_all) < 1) {
    $p1_c_han_all = 0;                          // ¸¡º÷¼ºÇÔ
} else {
    $p1_c_han_all = $p1_c_han_all - $p1_n_han_all + $p1_c_allo_kin + $p1_l_allo_kin - $p1_c_allo_kin;
    $p1_c_han_all = number_format(($p1_c_han_all / $tani), $keta);
}
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='¥ê¥Ë¥¢ÈÎ´ÉÈñ'", $p1_ym);
if (getUniResult($query, $p1_l_han_all) < 1) {
    $p1_l_han_all         = 0 - $p1_s_han_all_sagaku;     // ¸¡º÷¼ºÇÔ
    $p1_lh_han_all        = 0;                            // ¸¡º÷¼ºÇÔ
    $p1_lh_han_all_sagaku = 0;                            // ¸¡º÷¼ºÇÔ
} else {
    $p1_l_han_all         = $p1_l_han_all - $p1_l_allo_kin;
    $p1_lh_han_all        = $p1_l_han_all - $p1_s_han_all_sagaku - $p1_b_han_all_sagaku;
    $p1_lh_han_all_sagaku = $p1_lh_han_all;
    $p1_l_han_all         = $p1_l_han_all - $p1_s_han_all_sagaku;     // »î¸³½¤ÍýÈÎ´ÉÈñ¹ç·×¤ò¥ê¥Ë¥¢¤ÎÈÎ´ÉÈñ¹ç·×¤è¤ê¥Þ¥¤¥Ê¥¹
    $p1_lh_han_all        = number_format(($p1_lh_han_all / $tani), $keta);
    $p1_l_han_all         = number_format(($p1_l_han_all / $tani), $keta);
}

    ///// Á°Á°·î
    ///// »î¸³¡¦½¤Íý
    $p2_s_han_all        = $p2_s_han_jin_sagaku + $p2_s_han_kei_sagaku;
    $p2_s_han_all_sagaku = $p2_s_han_all;
    $p2_s_han_all        = number_format(($p2_s_han_all / $tani), $keta);
    ///// µ¡¹©
    $p2_b_han_all        = $p2_b_han_jin_sagaku + $p2_b_han_kei_sagaku;
    $p2_b_han_all_sagaku = $p2_b_han_all;
    $p2_b_han_all        = number_format(($p2_b_han_all / $tani), $keta);
    ///// CL
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='Á´ÂÎÈÎ´ÉÈñ'", $p2_ym);
if (getUniResult($query, $p2_all_han_all) < 1) {
    $p2_all_han_all = 0;                        // ¸¡º÷¼ºÇÔ
} else {
    $p2_all_han_all = number_format(($p2_all_han_all / $tani), $keta);
}
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='¥«¥×¥éÈÎ´ÉÈñ'", $p2_ym);
if (getUniResult($query, $p2_c_han_all) < 1) {
    $p2_c_han_all = 0;                          // ¸¡º÷¼ºÇÔ
} else {
    $p2_c_han_all = $p2_c_han_all - $p2_n_han_all + $p2_c_allo_kin + $p2_l_allo_kin - $p2_c_allo_kin;
    $p2_c_han_all = number_format(($p2_c_han_all / $tani), $keta);
}
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='¥ê¥Ë¥¢ÈÎ´ÉÈñ'", $p2_ym);
if (getUniResult($query, $p2_l_han_all) < 1) {
    $p2_l_han_all         = 0 - $p2_s_han_all_sagaku;     // ¸¡º÷¼ºÇÔ
    $p2_lh_han_all        = 0;                            // ¸¡º÷¼ºÇÔ
    $p2_lh_han_all_sagaku = 0;                            // ¸¡º÷¼ºÇÔ
} else {
    $p2_l_han_all         = $p2_l_han_all - $p2_l_allo_kin;
    $p2_lh_han_all        = $p2_l_han_all - $p2_s_han_all_sagaku - $p2_b_han_all_sagaku;
    $p2_lh_han_all_sagaku = $p2_lh_han_all;
    $p2_l_han_all         = $p2_l_han_all - $p2_s_han_all_sagaku;     // »î¸³½¤ÍýÈÎ´ÉÈñ¹ç·×¤ò¥ê¥Ë¥¢¤ÎÈÎ´ÉÈñ¹ç·×¤è¤ê¥Þ¥¤¥Ê¥¹
    $p2_lh_han_all        = number_format(($p2_lh_han_all / $tani), $keta);
    $p2_l_han_all         = number_format(($p2_l_han_all / $tani), $keta);
}

    ///// º£´üÎß·×
    ///// »î¸³¡¦½¤Íý
    $rui_s_han_all        = $rui_s_han_jin_sagaku + $rui_s_han_kei_sagaku;
    $rui_s_han_all_sagaku = $rui_s_han_all;
    $rui_s_han_all        = number_format(($rui_s_han_all / $tani), $keta);
    ///// µ¡¹©
    $rui_b_han_all        = $rui_b_han_jin_sagaku + $rui_b_han_kei_sagaku;
    $rui_b_han_all_sagaku = $rui_b_han_all;
    $rui_b_han_all        = number_format(($rui_b_han_all / $tani), $keta);
    ///// CL
$query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='Á´ÂÎÈÎ´ÉÈñ'", $str_ym, $yyyymm);
if (getUniResult($query, $rui_all_han_all) < 1) {
    $rui_all_han_all = 0;                       // ¸¡º÷¼ºÇÔ
} else {
    $rui_all_han_all = number_format(($rui_all_han_all / $tani), $keta);
}
$query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='¥«¥×¥éÈÎ´ÉÈñ'", $str_ym, $yyyymm);
if (getUniResult($query, $rui_c_han_all) < 1) {
    $rui_c_han_all = 0;                         // ¸¡º÷¼ºÇÔ
} else {
    $rui_c_han_all = $rui_c_han_all - $rui_n_han_all + $rui_c_allo_kin + $rui_l_allo_kin - $rui_c_allo_kin;
    $rui_c_han_all = number_format(($rui_c_han_all / $tani), $keta);
}
$query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='¥ê¥Ë¥¢ÈÎ´ÉÈñ'", $str_ym, $yyyymm);
if (getUniResult($query, $rui_l_han_all) < 1) {
    $rui_l_han_all         = 0 - $rui_s_han_all_sagaku;   // ¸¡º÷¼ºÇÔ
    $rui_lh_han_all        = 0;                           // ¸¡º÷¼ºÇÔ
    $rui_lh_han_all_sagaku = 0;                           // ¸¡º÷¼ºÇÔ
} else {
    $rui_l_han_all         = $rui_l_han_all - $rui_l_allo_kin;
    $rui_lh_han_all        = $rui_l_han_all - $rui_s_han_all_sagaku - $rui_b_han_all_sagaku;
    $rui_lh_han_all_sagaku = $rui_lh_han_all;
    $rui_l_han_all         = $rui_l_han_all - $rui_s_han_all_sagaku;     // »î¸³½¤ÍýÈÎ´ÉÈñ¹ç·×¤ò¥ê¥Ë¥¢¤ÎÈÎ´ÉÈñ¹ç·×¤è¤ê¥Þ¥¤¥Ê¥¹
    $rui_lh_han_all        = number_format(($rui_lh_han_all / $tani), $keta);
    $rui_l_han_all         = number_format(($rui_l_han_all / $tani), $keta);
}

/********** ±Ä¶ÈÍø±× **********/
    ///// ¾¦´É
// 2013/11/07 ÄÉ²Ã 2013Ç¯10·îÅÙ ¶ÈÌ³°ÑÂ÷Èñ¡Ê²£ÀîÇÉ¸¯ÎÁ¡ËÄ´À°
if ($p2_ym == 201310) {
    $p2_n_gross_profit -= 1245035;
}
if ($p2_ym == 201311) {
    $p2_n_gross_profit += 1245035;
}
if ($p2_ym == 201408) {
    $p2_n_gross_profit += 841368;
}
$p2_n_ope_profit    = $p2_n_gross_profit - $p2_n_han_all;
$p2_n_han_all       = number_format(($p2_n_han_all / $tani), $keta);
$p2_n_gross_profit  = number_format(($p2_n_gross_profit / $tani), $keta);

// 2013/11/07 ÄÉ²Ã 2013Ç¯10·îÅÙ ¶ÈÌ³°ÑÂ÷Èñ¡Ê²£ÀîÇÉ¸¯ÎÁ¡ËÄ´À°
if ($p1_ym == 201310) {
    $p1_n_gross_profit -= 1245035;
}
if ($p1_ym == 201311) {
    $p1_n_gross_profit += 1245035;
}
if ($p1_ym == 201408) {
    $p1_n_gross_profit += 841368;
}
$p1_n_ope_profit    = $p1_n_gross_profit - $p1_n_han_all;
$p1_n_han_all       = number_format(($p1_n_han_all / $tani), $keta);
$p1_n_gross_profit  = number_format(($p1_n_gross_profit / $tani), $keta);

// 2013/11/07 ÄÉ²Ã 2013Ç¯10·îÅÙ ¶ÈÌ³°ÑÂ÷Èñ¡Ê²£ÀîÇÉ¸¯ÎÁ¡ËÄ´À°
if ($yyyymm == 201310) {
    $n_gross_profit -= 1245035;
}
if ($yyyymm == 201311) {
    $n_gross_profit += 1245035;
}
if ($yyyymm == 201408) {
    $n_gross_profit += 841368;
}
$n_ope_profit       = $n_gross_profit - $n_han_all;
$n_han_all          = number_format(($n_han_all / $tani), $keta);
$n_gross_profit     = number_format(($n_gross_profit / $tani), $keta);

// 2013/11/07 ÄÉ²Ã 2013Ç¯10·îÅÙ ¶ÈÌ³°ÑÂ÷Èñ¡Ê²£ÀîÇÉ¸¯ÎÁ¡ËÄ´À°
if ($yyyymm >= 201310 && $yyyymm <= 201403) {
    $rui_n_gross_profit -= 1245035;
}
if ($yyyymm >= 201311 && $yyyymm <= 201403) {
    $rui_n_gross_profit += 1245035;
}
if ($yyyymm >= 201408 && $yyyymm <= 201503) {
    $rui_n_gross_profit += 841368;
}
$rui_n_ope_profit   = $rui_n_gross_profit - $rui_n_han_all;
$rui_n_han_all      = number_format(($rui_n_han_all / $tani), $keta);
$rui_n_gross_profit = number_format(($rui_n_gross_profit / $tani), $keta);
    ///// »î¸³¡¦½¤Íý
$p2_s_ope_profit         = $p2_s_gross_profit_sagaku - $p2_s_han_all_sagaku;
$p2_s_ope_profit_sagaku  = $p2_s_ope_profit;
$p2_s_ope_profit         = $p2_s_ope_profit + $p2_sc_uri_sagaku - $p2_sc_metarial_sagaku;       // ¥«¥×¥é»î¸³½¤Íý¤ò²ÃÌ£¡Êsagaku¤Î¸å¡Ý¥ê¥Ë¥¢¤«¤é¥Þ¥¤¥Ê¥¹¤·¤Æ¤·¤Þ¤¦°Ù¡Ë
if ($p2_ym == 200912) {
    $p2_s_ope_profit = $p2_s_ope_profit + 1409708;
}
if ($p2_ym >= 201001) {
    $p2_s_ope_profit = $p2_s_ope_profit + $p2_s_kyu_kei - $p2_s_kyu_kin;  // ÅºÅÄ¤µ¤ó¤ÎµëÍ¿¤òC¡¦L¤Ï35%¡£»î¸³½¤Íý¤Ë30%¿¶Ê¬
    //$p2_s_ope_profit = $p2_s_ope_profit + 432323 - 129697;  // ÅºÅÄ¤µ¤ó¤ÎµëÍ¿¤òC¡¦L¤Ï35%¡£»î¸³½¤Íý¤Ë30%¿¶Ê¬
}
$p2_s_ope_profit         = number_format(($p2_s_ope_profit / $tani), $keta);

$p1_s_ope_profit         = $p1_s_gross_profit_sagaku - $p1_s_han_all_sagaku;
$p1_s_ope_profit_sagaku  = $p1_s_ope_profit;
$p1_s_ope_profit         = $p1_s_ope_profit + $p1_sc_uri_sagaku - $p1_sc_metarial_sagaku;       // ¥«¥×¥é»î¸³½¤Íý¤ò²ÃÌ£¡Êsagaku¤Î¸å¡Ý¥ê¥Ë¥¢¤«¤é¥Þ¥¤¥Ê¥¹¤·¤Æ¤·¤Þ¤¦°Ù¡Ë
if ($p1_ym == 200912) {
    $p1_s_ope_profit = $p1_s_ope_profit + 1409708;
}
if ($p1_ym >= 201001) {
    $p1_s_ope_profit = $p1_s_ope_profit + $p1_s_kyu_kei - $p1_s_kyu_kin;  // ÅºÅÄ¤µ¤ó¤ÎµëÍ¿¤òC¡¦L¤Ï35%¡£»î¸³½¤Íý¤Ë30%¿¶Ê¬
    //$p1_s_ope_profit = $p1_s_ope_profit + 432323 - 129697;  // ÅºÅÄ¤µ¤ó¤ÎµëÍ¿¤òC¡¦L¤Ï35%¡£»î¸³½¤Íý¤Ë30%¿¶Ê¬
}
$p1_s_ope_profit         = number_format(($p1_s_ope_profit / $tani), $keta);

$s_ope_profit            = $s_gross_profit_sagaku - $s_han_all_sagaku;
$s_ope_profit_sagaku     = $s_ope_profit;
$s_ope_profit            = $s_ope_profit + $sc_uri_sagaku - $sc_metarial_sagaku;                // ¥«¥×¥é»î¸³½¤Íý¤ò²ÃÌ£¡Êsagaku¤Î¸å¡Ý¥ê¥Ë¥¢¤«¤é¥Þ¥¤¥Ê¥¹¤·¤Æ¤·¤Þ¤¦°Ù¡Ë
if ($yyyymm == 200912) {
    $s_ope_profit = $s_ope_profit + 1409708;
}
if ($yyyymm >= 201001) {
    $s_ope_profit = $s_ope_profit + $s_kyu_kei - $s_kyu_kin;  // ÅºÅÄ¤µ¤ó¤ÎµëÍ¿¤òC¡¦L¤Ï35%¡£»î¸³½¤Íý¤Ë30%¿¶Ê¬
    //$s_ope_profit = $s_ope_profit + 432323 - 129697;  // ÅºÅÄ¤µ¤ó¤ÎµëÍ¿¤òC¡¦L¤Ï35%¡£»î¸³½¤Íý¤Ë30%¿¶Ê¬
}
$s_ope_profit            = number_format(($s_ope_profit / $tani), $keta);

$rui_s_ope_profit        = $rui_s_gross_profit_sagaku - $rui_s_han_all_sagaku;
$rui_s_ope_profit_sagaku = $rui_s_ope_profit;
$rui_s_ope_profit        = $rui_s_ope_profit + $rui_sc_uri_sagaku - $rui_sc_metarial_sagaku;    // ¥«¥×¥é»î¸³½¤Íý¤ò²ÃÌ£¡Êsagaku¤Î¸å¡Ý¥ê¥Ë¥¢¤«¤é¥Þ¥¤¥Ê¥¹¤·¤Æ¤·¤Þ¤¦°Ù¡Ë
if ($yyyymm >= 200912 && $yyyymm <= 201003) {
    $rui_s_ope_profit = $rui_s_ope_profit + 1409708;
}
if ($yyyymm >= 201001) {
    $rui_s_ope_profit = $rui_s_ope_profit + $rui_s_kyu_kei - $rui_s_kyu_kin;  // ÅºÅÄ¤µ¤ó¤ÎµëÍ¿¤òC¡¦L¤Ï35%¡£»î¸³½¤Íý¤Ë30%¿¶Ê¬
    //$rui_s_ope_profit = $rui_s_ope_profit + 432323 - 129697;  // ÅºÅÄ¤µ¤ó¤ÎµëÍ¿¤òC¡¦L¤Ï35%¡£»î¸³½¤Íý¤Ë30%¿¶Ê¬
}
$rui_s_ope_profit        = number_format(($rui_s_ope_profit / $tani), $keta);
    ///// µ¡¹©
$p2_b_ope_profit         = $p2_b_gross_profit_sagaku - $p2_b_han_all_sagaku;
$p2_b_ope_profit_sagaku  = $p2_b_ope_profit;
$p2_b_ope_profit         = number_format(($p2_b_ope_profit / $tani), $keta);

$p1_b_ope_profit         = $p1_b_gross_profit_sagaku - $p1_b_han_all_sagaku;
$p1_b_ope_profit_sagaku  = $p1_b_ope_profit;
$p1_b_ope_profit         = number_format(($p1_b_ope_profit / $tani), $keta);

$b_ope_profit            = $b_gross_profit_sagaku - $b_han_all_sagaku;
$b_ope_profit_sagaku     = $b_ope_profit;
$b_ope_profit            = number_format(($b_ope_profit / $tani), $keta);

$rui_b_ope_profit        = $rui_b_gross_profit_sagaku - $rui_b_han_all_sagaku;
$rui_b_ope_profit_sagaku = $rui_b_ope_profit;
$rui_b_ope_profit        = number_format(($rui_b_ope_profit / $tani), $keta);

    ///// Åö·î
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='Á´ÂÎ±Ä¶ÈÍø±×'", $yyyymm);
if (getUniResult($query, $all_ope_profit) < 1) {
    $all_ope_profit = 0;                        // ¸¡º÷¼ºÇÔ
} else {
    if ($yyyymm == 200906) {
        $all_ope_profit = $all_ope_profit + $n_uri_sagaku - 3100900;
    } elseif ($yyyymm == 200905) {
        $all_ope_profit = $all_ope_profit + $n_uri_sagaku + 1550450;
    } elseif ($yyyymm == 200904) {
        $all_ope_profit = $all_ope_profit + $n_uri_sagaku + 1550450;
    } else {
        $all_ope_profit = $all_ope_profit + $n_uri_sagaku;
    }
    // 2012/02/08 ÄÉ²Ã 2012Ç¯1·îÅÙ ¶ÈÌ³°ÑÂ÷Èñ¡ÊÊ¿½Ð²£ÀîÇÉ¸¯ÎÁ¡ËÄ´À°
    if ($yyyymm == 201201) {
        $all_ope_profit -=1156130;
    }
    // 2012/03/05 ÄÉ²Ã 2012Ç¯1·îÅÙ ¶ÈÌ³°ÑÂ÷Èñ¡ÊÊ¿½Ð²£ÀîÇÉ¸¯ÎÁ¡ËÄ´À° Ìá¤·
    if ($yyyymm == 201202) {
        $all_ope_profit +=1156130;
    }
    $all_ope_profit = number_format(($all_ope_profit / $tani), $keta);
}
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='¥«¥×¥é±Ä¶ÈÍø±×'", $yyyymm);
if (getUniResult($query, $c_ope_profit) < 1) {
    $c_ope_profit = 0;                          // ¸¡º÷¼ºÇÔ
    $c_ope_profit_temp = 0;
} else {
    $c_ope_profit = $c_ope_profit + $n_sagaku + $c_allo_kin - $sc_uri_sagaku + $sc_metarial_sagaku; // ¥«¥×¥é»î¸³½¤Íý¤ò²ÃÌ£
    if ($yyyymm == 200912) {
        $c_ope_profit = $c_ope_profit - 1227429;
    }
    if ($yyyymm >= 201001) {
        $c_ope_profit = $c_ope_profit - $c_kyu_kin; // ÅºÅÄ¤µ¤ó¤ÎµëÍ¿¤òC¡¦L¤Ï35%¡£»î¸³½¤Íý¤Ë30%¿¶Ê¬
        //$c_ope_profit = $c_ope_profit - 151313; // ÅºÅÄ¤µ¤ó¤ÎµëÍ¿¤òC¡¦L¤Ï35%¡£»î¸³½¤Íý¤Ë30%¿¶Ê¬
    }
    // 2013/11/07 ÄÉ²Ã 2013Ç¯10·îÅÙ ¶ÈÌ³°ÑÂ÷Èñ¡Ê²£ÀîÇÉ¸¯ÎÁ¡ËÄ´À°
    if ($yyyymm == 201310) {
        $c_ope_profit += 1245035;
    }
    if ($yyyymm == 201311) {
        $c_ope_profit -= 1245035;
    }
    if ($yyyymm == 201408) {
        $c_ope_profit +=229464;
    }
    $c_ope_profit_temp = $c_ope_profit;
    $c_ope_profit      = number_format(($c_ope_profit / $tani), $keta);
}
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='¥ê¥Ë¥¢±Ä¶ÈÍø±×'", $yyyymm);
if (getUniResult($query, $l_ope_profit) < 1) {
    $l_ope_profit         = 0 - $s_ope_profit_sagaku;     // ¸¡º÷¼ºÇÔ
    $lh_ope_profit        = 0;                            // ¸¡º÷¼ºÇÔ
    $lh_ope_profit_sagaku = 0;                            // ¸¡º÷¼ºÇÔ
} else {
    if ($yyyymm == 200906) {
        $l_ope_profit = $l_ope_profit - 3100900;
    } elseif ($yyyymm == 200905) {
        $l_ope_profit = $l_ope_profit + 1550450;
    } elseif ($yyyymm == 200904) {
        $l_ope_profit = $l_ope_profit + 1550450;
    }
    $l_ope_profit         = $l_ope_profit  + $l_allo_kin;
    if ($yyyymm == 200912) {
        $l_ope_profit = $l_ope_profit - 182279;
    }
    if ($yyyymm >= 201001) {
        $l_ope_profit = $l_ope_profit - $l_kyu_kin; // ÅºÅÄ¤µ¤ó¤ÎµëÍ¿¤òC¡¦L¤Ï35%¡£»î¸³½¤Íý¤Ë30%¿¶Ê¬
        //$l_ope_profit = $l_ope_profit - 151313; // ÅºÅÄ¤µ¤ó¤ÎµëÍ¿¤òC¡¦L¤Ï35%¡£»î¸³½¤Íý¤Ë30%¿¶Ê¬
    }
    if ($yyyymm == 201004) {
        $l_ope_profit = $l_ope_profit - 255240;
    }
    // 2012/02/08 ÄÉ²Ã 2012Ç¯1·îÅÙ ¶ÈÌ³°ÑÂ÷Èñ¡ÊÊ¿½Ð²£ÀîÇÉ¸¯ÎÁ¡ËÄ´À°
    if ($yyyymm == 201201) {
        $l_ope_profit -=1156130;
    }
    // 2012/03/05 ÄÉ²Ã 2012Ç¯1·îÅÙ ¶ÈÌ³°ÑÂ÷Èñ¡ÊÊ¿½Ð²£ÀîÇÉ¸¯ÎÁ¡ËÄ´À° Ìá¤·
    if ($yyyymm == 201202) {
        $l_ope_profit +=1156130;
    }
    if ($yyyymm == 201408) {
        $l_ope_profit -=229464;
    }
    $lh_ope_profit        = $l_ope_profit - $s_ope_profit_sagaku - $b_ope_profit_sagaku;
    $lh_ope_profit_sagaku = $lh_ope_profit;
    $l_ope_profit         = $l_ope_profit - $s_ope_profit_sagaku;     // »î¸³½¤Íý±Ä¶ÈÍø±×¤ò¥ê¥Ë¥¢¤Î±Ä¶ÈÍø±×¤è¤ê¥Þ¥¤¥Ê¥¹
    $lh_ope_profit        = number_format(($lh_ope_profit / $tani), $keta);
    $l_ope_profit         = number_format(($l_ope_profit / $tani), $keta);
}
    ///// Á°·î
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='Á´ÂÎ±Ä¶ÈÍø±×'", $p1_ym);
if (getUniResult($query, $p1_all_ope_profit) < 1) {
    $p1_all_ope_profit = 0;                     // ¸¡º÷¼ºÇÔ
} else {
    if ($p1_ym == 200906) {
        $p1_all_ope_profit = $p1_all_ope_profit + $p1_n_uri_sagaku - 3100900;
    } elseif ($p1_ym == 200905) {
        $p1_all_ope_profit = $p1_all_ope_profit + $p1_n_uri_sagaku + 1550450;
    } elseif ($p1_ym == 200904) {
        $p1_all_ope_profit = $p1_all_ope_profit + $p1_n_uri_sagaku + 1550450;
    } else {
        $p1_all_ope_profit = $p1_all_ope_profit + $p1_n_uri_sagaku;
    }
    // 2012/02/08 ÄÉ²Ã 2012Ç¯1·îÅÙ ¶ÈÌ³°ÑÂ÷Èñ¡ÊÊ¿½Ð²£ÀîÇÉ¸¯ÎÁ¡ËÄ´À°
    if ($p1_ym == 201201) {
        $p1_all_ope_profit -=1156130;
    }
    // 2012/03/05 ÄÉ²Ã 2012Ç¯1·îÅÙ ¶ÈÌ³°ÑÂ÷Èñ¡ÊÊ¿½Ð²£ÀîÇÉ¸¯ÎÁ¡ËÄ´À° Ìá¤·
    if ($p1_ym == 201202) {
        $p1_all_ope_profit +=1156130;
    }
    $p1_all_ope_profit = number_format(($p1_all_ope_profit / $tani), $keta);
}
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='¥«¥×¥é±Ä¶ÈÍø±×'", $p1_ym);
if (getUniResult($query, $p1_c_ope_profit) < 1) {
    $p1_c_ope_profit = 0;                       // ¸¡º÷¼ºÇÔ
} else {
    $p1_c_ope_profit = $p1_c_ope_profit + $p1_n_sagaku + $p1_c_allo_kin - $p1_sc_uri_sagaku + $p1_sc_metarial_sagaku; // ¥«¥×¥é»î¸³½¤Íý¤ò²ÃÌ£
    if ($p1_ym == 200912) {
        $p1_c_ope_profit = $p1_c_ope_profit - 1227429;
    }
    if ($p1_ym >= 201001) {
        $p1_c_ope_profit = $p1_c_ope_profit - $p1_c_kyu_kin;   // ÅºÅÄ¤µ¤ó¤ÎµëÍ¿¤òC¡¦L¤Ï35%¡£»î¸³½¤Íý¤Ë30%¿¶Ê¬
        //$p1_c_ope_profit = $p1_c_ope_profit - 151313;   // ÅºÅÄ¤µ¤ó¤ÎµëÍ¿¤òC¡¦L¤Ï35%¡£»î¸³½¤Íý¤Ë30%¿¶Ê¬
    }
    // 2013/11/07 ÄÉ²Ã 2013Ç¯10·îÅÙ ¶ÈÌ³°ÑÂ÷Èñ¡Ê²£ÀîÇÉ¸¯ÎÁ¡ËÄ´À°
    if ($p1_ym == 201310) {
        $p1_c_ope_profit += 1245035;
    }
    if ($p1_ym == 201311) {
        $p1_c_ope_profit -= 1245035;
    }
    if ($p1_ym == 201408) {
        $p1_c_ope_profit +=229464;
    }
    $p1_c_ope_profit_temp = $p1_c_ope_profit;
    $p1_c_ope_profit = number_format(($p1_c_ope_profit / $tani), $keta);
}
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='¥ê¥Ë¥¢±Ä¶ÈÍø±×'", $p1_ym);
if (getUniResult($query, $p1_l_ope_profit) < 1) {
    $p1_l_ope_profit         = 0 - $p1_s_ope_profit_sagaku;     // ¸¡º÷¼ºÇÔ
    $p1_lh_ope_profit        = 0;                               // ¸¡º÷¼ºÇÔ
    $p1_lh_ope_profit_sagaku = 0;                               // ¸¡º÷¼ºÇÔ
} else {
    if ($p1_ym == 200906) {
        $p1_l_ope_profit = $p1_l_ope_profit - 3100900;
    } elseif ($p1_ym == 200905) {
        $p1_l_ope_profit = $p1_l_ope_profit + 1550450;
    } elseif ($p1_ym == 200904) {
        $p1_l_ope_profit = $p1_l_ope_profit + 1550450;
    }
    if ($p1_ym == 200912) {
        $p1_l_ope_profit = $p1_l_ope_profit - 182279;
    }
    if ($p1_ym >= 201001) {
        $p1_l_ope_profit = $p1_l_ope_profit - $p1_l_kyu_kin;   // ÅºÅÄ¤µ¤ó¤ÎµëÍ¿¤òC¡¦L¤Ï35%¡£»î¸³½¤Íý¤Ë30%¿¶Ê¬
        //$p1_l_ope_profit = $p1_l_ope_profit - 151313;   // ÅºÅÄ¤µ¤ó¤ÎµëÍ¿¤òC¡¦L¤Ï35%¡£»î¸³½¤Íý¤Ë30%¿¶Ê¬
    }
    if ($p1_ym == 201004) {
        $p1_l_ope_profit = $p1_l_ope_profit - 255240;
    }
    // 2012/02/08 ÄÉ²Ã 2012Ç¯1·îÅÙ ¶ÈÌ³°ÑÂ÷Èñ¡ÊÊ¿½Ð²£ÀîÇÉ¸¯ÎÁ¡ËÄ´À°
    if ($p1_ym == 201201) {
        $p1_l_ope_profit -=1156130;
    }
    // 2012/03/05 ÄÉ²Ã 2012Ç¯1·îÅÙ ¶ÈÌ³°ÑÂ÷Èñ¡ÊÊ¿½Ð²£ÀîÇÉ¸¯ÎÁ¡ËÄ´À° Ìá¤·
    if ($p1_ym == 201202) {
        $p1_l_ope_profit +=1156130;
    }
    if ($p1_ym == 201408) {
        $p1_l_ope_profit -=229464;
    }
    $p1_l_ope_profit         = $p1_l_ope_profit  + $p1_l_allo_kin;
    $p1_lh_ope_profit        = $p1_l_ope_profit - $p1_s_ope_profit_sagaku - $p1_b_ope_profit_sagaku;
    $p1_lh_ope_profit_sagaku = $p1_lh_ope_profit;
    $p1_l_ope_profit         = $p1_l_ope_profit - $p1_s_ope_profit_sagaku;     // »î¸³½¤Íý±Ä¶ÈÍø±×¤ò¥ê¥Ë¥¢¤Î±Ä¶ÈÍø±×¤è¤ê¥Þ¥¤¥Ê¥¹
    $p1_lh_ope_profit        = number_format(($p1_lh_ope_profit / $tani), $keta);
    $p1_l_ope_profit         = number_format(($p1_l_ope_profit / $tani), $keta);
}
    ///// Á°Á°·î
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='Á´ÂÎ±Ä¶ÈÍø±×'", $p2_ym);
if (getUniResult($query, $p2_all_ope_profit) < 1) {
    $p2_all_ope_profit = 0;                     // ¸¡º÷¼ºÇÔ
} else {
    if ($p2_ym == 200906) {
        $p2_all_ope_profit = $p2_all_ope_profit + $p2_n_uri_sagaku - 3100900;
    } elseif ($p2_ym == 200905) {
        $p2_all_ope_profit = $p2_all_ope_profit + $p2_n_uri_sagaku + 1550450;
    } elseif ($p2_ym == 200904) {
        $p2_all_ope_profit = $p2_all_ope_profit + $p2_n_uri_sagaku + 1550450;
    } else {
        $p2_all_ope_profit = $p2_all_ope_profit + $p2_n_uri_sagaku;
    }
    // 2012/02/08 ÄÉ²Ã 2012Ç¯1·îÅÙ ¶ÈÌ³°ÑÂ÷Èñ¡ÊÊ¿½Ð²£ÀîÇÉ¸¯ÎÁ¡ËÄ´À°
    if ($p2_ym == 201201) {
        $p2_all_ope_profit -=1156130;
    }
    // 2012/03/05 ÄÉ²Ã 2012Ç¯1·îÅÙ ¶ÈÌ³°ÑÂ÷Èñ¡ÊÊ¿½Ð²£ÀîÇÉ¸¯ÎÁ¡ËÄ´À° Ìá¤·
    if ($p2_ym == 201202) {
        $p2_all_ope_profit +=1156130;
    }
    $p2_all_ope_profit = number_format(($p2_all_ope_profit / $tani), $keta);
}
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='¥«¥×¥é±Ä¶ÈÍø±×'", $p2_ym);
if (getUniResult($query, $p2_c_ope_profit) < 1) {
    $p2_c_ope_profit = 0;                       // ¸¡º÷¼ºÇÔ
} else {
    $p2_c_ope_profit = $p2_c_ope_profit + $p2_n_sagaku + $p2_c_allo_kin - $p2_sc_uri_sagaku + $p2_sc_metarial_sagaku; // ¥«¥×¥é»î¸³½¤Íý¤ò²ÃÌ£
    if ($p2_ym == 200912) {
        $p2_c_ope_profit = $p2_c_ope_profit - 1227429;
    }
    if ($p2_ym >= 201001) {
        $p2_c_ope_profit = $p2_c_ope_profit - $p2_c_kyu_kin;   // ÅºÅÄ¤µ¤ó¤ÎµëÍ¿¤òC¡¦L¤Ï35%¡£»î¸³½¤Íý¤Ë30%¿¶Ê¬
        //$p2_c_ope_profit = $p2_c_ope_profit - 151313;   // ÅºÅÄ¤µ¤ó¤ÎµëÍ¿¤òC¡¦L¤Ï35%¡£»î¸³½¤Íý¤Ë30%¿¶Ê¬
    }
    // 2013/11/07 ÄÉ²Ã 2013Ç¯10·îÅÙ ¶ÈÌ³°ÑÂ÷Èñ¡Ê²£ÀîÇÉ¸¯ÎÁ¡ËÄ´À°
    if ($p2_ym == 201310) {
        $p2_c_ope_profit += 1245035;
    }
    if ($p2_ym == 201311) {
        $p2_c_ope_profit -= 1245035;
    }
    if ($p2_ym == 201408) {
        $p2_c_ope_profit +=229464;
    }
    $p2_c_ope_profit_temp = $p2_c_ope_profit;
    $p2_c_ope_profit = number_format(($p2_c_ope_profit / $tani), $keta);
}
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='¥ê¥Ë¥¢±Ä¶ÈÍø±×'", $p2_ym);
if (getUniResult($query, $p2_l_ope_profit) < 1) {
    $p2_l_ope_profit         = 0 - $p2_s_ope_profit_sagaku;     // ¸¡º÷¼ºÇÔ
    $p2_lh_ope_profit        = 0;                               // ¸¡º÷¼ºÇÔ
    $p2_lh_ope_profit_sagaku = 0;                               // ¸¡º÷¼ºÇÔ
} else {
    if ($p2_ym == 200906) {
        $p2_l_ope_profit = $p2_l_ope_profit - 3100900;
    } elseif ($p2_ym == 200905) {
        $p2_l_ope_profit = $p2_l_ope_profit + 1550450;
    } elseif ($p2_ym == 200904) {
        $p2_l_ope_profit = $p2_l_ope_profit + 1550450;
    }
    if ($p2_ym == 200912) {
        $p2_l_ope_profit = $p2_l_ope_profit - 182279;
    }
    if ($p2_ym >= 201001) {
        $p2_l_ope_profit = $p2_l_ope_profit - $p2_l_kyu_kin;   // ÅºÅÄ¤µ¤ó¤ÎµëÍ¿¤òC¡¦L¤Ï35%¡£»î¸³½¤Íý¤Ë30%¿¶Ê¬
        //$p2_l_ope_profit = $p2_l_ope_profit - 151313;   // ÅºÅÄ¤µ¤ó¤ÎµëÍ¿¤òC¡¦L¤Ï35%¡£»î¸³½¤Íý¤Ë30%¿¶Ê¬
    }
    if ($p2_ym == 201004) {
        $p2_l_ope_profit = $p2_l_ope_profit - 255240;
    }
    // 2012/02/08 ÄÉ²Ã 2012Ç¯1·îÅÙ ¶ÈÌ³°ÑÂ÷Èñ¡ÊÊ¿½Ð²£ÀîÇÉ¸¯ÎÁ¡ËÄ´À°
    if ($p2_ym == 201201) {
        $p2_l_ope_profit -=1156130;
    }
    // 2012/03/05 ÄÉ²Ã 2012Ç¯1·îÅÙ ¶ÈÌ³°ÑÂ÷Èñ¡ÊÊ¿½Ð²£ÀîÇÉ¸¯ÎÁ¡ËÄ´À° Ìá¤·
    if ($p2_ym == 201202) {
        $p2_l_ope_profit +=1156130;
    }
    if ($p2_ym == 201408) {
        $p2_l_ope_profit -=229464;
    }
    $p2_l_ope_profit         = $p2_l_ope_profit  + $p2_l_allo_kin;
    $p2_lh_ope_profit        = $p2_l_ope_profit - $p2_s_ope_profit_sagaku - $p2_b_ope_profit_sagaku;
    $p2_lh_ope_profit_sagaku = $p2_lh_ope_profit;
    $p2_l_ope_profit         = $p2_l_ope_profit - $p2_s_ope_profit_sagaku;     // »î¸³½¤Íý±Ä¶ÈÍø±×¤ò¥ê¥Ë¥¢¤Î±Ä¶ÈÍø±×¤è¤ê¥Þ¥¤¥Ê¥¹
    $p2_lh_ope_profit        = number_format(($p2_lh_ope_profit / $tani), $keta);
    $p2_l_ope_profit         = number_format(($p2_l_ope_profit / $tani), $keta);
}
    ///// º£´üÎß·×
$query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='Á´ÂÎ±Ä¶ÈÍø±×'", $str_ym, $yyyymm);
if (getUniResult($query, $rui_all_ope_profit) < 1) {
    $rui_all_ope_profit = 0;                    // ¸¡º÷¼ºÇÔ
} else {
    if ($yyyymm == 200905) {
        $rui_all_ope_profit = $rui_all_ope_profit + 3100900;
    } elseif ($yyyymm == 200904) {
        $rui_all_ope_profit = $rui_all_ope_profit + 1550450;
    }
    // 2012/02/08 ÄÉ²Ã 2012Ç¯1·îÅÙ ¶ÈÌ³°ÑÂ÷Èñ¡ÊÊ¿½Ð²£ÀîÇÉ¸¯ÎÁ¡ËÄ´À°
    if ($yyyymm >= 201201 && $yyyymm <= 201203) {
        $rui_all_ope_profit -=1156130;
    }
    // 2012/03/05 ÄÉ²Ã 2012Ç¯1·îÅÙ ¶ÈÌ³°ÑÂ÷Èñ¡ÊÊ¿½Ð²£ÀîÇÉ¸¯ÎÁ¡ËÄ´À° Ìá¤·
    if ($yyyymm >= 201202 && $yyyymm <= 201203) {
        $rui_all_ope_profit +=1156130;
    }
    $rui_all_ope_profit = $rui_all_ope_profit + $rui_n_uri_sagaku;
    $rui_all_ope_profit = number_format(($rui_all_ope_profit / $tani), $keta);
}
$query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='¥«¥×¥é±Ä¶ÈÍø±×'", $str_ym, $yyyymm);
if (getUniResult($query, $rui_c_ope_profit) < 1) {
    $rui_c_ope_profit = 0;                      // ¸¡º÷¼ºÇÔ
} else {
    $rui_c_ope_profit = $rui_c_ope_profit + $rui_n_sagaku + $rui_c_allo_kin - $rui_sc_uri_sagaku + $rui_sc_metarial_sagaku; // ¥«¥×¥é»î¸³½¤Íý¤ò²ÃÌ£
    if ($yyyymm >= 200912 && $yyyymm <= 201003) {
        $rui_c_ope_profit = $rui_c_ope_profit - 1227429;
    }
    if ($yyyymm >= 201001) {
        $rui_c_ope_profit = $rui_c_ope_profit - $rui_c_kyu_kin; // ÅºÅÄ¤µ¤ó¤ÎµëÍ¿¤òC¡¦L¤Ï35%¡£»î¸³½¤Íý¤Ë30%¿¶Ê¬
        //$rui_c_ope_profit = $rui_c_ope_profit - 151313; // ÅºÅÄ¤µ¤ó¤ÎµëÍ¿¤òC¡¦L¤Ï35%¡£»î¸³½¤Íý¤Ë30%¿¶Ê¬
    }
    // 2013/11/07 ÄÉ²Ã 2013Ç¯10·îÅÙ ¶ÈÌ³°ÑÂ÷Èñ¡Ê²£ÀîÇÉ¸¯ÎÁ¡ËÄ´À°
    if ($yyyymm >= 201310 && $yyyymm <= 201403) {
        $rui_c_ope_profit += 1245035;
    }
    if ($yyyymm >= 201311 && $yyyymm <= 201403) {
        $rui_c_ope_profit -= 1245035;
    }
    if ($yyyymm >= 201408 && $yyyymm <= 201503) {
        $rui_c_ope_profit +=229464;
    }
    $rui_c_ope_profit = number_format(($rui_c_ope_profit / $tani), $keta);
}
$query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='¥ê¥Ë¥¢±Ä¶ÈÍø±×'", $str_ym, $yyyymm);
if (getUniResult($query, $rui_l_ope_profit) < 1) {
    $rui_l_ope_profit         = 0 - $rui_s_ope_profit_sagaku;   // ¸¡º÷¼ºÇÔ
    $rui_lh_ope_profit        = 0;                              // ¸¡º÷¼ºÇÔ
    $rui_lh_ope_profit_sagaku = 0;                              // ¸¡º÷¼ºÇÔ
    $rui_l_ope_profit_temp = 0;
} else {
    if ($yyyymm == 200905) {
        $rui_l_ope_profit = $rui_l_ope_profit + 3100900;
    } elseif ($yyyymm == 200904) {
        $rui_l_ope_profit = $rui_l_ope_profit + 1550450;
    }
    if ($yyyymm >= 200912 && $yyyymm <= 201003) {
        $rui_l_ope_profit = $rui_l_ope_profit - 182279;
    }
    if ($yyyymm >= 201001) {
        $rui_l_ope_profit = $rui_l_ope_profit - $rui_l_kyu_kin; // ÅºÅÄ¤µ¤ó¤ÎµëÍ¿¤òC¡¦L¤Ï35%¡£»î¸³½¤Íý¤Ë30%¿¶Ê¬
        //$rui_l_ope_profit = $rui_l_ope_profit - 151313; // ÅºÅÄ¤µ¤ó¤ÎµëÍ¿¤òC¡¦L¤Ï35%¡£»î¸³½¤Íý¤Ë30%¿¶Ê¬
    }
    if ($yyyymm >= 201004 && $yyyymm <= 201103) {
        $rui_l_ope_profit = $rui_l_ope_profit - 255240;
    }
    // 2012/02/08 ÄÉ²Ã 2012Ç¯1·îÅÙ ¶ÈÌ³°ÑÂ÷Èñ¡ÊÊ¿½Ð²£ÀîÇÉ¸¯ÎÁ¡ËÄ´À°
    if ($yyyymm >= 201201 && $yyyymm <= 201203) {
        $rui_l_ope_profit -=1156130;
    }
    // 2012/03/05 ÄÉ²Ã 2012Ç¯1·îÅÙ ¶ÈÌ³°ÑÂ÷Èñ¡ÊÊ¿½Ð²£ÀîÇÉ¸¯ÎÁ¡ËÄ´À° Ìá¤·
    if ($yyyymm >= 201202 && $yyyymm <= 201203) {
        $rui_l_ope_profit +=1156130;
    }
    if ($yyyymm >= 201408 && $yyyymm <= 201503) {
        $rui_l_ope_profit = $rui_l_ope_profit - 229464;
    }
    $rui_l_ope_profit         = $rui_l_ope_profit  + $rui_l_allo_kin;
    $rui_lh_ope_profit        = $rui_l_ope_profit - $rui_s_ope_profit_sagaku - $rui_b_ope_profit_sagaku;
    $rui_lh_ope_profit_sagaku = $rui_lh_ope_profit;
    $rui_l_ope_profit         = $rui_l_ope_profit - $rui_s_ope_profit_sagaku;     // »î¸³½¤Íý±Ä¶ÈÍø±×¤ò¥ê¥Ë¥¢¤Î±Ä¶ÈÍø±×¤è¤ê¥Þ¥¤¥Ê¥¹
    $rui_l_ope_profit_temp = $rui_l_ope_profit;
    $rui_lh_ope_profit        = number_format(($rui_lh_ope_profit / $tani), $keta);
    $rui_l_ope_profit         = number_format(($rui_l_ope_profit / $tani), $keta);
}

/********** ±Ä¶È³°¼ý±×¤Î¶ÈÌ³°ÑÂ÷¼ýÆþ **********/
    ///// Åö·î
if ($yyyymm >= 201001) {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='¾¦´É¶ÈÌ³°ÑÂ÷¼ýÆþºÆ·×»»'", $yyyymm);
    if (getUniResult($query, $n_gyoumu) < 1) {
        $n_gyoumu      = 0;                       // ¸¡º÷¼ºÇÔ
        $n_gyoumu_temp = 0;
    } else {
        if ($yyyymm == 201001) {
            $n_gyoumu = $n_gyoumu + 63096;
        }
        $n_gyoumu_temp = $n_gyoumu;
    }
} else {
    $n_gyoumu     = 0;
}
if ($yyyymm >= 201001) {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='»î½¤¶ÈÌ³°ÑÂ÷¼ýÆþºÆ·×»»'", $yyyymm);
} else {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='»î½¤¶ÈÌ³°ÑÂ÷¼ýÆþ'", $yyyymm);
}
if (getUniResult($query, $s_gyoumu) < 1) {
    $s_gyoumu        = 0;                       // ¸¡º÷¼ºÇÔ
    $s_gyoumu_sagaku = 0;
} else {
    $s_gyoumu_sagaku = $s_gyoumu;
    if ($yyyymm == 200912) {
        $s_gyoumu = $s_gyoumu - 722;
    }
    if ($yyyymm == 201001) {
        $s_gyoumu = $s_gyoumu + 29125;
    }
    $s_gyoumu        = number_format(($s_gyoumu / $tani), $keta);
}
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='Á´ÂÎ¶ÈÌ³°ÑÂ÷¼ýÆþ'", $yyyymm);
if (getUniResult($query, $all_gyoumu) < 1) {
    $all_gyoumu = 0;                            // ¸¡º÷¼ºÇÔ
} else {
    if ($yyyymm == 200906) {
        $all_gyoumu = $all_gyoumu + 3100900;
    } elseif ($yyyymm == 200905) {
        $all_gyoumu = $all_gyoumu - 1550450;
    } elseif ($yyyymm == 200904) {
        $all_gyoumu = $all_gyoumu - 1550450;
    }
    if ($yyyymm == 200912) {
        $all_gyoumu = $all_gyoumu - 466000;
    }
    if ($yyyymm == 201001) {
        $all_gyoumu = $all_gyoumu + 466000;
    }
    $all_gyoumu = number_format(($all_gyoumu / $tani), $keta);
}
if ($yyyymm >= 201001) {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='¥«¥×¥é¶ÈÌ³°ÑÂ÷¼ýÆþºÆ·×»»'", $yyyymm);
} else {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='¥«¥×¥é¶ÈÌ³°ÑÂ÷¼ýÆþ'", $yyyymm);
}
if (getUniResult($query, $c_gyoumu) < 1) {
    $c_gyoumu = 0;                              // ¸¡º÷¼ºÇÔ
} else {
    if ($yyyymm == 200912) {
        $c_gyoumu = $c_gyoumu - 389809;
    }
    if ($yyyymm == 201001) {
        $c_gyoumu = $c_gyoumu + 315529;
    }
    $c_gyoumu = number_format(($c_gyoumu / $tani), $keta);
}
if ($yyyymm >= 201001) {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='µ¡¹©¶ÈÌ³°ÑÂ÷¼ýÆþºÆ·×»»'", $yyyymm);
} else {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='µ¡¹©¶ÈÌ³°ÑÂ÷¼ýÆþ'", $yyyymm);
}
if (getUniResult($query, $b_gyoumu) < 1) {
    $b_gyoumu = 0;    // ¸¡º÷¼ºÇÔ
    $b_gyoumu_sagaku = 0;
} else {
    if ($yyyymm == 200912) {
        $b_gyoumu = $b_gyoumu - 4931;
    }
    if ($yyyymm == 201001) {
        $b_gyoumu = $b_gyoumu + 4852;
    }
    $b_gyoumu_sagaku = $b_gyoumu;
    $b_gyoumu = number_format(($b_gyoumu / $tani), $keta);
}
if ($yyyymm >= 201001) {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='¥ê¥Ë¥¢¶ÈÌ³°ÑÂ÷¼ýÆþºÆ·×»»'", $yyyymm);
} else {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='¥ê¥Ë¥¢¶ÈÌ³°ÑÂ÷¼ýÆþ'", $yyyymm);
}
if (getUniResult($query, $l_gyoumu) < 1) {
    $l_gyoumu = 0 - $s_gyoumu_sagaku;     // ¸¡º÷¼ºÇÔ
    $lh_gyoumu = 0;     // ¸¡º÷¼ºÇÔ
    $lh_gyoumu_sagaku = 0;     // ¸¡º÷¼ºÇÔ
} else {
    if ($yyyymm == 200906) {
        $l_gyoumu = $l_gyoumu + 3100900;
    } elseif ($yyyymm == 200905) {
        $l_gyoumu = $l_gyoumu - 1550450;
    } elseif ($yyyymm == 200904) {
        $l_gyoumu = $l_gyoumu - 1550450;
    }
    if ($yyyymm == 200912) {
        $l_gyoumu = $l_gyoumu - 76191;
    }
    if ($yyyymm == 201001) {
        $l_gyoumu = $l_gyoumu + 58250;
    }
    if ($yyyymm >= 201001) {
        $l_gyoumu  = $l_gyoumu + $s_gyoumu_sagaku;
    }
    $lh_gyoumu = $l_gyoumu - $s_gyoumu_sagaku - $b_gyoumu_sagaku;
    $lh_gyoumu_sagaku = $lh_gyoumu;
    $l_gyoumu         = $l_gyoumu - $s_gyoumu_sagaku;     // »î¸³½¤Íý¶ÈÌ³°ÑÂ÷¼ýÆþ¤ò¥ê¥Ë¥¢¤Î¶ÈÌ³°ÑÂ÷¼ýÆþ¤è¤ê¥Þ¥¤¥Ê¥¹
    $lh_gyoumu = number_format(($lh_gyoumu / $tani), $keta);
    $l_gyoumu = number_format(($l_gyoumu / $tani), $keta);
}
    ///// Á°·î
if ($p1_ym >= 201001) {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='¾¦´É¶ÈÌ³°ÑÂ÷¼ýÆþºÆ·×»»'", $p1_ym);
    if (getUniResult($query, $p1_n_gyoumu) < 1) {
        $p1_n_gyoumu      = 0;                       // ¸¡º÷¼ºÇÔ
        $p1_n_gyoumu_temp = 0;
    } else {
        if ($p1_ym == 201001) {
            $p1_n_gyoumu = $p1_n_gyoumu + 63096;
        }
        $p1_n_gyoumu_temp = $p1_n_gyoumu;
    }
} else {
    $p1_n_gyoumu     = 0;
}
if ($p1_ym >= 201001) {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='»î½¤¶ÈÌ³°ÑÂ÷¼ýÆþºÆ·×»»'", $p1_ym);
} else {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='»î½¤¶ÈÌ³°ÑÂ÷¼ýÆþ'", $p1_ym);
}
if (getUniResult($query, $p1_s_gyoumu) < 1) {
    $p1_s_gyoumu        = 0;                       // ¸¡º÷¼ºÇÔ
    $p1_s_gyoumu_sagaku = 0;
} else {
    $p1_s_gyoumu_sagaku = $p1_s_gyoumu;
    if ($p1_ym == 200912) {
        $p1_s_gyoumu = $p1_s_gyoumu - 722;
    }
    if ($p1_ym == 201001) {
        $p1_s_gyoumu = $p1_s_gyoumu + 29125;
    }
    $p1_s_gyoumu        = number_format(($p1_s_gyoumu / $tani), $keta);
}
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='Á´ÂÎ¶ÈÌ³°ÑÂ÷¼ýÆþ'", $p1_ym);
if (getUniResult($query, $p1_all_gyoumu) < 1) {
    $p1_all_gyoumu = 0;                         // ¸¡º÷¼ºÇÔ
} else {
    if ($p1_ym == 200906) {
        $p1_all_gyoumu = $p1_all_gyoumu + 3100900;
    } elseif ($p1_ym == 200905) {
        $p1_all_gyoumu = $p1_all_gyoumu - 1550450;
    } elseif ($p1_ym == 200904) {
        $p1_all_gyoumu = $p1_all_gyoumu - 1550450;
    }
    if ($p1_ym == 200912) {
        $p1_all_gyoumu = $p1_all_gyoumu - 466000;
    }
    if ($p1_ym == 201001) {
        $p1_all_gyoumu = $p1_all_gyoumu + 466000;
    }
    $p1_all_gyoumu = number_format(($p1_all_gyoumu / $tani), $keta);
}
if ($p1_ym >= 201001) {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='¥«¥×¥é¶ÈÌ³°ÑÂ÷¼ýÆþºÆ·×»»'", $p1_ym);
} else {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='¥«¥×¥é¶ÈÌ³°ÑÂ÷¼ýÆþ'", $p1_ym);
}
if (getUniResult($query, $p1_c_gyoumu) < 1) {
    $p1_c_gyoumu = 0;                              // ¸¡º÷¼ºÇÔ
} else {
    if ($p1_ym == 200912) {
        $p1_c_gyoumu = $p1_c_gyoumu - 389809;
    }
    if ($p1_ym == 201001) {
        $p1_c_gyoumu = $p1_c_gyoumu + 315529;
    }
    $p1_c_gyoumu = number_format(($p1_c_gyoumu / $tani), $keta);
}
if ($p1_ym >= 201001) {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='µ¡¹©¶ÈÌ³°ÑÂ÷¼ýÆþºÆ·×»»'", $p1_ym);
} else {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='µ¡¹©¶ÈÌ³°ÑÂ÷¼ýÆþ'", $p1_ym);
}
if (getUniResult($query, $p1_b_gyoumu) < 1) {
    $p1_b_gyoumu = 0;    // ¸¡º÷¼ºÇÔ
    $p1_b_gyoumu_sagaku = 0;
} else {
    if ($p1_ym == 200912) {
        $p1_b_gyoumu = $p1_b_gyoumu - 4931;
    }
    if ($p1_ym == 201001) {
        $p1_b_gyoumu = $p1_b_gyoumu + 4852;
    }
    $p1_b_gyoumu_sagaku = $p1_b_gyoumu;
    $p1_b_gyoumu = number_format(($p1_b_gyoumu / $tani), $keta);
}
if ($p1_ym >= 201001) {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='¥ê¥Ë¥¢¶ÈÌ³°ÑÂ÷¼ýÆþºÆ·×»»'", $p1_ym);
} else {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='¥ê¥Ë¥¢¶ÈÌ³°ÑÂ÷¼ýÆþ'", $p1_ym);
}
if (getUniResult($query, $p1_l_gyoumu) < 1) {
    $p1_l_gyoumu = 0 - $p1_s_gyoumu_sagaku;     // ¸¡º÷¼ºÇÔ
    $p1_lh_gyoumu = 0;     // ¸¡º÷¼ºÇÔ
    $p1_lh_gyoumu_sagaku = 0;     // ¸¡º÷¼ºÇÔ
} else {
    if ($p1_ym == 200906) {
        $p1_l_gyoumu = $p1_l_gyoumu + 3100900;
    } elseif ($p1_ym == 200905) {
        $p1_l_gyoumu = $p1_l_gyoumu - 1550450;
    } elseif ($p1_ym == 200904) {
        $p1_l_gyoumu = $p1_l_gyoumu - 1550450;
    }
    if ($p1_ym == 200912) {
        $p1_l_gyoumu = $p1_l_gyoumu - 76191;
    }
    if ($p1_ym == 201001) {
        $p1_l_gyoumu = $p1_l_gyoumu + 58250;
    }
    if ($p1_ym >= 201001) {
        $p1_l_gyoumu  = $p1_l_gyoumu + $p1_s_gyoumu_sagaku;
    }
    $p1_lh_gyoumu = $p1_l_gyoumu - $p1_s_gyoumu_sagaku - $p1_b_gyoumu_sagaku;
    $p1_lh_gyoumu_sagaku = $p1_lh_gyoumu;
    $p1_l_gyoumu         = $p1_l_gyoumu - $p1_s_gyoumu_sagaku;     // »î¸³½¤Íý¶ÈÌ³°ÑÂ÷¼ýÆþ¤ò¥ê¥Ë¥¢¤Î¶ÈÌ³°ÑÂ÷¼ýÆþ¤è¤ê¥Þ¥¤¥Ê¥¹
    $p1_lh_gyoumu = number_format(($p1_lh_gyoumu / $tani), $keta);
    $p1_l_gyoumu = number_format(($p1_l_gyoumu / $tani), $keta);
}
    ///// Á°Á°·î
if ($p2_ym >= 201001) {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='¾¦´É¶ÈÌ³°ÑÂ÷¼ýÆþºÆ·×»»'", $p2_ym);
    if (getUniResult($query, $p2_n_gyoumu) < 1) {
        $p2_n_gyoumu      = 0;                       // ¸¡º÷¼ºÇÔ
        $p2_n_gyoumu_temp = 0;
    } else {
        if ($p2_ym == 201001) {
            $p2_n_gyoumu = $p2_n_gyoumu + 63096;
        }
        $p2_n_gyoumu_temp = $p2_n_gyoumu;
    }
} else {
    $p2_n_gyoumu     = 0;
}
if ($p2_ym >= 201001) {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='»î½¤¶ÈÌ³°ÑÂ÷¼ýÆþºÆ·×»»'", $p2_ym);
} else {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='»î½¤¶ÈÌ³°ÑÂ÷¼ýÆþ'", $p2_ym);
}
if (getUniResult($query, $p2_s_gyoumu) < 1) {
    $p2_s_gyoumu        = 0;                       // ¸¡º÷¼ºÇÔ
    $p2_s_gyoumu_sagaku = 0;
} else {
    $p2_s_gyoumu_sagaku = $p2_s_gyoumu;
    if ($p2_ym == 200912) {
        $p2_s_gyoumu = $p2_s_gyoumu - 722;
    }
    if ($p2_ym == 201001) {
        $p2_s_gyoumu = $p2_s_gyoumu + 29125;
    }
    $p2_s_gyoumu        = number_format(($p2_s_gyoumu / $tani), $keta);
}
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='Á´ÂÎ¶ÈÌ³°ÑÂ÷¼ýÆþ'", $p2_ym);
if (getUniResult($query, $p2_all_gyoumu) < 1) {
    $p2_all_gyoumu = 0;   // ¸¡º÷¼ºÇÔ
} else {
    if ($p2_ym == 200906) {
        $p2_all_gyoumu = $p2_all_gyoumu + 3100900;
    } elseif ($p2_ym == 200905) {
        $p2_all_gyoumu = $p2_all_gyoumu - 1550450;
    } elseif ($p2_ym == 200904) {
        $p2_all_gyoumu = $p2_all_gyoumu - 1550450;
    }
    if ($p2_ym == 200912) {
        $p2_all_gyoumu = $p2_all_gyoumu - 466000;
    }
    if ($p2_ym == 201001) {
        $p2_all_gyoumu = $p2_all_gyoumu + 466000;
    }
    $p2_all_gyoumu = number_format(($p2_all_gyoumu / $tani), $keta);
}
if ($p2_ym >= 201001) {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='¥«¥×¥é¶ÈÌ³°ÑÂ÷¼ýÆþºÆ·×»»'", $p2_ym);
} else {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='¥«¥×¥é¶ÈÌ³°ÑÂ÷¼ýÆþ'", $p2_ym);
}
if (getUniResult($query, $p2_c_gyoumu) < 1) {
    $p2_c_gyoumu = 0;                              // ¸¡º÷¼ºÇÔ
} else {
    if ($p2_ym == 200912) {
        $p2_c_gyoumu = $p2_c_gyoumu - 389809;
    }
    if ($p2_ym == 201001) {
        $p2_c_gyoumu = $p2_c_gyoumu + 315529;
    }
    $p2_c_gyoumu = number_format(($p2_c_gyoumu / $tani), $keta);
}
if ($p2_ym >= 201001) {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='µ¡¹©¶ÈÌ³°ÑÂ÷¼ýÆþºÆ·×»»'", $p2_ym);
} else {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='µ¡¹©¶ÈÌ³°ÑÂ÷¼ýÆþ'", $p2_ym);
}
if (getUniResult($query, $p2_b_gyoumu) < 1) {
    $p2_b_gyoumu = 0;    // ¸¡º÷¼ºÇÔ
    $p2_b_gyoumu_sagaku = 0;
} else {
    if ($p2_ym == 200912) {
        $p2_b_gyoumu = $p2_b_gyoumu - 4931;
    }
    if ($p2_ym == 201001) {
        $p2_b_gyoumu = $p2_b_gyoumu + 4852;
    }
    $p2_b_gyoumu_sagaku = $p2_b_gyoumu;
    $p2_b_gyoumu = number_format(($p2_b_gyoumu / $tani), $keta);
}
if ($p2_ym >= 201001) {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='¥ê¥Ë¥¢¶ÈÌ³°ÑÂ÷¼ýÆþºÆ·×»»'", $p2_ym);
} else {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='¥ê¥Ë¥¢¶ÈÌ³°ÑÂ÷¼ýÆþ'", $p2_ym);
}
if (getUniResult($query, $p2_l_gyoumu) < 1) {
    $p2_l_gyoumu = 0 - $p2_s_gyoumu_sagaku;     // ¸¡º÷¼ºÇÔ
    $p2_lh_gyoumu = 0;     // ¸¡º÷¼ºÇÔ
    $p2_lh_gyoumu_sagaku = 0;     // ¸¡º÷¼ºÇÔ
} else {
    if ($p2_ym == 200906) {
        $p2_l_gyoumu = $p2_l_gyoumu + 3100900;
    } elseif ($p2_ym == 200905) {
        $p2_l_gyoumu = $p2_l_gyoumu - 1550450;
    } elseif ($p2_ym == 200904) {
        $p2_l_gyoumu = $p2_l_gyoumu - 1550450;
    }
    if ($p2_ym == 200912) {
        $p2_l_gyoumu = $p2_l_gyoumu - 76191;
    }
    if ($p2_ym == 201001) {
        $p2_l_gyoumu = $p2_l_gyoumu + 58250;
    }
    if ($p2_ym >= 201001) {
        $p2_l_gyoumu  = $p2_l_gyoumu + $p2_s_gyoumu_sagaku;
    }
    $p2_lh_gyoumu = $p2_l_gyoumu - $p2_s_gyoumu_sagaku - $p2_b_gyoumu_sagaku;
    $p2_lh_gyoumu_sagaku = $p2_lh_gyoumu;
    $p2_l_gyoumu         = $p2_l_gyoumu - $p2_s_gyoumu_sagaku;     // »î¸³½¤Íý¶ÈÌ³°ÑÂ÷¼ýÆþ¤ò¥ê¥Ë¥¢¤Î¶ÈÌ³°ÑÂ÷¼ýÆþ¤è¤ê¥Þ¥¤¥Ê¥¹
    $p2_lh_gyoumu = number_format(($p2_lh_gyoumu / $tani), $keta);
    $p2_l_gyoumu = number_format(($p2_l_gyoumu / $tani), $keta);
}
    ///// º£´üÎß·×
if ($yyyymm >= 201004) {
    $query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='¾¦´É¶ÈÌ³°ÑÂ÷¼ýÆþºÆ·×»»'", $str_ym, $yyyymm);
    if (getUniResult($query, $rui_n_gyoumu) < 1) {
        $rui_n_gyoumu = 0;                          // ¸¡º÷¼ºÇÔ
    }
} elseif ($yyyymm >= 201001 && $yyyymm <= 201003) {
    $rui_n_gyoumu_a = 0;
    $query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=201001 and pl_bs_ym<=%d and note='¾¦´É¶ÈÌ³°ÑÂ÷¼ýÆþºÆ·×»»'", $yyyymm);
    if (getUniResult($query, $rui_n_gyoumu_b) < 1) {
        $rui_n_gyoumu_b = 0;                          // ¸¡º÷¼ºÇÔ
    }
    $rui_n_gyoumu = $rui_n_gyoumu_a + $rui_n_gyoumu_b;
    $rui_n_gyoumu = $rui_n_gyoumu + 63096;
} else {
    $rui_n_gyoumu = 0;
}
if ($yyyymm >= 201004) {
    $query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='»î½¤¶ÈÌ³°ÑÂ÷¼ýÆþºÆ·×»»'", $str_ym, $yyyymm);
    if (getUniResult($query, $rui_s_gyoumu) < 1) {
        $rui_s_gyoumu = 0;                          // ¸¡º÷¼ºÇÔ
    } else {
        $rui_s_gyoumu_sagaku = $rui_s_gyoumu;
        $rui_s_gyoumu = number_format(($rui_s_gyoumu / $tani), $keta);
    }
} elseif ($yyyymm >= 201001 && $yyyymm <= 201003) {
    $query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=200904 and pl_bs_ym<=200912 and note='»î½¤¶ÈÌ³°ÑÂ÷¼ýÆþ'");
    if (getUniResult($query, $rui_s_gyoumu_a) < 1) {
        $rui_s_gyoumu_a = 0;                          // ¸¡º÷¼ºÇÔ
    }
    $query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=201001 and pl_bs_ym<=%d and note='»î½¤¶ÈÌ³°ÑÂ÷¼ýÆþºÆ·×»»'", $yyyymm);
    if (getUniResult($query, $rui_s_gyoumu_b) < 1) {
        $rui_s_gyoumu_b = 0;                          // ¸¡º÷¼ºÇÔ
    }
    $rui_s_gyoumu = $rui_s_gyoumu_a + $rui_s_gyoumu_b;
    
    if ($yyyymm >= 200912 && $yyyymm <= 201003) {
        $rui_s_gyoumu = $rui_s_gyoumu - 722;
    }
    if ($yyyymm >= 201001 && $yyyymm <= 201003) {
        $rui_s_gyoumu = $rui_s_gyoumu + 29125;
    }
    $rui_s_gyoumu_sagaku = $rui_s_gyoumu;
    $rui_s_gyoumu = number_format(($rui_s_gyoumu / $tani), $keta);
} else {
    $query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='»î½¤¶ÈÌ³°ÑÂ÷¼ýÆþ'", $str_ym, $yyyymm);
    if (getUniResult($query, $rui_s_gyoumu) < 1) {
        $rui_s_gyoumu        = 0;                   // ¸¡º÷¼ºÇÔ
        $rui_s_gyoumu_sagaku = 0;
    } else {
        $rui_s_gyoumu_sagaku = $rui_s_gyoumu;
        if ($yyyymm >= 200912 && $yyyymm <= 201003) {
            $rui_s_gyoumu = $rui_s_gyoumu - 722;
        }
        if ($yyyymm >= 201001 && $yyyymm <= 201003) {
            $rui_s_gyoumu = $rui_s_gyoumu + 29125;
        }
        $rui_s_gyoumu = number_format(($rui_s_gyoumu / $tani), $keta);
    }
}
$query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='Á´ÂÎ¶ÈÌ³°ÑÂ÷¼ýÆþ'", $str_ym, $yyyymm);
if (getUniResult($query, $rui_all_gyoumu) < 1) {
    $rui_all_gyoumu = 0;                        // ¸¡º÷¼ºÇÔ
} else {
    if ($yyyymm >= 200912 && $yyyymm <= 201003) {
        $rui_all_gyoumu = $rui_all_gyoumu - 466000;
    }
    if ($yyyymm >= 201001 && $yyyymm <= 201003) {
        $rui_all_gyoumu = $rui_all_gyoumu + 466000;
    }
    $rui_all_gyoumu = number_format(($rui_all_gyoumu / $tani), $keta);
}
if ($yyyymm >= 201004) {
    $query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='¥«¥×¥é¶ÈÌ³°ÑÂ÷¼ýÆþºÆ·×»»'", $str_ym, $yyyymm);
    if (getUniResult($query, $rui_c_gyoumu) < 1) {
        $rui_c_gyoumu = 0;                          // ¸¡º÷¼ºÇÔ
    } else {
        $rui_c_gyoumu = number_format(($rui_c_gyoumu / $tani), $keta);
    }
} elseif ($yyyymm >= 201001 && $yyyymm <= 201003) {
    $query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=200904 and pl_bs_ym<=200912 and note='¥«¥×¥é¶ÈÌ³°ÑÂ÷¼ýÆþ'");
    if (getUniResult($query, $rui_c_gyoumu_a) < 1) {
        $rui_c_gyoumu_a = 0;                          // ¸¡º÷¼ºÇÔ
    }
    $query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=201001 and pl_bs_ym<=%d and note='¥«¥×¥é¶ÈÌ³°ÑÂ÷¼ýÆþºÆ·×»»'", $yyyymm);
    if (getUniResult($query, $rui_c_gyoumu_b) < 1) {
        $rui_c_gyoumu_b = 0;                          // ¸¡º÷¼ºÇÔ
    }
    $rui_c_gyoumu = $rui_c_gyoumu_a + $rui_c_gyoumu_b;
    
    if ($yyyymm >= 200912 && $yyyymm <= 201003) {
        $rui_c_gyoumu = $rui_c_gyoumu - 389809;
    }
    if ($yyyymm >= 201001 && $yyyymm <= 201003) {
        $rui_c_gyoumu = $rui_c_gyoumu + 315529;
    }
    $rui_c_gyoumu = number_format(($rui_c_gyoumu / $tani), $keta);
} else {
    $query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='¥«¥×¥é¶ÈÌ³°ÑÂ÷¼ýÆþ'", $str_ym, $yyyymm);
    if (getUniResult($query, $rui_c_gyoumu) < 1) {
        $rui_c_gyoumu = 0;                          // ¸¡º÷¼ºÇÔ
    } else {
        if ($yyyymm >= 200912 && $yyyymm <= 201003) {
            $rui_c_gyoumu = $rui_c_gyoumu - 389809;
        }
        if ($yyyymm >= 201001 && $yyyymm <= 201003) {
            $rui_c_gyoumu = $rui_c_gyoumu + 315529;
        }
        $rui_c_gyoumu = number_format(($rui_c_gyoumu / $tani), $keta);
    }
}
if ($yyyymm >= 201004) {
    $query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='µ¡¹©¶ÈÌ³°ÑÂ÷¼ýÆþºÆ·×»»'", $str_ym, $yyyymm);
    if (getUniResult($query, $rui_b_gyoumu) < 1) {
        $rui_b_gyoumu = 0;    // ¸¡º÷¼ºÇÔ
        $rui_b_gyoumu_sagaku = 0;
    } else {
        $rui_b_gyoumu_sagaku = $rui_b_gyoumu;
        $rui_b_gyoumu = number_format(($rui_b_gyoumu / $tani), $keta);
    }
} elseif ($yyyymm >= 201001 && $yyyymm <= 201003) {
    $query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=200904 and pl_bs_ym<=200912 and note='µ¡¹©¶ÈÌ³°ÑÂ÷¼ýÆþ'");
    if (getUniResult($query, $rui_b_gyoumu_a) < 1) {
        $rui_b_gyoumu_a = 0;                          // ¸¡º÷¼ºÇÔ
    }
    $query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=201001 and pl_bs_ym<=%d and note='µ¡¹©¶ÈÌ³°ÑÂ÷¼ýÆþºÆ·×»»'", $yyyymm);
    if (getUniResult($query, $rui_b_gyoumu_b) < 1) {
        $rui_b_gyoumu_b = 0;                          // ¸¡º÷¼ºÇÔ
    }
    $rui_b_gyoumu = $rui_b_gyoumu_a + $rui_b_gyoumu_b;
    if ($yyyymm >= 200912 && $yyyymm <= 201003) {
        $rui_b_gyoumu = $rui_b_gyoumu - 4931;
    }
    if ($yyyymm >= 201001 && $yyyymm <= 201003) {
        $rui_b_gyoumu = $rui_b_gyoumu + 4852;
    }
    $rui_b_gyoumu_sagaku = $rui_b_gyoumu;
    $rui_b_gyoumu = number_format(($rui_b_gyoumu / $tani), $keta);
} else {
    $query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='µ¡¹©¶ÈÌ³°ÑÂ÷¼ýÆþ'", $str_ym, $yyyymm);
    if (getUniResult($query, $rui_b_gyoumu) < 1) {
        $rui_b_gyoumu = 0;    // ¸¡º÷¼ºÇÔ
        $rui_b_gyoumu_sagaku = 0;
    } else {
        if ($yyyymm >= 200912 && $yyyymm <= 201003) {
            $rui_b_gyoumu = $rui_b_gyoumu - 4931;
        }
        if ($yyyymm >= 201001 && $yyyymm <= 201003) {
            $rui_b_gyoumu = $rui_b_gyoumu + 4852;
        }
        $rui_b_gyoumu_sagaku = $rui_b_gyoumu;
        $rui_b_gyoumu = number_format(($rui_b_gyoumu / $tani), $keta);
    }
}
if ($yyyymm >= 201004) {
    $query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='¥ê¥Ë¥¢¶ÈÌ³°ÑÂ÷¼ýÆþºÆ·×»»'", $str_ym, $yyyymm);
    if (getUniResult($query, $rui_l_gyoumu) < 1) {
        $rui_l_gyoumu = 0 - $rui_s_gyoumu_sagaku;   // ¸¡º÷¼ºÇÔ
        $rui_lh_gyoumu = 0;     // ¸¡º÷¼ºÇÔ
        $rui_lh_gyoumu_sagaku = 0;     // ¸¡º÷¼ºÇÔ
    } else {
        $rui_l_gyoumu = $rui_l_gyoumu + $rui_s_gyoumu_sagaku;
        $rui_lh_gyoumu = $rui_l_gyoumu - $rui_s_gyoumu_sagaku - $rui_b_gyoumu_sagaku;
        $rui_lh_gyoumu_sagaku = $rui_lh_gyoumu;
        $rui_l_gyoumu         = $rui_l_gyoumu - $rui_s_gyoumu_sagaku;     // »î¸³½¤Íý¶ÈÌ³°ÑÂ÷¼ýÆþ¤ò¥ê¥Ë¥¢¤Î¶ÈÌ³°ÑÂ÷¼ýÆþ¤è¤ê¥Þ¥¤¥Ê¥¹
        $rui_lh_gyoumu = number_format(($rui_lh_gyoumu / $tani), $keta);
        $rui_l_gyoumu = number_format(($rui_l_gyoumu / $tani), $keta);
    }
} elseif ($yyyymm >= 201001 && $yyyymm <= 201003) {
    $query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=200904 and pl_bs_ym<=200912 and note='¥ê¥Ë¥¢¶ÈÌ³°ÑÂ÷¼ýÆþ'");
    if (getUniResult($query, $rui_l_gyoumu_a) < 1) {
        $rui_l_gyoumu_a = 0;                          // ¸¡º÷¼ºÇÔ
    }
    $query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=201001 and pl_bs_ym<=%d and note='¥ê¥Ë¥¢¶ÈÌ³°ÑÂ÷¼ýÆþºÆ·×»»'", $yyyymm);
    if (getUniResult($query, $rui_l_gyoumu_b) < 1) {
        $rui_l_gyoumu_b = 0;                          // ¸¡º÷¼ºÇÔ
    }
    $rui_l_gyoumu = $rui_l_gyoumu_a + $rui_l_gyoumu_b;
    
    if ($yyyymm >= 200912 && $yyyymm <= 201003) {
        $rui_l_gyoumu = $rui_l_gyoumu - 76191;
    }
    if ($yyyymm >= 201001 && $yyyymm <= 201003) {
        $rui_l_gyoumu = $rui_l_gyoumu + 58250 + 29125;
    }
    $rui_l_gyoumu = $rui_l_gyoumu + $rui_s_gyoumu_b;
    $rui_lh_gyoumu = $rui_l_gyoumu - $rui_s_gyoumu_sagaku - $rui_b_gyoumu_sagaku;
    $rui_lh_gyoumu_sagaku = $rui_lh_gyoumu;
    $rui_l_gyoumu         = $rui_l_gyoumu - $rui_s_gyoumu_sagaku;     // »î¸³½¤Íý¶ÈÌ³°ÑÂ÷¼ýÆþ¤ò¥ê¥Ë¥¢¤Î¶ÈÌ³°ÑÂ÷¼ýÆþ¤è¤ê¥Þ¥¤¥Ê¥¹
    $rui_lh_gyoumu = number_format(($rui_lh_gyoumu / $tani), $keta);
    $rui_l_gyoumu = number_format(($rui_l_gyoumu / $tani), $keta);
} else {
    $query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='¥ê¥Ë¥¢¶ÈÌ³°ÑÂ÷¼ýÆþ'", $str_ym, $yyyymm);
    if (getUniResult($query, $rui_l_gyoumu) < 1) {
        $rui_l_gyoumu = 0 - $rui_s_gyoumu_sagaku;   // ¸¡º÷¼ºÇÔ
        $rui_lh_gyoumu = 0;     // ¸¡º÷¼ºÇÔ
        $rui_lh_gyoumu_sagaku = 0;     // ¸¡º÷¼ºÇÔ
    } else {
        if ($yyyymm >= 200912 && $yyyymm <= 201003) {
            $rui_l_gyoumu = $rui_l_gyoumu - 76191;
        }
        //$rui_l_gyoumu = $rui_l_gyoumu + $rui_s_gyoumu_sagaku;
        $rui_lh_gyoumu = $rui_l_gyoumu - $rui_s_gyoumu_sagaku - $rui_b_gyoumu_sagaku;
        $rui_lh_gyoumu_sagaku = $rui_lh_gyoumu;
        $rui_l_gyoumu         = $rui_l_gyoumu - $rui_s_gyoumu_sagaku;     // »î¸³½¤Íý¶ÈÌ³°ÑÂ÷¼ýÆþ¤ò¥ê¥Ë¥¢¤Î¶ÈÌ³°ÑÂ÷¼ýÆþ¤è¤ê¥Þ¥¤¥Ê¥¹
        $rui_lh_gyoumu = number_format(($rui_lh_gyoumu / $tani), $keta);
        $rui_l_gyoumu = number_format(($rui_l_gyoumu / $tani), $keta);
    }
}
/********** ±Ä¶È³°¼ý±×¤Î»ÅÆþ³ä°ú **********/
    ///// Åö·î
if ($yyyymm >= 201001) {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='¾¦´É»ÅÆþ³ä°úºÆ·×»»'", $yyyymm);
    if (getUniResult($query, $n_swari) < 1) {
        $n_swari        = 0;                        // ¸¡º÷¼ºÇÔ
        $n_swari_temp = 0;
    } else {
        $n_swari_temp = $n_swari;
    }
} else {
    $n_swari     = 0;
}
if ($yyyymm >= 201001) {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='»î½¤»ÅÆþ³ä°úºÆ·×»»'", $yyyymm);
} else {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='»î½¤»ÅÆþ³ä°ú'", $yyyymm);
}
if (getUniResult($query, $s_swari) < 1) {
    $s_swari        = 0;                        // ¸¡º÷¼ºÇÔ
    $s_swari_sagaku = 0;
} else {
    $s_swari_sagaku = $s_swari;
    $s_swari        = number_format(($s_swari / $tani), $keta);
}
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='Á´ÂÎ»ÅÆþ³ä°ú'", $yyyymm);
if (getUniResult($query, $all_swari) < 1) {
    $all_swari = 0;                             // ¸¡º÷¼ºÇÔ
} else {
    $all_swari = number_format(($all_swari / $tani), $keta);
}
if ($yyyymm >= 201001) {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='¥«¥×¥é»ÅÆþ³ä°úºÆ·×»»'", $yyyymm);
} else {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='¥«¥×¥é»ÅÆþ³ä°ú'", $yyyymm);
}
if (getUniResult($query, $c_swari) < 1) {
    $c_swari = 0;                               // ¸¡º÷¼ºÇÔ
} else {
    $c_swari = number_format(($c_swari / $tani), $keta);
}
if ($yyyymm >= 201001) {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='µ¡¹©»ÅÆþ³ä°úºÆ·×»»'", $yyyymm);
} else {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='µ¡¹©»ÅÆþ³ä°ú'", $yyyymm);
}
if (getUniResult($query, $b_swari) < 1) {
    $b_swari        = 0;                        // ¸¡º÷¼ºÇÔ
    $b_swari_sagaku = 0;
} else {
    $b_swari_sagaku = $b_swari;
    $b_swari        = number_format(($b_swari / $tani), $keta);
}
if ($yyyymm >= 201001) {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='¥ê¥Ë¥¢»ÅÆþ³ä°úºÆ·×»»'", $yyyymm);
} else {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='¥ê¥Ë¥¢»ÅÆþ³ä°ú'", $yyyymm);
}
if (getUniResult($query, $l_swari) < 1) {
    $l_swari = 0 - $s_swari_sagaku;     // ¸¡º÷¼ºÇÔ
    $lh_swari = 0;     // ¸¡º÷¼ºÇÔ
    $lh_swari_sagaku = 0;     // ¸¡º÷¼ºÇÔ
} else {
    if ($yyyymm >= 201001) {
        $l_swari = $l_swari + $s_swari_sagaku;
    }
    $lh_swari = $l_swari - $s_swari_sagaku - $b_swari_sagaku;
    $lh_swari_sagaku = $lh_swari;
    $l_swari         = $l_swari - $s_swari_sagaku;     // »î¸³½¤Íý»ÅÆþ³ä°ú¤ò¥ê¥Ë¥¢¤Î»ÅÆþ³ä°ú¤è¤ê¥Þ¥¤¥Ê¥¹
    $lh_swari = number_format(($lh_swari / $tani), $keta);
    $l_swari = number_format(($l_swari / $tani), $keta);
}
    ///// Á°·î
if ($p1_ym >= 201001) {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='¾¦´É»ÅÆþ³ä°úºÆ·×»»'", $p1_ym);
    if (getUniResult($query, $p1_n_swari) < 1) {
        $p1_n_swari        = 0;                        // ¸¡º÷¼ºÇÔ
        $p1_n_swari_temp = 0;
    } else {
        $p1_n_swari_temp = $p1_n_swari;
    }
} else {
    $p1_n_swari     = 0;
}
if ($p1_ym >= 201001) {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='»î½¤»ÅÆþ³ä°úºÆ·×»»'", $p1_ym);
} else {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='»î½¤»ÅÆþ³ä°ú'", $p1_ym);
}
if (getUniResult($query, $p1_s_swari) < 1) {
    $p1_s_swari        = 0;                        // ¸¡º÷¼ºÇÔ
    $p1_s_swari_sagaku = 0;
} else {
    $p1_s_swari_sagaku = $p1_s_swari;
    $p1_s_swari        = number_format(($p1_s_swari / $tani), $keta);
}
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='Á´ÂÎ»ÅÆþ³ä°ú'", $p1_ym);
if (getUniResult($query, $p1_all_swari) < 1) {
    $p1_all_swari = 0;                          // ¸¡º÷¼ºÇÔ
} else {
    $p1_all_swari = number_format(($p1_all_swari / $tani), $keta);
}
if ($p1_ym >= 201001) {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='¥«¥×¥é»ÅÆþ³ä°úºÆ·×»»'", $p1_ym);
} else {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='¥«¥×¥é»ÅÆþ³ä°ú'", $p1_ym);
}
if (getUniResult($query, $p1_c_swari) < 1) {
    $p1_c_swari = 0;                               // ¸¡º÷¼ºÇÔ
} else {
    $p1_c_swari = number_format(($p1_c_swari / $tani), $keta);
}
if ($p1_ym >= 201001) {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='µ¡¹©»ÅÆþ³ä°úºÆ·×»»'", $p1_ym);
} else {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='µ¡¹©»ÅÆþ³ä°ú'", $p1_ym);
}
if (getUniResult($query, $p1_b_swari) < 1) {
    $p1_b_swari        = 0;                        // ¸¡º÷¼ºÇÔ
    $p1_b_swari_sagaku = 0;
} else {
    $p1_b_swari_sagaku = $p1_b_swari;
    $p1_b_swari        = number_format(($p1_b_swari / $tani), $keta);
}
if ($p1_ym >= 201001) {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='¥ê¥Ë¥¢»ÅÆþ³ä°úºÆ·×»»'", $p1_ym);
} else {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='¥ê¥Ë¥¢»ÅÆþ³ä°ú'", $p1_ym);
}
if (getUniResult($query, $p1_l_swari) < 1) {
    $p1_l_swari = 0 - $p1_s_swari_sagaku;     // ¸¡º÷¼ºÇÔ
    $p1_lh_swari = 0;     // ¸¡º÷¼ºÇÔ
    $p1_lh_swari_sagaku = 0;     // ¸¡º÷¼ºÇÔ
} else {
    if ($p1_ym >= 201001) {
        $p1_l_swari = $p1_l_swari + $p1_s_swari_sagaku;
    }
    $p1_lh_swari = $p1_l_swari - $p1_s_swari_sagaku - $p1_b_swari_sagaku;
    $p1_lh_swari_sagaku = $p1_lh_swari;
    $p1_l_swari         = $p1_l_swari - $p1_s_swari_sagaku;     // »î¸³½¤Íý»ÅÆþ³ä°ú¤ò¥ê¥Ë¥¢¤Î»ÅÆþ³ä°ú¤è¤ê¥Þ¥¤¥Ê¥¹
    $p1_lh_swari = number_format(($p1_lh_swari / $tani), $keta);
    $p1_l_swari = number_format(($p1_l_swari / $tani), $keta);
}
    ///// Á°Á°·î
if ($p2_ym >= 201001) {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='¾¦´É»ÅÆþ³ä°úºÆ·×»»'", $p2_ym);
    if (getUniResult($query, $p2_n_swari) < 1) {
        $p2_n_swari        = 0;                        // ¸¡º÷¼ºÇÔ
        $p2_n_swari_temp = 0;
    } else {
        $p2_n_swari_temp = $p2_n_swari;
    }
} else {
    $p2_n_swari     = 0;
}
if ($p2_ym >= 201001) {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='»î½¤»ÅÆþ³ä°úºÆ·×»»'", $p2_ym);
} else {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='»î½¤»ÅÆþ³ä°ú'", $p2_ym);
}
if (getUniResult($query, $p2_s_swari) < 1) {
    $p2_s_swari        = 0;                        // ¸¡º÷¼ºÇÔ
    $p2_s_swari_sagaku = 0;
} else {
    $p2_s_swari_sagaku = $p2_s_swari;
    $p2_s_swari        = number_format(($p2_s_swari / $tani), $keta);
}
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='Á´ÂÎ»ÅÆþ³ä°ú'", $p2_ym);
if (getUniResult($query, $p2_all_swari) < 1) {
    $p2_all_swari = 0;                          // ¸¡º÷¼ºÇÔ
} else {
    $p2_all_swari = number_format(($p2_all_swari / $tani), $keta);
}
if ($p2_ym >= 201001) {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='¥«¥×¥é»ÅÆþ³ä°úºÆ·×»»'", $p2_ym);
} else {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='¥«¥×¥é»ÅÆþ³ä°ú'", $p2_ym);
}
if (getUniResult($query, $p2_c_swari) < 1) {
    $p2_c_swari = 0;                               // ¸¡º÷¼ºÇÔ
} else {
    $p2_c_swari = number_format(($p2_c_swari / $tani), $keta);
}
if ($p2_ym >= 201001) {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='µ¡¹©»ÅÆþ³ä°úºÆ·×»»'", $p2_ym);
} else {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='µ¡¹©»ÅÆþ³ä°ú'", $p2_ym);
}
if (getUniResult($query, $p2_b_swari) < 1) {
    $p2_b_swari        = 0;                        // ¸¡º÷¼ºÇÔ
    $p2_b_swari_sagaku = 0;
} else {
    $p2_b_swari_sagaku = $p2_b_swari;
    $p2_b_swari        = number_format(($p2_b_swari / $tani), $keta);
}
if ($p2_ym >= 201001) {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='¥ê¥Ë¥¢»ÅÆþ³ä°úºÆ·×»»'", $p2_ym);
} else {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='¥ê¥Ë¥¢»ÅÆþ³ä°ú'", $p2_ym);
}
if (getUniResult($query, $p2_l_swari) < 1) {
    $p2_l_swari = 0 - $p2_s_swari_sagaku;     // ¸¡º÷¼ºÇÔ
    $p2_lh_swari = 0;     // ¸¡º÷¼ºÇÔ
    $p2_lh_swari_sagaku = 0;     // ¸¡º÷¼ºÇÔ
} else {
    if ($p2_ym >= 201001) {
        $p2_l_swari = $p2_l_swari + $p2_s_swari_sagaku;
    }
    $p2_lh_swari = $p2_l_swari - $p2_s_swari_sagaku - $p2_b_swari_sagaku;
    $p2_lh_swari_sagaku = $p2_lh_swari;
    $p2_l_swari         = $p2_l_swari - $p2_s_swari_sagaku;     // »î¸³½¤Íý»ÅÆþ³ä°ú¤ò¥ê¥Ë¥¢¤Î»ÅÆþ³ä°ú¤è¤ê¥Þ¥¤¥Ê¥¹
    $p2_lh_swari = number_format(($p2_lh_swari / $tani), $keta);
    $p2_l_swari = number_format(($p2_l_swari / $tani), $keta);
}
    ///// º£´üÎß·×
if ($yyyymm >= 201004) {
    $query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='¾¦´É»ÅÆþ³ä°úºÆ·×»»'", $str_ym, $yyyymm);
    if (getUniResult($query, $rui_n_swari) < 1) {
        $rui_n_swari = 0;                           // ¸¡º÷¼ºÇÔ
    }
} elseif ($yyyymm >= 201001 && $yyyymm <= 201003) {
    $rui_n_swari_a = 0;
    $query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=201001 and pl_bs_ym<=%d and note='¾¦´É»ÅÆþ³ä°úºÆ·×»»'", $yyyymm);
    if (getUniResult($query, $rui_n_swari_b) < 1) {
        $rui_n_swari_b = 0;                          // ¸¡º÷¼ºÇÔ
    }
    $rui_n_swari = $rui_n_swari_a + $rui_n_swari_b;
} else {
    $rui_n_swari = 0;
}
if ($yyyymm >= 201004) {
    $query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='»î½¤»ÅÆþ³ä°úºÆ·×»»'", $str_ym, $yyyymm);
    if (getUniResult($query, $rui_s_swari) < 1) {
        $rui_s_swari = 0;                           // ¸¡º÷¼ºÇÔ
    } else {
        $rui_s_swari_sagaku = $rui_s_swari;
        $rui_s_swari = number_format(($rui_s_swari / $tani), $keta);
    }
} elseif ($yyyymm >= 201001 && $yyyymm <= 201003) {
    $query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=200904 and pl_bs_ym<=200912 and note='»î½¤»ÅÆþ³ä°ú'");
    if (getUniResult($query, $rui_s_swari_a) < 1) {
        $rui_s_swari_a = 0;                          // ¸¡º÷¼ºÇÔ
    }
    $query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=201001 and pl_bs_ym<=%d and note='»î½¤»ÅÆþ³ä°úºÆ·×»»'", $yyyymm);
    if (getUniResult($query, $rui_s_swari_b) < 1) {
        $rui_s_swari_b = 0;                          // ¸¡º÷¼ºÇÔ
    }
    $rui_s_swari = $rui_s_swari_a + $rui_s_swari_b;
    $rui_s_swari_sagaku = $rui_s_swari;
    $rui_s_swari = number_format(($rui_s_swari / $tani), $keta);
} else {
    $query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='»î½¤»ÅÆþ³ä°ú'", $str_ym, $yyyymm);
    if (getUniResult($query, $rui_s_swari) < 1) {
        $rui_s_swari        = 0;                    // ¸¡º÷¼ºÇÔ
        $rui_s_swari_sagaku = 0;
    } else {
        $rui_s_swari_sagaku = $rui_s_swari;
        $rui_s_swari = number_format(($rui_s_swari / $tani), $keta);
    }
}
$query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='Á´ÂÎ»ÅÆþ³ä°ú'", $str_ym, $yyyymm);
if (getUniResult($query, $rui_all_swari) < 1) {
    $rui_all_swari = 0;                         // ¸¡º÷¼ºÇÔ
} else {
    $rui_all_swari = number_format(($rui_all_swari / $tani), $keta);
}
if ($yyyymm >= 201004) {
    $query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='¥«¥×¥é»ÅÆþ³ä°úºÆ·×»»'", $str_ym, $yyyymm);
    if (getUniResult($query, $rui_c_swari) < 1) {
        $rui_c_swari = 0;                           // ¸¡º÷¼ºÇÔ
    } else {
        $rui_c_swari = number_format(($rui_c_swari / $tani), $keta);
    }
} elseif ($yyyymm >= 201001 && $yyyymm <= 201003) {
    $query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=200904 and pl_bs_ym<=200912 and note='¥«¥×¥é»ÅÆþ³ä°ú'");
    if (getUniResult($query, $rui_c_swari_a) < 1) {
        $rui_c_swari_a = 0;                          // ¸¡º÷¼ºÇÔ
    }
    $query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=201001 and pl_bs_ym<=%d and note='¥«¥×¥é»ÅÆþ³ä°úºÆ·×»»'", $yyyymm);
    if (getUniResult($query, $rui_c_swari_b) < 1) {
        $rui_c_swari_b = 0;                          // ¸¡º÷¼ºÇÔ
    }
    $rui_c_swari = $rui_c_swari_a + $rui_c_swari_b;
    $rui_c_swari = number_format(($rui_c_swari / $tani), $keta);
} else {
    $query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='¥«¥×¥é»ÅÆþ³ä°ú'", $str_ym, $yyyymm);
    if (getUniResult($query, $rui_c_swari) < 1) {
        $rui_c_swari = 0;                           // ¸¡º÷¼ºÇÔ
    } else {
        $rui_c_swari = number_format(($rui_c_swari / $tani), $keta);
    }
}
if ($yyyymm >= 201004) {
    $query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='µ¡¹©»ÅÆþ³ä°úºÆ·×»»'", $str_ym, $yyyymm);
    if (getUniResult($query, $rui_b_swari) < 1) {
        $rui_b_swari = 0;    // ¸¡º÷¼ºÇÔ
        $rui_b_swari_sagaku = 0;
    } else {
        $rui_b_swari_sagaku = $rui_b_swari;
        $rui_b_swari = number_format(($rui_b_swari / $tani), $keta);
    }
} elseif ($yyyymm >= 201001 && $yyyymm <= 201003) {
    $query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=200904 and pl_bs_ym<=200912 and note='µ¡¹©»ÅÆþ³ä°ú'");
    if (getUniResult($query, $rui_b_swari_a) < 1) {
        $rui_b_swari_a = 0;                          // ¸¡º÷¼ºÇÔ
    }
    $query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=201001 and pl_bs_ym<=%d and note='µ¡¹©»ÅÆþ³ä°úºÆ·×»»'", $yyyymm);
    if (getUniResult($query, $rui_b_swari_b) < 1) {
        $rui_b_swari_b = 0;                          // ¸¡º÷¼ºÇÔ
    }
    $rui_b_swari = $rui_b_swari_a + $rui_b_swari_b;
    $rui_b_swari_sagaku = $rui_b_swari;
    $rui_b_swari = number_format(($rui_b_swari / $tani), $keta);
} else {
    $query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='µ¡¹©»ÅÆþ³ä°ú'", $str_ym, $yyyymm);
    if (getUniResult($query, $rui_b_swari) < 1) {
        $rui_b_swari = 0;    // ¸¡º÷¼ºÇÔ
        $rui_b_swari_sagaku = 0;
    } else {
        $rui_b_swari_sagaku = $rui_b_swari;
        $rui_b_swari = number_format(($rui_b_swari / $tani), $keta);
    }
}
if ($yyyymm >= 201004) {
    $query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='¥ê¥Ë¥¢»ÅÆþ³ä°úºÆ·×»»'", $str_ym, $yyyymm);
    if (getUniResult($query, $rui_l_swari) < 1) {
        $rui_l_swari = 0 - $rui_s_swari_sagaku;   // ¸¡º÷¼ºÇÔ
        $rui_lh_swari = 0;     // ¸¡º÷¼ºÇÔ
        $rui_lh_swari_sagaku = 0;     // ¸¡º÷¼ºÇÔ
    } else {
        $rui_l_swari = $rui_l_swari + $rui_s_swari_sagaku;
        $rui_lh_swari = $rui_l_swari - $rui_s_swari_sagaku - $rui_b_swari_sagaku;
        $rui_lh_swari_sagaku = $rui_lh_swari;
        $rui_l_swari         = $rui_l_swari - $rui_s_swari_sagaku;     // »î¸³½¤Íý»ÅÆþ³ä°ú¤ò¥ê¥Ë¥¢¤Î»ÅÆþ³ä°ú¤è¤ê¥Þ¥¤¥Ê¥¹
        $rui_lh_swari = number_format(($rui_lh_swari / $tani), $keta);
        $rui_l_swari = number_format(($rui_l_swari / $tani), $keta);
    }
} elseif ($yyyymm >= 201001 && $yyyymm <= 201003) {
    $query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=200904 and pl_bs_ym<=200912 and note='¥ê¥Ë¥¢»ÅÆþ³ä°ú'");
    if (getUniResult($query, $rui_l_swari_a) < 1) {
        $rui_l_swari_a = 0;                          // ¸¡º÷¼ºÇÔ
    }
    $query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=201001 and pl_bs_ym<=%d and note='¥ê¥Ë¥¢»ÅÆþ³ä°úºÆ·×»»'", $yyyymm);
    if (getUniResult($query, $rui_l_swari_b) < 1) {
        $rui_l_swari_b = 0;                          // ¸¡º÷¼ºÇÔ
    }
    $rui_l_swari = $rui_l_swari_a + $rui_l_swari_b;
    $rui_l_swari = $rui_l_swari - $rui_s_swari_a;
    $rui_l_swari = $rui_l_swari + $rui_s_swari_sagaku;
    $rui_lh_swari = $rui_l_swari - $rui_s_swari_sagaku - $rui_b_swari_sagaku;
    $rui_lh_swari_sagaku = $rui_lh_swari;
    $rui_l_swari         = $rui_l_swari - $rui_s_swari_sagaku;     // »î¸³½¤Íý»ÅÆþ³ä°ú¤ò¥ê¥Ë¥¢¤Î»ÅÆþ³ä°ú¤è¤ê¥Þ¥¤¥Ê¥¹
    $rui_lh_swari = number_format(($rui_lh_swari / $tani), $keta);
    $rui_l_swari = number_format(($rui_l_swari / $tani), $keta);
} else {
    $query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='¥ê¥Ë¥¢»ÅÆþ³ä°ú'", $str_ym, $yyyymm);
    if (getUniResult($query, $rui_l_swari) < 1) {
        $rui_l_swari = 0 - $rui_s_swari_sagaku;   // ¸¡º÷¼ºÇÔ
        $rui_lh_swari = 0;     // ¸¡º÷¼ºÇÔ
        $rui_lh_swari_sagaku = 0;     // ¸¡º÷¼ºÇÔ
    } else {
        //$rui_l_swari = $rui_l_swari + $rui_s_swari_sagaku;
        $rui_lh_swari = $rui_l_swari - $rui_s_swari_sagaku - $rui_b_swari_sagaku;
        $rui_lh_swari_sagaku = $rui_lh_swari;
        $rui_l_swari         = $rui_l_swari - $rui_s_swari_sagaku;     // »î¸³½¤Íý»ÅÆþ³ä°ú¤ò¥ê¥Ë¥¢¤Î»ÅÆþ³ä°ú¤è¤ê¥Þ¥¤¥Ê¥¹
        $rui_lh_swari = number_format(($rui_lh_swari / $tani), $keta);
        $rui_l_swari = number_format(($rui_l_swari / $tani), $keta);
    }
}
/********** ±Ä¶È³°¼ý±×¤Î¤½¤ÎÂ¾ **********/
    ///// Åö·î
if ($yyyymm >= 201001) {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='»î½¤±Ä¶È³°¼ý±×¤½¤ÎÂ¾ºÆ·×»»'", $yyyymm);
} else {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='»î½¤±Ä¶È³°¼ý±×¤½¤ÎÂ¾'", $yyyymm);
}
if (getUniResult($query, $s_pother) < 1) {
    $s_pother        = 0;                       // ¸¡º÷¼ºÇÔ
    $s_pother_sagaku = 0;
} else {
    $s_pother_sagaku = $s_pother;
    if ($yyyymm == 200912) {
        $s_pother = $s_pother + 722;
    }
    if ($yyyymm == 201001) {
        $s_pother = $s_pother - 29125;
    }
    $s_pother        = number_format(($s_pother / $tani), $keta);
}
if ($yyyymm >= 201001) {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='¾¦´É±Ä¶È³°¼ý±×¤½¤ÎÂ¾ºÆ·×»»'", $yyyymm);
} else {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='¾¦´É±Ä¶È³°¼ý±×¤½¤ÎÂ¾'", $yyyymm);
}
if (getUniResult($query, $n_pother) < 1) {
    $n_pother = 0;                              // ¸¡º÷¼ºÇÔ
    $n_sagaku = $n_sagaku - $n_pother;          // ¥«¥×¥éº¹³Û·×»»ÍÑ
} else {
    if ($yyyymm == 201001) {
        $n_pother = $n_pother - 63096;
    }
    $n_sagaku = $n_sagaku - $n_pother;          // ¥«¥×¥éº¹³Û·×»»ÍÑ
}
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='Á´ÂÎ±Ä¶È³°¼ý±×¤½¤ÎÂ¾'", $yyyymm);
if (getUniResult($query, $all_pother) < 1) {
    $all_pother = 0;                            // ¸¡º÷¼ºÇÔ
} else {
    if ($yyyymm == 200912) {
        $all_pother = $all_pother + 466000;
    }
    if ($yyyymm == 201001) {
        $all_pother = $all_pother - 466000;
    }
    if ($yyyymm == 201002) {
        $all_pother = $all_pother + 600000;
    }
    if ($yyyymm == 201003) {
        $all_pother = $all_pother - 600000;
    }
    $all_pother = number_format(($all_pother / $tani), $keta);
}
if ($yyyymm >= 201001) {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='¥«¥×¥é±Ä¶È³°¼ý±×¤½¤ÎÂ¾ºÆ·×»»'", $yyyymm);
} else {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='¥«¥×¥é±Ä¶È³°¼ý±×¤½¤ÎÂ¾'", $yyyymm);
}
if (getUniResult($query, $c_pother) < 1) {
    $c_pother = 0;                              // ¸¡º÷¼ºÇÔ
} else {
    if ($yyyymm < 201001) {
        $c_pother = $c_pother - $n_pother;
    }
    if ($yyyymm == 200912) {
        $c_pother = $c_pother + 389809;
    }
    if ($yyyymm == 201001) {
        $c_pother = $c_pother - 315529;
    }
    $c_pother = number_format(($c_pother / $tani), $keta);
}
if ($yyyymm >= 201001) {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='µ¡¹©±Ä¶È³°¼ý±×¤½¤ÎÂ¾ºÆ·×»»'", $yyyymm);
} else {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='µ¡¹©±Ä¶È³°¼ý±×¤½¤ÎÂ¾'", $yyyymm);
}
if (getUniResult($query, $b_pother) < 1) {
    $b_pother = 0;    // ¸¡º÷¼ºÇÔ
    $b_pother_sagaku = 0;
} else {
    if ($yyyymm == 200912) {
        $b_pother = $b_pother + 4931;
    }
    if ($yyyymm == 201001) {
        $b_pother = $b_pother - 4852;
    }
    $b_pother_sagaku = $b_pother;
    $b_pother = number_format(($b_pother / $tani), $keta);
}
if ($yyyymm >= 201001) {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='¥ê¥Ë¥¢±Ä¶È³°¼ý±×¤½¤ÎÂ¾ºÆ·×»»'", $yyyymm);
} else {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='¥ê¥Ë¥¢±Ä¶È³°¼ý±×¤½¤ÎÂ¾'", $yyyymm);
}
if (getUniResult($query, $l_pother) < 1) {
    $l_pother = 0 - $s_pother_sagaku;     // ¸¡º÷¼ºÇÔ
    $lh_pother = 0;     // ¸¡º÷¼ºÇÔ
    $lh_pother_sagaku = 0;     // ¸¡º÷¼ºÇÔ
} else {
    if ($yyyymm == 200912) {
        $l_pother = $l_pother + 76191;
    }
    if ($yyyymm == 201001) {
        $l_pother = $l_pother - 58250;
    }
    if ($yyyymm >= 201001) {
        $l_pother = $l_pother + $s_pother_sagaku;
    }
    $lh_pother = $l_pother - $s_pother_sagaku - $b_pother_sagaku;
    $lh_pother_sagaku = $lh_pother;
    $l_pother         = $l_pother - $s_pother_sagaku;     // »î¸³½¤Íý±Ä¶È³°¼ý±×¤½¤ÎÂ¾¤ò¥ê¥Ë¥¢¤Î±Ä¶È³°¼ý±×¤½¤ÎÂ¾¤è¤ê¥Þ¥¤¥Ê¥¹
    $lh_pother = number_format(($lh_pother / $tani), $keta);
    $l_pother = number_format(($l_pother / $tani), $keta);
}
    ///// Á°·î
if ($p1_ym >= 201001) {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='»î½¤±Ä¶È³°¼ý±×¤½¤ÎÂ¾ºÆ·×»»'", $p1_ym);
} else {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='»î½¤±Ä¶È³°¼ý±×¤½¤ÎÂ¾'", $p1_ym);
}
if (getUniResult($query, $p1_s_pother) < 1) {
    $p1_s_pother        = 0;                       // ¸¡º÷¼ºÇÔ
    $p1_s_pother_sagaku = 0;
} else {
    $p1_s_pother_sagaku = $p1_s_pother;
    if ($p1_ym == 200912) {
        $p1_s_pother = $p1_s_pother + 722;
    }
    if ($p1_ym == 201001) {
        $p1_s_pother = $p1_s_pother - 29125;
    }
    $p1_s_pother        = number_format(($p1_s_pother / $tani), $keta);
}
if ($p1_ym >= 201001) {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='¾¦´É±Ä¶È³°¼ý±×¤½¤ÎÂ¾ºÆ·×»»'", $p1_ym);
} else {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='¾¦´É±Ä¶È³°¼ý±×¤½¤ÎÂ¾'", $p1_ym);
}
if (getUniResult($query, $p1_n_pother) < 1) {
    $p1_n_pother = 0;                              // ¸¡º÷¼ºÇÔ
    $p1_n_sagaku = $p1_n_sagaku - $p1_n_pother;          // ¥«¥×¥éº¹³Û·×»»ÍÑ
} else {
    if ($p1_ym == 201001) {
        $p1_n_pother = $p1_n_pother - 63096;
    }
    $p1_n_sagaku = $p1_n_sagaku - $p1_n_pother;          // ¥«¥×¥éº¹³Û·×»»ÍÑ
}
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='Á´ÂÎ±Ä¶È³°¼ý±×¤½¤ÎÂ¾'", $p1_ym);
if (getUniResult($query, $p1_all_pother) < 1) {
    $p1_all_pother = 0;                         // ¸¡º÷¼ºÇÔ
} else {
    if ($p1_ym == 200912) {
        $p1_all_pother = $p1_all_pother + 466000;
    }
    if ($p1_ym == 201001) {
        $p1_all_pother = $p1_all_pother - 466000;
    }
    if ($p1_ym == 201002) {
        $p1_all_pother = $p1_all_pother + 600000;
    }
    if ($p1_ym == 201003) {
        $p1_all_pother = $p1_all_pother - 600000;
    }
    $p1_all_pother = number_format(($p1_all_pother / $tani), $keta);
}
if ($p1_ym >= 201001) {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='¥«¥×¥é±Ä¶È³°¼ý±×¤½¤ÎÂ¾ºÆ·×»»'", $p1_ym);
} else {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='¥«¥×¥é±Ä¶È³°¼ý±×¤½¤ÎÂ¾'", $p1_ym);
}
if (getUniResult($query, $p1_c_pother) < 1) {
    $p1_c_pother = 0;                              // ¸¡º÷¼ºÇÔ
} else {
    if ($p1_ym < 201001) {
        $p1_c_pother = $p1_c_pother - $p1_n_pother;
    }
    if ($p1_ym == 200912) {
        $p1_c_pother = $p1_c_pother + 389809;
    }
    if ($p1_ym == 201001) {
        $p1_c_pother = $p1_c_pother - 315529;
    }
    $p1_c_pother = number_format(($p1_c_pother / $tani), $keta);
}
if ($p1_ym >= 201001) {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='µ¡¹©±Ä¶È³°¼ý±×¤½¤ÎÂ¾ºÆ·×»»'", $p1_ym);
} else {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='µ¡¹©±Ä¶È³°¼ý±×¤½¤ÎÂ¾'", $p1_ym);
}
if (getUniResult($query, $p1_b_pother) < 1) {
    $p1_b_pother = 0;    // ¸¡º÷¼ºÇÔ
    $p1_b_pother_sagaku = 0;
} else {
    if ($p1_ym == 200912) {
        $p1_b_pother = $p1_b_pother + 4931;
    }
    if ($p1_ym == 201001) {
        $p1_b_pother = $p1_b_pother - 4852;
    }
    $p1_b_pother_sagaku = $p1_b_pother;
    $p1_b_pother = number_format(($p1_b_pother / $tani), $keta);
}
if ($p1_ym >= 201001) {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='¥ê¥Ë¥¢±Ä¶È³°¼ý±×¤½¤ÎÂ¾ºÆ·×»»'", $p1_ym);
} else {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='¥ê¥Ë¥¢±Ä¶È³°¼ý±×¤½¤ÎÂ¾'", $p1_ym);
}
if (getUniResult($query, $p1_l_pother) < 1) {
    $p1_l_pother = 0 - $p1_s_pother_sagaku;     // ¸¡º÷¼ºÇÔ
    $p1_lh_pother = 0;     // ¸¡º÷¼ºÇÔ
    $p1_lh_pother_sagaku = 0;     // ¸¡º÷¼ºÇÔ
} else {
    if ($p1_ym == 200912) {
        $p1_l_pother = $p1_l_pother + 76191;
    }
    if ($p1_ym == 201001) {
        $p1_l_pother = $p1_l_pother - 58250;
    }
    if ($p1_ym >= 201001) {
        $p1_l_pother = $p1_l_pother + $p1_s_pother_sagaku;
    }
    $p1_lh_pother = $p1_l_pother - $p1_s_pother_sagaku - $p1_b_pother_sagaku;
    $p1_lh_pother_sagaku = $p1_lh_pother;
    $p1_l_pother         = $p1_l_pother - $p1_s_pother_sagaku;     // »î¸³½¤Íý±Ä¶È³°¼ý±×¤½¤ÎÂ¾¤ò¥ê¥Ë¥¢¤Î±Ä¶È³°¼ý±×¤½¤ÎÂ¾¤è¤ê¥Þ¥¤¥Ê¥¹
    $p1_lh_pother = number_format(($p1_lh_pother / $tani), $keta);
    $p1_l_pother = number_format(($p1_l_pother / $tani), $keta);
}
    ///// Á°Á°·î
if ($p2_ym >= 201001) {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='»î½¤±Ä¶È³°¼ý±×¤½¤ÎÂ¾ºÆ·×»»'", $p2_ym);
} else {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='»î½¤±Ä¶È³°¼ý±×¤½¤ÎÂ¾'", $p2_ym);
}
if (getUniResult($query, $p2_s_pother) < 1) {
    $p2_s_pother        = 0;                       // ¸¡º÷¼ºÇÔ
    $p2_s_pother_sagaku = 0;
} else {
    $p2_s_pother_sagaku = $p2_s_pother;
    if ($p2_ym == 200912) {
        $p2_s_pother = $p2_s_pother + 722;
    }
    if ($p2_ym == 201001) {
        $p2_s_pother = $p2_s_pother - 29125;
    }
    $p2_s_pother        = number_format(($p2_s_pother / $tani), $keta);
}
if ($p2_ym >= 201001) {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='¾¦´É±Ä¶È³°¼ý±×¤½¤ÎÂ¾ºÆ·×»»'", $p2_ym);
} else {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='¾¦´É±Ä¶È³°¼ý±×¤½¤ÎÂ¾'", $p2_ym);
}
if (getUniResult($query, $p2_n_pother) < 1) {
    $p2_n_pother = 0;                              // ¸¡º÷¼ºÇÔ
    $p2_n_sagaku = $p2_n_sagaku - $p2_n_pother;          // ¥«¥×¥éº¹³Û·×»»ÍÑ
} else {
    if ($p2_ym == 201001) {
        $p2_n_pother = $p2_n_pother - 63096;
    }
    $p2_n_sagaku = $p2_n_sagaku - $p2_n_pother;          // ¥«¥×¥éº¹³Û·×»»ÍÑ
}
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='Á´ÂÎ±Ä¶È³°¼ý±×¤½¤ÎÂ¾'", $p2_ym);
if (getUniResult($query, $p2_all_pother) < 1) {
    $p2_all_pother = 0;                         // ¸¡º÷¼ºÇÔ
} else {
    if ($p2_ym == 200912) {
        $p2_all_pother = $p2_all_pother + 466000;
    }
    if ($p2_ym == 201001) {
        $p2_all_pother = $p2_all_pother - 466000;
    }
    if ($p2_ym == 201002) {
        $p2_all_pother = $p2_all_pother + 600000;
    }
    if ($p2_ym == 201003) {
        $p2_all_pother = $p2_all_pother - 600000;
    }
    $p2_all_pother = number_format(($p2_all_pother / $tani), $keta);
}
if ($p2_ym >= 201001) {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='¥«¥×¥é±Ä¶È³°¼ý±×¤½¤ÎÂ¾ºÆ·×»»'", $p2_ym);
} else {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='¥«¥×¥é±Ä¶È³°¼ý±×¤½¤ÎÂ¾'", $p2_ym);
}
if (getUniResult($query, $p2_c_pother) < 1) {
    $p2_c_pother = 0;                              // ¸¡º÷¼ºÇÔ
} else {
    if ($p2_ym < 201001) {
        $p2_c_pother = $p2_c_pother - $p2_n_pother;
    }
    if ($p2_ym == 200912) {
        $p2_c_pother = $p2_c_pother + 389809;
    }
    if ($p2_ym == 201001) {
        $p2_c_pother = $p2_c_pother - 315529;
    }
    $p2_c_pother = number_format(($p2_c_pother / $tani), $keta);
}
if ($p2_ym >= 201001) {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='µ¡¹©±Ä¶È³°¼ý±×¤½¤ÎÂ¾ºÆ·×»»'", $p2_ym);
} else {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='µ¡¹©±Ä¶È³°¼ý±×¤½¤ÎÂ¾'", $p2_ym);
}
if (getUniResult($query, $p2_b_pother) < 1) {
    $p2_b_pother = 0;    // ¸¡º÷¼ºÇÔ
    $p2_b_pother_sagaku = 0;
} else {
    if ($p2_ym == 200912) {
        $p2_b_pother = $p2_b_pother + 4931;
    }
    if ($p2_ym == 201001) {
        $p2_b_pother = $p2_b_pother - 4852;
    }
    $p2_b_pother_sagaku = $p2_b_pother;
    $p2_b_pother = number_format(($p2_b_pother / $tani), $keta);
}
if ($p2_ym >= 201001) {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='¥ê¥Ë¥¢±Ä¶È³°¼ý±×¤½¤ÎÂ¾ºÆ·×»»'", $p2_ym);
} else {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='¥ê¥Ë¥¢±Ä¶È³°¼ý±×¤½¤ÎÂ¾'", $p2_ym);
}
if (getUniResult($query, $p2_l_pother) < 1) {
    $p2_l_pother = 0 - $p2_s_pother_sagaku;     // ¸¡º÷¼ºÇÔ
    $p2_lh_pother = 0;     // ¸¡º÷¼ºÇÔ
    $p2_lh_pother_sagaku = 0;     // ¸¡º÷¼ºÇÔ
} else {
    if ($p2_ym == 200912) {
        $p2_l_pother = $p2_l_pother + 76191;
    }
    if ($p2_ym == 201001) {
        $p2_l_pother = $p2_l_pother - 58250;
    }
    if ($p2_ym >= 201001) {
        $p2_l_pother = $p2_l_pother + $p2_s_pother_sagaku;
    }
    $p2_lh_pother = $p2_l_pother - $p2_s_pother_sagaku - $p2_b_pother_sagaku;
    $p2_lh_pother_sagaku = $p2_lh_pother;
    $p2_l_pother         = $p2_l_pother - $p2_s_pother_sagaku;     // »î¸³½¤Íý±Ä¶È³°¼ý±×¤½¤ÎÂ¾¤ò¥ê¥Ë¥¢¤Î±Ä¶È³°¼ý±×¤½¤ÎÂ¾¤è¤ê¥Þ¥¤¥Ê¥¹
    $p2_lh_pother = number_format(($p2_lh_pother / $tani), $keta);
    $p2_l_pother = number_format(($p2_l_pother / $tani), $keta);
}
    ///// º£´üÎß·×
if ($yyyymm >= 201004) {
    $query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='¾¦´É±Ä¶È³°¼ý±×¤½¤ÎÂ¾ºÆ·×»»'", $str_ym, $yyyymm);
    if (getUniResult($query, $rui_n_pother) < 1) {
        $rui_n_pother = 0;                          // ¸¡º÷¼ºÇÔ
    } else {
        //$rui_n_pother_sagaku = $rui_n_pother;
    }
} elseif ($yyyymm >= 201001 && $yyyymm <= 201003) {
    $query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=200904 and pl_bs_ym<=200912 and note='¾¦´É±Ä¶È³°¼ý±×¤½¤ÎÂ¾'");
    if (getUniResult($query, $rui_n_pother_a) < 1) {
        $rui_n_pother_a = 0;                          // ¸¡º÷¼ºÇÔ
    }
    $query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=201001 and pl_bs_ym<=%d and note='¾¦´É±Ä¶È³°¼ý±×¤½¤ÎÂ¾ºÆ·×»»'", $yyyymm);
    if (getUniResult($query, $rui_n_pother_b) < 1) {
        $rui_n_pother_b = 0;                          // ¸¡º÷¼ºÇÔ
    }
    $rui_n_pother = $rui_n_pother_a + $rui_n_pother_b;
    $rui_n_pother = $rui_n_pother - 63096;
} else {
    $query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='¾¦´É±Ä¶È³°¼ý±×¤½¤ÎÂ¾'", $str_ym, $yyyymm);
    if (getUniResult($query, $rui_n_pother) < 1) {
        $rui_n_pother = 0;                          // ¸¡º÷¼ºÇÔ
        $rui_n_sagaku = $rui_n_sagaku - $rui_n_pother;      // ¥«¥×¥éº¹³Û·×»»ÍÑ
    } else {
        $rui_n_sagaku = $rui_n_sagaku - $rui_n_pother;      // ¥«¥×¥éº¹³Û·×»»ÍÑ
    }
}
if ($yyyymm >= 201004) {
    $query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='»î½¤±Ä¶È³°¼ý±×¤½¤ÎÂ¾ºÆ·×»»'", $str_ym, $yyyymm);
    if (getUniResult($query, $rui_s_pother) < 1) {
        $rui_s_pother = 0;                          // ¸¡º÷¼ºÇÔ
    } else {
        $rui_s_pother_sagaku = $rui_s_pother;
        $rui_s_pother = number_format(($rui_s_pother / $tani), $keta);
    }
} elseif ($yyyymm >= 201001 && $yyyymm <= 201003) {
    $query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=200904 and pl_bs_ym<=200912 and note='»î½¤±Ä¶È³°¼ý±×¤½¤ÎÂ¾'");
    if (getUniResult($query, $rui_s_pother_a) < 1) {
        $rui_s_pother_a = 0;                          // ¸¡º÷¼ºÇÔ
    }
    $query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=201001 and pl_bs_ym<=%d and note='»î½¤±Ä¶È³°¼ý±×¤½¤ÎÂ¾ºÆ·×»»'", $yyyymm);
    if (getUniResult($query, $rui_s_pother_b) < 1) {
        $rui_s_pother_b = 0;                          // ¸¡º÷¼ºÇÔ
    }
    $rui_s_pother = $rui_s_pother_a + $rui_s_pother_b;
    
    if ($yyyymm >= 200912 && $yyyymm <= 201003) {
        $rui_s_pother = $rui_s_pother + 722;
    }
    if ($yyyymm >= 201001 && $yyyymm <= 201003) {
        $rui_s_pother = $rui_s_pother - 29125;
    }
    $rui_s_pother_sagaku = $rui_s_pother;
    $rui_s_pother = number_format(($rui_s_pother / $tani), $keta);
} else {
    $query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='»î½¤±Ä¶È³°¼ý±×¤½¤ÎÂ¾'", $str_ym, $yyyymm);
    if (getUniResult($query, $rui_s_pother) < 1) {
        $rui_s_pother        = 0;                   // ¸¡º÷¼ºÇÔ
        $rui_s_pother_sagaku = 0;
    } else {
        $rui_s_pother_sagaku = $rui_s_pother;
        if ($yyyymm >= 200912 && $yyyymm <= 201003) {
            $rui_s_pother = $rui_s_pother + 722;
        }
        if ($yyyymm >= 201001 && $yyyymm <= 201003) {
            $rui_s_pother = $rui_s_pother - 29125;
        }
        $rui_s_pother = number_format(($rui_s_pother / $tani), $keta);
    }
}

$query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='Á´ÂÎ±Ä¶È³°¼ý±×¤½¤ÎÂ¾'", $str_ym, $yyyymm);
if (getUniResult($query, $rui_all_pother) < 1) {
    $rui_all_pother = 0;                        // ¸¡º÷¼ºÇÔ
} else {
    if ($yyyymm >= 200912 && $yyyymm <= 201003) {
        $rui_all_pother = $rui_all_pother + 466000;
    }
    if ($yyyymm >= 201001 && $yyyymm <= 201003) {
        $rui_all_pother = $rui_all_pother - 466000;
    }
    if ($yyyymm >= 201002 && $yyyymm <= 201003) {
        $rui_all_pother = $rui_all_pother + 600000;
    }
    if ($yyyymm == 201003) {
        $rui_all_pother = $rui_all_pother - 600000;
    }
    $rui_all_pother = number_format(($rui_all_pother / $tani), $keta);
}
if ($yyyymm >= 201004) {
    $query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='¥«¥×¥é±Ä¶È³°¼ý±×¤½¤ÎÂ¾ºÆ·×»»'", $str_ym, $yyyymm);
    if (getUniResult($query, $rui_c_pother) < 1) {
        $rui_c_pother = 0;                          // ¸¡º÷¼ºÇÔ
    } else {
        $rui_c_pother = number_format(($rui_c_pother / $tani), $keta);
    }
} elseif ($yyyymm >= 201001 && $yyyymm <= 201003) {
    $query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=200904 and pl_bs_ym<=200912 and note='¥«¥×¥é±Ä¶È³°¼ý±×¤½¤ÎÂ¾'");
    if (getUniResult($query, $rui_c_pother_a) < 1) {
        $rui_c_pother_a = 0;                          // ¸¡º÷¼ºÇÔ
    }
    $query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=201001 and pl_bs_ym<=%d and note='¥«¥×¥é±Ä¶È³°¼ý±×¤½¤ÎÂ¾ºÆ·×»»'", $yyyymm);
    if (getUniResult($query, $rui_c_pother_b) < 1) {
        $rui_c_pother_b = 0;                          // ¸¡º÷¼ºÇÔ
    }
    $rui_c_pother = $rui_c_pother_a + $rui_c_pother_b;
    
    if ($yyyymm >= 200912 && $yyyymm <= 201003) {
        $rui_c_pother = $rui_c_pother + 389809;
    }
    if ($yyyymm >= 201001 && $yyyymm <= 201003) {
        $rui_c_pother = $rui_c_pother - 315529;
    }
    $rui_c_pother = $rui_c_pother - $rui_n_pother_a;
    $rui_c_pother = number_format(($rui_c_pother / $tani), $keta);
} else {
    $query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='¥«¥×¥é±Ä¶È³°¼ý±×¤½¤ÎÂ¾'", $str_ym, $yyyymm);
    if (getUniResult($query, $rui_c_pother) < 1) {
        $rui_c_pother = 0;                          // ¸¡º÷¼ºÇÔ
    } else {
        $rui_c_pother = $rui_c_pother - $rui_n_pother;
        if ($yyyymm >= 200912 && $yyyymm <= 201003) {
            $rui_c_pother = $rui_c_pother + 389809;
        }
        if ($yyyymm >= 201001 && $yyyymm <= 201003) {
            $rui_c_pother = $rui_c_pother - 315529;
        }
        $rui_c_pother = number_format(($rui_c_pother / $tani), $keta);
    }
}
if ($yyyymm >= 201004) {
    $query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='µ¡¹©±Ä¶È³°¼ý±×¤½¤ÎÂ¾ºÆ·×»»'", $str_ym, $yyyymm);
    if (getUniResult($query, $rui_b_pother) < 1) {
        $rui_b_pother = 0;    // ¸¡º÷¼ºÇÔ
        $rui_b_pother_sagaku = 0;
    } else {
        $rui_b_pother_sagaku = $rui_b_pother;
        $rui_b_pother = number_format(($rui_b_pother / $tani), $keta);
    }
} elseif ($yyyymm >= 201001 && $yyyymm <= 201003) {
    $query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=200904 and pl_bs_ym<=200912 and note='µ¡¹©±Ä¶È³°¼ý±×¤½¤ÎÂ¾'");
    if (getUniResult($query, $rui_b_pother_a) < 1) {
        $rui_b_pother_a = 0;                          // ¸¡º÷¼ºÇÔ
    }
    $query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=201001 and pl_bs_ym<=%d and note='µ¡¹©±Ä¶È³°¼ý±×¤½¤ÎÂ¾ºÆ·×»»'", $yyyymm);
    if (getUniResult($query, $rui_b_pother_b) < 1) {
        $rui_b_pother_b = 0;                          // ¸¡º÷¼ºÇÔ
    }
    $rui_b_pother = $rui_b_pother_a + $rui_b_pother_b;
    if ($yyyymm >= 200912 && $yyyymm <= 201003) {
        $rui_b_pother = $rui_b_pother + 4931;
    }
    if ($yyyymm >= 201001 && $yyyymm <= 201003) {
        $rui_b_pother = $rui_b_pother - 4852;
    }
    $rui_b_pother_sagaku = $rui_b_pother;
    $rui_b_pother = number_format(($rui_b_pother / $tani), $keta);
} else {
    $query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='µ¡¹©±Ä¶È³°¼ý±×¤½¤ÎÂ¾'", $str_ym, $yyyymm);
    if (getUniResult($query, $rui_b_pother) < 1) {
        $rui_b_pother = 0;    // ¸¡º÷¼ºÇÔ
        $rui_b_pother_sagaku = 0;
    } else {
        if ($yyyymm >= 200912 && $yyyymm <= 201003) {
            $rui_b_pother = $rui_b_pother + 4931;
        }
        if ($yyyymm >= 201001 && $yyyymm <= 201003) {
            $rui_b_pother = $rui_b_pother - 4852;
        }
        $rui_b_pother_sagaku = $rui_b_pother;
        $rui_b_pother = number_format(($rui_b_pother / $tani), $keta);
    }
}
if ($yyyymm >= 201004) {
    $query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='¥ê¥Ë¥¢±Ä¶È³°¼ý±×¤½¤ÎÂ¾ºÆ·×»»'", $str_ym, $yyyymm);
    if (getUniResult($query, $rui_l_pother) < 1) {
        $rui_l_pother = 0 - $rui_s_pother_sagaku;   // ¸¡º÷¼ºÇÔ
        $rui_lh_pother = 0;     // ¸¡º÷¼ºÇÔ
        $rui_lh_pother_sagaku = 0;     // ¸¡º÷¼ºÇÔ
    } else {
        $rui_l_pother = $rui_l_pother + $rui_s_pother_sagaku;
        $rui_lh_pother = $rui_l_pother - $rui_s_pother_sagaku - $rui_b_pother_sagaku;
        $rui_lh_pother_sagaku = $rui_lh_pother;
        $rui_l_pother         = $rui_l_pother - $rui_s_pother_sagaku;     // »î¸³½¤Íý±Ä¶È³°¼ý±×¤½¤ÎÂ¾¤ò¥ê¥Ë¥¢¤Î±Ä¶È³°¼ý±×¤½¤ÎÂ¾¤è¤ê¥Þ¥¤¥Ê¥¹
        $rui_lh_pother = number_format(($rui_lh_pother / $tani), $keta);
        $rui_l_pother = number_format(($rui_l_pother / $tani), $keta);
    }
} elseif ($yyyymm >= 201001 && $yyyymm <= 201003) {
    $query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=200904 and pl_bs_ym<=200912 and note='¥ê¥Ë¥¢±Ä¶È³°¼ý±×¤½¤ÎÂ¾'");
    if (getUniResult($query, $rui_l_pother_a) < 1) {
        $rui_l_pother_a = 0;                          // ¸¡º÷¼ºÇÔ
    }
    $query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=201001 and pl_bs_ym<=%d and note='¥ê¥Ë¥¢±Ä¶È³°¼ý±×¤½¤ÎÂ¾ºÆ·×»»'", $yyyymm);
    if (getUniResult($query, $rui_l_pother_b) < 1) {
        $rui_l_pother_b = 0;                          // ¸¡º÷¼ºÇÔ
    }
    $rui_l_pother = $rui_l_pother_a + $rui_l_pother_b;
    
    if ($yyyymm >= 200912 && $yyyymm <= 201003) {
        $rui_l_pother = $rui_l_pother + 76191;
    }
    if ($yyyymm >= 201001 && $yyyymm <= 201003) {
        $rui_l_pother = $rui_l_pother - 58250 - 722;
    }
    $rui_l_pother = $rui_l_pother - $rui_s_pother_a;
    $rui_l_pother = $rui_l_pother + $rui_s_pother_sagaku;
    $rui_lh_pother = $rui_l_pother - $rui_s_pother_sagaku - $rui_b_pother_sagaku;
    $rui_lh_pother_sagaku = $rui_lh_pother;
    $rui_l_pother         = $rui_l_pother - $rui_s_pother_sagaku;     // »î¸³½¤Íý±Ä¶È³°¼ý±×¤½¤ÎÂ¾¤ò¥ê¥Ë¥¢¤Î±Ä¶È³°¼ý±×¤½¤ÎÂ¾¤è¤ê¥Þ¥¤¥Ê¥¹
    $rui_lh_pother = number_format(($rui_lh_pother / $tani), $keta);
    $rui_l_pother = number_format(($rui_l_pother / $tani), $keta);
} else {
    $query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='¥ê¥Ë¥¢±Ä¶È³°¼ý±×¤½¤ÎÂ¾'", $str_ym, $yyyymm);
    if (getUniResult($query, $rui_l_pother) < 1) {
        $rui_l_pother = 0 - $rui_s_pother_sagaku;   // ¸¡º÷¼ºÇÔ
        $rui_lh_pother = 0;     // ¸¡º÷¼ºÇÔ
        $rui_lh_pother_sagaku = 0;     // ¸¡º÷¼ºÇÔ
    } else {
        if ($yyyymm >= 200912 && $yyyymm <= 201003) {
            $rui_l_pother = $rui_l_pother + 76191;
        }
        //$rui_l_pother = $rui_l_pother + $rui_s_pother_sagaku;
        $rui_lh_pother = $rui_l_pother - $rui_s_pother_sagaku - $rui_b_pother_sagaku;
        $rui_lh_pother_sagaku = $rui_lh_pother;
        $rui_l_pother         = $rui_l_pother - $rui_s_pother_sagaku;     // »î¸³½¤Íý±Ä¶È³°¼ý±×¤½¤ÎÂ¾¤ò¥ê¥Ë¥¢¤Î±Ä¶È³°¼ý±×¤½¤ÎÂ¾¤è¤ê¥Þ¥¤¥Ê¥¹
        $rui_lh_pother = number_format(($rui_lh_pother / $tani), $keta);
        $rui_l_pother = number_format(($rui_l_pother / $tani), $keta);
    }
}
/********** ±Ä¶È³°¼ý±×¤Î¹ç·× **********/
    ///// ¾¦´É
$p2_n_nonope_profit_sum  = $p2_n_gyoumu + $p2_n_swari + $p2_n_pother;
$p2_n_gyoumu             = number_format(($p2_n_gyoumu / $tani), $keta);
$p2_n_swari              = number_format(($p2_n_swari / $tani), $keta);
$p2_n_pother             = number_format(($p2_n_pother / $tani), $keta);

$p1_n_nonope_profit_sum  = $p1_n_gyoumu + $p1_n_swari + $p1_n_pother;
$p1_n_gyoumu             = number_format(($p1_n_gyoumu / $tani), $keta);
$p1_n_swari              = number_format(($p1_n_swari / $tani), $keta);
$p1_n_pother             = number_format(($p1_n_pother / $tani), $keta);

$n_nonope_profit_sum     = $n_gyoumu + $n_swari + $n_pother;
$n_gyoumu                = number_format(($n_gyoumu / $tani), $keta);
$n_swari                 = number_format(($n_swari / $tani), $keta);
$n_pother                = number_format(($n_pother / $tani), $keta);

$rui_n_nonope_profit_sum = $rui_n_gyoumu + $rui_n_swari + $rui_n_pother;
$rui_n_gyoumu            = number_format(($rui_n_gyoumu / $tani), $keta);
$rui_n_swari             = number_format(($rui_n_swari / $tani), $keta);
$rui_n_pother            = number_format(($rui_n_pother / $tani), $keta);
    ///// »î¸³¡¦½¤Íý
$p2_s_nonope_profit_sum         = $p2_s_gyoumu_sagaku + $p2_s_swari_sagaku + $p2_s_pother_sagaku;
$p2_s_nonope_profit_sum_sagaku  = $p2_s_nonope_profit_sum;
$p2_s_nonope_profit_sum         = number_format(($p2_s_nonope_profit_sum / $tani), $keta);

$p1_s_nonope_profit_sum         = $p1_s_gyoumu_sagaku + $p1_s_swari_sagaku + $p1_s_pother_sagaku;
$p1_s_nonope_profit_sum_sagaku  = $p1_s_nonope_profit_sum;
$p1_s_nonope_profit_sum         = number_format(($p1_s_nonope_profit_sum / $tani), $keta);

$s_nonope_profit_sum            = $s_gyoumu_sagaku + $s_swari_sagaku + $s_pother_sagaku;
$s_nonope_profit_sum_sagaku     = $s_nonope_profit_sum;
$s_nonope_profit_sum            = number_format(($s_nonope_profit_sum / $tani), $keta);

$rui_s_nonope_profit_sum        = $rui_s_gyoumu_sagaku + $rui_s_swari_sagaku + $rui_s_pother_sagaku;
$rui_s_nonope_profit_sum_sagaku = $rui_s_nonope_profit_sum;
$rui_s_nonope_profit_sum        = number_format(($rui_s_nonope_profit_sum / $tani), $keta);
    ///// µ¡¹©
$p2_b_nonope_profit_sum         = $p2_b_gyoumu_sagaku + $p2_b_swari_sagaku + $p2_b_pother_sagaku;
$p2_b_nonope_profit_sum_sagaku  = $p2_b_nonope_profit_sum;
$p2_b_nonope_profit_sum         = number_format(($p2_b_nonope_profit_sum / $tani), $keta);

$p1_b_nonope_profit_sum         = $p1_b_gyoumu_sagaku + $p1_b_swari_sagaku + $p1_b_pother_sagaku;
$p1_b_nonope_profit_sum_sagaku  = $p1_b_nonope_profit_sum;
$p1_b_nonope_profit_sum         = number_format(($p1_b_nonope_profit_sum / $tani), $keta);

$b_nonope_profit_sum            = $b_gyoumu_sagaku + $b_swari_sagaku + $b_pother_sagaku;
$b_nonope_profit_sum_sagaku     = $b_nonope_profit_sum;
$b_nonope_profit_sum            = number_format(($b_nonope_profit_sum / $tani), $keta);

$rui_b_nonope_profit_sum        = $rui_b_gyoumu_sagaku + $rui_b_swari_sagaku + $rui_b_pother_sagaku;
$rui_b_nonope_profit_sum_sagaku = $rui_b_nonope_profit_sum;
$rui_b_nonope_profit_sum        = number_format(($rui_b_nonope_profit_sum / $tani), $keta);

    ///// Åö·î
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='Á´ÂÎ±Ä¶È³°¼ý±×·×'", $yyyymm);
if (getUniResult($query, $all_nonope_profit_sum) < 1) {
    $all_nonope_profit_sum = 0;                 // ¸¡º÷¼ºÇÔ
} else {
    if ($yyyymm == 200906) {
        $all_nonope_profit_sum = $all_nonope_profit_sum + 3100900;
    } elseif ($yyyymm == 200905) {
        $all_nonope_profit_sum = $all_nonope_profit_sum - 1550450;
    } elseif ($yyyymm == 200904) {
        $all_nonope_profit_sum = $all_nonope_profit_sum - 1550450;
    }
    if ($yyyymm == 201002) {
        $all_nonope_profit_sum = $all_nonope_profit_sum + 600000;
    }
    if ($yyyymm == 201003) {
        $all_nonope_profit_sum = $all_nonope_profit_sum - 600000;
    }
    $all_nonope_profit_sum = number_format(($all_nonope_profit_sum / $tani), $keta);
}
if ($yyyymm >= 201001) {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='¥«¥×¥é±Ä¶È³°¼ý±×·×ºÆ·×»»'", $yyyymm);
} else {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='¥«¥×¥é±Ä¶È³°¼ý±×·×'", $yyyymm);
}
if (getUniResult($query, $c_nonope_profit_sum) < 1) {
    $c_nonope_profit_sum = 0;                   // ¸¡º÷¼ºÇÔ
    $c_nonope_profit_sum_temp = 0;
} else {
    if ($yyyymm < 201001) {
        $c_nonope_profit_sum = $c_nonope_profit_sum - $n_nonope_profit_sum;
    }
    $c_nonope_profit_sum_temp = $c_nonope_profit_sum;
    $c_nonope_profit_sum      = number_format(($c_nonope_profit_sum / $tani), $keta);
}
if ($yyyymm >= 201001) {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='¥ê¥Ë¥¢±Ä¶È³°¼ý±×·×ºÆ·×»»'", $yyyymm);
} else {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='¥ê¥Ë¥¢±Ä¶È³°¼ý±×·×'", $yyyymm);
}
if (getUniResult($query, $l_nonope_profit_sum) < 1) {
    $l_nonope_profit_sum = 0 - $s_nonope_profit_sum_sagaku;     // ¸¡º÷¼ºÇÔ
    $lh_nonope_profit_sum = 0;     // ¸¡º÷¼ºÇÔ
    $lh_nonope_profit_sum_sagaku = 0;     // ¸¡º÷¼ºÇÔ
} else {
    if ($yyyymm == 200906) {
        $l_nonope_profit_sum = $l_nonope_profit_sum + 3100900;
    } elseif ($yyyymm == 200905) {
        $l_nonope_profit_sum = $l_nonope_profit_sum - 1550450;
    } elseif ($yyyymm == 200904) {
        $l_nonope_profit_sum = $l_nonope_profit_sum - 1550450;
    }
    if ($yyyymm >= 201001) {
        $l_nonope_profit_sum = $l_nonope_profit_sum + $s_nonope_profit_sum_sagaku;
    }
    $lh_nonope_profit_sum = $l_nonope_profit_sum - $s_nonope_profit_sum_sagaku - $b_nonope_profit_sum_sagaku;
    $lh_nonope_profit_sum_sagaku = $lh_nonope_profit_sum;
    $l_nonope_profit_sum         = $l_nonope_profit_sum - $s_nonope_profit_sum_sagaku;     // »î¸³½¤Íý±Ä¶È³°¼ý±×·×¤ò¥ê¥Ë¥¢¤Î±Ä¶È³°¼ý±×·×¤è¤ê¥Þ¥¤¥Ê¥¹
    $lh_nonope_profit_sum = number_format(($lh_nonope_profit_sum / $tani), $keta);
    $l_nonope_profit_sum = number_format(($l_nonope_profit_sum / $tani), $keta);
}
    ///// Á°·î
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='Á´ÂÎ±Ä¶È³°¼ý±×·×'", $p1_ym);
if (getUniResult($query, $p1_all_nonope_profit_sum) < 1) {
    $p1_all_nonope_profit_sum = 0;              // ¸¡º÷¼ºÇÔ
} else {
    if ($p1_ym == 200906) {
        $p1_all_nonope_profit_sum = $p1_all_nonope_profit_sum + 3100900;
    } elseif ($p1_ym == 200905) {
        $p1_all_nonope_profit_sum = $p1_all_nonope_profit_sum - 1550450;
    } elseif ($p1_ym == 200904) {
        $p1_all_nonope_profit_sum = $p1_all_nonope_profit_sum - 1550450;
    }
    if ($p1_ym == 201002) {
        $p1_all_nonope_profit_sum = $p1_all_nonope_profit_sum + 600000;
    }
    if ($p1_ym == 201003) {
        $p1_all_nonope_profit_sum = $p1_all_nonope_profit_sum - 600000;
    }
    $p1_all_nonope_profit_sum = number_format(($p1_all_nonope_profit_sum / $tani), $keta);
}
if ($p1_ym >= 201001) {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='¥«¥×¥é±Ä¶È³°¼ý±×·×ºÆ·×»»'", $p1_ym);
} else {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='¥«¥×¥é±Ä¶È³°¼ý±×·×'", $p1_ym);
}
if (getUniResult($query, $p1_c_nonope_profit_sum) < 1) {
    $p1_c_nonope_profit_sum = 0;                   // ¸¡º÷¼ºÇÔ
    $p1_c_nonope_profit_sum_temp = 0;
} else {
    if ($p1_ym < 201001) {
        $p1_c_nonope_profit_sum = $p1_c_nonope_profit_sum - $p1_n_nonope_profit_sum;
    }
    $p1_c_nonope_profit_sum_temp = $p1_c_nonope_profit_sum;
    $p1_c_nonope_profit_sum      = number_format(($p1_c_nonope_profit_sum / $tani), $keta);
}
if ($p1_ym >= 201001) {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='¥ê¥Ë¥¢±Ä¶È³°¼ý±×·×ºÆ·×»»'", $p1_ym);
} else {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='¥ê¥Ë¥¢±Ä¶È³°¼ý±×·×'", $p1_ym);
}
if (getUniResult($query, $p1_l_nonope_profit_sum) < 1) {
    $p1_l_nonope_profit_sum = 0 - $p1_s_nonope_profit_sum_sagaku;     // ¸¡º÷¼ºÇÔ
    $p1_lh_nonope_profit_sum = 0;     // ¸¡º÷¼ºÇÔ
    $p1_lh_nonope_profit_sum_sagaku = 0;     // ¸¡º÷¼ºÇÔ
} else {    
    if ($p1_ym == 200906) {
        $p1_l_nonope_profit_sum = $p1_l_nonope_profit_sum + 3100900;
    } elseif ($p1_ym == 200905) {
        $p1_l_nonope_profit_sum = $p1_l_nonope_profit_sum - 1550450;
    } elseif ($p1_ym == 200904) {
        $p1_l_nonope_profit_sum = $p1_l_nonope_profit_sum - 1550450;
    }
    if ($p1_ym >= 201001) {
        $p1_l_nonope_profit_sum = $p1_l_nonope_profit_sum + $p1_s_nonope_profit_sum_sagaku;
    }
    $p1_lh_nonope_profit_sum = $p1_l_nonope_profit_sum - $p1_s_nonope_profit_sum_sagaku - $p1_b_nonope_profit_sum_sagaku;
    $p1_lh_nonope_profit_sum_sagaku = $p1_lh_nonope_profit_sum;
    $p1_l_nonope_profit_sum         = $p1_l_nonope_profit_sum - $p1_s_nonope_profit_sum_sagaku;     // »î¸³½¤Íý±Ä¶È³°¼ý±×·×¤ò¥ê¥Ë¥¢¤Î±Ä¶È³°¼ý±×·×¤è¤ê¥Þ¥¤¥Ê¥¹
    $p1_lh_nonope_profit_sum = number_format(($p1_lh_nonope_profit_sum / $tani), $keta);
    $p1_l_nonope_profit_sum = number_format(($p1_l_nonope_profit_sum / $tani), $keta);
}
    ///// Á°Á°·î
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='Á´ÂÎ±Ä¶È³°¼ý±×·×'", $p2_ym);
if (getUniResult($query, $p2_all_nonope_profit_sum) < 1) {
    $p2_all_nonope_profit_sum = 0;              // ¸¡º÷¼ºÇÔ
} else {
    if ($p2_ym == 200906) {
        $p2_all_nonope_profit_sum = $p2_all_nonope_profit_sum + 3100900;
    } elseif ($p2_ym == 200905) {
        $p2_all_nonope_profit_sum = $p2_all_nonope_profit_sum - 1550450;
    } elseif ($p2_ym == 200904) {
        $p2_all_nonope_profit_sum = $p2_all_nonope_profit_sum - 1550450;
    }
    if ($p2_ym == 201002) {
        $p2_all_nonope_profit_sum = $p2_all_nonope_profit_sum + 600000;
    }
    if ($p2_ym == 201003) {
        $p2_all_nonope_profit_sum = $p2_all_nonope_profit_sum - 600000;
    }
    $p2_all_nonope_profit_sum = number_format(($p2_all_nonope_profit_sum / $tani), $keta);
}
if ($p2_ym >= 201001) {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='¥«¥×¥é±Ä¶È³°¼ý±×·×ºÆ·×»»'", $p2_ym);
} else {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='¥«¥×¥é±Ä¶È³°¼ý±×·×'", $p2_ym);
}
if (getUniResult($query, $p2_c_nonope_profit_sum) < 1) {
    $p2_c_nonope_profit_sum = 0;                   // ¸¡º÷¼ºÇÔ
    $p2_c_nonope_profit_sum_temp = 0;
} else {
    if ($p2_ym < 201001) {
        $p2_c_nonope_profit_sum = $p2_c_nonope_profit_sum - $p2_n_nonope_profit_sum;
    }
    $p2_c_nonope_profit_sum_temp = $p2_c_nonope_profit_sum;
    $p2_c_nonope_profit_sum      = number_format(($p2_c_nonope_profit_sum / $tani), $keta);
}
if ($p2_ym >= 201001) {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='¥ê¥Ë¥¢±Ä¶È³°¼ý±×·×ºÆ·×»»'", $p2_ym);
} else {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='¥ê¥Ë¥¢±Ä¶È³°¼ý±×·×'", $p2_ym);
}
if (getUniResult($query, $p2_l_nonope_profit_sum) < 1) {
    $p2_l_nonope_profit_sum = 0 - $p2_s_nonope_profit_sum_sagaku;     // ¸¡º÷¼ºÇÔ
    $p2_lh_nonope_profit_sum = 0;     // ¸¡º÷¼ºÇÔ
    $p2_lh_nonope_profit_sum_sagaku = 0;     // ¸¡º÷¼ºÇÔ
} else {
    if ($p2_ym == 200906) {
        $p2_l_nonope_profit_sum = $p2_l_nonope_profit_sum + 3100900;
    } elseif ($p2_ym == 200905) {
        $p2_l_nonope_profit_sum = $p2_l_nonope_profit_sum - 1550450;
    } elseif ($p2_ym == 200904) {
        $p2_l_nonope_profit_sum = $p2_l_nonope_profit_sum - 1550450;
    }
    if ($p2_ym >= 201001) {
        $p2_l_nonope_profit_sum = $p2_l_nonope_profit_sum + $p2_s_nonope_profit_sum_sagaku;
    }
    $p2_lh_nonope_profit_sum = $p2_l_nonope_profit_sum - $p2_s_nonope_profit_sum_sagaku - $p2_b_nonope_profit_sum_sagaku;
    $p2_lh_nonope_profit_sum_sagaku = $p2_lh_nonope_profit_sum;
    $p2_l_nonope_profit_sum         = $p2_l_nonope_profit_sum - $p2_s_nonope_profit_sum_sagaku;     // »î¸³½¤Íý±Ä¶È³°¼ý±×·×¤ò¥ê¥Ë¥¢¤Î±Ä¶È³°¼ý±×·×¤è¤ê¥Þ¥¤¥Ê¥¹
    $p2_lh_nonope_profit_sum = number_format(($p2_lh_nonope_profit_sum / $tani), $keta);
    $p2_l_nonope_profit_sum = number_format(($p2_l_nonope_profit_sum / $tani), $keta);
}
    ///// º£´üÎß·×
$query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='Á´ÂÎ±Ä¶È³°¼ý±×·×'", $str_ym, $yyyymm);
if (getUniResult($query, $rui_all_nonope_profit_sum) < 1) {
    $rui_all_nonope_profit_sum = 0;             // ¸¡º÷¼ºÇÔ
} else {
    if ($yyyymm >= 201002 && $yyyymm <= 201003) {
        $rui_all_nonope_profit_sum = $rui_all_nonope_profit_sum + 600000;
    }
    if ($yyyymm == 201003) {
        $rui_all_nonope_profit_sum = $rui_all_nonope_profit_sum - 600000;
    }
    $rui_all_nonope_profit_sum = number_format(($rui_all_nonope_profit_sum / $tani), $keta);
}
if ($yyyymm >= 201004) {
    $query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='¥«¥×¥é±Ä¶È³°¼ý±×·×ºÆ·×»»'", $str_ym, $yyyymm);
    if (getUniResult($query, $rui_c_nonope_profit_sum) < 1) {
        $rui_c_nonope_profit_sum = 0;                           // ¸¡º÷¼ºÇÔ
    } else {
        //$rui_c_nonope_profit_sum = $rui_c_nonope_profit_sum - $rui_n_nonope_profit_sum;
        $rui_c_nonope_profit_sum = number_format(($rui_c_nonope_profit_sum / $tani), $keta);
    }
} elseif ($yyyymm >= 201001 && $yyyymm <= 201003) {
    $query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=200904 and pl_bs_ym<=200912 and note='¥«¥×¥é±Ä¶È³°¼ý±×·×'");
    if (getUniResult($query, $rui_c_nonope_profit_sum_a) < 1) {
        $rui_c_nonope_profit_sum_a = 0;                          // ¸¡º÷¼ºÇÔ
    }
    $query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=201001 and pl_bs_ym<=%d and note='¥«¥×¥é±Ä¶È³°¼ý±×·×ºÆ·×»»'", $yyyymm);
    if (getUniResult($query, $rui_c_nonope_profit_sum_b) < 1) {
        $rui_c_nonope_profit_sum_b = 0;                          // ¸¡º÷¼ºÇÔ
    }
    $rui_c_nonope_profit_sum = $rui_c_nonope_profit_sum_a + $rui_c_nonope_profit_sum_b;
    if ($yyyymm < 201001) {
        $rui_c_nonope_profit_sum = $rui_c_nonope_profit_sum - $rui_n_nonope_profit_sum;
    }
    $rui_c_nonope_profit_sum = $rui_c_nonope_profit_sum - $rui_n_pother_a;
    $rui_c_nonope_profit_sum = number_format(($rui_c_nonope_profit_sum / $tani), $keta);
} else {
    $query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='¥«¥×¥é±Ä¶È³°¼ý±×·×'", $str_ym, $yyyymm);
    if (getUniResult($query, $rui_c_nonope_profit_sum) < 1) {
        $rui_c_nonope_profit_sum = 0;                           // ¸¡º÷¼ºÇÔ
    } else {
        $rui_c_nonope_profit_sum = $rui_c_nonope_profit_sum - $rui_n_nonope_profit_sum;
        $rui_c_nonope_profit_sum = number_format(($rui_c_nonope_profit_sum / $tani), $keta);
    }
}
if ($yyyymm >= 201004) {
    $query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='¥ê¥Ë¥¢±Ä¶È³°¼ý±×·×ºÆ·×»»'", $str_ym, $yyyymm);
    if (getUniResult($query, $rui_l_nonope_profit_sum) < 1) {
        $rui_l_nonope_profit_sum = 0 - $rui_s_nonope_profit_sum_sagaku;   // ¸¡º÷¼ºÇÔ
        $rui_lh_nonope_profit_sum = 0;     // ¸¡º÷¼ºÇÔ
        $rui_lh_nonope_profit_sum_sagaku = 0;     // ¸¡º÷¼ºÇÔ
    } else {
        //$rui_l_nonope_profit_sum      = $rui_l_nonope_profit_sum - $rui_s_nonope_profit_sum;
        $rui_lh_nonope_profit_sum = $rui_l_nonope_profit_sum - $rui_b_nonope_profit_sum_sagaku;// - $rui_s_nonope_profit_sum_sagaku - $rui_b_nonope_profit_sum_sagaku;
        $rui_lh_nonope_profit_sum_sagaku = $rui_lh_nonope_profit_sum;
        //$rui_l_nonope_profit_sum         = $rui_l_nonope_profit_sum - $rui_s_nonope_profit_sum_sagaku;     // »î¸³½¤Íý±Ä¶È³°¼ý±×·×¤ò¥ê¥Ë¥¢¤Î±Ä¶È³°¼ý±×·×¤è¤ê¥Þ¥¤¥Ê¥¹
        $rui_l_nonope_profit_sum_temp = $rui_l_nonope_profit_sum;         // ·Ð¾ïÍø±×·×»»ÍÑ
        $rui_lh_nonope_profit_sum = number_format(($rui_lh_nonope_profit_sum / $tani), $keta);
        $rui_l_nonope_profit_sum      = number_format(($rui_l_nonope_profit_sum / $tani), $keta);
    }
} elseif ($yyyymm >= 201001 && $yyyymm <= 201003) {
    $query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=200904 and pl_bs_ym<=200912 and note='¥ê¥Ë¥¢±Ä¶È³°¼ý±×·×'");
    if (getUniResult($query, $rui_l_nonope_profit_sum_a) < 1) {
        $rui_l_nonope_profit_sum_a = 0;                          // ¸¡º÷¼ºÇÔ
    }
    $query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=201001 and pl_bs_ym<=%d and note='¥ê¥Ë¥¢±Ä¶È³°¼ý±×·×ºÆ·×»»'", $yyyymm);
    if (getUniResult($query, $rui_l_nonope_profit_sum_b) < 1) {
        $rui_l_nonope_profit_sum_b = 0;                          // ¸¡º÷¼ºÇÔ
    }
    $rui_l_nonope_profit_sum      = $rui_l_nonope_profit_sum_a + $rui_l_nonope_profit_sum_b;
    $rui_l_nonope_profit_sum      = $rui_l_nonope_profit_sum + $rui_s_nonope_profit_sum_sagaku;
    $rui_l_nonope_profit_sum      = $rui_l_nonope_profit_sum - $rui_s_gyoumu_a - $rui_s_swari_a - $rui_s_pother_a;
    $rui_lh_nonope_profit_sum = $rui_l_nonope_profit_sum - $rui_s_nonope_profit_sum_sagaku - $rui_b_nonope_profit_sum_sagaku;
    $rui_lh_nonope_profit_sum_sagaku = $rui_lh_nonope_profit_sum;
    $rui_l_nonope_profit_sum         = $rui_l_nonope_profit_sum - $rui_s_nonope_profit_sum_sagaku;     // »î¸³½¤Íý±Ä¶È³°¼ý±×·×¤ò¥ê¥Ë¥¢¤Î±Ä¶È³°¼ý±×·×¤è¤ê¥Þ¥¤¥Ê¥¹
    $rui_l_nonope_profit_sum_temp = $rui_l_nonope_profit_sum;         // ·Ð¾ïÍø±×·×»»ÍÑ
    $rui_lh_nonope_profit_sum = number_format(($rui_lh_nonope_profit_sum / $tani), $keta);
    $rui_l_nonope_profit_sum      = number_format(($rui_l_nonope_profit_sum / $tani), $keta);
} else {
    $query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='¥ê¥Ë¥¢±Ä¶È³°¼ý±×·×'", $str_ym, $yyyymm);
    if (getUniResult($query, $rui_l_nonope_profit_sum) < 1) {
        $rui_l_nonope_profit_sum = 0 - $rui_s_nonope_profit_sum_sagaku;   // ¸¡º÷¼ºÇÔ
        $rui_lh_nonope_profit_sum = 0;     // ¸¡º÷¼ºÇÔ
        $rui_lh_nonope_profit_sum_sagaku = 0;     // ¸¡º÷¼ºÇÔ
    } else {
        //$rui_l_nonope_profit_sum = $rui_l_nonope_profit_sum - $rui_s_nonope_profit_sum_sagaku;
        $rui_lh_nonope_profit_sum = $rui_l_nonope_profit_sum - $rui_s_nonope_profit_sum_sagaku - $rui_b_nonope_profit_sum_sagaku;
        $rui_lh_nonope_profit_sum_sagaku = $rui_lh_nonope_profit_sum;
        $rui_l_nonope_profit_sum         = $rui_l_nonope_profit_sum - $rui_s_nonope_profit_sum_sagaku;     // »î¸³½¤Íý±Ä¶È³°¼ý±×·×¤ò¥ê¥Ë¥¢¤Î±Ä¶È³°¼ý±×·×¤è¤ê¥Þ¥¤¥Ê¥¹
        $rui_lh_nonope_profit_sum = number_format(($rui_lh_nonope_profit_sum / $tani), $keta);
        $rui_l_nonope_profit_sum = number_format(($rui_l_nonope_profit_sum / $tani), $keta);
    }
}

/********** ±Ä¶È³°ÈñÍÑ¤Î»ÙÊ§ÍøÂ© **********/
    ///// Åö·î
if ($yyyymm >= 201001) {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='¾¦´É»ÙÊ§ÍøÂ©ºÆ·×»»'", $yyyymm);
    if (getUniResult($query, $n_srisoku) < 1) {
        $n_srisoku        = 0;                      // ¸¡º÷¼ºÇÔ
        $n_srisoku_temp = 0;
    } else {
        $n_srisoku_temp = $n_srisoku;
    }
} else {
    $n_srisoku     = 0;
}
if ($yyyymm >= 201001) {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='»î½¤»ÙÊ§ÍøÂ©ºÆ·×»»'", $yyyymm);
} else {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='»î½¤»ÙÊ§ÍøÂ©'", $yyyymm);
}
if (getUniResult($query, $s_srisoku) < 1) {
    $s_srisoku        = 0;                      // ¸¡º÷¼ºÇÔ
    $s_srisoku_sagaku = 0;
} else {
    $s_srisoku_sagaku = $s_srisoku;
    $s_srisoku        = number_format(($s_srisoku / $tani), $keta);
}
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='Á´ÂÎ»ÙÊ§ÍøÂ©'", $yyyymm);
if (getUniResult($query, $all_srisoku) < 1) {
    $all_srisoku = 0;                           // ¸¡º÷¼ºÇÔ
} else {
    $all_srisoku = number_format(($all_srisoku / $tani), $keta);
}
if ($yyyymm >= 201001) {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='¥«¥×¥é»ÙÊ§ÍøÂ©ºÆ·×»»'", $yyyymm);
} else {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='¥«¥×¥é»ÙÊ§ÍøÂ©'", $yyyymm);
}
if (getUniResult($query, $c_srisoku) < 1) {
    $c_srisoku = 0;                             // ¸¡º÷¼ºÇÔ
} else {
    $c_srisoku = number_format(($c_srisoku / $tani), $keta);
}
if ($yyyymm >= 201001) {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='µ¡¹©»ÙÊ§ÍøÂ©ºÆ·×»»'", $yyyymm);
} else {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='µ¡¹©»ÙÊ§ÍøÂ©'", $yyyymm);
}
if (getUniResult($query, $b_srisoku) < 1) {
    $b_srisoku        = 0;                      // ¸¡º÷¼ºÇÔ
    $b_srisoku_sagaku = 0;
} else {
    $b_srisoku_sagaku = $b_srisoku;
    $b_srisoku        = number_format(($b_srisoku / $tani), $keta);
}
if ($yyyymm >= 201001) {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='¥ê¥Ë¥¢»ÙÊ§ÍøÂ©ºÆ·×»»'", $yyyymm);
} else {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='¥ê¥Ë¥¢»ÙÊ§ÍøÂ©'", $yyyymm);
}
if (getUniResult($query, $l_srisoku) < 1) {
    $l_srisoku = 0 - $s_srisoku_sagaku;     // ¸¡º÷¼ºÇÔ
    $lh_srisoku = 0;     // ¸¡º÷¼ºÇÔ
    $lh_srisoku_sagaku = 0;     // ¸¡º÷¼ºÇÔ
} else {
    if ($yyyymm >= 201001) {
        $l_srisoku = $l_srisoku + $s_srisoku_sagaku;
    }
    $lh_srisoku = $l_srisoku - $s_srisoku_sagaku - $b_srisoku_sagaku;
    $lh_srisoku_sagaku = $lh_srisoku;
    $l_srisoku         = $l_srisoku - $s_srisoku_sagaku;     // »î¸³½¤Íý»ÙÊ§ÍøÂ©¤ò¥ê¥Ë¥¢¤Î»ÙÊ§ÍøÂ©¤è¤ê¥Þ¥¤¥Ê¥¹
    $lh_srisoku = number_format(($lh_srisoku / $tani), $keta);
    $l_srisoku = number_format(($l_srisoku / $tani), $keta);
}
    ///// Á°·î
if ($p1_ym >= 201001) {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='¾¦´É»ÙÊ§ÍøÂ©ºÆ·×»»'", $p1_ym);
    if (getUniResult($query, $p1_n_srisoku) < 1) {
        $p1_n_srisoku        = 0;                      // ¸¡º÷¼ºÇÔ
        $p1_n_srisoku_temp = 0;
    } else {
        $p1_n_srisoku_temp = $p1_n_srisoku;
    }
} else {
    $p1_n_srisoku     = 0;
}
if ($p1_ym >= 201001) {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='»î½¤»ÙÊ§ÍøÂ©ºÆ·×»»'", $p1_ym);
} else {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='»î½¤»ÙÊ§ÍøÂ©'", $p1_ym);
}
if (getUniResult($query, $p1_s_srisoku) < 1) {
    $p1_s_srisoku        = 0;                      // ¸¡º÷¼ºÇÔ
    $p1_s_srisoku_sagaku = 0;
} else {
    $p1_s_srisoku_sagaku = $p1_s_srisoku;
    $p1_s_srisoku        = number_format(($p1_s_srisoku / $tani), $keta);
}
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='Á´ÂÎ»ÙÊ§ÍøÂ©'", $p1_ym);
if (getUniResult($query, $p1_all_srisoku) < 1) {
    $p1_all_srisoku = 0;                        // ¸¡º÷¼ºÇÔ
} else {
    $p1_all_srisoku = number_format(($p1_all_srisoku / $tani), $keta);
}
if ($p1_ym >= 201001) {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='¥«¥×¥é»ÙÊ§ÍøÂ©ºÆ·×»»'", $p1_ym);
} else {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='¥«¥×¥é»ÙÊ§ÍøÂ©'", $p1_ym);
}
if (getUniResult($query, $p1_c_srisoku) < 1) {
    $p1_c_srisoku = 0;                             // ¸¡º÷¼ºÇÔ
} else {
    $p1_c_srisoku = number_format(($p1_c_srisoku / $tani), $keta);
}
if ($p1_ym >= 201001) {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='µ¡¹©»ÙÊ§ÍøÂ©ºÆ·×»»'", $p1_ym);
} else {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='µ¡¹©»ÙÊ§ÍøÂ©'", $p1_ym);
}
if (getUniResult($query, $p1_b_srisoku) < 1) {
    $p1_b_srisoku        = 0;                      // ¸¡º÷¼ºÇÔ
    $p1_b_srisoku_sagaku = 0;
} else {
    $p1_b_srisoku_sagaku = $p1_b_srisoku;
    $p1_b_srisoku        = number_format(($p1_b_srisoku / $tani), $keta);
}
if ($p1_ym >= 201001) {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='¥ê¥Ë¥¢»ÙÊ§ÍøÂ©ºÆ·×»»'", $p1_ym);
} else {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='¥ê¥Ë¥¢»ÙÊ§ÍøÂ©'", $p1_ym);
}
if (getUniResult($query, $p1_l_srisoku) < 1) {
    $p1_l_srisoku = 0 - $p1_s_srisoku_sagaku;     // ¸¡º÷¼ºÇÔ
    $p1_lh_srisoku = 0;     // ¸¡º÷¼ºÇÔ
    $p1_lh_srisoku_sagaku = 0;     // ¸¡º÷¼ºÇÔ
} else {
    if ($p1_ym >= 201001) {
        $p1_l_srisoku = $p1_l_srisoku + $p1_s_srisoku_sagaku;
    }
    $p1_lh_srisoku = $p1_l_srisoku - $p1_s_srisoku_sagaku - $p1_b_srisoku_sagaku;
    $p1_lh_srisoku_sagaku = $p1_lh_srisoku;
    $p1_l_srisoku         = $p1_l_srisoku - $p1_s_srisoku_sagaku;     // »î¸³½¤Íý»ÙÊ§ÍøÂ©¤ò¥ê¥Ë¥¢¤Î»ÙÊ§ÍøÂ©¤è¤ê¥Þ¥¤¥Ê¥¹
    $p1_lh_srisoku = number_format(($p1_lh_srisoku / $tani), $keta);
    $p1_l_srisoku = number_format(($p1_l_srisoku / $tani), $keta);
}
    ///// Á°Á°·î
if ($p2_ym >= 201001) {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='¾¦´É»ÙÊ§ÍøÂ©ºÆ·×»»'", $p2_ym);
    if (getUniResult($query, $p2_n_srisoku) < 1) {
        $p2_n_srisoku        = 0;                      // ¸¡º÷¼ºÇÔ
        $p2_n_srisoku_temp = 0;
    } else {
        $p2_n_srisoku_temp = $p2_n_srisoku;
    }
} else {
    $p2_n_srisoku     = 0;
}
if ($p2_ym >= 201001) {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='»î½¤»ÙÊ§ÍøÂ©ºÆ·×»»'", $p2_ym);
} else {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='»î½¤»ÙÊ§ÍøÂ©'", $p2_ym);
}
if (getUniResult($query, $p2_s_srisoku) < 1) {
    $p2_s_srisoku        = 0;                      // ¸¡º÷¼ºÇÔ
    $p2_s_srisoku_sagaku = 0;
} else {
    $p2_s_srisoku_sagaku = $p2_s_srisoku;
    $p2_s_srisoku        = number_format(($p2_s_srisoku / $tani), $keta);
}
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='Á´ÂÎ»ÙÊ§ÍøÂ©'", $p2_ym);
if (getUniResult($query, $p2_all_srisoku) < 1) {
    $p2_all_srisoku = 0;                        // ¸¡º÷¼ºÇÔ
} else {
    $p2_all_srisoku = number_format(($p2_all_srisoku / $tani), $keta);
}
if ($p2_ym >= 201001) {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='¥«¥×¥é»ÙÊ§ÍøÂ©ºÆ·×»»'", $p2_ym);
} else {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='¥«¥×¥é»ÙÊ§ÍøÂ©'", $p2_ym);
}
if (getUniResult($query, $p2_c_srisoku) < 1) {
    $p2_c_srisoku = 0;                             // ¸¡º÷¼ºÇÔ
} else {
    $p2_c_srisoku = number_format(($p2_c_srisoku / $tani), $keta);
}
if ($p2_ym >= 201001) {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='µ¡¹©»ÙÊ§ÍøÂ©ºÆ·×»»'", $p2_ym);
} else {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='µ¡¹©»ÙÊ§ÍøÂ©'", $p2_ym);
}
if (getUniResult($query, $p2_b_srisoku) < 1) {
    $p2_b_srisoku        = 0;                      // ¸¡º÷¼ºÇÔ
    $p2_b_srisoku_sagaku = 0;
} else {
    $p2_b_srisoku_sagaku = $p2_b_srisoku;
    $p2_b_srisoku        = number_format(($p2_b_srisoku / $tani), $keta);
}
if ($p2_ym >= 201001) {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='¥ê¥Ë¥¢»ÙÊ§ÍøÂ©ºÆ·×»»'", $p2_ym);
} else {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='¥ê¥Ë¥¢»ÙÊ§ÍøÂ©'", $p2_ym);
}
if (getUniResult($query, $p2_l_srisoku) < 1) {
    $p2_l_srisoku = 0 - $p2_s_srisoku_sagaku;     // ¸¡º÷¼ºÇÔ
    $p2_lh_srisoku = 0;     // ¸¡º÷¼ºÇÔ
    $p2_lh_srisoku_sagaku = 0;     // ¸¡º÷¼ºÇÔ
} else {
    if ($p2_ym >= 201001) {
        $p2_l_srisoku = $p2_l_srisoku + $p2_s_srisoku_sagaku;
    }
    $p2_lh_srisoku = $p2_l_srisoku - $p2_s_srisoku_sagaku - $p2_b_srisoku_sagaku;
    $p2_lh_srisoku_sagaku = $p2_lh_srisoku;
    $p2_l_srisoku         = $p2_l_srisoku - $p2_s_srisoku_sagaku;     // »î¸³½¤Íý»ÙÊ§ÍøÂ©¤ò¥ê¥Ë¥¢¤Î»ÙÊ§ÍøÂ©¤è¤ê¥Þ¥¤¥Ê¥¹
    $p2_lh_srisoku = number_format(($p2_lh_srisoku / $tani), $keta);
    $p2_l_srisoku = number_format(($p2_l_srisoku / $tani), $keta);
}
    ///// º£´üÎß·×
if ($yyyymm >= 201004) {
    $query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='¾¦´É»ÙÊ§ÍøÂ©ºÆ·×»»'", $str_ym, $yyyymm);
    if (getUniResult($query, $rui_n_srisoku) < 1) {
        $rui_n_srisoku = 0;                           // ¸¡º÷¼ºÇÔ
    }
} elseif ($yyyymm >= 201001 && $yyyymm <= 201003) {
    $rui_n_srisoku_a = 0;
    $query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=201001 and pl_bs_ym<=%d and note='¾¦´É»ÙÊ§ÍøÂ©ºÆ·×»»'", $yyyymm);
    if (getUniResult($query, $rui_n_srisoku_b) < 1) {
        $rui_n_srisoku_b = 0;                          // ¸¡º÷¼ºÇÔ
    }
    $rui_n_srisoku = $rui_n_srisoku_a + $rui_n_srisoku_b;
} else {
    $rui_n_srisoku = 0;
}

if ($yyyymm >= 201004) {
    $query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='»î½¤»ÙÊ§ÍøÂ©ºÆ·×»»'", $str_ym, $yyyymm);
    if (getUniResult($query, $rui_s_srisoku) < 1) {
        $rui_s_srisoku = 0;                           // ¸¡º÷¼ºÇÔ
    } else {
        $rui_s_srisoku_sagaku = $rui_s_srisoku;
        $rui_s_srisoku = number_format(($rui_s_srisoku / $tani), $keta);
    }
} elseif ($yyyymm >= 201001 && $yyyymm <= 201003) {
    $query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=200904 and pl_bs_ym<=200912 and note='»î½¤»ÙÊ§ÍøÂ©'");
    if (getUniResult($query, $rui_s_srisoku_a) < 1) {
        $rui_s_srisoku_a = 0;                          // ¸¡º÷¼ºÇÔ
    }
    $query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=201001 and pl_bs_ym<=%d and note='»î½¤»ÙÊ§ÍøÂ©ºÆ·×»»'", $yyyymm);
    if (getUniResult($query, $rui_s_srisoku_b) < 1) {
        $rui_s_srisoku_b = 0;                          // ¸¡º÷¼ºÇÔ
    }
    $rui_s_srisoku = $rui_s_srisoku_a + $rui_s_srisoku_b;
    $rui_s_srisoku_sagaku = $rui_s_srisoku;
    $rui_s_srisoku = number_format(($rui_s_srisoku / $tani), $keta);
} else {
    $query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='»î½¤»ÙÊ§ÍøÂ©'", $str_ym, $yyyymm);
    if (getUniResult($query, $rui_s_srisoku) < 1) {
        $rui_s_srisoku        = 0;                  // ¸¡º÷¼ºÇÔ
        $rui_s_srisoku_sagaku = 0;
    } else {
        $rui_s_srisoku_sagaku = $rui_s_srisoku;
        $rui_s_srisoku = number_format(($rui_s_srisoku / $tani), $keta);
    }
}

$query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='Á´ÂÎ»ÙÊ§ÍøÂ©'", $str_ym, $yyyymm);
if (getUniResult($query, $rui_all_srisoku) < 1) {
    $rui_all_srisoku = 0;                       // ¸¡º÷¼ºÇÔ
} else {
    $rui_all_srisoku = number_format(($rui_all_srisoku / $tani), $keta);
}
if ($yyyymm >= 201004) {
    $query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='¥«¥×¥é»ÙÊ§ÍøÂ©ºÆ·×»»'", $str_ym, $yyyymm);
    if (getUniResult($query, $rui_c_srisoku) < 1) {
        $rui_c_srisoku = 0;                           // ¸¡º÷¼ºÇÔ
    } else {
        $rui_c_srisoku = number_format(($rui_c_srisoku / $tani), $keta);
    }
} elseif ($yyyymm >= 201001 && $yyyymm <= 201003) {
    $query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=200904 and pl_bs_ym<=200912 and note='¥«¥×¥é»ÙÊ§ÍøÂ©'");
    if (getUniResult($query, $rui_c_srisoku_a) < 1) {
        $rui_c_srisoku_a = 0;                          // ¸¡º÷¼ºÇÔ
    }
    $query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=201001 and pl_bs_ym<=%d and note='¥«¥×¥é»ÙÊ§ÍøÂ©ºÆ·×»»'", $yyyymm);
    if (getUniResult($query, $rui_c_srisoku_b) < 1) {
        $rui_c_srisoku_b = 0;                          // ¸¡º÷¼ºÇÔ
    }
    $rui_c_srisoku = $rui_c_srisoku_a + $rui_c_srisoku_b;
    $rui_c_srisoku = number_format(($rui_c_srisoku / $tani), $keta);
} else {
    $query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='¥«¥×¥é»ÙÊ§ÍøÂ©'", $str_ym, $yyyymm);
    if (getUniResult($query, $rui_c_srisoku) < 1) {
        $rui_c_srisoku = 0;                           // ¸¡º÷¼ºÇÔ
    } else {
        $rui_c_srisoku = number_format(($rui_c_srisoku / $tani), $keta);
    }
}
if ($yyyymm >= 201004) {
    $query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='µ¡¹©»ÙÊ§ÍøÂ©ºÆ·×»»'", $str_ym, $yyyymm);
    if (getUniResult($query, $rui_b_srisoku) < 1) {
        $rui_b_srisoku = 0;    // ¸¡º÷¼ºÇÔ
        $rui_b_srisoku_sagaku = 0;
    } else {
        $rui_b_srisoku_sagaku = $rui_b_srisoku;
        $rui_b_srisoku = number_format(($rui_b_srisoku / $tani), $keta);
    }
} elseif ($yyyymm >= 201001 && $yyyymm <= 201003) {
    $query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=200904 and pl_bs_ym<=200912 and note='µ¡¹©»ÙÊ§ÍøÂ©'");
    if (getUniResult($query, $rui_b_srisoku_a) < 1) {
        $rui_b_srisoku_a = 0;                          // ¸¡º÷¼ºÇÔ
    }
    $query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=201001 and pl_bs_ym<=%d and note='µ¡¹©»ÙÊ§ÍøÂ©ºÆ·×»»'", $yyyymm);
    if (getUniResult($query, $rui_b_srisoku_b) < 1) {
        $rui_b_srisoku_b = 0;                          // ¸¡º÷¼ºÇÔ
    }
    $rui_b_srisoku = $rui_b_srisoku_a + $rui_b_srisoku_b;
    $rui_b_srisoku_sagaku = $rui_b_srisoku;
    $rui_b_srisoku = number_format(($rui_b_srisoku / $tani), $keta);
} else {
    $query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='µ¡¹©»ÙÊ§ÍøÂ©'", $str_ym, $yyyymm);
    if (getUniResult($query, $rui_b_srisoku) < 1) {
        $rui_b_srisoku = 0;    // ¸¡º÷¼ºÇÔ
        $rui_b_srisoku_sagaku = 0;
    } else {
        $rui_b_srisoku_sagaku = $rui_b_srisoku;
        $rui_b_srisoku = number_format(($rui_b_srisoku / $tani), $keta);
    }
}
if ($yyyymm >= 201004) {
    $query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='¥ê¥Ë¥¢»ÙÊ§ÍøÂ©ºÆ·×»»'", $str_ym, $yyyymm);
    if (getUniResult($query, $rui_l_srisoku) < 1) {
        $rui_l_srisoku = 0 - $rui_s_srisoku_sagaku;   // ¸¡º÷¼ºÇÔ
        $rui_lh_srisoku = 0;     // ¸¡º÷¼ºÇÔ
        $rui_lh_srisoku_sagaku = 0;     // ¸¡º÷¼ºÇÔ
    } else {
        $rui_l_srisoku = $rui_l_srisoku + $rui_s_srisoku_sagaku;
        $rui_lh_srisoku = $rui_l_srisoku - $rui_s_srisoku_sagaku - $rui_b_srisoku_sagaku;
        $rui_lh_srisoku_sagaku = $rui_lh_srisoku;
        $rui_l_srisoku         = $rui_l_srisoku - $rui_s_srisoku_sagaku;     // »î¸³½¤Íý»ÙÊ§ÍøÂ©¤ò¥ê¥Ë¥¢¤Î»ÙÊ§ÍøÂ©¤è¤ê¥Þ¥¤¥Ê¥¹
        $rui_lh_srisoku = number_format(($rui_lh_srisoku / $tani), $keta);
        $rui_l_srisoku = number_format(($rui_l_srisoku / $tani), $keta);
    }
} elseif ($yyyymm >= 201001 && $yyyymm <= 201003) {
    $query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=200904 and pl_bs_ym<=200912 and note='¥ê¥Ë¥¢»ÙÊ§ÍøÂ©'");
    if (getUniResult($query, $rui_l_srisoku_a) < 1) {
        $rui_l_srisoku_a = 0;                          // ¸¡º÷¼ºÇÔ
    }
    $query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=201001 and pl_bs_ym<=%d and note='¥ê¥Ë¥¢»ÙÊ§ÍøÂ©ºÆ·×»»'", $yyyymm);
    if (getUniResult($query, $rui_l_srisoku_b) < 1) {
        $rui_l_srisoku_b = 0;                          // ¸¡º÷¼ºÇÔ
    }
    $rui_l_srisoku = $rui_l_srisoku_a + $rui_l_srisoku_b;
    $rui_l_srisoku = $rui_l_srisoku - $rui_s_srisoku_a;
    $rui_l_srisoku = $rui_l_srisoku + $rui_s_srisoku_sagaku;
    $rui_lh_srisoku = $rui_l_srisoku - $rui_s_srisoku_sagaku - $rui_b_srisoku_sagaku;
    $rui_lh_srisoku_sagaku = $rui_lh_srisoku;
    $rui_l_srisoku         = $rui_l_srisoku - $rui_s_srisoku_sagaku;     // »î¸³½¤Íý»ÙÊ§ÍøÂ©¤ò¥ê¥Ë¥¢¤Î»ÙÊ§ÍøÂ©¤è¤ê¥Þ¥¤¥Ê¥¹
    $rui_lh_srisoku = number_format(($rui_lh_srisoku / $tani), $keta);
    $rui_l_srisoku = number_format(($rui_l_srisoku / $tani), $keta);
} else {
    $query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='¥ê¥Ë¥¢»ÙÊ§ÍøÂ©'", $str_ym, $yyyymm);
    if (getUniResult($query, $rui_l_srisoku) < 1) {
        $rui_l_srisoku = 0 - $rui_s_srisoku_sagaku;   // ¸¡º÷¼ºÇÔ
        $rui_lh_srisoku = 0;     // ¸¡º÷¼ºÇÔ
        $rui_lh_srisoku_sagaku = 0;     // ¸¡º÷¼ºÇÔ
    } else {
        //$rui_l_srisoku = $rui_l_srisoku + $rui_s_srisoku_sagaku;
        $rui_lh_srisoku = $rui_l_srisoku - $rui_s_srisoku_sagaku - $rui_b_srisoku_sagaku;
        $rui_lh_srisoku_sagaku = $rui_lh_srisoku;
        $rui_l_srisoku         = $rui_l_srisoku - $rui_s_srisoku_sagaku;     // »î¸³½¤Íý»ÙÊ§ÍøÂ©¤ò¥ê¥Ë¥¢¤Î»ÙÊ§ÍøÂ©¤è¤ê¥Þ¥¤¥Ê¥¹
        $rui_lh_srisoku = number_format(($rui_lh_srisoku / $tani), $keta);
        $rui_l_srisoku = number_format(($rui_l_srisoku / $tani), $keta);
    }
}

/********** ±Ä¶È³°ÈñÍÑ¤Î¤½¤ÎÂ¾ **********/
    ///// Åö·î
if ($yyyymm >= 201001) {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='¾¦´É±Ä¶È³°ÈñÍÑ¤½¤ÎÂ¾ºÆ·×»»'", $yyyymm);
    if (getUniResult($query, $n_lother) < 1) {
        $n_lother      = 0;                       // ¸¡º÷¼ºÇÔ
        $n_lother_temp = 0;
    } else {
        $n_lother_temp = $n_lother;
    }
} else {
    $n_lother     = 0;
}
if ($yyyymm >= 201001) {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='»î½¤±Ä¶È³°ÈñÍÑ¤½¤ÎÂ¾ºÆ·×»»'", $yyyymm);
} else {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='»î½¤±Ä¶È³°ÈñÍÑ¤½¤ÎÂ¾'", $yyyymm);
}
if (getUniResult($query, $s_lother) < 1) {
    $s_lother        = 0;                       // ¸¡º÷¼ºÇÔ
    $s_lother_sagaku = 0;
} else {
    $s_lother_sagaku = $s_lother;
    $s_lother        = number_format(($s_lother / $tani), $keta);
}
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='Á´ÂÎ±Ä¶È³°ÈñÍÑ¤½¤ÎÂ¾'", $yyyymm);
if (getUniResult($query, $all_lother) < 1) {
    $all_lother = 0;                            // ¸¡º÷¼ºÇÔ
} else {
    $all_lother = number_format(($all_lother / $tani), $keta);
}
if ($yyyymm >= 201001) {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='¥«¥×¥é±Ä¶È³°ÈñÍÑ¤½¤ÎÂ¾ºÆ·×»»'", $yyyymm);
} else {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='¥«¥×¥é±Ä¶È³°ÈñÍÑ¤½¤ÎÂ¾'", $yyyymm);
}
if (getUniResult($query, $c_lother) < 1) {
    $c_lother = 0;                              // ¸¡º÷¼ºÇÔ
} else {
    $c_lother = number_format(($c_lother / $tani), $keta);
}
if ($yyyymm >= 201001) {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='µ¡¹©±Ä¶È³°ÈñÍÑ¤½¤ÎÂ¾ºÆ·×»»'", $yyyymm);
} else {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='µ¡¹©±Ä¶È³°ÈñÍÑ¤½¤ÎÂ¾'", $yyyymm);
}
if (getUniResult($query, $b_lother) < 1) {
    $b_lother        = 0;                       // ¸¡º÷¼ºÇÔ
    $b_lother_sagaku = 0;
} else {
    $b_lother_sagaku = $b_lother;
    $b_lother        = number_format(($b_lother / $tani), $keta);
}
if ($yyyymm >= 201001) {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='¥ê¥Ë¥¢±Ä¶È³°ÈñÍÑ¤½¤ÎÂ¾ºÆ·×»»'", $yyyymm);
} else {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='¥ê¥Ë¥¢±Ä¶È³°ÈñÍÑ¤½¤ÎÂ¾'", $yyyymm);
}
if (getUniResult($query, $l_lother) < 1) {
    $l_lother = 0 - $s_lother_sagaku;     // ¸¡º÷¼ºÇÔ
    $lh_lother = 0;     // ¸¡º÷¼ºÇÔ
    $lh_lother_sagaku = 0;     // ¸¡º÷¼ºÇÔ
} else {
    if ($yyyymm >= 201001) {
        $l_lother = $l_lother + $s_lother_sagaku;
    }
    $lh_lother = $l_lother - $s_lother_sagaku - $b_lother_sagaku;
    $lh_lother_sagaku = $lh_lother;
    $l_lother         = $l_lother - $s_lother_sagaku;     // »î¸³½¤Íý±Ä¶È³°ÈñÍÑ¤½¤ÎÂ¾¤ò¥ê¥Ë¥¢¤Î±Ä¶È³°ÈñÍÑ¤½¤ÎÂ¾¤è¤ê¥Þ¥¤¥Ê¥¹
    $lh_lother = number_format(($lh_lother / $tani), $keta);
    $l_lother = number_format(($l_lother / $tani), $keta);
}
    ///// Á°·î
if ($p1_ym >= 201001) {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='¾¦´É±Ä¶È³°ÈñÍÑ¤½¤ÎÂ¾ºÆ·×»»'", $p1_ym);
    if (getUniResult($query, $p1_n_lother) < 1) {
        $p1_n_lother      = 0;                       // ¸¡º÷¼ºÇÔ
        $p1_n_lother_temp = 0;
    } else {
        $p1_n_lother_temp = $p1_n_lother;
    }
} else {
    $p1_n_lother     = 0;
}
if ($p1_ym >= 201001) {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='»î½¤±Ä¶È³°ÈñÍÑ¤½¤ÎÂ¾ºÆ·×»»'", $p1_ym);
} else {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='»î½¤±Ä¶È³°ÈñÍÑ¤½¤ÎÂ¾'", $p1_ym);
}
if (getUniResult($query, $p1_s_lother) < 1) {
    $p1_s_lother        = 0;                       // ¸¡º÷¼ºÇÔ
    $p1_s_lother_sagaku = 0;
} else {
    $p1_s_lother_sagaku = $p1_s_lother;
    $p1_s_lother        = number_format(($p1_s_lother / $tani), $keta);
}
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='Á´ÂÎ±Ä¶È³°ÈñÍÑ¤½¤ÎÂ¾'", $p1_ym);
if (getUniResult($query, $p1_all_lother) < 1) {
    $p1_all_lother = 0;                         // ¸¡º÷¼ºÇÔ
} else {
    $p1_all_lother = number_format(($p1_all_lother / $tani), $keta);
}
if ($p1_ym >= 201001) {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='¥«¥×¥é±Ä¶È³°ÈñÍÑ¤½¤ÎÂ¾ºÆ·×»»'", $p1_ym);
} else {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='¥«¥×¥é±Ä¶È³°ÈñÍÑ¤½¤ÎÂ¾'", $p1_ym);
}
if (getUniResult($query, $p1_c_lother) < 1) {
    $p1_c_lother = 0;                              // ¸¡º÷¼ºÇÔ
} else {
    $p1_c_lother = number_format(($p1_c_lother / $tani), $keta);
}
if ($p1_ym >= 201001) {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='µ¡¹©±Ä¶È³°ÈñÍÑ¤½¤ÎÂ¾ºÆ·×»»'", $p1_ym);
} else {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='µ¡¹©±Ä¶È³°ÈñÍÑ¤½¤ÎÂ¾'", $p1_ym);
}
if (getUniResult($query, $p1_b_lother) < 1) {
    $p1_b_lother        = 0;                       // ¸¡º÷¼ºÇÔ
    $p1_b_lother_sagaku = 0;
} else {
    $p1_b_lother_sagaku = $p1_b_lother;
    $p1_b_lother        = number_format(($p1_b_lother / $tani), $keta);
}
if ($p1_ym >= 201001) {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='¥ê¥Ë¥¢±Ä¶È³°ÈñÍÑ¤½¤ÎÂ¾ºÆ·×»»'", $p1_ym);
} else {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='¥ê¥Ë¥¢±Ä¶È³°ÈñÍÑ¤½¤ÎÂ¾'", $p1_ym);
}
if (getUniResult($query, $p1_l_lother) < 1) {
    $p1_l_lother = 0 - $p1_s_lother_sagaku;     // ¸¡º÷¼ºÇÔ
    $p1_lh_lother = 0;     // ¸¡º÷¼ºÇÔ
    $p1_lh_lother_sagaku = 0;     // ¸¡º÷¼ºÇÔ
} else {
    if ($p1_ym >= 201001) {
        $p1_l_lother = $p1_l_lother + $p1_s_lother_sagaku;
    }
    $p1_lh_lother = $p1_l_lother - $p1_s_lother_sagaku - $p1_b_lother_sagaku;
    $p1_lh_lother_sagaku = $p1_lh_lother;
    $p1_l_lother         = $p1_l_lother - $p1_s_lother_sagaku;     // »î¸³½¤Íý±Ä¶È³°ÈñÍÑ¤½¤ÎÂ¾¤ò¥ê¥Ë¥¢¤Î±Ä¶È³°ÈñÍÑ¤½¤ÎÂ¾¤è¤ê¥Þ¥¤¥Ê¥¹
    $p1_lh_lother = number_format(($p1_lh_lother / $tani), $keta);
    $p1_l_lother = number_format(($p1_l_lother / $tani), $keta);
}
    ///// Á°Á°·î
if ($p2_ym >= 201001) {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='¾¦´É±Ä¶È³°ÈñÍÑ¤½¤ÎÂ¾ºÆ·×»»'", $p2_ym);
    if (getUniResult($query, $p2_n_lother) < 1) {
        $p2_n_lother      = 0;                       // ¸¡º÷¼ºÇÔ
        $p2_n_lother_temp = 0;
    } else {
        $p2_n_lother_temp = $p2_n_lother;
    }
} else {
    $p2_n_lother     = 0;
}
if ($p2_ym >= 201001) {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='»î½¤±Ä¶È³°ÈñÍÑ¤½¤ÎÂ¾ºÆ·×»»'", $p2_ym);
} else {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='»î½¤±Ä¶È³°ÈñÍÑ¤½¤ÎÂ¾'", $p2_ym);
}
if (getUniResult($query, $p2_s_lother) < 1) {
    $p2_s_lother        = 0;                       // ¸¡º÷¼ºÇÔ
    $p2_s_lother_sagaku = 0;
} else {
    $p2_s_lother_sagaku = $p2_s_lother;
    $p2_s_lother        = number_format(($p2_s_lother / $tani), $keta);
}
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='Á´ÂÎ±Ä¶È³°ÈñÍÑ¤½¤ÎÂ¾'", $p2_ym);
if (getUniResult($query, $p2_all_lother) < 1) {
    $p2_all_lother = 0;                         // ¸¡º÷¼ºÇÔ
} else {
    $p2_all_lother = number_format(($p2_all_lother / $tani), $keta);
}
if ($p2_ym >= 201001) {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='¥«¥×¥é±Ä¶È³°ÈñÍÑ¤½¤ÎÂ¾ºÆ·×»»'", $p2_ym);
} else {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='¥«¥×¥é±Ä¶È³°ÈñÍÑ¤½¤ÎÂ¾'", $p2_ym);
}
if (getUniResult($query, $p2_c_lother) < 1) {
    $p2_c_lother = 0;                              // ¸¡º÷¼ºÇÔ
} else {
    $p2_c_lother = number_format(($p2_c_lother / $tani), $keta);
}
if ($p2_ym >= 201001) {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='µ¡¹©±Ä¶È³°ÈñÍÑ¤½¤ÎÂ¾ºÆ·×»»'", $p2_ym);
} else {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='µ¡¹©±Ä¶È³°ÈñÍÑ¤½¤ÎÂ¾'", $p2_ym);
}
if (getUniResult($query, $p2_b_lother) < 1) {
    $p2_b_lother        = 0;                       // ¸¡º÷¼ºÇÔ
    $p2_b_lother_sagaku = 0;
} else {
    $p2_b_lother_sagaku = $p2_b_lother;
    $p2_b_lother        = number_format(($p2_b_lother / $tani), $keta);
}
if ($p2_ym >= 201001) {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='¥ê¥Ë¥¢±Ä¶È³°ÈñÍÑ¤½¤ÎÂ¾ºÆ·×»»'", $p2_ym);
} else {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='¥ê¥Ë¥¢±Ä¶È³°ÈñÍÑ¤½¤ÎÂ¾'", $p2_ym);
}
if (getUniResult($query, $p2_l_lother) < 1) {
    $p2_l_lother = 0 - $p1_s_lother_sagaku;     // ¸¡º÷¼ºÇÔ
    $p2_lh_lother = 0;     // ¸¡º÷¼ºÇÔ
    $p2_lh_lother_sagaku = 0;     // ¸¡º÷¼ºÇÔ
} else {
    if ($p2_ym >= 201001) {
        $p2_l_lother = $p2_l_lother + $p2_s_lother_sagaku;
    }
    $p2_lh_lother = $p2_l_lother - $p2_s_lother_sagaku - $p2_b_lother_sagaku;
    $p2_lh_lother_sagaku = $p2_lh_lother;
    $p2_l_lother         = $p2_l_lother - $p2_s_lother_sagaku;     // »î¸³½¤Íý±Ä¶È³°ÈñÍÑ¤½¤ÎÂ¾¤ò¥ê¥Ë¥¢¤Î±Ä¶È³°ÈñÍÑ¤½¤ÎÂ¾¤è¤ê¥Þ¥¤¥Ê¥¹
    $p2_lh_lother = number_format(($p2_lh_lother / $tani), $keta);
    $p2_l_lother = number_format(($p2_l_lother / $tani), $keta);
}
    ///// º£´üÎß·×
if ($yyyymm >= 201004) {
    $query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='¾¦´É±Ä¶È³°ÈñÍÑ¤½¤ÎÂ¾ºÆ·×»»'", $str_ym, $yyyymm);
    if (getUniResult($query, $rui_n_lother) < 1) {
        $rui_n_lother = 0;                           // ¸¡º÷¼ºÇÔ
    }
} elseif ($yyyymm >= 201001 && $yyyymm <= 201003) {
    $rui_n_lother_a = 0;
    $query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=201001 and pl_bs_ym<=%d and note='¾¦´É±Ä¶È³°ÈñÍÑ¤½¤ÎÂ¾ºÆ·×»»'", $yyyymm);
    if (getUniResult($query, $rui_n_lother_b) < 1) {
        $rui_n_lother_b = 0;                          // ¸¡º÷¼ºÇÔ
    }
    $rui_n_lother = $rui_n_lother_a + $rui_n_lother_b;
} else {
    $rui_n_lother = 0;
}
if ($yyyymm >= 201004) {
    $query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='»î½¤±Ä¶È³°ÈñÍÑ¤½¤ÎÂ¾ºÆ·×»»'", $str_ym, $yyyymm);
    if (getUniResult($query, $rui_s_lother) < 1) {
        $rui_s_lother = 0;                           // ¸¡º÷¼ºÇÔ
    } else {
        $rui_s_lother_sagaku = $rui_s_lother;
        $rui_s_lother = number_format(($rui_s_lother / $tani), $keta);
    }
} elseif ($yyyymm >= 201001 && $yyyymm <= 201003) {
    $query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=200904 and pl_bs_ym<=200912 and note='»î½¤±Ä¶È³°ÈñÍÑ¤½¤ÎÂ¾'");
    if (getUniResult($query, $rui_s_lother_a) < 1) {
        $rui_s_lother_a = 0;                          // ¸¡º÷¼ºÇÔ
    }
    $query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=201001 and pl_bs_ym<=%d and note='»î½¤±Ä¶È³°ÈñÍÑ¤½¤ÎÂ¾ºÆ·×»»'", $yyyymm);
    if (getUniResult($query, $rui_s_lother_b) < 1) {
        $rui_s_lother_b = 0;                          // ¸¡º÷¼ºÇÔ
    }
    $rui_s_lother = $rui_s_lother_a + $rui_s_lother_b;
    $rui_s_lother_sagaku = $rui_s_lother;
    $rui_s_lother = number_format(($rui_s_lother / $tani), $keta);
} else {
    $query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='»î½¤±Ä¶È³°ÈñÍÑ¤½¤ÎÂ¾'", $str_ym, $yyyymm);
    if (getUniResult($query, $rui_s_lother) < 1) {
        $rui_s_lother        = 0;                   // ¸¡º÷¼ºÇÔ
        $rui_s_lother_sagaku = 0;
    } else {
        $rui_s_lother_sagaku = $rui_s_lother;
        $rui_s_lother        = number_format(($rui_s_lother / $tani), $keta);
    }
}
$query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='Á´ÂÎ±Ä¶È³°ÈñÍÑ¤½¤ÎÂ¾'", $str_ym, $yyyymm);
if (getUniResult($query, $rui_all_lother) < 1) {
    $rui_all_lother = 0;                        // ¸¡º÷¼ºÇÔ
} else {
    $rui_all_lother = number_format(($rui_all_lother / $tani), $keta);
}
if ($yyyymm >= 201004) {
    $query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='¥«¥×¥é±Ä¶È³°ÈñÍÑ¤½¤ÎÂ¾ºÆ·×»»'", $str_ym, $yyyymm);
    if (getUniResult($query, $rui_c_lother) < 1) {
        $rui_c_lother = 0;                           // ¸¡º÷¼ºÇÔ
    } else {
        $rui_c_lother = number_format(($rui_c_lother / $tani), $keta);
    }
} elseif ($yyyymm >= 201001 && $yyyymm <= 201003) {
    $query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=200904 and pl_bs_ym<=200912 and note='¥«¥×¥é±Ä¶È³°ÈñÍÑ¤½¤ÎÂ¾'");
    if (getUniResult($query, $rui_c_lother_a) < 1) {
        $rui_c_lother_a = 0;                          // ¸¡º÷¼ºÇÔ
    }
    $query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=201001 and pl_bs_ym<=%d and note='¥«¥×¥é±Ä¶È³°ÈñÍÑ¤½¤ÎÂ¾ºÆ·×»»'", $yyyymm);
    if (getUniResult($query, $rui_c_lother_b) < 1) {
        $rui_c_lother_b = 0;                          // ¸¡º÷¼ºÇÔ
    }
    $rui_c_lother = $rui_c_lother_a + $rui_c_lother_b;
    $rui_c_lother = number_format(($rui_c_lother / $tani), $keta);
} else {
    $query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='¥«¥×¥é±Ä¶È³°ÈñÍÑ¤½¤ÎÂ¾'", $str_ym, $yyyymm);
    if (getUniResult($query, $rui_c_lother) < 1) {
        $rui_c_lother = 0;                           // ¸¡º÷¼ºÇÔ
    } else {
        $rui_c_lother = number_format(($rui_c_lother / $tani), $keta);
    }
}
if ($yyyymm >= 201004) {
    $query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='µ¡¹©±Ä¶È³°ÈñÍÑ¤½¤ÎÂ¾ºÆ·×»»'", $str_ym, $yyyymm);
    if (getUniResult($query, $rui_b_lother) < 1) {
        $rui_b_lother = 0;    // ¸¡º÷¼ºÇÔ
        $rui_b_lother_sagaku = 0;
    } else {
        $rui_b_lother_sagaku = $rui_b_lother;
        $rui_b_lother = number_format(($rui_b_lother / $tani), $keta);
    }
} elseif ($yyyymm >= 201001 && $yyyymm <= 201003) {
    $query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=200904 and pl_bs_ym<=200912 and note='µ¡¹©±Ä¶È³°ÈñÍÑ¤½¤ÎÂ¾'");
    if (getUniResult($query, $rui_b_lother_a) < 1) {
        $rui_b_lother_a = 0;                          // ¸¡º÷¼ºÇÔ
    }
    $query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=201001 and pl_bs_ym<=%d and note='µ¡¹©±Ä¶È³°ÈñÍÑ¤½¤ÎÂ¾ºÆ·×»»'", $yyyymm);
    if (getUniResult($query, $rui_b_lother_b) < 1) {
        $rui_b_lother_b = 0;                          // ¸¡º÷¼ºÇÔ
    }
    $rui_b_lother = $rui_b_lother_a + $rui_b_lother_b;
    $rui_b_lother_sagaku = $rui_b_lother;
    $rui_b_lother = number_format(($rui_b_lother / $tani), $keta);
} else {
    $query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='µ¡¹©±Ä¶È³°ÈñÍÑ¤½¤ÎÂ¾'", $str_ym, $yyyymm);
    if (getUniResult($query, $rui_b_lother) < 1) {
        $rui_b_lother = 0;    // ¸¡º÷¼ºÇÔ
        $rui_b_lother_sagaku = 0;
    } else {
        $rui_b_lother_sagaku = $rui_b_lother;
        $rui_b_lother = number_format(($rui_b_lother / $tani), $keta);
    }
}
if ($yyyymm >= 201004) {
    $query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='¥ê¥Ë¥¢±Ä¶È³°ÈñÍÑ¤½¤ÎÂ¾ºÆ·×»»'", $str_ym, $yyyymm);
    if (getUniResult($query, $rui_l_lother) < 1) {
        $rui_l_lother = 0 - $rui_s_lother_sagaku;   // ¸¡º÷¼ºÇÔ
        $rui_lh_lother = 0;     // ¸¡º÷¼ºÇÔ
        $rui_lh_lother_sagaku = 0;     // ¸¡º÷¼ºÇÔ
    } else {
        $rui_l_lother = $rui_l_lother + $rui_s_lother_sagaku;
        $rui_lh_lother = $rui_l_lother - $rui_s_lother_sagaku - $rui_b_lother_sagaku;
        $rui_lh_lother_sagaku = $rui_lh_lother;
        $rui_l_lother         = $rui_l_lother - $rui_s_lother_sagaku;     // »î¸³½¤Íý±Ä¶È³°ÈñÍÑ¤½¤ÎÂ¾¤ò¥ê¥Ë¥¢¤Î±Ä¶È³°ÈñÍÑ¤½¤ÎÂ¾¤è¤ê¥Þ¥¤¥Ê¥¹
        $rui_lh_lother = number_format(($rui_lh_lother / $tani), $keta);
        $rui_l_lother = number_format(($rui_l_lother / $tani), $keta);
    }
} elseif ($yyyymm >= 201001 && $yyyymm <= 201003) {
    $query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=200904 and pl_bs_ym<=200912 and note='¥ê¥Ë¥¢±Ä¶È³°ÈñÍÑ¤½¤ÎÂ¾'");
    if (getUniResult($query, $rui_l_lother_a) < 1) {
        $rui_l_lother_a = 0;                          // ¸¡º÷¼ºÇÔ
    }
    $query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=201001 and pl_bs_ym<=%d and note='¥ê¥Ë¥¢±Ä¶È³°ÈñÍÑ¤½¤ÎÂ¾ºÆ·×»»'", $yyyymm);
    if (getUniResult($query, $rui_l_lother_b) < 1) {
        $rui_l_lother_b = 0;                          // ¸¡º÷¼ºÇÔ
    }
    $rui_l_lother = $rui_l_lother_a + $rui_l_lother_b;
    $rui_l_lother = $rui_l_lother - $rui_s_lother_a;
    $rui_l_lother = $rui_l_lother + $rui_s_lother_sagaku;
    $rui_lh_lother = $rui_l_lother - $rui_s_lother_sagaku - $rui_b_lother_sagaku;
    $rui_lh_lother_sagaku = $rui_lh_lother;
    $rui_l_lother         = $rui_l_lother - $rui_s_lother_sagaku;     // »î¸³½¤Íý±Ä¶È³°ÈñÍÑ¤½¤ÎÂ¾¤ò¥ê¥Ë¥¢¤Î±Ä¶È³°ÈñÍÑ¤½¤ÎÂ¾¤è¤ê¥Þ¥¤¥Ê¥¹
    $rui_lh_lother = number_format(($rui_lh_lother / $tani), $keta);
    $rui_l_lother = number_format(($rui_l_lother / $tani), $keta);
} else {
    $query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='¥ê¥Ë¥¢±Ä¶È³°ÈñÍÑ¤½¤ÎÂ¾'", $str_ym, $yyyymm);
    if (getUniResult($query, $rui_l_lother) < 1) {
        $rui_l_lother = 0 - $rui_s_lother_sagaku;   // ¸¡º÷¼ºÇÔ
        $rui_lh_lother = 0;     // ¸¡º÷¼ºÇÔ
        $rui_lh_lother_sagaku = 0;     // ¸¡º÷¼ºÇÔ
    } else {
        //$rui_l_lother = $rui_l_lother + $rui_s_lother_sagaku;
        $rui_lh_lother = $rui_l_lother - $rui_s_lother_sagaku - $rui_b_lother_sagaku;
        $rui_lh_lother_sagaku = $rui_lh_lother;
        $rui_l_lother         = $rui_l_lother - $rui_s_lother_sagaku;     // »î¸³½¤Íý±Ä¶È³°ÈñÍÑ¤½¤ÎÂ¾¤ò¥ê¥Ë¥¢¤Î±Ä¶È³°ÈñÍÑ¤½¤ÎÂ¾¤è¤ê¥Þ¥¤¥Ê¥¹
        $rui_lh_lother = number_format(($rui_lh_lother / $tani), $keta);
        $rui_l_lother = number_format(($rui_l_lother / $tani), $keta);
    }
}
/********** ±Ä¶È³°ÈñÍÑ¤Î¹ç·× **********/
    ///// ¾¦´É
$p2_n_nonope_loss_sum  = $p2_n_srisoku + $p2_n_lother;
$p2_n_srisoku          = number_format(($p2_n_srisoku / $tani), $keta);
$p2_n_lother           = number_format(($p2_n_lother / $tani), $keta);

$p1_n_nonope_loss_sum  = $p1_n_srisoku + $p1_n_lother;
$p1_n_srisoku          = number_format(($p1_n_srisoku / $tani), $keta);
$p1_n_lother           = number_format(($p1_n_lother / $tani), $keta);

$n_nonope_loss_sum     = $n_srisoku + $n_lother;
$n_srisoku             = number_format(($n_srisoku / $tani), $keta);
$n_lother              = number_format(($n_lother / $tani), $keta);

$rui_n_nonope_loss_sum = $rui_n_srisoku + $rui_n_lother;
$rui_n_srisoku         = number_format(($rui_n_srisoku / $tani), $keta);
$rui_n_lother          = number_format(($rui_n_lother / $tani), $keta);
    ///// »î¸³¡¦½¤Íý
$p2_s_nonope_loss_sum         = $p2_s_srisoku_sagaku + $p2_s_lother_sagaku;
$p2_s_nonope_loss_sum_sagaku  = $p2_s_nonope_loss_sum;
$p2_s_nonope_loss_sum         = number_format(($p2_s_nonope_loss_sum / $tani), $keta);

$p1_s_nonope_loss_sum         = $p1_s_srisoku_sagaku + $p1_s_lother_sagaku;
$p1_s_nonope_loss_sum_sagaku  = $p1_s_nonope_loss_sum;
$p1_s_nonope_loss_sum         = number_format(($p1_s_nonope_loss_sum / $tani), $keta);

$s_nonope_loss_sum            = $s_srisoku_sagaku + $s_lother_sagaku;
$s_nonope_loss_sum_sagaku     = $s_nonope_loss_sum;
$s_nonope_loss_sum            = number_format(($s_nonope_loss_sum / $tani), $keta);

$rui_s_nonope_loss_sum        = $rui_s_srisoku_sagaku + $rui_s_lother_sagaku;
$rui_s_nonope_loss_sum_sagaku = $rui_s_nonope_loss_sum;
$rui_s_nonope_loss_sum        = number_format(($rui_s_nonope_loss_sum / $tani), $keta);
    ///// µ¡¹©
$p2_b_nonope_loss_sum         = $p2_b_srisoku_sagaku + $p2_b_lother_sagaku;
$p2_b_nonope_loss_sum_sagaku  = $p2_b_nonope_loss_sum;
$p2_b_nonope_loss_sum         = number_format(($p2_b_nonope_loss_sum / $tani), $keta);

$p1_b_nonope_loss_sum         = $p1_b_srisoku_sagaku + $p1_b_lother_sagaku;
$p1_b_nonope_loss_sum_sagaku  = $p1_b_nonope_loss_sum;
$p1_b_nonope_loss_sum         = number_format(($p1_b_nonope_loss_sum / $tani), $keta);

$b_nonope_loss_sum            = $b_srisoku_sagaku + $b_lother_sagaku;
$b_nonope_loss_sum_sagaku     = $b_nonope_loss_sum;
$b_nonope_loss_sum            = number_format(($b_nonope_loss_sum / $tani), $keta);

$rui_b_nonope_loss_sum        = $rui_b_srisoku_sagaku + $rui_b_lother_sagaku;
$rui_b_nonope_loss_sum_sagaku = $rui_b_nonope_loss_sum;
$rui_b_nonope_loss_sum        = number_format(($rui_b_nonope_loss_sum / $tani), $keta);

    ///// Åö·î
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='Á´ÂÎ±Ä¶È³°ÈñÍÑ·×'", $yyyymm);
if (getUniResult($query, $all_nonope_loss_sum) < 1) {
    $all_nonope_loss_sum = 0;                   // ¸¡º÷¼ºÇÔ
} else {
    $all_nonope_loss_sum = number_format(($all_nonope_loss_sum / $tani), $keta);
}
if ($yyyymm >= 201001) {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='¥«¥×¥é±Ä¶È³°ÈñÍÑ·×ºÆ·×»»'", $yyyymm);
} else {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='¥«¥×¥é±Ä¶È³°ÈñÍÑ·×'", $yyyymm);
}
if (getUniResult($query, $c_nonope_loss_sum) < 1) {
    $c_nonope_loss_sum = 0;                     // ¸¡º÷¼ºÇÔ
    $c_nonope_loss_sum_temp = 0;
} else {
    $c_nonope_loss_sum_temp = $c_nonope_loss_sum;
    $c_nonope_loss_sum = number_format(($c_nonope_loss_sum / $tani), $keta);
}
if ($yyyymm >= 201001) {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='¥ê¥Ë¥¢±Ä¶È³°ÈñÍÑ·×ºÆ·×»»'", $yyyymm);
} else {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='¥ê¥Ë¥¢±Ä¶È³°ÈñÍÑ·×'", $yyyymm);
}
if (getUniResult($query, $l_nonope_loss_sum) < 1) {
    $l_nonope_loss_sum = 0 - $s_nonope_loss_sum_sagaku;     // ¸¡º÷¼ºÇÔ
    $lh_nonope_loss_sum = 0;     // ¸¡º÷¼ºÇÔ
    $lh_nonope_loss_sum_sagaku = 0;     // ¸¡º÷¼ºÇÔ
} else {
    if ($yyyymm >= 201001) {
        $l_nonope_loss_sum = $l_nonope_loss_sum + $s_nonope_loss_sum_sagaku;
    }
    $lh_nonope_loss_sum = $l_nonope_loss_sum - $s_nonope_loss_sum_sagaku - $b_nonope_loss_sum_sagaku;
    $lh_nonope_loss_sum_sagaku = $lh_nonope_loss_sum;
    $l_nonope_loss_sum         = $l_nonope_loss_sum - $s_nonope_loss_sum_sagaku;     // »î¸³½¤Íý±Ä¶È³°ÈñÍÑ·×¤ò¥ê¥Ë¥¢¤Î±Ä¶È³°ÈñÍÑ·×¤è¤ê¥Þ¥¤¥Ê¥¹
    $lh_nonope_loss_sum = number_format(($lh_nonope_loss_sum / $tani), $keta);
    $l_nonope_loss_sum = number_format(($l_nonope_loss_sum / $tani), $keta);
}
    ///// Á°·î
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='Á´ÂÎ±Ä¶È³°ÈñÍÑ·×'", $p1_ym);
if (getUniResult($query, $p1_all_nonope_loss_sum) < 1) {
    $p1_all_nonope_loss_sum = 0;                // ¸¡º÷¼ºÇÔ
} else {
    $p1_all_nonope_loss_sum = number_format(($p1_all_nonope_loss_sum / $tani), $keta);
}
if ($p1_ym >= 201001) {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='¥«¥×¥é±Ä¶È³°ÈñÍÑ·×ºÆ·×»»'", $p1_ym);
} else {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='¥«¥×¥é±Ä¶È³°ÈñÍÑ·×'", $p1_ym);
}
if (getUniResult($query, $p1_c_nonope_loss_sum) < 1) {
    $p1_c_nonope_loss_sum = 0;                     // ¸¡º÷¼ºÇÔ
    $p1_c_nonope_loss_sum_temp = 0;
} else {
    $p1_c_nonope_loss_sum_temp = $p1_c_nonope_loss_sum;
    $p1_c_nonope_loss_sum = number_format(($p1_c_nonope_loss_sum / $tani), $keta);
}
if ($p1_ym >= 201001) {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='¥ê¥Ë¥¢±Ä¶È³°ÈñÍÑ·×ºÆ·×»»'", $p1_ym);
} else {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='¥ê¥Ë¥¢±Ä¶È³°ÈñÍÑ·×'", $p1_ym);
}
if (getUniResult($query, $p1_l_nonope_loss_sum) < 1) {
    $p1_l_nonope_loss_sum = 0 - $p1_s_nonope_loss_sum_sagaku;     // ¸¡º÷¼ºÇÔ
    $p1_lh_nonope_loss_sum = 0;     // ¸¡º÷¼ºÇÔ
    $p1_lh_nonope_loss_sum_sagaku = 0;     // ¸¡º÷¼ºÇÔ
} else {
    if ($p1_ym >= 201001) {
        $p1_l_nonope_loss_sum = $p1_l_nonope_loss_sum + $p1_s_nonope_loss_sum_sagaku;
    }
    $p1_lh_nonope_loss_sum = $p1_l_nonope_loss_sum - $p1_s_nonope_loss_sum_sagaku - $p1_b_nonope_loss_sum_sagaku;
    $p1_lh_nonope_loss_sum_sagaku = $p1_lh_nonope_loss_sum;
    $p1_l_nonope_loss_sum         = $p1_l_nonope_loss_sum - $p1_s_nonope_loss_sum_sagaku;     // »î¸³½¤Íý±Ä¶È³°ÈñÍÑ·×¤ò¥ê¥Ë¥¢¤Î±Ä¶È³°ÈñÍÑ·×¤è¤ê¥Þ¥¤¥Ê¥¹
    $p1_lh_nonope_loss_sum = number_format(($p1_lh_nonope_loss_sum / $tani), $keta);
    $p1_l_nonope_loss_sum = number_format(($p1_l_nonope_loss_sum / $tani), $keta);
}
    ///// Á°Á°·î
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='Á´ÂÎ±Ä¶È³°ÈñÍÑ·×'", $p2_ym);
if (getUniResult($query, $p2_all_nonope_loss_sum) < 1) {
    $p2_all_nonope_loss_sum = 0;                // ¸¡º÷¼ºÇÔ
} else {
    $p2_all_nonope_loss_sum = number_format(($p2_all_nonope_loss_sum / $tani), $keta);
}
if ($p2_ym >= 201001) {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='¥«¥×¥é±Ä¶È³°ÈñÍÑ·×ºÆ·×»»'", $p2_ym);
} else {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='¥«¥×¥é±Ä¶È³°ÈñÍÑ·×'", $p2_ym);
}
if (getUniResult($query, $p2_c_nonope_loss_sum) < 1) {
    $p2_c_nonope_loss_sum = 0;                     // ¸¡º÷¼ºÇÔ
    $p2_c_nonope_loss_sum_temp = 0;
} else {
    $p2_c_nonope_loss_sum_temp = $p2_c_nonope_loss_sum;
    $p2_c_nonope_loss_sum = number_format(($p2_c_nonope_loss_sum / $tani), $keta);
}
if ($p2_ym >= 201001) {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='¥ê¥Ë¥¢±Ä¶È³°ÈñÍÑ·×ºÆ·×»»'", $p2_ym);
} else {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='¥ê¥Ë¥¢±Ä¶È³°ÈñÍÑ·×'", $p2_ym);
}
if (getUniResult($query, $p2_l_nonope_loss_sum) < 1) {
    $p2_l_nonope_loss_sum = 0 - $p2_s_nonope_loss_sum_sagaku;     // ¸¡º÷¼ºÇÔ
    $p2_lh_nonope_loss_sum = 0;     // ¸¡º÷¼ºÇÔ
    $p2_lh_nonope_loss_sum_sagaku = 0;     // ¸¡º÷¼ºÇÔ
} else {
    if ($p2_ym >= 201001) {
        $p2_l_nonope_loss_sum = $p2_l_nonope_loss_sum + $p2_s_nonope_loss_sum_sagaku;
    }
    $p2_lh_nonope_loss_sum = $p2_l_nonope_loss_sum - $p2_s_nonope_loss_sum_sagaku - $p2_b_nonope_loss_sum_sagaku;
    $p2_lh_nonope_loss_sum_sagaku = $p2_lh_nonope_loss_sum;
    $p2_l_nonope_loss_sum         = $p2_l_nonope_loss_sum - $p2_s_nonope_loss_sum_sagaku;     // »î¸³½¤Íý±Ä¶È³°ÈñÍÑ·×¤ò¥ê¥Ë¥¢¤Î±Ä¶È³°ÈñÍÑ·×¤è¤ê¥Þ¥¤¥Ê¥¹
    $p2_lh_nonope_loss_sum = number_format(($p2_lh_nonope_loss_sum / $tani), $keta);
    $p2_l_nonope_loss_sum = number_format(($p2_l_nonope_loss_sum / $tani), $keta);
}
    ///// º£´üÎß·×
$query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='Á´ÂÎ±Ä¶È³°ÈñÍÑ·×'", $str_ym, $yyyymm);
if (getUniResult($query, $rui_all_nonope_loss_sum) < 1) {
    $rui_all_nonope_loss_sum = 0;               // ¸¡º÷¼ºÇÔ
} else {
    $rui_all_nonope_loss_sum = number_format(($rui_all_nonope_loss_sum / $tani), $keta);
}
if ($yyyymm >= 201004) {
    $query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='¥«¥×¥é±Ä¶È³°ÈñÍÑ·×ºÆ·×»»'", $str_ym, $yyyymm);
    if (getUniResult($query, $rui_c_nonope_loss_sum) < 1) {
        $rui_c_nonope_loss_sum = 0;                           // ¸¡º÷¼ºÇÔ
    } else {
        $rui_c_nonope_loss_sum = number_format(($rui_c_nonope_loss_sum / $tani), $keta);
    }
} elseif ($yyyymm >= 201001 && $yyyymm <= 201003) {
    $query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=200904 and pl_bs_ym<=200912 and note='¥«¥×¥é±Ä¶È³°ÈñÍÑ·×'");
    if (getUniResult($query, $rui_c_nonope_loss_sum_a) < 1) {
        $rui_c_nonope_loss_sum_a = 0;                          // ¸¡º÷¼ºÇÔ
    }
    $query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=201001 and pl_bs_ym<=%d and note='¥«¥×¥é±Ä¶È³°ÈñÍÑ·×ºÆ·×»»'", $yyyymm);
    if (getUniResult($query, $rui_c_nonope_loss_sum_b) < 1) {
        $rui_c_nonope_loss_sum_b = 0;                          // ¸¡º÷¼ºÇÔ
    }
    $rui_c_nonope_loss_sum = $rui_c_nonope_loss_sum_a + $rui_c_nonope_loss_sum_b;
    $rui_c_nonope_loss_sum = number_format(($rui_c_nonope_loss_sum / $tani), $keta);
} else {
    $query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='¥«¥×¥é±Ä¶È³°ÈñÍÑ·×'", $str_ym, $yyyymm);
    if (getUniResult($query, $rui_c_nonope_loss_sum) < 1) {
        $rui_c_nonope_loss_sum = 0;                           // ¸¡º÷¼ºÇÔ
    } else {
        $rui_c_nonope_loss_sum = number_format(($rui_c_nonope_loss_sum / $tani), $keta);
    }
}
if ($yyyymm >= 201004) {
    $query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='¥ê¥Ë¥¢±Ä¶È³°ÈñÍÑ·×ºÆ·×»»'", $str_ym, $yyyymm);
    if (getUniResult($query, $rui_l_nonope_loss_sum) < 1) {
        $rui_l_nonope_loss_sum = 0 - $rui_s_nonope_loss_sum_sagaku;   // ¸¡º÷¼ºÇÔ
        $rui_lh_nonope_loss_sum = 0;     // ¸¡º÷¼ºÇÔ
        $rui_lh_nonope_loss_sum_sagaku = 0;     // ¸¡º÷¼ºÇÔ
    } else {
        $rui_l_nonope_loss_sum = $rui_l_nonope_loss_sum + $rui_s_nonope_loss_sum_sagaku;
        $rui_lh_nonope_loss_sum = $rui_l_nonope_loss_sum - $rui_s_nonope_loss_sum_sagaku - $rui_b_nonope_loss_sum_sagaku;
        $rui_lh_nonope_loss_sum_sagaku = $rui_lh_nonope_loss_sum;
        $rui_l_nonope_loss_sum         = $rui_l_nonope_loss_sum - $rui_s_nonope_loss_sum_sagaku;     // »î¸³½¤Íý±Ä¶È³°ÈñÍÑ·×¤ò¥ê¥Ë¥¢¤Î±Ä¶È³°ÈñÍÑ·×¤è¤ê¥Þ¥¤¥Ê¥¹
        $rui_l_nonope_loss_sum_temp = $rui_l_nonope_loss_sum;         // ·Ð¾ïÍø±×·×»»ÍÑ
        $rui_lh_nonope_loss_sum = number_format(($rui_lh_nonope_loss_sum / $tani), $keta);
        $rui_l_nonope_loss_sum      = number_format(($rui_l_nonope_loss_sum / $tani), $keta);
    }
} elseif ($yyyymm >= 201001 && $yyyymm <= 201003) {
    $query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=200904 and pl_bs_ym<=200912 and note='¥ê¥Ë¥¢±Ä¶È³°ÈñÍÑ·×'");
    if (getUniResult($query, $rui_l_nonope_loss_sum_a) < 1) {
        $rui_l_nonope_loss_sum_a = 0;                          // ¸¡º÷¼ºÇÔ
    }
    $query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=201001 and pl_bs_ym<=%d and note='¥ê¥Ë¥¢±Ä¶È³°ÈñÍÑ·×ºÆ·×»»'", $yyyymm);
    if (getUniResult($query, $rui_l_nonope_loss_sum_b) < 1) {
        $rui_l_nonope_loss_sum_b = 0;                          // ¸¡º÷¼ºÇÔ
    }
    $rui_l_nonope_loss_sum      = $rui_l_nonope_loss_sum_a + $rui_l_nonope_loss_sum_b;
    $rui_l_nonope_loss_sum = $rui_l_nonope_loss_sum + $rui_s_nonope_loss_sum_sagaku;
    $rui_l_nonope_loss_sum      = $rui_l_nonope_loss_sum - $rui_s_srisoku_a - $rui_s_lother_a;
    $rui_lh_nonope_loss_sum = $rui_l_nonope_loss_sum - $rui_s_nonope_loss_sum_sagaku - $rui_b_nonope_loss_sum_sagaku;
    $rui_lh_nonope_loss_sum_sagaku = $rui_lh_nonope_loss_sum;
    $rui_l_nonope_loss_sum         = $rui_l_nonope_loss_sum - $rui_s_nonope_loss_sum_sagaku;     // »î¸³½¤Íý±Ä¶È³°ÈñÍÑ·×¤ò¥ê¥Ë¥¢¤Î±Ä¶È³°ÈñÍÑ·×¤è¤ê¥Þ¥¤¥Ê¥¹
    $rui_l_nonope_loss_sum_temp = $rui_l_nonope_loss_sum;         // ·Ð¾ïÍø±×·×»»ÍÑ
    $rui_lh_nonope_loss_sum = number_format(($rui_lh_nonope_loss_sum / $tani), $keta);
    $rui_l_nonope_loss_sum      = number_format(($rui_l_nonope_loss_sum / $tani), $keta);
} else {
    $query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='¥ê¥Ë¥¢±Ä¶È³°ÈñÍÑ·×'", $str_ym, $yyyymm);
    if (getUniResult($query, $rui_l_nonope_loss_sum) < 1) {
        $rui_l_nonope_loss_sum = 0 - $rui_s_nonope_loss_sum_sagaku;   // ¸¡º÷¼ºÇÔ
        $rui_lh_nonope_loss_sum = 0;     // ¸¡º÷¼ºÇÔ
        $rui_lh_nonope_loss_sum_sagaku = 0;     // ¸¡º÷¼ºÇÔ
    } else {
        //$rui_l_nonope_loss_sum = $rui_l_nonope_loss_sum + $rui_s_nonope_loss_sum_sagaku;
        $rui_lh_nonope_loss_sum = $rui_l_nonope_loss_sum - $rui_s_nonope_loss_sum_sagaku - $rui_b_nonope_loss_sum_sagaku;
        $rui_lh_nonope_loss_sum_sagaku = $rui_lh_nonope_loss_sum;
        $rui_l_nonope_loss_sum         = $rui_l_nonope_loss_sum - $rui_s_nonope_loss_sum_sagaku;     // »î¸³½¤Íý±Ä¶È³°ÈñÍÑ·×¤ò¥ê¥Ë¥¢¤Î±Ä¶È³°ÈñÍÑ·×¤è¤ê¥Þ¥¤¥Ê¥¹
        $rui_lh_nonope_loss_sum = number_format(($rui_lh_nonope_loss_sum / $tani), $keta);
        $rui_l_nonope_loss_sum = number_format(($rui_l_nonope_loss_sum / $tani), $keta);
    }
}

/********** ·Ð¾ïÍø±× **********/
    ///// ¾¦´É
$p2_n_current_profit     = $p2_n_ope_profit + $p2_n_nonope_profit_sum - $p2_n_nonope_loss_sum;
$p2_n_ope_profit         = number_format(($p2_n_ope_profit / $tani), $keta);
$p2_n_nonope_profit_sum  = number_format(($p2_n_nonope_profit_sum / $tani), $keta);
$p2_n_nonope_loss_sum    = number_format(($p2_n_nonope_loss_sum / $tani), $keta);
$p2_n_current_profit     = number_format(($p2_n_current_profit / $tani), $keta);

$p1_n_current_profit     = $p1_n_ope_profit + $p1_n_nonope_profit_sum - $p1_n_nonope_loss_sum;
$p1_n_ope_profit         = number_format(($p1_n_ope_profit / $tani), $keta);
$p1_n_nonope_profit_sum  = number_format(($p1_n_nonope_profit_sum / $tani), $keta);
$p1_n_nonope_loss_sum    = number_format(($p1_n_nonope_loss_sum / $tani), $keta);
$p1_n_current_profit     = number_format(($p1_n_current_profit / $tani), $keta);

$n_current_profit        = $n_ope_profit + $n_nonope_profit_sum - $n_nonope_loss_sum;
$n_ope_profit            = number_format(($n_ope_profit / $tani), $keta);
$n_nonope_profit_sum     = number_format(($n_nonope_profit_sum / $tani), $keta);
$n_nonope_loss_sum       = number_format(($n_nonope_loss_sum / $tani), $keta);
$n_current_profit        = number_format(($n_current_profit / $tani), $keta);

$rui_n_current_profit    = $rui_n_ope_profit + $rui_n_nonope_profit_sum - $rui_n_nonope_loss_sum;
$rui_n_ope_profit        = number_format(($rui_n_ope_profit / $tani), $keta);
$rui_n_nonope_profit_sum = number_format(($rui_n_nonope_profit_sum / $tani), $keta);
$rui_n_nonope_loss_sum   = number_format(($rui_n_nonope_loss_sum / $tani), $keta);
$rui_n_current_profit    = number_format(($rui_n_current_profit / $tani), $keta);
    ///// »î¸³¡¦½¤Íý
$p2_s_current_profit         = $p2_s_ope_profit_sagaku + $p2_s_nonope_profit_sum_sagaku - $p2_s_nonope_loss_sum_sagaku;
$p2_s_current_profit_sagaku  = $p2_s_current_profit;
$p2_s_current_profit         = $p2_s_current_profit + $p2_sc_uri_sagaku - $p2_sc_metarial_sagaku;      // ¥«¥×¥é»î¸³½¤Íý¤ò²ÃÌ£¡Êsagaku¤Î¸å¡Ý¥ê¥Ë¥¢¤«¤é¥Þ¥¤¥Ê¥¹¤·¤Æ¤·¤Þ¤¦°Ù¡Ë
if ($p2_ym == 200912) {
    $p2_s_current_profit = $p2_s_current_profit + 1409708;
}
if ($p2_ym >= 201001) {
    $p2_s_current_profit = $p2_s_current_profit + $p2_s_kyu_kei - $p2_s_kyu_kin;  // ÅºÅÄ¤µ¤ó¤ÎµëÍ¿¤òC¡¦L¤Ï35%¡£»î¸³½¤Íý¤Ë30%¿¶Ê¬
    //$p2_s_current_profit = $p2_s_current_profit + 432323 - 129697;  // ÅºÅÄ¤µ¤ó¤ÎµëÍ¿¤òC¡¦L¤Ï35%¡£»î¸³½¤Íý¤Ë30%¿¶Ê¬
}
$p2_s_current_profit         = number_format(($p2_s_current_profit / $tani), $keta);

$p1_s_current_profit         = $p1_s_ope_profit_sagaku + $p1_s_nonope_profit_sum_sagaku - $p1_s_nonope_loss_sum_sagaku;
$p1_s_current_profit_sagaku  = $p1_s_current_profit;
$p1_s_current_profit         = $p1_s_current_profit + $p1_sc_uri_sagaku - $p1_sc_metarial_sagaku;      // ¥«¥×¥é»î¸³½¤Íý¤ò²ÃÌ£¡Êsagaku¤Î¸å¡Ý¥ê¥Ë¥¢¤«¤é¥Þ¥¤¥Ê¥¹¤·¤Æ¤·¤Þ¤¦°Ù¡Ë
if ($p1_ym == 200912) {
    $p1_s_current_profit = $p1_s_current_profit + 1409708;
}
if ($p1_ym >= 201001) {
    $p1_s_current_profit = $p1_s_current_profit + $p1_s_kyu_kei - $p1_s_kyu_kin;  // ÅºÅÄ¤µ¤ó¤ÎµëÍ¿¤òC¡¦L¤Ï35%¡£»î¸³½¤Íý¤Ë30%¿¶Ê¬
    //$p1_s_current_profit = $p1_s_current_profit + 432323 - 129697;  // ÅºÅÄ¤µ¤ó¤ÎµëÍ¿¤òC¡¦L¤Ï35%¡£»î¸³½¤Íý¤Ë30%¿¶Ê¬
}
$p1_s_current_profit         = number_format(($p1_s_current_profit / $tani), $keta);

$s_current_profit            = $s_ope_profit_sagaku + $s_nonope_profit_sum_sagaku - $s_nonope_loss_sum_sagaku;
$s_current_profit_sagaku     = $s_current_profit;
$s_current_profit            = $s_current_profit + $sc_uri_sagaku - $sc_metarial_sagaku;      // ¥«¥×¥é»î¸³½¤Íý¤ò²ÃÌ£¡Êsagaku¤Î¸å¡Ý¥ê¥Ë¥¢¤«¤é¥Þ¥¤¥Ê¥¹¤·¤Æ¤·¤Þ¤¦°Ù¡Ë
if ($yyyymm == 200912) {
    $s_current_profit = $s_current_profit + 1409708;
}
if ($yyyymm >= 201001) {
    $s_current_profit = $s_current_profit + $s_kyu_kei - $s_kyu_kin;  // ÅºÅÄ¤µ¤ó¤ÎµëÍ¿¤òC¡¦L¤Ï35%¡£»î¸³½¤Íý¤Ë30%¿¶Ê¬
    //$s_current_profit = $s_current_profit + 432323 - 129697;  // ÅºÅÄ¤µ¤ó¤ÎµëÍ¿¤òC¡¦L¤Ï35%¡£»î¸³½¤Íý¤Ë30%¿¶Ê¬
}
$s_current_profit            = number_format(($s_current_profit / $tani), $keta);

$rui_s_current_profit        = $rui_s_ope_profit_sagaku + $rui_s_nonope_profit_sum_sagaku - $rui_s_nonope_loss_sum_sagaku;
$rui_s_current_profit_sagaku = $rui_s_current_profit;
$rui_s_current_profit        = $rui_s_current_profit + $rui_sc_uri_sagaku - $rui_sc_metarial_sagaku;      // ¥«¥×¥é»î¸³½¤Íý¤ò²ÃÌ£¡Êsagaku¤Î¸å¡Ý¥ê¥Ë¥¢¤«¤é¥Þ¥¤¥Ê¥¹¤·¤Æ¤·¤Þ¤¦°Ù¡Ë
if ($yyyymm >= 200912 && $yyyymm <= 201003) {
    $rui_s_current_profit = $rui_s_current_profit + 1409708;
}
if ($yyyymm >= 201001) {
    $rui_s_current_profit = $rui_s_current_profit + $rui_s_kyu_kei - $rui_s_kyu_kin;  // ÅºÅÄ¤µ¤ó¤ÎµëÍ¿¤òC¡¦L¤Ï35%¡£»î¸³½¤Íý¤Ë30%¿¶Ê¬
    //$rui_s_current_profit = $rui_s_current_profit + 432323 - 129697;  // ÅºÅÄ¤µ¤ó¤ÎµëÍ¿¤òC¡¦L¤Ï35%¡£»î¸³½¤Íý¤Ë30%¿¶Ê¬
}
$rui_s_current_profit        = number_format(($rui_s_current_profit / $tani), $keta);
    ///// µ¡¹©
$p2_b_current_profit         = $p2_b_ope_profit_sagaku + $p2_b_nonope_profit_sum_sagaku - $p2_b_nonope_loss_sum_sagaku;
$p2_b_current_profit_sagaku  = $p2_b_current_profit;
$p2_b_current_profit         = number_format(($p2_b_current_profit / $tani), $keta);

$p1_b_current_profit         = $p1_b_ope_profit_sagaku + $p1_b_nonope_profit_sum_sagaku - $p1_b_nonope_loss_sum_sagaku;
$p1_b_current_profit_sagaku  = $p1_b_current_profit;
$p1_b_current_profit         = number_format(($p1_b_current_profit / $tani), $keta);

$b_current_profit            = $b_ope_profit_sagaku + $b_nonope_profit_sum_sagaku - $b_nonope_loss_sum_sagaku;
$b_current_profit_sagaku     = $b_current_profit;
$b_current_profit            = number_format(($b_current_profit / $tani), $keta);

$rui_b_current_profit        = $rui_b_ope_profit_sagaku + $rui_b_nonope_profit_sum_sagaku - $rui_b_nonope_loss_sum_sagaku;
$rui_b_current_profit_sagaku = $rui_b_current_profit;
$rui_b_current_profit        = number_format(($rui_b_current_profit / $tani), $keta);

    ///// Åö·î
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='Á´ÂÎ·Ð¾ïÍø±×'", $yyyymm);
if (getUniResult($query, $all_current_profit) < 1) {
    $all_current_profit   = 0;                // ¸¡º÷¼ºÇÔ
    $all_current_profit_t = 0;                // ¸¡º÷¼ºÇÔ
} else {
    if ($yyyymm == 201002) {
        $all_current_profit = $all_current_profit + 600000;
    }
    if ($yyyymm == 201003) {
        $all_current_profit = $all_current_profit - 600000;
    }
    // 2012/02/08 ÄÉ²Ã 2012Ç¯1·îÅÙ ¶ÈÌ³°ÑÂ÷Èñ¡ÊÊ¿½Ð²£ÀîÇÉ¸¯ÎÁ¡ËÄ´À°
    if ($yyyymm == 201201) {
        $all_current_profit -=1156130;
    }
    // 2012/03/05 ÄÉ²Ã 2012Ç¯1·îÅÙ ¶ÈÌ³°ÑÂ÷Èñ¡ÊÊ¿½Ð²£ÀîÇÉ¸¯ÎÁ¡ËÄ´À° Ìá¤·
    if ($yyyymm == 201202) {
        $all_current_profit +=1156130;
    }
    $all_current_profit   = $all_current_profit + $n_uri_sagaku;
    $all_current_profit_t = $all_current_profit;
    $all_current_profit   = number_format(($all_current_profit / $tani), $keta);
}
if ($yyyymm >= 201001) {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='¥«¥×¥é·Ð¾ïÍø±×ºÆ·×»»'", $yyyymm);
} else {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='¥«¥×¥é·Ð¾ïÍø±×'", $yyyymm);
}
if (getUniResult($query, $c_current_profit) < 1) {
    $c_current_profit = 0;                  // ¸¡º÷¼ºÇÔ
} else {
    if ($yyyymm < 201001) {
        $c_current_profit = $c_current_profit + $n_sagaku + $c_allo_kin - $sc_uri_sagaku + $sc_metarial_sagaku; // ¥«¥×¥é»î¸³½¤Íý¤ò²ÃÌ£
    } else {
        $c_current_profit = $c_ope_profit_temp + $c_nonope_profit_sum_temp - $c_nonope_loss_sum_temp;
    }
    if ($yyyymm == 200912) {
        $c_current_profit = $c_current_profit - 1227429;
    }
    if ($yyyymm >= 201001) {
        //$c_current_profit = $c_current_profit - $c_kyu_kin;
        //$c_current_profit = $c_current_profit - 151313;
    }
    $c_current_profit = number_format(($c_current_profit / $tani), $keta);
}
if ($yyyymm >= 201001) {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='¥ê¥Ë¥¢·Ð¾ïÍø±×ºÆ·×»»'", $yyyymm);
} else {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='¥ê¥Ë¥¢·Ð¾ïÍø±×'", $yyyymm);
}
if (getUniResult($query, $l_current_profit) < 1) {
    $l_current_profit  = 0;       // ¸¡º÷¼ºÇÔ
    $lh_current_profit = 0;       // ¸¡º÷¼ºÇÔ
} else {
    if ($yyyymm < 201001) {
        $l_current_profit = $l_current_profit - $s_current_profit_sagaku + $l_allo_kin;
    } else {
        $l_current_profit = $l_current_profit - $s_ope_profit_sagaku + $l_allo_kin;
    }
    if ($yyyymm == 200912) {
        $l_current_profit = $l_current_profit - 182279;
    }
    if ($yyyymm >= 201001) {
        $l_current_profit = $l_current_profit - $l_kyu_kin; // ÅºÅÄ¤µ¤ó¤ÎµëÍ¿¤òC¡¦L¤Ï35%¡£»î¸³½¤Íý¤Ë30%¿¶Ê¬
        //$l_current_profit = $l_current_profit - 151313; // ÅºÅÄ¤µ¤ó¤ÎµëÍ¿¤òC¡¦L¤Ï35%¡£»î¸³½¤Íý¤Ë30%¿¶Ê¬
    }
    if ($yyyymm == 201004) {
        $l_current_profit = $l_current_profit - 255240;
    }
    // 2012/02/08 ÄÉ²Ã 2012Ç¯1·îÅÙ ¶ÈÌ³°ÑÂ÷Èñ¡ÊÊ¿½Ð²£ÀîÇÉ¸¯ÎÁ¡ËÄ´À°
    if ($yyyymm == 201201) {
        $l_current_profit -=1156130;
    }
    // 2012/03/05 ÄÉ²Ã 2012Ç¯1·îÅÙ ¶ÈÌ³°ÑÂ÷Èñ¡ÊÊ¿½Ð²£ÀîÇÉ¸¯ÎÁ¡ËÄ´À° Ìá¤·
    if ($yyyymm == 201202) {
        $l_current_profit +=1156130;
    }
    if ($yyyymm == 201408) {
        $l_current_profit -=229464;
    }
    $l_current_profit = $l_current_profit + $s_current_profit_sagaku;
    $lh_current_profit = $l_current_profit - $s_current_profit_sagaku - $b_current_profit_sagaku;
    $lh_current_profit_sagaku = $lh_current_profit;
    $l_current_profit = $l_current_profit - $s_current_profit_sagaku;
    $lh_current_profit = number_format(($lh_current_profit / $tani), $keta);
    $l_current_profit = number_format(($l_current_profit / $tani), $keta);
}
    ///// Á°·î
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='Á´ÂÎ·Ð¾ïÍø±×'", $p1_ym);
if (getUniResult($query, $p1_all_current_profit) < 1) {
    $p1_all_current_profit = 0;             // ¸¡º÷¼ºÇÔ
} else {
    if ($p1_ym == 201002) {
        $p1_all_current_profit = $p1_all_current_profit + 600000;
    }
    if ($p1_ym == 201003) {
        $p1_all_current_profit = $p1_all_current_profit - 600000;
    }
    // 2012/02/08 ÄÉ²Ã 2012Ç¯1·îÅÙ ¶ÈÌ³°ÑÂ÷Èñ¡ÊÊ¿½Ð²£ÀîÇÉ¸¯ÎÁ¡ËÄ´À°
    if ($p1_ym == 201201) {
        $p1_all_current_profit -=1156130;
    }
    // 2012/03/05 ÄÉ²Ã 2012Ç¯1·îÅÙ ¶ÈÌ³°ÑÂ÷Èñ¡ÊÊ¿½Ð²£ÀîÇÉ¸¯ÎÁ¡ËÄ´À° Ìá¤·
    if ($p1_ym == 201202) {
        $p1_all_current_profit +=1156130;
    }
    $p1_all_current_profit = $p1_all_current_profit + $p1_n_uri_sagaku;
    $p1_all_current_profit = number_format(($p1_all_current_profit / $tani), $keta);
}
if ($p1_ym >= 201001) {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='¥«¥×¥é·Ð¾ïÍø±×ºÆ·×»»'", $p1_ym);
} else {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='¥«¥×¥é·Ð¾ïÍø±×'", $p1_ym);
}
if (getUniResult($query, $p1_c_current_profit) < 1) {
    $p1_c_current_profit = 0;                  // ¸¡º÷¼ºÇÔ
} else {
    if ($p1_ym < 201001) {
        $p1_c_current_profit = $p1_c_current_profit + $p1_n_sagaku + $p1_c_allo_kin - $p1_sc_uri_sagaku + $p1_sc_metarial_sagaku; // ¥«¥×¥é»î¸³½¤Íý¤ò²ÃÌ£
    } else {
        $p1_c_current_profit = $p1_c_ope_profit_temp + $p1_c_nonope_profit_sum_temp - $p1_c_nonope_loss_sum_temp;
    }
    if ($p1_ym == 200912) {
        $p1_c_current_profit = $p1_c_current_profit - 1227429;
    }
    if ($p1_ym >= 201001) {
        //$p1_c_current_profit = $p1_c_current_profit - $p1_c_kyu_kin;   // ÅºÅÄ¤µ¤ó¤ÎµëÍ¿¤òC¡¦L¤Ï35%¡£»î¸³½¤Íý¤Ë30%¿¶Ê¬
        //$p1_c_current_profit = $p1_c_current_profit - 151313;   // ÅºÅÄ¤µ¤ó¤ÎµëÍ¿¤òC¡¦L¤Ï35%¡£»î¸³½¤Íý¤Ë30%¿¶Ê¬
    }
    $p1_c_current_profit = number_format(($p1_c_current_profit / $tani), $keta);
}
if ($p1_ym >= 201001) {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='¥ê¥Ë¥¢·Ð¾ïÍø±×ºÆ·×»»'", $p1_ym);
} else {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='¥ê¥Ë¥¢·Ð¾ïÍø±×'", $p1_ym);
}
if (getUniResult($query, $p1_l_current_profit) < 1) {
    $p1_l_current_profit  = 0;       // ¸¡º÷¼ºÇÔ
    $p1_lh_current_profit = 0;       // ¸¡º÷¼ºÇÔ
} else {
    if ($p1_ym < 201001) {
        $p1_l_current_profit = $p1_l_current_profit - $p1_s_current_profit_sagaku + $p1_l_allo_kin;
    } else {
        $p1_l_current_profit = $p1_l_current_profit - $p1_s_ope_profit_sagaku + $p1_l_allo_kin;
    }
    if ($p1_ym == 200912) {
        $p1_l_current_profit = $p1_l_current_profit - 182279;
    }
    if ($p1_ym >= 201001) {
        $p1_l_current_profit = $p1_l_current_profit - $p1_l_kyu_kin;   // ÅºÅÄ¤µ¤ó¤ÎµëÍ¿¤òC¡¦L¤Ï35%¡£»î¸³½¤Íý¤Ë30%¿¶Ê¬
        //$p1_c_current_profit = $p1_c_current_profit - 151313;   // ÅºÅÄ¤µ¤ó¤ÎµëÍ¿¤òC¡¦L¤Ï35%¡£»î¸³½¤Íý¤Ë30%¿¶Ê¬
    }
    if ($p1_ym == 201004) {
        $p1_l_current_profit = $p1_l_current_profit - 255240;
    }
    // 2012/02/08 ÄÉ²Ã 2012Ç¯1·îÅÙ ¶ÈÌ³°ÑÂ÷Èñ¡ÊÊ¿½Ð²£ÀîÇÉ¸¯ÎÁ¡ËÄ´À°
    if ($p1_ym == 201201) {
        $p1_l_current_profit -=1156130;
    }
    // 2012/03/05 ÄÉ²Ã 2012Ç¯1·îÅÙ ¶ÈÌ³°ÑÂ÷Èñ¡ÊÊ¿½Ð²£ÀîÇÉ¸¯ÎÁ¡ËÄ´À° Ìá¤·
    if ($p1_ym == 201202) {
        $p1_l_current_profit +=1156130;
    }
    if ($p1_ym == 201408) {
        $p1_l_current_profit -=229464;
    }
    $p1_l_current_profit = $p1_l_current_profit + $p1_s_current_profit_sagaku;
    $p1_lh_current_profit = $p1_l_current_profit - $p1_s_current_profit_sagaku - $p1_b_current_profit_sagaku;
    $p1_lh_current_profit_sagaku = $p1_lh_current_profit;
    $p1_l_current_profit = $p1_l_current_profit - $p1_s_current_profit_sagaku;
    $p1_lh_current_profit = number_format(($p1_lh_current_profit / $tani), $keta);
    $p1_l_current_profit = number_format(($p1_l_current_profit / $tani), $keta);
}
    ///// Á°Á°·î
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='Á´ÂÎ·Ð¾ïÍø±×'", $p2_ym);
if (getUniResult($query, $p2_all_current_profit) < 1) {
    $p2_all_current_profit = 0;             // ¸¡º÷¼ºÇÔ
} else {
    if ($p2_ym == 201002) {
        $p2_all_current_profit = $p2_all_current_profit + 600000;
    }
    if ($p2_ym == 201003) {
        $p2_all_current_profit = $p2_all_current_profit - 600000;
    }
    // 2012/02/08 ÄÉ²Ã 2012Ç¯1·îÅÙ ¶ÈÌ³°ÑÂ÷Èñ¡ÊÊ¿½Ð²£ÀîÇÉ¸¯ÎÁ¡ËÄ´À°
    if ($p2_ym == 201201) {
        $p2_all_current_profit -=1156130;
    }
    // 2012/03/05 ÄÉ²Ã 2012Ç¯1·îÅÙ ¶ÈÌ³°ÑÂ÷Èñ¡ÊÊ¿½Ð²£ÀîÇÉ¸¯ÎÁ¡ËÄ´À° Ìá¤·
    if ($p2_ym == 201202) {
        $p2_all_current_profit +=1156130;
    }
    $p2_all_current_profit = $p2_all_current_profit + $p2_n_uri_sagaku;
    $p2_all_current_profit = number_format(($p2_all_current_profit / $tani), $keta);
}
if ($p2_ym >= 201001) {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='¥«¥×¥é·Ð¾ïÍø±×ºÆ·×»»'", $p2_ym);
} else {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='¥«¥×¥é·Ð¾ïÍø±×'", $p2_ym);
}
if (getUniResult($query, $p2_c_current_profit) < 1) {
    $p2_c_current_profit = 0;                  // ¸¡º÷¼ºÇÔ
} else {
    if ($p2_ym < 201001) {
        $p2_c_current_profit = $p2_c_current_profit + $p2_n_sagaku + $p2_c_allo_kin - $p2_sc_uri_sagaku + $p2_sc_metarial_sagaku; // ¥«¥×¥é»î¸³½¤Íý¤ò²ÃÌ£
    } else {
        $p2_c_current_profit = $p2_c_ope_profit_temp + $p2_c_nonope_profit_sum_temp - $p2_c_nonope_loss_sum_temp;
    }
    if ($p2_ym == 200912) {
        $p2_c_current_profit = $p2_c_current_profit - 1227429;
    }
    if ($p2_ym >= 201001) {
        //$p2_c_current_profit = $p2_c_current_profit - $p2_c_kyu_kin;   // ÅºÅÄ¤µ¤ó¤ÎµëÍ¿¤òC¡¦L¤Ï35%¡£»î¸³½¤Íý¤Ë30%¿¶Ê¬
        //$p2_c_current_profit = $p2_c_current_profit - 151313;   // ÅºÅÄ¤µ¤ó¤ÎµëÍ¿¤òC¡¦L¤Ï35%¡£»î¸³½¤Íý¤Ë30%¿¶Ê¬
    }
    $p2_c_current_profit = number_format(($p2_c_current_profit / $tani), $keta);
}
if ($p2_ym >= 201001) {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='¥ê¥Ë¥¢·Ð¾ïÍø±×ºÆ·×»»'", $p2_ym);
} else {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='¥ê¥Ë¥¢·Ð¾ïÍø±×'", $p2_ym);
}
if (getUniResult($query, $p2_l_current_profit) < 1) {
    $p2_l_current_profit  = 0;       // ¸¡º÷¼ºÇÔ
    $p2_lh_current_profit = 0;       // ¸¡º÷¼ºÇÔ
} else {
    if ($p2_ym < 201001) {
        $p2_l_current_profit = $p2_l_current_profit - $p2_s_current_profit_sagaku + $p2_l_allo_kin;
    } else {
        $p2_l_current_profit = $p2_l_current_profit - $p2_s_ope_profit_sagaku + $p2_l_allo_kin;
    }
    if ($p2_ym == 200912) {
        $p2_l_current_profit = $p2_l_current_profit - 182279;
    }
    if ($p2_ym >= 201001) {
        $p2_l_current_profit = $p2_l_current_profit - $p2_l_kyu_kin;
        //$p2_l_current_profit = $p2_l_current_profit - 151313;
    }
    if ($p2_ym == 201004) {
        $p2_l_current_profit = $p2_l_current_profit - 255240;
    }
    // 2012/02/08 ÄÉ²Ã 2012Ç¯1·îÅÙ ¶ÈÌ³°ÑÂ÷Èñ¡ÊÊ¿½Ð²£ÀîÇÉ¸¯ÎÁ¡ËÄ´À°
    if ($p2_ym == 201201) {
        $p2_l_current_profit -=1156130;
    }
    // 2012/03/05 ÄÉ²Ã 2012Ç¯1·îÅÙ ¶ÈÌ³°ÑÂ÷Èñ¡ÊÊ¿½Ð²£ÀîÇÉ¸¯ÎÁ¡ËÄ´À° Ìá¤·
    if ($p2_ym == 201202) {
        $p2_l_current_profit +=1156130;
    }
    if ($p2_ym == 201408) {
        $p2_l_current_profit -=229464;
    }
    $p2_l_current_profit = $p2_l_current_profit + $p2_s_current_profit_sagaku;
    $p2_lh_current_profit = $p2_l_current_profit - $p2_s_current_profit_sagaku - $p2_b_current_profit_sagaku;
    $p2_lh_current_profit_sagaku = $p2_lh_current_profit;
    $p2_l_current_profit = $p2_l_current_profit - $p2_s_current_profit_sagaku;
    $p2_lh_current_profit = number_format(($p2_lh_current_profit / $tani), $keta);
    $p2_l_current_profit = number_format(($p2_l_current_profit / $tani), $keta);
}
    ///// º£´üÎß·×
$query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='Á´ÂÎ·Ð¾ïÍø±×'", $str_ym, $yyyymm);
if (getUniResult($query, $rui_all_current_profit) < 1) {
    $rui_all_current_profit = 0;            // ¸¡º÷¼ºÇÔ
} else {
    if ($yyyymm >= 201002 && $yyyymm <= 201003) {
        $rui_all_current_profit = $rui_all_current_profit + 600000;
    }
    if ($yyyymm == 201003) {
        $rui_all_current_profit = $rui_all_current_profit - 600000;
    }
    // 2012/02/08 ÄÉ²Ã 2012Ç¯1·îÅÙ ¶ÈÌ³°ÑÂ÷Èñ¡ÊÊ¿½Ð²£ÀîÇÉ¸¯ÎÁ¡ËÄ´À°
    if ($yyyymm >= 201201 && $yyyymm <= 201203) {
        $rui_all_current_profit -=1156130;
    }
    // 2012/03/05 ÄÉ²Ã 2012Ç¯1·îÅÙ ¶ÈÌ³°ÑÂ÷Èñ¡ÊÊ¿½Ð²£ÀîÇÉ¸¯ÎÁ¡ËÄ´À° Ìá¤·
    if ($yyyymm >= 201202 && $yyyymm <= 201203) {
        $rui_all_current_profit +=1156130;
    }
    $rui_all_current_profit   = $rui_all_current_profit + $rui_n_uri_sagaku;
    $rui_all_current_profit_t = $rui_all_current_profit;
    $rui_all_current_profit   = number_format(($rui_all_current_profit / $tani), $keta);
}
if ($yyyymm >= 201004) {
    $query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='¥«¥×¥é·Ð¾ïÍø±×ºÆ·×»»'", $str_ym, $yyyymm);
    if (getUniResult($query, $rui_c_current_profit) < 1) {
        $rui_c_current_profit = 0;                           // ¸¡º÷¼ºÇÔ
    } else {
        $rui_c_current_profit = $rui_c_current_profit + $rui_n_sagaku + $rui_c_allo_kin - $rui_sc_uri_sagaku + $rui_sc_metarial_sagaku; // ¥«¥×¥é»î¸³½¤Íý¤ò²ÃÌ£
        if ($yyyymm >= 201001) {
            $rui_c_current_profit = $rui_c_current_profit - $rui_c_kyu_kin; // ÅºÅÄ¤µ¤ó¤ÎµëÍ¿¤òC¡¦L¤Ï35%¡£»î¸³½¤Íý¤Ë30%¿¶Ê¬
            //$rui_c_current_profit = $rui_c_current_profit - 151313; // ÅºÅÄ¤µ¤ó¤ÎµëÍ¿¤òC¡¦L¤Ï35%¡£»î¸³½¤Íý¤Ë30%¿¶Ê¬
        }
        // 2013/11/07 ÄÉ²Ã 2013Ç¯10·îÅÙ ¶ÈÌ³°ÑÂ÷Èñ¡Ê²£ÀîÇÉ¸¯ÎÁ¡ËÄ´À°
        if ($yyyymm >= 201310 && $yyyymm <= 201403) {
            $rui_c_current_profit += 1245035;
        }
        if ($yyyymm >= 201311 && $yyyymm <= 201403) {
            $rui_c_current_profit -= 1245035;
        }
        if ($yyyymm >= 201408 && $yyyymm <= 201503) {
            $rui_c_current_profit += 229464;
        }
        $rui_c_current_profit = number_format(($rui_c_current_profit / $tani), $keta);
    }
} elseif ($yyyymm >= 201001 && $yyyymm <= 201003) {
    $query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=200904 and pl_bs_ym<=200912 and note='¥«¥×¥é·Ð¾ïÍø±×'");
    if (getUniResult($query, $rui_c_current_profit_a) < 1) {
        $rui_c_current_profit_a = 0;                          // ¸¡º÷¼ºÇÔ
    }
    $query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=201001 and pl_bs_ym<=%d and note='¥«¥×¥é·Ð¾ïÍø±×ºÆ·×»»'", $yyyymm);
    if (getUniResult($query, $rui_c_current_profit_b) < 1) {
        $rui_c_current_profit_b = 0;                          // ¸¡º÷¼ºÇÔ
    }
    $rui_c_current_profit = $rui_c_current_profit_a + $rui_c_current_profit_b;
    if ($yyyymm >= 200912 && $yyyymm <= 201003) {
        $rui_c_current_profit = $rui_c_current_profit - 1227429;
    }
    if ($yyyymm >= 201001) {
        $rui_c_current_profit = $rui_c_current_profit - $rui_c_kyu_kin; // ÅºÅÄ¤µ¤ó¤ÎµëÍ¿¤òC¡¦L¤Ï35%¡£»î¸³½¤Íý¤Ë30%¿¶Ê¬
        //$rui_c_current_profit = $rui_c_current_profit - 151313; // ÅºÅÄ¤µ¤ó¤ÎµëÍ¿¤òC¡¦L¤Ï35%¡£»î¸³½¤Íý¤Ë30%¿¶Ê¬
    }
    $rui_c_current_profit = $rui_c_current_profit + $rui_n_sagaku + $rui_c_allo_kin - $rui_sc_uri_sagaku + $rui_sc_metarial_sagaku  - $rui_n_pother_a; // ¥«¥×¥é»î¸³½¤Íý¤ò²ÃÌ£
    $rui_c_current_profit = number_format(($rui_c_current_profit / $tani), $keta);
} else {
    $query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='¥«¥×¥é·Ð¾ïÍø±×'", $str_ym, $yyyymm);
    if (getUniResult($query, $rui_c_current_profit) < 1) {
        $rui_c_current_profit = 0;                           // ¸¡º÷¼ºÇÔ
    } else {
        $rui_c_current_profit = $rui_c_current_profit + $rui_n_sagaku + $rui_c_allo_kin - $rui_sc_uri_sagaku + $rui_sc_metarial_sagaku; // ¥«¥×¥é»î¸³½¤Íý¤ò²ÃÌ£
        if ($yyyymm >= 200912 && $yyyymm <= 201003) {
            $rui_c_current_profit = $rui_c_current_profit - 1227429;
        }
        $rui_c_current_profit = number_format(($rui_c_current_profit / $tani), $keta);
    }
}
if ($yyyymm >= 201004) {
    $query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='¥ê¥Ë¥¢·Ð¾ïÍø±×ºÆ·×»»'", $str_ym, $yyyymm);
    if (getUniResult($query, $rui_l_current_profit) < 1) {
        $rui_l_current_profit = 0 - $rui_s_current_profit_sagaku;   // ¸¡º÷¼ºÇÔ
        $rui_lh_current_profit = 0;     // ¸¡º÷¼ºÇÔ
        $rui_lh_current_profit_sagaku = 0;     // ¸¡º÷¼ºÇÔ
    } else {
        //$rui_l_current_profit = $rui_l_current_profit - $rui_s_current_profit_sagaku + $rui_l_allo_kin;
        // 2012/02/08 ÄÉ²Ã 2012Ç¯1·îÅÙ ¶ÈÌ³°ÑÂ÷Èñ¡ÊÊ¿½Ð²£ÀîÇÉ¸¯ÎÁ¡ËÄ´À°
        if ($yyyymm >= 201201 && $yyyymm <= 201203) {
            $rui_l_current_profit -=1156130;
        }
        // 2012/03/05 ÄÉ²Ã 2012Ç¯1·îÅÙ ¶ÈÌ³°ÑÂ÷Èñ¡ÊÊ¿½Ð²£ÀîÇÉ¸¯ÎÁ¡ËÄ´À° Ìá¤·
        if ($yyyymm >= 201202 && $yyyymm <= 201203) {
            $rui_l_current_profit +=1156130;
        }
        $rui_l_current_profit = $rui_l_ope_profit_temp + $rui_l_nonope_profit_sum_temp - $rui_l_nonope_loss_sum_temp;
        $rui_lh_current_profit = $rui_l_current_profit - $rui_b_current_profit_sagaku;
        $rui_lh_current_profit_sagaku = $rui_lh_current_profit;
        $rui_lh_current_profit = number_format(($rui_lh_current_profit / $tani), $keta);
        $rui_l_current_profit = number_format(($rui_l_current_profit / $tani), $keta);
    }
} elseif ($yyyymm >= 201001 && $yyyymm <= 201003) {
    //$query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=200904 and pl_bs_ym<=200911 and note='¥ê¥Ë¥¢·Ð¾ïÍø±×'");
    //if (getUniResult($query, $rui_l_current_profit_a) < 1) {
    //    $rui_l_current_profit_a = 0;                          // ¸¡º÷¼ºÇÔ
    //}
    //$query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=200912 and pl_bs_ym<=%d and note='¥ê¥Ë¥¢·Ð¾ïÍø±×ºÆ·×»»'", $yyyymm);
    //if (getUniResult($query, $rui_l_current_profit_b) < 1) {
    //    $rui_l_current_profit_b = 0;                          // ¸¡º÷¼ºÇÔ
    //}
    //$rui_l_current_profit = $rui_l_current_profit_a + $rui_l_current_profit_b;
    //if ($yyyymm >= 200912 && $yyyymm <= 201003) {
    //    $rui_l_current_profit = $rui_l_current_profit - 182279;
    //}
    //if ($yyyymm >= 201001 && $yyyymm <= 201003) {
    //    $rui_l_current_profit = $rui_l_current_profit - 151313;
    //}
    //$rui_l_current_profit = $rui_l_current_profit - $rui_s_current_profit_sagaku + $rui_l_allo_kin;
    //$rui_l_current_profit = $rui_l_current_profit - $rui_s_ope_profit_sagaku + $rui_l_allo_kin;
    
    $rui_l_current_profit = $rui_l_ope_profit_temp + $rui_l_nonope_profit_sum_temp - $rui_l_nonope_loss_sum_temp;
    $rui_lh_current_profit = $rui_l_current_profit - $rui_b_current_profit_sagaku;
    $rui_lh_current_profit_sagaku = $rui_lh_current_profit;
    $rui_lh_current_profit = number_format(($rui_lh_current_profit / $tani), $keta);
    $rui_l_current_profit = number_format(($rui_l_current_profit / $tani), $keta);
} else {
    $query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='¥ê¥Ë¥¢·Ð¾ïÍø±×'", $str_ym, $yyyymm);
    if (getUniResult($query, $rui_l_current_profit) < 1) {
        $rui_l_current_profit = 0 - $rui_s_current_profit_sagaku;   // ¸¡º÷¼ºÇÔ
        $rui_lh_current_profit = 0;     // ¸¡º÷¼ºÇÔ
        $rui_lh_current_profit_sagaku = 0;     // ¸¡º÷¼ºÇÔ
    } else {
        $rui_l_current_profit = $rui_l_current_profit  + $rui_l_allo_kin;
        if ($yyyymm >= 200912 && $yyyymm <= 201003) {
            $rui_l_current_profit = $rui_l_current_profit - 182279;
        }
        $rui_lh_current_profit = $rui_l_current_profit - $rui_s_current_profit_sagaku - $rui_b_current_profit_sagaku;
        $rui_lh_current_profit_sagaku = $rui_lh_current_profit;
        //$rui_l_current_profit = $rui_l_current_profit + $rui_sc_uri_sagaku - $rui_sc_metarial_sagaku;     // ¥«¥×¥é»î½¤Çä¾å¹â¤ò²ÃÌ£¡Ê¹ç·×ÍóÍÑ¡Ë
        $rui_l_current_profit         = $rui_l_current_profit - $rui_s_current_profit_sagaku;     // »î¸³½¤Íý·Ð¾ïÍø±×¤ò¥ê¥Ë¥¢¤Î·Ð¾ïÍø±×¤è¤ê¥Þ¥¤¥Ê¥¹
        $rui_lh_current_profit = number_format(($rui_lh_current_profit / $tani), $keta);
        $rui_l_current_profit = number_format(($rui_l_current_profit / $tani), $keta);
    }
}

/********** ÆÃÊÌÍø±× **********/
    ///// Åö·î
$query = sprintf("select kin1 from pl_bs_summary where t_id='C' and t_row=1 and pl_bs_ym=%d", $yyyymm);
if (getUniResult($query, $all_special_profit) < 1) {
    $all_special_profit   = 0;            // ¸¡º÷¼ºÇÔ
    $all_special_profit_t = 0;            // ¸¡º÷¼ºÇÔ
} else {
    $all_special_profit_t = $all_special_profit;
    $all_special_profit = number_format(($all_special_profit / $tani), $keta);
}
    ///// º£´üÎß·×
$query = sprintf("select sum(kin1) from pl_bs_summary where t_id='C' and t_row=1 and pl_bs_ym>=%d and pl_bs_ym<=%d", $str_ym, $yyyymm);
if (getUniResult($query, $rui_all_special_profit) < 1) {
    $rui_all_special_profit   = 0;            // ¸¡º÷¼ºÇÔ
    $rui_all_special_profit_t = 0;            // ¸¡º÷¼ºÇÔ
} else {
    $rui_all_special_profit_t = $rui_all_special_profit;
    $rui_all_special_profit   = number_format(($rui_all_special_profit / $tani), $keta);
}

/********** ÆÃÊÌÂ»¼º **********/
    ///// Åö·î
$query = sprintf("select kin1 from pl_bs_summary where t_id='C' and t_row=2 and pl_bs_ym=%d", $yyyymm);
if (getUniResult($query, $all_special_loss) < 1) {
    $all_special_loss   = 0;            // ¸¡º÷¼ºÇÔ
    $all_special_loss_t = 0;            // ¸¡º÷¼ºÇÔ
} else {
    $all_special_loss_t = $all_special_loss;
    $all_special_loss   = number_format(($all_special_loss / $tani), $keta);
}
    ///// º£´üÎß·×
$query = sprintf("select sum(kin1) from pl_bs_summary where t_id='C' and t_row=2 and pl_bs_ym>=%d and pl_bs_ym<=%d", $str_ym, $yyyymm);
if (getUniResult($query, $rui_all_special_loss) < 1) {
    $rui_all_special_loss   = 0;            // ¸¡º÷¼ºÇÔ
    $rui_all_special_loss_t = 0;            // ¸¡º÷¼ºÇÔ
} else {
    $rui_all_special_loss_t = $rui_all_special_loss;
    $rui_all_special_loss   = number_format(($rui_all_special_loss / $tani), $keta);
}

/********** ÀÇ°úÁ°½ãÍø±×¶â³Û **********/
    ///// Åö·î
$all_before_tax_net_profit_t = $all_current_profit_t + $all_special_profit_t - $all_special_loss_t;
$all_before_tax_net_profit   = number_format(($all_before_tax_net_profit_t / $tani), $keta);
    ///// º£´üÎß·×
$rui_all_before_tax_net_profit_t = $rui_all_current_profit_t + $rui_all_special_profit_t - $rui_all_special_loss_t;
$rui_all_before_tax_net_profit   = number_format(($rui_all_before_tax_net_profit_t / $tani), $keta);
/********** Ë¡¿ÍÀÇÅù **********/
    ///// Åö·î
$query = sprintf("SELECT SUM(kin1) FROM pl_bs_summary WHERE t_id='C' AND t_row>=3 AND pl_bs_ym=%d", $yyyymm);
if (getUniResult($query, $all_corporation_tax) < 1) {
    $all_corporation_tax   = 0;            // ¸¡º÷¼ºÇÔ
    $all_corporation_tax_t = 0;            // ¸¡º÷¼ºÇÔ
} else {
    $all_corporation_tax_t = $all_corporation_tax;
    $all_corporation_tax   = number_format(($all_corporation_tax / $tani), $keta);
}
    ///// º£´üÎß·×
$query = sprintf("select sum(kin1) from pl_bs_summary where t_id='C' and t_row>=3 and pl_bs_ym>=%d and pl_bs_ym<=%d", $str_ym, $yyyymm);
if (getUniResult($query, $rui_all_corporation_tax) < 1) {
    $rui_all_corporation_tax   = 0;            // ¸¡º÷¼ºÇÔ
    $rui_all_corporation_tax_t = 0;            // ¸¡º÷¼ºÇÔ
} else {
    $rui_all_corporation_tax_t = $rui_all_corporation_tax;
    $rui_all_corporation_tax   = number_format(($rui_all_corporation_tax / $tani), $keta);
}

/********** Åö´ü½ãÍø±×¶â³Û **********/
    ///// Åö·î
$all_net_profit_t = $all_before_tax_net_profit_t - $all_corporation_tax_t;
$all_net_profit   = number_format(($all_net_profit_t / $tani), $keta);
    ///// º£´üÎß·×
$rui_all_net_profit_t = $rui_all_before_tax_net_profit_t - $rui_all_corporation_tax_t;
$rui_all_net_profit   = number_format(($rui_all_net_profit_t / $tani), $keta);

////////// ÆÃµ­»ö¹à¤Î¼èÆÀ
$query = sprintf("select comment from act_comment_history where pl_bs_ym=%d and item='¥«¥×¥éÂ»±×·×»»½ñ'", $yyyymm);
if (getUniResult($query,$comment_c) <= 0) {
    $comment_c = "";
}
$query = sprintf("select comment from act_comment_history where pl_bs_ym=%d and item='¥ê¥Ë¥¢Â»±×·×»»½ñ'", $yyyymm);
if (getUniResult($query,$comment_l) <= 0) {
    $comment_l = "";
}
$query = sprintf("select comment from act_comment_history where pl_bs_ym=%d and item='¥Ä¡¼¥ëÂ»±×·×»»½ñ'", $yyyymm);
if (getUniResult($query,$comment_t) <= 0) {
    $comment_t = "";
}
$query = sprintf("select comment from act_comment_history where pl_bs_ym=%d and item='»î¸³¡¦½¤ÍýÂ»±×·×»»½ñ'", $yyyymm);
if (getUniResult($query,$comment_s) <= 0) {
    $comment_s = "";
}

$query = sprintf("select comment from act_comment_history where pl_bs_ym=%d and item='¾¦ÉÊ´ÉÍýÂ»±×·×»»½ñ'", $yyyymm);
if (getUniResult($query,$comment_b) <= 0) {
    $comment_b = "";
}
$query = sprintf("select comment from act_comment_history where pl_bs_ym=%d and item='Á´ÂÎÂ»±×·×»»½ñ'", $yyyymm);
if (getUniResult($query,$comment_all) <= 0) {
    $comment_all = "";
}
$query = sprintf("select comment from act_comment_history where pl_bs_ym=%d and item='¤½¤ÎÂ¾Â»±×·×»»½ñ'", $yyyymm);
if (getUniResult($query,$comment_other) <= 0) {
    $comment_other = "";
}
//$test  = "Çä¾å¹â";
//$test2 = "¥«¥×¥é";
//$test  = $test2 . $test;
if (isset($_POST['input_data'])) {                        // Åö·î¥Ç¡¼¥¿¤ÎÅÐÏ¿
    ///////// ¹àÌÜ¤È¥¤¥ó¥Ç¥Ã¥¯¥¹¤Î´ØÏ¢ÉÕ¤±
    $item = array();
    $item[0]   = "Çä¾å¹â";
    $item[1]   = "´ü¼óºàÎÁ»Å³ÝÉÊÃª²·¹â";
    $item[2]   = "ºàÎÁÈñ(»ÅÆþ¹â)";
    $item[3]   = "Ï«Ì³Èñ";
    $item[4]   = "À½Â¤·ÐÈñ";
    $item[5]   = "´üËöºàÎÁ»Å³ÝÉÊÃª²·¹â";
    $item[6]   = "Çä¾å¸¶²Á";
    $item[7]   = "Çä¾åÁíÍø±×";
    $item[8]   = "¿Í·ïÈñ";
    $item[9]   = "·ÐÈñ";
    $item[10]  = "ÈÎ´ÉÈñµÚ¤Ó°ìÈÌ´ÉÍýÈñ·×";
    $item[11]  = "±Ä¶ÈÍø±×";
    $item[12]  = "¶ÈÌ³°ÑÂ÷¼ýÆþ";
    $item[13]  = "»ÅÆþ³ä°ú";
    $item[14]  = "±Ä¶È³°¼ý±×¤½¤ÎÂ¾";
    $item[15]  = "±Ä¶È³°¼ý±×·×";
    $item[16]  = "»ÙÊ§ÍøÂ©";
    $item[17]  = "±Ä¶È³°ÈñÍÑ¤½¤ÎÂ¾";
    $item[18]  = "±Ä¶È³°ÈñÍÑ·×";
    $item[19]  = "·Ð¾ïÍø±×";
    // 2012/01/13 ÄÉ²Ã
    $item[20]  = "ÆÃÊÌÍø±×";
    $item[21]  = "ÆÃÊÌÂ»¼º";
    $item[22]  = "ÀÇ°úÁ°½ãÍø±×¶â³Û";
    $item[23]  = "Ë¡¿ÍÀÇÅù";
    $item[24]  = "Åö´ü½ãÍø±×¶â³Û";
    ///////// ³Æ¥Ç¡¼¥¿¤ÎÊÝ´É ¥«¥×¥é=0 ¥ê¥Ë¥¢=1 ¥Ä¡¼¥ë=2 »î½¤=3 ¾¦´É=4 Á´ÂÎ=5
    $input_data = array();
    for ($i = 0; $i < 25; $i++) {
        switch ($i) {
                case  0:                                            // Çä¾å¹â
                    $input_data[$i][0] = $c_uri;                    // ¥«¥×¥é
                    $input_data[$i][1] = $lh_uri;                   // ¥ê¥Ë¥¢
                    $input_data[$i][2] = $b_uri;                    // µ¡¹©
                    $input_data[$i][3] = $s_uri;                    // »î½¤
                    $input_data[$i][4] = $n_uri;                    // ¾¦´É
                    $input_data[$i][5] = $all_uri;                  // Á´ÂÎ
                break;
                case  1:                                            // ´ü¼óºàÎÁ»Å³ÝÉÊÃª²·¹â
                    $input_data[$i][0] = $c_invent;                 // ¥«¥×¥é
                    $input_data[$i][1] = $lh_invent;                // ¥ê¥Ë¥¢
                    $input_data[$i][2] = $b_invent;                 // µ¡¹©
                    $input_data[$i][3] = $s_invent;                 // »î½¤
                    $input_data[$i][4] = $n_invent;                 // ¾¦´É
                    $input_data[$i][5] = $all_invent;               // Á´ÂÎ
                break;
                case  2:                                            // ºàÎÁÈñ(»ÅÆþ¹â)
                    $input_data[$i][0] = $c_metarial;               // ¥«¥×¥é
                    $input_data[$i][1] = $lh_metarial;              // ¥ê¥Ë¥¢
                    $input_data[$i][2] = $b_metarial;               // µ¡¹©
                    $input_data[$i][3] = $s_metarial;               // »î½¤
                    $input_data[$i][4] = $n_metarial;               // ¾¦´É
                    $input_data[$i][5] = $all_metarial;             // Á´ÂÎ
                break;
                case  3:                                            // Ï«Ì³Èñ
                    $input_data[$i][0] = $c_roumu;                  // ¥«¥×¥é
                    $input_data[$i][1] = $lh_roumu;                 // ¥ê¥Ë¥¢
                    $input_data[$i][2] = $b_roumu;                  // µ¡¹©
                    $input_data[$i][3] = $s_roumu;                  // »î½¤
                    $input_data[$i][4] = $n_roumu;                  // ¾¦´É
                    $input_data[$i][5] = $all_roumu;                // Á´ÂÎ
                break;
                case  4:                                            // À½Â¤·ÐÈñ
                    $input_data[$i][0] = $c_expense;                // ¥«¥×¥é
                    $input_data[$i][1] = $lh_expense;               // ¥ê¥Ë¥¢
                    $input_data[$i][2] = $b_expense;                // µ¡¹©
                    $input_data[$i][3] = $s_expense;                // »î½¤
                    $input_data[$i][4] = $n_expense;                // ¾¦´É
                    $input_data[$i][5] = $all_expense;              // Á´ÂÎ
                break;
                case  5:                                            // ´üËöºàÎÁ»Å³ÝÉÊÃª²·¹â
                    $input_data[$i][0] = $c_endinv;                 // ¥«¥×¥é
                    $input_data[$i][1] = $lh_endinv;                // ¥ê¥Ë¥¢
                    $input_data[$i][2] = $b_endinv;                 // µ¡¹©
                    $input_data[$i][3] = $s_endinv;                 // »î½¤
                    $input_data[$i][4] = $n_endinv;                 // ¾¦´É
                    $input_data[$i][5] = $all_endinv;               // Á´ÂÎ
                break;
                case  6:                                            // Çä¾å¸¶²Á
                    $input_data[$i][0] = $c_urigen;                 // ¥«¥×¥é
                    $input_data[$i][1] = $lh_urigen;                // ¥ê¥Ë¥¢
                    $input_data[$i][2] = $b_urigen;                 // µ¡¹©
                    $input_data[$i][3] = $s_urigen;                 // »î½¤
                    $input_data[$i][4] = $n_urigen;                 // ¾¦´É
                    $input_data[$i][5] = $all_urigen;               // Á´ÂÎ
                break;
                case  7:                                            // Çä¾åÁíÍø±×
                    $input_data[$i][0] = $c_gross_profit;           // ¥«¥×¥é
                    $input_data[$i][1] = $lh_gross_profit;          // ¥ê¥Ë¥¢
                    $input_data[$i][2] = $b_gross_profit;           // µ¡¹©
                    $input_data[$i][3] = $s_gross_profit;           // »î½¤
                    $input_data[$i][4] = $n_gross_profit;           // ¾¦´É
                    $input_data[$i][5] = $all_gross_profit;         // Á´ÂÎ
                break;
                case  8:                                            // ¿Í·ïÈñ
                    $input_data[$i][0] = $c_han_jin;                // ¥«¥×¥é
                    $input_data[$i][1] = $lh_han_jin;               // ¥ê¥Ë¥¢
                    $input_data[$i][2] = $b_han_jin;                // µ¡¹©
                    $input_data[$i][3] = $s_han_jin;                // »î½¤
                    $input_data[$i][4] = $n_han_jin;                // ¾¦´É
                    $input_data[$i][5] = $all_han_jin;              // Á´ÂÎ
                break;
                case  9:                                            // ·ÐÈñ
                    $input_data[$i][0] = $c_han_kei;                // ¥«¥×¥é
                    $input_data[$i][1] = $lh_han_kei;               // ¥ê¥Ë¥¢
                    $input_data[$i][2] = $b_han_kei;                // µ¡¹©
                    $input_data[$i][3] = $s_han_kei;                // »î½¤
                    $input_data[$i][4] = $n_han_kei;                // ¾¦´É
                    $input_data[$i][5] = $all_han_kei;              // Á´ÂÎ
                break;
                case 10:                                            // ÈÎ´ÉÈñµÚ¤Ó°ìÈÌ´ÉÍýÈñ·×
                    $input_data[$i][0] = $c_han_all;                // ¥«¥×¥é
                    $input_data[$i][1] = $lh_han_all;               // ¥ê¥Ë¥¢
                    $input_data[$i][2] = $b_han_all;                // µ¡¹©
                    $input_data[$i][3] = $s_han_all;                // »î½¤
                    $input_data[$i][4] = $n_han_all;                // ¾¦´É
                    $input_data[$i][5] = $all_han_all;              // Á´ÂÎ
                break;
                case 11:                                            // ±Ä¶ÈÍø±×
                    $input_data[$i][0] = $c_ope_profit;             // ¥«¥×¥é
                    $input_data[$i][1] = $lh_ope_profit;            // ¥ê¥Ë¥¢
                    $input_data[$i][2] = $b_ope_profit;             // µ¡¹©
                    $input_data[$i][3] = $s_ope_profit;             // »î½¤
                    $input_data[$i][4] = $n_ope_profit;             // ¾¦´É
                    $input_data[$i][5] = $all_ope_profit;           // Á´ÂÎ
                break;
                case 12:                                            // ¶ÈÌ³°ÑÂ÷¼ýÆþ
                    $input_data[$i][0] = $c_gyoumu;                 // ¥«¥×¥é
                    $input_data[$i][1] = $lh_gyoumu;                // ¥ê¥Ë¥¢
                    $input_data[$i][2] = $b_gyoumu;                 // µ¡¹©
                    $input_data[$i][3] = $s_gyoumu;                 // »î½¤
                    $input_data[$i][4] = $n_gyoumu;                 // ¾¦´É
                    $input_data[$i][5] = $all_gyoumu;               // Á´ÂÎ
                break;
                case 13:                                            // »ÅÆþ³ä°ú
                    $input_data[$i][0] = $c_swari;                  // ¥«¥×¥é
                    $input_data[$i][1] = $lh_swari;                 // ¥ê¥Ë¥¢
                    $input_data[$i][2] = $b_swari;                  // µ¡¹©
                    $input_data[$i][3] = $s_swari;                  // »î½¤
                    $input_data[$i][4] = $n_swari;                  // ¾¦´É
                    $input_data[$i][5] = $all_swari;                // Á´ÂÎ
                break;
                case 14:                                            // ±Ä¶È³°¼ý±×¤½¤ÎÂ¾
                    $input_data[$i][0] = $c_pother;                 // ¥«¥×¥é
                    $input_data[$i][1] = $lh_pother;                // ¥ê¥Ë¥¢
                    $input_data[$i][2] = $b_pother;                 // µ¡¹©
                    $input_data[$i][3] = $s_pother;                 // »î½¤
                    $input_data[$i][4] = $n_pother;                 // ¾¦´É
                    $input_data[$i][5] = $all_pother;               // Á´ÂÎ
                break;
                case 15:                                            // ±Ä¶È³°¼ý±×·×
                    $input_data[$i][0] = $c_nonope_profit_sum;      // ¥«¥×¥é
                    $input_data[$i][1] = $lh_nonope_profit_sum;     // ¥ê¥Ë¥¢
                    $input_data[$i][2] = $b_nonope_profit_sum;      // µ¡¹©
                    $input_data[$i][3] = $s_nonope_profit_sum;      // »î½¤
                    $input_data[$i][4] = $n_nonope_profit_sum;      // ¾¦´É
                    $input_data[$i][5] = $all_nonope_profit_sum;    // Á´ÂÎ
                break;
                case 16:                                            // »ÙÊ§ÍøÂ©
                    $input_data[$i][0] = $c_srisoku;                // ¥«¥×¥é
                    $input_data[$i][1] = $lh_srisoku;               // ¥ê¥Ë¥¢
                    $input_data[$i][2] = $b_srisoku;                // µ¡¹©
                    $input_data[$i][3] = $s_srisoku;                // »î½¤
                    $input_data[$i][4] = $n_srisoku;                // ¾¦´É
                    $input_data[$i][5] = $all_srisoku;              // Á´ÂÎ
                break;
                case 17:                                            // ±Ä¶È³°ÈñÍÑ¤½¤ÎÂ¾
                    $input_data[$i][0] = $c_lother;                 // ¥«¥×¥é
                    $input_data[$i][1] = $lh_lother;                // ¥ê¥Ë¥¢
                    $input_data[$i][2] = $b_lother;                 // µ¡¹©
                    $input_data[$i][3] = $s_lother;                 // »î½¤
                    $input_data[$i][4] = $n_lother;                 // ¾¦´É
                    $input_data[$i][5] = $all_lother;               // Á´ÂÎ
                break;
                case 18:                                            // ±Ä¶È³°ÈñÍÑ·×
                    $input_data[$i][0] = $c_nonope_loss_sum;        // ¥«¥×¥é
                    $input_data[$i][1] = $lh_nonope_loss_sum;       // ¥ê¥Ë¥¢
                    $input_data[$i][2] = $b_nonope_loss_sum;        // µ¡¹©
                    $input_data[$i][3] = $s_nonope_loss_sum;        // »î½¤
                    $input_data[$i][4] = $n_nonope_loss_sum;        // ¾¦´É
                    $input_data[$i][5] = $all_nonope_loss_sum;      // Á´ÂÎ
                break;
                case 19:                                            // ·Ð¾ïÍø±×
                    $input_data[$i][0] = $c_current_profit;         // ¥«¥×¥é
                    $input_data[$i][1] = $lh_current_profit;        // ¥ê¥Ë¥¢
                    $input_data[$i][2] = $b_current_profit;         // µ¡¹©
                    $input_data[$i][3] = $s_current_profit;         // »î½¤
                    $input_data[$i][4] = $n_current_profit;         // ¾¦´É
                    $input_data[$i][5] = $all_current_profit;       // Á´ÂÎ
                break;
                // 2012/01/13 ÄÉ²Ã
                case 20:                                            // ÆÃÊÌÍø±×
                    $input_data[$i][0] = 0;                         // ¥«¥×¥é
                    $input_data[$i][1] = 0;                         // ¥ê¥Ë¥¢
                    $input_data[$i][2] = 0;                         // µ¡¹©
                    $input_data[$i][3] = 0;                         // »î½¤
                    $input_data[$i][4] = 0;                         // ¾¦´É
                    $input_data[$i][5] = $all_special_profit;       // Á´ÂÎ
                break;
                case 21:                                            // ÆÃÊÌÂ»¼º
                    $input_data[$i][0] = 0;                         // ¥«¥×¥é
                    $input_data[$i][1] = 0;                         // ¥ê¥Ë¥¢
                    $input_data[$i][2] = 0;                         // µ¡¹©
                    $input_data[$i][3] = 0;                         // »î½¤
                    $input_data[$i][4] = 0;                         // ¾¦´É
                    $input_data[$i][5] = $all_special_loss;         // Á´ÂÎ
                break;
                case 22:                                                    // ÀÇ°úÁ°½ãÍø±×¶â³Û
                    $input_data[$i][0] = 0;                                 // ¥«¥×¥é
                    $input_data[$i][1] = 0;                                 // ¥ê¥Ë¥¢
                    $input_data[$i][2] = 0;                                 // µ¡¹©
                    $input_data[$i][3] = 0;                                 // »î½¤
                    $input_data[$i][4] = 0;                                 // ¾¦´É
                    $input_data[$i][5] = $all_before_tax_net_profit;        // Á´ÂÎ
                break;
                case 23:                                            // Ë¡¿ÍÀÇÅù
                    $input_data[$i][0] = 0;                         // ¥«¥×¥é
                    $input_data[$i][1] = 0;                         // ¥ê¥Ë¥¢
                    $input_data[$i][2] = 0;                         // µ¡¹©
                    $input_data[$i][3] = 0;                         // »î½¤
                    $input_data[$i][4] = 0;                         // ¾¦´É
                    $input_data[$i][5] = $all_corporation_tax;      // Á´ÂÎ
                break;
                case 24:                                            // Åö´ü½ãÍø±×¶â³Û
                    $input_data[$i][0] = 0;                         // ¥«¥×¥é
                    $input_data[$i][1] = 0;                         // ¥ê¥Ë¥¢
                    $input_data[$i][2] = 0;                         // µ¡¹©
                    $input_data[$i][3] = 0;                         // »î½¤
                    $input_data[$i][4] = 0;                         // ¾¦´É
                    $input_data[$i][5] = $all_net_profit;           // Á´ÂÎ
                break;
                default:
                break;
            }
    }
    // ¥«¥×¥éÅÐÏ¿
    $head  = "¥«¥×¥é";
    $sec   = 0;
    insert_date($head,$item,$yyyymm,$input_data,$sec);
    // ¥ê¥Ë¥¢ÅÐÏ¿
    $head  = "¥ê¥Ë¥¢É¸½à";
    $sec   = 1;
    insert_date($head,$item,$yyyymm,$input_data,$sec);
    // µ¡¹©ÅÐÏ¿
    $head  = "µ¡¹©";
    $sec   = 2;
    insert_date($head,$item,$yyyymm,$input_data,$sec);
    // »î¸³½¤ÍýÅÐÏ¿
    $head  = "»î¸³½¤Íý";
    $sec   = 3;
    insert_date($head,$item,$yyyymm,$input_data,$sec);
    // ¾¦ÉÊ´ÉÍýÅÐÏ¿
    $head  = "¾¦ÉÊ´ÉÍý";
    $sec   = 4;
    insert_date($head,$item,$yyyymm,$input_data,$sec);
    // Á´ÂÎÅÐÏ¿
    $head  = "Á´ÂÎ";
    $sec   = 5;
    insert_date($head,$item,$yyyymm,$input_data,$sec);  
}
function insert_date($head,$item,$yyyymm,$input_data,$sec) 
{
    for ($i = 0; $i < 25; $i++) {
        $item_in     = array();
        $item_in[$i] = $head . $item[$i];
        $input_data[$i][$sec] = str_replace(',','',$input_data[$i][$sec]);
        $query = sprintf("select kin from profit_loss_pl_history where pl_bs_ym=%d and note='%s'", $yyyymm, $item_in[$i]);
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
            $query = sprintf("insert into profit_loss_pl_history (pl_bs_ym, kin, note, last_date, last_user) values (%d, %d, '%s', CURRENT_TIMESTAMP, '%s')", $yyyymm, $input_data[$i][$sec], $item_in[$i], $_SESSION['User_ID']);
            if (query_affected_trans($con, $query) <= 0) {
                $_SESSION["s_sysmsg"] .= sprintf("%s¤Î¿·µ¬ÅÐÏ¿¤Ë¼ºÇÔ<br> %d", $item_in[$i], $yyyymm);
                query_affected_trans($con, "rollback");     // transaction rollback
                exit();
            }
            /////////// commit ¥È¥é¥ó¥¶¥¯¥·¥ç¥ó½ªÎ»
            query_affected_trans($con, "commit");
            $_SESSION["s_sysmsg"] = sprintf("<font color='yellow'>%d Â»±×¥Ç¡¼¥¿ ¿·µ¬ ÅÐÏ¿´°Î»</font>",$yyyymm);
        } else {
            /////////// begin ¥È¥é¥ó¥¶¥¯¥·¥ç¥ó³«»Ï
            if ($con = db_connect()) {
                query_affected_trans($con, "begin");
            } else {
                $_SESSION["s_sysmsg"] .= "¥Ç¡¼¥¿¥Ù¡¼¥¹¤ËÀÜÂ³¤Ç¤­¤Þ¤»¤ó";
                exit();
            }
            ////////// UPDATE Start
            $query = sprintf("update profit_loss_pl_history set kin=%d, last_date=CURRENT_TIMESTAMP, last_user='%s' where pl_bs_ym=%d and note='%s'", $input_data[$i][$sec], $_SESSION['User_ID'], $yyyymm, $item_in[$i]);
            if (query_affected_trans($con, $query) <= 0) {
                $_SESSION["s_sysmsg"] .= sprintf("%s¤ÎUPDATE¤Ë¼ºÇÔ<br> %d", $item_in[$i], $yyyymm);
                query_affected_trans($con, "rollback");     // transaction rollback
                exit();
            }
            /////////// commit ¥È¥é¥ó¥¶¥¯¥·¥ç¥ó½ªÎ»
            query_affected_trans($con, "commit");
            $_SESSION["s_sysmsg"] = sprintf("<font color='yellow'>%d Â»±×¥Ç¡¼¥¿ ÊÑ¹¹ ´°Î»</font>",$yyyymm);
        }
    }
    $_SESSION["s_sysmsg"] .= "Åö·î¤Î¥Ç¡¼¥¿¤òÅÐÏ¿¤·¤Þ¤·¤¿¡£";
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
<script type=text/javascript language='JavaScript'>
<!--
/* ÆþÎÏÊ¸»ú¤¬¿ô»ú¤«¤É¤¦¤«¥Á¥§¥Ã¥¯ */
function isDigit(str) {
    var len=str.length;
    var c;
    for (i=0; i<len; i++) {
        c = str.charAt(i);
        if (("0" > c) || (c > "9")) {
            alert("¿ôÃÍ°Ê³°¤ÏÆþÎÏ½ÐÍè¤Þ¤»¤ó¡£");
            return false;
        }
    }
    return true;
}
function isDigitcho(str) {
    var len=str.length;
    var c;
    for (i=0; i<len; i++) {
        c = str.charAt(i);
        if ((i == 0) && (c == "-")) {
            return true;
        }
        if (("0" > c) || (c > "9")) {
            alert("¿ôÃÍ°Ê³°¤ÏÆþÎÏ½ÐÍè¤Þ¤»¤ó¡£");
            return false;
        }
    }
    return true;
}
/* ½é´üÆþÎÏ¥¨¥ì¥á¥ó¥È¤Ø¥Õ¥©¡¼¥«¥¹¤µ¤»¤ë */
function set_focus(){
    document.jin.jin_1.focus();
    document.jin.jin_1.select();
}
function data_input_click(obj) {
    return confirm("Åö·î¤Î¥Ç¡¼¥¿¤òÅÐÏ¿¤·¤Þ¤¹¡£\n´û¤Ë¥Ç¡¼¥¿¤¬¤¢¤ë¾ì¹ç¤Ï¾å½ñ¤­¤µ¤ì¤Þ¤¹¡£");
}
// -->
</script>
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
    font: normal 10pt;
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
ol {
    line-height: normal;
}
pre {
    font-size: 10.0pt;
    font-family: monospace;
}
.OnOff_font {
    font-size:     8.5pt;
    font-family:   monospace;
}
-->
</style>
</head>
<!--  style='overflow-y:hidden;' -->
<body>
    <center>
<?= $menu->out_title_border() ?>
        <table width='100%' border='1' cellspacing='1' cellpadding='1'>
            <tr>
                <td colspan='2' bgcolor='#d6d3ce' align='center' class='corporate_name'>
                    <?=$menu->out_caption(), "\n"?>
                </td>
                <form method='post' action='<?php echo $menu->out_self() ?>'>
                    <td colspan='14' bgcolor='#d6d3ce' align='right' class='pt10'>
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
                        <?php
                        if ($_SESSION['User_ID'] == '300144') {
                            if ($keta == 0 && $tani == 1) {
                        ?>
                            &nbsp;
                            <input class='pt10b' type='submit' name='input_data' value='Åö·î¥Ç¡¼¥¿ÅÐÏ¿' onClick='return data_input_click(this)'>
                        <?php
                            } else {
                        ?>
                            <input class='pt10b' type='submit' name='input_data' value='Åö·î¥Ç¡¼¥¿ÅÐÏ¿' onClick='return data_input_click(this)' disabled>
                        <?php
                            }
                        }
                        ?>
                    </td>
                </form>
            </tr>
        </table>
    <table width='100%' bgcolor='#d6d3ce' align='center' cellspacing="0" cellpadding="3" border='1'>
        <tr>
        <td>
        <table width='100%' bgcolor='#d6d3ce' align='center' cellspacing="0" cellpadding="3" border='1'>
            <TBODY>
                <tr>
                    <td rowspan='2' colspan='3' width='200' align='center' class='pt10b' bgcolor='#ffffc6'>¹à¡¡¡¡¡¡ÌÜ</td>
                    <td colspan='4' align='center' class='pt10b' bgcolor='#ffffc6'>¥«¡¡¥×¡¡¥é</td>
                    <td colspan='4' align='center' class='pt10b' bgcolor='#ffffc6'>¥ê¡¡¥Ë¡¡¥¢</td>
                    <td colspan='4' align='center' class='pt10b' bgcolor='#ffffc6'>¥Ä¡¡¡¼¡¡¥ë</td>
                    <td colspan='4' align='center' class='pt10b' bgcolor='#ffffc6'>»î¸³¡¦½¤Íý</td>
                    <td colspan='4' align='center' class='pt10b' bgcolor='#ffffc6'>¾¦ÉÊ´ÉÍý</td>
                    <td colspan='4' align='center' class='pt10b' bgcolor='#ffffc6'>¹ç¡¡¡¡¡¡·×</td>
                    <td rowspan='2' width='400' align='left' class='pt10b' bgcolor='#ffffc6'>À½Â¤´ÖÀÜ·ÐÈñ¡¦ÈÎ´ÉÈñ¤ÎÇÛÉê´ð½à</td>
                </tr>
                <tr>
                    <td nowrap align='center' class='pt10b' bgcolor='#d6d3ce'><?php echo $p2_ym ?> </td>
                    <td nowrap align='center' class='pt10b' bgcolor='#d6d3ce'><?php echo $p1_ym ?> </td>
                    <td nowrap align='center' class='pt10b' bgcolor='#ffffc6'><?php echo $yyyymm ?></td>
                    <td nowrap align='center' class='pt10b' bgcolor='#ffffc6'>Îß¡¡·×</td>
                    <td nowrap align='center' class='pt10b' bgcolor='#d6d3ce'><?php echo $p2_ym ?> </td>
                    <td nowrap align='center' class='pt10b' bgcolor='#d6d3ce'><?php echo $p1_ym ?> </td>
                    <td nowrap align='center' class='pt10b' bgcolor='#ffffc6'><?php echo $yyyymm ?></td>
                    <td nowrap align='center' class='pt10b' bgcolor='#ffffc6'>Îß¡¡·×</td>
                    <td nowrap align='center' class='pt10b' bgcolor='#d6d3ce'><?php echo $p2_ym ?> </td>
                    <td nowrap align='center' class='pt10b' bgcolor='#d6d3ce'><?php echo $p1_ym ?> </td>
                    <td nowrap align='center' class='pt10b' bgcolor='#ffffc6'><?php echo $yyyymm ?></td>
                    <td nowrap align='center' class='pt10b' bgcolor='#ffffc6'>Îß¡¡·×</td>
                    <td nowrap align='center' class='pt10b' bgcolor='#d6d3ce'><?php echo $p2_ym ?> </td>
                    <td nowrap align='center' class='pt10b' bgcolor='#d6d3ce'><?php echo $p1_ym ?> </td>
                    <td nowrap align='center' class='pt10b' bgcolor='#ffffc6'><?php echo $yyyymm ?></td>
                    <td nowrap align='center' class='pt10b' bgcolor='#ffffc6'>Îß¡¡·×</td>
                    <td nowrap align='center' class='pt10b' bgcolor='#d6d3ce'><?php echo $p2_ym ?> </td>
                    <td nowrap align='center' class='pt10b' bgcolor='#d6d3ce'><?php echo $p1_ym ?> </td>
                    <td nowrap align='center' class='pt10b' bgcolor='#ffffc6'><?php echo $yyyymm ?></td>
                    <td nowrap align='center' class='pt10b' bgcolor='#ffffc6'>Îß¡¡·×</td>
                    <td nowrap align='center' class='pt10b' bgcolor='#d6d3ce'><?php echo $p2_ym ?> </td>
                    <td nowrap align='center' class='pt10b' bgcolor='#d6d3ce'><?php echo $p1_ym ?> </td>
                    <td nowrap align='center' class='pt10b' bgcolor='#ffffc6'><?php echo $yyyymm ?></td>
                    <td nowrap align='center' class='pt10b' bgcolor='#ffffc6'>Îß¡¡·×</td>
                </tr>
                <tr>
                    <td rowspan='11' width='10' align='center' valign='middle' class='pt10b' bgcolor='#ceffce'>±Ä¡¡¶È¡¡Â»¡¡±×</td>
                    <td colspan='2' nowrap align='center' class='pt10b' bgcolor='#ceffce'>Çä¡¡¾å¡¡¹â</td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo $p2_c_uri ?>   </td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo $p1_c_uri ?>   </td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo $c_uri ?>      </td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo $rui_c_uri ?>  </td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo $p2_lh_uri ?>   </td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo $p1_lh_uri ?>   </td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo $lh_uri ?>      </td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo $rui_lh_uri ?>  </td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo $p2_b_uri ?>   </td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo $p1_b_uri ?>   </td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo $b_uri ?>      </td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo $rui_b_uri ?>  </td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo $p2_s_uri ?>   </td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo $p1_s_uri ?>   </td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo $s_uri ?>      </td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo $rui_s_uri ?>  </td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo $p2_n_uri ?>   </td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo $p1_n_uri ?>   </td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo $n_uri ?>      </td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo $rui_n_uri ?>  </td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo $p2_all_uri ?> </td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo $p1_all_uri ?> </td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo $all_uri ?>    </td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo $rui_all_uri ?></td>
                    <td nowrap align='left'  class='pt10' bgcolor='#d6d3ce'>¼ÂºÝÇä¾å¹â</td>
                </tr>
                <tr>
                    <td rowspan='6' width='10' align='center' valign='middle' class='pt10b' bgcolor='#ffffc6'>Çä¾å¸¶²Á</td> <!-- Çä¾å¸¶²Á -->
                    <td nowrap align='left' class='pt10b' bgcolor='white'>¡¡´ü¼óºàÎÁ»Å³ÝÉÊÃª²·¹â</td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $p2_c_invent ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $p1_c_invent ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $c_invent ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $rui_c_invent ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $p2_lh_invent ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $p1_lh_invent ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $lh_invent ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $rui_lh_invent ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $p2_b_invent ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $p1_b_invent ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $b_invent ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $rui_b_invent ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $p2_s_invent ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $p1_s_invent ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $s_invent ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $rui_s_invent ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $p2_n_invent ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $p1_n_invent ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $n_invent ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $rui_n_invent ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $p2_all_invent ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $p1_all_invent ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $all_invent ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $rui_all_invent ?></td>
                    <td nowrap align='left'  class='pt10'>ÁíÊ¿¶ÑÃ±²Á¤Ë¤è¤ëÃª²·¹â</td>
                </tr>
                <tr>
                    <td nowrap align='left' class='pt10b' bgcolor='#e6e6e6'>¡¡ºàÎÁÈñ(»ÅÆþ¹â)</td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo $p2_c_metarial ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo $p1_c_metarial ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo $c_metarial ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo $rui_c_metarial ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo $p2_lh_metarial ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo $p1_lh_metarial ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo $lh_metarial ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo $rui_lh_metarial ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo $p2_b_metarial ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo $p1_b_metarial ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo $b_metarial ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo $rui_b_metarial ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo $p2_s_metarial ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo $p1_s_metarial ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo $s_metarial ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo $rui_s_metarial ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo $p2_n_metarial ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo $p1_n_metarial ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo $n_metarial ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo $rui_n_metarial ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo $p2_all_metarial ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo $p1_all_metarial ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo $all_metarial ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo $rui_all_metarial ?></td>
                    <td nowrap align='left'  class='pt10'>Çã³Ý¹ØÆþ¹âÈæ</td>
                </tr>
                <tr>
                    <td nowrap align='left' class='pt10b' bgcolor='white'>¡¡Ï«¡¡¡¡Ì³¡¡¡¡Èñ</td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $p2_c_roumu ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $p1_c_roumu ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $c_roumu ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $rui_c_roumu ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $p2_lh_roumu ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $p1_lh_roumu ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $lh_roumu ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $rui_lh_roumu ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $p2_b_roumu ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $p1_b_roumu ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $b_roumu ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $rui_b_roumu ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $p2_s_roumu ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $p1_s_roumu ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $s_roumu ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $rui_s_roumu ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $p2_n_roumu ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $p1_n_roumu ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $n_roumu ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $rui_n_roumu ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $p2_all_roumu ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $p1_all_roumu ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $all_roumu ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $rui_all_roumu ?></td>
                    <td nowrap align='left'  class='pt10'>£Ã£Ì¥µ¡¼¥Ó¥¹³ä¹çÈæ</td>
                </tr>
                <tr>
                    <td nowrap align='left' class='pt10b' bgcolor='#e6e6e6'>¡¡·Ð¡¡¡¡¡¡¡¡¡¡Èñ</td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo $p2_c_expense ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo $p1_c_expense ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo $c_expense ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo $rui_c_expense ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo $p2_lh_expense ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo $p1_lh_expense ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo $lh_expense ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo $rui_lh_expense ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo $p2_b_expense ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo $p1_b_expense ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo $b_expense ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo $rui_b_expense ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo $p2_s_expense ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo $p1_s_expense ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo $s_expense ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo $rui_s_expense ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo $p2_n_expense ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo $p1_n_expense ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo $n_expense ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo $rui_n_expense ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo $p2_all_expense ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo $p1_all_expense ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo $all_expense ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo $rui_all_expense ?></td>
                    <td nowrap align='left'  class='pt10'>£Ã£ÌÄ¾ÀÜ·ÐÈñ¹ç·×ÈæÎ¨</td>
                </tr>
                <tr>
                    <td nowrap align='left' class='pt10b' bgcolor='white'>¡¡´üËöºàÎÁ»Å³ÝÉÊÃª²·¹â</td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $p2_c_endinv ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $p1_c_endinv ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $c_endinv ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $c_endinv ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $p2_lh_endinv ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $p1_lh_endinv ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $lh_endinv ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $lh_endinv ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $p2_b_endinv ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $p1_b_endinv ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $b_endinv ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $b_endinv ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $p2_s_endinv ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $p1_s_endinv ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $s_endinv ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $s_endinv ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $p2_n_endinv ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $p1_n_endinv ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $n_endinv ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $n_endinv ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $p2_all_endinv ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $p1_all_endinv ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $all_endinv ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $all_endinv ?></td>
                    <td nowrap align='left'  class='pt10'>ÁíÊ¿¶ÑÃ±²Á¤Ë¤è¤ëÃª²·¹â</td>
                </tr>
                <tr>
                    <td nowrap align='left' class='pt10b' bgcolor='#ffffc6'>¡¡Çä¡¡¾å¡¡¸¶¡¡²Á</td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo $p2_c_urigen ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo $p1_c_urigen ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo $c_urigen ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo $rui_c_urigen ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo $p2_lh_urigen ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo $p1_lh_urigen ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo $lh_urigen ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo $rui_lh_urigen ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo $p2_b_urigen ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo $p1_b_urigen ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo $b_urigen ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo $rui_b_urigen ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo $p2_s_urigen ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo $p1_s_urigen ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo $s_urigen ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo $rui_s_urigen ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo $p2_n_urigen ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo $p1_n_urigen ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo $n_urigen ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo $rui_n_urigen ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo $p2_all_urigen ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo $p1_all_urigen ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo $all_urigen ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo $rui_all_urigen ?></td>
                    <td nowrap align='left'  class='pt10'>£Ã£ÌÄ¾ÀÜ·ÐÈñ¹ç·×ÈæÎ¨</td>
                </tr>
                <tr>
                    <td colspan='2' nowrap align='center' class='pt10b' bgcolor='#ceffce'>Çä¡¡¾å¡¡Áí¡¡Íø¡¡±×</td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo $p2_c_gross_profit ?></td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo $p1_c_gross_profit ?></td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo $c_gross_profit ?></td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo $rui_c_gross_profit ?></td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo $p2_lh_gross_profit ?></td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo $p1_lh_gross_profit ?></td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo $lh_gross_profit ?></td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo $rui_lh_gross_profit ?></td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo $p2_b_gross_profit ?></td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo $p1_b_gross_profit ?></td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo $b_gross_profit ?></td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo $rui_b_gross_profit ?></td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo $p2_s_gross_profit ?></td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo $p1_s_gross_profit ?></td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo $s_gross_profit ?></td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo $rui_s_gross_profit ?></td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo $p2_n_gross_profit ?></td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo $p1_n_gross_profit ?></td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo $n_gross_profit ?></td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo $rui_n_gross_profit ?></td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo $p2_all_gross_profit ?></td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo $p1_all_gross_profit ?></td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo $all_gross_profit ?></td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo $rui_all_gross_profit ?></td>
                    <td nowrap align='left'  class='pt10' bgcolor='#d6d3ce'>¡¡</td>  <!-- Í¾Çò -->
                </tr>
                <tr>
                    <td rowspan='3' width='10' align='center' valign='middle' class='pt10b' bgcolor='#ffffc6'></td> <!-- ÈÎ´ÉÈñ -->
                    <td nowrap align='left' class='pt10b' bgcolor='#e6e6e6'>¡¡¿Í¡¡¡¡·ï¡¡¡¡Èñ</td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo $p2_c_han_jin ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo $p1_c_han_jin ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo $c_han_jin ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo $rui_c_han_jin ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo $p2_lh_han_jin ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo $p1_lh_han_jin ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo $lh_han_jin ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo $rui_lh_han_jin ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo $p2_b_han_jin ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo $p1_b_han_jin ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo $b_han_jin ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo $rui_b_han_jin ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo $p2_s_han_jin ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo $p1_s_han_jin ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo $s_han_jin ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo $rui_s_han_jin ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo $p2_n_han_jin ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo $p1_n_han_jin ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo $n_han_jin ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo $rui_n_han_jin ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo $p2_all_han_jin ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo $p1_all_han_jin ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo $all_han_jin ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo $rui_all_han_jin ?></td>
                    <td nowrap align='left'  class='pt10'>£Ã£ÌÄ¾ÀÜµëÎÁÈæÎ¨</td>
                </tr>
                <tr>
                    <td nowrap align='left' class='pt10b' bgcolor='white'>¡¡·Ð¡¡¡¡¡¡¡¡¡¡Èñ</td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $p2_c_han_kei ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $p1_c_han_kei ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $c_han_kei ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $rui_c_han_kei ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $p2_lh_han_kei ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $p1_lh_han_kei ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $lh_han_kei ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $rui_lh_han_kei ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $p2_b_han_kei ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $p1_b_han_kei ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $b_han_kei ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $rui_b_han_kei ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $p2_s_han_kei ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $p1_s_han_kei ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $s_han_kei ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $rui_s_han_kei ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $p2_n_han_kei ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $p1_n_han_kei ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $n_han_kei ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $rui_n_han_kei ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $p2_all_han_kei ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $p1_all_han_kei ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $all_han_kei ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $rui_all_han_kei ?></td>
                    <td nowrap align='left'  class='pt10'>£Ã£ÌÀêÍ­ÌÌÀÑÈæ¡¦£Ã£ÌÄ¾ÀÜ·ÐÈñ¹ç·×ÈæÎ¨Â¾</td>
                </tr>
                <tr>
                    <td nowrap align='center' class='pt10b' bgcolor='#ffffc6'>ÈÎ´ÉÈñµÚ¤Ó°ìÈÌ´ÉÍýÈñ·×</td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo $p2_c_han_all ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo $p1_c_han_all ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo $c_han_all ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo $rui_c_han_all ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo $p2_lh_han_all ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo $p1_lh_han_all ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo $lh_han_all ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo $rui_lh_han_all ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo $p2_b_han_all ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo $p1_b_han_all ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo $b_han_all ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo $rui_b_han_all ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo $p2_s_han_all ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo $p1_s_han_all ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo $s_han_all ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo $rui_s_han_all ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo $p2_n_han_all ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo $p1_n_han_all ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo $n_han_all ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo $rui_n_han_all ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo $p2_all_han_all ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo $p1_all_han_all ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo $all_han_all ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo $rui_all_han_all ?></td>
                    <td nowrap align='left'  class='pt10'>¡¡</td>  <!-- Í¾Çò -->
                </tr>
                <tr>
                    <td colspan='3' nowrap align='center' class='pt10b' bgcolor='#ceffce'>±Ä¡¡¡¡¶È¡¡¡¡Íø¡¡¡¡±×</td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo $p2_c_ope_profit ?></td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo $p1_c_ope_profit ?></td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo $c_ope_profit ?></td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo $rui_c_ope_profit ?></td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo $p2_lh_ope_profit ?></td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo $p1_lh_ope_profit ?></td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo $lh_ope_profit ?></td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo $rui_lh_ope_profit ?></td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo $p2_b_ope_profit ?></td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo $p1_b_ope_profit ?></td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo $b_ope_profit ?></td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo $rui_b_ope_profit ?></td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo $p2_s_ope_profit ?></td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo $p1_s_ope_profit ?></td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo $s_ope_profit ?></td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo $rui_s_ope_profit ?></td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo $p2_n_ope_profit ?></td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo $p1_n_ope_profit ?></td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo $n_ope_profit ?></td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo $rui_n_ope_profit ?></td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo $p2_all_ope_profit ?></td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo $p1_all_ope_profit ?></td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo $all_ope_profit ?></td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo $rui_all_ope_profit ?></td>
                    <td nowrap align='left'  class='pt10'>¡¡</td>  <!-- Í¾Çò -->
                </tr>
                <tr>
                    <td rowspan='7' align='center' valign='middle' class='pt10b' bgcolor='#ceffce'>±Ä¶È³°Â»±×</td>
                    <td rowspan='4' align='center' class='pt10' bgcolor='#ffffc6'></td> <!-- Í¾Çò -->
                    <td nowrap align='left' class='pt10b' bgcolor='white'>¡¡¶ÈÌ³°ÑÂ÷¼ýÆþ</td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $p2_c_gyoumu ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $p1_c_gyoumu ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $c_gyoumu ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $rui_c_gyoumu ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $p2_lh_gyoumu ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $p1_lh_gyoumu ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $lh_gyoumu ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $rui_lh_gyoumu ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $p2_b_gyoumu ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $p1_b_gyoumu ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $b_gyoumu ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $rui_b_gyoumu ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $p2_s_gyoumu ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $p1_s_gyoumu ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $s_gyoumu ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $rui_s_gyoumu ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $p2_n_gyoumu ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $p1_n_gyoumu ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $n_gyoumu ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $rui_n_gyoumu ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $p2_all_gyoumu ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $p1_all_gyoumu ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $all_gyoumu ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $rui_all_gyoumu ?></td>
                    <?php if ($yyyymm >= 201001) { ?>
                    <td nowrap align='left'  class='pt10'>Åö·î¤Î¿Í°÷Èæ</td>
                    <?php } else { ?>
                    <td nowrap align='left'  class='pt10'>Á°´ü¼ÂÀÓ¤ÎÇä¾å¹âÈæ</td>
                    <?php } ?>
                </tr>
                <tr>
                    <td nowrap align='left' class='pt10b' bgcolor='#e6e6e6'>¡¡»Å¡¡Æþ¡¡³ä¡¡°ú</td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo $p2_c_swari ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo $p1_c_swari ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo $c_swari ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo $rui_c_swari ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo $p2_lh_swari ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo $p1_lh_swari ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo $lh_swari ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo $rui_lh_swari ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo $p2_b_swari ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo $p1_b_swari ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo $b_swari ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo $rui_b_swari ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo $p2_s_swari ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo $p1_s_swari ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo $s_swari ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo $rui_s_swari ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo $p2_n_swari ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo $p1_n_swari ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo $n_swari ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo $rui_n_swari ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo $p2_all_swari ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo $p1_all_swari ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo $all_swari ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo $rui_all_swari ?></td>
                    <?php if ($yyyymm >= 201001) { ?>
                    <td nowrap align='left'  class='pt10'>Åö·î¤Î¿Í°÷Èæ</td>
                    <?php } else { ?>
                    <td nowrap align='left'  class='pt10'>Á°´ü¼ÂÀÓ¤ÎÇä¾å¹âÈæ</td>
                    <?php } ?>
                </tr>
                <tr>
                    <td nowrap align='left' class='pt10b' bgcolor='white'>¡¡¤½¡¡¡¡¤Î¡¡¡¡Â¾</td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $p2_c_pother ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $p1_c_pother ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $c_pother ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $rui_c_pother ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $p2_lh_pother ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $p1_lh_pother ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $lh_pother ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $rui_lh_pother ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $p2_b_pother ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $p1_b_pother ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $b_pother ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $rui_b_pother ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $p2_s_pother ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $p1_s_pother ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $s_pother ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $rui_s_pother ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $p2_n_pother ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $p1_n_pother ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $n_pother ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $rui_n_pother ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $p2_all_pother ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $p1_all_pother ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $all_pother ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $rui_all_pother ?></td>
                    <?php if ($yyyymm >= 201001) { ?>
                    <td nowrap align='left'  class='pt10'>Åö·î¤Î¿Í°÷Èæ</td>
                    <?php } else { ?>
                    <td nowrap align='left'  class='pt10'>Á°´ü¼ÂÀÓ¤ÎÇä¾å¹âÈæ</td>
                    <?php } ?>
                </tr>
                <tr>
                    <td nowrap align='left' class='pt10b' bgcolor='#ffffc6'>¡¡±Ä¶È³°¼ý±× ·×</td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo $p2_c_nonope_profit_sum ?>   </td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo $p1_c_nonope_profit_sum ?>   </td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo $c_nonope_profit_sum ?>      </td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo $rui_c_nonope_profit_sum ?>  </td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo $p2_lh_nonope_profit_sum ?>   </td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo $p1_lh_nonope_profit_sum ?>   </td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo $lh_nonope_profit_sum ?>      </td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo $rui_lh_nonope_profit_sum ?>  </td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo $p2_b_nonope_profit_sum ?>   </td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo $p1_b_nonope_profit_sum ?>   </td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo $b_nonope_profit_sum ?>      </td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo $rui_b_nonope_profit_sum ?>  </td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo $p2_s_nonope_profit_sum ?>   </td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo $p1_s_nonope_profit_sum ?>   </td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo $s_nonope_profit_sum ?>      </td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo $rui_s_nonope_profit_sum ?>  </td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo $p2_n_nonope_profit_sum ?>   </td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo $p1_n_nonope_profit_sum ?>   </td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo $n_nonope_profit_sum ?>      </td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo $rui_n_nonope_profit_sum ?>  </td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo $p2_all_nonope_profit_sum ?> </td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo $p1_all_nonope_profit_sum ?> </td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo $all_nonope_profit_sum ?>    </td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo $rui_all_nonope_profit_sum ?></td>
                    <td nowrap align='left'  class='pt10'>¡¡</td> <!-- Í¾Çò -->
                </tr>
                <tr>
                    <td rowspan='3' align='center' class='pt10' bgcolor='#ffffc6'></td> <!-- Í¾Çò -->
                    <td nowrap align='left' class='pt10b' bgcolor='#e6e6e6'>¡¡»Ù¡¡Ê§¡¡Íø¡¡Â©</td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo $p2_c_srisoku ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo $p1_c_srisoku ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo $c_srisoku ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo $rui_c_srisoku ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo $p2_lh_srisoku ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo $p1_lh_srisoku ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo $lh_srisoku ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo $rui_lh_srisoku ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo $p2_b_srisoku ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo $p1_b_srisoku ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo $b_srisoku ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo $rui_b_srisoku ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo $p2_s_srisoku ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo $p1_s_srisoku ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo $s_srisoku ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo $rui_s_srisoku ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo $p2_n_srisoku ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo $p1_n_srisoku ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo $n_srisoku ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo $rui_n_srisoku ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo $p2_all_srisoku ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo $p1_all_srisoku ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo $all_srisoku ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo $rui_all_srisoku ?></td>
                    <?php if ($yyyymm >= 201001) { ?>
                    <td nowrap align='left'  class='pt10'>Åö·î¤Î¿Í°÷Èæ</td>
                    <?php } else { ?>
                    <td nowrap align='left'  class='pt10'>Á°´ü¼ÂÀÓ¤ÎÇä¾å¹âÈæ</td>
                    <?php } ?>
                </tr>
                <tr>
                    <td nowrap align='left' class='pt10b' bgcolor='white'>¡¡¤½¡¡¡¡¤Î¡¡¡¡Â¾</td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $p2_c_lother ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $p1_c_lother ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $c_lother ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $rui_c_lother ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $p2_lh_lother ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $p1_lh_lother ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $lh_lother ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $rui_lh_lother ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $p2_b_lother ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $p1_b_lother ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $b_lother ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $rui_b_lother ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $p2_s_lother ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $p1_s_lother ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $s_lother ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $rui_s_lother ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $p2_n_lother ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $p1_n_lother ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $n_lother ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $rui_n_lother ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $p2_all_lother ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $p1_all_lother ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $all_lother ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $rui_all_lother ?></td>
                    <?php if ($yyyymm >= 201001) { ?>
                    <td nowrap align='left'  class='pt10'>Åö·î¤Î¿Í°÷Èæ</td>
                    <?php } else { ?>
                    <td nowrap align='left'  class='pt10'>Á°´ü¼ÂÀÓ¤ÎÇä¾å¹âÈæ</td>
                    <?php } ?>
                </tr>
                <tr>
                    <td nowrap align='left' class='pt10b' bgcolor='#ffffc6'>¡¡±Ä¶È³°ÈñÍÑ ·×</td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo $p2_c_nonope_loss_sum ?>   </td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo $p1_c_nonope_loss_sum ?>   </td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo $c_nonope_loss_sum ?>      </td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo $rui_c_nonope_loss_sum ?>  </td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo $p2_lh_nonope_loss_sum ?>   </td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo $p1_lh_nonope_loss_sum ?>   </td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo $lh_nonope_loss_sum ?>      </td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo $rui_lh_nonope_loss_sum ?>  </td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo $p2_b_nonope_loss_sum ?>   </td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo $p1_b_nonope_loss_sum ?>   </td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo $b_nonope_loss_sum ?>      </td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo $rui_b_nonope_loss_sum ?>  </td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo $p2_s_nonope_loss_sum ?>   </td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo $p1_s_nonope_loss_sum ?>   </td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo $s_nonope_loss_sum ?>      </td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo $rui_s_nonope_loss_sum ?>  </td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo $p2_n_nonope_loss_sum ?>   </td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo $p1_n_nonope_loss_sum ?>   </td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo $n_nonope_loss_sum ?>      </td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo $rui_n_nonope_loss_sum ?>  </td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo $p2_all_nonope_loss_sum ?> </td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo $p1_all_nonope_loss_sum ?> </td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo $all_nonope_loss_sum ?>    </td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo $rui_all_nonope_loss_sum ?></td>
                    <td nowrap align='left'  class='pt10'>¡¡</td> <!-- Í¾Çò -->
                </tr>
                <tr>
                    <td colspan='3' nowrap align='center' class='pt10b' bgcolor='#ceffce'>·Ð¡¡¡¡¾ï¡¡¡¡Íø¡¡¡¡±×</td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo $p2_c_current_profit ?>   </td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo $p1_c_current_profit ?>   </td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo $c_current_profit ?>      </td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo $rui_c_current_profit ?>  </td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo $p2_lh_current_profit ?>   </td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo $p1_lh_current_profit ?>   </td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo $lh_current_profit ?>      </td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo $rui_lh_current_profit ?>  </td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo $p2_b_current_profit ?>   </td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo $p1_b_current_profit ?>   </td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo $b_current_profit ?>      </td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo $rui_b_current_profit ?>  </td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo $p2_s_current_profit ?>   </td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo $p1_s_current_profit ?>   </td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo $s_current_profit ?>      </td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo $rui_s_current_profit ?>  </td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo $p2_n_current_profit ?>   </td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo $p1_n_current_profit ?>   </td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo $n_current_profit ?>      </td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo $rui_n_current_profit ?>  </td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo $p2_all_current_profit ?> </td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo $p1_all_current_profit ?> </td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo $all_current_profit ?>    </td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo $rui_all_current_profit ?></td>
                    <td nowrap align='left'  class='pt10'>¡¡</td>  <!-- Í¾Çò -->
                </tr>
                <tr>
                    <td colspan='23' rowspan='5' bgcolor='white' nowrap align='center' class='pt10b'>¡¡</td>
                    <td colspan='3' bgcolor='white' nowrap align='right' class='pt10b'>ÆÃÊÌÍø±×</td>
                    <td nowrap align='right' class='pt10b' bgcolor='white'><?php echo $rui_all_special_profit ?></td>
                    <td rowspan='5' bgcolor='white' nowrap align='center' class='pt10b'>¡¡</td>
                </tr>
                <tr>
                    <td colspan='3' bgcolor='white' nowrap align='right' class='pt10b'>ÆÃÊÌÂ»¼º</td>
                    <td nowrap align='right' class='pt10b' bgcolor='white'><?php echo $rui_all_special_loss ?></td>
                </tr>
                <tr>
                    <td colspan='3' bgcolor='white' nowrap align='right' class='pt10b'>ÀÇ°úÁ°½ãÍø±×¶â³Û</td>
                    <td nowrap align='right' class='pt10b' bgcolor='white'><?php echo $rui_all_before_tax_net_profit ?></td>
                </tr>
                <tr>
                    <td colspan='3' bgcolor='white' nowrap align='right' class='pt10b'>Ë¡¿ÍÀÇÅù</td>
                    <td nowrap align='right' class='pt10b' bgcolor='white'><?php echo $rui_all_corporation_tax ?></td>
                </tr>
                <tr>
                    <td colspan='3' bgcolor='white' nowrap align='right' class='pt10b'>Åö´ü½ãÍø±×¶â³Û</td>
                    <td nowrap align='right' class='pt10b' bgcolor='white'><?php echo $rui_all_net_profit ?></td>
                </tr>
            </TBODY>
        </table>
        </td>
        </tr>
        <tr>
        <td>
        <table width='100%' bgcolor='#d6d3ce' align='center' cellspacing="0" cellpadding="3" border='1'>
            <tbody>
                <tr>
                    <td colspan='20' bgcolor='white' align='left' class='pt10b'><a href='<%=$menu->out_action('ÆÃµ­»ö¹àÆþÎÏ')%>?<?php echo uniqid('menu') ?>' style='text-decoration:none; color:black;'>¡¡¢¨¡¡·î¼¡Â»±×ÆÃµ­»ö¹à</a></td>
                </tr>
                <tr>
                    <td colspan='20' bgcolor='white' class='pt10'>
                        <ol>
                        <?php
                            if ($comment_c != "") {
                                echo "<li><pre>$comment_c</pre></li>\n";
                            }
                            if ($comment_l != "") {
                                echo "<li><pre>$comment_l</pre></li>\n";
                            }
                            if ($comment_t != "") {
                                echo "<li><pre>$comment_t</pre></li>\n";
                            }
                            if ($comment_s != "") {
                                echo "<li><pre>$comment_s</pre></li>\n";
                            }
                            if ($comment_b != "") {
                                echo "<li><pre>$comment_b</pre></li>\n";
                            }
                            if ($comment_all != "") {
                                echo "<li><pre>$comment_all</pre></li>\n";
                            }
                            if ($comment_other != "") {
                                echo "<li><pre>$comment_other</pre></li>\n";
                            }
                        ?>
                        </ol>
                    </td>
                </tr>
            </tbody>
        </table>
        </td>
        </tr>
    </table>
    </center>
</body>
</html>
