<?php
//////////////////////////////////////////////////////////////////////////////
// 月次損益関係 決算書                                                      //
// Copyright(C) 2018-2020 Norihisa.Ohya norihisa_ooya@nitto-kohki.co.jp     //
// Changed history                                                          //
// 2018/06/26 Created   financial_report_view.php                           //
// 2018/07/05 第１四半期決算で一部調整                                      //
// 2018/07/25 可能な部分でAS計算の損益データと比較を追加                    //
//            営業外に関しては、為替差損益の為、同金額の差異がでる          //
// 2018/10/05 デザインのくずれを修正                                        //
// 2018/10/17 19期第2四半期の結果を受けて修正                               //
// 2019/04/09 販管費のクレーム対応費を追加                                  //
// 2019/05/17 日付の取得方法の変更                                          //
// 2020/01/27 減価償却費明細表を追加                                        //
// 2020/04/13 eCA用のデータ抜き出しを追加                                   //
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
$nk_ki = $ki + 44;

//////////// タイトル名(ソースのタイトル名とフォームのタイトル名)
if ($tuki_chk == 3) {
    $menu->set_title("第 {$ki} 期　本決算　決　算　書");
} else {
    $menu->set_title("第 {$ki} 期　第{$hanki}四半期　決　算　書");
}

//// 貸借対照表
//// 流動資産
// 現金及び預金
$res   = array();
$field = array();
$rows  = array();
$genkin_kin = 0;
$note = '現金及び預金';
$query = sprintf("SELECT rep_kin FROM financial_report_data WHERE rep_ymd=%d and rep_note='%s'", $yyyymm, $note);
if ($rows=getResultWithField2($query, $field, $res) <= 0) {
    $genkin_kin = 0;
} else {
    $genkin_kin = $res[0][0];
}
// 2020/03/26 eCAデータ連携対応 出力
$csv_data = array();
$csv_data[0][0] = $note;
$csv_data[0][1] = $genkin_kin;

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
// 2020/03/26 eCAデータ連携対応 出力
$csv_data[1][0] = $note;
$csv_data[1][1] = $urikake_kin;

// 仕掛品
$res   = array();
$field = array();
$rows  = array();
$tai_shikakari_kin = 0;
$note = '貸借仕掛品';
$query = sprintf("SELECT rep_kin FROM financial_report_data WHERE rep_ymd=%d and rep_note='%s'", $yyyymm, $note);
if ($rows=getResultWithField2($query, $field, $res) <= 0) {
    $tai_shikakari_kin = 0;
} else {
    $tai_shikakari_kin = $res[0][0];
}
// 原材料及び貯蔵品
$res   = array();
$field = array();
$rows  = array();
$tai_zairyo_kin = 0;
$note = '貸借原材料及び貯蔵品';
$query = sprintf("SELECT rep_kin FROM financial_report_data WHERE rep_ymd=%d and rep_note='%s'", $yyyymm, $note);
if ($rows=getResultWithField2($query, $field, $res) <= 0) {
    $tai_zairyo_kin = 0;
} else {
    $tai_zairyo_kin = $res[0][0];
}
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
// 未収入金
$res   = array();
$field = array();
$rows  = array();
$mishu_kin_kin = 0;
$note = '未収入金';
$query = sprintf("SELECT rep_kin FROM financial_report_data WHERE rep_ymd=%d and rep_note='%s'", $yyyymm, $note);
if ($rows=getResultWithField2($query, $field, $res) <= 0) {
    $mishu_kin_kin = 0;
} else {
    $mishu_kin_kin = $res[0][0];
}
// 未収消費税等
$res   = array();
$field = array();
$rows  = array();
$mishu_shozei_kin = 0;
$note = '未収消費税等';
$query = sprintf("SELECT kin FROM profit_loss_bs_history WHERE pl_bs_ym=%d and note='%s'", $yyyymm, $note);
if ($rows=getResultWithField2($query, $field, $res) <= 0) {
    $mishu_shozei_kin = 0;
} else {
    $mishu_shozei_kin = $res[0][0];
}
// その他の流動資産
$res   = array();
$field = array();
$rows  = array();
$ta_ryudo_shisan_kin = 0;
$note = 'その他の流動資産';
$query = sprintf("SELECT rep_kin FROM financial_report_data WHERE rep_ymd=%d and rep_note='%s'", $yyyymm, $note);
if ($rows=getResultWithField2($query, $field, $res) <= 0) {
    $ta_ryudo_shisan_kin = 0;
} else {
    $ta_ryudo_shisan_kin = $res[0][0];
}

// 流動資産合計
$ryudo_total_kin = $genkin_kin + $urikake_kin + $tai_shikakari_kin + $tai_zairyo_kin + $mae_hiyo_kin + $mishu_kin_kin + $mishu_shozei_kin + $ta_ryudo_shisan_kin;

//// 固定資産
//// 有形固定資産
// 建物
$res   = array();
$field = array();
$rows  = array();
$tatemono_shisan_kin = 0;
$note = '建物';
$query = sprintf("SELECT rep_kin FROM financial_report_data WHERE rep_ymd=%d and rep_note='%s'", $yyyymm, $note);
if ($rows=getResultWithField2($query, $field, $res) <= 0) {
    $tatemono_shisan_kin = 0;
} else {
    $tatemono_shisan_kin = $res[0][0];
}
// 機械及び装置
$res   = array();
$field = array();
$rows  = array();
$kikai_shisan_kin = 0;
$note = '機械及び装置';
$query = sprintf("SELECT rep_kin FROM financial_report_data WHERE rep_ymd=%d and rep_note='%s'", $yyyymm, $note);
if ($rows=getResultWithField2($query, $field, $res) <= 0) {
    $kikai_shisan_kin = 0;
} else {
    $kikai_shisan_kin = $res[0][0];
}
// 車輌運搬具
$res   = array();
$field = array();
$rows  = array();
$sharyo_shisan_kin = 0;
$note = '車輌運搬具';
$query = sprintf("SELECT rep_kin FROM financial_report_data WHERE rep_ymd=%d and rep_note='%s'", $yyyymm, $note);
if ($rows=getResultWithField2($query, $field, $res) <= 0) {
    $sharyo_shisan_kin = 0;
} else {
    $sharyo_shisan_kin = $res[0][0];
}
// 工具器具及び備品
$res   = array();
$field = array();
$rows  = array();
$kougu_shisan_kin = 0;
$note = '工具器具及び備品';
$query = sprintf("SELECT rep_kin FROM financial_report_data WHERE rep_ymd=%d and rep_note='%s'", $yyyymm, $note);
if ($rows=getResultWithField2($query, $field, $res) <= 0) {
    $kougu_shisan_kin = 0;
} else {
    $kougu_shisan_kin = $res[0][0];
}
// リース資産
$res   = array();
$field = array();
$rows  = array();
$lease_shisan_kin = 0;
$note = 'リース資産';
$query = sprintf("SELECT rep_kin FROM financial_report_data WHERE rep_ymd=%d and rep_note='%s'", $yyyymm, $note);
if ($rows=getResultWithField2($query, $field, $res) <= 0) {
    $lease_shisan_kin = 0;
} else {
    $lease_shisan_kin = $res[0][0];
}
// 建設仮勘定
$res   = array();
$field = array();
$rows  = array();
$kenkari_kin = 0;
$note = '建設仮勘定';
$query = sprintf("SELECT kin FROM profit_loss_bs_history WHERE pl_bs_ym=%d and note='%s'", $yyyymm, $note);
if ($rows=getResultWithField2($query, $field, $res) <= 0) {
    $kenkari_kin = 0;
} else {
    $kenkari_kin = $res[0][0];
}

// 有形固定資産合計
$yukei_shisan_kin = $tatemono_shisan_kin + $kikai_shisan_kin + $sharyo_shisan_kin + $kougu_shisan_kin + $lease_shisan_kin + $kenkari_kin;

//// 無形固定資産
// 電話加入権
$res   = array();
$field = array();
$rows  = array();
$denwa_shisan_kin = 0;
$note = '電話加入権';
$query = sprintf("SELECT rep_kin FROM financial_report_data WHERE rep_ymd=%d and rep_note='%s'", $yyyymm, $note);
if ($rows=getResultWithField2($query, $field, $res) <= 0) {
    $denwa_shisan_kin = 0;
} else {
    $denwa_shisan_kin = $res[0][0];
}
// 施設利用権
$res   = array();
$field = array();
$rows  = array();
$shisetsu_shisan_kin = 0;
$note = '施設利用権';
$query = sprintf("SELECT kin FROM profit_loss_bs_history WHERE pl_bs_ym=%d and note='%s'", $yyyymm, $note);
if ($rows=getResultWithField2($query, $field, $res) <= 0) {
    $shisetsu_shisan_kin = 0;
} else {
    $shisetsu_shisan_kin = $res[0][0];
}
// ソフトウェア
$res   = array();
$field = array();
$rows  = array();
$soft_shisan_kin = 0;
$note = 'ソフトウェア';
$query = sprintf("SELECT kin FROM profit_loss_bs_history WHERE pl_bs_ym=%d and note='%s'", $yyyymm, $note);
if ($rows=getResultWithField2($query, $field, $res) <= 0) {
    $soft_shisan_kin = 0;
} else {
    $soft_shisan_kin = $res[0][0];
}

// 無形固定資産合計
$mukei_shisan_kin = $denwa_shisan_kin + $shisetsu_shisan_kin + $soft_shisan_kin;

//// 投資その他の資産
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

// その他の投資等
$res   = array();
$field = array();
$rows  = array();
$sonota_toshi_kin = 0;
$note = 'その他の投資等';
$query = sprintf("SELECT kin FROM profit_loss_bs_history WHERE pl_bs_ym=%d and note='%s'", $yyyymm, $note);
if ($rows=getResultWithField2($query, $field, $res) <= 0) {
    $sonota_toshi_kin = 0;
} else {
    $sonota_toshi_kin = $res[0][0];
}

// 投資その他の資産合計
$toshi_sonota_kin = $choki_kashi_kin + $choki_maebara_kin + $kotei_kuri_zei_kin + $sonota_toshi_kin;

// 固定資産合計
$kotei_shisan_total_kin = $yukei_shisan_kin + $mukei_shisan_kin + $toshi_sonota_kin;

// 資産の部合計
$shisan_total_kin = $ryudo_total_kin + $kotei_shisan_total_kin;

