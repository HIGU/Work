<?php
//////////////////////////////////////////////////////////////////////////////
// 月次損益関係 ２期比較表 本決算損益表 予算無しVer(Webにデータがない為)    //
// Copyright (C) 2012-     Norihisa.Ohya norihisa_ooya@nitto-kohki.co.jp    //
// Changed history                                                          //
// 2012/01/17 Created   profit_loss_pl_act_2ki.php                          //
// 2012/01/20 プログラムの完成 チェック済 稼動                              //
// 2012/02/13 第４四半期のみ表示形式が違っていたのに対応                    //
// 2012/04/18 第４四半期のみ表示形式が違っていたのに対応（２回目）          //
//////////////////////////////////////////////////////////////////////////////
// ini_set('error_reporting', E_STRICT);    // E_STRICT=2048(php5) E_ALL=2047 debug 用
// ini_set('error_reporting', E_ALL);       // E_ALL='2047' debug 用
// ini_set('display_errors', '1');          // Error 表示 ON debug 用 リリース後コメント
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

///// 対象当月
$yyyymm   = $_SESSION['2ki_ym'];
$ki       = Ym_to_tnk($_SESSION['2ki_ym']);
$b_yyyymm = $yyyymm - 100;
$p1_ki    = Ym_to_tnk($b_yyyymm);
///// 期初年月の算出
$yyyy = substr($yyyymm, 0,4);
$mm   = substr($yyyymm, 4,2);
if (($mm >= 1) && ($mm <= 3)) {
    $yyyy = ($yyyy - 1);
}
$str_ym   = $yyyy . "04";   // 当期 期初年月
$b_str_ym = $str_ym - 100;  // 前期 期初年月

///// 四半期 年月の算出(年が切り替わることはないのでそのままマイナスでOK)
$p1_ym = $yyyymm - 1;
$p2_ym = $yyyymm - 2;

///// 表示用 ４桁の年月の算出YYMM
$yy     = substr($yyyymm, 2,2);  // 当期年（yy）
$b_yy   = $yy - 1;               // 前期年（yy）
$b2_yy  = $b_yy - 1;             // 前期年（yy）第４四半期
$mm     = substr($yyyymm, 4,2);  // 最終月(mm)
$p1_mm  = substr($p1_ym, 4,2);   // 四半期前月(mm)
$p2_mm  = substr($p2_ym, 4,2);   // 四半期前々月(mm)

///// 期・半期の取得
$tuki_chk = substr($_SESSION['2ki_ym'],4,2);
if ($tuki_chk == 3) {
    $hanki = '４';
} elseif ($tuki_chk == 6) {
    $hanki = '１';
} elseif ($tuki_chk == 9) {
    $hanki = '２';
} elseif ($tuki_chk == 12) {
    $hanki = '３';
}
///// タイトル名(ソースのタイトル名とフォームのタイトル名)
if ($tuki_chk == 3) {
    $menu->set_title("第 {$ki} 期　予　算　実　績　比　較　表");
} else {
    $menu->set_title("第 {$ki} 期　第{$hanki}四半期　予　算　実　績　比　較　表");
}
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

// 第４四半期用 四半期年月
if ($tuki_chk == 3) {
    $yyyy   = substr($yyyymm, 0,4);
    $b_yyyy = $yyyy - 1;
    $h1_str = $b_yyyy . '04';
    $h1_end = $b_yyyy . '06';
    $h2_str = $b_yyyy . '07';
    $h2_end = $b_yyyy . '09';
    $h3_str = $b_yyyy . '10';
    $h3_end = $b_yyyy . '12';
    $h4_str = $yyyy . '01';
    $h4_end = $yyyy . '03';
}
/********** 売上高 **********/
if ($tuki_chk == 3) {
        ///// 第１四半期
    $query = sprintf("select sum(kin) from profit_loss_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='全体売上高'", $h1_str, $h1_end);
    if (getUniResult($query, $h1_all_uri) < 1) {
        $h1_all_uri = 0;                 // 検索失敗
    }
        ///// 第２四半期
    $query = sprintf("select sum(kin) from profit_loss_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='全体売上高'", $h2_str, $h2_end);
    if (getUniResult($query, $h2_all_uri) < 1) {
        $h2_all_uri = 0;                 // 検索失敗
    }
        ///// 第３四半期
    $query = sprintf("select sum(kin) from profit_loss_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='全体売上高'", $h3_str, $h3_end);
    if (getUniResult($query, $h3_all_uri) < 1) {
        $h3_all_uri = 0;                 // 検索失敗
    }
        ///// 第４四半期
    $query = sprintf("select sum(kin) from profit_loss_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='全体売上高'", $h4_str, $h4_end);
    if (getUniResult($query, $h4_all_uri) < 1) {
        $h4_all_uri = 0;                 // 検索失敗
    }
} else {
        ///// 当月
    $query = sprintf("select kin from profit_loss_pl_history where pl_bs_ym=%d and note='全体売上高'", $yyyymm);
    if (getUniResult($query, $all_uri) < 1) {
        $all_uri = 0;                 // 検索失敗
    }
        ///// 前月
    $query = sprintf("select kin from profit_loss_pl_history where pl_bs_ym=%d and note='全体売上高'", $p1_ym);
    if (getUniResult($query, $p1_all_uri) < 1) {
        $p1_all_uri = 0;                 // 検索失敗
    }
        ///// 前々月
    $query = sprintf("select kin from profit_loss_pl_history where pl_bs_ym=%d and note='全体売上高'", $p2_ym);
    if (getUniResult($query, $p2_all_uri) < 1) {
        $p2_all_uri = 0;                 // 検索失敗
    }
}
    ///// 当期
$query = sprintf("select sum(kin) from profit_loss_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='全体売上高'", $str_ym, $yyyymm);
if (getUniResult($query, $rui_all_uri) < 1) {
    $rui_all_uri = 0;                 // 検索失敗
}
    ///// 前期
$query = sprintf("select sum(kin) from profit_loss_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='全体売上高'", $b_str_ym, $b_yyyymm);
if (getUniResult($query, $p1_rui_all_uri) < 1) {
    $p1_rui_all_uri = 0;                 // 検索失敗
}

/********** 売上原価 **********/
if ($tuki_chk == 3) {
        ///// 第１四半期
    $query = sprintf("select sum(kin) from profit_loss_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='全体売上原価'", $h1_str, $h1_end);
    if (getUniResult($query, $h1_all_urigen) < 1) {
        $h1_all_urigen = 0;                 // 検索失敗
    }
        ///// 第２四半期
    $query = sprintf("select sum(kin) from profit_loss_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='全体売上原価'", $h2_str, $h2_end);
    if (getUniResult($query, $h2_all_urigen) < 1) {
        $h2_all_urigen = 0;                 // 検索失敗
    }
        ///// 第３四半期
    $query = sprintf("select sum(kin) from profit_loss_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='全体売上原価'", $h3_str, $h3_end);
    if (getUniResult($query, $h3_all_urigen) < 1) {
        $h3_all_urigen = 0;                 // 検索失敗
    }
        ///// 第４四半期
    $query = sprintf("select sum(kin) from profit_loss_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='全体売上原価'", $h4_str, $h4_end);
    if (getUniResult($query, $h4_all_urigen) < 1) {
        $h4_all_urigen = 0;                 // 検索失敗
    }
} else {
        ///// 当月
    $query = sprintf("select kin from profit_loss_pl_history where pl_bs_ym=%d and note='全体売上原価'", $yyyymm);
    if (getUniResult($query, $all_urigen) < 1) {
        $all_urigen = 0;                 // 検索失敗
    }
        ///// 前月
    $query = sprintf("select kin from profit_loss_pl_history where pl_bs_ym=%d and note='全体売上原価'", $p1_ym);
    if (getUniResult($query, $p1_all_urigen) < 1) {
        $p1_all_urigen = 0;                 // 検索失敗
    }
        ///// 前々月
    $query = sprintf("select kin from profit_loss_pl_history where pl_bs_ym=%d and note='全体売上原価'", $p2_ym);
    if (getUniResult($query, $p2_all_urigen) < 1) {
        $p2_all_urigen = 0;                 // 検索失敗
    }
}
    ///// 当期
$query = sprintf("select sum(kin) from profit_loss_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='全体売上原価'", $str_ym, $yyyymm);
if (getUniResult($query, $rui_all_urigen) < 1) {
    $rui_all_urigen = 0;                 // 検索失敗
}
    ///// 前期
$query = sprintf("select sum(kin) from profit_loss_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='全体売上原価'", $b_str_ym, $b_yyyymm);
if (getUniResult($query, $p1_rui_all_urigen) < 1) {
    $p1_rui_all_urigen = 0;                 // 検索失敗
}

/********** 売上総利益 **********/
if ($tuki_chk == 3) {
        ///// 第１四半期
    $query = sprintf("select sum(kin) from profit_loss_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='全体売上総利益'", $h1_str, $h1_end);
    if (getUniResult($query, $h1_all_gross_profit) < 1) {
        $h1_all_gross_profit = 0;                 // 検索失敗
    }
        ///// 第２四半期
    $query = sprintf("select sum(kin) from profit_loss_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='全体売上総利益'", $h2_str, $h2_end);
    if (getUniResult($query, $h2_all_gross_profit) < 1) {
        $h2_all_gross_profit = 0;                 // 検索失敗
    }
        ///// 第３四半期
    $query = sprintf("select sum(kin) from profit_loss_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='全体売上総利益'", $h3_str, $h3_end);
    if (getUniResult($query, $h3_all_gross_profit) < 1) {
        $h3_all_gross_profit = 0;                 // 検索失敗
    }
        ///// 第４四半期
    $query = sprintf("select sum(kin) from profit_loss_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='全体売上総利益'", $h4_str, $h4_end);
    if (getUniResult($query, $h4_all_gross_profit) < 1) {
        $h4_all_gross_profit = 0;                 // 検索失敗
    }
} else {
        ///// 当月
    $query = sprintf("select kin from profit_loss_pl_history where pl_bs_ym=%d and note='全体売上総利益'", $yyyymm);
    if (getUniResult($query, $all_gross_profit) < 1) {
        $all_gross_profit = 0;                 // 検索失敗
    }
        ///// 前月
    $query = sprintf("select kin from profit_loss_pl_history where pl_bs_ym=%d and note='全体売上総利益'", $p1_ym);
    if (getUniResult($query, $p1_all_gross_profit) < 1) {
        $p1_all_gross_profit = 0;                 // 検索失敗
    }
        ///// 前々月
    $query = sprintf("select kin from profit_loss_pl_history where pl_bs_ym=%d and note='全体売上総利益'", $p2_ym);
    if (getUniResult($query, $p2_all_gross_profit) < 1) {
        $p2_all_gross_profit = 0;                 // 検索失敗
    }
}
    ///// 当期
$query = sprintf("select sum(kin) from profit_loss_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='全体売上総利益'", $str_ym, $yyyymm);
if (getUniResult($query, $rui_all_gross_profit) < 1) {
    $rui_all_gross_profit = 0;                 // 検索失敗
}
    ///// 前期
