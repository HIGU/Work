<?php
//////////////////////////////////////////////////////////////////////////////
// 月次損益関係 月次 カプラ特注・標準 損益計算書                            //
// Copyright (C) 2009-2018 Norihisa.Ohya norihisa_ooya@nitto-kohki.co.jp    //
// Changed history                                                          //
// 2009/08/21 Created   profit_loss_pl_act_ctoku.php                        //
// 2009/10/07 商管の売上調整の際カプラ全体からマイナスするよう変更          //
// 2009/10/15 売上高・売上総利益・営業利益・経常利益を太字に変更            //
// 2009/12/10 段落を調整                                                    //
// 2010/01/15 200912分試修の労務費調整のためこちらにも調整埋め込み          //
// 2010/01/19 200912度の業務委託収入とその他を調整（1月度戻しの分も）       //
// 2010/02/04 2010/01度より営業外に再計算した値を適用                       //
// 2010/02/08 201001度から配賦した労務費を加味するように変更           大谷 //
// 2010/10/08 グラフ作成用のデータ登録を追加                           大谷 //
// 2011/07/14 データ登録で労務費と経費のデータが同じだったのを修正     大谷 //
// 2013/11/07 2013年10月 商管業務委託費 調整                                //
//            カプラ材料費 -1,245,035円、商管製造経費 +1,245,035円     大谷 //
//             ※ 横川派遣料 11月に逆調整を行うこと                         //
// 2013/11/07 2013年11月 商管業務委託費 調整                                //
//            カプラ材料費 +1,245,035円、商管製造経費 -1,245,035円     大谷 //
// 2014/09/04 商管の製造経費労務費を各セグメント配賦の為調整           大谷 //
// 2018/10/10 2018/09固定資産訂正分はすべてカプラ標準なので調整        大谷 //
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

////////////// サイト設定
// $menu->set_site(10, 7);                     // site_index=10(損益メニュー) site_id=7(月次損益)
//////////// 表題の設定
$menu->set_caption('栃木日東工器(株)');
//////////// 呼出先のaction名とアドレス設定
$menu->set_action('特記事項入力',   PL . 'profit_loss_comment_put_ctoku.php');

///// 期・月の取得
$ki = Ym_to_tnk($_SESSION['pl_ym']);
$tuki = substr($_SESSION['pl_ym'],4,2);

//////////// タイトル名(ソースのタイトル名とフォームのタイトル名)
$menu->set_title("第 {$ki} 期　{$tuki} 月度　カプラ特注・標準 商 品 別 損 益 計 算 書");

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

///// 表示単位を設定取得
if (isset($_POST['keihi_tani'])) {
    $_SESSION['keihi_tani'] = $_POST['keihi_tani'];
    $tani = $_SESSION['keihi_tani'];
} elseif (isset($_SESSION['keihi_tani'])) {
    $tani = $_SESSION['keihi_tani'];
} else {
    $tani = 1000;        // 初期値 表示単位 千円
    $_SESSION['keihi_tani'] = $tani;
}
///// 表示 小数部桁数 設定取得
if (isset($_POST['keihi_keta'])) {
    $_SESSION['keihi_keta'] = $_POST['keihi_keta'];
    $keta = $_SESSION['keihi_keta'];
} elseif (isset($_SESSION['keihi_keta'])) {
    $keta = $_SESSION['keihi_keta'];
} else {
    $keta = 0;          // 初期値 小数点以下桁数
    $_SESSION['keihi_keta'] = $keta;
}

/********** 売上高 **********/
    ///// 当月
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='カプラ特注売上高'", $yyyymm);
if (getUniResult($query, $ctoku_uri) < 1) {
    $ctoku_uri        = 0;     // 検索失敗
    $ctoku_uri_sagaku = 0;
} else {
    if ($yyyymm == 201801) {
        $ctoku_uri = $ctoku_uri - 7880000;
    }
    if ($yyyymm == 201802) {
        $ctoku_uri = $ctoku_uri + 7880000;
    }
    $ctoku_uri_sagaku = $ctoku_uri;
    $ctoku_uri        = number_format(($ctoku_uri / $tani), $keta);
}
if ( $yyyymm >= 200911) {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='カプラ試修売上高'", $yyyymm);
    if (getUniResult($query, $sc_uri) < 1) {
        $sc_uri        = 0;    // 検索失敗
        $sc_uri_sagaku = 0;
        $sc_uri_temp   = 0;
    } else {
        $sc_uri_temp   = $sc_uri;
        $sc_uri_sagaku = $sc_uri;
        $sc_uri        = number_format(($sc_uri / $tani), $keta);
    }
} else{
    $sc_uri            = 0;    // 検索失敗
    $sc_uri_sagaku     = 0;
    $sc_uri_temp       = 0;
}
if ( $yyyymm >= 200910) {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='カプラ売上高'", $yyyymm);
    if (getUniResult($query, $c_uri) < 1) {
        $c_uri         = 0;    // 検索失敗
        $ch_uri        = 0;
        $ch_uri_sagaku = 0;
    }
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='商管売上調整額'", $yyyymm);
    if (getUniResult($query, $b_uri_cho) < 1) {
        $b_uri_cho     = 0;                                 // 検索失敗
        $c_uri         = $c_uri - $sc_uri_sagaku;           //カプラ試験修理を加味
        $ch_uri        = $c_uri - $ctoku_uri_sagaku;
        $ch_uri_sagaku = $ch_uri;
        $ch_uri        = number_format(($ch_uri / $tani), $keta);
        $c_uri         = number_format(($c_uri / $tani), $keta);
    } else {
        $c_uri         = $c_uri - $sc_uri_sagaku;
        $ch_uri        = $c_uri - $ctoku_uri_sagaku;
        $ch_uri_sagaku = $ch_uri;
        $ch_uri        = number_format(($ch_uri / $tani), $keta);
        $c_uri         = number_format(($c_uri / $tani), $keta);
    }
} else {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='カプラ売上高'", $yyyymm);
    if (getUniResult($query, $c_uri) < 1) {
        $c_uri         = 0;     // 検索失敗
        $ch_uri        = 0;
        $ch_uri_sagaku = 0;
    } else {
        $ch_uri        = $c_uri - $ctoku_uri_sagaku;
        $ch_uri_sagaku = $ch_uri;
        $ch_uri        = number_format(($ch_uri / $tani), $keta);
        $c_uri         = number_format(($c_uri / $tani), $keta);
    }
}
    ///// 前月
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='カプラ特注売上高'", $p1_ym);
if (getUniResult($query, $p1_ctoku_uri) < 1) {
    $p1_ctoku_uri         = 0;     // 検索失敗
    $p1_ctoku_uri_sagaku  = 0;
} else {
    if ($p1_ym == 201801) {
        $p1_ctoku_uri = $p1_ctoku_uri - 7880000;
    }
    if ($p1_ym == 201802) {
        $p1_ctoku_uri = $p1_ctoku_uri + 7880000;
    }
    $p1_ctoku_uri_sagaku  = $p1_ctoku_uri;
    $p1_ctoku_uri         = number_format(($p1_ctoku_uri / $tani), $keta);
}
if ( $yyyymm >= 200911) {
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
} else{
    $p1_sc_uri            = 0;     // 検索失敗
    $p1_sc_uri_sagaku     = 0;
    $p1_sc_uri_temp       = 0;
}
if ( $yyyymm >= 200911) {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='カプラ売上高'", $p1_ym);
    if (getUniResult($query, $p1_c_uri) < 1) {
        $p1_c_uri         = 0;     // 検索失敗
        $p1_ch_uri        = 0;
        $p1_ch_uri_sagaku = 0;
    }
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='商管売上調整額'", $p1_ym);
    if (getUniResult($query, $p1_b_uri_cho) < 1) {
        $p1_b_uri_cho     = 0; // 検索失敗
        $p1_c_uri         = $p1_c_uri - $p1_sc_uri_sagaku;          //カプラ試験修理を加味
        $p1_ch_uri        = $p1_c_uri - $p1_ctoku_uri_sagaku;
        $p1_ch_uri_sagaku = $p1_ch_uri;
        $p1_ch_uri        = number_format(($p1_ch_uri / $tani), $keta);
        $p1_c_uri         = number_format(($p1_c_uri / $tani), $keta);
    } else {
        $p1_c_uri         = $p1_c_uri - $p1_sc_uri_sagaku;
        $p1_ch_uri        = $p1_c_uri - $p1_ctoku_uri_sagaku;
        $p1_ch_uri_sagaku = $p1_ch_uri;
        $p1_ch_uri        = number_format(($p1_ch_uri / $tani), $keta);
        $p1_c_uri         = number_format(($p1_c_uri / $tani), $keta);
    }
} else {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='カプラ売上高'", $p1_ym);
    if (getUniResult($query, $p1_c_uri) < 1) {
        $p1_c_uri         = 0;     // 検索失敗
        $p1_ch_uri        = 0;
        $p1_ch_uri_sagaku = 0;
    } else {
        $p1_ch_uri        = $p1_c_uri - $p1_ctoku_uri_sagaku;
        $p1_ch_uri_sagaku = $p1_ch_uri;
        $p1_ch_uri        = number_format(($p1_ch_uri / $tani), $keta);
        $p1_c_uri         = number_format(($p1_c_uri / $tani), $keta);
    }
}
    ///// 前前月
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='カプラ特注売上高'", $p2_ym);
if (getUniResult($query, $p2_ctoku_uri) < 1) {
    $p2_ctoku_uri         = 0;     // 検索失敗
    $p2_ctoku_uri_sagaku  = 0;
} else {
    if ($p2_ym == 201801) {
        $p2_ctoku_uri = $p2_ctoku_uri - 7880000;
    }
    if ($p2_ym == 201802) {
        $p2_ctoku_uri = $p2_ctoku_uri + 7880000;
    }
    $p2_ctoku_uri_sagaku  = $p2_ctoku_uri;
    $p2_ctoku_uri         = number_format(($p2_ctoku_uri / $tani), $keta);
}
if ( $yyyymm >= 200911) {
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
} else{
    $p2_sc_uri            = 0;     // 検索失敗
    $p2_sc_uri_sagaku     = 0;
    $p2_sc_uri_temp       = 0;
}
if ( $yyyymm >= 200912) {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='カプラ売上高'", $p2_ym);
    if (getUniResult($query, $p2_c_uri) < 1) {
        $p2_c_uri         = 0;     // 検索失敗
        $p2_ch_uri        = 0;
        $p2_ch_uri_sagaku = 0;
    }
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='商管売上調整額'", $p2_ym);
    if (getUniResult($query, $p2_b_uri_cho) < 1) {
        $p2_b_uri_cho     = 0; // 検索失敗
        $p2_c_uri         = $p2_c_uri - $p2_sc_uri_sagaku;          //カプラ試験修理を加味
        $p2_ch_uri        = $p2_c_uri - $p2_ctoku_uri_sagaku;
        $p2_ch_uri_sagaku = $p2_ch_uri;
        $p2_ch_uri        = number_format(($p1_ch_uri / $tani), $keta);
        $p2_c_uri         = number_format(($p1_c_uri / $tani), $keta);
    } else {
        $p2_c_uri         = $p2_c_uri - $p2_sc_uri_sagaku;
        $p2_ch_uri        = $p2_c_uri - $p2_ctoku_uri_sagaku;
        $p2_ch_uri_sagaku = $p2_ch_uri;
        $p2_ch_uri        = number_format(($p2_ch_uri / $tani), $keta);
        $p2_c_uri         = number_format(($p2_c_uri / $tani), $keta);
    }
} else {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='カプラ売上高'", $p2_ym);
    if (getUniResult($query, $p2_c_uri) < 1) {
        $p2_c_uri         = 0;     // 検索失敗
        $p2_ch_uri        = 0;
        $p2_ch_uri_sagaku = 0;
    } else {
        $p2_ch_uri        = $p2_c_uri - $p2_ctoku_uri_sagaku;
        $p2_ch_uri_sagaku = $p2_ch_uri;
        $p2_ch_uri        = number_format(($p2_ch_uri / $tani), $keta);
        $p2_c_uri         = number_format(($p2_c_uri / $tani), $keta);
    }
}
    ///// 今期累計
$query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='カプラ特注売上高'", $str_ym, $yyyymm);
if (getUniResult($query, $rui_ctoku_uri) < 1) {
    $rui_ctoku_uri         = 0;     // 検索失敗
    $rui_ctoku_uri_sagaku  = 0;
} else {
    $rui_ctoku_uri_sagaku  = $rui_ctoku_uri;
    $rui_ctoku_uri         = number_format(($rui_ctoku_uri / $tani), $keta);
}
if ( $yyyymm >= 200911) {
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
} else{
    $rui_sc_uri            = 0;     // 検索失敗
    $rui_sc_uri_sagaku     = 0;
    $rui_sc_uri_temp       = 0;
}
if ( $yyyymm >= 201004) {
    $query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='カプラ売上高'", $str_ym, $yyyymm);
    if (getUniResult($query, $rui_c_uri) < 1) {
        $rui_c_uri         = 0;     // 検索失敗
        $rui_ch_uri        = 0;
        $rui_ch_uri_sagaku = 0;
    }
    $query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='商管売上調整額'", $str_ym, $yyyymm);
    if (getUniResult($query, $rui_b_uri_cho) < 1) {
        $rui_b_uri_cho     = 0;
        $rui_c_uri         = $rui_c_uri - $rui_sc_uri_sagaku;           //カプラ試験修理を加味
        $rui_ch_uri        = $rui_c_uri - $rui_ctoku_uri_sagaku;
        $rui_ch_uri_sagaku = $rui_ch_uri;
        $rui_ch_uri        = number_format(($rui_ch_uri / $tani), $keta);
        $rui_c_uri         = number_format(($rui_c_uri / $tani), $keta);
    } else {
        $rui_c_uri         = $rui_c_uri - $rui_sc_uri_sagaku;
        $rui_ch_uri        = $rui_c_uri - $rui_ctoku_uri_sagaku;
        $rui_ch_uri_sagaku = $rui_ch_uri;
        $rui_ch_uri        = number_format(($rui_ch_uri / $tani), $keta);
        $rui_c_uri         = number_format(($rui_c_uri / $tani), $keta);
    }
} else if($yyyymm >= 200910 && $yyyymm <= 201003) {
    $query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='カプラ売上高'", $str_ym, $yyyymm);
    if (getUniResult($query, $rui_c_uri) < 1) {
        $rui_c_uri         = 0;     // 検索失敗
        $rui_ch_uri        = 0;
        $rui_ch_uri_sagaku = 0;
    }
    $str_ymb = 200910;
    $query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='商管売上調整額'", $str_ymb, $yyyymm);
    if (getUniResult($query, $rui_b_uri_cho) < 1) {
        $rui_b_uri_cho     = 0;
        $rui_c_uri         = $rui_c_uri - $rui_sc_uri_sagaku;           //カプラ試験修理を加味
        $rui_ch_uri        = $rui_c_uri - $rui_ctoku_uri_sagaku;
        $rui_ch_uri_sagaku = $rui_ch_uri;
        $rui_ch_uri        = number_format(($rui_ch_uri / $tani), $keta);
        $rui_c_uri         = number_format(($rui_c_uri / $tani), $keta);
    } else {
        $rui_c_uri         = $rui_c_uri - $rui_sc_uri_sagaku;
        $rui_ch_uri        = $rui_c_uri - $rui_ctoku_uri_sagaku;
        $rui_ch_uri_sagaku = $rui_ch_uri;
        $rui_ch_uri        = number_format(($rui_ch_uri / $tani), $keta);
        $rui_c_uri         = number_format(($rui_c_uri / $tani), $keta);
    }
} else {
    $query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='カプラ売上高'", $str_ym, $yyyymm);
    if (getUniResult($query, $rui_c_uri) < 1) {
        $rui_c_uri         = 0;     // 検索失敗
        $rui_ch_uri        = 0;
        $rui_ch_uri_sagaku = 0;
    } else {
        $rui_ch_uri        = $rui_c_uri - $rui_ctoku_uri_sagaku;
        $rui_ch_uri_sagaku = $rui_ch_uri;
        $rui_ch_uri        = number_format(($rui_ch_uri / $tani), $keta);
        $rui_c_uri         = number_format(($rui_c_uri / $tani), $keta);
    }
}

/********** 期首材料仕掛品棚卸高 **********/
    ///// 当月
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='カプラ特注期首棚卸高'", $yyyymm);
if (getUniResult($query, $ctoku_invent) < 1) {
    $ctoku_invent        = 0;     // 検索失敗
    $ctoku_invent_sagaku = 0;
} else {
    $ctoku_invent_sagaku = $ctoku_invent;
    $ctoku_invent = number_format(($ctoku_invent / $tani), $keta);
}
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='カプラ期首棚卸高'", $yyyymm);
if (getUniResult($query, $c_invent) < 1) {
    $c_invent            = 0;     // 検索失敗
    $ch_invent           = 0;
    $ch_invent_sagaku    = 0;
} else {
    $ch_invent           = $c_invent - $ctoku_invent_sagaku;
    $ch_invent_sagaku    = $ch_invent;
    $ch_invent           = number_format(($ch_invent / $tani), $keta);
    $c_invent            = number_format(($c_invent / $tani), $keta);
}
    ///// 前月
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='カプラ特注期首棚卸高'", $p1_ym);
if (getUniResult($query, $p1_ctoku_invent) < 1) {
    $p1_ctoku_invent        = 0;     // 検索失敗
    $p1_ctoku_invent_sagaku = 0;
} else {
    $p1_ctoku_invent_sagaku = $p1_ctoku_invent;
    $p1_ctoku_invent        = number_format(($p1_ctoku_invent / $tani), $keta);
}
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='カプラ期首棚卸高'", $p1_ym);
if (getUniResult($query, $p1_c_invent) < 1) {
    $p1_c_invent            = 0;     // 検索失敗
    $p1_ch_invent           = 0;
    $p1_ch_invent_sagaku    = 0;
} else {
    $p1_ch_invent           = $p1_c_invent - $p1_ctoku_invent_sagaku;
    $p1_ch_invent_sagaku    = $p1_ch_invent;
    $p1_ch_invent           = number_format(($p1_ch_invent / $tani), $keta);
    $p1_c_invent            = number_format(($p1_c_invent / $tani), $keta);
}
    ///// 前前月
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='カプラ特注期首棚卸高'", $p2_ym);
if (getUniResult($query, $p2_ctoku_invent) < 1) {
    $p2_ctoku_invent        = 0;     // 検索失敗
    $p2_ctoku_invent_sagaku = 0;
} else {
    $p2_ctoku_invent_sagaku = $p2_ctoku_invent;
    $p2_ctoku_invent        = number_format(($p2_ctoku_invent / $tani), $keta);
}
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='カプラ期首棚卸高'", $p2_ym);
if (getUniResult($query, $p2_c_invent) < 1) {
    $p2_c_invent            = 0;     // 検索失敗
    $p2_ch_invent           = 0;
    $p2_ch_invent_sagaku    = 0;
} else {
    $p2_ch_invent           = $p2_c_invent - $p2_ctoku_invent_sagaku;
    $p2_ch_invent_sagaku    = $p2_ch_invent;
    $p2_ch_invent           = number_format(($p2_ch_invent / $tani), $keta);
    $p2_c_invent            = number_format(($p2_c_invent / $tani), $keta);
}
    ///// 今期累計
    /////   期首棚卸高の累計は 期初年月の期首棚卸高になる
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='カプラ特注期首棚卸高'", $str_ym);
if (getUniResult($query, $rui_ctoku_invent) < 1) {
    $rui_ctoku_invent        = 0;     // 検索失敗
    $rui_ctoku_invent_sagaku = 0;
} else {
    $rui_ctoku_invent_sagaku = $rui_ctoku_invent;
    $rui_ctoku_invent        = number_format(($rui_ctoku_invent / $tani), $keta);
}
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='カプラ期首棚卸高'", $str_ym);
if (getUniResult($query, $rui_c_invent) < 1) {
    $rui_c_invent            = 0;     // 検索失敗
    $rui_ch_invent           = 0;
    $rui_ch_invent_sagaku    = 0;
} else {
    $rui_ch_invent           = $rui_c_invent - $rui_ctoku_invent_sagaku;
    $rui_ch_invent_sagaku    = $rui_ch_invent;
    $rui_ch_invent           = number_format(($rui_ch_invent / $tani), $keta);
    $rui_c_invent            = number_format(($rui_c_invent / $tani), $keta);
}

/********** 材料費(仕入高) **********/
    ///// 当月
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='カプラ特注仕入高'", $yyyymm);
if (getUniResult($query, $ctoku_metarial) < 1) {
    $ctoku_metarial          = 0;   // 検索失敗
    $ctoku_metarial_sagaku   = 0;
} else {
    $ctoku_metarial_sagaku   = $ctoku_metarial;
    $ctoku_metarial          = number_format(($ctoku_metarial / $tani), $keta);
}
if ( $yyyymm >= 200911) {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='カプラ試修材料費'", $yyyymm);
    if (getUniResult($query, $sc_metarial) < 1) {
        $sc_metarial         = 0;   // 検索失敗
        $sc_metarial_sagaku  = 0;
        $sc_metarial_temp    = 0;
    } else {
        $sc_metarial_temp    = $sc_metarial;
        $sc_metarial_sagaku  = $sc_metarial;
        $sc_metarial         = number_format(($sc_metarial / $tani), $keta);
    }
} else{
    $sc_metarial             = 0;     // 検索失敗
    $sc_metarial_sagaku      = 0;
    $sc_metarial_temp        = 0;
}
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='カプラ材料費'", $yyyymm);
if (getUniResult($query, $c_metarial) < 1) {
    $c_metarial              = 0;     // 検索失敗
    $ch_metarial             = 0;
    $ch_metarial_sagaku      = 0;
} else {
    $c_metarial              = $c_metarial - $sc_metarial_sagaku;       //カプラ試験修理を加味
    // 2013/11/07 追加 2013年10月度 業務委託費（横川派遣料）調整
    if ($yyyymm == 201310) {
        $c_metarial -= 1245035;
    }
    if ($yyyymm == 201311) {
        $c_metarial += 1245035;
    }
    $ch_metarial             = $c_metarial - $ctoku_metarial_sagaku;
    $ch_metarial_sagaku      = $ch_metarial;
    // 2013/11/07 追加 2013年10月度 業務委託費（横川派遣料）調整
    $ch_metarial             = number_format(($ch_metarial / $tani), $keta);
    $c_metarial              = number_format(($c_metarial / $tani), $keta);
}
    ///// 前月
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='カプラ特注仕入高'", $p1_ym);
if (getUniResult($query, $p1_ctoku_metarial) < 1) {
    $p1_ctoku_metarial         = 0;   // 検索失敗
    $p1_ctoku_metarial_sagaku  = 0;
} else {
    $p1_ctoku_metarial_sagaku  = $p1_ctoku_metarial;
    $p1_ctoku_metarial         = number_format(($p1_ctoku_metarial / $tani), $keta);
}
if ( $yyyymm >= 200911) {
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
} else{
    $p1_sc_metarial            = 0;     // 検索失敗
    $p1_sc_metarial_sagaku     = 0;
    $p1_sc_metarial_temp       = 0;
}
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='カプラ材料費'", $p1_ym);
if (getUniResult($query, $p1_c_metarial) < 1) {
    $p1_c_metarial         = 0;         // 検索失敗
    $p1_ch_metarial        = 0;
    $p1_ch_metarial_sagaku = 0;
} else {
    $p1_c_metarial         = $p1_c_metarial - $p1_sc_metarial_sagaku;       //カプラ試験修理を加味
    // 2013/11/07 追加 2013年10月度 業務委託費（横川派遣料）調整
    if ($p1_ym == 201310) {
        $p1_c_metarial -= 1245035;
    }
    if ($p1_ym == 201311) {
        $p1_c_metarial += 1245035;
    }
    $p1_ch_metarial        = $p1_c_metarial - $p1_ctoku_metarial_sagaku;
    $p1_ch_metarial_sagaku = $p1_ch_metarial;
    $p1_ch_metarial        = number_format(($p1_ch_metarial / $tani), $keta);
    $p1_c_metarial = number_format(($p1_c_metarial / $tani), $keta);
}
    ///// 前前月
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='カプラ特注仕入高'", $p2_ym);
if (getUniResult($query, $p2_ctoku_metarial) < 1) {
    $p2_ctoku_metarial        = 0;      // 検索失敗
    $p2_ctoku_metarial_sagaku = 0;
} else {
    $p2_ctoku_metarial_sagaku = $p2_ctoku_metarial;
    $p2_ctoku_metarial        = number_format(($p2_ctoku_metarial / $tani), $keta);
}
if ( $yyyymm >= 200911) {
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
} else{
    $p2_sc_metarial            = 0;     // 検索失敗
    $p2_sc_metarial_sagaku     = 0;
    $p2_sc_metarial_temp       = 0;
}
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='カプラ材料費'", $p2_ym);
if (getUniResult($query, $p2_c_metarial) < 1) {
    $p2_c_metarial         = 0;         // 検索失敗
    $p2_ch_metarial        = 0;
    $p2_ch_metarial_sagaku = 0;
} else {
    $p2_c_metarial         = $p2_c_metarial - $p2_sc_metarial_sagaku;       //カプラ試験修理を加味
    // 2013/11/07 追加 2013年10月度 業務委託費（横川派遣料）調整
    if ($p2_ym == 201310) {
        $p2_c_metarial -= 1245035;
    }
    if ($p2_ym == 201311) {
        $p2_c_metarial += 1245035;
    }
    $p2_ch_metarial        = $p2_c_metarial - $p2_ctoku_metarial_sagaku;
    $p2_ch_metarial_sagaku = $p2_ch_metarial;
    $p2_ch_metarial        = number_format(($p2_ch_metarial / $tani), $keta);
    $p2_c_metarial         = number_format(($p2_c_metarial / $tani), $keta);
}
    ///// 今期累計
