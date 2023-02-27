<?php
//////////////////////////////////////////////////////////////////////////////
// ·î¼¡Â»±×´Ø·¸ ·î¼¡ ¥ê¥Ë¥¢¡¦µ¡¹© Â»±×·×»»½ñ                                //
// Copyright (C) 2015 - 2015 Norihisa.Ohya norihisa_ooya@nitto-kohki.co.jp  //
// Changed history                                                          //
// 2015/06/04 Created   profit_loss_pl_act_lt.php                           //
// 2015/06/15 µ¡¹©¤Î¥Ç¡¼¥¿¤òÉ½¼¨¤¹¤ë¤è¤¦¤ËÊÑ¹¹(¥Ð¥¤¥â¥ë¤ÎÆþÂØ¤Ê¤Î¤Ç$b_¡Á)   //
//////////////////////////////////////////////////////////////////////////////
// ini_set('error_reporting', E_STRICT);       // E_STRICT=2048(php5) E_ALL=2047 debug ÍÑ
// ini_set('error_reporting', E_ALL);          // E_ALL='2047' debug ÍÑ
// ini_set('display_errors', '1');             // Error É½¼¨ ON debug ÍÑ ¥ê¥ê¡¼¥¹¸å¥³¥á¥ó¥È
session_start();                            // ini_set()¤Î¼¡¤Ë»ØÄê¤¹¤ë¤³¤È Script ºÇ¾å¹Ô

require_once ('../function.php');           // define.php ¤È pgsql.php ¤ò require_once ¤·¤Æ¤¤¤ë
require_once ('../tnk_func.php');           // TNK ¤Ë°ÍÂ¸¤¹¤ëÉôÊ¬¤Î´Ø¿ô¤ò require_once ¤·¤Æ¤¤¤ë
require_once ('../MenuHeader.php');         // TNK Á´¶¦ÄÌ menu class
access_log();                               // Script Name ¤Ï¼«Æ°¼èÆÀ

///// TNK ¶¦ÍÑ¥á¥Ë¥å¡¼¥¯¥é¥¹¤Î¥¤¥ó¥¹¥¿¥ó¥¹¤òºîÀ®
$menu = new MenuHeader(0);                  // Ç§¾Ú¥Á¥§¥Ã¥¯0=°ìÈÌ°Ê¾å Ìá¤êÀè=TOP_MENU ¥¿¥¤¥È¥ëÌ¤ÀßÄê
    // ¼ÂºÝ¤ÎÇ§¾Ú¤Ïprofit_loss_submit.php¤Ç¹Ô¤Ã¤Æ¤¤¤ëaccount_group_check()¤ò»ÈÍÑ

////////////// ¥µ¥¤¥ÈÀßÄê
// $menu->set_site(10, 7);                  // site_index=10(Â»±×¥á¥Ë¥å¡¼) site_id=7(·î¼¡Â»±×)
//////////// É½Âê¤ÎÀßÄê
$menu->set_caption('ÆÊÌÚÆüÅì¹©´ï(³ô)');
//////////// ¸Æ½ÐÀè¤ÎactionÌ¾¤È¥¢¥É¥ì¥¹ÀßÄê
$menu->set_action('ÆÃµ­»ö¹àÆþÎÏ',   PL . 'profit_loss_comment_put_lt.php');

///// ´ü¡¦·î¤Î¼èÆÀ
$ki = Ym_to_tnk($_SESSION['pl_ym']);
$tuki = substr($_SESSION['pl_ym'],4,2);

