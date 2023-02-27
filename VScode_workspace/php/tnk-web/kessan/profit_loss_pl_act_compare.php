<?php
//////////////////////////////////////////////////////////////////////////////
// 月次損益関係 月次調査資料Web版 対前月差額                                //
// Copyright (C) 2010 - 2019 Norihisa.Ohya norihisa_ooya@nitto-kohki.co.jp  //
// Changed history                                                          //
// 2010/01/14 Created   profit_loss_pl_act_compare.php                      //
// 2010/01/15 200912分試修労務費調整を埋め込み                              //
// 2010/01/19 200912度の業務委託収入とその他を調整（1月度戻しの分も）       //
// 2010/02/05 201001度より営業外を人員比で配賦するように変更                //
//            ～再計算を取得するように変更                                  //
// 2010/02/08 201001度から配賦した労務費を加味するように変更                //
// 2010/03/04 201002度営業外収益その他の調整を追加。201003には戻し          //
// 2010/05/11 201004度リニアの売上(ツール)255,240円を商管に移動        大谷 //
// 2012/02/09 データ取得を都度計算ではなく、履歴から取得に変更         大谷 //
// 2019/02/08 リニア標準・ツールを追加し、最新の損益に対応             大谷 //
//            2016年4月～（前月は2016年3月～）対応                          //
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
///// 呼出先のaction名とアドレス設定
$menu->set_action('特記事項入力',   PL . 'profit_loss_comment_put.php');

///// 期・月の取得
$ki = Ym_to_tnk($_SESSION['pl_ym']);
$tuki = substr($_SESSION['pl_ym'],4,2);

///// タイトル名(ソースのタイトル名とフォームのタイトル名)
$menu->set_title("第 {$ki} 期　{$tuki} 月度　月 次 調 査 資 料（ 対 前 月 比 ）");

///// 対象当月
$yyyymm = $_SESSION['pl_ym'];
///// 対象前月
if (substr($yyyymm,4,2)!=01) {
    $p1_ym = $yyyymm - 1;
} else {
    $p1_ym = $yyyymm - 100;
    $p1_ym = $p1_ym + 11;
}
///// 期初年月の算出
$yyyy = substr($yyyymm, 0,4);
$mm   = substr($yyyymm, 4,2);
if (($mm >= 1) && ($mm <= 3)) {
    $yyyy = ($yyyy - 1);
}
$str_ym = $yyyy . "04";     // 期初年月

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
$query = sprintf("select kin from profit_loss_pl_history where pl_bs_ym=%d and note='カプラ売上高'", $yyyymm);
if (getUniResult($query, $c_uri) < 1) {
    $c_uri = 0;                 // 検索失敗
}
if ($yyyymm >= 201604) {
    $query = sprintf("select kin from profit_loss_pl_history where pl_bs_ym=%d and note='リニア標準売上高'", $yyyymm);
    if (getUniResult($query, $l_uri) < 1) {
        $l_uri = 0;                 // 検索失敗
    }
} else {
    $query = sprintf("select kin from profit_loss_pl_history where pl_bs_ym=%d and note='リニア売上高'", $yyyymm);
    if (getUniResult($query, $l_uri) < 1) {
        $l_uri = 0;                 // 検索失敗
    }
}
if ($yyyymm >= 201604) {
    $query = sprintf("select kin from profit_loss_pl_history where pl_bs_ym=%d and note='機工売上高'", $yyyymm);
    if (getUniResult($query, $t_uri) < 1) {
        $t_uri = 0;                 // 検索失敗
    }
} else {
    $t_uri = 0;                 // 検索失敗
}
$query = sprintf("select kin from profit_loss_pl_history where pl_bs_ym=%d and note='試験修理売上高'", $yyyymm);
if (getUniResult($query, $s_uri) < 1) {
    $s_uri = 0;                 // 検索失敗
}
$query = sprintf("select kin from profit_loss_pl_history where pl_bs_ym=%d and note='商品管理売上高'", $yyyymm);
if (getUniResult($query, $b_uri) < 1) {
    $b_uri = 0;                 // 検索失敗
}
$query = sprintf("select kin from profit_loss_pl_history where pl_bs_ym=%d and note='全体売上高'", $yyyymm);
if (getUniResult($query, $all_uri) < 1) {
    $all_uri = 0;                 // 検索失敗
}
    ///// 前期
$query = sprintf("select kin from profit_loss_pl_history where pl_bs_ym=%d and note='カプラ売上高'", $p1_ym);
if (getUniResult($query, $p1_c_uri) < 1) {
    $p1_c_uri = 0;                      // 検索失敗
}
if ($p1_ym >= 201603) {
    $query = sprintf("select kin from profit_loss_pl_history where pl_bs_ym=%d and note='リニア標準売上高'", $p1_ym);
    if (getUniResult($query, $p1_l_uri) < 1) {
        $p1_l_uri = 0;                      // 検索失敗
    }
} else {
    $query = sprintf("select kin from profit_loss_pl_history where pl_bs_ym=%d and note='リニア売上高'", $p1_ym);
    if (getUniResult($query, $p1_l_uri) < 1) {
        $p1_l_uri = 0;                      // 検索失敗
    }
}
if ($p1_ym >= 201603) {
    $query = sprintf("select kin from profit_loss_pl_history where pl_bs_ym=%d and note='機工売上高'", $p1_ym);
    if (getUniResult($query, $p1_t_uri) < 1) {
        $p1_t_uri = 0;                 // 検索失敗
    }
} else {
    $p1_t_uri = 0;                 // 検索失敗
}
$query = sprintf("select kin from profit_loss_pl_history where pl_bs_ym=%d and note='試験修理売上高'", $p1_ym);
if (getUniResult($query, $p1_s_uri) < 1) {
    $p1_s_uri = 0;                      // 検索失敗
}
$query = sprintf("select kin from profit_loss_pl_history where pl_bs_ym=%d and note='商品管理売上高'", $p1_ym);
if (getUniResult($query, $p1_b_uri) < 1) {
    $p1_b_uri = 0;                      // 検索失敗
}
$query = sprintf("select kin from profit_loss_pl_history where pl_bs_ym=%d and note='全体売上高'", $p1_ym);
if (getUniResult($query, $p1_all_uri) < 1) {
    $p1_all_uri = 0;                      // 検索失敗
}

/********** 期首材料仕掛品棚卸高 **********/
    ///// 当期
$query = sprintf("select kin from profit_loss_pl_history where pl_bs_ym=%d and note='全体期首材料仕掛品棚卸高'", $yyyymm);
if (getUniResult($query, $all_invent) < 1) {
    $all_invent = 0;                        // 検索失敗
}
$query = sprintf("select kin from profit_loss_pl_history where pl_bs_ym=%d and note='カプラ期首材料仕掛品棚卸高'", $yyyymm);
if (getUniResult($query, $c_invent) < 1) {
    $c_invent = 0;                          // 検索失敗
}
if ($yyyymm >= 201604) {
    $query = sprintf("select kin from profit_loss_pl_history where pl_bs_ym=%d and note='リニア標準期首材料仕掛品棚卸高'", $yyyymm);
    if (getUniResult($query, $l_invent) < 1) {
        $l_invent = 0;                          // 検索失敗
    }
} else {
    $query = sprintf("select kin from profit_loss_pl_history where pl_bs_ym=%d and note='リニア期首材料仕掛品棚卸高'", $yyyymm);
    if (getUniResult($query, $l_invent) < 1) {
        $l_invent = 0;                          // 検索失敗
    }
}
if ($yyyymm >= 201604) {
    $query = sprintf("select kin from profit_loss_pl_history where pl_bs_ym=%d and note='機工期首材料仕掛品棚卸高'", $yyyymm);
    if (getUniResult($query, $t_invent) < 1) {
        $t_invent = 0;                 // 検索失敗
    }
} else {
    $t_invent = 0;                 // 検索失敗
}
$query = sprintf("select kin from profit_loss_pl_history where pl_bs_ym=%d and note='商品管理期首材料仕掛品棚卸高'", $yyyymm);
if (getUniResult($query, $b_invent) < 1) {
    $b_invent = 0;                          // 検索失敗
}
$query = sprintf("select kin from profit_loss_pl_history where pl_bs_ym=%d and note='試験修理期首材料仕掛品棚卸高'", $yyyymm);
if (getUniResult($query, $s_invent) < 1) {
    $s_invent = 0;                          // 検索失敗
}
    ///// 前期
$query = sprintf("select kin from profit_loss_pl_history where pl_bs_ym=%d and note='全体期首材料仕掛品棚卸高'", $p1_ym);
if (getUniResult($query, $p1_all_invent) < 1) {
    $p1_all_invent = 0;                        // 検索失敗
}
$query = sprintf("select kin from profit_loss_pl_history where pl_bs_ym=%d and note='カプラ期首材料仕掛品棚卸高'", $p1_ym);
if (getUniResult($query, $p1_c_invent) < 1) {
    $p1_c_invent = 0;                          // 検索失敗
}
if ($p1_ym >= 201603) {
    $query = sprintf("select kin from profit_loss_pl_history where pl_bs_ym=%d and note='リニア標準期首材料仕掛品棚卸高'", $p1_ym);
    if (getUniResult($query, $p1_l_invent) < 1) {
        $p1_l_invent = 0;                          // 検索失敗
    }
} else {
    $query = sprintf("select kin from profit_loss_pl_history where pl_bs_ym=%d and note='リニア期首材料仕掛品棚卸高'", $p1_ym);
    if (getUniResult($query, $p1_l_invent) < 1) {
        $p1_l_invent = 0;                          // 検索失敗
    }
}
if ($p1_ym >= 201603) {
    $query = sprintf("select kin from profit_loss_pl_history where pl_bs_ym=%d and note='機工期首材料仕掛品棚卸高'", $p1_ym);
    if (getUniResult($query, $p1_t_invent) < 1) {
        $p1_t_invent = 0;                 // 検索失敗
    }
} else {
    $p1_t_invent = 0;                 // 検索失敗
}
$query = sprintf("select kin from profit_loss_pl_history where pl_bs_ym=%d and note='商品管理期首材料仕掛品棚卸高'", $p1_ym);
if (getUniResult($query, $p1_b_invent) < 1) {
    $p1_b_invent = 0;                          // 検索失敗
}
$query = sprintf("select kin from profit_loss_pl_history where pl_bs_ym=%d and note='試験修理期首材料仕掛品棚卸高'", $p1_ym);
if (getUniResult($query, $p1_s_invent) < 1) {
    $p1_s_invent = 0;                          // 検索失敗
}

/********** 材料費(仕入高) **********/
    ///// 当期
$query = sprintf("select kin from profit_loss_pl_history where pl_bs_ym=%d and note='カプラ材料費(仕入高)'", $yyyymm);
if (getUniResult($query, $c_metarial) < 1) {
    $c_metarial = 0;                 // 検索失敗
}
if ($yyyymm >= 201604) {
    $query = sprintf("select kin from profit_loss_pl_history where pl_bs_ym=%d and note='リニア標準材料費(仕入高)'", $yyyymm);
    if (getUniResult($query, $l_metarial) < 1) {
        $l_metarial = 0;                 // 検索失敗
    }
} else {
    $query = sprintf("select kin from profit_loss_pl_history where pl_bs_ym=%d and note='リニア材料費(仕入高)'", $yyyymm);
    if (getUniResult($query, $l_metarial) < 1) {
        $l_metarial = 0;                 // 検索失敗
    }
}
if ($yyyymm >= 201604) {
    $query = sprintf("select kin from profit_loss_pl_history where pl_bs_ym=%d and note='機工材料費(仕入高)'", $yyyymm);
    if (getUniResult($query, $t_metarial) < 1) {
        $t_metarial = 0;                 // 検索失敗
    }
} else {
    $t_metarial = 0;                 // 検索失敗
}
$query = sprintf("select kin from profit_loss_pl_history where pl_bs_ym=%d and note='試験修理材料費(仕入高)'", $yyyymm);
if (getUniResult($query, $s_metarial) < 1) {
    $s_metarial = 0;                 // 検索失敗
}
$query = sprintf("select kin from profit_loss_pl_history where pl_bs_ym=%d and note='商品管理材料費(仕入高)'", $yyyymm);
if (getUniResult($query, $b_metarial) < 1) {
    $b_metarial = 0;                 // 検索失敗
}
$query = sprintf("select kin from profit_loss_pl_history where pl_bs_ym=%d and note='全体材料費(仕入高)'", $yyyymm);
if (getUniResult($query, $all_metarial) < 1) {
    $all_metarial = 0;                 // 検索失敗
}
    ///// 前期
$query = sprintf("select kin from profit_loss_pl_history where pl_bs_ym=%d and note='カプラ材料費(仕入高)'", $p1_ym);
if (getUniResult($query, $p1_c_metarial) < 1) {
    $p1_c_metarial = 0;                      // 検索失敗
}
if ($p1_ym >= 201603) {
    $query = sprintf("select kin from profit_loss_pl_history where pl_bs_ym=%d and note='リニア標準材料費(仕入高)'", $p1_ym);
    if (getUniResult($query, $p1_l_metarial) < 1) {
        $p1_l_metarial = 0;                      // 検索失敗
    }
} else {
    $query = sprintf("select kin from profit_loss_pl_history where pl_bs_ym=%d and note='リニア材料費(仕入高)'", $p1_ym);
    if (getUniResult($query, $p1_l_metarial) < 1) {
        $p1_l_metarial = 0;                      // 検索失敗
    }
}
if ($p1_ym >= 201603) {
    $query = sprintf("select kin from profit_loss_pl_history where pl_bs_ym=%d and note='機工材料費(仕入高)'", $p1_ym);
    if (getUniResult($query, $p1_t_metarial) < 1) {
        $p1_t_metarial = 0;                 // 検索失敗
    }
} else {
    $p1_t_metarial = 0;                 // 検索失敗
}
$query = sprintf("select kin from profit_loss_pl_history where pl_bs_ym=%d and note='試験修理材料費(仕入高)'", $p1_ym);
if (getUniResult($query, $p1_s_metarial) < 1) {
    $p1_s_metarial = 0;                      // 検索失敗
}
$query = sprintf("select kin from profit_loss_pl_history where pl_bs_ym=%d and note='商品管理材料費(仕入高)'", $p1_ym);
if (getUniResult($query, $p1_b_metarial) < 1) {
    $p1_b_metarial = 0;                      // 検索失敗
}
$query = sprintf("select kin from profit_loss_pl_history where pl_bs_ym=%d and note='全体材料費(仕入高)'", $p1_ym);
if (getUniResult($query, $p1_all_metarial) < 1) {
    $p1_all_metarial = 0;                      // 検索失敗
}

/********** 労務費 **********/
    ///// 当期
$query = sprintf("select kin from profit_loss_pl_history where pl_bs_ym=%d and note='カプラ労務費'", $yyyymm);
if (getUniResult($query, $c_roumu) < 1) {
    $c_roumu = 0;                 // 検索失敗
}
if ($yyyymm >= 201604) {
    $query = sprintf("select kin from profit_loss_pl_history where pl_bs_ym=%d and note='リニア標準労務費'", $yyyymm);
    if (getUniResult($query, $l_roumu) < 1) {
        $l_roumu = 0;                 // 検索失敗
    }
} else {
    $query = sprintf("select kin from profit_loss_pl_history where pl_bs_ym=%d and note='リニア労務費'", $yyyymm);
    if (getUniResult($query, $l_roumu) < 1) {
        $l_roumu = 0;                 // 検索失敗
    }
}
if ($yyyymm >= 201604) {
    $query = sprintf("select kin from profit_loss_pl_history where pl_bs_ym=%d and note='機工労務費'", $yyyymm);
    if (getUniResult($query, $t_roumu) < 1) {
        $t_roumu = 0;                 // 検索失敗
    }
} else {
    $t_roumu = 0;                 // 検索失敗
}
$query = sprintf("select kin from profit_loss_pl_history where pl_bs_ym=%d and note='試験修理労務費'", $yyyymm);
if (getUniResult($query, $s_roumu) < 1) {
    $s_roumu = 0;                 // 検索失敗
}
$query = sprintf("select kin from profit_loss_pl_history where pl_bs_ym=%d and note='商品管理労務費'", $yyyymm);
if (getUniResult($query, $b_roumu) < 1) {
    $b_roumu = 0;                 // 検索失敗
}
$query = sprintf("select kin from profit_loss_pl_history where pl_bs_ym=%d and note='全体労務費'", $yyyymm);
if (getUniResult($query, $all_roumu) < 1) {
    $all_roumu = 0;                 // 検索失敗
}
    ///// 前期
$query = sprintf("select kin from profit_loss_pl_history where pl_bs_ym=%d and note='カプラ労務費'", $p1_ym);
if (getUniResult($query, $p1_c_roumu) < 1) {
    $p1_c_roumu = 0;                      // 検索失敗
}
if ($p1_ym >= 201603) {
    $query = sprintf("select kin from profit_loss_pl_history where pl_bs_ym=%d and note='リニア標準労務費'", $p1_ym);
    if (getUniResult($query, $p1_l_roumu) < 1) {
        $p1_l_roumu = 0;                      // 検索失敗
    }
} else {
    $query = sprintf("select kin from profit_loss_pl_history where pl_bs_ym=%d and note='リニア労務費'", $p1_ym);
    if (getUniResult($query, $p1_l_roumu) < 1) {
        $p1_l_roumu = 0;                      // 検索失敗
    }
}
if ($p1_ym >= 201603) {
    $query = sprintf("select kin from profit_loss_pl_history where pl_bs_ym=%d and note='機工労務費'", $p1_ym);
    if (getUniResult($query, $p1_t_roumu) < 1) {
        $p1_t_roumu = 0;                 // 検索失敗
    }
} else {
    $p1_t_roumu = 0;                 // 検索失敗
}
$query = sprintf("select kin from profit_loss_pl_history where pl_bs_ym=%d and note='試験修理労務費'", $p1_ym);
if (getUniResult($query, $p1_s_roumu) < 1) {
    $p1_s_roumu = 0;                      // 検索失敗
}
$query = sprintf("select kin from profit_loss_pl_history where pl_bs_ym=%d and note='商品管理労務費'", $p1_ym);
if (getUniResult($query, $p1_b_roumu) < 1) {
    $p1_b_roumu = 0;                      // 検索失敗
}
$query = sprintf("select kin from profit_loss_pl_history where pl_bs_ym=%d and note='全体労務費'", $p1_ym);
if (getUniResult($query, $p1_all_roumu) < 1) {
    $p1_all_roumu = 0;                      // 検索失敗
}