$query = sprintf("select sum(kin) from profit_loss_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='全体売上総利益'", $b_str_ym, $b_yyyymm);
if (getUniResult($query, $p1_rui_all_gross_profit) < 1) {
    $p1_rui_all_gross_profit = 0;                 // 検索失敗
}

/********** 販管費の合計 **********/
if ($tuki_chk == 3) {
        ///// 第１四半期
    $query = sprintf("select sum(kin) from profit_loss_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='全体販管費及び一般管理費計'", $h1_str, $h1_end);
    if (getUniResult($query, $h1_all_han_all) < 1) {
        $h1_all_han_all = 0;                 // 検索失敗
    }
        ///// 第２四半期
    $query = sprintf("select sum(kin) from profit_loss_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='全体販管費及び一般管理費計'", $h2_str, $h2_end);
    if (getUniResult($query, $h2_all_han_all) < 1) {
        $h2_all_han_all = 0;                 // 検索失敗
    }
        ///// 第３四半期
    $query = sprintf("select sum(kin) from profit_loss_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='全体販管費及び一般管理費計'", $h3_str, $h3_end);
    if (getUniResult($query, $h3_all_han_all) < 1) {
        $h3_all_han_all = 0;                 // 検索失敗
    }
        ///// 第４四半期
    $query = sprintf("select sum(kin) from profit_loss_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='全体販管費及び一般管理費計'", $h4_str, $h4_end);
    if (getUniResult($query, $h4_all_han_all) < 1) {
        $h4_all_han_all = 0;                 // 検索失敗
    }
} else {
        ///// 当月
    $query = sprintf("select kin from profit_loss_pl_history where pl_bs_ym=%d and note='全体販管費及び一般管理費計'", $yyyymm);
    if (getUniResult($query, $all_han_all) < 1) {
        $all_han_all = 0;                 // 検索失敗
    }
        ///// 前月
    $query = sprintf("select kin from profit_loss_pl_history where pl_bs_ym=%d and note='全体販管費及び一般管理費計'", $p1_ym);
    if (getUniResult($query, $p1_all_han_all) < 1) {
        $p1_all_han_all = 0;                 // 検索失敗
    }
        ///// 前々月
    $query = sprintf("select kin from profit_loss_pl_history where pl_bs_ym=%d and note='全体販管費及び一般管理費計'", $p2_ym);
    if (getUniResult($query, $p2_all_han_all) < 1) {
        $p2_all_han_all = 0;                 // 検索失敗
    }
}
    ///// 当期
$query = sprintf("select sum(kin) from profit_loss_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='全体販管費及び一般管理費計'", $str_ym, $yyyymm);
if (getUniResult($query, $rui_all_han_all) < 1) {
    $rui_all_han_all = 0;                 // 検索失敗
}
    ///// 前期
$query = sprintf("select sum(kin) from profit_loss_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='全体販管費及び一般管理費計'", $b_str_ym, $b_yyyymm);
if (getUniResult($query, $p1_rui_all_han_all) < 1) {
    $p1_rui_all_han_all = 0;                 // 検索失敗
}

/********** 営業利益 **********/
if ($tuki_chk == 3) {
        ///// 第１四半期
    $query = sprintf("select sum(kin) from profit_loss_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='全体営業利益'", $h1_str, $h1_end);
    if (getUniResult($query, $h1_all_ope_profit) < 1) {
        $h1_all_ope_profit = 0;                 // 検索失敗
    }
        ///// 第２四半期
    $query = sprintf("select sum(kin) from profit_loss_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='全体営業利益'", $h2_str, $h2_end);
    if (getUniResult($query, $h2_all_ope_profit) < 1) {
        $h2_all_ope_profit = 0;                 // 検索失敗
    }
        ///// 第３四半期
    $query = sprintf("select sum(kin) from profit_loss_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='全体営業利益'", $h3_str, $h3_end);
    if (getUniResult($query, $h3_all_ope_profit) < 1) {
        $h3_all_ope_profit = 0;                 // 検索失敗
    }
        ///// 第４四半期
    $query = sprintf("select sum(kin) from profit_loss_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='全体営業利益'", $h4_str, $h4_end);
    if (getUniResult($query, $h4_all_ope_profit) < 1) {
        $h4_all_ope_profit = 0;                 // 検索失敗
    }
} else {
        ///// 当月
    $query = sprintf("select kin from profit_loss_pl_history where pl_bs_ym=%d and note='全体営業利益'", $yyyymm);
    if (getUniResult($query, $all_ope_profit) < 1) {
        $all_ope_profit = 0;                 // 検索失敗
    }
        ///// 前月
    $query = sprintf("select kin from profit_loss_pl_history where pl_bs_ym=%d and note='全体営業利益'", $p1_ym);
    if (getUniResult($query, $p1_all_ope_profit) < 1) {
        $p1_all_ope_profit = 0;                 // 検索失敗
    }
        ///// 前々月
    $query = sprintf("select kin from profit_loss_pl_history where pl_bs_ym=%d and note='全体営業利益'", $p2_ym);
    if (getUniResult($query, $p2_all_ope_profit) < 1) {
        $p2_all_ope_profit = 0;                 // 検索失敗
    }
}
    ///// 当期
$query = sprintf("select sum(kin) from profit_loss_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='全体営業利益'", $str_ym, $yyyymm);
if (getUniResult($query, $rui_all_ope_profit) < 1) {
    $rui_all_ope_profit = 0;                 // 検索失敗
}
    ///// 前期
$query = sprintf("select sum(kin) from profit_loss_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='全体営業利益'", $b_str_ym, $b_yyyymm);
if (getUniResult($query, $p1_rui_all_ope_profit) < 1) {
    $p1_rui_all_ope_profit = 0;                 // 検索失敗
}

/********** 営業外収益の合計 **********/
if ($tuki_chk == 3) {
        ///// 第１四半期
    $query = sprintf("select sum(kin) from profit_loss_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='全体営業外収益計'", $h1_str, $h1_end);
    if (getUniResult($query, $h1_all_nonope_profit_sum) < 1) {
        $h1_all_nonope_profit_sum = 0;                 // 検索失敗
    }
        ///// 第２四半期
    $query = sprintf("select sum(kin) from profit_loss_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='全体営業外収益計'", $h2_str, $h2_end);
    if (getUniResult($query, $h2_all_nonope_profit_sum) < 1) {
        $h2_all_nonope_profit_sum = 0;                 // 検索失敗
    }
        ///// 第３四半期
    $query = sprintf("select sum(kin) from profit_loss_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='全体営業外収益計'", $h3_str, $h3_end);
    if (getUniResult($query, $h3_all_nonope_profit_sum) < 1) {
        $h3_all_nonope_profit_sum = 0;                 // 検索失敗
    }
        ///// 第４四半期
    $query = sprintf("select sum(kin) from profit_loss_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='全体営業外収益計'", $h4_str, $h4_end);
    if (getUniResult($query, $h4_all_nonope_profit_sum) < 1) {
        $h4_all_nonope_profit_sum = 0;                 // 検索失敗
    }
} else {
        ///// 当月
    $query = sprintf("select kin from profit_loss_pl_history where pl_bs_ym=%d and note='全体営業外収益計'", $yyyymm);
    if (getUniResult($query, $all_nonope_profit_sum) < 1) {
        $all_nonope_profit_sum = 0;                 // 検索失敗
    }
        ///// 前月
    $query = sprintf("select kin from profit_loss_pl_history where pl_bs_ym=%d and note='全体営業外収益計'", $p1_ym);
    if (getUniResult($query, $p1_all_nonope_profit_sum) < 1) {
        $p1_all_nonope_profit_sum = 0;                 // 検索失敗
    }
        ///// 前々月
    $query = sprintf("select kin from profit_loss_pl_history where pl_bs_ym=%d and note='全体営業外収益計'", $p2_ym);
    if (getUniResult($query, $p2_all_nonope_profit_sum) < 1) {
        $p2_all_nonope_profit_sum = 0;                 // 検索失敗
    }
}
    ///// 当期
$query = sprintf("select sum(kin) from profit_loss_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='全体営業外収益計'", $str_ym, $yyyymm);
if (getUniResult($query, $rui_all_nonope_profit_sum) < 1) {
    $rui_all_nonope_profit_sum = 0;                 // 検索失敗
}
    ///// 前期
$query = sprintf("select sum(kin) from profit_loss_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='全体営業外収益計'", $b_str_ym, $b_yyyymm);
if (getUniResult($query, $p1_rui_all_nonope_profit_sum) < 1) {
    $p1_rui_all_nonope_profit_sum = 0;                 // 検索失敗
}

/********** 営業外費用の合計 **********/
if ($tuki_chk == 3) {
        ///// 第１四半期
    $query = sprintf("select sum(kin) from profit_loss_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='全体営業外費用計'", $h1_str, $h1_end);
    if (getUniResult($query, $h1_all_nonope_loss_sum) < 1) {
        $h1_all_nonope_loss_sum = 0;                 // 検索失敗
    }
        ///// 第２四半期
    $query = sprintf("select sum(kin) from profit_loss_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='全体営業外費用計'", $h2_str, $h2_end);
    if (getUniResult($query, $h2_all_nonope_loss_sum) < 1) {
        $h2_all_nonope_loss_sum = 0;                 // 検索失敗
    }
        ///// 第３四半期
    $query = sprintf("select sum(kin) from profit_loss_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='全体営業外費用計'", $h3_str, $h3_end);
    if (getUniResult($query, $h3_all_nonope_loss_sum) < 1) {
        $h3_all_nonope_loss_sum = 0;                 // 検索失敗
    }
        ///// 第４四半期
    $query = sprintf("select sum(kin) from profit_loss_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='全体営業外費用計'", $h4_str, $h4_end);
    if (getUniResult($query, $h4_all_nonope_loss_sum) < 1) {
        $h4_all_nonope_loss_sum = 0;                 // 検索失敗
    }
} else {
        ///// 当月
    $query = sprintf("select kin from profit_loss_pl_history where pl_bs_ym=%d and note='全体営業外費用計'", $yyyymm);
    if (getUniResult($query, $all_nonope_loss_sum) < 1) {
        $all_nonope_loss_sum = 0;                 // 検索失敗
    }
        ///// 前月
    $query = sprintf("select kin from profit_loss_pl_history where pl_bs_ym=%d and note='全体営業外費用計'", $p1_ym);
    if (getUniResult($query, $p1_all_nonope_loss_sum) < 1) {
        $p1_all_nonope_loss_sum = 0;                 // 検索失敗
    }
        ///// 前々月
    $query = sprintf("select kin from profit_loss_pl_history where pl_bs_ym=%d and note='全体営業外費用計'", $p2_ym);
    if (getUniResult($query, $p2_all_nonope_loss_sum) < 1) {
        $p2_all_nonope_loss_sum = 0;                 // 検索失敗
    }
}
    ///// 当期