$query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='カプラ特注仕入高'", $str_ym, $yyyymm);
if (getUniResult($query, $rui_ctoku_metarial) < 1) {
    $rui_ctoku_metarial        = 0;     // 検索失敗
    $rui_ctoku_metarial_sagaku = 0;
} else {
    $rui_ctoku_metarial_sagaku = $rui_ctoku_metarial;
    $rui_ctoku_metarial        = number_format(($rui_ctoku_metarial / $tani), $keta);
}
if ( $yyyymm >= 200911) {
    $query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='カプラ試修材料費'", $str_ym, $yyyymm);
    if (getUniResult($query, $rui_sc_metarial) < 1) {
        $rui_sc_metarial        = 0;    // 検索失敗
        $rui_sc_metarial_sagaku = 0;
        $rui_sc_metarial_temp   = 0;
    } else {
        $rui_sc_metarial_temp   = $rui_sc_metarial;
        $rui_sc_metarial_sagaku = $rui_sc_metarial;
        $rui_sc_metarial        = number_format(($rui_sc_metarial / $tani), $keta);
    }
} else{
    $rui_sc_metarial            = 0;    // 検索失敗
    $rui_sc_metarial_sagaku     = 0;
    $rui_sc_metarial_temp       = 0;
}
$query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='カプラ材料費'", $str_ym, $yyyymm);
if (getUniResult($query, $rui_c_metarial) < 1) {
    $rui_c_metarial         = 0;        // 検索失敗
    $rui_ch_metarial        = 0;
    $rui_ch_metarial_sagaku = 0;
} else {
    $rui_c_metarial         = $rui_c_metarial - $rui_sc_metarial_sagaku;       //カプラ試験修理を加味
    // 2013/11/07 追加 2013年10月度 業務委託費（横川派遣料）調整
    if ($yyyymm >= 201310 && $yyyymm <= 201403) {
        $rui_c_metarial -= 1245035;
    }
    if ($yyyymm >= 201311 && $yyyymm <= 201403) {
        $rui_c_metarial += 1245035;
    }
    $rui_ch_metarial        = $rui_c_metarial - $rui_ctoku_metarial_sagaku;
    $rui_ch_metarial_sagaku = $rui_ch_metarial;
    $rui_ch_metarial        = number_format(($rui_ch_metarial / $tani), $keta);
    $rui_c_metarial         = number_format(($rui_c_metarial / $tani), $keta);
}

/********** 労務費 **********/
    ///// 当月
    // 商品管理（カプラに暫定的に入れているため）
    // 下は7月未払い給与分
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='商管労務費'", $yyyymm);
if (getUniResult($query, $b_roumu_sagaku) < 1) {
    $b_roumu_sagaku = 0;    // 検索失敗
}
$query = sprintf("select sum(orign_kin) from act_allo_history where pl_bs_ym=%d and actcod>=8101 and orign_id=580", $yyyymm);
if (getUniResult($query, $b_roumu) < 1) {
    $b_roumu        = 0;    // 検索失敗
} else {
    $b_roumu        = $b_roumu + $b_roumu_sagaku;
}
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='カプラ特注労務費'", $yyyymm);
if (getUniResult($query, $ctoku_roumu) < 1) {
    $ctoku_roumu        = 0;    // 検索失敗
    $ctoku_roumu_sagaku = 0;
} else {
    $ctoku_roumu_sagaku = $ctoku_roumu;
    $ctoku_roumu        = number_format(($ctoku_roumu / $tani), $keta);
}
if ($yyyymm >= 201001) {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='カプラ給与配賦率'", $yyyymm);
    if (getUniResult($query, $c_kyu_kin) < 1) {
        $c_kyu_kin = 0;
    }
}
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='カプラ労務費'", $yyyymm);
if (getUniResult($query, $c_roumu) < 1) {
    $c_roumu            = 0;    // 検索失敗]
    $ch_roumu           = 0;
    $ch_roumu_sagaku    = 0;
} else {
    $c_roumu            = $c_roumu - $b_roumu;
    if ($yyyymm == 200912) {
        $c_roumu = $c_roumu + 1227429;
    }
    if ($yyyymm >= 201001) {
        $c_roumu = $c_roumu + $c_kyu_kin;   // カプラ配賦給与を加味(全て標準に）
        //$c_roumu = $c_roumu + 151313;   // 添田さんの給与をC・Lは35%。試験修理に30%振分
    }
    if ($yyyymm == 201408) {
        $c_roumu = $c_roumu + 611904;
    }
    $ch_roumu           = $c_roumu - $ctoku_roumu_sagaku;
    $ch_roumu_sagaku    = $ch_roumu;
    $ch_roumu           = number_format(($ch_roumu / $tani), $keta);
    $c_roumu            = number_format(($c_roumu / $tani), $keta);
}
    ///// 前月
    // 商品管理（カプラに暫定的に入れているため）
    // 下は7月未払い給与分
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='商管労務費'", $p1_ym);
if (getUniResult($query, $p1_b_roumu_sagaku) < 1) {
    $p1_b_roumu_sagaku = 0;    // 検索失敗
}
$query = sprintf("select sum(orign_kin) from act_allo_history where pl_bs_ym=%d and actcod>=8101 and orign_id=580", $p1_ym);
if (getUniResult($query, $p1_b_roumu) < 1) {
    $p1_b_roumu        = 0;    // 検索失敗
} else {
    $p1_b_roumu        = $p1_b_roumu + $p1_b_roumu_sagaku;
}
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='カプラ特注労務費'", $p1_ym);
if (getUniResult($query, $p1_ctoku_roumu) < 1) {
    $p1_ctoku_roumu        = 0;    // 検索失敗
    $p1_ctoku_roumu_sagaku = 0;
} else {
    $p1_ctoku_roumu_sagaku = $p1_ctoku_roumu;
    $p1_ctoku_roumu        = number_format(($p1_ctoku_roumu / $tani), $keta);
}
if ($p1_ym >= 201001) {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='カプラ給与配賦率'", $p1_ym);
    if (getUniResult($query, $p1_c_kyu_kin) < 1) {
        $p1_c_kyu_kin = 0;
    }
}
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='カプラ労務費'", $p1_ym);
if (getUniResult($query, $p1_c_roumu) < 1) {
    $p1_c_roumu         = 0;       // 検索失敗]
    $p1_ch_roumu        = 0;
    $p1_ch_roumu_sagaku = 0;
} else {
    $p1_c_roumu         = $p1_c_roumu - $p1_b_roumu;
    if ($p1_ym == 200912) {
        $p1_c_roumu = $p1_c_roumu + 1227429;
    }
    if ($p1_ym >= 201001) {
        $p1_c_roumu = $p1_c_roumu + $p1_c_kyu_kin;   // カプラ配賦給与を加味（全て標準に）
        //$p1_c_roumu = $p1_c_roumu + 151313; // 添田さんの給与をC・Lは35%。試験修理に30%振分
    }
    if ($p1_ym == 201408) {
        $p1_c_roumu = $p1_c_roumu + 611904;
    }
    $p1_ch_roumu        = $p1_c_roumu - $p1_ctoku_roumu_sagaku;
    $p1_ch_roumu_sagaku = $p1_ch_roumu;
    $p1_ch_roumu        = number_format(($p1_ch_roumu / $tani), $keta);
    $p1_c_roumu         = number_format(($p1_c_roumu / $tani), $keta);
}
    ///// 前前月
    // 商品管理（カプラに暫定的に入れているため）
    // 下は7月未払い給与分
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='商管労務費'", $p2_ym);
if (getUniResult($query, $p2_b_roumu_sagaku) < 1) {
    $p2_b_roumu_sagaku  = 0;       // 検索失敗
}
$query = sprintf("select sum(orign_kin) from act_allo_history where pl_bs_ym=%d and actcod>=8101 and orign_id=580", $p2_ym);
if (getUniResult($query, $p2_b_roumu) < 1) {
    $p2_b_roumu         = 0;      // 検索失敗
} else {
    $p2_b_roumu         = $p2_b_roumu + $p2_b_roumu_sagaku;
}
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='カプラ特注労務費'", $p2_ym);
if (getUniResult($query, $p2_ctoku_roumu) < 1) {
    $p2_ctoku_roumu        = 0;   // 検索失敗
    $p2_ctoku_roumu_sagaku = 0;
} else {
    $p2_ctoku_roumu_sagaku = $p2_ctoku_roumu;
    $p2_ctoku_roumu        = number_format(($p2_ctoku_roumu / $tani), $keta);
}
if ($p2_ym >= 201001) {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='カプラ給与配賦率'", $p2_ym);
    if (getUniResult($query, $p2_c_kyu_kin) < 1) {
        $p2_c_kyu_kin = 0;
    }
}
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='カプラ労務費'", $p2_ym);
if (getUniResult($query, $p2_c_roumu) < 1) {
    $p2_c_roumu            = 0;   // 検索失敗]
    $p2_ch_roumu           = 0;
    $p2_ch_roumu_sagaku    = 0;
} else {
    $p2_c_roumu            = $p2_c_roumu - $p2_b_roumu;
    if ($p2_ym == 200912) {
        $p2_c_roumu = $p2_c_roumu + 1227429;
    }
    if ($p2_ym >= 201001) {
        $p2_c_roumu = $p2_c_roumu + $p2_c_kyu_kin;   // カプラ配賦給与を加味（全て標準に）
        //$p2_c_roumu = $p2_c_roumu + 151313;    // 添田さんの給与をC・Lは35%。試験修理に30%振分
    }
    if ($p2_ym == 201408) {
        $p2_c_roumu = $p2_c_roumu + 611904;
    }
    $p2_ch_roumu           = $p2_c_roumu - $p2_ctoku_roumu_sagaku;
    $p2_ch_roumu_sagaku    = $p2_ch_roumu;
    $p2_ch_roumu           = number_format(($p2_ch_roumu / $tani), $keta);
    $p2_c_roumu            = number_format(($p2_c_roumu / $tani), $keta);
}
    ///// 今期累計
    // 商品管理（カプラに暫定的に入れているため）
    // 下は7月未払い給与分
$query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='商管労務費'", $str_ym, $yyyymm);
if (getUniResult($query, $rui_b_roumu_sagaku) < 1) {
    $rui_b_roumu_sagaku = 0;    // 検索失敗
}
$query = sprintf("select sum(orign_kin) from act_allo_history where pl_bs_ym>=%d and pl_bs_ym<=%d and actcod>=8101 and orign_id=580", $str_ym, $yyyymm);
if (getUniResult($query, $rui_b_roumu) < 1) {
    $rui_b_roumu        = 0;    // 検索失敗
} else {
    $rui_b_roumu        = $rui_b_roumu + $rui_b_roumu_sagaku;
}
$query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='カプラ特注労務費'", $str_ym, $yyyymm);
if (getUniResult($query, $rui_ctoku_roumu) < 1) {
    $rui_ctoku_roumu        = 0;    // 検索失敗
    $rui_ctoku_roumu_sagaku = 0;
} else {
    $rui_ctoku_roumu_sagaku = $rui_ctoku_roumu;
    $rui_ctoku_roumu        = number_format(($rui_ctoku_roumu / $tani), $keta);
}
if ($yyyymm >= 201001) {
    $query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='カプラ給与配賦率'", $str_ym, $yyyymm);
    if (getUniResult($query, $rui_c_kyu_kin) < 1) {
        $rui_c_kyu_kin = 0;
    }
}
$query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='カプラ労務費'", $str_ym, $yyyymm);
if (getUniResult($query, $rui_c_roumu) < 1) {
    $rui_c_roumu         = 0;       // 検索失敗
    $rui_ch_roumu        = 0;
    $rui_ch_roumu_sagaku = 0;
} else {
    $rui_c_roumu         = $rui_c_roumu - $rui_b_roumu;
    if ($yyyymm >= 200912 && $yyyymm <= 201003) {
        $rui_c_roumu = $rui_c_roumu + 1227429;
    }
    if ($yyyymm >= 201001) {
        $rui_c_roumu = $rui_c_roumu + $rui_c_kyu_kin;   // カプラ配賦給与を加味（全て標準に）
        //$rui_c_roumu = $rui_c_roumu + 151313;   // 添田さんの給与をC・Lは35%。試験修理に30%振分
    }
    if ($yyyymm >= 201408 && $yyyymm <= 201503) {
        $rui_c_roumu = $rui_c_roumu + 611904;
    }
    $rui_ch_roumu        = $rui_c_roumu - $rui_ctoku_roumu_sagaku;
    $rui_ch_roumu_sagaku = $rui_ch_roumu;
    $rui_ch_roumu        = number_format(($rui_ch_roumu / $tani), $keta);
    $rui_c_roumu         = number_format(($rui_c_roumu / $tani), $keta);
}

/********** 経費(製造経費) **********/
    ///// 当月
    // 商品管理（カプラに暫定的に入れているため）
$query = sprintf("select sum(orign_kin) from act_allo_history where pl_bs_ym=%d and actcod>=7501 and actcod<=8000 and orign_id=580", $yyyymm);
if (getUniResult($query, $b_expense) < 1) {
    $b_expense            = 0;      // 検索失敗
}
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='カプラ特注製造経費'", $yyyymm);
if (getUniResult($query, $ctoku_expense) < 1) {
    $ctoku_expense        = 0;      // 検索失敗
    $ctoku_expense_sagaku = 0;
} else {
    $ctoku_expense_sagaku = $ctoku_expense;
    $ctoku_expense        = number_format(($ctoku_expense / $tani), $keta);
}
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='カプラ製造経費'", $yyyymm);
if (getUniResult($query, $c_expense) < 1) {
    $c_expense         = 0;         // 検索失敗
    $ch_expense        = 0;
    $ch_expense_sagaku = 0;
} else {
    $c_expense         = $c_expense - $b_expense;     // カプラ製造経費－商管製造経費
    $ch_expense        = $c_expense - $ctoku_expense_sagaku;
    $ch_expense_sagaku = $ch_expense;
    $ch_expense        = number_format(($ch_expense / $tani), $keta);
    $c_expense         = number_format(($c_expense / $tani), $keta);
}
    ///// 前月
    // 商品管理（カプラに暫定的に入れているため）
$query = sprintf("select sum(orign_kin) from act_allo_history where pl_bs_ym=%d and actcod>=7501 and actcod<=8000 and orign_id=580", $p1_ym);
if (getUniResult($query, $p1_b_expense) < 1) {
    $p1_b_expense            = 0;   // 検索失敗
}
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='カプラ特注製造経費'", $p1_ym);
if (getUniResult($query, $p1_ctoku_expense) < 1) {
    $p1_ctoku_expense        = 0;   // 検索失敗
    $p1_ctoku_expense_sagaku = 0;
} else {
    $p1_ctoku_expense_sagaku = $p1_ctoku_expense;
    $p1_ctoku_expense        = number_format(($p1_ctoku_expense / $tani), $keta);
}
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='カプラ製造経費'", $p1_ym);
if (getUniResult($query, $p1_c_expense) < 1) {
    $p1_c_expense         = 0;      // 検索失敗
    $p1_ch_expense        = 0;
    $p1_ch_expense_sagaku = 0;
} else {
    $p1_c_expense         = $p1_c_expense - $p1_b_expense;      // カプラ製造経費－商管製造経費
    $p1_ch_expense        = $p1_c_expense - $p1_ctoku_expense_sagaku;
    $p1_ch_expense_sagaku = $p1_ch_expense;
    $p1_ch_expense        = number_format(($p1_ch_expense / $tani), $keta);
    $p1_c_expense         = number_format(($p1_c_expense / $tani), $keta);
}
    ///// 前前月
    // 商品管理（カプラに暫定的に入れているため）
$query = sprintf("select sum(orign_kin) from act_allo_history where pl_bs_ym=%d and actcod>=7501 and actcod<=8000 and orign_id=580", $p2_ym);
if (getUniResult($query, $p2_b_expense) < 1) {
    $p2_b_expense            = 0;      // 検索失敗
}
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='カプラ特注製造経費'", $p2_ym);
if (getUniResult($query, $p2_ctoku_expense) < 1) {
    $p2_ctoku_expense        = 0;      // 検索失敗
    $p2_ctoku_expense_sagaku = 0;
} else {
    $p2_ctoku_expense_sagaku = $p2_ctoku_expense;
    $p2_ctoku_expense        = number_format(($p2_ctoku_expense / $tani), $keta);
}
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='カプラ製造経費'", $p2_ym);
if (getUniResult($query, $p2_c_expense) < 1) {
    $p2_c_expense         = 0;         // 検索失敗
    $p2_ch_expense        = 0;
    $p2_ch_expense_sagaku = 0;
} else {
    $p2_c_expense         = $p2_c_expense - $p2_b_expense;     // カプラ製造経費－商管製造経費
    $p2_ch_expense        = $p2_c_expense - $p2_ctoku_expense_sagaku;
    $p2_ch_expense_sagaku = $p2_ch_expense;
    $p2_ch_expense        = number_format(($p2_ch_expense / $tani), $keta);
    $p2_c_expense         = number_format(($p2_c_expense / $tani), $keta);
}
    ///// 今期累計
    // 商品管理（カプラに暫定的に入れているため）
$query = sprintf("select sum(orign_kin) from act_allo_history where pl_bs_ym>=%d and pl_bs_ym<=%d and actcod>=7501 and actcod<=8000 and orign_id=580", $str_ym, $yyyymm);
if (getUniResult($query, $rui_b_expense) < 1) {
    $rui_b_expense            = 0;      // 検索失敗
}
$query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='カプラ特注製造経費'", $str_ym, $yyyymm);
if (getUniResult($query, $rui_ctoku_expense) < 1) {
    $rui_ctoku_expense        = 0;      // 検索失敗
    $rui_ctoku_expense_sagaku = 0;
} else {
    $rui_ctoku_expense_sagaku = $rui_ctoku_expense;
    $rui_ctoku_expense        = number_format(($rui_ctoku_expense / $tani), $keta);
}
$query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='カプラ製造経費'", $str_ym, $yyyymm);
if (getUniResult($query, $rui_c_expense) < 1) {
    $rui_c_expense         = 0;         // 検索失敗
    $rui_ch_expense        = 0;
    $rui_ch_expense_sagaku = 0;
} else {
    $rui_c_expense         = $rui_c_expense - $rui_b_expense;     // カプラ製造経費－商管製造経費
    $rui_ch_expense        = $rui_c_expense - $rui_ctoku_expense_sagaku;
    $rui_ch_expense_sagaku = $rui_ch_expense;
    $rui_ch_expense        = number_format(($rui_ch_expense / $tani), $keta);
    $rui_c_expense         = number_format(($rui_c_expense / $tani), $keta);
}

/********** 期末材料仕掛品棚卸高 **********/
    ///// 当月
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='カプラ特注期末棚卸高'", $yyyymm);
if (getUniResult($query, $ctoku_endinv) < 1) {
    $ctoku_endinv        = 0;                               // 検索失敗
    $ctoku_endinv_sagaku = 0;
} else {
    $ctoku_endinv_sagaku = $ctoku_endinv;
    $ctoku_endinv        = ($ctoku_endinv * (-1));          // 符号反転
    $ctoku_endinv        = number_format(($ctoku_endinv / $tani), $keta);
}
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='カプラ期末棚卸高'", $yyyymm);
if (getUniResult($query, $c_endinv) < 1) {
    $c_endinv            = 0;                               // 検索失敗
    $ch_endinv           = 0;
    $ch_endinv_sagaku    = 0;
} else {
    $ch_endinv           = $c_endinv - $ctoku_endinv_sagaku;
    $ch_endinv           = ($ch_endinv * (-1));
    $c_endinv            = ($c_endinv * (-1));              // 符号反転
    $ch_endinv_sagaku    = $ch_endinv;
    $ch_endinv           = number_format(($ch_endinv / $tani), $keta);
    $c_endinv            = number_format(($c_endinv / $tani), $keta);
}
    ///// 前月
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='カプラ特注期末棚卸高'", $p1_ym);
if (getUniResult($query, $p1_ctoku_endinv) < 1) {
    $p1_ctoku_endinv        = 0;                            // 検索失敗
    $p1_ctoku_endinv_sagaku = 0;
} else {
    $p1_ctoku_endinv_sagaku = $p1_ctoku_endinv;
    $p1_ctoku_endinv        = ($p1_ctoku_endinv * (-1));    // 符号反転
    $p1_ctoku_endinv        = number_format(($p1_ctoku_endinv / $tani), $keta);
}
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='カプラ期末棚卸高'", $p1_ym);
if (getUniResult($query, $p1_c_endinv) < 1) {
    $p1_c_endinv            = 0;                            // 検索失敗
    $p1_ch_endinv           = 0;
    $p1_ch_endinv_sagaku    = 0;
} else {
    $p1_ch_endinv           = $p1_c_endinv - $p1_ctoku_endinv_sagaku;
    $p1_ch_endinv           = ($p1_ch_endinv * (-1));
    $p1_c_endinv            = ($p1_c_endinv * (-1));        // 符号反転
    $p1_ch_endinv_sagaku    = $p1_ch_endinv;
    $p1_ch_endinv           = number_format(($p1_ch_endinv / $tani), $keta);
    $p1_c_endinv            = number_format(($p1_c_endinv / $tani), $keta);
}
    ///// 前前月
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='カプラ特注期末棚卸高'", $p2_ym);
if (getUniResult($query, $p2_ctoku_endinv) < 1) {
    $p2_ctoku_endinv        = 0;                            // 検索失敗
    $p2_ctoku_endinv_sagaku = 0;
} else {
    $p2_ctoku_endinv_sagaku = $p2_ctoku_endinv;
    $p2_ctoku_endinv        = ($p2_ctoku_endinv * (-1));    // 符号反転
    $p2_ctoku_endinv        = number_format(($p2_ctoku_endinv / $tani), $keta);
}
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='カプラ期末棚卸高'", $p2_ym);
if (getUniResult($query, $p2_c_endinv) < 1) {
    $p2_c_endinv            = 0;                            // 検索失敗
    $p2_ch_endinv           = 0;
    $p2_ch_endinv_sagaku    = 0;
} else {
    $p2_ch_endinv           = $p2_c_endinv - $p2_ctoku_endinv_sagaku;
    $p2_ch_endinv           = ($p2_ch_endinv * (-1));
    $p2_c_endinv            = ($p2_c_endinv * (-1));        // 符号反転
    $p2_ch_endinv_sagaku    = $p2_ch_endinv;
    $p2_ch_endinv           = number_format(($p2_ch_endinv / $tani), $keta);
    $p2_c_endinv            = number_format(($p2_c_endinv / $tani), $keta);
}
    ///// 今期累計
    /////   期末棚卸高の累計は当月と同じ

///////// 商品管理分の差額計算（労務費＋製造経費）
$b_sagaku     = $b_roumu + $b_expense;
$p1_b_sagaku  = $p1_b_roumu + $p1_b_expense;
$p2_b_sagaku  = $p2_b_roumu + $p2_b_expense;
$rui_b_sagaku = $rui_b_roumu + $rui_b_expense;
/********** 売上原価 **********/
    ///// 当月
    ///// カプラ特注
$ctoku_urigen        = $ctoku_invent_sagaku + $ctoku_metarial_sagaku + $ctoku_roumu_sagaku + $ctoku_expense_sagaku - $ctoku_endinv_sagaku;
$ctoku_urigen_sagaku = $ctoku_urigen;
$ctoku_urigen        = number_format(($ctoku_urigen / $tani), $keta);
    ///// CL
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='カプラ売上原価'", $yyyymm);
if (getUniResult($query, $c_urigen) < 1) {
    $c_urigen         = 0;     // 検索失敗
    $ch_urigen        = 0;     // 検索失敗
    $ch_urigen_sagaku = 0;     // 検索失敗
} else {
    $c_urigen         = $c_urigen - $b_sagaku - $sc_metarial_sagaku;    //カプラ試験修理も加味
    if ($yyyymm == 200912) {
        $c_urigen = $c_urigen + 1227429;
    }
    if ($yyyymm >= 201001) {
        $c_urigen = $c_urigen + $c_kyu_kin;     // 添田さんの給与をC・Lは35%。試験修理に30%振分(全て標準)
        //$c_urigen = $c_urigen + 151313;     // 添田さんの給与をC・Lは35%。試験修理に30%振分
    }
    // 2013/11/07 追加 2013年10月度 業務委託費（横川派遣料）調整
    if ($yyyymm == 201310) {
        $c_urigen -= 1245035;
    }
    if ($yyyymm == 201311) {
        $c_urigen += 1245035;
    }
    if ($yyyymm == 201408) {
        $c_urigen += 611904;
    }
    $ch_urigen        = $c_urigen - $ctoku_urigen_sagaku;
    $ch_urigen_sagaku = $ch_urigen;
    $ch_urigen        = number_format(($ch_urigen / $tani), $keta);
    $c_urigen         = number_format(($c_urigen / $tani), $keta);
}
    ///// 前月
    ///// カプラ特注
