<?php
//////////////////////////////////////////////////////////////////////////////
// 月次損益関係 勘定科目内訳明細書                                          //
// Copyright(C) 2020-2020 Norihisa.Ohya norihisa_ooya@nitto-kohki.co.jp     //
// Changed history                                                          //
// 2020/06/12 Created   account_statement_view.php                          //
//////////////////////////////////////////////////////////////////////////////
ini_set('error_reporting', E_STRICT);       // E_STRICT=2048(php5) E_ALL=2047 debug 用
// ini_set('error_reporting',E_ALL);           // E_ALL='2047' debug 用
// ini_set('display_errors','1');              // Error 表示 ON debug 用 リリース後コメント
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
// $menu->set_action('抽象化名',   PL . 'address.php');

$url_referer     = $_SESSION['pl_referer'];     // 呼出もとの URL を取得

$menu->set_action('部品仕掛Ｃ', PL . 'cost_parts_widget_view.php');
$menu->set_action('原材料', PL . 'cost_material_view.php');
$menu->set_action('部品', PL . 'cost_parts_view.php');
$menu->set_action('切粉', PL . 'cost_kiriko_view.php');

///// 対象当月
$ki2_ym   = $_SESSION['2ki_ym'];
$yyyymm   = $_SESSION['2ki_ym'];
$ki       = Ym_to_tnk($_SESSION['2ki_ym']);
$b_yyyymm = $yyyymm - 100;
$p1_ki    = Ym_to_tnk($b_yyyymm);

///// 前期末 年月の算出
$yyyy = substr($yyyymm, 0,4);
$mm   = substr($yyyymm, 4,2);
if (($mm >= 1) && ($mm <= 3)) {
    $yyyy = ($yyyy - 1);
}
$pre_end_ym = $yyyy . "03";     // 前期末年月

///// 期・半期の取得
$tuki_chk = substr($_SESSION['2ki_ym'],4,2);
if ($tuki_chk >= 1 && $tuki_chk <= 3) {           //第４四半期
    $hanki = '４';
} elseif ($tuki_chk >= 4 && $tuki_chk <= 6) {     //第１四半期
    $hanki = '１';
} elseif ($tuki_chk >= 7 && $tuki_chk <= 9) {     //第２四半期
    $hanki = '２';
} elseif ($tuki_chk >= 10) {    //第３四半期
    $hanki = '３';
}

///// 年月範囲の取得
if ($tuki_chk >= 1 && $tuki_chk <= 3) {           //第４四半期
    $str_ym = $yyyy . '04';
    $end_ym = $yyyymm;
} elseif ($tuki_chk >= 4 && $tuki_chk <= 6) {     //第１四半期
    $str_ym = $yyyy . '04';
    $end_ym = $yyyymm;
} elseif ($tuki_chk >= 7 && $tuki_chk <= 9) {     //第２四半期
    $str_ym = $yyyy . '04';
    $end_ym = $yyyymm;
} elseif ($tuki_chk >= 10) {    //第３四半期
    $str_ym = $yyyy . '04';
    $end_ym = $yyyymm;
}
///// TNK期 → NK期へ変換
$nk_ki   = $ki + 44;
$nk_p1ki = $p1_ki + 44;

//////////// タイトル名(ソースのタイトル名とフォームのタイトル名)
if ($tuki_chk == 3) {
    $menu->set_title("第 {$ki} 期　本決算　勘　定　科　目　内　訳　明　細　書");
} else {
    $menu->set_title("第 {$ki} 期　第{$hanki}四半期　勘　定　科　目　内　訳　明　細　書");
}

///// 現金及び預金
// 現金1100 00
/*
$res   = array();
$field = array();
$rows  = array();
$genkin_kin = 0;
$sum1 = '1101';
$sum2 = '00';
$query = sprintf("select rep_cri, rep_de, rep_cr from financial_report_cal where rep_ymd=%d and rep_summary1='%s' and rep_summary2='%s'", $yyyymm, $sum1, $sum2);
if ($rows=getResultWithField2($query, $field, $res) <= 0) {
    $genkin_kin = 0;
} else {
    $genkin_kin = $res[0][0] + $res[0][1] - $res[0][2];
}
*/
$res_k   = array();
$field_k = array();
$rows_k  = array();
$genkin_kin = 0;
$sum1 = '1101';
$sum2 = '00';
$query_k = sprintf("select rep_cri, rep_de, rep_cr from financial_report_cal where rep_ymd=%d and rep_summary1='%s' and rep_summary2='%s'", $nk_ki, $sum1, $sum2);
if ($rows_k=getResultWithField2($query_k, $field_k, $res_k) <= 0) {
    $genkin_kishu = 0;
} else {
    $genkin_kishu = $res_k[0][0];
}
$res   = array();
$field = array();
$rows  = array();
$query = sprintf("select SUM(rep_de), SUM(rep_cr) from financial_report_month where rep_ymd>=%d and rep_ymd<=%d and rep_summary1='%s'", $str_ym, $end_ym, $sum1);
if ($rows=getResultWithField2($query, $field, $res) <= 0) {
    $genkin_kin = $genkin_kishu;
} else {
    $genkin_kin = $genkin_kishu + ($res[0][0] - $res[0][1]);
}

// 当座1103 00
/*
$res   = array();
$field = array();
$rows  = array();
$touza_kin = 0;
$sum1 = '1103';
$sum2 = '00';
$query = sprintf("select rep_cri, rep_de, rep_cr from financial_report_cal where rep_ymd=%d and rep_summary1='%s' and rep_summary2='%s'", $yyyymm, $sum1, $sum2);
if ($rows=getResultWithField2($query, $field, $res) <= 0) {
    $touza_kin = 0;
} else {
    $touza_kin = $res[0][0] + $res[0][1] - $res[0][2];
}
*/
$res_k   = array();
$field_k = array();
$rows_k  = array();
$touza_kin = 0;
$sum1 = '1103';
$sum2 = '00';
$query_k = sprintf("select rep_cri, rep_de, rep_cr from financial_report_cal where rep_ymd=%d and rep_summary1='%s' and rep_summary2='%s'", $nk_ki, $sum1, $sum2);
if ($rows_k=getResultWithField2($query_k, $field_k, $res_k) <= 0) {
    $touza_kishu = 0;
} else {
    $touza_kishu = $res_k[0][0];
}
$res   = array();
$field = array();
$rows  = array();
$query = sprintf("select SUM(rep_de), SUM(rep_cr) from financial_report_month where rep_ymd>=%d and rep_ymd<=%d and rep_summary1='%s'", $str_ym, $end_ym, $sum1);
if ($rows=getResultWithField2($query, $field, $res) <= 0) {
    $touza_kin = $touza_kishu;
} else {
    $touza_kin = $touza_kishu + ($res[0][0] - $res[0][1]);
}

// 普通預金1104 00 の合計（銀行コード違い）
/*
$res   = array();
$field = array();
$rows  = array();
$futsu_kin = 0;
$sum1 = '1104';
$sum2 = '00';
$query = sprintf("select SUM(rep_cri), SUM(rep_de), SUM(rep_cr) from financial_report_cal where rep_ymd=%d and rep_summary1='%s' and rep_summary2='%s'", $yyyymm, $sum1, $sum2);
if ($rows=getResultWithField2($query, $field, $res) <= 0) {
    $futsu_kin = 0;
} else {
    $futsu_kin = $res[0][0] + $res[0][1] - $res[0][2];
}
*/
$res_k   = array();
$field_k = array();
$rows_k  = array();
$futsu_kin = 0;
$sum1 = '1104';
$sum2 = '00';
$query_k = sprintf("select SUM(rep_cri) from financial_report_cal where rep_ymd=%d and rep_summary1='%s' and rep_summary2='%s'", $nk_ki, $sum1, $sum2);
if ($rows_k=getResultWithField2($query_k, $field_k, $res_k) <= 0) {
    $futsu_kishu = 0;
} else {
    $futsu_kishu = $res_k[0][0];
}

$res   = array();
$field = array();
$rows  = array();
$query = sprintf("select SUM(rep_de), SUM(rep_cr) from financial_report_month where rep_ymd>=%d and rep_ymd<=%d and rep_summary1='%s'", $str_ym, $end_ym, $sum1);
if ($rows=getResultWithField2($query, $field, $res) <= 0) {
    $futsu_kin = $futsu_kishu;
} else {
    $futsu_kin = $futsu_kishu + ($res[0][0] - $res[0][1]);
}

// 定期預金1106 00
/*
$res   = array();
$field = array();
$rows  = array();
$teiki_kin = 0;
$sum1 = '1106';
$sum2 = '00';
$query = sprintf("select SUM(rep_cri), SUM(rep_de), SUM(rep_cr) from financial_report_cal where rep_ymd=%d and rep_summary1='%s' and rep_summary2='%s'", $yyyymm, $sum1, $sum2);
if ($rows=getResultWithField2($query, $field, $res) <= 0) {
    $teiki_kin = 0;
} else {
    $teiki_kin = $res[0][0] + $res[0][1] - $res[0][2];
}
*/
$res_k   = array();
$field_k = array();
$rows_k  = array();
$teiki_kin = 0;
$sum1 = '1106';
$sum2 = '00';
$query_k = sprintf("select SUM(rep_cri), SUM(rep_de), SUM(rep_cr) from financial_report_cal where rep_ymd=%d and rep_summary1='%s' and rep_summary2='%s'", $nk_ki, $sum1, $sum2);
if ($rows_k=getResultWithField2($query_k, $field_k, $res_k) <= 0) {
    $teiki_kishu = 0;
} else {
    $teiki_kishu = $res_k[0][0];
}
$res   = array();
$field = array();
$rows  = array();
$query = sprintf("select SUM(rep_de), SUM(rep_cr) from financial_report_month where rep_ymd>=%d and rep_ymd<=%d and rep_summary1='%s'", $str_ym, $end_ym, $sum1);
if ($rows=getResultWithField2($query, $field, $res) <= 0) {
    $teiki_kin = $teiki_kishu;
} else {
    $teiki_kin = $teiki_kishu + ($res[0][0] - $res[0][1]);
}

// 預金小計の計算
$yokin_total_kin = $touza_kin + $futsu_kin + $teiki_kin;
// 現金及び預金合計の計算
$genyo_total_kin = $genkin_kin + $touza_kin + $futsu_kin + $teiki_kin;

// 現金および預金の内訳 明細
// 普通預金 10：足利、11：三菱UFJ、12：三菱UFJ信託
$res_k   = array();
$field_k = array();
$rows_k  = array();
$sum1 = '1104';
$sum2 = '00';
$sum3 = '10';
$query_k = sprintf("select rep_cri, rep_de, rep_cr from financial_report_cal where rep_ymd=%d and rep_summary1='%s' and rep_summary2='%s' and rep_gin='%s'", $nk_ki, $sum1, $sum2, $sum3);
if ($rows_k=getResultWithField2($query_k, $field_k, $res_k) <= 0) {
    $ashi_futu_kishu = 0;
} else {
    $ashi_futu_kishu = $res_k[0][0];
}
$res   = array();
$field = array();
$rows  = array();
$query = sprintf("select SUM(rep_de), SUM(rep_cr) from financial_report_month where rep_ymd>=%d and rep_ymd<=%d and rep_summary1='%s' and rep_gin='%s'", $str_ym, $end_ym, $sum1, $sum3);
if ($rows=getResultWithField2($query, $field, $res) <= 0) {
    $ashi_futu_kin = $ashi_futu_kishu;
} else {
    $ashi_futu_kin = $ashi_futu_kishu + ($res[0][0] - $res[0][1]);
}
$res_k   = array();
$field_k = array();
$rows_k  = array();
$sum1 = '1104';
$sum2 = '00';
$sum3 = '11';
$query_k = sprintf("select rep_cri, rep_de, rep_cr from financial_report_cal where rep_ymd=%d and rep_summary1='%s' and rep_summary2='%s' and rep_gin='%s'", $nk_ki, $sum1, $sum2, $sum3);
if ($rows_k=getResultWithField2($query_k, $field_k, $res_k) <= 0) {
    $ufj_futu_kishu = 0;
} else {
    $ufj_futu_kishu = $res_k[0][0];
}
$res   = array();
$field = array();
$rows  = array();
$query = sprintf("select SUM(rep_de), SUM(rep_cr) from financial_report_month where rep_ymd>=%d and rep_ymd<=%d and rep_summary1='%s' and rep_gin='%s'", $str_ym, $end_ym, $sum1, $sum3);
if ($rows=getResultWithField2($query, $field, $res) <= 0) {
    $ufj_futu_kin = $ufj_futu_kishu;
} else {
    $ufj_futu_kin = $ufj_futu_kishu + ($res[0][0] - $res[0][1]);
}
$res_k   = array();
$field_k = array();
$rows_k  = array();
$sum1 = '1104';
$sum2 = '00';
$sum3 = '12';
$query_k = sprintf("select rep_cri, rep_de, rep_cr from financial_report_cal where rep_ymd=%d and rep_summary1='%s' and rep_summary2='%s' and rep_gin='%s'", $nk_ki, $sum1, $sum2, $sum3);
if ($rows_k=getResultWithField2($query_k, $field_k, $res_k) <= 0) {
    $ufjs_futu_kishu = 0;
} else {
    $ufjs_futu_kishu = $res_k[0][0];
}
$res   = array();
$field = array();
$rows  = array();
$query = sprintf("select SUM(rep_de), SUM(rep_cr) from financial_report_month where rep_ymd>=%d and rep_ymd<=%d and rep_summary1='%s' and rep_gin='%s'", $str_ym, $end_ym, $sum1, $sum3);
if ($rows=getResultWithField2($query, $field, $res) <= 0) {
    $ufjs_futu_kin = $ufjs_futu_kishu;
} else {
    $ufjs_futu_kin = $ufjs_futu_kishu + ($res[0][0] - $res[0][1]);
}

// 定期預金 10：足利、11：三菱UFJ、12：三菱UFJ信託
$res_k   = array();
$field_k = array();
$rows_k  = array();
$sum1 = '1106';
$sum2 = '00';
$sum3 = '10';
$query_k = sprintf("select rep_cri, rep_de, rep_cr from financial_report_cal where rep_ymd=%d and rep_summary1='%s' and rep_summary2='%s' and rep_gin='%s'", $nk_ki, $sum1, $sum2, $sum3);
if ($rows_k=getResultWithField2($query_k, $field_k, $res_k) <= 0) {
    $ashi_teiki_kishu = 0;
} else {
    $ashi_teiki_kishu = $res_k[0][0];
}
$res   = array();
$field = array();
$rows  = array();
$query = sprintf("select SUM(rep_de), SUM(rep_cr) from financial_report_month where rep_ymd>=%d and rep_ymd<=%d and rep_summary1='%s' and rep_gin='%s'", $str_ym, $end_ym, $sum1, $sum3);
if ($rows=getResultWithField2($query, $field, $res) <= 0) {
    $ashi_teiki_kin = $ashi_teiki_kishu;
} else {
    $ashi_teiki_kin = $ashi_teiki_kishu + ($res[0][0] - $res[0][1]);
}
$res_k   = array();
$field_k = array();
$rows_k  = array();
$sum1 = '1106';
$sum2 = '00';
$sum3 = '11';
$query_k = sprintf("select rep_cri, rep_de, rep_cr from financial_report_cal where rep_ymd=%d and rep_summary1='%s' and rep_summary2='%s' and rep_gin='%s'", $nk_ki, $sum1, $sum2, $sum3);
if ($rows_k=getResultWithField2($query_k, $field_k, $res_k) <= 0) {
    $ufj_teiki_kishu = 0;
} else {
    $ufj_teiki_kishu = $res_k[0][0];
}
$res   = array();
$field = array();
$rows  = array();
$query = sprintf("select SUM(rep_de), SUM(rep_cr) from financial_report_month where rep_ymd>=%d and rep_ymd<=%d and rep_summary1='%s' and rep_gin='%s'", $str_ym, $end_ym, $sum1, $sum3);
if ($rows=getResultWithField2($query, $field, $res) <= 0) {
    $ufj_teiki_kin = $ufj_teiki_kishu;
} else {
    $ufj_teiki_kin = $ufj_teiki_kishu + ($res[0][0] - $res[0][1]);
}
$res_k   = array();
$field_k = array();
$rows_k  = array();
$sum1 = '1106';
$sum2 = '00';
$sum3 = '12';
$query_k = sprintf("select rep_cri, rep_de, rep_cr from financial_report_cal where rep_ymd=%d and rep_summary1='%s' and rep_summary2='%s' and rep_gin='%s'", $nk_ki, $sum1, $sum2, $sum3);
if ($rows_k=getResultWithField2($query_k, $field_k, $res_k) <= 0) {
    $ufjs_teiki_kishu = 0;
} else {
    $ufjs_teiki_kishu = $res_k[0][0];
}
$res   = array();
$field = array();
$rows  = array();
$query = sprintf("select SUM(rep_de), SUM(rep_cr) from financial_report_month where rep_ymd>=%d and rep_ymd<=%d and rep_summary1='%s' and rep_gin='%s'", $str_ym, $end_ym, $sum1, $sum3);
if ($rows=getResultWithField2($query, $field, $res) <= 0) {
    $ufjs_teiki_kin = $ufjs_teiki_kishu;
} else {
    $ufjs_teiki_kin = $ufjs_teiki_kishu + ($res[0][0] - $res[0][1]);
}

// 銀行毎預金計
$ashi_total_kin = $ashi_futu_kin + $ashi_teiki_kin;
$ufj_total_kin  = $ufj_futu_kin + $ufj_teiki_kin;
$ufjs_total_kin = $ufjs_futu_kin + $ufjs_teiki_kin;

// 売掛金
$res   = array();
$field = array();
$rows  = array();
$urikake_kin = 0;
$note = '売掛金';
$query = sprintf("SELECT kin FROM profit_loss_bs_history WHERE pl_bs_ym=%d and note='%s'", $yyyymm, $note);
if ($rows=getResultWithField2($query, $field, $res) <= 0) {
    $urikake_kin = 0;
} else {
    $urikake_kin = $res[0][0];
}

// 売掛金 内訳の取得
// NK 
$res_k   = array();
$field_k = array();
$rows_k  = array();
$sum1 = '00001';
$sum2 = '00';
$query_k = sprintf("select SUM(rep_cri), SUM(rep_de), SUM(rep_cr) from financial_report_cal where rep_ymd=%d and rep_summary1='%s'", $end_ym, $sum1);
if ($rows_k=getResultWithField2($query_k, $field_k, $res_k) <= 0) {
    $nk_uri_kishu = 0;
} else {
    $nk_uri_kishu = $res_k[0][0];
}
$res   = array();
$field = array();
$rows  = array();
$query = sprintf("select SUM(rep_de), SUM(rep_cr) from financial_report_cal where rep_ymd=%d and rep_summary1='%s'", $end_ym, $sum1);
if ($rows=getResultWithField2($query, $field, $res) <= 0) {
    $nk_uri_kin = $nk_uri_kishu;
} else {
    $nk_uri_kin = $nk_uri_kishu + ($res[0][0] - $res[0][1]);
    if ($end_ym==202006) {
        $nk_uri_kin = 470191600;
    }
}

// 売掛金 内訳の取得
// MT 
$res_k   = array();
$field_k = array();
$rows_k  = array();
$sum1 = '00004';
$sum2 = '00';
$query_k = sprintf("select SUM(rep_cri), SUM(rep_de), SUM(rep_cr) from financial_report_cal where rep_ymd=%d and rep_summary1='%s'", $end_ym, $sum1);
if ($rows_k=getResultWithField2($query_k, $field_k, $res_k) <= 0) {
    $mt_uri_kishu = 0;
} else {
    $mt_uri_kishu = $res_k[0][0];
}
$res   = array();
$field = array();
$rows  = array();
$query = sprintf("select SUM(rep_de), SUM(rep_cr) from financial_report_cal where rep_ymd=%d and rep_summary1='%s'", $end_ym, $sum1);
if ($rows=getResultWithField2($query, $field, $res) <= 0) {
    $mt_uri_kin = $mt_uri_kishu;
} else {
    $mt_uri_kin = $mt_uri_kishu + ($res[0][0] - $res[0][1]);
}

// 売掛金 内訳の取得
// SNK
$res_k   = array();
$field_k = array();
$rows_k  = array();
$sum1 = '00005';
$sum2 = '00';
$query_k = sprintf("select SUM(rep_cri), SUM(rep_de), SUM(rep_cr) from financial_report_cal where rep_ymd=%d and rep_summary1='%s'", $end_ym, $sum1);
if ($rows_k=getResultWithField2($query_k, $field_k, $res_k) <= 0) {
    $snk_uri_kishu = 0;
} else {
    $snk_uri_kishu = $res_k[0][0];
}
$res   = array();
$field = array();
$rows  = array();
$query = sprintf("select SUM(rep_de), SUM(rep_cr) from financial_report_cal where rep_ymd=%d and rep_summary1='%s'", $end_ym, $sum1);
if ($rows=getResultWithField2($query, $field, $res) <= 0) {
    $snk_uri_kin = $snk_uri_kishu;
} else {
    $snk_uri_kin = $snk_uri_kishu + ($res[0][0] - $res[0][1]);
}

///// 在庫
// 製品1404 00
/*
$res   = array();
$field = array();
$rows  = array();
$seihin_kin = 0;
$sum1 = '1404';
$sum2 = '00';
$query = sprintf("select rep_cri, rep_de, rep_cr from financial_report_cal where rep_ymd=%d and rep_summary1='%s' and rep_summary2='%s'", $yyyymm, $sum1, $sum2);
if ($rows=getResultWithField2($query, $field, $res) <= 0) {
    $seihin_kin = 0;
} else {
    $seihin_kin = $res[0][0] + $res[0][1] - $res[0][2];
}
*/
$res_k   = array();
$field_k = array();
$rows_k  = array();
$seihin_kin = 0;
$sum1 = '1404';
$sum2 = '00';
$query_k = sprintf("select rep_cri, rep_de, rep_cr from financial_report_cal where rep_ymd=%d and rep_summary1='%s' and rep_summary2='%s'", $nk_ki, $sum1, $sum2);
if ($rows_k=getResultWithField2($query_k, $field_k, $res_k) <= 0) {
    $seihin_kishu = 0;
} else {
    $seihin_kishu = $res_k[0][0];
}
$res   = array();
$field = array();
$rows  = array();
$query = sprintf("select SUM(rep_de), SUM(rep_cr) from financial_report_month where rep_ymd>=%d and rep_ymd<=%d and rep_summary1='%s'", $str_ym, $end_ym, $sum1);
if ($rows=getResultWithField2($query, $field, $res) <= 0) {
    $seihin_kin = $seihin_kishu;
} else {
    $seihin_kin = $seihin_kishu + ($res[0][0] - $res[0][1]);
}

// 製品仕掛品1405 00
/*
$res   = array();
$field = array();
$rows  = array();
$seihinsi_kin = 0;
$sum1 = '1405';
$sum2 = '00';
$query = sprintf("select rep_cri, rep_de, rep_cr from financial_report_cal where rep_ymd=%d and rep_summary1='%s' and rep_summary2='%s'", $yyyymm, $sum1, $sum2);
if ($rows=getResultWithField2($query, $field, $res) <= 0) {
    $seihinsi_kin = 0;
} else {
    $seihinsi_kin = $res[0][0] + $res[0][1] - $res[0][2];
}
*/
$res_k   = array();
$field_k = array();
$rows_k  = array();
$seihinsi_kin = 0;
$sum1 = '1405';
$sum2 = '00';
$query_k = sprintf("select rep_cri, rep_de, rep_cr from financial_report_cal where rep_ymd=%d and rep_summary1='%s' and rep_summary2='%s'", $nk_ki, $sum1, $sum2);
if ($rows_k=getResultWithField2($query_k, $field_k, $res_k) <= 0) {
    $seihinsi_kishu = 0;
} else {
    $seihinsi_kishu = $res_k[0][0];
}
$res   = array();
$field = array();
$rows  = array();
$query = sprintf("select SUM(rep_de), SUM(rep_cr) from financial_report_month where rep_ymd>=%d and rep_ymd<=%d and rep_summary1='%s'", $str_ym, $end_ym, $sum1);
if ($rows=getResultWithField2($query, $field, $res) <= 0) {
    $seihinsi_kin = $seihinsi_kishu;
} else {
    $seihinsi_kin = $seihinsi_kishu + ($res[0][0] - $res[0][1]);
}

// 部品1406 00
/*
$res   = array();
$field = array();
$rows  = array();
$buhin_kin = 0;
$sum1 = '1406';
$sum2 = '00';
$query = sprintf("select rep_cri, rep_de, rep_cr from financial_report_cal where rep_ymd=%d and rep_summary1='%s' and rep_summary2='%s'", $yyyymm, $sum1, $sum2);
if ($rows=getResultWithField2($query, $field, $res) <= 0) {
    $buhin_kin = 0;
} else {
    $buhin_kin = $res[0][0] + $res[0][1] - $res[0][2];
}
*/
$res_k   = array();
$field_k = array();
$rows_k  = array();
$buhin_kin = 0;
$sum1 = '1406';
$sum2 = '00';
$query_k = sprintf("select rep_cri, rep_de, rep_cr from financial_report_cal where rep_ymd=%d and rep_summary1='%s' and rep_summary2='%s'", $nk_ki, $sum1, $sum2);
if ($rows_k=getResultWithField2($query_k, $field_k, $res_k) <= 0) {
    $buhin_kishu = 0;
} else {
    $buhin_kishu = $res_k[0][0];
}
$res   = array();
$field = array();
$rows  = array();
$query = sprintf("select SUM(rep_de), SUM(rep_cr) from financial_report_month where rep_ymd>=%d and rep_ymd<=%d and rep_summary1='%s'", $str_ym, $end_ym, $sum1);
if ($rows=getResultWithField2($query, $field, $res) <= 0) {
    $buhin_kin = $buhin_kishu;
} else {
    $buhin_kin = $buhin_kishu + ($res[0][0] - $res[0][1]);
}

// 部品仕掛品1407 30
/*
$res   = array();
$field = array();
$rows  = array();
$buhinsi_kin = 0;
$sum1 = '1407';
$sum2 = '30';
$query = sprintf("select rep_cri, rep_de, rep_cr from financial_report_cal where rep_ymd=%d and rep_summary1='%s' and rep_summary2='%s'", $yyyymm, $sum1, $sum2);
if ($rows=getResultWithField2($query, $field, $res) <= 0) {
    $buhinsi_kin = 0;
} else {
    $buhinsi_kin = $res[0][0] + $res[0][1] - $res[0][2];
}
*/
$res_k   = array();
$field_k = array();
$rows_k  = array();
$buhinsi_kin = 0;
$sum1 = '1407';
$sum2 = '30';
$query_k = sprintf("select rep_cri, rep_de, rep_cr from financial_report_cal where rep_ymd=%d and rep_summary1='%s' and rep_summary2='%s'", $nk_ki, $sum1, $sum2);
if ($rows_k=getResultWithField2($query_k, $field_k, $res_k) <= 0) {
    $buhinsi_kishu = 0;
} else {
    $buhinsi_kishu = $res_k[0][0];
}
$res   = array();
$field = array();
$rows  = array();
$query = sprintf("select SUM(rep_de), SUM(rep_cr) from financial_report_month where rep_ymd>=%d and rep_ymd<=%d and rep_summary1='%s'", $str_ym, $end_ym, $sum1);
if ($rows=getResultWithField2($query, $field, $res) <= 0) {
    $buhinsi_kin = $buhinsi_kishu;
} else {
    $buhinsi_kin = $buhinsi_kishu + ($res[0][0] - $res[0][1]);
}

// 原材料1408 00
/*
$res   = array();
$field = array();
$rows  = array();
$genzai_kin = 0;
$sum1 = '1408';
$sum2 = '00';
$query = sprintf("select rep_cri, rep_de, rep_cr from financial_report_cal where rep_ymd=%d and rep_summary1='%s' and rep_summary2='%s'", $yyyymm, $sum1, $sum2);
if ($rows=getResultWithField2($query, $field, $res) <= 0) {
    $genzai_kin = 0;
} else {
    $genzai_kin = $res[0][0] + $res[0][1] - $res[0][2];
}
*/
$res_k   = array();
$field_k = array();
$rows_k  = array();
$genzai_kin = 0;
$sum1 = '1408';
$sum2 = '00';
$query_k = sprintf("select rep_cri, rep_de, rep_cr from financial_report_cal where rep_ymd=%d and rep_summary1='%s' and rep_summary2='%s'", $nk_ki, $sum1, $sum2);
if ($rows_k=getResultWithField2($query_k, $field_k, $res_k) <= 0) {
    $genzai_kishu = 0;
} else {
    $genzai_kishu = $res_k[0][0];
}
$res   = array();
$field = array();
$rows  = array();
$query = sprintf("select SUM(rep_de), SUM(rep_cr) from financial_report_month where rep_ymd>=%d and rep_ymd<=%d and rep_summary1='%s'", $str_ym, $end_ym, $sum1);
if ($rows=getResultWithField2($query, $field, $res) <= 0) {
    $genzai_kin = $genzai_kishu;
} else {
    $genzai_kin = $genzai_kishu + ($res[0][0] - $res[0][1]);
}

// その他の棚卸品1409 の合計
/*
$res   = array();
$field = array();
$rows  = array();
$sonotatana_kin = 0;
$sum1 = '1409';
$sum2 = '00';
$query = sprintf("select SUM(rep_cri), SUM(rep_de), SUM(rep_cr) from financial_report_cal where rep_ymd=%d and rep_summary1='%s'", $yyyymm, $sum1);
if ($rows=getResultWithField2($query, $field, $res) <= 0) {
    $sonotatana_kin = 0;
} else {
    $sonotatana_kin = $res[0][0] + $res[0][1] - $res[0][2];
}
*/
$res_k   = array();
$field_k = array();
$rows_k  = array();
$sonotatana_kin = 0;
$sum1 = '1409';
$sum2 = '00';
$query_k = sprintf("select SUM(rep_cri), SUM(rep_de), SUM(rep_cr) from financial_report_cal where rep_ymd=%d and rep_summary1='%s'", $nk_ki, $sum1);
if ($rows_k=getResultWithField2($query_k, $field_k, $res_k) <= 0) {
    $sonotatana_kishu = 0;
} else {
    $sonotatana_kishu = $res_k[0][0];
}
$res   = array();
$field = array();
$rows  = array();
$query = sprintf("select SUM(rep_de), SUM(rep_cr) from financial_report_month where rep_ymd>=%d and rep_ymd<=%d and rep_summary1='%s'", $str_ym, $end_ym, $sum1);
if ($rows=getResultWithField2($query, $field, $res) <= 0) {
    $sonotatana_kin = $sonotatana_kishu;
} else {
    $sonotatana_kin = $sonotatana_kishu + ($res[0][0] - $res[0][1]);
}