$query = sprintf("select sum(kin) from profit_loss_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='全体営業外費用計'", $str_ym, $yyyymm);
if (getUniResult($query, $rui_all_nonope_loss_sum) < 1) {
    $rui_all_nonope_loss_sum = 0;                 // 検索失敗
}
    ///// 前期
$query = sprintf("select sum(kin) from profit_loss_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='全体営業外費用計'", $b_str_ym, $b_yyyymm);
if (getUniResult($query, $p1_rui_all_nonope_loss_sum) < 1) {
    $p1_rui_all_nonope_loss_sum = 0;                 // 検索失敗
}

/********** 経常利益 **********/
if ($tuki_chk == 3) {
        ///// 第１四半期
    $query = sprintf("select sum(kin) from profit_loss_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='全体経常利益'", $h1_str, $h1_end);
    if (getUniResult($query, $h1_all_current_profit) < 1) {
        $h1_all_current_profit = 0;                 // 検索失敗
    }
        ///// 第２四半期
    $query = sprintf("select sum(kin) from profit_loss_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='全体経常利益'", $h2_str, $h2_end);
    if (getUniResult($query, $h2_all_current_profit) < 1) {
        $h2_all_current_profit = 0;                 // 検索失敗
    }
        ///// 第３四半期
    $query = sprintf("select sum(kin) from profit_loss_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='全体経常利益'", $h3_str, $h3_end);
    if (getUniResult($query, $h3_all_current_profit) < 1) {
        $h3_all_current_profit = 0;                 // 検索失敗
    }
        ///// 第４四半期
    $query = sprintf("select sum(kin) from profit_loss_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='全体経常利益'", $h4_str, $h4_end);
    if (getUniResult($query, $h4_all_current_profit) < 1) {
        $h4_all_current_profit = 0;                 // 検索失敗
    }
} else {
        ///// 当月
    $query = sprintf("select kin from profit_loss_pl_history where pl_bs_ym=%d and note='全体経常利益'", $yyyymm);
    if (getUniResult($query, $all_current_profit) < 1) {
        $all_current_profit = 0;                 // 検索失敗
    }
        ///// 前月
    $query = sprintf("select kin from profit_loss_pl_history where pl_bs_ym=%d and note='全体経常利益'", $p1_ym);
    if (getUniResult($query, $p1_all_current_profit) < 1) {
        $p1_all_current_profit = 0;                 // 検索失敗
    }
        ///// 前々月
    $query = sprintf("select kin from profit_loss_pl_history where pl_bs_ym=%d and note='全体経常利益'", $p2_ym);
    if (getUniResult($query, $p2_all_current_profit) < 1) {
        $p2_all_current_profit = 0;                 // 検索失敗
    }
}
    ///// 当期
$query = sprintf("select sum(kin) from profit_loss_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='全体経常利益'", $str_ym, $yyyymm);
if (getUniResult($query, $rui_all_current_profit) < 1) {
    $rui_all_current_profit = 0;                 // 検索失敗
}
    ///// 前期
$query = sprintf("select sum(kin) from profit_loss_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='全体経常利益'", $b_str_ym, $b_yyyymm);
if (getUniResult($query, $p1_rui_all_current_profit) < 1) {
    $p1_rui_all_current_profit = 0;                 // 検索失敗
}

/********** 特別利益 **********/
if ($tuki_chk == 3) {
       ///// 第１四半期
    $query = sprintf("select sum(kin) from profit_loss_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='全体特別利益'", $h1_str, $h1_end);
    if (getUniResult($query, $h1_all_special_profit) < 1) {
        $h1_all_special_profit = 0;                 // 検索失敗
    }
       ///// 第２四半期
    $query = sprintf("select sum(kin) from profit_loss_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='全体特別利益'", $h2_str, $h2_end);
    if (getUniResult($query, $h2_all_special_profit) < 1) {
        $h2_all_special_profit = 0;                 // 検索失敗
    }
       ///// 第３四半期
    $query = sprintf("select sum(kin) from profit_loss_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='全体特別利益'", $h3_str, $h3_end);
    if (getUniResult($query, $h3_all_special_profit) < 1) {
        $h3_all_special_profit = 0;                 // 検索失敗
    }
       ///// 第４四半期
    $query = sprintf("select sum(kin) from profit_loss_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='全体特別利益'", $h4_str, $h4_end);
    if (getUniResult($query, $h4_all_special_profit) < 1) {
        $h4_all_special_profit = 0;                 // 検索失敗
    }
} else {
        ///// 当月
    $query = sprintf("select kin from profit_loss_pl_history where pl_bs_ym=%d and note='全体特別利益'", $yyyymm);
    if (getUniResult($query, $all_special_profit) < 1) {
        $all_special_profit = 0;                 // 検索失敗
    }
        ///// 前月
    $query = sprintf("select kin from profit_loss_pl_history where pl_bs_ym=%d and note='全体特別利益'", $p1_ym);
    if (getUniResult($query, $p1_all_special_profit) < 1) {
        $p1_all_special_profit = 0;                 // 検索失敗
    }
        ///// 前々月
    $query = sprintf("select kin from profit_loss_pl_history where pl_bs_ym=%d and note='全体特別利益'", $p2_ym);
    if (getUniResult($query, $p2_all_special_profit) < 1) {
        $p2_all_special_profit = 0;                 // 検索失敗
    }
}
    ///// 当期
$query = sprintf("select sum(kin) from profit_loss_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='全体特別利益'", $str_ym, $yyyymm);
if (getUniResult($query, $rui_all_special_profit) < 1) {
    $rui_all_special_profit = 0;                 // 検索失敗
}
    ///// 前期
$query = sprintf("select sum(kin) from profit_loss_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='全体特別利益'", $b_str_ym, $b_yyyymm);
if (getUniResult($query, $p1_rui_all_special_profit) < 1) {
    $p1_rui_all_special_profit = 0;                 // 検索失敗
}

/********** 特別損失 **********/
if ($tuki_chk == 3) {
       ///// 第１四半期
    $query = sprintf("select sum(kin) from profit_loss_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='全体特別損失'", $h1_str, $h1_end);
    if (getUniResult($query, $h1_all_special_loss) < 1) {
        $h1_all_special_loss = 0;                 // 検索失敗
    }
       ///// 第２四半期
    $query = sprintf("select sum(kin) from profit_loss_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='全体特別損失'", $h2_str, $h2_end);
    if (getUniResult($query, $h2_all_special_loss) < 1) {
        $h2_all_special_loss = 0;                 // 検索失敗
    }
       ///// 第３四半期
    $query = sprintf("select sum(kin) from profit_loss_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='全体特別損失'", $h3_str, $h3_end);
    if (getUniResult($query, $h3_all_special_loss) < 1) {
        $h3_all_special_loss = 0;                 // 検索失敗
    }
       ///// 第４四半期
    $query = sprintf("select sum(kin) from profit_loss_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='全体特別損失'", $h4_str, $h4_end);
    if (getUniResult($query, $h4_all_special_loss) < 1) {
        $h4_all_special_loss = 0;                 // 検索失敗
    }
} else {
        ///// 当月
    $query = sprintf("select kin from profit_loss_pl_history where pl_bs_ym=%d and note='全体特別損失'", $yyyymm);
    if (getUniResult($query, $all_special_loss) < 1) {
        $all_special_loss = 0;                 // 検索失敗
    }
        ///// 前月
    $query = sprintf("select kin from profit_loss_pl_history where pl_bs_ym=%d and note='全体特別損失'", $p1_ym);
    if (getUniResult($query, $p1_all_special_loss) < 1) {
        $p1_all_special_loss = 0;                 // 検索失敗
    }
        ///// 前々月
    $query = sprintf("select kin from profit_loss_pl_history where pl_bs_ym=%d and note='全体特別損失'", $p2_ym);
    if (getUniResult($query, $p2_all_special_loss) < 1) {
        $p2_all_special_loss = 0;                 // 検索失敗
    }
}
    ///// 当期
$query = sprintf("select sum(kin) from profit_loss_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='全体特別損失'", $str_ym, $yyyymm);
if (getUniResult($query, $rui_all_special_loss) < 1) {
    $rui_all_special_loss = 0;                 // 検索失敗
}
    ///// 前期
$query = sprintf("select sum(kin) from profit_loss_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='全体特別損失'", $b_str_ym, $b_yyyymm);
if (getUniResult($query, $p1_rui_all_special_loss) < 1) {
    $p1_rui_all_special_loss = 0;                 // 検索失敗
}

/********** 税引前純利益金額 **********/
if ($tuki_chk == 3) {
       ///// 第１四半期
    $query = sprintf("select sum(kin) from profit_loss_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='全体税引前純利益金額'", $h1_str, $h1_end);
    if (getUniResult($query, $h1_all_before_tax_net_profit) < 1) {
        $h1_all_before_tax_net_profit = 0;                 // 検索失敗
    }
       ///// 第２四半期
    $query = sprintf("select sum(kin) from profit_loss_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='全体税引前純利益金額'", $h2_str, $h2_end);
    if (getUniResult($query, $h2_all_before_tax_net_profit) < 1) {
        $h2_all_before_tax_net_profit = 0;                 // 検索失敗
    }
       ///// 第３四半期
    $query = sprintf("select sum(kin) from profit_loss_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='全体税引前純利益金額'", $h3_str, $h3_end);
    if (getUniResult($query, $h3_all_before_tax_net_profit) < 1) {
        $h3_all_before_tax_net_profit = 0;                 // 検索失敗
    }
       ///// 第４四半期
    $query = sprintf("select sum(kin) from profit_loss_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='全体税引前純利益金額'", $h4_str, $h4_end);
    if (getUniResult($query, $h4_all_before_tax_net_profit) < 1) {
        $h4_all_before_tax_net_profit = 0;                 // 検索失敗
    }
} else {
        ///// 当月
    $query = sprintf("select kin from profit_loss_pl_history where pl_bs_ym=%d and note='全体税引前純利益金額'", $yyyymm);
    if (getUniResult($query, $all_before_tax_net_profit) < 1) {
        $all_before_tax_net_profit = 0;                 // 検索失敗
    }
        ///// 前月
    $query = sprintf("select kin from profit_loss_pl_history where pl_bs_ym=%d and note='全体税引前純利益金額'", $p1_ym);
    if (getUniResult($query, $p1_all_before_tax_net_profit) < 1) {
        $p1_all_before_tax_net_profit = 0;                 // 検索失敗
    }
        ///// 前々月
    $query = sprintf("select kin from profit_loss_pl_history where pl_bs_ym=%d and note='全体税引前純利益金額'", $p2_ym);
    if (getUniResult($query, $p2_all_before_tax_net_profit) < 1) {
        $p2_all_before_tax_net_profit = 0;                 // 検索失敗
    }
}
    ///// 当期