/********** 経費(製造経費) **********/
    ///// 当期
$query = sprintf("select kin from profit_loss_pl_history where pl_bs_ym=%d and note='カプラ製造経費'", $yyyymm);
if (getUniResult($query, $c_expense) < 1) {
    $c_expense = 0;                 // 検索失敗
}
if ($yyyymm >= 201604) {
    $query = sprintf("select kin from profit_loss_pl_history where pl_bs_ym=%d and note='リニア標準製造経費'", $yyyymm);
    if (getUniResult($query, $l_expense) < 1) {
        $l_expense = 0;                 // 検索失敗
    }
} else {
    $query = sprintf("select kin from profit_loss_pl_history where pl_bs_ym=%d and note='リニア製造経費'", $yyyymm);
    if (getUniResult($query, $l_expense) < 1) {
        $l_expense = 0;                 // 検索失敗
    }
}
if ($yyyymm >= 201604) {
    $query = sprintf("select kin from profit_loss_pl_history where pl_bs_ym=%d and note='機工製造経費'", $yyyymm);
    if (getUniResult($query, $t_expense) < 1) {
        $t_expense = 0;                 // 検索失敗
    }
} else {
    $t_expense = 0;                 // 検索失敗
}
$query = sprintf("select kin from profit_loss_pl_history where pl_bs_ym=%d and note='試験修理製造経費'", $yyyymm);
if (getUniResult($query, $s_expense) < 1) {
    $s_expense = 0;                 // 検索失敗
}
$query = sprintf("select kin from profit_loss_pl_history where pl_bs_ym=%d and note='商品管理製造経費'", $yyyymm);
if (getUniResult($query, $b_expense) < 1) {
    $b_expense = 0;                 // 検索失敗
}
$query = sprintf("select kin from profit_loss_pl_history where pl_bs_ym=%d and note='全体製造経費'", $yyyymm);
if (getUniResult($query, $all_expense) < 1) {
    $all_expense = 0;                 // 検索失敗
}
    ///// 前期
$query = sprintf("select kin from profit_loss_pl_history where pl_bs_ym=%d and note='カプラ製造経費'", $p1_ym);
if (getUniResult($query, $p1_c_expense) < 1) {
    $p1_c_expense = 0;                      // 検索失敗
}
if ($p1_ym >= 201603) {
    $query = sprintf("select kin from profit_loss_pl_history where pl_bs_ym=%d and note='リニア標準製造経費'", $p1_ym);
    if (getUniResult($query, $p1_l_expense) < 1) {
        $p1_l_expense = 0;                      // 検索失敗
    }
} else {
    $query = sprintf("select kin from profit_loss_pl_history where pl_bs_ym=%d and note='リニア製造経費'", $p1_ym);
    if (getUniResult($query, $p1_l_expense) < 1) {
        $p1_l_expense = 0;                      // 検索失敗
    }
}
if ($p1_ym >= 201603) {
    $query = sprintf("select kin from profit_loss_pl_history where pl_bs_ym=%d and note='機工製造経費'", $p1_ym);
    if (getUniResult($query, $p1_t_expense) < 1) {
        $p1_t_expense = 0;                 // 検索失敗
    }
} else {
    $p1_t_expense = 0;                 // 検索失敗
}
$query = sprintf("select kin from profit_loss_pl_history where pl_bs_ym=%d and note='試験修理製造経費'", $p1_ym);
if (getUniResult($query, $p1_s_expense) < 1) {
    $p1_s_expense = 0;                      // 検索失敗
}
$query = sprintf("select kin from profit_loss_pl_history where pl_bs_ym=%d and note='商品管理製造経費'", $p1_ym);
if (getUniResult($query, $p1_b_expense) < 1) {
    $p1_b_expense = 0;                      // 検索失敗
}
$query = sprintf("select kin from profit_loss_pl_history where pl_bs_ym=%d and note='全体製造経費'", $p1_ym);
if (getUniResult($query, $p1_all_expense) < 1) {
    $p1_all_expense = 0;                      // 検索失敗
}

/********** 期末材料仕掛品棚卸高 **********/
    ///// 当期
$query = sprintf("select kin from profit_loss_pl_history where pl_bs_ym=%d and note='全体期末材料仕掛品棚卸高'", $yyyymm);
if (getUniResult($query, $all_endinv) < 1) {
    $all_endinv = 0;                        // 検索失敗
}
$query = sprintf("select kin from profit_loss_pl_history where pl_bs_ym=%d and note='カプラ期末材料仕掛品棚卸高'", $yyyymm);
if (getUniResult($query, $c_endinv) < 1) {
    $c_endinv = 0;                          // 検索失敗
}
if ($yyyymm >= 201604) {
    $query = sprintf("select kin from profit_loss_pl_history where pl_bs_ym=%d and note='リニア標準期末材料仕掛品棚卸高'", $yyyymm);
    if (getUniResult($query, $l_endinv) < 1) {
        $l_endinv = 0;                          // 検索失敗
    }
} else {
    $query = sprintf("select kin from profit_loss_pl_history where pl_bs_ym=%d and note='リニア期末材料仕掛品棚卸高'", $yyyymm);
    if (getUniResult($query, $l_endinv) < 1) {
        $l_endinv = 0;                          // 検索失敗
    }
}
if ($yyyymm >= 201604) {
    $query = sprintf("select kin from profit_loss_pl_history where pl_bs_ym=%d and note='機工期末材料仕掛品棚卸高'", $yyyymm);
    if (getUniResult($query, $t_endinv) < 1) {
        $t_endinv = 0;                 // 検索失敗
    }
} else {
    $t_endinv = 0;                 // 検索失敗
}
$query = sprintf("select kin from profit_loss_pl_history where pl_bs_ym=%d and note='商品管理期末材料仕掛品棚卸高'", $yyyymm);
if (getUniResult($query, $b_endinv) < 1) {
    $b_endinv = 0;                          // 検索失敗
}
$query = sprintf("select kin from profit_loss_pl_history where pl_bs_ym=%d and note='試験修理期末材料仕掛品棚卸高'", $yyyymm);
if (getUniResult($query, $s_endinv) < 1) {
    $s_endinv = 0;                          // 検索失敗
}
    ///// 前期
$query = sprintf("select kin from profit_loss_pl_history where pl_bs_ym=%d and note='全体期末材料仕掛品棚卸高'", $p1_ym);
if (getUniResult($query, $p1_all_endinv) < 1) {
    $p1_all_endinv = 0;                        // 検索失敗
}
$query = sprintf("select kin from profit_loss_pl_history where pl_bs_ym=%d and note='カプラ期末材料仕掛品棚卸高'", $p1_ym);
if (getUniResult($query, $p1_c_endinv) < 1) {
    $p1_c_endinv = 0;                          // 検索失敗
}
if ($p1_ym >= 201603) {
    $query = sprintf("select kin from profit_loss_pl_history where pl_bs_ym=%d and note='リニア標準期末材料仕掛品棚卸高'", $p1_ym);
    if (getUniResult($query, $p1_l_endinv) < 1) {
        $p1_l_endinv = 0;                          // 検索失敗
    }
} else {
    $query = sprintf("select kin from profit_loss_pl_history where pl_bs_ym=%d and note='リニア期末材料仕掛品棚卸高'", $p1_ym);
    if (getUniResult($query, $p1_l_endinv) < 1) {
        $p1_l_endinv = 0;                          // 検索失敗
    }
}
if ($p1_ym >= 201603) {
    $query = sprintf("select kin from profit_loss_pl_history where pl_bs_ym=%d and note='機工期末材料仕掛品棚卸高'", $p1_ym);
    if (getUniResult($query, $p1_t_endinv) < 1) {
        $p1_t_endinv = 0;                 // 検索失敗
    }
} else {
    $p1_t_endinv = 0;                 // 検索失敗
}
$query = sprintf("select kin from profit_loss_pl_history where pl_bs_ym=%d and note='商品管理期末材料仕掛品棚卸高'", $p1_ym);
if (getUniResult($query, $p1_b_endinv) < 1) {
    $p1_b_endinv = 0;                          // 検索失敗
}
$query = sprintf("select kin from profit_loss_pl_history where pl_bs_ym=%d and note='試験修理期末材料仕掛品棚卸高'", $p1_ym);
if (getUniResult($query, $p1_s_endinv) < 1) {
    $p1_s_endinv = 0;                          // 検索失敗
}

/********** 売上原価 **********/
    ///// 当期
$query = sprintf("select kin from profit_loss_pl_history where pl_bs_ym=%d and note='カプラ売上原価'", $yyyymm);
if (getUniResult($query, $c_urigen) < 1) {
    $c_urigen = 0;                 // 検索失敗
}
if ($yyyymm >= 201604) {
    $query = sprintf("select kin from profit_loss_pl_history where pl_bs_ym=%d and note='リニア標準売上原価'", $yyyymm);
    if (getUniResult($query, $l_urigen) < 1) {
        $l_urigen = 0;                 // 検索失敗
    }
} else {
    $query = sprintf("select kin from profit_loss_pl_history where pl_bs_ym=%d and note='リニア売上原価'", $yyyymm);
    if (getUniResult($query, $l_urigen) < 1) {
        $l_urigen = 0;                 // 検索失敗
    }
}
if ($yyyymm >= 201604) {
    $query = sprintf("select kin from profit_loss_pl_history where pl_bs_ym=%d and note='機工売上原価'", $yyyymm);
    if (getUniResult($query, $t_urigen) < 1) {
        $t_urigen = 0;                 // 検索失敗
    }
} else {
    $t_urigen = 0;                 // 検索失敗
}
$query = sprintf("select kin from profit_loss_pl_history where pl_bs_ym=%d and note='試験修理売上原価'", $yyyymm);
if (getUniResult($query, $s_urigen) < 1) {
    $s_urigen = 0;                 // 検索失敗
}
$query = sprintf("select kin from profit_loss_pl_history where pl_bs_ym=%d and note='商品管理売上原価'", $yyyymm);
if (getUniResult($query, $b_urigen) < 1) {
    $b_urigen = 0;                 // 検索失敗
}
$query = sprintf("select kin from profit_loss_pl_history where pl_bs_ym=%d and note='全体売上原価'", $yyyymm);
if (getUniResult($query, $all_urigen) < 1) {
    $all_urigen = 0;                 // 検索失敗
}
    ///// 前期
$query = sprintf("select kin from profit_loss_pl_history where pl_bs_ym=%d and note='カプラ売上原価'", $p1_ym);
if (getUniResult($query, $p1_c_urigen) < 1) {
    $p1_c_urigen = 0;                      // 検索失敗
}
if ($p1_ym >= 201603) {
    $query = sprintf("select kin from profit_loss_pl_history where pl_bs_ym=%d and note='リニア標準売上原価'", $p1_ym);
    if (getUniResult($query, $p1_l_urigen) < 1) {
        $p1_l_urigen = 0;                      // 検索失敗
    }
} else {
    $query = sprintf("select kin from profit_loss_pl_history where pl_bs_ym=%d and note='リニア売上原価'", $p1_ym);
    if (getUniResult($query, $p1_l_urigen) < 1) {
        $p1_l_urigen = 0;                      // 検索失敗
    }
}
if ($p1_ym >= 201603) {
    $query = sprintf("select kin from profit_loss_pl_history where pl_bs_ym=%d and note='機工売上原価'", $p1_ym);
    if (getUniResult($query, $p1_t_urigen) < 1) {
        $p1_t_urigen = 0;                 // 検索失敗
    }
} else {
    $p1_t_urigen = 0;                 // 検索失敗
}
$query = sprintf("select kin from profit_loss_pl_history where pl_bs_ym=%d and note='試験修理売上原価'", $p1_ym);
if (getUniResult($query, $p1_s_urigen) < 1) {
    $p1_s_urigen = 0;                      // 検索失敗
}
$query = sprintf("select kin from profit_loss_pl_history where pl_bs_ym=%d and note='商品管理売上原価'", $p1_ym);
if (getUniResult($query, $p1_b_urigen) < 1) {
    $p1_b_urigen = 0;                      // 検索失敗
}
$query = sprintf("select kin from profit_loss_pl_history where pl_bs_ym=%d and note='全体売上原価'", $p1_ym);
if (getUniResult($query, $p1_all_urigen) < 1) {
    $p1_all_urigen = 0;                      // 検索失敗
}

/********** 売上総利益 **********/
    ///// 当期
$query = sprintf("select kin from profit_loss_pl_history where pl_bs_ym=%d and note='カプラ売上総利益'", $yyyymm);
if (getUniResult($query, $c_gross_profit) < 1) {
    $c_gross_profit = 0;                 // 検索失敗
}
if ($yyyymm >= 201604) {
    $query = sprintf("select kin from profit_loss_pl_history where pl_bs_ym=%d and note='リニア標準売上総利益'", $yyyymm);
    if (getUniResult($query, $l_gross_profit) < 1) {
        $l_gross_profit = 0;                 // 検索失敗
    }
} else {
    $query = sprintf("select kin from profit_loss_pl_history where pl_bs_ym=%d and note='リニア売上総利益'", $yyyymm);
    if (getUniResult($query, $l_gross_profit) < 1) {
        $l_gross_profit = 0;                 // 検索失敗
    }
}
if ($yyyymm >= 201604) {
    $query = sprintf("select kin from profit_loss_pl_history where pl_bs_ym=%d and note='機工売上総利益'", $yyyymm);
    if (getUniResult($query, $t_gross_profit) < 1) {
        $t_gross_profit = 0;                 // 検索失敗
    }
} else {
    $t_gross_profit = 0;                 // 検索失敗
}
$query = sprintf("select kin from profit_loss_pl_history where pl_bs_ym=%d and note='試験修理売上総利益'", $yyyymm);
if (getUniResult($query, $s_gross_profit) < 1) {
    $s_gross_profit = 0;                 // 検索失敗
}
$query = sprintf("select kin from profit_loss_pl_history where pl_bs_ym=%d and note='商品管理売上総利益'", $yyyymm);
if (getUniResult($query, $b_gross_profit) < 1) {
    $b_gross_profit = 0;                 // 検索失敗
}
$query = sprintf("select kin from profit_loss_pl_history where pl_bs_ym=%d and note='全体売上総利益'", $yyyymm);
if (getUniResult($query, $all_gross_profit) < 1) {
    $all_gross_profit = 0;                 // 検索失敗
}
    ///// 前期
$query = sprintf("select kin from profit_loss_pl_history where pl_bs_ym=%d and note='カプラ売上総利益'", $p1_ym);
if (getUniResult($query, $p1_c_gross_profit) < 1) {
    $p1_c_gross_profit = 0;                      // 検索失敗
}
if ($p1_ym >= 201603) {
    $query = sprintf("select kin from profit_loss_pl_history where pl_bs_ym=%d and note='リニア標準売上総利益'", $p1_ym);
    if (getUniResult($query, $p1_l_gross_profit) < 1) {
        $p1_l_gross_profit = 0;                      // 検索失敗
    }
} else {
    $query = sprintf("select kin from profit_loss_pl_history where pl_bs_ym=%d and note='リニア売上総利益'", $p1_ym);
    if (getUniResult($query, $p1_l_gross_profit) < 1) {
        $p1_l_gross_profit = 0;                      // 検索失敗
    }
}
if ($p1_ym >= 201603) {
    $query = sprintf("select kin from profit_loss_pl_history where pl_bs_ym=%d and note='機工売上総利益'", $p1_ym);
    if (getUniResult($query, $p1_t_gross_profit) < 1) {
        $p1_t_gross_profit = 0;                 // 検索失敗
    }
} else {
    $p1_t_gross_profit = 0;                 // 検索失敗
}
$query = sprintf("select kin from profit_loss_pl_history where pl_bs_ym=%d and note='試験修理売上総利益'", $p1_ym);
if (getUniResult($query, $p1_s_gross_profit) < 1) {
    $p1_s_gross_profit = 0;                      // 検索失敗
}
$query = sprintf("select kin from profit_loss_pl_history where pl_bs_ym=%d and note='商品管理売上総利益'", $p1_ym);
if (getUniResult($query, $p1_b_gross_profit) < 1) {
    $p1_b_gross_profit = 0;                      // 検索失敗
}
$query = sprintf("select kin from profit_loss_pl_history where pl_bs_ym=%d and note='全体売上総利益'", $p1_ym);
if (getUniResult($query, $p1_all_gross_profit) < 1) {
    $p1_all_gross_profit = 0;                      // 検索失敗
}

/********** 販管費の人件費 **********/
    ///// 当期
$query = sprintf("select kin from profit_loss_pl_history where pl_bs_ym=%d and note='カプラ人件費'", $yyyymm);
if (getUniResult($query, $c_han_jin) < 1) {
    $c_han_jin = 0;                 // 検索失敗
}
if ($yyyymm >= 201604) {
    $query = sprintf("select kin from profit_loss_pl_history where pl_bs_ym=%d and note='リニア標準人件費'", $yyyymm);
    if (getUniResult($query, $l_han_jin) < 1) {
        $l_han_jin = 0;                 // 検索失敗
    }
} else {
    $query = sprintf("select kin from profit_loss_pl_history where pl_bs_ym=%d and note='リニア人件費'", $yyyymm);
    if (getUniResult($query, $l_han_jin) < 1) {
        $l_han_jin = 0;                 // 検索失敗
    }
}
if ($yyyymm >= 201604) {
    $query = sprintf("select kin from profit_loss_pl_history where pl_bs_ym=%d and note='機工人件費'", $yyyymm);
    if (getUniResult($query, $t_han_jin) < 1) {
        $t_han_jin = 0;                 // 検索失敗
    }
} else {
    $t_han_jin = 0;                 // 検索失敗
}
$query = sprintf("select kin from profit_loss_pl_history where pl_bs_ym=%d and note='試験修理人件費'", $yyyymm);
if (getUniResult($query, $s_han_jin) < 1) {
    $s_han_jin = 0;                 // 検索失敗
}
$query = sprintf("select kin from profit_loss_pl_history where pl_bs_ym=%d and note='商品管理人件費'", $yyyymm);
if (getUniResult($query, $b_han_jin) < 1) {
    $b_han_jin = 0;                 // 検索失敗
}
$query = sprintf("select kin from profit_loss_pl_history where pl_bs_ym=%d and note='全体人件費'", $yyyymm);
if (getUniResult($query, $all_han_jin) < 1) {
    $all_han_jin = 0;                 // 検索失敗
}
    ///// 前期