$p1_ctoku_urigen        = $p1_ctoku_invent_sagaku + $p1_ctoku_metarial_sagaku + $p1_ctoku_roumu_sagaku + $p1_ctoku_expense_sagaku - $p1_ctoku_endinv_sagaku;
$p1_ctoku_urigen_sagaku = $p1_ctoku_urigen;
$p1_ctoku_urigen        = number_format(($p1_ctoku_urigen / $tani), $keta);
    ///// C
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='カプラ売上原価'", $p1_ym);
if (getUniResult($query, $p1_c_urigen) < 1) {
    $p1_c_urigen         = 0;     // 検索失敗
    $p1_ch_urigen        = 0;     // 検索失敗
    $p1_ch_urigen_sagaku = 0;     // 検索失敗
} else {
    $p1_c_urigen         = $p1_c_urigen - $p1_b_sagaku - $p1_sc_metarial_sagaku;    //カプラ試験修理も加味
    if ($p1_ym == 200912) {
        $p1_c_urigen = $p1_c_urigen + 1227429;
    }
    if ($p1_ym >= 201001) {
        $p1_c_urigen = $p1_c_urigen + $p1_c_kyu_kin;   // 添田さんの給与をC・Lは35%。試験修理に30%振分(全て標準)
        //$p1_c_urigen = $p1_c_urigen + 151313;   // 添田さんの給与をC・Lは35%。試験修理に30%振分
    }
    // 2013/11/07 追加 2013年10月度 業務委託費（横川派遣料）調整
    if ($p1_ym == 201310) {
        $p1_c_urigen -= 1245035;
    }
    if ($p1_ym == 201311) {
        $p1_c_urigen += 1245035;
    }
    if ($p1_ym == 201408) {
        $p1_c_urigen += 611904;
    }
    $p1_ch_urigen        = $p1_c_urigen - $p1_ctoku_urigen_sagaku;
    $p1_ch_urigen_sagaku = $p1_ch_urigen;
    $p1_ch_urigen        = number_format(($p1_ch_urigen / $tani), $keta);
    $p1_c_urigen         = number_format(($p1_c_urigen / $tani), $keta);
}
    ///// 前前月
    ///// カプラ特注
$p2_ctoku_urigen        = $p2_ctoku_invent_sagaku + $p2_ctoku_metarial_sagaku + $p2_ctoku_roumu_sagaku + $p2_ctoku_expense_sagaku - $p2_ctoku_endinv_sagaku;
$p2_ctoku_urigen_sagaku = $p2_ctoku_urigen;
$p2_ctoku_urigen        = number_format(($p2_ctoku_urigen / $tani), $keta);
    ///// C
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='カプラ売上原価'", $p2_ym);
if (getUniResult($query, $p2_c_urigen) < 1) {
    $p2_c_urigen         = 0;     // 検索失敗
    $p2_ch_urigen        = 0;     // 検索失敗
    $p2_ch_urigen_sagaku = 0;     // 検索失敗
} else {
    $p2_c_urigen         = $p2_c_urigen - $p2_b_sagaku - $p2_sc_metarial_sagaku;    //カプラ試験修理も加味
    if ($p2_ym == 200912) {
        $p2_c_urigen = $p2_c_urigen + 1227429;
    }
    if ($p2_ym >= 201001) {
        $p2_c_urigen = $p2_c_urigen + $p2_c_kyu_kin;   // 添田さんの給与をC・Lは35%。試験修理に30%振分(全て標準)
        //$p2_c_urigen = $p2_c_urigen + 151313;   // 添田さんの給与をC・Lは35%。試験修理に30%振分
    }
    // 2013/11/07 追加 2013年10月度 業務委託費（横川派遣料）調整
    if ($p2_ym == 201310) {
        $p2_c_urigen -= 1245035;
    }
    if ($p2_ym == 201311) {
        $p2_c_urigen += 1245035;
    }
    if ($p2_ym == 201408) {
        $p2_c_urigen += 611904;
    }
    $p2_ch_urigen        = $p2_c_urigen - $p2_ctoku_urigen_sagaku;
    $p2_ch_urigen_sagaku = $p2_ch_urigen;
    $p2_ch_urigen        = number_format(($p2_ch_urigen / $tani), $keta);
    $p2_c_urigen         = number_format(($p2_c_urigen / $tani), $keta);
}
    ///// 今期累計
    ///// カプラ特注
$rui_ctoku_urigen        = $rui_ctoku_invent_sagaku + $rui_ctoku_metarial_sagaku + $rui_ctoku_roumu_sagaku + $rui_ctoku_expense_sagaku - $ctoku_endinv_sagaku;
$rui_ctoku_urigen_sagaku = $rui_ctoku_urigen;
$rui_ctoku_urigen        = number_format(($rui_ctoku_urigen / $tani), $keta);
    ///// C
$query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='カプラ売上原価'", $str_ym, $yyyymm);
if (getUniResult($query, $rui_c_urigen) < 1) {
    $rui_c_urigen         = 0;     // 検索失敗
    $rui_ch_urigen        = 0;     // 検索失敗
    $rui_ch_urigen_sagaku = 0;     // 検索失敗
} else {
    $rui_c_urigen         = $rui_c_urigen - $rui_b_sagaku - $rui_sc_metarial_sagaku;    //カプラ試験修理も加味
    if ($yyyymm >= 200912 && $yyyymm <= 201003) {
        $rui_c_urigen = $rui_c_urigen + 1227429;
    }
    if ($yyyymm >= 201001) {
        $rui_c_urigen = $rui_c_urigen + $rui_c_kyu_kin;     // 添田さんの給与をC・Lは35%。試験修理に30%振分
        //$rui_c_urigen = $rui_c_urigen + 151313;    // 添田さんの給与をC・Lは35%。試験修理に30%振分
    }
    // 2013/11/07 追加 2013年10月度 業務委託費（横川派遣料）調整
    if ($yyyymm >= 201310 && $yyyymm <= 201403) {
        $rui_c_urigen -= 1245035;
    }
    if ($yyyymm >= 201311 && $yyyymm <= 201403) {
        $rui_c_urigen += 1245035;
    }
    if ($yyyymm >= 201408 && $yyyymm <= 201503) {
        $rui_c_urigen = $rui_c_urigen + 611904;
    }
    $rui_ch_urigen        = $rui_c_urigen - $rui_ctoku_urigen_sagaku;
    $rui_ch_urigen_sagaku = $rui_ch_urigen;
    $rui_ch_urigen        = number_format(($rui_ch_urigen / $tani), $keta);
    $rui_c_urigen         = number_format(($rui_c_urigen / $tani), $keta);
}

/********** 売上総利益 **********/
    ///// カプラ特注
$p2_ctoku_gross_profit         = $p2_ctoku_uri_sagaku - $p2_ctoku_urigen_sagaku;
$p2_ctoku_gross_profit_sagaku  = $p2_ctoku_gross_profit;
$p2_ctoku_gross_profit         = number_format(($p2_ctoku_gross_profit / $tani), $keta);

$p1_ctoku_gross_profit         = $p1_ctoku_uri_sagaku - $p1_ctoku_urigen_sagaku;
$p1_ctoku_gross_profit_sagaku  = $p1_ctoku_gross_profit;
$p1_ctoku_gross_profit         = number_format(($p1_ctoku_gross_profit / $tani), $keta);

$ctoku_gross_profit            = $ctoku_uri_sagaku - $ctoku_urigen_sagaku;
$ctoku_gross_profit_sagaku     = $ctoku_gross_profit;
$ctoku_gross_profit            = number_format(($ctoku_gross_profit / $tani), $keta);

$rui_ctoku_gross_profit        = $rui_ctoku_uri_sagaku - $rui_ctoku_urigen_sagaku;
$rui_ctoku_gross_profit_sagaku = $rui_ctoku_gross_profit;
$rui_ctoku_gross_profit        = number_format(($rui_ctoku_gross_profit / $tani), $keta);
    ///// 当月
if ( $yyyymm >= 200910) {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='カプラ総利益'", $yyyymm);
    if (getUniResult($query, $c_gross_profit) < 1) {
        $c_gross_profit         = 0;     // 検索失敗
        $ch_gross_profit        = 0;     // 検索失敗
        $ch_gross_profit_sagaku = 0;     // 検索失敗
    } else {
        $c_gross_profit         = $c_gross_profit + $b_sagaku - $sc_uri_sagaku + $sc_metarial_sagaku;    //カプラ試験修理も加味
        if ($yyyymm == 200912) {
            $c_gross_profit = $c_gross_profit - 1227429;
        }
        if ($yyyymm >= 201001) {
            $c_gross_profit = $c_gross_profit - $c_kyu_kin;     // 添田さんの給与をC・Lは35%。試験修理に30%振分(全て標準)
            //$c_gross_profit = $c_gross_profit - 151313;     // 添田さんの給与をC・Lは35%。試験修理に30%振分
        }
        // 2013/11/07 追加 2013年10月度 業務委託費（横川派遣料）調整
        if ($yyyymm == 201310) {
            $c_gross_profit += 1245035;
        }
        if ($yyyymm == 201311) {
            $c_gross_profit -= 1245035;
        }
        if ($yyyymm == 201408) {
            $c_gross_profit -= 611904;
        }
        $ch_gross_profit        = $c_gross_profit - $ctoku_gross_profit_sagaku;
        $ch_gross_profit_sagaku = $ch_gross_profit;
        $ch_gross_profit        = number_format(($ch_gross_profit / $tani), $keta);
        $c_gross_profit         = number_format(($c_gross_profit / $tani), $keta);
    }
} else {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='カプラ総利益'", $yyyymm);
    if (getUniResult($query, $c_gross_profit) < 1) {
        $c_gross_profit         = 0;     // 検索失敗
        $ch_gross_profit        = 0;     // 検索失敗
        $ch_gross_profit_sagaku = 0;     // 検索失敗
    } else {
        $c_gross_profit         = $c_gross_profit + $b_sagaku;
        $ch_gross_profit        = $c_gross_profit - $ctoku_gross_profit_sagaku;
        $ch_gross_profit_sagaku = $ch_gross_profit;
        $ch_gross_profit        = number_format(($ch_gross_profit / $tani), $keta);
        $c_gross_profit         = number_format(($c_gross_profit / $tani), $keta);
    }
}
    ///// 前月
if ( $yyyymm >= 200911) {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='カプラ総利益'", $p1_ym);
    if (getUniResult($query, $p1_c_gross_profit) < 1) {
        $p1_c_gross_profit         = 0;     // 検索失敗
        $p1_ch_gross_profit        = 0;     // 検索失敗
        $p1_ch_gross_profit_sagaku = 0;     // 検索失敗
    } else {
        $p1_c_gross_profit         = $p1_c_gross_profit + $p1_b_sagaku - $p1_sc_uri_sagaku + $p1_sc_metarial_sagaku;    //カプラ試験修理も加味
        if ($p1_ym == 200912) {
            $p1_c_gross_profit = $p1_c_gross_profit - 1227429;
        }
        if ($p1_ym >= 201001) {
            $p1_c_gross_profit = $p1_c_gross_profit - $p1_c_kyu_kin;   // 添田さんの給与をC・Lは35%。試験修理に30%振分(全て標準)
            //$p1_c_gross_profit = $p1_c_gross_profit - 151313;   // 添田さんの給与をC・Lは35%。試験修理に30%振分
        }
        // 2013/11/07 追加 2013年10月度 業務委託費（横川派遣料）調整
        if ($p1_ym == 201310) {
            $p1_c_gross_profit += 1245035;
        }
        if ($p1_ym == 201311) {
            $p1_c_gross_profit -= 1245035;
        }
        if ($p1_ym == 201408) {
            $p1_c_gross_profit -= 611904;
        }
        $p1_ch_gross_profit        = $p1_c_gross_profit - $p1_ctoku_gross_profit_sagaku;
        $p1_ch_gross_profit_sagaku = $p1_ch_gross_profit;
        $p1_ch_gross_profit        = number_format(($p1_ch_gross_profit / $tani), $keta);
        $p1_c_gross_profit         = number_format(($p1_c_gross_profit / $tani), $keta);
    }
} else {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='カプラ総利益'", $p1_ym);
    if (getUniResult($query, $p1_c_gross_profit) < 1) {
        $p1_c_gross_profit         = 0;     // 検索失敗
        $p1_ch_gross_profit        = 0;     // 検索失敗
        $p1_ch_gross_profit_sagaku = 0;     // 検索失敗
    } else {
        $p1_c_gross_profit         = $p1_c_gross_profit + $p1_b_sagaku;
        $p1_ch_gross_profit        = $p1_c_gross_profit - $p1_ctoku_gross_profit_sagaku;
        $p1_ch_gross_profit_sagaku = $p1_ch_gross_profit;
        $p1_ch_gross_profit        = number_format(($p1_ch_gross_profit / $tani), $keta);
        $p1_c_gross_profit         = number_format(($p1_c_gross_profit / $tani), $keta);
    }
}
    ///// 前前月
if ( $yyyymm >= 200912) {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='カプラ総利益'", $p2_ym);
    if (getUniResult($query, $p2_c_gross_profit) < 1) {
        $p2_c_gross_profit         = 0;     // 検索失敗
        $p2_ch_gross_profit        = 0;     // 検索失敗
        $p2_ch_gross_profit_sagaku = 0;     // 検索失敗
    } else {
        $p2_c_gross_profit         = $p2_c_gross_profit + $p2_b_sagaku - $p2_sc_uri_sagaku + $p2_sc_metarial_sagaku;    //カプラ試験修理も加味
        if ($p2_ym == 200912) {
            $p2_c_gross_profit = $p2_c_gross_profit - 1227429;
        }
        if ($p2_ym >= 201001) {
            $p2_c_gross_profit = $p2_c_gross_profit - $p2_c_kyu_kin;   // 添田さんの給与をC・Lは35%。試験修理に30%振分(すべて標準)
            //$p2_c_gross_profit = $p2_c_gross_profit - 151313;   // 添田さんの給与をC・Lは35%。試験修理に30%振分
        }
        // 2013/11/07 追加 2013年10月度 業務委託費（横川派遣料）調整
        if ($p2_ym == 201310) {
            $p2_c_gross_profit += 1245035;
        }
        if ($p2_ym == 201311) {
            $p2_c_gross_profit -= 1245035;
        }
        if ($p2_ym == 201408) {
            $p2_c_gross_profit -= 611904;
        }
        $p2_ch_gross_profit        = $p2_c_gross_profit - $p2_ctoku_gross_profit_sagaku;
        $p2_ch_gross_profit_sagaku = $p2_ch_gross_profit;
        $p2_ch_gross_profit        = number_format(($p2_ch_gross_profit / $tani), $keta);
        $p2_c_gross_profit         = number_format(($p2_c_gross_profit / $tani), $keta);
    }
} else {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='カプラ総利益'", $p2_ym);
    if (getUniResult($query, $p2_c_gross_profit) < 1) {
        $p2_c_gross_profit         = 0;     // 検索失敗
        $p2_ch_gross_profit        = 0;     // 検索失敗
        $p2_ch_gross_profit_sagaku = 0;     // 検索失敗
    } else {
        $p2_c_gross_profit         = $p2_c_gross_profit + $p2_b_sagaku - $p2_sc_uri_sagaku + $p2_sc_metarial_sagaku;    //カプラ試験修理も加味
        $p2_ch_gross_profit        = $p2_c_gross_profit - $p2_ctoku_gross_profit_sagaku;
        $p2_ch_gross_profit_sagaku = $p2_ch_gross_profit;
        $p2_ch_gross_profit        = number_format(($p2_ch_gross_profit / $tani), $keta);
        $p2_c_gross_profit         = number_format(($p2_c_gross_profit / $tani), $keta);
    }
}
    ///// 今期累計
if ( $yyyymm >= 200910) {
    $query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='カプラ総利益'", $str_ym, $yyyymm);
    if (getUniResult($query, $rui_c_gross_profit) < 1) {
        $rui_c_gross_profit         = 0;    // 検索失敗
        $rui_ch_gross_profit        = 0;    // 検索失敗
        $rui_ch_gross_profit_sagaku = 0;    // 検索失敗
    } else {
        $rui_c_gross_profit         = $rui_c_gross_profit + $rui_b_sagaku - $rui_sc_uri_sagaku + $rui_sc_metarial_sagaku;    //カプラ試験修理も加味
        if ($yyyymm >= 200912 && $yyyymm <= 201003) {
            $rui_c_gross_profit = $rui_c_gross_profit - 1227429;
        }
        if ($yyyymm >= 201001) {
            $rui_c_gross_profit = $rui_c_gross_profit - $rui_c_kyu_kin;     // 添田さんの給与をC・Lは35%。試験修理に30%振分
            //$rui_c_gross_profit = $rui_c_gross_profit - 151313;     // 添田さんの給与をC・Lは35%。試験修理に30%振分
        }
        // 2013/11/07 追加 2013年10月度 業務委託費（横川派遣料）調整
        if ($yyyymm >= 201310 && $yyyymm <= 201403) {
            $rui_c_gross_profit += 1245035;
        }
        if ($yyyymm >= 201311 && $yyyymm <= 201403) {
            $rui_c_gross_profit -= 1245035;
        }
        if ($yyyymm >= 201408 && $yyyymm <= 201503) {
            $rui_c_gross_profit = $rui_c_gross_profit - 611904;
        }
        $rui_ch_gross_profit        = $rui_c_gross_profit - $rui_ctoku_gross_profit_sagaku;
        $rui_ch_gross_profit_sagaku = $rui_ch_gross_profit;
        $rui_ch_gross_profit        = number_format(($rui_ch_gross_profit / $tani), $keta);
        $rui_c_gross_profit         = number_format(($rui_c_gross_profit / $tani), $keta);
    }
} else {
    $query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='カプラ総利益'", $str_ym, $yyyymm);
    if (getUniResult($query, $rui_c_gross_profit) < 1) {
        $rui_c_gross_profit         = 0;    // 検索失敗
        $rui_ch_gross_profit        = 0;    // 検索失敗
        $rui_ch_gross_profit_sagaku = 0;    // 検索失敗
    } else {
        $rui_c_gross_profit         = $rui_c_gross_profit + $rui_b_sagaku;
        $rui_ch_gross_profit        = $rui_c_gross_profit - $rui_ctoku_gross_profit_sagaku;
        $rui_ch_gross_profit_sagaku = $rui_ch_gross_profit;
        $rui_ch_gross_profit        = number_format(($rui_ch_gross_profit / $tani), $keta);
        $rui_c_gross_profit         = number_format(($rui_c_gross_profit / $tani), $keta);
    }
}

/********** 販管費の人件費 **********/
    ///// 当月
    // 商品管理（カプラに暫定的に入れているため）
    // 下は7月未払い給与分
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='商管人件費'", $yyyymm);
if (getUniResult($query, $b_han_jin_sagaku) < 1) {
    $b_han_jin_sagaku = 0;      // 検索失敗
}
$query = sprintf("select sum(orign_kin) from act_allo_history where pl_bs_ym=%d and actcod>=8101 and orign_id=670", $yyyymm);
if (getUniResult($query, $b_han_jin) < 1) {
    $b_han_jin        = 0;      // カプラ差額計算用
} else {
    $b_han_jin        = $b_han_jin + $b_han_jin_sagaku;
}
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='カプラ特注人件費'", $yyyymm);
if (getUniResult($query, $ctoku_han_jin) < 1) {
    $ctoku_han_jin        = 0;  // 検索失敗
    $ctoku_han_jin_sagaku = 0;
} else {
    $ctoku_han_jin_sagaku = $ctoku_han_jin;
    $ctoku_han_jin        = number_format(($ctoku_han_jin / $tani), $keta);
}
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='カプラ商管社員按分給与'", $yyyymm);
if (getUniResult($query, $c_allo_kin) < 1) {
    $c_allo_kin           = 0;  // 検索失敗
} else {
    //$c_han_jin = number_format(($c_han_jin / $tani), $keta);
}
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='カプラ人件費'", $yyyymm);
if (getUniResult($query, $c_han_jin) < 1) {
    $c_han_jin         = 0;     // 検索失敗
    $ch_han_jin        = 0;     // 検索失敗
    $ch_han_jin_sagaku = 0;     // 検索失敗
} else {
    $c_han_jin         = $c_han_jin - $b_han_jin - $c_allo_kin;
    $ch_han_jin        = $c_han_jin - $ctoku_han_jin_sagaku;
    $ch_han_jin_sagaku = $ch_han_jin;
    $ch_han_jin        = number_format(($ch_han_jin / $tani), $keta);
    $c_han_jin         = number_format(($c_han_jin / $tani), $keta);
}
    ///// 前月
    // 商品管理（カプラに暫定的に入れているため）
    // 下は7月未払い給与分
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='商管人件費'", $p1_ym);
if (getUniResult($query, $p1_b_han_jin_sagaku) < 1) {
    $p1_b_han_jin_sagaku = 0;   // 検索失敗
}
$query = sprintf("select sum(orign_kin) from act_allo_history where pl_bs_ym=%d and actcod>=8101 and orign_id=670", $p1_ym);
if (getUniResult($query, $p1_b_han_jin) < 1) {
    $p1_b_han_jin = 0;          // カプラ差額計算用
} else {
    $p1_b_han_jin = $p1_b_han_jin + $p1_b_han_jin_sagaku;
}
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='カプラ特注人件費'", $p1_ym);
if (getUniResult($query, $p1_ctoku_han_jin) < 1) {
    $p1_ctoku_han_jin        = 0;   // 検索失敗
    $p1_ctoku_han_jin_sagaku = 0;
} else {
    $p1_ctoku_han_jin_sagaku = $p1_ctoku_han_jin;
    $p1_ctoku_han_jin        = number_format(($p1_ctoku_han_jin / $tani), $keta);
}
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='カプラ商管社員按分給与'", $p1_ym);
if (getUniResult($query, $p1_c_allo_kin) < 1) {
    $p1_c_allo_kin = 0;             // 検索失敗
} else {
    //$c_han_jin = number_format(($c_han_jin / $tani), $keta);
}
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='カプラ人件費'", $p1_ym);
if (getUniResult($query, $p1_c_han_jin) < 1) {
    $p1_c_han_jin         = 0;      // 検索失敗
    $p1_ch_han_jin        = 0;      // 検索失敗
    $p1_ch_han_jin_sagaku = 0;      // 検索失敗
} else {
    $p1_c_han_jin         = $p1_c_han_jin - $p1_b_han_jin - $p1_c_allo_kin;
    $p1_ch_han_jin        = $p1_c_han_jin - $p1_ctoku_han_jin_sagaku;
    $p1_ch_han_jin_sagaku = $p1_ch_han_jin;
    $p1_ch_han_jin        = number_format(($p1_ch_han_jin / $tani), $keta);
    $p1_c_han_jin         = number_format(($p1_c_han_jin / $tani), $keta);
}
    ///// 前前月
    // 商品管理（カプラに暫定的に入れているため）
    // 下は7月未払い給与分
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='商管人件費'", $p2_ym);
if (getUniResult($query, $p2_b_han_jin_sagaku) < 1) {
    $p2_b_han_jin_sagaku = 0;       // 検索失敗
}
$query = sprintf("select sum(orign_kin) from act_allo_history where pl_bs_ym=%d and actcod>=8101 and orign_id=670", $p2_ym);
if (getUniResult($query, $p2_b_han_jin) < 1) {
    $p2_b_han_jin = 0;              // カプラ差額計算用
} else {
    $p2_b_han_jin = $p2_b_han_jin + $p2_b_han_jin_sagaku;
}
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='カプラ特注人件費'", $p2_ym);
if (getUniResult($query, $p2_ctoku_han_jin) < 1) {
    $p2_ctoku_han_jin        = 0;   // 検索失敗
    $p2_ctoku_han_jin_sagaku = 0;
} else {
    $p2_ctoku_han_jin_sagaku = $p2_ctoku_han_jin;
    $p2_ctoku_han_jin        = number_format(($p2_ctoku_han_jin / $tani), $keta);
}
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='カプラ商管社員按分給与'", $p2_ym);
if (getUniResult($query, $p2_c_allo_kin) < 1) {
    $p2_c_allo_kin = 0;             // 検索失敗
} else {
    //$c_han_jin = number_format(($c_han_jin / $tani), $keta);
}
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='カプラ人件費'", $p2_ym);
if (getUniResult($query, $p2_c_han_jin) < 1) {
    $p2_c_han_jin         = 0;      // 検索失敗
    $p2_ch_han_jin        = 0;      // 検索失敗
    $p2_ch_han_jin_sagaku = 0;      // 検索失敗
} else {
    $p2_c_han_jin         = $p2_c_han_jin - $p2_b_han_jin - $p2_c_allo_kin;
    $p2_ch_han_jin        = $p2_c_han_jin - $p2_ctoku_han_jin_sagaku;
    $p2_ch_han_jin_sagaku = $p2_ch_han_jin;
    $p2_ch_han_jin        = number_format(($p2_ch_han_jin / $tani), $keta);
    $p2_c_han_jin         = number_format(($p2_c_han_jin / $tani), $keta);
}
    ///// 今期累計
    // 商品管理（カプラに暫定的に入れているため）
    // 下は7月未払い給与分