$query = sprintf("select sum(kin) from profit_loss_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='全体税引前純利益金額'", $str_ym, $yyyymm);
if (getUniResult($query, $rui_all_before_tax_net_profit) < 1) {
    $rui_all_before_tax_net_profit = 0;                 // 検索失敗
}
    ///// 前期
$query = sprintf("select sum(kin) from profit_loss_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='全体税引前純利益金額'", $b_str_ym, $b_yyyymm);
if (getUniResult($query, $p1_rui_all_before_tax_net_profit) < 1) {
    $p1_rui_all_before_tax_net_profit = 0;                 // 検索失敗
}

/********** 法人税等の合計 **********/
if ($tuki_chk == 3) {
       ///// 第１四半期
    $query = sprintf("select sum(kin) from profit_loss_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='全体法人税等'", $h1_str, $h1_end);
    if (getUniResult($query, $h1_all_corporation_tax_etc) < 1) {
        $h1_all_corporation_tax_etc = 0;                 // 検索失敗
    }
       ///// 第２四半期
    $query = sprintf("select sum(kin) from profit_loss_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='全体法人税等'", $h2_str, $h2_end);
    if (getUniResult($query, $h2_all_corporation_tax_etc) < 1) {
        $h2_all_corporation_tax_etc = 0;                 // 検索失敗
    }
       ///// 第３四半期
    $query = sprintf("select sum(kin) from profit_loss_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='全体法人税等'", $h3_str, $h3_end);
    if (getUniResult($query, $h3_all_corporation_tax_etc) < 1) {
        $h3_all_corporation_tax_etc = 0;                 // 検索失敗
    }
       ///// 第４四半期
    $query = sprintf("select sum(kin) from profit_loss_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='全体法人税等'", $h4_str, $h4_end);
    if (getUniResult($query, $h4_all_corporation_tax_etc) < 1) {
        $h4_all_corporation_tax_etc = 0;                 // 検索失敗
    }
} else {
        ///// 当月
    $query = sprintf("select kin from profit_loss_pl_history where pl_bs_ym=%d and note='全体法人税等'", $yyyymm);
    if (getUniResult($query, $all_corporation_tax_etc) < 1) {
        $all_corporation_tax_etc = 0;                 // 検索失敗
    }
        ///// 前月
    $query = sprintf("select kin from profit_loss_pl_history where pl_bs_ym=%d and note='全体法人税等'", $p1_ym);
    if (getUniResult($query, $p1_all_corporation_tax_etc) < 1) {
        $p1_all_corporation_tax_etc = 0;                 // 検索失敗
    }
        ///// 前々月
    $query = sprintf("select kin from profit_loss_pl_history where pl_bs_ym=%d and note='全体法人税等'", $p2_ym);
    if (getUniResult($query, $p2_all_corporation_tax_etc) < 1) {
        $p2_all_corporation_tax_etc = 0;                 // 検索失敗
    }
}
    ///// 当期
$query = sprintf("select sum(kin) from profit_loss_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='全体法人税等'", $str_ym, $yyyymm);
if (getUniResult($query, $rui_all_corporation_tax_etc) < 1) {
    $rui_all_corporation_tax_etc = 0;                 // 検索失敗
}
    ///// 前期
$query = sprintf("select sum(kin) from profit_loss_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='全体法人税等'", $b_str_ym, $b_yyyymm);
if (getUniResult($query, $p1_rui_all_corporation_tax_etc) < 1) {
    $p1_rui_all_corporation_tax_etc = 0;                 // 検索失敗
}

/********** 当期純利益金額 **********/
if ($tuki_chk == 3) {
       ///// 第１四半期
    $query = sprintf("select sum(kin) from profit_loss_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='全体当期純利益金額'", $h1_str, $h1_end);
    if (getUniResult($query, $h1_all_pure_profit) < 1) {
        $h1_all_pure_profit = 0;                 // 検索失敗
    }
       ///// 第２四半期
    $query = sprintf("select sum(kin) from profit_loss_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='全体当期純利益金額'", $h2_str, $h2_end);
    if (getUniResult($query, $h2_all_pure_profit) < 1) {
        $h2_all_pure_profit = 0;                 // 検索失敗
    }
       ///// 第３四半期
    $query = sprintf("select sum(kin) from profit_loss_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='全体当期純利益金額'", $h3_str, $h3_end);
    if (getUniResult($query, $h3_all_pure_profit) < 1) {
        $h3_all_pure_profit = 0;                 // 検索失敗
    }
       ///// 第４四半期
    $query = sprintf("select sum(kin) from profit_loss_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='全体当期純利益金額'", $h4_str, $h4_end);
    if (getUniResult($query, $h4_all_pure_profit) < 1) {
        $h4_all_pure_profit = 0;                 // 検索失敗
    }
} else {
        ///// 当月
    $query = sprintf("select kin from profit_loss_pl_history where pl_bs_ym=%d and note='全体当期純利益金額'", $yyyymm);
    if (getUniResult($query, $all_pure_profit) < 1) {
        $all_pure_profit = 0;                 // 検索失敗
    }
        ///// 前月
    $query = sprintf("select kin from profit_loss_pl_history where pl_bs_ym=%d and note='全体当期純利益金額'", $p1_ym);
    if (getUniResult($query, $p1_all_pure_profit) < 1) {
        $p1_all_pure_profit = 0;                 // 検索失敗
    }
        ///// 前々月
    $query = sprintf("select kin from profit_loss_pl_history where pl_bs_ym=%d and note='全体当期純利益金額'", $p2_ym);
    if (getUniResult($query, $p2_all_pure_profit) < 1) {
        $p2_all_pure_profit = 0;                 // 検索失敗
    }
}
    ///// 当期
$query = sprintf("select sum(kin) from profit_loss_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='全体当期純利益金額'", $str_ym, $yyyymm);
if (getUniResult($query, $rui_all_pure_profit) < 1) {
    $rui_all_pure_profit = 0;                 // 検索失敗
}
    ///// 前期
$query = sprintf("select sum(kin) from profit_loss_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='全体当期純利益金額'", $b_str_ym, $b_yyyymm);
if (getUniResult($query, $p1_rui_all_pure_profit) < 1) {
    $p1_rui_all_pure_profit = 0;                 // 検索失敗
}

    ///// 四半期合計金額の計算
if ($tuki_chk != 3) {
    $t_all_uri                   = $p2_all_uri + $p1_all_uri + $all_uri;
    $t_all_urigen                = $p2_all_urigen + $p1_all_urigen + $all_urigen;
    $t_all_gross_profit          = $p2_all_gross_profit + $p1_all_gross_profit + $all_gross_profit;
    $t_all_han_all               = $p2_all_han_all + $p1_all_han_all + $all_han_all;
    $t_all_ope_profit            = $p2_all_ope_profit + $p1_all_ope_profit + $all_ope_profit;
    $t_all_nonope_profit_sum     = $p2_all_nonope_profit_sum + $p1_all_nonope_profit_sum + $all_nonope_profit_sum;
    $t_all_nonope_loss_sum       = $p2_all_nonope_loss_sum + $p1_all_nonope_loss_sum + $all_nonope_loss_sum;
    $t_all_current_profit        = $p2_all_current_profit + $p1_all_current_profit + $all_current_profit;
    $t_all_special_profit        = $p2_all_special_profit + $p1_all_special_profit + $all_special_profit;
    $t_all_special_loss          = $p2_all_special_loss + $p1_all_special_loss + $all_special_loss;
    $t_all_before_tax_net_profit = $p2_all_before_tax_net_profit + $p1_all_before_tax_net_profit + $all_before_tax_net_profit;
    $t_all_corporation_tax_etc   = $p2_all_corporation_tax_etc + $p1_all_corporation_tax_etc + $all_corporation_tax_etc;
    $t_all_pure_profit           = $p2_all_pure_profit + $p1_all_pure_profit + $all_pure_profit;
}

    ///// 増減額の計算
$def_all_uri                   = $rui_all_uri - $p1_rui_all_uri;
$def_all_urigen                = $rui_all_urigen - $p1_rui_all_urigen;
$def_all_gross_profit          = $rui_all_gross_profit - $p1_rui_all_gross_profit;
$def_all_han_all               = $rui_all_han_all - $p1_rui_all_han_all;
$def_all_ope_profit            = $rui_all_ope_profit - $p1_rui_all_ope_profit;
$def_all_nonope_profit_sum     = $rui_all_nonope_profit_sum - $p1_rui_all_nonope_profit_sum;
$def_all_nonope_loss_sum       = $rui_all_nonope_loss_sum - $p1_rui_all_nonope_loss_sum;
$def_all_current_profit        = $rui_all_current_profit - $p1_rui_all_current_profit;
$def_all_special_profit        = $rui_all_special_profit - $p1_rui_all_special_profit;
$def_all_special_loss          = $rui_all_special_loss - $p1_rui_all_special_loss;
$def_all_before_tax_net_profit = $rui_all_before_tax_net_profit - $p1_rui_all_before_tax_net_profit;
$def_all_corporation_tax_etc   = $rui_all_corporation_tax_etc - $p1_rui_all_corporation_tax_etc;
$def_all_pure_profit           = $rui_all_pure_profit - $p1_rui_all_pure_profit;

    ///// 増減率の計算