$query = sprintf("select kin from profit_loss_pl_history where pl_bs_ym=%d and note='カプラ人件費'", $p1_ym);
if (getUniResult($query, $p1_c_han_jin) < 1) {
    $p1_c_han_jin = 0;                      // 検索失敗
}
if ($p1_ym >= 201603) {
    $query = sprintf("select kin from profit_loss_pl_history where pl_bs_ym=%d and note='リニア標準人件費'", $p1_ym);
    if (getUniResult($query, $p1_l_han_jin) < 1) {
        $p1_l_han_jin = 0;                      // 検索失敗
    }
} else {
    $query = sprintf("select kin from profit_loss_pl_history where pl_bs_ym=%d and note='リニア人件費'", $p1_ym);
    if (getUniResult($query, $p1_l_han_jin) < 1) {
        $p1_l_han_jin = 0;                      // 検索失敗
    }
}
if ($p1_ym >= 201603) {
    $query = sprintf("select kin from profit_loss_pl_history where pl_bs_ym=%d and note='機工人件費'", $p1_ym);
    if (getUniResult($query, $p1_t_han_jin) < 1) {
        $p1_t_han_jin = 0;                 // 検索失敗
    }
} else {
    $p1_t_han_jin = 0;                 // 検索失敗
}
$query = sprintf("select kin from profit_loss_pl_history where pl_bs_ym=%d and note='試験修理人件費'", $p1_ym);
if (getUniResult($query, $p1_s_han_jin) < 1) {
    $p1_s_han_jin = 0;                      // 検索失敗
}
$query = sprintf("select kin from profit_loss_pl_history where pl_bs_ym=%d and note='商品管理人件費'", $p1_ym);
if (getUniResult($query, $p1_b_han_jin) < 1) {
    $p1_b_han_jin = 0;                      // 検索失敗
}
$query = sprintf("select kin from profit_loss_pl_history where pl_bs_ym=%d and note='全体人件費'", $p1_ym);
if (getUniResult($query, $p1_all_han_jin) < 1) {
    $p1_all_han_jin = 0;                      // 検索失敗
}

/********** 販管費の経費 **********/
    ///// 当期
$query = sprintf("select kin from profit_loss_pl_history where pl_bs_ym=%d and note='カプラ経費'", $yyyymm);
if (getUniResult($query, $c_han_kei) < 1) {
    $c_han_kei = 0;                 // 検索失敗
}
if ($yyyymm >= 201604) {
    $query = sprintf("select kin from profit_loss_pl_history where pl_bs_ym=%d and note='リニア標準経費'", $yyyymm);
    if (getUniResult($query, $l_han_kei) < 1) {
        $l_han_kei = 0;                 // 検索失敗
    }
} else {
    $query = sprintf("select kin from profit_loss_pl_history where pl_bs_ym=%d and note='リニア経費'", $yyyymm);
    if (getUniResult($query, $l_han_kei) < 1) {
        $l_han_kei = 0;                 // 検索失敗
    }
}
if ($yyyymm >= 201604) {
    $query = sprintf("select kin from profit_loss_pl_history where pl_bs_ym=%d and note='機工経費'", $yyyymm);
    if (getUniResult($query, $t_han_kei) < 1) {
        $t_han_kei = 0;                 // 検索失敗
    }
} else {
    $t_han_kei = 0;                 // 検索失敗
}
$query = sprintf("select kin from profit_loss_pl_history where pl_bs_ym=%d and note='試験修理経費'", $yyyymm);
if (getUniResult($query, $s_han_kei) < 1) {
    $s_han_kei = 0;                 // 検索失敗
}
$query = sprintf("select kin from profit_loss_pl_history where pl_bs_ym=%d and note='商品管理経費'", $yyyymm);
if (getUniResult($query, $b_han_kei) < 1) {
    $b_han_kei = 0;                 // 検索失敗
}
$query = sprintf("select kin from profit_loss_pl_history where pl_bs_ym=%d and note='全体経費'", $yyyymm);
if (getUniResult($query, $all_han_kei) < 1) {
    $all_han_kei = 0;                 // 検索失敗
}
    ///// 前期
$query = sprintf("select kin from profit_loss_pl_history where pl_bs_ym=%d and note='カプラ経費'", $p1_ym);
if (getUniResult($query, $p1_c_han_kei) < 1) {
    $p1_c_han_kei = 0;                      // 検索失敗
}
if ($p1_ym >= 201603) {
    $query = sprintf("select kin from profit_loss_pl_history where pl_bs_ym=%d and note='リニア標準経費'", $p1_ym);
    if (getUniResult($query, $p1_l_han_kei) < 1) {
        $p1_l_han_kei = 0;                      // 検索失敗
    }
} else {
    $query = sprintf("select kin from profit_loss_pl_history where pl_bs_ym=%d and note='リニア経費'", $p1_ym);
    if (getUniResult($query, $p1_l_han_kei) < 1) {
        $p1_l_han_kei = 0;                      // 検索失敗
    }
}
if ($p1_ym >= 201603) {
    $query = sprintf("select kin from profit_loss_pl_history where pl_bs_ym=%d and note='機工経費'", $p1_ym);
    if (getUniResult($query, $p1_t_han_kei) < 1) {
        $p1_t_han_kei = 0;                 // 検索失敗
    }
} else {
    $p1_t_han_kei = 0;                 // 検索失敗
}
$query = sprintf("select kin from profit_loss_pl_history where pl_bs_ym=%d and note='試験修理経費'", $p1_ym);
if (getUniResult($query, $p1_s_han_kei) < 1) {
    $p1_s_han_kei = 0;                      // 検索失敗
}
$query = sprintf("select kin from profit_loss_pl_history where pl_bs_ym=%d and note='商品管理経費'", $p1_ym);
if (getUniResult($query, $p1_b_han_kei) < 1) {
    $p1_b_han_kei = 0;                      // 検索失敗
}
$query = sprintf("select kin from profit_loss_pl_history where pl_bs_ym=%d and note='全体経費'", $p1_ym);
if (getUniResult($query, $p1_all_han_kei) < 1) {
    $p1_all_han_kei = 0;                      // 検索失敗
}

/********** 販管費の合計 **********/
    ///// 当期
$query = sprintf("select kin from profit_loss_pl_history where pl_bs_ym=%d and note='カプラ販管費及び一般管理費計'", $yyyymm);
if (getUniResult($query, $c_han_all) < 1) {
    $c_han_all = 0;                 // 検索失敗
}
if ($yyyymm >= 201604) {
    $query = sprintf("select kin from profit_loss_pl_history where pl_bs_ym=%d and note='リニア標準販管費及び一般管理費計'", $yyyymm);
    if (getUniResult($query, $l_han_all) < 1) {
        $l_han_all = 0;                 // 検索失敗
    }
} else {
    $query = sprintf("select kin from profit_loss_pl_history where pl_bs_ym=%d and note='リニア販管費及び一般管理費計'", $yyyymm);
    if (getUniResult($query, $l_han_all) < 1) {
        $l_han_all = 0;                 // 検索失敗
    }
}
if ($yyyymm >= 201604) {
    $query = sprintf("select kin from profit_loss_pl_history where pl_bs_ym=%d and note='機工販管費及び一般管理費計'", $yyyymm);
    if (getUniResult($query, $t_han_all) < 1) {
        $t_han_all = 0;                 // 検索失敗
    }
} else {
    $t_han_all = 0;                 // 検索失敗
}
$query = sprintf("select kin from profit_loss_pl_history where pl_bs_ym=%d and note='試験修理販管費及び一般管理費計'", $yyyymm);
if (getUniResult($query, $s_han_all) < 1) {
    $s_han_all = 0;                 // 検索失敗
}
$query = sprintf("select kin from profit_loss_pl_history where pl_bs_ym=%d and note='商品管理販管費及び一般管理費計'", $yyyymm);
if (getUniResult($query, $b_han_all) < 1) {
    $b_han_all = 0;                 // 検索失敗
}
$query = sprintf("select kin from profit_loss_pl_history where pl_bs_ym=%d and note='全体販管費及び一般管理費計'", $yyyymm);
if (getUniResult($query, $all_han_all) < 1) {
    $all_han_all = 0;                 // 検索失敗
}
    ///// 前期
$query = sprintf("select kin from profit_loss_pl_history where pl_bs_ym=%d and note='カプラ販管費及び一般管理費計'", $p1_ym);
if (getUniResult($query, $p1_c_han_all) < 1) {
    $p1_c_han_all = 0;                      // 検索失敗
}
if ($p1_ym >= 201603) {
    $query = sprintf("select kin from profit_loss_pl_history where pl_bs_ym=%d and note='リニア標準販管費及び一般管理費計'", $p1_ym);
    if (getUniResult($query, $p1_l_han_all) < 1) {
        $p1_l_han_all = 0;                      // 検索失敗
    }
} else {
    $query = sprintf("select kin from profit_loss_pl_history where pl_bs_ym=%d and note='リニア販管費及び一般管理費計'", $p1_ym);
    if (getUniResult($query, $p1_l_han_all) < 1) {
        $p1_l_han_all = 0;                      // 検索失敗
    }
}
if ($p1_ym >= 201603) {
    $query = sprintf("select kin from profit_loss_pl_history where pl_bs_ym=%d and note='機工販管費及び一般管理費計'", $p1_ym);
    if (getUniResult($query, $p1_t_han_all) < 1) {
        $p1_t_han_all = 0;                 // 検索失敗
    }
} else {
    $p1_t_han_all = 0;                 // 検索失敗
}
$query = sprintf("select kin from profit_loss_pl_history where pl_bs_ym=%d and note='試験修理販管費及び一般管理費計'", $p1_ym);
if (getUniResult($query, $p1_s_han_all) < 1) {
    $p1_s_han_all = 0;                      // 検索失敗
}
$query = sprintf("select kin from profit_loss_pl_history where pl_bs_ym=%d and note='商品管理販管費及び一般管理費計'", $p1_ym);
if (getUniResult($query, $p1_b_han_all) < 1) {
    $p1_b_han_all = 0;                      // 検索失敗
}
$query = sprintf("select kin from profit_loss_pl_history where pl_bs_ym=%d and note='全体販管費及び一般管理費計'", $p1_ym);
if (getUniResult($query, $p1_all_han_all) < 1) {
    $p1_all_han_all = 0;                      // 検索失敗
}

/********** 営業利益 **********/
    ///// 当期
$query = sprintf("select kin from profit_loss_pl_history where pl_bs_ym=%d and note='カプラ営業利益'", $yyyymm);
if (getUniResult($query, $c_ope_profit) < 1) {
    $c_ope_profit = 0;                 // 検索失敗
}
if ($yyyymm >= 201604) {
    $query = sprintf("select kin from profit_loss_pl_history where pl_bs_ym=%d and note='リニア標準営業利益'", $yyyymm);
    if (getUniResult($query, $l_ope_profit) < 1) {
        $l_ope_profit = 0;                 // 検索失敗
    }
} else {
    $query = sprintf("select kin from profit_loss_pl_history where pl_bs_ym=%d and note='リニア営業利益'", $yyyymm);
    if (getUniResult($query, $l_ope_profit) < 1) {
        $l_ope_profit = 0;                 // 検索失敗
    }
}
if ($yyyymm >= 201604) {
    $query = sprintf("select kin from profit_loss_pl_history where pl_bs_ym=%d and note='機工営業利益'", $yyyymm);
    if (getUniResult($query, $t_ope_profit) < 1) {
        $t_ope_profit = 0;                 // 検索失敗
    }
} else {
    $t_ope_profit = 0;                 // 検索失敗
}
$query = sprintf("select kin from profit_loss_pl_history where pl_bs_ym=%d and note='試験修理営業利益'", $yyyymm);
if (getUniResult($query, $s_ope_profit) < 1) {
    $s_ope_profit = 0;                 // 検索失敗
}
$query = sprintf("select kin from profit_loss_pl_history where pl_bs_ym=%d and note='商品管理営業利益'", $yyyymm);
if (getUniResult($query, $b_ope_profit) < 1) {
    $b_ope_profit = 0;                 // 検索失敗
}
$query = sprintf("select kin from profit_loss_pl_history where pl_bs_ym=%d and note='全体営業利益'", $yyyymm);
if (getUniResult($query, $all_ope_profit) < 1) {
    $all_ope_profit = 0;                 // 検索失敗
}
    ///// 前期
$query = sprintf("select kin from profit_loss_pl_history where pl_bs_ym=%d and note='カプラ営業利益'", $p1_ym);
if (getUniResult($query, $p1_c_ope_profit) < 1) {
    $p1_c_ope_profit = 0;                      // 検索失敗
}
if ($p1_ym >= 201603) {
    $query = sprintf("select kin from profit_loss_pl_history where pl_bs_ym=%d and note='リニア標準営業利益'", $p1_ym);
    if (getUniResult($query, $p1_l_ope_profit) < 1) {
        $p1_l_ope_profit = 0;                      // 検索失敗
    }
} else {
    $query = sprintf("select kin from profit_loss_pl_history where pl_bs_ym=%d and note='リニア営業利益'", $p1_ym);
    if (getUniResult($query, $p1_l_ope_profit) < 1) {
        $p1_l_ope_profit = 0;                      // 検索失敗
    }
}
if ($p1_ym >= 201603) {
    $query = sprintf("select kin from profit_loss_pl_history where pl_bs_ym=%d and note='機工営業利益'", $p1_ym);
    if (getUniResult($query, $p1_t_ope_profit) < 1) {
        $p1_t_ope_profit = 0;                 // 検索失敗
    }
} else {
    $p1_t_ope_profit = 0;                 // 検索失敗
}
$query = sprintf("select kin from profit_loss_pl_history where pl_bs_ym=%d and note='試験修理営業利益'", $p1_ym);
if (getUniResult($query, $p1_s_ope_profit) < 1) {
    $p1_s_ope_profit = 0;                      // 検索失敗
}
$query = sprintf("select kin from profit_loss_pl_history where pl_bs_ym=%d and note='商品管理営業利益'", $p1_ym);
if (getUniResult($query, $p1_b_ope_profit) < 1) {
    $p1_b_ope_profit = 0;                      // 検索失敗
}
$query = sprintf("select kin from profit_loss_pl_history where pl_bs_ym=%d and note='全体営業利益'", $p1_ym);
if (getUniResult($query, $p1_all_ope_profit) < 1) {
    $p1_all_ope_profit = 0;                      // 検索失敗
}

/********** 営業外収益の業務委託収入 **********/
    ///// 当期
$query = sprintf("select kin from profit_loss_pl_history where pl_bs_ym=%d and note='カプラ業務委託収入'", $yyyymm);
if (getUniResult($query, $c_gyoumu) < 1) {
    $c_gyoumu = 0;                 // 検索失敗
}
if ($yyyymm >= 201604) {
    $query = sprintf("select kin from profit_loss_pl_history where pl_bs_ym=%d and note='リニア標準業務委託収入'", $yyyymm);
    if (getUniResult($query, $l_gyoumu) < 1) {
        $l_gyoumu = 0;                 // 検索失敗
    }
} else {
    $query = sprintf("select kin from profit_loss_pl_history where pl_bs_ym=%d and note='リニア業務委託収入'", $yyyymm);
    if (getUniResult($query, $l_gyoumu) < 1) {
        $l_gyoumu = 0;                 // 検索失敗
    }
}
if ($yyyymm >= 201604) {
    $query = sprintf("select kin from profit_loss_pl_history where pl_bs_ym=%d and note='機工業務委託収入'", $yyyymm);
    if (getUniResult($query, $t_gyoumu) < 1) {
        $t_gyoumu = 0;                 // 検索失敗
    }
} else {
    $t_gyoumu = 0;                 // 検索失敗
}
$query = sprintf("select kin from profit_loss_pl_history where pl_bs_ym=%d and note='試験修理業務委託収入'", $yyyymm);
if (getUniResult($query, $s_gyoumu) < 1) {
    $s_gyoumu = 0;                 // 検索失敗
}
$query = sprintf("select kin from profit_loss_pl_history where pl_bs_ym=%d and note='商品管理業務委託収入'", $yyyymm);
if (getUniResult($query, $b_gyoumu) < 1) {
    $b_gyoumu = 0;                 // 検索失敗
}
$query = sprintf("select kin from profit_loss_pl_history where pl_bs_ym=%d and note='全体業務委託収入'", $yyyymm);
if (getUniResult($query, $all_gyoumu) < 1) {
    $all_gyoumu = 0;                 // 検索失敗
}
    ///// 前期
$query = sprintf("select kin from profit_loss_pl_history where pl_bs_ym=%d and note='カプラ業務委託収入'", $p1_ym);
if (getUniResult($query, $p1_c_gyoumu) < 1) {
    $p1_c_gyoumu = 0;                      // 検索失敗
}
if ($p1_ym >= 201603) {
    $query = sprintf("select kin from profit_loss_pl_history where pl_bs_ym=%d and note='リニア標準業務委託収入'", $p1_ym);
    if (getUniResult($query, $p1_l_gyoumu) < 1) {
        $p1_l_gyoumu = 0;                      // 検索失敗
    }
} else {
    $query = sprintf("select kin from profit_loss_pl_history where pl_bs_ym=%d and note='リニア業務委託収入'", $p1_ym);
    if (getUniResult($query, $p1_l_gyoumu) < 1) {
        $p1_l_gyoumu = 0;                      // 検索失敗
    }
}
if ($p1_ym >= 201603) {
    $query = sprintf("select kin from profit_loss_pl_history where pl_bs_ym=%d and note='機工業務委託収入'", $p1_ym);
    if (getUniResult($query, $p1_t_gyoumu) < 1) {
        $p1_t_gyoumu = 0;                 // 検索失敗
    }
} else {
    $p1_t_gyoumu = 0;                 // 検索失敗
}
$query = sprintf("select kin from profit_loss_pl_history where pl_bs_ym=%d and note='試験修理業務委託収入'", $p1_ym);
if (getUniResult($query, $p1_s_gyoumu) < 1) {
    $p1_s_gyoumu = 0;                      // 検索失敗
}
$query = sprintf("select kin from profit_loss_pl_history where pl_bs_ym=%d and note='商品管理業務委託収入'", $p1_ym);
if (getUniResult($query, $p1_b_gyoumu) < 1) {
    $p1_b_gyoumu = 0;                      // 検索失敗
}
$query = sprintf("select kin from profit_loss_pl_history where pl_bs_ym=%d and note='全体業務委託収入'", $p1_ym);
if (getUniResult($query, $p1_all_gyoumu) < 1) {
    $p1_all_gyoumu = 0;                      // 検索失敗
}

