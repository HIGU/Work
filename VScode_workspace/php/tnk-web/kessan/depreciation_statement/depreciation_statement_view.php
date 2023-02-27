<?php
//////////////////////////////////////////////////////////////////////////////
// 四半期 減価償却費明細表 照会                                             //
// Copyright (C) 2020-2020 Norihisa.Ohya norihisa_ooya@nitto-kohki.co.jp    //
//                                                                          //
// Changed history                                                          //
// 2020/01/27 Created  depreciation_statement_view.php                      //
// 2020/07/01 データをASから取得に変更                                      //
//////////////////////////////////////////////////////////////////////////////
// ini_set('error_reporting', E_ALL || E_STRICT);
// ini_set('error_reporting',E_ALL);           // E_ALL='2047' debug 用
// ini_set('display_errors','1');              // Error 表示 ON debug 用 リリース後コメント
session_start();                            // ini_set()の次に指定すること Script 最上行

require_once ('../../function.php');        // define.php と pgsql.php を require_once している
require_once ('../../tnk_func.php');        // TNK に依存する部分の関数を require_once している
require_once ('../../MenuHeader.php');      // TNK 全共通 menu class
access_log();                               // Script Name は自動取得

///// TNK 共用メニュークラスのインスタンスを作成
$menu = new MenuHeader(0);                  // 認証チェック0=一般以上 戻り先=TOP_MENU タイトル未設定
    // 実際の認証はprofit_loss_submit.phpで行っているaccount_group_check()を使用

////////////// サイト設定
// $menu->set_site(10, 7);                     // site_index=10(損益メニュー) site_id=7(月次損益)
//////////// 表題の設定
$menu->set_caption('栃木日東工器');
//////////// 呼出先のaction名とアドレス設定
// $menu->set_action('抽象化名',   PL . 'address.php');

///// 呼出もとの URL を取得
$url_referer     = $_SESSION['pl_referer'];
$current_script  = $_SERVER['PHP_SELF'];        // 現在実行中のスクリプト名を保存

/********** Logic Start **********/
///////////// サイトメニュー On / Off 
if ($_SESSION['site_view'] == 'on') {
    $site_view = 'MenuOFF';
} else {
    $site_view = 'MenuON';
}

//////////////// サイトメニューのＵＲＬ設定 & JavaScript生成
$menu_site_url = 'http:' . WEB_HOST . 'menu_site.php';
$menu_site_script =
"<script language='JavaScript'>
<!--
    parent.menu_site.location = '$menu_site_url';
// -->
</script>";
$menu_site_script = "";         // 月次メニューのため使わない

//////////// タイトルの日付・時間設定
$today = date("Y/m/d H:i:s");

//////////// JavaScript Stylesheet File を必ず読み込ませる
$uniq = uniqid("target");

///// 期・月の取得
$ki = Ym_to_tnk($_SESSION['2ki_ym']);
$tuki = substr($_SESSION['2ki_ym'],4,2);
$tuki = $tuki + 1 -1;   // 数値データに変換(09を9にしたいため)キャストでもいいのだが

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

//////////// タイトル名(ソースのタイトル名とフォームのタイトル名)
$menu->set_title("第 {$ki} 期　第{$hanki}四半期　減価償却資産および減価償却費の明細書");

///// 対象当月
$yyyymm = $_SESSION['2ki_ym'];
$ki     = Ym_to_tnk($_SESSION['2ki_ym']);
///// TNK期 → NK期へ変換
$nk_ki   = $ki + 44;
///// 対象前月
if (substr($yyyymm,4,2)!=01) {
    $p1_ym = $yyyymm - 1;
} else {
    $p1_ym = $yyyymm - 100;
    $p1_ym = $p1_ym + 11;
}
///// 対象前々月 これはとりあえず使わない
if (substr($p1_ym,4,2)!=01) {
    $p2_ym = $p1_ym - 1;
} else {
    $p2_ym = $p1_ym - 100;
    $p2_ym = $p2_ym + 11;
}
///// 前期末 年月の算出
$yyyy = substr($yyyymm, 0,4);
$mm   = substr($yyyymm, 4,2);
if (($mm >= 1) && ($mm <= 3)) {
    $yyyy = ($yyyy - 1);
}
$pre_end_ym = $yyyy . "03";     // 期初年月

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

///// 表示単位を設定取得
if (isset($_POST['state_tani'])) {
    $_SESSION['state_tani'] = $_POST['state_tani'];
    $tani = $_SESSION['state_tani'];
} elseif (isset($_SESSION['state_tani'])) {
    $tani = $_SESSION['state_tani'];
} else {
    $tani = 1;        // 初期値 表示単位 百万円
    $_SESSION['state_tani'] = $tani;
}
///// 表示 小数部桁数 設定取得
if (isset($_POST['state_keta'])) {
    $_SESSION['state_keta'] = $_POST['state_keta'];
    $keta = $_SESSION['state_keta'];
} elseif (isset($_SESSION['state_keta'])) {
    $keta = $_SESSION['state_keta'];
} else {
    $keta = 0;          // 初期値 小数点以下桁数
    $_SESSION['state_keta'] = $keta;
}
// $keta = 1;              // 比較棚卸表では小数点以下は1に固定しようと思ったがしない。


// データ取得

// 建物
$res_k   = array();
$field_k = array();
$rows_k  = array();
$sum1 = '2101';
$sum2 = '00';
$query_k = sprintf("select SUM(rep_cri), SUM(rep_de), SUM(rep_cr) from financial_report_cal where rep_ymd=%d and rep_summary1='%s' and rep_summary2='%s'", $nk_ki, $sum1, $sum2);
if ($rows_k=getResultWithField2($query_k, $field_k, $res_k) <= 0) {
    $tate_shu_kishu_kin = 0;
} else {
    $tate_shu_kishu_kin = $res_k[0][0];
}
$res_k   = array();
$field_k = array();
$rows_k  = array();
$sum1 = '2101';
$sum2 = '00';
$query_k = sprintf("select SUM(rep_de), SUM(rep_cr) from financial_report_month where rep_ymd>=%d and rep_ymd<=%d and rep_summary1='%s' and rep_summary2='%s'", $str_ym, $end_ym, $sum1, $sum2);
if ($rows_k=getResultWithField2($query_k, $field_k, $res_k) <= 0) {
    $tate_shu_zou_kin   = 0;
    $tate_shu_gen_kin   = 0;
} else {
    $tate_shu_zou_kin   = $res_k[0][0];
    $tate_shu_gen_kin   = $res_k[0][1];
}

$res_k   = array();
$field_k = array();
$rows_k  = array();
$sum1 = '3401';
$sum2 = '10';
$query_k = sprintf("select SUM(rep_cri), SUM(rep_de), SUM(rep_cr) from financial_report_cal where rep_ymd=%d and rep_summary1='%s' and rep_summary2='%s'", $nk_ki, $sum1, $sum2);
if ($rows_k=getResultWithField2($query_k, $field_k, $res_k) <= 0) {
    $tate_kishu_zan_kin = 0;
} else {
    $tate_kishu_zan_kin = $res_k[0][0];
}

$res_k   = array();
$field_k = array();
$rows_k  = array();
$sum1 = '3401';
$sum2 = '10';
$query_k = sprintf("select SUM(rep_de), SUM(rep_cr) from financial_report_month where rep_ymd>=%d and rep_ymd<=%d and rep_summary1='%s' and rep_summary2='%s'", $str_ym, $end_ym, $sum1, $sum2);
if ($rows_k=getResultWithField2($query_k, $field_k, $res_k) <= 0) {
    $tate_kishu_chou_kin = $tate_shu_kishu_kin + $tate_kishu_zan_kin;
    $tate_rui_gen_kin   = 0;
    $tate_rui_syo_kin   = 0;
} else {
    $tate_kishu_chou_kin = $tate_shu_kishu_kin + $tate_kishu_zan_kin;
    $tate_rui_gen_kin    = $res_k[0][0];
    $tate_rui_syo_kin    = $res_k[0][1];
}
$month = array();
$month[0][0] = '建物取得価額期首残高';
$month[0][1] = $tate_shu_kishu_kin;
$month[1][0] = '建物取得価額期中増加';
$month[1][1] = $tate_shu_zou_kin;
$month[2][0] = '建物取得価額期中減少';
$month[2][1] = $tate_shu_gen_kin;
$month[3][0] = '建物期首帳簿価額';
$month[3][1] = $tate_kishu_chou_kin;
$month[4][0] = '建物累計額期中減少';
$month[4][1] = $tate_rui_gen_kin;
$month[5][0] = '建物累計額当期償却額';
$month[5][1] = $tate_rui_syo_kin;

// 建物付属設備
$res_k   = array();
$field_k = array();
$rows_k  = array();
$sum1 = '2102';
$sum2 = '00';
$query_k = sprintf("select SUM(rep_cri), SUM(rep_de), SUM(rep_cr) from financial_report_cal where rep_ymd=%d and rep_summary1='%s' and rep_summary2='%s'", $nk_ki, $sum1, $sum2);
if ($rows_k=getResultWithField2($query_k, $field_k, $res_k) <= 0) {
    $tatef_shu_kishu_kin = 0;
} else {
    $tatef_shu_kishu_kin = $res_k[0][0];
}
$res_k   = array();
$field_k = array();
$rows_k  = array();
$sum1 = '2102';
$sum2 = '00';
$query_k = sprintf("select SUM(rep_de), SUM(rep_cr) from financial_report_month where rep_ymd>=%d and rep_ymd<=%d and rep_summary1='%s' and rep_summary2='%s'", $str_ym, $end_ym, $sum1, $sum2);
if ($rows_k=getResultWithField2($query_k, $field_k, $res_k) <= 0) {
    $tatef_shu_zou_kin   = 0;
    $tatef_shu_gen_kin   = 0;
} else {
    $tatef_shu_zou_kin   = $res_k[0][0];
    $tatef_shu_gen_kin   = $res_k[0][1];
}

$res_k   = array();
$field_k = array();
$rows_k  = array();
$sum1 = '3401';
$sum2 = '20';
$query_k = sprintf("select SUM(rep_cri), SUM(rep_de), SUM(rep_cr) from financial_report_cal where rep_ymd=%d and rep_summary1='%s' and rep_summary2='%s'", $nk_ki, $sum1, $sum2);
if ($rows_k=getResultWithField2($query_k, $field_k, $res_k) <= 0) {
    $tatef_kishu_zan_kin = 0;
} else {
    $tatef_kishu_zan_kin = $res_k[0][0];
}

$res_k   = array();
$field_k = array();
$rows_k  = array();
$sum1 = '3401';
$sum2 = '20';
$query_k = sprintf("select SUM(rep_de), SUM(rep_cr) from financial_report_month where rep_ymd>=%d and rep_ymd<=%d and rep_summary1='%s' and rep_summary2='%s'", $str_ym, $end_ym, $sum1, $sum2);
if ($rows_k=getResultWithField2($query_k, $field_k, $res_k) <= 0) {
    $tatef_kishu_chou_kin = $tatef_shu_kishu_kin + $tatef_kishu_zan_kin;
    $tatef_rui_gen_kin   = 0;
    $tatef_rui_syo_kin   = 0;
} else {
    $tatef_kishu_chou_kin = $tatef_shu_kishu_kin + $tatef_kishu_zan_kin;
    $tatef_rui_gen_kin    = $res_k[0][0];
    $tatef_rui_syo_kin    = $res_k[0][1];
}
$month[6][0]  = '建物附属設備取得価額期首残高';
$month[6][1]  = $tatef_shu_kishu_kin;
$month[7][0]  = '建物附属設備取得価額期中増加';
$month[7][1]  = $tatef_shu_zou_kin;
$month[8][0]  = '建物附属設備取得価額期中減少';
$month[8][1]  = $tatef_shu_gen_kin;
$month[9][0]  = '建物附属設備期首帳簿価額';
$month[9][1]  = $tatef_kishu_chou_kin;
$month[10][0] = '建物附属設備累計額期中減少';
$month[10][1] = $tatef_rui_gen_kin;
$month[11][0] = '建物附属設備累計額当期償却額';
$month[11][1] = $tatef_rui_syo_kin;

