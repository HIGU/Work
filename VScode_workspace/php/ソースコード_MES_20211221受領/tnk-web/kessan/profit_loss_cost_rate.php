<?php
//////////////////////////////////////////////////////////////////////////////
// 月次損益関係の原価率計算表(材料比率重視 推移監視)                        //
// Copyright (C) 2003-2005 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp      //
// Changed history                                                          //
// 2003/03/12 Created   profit_loss_cost_rate.php                           //
// 2003/03/13 StyleSheetを<link に設定 リンクファイルのコメントは           //
//                                      /* ... */ の１行にのみ対応(NN6.1)   //
// 2003/03/27 前半期・次半期のロジック変更 前期売上高比平均をロジック化     //
// 2004/05/11 左側のサイトメニューのオン・オフ ボタンを追加                 //
// 2004/09/07 合計の材料費・労務費経費をExcelの計算法方法に合わせるため変更 //
// 2005/06/15 MenuHeader class を使用して共通メニュー化及び認証方式へ変更   //
// 2005/08/29 set_focus()の中身を全てコメントアウト MenuHeaderに移行のため  //
//////////////////////////////////////////////////////////////////////////////
ini_set('error_reporting', E_ALL);          // E_ALL='2047' debug 用
ini_set('display_errors', '1');             // Error 表示 ON debug 用 リリース後コメント
session_start();                            // ini_set()の次に指定すること Script 最上行
require_once ('../function.php');           // define.php と pgsql.php を require_once している
require_once ('../tnk_func.php');           // TNK に依存する部分の関数を require_once している
require_once ('../MenuHeader.php');         // TNK 全共通 menu class
access_log();                               // Script Name は自動取得

///// TNK 共用メニュークラスのインスタンスを作成
$menu = new MenuHeader(0);                  // 認証チェック0=一般以上 戻り先=TOP_MENU タイトル未設定

////////////// サイト設定
// $menu->set_site(10, 7);                     // site_index=99(システムメニュー) site_id=60(テンプレート)
                                            // 月次・中間・決算 = 10 最後のメニュー = 99 を使用
                                            // 月次損益関係 = 7  下位メニュー無し <= 0

$current_script  = $menu->out_self();       // 現在実行中のスクリプト名を保存
$url_referer     = H_WEB_HOST . $menu->out_RetUrl();    // 呼出元をセットする

///// 期・月の取得
$ki = Ym_to_tnk($_SESSION['pl_ym']);
$tuki = substr($_SESSION['pl_ym'],4,2);
///// 対象当月
$yyyymm = (int)$_SESSION['pl_ym'];

//////////// タイトル名(ソースのタイトル名とフォームのタイトル名)
$menu->set_title("第 {$ki} 期　{$tuki} 月度　原 価 率 計 算 表 ");

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

/****** 次月へセットする関数 *****/
function forward_ym($ym) {
    if ($ym == "") {
        return FALSE;
    } else {
        $ym++;
        $yyyy = substr($ym, 0, 4);
        $mm   = substr($ym, 4, 2);
        if ($mm > 12) {
            $mm = "01";
            $yyyy++;
        }
        return (int)($yyyy . $mm);
    }
}
/***** 中間・期末決算年月の取得する関数 Backward *****/
function act_settl_ym($y4m2) {
    $yyyy = substr($y4m2, 0,4);
    $mm   = substr($y4m2, 4,2);
    if (($mm >= 1) && ($mm <= 3)) {
        $yyyy = ($yyyy - 1);                // 前年にセット
        return ($yyyy . "09");              // 中間年月
    } elseif (($mm >= 10) && ($mm <=12)) {
        return ($yyyy . "09");              // 中間年月
    } else {
        return ($yyyy . "03");              // 期末年月
    }
}
/***** 中間・期末決算年月の取得する関数 Forward *****/
function act_settl_ym_forward($y4m2) {
    $yyyy = substr($y4m2, 0,4);
    $mm   = substr($y4m2, 4,2);
    if (($mm >= 1) && ($mm <= 3)) {
        return ($yyyy . "09");              // 中間年月
    } elseif (($mm >= 10) && ($mm <=12)) {
        $yyyy = ($yyyy + 1);                // 次年にセット
        return ($yyyy . "03");              // 中間年月
    } else {
        $yyyy = ($yyyy + 1);                // 次年にセット
        return ($yyyy . "03");              // 期末年月
    }
}
///// 上記の関数を実行
$str_ym = act_settl_ym($yyyymm);

///// pl_str_ym の初期化
if ((!isset($_POST['backward_ki'])) && (!isset($_POST['forward_ki'])) ) {
    unset($_SESSION['pl_str_ym']);
}
///// 前期ボタンが押された時の処理
if (isset($_POST['backward_ki'])) {
    if (isset($_SESSION['pl_str_ym'])) {
        $str_ym = act_settl_ym($_SESSION['pl_str_ym']);
        if ($str_ym >= 200103) {
            $_SESSION['pl_str_ym'] = $str_ym;
        } else {
            $_SESSION['s_sysmsg'] .= sprintf("<font color='yellow'>%d の期末データはありません。</font>", $str_ym);
            $str_ym = $_SESSION['pl_str_ym'];
        }
    } else {
        $str_ym = act_settl_ym($str_ym);
        if ($str_ym >= 200103) {
            $_SESSION['pl_str_ym'] = $str_ym;
        } else {
            $_SESSION['s_sysmsg'] .= sprintf("<font color='yellow'>%d の期末データはありません。</font>", $str_ym);
            $str_ym = act_settl_ym($yyyymm);
        }
    }
}
///// 次期ボタンが押された時の処理
$today_ym = date("Ym");
if (isset($_POST['forward_ki'])) {
    if (isset($_SESSION['pl_str_ym'])) {
        $str_ym = act_settl_ym_forward($_SESSION['pl_str_ym']);
        if ($str_ym < $today_ym) {
            $_SESSION['pl_str_ym'] = $str_ym;
        } else {
            $_SESSION['s_sysmsg'] .= sprintf("<font color='yellow'>%d の期末データはありません。</font>", $str_ym);
            $str_ym = $_SESSION['pl_str_ym'];
        }
    } else {
        $str_ym = act_settl_ym_forward($str_ym);
        if ($str_ym < $today_ym) {
            $_SESSION['pl_str_ym'] = $str_ym;
        } else {
            $_SESSION['s_sysmsg'] .= sprintf("<font color='yellow'>%d の期末データはありません。</font>", $str_ym);
            $str_ym = act_settl_ym($yyyymm);
        }
    }
}

///// 表示単位を設定取得
if (isset($_POST['costrate_tani'])) {
    $_SESSION['costrate_tani'] = $_POST['costrate_tani'];
    $tani = $_SESSION['costrate_tani'];
} elseif (isset($_SESSION['costrate_tani'])) {
    $tani = $_SESSION['costrate_tani'];
} else {
    $tani = 1000;        // 初期値 表示単位 千円
    $_SESSION['costrate_tani'] = $tani;
}
///// 表示 小数部桁数 設定取得
if (isset($_POST['costrate_keta'])) {
    $_SESSION['costrate_keta'] = $_POST['costrate_keta'];
    $keta = $_SESSION['costrate_keta'];
} elseif (isset($_SESSION['costrate_keta'])) {
    $keta = $_SESSION['costrate_keta'];
} else {
    $keta = 0;          // 初期値 小数点以下桁数
    $_SESSION['costrate_keta'] = $keta;
}

//################################################################################################
///// 前期売上高比(売上原価率)の平均値の算出
$pre_str_ym = act_settl_ym($str_ym);        // 前々期の期末にセット
if ($pre_str_ym <= 200009) {
    $pre_str_ym = $str_ym;                  // 会社設立のチェック
}
    // カプラ
$query = sprintf("select sum(kin) from act_invent_gross_average_history where pl_bs_ym=%d and note like 'カプラ%%材料費'", $pre_str_ym);
getUniResult($query, $invent_zai_c);
if ($invent_zai_c == 0) {
    $_SESSION['s_sysmsg'] .= sprintf("総平均期末棚卸高 未登録<br>決算年月=%d", $pre_str_ym);
    header("Location: $url_referer");
    exit();
}
$query = sprintf("select sum(kin) from act_invent_gross_average_history where pl_bs_ym=%d and note like 'カプラ%%'", $pre_str_ym);
getUniResult($query, $invent_sum_c);
$invent_kei_c = ($invent_sum_c - $invent_zai_c);      // 合計から外作費の材料費を差し引いたのが労務費・経費
    // リニア