// 貯蔵品
$res   = array();
$field = array();
$rows  = array();
$note = '貯蔵品';
$query = sprintf("SELECT rep_kin FROM financial_report_data WHERE rep_ymd=%d and rep_note='%s'", $yyyymm, $note);
if ($rows=getResultWithField2($query, $field, $res) <= 0) {
    $chozo_kin = 0;
} else {
    $chozo_kin = $res[0][0];
}

// 在庫合計の計算
$zaiko_total_kin    = $seihin_kin + $seihinsi_kin + $buhin_kin + $buhinsi_kin + $genzai_kin + $sonotatana_kin + $chozo_kin;
// 製品合計の計算
$seihin_total_kin   = $seihin_kin;
// 仕掛品合計の計算
$sikakari_total_kin = $seihinsi_kin;
// 原材料及び貯蔵品合計の計算
$gencho_total_kin   = $buhin_kin + $buhinsi_kin + $genzai_kin + $sonotatana_kin + $chozo_kin;
// 流動資産在庫合計の計算
$ryudozaiko_total_kin   = $seihin_total_kin + $sikakari_total_kin + $gencho_total_kin;

// 評価切下げ抜出し
// 評価切下げ部品
$res   = array();
$field = array();
$rows  = array();
$note = '評価切下げ部品';
$query = sprintf("SELECT rep_kin FROM financial_report_data WHERE rep_ymd=%d and rep_note='%s'", $yyyymm, $note);
if ($rows=getResultWithField2($query, $field, $res) <= 0) {
    $hyoka_buhin_kin = 0;
} else {
    $hyoka_buhin_kin = $res[0][0];
}
// 評価切下げ材料
$res   = array();
$field = array();
$rows  = array();
$note = '評価切下げ材料';
$query = sprintf("SELECT rep_kin FROM financial_report_data WHERE rep_ymd=%d and rep_note='%s'", $yyyymm, $note);
if ($rows=getResultWithField2($query, $field, $res) <= 0) {
    $hyoka_zai_kin = 0;
} else {
    $hyoka_zai_kin = $res[0][0];
}
// 検査仕掛明細
$res   = array();
$field = array();
$rows  = array();
$note = '検査仕掛明細';
$query = sprintf("SELECT rep_kin FROM financial_report_data WHERE rep_ymd=%d and rep_note='%s'", $yyyymm, $note);
if ($rows=getResultWithField2($query, $field, $res) <= 0) {
    $tana_ken_kin = 0;
} else {
    $tana_ken_kin = $res[0][0];
}

// 工作仕掛明細
$res   = array();
$field = array();
$rows  = array();
$note = '工作仕掛明細';
$query = sprintf("SELECT rep_kin FROM financial_report_data WHERE rep_ymd=%d and rep_note='%s'", $yyyymm, $note);
if ($rows=getResultWithField2($query, $field, $res) <= 0) {
    $tana_kou_kin = 0;
} else {
    $tana_kou_kin = $res[0][0];
}

// 外注仕掛明細
$res   = array();
$field = array();
$rows  = array();
$note = '外注仕掛明細';
$query = sprintf("SELECT rep_kin FROM financial_report_data WHERE rep_ymd=%d and rep_note='%s'", $yyyymm, $note);
if ($rows=getResultWithField2($query, $field, $res) <= 0) {
    $tana_gai_kin = 0;
} else {
    $tana_gai_kin = $res[0][0];
}

// 棚卸資産の内訳計算
$sei_buhin_kin  = $buhin_kin - $hyoka_buhin_kin;
$han_total_kin  = $tana_ken_kin + $tana_kou_kin + $tana_gai_kin;
$gen_sizai_kin  = $genzai_kin - $hyoka_zai_kin;
$tana_sizai_kin = $sei_buhin_kin + $tana_ken_kin + $gen_sizai_kin;
$kumi_cc_kin    = $sonotatana_kin + $hyoka_buhin_kin + $hyoka_zai_kin;
$kumi_total_kin = $kumi_cc_kin + $sikakari_total_kin;

// 前払費用
$res   = array();
$field = array();
$rows  = array();
$mae_hiyo_kin = 0;
$note = '前払費用';
$query = sprintf("SELECT kin FROM profit_loss_bs_history WHERE pl_bs_ym=%d and note='%s'", $yyyymm, $note);
if ($rows=getResultWithField2($query, $field, $res) <= 0) {
    $mae_hiyo_kin = 0;
} else {
    $mae_hiyo_kin = $res[0][0];
}

///// 流動資産
// 有償支給未収入金1302 00
/*
$res   = array();
$field = array();
$rows  = array();
$yumi_kin = 0;
$sum1 = '1302';
$sum2 = '00';
$query = sprintf("select rep_cri, rep_de, rep_cr from financial_report_cal where rep_ymd=%d and rep_summary1='%s' and rep_summary2='%s'", $yyyymm, $sum1, $sum2);
if ($rows=getResultWithField2($query, $field, $res) <= 0) {
    $yumi_kin = 0;
} else {
    $yumi_kin = $res[0][0] + $res[0][1] - $res[0][2];
}
*/
$res_k   = array();
$field_k = array();
$rows_k  = array();
$yumi_kin = 0;
$sum1 = '1302';
$sum2 = '00';
$query_k = sprintf("select rep_cri, rep_de, rep_cr from financial_report_cal where rep_ymd=%d and rep_summary1='%s' and rep_summary2='%s'", $nk_ki, $sum1, $sum2);
if ($rows_k=getResultWithField2($query_k, $field_k, $res_k) <= 0) {
    $yumi_kishu = 0;
} else {
    $yumi_kishu = $res_k[0][0];
}
$res   = array();
$field = array();
$rows  = array();
$query = sprintf("select SUM(rep_de), SUM(rep_cr) from financial_report_month where rep_ymd>=%d and rep_ymd<=%d and rep_summary1='%s'", $str_ym, $end_ym, $sum1);
if ($rows=getResultWithField2($query, $field, $res) <= 0) {
    $yumi_kin = $yumi_kishu;
} else {
    $yumi_kin = $yumi_kishu + ($res[0][0] - $res[0][1]);
}

// 未収入金1503 00
/*
$res   = array();
$field = array();
$rows  = array();
$mishu_kin = 0;
$sum1 = '1503';
$sum2 = '00';
$query = sprintf("select rep_cri, rep_de, rep_cr from financial_report_cal where rep_ymd=%d and rep_summary1='%s' and rep_summary2='%s'", $yyyymm, $sum1, $sum2);
if ($rows=getResultWithField2($query, $field, $res) <= 0) {
    $mishu_kin = 0;
} else {
    $mishu_kin = $res[0][0] + $res[0][1] - $res[0][2];
}
*/
$res_k   = array();
$field_k = array();
$rows_k  = array();
$mishu_kin = 0;
$sum1 = '1503';
$sum2 = '00';
$query_k = sprintf("select rep_cri, rep_de, rep_cr from financial_report_cal where rep_ymd=%d and rep_summary1='%s' and rep_summary2='%s'", $nk_ki, $sum1, $sum2);
if ($rows_k=getResultWithField2($query_k, $field_k, $res_k) <= 0) {
    $mishu_kishu = 0;
} else {
    $mishu_kishu = $res_k[0][0];
}
$res   = array();
$field = array();
$rows  = array();
$query = sprintf("select SUM(rep_de), SUM(rep_cr) from financial_report_month where rep_ymd>=%d and rep_ymd<=%d and rep_summary1='%s'", $str_ym, $end_ym, $sum1);
if ($rows=getResultWithField2($query, $field, $res) <= 0) {
    $mishu_kin = $mishu_kishu;
} else {
    $mishu_kin = $mishu_kishu + ($res[0][0] - $res[0][1]);
}

// 未収収益1701 00
/*
$res   = array();
$field = array();
$rows  = array();
$mishueki_kin = 0;
$sum1 = '1701';
$sum2 = '00';
$query = sprintf("select rep_cri, rep_de, rep_cr from financial_report_cal where rep_ymd=%d and rep_summary1='%s' and rep_summary2='%s'", $yyyymm, $sum1, $sum2);
if ($rows=getResultWithField2($query, $field, $res) <= 0) {
    $mishueki_kin = 0;
} else {
    $mishueki_kin = $res[0][0] + $res[0][1] - $res[0][2];
}
*/
$res_k   = array();
$field_k = array();
$rows_k  = array();
$mishueki_kin = 0;
$sum1 = '1701';
$sum2 = '00';
$query_k = sprintf("select rep_cri, rep_de, rep_cr from financial_report_cal where rep_ymd=%d and rep_summary1='%s' and rep_summary2='%s'", $nk_ki, $sum1, $sum2);
if ($rows_k=getResultWithField2($query_k, $field_k, $res_k) <= 0) {
    $mishueki_kishu = 0;
} else {
    $mishueki_kishu = $res_k[0][0];
}
$res   = array();
$field = array();
$rows  = array();
$query = sprintf("select SUM(rep_de), SUM(rep_cr) from financial_report_month where rep_ymd>=%d and rep_ymd<=%d and rep_summary1='%s'", $str_ym, $end_ym, $sum1);
if ($rows=getResultWithField2($query, $field, $res) <= 0) {
    $mishueki_kin = $mishueki_kishu;
} else {
    $mishueki_kin = $mishueki_kishu + ($res[0][0] - $res[0][1]);
}

//// その他の流動資産
// 立替金1505 00
/*
$res   = array();
$field = array();
$rows  = array();
$tatekae_kin = 0;
$sum1 = '1505';
$sum2 = '00';
$query = sprintf("select rep_cri, rep_de, rep_cr from financial_report_cal where rep_ymd=%d and rep_summary1='%s' and rep_summary2='%s'", $yyyymm, $sum1, $sum2);
if ($rows=getResultWithField2($query, $field, $res) <= 0) {
    $tatekae_kin = 0;
} else {
    $tatekae_kin = $res[0][0] + $res[0][1] - $res[0][2];
}
*/
$res_k   = array();
$field_k = array();
$rows_k  = array();
$tatekae_kin = 0;
$sum1 = '1505';
$sum2 = '00';
$query_k = sprintf("select rep_cri, rep_de, rep_cr from financial_report_cal where rep_ymd=%d and rep_summary1='%s' and rep_summary2='%s'", $nk_ki, $sum1, $sum2);
if ($rows_k=getResultWithField2($query_k, $field_k, $res_k) <= 0) {
    $tatekae_kishu = 0;
} else {
    $tatekae_kishu = $res_k[0][0];
}
$res   = array();
$field = array();
$rows  = array();
$query = sprintf("select SUM(rep_de), SUM(rep_cr) from financial_report_month where rep_ymd>=%d and rep_ymd<=%d and rep_summary1='%s'", $str_ym, $end_ym, $sum1);
if ($rows=getResultWithField2($query, $field, $res) <= 0) {
    $tatekae_kin = $tatekae_kishu;
} else {
    $tatekae_kin = $tatekae_kishu + ($res[0][0] - $res[0][1]);
}

// 仮払金1504 00
/*
$res   = array();
$field = array();
$rows  = array();
$karibara_kin = 0;
$sum1 = '1504';
$sum2 = '00';
$query = sprintf("select rep_cri, rep_de, rep_cr from financial_report_cal where rep_ymd=%d and rep_summary1='%s' and rep_summary2='%s'", $yyyymm, $sum1, $sum2);
if ($rows=getResultWithField2($query, $field, $res) <= 0) {
    $karibara_kin = 0;
} else {
    // 仮払金は貯蔵品分をマイナス
    $karibara_kin = $res[0][0] + $res[0][1] - $res[0][2] - $chozo_kin;
}
*/
$res_k   = array();
$field_k = array();
$rows_k  = array();
$karibara_kin = 0;
$sum1 = '1504';
$sum2 = '00';
$query_k = sprintf("select rep_cri, rep_de, rep_cr from financial_report_cal where rep_ymd=%d and rep_summary1='%s' and rep_summary2='%s'", $nk_ki, $sum1, $sum2);
if ($rows_k=getResultWithField2($query_k, $field_k, $res_k) <= 0) {
    $karibara_kishu = 0;
} else {
    $karibara_kishu = $res_k[0][0];
}
$res   = array();
$field = array();
$rows  = array();
$query = sprintf("select SUM(rep_de), SUM(rep_cr) from financial_report_month where rep_ymd>=%d and rep_ymd<=%d and rep_summary1='%s'", $str_ym, $end_ym, $sum1);
if ($rows=getResultWithField2($query, $field, $res) <= 0) {
    $karibara_kin = $karibara_kishu - $chozo_kin;
} else {
    $karibara_kin = $karibara_kishu + ($res[0][0] - $res[0][1]) - $chozo_kin;
}

// その他流動資産2000 00
/*
$res   = array();
$field = array();
$rows  = array();
$hokaryudo_kin = 0;
$sum1 = '2000';
$sum2 = '00';
$query = sprintf("select rep_cri, rep_de, rep_cr from financial_report_cal where rep_ymd=%d and rep_summary1='%s' and rep_summary2='%s'", $yyyymm, $sum1, $sum2);
if ($rows=getResultWithField2($query, $field, $res) <= 0) {
    $hokaryudo_kin = 0;
} else {
    $hokaryudo_kin = $res[0][0] + $res[0][1] - $res[0][2];
}
*/
$res_k   = array();
$field_k = array();
$rows_k  = array();
$hokaryudo_kin = 0;
$sum1 = '2000';
$sum2 = '00';
$query_k = sprintf("select rep_cri, rep_de, rep_cr from financial_report_cal where rep_ymd=%d and rep_summary1='%s' and rep_summary2='%s'", $nk_ki, $sum1, $sum2);
if ($rows_k=getResultWithField2($query_k, $field_k, $res_k) <= 0) {
    $hokaryudo_kishu = 0;
} else {
    $hokaryudo_kishu = $res_k[0][0];
}
$res   = array();
$field = array();
$rows  = array();
$query = sprintf("select SUM(rep_de), SUM(rep_cr) from financial_report_month where rep_ymd>=%d and rep_ymd<=%d and rep_summary1='%s'", $str_ym, $end_ym, $sum1);
if ($rows=getResultWithField2($query, $field, $res) <= 0) {
    $hokaryudo_kin = $hokaryudo_kishu;
} else {
    $hokaryudo_kin = $hokaryudo_kishu + ($res[0][0] - $res[0][1]);
}

// その他流動資産にプラスする 他勘定未決算（資）1901 20
$res_k   = array();
$field_k = array();
$rows_k  = array();
$hokaryudo_kin = 0;
$sum1 = '1901';
$sum2 = '20';
$query_k = sprintf("select rep_cri, rep_de, rep_cr from financial_report_cal where rep_ymd=%d and rep_summary1='%s' and rep_summary2='%s'", $nk_ki, $sum1, $sum2);
if ($rows_k=getResultWithField2($query_k, $field_k, $res_k) <= 0) {
    $ta_miketsu_kishu = 0;
} else {
    $ta_miketsu_kishu = $res_k[0][0];
}
$res   = array();
$field = array();
$rows  = array();
$query = sprintf("select SUM(rep_de), SUM(rep_cr) from financial_report_month where rep_ymd>=%d and rep_ymd<=%d and rep_summary1='%s'", $str_ym, $end_ym, $sum1);
if ($rows=getResultWithField2($query, $field, $res) <= 0) {
    $ta_miketsu_kin = $ta_miketsu_kishu;
} else {
    $ta_miketsu_kin = $ta_miketsu_kishu + ($res[0][0] - $res[0][1]);
}

// その他流動資産の計算
$hokaryudo_kin = $hokaryudo_kin + $ta_miketsu_kin;

// 流動資産 未収入金の計算
$ryu_mishu_kin    = $yumi_kin + $mishu_kin + $mishueki_kin;
// 流動資産 未収入金合計の計算
$ryu_mishu_total_kin    = $ryu_mishu_kin;
// 流動資産 その他流動資産計の計算
$hokaryudo_total_kin    = $tatekae_kin + $karibara_kin + $hokaryudo_kin;
// 流動資産 未収入金合計の計算
$hokaryudo_all_kin    = $hokaryudo_total_kin;

//// 有形固定資産
/*
// 建物2101 00
$res   = array();
$field = array();
$rows  = array();
$tatemono_kin = 0;
$sum1 = '2101';
$sum2 = '00';
$query = sprintf("select rep_cri, rep_de, rep_cr from financial_report_cal where rep_ymd=%d and rep_summary1='%s' and rep_summary2='%s'", $yyyymm, $sum1, $sum2);
if ($rows=getResultWithField2($query, $field, $res) <= 0) {
    $tatemono_kin = 0;
} else {
    $tatemono_kin = $res[0][0] + $res[0][1] - $res[0][2];
}
*/
$res_k   = array();
$field_k = array();
$rows_k  = array();
$tatemono_kin = 0;
$sum1 = '2101';
$sum2 = '00';
$query_k = sprintf("select rep_cri, rep_de, rep_cr from financial_report_cal where rep_ymd=%d and rep_summary1='%s' and rep_summary2='%s'", $nk_ki, $sum1, $sum2);
if ($rows_k=getResultWithField2($query_k, $field_k, $res_k) <= 0) {
    $tatemono_kishu = 0;
} else {
    $tatemono_kishu = $res_k[0][0];
}
$res   = array();
$field = array();
$rows  = array();
$query = sprintf("select SUM(rep_de), SUM(rep_cr) from financial_report_month where rep_ymd>=%d and rep_ymd<=%d and rep_summary1='%s'", $str_ym, $end_ym, $sum1);
if ($rows=getResultWithField2($query, $field, $res) <= 0) {
    $tatemono_kin = $tatemono_kishu;
} else {
    $tatemono_kin = $tatemono_kishu + ($res[0][0] - $res[0][1]);
}

// 建物減累額3401 10
/*
$res   = array();
$field = array();
$rows  = array();
$tate_gen_kin = 0;
$sum1 = '3401';
$sum2 = '10';
$query = sprintf("select rep_cri, rep_de, rep_cr from financial_report_cal where rep_ymd=%d and rep_summary1='%s' and rep_summary2='%s'", $yyyymm, $sum1, $sum2);
if ($rows=getResultWithField2($query, $field, $res) <= 0) {
    $tate_gen_kin = 0;
} else {
    $tate_gen_kin = -($res[0][0] + $res[0][1] - $res[0][2]);
}
*/
$res_k   = array();
$field_k = array();
$rows_k  = array();
$tate_gen_kin = 0;
$sum1 = '3401';
$sum2 = '10';
$query_k = sprintf("select rep_cri, rep_de, rep_cr from financial_report_cal where rep_ymd=%d and rep_summary1='%s' and rep_summary2='%s'", $nk_ki, $sum1, $sum2);
if ($rows_k=getResultWithField2($query_k, $field_k, $res_k) <= 0) {
    $tate_gen_kishu = 0;
} else {
    $tate_gen_kishu = $res_k[0][0];
}
$res   = array();
$field = array();
$rows  = array();
$query = sprintf("select SUM(rep_de), SUM(rep_cr) from financial_report_month where rep_ymd>=%d and rep_ymd<=%d and rep_summary1='%s' and rep_summary2='%s'", $str_ym, $end_ym, $sum1, $sum2);
if ($rows=getResultWithField2($query, $field, $res) <= 0) {
    $tate_gen_kin = $tate_gen_kishu;
} else {
    $tate_gen_kin = -($tate_gen_kishu + ($res[0][0] - $res[0][1]));
}

// 建物資産金額
$tate_shisan_kin = $tatemono_kin - $tate_gen_kin;
// 建物付属設備2102 00
/*
$res   = array();
$field = array();
$rows  = array();
$fuzoku_kin = 0;
$sum1 = '2102';
$sum2 = '00';
$query = sprintf("select rep_cri, rep_de, rep_cr from financial_report_cal where rep_ymd=%d and rep_summary1='%s' and rep_summary2='%s'", $yyyymm, $sum1, $sum2);
if ($rows=getResultWithField2($query, $field, $res) <= 0) {
    $fuzoku_kin = 0;
} else {
    $fuzoku_kin = $res[0][0] + $res[0][1] - $res[0][2];
}
*/
$res_k   = array();
$field_k = array();
$rows_k  = array();
$fuzoku_kin = 0;
$sum1 = '2102';
$sum2 = '00';
$query_k = sprintf("select rep_cri, rep_de, rep_cr from financial_report_cal where rep_ymd=%d and rep_summary1='%s' and rep_summary2='%s'", $nk_ki, $sum1, $sum2);
if ($rows_k=getResultWithField2($query_k, $field_k, $res_k) <= 0) {
    $fuzoku_kishu = 0;
} else {
    $fuzoku_kishu = $res_k[0][0];
}
$res   = array();
$field = array();
$rows  = array();
$query = sprintf("select SUM(rep_de), SUM(rep_cr) from financial_report_month where rep_ymd>=%d and rep_ymd<=%d and rep_summary1='%s'", $str_ym, $end_ym, $sum1);
if ($rows=getResultWithField2($query, $field, $res) <= 0) {
    $fuzoku_kin = $fuzoku_kishu;
} else {
    $fuzoku_kin = $fuzoku_kishu + ($res[0][0] - $res[0][1]);
}

// 建物付属設備減累額3401 20
/*
$res   = array();
$field = array();
$rows  = array();
$fuzoku_gen_kin = 0;
$sum1 = '3401';
$sum2 = '20';
$query = sprintf("select rep_cri, rep_de, rep_cr from financial_report_cal where rep_ymd=%d and rep_summary1='%s' and rep_summary2='%s'", $yyyymm, $sum1, $sum2);
if ($rows=getResultWithField2($query, $field, $res) <= 0) {
    $fuzoku_gen_kin = 0;
} else {
    $fuzoku_gen_kin = -($res[0][0] + $res[0][1] - $res[0][2]);
}
*/
$res_k   = array();
$field_k = array();
$rows_k  = array();
$fuzoku_gen_kin = 0;
$sum1 = '3401';
$sum2 = '20';
$query_k = sprintf("select rep_cri, rep_de, rep_cr from financial_report_cal where rep_ymd=%d and rep_summary1='%s' and rep_summary2='%s'", $nk_ki, $sum1, $sum2);
if ($rows_k=getResultWithField2($query_k, $field_k, $res_k) <= 0) {
    $fuzoku_gen_kishu = 0;
} else {
    $fuzoku_gen_kishu = $res_k[0][0];
}
$res   = array();
$field = array();
$rows  = array();
$query = sprintf("select SUM(rep_de), SUM(rep_cr) from financial_report_month where rep_ymd>=%d and rep_ymd<=%d and rep_summary1='%s' and rep_summary2='%s'", $str_ym, $end_ym, $sum1, $sum2);
if ($rows=getResultWithField2($query, $field, $res) <= 0) {
    $fuzoku_gen_kin = $fuzoku_gen_kishu;
} else {
    $fuzoku_gen_kin = -($fuzoku_gen_kishu + ($res[0][0] - $res[0][1]));
}

// 建物付属設備資産金額
$fuzoku_shisan_kin = $fuzoku_kin - $fuzoku_gen_kin;
// 建物合計資産金額
$tate_all_shisan_kin = $tate_shisan_kin + $fuzoku_shisan_kin;

//eca用 資産金額建物
$tate_shutoku_kin = $tatemono_kin + $fuzoku_kin;
//eca用 減価償却累計額(建物)
$tate_rui_kin = -($tate_gen_kin + $fuzoku_gen_kin);

// 構築物2103 00
/*
$res   = array();
$field = array();
$rows  = array();
$kouchiku_kin = 0;
$sum1 = '2103';
$sum2 = '00';
$query = sprintf("select rep_cri, rep_de, rep_cr from financial_report_cal where rep_ymd=%d and rep_summary1='%s' and rep_summary2='%s'", $yyyymm, $sum1, $sum2);
if ($rows=getResultWithField2($query, $field, $res) <= 0) {
    $kouchiku_kin = 0;
} else {
    $kouchiku_kin = $res[0][0] + $res[0][1] - $res[0][2];
}
*/
$res_k   = array();
$field_k = array();
$rows_k  = array();
$kouchiku_kin = 0;
$sum1 = '2103';
$sum2 = '00';
$query_k = sprintf("select rep_cri, rep_de, rep_cr from financial_report_cal where rep_ymd=%d and rep_summary1='%s' and rep_summary2='%s'", $nk_ki, $sum1, $sum2);
if ($rows_k=getResultWithField2($query_k, $field_k, $res_k) <= 0) {
    $kouchiku_kishu = 0;
} else {
    $kouchiku_kishu = $res_k[0][0];
}
$res   = array();
$field = array();
$rows  = array();
$query = sprintf("select SUM(rep_de), SUM(rep_cr) from financial_report_month where rep_ymd>=%d and rep_ymd<=%d and rep_summary1='%s'", $str_ym, $end_ym, $sum1);
if ($rows=getResultWithField2($query, $field, $res) <= 0) {
    $kouchiku_kin = $kouchiku_kishu;
} else {
    $kouchiku_kin = $kouchiku_kishu + ($res[0][0] - $res[0][1]);
}

// 構築物減累額3401 30
/*
$res   = array();
$field = array();
$rows  = array();
$kouchiku_gen_kin = 0;
$sum1 = '3401';
$sum2 = '30';
$query = sprintf("select rep_cri, rep_de, rep_cr from financial_report_cal where rep_ymd=%d and rep_summary1='%s' and rep_summary2='%s'", $yyyymm, $sum1, $sum2);
if ($rows=getResultWithField2($query, $field, $res) <= 0) {
    $kouchiku_gen_kin = 0;
} else {
    $kouchiku_gen_kin = -($res[0][0] + $res[0][1] - $res[0][2]);
}
*/
$res_k   = array();
$field_k = array();
$rows_k  = array();
$kouchiku_gen_kin = 0;
$sum1 = '3401';
$sum2 = '30';
$query_k = sprintf("select rep_cri, rep_de, rep_cr from financial_report_cal where rep_ymd=%d and rep_summary1='%s' and rep_summary2='%s'", $nk_ki, $sum1, $sum2);
if ($rows_k=getResultWithField2($query_k, $field_k, $res_k) <= 0) {
    $kouchiku_gen_kishu = 0;
} else {
    $kouchiku_gen_kishu = $res_k[0][0];
}
$res   = array();
$field = array();
$rows  = array();
$query = sprintf("select SUM(rep_de), SUM(rep_cr) from financial_report_month where rep_ymd>=%d and rep_ymd<=%d and rep_summary1='%s' and rep_summary2='%s'", $str_ym, $end_ym, $sum1, $sum2);
if ($rows=getResultWithField2($query, $field, $res) <= 0) {
    $kouchiku_gen_kin = $kouchiku_gen_kishu;
} else {
    $kouchiku_gen_kin = -($kouchiku_gen_kishu + ($res[0][0] - $res[0][1]));
}

// 構築物資産金額
$kouchiku_shisan_kin = $kouchiku_kin - $kouchiku_gen_kin;

// 機械装置2104 00
/*
$res   = array();
$field = array();
$rows  = array();
$kikai_kin = 0;
$sum1 = '2104';
$sum2 = '00';
$query = sprintf("select rep_cri, rep_de, rep_cr from financial_report_cal where rep_ymd=%d and rep_summary1='%s' and rep_summary2='%s'", $yyyymm, $sum1, $sum2);
if ($rows=getResultWithField2($query, $field, $res) <= 0) {
    $kikai_kin = 0;
} else {
    $kikai_kin = $res[0][0] + $res[0][1] - $res[0][2];
}
*/
$res_k   = array();
$field_k = array();
$rows_k  = array();
$kikai_kin = 0;
$sum1 = '2104';
$sum2 = '00';
$query_k = sprintf("select rep_cri, rep_de, rep_cr from financial_report_cal where rep_ymd=%d and rep_summary1='%s' and rep_summary2='%s'", $nk_ki, $sum1, $sum2);
if ($rows_k=getResultWithField2($query_k, $field_k, $res_k) <= 0) {
    $kikai_kishu = 0;
} else {
    $kikai_kishu = $res_k[0][0];
}
$res   = array();
$field = array();
$rows  = array();
$query = sprintf("select SUM(rep_de), SUM(rep_cr) from financial_report_month where rep_ymd>=%d and rep_ymd<=%d and rep_summary1='%s'", $str_ym, $end_ym, $sum1);
if ($rows=getResultWithField2($query, $field, $res) <= 0) {
    $kikai_kin = $kikai_kishu;
} else {
    $kikai_kin = $kikai_kishu + ($res[0][0] - $res[0][1]);
}

// 機械装置減累額3401 40
/*
$res   = array();
$field = array();
$rows  = array();
$kikai_gen_kin = 0;
$sum1 = '3401';
$sum2 = '40';
$query = sprintf("select rep_cri, rep_de, rep_cr from financial_report_cal where rep_ymd=%d and rep_summary1='%s' and rep_summary2='%s'", $yyyymm, $sum1, $sum2);
if ($rows=getResultWithField2($query, $field, $res) <= 0) {
    $kikai_gen_kin = 0;
} else {
    $kikai_gen_kin = -($res[0][0] + $res[0][1] - $res[0][2]);
}
*/
$res_k   = array();
$field_k = array();
$rows_k  = array();
$kikai_gen_kin = 0;
$sum1 = '3401';
$sum2 = '40';
$query_k = sprintf("select rep_cri, rep_de, rep_cr from financial_report_cal where rep_ymd=%d and rep_summary1='%s' and rep_summary2='%s'", $nk_ki, $sum1, $sum2);
if ($rows_k=getResultWithField2($query_k, $field_k, $res_k) <= 0) {
    $kikai_gen_kishu = 0;
} else {
    $kikai_gen_kishu = $res_k[0][0];
}
$res   = array();
$field = array();
$rows  = array();
$query = sprintf("select SUM(rep_de), SUM(rep_cr) from financial_report_month where rep_ymd>=%d and rep_ymd<=%d and rep_summary1='%s' and rep_summary2='%s'", $str_ym, $end_ym, $sum1, $sum2);
if ($rows=getResultWithField2($query, $field, $res) <= 0) {
    $kikai_gen_kin = $kikai_gen_kishu;
} else {
    $kikai_gen_kin = -($kikai_gen_kishu + ($res[0][0] - $res[0][1]));
}

// 機械装置資産金額
$kikai_shisan_kin = $kikai_kin - $kikai_gen_kin;