//// 負債及び純資産の部
//// 負債の部
//// 流動負債
// 買掛金
$res   = array();
$field = array();
$rows  = array();
$kaikake_kin = 0;
$note = '買掛金';
$query = sprintf("SELECT rep_kin FROM financial_report_data WHERE rep_ymd=%d and rep_note='%s'", $yyyymm, $note);
if ($rows=getResultWithField2($query, $field, $res) <= 0) {
    $kaikake_kin = 0;
} else {
    $kaikake_kin = $res[0][0];
}
// リース債務（短期）
$res   = array();
$field = array();
$rows  = array();
$lease_tanki_kin = 0;
$note = 'リース債務(短期)';
$query = sprintf("SELECT kin FROM profit_loss_bs_history WHERE pl_bs_ym=%d and note='%s'", $yyyymm, $note);
if ($rows=getResultWithField2($query, $field, $res) <= 0) {
    $lease_tanki_kin = 0;
} else {
    $lease_tanki_kin = $res[0][0];
}
// 未払金
$res   = array();
$field = array();
$rows  = array();
$miharai_kin = 0;
$note = '未払金';
$query = sprintf("SELECT rep_kin FROM financial_report_data WHERE rep_ymd=%d and rep_note='%s'", $yyyymm, $note);
if ($rows=getResultWithField2($query, $field, $res) <= 0) {
    $miharai_kin = 0;
} else {
    $miharai_kin = $res[0][0];
}
// 未払消費税等
$res   = array();
$field = array();
$rows  = array();
$miharai_shozei_kin = 0;
$note = '未払消費税等';
$query = sprintf("SELECT rep_kin FROM financial_report_data WHERE rep_ymd=%d and rep_note='%s'", $yyyymm, $note);
if ($rows=getResultWithField2($query, $field, $res) <= 0) {
    $miharai_shozei_kin = 0;
} else {
    $miharai_shozei_kin = $res[0][0];
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

// 流動負債合計
$ryudo_fusai_total_kin = $kaikake_kin + $lease_tanki_kin + $miharai_kin + $miharai_shozei_kin + $miharai_hozei_kin + $miharai_hiyo_kin + $azukari_kin + $syoyo_hikiate_kin;

//// 固定負債
// リース債務（長期）
$res   = array();
$field = array();
$rows  = array();
$lease_choki_kin = 0;
$note = 'リース債務(長期)';
$query = sprintf("SELECT kin FROM profit_loss_bs_history WHERE pl_bs_ym=%d and note='%s'", $yyyymm, $note);
if ($rows=getResultWithField2($query, $field, $res) <= 0) {
    $lease_choki_kin = 0;
} else {
    $lease_choki_kin = $res[0][0];
}
// 長期未払金
$res   = array();
$field = array();
$rows  = array();
$choki_miharai_kin = 0;
$note = '長期未払金';
$query = sprintf("SELECT kin FROM profit_loss_bs_history WHERE pl_bs_ym=%d and note='%s'", $yyyymm, $note);
if ($rows=getResultWithField2($query, $field, $res) <= 0) {
    $choki_miharai_kin = 0;
} else {
    $choki_miharai_kin = $res[0][0];
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

// 固定負債合計
$kotei_fusai_kin = $lease_choki_kin + $choki_miharai_kin + $taisyoku_hikiate_kin;

// 負債合計
$fusai_total_kin = $ryudo_fusai_total_kin + $kotei_fusai_kin;

//// 純資産の部
//// 資本金
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

//// 資本剰余金
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

// 資本剰余金合計
$tai_shihon_jyoyo_total_kin = $shihon_jyunbi_kin + $sonota_shihon_jyoyo_kin;

//// 利益剰余金
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
// 繰越利益剰余金
$res   = array();
$field = array();
$rows  = array();
$tai_kuri_rieki_jyoyo_kin = 0;
$note = '繰越利益剰余金';
$query = sprintf("SELECT kin FROM profit_loss_bs_history WHERE pl_bs_ym=%d and note='%s'", $yyyymm, $note);
if ($rows=getResultWithField2($query, $field, $res) <= 0) {
    $tai_kuri_rieki_jyoyo_kin = 0;
} else {
    $tai_kuri_rieki_jyoyo_kin = $res[0][0];
}
// 当期純利益（繰越利益剰余金に合計する四半期対応）
$res   = array();
$field = array();
$rows  = array();
$tai_toujyun = 0;
$note = '当期純利益';
$query = sprintf("SELECT kin FROM profit_loss_bs_history WHERE pl_bs_ym=%d and note='%s'", $yyyymm, $note);
if ($rows=getResultWithField2($query, $field, $res) <= 0) {
    $tai_toujyun = 0;
} else {
    $tai_toujyun = $res[0][0];
}

// 貸借対照表用 繰越利益剰余金の計算
$tai_kuri_rieki_jyoyo_kin = $tai_kuri_rieki_jyoyo_kin + $tai_toujyun;

// 利益剰余金合計
$tai_rieki_jyoyo_total_kin = $tai_sonota_rieki_jyoyo_kin + $tai_kuri_rieki_jyoyo_kin;

// 純資産合計
$tai_jyun_shisan_total_kin = $shihon_total_kin + $tai_shihon_jyoyo_total_kin + $tai_rieki_jyoyo_total_kin;

// 負債及び純資産合計
$fusai_jyunshi_total_kin = $fusai_total_kin + $tai_jyun_shisan_total_kin;

// 貸借差額計算
$tai_sagaku_kin = $shisan_total_kin - $fusai_jyunshi_total_kin;

//// 経費明細書
//// 役員報酬
// 製造費用
$res   = array();
$field = array();
$rows  = array();
$yakuin_seizo_kin = 0;
$note = '製造経費役員報酬';
$query = sprintf("SELECT SUM(kin) FROM profit_loss_keihi_history WHERE pl_bs_ym>=%d and pl_bs_ym<=%d and note='%s'", $str_ym, $end_ym, $note);
if ($rows=getResultWithField2($query, $field, $res) <= 0) {
    $yakuin_seizo_kin = 0;
} else {
    $yakuin_seizo_kin = $res[0][0];
}

// 販管費
$res   = array();
$field = array();
$rows  = array();
$yakuin_han_kin = 0;
$note = '販管費役員報酬';
$query = sprintf("SELECT SUM(kin) FROM profit_loss_keihi_history WHERE pl_bs_ym>=%d and pl_bs_ym<=%d and note='%s'", $str_ym, $end_ym, $note);
if ($rows=getResultWithField2($query, $field, $res) <= 0) {
    $yakuin_han_kin = 0;
} else {
    $yakuin_han_kin = $res[0][0];
}

// 役員報酬合計
$yakuin_total_kin = $yakuin_seizo_kin + $yakuin_han_kin;

//// 給料手当
// 製造費用
$res   = array();
$field = array();
$rows  = array();
$kyuryo_seizo_kin = 0;
$note = '製造経費給料手当';
$query = sprintf("SELECT SUM(kin) FROM profit_loss_keihi_history WHERE pl_bs_ym>=%d and pl_bs_ym<=%d and note='%s'", $str_ym, $end_ym, $note);
if ($rows=getResultWithField2($query, $field, $res) <= 0) {
    $kyuryo_seizo_kin = 0;
} else {
    $kyuryo_seizo_kin = $res[0][0];
}
// 販管費
$res   = array();
$field = array();
$rows  = array();
$kyuryo_han_kin = 0;
$note = '販管費給料手当';
$query = sprintf("SELECT SUM(kin) FROM profit_loss_keihi_history WHERE pl_bs_ym>=%d and pl_bs_ym<=%d and note='%s'", $str_ym, $end_ym, $note);
if ($rows=getResultWithField2($query, $field, $res) <= 0) {
    $kyuryo_han_kin = 0;
} else {
    $kyuryo_han_kin = $res[0][0];
}

// 給料手当合計
$kyuryo_total_kin = $kyuryo_seizo_kin + $kyuryo_han_kin;

//// 賞与手当
// 製造費用
$res   = array();
$field = array();
$rows  = array();
$syoyo_teate_seizo_kin = 0;
$note = '製造経費賞与手当';
$query = sprintf("SELECT SUM(kin) FROM profit_loss_keihi_history WHERE pl_bs_ym>=%d and pl_bs_ym<=%d and note='%s'", $str_ym, $end_ym, $note);
if ($rows=getResultWithField2($query, $field, $res) <= 0) {
    $syoyo_teate_seizo_kin = 0;
} else {
    $syoyo_teate_seizo_kin = $res[0][0];
}
// 販管費
$res   = array();
$field = array();
$rows  = array();
$syoyo_teate_han_kin = 0;
$note = '販管費賞与手当';
$query = sprintf("SELECT SUM(kin) FROM profit_loss_keihi_history WHERE pl_bs_ym>=%d and pl_bs_ym<=%d and note='%s'", $str_ym, $end_ym, $note);
if ($rows=getResultWithField2($query, $field, $res) <= 0) {
    $syoyo_teate_han_kin = 0;
} else {
    $syoyo_teate_han_kin = $res[0][0];
}

// 賞与手当合計
$syoyo_teate_total_kin = $syoyo_teate_seizo_kin + $syoyo_teate_han_kin;

//// 顧問料
// 製造費用
$komon_seizo_kin = 0;
// 販管費
$res   = array();
$field = array();
$rows  = array();
$komon_han_kin = 0;
$note = '販管費顧問料';
$query = sprintf("SELECT SUM(kin) FROM profit_loss_keihi_history WHERE pl_bs_ym>=%d and pl_bs_ym<=%d and note='%s'", $str_ym, $end_ym, $note);
if ($rows=getResultWithField2($query, $field, $res) <= 0) {
    $komon_han_kin = 0;
} else {
    $komon_han_kin = $res[0][0];
}

// 顧問料合計
$komon_total_kin = $komon_seizo_kin + $komon_han_kin;

//// 厚生福利費
// 製造費用
$res   = array();
$field = array();
$rows  = array();
$fukuri_seizo_kin = 0;
$note = '製造経費厚生福利費';
$query = sprintf("SELECT rep_kin FROM financial_report_data WHERE rep_ymd=%d and rep_note='%s'", $yyyymm, $note);
if ($rows=getResultWithField2($query, $field, $res) <= 0) {
    $fukuri_seizo_kin = 0;
} else {
    $fukuri_seizo_kin = $res[0][0];
}
// 販管費
$res   = array();
$field = array();
$rows  = array();
$fukuri_han_kin = 0;
$note = '販管費厚生福利費';
$query = sprintf("SELECT rep_kin FROM financial_report_data WHERE rep_ymd=%d and rep_note='%s'", $yyyymm, $note);
if ($rows=getResultWithField2($query, $field, $res) <= 0) {
    $fukuri_han_kin = 0;
} else {
    $fukuri_han_kin = $res[0][0];
}

// 厚生福利費合計
$fukuri_total_kin = $fukuri_seizo_kin + $fukuri_han_kin;

//// 賞与引当金繰入額
// 製造費用
$res   = array();
$field = array();
$rows  = array();
$syoyo_hikiate_seizo_kin = 0;
$note = '製造経費賞与引当金繰入';
$query = sprintf("SELECT SUM(kin) FROM profit_loss_keihi_history WHERE pl_bs_ym>=%d and pl_bs_ym<=%d and note='%s'", $str_ym, $end_ym, $note);
if ($rows=getResultWithField2($query, $field, $res) <= 0) {
    $syoyo_hikiate_seizo_kin = 0;
} else {
    $syoyo_hikiate_seizo_kin = $res[0][0];
}
// 販管費
$res   = array();
$field = array();
$rows  = array();
$syoyo_hikiate_han_kin = 0;
$note = '販管費賞与引当金繰入';
$query = sprintf("SELECT SUM(kin) FROM profit_loss_keihi_history WHERE pl_bs_ym>=%d and pl_bs_ym<=%d and note='%s'", $str_ym, $end_ym, $note);
if ($rows=getResultWithField2($query, $field, $res) <= 0) {
    $syoyo_hikiate_han_kin = 0;
} else {
    $syoyo_hikiate_han_kin = $res[0][0];
}

// 賞与引当金繰入合計
$syoyo_hikiate_total_kin = $syoyo_hikiate_seizo_kin + $syoyo_hikiate_han_kin;

//// 退職給付費用
// 製造費用
$res   = array();
$field = array();
$rows  = array();
$tai_kyufu_seizo_kin = 0;
$note = '製造経費退職給付費用';
$query = sprintf("SELECT rep_kin FROM financial_report_data WHERE rep_ymd=%d and rep_note='%s'", $yyyymm, $note);
if ($rows=getResultWithField2($query, $field, $res) <= 0) {
    $tai_kyufu_seizo_kin = 0;
} else {
    $tai_kyufu_seizo_kin = $res[0][0];
}
// 販管費
$res   = array();
$field = array();
$rows  = array();
$tai_kyufu_han_kin = 0;
$note = '販管費退職給付費用';
$query = sprintf("SELECT rep_kin FROM financial_report_data WHERE rep_ymd=%d and rep_note='%s'", $yyyymm, $note);
if ($rows=getResultWithField2($query, $field, $res) <= 0) {
    $tai_kyufu_han_kin = 0;
} else {
    $tai_kyufu_han_kin = $res[0][0];
}

// 退職給付費用合計
$tai_kyufu_total_kin = $tai_kyufu_seizo_kin + $tai_kyufu_han_kin;

// 労務費合計
$roumu_total_kin = $yakuin_seizo_kin + $kyuryo_seizo_kin + $syoyo_teate_seizo_kin + $komon_seizo_kin + $fukuri_seizo_kin + $syoyo_hikiate_seizo_kin + $tai_kyufu_seizo_kin;
// 人件費合計
$jin_total_kin   = $yakuin_han_kin + $kyuryo_han_kin + $syoyo_teate_han_kin + $komon_han_kin + $fukuri_han_kin + $syoyo_hikiate_han_kin + $tai_kyufu_han_kin;
// 労務費人件費合計
$roumu_jin_total_kin = $roumu_total_kin + $jin_total_kin;

// 労務費・人件費差額計算
// 全体労務費
$res   = array();
$field = array();
$rows  = array();
$roumu_as_kin = 0;
$sum1 = '全体労務費';
$query = sprintf("select SUM(kin) from profit_loss_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='%s'", $str_ym, $end_ym, $sum1);
if ($rows=getResultWithField2($query, $field, $res) <= 0) {
    $roumu_as_kin = 0;
} else {
    $roumu_as_kin = $res[0][0];
}
$roumu_as_sagaku = $roumu_total_kin - $roumu_as_kin;

// 全体人件費
$res   = array();
$field = array();
$rows  = array();
$jin_as_kin = 0;
$sum1 = '全体人件費';
$query = sprintf("select SUM(kin) from profit_loss_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='%s'", $str_ym, $end_ym, $sum1);
if ($rows=getResultWithField2($query, $field, $res) <= 0) {
    $jin_as_kin = 0;
} else {
    $jin_as_kin = $res[0][0];
}
$jin_as_sagaku = $jin_total_kin - $jin_as_kin;


//// 製造経費・経費
//// 旅費交通費
// 製造費用
$res   = array();
$field = array();
$rows  = array();
$ryohi_seizo_kin = 0;
$note = '製造経費旅費交通費';
$query = sprintf("SELECT rep_kin FROM financial_report_data WHERE rep_ymd=%d and rep_note='%s'", $yyyymm, $note);
if ($rows=getResultWithField2($query, $field, $res) <= 0) {
    $ryohi_seizo_kin = 0;
} else {
    $ryohi_seizo_kin = $res[0][0];
}
// 販管費
$res   = array();
$field = array();
$rows  = array();
$ryohi_han_kin = 0;
$note = '販管費旅費交通費';
$query = sprintf("SELECT rep_kin FROM financial_report_data WHERE rep_ymd=%d and rep_note='%s'", $yyyymm, $note);
if ($rows=getResultWithField2($query, $field, $res) <= 0) {
    $ryohi_han_kin = 0;
} else {
    $ryohi_han_kin = $res[0][0];
}

// 旅費交通費合計
$ryohi_total_kin = $ryohi_seizo_kin + $ryohi_han_kin;

//// 通信費
// 製造費用
$res   = array();
$field = array();
$rows  = array();
$tsushin_seizo_kin = 0;
$note = '製造経費通信費';
$query = sprintf("SELECT SUM(kin) FROM profit_loss_keihi_history WHERE pl_bs_ym>=%d and pl_bs_ym<=%d and note='%s'", $str_ym, $end_ym, $note);
if ($rows=getResultWithField2($query, $field, $res) <= 0) {
    $tsushin_seizo_kin = 0;
} else {
    $tsushin_seizo_kin = $res[0][0];
}
// 販管費
$res   = array();
$field = array();
$rows  = array();
$tsushin_han_kin = 0;
$note = '販管費通信費';
$query = sprintf("SELECT SUM(kin) FROM profit_loss_keihi_history WHERE pl_bs_ym>=%d and pl_bs_ym<=%d and note='%s'", $str_ym, $end_ym, $note);
if ($rows=getResultWithField2($query, $field, $res) <= 0) {
    $tsushin_han_kin = 0;
} else {
    $tsushin_han_kin = $res[0][0];
}

// 通信費合計
$tsushin_total_kin = $tsushin_seizo_kin + $tsushin_han_kin;

//// 会議費
// 製造費用
$res   = array();
$field = array();
$rows  = array();
$kaigi_seizo_kin = 0;
$note = '製造経費会議費';
$query = sprintf("SELECT SUM(kin) FROM profit_loss_keihi_history WHERE pl_bs_ym>=%d and pl_bs_ym<=%d and note='%s'", $str_ym, $end_ym, $note);
if ($rows=getResultWithField2($query, $field, $res) <= 0) {
    $kaigi_seizo_kin = 0;
} else {
    $kaigi_seizo_kin = $res[0][0];
}
// 販管費
$res   = array();
$field = array();
$rows  = array();
$kaigi_han_kin = 0;
$note = '販管費会議費';
$query = sprintf("SELECT SUM(kin) FROM profit_loss_keihi_history WHERE pl_bs_ym>=%d and pl_bs_ym<=%d and note='%s'", $str_ym, $end_ym, $note);
if ($rows=getResultWithField2($query, $field, $res) <= 0) {
    $kaigi_han_kin = 0;
} else {
    $kaigi_han_kin = $res[0][0];
}

// 会議費合計
$kaigi_total_kin = $kaigi_seizo_kin + $kaigi_han_kin;

//// 交際接待費
// 製造費用
$res   = array();
$field = array();
$rows  = array();
$kosai_seizo_kin = 0;
$note = '製造経費交際接待費';
$query = sprintf("SELECT SUM(kin) FROM profit_loss_keihi_history WHERE pl_bs_ym>=%d and pl_bs_ym<=%d and note='%s'", $str_ym, $end_ym, $note);
if ($rows=getResultWithField2($query, $field, $res) <= 0) {
    $kosai_seizo_kin = 0;
} else {
    $kosai_seizo_kin = $res[0][0];
}
// 販管費
$res   = array();
$field = array();
$rows  = array();
$kosai_han_kin = 0;
$note = '販管費交際接待費';
$query = sprintf("SELECT SUM(kin) FROM profit_loss_keihi_history WHERE pl_bs_ym>=%d and pl_bs_ym<=%d and note='%s'", $str_ym, $end_ym, $note);
if ($rows=getResultWithField2($query, $field, $res) <= 0) {
    $kosai_han_kin = 0;
} else {
    $kosai_han_kin = $res[0][0];
}

// 交際接待費合計
$kosai_total_kin = $kosai_seizo_kin + $kosai_han_kin;

//// 広告宣伝費
$senden_seizo_kin = 0;
// 販管費
$res   = array();
$field = array();
$rows  = array();
$senden_han_kin = 0;
$note = '販管費広告宣伝費';
$query = sprintf("SELECT rep_kin FROM financial_report_data WHERE rep_ymd=%d and rep_note='%s'", $yyyymm, $note);
if ($rows=getResultWithField2($query, $field, $res) <= 0) {
    $senden_han_kin = 0;
} else {
    $senden_han_kin = $res[0][0];
}

// 広告宣伝費合計
$senden_total_kin = $senden_seizo_kin + $senden_han_kin;

//// 運賃荷造費
// 製造費用
$res   = array();
$field = array();
$rows  = array();
$nizukuri_seizo_kin = 0;
$note = '製造経費運賃荷造費';
$query = sprintf("SELECT SUM(kin) FROM profit_loss_keihi_history WHERE pl_bs_ym>=%d and pl_bs_ym<=%d and note='%s'", $str_ym, $end_ym, $note);
if ($rows=getResultWithField2($query, $field, $res) <= 0) {
    $nizukuri_seizo_kin = 0;
} else {
    $nizukuri_seizo_kin = $res[0][0];
}
// 販管費
$res   = array();
$field = array();
$rows  = array();
$nizukuri_han_kin = 0;
$note = '販管費運賃荷造費';
$query = sprintf("SELECT SUM(kin) FROM profit_loss_keihi_history WHERE pl_bs_ym>=%d and pl_bs_ym<=%d and note='%s'", $str_ym, $end_ym, $note);
if ($rows=getResultWithField2($query, $field, $res) <= 0) {
    $nizukuri_han_kin = 0;
} else {
    $nizukuri_han_kin = $res[0][0];
}

// 運賃荷造費合計
$nizukuri_total_kin = $nizukuri_seizo_kin + $nizukuri_han_kin;

//// 図書教育費
// 製造費用
$res   = array();
$field = array();
$rows  = array();
$tosyo_seizo_kin = 0;
$note = '製造経費図書教育費';
$query = sprintf("SELECT SUM(kin) FROM profit_loss_keihi_history WHERE pl_bs_ym>=%d and pl_bs_ym<=%d and note='%s'", $str_ym, $end_ym, $note);
if ($rows=getResultWithField2($query, $field, $res) <= 0) {
    $tosyo_seizo_kin = 0;
} else {
    $tosyo_seizo_kin = $res[0][0];
}
// 販管費
$res   = array();
$field = array();
$rows  = array();
$tosyo_han_kin = 0;
$note = '販管費図書教育費';
$query = sprintf("SELECT SUM(kin) FROM profit_loss_keihi_history WHERE pl_bs_ym>=%d and pl_bs_ym<=%d and note='%s'", $str_ym, $end_ym, $note);
if ($rows=getResultWithField2($query, $field, $res) <= 0) {
    $tosyo_han_kin = 0;
} else {
    $tosyo_han_kin = $res[0][0];
}

// 図書教育費合計
$tosyo_total_kin = $tosyo_seizo_kin + $tosyo_han_kin;

//// 業務委託費
// 製造費用
$res   = array();
$field = array();
$rows  = array();
$gyomu_seizo_kin = 0;
$note = '製造経費業務委託費';
$query = sprintf("SELECT rep_kin FROM financial_report_data WHERE rep_ymd=%d and rep_note='%s'", $yyyymm, $note);
if ($rows=getResultWithField2($query, $field, $res) <= 0) {
    $gyomu_seizo_kin = 0;
} else {
    $gyomu_seizo_kin = $res[0][0];
}
// 販管費
$res   = array();
$field = array();
$rows  = array();
$gyomu_han_kin = 0;
$note = '販管費業務委託費';
$query = sprintf("SELECT rep_kin FROM financial_report_data WHERE rep_ymd=%d and rep_note='%s'", $yyyymm, $note);
if ($rows=getResultWithField2($query, $field, $res) <= 0) {
    $gyomu_han_kin = 0;
} else {
    $gyomu_han_kin = $res[0][0];
}

// 業務委託費合計
$gyomu_total_kin = $gyomu_seizo_kin + $gyomu_han_kin;

//// 諸税公課
// 製造費用
$res   = array();
$field = array();
$rows  = array();
$syozei_seizo_kin = 0;
$note = '製造経費諸税公課';
$query = sprintf("SELECT SUM(kin) FROM profit_loss_keihi_history WHERE pl_bs_ym>=%d and pl_bs_ym<=%d and note='%s'", $str_ym, $end_ym, $note);
if ($rows=getResultWithField2($query, $field, $res) <= 0) {
    $syozei_seizo_kin = 0;
} else {
    $syozei_seizo_kin = $res[0][0];
}
// 販管費
$res   = array();
$field = array();
$rows  = array();
$syozei_han_kin = 0;
$note = '販管費諸税公課';
$query = sprintf("SELECT rep_kin FROM financial_report_data WHERE rep_ymd=%d and rep_note='%s'", $yyyymm, $note);
if ($rows=getResultWithField2($query, $field, $res) <= 0) {
    $syozei_han_kin = 0;
} else {
    $syozei_han_kin = $res[0][0];
}

// 諸税公課合計
$syozei_total_kin = $syozei_seizo_kin + $syozei_han_kin;

//// 試験研究費
// 製造費用
$res   = array();
$field = array();
$rows  = array();
$shiken_seizo_kin = 0;
$note = '製造経費試験研究費';
$query = sprintf("SELECT SUM(kin) FROM profit_loss_keihi_history WHERE pl_bs_ym>=%d and pl_bs_ym<=%d and note='%s'", $str_ym, $end_ym, $note);
if ($rows=getResultWithField2($query, $field, $res) <= 0) {
    $shiken_seizo_kin = 0;
} else {
    $shiken_seizo_kin = $res[0][0];
}
// 販管費
$shiken_han_kin = 0;

// 試験研究費合計
$shiken_total_kin = $shiken_seizo_kin + $shiken_han_kin;

//// 修繕費
// 製造費用
$res   = array();
$field = array();
$rows  = array();
$syuzen_seizo_kin = 0;
$note = '製造経費修繕費';
$query = sprintf("SELECT SUM(kin) FROM profit_loss_keihi_history WHERE pl_bs_ym>=%d and pl_bs_ym<=%d and note='%s'", $str_ym, $end_ym, $note);
if ($rows=getResultWithField2($query, $field, $res) <= 0) {
    $syuzen_seizo_kin = 0;
} else {
    $syuzen_seizo_kin = $res[0][0];
}
// 販管費
$res   = array();
$field = array();
$rows  = array();
$syuzen_han_kin = 0;
$note = '販管費修繕費';
$query = sprintf("SELECT SUM(kin) FROM profit_loss_keihi_history WHERE pl_bs_ym>=%d and pl_bs_ym<=%d and note='%s'", $str_ym, $end_ym, $note);
if ($rows=getResultWithField2($query, $field, $res) <= 0) {
    $syuzen_han_kin = 0;
} else {
    $syuzen_han_kin = $res[0][0];
}

// 修繕費合計
$syuzen_total_kin = $syuzen_seizo_kin + $syuzen_han_kin;

//// 事務用消耗品費
// 製造費用
$res   = array();
$field = array();
$rows  = array();
$jimu_seizo_kin = 0;
$note = '製造経費事務用消耗品費';
$query = sprintf("SELECT SUM(kin) FROM profit_loss_keihi_history WHERE pl_bs_ym>=%d and pl_bs_ym<=%d and note='%s'", $str_ym, $end_ym, $note);
if ($rows=getResultWithField2($query, $field, $res) <= 0) {
    $jimu_seizo_kin = 0;
} else {
    $jimu_seizo_kin = $res[0][0];
}
// 販管費
$res   = array();
$field = array();
$rows  = array();
$jimu_han_kin = 0;
$note = '販管費事務用消耗品費';
$query = sprintf("SELECT rep_kin FROM financial_report_data WHERE rep_ymd=%d and rep_note='%s'", $yyyymm, $note);
if ($rows=getResultWithField2($query, $field, $res) <= 0) {
    $jimu_han_kin = 0;
} else {
    $jimu_han_kin = $res[0][0];
}

// 事務用消耗品費合計
$jimu_total_kin = $jimu_seizo_kin + $jimu_han_kin;

//// 工場用消耗品費
// 製造費用
$res   = array();
$field = array();
$rows  = array();
$kojyo_seizo_kin = 0;
$note = '製造経費工場消耗品費';
$query = sprintf("SELECT SUM(kin) FROM profit_loss_keihi_history WHERE pl_bs_ym>=%d and pl_bs_ym<=%d and note='%s'", $str_ym, $end_ym, $note);
if ($rows=getResultWithField2($query, $field, $res) <= 0) {
    $kojyo_seizo_kin = 0;
} else {
    $kojyo_seizo_kin = $res[0][0];
}
// 販管費
$kojyo_han_kin = 0;

// 工場用消耗品費合計
$kojyo_total_kin = $kojyo_seizo_kin + $kojyo_han_kin;

//// 車輌費
// 製造費用
$res   = array();
$field = array();
$rows  = array();
$syaryo_seizo_kin = 0;
$note = '製造経費車両費';
$query = sprintf("SELECT SUM(kin) FROM profit_loss_keihi_history WHERE pl_bs_ym>=%d and pl_bs_ym<=%d and note='%s'", $str_ym, $end_ym, $note);
if ($rows=getResultWithField2($query, $field, $res) <= 0) {
    $syaryo_seizo_kin = 0;
} else {
    $syaryo_seizo_kin = $res[0][0];
}
// 販管費
$res   = array();
$field = array();
$rows  = array();
$syaryo_han_kin = 0;
$note = '販管費車両費';
$query = sprintf("SELECT SUM(kin) FROM profit_loss_keihi_history WHERE pl_bs_ym>=%d and pl_bs_ym<=%d and note='%s'", $str_ym, $end_ym, $note);
if ($rows=getResultWithField2($query, $field, $res) <= 0) {
    $syaryo_han_kin = 0;
} else {
    $syaryo_han_kin = $res[0][0];
}

// 車輌費合計
$syaryo_total_kin = $syaryo_seizo_kin + $syaryo_han_kin;

//// 保険料
// 製造費用
$res   = array();
$field = array();
$rows  = array();
$hoken_seizo_kin = 0;
$note = '製造経費保険料';
$query = sprintf("SELECT SUM(kin) FROM profit_loss_keihi_history WHERE pl_bs_ym>=%d and pl_bs_ym<=%d and note='%s'", $str_ym, $end_ym, $note);
if ($rows=getResultWithField2($query, $field, $res) <= 0) {
    $hoken_seizo_kin = 0;
} else {
    $hoken_seizo_kin = $res[0][0];
}
// 販管費
$res   = array();
$field = array();
$rows  = array();
$hoken_han_kin = 0;
$note = '販管費保険料';
$query = sprintf("SELECT SUM(kin) FROM profit_loss_keihi_history WHERE pl_bs_ym>=%d and pl_bs_ym<=%d and note='%s'", $str_ym, $end_ym, $note);
if ($rows=getResultWithField2($query, $field, $res) <= 0) {
    $hoken_han_kin = 0;
} else {
    $hoken_han_kin = $res[0][0];
}

// 保険料合計
$hoken_total_kin = $hoken_seizo_kin + $hoken_han_kin;

//// 水道光熱費
// 製造費用
$res   = array();
$field = array();
$rows  = array();
$suido_seizo_kin = 0;
$note = '製造経費水道光熱費';
$query = sprintf("SELECT SUM(kin) FROM profit_loss_keihi_history WHERE pl_bs_ym>=%d and pl_bs_ym<=%d and note='%s'", $str_ym, $end_ym, $note);
if ($rows=getResultWithField2($query, $field, $res) <= 0) {
    $suido_seizo_kin = 0;
} else {
    $suido_seizo_kin = $res[0][0];
}
// 販管費
$res   = array();
$field = array();
$rows  = array();
$suido_han_kin = 0;
$note = '販管費水道光熱費';
$query = sprintf("SELECT SUM(kin) FROM profit_loss_keihi_history WHERE pl_bs_ym>=%d and pl_bs_ym<=%d and note='%s'", $str_ym, $end_ym, $note);
if ($rows=getResultWithField2($query, $field, $res) <= 0) {
    $suido_han_kin = 0;
} else {
    $suido_han_kin = $res[0][0];
}

// 水道光熱費合計
$suido_total_kin = $suido_seizo_kin + $suido_han_kin;

//// 地代家賃
// 製造費用
$res   = array();
$field = array();
$rows  = array();
$yachin_seizo_kin = 0;
$note = '製造経費地代家賃';
$query = sprintf("SELECT rep_kin FROM financial_report_data WHERE rep_ymd=%d and rep_note='%s'", $yyyymm, $note);
if ($rows=getResultWithField2($query, $field, $res) <= 0) {
    $yachin_seizo_kin = 0;
} else {
    $yachin_seizo_kin = $res[0][0];
}
// 販管費
$res   = array();
$field = array();
$rows  = array();
$yachin_han_kin = 0;
$note = '販管費地代家賃';
$query = sprintf("SELECT rep_kin FROM financial_report_data WHERE rep_ymd=%d and rep_note='%s'", $yyyymm, $note);
if ($rows=getResultWithField2($query, $field, $res) <= 0) {
    $yachin_han_kin = 0;
} else {
    $yachin_han_kin = $res[0][0];
}

// 地代家賃合計
$yachin_total_kin = $yachin_seizo_kin + $yachin_han_kin;

//// 寄付金
// 製造費用
$kifu_seizo_kin = 0;
// 販管費
$res   = array();
$field = array();
$rows  = array();
$kifu_han_kin = 0;
$note = '販管費寄付金';
$query = sprintf("SELECT SUM(kin) FROM profit_loss_keihi_history WHERE pl_bs_ym>=%d and pl_bs_ym<=%d and note='%s'", $str_ym, $end_ym, $note);
if ($rows=getResultWithField2($query, $field, $res) <= 0) {
    $kifu_han_kin = 0;
} else {
    $kifu_han_kin = $res[0][0];
}

// 寄付金合計
$kifu_total_kin = $kifu_seizo_kin + $kifu_han_kin;

//// 賃借料
// 製造費用
$res   = array();
$field = array();
$rows  = array();
$chin_seizo_kin = 0;
$note = '製造経費賃借料';
$query = sprintf("SELECT SUM(kin) FROM profit_loss_keihi_history WHERE pl_bs_ym>=%d and pl_bs_ym<=%d and note='%s'", $str_ym, $end_ym, $note);
if ($rows=getResultWithField2($query, $field, $res) <= 0) {
    $chin_seizo_kin = 0;
} else {
    $chin_seizo_kin = $res[0][0];
}
// 販管費
$res   = array();
$field = array();
$rows  = array();
$chin_han_kin = 0;
$note = '販管費賃借料';
$query = sprintf("SELECT SUM(kin) FROM profit_loss_keihi_history WHERE pl_bs_ym>=%d and pl_bs_ym<=%d and note='%s'", $str_ym, $end_ym, $note);
if ($rows=getResultWithField2($query, $field, $res) <= 0) {
    $chin_han_kin = 0;
} else {
    $chin_han_kin = $res[0][0];
}

// 賃借料合計
$chin_total_kin = $chin_seizo_kin + $chin_han_kin;

//// 雑費
// 製造費用
$res   = array();
$field = array();
$rows  = array();
$zappi_seizo_kin = 0;
$note = '製造経費雑費';
$query = sprintf("SELECT rep_kin FROM financial_report_data WHERE rep_ymd=%d and rep_note='%s'", $yyyymm, $note);
if ($rows=getResultWithField2($query, $field, $res) <= 0) {
    $zappi_seizo_kin = 0;
} else {
    $zappi_seizo_kin = $res[0][0];
}
// 販管費
$res   = array();
$field = array();
$rows  = array();
$zappi_han_kin = 0;
$note = '販管費雑費';
$query = sprintf("SELECT rep_kin FROM financial_report_data WHERE rep_ymd=%d and rep_note='%s'", $yyyymm, $note);
if ($rows=getResultWithField2($query, $field, $res) <= 0) {
    $zappi_han_kin = 0;
} else {
    $zappi_han_kin = $res[0][0];
}

// 雑費合計
$zappi_total_kin = $zappi_seizo_kin + $zappi_han_kin;

//// クレーム対応費
// 製造費用
$res   = array();
$field = array();
$rows  = array();
$clame_seizo_kin = 0;
$note = '製造経費クレーム対応費';
$query = sprintf("SELECT SUM(kin) FROM profit_loss_keihi_history WHERE pl_bs_ym>=%d and pl_bs_ym<=%d and note='%s'", $str_ym, $end_ym, $note);
if ($rows=getResultWithField2($query, $field, $res) <= 0) {
    $clame_seizo_kin = 0;
} else {
    $clame_seizo_kin = $res[0][0];
}
// 販管費
// 製造費用
$res   = array();
$field = array();
$rows  = array();
$clame_han_kin = 0;
$note = '販管費クレーム対応費';
$query = sprintf("SELECT SUM(kin) FROM profit_loss_keihi_history WHERE pl_bs_ym>=%d and pl_bs_ym<=%d and note='%s'", $str_ym, $end_ym, $note);
if ($rows=getResultWithField2($query, $field, $res) <= 0) {
    $clame_han_kin = 0;
} else {
    $clame_han_kin = $res[0][0];
};

// クレーム対応費合計
$clame_total_kin = $clame_seizo_kin + $clame_han_kin;

//// 減価償却費
// 製造費用
$res   = array();
$field = array();
$rows  = array();
$genkasyo_seizo_kin = 0;
$note = '製造経費減価償却費';
$query = sprintf("SELECT SUM(kin) FROM profit_loss_keihi_history WHERE pl_bs_ym>=%d and pl_bs_ym<=%d and note='%s'", $str_ym, $end_ym, $note);
if ($rows=getResultWithField2($query, $field, $res) <= 0) {
    $genkasyo_seizo_kin = 0;
} else {
    $genkasyo_seizo_kin = $res[0][0];
}
// 販管費
$res   = array();
$field = array();
$rows  = array();
$genkasyo_han_kin = 0;
$note = '販管費減価償却費';
$query = sprintf("SELECT SUM(kin) FROM profit_loss_keihi_history WHERE pl_bs_ym>=%d and pl_bs_ym<=%d and note='%s'", $str_ym, $end_ym, $note);
if ($rows=getResultWithField2($query, $field, $res) <= 0) {
    $genkasyo_han_kin = 0;
} else {
    $genkasyo_han_kin = $res[0][0];
}

// 減価償却費合計
$genkasyo_total_kin = $genkasyo_seizo_kin + $genkasyo_han_kin;

// 製造経費合計
$seizo_keihi_total_kin = $ryohi_seizo_kin + $tsushin_seizo_kin + $kaigi_seizo_kin + $kosai_seizo_kin + $senden_seizo_kin + $nizukuri_seizo_kin + $tosyo_seizo_kin + $gyomu_seizo_kin + $syozei_seizo_kin + $shiken_seizo_kin + $syuzen_seizo_kin + $jimu_seizo_kin + $kojyo_seizo_kin + $syaryo_seizo_kin + $hoken_seizo_kin + $suido_seizo_kin + $yachin_seizo_kin + $kifu_seizo_kin + $chin_seizo_kin + $zappi_seizo_kin + $clame_seizo_kin + $genkasyo_seizo_kin;
// 経費合計
$han_keihi_total_kin   = $ryohi_han_kin + $tsushin_han_kin + $kaigi_han_kin + $kosai_han_kin + $senden_han_kin + $nizukuri_han_kin + $tosyo_han_kin + $gyomu_han_kin + $syozei_han_kin + $shiken_han_kin + $syuzen_han_kin + $jimu_han_kin + $kojyo_han_kin + $syaryo_han_kin + $hoken_han_kin + $suido_han_kin + $yachin_han_kin + $kifu_han_kin + $chin_han_kin + $zappi_han_kin + $clame_han_kin + $genkasyo_han_kin;
// 労務費人件費合計
$keihi_total_kin = $seizo_keihi_total_kin + $han_keihi_total_kin;

// 製造経費・経費（販管費）差額計算
// 全体製造経費
$res   = array();
$field = array();
$rows  = array();
$seikei_as_kin = 0;
$sum1 = '全体製造経費';
$query = sprintf("select SUM(kin) from profit_loss_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='%s'", $str_ym, $end_ym, $sum1);
if ($rows=getResultWithField2($query, $field, $res) <= 0) {
    $seikei_as_kin = 0;
} else {
    $seikei_as_kin = $res[0][0];
}
$seikei_as_sagaku = $seizo_keihi_total_kin - $seikei_as_kin;

// 全体経費（販管費）
$res   = array();
$field = array();
$rows  = array();
$hankei_as_kin = 0;
$sum1 = '全体経費';
$query = sprintf("select SUM(kin) from profit_loss_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='%s'", $str_ym, $end_ym, $sum1);
if ($rows=getResultWithField2($query, $field, $res) <= 0) {
    $hankei_as_kin = 0;
} else {
    $hankei_as_kin = $res[0][0];
}
$hankei_as_sagaku = $han_keihi_total_kin - $hankei_as_kin;


//// 製造費用合計
$seizo_hiyo_total_kin = $roumu_total_kin + $seizo_keihi_total_kin;
//// 販管費合計
$han_all_total_kin    = $jin_total_kin + $han_keihi_total_kin;
//// 総経費合計
$all_keihi_total_kin  = $seizo_hiyo_total_kin+ $han_all_total_kin;

//// 製造原価報告書
// 期首材料棚卸高
$res   = array();
$field = array();
$rows  = array();
$kishu_zairyo_kin = 0;
$note = '期首原材料及び貯蔵品';
$query = sprintf("SELECT rep_kin FROM financial_report_data WHERE rep_ymd=%d and rep_note='%s'", $yyyymm, $note);
if ($rows=getResultWithField2($query, $field, $res) <= 0) {
    $kishu_zairyo_kin = 0;
} else {
    $kishu_zairyo_kin = $res[0][0];
}

// 当期材料仕入高
$res   = array();
$field = array();
$rows  = array();
$touki_shiire_kin = 0;
$note = '当期材料仕入高';
$query = sprintf("SELECT rep_kin FROM financial_report_data WHERE rep_ymd=%d and rep_note='%s'", $yyyymm, $note);
if ($rows=getResultWithField2($query, $field, $res) <= 0) {
    $touki_shiire_kin = 0;
} else {
    $touki_shiire_kin = $res[0][0];
}

// 材料合計１ 期首材料＋当期材料仕入
$zai_total_1 = $kishu_zairyo_kin + $touki_shiire_kin;

// 期末材料棚卸高
$res   = array();
$field = array();
$rows  = array();
$kimatsu_zairyo_kin = 0;
$note = '期末原材料及び貯蔵品';
$query = sprintf("SELECT rep_kin FROM financial_report_data WHERE rep_ymd=%d and rep_note='%s'", $yyyymm, $note);
if ($rows=getResultWithField2($query, $field, $res) <= 0) {
    $kimatsu_zairyo_kin = 0;
} else {
    $kimatsu_zairyo_kin = $res[0][0];
}

// 材料合計２ 材料合計１－ 期末材料
$zai_total_2 = $zai_total_1 - $kimatsu_zairyo_kin;

//// 他勘定振替高計算
// 他勘定振替高（資）6100 00 と他勘定振替高（製）6400 00 と 原価差異（ＰＬ） 6420 00 の合計（符号逆）
// 他勘定振替高（資）6100 00
$res   = array();
$field = array();
$rows  = array();
$takan_shizai_kin = 0;
$sum1 = '6100';
$sum2 = '00';
$query = sprintf("select SUM(rep_de), SUM(rep_cr) from financial_report_month where rep_ymd>=%d and rep_ymd<=%d and rep_summary1='%s'", $str_ym, $end_ym, $sum1);
if ($rows=getResultWithField2($query, $field, $res) <= 0) {
    $takan_shizai_kin = 0;
} else {
    $takan_shizai_kin = -($res[0][0] - $res[0][1]);
}

// 他勘定振替高（製）6400 00
$res   = array();
$field = array();
$rows  = array();
$takan_sei_kin = 0;
$sum1 = '6400';
$sum2 = '00';
$query = sprintf("select SUM(rep_de), SUM(rep_cr) from financial_report_month where rep_ymd>=%d and rep_ymd<=%d and rep_summary1='%s'", $str_ym, $end_ym, $sum1);
if ($rows=getResultWithField2($query, $field, $res) <= 0) {
    $takan_sei_kin = 0;
} else {
    $takan_sei_kin = -($res[0][0] - $res[0][1]);
}

// 原価差異（ＰＬ） 6420 00
$res   = array();
$field = array();
$rows  = array();
$gensai_pl_kin = 0;
$sum1 = '6420';
$sum2 = '00';
$query = sprintf("select SUM(rep_de), SUM(rep_cr) from financial_report_month where rep_ymd>=%d and rep_ymd<=%d and rep_summary1='%s'", $str_ym, $end_ym, $sum1);
if ($rows=getResultWithField2($query, $field, $res) <= 0) {
    $gensai_pl_kin = 0;
} else {
    $gensai_pl_kin = -($res[0][0] - $res[0][1]);
}

// 他勘定振替高 計
$takan_total_kin = $takan_shizai_kin + $takan_sei_kin + $gensai_pl_kin;

// 当期材料費 計
$touki_zairyo_total = $zai_total_2 - $takan_total_kin;

// 当期総製造費用
$touki_total_seizo_hiyo = $touki_zairyo_total + $roumu_total_kin + $seizo_keihi_total_kin;

// 期首仕掛品棚卸高
$res   = array();
$field = array();
$rows  = array();
$kishu_shikakari_kin = 0;
$note = '期首仕掛品';
$query = sprintf("SELECT rep_kin FROM financial_report_data WHERE rep_ymd=%d and rep_note='%s'", $yyyymm, $note);
if ($rows=getResultWithField2($query, $field, $res) <= 0) {
    $kishu_shikakari_kin = 0;
} else {
    $kishu_shikakari_kin = $res[0][0];
}

// 当期製造経費合計
$toki_seizo_keihi_total = $touki_total_seizo_hiyo + $kishu_shikakari_kin;

// 期末仕掛品棚卸高
$res   = array();
$field = array();
$rows  = array();
$kimatsu_shikakari_kin = 0;
$note = '期末仕掛品';
$query = sprintf("SELECT rep_kin FROM financial_report_data WHERE rep_ymd=%d and rep_note='%s'", $yyyymm, $note);
if ($rows=getResultWithField2($query, $field, $res) <= 0) {
    $kimatsu_shikakari_kin = 0;
} else {
    $kimatsu_shikakari_kin = $res[0][0];
}

// 当期製品製造原価
$touki_seihin_seizo_genka = $toki_seizo_keihi_total - $kimatsu_shikakari_kin;

// 棚卸資産評価損（CR）6090 00
$res   = array();
$field = array();
$rows  = array();
$hyokason_cr_kin = 0;
$sum1 = '6090';
$sum2 = '00';
$query = sprintf("select SUM(rep_de), SUM(rep_cr) from financial_report_month where rep_ymd>=%d and rep_ymd<=%d and rep_summary1='%s'", $str_ym, $end_ym, $sum1);
if ($rows=getResultWithField2($query, $field, $res) <= 0) {
    $hyokason_cr_kin = 0;
} else {
    $hyokason_cr_kin = $res[0][0] - $res[0][1];
}

// 当期製品製造原価差額計算
// 全体売上原価 AS原価の集計
$res   = array();
$field = array();
$rows  = array();
$genka_as_kin = 0;
$sum1 = '全体売上原価';
$query = sprintf("select SUM(kin) from profit_loss_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='%s'", $str_ym, $end_ym, $sum1);
if ($rows=getResultWithField2($query, $field, $res) <= 0) {
    $genka_as_kin = 0;
} else {
    $genka_as_kin = $res[0][0];
}

$urigen_as_sagaku = $touki_seihin_seizo_genka - $genka_as_kin;


//// 損益計算書
// 売上高
// 全体売上高
$res   = array();
$field = array();
$rows  = array();
$uriage_kin = 0;
$sum1 = '全体売上高';
$query = sprintf("select SUM(kin) from profit_loss_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='%s'", $str_ym, $end_ym, $sum1);
if ($rows=getResultWithField2($query, $field, $res) <= 0) {
    $uriage_kin = 0;
} else {
    $uriage_kin = $res[0][0];
}

// 売上総利益金額
$uriage_sourieki_kin = $uriage_kin - $touki_seihin_seizo_genka;

// 売上総利益差額（上の決算書内での計算とAS直接の数字の比較）
// 全体売上総利益（AS直接の数字）
$res   = array();
$field = array();
$rows  = array();
$sourieki_as_kin = 0;
$sum1 = '全体売上総利益';
$query = sprintf("select SUM(kin) from profit_loss_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='%s'", $str_ym, $end_ym, $sum1);
if ($rows=getResultWithField2($query, $field, $res) <= 0) {
    $sourieki_as_kin = 0;
} else {
    $sourieki_as_kin = $res[0][0];
}
$sourieki_as_sagaku = $uriage_sourieki_kin - $sourieki_as_kin;

// 営業利益金額
$eigyo_rieki_kin = $uriage_sourieki_kin - $han_all_total_kin;

// 営業利益差額（上の決算書内での計算とAS直接の数字の比較）
// 全体営業利益（AS直接の数字）
$res   = array();
$field = array();
$rows  = array();
$eirieki_as_kin = 0;
$sum1 = '全体営業利益';
$query = sprintf("select SUM(kin) from profit_loss_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='%s'", $str_ym, $end_ym, $sum1);
if ($rows=getResultWithField2($query, $field, $res) <= 0) {
    $eirieki_as_kin = 0;
} else {
    $eirieki_as_kin = $res[0][0];
}
$eirieki_as_sagaku = $eigyo_rieki_kin - $eirieki_as_kin;

// 受取利息 9101 00
$res   = array();
$field = array();
$rows  = array();
$uketori_risoku_kin = 0;
$sum1 = '9101';
$sum2 = '00';
$query = sprintf("select SUM(rep_de), SUM(rep_cr) from financial_report_month where rep_ymd>=%d and rep_ymd<=%d and rep_summary1='%s'", $str_ym, $end_ym, $sum1);
if ($rows=getResultWithField2($query, $field, $res) <= 0) {
    $uketori_risoku_kin = 0;
} else {
    $uketori_risoku_kin = -($res[0][0] - $res[0][1]);
}

// 為替差益 ⇒ 為替差益 9206 00（符号逆）－ 為替差損 9303 00
$res   = array();
$field = array();
$rows  = array();
$saeki_temp = 0;
$sum1 = '9206';
$sum2 = '00';
$query = sprintf("select SUM(rep_de), SUM(rep_cr) from financial_report_month where rep_ymd>=%d and rep_ymd<=%d and rep_summary1='%s'", $str_ym, $end_ym, $sum1);
if ($rows=getResultWithField2($query, $field, $res) <= 0) {
    $saeki_temp = 0;
} else {
    $saeki_temp = -($res[0][0] - $res[0][1]);
}
$res   = array();
$field = array();
$rows  = array();
$sason_temp = 0;
$sum1 = '9303';
$sum2 = '00';
$query = sprintf("select SUM(rep_de), SUM(rep_cr) from financial_report_month where rep_ymd>=%d and rep_ymd<=%d and rep_summary1='%s'", $str_ym, $end_ym, $sum1);
if ($rows=getResultWithField2($query, $field, $res) <= 0) {
    $sason_temp = 0;
} else {
    $sason_temp = $res[0][0] - $res[0][1];
}

// 為替差損益計算
if ($saeki_temp > $sason_temp) {
    $kawase_saeki_kin = $saeki_temp - $sason_temp;
    $kawase_sason_kin = 0;
} elseif($saeki_temp < $sason_temp) {
    $kawase_saeki_kin = 0;
    $kawase_sason_kin = $sason_temp - $saeki_temp;
} else {
    $kawase_saeki_kin = 0;
    $kawase_sason_kin = 0;
}

// 固定資産売却益 9201 00
$res   = array();
$field = array();
$rows  = array();
$kotei_baieki_kin = 0;
$sum1 = '9201';
$sum2 = '00';
$query = sprintf("select SUM(rep_de), SUM(rep_cr) from financial_report_month where rep_ymd>=%d and rep_ymd<=%d and rep_summary1='%s'", $str_ym, $end_ym, $sum1);
if ($rows=getResultWithField2($query, $field, $res) <= 0) {
    $kotei_baieki_kin = 0;
} else {
    $kotei_baieki_kin = -($res[0][0] - $res[0][1]);
}

// 雑収入
$res   = array();
$field = array();
$rows  = array();
$zatsu_syu_kin = 0;
$note = '雑収入';
$query = sprintf("SELECT rep_kin FROM financial_report_data WHERE rep_ymd=%d and rep_note='%s'", $yyyymm, $note);
if ($rows=getResultWithField2($query, $field, $res) <= 0) {
    $zatsu_syu_kin = 0;
} else {
    $zatsu_syu_kin = $res[0][0];
}

// 営業外収益 計
$eigai_syueki_kin = $uketori_risoku_kin + $kawase_saeki_kin + $kotei_baieki_kin + $zatsu_syu_kin;

// 全体営業外収益計差額（上の決算書内での計算とAS直接の数字の比較）
// 全体営業外収益計（AS直接の数字）
$res   = array();
$field = array();
$rows  = array();
$gaisyu_as_kin = 0;
$sum1 = '全体営業外収益計';
$query = sprintf("select SUM(kin) from profit_loss_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='%s'", $str_ym, $end_ym, $sum1);
if ($rows=getResultWithField2($query, $field, $res) <= 0) {
    $gaisyu_as_kin = 0;
} else {
    $gaisyu_as_kin = $res[0][0];
}
$gaisyu_as_sagaku = $eigai_syueki_kin - $gaisyu_as_kin;

// 支払利息 8201
$res   = array();
$field = array();
$rows  = array();
$shiharai_risoku_kin = 0;
$sum1 = '8201';
$sum2 = '00';
$query = sprintf("select SUM(rep_de), SUM(rep_cr) from financial_report_month where rep_ymd>=%d and rep_ymd<=%d and rep_summary1='%s'", $str_ym, $end_ym, $sum1);
if ($rows=getResultWithField2($query, $field, $res) <= 0) {
    $shiharai_risoku_kin = 0;
} else {
    $shiharai_risoku_kin = $res[0][0] - $res[0][1];
}

// 固定資産除却損
$res   = array();
$field = array();
$rows  = array();
$kotei_jyoson_kin = 0;
$note = '固定資産除却損';
$query = sprintf("SELECT rep_kin FROM financial_report_data WHERE rep_ymd=%d and rep_note='%s'", $yyyymm, $note);
if ($rows=getResultWithField2($query, $field, $res) <= 0) {
    $kotei_jyoson_kin = 0;
} else {
    $kotei_jyoson_kin = $res[0][0];
}
// 固定資産売却損
$res   = array();
$field = array();
$rows  = array();
$kotei_baison_kin = 0;
$note = '固定資産売却損';
$query = sprintf("SELECT rep_kin FROM financial_report_data WHERE rep_ymd=%d and rep_note='%s'", $yyyymm, $note);
if ($rows=getResultWithField2($query, $field, $res) <= 0) {
    $kotei_baison_kin = 0;
} else {
    $kotei_baison_kin = $res[0][0];
}

// 営業外費用 計
$eigai_hiyo_kin = $shiharai_risoku_kin + $kotei_jyoson_kin + $kotei_baison_kin + $kawase_sason_kin;

// 全体営業外費用計差額（上の決算書内での計算とAS直接の数字の比較）
// 全体営業外費用計（AS直接の数字）
$res   = array();
$field = array();
$rows  = array();
$gaihiyo_as_kin = 0;
$sum1 = '全体営業外費用計';
$query = sprintf("select SUM(kin) from profit_loss_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='%s'", $str_ym, $end_ym, $sum1);
if ($rows=getResultWithField2($query, $field, $res) <= 0) {
    $gaihiyo_as_kin = 0;
} else {
    $gaihiyo_as_kin = $res[0][0];
}
$gaihiyo_as_sagaku = $eigai_hiyo_kin - $gaihiyo_as_kin;

// 経常利益金額
$keijyo_rieki_kin = $eigyo_rieki_kin + $eigai_syueki_kin - $eigai_hiyo_kin;

// 全体経常利益差額（上の決算書内での計算とAS直接の数字の比較）
// 全体経常利益（AS直接の数字）
$res   = array();
$field = array();
$rows  = array();
$keirieki_as_kin = 0;
$sum1 = '全体経常利益';
$query = sprintf("select SUM(kin) from profit_loss_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='%s'", $str_ym, $end_ym, $sum1);
if ($rows=getResultWithField2($query, $field, $res) <= 0) {
    $keirieki_as_kin = 0;
} else {
    $keirieki_as_kin = $res[0][0];
}
$keirieki_as_sagaku = $keijyo_rieki_kin - $keirieki_as_kin;

// 税引前当期純利益金額
$zeimae_jyunrieki_kin = $keijyo_rieki_kin;

// 全体税引前純利益金額差額（上の決算書内での計算とAS直接の数字の比較）
// 全体税引前純利益金額（AS直接の数字）
$res   = array();
$field = array();
$rows  = array();
$zeimaerieki_as_kin = 0;
$sum1 = '全体税引前純利益金額';
$query = sprintf("select SUM(kin) from profit_loss_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='%s'", $str_ym, $end_ym, $sum1);
if ($rows=getResultWithField2($query, $field, $res) <= 0) {
    $zeimaerieki_as_kin = 0;
} else {
    $zeimaerieki_as_kin = $res[0][0];
}
$zeimaerieki_as_sagaku = $zeimae_jyunrieki_kin - $zeimaerieki_as_kin;

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

// 法人税、住民税及び事業税
$hojin_jyumin_jigyo_kin = $hojin_jyumin_zei_kin + $jigyo_zei_kin;

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

// 税金合計の計算
$hojin_zeito_total_kin = $hojin_jyumin_zei_kin + $jigyo_zei_kin + $hojin_chosei_kin;

// 当期純利益金額
$toki_jyunrieki_kin = $zeimae_jyunrieki_kin - $hojin_zeito_total_kin;

// 全体当期純利益金額差額（上の決算書内での計算とAS直接の数字の比較）
// 全体当期純利益金額（AS直接の数字）
$res   = array();
$field = array();
$rows  = array();
$tokijyunrieki_as_kin = 0;
$sum1 = '全体当期純利益金額';
$query = sprintf("select SUM(kin) from profit_loss_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='%s'", $str_ym, $end_ym, $sum1);
if ($rows=getResultWithField2($query, $field, $res) <= 0) {
    $tokijyunrieki_as_kin = 0;
} else {
    $tokijyunrieki_as_kin = $res[0][0];
}
$tokijyunrieki_as_sagaku = $toki_jyunrieki_kin - $tokijyunrieki_as_kin;

//// 株主資本等変動計算書
// 資本金
$res_k   = array();
$field_k = array();
$rows_k  = array();
$shihon_kin = 0;
$sum1 = '4101';
$sum2 = '00';
$query_k = sprintf("select rep_cri, rep_de, rep_cr from financial_report_cal where rep_ymd=%d and rep_summary1='%s' and rep_summary2='%s'", $nk_ki, $sum1, $sum2);
if ($rows_k=getResultWithField2($query_k, $field_k, $res_k) <= 0) {
    $shihon_kishu = 0;
} else {
    $shihon_kishu = -$res_k[0][0];
}
$res   = array();
$field = array();
$rows  = array();
$query = sprintf("select SUM(rep_de), SUM(rep_cr) from financial_report_month where rep_ymd>=%d and rep_ymd<=%d and rep_summary1='%s' and rep_summary2='%s'", $str_ym, $end_ym, $sum1, $sum2);
if ($rows=getResultWithField2($query, $field, $res) <= 0) {
    $shihon_hendo = 0;
} else {
    $shihon_hendo = $res[0][0] - $res[0][1];
}

// 資本金残高
$shihon_kin = $shihon_kishu - $shihon_hendo;

//// 資本剰余金
// 資本準備金
$res_k   = array();
$field_k = array();
$rows_k  = array();
$shihon_jyunbi_kin = 0;
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
$query = sprintf("select SUM(rep_de), SUM(rep_cr) from financial_report_month where rep_ymd>=%d and rep_ymd<=%d and rep_summary1='%s' and rep_summary2='%s'", $str_ym, $end_ym, $sum1, $sum2);
if ($rows=getResultWithField2($query, $field, $res) <= 0) {
    $shihon_jyunbi_hendo = 0;
} else {
    $shihon_jyunbi_hendo = $res[0][0] - $res[0][1];
}

// 資本準備金残高
$shihon_jyunbi_kin = $shihon_jyunbi_kishu - $shihon_jyunbi_hendo;

// その他資本剰余金
$res_k   = array();
$field_k = array();
$rows_k  = array();
$sonota_shihon_jyoyo_kin = 0;
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
$query = sprintf("select SUM(rep_de), SUM(rep_cr) from financial_report_month where rep_ymd>=%d and rep_ymd<=%d and rep_summary1='%s' and rep_summary2='%s'", $str_ym, $end_ym, $sum1, $sum2);
if ($rows=getResultWithField2($query, $field, $res) <= 0) {
    $sonota_shihon_jyoyo_hendo = 0;
} else {
    $sonota_shihon_jyoyo_hendo = $res[0][0] - $res[0][1];
}

// その他資本剰余金残高
$sonota_shihon_jyoyo_kin = $sonota_shihon_jyoyo_kishu - $sonota_shihon_jyoyo_hendo;

//【資本剰余金】合計
$shihon_jyoyo_total_kishu = $shihon_jyunbi_kishu + $sonota_shihon_jyoyo_kishu;
$shihon_jyoyo_total_hendo = $shihon_jyunbi_hendo + $sonota_shihon_jyoyo_hendo;
$shihon_jyoyo_total_kin   = $shihon_jyunbi_kin + $sonota_shihon_jyoyo_kin;

//// 利益剰余金
// 利益準備金 4201 00
$res_k   = array();
$field_k = array();
$rows_k  = array();
$rieki_jyunbi_kin = 0;
$sum1 = '4201';
$sum2 = '00';
$query_k = sprintf("select rep_cri, rep_de, rep_cr from financial_report_cal where rep_ymd=%d and rep_summary1='%s' and rep_summary2='%s'", $nk_ki, $sum1, $sum2);
if ($rows_k=getResultWithField2($query_k, $field_k, $res_k) <= 0) {
    $rieki_jyunbi_kishu = 0;
} else {
    $rieki_jyunbi_kishu = -$res_k[0][0];
}
$res   = array();
$field = array();
$rows  = array();
$query = sprintf("select SUM(rep_de), SUM(rep_cr) from financial_report_month where rep_ymd>=%d and rep_ymd<=%d and rep_summary1='%s' and rep_summary2='%s'", $str_ym, $end_ym, $sum1, $sum2);
if ($rows=getResultWithField2($query, $field, $res) <= 0) {
    $rieki_jyunbi_kin = $rieki_jyunbi_kishu;
} else {
    $rieki_jyunbi_kin = $rieki_jyunbi_kishu + ($res[0][0] - $res[0][1]);
}

// その他利益剰余金 4213 00
$res_k   = array();
$field_k = array();
$rows_k  = array();
$sonota_rieki_jyoyo_kin = 0;
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
$query = sprintf("select SUM(rep_de), SUM(rep_cr) from financial_report_month where rep_ymd>=%d and rep_ymd<=%d and rep_summary1='%s' and rep_summary2='%s'", $str_ym, $end_ym, $sum1, $sum2);
if ($rows=getResultWithField2($query, $field, $res) <= 0) {
    $sonota_rieki_jyoyo_hendo = 0;
} else {
    $sonota_rieki_jyoyo_hendo = $res[0][0] - $res[0][1];
}

// その他利益剰余金残高
$sonota_rieki_jyoyo_kin = $sonota_rieki_jyoyo_kishu - $sonota_rieki_jyoyo_hendo;

// 繰越利益剰余金 4204 00
$res_k   = array();
$field_k = array();
$rows_k  = array();
$kuri_rieki_jyoyo_kin = 0;
$sum1 = '4204';
$sum2 = '00';
$query_k = sprintf("select rep_cri, rep_de, rep_cr from financial_report_cal where rep_ymd=%d and rep_summary1='%s' and rep_summary2='%s'", $nk_ki, $sum1, $sum2);
if ($rows_k=getResultWithField2($query_k, $field_k, $res_k) <= 0) {
    $kuri_rieki_jyoyo_kishu = 0;
} else {
    $kuri_rieki_jyoyo_kishu = -$res_k[0][0];
}
$res   = array();
$field = array();
$rows  = array();
$query = sprintf("select SUM(rep_de), SUM(rep_cr) from financial_report_month where rep_ymd>=%d and rep_ymd<=%d and rep_summary1='%s' and rep_summary2='%s'", $str_ym, $end_ym, $sum1, $sum2);
if ($rows=getResultWithField2($query, $field, $res) <= 0) {
    $kuri_rieki_jyoyo_hendo = 0;
} else {
    $kuri_rieki_jyoyo_hendo = -($res[0][0] - $res[0][1]);
}

if ($mm != '03') {
    $kuri_rieki_jyoyo_hendo = $toki_jyunrieki_kin;
}

// 繰越利益剰余金残高
$kuri_rieki_jyoyo_kin = $kuri_rieki_jyoyo_kishu + $kuri_rieki_jyoyo_hendo;

////【利益剰余金】合計
$rieki_jyoyo_total_kishu = $sonota_rieki_jyoyo_kishu + $kuri_rieki_jyoyo_kishu;
$rieki_jyoyo_total_hendo = $sonota_rieki_jyoyo_hendo + $kuri_rieki_jyoyo_hendo;
$rieki_jyoyo_total_kin   = $sonota_rieki_jyoyo_kin + $kuri_rieki_jyoyo_kin;

////《純資産合計》
$jyun_shisan_total_kishu = $shihon_kishu + $shihon_jyoyo_total_kishu + $rieki_jyoyo_total_kishu;
$jyun_shisan_total_hendo = $shihon_hendo + $shihon_jyoyo_total_hendo + $rieki_jyoyo_total_hendo;
$jyun_shisan_total_kin   = $shihon_kin + $shihon_jyoyo_total_kin + $rieki_jyoyo_total_kin;

if (isset($_POST['input_data'])) {                          // 当月データの登録
    ///////// 項目とインデックスの関連付け
    $item = array();
    $item[0]  = "売掛金";
    $item[1]  = "前払費用";
    $item[2]  = "建設仮勘定";
    $item[3]  = "ソフトウエアー";
    $item[4]  = "その他無形固定資産";                       // 施設利用権
    $item[5]  = "従業員長期貸付金";                         // 長期貸付金
    $item[6]  = "長期前払費用";
    $item[7]  = "繰延税金資産（固定）";
    $item[8]  = "未払費用";
    $item[9]  = "未払法人税等";
    $item[10] = "預り金";
    $item[11] = "リース債務（短期）";
    $item[12] = "リース債務（長期）";
    $item[13] = "賞与引当金";
    $item[14] = "長期未払金";
    $item[15] = "退職給付引当金";
    $item[16] = "資本金";
    $item[17] = "資本剰余金";
    $item[18] = "利益剰余金";
    $item[19] = "売上高";
    $item[20] = "当期製品製造原価";
    $item[21] = "eca運賃荷造費（荷造発送費）";              // 販管費
    $item[22] = "eca役員報酬";                              // 販管費
    $item[23] = "eca給料";                                  // 販管費
    $item[24] = "eca賞与";                                  // 販管費賞与手当
    $item[25] = "eca賞与引当金繰入";                        // 販管費賞与引当金繰入
    $item[26] = "eca顧問料";                                // 販管費
    $item[27] = "eca退職給付費用";                          // 販管費
    $item[28] = "eca通信費";                                // 販管費
    $item[29] = "eca交通費";                                // 販管費旅費交通費と海外出張費
    $item[30] = "eca減価償却費";                            // 販管費
    $item[31] = "eca租税公課";                              // 販管費諸税公課
    $item[32] = "eca賃借料";                                // 販管費
    $item[33] = "eca修繕費";                                // 販管費
    $item[34] = "eca交際接待費";                            // 販管費
    $item[35] = "eca事務用消耗品費";                        // 販管費
    $item[36] = "eca保険料";                                // 販管費
    $item[37] = "eca水道光熱費";                            // 販管費
    $item[38] = "eca車両費";                                // 販管費
    $item[39] = "eca図書教育費";                            // 販管費
    $item[40] = "eca購読料・寄付金";                        // 販管費
    $item[41] = "eca会議費";                                // 販管費
    $item[42] = "eca受取利息及び割引料";                    // 販管費
    $item[43] = "eca為替差益";                              // 販管費
    $item[44] = "eca固定資産売却益";                        // 販管費
    $item[45] = "eca当期純利益";                            // 販管費
    $item[46] = "eca為替差損";                              // 販管費
    $item[47] = "法人税等調整額";                           // 販管費
    $item[48] = "利益剰余金期首高合計";                     // 販管費
    ///////// 各データの保管
    $input_data = array();
    $input_data[0]  = $urikake_kin;
    $input_data[1]  = $mae_hiyo_kin;
    $input_data[2]  = $kenkari_kin;
    $input_data[3]  = $soft_shisan_kin;
    $input_data[4]  = $shisetsu_shisan_kin;                 // 施設利用権
    $input_data[5]  = $choki_kashi_kin;
    $input_data[6]  = $choki_maebara_kin;
    $input_data[7]  = $kotei_kuri_zei_kin;
    $input_data[8]  = $miharai_hiyo_kin;
    $input_data[9]  = $miharai_hozei_kin;
    $input_data[10] = $azukari_kin;
    $input_data[11] = $lease_tanki_kin;
    $input_data[12] = $lease_choki_kin;
    $input_data[13] = $syoyo_hikiate_kin;
    $input_data[14] = $choki_miharai_kin;
    $input_data[15] = $taisyoku_hikiate_kin;
    $input_data[16] = $shihon_kin;
    $input_data[17] = $shihon_jyoyo_total_kin;
    $input_data[18] = $rieki_jyoyo_total_kin;
    $input_data[19] = $uriage_kin;
    $input_data[20] = $touki_seihin_seizo_genka;
    $input_data[21] = $nizukuri_han_kin;
    $input_data[22] = $yakuin_han_kin;
    $input_data[23] = $kyuryo_han_kin;
    $input_data[24] = $syoyo_teate_han_kin;
    $input_data[25] = $syoyo_hikiate_han_kin;
    $input_data[26] = $komon_han_kin;
    $input_data[27] = $tai_kyufu_han_kin;
    $input_data[28] = $tsushin_han_kin;
    $input_data[29] = $ryohi_han_kin;
    $input_data[30] = $genkasyo_han_kin;
    $input_data[31] = $syozei_han_kin;
    $input_data[32] = $chin_han_kin;
    $input_data[33] = $syuzen_han_kin;
    $input_data[34] = $kosai_han_kin;
    $input_data[35] = $jimu_han_kin;
    $input_data[36] = $hoken_han_kin;
    $input_data[37] = $suido_han_kin;
    $input_data[38] = $syaryo_han_kin;
    $input_data[39] = $tosyo_han_kin;
    $input_data[40] = $kifu_han_kin;
    $input_data[41] = $kaigi_han_kin;
    $input_data[42] = $uketori_risoku_kin;
    $input_data[43] = $kawase_saeki_kin;
    $input_data[44] = $kotei_baieki_kin;
    $input_data[45] = $toki_jyunrieki_kin;
    $input_data[46] = $kawase_sason_kin;
    $input_data[47] = $hojin_chosei_kin;
    $input_data[48] = $rieki_jyoyo_total_kishu;
    ///////// 各データの登録
    
    insert_date($item,$yyyymm,$input_data);
}

$csv_num = count($csv_data);
for ($r=0; $r<$csv_num; $r++) {
    $csv_data[$r][0] = mb_convert_encoding($csv_data[$r][0], 'SJIS', 'auto');   // CSV用にEUCからSJISへ文字コード変換
}
/*
// eCA用 CSVデータの出力
// ここからがCSVファイルの作成（一時ファイルをサーバーに作成）
$outputFile = 'eca_data.csv';
$fp = fopen($outputFile, "w");
foreach($csv_data as $line){
    fputcsv($fp,$line);         // ここでCSVファイルに書き出し
}
fclose($fp);
// ここからがCSVファイルのダウンロード（サーバー→クライアント）
touch($outputFile);
header("Content-Type: application/csv");
header("Content-Disposition: attachment; filename=".$outputFile);
header("Content-Length:".filesize($outputFile));
readfile($outputFile);
unlink("{$outputFile}");         // ダウンロード後ファイルを削除
*/

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
        <table class='winbox_field' width='100%' align='center' bgcolor='black' cellspacing='0' cellpadding='5'>
            <THEAD>
                <!-- テーブル ヘッダーの表示 -->
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' colspan='3' align='center'>
                        <div class='pt10b'>資産の部</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' colspan='3' align='center'>
                        <div class='pt10b'>負債及び純資産の部</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none;border-right:none;border-top:none'>
                        <div class='pt11b'>　</div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' colspan='2' align='center'>
                        <div class='pt10b'>科目</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center'>
                        <div class='pt10b'>金額</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' colspan='2' align='center'>
                        <div class='pt10b'>科目</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center'>
                        <div class='pt10b'>金額</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none;border-right:none'>
                        <div class='pt11b'>　</div>
                    </td>
                </tr>
            </THEAD>
            <TFOOT>
                <!-- 現在はフッターは何もない -->
            </TFOOT>
            <TBODY>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' colspan='2' style='border-bottom:none'>
                        <div class='pt10b'>流動資産</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none'>
                        <div class='pt11b'>(<?= number_format($ryudo_total_kin) ?>)</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' colspan='2' style='border-bottom:none'>
                        <div class='pt10b'>負債の部</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none'>
                        <div class='pt11b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none;border-right:none'>
                        <div class='pt11b'>　</div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none;border-right:none'>
                        <div class='pt11b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' style='border-bottom:none'>
                        <div class='pt10b'>現金及び預金</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none'>
                        <div class='pt11b'><?= number_format($genkin_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' colspan='2' style='border-bottom:none'>
                        <div class='pt10b'>流動負債</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none'>
                        <div class='pt11b'>(<?= number_format($ryudo_fusai_total_kin) ?>)</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none;border-right:none'>
                        <div class='pt11b'>　</div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none;border-right:none'>
                        <div class='pt11b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' style='border-bottom:none'>
                        <div class='pt10b'>売掛金</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none'>
                        <div class='pt11b'><?= number_format($urikake_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none;border-right:none'>
                        <div class='pt11b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' style='border-bottom:none'>
                        <div class='pt10b'>買掛金</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none'>
                        <div class='pt11b'><?= number_format($kaikake_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none;border-right:none'>
                        <div class='pt11b'>　</div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none;border-right:none'>
                        <div class='pt11b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' style='border-bottom:none'>
                        <div class='pt10b'>仕掛品</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none'>
                        <div class='pt11b'><?= number_format($tai_shikakari_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none;border-right:none'>
                        <div class='pt11b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' style='border-bottom:none'>
                        <div class='pt10b'>リース債務</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none'>
                        <div class='pt11b'><?= number_format($lease_tanki_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none;border-right:none'>
                        <div class='pt11b'>　</div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none;border-right:none'>
                        <div class='pt11b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' style='border-bottom:none'>
                        <div class='pt10b'>原材料及び貯蔵品</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none'>
                        <div class='pt11b'><?= number_format($tai_zairyo_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none;border-right:none'>
                        <div class='pt11b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' style='border-bottom:none'>
                        <div class='pt10b'>未払金</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none'>
                        <div class='pt11b'><?= number_format($miharai_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none;border-right:none'>
                        <div class='pt11b'>　</div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none;border-right:none'>
                        <div class='pt11b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' style='border-bottom:none'>
                        <div class='pt10b'>前払費用</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none'>
                        <div class='pt11b'><?= number_format($mae_hiyo_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none;border-right:none'>
                        <div class='pt11b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' style='border-bottom:none'>
                        <div class='pt10b'>未払消費税等</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none'>
                        <div class='pt11b'><?= number_format($miharai_shozei_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none;border-right:none'>
                        <div class='pt11b'>　</div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none;border-right:none'>
                        <div class='pt11b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' style='border-bottom:none'>
                        <div class='pt10b'>未収入金</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none'>
                        <div class='pt11b'><?= number_format($mishu_kin_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none;border-right:none'>
                        <div class='pt11b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' style='border-bottom:none'>
                        <div class='pt10b'>未払法人税等</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none'>
                        <div class='pt11b'><?= number_format($miharai_hozei_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none;border-right:none'>
                        <div class='pt11b'>　</div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none;border-right:none'>
                        <div class='pt11b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' style='border-bottom:none'>
                        <div class='pt10b'>未収消費税等</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none'>
                        <div class='pt11b'><?= number_format($mishu_shozei_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' colspan='2' style='border-bottom:none'>
                        <div class='pt11b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none'>
                        <div class='pt11b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none;border-right:none'>
                        <div class='pt11b'>　</div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none;border-right:none'>
                        <div class='pt11b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' style='border-bottom:none'>
                        <div class='pt10b'>その他の流動資産</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none'>
                        <div class='pt11b'><?= number_format($ta_ryudo_shisan_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none;border-right:none'>
                        <div class='pt11b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' style='border-bottom:none'>
                        <div class='pt10b'>未払費用</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none'>
                        <div class='pt11b'><?= number_format($miharai_hiyo_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none;border-right:none'>
                        <div class='pt11b'>　</div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' colspan='2' style='border-bottom:none'>
                        <div class='pt10b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none'>
                        <div class='pt11b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none;border-right:none'>
                        <div class='pt11b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' style='border-bottom:none'>
                        <div class='pt10b'>預り金</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none'>
                        <div class='pt11b'><?= number_format($azukari_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none;border-right:none'>
                        <div class='pt11b'>　</div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' colspan='2' style='border-bottom:none'>
                        <div class='pt10b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none'>
                        <div class='pt11b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none;border-right:none'>
                        <div class='pt11b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' style='border-bottom:none'>
                        <div class='pt10b'>賞与引当金</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none'>
                        <div class='pt11b'><?= number_format($syoyo_hikiate_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none;border-right:none'>
                        <div class='pt11b'>　</div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' colspan='2' style='border-bottom:none'>
                        <div class='pt10b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none'>
                        <div class='pt11b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' colspan='2' style='border-bottom:none'>
                        <div class='pt10b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none'>
                        <div class='pt11b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none;border-right:none'>
                        <div class='pt11b'>　</div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' colspan='2' style='border-bottom:none'>
                        <div class='pt10b'>固定資産</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none'>
                        <div class='pt11b'>(<?= number_format($kotei_shisan_total_kin) ?>)</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' colspan='2' style='border-bottom:none'>
                        <div class='pt10b'>固定負債</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none'>
                        <div class='pt11b'>(<?= number_format($kotei_fusai_kin) ?>)</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none;border-right:none'>
                        <div class='pt11b'>　</div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' colspan='2' style='border-bottom:none'>
                        <div class='pt10b'>有形固定資産</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none'>
                        <div class='pt11b'>(<?= number_format($yukei_shisan_kin) ?>)</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none;border-right:none'>
                        <div class='pt11b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' style='border-bottom:none'>
                        <div class='pt10b'>リース債務</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none'>
                        <div class='pt11b'><?= number_format($lease_choki_kin) ?>    </div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none;border-right:none'>
                        <div class='pt11b'>　</div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none;border-right:none'>
                        <div class='pt11b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' style='border-bottom:none'>
                        <div class='pt10b'>建物</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none'>
                        <div class='pt11b'><?= number_format($tatemono_shisan_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none;border-right:none'>
                        <div class='pt11b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' style='border-bottom:none'>
                        <div class='pt10b'>長期未払金</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none'>
                        <div class='pt11b'><?= number_format($choki_miharai_kin) ?>    </div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none;border-right:none'>
                        <div class='pt11b'>　</div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none;border-right:none'>
                        <div class='pt11b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' style='border-bottom:none'>
                        <div class='pt10b'>機械及び装置</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none'>
                        <div class='pt11b'><?= number_format($kikai_shisan_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none;border-right:none'>
                        <div class='pt11b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' style='border-bottom:none'>
                        <div class='pt10b'>退職給付引当金</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none'>
                        <div class='pt11b'><?= number_format($taisyoku_hikiate_kin) ?>    </div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none;border-right:none'>
                        <div class='pt11b'>　</div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none;border-right:none'>
                        <div class='pt11b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' style='border-bottom:none'>
                        <div class='pt10b'>車輌運搬具</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none'>
                        <div class='pt11b'><?= number_format($sharyo_shisan_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' colspan='2' style='border-bottom:none'>
                        <div class='pt11b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none'>
                        <div class='pt11b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none;border-right:none'>
                        <div class='pt11b'>　</div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none;border-right:none'>
                        <div class='pt11b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' style='border-bottom:none'>
                        <div class='pt10b'>工具器具及び備品</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none'>
                        <div class='pt11b'><?= number_format($kougu_shisan_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none;border-right:none'>
                        <div class='pt11b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' style='border-bottom:none'>
                        <div class='pt10b'>負債合計</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none'>
                        <div class='pt11b'><?= number_format($fusai_total_kin) ?>    </div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none;border-right:none'>
                        <div class='pt11b'>　</div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none;border-right:none'>
                        <div class='pt11b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' style='border-bottom:none'>
                        <div class='pt10b'>リース資産</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none'>
                        <div class='pt11b'><?= number_format($lease_shisan_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' colspan='2' style='border-bottom:none'>
                        <div class='pt11b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none'>
                        <div class='pt11b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none;border-right:none'>
                        <div class='pt11b'>　</div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none;border-right:none'>
                        <div class='pt11b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' style='border-bottom:none'>
                        <div class='pt10b'>建設仮勘定</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none'>
                        <div class='pt11b'><?= number_format($kenkari_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' colspan='2' style='border-bottom:none'>
                        <div class='pt11b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none'>
                        <div class='pt11b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none;border-right:none'>
                        <div class='pt11b'>　</div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' colspan='2' style='border-bottom:none'>
                        <div class='pt10b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none'>
                        <div class='pt11b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' colspan='2' style='border-bottom:none'>
                        <div class='pt10b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none'>
                        <div class='pt11b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none;border-right:none'>
                        <div class='pt11b'>　</div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' colspan='2' style='border-bottom:none'>
                        <div class='pt10b'>無形固定資産</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none'>
                        <div class='pt11b'>(<?= number_format($mukei_shisan_kin) ?>)</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' colspan='2' style='border-bottom:none'>
                        <div class='pt10b'>純資産の部</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none'>
                        <div class='pt11b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none;border-right:none'>
                        <div class='pt11b'>　</div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none;border-right:none'>
                        <div class='pt11b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' style='border-bottom:none'>
                        <div class='pt10b'>電話加入権</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none'>
                        <div class='pt11b'><?= number_format($denwa_shisan_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' colspan='2' style='border-bottom:none'>
                        <div class='pt10b'>株主資本</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none'>
                        <div class='pt11b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none;border-right:none'>
                        <div class='pt11b'>　</div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none;border-right:none'>
                        <div class='pt11b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' style='border-bottom:none'>
                        <div class='pt10b'>施設利用権</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none'>
                        <div class='pt11b'><?= number_format($shisetsu_shisan_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' colspan='2' style='border-bottom:none'>
                        <div class='pt10b'>資本金</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none'>
                        <div class='pt11b'>(<?= number_format($shihon_total_kin) ?>)</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none;border-right:none'>
                        <div class='pt11b'>　</div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none;border-right:none'>
                        <div class='pt11b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' style='border-bottom:none'>
                        <div class='pt10b'>ソフトウェア</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none'>
                        <div class='pt11b'><?= number_format($soft_shisan_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none;border-right:none'>
                        <div class='pt11b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' style='border-bottom:none'>
                        <div class='pt10b'>資本金</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none'>
                        <div class='pt11b'><?= number_format($shihon_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none;border-right:none'>
                        <div class='pt11b'>　</div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' colspan='2' style='border-bottom:none'>
                        <div class='pt11b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none'>
                        <div class='pt11b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' colspan='2' style='border-bottom:none'>
                        <div class='pt10b'>資本剰余金</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none'>
                        <div class='pt11b'>(<?= number_format($tai_shihon_jyoyo_total_kin) ?>)</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none;border-right:none'>
                        <div class='pt11b'>　</div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' colspan='2' style='border-bottom:none'>
                        <div class='pt10b'>投資その他の資産</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none'>
                        <div class='pt11b'>(<?= number_format($toshi_sonota_kin) ?>)</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none;border-right:none'>
                        <div class='pt11b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' style='border-bottom:none'>
                        <div class='pt10b'>資本準備金</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none'>
                        <div class='pt11b'><?= number_format($shihon_jyunbi_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none;border-right:none'>
                        <div class='pt11b'>　</div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none;border-right:none'>
                        <div class='pt11b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' style='border-bottom:none'>
                        <div class='pt10b'>長期貸付金</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none'>
                        <div class='pt11b'><?= number_format($choki_kashi_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none;border-right:none'>
                        <div class='pt11b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' style='border-bottom:none'>
                        <div class='pt10b'>その他資本剰余金</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none'>
                        <div class='pt11b'><?= number_format($sonota_shihon_jyoyo_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none;border-right:none'>
                        <div class='pt11b'>　</div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none;border-right:none'>
                        <div class='pt11b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' style='border-bottom:none'>
                        <div class='pt10b'>長期前払費用</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none'>
                        <div class='pt11b'><?= number_format($choki_maebara_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' colspan='2' style='border-bottom:none'>
                        <div class='pt10b'>利益剰余金</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none'>
                        <div class='pt11b'>(<?= number_format($tai_rieki_jyoyo_total_kin) ?>)</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none;border-right:none'>
                        <div class='pt11b'>　</div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none;border-right:none'>
                        <div class='pt11b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' style='border-bottom:none'>
                        <div class='pt10b'>繰延税金資産</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none'>
                        <div class='pt11b'><?= number_format($kotei_kuri_zei_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none;border-right:none'>
                        <div class='pt11b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' style='border-bottom:none'>
                        <div class='pt10b'>その他利益剰余金</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none'>
                        <div class='pt11b'><?= number_format($tai_sonota_rieki_jyoyo_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none;border-right:none'>
                        <div class='pt11b'>　</div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none;border-right:none'>
                        <div class='pt11b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' style='border-bottom:none'>
                        <div class='pt10b'>その他の投資等</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none'>
                        <div class='pt11b'><?= number_format($sonota_toshi_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none;border-right:none'>
                        <div class='pt11b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' style='border-bottom:none'>
                        <div class='pt10b'>繰越利益剰余金</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none'>
                        <div class='pt11b'><?= number_format($tai_kuri_rieki_jyoyo_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none;border-right:none'>
                        <div class='pt11b'>　</div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' colspan='2' style='border-bottom:none'>
                        <div class='pt10b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none'>
                        <div class='pt11b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' colspan='2' style='border-bottom:none'>
                        <div class='pt10b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none'>
                        <div class='pt11b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none;border-right:none'>
                        <div class='pt11b'>　</div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' colspan='2' style='border-bottom:none'>
                        <div class='pt11b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none'>
                        <div class='pt11b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none;border-right:none'>
                        <div class='pt11b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' style='border-bottom:none'>
                        <div class='pt10b'>純資産合計</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none'>
                        <div class='pt11b'><?= number_format($tai_jyun_shisan_total_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none;border-right:none'>
                        <div class='pt11b'>　</div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' colspan='2'>
                        <div class='pt10b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right'>
                        <div class='pt11b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' colspan='2'>
                        <div class='pt10b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right'>
                        <div class='pt11b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-top:none;border-right:none'>
                        差額
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' colspan='2'>
                        <div class='pt10b'>資産合計</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right'>
                        <div class='pt11b'><?= number_format($shisan_total_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' colspan='2'>
                        <div class='pt10b'>負債及び純資産合計</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right'>
                        <div class='pt11b'><?= number_format($fusai_jyunshi_total_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right'>
                        <div class='pt11b'><?= number_format($tai_sagaku_kin) ?></div>
                    </td>
                </tr>
                
                
            </TBODY>
        </table>
            </td></tr>
        </table> <!----------------- ダミーEnd ------------------>
        
        <table bgcolor='#ffffff' align='center' cellspacing='0' cellpadding='10'>
            <tr><td> <!----------- ダミー(デザイン用) ------------>
        <center>（損益計算書）</center>
        <table class='winbox_field' width='100%' align='center' bgcolor='black' cellspacing='0' cellpadding='5'>
            <THEAD>
                <!-- テーブル ヘッダーの表示 -->
            </THEAD>
            <TFOOT>
                <!-- 現在はフッターは何もない -->
            </TFOOT>
            <TBODY>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='left' colspan='5' style='border-bottom:none'>
                        <div class='pt10b'>Ⅰ．営  業  収  益</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none;border-right:none;border-top:none'>
                        <div class='pt11b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none;border-right:none;border-top:none'>
                        <div class='pt11b'>　</div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none;border-right:none'>
                        <div class='pt10b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='left' style='border-bottom:none;border-right:none'>
                        <div class='pt10b'>　売  上  高</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none;border-right:none'>
                        <div class='pt10b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none;border-right:none'>
                        <div class='pt10b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none'>
                        <div class='pt11b'><?= number_format($uriage_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none;border-right:none;border-top:none'>
                        <div class='pt11b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none;border-right:none;border-top:none'>
                        <div class='pt11b'>　</div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='left' colspan='5' style='border-bottom:none'>
                        <div class='pt10b'>Ⅱ．営  業  費  用</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none;border-right:none;border-top:none'>
                        <div class='pt11b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none;border-right:none;border-top:none'>
                        <div class='pt11b'>　</div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none;border-right:none'>
                        <div class='pt10b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='left' style='border-bottom:none;border-right:none'>
                        <div class='pt10b'>１．売  上  原  価</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none;border-right:none'>
                        <div class='pt10b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none;border-right:none'>
                        <div class='pt10b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none'>
                        <div class='pt10b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none;border-right:none;border-top:none'>
                        <div class='pt11b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none;border-right:none;border-top:none'>
                        <div class='pt11b'>　</div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none;border-right:none'>
                        <div class='pt10b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none;border-right:none'>
                        <div class='pt10b'>　当期製品製造原価</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-right:none;border-bottom:1pt solid windowtext'>
                        <div class='pt11b'><?= number_format($touki_seihin_seizo_genka) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none;border-right:none'>
                        <div class='pt10b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:1pt solid windowtext'>
                        <div class='pt11b'><?= number_format($touki_seihin_seizo_genka) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-right:none;border-top:none'>
                        <div class='pt11b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none;border-right:none;border-top:none'>
                        <div class='pt11b'>　</div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none;border-right:none'>
                        <div class='pt10b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none;border-right:none'>
                        <div class='pt10b'>　売上総利益金額</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none;border-right:none'>
                        <div class='pt10b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none;border-right:none'>
                        <div class='pt10b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none'>
                        <div class='pt11b'><?= number_format($uriage_sourieki_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right'>
                        <div class='pt11b'><?= number_format($sourieki_as_sagaku) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none;border-right:none;border-top:none'>
                        <div class='pt11b'>　</div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none;border-right:none'>
                        <div class='pt10b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='left' style='border-bottom:none;border-right:none'>
                        <div class='pt10b'>２．販売費及び一般管理費</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none;border-right:none'>
                        <div class='pt10b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none;border-right:none'>
                        <div class='pt10b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:1pt solid windowtext'>
                        <div class='pt11b'><?= number_format($han_all_total_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-right:none'>
                        <div class='pt11b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none;border-right:none;border-top:none'>
                        <div class='pt11b'>　</div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none;border-right:none'>
                        <div class='pt10b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none;border-right:none'>
                        <div class='pt10b'>　営 業 利 益 金 額</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none;border-right:none'>
                        <div class='pt10b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none;border-right:none'>
                        <div class='pt10b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none'>
                        <div class='pt11b'><?= number_format($eigyo_rieki_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right'>
                        <div class='pt11b'><?= number_format($eirieki_as_sagaku) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none;border-right:none;border-top:none'>
                        <div class='pt11b'>　</div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='left' colspan='5' style='border-bottom:none'>
                        <div class='pt10b'>Ⅲ．営  業  外  収  益</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none;border-right:none;border-top:none'>
                        <div class='pt11b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none;border-right:none;border-top:none'>
                        <div class='pt11b'>　</div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none;border-right:none'>
                        <div class='pt10b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='left' style='border-bottom:none;border-right:none'>
                        <div class='pt10b'>受  取  利  息</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none;border-right:none'>
                        <div class='pt11b'><?= number_format($uketori_risoku_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none;border-right:none'>
                        <div class='pt10b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none'>
                        <div class='pt10b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-right:none;border-top:none'>
                        <div class='pt11b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none;border-right:none;border-top:none'>
                        <div class='pt11b'>　</div>
                    </td>
                </tr>
                <?php
                if ($kawase_saeki_kin <> 0) {
                ?>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none;border-right:none'>
                        <div class='pt10b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='left' style='border-bottom:none;border-right:none'>
                        <div class='pt10b'>為　替　差　益</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none;border-right:none'>
                        <div class='pt11b'><?= number_format($kawase_saeki_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none;border-right:none'>
                        <div class='pt10b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none'>
                        <div class='pt10b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-right:none;border-top:none'>
                        <div class='pt10b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none;border-right:none'>
                        <div class='pt10b'>　</div>
                    </td>
                </tr>
                <?php
                }
                ?>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none;border-right:none'>
                        <div class='pt10b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='left' style='border-bottom:none;border-right:none'>
                        <div class='pt10b'>固定資産売却益</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none;border-right:none'>
                        <div class='pt11b'><?= number_format($kotei_baieki_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none;border-right:none'>
                        <div class='pt10b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none'>
                        <div class='pt10b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-right:none;border-top:none'>
                        <div class='pt10b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none;border-right:none'>
                        <div class='pt10b'>　</div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none;border-right:none'>
                        <div class='pt10b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='left' style='border-bottom:none;border-right:none'>
                        <div class='pt10b'>雑    収    入</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-right:none;border-bottom:1pt solid windowtext'>
                        <div class='pt11b'><?= number_format($zatsu_syu_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none;border-right:none'>
                        <div class='pt10b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none'>
                        <div class='pt11b'><?= number_format($eigai_syueki_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right'>
                        <div class='pt11b'><?= number_format($gaisyu_as_sagaku) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='left' style='border-bottom:none;border-right:none'>
                        ※１
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='left' colspan='5' style='border-bottom:none'>
                        <div class='pt10b'>Ⅳ．営  業  外  費  用</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none;border-right:none'>
                        <div class='pt11b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none;border-right:none'>
                        <div class='pt11b'>　</div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none;border-right:none'>
                        <div class='pt10b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='left' style='border-bottom:none;border-right:none'>
                        <div class='pt10b'>支　払　利　息</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none;border-right:none'>
                        <div class='pt11b'><?= number_format($shiharai_risoku_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none;border-right:none'>
                        <div class='pt10b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none'>
                        <div class='pt10b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none;border-right:none'>
                        <div class='pt11b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none;border-right:none'>
                        <div class='pt11b'>　</div>
                    </td>
                </tr>
                <?php
                if ($kawase_sason_kin <> 0) {
                ?>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none;border-right:none'>
                        <div class='pt10b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='left' style='border-bottom:none;border-right:none'>
                        <div class='pt10b'>為　替　差　損</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none;border-right:none'>
                        <div class='pt11b'><?= number_format($kawase_sason_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none;border-right:none'>
                        <div class='pt10b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none'>
                        <div class='pt10b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none;border-right:none'>
                        <div class='pt11b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none;border-right:none'>
                        <div class='pt11b'>　</div>
                    </td>
                </tr>
                <?php
                }
                ?>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none;border-right:none'>
                        <div class='pt10b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='left' style='border-bottom:none;border-right:none'>
                        <div class='pt10b'>固定資産売却損</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none;border-right:none'>
                        <div class='pt11b'><?= number_format($kotei_baison_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none;border-right:none'>
                        <div class='pt10b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none'>
                        <div class='pt11b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-right:none'>
                        <div class='pt11b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-right:none;border-bottom:none'>
                        <div class='pt11b'>　</div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none;border-right:none'>
                        <div class='pt10b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='left' style='border-bottom:none;border-right:none'>
                        <div class='pt10b'>固定資産除却損</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-right:none;border-bottom:1pt solid windowtext'>
                        <div class='pt11b'><?= number_format($kotei_jyoson_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none;border-right:none'>
                        <div class='pt10b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:1pt solid windowtext'>
                        <div class='pt11b'><?= number_format($eigai_hiyo_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right'>
                        <div class='pt11b'><?= number_format($gaihiyo_as_sagaku) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='left' style='border-bottom:none;border-right:none'>
                        ※１と一致
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none;border-right:none'>
                        <div class='pt10b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none;border-right:none'>
                        <div class='pt10b'>　経 常 利 益 金 額</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none;border-right:none'>
                        <div class='pt10b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none;border-right:none'>
                        <div class='pt10b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:1pt solid windowtext'>
                        <div class='pt11b'><?= number_format($keijyo_rieki_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right'>
                        <div class='pt11b'><?= number_format($keirieki_as_sagaku) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-right:none;border-bottom:none'>
                        <div class='pt11b'>　</div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='left' colspan='2' style='border-bottom:none;border-right:none'>
                        <div class='pt10b'>　税引前当期純利益金額</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none;border-right:none'>
                        <div class='pt10b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none;border-right:none'>
                        <div class='pt10b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none'>
                        <div class='pt11b'><?= number_format($zeimae_jyunrieki_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right'>
                        <div class='pt11b'><?= number_format($zeimaerieki_as_sagaku) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-right:none;border-bottom:none'>
                        <div class='pt11b'>　</div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='left' colspan='2' style='border-bottom:none;border-right:none'>
                        <div class='pt10b'>　法人税、住民税及び事業税</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none;border-right:none'>
                        <div class='pt11b'><?= number_format($hojin_jyumin_jigyo_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none;border-right:none'>
                        <div class='pt10b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none'>
                        <div class='pt10b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-right:none;border-bottom:none'>
                        <div class='pt11b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-right:none;border-bottom:none'>
                        <div class='pt11b'>　</div>
                    </td>
                </tr>
                <!--
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='left' colspan='2'>
                        <div class='pt10b'>　過年度法人税等</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right'>
                        <div class='pt11b'><?= number_format($kishu_zairyo_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center'>
                        <div class='pt10b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center'>
                        <div class='pt10b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-right:none'>
                        <div class='pt11b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-right:none;border-bottom:none'>
                        <div class='pt11b'>　</div>
                    </td>
                </tr>
                -->
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='left' colspan='2' style='border-bottom:none;border-right:none'>
                        <div class='pt10b'>　法人税等調整額</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-right:none;border-bottom:1pt solid windowtext'>
                        <div class='pt11b'><?= number_format($hojin_chosei_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none;border-right:none'>
                        <div class='pt10b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:1pt solid windowtext'>
                        <div class='pt11b'><?= number_format($hojin_zeito_total_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-right:none'>
                        <div class='pt11b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-right:none;border-bottom:none'>
                        <div class='pt11b'>　</div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='left' colspan='2' style='border-right:none'>
                        <div class='pt10b'>　当期純利益金額</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-right:none'>
                        <div class='pt10b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-right:none'>
                        <div class='pt10b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right'>
                        <div class='pt11b'><?= number_format($toki_jyunrieki_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right'>
                        <div class='pt11b'><?= number_format($tokijyunrieki_as_sagaku) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-right:none;border-bottom:none'>
                        <div class='pt11b'>　</div>
                    </td>
                </tr>
            </TBODY>
        </table>
            </td></tr>
        </table> <!----------------- ダミーEnd ------------------>
                
        <table bgcolor='#ffffff' align='center' cellspacing='0' cellpadding='10'>
            <tr><td> <!----------- ダミー(デザイン用) ------------>
        <center>（製造原価報告書）</center>
        <table class='winbox_field' width='100%' align='center' bgcolor='black' cellspacing='0' cellpadding='5'>
            <THEAD>
                <!-- テーブル ヘッダーの表示 -->
            </THEAD>
            <TFOOT>
                <!-- 現在はフッターは何もない -->
            </TFOOT>
            <TBODY>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none;border-right:none'>
                        <div class='pt10b'>Ⅰ．材    料    費</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none;border-right:none'>
                        <div class='pt10b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none;border-right:none'>
                        <div class='pt10b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none'>
                        <div class='pt10b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none;border-right:none'>
                        <div class='pt10b'>　</div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none;border-right:none'>
                        <div class='pt10b'>　　期首材料棚卸高</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none;border-right:none'>
                        <div class='pt10b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none;border-right:none'>
                        <div class='pt11b'><?= number_format($kishu_zairyo_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none'>
                        <div class='pt10b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none;border-right:none'>
                        <div class='pt10b'>　</div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none;border-right:none'>
                        <div class='pt10b'>　　当期材料仕入高</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none;border-right:none'>
                        <div class='pt10b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-right:none;border-bottom:1pt solid windowtext'>
                        <div class='pt11b'><?= number_format($touki_shiire_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none'>
                        <div class='pt10b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none;border-right:none'>
                        <div class='pt10b'>　</div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none;border-right:none'>
                        <div class='pt10b'>　　合      計</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none;border-right:none'>
                        <div class='pt10b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none;border-right:none'>
                        <div class='pt11b'><?= number_format($zai_total_1) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none'>
                        <div class='pt10b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none;border-right:none'>
                        <div class='pt10b'>　</div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none;border-right:none'>
                        <div class='pt10b'>　　期末材料棚卸高</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none;border-right:none'>
                        <div class='pt10b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-right:none;border-bottom:1pt solid windowtext'>
                        <div class='pt11b'><?= number_format($kimatsu_zairyo_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none'>
                        <div class='pt10b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none;border-right:none'>
                        <div class='pt10b'>　</div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none;border-right:none'>
                        <div class='pt10b'>　　合      計</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none;border-right:none'>
                        <div class='pt10b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none;border-right:none'>
                        <div class='pt11b'><?= number_format($zai_total_2) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none'>
                        <div class='pt10b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none;border-right:none'>
                        <div class='pt10b'>　</div>
                    </td>
                </tr>
                <!--
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center'>
                        <div class='pt10b'>　　棚卸資産評価損</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center'>
                        <div class='pt10b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right'>
                        <div class='pt11b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center'>
                        <div class='pt10b'>　</div>
                    </td>
                </tr>
                -->
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none;border-right:none'>
                        <div class='pt10b'>　　他勘定振替高</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none;border-right:none'>
                        <div class='pt10b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-right:none;border-bottom:1pt solid windowtext'>
                        <div class='pt11b'><?= number_format($takan_total_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none'>
                        <div class='pt10b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none;border-right:none'>
                        <div class='pt10b'>　</div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none;border-right:none'>
                        <div class='pt10b'>　　当期材料費</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none;border-right:none'>
                        <div class='pt10b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none;border-right:none'>
                        <div class='pt10b'>※</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none'>
                        <div class='pt11b'><?= number_format($touki_zairyo_total) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none;border-right:none'>
                        <div class='pt10b'>　</div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' colspan='4' style='border-bottom:none'>
                        <div class='pt10b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none;border-right:none'>
                        <div class='pt10b'>　</div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none;border-right:none'>
                        <div class='pt10b'>Ⅱ．労    務    費</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none;border-right:none'>
                        <div class='pt10b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none;border-right:none'>
                        <div class='pt10b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none'>
                        <div class='pt11b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none;border-right:none'>
                        <div class='pt10b'>　</div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none;border-right:none'>
                        <div class='pt10b'>　当期労務費</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none;border-right:none'>
                        <div class='pt10b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none;border-right:none'>
                        <div class='pt10b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none'>
                        <div class='pt11b'><?= number_format($roumu_total_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none;border-right:none'>
                        <div class='pt10b'>　</div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' colspan='4' style='border-bottom:none'>
                        <div class='pt10b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none;border-right:none'>
                        <div class='pt10b'>　</div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none;border-right:none'>
                        <div class='pt10b'>Ⅲ．製  造  経  費</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none;border-right:none'>
                        <div class='pt10b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none;border-right:none'>
                        <div class='pt10b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none'>
                        <div class='pt11b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none;border-right:none'>
                        <div class='pt10b'>　</div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none;border-right:none'>
                        <div class='pt10b'>　当期製造経費</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none;border-right:none'>
                        <div class='pt10b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none;border-right:none'>
                        <div class='pt10b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:1pt solid windowtext'>
                        <div class='pt11b'><?= number_format($seizo_keihi_total_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none;border-right:none'>
                        <div class='pt10b'>　</div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none;border-right:none'>
                        <div class='pt10b'>　当期総製造費用</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none;border-right:none'>
                        <div class='pt10b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none;border-right:none'>
                        <div class='pt10b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none'>
                        <div class='pt11b'><?= number_format($touki_total_seizo_hiyo) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none;border-right:none'>
                        <div class='pt10b'>　</div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none;border-right:none'>
                        <div class='pt10b'>　期首仕掛品棚卸高</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none;border-right:none'>
                        <div class='pt10b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none;border-right:none'>
                        <div class='pt10b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:1pt solid windowtext'>
                        <div class='pt11b'><?= number_format($kishu_shikakari_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none;border-right:none'>
                        <div class='pt10b'>　</div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none;border-right:none'>
                        <div class='pt10b'>　合      計</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none;border-right:none'>
                        <div class='pt10b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none;border-right:none'>
                        <div class='pt10b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none'>
                        <div class='pt11b'><?= number_format($toki_seizo_keihi_total) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none;border-right:none'>
                        <div class='pt10b'>　</div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none;border-right:none'>
                        <div class='pt10b'>　期末仕掛品棚卸高</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none;border-right:none'>
                        <div class='pt10b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none;border-right:none'>
                        <div class='pt10b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:1pt solid windowtext'>
                        <div class='pt11b'><?= number_format($kimatsu_shikakari_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-right:none'>
                        <div class='pt10b'>　</div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none;border-right:none'>
                        <div class='pt10b'>　当期製品製造原価</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none;border-right:none'>
                        <div class='pt10b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none;border-right:none'>
                        <div class='pt10b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:1pt solid windowtext'>
                        <div class='pt11b'><?= number_format($touki_seihin_seizo_genka) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right'>
                        <div class='pt11b'><?= number_format($urigen_as_sagaku) ?></div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' colspan='4' style='border-bottom:none'>
                        <div class='pt10b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none;border-right:none'>
                        <div class='pt10b'>　</div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' colspan='2' style='border-right:none'>
                        <div class='pt10b'>期末材料棚卸高には、棚卸資産評価損</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-right:none'>
                        <div class='pt11b'><?= number_format($hyokason_cr_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='left'>
                        <div class='pt10b'>円が含まれております。</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none;border-right:none'>
                        <div class='pt10b'>　</div>
                    </td>
                </tr>
            </TBODY>
        </table>
            </td></tr>
        </table> <!----------------- ダミーEnd ------------------>
        
        <table bgcolor='#ffffff' align='center' cellspacing='0' cellpadding='10'>
            <tr><td> <!----------- ダミー(デザイン用) ------------>
        <center>（経費明細書）</center>
        <table class='winbox_field' width='100%' align='center' bgcolor='black' cellspacing='0' cellpadding='5'>
            <THEAD>
                <!-- テーブル ヘッダーの表示 -->
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center'>
                        <div class='pt10b'>科目</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center'>
                        <div class='pt10b'>製造費用</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center'>
                        <div class='pt10b'>販管費及び一般管理費</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center'>
                        <div class='pt10b'>合計</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none;border-right:none;border-top:none'>
                        <div class='pt10b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none;border-right:none;border-top:none'>
                        <div class='pt10b'>　</div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none'>
                        <div class='pt10b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none'>
                        <div class='pt10b'>（労務費）</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none'>
                        <div class='pt10b'>（人件費）</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none'>
                        <div class='pt10b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none;border-right:none'>
                        <div class='pt10b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none;border-right:none'>
                        <div class='pt10b'>　</div>
                    </td>
                </tr>
            </THEAD>
            <TFOOT>
                <!-- 現在はフッターは何もない -->
            </TFOOT>
            <TBODY>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none'>
                        <div class='pt10b'>役員報酬</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none'>
                        <div class='pt11b'><?= number_format($yakuin_seizo_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none'>
                        <div class='pt11b'><?= number_format($yakuin_han_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none'>
                        <div class='pt11b'><?= number_format($yakuin_total_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none;border-right:none'>
                        <div class='pt10b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none;border-right:none'>
                        <div class='pt10b'>　</div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none'>
                        <div class='pt10b'>給料手当</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none'>
                        <div class='pt11b'><?= number_format($kyuryo_seizo_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none'>
                        <div class='pt11b'><?= number_format($kyuryo_han_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none'>
                        <div class='pt11b'><?= number_format($kyuryo_total_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none;border-right:none;border-top:none'>
                        <div class='pt10b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none;border-right:none;border-top:none'>
                        <div class='pt10b'>　</div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none'>
                        <div class='pt10b'>賞与手当</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none'>
                        <div class='pt11b'><?= number_format($syoyo_teate_seizo_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none'>
                        <div class='pt11b'><?= number_format($syoyo_teate_han_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none'>
                        <div class='pt11b'><?= number_format($syoyo_teate_total_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none;border-right:none;border-top:none'>
                        <div class='pt10b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none;border-right:none;border-top:none'>
                        <div class='pt10b'>　</div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none'>
                        <div class='pt10b'>顧問料</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none'>
                        <div class='pt11b'>－</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none'>
                        <div class='pt11b'><?= number_format($komon_han_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none'>
                        <div class='pt11b'><?= number_format($komon_total_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none;border-right:none;border-top:none'>
                        <div class='pt10b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none;border-right:none;border-top:none'>
                        <div class='pt10b'>　</div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none'>
                        <div class='pt10b'>厚生福利費</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none'>
                        <div class='pt11b'><?= number_format($fukuri_seizo_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none'>
                        <div class='pt11b'><?= number_format($fukuri_han_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none'>
                        <div class='pt11b'><?= number_format($fukuri_total_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none;border-right:none;border-top:none'>
                        <div class='pt10b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none;border-right:none;border-top:none'>
                        <div class='pt10b'>　</div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none'>
                        <div class='pt10b'>賞与引当金繰入額</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none'>
                        <div class='pt11b'><?= number_format($syoyo_hikiate_seizo_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none'>
                        <div class='pt11b'><?= number_format($syoyo_hikiate_han_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none'>
                        <div class='pt11b'><?= number_format($syoyo_hikiate_total_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none;border-right:none;border-top:none'>
                        <div class='pt10b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none;border-right:none;border-top:none'>
                        <div class='pt10b'>　</div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center'>
                        <div class='pt10b'>退職給付費用</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right'>
                        <div class='pt11b'><?= number_format($tai_kyufu_seizo_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right'>
                        <div class='pt11b'><?= number_format($tai_kyufu_han_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right'>
                        <div class='pt11b'><?= number_format($tai_kyufu_total_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-right:none;border-top:none'>
                        <div class='pt10b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-right:none;border-top:none'>
                        <div class='pt10b'>　</div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right'>
                        <div class='pt10b'>小計</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right'>
                        <div class='pt11b'><?= number_format($roumu_total_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right'>
                        <div class='pt11b'><?= number_format($jin_total_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right'>
                        <div class='pt11b'><?= number_format($roumu_jin_total_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right'>
                        <div class='pt11b'><?= number_format($roumu_as_sagaku) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right'>
                        <div class='pt11b'><?= number_format($jin_as_sagaku) ?></div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none'>
                        <div class='pt10b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none'>
                        <div class='pt10b'>（製造経費）</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none'>
                        <div class='pt10b'>（経費）</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none'>
                        <div class='pt10b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none;border-right:none;border-top:none'>
                        <div class='pt10b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none;border-right:none;border-top:none'>
                        <div class='pt10b'>　</div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none'>
                        <div class='pt10b'>旅費交通費</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none'>
                        <div class='pt11b'><?= number_format($ryohi_seizo_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none'>
                        <div class='pt11b'><?= number_format($ryohi_han_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none'>
                        <div class='pt11b'><?= number_format($ryohi_total_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none;border-right:none;border-top:none'>
                        <div class='pt10b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none;border-right:none;border-top:none'>
                        <div class='pt10b'>　</div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none'>
                        <div class='pt10b'>通信費</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none'>
                        <div class='pt11b'><?= number_format($tsushin_seizo_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none'>
                        <div class='pt11b'><?= number_format($tsushin_han_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none'>
                        <div class='pt11b'><?= number_format($tsushin_total_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none;border-right:none;border-top:none'>
                        <div class='pt10b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none;border-right:none;border-top:none'>
                        <div class='pt10b'>　</div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none'>
                        <div class='pt10b'>会議費</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none'>
                        <div class='pt11b'><?= number_format($kaigi_seizo_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none'>
                        <div class='pt11b'><?= number_format($kaigi_han_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none'>
                        <div class='pt11b'><?= number_format($kaigi_total_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none;border-right:none;border-top:none'>
                        <div class='pt10b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none;border-right:none;border-top:none'>
                        <div class='pt10b'>　</div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none'>
                        <div class='pt10b'>交際接待費</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none'>
                        <div class='pt11b'><?= number_format($kosai_seizo_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none'>
                        <div class='pt11b'><?= number_format($kosai_han_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none'>
                        <div class='pt11b'><?= number_format($kosai_total_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none;border-right:none;border-top:none'>
                        <div class='pt10b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none;border-right:none;border-top:none'>
                        <div class='pt10b'>　</div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none'>
                        <div class='pt10b'>広告宣伝費</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none'>
                        <div class='pt11b'>－</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none'>
                        <div class='pt11b'><?= number_format($senden_han_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none'>
                        <div class='pt11b'><?= number_format($senden_total_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none;border-right:none;border-top:none'>
                        <div class='pt10b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none;border-right:none;border-top:none'>
                        <div class='pt10b'>　</div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none'>
                        <div class='pt10b'>運賃荷造費</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none'>
                        <div class='pt11b'><?= number_format($nizukuri_seizo_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none'>
                        <div class='pt11b'><?= number_format($nizukuri_han_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none'>
                        <div class='pt11b'><?= number_format($nizukuri_total_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none;border-right:none;border-top:none'>
                        <div class='pt10b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none;border-right:none;border-top:none'>
                        <div class='pt10b'>　</div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none'>
                        <div class='pt10b'>図書教育費</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none'>
                        <div class='pt11b'><?= number_format($tosyo_seizo_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none'>
                        <div class='pt11b'><?= number_format($tosyo_han_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none'>
                        <div class='pt11b'><?= number_format($tosyo_total_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none;border-right:none;border-top:none'>
                        <div class='pt10b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none;border-right:none;border-top:none'>
                        <div class='pt10b'>　</div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none'>
                        <div class='pt10b'>業務委託費</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none'>
                        <div class='pt11b'><?= number_format($gyomu_seizo_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none'>
                        <div class='pt11b'><?= number_format($gyomu_han_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none'>
                        <div class='pt11b'><?= number_format($gyomu_total_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none;border-right:none;border-top:none'>
                        <div class='pt10b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none;border-right:none;border-top:none'>
                        <div class='pt10b'>　</div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none'>
                        <div class='pt10b'>諸税公課</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none'>
                        <div class='pt11b'><?= number_format($syozei_seizo_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none'>
                        <div class='pt11b'><?= number_format($syozei_han_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none'>
                        <div class='pt11b'><?= number_format($syozei_total_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none;border-right:none;border-top:none'>
                        <div class='pt10b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none;border-right:none;border-top:none'>
                        <div class='pt10b'>　</div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none'>
                        <div class='pt10b'>試験研究費</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none'>
                        <div class='pt11b'><?= number_format($shiken_seizo_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none'>
                        <div class='pt11b'>－</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none'>
                        <div class='pt11b'><?= number_format($shiken_total_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none;border-right:none;border-top:none'>
                        <div class='pt10b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none;border-right:none;border-top:none'>
                        <div class='pt10b'>　</div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none'>
                        <div class='pt10b'>修繕費</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none'>
                        <div class='pt11b'><?= number_format($syuzen_seizo_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none'>
                        <div class='pt11b'><?= number_format($syuzen_han_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none'>
                        <div class='pt11b'><?= number_format($syuzen_total_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none;border-right:none;border-top:none'>
                        <div class='pt10b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none;border-right:none;border-top:none'>
                        <div class='pt10b'>　</div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none'>
                        <div class='pt10b'>事務用消耗品費</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none'>
                        <div class='pt11b'><?= number_format($jimu_seizo_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none'>
                        <div class='pt11b'><?= number_format($jimu_han_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none'>
                        <div class='pt11b'><?= number_format($jimu_total_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none;border-right:none;border-top:none'>
                        <div class='pt10b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none;border-right:none;border-top:none'>
                        <div class='pt10b'>　</div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none'>
                        <div class='pt10b'>工場用消耗品費</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none'>
                        <div class='pt11b'><?= number_format($kojyo_seizo_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none'>
                        <div class='pt11b'>－</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none'>
                        <div class='pt11b'><?= number_format($kojyo_total_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none;border-right:none;border-top:none'>
                        <div class='pt10b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none;border-right:none;border-top:none'>
                        <div class='pt10b'>　</div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none'>
                        <div class='pt10b'>車輌費</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none'>
                        <div class='pt11b'><?= number_format($syaryo_seizo_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none'>
                        <div class='pt11b'><?= number_format($syaryo_han_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none'>
                        <div class='pt11b'><?= number_format($syaryo_total_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none;border-right:none;border-top:none'>
                        <div class='pt10b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none;border-right:none;border-top:none'>
                        <div class='pt10b'>　</div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none'>
                        <div class='pt10b'>保険料</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none'>
                        <div class='pt11b'><?= number_format($hoken_seizo_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none'>
                        <div class='pt11b'><?= number_format($hoken_han_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none'>
                        <div class='pt11b'><?= number_format($hoken_total_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none;border-right:none;border-top:none'>
                        <div class='pt10b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none;border-right:none;border-top:none'>
                        <div class='pt10b'>　</div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none'>
                        <div class='pt10b'>水道光熱費</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none'>
                        <div class='pt11b'><?= number_format($suido_seizo_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none'>
                        <div class='pt11b'><?= number_format($suido_han_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none'>
                        <div class='pt11b'><?= number_format($suido_total_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none;border-right:none;border-top:none'>
                        <div class='pt10b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none;border-right:none;border-top:none'>
                        <div class='pt10b'>　</div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none'>
                        <div class='pt10b'>地代家賃</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none'>
                        <div class='pt11b'><?= number_format($yachin_seizo_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none'>
                        <div class='pt11b'><?= number_format($yachin_han_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none'>
                        <div class='pt11b'><?= number_format($yachin_total_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none;border-right:none;border-top:none'>
                        <div class='pt10b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none;border-right:none;border-top:none'>
                        <div class='pt10b'>　</div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none'>
                        <div class='pt10b'>寄付金</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none'>
                        <div class='pt11b'>－</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none'>
                        <div class='pt11b'><?= number_format($kifu_han_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none'>
                        <div class='pt11b'><?= number_format($kifu_total_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none;border-right:none;border-top:none'>
                        <div class='pt10b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none;border-right:none;border-top:none'>
                        <div class='pt10b'>　</div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none'>
                        <div class='pt10b'>賃借料</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none'>
                        <div class='pt11b'><?= number_format($chin_seizo_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none'>
                        <div class='pt11b'><?= number_format($chin_han_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none'>
                        <div class='pt11b'><?= number_format($chin_total_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none;border-right:none;border-top:none'>
                        <div class='pt10b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none;border-right:none;border-top:none'>
                        <div class='pt10b'>　</div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none'>
                        <div class='pt10b'>雑費</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none'>
                        <div class='pt11b'><?= number_format($zappi_seizo_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none'>
                        <div class='pt11b'><?= number_format($zappi_han_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none'>
                        <div class='pt11b'><?= number_format($zappi_total_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none;border-right:none;border-top:none'>
                        <div class='pt10b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none;border-right:none;border-top:none'>
                        <div class='pt10b'>　</div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none'>
                        <div class='pt10b'>クレーム対応費</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none'>
                        <div class='pt11b'><?= number_format($clame_seizo_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none'>
                        <div class='pt11b'><?= number_format($clame_han_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none'>
                        <div class='pt11b'><?= number_format($clame_total_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none;border-right:none;border-top:none'>
                        <div class='pt10b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none;border-right:none;border-top:none'>
                        <div class='pt10b'>　</div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center'>
                        <div class='pt10b'>減価償却費</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right'>
                        <div class='pt11b'><?= number_format($genkasyo_seizo_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right'>
                        <div class='pt11b'><?= number_format($genkasyo_han_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right'>
                        <div class='pt11b'><?= number_format($genkasyo_total_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-right:none;border-top:none'>
                        <div class='pt10b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-right:none;border-top:none'>
                        <div class='pt10b'>　</div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right'>
                        <div class='pt10b'>小計</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right'>
                        <div class='pt11b'><?= number_format($seizo_keihi_total_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right'>
                        <div class='pt11b'><?= number_format($han_keihi_total_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right'>
                        <div class='pt11b'><?= number_format($keihi_total_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right'>
                        <div class='pt11b'><?= number_format($seikei_as_sagaku) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right'>
                        <div class='pt11b'><?= number_format($hankei_as_sagaku) ?></div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right'>
                        <div class='pt10b'>合計</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right'>
                        <div class='pt11b'><?= number_format($seizo_hiyo_total_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right'>
                        <div class='pt11b'><?= number_format($han_all_total_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right'>
                        <div class='pt11b'><?= number_format($all_keihi_total_kin) ?></div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none;border-right:none;border-top:none'>
                        <div class='pt10b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none;border-right:none;border-top:none'>
                        <div class='pt10b'>　</div>
                    </td>
                </tr>
            </TBODY>
        </table>
            </td></tr>
        </table> <!----------------- ダミーEnd ------------------>
        
        <table bgcolor='#ffffff' align='center' cellspacing='0' cellpadding='10'>
            <tr><td> <!----------- ダミー(デザイン用) ------------>
        <center>（株主資本等変動計算書）</center>
        <table class='winbox_field' width='100%' align='center' bgcolor='black' cellspacing='0' cellpadding='5'>
            <THEAD>
            </THEAD>
            <TFOOT>
                <!-- 現在はフッターは何もない -->
            </TFOOT>
            <TBODY>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='left' colspan='5' style='border-bottom:none'>
                        <div class='pt10b'>（株主資本）</div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='left' style='border-bottom:none;border-right:none'>
                        <div class='pt10b'>　【資本金】</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='left' style='border-bottom:none;border-right:none'>
                        <div class='pt10b'>当期首残高</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='left' style='border-bottom:none;border-right:none'>
                        <div class='pt10b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none'>
                        <div class='pt11b'><?= number_format($shihon_kishu) ?></div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='left' style='border-bottom:none;border-right:none'>
                        <div class='pt10b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='left' style='border-bottom:none;border-right:none'>
                        <div class='pt10b'>当期変動額</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='left' style='border-bottom:none;border-right:none'>
                        <div class='pt10b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:1pt solid windowtext'>
                        <div class='pt11b'><?= number_format($shihon_hendo) ?></div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='left' style='border-bottom:none;border-right:none'>
                        <div class='pt10b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='left' style='border-bottom:none;border-right:none'>
                        <div class='pt10b'>当期末残高</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='left' style='border-bottom:none;border-right:none'>
                        <div class='pt10b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:1.5pt solid windowtext'>
                        <div class='pt11b'><?= number_format($shihon_kin) ?></div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='left' colspan='5' style='border-bottom:none'>
                        <div class='pt10b'>　【資本剰余金】</div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='left' style='border-bottom:none;border-right:none'>
                        <div class='pt10b'>　資本準備金</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='left' style='border-bottom:none;border-right:none'>
                        <div class='pt10b'>当期首残高</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='left' style='border-bottom:none;border-right:none'>
                        <div class='pt10b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none'>
                        <div class='pt11b'><?= number_format($shihon_jyunbi_kishu) ?></div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='left' style='border-bottom:none;border-right:none'>
                        <div class='pt10b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='left' style='border-bottom:none;border-right:none'>
                        <div class='pt10b'>当期変動額</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='left' style='border-bottom:none;border-right:none'>
                        <div class='pt10b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:1pt solid windowtext'>
                        <div class='pt11b'><?= number_format($shihon_jyunbi_hendo) ?></div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='left' style='border-bottom:none;border-right:none'>
                        <div class='pt10b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='left' style='border-bottom:none;border-right:none'>
                        <div class='pt10b'>当期末残高</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='left' style='border-bottom:none;border-right:none'>
                        <div class='pt10b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:1.5pt solid windowtext'>
                        <div class='pt11b'><?= number_format($shihon_jyunbi_kin) ?></div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='left' style='border-bottom:none;border-right:none'>
                        <div class='pt10b'>　その他資本剰余金</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='left' style='border-bottom:none;border-right:none'>
                        <div class='pt10b'>当期首残高</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='left' style='border-bottom:none;border-right:none'>
                        <div class='pt10b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none'>
                        <div class='pt11b'><?= number_format($sonota_shihon_jyoyo_kishu) ?></div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='left' style='border-bottom:none;border-right:none'>
                        <div class='pt10b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='left' style='border-bottom:none;border-right:none'>
                        <div class='pt10b'>当期変動額</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='left' style='border-bottom:none;border-right:none'>
                        <div class='pt10b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:1pt solid windowtext'>
                        <div class='pt11b'><?= number_format($sonota_shihon_jyoyo_hendo) ?></div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='left' style='border-bottom:none;border-right:none'>
                        <div class='pt10b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='left' style='border-bottom:none;border-right:none'>
                        <div class='pt10b'>当期末残高</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='left' style='border-bottom:none;border-right:none'>
                        <div class='pt10b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:1.5pt solid windowtext'>
                        <div class='pt11b'><?= number_format($sonota_shihon_jyoyo_kin) ?></div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='left' style='border-bottom:none;border-right:none'>
                        <div class='pt10b'>【資本剰余金】合計</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='left' style='border-bottom:none;border-right:none'>
                        <div class='pt10b'>当期首残高</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='left' style='border-bottom:none;border-right:none'>
                        <div class='pt10b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none'>
                        <div class='pt11b'><?= number_format($shihon_jyoyo_total_kishu) ?></div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='left' style='border-bottom:none;border-right:none'>
                        <div class='pt10b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='left' style='border-bottom:none;border-right:none'>
                        <div class='pt10b'>当期変動額</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='left' style='border-bottom:none;border-right:none'>
                        <div class='pt10b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:1pt solid windowtext'>
                        <div class='pt11b'><?= number_format($shihon_jyoyo_total_hendo) ?></div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='left' style='border-bottom:none;border-right:none'>
                        <div class='pt10b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='left' style='border-bottom:none;border-right:none'>
                        <div class='pt10b'>当期末残高</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='left' style='border-bottom:none;border-right:none'>
                        <div class='pt10b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:1.5pt solid windowtext'>
                        <div class='pt11b'><?= number_format($shihon_jyoyo_total_kin) ?></div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='left' colspan='5' style='border-bottom:none'>
                        <div class='pt10b'>　【利益剰余金】</div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='left' style='border-bottom:none;border-right:none'>
                        <div class='pt10b'>　利益準備金</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='left' style='border-bottom:none;border-right:none'>
                        <div class='pt10b'>当期首残高及び当期末残高</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='left' style='border-bottom:none;border-right:none'>
                        <div class='pt10b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none'>
                        <div class='pt11b'><?= number_format($rieki_jyunbi_kin) ?></div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='left' style='border-bottom:none;border-right:none'>
                        <div class='pt10b'>　その他利益剰余金</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='left' style='border-bottom:none;border-right:none'>
                        <div class='pt10b'>当期首残高</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='left' style='border-bottom:none;border-right:none'>
                        <div class='pt10b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none'>
                        <div class='pt11b'><?= number_format($sonota_rieki_jyoyo_kishu) ?></div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='left' style='border-bottom:none;border-right:none'>
                        <div class='pt10b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='left' style='border-bottom:none;border-right:none'>
                        <div class='pt10b'>当期変動額</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='left' style='border-bottom:none;border-right:none'>
                        <div class='pt10b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:1pt solid windowtext'>
                        <div class='pt11b'><?= number_format($sonota_rieki_jyoyo_hendo) ?></div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='left' style='border-bottom:none;border-right:none'>
                        <div class='pt10b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='left' style='border-bottom:none;border-right:none'>
                        <div class='pt10b'>当期末残高</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='left' style='border-bottom:none;border-right:none'>
                        <div class='pt10b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:1.5pt solid windowtext'>
                        <div class='pt11b'><?= number_format($sonota_rieki_jyoyo_kin) ?></div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='left' style='border-bottom:none;border-right:none'>
                        <div class='pt10b'>　繰越利益剰余金</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='left' style='border-bottom:none;border-right:none'>
                        <div class='pt10b'>当期首残高</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='left' style='border-bottom:none;border-right:none'>
                        <div class='pt10b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none'>
                        <div class='pt11b'><?= number_format($kuri_rieki_jyoyo_kishu) ?></div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='left' style='border-bottom:none;border-right:none'>
                        <div class='pt10b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='left' style='border-bottom:none;border-right:none'>
                        <div class='pt10b'>当期変動額</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='center' style='border-bottom:none;border-right:none'>
                        <div class='pt10b'>（当期純利益金額）</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:1pt solid windowtext'>
                        <div class='pt11b'><?= number_format($kuri_rieki_jyoyo_hendo) ?></div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='left' style='border-bottom:none;border-right:none'>
                        <div class='pt10b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='left' style='border-bottom:none;border-right:none'>
                        <div class='pt10b'>当期末残高</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='left' style='border-bottom:none;border-right:none'>
                        <div class='pt10b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:1.5pt solid windowtext'>
                        <div class='pt11b'><?= number_format($kuri_rieki_jyoyo_kin) ?></div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='left' style='border-bottom:none;border-right:none'>
                        <div class='pt10b'>【利益剰余金】合計</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='left' style='border-bottom:none;border-right:none'>
                        <div class='pt10b'>当期首残高</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='left' style='border-bottom:none;border-right:none'>
                        <div class='pt10b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none'>
                        <div class='pt11b'><?= number_format($rieki_jyoyo_total_kishu) ?></div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='left' style='border-bottom:none;border-right:none'>
                        <div class='pt10b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='left' style='border-bottom:none;border-right:none'>
                        <div class='pt10b'>当期変動額</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='left' style='border-bottom:none;border-right:none'>
                        <div class='pt10b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:1pt solid windowtext'>
                        <div class='pt11b'><?= number_format($rieki_jyoyo_total_hendo) ?></div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='left' style='border-bottom:none;border-right:none'>
                        <div class='pt10b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='left' style='border-bottom:none;border-right:none'>
                        <div class='pt10b'>当期末残高</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='left' style='border-bottom:none;border-right:none'>
                        <div class='pt10b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:1.5pt solid windowtext'>
                        <div class='pt11b'><?= number_format($rieki_jyoyo_total_kin) ?></div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='left' style='border-bottom:none;border-right:none'>
                        <div class='pt10b'>《純資産合計》</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='left' style='border-bottom:none;border-right:none'>
                        <div class='pt10b'>当期首残高</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='left' style='border-bottom:none;border-right:none'>
                        <div class='pt10b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:none'>
                        <div class='pt11b'><?= number_format($jyun_shisan_total_kishu) ?></div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='left' style='border-bottom:none;border-right:none'>
                        <div class='pt10b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='left' style='border-bottom:none;border-right:none'>
                        <div class='pt10b'>当期変動額</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='left' style='border-bottom:none;border-right:none'>
                        <div class='pt10b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:1pt solid windowtext'>
                        <div class='pt11b'><?= number_format($jyun_shisan_total_hendo) ?></div>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='left' style='border-right:none'>
                        <div class='pt10b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='left' style='border-right:none'>
                        <div class='pt10b'>当期末残高</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='left' style='border-right:none'>
                        <div class='pt10b'>　</div>
                    </td>
                    <td class='winbox' nowrap bgcolor='#ffffff' align='right' style='border-bottom:1.5pt solid windowtext'>
                        <div class='pt11b'><?= number_format($jyun_shisan_total_kin) ?></div>
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
