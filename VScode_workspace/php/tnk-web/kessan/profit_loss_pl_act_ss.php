<?php
//////////////////////////////////////////////////////////////////////////////
// 月次損益関係 月次 試験修理 CL 損益計算書                                 //
// Copyright (C) 2016 -      Norihisa.Ohya norihisa_ooya@nitto-kohki.co.jp  //
// Changed history                                                          //
// 2016/07/25 Created   profit_loss_pl_act_ss.php                           //
// 2016/08/01 検索失敗時にエラーになるのを修正（0にする）                   //
//////////////////////////////////////////////////////////////////////////////
// ini_set('error_reporting', E_STRICT);       // E_STRICT=2048(php5) E_ALL=2047 debug 用
// ini_set('error_reporting', E_ALL);          // E_ALL='2047' debug 用
// ini_set('display_errors', '1');             // Error 表示 ON debug 用 リリース後コメント
session_start();                            // ini_set()の次に指定すること Script 最上行

require_once ('../function.php');           // define.php と pgsql.php を require_once している
require_once ('../tnk_func.php');           // TNK に依存する部分の関数を require_once している
require_once ('../MenuHeader.php');         // TNK 全共通 menu class
access_log();                               // Script Name は自動取得

///// TNK 共用メニュークラスのインスタンスを作成
$menu = new MenuHeader(0);                  // 認証チェック0=一般以上 戻り先=TOP_MENU タイトル未設定
   // 実際の認証はprofit_loss_submit.phpで行っているaccount_group_check()を使用

///// サイト設定
// $menu->set_site(10, 7);                  // site_index=10(損益メニュー) site_id=7(月次損益)
///// 表題の設定
$menu->set_caption('栃木日東工器(株)');
///// 呼出先のaction名とアドレス設定
$menu->set_action('特記事項入力',   PL . 'profit_loss_comment_put_ss.php');

///// 期・月の取得
$ki = Ym_to_tnk($_SESSION['pl_ym']);
$tuki = substr($_SESSION['pl_ym'],4,2);

///// タイトル名(ソースのタイトル名とフォームのタイトル名)
$menu->set_title("第 {$ki} 期　{$tuki} 月度　試験修理　ＣＬ 商 品 別 損 益 計 算 書");

///// 対象当月
$yyyymm = $_SESSION['pl_ym'];
///// 対象前月
if (substr($yyyymm,4,2)!=01) {
    $p1_ym = $yyyymm - 1;
} else {
    $p1_ym = $yyyymm - 100;
    $p1_ym = $p1_ym + 11;
}
///// 対象前々月
if (substr($p1_ym,4,2)!=01) {
    $p2_ym = $p1_ym - 1;
} else {
    $p2_ym = $p1_ym - 100;
    $p2_ym = $p2_ym + 11;
}
///// 期初年月の算出
$yyyy = substr($yyyymm, 0,4);
$mm   = substr($yyyymm, 4,2);
if (($mm >= 1) && ($mm <= 3)) {
    $yyyy = ($yyyy - 1);
}
$str_ym = $yyyy . "04";     // 期初年月

//対象年月日
$ymd_str = $yyyymm . "01";
$ymd_end = $yyyymm . "99";
//対象前年月日
$p1_ymd_str = $p1_ym . "01";
$p1_ymd_end = $p1_ym . "99";
//対象前々年月日
$p2_ymd_str = $p2_ym . "01";
$p2_ymd_end = $p2_ym . "99";
//期初年月日
$str_ymd = $str_ym . "01";

///// 表示単位を設定取得
if (isset($_POST['keihi_tani'])) {
    $_SESSION['keihi_tani'] = $_POST['keihi_tani'];
    $tani = $_SESSION['keihi_tani'];
} elseif (isset($_SESSION['keihi_tani'])) {
    $tani = $_SESSION['keihi_tani'];
} else {
    $tani = 1000;           // 初期値 表示単位 千円
    $_SESSION['keihi_tani'] = $tani;
}
///// 表示 小数部桁数 設定取得
if (isset($_POST['keihi_keta'])) {
    $_SESSION['keihi_keta'] = $_POST['keihi_keta'];
    $keta = $_SESSION['keihi_keta'];
} elseif (isset($_SESSION['keihi_keta'])) {
    $keta = $_SESSION['keihi_keta'];
} else {
    $keta = 0;              // 初期値 小数点以下桁数
    $_SESSION['keihi_keta'] = $keta;
}

/********** 売上高 **********/
    ///// 当月
$query = sprintf("select sum(Uround(数量*単価,0)) as t_kingaku from hiuuri where 計上日>=%d and 計上日<=%d and 事業部='L' and (assyno like 'SS%%')", $ymd_str, $ymd_end);
if (getUniResult($query, $st_uri) < 1) {
    $st_uri        = 0;     // 検索失敗
    $st_uri_sagaku = 0;
    $st_uri_temp   = 0;
} else {
    $st_uri_temp   = $st_uri;
    $st_uri_sagaku = $st_uri;
    $st_uri        = number_format(($st_uri / $tani), $keta);
}
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='カプラ試修売上高'", $yyyymm);
if (getUniResult($query, $sc_uri) < 1) {
    $sc_uri        = 0;     // 検索失敗
    $sc_uri_sagaku = 0;
    $sc_uri_temp   = 0;
} else {
    $sc_uri_temp   = $sc_uri;
    $sc_uri_sagaku = $sc_uri;
    $sc_uri        = number_format(($sc_uri / $tani), $keta);
}
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='試修売上高'", $yyyymm);
if (getUniResult($query, $s_uri) < 1) {
    $s_uri        = 0;     // 検索失敗
    $s_uri_sagaku = 0;
    $s_uri_temp   = 0;
    $sl_uri       = 0;     // 検索失敗
    $sl_uri_temp  = 0;
} else {
    $s_uri_temp = $s_uri;
    if ($yyyymm == 200906) {
        $s_uri = $s_uri - 3100900;
    } elseif ($yyyymm == 200905) {
        $s_uri = $s_uri + 1550450;
    } elseif ($yyyymm == 200904) {
        $s_uri = $s_uri + 1550450;
    }
    $s_uri_sagaku = $s_uri;
    $sl_uri       = $s_uri;
    $sl_uri_temp  = $sl_uri;
}
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='試修売上調整額'", $yyyymm);
if (getUniResult($query, $s_uri_cho) < 1) {
    // 検索失敗
    $s_uri        = number_format(($s_uri / $tani), $keta);
    $sl_uri       = number_format(($sl_uri / $tani), $keta);
    $ss_uri       = 0;
    $ss_uri_temp  = 0;
} else {
    $s_uri_sagaku = $s_uri_sagaku + $s_uri_cho;
    $s_uri_temp   = $s_uri_sagaku;
    $sl_uri       = $s_uri_temp;                             // リニア試験修理を保管
    $sl_uri_temp  = $sl_uri;                                 // リニア試験修理の損益計算用temp
    $s_uri        = $s_uri_sagaku + $sc_uri_sagaku;          // カプラ試修売上高を加味（tempの下 リニアからマイナスしてしまう為）
    
    // 耐久・修理計算(耐久st、修理ss)
    $ss_uri       = $s_uri - $st_uri_temp;
    $ss_uri_temp  = $ss_uri;
    if ($s_uri <> 0) {
        $st_uri_allo     = Uround(($st_uri_temp / $s_uri), 3);    // 耐久売上高率
        $ss_uri_allo     = 1 - $st_uri_allo;                      // 修理売上高率
    } else {
        $st_uri_allo = 0;
        $st_uri_allo = 0;
    }
    $s_uri        = number_format(($s_uri / $tani), $keta);
    $ss_uri       = number_format(($ss_uri / $tani), $keta);
}
    ///// 前月
$query = sprintf("select sum(Uround(数量*単価,0)) as t_kingaku from hiuuri where 計上日>=%d and 計上日<=%d and 事業部='L' and (assyno like 'SS%%')", $p1_ymd_str, $p1_ymd_end);
if (getUniResult($query, $p1_st_uri) < 1) {
    $p1_st_uri        = 0;     // 検索失敗
    $p1_st_uri_sagaku = 0;
    $p1_st_uri_temp   = 0;
} else {
    $p1_st_uri_temp   = $p1_st_uri;
    $p1_st_uri_sagaku = $p1_st_uri;
    $p1_st_uri        = number_format(($p1_st_uri / $tani), $keta);
}

$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='カプラ試修売上高'", $p1_ym);
if (getUniResult($query, $p1_sc_uri) < 1) {
    $p1_sc_uri        = 0;     // 検索失敗
    $p1_sc_uri_sagaku = 0;
    $p1_sc_uri_temp   = 0;
} else {
    $p1_sc_uri_temp   = $p1_sc_uri;
    $p1_sc_uri_sagaku = $p1_sc_uri;
    $p1_sc_uri        = number_format(($p1_sc_uri / $tani), $keta);
}

$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='試修売上高'", $p1_ym);
if (getUniResult($query, $p1_s_uri) < 1) {
    $p1_s_uri        = 0;  // 検索失敗
    $p1_s_uri_sagaku = 0;
    $p1_s_uri_temp   = 0;
    $p1_sl_uri       = 0;  // 検索失敗
    $p1_sl_uri_temp  = 0;
} else {
    $p1_s_uri_temp = $p1_s_uri;
    if ($p1_ym == 200906) {
        $p1_s_uri = $p1_s_uri - 3100900;
    } elseif ($p1_ym == 200905) {
        $p1_s_uri = $p1_s_uri + 1550450;
    } elseif ($p1_ym == 200904) {
        $p1_s_uri = $p1_s_uri + 1550450;
    }
    $p1_s_uri_sagaku = $p1_s_uri;
    $p1_sl_uri       = $p1_s_uri;
    $p1_sl_uri_temp  = $p1_sl_uri;
}
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='試修売上調整額'", $p1_ym);
if (getUniResult($query, $p1_s_uri_cho) < 1) {
    // 検索失敗
    $p1_s_uri  = number_format(($p1_s_uri / $tani), $keta);
    $p1_sl_uri = number_format(($p1_sl_uri / $tani), $keta);
    $p1_ss_uri       = 0;
    $p1_ss_uri_temp  = 0;
} else {
    $p1_s_uri_sagaku = $p1_s_uri_sagaku + $p1_s_uri_cho;
    $p1_s_uri_temp   = $p1_s_uri_sagaku;
    $p1_sl_uri       = $p1_s_uri_temp;                             // リニア試験修理を保管
    $p1_sl_uri_temp  = $p1_sl_uri;                                 // リニア試験修理の損益計算用temp
    $p1_s_uri        = $p1_s_uri_sagaku + $p1_sc_uri_sagaku;       // カプラ試修売上高を加味（tempの下 リニアからマイナスしてしまう為）
    
    // 耐久・修理計算(耐久st、修理ss)
    $p1_ss_uri       = $p1_s_uri - $p1_st_uri_temp;
    $p1_ss_uri_temp  = $p1_ss_uri;
    if ($p1_s_uri <> 0) {
        $p1_st_uri_allo     = Uround(($p1_st_uri_temp / $p1_s_uri), 3);     // 耐久売上高率
        $p1_ss_uri_allo     = 1 - $p1_st_uri_allo;                          // 修理売上高率
    } else {
        $p1_st_uri_allo = 0;
        $p1_st_uri_allo = 0;
    }
    $p1_s_uri        = number_format(($p1_s_uri / $tani), $keta);
    $p1_ss_uri       = number_format(($p1_ss_uri / $tani), $keta);
}
    ///// 前前月
$query = sprintf("select sum(Uround(数量*単価,0)) as t_kingaku from hiuuri where 計上日>=%d and 計上日<=%d and 事業部='L' and (assyno like 'SS%%')", $p2_ymd_str, $p2_ymd_end);
if (getUniResult($query, $p2_st_uri) < 1) {
    $p2_st_uri        = 0;     // 検索失敗
    $p2_st_uri_sagaku = 0;
    $p2_st_uri_temp   = 0;
} else {
    $p2_st_uri_temp   = $p2_st_uri;
    $p2_st_uri_sagaku = $p2_st_uri;
    $p2_st_uri        = number_format(($p2_st_uri / $tani), $keta);
}

$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='カプラ試修売上高'", $p2_ym);
if (getUniResult($query, $p2_sc_uri) < 1) {
    $p2_sc_uri        = 0;     // 検索失敗
    $p2_sc_uri_sagaku = 0;
    $p2_sc_uri_temp   = 0;
} else {
    $p2_sc_uri_temp   = $p2_sc_uri;
    $p2_sc_uri_sagaku = $p2_sc_uri;
    $p2_sc_uri        = number_format(($p2_sc_uri / $tani), $keta);
}
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='試修売上高'", $p2_ym);
if (getUniResult($query, $p2_s_uri) < 1) {
    $p2_s_uri        = 0;  // 検索失敗
    $p2_s_uri_sagaku = 0;
    $p2_s_uri_temp   = 0;
    $p2_sl_uri       = 0;  // 検索失敗
    $p2_sl_uri_temp  = 0;
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
    $p2_sl_uri       = $p2_s_uri;
    $p2_sl_uri_temp  = $p2_sl_uri;
}
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='試修売上調整額'", $p2_ym);
if (getUniResult($query, $p2_s_uri_cho) < 1) {
    // 検索失敗
    $p2_s_uri  = number_format(($p2_s_uri / $tani), $keta);
    $p2_sl_uri = number_format(($p2_sl_uri / $tani), $keta);
    $p2_ss_uri       = 0;
    $p2_ss_uri_temp  = 0;
} else {
    $p2_s_uri_sagaku = $p2_s_uri_sagaku + $p2_s_uri_cho;
    $p2_s_uri_temp   = $p2_s_uri_sagaku;
    $p2_sl_uri       = $p2_s_uri_temp;                             // リニア試験修理を保管
    $p2_sl_uri_temp  = $p2_sl_uri;                                 // リニア試験修理の損益計算用temp
    $p2_s_uri        = $p2_s_uri_sagaku + $p2_sc_uri_sagaku;       // カプラ試修売上高を加味（tempの下 リニアからマイナスしてしまう為）
    
    // 耐久・修理計算(耐久st、修理ss)
    $p2_ss_uri       = $p2_s_uri - $p2_st_uri_temp;
    $p2_ss_uri_temp  = $p2_ss_uri;
    if ($p2_s_uri <> 0) {
        $p2_st_uri_allo     = Uround(($p2_st_uri_temp / $p2_s_uri), 3);     // 耐久売上高率
        $p2_ss_uri_allo     = 1 - $p2_st_uri_allo;                          // 修理売上高率
    } else {
        $p2_st_uri_allo = 0;
        $p2_st_uri_allo = 0;
    }
    $p2_s_uri        = number_format(($p2_s_uri / $tani), $keta);
    $p2_ss_uri       = number_format(($p2_ss_uri / $tani), $keta);
}

    ///// 今期累計
$query = sprintf("select sum(Uround(数量*単価,0)) as t_kingaku from hiuuri where 計上日>=%d and 計上日<=%d and 事業部='L' and (assyno like 'SS%%')", $str_ymd, $ymd_end);
if (getUniResult($query, $rui_st_uri) < 1) {
    $rui_st_uri        = 0;     // 検索失敗
    $rui_st_uri_sagaku = 0;
    $rui_st_uri_temp   = 0;
} else {
    $rui_st_uri_temp   = $rui_st_uri;
    $rui_st_uri_sagaku = $rui_st_uri;
    $rui_st_uri        = number_format(($rui_st_uri / $tani), $keta);
}
$query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='カプラ試修売上高'", $str_ym, $yyyymm);
if (getUniResult($query, $rui_sc_uri) < 1) {
    $rui_sc_uri        = 0;     // 検索失敗
    $rui_sc_uri_sagaku = 0;
    $rui_sc_uri_temp   = 0;
} else {
    $rui_sc_uri_temp   = $rui_sc_uri;
    $rui_sc_uri_sagaku = $rui_sc_uri;
    $rui_sc_uri        = number_format(($rui_sc_uri / $tani), $keta);
}
$query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='試修売上高'", $str_ym, $yyyymm);
if (getUniResult($query, $rui_s_uri) < 1) {
    $rui_s_uri        = 0;     // 検索失敗
    $rui_s_uri_sagaku = 0;
    $rui_sl_uri       = 0;     // 検索失敗
    $rui_sl_uri_temp  = 0;
} else {
    $rui_s_uri_sagaku = $rui_s_uri;
    $rui_sl_uri       = $rui_s_uri;
    $rui_sl_uri_temp  = $rui_sl_uri;
}
$query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='試修売上調整額'", $str_ym, $yyyymm);
if (getUniResult($query, $rui_s_uri_cho) < 1) {
    // 検索失敗
    $rui_s_uri  = number_format(($rui_s_uri / $tani), $keta);
    $rui_sl_uri = number_format(($rui_sl_uri / $tani), $keta);
    $rui_ss_uri       = 0;
    $rui_ss_uri_temp  = 0;
} else {
    $rui_s_uri_sagaku = $rui_s_uri_sagaku + $rui_s_uri_cho;
    $rui_sl_uri       = $rui_s_uri_sagaku;                           // リニア試験修理を保管
    $rui_sl_uri_temp  = $rui_sl_uri;                                 // リニア試験修理の損益計算用temp
    $rui_s_uri        = $rui_s_uri_sagaku + $rui_sc_uri_sagaku;      // カプラ試修売上高を加味（tempの下 リニアからマイナスしてしまう為）
    
    // 耐久・修理計算(耐久st、修理ss)
    $rui_ss_uri       = $rui_s_uri - $rui_st_uri_temp;
    $rui_ss_uri_temp  = $rui_ss_uri;
    $rui_s_uri        = number_format(($rui_s_uri / $tani), $keta);
    $rui_ss_uri       = number_format(($rui_ss_uri / $tani), $keta);
}

/********** 期首材料仕掛品棚卸高 **********/
    ///// 試験・修理
$p2_s_invent   = 0;
$p1_s_invent   = 0;
$s_invent      = 0;
$rui_s_invent  = 0;
$p2_st_invent  = 0;
$p1_st_invent  = 0;
$st_invent     = 0;
$rui_st_invent = 0;
$p2_ss_invent  = 0;
$p1_ss_invent  = 0;
$ss_invent     = 0;
$rui_ss_invent = 0;

/********** 材料費(仕入高) **********/
    ///// 当月
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='カプラ試修材料費'", $yyyymm);
if (getUniResult($query, $sc_metarial) < 1) {
    $sc_metarial        = 0;     // 検索失敗
    $sc_metarial_sagaku = 0;
    $sc_metarial_temp   = 0;
} else {
    $sc_metarial_temp   = $sc_metarial;
    $sc_metarial_sagaku = $sc_metarial;
    $sc_metarial        = number_format(($sc_metarial / $tani), $keta);
}
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='試修材料費'", $yyyymm);
if (getUniResult($query, $s_metarial) < 1) {
    $s_metarial        = 0;   // 検索失敗
    $s_metarial_sagaku = 0;
    $sl_metarial       = 0;
    $ss_metarial       = 0;
    $ss_metarial_temp  = 0;
    $st_metarial       = 0;
    $st_metarial_temp  = 0;
} else {
    $s_metarial_sagaku = $s_metarial;
    $sl_metarial       = $s_metarial;                                  // リニア試験修理材料費を保管
    $sl_metarial_temp  = $sl_metarial;                                 // リニア試験修理損益計算用（temp)
    $s_metarial        = $s_metarial + $sc_metarial_sagaku;            // カプラ試修材料費を加味（sagakuの下 リニアからマイナスしてしまう為）
    
    // 耐久・修理計算(耐久st、修理ss) カプラ試修材料費は修理な為、そのまま移行
    $ss_metarial       = $sc_metarial_temp;
    $st_metarial       = $s_metarial - $ss_metarial;
    $ss_metarial_temp  = $ss_metarial;
    $st_metarial_temp  = $st_metarial;
    $s_metarial        = number_format(($s_metarial / $tani), $keta);
    $ss_metarial       = number_format(($ss_metarial / $tani), $keta);
    $st_metarial       = number_format(($st_metarial / $tani), $keta);
}
    ///// 前月
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='カプラ試修材料費'", $p1_ym);
if (getUniResult($query, $p1_sc_metarial) < 1) {
    $p1_sc_metarial        = 0;     // 検索失敗
    $p1_sc_metarial_sagaku = 0;
    $p1_sc_metarial_temp   = 0;
} else {
    $p1_sc_metarial_temp   = $p1_sc_metarial;
    $p1_sc_metarial_sagaku = $p1_sc_metarial;
    $p1_sc_metarial        = number_format(($p1_sc_metarial / $tani), $keta);
}
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='試修材料費'", $p1_ym);
if (getUniResult($query, $p1_s_metarial) < 1) {
    $p1_s_metarial        = 0;   // 検索失敗
    $p1_s_metarial_sagaku = 0;
    $p1_sl_metarial       = 0;
    $p1_st_metarial       = 0;
    $p1_ss_metarial_temp  = 0;
    $p1_st_metarial_temp  = 0;
} else {
    $p1_s_metarial_sagaku = $p1_s_metarial;
    $p1_sl_metarial       = $p1_s_metarial;                                  // リニア試験修理材料費を保管
    $p1_sl_metarial_temp  = $p1_sl_metarial;                                 // リニア試験修理損益計算用（temp)
    $p1_s_metarial        = $p1_s_metarial + $p1_sc_metarial_sagaku;         // カプラ試修材料費を加味（sagakuの下 リニアからマイナスしてしまう為）
    
    // 耐久・修理計算(耐久st、修理ss) カプラ試修材料費は修理な為、そのまま移行
    $p1_ss_metarial       = $p1_sc_metarial_temp;
    $p1_st_metarial       = $p1_s_metarial - $p1_ss_metarial;
    $p1_ss_metarial_temp  = $p1_ss_metarial;
    $p1_st_metarial_temp  = $p1_st_metarial;
    $p1_s_metarial        = number_format(($p1_s_metarial / $tani), $keta);
    $p1_ss_metarial       = number_format(($p1_ss_metarial / $tani), $keta);
    $p1_st_metarial       = number_format(($p1_st_metarial / $tani), $keta);
}
    ///// 前前月
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='カプラ試修材料費'", $p2_ym);
if (getUniResult($query, $p2_sc_metarial) < 1) {
    $p2_sc_metarial        = 0;     // 検索失敗
    $p2_sc_metarial_sagaku = 0;
    $p2_sc_metarial_temp   = 0;
} else {
    $p2_sc_metarial_temp   = $p2_sc_metarial;
    $p2_sc_metarial_sagaku = $p2_sc_metarial;
    $p2_sc_metarial        = number_format(($p2_sc_metarial / $tani), $keta);
}
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='試修材料費'", $p2_ym);
if (getUniResult($query, $p2_s_metarial) < 1) {
    $p2_s_metarial        = 0;   // 検索失敗
    $p2_s_metarial_sagaku = 0;
    $p2_sl_metarial       = 0;
    $p2_st_metarial       = 0;
    $p2_ss_metarial_temp  = 0;
    $p2_st_metarial_temp  = 0;
} else {
    $p2_s_metarial_sagaku = $p2_s_metarial;
    $p2_sl_metarial       = $p2_s_metarial;                                  // リニア試験修理材料費を保管
    $p2_sl_metarial_temp  = $p2_sl_metarial;                                 // リニア試験修理損益計算用（temp)
    $p2_s_metarial        = $p2_s_metarial + $p2_sc_metarial_sagaku;         // カプラ試修材料費を加味（sagakuの下 リニアからマイナスしてしまう為）
    
    // 耐久・修理計算(耐久st、修理ss) カプラ試修材料費は修理な為、そのまま移行
    $p2_ss_metarial       = $p2_sc_metarial_temp;
    $p2_st_metarial       = $p2_s_metarial - $p2_ss_metarial;
    $p2_ss_metarial_temp  = $p2_ss_metarial;
    $p2_st_metarial_temp  = $p2_st_metarial;
    $p2_s_metarial        = number_format(($p2_s_metarial / $tani), $keta);
    $p2_ss_metarial       = number_format(($p2_ss_metarial / $tani), $keta);
    $p2_st_metarial       = number_format(($p2_st_metarial / $tani), $keta);
}
    ///// 今期累計