// 車輌運搬具2105 00
/*
$res   = array();
$field = array();
$rows  = array();
$sharyo_kin = 0;
$sum1 = '2105';
$sum2 = '00';
$query = sprintf("select rep_cri, rep_de, rep_cr from financial_report_cal where rep_ymd=%d and rep_summary1='%s' and rep_summary2='%s'", $yyyymm, $sum1, $sum2);
if ($rows=getResultWithField2($query, $field, $res) <= 0) {
    $sharyo_kin = 0;
} else {
    $sharyo_kin = $res[0][0] + $res[0][1] - $res[0][2];
}
*/
$res_k   = array();
$field_k = array();
$rows_k  = array();
$sharyo_kin = 0;
$sum1 = '2105';
$sum2 = '00';
$query_k = sprintf("select rep_cri, rep_de, rep_cr from financial_report_cal where rep_ymd=%d and rep_summary1='%s' and rep_summary2='%s'", $nk_ki, $sum1, $sum2);
if ($rows_k=getResultWithField2($query_k, $field_k, $res_k) <= 0) {
    $sharyo_kishu = 0;
} else {
    $sharyo_kishu = $res_k[0][0];
}
$res   = array();
$field = array();
$rows  = array();
$query = sprintf("select SUM(rep_de), SUM(rep_cr) from financial_report_month where rep_ymd>=%d and rep_ymd<=%d and rep_summary1='%s'", $str_ym, $end_ym, $sum1);
if ($rows=getResultWithField2($query, $field, $res) <= 0) {
    $sharyo_kin = $sharyo_kishu;
} else {
    $sharyo_kin = $sharyo_kishu + ($res[0][0] - $res[0][1]);
}

// 車輌運搬具減累額3401 50
/*
$res   = array();
$field = array();
$rows  = array();
$sharyo_gen_kin = 0;
$sum1 = '3401';
$sum2 = '50';
$query = sprintf("select rep_cri, rep_de, rep_cr from financial_report_cal where rep_ymd=%d and rep_summary1='%s' and rep_summary2='%s'", $yyyymm, $sum1, $sum2);
if ($rows=getResultWithField2($query, $field, $res) <= 0) {
    $sharyo_gen_kin = 0;
} else {
    $sharyo_gen_kin = -($res[0][0] + $res[0][1] - $res[0][2]);
}
*/
$res_k   = array();
$field_k = array();
$rows_k  = array();
$sharyo_gen_kin = 0;
$sum1 = '3401';
$sum2 = '50';
$query_k = sprintf("select rep_cri, rep_de, rep_cr from financial_report_cal where rep_ymd=%d and rep_summary1='%s' and rep_summary2='%s'", $nk_ki, $sum1, $sum2);
if ($rows_k=getResultWithField2($query_k, $field_k, $res_k) <= 0) {
    $sharyo_gen_kishu = 0;
} else {
    $sharyo_gen_kishu = $res_k[0][0];
}
$res   = array();
$field = array();
$rows  = array();
$query = sprintf("select SUM(rep_de), SUM(rep_cr) from financial_report_month where rep_ymd>=%d and rep_ymd<=%d and rep_summary1='%s' and rep_summary2='%s'", $str_ym, $end_ym, $sum1, $sum2);
if ($rows=getResultWithField2($query, $field, $res) <= 0) {
    $sharyo_gen_kin = $sharyo_gen_kishu;
} else {
    $sharyo_gen_kin = -($sharyo_gen_kishu + ($res[0][0] - $res[0][1]));
}

// 車輌運搬具資産金額
$sharyo_shisan_kin = $sharyo_kin - $sharyo_gen_kin;

// 器具工具2106 00
/*
$res   = array();
$field = array();
$rows  = array();
$kigu_kin = 0;
$sum1 = '2106';
$sum2 = '00';
$query = sprintf("select rep_cri, rep_de, rep_cr from financial_report_cal where rep_ymd=%d and rep_summary1='%s' and rep_summary2='%s'", $yyyymm, $sum1, $sum2);
if ($rows=getResultWithField2($query, $field, $res) <= 0) {
    $kigu_kin = 0;
} else {
    $kigu_kin = $res[0][0] + $res[0][1] - $res[0][2];
}
*/
$res_k   = array();
$field_k = array();
$rows_k  = array();
$kigu_kin = 0;
$sum1 = '2106';
$sum2 = '00';
$query_k = sprintf("select rep_cri, rep_de, rep_cr from financial_report_cal where rep_ymd=%d and rep_summary1='%s' and rep_summary2='%s'", $nk_ki, $sum1, $sum2);
if ($rows_k=getResultWithField2($query_k, $field_k, $res_k) <= 0) {
    $kigu_kishu = 0;
} else {
    $kigu_kishu = $res_k[0][0];
}
$res   = array();
$field = array();
$rows  = array();
$query = sprintf("select SUM(rep_de), SUM(rep_cr) from financial_report_month where rep_ymd>=%d and rep_ymd<=%d and rep_summary1='%s'", $str_ym, $end_ym, $sum1);
if ($rows=getResultWithField2($query, $field, $res) <= 0) {
    $kigu_kin = $kigu_kishu;
} else {
    $kigu_kin = $kigu_kishu + ($res[0][0] - $res[0][1]);
}

// 器具工具減累額3401 60
/*
$res   = array();
$field = array();
$rows  = array();
$kigu_gen_kin = 0;
$sum1 = '3401';
$sum2 = '60';
$query = sprintf("select rep_cri, rep_de, rep_cr from financial_report_cal where rep_ymd=%d and rep_summary1='%s' and rep_summary2='%s'", $yyyymm, $sum1, $sum2);
if ($rows=getResultWithField2($query, $field, $res) <= 0) {
    $kigu_gen_kin = 0;
} else {
    $kigu_gen_kin = -($res[0][0] + $res[0][1] - $res[0][2]);
}
*/
$res_k   = array();
$field_k = array();
$rows_k  = array();
$kigu_gen_kin = 0;
$sum1 = '3401';
$sum2 = '60';
$query_k = sprintf("select rep_cri, rep_de, rep_cr from financial_report_cal where rep_ymd=%d and rep_summary1='%s' and rep_summary2='%s'", $nk_ki, $sum1, $sum2);
if ($rows_k=getResultWithField2($query_k, $field_k, $res_k) <= 0) {
    $kigu_gen_kishu = 0;
} else {
    $kigu_gen_kishu = $res_k[0][0];
}
$res   = array();
$field = array();
$rows  = array();
$query = sprintf("select SUM(rep_de), SUM(rep_cr) from financial_report_month where rep_ymd>=%d and rep_ymd<=%d and rep_summary1='%s' and rep_summary2='%s'", $str_ym, $end_ym, $sum1, $sum2);
if ($rows=getResultWithField2($query, $field, $res) <= 0) {
    $kigu_gen_kin = $kigu_gen_kishu;
} else {
    $kigu_gen_kin = -($kigu_gen_kishu + ($res[0][0] - $res[0][1]));
}

// 器具工具資産金額
$kigu_shisan_kin = $kigu_kin - $kigu_gen_kin;

// 什器備品2107 00
/*
$res   = array();
$field = array();
$rows  = array();
$jyuki_kin = 0;
$sum1 = '2107';
$sum2 = '00';
$query = sprintf("select rep_cri, rep_de, rep_cr from financial_report_cal where rep_ymd=%d and rep_summary1='%s' and rep_summary2='%s'", $yyyymm, $sum1, $sum2);
if ($rows=getResultWithField2($query, $field, $res) <= 0) {
    $jyuki_kin = 0;
} else {
    $jyuki_kin = $res[0][0] + $res[0][1] - $res[0][2];
}
*/
$res_k   = array();
$field_k = array();
$rows_k  = array();
$jyuki_kin = 0;
$sum1 = '2107';
$sum2 = '00';
$query_k = sprintf("select rep_cri, rep_de, rep_cr from financial_report_cal where rep_ymd=%d and rep_summary1='%s' and rep_summary2='%s'", $nk_ki, $sum1, $sum2);
if ($rows_k=getResultWithField2($query_k, $field_k, $res_k) <= 0) {
    $jyuki_kishu = 0;
} else {
    $jyuki_kishu = $res_k[0][0];
}
$res   = array();
$field = array();
$rows  = array();
$query = sprintf("select SUM(rep_de), SUM(rep_cr) from financial_report_month where rep_ymd>=%d and rep_ymd<=%d and rep_summary1='%s'", $str_ym, $end_ym, $sum1);
if ($rows=getResultWithField2($query, $field, $res) <= 0) {
    $jyuki_kin = $jyuki_kishu;
} else {
    $jyuki_kin = $jyuki_kishu + ($res[0][0] - $res[0][1]);
}

// 什器備品減累額3401 70
/*
$res   = array();
$field = array();
$rows  = array();
$jyuki_gen_kin = 0;
$sum1 = '3401';
$sum2 = '70';
$query = sprintf("select rep_cri, rep_de, rep_cr from financial_report_cal where rep_ymd=%d and rep_summary1='%s' and rep_summary2='%s'", $yyyymm, $sum1, $sum2);
if ($rows=getResultWithField2($query, $field, $res) <= 0) {
    $jyuki_gen_kin = 0;
} else {
    $jyuki_gen_kin = -($res[0][0] + $res[0][1] - $res[0][2]);
}
*/
$res_k   = array();
$field_k = array();
$rows_k  = array();
$jyuki_gen_kin = 0;
$sum1 = '3401';
$sum2 = '70';
$query_k = sprintf("select rep_cri, rep_de, rep_cr from financial_report_cal where rep_ymd=%d and rep_summary1='%s' and rep_summary2='%s'", $nk_ki, $sum1, $sum2);
if ($rows_k=getResultWithField2($query_k, $field_k, $res_k) <= 0) {
    $jyuki_gen_kishu = 0;
} else {
    $jyuki_gen_kishu = $res_k[0][0];
}
$res   = array();
$field = array();
$rows  = array();
$query = sprintf("select SUM(rep_de), SUM(rep_cr) from financial_report_month where rep_ymd>=%d and rep_ymd<=%d and rep_summary1='%s' and rep_summary2='%s'", $str_ym, $end_ym, $sum1, $sum2);
if ($rows=getResultWithField2($query, $field, $res) <= 0) {
    $jyuki_gen_kin = $jyuki_gen_kishu;
} else {
    $jyuki_gen_kin = -($jyuki_gen_kishu + ($res[0][0] - $res[0][1]));
}

// 什器備品資産金額
$jyuki_shisan_kin = $jyuki_kin - $jyuki_gen_kin;
// 工具器具及び備品資産金額
$jyubihin_all_shisan_kin = $kigu_shisan_kin + $jyuki_shisan_kin;

//eca用 資産金額工具器具備品
$kikougu_shutoku_kin = $kigu_kin + $jyuki_kin;
//eca用 減価償却累計額(工具器具備品)
$kikougu_rui_kin = -($kigu_gen_kin + $jyuki_gen_kin);

// リース資産2110 00
/*
$res   = array();
$field = array();
$rows  = array();
$lease_kin = 0;
$sum1 = '2110';
$sum2 = '00';
$query = sprintf("select rep_cri, rep_de, rep_cr from financial_report_cal where rep_ymd=%d and rep_summary1='%s' and rep_summary2='%s'", $yyyymm, $sum1, $sum2);
if ($rows=getResultWithField2($query, $field, $res) <= 0) {
    $lease_kin = 0;
} else {
    $lease_kin = $res[0][0] + $res[0][1] - $res[0][2];
}
*/
$res_k   = array();
$field_k = array();
$rows_k  = array();
$lease_kin = 0;
$sum1 = '2110';
$sum2 = '00';
$query_k = sprintf("select rep_cri, rep_de, rep_cr from financial_report_cal where rep_ymd=%d and rep_summary1='%s' and rep_summary2='%s'", $nk_ki, $sum1, $sum2);
if ($rows_k=getResultWithField2($query_k, $field_k, $res_k) <= 0) {
    $lease_kishu = 0;
} else {
    $lease_kishu = $res_k[0][0];
}
$res   = array();
$field = array();
$rows  = array();
$query = sprintf("select SUM(rep_de), SUM(rep_cr) from financial_report_month where rep_ymd>=%d and rep_ymd<=%d and rep_summary1='%s'", $str_ym, $end_ym, $sum1);
if ($rows=getResultWithField2($query, $field, $res) <= 0) {
    $lease_kin = $lease_kishu;
} else {
    $lease_kin = $lease_kishu + ($res[0][0] - $res[0][1]);
}

// リース資産減累額3401 80
/*
$res   = array();
$field = array();
$rows  = array();
$lease_gen_kin = 0;
$sum1 = '3401';
$sum2 = '80';
$query = sprintf("select rep_cri, rep_de, rep_cr from financial_report_cal where rep_ymd=%d and rep_summary1='%s' and rep_summary2='%s'", $yyyymm, $sum1, $sum2);
if ($rows=getResultWithField2($query, $field, $res) <= 0) {
    $lease_gen_kin = 0;
} else {
    $lease_gen_kin = -($res[0][0] + $res[0][1] - $res[0][2]);
}
*/
$res_k   = array();
$field_k = array();
$rows_k  = array();
$lease_gen_kin = 0;
$sum1 = '3401';
$sum2 = '80';
$query_k = sprintf("select rep_cri, rep_de, rep_cr from financial_report_cal where rep_ymd=%d and rep_summary1='%s' and rep_summary2='%s'", $nk_ki, $sum1, $sum2);
if ($rows_k=getResultWithField2($query_k, $field_k, $res_k) <= 0) {
    $lease_gen_kishu = 0;
} else {
    $lease_gen_kishu = $res_k[0][0];
}
$res   = array();
$field = array();
$rows  = array();
$query = sprintf("select SUM(rep_de), SUM(rep_cr) from financial_report_month where rep_ymd>=%d and rep_ymd<=%d and rep_summary1='%s' and rep_summary2='%s'", $str_ym, $end_ym, $sum1, $sum2);
if ($rows=getResultWithField2($query, $field, $res) <= 0) {
    $lease_gen_kin = $lease_gen_kishu;
} else {
    $lease_gen_kin = -($lease_gen_kishu + ($res[0][0] - $res[0][1]));
}

// リース資産資産金額
$lease_shisan_kin = $lease_kin - $lease_gen_kin;

// 減価償却累計額合計
$gensyo_total_kin = $tate_gen_kin + $fuzoku_gen_kin + $kouchiku_gen_kin + $kikai_gen_kin + $sharyo_gen_kin + $kigu_gen_kin + $jyuki_gen_kin + $lease_gen_kin;
$gensyo_total_mi_kin = - $gensyo_total_kin;

// 固定資産簿価金額計
$boka_totai_kin = $tate_shisan_kin + $fuzoku_shisan_kin + $kouchiku_shisan_kin + $kikai_shisan_kin + $sharyo_shisan_kin + $kigu_shisan_kin + $jyuki_shisan_kin + $lease_shisan_kin;

//// 無形固定資産
// 電話加入権2207 00
/*
$res   = array();
$field = array();
$rows  = array();
$denwa_kin = 0;
$sum1 = '2207';
$sum2 = '00';
$query = sprintf("select rep_cri, rep_de, rep_cr from financial_report_cal where rep_ymd=%d and rep_summary1='%s' and rep_summary2='%s'", $yyyymm, $sum1, $sum2);
if ($rows=getResultWithField2($query, $field, $res) <= 0) {
    $denwa_kin = 0;
} else {
    $denwa_kin = $res[0][0] + $res[0][1] - $res[0][2];
}
*/
$res_k   = array();
$field_k = array();
$rows_k  = array();
$denwa_kin = 0;
$sum1 = '2207';
$sum2 = '00';
$query_k = sprintf("select rep_cri, rep_de, rep_cr from financial_report_cal where rep_ymd=%d and rep_summary1='%s' and rep_summary2='%s'", $nk_ki, $sum1, $sum2);
if ($rows_k=getResultWithField2($query_k, $field_k, $res_k) <= 0) {
    $denwa_kishu = 0;
} else {
    $denwa_kishu = $res_k[0][0];
}
$res   = array();
$field = array();
$rows  = array();
$query = sprintf("select SUM(rep_de), SUM(rep_cr) from financial_report_month where rep_ymd>=%d and rep_ymd<=%d and rep_summary1='%s'", $str_ym, $end_ym, $sum1);
if ($rows=getResultWithField2($query, $field, $res) <= 0) {
    $denwa_kin = $denwa_kishu;
} else {
    $denwa_kin = $denwa_kishu + ($res[0][0] - $res[0][1]);
}


//// 2018/10/10 18/09から繰延税金資産はまとめて
// 繰延税金資産
$res   = array();
$field = array();
$rows  = array();
$ryu_kurizei_shisan_kin = 0;
$note = '流動繰延税金資産';
$query = sprintf("SELECT kin FROM profit_loss_bs_history WHERE pl_bs_ym=%d and note='%s'", $yyyymm, $note);
if ($rows=getResultWithField2($query, $field, $res) <= 0) {
    $ryu_kurizei_shisan_kin = 0;
} else {
    $ryu_kurizei_shisan_kin = $res[0][0];
}
// 繰延税金資産
$res   = array();
$field = array();
$rows  = array();
$kotei_kuri_zei_kin = 0;
$note = '固定繰延税金資産';
$query = sprintf("SELECT kin FROM profit_loss_bs_history WHERE pl_bs_ym=%d and note='%s'", $yyyymm, $note);
if ($rows=getResultWithField2($query, $field, $res) <= 0) {
    $kotei_kuri_zei_kin = 0;
} else {
    $kotei_kuri_zei_kin = $res[0][0];
}
$kotei_kuri_zei_kin = $kotei_kuri_zei_kin + $ryu_kurizei_shisan_kin;

// 繰延税金資産 増減
// 流動資産
$res_k   = array();
$field_k = array();
$rows_k  = array();
$sum1 = '1702';
$sum2 = '00';
$query_k = sprintf("select rep_cri, rep_de, rep_cr from financial_report_cal where rep_ymd=%d and rep_summary1='%s' and rep_summary2='%s'", $nk_ki, $sum1, $sum2);
if ($rows_k=getResultWithField2($query_k, $field_k, $res_k) <= 0) {
    $ryudo_kurizei_kishu = 0;
} else {
    $ryudo_kurizei_kishu = $res_k[0][0];
}
// 繰延税金資産 増減計算
$res   = array();
$field = array();
$rows  = array();
$query = sprintf("select SUM(rep_de), SUM(rep_cr) from financial_report_month where rep_ymd>=%d and rep_ymd<=%d and rep_summary1='%s'", $str_ym, $end_ym, $sum1);
if ($rows=getResultWithField2($query, $field, $res) <= 0) {
    $ryudo_kurizei_kin_zou = 0;
    $ryudo_kurizei_kin_gen = 0;
} else {
    $ryudo_kurizei_kin_zou = $res[0][0];
    $ryudo_kurizei_kin_gen = $res[0][1];
    
    if ($ryudo_kurizei_kin_zou >= $ryudo_kurizei_kin_gen) {
        $ryudo_kurizei_kin_zou = $ryudo_kurizei_kin_zou - $ryudo_kurizei_kin_gen;
        $ryudo_kurizei_kin_gen = 0;
    } else {
        $ryudo_kurizei_kin_gen = $ryudo_kurizei_kin_gen - $ryudo_kurizei_kin_zou;
        $ryudo_kurizei_kin_zou = 0;
    }
}

// 固定資産
$res_k   = array();
$field_k = array();
$rows_k  = array();
$sum1 = '2312';
$sum2 = '00';
$query_k = sprintf("select rep_cri, rep_de, rep_cr from financial_report_cal where rep_ymd=%d and rep_summary1='%s' and rep_summary2='%s'", $nk_ki, $sum1, $sum2);
if ($rows_k=getResultWithField2($query_k, $field_k, $res_k) <= 0) {
    $kotei_kurizei_kishu = 0;
} else {
    $kotei_kurizei_kishu = $res_k[0][0];
}

$kotei_kurizei_kishu = $kotei_kurizei_kishu + $ryudo_kurizei_kishu;

// 繰延税金資産 増減計算
$res   = array();
$field = array();
$rows  = array();
$query = sprintf("select SUM(rep_de), SUM(rep_cr) from financial_report_month where rep_ymd>=%d and rep_ymd<=%d and rep_summary1='%s'", $str_ym, $end_ym, $sum1);
if ($rows=getResultWithField2($query, $field, $res) <= 0) {
    $kotei_kurizei_kin_zou = 0;
    $kotei_kurizei_kin_gen = 0;
} else {
    $kotei_kurizei_kin_zou = $res[0][0];
    $kotei_kurizei_kin_gen = $res[0][1];
    
    if ($kotei_kurizei_kin_zou >= $kotei_kurizei_kin_gen) {
        $kotei_kurizei_kin_zou = $kotei_kurizei_kin_zou - $kotei_kurizei_kin_gen;
        $kotei_kurizei_kin_gen = 0;
    } else {
        $kotei_kurizei_kin_gen = $kotei_kurizei_kin_gen - $kotei_kurizei_kin_zou;
        $kotei_kurizei_kin_zou = 0;
    }
}

$kotei_kurizei_kin_zou = $kotei_kurizei_kin_zou + $ryudo_kurizei_kin_zou;
$kotei_kurizei_kin_gen = $kotei_kurizei_kin_gen + $ryudo_kurizei_kin_gen;

// 長期貸付金
$res   = array();
$field = array();
$rows  = array();
$choki_kashi_kin = 0;
$note = '長期貸付金';
$query = sprintf("SELECT kin FROM profit_loss_bs_history WHERE pl_bs_ym=%d and note='%s'", $yyyymm, $note);
if ($rows=getResultWithField2($query, $field, $res) <= 0) {
    $choki_kashi_kin = 0;
} else {
    $choki_kashi_kin = $res[0][0];
}

// 従業員長期貸付金 増減
$res_k   = array();
$field_k = array();
$rows_k  = array();
$sum1 = '2303';
$sum2 = '20';
$query_k = sprintf("select rep_cri, rep_de, rep_cr from financial_report_cal where rep_ymd=%d and rep_summary1='%s' and rep_summary2='%s'", $nk_ki, $sum1, $sum2);
if ($rows_k=getResultWithField2($query_k, $field_k, $res_k) <= 0) {
    $jyu_kashi_kishu = 0;
} else {
    $jyu_kashi_kishu = $res_k[0][0];
}
$res   = array();
$field = array();
$rows  = array();
$query = sprintf("select SUM(rep_de), SUM(rep_cr) from financial_report_month where rep_ymd>=%d and rep_ymd<=%d and rep_summary1='%s'", $str_ym, $end_ym, $sum1);
if ($rows=getResultWithField2($query, $field, $res) <= 0) {
    $jyu_kashi_kin_zou = 0;
    $jyu_kashi_kin_gen = 0;
} else {
    $jyu_kashi_kin_zou = $res[0][0];
    $jyu_kashi_kin_gen = $res[0][1];
}

// 長期前払費用
$res   = array();
$field = array();
$rows  = array();
$choki_maebara_kin = 0;
$note = '長期前払費用';
$query = sprintf("SELECT kin FROM profit_loss_bs_history WHERE pl_bs_ym=%d and note='%s'", $yyyymm, $note);
if ($rows=getResultWithField2($query, $field, $res) <= 0) {
    $choki_maebara_kin = 0;
} else {
    $choki_maebara_kin = $res[0][0];
}
$res_k   = array();
$field_k = array();
$rows_k  = array();
$sum1 = '2308';
$query_k = sprintf("select SUM(rep_cri), SUM(rep_de), SUM(rep_cr) from financial_report_cal where rep_ymd=%d and rep_summary1='%s'", $nk_ki, $sum1);
if ($rows_k=getResultWithField2($query_k, $field_k, $res_k) <= 0) {
    $choki_maebara_kishu = 0;
} else {
    $choki_maebara_kishu = $res_k[0][0];
}
$res   = array();
$field = array();
$rows  = array();
$query = sprintf("select SUM(rep_de), SUM(rep_cr) from financial_report_month where rep_ymd>=%d and rep_ymd<=%d and rep_summary1='%s'", $str_ym, $end_ym, $sum1);
if ($rows=getResultWithField2($query, $field, $res) <= 0) {
    $choki_maebara_kin_zou = 0;
    $choki_maebara_kin_gen = 0;
} else {
    $choki_maebara_kin_zou = $res[0][0];
    $choki_maebara_kin_gen = $res[0][1];
}

///// 投資等
/*
// 出資金2301 00
$res   = array();
$field = array();
$rows  = array();
$shussi_kin = 0;
$sum1 = '2301';
$sum2 = '00';
$query = sprintf("select rep_cri, rep_de, rep_cr from financial_report_cal where rep_ymd=%d and rep_summary1='%s' and rep_summary2='%s'", $yyyymm, $sum1, $sum2);
if ($rows=getResultWithField2($query, $field, $res) <= 0) {
    $shussi_kin = 0;
} else {
    $shussi_kin = $res[0][0] + $res[0][1] - $res[0][2];
}
*/
$res_k   = array();
$field_k = array();
$rows_k  = array();
$shussi_kin = 0;
$sum1 = '2301';
$sum2 = '00';
$query_k = sprintf("select rep_cri, rep_de, rep_cr from financial_report_cal where rep_ymd=%d and rep_summary1='%s' and rep_summary2='%s'", $nk_ki, $sum1, $sum2);
if ($rows_k=getResultWithField2($query_k, $field_k, $res_k) <= 0) {
    $shussi_kishu = 0;
} else {
    $shussi_kishu = $res_k[0][0];
}
$res   = array();
$field = array();
$rows  = array();
$query = sprintf("select SUM(rep_de), SUM(rep_cr) from financial_report_month where rep_ymd>=%d and rep_ymd<=%d and rep_summary1='%s'", $str_ym, $end_ym, $sum1);
if ($rows=getResultWithField2($query, $field, $res) <= 0) {
    $shussi_kin = $shussi_kishu;
} else {
    $shussi_kin = $shussi_kishu + ($res[0][0] - $res[0][1]);
}
// 差入敷金保証金2302 00
/*
$res   = array();
$field = array();
$rows  = array();
$hosyo_kin = 0;
$sum1 = '2302';
$sum2 = '00';
$query = sprintf("select rep_cri, rep_de, rep_cr from financial_report_cal where rep_ymd=%d and rep_summary1='%s' and rep_summary2='%s'", $yyyymm, $sum1, $sum2);
if ($rows=getResultWithField2($query, $field, $res) <= 0) {
    $hosyo_kin = 0;
} else {
    $hosyo_kin = $res[0][0] + $res[0][1] - $res[0][2];
}
*/
$res_k   = array();
$field_k = array();
$rows_k  = array();
$hosyo_kin = 0;
$sum1 = '2302';
$sum2 = '00';
$query_k = sprintf("select rep_cri, rep_de, rep_cr from financial_report_cal where rep_ymd=%d and rep_summary1='%s' and rep_summary2='%s'", $nk_ki, $sum1, $sum2);
if ($rows_k=getResultWithField2($query_k, $field_k, $res_k) <= 0) {
    $hosyo_kishu = 0;
} else {
    $hosyo_kishu = $res_k[0][0];
}
$res   = array();
$field = array();
$rows  = array();
$query = sprintf("select SUM(rep_de), SUM(rep_cr) from financial_report_month where rep_ymd>=%d and rep_ymd<=%d and rep_summary1='%s'", $str_ym, $end_ym, $sum1);
if ($rows=getResultWithField2($query, $field, $res) <= 0) {
    $hosyo_kin = $hosyo_kishu;
} else {
    $hosyo_kin = $hosyo_kishu + ($res[0][0] - $res[0][1]);
}

// 投資等合計の計算
$toushi_total_kin    = $shussi_kin + $hosyo_kin;

///// 流動負債１
// 仮払消費税等1508 00
/*
$res   = array();
$field = array();
$rows  = array();
$kari00_kin = 0;
$sum1 = '1508';
$sum2 = '00';
$query = sprintf("select rep_cri, rep_de, rep_cr from financial_report_cal where rep_ymd=%d and rep_summary1='%s' and rep_summary2='%s'", $yyyymm, $sum1, $sum2);
if ($rows=getResultWithField2($query, $field, $res) <= 0) {
    $kari00_kin = 0;
} else {
    $kari00_kin = $res[0][0] + $res[0][1] - $res[0][2];
}
*/
$res_k   = array();
$field_k = array();
$rows_k  = array();
$kari00_kin = 0;
$sum1 = '1508';
$sum2 = '00';
$query_k = sprintf("select rep_cri, rep_de, rep_cr from financial_report_cal where rep_ymd=%d and rep_summary1='%s' and rep_summary2='%s'", $nk_ki, $sum1, $sum2);
if ($rows_k=getResultWithField2($query_k, $field_k, $res_k) <= 0) {
    $kari00_kishu = 0;
} else {
    $kari00_kishu = $res_k[0][0];
}
$res   = array();
$field = array();
$rows  = array();
$query = sprintf("select SUM(rep_de), SUM(rep_cr) from financial_report_month where rep_ymd>=%d and rep_ymd<=%d and rep_summary1='%s'", $str_ym, $end_ym, $sum1);
if ($rows=getResultWithField2($query, $field, $res) <= 0) {
    $kari00_kin = $kari00_kishu;
} else {
    $kari00_kin = $kari00_kishu + ($res[0][0] - $res[0][1]);
}

// 仮払消費税等(輸入)1508 20
/*
$res   = array();
$field = array();
$rows  = array();
$kari20_kin = 0;
$sum1 = '1508';
$sum2 = '20';
$query = sprintf("select rep_cri, rep_de, rep_cr from financial_report_cal where rep_ymd=%d and rep_summary1='%s' and rep_summary2='%s'", $yyyymm, $sum1, $sum2);
if ($rows=getResultWithField2($query, $field, $res) <= 0) {
    $kari20_kin = 0;
} else {
    $kari20_kin = $res[0][0] + $res[0][1] - $res[0][2];
}
*/
$res_k   = array();
$field_k = array();
$rows_k  = array();
$kari20_kin = 0;
$sum1 = '1508';
$sum2 = '20';
$query_k = sprintf("select rep_cri, rep_de, rep_cr from financial_report_cal where rep_ymd=%d and rep_summary1='%s' and rep_summary2='%s'", $nk_ki, $sum1, $sum2);
if ($rows_k=getResultWithField2($query_k, $field_k, $res_k) <= 0) {
    $kari20_kishu = 0;
} else {
    $kari20_kishu = $res_k[0][0];
}
$res   = array();
$field = array();
$rows  = array();
$query = sprintf("select SUM(rep_de), SUM(rep_cr) from financial_report_month where rep_ymd>=%d and rep_ymd<=%d and rep_summary1='%s' and rep_summary2='%s'", $str_ym, $end_ym, $sum1, $sum2);
if ($rows=getResultWithField2($query, $field, $res) <= 0) {
    $kari20_kin = $kari20_kishu;
} else {
    $kari20_kin = $kari20_kishu + ($res[0][0] - $res[0][1]);
}

// 仮払消費税等の合計(取得時に合計している為いらなかった)
$kari_zei_kin = - $kari00_kin;