$query = sprintf("select sum(kin) from act_invent_gross_average_history where pl_bs_ym=%d and note like 'リニア%%材料費'", $pre_str_ym);
getUniResult($query, $invent_zai_l);
$query = sprintf("select sum(kin) from act_invent_gross_average_history where pl_bs_ym=%d and note like 'リニア%%'", $pre_str_ym);
getUniResult($query, $invent_sum_l);
$invent_kei_l = ($invent_sum_l - $invent_zai_l);      // 合計から外作費の材料費を差し引いたのが労務費・経費
    // 合計
$invent_zai_all = ($invent_zai_c + $invent_zai_l);
$invent_kei_all = ($invent_kei_c + $invent_kei_l);
$invent_sum_all = ($invent_sum_c + $invent_sum_l);
///// 材料費と労務費・経費の割合計算
$percent_zai_c   = number_format(($invent_zai_c / $invent_sum_c * 100), 1);
$p_zai_c         = ($percent_zai_c / 100);      // 計算用
$percent_kei_c   = number_format((100 - $percent_zai_c), 1);
$percent_sum_c   = number_format(($percent_zai_c + $percent_kei_c), 1);
$percent_zai_l   = number_format(($invent_zai_l / $invent_sum_l * 100), 1);
$p_zai_l         = ($percent_zai_l / 100);
$percent_kei_l   = number_format((100 - $percent_zai_l), 1);
$percent_sum_l   = number_format(($percent_zai_l + $percent_kei_l), 1);
$percent_zai_all = number_format(($invent_zai_all / $invent_sum_all * 100), 1);
$p_zai_a         = ($percent_zai_all / 100);
$percent_kei_all = number_format((100 - $percent_zai_all), 1);
$percent_sum_all = number_format(($percent_zai_all + $percent_kei_all), 1);
///// 期初データから順番に処理
$data      = array();
$view_data = array();
$tmp_ym    = $pre_str_ym;
for ($cnt=0; $cnt < 6; $cnt++) {
    $data[$cnt]['ym']      = forward_ym($tmp_ym);       // 半期末年月から次月を取得
    $tmp_ym = $data[$cnt]['ym'];
    ///// 売上高の取得
    $query = sprintf("select カプラ, リニア, 全体 from wrk_uriage where 年月=%d", $data[$cnt]['ym']);
    $res_uri = array();
    if (getResult($query, $res_uri) <= 0) {
        $_SESSION['s_sysmsg'] .= sprintf("売上経歴が未登録<br>年月=%d", $data[$cnt]['ym']);
        header("Location: $url_referer");
        exit();
    } else {
        $data[$cnt]['uri_c'] = $res_uri[0]['カプラ'];
        $data[$cnt]['uri_l'] = $res_uri[0]['リニア'];
        $data[$cnt]['uri_a'] = $res_uri[0]['全体'];
    }
    if ($cnt == 0) {        ///// 期初の処理
        ///// 期首棚卸高
        $data[$cnt]['s_tana_zai_c'] = $invent_zai_c;
        $data[$cnt]['s_tana_kei_c'] = $invent_kei_c;
        $data[$cnt]['s_tana_sum_c'] = $invent_sum_c;
        $data[$cnt]['s_tana_zai_l'] = $invent_zai_l;
        $data[$cnt]['s_tana_kei_l'] = $invent_kei_l;
        $data[$cnt]['s_tana_sum_l'] = $invent_sum_l;
        $data[$cnt]['s_tana_zai_a'] = $invent_zai_all;
        $data[$cnt]['s_tana_kei_a'] = $invent_kei_all;
        $data[$cnt]['s_tana_sum_a'] = $invent_sum_all;
    } else {            ///// 通常月の処理
        ///// 期首棚卸高
        $data[$cnt]['s_tana_zai_c'] = ($data[$cnt-1]['e_tana_zai_c'] * (-1));
        $data[$cnt]['s_tana_kei_c'] = ($data[$cnt-1]['e_tana_kei_c'] * (-1));
        $data[$cnt]['s_tana_sum_c'] = ($data[$cnt-1]['e_tana_sum_c'] * (-1));
        $data[$cnt]['s_tana_zai_l'] = ($data[$cnt-1]['e_tana_zai_l'] * (-1));
        $data[$cnt]['s_tana_kei_l'] = ($data[$cnt-1]['e_tana_kei_l'] * (-1));
        $data[$cnt]['s_tana_sum_l'] = ($data[$cnt-1]['e_tana_sum_l'] * (-1));
        $data[$cnt]['s_tana_zai_a'] = ($data[$cnt-1]['e_tana_zai_a'] * (-1));
        $data[$cnt]['s_tana_kei_a'] = ($data[$cnt-1]['e_tana_kei_a'] * (-1));
        $data[$cnt]['s_tana_sum_a'] = ($data[$cnt-1]['e_tana_sum_a'] * (-1));
    }
    ///// 当月仕入発生高
        // 材料費
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='カプラ材料費'", $data[$cnt]['ym']);
    if (getUniResult($query, $metarial_c) < 1) {
        $_SESSION['s_sysmsg'] .= sprintf("カプラ材料費が未登録<br>年月=%d", $data[$cnt]['ym']);
        header("Location: $url_referer");
        exit();
    } else {
        $data[$cnt]['metarial_c']      = $metarial_c;
    }
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='リニア材料費'", $data[$cnt]['ym']);
    if (getUniResult($query, $metarial_l) < 1) {
        $_SESSION['s_sysmsg'] .= sprintf("リニア材料費が未登録<br>年月=%d", $data[$cnt]['ym']);
        header("Location: $url_referer");
        exit();
    } else {
        $data[$cnt]['metarial_l']      = $metarial_l;
    }
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='全体材料費'", $data[$cnt]['ym']);
    if (getUniResult($query, $metarial_a) < 1) {
        $_SESSION['s_sysmsg'] .= sprintf("全体材料費が未登録<br>年月=%d", $data[$cnt]['ym']);
        header("Location: $url_referer");
        exit();
    } else {
        $data[$cnt]['metarial_a']      = $metarial_a;
    }
    
        // 労務費・経費
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='カプラ労務費'", $data[$cnt]['ym']);
    if (getUniResult($query, $roumu_c) < 1) {
        $_SESSION['s_sysmsg'] .= sprintf("カプラ労務費が未登録<br>年月=%d", $data[$cnt]['ym']);
        header("Location: $url_referer");
        exit();
    }
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='リニア労務費'", $data[$cnt]['ym']);
    if (getUniResult($query, $roumu_l) < 1) {
        $_SESSION['s_sysmsg'] .= sprintf("カプラ労務費が未登録<br>年月=%d", $data[$cnt]['ym']);
        header("Location: $url_referer");
        exit();
    }
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='全体労務費'", $data[$cnt]['ym']);
    if (getUniResult($query, $roumu_a) < 1) {
        $_SESSION['s_sysmsg'] .= sprintf("全体労務費が未登録<br>年月=%d", $data[$cnt]['ym']);
        header("Location: $url_referer");
        exit();
    }
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='カプラ製造経費'", $data[$cnt]['ym']);
    if (getUniResult($query, $keihi_c) < 1) {
        $_SESSION['s_sysmsg'] .= sprintf("カプラ製造経費が未登録<br>年月=%d", $data[$cnt]['ym']);
        header("Location: $url_referer");
        exit();
    }
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='リニア製造経費'", $data[$cnt]['ym']);
    if (getUniResult($query, $keihi_l) < 1) {
        $_SESSION['s_sysmsg'] .= sprintf("リニア製造経費が未登録<br>年月=%d", $data[$cnt]['ym']);
        header("Location: $url_referer");
        exit();
    }
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='全体製造経費'", $data[$cnt]['ym']);
    if (getUniResult($query, $keihi_a) < 1) {
        $_SESSION['s_sysmsg'] .= sprintf("全体製造経費が未登録<br>年月=%d", $data[$cnt]['ym']);
        header("Location: $url_referer");
        exit();
    }
    $data[$cnt]['expense_c'] = ($roumu_c + $keihi_c);
    $data[$cnt]['expense_l'] = ($roumu_l + $keihi_l);
    $data[$cnt]['expense_a'] = ($roumu_a + $keihi_a);
    
    $data[$cnt]['shi_c'] = $data[$cnt]['metarial_c'] + $data[$cnt]['expense_c'];
    $data[$cnt]['shi_l'] = $data[$cnt]['metarial_l'] + $data[$cnt]['expense_l'];
    $data[$cnt]['shi_a'] = $data[$cnt]['metarial_a'] + $data[$cnt]['expense_a'];
    ///// 期末棚卸高
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='カプラ期末棚卸高'", $data[$cnt]['ym']);
    if (getUniResult($query, $e_tana_c) < 1) {
        $_SESSION['s_sysmsg'] .= sprintf("カプラ期末棚卸高が未登録<br>年月=%d", $data[$cnt]['ym']);
        header("Location: $url_referer");
        exit();
    } else {
        $data[$cnt]['e_tana_sum_c'] = $e_tana_c;
        $data[$cnt]['e_tana_zai_c'] = Uround(($p_zai_c * $data[$cnt]['e_tana_sum_c']),0);
        $data[$cnt]['e_tana_kei_c'] = $data[$cnt]['e_tana_sum_c'] - $data[$cnt]['e_tana_zai_c'];
    }
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='リニア期末棚卸高'", $data[$cnt]['ym']);
    if (getUniResult($query, $e_tana_l) < 1) {
        $_SESSION['s_sysmsg'] .= sprintf("リニア期末棚卸高が未登録<br>年月=%d", $data[$cnt]['ym']);
        header("Location: $url_referer");
        exit();
    } else {
        $data[$cnt]['e_tana_sum_l'] = $e_tana_l;
        $data[$cnt]['e_tana_zai_l'] = Uround(($p_zai_l * $data[$cnt]['e_tana_sum_l']),0);
        $data[$cnt]['e_tana_kei_l'] = $data[$cnt]['e_tana_sum_l'] - $data[$cnt]['e_tana_zai_l'];
    }
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='全体期末棚卸高'", $data[$cnt]['ym']);
    if (getUniResult($query, $e_tana_a) < 1) {
        $_SESSION['s_sysmsg'] .= sprintf("全体期末棚卸高が未登録<br>年月=%d", $data[$cnt]['ym']);
        header("Location: $url_referer");
        exit();
    } else {
        $data[$cnt]['e_tana_sum_a'] = $e_tana_a;
        $data[$cnt]['e_tana_zai_a'] = Uround(($p_zai_a * $data[$cnt]['e_tana_sum_a']),0);
        $data[$cnt]['e_tana_kei_a'] = $data[$cnt]['e_tana_sum_a'] - $data[$cnt]['e_tana_zai_a'];
    }
    $data[$cnt]['e_tana_zai_c'] = ($data[$cnt]['e_tana_zai_c'] * (-1));     // 符号反転
    $data[$cnt]['e_tana_kei_c'] = ($data[$cnt]['e_tana_kei_c'] * (-1));
    $data[$cnt]['e_tana_sum_c'] = ($data[$cnt]['e_tana_sum_c'] * (-1));
    $data[$cnt]['e_tana_zai_l'] = ($data[$cnt]['e_tana_zai_l'] * (-1));
    $data[$cnt]['e_tana_kei_l'] = ($data[$cnt]['e_tana_kei_l'] * (-1));
    $data[$cnt]['e_tana_sum_l'] = ($data[$cnt]['e_tana_sum_l'] * (-1));
    $data[$cnt]['e_tana_zai_a'] = ($data[$cnt]['e_tana_zai_a'] * (-1));
    $data[$cnt]['e_tana_kei_a'] = ($data[$cnt]['e_tana_kei_a'] * (-1));
    $data[$cnt]['e_tana_sum_a'] = ($data[$cnt]['e_tana_sum_a'] * (-1));
    ///// 売上原価の計算
        // 期首棚卸高 ＋ 当月仕入(材料)発生(経費) ＋ （−期末棚卸高) を各項目毎に計算する
    $data[$cnt]['gen_zai_c'] = $data[$cnt]['s_tana_zai_c'] + $data[$cnt]['metarial_c'] + ($data[$cnt]['e_tana_zai_c']);
    $data[$cnt]['gen_kei_c'] = $data[$cnt]['s_tana_kei_c'] + $data[$cnt]['expense_c']  + ($data[$cnt]['e_tana_kei_c']);
    $data[$cnt]['gen_sum_c'] = $data[$cnt]['s_tana_sum_c'] + $data[$cnt]['shi_c']      + ($data[$cnt]['e_tana_sum_c']);
    $data[$cnt]['gen_zai_l'] = $data[$cnt]['s_tana_zai_l'] + $data[$cnt]['metarial_l'] + ($data[$cnt]['e_tana_zai_l']);
    $data[$cnt]['gen_kei_l'] = $data[$cnt]['s_tana_kei_l'] + $data[$cnt]['expense_l']  + ($data[$cnt]['e_tana_kei_l']);
    $data[$cnt]['gen_sum_l'] = $data[$cnt]['s_tana_sum_l'] + $data[$cnt]['shi_l']      + ($data[$cnt]['e_tana_sum_l']);
    $data[$cnt]['gen_zai_a'] = $data[$cnt]['s_tana_zai_a'] + $data[$cnt]['metarial_a'] + ($data[$cnt]['e_tana_zai_a']);
    $data[$cnt]['gen_kei_a'] = $data[$cnt]['s_tana_kei_a'] + $data[$cnt]['expense_a']  + ($data[$cnt]['e_tana_kei_a']);
    $data[$cnt]['gen_sum_a'] = $data[$cnt]['s_tana_sum_a'] + $data[$cnt]['shi_a']      + ($data[$cnt]['e_tana_sum_a']);
    ///// 売上高比(売上原価率)
        // 売上原価 ／ 売上高 ＝ 売上原価率(売上高比)  各項目毎に計算する
    $data[$cnt]['ritu_zai_c'] = Uround(($data[$cnt]['gen_zai_c'] / $data[$cnt]['uri_c']) * 100, 1);
    $data[$cnt]['ritu_kei_c'] = Uround(($data[$cnt]['gen_kei_c'] / $data[$cnt]['uri_c']) * 100, 1);
    $data[$cnt]['ritu_sum_c'] = Uround(($data[$cnt]['gen_sum_c'] / $data[$cnt]['uri_c']) * 100, 1);
    $data[$cnt]['ritu_zai_l'] = Uround(($data[$cnt]['gen_zai_l'] / $data[$cnt]['uri_l']) * 100, 1);
    $data[$cnt]['ritu_kei_l'] = Uround(($data[$cnt]['gen_kei_l'] / $data[$cnt]['uri_l']) * 100, 1);
    $data[$cnt]['ritu_sum_l'] = Uround(($data[$cnt]['gen_sum_l'] / $data[$cnt]['uri_l']) * 100, 1);
    $data[$cnt]['ritu_zai_a'] = Uround(($data[$cnt]['gen_zai_a'] / $data[$cnt]['uri_a']) * 100, 1);
    $data[$cnt]['ritu_kei_a'] = Uround(($data[$cnt]['gen_kei_a'] / $data[$cnt]['uri_a']) * 100, 1);
    $data[$cnt]['ritu_sum_a'] = Uround(($data[$cnt]['gen_sum_a'] / $data[$cnt]['uri_a']) * 100, 1);
    
    $view_data[$cnt]['ritu_zai_c'] = number_format($data[$cnt]['ritu_zai_c'], 1);
    $view_data[$cnt]['ritu_kei_c'] = number_format($data[$cnt]['ritu_kei_c'], 1);
    $view_data[$cnt]['ritu_sum_c'] = number_format($data[$cnt]['ritu_sum_c'], 1);
    $view_data[$cnt]['ritu_zai_l'] = number_format($data[$cnt]['ritu_zai_l'], 1);
    $view_data[$cnt]['ritu_kei_l'] = number_format($data[$cnt]['ritu_kei_l'], 1);
    $view_data[$cnt]['ritu_sum_l'] = number_format($data[$cnt]['ritu_sum_l'], 1);
    $view_data[$cnt]['ritu_zai_a'] = number_format($data[$cnt]['ritu_zai_a'], 1);
    $view_data[$cnt]['ritu_kei_a'] = number_format($data[$cnt]['ritu_kei_a'], 1);
    $view_data[$cnt]['ritu_sum_a'] = number_format($data[$cnt]['ritu_sum_a'], 1);
    
}
$pre_ritu_zai_c = 0;        // 初期化
$pre_ritu_kei_c = 0;
$pre_ritu_sum_c = 0;
$pre_ritu_zai_l = 0;
$pre_ritu_kei_l = 0;
$pre_ritu_sum_l = 0;
$pre_ritu_zai_a = 0;
$pre_ritu_kei_a = 0;
$pre_ritu_sum_a = 0;
for ($cnt = 0; $cnt < 6; $cnt++) {
    $pre_ritu_zai_c += $data[$cnt]['ritu_zai_c'];
    $pre_ritu_kei_c += $data[$cnt]['ritu_kei_c'];
    $pre_ritu_sum_c += $data[$cnt]['ritu_sum_c'];
    $pre_ritu_zai_l += $data[$cnt]['ritu_zai_l'];
    $pre_ritu_kei_l += $data[$cnt]['ritu_kei_l'];
    $pre_ritu_sum_l += $data[$cnt]['ritu_sum_l'];
    $pre_ritu_zai_a += $data[$cnt]['ritu_zai_a'];
    $pre_ritu_kei_a += $data[$cnt]['ritu_kei_a'];
    $pre_ritu_sum_a += $data[$cnt]['ritu_sum_a'];
}
$view_pre_ritu_zai_c = number_format($pre_ritu_zai_c / 6, 1);
$view_pre_ritu_kei_c = number_format($pre_ritu_kei_c / 6, 1);
$view_pre_ritu_sum_c = number_format($pre_ritu_sum_c / 6, 1);
$view_pre_ritu_zai_l = number_format($pre_ritu_zai_l / 6, 1);
$view_pre_ritu_kei_l = number_format($pre_ritu_kei_l / 6, 1);
$view_pre_ritu_sum_l = number_format($pre_ritu_sum_l / 6, 1);
$view_pre_ritu_zai_a = number_format($pre_ritu_zai_a / 6, 1);
$view_pre_ritu_kei_a = number_format($pre_ritu_kei_a / 6, 1);
$view_pre_ritu_sum_a = number_format($pre_ritu_sum_a / 6, 1);
//################################################################################################