$query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='カプラ試修材料費'", $str_ym, $yyyymm);
if (getUniResult($query, $rui_sc_metarial) < 1) {
    $rui_sc_metarial        = 0;     // 検索失敗
    $rui_sc_metarial_sagaku = 0;
    $rui_sc_metarial_temp   = 0;
} else {
    $rui_sc_metarial_temp   = $rui_sc_metarial;
    $rui_sc_metarial_sagaku = $rui_sc_metarial;
    $rui_sc_metarial        = number_format(($rui_sc_metarial / $tani), $keta);
}
$query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='試修材料費'", $str_ym, $yyyymm);
if (getUniResult($query, $rui_s_metarial) < 1) {
    $rui_s_metarial        = 0;   // 検索失敗
    $rui_s_metarial_sagaku = 0;
    $rui_sl_metarial       = 0;
    $rui_st_metarial       = 0;
    $rui_ss_metarial_temp  = 0;
    $rui_st_metarial_temp  = 0;
} else {
    $rui_s_metarial_sagaku = $rui_s_metarial;
    $rui_sl_metarial       = $rui_s_metarial;                                  // リニア試験修理材料費を保管
    $rui_sl_metarial_temp  = $rui_sl_metarial;                                 // リニア試験修理損益計算用（temp)
    $rui_s_metarial        = $rui_s_metarial + $rui_sc_metarial_sagaku;        // カプラ試修材料費を加味（sagakuの下 リニアからマイナスしてしまう為）
    
    // 耐久・修理計算(耐久st、修理ss) カプラ試修材料費は修理な為、そのまま移行
    $rui_ss_metarial       = $rui_sc_metarial_temp;
    $rui_st_metarial       = $rui_s_metarial - $rui_ss_metarial;
    $rui_ss_metarial_temp  = $rui_ss_metarial;
    $rui_st_metarial_temp  = $rui_st_metarial;
    $rui_s_metarial        = number_format(($rui_s_metarial / $tani), $keta);
    $rui_ss_metarial       = number_format(($rui_ss_metarial / $tani), $keta);
    $rui_st_metarial       = number_format(($rui_st_metarial / $tani), $keta);
}

/********** 労務費 **********/
    ///// 当月
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='カプラ試修労務費'", $yyyymm);
if (getUniResult($query, $sc_roumu) < 1) {
    $sc_roumu        = 0;     // 検索失敗
    $sc_roumu_sagaku = 0;
    $sc_roumu_temp   = 0;
} else {
    $sc_roumu_temp   = $sc_roumu;
    $sc_roumu_sagaku = $sc_roumu;
    if ($yyyymm == 200912) {
        $sc_roumu = $sc_roumu - 213810;
    }
    $sc_roumu        = number_format(($sc_roumu / $tani), $keta);
}
if ($yyyymm >= 201001) {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='試修給与配賦額'", $yyyymm);
    if (getUniResult($query, $s_kyu_kei) < 1) {
        $s_kyu_kei = 0;                    // 検索失敗
        $s_kyu_kin = 0;
    } else {
        $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='試修給与配賦率'", $yyyymm);
        if (getUniResult($query, $s_kyu_kin) < 1) {
            $s_kyu_kin = 0;
        }
    }
}
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='試修労務費'", $yyyymm);
if (getUniResult($query, $s_roumu) < 1) {
    $s_roumu         = 0;    // 検索失敗
    $s_roumu_sagaku  = 0;
    $sl_roumu        = 0;
    $sl_roumu_temp   = 0;
    $st_roumu        = 0;
    $st_roumu_temp   = 0;
    $ss_roumu        = 0;
    $ss_roumu_temp   = 0;
} else {
    if ($yyyymm >= 201001) {
        $s_roumu = $s_roumu - $s_kyu_kei + $s_kyu_kin;    // 試修配賦給与を加味
        //$s_roumu = $s_roumu - 432323 + 129697;  // 添田さんの給与をC・Lは35%。試験修理に30%振分
    }
    $s_roumu_sagaku  = $s_roumu;
    $sl_roumu        = $s_roumu - $sc_roumu_temp;                 // リニア試験修理労務費を計算
    $sl_roumu_temp   = $sl_roumu;                                 // リニア試験修理損益計算用（temp)
    if ($yyyymm == 200912) {
        $s_roumu = $s_roumu - 1409708;
    }
    if ($yyyymm == 200912) {
        $sl_roumu = $sl_roumu - 1195898;
    }
    
    // 耐久・修理計算(耐久st、修理ss)
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='修理労務費'", $yyyymm);
    if (getUniResult($query, $ss_roumu) < 1) {
        $ss_roumu      = 0;     // 検索失敗
    }
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='耐久労務費'", $yyyymm);
    if (getUniResult($query, $st_roumu) < 1) {
        $st_roumu      = 0;     // 検索失敗
    }
    // 配賦労務費差額計算
    $ss_roumu_temp  = $ss_roumu;
    $st_roumu_temp  = $st_roumu;
    $s_roumu        = number_format(($s_roumu / $tani), $keta);
    $ss_roumu       = number_format(($ss_roumu / $tani), $keta);
    $st_roumu       = number_format(($st_roumu / $tani), $keta);
}
    ///// 前月
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='カプラ試修労務費'", $p1_ym);
if (getUniResult($query, $p1_sc_roumu) < 1) {
    $p1_sc_roumu        = 0;     // 検索失敗
    $p1_sc_roumu_sagaku = 0;
    $p1_sc_roumu_temp   = 0;
} else {
    $p1_sc_roumu_temp   = $p1_sc_roumu;
    $p1_sc_roumu_sagaku = $p1_sc_roumu;
    if ($p1_ym == 200912) {
        $p1_sc_roumu = $p1_sc_roumu - 213810;
    }
    $p1_sc_roumu        = number_format(($p1_sc_roumu / $tani), $keta);
}
if ($p1_ym >= 201001) {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='試修給与配賦額'", $p1_ym);
    if (getUniResult($query, $p1_s_kyu_kei) < 1) {
        $p1_s_kyu_kei = 0;                    // 検索失敗
        $p1_s_kyu_kin = 0;
    } else {
        $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='試修給与配賦率'", $p1_ym);
        if (getUniResult($query, $p1_s_kyu_kin) < 1) {
            $p1_s_kyu_kin = 0;
        }
    }
}
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='試修労務費'", $p1_ym);
if (getUniResult($query, $p1_s_roumu) < 1) {
    $p1_s_roumu        = 0;    // 検索失敗
    $p1_s_roumu_sagaku = 0;
    $p1_sl_roumu       = 0;
    $p1_sl_roumu_temp  = 0;
    $p1_ss_roumu       = 0;
    $p1_ss_roumu_temp  = 0;
    $p1_st_roumu       = 0;
    $p1_st_roumu_temp  = 0;
} else {
    if ($p1_ym >= 201001) {
        $p1_s_roumu = $p1_s_roumu - $p1_s_kyu_kei + $p1_s_kyu_kin;    // 試修配賦給与を加味
        //$p1_s_roumu = $p1_s_roumu - 432323 + 129697;    // 添田さんの給与をC・Lは35%。試験修理に30%振分
    }
    $p1_s_roumu_sagaku = $p1_s_roumu;
    $p1_sl_roumu       = $p1_s_roumu - $p1_sc_roumu_temp;              // リニア試験修理材料費を計算
    $p1_sl_roumu_temp  = $p1_sl_roumu;                                 // リニア試験修理損益計算用（temp)
    if ($p1_ym == 200912) {
        $p1_s_roumu = $p1_s_roumu - 1409708;
    }
    if ($p1_ym == 200912) {
        $p1_sl_roumu = $p1_sl_roumu - 1195898;
    }
    
    // 耐久・修理計算(耐久st、修理ss)
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='修理労務費'", $p1_ym);
    if (getUniResult($query, $p1_ss_roumu) < 1) {
        $p1_ss_roumu        = 0;     // 検索失敗
    }
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='耐久労務費'", $p1_ym);
    if (getUniResult($query, $p1_st_roumu) < 1) {
        $p1_st_roumu        = 0;     // 検索失敗
    }
    // 配賦労務費差額計算
    $p1_ss_roumu_temp  = $p1_ss_roumu;
    $p1_st_roumu_temp  = $p1_st_roumu;
    $p1_s_roumu        = number_format(($p1_s_roumu / $tani), $keta);
    $p1_ss_roumu       = number_format(($p1_ss_roumu / $tani), $keta);
    $p1_st_roumu       = number_format(($p1_st_roumu / $tani), $keta);
}
    ///// 前前月
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='カプラ試修労務費'", $p2_ym);
if (getUniResult($query, $p2_sc_roumu) < 1) {
    $p2_sc_roumu        = 0;     // 検索失敗
    $p2_sc_roumu_sagaku = 0;
    $p2_sc_roumu_temp   = 0;
} else {
    $p2_sc_roumu_temp   = $p2_sc_roumu;
    $p2_sc_roumu_sagaku = $p2_sc_roumu;
    if ($p2_ym == 200912) {
        $p2_sc_roumu = $p2_sc_roumu - 213810;
    }
    $p2_sc_roumu        = number_format(($p2_sc_roumu / $tani), $keta);
}
if ($p2_ym >= 201001) {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='試修給与配賦額'", $p2_ym);
    if (getUniResult($query, $p2_s_kyu_kei) < 1) {
        $p2_s_kyu_kei = 0;                    // 検索失敗
        $p2_s_kyu_kin = 0;
    } else {
        $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='試修給与配賦率'", $p2_ym);
        if (getUniResult($query, $p2_s_kyu_kin) < 1) {
            $p2_s_kyu_kin = 0;
        }
    }
}
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='試修労務費'", $p2_ym);
if (getUniResult($query, $p2_s_roumu) < 1) {
    $p2_s_roumu         = 0;    // 検索失敗
    $p2_s_roumu_sagaku  = 0;
    $p2_sl_roumu        = 0;
    $p2_sl_roumu_temp   = 0;
    $p2_ss_roumu        = 0;
    $p2_ss_roumu_temp   = 0;
    $p2_st_roumu        = 0;
    $p2_st_roumu_temp   = 0;
} else {
    if ($p2_ym >= 201001) {
        $p2_s_roumu = $p2_s_roumu - $p2_s_kyu_kei + $p2_s_kyu_kin;    // 試修配賦給与を加味
        //$p2_s_roumu = $p2_s_roumu - 432323 + 129697;    // 添田さんの給与をC・Lは35%。試験修理に30%振分
    }
    $p2_s_roumu_sagaku  = $p2_s_roumu;
    $p2_sl_roumu        = $p2_s_roumu - $p2_sc_roumu_temp;              // リニア試験修理材料費を計算
    $p2_sl_roumu_temp   = $p2_sl_roumu;                                 // リニア試験修理損益計算用（temp)
    if ($p2_ym == 200912) {
        $p2_s_roumu = $p2_s_roumu - 1409708;
    }
    if ($p2_ym == 200912) {
        $p2_sl_roumu = $p2_sl_roumu - 1195898;
    }
    
    // 耐久・修理計算(耐久st、修理ss)
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='修理労務費'", $p2_ym);
    if (getUniResult($query, $p2_ss_roumu) < 1) {
        $p2_ss_roumu        = 0;     // 検索失敗
    }
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='耐久労務費'", $p2_ym);
    if (getUniResult($query, $p2_st_roumu) < 1) {
        $p2_st_roumu        = 0;     // 検索失敗
    }
    // 配賦労務費差額計算
    $p2_ss_roumu_temp  = $p2_ss_roumu;
    $p2_st_roumu_temp  = $p2_st_roumu;
    $p2_s_roumu        = number_format(($p2_s_roumu / $tani), $keta);
    $p2_ss_roumu       = number_format(($p2_ss_roumu / $tani), $keta);
    $p2_st_roumu       = number_format(($p2_st_roumu / $tani), $keta);
}
    ///// 今期累計
$query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='カプラ試修労務費'", $str_ym, $yyyymm);
if (getUniResult($query, $rui_sc_roumu) < 1) {
    $rui_sc_roumu        = 0;     // 検索失敗
    $rui_sc_roumu_sagaku = 0;
    $rui_sc_roumu_temp   = 0;
} else {
    $rui_sc_roumu_temp   = $rui_sc_roumu;
    $rui_sc_roumu_sagaku = $rui_sc_roumu;
    if ($yyyymm >= 200912 && $yyyymm <= 201003) {
        $rui_sc_roumu = $rui_sc_roumu - 213810;
    }
    $rui_sc_roumu        = number_format(($rui_sc_roumu / $tani), $keta);
}
if ($yyyymm >= 201001) {
    $query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='試修給与配賦額'", $str_ym, $yyyymm);
    if (getUniResult($query, $rui_s_kyu_kei) < 1) {
        $rui_s_kyu_kei = 0;                    // 検索失敗
        $rui_s_kyu_kin = 0;
    } else {
        $query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='試修給与配賦率'", $str_ym, $yyyymm);
        if (getUniResult($query, $rui_s_kyu_kin) < 1) {
            $rui_s_kyu_kin = 0;
        }
    }
}
$query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='試修労務費'", $str_ym, $yyyymm);
if (getUniResult($query, $rui_s_roumu) < 1) {
    $rui_s_roumu         = 0;    // 検索失敗
    $rui_s_roumu_sagaku  = 0;
    $rui_sl_roumu        = 0;
    $rui_sl_roumu_temp   = 0;
    $rui_ss_roumu        = 0;
    $rui_ss_roumu_temp   = 0;
    $rui_st_roumu        = 0;
    $rui_st_roumu_temp   = 0;
} else {
    if ($yyyymm >= 201001) {
        $rui_s_roumu = $rui_s_roumu - $rui_s_kyu_kei + $rui_s_kyu_kin;    // 試修配賦給与を加味
        //$rui_s_roumu = $rui_s_roumu - 432323 + 129697;  // 添田さんの給与をC・Lは35%。試験修理に30%振分
    }
    $rui_s_roumu_sagaku  = $rui_s_roumu;
    $rui_sl_roumu        = $rui_s_roumu - $rui_sc_roumu_temp;             // リニア試験修理材料費を計算
    $rui_sl_roumu_temp   = $rui_sl_roumu;                                 // リニア試験修理損益計算用（temp)
    if ($yyyymm >= 200912 && $yyyymm <= 201003) {
        $rui_s_roumu = $rui_s_roumu - 1409708;
    }
    if ($yyyymm >= 200912 && $yyyymm <= 201003) {
        $rui_sl_roumu = $rui_sl_roumu - 1195898;
    }
    
    // 耐久・修理計算(耐久st、修理ss)
    $query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='修理労務費'", $str_ym, $yyyymm);
    if (getUniResult($query, $rui_ss_roumu) < 1) {
        $rui_ss_roumu        = 0;     // 検索失敗
    }
    $query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='耐久労務費'", $str_ym, $yyyymm);
    if (getUniResult($query, $rui_st_roumu) < 1) {
        $rui_st_roumu        = 0;     // 検索失敗
    }
    // 配賦労務費差額計算
    $rui_ss_roumu_temp  = $rui_ss_roumu;
    $rui_st_roumu_temp  = $rui_st_roumu;
    $rui_s_roumu        = number_format(($rui_s_roumu / $tani), $keta);
    $rui_ss_roumu       = number_format(($rui_ss_roumu / $tani), $keta);
    $rui_st_roumu       = number_format(($rui_st_roumu / $tani), $keta);
}

/********** 経費(製造経費) **********/
    ///// 当月
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='カプラ試修製造経費'", $yyyymm);
if (getUniResult($query, $sc_expense) < 1) {
    $sc_expense        = 0;     // 検索失敗
    $sc_expense_sagaku = 0;
    $sc_expense_temp   = 0;
} else {
    $sc_expense_temp   = $sc_expense;
    $sc_expense_sagaku = $sc_expense;
    $sc_expense        = number_format(($sc_expense / $tani), $keta);
}
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='試修製造経費'", $yyyymm);
if (getUniResult($query, $s_expense) < 1) {
    $s_expense         = 0;    // 検索失敗
    $s_expense_sagaku  = 0;
    $sl_expense        = 0;
    $sl_expense_temp   = 0;
    $ss_expense        = 0;
    $ss_expense_temp   = 0;
    $st_expense        = 0;
    $st_expense_temp   = 0;
} else {
    $s_expense_sagaku  = $s_expense;
    $sl_expense        = $s_expense - $sc_expense_temp;               // リニア試験修理製造経費を計算
    $sl_expense_temp   = $sl_expense;                                 // リニア試験修理損益計算用（temp)
    
    // 耐久・修理計算(耐久st、修理ss)
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='修理製造経費'", $yyyymm);
    if (getUniResult($query, $ss_expense) < 1) {
        $ss_expense        = 0;     // 検索失敗
    }
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='耐久製造経費'", $yyyymm);
    if (getUniResult($query, $st_expense) < 1) {
        $st_expense        = 0;     // 検索失敗
    }
    // 配賦労務費差額計算
    $ss_expense_temp  = $ss_expense;
    $st_expense_temp  = $st_expense;
    $s_expense        = number_format(($s_expense / $tani), $keta);
    $ss_expense       = number_format(($ss_expense / $tani), $keta);
    $st_expense       = number_format(($st_expense / $tani), $keta);
}
    ///// 前月
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='カプラ試修製造経費'", $p1_ym);
if (getUniResult($query, $p1_sc_expense) < 1) {
    $p1_sc_expense        = 0;     // 検索失敗
    $p1_sc_expense_sagaku = 0;
    $p1_sc_expense_temp   = 0;
} else {
    $p1_sc_expense_temp   = $p1_sc_expense;
    $p1_sc_expense_sagaku = $p1_sc_expense;
    $p1_sc_expense        = number_format(($p1_sc_expense / $tani), $keta);
}
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='試修製造経費'", $p1_ym);
if (getUniResult($query, $p1_s_expense) < 1) {
    $p1_s_expense         = 0;    // 検索失敗
    $p1_s_expense_sagaku  = 0;
    $p1_sl_expense        = 0;
    $p1_sl_expense_temp   = 0;
    $p1_ss_expense        = 0;
    $p1_ss_expense_temp   = 0;
    $p1_st_expense        = 0;
    $p1_st_expense_temp   = 0;
} else {
    $p1_s_expense_sagaku  = $p1_s_expense;
    $p1_sl_expense        = $p1_s_expense - $p1_sc_expense_temp;            // リニア試験修理製造経費を計算
    $p1_sl_expense_temp   = $p1_sl_expense;                                 // リニア試験修理損益計算用（temp)
    
    // 耐久・修理計算(耐久st、修理ss)
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='修理製造経費'", $p1_ym);
    if (getUniResult($query, $p1_ss_expense) < 1) {
        $p1_ss_expense        = 0;     // 検索失敗
    }
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='耐久製造経費'", $p1_ym);
    if (getUniResult($query, $p1_st_expense) < 1) {
        $p1_st_expense        = 0;     // 検索失敗
    }
    // 配賦労務費差額計算
    $p1_ss_expense_temp  = $p1_ss_expense;
    $p1_st_expense_temp  = $p1_st_expense;
    $p1_s_expense        = number_format(($p1_s_expense / $tani), $keta);
    $p1_ss_expense       = number_format(($p1_ss_expense / $tani), $keta);
    $p1_st_expense       = number_format(($p1_st_expense / $tani), $keta);
}
    ///// 前前月
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='カプラ試修製造経費'", $p2_ym);
if (getUniResult($query, $p2_sc_expense) < 1) {
    $p2_sc_expense        = 0;     // 検索失敗
    $p2_sc_expense_sagaku = 0;
    $p2_sc_expense_temp   = 0;
} else {
    $p2_sc_expense_temp   = $p2_sc_expense;
    $p2_sc_expense_sagaku = $p2_sc_expense;
    $p2_sc_expense        = number_format(($p2_sc_expense / $tani), $keta);
}
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='試修製造経費'", $p2_ym);
if (getUniResult($query, $p2_s_expense) < 1) {
    $p2_s_expense         = 0;    // 検索失敗
    $p2_s_expense_sagaku  = 0;
    $p2_sl_expense        = 0;
    $p2_sl_expense_temp   = 0;
    $p2_ss_expense        = 0;
    $p2_ss_expense_temp   = 0;
    $p2_st_expense        = 0;
    $p2_st_expense_temp   = 0;
} else {
    $p2_s_expense_sagaku  = $p2_s_expense;
    $p2_sl_expense        = $p2_s_expense - $p2_sc_expense_temp;            // リニア試験修理製造経費を計算
    $p2_sl_expense_temp   = $p2_sl_expense;                                 // リニア試験修理損益計算用（temp)
    
    // 耐久・修理計算(耐久st、修理ss)
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='修理製造経費'", $p2_ym);
    if (getUniResult($query, $p2_ss_expense) < 1) {
        $p2_ss_expense        = 0;     // 検索失敗
    }
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='耐久製造経費'", $p2_ym);
    if (getUniResult($query, $p2_st_expense) < 1) {
        $p2_st_expense        = 0;     // 検索失敗
    }
    // 配賦労務費差額計算
    $p2_ss_expense_temp  = $p2_ss_expense;
    $p2_st_expense_temp  = $p2_st_expense;
    $p2_s_expense        = number_format(($p2_s_expense / $tani), $keta);
    $p2_ss_expense       = number_format(($p2_ss_expense / $tani), $keta);
    $p2_st_expense       = number_format(($p2_st_expense / $tani), $keta);
}
    ///// 今期累計
$query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='カプラ試修製造経費'", $str_ym, $yyyymm);
if (getUniResult($query, $rui_sc_expense) < 1) {
    $rui_sc_expense        = 0;     // 検索失敗
    $rui_sc_expense_sagaku = 0;
    $rui_sc_expense_temp   = 0;
} else {
    $rui_sc_expense_temp   = $rui_sc_expense;
    $rui_sc_expense_sagaku = $rui_sc_expense;
    $rui_sc_expense        = number_format(($rui_sc_expense / $tani), $keta);
}
$query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='試修製造経費'", $str_ym, $yyyymm);
if (getUniResult($query, $rui_s_expense) < 1) {
    $rui_s_expense         = 0;    // 検索失敗
    $rui_s_expense_sagaku  = 0;
    $rui_sl_expense        = 0;
    $rui_sl_expense_temp   = 0;
    $rui_ss_expense        = 0;
    $rui_ss_expense_temp   = 0;
    $rui_st_expense        = 0;
    $rui_st_expense_temp   = 0;
} else {
    $rui_s_expense_sagaku  = $rui_s_expense;
    $rui_sl_expense        = $rui_s_expense - $rui_sc_expense_temp;           // リニア試験修理製造経費を計算
    $rui_sl_expense_temp   = $rui_sl_expense;                                 // リニア試験修理損益計算用（temp)
    
    // 耐久・修理計算(耐久st、修理ss)
    $query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='修理製造経費'", $str_ym, $yyyymm);
    if (getUniResult($query, $rui_ss_expense) < 1) {
        $rui_ss_expense        = 0;     // 検索失敗
    }
    $query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='耐久製造経費'", $str_ym, $yyyymm);
    if (getUniResult($query, $rui_st_expense) < 1) {
        $rui_st_expense        = 0;     // 検索失敗
    }
    // 配賦労務費差額計算
    $rui_ss_expense_temp  = $rui_ss_expense;
    $rui_st_expense_temp  = $rui_st_expense;
    $rui_s_expense        = number_format(($rui_s_expense / $tani), $keta);
    $rui_ss_expense       = number_format(($rui_ss_expense / $tani), $keta);
    $rui_st_expense       = number_format(($rui_st_expense / $tani), $keta);
}

/********** 期末材料仕掛品棚卸高 **********/
    ///// 試験・修理
$p2_s_endinv  = 0;
$p1_s_endinv  = 0;
$s_endinv     = 0;
$p2_ss_endinv  = 0;
$p1_ss_endinv  = 0;
$ss_endinv     = 0;
$p2_st_endinv  = 0;
$p1_st_endinv  = 0;
$st_endinv     = 0;

$p2_sc_endinv = 0;
$p1_sc_endinv = 0;
$sc_endinv    = 0;
$p2_sl_endinv = 0;
$p1_sl_endinv = 0;
$sl_endinv    = 0;