/********** 営業外収益の仕入割引 **********/
    ///// 当期
$query = sprintf("select kin from profit_loss_pl_history where pl_bs_ym=%d and note='カプラ仕入割引'", $yyyymm);
if (getUniResult($query, $c_swari) < 1) {
    $c_swari = 0;                 // 検索失敗
}
if ($yyyymm >= 201604) {
    $query = sprintf("select kin from profit_loss_pl_history where pl_bs_ym=%d and note='リニア標準仕入割引'", $yyyymm);
    if (getUniResult($query, $l_swari) < 1) {
        $l_swari = 0;                 // 検索失敗
    }
} else {
    $query = sprintf("select kin from profit_loss_pl_history where pl_bs_ym=%d and note='リニア仕入割引'", $yyyymm);
    if (getUniResult($query, $l_swari) < 1) {
        $l_swari = 0;                 // 検索失敗
    }
}
if ($yyyymm >= 201604) {
    $query = sprintf("select kin from profit_loss_pl_history where pl_bs_ym=%d and note='機工仕入割引'", $yyyymm);
    if (getUniResult($query, $t_swari) < 1) {
        $t_swari = 0;                 // 検索失敗
    }
} else {
    $t_swari = 0;                 // 検索失敗
}
$query = sprintf("select kin from profit_loss_pl_history where pl_bs_ym=%d and note='試験修理仕入割引'", $yyyymm);
if (getUniResult($query, $s_swari) < 1) {
    $s_swari = 0;                 // 検索失敗
}
$query = sprintf("select kin from profit_loss_pl_history where pl_bs_ym=%d and note='商品管理仕入割引'", $yyyymm);
if (getUniResult($query, $b_swari) < 1) {
    $b_swari = 0;                 // 検索失敗
}
$query = sprintf("select kin from profit_loss_pl_history where pl_bs_ym=%d and note='全体仕入割引'", $yyyymm);
if (getUniResult($query, $all_swari) < 1) {
    $all_swari = 0;                 // 検索失敗
}
    ///// 前期
$query = sprintf("select kin from profit_loss_pl_history where pl_bs_ym=%d and note='カプラ仕入割引'", $p1_ym);
if (getUniResult($query, $p1_c_swari) < 1) {
    $p1_c_swari = 0;                      // 検索失敗
}
if ($p1_ym >= 201603) {
    $query = sprintf("select kin from profit_loss_pl_history where pl_bs_ym=%d and note='リニア標準仕入割引'", $p1_ym);
    if (getUniResult($query, $p1_l_swari) < 1) {
        $p1_l_swari = 0;                      // 検索失敗
    }
} else {
    $query = sprintf("select kin from profit_loss_pl_history where pl_bs_ym=%d and note='リニア仕入割引'", $p1_ym);
    if (getUniResult($query, $p1_l_swari) < 1) {
        $p1_l_swari = 0;                      // 検索失敗
    }
}
if ($p1_ym >= 201603) {
    $query = sprintf("select kin from profit_loss_pl_history where pl_bs_ym=%d and note='機工仕入割引'", $p1_ym);
    if (getUniResult($query, $p1_t_swari) < 1) {
        $p1_t_swari = 0;                 // 検索失敗
    }
} else {
    $p1_t_swari = 0;                 // 検索失敗
}
$query = sprintf("select kin from profit_loss_pl_history where pl_bs_ym=%d and note='試験修理仕入割引'", $p1_ym);
if (getUniResult($query, $p1_s_swari) < 1) {
    $p1_s_swari = 0;                      // 検索失敗
}
$query = sprintf("select kin from profit_loss_pl_history where pl_bs_ym=%d and note='商品管理仕入割引'", $p1_ym);
if (getUniResult($query, $p1_b_swari) < 1) {
    $p1_b_swari = 0;                      // 検索失敗
}
$query = sprintf("select kin from profit_loss_pl_history where pl_bs_ym=%d and note='全体仕入割引'", $p1_ym);
if (getUniResult($query, $p1_all_swari) < 1) {
    $p1_all_swari = 0;                      // 検索失敗
}

/********** 営業外収益のその他 **********/
    ///// 当期
$query = sprintf("select kin from profit_loss_pl_history where pl_bs_ym=%d and note='カプラ営業外収益その他'", $yyyymm);
if (getUniResult($query, $c_pother) < 1) {
    $c_pother = 0;                 // 検索失敗
}
if ($yyyymm >= 201604) {
    $query = sprintf("select kin from profit_loss_pl_history where pl_bs_ym=%d and note='リニア標準営業外収益その他'", $yyyymm);
    if (getUniResult($query, $l_pother) < 1) {
        $l_pother = 0;                 // 検索失敗
    }
} else {
    $query = sprintf("select kin from profit_loss_pl_history where pl_bs_ym=%d and note='リニア営業外収益その他'", $yyyymm);
    if (getUniResult($query, $l_pother) < 1) {
        $l_pother = 0;                 // 検索失敗
    }
}
if ($yyyymm >= 201604) {
    $query = sprintf("select kin from profit_loss_pl_history where pl_bs_ym=%d and note='機工営業外収益その他'", $yyyymm);
    if (getUniResult($query, $t_pother) < 1) {
        $t_pother = 0;                 // 検索失敗
    }
} else {
    $t_pother = 0;                 // 検索失敗
}
$query = sprintf("select kin from profit_loss_pl_history where pl_bs_ym=%d and note='試験修理営業外収益その他'", $yyyymm);
if (getUniResult($query, $s_pother) < 1) {
    $s_pother = 0;                 // 検索失敗
}
$query = sprintf("select kin from profit_loss_pl_history where pl_bs_ym=%d and note='商品管理営業外収益その他'", $yyyymm);
if (getUniResult($query, $b_pother) < 1) {
    $b_pother = 0;                 // 検索失敗
}
$query = sprintf("select kin from profit_loss_pl_history where pl_bs_ym=%d and note='全体営業外収益その他'", $yyyymm);
if (getUniResult($query, $all_pother) < 1) {
    $all_pother = 0;                 // 検索失敗
}
    ///// 前期
$query = sprintf("select kin from profit_loss_pl_history where pl_bs_ym=%d and note='カプラ営業外収益その他'", $p1_ym);
if (getUniResult($query, $p1_c_pother) < 1) {
    $p1_c_pother = 0;                      // 検索失敗
}
if ($p1_ym >= 201603) {
    $query = sprintf("select kin from profit_loss_pl_history where pl_bs_ym=%d and note='リニア標準営業外収益その他'", $p1_ym);
    if (getUniResult($query, $p1_l_pother) < 1) {
        $p1_l_pother = 0;                      // 検索失敗
    }
} else {
    $query = sprintf("select kin from profit_loss_pl_history where pl_bs_ym=%d and note='リニア営業外収益その他'", $p1_ym);
    if (getUniResult($query, $p1_l_pother) < 1) {
        $p1_l_pother = 0;                      // 検索失敗
    }
}
if ($p1_ym >= 201603) {
    $query = sprintf("select kin from profit_loss_pl_history where pl_bs_ym=%d and note='機工営業外収益その他'", $p1_ym);
    if (getUniResult($query, $p1_t_pother) < 1) {
        $p1_t_pother = 0;                 // 検索失敗
    }
} else {
    $p1_t_pother = 0;                 // 検索失敗
}
$query = sprintf("select kin from profit_loss_pl_history where pl_bs_ym=%d and note='試験修理営業外収益その他'", $p1_ym);
if (getUniResult($query, $p1_s_pother) < 1) {
    $p1_s_pother = 0;                      // 検索失敗
}
$query = sprintf("select kin from profit_loss_pl_history where pl_bs_ym=%d and note='商品管理営業外収益その他'", $p1_ym);
if (getUniResult($query, $p1_b_pother) < 1) {
    $p1_b_pother = 0;                      // 検索失敗
}
$query = sprintf("select kin from profit_loss_pl_history where pl_bs_ym=%d and note='全体営業外収益その他'", $p1_ym);
if (getUniResult($query, $p1_all_pother) < 1) {
    $p1_all_pother = 0;                      // 検索失敗
}

/********** 営業外収益の合計 **********/
    ///// 当期
$query = sprintf("select kin from profit_loss_pl_history where pl_bs_ym=%d and note='カプラ営業外収益計'", $yyyymm);
if (getUniResult($query, $c_nonope_profit_sum) < 1) {
    $c_nonope_profit_sum = 0;                 // 検索失敗
}
if ($yyyymm >= 201604) {
    $query = sprintf("select kin from profit_loss_pl_history where pl_bs_ym=%d and note='リニア標準営業外収益計'", $yyyymm);
    if (getUniResult($query, $l_nonope_profit_sum) < 1) {
        $l_nonope_profit_sum = 0;                 // 検索失敗
    }
} else {
    $query = sprintf("select kin from profit_loss_pl_history where pl_bs_ym=%d and note='リニア営業外収益計'", $yyyymm);
    if (getUniResult($query, $l_nonope_profit_sum) < 1) {
        $l_nonope_profit_sum = 0;                 // 検索失敗
    }
}
if ($yyyymm >= 201604) {
    $query = sprintf("select kin from profit_loss_pl_history where pl_bs_ym=%d and note='機工営業外収益計'", $yyyymm);
    if (getUniResult($query, $t_nonope_profit_sum) < 1) {
        $t_nonope_profit_sum = 0;                 // 検索失敗
    }
} else {
    $t_nonope_profit_sum = 0;                 // 検索失敗
}
$query = sprintf("select kin from profit_loss_pl_history where pl_bs_ym=%d and note='試験修理営業外収益計'", $yyyymm);
if (getUniResult($query, $s_nonope_profit_sum) < 1) {
    $s_nonope_profit_sum = 0;                 // 検索失敗
}
$query = sprintf("select kin from profit_loss_pl_history where pl_bs_ym=%d and note='商品管理営業外収益計'", $yyyymm);
if (getUniResult($query, $b_nonope_profit_sum) < 1) {
    $b_nonope_profit_sum = 0;                 // 検索失敗
}
$query = sprintf("select kin from profit_loss_pl_history where pl_bs_ym=%d and note='全体営業外収益計'", $yyyymm);
if (getUniResult($query, $all_nonope_profit_sum) < 1) {
    $all_nonope_profit_sum = 0;                 // 検索失敗
}
    ///// 前期
$query = sprintf("select kin from profit_loss_pl_history where pl_bs_ym=%d and note='カプラ営業外収益計'", $p1_ym);
if (getUniResult($query, $p1_c_nonope_profit_sum) < 1) {
    $p1_c_nonope_profit_sum = 0;                      // 検索失敗
}
if ($p1_ym >= 201603) {
    $query = sprintf("select kin from profit_loss_pl_history where pl_bs_ym=%d and note='リニア標準営業外収益計'", $p1_ym);
    if (getUniResult($query, $p1_l_nonope_profit_sum) < 1) {
        $p1_l_nonope_profit_sum = 0;                      // 検索失敗
    }
} else {
    $query = sprintf("select kin from profit_loss_pl_history where pl_bs_ym=%d and note='リニア営業外収益計'", $p1_ym);
    if (getUniResult($query, $p1_l_nonope_profit_sum) < 1) {
        $p1_l_nonope_profit_sum = 0;                      // 検索失敗
    }
}
if ($p1_ym >= 201603) {
    $query = sprintf("select kin from profit_loss_pl_history where pl_bs_ym=%d and note='機工営業外収益計'", $p1_ym);
    if (getUniResult($query, $p1_t_nonope_profit_sum) < 1) {
        $p1_t_nonope_profit_sum = 0;                 // 検索失敗
    }
} else {
    $p1_t_nonope_profit_sum = 0;                 // 検索失敗
}
$query = sprintf("select kin from profit_loss_pl_history where pl_bs_ym=%d and note='試験修理営業外収益計'", $p1_ym);
if (getUniResult($query, $p1_s_nonope_profit_sum) < 1) {
    $p1_s_nonope_profit_sum = 0;                      // 検索失敗
}
$query = sprintf("select kin from profit_loss_pl_history where pl_bs_ym=%d and note='商品管理営業外収益計'", $p1_ym);
if (getUniResult($query, $p1_b_nonope_profit_sum) < 1) {
    $p1_b_nonope_profit_sum = 0;                      // 検索失敗
}
$query = sprintf("select kin from profit_loss_pl_history where pl_bs_ym=%d and note='全体営業外収益計'", $p1_ym);
if (getUniResult($query, $p1_all_nonope_profit_sum) < 1) {
    $p1_all_nonope_profit_sum = 0;                      // 検索失敗
}

/********** 営業外費用の支払利息 **********/
    ///// 当期
$query = sprintf("select kin from profit_loss_pl_history where pl_bs_ym=%d and note='カプラ支払利息'", $yyyymm);
if (getUniResult($query, $c_srisoku) < 1) {
    $c_srisoku = 0;                 // 検索失敗
}
if ($yyyymm >= 201604) {
    $query = sprintf("select kin from profit_loss_pl_history where pl_bs_ym=%d and note='リニア標準支払利息'", $yyyymm);
    if (getUniResult($query, $l_srisoku) < 1) {
        $l_srisoku = 0;                 // 検索失敗
    }
} else {
    $query = sprintf("select kin from profit_loss_pl_history where pl_bs_ym=%d and note='リニア支払利息'", $yyyymm);
    if (getUniResult($query, $l_srisoku) < 1) {
        $l_srisoku = 0;                 // 検索失敗
    }
}
if ($yyyymm >= 201604) {
    $query = sprintf("select kin from profit_loss_pl_history where pl_bs_ym=%d and note='機工支払利息'", $yyyymm);
    if (getUniResult($query, $t_srisoku) < 1) {
        $t_srisoku = 0;                 // 検索失敗
    }
} else {
    $t_srisoku = 0;                 // 検索失敗
}
$query = sprintf("select kin from profit_loss_pl_history where pl_bs_ym=%d and note='試験修理支払利息'", $yyyymm);
if (getUniResult($query, $s_srisoku) < 1) {
    $s_srisoku = 0;                 // 検索失敗
}
$query = sprintf("select kin from profit_loss_pl_history where pl_bs_ym=%d and note='商品管理支払利息'", $yyyymm);
if (getUniResult($query, $b_srisoku) < 1) {
    $b_srisoku = 0;                 // 検索失敗
}
$query = sprintf("select kin from profit_loss_pl_history where pl_bs_ym=%d and note='全体支払利息'", $yyyymm);
if (getUniResult($query, $all_srisoku) < 1) {
    $all_srisoku = 0;                 // 検索失敗
}
    ///// 前期
$query = sprintf("select kin from profit_loss_pl_history where pl_bs_ym=%d and note='カプラ支払利息'", $p1_ym);
if (getUniResult($query, $p1_c_srisoku) < 1) {
    $p1_c_srisoku = 0;                      // 検索失敗
}
if ($p1_ym >= 201603) {
    $query = sprintf("select kin from profit_loss_pl_history where pl_bs_ym=%d and note='リニア標準支払利息'", $p1_ym);
    if (getUniResult($query, $p1_l_srisoku) < 1) {
        $p1_l_srisoku = 0;                      // 検索失敗
    }
} else {
    $query = sprintf("select kin from profit_loss_pl_history where pl_bs_ym=%d and note='リニア支払利息'", $p1_ym);
    if (getUniResult($query, $p1_l_srisoku) < 1) {
        $p1_l_srisoku = 0;                      // 検索失敗
    }
}
if ($p1_ym >= 201603) {
    $query = sprintf("select kin from profit_loss_pl_history where pl_bs_ym=%d and note='機工支払利息'", $p1_ym);
    if (getUniResult($query, $p1_t_srisoku) < 1) {
        $p1_t_srisoku = 0;                 // 検索失敗
    }
} else {
    $p1_t_srisoku = 0;                 // 検索失敗
}
$query = sprintf("select kin from profit_loss_pl_history where pl_bs_ym=%d and note='試験修理支払利息'", $p1_ym);
if (getUniResult($query, $p1_s_srisoku) < 1) {
    $p1_s_srisoku = 0;                      // 検索失敗
}
$query = sprintf("select kin from profit_loss_pl_history where pl_bs_ym=%d and note='商品管理支払利息'", $p1_ym);
if (getUniResult($query, $p1_b_srisoku) < 1) {
    $p1_b_srisoku = 0;                      // 検索失敗
}
$query = sprintf("select kin from profit_loss_pl_history where pl_bs_ym=%d and note='全体支払利息'", $p1_ym);
if (getUniResult($query, $p1_all_srisoku) < 1) {
    $p1_all_srisoku = 0;                      // 検索失敗
}

/********** 営業外費用のその他 **********/
    ///// 当期
$query = sprintf("select kin from profit_loss_pl_history where pl_bs_ym=%d and note='カプラ営業外費用その他'", $yyyymm);
if (getUniResult($query, $c_lother) < 1) {
    $c_lother = 0;                 // 検索失敗
}
if ($yyyymm >= 201604) {
    $query = sprintf("select kin from profit_loss_pl_history where pl_bs_ym=%d and note='リニア標準営業外費用その他'", $yyyymm);
    if (getUniResult($query, $l_lother) < 1) {
        $l_lother = 0;                 // 検索失敗
    }
} else {
    $query = sprintf("select kin from profit_loss_pl_history where pl_bs_ym=%d and note='リニア営業外費用その他'", $yyyymm);
    if (getUniResult($query, $l_lother) < 1) {
        $l_lother = 0;                 // 検索失敗
    }
}
if ($yyyymm >= 201604) {
    $query = sprintf("select kin from profit_loss_pl_history where pl_bs_ym=%d and note='機工営業外費用その他'", $yyyymm);
    if (getUniResult($query, $t_lother) < 1) {
        $t_lother = 0;                 // 検索失敗
    }
} else {
    $t_lother = 0;                 // 検索失敗
}
$query = sprintf("select kin from profit_loss_pl_history where pl_bs_ym=%d and note='試験修理営業外費用その他'", $yyyymm);
if (getUniResult($query, $s_lother) < 1) {
    $s_lother = 0;                 // 検索失敗
}
$query = sprintf("select kin from profit_loss_pl_history where pl_bs_ym=%d and note='商品管理営業外費用その他'", $yyyymm);
if (getUniResult($query, $b_lother) < 1) {
    $b_lother = 0;                 // 検索失敗
}
$query = sprintf("select kin from profit_loss_pl_history where pl_bs_ym=%d and note='全体営業外費用その他'", $yyyymm);
if (getUniResult($query, $all_lother) < 1) {
    $all_lother = 0;                 // 検索失敗
}
    ///// 前期