// 前払消費税等1560 00
/*
$res   = array();
$field = array();
$rows  = array();
$mae_zei_kin = 0;
$sum1 = '1560';
$sum2 = '00';
$query = sprintf("select rep_cri, rep_de, rep_cr from financial_report_cal where rep_ymd=%d and rep_summary1='%s' and rep_summary2='%s'", $yyyymm, $sum1, $sum2);
if ($rows=getResultWithField2($query, $field, $res) <= 0) {
    $mae_zei_kin = 0;
} else {
    $mae_zei_kin = $res[0][0] + $res[0][1] - $res[0][2];
}
*/
$res_k   = array();
$field_k = array();
$rows_k  = array();
$mae_zei_kin = 0;
$sum1 = '1560';
$sum2 = '00';
$query_k = sprintf("select rep_cri, rep_de, rep_cr from financial_report_cal where rep_ymd=%d and rep_summary1='%s' and rep_summary2='%s'", $nk_ki, $sum1, $sum2);
if ($rows_k=getResultWithField2($query_k, $field_k, $res_k) <= 0) {
    $mae_zei_kishu = 0;
} else {
    $mae_zei_kishu = $res_k[0][0];
}
$res   = array();
$field = array();
$rows  = array();
$query = sprintf("select SUM(rep_de), SUM(rep_cr) from financial_report_month where rep_ymd>=%d and rep_ymd<=%d and rep_summary1='%s' and rep_summary2='%s'", $str_ym, $end_ym, $sum1, $sum2);
if ($rows=getResultWithField2($query, $field, $res) <= 0) {
    $mae_zei_kin = $mae_zei_kishu;
} else {
    $mae_zei_kin = -($mae_zei_kishu + ($res[0][0] - $res[0][1]));
}

// 仮受消費税等3227 00
/*
$res   = array();
$field = array();
$rows  = array();
$kariuke_zei_kin = 0;
$sum1 = '3227';
$sum2 = '00';
$query = sprintf("select rep_cri, rep_de, rep_cr from financial_report_cal where rep_ymd=%d and rep_summary1='%s' and rep_summary2='%s'", $yyyymm, $sum1, $sum2);
if ($rows=getResultWithField2($query, $field, $res) <= 0) {
    $kariuke_zei_kin = 0;
} else {
    $kariuke_zei_kin = -($res[0][0] + $res[0][1] - $res[0][2]);
}
*/
$res_k   = array();
$field_k = array();
$rows_k  = array();
$kariuke_zei_kin = 0;
$sum1 = '3227';
$sum2 = '00';
$query_k = sprintf("select rep_cri, rep_de, rep_cr from financial_report_cal where rep_ymd=%d and rep_summary1='%s' and rep_summary2='%s'", $nk_ki, $sum1, $sum2);
if ($rows_k=getResultWithField2($query_k, $field_k, $res_k) <= 0) {
    $kariuke_zei_kishu = 0;
} else {
    $kariuke_zei_kishu = $res_k[0][0];
}
$res   = array();
$field = array();
$rows  = array();
$query = sprintf("select SUM(rep_de), SUM(rep_cr) from financial_report_month where rep_ymd>=%d and rep_ymd<=%d and rep_summary1='%s' and rep_summary2='%s'", $str_ym, $end_ym, $sum1, $sum2);
if ($rows=getResultWithField2($query, $field, $res) <= 0) {
    $kariuke_zei_kin = $kariuke_zei_kishu;
} else {
    $kariuke_zei_kin = -($kariuke_zei_kishu + ($res[0][0] - $res[0][1]));
}

// 未払消費税等3228 00
/*
$res   = array();
$field = array();
$rows  = array();
$miharai_zei_kin = 0;
$sum1 = '3228';
$sum2 = '00';
$query = sprintf("select rep_cri, rep_de, rep_cr from financial_report_cal where rep_ymd=%d and rep_summary1='%s' and rep_summary2='%s'", $yyyymm, $sum1, $sum2);
if ($rows=getResultWithField2($query, $field, $res) <= 0) {
    $miharai_zei_kin = 0;
} else {
    $miharai_zei_kin = -($res[0][0] + $res[0][1] - $res[0][2]);
}
*/
$res_k   = array();
$field_k = array();
$rows_k  = array();
$miharai_zei_kin = 0;
$sum1 = '3228';
$sum2 = '00';
$query_k = sprintf("select rep_cri, rep_de, rep_cr from financial_report_cal where rep_ymd=%d and rep_summary1='%s' and rep_summary2='%s'", $nk_ki, $sum1, $sum2);
if ($rows_k=getResultWithField2($query_k, $field_k, $res_k) <= 0) {
    $miharai_zei_kishu = 0;
} else {
    $miharai_zei_kishu = $res_k[0][0];
}
$res   = array();
$field = array();
$rows  = array();
$query = sprintf("select SUM(rep_de), SUM(rep_cr) from financial_report_month where rep_ymd>=%d and rep_ymd<=%d and rep_summary1='%s' and rep_summary2='%s'", $str_ym, $end_ym, $sum1, $sum2);
if ($rows=getResultWithField2($query, $field, $res) <= 0) {
    $miharai_zei_kin = $miharai_zei_kishu;
} else {
    $miharai_zei_kin = -($miharai_zei_kishu + ($res[0][0] - $res[0][1]));
}

// 未払消費税等の合計の計算
$mihazei_total_kin = $kari_zei_kin + $mae_zei_kin + $kariuke_zei_kin + $miharai_zei_kin;

// 未払消費税等の内訳 計算
///// 前期末 年月の算出
$end_yyyy = substr($end_ym, 0,4);
$end_mm   = substr($end_ym, 4,2);

if ($end_mm == 3) {                     // 3月の場合9月と合算
    // 仮払消費税計算 最後マイナス
    // 仮払消費税等
    $res_k   = array();
    $field_k = array();
    $rows_k  = array();
    $sum1 = '1508';
    $sum2 = '00';
    $sum3 = '34';
    $query_k = sprintf("select SUM(rep_cri) from financial_report_cal where rep_ymd>=%d and rep_ymd<=%d and rep_summary1='%s' and rep_summary2='%s' and rep_gin='%s'", $str_ym, $end_ym, $sum1, $sum2, $sum3);
    if ($rows_k=getResultWithField2($query_k, $field_k, $res_k) <= 0) {
        $karibara_zei_kin = 0;
    } else {
        $karibara_zei_kin = $res_k[0][0];
    }
    // 仮払消費税等（輸入）
    $res_k   = array();
    $field_k = array();
    $rows_k  = array();
    $sum1 = '1508';
    $sum2 = '20';
    $sum3 = '34';
    $query_k = sprintf("select SUM(rep_cri) from financial_report_cal where rep_ymd>=%d and rep_ymd<=%d and rep_summary1='%s' and rep_summary2='%s' and rep_gin='%s'", $str_ym, $end_ym, $sum1, $sum2, $sum3);
    if ($rows_k=getResultWithField2($query_k, $field_k, $res_k) <= 0) {
        $karibara_yunyu_zei_kin = 0;
    } else {
        $karibara_yunyu_zei_kin = $res_k[0][0];
    }
    // 未払消費税等（中間納付分）
    $res_k   = array();
    $field_k = array();
    $rows_k  = array();
    $sum1 = '1560';
    $sum2 = '00';
    $sum3 = '34';
    $query_k = sprintf("select SUM(rep_cri) from financial_report_cal where rep_ymd>=%d and rep_ymd<=%d and rep_summary1='%s' and rep_summary2='%s' and rep_gin='%s'", $str_ym, $end_ym, $sum1, $sum2, $sum3);
    if ($rows_k=getResultWithField2($query_k, $field_k, $res_k) <= 0) {
        $mae_sho_zei_kin = 0;
    } else {
        $mae_sho_zei_kin = $res_k[0][0];
    }
    // 仮払消費税 合計
    $karibara_zei_total = -($karibara_zei_kin+$karibara_yunyu_zei_kin+$mae_sho_zei_kin);
    
    // 仮受消費税計算
    // 仮受消費税等
    $res_k   = array();
    $field_k = array();
    $rows_k  = array();
    $sum1 = '3227';
    $sum2 = '00';
    $sum3 = '34';
    $query_k = sprintf("select SUM(rep_cri) from financial_report_cal where rep_ymd>=%d and rep_ymd<=%d and rep_summary1='%s' and rep_summary2='%s' and rep_gin='%s'", $str_ym, $end_ym, $sum1, $sum2, $sum3);
    if ($rows_k=getResultWithField2($query_k, $field_k, $res_k) <= 0) {
        $kariuke_sho_zei_kin = 0;
    } else {
        $kariuke_sho_zei_kin = $res_k[0][0];
    }
    
} elseif ($end_mm == 9) {           // 9月の場合9月のみ
    // 仮払消費税計算 最後マイナス
    // 仮払消費税等
    $res_k   = array();
    $field_k = array();
    $rows_k  = array();
    $sum1 = '1508';
    $sum2 = '00';
    $sum3 = '34';
    $query_k = sprintf("select SUM(rep_cri) from financial_report_cal where rep_ymd>=%d and rep_ymd<=%d and rep_summary1='%s' and rep_summary2='%s' and rep_gin='%s'", $str_ym, $end_ym, $sum1, $sum2, $sum3);
    if ($rows_k=getResultWithField2($query_k, $field_k, $res_k) <= 0) {
        $karibara_zei_kin = 0;
    } else {
        $karibara_zei_kin = $res_k[0][0];
    }
    // 仮払消費税等（輸入）
    $res_k   = array();
    $field_k = array();
    $rows_k  = array();
    $sum1 = '1508';
    $sum2 = '20';
    $sum3 = '34';
    $query_k = sprintf("select SUM(rep_cri) from financial_report_cal where rep_ymd>=%d and rep_ymd<=%d and rep_summary1='%s' and rep_summary2='%s' and rep_gin='%s'", $str_ym, $end_ym, $sum1, $sum2, $sum3);
    if ($rows_k=getResultWithField2($query_k, $field_k, $res_k) <= 0) {
        $karibara_yunyu_zei_kin = 0;
    } else {
        $karibara_yunyu_zei_kin = $res_k[0][0];
    }
    // 前払消費税等（中間納付分）
    $res_k   = array();
    $field_k = array();
    $rows_k  = array();
    $sum1 = '1560';
    $sum2 = '00';
    $sum3 = '34';
    $query_k = sprintf("select SUM(rep_cri) from financial_report_cal where rep_ymd>=%d and rep_ymd<=%d and rep_summary1='%s' and rep_summary2='%s' and rep_gin='%s'", $str_ym, $end_ym, $sum1, $sum2, $sum3);
    if ($rows_k=getResultWithField2($query_k, $field_k, $res_k) <= 0) {
        $mae_sho_zei_kin = 0;
    } else {
        $mae_sho_zei_kin = $res_k[0][0];
    }
    // 仮払消費税 合計
    $karibara_zei_total = -($karibara_zei_kin+$karibara_yunyu_zei_kin+$mae_sho_zei_kin);
    
    // 仮受消費税計算
    // 仮受消費税等
    $res_k   = array();
    $field_k = array();
    $rows_k  = array();
    $sum1 = '3227';
    $sum2 = '00';
    $sum3 = '34';
    $query_k = sprintf("select SUM(rep_cri) from financial_report_cal where rep_ymd>=%d and rep_ymd<=%d and rep_summary1='%s' and rep_summary2='%s' and rep_gin='%s'", $str_ym, $end_ym, $sum1, $sum2, $sum3);
    if ($rows_k=getResultWithField2($query_k, $field_k, $res_k) <= 0) {
        $kariuke_sho_zei_kin = 0;
    } else {
        $kariuke_sho_zei_kin = $res_k[0][0];
    }
}  elseif ($end_mm == 12) {           // 12月の場合 9月分と10～12月分の合計
    // 下期年月
    $ss_str_ym = $end_yyyy . '10';
    // 仮払消費税計算 最後マイナス
    // 仮払消費税等 9月分
    $res_k   = array();
    $field_k = array();
    $rows_k  = array();
    $sum1 = '1508';
    $sum2 = '00';
    $sum3 = '34';
    $query_k = sprintf("select SUM(rep_cri) from financial_report_cal where rep_ymd>=%d and rep_ymd<=%d and rep_summary1='%s' and rep_summary2='%s' and rep_gin='%s'", $str_ym, $end_ym, $sum1, $sum2, $sum3);
    if ($rows_k=getResultWithField2($query_k, $field_k, $res_k) <= 0) {
        $karibara_zei_kin = 0;
    } else {
        $karibara_zei_kin = $res_k[0][0];
    }
    // 仮払消費税等 10～12月分
    $res   = array();
    $field = array();
    $rows  = array();
    $query = sprintf("select SUM(rep_de), SUM(rep_cr) from financial_report_month where rep_ymd>=%d and rep_ymd<=%d and rep_summary1='%s' and rep_summary2='%s'", $ss_str_ym, $end_ym, $sum1, $sum2);
    if ($rows=getResultWithField2($query, $field, $res) <= 0) {
        $karibara_zei_kin = $karibara_zei_kin;
    } else {
        $karibara_zei_kin = $karibara_zei_kin + ($res[0][0] - $res[0][1]);
    }
    // 仮払消費税等（輸入）
    $res_k   = array();
    $field_k = array();
    $rows_k  = array();
    $sum1 = '1508';
    $sum2 = '20';
    $sum3 = '34';
    $query_k = sprintf("select SUM(rep_cri) from financial_report_cal where rep_ymd>=%d and rep_ymd<=%d and rep_summary1='%s' and rep_summary2='%s' and rep_gin='%s'", $str_ym, $end_ym, $sum1, $sum2, $sum3);
    if ($rows_k=getResultWithField2($query_k, $field_k, $res_k) <= 0) {
        $karibara_yunyu_zei_kin = 0;
    } else {
        $karibara_yunyu_zei_kin = $res_k[0][0];
    }
    // 仮払消費税等（輸入） 10～12月分
    $res   = array();
    $field = array();
    $rows  = array();
    $query = sprintf("select SUM(rep_de), SUM(rep_cr) from financial_report_month where rep_ymd>=%d and rep_ymd<=%d and rep_summary1='%s' and rep_summary2='%s'", $ss_str_ym, $end_ym, $sum1, $sum2);
    if ($rows=getResultWithField2($query, $field, $res) <= 0) {
        $karibara_yunyu_zei_kin = $karibara_yunyu_zei_kin;
    } else {
        $karibara_yunyu_zei_kin = $karibara_yunyu_zei_kin + ($res[0][0] - $res[0][1]);
    }
    // 前払消費税等（中間納付分）
    $res_k   = array();
    $field_k = array();
    $rows_k  = array();
    $sum1 = '1560';
    $sum2 = '00';
    $sum3 = '34';
    $query_k = sprintf("select SUM(rep_cri) from financial_report_cal where rep_ymd>=%d and rep_ymd<=%d and rep_summary1='%s' and rep_summary2='%s' and rep_gin='%s'", $str_ym, $end_ym, $sum1, $sum2, $sum3);
    if ($rows_k=getResultWithField2($query_k, $field_k, $res_k) <= 0) {
        $mae_sho_zei_kin = 0;
    } else {
        $mae_sho_zei_kin = $res_k[0][0];
    }
    // 前払消費税等（中間納付分） 10～12月分
    $res   = array();
    $field = array();
    $rows  = array();
    $query = sprintf("select SUM(rep_de), SUM(rep_cr) from financial_report_month where rep_ymd>=%d and rep_ymd<=%d and rep_summary1='%s' and rep_summary2='%s'", $ss_str_ym, $end_ym, $sum1, $sum2);
    if ($rows=getResultWithField2($query, $field, $res) <= 0) {
        $mae_sho_zei_kin = $mae_sho_zei_kin;
    } else {
        $mae_sho_zei_kin = $mae_sho_zei_kin + ($res[0][0] - $res[0][1]);
    }
    // 仮払消費税 合計
    $karibara_zei_total = -($karibara_zei_kin+$karibara_yunyu_zei_kin+$mae_sho_zei_kin);
    
    // 仮受消費税計算
    // 仮受消費税等
    $res_k   = array();
    $field_k = array();
    $rows_k  = array();
    $sum1 = '3227';
    $sum2 = '00';
    $sum3 = '34';
    $query_k = sprintf("select SUM(rep_cri) from financial_report_cal where rep_ymd>=%d and rep_ymd<=%d and rep_summary1='%s' and rep_summary2='%s' and rep_gin='%s'", $str_ym, $end_ym, $sum1, $sum2, $sum3);
    if ($rows_k=getResultWithField2($query_k, $field_k, $res_k) <= 0) {
        $kariuke_sho_zei_kin = 0;
    } else {
        $kariuke_sho_zei_kin = $res_k[0][0];
    }
    // 仮受消費税等 10～12月分
    $res   = array();
    $field = array();
    $rows  = array();
    $query = sprintf("select SUM(rep_de), SUM(rep_cr) from financial_report_month where rep_ymd>=%d and rep_ymd<=%d and rep_summary1='%s' and rep_summary2='%s'", $ss_str_ym, $end_ym, $sum1, $sum2);
    if ($rows=getResultWithField2($query, $field, $res) <= 0) {
        $kariuke_sho_zei_kin = $kariuke_sho_zei_kin;
    } else {
        $kariuke_sho_zei_kin = $kariuke_sho_zei_kin + ($res[0][1] - $res[0][0]);
    }
} else {                            // 6は計算方法が違う
    $karibara_zei_kin       = $kari00_kin;
    //$karibara_yunyu_zei_kin = $kari20_kin;
    $mae_sho_zei_kin        = $mae_zei_kin;
    
    $karibara_zei_total     = -($karibara_zei_kin+$mae_sho_zei_kin);
    
    $kariuke_sho_zei_kin    = $kariuke_zei_kin;
}


///// 流動負債２
/*
// 買掛金3103 00
$res   = array();
$field = array();
$rows  = array();
$kaikake_kin = 0;
$sum1 = '3103';
$sum2 = '00';
$query = sprintf("select rep_cri, rep_de, rep_cr from financial_report_cal where rep_ymd=%d and rep_summary1='%s' and rep_summary2='%s'", $yyyymm, $sum1, $sum2);
if ($rows=getResultWithField2($query, $field, $res) <= 0) {
    $kaikake_kin = 0;
} else {
    $kaikake_kin = -($res[0][0] + $res[0][1] - $res[0][2]);
}
*/
$res_k   = array();
$field_k = array();
$rows_k  = array();
$kaikake_kin = 0;
$sum1 = '3103';
$sum2 = '00';
$query_k = sprintf("select rep_cri, rep_de, rep_cr from financial_report_cal where rep_ymd=%d and rep_summary1='%s' and rep_summary2='%s'", $nk_ki, $sum1, $sum2);
if ($rows_k=getResultWithField2($query_k, $field_k, $res_k) <= 0) {
    $kaikake_kishu = 0;
} else {
    $kaikake_kishu = $res_k[0][0];
}
$res   = array();
$field = array();
$rows  = array();
$query = sprintf("select SUM(rep_de), SUM(rep_cr) from financial_report_month where rep_ymd>=%d and rep_ymd<=%d and rep_summary1='%s' and rep_summary2='%s'", $str_ym, $end_ym, $sum1, $sum2);
if ($rows=getResultWithField2($query, $field, $res) <= 0) {
    $kaikake_kin = $kaikake_kishu;
} else {
    $kaikake_kin = -($kaikake_kishu + ($res[0][0] - $res[0][1]));
}

// 買掛金期日振込3102 00
/*
$res   = array();
$field = array();
$rows  = array();
$kaikake_kiji_kin = 0;
$sum1 = '3102';
$sum2 = '00';
$query = sprintf("select rep_cri, rep_de, rep_cr from financial_report_cal where rep_ymd=%d and rep_summary1='%s' and rep_summary2='%s'", $yyyymm, $sum1, $sum2);
if ($rows=getResultWithField2($query, $field, $res) <= 0) {
    $kaikake_kiji_kin = 0;
} else {
    $kaikake_kiji_kin = -($res[0][0] + $res[0][1] - $res[0][2]);
}
*/
$res_k   = array();
$field_k = array();
$rows_k  = array();
$kaikake_kiji_kin = 0;
$sum1 = '3102';
$sum2 = '00';
$query_k = sprintf("select rep_cri, rep_de, rep_cr from financial_report_cal where rep_ymd=%d and rep_summary1='%s' and rep_summary2='%s'", $nk_ki, $sum1, $sum2);
if ($rows_k=getResultWithField2($query_k, $field_k, $res_k) <= 0) {
    $kaikake_kiji_kishu = 0;
} else {
    $kaikake_kiji_kishu = $res_k[0][0];
}
$res   = array();
$field = array();
$rows  = array();
$query = sprintf("select SUM(rep_de), SUM(rep_cr) from financial_report_month where rep_ymd>=%d and rep_ymd<=%d and rep_summary1='%s' and rep_summary2='%s'", $str_ym, $end_ym, $sum1, $sum2);
if ($rows=getResultWithField2($query, $field, $res) <= 0) {
    $kaikake_kiji_kin = $kaikake_kiji_kishu;
} else {
    $kaikake_kiji_kin = -($kaikake_kiji_kishu + ($res[0][0] - $res[0][1]));
}

// 買掛金の合計の計算
$kaikake_total_kin = $kaikake_kin + $kaikake_kiji_kin;

// 買掛金の内訳 TOP10の取得
$kaikake_top     = array();
$kaikake_top_kin = 0;
for ($i = 1; $i < 11; $i++) {
    $res_k   = array();
    $field_k = array();
    $rows_k  = array();
    $sum1 = '01';
    $sum2 = $i;
    $query_k = sprintf("select rep_summary1,rep_cri from financial_report_cal where rep_ymd=%d and rep_summary2='%s' and rep_gin='%s'", $end_ym, $sum1, $sum2);
    if ($rows_k=getResultWithField2($query_k, $field_k, $res_k) <= 0) {
        $kaikake_top_code = 0;
    } else {
        $kaikake_top_code    = $res_k[0][0];
        $kaikake_top[$i][2]  = $res_k[0][1];       // 金額
    
        $res_m   = array();
        $field_m = array();
        $rows_m  = array();
        $query_m = sprintf("select name, address1, address2 from vendor_master WHERE vendor='%s'", $kaikake_top_code);
        if ($rows_m=getResultWithField2($query_m, $field_m, $res_m) <= 0) {
            $kaikake_top[$i][0]    = '';    // 発注先名
            $kaikake_top[$i][1]    = '';    // 住所
        } else {
            $kaikake_top[$i][0]    = $res_m[0][0];
            if ($kaikake_top_code=='01298') {
                $res_m[0][1] = '栃木県宇都宮市';
            } elseif ($kaikake_top_code=='01299') {
                $res_m[0][1] = '茨城県日立市';
            } elseif ($kaikake_top_code=='00958') {
                $res_m[0][1] = '東京都文京区';
            } elseif ($kaikake_top_code=='00642') {
                $res_m[0][1] = '千葉県船橋市';
            }
            $kaikake_top[$i][1]    = preg_replace("/( |　)/", "", $res_m[0][1] . $res_m[0][2]);
        }
    }
    $kaikake_top_kin = $kaikake_top_kin + $kaikake_top[$i][2];
}

// 買掛金の内訳 その他計算
$kaikake_top_sonota_kin = $kaikake_total_kin - $kaikake_top_kin;

///// 流動負債３
// 未払金3105 00
/*
$res   = array();
$field = array();
$rows  = array();
$miharai_kin = 0;
$sum1 = '3105';
$sum2 = '00';
$query = sprintf("select rep_cri, rep_de, rep_cr from financial_report_cal where rep_ymd=%d and rep_summary1='%s' and rep_summary2='%s'", $yyyymm, $sum1, $sum2);
if ($rows=getResultWithField2($query, $field, $res) <= 0) {
    $miharai_kin = 0;
} else {
    $miharai_kin = -($res[0][0] + $res[0][1] - $res[0][2]);
}
*/
$res_k   = array();
$field_k = array();
$rows_k  = array();
$miharai_kin = 0;
$sum1 = '3105';
$sum2 = '00';
$query_k = sprintf("select rep_cri, rep_de, rep_cr from financial_report_cal where rep_ymd=%d and rep_summary1='%s' and rep_summary2='%s'", $nk_ki, $sum1, $sum2);
if ($rows_k=getResultWithField2($query_k, $field_k, $res_k) <= 0) {
    $miharai_kishu = 0;
} else {
    $miharai_kishu = $res_k[0][0];
}
$res   = array();
$field = array();
$rows  = array();
$query = sprintf("select SUM(rep_de), SUM(rep_cr) from financial_report_month where rep_ymd>=%d and rep_ymd<=%d and rep_summary1='%s' and rep_summary2='%s'", $str_ym, $end_ym, $sum1, $sum2);
if ($rows=getResultWithField2($query, $field, $res) <= 0) {
    $miharai_kin = $miharai_kishu;
} else {
    $miharai_kin = -($miharai_kishu + ($res[0][0] - $res[0][1]));
}

// 未払金期日指定3106 00
/*
$res   = array();
$field = array();
$rows  = array();
$miharai_kiji_kin = 0;
$sum1 = '3106';
$sum2 = '00';
$query = sprintf("select rep_cri, rep_de, rep_cr from financial_report_cal where rep_ymd=%d and rep_summary1='%s' and rep_summary2='%s'", $yyyymm, $sum1, $sum2);
if ($rows=getResultWithField2($query, $field, $res) <= 0) {
    $miharai_kiji_kin = 0;
} else {
    $miharai_kiji_kin = -($res[0][0] + $res[0][1] - $res[0][2]);
}
*/
$res_k   = array();
$field_k = array();
$rows_k  = array();
$miharai_kiji_kin = 0;
$sum1 = '3106';
$sum2 = '00';
$query_k = sprintf("select rep_cri, rep_de, rep_cr from financial_report_cal where rep_ymd=%d and rep_summary1='%s' and rep_summary2='%s'", $nk_ki, $sum1, $sum2);
if ($rows_k=getResultWithField2($query_k, $field_k, $res_k) <= 0) {
    $miharai_kiji_kishu = 0;
} else {
    $miharai_kiji_kishu = $res_k[0][0];
}
$res   = array();
$field = array();
$rows  = array();
$query = sprintf("select SUM(rep_de), SUM(rep_cr) from financial_report_month where rep_ymd>=%d and rep_ymd<=%d and rep_summary1='%s' and rep_summary2='%s'", $str_ym, $end_ym, $sum1, $sum2);
if ($rows=getResultWithField2($query, $field, $res) <= 0) {
    $miharai_kiji_kin = $miharai_kiji_kishu;
} else {
    $miharai_kiji_kin = -($miharai_kiji_kishu + ($res[0][0] - $res[0][1]));
}

// 未払金の合計の計算
$miharai_total_kin = $miharai_kin + $miharai_kiji_kin;

// 未払金の内訳 TOP10の取得
$miharai_top     = array();
$miharai_top_kin = 0;
for ($i = 1; $i < 11; $i++) {
    $res_k   = array();
    $field_k = array();
    $rows_k  = array();
    $sum1 = 'MIHAR';
    $sum2 = $i;
    $query_k = sprintf("select rep_summary2,rep_gin,rep_cri from financial_report_cal where rep_ymd=%d and rep_summary1='%s' and rep_de=%d", $end_ym, $sum1, $sum2);
    if ($rows_k=getResultWithField2($query_k, $field_k, $res_k) <= 0) {
        $miharai_top[$i][0] = '';
        $miharai_top[$i][1] = '';
        $miharai_top[$i][2] = 0;
        
    } else {
        $miharai_top[$i][0]  = $res_k[0][0];
        $miharai_top[$i][1]  = $res_k[0][1];
        $miharai_top[$i][2]  = $res_k[0][2];
    
        if ($miharai_top[$i][0]=='株式会社伸和商事') {
            $miharai_top[$i][1] = '栃木県鹿沼市貝島町４５６';
        } elseif ($miharai_top[$i][0]=='辰巳産業（株）北関東営業所') {
            $miharai_top[$i][1] = '群馬県館林市諏訪町１３７９';
        } elseif ($miharai_top[$i][0]=='晋豊建設（株）') {
            $miharai_top[$i][1] = '栃木県宇都宮市屋板町５７８番地３７８';
        } elseif ($miharai_top[$i][0]=='（株）ウエノ宇都宮支店') {
            $miharai_top[$i][1] = '栃木県宇都宮市鶴田町１３３７－３';
        } elseif ($miharai_top[$i][0]=='株式会社山善ＳＦＳ営業本部') {
            $miharai_top[$i][1] = '東京都港区港南2-16-2太陽生命品川ビル12階';
        } elseif ($miharai_top[$i][0]=='株式会社ミスミ') {
            $miharai_top[$i][1] = '東京都文京区後楽2-5-1飯田橋ファーストビル';
        } elseif ($miharai_top[$i][0]=='三信電工株式会社') {
            $miharai_top[$i][1] = '栃木県宇都宮市川俣町１０５６';
        } elseif ($miharai_top[$i][0]=='株式会社キ－エンス') {
            $miharai_top[$i][1] = '大阪府大阪市東淀川区東中島１丁目３番１４号';
        } elseif ($miharai_top[$i][0]=='空圧工業（株）') {
            $miharai_top[$i][1] = '神奈川県横浜市鶴見区駒岡２－６－２７';
        } elseif ($miharai_top[$i][0]=='株式会社水戸設備工業') {
            $miharai_top[$i][1] = '栃木県宇都宮市御幸ヶ原町１４３－４７';
        } elseif ($miharai_top[$i][0]=='（有）小林石油商会') {
            $miharai_top[$i][1] = '栃木県さくら市氏家１８８４';
        } elseif ($miharai_top[$i][0]=='山田マシンツール株式会社') {
            $miharai_top[$i][1] = '東京都台東区台東１丁目２３－６';
        } elseif ($miharai_top[$i][0]=='（株）ナショナルマシナリーアジア') {
            $miharai_top[$i][1] = '愛知県春日井市堀ノ内町２－１１－１６';
        } elseif ($miharai_top[$i][0]=='株式会社協立電気') {
            $miharai_top[$i][1] = '栃木県さくら市氏家２５５８';
        } elseif ($miharai_top[$i][0]=='（有）横川紙工業') {
            $miharai_top[$i][1] = '栃木県宇都宮市中島町４３５－１';
        } elseif ($miharai_top[$i][0]=='株式会社東鋼') {
            $miharai_top[$i][1] = '東京都文京区本郷５丁目２７番１０号';
        } elseif ($miharai_top[$i][0]=='有限会社桜井農園') {
            $miharai_top[$i][0] = '株式会社ミスミ';
            $miharai_top[$i][1] = '東京都文京区後楽2-5-1飯田橋ファーストビル';
        }
    }
    $miharai_top_kin = $miharai_top_kin + $miharai_top[$i][2];
}

// 未払金の内訳 その他計算
$miharai_top_sonota_kin = $miharai_total_kin - $miharai_top_kin;