if ($p1_rui_all_uri < 0) {
    if ($def_all_uri < 0) {
        $def_all_uri_rate = number_format(( -$def_all_uri / $p1_rui_all_uri * 100), 1);
    } else {
        $def_all_uri_rate = number_format(( $def_all_uri / -$p1_rui_all_uri * 100), 1);
    }
} else {
    $def_all_uri_rate = number_format(($def_all_uri / $p1_rui_all_uri * 100), 1);
}
if ($p1_rui_all_urigen < 0) {
    if ($def_all_urigen < 0) {
        $def_all_urigen_rate = number_format(( -$def_all_urigen / $p1_rui_all_urigen * 100), 1);
    } else {
        $def_all_urigen_rate = number_format(( $def_all_urigen / -$p1_rui_all_urigen * 100), 1);
    }
} else {
    $def_all_urigen_rate = number_format(($def_all_urigen / $p1_rui_all_urigen * 100), 1);
}
if ($p1_rui_all_gross_profit < 0) {
    if ($def_all_gross_profit < 0) {
        $def_all_gross_profit_rate = number_format(( -$def_all_gross_profit / $p1_rui_all_gross_profit * 100), 1);
    } else {
        $def_all_gross_profit_rate = number_format(( $def_all_gross_profit / -$p1_rui_all_gross_profit * 100), 1);
    }
} else {
    $def_all_gross_profit_rate = number_format(($def_all_gross_profit / $p1_rui_all_gross_profit * 100), 1);
}
if ($p1_rui_all_han_all < 0) {
    if ($def_all_han_all < 0) {
        $def_all_han_all_rate = number_format(( -$def_all_han_all / $p1_rui_all_han_all * 100), 1);
    } else {
        $def_all_han_all_rate = number_format(( $def_all_han_all / -$p1_rui_all_han_all * 100), 1);
    }
} else {
    $def_all_han_all_rate = number_format(($def_all_han_all / $p1_rui_all_han_all * 100), 1);
}
if ($p1_rui_all_ope_profit < 0) {
    if ($def_all_ope_profit < 0) {
        $def_all_ope_profit_rate = number_format(( -$def_all_ope_profit / $p1_rui_all_ope_profit * 100), 1);
    } else {
        $def_all_ope_profit_rate = number_format(( $def_all_ope_profit / -$p1_rui_all_ope_profit * 100), 1);
    }
} else {
    $def_all_ope_profit_rate = number_format(($def_all_ope_profit / $p1_rui_all_ope_profit * 100), 1);
}
if ($p1_rui_all_nonope_profit_sum < 0) {
    if ($def_all_nonope_profit_sum < 0) {
        $def_all_nonope_profit_sum_rate = number_format(( -$def_all_nonope_profit_sum / $p1_rui_all_nonope_profit_sum * 100), 1);
    } else {
        $def_all_nonope_profit_sum_rate = number_format(( $def_all_nonope_profit_sum / -$p1_rui_all_nonope_profit_sum * 100), 1);
    }
} else {
    $def_all_nonope_profit_sum_rate = number_format(($def_all_nonope_profit_sum / $p1_rui_all_nonope_profit_sum * 100), 1);
}
if ($p1_rui_all_nonope_loss_sum < 0) {
    if ($def_all_nonope_loss_sum < 0) {
        $def_all_nonope_loss_sum_rate = number_format(( -$def_all_nonope_loss_sum / $p1_rui_all_nonope_loss_sum * 100), 1);
    } else {
        $def_all_nonope_loss_sum_rate = number_format(( $def_all_nonope_loss_sum / -$p1_rui_all_nonope_loss_sum * 100), 1);
    }
} else {
    $def_all_nonope_loss_sum_rate = number_format(($def_all_nonope_loss_sum / $p1_rui_all_nonope_loss_sum * 100), 1);
}
if ($p1_rui_all_current_profit < 0) {
    if ($def_all_current_profit < 0) {
        $def_all_current_profit_rate = number_format(( -$def_all_current_profit / $p1_rui_all_current_profit * 100), 1);
    } else {
        $def_all_current_profit_rate = number_format(( $def_all_current_profit / -$p1_rui_all_current_profit * 100), 1);
    }
} else {
    $def_all_current_profit_rate = number_format(($def_all_current_profit / $p1_rui_all_current_profit * 100), 1);
}
if ($p1_rui_all_before_tax_net_profit < 0) {
    if ($def_all_before_tax_net_profit < 0) {
        $def_all_before_tax_net_profit_rate = number_format(( -$def_all_before_tax_net_profit / $p1_rui_all_before_tax_net_profit * 100), 1);
    } else {
        $def_all_before_tax_net_profit_rate = number_format(( $def_all_before_tax_net_profit / -$p1_rui_all_before_tax_net_profit * 100), 1);
    }
} else {
    $def_all_before_tax_net_profit_rate = number_format(($def_all_before_tax_net_profit / $p1_rui_all_before_tax_net_profit * 100), 1);
}
if ($p1_rui_all_corporation_tax_etc < 0) {
    if ($def_all_corporation_tax_etc < 0) {
        $def_all_corporation_tax_etc_rate = number_format(( -$def_all_corporation_tax_etc / $p1_rui_all_corporation_tax_etc * 100), 1);
    } else {
        $def_all_corporation_tax_etc_rate = number_format(( $def_all_corporation_tax_etc / -$p1_rui_all_corporation_tax_etc * 100), 1);
    }
} else {
    $def_all_corporation_tax_etc_rate = number_format(($def_all_corporation_tax_etc / $p1_rui_all_corporation_tax_etc * 100), 1);
}
if ($p1_rui_all_pure_profit < 0) {
    if ($def_all_pure_profit < 0) {
        $def_all_pure_profit_rate = number_format(( -$def_all_pure_profit / $p1_rui_all_pure_profit * 100), 1);
    } else {
        $def_all_pure_profit_rate = number_format(( $def_all_pure_profit / -$p1_rui_all_pure_profit * 100), 1);
    }
} else {
    $def_all_pure_profit_rate = number_format(($def_all_pure_profit / $p1_rui_all_pure_profit * 100), 1);
}

    ///// 各桁のフォーマット変更