$query = sprintf("select kin from profit_loss_pl_history where pl_bs_ym=%d and note='カプラ営業外費用その他'", $p1_ym);
if (getUniResult($query, $p1_c_lother) < 1) {
    $p1_c_lother = 0;                      // 検索失敗
}
if ($p1_ym >= 201603) {
    $query = sprintf("select kin from profit_loss_pl_history where pl_bs_ym=%d and note='リニア標準営業外費用その他'", $p1_ym);
    if (getUniResult($query, $p1_l_lother) < 1) {
        $p1_l_lother = 0;                      // 検索失敗
    }
} else {
    $query = sprintf("select kin from profit_loss_pl_history where pl_bs_ym=%d and note='リニア営業外費用その他'", $p1_ym);
    if (getUniResult($query, $p1_l_lother) < 1) {
        $p1_l_lother = 0;                      // 検索失敗
    }
}
if ($p1_ym >= 201603) {
    $query = sprintf("select kin from profit_loss_pl_history where pl_bs_ym=%d and note='機工営業外費用その他'", $p1_ym);
    if (getUniResult($query, $p1_t_lother) < 1) {
        $p1_t_lother = 0;                 // 検索失敗
    }
} else {
    $p1_t_lother = 0;                 // 検索失敗
}
$query = sprintf("select kin from profit_loss_pl_history where pl_bs_ym=%d and note='試験修理営業外費用その他'", $p1_ym);
if (getUniResult($query, $p1_s_lother) < 1) {
    $p1_s_lother = 0;                      // 検索失敗
}
$query = sprintf("select kin from profit_loss_pl_history where pl_bs_ym=%d and note='商品管理営業外費用その他'", $p1_ym);
if (getUniResult($query, $p1_b_lother) < 1) {
    $p1_b_lother = 0;                      // 検索失敗
}
$query = sprintf("select kin from profit_loss_pl_history where pl_bs_ym=%d and note='全体営業外費用その他'", $p1_ym);
if (getUniResult($query, $p1_all_lother) < 1) {
    $p1_all_lother = 0;                      // 検索失敗
}

/********** 営業外費用の合計 **********/
    ///// 当期
$query = sprintf("select kin from profit_loss_pl_history where pl_bs_ym=%d and note='カプラ営業外費用計'", $yyyymm);
if (getUniResult($query, $c_nonope_loss_sum) < 1) {
    $c_nonope_loss_sum = 0;                 // 検索失敗
}
if ($yyyymm >= 201604) {
    $query = sprintf("select kin from profit_loss_pl_history where pl_bs_ym=%d and note='リニア標準営業外費用計'", $yyyymm);
    if (getUniResult($query, $l_nonope_loss_sum) < 1) {
        $l_nonope_loss_sum = 0;                 // 検索失敗
    }
} else {
    $query = sprintf("select kin from profit_loss_pl_history where pl_bs_ym=%d and note='リニア営業外費用計'", $yyyymm);
    if (getUniResult($query, $l_nonope_loss_sum) < 1) {
        $l_nonope_loss_sum = 0;                 // 検索失敗
    }
}
if ($yyyymm >= 201604) {
    $query = sprintf("select kin from profit_loss_pl_history where pl_bs_ym=%d and note='機工営業外費用計'", $yyyymm);
    if (getUniResult($query, $t_nonope_loss_sum) < 1) {
        $t_nonope_loss_sum = 0;                 // 検索失敗
    }
} else {
    $t_nonope_loss_sum = 0;                 // 検索失敗
}
$query = sprintf("select kin from profit_loss_pl_history where pl_bs_ym=%d and note='試験修理営業外費用計'", $yyyymm);
if (getUniResult($query, $s_nonope_loss_sum) < 1) {
    $s_nonope_loss_sum = 0;                 // 検索失敗
}
$query = sprintf("select kin from profit_loss_pl_history where pl_bs_ym=%d and note='商品管理営業外費用計'", $yyyymm);
if (getUniResult($query, $b_nonope_loss_sum) < 1) {
    $b_nonope_loss_sum = 0;                 // 検索失敗
}
$query = sprintf("select kin from profit_loss_pl_history where pl_bs_ym=%d and note='全体営業外費用計'", $yyyymm);
if (getUniResult($query, $all_nonope_loss_sum) < 1) {
    $all_nonope_loss_sum = 0;                 // 検索失敗
}
    ///// 前期
$query = sprintf("select kin from profit_loss_pl_history where pl_bs_ym=%d and note='カプラ営業外費用計'", $p1_ym);
if (getUniResult($query, $p1_c_nonope_loss_sum) < 1) {
    $p1_c_nonope_loss_sum = 0;                      // 検索失敗
}
if ($p1_ym >= 201603) {
    $query = sprintf("select kin from profit_loss_pl_history where pl_bs_ym=%d and note='リニア標準営業外費用計'", $p1_ym);
    if (getUniResult($query, $p1_l_nonope_loss_sum) < 1) {
        $p1_l_nonope_loss_sum = 0;                      // 検索失敗
    }
} else {
    $query = sprintf("select kin from profit_loss_pl_history where pl_bs_ym=%d and note='リニア営業外費用計'", $p1_ym);
    if (getUniResult($query, $p1_l_nonope_loss_sum) < 1) {
        $p1_l_nonope_loss_sum = 0;                      // 検索失敗
    }
}
if ($p1_ym >= 201603) {
    $query = sprintf("select kin from profit_loss_pl_history where pl_bs_ym=%d and note='機工営業外費用計'", $p1_ym);
    if (getUniResult($query, $p1_t_nonope_loss_sum) < 1) {
        $p1_t_nonope_loss_sum = 0;                 // 検索失敗
    }
} else {
    $p1_t_nonope_loss_sum = 0;                 // 検索失敗
}
$query = sprintf("select kin from profit_loss_pl_history where pl_bs_ym=%d and note='試験修理営業外費用計'", $p1_ym);
if (getUniResult($query, $p1_s_nonope_loss_sum) < 1) {
    $p1_s_nonope_loss_sum = 0;                      // 検索失敗
}
$query = sprintf("select kin from profit_loss_pl_history where pl_bs_ym=%d and note='商品管理営業外費用計'", $p1_ym);
if (getUniResult($query, $p1_b_nonope_loss_sum) < 1) {
    $p1_b_nonope_loss_sum = 0;                      // 検索失敗
}
$query = sprintf("select kin from profit_loss_pl_history where pl_bs_ym=%d and note='全体営業外費用計'", $p1_ym);
if (getUniResult($query, $p1_all_nonope_loss_sum) < 1) {
    $p1_all_nonope_loss_sum = 0;                      // 検索失敗
}

/********** 経常利益 **********/
    ///// 当期
$query = sprintf("select kin from profit_loss_pl_history where pl_bs_ym=%d and note='カプラ経常利益'", $yyyymm);
if (getUniResult($query, $c_current_profit) < 1) {
    $c_current_profit = 0;                 // 検索失敗
}
if ($yyyymm >= 201604) {
    $query = sprintf("select kin from profit_loss_pl_history where pl_bs_ym=%d and note='リニア標準経常利益'", $yyyymm);
    if (getUniResult($query, $l_current_profit) < 1) {
        $l_current_profit = 0;                 // 検索失敗
    }
} else {
    $query = sprintf("select kin from profit_loss_pl_history where pl_bs_ym=%d and note='リニア経常利益'", $yyyymm);
    if (getUniResult($query, $l_current_profit) < 1) {
        $l_current_profit = 0;                 // 検索失敗
    }
}
if ($yyyymm >= 201604) {
    $query = sprintf("select kin from profit_loss_pl_history where pl_bs_ym=%d and note='機工経常利益'", $yyyymm);
    if (getUniResult($query, $t_current_profit) < 1) {
        $t_current_profit = 0;                 // 検索失敗
    }
} else {
    $t_current_profit = 0;                 // 検索失敗
}
$query = sprintf("select kin from profit_loss_pl_history where pl_bs_ym=%d and note='試験修理経常利益'", $yyyymm);
if (getUniResult($query, $s_current_profit) < 1) {
    $s_current_profit = 0;                 // 検索失敗
}
$query = sprintf("select kin from profit_loss_pl_history where pl_bs_ym=%d and note='商品管理経常利益'", $yyyymm);
if (getUniResult($query, $b_current_profit) < 1) {
    $b_current_profit = 0;                 // 検索失敗
}
$query = sprintf("select kin from profit_loss_pl_history where pl_bs_ym=%d and note='全体経常利益'", $yyyymm);
if (getUniResult($query, $all_current_profit) < 1) {
    $all_current_profit = 0;                 // 検索失敗
}
    ///// 前期
$query = sprintf("select kin from profit_loss_pl_history where pl_bs_ym=%d and note='カプラ経常利益'", $p1_ym);
if (getUniResult($query, $p1_c_current_profit) < 1) {
    $p1_c_current_profit = 0;                      // 検索失敗
}
if ($p1_ym >= 201603) {
    $query = sprintf("select kin from profit_loss_pl_history where pl_bs_ym=%d and note='リニア標準経常利益'", $p1_ym);
    if (getUniResult($query, $p1_l_current_profit) < 1) {
        $p1_l_current_profit = 0;                      // 検索失敗
    }
} else {
    $query = sprintf("select kin from profit_loss_pl_history where pl_bs_ym=%d and note='リニア経常利益'", $p1_ym);
    if (getUniResult($query, $p1_l_current_profit) < 1) {
        $p1_l_current_profit = 0;                      // 検索失敗
    }
}
if ($p1_ym >= 201603) {
    $query = sprintf("select kin from profit_loss_pl_history where pl_bs_ym=%d and note='機工経常利益'", $p1_ym);
    if (getUniResult($query, $p1_t_current_profit) < 1) {
        $p1_t_current_profit = 0;                 // 検索失敗
    }
} else {
    $p1_t_current_profit = 0;                 // 検索失敗
}
$query = sprintf("select kin from profit_loss_pl_history where pl_bs_ym=%d and note='試験修理経常利益'", $p1_ym);
if (getUniResult($query, $p1_s_current_profit) < 1) {
    $p1_s_current_profit = 0;                      // 検索失敗
}
$query = sprintf("select kin from profit_loss_pl_history where pl_bs_ym=%d and note='商品管理経常利益'", $p1_ym);
if (getUniResult($query, $p1_b_current_profit) < 1) {
    $p1_b_current_profit = 0;                      // 検索失敗
}
$query = sprintf("select kin from profit_loss_pl_history where pl_bs_ym=%d and note='全体経常利益'", $p1_ym);
if (getUniResult($query, $p1_all_current_profit) < 1) {
    $p1_all_current_profit = 0;                      // 検索失敗
}

/********** 特別利益 **********/
    ///// 当期
$query = sprintf("select kin from profit_loss_pl_history where pl_bs_ym=%d and note='全体特別利益'", $yyyymm);
if (getUniResult($query, $all_special_profit) < 1) {
    $all_special_profit = 0;                 // 検索失敗
}
    ///// 前期
$query = sprintf("select kin from profit_loss_pl_history where pl_bs_ym=%d and note='全体特別利益'", $p1_ym);
if (getUniResult($query, $p1_all_special_profit) < 1) {
    $p1_all_special_profit = 0;                      // 検索失敗
}

/********** 特別損失 **********/
    ///// 当期
$query = sprintf("select kin from profit_loss_pl_history where pl_bs_ym=%d and note='全体特別損失'", $yyyymm);
if (getUniResult($query, $all_special_loss) < 1) {
    $all_special_loss = 0;                 // 検索失敗
}
    ///// 前期
$query = sprintf("select kin from profit_loss_pl_history where pl_bs_ym=%d and note='全体特別損失'", $p1_ym);
if (getUniResult($query, $p1_all_special_loss) < 1) {
    $p1_all_special_loss = 0;                      // 検索失敗
}

/********** 税引前純利益金額 **********/
    ///// 当期
$query = sprintf("select kin from profit_loss_pl_history where pl_bs_ym=%d and note='全体税引前純利益金額'", $yyyymm);
if (getUniResult($query, $all_before_tax_net_profit) < 1) {
    $all_before_tax_net_profit = 0;                 // 検索失敗
}
    ///// 前期
$query = sprintf("select kin from profit_loss_pl_history where pl_bs_ym=%d and note='全体税引前純利益金額'", $p1_ym);
if (getUniResult($query, $p1_all_before_tax_net_profit) < 1) {
    $p1_all_before_tax_net_profit = 0;                      // 検索失敗
}

    ///// 各前期比増減の計算
$def_c_uri               = $c_uri - $p1_c_uri;
$def_c_invent            = $c_invent - $p1_c_invent;
$def_c_metarial          = $c_metarial - $p1_c_metarial;
$def_c_roumu             = $c_roumu - $p1_c_roumu;
$def_c_expense           = $c_expense - $p1_c_expense;
$def_c_endinv            = $p1_c_endinv - $c_endinv;
$def_c_urigen            = $c_urigen - $p1_c_urigen;
$def_c_gross_profit      = $c_gross_profit - $p1_c_gross_profit;
$def_c_han_jin           = $c_han_jin - $p1_c_han_jin;
$def_c_han_kei           = $c_han_kei - $p1_c_han_kei;
$def_c_han_all           = $c_han_all - $p1_c_han_all;
$def_c_ope_profit        = $c_ope_profit - $p1_c_ope_profit;
$def_c_gyoumu            = $c_gyoumu - $p1_c_gyoumu;
$def_c_swari             = $c_swari - $p1_c_swari;
$def_c_pother            = $c_pother - $p1_c_pother;
$def_c_nonope_profit_sum = $c_nonope_profit_sum - $p1_c_nonope_profit_sum;
$def_c_srisoku           = $c_srisoku - $p1_c_srisoku;
$def_c_lother            = $c_lother - $p1_c_lother;
$def_c_nonope_loss_sum   = $c_nonope_loss_sum - $p1_c_nonope_loss_sum;
$def_c_current_profit    = $c_current_profit - $p1_c_current_profit;

$def_l_uri               = $l_uri - $p1_l_uri;
$def_l_invent            = $l_invent - $p1_l_invent;
$def_l_metarial          = $l_metarial - $p1_l_metarial;
$def_l_roumu             = $l_roumu - $p1_l_roumu;
$def_l_expense           = $l_expense - $p1_l_expense;
$def_l_endinv            = $p1_l_endinv - $l_endinv;
$def_l_urigen            = $l_urigen - $p1_l_urigen;
$def_l_gross_profit      = $l_gross_profit - $p1_l_gross_profit;
$def_l_han_jin           = $l_han_jin - $p1_l_han_jin;
$def_l_han_kei           = $l_han_kei - $p1_l_han_kei;
$def_l_han_all           = $l_han_all - $p1_l_han_all;
$def_l_ope_profit        = $l_ope_profit - $p1_l_ope_profit;
$def_l_gyoumu            = $l_gyoumu - $p1_l_gyoumu;
$def_l_swari             = $l_swari - $p1_l_swari;
$def_l_pother            = $l_pother - $p1_l_pother;
$def_l_nonope_profit_sum = $l_nonope_profit_sum - $p1_l_nonope_profit_sum;
$def_l_srisoku           = $l_srisoku - $p1_l_srisoku;
$def_l_lother            = $l_lother - $p1_l_lother;
$def_l_nonope_loss_sum   = $l_nonope_loss_sum - $p1_l_nonope_loss_sum;
$def_l_current_profit    = $l_current_profit - $p1_l_current_profit;

$def_t_uri               = $t_uri - $p1_t_uri;
$def_t_invent            = $t_invent - $p1_t_invent;
$def_t_metarial          = $t_metarial - $p1_t_metarial;
$def_t_roumu             = $t_roumu - $p1_t_roumu;
$def_t_expense           = $t_expense - $p1_t_expense;
$def_t_endinv            = $p1_t_endinv - $t_endinv;
$def_t_urigen            = $t_urigen - $p1_t_urigen;
$def_t_gross_profit      = $t_gross_profit - $p1_t_gross_profit;
$def_t_han_jin           = $t_han_jin - $p1_t_han_jin;
$def_t_han_kei           = $t_han_kei - $p1_t_han_kei;
$def_t_han_all           = $t_han_all - $p1_t_han_all;
$def_t_ope_profit        = $t_ope_profit - $p1_t_ope_profit;
$def_t_gyoumu            = $t_gyoumu - $p1_t_gyoumu;
$def_t_swari             = $t_swari - $p1_t_swari;
$def_t_pother            = $t_pother - $p1_t_pother;
$def_t_nonope_profit_sum = $t_nonope_profit_sum - $p1_t_nonope_profit_sum;
$def_t_srisoku           = $t_srisoku - $p1_t_srisoku;
$def_t_lother            = $t_lother - $p1_t_lother;
$def_t_nonope_loss_sum   = $t_nonope_loss_sum - $p1_t_nonope_loss_sum;
$def_t_current_profit    = $t_current_profit - $p1_t_current_profit;

$def_s_uri               = $s_uri - $p1_s_uri;
$def_s_invent            = $s_invent - $p1_s_invent;
$def_s_metarial          = $s_metarial - $p1_s_metarial;
$def_s_roumu             = $s_roumu - $p1_s_roumu;
$def_s_expense           = $s_expense - $p1_s_expense;
$def_s_endinv            = $p1_s_endinv - $s_endinv;
$def_s_urigen            = $s_urigen - $p1_s_urigen;
$def_s_gross_profit      = $s_gross_profit - $p1_s_gross_profit;
$def_s_han_jin           = $s_han_jin - $p1_s_han_jin;
$def_s_han_kei           = $s_han_kei - $p1_s_han_kei;
$def_s_han_all           = $s_han_all - $p1_s_han_all;
$def_s_ope_profit        = $s_ope_profit - $p1_s_ope_profit;
$def_s_gyoumu            = $s_gyoumu - $p1_s_gyoumu;
$def_s_swari             = $s_swari - $p1_s_swari;
$def_s_pother            = $s_pother - $p1_s_pother;
$def_s_nonope_profit_sum = $s_nonope_profit_sum - $p1_s_nonope_profit_sum;
$def_s_srisoku           = $s_srisoku - $p1_s_srisoku;
$def_s_lother            = $s_lother - $p1_s_lother;
$def_s_nonope_loss_sum   = $s_nonope_loss_sum - $p1_s_nonope_loss_sum;
$def_s_current_profit    = $s_current_profit - $p1_s_current_profit;