///// 決算月の総平均単価による期末棚卸高の明細取得
    // カプラ
$query = sprintf("select sum(kin) from act_invent_gross_average_history where pl_bs_ym=%d and note like 'カプラ%%材料費'", $str_ym);
getUniResult($query, $invent_zai_c);
if ($invent_zai_c == 0) {
    $_SESSION['s_sysmsg'] .= sprintf("総平均期末棚卸高 未登録<br>決算年月=%d", $str_ym);
    header("Location: $url_referer");
    exit();
}
$query = sprintf("select sum(kin) from act_invent_gross_average_history where pl_bs_ym=%d and note like 'カプラ%%'", $str_ym);
getUniResult($query, $invent_sum_c);
$invent_kei_c = ($invent_sum_c - $invent_zai_c);      // 合計から外作費の材料費を差し引いたのが労務費・経費
    // リニア
$query = sprintf("select sum(kin) from act_invent_gross_average_history where pl_bs_ym=%d and note like 'リニア%%材料費'", $str_ym);
getUniResult($query, $invent_zai_l);
$query = sprintf("select sum(kin) from act_invent_gross_average_history where pl_bs_ym=%d and note like 'リニア%%'", $str_ym);
getUniResult($query, $invent_sum_l);
$invent_kei_l = ($invent_sum_l - $invent_zai_l);      // 合計から外作費の材料費を差し引いたのが労務費・経費
    // 合計