$query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='商管人件費'", $str_ym, $yyyymm);
if (getUniResult($query, $rui_b_han_jin_sagaku) < 1) {
    $rui_b_han_jin_sagaku = 0;      // 検索失敗
}
$query = sprintf("select sum(orign_kin) from act_allo_history where pl_bs_ym>=%d and pl_bs_ym<=%d and actcod>=8101 and orign_id=670", $str_ym, $yyyymm);
if (getUniResult($query, $rui_b_han_jin) < 1) {
    $rui_b_han_jin = 0;             // カプラ差額計算用
} else {
    // 下は7月未払い旧余分追加 テスト用
    $rui_b_han_jin = $rui_b_han_jin + $rui_b_han_jin_sagaku;
}
$query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='カプラ特注人件費'", $str_ym, $yyyymm);
if (getUniResult($query, $rui_ctoku_han_jin) < 1) {
    $rui_ctoku_han_jin        = 0;  // 検索失敗
    $rui_ctoku_han_jin_sagaku = 0;
} else {
    $rui_ctoku_han_jin_sagaku = $rui_ctoku_han_jin;
    $rui_ctoku_han_jin        = number_format(($rui_ctoku_han_jin / $tani), $keta);
}
$query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='カプラ商管社員按分給与'", $str_ym, $yyyymm);
if (getUniResult($query, $rui_c_allo_kin) < 1) {
    $rui_c_allo_kin = 0;            // 検索失敗
} else {
    //$c_han_jin = number_format(($c_han_jin / $tani), $keta);
}
$query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='カプラ人件費'", $str_ym, $yyyymm);
if (getUniResult($query, $rui_c_han_jin) < 1) {
    $rui_c_han_jin         = 0;     // 検索失敗
    $rui_ch_han_jin        = 0;     // 検索失敗
    $rui_ch_han_jin_sagaku = 0;     // 検索失敗
} else {
    $rui_c_han_jin         = $rui_c_han_jin - $rui_b_han_jin - $rui_c_allo_kin;
    $rui_ch_han_jin        = $rui_c_han_jin - $rui_ctoku_han_jin_sagaku;
    $rui_ch_han_jin_sagaku = $rui_ch_han_jin;
    $rui_ch_han_jin        = number_format(($rui_ch_han_jin / $tani), $keta);
    $rui_c_han_jin         = number_format(($rui_c_han_jin / $tani), $keta);
}

/********** 販管費の経費 **********/
    ///// 当月
    // 商品管理（カプラに暫定的に入れているため）
$query = sprintf("select sum(orign_kin) from act_allo_history where pl_bs_ym=%d and actcod>=7501 and actcod<=8000 and orign_id=670", $yyyymm);
if (getUniResult($query, $b_han_kei) < 1) {
    $b_han_kei =0;                  // カプラ差額計算用
}
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='カプラ特注販管費経費'", $yyyymm);
if (getUniResult($query, $ctoku_han_kei) < 1) {
    $ctoku_han_kei        = 0;      // 検索失敗
    $ctoku_han_kei_sagaku = 0;
} else {
    $ctoku_han_kei_sagaku = $ctoku_han_kei;
    $ctoku_han_kei        = number_format(($ctoku_han_kei / $tani), $keta);
}
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='カプラ経費'", $yyyymm);
if (getUniResult($query, $c_han_kei) < 1) {
    $c_han_kei         = 0;         // 検索失敗
    $ch_han_kei        = 0;         // 検索失敗
    $ch_han_kei_sagaku = 0;         // 検索失敗
} else {
    $c_han_kei         = $c_han_kei - $b_han_kei;
    $ch_han_kei        = $c_han_kei - $ctoku_han_kei_sagaku;
    $ch_han_kei_sagaku = $ch_han_kei;
    $ch_han_kei        = number_format(($ch_han_kei / $tani), $keta);
    $c_han_kei         = number_format(($c_han_kei / $tani), $keta);
}
    ///// 前月
    // 商品管理（カプラに暫定的に入れているため）
$query = sprintf("select sum(orign_kin) from act_allo_history where pl_bs_ym=%d and actcod>=7501 and actcod<=8000 and orign_id=670", $p1_ym);
if (getUniResult($query, $p1_b_han_kei) < 1) {
    $p1_b_han_kei = 0;              // カプラ差額計算用
}
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='カプラ特注販管費経費'", $p1_ym);
if (getUniResult($query, $p1_ctoku_han_kei) < 1) {
    $p1_ctoku_han_kei        = 0;   // 検索失敗
    $p1_ctoku_han_kei_sagaku = 0;
} else {
    $p1_ctoku_han_kei_sagaku = $p1_ctoku_han_kei;
    $p1_ctoku_han_kei        = number_format(($p1_ctoku_han_kei / $tani), $keta);
}
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='カプラ経費'", $p1_ym);
if (getUniResult($query, $p1_c_han_kei) < 1) {
    $p1_c_han_kei         = 0;     // 検索失敗
    $p1_ch_han_kei        = 0;     // 検索失敗
    $p1_ch_han_kei_sagaku = 0;     // 検索失敗
} else {
    $p1_c_han_kei         = $p1_c_han_kei - $p1_b_han_kei;
    $p1_ch_han_kei        = $p1_c_han_kei - $p1_ctoku_han_kei_sagaku;
    $p1_ch_han_kei_sagaku = $p1_ch_han_kei;
    $p1_ch_han_kei        = number_format(($p1_ch_han_kei / $tani), $keta);
    $p1_c_han_kei         = number_format(($p1_c_han_kei / $tani), $keta);
}
    ///// 前前月
    // 商品管理（カプラに暫定的に入れているため）
$query = sprintf("select sum(orign_kin) from act_allo_history where pl_bs_ym=%d and actcod>=7501 and actcod<=8000 and orign_id=670", $p2_ym);
if (getUniResult($query, $p2_b_han_kei) < 1) {
    $p2_b_han_kei = 0;             // カプラ差額計算用
}
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='カプラ特注販管費経費'", $p2_ym);
if (getUniResult($query, $p2_ctoku_han_kei) < 1) {
    $p2_ctoku_han_kei        = 0;  // 検索失敗
    $p2_ctoku_han_kei_sagaku = 0;
} else {
    $p2_ctoku_han_kei_sagaku = $p2_ctoku_han_kei;
    $p2_ctoku_han_kei = number_format(($p2_ctoku_han_kei / $tani), $keta);
}
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='カプラ経費'", $p2_ym);
if (getUniResult($query, $p2_c_han_kei) < 1) {
    $p2_c_han_kei         = 0;     // 検索失敗
    $p2_ch_han_kei        = 0;     // 検索失敗
    $p2_ch_han_kei_sagaku = 0;     // 検索失敗
} else {
    $p2_c_han_kei         = $p2_c_han_kei - $p2_b_han_kei;
    $p2_ch_han_kei        = $p2_c_han_kei - $p2_ctoku_han_kei_sagaku;
    $p2_ch_han_kei_sagaku = $p2_ch_han_kei;
    $p2_ch_han_kei        = number_format(($p2_ch_han_kei / $tani), $keta);
    $p2_c_han_kei         = number_format(($p2_c_han_kei / $tani), $keta);
}
    ///// 今期累計
    // 商品管理（カプラに暫定的に入れているため）
$query = sprintf("select sum(orign_kin) from act_allo_history where pl_bs_ym>=%d and pl_bs_ym<=%d and actcod>=7501 and actcod<=8000 and orign_id=670", $str_ym, $yyyymm);
if (getUniResult($query, $rui_b_han_kei) < 1) {
    $rui_b_han_kei = 0;
}
$query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='カプラ特注販管費経費'", $str_ym, $yyyymm);
if (getUniResult($query, $rui_ctoku_han_kei) < 1) {
    $rui_ctoku_han_kei        = 0; // 検索失敗
    $rui_ctoku_han_kei_sagaku = 0;
} else {
    $rui_ctoku_han_kei_sagaku = $rui_ctoku_han_kei;
    $rui_ctoku_han_kei        = number_format(($rui_ctoku_han_kei / $tani), $keta);
}
$query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='カプラ経費'", $str_ym, $yyyymm);
if (getUniResult($query, $rui_c_han_kei) < 1) {
    $rui_c_han_kei         = 0;    // 検索失敗
    $rui_ch_han_kei        = 0;    // 検索失敗
    $rui_ch_han_kei_sagaku = 0;    // 検索失敗
} else {
    $rui_c_han_kei         = $rui_c_han_kei - $rui_b_han_kei;
    $rui_ch_han_kei        = $rui_c_han_kei - $rui_ctoku_han_kei_sagaku;
    $rui_ch_han_kei_sagaku = $rui_ch_han_kei;
    $rui_ch_han_kei        = number_format(($rui_ch_han_kei / $tani), $keta);
    $rui_c_han_kei         = number_format(($rui_c_han_kei / $tani), $keta);
}

/********** 販管費の合計 **********/
    ///// 当月
    ///// カプラ特注
$ctoku_han_all        = $ctoku_han_jin_sagaku + $ctoku_han_kei_sagaku;
$ctoku_han_all_sagaku = $ctoku_han_all;
$ctoku_han_all        = number_format(($ctoku_han_all / $tani), $keta);
    ///// C
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='カプラ販管費'", $yyyymm);
if (getUniResult($query, $c_han_all) < 1) {
    $c_han_all         = 0;     // 検索失敗
    $ch_han_all        = 0;     // 検索失敗
    $ch_han_all_sagaku = 0;     // 検索失敗
} else {
    $c_han_all         = $c_han_all - $b_han_jin - $b_han_kei - $c_allo_kin;
    $ch_han_all        = $c_han_all - $ctoku_han_all_sagaku;
    $ch_han_all_sagaku = $ch_han_all;
    $ch_han_all        = number_format(($ch_han_all / $tani), $keta);
    $c_han_all         = number_format(($c_han_all / $tani), $keta);
}
    ///// 前月
    ///// カプラ特注
$p1_ctoku_han_all        = $p1_ctoku_han_jin_sagaku + $p1_ctoku_han_kei_sagaku;
$p1_ctoku_han_all_sagaku = $p1_ctoku_han_all;
$p1_ctoku_han_all        = number_format(($p1_ctoku_han_all / $tani), $keta);
    ///// C
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='カプラ販管費'", $p1_ym);
if (getUniResult($query, $p1_c_han_all) < 1) {
    $p1_c_han_all         = 0;  // 検索失敗
    $p1_ch_han_all        = 0;  // 検索失敗
    $p1_ch_han_all_sagaku = 0;  // 検索失敗
} else {
    $p1_c_han_all         = $p1_c_han_all - $p1_b_han_jin - $p1_b_han_kei - $p1_c_allo_kin;
    $p1_ch_han_all        = $p1_c_han_all - $p1_ctoku_han_all_sagaku;
    $p1_ch_han_all_sagaku = $p1_ch_han_all;
    $p1_ch_han_all        = number_format(($p1_ch_han_all / $tani), $keta);
    $p1_c_han_all         = number_format(($p1_c_han_all / $tani), $keta);
}
    ///// 前前月
    ///// カプラ特注
$p2_ctoku_han_all        = $p2_ctoku_han_jin_sagaku + $p2_ctoku_han_kei_sagaku;
$p2_ctoku_han_all_sagaku = $p2_ctoku_han_all;
$p2_ctoku_han_all        = number_format(($p2_ctoku_han_all / $tani), $keta);
    ///// C
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='カプラ販管費'", $p2_ym);
if (getUniResult($query, $p2_c_han_all) < 1) {
    $p2_c_han_all         = 0;  // 検索失敗
    $p2_ch_han_all        = 0;  // 検索失敗
    $p2_ch_han_all_sagaku = 0;  // 検索失敗
} else {
    $p2_c_han_all         = $p2_c_han_all - $p2_b_han_jin - $p2_b_han_kei - $p2_c_allo_kin;
    $p2_ch_han_all        = $p2_c_han_all - $p2_ctoku_han_all_sagaku;
    $p2_ch_han_all_sagaku = $p2_ch_han_all;
    $p2_ch_han_all        = number_format(($p2_ch_han_all / $tani), $keta);
    $p2_c_han_all         = number_format(($p2_c_han_all / $tani), $keta);
}
    ///// 今期累計
    ///// カプラ特注
$rui_ctoku_han_all        = $rui_ctoku_han_jin_sagaku + $rui_ctoku_han_kei_sagaku;
$rui_ctoku_han_all_sagaku = $rui_ctoku_han_all;
$rui_ctoku_han_all        = number_format(($rui_ctoku_han_all / $tani), $keta);
    ///// CL
$query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='カプラ販管費'", $str_ym, $yyyymm);
if (getUniResult($query, $rui_c_han_all) < 1) {
    $rui_c_han_all         = 0;     // 検索失敗
    $rui_ch_han_all        = 0;     // 検索失敗
    $rui_ch_han_all_sagaku = 0;     // 検索失敗
} else {
    $rui_c_han_all         = $rui_c_han_all - $rui_b_han_jin - $rui_b_han_kei - $rui_c_allo_kin;
    $rui_ch_han_all        = $rui_c_han_all - $rui_ctoku_han_all_sagaku;
    $rui_ch_han_all_sagaku = $rui_ch_han_all;
    $rui_ch_han_all        = number_format(($rui_ch_han_all / $tani), $keta);
    $rui_c_han_all         = number_format(($rui_c_han_all / $tani), $keta);
}

///////// 商品管理分の差額計算（労務費＋製造経費＋販管費人件費＋販管費経費）
$b_sagaku     = $b_sagaku + $b_han_jin + $b_han_kei;
$p1_b_sagaku  = $p1_b_sagaku + $p1_b_han_jin + $p1_b_han_kei;
$p2_b_sagaku  = $p2_b_sagaku + $p2_b_han_jin + $p2_b_han_kei;
$rui_b_sagaku = $rui_b_sagaku + $rui_b_han_jin + $rui_b_han_kei;
/********** 営業利益 **********/
    ///// カプラ特注
$p2_ctoku_ope_profit         = $p2_ctoku_gross_profit_sagaku - $p2_ctoku_han_all_sagaku;
$p2_ctoku_ope_profit_sagaku  = $p2_ctoku_ope_profit;
$p2_ctoku_ope_profit         = number_format(($p2_ctoku_ope_profit / $tani), $keta);

$p1_ctoku_ope_profit         = $p1_ctoku_gross_profit_sagaku - $p1_ctoku_han_all_sagaku;
$p1_ctoku_ope_profit_sagaku  = $p1_ctoku_ope_profit;
$p1_ctoku_ope_profit         = number_format(($p1_ctoku_ope_profit / $tani), $keta);

$ctoku_ope_profit            = $ctoku_gross_profit_sagaku - $ctoku_han_all_sagaku;
$ctoku_ope_profit_sagaku     = $ctoku_ope_profit;
$ctoku_ope_profit            = number_format(($ctoku_ope_profit / $tani), $keta);

$rui_ctoku_ope_profit        = $rui_ctoku_gross_profit_sagaku - $rui_ctoku_han_all_sagaku;
$rui_ctoku_ope_profit_sagaku = $rui_ctoku_ope_profit;
$rui_ctoku_ope_profit        = number_format(($rui_ctoku_ope_profit / $tani), $keta);

    ///// 当月
if ( $yyyymm >= 200910) {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='カプラ営業利益'", $yyyymm);
    if (getUniResult($query, $c_ope_profit) < 1) {
        $c_ope_profit         = 0;      // 検索失敗
        $ch_ope_profit        = 0;      // 検索失敗
        $ch_ope_profit_sagaku = 0;      // 検索失敗
        $c_ope_profit_temp    = 0;
    } else {
        $c_ope_profit         = $c_ope_profit + $b_sagaku + $c_allo_kin - $sc_uri_sagaku + $sc_metarial_sagaku;    //カプラ試験修理も加味
        if ($yyyymm == 200912) {
            $c_ope_profit = $c_ope_profit - 1227429;
        }
        if ($yyyymm >= 201001) {
            $c_ope_profit = $c_ope_profit - $c_kyu_kin; // 添田さんの給与をC・Lは35%。試験修理に30%振分
            //$c_ope_profit = $c_ope_profit - 151313; // 添田さんの給与をC・Lは35%。試験修理に30%振分
        }
        // 2013/11/07 追加 2013年10月度 業務委託費（横川派遣料）調整
        if ($yyyymm == 201310) {
            $c_ope_profit += 1245035;
        }
        if ($yyyymm == 201311) {
            $c_ope_profit -= 1245035;
        }
        if ($yyyymm == 201408) {
            $c_ope_profit -=611904;
        }
        $c_ope_profit_temp    = $c_ope_profit;
        $ch_ope_profit        = $c_ope_profit - $ctoku_ope_profit_sagaku;
        $ch_ope_profit_sagaku = $ch_ope_profit;
        $ch_ope_profit        = number_format(($ch_ope_profit / $tani), $keta);
        $c_ope_profit         = number_format(($c_ope_profit / $tani), $keta);
    }
} else {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='カプラ営業利益'", $yyyymm);
    if (getUniResult($query, $c_ope_profit) < 1) {
        $c_ope_profit         = 0;      // 検索失敗
        $ch_ope_profit        = 0;      // 検索失敗
        $ch_ope_profit_sagaku = 0;      // 検索失敗
        $c_ope_profit_temp    = 0;
    } else {
        $c_ope_profit         = $c_ope_profit + $b_sagaku + $c_allo_kin;
        $c_ope_profit_temp    = $c_ope_profit;
        $ch_ope_profit        = $c_ope_profit - $ctoku_ope_profit_sagaku;
        $ch_ope_profit_sagaku = $ch_ope_profit;
        $ch_ope_profit        = number_format(($ch_ope_profit / $tani), $keta);
        $c_ope_profit         = number_format(($c_ope_profit / $tani), $keta);
    }
}
    ///// 前月
if ( $yyyymm >= 200911) {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='カプラ営業利益'", $p1_ym);
    if (getUniResult($query, $p1_c_ope_profit) < 1) {
        $p1_c_ope_profit         = 0;   // 検索失敗
        $p1_ch_ope_profit        = 0;   // 検索失敗
        $p1_ch_ope_profit_sagaku = 0;   // 検索失敗
        $p1_c_ope_profit_temp    = 0;
    } else {
        $p1_c_ope_profit         = $p1_c_ope_profit + $p1_b_sagaku + $p1_c_allo_kin - $p1_sc_uri_sagaku + $p1_sc_metarial_sagaku;    //カプラ試験修理も加味
        if ($p1_ym == 200912) {
            $p1_c_ope_profit = $p1_c_ope_profit - 1227429;
        }
        if ($p1_ym >= 201001) {
            $p1_c_ope_profit = $p1_c_ope_profit - $p1_c_kyu_kin;   // 添田さんの給与をC・Lは35%。試験修理に30%振分
            //$p1_c_ope_profit = $p1_c_ope_profit - 151313;   // 添田さんの給与をC・Lは35%。試験修理に30%振分
        }
        // 2013/11/07 追加 2013年10月度 業務委託費（横川派遣料）調整
        if ($p1_ym == 201310) {
            $p1_c_ope_profit += 1245035;
        }
        if ($p1_ym == 201311) {
            $p1_c_ope_profit -= 1245035;
        }
        if ($p1_ym == 201408) {
            $p1_c_ope_profit -=611904;
        }
        $p1_c_ope_profit_temp    = $p1_c_ope_profit;
        $p1_ch_ope_profit        = $p1_c_ope_profit - $p1_ctoku_ope_profit_sagaku;
        $p1_ch_ope_profit_sagaku = $p1_ch_ope_profit;
        $p1_ch_ope_profit        = number_format(($p1_ch_ope_profit / $tani), $keta);
        $p1_c_ope_profit         = number_format(($p1_c_ope_profit / $tani), $keta);
    }
} else {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='カプラ営業利益'", $p1_ym);
    if (getUniResult($query, $p1_c_ope_profit) < 1) {
        $p1_c_ope_profit         = 0;   // 検索失敗
        $p1_ch_ope_profit        = 0;   // 検索失敗
        $p1_ch_ope_profit_sagaku = 0;   // 検索失敗
        $p1_c_ope_profit_temp    = 0;
    } else {
        $p1_c_ope_profit         = $p1_c_ope_profit + $p1_b_sagaku + $p1_c_allo_kin;
        $p1_c_ope_profit_temp    = $p1_c_ope_profit;
        $p1_ch_ope_profit        = $p1_c_ope_profit - $p1_ctoku_ope_profit_sagaku;
        $p1_ch_ope_profit_sagaku = $p1_ch_ope_profit;
        $p1_ch_ope_profit        = number_format(($p1_ch_ope_profit / $tani), $keta);
        $p1_c_ope_profit         = number_format(($p1_c_ope_profit / $tani), $keta);
    }
}
    ///// 前前月
if ( $yyyymm >= 200912) {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='カプラ営業利益'", $p2_ym);
    if (getUniResult($query, $p2_c_ope_profit) < 1) {
        $p2_c_ope_profit         = 0;   // 検索失敗
        $p2_ch_ope_profit        = 0;   // 検索失敗
        $p2_ch_ope_profit_sagaku = 0;   // 検索失敗
        $p2_c_ope_profit_temp     = 0;
    } else {
        $p2_c_ope_profit         = $p2_c_ope_profit + $p2_b_sagaku + $p2_c_allo_kin - $p2_sc_uri_sagaku + $p2_sc_metarial_sagaku;    //カプラ試験修理も加味
        if ($p2_ym == 200912) {
            $p2_c_ope_profit = $p2_c_ope_profit - 1227429;
        }
        if ($p2_ym >= 201001) {
            $p2_c_ope_profit = $p2_c_ope_profit - $p2_c_kyu_kin;   // 添田さんの給与をC・Lは35%。試験修理に30%振分
            //$p2_c_ope_profit = $p2_c_ope_profit - 151313;   // 添田さんの給与をC・Lは35%。試験修理に30%振分
        }
        // 2013/11/07 追加 2013年10月度 業務委託費（横川派遣料）調整
        if ($p2_ym == 201310) {
            $p2_c_ope_profit += 1245035;
        }
        if ($p2_ym == 201311) {
            $p2_c_ope_profit -= 1245035;
        }
        if ($p2_ym == 201408) {
            $p2_c_ope_profit -=611904;
        }
        $p2_c_ope_profit_temp    = $p2_c_ope_profit;
        $p2_ch_ope_profit        = $p2_c_ope_profit - $p2_ctoku_ope_profit_sagaku;
        $p2_ch_ope_profit_sagaku = $p2_ch_ope_profit;
        $p2_ch_ope_profit        = number_format(($p2_ch_ope_profit / $tani), $keta);
        $p2_c_ope_profit         = number_format(($p2_c_ope_profit / $tani), $keta);
    }
} else {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='カプラ営業利益'", $p2_ym);
    if (getUniResult($query, $p2_c_ope_profit) < 1) {
        $p2_c_ope_profit         = 0;   // 検索失敗
        $p2_ch_ope_profit        = 0;   // 検索失敗
        $p2_ch_ope_profit_sagaku = 0;   // 検索失敗
        $p2_c_ope_profit_temp    = 0;
    } else {
        $p2_c_ope_profit         = $p2_c_ope_profit + $p2_b_sagaku + $p2_c_allo_kin - $p2_sc_uri_sagaku;
        $p2_c_ope_profit_temp    = $p2_c_ope_profit;
        $p2_ch_ope_profit        = $p2_c_ope_profit - $p2_ctoku_ope_profit_sagaku;
        $p2_ch_ope_profit_sagaku = $p2_ch_ope_profit;
        $p2_ch_ope_profit        = number_format(($p2_ch_ope_profit / $tani), $keta);
        $p2_c_ope_profit         = number_format(($p2_c_ope_profit / $tani), $keta);
    }
}
    ///// 今期累計
if ( $yyyymm >= 200910) {
    $query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='カプラ営業利益'", $str_ym, $yyyymm);
    if (getUniResult($query, $rui_c_ope_profit) < 1) {
        $rui_c_ope_profit         = 0;  // 検索失敗
        $rui_ch_ope_profit        = 0;  // 検索失敗
        $rui_ch_ope_profit_sagaku = 0;  // 検索失敗
        $rui_c_ope_profit_temp    = 0;
    } else {
        $rui_c_ope_profit         = $rui_c_ope_profit + $rui_b_sagaku + $rui_c_allo_kin - $rui_sc_uri_sagaku + $rui_sc_metarial_sagaku;    //カプラ試験修理も加味
        if ($yyyymm >= 200912 && $yyyymm <= 201003) {
            $rui_c_ope_profit = $rui_c_ope_profit - 1227429;
        }
        if ($yyyymm >= 201001) {
            $rui_c_ope_profit = $rui_c_ope_profit - $rui_c_kyu_kin; // 添田さんの給与をC・Lは35%。試験修理に30%振分
            //$rui_c_ope_profit = $rui_c_ope_profit - 151313; // 添田さんの給与をC・Lは35%。試験修理に30%振分
        }
        // 2013/11/07 追加 2013年10月度 業務委託費（横川派遣料）調整
        if ($yyyymm >= 201310 && $yyyymm <= 201403) {
            $rui_c_ope_profit += 1245035;
        }
        if ($yyyymm >= 201311 && $yyyymm <= 201403) {
            $rui_c_ope_profit -= 1245035;
        }
        if ($yyyymm >= 201408 && $yyyymm <= 201503) {
            $rui_c_ope_profit = $rui_c_ope_profit - 611904;
        }
        $rui_c_ope_profit_temp    = $rui_c_ope_profit;
        $rui_ch_ope_profit        = $rui_c_ope_profit - $rui_ctoku_ope_profit_sagaku;
        $rui_ch_ope_profit_sagaku = $rui_ch_ope_profit;
        $rui_ch_ope_profit        = number_format(($rui_ch_ope_profit / $tani), $keta);
        $rui_c_ope_profit         = number_format(($rui_c_ope_profit / $tani), $keta);
    }
} else {
    $query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='カプラ営業利益'", $str_ym, $yyyymm);
    if (getUniResult($query, $rui_c_ope_profit) < 1) {
        $rui_c_ope_profit         = 0;  // 検索失敗
        $rui_ch_ope_profit        = 0;  // 検索失敗
        $rui_ch_ope_profit_sagaku = 0;  // 検索失敗
        $rui_c_ope_profit_temp    = 0;
    } else {
        $rui_c_ope_profit         = $rui_c_ope_profit + $rui_b_sagaku + $rui_c_allo_kin;
        $rui_c_ope_profit_temp    = $rui_c_ope_profit;
        $rui_ch_ope_profit        = $rui_c_ope_profit - $rui_ctoku_ope_profit_sagaku;
        $rui_ch_ope_profit_sagaku = $rui_ch_ope_profit;
        $rui_ch_ope_profit        = number_format(($rui_ch_ope_profit / $tani), $keta);
        $rui_c_ope_profit         = number_format(($rui_c_ope_profit / $tani), $keta);
    }
}