///// 流動負債４
// 前受金3221 00
/*
$res   = array();
$field = array();
$rows  = array();
$maeuke_kin = 0;
$sum1 = '3221';
$sum2 = '00';
$query = sprintf("select rep_cri, rep_de, rep_cr from financial_report_cal where rep_ymd=%d and rep_summary1='%s' and rep_summary2='%s'", $yyyymm, $sum1, $sum2);
if ($rows=getResultWithField2($query, $field, $res) <= 0) {
    $maeuke_kin = 0;
} else {
    $maeuke_kin = -($res[0][0] + $res[0][1] - $res[0][2]);
}
*/
$res_k   = array();
$field_k = array();
$rows_k  = array();
$maeuke_kin = 0;
$sum1 = '3221';
$sum2 = '00';
$query_k = sprintf("select rep_cri, rep_de, rep_cr from financial_report_cal where rep_ymd=%d and rep_summary1='%s' and rep_summary2='%s'", $nk_ki, $sum1, $sum2);
if ($rows_k=getResultWithField2($query_k, $field_k, $res_k) <= 0) {
    $maeuke_kishu = 0;
} else {
    $maeuke_kishu = $res_k[0][0];
}
$res   = array();
$field = array();
$rows  = array();
$query = sprintf("select SUM(rep_de), SUM(rep_cr) from financial_report_month where rep_ymd>=%d and rep_ymd<=%d and rep_summary1='%s' and rep_summary2='%s'", $str_ym, $end_ym, $sum1, $sum2);
if ($rows=getResultWithField2($query, $field, $res) <= 0) {
    $maeuke_kin = $maeuke_kishu;
} else {
    $maeuke_kin = -($maeuke_kishu + ($res[0][0] - $res[0][1]));
}

// その他流動負債3229 00
/*
$res   = array();
$field = array();
$rows  = array();
$sonota_ryudo_kin = 0;
$sum1 = '3229';
$sum2 = '00';
$query = sprintf("select rep_cri, rep_de, rep_cr from financial_report_cal where rep_ymd=%d and rep_summary1='%s' and rep_summary2='%s'", $yyyymm, $sum1, $sum2);
if ($rows=getResultWithField2($query, $field, $res) <= 0) {
    $sonota_ryudo_kin = 0;
} else {
    $sonota_ryudo_kin = -($res[0][0] + $res[0][1] - $res[0][2]);
}
*/
$res_k   = array();
$field_k = array();
$rows_k  = array();
$sonota_ryudo_kin = 0;
$sum1 = '3229';
$sum2 = '00';
$query_k = sprintf("select rep_cri, rep_de, rep_cr from financial_report_cal where rep_ymd=%d and rep_summary1='%s' and rep_summary2='%s'", $nk_ki, $sum1, $sum2);
if ($rows_k=getResultWithField2($query_k, $field_k, $res_k) <= 0) {
    $sonota_ryudo_kishu = 0;
} else {
    $sonota_ryudo_kishu = $res_k[0][0];
}
$res   = array();
$field = array();
$rows  = array();
$query = sprintf("select SUM(rep_de), SUM(rep_cr) from financial_report_month where rep_ymd>=%d and rep_ymd<=%d and rep_summary1='%s' and rep_summary2='%s'", $str_ym, $end_ym, $sum1, $sum2);
if ($rows=getResultWithField2($query, $field, $res) <= 0) {
    $sonota_ryudo_kin = $sonota_ryudo_kishu;
} else {
    $sonota_ryudo_kin = -($sonota_ryudo_kishu + ($res[0][0] - $res[0][1]));
}

// その他流動負債の合計の計算
$sonota_ryudo_total_kin = $maeuke_kin + $sonota_ryudo_kin;
// 流動負債の合計の計算（流動負債２～４の合計）
$ryudo_fusai_total_kin = $kaikake_total_kin + $miharai_total_kin + $sonota_ryudo_total_kin;

// 預り金の計算
// 源泉所得税
$res_k   = array();
$field_k = array();
$rows_k  = array();
$sum1 = '3222';
$sum2 = '11';
$query_k = sprintf("select rep_cri, rep_de, rep_cr from financial_report_cal where rep_ymd=%d and rep_summary1='%s' and rep_summary2='%s'", $nk_ki, $sum1, $sum2);
if ($rows_k=getResultWithField2($query_k, $field_k, $res_k) <= 0) {
    $gen_shotoku_kishu = 0;
} else {
    $gen_shotoku_kishu = $res_k[0][0];
}
$res   = array();
$field = array();
$rows  = array();
$query = sprintf("select SUM(rep_de), SUM(rep_cr) from financial_report_month where rep_ymd>=%d and rep_ymd<=%d and rep_summary1='%s' and rep_summary2='%s'", $str_ym, $end_ym, $sum1, $sum2);
if ($rows=getResultWithField2($query, $field, $res) <= 0) {
    $gen_shotoku_kin = $gen_shotoku_kishu;
} else {
    $gen_shotoku_kin = -($gen_shotoku_kishu + ($res[0][0] - $res[0][1]));
}

// 源泉住民税
$res_k   = array();
$field_k = array();
$rows_k  = array();
$sum1 = '3222';
$sum2 = '12';
$query_k = sprintf("select rep_cri, rep_de, rep_cr from financial_report_cal where rep_ymd=%d and rep_summary1='%s' and rep_summary2='%s'", $nk_ki, $sum1, $sum2);
if ($rows_k=getResultWithField2($query_k, $field_k, $res_k) <= 0) {
    $gen_jyu_kishu = 0;
} else {
    $gen_jyu_kishu = $res_k[0][0];
}
$res   = array();
$field = array();
$rows  = array();
$query = sprintf("select SUM(rep_de), SUM(rep_cr) from financial_report_month where rep_ymd>=%d and rep_ymd<=%d and rep_summary1='%s' and rep_summary2='%s'", $str_ym, $end_ym, $sum1, $sum2);
if ($rows=getResultWithField2($query, $field, $res) <= 0) {
    $gen_jyu_kin = $gen_jyu_kishu;
} else {
    $gen_jyu_kin = -($gen_jyu_kishu + ($res[0][0] - $res[0][1]));
}

// 健康保険料
$res_k   = array();
$field_k = array();
$rows_k  = array();
$sum1 = '3222';
$sum2 = '21';
$query_k = sprintf("select rep_cri, rep_de, rep_cr from financial_report_cal where rep_ymd=%d and rep_summary1='%s' and rep_summary2='%s'", $nk_ki, $sum1, $sum2);
if ($rows_k=getResultWithField2($query_k, $field_k, $res_k) <= 0) {
    $ken_hoken_kishu = 0;
} else {
    $ken_hoken_kishu = $res_k[0][0];
}
$res   = array();
$field = array();
$rows  = array();
$query = sprintf("select SUM(rep_de), SUM(rep_cr) from financial_report_month where rep_ymd>=%d and rep_ymd<=%d and rep_summary1='%s' and rep_summary2='%s'", $str_ym, $end_ym, $sum1, $sum2);
if ($rows=getResultWithField2($query, $field, $res) <= 0) {
    $ken_hoken_kin = $ken_hoken_kishu;
} else {
    $ken_hoken_kin = -($ken_hoken_kishu + ($res[0][0] - $res[0][1]));
}

// 厚生年金保険料
$res_k   = array();
$field_k = array();
$rows_k  = array();
$sum1 = '3222';
$sum2 = '22';
$query_k = sprintf("select rep_cri, rep_de, rep_cr from financial_report_cal where rep_ymd=%d and rep_summary1='%s' and rep_summary2='%s'", $nk_ki, $sum1, $sum2);
if ($rows_k=getResultWithField2($query_k, $field_k, $res_k) <= 0) {
    $kou_hoken_kishu = 0;
} else {
    $kou_hoken_kishu = $res_k[0][0];
}
$res   = array();
$field = array();
$rows  = array();
$query = sprintf("select SUM(rep_de), SUM(rep_cr) from financial_report_month where rep_ymd>=%d and rep_ymd<=%d and rep_summary1='%s' and rep_summary2='%s'", $str_ym, $end_ym, $sum1, $sum2);
if ($rows=getResultWithField2($query, $field, $res) <= 0) {
    $kou_hoken_kin = $kou_hoken_kishu;
} else {
    $kou_hoken_kin = -($kou_hoken_kishu + ($res[0][0] - $res[0][1]));
}

// 預り金 その他
$res_k   = array();
$field_k = array();
$rows_k  = array();
$sum1 = '3222';
$sum2 = '90';
$query_k = sprintf("select rep_cri, rep_de, rep_cr from financial_report_cal where rep_ymd=%d and rep_summary1='%s' and rep_summary2='%s'", $nk_ki, $sum1, $sum2);
if ($rows_k=getResultWithField2($query_k, $field_k, $res_k) <= 0) {
    $azu_sonota_kishu = 0;
} else {
    $azu_sonota_kishu = $res_k[0][0];
}
$res   = array();
$field = array();
$rows  = array();
$query = sprintf("select SUM(rep_de), SUM(rep_cr) from financial_report_month where rep_ymd>=%d and rep_ymd<=%d and rep_summary1='%s' and rep_summary2='%s'", $str_ym, $end_ym, $sum1, $sum2);
if ($rows=getResultWithField2($query, $field, $res) <= 0) {
    $azu_sonota_kin = $azu_sonota_kishu;
} else {
    $azu_sonota_kin = -($azu_sonota_kishu + ($res[0][0] - $res[0][1]));
}

///// 損益計算書の計算
///// 製造原価報告書
// 期首棚卸
// 製品1404 00
/*
$res   = array();
$field = array();
$rows  = array();
$z_seihin_kin = 0;
$sum1 = '1404';
$sum2 = '00';
$query = sprintf("select rep_cri, rep_de, rep_cr from financial_report_cal where rep_ymd=%d and rep_summary1='%s' and rep_summary2='%s'", $b_yyyymm, $sum1, $sum2);
if ($rows=getResultWithField2($query, $field, $res) <= 0) {
    $z_seihin_kin = 0;
} else {
    $z_seihin_kin = $res[0][0] + $res[0][1] - $res[0][2];
}
*/
$res_k   = array();
$field_k = array();
$rows_k  = array();
$z_seihin_kin = 0;
$sum1 = '1404';
$sum2 = '00';
$query_k = sprintf("select rep_cri, rep_de, rep_cr from financial_report_cal where rep_ymd=%d and rep_summary1='%s' and rep_summary2='%s'", $nk_ki, $sum1, $sum2);
if ($rows_k=getResultWithField2($query_k, $field_k, $res_k) <= 0) {
    $z_seihin_kin = 0;
} else {
    $z_seihin_kin = $res_k[0][0];
}

// 製品仕掛品1405 00
/*
$res   = array();
$field = array();
$rows  = array();
$z_seihinsi_kin = 0;
$sum1 = '1405';
$sum2 = '00';
$query = sprintf("select rep_cri, rep_de, rep_cr from financial_report_cal where rep_ymd=%d and rep_summary1='%s' and rep_summary2='%s'", $b_yyyymm, $sum1, $sum2);
if ($rows=getResultWithField2($query, $field, $res) <= 0) {
    $z_seihinsi_kin = 0;
} else {
    $z_seihinsi_kin = $res[0][0] + $res[0][1] - $res[0][2];
}
*/
$res_k   = array();
$field_k = array();
$rows_k  = array();
$z_seihinsi_kin = 0;
$sum1 = '1405';
$sum2 = '00';
$query_k = sprintf("select rep_cri, rep_de, rep_cr from financial_report_cal where rep_ymd=%d and rep_summary1='%s' and rep_summary2='%s'", $nk_ki, $sum1, $sum2);
if ($rows_k=getResultWithField2($query_k, $field_k, $res_k) <= 0) {
    $z_seihinsi_kin = 0;
} else {
    $z_seihinsi_kin = $res_k[0][0];
}

// 部品1406 00
/*
$res   = array();
$field = array();
$rows  = array();
$z_buhin_kin = 0;
$sum1 = '1406';
$sum2 = '00';
$query = sprintf("select rep_cri, rep_de, rep_cr from financial_report_cal where rep_ymd=%d and rep_summary1='%s' and rep_summary2='%s'", $b_yyyymm, $sum1, $sum2);
if ($rows=getResultWithField2($query, $field, $res) <= 0) {
    $z_buhin_kin = 0;
} else {
    $z_buhin_kin = $res[0][0] + $res[0][1] - $res[0][2];
}
*/
$res_k   = array();
$field_k = array();
$rows_k  = array();
$z_buhin_kin = 0;
$sum1 = '1406';
$sum2 = '00';
$query_k = sprintf("select rep_cri, rep_de, rep_cr from financial_report_cal where rep_ymd=%d and rep_summary1='%s' and rep_summary2='%s'", $nk_ki, $sum1, $sum2);
if ($rows_k=getResultWithField2($query_k, $field_k, $res_k) <= 0) {
    $z_buhin_kin = 0;
} else {
    $z_buhin_kin = $res_k[0][0];
}

// 部品仕掛品1407 30
/*
$res   = array();
$field = array();
$rows  = array();
$z_buhinsi_kin = 0;
$sum1 = '1407';
$sum2 = '30';
$query = sprintf("select rep_cri, rep_de, rep_cr from financial_report_cal where rep_ymd=%d and rep_summary1='%s' and rep_summary2='%s'", $b_yyyymm, $sum1, $sum2);
if ($rows=getResultWithField2($query, $field, $res) <= 0) {
    $z_buhinsi_kin = 0;
} else {
    $z_buhinsi_kin = $res[0][0] + $res[0][1] - $res[0][2];
}
*/
$res_k   = array();
$field_k = array();
$rows_k  = array();
$z_buhinsi_kin = 0;
$sum1 = '1407';
$sum2 = '30';
$query_k = sprintf("select rep_cri, rep_de, rep_cr from financial_report_cal where rep_ymd=%d and rep_summary1='%s' and rep_summary2='%s'", $nk_ki, $sum1, $sum2);
if ($rows_k=getResultWithField2($query_k, $field_k, $res_k) <= 0) {
    $z_buhinsi_kin = 0;
} else {
    $z_buhinsi_kin = $res_k[0][0];
}

// 原材料1408 00
/*
$res   = array();
$field = array();
$rows  = array();
$z_genzai_kin = 0;
$sum1 = '1408';
$sum2 = '00';
$query = sprintf("select rep_cri, rep_de, rep_cr from financial_report_cal where rep_ymd=%d and rep_summary1='%s' and rep_summary2='%s'", $b_yyyymm, $sum1, $sum2);
if ($rows=getResultWithField2($query, $field, $res) <= 0) {
    $z_genzai_kin = 0;
} else {
    $z_genzai_kin = $res[0][0] + $res[0][1] - $res[0][2];
}
*/
$res_k   = array();
$field_k = array();
$rows_k  = array();
$z_genzai_kin = 0;
$sum1 = '1408';
$sum2 = '00';
$query_k = sprintf("select rep_cri, rep_de, rep_cr from financial_report_cal where rep_ymd=%d and rep_summary1='%s' and rep_summary2='%s'", $nk_ki, $sum1, $sum2);
if ($rows_k=getResultWithField2($query_k, $field_k, $res_k) <= 0) {
    $z_genzai_kin = 0;
} else {
    $z_genzai_kin = $res_k[0][0];
}

// その他の棚卸品1409 の合計
/*
$res   = array();
$field = array();
$rows  = array();
$z_sonotatana_kin = 0;
$sum1 = '1409';
$sum2 = '00';
$query = sprintf("select SUM(rep_cri), SUM(rep_de), SUM(rep_cr) from financial_report_cal where rep_ymd=%d and rep_summary1='%s'", $b_yyyymm, $sum1);
if ($rows=getResultWithField2($query, $field, $res) <= 0) {
    $z_sonotatana_kin = 0;
} else {
    $z_sonotatana_kin = $res[0][0] + $res[0][1] - $res[0][2];
}
*/
$res_k   = array();
$field_k = array();
$rows_k  = array();
$z_sonotatana_kin = 0;
$sum1 = '1409';
$sum2 = '00';
$query_k = sprintf("select SUM(rep_cri), SUM(rep_de), SUM(rep_cr) from financial_report_cal where rep_ymd=%d and rep_summary1='%s'", $nk_ki, $sum1);
if ($rows_k=getResultWithField2($query_k, $field_k, $res_k) <= 0) {
    $z_sonotatana_kin = 0;
} else {
    $z_sonotatana_kin = $res_k[0][0];
}

// 在庫合計の計算
$z_zaiko_total_kin    = $z_seihin_kin + $z_seihinsi_kin + $z_buhin_kin + $z_buhinsi_kin + $z_genzai_kin + $z_sonotatana_kin;
// 仕掛品合計の計算
$z_sikakari_total_kin = $z_seihinsi_kin;
// 原材料及び貯蔵品合計の計算
$z_gencho_total_kin   = $z_buhin_kin + $z_buhinsi_kin + $z_genzai_kin + $z_sonotatana_kin;

//// 期末棚卸高
// 在庫合計の計算
$kimatsu_total_kin  = $seihin_kin + $seihinsi_kin + $buhin_kin + $buhinsi_kin + $genzai_kin + $sonotatana_kin;
// 仕掛品合計の計算
$kimatsu_sikakari_total_kin = $seihinsi_kin;
// 原材料及び貯蔵品合計の計算
$kimatsu_gencho_total_kin   = $buhin_kin + $buhinsi_kin + $genzai_kin + $sonotatana_kin;

//// P / L   営業外損益、特別損益、他
// 営業外収益の計算
// 雑収入9103 の合計
$res   = array();
$field = array();
$rows  = array();
$zatsushu_kin = 0;
$sum1 = '9103';
$sum2 = '00';
$query = sprintf("select SUM(rep_de), SUM(rep_cr) from financial_report_month where rep_ymd>=%d and rep_ymd<=%d and rep_summary1='%s'", $str_ym, $end_ym, $sum1);
if ($rows=getResultWithField2($query, $field, $res) <= 0) {
    $zatsushu_kin = 0;
} else {
    $zatsushu_kin = $res[0][1] - $res[0][0];
}
// 業務委託収入9107 の合計
$res   = array();
$field = array();
$rows  = array();
$gyomushu_kin = 0;
$sum1 = '9107';
$sum2 = '00';
$query = sprintf("select SUM(rep_de), SUM(rep_cr) from financial_report_month where rep_ymd>=%d and rep_ymd<=%d and rep_summary1='%s'", $str_ym, $end_ym, $sum1);
if ($rows=getResultWithField2($query, $field, $res) <= 0) {
    $gyomushu_kin = 0;
} else {
    $gyomushu_kin = $res[0][1] - $res[0][0];
}

// 営業外収益合計の計算
$eigyo_shueki_total_kin = $zatsushu_kin + $gyomushu_kin;

// 営業外費用の計算
// その他営業外費用9310 の合計
$res   = array();
$field = array();
$rows  = array();
$sonota_eihiyo_kin = 0;
$sum1 = '9310';
$sum2 = '00';
$query = sprintf("select SUM(rep_de), SUM(rep_cr) from financial_report_month where rep_ymd>=%d and rep_ymd<=%d and rep_summary1='%s'", $str_ym, $end_ym, $sum1);
if ($rows=getResultWithField2($query, $field, $res) <= 0) {
    $sonota_eihiyo_kin = 0;
} else {
    $sonota_eihiyo_kin = -($res[0][1] - $res[0][0]);
}
// 固定資産売却損9317 の合計
$res   = array();
$field = array();
$rows  = array();
$kotei_baison_kin = 0;
$sum1 = '9317';
$sum2 = '00';
$query = sprintf("select SUM(rep_de), SUM(rep_cr) from financial_report_month where rep_ymd>=%d and rep_ymd<=%d and rep_summary1='%s'", $str_ym, $end_ym, $sum1);
if ($rows=getResultWithField2($query, $field, $res) <= 0) {
    $kotei_baison_kin = 0;
} else {
    $kotei_baison_kin = -($res[0][1] - $res[0][0]);
}

// 固定資産除却損9311 の合計
$res   = array();
$field = array();
$rows  = array();
$kotei_jyoson_kin = 0;
$sum1 = '9311';
$sum2 = '00';
$query = sprintf("select SUM(rep_de), SUM(rep_cr) from financial_report_month where rep_ymd>=%d and rep_ymd<=%d and rep_summary1='%s'", $str_ym, $end_ym, $sum1);
if ($rows=getResultWithField2($query, $field, $res) <= 0) {
    $kotei_jyoson_kin = 0;
} else {
    $kotei_jyoson_kin = -($res[0][1] - $res[0][0]);
}

$kotei_son_total = $kotei_baison_kin + $kotei_jyoson_kin;

// 法人税等の計算
// 法人税及び住民税9401 の合計
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
// 事業税9402 の合計
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

// 法人税等調整額9405 の合計
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

// 法人税等合計の計算
$hojin_zeito_total_kin = $hojin_jyumin_zei_kin + $jigyo_zei_kin + $hojin_chosei_kin;

// 法人税・住民税及び事業税の内訳 合計の計算
$hojin_uchi_total_kin = $hojin_jyumin_zei_kin + $jigyo_zei_kin;

// eca用法人税、住民税及び事業税
$eca_hojin_zeito_total_kin = $hojin_jyumin_zei_kin + $jigyo_zei_kin;

// 法人税等（国税）預金利息等に対する源泉所得税額
$res_k   = array();
$field_k = array();
$rows_k  = array();
$sum1 = '9401';
$sum2 = '20';
$query_k = sprintf("select rep_cri, rep_de, rep_cr from financial_report_cal where rep_ymd=%d and rep_summary1='%s' and rep_summary2='%s'", $nk_ki, $sum1, $sum2);
if ($rows_k=getResultWithField2($query_k, $field_k, $res_k) <= 0) {
    $gensen_shotoku_kishu = 0;
} else {
    $gensen_shotoku_kishu = $res_k[0][0];
}
$res   = array();
$field = array();
$rows  = array();
$query = sprintf("select SUM(rep_de), SUM(rep_cr) from financial_report_month where rep_ymd>=%d and rep_ymd<=%d and rep_summary1='%s' and rep_summary2='%s'", $str_ym, $end_ym, $sum1, $sum2);
if ($rows=getResultWithField2($query, $field, $res) <= 0) {
    $gensen_shotoku_kin = $gensen_shotoku_kishu;
} else {
    $gensen_shotoku_kin = $gensen_shotoku_kishu + ($res[0][0] - $res[0][1]);
}

// 当期法人税住民税事業税引当額
$toki_hojin_jigyo = $hojin_uchi_total_kin - $gensen_shotoku_kin;

// 未払法人税等 期首
$res_k   = array();
$field_k = array();
$rows_k  = array();
$sum1 = '3211';
$sum2 = '00';
$query_k = sprintf("select rep_cri, rep_de, rep_cr from financial_report_cal where rep_ymd=%d and rep_summary1='%s' and rep_summary2='%s'", $nk_ki, $sum1, $sum2);
if ($rows_k=getResultWithField2($query_k, $field_k, $res_k) <= 0) {
    $miharai_hozei_kishu = 0;
} else {
    $miharai_hozei_kishu = -$res_k[0][0];
}

// 未払法人税等
$res   = array();
$field = array();
$rows  = array();
$miharai_hozei_kin = 0;
$note = '未払法人税等';
$query = sprintf("SELECT kin FROM profit_loss_bs_history WHERE pl_bs_ym=%d and note='%s'", $yyyymm, $note);
if ($rows=getResultWithField2($query, $field, $res) <= 0) {
    $miharai_hozei_kin = 0;
} else {
    $miharai_hozei_kin = $res[0][0];
}

$miharai_hozei_settei = $toki_hojin_jigyo;
$miharai_hozei_shiha  = $miharai_hozei_kishu + $miharai_hozei_settei - $miharai_hozei_kin;

// 未払費用
$res   = array();
$field = array();
$rows  = array();
$miharai_hiyo_kin = 0;
$note = '未払費用';
$query = sprintf("SELECT kin FROM profit_loss_bs_history WHERE pl_bs_ym=%d and note='%s'", $yyyymm, $note);
if ($rows=getResultWithField2($query, $field, $res) <= 0) {
    $miharai_hiyo_kin = 0;
} else {
    $miharai_hiyo_kin = $res[0][0];
}

// 預り金
$res   = array();
$field = array();
$rows  = array();
$azukari_kin = 0;
$note = '預り金';
$query = sprintf("SELECT kin FROM profit_loss_bs_history WHERE pl_bs_ym=%d and note='%s'", $yyyymm, $note);
if ($rows=getResultWithField2($query, $field, $res) <= 0) {
    $azukari_kin = 0;
} else {
    $azukari_kin = $res[0][0];
}

// 賞与引当金
$res   = array();
$field = array();
$rows  = array();
$syoyo_hikiate_kin = 0;
$note = '賞与引当金';
$query = sprintf("SELECT kin FROM profit_loss_bs_history WHERE pl_bs_ym=%d and note='%s'", $yyyymm, $note);
if ($rows=getResultWithField2($query, $field, $res) <= 0) {
    $syoyo_hikiate_kin = 0;
} else {
    $syoyo_hikiate_kin = $res[0][0];
}

// 退職給付引当金
$res   = array();
$field = array();
$rows  = array();
$taisyoku_hikiate_kin = 0;
$note = '退職給付引当金';
$query = sprintf("SELECT kin FROM profit_loss_bs_history WHERE pl_bs_ym=%d and note='%s'", $yyyymm, $note);
if ($rows=getResultWithField2($query, $field, $res) <= 0) {
    $taisyoku_hikiate_kin = 0;
} else {
    $taisyoku_hikiate_kin = $res[0][0];
}

// 退職給付引当金 増減
$res_k   = array();
$field_k = array();
$rows_k  = array();
$sum1 = '3302';
$sum2 = '00';
$query_k = sprintf("select rep_cri, rep_de, rep_cr from financial_report_cal where rep_ymd=%d and rep_summary1='%s' and rep_summary2='%s'", $nk_ki, $sum1, $sum2);
if ($rows_k=getResultWithField2($query_k, $field_k, $res_k) <= 0) {
    $tai_hiki_kishu = 0;
} else {
    $tai_hiki_kishu = -$res_k[0][0];
}
$res   = array();
$field = array();
$rows  = array();
$query = sprintf("select SUM(rep_de), SUM(rep_cr) from financial_report_month where rep_ymd>=%d and rep_ymd<=%d and rep_summary1='%s'", $str_ym, $end_ym, $sum1);
if ($rows=getResultWithField2($query, $field, $res) <= 0) {
    $tai_hiki_kin_zou = 0;
    $tai_hiki_kin_gen = 0;
} else {
    $tai_hiki_kin_zou = $res[0][0];
    $tai_hiki_kin_gen = $res[0][1];
}

// 退職給付引当金 目的使用
$res   = array();
$field = array();
$rows  = array();
$sum3 = '12';
$query = sprintf("select SUM(rep_cri) from financial_report_cal where rep_ymd>=%d and rep_ymd<=%d and rep_summary1='%s' and rep_gin='%s'", $str_ym, $end_ym, $sum1, $sum3);
if ($rows=getResultWithField2($query, $field, $res) <= 0) {
    $tai_hiki_kin_moku = 0;
} else {
    $tai_hiki_kin_moku = $res[0][0];
}

// 退職給付引当金 その他
$res   = array();
$field = array();
$rows  = array();
$sum3 = '12';
$query = sprintf("select SUM(rep_cri) from financial_report_cal where rep_ymd>=%d and rep_ymd<=%d and rep_summary1='%s' and rep_gin<>'%s'", $str_ym, $end_ym, $sum1, $sum3);
if ($rows=getResultWithField2($query, $field, $res) <= 0) {
    $tai_hiki_kin_sonota = 0;
} else {
    $tai_hiki_kin_sonota = $res[0][0];
}

// 資本金
$res   = array();
$field = array();
$rows  = array();
$shihon_kin = 0;
$note = '資本金';
$query = sprintf("SELECT kin FROM profit_loss_bs_history WHERE pl_bs_ym=%d and note='%s'", $yyyymm, $note);
if ($rows=getResultWithField2($query, $field, $res) <= 0) {
    $shihon_kin = 0;
} else {
    $shihon_kin = $res[0][0];
}

// 資本金計
$shihon_total_kin = $shihon_kin;


// 資本金 増減
$res_k   = array();
$field_k = array();
$rows_k  = array();
$sum1 = '4101';
$sum2 = '00';
$query_k = sprintf("select rep_cri, rep_de, rep_cr from financial_report_cal where rep_ymd=%d and rep_summary1='%s' and rep_summary2='%s'", $nk_ki, $sum1, $sum2);
if ($rows_k=getResultWithField2($query_k, $field_k, $res_k) <= 0) {
    $shihon_kin_kishu = 0;
} else {
    $shihon_kin_kishu = -$res_k[0][0];
}
$res   = array();
$field = array();
$rows  = array();
$query = sprintf("select SUM(rep_de), SUM(rep_cr) from financial_report_month where rep_ymd>=%d and rep_ymd<=%d and rep_summary1='%s'", $str_ym, $end_ym, $sum1);
if ($rows=getResultWithField2($query, $field, $res) <= 0) {
    $shihon_kin_zou = 0;
    $shihon_kin_gen = 0;
} else {
    $shihon_kin_zou = $res[0][0];
    $shihon_kin_gen = $res[0][1];
}

// 資本準備金
$res   = array();
$field = array();
$rows  = array();
$shihon_jyunbi_kin = 0;
$note = '資本準備金';
$query = sprintf("SELECT kin FROM profit_loss_bs_history WHERE pl_bs_ym=%d and note='%s'", $yyyymm, $note);
if ($rows=getResultWithField2($query, $field, $res) <= 0) {
    $shihon_jyunbi_kin = 0;
} else {
    $shihon_jyunbi_kin = $res[0][0];
}


// 資本準備金 増減
$res_k   = array();
$field_k = array();
$rows_k  = array();
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
$query = sprintf("select SUM(rep_de), SUM(rep_cr) from financial_report_month where rep_ymd>=%d and rep_ymd<=%d and rep_summary1='%s'", $str_ym, $end_ym, $sum1);
if ($rows=getResultWithField2($query, $field, $res) <= 0) {
    $shihon_jyunbi_zou = 0;
    $shihon_jyunbi_gen = 0;
} else {
    $shihon_jyunbi_zou = $res[0][0];
    $shihon_jyunbi_gen = $res[0][1];
}

// その他資本剰余金
$res   = array();
$field = array();
$rows  = array();
$sonota_shihon_jyoyo_kin = 0;
$note = 'その他資本剰余金';
$query = sprintf("SELECT kin FROM profit_loss_bs_history WHERE pl_bs_ym=%d and note='%s'", $yyyymm, $note);
if ($rows=getResultWithField2($query, $field, $res) <= 0) {
    $sonota_shihon_jyoyo_kin = 0;
} else {
    $sonota_shihon_jyoyo_kin = $res[0][0];
}

// その他資本剰余金 増減
$res_k   = array();
$field_k = array();
$rows_k  = array();
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
$query = sprintf("select SUM(rep_de), SUM(rep_cr) from financial_report_month where rep_ymd>=%d and rep_ymd<=%d and rep_summary1='%s'", $str_ym, $end_ym, $sum1);
if ($rows=getResultWithField2($query, $field, $res) <= 0) {
    $sonota_shihon_jyoyo_zou = 0;
    $sonota_shihon_jyoyo_gen = 0;
} else {
    $sonota_shihon_jyoyo_zou = $res[0][0];
    $sonota_shihon_jyoyo_gen = $res[0][1];
}