if ($tuki_chk == 3) {
    $h1_all_uri                       = number_format(($h1_all_uri / $tani), $keta);
    $h2_all_uri                       = number_format(($h2_all_uri / $tani), $keta);
    $h3_all_uri                       = number_format(($h3_all_uri / $tani), $keta);
    $h4_all_uri                       = number_format(($h4_all_uri / $tani), $keta);
    $rui_all_uri                      = number_format(($rui_all_uri / $tani), $keta);
    $p1_rui_all_uri                   = number_format(($p1_rui_all_uri / $tani), $keta);
    $def_all_uri                      = number_format(($def_all_uri / $tani), $keta);
    $h1_all_urigen                    = number_format(($h1_all_urigen / $tani), $keta);
    $h2_all_urigen                    = number_format(($h2_all_urigen / $tani), $keta);
    $h3_all_urigen                    = number_format(($h3_all_urigen / $tani), $keta);
    $h4_all_urigen                    = number_format(($h4_all_urigen / $tani), $keta);
    $rui_all_urigen                   = number_format(($rui_all_urigen / $tani), $keta);
    $p1_rui_all_urigen                = number_format(($p1_rui_all_urigen / $tani), $keta);
    $def_all_urigen                   = number_format(($def_all_urigen / $tani), $keta);
    $h1_all_gross_profit              = number_format(($h1_all_gross_profit / $tani), $keta);
    $h2_all_gross_profit              = number_format(($h2_all_gross_profit / $tani), $keta);
    $h3_all_gross_profit              = number_format(($h3_all_gross_profit / $tani), $keta);
    $h4_all_gross_profit              = number_format(($h4_all_gross_profit / $tani), $keta);
    $rui_all_gross_profit             = number_format(($rui_all_gross_profit / $tani), $keta);
    $p1_rui_all_gross_profit          = number_format(($p1_rui_all_gross_profit / $tani), $keta);
    $def_all_gross_profit             = number_format(($def_all_gross_profit / $tani), $keta);
    $h1_all_han_all                   = number_format(($h1_all_han_all / $tani), $keta);
    $h2_all_han_all                   = number_format(($h2_all_han_all / $tani), $keta);
    $h3_all_han_all                   = number_format(($h3_all_han_all / $tani), $keta);
    $h4_all_han_all                   = number_format(($h4_all_han_all / $tani), $keta);
    $rui_all_han_all                  = number_format(($rui_all_han_all / $tani), $keta);
    $p1_rui_all_han_all               = number_format(($p1_rui_all_han_all / $tani), $keta);
    $def_all_han_all                  = number_format(($def_all_han_all / $tani), $keta);
    $h1_all_ope_profit                = number_format(($h1_all_ope_profit / $tani), $keta);
    $h2_all_ope_profit                = number_format(($h2_all_ope_profit / $tani), $keta);
    $h3_all_ope_profit                = number_format(($h3_all_ope_profit / $tani), $keta);
    $h4_all_ope_profit                = number_format(($h4_all_ope_profit / $tani), $keta);
    $rui_all_ope_profit               = number_format(($rui_all_ope_profit / $tani), $keta);
    $p1_rui_all_ope_profit            = number_format(($p1_rui_all_ope_profit / $tani), $keta);
    $def_all_ope_profit               = number_format(($def_all_ope_profit / $tani), $keta);
    $h1_all_nonope_profit_sum         = number_format(($h1_all_nonope_profit_sum / $tani), $keta);
    $h2_all_nonope_profit_sum         = number_format(($h2_all_nonope_profit_sum / $tani), $keta);
    $h3_all_nonope_profit_sum         = number_format(($h3_all_nonope_profit_sum / $tani), $keta);
    $h4_all_nonope_profit_sum         = number_format(($h4_all_nonope_profit_sum / $tani), $keta);
    $rui_all_nonope_profit_sum        = number_format(($rui_all_nonope_profit_sum / $tani), $keta);
    $p1_rui_all_nonope_profit_sum     = number_format(($p1_rui_all_nonope_profit_sum / $tani), $keta);
    $def_all_nonope_profit_sum        = number_format(($def_all_nonope_profit_sum / $tani), $keta);
    $h1_all_nonope_loss_sum           = number_format(($h1_all_nonope_loss_sum / $tani), $keta);
    $h2_all_nonope_loss_sum           = number_format(($h2_all_nonope_loss_sum / $tani), $keta);
    $h3_all_nonope_loss_sum           = number_format(($h3_all_nonope_loss_sum / $tani), $keta);
    $h4_all_nonope_loss_sum           = number_format(($h4_all_nonope_loss_sum / $tani), $keta);
    $rui_all_nonope_loss_sum          = number_format(($rui_all_nonope_loss_sum / $tani), $keta);
    $p1_rui_all_nonope_loss_sum       = number_format(($p1_rui_all_nonope_loss_sum / $tani), $keta);
    $def_all_nonope_loss_sum          = number_format(($def_all_nonope_loss_sum / $tani), $keta);
    $h1_all_current_profit            = number_format(($h1_all_current_profit / $tani), $keta);
    $h2_all_current_profit            = number_format(($h2_all_current_profit / $tani), $keta);
    $h3_all_current_profit            = number_format(($h3_all_current_profit / $tani), $keta);
    $h4_all_current_profit            = number_format(($h4_all_current_profit / $tani), $keta);
    $rui_all_current_profit           = number_format(($rui_all_current_profit / $tani), $keta);
    $p1_rui_all_current_profit        = number_format(($p1_rui_all_current_profit / $tani), $keta);
    $def_all_current_profit           = number_format(($def_all_current_profit / $tani), $keta);
    $h1_all_special_profit            = number_format(($h1_all_special_profit / $tani), $keta);
    $h2_all_special_profit            = number_format(($h2_all_special_profit / $tani), $keta);
    $h3_all_special_profit            = number_format(($h3_all_special_profit / $tani), $keta);
    $h4_all_special_profit            = number_format(($h4_all_special_profit / $tani), $keta);
    $rui_all_special_profit           = number_format(($rui_all_special_profit / $tani), $keta);
    $p1_rui_all_special_profit        = number_format(($p1_rui_all_special_profit / $tani), $keta);
    $def_all_special_profit           = number_format(($def_all_special_profit / $tani), $keta);
    $h1_all_special_loss              = number_format(($h1_all_special_loss / $tani), $keta);
    $h2_all_special_loss              = number_format(($h2_all_special_loss / $tani), $keta);
    $h3_all_special_loss              = number_format(($h3_all_special_loss / $tani), $keta);
    $h4_all_special_loss              = number_format(($h4_all_special_loss / $tani), $keta);
    $rui_all_special_loss             = number_format(($rui_all_special_loss / $tani), $keta);
    $p1_rui_all_special_loss          = number_format(($p1_rui_all_special_loss / $tani), $keta);
    $def_all_special_loss             = number_format(($def_all_special_loss / $tani), $keta);
    $h1_all_before_tax_net_profit     = number_format(($h1_all_before_tax_net_profit / $tani), $keta);
    $h2_all_before_tax_net_profit     = number_format(($h2_all_before_tax_net_profit / $tani), $keta);
    $h3_all_before_tax_net_profit     = number_format(($h3_all_before_tax_net_profit / $tani), $keta);
    $h4_all_before_tax_net_profit     = number_format(($h4_all_before_tax_net_profit / $tani), $keta);
    $rui_all_before_tax_net_profit    = number_format(($rui_all_before_tax_net_profit / $tani), $keta);
    $p1_rui_all_before_tax_net_profit = number_format(($p1_rui_all_before_tax_net_profit / $tani), $keta);
    $def_all_before_tax_net_profit    = number_format(($def_all_before_tax_net_profit / $tani), $keta);
    $h1_all_corporation_tax_etc       = number_format(($h1_all_corporation_tax_etc / $tani), $keta);
    $h2_all_corporation_tax_etc       = number_format(($h2_all_corporation_tax_etc / $tani), $keta);
    $h3_all_corporation_tax_etc       = number_format(($h3_all_corporation_tax_etc / $tani), $keta);
    $h4_all_corporation_tax_etc       = number_format(($h4_all_corporation_tax_etc / $tani), $keta);
    $rui_all_corporation_tax_etc      = number_format(($rui_all_corporation_tax_etc / $tani), $keta);
    $p1_rui_all_corporation_tax_etc   = number_format(($p1_rui_all_corporation_tax_etc / $tani), $keta);
    $def_all_corporation_tax_etc      = number_format(($def_all_corporation_tax_etc / $tani), $keta);
    $h1_all_pure_profit               = number_format(($h1_all_pure_profit / $tani), $keta);
    $h2_all_pure_profit               = number_format(($h2_all_pure_profit / $tani), $keta);
    $h3_all_pure_profit               = number_format(($h3_all_pure_profit / $tani), $keta);
    $h4_all_pure_profit               = number_format(($h4_all_pure_profit / $tani), $keta);
    $rui_all_pure_profit              = number_format(($rui_all_pure_profit / $tani), $keta);
    $p1_rui_all_pure_profit           = number_format(($p1_rui_all_pure_profit / $tani), $keta);
    $def_all_pure_profit              = number_format(($def_all_pure_profit / $tani), $keta);
} else{
    $all_uri                          = number_format(($all_uri / $tani), $keta);
    $p1_all_uri                       = number_format(($p1_all_uri / $tani), $keta);
    $p2_all_uri                       = number_format(($p2_all_uri / $tani), $keta);
    $t_all_uri                        = number_format(($t_all_uri / $tani), $keta);
    $rui_all_uri                      = number_format(($rui_all_uri / $tani), $keta);
    $p1_rui_all_uri                   = number_format(($p1_rui_all_uri / $tani), $keta);
    $def_all_uri                      = number_format(($def_all_uri / $tani), $keta);
    $all_urigen                       = number_format(($all_urigen / $tani), $keta);
    $p1_all_urigen                    = number_format(($p1_all_urigen / $tani), $keta);
    $p2_all_urigen                    = number_format(($p2_all_urigen / $tani), $keta);
    $t_all_urigen                     = number_format(($t_all_urigen / $tani), $keta);
    $rui_all_urigen                   = number_format(($rui_all_urigen / $tani), $keta);
    $p1_rui_all_urigen                = number_format(($p1_rui_all_urigen / $tani), $keta);
    $def_all_urigen                   = number_format(($def_all_urigen / $tani), $keta);
    $all_gross_profit                 = number_format(($all_gross_profit / $tani), $keta);
    $p1_all_gross_profit              = number_format(($p1_all_gross_profit / $tani), $keta);
    $p2_all_gross_profit              = number_format(($p2_all_gross_profit / $tani), $keta);
    $t_all_gross_profit               = number_format(($t_all_gross_profit / $tani), $keta);
    $rui_all_gross_profit             = number_format(($rui_all_gross_profit / $tani), $keta);
    $p1_rui_all_gross_profit          = number_format(($p1_rui_all_gross_profit / $tani), $keta);
    $def_all_gross_profit             = number_format(($def_all_gross_profit / $tani), $keta);
    $all_han_all                      = number_format(($all_han_all / $tani), $keta);
    $p1_all_han_all                   = number_format(($p1_all_han_all / $tani), $keta);
    $p2_all_han_all                   = number_format(($p2_all_han_all / $tani), $keta);
    $t_all_han_all                    = number_format(($t_all_han_all / $tani), $keta);
    $rui_all_han_all                  = number_format(($rui_all_han_all / $tani), $keta);
    $p1_rui_all_han_all               = number_format(($p1_rui_all_han_all / $tani), $keta);
    $def_all_han_all                  = number_format(($def_all_han_all / $tani), $keta);
    $all_ope_profit                   = number_format(($all_ope_profit / $tani), $keta);
    $p1_all_ope_profit                = number_format(($p1_all_ope_profit / $tani), $keta);
    $p2_all_ope_profit                = number_format(($p2_all_ope_profit / $tani), $keta);
    $t_all_ope_profit                 = number_format(($t_all_ope_profit / $tani), $keta);
    $rui_all_ope_profit               = number_format(($rui_all_ope_profit / $tani), $keta);
    $p1_rui_all_ope_profit            = number_format(($p1_rui_all_ope_profit / $tani), $keta);
    $def_all_ope_profit               = number_format(($def_all_ope_profit / $tani), $keta);
    $all_nonope_profit_sum            = number_format(($all_nonope_profit_sum / $tani), $keta);
    $p1_all_nonope_profit_sum         = number_format(($p1_all_nonope_profit_sum / $tani), $keta);
    $p2_all_nonope_profit_sum         = number_format(($p2_all_nonope_profit_sum / $tani), $keta);
    $t_all_nonope_profit_sum          = number_format(($t_all_nonope_profit_sum / $tani), $keta);
    $rui_all_nonope_profit_sum        = number_format(($rui_all_nonope_profit_sum / $tani), $keta);
    $p1_rui_all_nonope_profit_sum     = number_format(($p1_rui_all_nonope_profit_sum / $tani), $keta);
    $def_all_nonope_profit_sum        = number_format(($def_all_nonope_profit_sum / $tani), $keta);
    $all_nonope_loss_sum              = number_format(($all_nonope_loss_sum / $tani), $keta);
    $p1_all_nonope_loss_sum           = number_format(($p1_all_nonope_loss_sum / $tani), $keta);
    $p2_all_nonope_loss_sum           = number_format(($p2_all_nonope_loss_sum / $tani), $keta);
    $t_all_nonope_loss_sum            = number_format(($t_all_nonope_loss_sum / $tani), $keta);
    $rui_all_nonope_loss_sum          = number_format(($rui_all_nonope_loss_sum / $tani), $keta);
    $p1_rui_all_nonope_loss_sum       = number_format(($p1_rui_all_nonope_loss_sum / $tani), $keta);
    $def_all_nonope_loss_sum          = number_format(($def_all_nonope_loss_sum / $tani), $keta);
    $all_current_profit               = number_format(($all_current_profit / $tani), $keta);
    $p1_all_current_profit            = number_format(($p1_all_current_profit / $tani), $keta);
    $p2_all_current_profit            = number_format(($p2_all_current_profit / $tani), $keta);
    $t_all_current_profit             = number_format(($t_all_current_profit / $tani), $keta);
    $rui_all_current_profit           = number_format(($rui_all_current_profit / $tani), $keta);
    $p1_rui_all_current_profit        = number_format(($p1_rui_all_current_profit / $tani), $keta);
    $def_all_current_profit           = number_format(($def_all_current_profit / $tani), $keta);
    $all_special_profit               = number_format(($all_special_profit / $tani), $keta);
    $p1_all_special_profit            = number_format(($p1_all_special_profit / $tani), $keta);
    $p2_all_special_profit            = number_format(($p2_all_special_profit / $tani), $keta);
    $t_all_special_profit             = number_format(($t_all_special_profit / $tani), $keta);
    $rui_all_special_profit           = number_format(($rui_all_special_profit / $tani), $keta);
    $p1_rui_all_special_profit        = number_format(($p1_rui_all_special_profit / $tani), $keta);
    $def_all_special_profit           = number_format(($def_all_special_profit / $tani), $keta);
    $all_special_loss                 = number_format(($all_special_loss / $tani), $keta);
    $p1_all_special_loss              = number_format(($p1_all_special_loss / $tani), $keta);
    $p2_all_special_loss              = number_format(($p2_all_special_loss / $tani), $keta);
    $t_all_special_loss               = number_format(($t_all_special_loss / $tani), $keta);
    $rui_all_special_loss             = number_format(($rui_all_special_loss / $tani), $keta);
    $p1_rui_all_special_loss          = number_format(($p1_rui_all_special_loss / $tani), $keta);
    $def_all_special_loss             = number_format(($def_all_special_loss / $tani), $keta);
    $all_before_tax_net_profit        = number_format(($all_before_tax_net_profit / $tani), $keta);
    $p1_all_before_tax_net_profit     = number_format(($p1_all_before_tax_net_profit / $tani), $keta);
    $p2_all_before_tax_net_profit     = number_format(($p2_all_before_tax_net_profit / $tani), $keta);
    $t_all_before_tax_net_profit      = number_format(($t_all_before_tax_net_profit / $tani), $keta);
    $rui_all_before_tax_net_profit    = number_format(($rui_all_before_tax_net_profit / $tani), $keta);
    $p1_rui_all_before_tax_net_profit = number_format(($p1_rui_all_before_tax_net_profit / $tani), $keta);
    $def_all_before_tax_net_profit    = number_format(($def_all_before_tax_net_profit / $tani), $keta);
    $all_corporation_tax_etc          = number_format(($all_corporation_tax_etc / $tani), $keta);
    $p1_all_corporation_tax_etc       = number_format(($p1_all_corporation_tax_etc / $tani), $keta);
    $p2_all_corporation_tax_etc       = number_format(($p2_all_corporation_tax_etc / $tani), $keta);
    $t_all_corporation_tax_etc        = number_format(($t_all_corporation_tax_etc / $tani), $keta);
    $rui_all_corporation_tax_etc      = number_format(($rui_all_corporation_tax_etc / $tani), $keta);
    $p1_rui_all_corporation_tax_etc   = number_format(($p1_rui_all_corporation_tax_etc / $tani), $keta);
    $def_all_corporation_tax_etc      = number_format(($def_all_corporation_tax_etc / $tani), $keta);
    $all_pure_profit                  = number_format(($all_pure_profit / $tani), $keta);
    $p1_all_pure_profit               = number_format(($p1_all_pure_profit / $tani), $keta);
    $p2_all_pure_profit               = number_format(($p2_all_pure_profit / $tani), $keta);
    $t_all_pure_profit                = number_format(($t_all_pure_profit / $tani), $keta);
    $rui_all_pure_profit              = number_format(($rui_all_pure_profit / $tani), $keta);
    $p1_rui_all_pure_profit           = number_format(($p1_rui_all_pure_profit / $tani), $keta);
    $def_all_pure_profit              = number_format(($def_all_pure_profit / $tani), $keta);
}