/********** 営業外収益の業務委託収入 **********/
    ///// 当月
if ($yyyymm >= 201001) {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='カプラ特注業務委託収入再計算'", $yyyymm);
} else {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='カプラ特注業務委託収入'", $yyyymm);
}
if (getUniResult($query, $ctoku_gyoumu) < 1) {
    $ctoku_gyoumu         = 0;          // 検索失敗
    $ctoku_gyoumu_sagaku  = 0;          // 検索失敗
} else {
    if ($yyyymm == 200912) {
        $ctoku_gyoumu = $ctoku_gyoumu - 115715;
    }
    if ($yyyymm == 201001) {
        $ctoku_gyoumu = $ctoku_gyoumu + 58247;
    }
    $ctoku_gyoumu_sagaku = $ctoku_gyoumu;
    $ctoku_gyoumu        = number_format(($ctoku_gyoumu / $tani), $keta);
}
if ($yyyymm >= 201001) {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='カプラ業務委託収入再計算'", $yyyymm);
} else {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='カプラ業務委託収入'", $yyyymm);
}
if (getUniResult($query, $c_gyoumu) < 1) {
    $c_gyoumu         = 0;          // 検索失敗
    $ch_gyoumu        = 0;          // 検索失敗
    $ch_gyoumu_sagaku = 0;          // 検索失敗
} else {
    if ($yyyymm == 200912) {
        $c_gyoumu = $c_gyoumu - 389809;
    }
    if ($yyyymm == 201001) {
        $c_gyoumu = $c_gyoumu + 315529;
    }
    $ch_gyoumu        = $c_gyoumu - $ctoku_gyoumu_sagaku;
    $ch_gyoumu_sagaku = $ch_gyoumu;
    $ch_gyoumu        = number_format(($ch_gyoumu / $tani), $keta);
    $c_gyoumu = number_format(($c_gyoumu / $tani), $keta);
}
    ///// 前月
if ($p1_ym >= 201001) {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='カプラ特注業務委託収入再計算'", $p1_ym);
} else {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='カプラ特注業務委託収入'", $p1_ym);
}
if (getUniResult($query, $p1_ctoku_gyoumu) < 1) {
    $p1_ctoku_gyoumu         = 0;          // 検索失敗
    $p1_ctoku_gyoumu_sagaku  = 0;          // 検索失敗
} else {
    if ($p1_ym == 200912) {
        $p1_ctoku_gyoumu = $p1_ctoku_gyoumu - 115715;
    }
    if ($p1_ym == 201001) {
        $p1_ctoku_gyoumu = $p1_ctoku_gyoumu + 58247;
    }
    $p1_ctoku_gyoumu_sagaku = $p1_ctoku_gyoumu;
    $p1_ctoku_gyoumu        = number_format(($p1_ctoku_gyoumu / $tani), $keta);
}
if ($p1_ym >= 201001) {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='カプラ業務委託収入再計算'", $p1_ym);
} else {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='カプラ業務委託収入'", $p1_ym);
}
if (getUniResult($query, $p1_c_gyoumu) < 1) {
    $p1_c_gyoumu         = 0;          // 検索失敗
    $p1_ch_gyoumu        = 0;          // 検索失敗
    $p1_ch_gyoumu_sagaku = 0;          // 検索失敗
} else {
    if ($p1_ym == 200912) {
        $p1_c_gyoumu = $p1_c_gyoumu - 389809;
    }
    if ($p1_ym == 201001) {
        $p1_c_gyoumu = $p1_c_gyoumu + 315529;
    }
    $p1_ch_gyoumu        = $p1_c_gyoumu - $p1_ctoku_gyoumu_sagaku;
    $p1_ch_gyoumu_sagaku = $p1_ch_gyoumu;
    $p1_ch_gyoumu        = number_format(($p1_ch_gyoumu / $tani), $keta);
    $p1_c_gyoumu         = number_format(($p1_c_gyoumu / $tani), $keta);
}
    ///// 前前月
if ($p2_ym >= 201001) {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='カプラ特注業務委託収入再計算'", $p2_ym);
} else {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='カプラ特注業務委託収入'", $p2_ym);
}
if (getUniResult($query, $p2_ctoku_gyoumu) < 1) {
    $p2_ctoku_gyoumu         = 0;          // 検索失敗
    $p2_ctoku_gyoumu_sagaku  = 0;          // 検索失敗
} else {
    if ($p2_ym == 200912) {
        $p2_ctoku_gyoumu = $p2_ctoku_gyoumu - 115715;
    }
    if ($p2_ym == 201001) {
        $p2_ctoku_gyoumu = $p2_ctoku_gyoumu + 58247;
    }
    $p2_ctoku_gyoumu_sagaku = $p2_ctoku_gyoumu;
    $p2_ctoku_gyoumu        = number_format(($p2_ctoku_gyoumu / $tani), $keta);
}
if ($p2_ym >= 201001) {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='カプラ業務委託収入再計算'", $p2_ym);
} else {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='カプラ業務委託収入'", $p2_ym);
}
if (getUniResult($query, $p2_c_gyoumu) < 1) {
    $p2_c_gyoumu         = 0;          // 検索失敗
    $p2_ch_gyoumu        = 0;          // 検索失敗
    $p2_ch_gyoumu_sagaku = 0;          // 検索失敗
} else {
    if ($p2_ym == 200912) {
        $p2_c_gyoumu = $p2_c_gyoumu - 389809;
    }
    if ($p2_ym == 201001) {
        $p2_c_gyoumu = $p2_c_gyoumu + 315529;
    }
    $p2_ch_gyoumu        = $p2_c_gyoumu - $p2_ctoku_gyoumu_sagaku;
    $p2_ch_gyoumu_sagaku = $p2_ch_gyoumu;
    $p2_ch_gyoumu        = number_format(($p2_ch_gyoumu / $tani), $keta);
    $p2_c_gyoumu         = number_format(($p2_c_gyoumu / $tani), $keta);
}
    ///// 今期累計
if ($yyyymm >= 201004) {
    $query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='カプラ特注業務委託収入再計算'", $str_ym, $yyyymm);
    if (getUniResult($query, $rui_ctoku_gyoumu) < 1) {
        $rui_ctoku_gyoumu        = 0;   // 検索失敗
        $rui_ctoku_gyoumu_sagaku = 0;
    } else {
        $rui_ctoku_gyoumu_sagaku = $rui_ctoku_gyoumu;
        $rui_ctoku_gyoumu        = number_format(($rui_ctoku_gyoumu / $tani), $keta);
    }
} elseif ($yyyymm >= 201001 && $yyyymm <= 201003) {
    $query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=200904 and pl_bs_ym<=200912 and note='カプラ特注業務委託収入'");
    if (getUniResult($query, $rui_ctoku_gyoumu_a) < 1) {
        $rui_ctoku_gyoumu_a = 0;                          // 検索失敗
    }
    $query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=201001 and pl_bs_ym<=%d and note='カプラ特注業務委託収入再計算'", $yyyymm);
    if (getUniResult($query, $rui_ctoku_gyoumu_b) < 1) {
        $rui_ctoku_gyoumu_b = 0;                          // 検索失敗
    }
    $rui_ctoku_gyoumu = $rui_ctoku_gyoumu_a + $rui_ctoku_gyoumu_b;
    
    if ($yyyymm >= 200912 && $yyyymm <= 201003) {
        $rui_ctoku_gyoumu = $rui_ctoku_gyoumu - 115715;
    }
    if ($yyyymm >= 201001 && $yyyymm <= 201003) {
        $rui_ctoku_gyoumu = $rui_ctoku_gyoumu + 58247;
    }
    $rui_ctoku_gyoumu_sagaku = $rui_ctoku_gyoumu;
    $rui_ctoku_gyoumu        = number_format(($rui_ctoku_gyoumu / $tani), $keta);
} else {
    $query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='カプラ特注業務委託収入'", $str_ym, $yyyymm);
    if (getUniResult($query, $rui_ctoku_gyoumu) < 1) {
        $rui_ctoku_gyoumu        = 0;   // 検索失敗
        $rui_ctoku_gyoumu_sagaku = 0;
    } else {
        if ($yyyymm >= 200912 && $yyyymm <= 201003) {
            $rui_ctoku_gyoumu = $rui_ctoku_gyoumu - 115715;
        }
        if ($yyyymm >= 201001 && $yyyymm <= 201003) {
            $rui_ctoku_gyoumu = $rui_ctoku_gyoumu + 58247;
        }
        $rui_ctoku_gyoumu_sagaku = $rui_ctoku_gyoumu;
        $rui_ctoku_gyoumu        = number_format(($rui_ctoku_gyoumu / $tani), $keta);
    }
}
if ($yyyymm >= 201004) {
    $query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='カプラ業務委託収入再計算'", $str_ym, $yyyymm);
    if (getUniResult($query, $rui_c_gyoumu) < 1) {
        $rui_c_gyoumu         = 0;      // 検索失敗
        $rui_ch_gyoumu        = 0;      // 検索失敗
        $rui_ch_gyoumu_sagaku = 0;      // 検索失敗
    } else {
        $rui_ch_gyoumu        = $rui_c_gyoumu - $rui_ctoku_gyoumu_sagaku;
        $rui_ch_gyoumu_sagaku = $rui_ch_gyoumu;
        $rui_ch_gyoumu        = number_format(($rui_ch_gyoumu / $tani), $keta);
        $rui_c_gyoumu         = number_format(($rui_c_gyoumu / $tani), $keta);
    }
} elseif ($yyyymm >= 201001 && $yyyymm <= 201003) {
    $query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=200904 and pl_bs_ym<=200912 and note='カプラ業務委託収入'");
    if (getUniResult($query, $rui_c_gyoumu_a) < 1) {
        $rui_c_gyoumu_a = 0;                          // 検索失敗
    }
    $query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=201001 and pl_bs_ym<=%d and note='カプラ業務委託収入再計算'", $yyyymm);
    if (getUniResult($query, $rui_c_gyoumu_b) < 1) {
        $rui_c_gyoumu_b = 0;                          // 検索失敗
    }
    $rui_c_gyoumu = $rui_c_gyoumu_a + $rui_c_gyoumu_b;
    
    if ($yyyymm >= 200912 && $yyyymm <= 201003) {
        $rui_c_gyoumu = $rui_c_gyoumu - 389809;
    }
    if ($yyyymm >= 201001 && $yyyymm <= 201003) {
        $rui_c_gyoumu = $rui_c_gyoumu + 315529;
    }
    $rui_ch_gyoumu        = $rui_c_gyoumu - $rui_ctoku_gyoumu_sagaku;
    $rui_ch_gyoumu_sagaku = $rui_ch_gyoumu;
    $rui_ch_gyoumu        = number_format(($rui_ch_gyoumu / $tani), $keta);
    $rui_c_gyoumu         = number_format(($rui_c_gyoumu / $tani), $keta);
} else {
    $query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='カプラ業務委託収入'", $str_ym, $yyyymm);
    if (getUniResult($query, $rui_c_gyoumu) < 1) {
        $rui_c_gyoumu         = 0;      // 検索失敗
        $rui_ch_gyoumu        = 0;      // 検索失敗
        $rui_ch_gyoumu_sagaku = 0;      // 検索失敗
    } else {
        if ($yyyymm >= 200912 && $yyyymm <= 201003) {
            $rui_c_gyoumu = $rui_c_gyoumu - 389809;
        }
        if ($yyyymm >= 201001 && $yyyymm <= 201003) {
            $rui_c_gyoumu = $rui_c_gyoumu + 315529;
        }
        $rui_ch_gyoumu        = $rui_c_gyoumu - $rui_ctoku_gyoumu_sagaku;
        $rui_ch_gyoumu_sagaku = $rui_ch_gyoumu;
        $rui_ch_gyoumu        = number_format(($rui_ch_gyoumu / $tani), $keta);
        $rui_c_gyoumu         = number_format(($rui_c_gyoumu / $tani), $keta);
    }
}
/********** 営業外収益の仕入割引 **********/
    ///// 当月
if ($yyyymm >= 201001) {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='カプラ特注仕入割引再計算'", $yyyymm);
} else {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='カプラ特注仕入割引'", $yyyymm);
}
if (getUniResult($query, $ctoku_swari) < 1) {
    $ctoku_swari         = 0;           // 検索失敗
    $ctoku_swari_sagaku  = 0;           // 検索失敗
} else {
    $ctoku_swari_sagaku = $ctoku_swari;
    $ctoku_swari        = number_format(($ctoku_swari / $tani), $keta);
}
if ($yyyymm >= 201001) {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='カプラ仕入割引再計算'", $yyyymm);
} else {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='カプラ仕入割引'", $yyyymm);
}
if (getUniResult($query, $c_swari) < 1) {
    $c_swari         = 0;           // 検索失敗
    $ch_swari        = 0;           // 検索失敗
    $ch_swari_sagaku = 0;           // 検索失敗
} else {
    $ch_swari        = $c_swari - $ctoku_swari_sagaku;
    $ch_swari_sagaku = $ch_swari;
    $ch_swari        = number_format(($ch_swari / $tani), $keta);
    $c_swari         = number_format(($c_swari / $tani), $keta);
}
    ///// 前月
if ($p1_ym >= 201001) {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='カプラ特注仕入割引再計算'", $p1_ym);
} else {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='カプラ特注仕入割引'", $p1_ym);
}
if (getUniResult($query, $p1_ctoku_swari) < 1) {
    $p1_ctoku_swari         = 0;           // 検索失敗
    $p1_ctoku_swari_sagaku  = 0;           // 検索失敗
} else {
    $p1_ctoku_swari_sagaku = $p1_ctoku_swari;
    $p1_ctoku_swari        = number_format(($p1_ctoku_swari / $tani), $keta);
}
if ($p1_ym >= 201001) {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='カプラ仕入割引再計算'", $p1_ym);
} else {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='カプラ仕入割引'", $p1_ym);
}
if (getUniResult($query, $p1_c_swari) < 1) {
    $p1_c_swari         = 0;           // 検索失敗
    $p1_ch_swari        = 0;           // 検索失敗
    $p1_ch_swari_sagaku = 0;           // 検索失敗
} else {
    $p1_ch_swari        = $p1_c_swari - $p1_ctoku_swari_sagaku;
    $p1_ch_swari_sagaku = $p1_ch_swari;
    $p1_ch_swari        = number_format(($p1_ch_swari / $tani), $keta);
    $p1_c_swari         = number_format(($p1_c_swari / $tani), $keta);
}
    ///// 前前月
if ($p2_ym >= 201001) {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='カプラ特注仕入割引再計算'", $p2_ym);
} else {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='カプラ特注仕入割引'", $p2_ym);
}
if (getUniResult($query, $p2_ctoku_swari) < 1) {
    $p2_ctoku_swari         = 0;           // 検索失敗
    $p2_ctoku_swari_sagaku  = 0;           // 検索失敗
} else {
    $p2_ctoku_swari_sagaku = $p2_ctoku_swari;
    $p2_ctoku_swari        = number_format(($p2_ctoku_swari / $tani), $keta);
}
if ($p2_ym >= 201001) {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='カプラ仕入割引再計算'", $p2_ym);
} else {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='カプラ仕入割引'", $p2_ym);
}
if (getUniResult($query, $p2_c_swari) < 1) {
    $p2_c_swari         = 0;           // 検索失敗
    $p2_ch_swari        = 0;           // 検索失敗
    $p2_ch_swari_sagaku = 0;           // 検索失敗
} else {
    $p2_ch_swari        = $p2_c_swari - $p2_ctoku_swari_sagaku;
    $p2_ch_swari_sagaku = $p2_ch_swari;
    $p2_ch_swari        = number_format(($p2_ch_swari / $tani), $keta);
    $p2_c_swari         = number_format(($p2_c_swari / $tani), $keta);
}
    ///// 今期累計
if ($yyyymm >= 201004) {
    $query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='カプラ特注仕入割引再計算'", $str_ym, $yyyymm);
    if (getUniResult($query, $rui_ctoku_swari) < 1) {
        $rui_ctoku_swari        = 0;    // 検索失敗
        $rui_ctoku_swari_sagaku = 0;
    } else {
        $rui_ctoku_swari_sagaku = $rui_ctoku_swari;
        $rui_ctoku_swari        = number_format(($rui_ctoku_swari / $tani), $keta);
    }
} elseif ($yyyymm >= 201001 && $yyyymm <= 201003) {
    $query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=200904 and pl_bs_ym<=200912 and note='カプラ特注仕入割引'");
    if (getUniResult($query, $rui_ctoku_swari_a) < 1) {
        $rui_ctoku_swari_a = 0;                          // 検索失敗
    }
    $query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=201001 and pl_bs_ym<=%d and note='カプラ特注仕入割引再計算'", $yyyymm);
    if (getUniResult($query, $rui_ctoku_swari_b) < 1) {
        $rui_ctoku_swari_b = 0;                          // 検索失敗
    }
    $rui_ctoku_swari        = $rui_ctoku_swari_a + $rui_ctoku_swari_b;
    $rui_ctoku_swari_sagaku = $rui_ctoku_swari;
    $rui_ctoku_swari        = number_format(($rui_ctoku_swari / $tani), $keta);
} else {
    $query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='カプラ特注仕入割引'", $str_ym, $yyyymm);
    if (getUniResult($query, $rui_ctoku_swari) < 1) {
        $rui_ctoku_swari        = 0;    // 検索失敗
        $rui_ctoku_swari_sagaku = 0;
    } else {
        $rui_ctoku_swari_sagaku = $rui_ctoku_swari;
        $rui_ctoku_swari        = number_format(($rui_ctoku_swari / $tani), $keta);
    }
}
if ($yyyymm >= 201004) {
    $query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='カプラ仕入割引再計算'", $str_ym, $yyyymm);
    if (getUniResult($query, $rui_c_swari) < 1) {
        $rui_c_swari         = 0;       // 検索失敗
        $rui_ch_swari        = 0;       // 検索失敗
        $rui_ch_swari_sagaku = 0;       // 検索失敗
    } else {
        $rui_ch_swari        = $rui_c_swari - $rui_ctoku_swari_sagaku;
        $rui_ch_swari_sagaku = $rui_ch_swari;
        $rui_ch_swari        = number_format(($rui_ch_swari / $tani), $keta);
        $rui_c_swari         = number_format(($rui_c_swari / $tani), $keta);
    }
} elseif ($yyyymm >= 201001 && $yyyymm <= 201003) {
    $query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=200904 and pl_bs_ym<=200912 and note='カプラ仕入割引'");
    if (getUniResult($query, $rui_c_swari_a) < 1) {
        $rui_c_swari_a = 0;                          // 検索失敗
    }
    $query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=201001 and pl_bs_ym<=%d and note='カプラ仕入割引再計算'", $yyyymm);
    if (getUniResult($query, $rui_c_swari_b) < 1) {
        $rui_c_swari_b = 0;                          // 検索失敗
    }
    $rui_c_swari         = $rui_c_swari_a + $rui_c_swari_b;
    $rui_ch_swari        = $rui_c_swari - $rui_ctoku_swari_sagaku;
    $rui_ch_swari_sagaku = $rui_ch_swari;
    $rui_ch_swari        = number_format(($rui_ch_swari / $tani), $keta);
    $rui_c_swari         = number_format(($rui_c_swari / $tani), $keta);
} else {
    $query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='カプラ仕入割引'", $str_ym, $yyyymm);
    if (getUniResult($query, $rui_c_swari) < 1) {
        $rui_c_swari         = 0;       // 検索失敗
        $rui_ch_swari        = 0;       // 検索失敗
        $rui_ch_swari_sagaku = 0;       // 検索失敗
    } else {
        $rui_ch_swari        = $rui_c_swari - $rui_ctoku_swari_sagaku;
        $rui_ch_swari_sagaku = $rui_ch_swari;
        $rui_ch_swari        = number_format(($rui_ch_swari / $tani), $keta);
        $rui_c_swari         = number_format(($rui_c_swari / $tani), $keta);
    }
}
/********** 営業外収益のその他 **********/
    ///// 当月
    // 商品管理（カプラに暫定的に入れているため）
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='商管営業外収益その他'", $yyyymm);
if (getUniResult($query, $b_pother) < 1) {
    $b_pother = 0;                  // 検索失敗
}
if ($yyyymm >= 201001) {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='カプラ特注営業外収益その他再計算'", $yyyymm);
} else {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='カプラ特注営業外収益その他'", $yyyymm);
}
if (getUniResult($query, $ctoku_pother) < 1) {
    $ctoku_pother         = 0;          // 検索失敗
    $ctoku_pother_sagaku  = 0;          // 検索失敗
} else {
    if ($yyyymm == 200912) {
        $ctoku_pother = $ctoku_pother + 115715;
    }
    if ($yyyymm == 201001) {
        $ctoku_pother = $ctoku_pother - 58247;
    }
    $ctoku_pother_sagaku = $ctoku_pother;
    $ctoku_pother        = number_format(($ctoku_pother / $tani), $keta);
}
if ($yyyymm >= 201001) {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='カプラ営業外収益その他再計算'", $yyyymm);
} else {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='カプラ営業外収益その他'", $yyyymm);
}
if (getUniResult($query, $c_pother) < 1) {
    $c_pother         = 0;          // 検索失敗
    $ch_pother        = 0;          // 検索失敗
    $ch_pother_sagaku = 0;          // 検索失敗
} else {
    if ($yyyymm < 201001) {
        $c_pother = $c_pother - $b_pother;
    }
    if ($yyyymm == 200912) {
        $c_pother = $c_pother + 389809;
    }
    if ($yyyymm == 201001) {
        $c_pother = $c_pother - 315529;
    }
    $ch_pother        = $c_pother - $ctoku_pother_sagaku;
    $ch_pother_sagaku = $ch_pother;
    $ch_pother        = number_format(($ch_pother / $tani), $keta);
    $c_pother = number_format(($c_pother / $tani), $keta);
}
    ///// 前月
    // 商品管理（カプラに暫定的に入れているため）
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='商管営業外収益その他'", $p1_ym);
if (getUniResult($query, $p1_b_pother) < 1) {
    $p1_b_pother = 0;               // 検索失敗
}
if ($p1_ym >= 201001) {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='カプラ特注営業外収益その他再計算'", $p1_ym);
} else {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='カプラ特注営業外収益その他'", $p1_ym);
}
if (getUniResult($query, $p1_ctoku_pother) < 1) {
    $p1_ctoku_pother         = 0;          // 検索失敗
    $p1_ctoku_pother_sagaku  = 0;          // 検索失敗
} else {
    if ($p1_ym == 200912) {
        $p1_ctoku_pother = $p1_ctoku_pother + 115715;
    }
    if ($p1_ym == 201001) {
        $p1_ctoku_pother = $p1_ctoku_pother - 58247;
    }
    $p1_ctoku_pother_sagaku = $p1_ctoku_pother;
    $p1_ctoku_pother        = number_format(($p1_ctoku_pother / $tani), $keta);
}
if ($p1_ym >= 201001) {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='カプラ営業外収益その他再計算'", $p1_ym);
} else {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='カプラ営業外収益その他'", $p1_ym);
}
if (getUniResult($query, $p1_c_pother) < 1) {
    $p1_c_pother         = 0;          // 検索失敗
    $p1_ch_pother        = 0;          // 検索失敗
    $p1_ch_pother_sagaku = 0;          // 検索失敗
} else {
    if ($p1_ym < 201001) {
        $p1_c_pother = $p1_c_pother - $p1_b_pother;
    }
    if ($p1_ym == 200912) {
        $p1_c_pother = $p1_c_pother + 389809;
    }
    if ($p1_ym == 201001) {
        $p1_c_pother = $p1_c_pother - 315529;
    }
    $p1_ch_pother        = $p1_c_pother - $p1_ctoku_pother_sagaku;
    $p1_ch_pother_sagaku = $p1_ch_pother;
    $p1_ch_pother        = number_format(($p1_ch_pother / $tani), $keta);
    $p1_c_pother         = number_format(($p1_c_pother / $tani), $keta);
}
    ///// 前前月
    // 商品管理（カプラに暫定的に入れているため）
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='商管営業外収益その他'", $p2_ym);
if (getUniResult($query, $p2_b_pother) < 1) {
    $p2_b_pother = 0;               // 検索失敗
}
if ($p2_ym >= 201001) {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='カプラ特注営業外収益その他再計算'", $p2_ym);
} else {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='カプラ特注営業外収益その他'", $p2_ym);
}
if (getUniResult($query, $p2_ctoku_pother) < 1) {
    $p2_ctoku_pother         = 0;          // 検索失敗
    $p2_ctoku_pother_sagaku  = 0;          // 検索失敗
} else {
    if ($p2_ym == 200912) {
        $p2_ctoku_pother = $p2_ctoku_pother + 115715;
    }
    if ($p2_ym == 201001) {
        $p2_ctoku_pother = $p2_ctoku_pother - 58247;
    }
    $p2_ctoku_pother_sagaku = $p2_ctoku_pother;
    $p2_ctoku_pother        = number_format(($p2_ctoku_pother / $tani), $keta);
}
if ($p2_ym >= 201001) {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='カプラ営業外収益その他再計算'", $p2_ym);
} else {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='カプラ営業外収益その他'", $p2_ym);
}
if (getUniResult($query, $p2_c_pother) < 1) {
    $p2_c_pother         = 0;          // 検索失敗
    $p2_ch_pother        = 0;          // 検索失敗
    $p2_ch_pother_sagaku = 0;          // 検索失敗
} else {
    if ($p2_ym < 201001) {
        $p2_c_pother = $p2_c_pother - $p2_b_pother;
    }
    if ($p2_ym == 200912) {
        $p2_c_pother = $p2_c_pother + 389809;
    }
    if ($p2_ym == 201001) {
        $p2_c_pother = $p2_c_pother - 315529;
    }
    $p2_ch_pother        = $p2_c_pother - $p2_ctoku_pother_sagaku;
    $p2_ch_pother_sagaku = $p2_ch_pother;
    $p2_ch_pother        = number_format(($p2_ch_pother / $tani), $keta);
    $p2_c_pother         = number_format(($p2_c_pother / $tani), $keta);
}
    ///// 今期累計
    // 商品管理（カプラに暫定的に入れているため）
