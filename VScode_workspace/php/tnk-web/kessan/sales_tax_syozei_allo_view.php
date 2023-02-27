<?php
//////////////////////////////////////////////////////////////////////////////
// 月次損益関係 消費税申告書 消費税等計算表                                 //
// Copyright(C) 2021-2021 Norihisa.Ohya norihisa_ooya@nitto-kohki.co.jp     //
// Changed history                                                          //
// 2021/04/23 Created   sales_tax_syozei_allo_view.php                      //
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
    $menu->set_title("第 {$ki} 期　本決算　消　費　税　等　計　算　表");
} else {
    $menu->set_title("第 {$ki} 期　第{$hanki}四半期　消　費　税　等　計　算　表");
}

$cost_ym = array();
$tuki_chk = substr($_SESSION['2ki_ym'],4,2);
if ($tuki_chk >= 1 && $tuki_chk <= 3) {           //第４四半期
    $hanki = '４';
    $yyyy_tou = $yyyy + 1;
    $cost_ym[0]  = $yyyy . '04';
    $cost_ym[1]  = $yyyy . '05';
    $cost_ym[2]  = $yyyy . '06';
    $cost_ym[3]  = $yyyy . '07';
    $cost_ym[4]  = $yyyy . '08';
    $cost_ym[5]  = $yyyy . '09';
    $cost_ym[6]  = $yyyy . '10';
    $cost_ym[7]  = $yyyy . '11';
    $cost_ym[8]  = $yyyy . '12';
    $cost_ym[9]  = $yyyy_tou . '01';
    $cost_ym[10] = $yyyy_tou . '02';
    $cost_ym[11] = $yyyy_tou . '03';
    $cnum        = 12;
} elseif ($tuki_chk >= 4 && $tuki_chk <= 6) {     //第１四半期
    $hanki = '１';
    $cost_ym[0]  = $yyyy . '04';
    $cost_ym[1]  = $yyyy . '05';
    $cost_ym[2]  = $yyyy . '06';
    $cnum        = 3;
} elseif ($tuki_chk >= 7 && $tuki_chk <= 9) {     //第２四半期
    $hanki = '２';
    $cost_ym[0] = $yyyy . '04';
    $cost_ym[1] = $yyyy . '05';
    $cost_ym[2] = $yyyy . '06';
    $cost_ym[3] = $yyyy . '07';
    $cost_ym[4]  = $yyyy . '08';
    $cost_ym[5]  = $yyyy . '09';
    $cnum        = 6;
} elseif ($tuki_chk >= 10) {    //第３四半期
    $hanki = '３';
    $cost_ym[0]  = $yyyy . '04';
    $cost_ym[1]  = $yyyy . '05';
    $cost_ym[2]  = $yyyy . '06';
    $cost_ym[3]  = $yyyy . '07';
    $cost_ym[4]  = $yyyy . '08';
    $cost_ym[5]  = $yyyy . '09';
    $cost_ym[6]  = $yyyy . '10';
    $cost_ym[7]  = $yyyy . '11';
    $cost_ym[8]  = $yyyy . '12';
    $cnum        = 9;
}

// 翌期4月分
$cost_ym_next = $yyyy + 1 . '04';

// 日東工器譲受資産関係
if ($nk_ki == 65) {
    $nk_kotei             = 76600469;
    $nk_kotei_kei         = 598519;
    $nk_kotei_zei         = floor($nk_kotei * 0.1*pow(10,0))/pow(10,0);
    $nk_kotei_kei_zei     = floor($nk_kotei_kei * 0.1*pow(10,0))/pow(10,0);
    $nk_kotei_zei_edp     = 7660047;
    $nk_kotei_kei_zei_edp = 59852;
}

// 別メニューで作成したデータの取得

///////////// データ取得順により 右側の表からデータ取得
///////////// 未払・取引先別消費税額計算表 合計金額を取得
// query部は共用
$query = "select
                SUM(rep_kin) as t_kin
          from
                sales_tax_calculate_list";

// 月毎の合計金額を取得
$t_kou8_kin   = 0;     // 税抜購入(軽8％)
$t_kou10_kin  = 0;     // 税抜購入(10％)
$t_sumi10_kin = 0;     // 税金計上済(10％)
$t_zeigai_kin = 0;     // 課税対象外
$t_kari10_kin = 0;     // 仮払消費税(10％)
$t_jido8_kin  = 0;     // 自動計算額(軽8％)
$t_jido10_kin = 0;     // 自動計算額(10％)

// 税金計上済(10％)
for ($r=0; $r<$cnum; $r++) {
    // 日付の設定
    $d_ym = $cost_ym[$r];
    $search = "where rep_ki=$nk_ki and rep_ymd=$d_ym and rep_code='3333'";
    $query_s = sprintf("$query %s", $search);     // SQL query 文の完成
    $res_sum = array();
    if ($rows=getResult($query_s, $res_sum) <= 0) {
        $m_sumi10_kin[$r] = 0;
    } else {
        $m_sumi10_kin[$r] = $res_sum[0][0];
        $t_sumi10_kin += $m_sumi10_kin[$r];
    }
}

// 課税対象外
for ($r=0; $r<$cnum; $r++) {
    // 日付の設定
    $d_ym = $cost_ym[$r];
    $search = "where rep_ki=$nk_ki and rep_ymd=$d_ym and rep_kubun='X'";
    $query_s = sprintf("$query %s", $search);     // SQL query 文の完成
    $res_sum = array();
    if ($rows=getResult($query_s, $res_sum) <= 0) {
        $m_zeigai_kin[$r] = 0;
    } else {
        $m_zeigai_kin[$r] = $res_sum[0][0];
        $t_zeigai_kin += $m_zeigai_kin[$r];
    }
}

// 仮払消費税(10％)
for ($r=0; $r<$cnum; $r++) {
    // 日付の設定
    $d_ym = $cost_ym[$r];
    $search = "where rep_ki=$nk_ki and rep_ymd=$d_ym and rep_kubun='3'";
    $query_s = sprintf("$query %s", $search);     // SQL query 文の完成
    $res_sum = array();
    if ($rows=getResult($query_s, $res_sum) <= 0) {
        $m_kari10_kin[$r] = 0;
    } else {
        $m_kari10_kin[$r] = $res_sum[0][0];
        $t_kari10_kin += $m_kari10_kin[$r];
    }
}

// 税抜購入(軽8％)
for ($r=0; $r<$cnum; $r++) {
    // 日付の設定
    $d_ym = $cost_ym[$r];
    $search = "where rep_ki=$nk_ki and rep_ymd=$d_ym and rep_code='A108'";
    $query_s = sprintf("$query %s", $search);     // SQL query 文の完成
    $res_sum = array();
    if ($rows=getResult($query_s, $res_sum) <= 0) {
        $m_kou8_kin[$r] = 0;
        $m_jido8_kin[$r] = 0;
    } else {
        $m_kou8_kin[$r]  = $res_sum[0][0];
        $m_jido8_kin[$r] = round($m_kou8_kin[$r] * 0.08, 0);
        $t_kou8_kin     += $m_kou8_kin[$r];
        $t_jido8_kin    += $m_jido8_kin[$r];
    }
}