$def_b_uri               = $b_uri - $p1_b_uri;
$def_b_invent            = $b_invent - $p1_b_invent;
$def_b_metarial          = $b_metarial - $p1_b_metarial;
$def_b_roumu             = $b_roumu - $p1_b_roumu;
$def_b_expense           = $b_expense - $p1_b_expense;
$def_b_endinv            = $p1_b_endinv - $b_endinv;
$def_b_urigen            = $b_urigen - $p1_b_urigen;
$def_b_gross_profit      = $b_gross_profit - $p1_b_gross_profit;
$def_b_han_jin           = $b_han_jin - $p1_b_han_jin;
$def_b_han_kei           = $b_han_kei - $p1_b_han_kei;
$def_b_han_all           = $b_han_all - $p1_b_han_all;
$def_b_ope_profit        = $b_ope_profit - $p1_b_ope_profit;
$def_b_gyoumu            = $b_gyoumu - $p1_b_gyoumu;
$def_b_swari             = $b_swari - $p1_b_swari;
$def_b_pother            = $b_pother - $p1_b_pother;
$def_b_nonope_profit_sum = $b_nonope_profit_sum - $p1_b_nonope_profit_sum;
$def_b_srisoku           = $b_srisoku - $p1_b_srisoku;
$def_b_lother            = $b_lother - $p1_b_lother;
$def_b_nonope_loss_sum   = $b_nonope_loss_sum - $p1_b_nonope_loss_sum;
$def_b_current_profit    = $b_current_profit - $p1_b_current_profit;

$def_all_uri                   = $all_uri - $p1_all_uri;
$def_all_invent                = $all_invent - $p1_all_invent;
$def_all_metarial              = $all_metarial - $p1_all_metarial;
$def_all_roumu                 = $all_roumu - $p1_all_roumu;
$def_all_expense               = $all_expense - $p1_all_expense;
$def_all_endinv                = $p1_all_endinv - $all_endinv;
$def_all_urigen                = $all_urigen - $p1_all_urigen;
$def_all_gross_profit          = $all_gross_profit - $p1_all_gross_profit;
$def_all_han_jin               = $all_han_jin - $p1_all_han_jin;
$def_all_han_kei               = $all_han_kei - $p1_all_han_kei;
$def_all_han_all               = $all_han_all - $p1_all_han_all;
$def_all_ope_profit            = $all_ope_profit - $p1_all_ope_profit;
$def_all_gyoumu                = $all_gyoumu - $p1_all_gyoumu;
$def_all_swari                 = $all_swari - $p1_all_swari;
$def_all_pother                = $all_pother - $p1_all_pother;
$def_all_nonope_profit_sum     = $all_nonope_profit_sum - $p1_all_nonope_profit_sum;
$def_all_srisoku               = $all_srisoku - $p1_all_srisoku;
$def_all_lother                = $all_lother - $p1_all_lother;
$def_all_nonope_loss_sum       = $all_nonope_loss_sum - $p1_all_nonope_loss_sum;
$def_all_current_profit        = $all_current_profit - $p1_all_current_profit;
$def_all_special_profit        = $all_special_profit - $p1_all_special_profit;
$def_all_special_loss          = $all_special_loss - $p1_all_special_loss;
$def_all_before_tax_net_profit = $all_before_tax_net_profit - $p1_all_before_tax_net_profit;

    ///// 各桁のフォーマット変更
$c_uri                   = number_format(($c_uri / $tani), $keta);
$p1_c_uri                = number_format(($p1_c_uri / $tani), $keta);
$def_c_uri               = number_format(($def_c_uri / $tani), $keta);
$c_invent                = number_format(($c_invent / $tani), $keta);
$p1_c_invent             = number_format(($p1_c_invent / $tani), $keta);
$def_c_invent            = number_format(($def_c_invent / $tani), $keta);
$c_metarial              = number_format(($c_metarial / $tani), $keta);
$p1_c_metarial           = number_format(($p1_c_metarial / $tani), $keta);
$def_c_metarial          = number_format(($def_c_metarial / $tani), $keta);
$c_roumu                 = number_format(($c_roumu / $tani), $keta);
$p1_c_roumu              = number_format(($p1_c_roumu / $tani), $keta);
$def_c_roumu             = number_format(($def_c_roumu / $tani), $keta);
$c_expense               = number_format(($c_expense / $tani), $keta);
$p1_c_expense            = number_format(($p1_c_expense / $tani), $keta);
$def_c_expense           = number_format(($def_c_expense / $tani), $keta);
$c_endinv                = number_format(($c_endinv / $tani), $keta);
$p1_c_endinv             = number_format(($p1_c_endinv / $tani), $keta);
$def_c_endinv            = number_format(($def_c_endinv / $tani), $keta);
$c_urigen                = number_format(($c_urigen / $tani), $keta);
$p1_c_urigen             = number_format(($p1_c_urigen / $tani), $keta);
$def_c_urigen            = number_format(($def_c_urigen / $tani), $keta);
$c_gross_profit          = number_format(($c_gross_profit / $tani), $keta);
$p1_c_gross_profit       = number_format(($p1_c_gross_profit / $tani), $keta);
$def_c_gross_profit      = number_format(($def_c_gross_profit / $tani), $keta);
$c_han_jin               = number_format(($c_han_jin / $tani), $keta);
$p1_c_han_jin            = number_format(($p1_c_han_jin / $tani), $keta);
$def_c_han_jin           = number_format(($def_c_han_jin / $tani), $keta);
$c_han_kei               = number_format(($c_han_kei / $tani), $keta);
$p1_c_han_kei            = number_format(($p1_c_han_kei / $tani), $keta);
$def_c_han_kei           = number_format(($def_c_han_kei / $tani), $keta);
$c_han_all               = number_format(($c_han_all / $tani), $keta);
$p1_c_han_all            = number_format(($p1_c_han_all / $tani), $keta);
$def_c_han_all           = number_format(($def_c_han_all / $tani), $keta);
$c_ope_profit            = number_format(($c_ope_profit / $tani), $keta);
$p1_c_ope_profit         = number_format(($p1_c_ope_profit / $tani), $keta);
$def_c_ope_profit        = number_format(($def_c_ope_profit / $tani), $keta);
$c_gyoumu                = number_format(($c_gyoumu / $tani), $keta);
$p1_c_gyoumu             = number_format(($p1_c_gyoumu / $tani), $keta);
$def_c_gyoumu            = number_format(($def_c_gyoumu / $tani), $keta);
$c_swari                 = number_format(($c_swari / $tani), $keta);
$p1_c_swari              = number_format(($p1_c_swari / $tani), $keta);
$def_c_swari             = number_format(($def_c_swari / $tani), $keta);
$c_pother                = number_format(($c_pother / $tani), $keta);
$p1_c_pother             = number_format(($p1_c_pother / $tani), $keta);
$def_c_pother            = number_format(($def_c_pother / $tani), $keta);
$c_nonope_profit_sum     = number_format(($c_nonope_profit_sum / $tani), $keta);
$p1_c_nonope_profit_sum  = number_format(($p1_c_nonope_profit_sum / $tani), $keta);
$def_c_nonope_profit_sum = number_format(($def_c_nonope_profit_sum / $tani), $keta);
$c_srisoku               = number_format(($c_srisoku / $tani), $keta);
$p1_c_srisoku            = number_format(($p1_c_srisoku / $tani), $keta);
$def_c_srisoku           = number_format(($def_c_srisoku / $tani), $keta);
$c_lother                = number_format(($c_lother / $tani), $keta);
$p1_c_lother             = number_format(($p1_c_lother / $tani), $keta);
$def_c_lother            = number_format(($def_c_lother / $tani), $keta);
$c_nonope_loss_sum       = number_format(($c_nonope_loss_sum / $tani), $keta);
$p1_c_nonope_loss_sum    = number_format(($p1_c_nonope_loss_sum / $tani), $keta);
$def_c_nonope_loss_sum   = number_format(($def_c_nonope_loss_sum / $tani), $keta);
$c_current_profit        = number_format(($c_current_profit / $tani), $keta);
$p1_c_current_profit     = number_format(($p1_c_current_profit / $tani), $keta);
$def_c_current_profit    = number_format(($def_c_current_profit / $tani), $keta);

$l_uri                   = number_format(($l_uri / $tani), $keta);
$p1_l_uri                = number_format(($p1_l_uri / $tani), $keta);
$def_l_uri               = number_format(($def_l_uri / $tani), $keta);
$l_invent                = number_format(($l_invent / $tani), $keta);
$p1_l_invent             = number_format(($p1_l_invent / $tani), $keta);
$def_l_invent            = number_format(($def_l_invent / $tani), $keta);
$l_metarial              = number_format(($l_metarial / $tani), $keta);
$p1_l_metarial           = number_format(($p1_l_metarial / $tani), $keta);
$def_l_metarial          = number_format(($def_l_metarial / $tani), $keta);
$l_roumu                 = number_format(($l_roumu / $tani), $keta);
$p1_l_roumu              = number_format(($p1_l_roumu / $tani), $keta);
$def_l_roumu             = number_format(($def_l_roumu / $tani), $keta);
$l_expense               = number_format(($l_expense / $tani), $keta);
$p1_l_expense            = number_format(($p1_l_expense / $tani), $keta);
$def_l_expense           = number_format(($def_l_expense / $tani), $keta);
$l_endinv                = number_format(($l_endinv / $tani), $keta);
$p1_l_endinv             = number_format(($p1_l_endinv / $tani), $keta);
$def_l_endinv            = number_format(($def_l_endinv / $tani), $keta);
$l_urigen                = number_format(($l_urigen / $tani), $keta);
$p1_l_urigen             = number_format(($p1_l_urigen / $tani), $keta);
$def_l_urigen            = number_format(($def_l_urigen / $tani), $keta);
$l_gross_profit          = number_format(($l_gross_profit / $tani), $keta);
$p1_l_gross_profit       = number_format(($p1_l_gross_profit / $tani), $keta);
$def_l_gross_profit      = number_format(($def_l_gross_profit / $tani), $keta);
$l_han_jin               = number_format(($l_han_jin / $tani), $keta);
$p1_l_han_jin            = number_format(($p1_l_han_jin / $tani), $keta);
$def_l_han_jin           = number_format(($def_l_han_jin / $tani), $keta);
$l_han_kei               = number_format(($l_han_kei / $tani), $keta);
$p1_l_han_kei            = number_format(($p1_l_han_kei / $tani), $keta);
$def_l_han_kei           = number_format(($def_l_han_kei / $tani), $keta);
$l_han_all               = number_format(($l_han_all / $tani), $keta);
$p1_l_han_all            = number_format(($p1_l_han_all / $tani), $keta);
$def_l_han_all           = number_format(($def_l_han_all / $tani), $keta);
$l_ope_profit            = number_format(($l_ope_profit / $tani), $keta);
$p1_l_ope_profit         = number_format(($p1_l_ope_profit / $tani), $keta);
$def_l_ope_profit        = number_format(($def_l_ope_profit / $tani), $keta);
$l_gyoumu                = number_format(($l_gyoumu / $tani), $keta);
$p1_l_gyoumu             = number_format(($p1_l_gyoumu / $tani), $keta);
$def_l_gyoumu            = number_format(($def_l_gyoumu / $tani), $keta);
$l_swari                 = number_format(($l_swari / $tani), $keta);
$p1_l_swari              = number_format(($p1_l_swari / $tani), $keta);
$def_l_swari             = number_format(($def_l_swari / $tani), $keta);
$l_pother                = number_format(($l_pother / $tani), $keta);
$p1_l_pother             = number_format(($p1_l_pother / $tani), $keta);
$def_l_pother            = number_format(($def_l_pother / $tani), $keta);
$l_nonope_profit_sum     = number_format(($l_nonope_profit_sum / $tani), $keta);
$p1_l_nonope_profit_sum  = number_format(($p1_l_nonope_profit_sum / $tani), $keta);
$def_l_nonope_profit_sum = number_format(($def_l_nonope_profit_sum / $tani), $keta);
$l_srisoku               = number_format(($l_srisoku / $tani), $keta);
$p1_l_srisoku            = number_format(($p1_l_srisoku / $tani), $keta);
$def_l_srisoku           = number_format(($def_l_srisoku / $tani), $keta);
$l_lother                = number_format(($l_lother / $tani), $keta);
$p1_l_lother             = number_format(($p1_l_lother / $tani), $keta);
$def_l_lother            = number_format(($def_l_lother / $tani), $keta);
$l_nonope_loss_sum       = number_format(($l_nonope_loss_sum / $tani), $keta);
$p1_l_nonope_loss_sum    = number_format(($p1_l_nonope_loss_sum / $tani), $keta);
$def_l_nonope_loss_sum   = number_format(($def_l_nonope_loss_sum / $tani), $keta);
$l_current_profit        = number_format(($l_current_profit / $tani), $keta);
$p1_l_current_profit     = number_format(($p1_l_current_profit / $tani), $keta);
$def_l_current_profit    = number_format(($def_l_current_profit / $tani), $keta);

$t_uri                   = number_format(($t_uri / $tani), $keta);
$p1_t_uri                = number_format(($p1_t_uri / $tani), $keta);
$def_t_uri               = number_format(($def_t_uri / $tani), $keta);
$t_invent                = number_format(($t_invent / $tani), $keta);
$p1_t_invent             = number_format(($p1_t_invent / $tani), $keta);
$def_t_invent            = number_format(($def_t_invent / $tani), $keta);
$t_metarial              = number_format(($t_metarial / $tani), $keta);
$p1_t_metarial           = number_format(($p1_t_metarial / $tani), $keta);
$def_t_metarial          = number_format(($def_t_metarial / $tani), $keta);
$t_roumu                 = number_format(($t_roumu / $tani), $keta);
$p1_t_roumu              = number_format(($p1_t_roumu / $tani), $keta);
$def_t_roumu             = number_format(($def_t_roumu / $tani), $keta);
$t_expense               = number_format(($t_expense / $tani), $keta);
$p1_t_expense            = number_format(($p1_t_expense / $tani), $keta);
$def_t_expense           = number_format(($def_t_expense / $tani), $keta);
$t_endinv                = number_format(($t_endinv / $tani), $keta);
$p1_t_endinv             = number_format(($p1_t_endinv / $tani), $keta);
$def_t_endinv            = number_format(($def_t_endinv / $tani), $keta);
$t_urigen                = number_format(($t_urigen / $tani), $keta);
$p1_t_urigen             = number_format(($p1_t_urigen / $tani), $keta);
$def_t_urigen            = number_format(($def_t_urigen / $tani), $keta);
$t_gross_profit          = number_format(($t_gross_profit / $tani), $keta);
$p1_t_gross_profit       = number_format(($p1_t_gross_profit / $tani), $keta);
$def_t_gross_profit      = number_format(($def_t_gross_profit / $tani), $keta);
$t_han_jin               = number_format(($t_han_jin / $tani), $keta);
$p1_t_han_jin            = number_format(($p1_t_han_jin / $tani), $keta);
$def_t_han_jin           = number_format(($def_t_han_jin / $tani), $keta);
$t_han_kei               = number_format(($t_han_kei / $tani), $keta);
$p1_t_han_kei            = number_format(($p1_t_han_kei / $tani), $keta);
$def_t_han_kei           = number_format(($def_t_han_kei / $tani), $keta);
$t_han_all               = number_format(($t_han_all / $tani), $keta);
$p1_t_han_all            = number_format(($p1_t_han_all / $tani), $keta);
$def_t_han_all           = number_format(($def_t_han_all / $tani), $keta);
$t_ope_profit            = number_format(($t_ope_profit / $tani), $keta);
$p1_t_ope_profit         = number_format(($p1_t_ope_profit / $tani), $keta);
$def_t_ope_profit        = number_format(($def_t_ope_profit / $tani), $keta);
$t_gyoumu                = number_format(($t_gyoumu / $tani), $keta);
$p1_t_gyoumu             = number_format(($p1_t_gyoumu / $tani), $keta);
$def_t_gyoumu            = number_format(($def_t_gyoumu / $tani), $keta);
$t_swari                 = number_format(($t_swari / $tani), $keta);
$p1_t_swari              = number_format(($p1_t_swari / $tani), $keta);
$def_t_swari             = number_format(($def_t_swari / $tani), $keta);
$t_pother                = number_format(($t_pother / $tani), $keta);
$p1_t_pother             = number_format(($p1_t_pother / $tani), $keta);
$def_t_pother            = number_format(($def_t_pother / $tani), $keta);
$t_nonope_profit_sum     = number_format(($t_nonope_profit_sum / $tani), $keta);
$p1_t_nonope_profit_sum  = number_format(($p1_t_nonope_profit_sum / $tani), $keta);
$def_t_nonope_profit_sum = number_format(($def_t_nonope_profit_sum / $tani), $keta);
$t_srisoku               = number_format(($t_srisoku / $tani), $keta);
$p1_t_srisoku            = number_format(($p1_t_srisoku / $tani), $keta);
$def_t_srisoku           = number_format(($def_t_srisoku / $tani), $keta);
$t_lother                = number_format(($t_lother / $tani), $keta);
$p1_t_lother             = number_format(($p1_t_lother / $tani), $keta);
$def_t_lother            = number_format(($def_t_lother / $tani), $keta);
$t_nonope_loss_sum       = number_format(($t_nonope_loss_sum / $tani), $keta);
$p1_t_nonope_loss_sum    = number_format(($p1_t_nonope_loss_sum / $tani), $keta);
$def_t_nonope_loss_sum   = number_format(($def_t_nonope_loss_sum / $tani), $keta);
$t_current_profit        = number_format(($t_current_profit / $tani), $keta);
$p1_t_current_profit     = number_format(($p1_t_current_profit / $tani), $keta);
$def_t_current_profit    = number_format(($def_t_current_profit / $tani), $keta);