$invent_zai_all = ($invent_zai_c + $invent_zai_l);
$invent_kei_all = ($invent_kei_c + $invent_kei_l);
$invent_sum_all = ($invent_sum_c + $invent_sum_l);
///// 材料費と労務費・経費の割合計算
$percent_zai_c   = number_format(($invent_zai_c / $invent_sum_c * 100), 1);
$p_zai_c         = ($percent_zai_c / 100);      // 計算用
$percent_kei_c   = number_format((100 - $percent_zai_c), 1);
$percent_sum_c   = number_format(($percent_zai_c + $percent_kei_c), 1);
$percent_zai_l   = number_format(($invent_zai_l / $invent_sum_l * 100), 1);
$p_zai_l         = ($percent_zai_l / 100);
$percent_kei_l   = number_format((100 - $percent_zai_l), 1);
$percent_sum_l   = number_format(($percent_zai_l + $percent_kei_l), 1);
$percent_zai_all = number_format(($invent_zai_all / $invent_sum_all * 100), 1);
$p_zai_a         = ($percent_zai_all / 100);
$percent_kei_all = number_format((100 - $percent_zai_all), 1);
$percent_sum_all = number_format(($percent_zai_all + $percent_kei_all), 1);
    // view data 生成
$view_i_zai_c   = number_format($invent_zai_c / $tani, $keta);
$view_i_kei_c   = number_format($invent_kei_c / $tani, $keta);
$view_i_sum_c   = number_format($invent_sum_c / $tani, $keta);
$view_i_zai_l   = number_format($invent_zai_l / $tani, $keta);
$view_i_kei_l   = number_format($invent_kei_l / $tani, $keta);
$view_i_sum_l   = number_format($invent_sum_l / $tani, $keta);
$view_i_zai_all = number_format($invent_zai_all / $tani, $keta);
$view_i_kei_all = number_format($invent_kei_all / $tani, $keta);
$view_i_sum_all = number_format($invent_sum_all / $tani, $keta);
///// 期初データから順番に処理
$data      = array();
$view_data = array();
$tmp_ym    = $str_ym;
for ($cnt=0; $tmp_ym < $yyyymm; $cnt++) {
    $data[$cnt]['ym']      = forward_ym($tmp_ym);       // 半期末年月から次月を取得
    $view_data[$cnt]['ym'] = (substr($data[$cnt]['ym'], 0, 4) . "/" . substr($data[$cnt]['ym'], 4, 2));
    $tmp_ym = $data[$cnt]['ym'];
    ///// 売上高の取得
    $query = sprintf("select カプラ, リニア, 全体 from wrk_uriage where 年月=%d", $data[$cnt]['ym']);
    $res_uri = array();
    if (getResult($query, $res_uri) <= 0) {
        $_SESSION['s_sysmsg'] .= sprintf("売上経歴が未登録<br>年月=%d", $data[$cnt]['ym']);
        header("Location: $url_referer");
        exit();
    } else {
        $data[$cnt]['uri_c'] = $res_uri[0]['カプラ'];
        $data[$cnt]['uri_l'] = $res_uri[0]['リニア'];
        $data[$cnt]['uri_a'] = $res_uri[0]['全体'];
        $view_data[$cnt]['uri_c'] = number_format($data[$cnt]['uri_c'] / $tani, $keta);
        $view_data[$cnt]['uri_l'] = number_format($data[$cnt]['uri_l'] / $tani, $keta);
        $view_data[$cnt]['uri_a'] = number_format($data[$cnt]['uri_a'] / $tani, $keta);
    }
    if ($cnt == 0) {        ///// 期初の処理
        ///// 期首棚卸高
        $data[$cnt]['s_tana_zai_c'] = $invent_zai_c;
        $data[$cnt]['s_tana_kei_c'] = $invent_kei_c;
        $data[$cnt]['s_tana_sum_c'] = $invent_sum_c;
        $data[$cnt]['s_tana_zai_l'] = $invent_zai_l;
        $data[$cnt]['s_tana_kei_l'] = $invent_kei_l;
        $data[$cnt]['s_tana_sum_l'] = $invent_sum_l;
        $data[$cnt]['s_tana_zai_a'] = $invent_zai_all;
        $data[$cnt]['s_tana_kei_a'] = $invent_kei_all;
        $data[$cnt]['s_tana_sum_a'] = $invent_sum_all;
        $view_data[$cnt]['s_tana_zai_c'] = $view_i_zai_c  ;
        $view_data[$cnt]['s_tana_kei_c'] = $view_i_kei_c  ;
        $view_data[$cnt]['s_tana_sum_c'] = $view_i_sum_c  ;
        $view_data[$cnt]['s_tana_zai_l'] = $view_i_zai_l  ;
        $view_data[$cnt]['s_tana_kei_l'] = $view_i_kei_l  ;
        $view_data[$cnt]['s_tana_sum_l'] = $view_i_sum_l  ;
        $view_data[$cnt]['s_tana_zai_a'] = $view_i_zai_all;
        $view_data[$cnt]['s_tana_kei_a'] = $view_i_kei_all;
        $view_data[$cnt]['s_tana_sum_a'] = $view_i_sum_all;
    } else {            ///// 通常月の処理
        ///// 期首棚卸高
        $data[$cnt]['s_tana_zai_c'] = ($data[$cnt-1]['e_tana_zai_c'] * (-1));
        $data[$cnt]['s_tana_kei_c'] = ($data[$cnt-1]['e_tana_kei_c'] * (-1));
        $data[$cnt]['s_tana_sum_c'] = ($data[$cnt-1]['e_tana_sum_c'] * (-1));
        $data[$cnt]['s_tana_zai_l'] = ($data[$cnt-1]['e_tana_zai_l'] * (-1));
        $data[$cnt]['s_tana_kei_l'] = ($data[$cnt-1]['e_tana_kei_l'] * (-1));
        $data[$cnt]['s_tana_sum_l'] = ($data[$cnt-1]['e_tana_sum_l'] * (-1));
        $data[$cnt]['s_tana_zai_a'] = ($data[$cnt-1]['e_tana_zai_a'] * (-1));
        $data[$cnt]['s_tana_kei_a'] = ($data[$cnt-1]['e_tana_kei_a'] * (-1));
        $data[$cnt]['s_tana_sum_a'] = ($data[$cnt-1]['e_tana_sum_a'] * (-1));
        
        $view_data[$cnt]['s_tana_zai_c'] = number_format($data[$cnt]['s_tana_zai_c'] / $tani, $keta);
        $view_data[$cnt]['s_tana_kei_c'] = number_format($data[$cnt]['s_tana_kei_c'] / $tani, $keta);
        $view_data[$cnt]['s_tana_sum_c'] = number_format($data[$cnt]['s_tana_sum_c'] / $tani, $keta);
        $view_data[$cnt]['s_tana_zai_l'] = number_format($data[$cnt]['s_tana_zai_l'] / $tani, $keta);
        $view_data[$cnt]['s_tana_kei_l'] = number_format($data[$cnt]['s_tana_kei_l'] / $tani, $keta);
        $view_data[$cnt]['s_tana_sum_l'] = number_format($data[$cnt]['s_tana_sum_l'] / $tani, $keta);
        $view_data[$cnt]['s_tana_zai_a'] = number_format($data[$cnt]['s_tana_zai_a'] / $tani, $keta);
        $view_data[$cnt]['s_tana_kei_a'] = number_format($data[$cnt]['s_tana_kei_a'] / $tani, $keta);
        $view_data[$cnt]['s_tana_sum_a'] = number_format($data[$cnt]['s_tana_sum_a'] / $tani, $keta);
    }
    ///// 当月仕入発生高
        // 材料費
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='カプラ材料費'", $data[$cnt]['ym']);
    if (getUniResult($query, $metarial_c) < 1) {
        $_SESSION['s_sysmsg'] .= sprintf("カプラ材料費が未登録<br>年月=%d", $data[$cnt]['ym']);
        header("Location: $url_referer");
        exit();
    } else {
        $data[$cnt]['metarial_c']      = $metarial_c;
        $view_data[$cnt]['metarial_c'] = number_format(($metarial_c / $tani), $keta);
    }
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='リニア材料費'", $data[$cnt]['ym']);
    if (getUniResult($query, $metarial_l) < 1) {
        $_SESSION['s_sysmsg'] .= sprintf("リニア材料費が未登録<br>年月=%d", $data[$cnt]['ym']);
        header("Location: $url_referer");
        exit();
    } else {
        $data[$cnt]['metarial_l']      = $metarial_l;
        $view_data[$cnt]['metarial_l'] = number_format(($metarial_l / $tani), $keta);
    }
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='全体材料費'", $data[$cnt]['ym']);
    if (getUniResult($query, $metarial_a) < 1) {
        $_SESSION['s_sysmsg'] .= sprintf("全体材料費が未登録<br>年月=%d", $data[$cnt]['ym']);
        header("Location: $url_referer");
        exit();
    } else {
        $data[$cnt]['metarial_a']      = $metarial_a;
        $view_data[$cnt]['metarial_a'] = number_format(($metarial_a / $tani), $keta);
    }
    
        // 労務費・経費
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='カプラ労務費'", $data[$cnt]['ym']);
    if (getUniResult($query, $roumu_c) < 1) {
        $_SESSION['s_sysmsg'] .= sprintf("カプラ労務費が未登録<br>年月=%d", $data[$cnt]['ym']);
        header("Location: $url_referer");
        exit();
    }
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='リニア労務費'", $data[$cnt]['ym']);
    if (getUniResult($query, $roumu_l) < 1) {
        $_SESSION['s_sysmsg'] .= sprintf("カプラ労務費が未登録<br>年月=%d", $data[$cnt]['ym']);
        header("Location: $url_referer");
        exit();
    }
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='全体労務費'", $data[$cnt]['ym']);
    if (getUniResult($query, $roumu_a) < 1) {
        $_SESSION['s_sysmsg'] .= sprintf("全体労務費が未登録<br>年月=%d", $data[$cnt]['ym']);
        header("Location: $url_referer");
        exit();
    }
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='カプラ製造経費'", $data[$cnt]['ym']);
    if (getUniResult($query, $keihi_c) < 1) {
        $_SESSION['s_sysmsg'] .= sprintf("カプラ製造経費が未登録<br>年月=%d", $data[$cnt]['ym']);
        header("Location: $url_referer");
        exit();
    }
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='リニア製造経費'", $data[$cnt]['ym']);
    if (getUniResult($query, $keihi_l) < 1) {
        $_SESSION['s_sysmsg'] .= sprintf("リニア製造経費が未登録<br>年月=%d", $data[$cnt]['ym']);
        header("Location: $url_referer");
        exit();
    }
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='全体製造経費'", $data[$cnt]['ym']);
    if (getUniResult($query, $keihi_a) < 1) {
        $_SESSION['s_sysmsg'] .= sprintf("全体製造経費が未登録<br>年月=%d", $data[$cnt]['ym']);
        header("Location: $url_referer");
        exit();
    }
    $data[$cnt]['expense_c'] = ($roumu_c + $keihi_c);
    $data[$cnt]['expense_l'] = ($roumu_l + $keihi_l);
    $data[$cnt]['expense_a'] = ($roumu_a + $keihi_a);
    $view_data[$cnt]['expense_c'] = number_format($data[$cnt]['expense_c'] / $tani, $keta);
    $view_data[$cnt]['expense_l'] = number_format($data[$cnt]['expense_l'] / $tani, $keta);
    $view_data[$cnt]['expense_a'] = number_format($data[$cnt]['expense_a'] / $tani, $keta);
    
    $data[$cnt]['shi_c'] = $data[$cnt]['metarial_c'] + $data[$cnt]['expense_c'];
    $data[$cnt]['shi_l'] = $data[$cnt]['metarial_l'] + $data[$cnt]['expense_l'];
    $data[$cnt]['shi_a'] = $data[$cnt]['metarial_a'] + $data[$cnt]['expense_a'];
    $view_data[$cnt]['shi_c'] = number_format($data[$cnt]['shi_c'] / $tani, $keta);
    $view_data[$cnt]['shi_l'] = number_format($data[$cnt]['shi_l'] / $tani, $keta);
    $view_data[$cnt]['shi_a'] = number_format($data[$cnt]['shi_a'] / $tani, $keta);
    ///// 期末棚卸高
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='カプラ期末棚卸高'", $data[$cnt]['ym']);
    if (getUniResult($query, $e_tana_c) < 1) {
        $_SESSION['s_sysmsg'] .= sprintf("カプラ期末棚卸高が未登録<br>年月=%d", $data[$cnt]['ym']);
        header("Location: $url_referer");
        exit();
    } else {
        $data[$cnt]['e_tana_sum_c'] = $e_tana_c;
        $data[$cnt]['e_tana_zai_c'] = Uround(($p_zai_c * $data[$cnt]['e_tana_sum_c']),0);
        $data[$cnt]['e_tana_kei_c'] = $data[$cnt]['e_tana_sum_c'] - $data[$cnt]['e_tana_zai_c'];
    }
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='リニア期末棚卸高'", $data[$cnt]['ym']);
    if (getUniResult($query, $e_tana_l) < 1) {
        $_SESSION['s_sysmsg'] .= sprintf("リニア期末棚卸高が未登録<br>年月=%d", $data[$cnt]['ym']);
        header("Location: $url_referer");
        exit();
    } else {
        $data[$cnt]['e_tana_sum_l'] = $e_tana_l;
        $data[$cnt]['e_tana_zai_l'] = Uround(($p_zai_l * $data[$cnt]['e_tana_sum_l']),0);
        $data[$cnt]['e_tana_kei_l'] = $data[$cnt]['e_tana_sum_l'] - $data[$cnt]['e_tana_zai_l'];
    }
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='全体期末棚卸高'", $data[$cnt]['ym']);
    if (getUniResult($query, $e_tana_a) < 1) {
        $_SESSION['s_sysmsg'] .= sprintf("全体期末棚卸高が未登録<br>年月=%d", $data[$cnt]['ym']);
        header("Location: $url_referer");
        exit();
    } else {
        $data[$cnt]['e_tana_sum_a'] = $e_tana_a;
        // $data[$cnt]['e_tana_zai_a'] = Uround(($p_zai_a * $data[$cnt]['e_tana_sum_a']),0);
        // $data[$cnt]['e_tana_kei_a'] = $data[$cnt]['e_tana_sum_a'] - $data[$cnt]['e_tana_zai_a'];
        // Excelの計算法方法に合わせるため変更 2004/09/07
        $data[$cnt]['e_tana_zai_a'] = $data[$cnt]['e_tana_zai_c'] + $data[$cnt]['e_tana_zai_l'];
        $data[$cnt]['e_tana_kei_a'] = $data[$cnt]['e_tana_kei_c'] + $data[$cnt]['e_tana_kei_l'];
        // e_tana_sum_a は検証用にテーブルのデータを使っている(合計が合うかチェックできる)
    }
    $data[$cnt]['e_tana_zai_c'] = ($data[$cnt]['e_tana_zai_c'] * (-1));     // 符号反転
    $data[$cnt]['e_tana_kei_c'] = ($data[$cnt]['e_tana_kei_c'] * (-1));
    $data[$cnt]['e_tana_sum_c'] = ($data[$cnt]['e_tana_sum_c'] * (-1));
    $data[$cnt]['e_tana_zai_l'] = ($data[$cnt]['e_tana_zai_l'] * (-1));
    $data[$cnt]['e_tana_kei_l'] = ($data[$cnt]['e_tana_kei_l'] * (-1));
    $data[$cnt]['e_tana_sum_l'] = ($data[$cnt]['e_tana_sum_l'] * (-1));
    $data[$cnt]['e_tana_zai_a'] = ($data[$cnt]['e_tana_zai_a'] * (-1));
    $data[$cnt]['e_tana_kei_a'] = ($data[$cnt]['e_tana_kei_a'] * (-1));
    $data[$cnt]['e_tana_sum_a'] = ($data[$cnt]['e_tana_sum_a'] * (-1));
    
    $view_data[$cnt]['e_tana_zai_c'] = number_format($data[$cnt]['e_tana_zai_c'] / $tani, $keta);
    $view_data[$cnt]['e_tana_kei_c'] = number_format($data[$cnt]['e_tana_kei_c'] / $tani, $keta);
    $view_data[$cnt]['e_tana_sum_c'] = number_format($data[$cnt]['e_tana_sum_c'] / $tani, $keta);
    $view_data[$cnt]['e_tana_zai_l'] = number_format($data[$cnt]['e_tana_zai_l'] / $tani, $keta);
    $view_data[$cnt]['e_tana_kei_l'] = number_format($data[$cnt]['e_tana_kei_l'] / $tani, $keta);
    $view_data[$cnt]['e_tana_sum_l'] = number_format($data[$cnt]['e_tana_sum_l'] / $tani, $keta);
    $view_data[$cnt]['e_tana_zai_a'] = number_format($data[$cnt]['e_tana_zai_a'] / $tani, $keta);
    $view_data[$cnt]['e_tana_kei_a'] = number_format($data[$cnt]['e_tana_kei_a'] / $tani, $keta);
    $view_data[$cnt]['e_tana_sum_a'] = number_format($data[$cnt]['e_tana_sum_a'] / $tani, $keta);
    ///// 売上原価の計算
        // 期首棚卸高 ＋ 当月仕入(材料)発生(経費) ＋ （−期末棚卸高) を各項目毎に計算する
    $data[$cnt]['gen_zai_c'] = $data[$cnt]['s_tana_zai_c'] + $data[$cnt]['metarial_c'] + ($data[$cnt]['e_tana_zai_c']);
    $data[$cnt]['gen_kei_c'] = $data[$cnt]['s_tana_kei_c'] + $data[$cnt]['expense_c']  + ($data[$cnt]['e_tana_kei_c']);
    $data[$cnt]['gen_sum_c'] = $data[$cnt]['s_tana_sum_c'] + $data[$cnt]['shi_c']      + ($data[$cnt]['e_tana_sum_c']);
    $data[$cnt]['gen_zai_l'] = $data[$cnt]['s_tana_zai_l'] + $data[$cnt]['metarial_l'] + ($data[$cnt]['e_tana_zai_l']);
    $data[$cnt]['gen_kei_l'] = $data[$cnt]['s_tana_kei_l'] + $data[$cnt]['expense_l']  + ($data[$cnt]['e_tana_kei_l']);
    $data[$cnt]['gen_sum_l'] = $data[$cnt]['s_tana_sum_l'] + $data[$cnt]['shi_l']      + ($data[$cnt]['e_tana_sum_l']);
    $data[$cnt]['gen_zai_a'] = $data[$cnt]['s_tana_zai_a'] + $data[$cnt]['metarial_a'] + ($data[$cnt]['e_tana_zai_a']);
    $data[$cnt]['gen_kei_a'] = $data[$cnt]['s_tana_kei_a'] + $data[$cnt]['expense_a']  + ($data[$cnt]['e_tana_kei_a']);
    $data[$cnt]['gen_sum_a'] = $data[$cnt]['s_tana_sum_a'] + $data[$cnt]['shi_a']      + ($data[$cnt]['e_tana_sum_a']);
    
    $view_data[$cnt]['gen_zai_c'] = number_format($data[$cnt]['gen_zai_c'] / $tani, $keta);
    $view_data[$cnt]['gen_kei_c'] = number_format($data[$cnt]['gen_kei_c'] / $tani, $keta);
    $view_data[$cnt]['gen_sum_c'] = number_format($data[$cnt]['gen_sum_c'] / $tani, $keta);
    $view_data[$cnt]['gen_zai_l'] = number_format($data[$cnt]['gen_zai_l'] / $tani, $keta);
    $view_data[$cnt]['gen_kei_l'] = number_format($data[$cnt]['gen_kei_l'] / $tani, $keta);
    $view_data[$cnt]['gen_sum_l'] = number_format($data[$cnt]['gen_sum_l'] / $tani, $keta);
    $view_data[$cnt]['gen_zai_a'] = number_format($data[$cnt]['gen_zai_a'] / $tani, $keta);
    $view_data[$cnt]['gen_kei_a'] = number_format($data[$cnt]['gen_kei_a'] / $tani, $keta);
    $view_data[$cnt]['gen_sum_a'] = number_format($data[$cnt]['gen_sum_a'] / $tani, $keta);
    ///// 売上高比
        // 売上原価 ／ 売上高 ＝ 売上原価率(売上高比)  各項目毎に計算する
    $data[$cnt]['ritu_zai_c'] = Uround(($data[$cnt]['gen_zai_c'] / $data[$cnt]['uri_c']) * 100, 1);
    $data[$cnt]['ritu_kei_c'] = Uround(($data[$cnt]['gen_kei_c'] / $data[$cnt]['uri_c']) * 100, 1);
    $data[$cnt]['ritu_sum_c'] = Uround(($data[$cnt]['gen_sum_c'] / $data[$cnt]['uri_c']) * 100, 1);
    $data[$cnt]['ritu_zai_l'] = Uround(($data[$cnt]['gen_zai_l'] / $data[$cnt]['uri_l']) * 100, 1);
    $data[$cnt]['ritu_kei_l'] = Uround(($data[$cnt]['gen_kei_l'] / $data[$cnt]['uri_l']) * 100, 1);
    $data[$cnt]['ritu_sum_l'] = Uround(($data[$cnt]['gen_sum_l'] / $data[$cnt]['uri_l']) * 100, 1);
    $data[$cnt]['ritu_zai_a'] = Uround(($data[$cnt]['gen_zai_a'] / $data[$cnt]['uri_a']) * 100, 1);
    $data[$cnt]['ritu_kei_a'] = Uround(($data[$cnt]['gen_kei_a'] / $data[$cnt]['uri_a']) * 100, 1);
    $data[$cnt]['ritu_sum_a'] = Uround(($data[$cnt]['gen_sum_a'] / $data[$cnt]['uri_a']) * 100, 1);
    
    $view_data[$cnt]['ritu_zai_c'] = number_format($data[$cnt]['ritu_zai_c'], 1);
    $view_data[$cnt]['ritu_kei_c'] = number_format($data[$cnt]['ritu_kei_c'], 1);
    $view_data[$cnt]['ritu_sum_c'] = number_format($data[$cnt]['ritu_sum_c'], 1);
    $view_data[$cnt]['ritu_zai_l'] = number_format($data[$cnt]['ritu_zai_l'], 1);
    $view_data[$cnt]['ritu_kei_l'] = number_format($data[$cnt]['ritu_kei_l'], 1);
    $view_data[$cnt]['ritu_sum_l'] = number_format($data[$cnt]['ritu_sum_l'], 1);
    $view_data[$cnt]['ritu_zai_a'] = number_format($data[$cnt]['ritu_zai_a'], 1);
    $view_data[$cnt]['ritu_kei_a'] = number_format($data[$cnt]['ritu_kei_a'], 1);
    $view_data[$cnt]['ritu_sum_a'] = number_format($data[$cnt]['ritu_sum_a'], 1);
    
    ///// 前期・次期のボタンが押された時の例外処理 (表示期間の最大は６ヶ月間)
    if ($cnt >= 6) {
        break;
    }
}