/////////// HTML Header を出力してキャッシュを制御
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
/* 入力文字が数字かどうかチェック */
function isDigit(str) {
    var len=str.length;
    var c;
    for (i=0; i<len; i++) {
        c = str.charAt(i);
        if (("0" > c) || (c > "9")) {
            alert("数値以外は入力出来ません。");
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
            alert("数値以外は入力出来ません。");
            return false;
        }
    }
    return true;
}
/* 初期入力エレメントへフォーカスさせる */
function set_focus(){
    document.jin.jin_1.focus();
    document.jin.jin_1.select();
}
function data_input_click(obj) {
    return confirm("当月のデータを登録します。\n既にデータがある場合は上書きされます。");
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
                    <td rowspan='3' colspan='2' width='200' align='center' class='pt10b' bgcolor='#ffffc6'>項　　　目</td>
                    <?php if($tuki_chk==3) { ?>
                        <td colspan='5' rowspan='2' align='center' class='pt10b' bgcolor='#ffffc6'>四　半　期　損　益<BR>（<?php echo $b_yy ?>/04〜<?php echo $yy ?>/<?php echo $mm ?>）</td>
                    <?php } else { ?>
                        <td colspan='4' rowspan='2' align='center' class='pt10b' bgcolor='#ffffc6'>第　<?php echo $hanki ?>　四　半　期　損　益<BR>（<?php echo $yy ?>/<?php echo $p2_mm ?>〜<?php echo $yy ?>/<?php echo $mm ?>）</td>
                    <?php } ?>
                    <?php if($tuki_chk==3) { ?>
                        <td colspan='4' rowspan='2' align='center' class='pt10b' bgcolor='#ffffc6' style='border-right-style:none;'>第　<?php echo $ki ?>　期　損　益　累　計<BR>（<?php echo $b_yy ?>/04〜<?php echo $yy ?>/<?php echo $mm ?>）</td>
                    <?php } else { ?>
                        <td colspan='4' rowspan='2' align='center' class='pt10b' bgcolor='#ffffc6' style='border-right-style:none;'>第<?php echo $hanki ?>四半期までの累計<BR>（<?php echo $yy ?>/04〜<?php echo $yy ?>/<?php echo $mm ?>）</td>
                    <?php } ?>
                    <td colspan='3' align='center' class='pt10b' bgcolor='#ffffc6' style='border-left-style:none;'>　</td>
                </tr>
                <tr>
                    <?php if($tuki_chk==3) { ?>
                        <td colspan='3' nowrap align='center' class='pt10b' bgcolor='#ffffc6'>前期比較（<?php echo $b2_yy ?>/04〜<?php echo $b_yy ?>/<?php echo $mm ?>）</td>
                    <?php } else { ?>
                        <td colspan='3' nowrap align='center' class='pt10b' bgcolor='#ffffc6'>前期比較（<?php echo $b_yy ?>/04〜<?php echo $b_yy ?>/<?php echo $mm ?>）</td>
                    <?php } ?>
                </tr>
                <tr>
                    <?php if($tuki_chk==3) { ?>
                        <td nowrap align='center' class='pt10b' bgcolor='#ffffc6'>第１四半期</td>
                        <td nowrap align='center' class='pt10b' bgcolor='#ffffc6'>第２四半期</td>
                        <td nowrap align='center' class='pt10b' bgcolor='#ffffc6'>第３四半期</td>
                        <td nowrap align='center' class='pt10b' bgcolor='#ffffc6'>第４四半期</td>
                    <?php } else { ?>
                        <td nowrap align='center' class='pt10b' bgcolor='#ffffc6'><?php echo $yy ?>/<?php echo $p2_mm ?>月</td>
                        <td nowrap align='center' class='pt10b' bgcolor='#ffffc6'><?php echo $yy ?>/<?php echo $p1_mm ?>月</td>
                        <td nowrap align='center' class='pt10b' bgcolor='#ffffc6'><?php echo $yy ?>/<?php echo $mm ?>月</td>
                    <?php } ?>
                    <td nowrap align='center' class='pt10b' bgcolor='#ffffc6'>計</td>
                    <td nowrap align='center' class='pt10b' bgcolor='#d6d3ce'>予　　算</td>
                    <td nowrap align='center' class='pt10b' bgcolor='#ffffc6'>実　　績</td>
                    <td nowrap align='center' class='pt10b' bgcolor='#d6d3ce'>予算差異</td>
                    <td nowrap align='center' class='pt10b' bgcolor='#d6d3ce'>達成率</td>
                    <td nowrap align='center' class='pt10b' bgcolor='#ffffc6'>前期実績</td>
                    <td nowrap align='center' class='pt10b' bgcolor='#ffffc6'>増減額</td>
                    <td nowrap align='center' class='pt10b' bgcolor='#ffffc6'>増減率</td>
                </tr>
                <tr>
                    <td colspan='2' nowrap align='center' class='pt10b' bgcolor='#ceffce'>売　上　高</td>
                    <?php if($tuki_chk==3) { ?>
                        <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo $h1_all_uri ?></td>
                        <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo $h2_all_uri ?></td>
                        <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo $h3_all_uri ?></td>
                        <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo $h4_all_uri ?></td>
                        <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo $rui_all_uri ?></td>
                    <?php } else { ?>
                        <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo $p2_all_uri ?></td>
                        <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo $p1_all_uri ?></td>
                        <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo $all_uri ?></td>
                        <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo $t_all_uri ?></td>
                    <?php } ?>
                    <td nowrap align='right' class='pt10b' bgcolor='#d6d3ce'>　</td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo $rui_all_uri ?></td>
                    <td nowrap align='right' class='pt10b' bgcolor='#d6d3ce'>　</td>
                    <td nowrap align='right' class='pt10b' bgcolor='#d6d3ce'>　</td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo $p1_rui_all_uri ?></td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo $def_all_uri ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#ceffce'><?php echo $def_all_uri_rate ?>%</td>
                </tr>
                <tr>
                    <td nowrap align='center' class='pt10' bgcolor='#ceffce' style='border-bottom-style:none;'>　</td>
                    <td nowrap align='left' class='pt10' bgcolor='white'>　売　上　原　価</td>
                    <?php if($tuki_chk==3) { ?>
                        <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $h1_all_urigen ?></td>
                        <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $h2_all_urigen ?></td>
                        <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $h3_all_urigen ?></td>
                        <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $h4_all_urigen ?></td>
                        <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $rui_all_urigen ?></td>
                    <?php } else { ?>
                        <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $p2_all_urigen ?></td>
                        <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $p1_all_urigen ?></td>
                        <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $all_urigen ?></td>
                        <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $t_all_urigen ?></td>
                    <?php } ?>
                    <td nowrap align='right' class='pt10' bgcolor='#d6d3ce'>　</td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $rui_all_urigen ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#d6d3ce'>　</td>
                    <td nowrap align='right' class='pt10' bgcolor='#d6d3ce'>　</td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $p1_rui_all_urigen ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $def_all_urigen ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $def_all_urigen_rate ?>%</td>
                </tr>
                <tr>
                    <td nowrap align='center' class='pt10' bgcolor='#ceffce' style='border-top-style:none; border-right-style:none;'>　</td>
                    <td nowrap align='center' class='pt10b' bgcolor='#ceffce' style='border-left-style:none;'>売　上　総　利　益</td>
                    <?php if($tuki_chk==3) { ?>
                        <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo $h1_all_gross_profit ?></td>
                        <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo $h2_all_gross_profit ?></td>
                        <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo $h3_all_gross_profit ?></td>
                        <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo $h4_all_gross_profit ?></td>
                        <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo $rui_all_gross_profit ?></td>
                    <?php } else { ?>
                        <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo $p2_all_gross_profit ?></td>
                        <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo $p1_all_gross_profit ?></td>
                        <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo $all_gross_profit ?></td>
                        <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo $t_all_gross_profit ?></td>
                    <?php } ?>
                    <td nowrap align='right' class='pt10b' bgcolor='#d6d3ce'>　</td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo $rui_all_gross_profit ?></td>
                    <td nowrap align='right' class='pt10b' bgcolor='#d6d3ce'>　</td>
                    <td nowrap align='right' class='pt10b' bgcolor='#d6d3ce'>　</td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo $p1_rui_all_gross_profit ?></td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo $def_all_gross_profit ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#ceffce'><?php echo $def_all_gross_profit_rate ?>%</td>
                </tr>
                <tr>
                    <td nowrap align='center' class='pt10' bgcolor='#ceffce' style='border-bottom-style:none;'>　</td>
                    <td nowrap align='center' class='pt10' bgcolor='white'>販管費及び一般管理費計</td>
                    <?php if($tuki_chk==3) { ?>
                        <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $h1_all_han_all ?></td>
                        <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $h2_all_han_all ?></td>
                        <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $h3_all_han_all ?></td>
                        <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $h4_all_han_all ?></td>
                        <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $rui_all_han_all ?></td>
                    <?php } else { ?>
                        <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $p2_all_han_all ?></td>
                        <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $p1_all_han_all ?></td>
                        <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $all_han_all ?></td>
                        <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $t_all_han_all ?></td>
                    <?php } ?>
                    <td nowrap align='right' class='pt10' bgcolor='#d6d3ce'>　</td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $rui_all_han_all ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#d6d3ce'>　</td>
                    <td nowrap align='right' class='pt10' bgcolor='#d6d3ce'>　</td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $p1_rui_all_han_all ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $def_all_han_all ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $def_all_han_all_rate ?>%</td>
                </tr>
                <tr>
                    <td nowrap align='center' class='pt10' bgcolor='#ceffce' style='border-top-style:none; border-right-style:none;'>　</td>
                    <td nowrap align='center' class='pt10b' bgcolor='#ceffce' style='border-left-style:none;'>営　業　利　益</td>
                    <?php if($tuki_chk==3) { ?>
                        <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo $h1_all_ope_profit ?></td>
                        <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo $h2_all_ope_profit ?></td>
                        <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo $h3_all_ope_profit ?></td>
                        <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo $h4_all_ope_profit ?></td>
                        <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo $rui_all_ope_profit ?></td>
                    <?php } else { ?>
                        <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo $p2_all_ope_profit ?></td>
                        <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo $p1_all_ope_profit ?></td>
                        <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo $all_ope_profit ?></td>
                        <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo $t_all_ope_profit ?></td>
                    <?php } ?>
                    <td nowrap align='right' class='pt10b' bgcolor='#d6d3ce'>　</td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo $rui_all_ope_profit ?></td>
                    <td nowrap align='right' class='pt10b' bgcolor='#d6d3ce'>　</td>
                    <td nowrap align='right' class='pt10b' bgcolor='#d6d3ce'>　</td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo $p1_rui_all_ope_profit ?></td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo $def_all_ope_profit ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#ceffce'><?php echo $def_all_ope_profit_rate ?>%</td>
                </tr>
                <tr>
                    <td nowrap align='center' class='pt10' bgcolor='#ceffce' style='border-bottom-style:none;'>　</td>
                    <td nowrap align='left' class='pt10' bgcolor='white'>　営業外収益 計</td>
                    <?php if($tuki_chk==3) { ?>
                        <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $h1_all_nonope_profit_sum ?></td>
                        <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $h2_all_nonope_profit_sum ?></td>
                        <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $h3_all_nonope_profit_sum ?></td>
                        <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $h4_all_nonope_profit_sum ?></td>
                        <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $rui_all_nonope_profit_sum ?></td>
                    <?php } else { ?>
                        <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $p2_all_nonope_profit_sum ?></td>
                        <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $p1_all_nonope_profit_sum ?></td>
                        <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $all_nonope_profit_sum ?></td>
                        <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $t_all_nonope_profit_sum ?></td>
                    <?php } ?>
                    <td nowrap align='right' class='pt10' bgcolor='#d6d3ce'>　</td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $rui_all_nonope_profit_sum ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#d6d3ce'>　</td>
                    <td nowrap align='right' class='pt10' bgcolor='#d6d3ce'>　</td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $p1_rui_all_nonope_profit_sum ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $def_all_nonope_profit_sum ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $def_all_nonope_profit_sum_rate ?>%</td>
                </tr>
                <tr>
                    <td nowrap align='center' class='pt10' bgcolor='#ceffce' style='border-bottom-style:none; border-top-style:none;'>　</td>
                    <td nowrap align='left' class='pt10' bgcolor='white'>　営業外費用 計</td>
                    <?php if($tuki_chk==3) { ?>
                        <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $h1_all_nonope_loss_sum ?></td>
                        <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $h2_all_nonope_loss_sum ?></td>
                        <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $h3_all_nonope_loss_sum ?></td>
                        <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $h4_all_nonope_loss_sum ?></td>
                        <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $rui_all_nonope_loss_sum ?></td>
                    <?php } else { ?>
                        <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $p2_all_nonope_loss_sum ?></td>
                        <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $p1_all_nonope_loss_sum ?></td>
                        <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $all_nonope_loss_sum ?></td>
                        <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $t_all_nonope_loss_sum ?></td>
                    <?php } ?>
                    <td nowrap align='right' class='pt10' bgcolor='#d6d3ce'>　</td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $rui_all_nonope_loss_sum ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#d6d3ce'>　</td>
                    <td nowrap align='right' class='pt10' bgcolor='#d6d3ce'>　</td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $p1_rui_all_nonope_loss_sum ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $def_all_nonope_loss_sum ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $def_all_nonope_loss_sum_rate ?>%</td>
                <tr>
                    <td nowrap align='center' class='pt10' bgcolor='#ceffce' style='border-top-style:none; border-right-style:none;'>　</td>
                    <td nowrap align='center' class='pt10b' bgcolor='#ceffce' style='border-left-style:none;'>経　常　利　益</td>
                    <?php if($tuki_chk==3) { ?>
                        <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo $h1_all_current_profit ?></td>
                        <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo $h2_all_current_profit ?></td>
                        <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo $h3_all_current_profit ?></td>
                        <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo $h4_all_current_profit ?></td>
                        <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo $rui_all_current_profit ?></td>
                    <?php } else { ?>
                        <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo $p2_all_current_profit ?></td>
                        <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo $p1_all_current_profit ?></td>
                        <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo $all_current_profit ?></td>
                        <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo $t_all_current_profit ?></td>
                    <?php } ?>
                    <td nowrap align='right' class='pt10b' bgcolor='#d6d3ce'>　</td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo $rui_all_current_profit ?></td>
                    <td nowrap align='right' class='pt10b' bgcolor='#d6d3ce'>　</td>
                    <td nowrap align='right' class='pt10b' bgcolor='#d6d3ce'>　</td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo $p1_rui_all_current_profit ?></td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo $def_all_current_profit ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#ceffce'><?php echo $def_all_current_profit_rate ?>%</td>
                </tr>
                <tr>
                    <td nowrap align='center' class='pt10' bgcolor='#ceffce' style='border-bottom-style:none;'>　</td>
                    <td nowrap align='left' class='pt10' bgcolor='white'>　特　別　利　益</td>
                    <?php if($tuki_chk==3) { ?>
                        <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $h1_all_special_profit ?></td>
                        <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $h2_all_special_profit ?></td>
                        <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $h3_all_special_profit ?></td>
                        <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $h4_all_special_profit ?></td>
                        <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $rui_all_special_profit ?></td>
                    <?php } else { ?>
                        <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $p2_all_special_profit ?></td>
                        <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $p1_all_special_profit ?></td>
                        <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $all_special_profit ?></td>
                        <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $t_all_special_profit ?></td>
                    <?php } ?>
                    <td nowrap align='right' class='pt10' bgcolor='#d6d3ce'>　</td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $rui_all_special_profit ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#d6d3ce'>　</td>
                    <td nowrap align='right' class='pt10' bgcolor='#d6d3ce'>　</td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $p1_rui_all_special_profit ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $def_all_special_profit ?></td>
                    <td nowrap align='center' class='pt10' bgcolor='white'>―</td>
                </tr>
                <tr>
                    <td nowrap align='center' class='pt10' bgcolor='#ceffce' style='border-bottom-style:none; border-top-style:none;'>　</td>
                    <td nowrap align='left' class='pt10' bgcolor='white'>　特　別　損　失</td>
                    <?php if($tuki_chk==3) { ?>
                        <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $h1_all_special_loss ?></td>
                        <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $h2_all_special_loss ?></td>
                        <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $h3_all_special_loss ?></td>
                        <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $h4_all_special_loss ?></td>
                        <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $rui_all_special_loss ?></td>
                    <?php } else { ?>
                        <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $p2_all_special_loss ?></td>
                        <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $p1_all_special_loss ?></td>
                        <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $all_special_loss ?></td>
                        <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $t_all_special_loss ?></td>
                    <?php } ?>
                    <td nowrap align='right' class='pt10' bgcolor='#d6d3ce'>　</td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $rui_all_special_loss ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#d6d3ce'>　</td>
                    <td nowrap align='right' class='pt10' bgcolor='#d6d3ce'>　</td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $p1_rui_all_special_loss ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $def_all_special_loss ?></td>
                    <td nowrap align='center' class='pt10' bgcolor='white'>―</td>
                </tr>
                <tr>
                    <td nowrap align='center' class='pt10' bgcolor='#ceffce' style='border-top-style:none; border-right-style:none;'>　</td>
                    <td nowrap align='center' class='pt10b' bgcolor='#ceffce' style='border-left-style:none;'>税引前当期利益金額</td>
                    <?php if($tuki_chk==3) { ?>
                        <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo $h1_all_before_tax_net_profit ?></td>
                        <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo $h2_all_before_tax_net_profit ?></td>
                        <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo $h3_all_before_tax_net_profit ?></td>
                        <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo $h4_all_before_tax_net_profit ?></td>
                        <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo $rui_all_before_tax_net_profit ?></td>
                    <?php } else { ?>
                        <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo $p2_all_before_tax_net_profit ?></td>
                        <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo $p1_all_before_tax_net_profit ?></td>
                        <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo $all_before_tax_net_profit ?></td>
                        <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo $t_all_before_tax_net_profit ?></td>
                    <?php } ?>
                    <td nowrap align='right' class='pt10b' bgcolor='#d6d3ce'>　</td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo $rui_all_before_tax_net_profit ?></td>
                    <td nowrap align='right' class='pt10b' bgcolor='#d6d3ce'>　</td>
                    <td nowrap align='right' class='pt10b' bgcolor='#d6d3ce'>　</td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo $p1_rui_all_before_tax_net_profit ?></td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo $def_all_before_tax_net_profit ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#ceffce'><?php echo $def_all_before_tax_net_profit_rate ?>%</td>
                </tr>
                <tr>
                    <td nowrap align='center' class='pt10' bgcolor='#ceffce' style='border-bottom-style:none;'>　</td>
                    <td nowrap align='left' class='pt10' bgcolor='white'>　法人税、事業税</td>
                    <?php if($tuki_chk==3) { ?>
                        <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $h1_all_corporation_tax_etc ?></td>
                        <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $h2_all_corporation_tax_etc ?></td>
                        <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $h3_all_corporation_tax_etc ?></td>
                        <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $h4_all_corporation_tax_etc ?></td>
                        <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $rui_all_corporation_tax_etc ?></td>
                    <?php } else { ?>
                        <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $p2_all_corporation_tax_etc ?></td>
                        <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $p1_all_corporation_tax_etc ?></td>
                        <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $all_corporation_tax_etc ?></td>
                        <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $t_all_corporation_tax_etc ?></td>
                    <?php } ?>
                    <td nowrap align='right' class='pt10' bgcolor='#d6d3ce'>　</td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $rui_all_corporation_tax_etc ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#d6d3ce'>　</td>
                    <td nowrap align='right' class='pt10' bgcolor='#d6d3ce'>　</td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $p1_rui_all_corporation_tax_etc ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $def_all_corporation_tax_etc ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $def_all_corporation_tax_etc_rate ?>%</td>
                </tr>
                <tr>
                    <td nowrap align='center' class='pt10' bgcolor='#ceffce' style='border-top-style:none; border-right-style:none;'>　</td>
                    <td nowrap align='center' class='pt10b' bgcolor='#ceffce' style='border-left-style:none;'>当　期　利　益</td>
                    <?php if($tuki_chk==3) { ?>
                        <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo $h1_all_pure_profit ?></td>
                        <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo $h2_all_pure_profit ?></td>
                        <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo $h3_all_pure_profit ?></td>
                        <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo $h4_all_pure_profit ?></td>
                        <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo $rui_all_pure_profit ?></td>
                    <?php } else { ?>
                        <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo $p2_all_pure_profit ?></td>
                        <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo $p1_all_pure_profit ?></td>
                        <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo $all_pure_profit ?></td>
                        <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo $t_all_pure_profit ?></td>
                    <?php } ?>
                    <td nowrap align='right' class='pt10b' bgcolor='#d6d3ce'>　</td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo $rui_all_pure_profit ?></td>
                    <td nowrap align='right' class='pt10b' bgcolor='#d6d3ce'>　</td>
                    <td nowrap align='right' class='pt10b' bgcolor='#d6d3ce'>　</td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo $p1_rui_all_pure_profit ?></td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo $def_all_pure_profit ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#ceffce'><?php echo $def_all_pure_profit_rate ?>%</td>
                </tr>
            </TBODY>
        </table>
        </td>
        </tr>
    </table>
    </center>
</body>
</html>