// 税抜購入(10％)
for ($r=0; $r<$cnum; $r++) {
    // 日付の設定
    $d_ym = $cost_ym[$r];
    $search = "where rep_ki=$nk_ki and rep_ymd=$d_ym";
    $query_s = sprintf("$query %s", $search);     // SQL query 文の完成
    $res_sum = array();
    if ($rows=getResult($query_s, $res_sum) <= 0) {
        $m_kou10_kin[$r] = 0 - $m_kari10_kin[$r] - $m_kou8_kin[$r] - $m_sumi10_kin[$r] - $m_zeigai_kin[$r];
    } else {
        $m_kou10_kin[$r] = $res_sum[0][0] - $m_kari10_kin[$r] - $m_kou8_kin[$r] - $m_sumi10_kin[$r] - $m_zeigai_kin[$r];
        $t_kou10_kin += $m_kou10_kin[$r];
    }
}


///////////// 未払金支払明細表 合計金額を取得
// query部は共用
$query = "select
                SUM(rep_buy) as t_buy,
                SUM(rep_tax) as t_tax
          from
                sales_tax_payment_list";

// 月毎の切粉の合計金額を取得
$t_buy_kin = 0;
$t_tax_kin = 0;
for ($r=0; $r<$cnum; $r++) {
    // 日付の設定
    $d_ym = $cost_ym[$r];
    $search = "where rep_ki=$nk_ki and rep_ymd=$d_ym";
    $query_s = sprintf("$query %s", $search);     // SQL query 文の完成
    $res_sum = array();
    if ($rows=getResult($query_s, $res_sum) <= 0) {
        $m_buy_kin[$r]    = 0 - $m_kou8_kin[$r];
        $m_tax_kin[$r]    = 0 - $m_jido8_kin[$r];
        $m_jido10_kin[$r] = 0 - $m_tax_kin[$r] - $m_kari10_kin[$r];
    } else {
        $m_buy_kin[$r] = $res_sum[0][0] - $m_kou8_kin[$r];
        $t_buy_kin += $m_buy_kin[$r];
        $m_tax_kin[$r] = $res_sum[0][1] - $m_jido8_kin[$r];
        $m_jido10_kin[$r] = $m_tax_kin[$r] - $m_kari10_kin[$r];
        $t_tax_kin += $m_tax_kin[$r];
        $t_jido10_kin += $m_jido10_kin[$r];
    }
}

///////// 項目とインデックスの関連付け
$gitem = array();
$gitem[0]   = "当月発生購入額軽8";
$gitem[1]   = "当月発生消費税額軽8";
$gitem[2]   = "当月発生購入額10";
$gitem[3]   = "当月発生消費税額10";
$gitem[4]   = "未払伝票税抜購入軽8";
$gitem[5]   = "未払伝票税抜購入10";
$gitem[6]   = "未払伝票税金計上済10";
$gitem[7]   = "未払伝票課税対象外";
$gitem[8]   = "未払伝票仮払消費税10";
$gitem[9]   = "仮払消費税自動計算額軽8";
$gitem[10]  = "仮払消費税自動計算額10"; 
$gitem[11]  = "仮払消費税等輸入"; 
$gitem[12]  = "未払消費税等中間納付"; 
$gitem[13]  = $cost_ym_next . "中間納付税額"; 
$gitem[14]  = "仮払消費税等"; 
///////// 各データの保管
$view_data = array();

$num_input = count($gitem);
for ($i = 0; $i < $num_input; $i++) {
    $query = sprintf("select rep_kin from sales_tax_create_data where rep_ki=%d and rep_note='%s'", $nk_ki, $gitem[$i]);
    $res_in = array();
    if (getResult2($query,$res_in) <= 0) {
        /////////// begin トランザクション開始
        if ($con = db_connect()) {
            query_affected_trans($con, "begin");
        } else {
            $_SESSION["s_sysmsg"] .= "データベースに接続できません";
            exit();
        }
        /////////// commit トランザクション終了
        query_affected_trans($con, "commit");
        $view_data[0][$i] = 0;
    } else {
        /////////// begin トランザクション開始
        if ($con = db_connect()) {
            query_affected_trans($con, "begin");
        } else {
            $_SESSION["s_sysmsg"] .= "データベースに接続できません";
            exit();
        }
        /////////// commit トランザクション終了
        query_affected_trans($con, "commit");
        $view_data[0][$i] = $res_in[0][0];
    }
}

// 未払金消費税計算(切り捨て）
$miha_siire_zei10  = floor($view_data[0][5] * 0.1*pow(10,0))/pow(10,0);
$miha_siire_zei8k  = floor($view_data[0][4] * 0.08*pow(10,0))/pow(10,0);
$miha_siire_zei10d = floor($view_data[0][6] * 0.1*pow(10,0))/pow(10,0);

// テスト あとで削除
$view_data[0][13] = 9019600;
// （中間納付税額）計算
$view_data[0][13] = $view_data[0][12] + $view_data[0][13];

// 21期のみ特別
if ($nk_ki==65) {
    //$miha_siire_zei10d = $miha_siire_zei10d + 15344000;
}

/// 買掛データ取得
$str_ymd = $str_ym . '00';
$end_ymd = $end_ym . '99';
$query = sprintf("SELECT SUM(round(order_price*siharai,0)) FROM act_payable WHERE act_date>=%d and act_date<=%d and vendor<>'00222' and vendor<>'01111' and vendor<>'00948' and vendor<>'05001' and vendor<>'99999' and (vendor <'03000' or vendor> '03999') ", $str_ymd, $end_ymd);
$res_kai = array();
$kai_siire = 0;
if (getResult2($query,$res_kai) <= 0) {
    $kai_siire = 0;    
} else {
    $kai_siire = $res_kai[0][0];
}
// 消費税計算(切り捨て）
$kai_siire_zei = floor($kai_siire * 0.1*pow(10,0))/pow(10,0);

/// 仕訳データ取得 8% BK
$query = sprintf("SELECT SUM(rep_kin) - SUM(ROUND(rep_kin/1.08)) as kin FROM sales_tax_koujyo_siwake WHERE rep_ki=%d and rep_kamoku > '7501' and rep_kamoku <= '8123' and rep_kubun='BK'", $nk_ki);
$res_siwa8bk = array();
$siwa8bk_siire = 0;
if (getResult2($query,$res_siwa8bk) <= 0) {
    $siwa8bk_siire = 0;    
} else {
    $siwa8bk_siire = $res_siwa8bk[0][0];
}