if ($yyyymm >= 201004) {
    $query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='商管営業外収益その他再計算'", $str_ym, $yyyymm);
    if (getUniResult($query, $rui_b_pother) < 1) {
        $rui_b_pother = 0;                          // 検索失敗
    } else {
        $rui_b_pother_sagaku = $rui_b_pother;
    }
} elseif ($yyyymm >= 201001 && $yyyymm <= 201003) {
    $query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=200904 and pl_bs_ym<=200912 and note='商管営業外収益その他'");
    if (getUniResult($query, $rui_b_pother_a) < 1) {
        $rui_b_pother_a = 0;                          // 検索失敗
    }
    $query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=201001 and pl_bs_ym<=%d and note='商管営業外収益その他再計算'", $yyyymm);
    if (getUniResult($query, $rui_b_pother_b) < 1) {
        $rui_b_pother_b = 0;                          // 検索失敗
    }
    $rui_b_pother = $rui_b_pother_a + $rui_b_pother_b;
} else {
    $query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='商管営業外収益その他'", $str_ym, $yyyymm);
    if (getUniResult($query, $rui_b_pother) < 1) {
        $rui_b_pother = 0;                          // 検索失敗
        $rui_b_sagaku = $rui_b_sagaku - $rui_b_pother;      // カプラ差額計算用
    } else {
        $rui_b_sagaku = $rui_b_sagaku - $rui_b_pother;      // カプラ差額計算用
    }
}
if ($yyyymm >= 201004) {
    $query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='カプラ特注営業外収益その他再計算'", $str_ym, $yyyymm);
    if (getUniResult($query, $rui_ctoku_pother) < 1) {
        $rui_ctoku_pother        = 0;   // 検索失敗
        $rui_ctoku_pother_sagaku = 0;
    } else {
        $rui_ctoku_pother_sagaku = $rui_ctoku_pother;
        $rui_ctoku_pother        = number_format(($rui_ctoku_pother / $tani), $keta);
    }
} elseif ($yyyymm >= 201001 && $yyyymm <= 201003) {
    $query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=200904 and pl_bs_ym<=200912 and note='カプラ特注営業外収益その他'");
    if (getUniResult($query, $rui_ctoku_pother_a) < 1) {
        $rui_ctoku_pother_a = 0;                          // 検索失敗
    }
    $query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=201001 and pl_bs_ym<=%d and note='カプラ特注営業外収益その他再計算'", $yyyymm);
    if (getUniResult($query, $rui_ctoku_pother_b) < 1) {
        $rui_ctoku_pother_b = 0;                          // 検索失敗
    }
    $rui_ctoku_pother = $rui_ctoku_pother_a + $rui_ctoku_pother_b;
    
    if ($yyyymm >= 200912 && $yyyymm <= 201003) {
        $rui_ctoku_pother = $rui_ctoku_pother + 115715;
    }
    if ($yyyymm >= 201001 && $yyyymm <= 201003) {
        $rui_ctoku_pother = $rui_ctoku_pother - 58247;
    }
    $rui_ctoku_pother_sagaku = $rui_ctoku_pother;
    $rui_ctoku_pother        = number_format(($rui_ctoku_pother / $tani), $keta);
} else {
    $query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='カプラ特注営業外収益その他'", $str_ym, $yyyymm);
    if (getUniResult($query, $rui_ctoku_pother) < 1) {
        $rui_ctoku_pother        = 0;   // 検索失敗
        $rui_ctoku_pother_sagaku = 0;
    } else {
        if ($yyyymm >= 200912 && $yyyymm <= 201003) {
           $rui_ctoku_pother = $rui_ctoku_pother + 115715;
        }
        if ($yyyymm >= 201001 && $yyyymm <= 201003) {
            $rui_ctoku_pother = $rui_ctoku_pother - 58247;
        }
        $rui_ctoku_pother_sagaku = $rui_ctoku_pother;
        $rui_ctoku_pother        = number_format(($rui_ctoku_pother / $tani), $keta);
    }
}
if ($yyyymm >= 201004) {
    $query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='カプラ営業外収益その他再計算'", $str_ym, $yyyymm);
    if (getUniResult($query, $rui_c_pother) < 1) {
        $rui_c_pother         = 0;      // 検索失敗
        $rui_ch_pother        = 0;      // 検索失敗
        $rui_ch_pother_sagaku = 0;      // 検索失敗
    } else {
        $rui_ch_pother        = $rui_c_pother - $rui_ctoku_pother_sagaku;
        $rui_ch_pother_sagaku = $rui_ch_pother;
        $rui_ch_pother        = number_format(($rui_ch_pother / $tani), $keta);
        $rui_c_pother = number_format(($rui_c_pother / $tani), $keta);
    }
} elseif ($yyyymm >= 201001 && $yyyymm <= 201003) {
    $query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=200904 and pl_bs_ym<=200912 and note='カプラ営業外収益その他'");
    if (getUniResult($query, $rui_c_pother_a) < 1) {
        $rui_c_pother_a = 0;                          // 検索失敗
    }
    $query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=201001 and pl_bs_ym<=%d and note='カプラ営業外収益その他再計算'", $yyyymm);
    if (getUniResult($query, $rui_c_pother_b) < 1) {
        $rui_c_pother_b = 0;                          // 検索失敗
    }
    $rui_c_pother = $rui_c_pother_a + $rui_c_pother_b;
    
    if ($yyyymm >= 200912 && $yyyymm <= 201003) {
        $rui_c_pother = $rui_c_pother + 389809;
    }
    if ($yyyymm >= 201001 && $yyyymm <= 201003) {
        $rui_c_pother = $rui_c_pother - 315529;
    }
    $rui_c_pother         = $rui_c_pother - $rui_b_pother_a;
    $rui_ch_pother        = $rui_c_pother - $rui_ctoku_pother_sagaku;
    $rui_ch_pother_sagaku = $rui_ch_pother;
    $rui_ch_pother        = number_format(($rui_ch_pother / $tani), $keta);
    $rui_c_pother         = number_format(($rui_c_pother / $tani), $keta);
} else {
    $query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='カプラ営業外収益その他'", $str_ym, $yyyymm);
    if (getUniResult($query, $rui_c_pother) < 1) {
        $rui_c_pother         = 0;      // 検索失敗
        $rui_ch_pother        = 0;      // 検索失敗
        $rui_ch_pother_sagaku = 0;      // 検索失敗
    } else {
        if ($yyyymm >= 200912 && $yyyymm <= 201003) {
            $rui_c_pother = $rui_c_pother + 389809;
        }
        if ($yyyymm >= 201001 && $yyyymm <= 201003) {
            $rui_c_pother = $rui_c_pother - 315529;
        }
        $rui_c_pother         = $rui_c_pother - $rui_b_pother;
        $rui_ch_pother        = $rui_c_pother - $rui_ctoku_pother_sagaku;
        $rui_ch_pother_sagaku = $rui_ch_pother;
        $rui_ch_pother        = number_format(($rui_ch_pother / $tani), $keta);
        $rui_c_pother         = number_format(($rui_c_pother / $tani), $keta);
    }
}
/********** 営業外収益の合計 **********/
    ///// カプラ特注
$p2_ctoku_nonope_profit_sum         = $p2_ctoku_gyoumu_sagaku + $p2_ctoku_swari_sagaku + $p2_ctoku_pother_sagaku;
$p2_ctoku_nonope_profit_sum_sagaku  = $p2_ctoku_nonope_profit_sum;
$p2_ctoku_nonope_profit_sum         = number_format(($p2_ctoku_nonope_profit_sum / $tani), $keta);

$p1_ctoku_nonope_profit_sum         = $p1_ctoku_gyoumu_sagaku + $p1_ctoku_swari_sagaku + $p1_ctoku_pother_sagaku;
$p1_ctoku_nonope_profit_sum_sagaku  = $p1_ctoku_nonope_profit_sum;
$p1_ctoku_nonope_profit_sum         = number_format(($p1_ctoku_nonope_profit_sum / $tani), $keta);

$ctoku_nonope_profit_sum            = $ctoku_gyoumu_sagaku + $ctoku_swari_sagaku + $ctoku_pother_sagaku;
$ctoku_nonope_profit_sum_sagaku     = $ctoku_nonope_profit_sum;
$ctoku_nonope_profit_sum            = number_format(($ctoku_nonope_profit_sum / $tani), $keta);

$rui_ctoku_nonope_profit_sum        = $rui_ctoku_gyoumu_sagaku + $rui_ctoku_swari_sagaku + $rui_ctoku_pother_sagaku;
$rui_ctoku_nonope_profit_sum_sagaku = $rui_ctoku_nonope_profit_sum;
$rui_ctoku_nonope_profit_sum        = number_format(($rui_ctoku_nonope_profit_sum / $tani), $keta);

    ///// 当月
if ($yyyymm >= 201001) {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='カプラ営業外収益計再計算'", $yyyymm);
} else {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='カプラ営業外収益計'", $yyyymm);
}
if (getUniResult($query, $c_nonope_profit_sum) < 1) {
    $c_nonope_profit_sum         = 0;       // 検索失敗
    $ch_nonope_profit_sum        = 0;       // 検索失敗
    $ch_nonope_profit_sum_sagaku = 0;       // 検索失敗
    $c_nonope_profit_sum_temp    = 0;
} else {
    if ($yyyymm < 201001) {
        $c_nonope_profit_sum = $c_nonope_profit_sum - $b_pother;
    }
    $c_nonope_profit_sum_temp    = $c_nonope_profit_sum;
    $ch_nonope_profit_sum        = $c_nonope_profit_sum - $ctoku_nonope_profit_sum_sagaku;
    $ch_nonope_profit_sum_sagaku = $ch_nonope_profit_sum;
    $ch_nonope_profit_sum        = number_format(($ch_nonope_profit_sum / $tani), $keta);
    $c_nonope_profit_sum         = number_format(($c_nonope_profit_sum / $tani), $keta);
}
    ///// 前月
if ($p1_ym >= 201001) {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='カプラ営業外収益計再計算'", $p1_ym);
} else {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='カプラ営業外収益計'", $p1_ym);
}
if (getUniResult($query, $p1_c_nonope_profit_sum) < 1) {
    $p1_c_nonope_profit_sum         = 0;       // 検索失敗
    $p1_ch_nonope_profit_sum        = 0;       // 検索失敗
    $p1_ch_nonope_profit_sum_sagaku = 0;       // 検索失敗
    $p1_c_nonope_profit_sum_temp    = 0;
} else {
    if ($p1_ym < 201001) {
        $p1_c_nonope_profit_sum = $p1_c_nonope_profit_sum - $p1_b_pother;
    }
    $p1_c_nonope_profit_sum_temp    = $p1_c_nonope_profit_sum;
    $p1_ch_nonope_profit_sum        = $p1_c_nonope_profit_sum - $p1_ctoku_nonope_profit_sum_sagaku;
    $p1_ch_nonope_profit_sum_sagaku = $p1_ch_nonope_profit_sum;
    $p1_ch_nonope_profit_sum        = number_format(($p1_ch_nonope_profit_sum / $tani), $keta);
    $p1_c_nonope_profit_sum         = number_format(($p1_c_nonope_profit_sum / $tani), $keta);
}
    ///// 前前月
if ($p2_ym >= 201001) {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='カプラ営業外収益計再計算'", $p2_ym);
} else {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='カプラ営業外収益計'", $p2_ym);
}
if (getUniResult($query, $p2_c_nonope_profit_sum) < 1) {
    $p2_c_nonope_profit_sum         = 0;       // 検索失敗
    $p2_ch_nonope_profit_sum        = 0;       // 検索失敗
    $p2_ch_nonope_profit_sum_sagaku = 0;       // 検索失敗
    $p2_c_nonope_profit_sum_temp    = 0;
} else {
    if ($p2_ym < 201001) {
        $p2_c_nonope_profit_sum = $p2_c_nonope_profit_sum - $p2_b_pother;
    }
    $p2_c_nonope_profit_sum_temp    = $p2_c_nonope_profit_sum;
    $p2_ch_nonope_profit_sum        = $p2_c_nonope_profit_sum - $p2_ctoku_nonope_profit_sum_sagaku;
    $p2_ch_nonope_profit_sum_sagaku = $p2_ch_nonope_profit_sum;
    $p2_ch_nonope_profit_sum        = number_format(($p2_ch_nonope_profit_sum / $tani), $keta);
    $p2_c_nonope_profit_sum         = number_format(($p2_c_nonope_profit_sum / $tani), $keta);
}
    ///// 今期累計
if ($yyyymm >= 201004) {
    $query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='カプラ営業外収益計再計算'", $str_ym, $yyyymm);
    if (getUniResult($query, $rui_c_nonope_profit_sum) < 1) {
        $rui_c_nonope_profit_sum         = 0;   // 検索失敗
        $rui_ch_nonope_profit_sum        = 0;   // 検索失敗
        $rui_ch_nonope_profit_sum_sagaku = 0;   // 検索失敗
    } else {
        //$rui_c_nonope_profit_sum       = $rui_c_nonope_profit_sum - $rui_b_nonope_profit_sum;
        $rui_ch_nonope_profit_sum        = $rui_c_nonope_profit_sum - $rui_ctoku_nonope_profit_sum_sagaku;
        $rui_ch_nonope_profit_sum_sagaku = $rui_ch_nonope_profit_sum;
        $rui_ch_nonope_profit_sum        = number_format(($rui_ch_nonope_profit_sum / $tani), $keta);
        $rui_c_nonope_profit_sum         = number_format(($rui_c_nonope_profit_sum / $tani), $keta);
    }
} elseif ($yyyymm >= 201001 && $yyyymm <= 201003) {
    $query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=200904 and pl_bs_ym<=200912 and note='カプラ営業外収益計'");
    if (getUniResult($query, $rui_c_nonope_profit_sum_a) < 1) {
        $rui_c_nonope_profit_sum_a = 0;                          // 検索失敗
    }
    $query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=201001 and pl_bs_ym<=%d and note='カプラ営業外収益計再計算'", $yyyymm);
    if (getUniResult($query, $rui_c_nonope_profit_sum_b) < 1) {
        $rui_c_nonope_profit_sum_b = 0;                          // 検索失敗
    }
    $rui_c_nonope_profit_sum = $rui_c_nonope_profit_sum_a + $rui_c_nonope_profit_sum_b;
    if ($yyyymm < 201001) {
        $rui_c_nonope_profit_sum = $rui_c_nonope_profit_sum - $rui_b_nonope_profit_sum;
    }
    $rui_c_nonope_profit_sum         = $rui_c_nonope_profit_sum - $rui_b_pother_a;
    $rui_ch_nonope_profit_sum        = $rui_c_nonope_profit_sum - $rui_ctoku_nonope_profit_sum_sagaku;
    $rui_ch_nonope_profit_sum_sagaku = $rui_ch_nonope_profit_sum;
    $rui_ch_nonope_profit_sum        = number_format(($rui_ch_nonope_profit_sum / $tani), $keta);
    $rui_c_nonope_profit_sum         = number_format(($rui_c_nonope_profit_sum / $tani), $keta);
} else {
    $query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='カプラ営業外収益計'", $str_ym, $yyyymm);
    if (getUniResult($query, $rui_c_nonope_profit_sum) < 1) {
        $rui_c_nonope_profit_sum         = 0;   // 検索失敗
        $rui_ch_nonope_profit_sum        = 0;   // 検索失敗
        $rui_ch_nonope_profit_sum_sagaku = 0;   // 検索失敗
    } else {
        $rui_c_nonope_profit_sum         = $rui_c_nonope_profit_sum - $rui_b_pother;
        $rui_ch_nonope_profit_sum        = $rui_c_nonope_profit_sum - $rui_ctoku_nonope_profit_sum_sagaku;
        $rui_ch_nonope_profit_sum_sagaku = $rui_ch_nonope_profit_sum;
        $rui_ch_nonope_profit_sum        = number_format(($rui_ch_nonope_profit_sum / $tani), $keta);
        $rui_c_nonope_profit_sum         = number_format(($rui_c_nonope_profit_sum / $tani), $keta);
    }
}
/********** 営業外費用の支払利息 **********/
    ///// 当月
if ($yyyymm >= 201001) {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='カプラ特注支払利息再計算'", $yyyymm);
} else {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='カプラ特注支払利息'", $yyyymm);
}
if (getUniResult($query, $ctoku_srisoku) < 1) {
    $ctoku_srisoku         = 0;                 // 検索失敗
    $ctoku_srisoku_sagaku = 0;                 // 検索失敗
} else {
    $ctoku_srisoku_sagaku = $ctoku_srisoku;
    $ctoku_srisoku        = number_format(($ctoku_srisoku / $tani), $keta);
}
if ($yyyymm >= 201001) {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='カプラ支払利息再計算'", $yyyymm);
} else {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='カプラ支払利息'", $yyyymm);
}
if (getUniResult($query, $c_srisoku) < 1) {
    $c_srisoku         = 0;                 // 検索失敗
    $ch_srisoku        = 0;                 // 検索失敗
    $ch_srisoku_sagaku = 0;                 // 検索失敗
} else {
    $ch_srisoku        = $c_srisoku - $ctoku_srisoku_sagaku;
    $ch_srisoku_sagaku = $ch_srisoku;
    $ch_srisoku        = number_format(($ch_srisoku / $tani), $keta);
    $c_srisoku         = number_format(($c_srisoku / $tani), $keta);
}
    ///// 前月
if ($p1_ym >= 201001) {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='カプラ特注支払利息再計算'", $p1_ym);
} else {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='カプラ特注支払利息'", $p1_ym);
}
if (getUniResult($query, $p1_ctoku_srisoku) < 1) {
    $p1_ctoku_srisoku         = 0;                 // 検索失敗
    $p1_ctoku_srisoku_sagaku = 0;                 // 検索失敗
} else {
    $p1_ctoku_srisoku_sagaku = $p1_ctoku_srisoku;
    $p1_ctoku_srisoku        = number_format(($p1_ctoku_srisoku / $tani), $keta);
}
if ($p1_ym >= 201001) {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='カプラ支払利息再計算'", $p1_ym);
} else {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='カプラ支払利息'", $p1_ym);
}
if (getUniResult($query, $p1_c_srisoku) < 1) {
    $p1_c_srisoku         = 0;                 // 検索失敗
    $p1_ch_srisoku        = 0;                 // 検索失敗
    $p1_ch_srisoku_sagaku = 0;                 // 検索失敗
} else {
    $p1_ch_srisoku        = $p1_c_srisoku - $p1_ctoku_srisoku_sagaku;
    $p1_ch_srisoku_sagaku = $p1_ch_srisoku;
    $p1_ch_srisoku        = number_format(($p1_ch_srisoku / $tani), $keta);
    $p1_c_srisoku         = number_format(($p1_c_srisoku / $tani), $keta);
}
    ///// 前前月
if ($p2_ym >= 201001) {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='カプラ特注支払利息再計算'", $p2_ym);
} else {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='カプラ特注支払利息'", $p2_ym);
}
if (getUniResult($query, $p2_ctoku_srisoku) < 1) {
    $p2_ctoku_srisoku         = 0;                 // 検索失敗
    $p2_ctoku_srisoku_sagaku = 0;                 // 検索失敗
} else {
    $p2_ctoku_srisoku_sagaku = $p2_ctoku_srisoku;
    $p2_ctoku_srisoku        = number_format(($p2_ctoku_srisoku / $tani), $keta);
}
if ($p2_ym >= 201001) {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='カプラ支払利息再計算'", $p2_ym);
} else {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='カプラ支払利息'", $p2_ym);
}
if (getUniResult($query, $p2_c_srisoku) < 1) {
    $p2_c_srisoku         = 0;                 // 検索失敗
    $p2_ch_srisoku        = 0;                 // 検索失敗
    $p2_ch_srisoku_sagaku = 0;                 // 検索失敗
} else {
    $p2_ch_srisoku        = $p2_c_srisoku - $p2_ctoku_srisoku_sagaku;
    $p2_ch_srisoku_sagaku = $p2_ch_srisoku;
    $p2_ch_srisoku        = number_format(($p2_ch_srisoku / $tani), $keta);
    $p2_c_srisoku         = number_format(($p2_c_srisoku / $tani), $keta);
}
    ///// 今期累計
if ($yyyymm >= 201004) {
    $query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='カプラ特注支払利息再計算'", $str_ym, $yyyymm);
    if (getUniResult($query, $rui_ctoku_srisoku) < 1) {
        $rui_ctoku_srisoku        = 0;          // 検索失敗
        $rui_ctoku_srisoku_sagaku = 0;
    } else {
        $rui_ctoku_srisoku_sagaku = $rui_ctoku_srisoku;
        $rui_ctoku_srisoku        = number_format(($rui_ctoku_srisoku / $tani), $keta);
    }
} elseif ($yyyymm >= 201001 && $yyyymm <= 201003) {
    $query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=200904 and pl_bs_ym<=200912 and note='カプラ特注支払利息'");
    if (getUniResult($query, $rui_ctoku_srisoku_a) < 1) {
        $rui_ctoku_srisoku_a = 0;                          // 検索失敗
    }
    $query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=201001 and pl_bs_ym<=%d and note='カプラ特注支払利息再計算'", $yyyymm);
    if (getUniResult($query, $rui_ctoku_srisoku_b) < 1) {
        $rui_ctoku_srisoku_b = 0;                          // 検索失敗
    }
    $rui_ctoku_srisoku        = $rui_ctoku_srisoku_a + $rui_ctoku_srisoku_b;
    $rui_ctoku_srisoku_sagaku = $rui_ctoku_srisoku;
    $rui_ctoku_srisoku        = number_format(($rui_ctoku_srisoku / $tani), $keta);
} else {
    $query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='カプラ特注支払利息'", $str_ym, $yyyymm);
    if (getUniResult($query, $rui_ctoku_srisoku) < 1) {
        $rui_ctoku_srisoku        = 0;          // 検索失敗
        $rui_ctoku_srisoku_sagaku = 0;
    } else {
        $rui_ctoku_srisoku_sagaku = $rui_ctoku_srisoku;
        $rui_ctoku_srisoku        = number_format(($rui_ctoku_srisoku / $tani), $keta);
    }
}
if ($yyyymm >= 201004) {
    $query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='カプラ支払利息再計算'", $str_ym, $yyyymm);
    if (getUniResult($query, $rui_c_srisoku) < 1) {
        $rui_c_srisoku         = 0;             // 検索失敗
        $rui_ch_srisoku        = 0;             // 検索失敗
        $rui_ch_srisoku_sagaku = 0;             // 検索失敗
    } else {
        $rui_ch_srisoku        = $rui_c_srisoku - $rui_ctoku_srisoku_sagaku;
        $rui_ch_srisoku_sagaku = $rui_ch_srisoku;
        $rui_ch_srisoku        = number_format(($rui_ch_srisoku / $tani), $keta);
        $rui_c_srisoku         = number_format(($rui_c_srisoku / $tani), $keta);
    }
} elseif ($yyyymm >= 201001 && $yyyymm <= 201003) {
    $query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=200904 and pl_bs_ym<=200912 and note='カプラ支払利息'");
    if (getUniResult($query, $rui_c_srisoku_a) < 1) {
        $rui_c_srisoku_a = 0;                          // 検索失敗
    }
    $query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=201001 and pl_bs_ym<=%d and note='カプラ支払利息再計算'", $yyyymm);
    if (getUniResult($query, $rui_c_srisoku_b) < 1) {
        $rui_c_srisoku_b = 0;                          // 検索失敗
    }
    $rui_c_srisoku         = $rui_c_srisoku_a + $rui_c_srisoku_b;
    $rui_ch_srisoku        = $rui_c_srisoku - $rui_ctoku_srisoku_sagaku;
    $rui_ch_srisoku_sagaku = $rui_ch_srisoku;
    $rui_ch_srisoku        = number_format(($rui_ch_srisoku / $tani), $keta);
    $rui_c_srisoku         = number_format(($rui_c_srisoku / $tani), $keta);
} else {
    $query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='カプラ支払利息'", $str_ym, $yyyymm);
    if (getUniResult($query, $rui_c_srisoku) < 1) {
        $rui_c_srisoku         = 0;             // 検索失敗
        $rui_ch_srisoku        = 0;             // 検索失敗
        $rui_ch_srisoku_sagaku = 0;             // 検索失敗
    } else {
        $rui_ch_srisoku        = $rui_c_srisoku - $rui_ctoku_srisoku_sagaku;
        $rui_ch_srisoku_sagaku = $rui_ch_srisoku;
        $rui_ch_srisoku        = number_format(($rui_ch_srisoku / $tani), $keta);
        $rui_c_srisoku         = number_format(($rui_c_srisoku / $tani), $keta);
    }
}
/********** 営業外費用のその他 **********/
    ///// 当月
