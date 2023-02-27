<?php
//////////////////////////////////////////////////////////////////////////////
// 月次損益関係 勘定科目組替表                                              //
// Copyright(C) 2018-2022 Norihisa.Ohya norihisa_ooya@nitto-kohki.co.jp     //
// Changed history                                                          //
// 2018/06/26 Created   account_transfer_view.php                           //
// 2018/10/17 19期第2四半期決算の結果を受けて修正                           //
// 2019/01/10 前払消費税をマイナスに19期第3四半期                           //
// 2019/04/09 貯蔵品を2019/03のデータに変更                                 //
// 2019/05/17 日付の取得方法の変更                                          //
// 2019/10/07 貯蔵品を2019/09のデータに変更                                 //
// 2020/04/06 貯蔵品を2020/03のデータに変更                                 //
// 2020/04/13 eCA用のデータ抜出しを追加                                     //
// 2020/06/25 勘定内訳明細書用のデータを追加（20期分）                      //
// 2020/06/30 減価償却費明細書用のデータを追加（20期分）                    //
// 2020/07/08 貯蔵品を2020/06のデータに変更                                 //
// 2021/01/13 各種データを追加（21期12月分）                                //
// 2021/04/08 各種データを追加（21期3月分）                                 //
// 2022/01/12 各種データを追加（22期12月分）                                //
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
    $menu->set_title("第 {$ki} 期　本決算　勘　定　科　目　組　替　表");
} else {
    $menu->set_title("第 {$ki} 期　第{$hanki}四半期　勘　定　科　目　組　替　表");
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

// 現金及び預金合計の計算
$genyo_total_kin = $genkin_kin + $touza_kin + $futsu_kin + $teiki_kin;

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

// 貯蔵品 データはないので月次の貸借対照表と同じ式で直接入力
// 評価切下げの入力 データは無いので月次の貸借対照表と同じ式で直接入力
// 部品仕掛明細の入力 紫の資料から 合計でOK 
// 無形固定資産の取得価額 期首残高、期中増加、期中減少を取得
// 期首は通期一定で増減は都度
$chozo_kin       = 0;
$hyoka_buhin_kin = 0;
$hyoka_zai_kin   = 0;
$tana_kou_kin    = 0;
$tana_gai_kin    = 0;
$tana_ken_kin    = 0;

$den_kishu_kin   = 0;
$shi_kishu_kin   = 0;
$sft_kishu_kin   = 0;
$den_zou_kin     = 0;
$shi_zou_kin     = 0;
$sft_zou_kin     = 0;
$den_gen_kin     = 0;
$shi_gen_kin     = 0;
$sft_gen_kin     = 0;

if ($yyyymm == 201609) {
    $chozo_kin  = 27994100;    // 確定 圧造工具 26,979,200円 貯蔵品 ３点 1,014,800円（151,200円と447,000円と416,700円）
}
if ($yyyymm >= 201610 && $yyyymm <= 201610) {
    $chozo_kin  = 27994100;    // 確定 圧造工具 26,979,200円 貯蔵品 ３点 1,014,800円（151,200円と447,000円と416,700円）
}
if ($yyyymm >= 201611 && $yyyymm <= 201611) {
    $chozo_kin  = 28060500;    // 確定 圧造工具 26,979,200円 貯蔵品 ４点 1,081,300円（151,200円と447,000円と416,700円と66,500円）
}
if ($yyyymm >= 201612 && $yyyymm <= 201701) {
    $chozo_kin  = 28118600;    // 確定 圧造工具 26,979,200円 貯蔵品 ４点 1,139,400円（151,200円と447,000円と416,700円と66,500円と58,000円）
}
if ($yyyymm >= 201702 && $yyyymm <= 201702) {
    $chozo_kin  = 27701900;    // 確定 圧造工具 26,979,200円 貯蔵品 ４点 722,700円（151,200円と447,000円と66,500円と58,000円）
}
if ($yyyymm == 201703) {
    $chozo_kin  = 27170800;    // 確定 圧造工具 26,448,100円 貯蔵品 ４点 722,700円（151,200円と447,000円と66,500円と58,000円）
}
if ($yyyymm >= 201704 && $yyyymm <= 201708) {
    $chozo_kin  = 27170800;    // 確定 圧造工具 26,448,100円 貯蔵品 ４点 722,700円（151,200円と447,000円と66,500円と58,000円）
}
if ($yyyymm == 201709) {
    $chozo_kin  = 31331800;    // 確定 圧造工具 30,609,100円 貯蔵品 ４点 722,700円（151,200円と447,000円と66,500円と58,000円）
}
if ($yyyymm >= 201710 && $yyyymm <= 201802) {
    $chozo_kin  = 31331800;    // 確定 圧造工具 30,609,100円 貯蔵品 ４点 722,700円（151,200円と447,000円と66,500円と58,000円）
}
if ($yyyymm == 201803) {
    $chozo_kin  = 30723300;    // 確定 圧造工具 29,523,100円 貯蔵品 ５点 1,200,200円（151,200円と447,000円と66,500円と58,000円と477,500円）
}

if ($yyyymm >= 201804 && $yyyymm <= 201808) {
    $chozo_kin  = 30723300;    // 確定 圧造工具 29,523,100円 貯蔵品 ５点 1,200,200円（151,200円と447,000円と66,500円と58,000円と477,500円）
}

if ($yyyymm >= 201809 && $yyyymm <= 201902) {
    $chozo_kin  = 31076300;    // 確定 圧造工具 29,528,100円 貯蔵品 ５点 1,548,200円（151,200円と447,000円と66,500円と58,000円と477,500円と348,000円）
}
if ($yyyymm == 201903) {
    $chozo_kin  = 29013600;    // 確定 圧造工具 27,332,900円 貯蔵品 １０点 1,680,700円（151,200円と447,000円と58,000円と477,500円と348,000円と39,800円が5点）
}
if ($yyyymm >= 201904 && $yyyymm <= 201908) {
    $chozo_kin  = 29013600;    // 確定 圧造工具 27,332,900円 貯蔵品 １０点 1,680,700円（151,200円と447,000円と58,000円と477,500円と348,000円と39,800円が5点）
}
if ($yyyymm >= 201909 && $yyyymm <= 202002) {
    $chozo_kin  = 30711100;    // 確定 圧造工具 29,030,400円 貯蔵品 １０点 1,680,700円（151,200円と447,000円と58,000円と477,500円と348,000円と39,800円が5点）
}

// 2020/03
if ($yyyymm >= 202003 && $yyyymm <= 202005) {
    $chozo_kin  = 31847700;    // 確定 圧造工具 30,225,000円 貯蔵品 ９点 1,622,700円（151,200円と447,000円と477,500円と348,000円と39,800円が5点）
}
// 2020/03
if ($yyyymm >= 202006 && $yyyymm <= 202008) {
    $chozo_kin  = 34437700;    // 確定 圧造工具 30,225,000円 貯蔵品 ９点 4,212,700円（151,200円と447,000円と477,500円と348,000円と39,800円が5点と2,590,000円）
}
// 2020/10/07 追加 仮払金→棚卸資産
if ($yyyymm >= 202009 && $yyyymm <= 202102) {
    $chozo_kin  = 35225500;    // 確定 圧造工具 31,012,800円 貯蔵品 ９点 4,212,700円（151,200円と447,000円と477,500円と348,000円と39,800円が5点と2,590,000円）
}
// 2021/04/08 追加 仮払金→棚卸資産
if ($yyyymm >= 202103 && $yyyymm <= 202105) {
    $chozo_kin  = 34201500;    // 確定 圧造工具 29,988,800円 貯蔵品 ９点 4,212,700円（151,200円と447,000円と477,500円と348,000円と39,800円が5点と2,590,000円）
}
// 2021/07/07 追加 仮払金→棚卸資産
if ($yyyymm >= 202106 && $yyyymm <= 202108) {
    $chozo_kin  = 34390591;    // 確定 圧造工具 29,988,800円 貯蔵品 ９点 4,401,791円（151,200円と447,000円と477,500円と348,000円と39,800円が5点と2,590,000円と189,091円）
}
// 2021/07/07 追加 仮払金→棚卸資産
if ($yyyymm >= 202109 && $yyyymm <= 202202) {
    $chozo_kin  = 35918291;    // 確定 圧造工具 31,516,500円 貯蔵品 ９点 4,401,791円（151,200円と447,000円と477,500円と348,000円と39,800円が5点と2,590,000円と189,091円）
}
// 2021/07/07 追加 仮払金→棚卸資産
if ($yyyymm >= 202203 && $yyyymm <= 202205) {
    $chozo_kin  = 33416691;    // 確定 圧造工具 33,416,691円 貯蔵品 ９点 4,401,791円（151,200円と447,000円と477,500円と348,000円と39,800円が5点と2,590,000円と189,091円）
}

// 棚卸等入力
if ($yyyymm >= 201806 && $yyyymm <= 201808) {
    $hyoka_buhin_kin = 24145944;    // 評価切下げ 部品 24,145,944円
    $hyoka_zai_kin   = 5054790;     // 評価切下げ 原材料 5,054,790円
    $tana_kou_kin    = 58478522;    // 工作仕掛 2018/06
    $tana_gai_kin    = 115024519;   // 外注仕掛 2018/06
    $tana_ken_kin    = 6794210;     // 検査仕掛 2018/06
}
if ($yyyymm >= 201809 && $yyyymm <= 201811) {
    $hyoka_buhin_kin = 20982681;    // 評価切下げ 部品 20,982,681円
    $hyoka_zai_kin   = 3245692;     // 評価切下げ 原材料 3,245,692円
    $tana_kou_kin    = 39056743;    // 工作仕掛 2018/09
    $tana_gai_kin    = 106305243;   // 外注仕掛 2018/09
    $tana_ken_kin    = 8507486;     // 検査仕掛 2018/09
}
if ($yyyymm >= 201812 && $yyyymm <= 201902) {
    $hyoka_buhin_kin = 21353386;    // 評価切下げ 部品 21,353,386円
    $hyoka_zai_kin   = 4301424;     // 評価切下げ 原材料 4,301,424円
    $tana_kou_kin    = 51762589;    // 工作仕掛 2018/12
    $tana_gai_kin    = 120393767;   // 外注仕掛 2018/12
    $tana_ken_kin    = 18413966;    // 検査仕掛 2018/12
}

if ($yyyymm >= 201903 && $yyyymm <= 201905) {
    $hyoka_buhin_kin = 27099309;    // 評価切下げ 部品 27,099,309円
    $hyoka_zai_kin   = 3837098;     // 評価切下げ 原材料 3,837,098円
    $tana_kou_kin    = 43499362;    // 工作仕掛 2019/03
    $tana_gai_kin    = 118595019;   // 外注仕掛 2019/03
    $tana_ken_kin    = 6129984;     // 検査仕掛 2019/03
}
if ($yyyymm >= 201906 && $yyyymm <= 201908) {
    $hyoka_buhin_kin = 27338977;    // 評価切下げ 部品 27,338,977円
    $hyoka_zai_kin   = 3632576;     // 評価切下げ 原材料 3,632,576円
    $tana_kou_kin    = 44994415;    // 工作仕掛 2019/06
    $tana_gai_kin    = 120740158;   // 外注仕掛 2019/06
    $tana_ken_kin    = 7774737;     // 検査仕掛 2019/06
}
if ($yyyymm >= 201909 && $yyyymm <= 201911) {
    $hyoka_buhin_kin = 26052650;    // 評価切下げ 部品 26,052,650円
    $hyoka_zai_kin   = 3462809;     // 評価切下げ 原材料 3,462,809円
    $tana_kou_kin    = 34775430;    // 工作仕掛 2019/09
    $tana_gai_kin    = 86308322;    // 外注仕掛 2019/09
    $tana_ken_kin    = 38770378;    // 検査仕掛 2019/09
}
if ($yyyymm >= 201912 && $yyyymm <= 202002) {
    $hyoka_buhin_kin = 25648144;    // 評価切下げ 部品 25,648,144円
    $hyoka_zai_kin   = 3145474;     // 評価切下げ 原材料 4,301,424円
    $tana_kou_kin    = 48147770;    // 工作仕掛 2019/12
    $tana_gai_kin    = 144697973;   // 外注仕掛 2019/12
    $tana_ken_kin    = 6235679;     // 検査仕掛 2019/12
}
if ($yyyymm >= 202003 && $yyyymm <= 202005) {
    $hyoka_buhin_kin = 22146591;    // 評価切下げ 部品 22,146,591円
    $hyoka_zai_kin   = 2936551;     // 評価切下げ 原材料 2,936,551円
    $tana_kou_kin    = 43412814;    // 工作仕掛 2020/03
    $tana_gai_kin    = 144834707;   // 外注仕掛 2020/03
    $tana_ken_kin    = 2043234;     // 検査仕掛 2020/03
}
if ($yyyymm >= 202006 && $yyyymm <= 202008) {
    $hyoka_buhin_kin = 29663899;    // 評価切下げ 部品 29,663,899円
    $hyoka_zai_kin   = 2541727;     // 評価切下げ 原材料 2,541,727円
    $tana_kou_kin    = 33562592;    // 工作仕掛 2020/06
    $tana_gai_kin    = 157623908;   // 外注仕掛 2020/06
    $tana_ken_kin    = 4483191;     // 検査仕掛 2020/06
}
if ($yyyymm >= 202009 && $yyyymm <= 202011) {
    $hyoka_buhin_kin = 18889691;    // 評価切下げ 部品 18,889,691円
    $hyoka_zai_kin   = 2247663;     // 評価切下げ 原材料 2,247,663円
    $tana_kou_kin    = 44194009;    // 工作仕掛 2020/09
    $tana_gai_kin    = 149995875;   // 外注仕掛 2020/09
    $tana_ken_kin    = 6145842;     // 検査仕掛 2020/09
}
if ($yyyymm >= 202012 && $yyyymm <= 202102) {
    $hyoka_buhin_kin = 20714112;    // 評価切下げ 部品 20,714,112円
    $hyoka_zai_kin   = 3098026;     // 評価切下げ 原材料 3,098,026円
    $tana_kou_kin    = 39886443;    // 工作仕掛 2020/12
    $tana_gai_kin    = 180087279;   // 外注仕掛 2020/12
    $tana_ken_kin    = 3318087;     // 検査仕掛 2020/12
}
if ($yyyymm >= 202103 && $yyyymm <= 202105) {
    $hyoka_buhin_kin = 23313658;    // 評価切下げ 部品 23,313,658円
    $hyoka_zai_kin   = 3853278;     // 評価切下げ 原材料 3,853,278円
    $tana_kou_kin    = 52966381;    // 工作仕掛 2021/03
    $tana_gai_kin    = 178610899;   // 外注仕掛 2021/03
    $tana_ken_kin    = 4799597;     // 検査仕掛 2021/03
}
if ($yyyymm >= 202106 && $yyyymm <= 202108) {
    $hyoka_buhin_kin = 29379248;    // 評価切下げ 部品 29,379,248円
    $hyoka_zai_kin   = 3226918;     // 評価切下げ 原材料 3,226,918円
    $tana_kou_kin    = 53398839;    // 工作仕掛 2021/06
    $tana_gai_kin    = 191936510;   // 外注仕掛 2021/06
    $tana_ken_kin    = 3863607;     // 検査仕掛 2021/06
}
if ($yyyymm >= 202109 && $yyyymm <= 202111) {
    $hyoka_buhin_kin = 27697190;    // 評価切下げ 部品 27,697,190円
    $hyoka_zai_kin   = 3183503;     // 評価切下げ 原材料 3,183,503円
    $tana_kou_kin    = 35205657;    // 工作仕掛 2021/09
    $tana_gai_kin    = 174850313;   // 外注仕掛 2021/09
    $tana_ken_kin    = 0;           // 検査仕掛 2021/09
}
if ($yyyymm >= 202112 && $yyyymm <= 202202) {
    $hyoka_buhin_kin = 23684417;    // 評価切下げ 部品 23,684,417円
    $hyoka_zai_kin   = 4110029;     // 評価切下げ 原材料 4,110,029円
    $tana_kou_kin    = 59268755;    // 工作仕掛 2021/12
    $tana_gai_kin    = 182641926;   // 外注仕掛 2021/12
    $tana_ken_kin    = 8374184;     // 検査仕掛 2021/12
}
if ($yyyymm >= 202203 && $yyyymm <= 202205) {
    $hyoka_buhin_kin = 22836045;    // 評価切下げ 部品 22,836,045円
    $hyoka_zai_kin   = 4420149;     // 評価切下げ 原材料 4,420,149円
    $tana_kou_kin    = 56931578;    // 工作仕掛 2022/03
    $tana_gai_kin    = 218422532;   // 外注仕掛 2022/03
    $tana_ken_kin    = 0;           // 検査仕掛 2022/03
}

// 無形固定資産の取得価額 期首残高、期中増加、期中減少を入力
if ($yyyymm >= 201803 && $yyyymm <= 201805) {
    $den_kishu_kin   = 1224000;     // 電話期首残高    1,224,000円
    $shi_kishu_kin   = 13120400;    // 施設期首残高   13,120,400円
    $sft_kishu_kin   = 16163122;    // ソフト期首残高 16,163,122円
    $den_zou_kin     = 0;           // 電話期中増加            0円
    $shi_zou_kin     = 0;           // 施設期中増加            0円
    $sft_zou_kin     = 7565000;     // ソフト期中増加  7,565,000円
    $den_gen_kin     = 0;           // 電話期中減少            0円
    $shi_gen_kin     = 0;           // 施設期中減少            0円
    $sft_gen_kin     = 0;           // ソフト期中減少          0円
}
if ($yyyymm >= 201806 && $yyyymm <= 201808) {
    $den_kishu_kin   = 1224000;     // 電話期首残高    1,224,000円
    $shi_kishu_kin   = 13120400;    // 施設期首残高   13,120,400円
    $sft_kishu_kin   = 23728122;    // ソフト期首残高 23,728,122円
    $den_zou_kin     = 0;           // 電話期中増加            0円
    $shi_zou_kin     = 0;           // 施設期中増加            0円
    $sft_zou_kin     = 0;           // ソフト期中増加          0円
    $den_gen_kin     = 0;           // 電話期中減少            0円
    $shi_gen_kin     = 0;           // 施設期中減少            0円
    $sft_gen_kin     = 0;           // ソフト期中減少          0円
}
if ($yyyymm >= 201809 && $yyyymm <= 201811) {
    $den_kishu_kin   = 1224000;     // 電話期首残高    1,224,000円
    $shi_kishu_kin   = 13120400;    // 施設期首残高   13,120,400円
    $sft_kishu_kin   = 23728122;    // ソフト期首残高 23,728,122円
    $den_zou_kin     = 0;           // 電話期中増加            0円
    $shi_zou_kin     = 0;           // 施設期中増加            0円
    $sft_zou_kin     = 0;           // ソフト期中増加          0円
    $den_gen_kin     = 0;           // 電話期中減少            0円
    $shi_gen_kin     = 0;           // 施設期中減少            0円
    $sft_gen_kin     = 0;           // ソフト期中減少          0円
}
if ($yyyymm >= 201812 && $yyyymm <= 201902) {
    $den_kishu_kin   = 1224000;     // 電話期首残高    1,224,000円
    $shi_kishu_kin   = 13120400;    // 施設期首残高   13,120,400円
    $sft_kishu_kin   = 23728122;    // ソフト期首残高 23,728,122円
    $den_zou_kin     = 0;           // 電話期中増加            0円
    $shi_zou_kin     = 0;           // 施設期中増加            0円
    $sft_zou_kin     = 0;           // ソフト期中増加          0円
    $den_gen_kin     = 0;           // 電話期中減少            0円
    $shi_gen_kin     = 0;           // 施設期中減少            0円
    $sft_gen_kin     = 0;           // ソフト期中減少          0円
}
if ($yyyymm >= 201903 && $yyyymm <= 201905) {
    $den_kishu_kin   = 1224000;     // 電話期首残高    1,224,000円
    $shi_kishu_kin   = 13120400;    // 施設期首残高   13,120,400円
    $sft_kishu_kin   = 23728122;    // ソフト期首残高 23,728,122円
    $den_zou_kin     = 0;           // 電話期中増加            0円
    $shi_zou_kin     = 0;           // 施設期中増加            0円
    $sft_zou_kin     = 0;           // ソフト期中増加          0円
    $den_gen_kin     = 0;           // 電話期中減少            0円
    $shi_gen_kin     = 0;           // 施設期中減少            0円
    $sft_gen_kin     = 0;           // ソフト期中減少          0円
}
if ($yyyymm >= 201906 && $yyyymm <= 201908) {
    $den_kishu_kin   = 1224000;     // 電話期首残高    1,224,000円
    $shi_kishu_kin   = 13120400;    // 施設期首残高   13,120,400円
    $sft_kishu_kin   = 23728122;    // ソフト期首残高 23,728,122円
    $den_zou_kin     = 0;           // 電話期中増加            0円
    $shi_zou_kin     = 0;           // 施設期中増加            0円
    $sft_zou_kin     = 0;           // ソフト期中増加          0円
    $den_gen_kin     = 0;           // 電話期中減少            0円
    $shi_gen_kin     = 0;           // 施設期中減少            0円
    $sft_gen_kin     = 0;           // ソフト期中減少          0円
}
if ($yyyymm >= 201909 && $yyyymm <= 201911) {
    $den_kishu_kin   = 1224000;     // 電話期首残高    1,224,000円
    $shi_kishu_kin   = 13120400;    // 施設期首残高   13,120,400円
    $sft_kishu_kin   = 23728122;    // ソフト期首残高 23,728,122円
    $den_zou_kin     = 0;           // 電話期中増加            0円
    $shi_zou_kin     = 0;           // 施設期中増加            0円
    $sft_zou_kin     = 0;           // ソフト期中増加          0円
    $den_gen_kin     = 0;           // 電話期中減少            0円
    $shi_gen_kin     = 0;           // 施設期中減少            0円
    $sft_gen_kin     = 0;           // ソフト期中減少          0円
}
if ($yyyymm >= 201912 && $yyyymm <= 202002) {
    $den_kishu_kin   = 1224000;     // 電話期首残高    1,224,000円
    $shi_kishu_kin   = 13120400;    // 施設期首残高   13,120,400円
    $sft_kishu_kin   = 23728122;    // ソフト期首残高 23,728,122円
    $den_zou_kin     = 0;           // 電話期中増加            0円
    $shi_zou_kin     = 0;           // 施設期中増加            0円
    $sft_zou_kin     = 0;           // ソフト期中増加          0円
    $den_gen_kin     = 0;           // 電話期中減少            0円
    $shi_gen_kin     = 0;           // 施設期中減少            0円
    $sft_gen_kin     = 0;           // ソフト期中減少          0円
}
if ($yyyymm >= 202003 && $yyyymm <= 202005) {
    $den_kishu_kin   = 1224000;     // 電話期首残高    1,224,000円
    $shi_kishu_kin   = 13120400;    // 施設期首残高   13,120,400円
    $sft_kishu_kin   = 23728122;    // ソフト期首残高 23,728,122円
    $den_zou_kin     = 0;           // 電話期中増加            0円
    $shi_zou_kin     = 0;           // 施設期中増加            0円
    $sft_zou_kin     = 0;           // ソフト期中増加          0円
    $den_gen_kin     = 0;           // 電話期中減少            0円
    $shi_gen_kin     = 0;           // 施設期中減少            0円
    $sft_gen_kin     = 0;           // ソフト期中減少          0円
}
if ($yyyymm >= 202006 && $yyyymm <= 202008) {
    $den_kishu_kin   = 1224000;     // 電話期首残高    1,224,000円
    $shi_kishu_kin   = 13120400;    // 施設期首残高   13,120,400円
    $sft_kishu_kin   = 23728122;    // ソフト期首残高 23,728,122円
    $den_zou_kin     = 0;           // 電話期中増加            0円
    $shi_zou_kin     = 0;           // 施設期中増加            0円
    $sft_zou_kin     = 17639120;    // ソフト期中増加 17,639,120円
    $den_gen_kin     = 0;           // 電話期中減少            0円
    $shi_gen_kin     = 0;           // 施設期中減少            0円
    $sft_gen_kin     = 0;           // ソフト期中減少          0円
}
if ($yyyymm >= 202009 && $yyyymm <= 202011) {
    $den_kishu_kin   = 1224000;     // 電話期首残高    1,224,000円
    $shi_kishu_kin   = 13120400;    // 施設期首残高   13,120,400円
    $sft_kishu_kin   = 23728122;    // ソフト期首残高 23,728,122円
    $den_zou_kin     = 0;           // 電話期中増加            0円
    $shi_zou_kin     = 0;           // 施設期中増加            0円
    $sft_zou_kin     = 20537520;    // ソフト期中増加 20,537,520円
    $den_gen_kin     = 0;           // 電話期中減少            0円
    $shi_gen_kin     = 0;           // 施設期中減少            0円
    $sft_gen_kin     = 0;           // ソフト期中減少          0円
}
if ($yyyymm >= 202012 && $yyyymm <= 202102) {
    $den_kishu_kin   = 1224000;     // 電話期首残高    1,224,000円
    $shi_kishu_kin   = 13120400;    // 施設期首残高   13,120,400円
    $sft_kishu_kin   = 23728122;    // ソフト期首残高 23,728,122円
    $den_zou_kin     = 0;           // 電話期中増加            0円
    $shi_zou_kin     = 0;           // 施設期中増加            0円
    $sft_zou_kin     = 20537520;    // ソフト期中増加 20,537,520円
    $den_gen_kin     = 0;           // 電話期中減少            0円
    $shi_gen_kin     = 0;           // 施設期中減少            0円
    $sft_gen_kin     = 0;           // ソフト期中減少          0円
}
if ($yyyymm >= 202103 && $yyyymm <= 202105) {
    $den_kishu_kin   = 1224000;     // 電話期首残高    1,224,000円
    $shi_kishu_kin   = 13120400;    // 施設期首残高   13,120,400円
    $sft_kishu_kin   = 23728122;    // ソフト期首残高 23,728,122円
    $den_zou_kin     = 0;           // 電話期中増加            0円
    $shi_zou_kin     = 0;           // 施設期中増加            0円
    $sft_zou_kin     = 20537520;    // ソフト期中増加 20,537,520円
    $den_gen_kin     = 0;           // 電話期中減少            0円
    $shi_gen_kin     = 0;           // 施設期中減少            0円
    $sft_gen_kin     = 0;           // ソフト期中減少          0円
}
if ($yyyymm >= 202106 && $yyyymm <= 202108) {
    $den_kishu_kin   = 1224000;     // 電話期首残高    1,224,000円
    $shi_kishu_kin   = 13120400;    // 施設期首残高   13,120,400円
    $sft_kishu_kin   = 44265642;    // ソフト期首残高 44,265,642円
    $den_zou_kin     = 0;           // 電話期中増加            0円
    $shi_zou_kin     = 0;           // 施設期中増加            0円
    $sft_zou_kin     = 547000;      // ソフト期中増加    547,000円
    $den_gen_kin     = 0;           // 電話期中減少            0円
    $shi_gen_kin     = 0;           // 施設期中減少            0円
    $sft_gen_kin     = 0;           // ソフト期中減少          0円
}
if ($yyyymm >= 202109 && $yyyymm <= 202111) {
    $den_kishu_kin   = 1224000;     // 電話期首残高    1,224,000円
    $shi_kishu_kin   = 13120400;    // 施設期首残高   13,120,400円
    $sft_kishu_kin   = 44265642;    // ソフト期首残高 44,265,642円
    $den_zou_kin     = 0;           // 電話期中増加            0円
    $shi_zou_kin     = 0;           // 施設期中増加            0円
    $sft_zou_kin     = 547000;      // ソフト期中増加    547,000円
    $den_gen_kin     = 0;           // 電話期中減少            0円
    $shi_gen_kin     = 0;           // 施設期中減少            0円
    $sft_gen_kin     = 0;           // ソフト期中減少          0円
}
if ($yyyymm >= 202112 && $yyyymm <= 202202) {
    $den_kishu_kin   = 1224000;     // 電話期首残高    1,224,000円
    $shi_kishu_kin   = 13120400;    // 施設期首残高   13,120,400円
    $sft_kishu_kin   = 44265642;    // ソフト期首残高 44,265,642円
    $den_zou_kin     = 0;           // 電話期中増加            0円
    $shi_zou_kin     = 0;           // 施設期中増加            0円
    $sft_zou_kin     = 547000;      // ソフト期中増加    547,000円
    $den_gen_kin     = 0;           // 電話期中減少            0円
    $shi_gen_kin     = 0;           // 施設期中減少            0円
    $sft_gen_kin     = 0;           // ソフト期中減少          0円
}
if ($yyyymm >= 202203 && $yyyymm <= 202205) {
    $den_kishu_kin   = 1224000;     // 電話期首残高    1,224,000円
    $shi_kishu_kin   = 13120400;    // 施設期首残高   13,120,400円
    $sft_kishu_kin   = 44265642;    // ソフト期首残高 44,265,642円
    $den_zou_kin     = 0;           // 電話期中増加            0円
    $shi_zou_kin     = 0;           // 施設期中増加            0円
    $sft_zou_kin     = 547000;      // ソフト期中増加    547,000円
    $den_gen_kin     = 0;           // 電話期中減少            0円
    $shi_gen_kin     = 0;           // 施設期中減少            0円
    $sft_gen_kin     = 0;           // ソフト期中減少          0円
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
*/

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

// eca用法人税、住民税及び事業税
$eca_hojin_zeito_total_kin = $hojin_jyumin_zei_kin + $jigyo_zei_kin;

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
    $item[72]  = "評価切下げ部品";
    $item[73]  = "評価切下げ材料";
    $item[74]  = "工作仕掛明細";
    $item[75]  = "外注仕掛明細";
    $item[76]  = "検査仕掛明細";
    $item[77]  = "電話期首残高";
    $item[78]  = "施設期首残高";
    $item[79]  = "ソフト期首残高";
    $item[80]  = "電話期中増加";
    $item[81]  = "施設期中増加";
    $item[82]  = "ソフト期中増加";
    $item[83]  = "電話期中減少";
    $item[84]  = "施設期中減少";
    $item[85]  = "ソフト期中減少";
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
    $input_data[72]  = $hyoka_buhin_kin;
    $input_data[73]  = $hyoka_zai_kin;
    $input_data[74]  = $tana_kou_kin;
    $input_data[75]  = $tana_gai_kin;
    $input_data[76]  = $tana_ken_kin;
    $input_data[77]  = $den_kishu_kin;
    $input_data[78]  = $shi_kishu_kin;
    $input_data[79]  = $sft_kishu_kin;
    $input_data[80]  = $den_zou_kin;
    $input_data[81]  = $shi_zou_kin;
    $input_data[82]  = $sft_zou_kin;
    $input_data[83]  = $den_gen_kin;
    $input_data[84]  = $shi_gen_kin;
    $input_data[85]  = $sft_gen_kin;
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
-->
</style>
</head>
<body>
    <center>
<?= $menu->out_title_border() ?>
        <?php
            //  bgcolor='#ceffce' 黄緑
            //  bgcolor='#ffffc6' 薄い黄色
            //  bgcolor='#d6d3ce' Win グレイ
        ?>
    <!--------------- ここから本文の表を表示する -------------------->
        <table bgcolor='#ffffff' align='center' cellspacing='0' cellpadding='10'>
            <tr><td> <!----------- ダミー(デザイン用) ------------>
        <center>（貸借対照表）</center>
        <table class='winbox_field' width='100%' align='center' bgcolor='black' cellspacing='1' cellpadding='5'>
            <THEAD>
                <!-- テーブル ヘッダーの表示 -->
                <tr>
                    <th class='winbox' nowrap rowspan='2'>　</th>
                    <th class='winbox' nowrap colspan='4'>試算表</th>
                    <th class='winbox' nowrap colspan='3'>決算書(B/S)</th>
                </tr>
                <tr>
                    <th class='winbox' nowrap colspan='2'>勘定科目</th>
                    <th class='winbox' nowrap>金額</th>
                    <th class='winbox' nowrap>備考</th>
                    <th class='winbox' nowrap colspan='2'>勘定科目</th>
                    <th class='winbox' nowrap>金額</th>
                </tr>
            </THEAD>
            <TFOOT>
                <!-- 現在はフッターは何もない -->
            </TFOOT>
            <TBODY>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' rowspan='5' valign='top'>
                        <div class='pt10b'>１</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' rowspan='5' valign='top'>
                        <div class='pt10b'>現金</div><BR>
                        <div class='pt10b'>預金</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#fde9d9'>
                        <div class='pt10b'>現金</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ccffcc' align='right'>
                        <div class='pt11b'><?= number_format($genkin_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' rowspan='5' valign='top'>
                        <div class='pt10b'>流動資産</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>現金及び預金</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffc6' align='right'>
                        <div class='pt11b'><?= number_format($genyo_total_kin) ?></div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#fde9d9'>
                        <div class='pt10b'>当座預金</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ccffcc' align='right'>
                        <div class='pt11b'><?= number_format($touza_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>　</div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#fde9d9'>
                        <div class='pt10b'>普通預金</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ccffcc' align='right'>
                        <div class='pt11b'><?= number_format($futsu_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>　</div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#fde9d9'>
                        <div class='pt10b'>定期預金</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ccffcc' align='right'>
                        <div class='pt11b'><?= number_format($teiki_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>大口定期</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>　</div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>合計</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right'>
                        <div class='pt11b'><?= number_format($genyo_total_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>合計</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right'>
                        <div class='pt11b'><?= number_format($genyo_total_kin) ?></div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' rowspan='8' valign='top'>
                        <div class='pt10b'>２</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' rowspan='8' valign='top'>
                        <div class='pt10b'>在庫</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>製品</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right'>
                        <div class='pt11b'><?= number_format($seihin_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' rowspan='8' valign='top'>
                        <div class='pt10b'>流動資産</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>製品</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right'>
                        <div class='pt11b'><?= number_format($seihin_total_kin) ?></div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>製品仕掛品</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ccffcc' align='right'>
                        <div class='pt11b'><?= number_format($seihinsi_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>仕掛品</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right'>
                        <div class='pt11b'><?= number_format($sikakari_total_kin) ?></div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>部品</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ccffcc' align='right'>
                        <div class='pt11b'><?= number_format($buhin_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>原材料及び貯蔵品</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right'>
                        <div class='pt11b'><?= number_format($gencho_total_kin) ?></div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>部品仕掛品</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ccffcc' align='right'>
                        <div class='pt11b'><?= number_format($buhinsi_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>　</div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>原材料</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ccffcc' align='right'>
                        <div class='pt11b'><?= number_format($genzai_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>　</div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>その他の棚卸品</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ccffcc' align='right'>
                        <div class='pt11b'><?= number_format($sonotatana_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>　</div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>貯蔵品</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ccffcc' align='right'>
                        <div class='pt11b'><?= number_format($chozo_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>　</div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>合計</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right'>
                        <div class='pt11b'><?= number_format($zaiko_total_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>合計</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right'>
                        <div class='pt11b'><?= number_format($ryudozaiko_total_kin) ?></div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' rowspan='8' valign='top'>
                        <div class='pt10b'>３</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' rowspan='8' valign='top'>
                        <div class='pt10b'>流動資産</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#fde9d9'>
                        <div class='pt10b'>有償支給未収入金</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ccffcc' align='right'>
                        <div class='pt11b'><?= number_format($yumi_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' rowspan='8' valign='top'>
                        <div class='pt10b'>流動資産</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>未収入金</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffc6' align='right'>
                        <div class='pt11b'><?= number_format($ryu_mishu_kin) ?></div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#fde9d9'>
                        <div class='pt10b'>未収入金</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ccffcc' align='right'>
                        <div class='pt11b'><?= number_format($mishu_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right'>
                        <div class='pt11b'>　</div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#fde9d9'>
                        <div class='pt10b'>未収収益</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ccffcc' align='right'>
                        <div class='pt11b'><?= number_format($mishueki_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right'>
                        <div class='pt11b'>　</div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>合計</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right'>
                        <div class='pt11b'><?= number_format($ryu_mishu_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>合計</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right'>
                        <div class='pt11b'><?= number_format($ryu_mishu_total_kin) ?></div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#fde9d9'>
                        <div class='pt10b'>立替金</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ccffcc' align='right'>
                        <div class='pt11b'><?= number_format($tatekae_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>その他流動資産</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffc6' align='right'>
                        <div class='pt11b'><?= number_format($hokaryudo_total_kin) ?></div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#fde9d9'>
                        <div class='pt10b'>仮払金</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ccffcc' align='right'>
                        <div class='pt11b'><?= number_format($karibara_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>　</div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>その他の流動資産</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ccffcc' align='right'>
                        <div class='pt11b'><?= number_format($hokaryudo_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>　</div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>合計</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right'>
                        <div class='pt11b'><?= number_format($hokaryudo_total_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>合計</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right'>
                        <div class='pt11b'><?= number_format($hokaryudo_all_kin) ?></div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' rowspan='10' valign='top'>
                        <div class='pt10b'>４</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' rowspan='10' valign='top'>
                        <div class='pt10b'>有形</div><BR>
                        <div class='pt10b'>固定資産</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>建物</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ccffcc' align='right'>
                        <div class='pt11b'><?= number_format($tatemono_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#cc99ff' align='right'>
                        <div class='pt11b'><?= number_format($tate_gen_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' rowspan='10' valign='top'>
                        <div class='pt10b'>有形</div><BR>
                        <div class='pt10b'>固定資産</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>建物</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffc6' align='right'>
                        <div class='pt11b'><?= number_format($tate_all_shisan_kin) ?></div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>設備</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ccffcc' align='right'>
                        <div class='pt11b'><?= number_format($fuzoku_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#cc99ff' align='right'>
                        <div class='pt11b'><?= number_format($fuzoku_gen_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>（設備加算）</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>　</div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>構築物</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ccffcc' align='right'>
                        <div class='pt11b'><?= number_format($kouchiku_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#cc99ff' align='right'>
                        <div class='pt11b'><?= number_format($kouchiku_gen_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>構築物</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffc6' align='right'>
                        <div class='pt11b'><?= number_format($kouchiku_shisan_kin) ?></div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>機械装置</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ccffcc' align='right'>
                        <div class='pt11b'><?= number_format($kikai_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#cc99ff' align='right'>
                        <div class='pt11b'><?= number_format($kikai_gen_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>機械及び装置</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffc6' align='right'>
                        <div class='pt11b'><?= number_format($kikai_shisan_kin) ?></div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>車輌運搬具</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ccffcc' align='right'>
                        <div class='pt11b'><?= number_format($sharyo_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#cc99ff' align='right'>
                        <div class='pt11b'><?= number_format($sharyo_gen_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>車輌運搬具</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffc6' align='right'>
                        <div class='pt11b'><?= number_format($sharyo_shisan_kin) ?></div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>器具工具</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ccffcc' align='right'>
                        <div class='pt11b'><?= number_format($kigu_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#cc99ff' align='right'>
                        <div class='pt11b'><?= number_format($kigu_gen_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>工具器具及び備品</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffc6' align='right'>
                        <div class='pt11b'><?= number_format($jyubihin_all_shisan_kin) ?></div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>什器備品</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ccffcc' align='right'>
                        <div class='pt11b'><?= number_format($jyuki_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#cc99ff' align='right'>
                        <div class='pt11b'><?= number_format($jyuki_gen_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>リース資産</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffc6' align='right'>
                        <div class='pt11b'><?= number_format($lease_shisan_kin) ?></div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>リース資産</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ccffcc' align='right'>
                        <div class='pt11b'><?= number_format($lease_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#cc99ff' align='right'>
                        <div class='pt11b'><?= number_format($lease_gen_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>合計</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right'>
                        <div class='pt11b'><?= number_format($boka_totai_kin) ?></div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>減価償却累計額</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ccffcc' align='right'>
                        <div class='pt11b'><?= number_format($gensyo_total_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>　</div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>簿価</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right'>
                        <div class='pt11b'><?= number_format($boka_totai_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>減価償却累計額</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right'>
                        <div class='pt11b'><?= number_format($gensyo_total_mi_kin) ?></div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' rowspan='3' valign='top'>
                        <div class='pt10b'>５</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' rowspan='3' valign='top'>
                        <div class='pt10b'>無形</div><BR>
                        <div class='pt10b'>固定資産</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#fde9d9'>
                        <div class='pt10b'>電話加入権</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ccffcc' align='right'>
                        <div class='pt11b'><?= number_format($denwa_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' rowspan='3' valign='top'>
                        <div class='pt10b'>無形</div><BR>
                        <div class='pt10b'>固定資産</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>電話加入権</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right'>
                        <div class='pt11b'><?= number_format($denwa_kin) ?></div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right'>
                        <div class='pt11b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>　</div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>合計</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right'>
                        <div class='pt11b'><?= number_format($denwa_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>合計</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right'>
                        <div class='pt11b'><?= number_format($denwa_kin) ?></div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' rowspan='3' valign='top'>
                        <div class='pt10b'>６</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' rowspan='3' valign='top'>
                        <div class='pt10b'>投資等</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#fde9d9'>
                        <div class='pt10b'>出資金</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ccffcc' align='right'>
                        <div class='pt11b'><?= number_format($shussi_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' rowspan='3' valign='top'>
                        <div class='pt10b'>投資等</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>その他の投資等</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right'>
                        <div class='pt11b'><?= number_format($toushi_total_kin) ?></div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>差入敷金保証金</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ccffcc' align='right'>
                        <div class='pt11b'><?= number_format($hosyo_kin) ?></div>
                    </td>   
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>　</div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>合計</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right'>
                        <div class='pt11b'><?= number_format($toushi_total_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>合計</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right'>
                        <div class='pt11b'><?= number_format($toushi_total_kin) ?></div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' rowspan='15' valign='top'>
                        <div class='pt10b'>７</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' rowspan='5' valign='top'>
                        <div class='pt10b'>流動負債</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>仮払消費税等</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ccffcc' align='right'>
                        <div class='pt11b'><?= number_format($kari_zei_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>(仮消 輸入含む)</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' rowspan='5' valign='top'>
                        <div class='pt10b'>流動負債</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>未払消費税等</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffc6' align='right'>
                        <div class='pt11b'><?= number_format($mihazei_total_kin) ?></div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>前払消費税</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ccffcc' align='right'>
                        <div class='pt11b'><?= number_format($mae_zei_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right'>
                        <div class='pt11b'>　</div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>仮受消費税等</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ccffcc' align='right'>
                        <div class='pt11b'><?= number_format($kariuke_zei_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right'>
                        <div class='pt11b'>　</div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>未払消費税等</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ccffcc' align='right'>
                        <div class='pt11b'><?= number_format($miharai_zei_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>(四半期計上分)</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right'>
                        <div class='pt11b'>　</div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>合計</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right'>
                        <div class='pt11b'><?= number_format($mihazei_total_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>合計</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right'>
                        <div class='pt11b'><?= number_format($mihazei_total_kin) ?></div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' rowspan='10' valign='top'>
                        <div class='pt10b'>流動負債</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#fde9d9'>
                        <div class='pt10b'>買掛金</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ccffcc' align='right'>
                        <div class='pt11b'><?= number_format($kaikake_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' rowspan='3' valign='top'>
                        <div class='pt10b'>流動負債</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>買掛金</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffc6' align='right'>
                        <div class='pt11b'><?= number_format($kaikake_total_kin) ?></div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>買掛金期日振込</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ccffcc' align='right'>
                        <div class='pt11b'><?= number_format($kaikake_kiji_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>　</div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>合計</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right'>
                        <div class='pt11b'><?= number_format($kaikake_total_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>合計</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right'>
                        <div class='pt11b'><?= number_format($kaikake_total_kin) ?></div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#fde9d9'>
                        <div class='pt10b'>未払金</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ccffcc' align='right'>
                        <div class='pt11b'><?= number_format($miharai_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' rowspan='3' valign='top'>
                        <div class='pt10b'>流動負債</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>未払金</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffc6' align='right'>
                        <div class='pt11b'><?= number_format($miharai_total_kin) ?></div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>未払金期日指定</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ccffcc' align='right'>
                        <div class='pt11b'><?= number_format($miharai_kiji_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>　</div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>合計</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right'>
                        <div class='pt11b'><?= number_format($miharai_total_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>合計</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right'>
                        <div class='pt11b'><?= number_format($miharai_total_kin) ?></div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>前受金</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ccffcc' align='right'>
                        <div class='pt11b'><?= number_format($maeuke_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' rowspan='3' valign='top'>
                        <div class='pt10b'>流動負債</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>その他の流動負債</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffc6' align='right'>
                        <div class='pt11b'><?= number_format($sonota_ryudo_total_kin) ?></div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>その他の流動負債</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ccffcc' align='right'>
                        <div class='pt11b'><?= number_format($sonota_ryudo_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>　</div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>合計</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right'>
                        <div class='pt11b'><?= number_format($sonota_ryudo_total_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>合計</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right'>
                        <div class='pt11b'><?= number_format($sonota_ryudo_total_kin) ?></div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>合計</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right'>
                        <div class='pt11b'><?= number_format($ryudo_fusai_total_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>合計</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right'>
                        <div class='pt11b'><?= number_format($ryudo_fusai_total_kin) ?></div>
                    </td>
                </tr>
            </TBODY>
        </table>
            </td></tr>
        </table> <!----------------- ダミーEnd ------------------>
        
        <table bgcolor='#ffffff' align='center' cellspacing='0' cellpadding='10'>
            <tr><td> <!----------- ダミー(デザイン用) ------------>
        <center>（損益計算書）</center>
        <table class='winbox_field' width='100%' align='center' bgcolor='black' cellspacing='1' cellpadding='5'>
            <THEAD>
                <!-- テーブル ヘッダーの表示 -->
                <tr>
                    <th class='winbox' nowrap rowspan='2'>　</th>
                    <th class='winbox' nowrap colspan='4'>試算表</th>
                    <th class='winbox' nowrap colspan='3'>決算書(P/L,製造原価報告書、経費明細書）</th>
                </tr>
                <tr>
                    <th class='winbox' nowrap colspan='2'>勘定科目</th>
                    <th class='winbox' nowrap>金額</th>
                    <th class='winbox' nowrap>備考</th>
                    <th class='winbox' nowrap colspan='2'>勘定科目</th>
                    <th class='winbox' nowrap>金額</th>
                </tr>
                <tr>
                    <th class='winbox' nowrap>No.</th>
                    <th class='winbox' nowrap colspan='7'>製造原価報告書</th>
                </tr>
            </THEAD>
            <TFOOT>
                <!-- 現在はフッターは何もない -->
            </TFOOT>
            <TBODY>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' rowspan='5' valign='top'>
                        <div class='pt10b'>１</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' rowspan='5' valign='top'>
                        <div class='pt10b'>材料費</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>期首棚卸高</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right'>
                        <div class='pt11b'><?= number_format($z_zaiko_total_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' rowspan='5' valign='top'>
                        <div class='pt10b'>損益計算書</div><BR>
                        <div class='pt10b'>製造原価</div><BR>
                        <div class='pt10b'>報告書</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>期首製品棚卸高</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ccffcc' align='right'>
                        <div class='pt10b'>　</div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>仕掛品</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ccffcc' align='right'>
                        <div class='pt11b'><?= number_format($z_sikakari_total_kin) ?></div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>原材料及び貯蔵品</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ccffcc' align='right'>
                        <div class='pt11b'><?= number_format($z_gencho_total_kin) ?></div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ccffcc' align='right'>
                        <div class='pt11b'>　</div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>合計</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right'>
                        <div class='pt11b'><?= number_format($z_zaiko_total_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>合計</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right'>
                        <div class='pt11b'><?= number_format($z_zaiko_total_kin) ?></div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' rowspan='5' valign='top'>
                        <div class='pt10b'>２</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' rowspan='5' valign='top'>
                        <div class='pt10b'>材料費</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>期末棚卸高</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right'>
                        <div class='pt11b'><?= number_format($kimatsu_total_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' rowspan='5' valign='top'>
                        <div class='pt10b'>損益計算書</div><BR>
                        <div class='pt10b'>製造原価</div><BR>
                        <div class='pt10b'>報告書</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>期末製品棚卸高</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ccffcc' align='right'>
                        <div class='pt10b'>　</div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>仕掛品</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ccffcc' align='right'>
                        <div class='pt11b'><?= number_format($kimatsu_sikakari_total_kin) ?></div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>原材料及び貯蔵品</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ccffcc' align='right'>
                        <div class='pt11b'><?= number_format($kimatsu_gencho_total_kin) ?></div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ccffcc' align='right'>
                        <div class='pt11b'>　</div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>合計</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right'>
                        <div class='pt11b'><?= number_format($kimatsu_total_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>合計</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right'>
                        <div class='pt11b'><?= number_format($kimatsu_total_kin) ?></div>
                    </td>
                </tr>
                <tr>
                    <th class='winbox' nowrap>No.</th>
                    <th class='winbox' nowrap colspan='7'>P / L   営業外損益、特別損益、他</th>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' rowspan='3' valign='top'>
                        <div class='pt10b'>１</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' rowspan='3' valign='top'>
                        <div class='pt10b'>営業外収益</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>雑収入</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ccffcc' align='right'>
                        <div class='pt11b'><?= number_format($zatsushu_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' rowspan='3' valign='top'>
                        <div class='pt10b'>営業外収益</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>雑収入</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right'>
                        <div class='pt11b'><?= number_format($eigyo_shueki_total_kin) ?></div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>業務委託収入</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ccffcc' align='right'>
                        <div class='pt11b'><?= number_format($gyomushu_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>　</div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>合計</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right'>
                        <div class='pt11b'><?= number_format($eigyo_shueki_total_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>合計</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right'>
                        <div class='pt11b'><?= number_format($eigyo_shueki_total_kin) ?></div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' rowspan='5' valign='top'>
                        <div class='pt10b'>２</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' rowspan='5' valign='top'>
                        <div class='pt10b'>営業外費用</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>その他の営業外費用</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ccffcc' align='right'>
                        <div class='pt11b'><?= number_format($sonota_eihiyo_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' rowspan='2' valign='top'>
                        <div class='pt10b'>営業外費用</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>その他の営業外費用</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right'>
                        <div class='pt11b'><?= number_format($sonota_eihiyo_kin) ?></div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>合計</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right'>
                        <div class='pt11b'><?= number_format($sonota_eihiyo_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>合計</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right'>
                        <div class='pt11b'><?= number_format($sonota_eihiyo_kin) ?></div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>固定資産売却損</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ccffcc' align='right'>
                        <div class='pt11b'><?= number_format($kotei_baison_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' rowspan='3' valign='top'>
                        <div class='pt10b'>営業外費用</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>固定資産売却損</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right'>
                        <div class='pt11b'><?= number_format($kotei_baison_kin) ?></div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>固定資産除却損</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ccffcc' align='right'>
                        <div class='pt11b'><?= number_format($kotei_jyoson_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>固定資産除却損</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right'>
                        <div class='pt11b'><?= number_format($kotei_jyoson_kin) ?></div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>合計</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right'>
                        <div class='pt11b'><?= number_format($kotei_son_total) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>合計</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right'>
                        <div class='pt11b'><?= number_format($kotei_son_total) ?></div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' rowspan='4' valign='top'>
                        <div class='pt10b'>３</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' rowspan='4' valign='top'>
                        <div class='pt10b'>法人税等</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>法人税及び住民税</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ccffcc' align='right'>
                        <div class='pt11b'><?= number_format($hojin_jyumin_zei_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' rowspan='4' valign='top'>
                        <div class='pt10b'>法人税等</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' rowspan='2' valign='top'>
                        <div class='pt10b'>法人税、住民税</div><BR>
                        <div class='pt10b'>及び事業税</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right'  rowspan='2'>
                        <div class='pt11b'><?= number_format($hojin_zeito_total_kin) ?></div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>事業税</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ccffcc' align='right'>
                        <div class='pt11b'><?= number_format($jigyo_zei_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>　</div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>法人税等調整額</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ccffcc' align='right'>
                        <div class='pt11b'><?= number_format($hojin_chosei_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>　</div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>合計</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right'>
                        <div class='pt11b'><?= number_format($hojin_zeito_total_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>合計</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right'>
                        <div class='pt11b'><?= number_format($hojin_zeito_total_kin) ?></div>
                    </td>
                </tr>
                <tr>
                    <th class='winbox' nowrap>No.</th>
                    <th class='winbox' nowrap colspan='7'>経    費    明   細    書</th>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' rowspan='3' valign='top'>
                        <div class='pt10b'>１</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' rowspan='3' valign='top'>
                        <div class='pt10b'>販管費</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>旅費交通費</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ccffcc' align='right'>
                        <div class='pt11b'><?= number_format($han_ryohi_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' rowspan='3' valign='top'>
                        <div class='pt10b'>販管費</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>旅費交通費</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffc6' align='right'>
                        <div class='pt11b'><?= number_format($han_ryohi_total_kin) ?></div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>海外出張費</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ccffcc' align='right'>
                        <div class='pt11b'><?= number_format($han_kaigai_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right'>
                        <div class='pt11b'>　</div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>合計</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right'>
                        <div class='pt11b'><?= number_format($han_ryohi_total_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>合計</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right'>
                        <div class='pt11b'><?= number_format($han_ryohi_total_kin) ?></div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' rowspan='3' valign='top'>
                        <div class='pt10b'>２</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' rowspan='3' valign='top'>
                        <div class='pt10b'>販管費</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>広告宣伝費</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ccffcc' align='right'>
                        <div class='pt11b'><?= number_format($han_kokoku_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' rowspan='3' valign='top'>
                        <div class='pt10b'>販管費</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>広告宣伝費</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffc6' align='right'>
                        <div class='pt11b'><?= number_format($han_kokoku_total_kin) ?></div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>求人費</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ccffcc' align='right'>
                        <div class='pt11b'><?= number_format($han_kyujin_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right'>
                        <div class='pt11b'>　</div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>合計</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right'>
                        <div class='pt11b'><?= number_format($han_kokoku_total_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>合計</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right'>
                        <div class='pt11b'><?= number_format($han_kokoku_total_kin) ?></div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' rowspan='3' valign='top'>
                        <div class='pt10b'>３</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' rowspan='3' valign='top'>
                        <div class='pt10b'>販管費</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>業務委託費</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ccffcc' align='right'>
                        <div class='pt11b'><?= number_format($han_gyomu_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' rowspan='3' valign='top'>
                        <div class='pt10b'>販管費</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>業務委託費</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffc6' align='right'>
                        <div class='pt11b'><?= number_format($han_gyomu_total_kin) ?></div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>支払手数料</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ccffcc' align='right'>
                        <div class='pt11b'><?= number_format($han_tesu_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right'>
                        <div class='pt11b'>　</div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>合計</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right'>
                        <div class='pt11b'><?= number_format($han_gyomu_total_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>合計</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right'>
                        <div class='pt11b'><?= number_format($han_gyomu_total_kin) ?></div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' rowspan='3' valign='top'>
                        <div class='pt10b'>４</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' rowspan='3' valign='top'>
                        <div class='pt10b'>販管費</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>事業等</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ccffcc' align='right'>
                        <div class='pt11b'><?= number_format($han_jigyo_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' rowspan='3' valign='top'>
                        <div class='pt10b'>販管費</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>諸税公課</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffc6' align='right'>
                        <div class='pt11b'><?= number_format($han_zeikoka_total_kin) ?></div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>諸税公課</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ccffcc' align='right'>
                        <div class='pt11b'><?= number_format($han_zeikoka_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right'>
                        <div class='pt11b'>　</div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>合計</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right'>
                        <div class='pt11b'><?= number_format($han_zeikoka_total_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>合計</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right'>
                        <div class='pt11b'><?= number_format($han_zeikoka_total_kin) ?></div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' rowspan='3' valign='top'>
                        <div class='pt10b'>５</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' rowspan='3' valign='top'>
                        <div class='pt10b'>販管費</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>事務用消耗品費</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ccffcc' align='right'>
                        <div class='pt11b'><?= number_format($han_jimuyo_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' rowspan='3' valign='top'>
                        <div class='pt10b'>販管費</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>事務用消耗品費</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffc6' align='right'>
                        <div class='pt11b'><?= number_format($han_jimuyo_total_kin) ?></div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>工場消耗品費</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ccffcc' align='right'>
                        <div class='pt11b'><?= number_format($han_kojyo_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right'>
                        <div class='pt11b'>　</div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>合計</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right'>
                        <div class='pt11b'><?= number_format($han_jimuyo_total_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>合計</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right'>
                        <div class='pt11b'><?= number_format($han_jimuyo_total_kin) ?></div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' rowspan='4' valign='top'>
                        <div class='pt10b'>６</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' rowspan='4' valign='top'>
                        <div class='pt10b'>販管費</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>雑費</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ccffcc' align='right'>
                        <div class='pt11b'><?= number_format($han_zappi_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' rowspan='4' valign='top'>
                        <div class='pt10b'>販管費</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>雑費</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffc6' align='right'>
                        <div class='pt11b'><?= number_format($han_zappi_total_kin) ?></div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>保証修理費</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ccffcc' align='right'>
                        <div class='pt11b'><?= number_format($han_hosyo_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right'>
                        <div class='pt11b'>　</div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>諸会費</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ccffcc' align='right'>
                        <div class='pt11b'><?= number_format($han_kaihi_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right'>
                        <div class='pt11b'>　</div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>合計</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right'>
                        <div class='pt11b'><?= number_format($han_zappi_total_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>合計</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right'>
                        <div class='pt11b'><?= number_format($han_zappi_total_kin) ?></div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' rowspan='3' valign='top'>
                        <div class='pt10b'>７</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' rowspan='3' valign='top'>
                        <div class='pt10b'>販管費</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>地代家賃</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ccffcc' align='right'>
                        <div class='pt11b'><?= number_format($han_yachin_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' rowspan='3' valign='top'>
                        <div class='pt10b'>販管費</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>地代家賃</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffc6' align='right'>
                        <div class='pt11b'><?= number_format($han_yachin_total_kin) ?></div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>倉敷料</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ccffcc' align='right'>
                        <div class='pt11b'><?= number_format($han_kura_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right'>
                        <div class='pt11b'>　</div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>合計</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right'>
                        <div class='pt11b'><?= number_format($han_yachin_total_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>合計</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right'>
                        <div class='pt11b'><?= number_format($han_yachin_total_kin) ?></div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' rowspan='3' valign='top'>
                        <div class='pt10b'>８</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' rowspan='3' valign='top'>
                        <div class='pt10b'>販管費</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>法定福利費</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ccffcc' align='right'>
                        <div class='pt11b'><?= number_format($han_hofukuri_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' rowspan='3' valign='top'>
                        <div class='pt10b'>販管費</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>厚生福利費</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffc6' align='right'>
                        <div class='pt11b'><?= number_format($han_kofukuri_total_kin) ?></div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>厚生福利費</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ccffcc' align='right'>
                        <div class='pt11b'><?= number_format($han_kofukuri_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right'>
                        <div class='pt11b'>　</div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>合計</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right'>
                        <div class='pt11b'><?= number_format($han_kofukuri_total_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>合計</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right'>
                        <div class='pt11b'><?= number_format($han_kofukuri_total_kin) ?></div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' rowspan='3' valign='top'>
                        <div class='pt10b'>９</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' rowspan='3' valign='top'>
                        <div class='pt10b'>販管費</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>退職給与金</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ccffcc' align='right'>
                        <div class='pt11b'><?= number_format($han_taikyuyo_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' rowspan='3' valign='top'>
                        <div class='pt10b'>販管費</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>退職給付費用</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffc6' align='right'>
                        <div class='pt11b'><?= number_format($han_taikyufu_total_kin) ?></div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>退職給付費用</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ccffcc' align='right'>
                        <div class='pt11b'><?= number_format($han_taikyufu_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right'>
                        <div class='pt11b'>　</div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>合計</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right'>
                        <div class='pt11b'><?= number_format($han_taikyufu_total_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>合計</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right'>
                        <div class='pt11b'><?= number_format($han_taikyufu_total_kin) ?></div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' rowspan='3' valign='top'>
                        <div class='pt10b'>１０</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' rowspan='3' valign='top'>
                        <div class='pt10b'>製造経費</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>旅費交通費</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ccffcc' align='right'>
                        <div class='pt11b'><?= number_format($sei_ryohi_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' rowspan='3' valign='top'>
                        <div class='pt10b'>製造経費</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>旅費交通費</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffc6' align='right'>
                        <div class='pt11b'><?= number_format($sei_ryohi_total_kin) ?></div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>海外出張費</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ccffcc' align='right'>
                        <div class='pt11b'><?= number_format($sei_kaigai_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right'>
                        <div class='pt11b'>　</div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>合計</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right'>
                        <div class='pt11b'><?= number_format($sei_ryohi_total_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>合計</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right'>
                        <div class='pt11b'><?= number_format($sei_ryohi_total_kin) ?></div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' rowspan='3' valign='top'>
                        <div class='pt10b'>１１</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' rowspan='3' valign='top'>
                        <div class='pt10b'>製造経費</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>業務委託費</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ccffcc' align='right'>
                        <div class='pt11b'><?= number_format($sei_gyomu_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' rowspan='3' valign='top'>
                        <div class='pt10b'>製造経費</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>業務委託費</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffc6' align='right'>
                        <div class='pt11b'><?= number_format($sei_gyomu_total_kin) ?></div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>支払手数料</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ccffcc' align='right'>
                        <div class='pt11b'><?= number_format($sei_tesu_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right'>
                        <div class='pt11b'>　</div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>合計</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right'>
                        <div class='pt11b'><?= number_format($sei_gyomu_total_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>合計</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right'>
                        <div class='pt11b'><?= number_format($sei_gyomu_total_kin) ?></div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' rowspan='6' valign='top'>
                        <div class='pt10b'>１２</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' rowspan='6' valign='top'>
                        <div class='pt10b'>販管費</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>雑費</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ccffcc' align='right'>
                        <div class='pt11b'><?= number_format($sei_zappi_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' rowspan='6' valign='top'>
                        <div class='pt10b'>販管費</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>雑費</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffc6' align='right'>
                        <div class='pt11b'><?= number_format($sei_zappi_total_kin) ?></div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>広告宣伝費</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ccffcc' align='right'>
                        <div class='pt11b'><?= number_format($sei_kokoku_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right'>
                        <div class='pt11b'>　</div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>求人費</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ccffcc' align='right'>
                        <div class='pt11b'><?= number_format($sei_kyujin_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right'>
                        <div class='pt11b'>　</div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>保証修理費</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ccffcc' align='right'>
                        <div class='pt11b'><?= number_format($sei_hosyo_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right'>
                        <div class='pt11b'>　</div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>諸会費</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ccffcc' align='right'>
                        <div class='pt11b'><?= number_format($sei_kaihi_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right'>
                        <div class='pt11b'>　</div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>合計</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right'>
                        <div class='pt11b'><?= number_format($sei_zappi_total_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>合計</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right'>
                        <div class='pt11b'><?= number_format($sei_zappi_total_kin) ?></div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' rowspan='3' valign='top'>
                        <div class='pt10b'>１３</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' rowspan='3' valign='top'>
                        <div class='pt10b'>製造経費</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>地代家賃</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ccffcc' align='right'>
                        <div class='pt11b'><?= number_format($sei_yachin_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' rowspan='3' valign='top'>
                        <div class='pt10b'>製造経費</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>地代家賃</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffc6' align='right'>
                        <div class='pt11b'><?= number_format($sei_yachin_total_kin) ?></div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>倉敷料</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ccffcc' align='right'>
                        <div class='pt11b'><?= number_format($sei_kura_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right'>
                        <div class='pt11b'>　</div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>合計</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right'>
                        <div class='pt11b'><?= number_format($sei_yachin_total_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>合計</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right'>
                        <div class='pt11b'><?= number_format($sei_yachin_total_kin) ?></div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' rowspan='3' valign='top'>
                        <div class='pt10b'>１４</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' rowspan='3' valign='top'>
                        <div class='pt10b'>労務費</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>法定福利費</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ccffcc' align='right'>
                        <div class='pt11b'><?= number_format($sei_hofukuri_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' rowspan='3' valign='top'>
                        <div class='pt10b'>労務費</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>厚生福利費</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffc6' align='right'>
                        <div class='pt11b'><?= number_format($sei_kofukuri_total_kin) ?></div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>厚生福利費</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ccffcc' align='right'>
                        <div class='pt11b'><?= number_format($sei_kofukuri_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right'>
                        <div class='pt11b'>　</div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>合計</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right'>
                        <div class='pt11b'><?= number_format($sei_kofukuri_total_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>合計</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right'>
                        <div class='pt11b'><?= number_format($sei_kofukuri_total_kin) ?></div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' rowspan='3' valign='top'>
                        <div class='pt10b'>１５</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' rowspan='3' valign='top'>
                        <div class='pt10b'>労務費</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>退職給与金</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ccffcc' align='right'>
                        <div class='pt11b'><?= number_format($sei_taikyuyo_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' rowspan='3' valign='top'>
                        <div class='pt10b'>労務費</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>退職給付費用</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffc6' align='right'>
                        <div class='pt11b'><?= number_format($sei_taikyufu_total_kin) ?></div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>退職給付費用</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ccffcc' align='right'>
                        <div class='pt11b'><?= number_format($sei_taikyufu_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right'>
                        <div class='pt11b'>　</div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>合計</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right'>
                        <div class='pt11b'><?= number_format($sei_taikyufu_total_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff'>
                        <div class='pt10b'>合計</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right'>
                        <div class='pt11b'><?= number_format($sei_taikyufu_total_kin) ?></div>
                    </td>
                </tr>
            </TBODY>
        </table>
            </td></tr>
        </table> <!----------------- ダミーEnd ------------------>
        <form method='post' action='<?php echo $menu->out_self() ?>'>
            <input class='pt10b' type='submit' name='input_data' value='登録' onClick='return data_input_click(this)'>
        </form>
    </center>
</body>
</html>