// 構築物
$res_k   = array();
$field_k = array();
$rows_k  = array();
$sum1 = '2103';
$sum2 = '00';
$query_k = sprintf("select SUM(rep_cri), SUM(rep_de), SUM(rep_cr) from financial_report_cal where rep_ymd=%d and rep_summary1='%s' and rep_summary2='%s'", $nk_ki, $sum1, $sum2);
if ($rows_k=getResultWithField2($query_k, $field_k, $res_k) <= 0) {
    $kou_shu_kishu_kin = 0;
} else {
    $kou_shu_kishu_kin = $res_k[0][0];
}
$res_k   = array();
$field_k = array();
$rows_k  = array();
$sum1 = '2103';
$sum2 = '00';
$query_k = sprintf("select SUM(rep_de), SUM(rep_cr) from financial_report_month where rep_ymd>=%d and rep_ymd<=%d and rep_summary1='%s' and rep_summary2='%s'", $str_ym, $end_ym, $sum1, $sum2);
if ($rows_k=getResultWithField2($query_k, $field_k, $res_k) <= 0) {
    $kou_shu_zou_kin   = 0;
    $kou_shu_gen_kin   = 0;
} else {
    $kou_shu_zou_kin   = $res_k[0][0];
    $kou_shu_gen_kin   = $res_k[0][1];
}

$res_k   = array();
$field_k = array();
$rows_k  = array();
$sum1 = '3401';
$sum2 = '30';
$query_k = sprintf("select SUM(rep_cri), SUM(rep_de), SUM(rep_cr) from financial_report_cal where rep_ymd=%d and rep_summary1='%s' and rep_summary2='%s'", $nk_ki, $sum1, $sum2);
if ($rows_k=getResultWithField2($query_k, $field_k, $res_k) <= 0) {
    $kou_kishu_zan_kin = 0;
} else {
    $kou_kishu_zan_kin = $res_k[0][0];
}

$res_k   = array();
$field_k = array();
$rows_k  = array();
$sum1 = '3401';
$sum2 = '30';
$query_k = sprintf("select SUM(rep_de), SUM(rep_cr) from financial_report_month where rep_ymd>=%d and rep_ymd<=%d and rep_summary1='%s' and rep_summary2='%s'", $str_ym, $end_ym, $sum1, $sum2);
if ($rows_k=getResultWithField2($query_k, $field_k, $res_k) <= 0) {
    $kou_kishu_chou_kin = $kou_shu_kishu_kin + $kou_kishu_zan_kin;
    $kou_rui_gen_kin   = 0;
    $kou_rui_syo_kin   = 0;
} else {
    $kou_kishu_chou_kin = $kou_shu_kishu_kin + $kou_kishu_zan_kin;
    $kou_rui_gen_kin    = $res_k[0][0];
    $kou_rui_syo_kin    = $res_k[0][1];
}
$month[12][0] = '構築物取得価額期首残高';
$month[12][1] = $kou_shu_kishu_kin;
$month[13][0] = '構築物取得価額期中増加';
$month[13][1] = $kou_shu_zou_kin;
$month[14][0] = '構築物取得価額期中減少';
$month[14][1] = $kou_shu_gen_kin;
$month[15][0] = '構築物期首帳簿価額';
$month[15][1] = $kou_kishu_chou_kin;
$month[16][0] = '構築物累計額期中減少';
$month[16][1] = $kou_rui_gen_kin;
$month[17][0] = '構築物累計額当期償却額';
$month[17][1] = $kou_rui_syo_kin;

// 機械装置
$res_k   = array();
$field_k = array();
$rows_k  = array();
$sum1 = '2104';
$sum2 = '00';
$query_k = sprintf("select SUM(rep_cri), SUM(rep_de), SUM(rep_cr) from financial_report_cal where rep_ymd=%d and rep_summary1='%s' and rep_summary2='%s'", $nk_ki, $sum1, $sum2);
if ($rows_k=getResultWithField2($query_k, $field_k, $res_k) <= 0) {
    $kikai_shu_kishu_kin = 0;
} else {
    $kikai_shu_kishu_kin = $res_k[0][0];
}
$res_k   = array();
$field_k = array();
$rows_k  = array();
$sum1 = '2104';
$sum2 = '00';
$query_k = sprintf("select SUM(rep_de), SUM(rep_cr) from financial_report_month where rep_ymd>=%d and rep_ymd<=%d and rep_summary1='%s' and rep_summary2='%s'", $str_ym, $end_ym, $sum1, $sum2);
if ($rows_k=getResultWithField2($query_k, $field_k, $res_k) <= 0) {
    $kikai_shu_zou_kin   = 0;
    $kikai_shu_gen_kin   = 0;
} else {
    $kikai_shu_zou_kin   = $res_k[0][0];
    $kikai_shu_gen_kin   = $res_k[0][1];
}

$res_k   = array();
$field_k = array();
$rows_k  = array();
$sum1 = '3401';
$sum2 = '40';
$query_k = sprintf("select SUM(rep_cri), SUM(rep_de), SUM(rep_cr) from financial_report_cal where rep_ymd=%d and rep_summary1='%s' and rep_summary2='%s'", $nk_ki, $sum1, $sum2);
if ($rows_k=getResultWithField2($query_k, $field_k, $res_k) <= 0) {
    $kikai_kishu_zan_kin = 0;
} else {
    $kikai_kishu_zan_kin = $res_k[0][0];
}

$res_k   = array();
$field_k = array();
$rows_k  = array();
$sum1 = '3401';
$sum2 = '40';
$query_k = sprintf("select SUM(rep_de), SUM(rep_cr) from financial_report_month where rep_ymd>=%d and rep_ymd<=%d and rep_summary1='%s' and rep_summary2='%s'", $str_ym, $end_ym, $sum1, $sum2);
if ($rows_k=getResultWithField2($query_k, $field_k, $res_k) <= 0) {
    $kikai_kishu_chou_kin = $kikai_shu_kishu_kin + $kikai_kishu_zan_kin;
    $kikai_rui_gen_kin   = 0;
    $kikai_rui_syo_kin   = 0;
} else {
    $kikai_kishu_chou_kin = $kikai_shu_kishu_kin + $kikai_kishu_zan_kin;
    $kikai_rui_gen_kin    = $res_k[0][0];
    $kikai_rui_syo_kin    = $res_k[0][1];
}
$month[18][0] = '機械装置取得価額期首残高';
$month[18][1] = $kikai_shu_kishu_kin;
$month[19][0] = '機械装置取得価額期中増加';
$month[19][1] = $kikai_shu_zou_kin;
$month[20][0] = '機械装置取得価額期中減少';
$month[20][1] = $kikai_shu_gen_kin;
$month[21][0] = '機械装置期首帳簿価額';
$month[21][1] = $kikai_kishu_chou_kin;
$month[22][0] = '機械装置累計額期中減少';
$month[22][1] = $kikai_rui_gen_kin;
$month[23][0] = '機械装置累計額当期償却額';
$month[23][1] = $kikai_rui_syo_kin;

// 車輛運搬具
$res_k   = array();
$field_k = array();
$rows_k  = array();
$sum1 = '2105';
$sum2 = '00';
$query_k = sprintf("select SUM(rep_cri), SUM(rep_de), SUM(rep_cr) from financial_report_cal where rep_ymd=%d and rep_summary1='%s' and rep_summary2='%s'", $nk_ki, $sum1, $sum2);
if ($rows_k=getResultWithField2($query_k, $field_k, $res_k) <= 0) {
    $syaryo_shu_kishu_kin = 0;
} else {
    $syaryo_shu_kishu_kin = $res_k[0][0];
}
$res_k   = array();
$field_k = array();
$rows_k  = array();
$sum1 = '2105';
$sum2 = '00';
$query_k = sprintf("select SUM(rep_de), SUM(rep_cr) from financial_report_month where rep_ymd>=%d and rep_ymd<=%d and rep_summary1='%s' and rep_summary2='%s'", $str_ym, $end_ym, $sum1, $sum2);
if ($rows_k=getResultWithField2($query_k, $field_k, $res_k) <= 0) {
    $syaryo_shu_zou_kin   = 0;
    $syaryo_shu_gen_kin   = 0;
} else {
    $syaryo_shu_zou_kin   = $res_k[0][0];
    $syaryo_shu_gen_kin   = $res_k[0][1];
}

$res_k   = array();
$field_k = array();
$rows_k  = array();
$sum1 = '3401';
$sum2 = '50';
$query_k = sprintf("select SUM(rep_cri), SUM(rep_de), SUM(rep_cr) from financial_report_cal where rep_ymd=%d and rep_summary1='%s' and rep_summary2='%s'", $nk_ki, $sum1, $sum2);
if ($rows_k=getResultWithField2($query_k, $field_k, $res_k) <= 0) {
    $syaryo_kishu_zan_kin = 0;
} else {
    $syaryo_kishu_zan_kin = $res_k[0][0];
}

$res_k   = array();
$field_k = array();
$rows_k  = array();
$sum1 = '3401';
$sum2 = '50';
$query_k = sprintf("select SUM(rep_de), SUM(rep_cr) from financial_report_month where rep_ymd>=%d and rep_ymd<=%d and rep_summary1='%s' and rep_summary2='%s'", $str_ym, $end_ym, $sum1, $sum2);
if ($rows_k=getResultWithField2($query_k, $field_k, $res_k) <= 0) {
    $syaryo_kishu_chou_kin = $syaryo_shu_kishu_kin + $syaryo_kishu_zan_kin;
    $syaryo_rui_gen_kin   = 0;
    $syaryo_rui_syo_kin   = 0;
} else {
    $syaryo_kishu_chou_kin = $syaryo_shu_kishu_kin + $syaryo_kishu_zan_kin;
    $syaryo_rui_gen_kin    = $res_k[0][0];
    $syaryo_rui_syo_kin    = $res_k[0][1];
}
$month[24][0] = '車輛運搬具取得価額期首残高';
$month[24][1] = $syaryo_shu_kishu_kin;
$month[25][0] = '車輛運搬具取得価額期中増加';
$month[25][1] = $syaryo_shu_zou_kin;
$month[26][0] = '車輛運搬具取得価額期中減少';
$month[26][1] = $syaryo_shu_gen_kin;
$month[27][0] = '車輛運搬具期首帳簿価額';
$month[27][1] = $syaryo_kishu_chou_kin;
$month[28][0] = '車輛運搬具累計額期中減少';
$month[28][1] = $syaryo_rui_gen_kin;
$month[29][0] = '車輛運搬具累計額当期償却額';
$month[29][1] = $syaryo_rui_syo_kin;