/********** 売上原価 **********/
    ///// 当月
    ///// 試験・修理
    $s_urigen            = $s_invent + $s_metarial_sagaku + $s_roumu_sagaku + $s_expense_sagaku + $s_endinv;
    $s_urigen_sagaku     = $s_urigen;
    $sc_urigen           = $sc_metarial_temp + $sc_roumu_temp + $sc_expense_temp;             // カプラ試験修理売上原価の計算
    $sc_urigen_temp      = $sc_urigen;                                                        // カプラ試験修理損益計算用(temp)
    $sl_urigen           = $s_urigen - $sc_urigen;                                            // リニア試験修理売上原価の計算
    $sl_urigen_temp      = $sl_urigen;                                                        // カプラ試験修理損益計算用(temp)
    $s_urigen            = $s_urigen + $sc_metarial_sagaku;                                   // カプラ試修材料費を加味（sagakuの下 リニアからマイナスしてしまう為）
    if ($yyyymm == 200912) {
        $s_urigen = $s_urigen - 1409708;
    }
    if ($yyyymm == 200912) {
        $sc_urigen = $sc_urigen - 213810;
    }
    if ($yyyymm == 200912) {
        $sl_urigen = $sl_urigen - 1195898;
    }
    
    // 耐久・修理計算(耐久st、修理ss)
    $ss_urigen      = $ss_invent + $ss_metarial_temp + $ss_roumu_temp + $ss_expense_temp + $ss_endinv;
    $ss_urigen_temp = $ss_urigen;
    $st_urigen      = $st_invent + $st_metarial_temp + $st_roumu_temp + $st_expense_temp + $st_endinv;
    $st_urigen_temp = $st_urigen;
        
    $s_urigen       = number_format(($s_urigen / $tani), $keta);
    $ss_urigen      = number_format(($ss_urigen / $tani), $keta);
    $st_urigen      = number_format(($st_urigen / $tani), $keta);
    ///// 前月
    ///// 試験・修理
    $p1_s_urigen         = $p1_s_invent + $p1_s_metarial_sagaku + $p1_s_roumu_sagaku + $p1_s_expense_sagaku + $p1_s_endinv;
    $p1_s_urigen_sagaku  = $p1_s_urigen;
    $p1_sc_urigen        = $p1_sc_metarial_temp + $p1_sc_roumu_temp + $p1_sc_expense_temp;    // カプラ試験修理売上原価の計算
    $p1_sc_urigen_temp   = $p1_sc_urigen;                                                     // カプラ試験修理損益計算用(temp)
    $p1_sl_urigen        = $p1_s_urigen - $p1_sc_urigen;                                      // リニア試験修理売上原価の計算
    $p1_sl_urigen_temp   = $p1_sl_urigen;                                                     // カプラ試験修理損益計算用(temp)
    $p1_s_urigen         = $p1_s_urigen + $p1_sc_metarial_sagaku;                             // カプラ試修材料費を加味（sagakuの下 リニアからマイナスしてしまう為）
    if ($p1_ym == 200912) {
        $p1_s_urigen = $p1_s_urigen - 1409708;
    }
    if ($p1_ym == 200912) {
        $p1_sc_urigen = $p1_sc_urigen - 213810;
    }
    if ($p1_ym == 200912) {
        $p1_sl_urigen = $p1_sl_urigen - 1195898;
    }
    
    // 耐久・修理計算(耐久st、修理ss)
    $p1_ss_urigen      = $p1_ss_invent + $p1_ss_metarial_temp + $p1_ss_roumu_temp + $p1_ss_expense_temp + $p1_ss_endinv;
    $p1_ss_urigen_temp = $p1_ss_urigen;
    $p1_st_urigen      = $p1_st_invent + $p1_st_metarial_temp + $p1_st_roumu_temp + $p1_st_expense_temp + $p1_st_endinv;
    $p1_st_urigen_temp = $p1_st_urigen;
        
    $p1_s_urigen       = number_format(($p1_s_urigen / $tani), $keta);
    $p1_ss_urigen      = number_format(($p1_ss_urigen / $tani), $keta);
    $p1_st_urigen      = number_format(($p1_st_urigen / $tani), $keta);
    ///// 前前月
    ///// 試験・修理
    $p2_s_urigen         = $p2_s_invent + $p2_s_metarial_sagaku + $p2_s_roumu_sagaku + $p2_s_expense_sagaku + $p2_s_endinv;
    $p2_s_urigen_sagaku  = $p2_s_urigen;
    $p2_sc_urigen        = $p2_sc_metarial_temp + $p2_sc_roumu_temp + $p2_sc_expense_temp;    // カプラ試験修理売上原価の計算
    $p2_sc_urigen_temp   = $p2_sc_urigen;                                                     // カプラ試験修理損益計算用(temp)
    $p2_sl_urigen        = $p2_s_urigen - $p2_sc_urigen;                                      // リニア試験修理売上原価の計算
    $p2_sl_urigen_temp   = $p2_sl_urigen;                                                     // カプラ試験修理損益計算用(temp)
    $p2_s_urigen         = $p2_s_urigen + $p2_sc_metarial_sagaku;                             // カプラ試修材料費を加味（sagakuの下 リニアからマイナスしてしまう為）
    if ($p2_ym == 200912) {
        $p2_s_urigen = $p2_s_urigen - 1409708;
    }
    if ($p2_ym == 200912) {
        $p2_sc_urigen = $p2_sc_urigen - 213810;
    }
    if ($p2_ym == 200912) {
        $p2_sl_urigen = $p2_sl_urigen - 1195898;
    }
    
    // 耐久・修理計算(耐久st、修理ss)
    $p2_ss_urigen      = $p2_ss_invent + $p2_ss_metarial_temp + $p2_ss_roumu_temp + $p2_ss_expense_temp + $p2_ss_endinv;
    $p2_ss_urigen_temp = $p2_ss_urigen;
    $p2_st_urigen      = $p2_st_invent + $p2_st_metarial_temp + $p2_st_roumu_temp + $p2_st_expense_temp + $p2_st_endinv;
    $p2_st_urigen_temp = $p2_st_urigen;
        
    $p2_s_urigen       = number_format(($p2_s_urigen / $tani), $keta);
    $p2_ss_urigen      = number_format(($p2_ss_urigen / $tani), $keta);
    $p2_st_urigen      = number_format(($p2_st_urigen / $tani), $keta);
    ///// 今期累計
    ///// 試験・修理
    $rui_s_urigen        = $rui_s_invent + $rui_s_metarial_sagaku + $rui_s_roumu_sagaku + $rui_s_expense_sagaku + $s_endinv;
    $rui_s_urigen_sagaku = $rui_s_urigen;
    $rui_sc_urigen       = $rui_sc_metarial_temp + $rui_sc_roumu_temp + $rui_sc_expense_temp; // カプラ試験修理売上原価の計算
    $rui_sc_urigen_temp  = $rui_sc_urigen;                                                    // カプラ試験修理損益計算用(temp)
    $rui_sl_urigen       = $rui_s_urigen - $rui_sc_urigen;                                    // リニア試験修理売上原価の計算
    $rui_sl_urigen_temp  = $rui_sl_urigen;                                                    // カプラ試験修理損益計算用(temp)
    $rui_s_urigen        = $rui_s_urigen + $rui_sc_metarial_sagaku;                           // カプラ試修材料費を加味（sagakuの下 リニアからマイナスしてしまう為）
    if ($yyyymm >= 200912 && $yyyymm <= 201003) {
        $rui_s_urigen = $rui_s_urigen - 1409708;
    }
    if ($yyyymm >= 200912 && $yyyymm <= 201003) {
        $rui_sc_urigen = $rui_sc_urigen - 213810;
    }
    if ($yyyymm >= 200912 && $yyyymm <= 201003) {
        $rui_sl_urigen = $rui_sl_urigen - 1195898;
    }
    
    // 耐久・修理計算(耐久st、修理ss)
    $rui_ss_urigen      = $rui_ss_invent + $rui_ss_metarial_temp + $rui_ss_roumu_temp + $rui_ss_expense_temp + $ss_endinv;
    $rui_ss_urigen_temp = $rui_ss_urigen;
    $rui_st_urigen      = $rui_st_invent + $rui_st_metarial_temp + $rui_st_roumu_temp + $rui_st_expense_temp + $st_endinv;
    $rui_st_urigen_temp = $rui_st_urigen;
        
    $rui_s_urigen       = number_format(($rui_s_urigen / $tani), $keta);
    $rui_ss_urigen      = number_format(($rui_ss_urigen / $tani), $keta);
    $rui_st_urigen      = number_format(($rui_st_urigen / $tani), $keta);

/********** 売上総利益 **********/
    ///// 試験・修理
$p2_s_gross_profit         = $p2_s_uri_sagaku - $p2_s_urigen_sagaku;
$p2_s_gross_profit_sagaku  = $p2_s_gross_profit;
$p2_sc_gross_profit        = $p2_sc_uri_temp - $p2_sc_urigen_temp;      // カプラ試修売上総利益計算
$p2_sc_gross_profit_temp   = $p2_sc_gross_profit;                       // カプラ試修売上総利益損益計算用(temp)
$p2_sl_gross_profit        = $p2_sl_uri_temp - $p2_sl_urigen_temp;      // リニア試修売上総利益計算
$p2_sl_gross_profit_temp   = $p2_sl_gross_profit;                       // リニア試修売上総利益損益計算用(temp)
$p2_s_gross_profit         = $p2_s_gross_profit + $p2_sc_uri_sagaku - $p2_sc_metarial_sagaku;   // カプラ試修売上高を加味（sagakuの下 リニアからマイナスしてしまう為）
if ($p2_ym == 200912) {
    $p2_s_gross_profit = $p2_s_gross_profit + 1409708;
}
if ($p2_ym == 200912) {
    $p2_sl_gross_profit = $p2_sl_gross_profit + 1195898;
}
if ($p2_ym == 200912) {
    $p2_sc_gross_profit = $p2_sc_gross_profit + 213810;
}

// 耐久・修理計算(耐久st、修理ss)
$p2_ss_gross_profit      = $p2_ss_uri_temp - $p2_ss_urigen_temp;
$p2_ss_gross_profit_temp = $p2_ss_gross_profit;
$p2_st_gross_profit      = $p2_st_uri_temp - $p2_st_urigen_temp;
$p2_st_gross_profit_temp = $p2_st_gross_profit;

$p2_s_gross_profit            = number_format(($p2_s_gross_profit / $tani), $keta);
$p2_ss_gross_profit           = number_format(($p2_ss_gross_profit / $tani), $keta);
$p2_st_gross_profit           = number_format(($p2_st_gross_profit / $tani), $keta);

$p1_s_gross_profit         = $p1_s_uri_sagaku - $p1_s_urigen_sagaku;
$p1_s_gross_profit_sagaku  = $p1_s_gross_profit;
$p1_sc_gross_profit        = $p1_sc_uri_temp - $p1_sc_urigen_temp;      // カプラ試修売上総利益計算
$p1_sc_gross_profit_temp   = $p1_sc_gross_profit;                       // カプラ試修売上総利益損益計算用(temp)
$p1_sl_gross_profit        = $p1_sl_uri_temp - $p1_sl_urigen_temp;      // リニア試修売上総利益計算
$p1_sl_gross_profit_temp   = $p1_sl_gross_profit;                       // リニア試修売上総利益損益計算用(temp)
$p1_s_gross_profit         = $p1_s_gross_profit + $p1_sc_uri_sagaku - $p1_sc_metarial_sagaku;   // カプラ試修売上高を加味（sagakuの下 リニアからマイナスしてしまう為）
if ($p1_ym == 200912) {
    $p1_s_gross_profit = $p1_s_gross_profit + 1409708;
}
if ($p1_ym == 200912) {
    $p1_sl_gross_profit = $p1_sl_gross_profit + 1195898;
}
if ($p1_ym == 200912) {
    $p1_sc_gross_profit = $p1_sc_gross_profit + 213810;
}

// 耐久・修理計算(耐久st、修理ss)
$p1_ss_gross_profit      = $p1_ss_uri_temp - $p1_ss_urigen_temp;
$p1_ss_gross_profit_temp = $p1_ss_gross_profit;
$p1_st_gross_profit      = $p1_st_uri_temp - $p1_st_urigen_temp;
$p1_st_gross_profit_temp = $p1_st_gross_profit;

$p1_s_gross_profit            = number_format(($p1_s_gross_profit / $tani), $keta);
$p1_ss_gross_profit           = number_format(($p1_ss_gross_profit / $tani), $keta);
$p1_st_gross_profit           = number_format(($p1_st_gross_profit / $tani), $keta);

$s_gross_profit            = $s_uri_sagaku - $s_urigen_sagaku;
$s_gross_profit_sagaku     = $s_gross_profit;
$sc_gross_profit           = $sc_uri_temp - $sc_urigen_temp;            // カプラ試修売上総利益計算
$sc_gross_profit_temp      = $sc_gross_profit;                          // カプラ試修売上総利益損益計算用(temp)
$sl_gross_profit           = $sl_uri_temp - $sl_urigen_temp;            // リニア試修売上総利益計算
$sl_gross_profit_temp      = $sl_gross_profit;                          // リニア試修売上総利益損益計算用(temp)
$s_gross_profit            = $s_gross_profit + $sc_uri_sagaku - $sc_metarial_sagaku;   // カプラ試修売上高を加味（sagakuの下 リニアからマイナスしてしまう為）
if ($yyyymm == 200912) {
    $s_gross_profit = $s_gross_profit + 1409708;
}
if ($yyyymm == 200912) {
    $sc_gross_profit = $sc_gross_profit + 213810;
}
if ($yyyymm == 200912) {
    $sl_gross_profit = $sl_gross_profit + 1195898;
}

// 耐久・修理計算(耐久st、修理ss)
$ss_gross_profit      = $ss_uri_temp - $ss_urigen_temp;
$ss_gross_profit_temp = $ss_gross_profit;
$st_gross_profit      = $st_uri_temp - $st_urigen_temp;
$st_gross_profit_temp = $st_gross_profit;

$s_gross_profit            = number_format(($s_gross_profit / $tani), $keta);
$ss_gross_profit           = number_format(($ss_gross_profit / $tani), $keta);
$st_gross_profit           = number_format(($st_gross_profit / $tani), $keta);

$rui_s_gross_profit        = $rui_s_uri_sagaku - $rui_s_urigen_sagaku;
$rui_s_gross_profit_sagaku = $rui_s_gross_profit;
$rui_sc_gross_profit       = $rui_sc_uri_temp - $rui_sc_urigen_temp;    // カプラ試修売上総利益計算
$rui_sc_gross_profit_temp  = $rui_sc_gross_profit;                      // カプラ試修売上総利益損益計算用(temp)
$rui_sl_gross_profit       = $rui_sl_uri_temp - $rui_sl_urigen_temp;    // リニア試修売上総利益計算
$rui_sl_gross_profit_temp  = $rui_sl_gross_profit;                      // リニア試修売上総利益損益計算用(temp)
$rui_s_gross_profit        = $rui_s_gross_profit + $rui_sc_uri_sagaku - $rui_sc_metarial_sagaku;   // カプラ試修売上高を加味（sagakuの下 リニアからマイナスしてしまう為）
if ($yyyymm >= 200912 && $yyyymm <= 201003) {
    $rui_s_gross_profit = $rui_s_gross_profit + 1409708;
}
if ($yyyymm >= 200912 && $yyyymm <= 201003) {
    $rui_sc_gross_profit = $rui_sc_gross_profit + 213810;
}
if ($yyyymm >= 200912 && $yyyymm <= 201003) {
    $rui_sl_gross_profit = $rui_sl_gross_profit + 1195898;
}

// 耐久・修理計算(耐久st、修理ss)
$rui_ss_gross_profit      = $rui_ss_uri_temp - $rui_ss_urigen_temp;
$rui_ss_gross_profit_temp = $rui_ss_gross_profit;
$rui_st_gross_profit      = $rui_st_uri_temp - $rui_st_urigen_temp;
$rui_st_gross_profit_temp = $rui_st_gross_profit;

$rui_s_gross_profit            = number_format(($rui_s_gross_profit / $tani), $keta);
$rui_ss_gross_profit           = number_format(($rui_ss_gross_profit / $tani), $keta);
$rui_st_gross_profit           = number_format(($rui_st_gross_profit / $tani), $keta);

/********** 販管費の人件費 **********/
    ///// 当月
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='カプラ試修人件費'", $yyyymm);
if (getUniResult($query, $sc_han_jin) < 1) {
    $sc_han_jin        = 0;     // 検索失敗
    $sc_han_jin_sagaku = 0;
    $sc_han_jin_temp   = 0;
} else {
    $sc_han_jin_temp   = $sc_han_jin;
    $sc_han_jin_sagaku = $sc_han_jin;
    $sc_han_jin        = number_format(($sc_han_jin / $tani), $keta);
}
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='試修人件費'", $yyyymm);
if (getUniResult($query, $s_han_jin) < 1) {
    $s_han_jin         = 0;    // 検索失敗
    $s_han_jin_sagaku  = 0;
    $sl_han_jin        = 0;
    $sl_han_jin_temp   = 0;
    $ss_han_jin        = 0;
    $ss_han_jin_temp   = 0;
    $st_han_jin        = 0;
    $st_han_jin_temp   = 0;
} else {
    $s_han_jin_sagaku  = $s_han_jin;
    $sl_han_jin        = $s_han_jin - $sc_han_jin_temp;               // リニア試験修理人件費を計算
    $sl_han_jin_temp   = $sl_han_jin;                                 // リニア試験修理損益計算用（temp)
    
    // 耐久・修理計算(耐久st、修理ss)
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='修理人件費'", $yyyymm);
    if (getUniResult($query, $ss_han_jin) < 1) {
        $ss_han_jin        = 0;     // 検索失敗
    }
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='耐久人件費'", $yyyymm);
    if (getUniResult($query, $st_han_jin) < 1) {
        $st_han_jin        = 0;     // 検索失敗
    }
    // 配賦労務費差額計算
    $ss_han_jin_temp  = $ss_han_jin;
    $st_han_jin_temp  = $st_han_jin;
    $s_han_jin        = number_format(($s_han_jin / $tani), $keta);
    $ss_han_jin       = number_format(($ss_han_jin / $tani), $keta);
    $st_han_jin       = number_format(($st_han_jin / $tani), $keta);
}
    ///// 前月
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='カプラ試修人件費'", $p1_ym);
if (getUniResult($query, $p1_sc_han_jin) < 1) {
    $p1_sc_han_jin        = 0;     // 検索失敗
    $p1_sc_han_jin_sagaku = 0;
    $p1_sc_han_jin_temp   = 0;
} else {
    $p1_sc_han_jin_temp   = $p1_sc_han_jin;
    $p1_sc_han_jin_sagaku = $p1_sc_han_jin;
    $p1_sc_han_jin        = number_format(($p1_sc_han_jin / $tani), $keta);
}
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='試修人件費'", $p1_ym);
if (getUniResult($query, $p1_s_han_jin) < 1) {
    $p1_s_han_jin         = 0;    // 検索失敗
    $p1_s_han_jin_sagaku  = 0;
    $p1_sl_han_jin        = 0;
    $p1_sl_han_jin_temp   = 0;
    $p1_ss_han_jin        = 0;
    $p1_ss_han_jin_temp   = 0;
    $p1_st_han_jin        = 0;
    $p1_st_han_jin_temp   = 0;
} else {
    $p1_s_han_jin_sagaku  = $p1_s_han_jin;
    $p1_sl_han_jin        = $p1_s_han_jin - $p1_sc_han_jin_temp;            // リニア試験修理人件費を計算
    $p1_sl_han_jin_temp   = $p1_sl_han_jin;                                 // リニア試験修理損益計算用（temp)
    
    // 耐久・修理計算(耐久st、修理ss)
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='修理人件費'", $p1_ym);
    if (getUniResult($query, $p1_ss_han_jin) < 1) {
        $p1_ss_han_jin        = 0;     // 検索失敗
    }
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='耐久人件費'", $p1_ym);
    if (getUniResult($query, $p1_st_han_jin) < 1) {
        $p1_st_han_jin        = 0;     // 検索失敗
    }
    // 配賦労務費差額計算
    $p1_ss_han_jin_temp  = $p1_ss_han_jin;
    $p1_st_han_jin_temp  = $p1_st_han_jin;
    $p1_s_han_jin        = number_format(($p1_s_han_jin / $tani), $keta);
    $p1_ss_han_jin       = number_format(($p1_ss_han_jin / $tani), $keta);
    $p1_st_han_jin       = number_format(($p1_st_han_jin / $tani), $keta);
}
    ///// 前前月
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='カプラ試修人件費'", $p2_ym);
if (getUniResult($query, $p2_sc_han_jin) < 1) {
    $p2_sc_han_jin        = 0;     // 検索失敗
    $p2_sc_han_jin_sagaku = 0;
    $p2_sc_han_jin_temp   = 0;
} else {
    $p2_sc_han_jin_temp   = $p2_sc_han_jin;
    $p2_sc_han_jin_sagaku = $p2_sc_han_jin;
    $p2_sc_han_jin        = number_format(($p2_sc_han_jin / $tani), $keta);
}
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='試修人件費'", $p2_ym);
if (getUniResult($query, $p2_s_han_jin) < 1) {
    $p2_s_han_jin         = 0;    // 検索失敗
    $p2_s_han_jin_sagaku  = 0;
    $p2_sl_han_jin        = 0;
    $p2_sl_han_jin_temp   = 0;
    $p2_ss_han_jin        = 0;
    $p2_ss_han_jin_temp   = 0;
    $p2_st_han_jin        = 0;
    $p2_st_han_jin_temp   = 0;
} else {
    $p2_s_han_jin_sagaku  = $p2_s_han_jin;
    $p2_sl_han_jin        = $p2_s_han_jin - $p2_sc_han_jin_temp;            // リニア試験修理人件費を計算
    $p2_sl_han_jin_temp   = $p2_sl_han_jin;                                 // リニア試験修理損益計算用（temp)
    
    // 耐久・修理計算(耐久st、修理ss)
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='修理人件費'", $p2_ym);
    if (getUniResult($query, $p2_ss_han_jin) < 1) {
        $p2_ss_han_jin        = 0;     // 検索失敗
    }
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='耐久人件費'", $p2_ym);
    if (getUniResult($query, $p2_st_han_jin) < 1) {
        $p2_st_han_jin        = 0;     // 検索失敗
    }
    // 配賦労務費差額計算
    $p2_ss_han_jin_temp  = $p2_ss_han_jin;
    $p2_st_han_jin_temp  = $p2_st_han_jin;
    $p2_s_han_jin        = number_format(($p2_s_han_jin / $tani), $keta);
    $p2_ss_han_jin       = number_format(($p2_ss_han_jin / $tani), $keta);
    $p2_st_han_jin       = number_format(($p2_st_han_jin / $tani), $keta);
}
    ///// 今期累計
$query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='カプラ試修人件費'", $str_ym, $yyyymm);
if (getUniResult($query, $rui_sc_han_jin) < 1) {
    $rui_sc_han_jin        = 0;     // 検索失敗
    $rui_sc_han_jin_sagaku = 0;
    $rui_sc_han_jin_temp   = 0;
} else {
    $rui_sc_han_jin_temp   = $rui_sc_han_jin;
    $rui_sc_han_jin_sagaku = $rui_sc_han_jin;
    $rui_sc_han_jin        = number_format(($rui_sc_han_jin / $tani), $keta);
}
$query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='試修人件費'", $str_ym, $yyyymm);
if (getUniResult($query, $rui_s_han_jin) < 1) {
    $rui_s_han_jin         = 0;    // 検索失敗
    $rui_s_han_jin_sagaku  = 0;
    $rui_sl_han_jin        = 0;
    $rui_sl_han_jin_temp   = 0;
    $rui_ss_han_jin        = 0;
    $rui_ss_han_jin_temp   = 0;
    $rui_st_han_jin        = 0;
    $rui_st_han_jin_temp   = 0;
} else {
    $rui_s_han_jin_sagaku  = $rui_s_han_jin;
    $rui_sl_han_jin        = $rui_s_han_jin - $rui_sc_han_jin_temp;           // リニア試験修理人件費を計算
    $rui_sl_han_jin_temp   = $rui_sl_han_jin;                                 // リニア試験修理損益計算用（temp)
    
    // 耐久・修理計算(耐久st、修理ss)
    $query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='修理人件費'", $str_ym, $yyyymm);
    if (getUniResult($query, $rui_ss_han_jin) < 1) {
        $rui_ss_han_jin        = 0;     // 検索失敗
    }
    $query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='耐久人件費'", $str_ym, $yyyymm);
    if (getUniResult($query, $rui_st_han_jin) < 1) {
        $rui_st_han_jin        = 0;     // 検索失敗
    }
    // 配賦労務費差額計算
    $rui_ss_han_jin_temp  = $rui_ss_han_jin;
    $rui_st_han_jin_temp  = $rui_st_han_jin;
    $rui_s_han_jin        = number_format(($rui_s_han_jin / $tani), $keta);
    $rui_ss_han_jin       = number_format(($rui_ss_han_jin / $tani), $keta);
    $rui_st_han_jin       = number_format(($rui_st_han_jin / $tani), $keta);
}