if ($yyyymm >= 201001) {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='カプラ特注営業外費用その他再計算'", $yyyymm);
} else {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='カプラ特注営業外費用その他'", $yyyymm);
}
if (getUniResult($query, $ctoku_lother) < 1) {
    $ctoku_lother         = 0;                  // 検索失敗
    $ctoku_lother_sagaku = 0;                  // 検索失敗
} else {
    $ctoku_lother_sagaku = $ctoku_lother;
    $ctoku_lother        = number_format(($ctoku_lother / $tani), $keta);
}
if ($yyyymm >= 201001) {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='カプラ営業外費用その他再計算'", $yyyymm);
} else {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='カプラ営業外費用その他'", $yyyymm);
}
if (getUniResult($query, $c_lother) < 1) {
    $c_lother         = 0;                  // 検索失敗
    $ch_lother        = 0;                  // 検索失敗
    $ch_lother_sagaku = 0;                  // 検索失敗
} else {
    $ch_lother        = $c_lother - $ctoku_lother_sagaku;
    $ch_lother_sagaku = $ch_lother;
    $ch_lother        = number_format(($ch_lother / $tani), $keta);
    $c_lother         = number_format(($c_lother / $tani), $keta);
}
    ///// 前月
if ($p1_ym >= 201001) {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='カプラ特注営業外費用その他再計算'", $p1_ym);
} else {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='カプラ特注営業外費用その他'", $p1_ym);
}
if (getUniResult($query, $p1_ctoku_lother) < 1) {
    $p1_ctoku_lother         = 0;                  // 検索失敗
    $p1_ctoku_lother_sagaku = 0;                  // 検索失敗
} else {
    $p1_ctoku_lother_sagaku = $p1_ctoku_lother;
    $p1_ctoku_lother        = number_format(($p1_ctoku_lother / $tani), $keta);
}
if ($p1_ym >= 201001) {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='カプラ営業外費用その他再計算'", $p1_ym);
} else {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='カプラ営業外費用その他'", $p1_ym);
}
if (getUniResult($query, $p1_c_lother) < 1) {
    $p1_c_lother         = 0;                  // 検索失敗
    $p1_ch_lother        = 0;                  // 検索失敗
    $p1_ch_lother_sagaku = 0;                  // 検索失敗
} else {
    $p1_ch_lother        = $p1_c_lother - $p1_ctoku_lother_sagaku;
    $p1_ch_lother_sagaku = $p1_ch_lother;
    $p1_ch_lother        = number_format(($p1_ch_lother / $tani), $keta);
    $p1_c_lother         = number_format(($p1_c_lother / $tani), $keta);
}
    ///// 前前月
if ($p2_ym >= 201001) {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='カプラ特注営業外費用その他再計算'", $p2_ym);
} else {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='カプラ特注営業外費用その他'", $p2_ym);
}
if (getUniResult($query, $p2_ctoku_lother) < 1) {
    $p2_ctoku_lother         = 0;                  // 検索失敗
    $p2_ctoku_lother_sagaku = 0;                  // 検索失敗
} else {
    $p2_ctoku_lother_sagaku = $p2_ctoku_lother;
    $p2_ctoku_lother        = number_format(($p2_ctoku_lother / $tani), $keta);
}
if ($p2_ym >= 201001) {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='カプラ営業外費用その他再計算'", $p2_ym);
} else {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='カプラ営業外費用その他'", $p2_ym);
}
if (getUniResult($query, $p2_c_lother) < 1) {
    $p2_c_lother         = 0;                  // 検索失敗
    $p2_ch_lother        = 0;                  // 検索失敗
    $p2_ch_lother_sagaku = 0;                  // 検索失敗
} else {
    $p2_ch_lother        = $p2_c_lother - $p2_ctoku_lother_sagaku;
    $p2_ch_lother_sagaku = $p2_ch_lother;
    $p2_ch_lother        = number_format(($p2_ch_lother / $tani), $keta);
    $p2_c_lother         = number_format(($p2_c_lother / $tani), $keta);
}
    ///// 今期累計
if ($yyyymm >= 201004) {
    $query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='カプラ特注営業外費用その他再計算'", $str_ym, $yyyymm);
    if (getUniResult($query, $rui_ctoku_lother) < 1) {
        $rui_ctoku_lother        = 0;           // 検索失敗
        $rui_ctoku_lother_sagaku = 0;
    } else {
        $rui_ctoku_lother_sagaku = $rui_ctoku_lother;
        $rui_ctoku_lother        = number_format(($rui_ctoku_lother / $tani), $keta);
    }
} elseif ($yyyymm >= 201001 && $yyyymm <= 201003) {
    $query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=200904 and pl_bs_ym<=200912 and note='カプラ特注営業外費用その他'");
    if (getUniResult($query, $rui_ctoku_lother_a) < 1) {
        $rui_ctoku_lother_a = 0;                          // 検索失敗
    }
    $query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=201001 and pl_bs_ym<=%d and note='カプラ特注営業外費用その他再計算'", $yyyymm);
    if (getUniResult($query, $rui_ctoku_lother_b) < 1) {
        $rui_ctoku_lother_b = 0;                          // 検索失敗
    }
    $rui_ctoku_lother        = $rui_ctoku_lother_a + $rui_ctoku_lother_b;
    $rui_ctoku_lother_sagaku = $rui_ctoku_lother;
    $rui_ctoku_lother        = number_format(($rui_ctoku_lother / $tani), $keta);
} else {
    $query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='カプラ特注営業外費用その他'", $str_ym, $yyyymm);
    if (getUniResult($query, $rui_ctoku_lother) < 1) {
        $rui_ctoku_lother        = 0;           // 検索失敗
        $rui_ctoku_lother_sagaku = 0;
    } else {
        $rui_ctoku_lother_sagaku = $rui_ctoku_lother;
        $rui_ctoku_lother        = number_format(($rui_ctoku_lother / $tani), $keta);
    }
}
if ($yyyymm >= 201004) {
    $query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='カプラ営業外費用その他再計算'", $str_ym, $yyyymm);
    if (getUniResult($query, $rui_c_lother) < 1) {
        $rui_c_lother         = 0;              // 検索失敗
        $rui_ch_lother        = 0;              // 検索失敗
        $rui_ch_lother_sagaku = 0;              // 検索失敗
    } else {
        $rui_ch_lother        = $rui_c_lother - $rui_ctoku_lother_sagaku;
        $rui_ch_lother_sagaku = $rui_ch_lother;
        $rui_ch_lother        = number_format(($rui_ch_lother / $tani), $keta);
        $rui_c_lother = number_format(($rui_c_lother / $tani), $keta);
    }
} elseif ($yyyymm >= 201001 && $yyyymm <= 201003) {
    $query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=200904 and pl_bs_ym<=200912 and note='カプラ営業外費用その他'");
    if (getUniResult($query, $rui_c_lother_a) < 1) {
        $rui_c_lother_a = 0;                          // 検索失敗
    }
    $query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=201001 and pl_bs_ym<=%d and note='カプラ営業外費用その他再計算'", $yyyymm);
    if (getUniResult($query, $rui_c_lother_b) < 1) {
        $rui_c_lother_b = 0;                          // 検索失敗
    }
    $rui_c_lother         = $rui_c_lother_a + $rui_c_lother_b;
    $rui_ch_lother        = $rui_c_lother - $rui_ctoku_lother_sagaku;
    $rui_ch_lother_sagaku = $rui_ch_lother;
    $rui_ch_lother        = number_format(($rui_ch_lother / $tani), $keta);
    $rui_c_lother         = number_format(($rui_c_lother / $tani), $keta);
} else {
    $query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='カプラ営業外費用その他'", $str_ym, $yyyymm);
    if (getUniResult($query, $rui_c_lother) < 1) {
        $rui_c_lother         = 0;              // 検索失敗
        $rui_ch_lother        = 0;              // 検索失敗
        $rui_ch_lother_sagaku = 0;              // 検索失敗
    } else {
        $rui_ch_lother        = $rui_c_lother - $rui_ctoku_lother_sagaku;
        $rui_ch_lother_sagaku = $rui_ch_lother;
        $rui_ch_lother        = number_format(($rui_ch_lother / $tani), $keta);
        $rui_c_lother         = number_format(($rui_c_lother / $tani), $keta);
    }
}
/********** 営業外費用の合計 **********/
    ///// カプラ特注
$p2_ctoku_nonope_loss_sum         = $p2_ctoku_srisoku_sagaku + $p2_ctoku_lother_sagaku;
$p2_ctoku_nonope_loss_sum_sagaku  = $p2_ctoku_nonope_loss_sum;
$p2_ctoku_nonope_loss_sum         = number_format(($p2_ctoku_nonope_loss_sum / $tani), $keta);

$p1_ctoku_nonope_loss_sum         = $p1_ctoku_srisoku_sagaku + $p1_ctoku_lother_sagaku;
$p1_ctoku_nonope_loss_sum_sagaku  = $p1_ctoku_nonope_loss_sum;
$p1_ctoku_nonope_loss_sum         = number_format(($p1_ctoku_nonope_loss_sum / $tani), $keta);

$ctoku_nonope_loss_sum            = $ctoku_srisoku_sagaku + $ctoku_lother_sagaku;
$ctoku_nonope_loss_sum_sagaku     = $ctoku_nonope_loss_sum;
$ctoku_nonope_loss_sum            = number_format(($ctoku_nonope_loss_sum / $tani), $keta);

$rui_ctoku_nonope_loss_sum        = $rui_ctoku_srisoku_sagaku + $rui_ctoku_lother_sagaku;
$rui_ctoku_nonope_loss_sum_sagaku = $rui_ctoku_nonope_loss_sum;
$rui_ctoku_nonope_loss_sum        = number_format(($rui_ctoku_nonope_loss_sum / $tani), $keta);

    ///// 当月
if ($yyyymm >= 201001) {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='カプラ営業外費用計再計算'", $yyyymm);
} else {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='カプラ営業外費用計'", $yyyymm);
}
if (getUniResult($query, $c_nonope_loss_sum) < 1) {
    $c_nonope_loss_sum         = 0;     // 検索失敗
    $ch_nonope_loss_sum        = 0;     // 検索失敗
    $ch_nonope_loss_sum_sagaku = 0;     // 検索失敗
    $c_nonope_loss_sum_temp    = 0;     // 検索失敗
} else {
    $ch_nonope_loss_sum        = $c_nonope_loss_sum - $ctoku_nonope_loss_sum_sagaku;
    $ch_nonope_loss_sum_sagaku = $ch_nonope_loss_sum;
    $ch_nonope_loss_sum        = number_format(($ch_nonope_loss_sum / $tani), $keta);
    $c_nonope_loss_sum_temp    = $c_nonope_loss_sum;
    $c_nonope_loss_sum         = number_format(($c_nonope_loss_sum / $tani), $keta);
}
    ///// 前月
if ($p1_ym >= 201001) {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='カプラ営業外費用計再計算'", $p1_ym);
} else {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='カプラ営業外費用計'", $p1_ym);
}
if (getUniResult($query, $p1_c_nonope_loss_sum) < 1) {
    $p1_c_nonope_loss_sum         = 0;     // 検索失敗
    $p1_ch_nonope_loss_sum        = 0;     // 検索失敗
    $p1_ch_nonope_loss_sum_sagaku = 0;     // 検索失敗
    $p1_c_nonope_loss_sum_temp    = 0;     // 検索失敗
} else {
    $p1_ch_nonope_loss_sum        = $p1_c_nonope_loss_sum - $p1_ctoku_nonope_loss_sum_sagaku;
    $p1_ch_nonope_loss_sum_sagaku = $p1_ch_nonope_loss_sum;
    $p1_ch_nonope_loss_sum        = number_format(($p1_ch_nonope_loss_sum / $tani), $keta);
    $p1_c_nonope_loss_sum_temp    = $p1_c_nonope_loss_sum;
    $p1_c_nonope_loss_sum         = number_format(($p1_c_nonope_loss_sum / $tani), $keta);
}
    ///// 前前月
if ($p2_ym >= 201001) {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='カプラ営業外費用計再計算'", $p2_ym);
} else {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='カプラ営業外費用計'", $p2_ym);
}
if (getUniResult($query, $p2_c_nonope_loss_sum) < 1) {
    $p2_c_nonope_loss_sum         = 0;     // 検索失敗
    $p2_ch_nonope_loss_sum        = 0;     // 検索失敗
    $p2_ch_nonope_loss_sum_sagaku = 0;     // 検索失敗
    $p2_c_nonope_loss_sum_temp    = 0;     // 検索失敗
} else {
    $p2_ch_nonope_loss_sum        = $p2_c_nonope_loss_sum - $p2_ctoku_nonope_loss_sum_sagaku;
    $p2_ch_nonope_loss_sum_sagaku = $p2_ch_nonope_loss_sum;
    $p2_ch_nonope_loss_sum        = number_format(($p2_ch_nonope_loss_sum / $tani), $keta);
    $p2_c_nonope_loss_sum_temp    = $p2_c_nonope_loss_sum;
    $p2_c_nonope_loss_sum         = number_format(($p2_c_nonope_loss_sum / $tani), $keta);
}
    ///// 今期累計
if ($yyyymm >= 201004) {
    $query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='カプラ営業外費用計再計算'", $str_ym, $yyyymm);
    if (getUniResult($query, $rui_c_nonope_loss_sum) < 1) {
        $rui_c_nonope_loss_sum         = 0; // 検索失敗
        $rui_ch_nonope_loss_sum        = 0; // 検索失敗
        $rui_ch_nonope_loss_sum_sagaku = 0; // 検索失敗
    } else {
        $rui_ch_nonope_loss_sum        = $rui_c_nonope_loss_sum - $rui_ctoku_nonope_loss_sum_sagaku;
        $rui_ch_nonope_loss_sum_sagaku = $rui_ch_nonope_loss_sum;
        $rui_ch_nonope_loss_sum        = number_format(($rui_ch_nonope_loss_sum / $tani), $keta);
        $rui_c_nonope_loss_sum         = number_format(($rui_c_nonope_loss_sum / $tani), $keta);
    }
} elseif ($yyyymm >= 201001 && $yyyymm <= 201003) {
    $query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=200904 and pl_bs_ym<=200912 and note='カプラ営業外費用計'");
    if (getUniResult($query, $rui_c_nonope_loss_sum_a) < 1) {
        $rui_c_nonope_loss_sum_a = 0;                          // 検索失敗
    }
    $query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=201001 and pl_bs_ym<=%d and note='カプラ営業外費用計再計算'", $yyyymm);
    if (getUniResult($query, $rui_c_nonope_loss_sum_b) < 1) {
        $rui_c_nonope_loss_sum_b = 0;                          // 検索失敗
    }
    $rui_c_nonope_loss_sum         = $rui_c_nonope_loss_sum_a + $rui_c_nonope_loss_sum_b;
    $rui_ch_nonope_loss_sum        = $rui_c_nonope_loss_sum - $rui_ctoku_nonope_loss_sum_sagaku;
    $rui_ch_nonope_loss_sum_sagaku = $rui_ch_nonope_loss_sum;
    $rui_ch_nonope_loss_sum        = number_format(($rui_ch_nonope_loss_sum / $tani), $keta);
    $rui_c_nonope_loss_sum         = number_format(($rui_c_nonope_loss_sum / $tani), $keta);
} else {
    $query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='カプラ営業外費用計'", $str_ym, $yyyymm);
    if (getUniResult($query, $rui_c_nonope_loss_sum) < 1) {
        $rui_c_nonope_loss_sum         = 0; // 検索失敗
        $rui_ch_nonope_loss_sum        = 0; // 検索失敗
        $rui_ch_nonope_loss_sum_sagaku = 0; // 検索失敗
    } else {
        $rui_ch_nonope_loss_sum        = $rui_c_nonope_loss_sum - $rui_ctoku_nonope_loss_sum_sagaku;
        $rui_ch_nonope_loss_sum_sagaku = $rui_ch_nonope_loss_sum;
        $rui_ch_nonope_loss_sum        = number_format(($rui_ch_nonope_loss_sum / $tani), $keta);
        $rui_c_nonope_loss_sum         = number_format(($rui_c_nonope_loss_sum / $tani), $keta);
    }
}
/********** 経常利益 **********/
///////// 商品管理分の差額計算（労務費＋製造経費＋販管費人件費＋販管費経費-営業外収益その他）
$b_sagaku     = $b_sagaku - $b_pother;
$p1_b_sagaku  = $p1_b_sagaku - $p1_b_pother;
$p2_b_sagaku  = $p2_b_sagaku - $p2_b_pother;
//$rui_b_sagaku = $rui_b_sagaku - $rui_b_pother;
    ///// カプラ特注
$p2_ctoku_current_profit         = $p2_ctoku_ope_profit_sagaku + $p2_ctoku_nonope_profit_sum_sagaku - $p2_ctoku_nonope_loss_sum_sagaku;
$p2_ctoku_current_profit_sagaku  = $p2_ctoku_current_profit;
$p2_ctoku_current_profit         = number_format(($p2_ctoku_current_profit / $tani), $keta);

$p1_ctoku_current_profit         = $p1_ctoku_ope_profit_sagaku + $p1_ctoku_nonope_profit_sum_sagaku - $p1_ctoku_nonope_loss_sum_sagaku;
$p1_ctoku_current_profit_sagaku  = $p1_ctoku_current_profit;
$p1_ctoku_current_profit         = number_format(($p1_ctoku_current_profit / $tani), $keta);

$ctoku_current_profit            = $ctoku_ope_profit_sagaku + $ctoku_nonope_profit_sum_sagaku - $ctoku_nonope_loss_sum_sagaku;
$ctoku_current_profit_sagaku     = $ctoku_current_profit;
$ctoku_current_profit            = number_format(($ctoku_current_profit / $tani), $keta);

$rui_ctoku_current_profit        = $rui_ctoku_ope_profit_sagaku + $rui_ctoku_nonope_profit_sum_sagaku - $rui_ctoku_nonope_loss_sum_sagaku;
$rui_ctoku_current_profit_sagaku = $rui_ctoku_current_profit;
$rui_ctoku_current_profit        = number_format(($rui_ctoku_current_profit / $tani), $keta);

    ///// 当月