// 器具工具
$res_k   = array();
$field_k = array();
$rows_k  = array();
$sum1 = '2106';
$sum2 = '00';
$query_k = sprintf("select SUM(rep_cri), SUM(rep_de), SUM(rep_cr) from financial_report_cal where rep_ymd=%d and rep_summary1='%s' and rep_summary2='%s'", $nk_ki, $sum1, $sum2);
if ($rows_k=getResultWithField2($query_k, $field_k, $res_k) <= 0) {
    $kigu_shu_kishu_kin = 0;
} else {
    $kigu_shu_kishu_kin = $res_k[0][0];
}
$res_k   = array();
$field_k = array();
$rows_k  = array();
$sum1 = '2106';
$sum2 = '00';
$query_k = sprintf("select SUM(rep_de), SUM(rep_cr) from financial_report_month where rep_ymd>=%d and rep_ymd<=%d and rep_summary1='%s' and rep_summary2='%s'", $str_ym, $end_ym, $sum1, $sum2);
if ($rows_k=getResultWithField2($query_k, $field_k, $res_k) <= 0) {
    $kigu_shu_zou_kin   = 0;
    $kigu_shu_gen_kin   = 0;
} else {
    $kigu_shu_zou_kin   = $res_k[0][0];
    $kigu_shu_gen_kin   = $res_k[0][1];
}

$res_k   = array();
$field_k = array();
$rows_k  = array();
$sum1 = '3401';
$sum2 = '60';
$query_k = sprintf("select SUM(rep_cri), SUM(rep_de), SUM(rep_cr) from financial_report_cal where rep_ymd=%d and rep_summary1='%s' and rep_summary2='%s'", $nk_ki, $sum1, $sum2);
if ($rows_k=getResultWithField2($query_k, $field_k, $res_k) <= 0) {
    $kigu_kishu_zan_kin = 0;
} else {
    $kigu_kishu_zan_kin = $res_k[0][0];
}

$res_k   = array();
$field_k = array();
$rows_k  = array();
$sum1 = '3401';
$sum2 = '60';
$query_k = sprintf("select SUM(rep_de), SUM(rep_cr) from financial_report_month where rep_ymd>=%d and rep_ymd<=%d and rep_summary1='%s' and rep_summary2='%s'", $str_ym, $end_ym, $sum1, $sum2);
if ($rows_k=getResultWithField2($query_k, $field_k, $res_k) <= 0) {
    $kigu_kishu_chou_kin = $kigu_shu_kishu_kin + $kigu_kishu_zan_kin;
    $kigu_rui_gen_kin   = 0;
    $kigu_rui_syo_kin   = 0;
} else {
    $kigu_kishu_chou_kin = $kigu_shu_kishu_kin + $kigu_kishu_zan_kin;
    $kigu_rui_gen_kin    = $res_k[0][0];
    $kigu_rui_syo_kin    = $res_k[0][1];
}
$month[30][0] = '器具工具取得価額期首残高';
$month[30][1] = $kigu_shu_kishu_kin;
$month[31][0] = '器具工具取得価額期中増加';
$month[31][1] = $kigu_shu_zou_kin;
$month[32][0] = '器具工具取得価額期中減少';
$month[32][1] = $kigu_shu_gen_kin;
$month[33][0] = '器具工具期首帳簿価額';
$month[33][1] = $kigu_kishu_chou_kin;
$month[34][0] = '器具工具累計額期中減少';
$month[34][1] = $kigu_rui_gen_kin;
$month[35][0] = '器具工具累計額当期償却額';
$month[35][1] = $kigu_rui_syo_kin;

// 什器備品
$res_k   = array();
$field_k = array();
$rows_k  = array();
$sum1 = '2107';
$sum2 = '00';
$query_k = sprintf("select SUM(rep_cri), SUM(rep_de), SUM(rep_cr) from financial_report_cal where rep_ymd=%d and rep_summary1='%s' and rep_summary2='%s'", $nk_ki, $sum1, $sum2);
if ($rows_k=getResultWithField2($query_k, $field_k, $res_k) <= 0) {
    $bihin_shu_kishu_kin = 0;
} else {
    $bihin_shu_kishu_kin = $res_k[0][0];
}
$res_k   = array();
$field_k = array();
$rows_k  = array();
$sum1 = '2107';
$sum2 = '00';
$query_k = sprintf("select SUM(rep_de), SUM(rep_cr) from financial_report_month where rep_ymd>=%d and rep_ymd<=%d and rep_summary1='%s' and rep_summary2='%s'", $str_ym, $end_ym, $sum1, $sum2);
if ($rows_k=getResultWithField2($query_k, $field_k, $res_k) <= 0) {
    $bihin_shu_zou_kin   = 0;
    $bihin_shu_gen_kin   = 0;
} else {
    $bihin_shu_zou_kin   = $res_k[0][0];
    $bihin_shu_gen_kin   = $res_k[0][1];
}

$res_k   = array();
$field_k = array();
$rows_k  = array();
$sum1 = '3401';
$sum2 = '70';
$query_k = sprintf("select SUM(rep_cri), SUM(rep_de), SUM(rep_cr) from financial_report_cal where rep_ymd=%d and rep_summary1='%s' and rep_summary2='%s'", $nk_ki, $sum1, $sum2);
if ($rows_k=getResultWithField2($query_k, $field_k, $res_k) <= 0) {
    $bihin_kishu_zan_kin = 0;
} else {
    $bihin_kishu_zan_kin = $res_k[0][0];
}

$res_k   = array();
$field_k = array();
$rows_k  = array();
$sum1 = '3401';
$sum2 = '70';
$query_k = sprintf("select SUM(rep_de), SUM(rep_cr) from financial_report_month where rep_ymd>=%d and rep_ymd<=%d and rep_summary1='%s' and rep_summary2='%s'", $str_ym, $end_ym, $sum1, $sum2);
if ($rows_k=getResultWithField2($query_k, $field_k, $res_k) <= 0) {
    $bihin_kishu_chou_kin = $bihin_shu_kishu_kin + $bihin_kishu_zan_kin;
    $bihin_rui_gen_kin   = 0;
    $bihin_rui_syo_kin   = 0;
} else {
    $bihin_kishu_chou_kin = $bihin_shu_kishu_kin + $bihin_kishu_zan_kin;
    $bihin_rui_gen_kin    = $res_k[0][0];
    $bihin_rui_syo_kin    = $res_k[0][1];
}
$month[36][0] = '什器備品取得価額期首残高';
$month[36][1] = $bihin_shu_kishu_kin;
$month[37][0] = '什器備品取得価額期中増加';
$month[37][1] = $bihin_shu_zou_kin;
$month[38][0] = '什器備品取得価額期中減少';
$month[38][1] = $bihin_shu_gen_kin;
$month[39][0] = '什器備品期首帳簿価額';
$month[39][1] = $bihin_kishu_chou_kin;
$month[40][0] = '什器備品累計額期中減少';
$month[40][1] = $bihin_rui_gen_kin;
$month[41][0] = '什器備品累計額当期償却額';
$month[41][1] = $bihin_rui_syo_kin;

// リース資産
$res_k   = array();
$field_k = array();
$rows_k  = array();
$sum1 = '2110';
$sum2 = '00';
$query_k = sprintf("select SUM(rep_cri), SUM(rep_de), SUM(rep_cr) from financial_report_cal where rep_ymd=%d and rep_summary1='%s' and rep_summary2='%s'", $nk_ki, $sum1, $sum2);
if ($rows_k=getResultWithField2($query_k, $field_k, $res_k) <= 0) {
    $lease_shu_kishu_kin = 0;
} else {
    $lease_shu_kishu_kin = $res_k[0][0];
}
$res_k   = array();
$field_k = array();
$rows_k  = array();
$sum1 = '2110';
$sum2 = '00';
$query_k = sprintf("select SUM(rep_de), SUM(rep_cr) from financial_report_month where rep_ymd>=%d and rep_ymd<=%d and rep_summary1='%s' and rep_summary2='%s'", $str_ym, $end_ym, $sum1, $sum2);
if ($rows_k=getResultWithField2($query_k, $field_k, $res_k) <= 0) {
    $lease_shu_zou_kin   = 0;
    $lease_shu_gen_kin   = 0;
} else {
    $lease_shu_zou_kin   = $res_k[0][0];
    $lease_shu_gen_kin   = $res_k[0][1];
}

$res_k   = array();
$field_k = array();
$rows_k  = array();
$sum1 = '3401';
$sum2 = '80';
$query_k = sprintf("select SUM(rep_cri), SUM(rep_de), SUM(rep_cr) from financial_report_cal where rep_ymd=%d and rep_summary1='%s' and rep_summary2='%s'", $nk_ki, $sum1, $sum2);
if ($rows_k=getResultWithField2($query_k, $field_k, $res_k) <= 0) {
    $lease_kishu_zan_kin = 0;
} else {
    $lease_kishu_zan_kin = $res_k[0][0];
}

$res_k   = array();
$field_k = array();
$rows_k  = array();
$sum1 = '3401';
$sum2 = '80';
$query_k = sprintf("select SUM(rep_de), SUM(rep_cr) from financial_report_month where rep_ymd>=%d and rep_ymd<=%d and rep_summary1='%s' and rep_summary2='%s'", $str_ym, $end_ym, $sum1, $sum2);
if ($rows_k=getResultWithField2($query_k, $field_k, $res_k) <= 0) {
    $lease_kishu_chou_kin = $lease_shu_kishu_kin + $lease_kishu_zan_kin;
    $lease_rui_gen_kin   = 0;
    $lease_rui_syo_kin   = 0;
} else {
    $lease_kishu_chou_kin = $lease_shu_kishu_kin + $lease_kishu_zan_kin;
    $lease_rui_gen_kin    = $res_k[0][0];
    $lease_rui_syo_kin    = $res_k[0][1];
}
$month[42][0] = 'リース資産取得価額期首残高';
$month[42][1] = $lease_shu_kishu_kin;
$month[43][0] = 'リース資産取得価額期中増加';
$month[43][1] = $lease_shu_zou_kin;
$month[44][0] = 'リース資産取得価額期中減少';
$month[44][1] = $lease_shu_gen_kin;
$month[45][0] = 'リース資産期首帳簿価額';
$month[45][1] = $lease_kishu_chou_kin;
$month[46][0] = 'リース資産累計額期中減少';
$month[46][1] = $lease_rui_gen_kin;
$month[47][0] = 'リース資産累計額当期償却額';
$month[47][1] = $lease_rui_syo_kin;

// 電話加入権
$res   = array();
$field = array();
$rows  = array();
$note = '電話期首残高';
$query = sprintf("SELECT rep_kin FROM financial_report_data WHERE rep_ymd=%d and rep_note='%s'", $yyyymm, $note);
if ($rows=getResultWithField2($query, $field, $res) <= 0) {
    $denwa_shu_kishu_kin = 0;
} else {
    $denwa_shu_kishu_kin = $res[0][0];
}
$res   = array();
$field = array();
$rows  = array();
$note = '電話期中増加';
$query = sprintf("SELECT rep_kin FROM financial_report_data WHERE rep_ymd=%d and rep_note='%s'", $yyyymm, $note);
if ($rows=getResultWithField2($query, $field, $res) <= 0) {
    $denwa_shu_zou_kin = 0;
} else {
    $denwa_shu_zou_kin = $res[0][0];
}
$res   = array();
$field = array();
$rows  = array();
$note = '電話期中減少';
$query = sprintf("SELECT rep_kin FROM financial_report_data WHERE rep_ymd=%d and rep_note='%s'", $yyyymm, $note);
if ($rows=getResultWithField2($query, $field, $res) <= 0) {
    $denwa_shu_gen_kin = 0;
} else {
    $denwa_shu_gen_kin = $res[0][0];
}