/********** 販管費の経費 **********/
    ///// 当月
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='カプラ試修販管費経費'", $yyyymm);
if (getUniResult($query, $sc_han_kei) < 1) {
    $sc_han_kei        = 0;     // 検索失敗
    $sc_han_kei_sagaku = 0;
    $sc_han_kei_temp   = 0;
} else {
    $sc_han_kei_temp   = $sc_han_kei;
    $sc_han_kei_sagaku = $sc_han_kei;
    $sc_han_kei        = number_format(($sc_han_kei / $tani), $keta);
}
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='試修販管費経費'", $yyyymm);
if (getUniResult($query, $s_han_kei) < 1) {
    $s_han_kei        = 0;    // 検索失敗
    $s_han_kei_sagaku = 0;
    $sl_han_kei       = 0;
    $sl_han_kei_temp  = 0;
    $ss_han_kei       = 0;
    $ss_han_kei_temp  = 0;
    $st_han_kei       = 0;
    $st_han_kei_temp  = 0;
} else {
    $s_han_kei_sagaku  = $s_han_kei;
    $sl_han_kei        = $s_han_kei - $sc_han_kei_temp;               // リニア試験修理販管費経費を計算
    $sl_han_kei_temp   = $sl_han_kei;                                 // リニア試験修理損益計算用（temp)
    
    // 耐久・修理計算(耐久st、修理ss)
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='修理販管費経費'", $yyyymm);
    if (getUniResult($query, $ss_han_kei) < 1) {
        $ss_han_kei        = 0;     // 検索失敗
    }
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='耐久販管費経費'", $yyyymm);
    if (getUniResult($query, $st_han_kei) < 1) {
        $st_han_kei        = 0;     // 検索失敗
    }
    // 配賦労務費差額計算
    $ss_han_kei_temp  = $ss_han_kei;
    $st_han_kei_temp  = $st_han_kei;
    $s_han_kei        = number_format(($s_han_kei / $tani), $keta);
    $ss_han_kei       = number_format(($ss_han_kei / $tani), $keta);
    $st_han_kei       = number_format(($st_han_kei / $tani), $keta);
}
    ///// 前月
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='カプラ試修販管費経費'", $p1_ym);
if (getUniResult($query, $p1_sc_han_kei) < 1) {
    $p1_sc_han_kei        = 0;     // 検索失敗
    $p1_sc_han_kei_sagaku = 0;
    $p1_sc_han_kei_temp   = 0;
} else {
    $p1_sc_han_kei_temp   = $p1_sc_han_kei;
    $p1_sc_han_kei_sagaku = $p1_sc_han_kei;
    $p1_sc_han_kei        = number_format(($p1_sc_han_kei / $tani), $keta);
}
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='試修販管費経費'", $p1_ym);
if (getUniResult($query, $p1_s_han_kei) < 1) {
    $p1_s_han_kei         = 0;    // 検索失敗
    $p1_s_han_kei_sagaku  = 0;
    $p1_sl_han_kei        = 0;
    $p1_sl_han_kei_temp   = 0;
    $p1_ss_han_kei        = 0;
    $p1_ss_han_kei_temp   = 0;
    $p1_st_han_kei        = 0;
    $p1_st_han_kei_temp   = 0;
} else {
    $p1_s_han_kei_sagaku  = $p1_s_han_kei;
    $p1_sl_han_kei        = $p1_s_han_kei - $p1_sc_han_kei_temp;            // リニア試験修理販管費経費を計算
    $p1_sl_han_kei_temp   = $p1_sl_han_kei;                                 // リニア試験修理損益計算用（temp)
    
    // 耐久・修理計算(耐久st、修理ss)
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='修理販管費経費'", $p1_ym);
    if (getUniResult($query, $p1_ss_han_kei) < 1) {
        $p1_ss_han_kei        = 0;     // 検索失敗
    }
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='耐久販管費経費'", $p1_ym);
    if (getUniResult($query, $p1_st_han_kei) < 1) {
        $p1_st_han_kei        = 0;     // 検索失敗
    }
    // 配賦労務費差額計算
    $p1_ss_han_kei_temp  = $p1_ss_han_kei;
    $p1_st_han_kei_temp  = $p1_st_han_kei;
    $p1_s_han_kei        = number_format(($p1_s_han_kei / $tani), $keta);
    $p1_ss_han_kei       = number_format(($p1_ss_han_kei / $tani), $keta);
    $p1_st_han_kei       = number_format(($p1_st_han_kei / $tani), $keta);
}
    ///// 前前月
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='カプラ試修販管費経費'", $p2_ym);
if (getUniResult($query, $p2_sc_han_kei) < 1) {
    $p2_sc_han_kei        = 0;     // 検索失敗
    $p2_sc_han_kei_sagaku = 0;
    $p2_sc_han_kei_temp   = 0;
} else {
    $p2_sc_han_kei_temp   = $p2_sc_han_kei;
    $p2_sc_han_kei_sagaku = $p2_sc_han_kei;
    $p2_sc_han_kei        = number_format(($p2_sc_han_kei / $tani), $keta);
}
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='試修販管費経費'", $p2_ym);
if (getUniResult($query, $p2_s_han_kei) < 1) {
    $p2_s_han_kei         = 0;    // 検索失敗
    $p2_s_han_kei_sagaku  = 0;
    $p2_sl_han_kei        = 0;
    $p2_sl_han_kei_temp   = 0;
    $p2_ss_han_kei        = 0;
    $p2_ss_han_kei_temp   = 0;
    $p2_st_han_kei        = 0;
    $p2_st_han_kei_temp   = 0;
} else {
    $p2_s_han_kei_sagaku  = $p2_s_han_kei;
    $p2_sl_han_kei        = $p2_s_han_kei - $p2_sc_han_kei_temp;            // リニア試験修理販管費経費を計算
    $p2_sl_han_kei_temp   = $p2_sl_han_kei;                                 // リニア試験修理損益計算用（temp)
    
    // 耐久・修理計算(耐久st、修理ss)
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='修理販管費経費'", $p2_ym);
    if (getUniResult($query, $p2_ss_han_kei) < 1) {
        $p2_ss_han_kei        = 0;     // 検索失敗
    }
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='耐久販管費経費'", $p2_ym);
    if (getUniResult($query, $p2_st_han_kei) < 1) {
        $p2_st_han_kei        = 0;     // 検索失敗
    }
    // 配賦労務費差額計算
    $p2_ss_han_kei_temp  = $p2_ss_han_kei;
    $p2_st_han_kei_temp  = $p2_st_han_kei;
    $p2_s_han_kei        = number_format(($p2_s_han_kei / $tani), $keta);
    $p2_ss_han_kei       = number_format(($p2_ss_han_kei / $tani), $keta);
    $p2_st_han_kei       = number_format(($p2_st_han_kei / $tani), $keta);
}
    ///// 今期累計
$query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='カプラ試修販管費経費'", $str_ym, $yyyymm);
if (getUniResult($query, $rui_sc_han_kei) < 1) {
    $rui_sc_han_kei        = 0;     // 検索失敗
    $rui_sc_han_kei_sagaku = 0;
    $rui_sc_han_kei_temp   = 0;
} else {
    $rui_sc_han_kei_temp   = $rui_sc_han_kei;
    $rui_sc_han_kei_sagaku = $rui_sc_han_kei;
    $rui_sc_han_kei        = number_format(($rui_sc_han_kei / $tani), $keta);
}
$query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='試修販管費経費'", $str_ym, $yyyymm);
if (getUniResult($query, $rui_s_han_kei) < 1) {
    $rui_s_han_kei         = 0;    // 検索失敗
    $rui_s_han_kei_sagaku  = 0;
    $rui_sl_han_kei        = 0;
    $rui_sl_han_kei_temp   = 0;
    $rui_ss_han_kei        = 0;
    $rui_ss_han_kei_temp   = 0;
    $rui_st_han_kei        = 0;
    $rui_st_han_kei_temp   = 0;
} else {
    $rui_s_han_kei_sagaku  = $rui_s_han_kei;
    $rui_sl_han_kei        = $rui_s_han_kei - $rui_sc_han_kei_temp;           // リニア試験修理販管費経費を計算
    $rui_sl_han_kei_temp   = $rui_sl_han_kei;                                 // リニア試験修理損益計算用（temp)
    
    // 耐久・修理計算(耐久st、修理ss)
    $query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='修理販管費経費'", $str_ym, $yyyymm);
    if (getUniResult($query, $rui_ss_han_kei) < 1) {
        $rui_ss_han_kei        = 0;     // 検索失敗
    }
    $query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='耐久販管費経費'", $str_ym, $yyyymm);
    if (getUniResult($query, $rui_st_han_kei) < 1) {
        $rui_st_han_kei        = 0;     // 検索失敗
    }
    // 配賦労務費差額計算
    $rui_ss_han_kei_temp  = $rui_ss_han_kei;
    $rui_st_han_kei_temp  = $rui_st_han_kei;
    $rui_s_han_kei        = number_format(($rui_s_han_kei / $tani), $keta);
    $rui_ss_han_kei       = number_format(($rui_ss_han_kei / $tani), $keta);
    $rui_st_han_kei       = number_format(($rui_st_han_kei / $tani), $keta);
}

/********** 販管費の合計 **********/
    ///// 当月
    ///// 試験・修理
    $s_han_all            = $s_han_jin_sagaku + $s_han_kei_sagaku;
    $s_han_all_sagaku     = $s_han_all;
    $sc_han_all           = $sc_han_jin_temp + $sc_han_kei_temp;            // カプラ試修販管費合計の計算
    $sc_han_all_temp      = $sc_han_all;                                    // カプラ試修損益計算用(temp)
    $sl_han_all           = $sl_han_jin_temp + $sl_han_kei_temp;            // リニア試修販管費合計の計算
    $sl_han_all_temp      = $sl_han_all;                                    // リニア試修損益計算用(temp)
    
    // 耐久・修理計算(耐久st、修理ss)
    $ss_han_all       = $ss_han_jin_temp + $ss_han_kei_temp;            // 修理販管費合計の計算
    $ss_han_all_temp  = $ss_han_all;                                    // 修理損益計算用(temp)
    $st_han_all       = $st_han_jin_temp + $st_han_kei_temp;            // 耐久販管費合計の計算
    $st_han_all_temp  = $st_han_all;                                    // 耐久損益計算用(temp)
    $s_han_all        = number_format(($s_han_all / $tani), $keta);
    $ss_han_all       = number_format(($ss_han_all / $tani), $keta);
    $st_han_all       = number_format(($st_han_all / $tani), $keta);
    
    ///// 前月
    ///// 試験・修理
    $p1_s_han_all         = $p1_s_han_jin_sagaku + $p1_s_han_kei_sagaku;
    $p1_s_han_all_sagaku  = $p1_s_han_all;
    $p1_sc_han_all        = $p1_sc_han_jin_temp + $p1_sc_han_kei_temp;      // カプラ試修販管費合計の計算
    $p1_sc_han_all_temp   = $p1_sc_han_all;                                 // カプラ試修損益計算用(temp)
    $p1_sl_han_all        = $p1_sl_han_jin_temp + $p1_sl_han_kei_temp;      // リニア試修販管費合計の計算
    $p1_sl_han_all_temp   = $p1_sl_han_all;                                 // リニア試修損益計算用(temp)
    
    // 耐久・修理計算(耐久st、修理ss)
    $p1_ss_han_all       = $p1_ss_han_jin_temp + $p1_ss_han_kei_temp;            // 修理販管費合計の計算
    $p1_ss_han_all_temp  = $p1_ss_han_all;                                    // 修理損益計算用(temp)
    $p1_st_han_all       = $p1_st_han_jin_temp + $p1_st_han_kei_temp;            // 耐久販管費合計の計算
    $p1_st_han_all_temp  = $p1_st_han_all;                                    // 耐久損益計算用(temp)
    $p1_s_han_all        = number_format(($p1_s_han_all / $tani), $keta);
    $p1_ss_han_all       = number_format(($p1_ss_han_all / $tani), $keta);
    $p1_st_han_all       = number_format(($p1_st_han_all / $tani), $keta);
    
    ///// 前前月
    ///// 試験・修理
    $p2_s_han_all         = $p2_s_han_jin_sagaku + $p2_s_han_kei_sagaku;
    $p2_s_han_all_sagaku  = $p2_s_han_all;
    $p2_sc_han_all        = $p2_sc_han_jin_temp + $p2_sc_han_kei_temp;      // カプラ試修販管費合計の計算
    $p2_sc_han_all_temp   = $p2_sc_han_all;                                 // カプラ試修損益計算用(temp)
    $p2_sl_han_all        = $p2_sl_han_jin_temp + $p2_sl_han_kei_temp;      // リニア試修販管費合計の計算
    $p2_sl_han_all_temp   = $p2_sl_han_all;                                 // リニア試修損益計算用(temp)
    
    // 耐久・修理計算(耐久st、修理ss)
    $p2_ss_han_all       = $p2_ss_han_jin_temp + $p2_ss_han_kei_temp;            // 修理販管費合計の計算
    $p2_ss_han_all_temp  = $p2_ss_han_all;                                    // 修理損益計算用(temp)
    $p2_st_han_all       = $p2_st_han_jin_temp + $p2_st_han_kei_temp;            // 耐久販管費合計の計算
    $p2_st_han_all_temp  = $p2_st_han_all;                                    // 耐久損益計算用(temp)
    $p2_s_han_all        = number_format(($p2_s_han_all / $tani), $keta);
    $p2_ss_han_all       = number_format(($p2_ss_han_all / $tani), $keta);
    $p2_st_han_all       = number_format(($p2_st_han_all / $tani), $keta);
    
    ///// 今期累計
    ///// 試験・修理
    $rui_s_han_all        = $rui_s_han_jin_sagaku + $rui_s_han_kei_sagaku;
    $rui_s_han_all_sagaku = $rui_s_han_all;
    $rui_sc_han_all       = $rui_sc_han_jin_temp + $rui_sc_han_kei_temp;    // カプラ試修販管費合計の計算
    $rui_sc_han_all_temp  = $rui_sc_han_all;                                // カプラ試修損益計算用(temp)
    $rui_sl_han_all       = $rui_sl_han_jin_temp + $rui_sl_han_kei_temp;    // リニア試修販管費合計の計算
    $rui_sl_han_all_temp  = $rui_sl_han_all;                                // リニア試修損益計算用(temp)
    
    // 耐久・修理計算(耐久st、修理ss)
    $rui_ss_han_all       = $rui_ss_han_jin_temp + $rui_ss_han_kei_temp;            // 修理販管費合計の計算
    $rui_ss_han_all_temp  = $rui_ss_han_all;                                    // 修理損益計算用(temp)
    $rui_st_han_all       = $rui_st_han_jin_temp + $rui_st_han_kei_temp;            // 耐久販管費合計の計算
    $rui_st_han_all_temp  = $rui_st_han_all;                                    // 耐久損益計算用(temp)
    $rui_s_han_all        = number_format(($rui_s_han_all / $tani), $keta);
    $rui_ss_han_all       = number_format(($rui_ss_han_all / $tani), $keta);
    $rui_st_han_all       = number_format(($rui_st_han_all / $tani), $keta);

/********** 営業利益 **********/
    ///// 試験・修理
$p2_s_ope_profit         = $p2_s_gross_profit_sagaku - $p2_s_han_all_sagaku;
$p2_s_ope_profit_sagaku  = $p2_s_ope_profit;
$p2_sc_ope_profit        = $p2_sc_gross_profit_temp - $p2_sc_han_all_temp;      // カプラ試修営業利益の計算
$p2_sc_ope_profit_temp   = $p2_sc_ope_profit;                                   // カプラ試修損益計算用(temp)
$p2_sl_ope_profit        = $p2_sl_gross_profit_temp - $p2_sl_han_all_temp;      // リニア試修営業利益の計算
$p2_sl_ope_profit_temp   = $p2_sl_ope_profit;                                   // リニア試修損益計算用(temp)
$p2_s_ope_profit         = $p2_s_ope_profit + $p2_sc_uri_sagaku - $p2_sc_metarial_sagaku;   // カプラ試修売上高を加味（sagakuの下 リニアからマイナスしてしまう為）
if ($p2_ym == 200912) {
    $p2_s_ope_profit = $p2_s_ope_profit + 1409708;
}
if ($p2_ym == 200912) {
    $p2_sl_ope_profit = $p2_sl_ope_profit + 1195898;
}
if ($p2_ym == 200912) {
    $p2_sc_ope_profit = $p2_sc_ope_profit + 213810;
}

// 耐久・修理計算(耐久st、修理ss)
$p2_ss_ope_profit      = $p2_ss_gross_profit_temp - $p2_ss_han_all_temp;      // 修理営業利益の計算
$p2_ss_ope_profit_temp = $p2_ss_ope_profit;                                   // 修理損益計算用(temp)
$p2_st_ope_profit      = $p2_st_gross_profit_temp - $p2_st_han_all_temp;      // 耐久営業利益の計算
$p2_st_ope_profit_temp = $p2_st_ope_profit;                                   // 耐久損益計算用(temp)

$p2_s_ope_profit       = number_format(($p2_s_ope_profit / $tani), $keta);
$p2_ss_ope_profit      = number_format(($p2_ss_ope_profit / $tani), $keta);
$p2_st_ope_profit      = number_format(($p2_st_ope_profit / $tani), $keta);

$p1_s_ope_profit         = $p1_s_gross_profit_sagaku - $p1_s_han_all_sagaku;
$p1_s_ope_profit_sagaku  = $p1_s_ope_profit;
$p1_sc_ope_profit        = $p1_sc_gross_profit_temp - $p1_sc_han_all_temp;      // カプラ試修営業利益の計算
$p1_sc_ope_profit_temp   = $p1_sc_ope_profit;                                   // カプラ試修損益計算用(temp)
$p1_sl_ope_profit        = $p1_sl_gross_profit_temp - $p1_sl_han_all_temp;      // リニア試修営業利益の計算
$p1_sl_ope_profit_temp   = $p1_sl_ope_profit;                                   // リニア試修損益計算用(temp)
$p1_s_ope_profit         = $p1_s_ope_profit + $p1_sc_uri_sagaku - $p1_sc_metarial_sagaku;   // カプラ試修売上高を加味（sagakuの下 リニアからマイナスしてしまう為）
if ($p1_ym == 200912) {
    $p1_s_ope_profit = $p1_s_ope_profit + 1409708;
}
if ($p1_ym == 200912) {
    $p1_sl_ope_profit = $p1_sl_ope_profit + 1195898;
}
if ($p1_ym == 200912) {
    $p1_sc_ope_profit = $p1_sc_ope_profit + 213810;
}

// 耐久・修理計算(耐久st、修理ss)
$p1_ss_ope_profit      = $p1_ss_gross_profit_temp - $p1_ss_han_all_temp;      // 修理営業利益の計算
$p1_ss_ope_profit_temp = $p1_ss_ope_profit;                                   // 修理損益計算用(temp)
$p1_st_ope_profit      = $p1_st_gross_profit_temp - $p1_st_han_all_temp;      // 耐久営業利益の計算
$p1_st_ope_profit_temp = $p1_st_ope_profit;                                   // 耐久損益計算用(temp)

$p1_s_ope_profit       = number_format(($p1_s_ope_profit / $tani), $keta);
$p1_ss_ope_profit      = number_format(($p1_ss_ope_profit / $tani), $keta);
$p1_st_ope_profit      = number_format(($p1_st_ope_profit / $tani), $keta);

$s_ope_profit            = $s_gross_profit_sagaku - $s_han_all_sagaku;
$s_ope_profit_sagaku     = $s_ope_profit;
$sc_ope_profit           = $sc_gross_profit_temp - $sc_han_all_temp;            // カプラ試修営業利益の計算
$sc_ope_profit_temp      = $sc_ope_profit;                                      // カプラ試修損益計算用(temp)
$sl_ope_profit           = $sl_gross_profit_temp - $sl_han_all_temp;            // リニア試修営業利益の計算
$sl_ope_profit_temp      = $sl_ope_profit;                                      // リニア試修損益計算用(temp)
$s_ope_profit            = $s_ope_profit + $sc_uri_sagaku - $sc_metarial_sagaku;   // カプラ試修売上高を加味（sagakuの下 リニアからマイナスしてしまう為）
if ($yyyymm == 200912) {
    $s_ope_profit = $s_ope_profit + 1409708;
}
if ($yyyymm == 200912) {
    $sc_ope_profit = $sc_ope_profit + 213810;
}
if ($yyyymm == 200912) {
    $sl_ope_profit = $sl_ope_profit + 1195898;
}

// 耐久・修理計算(耐久st、修理ss)
$ss_ope_profit      = $ss_gross_profit_temp - $ss_han_all_temp;      // 修理営業利益の計算
$ss_ope_profit_temp = $ss_ope_profit;                                   // 修理損益計算用(temp)
$st_ope_profit      = $st_gross_profit_temp - $st_han_all_temp;      // 耐久営業利益の計算
$st_ope_profit_temp = $st_ope_profit;                                   // 耐久損益計算用(temp)

$s_ope_profit       = number_format(($s_ope_profit / $tani), $keta);
$ss_ope_profit      = number_format(($ss_ope_profit / $tani), $keta);
$st_ope_profit      = number_format(($st_ope_profit / $tani), $keta);

$rui_s_ope_profit        = $rui_s_gross_profit_sagaku - $rui_s_han_all_sagaku;
$rui_s_ope_profit_sagaku = $rui_s_ope_profit;
$rui_sc_ope_profit       = $rui_sc_gross_profit_temp - $rui_sc_han_all_temp;    // カプラ試修営業利益の計算
$rui_sc_ope_profit_temp  = $rui_sc_ope_profit;                                  // カプラ試修損益計算用(temp)
$rui_sl_ope_profit       = $rui_sl_gross_profit_temp - $rui_sl_han_all_temp;    // リニア試修営業利益の計算
$rui_sl_ope_profit_temp  = $rui_sl_ope_profit;                                  // リニア試修損益計算用(temp)
$rui_s_ope_profit        = $rui_s_ope_profit + $rui_sc_uri_sagaku - $rui_sc_metarial_sagaku;   // カプラ試修売上高を加味（sagakuの下 リニアからマイナスしてしまう為）
if ($yyyymm >= 200912 && $yyyymm <= 201003) {
    $rui_s_ope_profit = $rui_s_ope_profit + 1409708;
}
if ($yyyymm >= 200912 && $yyyymm <= 201003) {
    $rui_sc_ope_profit = $rui_sc_ope_profit + 213810;
}
if ($yyyymm >= 200912 && $yyyymm <= 201003) {
    $rui_sl_ope_profit = $rui_sl_ope_profit + 1195898;
}

// 耐久・修理計算(耐久st、修理ss)
$rui_ss_ope_profit      = $rui_ss_gross_profit_temp - $rui_ss_han_all_temp;      // 修理営業利益の計算
$rui_ss_ope_profit_temp = $rui_ss_ope_profit;                                   // 修理損益計算用(temp)
$rui_st_ope_profit      = $rui_st_gross_profit_temp - $rui_st_han_all_temp;      // 耐久営業利益の計算
$rui_st_ope_profit_temp = $rui_st_ope_profit;                                   // 耐久損益計算用(temp)

$rui_s_ope_profit       = number_format(($rui_s_ope_profit / $tani), $keta);
$rui_ss_ope_profit      = number_format(($rui_ss_ope_profit / $tani), $keta);
$rui_st_ope_profit      = number_format(($rui_st_ope_profit / $tani), $keta);

/********** 営業外収益の業務委託収入 **********/
    ///// 当月
if ($yyyymm >= 201001) {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='カプラ試修業務委託収入再計算'", $yyyymm);
} else {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='カプラ試修業務委託収入'", $yyyymm);
}
if (getUniResult($query, $sc_gyoumu) < 1) {
    $sc_gyoumu        = 0;     // 検索失敗
    $sc_gyoumu_sagaku = 0;
    $sc_gyoumu_temp   = 0;
} else {
    if ($yyyymm == 200912) {
        $sc_gyoumu = $sc_gyoumu - 101;
    }
    if ($yyyymm == 201001) {
        $sc_gyoumu = $sc_gyoumu + 4855;
    }
    $sc_gyoumu_temp   = $sc_gyoumu;
    $sc_gyoumu_sagaku = $sc_gyoumu;
    $sc_gyoumu        = number_format(($sc_gyoumu / $tani), $keta);
}
if ($yyyymm >= 201001) {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='試修業務委託収入再計算'", $yyyymm);
} else {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='試修業務委託収入'", $yyyymm);
}
if (getUniResult($query, $s_gyoumu) < 1) {
    $s_gyoumu         = 0;    // 検索失敗
    $s_gyoumu_sagaku  = 0;
    $sl_gyoumu        = 0;
    $sl_gyoumu_temp   = 0;
    $ss_gyoumu        = 0;
    $ss_gyoumu_temp   = 0;
    $st_gyoumu        = 0;
    $st_gyoumu_temp   = 0;
} else {
    if ($yyyymm == 200912) {
        $s_gyoumu = $s_gyoumu - 722;
    }
    if ($yyyymm == 201001) {
        $s_gyoumu = $s_gyoumu + 29125;
    }
    $s_gyoumu_sagaku  = $s_gyoumu;
    $sl_gyoumu        = $s_gyoumu - $sc_gyoumu_temp;                // リニア試験修理業務委託収入を計算
    $sl_gyoumu_temp   = $sl_gyoumu;                                 // リニア試験修理損益計算用（temp)
    
    // 耐久・修理計算(耐久st、修理ss)
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='修理業務委託収入再計算'", $yyyymm);
    if (getUniResult($query, $ss_gyoumu) < 1) {
        $ss_gyoumu        = 0;     // 検索失敗
    }
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='耐久業務委託収入再計算'", $yyyymm);
    if (getUniResult($query, $st_gyoumu) < 1) {
        $st_gyoumu        = 0;     // 検索失敗
    }
    // 配賦労務費差額計算
    $ss_gyoumu_temp  = $ss_gyoumu;
    $st_gyoumu_temp  = $st_gyoumu;
    $s_gyoumu        = number_format(($s_gyoumu / $tani), $keta);
    $ss_gyoumu       = number_format(($ss_gyoumu / $tani), $keta);
    $st_gyoumu       = number_format(($st_gyoumu / $tani), $keta);
}
    ///// 前月