if ($yyyymm >= 201001) {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='カプラ経常利益再計算'", $yyyymm);
} else {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='カプラ経常利益'", $yyyymm);
}
if (getUniResult($query, $c_current_profit) < 1) {
    $c_current_profit         = 0;      // 検索失敗
    $ch_current_profit        = 0;      // 検索失敗
    $ch_current_profit_sagaku = 0;      // 検索失敗
} else {
    if ($yyyymm < 201001) {
        $c_current_profit = $c_current_profit + $b_sagaku + $c_allo_kin - $sc_uri_sagaku + $sc_metarial_sagaku; // カプラ試験修理を加味
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
    $ch_current_profit        = $c_current_profit - $ctoku_current_profit_sagaku;
    $ch_current_profit_sagaku = $ch_current_profit;
    $ch_current_profit        = number_format(($ch_current_profit / $tani), $keta);
    $c_current_profit         = number_format(($c_current_profit / $tani), $keta);
}
    ///// 前月
if ($p1_ym >= 201001) {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='カプラ経常利益再計算'", $p1_ym);
} else {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='カプラ経常利益'", $p1_ym);
}
if (getUniResult($query, $p1_c_current_profit) < 1) {
    $p1_c_current_profit         = 0;      // 検索失敗
    $p1_ch_current_profit        = 0;      // 検索失敗
    $p1_ch_current_profit_sagaku = 0;      // 検索失敗
} else {
    if ($p1_ym < 201001) {
        $p1_c_current_profit = $p1_c_current_profit + $p1_b_sagaku + $p1_c_allo_kin - $p1_sc_uri_sagaku + $p1_sc_metarial_sagaku; // カプラ試験修理を加味
    } else {
        $p1_c_current_profit = $p1_c_ope_profit_temp + $p1_c_nonope_profit_sum_temp - $p1_c_nonope_loss_sum_temp;
    }
    if ($p1_ym == 200912) {
        $p1_c_current_profit = $p1_c_current_profit - 1227429;
    }
    if ($p1_ym >= 201001) {
        //$p1_c_current_profit = $p1_c_current_profit - $p1_c_kyu_kin;   // 添田さんの給与をC・Lは35%。試験修理に30%振分
        //$p1_c_current_profit = $p1_c_current_profit - 151313;   // 添田さんの給与をC・Lは35%。試験修理に30%振分
    }
    $p1_ch_current_profit        = $p1_c_current_profit - $p1_ctoku_current_profit_sagaku;
    $p1_ch_current_profit_sagaku = $p1_ch_current_profit;
    $p1_ch_current_profit        = number_format(($p1_ch_current_profit / $tani), $keta);
    $p1_c_current_profit         = number_format(($p1_c_current_profit / $tani), $keta);
}
    ///// 前前月
if ($p2_ym >= 201001) {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='カプラ経常利益再計算'", $p2_ym);
} else {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='カプラ経常利益'", $p2_ym);
}
if (getUniResult($query, $p2_c_current_profit) < 1) {
    $p2_c_current_profit         = 0;      // 検索失敗
    $p2_ch_current_profit        = 0;      // 検索失敗
    $p2_ch_current_profit_sagaku = 0;      // 検索失敗
} else {
    if ($p2_ym < 201001) {
        $p2_c_current_profit = $p2_c_current_profit + $p2_b_sagaku + $p2_c_allo_kin - $p2_sc_uri_sagaku + $p2_sc_metarial_sagaku; // カプラ試験修理を加味
    } else {
        $p2_c_current_profit = $p2_c_ope_profit_temp + $p2_c_nonope_profit_sum_temp - $p2_c_nonope_loss_sum_temp;
    }
    if ($p2_ym == 200912) {
        $p2_c_current_profit = $p2_c_current_profit - 1227429;
    }
    if ($p2_ym >= 201001) {
        //$p2_c_current_profit = $p2_c_current_profit - $p2_c_kyu_kin;   // 添田さんの給与をC・Lは35%。試験修理に30%振分
        //$p2_c_current_profit = $p2_c_current_profit - 151313;   // 添田さんの給与をC・Lは35%。試験修理に30%振分
    }
    $p2_ch_current_profit        = $p2_c_current_profit - $p2_ctoku_current_profit_sagaku;
    $p2_ch_current_profit_sagaku = $p2_ch_current_profit;
    $p2_ch_current_profit        = number_format(($p2_ch_current_profit / $tani), $keta);
    $p2_c_current_profit         = number_format(($p2_c_current_profit / $tani), $keta);
}
    ///// 今期累計
if ($yyyymm >= 201004) {
    $query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='カプラ経常利益再計算'", $str_ym, $yyyymm);
    if (getUniResult($query, $rui_c_current_profit) < 1) {
        $rui_c_current_profit         = 0;  // 検索失敗
        $rui_ch_current_profit        = 0;  // 検索失敗
        $rui_ch_current_profit_sagaku = 0;  // 検索失敗
    } else {
        $rui_c_current_profit         = $rui_c_current_profit + $rui_b_sagaku + $rui_c_allo_kin - $rui_sc_uri_sagaku + $rui_sc_metarial_sagaku; // カプラ試験修理を加味
        if ($yyyymm >= 201001) {
            $rui_c_current_profit = $rui_c_current_profit - $rui_c_kyu_kin; // 添田さんの給与をC・Lは35%。試験修理に30%振分
            //$rui_c_current_profit = $rui_c_current_profit - 151313; // 添田さんの給与をC・Lは35%。試験修理に30%振分
        }
        // 2013/11/07 追加 2013年10月度 業務委託費（横川派遣料）調整
        if ($yyyymm >= 201310 && $yyyymm <= 201403) {
            $rui_c_current_profit += 1245035;
        }
        if ($yyyymm >= 201311 && $yyyymm <= 201403) {
            $rui_c_current_profit -= 1245035;
        }
        if ($yyyymm >= 201408 && $yyyymm <= 201503) {
            $rui_c_current_profit = $rui_c_current_profit - 611904;
        }
        $rui_ch_current_profit        = $rui_c_current_profit - $rui_ctoku_current_profit_sagaku;
        $rui_ch_current_profit_sagaku = $rui_ch_current_profit;
        $rui_ch_current_profit        = number_format(($rui_ch_current_profit / $tani), $keta);
        $rui_c_current_profit         = number_format(($rui_c_current_profit / $tani), $keta);
    }
} elseif ($yyyymm >= 201001 && $yyyymm <= 201003) {
    $query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=200904 and pl_bs_ym<=200912 and note='カプラ経常利益'");
    if (getUniResult($query, $rui_c_current_profit_a) < 1) {
        $rui_c_current_profit_a = 0;                          // 検索失敗
    }
    $query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=201001 and pl_bs_ym<=%d and note='カプラ経常利益再計算'", $yyyymm);
    if (getUniResult($query, $rui_c_current_profit_b) < 1) {
        $rui_c_current_profit_b = 0;                          // 検索失敗
    }
    $rui_c_current_profit = $rui_c_current_profit_a + $rui_c_current_profit_b;
    if ($yyyymm >= 200912 && $yyyymm <= 201003) {
        $rui_c_current_profit = $rui_c_current_profit - 1227429;
    }
    if ($yyyymm >= 201001) {
        $rui_c_current_profit = $rui_c_current_profit - $rui_c_kyu_kin; // 添田さんの給与をC・Lは35%。試験修理に30%振分
        //$rui_c_current_profit = $rui_c_current_profit - 151313; // 添田さんの給与をC・Lは35%。試験修理に30%振分
    }
    $rui_c_current_profit         = $rui_c_current_profit + $rui_b_sagaku + $rui_c_allo_kin - $rui_sc_uri_sagaku + $rui_sc_metarial_sagaku - $rui_b_pother_a; // カプラ試験修理を加味
    $rui_ch_current_profit        = $rui_c_current_profit - $rui_ctoku_current_profit_sagaku;
    $rui_ch_current_profit_sagaku = $rui_ch_current_profit;
    $rui_ch_current_profit        = number_format(($rui_ch_current_profit / $tani), $keta);
    $rui_c_current_profit         = number_format(($rui_c_current_profit / $tani), $keta);
} else {
    $query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='カプラ経常利益'", $str_ym, $yyyymm);
    if (getUniResult($query, $rui_c_current_profit) < 1) {
        $rui_c_current_profit         = 0;  // 検索失敗
        $rui_ch_current_profit        = 0;  // 検索失敗
        $rui_ch_current_profit_sagaku = 0;  // 検索失敗
    } else {
        $rui_c_current_profit         = $rui_c_current_profit + $rui_b_sagaku + $rui_c_allo_kin - $rui_sc_uri_sagaku + $rui_sc_metarial_sagaku; // カプラ試験修理を加味
        if ($yyyymm >= 200912 && $yyyymm <= 201003) {
            $rui_c_current_profit = $rui_c_current_profit - 1227429;
        }
        $rui_ch_current_profit        = $rui_c_current_profit - $rui_ctoku_current_profit_sagaku;
        $rui_ch_current_profit_sagaku = $rui_ch_current_profit;
        $rui_ch_current_profit        = number_format(($rui_ch_current_profit / $tani), $keta);
        $rui_c_current_profit         = number_format(($rui_c_current_profit / $tani), $keta);
    }
}
////////// 特記事項の取得
$query = sprintf("select comment from act_comment_history where pl_bs_ym=%d and item='カプラ標準損益計算書'", $yyyymm);
if (getUniResult($query,$comment_c) <= 0) {
    $comment_c = "";
}
$query = sprintf("select comment from act_comment_history where pl_bs_ym=%d and item='カプラ特注損益計算書'", $yyyymm);
if (getUniResult($query,$comment_ctoku) <= 0) {
    $comment_ctoku = "";
}
$query = sprintf("select comment from act_comment_history where pl_bs_ym=%d and item='全体ctoku損益計算書'", $yyyymm);
if (getUniResult($query,$comment_all) <= 0) {
    $comment_all = "";
}
$query = sprintf("select comment from act_comment_history where pl_bs_ym=%d and item='その他ctoku損益計算書'", $yyyymm);
if (getUniResult($query,$comment_other) <= 0) {
    $comment_other = "";
}
if (isset($_POST['input_data'])) {                        // 当月データの登録
    ///////// 項目とインデックスの関連付け
    $item = array();
    $item[0]   = "売上高";
    $item[1]   = "期首材料仕掛品棚卸高";
    $item[2]   = "材料費(仕入高)";
    $item[3]   = "労務費";
    $item[4]   = "製造経費";
    $item[5]   = "期末材料仕掛品棚卸高";
    $item[6]   = "売上原価";
    $item[7]   = "売上総利益";
    $item[8]   = "人件費";
    $item[9]   = "経費";
    $item[10]  = "販管費及び一般管理費計";
    $item[11]  = "営業利益";
    $item[12]  = "業務委託収入";
    $item[13]  = "仕入割引";
    $item[14]  = "営業外収益その他";
    $item[15]  = "営業外収益計";
    $item[16]  = "支払利息";
    $item[17]  = "営業外費用その他";
    $item[18]  = "営業外費用計";
    $item[19]  = "経常利益";
    ///////// 各データの保管 カプラ特注=0 カプラ標準=1
    $input_data = array();
    for ($i = 0; $i < 20; $i++) {
        switch ($i) {
                case  0:                                            // 売上高
                    $input_data[$i][0] = $ctoku_uri;                // カプラ特注
                    $input_data[$i][1] = $ch_uri;                   // カプラ標準
                break;
                case  1:                                            // 期首材料仕掛品棚卸高
                    $input_data[$i][0] = $ctoku_invent;             // カプラ特注
                    $input_data[$i][1] = $ch_invent;                // カプラ標準
                break;
                case  2:                                            // 材料費(仕入高)
                    $input_data[$i][0] = $ctoku_metarial;           // カプラ特注
                    $input_data[$i][1] = $ch_metarial;              // カプラ標準
                break;
                case  3:                                            // 労務費
                    $input_data[$i][0] = $ctoku_roumu;              // カプラ特注
                    $input_data[$i][1] = $ch_roumu;                 // カプラ標準
                break;
                case  4:                                            // 製造経費
                    $input_data[$i][0] = $ctoku_expense;            // カプラ特注
                    $input_data[$i][1] = $ch_expense;               // カプラ標準
                break;
                case  5:                                            // 期末材料仕掛品棚卸高
                    $input_data[$i][0] = $ctoku_endinv;             // カプラ特注
                    $input_data[$i][1] = $ch_endinv;                // カプラ標準
                break;
                case  6:                                            // 売上原価
                    $input_data[$i][0] = $ctoku_urigen;             // カプラ特注
                    $input_data[$i][1] = $ch_urigen;                // カプラ標準
                break;
                case  7:                                            // 売上総利益
                    $input_data[$i][0] = $ctoku_gross_profit;       // カプラ特注
                    $input_data[$i][1] = $ch_gross_profit;          // カプラ標準
                break;
                case  8:                                            // 人件費
                    $input_data[$i][0] = $ctoku_han_jin;            // カプラ特注
                    $input_data[$i][1] = $ch_han_jin;               // カプラ標準
                break;
                case  9:                                            // 経費
                    $input_data[$i][0] = $ctoku_han_kei;            // カプラ特注
                    $input_data[$i][1] = $ch_han_kei;               // カプラ標準
                break;
                case 10:                                            // 販管費及び一般管理費計
                    $input_data[$i][0] = $ctoku_han_all;            // カプラ特注
                    $input_data[$i][1] = $ch_han_all;               // カプラ標準
                break;
                case 11:                                            // 営業利益
                    $input_data[$i][0] = $ctoku_ope_profit;         // カプラ特注
                    $input_data[$i][1] = $ch_ope_profit;            // カプラ標準
                break;
                case 12:                                            // 業務委託収入
                    $input_data[$i][0] = $ctoku_gyoumu;             // カプラ特注
                    $input_data[$i][1] = $ch_gyoumu;                // カプラ標準
                break;
                case 13:                                            // 仕入割引
                    $input_data[$i][0] = $ctoku_swari;              // カプラ特注
                    $input_data[$i][1] = $ch_swari;                 // カプラ標準
                break;
                case 14:                                            // 営業外収益その他
                    $input_data[$i][0] = $ctoku_pother;             // カプラ特注
                    $input_data[$i][1] = $ch_pother;                // カプラ標準
                break;
                case 15:                                            // 営業外収益計
                    $input_data[$i][0] = $ctoku_nonope_profit_sum;  // カプラ特注
                    $input_data[$i][1] = $ch_nonope_profit_sum;     // カプラ標準
                break;
                case 16:                                            // 支払利息
                    $input_data[$i][0] = $ctoku_srisoku;            // カプラ特注
                    $input_data[$i][1] = $ch_srisoku;               // カプラ標準
                break;
                case 17:                                            // 営業外費用その他
                    $input_data[$i][0] = $ctoku_lother;             // カプラ特注
                    $input_data[$i][1] = $ch_lother;                // カプラ標準
                break;
                case 18:                                            // 営業外費用計
                    $input_data[$i][0] = $ctoku_nonope_loss_sum;    // カプラ特注
                    $input_data[$i][1] = $ch_nonope_loss_sum;       // カプラ標準
                break;
                case 19:                                            // 経常利益
                    $input_data[$i][0] = $ctoku_current_profit;     // カプラ特注
                    $input_data[$i][1] = $ch_current_profit;        // カプラ標準
                break;
                default:
                break;
            }
    }
    // カプラ特注登録
    $head  = "カプラ特注";
    $sec   = 0;
    insert_date($head,$item,$yyyymm,$input_data,$sec);
    // カプラ標準登録
    $head  = "カプラ標準";
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
            /////////// begin トランザクション開始
            if ($con = db_connect()) {
                query_affected_trans($con, "begin");
            } else {
                $_SESSION["s_sysmsg"] .= "データベースに接続できません";
                exit();
            }
            ////////// Insert Start
            $query = sprintf("insert into profit_loss_pl_history (pl_bs_ym, kin, note, last_date, last_user) values (%d, %d, '%s', CURRENT_TIMESTAMP, '%s')", $yyyymm, $input_data[$i][$sec], $item_in[$i], $_SESSION['User_ID']);
            if (query_affected_trans($con, $query) <= 0) {
                $_SESSION["s_sysmsg"] .= sprintf("%sの新規登録に失敗<br> %d", $item_in[$i], $yyyymm);
                query_affected_trans($con, "rollback");     // transaction rollback
                exit();
            }
            /////////// commit トランザクション終了
            query_affected_trans($con, "commit");
            $_SESSION["s_sysmsg"] = sprintf("<font color='yellow'>%d 損益データ 新規 登録完了</font>",$yyyymm);
        } else {
            /////////// begin トランザクション開始
            if ($con = db_connect()) {
                query_affected_trans($con, "begin");
            } else {
                $_SESSION["s_sysmsg"] .= "データベースに接続できません";
                exit();
            }
            ////////// UPDATE Start
            $query = sprintf("update profit_loss_pl_history set kin=%d, last_date=CURRENT_TIMESTAMP, last_user='%s' where pl_bs_ym=%d and note='%s'", $input_data[$i][$sec], $_SESSION['User_ID'], $yyyymm, $item_in[$i]);
            if (query_affected_trans($con, $query) <= 0) {
                $_SESSION["s_sysmsg"] .= sprintf("%sのUPDATEに失敗<br> %d", $item_in[$i], $yyyymm);
                query_affected_trans($con, "rollback");     // transaction rollback
                exit();
            }
            /////////// commit トランザクション終了
            query_affected_trans($con, "commit");
            $_SESSION["s_sysmsg"] = sprintf("<font color='yellow'>%d 損益データ 変更 完了</font>",$yyyymm);
        }
    }
    $_SESSION["s_sysmsg"] .= "当月のデータを登録しました。";
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
                        <?php
                        if ($_SESSION['User_ID'] == '300144') {
                            if ($keta == 0 && $tani == 1) {
                        ?>
                            &nbsp;
                            <input class='pt10b' type='submit' name='input_data' value='当月データ登録' onClick='return data_input_click(this)'>
                        <?php
                            } else {
                        ?>
                            <input class='pt10b' type='submit' name='input_data' value='当月データ登録' onClick='return data_input_click(this)' disabled>
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
                    <td rowspan='2' colspan='3' width='200' align='center' class='pt10b' bgcolor='#ffffc6'>項　　　目</td>
                    <td colspan='4' align='center' class='pt10b' bgcolor='#ffffc6'>特　　　注</td>
                    <td colspan='4' align='center' class='pt10b' bgcolor='#ffffc6'>標　　　準</td>
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
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo $p2_ctoku_uri ?>   </td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo $p1_ctoku_uri ?>   </td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo $ctoku_uri ?>      </td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo $rui_ctoku_uri ?>  </td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo $p2_ch_uri ?>   </td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo $p1_ch_uri ?>   </td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo $ch_uri ?>      </td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo $rui_ch_uri ?>  </td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo $p2_c_uri ?>   </td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo $p1_c_uri ?>   </td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo $c_uri ?>      </td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo $rui_c_uri ?>  </td>
                    <td nowrap align='left'  class='pt10' bgcolor='#d6d3ce'>実際売上高</td>
                </tr>
                <tr>
                    <td rowspan='6' width='10' align='center' valign='middle' class='pt10b' bgcolor='#ffffc6'>売上原価</td> <!-- 売上原価 -->
                    <td nowrap align='left' class='pt10b' bgcolor='white'>　期首材料仕掛品棚卸高</td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $p2_ctoku_invent ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $p1_ctoku_invent ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $ctoku_invent ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $rui_ctoku_invent ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $p2_ch_invent ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $p1_ch_invent ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $ch_invent ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $rui_ch_invent ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $p2_c_invent ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $p1_c_invent ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $c_invent ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $rui_c_invent ?></td>
                    <td nowrap align='left'  class='pt10'>標準原価による棚卸高</td>
                </tr>
                <tr>
                    <td nowrap align='left' class='pt10b' bgcolor='#e6e6e6'>　材料費(仕入高)</td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo $p2_ctoku_metarial ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo $p1_ctoku_metarial ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo $ctoku_metarial ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo $rui_ctoku_metarial ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo $p2_ch_metarial ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo $p1_ch_metarial ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo $ch_metarial ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo $rui_ch_metarial ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo $p2_c_metarial ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo $p1_c_metarial ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo $c_metarial ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo $rui_c_metarial ?></td>
                    <td nowrap align='left'  class='pt10'>買掛購入高比</td>
                </tr>
                <tr>
                    <td nowrap align='left' class='pt10b' bgcolor='white'>　労　　務　　費</td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $p2_ctoku_roumu ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $p1_ctoku_roumu ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $ctoku_roumu ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $rui_ctoku_roumu ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $p2_ch_roumu ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $p1_ch_roumu ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $ch_roumu ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $rui_ch_roumu ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $p2_c_roumu ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $p1_c_roumu ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $c_roumu ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $rui_c_roumu ?></td>
                    <td nowrap align='left'  class='pt10'>サービス割合比及び前半期売上高比</td>
                </tr>
                <tr>
                    <td nowrap align='left' class='pt10b' bgcolor='#e6e6e6'>　経　　　　　費</td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo $p2_ctoku_expense ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo $p1_ctoku_expense ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo $ctoku_expense ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo $rui_ctoku_expense ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo $p2_ch_expense ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo $p1_ch_expense ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo $ch_expense ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo $rui_ch_expense ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo $p2_c_expense ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo $p1_c_expense ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo $c_expense ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo $rui_c_expense ?></td>
                    <td nowrap align='left'  class='pt10'>サービス割合比及び前半期売上高比</td>
                </tr>
                <tr>
                    <td nowrap align='left' class='pt10b' bgcolor='white'>　期末材料仕掛品棚卸高</td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $p2_ctoku_endinv ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $p1_ctoku_endinv ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $ctoku_endinv ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $ctoku_endinv ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $p2_ch_endinv ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $p1_ch_endinv ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $ch_endinv ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $ch_endinv ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $p2_c_endinv ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $p1_c_endinv ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $c_endinv ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $c_endinv ?></td>
                    <td nowrap align='left'  class='pt10'>標準原価による棚卸高</td>
                </tr>
                <tr>
                    <td nowrap align='left' class='pt10b' bgcolor='#ffffc6'>　売　上　原　価</td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo $p2_ctoku_urigen ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo $p1_ctoku_urigen ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo $ctoku_urigen ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo $rui_ctoku_urigen ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo $p2_ch_urigen ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo $p1_ch_urigen ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo $ch_urigen ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo $rui_ch_urigen ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo $p2_c_urigen ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo $p1_c_urigen ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo $c_urigen ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo $rui_c_urigen ?></td>
                    <td nowrap align='left'  class='pt10'>　</td>
                </tr>
                <tr>
                    <td colspan='2' nowrap align='center' class='pt10b' bgcolor='#ceffce'>売　上　総　利　益</td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo $p2_ctoku_gross_profit ?></td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo $p1_ctoku_gross_profit ?></td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo $ctoku_gross_profit ?></td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo $rui_ctoku_gross_profit ?></td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo $p2_ch_gross_profit ?></td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo $p1_ch_gross_profit ?></td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo $ch_gross_profit ?></td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo $rui_ch_gross_profit ?></td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo $p2_c_gross_profit ?></td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo $p1_c_gross_profit ?></td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo $c_gross_profit ?></td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo $rui_c_gross_profit ?></td>
                    <td nowrap align='left'  class='pt10' bgcolor='#d6d3ce'>　</td>  <!-- 余白 -->
                </tr>
                <tr>
                    <td rowspan='3' width='10' align='center' valign='middle' class='pt10b' bgcolor='#ffffc6'></td> <!-- 販管費 -->
                    <td nowrap align='left' class='pt10b' bgcolor='#e6e6e6'>　人　　件　　費</td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo $p2_ctoku_han_jin ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo $p1_ctoku_han_jin ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo $ctoku_han_jin ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo $rui_ctoku_han_jin ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo $p2_ch_han_jin ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo $p1_ch_han_jin ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo $ch_han_jin ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo $rui_ch_han_jin ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo $p2_c_han_jin ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo $p1_c_han_jin ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo $c_han_jin ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo $rui_c_han_jin ?></td>
                    <td nowrap align='left'  class='pt10'>部門人員比率</td>
                </tr>
                <tr>
                    <td nowrap align='left' class='pt10b' bgcolor='white'>　経　　　　　費</td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $p2_ctoku_han_kei ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $p1_ctoku_han_kei ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $ctoku_han_kei ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $rui_ctoku_han_kei ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $p2_ch_han_kei ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $p1_ch_han_kei ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $ch_han_kei ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $rui_ch_han_kei ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $p2_c_han_kei ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $p1_c_han_kei ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $c_han_kei ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $rui_c_han_kei ?></td>
                    <td nowrap align='left'  class='pt10'>部門人員比率</td>
                </tr>
                <tr>
                    <td nowrap align='center' class='pt10b' bgcolor='#ffffc6'>販管費及び一般管理費計</td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo $p2_ctoku_han_all ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo $p1_ctoku_han_all ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo $ctoku_han_all ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo $rui_ctoku_han_all ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo $p2_ch_han_all ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo $p1_ch_han_all ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo $ch_han_all ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo $rui_ch_han_all ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo $p2_c_han_all ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo $p1_c_han_all ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo $c_han_all ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo $rui_c_han_all ?></td>
                    <td nowrap align='left'  class='pt10'>　</td>  <!-- 余白 -->
                </tr>
                <tr>
                    <td colspan='3' nowrap align='center' class='pt10b' bgcolor='#ceffce'>営　　業　　利　　益</td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo $p2_ctoku_ope_profit ?></td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo $p1_ctoku_ope_profit ?></td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo $ctoku_ope_profit ?></td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo $rui_ctoku_ope_profit ?></td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo $p2_ch_ope_profit ?></td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo $p1_ch_ope_profit ?></td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo $ch_ope_profit ?></td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo $rui_ch_ope_profit ?></td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo $p2_c_ope_profit ?></td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo $p1_c_ope_profit ?></td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo $c_ope_profit ?></td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo $rui_c_ope_profit ?></td>
                    <td nowrap align='left'  class='pt10'>　</td>  <!-- 余白 -->
                </tr>
                <tr>
                    <td rowspan='7' align='center' valign='middle' class='pt10b' bgcolor='#ceffce'>営業外損益</td>
                    <td rowspan='4' align='center' class='pt10' bgcolor='#ffffc6'></td> <!-- 余白 -->
                    <td nowrap align='left' class='pt10b' bgcolor='white'>　業務委託収入</td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $p2_ctoku_gyoumu ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $p1_ctoku_gyoumu ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $ctoku_gyoumu ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $rui_ctoku_gyoumu ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $p2_ch_gyoumu ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $p1_ch_gyoumu ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $ch_gyoumu ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $rui_ch_gyoumu ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $p2_c_gyoumu ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $p1_c_gyoumu ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $c_gyoumu ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $rui_c_gyoumu ?></td>
                    <td nowrap align='left'  class='pt10'>前半期実績の売上高比</td>
                </tr>
                <tr>
                    <td nowrap align='left' class='pt10b' bgcolor='#e6e6e6'>　仕　入　割　引</td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo $p2_ctoku_swari ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo $p1_ctoku_swari ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo $ctoku_swari ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo $rui_ctoku_swari ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo $p2_ch_swari ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo $p1_ch_swari ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo $ch_swari ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo $rui_ch_swari ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo $p2_c_swari ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo $p1_c_swari ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo $c_swari ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo $rui_c_swari ?></td>
                    <td nowrap align='left'  class='pt10'>前半期実績の売上高比</td>
                </tr>
                <tr>
                    <td nowrap align='left' class='pt10b' bgcolor='white'>　そ　　の　　他</td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $p2_ctoku_pother ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $p1_ctoku_pother ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $ctoku_pother ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $rui_ctoku_pother ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $p2_ch_pother ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $p1_ch_pother ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $ch_pother ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $rui_ch_pother ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $p2_c_pother ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $p1_c_pother ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $c_pother ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $rui_c_pother ?></td>
                    <td nowrap align='left'  class='pt10'>前半期実績の売上高比</td>
                </tr>
                <tr>
                    <td nowrap align='left' class='pt10b' bgcolor='#ffffc6'>　営業外収益 計</td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo $p2_ctoku_nonope_profit_sum ?>   </td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo $p1_ctoku_nonope_profit_sum ?>   </td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo $ctoku_nonope_profit_sum ?>      </td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo $rui_ctoku_nonope_profit_sum ?>  </td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo $p2_ch_nonope_profit_sum ?>   </td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo $p1_ch_nonope_profit_sum ?>   </td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo $ch_nonope_profit_sum ?>      </td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo $rui_ch_nonope_profit_sum ?>  </td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo $p2_c_nonope_profit_sum ?>   </td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo $p1_c_nonope_profit_sum ?>   </td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo $c_nonope_profit_sum ?>      </td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo $rui_c_nonope_profit_sum ?>  </td>
                    <td nowrap align='left'  class='pt10'>　</td> <!-- 余白 -->
                </tr>
                <tr>
                    <td rowspan='3' align='center' class='pt10' bgcolor='#ffffc6'></td> <!-- 余白 -->
                    <td nowrap align='left' class='pt10b' bgcolor='#e6e6e6'>　支　払　利　息</td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo $p2_ctoku_srisoku ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo $p1_ctoku_srisoku ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo $ctoku_srisoku ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo $rui_ctoku_srisoku ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo $p2_ch_srisoku ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo $p1_ch_srisoku ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo $ch_srisoku ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo $rui_ch_srisoku ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo $p2_c_srisoku ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo $p1_c_srisoku ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo $c_srisoku ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='#e6e6e6'><?php echo $rui_c_srisoku ?></td>
                    <td nowrap align='left'  class='pt10'>前半期実績の売上高比</td>
                </tr>
                <tr>
                    <td nowrap align='left' class='pt10b' bgcolor='white'>　そ　　の　　他</td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $p2_ctoku_lother ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $p1_ctoku_lother ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $ctoku_lother ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $rui_ctoku_lother ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $p2_ch_lother ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $p1_ch_lother ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $ch_lother ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $rui_ch_lother ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $p2_c_lother ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $p1_c_lother ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $c_lother ?></td>
                    <td nowrap align='right' class='pt10' bgcolor='white'><?php echo $rui_c_lother ?></td>
                    <td nowrap align='left'  class='pt10'>前半期実績の売上高比</td>
                </tr>
                <tr>
                    <td nowrap align='left' class='pt10b' bgcolor='#ffffc6'>　営業外費用 計</td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo $p2_ctoku_nonope_loss_sum ?>   </td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo $p1_ctoku_nonope_loss_sum ?>   </td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo $ctoku_nonope_loss_sum ?>      </td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo $rui_ctoku_nonope_loss_sum ?>  </td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo $p2_ch_nonope_loss_sum ?>   </td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo $p1_ch_nonope_loss_sum ?>   </td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo $ch_nonope_loss_sum ?>      </td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo $rui_ch_nonope_loss_sum ?>  </td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo $p2_c_nonope_loss_sum ?>   </td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo $p1_c_nonope_loss_sum ?>   </td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo $c_nonope_loss_sum ?>      </td>
                    <td nowrap align='right' class='pt10' bgcolor='#ffffc6'><?php echo $rui_c_nonope_loss_sum ?>  </td>
                    <td nowrap align='left'  class='pt10'>　</td> <!-- 余白 -->
                </tr>
                <tr>
                    <td colspan='3' nowrap align='center' class='pt10b' bgcolor='#ceffce'>経　　常　　利　　益</td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo $p2_ctoku_current_profit ?>   </td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo $p1_ctoku_current_profit ?>   </td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo $ctoku_current_profit ?>      </td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo $rui_ctoku_current_profit ?>  </td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo $p2_ch_current_profit ?>   </td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo $p1_ch_current_profit ?>   </td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo $ch_current_profit ?>      </td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo $rui_ch_current_profit ?>  </td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo $p2_c_current_profit ?>   </td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo $p1_c_current_profit ?>   </td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo $c_current_profit ?>      </td>
                    <td nowrap align='right' class='pt10b' bgcolor='#ceffce'><?php echo $rui_c_current_profit ?>  </td>
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
                            if ($comment_c != "") {
                                echo "<li><pre>$comment_c</pre></li>\n";
                            }
                            if ($comment_ctoku != "") {
                                echo "<li><pre>$comment_ctoku</pre></li>\n";
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