$res_k   = array();
$field_k = array();
$rows_k  = array();
$sum1 = '2207';
$sum2 = '00';
$query_k = sprintf("select SUM(rep_cri), SUM(rep_de), SUM(rep_cr) from financial_report_cal where rep_ymd=%d and rep_summary1='%s' and rep_summary2='%s'", $nk_ki, $sum1, $sum2);
if ($rows_k=getResultWithField2($query_k, $field_k, $res_k) <= 0) {
    $denwa_kishu_chou_kin = 0;
} else {
    $denwa_kishu_chou_kin = $res_k[0][0];
}
$res_k   = array();
$field_k = array();
$rows_k  = array();
$sum1 = '2207';
$sum2 = '00';
$query_k = sprintf("select SUM(rep_de), SUM(rep_cr) from financial_report_month where rep_ymd>=%d and rep_ymd<=%d and rep_summary1='%s' and rep_summary2='%s'", $str_ym, $end_ym, $sum1, $sum2);
if ($rows_k=getResultWithField2($query_k, $field_k, $res_k) <= 0) {
    $denwa_rui_gen_kin   = 0;
    $denwa_rui_syo_kin   = 0;
} else {
    $denwa_rui_gen_kin   = $res_k[0][0];
    $denwa_rui_syo_kin   = $res_k[0][1];
}
/*
$res_k   = array();
$field_k = array();
$rows_k  = array();
$sum1 = '2207';
$sum2 = '00';
$query_k = sprintf("select rep_cri, rep_de, rep_cr from financial_report_cal where rep_ymd=%d and rep_summary1='%s' and rep_summary2='%s'", $nk_ki, $sum1, $sum2);
if ($rows_k=getResultWithField2($query_k, $field_k, $res_k) <= 0) {
    $denwa_kishu_chou_kin = 0;
    $denwa_rui_gen_kin    = 0;
    $denwa_rui_syo_kin    = 0;
} else {
    $denwa_kishu_chou_kin = $res_k[0][0];
    $denwa_rui_gen_kin    = $res_k[0][1];
    $denwa_rui_syo_kin    = $res_k[0][2];
}
*/
$month[48][0] = '電話加入権取得価額期首残高';
$month[48][1] = $denwa_shu_kishu_kin;
$month[49][0] = '電話加入権取得価額期中増加';
$month[49][1] = $denwa_shu_zou_kin;
$month[50][0] = '電話加入権取得価額期中減少';
$month[50][1] = $denwa_shu_gen_kin;
$month[51][0] = '電話加入権期首帳簿価額';
$month[51][1] = $denwa_kishu_chou_kin;
$month[52][0] = '電話加入権累計額期中減少';
$month[52][1] = $denwa_rui_gen_kin;
$month[53][0] = '電話加入権累計額当期償却額';
$month[53][1] = $denwa_rui_syo_kin;

// 施設利用権
$res   = array();
$field = array();
$rows  = array();
$note = '施設期首残高';
$query = sprintf("SELECT rep_kin FROM financial_report_data WHERE rep_ymd=%d and rep_note='%s'", $yyyymm, $note);
if ($rows=getResultWithField2($query, $field, $res) <= 0) {
    $sisetu_shu_kishu_kin = 0;
} else {
    $sisetu_shu_kishu_kin = $res[0][0];
}
$res   = array();
$field = array();
$rows  = array();
$note = '施設期中増加';
$query = sprintf("SELECT rep_kin FROM financial_report_data WHERE rep_ymd=%d and rep_note='%s'", $yyyymm, $note);
if ($rows=getResultWithField2($query, $field, $res) <= 0) {
    $sisetu_shu_zou_kin = 0;
} else {
    $sisetu_shu_zou_kin = $res[0][0];
}
$res   = array();
$field = array();
$rows  = array();
$note = '施設期中減少';
$query = sprintf("SELECT rep_kin FROM financial_report_data WHERE rep_ymd=%d and rep_note='%s'", $yyyymm, $note);
if ($rows=getResultWithField2($query, $field, $res) <= 0) {
    $sisetu_shu_gen_kin = 0;
} else {
    $sisetu_shu_gen_kin = $res[0][0];
}

$res_k   = array();
$field_k = array();
$rows_k  = array();
$sum1 = '2208';
$sum2 = '00';
$query_k = sprintf("select SUM(rep_cri), SUM(rep_de), SUM(rep_cr) from financial_report_cal where rep_ymd=%d and rep_summary1='%s' and rep_summary2='%s'", $nk_ki, $sum1, $sum2);
if ($rows_k=getResultWithField2($query_k, $field_k, $res_k) <= 0) {
    $sisetu_kishu_chou_kin = 0;
} else {
    $sisetu_kishu_chou_kin = $res_k[0][0];
}
$res_k   = array();
$field_k = array();
$rows_k  = array();
$sum1 = '2208';
$sum2 = '00';
$query_k = sprintf("select SUM(rep_de), SUM(rep_cr) from financial_report_month where rep_ymd>=%d and rep_ymd<=%d and rep_summary1='%s' and rep_summary2='%s'", $str_ym, $end_ym, $sum1, $sum2);
if ($rows_k=getResultWithField2($query_k, $field_k, $res_k) <= 0) {
    $sisetu_rui_gen_kin   = 0;
    $sisetu_rui_syo_kin   = 0;
} else {
    $sisetu_rui_gen_kin   = $res_k[0][0];
    $sisetu_rui_syo_kin   = $res_k[0][1];
}
/*
$res_k   = array();
$field_k = array();
$rows_k  = array();
$sum1 = '2208';
$sum2 = '00';
$query_k = sprintf("select rep_cri, rep_de, rep_cr from financial_report_cal where rep_ymd=%d and rep_summary1='%s' and rep_summary2='%s'", $nk_ki, $sum1, $sum2);
if ($rows_k=getResultWithField2($query_k, $field_k, $res_k) <= 0) {
    $sisetu_kishu_chou_kin = 0;
    $sisetu_rui_gen_kin    = 0;
    $sisetu_rui_syo_kin    = 0;
} else {
    $sisetu_kishu_chou_kin = $res_k[0][0];
    $sisetu_rui_gen_kin    = 0;                 //$res_k[0][1];
    $sisetu_rui_syo_kin    = $res_k[0][2];
}
*/
$month[54][0] = '施設利用権取得価額期首残高';
$month[54][1] = $sisetu_shu_kishu_kin;
$month[55][0] = '施設利用権取得価額期中増加';
$month[55][1] = $sisetu_shu_zou_kin;
$month[56][0] = '施設利用権取得価額期中減少';
$month[56][1] = $sisetu_shu_gen_kin;
$month[57][0] = '施設利用権期首帳簿価額';
$month[57][1] = $sisetu_kishu_chou_kin;
$month[58][0] = '施設利用権累計額期中減少';
$month[58][1] = $sisetu_rui_gen_kin;
$month[59][0] = '施設利用権累計額当期償却額';
$month[59][1] = $sisetu_rui_syo_kin;

// ソフトウェア
$res   = array();
$field = array();
$rows  = array();
$note = 'ソフト期首残高';
$query = sprintf("SELECT rep_kin FROM financial_report_data WHERE rep_ymd=%d and rep_note='%s'", $yyyymm, $note);
if ($rows=getResultWithField2($query, $field, $res) <= 0) {
    $soft_shu_kishu_kin = 0;
} else {
    $soft_shu_kishu_kin = $res[0][0];
}
$res   = array();
$field = array();
$rows  = array();
$note = 'ソフト期中増加';
$query = sprintf("SELECT rep_kin FROM financial_report_data WHERE rep_ymd=%d and rep_note='%s'", $yyyymm, $note);
if ($rows=getResultWithField2($query, $field, $res) <= 0) {
    $soft_shu_zou_kin = 0;
} else {
    $soft_shu_zou_kin = $res[0][0];
}
$res   = array();
$field = array();
$rows  = array();
$note = 'ソフト期中減少';
$query = sprintf("SELECT rep_kin FROM financial_report_data WHERE rep_ymd=%d and rep_note='%s'", $yyyymm, $note);
if ($rows=getResultWithField2($query, $field, $res) <= 0) {
    $soft_shu_gen_kin = 0;
} else {
    $soft_shu_gen_kin = $res[0][0];
}

$res_k   = array();
$field_k = array();
$rows_k  = array();
$sum1 = '2212';
$sum2 = '00';
$query_k = sprintf("select SUM(rep_cri), SUM(rep_de), SUM(rep_cr) from financial_report_cal where rep_ymd=%d and rep_summary1='%s' and rep_summary2='%s'", $nk_ki, $sum1, $sum2);
if ($rows_k=getResultWithField2($query_k, $field_k, $res_k) <= 0) {
    $soft_kishu_chou_kin = 0;
} else {
    $soft_kishu_chou_kin = $res_k[0][0];
}
$res_k   = array();
$field_k = array();
$rows_k  = array();
$sum1 = '2212';
$sum2 = '00';
$query_k = sprintf("select SUM(rep_de), SUM(rep_cr) from financial_report_month where rep_ymd>=%d and rep_ymd<=%d and rep_summary1='%s' and rep_summary2='%s'", $str_ym, $end_ym, $sum1, $sum2);
if ($rows_k=getResultWithField2($query_k, $field_k, $res_k) <= 0) {
    $soft_rui_gen_kin   = 0;
    $soft_rui_syo_kin   = 0;
} else {
    $soft_rui_gen_kin   = $res_k[0][0] - $soft_shu_zou_kin;
    $soft_rui_syo_kin   = $res_k[0][1];
}
/*
$res_k   = array();
$field_k = array();
$rows_k  = array();
$sum1 = '2212';
$sum2 = '00';
$query_k = sprintf("select rep_cri, rep_de, rep_cr from financial_report_cal where rep_ymd=%d and rep_summary1='%s' and rep_summary2='%s'", $nk_ki, $sum1, $sum2);
if ($rows_k=getResultWithField2($query_k, $field_k, $res_k) <= 0) {
    $soft_kishu_chou_kin = 0;
    $soft_rui_gen_kin    = 0;
    $soft_rui_syo_kin    = 0;
} else {
    $soft_kishu_chou_kin = $res_k[0][0];
    $soft_rui_gen_kin    = 0;               //$res_k[0][1];
    $soft_rui_syo_kin    = $res_k[0][2];
}
*/
$month[60][0] = 'ソフトウェア取得価額期首残高';
$month[60][1] = $soft_shu_kishu_kin;
$month[61][0] = 'ソフトウェア取得価額期中増加';
$month[61][1] = $soft_shu_zou_kin;
$month[62][0] = 'ソフトウェア取得価額期中減少';
$month[62][1] = $soft_shu_gen_kin;
$month[63][0] = 'ソフトウェア期首帳簿価額';
$month[63][1] = $soft_kishu_chou_kin;
$month[64][0] = 'ソフトウェア累計額期中減少';
$month[64][1] = $soft_rui_gen_kin;
$month[65][0] = 'ソフトウェア累計額当期償却額';
$month[65][1] = $soft_rui_syo_kin;

///// act_comp_invent_history よりデータ取得
    ///// 当月