// その他利益剰余金
$res   = array();
$field = array();
$rows  = array();
$tai_sonota_rieki_jyoyo_kin = 0;
$note = 'その他利益剰余金';
$query = sprintf("SELECT kin FROM profit_loss_bs_history WHERE pl_bs_ym=%d and note='%s'", $yyyymm, $note);
if ($rows=getResultWithField2($query, $field, $res) <= 0) {
    $tai_sonota_rieki_jyoyo_kin = 0;
} else {
    $tai_sonota_rieki_jyoyo_kin = $res[0][0];
}

// その他利益剰余金 増減
$res_k   = array();
$field_k = array();
$rows_k  = array();
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
$query = sprintf("select SUM(rep_de), SUM(rep_cr) from financial_report_month where rep_ymd>=%d and rep_ymd<=%d and rep_summary1='%s'", $str_ym, $end_ym, $sum1);
if ($rows=getResultWithField2($query, $field, $res) <= 0) {
    $sonota_rieki_jyoyo_zou = 0;
    $sonota_rieki_jyoyo_gen = 0;
} else {
    $sonota_rieki_jyoyo_zou = $res[0][0];
    $sonota_rieki_jyoyo_gen = $res[0][1];
}

// 資本金及び剰余金の内訳 計
$shihon_jyoyo_total = $shihon_total_kin + $shihon_jyunbi_kin + $sonota_shihon_jyoyo_kin + $tai_sonota_rieki_jyoyo_kin;
$shihon_jyoyo_kishu = $shihon_kin_kishu + $shihon_jyunbi_kishu + $sonota_shihon_jyoyo_kishu + $sonota_rieki_jyoyo_kishu;
$shihon_jyoyo_zou   = $shihon_kin_zou + $shihon_jyunbi_zou + $sonota_shihon_jyoyo_zou + $sonota_rieki_jyoyo_zou;
$shihon_jyoyo_gen   = $shihon_kin_gen + $shihon_jyunbi_gen + $sonota_shihon_jyoyo_gen + $sonota_rieki_jyoyo_gen;

//// 経費明細書の計算
// 販管費 旅費交通費合計
// 旅費交通費
$res   = array();
$field = array();
$rows  = array();
$han_ryohi_kin = 0;
$note  = '販管費旅費交通費';
$query = sprintf("select SUM(kin) from profit_loss_keihi_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='%s'", $str_ym, $end_ym, $note);
if ($rows=getResultWithField2($query, $field, $res) <= 0) {
    $han_ryohi_kin = 0;
} else {
    $han_ryohi_kin = $res[0][0];
}
// 海外出張費
$res   = array();
$field = array();
$rows  = array();
$han_kaigai_kin = 0;
$note  = '販管費海外出張費';
$query = sprintf("select SUM(kin) from profit_loss_keihi_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='%s'", $str_ym, $end_ym, $note);
if ($rows=getResultWithField2($query, $field, $res) <= 0) {
    $han_kaigai_kin = 0;
} else {
    $han_kaigai_kin = $res[0][0];
}

// 販管費旅費交通費合計の計算
$han_ryohi_total_kin = $han_ryohi_kin + $han_kaigai_kin;

// 販管費 広告宣伝費合計
// 広告宣伝費
$res   = array();
$field = array();
$rows  = array();
$han_kokoku_kin = 0;
$note  = '販管費広告宣伝費';
$query = sprintf("select SUM(kin) from profit_loss_keihi_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='%s'", $str_ym, $end_ym, $note);
if ($rows=getResultWithField2($query, $field, $res) <= 0) {
    $han_kokoku_kin = 0;
} else {
    $han_kokoku_kin = $res[0][0];
}
// 求人費
$res   = array();
$field = array();
$rows  = array();
$han_kyujin_kin = 0;
$note  = '販管費求人費';
$query = sprintf("select SUM(kin) from profit_loss_keihi_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='%s'", $str_ym, $end_ym, $note);
if ($rows=getResultWithField2($query, $field, $res) <= 0) {
    $han_kyujin_kin = 0;
} else {
    $han_kyujin_kin = $res[0][0];
}

// 販管費広告宣伝費合計の計算
$han_kokoku_total_kin = $han_kokoku_kin + $han_kyujin_kin;

// 販管費 業務委託費合計
// 業務委託費
$res   = array();
$field = array();
$rows  = array();
$han_gyomu_kin = 0;
$note  = '販管費業務委託費';
$query = sprintf("select SUM(kin) from profit_loss_keihi_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='%s'", $str_ym, $end_ym, $note);
if ($rows=getResultWithField2($query, $field, $res) <= 0) {
    $han_gyomu_kin = 0;
} else {
    $han_gyomu_kin = $res[0][0];
}
// 支払手数料
$res   = array();
$field = array();
$rows  = array();
$han_tesu_kin = 0;
$note  = '販管費支払手数料';
$query = sprintf("select SUM(kin) from profit_loss_keihi_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='%s'", $str_ym, $end_ym, $note);
if ($rows=getResultWithField2($query, $field, $res) <= 0) {
    $han_tesu_kin = 0;
} else {
    $han_tesu_kin = $res[0][0];
}

// 販管費業務委託費合計の計算
$han_gyomu_total_kin = $han_gyomu_kin + $han_tesu_kin;

// 販管費 諸税公課合計
// 事業等
$res   = array();
$field = array();
$rows  = array();
$han_jigyo_kin = 0;
$note  = '販管費事業等';
$query = sprintf("select SUM(kin) from profit_loss_keihi_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='%s'", $str_ym, $end_ym, $note);
if ($rows=getResultWithField2($query, $field, $res) <= 0) {
    $han_jigyo_kin = 0;
} else {
    $han_jigyo_kin = $res[0][0];
}
// 諸税公課
$res   = array();
$field = array();
$rows  = array();
$han_zeikoka_kin = 0;
$note  = '販管費諸税公課';
$query = sprintf("select SUM(kin) from profit_loss_keihi_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='%s'", $str_ym, $end_ym, $note);
if ($rows=getResultWithField2($query, $field, $res) <= 0) {
    $han_zeikoka_kin = 0;
} else {
    $han_zeikoka_kin = $res[0][0];
}

// 販管費諸税公課合計の計算
$han_zeikoka_total_kin = $han_jigyo_kin + $han_zeikoka_kin;

// 諸税公課合計
// 事業等
$res   = array();
$field = array();
$rows  = array();
$han_jigyo_kin = 0;
$note  = '合計事業等';
$query = sprintf("select SUM(kin) from profit_loss_keihi_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='%s'", $str_ym, $end_ym, $note);
if ($rows=getResultWithField2($query, $field, $res) <= 0) {
    $all_jigyo_kin = 0;
} else {
    $all_jigyo_kin = $res[0][0];
}
// 諸税公課
$res   = array();
$field = array();
$rows  = array();
$han_zeikoka_kin = 0;
$note  = '合計諸税公課';
$query = sprintf("select SUM(kin) from profit_loss_keihi_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='%s'", $str_ym, $end_ym, $note);
if ($rows=getResultWithField2($query, $field, $res) <= 0) {
    $all_zeikoka_kin = 0;
} else {
    $all_zeikoka_kin = $res[0][0];
}

// 販管費諸税公課合計の計算
$all_zeikoka_total_kin = $all_jigyo_kin + $all_zeikoka_kin;

// 販管費 事務用消耗品費合計
// 事務用消耗品費
$res   = array();
$field = array();
$rows  = array();
$han_jimuyo_kin = 0;
$note  = '販管費事務用消耗品費';
$query = sprintf("select SUM(kin) from profit_loss_keihi_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='%s'", $str_ym, $end_ym, $note);
if ($rows=getResultWithField2($query, $field, $res) <= 0) {
    $han_jimuyo_kin = 0;
} else {
    $han_jimuyo_kin = $res[0][0];
}
// 工場消耗品費
$res   = array();
$field = array();
$rows  = array();
$han_kojyo_kin = 0;
$note  = '販管費工場消耗品費';
$query = sprintf("select SUM(kin) from profit_loss_keihi_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='%s'", $str_ym, $end_ym, $note);
if ($rows=getResultWithField2($query, $field, $res) <= 0) {
    $han_kojyo_kin = 0;
} else {
    $han_kojyo_kin = $res[0][0];
}

// 販管費事務用消耗品費合計の計算
$han_jimuyo_total_kin = $han_jimuyo_kin + $han_kojyo_kin;

// 販管費 雑費合計
// 雑費
$res   = array();
$field = array();
$rows  = array();
$han_zappi_kin = 0;
$note  = '販管費雑費';
$query = sprintf("select SUM(kin) from profit_loss_keihi_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='%s'", $str_ym, $end_ym, $note);
if ($rows=getResultWithField2($query, $field, $res) <= 0) {
    $han_zappi_kin = 0;
} else {
    $han_zappi_kin = $res[0][0];
}
// 保証修理費
$res   = array();
$field = array();
$rows  = array();
$han_hosyo_kin = 0;
$note  = '販管費保証修理費';
$query = sprintf("select SUM(kin) from profit_loss_keihi_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='%s'", $str_ym, $end_ym, $note);
if ($rows=getResultWithField2($query, $field, $res) <= 0) {
    $han_hosyo_kin = 0;
} else {
    $han_hosyo_kin = $res[0][0];
}
// 諸会費
$res   = array();
$field = array();
$rows  = array();
$han_kaihi_kin = 0;
$note  = '販管費諸会費';
$query = sprintf("select SUM(kin) from profit_loss_keihi_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='%s'", $str_ym, $end_ym, $note);
if ($rows=getResultWithField2($query, $field, $res) <= 0) {
    $han_kaihi_kin = 0;
} else {
    $han_kaihi_kin = $res[0][0];
}

// 販管費雑費合計の計算
$han_zappi_total_kin = $han_zappi_kin + $han_hosyo_kin + $han_kaihi_kin;

// 販管費 地代家賃合計
// 地代家賃
$res   = array();
$field = array();
$rows  = array();
$han_yachin_kin = 0;
$note  = '販管費地代家賃';
$query = sprintf("select SUM(kin) from profit_loss_keihi_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='%s'", $str_ym, $end_ym, $note);
if ($rows=getResultWithField2($query, $field, $res) <= 0) {
    $han_yachin_kin = 0;
} else {
    $han_yachin_kin = $res[0][0];
}
// 倉敷料
$res   = array();
$field = array();
$rows  = array();
$han_kura_kin = 0;
$note  = '販管費倉敷料';
$query = sprintf("select SUM(kin) from profit_loss_keihi_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='%s'", $str_ym, $end_ym, $note);
if ($rows=getResultWithField2($query, $field, $res) <= 0) {
    $han_kura_kin = 0;
} else {
    $han_kura_kin = $res[0][0];
}

// 販管費地代家賃合計の計算
$han_yachin_total_kin = $han_yachin_kin + $han_kura_kin;

// 販管費 厚生福利費合計
// 法定福利費
$res   = array();
$field = array();
$rows  = array();
$han_hofukuri_kin = 0;
$note  = '販管費法定福利費';
$query = sprintf("select SUM(kin) from profit_loss_keihi_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='%s'", $str_ym, $end_ym, $note);
if ($rows=getResultWithField2($query, $field, $res) <= 0) {
    $han_hofukuri_kin = 0;
} else {
    $han_hofukuri_kin = $res[0][0];
}
// 厚生福利費
$res   = array();
$field = array();
$rows  = array();
$han_kofukuri_kin = 0;
$note  = '販管費厚生福利費';
$query = sprintf("select SUM(kin) from profit_loss_keihi_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='%s'", $str_ym, $end_ym, $note);
if ($rows=getResultWithField2($query, $field, $res) <= 0) {
    $han_kofukuri_kin = 0;
} else {
    $han_kofukuri_kin = $res[0][0];
}

// 販管費厚生福利費合計の計算
$han_kofukuri_total_kin = $han_hofukuri_kin + $han_kofukuri_kin;

// 販管費 退職給付費用合計
// 退職給与金
$res   = array();
$field = array();
$rows  = array();
$han_taikyuyo_kin = 0;
$note  = '販管費退職給与金';
$query = sprintf("select SUM(kin) from profit_loss_keihi_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='%s'", $str_ym, $end_ym, $note);
if ($rows=getResultWithField2($query, $field, $res) <= 0) {
    $han_taikyuyo_kin = 0;
} else {
    $han_taikyuyo_kin = $res[0][0];
}
// 退職給付費用
$res   = array();
$field = array();
$rows  = array();
$han_taikyufu_kin = 0;
$note  = '販管費退職給付費用';
$query = sprintf("select SUM(kin) from profit_loss_keihi_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='%s'", $str_ym, $end_ym, $note);
if ($rows=getResultWithField2($query, $field, $res) <= 0) {
    $han_taikyufu_kin = 0;
} else {
    $han_taikyufu_kin = $res[0][0];
}

// 販管費退職給付費用合計の計算
$han_taikyufu_total_kin = $han_taikyuyo_kin + $han_taikyufu_kin;

// 製造経費 旅費交通費合計
// 旅費交通費
$res   = array();
$field = array();
$rows  = array();
$sei_ryohi_kin = 0;
$note  = '製造経費旅費交通費';
$query = sprintf("select SUM(kin) from profit_loss_keihi_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='%s'", $str_ym, $end_ym, $note);
if ($rows=getResultWithField2($query, $field, $res) <= 0) {
    $sei_ryohi_kin = 0;
} else {
    $sei_ryohi_kin = $res[0][0];
}
// 海外出張
$res   = array();
$field = array();
$rows  = array();
$sei_kaigai_kin = 0;
$note  = '製造経費海外出張';
$query = sprintf("select SUM(kin) from profit_loss_keihi_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='%s'", $str_ym, $end_ym, $note);
if ($rows=getResultWithField2($query, $field, $res) <= 0) {
    $sei_kaigai_kin = 0;
} else {
    $sei_kaigai_kin = $res[0][0];
}

// 製造経費旅費交通費合計の計算
$sei_ryohi_total_kin = $sei_ryohi_kin + $sei_kaigai_kin;

// 製造経費 業務委託費合計
// 業務委託費
$res   = array();
$field = array();
$rows  = array();
$sei_gyomu_kin = 0;
$note  = '製造経費業務委託費';
$query = sprintf("select SUM(kin) from profit_loss_keihi_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='%s'", $str_ym, $end_ym, $note);
if ($rows=getResultWithField2($query, $field, $res) <= 0) {
    $sei_gyomu_kin = 0;
} else {
    $sei_gyomu_kin = $res[0][0];
}
// 支払手数料
$res   = array();
$field = array();
$rows  = array();
$sei_tesu_kin = 0;
$note  = '製造経費支払手数料';
$query = sprintf("select SUM(kin) from profit_loss_keihi_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='%s'", $str_ym, $end_ym, $note);
if ($rows=getResultWithField2($query, $field, $res) <= 0) {
    $sei_tesu_kin = 0;
} else {
    $sei_tesu_kin = $res[0][0];
}

// 製造経費業務委託費合計の計算
$sei_gyomu_total_kin = $sei_gyomu_kin + $sei_tesu_kin;

// 製造経費 雑費合計
// 雑費
$res   = array();
$field = array();
$rows  = array();
$sei_zappi_kin = 0;
$note  = '製造経費雑費';
$query = sprintf("select SUM(kin) from profit_loss_keihi_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='%s'", $str_ym, $end_ym, $note);
if ($rows=getResultWithField2($query, $field, $res) <= 0) {
    $sei_zappi_kin = 0;
} else {
    $sei_zappi_kin = $res[0][0];
}
// 広告宣伝費
$res   = array();
$field = array();
$rows  = array();
$sei_kokoku_kin = 0;
$note  = '製造経費広告宣伝費';
$query = sprintf("select SUM(kin) from profit_loss_keihi_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='%s'", $str_ym, $end_ym, $note);
if ($rows=getResultWithField2($query, $field, $res) <= 0) {
    $sei_kokoku_kin = 0;
} else {
    $sei_kokoku_kin = $res[0][0];
}
// 求人費
$res   = array();
$field = array();
$rows  = array();
$sei_kyujin_kin = 0;
$note  = '製造経費求人費';
$query = sprintf("select SUM(kin) from profit_loss_keihi_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='%s'", $str_ym, $end_ym, $note);
if ($rows=getResultWithField2($query, $field, $res) <= 0) {
    $sei_kyujin_kin = 0;
} else {
    $sei_kyujin_kin = $res[0][0];
}
// 保証修理費
$res   = array();
$field = array();
$rows  = array();
$sei_hosyo_kin = 0;
$note  = '製造経費保証修理費';
$query = sprintf("select SUM(kin) from profit_loss_keihi_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='%s'", $str_ym, $end_ym, $note);
if ($rows=getResultWithField2($query, $field, $res) <= 0) {
    $sei_hosyo_kin = 0;
} else {
    $sei_hosyo_kin = $res[0][0];
}
// 諸会費
$res   = array();
$field = array();
$rows  = array();
$sei_kaihi_kin = 0;
$note  = '製造経費諸会費';
$query = sprintf("select SUM(kin) from profit_loss_keihi_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='%s'", $str_ym, $end_ym, $note);
if ($rows=getResultWithField2($query, $field, $res) <= 0) {
    $sei_kaihi_kin = 0;
} else {
    $sei_kaihi_kin = $res[0][0];
}

// 製造経費雑費合計の計算
$sei_zappi_total_kin = $sei_zappi_kin + $sei_kokoku_kin + $sei_kyujin_kin + $sei_hosyo_kin + $sei_kaihi_kin;

// 製造経費 地代家賃合計
// 地代家賃
$res   = array();
$field = array();
$rows  = array();
$sei_yachin_kin = 0;
$note  = '製造経費地代家賃';
$query = sprintf("select SUM(kin) from profit_loss_keihi_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='%s'", $str_ym, $end_ym, $note);
if ($rows=getResultWithField2($query, $field, $res) <= 0) {
    $sei_yachin_kin = 0;
} else {
    $sei_yachin_kin = $res[0][0];
}
// 倉敷料
$res   = array();
$field = array();
$rows  = array();
$sei_kura_kin = 0;
$note  = '製造経費倉敷料';
$query = sprintf("select SUM(kin) from profit_loss_keihi_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='%s'", $str_ym, $end_ym, $note);
if ($rows=getResultWithField2($query, $field, $res) <= 0) {
    $sei_kura_kin = 0;
} else {
    $sei_kura_kin = $res[0][0];
}

// 製造経費地代家賃合計の計算
$sei_yachin_total_kin = $sei_yachin_kin + $sei_kura_kin;

// 労務費 厚生福利費合計
// 法定福利費
$res   = array();
$field = array();
$rows  = array();
$sei_hofukuri_kin = 0;
$note  = '製造経費法定福利費';
$query = sprintf("select SUM(kin) from profit_loss_keihi_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='%s'", $str_ym, $end_ym, $note);
if ($rows=getResultWithField2($query, $field, $res) <= 0) {
    $sei_hofukuri_kin = 0;
} else {
    $sei_hofukuri_kin = $res[0][0];
}
// 厚生福利費
$res   = array();
$field = array();
$rows  = array();
$sei_kofukuri_kin = 0;
$note  = '製造経費厚生福利費';
$query = sprintf("select SUM(kin) from profit_loss_keihi_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='%s'", $str_ym, $end_ym, $note);
if ($rows=getResultWithField2($query, $field, $res) <= 0) {
    $sei_kofukuri_kin = 0;
} else {
    $sei_kofukuri_kin = $res[0][0];
}

// 労務費厚生福利費合計の計算
$sei_kofukuri_total_kin = $sei_hofukuri_kin + $sei_kofukuri_kin;

// 労務費 退職給付費用合計
// 退職給与金
$res   = array();
$field = array();
$rows  = array();
$sei_taikyuyo_kin = 0;
$note  = '製造経費退職給与金';
$query = sprintf("select SUM(kin) from profit_loss_keihi_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='%s'", $str_ym, $end_ym, $note);
if ($rows=getResultWithField2($query, $field, $res) <= 0) {
    $sei_taikyuyo_kin = 0;
} else {
    $sei_taikyuyo_kin = $res[0][0];
}
// 退職給付費用
$res   = array();
$field = array();
$rows  = array();
$sei_taikyufu_kin = 0;
$note  = '製造経費退職給付費用';
$query = sprintf("select SUM(kin) from profit_loss_keihi_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='%s'", $str_ym, $end_ym, $note);
if ($rows=getResultWithField2($query, $field, $res) <= 0) {
    $sei_taikyufu_kin = 0;
} else {
    $sei_taikyufu_kin = $res[0][0];
}

// 労務費退職給付費用合計の計算
$sei_taikyufu_total_kin = $sei_taikyuyo_kin + $sei_taikyufu_kin;

$ym4s = substr($str_ym, 2, 4);
$ym4e = substr($end_ym, 2, 4);

// 諸税公課 固定資産税
// 製造経費
$res   = array();
$field = array();
$rows  = array();
$sum1 = '7521';
$sum2 = '20';
$query = sprintf("SELECT CASE WHEN sum(act_monthly) IS NULL THEN 0 WHEN sum(act_monthly) = 0 THEN 0 ELSE sum(act_monthly) END FROM act_summary where act_yymm>=%d and act_yymm<=%d and actcod='%s' and aucod='%s'", $ym4s, $ym4e, $sum1, $sum2);
if ($rows=getResultWithField2($query, $field, $res) <= 0) {
    $kotei_zei_sei_kin = 0;
} else {
    $kotei_zei_sei_kin = $res[0][0];
}
// 販管費
$res   = array();
$field = array();
$rows  = array();
$sum1 = '7521';
$sum2 = '20';
$query = sprintf("SELECT CASE WHEN sum(act_monthly) IS NULL THEN 0 WHEN sum(act_monthly) = 0 THEN 0 ELSE sum(act_monthly) END FROM act_sga_summary where act_yymm>=%d and act_yymm<=%d and actcod='%s' and aucod='%s'", $ym4s, $ym4e, $sum1, $sum2);
if ($rows=getResultWithField2($query, $field, $res) <= 0) {
    $kotei_zei_han_kin = 0;
} else {
    $kotei_zei_han_kin = $res[0][0];
}
// 合計
$kotei_zei_total_kin = $kotei_zei_sei_kin + $kotei_zei_han_kin;

// 諸税公課 印紙税
// 製造経費
$res   = array();
$field = array();
$rows  = array();
$sum1 = '7521';
$sum2 = '10';
$query = sprintf("SELECT CASE WHEN sum(act_monthly) IS NULL THEN 0 WHEN sum(act_monthly) = 0 THEN 0 ELSE sum(act_monthly) END FROM act_summary where act_yymm>=%d and act_yymm<=%d and actcod='%s' and aucod='%s'", $ym4s, $ym4e, $sum1, $sum2);
if ($rows=getResultWithField2($query, $field, $res) <= 0) {
    $inshi_zei_sei_kin = 0;
} else {
    $inshi_zei_sei_kin = $res[0][0];
}
// 販管費
$res   = array();
$field = array();
$rows  = array();
$sum1 = '7521';
$sum2 = '10';
$query = sprintf("SELECT CASE WHEN sum(act_monthly) IS NULL THEN 0 WHEN sum(act_monthly) = 0 THEN 0 ELSE sum(act_monthly) END FROM act_sga_summary where act_yymm>=%d and act_yymm<=%d and actcod='%s' and aucod='%s'", $ym4s, $ym4e, $sum1, $sum2);
if ($rows=getResultWithField2($query, $field, $res) <= 0) {
    $inshi_zei_han_kin = 0;
} else {
    $inshi_zei_han_kin = $res[0][0];
}
// 合計
$inshi_zei_total_kin = $inshi_zei_sei_kin + $inshi_zei_han_kin;

// 諸税公課 登録免許税（その他）
// 製造経費
$res   = array();
$field = array();
$rows  = array();
$sum1 = '7521';
$sum2 = '90';
$query = sprintf("SELECT CASE WHEN sum(act_monthly) IS NULL THEN 0 WHEN sum(act_monthly) = 0 THEN 0 ELSE sum(act_monthly) END FROM act_summary where act_yymm>=%d and act_yymm<=%d and actcod='%s' and aucod='%s'", $ym4s, $ym4e, $sum1, $sum2);
if ($rows=getResultWithField2($query, $field, $res) <= 0) {
    $touroku_zei_sei_kin = 0;
} else {
    $touroku_zei_sei_kin = $res[0][0];
}
// 販管費
$res   = array();
$field = array();
$rows  = array();
$sum1 = '7521';
$sum2 = '90';
$query = sprintf("SELECT CASE WHEN sum(act_monthly) IS NULL THEN 0 WHEN sum(act_monthly) = 0 THEN 0 ELSE sum(act_monthly) END FROM act_sga_summary where act_yymm>=%d and act_yymm<=%d and actcod='%s' and aucod='%s'", $ym4s, $ym4e, $sum1, $sum2);
if ($rows=getResultWithField2($query, $field, $res) <= 0) {
    $touroku_zei_han_kin = 0;
} else {
    $touroku_zei_han_kin = $res[0][0];
}
// 合計
$touroku_zei_total_kin = $touroku_zei_sei_kin + $touroku_zei_han_kin;

// 諸税公課 合計
$shozei_sei_total = $kotei_zei_sei_kin + $inshi_zei_sei_kin + $touroku_zei_sei_kin;
$shozei_han_total = $kotei_zei_han_kin + $inshi_zei_han_kin + $touroku_zei_han_kin;
$shozei_total     = $kotei_zei_total_kin + $inshi_zei_total_kin + $touroku_zei_total_kin;

if (isset($_POST['input_data'])) {                        // 当月データの登録
    ///////// 項目とインデックスの関連付け
    $item = array();
    $item[0]   = "現金及び預金";
    $item[1]   = "貸借製品";
    $item[2]   = "貸借仕掛品";
    $item[3]   = "貸借原材料及び貯蔵品";
    $item[4]   = "未収入金";
    $item[5]   = "その他の流動資産";
    $item[6]   = "建物";
    $item[7]   = "構築物";
    $item[8]   = "機械及び装置";
    $item[9]   = "車輌運搬具";
    $item[10]  = "工具器具及び備品";
    $item[11]  = "リース資産";
    $item[12]  = "減価償却累計額";
    $item[13]  = "電話加入権";
    $item[14]  = "その他の投資等";
    $item[15]  = "未払消費税等";
    $item[16]  = "買掛金";
    $item[17]  = "未払金";
    $item[18]  = "その他の流動負債";
    $item[19]  = "期首仕掛品";
    $item[20]  = "期首原材料及び貯蔵品";
    $item[21]  = "期末仕掛品";
    $item[22]  = "期末原材料及び貯蔵品";
    $item[23]  = "雑収入";
    $item[24]  = "その他の営業外費用";
    $item[25]  = "固定資産除却損";
    $item[26]  = "eca法人税、住民税及び事業税";
    $item[27]  = "販管費旅費交通費";
    $item[28]  = "販管費広告宣伝費";
    $item[29]  = "販管費業務委託費";
    $item[30]  = "販管費諸税公課";
    $item[31]  = "販管費事務用消耗品費";
    $item[32]  = "販管費雑費";
    $item[33]  = "販管費地代家賃";
    $item[34]  = "販管費厚生福利費";
    $item[35]  = "販管費退職給付費用";
    $item[36]  = "製造経費旅費交通費";
    $item[37]  = "製造経費業務委託費";
    $item[38]  = "製造経費雑費";
    $item[39]  = "製造経費地代家賃";
    $item[40]  = "製造経費厚生福利費";
    $item[41]  = "製造経費退職給付費用";
    $item[42]  = "固定資産売却損";
    $item[43]  = "有償支給未収入金";
    $item[44]  = "立替金";
    $item[45]  = "明細未収入金";
    $item[46]  = "仮払金";
    $item[47]  = "明細その他流動資産";
    $item[48]  = "資産金額建物";
    $item[49]  = "減価償却累計額(建物)";
    $item[50]  = "資産金額機械及び装置";
    $item[51]  = "減価償却累計額(機械及び装置)";
    $item[52]  = "資産金額車輌運搬具";
    $item[53]  = "減価償却累計額(車輌運搬具)";
    $item[54]  = "資産金額工具器具備品";
    $item[55]  = "減価償却累計額(工具器具備品)";
    $item[56]  = "資産金額リース資産";
    $item[57]  = "減価償却累計額(リース資産)";
    $item[58]  = "eca法定福利費";                       // 販管費
    $item[59]  = "eca福利厚生費";                       // 販管費
    $item[60]  = "eca倉敷料";                           // 販管費
    $item[61]  = "eca地代家賃";                         // 販管費
    $item[62]  = "eca業務委託費";                       // 販管費
    $item[63]  = "eca支払手数料";                       // 販管費
    $item[64]  = "eca求人費";                           // 販管費
    $item[65]  = "eca諸会費";                           // 販管費
    $item[66]  = "eca雑費";                             // 販管費
    $item[67]  = "eca未収収益";                         // 販管費
    $item[68]  = "資産金額構築物";
    $item[69]  = "減価償却累計額(構築物)";
    $item[70]  = "eca広告宣伝費";                       // 販管費
    $item[71]  = "貯蔵品";
    ///////// 各データの保管
    $input_data = array();
    $input_data[0]   = $genyo_total_kin;
    $input_data[1]   = $seihin_total_kin;
    $input_data[2]   = $sikakari_total_kin;
    $input_data[3]   = $gencho_total_kin;
    $input_data[4]   = $ryu_mishu_kin;
    $input_data[5]   = $hokaryudo_total_kin;
    $input_data[6]   = $tate_all_shisan_kin;
    $input_data[7]   = $kouchiku_shisan_kin;
    $input_data[8]   = $kikai_shisan_kin;
    $input_data[9]   = $sharyo_shisan_kin;
    $input_data[10]  = $jyubihin_all_shisan_kin;
    $input_data[11]  = $lease_shisan_kin;
    $input_data[12]  = $gensyo_total_mi_kin;
    $input_data[13]  = $denwa_kin;
    $input_data[14]  = $toushi_total_kin;
    $input_data[15]  = $mihazei_total_kin;
    $input_data[16]  = $kaikake_total_kin;
    $input_data[17]  = $miharai_total_kin;
    $input_data[18]  = $sonota_ryudo_total_kin;
    $input_data[19]  = $z_sikakari_total_kin;
    $input_data[20]  = $z_gencho_total_kin;
    $input_data[21]  = $kimatsu_sikakari_total_kin;
    $input_data[22]  = $kimatsu_gencho_total_kin;
    $input_data[23]  = $eigyo_shueki_total_kin;
    $input_data[24]  = $sonota_eihiyo_kin;
    $input_data[25]  = $kotei_jyoson_kin;
    $input_data[26]  = $eca_hojin_zeito_total_kin;
    $input_data[27]  = $han_ryohi_total_kin;
    $input_data[28]  = $han_kokoku_total_kin;
    $input_data[29]  = $han_gyomu_total_kin;
    $input_data[30]  = $han_zeikoka_total_kin;
    $input_data[31]  = $han_jimuyo_total_kin;
    $input_data[32]  = $han_zappi_total_kin;
    $input_data[33]  = $han_yachin_total_kin;
    $input_data[34]  = $han_kofukuri_total_kin;
    $input_data[35]  = $han_taikyufu_total_kin;
    $input_data[36]  = $sei_ryohi_total_kin;
    $input_data[37]  = $sei_gyomu_total_kin;
    $input_data[38]  = $sei_zappi_total_kin;
    $input_data[39]  = $sei_yachin_total_kin;
    $input_data[40]  = $sei_kofukuri_total_kin;
    $input_data[41]  = $sei_taikyufu_total_kin;
    $input_data[42]  = $kotei_baison_kin;
    $input_data[43]  = $yumi_kin;
    $input_data[44]  = $tatekae_kin;
    $input_data[45]  = $mishu_kin;
    $input_data[46]  = $karibara_kin;
    $input_data[47]  = $hokaryudo_kin;
    $input_data[48]  = $tate_shutoku_kin;
    $input_data[49]  = $tate_rui_kin;
    $input_data[50]  = $kikai_kin;
    $input_data[51]  = -$kikai_gen_kin;
    $input_data[52]  = $sharyo_kin;
    $input_data[53]  = -$sharyo_gen_kin;
    $input_data[54]  = $kikougu_shutoku_kin;
    $input_data[55]  = $kikougu_rui_kin;
    $input_data[56]  = $lease_kin;
    $input_data[57]  = -$lease_gen_kin;
    $input_data[58]  = $han_hofukuri_kin;
    $input_data[59]  = $han_kofukuri_kin;
    $input_data[60]  = $han_kura_kin;
    $input_data[61]  = $han_yachin_kin;
    $input_data[62]  = $han_gyomu_kin;
    $input_data[63]  = $han_tesu_kin;
    $input_data[64]  = $han_kyujin_kin;
    $input_data[65]  = $han_kaihi_kin;
    $input_data[66]  = $han_zappi_kin;
    $input_data[67]  = $mishueki_kin;
    $input_data[68]  = $kouchiku_kin;
    $input_data[69]  = -$kouchiku_gen_kin;
    $input_data[70]  = $han_kokoku_kin;
    $input_data[71]  = $chozo_kin;
    ///////// 各データの登録
    insert_date($item,$yyyymm,$input_data);
}