if ($p1_ym >= 201001) {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='カプラ試修業務委託収入再計算'", $p1_ym);
} else {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='カプラ試修業務委託収入'", $p1_ym);
}
if (getUniResult($query, $p1_sc_gyoumu) < 1) {
    $p1_sc_gyoumu        = 0;     // 検索失敗
    $p1_sc_gyoumu_sagaku = 0;
    $p1_sc_gyoumu_temp   = 0;
} else {
    if ($p1_ym == 200912) {
        $p1_sc_gyoumu = $p1_sc_gyoumu - 101;
    }
    if ($p1_ym == 201001) {
        $p1_sc_gyoumu = $p1_sc_gyoumu + 4855;
    }
    $p1_sc_gyoumu_temp   = $p1_sc_gyoumu;
    $p1_sc_gyoumu_sagaku = $p1_sc_gyoumu;
    $p1_sc_gyoumu        = number_format(($p1_sc_gyoumu / $tani), $keta);
}
if ($p1_ym >= 201001) {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='試修業務委託収入再計算'", $p1_ym);
} else {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='試修業務委託収入'", $p1_ym);
}
if (getUniResult($query, $p1_s_gyoumu) < 1) {
    $p1_s_gyoumu         = 0;    // 検索失敗
    $p1_s_gyoumu_sagaku  = 0;
    $p1_sl_gyoumu        = 0;
    $p1_sl_gyoumu_temp   = 0;
    $p1_ss_gyoumu        = 0;
    $p1_ss_gyoumu_temp   = 0;
    $p1_st_gyoumu        = 0;
    $p1_st_gyoumu_temp   = 0;
} else {
    if ($p1_ym == 200912) {
        $p1_s_gyoumu = $p1_s_gyoumu - 722;
    }
    if ($p1_ym == 201001) {
        $p1_s_gyoumu = $p1_s_gyoumu + 29125;
    }
    $p1_s_gyoumu_sagaku  = $p1_s_gyoumu;
    $p1_sl_gyoumu        = $p1_s_gyoumu - $p1_sc_gyoumu_temp;             // リニア試験修理業務委託収入を計算
    $p1_sl_gyoumu_temp   = $p1_sl_gyoumu;                                 // リニア試験修理損益計算用（temp)
    
    // 耐久・修理計算(耐久st、修理ss)
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='修理業務委託収入再計算'", $p1_ym);
    if (getUniResult($query, $p1_ss_gyoumu) < 1) {
        $p1_ss_gyoumu        = 0;     // 検索失敗
    }
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='耐久業務委託収入再計算'", $p1_ym);
    if (getUniResult($query, $p1_st_gyoumu) < 1) {
        $p1_st_gyoumu        = 0;     // 検索失敗
    }
    // 配賦労務費差額計算
    $p1_ss_gyoumu_temp  = $p1_ss_gyoumu;
    $p1_st_gyoumu_temp  = $p1_st_gyoumu;
    $p1_s_gyoumu        = number_format(($p1_s_gyoumu / $tani), $keta);
    $p1_ss_gyoumu       = number_format(($p1_ss_gyoumu / $tani), $keta);
    $p1_st_gyoumu       = number_format(($p1_st_gyoumu / $tani), $keta);
}
    ///// 前前月
if ($p2_ym >= 201001) {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='カプラ試修業務委託収入再計算'", $p2_ym);
} else {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='カプラ試修業務委託収入'", $p2_ym);
}
if (getUniResult($query, $p2_sc_gyoumu) < 1) {
    $p2_sc_gyoumu        = 0;     // 検索失敗
    $p2_sc_gyoumu_sagaku = 0;
    $p2_sc_gyoumu_temp   = 0;
} else {
    if ($p2_ym == 200912) {
        $p2_sc_gyoumu = $p2_sc_gyoumu - 101;
    }
    if ($p2_ym == 201001) {
        $p2_sc_gyoumu = $p2_sc_gyoumu + 4855;
    }
    $p2_sc_gyoumu_temp   = $p2_sc_gyoumu;
    $p2_sc_gyoumu_sagaku = $p2_sc_gyoumu;
    $p2_sc_gyoumu        = number_format(($p2_sc_gyoumu / $tani), $keta);
}
if ($p2_ym >= 201001) {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='試修業務委託収入再計算'", $p2_ym);
} else {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='試修業務委託収入'", $p2_ym);
}
if (getUniResult($query, $p2_s_gyoumu) < 1) {
    $p2_s_gyoumu         = 0;    // 検索失敗
    $p2_s_gyoumu_sagaku  = 0;
    $p2_sl_gyoumu        = 0;
    $p2_sl_gyoumu_temp   = 0;
    $p2_ss_gyoumu        = 0;
    $p2_ss_gyoumu_temp   = 0;
    $p2_st_gyoumu        = 0;
    $p2_st_gyoumu_temp   = 0;
} else {
    if ($p2_ym == 200912) {
        $p2_s_gyoumu = $p2_s_gyoumu - 722;
    }
    if ($p2_ym == 201001) {
        $p2_s_gyoumu = $p2_s_gyoumu + 29125;
    }
    $p2_s_gyoumu_sagaku  = $p2_s_gyoumu;
    $p2_sl_gyoumu        = $p2_s_gyoumu - $p2_sc_gyoumu_temp;             // リニア試験修理業務委託収入を計算
    $p2_sl_gyoumu_temp   = $p2_sl_gyoumu;                                 // リニア試験修理損益計算用（temp)
    
    // 耐久・修理計算(耐久st、修理ss)
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='修理業務委託収入再計算'", $p2_ym);
    if (getUniResult($query, $p2_ss_gyoumu) < 1) {
        $p2_ss_gyoumu        = 0;     // 検索失敗
    }
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='耐久業務委託収入再計算'", $p2_ym);
    if (getUniResult($query, $p2_st_gyoumu) < 1) {
        $p2_st_gyoumu        = 0;     // 検索失敗
    }
    // 配賦労務費差額計算
    $p2_ss_gyoumu_temp  = $p2_ss_gyoumu;
    $p2_st_gyoumu_temp  = $p2_st_gyoumu;
    $p2_s_gyoumu        = number_format(($p2_s_gyoumu / $tani), $keta);
    $p2_ss_gyoumu       = number_format(($p2_ss_gyoumu / $tani), $keta);
    $p2_st_gyoumu       = number_format(($p2_st_gyoumu / $tani), $keta);
}
    ///// 今期累計
if ($yyyymm >= 201004) {
    $query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='カプラ試修業務委託収入再計算'", $str_ym, $yyyymm);
    if (getUniResult($query, $rui_sc_gyoumu) < 1) {
        $rui_sc_gyoumu_temp   = 0;
        $rui_sc_gyoumu_sagaku = 0;
        $rui_sc_gyoumu        = 0;                     // 検索失敗
    } else {
        $rui_sc_gyoumu_temp   = $rui_sc_gyoumu;
        $rui_sc_gyoumu_sagaku = $rui_sc_gyoumu;
        $rui_sc_gyoumu = number_format(($rui_sc_gyoumu / $tani), $keta);
    }
} elseif ($yyyymm >= 201001 && $yyyymm <= 201003) {
    $query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=200904 and pl_bs_ym<=200912 and note='カプラ試修業務委託収入'");
    if (getUniResult($query, $rui_sc_gyoumu_a) < 1) {
        $rui_sc_gyoumu_a = 0;                          // 検索失敗
    }
    $query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=201001 and pl_bs_ym<=%d and note='カプラ試修業務委託収入再計算'", $yyyymm);
    if (getUniResult($query, $rui_sc_gyoumu_b) < 1) {
        $rui_sc_gyoumu_b = 0;                          // 検索失敗
    }
    $rui_sc_gyoumu = $rui_sc_gyoumu_a + $rui_sc_gyoumu_b;
    
    if ($yyyymm >= 200912 && $yyyymm <= 201003) {
        $rui_sc_gyoumu = $rui_sc_gyoumu - 101;
    }
    if ($yyyymm >= 201001 && $yyyymm <= 201003) {
        $rui_sc_gyoumu = $rui_sc_gyoumu + 4855;
    }
    $rui_sc_gyoumu_temp   = $rui_sc_gyoumu;
    $rui_sc_gyoumu_sagaku = $rui_sc_gyoumu;
    $rui_sc_gyoumu        = number_format(($rui_sc_gyoumu / $tani), $keta);
} else {
    $query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='カプラ試修業務委託収入'", $str_ym, $yyyymm);
    if (getUniResult($query, $rui_sc_gyoumu) < 1) {
        $rui_sc_gyoumu        = 0;     // 検索失敗
        $rui_sc_gyoumu_sagaku = 0;
        $rui_sc_gyoumu_temp   = 0;
    } else {
        if ($yyyymm >= 200912 && $yyyymm <= 201003) {
            $rui_sc_gyoumu = $rui_sc_gyoumu - 101;
        }
        if ($yyyymm >= 201001 && $yyyymm <= 201003) {
            $rui_sc_gyoumu = $rui_sc_gyoumu + 4855;
        }
        $rui_sc_gyoumu_temp   = $rui_sc_gyoumu;
        $rui_sc_gyoumu_sagaku = $rui_sc_gyoumu;
        $rui_sc_gyoumu        = number_format(($rui_sc_gyoumu / $tani), $keta);
    }
}
if ($yyyymm >= 201004) {
    $query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='試修業務委託収入再計算'", $str_ym, $yyyymm);
    if (getUniResult($query, $rui_s_gyoumu) < 1) {
        $rui_s_gyoumu         = 0;                          // 検索失敗
        $rui_s_gyoumu_sagaku  = 0;
        $rui_sl_gyoumu        = 0;
        $rui_sl_gyoumu_temp   = 0;
        $rui_ss_gyoumu        = 0;
        $rui_ss_gyoumu_temp   = 0;
        $rui_st_gyoumu        = 0;
        $rui_st_gyoumu_temp   = 0;
    } else {
        $rui_s_gyoumu_sagaku  = $rui_s_gyoumu;
        $rui_sl_gyoumu        = $rui_s_gyoumu - $rui_sc_gyoumu_temp;            // リニア試験修理業務委託収入を計算
        $rui_sl_gyoumu_temp   = $rui_sl_gyoumu;                                 // リニア試験修理損益計算用（temp)
        
        // 耐久・修理計算(耐久st、修理ss)
        $query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='修理業務委託収入再計算'", $str_ym, $yyyymm);
        if (getUniResult($query, $rui_ss_gyoumu) < 1) {
            $rui_ss_gyoumu        = 0;     // 検索失敗
        }
        $query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='耐久業務委託収入再計算'", $str_ym, $yyyymm);
        if (getUniResult($query, $rui_st_gyoumu) < 1) {
            $rui_st_gyoumu        = 0;     // 検索失敗
        }
        // 配賦労務費差額計算
        $rui_ss_gyoumu_temp  = $rui_ss_gyoumu;
        $rui_st_gyoumu_temp  = $rui_st_gyoumu;
        $rui_s_gyoumu        = number_format(($rui_s_gyoumu / $tani), $keta);
        $rui_ss_gyoumu       = number_format(($rui_ss_gyoumu / $tani), $keta);
        $rui_st_gyoumu       = number_format(($rui_st_gyoumu / $tani), $keta);
    }
} elseif ($yyyymm >= 201001 && $yyyymm <= 201003) {
    $query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=200904 and pl_bs_ym<=200912 and note='試修業務委託収入'");
    if (getUniResult($query, $rui_s_gyoumu_a) < 1) {
        $rui_s_gyoumu_a = 0;                          // 検索失敗
    }
    $query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=201001 and pl_bs_ym<=%d and note='試修業務委託収入再計算'", $yyyymm);
    if (getUniResult($query, $rui_s_gyoumu_b) < 1) {
        $rui_s_gyoumu_b = 0;                          // 検索失敗
    }
    $rui_s_gyoumu = $rui_s_gyoumu_a + $rui_s_gyoumu_b;
    
    if ($yyyymm >= 200912 && $yyyymm <= 201003) {
        $rui_s_gyoumu = $rui_s_gyoumu - 722;
    }
    if ($yyyymm >= 201001 && $yyyymm <= 201003) {
        $rui_s_gyoumu = $rui_s_gyoumu + 29125;
    }
    $rui_s_gyoumu_sagaku  = $rui_s_gyoumu;
    $rui_sl_gyoumu        = $rui_s_gyoumu - $rui_sc_gyoumu_temp;            // リニア試験修理業務委託収入を計算
    $rui_sl_gyoumu_temp   = $rui_sl_gyoumu;                                 // リニア試験修理損益計算用（temp)
    $rui_s_gyoumu         = number_format(($rui_s_gyoumu / $tani), $keta);
    $rui_sl_gyoumu        = number_format(($rui_sl_gyoumu / $tani), $keta);
} else {
    $query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='試修業務委託収入'", $str_ym, $yyyymm);
    if (getUniResult($query, $rui_s_gyoumu) < 1) {
        $rui_s_gyoumu         = 0;    // 検索失敗
        $rui_s_gyoumu_sagaku  = 0;
        $rui_sl_gyoumu        = 0;
        $rui_sl_gyoumu_temp   = 0;
    } else {
        if ($yyyymm >= 200912 && $yyyymm <= 201003) {
            $rui_s_gyoumu = $rui_s_gyoumu - 722;
        }
        if ($yyyymm >= 201001 && $yyyymm <= 201003) {
            $rui_s_gyoumu = $rui_s_gyoumu + 29125;
        }
        $rui_s_gyoumu_sagaku  = $rui_s_gyoumu;
        $rui_sl_gyoumu        = $rui_s_gyoumu - $rui_sc_gyoumu_temp;            // リニア試験修理業務委託収入を計算
        $rui_sl_gyoumu_temp   = $rui_sl_gyoumu;                                 // リニア試験修理損益計算用（temp)
        $rui_s_gyoumu         = number_format(($rui_s_gyoumu / $tani), $keta);
        $rui_sl_gyoumu        = number_format(($rui_sl_gyoumu / $tani), $keta);
    }
}

/********** 営業外収益の仕入割引 **********/
    ///// 当月
if ($yyyymm >= 201001) {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='カプラ試修仕入割引再計算'", $yyyymm);
} else {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='カプラ試修仕入割引'", $yyyymm);
}
if (getUniResult($query, $sc_swari) < 1) {
    $sc_swari        = 0;     // 検索失敗
    $sc_swari_sagaku = 0;
    $sc_swari_temp   = 0;
} else {
    $sc_swari_temp   = $sc_swari;
    $sc_swari_sagaku = $sc_swari;
    $sc_swari        = number_format(($sc_swari / $tani), $keta);
}
if ($yyyymm >= 201001) {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='試修仕入割引再計算'", $yyyymm);
} else {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='試修仕入割引'", $yyyymm);
}
if (getUniResult($query, $s_swari) < 1) {
    $s_swari         = 0;    // 検索失敗
    $s_swari_sagaku  = 0;
    $sl_swari        = 0;
    $sl_swari_temp   = 0;
    $ss_swari        = 0;
    $ss_swari_temp   = 0;
    $st_swari        = 0;
    $st_swari_temp   = 0;
} else {
    $s_swari_sagaku  = $s_swari;
    $sl_swari        = $s_swari - $sc_swari_temp;                 // リニア試験修理仕入割引を計算
    $sl_swari_temp   = $sl_swari;                                 // リニア試験修理損益計算用（temp)
    
    // 耐久・修理計算(耐久st、修理ss)
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='修理仕入割引再計算'", $yyyymm);
    if (getUniResult($query, $ss_swari) < 1) {
        $ss_swari        = 0;     // 検索失敗
    }
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='耐久仕入割引再計算'", $yyyymm);
    if (getUniResult($query, $st_swari) < 1) {
        $st_swari        = 0;     // 検索失敗
    }
    // 配賦労務費差額計算
    $ss_swari_temp  = $ss_swari;
    $st_swari_temp  = $st_swari;
    $s_swari        = number_format(($s_swari / $tani), $keta);
    $ss_swari       = number_format(($ss_swari / $tani), $keta);
    $st_swari       = number_format(($st_swari / $tani), $keta);
}
    ///// 前月
if ($p1_ym >= 201001) {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='カプラ試修仕入割引再計算'", $p1_ym);
} else {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='カプラ試修仕入割引'", $p1_ym);
}
if (getUniResult($query, $p1_sc_swari) < 1) {
    $p1_sc_swari        = 0;     // 検索失敗
    $p1_sc_swari_sagaku = 0;
    $p1_sc_swari_temp   = 0;
} else {
    $p1_sc_swari_temp   = $p1_sc_swari;
    $p1_sc_swari_sagaku = $p1_sc_swari;
    $p1_sc_swari        = number_format(($p1_sc_swari / $tani), $keta);
}
if ($p1_ym >= 201001) {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='試修仕入割引再計算'", $p1_ym);
} else {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='試修仕入割引'", $p1_ym);
}
if (getUniResult($query, $p1_s_swari) < 1) {
    $p1_s_swari         = 0;    // 検索失敗
    $p1_s_swari_sagaku  = 0;
    $p1_sl_swari        = 0;
    $p1_sl_swari_temp   = 0;
    $p1_ss_swari        = 0;
    $p1_ss_swari_temp   = 0;
    $p1_st_swari        = 0;
    $p1_st_swari_temp   = 0;
} else {
    $p1_s_swari_sagaku  = $p1_s_swari;
    $p1_sl_swari        = $p1_s_swari - $p1_sc_swari_temp;              // リニア試験修理仕入割引を計算
    $p1_sl_swari_temp   = $p1_sl_swari;                                 // リニア試験修理損益計算用（temp)
    
    // 耐久・修理計算(耐久st、修理ss)
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='修理仕入割引再計算'", $p1_ym);
    if (getUniResult($query, $p1_ss_swari) < 1) {
        $p1_ss_swari        = 0;     // 検索失敗
    }
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='耐久仕入割引再計算'", $p1_ym);
    if (getUniResult($query, $p1_st_swari) < 1) {
        $p1_st_swari        = 0;     // 検索失敗
    }
    // 配賦労務費差額計算
    $p1_ss_swari_temp  = $p1_ss_swari;
    $p1_st_swari_temp  = $p1_st_swari;
    $p1_s_swari        = number_format(($p1_s_swari / $tani), $keta);
    $p1_ss_swari       = number_format(($p1_ss_swari / $tani), $keta);
    $p1_st_swari       = number_format(($p1_st_swari / $tani), $keta);
}
    ///// 前前月
if ($p2_ym >= 201001) {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='カプラ試修仕入割引再計算'", $p2_ym);
} else {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='カプラ試修仕入割引'", $p2_ym);
}
if (getUniResult($query, $p2_sc_swari) < 1) {
    $p2_sc_swari        = 0;     // 検索失敗
    $p2_sc_swari_sagaku = 0;
    $p2_sc_swari_temp   = 0;
} else {
    $p2_sc_swari_temp   = $p2_sc_swari;
    $p2_sc_swari_sagaku = $p2_sc_swari;
    $p2_sc_swari        = number_format(($p2_sc_swari / $tani), $keta);
}
if ($p2_ym >= 201001) {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='試修仕入割引再計算'", $p2_ym);
} else {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='試修仕入割引'", $p2_ym);
}
if (getUniResult($query, $p2_s_swari) < 1) {
    $p2_s_swari         = 0;    // 検索失敗
    $p2_s_swari_sagaku  = 0;
    $p2_sl_swari        = 0;
    $p2_sl_swari_temp   = 0;
    $p2_ss_swari        = 0;
    $p2_ss_swari_temp   = 0;
    $p2_st_swari        = 0;
    $p2_st_swari_temp   = 0;
} else {
    $p2_s_swari_sagaku  = $p2_s_swari;
    $p2_sl_swari        = $p2_s_swari - $p2_sc_swari_temp;              // リニア試験修理仕入割引を計算
    $p2_sl_swari_temp   = $p2_sl_swari;                                 // リニア試験修理損益計算用（temp)
    
    // 耐久・修理計算(耐久st、修理ss)
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='修理仕入割引再計算'", $p2_ym);
    if (getUniResult($query, $p2_ss_swari) < 1) {
        $p2_ss_swari        = 0;     // 検索失敗
    }
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='耐久仕入割引再計算'", $p2_ym);
    if (getUniResult($query, $p2_st_swari) < 1) {
        $p2_st_swari        = 0;     // 検索失敗
    }
    // 配賦労務費差額計算
    $p2_ss_swari_temp  = $p2_ss_swari;
    $p2_st_swari_temp  = $p2_st_swari;
    $p2_s_swari        = number_format(($p2_s_swari / $tani), $keta);
    $p2_ss_swari       = number_format(($p2_ss_swari / $tani), $keta);
    $p2_st_swari       = number_format(($p2_st_swari / $tani), $keta);
}
    ///// 今期累計
if ($yyyymm >= 201004) {
    $query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='カプラ試修仕入割引再計算'", $str_ym, $yyyymm);
    if (getUniResult($query, $rui_sc_swari) < 1) {
        $rui_sc_swari        = 0;                           // 検索失敗
        $rui_sc_swari_temp   = 0;
        $rui_sc_swari_sagaku = 0;
    } else {
        $rui_sc_swari_temp   = $rui_sc_swari;
        $rui_sc_swari_sagaku = $rui_sc_swari;
        $rui_sc_swari        = number_format(($rui_sc_swari / $tani), $keta);
    }
} elseif ($yyyymm >= 201001 && $yyyymm <= 201003) {
    $query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=200904 and pl_bs_ym<=200912 and note='カプラ試修仕入割引'");
    if (getUniResult($query, $rui_sc_swari_a) < 1) {
        $rui_sc_swari_a = 0;                          // 検索失敗
    }
    $query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=201001 and pl_bs_ym<=%d and note='カプラ試修仕入割引再計算'", $yyyymm);
    if (getUniResult($query, $rui_sc_swari_b) < 1) {
        $rui_sc_swari_b = 0;                          // 検索失敗
    }
    $rui_sc_swari        = $rui_sc_swari_a + $rui_sc_swari_b;
    $rui_sc_swari_temp   = $rui_sc_swari;
    $rui_sc_swari_sagaku = $rui_sc_swari;
    $rui_sc_swari        = number_format(($rui_sc_swari / $tani), $keta);
} else {
    $query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='カプラ試修仕入割引'", $str_ym, $yyyymm);
    if (getUniResult($query, $rui_sc_swari) < 1) {
        $rui_sc_swari        = 0;     // 検索失敗
        $rui_sc_swari_sagaku = 0;
        $rui_sc_swari_temp   = 0;
    } else {
        $rui_sc_swari_temp   = $rui_sc_swari;
        $rui_sc_swari_sagaku = $rui_sc_swari;
        $rui_sc_swari        = number_format(($rui_sc_swari / $tani), $keta);
    }
}
if ($yyyymm >= 201004) {
    $query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='試修仕入割引再計算'", $str_ym, $yyyymm);
    if (getUniResult($query, $rui_s_swari) < 1) {
        $rui_s_swari         = 0;                           // 検索失敗
        $rui_s_swari_sagaku  = 0;
        $rui_sl_swari        = 0;
        $rui_sl_swari_temp   = 0;
        $rui_ss_swari        = 0;
        $rui_ss_swari_temp   = 0;
        $rui_st_swari        = 0;
        $rui_st_swari_temp   = 0;
    } else {
        $rui_s_swari_sagaku  = $rui_s_swari;
        $rui_sl_swari        = $rui_s_swari - $rui_sc_swari_temp;             // リニア試験修理仕入割引を計算
        $rui_sl_swari_temp   = $rui_sl_swari;                                 // リニア試験修理損益計算用（temp)
        
        // 耐久・修理計算(耐久st、修理ss)
        $query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='修理仕入割引再計算'", $str_ym, $yyyymm);
        if (getUniResult($query, $rui_ss_swari) < 1) {
            $rui_ss_swari        = 0;     // 検索失敗
        }
        $query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='耐久仕入割引再計算'", $str_ym, $yyyymm);
        if (getUniResult($query, $rui_st_swari) < 1) {
            $rui_st_swari        = 0;     // 検索失敗
        }
        // 配賦労務費差額計算
        $rui_ss_swari_temp  = $rui_ss_swari;
        $rui_st_swari_temp  = $rui_st_swari;
        $rui_s_swari        = number_format(($rui_s_swari / $tani), $keta);
        $rui_ss_swari       = number_format(($rui_ss_swari / $tani), $keta);
        $rui_st_swari       = number_format(($rui_st_swari / $tani), $keta);
    }
} elseif ($yyyymm >= 201001 && $yyyymm <= 201003) {
    $query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=200904 and pl_bs_ym<=200912 and note='試修仕入割引'");
    if (getUniResult($query, $rui_s_swari_a) < 1) {
        $rui_s_swari_a = 0;                          // 検索失敗
    }
    $query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=201001 and pl_bs_ym<=%d and note='試修仕入割引再計算'", $yyyymm);
    if (getUniResult($query, $rui_s_swari_b) < 1) {
        $rui_s_swari_b = 0;                          // 検索失敗
    }
    $rui_s_swari         = $rui_s_swari_a + $rui_s_swari_b;
    $rui_s_swari_sagaku  = $rui_s_swari;
    $rui_sl_swari        = $rui_s_swari - $rui_sc_swari_temp;             // リニア試験修理仕入割引を計算
    $rui_sl_swari_temp   = $rui_sl_swari;                                 // リニア試験修理損益計算用（temp)
    $rui_s_swari         = number_format(($rui_s_swari / $tani), $keta);
    $rui_sl_swari        = number_format(($rui_sl_swari / $tani), $keta);
} else {
    $query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='試修仕入割引'", $str_ym, $yyyymm);
    if (getUniResult($query, $rui_s_swari) < 1) {
        $rui_s_swari         = 0;    // 検索失敗
        $rui_s_swari_sagaku  = 0;
        $rui_sl_swari        = 0;
        $rui_sl_swari_temp   = 0;
    } else {
        $rui_s_swari_sagaku  = $rui_s_swari;
        $rui_sl_swari        = $rui_s_swari - $rui_sc_swari_temp;             // リニア試験修理仕入割引を計算
        $rui_sl_swari_temp   = $rui_sl_swari;                                 // リニア試験修理損益計算用（temp)
        $rui_s_swari         = number_format(($rui_s_swari / $tani), $keta);
        $rui_sl_swari        = number_format(($rui_sl_swari / $tani), $keta);
    }
}
/********** 営業外収益のその他 **********/
    ///// 当月