/*
$month = array();
$query = "select item, kin from act_state_depreciation_history where state_ym=$yyyymm";
if (($rows = getResult2($query, $month)) <= 0) {
    $_SESSION['s_sysmsg'] = sprintf("減価償却費明細表のデータなし！<br>第 %d期 第%s四半期",$ki,$hanki);
    header("Location: $url_referer");
    exit();
} else {
*/
    $rows = count($month);
    ///// item の名前と金額を指定の単位と少数桁数でハッシュへ代入
    for ($r=0; $r<$rows; $r++) {
        $month["{$month[$r][0]}"] = Uround($month[$r][1] / $tani, $keta);
    }
    /////////////////////////////////////////////////////////////////////// 取得価額期首残高 START
    ///// 各金額を３桁カンマでハッシュへ代入
    $tbody['tbody_shutoku_kishu_tate']   = number_format($month['建物取得価額期首残高'], $keta);
    $tbody['tbody_shutoku_kishu_fuzoku'] = number_format($month['建物附属設備取得価額期首残高'], $keta);
    $tbody['tbody_shutoku_kishu_kouti']  = number_format($month['構築物取得価額期首残高']  , $keta);
    $tbody['tbody_shutoku_kishu_kikai']  = number_format($month['機械装置取得価額期首残高'], $keta);
    $tbody['tbody_shutoku_kishu_sharyo'] = number_format($month['車輛運搬具取得価額期首残高'], $keta);
    $tbody['tbody_shutoku_kishu_kigu']   = number_format($month['器具工具取得価額期首残高'], $keta);
    $tbody['tbody_shutoku_kishu_jyuki']  = number_format($month['什器備品取得価額期首残高'], $keta);
    $tbody['tbody_shutoku_kishu_lease']  = number_format($month['リース資産取得価額期首残高'], $keta);
    $tbody['tbody_shutoku_kishu_denwa']  = number_format($month['電話加入権取得価額期首残高'], $keta);
    $tbody['tbody_shutoku_kishu_shise']  = number_format($month['施設利用権取得価額期首残高'], $keta);
    $tbody['tbody_shutoku_kishu_soft']   = number_format($month['ソフトウェア取得価額期首残高'], $keta);
    ///// 建物合計、工具器具備品計、有形合計、無形合計、総合計を計算
    $total_shutoku_kishu_tate  = $month['建物取得価額期首残高'] + $month['建物附属設備取得価額期首残高'];
    $total_shutoku_kishu_kougu = $month['器具工具取得価額期首残高'] + $month['什器備品取得価額期首残高'];
    $total_shutoku_kishu_yukei = $total_shutoku_kishu_tate + $month['構築物取得価額期首残高'] + $month['機械装置取得価額期首残高'] + 
                                 $month['車輛運搬具取得価額期首残高'] + $total_shutoku_kishu_kougu + $month['リース資産取得価額期首残高'];
    $total_shutoku_kishu_mukei = $month['電話加入権取得価額期首残高'] + $month['施設利用権取得価額期首残高'] + $month['ソフトウェア取得価額期首残高'];
    $total_shutoku_kishu_all   = $total_shutoku_kishu_yukei + $total_shutoku_kishu_mukei;
    ///// 計算結果をハッシュへ代入
    $tbody['tbody_shutoku_kishu_tate_total']  = number_format($total_shutoku_kishu_tate, $keta);
    $tbody['tbody_shutoku_kishu_kougu_total'] = number_format($total_shutoku_kishu_kougu, $keta);
    $tbody['tbody_shutoku_kishu_yukei_total'] = number_format($total_shutoku_kishu_yukei, $keta);
    $tbody['tbody_shutoku_kishu_mukei_total'] = number_format($total_shutoku_kishu_mukei, $keta);
    $tbody['tbody_shutoku_kishu_all']         = number_format($total_shutoku_kishu_all, $keta);
    /////////////////////////////////////////////////////////////////////// 取得価額期首残高 END
    
    /////////////////////////////////////////////////////////////////////// 取得価額期中増加 START
    ///// 各金額を３桁カンマでハッシュへ代入
    $tbody['tbody_shutoku_zou_tate']   = number_format($month['建物取得価額期中増加'], $keta);
    $tbody['tbody_shutoku_zou_fuzoku'] = number_format($month['建物附属設備取得価額期中増加'], $keta);
    $tbody['tbody_shutoku_zou_kouti']  = number_format($month['構築物取得価額期中増加']  , $keta);
    $tbody['tbody_shutoku_zou_kikai']  = number_format($month['機械装置取得価額期中増加'], $keta);
    $tbody['tbody_shutoku_zou_sharyo'] = number_format($month['車輛運搬具取得価額期中増加'], $keta);
    $tbody['tbody_shutoku_zou_kigu']   = number_format($month['器具工具取得価額期中増加'], $keta);
    $tbody['tbody_shutoku_zou_jyuki']  = number_format($month['什器備品取得価額期中増加'], $keta);
    $tbody['tbody_shutoku_zou_lease']  = number_format($month['リース資産取得価額期中増加'], $keta);
    $tbody['tbody_shutoku_zou_denwa']  = number_format($month['電話加入権取得価額期中増加'], $keta);
    $tbody['tbody_shutoku_zou_shise']  = number_format($month['施設利用権取得価額期中増加'], $keta);
    $tbody['tbody_shutoku_zou_soft']   = number_format($month['ソフトウェア取得価額期中増加'], $keta);
    ///// 建物合計、工具器具備品計、有形合計、無形合計、総合計を計算
    $total_shutoku_zou_tate  = $month['建物取得価額期中増加'] + $month['建物附属設備取得価額期中増加'];
    $total_shutoku_zou_kougu = $month['器具工具取得価額期中増加'] + $month['什器備品取得価額期中増加'];
    $total_shutoku_zou_yukei = $total_shutoku_zou_tate + $month['構築物取得価額期中増加'] + $month['機械装置取得価額期中増加'] + 
                               $month['車輛運搬具取得価額期中増加'] + $total_shutoku_zou_kougu + $month['リース資産取得価額期中増加'];
    $total_shutoku_zou_mukei = $month['電話加入権取得価額期中増加'] + $month['施設利用権取得価額期中増加'] + $month['ソフトウェア取得価額期中増加'];
    $total_shutoku_zou_all   = $total_shutoku_zou_yukei + $total_shutoku_zou_mukei;
    ///// 計算結果をハッシュへ代入
    $tbody['tbody_shutoku_zou_tate_total']  = number_format($total_shutoku_zou_tate, $keta);
    $tbody['tbody_shutoku_zou_kougu_total'] = number_format($total_shutoku_zou_kougu, $keta);
    $tbody['tbody_shutoku_zou_yukei_total'] = number_format($total_shutoku_zou_yukei, $keta);
    $tbody['tbody_shutoku_zou_mukei_total'] = number_format($total_shutoku_zou_mukei, $keta);
    $tbody['tbody_shutoku_zou_all']         = number_format($total_shutoku_zou_all, $keta);
    /////////////////////////////////////////////////////////////////////// 取得価額期中増加 END
    
    /////////////////////////////////////////////////////////////////////// 取得価額期中減少 START
    ///// 各金額を３桁カンマでハッシュへ代入
    $tbody['tbody_shutoku_gen_tate']   = number_format($month['建物取得価額期中減少'], $keta);
    $tbody['tbody_shutoku_gen_fuzoku'] = number_format($month['建物附属設備取得価額期中減少'], $keta);
    $tbody['tbody_shutoku_gen_kouti']  = number_format($month['構築物取得価額期中減少']  , $keta);
    $tbody['tbody_shutoku_gen_kikai']  = number_format($month['機械装置取得価額期中減少'], $keta);
    $tbody['tbody_shutoku_gen_sharyo'] = number_format($month['車輛運搬具取得価額期中減少'], $keta);
    $tbody['tbody_shutoku_gen_kigu']   = number_format($month['器具工具取得価額期中減少'], $keta);
    $tbody['tbody_shutoku_gen_jyuki']  = number_format($month['什器備品取得価額期中減少'], $keta);
    $tbody['tbody_shutoku_gen_lease']  = number_format($month['リース資産取得価額期中減少'], $keta);
    $tbody['tbody_shutoku_gen_denwa']  = number_format($month['電話加入権取得価額期中減少'], $keta);
    $tbody['tbody_shutoku_gen_shise']  = number_format($month['施設利用権取得価額期中減少'], $keta);
    $tbody['tbody_shutoku_gen_soft']   = number_format($month['ソフトウェア取得価額期中減少'], $keta);
    ///// 建物合計、工具器具備品計、有形合計、無形合計、総合計を計算
    $total_shutoku_gen_tate  = $month['建物取得価額期中減少'] + $month['建物附属設備取得価額期中減少'];
    $total_shutoku_gen_kougu = $month['器具工具取得価額期中減少'] + $month['什器備品取得価額期中減少'];
    $total_shutoku_gen_yukei = $total_shutoku_gen_tate + $month['構築物取得価額期中減少'] + $month['機械装置取得価額期中減少'] + 
                               $month['車輛運搬具取得価額期中減少'] + $total_shutoku_gen_kougu + $month['リース資産取得価額期中減少'];
    $total_shutoku_gen_mukei = $month['電話加入権取得価額期中減少'] + $month['施設利用権取得価額期中減少'] + $month['ソフトウェア取得価額期中減少'];
    $total_shutoku_gen_all   = $total_shutoku_gen_yukei + $total_shutoku_gen_mukei;
    ///// 計算結果をハッシュへ代入
    $tbody['tbody_shutoku_gen_tate_total']  = number_format($total_shutoku_gen_tate, $keta);
    $tbody['tbody_shutoku_gen_kougu_total'] = number_format($total_shutoku_gen_kougu, $keta);
    $tbody['tbody_shutoku_gen_yukei_total'] = number_format($total_shutoku_gen_yukei, $keta);
    $tbody['tbody_shutoku_gen_mukei_total'] = number_format($total_shutoku_gen_mukei, $keta);
    $tbody['tbody_shutoku_gen_all']         = number_format($total_shutoku_gen_all, $keta);
    /////////////////////////////////////////////////////////////////////// 取得価額期中減少 END
    
    /////////////////////////////////////////////////////////////////////// 取得価額期末残高 START
    ///// 各期末残高を計算
    // 建物、建物附属設備
    $tbody_shutoku_kima_tate   = $month['建物取得価額期首残高'] + $month['建物取得価額期中増加'] - $month['建物取得価額期中減少'];
    $tbody_shutoku_kima_fuzoku = $month['建物附属設備取得価額期首残高'] + $month['建物附属設備取得価額期中増加'] - $month['建物附属設備取得価額期中減少'];
    // 建物合計
    $total_shutoku_kima_tate   = $tbody_shutoku_kima_tate + $tbody_shutoku_kima_fuzoku;
    // 構築物、機械装置、車輛運搬具、器具工具、什器備品
    $tbody_shutoku_kima_kouti  = $month['構築物取得価額期首残高'] + $month['構築物取得価額期中増加'] - $month['構築物取得価額期中減少'];
    $tbody_shutoku_kima_kikai  = $month['機械装置取得価額期首残高'] + $month['機械装置取得価額期中増加'] - $month['機械装置取得価額期中減少'];
    $tbody_shutoku_kima_sharyo = $month['車輛運搬具取得価額期首残高'] + $month['車輛運搬具取得価額期中増加'] - $month['車輛運搬具取得価額期中減少'];
    $tbody_shutoku_kima_kigu   = $month['器具工具取得価額期首残高'] + $month['器具工具取得価額期中増加'] - $month['器具工具取得価額期中減少'];
    $tbody_shutoku_kima_jyuki  = $month['什器備品取得価額期首残高'] + $month['什器備品取得価額期中増加'] - $month['什器備品取得価額期中減少'];
    // 器具工具、什器備品合計
    $total_shutoku_kima_kougu  = $tbody_shutoku_kima_kigu + $tbody_shutoku_kima_jyuki;
    // リース資産
    $tbody_shutoku_kima_lease  = $month['リース資産取得価額期首残高'] + $month['リース資産取得価額期中増加'] - $month['リース資産取得価額期中減少'];
    // 有形合計
    $total_shutoku_kima_yukei  = $total_shutoku_kima_tate + $tbody_shutoku_kima_kouti + $tbody_shutoku_kima_kikai + 
                                 $tbody_shutoku_kima_sharyo + $total_shutoku_kima_kougu + $tbody_shutoku_kima_lease;
    // 電話加入権、施設利用権、ソフトウェア
    $tbody_shutoku_kima_denwa  = $month['電話加入権取得価額期首残高'] + $month['電話加入権取得価額期中増加'] - $month['電話加入権取得価額期中減少'];
    $tbody_shutoku_kima_shise  = $month['施設利用権取得価額期首残高'] + $month['施設利用権取得価額期中増加'] - $month['施設利用権取得価額期中減少'];
    $tbody_shutoku_kima_soft   = $month['ソフトウェア取得価額期首残高'] + $month['ソフトウェア取得価額期中増加'] - $month['ソフトウェア取得価額期中減少'];
    // 無形合計
    $total_shutoku_kima_mukei  = $tbody_shutoku_kima_denwa + $tbody_shutoku_kima_shise + $tbody_shutoku_kima_soft;
    // 総合計
    $total_shutoku_kima_all    = $total_shutoku_kima_yukei + $total_shutoku_kima_mukei;
    ///// 計算結果をハッシュへ代入
    $tbody['tbody_shutoku_kima_tate']        = number_format($tbody_shutoku_kima_tate, $keta);
    $tbody['tbody_shutoku_kima_fuzoku']      = number_format($tbody_shutoku_kima_fuzoku, $keta);
    $tbody['tbody_shutoku_kima_tate_total']  = number_format($total_shutoku_kima_tate, $keta);
    $tbody['tbody_shutoku_kima_kouti']       = number_format($tbody_shutoku_kima_kouti, $keta);
    $tbody['tbody_shutoku_kima_kikai']       = number_format($tbody_shutoku_kima_kikai, $keta);
    $tbody['tbody_shutoku_kima_sharyo']      = number_format($tbody_shutoku_kima_sharyo, $keta);
    $tbody['tbody_shutoku_kima_kigu']        = number_format($tbody_shutoku_kima_kigu, $keta);
    $tbody['tbody_shutoku_kima_jyuki']       = number_format($tbody_shutoku_kima_jyuki, $keta);
    $tbody['tbody_shutoku_kima_kougu_total'] = number_format($total_shutoku_kima_kougu, $keta);
    $tbody['tbody_shutoku_kima_lease']       = number_format($tbody_shutoku_kima_lease, $keta);
    $tbody['tbody_shutoku_kima_yukei_total'] = number_format($total_shutoku_kima_yukei, $keta);
    $tbody['tbody_shutoku_kima_denwa']       = number_format($tbody_shutoku_kima_denwa, $keta);
    $tbody['tbody_shutoku_kima_shise']       = number_format($tbody_shutoku_kima_shise, $keta);
    $tbody['tbody_shutoku_kima_soft']        = number_format($tbody_shutoku_kima_soft, $keta);
    $tbody['tbody_shutoku_kima_mukei_total'] = number_format($total_shutoku_kima_mukei, $keta);
    $tbody['tbody_shutoku_kima_all']         = number_format($total_shutoku_kima_all, $keta);
    /////////////////////////////////////////////////////////////////////// 取得価額期末残高 END
    
    /////////////////////////////////////////////////////////////////////// 期首帳簿価額 START
    ///// 各金額を３桁カンマでハッシュへ代入
    $tbody['tbody_kishu_cho_tate']   = number_format($month['建物期首帳簿価額'], $keta);
    $tbody['tbody_kishu_cho_fuzoku'] = number_format($month['建物附属設備期首帳簿価額'], $keta);
    $tbody['tbody_kishu_cho_kouti']  = number_format($month['構築物期首帳簿価額']  , $keta);
    $tbody['tbody_kishu_cho_kikai']  = number_format($month['機械装置期首帳簿価額'], $keta);
    $tbody['tbody_kishu_cho_sharyo'] = number_format($month['車輛運搬具期首帳簿価額'], $keta);
    $tbody['tbody_kishu_cho_kigu']   = number_format($month['器具工具期首帳簿価額'], $keta);
    $tbody['tbody_kishu_cho_jyuki']  = number_format($month['什器備品期首帳簿価額'], $keta);
    $tbody['tbody_kishu_cho_lease']  = number_format($month['リース資産期首帳簿価額'], $keta);
    $tbody['tbody_kishu_cho_denwa']  = number_format($month['電話加入権期首帳簿価額'], $keta);
    $tbody['tbody_kishu_cho_shise']  = number_format($month['施設利用権期首帳簿価額'], $keta);
    $tbody['tbody_kishu_cho_soft']   = number_format($month['ソフトウェア期首帳簿価額'], $keta);
    ///// 建物合計、工具器具備品計、有形合計、無形合計、総合計を計算
    $total_kishu_cho_tate  = $month['建物期首帳簿価額'] + $month['建物附属設備期首帳簿価額'];
    $total_kishu_cho_kougu = $month['器具工具期首帳簿価額'] + $month['什器備品期首帳簿価額'];
    $total_kishu_cho_yukei = $total_kishu_cho_tate + $month['構築物期首帳簿価額'] + $month['機械装置期首帳簿価額'] + 
                             $month['車輛運搬具期首帳簿価額'] + $total_kishu_cho_kougu + $month['リース資産期首帳簿価額'];
    $total_kishu_cho_mukei = $month['電話加入権期首帳簿価額'] + $month['施設利用権期首帳簿価額'] + $month['ソフトウェア期首帳簿価額'];
    $total_kishu_cho_all   = $total_kishu_cho_yukei + $total_kishu_cho_mukei;
    ///// 計算結果をハッシュへ代入
    $tbody['tbody_kishu_cho_tate_total']  = number_format($total_kishu_cho_tate, $keta);
    $tbody['tbody_kishu_cho_kougu_total'] = number_format($total_kishu_cho_kougu, $keta);
    $tbody['tbody_kishu_cho_yukei_total'] = number_format($total_kishu_cho_yukei, $keta);
    $tbody['tbody_kishu_cho_mukei_total'] = number_format($total_kishu_cho_mukei, $keta);
    $tbody['tbody_kishu_cho_all']         = number_format($total_kishu_cho_all, $keta);
    /////////////////////////////////////////////////////////////////////// 期首帳簿価額 END
    
    /////////////////////////////////////////////////////////////////////// 減価償却累計額期首残高 START
    ///// 各期末残高を計算
    // 建物、建物附属設備
    $tbody_rui_kishu_tate   = $month['建物取得価額期首残高'] - $month['建物期首帳簿価額'];
    $tbody_rui_kishu_fuzoku = $month['建物附属設備取得価額期首残高'] - $month['建物附属設備期首帳簿価額'];
    // 建物合計
    $total_rui_kishu_tate   = $tbody_rui_kishu_tate + $tbody_rui_kishu_fuzoku;
    // 構築物、機械装置、車輛運搬具、器具工具、什器備品
    $tbody_rui_kishu_kouti  = $month['構築物取得価額期首残高'] - $month['構築物期首帳簿価額'];
    $tbody_rui_kishu_kikai  = $month['機械装置取得価額期首残高'] - $month['機械装置期首帳簿価額'];
    $tbody_rui_kishu_sharyo = $month['車輛運搬具取得価額期首残高'] - $month['車輛運搬具期首帳簿価額'];
    $tbody_rui_kishu_kigu   = $month['器具工具取得価額期首残高'] - $month['器具工具期首帳簿価額'];
    $tbody_rui_kishu_jyuki  = $month['什器備品取得価額期首残高'] - $month['什器備品期首帳簿価額'];
    // 器具工具、什器備品合計
    $total_rui_kishu_kougu  = $tbody_rui_kishu_kigu + $tbody_rui_kishu_jyuki;
    // リース資産
    $tbody_rui_kishu_lease  = $month['リース資産取得価額期首残高'] - $month['リース資産期首帳簿価額'];
    // 有形合計
    $total_rui_kishu_yukei  = $total_rui_kishu_tate + $tbody_rui_kishu_kouti + $tbody_rui_kishu_kikai + 
                              $tbody_rui_kishu_sharyo + $total_rui_kishu_kougu + $tbody_rui_kishu_lease;
    // 電話加入権、施設利用権、ソフトウェア
    $tbody_rui_kishu_denwa  = $month['電話加入権取得価額期首残高'] - $month['電話加入権期首帳簿価額'];
    $tbody_rui_kishu_shise  = $month['施設利用権取得価額期首残高'] - $month['施設利用権期首帳簿価額'];
    $tbody_rui_kishu_soft   = $month['ソフトウェア取得価額期首残高'] - $month['ソフトウェア期首帳簿価額'];
    // 無形合計
    $total_rui_kishu_mukei  = $tbody_rui_kishu_denwa + $tbody_rui_kishu_shise + $tbody_rui_kishu_soft;
    // 総合計
    $total_rui_kishu_all    = $total_rui_kishu_yukei + $total_rui_kishu_mukei;
    ///// 計算結果をハッシュへ代入
    $tbody['tbody_rui_kishu_tate']        = number_format($tbody_rui_kishu_tate, $keta);
    $tbody['tbody_rui_kishu_fuzoku']      = number_format($tbody_rui_kishu_fuzoku, $keta);
    $tbody['tbody_rui_kishu_tate_total']  = number_format($total_rui_kishu_tate, $keta);
    $tbody['tbody_rui_kishu_kouti']       = number_format($tbody_rui_kishu_kouti, $keta);
    $tbody['tbody_rui_kishu_kikai']       = number_format($tbody_rui_kishu_kikai, $keta);
    $tbody['tbody_rui_kishu_sharyo']      = number_format($tbody_rui_kishu_sharyo, $keta);
    $tbody['tbody_rui_kishu_kigu']        = number_format($tbody_rui_kishu_kigu, $keta);
    $tbody['tbody_rui_kishu_jyuki']       = number_format($tbody_rui_kishu_jyuki, $keta);
    $tbody['tbody_rui_kishu_kougu_total'] = number_format($total_rui_kishu_kougu, $keta);
    $tbody['tbody_rui_kishu_lease']       = number_format($tbody_rui_kishu_lease, $keta);
    $tbody['tbody_rui_kishu_yukei_total'] = number_format($total_rui_kishu_yukei, $keta);
    $tbody['tbody_rui_kishu_denwa']       = number_format($tbody_rui_kishu_denwa, $keta);
    $tbody['tbody_rui_kishu_shise']       = number_format($tbody_rui_kishu_shise, $keta);
    $tbody['tbody_rui_kishu_soft']        = number_format($tbody_rui_kishu_soft, $keta);
    $tbody['tbody_rui_kishu_mukei_total'] = number_format($total_rui_kishu_mukei, $keta);
    $tbody['tbody_rui_kishu_all']         = number_format($total_rui_kishu_all, $keta);
    /////////////////////////////////////////////////////////////////////// 減価償却累計額期首残高 END
    
    /////////////////////////////////////////////////////////////////////// 減価償却累計額期中減少 START
    ///// 各金額を３桁カンマでハッシュへ代入
    $tbody['tbody_rui_gen_tate']   = number_format($month['建物累計額期中減少'], $keta);
    $tbody['tbody_rui_gen_fuzoku'] = number_format($month['建物附属設備累計額期中減少'], $keta);
    $tbody['tbody_rui_gen_kouti']  = number_format($month['構築物累計額期中減少']  , $keta);
    $tbody['tbody_rui_gen_kikai']  = number_format($month['機械装置累計額期中減少'], $keta);
    $tbody['tbody_rui_gen_sharyo'] = number_format($month['車輛運搬具累計額期中減少'], $keta);
    $tbody['tbody_rui_gen_kigu']   = number_format($month['器具工具累計額期中減少'], $keta);
    $tbody['tbody_rui_gen_jyuki']  = number_format($month['什器備品累計額期中減少'], $keta);
    $tbody['tbody_rui_gen_lease']  = number_format($month['リース資産累計額期中減少'], $keta);
    $tbody['tbody_rui_gen_denwa']  = number_format($month['電話加入権累計額期中減少'], $keta);
    $tbody['tbody_rui_gen_shise']  = number_format($month['施設利用権累計額期中減少'], $keta);
    $tbody['tbody_rui_gen_soft']   = number_format($month['ソフトウェア累計額期中減少'], $keta);
    ///// 建物合計、工具器具備品計、有形合計、無形合計、総合計を計算
    $total_rui_gen_tate  = $month['建物累計額期中減少'] + $month['建物附属設備累計額期中減少'];
    $total_rui_gen_kougu = $month['器具工具累計額期中減少'] + $month['什器備品累計額期中減少'];
    $total_rui_gen_yukei = $total_rui_gen_tate + $month['構築物累計額期中減少'] + $month['機械装置累計額期中減少'] + 
                           $month['車輛運搬具累計額期中減少'] + $total_rui_gen_kougu + $month['リース資産累計額期中減少'];
    $total_rui_gen_mukei = $month['電話加入権累計額期中減少'] + $month['施設利用権累計額期中減少'] + $month['ソフトウェア累計額期中減少'];
    $total_rui_gen_all   = $total_rui_gen_yukei + $total_rui_gen_mukei;
    ///// 計算結果をハッシュへ代入
    $tbody['tbody_rui_gen_tate_total']  = number_format($total_rui_gen_tate, $keta);
    $tbody['tbody_rui_gen_kougu_total'] = number_format($total_rui_gen_kougu, $keta);
    $tbody['tbody_rui_gen_yukei_total'] = number_format($total_rui_gen_yukei, $keta);
    $tbody['tbody_rui_gen_mukei_total'] = number_format($total_rui_gen_mukei, $keta);
    $tbody['tbody_rui_gen_all']         = number_format($total_rui_gen_all, $keta);
    /////////////////////////////////////////////////////////////////////// 減価償却累計額期中減少 END
    
    /////////////////////////////////////////////////////////////////////// 減価償却累計額当期償却額 START
    ///// 各金額を３桁カンマでハッシュへ代入
    $tbody['tbody_rui_syo_tate']   = number_format($month['建物累計額当期償却額'], $keta);
    $tbody['tbody_rui_syo_fuzoku'] = number_format($month['建物附属設備累計額当期償却額'], $keta);
    $tbody['tbody_rui_syo_kouti']  = number_format($month['構築物累計額当期償却額']  , $keta);
    $tbody['tbody_rui_syo_kikai']  = number_format($month['機械装置累計額当期償却額'], $keta);
    $tbody['tbody_rui_syo_sharyo'] = number_format($month['車輛運搬具累計額当期償却額'], $keta);
    $tbody['tbody_rui_syo_kigu']   = number_format($month['器具工具累計額当期償却額'], $keta);
    $tbody['tbody_rui_syo_jyuki']  = number_format($month['什器備品累計額当期償却額'], $keta);
    $tbody['tbody_rui_syo_lease']  = number_format($month['リース資産累計額当期償却額'], $keta);
    $tbody['tbody_rui_syo_denwa']  = number_format($month['電話加入権累計額当期償却額'], $keta);
    $tbody['tbody_rui_syo_shise']  = number_format($month['施設利用権累計額当期償却額'], $keta);
    $tbody['tbody_rui_syo_soft']   = number_format($month['ソフトウェア累計額当期償却額'], $keta);
    ///// 建物合計、工具器具備品計、有形合計、無形合計、総合計を計算
    $total_rui_syo_tate  = $month['建物累計額当期償却額'] + $month['建物附属設備累計額当期償却額'];
    $total_rui_syo_kougu = $month['器具工具累計額当期償却額'] + $month['什器備品累計額当期償却額'];
    $total_rui_syo_yukei = $total_rui_syo_tate + $month['構築物累計額当期償却額'] + $month['機械装置累計額当期償却額'] + 
                           $month['車輛運搬具累計額当期償却額'] + $total_rui_syo_kougu + $month['リース資産累計額当期償却額'];
    $total_rui_syo_mukei = $month['電話加入権累計額当期償却額'] + $month['施設利用権累計額当期償却額'] + $month['ソフトウェア累計額当期償却額'];
    $total_rui_syo_all   = $total_rui_syo_yukei + $total_rui_syo_mukei;
    ///// 計算結果をハッシュへ代入
    $tbody['tbody_rui_syo_tate_total']  = number_format($total_rui_syo_tate, $keta);
    $tbody['tbody_rui_syo_kougu_total'] = number_format($total_rui_syo_kougu, $keta);
    $tbody['tbody_rui_syo_yukei_total'] = number_format($total_rui_syo_yukei, $keta);
    $tbody['tbody_rui_syo_mukei_total'] = number_format($total_rui_syo_mukei, $keta);
    $tbody['tbody_rui_syo_all']         = number_format($total_rui_syo_all, $keta);
    /////////////////////////////////////////////////////////////////////// 減価償却累計額当期償却額 END
    
    /////////////////////////////////////////////////////////////////////// 減価償却累計額期末残高 START
    ///// 各期末残高を計算
    // 建物、建物附属設備
    $tbody_rui_kima_tate   = $tbody_rui_kishu_tate - $month['建物累計額期中減少'] + $month['建物累計額当期償却額'];
    $tbody_rui_kima_fuzoku = $tbody_rui_kishu_fuzoku - $month['建物附属設備累計額期中減少'] + $month['建物附属設備累計額当期償却額'];
    // 建物合計
    $total_rui_kima_tate   = $tbody_rui_kima_tate + $tbody_rui_kima_fuzoku;
    // 構築物、機械装置、車輛運搬具、器具工具、什器備品
    $tbody_rui_kima_kouti  = $tbody_rui_kishu_kouti - $month['構築物累計額期中減少'] + $month['構築物累計額当期償却額'];
    $tbody_rui_kima_kikai  = $tbody_rui_kishu_kikai - $month['機械装置累計額期中減少'] + $month['機械装置累計額当期償却額'];
    $tbody_rui_kima_sharyo = $tbody_rui_kishu_sharyo - $month['車輛運搬具累計額期中減少'] + $month['車輛運搬具累計額当期償却額'];
    $tbody_rui_kima_kigu   = $tbody_rui_kishu_kigu - $month['器具工具累計額期中減少'] + $month['器具工具累計額当期償却額'];
    $tbody_rui_kima_jyuki  = $tbody_rui_kishu_jyuki - $month['什器備品累計額期中減少'] + $month['什器備品累計額当期償却額'];
    // 器具工具、什器備品合計
    $total_rui_kima_kougu  = $tbody_rui_kima_kigu + $tbody_rui_kima_jyuki;
    // リース資産
    $tbody_rui_kima_lease  = $tbody_rui_kishu_lease - $month['リース資産累計額期中減少'] + $month['リース資産累計額当期償却額'];
    // 有形合計
    $total_rui_kima_yukei  = $total_rui_kima_tate + $tbody_rui_kima_kouti + $tbody_rui_kima_kikai + 
                             $tbody_rui_kima_sharyo + $total_rui_kima_kougu + $tbody_rui_kima_lease;
    // 電話加入権、施設利用権、ソフトウェア
    $tbody_rui_kima_denwa  = $tbody_rui_kishu_denwa - $month['電話加入権累計額期中減少'] + $month['電話加入権累計額当期償却額'];
    $tbody_rui_kima_shise  = $tbody_rui_kishu_shise - $month['施設利用権累計額期中減少'] + $month['施設利用権累計額当期償却額'];
    $tbody_rui_kima_soft   = $tbody_rui_kishu_soft - $month['ソフトウェア累計額期中減少'] + $month['ソフトウェア累計額当期償却額'];
    // 無形合計
    $total_rui_kima_mukei  = $tbody_rui_kima_denwa + $tbody_rui_kima_shise + $tbody_rui_kima_soft;
    // 総合計
    $total_rui_kima_all    = $total_rui_kima_yukei + $total_rui_kima_mukei;
    ///// 計算結果をハッシュへ代入
    $tbody['tbody_rui_kima_tate']        = number_format($tbody_rui_kima_tate, $keta);
    $tbody['tbody_rui_kima_fuzoku']      = number_format($tbody_rui_kima_fuzoku, $keta);
    $tbody['tbody_rui_kima_tate_total']  = number_format($total_rui_kima_tate, $keta);
    $tbody['tbody_rui_kima_kouti']       = number_format($tbody_rui_kima_kouti, $keta);
    $tbody['tbody_rui_kima_kikai']       = number_format($tbody_rui_kima_kikai, $keta);
    $tbody['tbody_rui_kima_sharyo']      = number_format($tbody_rui_kima_sharyo, $keta);
    $tbody['tbody_rui_kima_kigu']        = number_format($tbody_rui_kima_kigu, $keta);
    $tbody['tbody_rui_kima_jyuki']       = number_format($tbody_rui_kima_jyuki, $keta);
    $tbody['tbody_rui_kima_kougu_total'] = number_format($total_rui_kima_kougu, $keta);
    $tbody['tbody_rui_kima_lease']       = number_format($tbody_rui_kima_lease, $keta);
    $tbody['tbody_rui_kima_yukei_total'] = number_format($total_rui_kima_yukei, $keta);
    $tbody['tbody_rui_kima_denwa']       = number_format($tbody_rui_kima_denwa, $keta);
    $tbody['tbody_rui_kima_shise']       = number_format($tbody_rui_kima_shise, $keta);
    $tbody['tbody_rui_kima_soft']        = number_format($tbody_rui_kima_soft, $keta);
    $tbody['tbody_rui_kima_mukei_total'] = number_format($total_rui_kima_mukei, $keta);
    $tbody['tbody_rui_kima_all']         = number_format($total_rui_kima_all, $keta);
    /////////////////////////////////////////////////////////////////////// 減価償却累計額期末残高 END
    
    /////////////////////////////////////////////////////////////////////// 除却資産等の帳簿価額 START
    ///// 各除却資産等の帳簿価額を計算
    // 建物、建物附属設備
    $tbody_jyo_cho_tate   = $month['建物取得価額期中減少'] - $month['建物累計額期中減少'];
    $tbody_jyo_cho_fuzoku = $month['建物附属設備取得価額期中減少'] - $month['建物附属設備累計額期中減少'];
    // 建物合計
    $total_jyo_cho_tate   = $tbody_jyo_cho_tate + $tbody_jyo_cho_fuzoku;
    // 構築物、機械装置、車輛運搬具、器具工具、什器備品
    $tbody_jyo_cho_kouti  = $month['構築物取得価額期中減少'] - $month['構築物累計額期中減少'];
    $tbody_jyo_cho_kikai  = $month['機械装置取得価額期中減少'] - $month['機械装置累計額期中減少'];
    $tbody_jyo_cho_sharyo = $month['車輛運搬具取得価額期中減少'] - $month['車輛運搬具累計額期中減少'];
    $tbody_jyo_cho_kigu   = $month['器具工具取得価額期中減少'] - $month['器具工具累計額期中減少'];
    $tbody_jyo_cho_jyuki  = $month['什器備品取得価額期中減少'] - $month['什器備品累計額期中減少'];
    // 器具工具、什器備品合計
    $total_jyo_cho_kougu  = $tbody_jyo_cho_kigu + $tbody_jyo_cho_jyuki;
    // リース資産
    $tbody_jyo_cho_lease  = $month['リース資産取得価額期中減少'] - $month['リース資産累計額期中減少'];
    // 有形合計
    $total_jyo_cho_yukei  = $total_jyo_cho_tate + $tbody_jyo_cho_kouti + $tbody_jyo_cho_kikai + 
                            $tbody_jyo_cho_sharyo + $total_jyo_cho_kougu + $tbody_jyo_cho_lease;
    // 電話加入権、施設利用権、ソフトウェア
    $tbody_jyo_cho_denwa  = $month['電話加入権取得価額期中減少'] - $month['電話加入権累計額期中減少'];
    $tbody_jyo_cho_shise  = $month['施設利用権取得価額期中減少'] - $month['施設利用権累計額期中減少'];
    $tbody_jyo_cho_soft   = $month['ソフトウェア取得価額期中減少'] - $month['ソフトウェア累計額期中減少'];
    // 無形合計
    $total_jyo_cho_mukei  = $tbody_jyo_cho_denwa + $tbody_jyo_cho_shise + $tbody_jyo_cho_soft;
    // 総合計
    $total_jyo_cho_all    = $total_jyo_cho_yukei + $total_jyo_cho_mukei;
    ///// 計算結果をハッシュへ代入
    $tbody['tbody_jyo_cho_tate']        = number_format($tbody_jyo_cho_tate, $keta);
    $tbody['tbody_jyo_cho_fuzoku']      = number_format($tbody_jyo_cho_fuzoku, $keta);
    $tbody['tbody_jyo_cho_tate_total']  = number_format($total_jyo_cho_tate, $keta);
    $tbody['tbody_jyo_cho_kouti']       = number_format($tbody_jyo_cho_kouti, $keta);
    $tbody['tbody_jyo_cho_kikai']       = number_format($tbody_jyo_cho_kikai, $keta);
    $tbody['tbody_jyo_cho_sharyo']      = number_format($tbody_jyo_cho_sharyo, $keta);
    $tbody['tbody_jyo_cho_kigu']        = number_format($tbody_jyo_cho_kigu, $keta);
    $tbody['tbody_jyo_cho_jyuki']       = number_format($tbody_jyo_cho_jyuki, $keta);
    $tbody['tbody_jyo_cho_kougu_total'] = number_format($total_jyo_cho_kougu, $keta);
    $tbody['tbody_jyo_cho_lease']       = number_format($tbody_jyo_cho_lease, $keta);
    $tbody['tbody_jyo_cho_yukei_total'] = number_format($total_jyo_cho_yukei, $keta);
    $tbody['tbody_jyo_cho_denwa']       = number_format($tbody_jyo_cho_denwa, $keta);
    $tbody['tbody_jyo_cho_shise']       = number_format($tbody_jyo_cho_shise, $keta);
    $tbody['tbody_jyo_cho_soft']        = number_format($tbody_jyo_cho_soft, $keta);
    $tbody['tbody_jyo_cho_mukei_total'] = number_format($total_jyo_cho_mukei, $keta);
    $tbody['tbody_jyo_cho_all']         = number_format($total_jyo_cho_all, $keta);
    /////////////////////////////////////////////////////////////////////// 除却資産等の帳簿価額 END
    
    /////////////////////////////////////////////////////////////////////// 期末帳簿残高 START
    ///// 各期末帳簿残高を計算
    // 建物、建物附属設備
    $tbody_kima_cho_tate   = $tbody_shutoku_kima_tate - $tbody_rui_kima_tate;
    $tbody_kima_cho_fuzoku = $tbody_shutoku_kima_fuzoku - $tbody_rui_kima_fuzoku;
    // 建物合計
    $total_kima_cho_tate   = $tbody_kima_cho_tate + $tbody_kima_cho_fuzoku;
    // 構築物、機械装置、車輛運搬具、器具工具、什器備品
    $tbody_kima_cho_kouti  = $tbody_shutoku_kima_kouti - $tbody_rui_kima_kouti;
    $tbody_kima_cho_kikai  = $tbody_shutoku_kima_kikai - $tbody_rui_kima_kikai;
    $tbody_kima_cho_sharyo = $tbody_shutoku_kima_sharyo - $tbody_rui_kima_sharyo;
    $tbody_kima_cho_kigu   = $tbody_shutoku_kima_kigu - $tbody_rui_kima_kigu;
    $tbody_kima_cho_jyuki  = $tbody_shutoku_kima_jyuki - $tbody_rui_kima_jyuki;
    // 器具工具、什器備品合計
    $total_kima_cho_kougu  = $tbody_kima_cho_kigu + $tbody_kima_cho_jyuki;
    // リース資産
    $tbody_kima_cho_lease  = $tbody_shutoku_kima_lease - $tbody_rui_kima_lease;
    // 有形合計
    $total_kima_cho_yukei  = $total_kima_cho_tate + $tbody_kima_cho_kouti + $tbody_kima_cho_kikai + 
                             $tbody_kima_cho_sharyo + $total_kima_cho_kougu + $tbody_kima_cho_lease;
    // 電話加入権、施設利用権、ソフトウェア
    $tbody_kima_cho_denwa  = $tbody_shutoku_kima_denwa - $tbody_rui_kima_denwa;
    $tbody_kima_cho_shise  = $tbody_shutoku_kima_shise - $tbody_rui_kima_shise;
    $tbody_kima_cho_soft   = $tbody_shutoku_kima_soft - $tbody_rui_kima_soft;
    // 無形合計
    $total_kima_cho_mukei  = $tbody_kima_cho_denwa + $tbody_kima_cho_shise + $tbody_kima_cho_soft;
    // 総合計
    $total_kima_cho_all    = $total_kima_cho_yukei + $total_kima_cho_mukei;
    ///// 計算結果をハッシュへ代入
    $tbody['tbody_kima_cho_tate']        = number_format($tbody_kima_cho_tate, $keta);
    $tbody['tbody_kima_cho_fuzoku']      = number_format($tbody_kima_cho_fuzoku, $keta);
    $tbody['tbody_kima_cho_tate_total']  = number_format($total_kima_cho_tate, $keta);
    $tbody['tbody_kima_cho_kouti']       = number_format($tbody_kima_cho_kouti, $keta);
    $tbody['tbody_kima_cho_kikai']       = number_format($tbody_kima_cho_kikai, $keta);
    $tbody['tbody_kima_cho_sharyo']      = number_format($tbody_kima_cho_sharyo, $keta);
    $tbody['tbody_kima_cho_kigu']        = number_format($tbody_kima_cho_kigu, $keta);
    $tbody['tbody_kima_cho_jyuki']       = number_format($tbody_kima_cho_jyuki, $keta);
    $tbody['tbody_kima_cho_kougu_total'] = number_format($total_kima_cho_kougu, $keta);
    $tbody['tbody_kima_cho_lease']       = number_format($tbody_kima_cho_lease, $keta);
    $tbody['tbody_kima_cho_yukei_total'] = number_format($total_kima_cho_yukei, $keta);
    $tbody['tbody_kima_cho_denwa']       = number_format($tbody_kima_cho_denwa, $keta);
    $tbody['tbody_kima_cho_shise']       = number_format($tbody_kima_cho_shise, $keta);
    $tbody['tbody_kima_cho_soft']        = number_format($tbody_kima_cho_soft, $keta);
    $tbody['tbody_kima_cho_mukei_total'] = number_format($total_kima_cho_mukei, $keta);
    $tbody['tbody_kima_cho_all']         = number_format($total_kima_cho_all, $keta);
    /////////////////////////////////////////////////////////////////////// 期末帳簿残高 END