/// 仕訳データ取得 8% ZK
$query = sprintf("SELECT SUM(ROUND(rep_kin*1.08)) - SUM(rep_kin) FROM sales_tax_koujyo_siwake WHERE rep_ki=%d and rep_kamoku > '7501' and rep_kamoku <= '8123' and rep_kubun='ZK' and rep_teki='A008'", $nk_ki);
$res_siwa8zk = array();
$siwa8zk_siire = 0;
if (getResult2($query,$res_siwa8zk) <= 0) {
    $siwa8zk_siire = 0;    
} else {
    $siwa8zk_siire = $res_siwa8zk[0][0];
}
// 消費税計算(四捨五入）
$siwa8_siire     = $siwa8bk_siire + $siwa8zk_siire;
$siwa8_siire_zei = floor($siwa8_siire / 0.08*pow(10,0))/pow(10,0);

/// 仕訳データ取得 8%軽 ブランクでA108
$query = sprintf("SELECT SUM(rep_kin) as kin FROM sales_tax_koujyo_siwake WHERE rep_ki=%d and rep_kubun='' and rep_teki='A108'", $nk_ki);
$res_siwa8d = array();
$siwa8d_siire = 0;
if (getResult2($query,$res_siwa8d) <= 0) {
    $siwa8d_siire = 0;    
} else {
    $siwa8d_siire = $res_siwa8d[0][0];
}
// 消費税計算(四捨五入）
$siwa8d_siire_zei = round($siwa8d_siire / 0.08,0);

//⑨ 消費税10％計 計算
$syo10_9_total = $view_data[0][14] + $view_data[0][11] + $view_data[0][12] - $siwa8_siire - $siwa8d_siire;

//⑩ 仕訳伝票仕入高 消費税10％ 計算
$siwa10_siire = $syo10_9_total - $kai_siire_zei - $miha_siire_zei10 - $miha_siire_zei10d - $view_data[0][11] - $nk_kotei_zei - $nk_kotei_kei_zei - $view_data[0][12];

// 税抜き金額計算
$siwa10_siire_zei = round($siwa10_siire / 0.1,0);

//⑯ 税抜金額計 計算
$zeinuki_16_total = $kai_siire + $view_data[0][5] + $view_data[0][4] + $view_data[0][6] + $siwa10_siire_zei + $siwa8d_siire_zei + $siwa8_siire_zei + $nk_kotei + $nk_kotei_kei;

//⑨ 消費税軽８％計 計算
$syo8_kei_total = $siwa8d_siire + $miha_siire_zei8k;

//イ EDP NK譲受資産
$edp_nk_kotei = $kai_siire_zei + $view_data[0][10] + $view_data[0][9] + $view_data[0][8] + $siwa10_siire + $siwa8d_siire + $siwa8_siire;

//EDP消費税計上額 計 計算
$edp_syozei_kotei = $edp_nk_kotei + $view_data[0][11] + $nk_kotei_zei_edp + $nk_kotei_kei_zei_edp + $view_data[0][12];

//税抜金額（課税対象）合計金額
// ⑱10％
$zeinuki_kazei_kei10 = $kai_siire + $view_data[0][5] + $view_data[0][6] + $siwa10_siire_zei + $nk_kotei + $nk_kotei_kei;
// ⑲8％軽
$zeinuki_kazei_kei8d = $view_data[0][4] + $siwa8d_siire_zei;
// ⑳8％
$zeinuki_kazei_kei8  = $siwa8_siire_zei;

// 調整計算
// 買掛金計算額差異
$kai_siire_sai = 0;

// 未払金消費税調整
// 10% ④＋⑥-A-C
$miha_zei_sai10 = $miha_siire_zei10 + $miha_siire_zei10d - $view_data[0][10] - $view_data[0][8];
// 8%軽 ④-B
$miha_zei_sai8d = $miha_siire_zei8k - $view_data[0][9];

// 未払金消費税調整
// 固定資産調整 (d - 横）＋（e - 横）
$kotei_cho_sai = ($nk_kotei_zei - $nk_kotei_zei_edp) + ($nk_kotei_kei_zei - $nk_kotei_kei_zei_edp);

// 総計計算
// 10％
$zeinuki_total_10 = $zeinuki_kazei_kei10;    // 10% 税抜金額（課税対象）
$zei10_total_10   = $syo10_9_total;          // 10% 消費税１０％ 本来は⑨と調整関係の合計
$edp_total_10     = $kai_siire_zei + $view_data[0][10] + $view_data[0][8] + $siwa10_siire + $view_data[0][11] + $nk_kotei_zei_edp + $nk_kotei_kei_zei_edp + $view_data[0][12] + $miha_zei_sai10 + $kotei_cho_sai; // 10% ＥＤＰ消費税計上額
$zei4_total_10    = floor($zeinuki_total_10 * 0.078*pow(10,0))/pow(10,0); // 10% 消費税４％ 10% 税抜金額（課税対象）の0.078倍 切り捨て
$zeikomi_total_10 = floor($zeinuki_total_10 * 1.1*pow(10,0))/pow(10,0); // 10% 税込金額 10% 税抜金額（課税対象）の1.1倍 切り捨て

// 8％軽
$zeinuki_total_8d = $zeinuki_kazei_kei8d; // 8%軽 税抜金額（課税対象）
$zei8d_total_8d   = $syo8_kei_total;      // 8%軽 消費税軽８％ 本来は⑨と調整関係の合計
$edp_total_8d     = $view_data[0][9] + $siwa8d_siire + $miha_zei_sai8d; // 8%軽 ＥＤＰ消費税計上額
$zei4_total_8d    = floor($zeinuki_total_8d * 0.0624*pow(10,0))/pow(10,0); // 8%軽 消費税４％ 8%軽 税抜金額（課税対象）の0.0624倍 切り捨て
$zeikomi_total_8d = floor($zeinuki_total_8d * 1.08*pow(10,0))/pow(10,0); // 8%軽 税込金額 8%軽 税抜金額（課税対象）の1.08倍 切り捨て

// 8％
$zeinuki_total_8  = $siwa8_siire_zei;   // 8% 税抜金額（課税対象） 本来は⑳と調整関係の合計
$zei8_total_8     = $siwa8_siire;       // 8% 消費税８％ 本来は⑨と調整関係の合計
$edp_total_8      = $siwa8_siire;       // 8% ＥＤＰ消費税計上額
$zei4_total_8     = floor($zeinuki_total_8 * 0.063*pow(10,0))/pow(10,0); // 8% 消費税４％ 8% 税抜金額（課税対象）の0.063倍 切り捨て
$zeikomi_total_8  = floor($zeinuki_total_8 * 1.08*pow(10,0))/pow(10,0); // 8% 税込金額 8% 税抜金額（課税対象）の1.08倍 切り捨て