if ($yyyymm >= 201001) {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='カプラ試修営業外収益その他再計算'", $yyyymm);
} else {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='カプラ試修営業外収益その他'", $yyyymm);
}
if (getUniResult($query, $sc_pother) < 1) {
    $sc_pother        = 0;     // 検索失敗
    $sc_pother_sagaku = 0;
    $sc_pother_temp   = 0;
} else {
    if ($yyyymm == 200912) {
        $sc_pother = $sc_pother + 101;
    }
    if ($yyyymm == 201001) {
        $sc_pother = $sc_pother - 4855;
    }
    $sc_pother_temp   = $sc_pother;
    $sc_pother_sagaku = $sc_pother;
    $sc_pother        = number_format(($sc_pother / $tani), $keta);
}
if ($yyyymm >= 201001) {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='試修営業外収益その他再計算'", $yyyymm);
} else {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='試修営業外収益その他'", $yyyymm);
}
if (getUniResult($query, $s_pother) < 1) {
    $s_pother         = 0;    // 検索失敗
    $s_pother_sagaku  = 0;
    $sl_pother        = 0;
    $sl_pother_temp   = 0;
    $ss_pother        = 0;
    $ss_pother_temp   = 0;
    $st_pother        = 0;
    $st_pother_temp   = 0;
} else {
    if ($yyyymm == 200912) {
        $s_pother = $s_pother + 722;
    }
    if ($yyyymm == 201001) {
        $s_pother = $s_pother - 29125;
    }
    $s_pother_sagaku  = $s_pother;
    $sl_pother        = $s_pother - $sc_pother_temp;                // リニア試験修理営業外収益その他を計算
    $sl_pother_temp   = $sl_pother;                                 // リニア試験修理損益計算用（temp)
    
    // 耐久・修理計算(耐久st、修理ss)
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='修理営業外収益その他再計算'", $yyyymm);
    if (getUniResult($query, $ss_pother) < 1) {
        $ss_pother        = 0;     // 検索失敗
    }
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='耐久営業外収益その他再計算'", $yyyymm);
    if (getUniResult($query, $st_pother) < 1) {
        $st_pother        = 0;     // 検索失敗
    }
    // 配賦労務費差額計算
    $ss_pother_temp  = $ss_pother;
    $st_pother_temp  = $st_pother;
    $s_pother        = number_format(($s_pother / $tani), $keta);
    $ss_pother       = number_format(($ss_pother / $tani), $keta);
    $st_pother       = number_format(($st_pother / $tani), $keta);
}
    ///// 前月
if ($p1_ym >= 201001) {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='カプラ試修営業外収益その他再計算'", $p1_ym);
} else {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='カプラ試修営業外収益その他'", $p1_ym);
}
if (getUniResult($query, $p1_sc_pother) < 1) {
    $p1_sc_pother        = 0;     // 検索失敗
    $p1_sc_pother_sagaku = 0;
    $p1_sc_pother_temp   = 0;
} else {
    if ($p1_ym == 200912) {
        $p1_sc_pother = $p1_sc_pother + 101;
    }
    if ($p1_ym == 201001) {
        $p1_sc_pother = $p1_sc_pother - 4855;
    }
    $p1_sc_pother_temp   = $p1_sc_pother;
    $p1_sc_pother_sagaku = $p1_sc_pother;
    $p1_sc_pother        = number_format(($p1_sc_pother / $tani), $keta);
}
if ($p1_ym >= 201001) {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='試修営業外収益その他再計算'", $p1_ym);
} else {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='試修営業外収益その他'", $p1_ym);
}
if (getUniResult($query, $p1_s_pother) < 1) {
    $p1_s_pother         = 0;    // 検索失敗
    $p1_s_pother_sagaku  = 0;
    $p1_sl_pother        = 0;
    $p1_sl_pother_temp   = 0;
    $p1_ss_pother        = 0;
    $p1_ss_pother_temp   = 0;
    $p1_st_pother        = 0;
    $p1_st_pother_temp   = 0;
} else {
    if ($p1_ym == 200912) {
        $p1_s_pother = $p1_s_pother + 722;
    }
    if ($p1_ym == 201001) {
        $p1_s_pother = $p1_s_pother - 29125;
    }
    $p1_s_pother_sagaku  = $p1_s_pother;
    $p1_sl_pother        = $p1_s_pother - $p1_sc_pother_temp;             // リニア試験修理営業外収益その他を計算
    $p1_sl_pother_temp   = $p1_sl_pother;                                 // リニア試験修理損益計算用（temp)
    
    // 耐久・修理計算(耐久st、修理ss)
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='修理営業外収益その他再計算'", $p1_ym);
    if (getUniResult($query, $p1_ss_pother) < 1) {
        $p1_ss_pother        = 0;     // 検索失敗
    }
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='耐久営業外収益その他再計算'", $p1_ym);
    if (getUniResult($query, $p1_st_pother) < 1) {
        $p1_st_pother        = 0;     // 検索失敗
    }
    // 配賦労務費差額計算
    $p1_ss_pother_temp  = $p1_ss_pother;
    $p1_st_pother_temp  = $p1_st_pother;
    $p1_s_pother        = number_format(($p1_s_pother / $tani), $keta);
    $p1_ss_pother       = number_format(($p1_ss_pother / $tani), $keta);
    $p1_st_pother       = number_format(($p1_st_pother / $tani), $keta);
}
    ///// 前前月
if ($p2_ym >= 201001) {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='カプラ試修営業外収益その他再計算'", $p2_ym);
} else {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='カプラ試修営業外収益その他'", $p2_ym);
}
if (getUniResult($query, $p2_sc_pother) < 1) {
    $p2_sc_pother        = 0;     // 検索失敗
    $p2_sc_pother_sagaku = 0;
    $p2_sc_pother_temp   = 0;
} else {
    if ($p2_ym == 200912) {
        $p2_sc_pother = $p2_sc_pother + 101;
    }
    if ($p2_ym == 201001) {
        $p2_sc_pother = $p2_sc_pother - 4855;
    }
    $p2_sc_pother_temp   = $p2_sc_pother;
    $p2_sc_pother_sagaku = $p2_sc_pother;
    $p2_sc_pother        = number_format(($p2_sc_pother / $tani), $keta);
}
if ($p2_ym >= 201001) {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='試修営業外収益その他再計算'", $p2_ym);
} else {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='試修営業外収益その他'", $p2_ym);
}
if (getUniResult($query, $p2_s_pother) < 1) {
    $p2_s_pother         = 0;    // 検索失敗
    $p2_s_pother_sagaku  = 0;
    $p2_sl_pother        = 0;
    $p2_sl_pother_temp   = 0;
    $p2_ss_pother        = 0;
    $p2_ss_pother_temp   = 0;
    $p2_st_pother        = 0;
    $p2_st_pother_temp   = 0;
} else {
    if ($p2_ym == 200912) {
        $p2_s_pother = $p2_s_pother + 722;
    }
    if ($p2_ym == 201001) {
        $p2_s_pother = $p2_s_pother - 29125;
    }
    $p2_s_pother_sagaku  = $p2_s_pother;
    $p2_sl_pother        = $p2_s_pother - $p2_sc_pother_temp;             // リニア試験修理営業外収益その他を計算
    $p2_sl_pother_temp   = $p2_sl_pother;                                 // リニア試験修理損益計算用（temp)
    
    // 耐久・修理計算(耐久st、修理ss)
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='修理営業外収益その他再計算'", $p2_ym);
    if (getUniResult($query, $p2_ss_pother) < 1) {
        $p2_ss_pother        = 0;     // 検索失敗
    }
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='耐久営業外収益その他再計算'", $p2_ym);
    if (getUniResult($query, $p2_st_pother) < 1) {
        $p2_st_pother        = 0;     // 検索失敗
    }
    // 配賦労務費差額計算
    $p2_ss_pother_temp  = $p2_ss_pother;
    $p2_st_pother_temp  = $p2_st_pother;
    $p2_s_pother        = number_format(($p2_s_pother / $tani), $keta);
    $p2_ss_pother       = number_format(($p2_ss_pother / $tani), $keta);
    $p2_st_pother       = number_format(($p2_st_pother / $tani), $keta);
}
    ///// 今期累計
if ($yyyymm >= 201004) {
    $query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='カプラ試修営業外収益その他再計算'", $str_ym, $yyyymm);
    if (getUniResult($query, $rui_sc_pother) < 1) {
        $rui_sc_pother        = 0;                          // 検索失敗
        $rui_sc_pother_temp   = 0;
        $rui_sc_pother_sagaku = 0;
    } else {
        $rui_sc_pother_temp   = $rui_sc_pother;
        $rui_sc_pother_sagaku = $rui_sc_pother;
        $rui_sc_pother        = number_format(($rui_sc_pother / $tani), $keta);
    }
} elseif ($yyyymm >= 201001 && $yyyymm <= 201003) {
    $query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=200904 and pl_bs_ym<=200912 and note='カプラ試修営業外収益その他'");
    if (getUniResult($query, $rui_sc_pother_a) < 1) {
        $rui_sc_pother_a = 0;                          // 検索失敗
    }
    $query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=201001 and pl_bs_ym<=%d and note='カプラ試修営業外収益その他再計算'", $yyyymm);
    if (getUniResult($query, $rui_sc_pother_b) < 1) {
        $rui_sc_pother_b = 0;                          // 検索失敗
    }
    $rui_sc_pother = $rui_sc_pother_a + $rui_sc_pother_b;
    
    if ($yyyymm >= 200912 && $yyyymm <= 201003) {
        $rui_sc_pother = $rui_sc_pother + 101;
    }
    if ($yyyymm >= 201001 && $yyyymm <= 201003) {
        $rui_sc_pother = $rui_sc_pother - 4855;
    }
    $rui_sc_pother_temp   = $rui_sc_pother;
    $rui_sc_pother_sagaku = $rui_sc_pother;
    $rui_sc_pother        = number_format(($rui_sc_pother / $tani), $keta);
} else {
    $query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='カプラ試修営業外収益その他'", $str_ym, $yyyymm);
    if (getUniResult($query, $rui_sc_pother) < 1) {
        $rui_sc_pother        = 0;     // 検索失敗
        $rui_sc_pother_sagaku = 0;
        $rui_sc_pother_temp   = 0;
    } else {
        if ($yyyymm >= 200912 && $yyyymm <= 201003) {
            $rui_sc_pother = $rui_sc_pother + 101;
        }
        if ($yyyymm >= 201001 && $yyyymm <= 201003) {
            $rui_sc_pother = $rui_sc_pother - 4855;
        }
        $rui_sc_pother_temp   = $rui_sc_pother;
        $rui_sc_pother_sagaku = $rui_sc_pother;
        $rui_sc_pother        = number_format(($rui_sc_pother / $tani), $keta);
    }
}
if ($yyyymm >= 201004) {
    $query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='試修営業外収益その他再計算'", $str_ym, $yyyymm);
    if (getUniResult($query, $rui_s_pother) < 1) {
        $rui_s_pother         = 0;                          // 検索失敗
        $rui_s_pother_sagaku  = 0;
        $rui_sl_pother        = 0;
        $rui_sl_pother_temp   = 0;
        $rui_ss_pother        = 0;
        $rui_ss_pother_temp   = 0;
        $rui_st_pother        = 0;
        $rui_st_pother_temp   = 0;
    } else {
        $rui_s_pother_sagaku  = $rui_s_pother;
        $rui_sl_pother        = $rui_s_pother - $rui_sc_pother_temp;           // リニア試験修理営業外収益その他を計算
        $rui_sl_pother_temp   = $rui_sl_pother;                                // リニア試験修理損益計算用（temp)
        
        // 耐久・修理計算(耐久st、修理ss)
        $query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='修理営業外収益その他再計算'", $str_ym, $yyyymm);
        if (getUniResult($query, $rui_ss_pother) < 1) {
            $rui_ss_pother        = 0;     // 検索失敗
        }
        $query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='耐久営業外収益その他再計算'", $str_ym, $yyyymm);
        if (getUniResult($query, $rui_st_pother) < 1) {
            $rui_st_pother        = 0;     // 検索失敗
        }
        // 配賦労務費差額計算
        $rui_ss_pother_temp  = $rui_ss_pother;
        $rui_st_pother_temp  = $rui_st_pother;
        $rui_s_pother        = number_format(($rui_s_pother / $tani), $keta);
        $rui_ss_pother       = number_format(($rui_ss_pother / $tani), $keta);
        $rui_st_pother       = number_format(($rui_st_pother / $tani), $keta);
    }
} elseif ($yyyymm >= 201001 && $yyyymm <= 201003) {
    $query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=200904 and pl_bs_ym<=200912 and note='試修営業外収益その他'");
    if (getUniResult($query, $rui_s_pother_a) < 1) {
        $rui_s_pother_a = 0;                          // 検索失敗
    }
    $query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=201001 and pl_bs_ym<=%d and note='試修営業外収益その他再計算'", $yyyymm);
    if (getUniResult($query, $rui_s_pother_b) < 1) {
        $rui_s_pother_b = 0;                          // 検索失敗
    }
    $rui_s_pother = $rui_s_pother_a + $rui_s_pother_b;
    
    if ($yyyymm >= 200912 && $yyyymm <= 201003) {
        $rui_s_pother = $rui_s_pother + 722;
    }
    if ($yyyymm >= 201001 && $yyyymm <= 201003) {
        $rui_s_pother = $rui_s_pother - 29125;
    }
    $rui_s_pother_sagaku  = $rui_s_pother;
    $rui_sl_pother        = $rui_s_pother - $rui_sc_pother_temp;           // リニア試験修理営業外収益その他を計算
    $rui_sl_pother_temp   = $rui_sl_pother;                                // リニア試験修理損益計算用（temp)
    $rui_s_pother         = number_format(($rui_s_pother / $tani), $keta);
    $rui_sl_pother        = number_format(($rui_sl_pother / $tani), $keta);
} else {
    $query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='試修営業外収益その他'", $str_ym, $yyyymm);
    if (getUniResult($query, $rui_s_pother) < 1) {
        $rui_s_pother         = 0;    // 検索失敗
        $rui_s_pother_sagaku  = 0;
        $rui_sl_pother        = 0;
        $rui_sl_pother_temp   = 0;
    } else {
        if ($yyyymm >= 200912 && $yyyymm <= 201003) {
            $rui_s_pother = $rui_s_pother + 722;
        }
        if ($yyyymm >= 201001 && $yyyymm <= 201003) {
            $rui_s_pother = $rui_s_pother - 29125;
        }
        $rui_s_pother_sagaku  = $rui_s_pother;
        $rui_sl_pother        = $rui_s_pother - $rui_sc_pother_temp;           // リニア試験修理営業外収益その他を計算
        $rui_sl_pother_temp   = $rui_sl_pother;                                // リニア試験修理損益計算用（temp)
        $rui_s_pother         = number_format(($rui_s_pother / $tani), $keta);
        $rui_sl_pother        = number_format(($rui_sl_pother / $tani), $keta);
    }
}
/********** 営業外収益の合計 **********/
    ///// 試験・修理
$p2_s_nonope_profit_sum         = $p2_s_gyoumu_sagaku + $p2_s_swari_sagaku + $p2_s_pother_sagaku;
$p2_s_nonope_profit_sum_sagaku  = $p2_s_nonope_profit_sum;
// 耐久・修理計算(耐久st、修理ss)
$p2_ss_nonope_profit_sum        = $p2_ss_gyoumu_temp + $p2_ss_swari_temp + $p2_ss_pother_temp;      // 修理営業外収益の合計計算
$p2_ss_nonope_profit_sum_temp   = $p2_ss_nonope_profit_sum;                                         // 修理損益計算用(temp)
$p2_st_nonope_profit_sum        = $p2_st_gyoumu_temp + $p2_st_swari_temp + $p2_st_pother_temp;      // 耐久営業外収益の合計計算
$p2_st_nonope_profit_sum_temp   = $p2_st_nonope_profit_sum;                                         // 耐久損益計算用(temp)
$p2_s_nonope_profit_sum         = number_format(($p2_s_nonope_profit_sum / $tani), $keta);
$p2_ss_nonope_profit_sum        = number_format(($p2_ss_nonope_profit_sum / $tani), $keta);
$p2_st_nonope_profit_sum        = number_format(($p2_st_nonope_profit_sum / $tani), $keta);

$p1_s_nonope_profit_sum         = $p1_s_gyoumu_sagaku + $p1_s_swari_sagaku + $p1_s_pother_sagaku;
$p1_s_nonope_profit_sum_sagaku  = $p1_s_nonope_profit_sum;
// 耐久・修理計算(耐久st、修理ss)
$p1_ss_nonope_profit_sum        = $p1_ss_gyoumu_temp + $p1_ss_swari_temp + $p1_ss_pother_temp;      // 修理営業外収益の合計計算
$p1_ss_nonope_profit_sum_temp   = $p1_ss_nonope_profit_sum;                                         // 修理損益計算用(temp)
$p1_st_nonope_profit_sum        = $p1_st_gyoumu_temp + $p1_st_swari_temp + $p1_st_pother_temp;      // 耐久営業外収益の合計計算
$p1_st_nonope_profit_sum_temp   = $p1_st_nonope_profit_sum;                                         // 耐久損益計算用(temp)
$p1_s_nonope_profit_sum         = number_format(($p1_s_nonope_profit_sum / $tani), $keta);
$p1_ss_nonope_profit_sum        = number_format(($p1_ss_nonope_profit_sum / $tani), $keta);
$p1_st_nonope_profit_sum        = number_format(($p1_st_nonope_profit_sum / $tani), $keta);

$s_nonope_profit_sum            = $s_gyoumu_sagaku + $s_swari_sagaku + $s_pother_sagaku;
$s_nonope_profit_sum_sagaku     = $s_nonope_profit_sum;
// 耐久・修理計算(耐久st、修理ss)
$ss_nonope_profit_sum           = $ss_gyoumu_temp + $ss_swari_temp + $ss_pother_temp;               // 修理営業外収益の合計計算
$ss_nonope_profit_sum_temp      = $ss_nonope_profit_sum;                                            // 修理損益計算用(temp)
$st_nonope_profit_sum           = $st_gyoumu_temp + $st_swari_temp + $st_pother_temp;               // 耐久営業外収益の合計計算
$st_nonope_profit_sum_temp      = $st_nonope_profit_sum;                                            // 耐久損益計算用(temp)
$s_nonope_profit_sum            = number_format(($s_nonope_profit_sum / $tani), $keta);
$ss_nonope_profit_sum           = number_format(($ss_nonope_profit_sum / $tani), $keta);
$st_nonope_profit_sum           = number_format(($st_nonope_profit_sum / $tani), $keta);

$rui_s_nonope_profit_sum        = $rui_s_gyoumu_sagaku + $rui_s_swari_sagaku + $rui_s_pother_sagaku;
$rui_s_nonope_profit_sum_sagaku = $rui_s_nonope_profit_sum;
// 耐久・修理計算(耐久st、修理ss)
$rui_ss_nonope_profit_sum       = $rui_ss_gyoumu_temp + $rui_ss_swari_temp + $rui_ss_pother_temp;   // 修理営業外収益の合計計算
$rui_ss_nonope_profit_sum_temp  = $rui_ss_nonope_profit_sum;                                        // 修理損益計算用(temp)
$rui_st_nonope_profit_sum       = $rui_st_gyoumu_temp + $rui_st_swari_temp + $rui_st_pother_temp;   // 耐久営業外収益の合計計算
$rui_st_nonope_profit_sum_temp  = $rui_st_nonope_profit_sum;                                        // 耐久損益計算用(temp)
$rui_s_nonope_profit_sum        = number_format(($rui_s_nonope_profit_sum / $tani), $keta);
$rui_ss_nonope_profit_sum       = number_format(($rui_ss_nonope_profit_sum / $tani), $keta);
$rui_st_nonope_profit_sum       = number_format(($rui_st_nonope_profit_sum / $tani), $keta);

/********** 営業外費用の支払利息 **********/
    ///// 当月
if ($yyyymm >= 201001) {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='カプラ試修支払利息再計算'", $yyyymm);
} else {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='カプラ試修支払利息'", $yyyymm);
}
if (getUniResult($query, $sc_srisoku) < 1) {
    $sc_srisoku        = 0;     // 検索失敗
    $sc_srisoku_sagaku = 0;
    $sc_srisoku_temp   = 0;
} else {
    $sc_srisoku_temp   = $sc_srisoku;
    $sc_srisoku_sagaku = $sc_srisoku;
    $sc_srisoku        = number_format(($sc_srisoku / $tani), $keta);
}
if ($yyyymm >= 201001) {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='試修支払利息再計算'", $yyyymm);
} else {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='試修支払利息'", $yyyymm);
}
if (getUniResult($query, $s_srisoku) < 1) {
    $s_srisoku         = 0;    // 検索失敗
    $s_srisoku_sagaku  = 0;
    $sl_srisoku        = 0;
    $sl_srisoku_temp   = 0;
    $ss_srisoku        = 0;
    $ss_srisoku_temp   = 0;
    $st_srisoku        = 0;
    $st_srisoku_temp   = 0;
} else {
    $s_srisoku_sagaku  = $s_srisoku;
    $sl_srisoku        = $s_srisoku - $sc_srisoku_temp;               // リニア試験修理支払利息を計算
    $sl_srisoku_temp   = $sl_srisoku;                                 // リニア試験修理損益計算用（temp)
    
    // 耐久・修理計算(耐久st、修理ss)
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='修理支払利息再計算'", $yyyymm);
    if (getUniResult($query, $ss_srisoku) < 1) {
        $ss_srisoku        = 0;     // 検索失敗
    }
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='耐久支払利息再計算'", $yyyymm);
    if (getUniResult($query, $st_srisoku) < 1) {
        $st_srisoku        = 0;     // 検索失敗
    }
    // 配賦労務費差額計算
    $ss_srisoku_temp  = $ss_srisoku;
    $st_srisoku_temp  = $st_srisoku;
    $s_srisoku        = number_format(($s_srisoku / $tani), $keta);
    $ss_srisoku       = number_format(($ss_srisoku / $tani), $keta);
    $st_srisoku       = number_format(($st_srisoku / $tani), $keta);
}
    ///// 前月
if ($p1_ym >= 201001) {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='カプラ試修支払利息再計算'", $p1_ym);
} else {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='カプラ試修支払利息'", $p1_ym);
}
if (getUniResult($query, $p1_sc_srisoku) < 1) {
    $p1_sc_srisoku        = 0;     // 検索失敗
    $p1_sc_srisoku_sagaku = 0;
    $p1_sc_srisoku_temp   = 0;
} else {
    $p1_sc_srisoku_temp   = $p1_sc_srisoku;
    $p1_sc_srisoku_sagaku = $p1_sc_srisoku;
    $p1_sc_srisoku        = number_format(($p1_sc_srisoku / $tani), $keta);
}
if ($p1_ym >= 201001) {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='試修支払利息再計算'", $p1_ym);
} else {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='試修支払利息'", $p1_ym);
}
if (getUniResult($query, $p1_s_srisoku) < 1) {
    $p1_s_srisoku         = 0;    // 検索失敗
    $p1_s_srisoku_sagaku  = 0;
    $p1_sl_srisoku        = 0;
    $p1_sl_srisoku_temp   = 0;
    $p1_ss_srisoku        = 0;
    $p1_ss_srisoku_temp   = 0;
    $p1_st_srisoku        = 0;
    $p1_st_srisoku_temp   = 0;
} else {
    $p1_s_srisoku_sagaku  = $p1_s_srisoku;
    $p1_sl_srisoku        = $p1_s_srisoku - $p1_sc_srisoku_temp;            // リニア試験修理支払利息を計算
    $p1_sl_srisoku_temp   = $p1_sl_srisoku;                                 // リニア試験修理損益計算用（temp)
    
    // 耐久・修理計算(耐久st、修理ss)
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='修理支払利息再計算'", $p1_ym);
    if (getUniResult($query, $p1_ss_srisoku) < 1) {
        $p1_ss_srisoku        = 0;     // 検索失敗
    }
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='耐久支払利息再計算'", $p1_ym);
    if (getUniResult($query, $p1_st_srisoku) < 1) {
        $p1_st_srisoku        = 0;     // 検索失敗
    }
    // 配賦労務費差額計算
    $p1_ss_srisoku_temp  = $p1_ss_srisoku;
    $p1_st_srisoku_temp  = $p1_st_srisoku;
    $p1_s_srisoku        = number_format(($p1_s_srisoku / $tani), $keta);
    $p1_ss_srisoku       = number_format(($p1_ss_srisoku / $tani), $keta);
    $p1_st_srisoku       = number_format(($p1_st_srisoku / $tani), $keta);
}
    ///// 前前月