/*
}
*/

/********** patTemplate 書出し ************/
include_once ( '../../../patTemplate/include/patTemplate.php' );
$tmpl = new patTemplate();

//  In diesem Verzeichnis liegen die Templates
$tmpl->setBasedir( 'templates' );

$tmpl->readTemplatesFromFile( 'shihanki_depreciation_statement_202001.templ.html' );

$tmpl->addVar('page', 'PAGE_TITLE'         , '減価償却資産および減価償却費の明細');
$tmpl->addVar('page', 'PAGE_MENU_SITE_URL' , $menu_site_script);
$tmpl->addVar('page', 'PAGE_UNIQUE'        , $uniq);
$tmpl->addVar('page', 'PAGE_RETURN_URL'    , $url_referer);
$tmpl->addVar('page', 'PAGE_CURRENT_URL'   , $current_script);
$tmpl->addVar('page', 'PAGE_SITE_VIEW'     , $site_view);
$tmpl->addVar('page', 'PAGE_HEADER_TITLE'  , "第{$ki}期 第{$hanki}四半期 減価償却資産および減価償却費の明細");
$tmpl->addVar('page', 'PAGE_HEADER_TODAY'  , $today);
$tmpl->addVar('page', 'OUT_CSS'            , $menu->out_css());
$tmpl->addVar('page', 'OUT_JSBASE'         , $menu->out_jsBaseClass());
$tmpl->addVar('page', 'OUT_TITLE_BORDER'   , $menu->out_title_border());