//////////// ¥¿¥¤¥È¥ëÌ¾(¥½¡¼¥¹¤Î¥¿¥¤¥È¥ëÌ¾¤È¥Õ¥©¡¼¥à¤Î¥¿¥¤¥È¥ëÌ¾)
$menu->set_title("Âè {$ki} ´ü¡¡{$tuki} ·îÅÙ¡¡£Ì £Ô ¾¦ ÉÊ ÊÌ Â» ±× ·× »» ½ñ");

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
if ($yyyymm >= 200909) {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='»î½¤Çä¾å¹â'", $yyyymm);
    if (getUniResult($query, $s_uri) < 1) {
        $s_uri        = 0;      // ¸¡º÷¼ºÇÔ
        $s_uri_sagaku = 0;
    } else {
        if ($yyyymm == 200906) {
            $s_uri = $s_uri - 3100900;
        } elseif ($yyyymm == 200905) {
            $s_uri = $s_uri + 1550450;
        } elseif ($yyyymm == 200904) {
            $s_uri = $s_uri + 1550450;
        }
        $s_uri_sagaku = $s_uri;
    }
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='»î½¤Çä¾åÄ´À°³Û'", $yyyymm);
    if (getUniResult($query, $s_uri_cho) < 1) {
        // ¸¡º÷¼ºÇÔ
        $s_uri = number_format(($s_uri / $tani), $keta);
    } else {
        $s_uri_sagaku = $s_uri_sagaku + $s_uri_cho;
        $s_uri        = number_format(($s_uri / $tani), $keta);
    }
} else {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='»î½¤Çä¾å¹â'", $yyyymm);
    if (getUniResult($query, $s_uri) < 1) {
        $s_uri        = 0;      // ¸¡º÷¼ºÇÔ
        $s_uri_sagaku = 0;
    } else {
        if ($yyyymm == 200906) {
            $s_uri = $s_uri - 3100900;
        } elseif ($yyyymm == 200905) {
            $s_uri = $s_uri + 1550450;
        } elseif ($yyyymm == 200904) {
            $s_uri = $s_uri + 1550450;
        }
        $s_uri_sagaku = $s_uri;
        $s_uri = number_format(($s_uri / $tani), $keta);
    }
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
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='»î½¤Çä¾å¹â'", $p1_ym);
    if (getUniResult($query, $p1_s_uri) < 1) {
        $p1_s_uri        = 0;     // ¸¡º÷¼ºÇÔ
        $p1_s_uri_sagaku = 0;
    } else {
        if ($p1_ym == 200906) {
            $p1_s_uri = $p1_s_uri - 3100900;
        } elseif ($p1_ym == 200905) {
            $p1_s_uri = $p1_s_uri + 1550450;
        } elseif ($p1_ym == 200904) {
            $p1_s_uri = $p1_s_uri + 1550450;
        }
        $p1_s_uri_sagaku = $p1_s_uri;
    }
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='»î½¤Çä¾åÄ´À°³Û'", $p1_ym);
    if (getUniResult($query, $p1_s_uri_cho) < 1) {
        // ¸¡º÷¼ºÇÔ
        $p1_s_uri = number_format(($p1_s_uri / $tani), $keta);
    } else {
        $p1_s_uri_sagaku = $p1_s_uri_sagaku + $p1_s_uri_cho;
        $p1_s_uri        = number_format(($p1_s_uri / $tani), $keta);
    }
} else {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='»î½¤Çä¾å¹â'", $p1_ym);
    if (getUniResult($query, $p1_s_uri) < 1) {
        $p1_s_uri        = 0;     // ¸¡º÷¼ºÇÔ
        $p1_s_uri_sagaku = 0;
    } else {
        if ($p1_ym == 200906) {
            $p1_s_uri = $p1_s_uri - 3100900;
        } elseif ($p1_ym == 200905) {
            $p1_s_uri = $p1_s_uri + 1550450;
        } elseif ($p1_ym == 200904) {
            $p1_s_uri = $p1_s_uri + 1550450;
        }
        $p1_s_uri_sagaku = $p1_s_uri;
        $p1_s_uri        = number_format(($p1_s_uri / $tani), $keta);
    }
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
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='»î½¤Çä¾å¹â'", $p2_ym);
    if (getUniResult($query, $p2_s_uri) < 1) {
        $p2_s_uri        = 0;     // ¸¡º÷¼ºÇÔ
        $p2_s_uri_sagaku = 0;
    } else {
        if ($p2_ym == 200906) {
            $p2_s_uri = $p2_s_uri - 3100900;
        } elseif ($p2_ym == 200905) {
            $p2_s_uri = $p2_s_uri + 1550450;
        } elseif ($p2_ym == 200904) {
            $p2_s_uri = $p2_s_uri + 1550450;
        }
        $p2_s_uri_sagaku = $p2_s_uri;
    }
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='»î½¤Çä¾åÄ´À°³Û'", $p2_ym);
    if (getUniResult($query, $p2_s_uri_cho) < 1) {
        // ¸¡º÷¼ºÇÔ
        $p2_s_uri = number_format(($p2_s_uri / $tani), $keta);
    } else {
        $p2_s_uri_sagaku = $p2_s_uri_sagaku + $p2_s_uri_cho;
        $p2_s_uri        = number_format(($p2_s_uri / $tani), $keta);
    }
} else {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='»î½¤Çä¾å¹â'", $p2_ym);
    if (getUniResult($query, $p2_s_uri) < 1) {
        $p2_s_uri        = 0;     // ¸¡º÷¼ºÇÔ
        $p2_s_uri_sagaku = 0;
    } else {
        if ($p2_ym == 200906) {
            $p2_s_uri = $p2_s_uri - 3100900;
        } elseif ($p2_ym == 200905) {
            $p2_s_uri = $p2_s_uri + 1550450;
        } elseif ($p2_ym == 200904) {
            $p2_s_uri = $p2_s_uri + 1550450;
        }
        $p2_s_uri_sagaku = $p2_s_uri;
        $p2_s_uri        = number_format(($p2_s_uri / $tani), $keta);
    }
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
if ($yyyymm >= 200909) {
    $query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='»î½¤Çä¾å¹â'", $str_ym, $yyyymm);
    if (getUniResult($query, $rui_s_uri) < 1) {
        $rui_s_uri        = 0;      // ¸¡º÷¼ºÇÔ
        $rui_s_uri_sagaku = 0;
    } else {
        $rui_s_uri_sagaku = $rui_s_uri;
    }
    $query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='»î½¤Çä¾åÄ´À°³Û'", $str_ym, $yyyymm);
    if (getUniResult($query, $rui_s_uri_cho) < 1) {
        // ¸¡º÷¼ºÇÔ
        $rui_s_uri        = number_format(($rui_s_uri / $tani), $keta);
    } else {
        $rui_s_uri_sagaku = $rui_s_uri_sagaku + $rui_s_uri_cho;
        $rui_s_uri        = number_format(($rui_s_uri / $tani), $keta);
    }
} else {
    $query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='»î½¤Çä¾å¹â'", $str_ym, $yyyymm);
    if (getUniResult($query, $rui_s_uri) < 1) {
        $rui_s_uri        = 0;     // ¸¡º÷¼ºÇÔ
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
    ///// »î¸³¡¦½¤Íý
$p2_s_invent  = 0;
$p1_s_invent  = 0;
$s_invent     = 0;
$rui_s_invent = 0;
    ///// Åö·î
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
    ///// Åö·î
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='»î½¤ºàÎÁÈñ'", $yyyymm);
if (getUniResult($query, $s_metarial) < 1) {
    $s_metarial        = 0;          // ¸¡º÷¼ºÇÔ
    $s_metarial_sagaku = 0;
} else {
    $s_metarial_sagaku = $s_metarial;
    $s_metarial        = number_format(($s_metarial / $tani), $keta);
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
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='»î½¤ºàÎÁÈñ'", $p1_ym);
if (getUniResult($query, $p1_s_metarial) < 1) {
    $p1_s_metarial        = 0;          // ¸¡º÷¼ºÇÔ
    $p1_s_metarial_sagaku = 0;
} else {
    $p1_s_metarial_sagaku = $p1_s_metarial;
    $p1_s_metarial        = number_format(($p1_s_metarial / $tani), $keta);
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
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='»î½¤ºàÎÁÈñ'", $p2_ym);
if (getUniResult($query, $p2_s_metarial) < 1) {
    $p2_s_metarial        = 0;          // ¸¡º÷¼ºÇÔ
    $p2_s_metarial_sagaku = 0;
} else {
    $p2_s_metarial_sagaku = $p2_s_metarial;
    $p2_s_metarial        = number_format(($p2_s_metarial / $tani), $keta);
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
$query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='»î½¤ºàÎÁÈñ'", $str_ym, $yyyymm);
if (getUniResult($query, $rui_s_metarial) < 1) {
    $rui_s_metarial        = 0;          // ¸¡º÷¼ºÇÔ
    $rui_s_metarial_sagaku = 0;
} else {
    $rui_s_metarial_sagaku = $rui_s_metarial;
    $rui_s_metarial        = number_format(($rui_s_metarial / $tani), $keta);
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
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='»î½¤Ï«Ì³Èñ'", $yyyymm);
if (getUniResult($query, $s_roumu) < 1) {
    $s_roumu        = 0;    // ¸¡º÷¼ºÇÔ
    $s_roumu_sagaku = 0;
} else {
    $s_roumu_sagaku = $s_roumu;
    $s_roumu        = number_format(($s_roumu / $tani), $keta);
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
    ///// Á°·î
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='»î½¤Ï«Ì³Èñ'", $p1_ym);
if (getUniResult($query, $p1_s_roumu) < 1) {
    $p1_s_roumu        = 0;    // ¸¡º÷¼ºÇÔ
    $p1_s_roumu_sagaku = 0;
} else {
    $p1_s_roumu_sagaku = $p1_s_roumu;
    $p1_s_roumu        = number_format(($p1_s_roumu / $tani), $keta);
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
    ///// Á°Á°·î
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='»î½¤Ï«Ì³Èñ'", $p2_ym);
if (getUniResult($query, $p2_s_roumu) < 1) {
    $p2_s_roumu        = 0;    // ¸¡º÷¼ºÇÔ
    $p2_s_roumu_sagaku = 0;
} else {
    $p2_s_roumu_sagaku = $p2_s_roumu;
    $p2_s_roumu        = number_format(($p2_s_roumu / $tani), $keta);
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
    ///// º£´üÎß·×
$query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='»î½¤Ï«Ì³Èñ'", $str_ym, $yyyymm);
if (getUniResult($query, $rui_s_roumu) < 1) {
    $rui_s_roumu        = 0;    // ¸¡º÷¼ºÇÔ
    $rui_s_roumu_sagaku = 0;
} else {
    $rui_s_roumu_sagaku = $rui_s_roumu;
    $rui_s_roumu        = number_format(($rui_s_roumu / $tani), $keta);
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

/********** ·ÐÈñ(À½Â¤·ÐÈñ) **********/
    ///// Åö·î
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='»î½¤À½Â¤·ÐÈñ'", $yyyymm);
if (getUniResult($query, $s_expense) < 1) {
    $s_expense        = 0;    // ¸¡º÷¼ºÇÔ
    $s_expense_sagaku = 0;
} else {
    $s_expense_sagaku = $s_expense;
    $s_expense        = number_format(($s_expense / $tani), $keta);
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
    ///// Á°·î
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='»î½¤À½Â¤·ÐÈñ'", $p1_ym);
if (getUniResult($query, $p1_s_expense) < 1) {
    $p1_s_expense        = 0;    // ¸¡º÷¼ºÇÔ
    $p1_s_expense_sagaku = 0;
} else {
    $p1_s_expense_sagaku = $p1_s_expense;
    $p1_s_expense        = number_format(($p1_s_expense / $tani), $keta);
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
    ///// Á°Á°·î
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='»î½¤À½Â¤·ÐÈñ'", $p2_ym);
if (getUniResult($query, $p2_s_expense) < 1) {
    $p2_s_expense        = 0;    // ¸¡º÷¼ºÇÔ
    $p2_s_expense_sagaku = 0;
} else {
    $p2_s_expense_sagaku = $p2_s_expense;
    $p2_s_expense        = number_format(($p2_s_expense / $tani), $keta);
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
    ///// º£´üÎß·×
$query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='»î½¤À½Â¤·ÐÈñ'", $str_ym, $yyyymm);
if (getUniResult($query, $rui_s_expense) < 1) {
    $rui_s_expense        = 0;    // ¸¡º÷¼ºÇÔ
    $rui_s_expense_sagaku = 0;
} else {
    $rui_s_expense_sagaku = $rui_s_expense;
    $rui_s_expense        = number_format(($rui_s_expense / $tani), $keta);
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

/********** ´üËöºàÎÁ»Å³ÝÉÊÃª²·¹â **********/
    ///// »î¸³¡¦½¤Íý
$p2_s_endinv = 0;
$p1_s_endinv = 0;
$s_endinv    = 0;
    ///// Åö·î
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
    $s_urigen        = number_format(($s_urigen / $tani), $keta);
    ///// µ¡¹©
    $b_urigen        = $b_invent_sagaku + $b_metarial_sagaku + $b_roumu_sagaku + $b_expense_sagaku - $b_endinv_sagaku;
    $b_urigen_sagaku = $b_urigen;
    $b_urigen        = number_format(($b_urigen / $tani), $keta);
    ///// CL
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
    $p1_s_urigen        = number_format(($p1_s_urigen / $tani), $keta);
    ///// µ¡¹©
    $p1_b_urigen        = $p1_b_invent_sagaku + $p1_b_metarial_sagaku + $p1_b_roumu_sagaku + $p1_b_expense_sagaku - $p1_b_endinv_sagaku;
    $p1_b_urigen_sagaku = $p1_b_urigen;
    $p1_b_urigen        = number_format(($p1_b_urigen / $tani), $keta);
    ///// CL
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
    $p2_s_urigen        = number_format(($p2_s_urigen / $tani), $keta);
    ///// µ¡¹©
    $p2_b_urigen        = $p2_b_invent_sagaku + $p2_b_metarial_sagaku + $p2_b_roumu_sagaku + $p2_b_expense_sagaku - $p2_b_endinv_sagaku;
    $p2_b_urigen_sagaku = $p2_b_urigen;
    $p2_b_urigen        = number_format(($p2_b_urigen / $tani), $keta);
    ///// CL
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
    $rui_s_urigen        = number_format(($rui_s_urigen / $tani), $keta);
    ///// µ¡¹©
    $rui_b_urigen        = $rui_b_invent_sagaku + $rui_b_metarial_sagaku + $rui_b_roumu_sagaku + $rui_b_expense_sagaku - $b_endinv_sagaku;
    $rui_b_urigen_sagaku = $rui_b_urigen;
    $rui_b_urigen        = number_format(($rui_b_urigen / $tani), $keta);
    ///// CL
$query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='Á´ÂÎÇä¾å¸¶²Á'", $str_ym, $yyyymm);
if (getUniResult($query, $rui_all_urigen) < 1) {
    $rui_all_urigen = 0;                                // ¸¡º÷¼ºÇÔ
} else {
    $rui_all_urigen = number_format(($rui_all_urigen / $tani), $keta);
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
    ///// »î¸³¡¦½¤Íý
$p2_s_gross_profit         = $p2_s_uri_sagaku - $p2_s_urigen_sagaku;
$p2_s_gross_profit_sagaku  = $p2_s_gross_profit;
$p2_s_gross_profit         = number_format(($p2_s_gross_profit / $tani), $keta);

$p1_s_gross_profit         = $p1_s_uri_sagaku - $p1_s_urigen_sagaku;
$p1_s_gross_profit_sagaku  = $p1_s_gross_profit;
$p1_s_gross_profit         = number_format(($p1_s_gross_profit / $tani), $keta);

$s_gross_profit            = $s_uri_sagaku - $s_urigen_sagaku;
$s_gross_profit_sagaku     = $s_gross_profit;
$s_gross_profit            = number_format(($s_gross_profit / $tani), $keta);

$rui_s_gross_profit        = $rui_s_uri_sagaku - $rui_s_urigen_sagaku;
$rui_s_gross_profit_sagaku = $rui_s_gross_profit;
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
    $s_han_jin        = 0;    // ¸¡º÷¼ºÇÔ
    $s_han_jin_sagaku = 0;
} else {
    $s_han_jin_sagaku = $s_han_jin;
    $s_han_jin        = number_format(($s_han_jin / $tani), $keta);
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
    $l_allo_kin       = 0;    // ¸¡º÷¼ºÇÔ
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
    ///// Á°·î
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='»î½¤¿Í·ïÈñ'", $p1_ym);
if (getUniResult($query, $p1_s_han_jin) < 1) {
    $p1_s_han_jin        = 0;    // ¸¡º÷¼ºÇÔ
    $p1_s_han_jin_sagaku = 0;
} else {
    $p1_s_han_jin_sagaku = $p1_s_han_jin;
    $p1_s_han_jin        = number_format(($p1_s_han_jin / $tani), $keta);
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
    $p1_l_allo_kin       = 0;    // ¸¡º÷¼ºÇÔ
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
    ///// Á°Á°·î
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='»î½¤¿Í·ïÈñ'", $p2_ym);
if (getUniResult($query, $p2_s_han_jin) < 1) {
    $p2_s_han_jin        = 0;    // ¸¡º÷¼ºÇÔ
    $p2_s_han_jin_sagaku = 0;
} else {
    $p2_s_han_jin_sagaku = $p2_s_han_jin;
    $p2_s_han_jin        = number_format(($p2_s_han_jin / $tani), $keta);
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
    $p2_l_allo_kin       = 0;    // ¸¡º÷¼ºÇÔ
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
    ///// º£´üÎß·×
$query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='»î½¤¿Í·ïÈñ'", $str_ym, $yyyymm);
if (getUniResult($query, $rui_s_han_jin) < 1) {
    $rui_s_han_jin        = 0;    // ¸¡º÷¼ºÇÔ
    $rui_s_han_jin_sagaku = 0;
} else {
    $rui_s_han_jin_sagaku = $rui_s_han_jin;
    $rui_s_han_jin        = number_format(($rui_s_han_jin / $tani), $keta);
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
    $rui_l_allo_kin       = 0;    // ¸¡º÷¼ºÇÔ
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

/********** ÈÎ´ÉÈñ¤Î·ÐÈñ **********/
    ///// Åö·î
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='»î½¤ÈÎ´ÉÈñ·ÐÈñ'", $yyyymm);
if (getUniResult($query, $s_han_kei) < 1) {
    $s_han_kei        = 0;    // ¸¡º÷¼ºÇÔ
    $s_han_kei_sagaku = 0;
} else {
    $s_han_kei_sagaku = $s_han_kei;
    $s_han_kei        = number_format(($s_han_kei / $tani), $keta);
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
    ///// Á°·î
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='»î½¤ÈÎ´ÉÈñ·ÐÈñ'", $p1_ym);
if (getUniResult($query, $p1_s_han_kei) < 1) {
    $p1_s_han_kei        = 0;    // ¸¡º÷¼ºÇÔ
    $p1_s_han_kei_sagaku = 0;
} else {
    $p1_s_han_kei_sagaku = $p1_s_han_kei;
    $p1_s_han_kei        = number_format(($p1_s_han_kei / $tani), $keta);
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
    ///// Á°Á°·î
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='»î½¤ÈÎ´ÉÈñ·ÐÈñ'", $p2_ym);
if (getUniResult($query, $p2_s_han_kei) < 1) {
    $p2_s_han_kei        = 0;    // ¸¡º÷¼ºÇÔ
    $p2_s_han_kei_sagaku = 0;
} else {
    $p2_s_han_kei_sagaku = $p2_s_han_kei;
    $p2_s_han_kei        = number_format(($p2_s_han_kei / $tani), $keta);
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
    ///// º£´üÎß·×
$query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='»î½¤ÈÎ´ÉÈñ·ÐÈñ'", $str_ym, $yyyymm);
if (getUniResult($query, $rui_s_han_kei) < 1) {
    $rui_s_han_kei        = 0;    // ¸¡º÷¼ºÇÔ
    $rui_s_han_kei_sagaku = 0;
} else {
    $rui_s_han_kei_sagaku = $rui_s_han_kei;
    $rui_s_han_kei        = number_format(($rui_s_han_kei / $tani), $keta);
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

/********** ÈÎ´ÉÈñ¤Î¹ç·× **********/
    ///// Åö·î
    ///// »î¸³¡¦½¤Íý
    $s_han_all        = $s_han_jin_sagaku + $s_han_kei_sagaku;
    $s_han_all_sagaku = $s_han_all;
    $s_han_all        = number_format(($s_han_all / $tani), $keta);
    ///// Åö·î
    ///// µ¡¹©
    $b_han_all        = $b_han_jin_sagaku + $b_han_kei_sagaku;
    $b_han_all_sagaku = $b_han_all;
    $b_han_all        = number_format(($b_han_all / $tani), $keta);
    ///// CL
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
    ///// »î¸³¡¦½¤Íý
$p2_s_ope_profit         = $p2_s_gross_profit_sagaku - $p2_s_han_all_sagaku;
$p2_s_ope_profit_sagaku  = $p2_s_ope_profit;
$p2_s_ope_profit         = number_format(($p2_s_ope_profit / $tani), $keta);

$p1_s_ope_profit         = $p1_s_gross_profit_sagaku - $p1_s_han_all_sagaku;
$p1_s_ope_profit_sagaku  = $p1_s_ope_profit;
$p1_s_ope_profit         = number_format(($p1_s_ope_profit / $tani), $keta);

$s_ope_profit            = $s_gross_profit_sagaku - $s_han_all_sagaku;
$s_ope_profit_sagaku     = $s_ope_profit;
$s_ope_profit            = number_format(($s_ope_profit / $tani), $keta);

$rui_s_ope_profit        = $rui_s_gross_profit_sagaku - $rui_s_han_all_sagaku;
$rui_s_ope_profit_sagaku = $rui_s_ope_profit;
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
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='»î½¤¶ÈÌ³°ÑÂ÷¼ýÆþºÆ·×»»'", $yyyymm);
} else {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='»î½¤¶ÈÌ³°ÑÂ÷¼ýÆþ'", $yyyymm);
}
if (getUniResult($query, $s_gyoumu) < 1) {
    $s_gyoumu        = 0;                       // ¸¡º÷¼ºÇÔ
    $s_gyoumu_sagaku = 0;
} else {
    if ($yyyymm == 200912) {
        $s_gyoumu = $s_gyoumu - 722;
    }
    if ($yyyymm == 201001) {
        $s_gyoumu = $s_gyoumu + 29125;
    }
    $s_gyoumu_sagaku = $s_gyoumu;
    $s_gyoumu        = number_format(($s_gyoumu / $tani), $keta);
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
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='»î½¤¶ÈÌ³°ÑÂ÷¼ýÆþºÆ·×»»'", $p1_ym);
} else {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='»î½¤¶ÈÌ³°ÑÂ÷¼ýÆþ'", $p1_ym);
}
if (getUniResult($query, $p1_s_gyoumu) < 1) {
    $p1_s_gyoumu        = 0;                       // ¸¡º÷¼ºÇÔ
    $p1_s_gyoumu_sagaku = 0;
} else {
    if ($p1_ym == 200912) {
        $p1_s_gyoumu = $p1_s_gyoumu - 722;
    }
    if ($p1_ym == 201001) {
        $p1_s_gyoumu = $p1_s_gyoumu + 29125;
    }
    $p1_s_gyoumu_sagaku = $p1_s_gyoumu;
    $p1_s_gyoumu        = number_format(($p1_s_gyoumu / $tani), $keta);
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
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='»î½¤¶ÈÌ³°ÑÂ÷¼ýÆþºÆ·×»»'", $p2_ym);
} else {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='»î½¤¶ÈÌ³°ÑÂ÷¼ýÆþ'", $p2_ym);
}
if (getUniResult($query, $p2_s_gyoumu) < 1) {
    $p2_s_gyoumu        = 0;                       // ¸¡º÷¼ºÇÔ
    $p2_s_gyoumu_sagaku = 0;
} else {
    if ($p2_ym == 200912) {
        $p2_s_gyoumu = $p2_s_gyoumu - 722;
    }
    if ($p2_ym == 201001) {
        $p2_s_gyoumu = $p2_s_gyoumu + 29125;
    }
    $p2_s_gyoumu_sagaku = $p2_s_gyoumu;
    $p2_s_gyoumu        = number_format(($p2_s_gyoumu / $tani), $keta);
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
    $query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='»î½¤¶ÈÌ³°ÑÂ÷¼ýÆþºÆ·×»»'", $str_ym, $yyyymm);
    if (getUniResult($query, $rui_s_gyoumu) < 1) {
        $rui_s_gyoumu = 0;    // ¸¡º÷¼ºÇÔ
        $rui_s_gyoumu_sagaku = 0;
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
        //$rui_s_gyoumu_b = $rui_s_gyoumu_b - 722;
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
        if ($yyyymm >= 200912 && $yyyymm <= 201003) {
            $rui_s_gyoumu = $rui_s_gyoumu - 722;
        }
        if ($yyyymm >= 201001 && $yyyymm <= 201003) {
            $rui_s_gyoumu = $rui_s_gyoumu + 29125;
        }
        $rui_s_gyoumu_sagaku = $rui_s_gyoumu;
        $rui_s_gyoumu = number_format(($rui_s_gyoumu / $tani), $keta);
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
    if ($yyyymm == 200912) {
        $s_pother = $s_pother + 722;
    }
    if ($yyyymm == 201001) {
        $s_pother = $s_pother - 29125;
    }
    $s_pother_sagaku = $s_pother;
    $s_pother        = number_format(($s_pother / $tani), $keta);
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
    if ($p1_ym == 200912) {
        $p1_s_pother = $p1_s_pother + 722;
    }
    if ($p1_ym == 201001) {
        $p1_s_pother = $p1_s_pother - 29125;
    }
    $p1_s_pother_sagaku = $p1_s_pother;
    $p1_s_pother        = number_format(($p1_s_pother / $tani), $keta);
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
    if ($p2_ym == 200912) {
        $p2_s_pother = $p2_s_pother + 722;
    }
    if ($p2_ym == 201001) {
        $p2_s_pother = $p2_s_pother - 29125;
    }
    $p2_s_pother_sagaku = $p2_s_pother;
    $p2_s_pother        = number_format(($p2_s_pother / $tani), $keta);
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
        if ($yyyymm >= 200912 && $yyyymm <= 201003) {
            $rui_s_pother = $rui_s_pother + 722;
        }
        if ($yyyymm >= 201001 && $yyyymm <= 201003) {
            $rui_s_pother = $rui_s_pother - 29125;
        }
        $rui_s_pother_sagaku = $rui_s_pother;
        $rui_s_pother = number_format(($rui_s_pother / $tani), $keta);
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
    $query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='»î½¤»ÙÊ§ÍøÂ©ºÆ·×»»'", $str_ym, $yyyymm);
    if (getUniResult($query, $rui_s_srisoku) < 1) {
        $rui_s_srisoku = 0;                           // ¸¡º÷¼ºÇÔ
        $rui_s_srisoku_sagaku = 0;
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
    $query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='»î½¤±Ä¶È³°ÈñÍÑ¤½¤ÎÂ¾ºÆ·×»»'", $str_ym, $yyyymm);
    if (getUniResult($query, $rui_s_lother) < 1) {
        $rui_s_lother = 0;                           // ¸¡º÷¼ºÇÔ
        $rui_s_lother_sagaku = 0;
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
    ///// »î¸³¡¦½¤Íý
$p2_s_current_profit         = $p2_s_ope_profit_sagaku + $p2_s_nonope_profit_sum_sagaku - $p2_s_nonope_loss_sum_sagaku;
$p2_s_current_profit_sagaku  = $p2_s_current_profit;
$p2_s_current_profit         = number_format(($p2_s_current_profit / $tani), $keta);

$p1_s_current_profit         = $p1_s_ope_profit_sagaku + $p1_s_nonope_profit_sum_sagaku - $p1_s_nonope_loss_sum_sagaku;
$p1_s_current_profit_sagaku  = $p1_s_current_profit;
$p1_s_current_profit         = number_format(($p1_s_current_profit / $tani), $keta);

$s_current_profit            = $s_ope_profit_sagaku + $s_nonope_profit_sum_sagaku - $s_nonope_loss_sum_sagaku;
$s_current_profit_sagaku     = $s_current_profit;
$s_current_profit            = number_format(($s_current_profit / $tani), $keta);

$rui_s_current_profit        = $rui_s_ope_profit_sagaku + $rui_s_nonope_profit_sum_sagaku - $rui_s_nonope_loss_sum_sagaku;
$rui_s_current_profit_sagaku = $rui_s_current_profit;
$rui_s_current_profit_temp   = $rui_s_current_profit;
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

////////// ÆÃµ­»ö¹à¤Î¼èÆÀ
$query = sprintf("select comment from act_comment_history where pl_bs_ym=%d and item='¥ê¥Ë¥¢blsÂ»±×·×»»½ñ'", $yyyymm);
if (getUniResult($query,$comment_l) <= 0) {
    $comment_l = "";
}
$query = sprintf("select comment from act_comment_history where pl_bs_ym=%d and item='µ¡¹©Â»±×·×»»½ñ'", $yyyymm);
if (getUniResult($query,$comment_b) <= 0) {
    $comment_b = "";
}

$query = sprintf("select comment from act_comment_history where pl_bs_ym=%d and item='Á´ÂÎblsÂ»±×·×»»½ñ'", $yyyymm);
if (getUniResult($query,$comment_all) <= 0) {
    $comment_all = "";
}
$query = sprintf("select comment from act_comment_history where pl_bs_ym=%d and item='¤½¤ÎÂ¾blsÂ»±×·×»»½ñ'", $yyyymm);
if (getUniResult($query,$comment_other) <= 0) {
    $comment_other = "";
}
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
    ///////// ³Æ¥Ç¡¼¥¿¤ÎÊÝ´É ¥ê¥Ë¥¢É¸½à=0 µ¡¹©=1
    $input_data = array();
    for ($i = 0; $i < 20; $i++) {
        switch ($i) {
                case  0:                                            // Çä¾å¹â
                    $input_data[$i][0] = $lh_uri;                   // ¥ê¥Ë¥¢É¸½à
                    $input_data[$i][1] = $b_uri;                    // µ¡¹©
                break;
                case  1:                                            // ´ü¼óºàÎÁ»Å³ÝÉÊÃª²·¹â
                    $input_data[$i][0] = $lh_invent;                // ¥ê¥Ë¥¢É¸½à
                    $input_data[$i][1] = $b_invent;                 // µ¡¹©
                break;
                case  2:                                            // ºàÎÁÈñ(»ÅÆþ¹â)
                    $input_data[$i][0] = $lh_metarial;              // ¥ê¥Ë¥¢É¸½à
                    $input_data[$i][1] = $b_metarial;               // µ¡¹©
                break;
                case  3:                                            // Ï«Ì³Èñ
                    $input_data[$i][0] = $lh_roumu;                 // ¥ê¥Ë¥¢É¸½à
                    $input_data[$i][1] = $b_roumu;                  // µ¡¹©
                break;
                case  4:                                            // À½Â¤·ÐÈñ
                    $input_data[$i][0] = $lh_expense;               // ¥ê¥Ë¥¢É¸½à
                    $input_data[$i][1] = $b_expense;                // µ¡¹©
                break;
                case  5:                                            // ´üËöºàÎÁ»Å³ÝÉÊÃª²·¹â
                    $input_data[$i][0] = $lh_endinv;                // ¥ê¥Ë¥¢É¸½à
                    $input_data[$i][1] = $b_endinv;                 // µ¡¹©
                break;
                case  6:                                            // Çä¾å¸¶²Á
                    $input_data[$i][0] = $lh_urigen;                // ¥ê¥Ë¥¢É¸½à
                    $input_data[$i][1] = $b_urigen;                 // µ¡¹©
                break;
                case  7:                                            // Çä¾åÁíÍø±×
                    $input_data[$i][0] = $lh_gross_profit;          // ¥ê¥Ë¥¢É¸½à
                    $input_data[$i][1] = $b_gross_profit;           // µ¡¹©
                break;
                case  8:                                            // ¿Í·ïÈñ
                    $input_data[$i][0] = $lh_han_jin;               // ¥ê¥Ë¥¢É¸½à
                    $input_data[$i][1] = $b_han_jin;                // µ¡¹©
                break;
                case  9:                                            // ·ÐÈñ
                    $input_data[$i][0] = $lh_han_kei;               // ¥ê¥Ë¥¢É¸½à
                    $input_data[$i][1] = $b_han_kei;                // µ¡¹©
                break;
                case 10:                                            // ÈÎ´ÉÈñµÚ¤Ó°ìÈÌ´ÉÍýÈñ·×
                    $input_data[$i][0] = $lh_han_all;               // ¥ê¥Ë¥¢É¸½à
                    $input_data[$i][1] = $b_han_all;                // µ¡¹©
                break;
                case 11:                                            // ±Ä¶ÈÍø±×
                    $input_data[$i][0] = $lh_ope_profit;            // ¥ê¥Ë¥¢É¸½à
                    $input_data[$i][1] = $b_ope_profit;             // µ¡¹©
                break;
                case 12:                                            // ¶ÈÌ³°ÑÂ÷¼ýÆþ
                    $input_data[$i][0] = $lh_gyoumu;                // ¥ê¥Ë¥¢É¸½à
                    $input_data[$i][1] = $b_gyoumu;                 // µ¡¹©
                break;
                case 13:                                            // »ÅÆþ³ä°ú
                    $input_data[$i][0] = $lh_swari;                 // ¥ê¥Ë¥¢É¸½à
                    $input_data[$i][1] = $b_swari;                  // µ¡¹©
                break;
                case 14:                                            // ±Ä¶È³°¼ý±×¤½¤ÎÂ¾
                    $input_data[$i][0] = $lh_pother;                // ¥ê¥Ë¥¢É¸½à
                    $input_data[$i][1] = $b_pother;                 // µ¡¹©
                break;
                case 15:                                            // ±Ä¶È³°¼ý±×·×
                    $input_data[$i][0] = $lh_nonope_profit_sum;     // ¥ê¥Ë¥¢É¸½à
                    $input_data[$i][1] = $b_nonope_profit_sum;      // µ¡¹©
                break;
                case 16:                                            // »ÙÊ§ÍøÂ©
                    $input_data[$i][0] = $lh_srisoku;               // ¥ê¥Ë¥¢É¸½à
                    $input_data[$i][1] = $b_srisoku;                // µ¡¹©
                break;
                case 17:                                            // ±Ä¶È³°ÈñÍÑ¤½¤ÎÂ¾
                    $input_data[$i][0] = $lh_lother;                // ¥ê¥Ë¥¢É¸½à
                    $input_data[$i][1] = $b_lother;                 // µ¡¹©
                break;
                case 18:                                            // ±Ä¶È³°ÈñÍÑ·×
                    $input_data[$i][0] = $lh_nonope_loss_sum;       // ¥ê¥Ë¥¢É¸½à
                    $input_data[$i][1] = $b_nonope_loss_sum;        // µ¡¹©
                break;
                case 19:                                            // ·Ð¾ïÍø±×
                    $input_data[$i][0] = $lh_current_profit;        // ¥ê¥Ë¥¢É¸½à
                    $input_data[$i][1] = $b_current_profit;         // µ¡¹©
                break;
                default:
                break;
            }
    }
    // ¥ê¥Ë¥¢É¸½àÅÐÏ¿
    $head  = "¥ê¥Ë¥¢É¸½à";
    $sec   = 0;
    insert_date($head,$item,$yyyymm,$input_data,$sec);
    // µ¡¹©ÅÐÏ¿
    $head  = "µ¡¹©";
    $sec   = 1;
    insert_date($head,$item,$yyyymm,$input_data,$sec);
}
function insert_date($head,$item,$yyyymm,$input_data,$sec) 
{
    for ($i = 0; $i < 20; $i++) {
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
                    <td colspan='4' align='center' class='pt10b' bgcolor='#ffffc6'>¥ê¡¡¥Ë¡¡¥¢</td>
                    <td colspan='4' align='center' class='pt10b' bgcolor='#ffffc6'>¥Ä¡¡¡¼¡¡¥ë</td>
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
                </tr>
                <tr>
                    <td rowspan='11' width='10' align='center' valign='middle' class='pt10b' bgcolor='#ceffce'>±Ä¡¡¶È¡¡Â»¡¡±×</td>
                    <td colspan='2' nowrap align='center' class='pt10b' bgcolor='#ceffce'>Çä¡¡¾å¡¡¹â</td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo $p2_lh_uri ?>   </td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo $p1_lh_uri ?>   </td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo $lh_uri ?>      </td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo $rui_lh_uri ?>  </td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo $p2_b_uri ?>   </td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo $p1_b_uri ?>   </td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo $b_uri ?>      </td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo $rui_b_uri ?>  </td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo $p2_l_uri ?>   </td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo $p1_l_uri ?>   </td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo $l_uri ?>      </td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo $rui_l_uri ?>  </td>
                    <td nowrap align='left'  class='pt10' bgcolor='#d6d3ce'>¼ÂºÝÇä¾å¹â</td>
                </tr>
                <tr>
                    <td rowspan='6' width='10' align='center' valign='middle' class='pt10b' bgcolor='#ffffc6'>Çä¾å¸¶²Á</td> <!-- Çä¾å¸¶²Á -->
                    <td nowrap align='left' class='pt10b' bgcolor='white'>¡¡´ü¼óºàÎÁ»Å³ÝÉÊÃª²·¹â</td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $p2_lh_invent ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $p1_lh_invent ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $lh_invent ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $rui_lh_invent ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $p2_b_invent ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $p1_b_invent ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $b_invent ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $rui_b_invent ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $p2_l_invent ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $p1_l_invent ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $l_invent ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $rui_l_invent ?></td>
                    <td nowrap align='left'  class='pt10'>É¸½à¸¶²Á¤Ë¤è¤ëÃª²·¹â</td>
                </tr>
                <tr>
                    <td nowrap align='left' class='pt10b' bgcolor='#e6e6e6'>¡¡ºàÎÁÈñ(»ÅÆþ¹â)</td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo $p2_lh_metarial ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo $p1_lh_metarial ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo $lh_metarial ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo $rui_lh_metarial ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo $p2_b_metarial ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo $p1_b_metarial ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo $b_metarial ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo $rui_b_metarial ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo $p2_l_metarial ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo $p1_l_metarial ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo $l_metarial ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo $rui_l_metarial ?></td>
                    <td nowrap align='left'  class='pt10'>Çã³Ý¹ØÆþ¹âÈæ</td>
                </tr>
                <tr>
                    <td nowrap align='left' class='pt10b' bgcolor='white'>¡¡Ï«¡¡¡¡Ì³¡¡¡¡Èñ</td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $p2_lh_roumu ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $p1_lh_roumu ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $lh_roumu ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $rui_lh_roumu ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $p2_b_roumu ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $p1_b_roumu ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $b_roumu ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $rui_b_roumu ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $p2_l_roumu ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $p1_l_roumu ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $l_roumu ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $rui_l_roumu ?></td>
                    <td nowrap align='left'  class='pt10'>¥µ¡¼¥Ó¥¹³ä¹çÈæµÚ¤ÓÁ°È¾´üÇä¾å¹âÈæ</td>
                </tr>
                <tr>
                    <td nowrap align='left' class='pt10b' bgcolor='#e6e6e6'>¡¡·Ð¡¡¡¡¡¡¡¡¡¡Èñ</td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo $p2_lh_expense ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo $p1_lh_expense ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo $lh_expense ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo $rui_lh_expense ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo $p2_b_expense ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo $p1_b_expense ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo $b_expense ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo $rui_b_expense ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo $p2_l_expense ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo $p1_l_expense ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo $l_expense ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo $rui_l_expense ?></td>
                    <td nowrap align='left'  class='pt10'>¥µ¡¼¥Ó¥¹³ä¹çÈæµÚ¤ÓÁ°È¾´üÇä¾å¹âÈæ</td>
                </tr>
                <tr>
                    <td nowrap align='left' class='pt10b' bgcolor='white'>¡¡´üËöºàÎÁ»Å³ÝÉÊÃª²·¹â</td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $p2_lh_endinv ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $p1_lh_endinv ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $lh_endinv ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $lh_endinv ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $p2_b_endinv ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $p1_b_endinv ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $b_endinv ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $b_endinv ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $p2_l_endinv ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $p1_l_endinv ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $l_endinv ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $l_endinv ?></td>
                    <td nowrap align='left'  class='pt10'>É¸½à¸¶²Á¤Ë¤è¤ëÃª²·¹â</td>
                </tr>
                <tr>
                    <td nowrap align='left' class='pt10b' bgcolor='#ffffc6'>¡¡Çä¡¡¾å¡¡¸¶¡¡²Á</td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo $p2_lh_urigen ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo $p1_lh_urigen ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo $lh_urigen ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo $rui_lh_urigen ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo $p2_b_urigen ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo $p1_b_urigen ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo $b_urigen ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo $rui_b_urigen ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo $p2_l_urigen ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo $p1_l_urigen ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo $l_urigen ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo $rui_l_urigen ?></td>
                    <td nowrap align='left'  class='pt10'>¡¡</td>
                </tr>
                <tr>
                    <td colspan='2' nowrap align='center' class='pt10b' bgcolor='#ceffce'>Çä¡¡¾å¡¡Áí¡¡Íø¡¡±×</td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo $p2_lh_gross_profit ?></td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo $p1_lh_gross_profit ?></td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo $lh_gross_profit ?></td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo $rui_lh_gross_profit ?></td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo $p2_b_gross_profit ?></td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo $p1_b_gross_profit ?></td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo $b_gross_profit ?></td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo $rui_b_gross_profit ?></td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo $p2_l_gross_profit ?></td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo $p1_l_gross_profit ?></td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo $l_gross_profit ?></td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo $rui_l_gross_profit ?></td>
                    <td nowrap align='left'  class='pt10' bgcolor='#d6d3ce'>¡¡</td>  <!-- Í¾Çò -->
                </tr>
                <tr>
                    <td rowspan='3' width='10' align='center' valign='middle' class='pt10b' bgcolor='#ffffc6'></td> <!-- ÈÎ´ÉÈñ -->
                    <td nowrap align='left' class='pt10b' bgcolor='#e6e6e6'>¡¡¿Í¡¡¡¡·ï¡¡¡¡Èñ</td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo $p2_lh_han_jin ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo $p1_lh_han_jin ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo $lh_han_jin ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo $rui_lh_han_jin ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo $p2_b_han_jin ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo $p1_b_han_jin ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo $b_han_jin ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo $rui_b_han_jin ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo $p2_l_han_jin ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo $p1_l_han_jin ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo $l_han_jin ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo $rui_l_han_jin ?></td>
                    <td nowrap align='left'  class='pt10'>ÉôÌç¿Í°÷ÈæÎ¨</td>
                </tr>
                <tr>
                    <td nowrap align='left' class='pt10b' bgcolor='white'>¡¡·Ð¡¡¡¡¡¡¡¡¡¡Èñ</td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $p2_lh_han_kei ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $p1_lh_han_kei ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $lh_han_kei ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $rui_lh_han_kei ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $p2_b_han_kei ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $p1_b_han_kei ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $b_han_kei ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $rui_b_han_kei ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $p2_l_han_kei ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $p1_l_han_kei ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $l_han_kei ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $rui_l_han_kei ?></td>
                    <td nowrap align='left'  class='pt10'>ÉôÌç¿Í°÷ÈæÎ¨</td>
                </tr>
                <tr>
                    <td nowrap align='center' class='pt10b' bgcolor='#ffffc6'>ÈÎ´ÉÈñµÚ¤Ó°ìÈÌ´ÉÍýÈñ·×</td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo $p2_lh_han_all ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo $p1_lh_han_all ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo $lh_han_all ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo $rui_lh_han_all ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo $p2_b_han_all ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo $p1_b_han_all ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo $b_han_all ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo $rui_b_han_all ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo $p2_l_han_all ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo $p1_l_han_all ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo $l_han_all ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo $rui_l_han_all ?></td>
                    <td nowrap align='left'  class='pt10'>¡¡</td>  <!-- Í¾Çò -->
                </tr>
                <tr>
                    <td colspan='3' nowrap align='center' class='pt10b' bgcolor='#ceffce'>±Ä¡¡¡¡¶È¡¡¡¡Íø¡¡¡¡±×</td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo $p2_lh_ope_profit ?></td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo $p1_lh_ope_profit ?></td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo $lh_ope_profit ?></td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo $rui_lh_ope_profit ?></td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo $p2_b_ope_profit ?></td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo $p1_b_ope_profit ?></td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo $b_ope_profit ?></td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo $rui_b_ope_profit ?></td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo $p2_l_ope_profit ?></td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo $p1_l_ope_profit ?></td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo $l_ope_profit ?></td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo $rui_l_ope_profit ?></td>
                    <td nowrap align='left'  class='pt10'>¡¡</td>  <!-- Í¾Çò -->
                </tr>
                <tr>
                    <td rowspan='7' align='center' valign='middle' class='pt10b' bgcolor='#ceffce'>±Ä¶È³°Â»±×</td>
                    <td rowspan='4' align='center' class='pt10' bgcolor='#ffffc6'></td> <!-- Í¾Çò -->
                    <td nowrap align='left' class='pt10b' bgcolor='white'>¡¡¶ÈÌ³°ÑÂ÷¼ýÆþ</td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $p2_lh_gyoumu ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $p1_lh_gyoumu ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $lh_gyoumu ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $rui_lh_gyoumu ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $p2_b_gyoumu ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $p1_b_gyoumu ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $b_gyoumu ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $rui_b_gyoumu ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $p2_l_gyoumu ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $p1_l_gyoumu ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $l_gyoumu ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $rui_l_gyoumu ?></td>
                    <td nowrap align='left'  class='pt10'>Á°È¾´ü¼ÂÀÓ¤ÎÇä¾å¹âÈæ</td>
                </tr>
                <tr>
                    <td nowrap align='left' class='pt10b' bgcolor='#e6e6e6'>¡¡»Å¡¡Æþ¡¡³ä¡¡°ú</td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo $p2_lh_swari ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo $p1_lh_swari ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo $lh_swari ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo $rui_lh_swari ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo $p2_b_swari ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo $p1_b_swari ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo $b_swari ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo $rui_b_swari ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo $p2_l_swari ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo $p1_l_swari ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo $l_swari ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo $rui_l_swari ?></td>
                    <td nowrap align='left'  class='pt10'>Á°È¾´ü¼ÂÀÓ¤ÎÇä¾å¹âÈæ</td>
                </tr>
                <tr>
                    <td nowrap align='left' class='pt10b' bgcolor='white'>¡¡¤½¡¡¡¡¤Î¡¡¡¡Â¾</td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $p2_lh_pother ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $p1_lh_pother ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $lh_pother ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $rui_lh_pother ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $p2_b_pother ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $p1_b_pother ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $b_pother ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $rui_b_pother ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $p2_l_pother ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $p1_l_pother ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $l_pother ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $rui_l_pother ?></td>
                    <td nowrap align='left'  class='pt10'>Á°È¾´ü¼ÂÀÓ¤ÎÇä¾å¹âÈæ</td>
                </tr>
                <tr>
                    <td nowrap align='left' class='pt10b' bgcolor='#ffffc6'>¡¡±Ä¶È³°¼ý±× ·×</td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo $p2_lh_nonope_profit_sum ?>   </td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo $p1_lh_nonope_profit_sum ?>   </td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo $lh_nonope_profit_sum ?>      </td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo $rui_lh_nonope_profit_sum ?>  </td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo $p2_b_nonope_profit_sum ?>   </td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo $p1_b_nonope_profit_sum ?>   </td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo $b_nonope_profit_sum ?>      </td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo $rui_b_nonope_profit_sum ?>  </td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo $p2_l_nonope_profit_sum ?>   </td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo $p1_l_nonope_profit_sum ?>   </td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo $l_nonope_profit_sum ?>      </td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo $rui_l_nonope_profit_sum ?>  </td>
                    <td nowrap align='left'  class='pt10'>¡¡</td> <!-- Í¾Çò -->
                </tr>
                <tr>
                    <td rowspan='3' align='center' class='pt10' bgcolor='#ffffc6'></td> <!-- Í¾Çò -->
                    <td nowrap align='left' class='pt10b' bgcolor='#e6e6e6'>¡¡»Ù¡¡Ê§¡¡Íø¡¡Â©</td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo $p2_lh_srisoku ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo $p1_lh_srisoku ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo $lh_srisoku ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo $rui_lh_srisoku ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo $p2_b_srisoku ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo $p1_b_srisoku ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo $b_srisoku ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo $rui_b_srisoku ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo $p2_l_srisoku ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo $p1_l_srisoku ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo $l_srisoku ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo $rui_l_srisoku ?></td>
                    <td nowrap align='left'  class='pt10'>Á°È¾´ü¼ÂÀÓ¤ÎÇä¾å¹âÈæ</td>
                </tr>
                <tr>
                    <td nowrap align='left' class='pt10b' bgcolor='white'>¡¡¤½¡¡¡¡¤Î¡¡¡¡Â¾</td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $p2_lh_lother ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $p1_lh_lother ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $lh_lother ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $rui_lh_lother ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $p2_b_lother ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $p1_b_lother ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $b_lother ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $rui_b_lother ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $p2_l_lother ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $p1_l_lother ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $l_lother ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $rui_l_lother ?></td>
                    <td nowrap align='left'  class='pt10'>Á°È¾´ü¼ÂÀÓ¤ÎÇä¾å¹âÈæ</td>
                </tr>
                <tr>
                    <td nowrap align='left' class='pt10b' bgcolor='#ffffc6'>¡¡±Ä¶È³°ÈñÍÑ ·×</td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo $p2_lh_nonope_loss_sum ?>   </td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo $p1_lh_nonope_loss_sum ?>   </td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo $lh_nonope_loss_sum ?>      </td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo $rui_lh_nonope_loss_sum ?>  </td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo $p2_b_nonope_loss_sum ?>   </td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo $p1_b_nonope_loss_sum ?>   </td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo $b_nonope_loss_sum ?>      </td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo $rui_b_nonope_loss_sum ?>  </td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo $p2_l_nonope_loss_sum ?>   </td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo $p1_l_nonope_loss_sum ?>   </td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo $l_nonope_loss_sum ?>      </td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo $rui_l_nonope_loss_sum ?>  </td>
                    <td nowrap align='left'  class='pt10'>¡¡</td> <!-- Í¾Çò -->
                </tr>
                <tr>
                    <td colspan='3' nowrap align='center' class='pt10b' bgcolor='#ceffce'>·Ð¡¡¡¡¾ï¡¡¡¡Íø¡¡¡¡±×</td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo $p2_lh_current_profit ?>   </td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo $p1_lh_current_profit ?>   </td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo $lh_current_profit ?>      </td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo $rui_lh_current_profit ?>  </td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo $p2_b_current_profit ?>   </td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo $p1_b_current_profit ?>   </td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo $b_current_profit ?>      </td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo $rui_b_current_profit ?>  </td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo $p2_l_current_profit ?>   </td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo $p1_l_current_profit ?>   </td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo $l_current_profit ?>      </td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo $rui_l_current_profit ?>  </td>
                    <td nowrap align='left'  class='pt10'>¡¡</td>  <!-- Í¾Çò -->
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
                            if ($comment_l != "") {
                                echo "<li><pre>$comment_l</pre></li>\n";
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