if ($p2_ym >= 201001) {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='カプラ試修支払利息再計算'", $p2_ym);
} else {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='カプラ試修支払利息'", $p2_ym);
}
if (getUniResult($query, $p2_sc_srisoku) < 1) {
    $p2_sc_srisoku        = 0;     // 検索失敗
    $p2_sc_srisoku_sagaku = 0;
    $p2_sc_srisoku_temp   = 0;
} else {
    $p2_sc_srisoku_temp   = $p2_sc_srisoku;
    $p2_sc_srisoku_sagaku = $p2_sc_srisoku;
    $p2_sc_srisoku        = number_format(($p2_sc_srisoku / $tani), $keta);
}
if ($p2_ym >= 201001) {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='試修支払利息再計算'", $p2_ym);
} else {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='試修支払利息'", $p2_ym);
}
if (getUniResult($query, $p2_s_srisoku) < 1) {
    $p2_s_srisoku         = 0;    // 検索失敗
    $p2_s_srisoku_sagaku  = 0;
    $p2_sl_srisoku        = 0;
    $p2_sl_srisoku_temp   = 0;
    $p2_ss_srisoku        = 0;
    $p2_ss_srisoku_temp   = 0;
    $p2_st_srisoku        = 0;
    $p2_st_srisoku_temp   = 0;
} else {
    $p2_s_srisoku_sagaku  = $p2_s_srisoku;
    $p2_sl_srisoku        = $p2_s_srisoku - $p2_sc_srisoku_temp;            // リニア試験修理支払利息を計算
    $p2_sl_srisoku_temp   = $p2_sl_srisoku;                                 // リニア試験修理損益計算用（temp)
    
    // 耐久・修理計算(耐久st、修理ss)
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='修理支払利息再計算'", $p2_ym);
    if (getUniResult($query, $p2_ss_srisoku) < 1) {
        $p2_ss_srisoku        = 0;     // 検索失敗
    }
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='耐久支払利息再計算'", $p2_ym);
    if (getUniResult($query, $p2_st_srisoku) < 1) {
        $p2_st_srisoku        = 0;     // 検索失敗
    }
    // 配賦労務費差額計算
    $p2_ss_srisoku_temp  = $p2_ss_srisoku;
    $p2_st_srisoku_temp  = $p2_st_srisoku;
    $p2_s_srisoku        = number_format(($p2_s_srisoku / $tani), $keta);
    $p2_ss_srisoku       = number_format(($p2_ss_srisoku / $tani), $keta);
    $p2_st_srisoku       = number_format(($p2_st_srisoku / $tani), $keta);
}
    ///// 今期累計
if ($yyyymm >= 201004) {
    $query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='カプラ試修支払利息再計算'", $str_ym, $yyyymm);
    if (getUniResult($query, $rui_sc_srisoku) < 1) {
        $rui_sc_srisoku        = 0;                           // 検索失敗
        $rui_sc_srisoku_temp   = 0;
        $rui_sc_srisoku_sagaku = 0;
    } else {
        $rui_sc_srisoku_temp   = $rui_sc_srisoku;
        $rui_sc_srisoku_sagaku = $rui_sc_srisoku;
        $rui_sc_srisoku        = number_format(($rui_sc_srisoku / $tani), $keta);
    }
} elseif ($yyyymm >= 201001 && $yyyymm <= 201003) {
    $query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=200904 and pl_bs_ym<=200912 and note='カプラ試修支払利息'");
    if (getUniResult($query, $rui_sc_srisoku_a) < 1) {
        $rui_sc_srisoku_a = 0;                          // 検索失敗
    }
    $query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=201001 and pl_bs_ym<=%d and note='カプラ試修支払利息再計算'", $yyyymm);
    if (getUniResult($query, $rui_sc_srisoku_b) < 1) {
        $rui_sc_srisoku_b = 0;                          // 検索失敗
    }
    $rui_sc_srisoku        = $rui_sc_srisoku_a + $rui_sc_srisoku_b;
    $rui_sc_srisoku_temp   = $rui_sc_srisoku;
    $rui_sc_srisoku_sagaku = $rui_sc_srisoku;
    $rui_sc_srisoku        = number_format(($rui_sc_srisoku / $tani), $keta);
} else {
    $query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='カプラ試修支払利息'", $str_ym, $yyyymm);
    if (getUniResult($query, $rui_sc_srisoku) < 1) {
        $rui_sc_srisoku        = 0;     // 検索失敗
        $rui_sc_srisoku_sagaku = 0;
        $rui_sc_srisoku_temp   = 0;
    } else {
        $rui_sc_srisoku_temp   = $rui_sc_srisoku;
        $rui_sc_srisoku_sagaku = $rui_sc_srisoku;
        $rui_sc_srisoku        = number_format(($rui_sc_srisoku / $tani), $keta);
    }
}
if ($yyyymm >= 201004) {
    $query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='試修支払利息再計算'", $str_ym, $yyyymm);
    if (getUniResult($query, $rui_s_srisoku) < 1) {
        $rui_s_srisoku         = 0;                           // 検索失敗
        $rui_s_srisoku_sagaku  = 0;
        $rui_sl_srisoku        = 0;
        $rui_sl_srisoku_temp   = 0;
        $rui_ss_srisoku        = 0;
        $rui_ss_srisoku_temp   = 0;
        $rui_st_srisoku        = 0;
        $rui_st_srisoku_temp   = 0;
    } else {
        $rui_s_srisoku_sagaku  = $rui_s_srisoku;
        $rui_sl_srisoku        = $rui_s_srisoku - $rui_sc_srisoku_temp;           // リニア試験修理支払利息を計算
        $rui_sl_srisoku_temp   = $rui_sl_srisoku;                                 // リニア試験修理損益計算用（temp)
        
        // 耐久・修理計算(耐久st、修理ss)
        $query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='修理支払利息再計算'", $str_ym, $yyyymm);
        if (getUniResult($query, $rui_ss_srisoku) < 1) {
            $rui_ss_srisoku        = 0;     // 検索失敗
        }
        $query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='耐久支払利息再計算'", $str_ym, $yyyymm);
        if (getUniResult($query, $rui_st_srisoku) < 1) {
            $rui_st_srisoku        = 0;     // 検索失敗
        }
        // 配賦労務費差額計算
        $rui_ss_srisoku_temp  = $rui_ss_srisoku;
        $rui_st_srisoku_temp  = $rui_st_srisoku;
        $rui_s_srisoku        = number_format(($rui_s_srisoku / $tani), $keta);
        $rui_ss_srisoku       = number_format(($rui_ss_srisoku / $tani), $keta);
        $rui_st_srisoku       = number_format(($rui_st_srisoku / $tani), $keta);
    }
} elseif ($yyyymm >= 201001 && $yyyymm <= 201003) {
    $query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=200904 and pl_bs_ym<=200912 and note='試修支払利息'");
    if (getUniResult($query, $rui_s_srisoku_a) < 1) {
        $rui_s_srisoku_a = 0;                          // 検索失敗
    }
    $query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=201001 and pl_bs_ym<=%d and note='試修支払利息再計算'", $yyyymm);
    if (getUniResult($query, $rui_s_srisoku_b) < 1) {
        $rui_s_srisoku_b = 0;                          // 検索失敗
    }
    $rui_s_srisoku = $rui_s_srisoku_a + $rui_s_srisoku_b;
    $rui_s_srisoku_sagaku  = $rui_s_srisoku;
    $rui_sl_srisoku        = $rui_s_srisoku - $rui_sc_srisoku_temp;           // リニア試験修理支払利息を計算
    $rui_sl_srisoku_temp   = $rui_sl_srisoku;                                 // リニア試験修理損益計算用（temp)
    $rui_s_srisoku         = number_format(($rui_s_srisoku / $tani), $keta);
    $rui_sl_srisoku        = number_format(($rui_sl_srisoku / $tani), $keta);
} else {
    $query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='試修支払利息'", $str_ym, $yyyymm);
    if (getUniResult($query, $rui_s_srisoku) < 1) {
        $rui_s_srisoku         = 0;    // 検索失敗
        $rui_s_srisoku_sagaku  = 0;
        $rui_sl_srisoku        = 0;
        $rui_sl_srisoku_temp   = 0;
    } else {
        $rui_s_srisoku_sagaku  = $rui_s_srisoku;
        $rui_sl_srisoku        = $rui_s_srisoku - $rui_sc_srisoku_temp;           // リニア試験修理支払利息を計算
        $rui_sl_srisoku_temp   = $rui_sl_srisoku;                                 // リニア試験修理損益計算用（temp)
        $rui_s_srisoku         = number_format(($rui_s_srisoku / $tani), $keta);
        $rui_sl_srisoku        = number_format(($rui_sl_srisoku / $tani), $keta);
    }
}
/********** 営業外費用のその他 **********/
    ///// 当月
if ($yyyymm >= 201001) {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='カプラ試修営業外費用その他再計算'", $yyyymm);
} else {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='カプラ試修営業外費用その他'", $yyyymm);
}
if (getUniResult($query, $sc_lother) < 1) {
    $sc_lother        = 0;     // 検索失敗
    $sc_lother_sagaku = 0;
    $sc_lother_temp   = 0;
} else {
    $sc_lother_temp   = $sc_lother;
    $sc_lother_sagaku = $sc_lother;
    $sc_lother        = number_format(($sc_lother / $tani), $keta);
}
if ($yyyymm >= 201001) {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='試修営業外費用その他再計算'", $yyyymm);
} else {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='試修営業外費用その他'", $yyyymm);
}
if (getUniResult($query, $s_lother) < 1) {
    $s_lother         = 0;    // 検索失敗
    $s_lother_sagaku  = 0;
    $sl_lother        = 0;
    $sl_lother_temp   = 0;
    $ss_lother        = 0;
    $ss_lother_temp   = 0;
    $st_lother        = 0;
    $st_lother_temp   = 0;
} else {
    $s_lother_sagaku  = $s_lother;
    $sl_lother        = $s_lother - $sc_lother_temp;                // リニア試験修理営業外費用その他を計算
    $sl_lother_temp   = $sl_lother;                                 // リニア試験修理損益計算用（temp)
    
    // 耐久・修理計算(耐久st、修理ss)
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='修理営業外費用その他再計算'", $yyyymm);
    if (getUniResult($query, $ss_lother) < 1) {
        $ss_lother        = 0;     // 検索失敗
    }
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='耐久営業外費用その他再計算'", $yyyymm);
    if (getUniResult($query, $st_lother) < 1) {
        $st_lother        = 0;     // 検索失敗
    }
    // 配賦労務費差額計算
    $ss_lother_temp  = $ss_lother;
    $st_lother_temp  = $st_lother;
    $s_lother        = number_format(($s_lother / $tani), $keta);
    $ss_lother       = number_format(($ss_lother / $tani), $keta);
    $st_lother       = number_format(($st_lother / $tani), $keta);
}
    ///// 前月
if ($p1_ym >= 201001) {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='カプラ試修営業外費用その他再計算'", $p1_ym);
} else {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='カプラ試修営業外費用その他'", $p1_ym);
}
if (getUniResult($query, $p1_sc_lother) < 1) {
    $p1_sc_lother        = 0;     // 検索失敗
    $p1_sc_lother_sagaku = 0;
    $p1_sc_lother_temp   = 0;
} else {
    $p1_sc_lother_temp   = $p1_sc_lother;
    $p1_sc_lother_sagaku = $p1_sc_lother;
    $p1_sc_lother        = number_format(($p1_sc_lother / $tani), $keta);
}
if ($p1_ym >= 201001) {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='試修営業外費用その他再計算'", $p1_ym);
} else {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='試修営業外費用その他'", $p1_ym);
}
if (getUniResult($query, $p1_s_lother) < 1) {
    $p1_s_lother         = 0;    // 検索失敗
    $p1_s_lother_sagaku  = 0;
    $p1_sl_lother        = 0;
    $p1_sl_lother_temp   = 0;
    $p1_ss_lother        = 0;
    $p1_ss_lother_temp   = 0;
    $p1_st_lother        = 0;
    $p1_st_lother_temp   = 0;
} else {
    $p1_s_lother_sagaku  = $p1_s_lother;
    $p1_sl_lother        = $p1_s_lother - $p1_sc_lother_temp;             // リニア試験修理営業外費用その他を計算
    $p1_sl_lother_temp   = $p1_sl_lother;                                 // リニア試験修理損益計算用（temp)
    
    // 耐久・修理計算(耐久st、修理ss)
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='修理営業外費用その他再計算'", $p1_ym);
    if (getUniResult($query, $p1_ss_lother) < 1) {
        $p1_ss_lother        = 0;     // 検索失敗
    }
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='耐久営業外費用その他再計算'", $p1_ym);
    if (getUniResult($query, $p1_st_lother) < 1) {
        $p1_st_lother        = 0;     // 検索失敗
    }
    // 配賦労務費差額計算
    $p1_ss_lother_temp  = $p1_ss_lother;
    $p1_st_lother_temp  = $p1_st_lother;
    $p1_s_lother        = number_format(($p1_s_lother / $tani), $keta);
    $p1_ss_lother       = number_format(($p1_ss_lother / $tani), $keta);
    $p1_st_lother       = number_format(($p1_st_lother / $tani), $keta);
}
    ///// 前前月
if ($p2_ym >= 201001) {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='カプラ試修営業外費用その他再計算'", $p2_ym);
} else {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='カプラ試修営業外費用その他'", $p2_ym);
}
if (getUniResult($query, $p2_sc_lother) < 1) {
    $p2_sc_lother        = 0;     // 検索失敗
    $p2_sc_lother_sagaku = 0;
    $p2_sc_lother_temp   = 0;
} else {
    $p2_sc_lother_temp   = $p2_sc_lother;
    $p2_sc_lother_sagaku = $p2_sc_lother;
    $p2_sc_lother        = number_format(($p2_sc_lother / $tani), $keta);
}
if ($p2_ym >= 201001) {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='試修営業外費用その他再計算'", $p2_ym);
} else {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='試修営業外費用その他'", $p2_ym);
}
if (getUniResult($query, $p2_s_lother) < 1) {
    $p2_s_lother         = 0;    // 検索失敗
    $p2_s_lother_sagaku  = 0;
    $p2_sl_lother        = 0;
    $p2_sl_lother_temp   = 0;
    $p2_ss_lother        = 0;
    $p2_ss_lother_temp   = 0;
    $p2_st_lother        = 0;
    $p2_st_lother_temp   = 0;
} else {
    $p2_s_lother_sagaku  = $p2_s_lother;
    $p2_sl_lother        = $p2_s_lother - $p2_sc_lother_temp;             // リニア試験修理営業外費用その他を計算
    $p2_sl_lother_temp   = $p2_sl_lother;                                 // リニア試験修理損益計算用（temp)
    
    // 耐久・修理計算(耐久st、修理ss)
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='修理営業外費用その他再計算'", $p2_ym);
    if (getUniResult($query, $p2_ss_lother) < 1) {
        $p2_ss_lother        = 0;     // 検索失敗
    }
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='耐久営業外費用その他再計算'", $p2_ym);
    if (getUniResult($query, $p2_st_lother) < 1) {
        $p2_st_lother        = 0;     // 検索失敗
    }
    // 配賦労務費差額計算
    $p2_ss_lother_temp  = $p2_ss_lother;
    $p2_st_lother_temp  = $p2_st_lother;
    $p2_s_lother        = number_format(($p2_s_lother / $tani), $keta);
    $p2_ss_lother       = number_format(($p2_ss_lother / $tani), $keta);
    $p2_st_lother       = number_format(($p2_st_lother / $tani), $keta);
}
    ///// 今期累計
if ($yyyymm >= 201004) {
    $query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='カプラ試修営業外費用その他再計算'", $str_ym, $yyyymm);
    if (getUniResult($query, $rui_sc_lother) < 1) {
        $rui_sc_lother        = 0;                           // 検索失敗
        $rui_sc_lother_temp   = 0;
        $rui_sc_lother_sagaku = 0;
    } else {
        $rui_sc_lother_temp   = $rui_sc_lother;
        $rui_sc_lother_sagaku = $rui_sc_lother;
        $rui_sc_lother        = number_format(($rui_sc_lother / $tani), $keta);
    }
} elseif ($yyyymm >= 201001 && $yyyymm <= 201003) {
    $query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=200904 and pl_bs_ym<=200912 and note='カプラ試修営業外費用その他'");
    if (getUniResult($query, $rui_sc_lother_a) < 1) {
        $rui_sc_lother_a = 0;                          // 検索失敗
    }
    $query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=201001 and pl_bs_ym<=%d and note='カプラ試修営業外費用その他再計算'", $yyyymm);
    if (getUniResult($query, $rui_sc_lother_b) < 1) {
        $rui_sc_lother_b = 0;                          // 検索失敗
    }
    $rui_sc_lother        = $rui_sc_lother_a + $rui_sc_lother_b;
    $rui_sc_lother_temp   = $rui_sc_lother;
    $rui_sc_lother_sagaku = $rui_sc_lother;
    $rui_sc_lother        = number_format(($rui_sc_lother / $tani), $keta);
} else {
    $query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='カプラ試修営業外費用その他'", $str_ym, $yyyymm);
    if (getUniResult($query, $rui_sc_lother) < 1) {
        $rui_sc_lother        = 0;     // 検索失敗
        $rui_sc_lother_sagaku = 0;
        $rui_sc_lother_temp   = 0;
    } else {
        $rui_sc_lother_temp   = $rui_sc_lother;
        $rui_sc_lother_sagaku = $rui_sc_lother;
        $rui_sc_lother        = number_format(($rui_sc_lother / $tani), $keta);
    }
}
if ($yyyymm >= 201004) {
    $query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='試修営業外費用その他再計算'", $str_ym, $yyyymm);
    if (getUniResult($query, $rui_s_lother) < 1) {
        $rui_s_lother         = 0;                           // 検索失敗
        $rui_s_lother_sagaku  = 0;
        $rui_sl_lother        = 0;
        $rui_sl_lother_temp   = 0;
        $rui_ss_lother        = 0;
        $rui_ss_lother_temp   = 0;
        $rui_st_lother        = 0;
        $rui_st_lother_temp   = 0;
    } else {
        $rui_s_lother_sagaku  = $rui_s_lother;
        $rui_sl_lother        = $rui_s_lother - $rui_sc_lother_temp;            // リニア試験修理営業外費用その他を計算
        $rui_sl_lother_temp   = $rui_sl_lother;                                 // リニア試験修理損益計算用（temp)
        
        // 耐久・修理計算(耐久st、修理ss)
        $query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='修理営業外費用その他再計算'", $str_ym, $yyyymm);
        if (getUniResult($query, $rui_ss_lother) < 1) {
            $rui_ss_lother        = 0;     // 検索失敗
        }
        $query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='耐久営業外費用その他再計算'", $str_ym, $yyyymm);
        if (getUniResult($query, $rui_st_lother) < 1) {
            $rui_st_lother        = 0;     // 検索失敗
        }
        // 配賦労務費差額計算
        $rui_ss_lother_temp  = $rui_ss_lother;
        $rui_st_lother_temp  = $rui_st_lother;
        $rui_s_lother        = number_format(($rui_s_lother / $tani), $keta);
        $rui_ss_lother       = number_format(($rui_ss_lother / $tani), $keta);
        $rui_st_lother       = number_format(($rui_st_lother / $tani), $keta);
    }
} elseif ($yyyymm >= 201001 && $yyyymm <= 201003) {
    $query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=200904 and pl_bs_ym<=200912 and note='試修営業外費用その他'");
    if (getUniResult($query, $rui_s_lother_a) < 1) {
        $rui_s_lother_a = 0;                          // 検索失敗
    }
    $query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=201001 and pl_bs_ym<=%d and note='試修営業外費用その他再計算'", $yyyymm);
    if (getUniResult($query, $rui_s_lother_b) < 1) {
        $rui_s_lother_b = 0;                          // 検索失敗
    }
    $rui_s_lother = $rui_s_lother_a + $rui_s_lother_b;
    $rui_s_lother_sagaku  = $rui_s_lother;
    $rui_sl_lother        = $rui_s_lother - $rui_sc_lother_temp;            // リニア試験修理営業外費用その他を計算
    $rui_sl_lother_temp   = $rui_sl_lother;                                 // リニア試験修理損益計算用（temp)
    $rui_s_lother         = number_format(($rui_s_lother / $tani), $keta);
    $rui_sl_lother        = number_format(($rui_sl_lother / $tani), $keta);
} else {
    $query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='試修営業外費用その他'", $str_ym, $yyyymm);
    if (getUniResult($query, $rui_s_lother) < 1) {
        $rui_s_lother         = 0;    // 検索失敗
        $rui_s_lother_sagaku  = 0;
        $rui_sl_lother        = 0;
        $rui_sl_lother_temp   = 0;
    } else {
        $rui_s_lother_sagaku  = $rui_s_lother;
        $rui_sl_lother        = $rui_s_lother - $rui_sc_lother_temp;            // リニア試験修理営業外費用その他を計算
        $rui_sl_lother_temp   = $rui_sl_lother;                                 // リニア試験修理損益計算用（temp)
        $rui_s_lother         = number_format(($rui_s_lother / $tani), $keta);
        $rui_sl_lother        = number_format(($rui_sl_lother / $tani), $keta);
    }
}
/********** 営業外費用の合計 **********/
    ///// 試験・修理
$p2_s_nonope_loss_sum          = $p2_s_srisoku_sagaku + $p2_s_lother_sagaku;
$p2_s_nonope_loss_sum_sagaku   = $p2_s_nonope_loss_sum;
// 耐久・修理計算(耐久st、修理ss)
$p2_ss_nonope_loss_sum         = $p2_ss_srisoku_temp + $p2_ss_lother_temp;      // 修理営業外費用合計計算
$p2_ss_nonope_loss_sum_temp    = $p2_ss_nonope_loss_sum;                        // 修理損益計算用(temp)
$p2_st_nonope_loss_sum         = $p2_st_srisoku_temp + $p2_st_lother_temp;      // 耐久営業外費用合計計算
$p2_st_nonope_loss_sum_temp    = $p2_st_nonope_loss_sum;                        // 耐久損益計算用(temp)
$p2_s_nonope_loss_sum          = number_format(($p2_s_nonope_loss_sum / $tani), $keta);
$p2_ss_nonope_loss_sum         = number_format(($p2_ss_nonope_loss_sum / $tani), $keta);
$p2_st_nonope_loss_sum         = number_format(($p2_st_nonope_loss_sum / $tani), $keta);

$p1_s_nonope_loss_sum          = $p1_s_srisoku_sagaku + $p1_s_lother_sagaku;
$p1_s_nonope_loss_sum_sagaku   = $p1_s_nonope_loss_sum;
// 耐久・修理計算(耐久st、修理ss)
$p1_ss_nonope_loss_sum         = $p1_ss_srisoku_temp + $p1_ss_lother_temp;      // 修理営業外費用合計計算
$p1_ss_nonope_loss_sum_temp    = $p1_ss_nonope_loss_sum;                        // 修理損益計算用(temp)
$p1_st_nonope_loss_sum         = $p1_st_srisoku_temp + $p1_st_lother_temp;      // 耐久営業外費用合計計算
$p1_st_nonope_loss_sum_temp    = $p1_st_nonope_loss_sum;                        // 耐久損益計算用(temp)
$p1_s_nonope_loss_sum          = number_format(($p1_s_nonope_loss_sum / $tani), $keta);
$p1_ss_nonope_loss_sum         = number_format(($p1_ss_nonope_loss_sum / $tani), $keta);
$p1_st_nonope_loss_sum         = number_format(($p1_st_nonope_loss_sum / $tani), $keta);

$s_nonope_loss_sum             = $s_srisoku_sagaku + $s_lother_sagaku;
$s_nonope_loss_sum_sagaku      = $s_nonope_loss_sum;
// 耐久・修理計算(耐久st、修理ss)
$ss_nonope_loss_sum            = $ss_srisoku_temp + $ss_lother_temp;            // 修理営業外費用合計計算
$ss_nonope_loss_sum_temp       = $ss_nonope_loss_sum;                           // 修理損益計算用(temp)
$st_nonope_loss_sum            = $st_srisoku_temp + $st_lother_temp;            // 耐久営業外費用合計計算
$st_nonope_loss_sum_temp       = $st_nonope_loss_sum;                           // 耐久損益計算用(temp)
$s_nonope_loss_sum             = number_format(($s_nonope_loss_sum / $tani), $keta);
$ss_nonope_loss_sum            = number_format(($ss_nonope_loss_sum / $tani), $keta);
$st_nonope_loss_sum            = number_format(($st_nonope_loss_sum / $tani), $keta);

$rui_s_nonope_loss_sum         = $rui_s_srisoku_sagaku + $rui_s_lother_sagaku;
$rui_s_nonope_loss_sum_sagaku  = $rui_s_nonope_loss_sum;
// 耐久・修理計算(耐久st、修理ss)
$rui_ss_nonope_loss_sum        = $rui_ss_srisoku_temp + $rui_ss_lother_temp;    // 修理営業外費用合計計算
$rui_ss_nonope_loss_sum_temp   = $rui_ss_nonope_loss_sum;                       // 修理損益計算用(temp)
$rui_st_nonope_loss_sum        = $rui_st_srisoku_temp + $rui_st_lother_temp;    // 耐久営業外費用合計計算
$rui_st_nonope_loss_sum_temp   = $rui_st_nonope_loss_sum;                       // 耐久損益計算用(temp)
$rui_s_nonope_loss_sum         = number_format(($rui_s_nonope_loss_sum / $tani), $keta);
$rui_ss_nonope_loss_sum        = number_format(($rui_ss_nonope_loss_sum / $tani), $keta);
$rui_st_nonope_loss_sum        = number_format(($rui_st_nonope_loss_sum / $tani), $keta);