function insert_date($item,$yyyymm,$input_data) 
{
    $num_input = count($input_data);
    for ($i = 0; $i < $num_input; $i++) {
        $query = sprintf("select rep_kin from financial_report_data where rep_ymd=%d and rep_note='%s'", $yyyymm, $item[$i]);
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
            $query = sprintf("insert into financial_report_data (rep_ymd, rep_kin, rep_note, last_date, last_user) values (%d, %d, '%s', CURRENT_TIMESTAMP, '%s')", $yyyymm, $input_data[$i], $item[$i], $_SESSION['User_ID']);
            if (query_affected_trans($con, $query) <= 0) {
                $_SESSION["s_sysmsg"] .= sprintf("%sの新規登録に失敗<br> %d", $item[$i], $yyyymm);
                query_affected_trans($con, "rollback");     // transaction rollback
                exit();
            }
            /////////// commit トランザクション終了
            query_affected_trans($con, "commit");
            $_SESSION["s_sysmsg"] = sprintf("<font color='yellow'>%d 決算書データ 新規 登録完了</font>",$yyyymm);
        } else {
            /////////// begin トランザクション開始
            if ($con = db_connect()) {
                query_affected_trans($con, "begin");
            } else {
                $_SESSION["s_sysmsg"] .= "データベースに接続できません";
                exit();
            }
            ////////// UPDATE Start
            $query = sprintf("update financial_report_data set rep_kin=%d, last_date=CURRENT_TIMESTAMP, last_user='%s' where rep_ymd=%d and rep_note='%s'", $input_data[$i], $_SESSION['User_ID'], $yyyymm, $item[$i]);
            if (query_affected_trans($con, $query) <= 0) {
                $_SESSION["s_sysmsg"] .= sprintf("%sのUPDATEに失敗<br> %d", $item[$i], $yyyymm);
                query_affected_trans($con, "rollback");     // transaction rollback
                exit();
            }
            /////////// commit トランザクション終了
            query_affected_trans($con, "commit");
            $_SESSION["s_sysmsg"] = sprintf("<font color='yellow'>%d 決算書データ 変更 完了</font>",$yyyymm);
        }
    }
    $_SESSION["s_sysmsg"] .= "決算書のデータを登録しました。";
}