// 総合計計算
$zeinuki_total_all = $zeinuki_total_10 + $zeinuki_total_8d + $zeinuki_total_8;
$zei8_total_all    = $zei8_total_8;
$zei8d_total_all   = $zei8d_total_8d;
$zei10_total_all   = $zei10_total_10;
$edp_total_all     = $edp_total_10 + $edp_total_8d + $edp_total_8;
$zei4_total_all    = $zei4_total_10 + $zei4_total_8d + $zei4_total_8;
$zeikomi_total_all = $zeikomi_total_10 + $zeikomi_total_8d + $zeikomi_total_8;


if (isset($_POST['input_data'])) {                        // 当月データの登録
    ///////// 項目とインデックスの関連付け
    ///////// 項目とインデックスの関連付け
    $item = array();
    $item[0]   = "控除税額税抜金額課税10";
    $item[1]   = "控除税額税抜金額課税軽8";
    $item[2]   = "控除税額税抜金額課税8";
    $item[3]   = "控除税額税抜金額課税合計";
    $item[4]   = "控除税額税抜金額税8";
    $item[5]   = "控除税額税抜金額税8合計";
    $item[6]   = "控除税額税抜金額税軽8";
    $item[7]   = "控除税額税抜金額税軽8合計";
    $item[8]   = "控除税額税抜金額税10";
    $item[9]   = "控除税額税抜金額税10合計";
    $item[10]  = "控除税額税抜金額EDP10"; 
    $item[11]  = "控除税額税抜金額EDP軽8"; 
    $item[12]  = "控除税額税抜金額EDP8"; 
    $item[13]  = "控除税額税抜金額EDP合計"; 
    $item[14]  = "控除税額税抜金額税410";
    $item[15]  = "控除税額税抜金額税4軽8";
    $item[16]  = "控除税額税抜金額税48";
    $item[17]  = "控除税額税抜金額税4合計";
    $item[18]  = "控除税額税抜金額税込10";
    $item[19]  = "控除税額税抜金額税込軽8";
    $item[20]  = "控除税額税抜金額税込8";
    $item[21]  = "控除税額税抜金額税込合計";
    ///////// 各データの保管
    $input_data = array();
    $input_data[0]   = $zeinuki_total_10;
    $input_data[1]   = $zeinuki_total_8d;
    $input_data[2]   = $zeinuki_total_8;
    $input_data[3]   = $zeinuki_total_all;
    $input_data[4]   = $zei8_total_8;
    $input_data[5]   = $zei8_total_all;
    $input_data[6]   = $zei8d_total_8d;
    $input_data[7]   = $zei8d_total_all;
    $input_data[8]   = $zei10_total_10;
    $input_data[9]   = $zei10_total_all;
    $input_data[10]  = $edp_total_10;
    $input_data[11]  = $edp_total_8d;
    $input_data[12]  = $edp_total_8;
    $input_data[13]  = $edp_total_all;
    $input_data[14]  = $zei4_total_10;
    $input_data[15]  = $zei4_total_8d;
    $input_data[16]  = $zei4_total_8;
    $input_data[17]  = $zei4_total_all;
    $input_data[18]  = $zeikomi_total_10;
    $input_data[19]  = $zeikomi_total_8d;
    $input_data[20]  = $zeikomi_total_8;
    $input_data[21]  = $zeikomi_total_all;
    ///////// 各データの登録
    //insert_date($item,$nk_ki,$input_data);
}