/********** 経常利益 **********/
    ///// 試験・修理
$p2_s_current_profit         = $p2_s_ope_profit_sagaku + $p2_s_nonope_profit_sum_sagaku - $p2_s_nonope_loss_sum_sagaku;
$p2_s_current_profit_sagaku  = $p2_s_current_profit;
// 耐久・修理計算(耐久st、修理ss)
$p2_ss_current_profit        = $p2_ss_ope_profit_temp + $p2_ss_nonope_profit_sum_temp - $p2_ss_nonope_loss_sum_temp; // 修理経常利益計算
$p2_st_current_profit        = $p2_st_ope_profit_temp + $p2_st_nonope_profit_sum_temp - $p2_st_nonope_loss_sum_temp; // 耐久経常利益計算
$p2_s_current_profit         = $p2_s_current_profit + $p2_sc_uri_sagaku - $p2_sc_metarial_sagaku;   // カプラ試修売上高を加味（sagakuの下 リニアからマイナスしてしまう為）
if ($p2_ym == 200912) {
    $p2_s_current_profit = $p2_s_current_profit + 1409708;
}
if ($p2_ym == 200912) {
    $p2_sl_current_profit = $p2_sl_current_profit + 1195898;
}
if ($p2_ym == 200912) {
    $p2_sc_current_profit = $p2_sc_current_profit + 213810;
}
$p2_s_current_profit         = number_format(($p2_s_current_profit / $tani), $keta);
$p2_ss_current_profit        = number_format(($p2_ss_current_profit / $tani), $keta);
$p2_st_current_profit        = number_format(($p2_st_current_profit / $tani), $keta);

$p1_s_current_profit         = $p1_s_ope_profit_sagaku + $p1_s_nonope_profit_sum_sagaku - $p1_s_nonope_loss_sum_sagaku;
$p1_s_current_profit_sagaku  = $p1_s_current_profit;
// 耐久・修理計算(耐久st、修理ss)
$p1_ss_current_profit        = $p1_ss_ope_profit_temp + $p1_ss_nonope_profit_sum_temp - $p1_ss_nonope_loss_sum_temp; // 修理経常利益計算
$p1_st_current_profit        = $p1_st_ope_profit_temp + $p1_st_nonope_profit_sum_temp - $p1_st_nonope_loss_sum_temp; // 耐久経常利益計算
$p1_s_current_profit         = $p1_s_current_profit + $p1_sc_uri_sagaku - $p1_sc_metarial_sagaku;   // カプラ試修売上高を加味（sagakuの下 リニアからマイナスしてしまう為）
if ($p1_ym == 200912) {
    $p1_s_current_profit = $p1_s_current_profit + 1409708;
}
if ($p1_ym == 200912) {
    $p1_sl_current_profit = $p1_sl_current_profit + 1195898;
}
if ($p1_ym == 200912) {
    $p1_sc_current_profit = $p1_sc_current_profit + 213810;
}
$p1_s_current_profit         = number_format(($p1_s_current_profit / $tani), $keta);
$p1_ss_current_profit        = number_format(($p1_ss_current_profit / $tani), $keta);
$p1_st_current_profit        = number_format(($p1_st_current_profit / $tani), $keta);

$s_current_profit            = $s_ope_profit_sagaku + $s_nonope_profit_sum_sagaku - $s_nonope_loss_sum_sagaku;
$s_current_profit_sagaku     = $s_current_profit;
// 耐久・修理計算(耐久st、修理ss)
$ss_current_profit           = $ss_ope_profit_temp + $ss_nonope_profit_sum_temp - $ss_nonope_loss_sum_temp; // 修理経常利益計算
$st_current_profit           = $st_ope_profit_temp + $st_nonope_profit_sum_temp - $st_nonope_loss_sum_temp; // 耐久経常利益計算
$s_current_profit            = $s_current_profit + $sc_uri_sagaku - $sc_metarial_sagaku;   // カプラ試修売上高を加味（sagakuの下 リニアからマイナスしてしまう為）
if ($yyyymm == 200912) {
    $s_current_profit = $s_current_profit + 1409708;
}
if ($yyyymm == 200912) {
    $sc_current_profit = $sc_current_profit + 213810;
}
if ($yyyymm == 200912) {
    $sl_current_profit = $sl_current_profit + 1195898;
}
$s_current_profit            = number_format(($s_current_profit / $tani), $keta);
$ss_current_profit           = number_format(($ss_current_profit / $tani), $keta);
$st_current_profit           = number_format(($st_current_profit / $tani), $keta);

$rui_s_current_profit        = $rui_s_ope_profit_sagaku + $rui_s_nonope_profit_sum_sagaku - $rui_s_nonope_loss_sum_sagaku;
$rui_s_current_profit_sagaku = $rui_s_current_profit;
// 耐久・修理計算(耐久st、修理ss)
$rui_ss_current_profit       = $rui_ss_ope_profit_temp + $rui_ss_nonope_profit_sum_temp - $rui_ss_nonope_loss_sum_temp; // 修理経常利益計算
$rui_st_current_profit       = $rui_st_ope_profit_temp + $rui_st_nonope_profit_sum_temp - $rui_st_nonope_loss_sum_temp; // 耐久経常利益計算
$rui_s_current_profit        = $rui_s_current_profit + $rui_sc_uri_sagaku - $rui_sc_metarial_sagaku;   // カプラ試修売上高を加味（sagakuの下 リニアからマイナスしてしまう為）
if ($yyyymm >= 200912 && $yyyymm <= 201003) {
    $rui_s_current_profit = $rui_s_current_profit + 1409708;
}
if ($yyyymm >= 200912 && $yyyymm <= 201003) {
    $rui_sc_current_profit = $rui_sc_current_profit + 213810;
}
if ($yyyymm >= 200912 && $yyyymm <= 201003) {
    $rui_sl_current_profit = $rui_sl_current_profit + 1195898;
}
$rui_s_current_profit        = number_format(($rui_s_current_profit / $tani), $keta);
$rui_ss_current_profit       = number_format(($rui_ss_current_profit / $tani), $keta);
$rui_st_current_profit       = number_format(($rui_st_current_profit / $tani), $keta);

////////// 特記事項の取得
$query = sprintf("select comment from act_comment_history where pl_bs_ym=%d and item='修理損益計算書'", $yyyymm);
if (getUniResult($query,$comment_ss) <= 0) {
    $comment_ss = "";
}
$query = sprintf("select comment from act_comment_history where pl_bs_ym=%d and item='耐久損益計算書'", $yyyymm);
if (getUniResult($query,$comment_st) <= 0) {
    $comment_st = "";
}
$query = sprintf("select comment from act_comment_history where pl_bs_ym=%d and item='試修全体損益計算書'", $yyyymm);
if (getUniResult($query,$comment_s) <= 0) {
    $comment_s = "";
}

/////////// HTML Header を出力してキャッシュを制御
$menu->out_html_header();
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
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
                        単位
                        <select name='keihi_tani' class='pt10'>
                        <?php
                            if ($tani == 1000)
                                echo "<option value='1000' selected>　千円</option>\n";
                            else
                                echo "<option value='1000'>　千円</option>\n";
                            if ($tani == 1)
                                echo "<option value='1' selected>　　円</option>\n";
                            else
                                echo "<option value='1'>　　円</option>\n";
                            if ($tani == 1000000)
                                echo "<option value='1000000' selected>百万円</option>\n";
                            else
                                echo "<option value='1000000'>百万円</option>\n";
                            if ($tani == 10000)
                                echo "<option value='10000' selected>　万円</option>\n";
                            else
                                echo "<option value='10000'>　万円</option>\n";
                            if($tani == 100000)
                                echo "<option value='100000' selected>十万円</option>\n";
                            else
                                echo "<option value='100000'>十万円</option>\n";
                        ?>
                        </select>
                        少数桁
                        <select name='keihi_keta' class='pt10'>
                        <?php
                            if ($keta == 0)
                                echo "<option value='0' selected>０桁</option>\n";
                            else
                                echo "<option value='0'>０桁</option>\n";
                            if ($keta == 3)
                                echo "<option value='3' selected>３桁</option>\n";
                            else
                                echo "<option value='3'>３桁</option>\n";
                            if ($keta == 6)
                                echo "<option value='6' selected>６桁</option>\n";
                            else
                                echo "<option value='6'>６桁</option>\n";
                            if ($keta == 1)
                                echo "<option value='1' selected>１桁</option>\n";
                            else
                                echo "<option value='1'>１桁</option>\n";
                            if ($keta == 2)
                                echo "<option value='2' selected>２桁</option>\n";
                            else
                                echo "<option value='2'>２桁</option>\n";
                            if ($keta == 4)
                                echo "<option value='4' selected>４桁</option>\n";
                            else
                                echo "<option value='4'>４桁</option>\n";
                            if ($keta == 5)
                                echo "<option value='5' selected>５桁</option>\n";
                            else
                                echo "<option value='5'>５桁</option>\n";
                        ?>
                        </select>
                        <input class='pt10b' type='submit' name='return' value='単位変更'>
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
                    <td rowspan='2' colspan='3' width='200' align='center' class='pt10b' bgcolor='#ffffc6'>項　　　目</td>
                    <!-- <td colspan='4' align='center' class='pt10b' bgcolor='#ffffc6'>リ　ニ　ア</td> -->
                    <td colspan='4' align='center' class='pt10b' bgcolor='#ffffc6'>修　　　理</td>
                    <!-- <td colspan='4' align='center' class='pt10b' bgcolor='#ffffc6'>バ イ モ ル</td> -->
                    <td colspan='4' align='center' class='pt10b' bgcolor='#ffffc6'>耐　　　久</td>
                    <td colspan='4' align='center' class='pt10b' bgcolor='#ffffc6'>合　　　計</td>
                    <td rowspan='2' width='400' align='left' class='pt10b' bgcolor='#ffffc6'>製造間接経費・販管費の配賦基準</td>
                </tr>
                <tr>
                    <td nowrap align='center' class='pt10b' bgcolor='#d6d3ce'><?php echo $p2_ym ?> </td>
                    <td nowrap align='center' class='pt10b' bgcolor='#d6d3ce'><?php echo $p1_ym ?> </td>
                    <td nowrap align='center' class='pt10b' bgcolor='#ffffc6'><?php echo $yyyymm ?></td>
                    <td nowrap align='center' class='pt10b' bgcolor='#ffffc6'>累　計</td>
                    <td nowrap align='center' class='pt10b' bgcolor='#d6d3ce'><?php echo $p2_ym ?> </td>
                    <td nowrap align='center' class='pt10b' bgcolor='#d6d3ce'><?php echo $p1_ym ?> </td>
                    <td nowrap align='center' class='pt10b' bgcolor='#ffffc6'><?php echo $yyyymm ?></td>
                    <td nowrap align='center' class='pt10b' bgcolor='#ffffc6'>累　計</td>
                    <td nowrap align='center' class='pt10b' bgcolor='#d6d3ce'><?php echo $p2_ym ?> </td>
                    <td nowrap align='center' class='pt10b' bgcolor='#d6d3ce'><?php echo $p1_ym ?> </td>
                    <td nowrap align='center' class='pt10b' bgcolor='#ffffc6'><?php echo $yyyymm ?></td>
                    <td nowrap align='center' class='pt10b' bgcolor='#ffffc6'>累　計</td>
                </tr>
                <tr>
                    <td rowspan='11' width='10' align='center' valign='middle' class='pt10b' bgcolor='#ceffce'>営　業　損　益</td>
                    <td colspan='2' nowrap align='center' class='pt10b' bgcolor='#ceffce'>売　上　高</td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo $p2_ss_uri ?>   </td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo $p1_ss_uri ?>   </td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo $ss_uri ?>      </td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo $rui_ss_uri ?>  </td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo $p2_st_uri ?>   </td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo $p1_st_uri ?>   </td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo $st_uri ?>      </td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo $rui_st_uri ?>  </td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo $p2_s_uri ?>   </td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo $p1_s_uri ?>   </td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo $s_uri ?>      </td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo $rui_s_uri ?>  </td>
                    <td nowrap align='left'  class='pt10' bgcolor='#d6d3ce'>実際売上高</td>
                </tr>
                <tr>
                    <td rowspan='6' width='10' align='center' valign='middle' class='pt10b' bgcolor='#ffffc6'>売上原価</td> <!-- 売上原価 -->
                    <td nowrap align='left' class='pt10b' bgcolor='white'>　期首材料仕掛品棚卸高</td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $p2_ss_invent ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $p1_ss_invent ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $ss_invent ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $rui_ss_invent ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $p2_st_invent ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $p1_st_invent ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $st_invent ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $rui_st_invent ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $p2_s_invent ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $p1_s_invent ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $s_invent ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $rui_s_invent ?></td>
                    <td nowrap align='left'  class='pt10'>　</td>
                </tr>
                <tr>
                    <td nowrap align='left' class='pt10b' bgcolor='#e6e6e6'>　材料費(仕入高)</td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo $p2_ss_metarial ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo $p1_ss_metarial ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo $ss_metarial ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo $rui_ss_metarial ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo $p2_st_metarial ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo $p1_st_metarial ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo $st_metarial ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo $rui_st_metarial ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo $p2_s_metarial ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo $p1_s_metarial ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo $s_metarial ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo $rui_s_metarial ?></td>
                    <td nowrap align='left'  class='pt10'>担当者集計の材料費</td>
                </tr>
                <tr>
                    <td nowrap align='left' class='pt10b' bgcolor='white'>　労　　務　　費</td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $p2_ss_roumu ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $p1_ss_roumu ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $ss_roumu ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $rui_ss_roumu ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $p2_st_roumu ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $p1_st_roumu ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $st_roumu ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $rui_st_roumu ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $p2_s_roumu ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $p1_s_roumu ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $s_roumu ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $rui_s_roumu ?></td>
                    <td nowrap align='left'  class='pt10'>当月売上高比</td>
                </tr>
                <tr>
                    <td nowrap align='left' class='pt10b' bgcolor='#e6e6e6'>　経　　　　　費</td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo $p2_ss_expense ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo $p1_ss_expense ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo $ss_expense ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo $rui_ss_expense ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo $p2_st_expense ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo $p1_st_expense ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo $st_expense ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo $rui_st_expense ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo $p2_s_expense ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo $p1_s_expense ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo $s_expense ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo $rui_s_expense ?></td>
                    <td nowrap align='left'  class='pt10'>当月売上高比</td>
                </tr>
                <tr>
                    <td nowrap align='left' class='pt10b' bgcolor='white'>　期末材料仕掛品棚卸高</td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $p2_ss_endinv ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $p1_ss_endinv ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $ss_endinv ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $ss_endinv ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $p2_st_endinv ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $p1_st_endinv ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $st_endinv ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $st_endinv ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $p2_s_endinv ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $p1_s_endinv ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $s_endinv ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $s_endinv ?></td>
                    <td nowrap align='left'  class='pt10'>　</td>
                </tr>
                <tr>
                    <td nowrap align='left' class='pt10b' bgcolor='#ffffc6'>　売　上　原　価</td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo $p2_ss_urigen ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo $p1_ss_urigen ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo $ss_urigen ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo $rui_ss_urigen ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo $p2_st_urigen ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo $p1_st_urigen ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo $st_urigen ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo $rui_st_urigen ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo $p2_s_urigen ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo $p1_s_urigen ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo $s_urigen ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo $rui_s_urigen ?></td>
                    <td nowrap align='left'  class='pt10'>　</td>
                </tr>
                <tr>
                    <td colspan='2' nowrap align='center' class='pt10b' bgcolor='#ceffce'>売　上　総　利　益</td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo $p2_ss_gross_profit ?></td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo $p1_ss_gross_profit ?></td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo $ss_gross_profit ?></td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo $rui_ss_gross_profit ?></td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo $p2_st_gross_profit ?></td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo $p1_st_gross_profit ?></td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo $st_gross_profit ?></td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo $rui_st_gross_profit ?></td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo $p2_s_gross_profit ?></td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo $p1_s_gross_profit ?></td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo $s_gross_profit ?></td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo $rui_s_gross_profit ?></td>
                    <td nowrap align='left'  class='pt10' bgcolor='#d6d3ce'>　</td>  <!-- 余白 -->
                </tr>
                <tr>
                    <td rowspan='3' width='10' align='center' valign='middle' class='pt10b' bgcolor='#ffffc6'></td> <!-- 販管費 -->
                    <td nowrap align='left' class='pt10b' bgcolor='#e6e6e6'>　人　　件　　費</td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo $p2_ss_han_jin ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo $p1_ss_han_jin ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo $ss_han_jin ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo $rui_ss_han_jin ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo $p2_st_han_jin ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo $p1_st_han_jin ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo $st_han_jin ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo $rui_st_han_jin ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo $p2_s_han_jin ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo $p1_s_han_jin ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo $s_han_jin ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo $rui_s_han_jin ?></td>
                    <td nowrap align='left'  class='pt10'>当月売上高比</td>
                </tr>
                <tr>
                    <td nowrap align='left' class='pt10b' bgcolor='white'>　経　　　　　費</td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $p2_ss_han_kei ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $p1_ss_han_kei ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $ss_han_kei ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $rui_ss_han_kei ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $p2_st_han_kei ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $p1_st_han_kei ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $st_han_kei ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $rui_st_han_kei ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $p2_s_han_kei ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $p1_s_han_kei ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $s_han_kei ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $rui_s_han_kei ?></td>
                    <td nowrap align='left'  class='pt10'>当月売上高比</td>
                </tr>
                <tr>
                    <td nowrap align='center' class='pt10b' bgcolor='#ffffc6'>販管費及び一般管理費計</td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo $p2_ss_han_all ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo $p1_ss_han_all ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo $ss_han_all ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo $rui_ss_han_all ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo $p2_st_han_all ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo $p1_st_han_all ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo $st_han_all ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo $rui_st_han_all ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo $p2_s_han_all ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo $p1_s_han_all ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo $s_han_all ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo $rui_s_han_all ?></td>
                    <td nowrap align='left'  class='pt10'>　</td>  <!-- 余白 -->
                </tr>
                <tr>
                    <td colspan='3' nowrap align='center' class='pt10b' bgcolor='#ceffce'>営　　業　　利　　益</td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo $p2_ss_ope_profit ?></td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo $p1_ss_ope_profit ?></td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo $ss_ope_profit ?></td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo $rui_ss_ope_profit ?></td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo $p2_st_ope_profit ?></td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo $p1_st_ope_profit ?></td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo $st_ope_profit ?></td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo $rui_st_ope_profit ?></td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo $p2_s_ope_profit ?></td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo $p1_s_ope_profit ?></td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo $s_ope_profit ?></td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo $rui_s_ope_profit ?></td>
                    <td nowrap align='left'  class='pt10'>　</td>  <!-- 余白 -->
                </tr>
                <tr>
                    <td rowspan='7' align='center' valign='middle' class='pt10b' bgcolor='#ceffce'>営業外損益</td>
                    <td rowspan='4' align='center' class='pt10' bgcolor='#ffffc6'></td> <!-- 余白 -->
                    <td nowrap align='left' class='pt10b' bgcolor='white'>　業務委託収入</td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $p2_ss_gyoumu ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $p1_ss_gyoumu ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $ss_gyoumu ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $rui_ss_gyoumu ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $p2_st_gyoumu ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $p1_st_gyoumu ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $st_gyoumu ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $rui_st_gyoumu ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $p2_s_gyoumu ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $p1_s_gyoumu ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $s_gyoumu ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $rui_s_gyoumu ?></td>
                    <td nowrap align='left'  class='pt10'>当月人員比</td>
                </tr>
                <tr>
                    <td nowrap align='left' class='pt10b' bgcolor='#e6e6e6'>　仕　入　割　引</td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo $p2_ss_swari ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo $p1_ss_swari ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo $ss_swari ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo $rui_ss_swari ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo $p2_st_swari ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo $p1_st_swari ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo $st_swari ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo $rui_st_swari ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo $p2_s_swari ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo $p1_s_swari ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo $s_swari ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo $rui_s_swari ?></td>
                    <td nowrap align='left'  class='pt10'>当月人員比</td>
                </tr>
                <tr>
                    <td nowrap align='left' class='pt10b' bgcolor='white'>　そ　　の　　他</td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $p2_ss_pother ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $p1_ss_pother ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $ss_pother ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $rui_ss_pother ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $p2_st_pother ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $p1_st_pother ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $st_pother ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $rui_st_pother ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $p2_s_pother ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $p1_s_pother ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $s_pother ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $rui_s_pother ?></td>
                    <td nowrap align='left'  class='pt10'>当月人員比</td>
                </tr>
                <tr>
                    <td nowrap align='left' class='pt10b' bgcolor='#ffffc6'>　営業外収益 計</td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo $p2_ss_nonope_profit_sum ?>   </td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo $p1_ss_nonope_profit_sum ?>   </td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo $ss_nonope_profit_sum ?>      </td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo $rui_ss_nonope_profit_sum ?>  </td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo $p2_st_nonope_profit_sum ?>   </td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo $p1_st_nonope_profit_sum ?>   </td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo $st_nonope_profit_sum ?>      </td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo $rui_st_nonope_profit_sum ?>  </td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo $p2_s_nonope_profit_sum ?>   </td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo $p1_s_nonope_profit_sum ?>   </td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo $s_nonope_profit_sum ?>      </td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo $rui_s_nonope_profit_sum ?>  </td>
                    <td nowrap align='left'  class='pt10'>　</td> <!-- 余白 -->
                </tr>
                <tr>
                    <td rowspan='3' align='center' class='pt10' bgcolor='#ffffc6'></td> <!-- 余白 -->
                    <td nowrap align='left' class='pt10b' bgcolor='#e6e6e6'>　支　払　利　息</td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo $p2_ss_srisoku ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo $p1_ss_srisoku ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo $ss_srisoku ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo $rui_ss_srisoku ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo $p2_st_srisoku ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo $p1_st_srisoku ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo $st_srisoku ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo $rui_st_srisoku ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo $p2_s_srisoku ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo $p1_s_srisoku ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo $s_srisoku ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo $rui_s_srisoku ?></td>
                    <td nowrap align='left'  class='pt10'>当月人員比</td>
                </tr>
                <tr>
                    <td nowrap align='left' class='pt10b' bgcolor='white'>　そ　　の　　他</td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $p2_ss_lother ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $p1_ss_lother ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $ss_lother ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $rui_ss_lother ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $p2_st_lother ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $p1_st_lother ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $st_lother ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $rui_st_lother ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $p2_s_lother ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $p1_s_lother ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $s_lother ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $rui_s_lother ?></td>
                    <td nowrap align='left'  class='pt10'>当月人員比</td>
                </tr>
                <tr>
                    <td nowrap align='left' class='pt10b' bgcolor='#ffffc6'>　営業外費用 計</td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo $p2_ss_nonope_loss_sum ?>   </td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo $p1_ss_nonope_loss_sum ?>   </td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo $ss_nonope_loss_sum ?>      </td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo $rui_ss_nonope_loss_sum ?>  </td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo $p2_st_nonope_loss_sum ?>   </td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo $p1_st_nonope_loss_sum ?>   </td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo $st_nonope_loss_sum ?>      </td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo $rui_st_nonope_loss_sum ?>  </td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo $p2_s_nonope_loss_sum ?>   </td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo $p1_s_nonope_loss_sum ?>   </td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo $s_nonope_loss_sum ?>      </td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo $rui_s_nonope_loss_sum ?>  </td>
                    <td nowrap align='left'  class='pt10'>　</td> <!-- 余白 -->
                </tr>
                <tr>
                    <td colspan='3' nowrap align='center' class='pt10b' bgcolor='#ceffce'>経　　常　　利　　益</td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo $p2_ss_current_profit ?>   </td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo $p1_ss_current_profit ?>   </td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo $ss_current_profit ?>      </td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo $rui_ss_current_profit ?>  </td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo $p2_st_current_profit ?>   </td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo $p1_st_current_profit ?>   </td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo $st_current_profit ?>      </td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo $rui_st_current_profit ?>  </td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo $p2_s_current_profit ?>   </td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo $p1_s_current_profit ?>   </td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo $s_current_profit ?>      </td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo $rui_s_current_profit ?>  </td>
                    <td nowrap align='left'  class='pt10'>　</td>  <!-- 余白 -->
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
                    <td colspan='20' bgcolor='white' align='left' class='pt10b'><a href='<?=$menu->out_action('特記事項入力')?>?<?php echo uniqid('menu') ?>' style='text-decoration:none; color:black;'>　※　月次損益特記事項</a></td>
                </tr>
                <tr>
                    <td colspan='20' bgcolor='white' class='pt10'>
                        <ol>
                        <?php
                            if ($comment_ss != "") {
                                echo "<li><pre>$comment_ss</pre></li>\n";
                            }
                            if ($comment_st != "") {
                                echo "<li><pre>$comment_st</pre></li>\n";
                            }
                            if ($comment_s != "") {
                                echo "<li><pre>$comment_s</pre></li>\n";
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