/////////// HTML Header を出力してキャッシュを制御
$menu->out_html_header();
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<meta http-equiv="Content-Style-Type" content="text/css">
<title><?= $menu->out_title() ?></title>
<?= $menu->out_site_java() ?>
<?= $menu->out_css() ?>
<?= $menu->out_jsBaseClass() ?>
<script type=text/javascript language='JavaScript'>
<!--
function data_input_click(obj) {
    return confirm("当月のデータを登録します。\n既にデータがある場合は上書きされます。");
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
td.winboxt {
    border-style:           solid;
    border-width:           1px;
    border-top-color:       #FFFFFF;
    border-left-color:      #FFFFFF;
    border-right-color:     #bdaa90;
    border-bottom-color:    #bdaa90;
    writing-mode       :    tb-rl;
}
-->
</style>
</head>
<body>
<?= $menu->out_title_border() ?>
        <?php
            //  bgcolor='#ceffce' 黄緑
            //  bgcolor='#ffffc6' 薄い黄色
            //  bgcolor='#d6d3ce' Win グレイ
        ?>
    <!--------------- ここから本文の表を表示する -------------------->
        <table bgcolor='#ffffff' align='left' cellspacing='0' cellpadding='10'>
            <tr><td> <!----------- ダミー(デザイン用) ------------>
        <div class='pt11b'>1.現金および預金の内訳</div>
        <table class='winbox_field' width='100%' align='center' bgcolor='black' cellspacing='1' cellpadding='5'>
            <THEAD>
                <!-- テーブル ヘッダーの表示 -->
                <tr>
                    <td class='winboxt' nowrap bgcolor='#ffffff' rowspan='2' align='center'><div class='pt11b'>現金</div></td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center'><div class='pt11b'>科目</div></td>
                    <td class='winbox' nowrap bgcolor='#ffffff' colspan='4'><div class='pt11b' align='center'>内容</div></td>
                    <td class='winbox' nowrap bgcolor='#ffffff'><div class='pt11b' align='center'>金額</div></td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff'><div class='pt11b' align='center'>現金</div></td>
                    <td class='winbox' nowrap bgcolor='#ffffff' colspan='4'><div class='pt11b' align='center'>手許残高</div></td>
                    <td class='winbox' nowrap bgcolor='#E0FFFF' align='right'>
                        <div class='pt11b'><?= number_format($genkin_kin) ?></div>
                    </td>
                </tr>
            </THEAD>
            <TFOOT>
                <!-- 現在はフッターは何もない -->
            </TFOOT>
            <TBODY>
                <tr>
                    <td class='winboxt' nowrap bgcolor='#ffffff' rowspan='5' align='center'><div class='pt11b'>預金内訳</div></td>
                    <td class='winbox' nowrap bgcolor='#ffffff'><div class='pt11b' align='center'>銀行名</div></td>
                    <td class='winbox' nowrap bgcolor='#ffffff'><div class='pt11b' align='center'>支店名</div></td>
                    <td class='winbox' nowrap bgcolor='#ffffff'><div class='pt11b' align='center'>普通預金</div></td>
                    <td class='winbox' nowrap bgcolor='#ffffff'><div class='pt11b' align='center'>当座預金</div></td>
                    <td class='winbox' nowrap bgcolor='#ffffff'><div class='pt11b' align='center'>定期預金</div></td>
                    <td class='winbox' nowrap bgcolor='#ffffff'><div class='pt11b' align='center'>計</div></td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff'><div class='pt11b' align='center'>三菱UFJ</div></td>
                    <td class='winbox' nowrap bgcolor='#ffffff'><div class='pt11b' align='center'>池上</div></td>
                    <td class='winbox' nowrap bgcolor='#E0FFFF' align='right'>
                        <div class='pt11b'><?= number_format($ufj_futu_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#f5f5f5' align='right'>
                        <div class='pt11b'>-</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#E0FFFF' align='right'>
                        <div class='pt11b'><?= number_format($ufj_teiki_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#E0FFFF' align='right'>
                        <div class='pt11b'><?= number_format($ufj_total_kin) ?></div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff'><div class='pt11b' align='center'>三菱UFJ信託</div></td>
                    <td class='winbox' nowrap bgcolor='#ffffff'><div class='pt11b' align='center'>本店</div></td>
                    <td class='winbox' nowrap bgcolor='#E0FFFF' align='right'>
                        <div class='pt11b'><?= number_format($ufjs_futu_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#f5f5f5' align='right'>
                        <div class='pt11b'>-</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#E0FFFF' align='right'>
                        <div class='pt11b'><?= number_format($ufjs_teiki_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#E0FFFF' align='right'>
                        <div class='pt11b'><?= number_format($ufjs_total_kin) ?></div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff'><div class='pt11b' align='center'>足利</div></td>
                    <td class='winbox' nowrap bgcolor='#ffffff'><div class='pt11b' align='center'>氏家</div></td>
                    <td class='winbox' nowrap bgcolor='#E0FFFF' align='right'>
                        <div class='pt11b'><?= number_format($ashi_futu_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#f5f5f5' align='right'>
                        <div class='pt11b'>-</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#E0FFFF' align='right'>
                        <div class='pt11b'><?= number_format($ashi_teiki_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#E0FFFF' align='right'>
                        <div class='pt11b'><?= number_format($ashi_total_kin) ?></div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' colspan='2'><div class='pt11b' align='center'>小計</div></td>
                    <td class='winbox' nowrap bgcolor='#E0FFFF' align='right'>
                        <div class='pt11b'><?= number_format($futsu_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#E0FFFF' align='right'>
                        <div class='pt11b'><?= number_format($touza_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#E0FFFF' align='right'>
                        <div class='pt11b'><?= number_format($teiki_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#E0FFFF' align='right'>
                        <div class='pt11b'><?= number_format($yokin_total_kin) ?></div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' colspan='6' align='right'><div class='pt11b'>現金預金合計</div></td>
                    <td class='winbox' nowrap bgcolor='#E0FFFF' align='right'>
                        <div class='pt11b'><?= number_format($genyo_total_kin) ?></div>
                    </td>
                </tr>
            </TBODY>
        </table>
            </td></tr>
        </table> <!----------------- ダミーEnd ------------------>
        
        <BR clear='all'>
        
        <table bgcolor='#ffffff' align='left' cellspacing='0' cellpadding='10'>
            <tr><td> <!----------- ダミー(デザイン用) ------------>
        <div class='pt11b'>2.売掛金の内訳</div>
        <table class='winbox_field' width='100%' align='center' bgcolor='black' cellspacing='1' cellpadding='5'>
            <THEAD>
                <!-- テーブル ヘッダーの表示 -->
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center'><div class='pt11b'>社名</div></td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center'><div class='pt11b'>住所</div></td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center'><div class='pt11b'>金額</div></td>
                </tr>
            </THEAD>
            <TFOOT>
                <!-- 現在はフッターは何もない -->
            </TFOOT>
            <TBODY>
                <?php if ($nk_uri_kin<>0) { ?>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='left'>
                        <div class='pt11b'>日東工器株式会社</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='left'>
                        <div class='pt11b'>東京都大田区仲池上2丁目9番4号</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#E0FFFF' align='right'>
                        <div class='pt11b'><?= number_format($nk_uri_kin) ?></div>
                    </td>
                </tr>
                <?php } ?>
                <?php if ($mt_uri_kin<>0) { ?>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='left'>
                        <div class='pt11b'>株式会社 メドテック</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='left'>
                        <div class='pt11b'>山形県山形市若宮1-1-36</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#E0FFFF' align='right'>
                        <div class='pt11b'><?= number_format($mt_uri_kin) ?></div>
                    </td>
                </tr>
                <?php } ?>
                <?php if ($snk_uri_kin<>0) { ?>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='left'>
                        <div class='pt11b'>白河日東工器株式会社</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='left'>
                        <div class='pt11b'>福島県白河市双石横峰12番</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#E0FFFF' align='right'>
                        <div class='pt11b'><?= number_format($snk_uri_kin) ?></div>
                    </td>
                </tr>
                <?php } ?>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' colspan='2' align='right'><div class='pt11b'>合計</div></td>
                    <td class='winbox' nowrap bgcolor='#E0FFFF' align='right'>
                        <div class='pt11b'><?= number_format($urikake_kin) ?></div>
                    </td>
                </tr>
            </TBODY>
        </table>
            </td></tr>
        </table> <!----------------- ダミーEnd ------------------>
        
        <BR clear='all'>
        
        <table bgcolor='#ffffff' align='left' cellspacing='0' cellpadding='10'>
            <tr><td> <!----------- ダミー(デザイン用) ------------>
        <div class='pt11b'>3.棚卸資産の内訳</div>
        <table class='winbox_field' width='100%' align='center' bgcolor='black' cellspacing='1' cellpadding='5'>
            <THEAD>
                <!-- テーブル ヘッダーの表示 -->
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' rowspan='2'><div class='pt11b'>内訳</div></td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' colspan='6'><div class='pt11b'>原材料及び貯蔵品</div></td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' rowspan='2'><div class='pt11b'>仕掛品</div></td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' rowspan='2'><div class='pt11b'>合計</div></td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center'><div class='pt11b'>生産用部品</div></td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center'><div class='pt11b'>半成部品</div></td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center'><div class='pt11b'>原材料</div></td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center'><div class='pt11b'>ＣＣ部品</div></td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center'><div class='pt11b'>貯蔵品</div></td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center'><div class='pt11b'>小計</div></td>
                </tr>
            </THEAD>
            <TFOOT>
                <!-- 現在はフッターは何もない -->
            </TFOOT>
            <TBODY>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center'><div class='pt11b'>資材・検査</div></td>
                    <td class='winbox' nowrap bgcolor='#E0FFFF' align='right'>
                        <div class='pt11b'><?= number_format($sei_buhin_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#E0FFFF' align='right'>
                        <div class='pt11b'><?= number_format($tana_ken_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#E0FFFF' align='right'>
                        <div class='pt11b'><?= number_format($gen_sizai_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#E0FFFF' align='right'>
                        <div class='pt11b'>－</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#E0FFFF' align='right'>
                        <div class='pt11b'>－</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#E0FFFF' align='right'>
                        <div class='pt11b'><?= number_format($tana_sizai_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#E0FFFF' align='right'>
                        <div class='pt11b'>－</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#E0FFFF' align='right'>
                        <div class='pt11b'><?= number_format($tana_sizai_kin) ?></div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center'><div class='pt11b'>工作</div></td>
                    <td class='winbox' nowrap bgcolor='#E0FFFF' align='right'>
                        <div class='pt11b'>－</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#E0FFFF' align='right'>
                        <div class='pt11b'><?= number_format($tana_kou_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#E0FFFF' align='right'>
                        <div class='pt11b'>－</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#E0FFFF' align='right'>
                        <div class='pt11b'>－</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#E0FFFF' align='right'>
                        <div class='pt11b'>－</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#E0FFFF' align='right'>
                        <div class='pt11b'><?= number_format($tana_kou_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#E0FFFF' align='right'>
                        <div class='pt11b'>－</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#E0FFFF' align='right'>
                        <div class='pt11b'><?= number_format($tana_kou_kin) ?></div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center'><div class='pt11b'>外注</div></td>
                    <td class='winbox' nowrap bgcolor='#E0FFFF' align='right'>
                        <div class='pt11b'>－</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#E0FFFF' align='right'>
                        <div class='pt11b'><?= number_format($tana_gai_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#E0FFFF' align='right'>
                        <div class='pt11b'>－</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#E0FFFF' align='right'>
                        <div class='pt11b'>－</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#E0FFFF' align='right'>
                        <div class='pt11b'>－</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#E0FFFF' align='right'>
                        <div class='pt11b'><?= number_format($tana_gai_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#E0FFFF' align='right'>
                        <div class='pt11b'>－</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#E0FFFF' align='right'>
                        <div class='pt11b'><?= number_format($tana_gai_kin) ?></div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center'><div class='pt11b'>組立</div></td>
                    <td class='winbox' nowrap bgcolor='#E0FFFF' align='right'>
                        <div class='pt11b'>－</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#E0FFFF' align='right'>
                        <div class='pt11b'>－</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#E0FFFF' align='right'>
                        <div class='pt11b'>－</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#E0FFFF' align='right'>
                        <div class='pt11b'><?= number_format($kumi_cc_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#E0FFFF' align='right'>
                        <div class='pt11b'>－</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#E0FFFF' align='right'>
                        <div class='pt11b'><?= number_format($kumi_cc_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#E0FFFF' align='right'>
                        <div class='pt11b'><?= number_format($sikakari_total_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#E0FFFF' align='right'>
                        <div class='pt11b'><?= number_format($kumi_total_kin) ?></div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center'><div class='pt11b'>その他</div></td>
                    <td class='winbox' nowrap bgcolor='#E0FFFF' align='right'>
                        <div class='pt11b'>－</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#E0FFFF' align='right'>
                        <div class='pt11b'>－</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#E0FFFF' align='right'>
                        <div class='pt11b'>－</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#E0FFFF' align='right'>
                        <div class='pt11b'>－</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#E0FFFF' align='right'>
                        <div class='pt11b'><?= number_format($chozo_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#E0FFFF' align='right'>
                        <div class='pt11b'><?= number_format($chozo_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#E0FFFF' align='right'>
                        <div class='pt11b'>－</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#E0FFFF' align='right'>
                        <div class='pt11b'><?= number_format($chozo_kin) ?></div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center'><div class='pt11b'>合計</div></td>
                    <td class='winbox' nowrap bgcolor='#E0FFFF' align='right'>
                        <div class='pt11b'><?= number_format($sei_buhin_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#E0FFFF' align='right'>
                        <div class='pt11b'><?= number_format($han_total_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#E0FFFF' align='right'>
                        <div class='pt11b'><?= number_format($gen_sizai_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#E0FFFF' align='right'>
                        <div class='pt11b'><?= number_format($kumi_cc_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#E0FFFF' align='right'>
                        <div class='pt11b'><?= number_format($chozo_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#E0FFFF' align='right'>
                        <div class='pt11b'><?= number_format($gencho_total_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#E0FFFF' align='right'>
                        <div class='pt11b'><?= number_format($sikakari_total_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#E0FFFF' align='right'>
                        <div class='pt11b'><?= number_format($ryudozaiko_total_kin) ?></div>
                    </td>
                </tr>
            </TBODY>
        </table>
            </td></tr>
        </table> <!----------------- ダミーEnd ------------------>
        
        <BR clear='all'>
        
        <table bgcolor='#ffffff' align='left' cellspacing='0' cellpadding='10'>
            <tr><td> <!----------- ダミー(デザイン用) ------------>
        <div class='pt11b'>4.前払費用の内訳</div>
        <table class='winbox_field' width='100%' align='center' bgcolor='black' cellspacing='1' cellpadding='5'>
            <THEAD>
                <!-- テーブル ヘッダーの表示 -->
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center'><div class='pt11b'>社名</div></td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center'><div class='pt11b'>内容</div></td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center'><div class='pt11b'>金額</div></td>
                </tr>
            </THEAD>
            <TFOOT>
                <!-- 現在はフッターは何もない -->
            </TFOOT>
            <TBODY>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffe4c4' align='right'>
                        <div class='pt11b'>－</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffe4c4' align='right'>
                        <div class='pt11b'>－</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffe4c4' align='right'>
                        <div class='pt11b'>－</div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' colspan='2' align='right'><div class='pt11b'>合計</div></td>
                    <td class='winbox' nowrap bgcolor='#E0FFFF' align='right'>
                        <div class='pt11b'><?= number_format($mae_hiyo_kin) ?></div>
                    </td>
                </tr>
            </TBODY>
        </table>
            </td></tr>
        </table> <!----------------- ダミーEnd ------------------>
        
        <BR clear='all'>
        
        <table bgcolor='#ffffff' align='left' cellspacing='0' cellpadding='10'>
            <tr><td> <!----------- ダミー(デザイン用) ------------>
        <div class='pt11b'>5.未収入金の内訳</div>
        <table class='winbox_field' width='100%' align='center' bgcolor='black' cellspacing='1' cellpadding='5'>
            <THEAD>
                <!-- テーブル ヘッダーの表示 -->
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center'><div class='pt11b'>区分</div></td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center'><div class='pt11b'>内容</div></td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center'><div class='pt11b'>金額</div></td>
                </tr>
            </THEAD>
            <TFOOT>
                <!-- 現在はフッターは何もない -->
            </TFOOT>
            <TBODY>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffe4c4' align='right'>
                        <div class='pt11b'>－</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffe4c4' align='right'>
                        <div class='pt11b'>－</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffe4c4' align='right'>
                        <div class='pt11b'>－</div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' colspan='2' align='right'><div class='pt11b'>合計</div></td>
                    <td class='winbox' nowrap bgcolor='#E0FFFF' align='right'>
                        <div class='pt11b'><?= number_format($ryu_mishu_total_kin) ?></div>
                    </td>
                </tr>
            </TBODY>
        </table>
            </td></tr>
        </table> <!----------------- ダミーEnd ------------------>
        
        <BR clear='all'>
        
        <table bgcolor='#ffffff' align='left' cellspacing='0' cellpadding='10'>
            <tr><td> <!----------- ダミー(デザイン用) ------------>
        <div class='pt11b'>6.その他流動資産の内訳</div>
        <table class='winbox_field' width='100%' align='center' bgcolor='black' cellspacing='1' cellpadding='5'>
            <THEAD>
                <!-- テーブル ヘッダーの表示 -->
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center'><div class='pt11b'>勘定科目</div></td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center'><div class='pt11b'>内容</div></td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center'><div class='pt11b'>金額</div></td>
                </tr>
            </THEAD>
            <TFOOT>
                <!-- 現在はフッターは何もない -->
            </TFOOT>
            <TBODY>
                <?php if ($karibara_kin<>0) { ?>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='left'>
                        <div class='pt11b'>仮払金</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffe4c4' align='right'>
                        <div class='pt11b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#E0FFFF' align='right'>
                        <div class='pt11b'><?= number_format($karibara_kin) ?></div>
                    </td>
                </tr>
                <?php } ?>
                <?php if ($tatekae_kin<>0) { ?>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='left'>
                        <div class='pt11b'>立替金</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffe4c4' align='right'>
                        <div class='pt11b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#E0FFFF' align='right'>
                        <div class='pt11b'><?= number_format($tatekae_kin) ?></div>
                    </td>
                </tr>
                <?php } ?>
                <?php if ($hokaryudo_kin<>0) { ?>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='left'>
                        <div class='pt11b'>その他</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffe4c4' align='right'>
                        <div class='pt11b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#E0FFFF' align='right'>
                        <div class='pt11b'><?= number_format($hokaryudo_kin) ?></div>
                    </td>
                </tr>
                <?php } ?>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' colspan='2' align='right'><div class='pt11b'>合計</div></td>
                    <td class='winbox' nowrap bgcolor='#E0FFFF' align='right'>
                        <div class='pt11b'><?= number_format($hokaryudo_total_kin) ?></div>
                    </td>
                </tr>
            </TBODY>
        </table>
            </td></tr>
        </table> <!----------------- ダミーEnd ------------------>
        
        <BR clear='all'>
        
        <table bgcolor='#ffffff' align='left' cellspacing='0' cellpadding='10'>
            <tr><td> <!----------- ダミー(デザイン用) ------------>
        <div class='pt11b'>7.有形固定資産及び減価償却費の内訳　・・・　別紙明細書参照</div>
            </td></tr>
        </table> <!----------------- ダミーEnd ------------------>
        
        <BR clear='all'>
        
        <table bgcolor='#ffffff' align='left' cellspacing='0' cellpadding='10'>
            <tr><td> <!----------- ダミー(デザイン用) ------------>
        <div class='pt11b'>8.電話加入権の内訳</div>
        <table class='winbox_field' width='100%' align='center' bgcolor='black' cellspacing='1' cellpadding='5'>
            <THEAD>
                <!-- テーブル ヘッダーの表示 -->
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center'><div class='pt11b'>電話番号</div></td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center'><div class='pt11b'>摘要</div></td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center'><div class='pt11b'>金額</div></td>
                </tr>
            </THEAD>
            <TFOOT>
                <!-- 現在はフッターは何もない -->
            </TFOOT>
            <TBODY>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center'><div class='pt11b'>028－682－8851</div></td>
                    <td class='winbox' nowrap bgcolor='#ffffff'><div class='pt11b'>発着両用（代表）</div></td>
                    <td class='winbox' nowrap bgcolor='#E0FFFF' align='right'>
                        <div class='pt11b'>72,000</div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center'><div class='pt11b'>028－682－8852</div></td>
                    <td class='winbox' nowrap bgcolor='#ffffff'><div class='pt11b'>発着両用</div></td>
                    <td class='winbox' nowrap bgcolor='#E0FFFF' align='right'>
                        <div class='pt11b'>72,000</div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center'><div class='pt11b'>028－682－8853</div></td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right'><div class='pt11b'>（休止）</div></td>
                    <td class='winbox' nowrap bgcolor='#E0FFFF' align='right'>
                        <div class='pt11b'>72,000</div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center'><div class='pt11b'>028－682－9153</div></td>
                    <td class='winbox' nowrap bgcolor='#ffffff'><div class='pt11b'>ダイヤルイン（総務）</div></td>
                    <td class='winbox' nowrap bgcolor='#E0FFFF' align='right'>
                        <div class='pt11b'>72,000</div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center'><div class='pt11b'>028－682－9250</div></td>
                    <td class='winbox' nowrap bgcolor='#ffffff'><div class='pt11b'>ダイヤルイン（購買）</div></td>
                    <td class='winbox' nowrap bgcolor='#E0FFFF' align='right'>
                        <div class='pt11b'>72,000</div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center'><div class='pt11b'>028－682－7471</div></td>
                    <td class='winbox' nowrap bgcolor='#ffffff'><div class='pt11b'>ダイヤルイン</div></td>
                    <td class='winbox' nowrap bgcolor='#E0FFFF' align='right'>
                        <div class='pt11b'>72,000</div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center'><div class='pt11b'>028－682－3044</div></td>
                    <td class='winbox' nowrap bgcolor='#ffffff'><div class='pt11b'>ピンク電話（食堂）</div></td>
                    <td class='winbox' nowrap bgcolor='#E0FFFF' align='right'>
                        <div class='pt11b'>72,000</div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center'><div class='pt11b'>028－681－6481</div></td>
                    <td class='winbox' nowrap bgcolor='#ffffff'><div class='pt11b'>商品管理</div></td>
                    <td class='winbox' nowrap bgcolor='#E0FFFF' align='right'>
                        <div class='pt11b'>72,000</div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center'><div class='pt11b'>028－681－6482</div></td>
                    <td class='winbox' nowrap bgcolor='#ffffff'><div class='pt11b'>商品管理</div></td>
                    <td class='winbox' nowrap bgcolor='#E0FFFF' align='right'>
                        <div class='pt11b'>72,000</div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center'><div class='pt11b'>028－682－7367</div></td>
                    <td class='winbox' nowrap bgcolor='#ffffff'><div class='pt11b'>ＦＡＸ（商品管理）</div></td>
                    <td class='winbox' nowrap bgcolor='#E0FFFF' align='right'>
                        <div class='pt11b'>72,000</div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center'><div class='pt11b'>028－681－7038</div></td>
                    <td class='winbox' nowrap bgcolor='#ffffff'><div class='pt11b'>ＦＡＸ（事務所棟・ＩＳＤＮ）</div></td>
                    <td class='winbox' nowrap bgcolor='#E0FFFF' align='right'>
                        <div class='pt11b'>72,000</div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center'><div class='pt11b'>028－682－1324</div></td>
                    <td class='winbox' nowrap bgcolor='#ffffff'><div class='pt11b'>ＦＡＸ（第6工場1階事務所）</div></td>
                    <td class='winbox' nowrap bgcolor='#E0FFFF' align='right'>
                        <div class='pt11b'>72,000</div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center'><div class='pt11b'>028－681－7652</div></td>
                    <td class='winbox' nowrap bgcolor='#ffffff'><div class='pt11b'>試験修理直通</div></td>
                    <td class='winbox' nowrap bgcolor='#E0FFFF' align='right'>
                        <div class='pt11b'>72,000</div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center'><div class='pt11b'>028－681－5105</div></td>
                    <td class='winbox' nowrap bgcolor='#ffffff'><div class='pt11b'>交換機</div></td>
                    <td class='winbox' nowrap bgcolor='#E0FFFF' align='right'>
                        <div class='pt11b'>72,000</div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center'><div class='pt11b'>028－681－7011</div></td>
                    <td class='winbox' nowrap bgcolor='#ffffff'><div class='pt11b'>ＴＶ会議用ＩＳＤＮ</div></td>
                    <td class='winbox' nowrap bgcolor='#E0FFFF' align='right'>
                        <div class='pt11b'>72,000</div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center'><div class='pt11b'>028－681－7735</div></td>
                    <td class='winbox' nowrap bgcolor='#ffffff'><div class='pt11b'>サーバー室用</div></td>
                    <td class='winbox' nowrap bgcolor='#E0FFFF' align='right'>
                        <div class='pt11b'>72,000</div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center'><div class='pt11b'>028－682－8853</div></td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right'><div class='pt11b'>（休止）</div></td>
                    <td class='winbox' nowrap bgcolor='#E0FFFF' align='right'>
                        <div class='pt11b'>72,000</div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' colspan='2' align='right'><div class='pt11b'>合計</div></td>
                    <td class='winbox' nowrap bgcolor='#E0FFFF' align='right'>
                        <div class='pt11b'><?= number_format($denwa_kin) ?></div>
                    </td>
                </tr>
            </TBODY>
        </table>
            </td></tr>
        </table> <!----------------- ダミーEnd ------------------>
        
        <BR clear='all'>
        
        <table bgcolor='#ffffff' align='left' cellspacing='0' cellpadding='10'>
            <tr><td> <!----------- ダミー(デザイン用) ------------>
        <div class='pt11b'>9.ソフトウェアの内訳　・・・　別紙明細書参照</div>
            </td></tr>
        </table> <!----------------- ダミーEnd ------------------>
        
        <BR clear='all'>
        
        <table bgcolor='#ffffff' align='left' cellspacing='0' cellpadding='10'>
            <tr><td> <!----------- ダミー(デザイン用) ------------>
        <div class='pt11b'>10.繰延税金資産の内訳</div>
        <table class='winbox_field' width='100%' align='center' bgcolor='black' cellspacing='1' cellpadding='5'>
            <THEAD>
                <!-- テーブル ヘッダーの表示 -->
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center'><div class='pt11b'>摘要</div></td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center'><div class='pt11b'>期首残高</div></td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center'><div class='pt11b'>当期増加額</div></td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center'><div class='pt11b'>当期減少額</div></td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center'><div class='pt11b'>期末残高</div></td>
                </tr>
            </THEAD>
            <TFOOT>
                <!-- 現在はフッターは何もない -->
            </TFOOT>
            <TBODY>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='left'><div class='pt11b'>固定資産</div></td>
                    <td class='winbox' nowrap bgcolor='#E0FFFF' align='right'>
                        <div class='pt11b'><?= number_format($kotei_kurizei_kishu) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#E0FFFF' align='right'>
                        <div class='pt11b'><?= number_format($kotei_kurizei_kin_zou) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#E0FFFF' align='right'>
                        <div class='pt11b'><?= number_format($kotei_kurizei_kin_gen) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#E0FFFF' align='right'>
                        <div class='pt11b'><?= number_format($kotei_kuri_zei_kin) ?></div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right'><div class='pt11b'>合計</div></td>
                    <td class='winbox' nowrap bgcolor='#E0FFFF' align='right'>
                        <div class='pt11b'><?= number_format($kotei_kurizei_kishu) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#E0FFFF' align='right'>
                        <div class='pt11b'><?= number_format($kotei_kurizei_kin_zou) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#E0FFFF' align='right'>
                        <div class='pt11b'><?= number_format($kotei_kurizei_kin_gen) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#E0FFFF' align='right'>
                        <div class='pt11b'><?= number_format($kotei_kuri_zei_kin) ?></div>
                    </td>
                </tr>
            </TBODY>
        </table>
            </td></tr>
        </table> <!----------------- ダミーEnd ------------------>
        
        <BR clear='all'>
        
        <table bgcolor='#ffffff' align='left' cellspacing='0' cellpadding='10'>
            <tr><td> <!----------- ダミー(デザイン用) ------------>
        <div class='pt11b'>11.長期貸付金の内訳</div>
        <table class='winbox_field' width='100%' align='center' bgcolor='black' cellspacing='1' cellpadding='5'>
            <THEAD>
                <!-- テーブル ヘッダーの表示 -->
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center'><div class='pt11b'>摘要</div></td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center'><div class='pt11b'>期首残高</div></td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center'><div class='pt11b'>当期増加額</div></td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center'><div class='pt11b'>当期減少額</div></td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center'><div class='pt11b'>期末残高</div></td>
                </tr>
            </THEAD>
            <TFOOT>
                <!-- 現在はフッターは何もない -->
            </TFOOT>
            <TBODY>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='left'><div class='pt11b'>従業員貸付金</div></td>
                    <td class='winbox' nowrap bgcolor='#E0FFFF' align='right'>
                        <div class='pt11b'><?= number_format($jyu_kashi_kishu) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#E0FFFF' align='right'>
                        <div class='pt11b'><?= number_format($jyu_kashi_kin_zou) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#E0FFFF' align='right'>
                        <div class='pt11b'><?= number_format($jyu_kashi_kin_gen) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#E0FFFF' align='right'>
                        <div class='pt11b'><?= number_format($choki_kashi_kin) ?></div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right'><div class='pt11b'>合計</div></td>
                    <td class='winbox' nowrap bgcolor='#E0FFFF' align='right'>
                        <div class='pt11b'><?= number_format($jyu_kashi_kishu) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#E0FFFF' align='right'>
                        <div class='pt11b'><?= number_format($jyu_kashi_kin_zou) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#E0FFFF' align='right'>
                        <div class='pt11b'><?= number_format($jyu_kashi_kin_gen) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#E0FFFF' align='right'>
                        <div class='pt11b'><?= number_format($choki_kashi_kin) ?></div>
                    </td>
                </tr>
            </TBODY>
        </table>
            </td></tr>
        </table> <!----------------- ダミーEnd ------------------>
        
        <BR clear='all'>
        
        <table bgcolor='#ffffff' align='left' cellspacing='0' cellpadding='10'>
            <tr><td> <!----------- ダミー(デザイン用) ------------>
        <div class='pt11b'>12.長期前払費用の内訳</div>
        <table class='winbox_field' width='100%' align='center' bgcolor='black' cellspacing='1' cellpadding='5'>
            <THEAD>
                <!-- テーブル ヘッダーの表示 -->
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center'><div class='pt11b'>摘要</div></td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center'><div class='pt11b'>期首残高</div></td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center'><div class='pt11b'>当期増加額</div></td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center'><div class='pt11b'>当期減少額</div></td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center'><div class='pt11b'>期末残高</div></td>
                </tr>
            </THEAD>
            <TFOOT>
                <!-- 現在はフッターは何もない -->
            </TFOOT>
            <TBODY>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffe4c4' align='right'>
                        <div class='pt11b'>－</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffe4c4' align='right'>
                        <div class='pt11b'>－</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffe4c4' align='right'>
                        <div class='pt11b'>－</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffe4c4' align='right'>
                        <div class='pt11b'>－</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffe4c4' align='right'>
                        <div class='pt11b'>－</div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right'><div class='pt11b'>合計</div></td>
                    <td class='winbox' nowrap bgcolor='#E0FFFF' align='right'>
                        <div class='pt11b'><?= number_format($choki_maebara_kishu) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#E0FFFF' align='right'>
                        <div class='pt11b'><?= number_format($choki_maebara_kin_zou) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#E0FFFF' align='right'>
                        <div class='pt11b'><?= number_format($choki_maebara_kin_gen) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#E0FFFF' align='right'>
                        <div class='pt11b'><?= number_format($choki_maebara_kin) ?></div>
                    </td>
                </tr>
            </TBODY>
        </table>
            </td></tr>
        </table> <!----------------- ダミーEnd ------------------>
        
        <BR clear='all'>
        
        <table bgcolor='#ffffff' align='left' cellspacing='0' cellpadding='10'>
            <tr><td> <!----------- ダミー(デザイン用) ------------>
        <div class='pt11b'>13.その他投資等の内訳</div>
        <table class='winbox_field' width='100%' align='center' bgcolor='black' cellspacing='1' cellpadding='5'>
            <THEAD>
                <!-- テーブル ヘッダーの表示 -->
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center'><div class='pt11b'>区分</div></td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center'><div class='pt11b'>支払先</div></td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center'><div class='pt11b'>金額</div></td>
                </tr>
            </THEAD>
            <TFOOT>
                <!-- 現在はフッターは何もない -->
            </TFOOT>
            <TBODY>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='left'><div class='pt11b'>出資金</div></td>
                    <td class='winbox' nowrap bgcolor='#ffffff'><div class='pt11b'>情報通信システム協同組合</div></td>
                    <td class='winbox' nowrap bgcolor='#E0FFFF' align='right'>
                        <div class='pt11b'>10,000</div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' colspan='2'><div class='pt11b'>合計</div></td>
                    <td class='winbox' nowrap bgcolor='#E0FFFF' align='right'>
                        <div class='pt11b'>10,000</div>
                    </td>
                </tr>
            </TBODY>
        </table>
            </td></tr>
        </table> <!----------------- ダミーEnd ------------------>
        
        <BR clear='all'>
        
        <table bgcolor='#ffffff' align='left' cellspacing='0' cellpadding='10'>
            <tr><td> <!----------- ダミー(デザイン用) ------------>
        <div class='pt11b'>14.買掛金の内訳</div>
        <table class='winbox_field' width='100%' align='center' bgcolor='black' cellspacing='1' cellpadding='5'>
            <THEAD>
                <!-- テーブル ヘッダーの表示 -->
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center'><div class='pt11b'>会社名</div></td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center'><div class='pt11b'>本社住所</div></td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center'><div class='pt11b'>金額</div></td>
                </tr>
            </THEAD>
            <TFOOT>
                <!-- 現在はフッターは何もない -->
            </TFOOT>
            <TBODY>
                <?php for ($i = 1; $i < 11; $i++) { ?>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='left'>
                        <div class='pt11b'><?= $kaikake_top[$i][0] ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='left'>
                        <div class='pt11b'><?= $kaikake_top[$i][1] ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#E0FFFF' align='right'>
                        <div class='pt11b'><?= number_format($kaikake_top[$i][2]) ?></div>
                    </td>
                </tr>
                <?php } ?>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='left'>
                        <div class='pt11b'>その他</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='left'>
                        <div class='pt11b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#E0FFFF' align='right'>
                        <div class='pt11b'><?= number_format($kaikake_top_sonota_kin) ?></div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' colspan='2'><div class='pt11b'>合計</div></td>
                    <td class='winbox' nowrap bgcolor='#E0FFFF' align='right'>
                        <div class='pt11b'><?= number_format($kaikake_total_kin) ?></div>
                    </td>
                </tr>
            </TBODY>
        </table>
            </td></tr>
        </table> <!----------------- ダミーEnd ------------------>
        
        <BR clear='all'>
        
        <table bgcolor='#ffffff' align='left' cellspacing='0' cellpadding='10'>
            <tr><td> <!----------- ダミー(デザイン用) ------------>
        <div class='pt11b'>15.未払金の内訳</div>
        <table class='winbox_field' width='100%' align='center' bgcolor='black' cellspacing='1' cellpadding='5'>
            <THEAD>
                <!-- テーブル ヘッダーの表示 -->
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center'><div class='pt11b'>会社名・区分</div></td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center'><div class='pt11b'>本社住所・内容</div></td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center'><div class='pt11b'>金額</div></td>
                </tr>
            </THEAD>
            <TFOOT>
                <!-- 現在はフッターは何もない -->
            </TFOOT>
            <TBODY>
                <?php for ($i = 1; $i < 11; $i++) { ?>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='left'>
                        <div class='pt11b'><?= $miharai_top[$i][0] ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='left'>
                        <div class='pt11b'><?= $miharai_top[$i][1] ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#E0FFFF' align='right'>
                        <div class='pt11b'><?= number_format($miharai_top[$i][2]) ?></div>
                    </td>
                </tr>
                <?php } ?>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='left'>
                        <div class='pt11b'>その他</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='left'>
                        <div class='pt11b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#E0FFFF' align='right'>
                        <div class='pt11b'><?= number_format($miharai_top_sonota_kin) ?></div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' colspan='2'><div class='pt11b'>合計</div></td>
                    <td class='winbox' nowrap bgcolor='#E0FFFF' align='right'>
                        <div class='pt11b'><?= number_format($miharai_total_kin) ?></div>
                    </td>
                </tr>
            </TBODY>
        </table>
            </td></tr>
        </table> <!----------------- ダミーEnd ------------------>
        
        <BR clear='all'>
        
        <table bgcolor='#ffffff' align='left' cellspacing='0' cellpadding='10'>
            <tr><td> <!----------- ダミー(デザイン用) ------------>
        <div class='pt11b'>16.未払消費税等の内訳</div>
        <table class='winbox_field' width='100%' align='center' bgcolor='black' cellspacing='1' cellpadding='5'>
            <THEAD>
                <!-- テーブル ヘッダーの表示 -->
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center'><div class='pt11b'>区分</div></td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center'><div class='pt11b'>内容</div></td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center'><div class='pt11b'>金額</div></td>
                </tr>
            </THEAD>
            <TFOOT>
                <!-- 現在はフッターは何もない -->
            </TFOOT>
            <TBODY>
                <tr>
                    <td class='winbox' nowrap bgcolor='#FFFFFF' align='left'>
                        <div class='pt11b'>仮払消費税</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#E0FFFF' align='left'>
                        <div class='pt11b'>（予定納付額<?= number_format($mae_sho_zei_kin) ?>円含む）</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#E0FFFF' align='right'>
                        <div class='pt11b'><?= number_format($karibara_zei_total) ?></div>
                        <!--
                        <div class='pt11b'><?= mb_ereg_replace('-', '△', number_format($karibara_zei_total)) ?></div>
                        -->
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#FFFFFF' align='left'>
                        <div class='pt11b'>仮受消費税</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#FFFFFF' align='right'>
                        <div class='pt11b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#E0FFFF' align='right'>
                        <div class='pt11b'><?= number_format($kariuke_sho_zei_kin) ?></div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' colspan='2'><div class='pt11b'>合計</div></td>
                    <td class='winbox' nowrap bgcolor='#E0FFFF' align='right'>
                        <div class='pt11b'><?= number_format($mihazei_total_kin) ?></div>
                    </td>
                </tr>
            </TBODY>
        </table>
            </td></tr>
        </table> <!----------------- ダミーEnd ------------------>
        
        <BR clear='all'>
        
        <table bgcolor='#ffffff' align='left' cellspacing='0' cellpadding='10'>
            <tr><td> <!----------- ダミー(デザイン用) ------------>
        <div class='pt11b'>17.未払法人税等の内訳</div>
        <table class='winbox_field' width='100%' align='center' bgcolor='black' cellspacing='1' cellpadding='5'>
            <THEAD>
                <!-- テーブル ヘッダーの表示 -->
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center'><div class='pt11b'>区分</div></td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center'><div class='pt11b'>期首残高</div></td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center'><div class='pt11b'>当期支払額</div></td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center'><div class='pt11b'>当期戻入額</div></td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center'><div class='pt11b'>当期設定額</div></td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center'><div class='pt11b'>期末残高</div></td>
                </tr>
            </THEAD>
            <TFOOT>
                <!-- 現在はフッターは何もない -->
            </TFOOT>
            <TBODY>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='left'>
                        <div class='pt11b'>未払法人税等</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#E0FFFF' align='right'>
                        <div class='pt11b'><?= number_format($miharai_hozei_kishu) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffe4c4' align='right'>
                        <div class='pt11b'><?= number_format($miharai_hozei_shiha) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#E0FFFF' align='right'>
                        <div class='pt11b'>0</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffe4c4' align='right'>
                        <div class='pt11b'><?= number_format($miharai_hozei_settei) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#E0FFFF' align='right'>
                        <div class='pt11b'><?= number_format($miharai_hozei_kin) ?></div>
                    </td>
                </tr>
            </TBODY>
        </table>
            </td></tr>
        </table> <!----------------- ダミーEnd ------------------>
        
        <BR clear='all'>
        
        <table bgcolor='#ffffff' align='left' cellspacing='0' cellpadding='10'>
            <tr><td> <!----------- ダミー(デザイン用) ------------>
        <div class='pt11b'>18.未払費用の内訳</div>
        <table class='winbox_field' width='100%' align='center' bgcolor='black' cellspacing='1' cellpadding='5'>
            <THEAD>
                <!-- テーブル ヘッダーの表示 -->
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center'><div class='pt11b'>会社名・区分</div></td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center'><div class='pt11b'>本社住所・内容</div></td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center'><div class='pt11b'>金額</div></td>
                </tr>
            </THEAD>
            <TFOOT>
                <!-- 現在はフッターは何もない -->
            </TFOOT>
            <TBODY>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffe4c4' align='right'>
                        <div class='pt11b'>－</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffe4c4' align='right'>
                        <div class='pt11b'>－</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffe4c4' align='right'>
                        <div class='pt11b'>－</div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' colspan='2'><div class='pt11b'>合計</div></td>
                    <td class='winbox' nowrap bgcolor='#E0FFFF' align='right'>
                        <div class='pt11b'><?= number_format($miharai_hiyo_kin) ?></div>
                    </td>
                </tr>
            </TBODY>
        </table>
            </td></tr>
        </table> <!----------------- ダミーEnd ------------------>
        
        <BR clear='all'>
        
        <table bgcolor='#ffffff' align='left' cellspacing='0' cellpadding='10'>
            <tr><td> <!----------- ダミー(デザイン用) ------------>
        <div class='pt11b'>19.預り金の内訳</div>
        <table class='winbox_field' width='100%' align='center' bgcolor='black' cellspacing='1' cellpadding='5'>
            <THEAD>
                <!-- テーブル ヘッダーの表示 -->
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center'><div class='pt11b'>区分</div></td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center'><div class='pt11b'>本社住所・内容</div></td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center'><div class='pt11b'>金額</div></td>
                </tr>
            </THEAD>
            <TFOOT>
                <!-- 現在はフッターは何もない -->
            </TFOOT>
            <TBODY>
                <?php if ($gen_shotoku_kin<>0) { ?>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='left'>
                        <div class='pt11b'>源泉所得税</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffe4c4' align='right'>
                        <div class='pt11b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#E0FFFF' align='right'>
                        <div class='pt11b'><?= number_format($gen_shotoku_kin) ?></div>
                    </td>
                </tr>
                <?php } ?>
                <?php if ($gen_jyu_kin<>0) { ?>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='left'>
                        <div class='pt11b'>源泉住民税</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffe4c4' align='right'>
                        <div class='pt11b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#E0FFFF' align='right'>
                        <div class='pt11b'><?= number_format($gen_jyu_kin) ?></div>
                    </td>
                </tr>
                <?php } ?>
                <?php if ($ken_hoken_kin<>0) { ?>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='left'>
                        <div class='pt11b'>健康保険料</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffe4c4' align='right'>
                        <div class='pt11b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#E0FFFF' align='right'>
                        <div class='pt11b'><?= number_format($ken_hoken_kin) ?></div>
                    </td>
                </tr>
                <?php } ?>
                <?php if ($kou_hoken_kin<>0) { ?>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='left'>
                        <div class='pt11b'>厚生年金保険料</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffe4c4' align='right'>
                        <div class='pt11b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#E0FFFF' align='right'>
                        <div class='pt11b'><?= number_format($kou_hoken_kin) ?></div>
                    </td>
                </tr>
                <?php } ?>
                <?php if ($azu_sonota_kin<>0) { ?>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='left'>
                        <div class='pt11b'>その他</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right'>
                        <div class='pt11b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#E0FFFF' align='right'>
                        <div class='pt11b'><?= number_format($azu_sonota_kin) ?></div>
                    </td>
                </tr>
                <?php } ?>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' colspan='2'><div class='pt11b'>合計</div></td>
                    <td class='winbox' nowrap bgcolor='#E0FFFF' align='right'>
                        <div class='pt11b'><?= number_format($azukari_kin) ?></div>
                    </td>
                </tr>
            </TBODY>
        </table>
            </td></tr>
        </table> <!----------------- ダミーEnd ------------------>
        
        <BR clear='all'>
        
        <table bgcolor='#ffffff' align='left' cellspacing='0' cellpadding='10'>
            <tr><td> <!----------- ダミー(デザイン用) ------------>
        <div class='pt11b'>20.引当金の内訳</div>
        <table class='winbox_field' width='100%' align='center' bgcolor='black' cellspacing='1' cellpadding='5'>
            <THEAD>
                <!-- テーブル ヘッダーの表示 -->
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' rowspan='2'><div class='pt11b'>区分</div></td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' rowspan='2'><div class='pt11b'>期首残高</div></td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' rowspan='2'><div class='pt11b'>当期増加額</div></td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' colspan='2'><div class='pt11b'>当期減少額</div></td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' rowspan='2'><div class='pt11b'>期末残高</div></td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center'><div class='pt11b'>目的使用</div></td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center'><div class='pt11b'>その他</div></td>
                </tr>
            </THEAD>
            <TFOOT>
                <!-- 現在はフッターは何もない -->
            </TFOOT>
            <TBODY>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='left'>
                        <div class='pt11b'>賞与引当金</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffe4c4' align='right'>
                        <div class='pt11b'>-</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffe4c4' align='right'>
                        <div class='pt11b'>-</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffe4c4' align='right'>
                        <div class='pt11b'>-</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffe4c4' align='right'>
                        <div class='pt11b'>-</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#E0FFFF' align='right'>
                        <div class='pt11b'><?= number_format($syoyo_hikiate_kin) ?></div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='left'>
                        <div class='pt11b'>退職給付引当金</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#E0FFFF' align='right'>
                        <div class='pt11b'><?= number_format($tai_hiki_kishu) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#E0FFFF' align='right'>
                        <div class='pt11b'><?= number_format($tai_hiki_kin_gen) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffe4c4' align='right'>
                        <div class='pt11b'><?= number_format($tai_hiki_kin_moku) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffe4c4' align='right'>
                        <div class='pt11b'><?= number_format($tai_hiki_kin_sonota) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#E0FFFF' align='right'>
                        <div class='pt11b'><?= number_format($taisyoku_hikiate_kin) ?></div>
                    </td>
                </tr>
        </table>
            </td></tr>
        </table> <!----------------- ダミーEnd ------------------>
        
        <BR clear='all'>
        
        <table bgcolor='#ffffff' align='left' cellspacing='0' cellpadding='10'>
            <tr><td> <!----------- ダミー(デザイン用) ------------>
        <div class='pt11b'>21.資本金及び剰余金の内訳</div>
        <table class='winbox_field' width='100%' align='center' bgcolor='black' cellspacing='1' cellpadding='5'>
            <THEAD>
                <!-- テーブル ヘッダーの表示 -->
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center'><div class='pt11b'>区分</div></td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center'><div class='pt11b'>種類</div></td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center'><div class='pt11b'>期首残高</div></td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center'><div class='pt11b'>当期増加額</div></td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center'><div class='pt11b'>当期減少額</div></td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center'><div class='pt11b'>期末残高</div></td>
                </tr>
            </THEAD>
            <TFOOT>
                <!-- 現在はフッターは何もない -->
            </TFOOT>
            <TBODY>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='left'>
                        <div class='pt11b'>資本金</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center'>
                        <div class='pt11b'>普通株式</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#E0FFFF' align='right'>
                        <div class='pt11b'><?= number_format($shihon_kin_kishu) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#E0FFFF' align='right'>
                        <div class='pt11b'><?= number_format($shihon_kin_gen) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#E0FFFF' align='right'>
                        <div class='pt11b'><?= number_format($shihon_kin_zou) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#E0FFFF' align='right'>
                        <div class='pt11b'><?= number_format($shihon_total_kin) ?></div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='left'>
                        <div class='pt11b'>資本準備金</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right'>
                        <div class='pt11b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#E0FFFF' align='right'>
                        <div class='pt11b'><?= number_format($shihon_jyunbi_kishu) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#E0FFFF' align='right'>
                        <div class='pt11b'><?= number_format($shihon_jyunbi_gen) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#E0FFFF' align='right'>
                        <div class='pt11b'><?= number_format($shihon_jyunbi_zou) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#E0FFFF' align='right'>
                        <div class='pt11b'><?= number_format($shihon_jyunbi_kin) ?></div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='left'>
                        <div class='pt11b'>その他資本剰余金</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right'>
                        <div class='pt11b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#E0FFFF' align='right'>
                        <div class='pt11b'><?= number_format($sonota_shihon_jyoyo_kishu) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#E0FFFF' align='right'>
                        <div class='pt11b'><?= number_format($sonota_shihon_jyoyo_gen) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#E0FFFF' align='right'>
                        <div class='pt11b'><?= number_format($sonota_shihon_jyoyo_zou) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#E0FFFF' align='right'>
                        <div class='pt11b'><?= number_format($sonota_shihon_jyoyo_kin) ?></div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='left'>
                        <div class='pt11b'>その他利益剰余金</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right'>
                        <div class='pt11b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#E0FFFF' align='right'>
                        <div class='pt11b'><?= number_format($sonota_rieki_jyoyo_kishu) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#E0FFFF' align='right'>
                        <div class='pt11b'><?= number_format($sonota_rieki_jyoyo_gen) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#E0FFFF' align='right'>
                        <div class='pt11b'><?= number_format($sonota_rieki_jyoyo_zou) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#E0FFFF' align='right'>
                        <div class='pt11b'><?= number_format($tai_sonota_rieki_jyoyo_kin) ?></div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' colspan='2'><div class='pt11b'>合計</div></td>
                    <td class='winbox' nowrap bgcolor='#E0FFFF' align='right'>
                        <div class='pt11b'><?= number_format($shihon_jyoyo_kishu) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#E0FFFF' align='right'>
                        <div class='pt11b'><?= number_format($shihon_jyoyo_gen) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#E0FFFF' align='right'>
                        <div class='pt11b'><?= number_format($shihon_jyoyo_zou) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#E0FFFF' align='right'>
                        <div class='pt11b'><?= number_format($shihon_jyoyo_total) ?></div>
                    </td>
                </tr>
            </TBODY>
        </table>
            </td></tr>
        </table> <!----------------- ダミーEnd ------------------>
        
        <BR clear='all'>
        
        <table bgcolor='#ffffff' align='left' cellspacing='0' cellpadding='10'>
            <tr><td> <!----------- ダミー(デザイン用) ------------>
        <div class='pt11b'>22.諸税公課の内訳</div>
        <table class='winbox_field' width='100%' align='center' bgcolor='black' cellspacing='1' cellpadding='5'>
            <THEAD>
                <!-- テーブル ヘッダーの表示 -->
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' rowspan='2'><div class='pt11b'>摘要</div></td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center'><div class='pt11b'>製造用</div></td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center'><div class='pt11b'>販管費及び一般管理費</div></td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' rowspan='2'><div class='pt11b'>合計金額</div></td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' rowspan='2'><div class='pt11b'>備考</div></td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center'><div class='pt11b'>金額</div></td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center'><div class='pt11b'>金額</div></td>
                </tr>
            </THEAD>
            <TFOOT>
                <!-- 現在はフッターは何もない -->
            </TFOOT>
            <TBODY>
                <?php if ($kotei_zei_total_kin<>0) { ?>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='left'>
                        <div class='pt11b'>固定資産税</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#E0FFFF' align='right'>
                        <div class='pt11b'><?= number_format($kotei_zei_sei_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#E0FFFF' align='right'>
                        <div class='pt11b'><?= number_format($kotei_zei_han_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#E0FFFF' align='right'>
                        <div class='pt11b'><?= number_format($kotei_zei_total_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right'>
                        <div class='pt11b'>　</div>
                    </td>
                </tr>
                <?php } ?>
                <?php if ($inshi_zei_total_kin<>0) { ?>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='left'>
                        <div class='pt11b'>印紙税</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#E0FFFF' align='right'>
                        <div class='pt11b'><?= number_format($inshi_zei_sei_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#E0FFFF' align='right'>
                        <div class='pt11b'><?= number_format($inshi_zei_han_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#E0FFFF' align='right'>
                        <div class='pt11b'><?= number_format($inshi_zei_total_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right'>
                        <div class='pt11b'>　</div>
                    </td>
                </tr>
                <?php } ?>
                <?php if ($touroku_zei_total_kin<>0) { ?>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='left'>
                        <div class='pt11b'>登録免許税</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#E0FFFF' align='right'>
                        <div class='pt11b'><?= number_format($touroku_zei_sei_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#E0FFFF' align='right'>
                        <div class='pt11b'><?= number_format($touroku_zei_han_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#E0FFFF' align='right'>
                        <div class='pt11b'><?= number_format($touroku_zei_total_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right'>
                        <div class='pt11b'>　</div>
                    </td>
                </tr>
                <?php } ?>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right'><div class='pt11b'>合計</div></td>
                    <td class='winbox' nowrap bgcolor='#E0FFFF' align='right'>
                        <div class='pt11b'><?= number_format($shozei_sei_total) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#E0FFFF' align='right'>
                        <div class='pt11b'><?= number_format($shozei_han_total) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#E0FFFF' align='right'>
                        <div class='pt11b'><?= number_format($all_zeikoka_total_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right'>
                        <div class='pt11b'>　</div>
                    </td>
                </tr>
            </TBODY>
        </table>
            </td></tr>
        </table> <!----------------- ダミーEnd ------------------>
        
        <BR clear='all'>
        
        <table bgcolor='#ffffff' align='left' cellspacing='0' cellpadding='10'>
            <tr><td> <!----------- ダミー(デザイン用) ------------>
        <div class='pt11b'>23.雑収入の内訳</div>
        <table class='winbox_field' width='100%' align='center' bgcolor='black' cellspacing='1' cellpadding='5'>
            <THEAD>
                <!-- テーブル ヘッダーの表示 -->
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center'><div class='pt11b'>摘要</div></td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center'><div class='pt11b'>金額</div></td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center'><div class='pt11b'>備考</div></td>
                </tr>
            </THEAD>
            <TFOOT>
                <!-- 現在はフッターは何もない -->
            </TFOOT>
            <TBODY>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffe4c4' align='right'>
                        <div class='pt11b'>－</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffe4c4' align='right'>
                        <div class='pt11b'>－</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right'>
                        <div class='pt11b'>　</div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right'><div class='pt11b'>合計</div></td>
                    <td class='winbox' nowrap bgcolor='#E0FFFF' align='right'>
                        <div class='pt11b'><?= number_format($eigyo_shueki_total_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right'>
                        <div class='pt11b'>　</div>
                    </td>
                </tr>
            </TBODY>
        </table>
            </td></tr>
        </table> <!----------------- ダミーEnd ------------------>
        
        <BR clear='all'>
        
        <table bgcolor='#ffffff' align='left' cellspacing='0' cellpadding='10'>
            <tr><td> <!----------- ダミー(デザイン用) ------------>
        <div class='pt11b'>24.法人税・住民税及び事業税の内訳</div>
        <table class='winbox_field' width='100%' align='center' bgcolor='black' cellspacing='1' cellpadding='5'>
            <THEAD>
                <!-- テーブル ヘッダーの表示 -->
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center'><div class='pt11b'>摘要</div></td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center'><div class='pt11b'>金額</div></td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center'><div class='pt11b'>備考</div></td>
                </tr>
            </THEAD>
            <TFOOT>
                <!-- 現在はフッターは何もない -->
            </TFOOT>
            <TBODY>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='left'>
                        <div class='pt11b'>当期法人税住民税事業税引当額</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffe4c4' align='right'>
                        <div class='pt11b'><?= number_format($toki_hojin_jigyo) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right'>
                        <div class='pt11b'>　</div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='left'>
                        <div class='pt11b'>預金利息等に対する源泉所得税額</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#E0FFFF' align='right'>
                        <div class='pt11b'><?= number_format($gensen_shotoku_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right'>
                        <div class='pt11b'>　</div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right'><div class='pt11b'>合計</div></td>
                    <td class='winbox' nowrap bgcolor='#E0FFFF' align='right'>
                        <div class='pt11b'><?= number_format($hojin_uchi_total_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right'>
                        <div class='pt11b'>　</div>
                    </td>
                </tr>
            </TBODY>
        </table>
            </td></tr>
        </table> <!----------------- ダミーEnd ------------------>
        <!--
        <form method='post' action='<?php echo $menu->out_self() ?>'>
            <input class='pt10b' type='submit' name='input_data' value='登録' onClick='return data_input_click(this)'>
        </form>
        -->
</body>
</html>