function insert_date($item,$nk_ki,$input_data) 
{
    $num_input = count($input_data);
    for ($i = 0; $i < $num_input; $i++) {
        $query = sprintf("select rep_kin from sales_tax_create_data where rep_ki=%d and rep_note='%s'", $nk_ki, $item[$i]);
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
            $query = sprintf("insert into sales_tax_create_data (rep_ki, rep_kin, rep_note, last_date, last_user) values (%d, %d, '%s', CURRENT_TIMESTAMP, '%s')", $nk_ki, $input_data[$i], $item[$i], $_SESSION['User_ID']);
            if (query_affected_trans($con, $query) <= 0) {
                $_SESSION["s_sysmsg"] .= sprintf("%sの新規登録に失敗<br> %d", $item[$i], $yyyymm);
                query_affected_trans($con, "rollback");     // transaction rollback
                exit();
            }
            /////////// commit トランザクション終了
            query_affected_trans($con, "commit");
            $_SESSION["s_sysmsg"] = sprintf("<font color='yellow'>%d 消費税等計算表データ 新規 登録完了</font>",$yyyymm);
        } else {
            /////////// begin トランザクション開始
            if ($con = db_connect()) {
                query_affected_trans($con, "begin");
            } else {
                $_SESSION["s_sysmsg"] .= "データベースに接続できません";
                exit();
            }
            ////////// UPDATE Start
            $query = sprintf("update sales_tax_create_data set rep_kin=%d, last_date=CURRENT_TIMESTAMP, last_user='%s' where rep_ki=%d and rep_note='%s'", $input_data[$i], $_SESSION['User_ID'], $nk_ki, $item[$i]);
            if (query_affected_trans($con, $query) <= 0) {
                $_SESSION["s_sysmsg"] .= sprintf("%sのUPDATEに失敗<br> %d", $item[$i], $yyyymm);
                query_affected_trans($con, "rollback");     // transaction rollback
                exit();
            }
            /////////// commit トランザクション終了
            query_affected_trans($con, "commit");
            $_SESSION["s_sysmsg"] = sprintf("<font color='yellow'>%d 消費税等計算表データ 変更 完了</font>",$yyyymm);
        }
    }
    $_SESSION["s_sysmsg"] .= "消費税等計算表のデータを登録しました。";
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
<?= $menu->out_title_border() ?>
        <?php
            //  bgcolor='#ceffce' 黄緑
            //  bgcolor='#ffffc6' 薄い黄色
            //  bgcolor='#d6d3ce' Win グレイ
        ?>
        <!--------------- ここから本文の表を表示する -------------------->
        <BR><BR>
        <left>
        １．課税標準額計算表
        </left>
        <center>
        <table bgcolor='#d6d3ce' align='center' border='1' cellspacing='0' cellpadding='3'>
            <tr><td> <!----------- ダミー(デザイン用) ------------>
        <table class='winbox_field' width='100%' bgcolor='#d6d3ce' align='center' border='1' cellspacing='0' cellpadding='3'>
            <thead>
                <!-- テーブル ヘッダーの表示 -->
                <tr>
                    <th class='winbox' nowrap>内訳</th>
                    <th class='winbox' nowrap>税込金額</th>
                    <th class='winbox' nowrap>税抜金額</th>
                    <th class='winbox' nowrap>消費税５％</th>
                    <th class='winbox' nowrap>消費税８％</th>
                    <th class='winbox' nowrap>消費税１０％</th>
                    <th class='winbox' nowrap>EDP消費税計上額</th>
                    <th class='winbox' nowrap>備考</th>
                </tr>
            </thead>
            <tfoot>
                <!-- 現在はフッターは何もない -->
            </tfoot>
            <tbody>
            <?php
            // ＥＤＰ買掛金計上仕入額 2行目表示なし
            echo "<tr>\n";
            // 税込金額
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'><div class='pt10b'>ＥＤＰ総売上高</div></span></td>\n";
            // 税抜金額（課税対象）
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>　</span></td>\n";
            // 消費税８％
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>" . number_format(5072504131) . "</span></td>\n";
            // 消費税軽８％
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>　</span></td>\n";
            // 消費税１０％
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>　</span></td>\n";
            // 消費税４％
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>　</span></td>\n";
            // 税込金額
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>　</span></td>\n";
            // 備考
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>　</span></td>\n";
            echo "</tr>\n";
            
            // ＥＤＰ買掛金計上仕入額 1行目表示なし
            echo "<tr>\n";
            // タイトル
            echo "<td class='winbox' nowrap bgcolor='white' align='center'><div class='pt10b'>　</div></td>\n";
            // 税込金額
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>　</span></td>\n";
            // 消費税軽８％
            echo "  <th class='winbox' nowrap align='right'<span class='pt9'>　</span></td>\n";
            // 消費税１０％
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>　</span></td>\n";
            // EDP消費税計上額
            echo "  <th class='winbox' nowrap align='right'<span class='pt9'>　</span></td>\n";
            // 消費税４％
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>　</span></td>\n";
            // 税込金額
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>　</span></td>\n";
            // 備考
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>　</span></td>\n";
            echo "</tr>\n";
            
            // ＥＤＰ買掛金計上仕入額 3行目数字あり
            echo "<tr>\n";
            // 税込金額
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'><div class='pt10b'>上記の内免税売上高</div></span></td>\n";
            // 税抜金額（課税対象）
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>　</span></td>\n";
            // 税抜金額（課税対象）
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>" . number_format(71276267) . "</span></td>\n";
            // 税抜金額（課税対象）
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>　</span></td>\n";
            // 消費税８％
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>　</span></td>\n";
            // 消費税軽８％
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>　</span></td>\n";
            // 消費税１０％
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>　</span></td>\n";
            // 消費税１０％
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>　</span></td>\n";
            echo "</tr>\n";
            
            // ＥＤＰ未払金計上仕入額 1行目
            echo "<tr>\n";
            // タイトル
            echo "<td class='winbox' nowrap bgcolor='white' align='center'><div class='pt10b'>　</div></td>\n";
            // 税込金額
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>　</span></td>\n";
            // 税抜金額（課税対象）
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>　</span></td>\n";
            // 税抜金額（課税対象）
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>　</span></td>\n";
            // 税抜金額（課税対象）
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>　</span></td>\n";
            // 消費税８％
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>　</span></td>\n";
            // 消費税軽８％
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>　</span></td>\n";
            // 消費税４％
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>　</span></td>\n";
            echo "</tr>\n";
            
            // ＥＤＰ未払金計上仕入額 2行目
            echo "<tr>\n";
            // タイトル
            echo "<td class='winbox' nowrap bgcolor='white' align='center'><div class='pt10b'>① 差引課税売上高</div></td>\n";
            // 税抜金額（課税対象）
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>　</span></td>\n";
            // 税抜金額（課税対象）
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>" . number_format(5001227864) . "</span></td>\n";
            // 税抜金額（課税対象）
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>　</span></td>\n";
            // 消費税８％
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>　</span></td>\n";
            // 消費税軽８％
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>" . number_format(500122786) . "</span></td>\n";
            // 消費税軽８％
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>" . number_format(500122786) . "</span></td>\n";
            // 消費税１０％
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>" . number_format(0) . "</span></td>\n";
            echo "</tr>\n";
            
            // ＥＤＰ未払金計上仕入額 3行目
            echo "<tr>\n";
            // タイトル
            echo "<td class='winbox' nowrap bgcolor='white' align='center'><div class='pt10b'>② 有償支給材売上高</div></td>\n";
            // 税抜金額（課税対象）
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>　</span></td>\n";
            // 税抜金額（課税対象）
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>" . number_format(51148811) . "</span></td>\n";
            // 税抜金額（課税対象）
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>　</span></td>\n";
            // 消費税８％
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>　</span></td>\n";
            // 消費税軽８％
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>" . number_format(5114881) . "</span></td>\n";
            // 消費税１０％
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>" . number_format(5114656) . "</span></td>\n";
            // 消費税１０％
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>" . number_format(-225) . "</span></td>\n";
            echo "</tr>\n";
            
            // 仕訳伝票仕入高 1行目
            echo "<tr>\n";
            // タイトル
            echo "<td class='winbox' nowrap bgcolor='white' align='center'><div class='pt10b'>③ 切粉売却代</div></td>\n";
            // 税込金額
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>　</span></td>\n";
            // 税抜金額（課税対象）
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>" . number_format(93350079) . "</span></td>\n";
            // 税抜金額（課税対象）
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>　</span></td>\n";
            // 税抜金額（課税対象）
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>　</span></td>\n";
            // 消費税８％
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>" . number_format(9335008) . "</span></td>\n";
            // 消費税軽８％
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>" . number_format(9335010) . "</span></td>\n";
            // 消費税１０％
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>" . number_format(2) . "</span></td>\n";
            echo "</tr>\n";
            
            // 仕訳伝票仕入高 2行目
            echo "<tr>\n";
            // タイトル
            echo "<td class='winbox' nowrap bgcolor='white' align='center'><div class='pt10b'>④ 固定資産売却代(取引先)</div></td>\n";
            // 税抜金額（課税対象）
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>　</span></td>\n";
            // 税抜金額（課税対象）
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>" . number_format(0) . "</span></td>\n";
            // 税抜金額（課税対象）
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>　</span></td>\n";
            // 消費税８％
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>" . number_format(0) . "</span></td>\n";
            // 消費税軽８％
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>" . number_format(0) . "</span></td>\n";
            // 消費税軽８％
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>" . number_format(0) . "</span></td>\n";
            // 消費税１０％
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>" . number_format(0) . "</span></td>\n";
            echo "</tr>\n";
            
            // 仕訳伝票仕入高 3行目
            echo "<tr>\n";
            // タイトル
            echo "<td class='winbox' nowrap bgcolor='white' align='center'><div class='pt10b'>⑤ 栃木日東工器</div></td>\n";
            // 税抜金額（課税対象）
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>　</span></td>\n";
            // 税抜金額（課税対象）
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>　</span></td>\n";
            // 税抜金額（課税対象）
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>　</span></td>\n";
            // 消費税８％
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>　</span></td>\n";
            // 消費税８％
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>　</span></td>\n";
            // 消費税軽８％
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>　</span></td>\n";
            // EDP消費税計上額
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>　</span></td>\n";
            echo "</tr>\n";
            
            // 仕入割引
            echo "<tr>\n";
            // タイトル
            echo "<td class='winbox' nowrap bgcolor='white' align='center'><div class='pt10b'>固定資産売却代(NK)</div></td>\n";
            // 税込金額
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>　</span></td>\n";
            // 税抜金額（課税対象）
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>" . number_format(0) . "</span></td>\n";
            // 税抜金額（課税対象）
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>　</span></td>\n";
            // 消費税８％
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>" . number_format(0) . "</span></td>\n";
            // 消費税８％
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>　</span></td>\n";
            // 消費税軽８％
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>" . number_format(0) . "</span></td>\n";
            // 消費税１０％
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>　</span></td>\n";
            echo "</tr>\n";
            
            // 輸入取引に係る消費税等
            echo "<tr>\n";
            // タイトル
            echo "<td class='winbox' nowrap bgcolor='white' align='center'><div class='pt10b'>固定資産売却代(SNK)</div></td>\n";
            // 税込金額
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>　</span></td>\n";
            // 税抜金額（課税対象）
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>" . number_format(350238) . "</span></td>\n";
            // 消費税８％
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>　</span></td>\n";
            // 消費税軽８％
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>　</span></td>\n";
            // 消費税１０％
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>" . number_format(35024) . "</span></td>\n";
            // 消費税１０％
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>" . number_format(35024) . "</span></td>\n";
            // EDP消費税計上額
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>　</span></td>\n";
            echo "</tr>\n";
            
            // 日東工器譲受資産関係
            echo "<tr>\n";
            // タイトル
            echo "<td class='winbox' nowrap bgcolor='white' align='center'><div class='pt10b'>その他</div></td>\n";
            // 税込金額
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>　</span></td>\n";
            // 税抜金額（課税対象）
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>" . number_format(0) . "</span></td>\n";
            // 消費税８％
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>　</span></td>\n";
            // 消費税軽８％
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>" . number_format(0) . "</span></td>\n";
            // 消費税１０％
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>" . number_format(0) . "</span></td>\n";
            // EDP消費税計上額
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>" . number_format(0) . "</span></td>\n";
            // 税込金額
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>　</span></td>\n";
            echo "</tr>\n";
            
            // 固定資産
            echo "<tr>\n";
            // タイトル
            echo "<td class='winbox' nowrap bgcolor='white' align='center'><div class='pt10b'>　</div></td>\n";
            // 税込金額
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>　</span></td>\n";
            // 税抜金額（課税対象）
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>　</span></td>\n";
            // 税抜金額（課税対象）
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>　</span></td>\n";
            // 税抜金額（課税対象）
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>　</span></td>\n";
            // 消費税８％
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>　</span></td>\n";
            // 消費税８％
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>　</span></td>\n";
            // 消費税軽８％
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>　</span></td>\n";
            echo "</tr>\n";
            
            // 固定資産経費分
            echo "<tr>\n";
            // タイトル
            echo "<td class='winbox' nowrap bgcolor='white' align='center'><div class='pt10b'>⑥ 雑収入</div></td>\n";
            // 税込金額
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>　</span></td>\n";
            // 税抜金額（課税対象）
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>" . number_format(13357105) . "</span></td>\n";
            // 税抜金額（課税対象）
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>　</span></td>\n";
            // 税抜金額（課税対象）
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>　</span></td>\n";
            // 消費税８％
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>" . number_format(1335711) . "</span></td>\n";
            // 消費税８％
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>" . number_format(1335722) . "</span></td>\n";
            // 消費税軽８％
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>" . number_format(12) . "</span></td>\n";
            echo "</tr>\n";
            
            // 棚卸資産
            echo "<tr>\n";
            // タイトル
            echo "<td class='winbox' nowrap bgcolor='white' align='center'><div class='pt10b'>　</div></td>\n";
            // 税込金額
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>　</span></td>\n";
            // 税抜金額（課税対象）
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>　</span></td>\n";
            // 税抜金額（課税対象）
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>　</span></td>\n";
            // 税抜金額（課税対象）
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>　</span></td>\n";
            // 消費税８％
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>　</span></td>\n";
            // 消費税８％
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>　</span></td>\n";
            // 消費税軽８％
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>　</span></td>\n";
            echo "</tr>\n";
            
            // 中間納付計上額
            echo "<tr>\n";
            // タイトル
            echo "<td class='winbox' nowrap bgcolor='white' align='center'><div class='pt10b'>　</div></td>\n";
            // 税込金額
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>　</span></td>\n";
            // 税抜金額（課税対象）
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>　</span></td>\n";
            // 消費税８％
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>　</span></td>\n";
            // 消費税軽８％
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>　</span></td>\n";
            // 消費税１０％
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>　</span></td>\n";
            // 消費税１０％
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>　</span></td>\n";
            // EDP消費税計上額
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>　</span></td>\n";
            echo "</tr>\n";
            
            // （中間納付額）
            echo "<tr>\n";
            // タイトル
            echo "<td class='winbox' nowrap bgcolor='white' align='center'><div class='pt10b'>　</div></td>\n";
            // 税込金額
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>　</span></td>\n";
            // 税抜金額（課税対象）
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>　</span></td>\n";
            // 消費税８％
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>　</span></td>\n";
            // 消費税軽８％
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>調整</span></td>\n";
            // 消費税１０％
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>調整</span></td>\n";
            // 消費税１０％
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>調整</span></td>\n";
            // EDP消費税計上額
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>　</span></td>\n";
            echo "</tr>\n";
            
            // 計
            echo "<tr>\n";
            // タイトル
            echo "<td class='winbox' nowrap bgcolor='white' align='center'><div class='pt10b'>　</div></td>\n";
            // 税込金額
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>　</span></td>\n";
            // 税抜金額（課税対象）
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>　</span></td>\n";
            // 税抜金額（課税対象）
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>　</span></td>\n";
            // 税抜金額（課税対象）
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>　</span></td>\n";
            // 消費税８％
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>" . number_format(-212) . "</span></td>\n";
            // 消費税８％
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>" . number_format(0) . "</span></td>\n";
            // 消費税軽８％
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>雑収入消費税別計上</span></td>\n";
            echo "</tr>\n";
            
            // タイトルなし
            echo "<tr>\n";
            // タイトル
            echo "<td class='winbox' nowrap bgcolor='white' align='center'><div class='pt10b'>⑦ 回収等調整仮受消費税等</div></td>\n";
            // 税込金額
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>　</span></td>\n";
            // 税抜金額（課税対象）
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>　</span></td>\n";
            // 税抜金額（課税対象）
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>　</span></td>\n";
            // 税抜金額（課税対象）
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>　</span></td>\n";
            // 消費税８％
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>　</span></td>\n";
            // 消費税軽８％
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>　</span></td>\n";
            // 消費税１０％
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>　</span></td>\n";
            echo "</tr>\n";
            
            // タイトルなし
            echo "<tr>\n";
            // タイトル
            echo "<td class='winbox' nowrap bgcolor='white' align='center'><div class='pt10b'>ＥＤＰ仮受消費税等計上額</div></td>\n";
            // 税込金額
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>　</span></td>\n";
            // 税抜金額（課税対象）
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>" . number_format(5159434097) . "</span></td>\n";
            // 税抜金額（課税対象）
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>" . number_format(0) . "</span></td>\n";
            // 消費税８％
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>" . number_format(0) . "</span></td>\n";
            // 消費税軽８％
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>" . number_format(515943198) . "</span></td>\n";
            // 消費税１０％
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>" . number_format(515943198) . "</span></td>\n";
            // 消費税４％
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>　</span></td>\n";
            echo "</tr>\n";
            
            // タイトルなし
            echo "<tr>\n";
            // タイトル
            echo "<td class='winbox' nowrap bgcolor='white' align='center'><div class='pt10b'>　</div></td>\n";
            // 税込金額
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>　</span></td>\n";
            // 税抜金額（課税対象）
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>　</span></td>\n";
            // 税抜金額（課税対象）
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>　</span></td>\n";
            // 消費税８％
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>　</span></td>\n";
            // 消費税軽８％
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>　</span></td>\n";
            // 消費税１０％
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>　</span></td>\n";
            // 消費税４％
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>　</span></td>\n";
            echo "</tr>\n";
            
            // タイトルなし
            echo "<tr>\n";
            // タイトル
            echo "<td class='winbox' nowrap bgcolor='white' align='center' colspan='3'><div class='pt10b'>⑧ 課税標準額</div></td>\n";
            // 税込金額
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>試算表残高</span></td>\n";
            // 税抜金額（課税対象）
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>　</span></td>\n";
            // 税抜金額（課税対象）
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>試算表残高</span></td>\n";
            // 税抜金額（課税対象）
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>" . number_format(515943182) . "</span></td>\n";
            // 税込金額
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>　</span></td>\n";
            echo "</tr>\n";
            
            // タイトルなし
            echo "<tr>\n";
            // タイトル
            echo "<td class='winbox' nowrap bgcolor='white' align='center' colspan='3'><div class='pt10b'>①＋②＋③＋④＋⑤＋⑥＋⑦</div></td>\n";
            // 税込金額
            echo "  <th class='winbox' nowrap align='right' rowspan='6'><span class='pt9'>調整</span></td>\n";
            // 税抜金額（課税対象）
            echo "  <th class='winbox' nowrap align='right' colspan='2'><span class='pt9'>仮受消費税へ振替</span></td>\n";
            // 税抜金額（課税対象）
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>　</span></td>\n";
            // 税込金額
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>　</span></td>\n";
            echo "</tr>\n";
            
            // タイトルなし
            echo "<tr>\n";
            // タイトル
            echo "<td class='winbox' nowrap bgcolor='white' align='center' colspan='3'><div class='pt10b'>　</div></td>\n";
            // 税抜金額（課税対象）
            echo "  <th class='winbox' nowrap align='right' colspan='2'><span class='pt9'>(誤仕訳)</span></td>\n";
            // 税抜金額（課税対象）
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>　</span></td>\n";
            // 税込金額
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>　</span></td>\n";
            echo "</tr>\n";
            
            // タイトルなし
            echo "<tr>\n";
            // タイトル
            echo "<td class='winbox' nowrap bgcolor='white' align='center' colspan='3'><div class='pt10b'>　</div></td>\n";
            // 税抜金額（課税対象）
            echo "  <th class='winbox' nowrap align='right' colspan='2'><span class='pt9'>仮払消費税へ振替</span></td>\n";
            // 税抜金額（課税対象）
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>　</span></td>\n";
            // 税込金額
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>　</span></td>\n";
            echo "</tr>\n";
            
            // タイトルなし
            echo "<tr>\n";
            // タイトル
            echo "<td class='winbox' nowrap bgcolor='white' align='center' colspan='3'><div class='pt10b'>　</div></td>\n";
            // 税抜金額（課税対象）
            echo "  <th class='winbox' nowrap align='right' colspan='2'><span class='pt9'>(誤仕訳)</span></td>\n";
            // 税抜金額（課税対象）
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>　</span></td>\n";
            // 税込金額
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>　</span></td>\n";
            echo "</tr>\n";
            
            // タイトルなし
            echo "<tr>\n";
            // タイトル
            echo "<td class='winbox' nowrap bgcolor='white' align='center' colspan='3'><div class='pt10b'>　</div></td>\n";
            // 税抜金額（課税対象）
            echo "  <th class='winbox' nowrap align='right' colspan='2'><span class='pt9'>その他</span></td>\n";
            // 税抜金額（課税対象）
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>" . number_format(16) . "</span></td>\n";
            // 税込金額
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>　</span></td>\n";
            echo "</tr>\n";
            
            // タイトルなし
            echo "<tr>\n";
            // タイトル
            echo "<td class='winbox' nowrap bgcolor='white' align='center' colspan='3'><div class='pt10b'>　</div></td>\n";
            // 税抜金額（課税対象）
            echo "  <th class='winbox' nowrap align='right' colspan='2'><span class='pt9'>　</span></td>\n";
            // 税抜金額（課税対象）
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>　</span></td>\n";
            // 税込金額
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>　</span></td>\n";
            echo "</tr>\n";
            
            // タイトルなし
            echo "<tr>\n";
            // タイトル
            echo "<td class='winbox' nowrap bgcolor='white' align='center' colspan='3'><div class='pt10b'>　</div></td>\n";
            // 税込金額
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>差引残高</span></td>\n";
            // 税抜金額（課税対象）
            echo "  <th class='winbox' nowrap align='right' colspan='2'><span class='pt9'>差引</span></td>\n";
            // 税抜金額（課税対象）
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>" . number_format(515943198) . "</span></td>\n";
            // 税込金額
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>　</span></td>\n";
            echo "</tr>\n";
            ?>
            </tbody>
        </table>
            </td></tr>
        </table> <!----------------- ダミーEnd ------------------>
        </center>    
        <!--------------- ここから本文の表を表示する -------------------->
        <BR><BR>
        <left>
        ２．課税売上割引の確認
        </left>
        <center>
        <table bgcolor='#d6d3ce' align='center' border='1' cellspacing='0' cellpadding='3'>
            <tr><td> <!----------- ダミー(デザイン用) ------------>
        <table class='winbox_field' width='100%' bgcolor='#d6d3ce' align='center' border='1' cellspacing='0' cellpadding='3'>
            <thead>
                <!-- テーブル ヘッダーの表示 -->
                <tr>
                    <th class='winbox' nowrap colspan='7'>課税試算の譲渡等の対価の額の計算</th>
                </tr>
                <tr>
                    <th class='winbox' nowrap>内訳</th>
                    <th class='winbox' nowrap>金額</th>
                    <th class='winbox' nowrap colspan='2'>内訳</th>
                    <th class='winbox' nowrap>金額</th>
                    <th class='winbox' nowrap colspan='2'>　</th>
                </tr>
            </thead>
            <tfoot>
                <!-- 現在はフッターは何もない -->
            </tfoot>
            <tbody>
            <?php
            // 調整
            echo "<tr>\n";
            // タイトル
            echo "<td class='winbox' nowrap bgcolor='white' align='center'><div class='pt10b'>⑧課税売上高</div></td>\n";
            // 税込金額
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>" . number_format(5159434097) . "</span></td>\n";
            // 税抜金額（課税対象）
            echo "  <th class='winbox' nowrap align='right' colspan='2'><span class='pt9'>課税売上高＋免税売上高 Ａ</span></td>\n";
            // 消費税８％
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>" . number_format(5230710364) . "</span></td>\n";
            // 消費税軽８％
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>　</span></td>\n";
            // 消費税１０％
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>計算式</span></td>\n";
            echo "</tr>\n";
            
            // 調整
            echo "<tr>\n";
            // タイトル
            echo "<td class='winbox' nowrap bgcolor='white' align='center'><div class='pt10b'>免税売上高</div></td>\n";
            // 消費税８％
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>" . number_format(71276267) . "</span></td>\n";
            // 消費税軽８％
            echo "  <th class='winbox' nowrap align='right' rowspan='5'><span class='pt9'>非課税売上高</span></td>\n";
            // 消費税１０％
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>受取利息</span></td>\n";
            // EDP消費税計上額
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>" . number_format(166225) . "</span></td>\n";
            // 消費税４％
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>試算表残高</span></td>\n";
            // 税込金額
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>Ａ÷Ｂ＝</span></td>\n";
            echo "</tr>\n";
            
            // 調整
            echo "<tr>\n";
            // タイトル
            echo "<td class='winbox' nowrap bgcolor='white' align='center'><div class='pt10b'>　</div></td>\n";
            // 税抜金額（課税対象）
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>　</span></td>\n";
            // 消費税８％
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>雑収入</span></td>\n";
            // 消費税軽８％
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>" . number_format(118798) . "</span></td>\n";
            // 消費税１０％
            echo "  <th class='winbox' nowrap align='right'<span class='pt9'>消費税計算表</span></td>\n";
            // EDP消費税計上額
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>99.99</span></td>\n";
            echo "</tr>\n";
            
            // 調整
            echo "<tr>\n";
            // タイトル
            echo "<td class='winbox' nowrap bgcolor='white' align='center'><div class='pt10b'>　</div></td>\n";
            // 税抜金額（課税対象）
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>　</span></td>\n";
            // 消費税８％
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>有価証券利息</span></td>\n";
            // 消費税軽８％
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>" . number_format(0) . "</span></td>\n";
            // 消費税１０％
            echo "  <th class='winbox' nowrap align='right'<span class='pt9'>試算表残高</span></td>\n";
            // EDP消費税計上額
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>　</span></td>\n";
            echo "</tr>\n";
            
            // 調整
            echo "<tr>\n";
            // タイトル
            echo "<td class='winbox' nowrap bgcolor='white' align='center'><div class='pt10b'>　</div></td>\n";
            // 税抜金額（課税対象）
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>　</span></td>\n";
            // 消費税８％
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>土地売却代金</span></td>\n";
            // 消費税軽８％
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>" . number_format(0) . "</span></td>\n";
            // 消費税１０％
            echo "  <th class='winbox' nowrap align='right'<span class='pt9'>　</span></td>\n";
            // EDP消費税計上額
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>　</span></td>\n";
            echo "</tr>\n";
            
            // 調整
            echo "<tr>\n";
            // タイトル
            echo "<td class='winbox' nowrap bgcolor='white' align='center'><div class='pt10b'>　</div></td>\n";
            // 税抜金額（課税対象）
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>　</span></td>\n";
            // 消費税８％
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>　</span></td>\n";
            // 消費税軽８％
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>　</span></td>\n";
            // 消費税１０％
            echo "  <th class='winbox' nowrap align='right'<span class='pt9'>　</span></td>\n";
            // EDP消費税計上額
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>　</span></td>\n";
            echo "</tr>\n";
            
            // 前期調整分
            echo "<tr>\n";
            // タイトル
            echo "<td class='winbox' nowrap bgcolor='white' align='center' rowspan='2'><div class='pt10b'>Ａ        計</div></td>\n";
            // 税込金額
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>" . number_format(5230710364) . "</span></td>\n";
            // 税抜金額（課税対象）
            echo "  <th class='winbox' nowrap align='right' colspan='2'><span class='pt9'>Ｂ        計</span></td>\n";
            // 消費税８％
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>" . number_format(5230995387) . "</span></td>\n";
            // 消費税軽８％
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>　</span></td>\n";
            // 消費税１０％
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>　</span></td>\n";
            
            /*
            // 合計表示
            
            echo "<tr>\n";
            // 年月
            echo "<td class='winbox' nowrap bgcolor='white' align='center'><div class='pt10b'>合計</div></td>\n";
            // 購入額(軽8％)
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>" . number_format($t_kou8_kin) . "</span></td>\n";
            // 消費税額(軽8％)
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>" . number_format($t_jido8_kin) . "</span></td>\n";
            // 購入額(10%)
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>" . number_format($t_buy_kin) . "</span></td>\n";
            // 消費税額(10%)
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>" . number_format($t_tax_kin) . "</span></td>\n";
            // 税抜購入(軽8％)
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>" . number_format($t_kou8_kin) . "</span></td>\n";
            // 税抜購入(10%)
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>" . number_format($t_kou10_kin) . "</span></td>\n";
            // 税抜計上済(10%)
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>" . number_format($t_sumi10_kin) . "</span></td>\n";
            // 課税対象外
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>" . number_format($t_zeigai_kin) . "</span></td>\n";
            // 仮払消費税(10%)
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>" . number_format($t_kari10_kin) . "</span></td>\n";
            // 自動計算額(軽8％)
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>" . number_format($t_jido8_kin) . "</span></td>\n";
            // 自動計算額(10%)
            echo "  <th class='winbox' nowrap align='right'><span class='pt9'>" . number_format($t_jido10_kin) . "</span></td>\n";
            echo "</tr>\n";
            */
            ?>
            </tbody>
        </table>
            </td></tr>
        </table> <!----------------- ダミーEnd ------------------>
        
        <form method='post' action='<?php echo $menu->out_self() ?>'>
            <input class='pt10b' type='submit' name='input_data' value='登録' onClick='return data_input_click(this)'>
        </form>
    </center>
</body>
</html>