///// 表示単位をテンプレート変数への登録
if ($tani == 1) {
    $tmpl->addVar('page', 'en'       , 'selected');
    $tmpl->addVar('page', 'sen'      , '');
    $tmpl->addVar('page', 'jyuman'   , '');
    $tmpl->addVar('page', 'million'  , '');
} elseif ($tani == 1000) {
    $tmpl->addVar('page', 'en'       , '');
    $tmpl->addVar('page', 'sen'      , 'selected');
    $tmpl->addVar('page', 'jyuman'   , '');
    $tmpl->addVar('page', 'million'  , '');
} elseif ($tani == 100000) {
    $tmpl->addVar('page', 'en'       , '');
    $tmpl->addVar('page', 'sen'      , '');
    $tmpl->addVar('page', 'jyuman'   , 'selected');
    $tmpl->addVar('page', 'million'  , '');
} elseif ($tani == 1000000) {
    $tmpl->addVar('page', 'en'       , '');
    $tmpl->addVar('page', 'sen'      , '');
    $tmpl->addVar('page', 'jyuman'   , '');
    $tmpl->addVar('page', 'million'  , 'selected');
} else {
    $tmpl->addVar('page', 'en'       , '');
    $tmpl->addVar('page', 'sen'      , '');
    $tmpl->addVar('page', 'jyuman'   , '');
    $tmpl->addVar('page', 'million'  , 'selected');
}
///// 小数点以下の桁数
if ($keta == 0) {
    $tmpl->addVar('page', 'zero' , 'selected');
    $tmpl->addVar('page', 'ichi' , '');
    $tmpl->addVar('page', 'san'  , '');
    $tmpl->addVar('page', 'roku' , '');
} elseif ($keta == 1) {
    $tmpl->addVar('page', 'zero' , '');
    $tmpl->addVar('page', 'ichi' , 'selected');
    $tmpl->addVar('page', 'san'  , '');
    $tmpl->addVar('page', 'roku' , '');
} elseif ($keta == 3) {
    $tmpl->addVar('page', 'zero' , '');
    $tmpl->addVar('page', 'ichi' , '');
    $tmpl->addVar('page', 'san'  , 'selected');
    $tmpl->addVar('page', 'roku' , '');
} elseif ($keta == 6) {
    $tmpl->addVar('page', 'zero' , '');
    $tmpl->addVar('page', 'ichi' , '');
    $tmpl->addVar('page', 'san'  , '');
    $tmpl->addVar('page', 'roku' , 'selected');
} else {
    $tmpl->addVar('page', 'zero' , '');
    $tmpl->addVar('page', 'ichi' , 'selected');
    $tmpl->addVar('page', 'san'  , '');
    $tmpl->addVar('page', 'roku' , '');
}

///// ハッシュ配列で patTemplate に展開 カプラ・リニア・全体が tbody[]に代入されている
$tmpl->addVars('tbody', $tbody);

//$tmpl->addVars( 'tbody_rows', array('TBODY_DSP_NUM' => $dsp_num) );
//$tmpl->addVars( 'tbody_rows', array('TBODY_FIELD0'  => $field0) );
//$tmpl->addVars( 'tbody_rows', array('TBODY_FIELD1'  => $field1) );


/********** Logic End   **********/

/////////// HTML Header を出力してキャッシュを制御
$menu->out_html_header();

//  Alle Templates ausgeben
$tmpl->displayParsedTemplate();
/************* patTemplate 終了 *****************/

?>