/////////// HTML Header を出力してキャッシュを制御
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

<script language="JavaScript">
<!--
/* 入力文字が数字かどうかチェック */
function isDigit(str) {
    var len=str.length;
    var c;
    for (i=1; i<len; i++) {
        c = str.charAt(i);
        if((c < "0") || (c > "9")) {
            return true;
        }
    }
    return false;
}
/* 初期入力フォームのエレメントにフォーカスさせる */
function set_focus(){
    // document.body.focus();   // F2/F12キーを有効化する対応
    // document.mhForm.backwardStack.focus();  // 上記はIEのみのため、こちらに変更しNN対応
    // document.form_name.element_name.focus();      // 初期入力フォームがある場合はコメントを外す
    // document.form_name.element_name.select();
}
// -->
</script>
<link rel='stylesheet' href='account_settlement.css' type='text/css'> <!-- ファイル指定の場合 -->
<style type="text/css">
<!--
-->
</style>
</head>
<body onLoad='set_focus()'>
    <center>
<?= $menu->out_title_border() ?>
        <table width='100%' border='1' cellspacing='0' cellpadding='0'>
            <tr>
                <td colspan='1' width='130' bgcolor='#d6d3ce' align='center' class='corporate_name'>
                    栃木日東工器(株)
                </td>
                <form method='post' action='<?php echo $current_script ?>'>
                    <td bgcolor='green' width='70'align='center' class='pt10'>
                        <input class='pt10' type='submit' name='backward_ki' value='前半期'>
                    </td>
                    <td bgcolor='green' width='70'align='center' class='pt10'>
                        <input class='pt10' type='submit' name='forward_ki' value='次半期'>
                    </td>
                    <td colspan='7' bgcolor='#d6d3ce' align='right' class='pt10'>
                        単位
                        <select name='costrate_tani' class='pt10'>
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
                        <select name='costrate_keta' class='pt10'>
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
                        <input class='pt10b' type='submit' name='chg_measure' value='単位変更'>
                    </td>
                </form>
            </tr>
        </table>
        <table width='100%' bgcolor='#d6d3ce' align='center' cellspacing="0" cellpadding="3" border='1'>
            <tr><td> <!-- ダミー(デザイン用) -->
        <table width='100%' bgcolor='#d6d3ce' align='center' cellspacing="0" cellpadding="3" border='1'>
            <tbody>
                <tr>
                    <td rowspan='2' align='center' class='pt11b'>　</td>
                    <td colspan='3' align='center' class='pt11b' bgcolor='#ffffc6'>カ　プ　ラ</td>
                    <td colspan='3' align='center' class='pt11b' bgcolor='#ffffc6'>リ　ニ　ア</td>
                    <td colspan='3' align='center' class='pt11b' bgcolor='#ffffc6'>合　　　計</td>
                </tr>
                <tr>
                    <td nowrap align='center' class='pt10b' bgcolor='#ffffc6'>材　料　費<br>(外作費)</td>
                    <td nowrap align='center' class='pt10b' bgcolor='#ffffc6'>労務費経費<br>(内作費)</td>
                    <td nowrap align='center' class='pt10b' bgcolor='#ffffc6'>計</td>
                    <td nowrap align='center' class='pt10b' bgcolor='#ffffc6'>材　料　費<br>(外作費)</td>
                    <td nowrap align='center' class='pt10b' bgcolor='#ffffc6'>労務費経費<br>(内作費)</td>
                    <td nowrap align='center' class='pt10b' bgcolor='#ffffc6'>計</td>
                    <td nowrap align='center' class='pt10b' bgcolor='#ffffc6'>材　料　費<br>(外作費)</td>
                    <td nowrap align='center' class='pt10b' bgcolor='#ffffc6'>労務費経費<br>(内作費)</td>
                    <td nowrap align='center' class='pt10b' bgcolor='#ffffc6'>計</td>
                </tr>
                <tr>
                    <td nowrap align='center' class='pt11' bgcolor='white'>前期売上高比</td>
                    <td nowrap align='right' class='pt11b' bgcolor='white'><?php echo $view_pre_ritu_zai_c ?>%</td>
                    <td nowrap align='right' class='pt11b' bgcolor='white'><?php echo $view_pre_ritu_kei_c ?>%</td>
                    <td nowrap align='right' class='pt11b' bgcolor='white'><?php echo $view_pre_ritu_sum_c ?>%</td>
                    <td nowrap align='right' class='pt11b' bgcolor='white'><?php echo $view_pre_ritu_zai_l ?>%</td>
                    <td nowrap align='right' class='pt11b' bgcolor='white'><?php echo $view_pre_ritu_kei_l ?>%</td>
                    <td nowrap align='right' class='pt11b' bgcolor='white'><?php echo $view_pre_ritu_sum_c ?>%</td>
                    <td nowrap align='right' class='pt11b' bgcolor='white'><?php echo $view_pre_ritu_zai_a ?>%</td>
                    <td nowrap align='right' class='pt11b' bgcolor='white'><?php echo $view_pre_ritu_kei_a ?>%</td>
                    <td nowrap align='right' class='pt11b' bgcolor='white'><?php echo $view_pre_ritu_sum_a ?>%</td>
                </tr>
                <tr>
                    <td nowrap align='center' class='pt11' bgcolor='#ceffce'>期首棚卸高<br>(割合)</td>
                    <td nowrap align='right' class='pt11b' bgcolor='#e6e6e6'><?php echo $view_i_zai_c . "<br>(" . $percent_zai_c ?>%)</td>
                    <td nowrap align='right' class='pt11b' bgcolor='#e6e6e6'><?php echo $view_i_kei_c . "<br>(" . $percent_kei_c ?>%)</td>
                    <td nowrap align='right' class='pt11b' bgcolor='#e6e6e6'><?php echo $view_i_sum_c . "<br>(" . $percent_sum_c ?>%)</td>
                    <td nowrap align='right' class='pt11b' bgcolor='#e6e6e6'><?php echo $view_i_zai_l . "<br>(" . $percent_zai_l ?>%)</td>
                    <td nowrap align='right' class='pt11b' bgcolor='#e6e6e6'><?php echo $view_i_kei_l . "<br>(" . $percent_kei_l ?>%)</td>
                    <td nowrap align='right' class='pt11b' bgcolor='#e6e6e6'><?php echo $view_i_sum_l . "<br>(" . $percent_sum_l ?>%)</td>
                    <td nowrap align='right' class='pt11b' bgcolor='#e6e6e6'><?php echo $view_i_zai_all . "<br>(" . $percent_zai_all ?>%)</td>
                    <td nowrap align='right' class='pt11b' bgcolor='#e6e6e6'><?php echo $view_i_kei_all . "<br>(" . $percent_kei_all ?>%)</td>
                    <td nowrap align='right' class='pt11b' bgcolor='#e6e6e6'><?php echo $view_i_sum_all . "<br>(" . $percent_sum_all ?>%)</td>
                </tr>
                <?php for ($j = 0; $j < $cnt; $j++) { ?>
                <tr>
                    <td nowrap align='right' class='pt11b' bgcolor='white'><?php echo $view_data[$j]['ym'] ?></td>
                    <td nowrap align='center' class='pt11' bgcolor='white'>売　上　高</td>
                    <td colspan='2' nowrap align='center' class='pt11' bgcolor='white'><?php echo $view_data[$j]['uri_c'] ?></td>
                    <td colspan='3' nowrap align='center' class='pt11' bgcolor='white'><?php echo $view_data[$j]['uri_l'] ?></td>
                    <td colspan='3' nowrap align='center' class='pt11' bgcolor='white'><?php echo $view_data[$j]['uri_a'] ?></td>
                </tr>
                <tr>
                    <td nowrap align='center' class='pt11' bgcolor='#ceffce'>期首棚卸高</td>
                    <td nowrap align='right' class='pt11' bgcolor='#e6e6e6'><?php echo $view_data[$j]['s_tana_zai_c'] ?></td>
                    <td nowrap align='right' class='pt11' bgcolor='#e6e6e6'><?php echo $view_data[$j]['s_tana_kei_c'] ?></td>
                    <td nowrap align='right' class='pt11' bgcolor='#e6e6e6'><?php echo $view_data[$j]['s_tana_sum_c'] ?></td>
                    <td nowrap align='right' class='pt11' bgcolor='#e6e6e6'><?php echo $view_data[$j]['s_tana_zai_l'] ?></td>
                    <td nowrap align='right' class='pt11' bgcolor='#e6e6e6'><?php echo $view_data[$j]['s_tana_kei_l'] ?></td>
                    <td nowrap align='right' class='pt11' bgcolor='#e6e6e6'><?php echo $view_data[$j]['s_tana_sum_l'] ?></td>
                    <td nowrap align='right' class='pt11' bgcolor='#e6e6e6'><?php echo $view_data[$j]['s_tana_zai_a'] ?></td>
                    <td nowrap align='right' class='pt11' bgcolor='#e6e6e6'><?php echo $view_data[$j]['s_tana_kei_a'] ?></td>
                    <td nowrap align='right' class='pt11' bgcolor='#e6e6e6'><?php echo $view_data[$j]['s_tana_sum_a'] ?></td>
                </tr>
                <tr>
                    <td nowrap align='center' class='pt11' bgcolor='white'>当月発生高</td>
                    <td nowrap align='right' class='pt11' bgcolor='white'><?php echo $view_data[$j]['metarial_c'] ?></td>
                    <td nowrap align='right' class='pt11' bgcolor='white'><?php echo $view_data[$j]['expense_c'] ?> </td>
                    <td nowrap align='right' class='pt11' bgcolor='white'><?php echo $view_data[$j]['shi_c'] ?>     </td>
                    <td nowrap align='right' class='pt11' bgcolor='white'><?php echo $view_data[$j]['metarial_l'] ?></td>
                    <td nowrap align='right' class='pt11' bgcolor='white'><?php echo $view_data[$j]['expense_l'] ?> </td>
                    <td nowrap align='right' class='pt11' bgcolor='white'><?php echo $view_data[$j]['shi_l'] ?>     </td>
                    <td nowrap align='right' class='pt11' bgcolor='white'><?php echo $view_data[$j]['metarial_a'] ?></td>
                    <td nowrap align='right' class='pt11' bgcolor='white'><?php echo $view_data[$j]['expense_a'] ?> </td>
                    <td nowrap align='right' class='pt11' bgcolor='white'><?php echo $view_data[$j]['shi_a'] ?>     </td>
                </tr>
                <tr>
                    <td nowrap align='center' class='pt11' bgcolor='#ceffce'>期末棚卸高</td>
                    <td nowrap align='right' class='pt11' bgcolor='#e6e6e6'><?php echo $view_data[$j]['e_tana_zai_c'] ?></td>
                    <td nowrap align='right' class='pt11' bgcolor='#e6e6e6'><?php echo $view_data[$j]['e_tana_kei_c'] ?></td>
                    <td nowrap align='right' class='pt11' bgcolor='#e6e6e6'><?php echo $view_data[$j]['e_tana_sum_c'] ?></td>
                    <td nowrap align='right' class='pt11' bgcolor='#e6e6e6'><?php echo $view_data[$j]['e_tana_zai_l'] ?></td>
                    <td nowrap align='right' class='pt11' bgcolor='#e6e6e6'><?php echo $view_data[$j]['e_tana_kei_l'] ?></td>
                    <td nowrap align='right' class='pt11' bgcolor='#e6e6e6'><?php echo $view_data[$j]['e_tana_sum_l'] ?></td>
                    <td nowrap align='right' class='pt11' bgcolor='#e6e6e6'><?php echo $view_data[$j]['e_tana_zai_a'] ?></td>
                    <td nowrap align='right' class='pt11' bgcolor='#e6e6e6'><?php echo $view_data[$j]['e_tana_kei_a'] ?></td>
                    <td nowrap align='right' class='pt11' bgcolor='#e6e6e6'><?php echo $view_data[$j]['e_tana_sum_a'] ?></td>
                </tr>
                <tr>
                    <td nowrap align='center' class='pt11' bgcolor='white'>売上原価</td>
                    <td nowrap align='right' class='pt11' bgcolor='white'><?php echo $view_data[$j]['gen_zai_c'] ?></td>
                    <td nowrap align='right' class='pt11' bgcolor='white'><?php echo $view_data[$j]['gen_kei_c'] ?></td>
                    <td nowrap align='right' class='pt11' bgcolor='white'><?php echo $view_data[$j]['gen_sum_c'] ?></td>
                    <td nowrap align='right' class='pt11' bgcolor='white'><?php echo $view_data[$j]['gen_zai_l'] ?></td>
                    <td nowrap align='right' class='pt11' bgcolor='white'><?php echo $view_data[$j]['gen_kei_l'] ?></td>
                    <td nowrap align='right' class='pt11' bgcolor='white'><?php echo $view_data[$j]['gen_sum_l'] ?></td>
                    <td nowrap align='right' class='pt11' bgcolor='white'><?php echo $view_data[$j]['gen_zai_a'] ?></td>
                    <td nowrap align='right' class='pt11' bgcolor='white'><?php echo $view_data[$j]['gen_kei_a'] ?></td>
                    <td nowrap align='right' class='pt11' bgcolor='white'><?php echo $view_data[$j]['gen_sum_a'] ?></td>
                </tr>
                <tr>
                    <td nowrap align='center' class='pt11' bgcolor='#ceffce'>売上高比</td>
                    <td nowrap align='right' class='pt11b' bgcolor='#e6e6e6'><?php echo $view_data[$j]['ritu_zai_c'] ?>%</td>
                    <td nowrap align='right' class='pt11b' bgcolor='#e6e6e6'><?php echo $view_data[$j]['ritu_kei_c'] ?>%</td>
                    <td nowrap align='right' class='pt11b' bgcolor='#e6e6e6'><?php echo $view_data[$j]['ritu_sum_c'] ?>%</td>
                    <td nowrap align='right' class='pt11b' bgcolor='#e6e6e6'><?php echo $view_data[$j]['ritu_zai_l'] ?>%</td>
                    <td nowrap align='right' class='pt11b' bgcolor='#e6e6e6'><?php echo $view_data[$j]['ritu_kei_l'] ?>%</td>
                    <td nowrap align='right' class='pt11b' bgcolor='#e6e6e6'><?php echo $view_data[$j]['ritu_sum_l'] ?>%</td>
                    <td nowrap align='right' class='pt11b' bgcolor='#e6e6e6'><?php echo $view_data[$j]['ritu_zai_a'] ?>%</td>
                    <td nowrap align='right' class='pt11b' bgcolor='#e6e6e6'><?php echo $view_data[$j]['ritu_kei_a'] ?>%</td>
                    <td nowrap align='right' class='pt11b' bgcolor='#e6e6e6'><?php echo $view_data[$j]['ritu_sum_a'] ?>%</td>
                </tr>
                <?php } ?>
            </tbody>
        </table>
            </td></tr>
        </table> <!-- ダミーEnd -->
    </center>
</body>
</html>