$s_uri                   = number_format(($s_uri / $tani), $keta);
$p1_s_uri                = number_format(($p1_s_uri / $tani), $keta);
$def_s_uri               = number_format(($def_s_uri / $tani), $keta);
$s_invent                = number_format(($s_invent / $tani), $keta);
$p1_s_invent             = number_format(($p1_s_invent / $tani), $keta);
$def_s_invent            = number_format(($def_s_invent / $tani), $keta);
$s_metarial              = number_format(($s_metarial / $tani), $keta);
$p1_s_metarial           = number_format(($p1_s_metarial / $tani), $keta);
$def_s_metarial          = number_format(($def_s_metarial / $tani), $keta);
$s_roumu                 = number_format(($s_roumu / $tani), $keta);
$p1_s_roumu              = number_format(($p1_s_roumu / $tani), $keta);
$def_s_roumu             = number_format(($def_s_roumu / $tani), $keta);
$s_expense               = number_format(($s_expense / $tani), $keta);
$p1_s_expense            = number_format(($p1_s_expense / $tani), $keta);
$def_s_expense           = number_format(($def_s_expense / $tani), $keta);
$s_endinv                = number_format(($s_endinv / $tani), $keta);
$p1_s_endinv             = number_format(($p1_s_endinv / $tani), $keta);
$def_s_endinv            = number_format(($def_s_endinv / $tani), $keta);
$s_urigen                = number_format(($s_urigen / $tani), $keta);
$p1_s_urigen             = number_format(($p1_s_urigen / $tani), $keta);
$def_s_urigen            = number_format(($def_s_urigen / $tani), $keta);
$s_gross_profit          = number_format(($s_gross_profit / $tani), $keta);
$p1_s_gross_profit       = number_format(($p1_s_gross_profit / $tani), $keta);
$def_s_gross_profit      = number_format(($def_s_gross_profit / $tani), $keta);
$s_han_jin               = number_format(($s_han_jin / $tani), $keta);
$p1_s_han_jin            = number_format(($p1_s_han_jin / $tani), $keta);
$def_s_han_jin           = number_format(($def_s_han_jin / $tani), $keta);
$s_han_kei               = number_format(($s_han_kei / $tani), $keta);
$p1_s_han_kei            = number_format(($p1_s_han_kei / $tani), $keta);
$def_s_han_kei           = number_format(($def_s_han_kei / $tani), $keta);
$s_han_all               = number_format(($s_han_all / $tani), $keta);
$p1_s_han_all            = number_format(($p1_s_han_all / $tani), $keta);
$def_s_han_all           = number_format(($def_s_han_all / $tani), $keta);
$s_ope_profit            = number_format(($s_ope_profit / $tani), $keta);
$p1_s_ope_profit         = number_format(($p1_s_ope_profit / $tani), $keta);
$def_s_ope_profit        = number_format(($def_s_ope_profit / $tani), $keta);
$s_gyoumu                = number_format(($s_gyoumu / $tani), $keta);
$p1_s_gyoumu             = number_format(($p1_s_gyoumu / $tani), $keta);
$def_s_gyoumu            = number_format(($def_s_gyoumu / $tani), $keta);
$s_swari                 = number_format(($s_swari / $tani), $keta);
$p1_s_swari              = number_format(($p1_s_swari / $tani), $keta);
$def_s_swari             = number_format(($def_s_swari / $tani), $keta);
$s_pother                = number_format(($s_pother / $tani), $keta);
$p1_s_pother             = number_format(($p1_s_pother / $tani), $keta);
$def_s_pother            = number_format(($def_s_pother / $tani), $keta);
$s_nonope_profit_sum     = number_format(($s_nonope_profit_sum / $tani), $keta);
$p1_s_nonope_profit_sum  = number_format(($p1_s_nonope_profit_sum / $tani), $keta);
$def_s_nonope_profit_sum = number_format(($def_s_nonope_profit_sum / $tani), $keta);
$s_srisoku               = number_format(($s_srisoku / $tani), $keta);
$p1_s_srisoku            = number_format(($p1_s_srisoku / $tani), $keta);
$def_s_srisoku           = number_format(($def_s_srisoku / $tani), $keta);
$s_lother                = number_format(($s_lother / $tani), $keta);
$p1_s_lother             = number_format(($p1_s_lother / $tani), $keta);
$def_s_lother            = number_format(($def_s_lother / $tani), $keta);
$s_nonope_loss_sum       = number_format(($s_nonope_loss_sum / $tani), $keta);
$p1_s_nonope_loss_sum    = number_format(($p1_s_nonope_loss_sum / $tani), $keta);
$def_s_nonope_loss_sum   = number_format(($def_s_nonope_loss_sum / $tani), $keta);
$s_current_profit        = number_format(($s_current_profit / $tani), $keta);
$p1_s_current_profit     = number_format(($p1_s_current_profit / $tani), $keta);
$def_s_current_profit    = number_format(($def_s_current_profit / $tani), $keta);

$b_uri                   = number_format(($b_uri / $tani), $keta);
$p1_b_uri                = number_format(($p1_b_uri / $tani), $keta);
$def_b_uri               = number_format(($def_b_uri / $tani), $keta);
$b_invent                = number_format(($b_invent / $tani), $keta);
$p1_b_invent             = number_format(($p1_b_invent / $tani), $keta);
$def_b_invent            = number_format(($def_b_invent / $tani), $keta);
$b_metarial              = number_format(($b_metarial / $tani), $keta);
$p1_b_metarial           = number_format(($p1_b_metarial / $tani), $keta);
$def_b_metarial          = number_format(($def_b_metarial / $tani), $keta);
$b_roumu                 = number_format(($b_roumu / $tani), $keta);
$p1_b_roumu              = number_format(($p1_b_roumu / $tani), $keta);
$def_b_roumu             = number_format(($def_b_roumu / $tani), $keta);
$b_expense               = number_format(($b_expense / $tani), $keta);
$p1_b_expense            = number_format(($p1_b_expense / $tani), $keta);
$def_b_expense           = number_format(($def_b_expense / $tani), $keta);
$b_endinv                = number_format(($b_endinv / $tani), $keta);
$p1_b_endinv             = number_format(($p1_b_endinv / $tani), $keta);
$def_b_endinv            = number_format(($def_b_endinv / $tani), $keta);
$b_urigen                = number_format(($b_urigen / $tani), $keta);
$p1_b_urigen             = number_format(($p1_b_urigen / $tani), $keta);
$def_b_urigen            = number_format(($def_b_urigen / $tani), $keta);
$b_gross_profit          = number_format(($b_gross_profit / $tani), $keta);
$p1_b_gross_profit       = number_format(($p1_b_gross_profit / $tani), $keta);
$def_b_gross_profit      = number_format(($def_b_gross_profit / $tani), $keta);
$b_han_jin               = number_format(($b_han_jin / $tani), $keta);
$p1_b_han_jin            = number_format(($p1_b_han_jin / $tani), $keta);
$def_b_han_jin           = number_format(($def_b_han_jin / $tani), $keta);
$b_han_kei               = number_format(($b_han_kei / $tani), $keta);
$p1_b_han_kei            = number_format(($p1_b_han_kei / $tani), $keta);
$def_b_han_kei           = number_format(($def_b_han_kei / $tani), $keta);
$b_han_all               = number_format(($b_han_all / $tani), $keta);
$p1_b_han_all            = number_format(($p1_b_han_all / $tani), $keta);
$def_b_han_all           = number_format(($def_b_han_all / $tani), $keta);
$b_ope_profit            = number_format(($b_ope_profit / $tani), $keta);
$p1_b_ope_profit         = number_format(($p1_b_ope_profit / $tani), $keta);
$def_b_ope_profit        = number_format(($def_b_ope_profit / $tani), $keta);
$b_gyoumu                = number_format(($b_gyoumu / $tani), $keta);
$p1_b_gyoumu             = number_format(($p1_b_gyoumu / $tani), $keta);
$def_b_gyoumu            = number_format(($def_b_gyoumu / $tani), $keta);
$b_swari                 = number_format(($b_swari / $tani), $keta);
$p1_b_swari              = number_format(($p1_b_swari / $tani), $keta);
$def_b_swari             = number_format(($def_b_swari / $tani), $keta);
$b_pother                = number_format(($b_pother / $tani), $keta);
$p1_b_pother             = number_format(($p1_b_pother / $tani), $keta);
$def_b_pother            = number_format(($def_b_pother / $tani), $keta);
$b_nonope_profit_sum     = number_format(($b_nonope_profit_sum / $tani), $keta);
$p1_b_nonope_profit_sum  = number_format(($p1_b_nonope_profit_sum / $tani), $keta);
$def_b_nonope_profit_sum = number_format(($def_b_nonope_profit_sum / $tani), $keta);
$b_srisoku               = number_format(($b_srisoku / $tani), $keta);
$p1_b_srisoku            = number_format(($p1_b_srisoku / $tani), $keta);
$def_b_srisoku           = number_format(($def_b_srisoku / $tani), $keta);
$b_lother                = number_format(($b_lother / $tani), $keta);
$p1_b_lother             = number_format(($p1_b_lother / $tani), $keta);
$def_b_lother            = number_format(($def_b_lother / $tani), $keta);
$b_nonope_loss_sum       = number_format(($b_nonope_loss_sum / $tani), $keta);
$p1_b_nonope_loss_sum    = number_format(($p1_b_nonope_loss_sum / $tani), $keta);
$def_b_nonope_loss_sum   = number_format(($def_b_nonope_loss_sum / $tani), $keta);
$b_current_profit        = number_format(($b_current_profit / $tani), $keta);
$p1_b_current_profit     = number_format(($p1_b_current_profit / $tani), $keta);
$def_b_current_profit    = number_format(($def_b_current_profit / $tani), $keta);

$all_uri                       = number_format(($all_uri / $tani), $keta);
$p1_all_uri                    = number_format(($p1_all_uri / $tani), $keta);
$def_all_uri                   = number_format(($def_all_uri / $tani), $keta);
$all_invent                    = number_format(($all_invent / $tani), $keta);
$p1_all_invent                 = number_format(($p1_all_invent / $tani), $keta);
$def_all_invent                = number_format(($def_all_invent / $tani), $keta);
$all_metarial                  = number_format(($all_metarial / $tani), $keta);
$p1_all_metarial               = number_format(($p1_all_metarial / $tani), $keta);
$def_all_metarial              = number_format(($def_all_metarial / $tani), $keta);
$all_roumu                     = number_format(($all_roumu / $tani), $keta);
$p1_all_roumu                  = number_format(($p1_all_roumu / $tani), $keta);
$def_all_roumu                 = number_format(($def_all_roumu / $tani), $keta);
$all_expense                   = number_format(($all_expense / $tani), $keta);
$p1_all_expense                = number_format(($p1_all_expense / $tani), $keta);
$def_all_expense               = number_format(($def_all_expense / $tani), $keta);
$all_endinv                    = number_format(($all_endinv / $tani), $keta);
$p1_all_endinv                 = number_format(($p1_all_endinv / $tani), $keta);
$def_all_endinv                = number_format(($def_all_endinv / $tani), $keta);
$all_urigen                    = number_format(($all_urigen / $tani), $keta);
$p1_all_urigen                 = number_format(($p1_all_urigen / $tani), $keta);
$def_all_urigen                = number_format(($def_all_urigen / $tani), $keta);
$all_gross_profit              = number_format(($all_gross_profit / $tani), $keta);
$p1_all_gross_profit           = number_format(($p1_all_gross_profit / $tani), $keta);
$def_all_gross_profit          = number_format(($def_all_gross_profit / $tani), $keta);
$all_han_jin                   = number_format(($all_han_jin / $tani), $keta);
$p1_all_han_jin                = number_format(($p1_all_han_jin / $tani), $keta);
$def_all_han_jin               = number_format(($def_all_han_jin / $tani), $keta);
$all_han_kei                   = number_format(($all_han_kei / $tani), $keta);
$p1_all_han_kei                = number_format(($p1_all_han_kei / $tani), $keta);
$def_all_han_kei               = number_format(($def_all_han_kei / $tani), $keta);
$all_han_all                   = number_format(($all_han_all / $tani), $keta);
$p1_all_han_all                = number_format(($p1_all_han_all / $tani), $keta);
$def_all_han_all               = number_format(($def_all_han_all / $tani), $keta);
$all_ope_profit                = number_format(($all_ope_profit / $tani), $keta);
$p1_all_ope_profit             = number_format(($p1_all_ope_profit / $tani), $keta);
$def_all_ope_profit            = number_format(($def_all_ope_profit / $tani), $keta);
$all_gyoumu                    = number_format(($all_gyoumu / $tani), $keta);
$p1_all_gyoumu                 = number_format(($p1_all_gyoumu / $tani), $keta);
$def_all_gyoumu                = number_format(($def_all_gyoumu / $tani), $keta);
$all_swari                     = number_format(($all_swari / $tani), $keta);
$p1_all_swari                  = number_format(($p1_all_swari / $tani), $keta);
$def_all_swari                 = number_format(($def_all_swari / $tani), $keta);
$all_pother                    = number_format(($all_pother / $tani), $keta);
$p1_all_pother                 = number_format(($p1_all_pother / $tani), $keta);
$def_all_pother                = number_format(($def_all_pother / $tani), $keta);
$all_nonope_profit_sum         = number_format(($all_nonope_profit_sum / $tani), $keta);
$p1_all_nonope_profit_sum      = number_format(($p1_all_nonope_profit_sum / $tani), $keta);
$def_all_nonope_profit_sum     = number_format(($def_all_nonope_profit_sum / $tani), $keta);
$all_srisoku                   = number_format(($all_srisoku / $tani), $keta);
$p1_all_srisoku                = number_format(($p1_all_srisoku / $tani), $keta);
$def_all_srisoku               = number_format(($def_all_srisoku / $tani), $keta);
$all_lother                    = number_format(($all_lother / $tani), $keta);
$p1_all_lother                 = number_format(($p1_all_lother / $tani), $keta);
$def_all_lother                = number_format(($def_all_lother / $tani), $keta);
$all_nonope_loss_sum           = number_format(($all_nonope_loss_sum / $tani), $keta);
$p1_all_nonope_loss_sum        = number_format(($p1_all_nonope_loss_sum / $tani), $keta);
$def_all_nonope_loss_sum       = number_format(($def_all_nonope_loss_sum / $tani), $keta);
$all_current_profit            = number_format(($all_current_profit / $tani), $keta);
$p1_all_current_profit         = number_format(($p1_all_current_profit / $tani), $keta);
$def_all_current_profit        = number_format(($def_all_current_profit / $tani), $keta);
$all_special_profit            = number_format(($all_special_profit / $tani), $keta);
$p1_all_special_profit         = number_format(($p1_all_special_profit / $tani), $keta);
$def_all_special_profit        = number_format(($def_all_special_profit / $tani), $keta);
$all_special_loss              = number_format(($all_special_loss / $tani), $keta);
$p1_all_special_loss           = number_format(($p1_all_special_loss / $tani), $keta);
$def_all_special_loss          = number_format(($def_all_special_loss / $tani), $keta);
$all_before_tax_net_profit     = number_format(($all_before_tax_net_profit / $tani), $keta);
$p1_all_before_tax_net_profit  = number_format(($p1_all_before_tax_net_profit / $tani), $keta);
$def_all_before_tax_net_profit = number_format(($def_all_before_tax_net_profit / $tani), $keta);

////////// 特記事項の取得
$query = sprintf("select comment from act_comment_history where pl_bs_ym=%d and item='カプラ損益計算書'", $yyyymm);
if (getUniResult($query,$comment_c) <= 0) {
    $comment_c = "";
}
if ($yyyymm >= 201604) {
    $query = sprintf("select comment from act_comment_history where pl_bs_ym=%d and item='リニア標準損益計算書'", $yyyymm);
    if (getUniResult($query,$comment_l) <= 0) {
        $comment_l = "";
    }
} else {
    $query = sprintf("select comment from act_comment_history where pl_bs_ym=%d and item='リニア損益計算書'", $yyyymm);
    if (getUniResult($query,$comment_l) <= 0) {
        $comment_l = "";
    }
}
if ($yyyymm >= 201604) {
    $query = sprintf("select comment from act_comment_history where pl_bs_ym=%d and item='機工損益計算書'", $yyyymm);
    if (getUniResult($query,$comment_t) <= 0) {
        $comment_t = "";
    }
} else {
    $comment_t = "";
}
$query = sprintf("select comment from act_comment_history where pl_bs_ym=%d and item='試験・修理損益計算書'", $yyyymm);
if (getUniResult($query,$comment_s) <= 0) {
    $comment_s = "";
}

$query = sprintf("select comment from act_comment_history where pl_bs_ym=%d and item='商品管理損益計算書'", $yyyymm);
if (getUniResult($query,$comment_b) <= 0) {
    $comment_b = "";
}
$query = sprintf("select comment from act_comment_history where pl_bs_ym=%d and item='全体損益計算書'", $yyyymm);
if (getUniResult($query,$comment_all) <= 0) {
    $comment_all = "";
}
$query = sprintf("select comment from act_comment_history where pl_bs_ym=%d and item='その他損益計算書'", $yyyymm);
if (getUniResult($query,$comment_other) <= 0) {
    $comment_other = "";
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
                    <td colspan='3' align='center' class='pt10b' bgcolor='#ffffc6'>カ　プ　ラ</td>
                    <td colspan='3' align='center' class='pt10b' bgcolor='#ffffc6'>リ　ニ　ア</td>
                    <td colspan='3' align='center' class='pt10b' bgcolor='#ffffc6'>ツ　ー　ル</td>
                    <td colspan='3' align='center' class='pt10b' bgcolor='#ffffc6'>試験・修理</td>
                    <td colspan='3' align='center' class='pt10b' bgcolor='#ffffc6'>商品管理</td>
                    <td colspan='3' align='center' class='pt10b' bgcolor='#ffffc6'>合　　　計</td>
                    <td rowspan='2' width='400' align='left' class='pt10b' bgcolor='#ffffc6'>製造間接経費・販管費の配賦基準</td>
                </tr>
                <tr>
                    <td nowrap align='center' class='pt10b' bgcolor='#d6d3ce'><?php echo $p1_ym ?> </td>
                    <td nowrap align='center' class='pt10b' bgcolor='#ffffc6'><?php echo $yyyymm ?></td>
                    <td nowrap align='center' class='pt10b' bgcolor='#ffffc6'>差　額</td>
                    <td nowrap align='center' class='pt10b' bgcolor='#d6d3ce'><?php echo $p1_ym ?> </td>
                    <td nowrap align='center' class='pt10b' bgcolor='#ffffc6'><?php echo $yyyymm ?></td>
                    <td nowrap align='center' class='pt10b' bgcolor='#ffffc6'>差　額</td>
                    <td nowrap align='center' class='pt10b' bgcolor='#d6d3ce'><?php echo $p1_ym ?> </td>
                    <td nowrap align='center' class='pt10b' bgcolor='#ffffc6'><?php echo $yyyymm ?></td>
                    <td nowrap align='center' class='pt10b' bgcolor='#ffffc6'>差　額</td>
                    <td nowrap align='center' class='pt10b' bgcolor='#d6d3ce'><?php echo $p1_ym ?> </td>
                    <td nowrap align='center' class='pt10b' bgcolor='#ffffc6'><?php echo $yyyymm ?></td>
                    <td nowrap align='center' class='pt10b' bgcolor='#ffffc6'>差　額</td>
                    <td nowrap align='center' class='pt10b' bgcolor='#d6d3ce'><?php echo $p1_ym ?> </td>
                    <td nowrap align='center' class='pt10b' bgcolor='#ffffc6'><?php echo $yyyymm ?></td>
                    <td nowrap align='center' class='pt10b' bgcolor='#ffffc6'>差　額</td>
                    <td nowrap align='center' class='pt10b' bgcolor='#d6d3ce'><?php echo $p1_ym ?> </td>
                    <td nowrap align='center' class='pt10b' bgcolor='#ffffc6'><?php echo $yyyymm ?></td>
                    <td nowrap align='center' class='pt10b' bgcolor='#ffffc6'>差　額</td>
                </tr>
                <tr>
                    <td rowspan='11' width='10' align='center' valign='middle' class='pt10b' bgcolor='#ceffce'>営　業　損　益</td>
                    <td colspan='2' nowrap align='center' class='pt10b' bgcolor='#ceffce'>売　上　高</td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo $p1_c_uri ?>   </td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo $c_uri ?>      </td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo $def_c_uri ?>  </td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo $p1_l_uri ?>   </td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo $l_uri ?>      </td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo $def_l_uri ?>  </td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo $p1_t_uri ?>   </td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo $t_uri ?>      </td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo $def_t_uri ?>  </td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo $p1_s_uri ?>   </td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo $s_uri ?>      </td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo $def_s_uri ?>  </td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo $p1_b_uri ?>   </td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo $b_uri ?>      </td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo $def_b_uri ?>  </td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo $p1_all_uri ?> </td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo $all_uri ?>    </td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo $def_all_uri ?></td>
                    <td nowrap align='left'  class='pt10' bgcolor='#d6d3ce'>実際売上高</td>
                </tr>
                <tr>
                    <td rowspan='6' width='10' align='center' valign='middle' class='pt10b' bgcolor='#ffffc6'>売上原価</td> <!-- 売上原価 -->
                    <td nowrap align='left' class='pt10b' bgcolor='white'>　期首材料仕掛品棚卸高</td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $p1_c_invent ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $c_invent ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $def_c_invent ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $p1_l_invent ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $l_invent ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $def_l_invent ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $p1_t_invent ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $t_invent ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $def_t_invent ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $p1_s_invent ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $s_invent ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $def_s_invent ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $p1_b_invent ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $b_invent ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $def_b_invent ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $p1_all_invent ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $all_invent ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $def_all_invent ?></td>
                    <td nowrap align='left'  class='pt10'>総平均単価による棚卸高</td>
                </tr>
                <tr>
                    <td nowrap align='left' class='pt10b' bgcolor='#e6e6e6'>　材料費(仕入高)</td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo $p1_c_metarial ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo $c_metarial ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo $def_c_metarial ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo $p1_l_metarial ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo $l_metarial ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo $def_l_metarial ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo $p1_t_metarial ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo $t_metarial ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo $def_t_metarial ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo $p1_s_metarial ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo $s_metarial ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo $def_s_metarial ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo $p1_b_metarial ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo $b_metarial ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo $def_b_metarial ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo $p1_all_metarial ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo $all_metarial ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo $def_all_metarial ?></td>
                    <td nowrap align='left'  class='pt10'>買掛購入高比</td>
                </tr>
                <tr>
                    <td nowrap align='left' class='pt10b' bgcolor='white'>　労　　務　　費</td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $p1_c_roumu ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $c_roumu ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $def_c_roumu ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $p1_l_roumu ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $l_roumu ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $def_l_roumu ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $p1_t_roumu ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $t_roumu ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $def_t_roumu ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $p1_s_roumu ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $s_roumu ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $def_s_roumu ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $p1_b_roumu ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $b_roumu ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $def_b_roumu ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $p1_all_roumu ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $all_roumu ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $def_all_roumu ?></td>
                    <td nowrap align='left'  class='pt10'>ＣＬサービス割合比</td>
                </tr>
                <tr>
                    <td nowrap align='left' class='pt10b' bgcolor='#e6e6e6'>　経　　　　　費</td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo $p1_c_expense ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo $c_expense ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo $def_c_expense ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo $p1_l_expense ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo $l_expense ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo $def_l_expense ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo $p1_t_expense ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo $t_expense ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo $def_t_expense ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo $p1_s_expense ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo $s_expense ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo $def_s_expense ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo $p1_b_expense ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo $b_expense ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo $def_b_expense ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo $p1_all_expense ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo $all_expense ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo $def_all_expense ?></td>
                    <td nowrap align='left'  class='pt10'>ＣＬ直接経費合計比率</td>
                </tr>
                <tr>
                    <td nowrap align='left' class='pt10b' bgcolor='white'>　期末材料仕掛品棚卸高</td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $p1_c_endinv ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $c_endinv ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $def_c_endinv ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $p1_l_endinv ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $l_endinv ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $def_l_endinv ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $p1_t_endinv ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $t_endinv ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $def_t_endinv ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $p1_s_endinv ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $s_endinv ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $def_s_endinv ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $p1_b_endinv ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $b_endinv ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $def_b_endinv ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $p1_all_endinv ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $all_endinv ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $def_all_endinv ?></td>
                    <td nowrap align='left'  class='pt10'>総平均単価による棚卸高</td>
                </tr>
                <tr>
                    <td nowrap align='left' class='pt10b' bgcolor='#ffffc6'>　売　上　原　価</td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo $p1_c_urigen ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo $c_urigen ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo $def_c_urigen ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo $p1_l_urigen ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo $l_urigen ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo $def_l_urigen ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo $p1_t_urigen ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo $t_urigen ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo $def_t_urigen ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo $p1_s_urigen ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo $s_urigen ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo $def_s_urigen ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo $p1_b_urigen ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo $b_urigen ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo $def_b_urigen ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo $p1_all_urigen ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo $all_urigen ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo $def_all_urigen ?></td>
                    <td nowrap align='left'  class='pt10'>ＣＬ直接経費合計比率</td>
                </tr>
                <tr>
                    <td colspan='2' nowrap align='center' class='pt10b' bgcolor='#ceffce'>売　上　総　利　益</td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo $p1_c_gross_profit ?></td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo $c_gross_profit ?></td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo $def_c_gross_profit ?></td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo $p1_l_gross_profit ?></td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo $l_gross_profit ?></td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo $def_l_gross_profit ?></td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo $p1_t_gross_profit ?></td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo $t_gross_profit ?></td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo $def_t_gross_profit ?></td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo $p1_s_gross_profit ?></td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo $s_gross_profit ?></td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo $def_s_gross_profit ?></td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo $p1_b_gross_profit ?></td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo $b_gross_profit ?></td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo $def_b_gross_profit ?></td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo $p1_all_gross_profit ?></td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo $all_gross_profit ?></td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo $def_all_gross_profit ?></td>
                    <td nowrap align='left'  class='pt10' bgcolor='#d6d3ce'>　</td>  <!-- 余白 -->
                </tr>
                <tr>
                    <td rowspan='3' width='10' align='center' valign='middle' class='pt10b' bgcolor='#ffffc6'></td> <!-- 販管費 -->
                    <td nowrap align='left' class='pt10b' bgcolor='#e6e6e6'>　人　　件　　費</td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo $p1_c_han_jin ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo $c_han_jin ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo $def_c_han_jin ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo $p1_l_han_jin ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo $l_han_jin ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo $def_l_han_jin ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo $p1_t_han_jin ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo $t_han_jin ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo $def_t_han_jin ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo $p1_s_han_jin ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo $s_han_jin ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo $def_s_han_jin ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo $p1_b_han_jin ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo $b_han_jin ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo $def_b_han_jin ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo $p1_all_han_jin ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo $all_han_jin ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo $def_all_han_jin ?></td>
                    <td nowrap align='left'  class='pt10'>ＣＬ直接給料比率</td>
                </tr>
                <tr>
                    <td nowrap align='left' class='pt10b' bgcolor='white'>　経　　　　　費</td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $p1_c_han_kei ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $c_han_kei ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $def_c_han_kei ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $p1_l_han_kei ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $l_han_kei ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $def_l_han_kei ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $p1_t_han_kei ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $t_han_kei ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $def_t_han_kei ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $p1_s_han_kei ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $s_han_kei ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $def_s_han_kei ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $p1_b_han_kei ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $b_han_kei ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $def_b_han_kei ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $p1_all_han_kei ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $all_han_kei ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $def_all_han_kei ?></td>
                    <td nowrap align='left'  class='pt10'>ＣＬ占有面積比・ＣＬ直接経費合計比率他</td>
                </tr>
                <tr>
                    <td nowrap align='center' class='pt10b' bgcolor='#ffffc6'>販管費及び一般管理費計</td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo $p1_c_han_all ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo $c_han_all ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo $def_c_han_all ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo $p1_l_han_all ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo $l_han_all ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo $def_l_han_all ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo $p1_t_han_all ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo $t_han_all ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo $def_t_han_all ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo $p1_s_han_all ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo $s_han_all ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo $def_s_han_all ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo $p1_b_han_all ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo $b_han_all ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo $def_b_han_all ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo $p1_all_han_all ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo $all_han_all ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo $def_all_han_all ?></td>
                    <td nowrap align='left'  class='pt10'>　</td>  <!-- 余白 -->
                </tr>
                <tr>
                    <td colspan='3' nowrap align='center' class='pt10b' bgcolor='#ceffce'>営　　業　　利　　益</td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo $p1_c_ope_profit ?></td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo $c_ope_profit ?></td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo $def_c_ope_profit ?></td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo $p1_l_ope_profit ?></td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo $l_ope_profit ?></td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo $def_l_ope_profit ?></td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo $p1_t_ope_profit ?></td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo $t_ope_profit ?></td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo $def_t_ope_profit ?></td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo $p1_s_ope_profit ?></td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo $s_ope_profit ?></td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo $def_s_ope_profit ?></td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo $p1_b_ope_profit ?></td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo $b_ope_profit ?></td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo $def_b_ope_profit ?></td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo $p1_all_ope_profit ?></td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo $all_ope_profit ?></td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo $def_all_ope_profit ?></td>
                    <td nowrap align='left'  class='pt10'>　</td>  <!-- 余白 -->
                </tr>
                <tr>
                    <td rowspan='7' align='center' valign='middle' class='pt10b' bgcolor='#ceffce'>営業外損益</td>
                    <td rowspan='4' align='center' class='pt10' bgcolor='#ffffc6'></td> <!-- 余白 -->
                    <td nowrap align='left' class='pt10b' bgcolor='white'>　業務委託収入</td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $p1_c_gyoumu ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $c_gyoumu ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $def_c_gyoumu ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $p1_l_gyoumu ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $l_gyoumu ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $def_l_gyoumu ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $p1_t_gyoumu ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $t_gyoumu ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $def_t_gyoumu ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $p1_s_gyoumu ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $s_gyoumu ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $def_s_gyoumu ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $p1_b_gyoumu ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $b_gyoumu ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $def_b_gyoumu ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $p1_all_gyoumu ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $all_gyoumu ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $def_all_gyoumu ?></td>
                    <td nowrap align='left'  class='pt10'>前期実績の売上高比</td>
                </tr>
                <tr>
                    <td nowrap align='left' class='pt10b' bgcolor='#e6e6e6'>　仕　入　割　引</td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo $p1_c_swari ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo $c_swari ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo $def_c_swari ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo $p1_l_swari ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo $l_swari ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo $def_l_swari ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo $p1_t_swari ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo $t_swari ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo $def_t_swari ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo $p1_s_swari ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo $s_swari ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo $def_s_swari ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo $p1_b_swari ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo $b_swari ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo $def_b_swari ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo $p1_all_swari ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo $all_swari ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo $def_all_swari ?></td>
                    <td nowrap align='left'  class='pt10'>前期実績の売上高比</td>
                </tr>
                <tr>
                    <td nowrap align='left' class='pt10b' bgcolor='white'>　そ　　の　　他</td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $p1_c_pother ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $c_pother ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $def_c_pother ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $p1_l_pother ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $l_pother ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $def_l_pother ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $p1_t_pother ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $t_pother ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $def_t_pother ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $p1_s_pother ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $s_pother ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $def_s_pother ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $p1_b_pother ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $b_pother ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $def_b_pother ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $p1_all_pother ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $all_pother ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $def_all_pother ?></td>
                    <td nowrap align='left'  class='pt10'>前期実績の売上高比</td>
                </tr>
                <tr>
                    <td nowrap align='left' class='pt10b' bgcolor='#ffffc6'>　営業外収益 計</td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo $p1_c_nonope_profit_sum ?>   </td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo $c_nonope_profit_sum ?>      </td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo $def_c_nonope_profit_sum ?>  </td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo $p1_l_nonope_profit_sum ?>   </td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo $l_nonope_profit_sum ?>      </td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo $def_l_nonope_profit_sum ?>  </td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo $p1_t_nonope_profit_sum ?>   </td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo $t_nonope_profit_sum ?>      </td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo $def_t_nonope_profit_sum ?>  </td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo $p1_s_nonope_profit_sum ?>   </td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo $s_nonope_profit_sum ?>      </td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo $def_s_nonope_profit_sum ?>  </td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo $p1_b_nonope_profit_sum ?>   </td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo $b_nonope_profit_sum ?>      </td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo $def_b_nonope_profit_sum ?>  </td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo $p1_all_nonope_profit_sum ?> </td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo $all_nonope_profit_sum ?>    </td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo $def_all_nonope_profit_sum ?></td>
                    <td nowrap align='left'  class='pt10'>　</td> <!-- 余白 -->
                </tr>
                <tr>
                    <td rowspan='3' align='center' class='pt10' bgcolor='#ffffc6'></td> <!-- 余白 -->
                    <td nowrap align='left' class='pt10b' bgcolor='#e6e6e6'>　支　払　利　息</td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo $p1_c_srisoku ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo $c_srisoku ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo $def_c_srisoku ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo $p1_l_srisoku ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo $l_srisoku ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo $def_l_srisoku ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo $p1_t_srisoku ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo $t_srisoku ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo $def_t_srisoku ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo $p1_s_srisoku ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo $s_srisoku ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo $def_s_srisoku ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo $p1_b_srisoku ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo $b_srisoku ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo $def_b_srisoku ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo $p1_all_srisoku ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo $all_srisoku ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo $def_all_srisoku ?></td>
                    <td nowrap align='left'  class='pt10'>前期実績の売上高比</td>
                </tr>
                <tr>
                    <td nowrap align='left' class='pt10b' bgcolor='white'>　そ　　の　　他</td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $p1_c_lother ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $c_lother ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $def_c_lother ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $p1_l_lother ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $l_lother ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $def_l_lother ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $p1_t_lother ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $t_lother ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $def_t_lother ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $p1_s_lother ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $s_lother ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $def_s_lother ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $p1_b_lother ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $b_lother ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $def_b_lother ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $p1_all_lother ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $all_lother ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $def_all_lother ?></td>
                    <td nowrap align='left'  class='pt10'>前期実績の売上高比</td>
                </tr>
                <tr>
                    <td nowrap align='left' class='pt10b' bgcolor='#ffffc6'>　営業外費用 計</td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo $p1_c_nonope_loss_sum ?>   </td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo $c_nonope_loss_sum ?>      </td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo $def_c_nonope_loss_sum ?>  </td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo $p1_l_nonope_loss_sum ?>   </td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo $l_nonope_loss_sum ?>      </td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo $def_l_nonope_loss_sum ?>  </td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo $p1_t_nonope_loss_sum ?>   </td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo $t_nonope_loss_sum ?>      </td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo $def_t_nonope_loss_sum ?>  </td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo $p1_s_nonope_loss_sum ?>   </td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo $s_nonope_loss_sum ?>      </td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo $def_s_nonope_loss_sum ?>  </td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo $p1_b_nonope_loss_sum ?>   </td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo $b_nonope_loss_sum ?>      </td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo $def_b_nonope_loss_sum ?>  </td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo $p1_all_nonope_loss_sum ?> </td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo $all_nonope_loss_sum ?>    </td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo $def_all_nonope_loss_sum ?></td>
                    <td nowrap align='left'  class='pt10'>　</td> <!-- 余白 -->
                </tr>
                <tr>
                    <td colspan='3' nowrap align='center' class='pt10b' bgcolor='#ceffce'>経　　常　　利　　益</td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo $p1_c_current_profit ?>   </td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo $c_current_profit ?>      </td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo $def_c_current_profit ?>  </td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo $p1_l_current_profit ?>   </td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo $l_current_profit ?>      </td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo $def_l_current_profit ?>  </td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo $p1_t_current_profit ?>   </td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo $t_current_profit ?>      </td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo $def_t_current_profit ?>  </td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo $p1_s_current_profit ?>   </td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo $s_current_profit ?>      </td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo $def_s_current_profit ?>  </td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo $p1_b_current_profit ?>   </td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo $b_current_profit ?>      </td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo $def_b_current_profit ?>  </td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo $p1_all_current_profit ?> </td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo $all_current_profit ?>    </td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo $def_all_current_profit ?></td>
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
                    <td colspan='23' bgcolor='white' align='left' class='pt10b'><a href='<?=$menu->out_action('特記事項入力')?>?<?php echo uniqid('menu') ?>' style='text-decoration:none; color:black;'>　※　月次損益特記事項</a></td>
                </tr>
                <tr>
                    <td colspan='23' bgcolor='white' class='pt10'>
                        <ol>
                        <?php
                            if ($comment_c != "") {
                                echo "<li><pre>$comment_c</pre></li>\n";
                            }
                            if ($comment_l != "") {
                                echo "<li><pre>$comment_l</pre></li>\n";
                            }
                            if ($comment_s != "") {
                                echo "<li><pre>$comment_s</pre></li>\n";
                            }
                            if ($comment_t != "") {
                                echo "<li><pre>$comment_t</pre></li>\n";
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
