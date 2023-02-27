<?php
//////////////////////////////////////////////////////////////////////////////
// 試験修理・バイモルの人員比較計算表の登録・修正及び照会兼用               //
// Copyright (C) 2009-2021 Norihisa.Ohya norihisa_ooya@nitto-kohki.co.jp    //
// Changed history                                                          //
// 2009/08/24 Created   profit_loss_bls_input.php                           //
// 2009/11/02 バイモル・試験修理を分ける前にリニアの販管費の人件費に        //
//            商管への社員按分給与を加味するよう変更                        //
// 2009/12/09 試験修理の労務費を１１分は固定値にするように変更              //
//            サービス割合登録忘れの為                                      //
// 2009/12/10 コメントの整理                                                //
// 2010/03/04 添田さんの給与配賦を加味して労務費を計算するように変更        //
// 2010/06/04 異動に伴い調整人員名の変更                                    //
// 2010/10/06 前月のデータコピーを追加                                      //
// 2011/06/07 2011/04より試験修理部門に581追加                              //
// 2011/06/08 500部門の経費が試験修理部門に配布されていたのを               //
//            2011/06より配布しないように変更                               //
// 2013/01/28 バイモルを液体ポンプへ変更（表示のみデータはバイモルのまま）  //
// 2014/05/07 異動に伴い調整人員名の変更                                    //
// 2014/08/06 一部コメントの追加                                            //
// 2015/06/10 機工の計算を追加                                              //
// 2015/06/15 機工の給与配賦を6月度より変更                                 //
// 2015/11/06 機工の給与配賦を10月度より変更                                //
//                                  → 元に戻す                             //
// 2016/02/02 安田さんの配賦を8：2（試修：リニア）に変更                    //
//            以前は入力した給与の0.5をリニア→試修だったが、入力した給与の //
//            0.2を試修→リニア（×-0.2）に変更                             //
// 2016/04/25 2016/04より機工の給与配賦を変更                               //
// 2016/07/22 修理・耐久損益のための労務費・経費を計算登録                  //
// 2016/10/14 安田さんを安達さんへ変更（配賦割合は要検討$invent[16]）       //
// 2016/10/31 安達さんは100％試験なので、金額を入力しない(2016/10～)        //
// 2016/11/18 一番下に安田係長給与の20％を自動配賦するよう追加              //
//            リニアからマイナス(労務費以外には影響させない)                //
// 2017/05/08 人事異動による名称変更                                        //
// 2017/05/09 2017/05より機工配賦割合変更（千田副部長、石崎課長代理分）     //
// 2018/04/19 2018/04より機工配賦割合変更（千田副部長、安田課長分）         //
// 2018/10/17 コメントを修正                                                //
// 2019/02/05 コメントを修正                                                //
// 2019/05/09 人事異動に伴う名称の変更                                      //
// 2021/03/03 機工終了に伴う配布の終了                                      //
//////////////////////////////////////////////////////////////////////////////
ini_set('error_reporting', E_STRICT);       // E_STRICT=2048(php5) E_ALL=2047 debug 用
// ini_set('error_reporting',E_ALL);        // E_ALL='2047' debug 用
// ini_set('display_errors','1');           // Error 表示 ON debug 用 リリース後コメント
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
// $menu->set_caption('栃木日東工器(株)');
///// 呼出先のaction名とアドレス設定
// $menu->set_action('抽象化名',   PL . 'address.php');

$current_script  = $_SERVER['PHP_SELF'];        // 現在実行中のスクリプト名を保存
$url_referer     = $_SESSION['pl_referer'];     // 分岐処理前に保存されている呼出元をセットする

///// タイトルの日付・時間設定
$today = date("Y/m/d H:i:s");

///// 期・月の取得
$ki = Ym_to_tnk($_SESSION['pl_ym']);
$tuki = substr($_SESSION['pl_ym'],4,2);

///// タイトル名(ソースのタイトル名とフォームのタイトル名)
$menu->set_title("第{$ki}期　{$tuki}月度　ＢＬＳ 人員比率計算表");

///// 対象当月
$yyyymm = $_SESSION['pl_ym'];
///// 対象前月
if (substr($yyyymm,4,2)!=01) {
    $p1_ym = $yyyymm - 1;
} else {
    $p1_ym = $yyyymm - 100;
    $p1_ym = $p1_ym + 11;
}
///// yymm形式
$ym4 = substr($yyyymm, 2, 4);

///// バイモル売上比率のデータ取得
$res_b_allo = array();
$query = sprintf("select allo from act_pl_history where pl_bs_ym=%d and note='バイモル売上高'", $yyyymm);
if ((getResult($query,$res_b_allo)) > 0) {
    $bimor_allo = $res_b_allo[0][0];
} else {
    $_SESSION['s_sysmsg'] .= "ＣＬ損益計算が実行されていません。<br>";
    header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
    exit();
}
///// 機工売上比率のデータ取得
$res_t_allo = array();
$query = sprintf("select allo from act_pl_history where pl_bs_ym=%d and note='機工売上高'", $yyyymm);
if ((getResult($query,$res_t_allo)) > 0) {
    $tool_allo = $res_t_allo[0][0];
} else {
    $tool_allo = 0;
}
///// 試験・修理売上比率のデータ取得
$res_s_allo = array();
$query = sprintf("select allo from act_pl_history where pl_bs_ym=%d and note='試修売上高'", $yyyymm);
if ((getResult($query,$res_s_allo)) > 0) {
    $ss_allo = $res_s_allo[0][0];
} else {
    $_SESSION['s_sysmsg'] .= "ＣＬ損益計算が実行されていません。<br>";
    header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
    exit();
}
///// カプラ試験・修理売上比率のデータ取得
if ($yyyymm >= 200911) {
    $res_sc_allo = array();
    $query = sprintf("select allo from act_pl_history where pl_bs_ym=%d and note='カプラ試修売上高'", $yyyymm);
    if ((getResult($query,$res_sc_allo)) > 0) {
        $sc_allo = $res_sc_allo[0][0];
    } else {
        $_SESSION['s_sysmsg'] .= "カプラ試験・修理の売上が登録されていません。<br>先にカプラ試験・修理の売上を登録してください。<br>";
        header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
        exit();
    }
}
///// 商管社員按分給与（リニア）データ取得
$l_allo_kin = 0;
if ($yyyymm >= 200910) {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='リニア商管社員按分給与'", $yyyymm);
    $res = array();
    if ((getResult($query,$res)) > 0) {
        $l_allo_kin = $res[0][0];
    } else {
        $_SESSION['s_sysmsg'] .= sprintf("商管の損益登録がされていません。<br>先に商管への按分給与の入力を行ってください。", $yyyymm);
        query_affected_trans($con, "rollback");     // Transaction Rollback
        header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
        exit();
    }
}
///// リニア販管費の人件費データ取得
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='リニア人件費'", $yyyymm);
$res = array();
getResult($query, $res);
if ($res[0][0] != 0) {
    $l_jin = $res[0][0] - $l_allo_kin;
} else {
    $_SESSION['s_sysmsg'] .= sprintf("ＣＬ損益計算が実行されていません。<br>", $yyyymm);
    query_affected_trans($con, "rollback");     // Transaction Rollback
    header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
    exit();
}
///// リニア販管費の経費データ取得
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='リニア経費'", $yyyymm);
$res = array();
getResult($query, $res);
if ($res[0][0] != 0) {
    $l_kei = $res[0][0];
} else {
    $_SESSION['s_sysmsg'] .= sprintf("ＣＬ損益計算が実行されていません。<br>", $yyyymm);
    query_affected_trans($con, "rollback");     // Transaction Rollback
    header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
    exit();
}

///// 修理売上高率の取得
$ss_uri_allo = 0;
$query = sprintf("select allo from act_pl_history where pl_bs_ym=%d and note='修理売上高'", $yyyymm);
$res = array();
if ((getResult($query,$res)) > 0) {
    $ss_uri_allo = $res[0][0];
} else {
    $ss_uri_allo = 0;
}

///// 耐久売上高率の取得
$st_uri_allo = 0;
$query = sprintf("select allo from act_pl_history where pl_bs_ym=%d and note='耐久売上高'", $yyyymm);
$res = array();
if ((getResult($query,$res)) > 0) {
    $st_uri_allo = $res[0][0];
} else {
    $st_uri_allo = 0;
}

//////////// 人件費・経費のレコード数 フィールド数
$rec_jin   =  8;    // 人件費の使用科目数
$rec_keihi = 28;    // 経費の使用科目数
$f_mei     = 13;    // 明細(表)のフィールド数
//////////// 勘定科目の配列設定
/******
    8101 = 役員報酬
    8102 = 給料手当
    8103 = 賞与手当
    8104 = 顧問料
    8105 = 法定福利費
    8106 = 厚生福利費
    8121 = 賞与引当金繰入
    8123 = 退職給付費用  旧名→退職給与引当金繰入
******/
$jin_act = array(8101,8102,8103,8104,8105,8106,8121,8123);
/******
    7501 = 旅費交通費
    7502 = 海外出張
    7503 = 通信費
    7504 = 会議費
    7505 = 交際接待費
    7506 = 広告宣伝費
    7508 = 求人費
    7509 = 運賃荷造費
    7510 = 図書教育費
    7512 = 業務委託費
    7520 = 事業等       // 外形標準課税により追加
    7521 = 諸税公課
    7522 = 試験研究費
    7523 = 雑費
    7524 = 修繕費
    7525 = 保証修理費
    7526 = 事務用消耗品費
    7527 = 工場消耗品費
    7528 = 車両費
    7530 = 保険料
    7531 = 水道光熱費
    7532 = 諸会費
    7533 = 支払手数料
    7536 = 地代家賃
    7537 = 寄付金
    7538 = 倉敷料
    7540 = 賃借料
    7550 = クレーム対応費
    8000 = 減価償却費
******/
$kei_act = array(7501,7502,7503,7504,7505,7506,7508,7509,7510,7512,7521,7522,7523,7524,7525,7526,7527,7528,7530,7531,7532,7533,7536,7537,7538,7540,7550,8000);
////// 全体の配列   外形標準課税の事業等(7520)を最後に追加
$actcod  = array(8101,8102,8103,8104,8105,8106,8121,8123,7501,7502,7503,7504,7505,7506,7508,7509,7510,7512,7521,7522,7523,7524,7525,7526,7527,7528,7530,7531,7532,7533,7536,7537,7538,7540,8000,7520,7550);

///////// 項目とインデックスの関連付け
$item = array();
$item[0]   = "リニア社員数";
$item[1]   = "リニアパート数";
$item[2]   = "バイモルパート数";
$item[3]   = "試験修理パート数";
$item[4]   = "バイモル社員数１";
$item[5]   = "バイモル社員数２";
$item[6]   = "バイモル社員数３";
$item[7]   = "試験修理社員数１";
$item[8]   = "試験修理社員数２";
$item[9]   = "試験修理社員数３";
$item[10]  = "バイモル社員配布給与";
$item[11]  = "試験修理社員配布給与";
$item[12]  = "カプラ給与配賦率";
$item[13]  = "リニア給与配賦率";
$item[14]  = "試修給与配賦率";
$item[15]  = "試修給与配賦額";
$item[16]  = "試験修理社員配布給与２";
$item[17]   = "機工社員配布給与１";
$item[18]   = "機工社員配布給与２";
$item[19]   = "機工社員配布給与３";
$item[20]   = "機工社員配布給与４";
$item[21]   = "機工社員配布給与５";
$item[22]   = "機工経費調整";
$item[23]   = "試験修理社員配布給与３";
///////// 入力text 変数 初期化
$invent = array();
for ($i = 0; $i < 24; $i++) {
    if (isset($_POST['invent'][$i])) {
        $invent[$i]   = $_POST['invent'][$i];
        $invent_z[$i] = $_POST['invent_z'][$i];     // 前月分
    } else {
        $invent[$i]   = 0;
        $invent_z[$i] = 0;
    }
}
if (!isset($_POST['entry'])) {                      // データ入力
    ////////// 登録済みならば棚卸金額取得（当月）
    for ($i = 0; $i < 24; $i++) {
        if ($i >= 10 && $i <= 11) {
            $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='%s'", $yyyymm, $item[$i]);
        } elseif ($i >= 12 && $i <= 14) {
            $query = sprintf("select allo from act_pl_history where pl_bs_ym=%d and note='%s'", $yyyymm, $item[$i]);
        } elseif ($i >= 15) {
            $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='%s'", $yyyymm, $item[$i]);
        } else {
            $query = sprintf("select allo from act_pl_history where pl_bs_ym=%d and note='%s'", $yyyymm, $item[$i]);
        }
        $res = array();
        if (getResult2($query,$res) > 0) {
            $invent[$i] = $res[0][0];
        } else {
            $invent[$i] = 0;
        }
    }
    $l_part50 = UROUND(($invent[1] * 0.5),2);           // リニアパート掛率50％（当月）
    $l_total  = $invent[0] + $l_part50;                 // リニア計（当月）
    $b_shain  = $invent[4] + $invent[5] + $invent[6];   // バイモル社員数（当月）
    $b_part50 = UROUND(($invent[2] * 0.5),2);           // バイモルパート掛率50％（当月）
    $b_total  = $b_shain + $b_part50;                   // バイモル計（当月）
    $s_shain  = $invent[7] + $invent[8] + $invent[9];   // 試修社員数（当月）
    $s_part50 = UROUND(($invent[3] * 0.5),2);           // 試修パート掛率50％（当月）
    $s_total  = $s_shain + $s_part50;                   // 試修計（当月）
    
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='%s'", $yyyymm, $item[12]);
    $res = array();
    getResult($query, $res);
    if ($res[0][0] != 0) {
        $c_hai_kin = number_format($res[0][0]);
    } else {
        $c_hai_kin = 0;
    }
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='%s'", $yyyymm, $item[13]);
    $res = array();
    getResult($query, $res);
    if ($res[0][0] != 0) {
        $l_hai_kin = number_format($res[0][0]);
    } else {
        $l_hai_kin = 0;
    }
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='%s'", $yyyymm, $item[14]);
    $res = array();
    getResult($query, $res);
    if ($res[0][0] != 0) {
        $s_hai_kin = number_format($res[0][0]);
    } else {
        $s_hai_kin = 0;
    }
    for ($i = 0; $i < 24; $i++) {
        if ($i >= 10 && $i <= 11) {
            $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='%s'", $p1_ym, $item[$i]);
        } elseif ($i >= 12 && $i <= 14) {
            $query = sprintf("select allo from act_pl_history where pl_bs_ym=%d and note='%s'", $p1_ym, $item[$i]);
        } elseif ($i >= 15) {
            $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='%s'", $p1_ym, $item[$i]);
        } else {
            $query = sprintf("select allo from act_pl_history where pl_bs_ym=%d and note='%s'", $p1_ym, $item[$i]);
        }
        $res = array();
        if (getResult2($query,$res_z) > 0) {
            $invent_z[$i] = $res_z[0][0];
        } else {
            $invent_z[$i] = 0;
        }
    }
    $l_part50_z = UROUND(($invent_z[1] * 0.5),2);               // リニアパート掛率50％（前月）
    $l_total_z  = $invent_z[0] + $l_part50_z;                   // リニア計（前月）
    $b_shain_z  = $invent_z[4] + $invent_z[5] + $invent_z[6];   // バイモル社員数（前月）
    $b_part50_z = UROUND(($invent_z[2] * 0.5),2);               // バイモルパート掛率50％（前月）
    $b_total_z  = $b_shain_z + $b_part50_z;                     // バイモル計（前月）
    $s_shain_z  = $invent_z[7] + $invent_z[8] + $invent_z[9];   // 試修社員数（前月）
    $s_part50_z = UROUND(($invent_z[3] * 0.5),2);               // 試修パート掛率50％（前月）
    $s_total_z  = $s_shain_z + $s_part50_z;                     // 試修計（前月）
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='%s'", $p1_ym, $item[12]);
    $res = array();
    getResult($query, $res);
    if ($res[0][0] != 0) {
        $c_hai_kin_z = number_format($res[0][0]);
    } else {
        $c_hai_kin_z = 0;
    }
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='%s'", $p1_ym, $item[13]);
    $res = array();
    getResult($query, $res);
    if ($res[0][0] != 0) {
        $l_hai_kin_z = number_format($res[0][0]);
    } else {
        $l_hai_kin_z = 0;
    }
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='%s'", $p1_ym, $item[14]);
    $res = array();
    getResult($query, $res);
    if ($res[0][0] != 0) {
        $s_hai_kin_z = number_format($res[0][0]);
    } else {
        $s_hai_kin_z = 0;
    }
    
    if (isset($_POST['copy'])) {                        // 前月データのコピー
        $l_part50 = $l_part50_z;                        // リニアパート掛率50％（当月）
        $l_total  = $l_total_z;                         // リニア計（当月）
        $b_shain  = $b_shain_z;                         // バイモル社員数（当月）
        $b_part50 = $b_part50_z;                        // バイモルパート掛率50％（当月）
        $b_total  = $b_total_z;                         // バイモル計（当月）
        $s_shain  = $s_shain_z;                         // 試修社員数（当月）
        $s_part50 = $s_part50_z;                        // 試修パート掛率50％（当月）
        $s_total  = $s_total_z;                         // 試修計（当月）
        $c_hai_kin = $c_hai_kin_z;
        $l_hai_kin = $l_hai_kin_z;
        $s_hai_kin = $s_hai_kin_z;
        for ($i = 0; $i < 24; $i++) {
            $invent[$i] = $invent_z[$i];
        }
    }
} else {    // 登録処理  トランザクションで更新しているためレコード有り無しのチェックのみ
    $allo_kei = $invent[12] + $invent[13] + $invent[14];
    if ($allo_kei != 100) {
        if ($allo_kei != 0) {
            $_SESSION["s_sysmsg"] .= "率が１００ではありません！";
            header("Location: $current_script");
            exit();
        } else {
            $invent[12] = 0;
            $invent[13] = 0;
            $invent[14] = 0;
            $invent[15] = 0;
        }
    }
    $l_part50 = UROUND(($invent[1] * 0.5),2);
    $l_total  = $invent[0] + $l_part50;
    $b_shain  = $invent[4] + $invent[5] + $invent[6];
    $b_part50 = UROUND(($invent[2] * 0.5),2);
    $b_total  = $b_shain + $b_part50;
    $s_shain  = $invent[7] + $invent[8] + $invent[9];
    $s_part50 = UROUND(($invent[3] * 0.5),2);
    $s_total  = $s_shain + $s_part50;
    
    $l_part50_z = UROUND(($invent_z[1] * 0.5),2);
    $l_total_z  = $invent_z[0] + $l_part50_z;
    $b_shain_z  = $invent_z[4] + $invent_z[5] + $invent_z[6];
    $b_part50_z = UROUND(($invent_z[2] * 0.5),2);
    $b_total_z  = $b_shain_z + $b_part50_z;
    $s_shain_z  = $invent_z[7] + $invent_z[8] + $invent_z[9];
    $s_part50_z = UROUND(($invent_z[3] * 0.5),2);
    $s_total_z  = $s_shain_z + $s_part50_z;
    $c_hai_kin_z = number_format($c_hai_kin_z);
    $l_hai_kin_z = number_format($l_hai_kin_z);
    $s_hai_kin_z = number_format($s_hai_kin_z);
    
    // 試験修理給与配賦計算（添田さん分 試験修理の労務費よりマイナスして各配賦率で配賦する）
    $ckyu_kin = 0;      // カプラ給与配賦額
    $lkyu_kin = 0;      // リニア給与配賦額
    $skyu_kin = 0;      // 試修給与配賦額
    
    $allkyu_kin = $invent[15];
    $ckyu_kin   = UROUND(($invent[15] * $invent[12] / 100), 0);
    $lkyu_kin   = UROUND(($invent[15] * $invent[13] / 100), 0);
    $skyu_kin   = $invent[15] - $ckyu_kin - $lkyu_kin;
    // CL試験修理配賦計算
    $sckyu_kin = UROUND(($skyu_kin * $sc_allo), 0);
    $slkyu_kin = $skyu_kin - $sckyu_kin;
    
    $c_hai_kin = number_format($ckyu_kin);
    $l_hai_kin = number_format($lkyu_kin);
    $s_hai_kin = number_format($skyu_kin);    
    for ($i = 0; $i < 24; $i++) {
        if ($i >= 10 && $i <= 11) {
            $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='%s'", $p1_ym, $item[$i]);
        } elseif ($i >= 12 && $i <= 14) {
            $query = sprintf("select allo from act_pl_history where pl_bs_ym=%d and note='%s'", $p1_ym, $item[$i]);
        } elseif ($i >= 15) {
            $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='%s'", $p1_ym, $item[$i]);
        } else {
            $query = sprintf("select allo from act_pl_history where pl_bs_ym=%d and note='%s'", $p1_ym, $item[$i]);
        }
        $res = array();
        if (getResult2($query,$res_z) > 0) {
            $invent_z[$i] = $res_z[0][0];
        } else {
            $invent_z[$i] = 0;
        }
    }
    for ($i = 0; $i < 24; $i++) {
        if ($i >= 10) {
            $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='%s'", $yyyymm, $item[$i]);
        } else {
            $query = sprintf("select allo from act_pl_history where pl_bs_ym=%d and note='%s'", $yyyymm, $item[$i]);
        }
        $res = array();
        if (getResult2($query,$res) <= 0) {
            /////////// begin トランザクション開始
            if ($con = db_connect()) {
                query_affected_trans($con, "begin");
            } else {
                $_SESSION["s_sysmsg"] .= "データベースに接続できません";
                header("Location: $current_script");
                exit();
            }
            ////////// Insert Start
            if ($i >= 10 && $i <= 11) {
                $query = sprintf("insert into act_pl_history (pl_bs_ym, kin, note) values (%d, %d, '%s')", $yyyymm, $invent[$i], $item[$i]);
            } elseif ($i >= 12 && $i <= 15) {
                if ($i == 12) {
                    $query = sprintf("insert into act_pl_history (pl_bs_ym, kin, note, allo) values (%d, %d, '%s', %3.2f)", $yyyymm, $ckyu_kin, $item[$i], $invent[$i]);
                } elseif ($i == 13) {
                    $query = sprintf("insert into act_pl_history (pl_bs_ym, kin, note, allo) values (%d, %d, '%s', %3.2f)", $yyyymm, $lkyu_kin, $item[$i], $invent[$i]);
                } elseif ($i == 14) {
                    $query = sprintf("insert into act_pl_history (pl_bs_ym, kin, note, allo) values (%d, %d, '%s', %3.2f)", $yyyymm, $skyu_kin, $item[$i], $invent[$i]);
                } elseif ($i == 15) {
                    $query = sprintf("insert into act_pl_history (pl_bs_ym, kin, note, allo) values (%d, %d, '%s', %3.2f)", $yyyymm, $invent[$i], $item[$i], 100.00);
                }
            } elseif ($i >= 16) {
                $query = sprintf("insert into act_pl_history (pl_bs_ym, kin, note) values (%d, %d, '%s')", $yyyymm, $invent[$i], $item[$i]);
            } else {
                $query = sprintf("insert into act_pl_history (pl_bs_ym, allo, note) values (%d, %1.1f, '%s')", $yyyymm, $invent[$i], $item[$i]);
            }
            if (query_affected_trans($con, $query) <= 0) {
                $_SESSION["s_sysmsg"] .= sprintf("%sの新規登録に失敗<br>第 %d期 %d月", $item[$i], $ki, $tuki);
                query_affected_trans($con, "rollback");     // transaction rollback
                header("Location: $current_script");
                exit();
            }
            /////////// commit トランザクション終了
            query_affected_trans($con, "commit");
            $_SESSION["s_sysmsg"] = sprintf("<font color='yellow'>第%d期 %d月 BLS人員比較計算データ 新規 登録完了</font>",$ki,$tuki);
        } else {
            /////////// begin トランザクション開始
            if ($con = db_connect()) {
                query_affected_trans($con, "begin");
            } else {
                $_SESSION["s_sysmsg"] .= "データベースに接続できません";
                header("Location: $current_script");
                exit();
            }
            ////////// UPDATE Start
            if ($i >= 10 && $i <= 11) {
                $query = sprintf("update act_pl_history set kin=%d where pl_bs_ym=%d and note='%s'", $invent[$i], $yyyymm, $item[$i]);
            } elseif ($i >= 12 && $i <= 15) {
                if ($i == 12) {
                    $query = sprintf("update act_pl_history set kin=%d, allo=%3.2f where pl_bs_ym=%d and note='%s'", $ckyu_kin, $invent[$i], $yyyymm, $item[$i]);
                } elseif ($i == 13) {
                    $query = sprintf("update act_pl_history set kin=%d, allo=%3.2f where pl_bs_ym=%d and note='%s'", $lkyu_kin, $invent[$i], $yyyymm, $item[$i]);
                } elseif ($i == 14) {
                    $query = sprintf("update act_pl_history set kin=%d, allo=%3.2f where pl_bs_ym=%d and note='%s'", $skyu_kin, $invent[$i], $yyyymm, $item[$i]);
                } elseif ($i == 15) {
                    $query = sprintf("update act_pl_history set kin=%d, allo=%3.2f where pl_bs_ym=%d and note='%s'", $invent[$i], 100.00, $yyyymm, $item[$i]);
                }
            } elseif ($i >= 16) {
                $query = sprintf("update act_pl_history set kin=%d where pl_bs_ym=%d and note='%s'", $invent[$i], $yyyymm, $item[$i]);
            } else {
                $query = sprintf("update act_pl_history set allo=%1.1f where pl_bs_ym=%d and note='%s'", $invent[$i], $yyyymm, $item[$i]);
            }
            if (query_affected_trans($con, $query) <= 0) {
                $_SESSION["s_sysmsg"] .= sprintf("%sのUPDATEに失敗<br>第 %d期 %d月", $item[$i], $ki, $tuki);
                query_affected_trans($con, "rollback");     // transaction rollback
                header("Location: $current_script");
                exit();
            }
            /////////// commit トランザクション終了
            query_affected_trans($con, "commit");
            $_SESSION["s_sysmsg"] = sprintf("<font color='yellow'>第%d期 %d月 BLS人員比較計算データ 変更 完了</font>",$ki,$tuki);
        }
    }
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='カプラ試修給与配賦額'", $yyyymm);
    $res = array();
    if (getResult2($query,$res) <= 0) {
        /////////// begin トランザクション開始
        if ($con = db_connect()) {
            query_affected_trans($con, "begin");
        } else {
            $_SESSION["s_sysmsg"] .= "データベースに接続できません";
            header("Location: $current_script");
            exit();
        }
        $query = sprintf("insert into act_pl_history (pl_bs_ym, kin, note) values (%d, %d, 'カプラ試修給与配賦額')", $yyyymm, $sckyu_kin);
        if (query_affected_trans($con, $query) <= 0) {
            $_SESSION["s_sysmsg"] .= sprintf("カプラ試修給与配賦額の新規登録に失敗<br>第 %d期 %d月", $ki, $tuki);
            query_affected_trans($con, "rollback");     // transaction rollback
            header("Location: $current_script");
            exit();
        }
        /////////// commit トランザクション終了
        query_affected_trans($con, "commit");
        $_SESSION["s_sysmsg"] = sprintf("<font color='yellow'>第%d期 %d月 BLS人員比較計算データ 新規 登録完了</font>",$ki,$tuki);
    } else {
        /////////// begin トランザクション開始
        if ($con = db_connect()) {
            query_affected_trans($con, "begin");
        } else {
            $_SESSION["s_sysmsg"] .= "データベースに接続できません";
            header("Location: $current_script");
            exit();
        }
        ////////// UPDATE Start
        $query = sprintf("update act_pl_history set kin=%d where pl_bs_ym=%d and note='カプラ試修給与配賦額'", $sckyu_kin, $yyyymm);
        if (query_affected_trans($con, $query) <= 0) {
            $_SESSION["s_sysmsg"] .= sprintf("カプラ試修給与配賦額のUPDATEに失敗<br>第 %d期 %d月", $ki, $tuki);
            query_affected_trans($con, "rollback");     // transaction rollback
            header("Location: $current_script");
            exit();
        }
        /////////// commit トランザクション終了
        query_affected_trans($con, "commit");
        $_SESSION["s_sysmsg"] = sprintf("<font color='yellow'>第%d期 %d月 BLS人員比較計算データ 変更 完了</font>",$ki,$tuki);
    }
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='リニア試修給与配賦額'", $yyyymm);
    $res = array();
    if (getResult2($query,$res) <= 0) {
        /////////// begin トランザクション開始
        if ($con = db_connect()) {
            query_affected_trans($con, "begin");
        } else {
            $_SESSION["s_sysmsg"] .= "データベースに接続できません";
            header("Location: $current_script");
            exit();
        }
        $query = sprintf("insert into act_pl_history (pl_bs_ym, kin, note) values (%d, %d, 'リニア試修給与配賦額')", $yyyymm, $slkyu_kin);
        if (query_affected_trans($con, $query) <= 0) {
            $_SESSION["s_sysmsg"] .= sprintf("リニア試修給与配賦額の新規登録に失敗<br>第 %d期 %d月", $ki, $tuki);
            query_affected_trans($con, "rollback");     // transaction rollback
            header("Location: $current_script");
            exit();
        }
        /////////// commit トランザクション終了
        query_affected_trans($con, "commit");
        $_SESSION["s_sysmsg"] = sprintf("<font color='yellow'>第%d期 %d月 BLS人員比較計算データ 新規 登録完了</font>",$ki,$tuki);
    } else {
        /////////// begin トランザクション開始
        if ($con = db_connect()) {
            query_affected_trans($con, "begin");
        } else {
            $_SESSION["s_sysmsg"] .= "データベースに接続できません";
            header("Location: $current_script");
            exit();
        }
        ////////// UPDATE Start
        $query = sprintf("update act_pl_history set kin=%d where pl_bs_ym=%d and note='リニア試修給与配賦額'", $slkyu_kin, $yyyymm);
        if (query_affected_trans($con, $query) <= 0) {
            $_SESSION["s_sysmsg"] .= sprintf("リニア試修給与配賦額のUPDATEに失敗<br>第 %d期 %d月", $ki, $tuki);
            query_affected_trans($con, "rollback");     // transaction rollback
            header("Location: $current_script");
            exit();
        }
        /////////// commit トランザクション終了
        query_affected_trans($con, "commit");
        $_SESSION["s_sysmsg"] = sprintf("<font color='yellow'>第%d期 %d月 BLS人員比較計算データ 変更 完了</font>",$ki,$tuki);
    }

    // バイモル労務費
    $b_roumu = 0;
    // 560 部門労務費
    $query = sprintf("SELECT sum(act_monthly) FROM act_summary WHERE act_yymm=%d and act_id=560 and actcod>=8101 and actcod<=8130", $ym4);
    $res   = array();
    getResult($query, $res);
    $b_roumu += $res[0][0];
    // バイモル間接費 サービス割合より
    $query = sprintf("SELECT sum(total_cost) FROM service_percent_factory_expenses WHERE total_date=%d and (total_item='バイモル' or total_item='外注バイ')", $yyyymm);
    $res   = array();
    getResult($query, $res);
    $b_roumu += $res[0][0];
    // 500 第2生産部労務費配賦
    $query = sprintf("SELECT sum(act_monthly) FROM act_summary WHERE act_yymm=%d and act_id=500 and actcod>=8101 and actcod<=8130", $ym4);
    $res   = array();
    getResult($query, $res);
    $b_roumu += Uround(($res[0][0] * $bimor_allo),0);
    // バイモル間接社員の給与20％
    $b_roumu += Uround(($invent[10] * 0.2),0);
    
    $query_chk = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='バイモル労務費'", $yyyymm);
    if (getResult($query_chk,$res_chk) <= 0) {  ///// 既登録済みのチェック
        // 新規登録
        $query = sprintf("insert into act_pl_history (pl_bs_ym, kin, note) values (%d, %d, 'バイモル労務費')", $yyyymm, $b_roumu);
        if (query_affected_trans($con, $query) <= 0) {
            $_SESSION['s_sysmsg'] .= sprintf("バイモル労務費の登録に失敗<br>第 %d期 %d月",$ki,$tuki);
            query_affected_trans($con, "rollback");     // Transaction Rollback
            header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
            exit();
        }
    } else {
        $query = sprintf("update act_pl_history set kin=%d where pl_bs_ym=%d and note='バイモル労務費'", $b_roumu, $yyyymm);
        if (query_affected_trans($con, $query) <= 0) {
            $_SESSION['s_sysmsg'] .= sprintf("バイモル労務費の更新失敗<br>第 %d期 %d月",$ki,$tuki);
            query_affected_trans($con, "rollback");     // Transaction Rollback
            header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
            exit();
        }
    }
    
    // 試験修理労務費
    $s_roumu = 0;
    // 559 部門労務費
    $query = sprintf("SELECT sum(act_monthly) FROM act_summary WHERE act_yymm=%d and act_id=559 and actcod>=8101 and actcod<=8130", $ym4);
    $res   = array();
    getResult($query, $res);
    $s_roumu += $res[0][0];
    $roumu_559 = $res[0][0];    // 修理労務費暫定計算
    // 2011/04 より 581 部門労務費
    if ($yyyymm >= 201104) {
        $query = sprintf("SELECT sum(act_monthly) FROM act_summary WHERE act_yymm=%d and act_id=581 and actcod>=8101 and actcod<=8130", $ym4);
        $res   = array();
        getResult($query, $res);
        $s_roumu += $res[0][0];
        $roumu_581 = $res[0][0];    // 耐久労務費暫定計算
    }
    // 間接費配賦 サービス割合より
    $query = sprintf("SELECT sum(total_cost) FROM service_percent_factory_expenses WHERE total_date=%d", $yyyymm);
    $res   = array();
    getResult($query, $res);
    $s_roumu += Uround(($res[0][0] * $ss_allo),0);
    if ($yyyymm < 201106) {                        // 2011年6月より配賦しない
        // 500 第2生産部労務費配賦
        $query = sprintf("SELECT sum(act_monthly) FROM act_summary WHERE act_yymm=%d and act_id=500 and actcod>=8101 and actcod<=8130", $ym4);
        $res   = array();
        getResult($query, $res);
        $s_roumu += Uround(($res[0][0] * $ss_allo),0);
    }
    // 試験修理間接社員の給与20％
    if ($yyyymm < 201104) {
        $s_roumu += Uround(($invent[11] * 0.2),0);
    } else { // 201104より10%
        $s_roumu += Uround(($invent[11] * 0.1),0);
    }
    if ($yyyymm == 200911) {                        // 2009年11月は固定値
        $s_roumu = 2001186;
    }
    $s_roumu += Uround(($invent[16] * -0.2),0);
    
    // 安田さんの給与２０％を試修に配賦 → 後に耐久のみに配賦
    $s_roumu += Uround(($invent[23] * 0.1),0);
    
    $query_chk = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='試修労務費'", $yyyymm);
    if (getResult($query_chk,$res_chk) <= 0) {      // 既登録済みのチェック
        // 新規登録
        $query = sprintf("insert into act_pl_history (pl_bs_ym, kin, note) values (%d, %d, '試修労務費')", $yyyymm, $s_roumu);
        if (query_affected_trans($con, $query) <= 0) {
            $_SESSION['s_sysmsg'] .= sprintf("試修労務費の登録に失敗<br>第 %d期 %d月",$ki,$tuki);
            query_affected_trans($con, "rollback");     // Transaction Rollback
            header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
            exit();
        }
    } else {
        $query = sprintf("update act_pl_history set kin=%d where pl_bs_ym=%d and note='試修労務費'", $s_roumu, $yyyymm);
        if (query_affected_trans($con, $query) <= 0) {
            $_SESSION['s_sysmsg'] .= sprintf("試修労務費の更新失敗<br>第 %d期 %d月",$ki,$tuki);
            query_affected_trans($con, "rollback");     // Transaction Rollback
            header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
            exit();
        }
    }
    // 機工労務費
    $t_roumu = 0;
    // 560 部門労務費
    $query = sprintf("SELECT sum(act_monthly) FROM act_summary WHERE act_yymm=%d and act_id=560 and actcod>=8101 and actcod<=8130", $ym4);
    $res   = array();
    getResult($query, $res);
    $t_roumu += $res[0][0];
    if ($yyyymm >= 202102) {
        $t_roumu = 0;
    }
    // 大房部長代理の給与6月までは50％それ以降は5％
    if ($yyyymm >= 201504 && $yyyymm <= 201505) {
        $t_roumu += Uround(($invent[17] * 0.5),0);
        // 千田課長の給与5月から0.05％(4月は給与登録なしなのでIF文なし)
        $t_roumu += Uround(($invent[18] * 0.05),0);
        // 中山課長代理の給与5月から0.05％(4月は給与登録なしなのでIF文なし)
        $t_roumu += Uround(($invent[19] * 0.05),0);
        // 予備社員の給与5月から0.05％(4月は給与登録なしなのでIF文なし)
        $t_roumu += Uround(($invent[20] * 0.05),0);
    } elseif ($yyyymm >= 201506 && $yyyymm <= 201509) {
        // 6月以降、上半期は以下倍率（5月までとは順番も倍率も違う）
        // $invent[17] 小森谷部長 10%
        $t_roumu += Uround(($invent[17] * 0.1),0);
        // $invent[18] 大房部長代理 50%
        $t_roumu += Uround(($invent[18] * 0.5),0);
        // $invent[19] 千田課長 30%
        $t_roumu += Uround(($invent[19] * 0.3),0);
        // $invent[20] 中山課長代理 5%
        $t_roumu += Uround(($invent[20] * 0.05),0);
    } elseif($yyyymm >= 201510 && $yyyymm <= 201603) {
        // $invent[17] 小森谷部長 10%
        $t_roumu += Uround(($invent[17] * 0.1),0);
        // $invent[18] 大房部長代理 50%
        $t_roumu += Uround(($invent[18] * 0.5),0);
        // $invent[19] 千田課長 30%
        $t_roumu += Uround(($invent[19] * 0.3),0);
        // $invent[20] 中山課長代理 5%
        $t_roumu += Uround(($invent[20] * 0.05),0);
    } elseif($yyyymm >= 201604 && $yyyymm <= 201703) {
        // $invent[17] 小森谷副工場長 10%
        $t_roumu += Uround(($invent[17] * 0.1),0);
        // $invent[18] 大房部長 10%
        $t_roumu += Uround(($invent[18] * 0.1),0);
        // $invent[19] 千田副部長 40%
        $t_roumu += Uround(($invent[19] * 0.4),0);
        // $invent[20] 中山課長 10%
        $t_roumu += Uround(($invent[20] * 0.1),0);
    } elseif($yyyymm >= 201704 && $yyyymm <= 201703) {
        // $invent[17] 小森谷副工場長 10%
        $t_roumu += Uround(($invent[17] * 0.1),0);
        // $invent[18] 大房部長 10%
        $t_roumu += Uround(($invent[18] * 0.1),0);
        // $invent[19] 千田副部長 80%
        $t_roumu += Uround(($invent[19] * 0.8),0);
        // $invent[20] 中山課長 10%
        $t_roumu += Uround(($invent[20] * 0.1),0);
        // $invent[21] 石崎課長代理 20%
        $t_roumu += Uround(($invent[21] * 0.2),0);
    } elseif($yyyymm >= 201804) {
        // $invent[17] 基本0 10％
        $t_roumu += Uround(($invent[17] * 0.1),0);
        // $invent[18] 大房部長 10%
        $t_roumu += Uround(($invent[18] * 0.1),0);
        // $invent[19] 千田副部長 10%
        $t_roumu += Uround(($invent[19] * 0.1),0);
        // $invent[20] 中山課長 10%
        $t_roumu += Uround(($invent[20] * 0.1),0);
        // $invent[21] 安田課長 80%
        if($yyyymm >= 202010) {
            $t_roumu += Uround(($invent[21] * 0.1),0);
        } else {
            $t_roumu += Uround(($invent[21] * 0.8),0);
        }
    }
    $query_chk = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='機工労務費'", $yyyymm);
    if (getResult($query_chk,$res_chk) <= 0) {      // 既登録済みのチェック
        // 新規登録
        $query = sprintf("insert into act_pl_history (pl_bs_ym, kin, note) values (%d, %d, '機工労務費')", $yyyymm, $t_roumu);
        if (query_affected_trans($con, $query) <= 0) {
            $_SESSION['s_sysmsg'] .= sprintf("機工労務費の登録に失敗<br>第 %d期 %d月",$ki,$tuki);
            query_affected_trans($con, "rollback");     // Transaction Rollback
            header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
            exit();
        }
    } else {
        $query = sprintf("update act_pl_history set kin=%d where pl_bs_ym=%d and note='機工労務費'", $t_roumu, $yyyymm);
        if (query_affected_trans($con, $query) <= 0) {
            $_SESSION['s_sysmsg'] .= sprintf("機工労務費の更新失敗<br>第 %d期 %d月",$ki,$tuki);
            query_affected_trans($con, "rollback");     // Transaction Rollback
            header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
            exit();
        }
    }
    // 機工製造経費
    $t_keihi = 0;
    // 560 部門経費
    $query = sprintf("SELECT sum(act_monthly) FROM act_summary WHERE act_yymm=%d and act_id=560 and actcod<=8000", $ym4);
    $res = array();
    getResult($query, $res);
    $t_keihi += $res[0][0];
    // 機工経費調整を追加 560部門以外で処理してしまった金額を追加
    $t_keihi += $invent[22];
    if ($yyyymm >= 202102) {
        $t_keihi = 0;
    }
    $query_chk = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='機工製造経費'", $yyyymm);
    if (getResult($query_chk,$res_chk) <= 0) {      // 既登録済みのチェック
        // 新規登録
        $query = sprintf("insert into act_pl_history (pl_bs_ym, kin, note) values (%d, %d, '機工製造経費')", $yyyymm, $t_keihi);
        if (query_affected_trans($con, $query) <= 0) {
            $_SESSION['s_sysmsg'] .= sprintf("機工製造経費の登録に失敗<br>第 %d期 %d月",$ki,$tuki);
            query_affected_trans($con, "rollback");     // Transaction Rollback
            header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
            exit();
        }
    } else {
        $query = sprintf("update act_pl_history set kin=%d where pl_bs_ym=%d and note='機工製造経費'", $t_keihi, $yyyymm);
        if (query_affected_trans($con, $query) <= 0) {
            $_SESSION['s_sysmsg'] .= sprintf("機工製造経費の更新失敗<br>第 %d期 %d月",$ki,$tuki);
            query_affected_trans($con, "rollback");     // Transaction Rollback
            header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
            exit();
        }
    }

    //////////////////////////////////////  販管費登録
    //////////////////////////////////////  ここから入れる
    // バイモル販管費人件費の登録
    if ($l_total == 0) {
        $b_han_jin = 0;
    } elseif ($b_total == 0) {
        $b_han_jin = 0;
    } elseif ($l_jin == 0) {
        $b_han_jin = 0;
    } else {
        $b_han_jin = Uround(($l_jin * $b_total / $l_total),0);
    }
    $query_chk = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='バイモル人件費'", $yyyymm);
    if (getResult($query_chk,$res_chk) <= 0) {  ///// 既登録済みのチェック
        // 新規登録
        $query = sprintf("insert into act_pl_history (pl_bs_ym, kin, note) values (%d, %d, 'バイモル人件費')", $yyyymm, $b_han_jin);
        if (query_affected_trans($con, $query) <= 0) {
            $_SESSION['s_sysmsg'] .= sprintf("バイモル人件費の登録に失敗<br>第 %d期 %d月",$ki,$tuki);
            query_affected_trans($con, "rollback");     // Transaction Rollback
            header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
            exit();
        }
    } else {
        $query = sprintf("update act_pl_history set kin=%d where pl_bs_ym=%d and note='バイモル人件費'", $b_han_jin, $yyyymm);
        if (query_affected_trans($con, $query) <= 0) {
            $_SESSION['s_sysmsg'] .= sprintf("バイモル人件費の更新失敗<br>第 %d期 %d月",$ki,$tuki);
            query_affected_trans($con, "rollback");     // Transaction Rollback
            header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
            exit();
        }
    }
    // 試験修理販管費人件費の登録
    if ($l_total == 0) {
        $s_han_jin = 0;
    } elseif ($s_total == 0) {
        $s_han_jin = 0;
    } elseif ($l_jin == 0) {
        $s_han_jin = 0;
    } else {
        $s_han_jin = Uround(($l_jin * $s_total / $l_total),0);
    }
    $query_chk = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='試修人件費'", $yyyymm);
    if (getResult($query_chk,$res_chk) <= 0) {  ///// 既登録済みのチェック
        // 新規登録
        $query = sprintf("insert into act_pl_history (pl_bs_ym, kin, note) values (%d, %d, '試修人件費')", $yyyymm, $s_han_jin);
        if (query_affected_trans($con, $query) <= 0) {
            $_SESSION['s_sysmsg'] .= sprintf("試修人件費の登録に失敗<br>第 %d期 %d月",$ki,$tuki);
            query_affected_trans($con, "rollback");     // Transaction Rollback
            header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
            exit();
        }
    } else {
        $query = sprintf("update act_pl_history set kin=%d where pl_bs_ym=%d and note='試修人件費'", $s_han_jin, $yyyymm);
        if (query_affected_trans($con, $query) <= 0) {
            $_SESSION['s_sysmsg'] .= sprintf("試修人件費の更新失敗<br>第 %d期 %d月",$ki,$tuki);
            query_affected_trans($con, "rollback");     // Transaction Rollback
            header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
            exit();
        }
    }
    // 機工販管費人件費の登録
    $t_han_jin = Uround((($l_jin - $s_han_jin) * $tool_allo),0);
    if($yyyymm == 202010) {
        $t_han_jin = $t_han_jin - 97000;
    }
    if($yyyymm >= 202102) {
        $t_han_jin = 0;
    }
    $query_chk = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='機工人件費'", $yyyymm);
    if (getResult($query_chk,$res_chk) <= 0) {  ///// 既登録済みのチェック
        // 新規登録
        $query = sprintf("insert into act_pl_history (pl_bs_ym, kin, note) values (%d, %d, '機工人件費')", $yyyymm, $t_han_jin);
        if (query_affected_trans($con, $query) <= 0) {
            $_SESSION['s_sysmsg'] .= sprintf("機工人件費の登録に失敗<br>第 %d期 %d月",$ki,$tuki);
            query_affected_trans($con, "rollback");     // Transaction Rollback
            header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
            exit();
        }
    } else {
        $query = sprintf("update act_pl_history set kin=%d where pl_bs_ym=%d and note='機工人件費'", $t_han_jin, $yyyymm);
        if (query_affected_trans($con, $query) <= 0) {
            $_SESSION['s_sysmsg'] .= sprintf("機工人件費の更新失敗<br>第 %d期 %d月",$ki,$tuki);
            query_affected_trans($con, "rollback");     // Transaction Rollback
            header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
            exit();
        }
    }
    // バイモル販管費経費の登録
    if ($l_total == 0) {
        $b_han_kei = 0;
    } elseif ($b_total == 0) {
        $b_han_kei = 0;
    } elseif ($l_kei == 0) {
        $b_han_kei = 0;
    } else {
        $b_han_kei = Uround(($l_kei * $b_total / $l_total),0);
    }
    $query_chk = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='バイモル販管費経費'", $yyyymm);
    if (getResult($query_chk,$res_chk) <= 0) {  ///// 既登録済みのチェック
        // 新規登録
        $query = sprintf("insert into act_pl_history (pl_bs_ym, kin, note) values (%d, %d, 'バイモル販管費経費')", $yyyymm, $b_han_kei);
        if (query_affected_trans($con, $query) <= 0) {
            $_SESSION['s_sysmsg'] .= sprintf("バイモル販管費経費の登録に失敗<br>第 %d期 %d月",$ki,$tuki);
            query_affected_trans($con, "rollback");     // Transaction Rollback
            header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
            exit();
        }
    } else {
        $query = sprintf("update act_pl_history set kin=%d where pl_bs_ym=%d and note='バイモル販管費経費'", $b_han_kei, $yyyymm);
        if (query_affected_trans($con, $query) <= 0) {
            $_SESSION['s_sysmsg'] .= sprintf("バイモル販管費経費の更新失敗<br>第 %d期 %d月",$ki,$tuki);
            query_affected_trans($con, "rollback");     // Transaction Rollback
            header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
            exit();
        }
    }
    // 試験修理販管費経費の登録
    if ($l_total == 0) {
        $s_han_kei = 0;
    } elseif ($s_total == 0) {
        $s_han_kei = 0;
    } elseif ($l_kei == 0) {
        $s_han_kei = 0;
    } else {
        $s_han_kei = Uround(($l_kei * $s_total / $l_total),0);
    }
    $query_chk = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='試修販管費経費'", $yyyymm);
    if (getResult($query_chk,$res_chk) <= 0) {  ///// 既登録済みのチェック
        // 新規登録
        $query = sprintf("insert into act_pl_history (pl_bs_ym, kin, note) values (%d, %d, '試修販管費経費')", $yyyymm, $s_han_kei);
        if (query_affected_trans($con, $query) <= 0) {
            $_SESSION['s_sysmsg'] .= sprintf("試修販管費経費の登録に失敗<br>第 %d期 %d月",$ki,$tuki);
            query_affected_trans($con, "rollback");     // Transaction Rollback
            header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
            exit();
        }
    } else {
        $query = sprintf("update act_pl_history set kin=%d where pl_bs_ym=%d and note='試修販管費経費'", $s_han_kei, $yyyymm);
        if (query_affected_trans($con, $query) <= 0) {
            $_SESSION['s_sysmsg'] .= sprintf("試修販管費経費の更新失敗<br>第 %d期 %d月",$ki,$tuki);
            query_affected_trans($con, "rollback");     // Transaction Rollback
            header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
            exit();
        }
    }
    // 機工販管費経費の登録
    $t_han_kei = Uround((($l_kei - $s_han_kei) * $tool_allo),0);
    if($yyyymm == 202010) {
        $t_han_kei = $t_han_kei - 95000;
    }
    if($yyyymm >= 202102) {
        $t_han_kei = 0;
    }
    $query_chk = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='機工販管費経費'", $yyyymm);
    if (getResult($query_chk,$res_chk) <= 0) {  ///// 既登録済みのチェック
        // 新規登録
        $query = sprintf("insert into act_pl_history (pl_bs_ym, kin, note) values (%d, %d, '機工販管費経費')", $yyyymm, $t_han_kei);
        if (query_affected_trans($con, $query) <= 0) {
            $_SESSION['s_sysmsg'] .= sprintf("機工販管費経費の登録に失敗<br>第 %d期 %d月",$ki,$tuki);
            query_affected_trans($con, "rollback");     // Transaction Rollback
            header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
            exit();
        }
    } else {
        $query = sprintf("update act_pl_history set kin=%d where pl_bs_ym=%d and note='機工販管費経費'", $t_han_kei, $yyyymm);
        if (query_affected_trans($con, $query) <= 0) {
            $_SESSION['s_sysmsg'] .= sprintf("機工販管費経費の更新失敗<br>第 %d期 %d月",$ki,$tuki);
            query_affected_trans($con, "rollback");     // Transaction Rollback
            header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
            exit();
        }
    }
    // 試験修理 CL 配賦再計算
    ///////// 項目とインデックスの関連付け
    if ($yyyymm >= 200911) {
        $ss_item = array();
        $ss_item[0]   = "試修労務費";
        $ss_item[1]   = "試修製造経費";
        $ss_item[2]   = "試修人件費";
        $ss_item[3]   = "試修販管費経費";
        $ss_item[4]   = "試修業務委託収入";
        $ss_item[5]   = "試修仕入割引";
        $ss_item[6]   = "試修営業外収益その他";
        $ss_item[7]   = "試修支払利息";
        $ss_item[8]   = "試修営業外費用その他";
        $ss_item[9]   = "カプラ試修労務費";
        $ss_item[10]  = "カプラ試修製造経費";
        $ss_item[11]  = "カプラ試修人件費";
        $ss_item[12]  = "カプラ試修販管費経費";
        $ss_item[13]  = "カプラ試修業務委託収入";
        $ss_item[14]  = "カプラ試修仕入割引";
        $ss_item[15]  = "カプラ試修営業外収益その他";
        $ss_item[16]  = "カプラ試修支払利息";
        $ss_item[17]  = "カプラ試修営業外費用その他";
        $ss_item[18]  = "リニア試修労務費";
        $ss_item[19]  = "リニア試修製造経費";
        $ss_item[20]  = "リニア試修人件費";
        $ss_item[21]  = "リニア試修販管費経費";
        $ss_item[22]  = "リニア試修業務委託収入";
        $ss_item[23]  = "リニア試修仕入割引";
        $ss_item[24]  = "リニア試修営業外収益その他";
        $ss_item[25]  = "リニア試修支払利息";
        $ss_item[26]  = "リニア試修営業外費用その他";
        ////////// 登録済みならば金額取得
        $ss_invent = array();
        for ($i = 0; $i < 27; $i++) {
            $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='%s'", $yyyymm, $ss_item[$i]);
            $res = array();
            if (getResult2($query,$res) > 0) {
                $ss_invent[$i] = $res[0][0];
            }
        }
        // カプラ
        $ss_roumu      = $ss_invent[0] - $allkyu_kin + $skyu_kin;
        $ss_invent[9]  = Uround(($ss_roumu * $sc_allo),0);
        //$ss_invent[9]  = Uround(($ss_invent[0] * $sc_allo),0);
        $ss_invent[10] = Uround(($ss_invent[1] * $sc_allo),0);
        $ss_invent[11] = Uround(($ss_invent[2] * $sc_allo),0);
        $ss_invent[12] = Uround(($ss_invent[3] * $sc_allo),0);
        $ss_invent[13] = Uround(($ss_invent[4] * $sc_allo),0);
        $ss_invent[14] = Uround(($ss_invent[5] * $sc_allo),0);
        $ss_invent[15] = Uround(($ss_invent[6] * $sc_allo),0);
        $ss_invent[16] = Uround(($ss_invent[7] * $sc_allo),0);
        $ss_invent[17] = Uround(($ss_invent[8] * $sc_allo),0);
        // リニア
        $ss_invent[18] = $ss_roumu - $ss_invent[9];
        //$ss_invent[18] = $ss_invent[0] - $ss_invent[9];
        $ss_invent[19] = $ss_invent[1] - $ss_invent[10];
        $ss_invent[20] = $ss_invent[2] - $ss_invent[11];
        $ss_invent[21] = $ss_invent[3] - $ss_invent[12];
        $ss_invent[22] = $ss_invent[4] - $ss_invent[13];
        $ss_invent[23] = $ss_invent[5] - $ss_invent[14];
        $ss_invent[24] = $ss_invent[6] - $ss_invent[15];
        $ss_invent[25] = $ss_invent[7] - $ss_invent[16];
        $ss_invent[26] = $ss_invent[8] - $ss_invent[17];
        
        for ($i = 0; $i < 27; $i++) {
            $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='%s'", $yyyymm, $ss_item[$i]);
            $res = array();
            if (getResult2($query,$res) <= 0) {
                /////////// begin トランザクション開始
                if ($con = db_connect()) {
                    query_affected_trans($con, "begin");
                } else {
                    $_SESSION["s_sysmsg"] .= "データベースに接続できません";
                    header("Location: $current_script");
                    exit();
                }
                ////////// Insert Start
                $query = sprintf("insert into act_pl_history (pl_bs_ym, kin, note) values (%d, %d, '%s')", $yyyymm, $ss_invent[$i], $ss_item[$i]);
                if (query_affected_trans($con, $query) <= 0) {
                    $_SESSION["s_sysmsg"] .= sprintf("%sの新規登録に失敗<br>第 %d期 %d月", $ss_item[$i], $ki, $tuki);
                    query_affected_trans($con, "rollback");     // transaction rollback
                    header("Location: $current_script");
                    exit();
                }
                /////////// commit トランザクション終了
                query_affected_trans($con, "commit");
                $_SESSION["s_sysmsg"] = sprintf("<font color='yellow'>第%d期 %d月 BLS人員比較計算データ 新規 登録完了</font>",$ki,$tuki);
            } else {
                /////////// begin トランザクション開始
                if ($con = db_connect()) {
                    query_affected_trans($con, "begin");
                } else {
                    $_SESSION["s_sysmsg"] .= "データベースに接続できません";
                    header("Location: $current_script");
                    exit();
                }
                ////////// UPDATE Start
                $query = sprintf("update act_pl_history set kin=%d where pl_bs_ym=%d and note='%s'", $ss_invent[$i], $yyyymm, $ss_item[$i]);
                if (query_affected_trans($con, $query) <= 0) {
                    $_SESSION["s_sysmsg"] .= sprintf("%sのUPDATEに失敗<br>第 %d期 %d月", $ss_item[$i], $ki, $tuki);
                    query_affected_trans($con, "rollback");     // transaction rollback
                    header("Location: $current_script");
                    exit();
                }
                /////////// commit トランザクション終了
                query_affected_trans($con, "commit");
                $_SESSION["s_sysmsg"] = sprintf("<font color='yellow'>第%d期 %d月 BLS人員比較計算データ 変更 完了</font>",$ki,$tuki);
            }
        }
        // 耐久・修理損益計算用
        // 労務費計算
        //$ss_roumu  = $roumu_559 + $invent[15];  // 暫定修理労務費は559部門労務費と笹崎さんの給与合計
        $ss_roumu  = $invent[15];  // 暫定修理労務費は559部門労務費と笹崎さんの給与合計
        $st_roumu  = $roumu_581 + Uround(($invent[16] * -0.2),0) + Uround(($invent[23] * 0.2),0);  // 暫定耐久労務費は581部門労務費と安達さんの給与の8割の合計と安田さんの給与20％
        
        // 修理・耐久の労務費計算
        $s_roumu_all  = $ss_invent[0];
        $roumu_sagaku = $s_roumu_all - $ss_roumu - $st_roumu;
        if($roumu_sagaku <> 0) {
            //$ss_roumu_sagaku = Uround(($roumu_sagaku * $ss_uri_allo), 0);
            $ss_roumu_sagaku = 0;
            $st_roumu_sagaku = $roumu_sagaku - $ss_roumu_sagaku;
            $ss_roumu    = $ss_roumu + $ss_roumu_sagaku;
            $st_roumu    = $st_roumu + $st_roumu_sagaku;
        }
        // 製造経費計算
        // 559 部門経費 修理製造経費
        $query = sprintf("SELECT sum(act_monthly) FROM act_summary WHERE act_yymm=%d and act_id=559 and actcod<=8000", $ym4);
        $res = array();
        getResult($query, $res);
        $ss_keihi = $res[0][0];
        // 581 部門経費 耐久製造経費
        $query = sprintf("SELECT sum(act_monthly) FROM act_summary WHERE act_yymm=%d and act_id=581 and actcod<=8000", $ym4);
        $res = array();
        getResult($query, $res);
        $st_keihi = $res[0][0];
        
        // 修理・耐久の製造経費計算
        $s_keihi_all  = $ss_invent[1];
        $keihi_sagaku = $s_keihi_all - $ss_keihi - $st_keihi;
        if($keihi_sagaku <> 0) {
            $ss_keihi_sagaku = Uround(($keihi_sagaku * $ss_uri_allo), 0);
            $st_keihi_sagaku = $keihi_sagaku - $ss_keihi_sagaku;
            $ss_keihi    = $ss_keihi + $ss_keihi_sagaku;
            $st_keihi    = $st_keihi + $st_keihi_sagaku;
        }
        
        // 人件費計算
        $s_han_jin_all = $ss_invent[2];
        $st_han_jin    = Uround(($s_han_jin_all * $st_uri_allo), 0);
        $ss_han_jin    = $s_han_jin_all - $st_han_jin;
        
        // 販管費経費計算
        $s_han_kei_all  = $ss_invent[3];
        $st_han_kei    = Uround(($s_han_kei_all * $st_uri_allo), 0);
        $ss_han_kei    = $s_han_kei_all - $st_han_kei;
        
        $query_chk = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='修理労務費'", $yyyymm);
        if (getResult($query_chk,$res_chk) <= 0) {      // 既登録済みのチェック
            // 新規登録
            $query = sprintf("insert into act_pl_history (pl_bs_ym, kin, note) values (%d, %d, '修理労務費')", $yyyymm, $ss_roumu);
            if (query_affected_trans($con, $query) <= 0) {
                $_SESSION['s_sysmsg'] .= sprintf("修理労務費の登録に失敗<br>第 %d期 %d月",$ki,$tuki);
                query_affected_trans($con, "rollback");     // Transaction Rollback
                header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
                exit();
            }
        } else {
            $query = sprintf("update act_pl_history set kin=%d where pl_bs_ym=%d and note='修理労務費'", $ss_roumu, $yyyymm);
            if (query_affected_trans($con, $query) <= 0) {
                $_SESSION['s_sysmsg'] .= sprintf("修理労務費の更新失敗<br>第 %d期 %d月",$ki,$tuki);
                query_affected_trans($con, "rollback");     // Transaction Rollback
                header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
                exit();
            }
        }
        $query_chk = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='耐久労務費'", $yyyymm);
        if (getResult($query_chk,$res_chk) <= 0) {      // 既登録済みのチェック
            // 新規登録
            $query = sprintf("insert into act_pl_history (pl_bs_ym, kin, note) values (%d, %d, '耐久労務費')", $yyyymm, $st_roumu);
            if (query_affected_trans($con, $query) <= 0) {
                $_SESSION['s_sysmsg'] .= sprintf("修理労務費の登録に失敗<br>第 %d期 %d月",$ki,$tuki);
                query_affected_trans($con, "rollback");     // Transaction Rollback
                header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
                exit();
            }
        } else {
            $query = sprintf("update act_pl_history set kin=%d where pl_bs_ym=%d and note='耐久労務費'", $st_roumu, $yyyymm);
            if (query_affected_trans($con, $query) <= 0) {
                $_SESSION['s_sysmsg'] .= sprintf("修理労務費の更新失敗<br>第 %d期 %d月",$ki,$tuki);
                query_affected_trans($con, "rollback");     // Transaction Rollback
                header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
                exit();
            }
        }
        // 製造経費
        $query_chk = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='修理製造経費'", $yyyymm);
        if (getResult($query_chk,$res_chk) <= 0) {      // 既登録済みのチェック
            // 新規登録
            $query = sprintf("insert into act_pl_history (pl_bs_ym, kin, note) values (%d, %d, '修理製造経費')", $yyyymm, $ss_keihi);
            if (query_affected_trans($con, $query) <= 0) {
                $_SESSION['s_sysmsg'] .= sprintf("修理製造経費の登録に失敗<br>第 %d期 %d月",$ki,$tuki);
                query_affected_trans($con, "rollback");     // Transaction Rollback
                header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
                exit();
            }
        } else {
            $query = sprintf("update act_pl_history set kin=%d where pl_bs_ym=%d and note='修理製造経費'", $ss_keihi, $yyyymm);
            if (query_affected_trans($con, $query) <= 0) {
                $_SESSION['s_sysmsg'] .= sprintf("修理製造経費の更新失敗<br>第 %d期 %d月",$ki,$tuki);
                query_affected_trans($con, "rollback");     // Transaction Rollback
                header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
                exit();
            }
        }
        $query_chk = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='耐久製造経費'", $yyyymm);
        if (getResult($query_chk,$res_chk) <= 0) {      // 既登録済みのチェック
            // 新規登録
            $query = sprintf("insert into act_pl_history (pl_bs_ym, kin, note) values (%d, %d, '耐久製造経費')", $yyyymm, $st_keihi);
            if (query_affected_trans($con, $query) <= 0) {
                $_SESSION['s_sysmsg'] .= sprintf("耐久製造経費の登録に失敗<br>第 %d期 %d月",$ki,$tuki);
                query_affected_trans($con, "rollback");     // Transaction Rollback
                header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
                exit();
            }
        } else {
            $query = sprintf("update act_pl_history set kin=%d where pl_bs_ym=%d and note='耐久製造経費'", $st_keihi, $yyyymm);
            if (query_affected_trans($con, $query) <= 0) {
                $_SESSION['s_sysmsg'] .= sprintf("耐久製造経費の更新失敗<br>第 %d期 %d月",$ki,$tuki);
                query_affected_trans($con, "rollback");     // Transaction Rollback
                header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
                exit();
            }
        }
        $query_chk = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='修理人件費'", $yyyymm);
        if (getResult($query_chk,$res_chk) <= 0) {      // 既登録済みのチェック
            // 新規登録
            $query = sprintf("insert into act_pl_history (pl_bs_ym, kin, note) values (%d, %d, '修理人件費')", $yyyymm, $ss_han_jin);
            if (query_affected_trans($con, $query) <= 0) {
                $_SESSION['s_sysmsg'] .= sprintf("修理人件費の登録に失敗<br>第 %d期 %d月",$ki,$tuki);
                query_affected_trans($con, "rollback");     // Transaction Rollback
                header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
                exit();
            }
        } else {
            $query = sprintf("update act_pl_history set kin=%d where pl_bs_ym=%d and note='修理人件費'", $ss_han_jin, $yyyymm);
            if (query_affected_trans($con, $query) <= 0) {
                $_SESSION['s_sysmsg'] .= sprintf("修理人件費の更新失敗<br>第 %d期 %d月",$ki,$tuki);
                query_affected_trans($con, "rollback");     // Transaction Rollback
                header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
                exit();
            }
        }
        $query_chk = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='耐久人件費'", $yyyymm);
        if (getResult($query_chk,$res_chk) <= 0) {      // 既登録済みのチェック
            // 新規登録
            $query = sprintf("insert into act_pl_history (pl_bs_ym, kin, note) values (%d, %d, '耐久人件費')", $yyyymm, $st_han_jin);
            if (query_affected_trans($con, $query) <= 0) {
                $_SESSION['s_sysmsg'] .= sprintf("耐久人件費の登録に失敗<br>第 %d期 %d月",$ki,$tuki);
                query_affected_trans($con, "rollback");     // Transaction Rollback
                header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
                exit();
            }
        } else {
            $query = sprintf("update act_pl_history set kin=%d where pl_bs_ym=%d and note='耐久人件費'", $st_han_jin, $yyyymm);
            if (query_affected_trans($con, $query) <= 0) {
                $_SESSION['s_sysmsg'] .= sprintf("耐久人件費の更新失敗<br>第 %d期 %d月",$ki,$tuki);
                query_affected_trans($con, "rollback");     // Transaction Rollback
                header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
                exit();
            }
        }
        $query_chk = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='修理販管費経費'", $yyyymm);
        if (getResult($query_chk,$res_chk) <= 0) {      // 既登録済みのチェック
            // 新規登録
            $query = sprintf("insert into act_pl_history (pl_bs_ym, kin, note) values (%d, %d, '修理販管費経費')", $yyyymm, $ss_han_kei);
            if (query_affected_trans($con, $query) <= 0) {
                $_SESSION['s_sysmsg'] .= sprintf("修理販管費経費の登録に失敗<br>第 %d期 %d月",$ki,$tuki);
                query_affected_trans($con, "rollback");     // Transaction Rollback
                header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
                exit();
            }
        } else {
            $query = sprintf("update act_pl_history set kin=%d where pl_bs_ym=%d and note='修理販管費経費'", $ss_han_kei, $yyyymm);
            if (query_affected_trans($con, $query) <= 0) {
                $_SESSION['s_sysmsg'] .= sprintf("修理販管費経費の更新失敗<br>第 %d期 %d月",$ki,$tuki);
                query_affected_trans($con, "rollback");     // Transaction Rollback
                header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
                exit();
            }
        }
        $query_chk = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='耐久販管費経費'", $yyyymm);
        if (getResult($query_chk,$res_chk) <= 0) {      // 既登録済みのチェック
            // 新規登録
            $query = sprintf("insert into act_pl_history (pl_bs_ym, kin, note) values (%d, %d, '耐久販管費経費')", $yyyymm, $st_han_kei);
            if (query_affected_trans($con, $query) <= 0) {
                $_SESSION['s_sysmsg'] .= sprintf("耐久販管費経費の登録に失敗<br>第 %d期 %d月",$ki,$tuki);
                query_affected_trans($con, "rollback");     // Transaction Rollback
                header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
                exit();
            }
        } else {
            $query = sprintf("update act_pl_history set kin=%d where pl_bs_ym=%d and note='耐久販管費経費'", $st_han_kei, $yyyymm);
            if (query_affected_trans($con, $query) <= 0) {
                $_SESSION['s_sysmsg'] .= sprintf("耐久販管費経費の更新失敗<br>第 %d期 %d月",$ki,$tuki);
                query_affected_trans($con, "rollback");     // Transaction Rollback
                header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
                exit();
            }
        }
    }
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
        if (c == ".") {
            return true;
        }
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
    document.invent.invent_1.focus();
    document.invent.invent_1.select();
}
function data_copy_click(obj) {
    return confirm("前月のデータをコピーします。\n既にデータがある場合は上書きされます。");
}
// -->
</script>
<style type="text/css">
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
.pt11 {
    font-size:11pt;
    font-family: monospace;
}
.pt11b {
    font:bold 11pt;
    font-family: monospace;
}
.pt12b {
    font:bold 12pt;
    font-family: monospace;
}
th {
    font:bold 11pt;
    font-family: monospace;
}
.title_font {
    font:bold 14pt;
    font-family: monospace;
}
.today_font {
    font-size: 10.5pt;
    font-family: monospace;
}
.right{
    text-align:right;
    font:bold 12pt;
    font-family: monospace;
}
.rightb{
    text-align:right;
    font:bold 12pt;
    font-family: monospace;
    background-color: '#e6e6e6';
}
.rightbg{
    text-align:right;
    font:bold 12pt;
    font-family: monospace;
    background-color: '#ccffcc';
}
.rightby{
    text-align:right;
    font:bold 12pt;
    font-family: monospace;
    background-color: '#ffffcc';
}
.rightbb{
    text-align:right;
    font:bold 12pt;
    font-family: monospace;
    background-color: '#ccffff';
}
.margin0 {
    margin:0%;
}
-->
</style>
</head>
<body>
    <center>
<?= $menu->out_title_border() ?>
        <form name='invent' action='<?php echo $menu->out_self() ?>' method='post'>
        <table bgcolor='#d6d3ce' align='center' cellspacing='0' cellpadding='3' border='1'>
            <tr><td> <!-- ダミー(デザイン用) -->
            <table bgcolor='#d6d3ce' cellspacing='0' cellpadding='2' border='1'>
                <th colspan='3' bgcolor='#ccffcc' width='110'>　</th><th bgcolor='#ccffcc' width='110'><?php echo $p1_ym ?></th><th bgcolor='#ccffcc' width='110'><?php echo $yyyymm ?></th>
                <tr>
                    <td rowspan='4' align='center' bgcolor='#ccffcc' class='pt11b' colspan='2'>リ　ニ　ア</td>
                    <td align='center' bgcolor='white' class='pt11b'>社員数</td>
                    <td align='center' class='rightbg'>
                        <input type='hidden' name='invent_z[]' value='<?php echo $invent_z[0] ?>'>
                        <?php echo $invent_z[0] ?>
                    </td>
                    <td align='center' bgcolor='white'>
                        <input type='text' name='invent[]' size='11' maxlength='11' value='<?php echo $invent[0] ?>' class='right' onChange='return isDigit(value);'>
                    </td>
                </tr>
                <tr>
                    <td align='center' bgcolor='white' class='pt11b'>パート数</td>
                    <td align='center' class='rightbg'>
                        <input type='hidden' name='invent_z[]' value='<?php echo $invent_z[1] ?>'>
                        <?php echo $invent_z[1] ?>
                    </td>
                    <td align='center' bgcolor='white'>
                        <input type='text' name='invent[]' size='11' maxlength='11' value='<?php echo $invent[1] ?>' class='right' onChange='return isDigit(value);'>
                    </td>
                </tr>
                <tr>
                    <td align='center' bgcolor='#ccffcc' class='pt11b'>パート掛率50％</td>
                    <td bgcolor='#ccffcc' class='rightbg'>
                        <?php echo $l_part50_z ?>
                    </td>
                    <td bgcolor='#ccffcc' class='rightbg'>
                        <?php echo $l_part50 ?>
                    </td>
                </tr>
                <tr>
                    <td align='center' bgcolor='#ccffcc' class='pt11b'>計</td>
                    <td bgcolor='#ccffcc' class='rightbg'>
                        <?php echo $l_total_z ?>
                    </td>
                    <td bgcolor='#ccffcc' class='rightbg'>
                        <?php echo $l_total ?>
                    </td>
                </tr>
                <tr>
                    <td rowspan='4' align='center' bgcolor='#ffffcc' class='pt11b' colspan='2'>液体ポンプ</td>
                    <td align='center' bgcolor='#ffffcc' class='pt11b'>社員数</td>
                    <td bgcolor='#ccffcc' class='rightby'>
                        <?php echo $b_shain_z ?>
                    </td>
                    <td bgcolor='#ccffcc' class='rightby'>
                        <?php echo $b_shain ?>
                    </td>
                </tr>
                <tr>
                    <td align='center' bgcolor='white' class='pt11b'>パート数</td>
                    <td align='center' class='rightby'>
                        <input type='hidden' name='invent_z[]' value='<?php echo $invent_z[2] ?>'>
                        <?php echo $invent_z[2] ?>
                    </td>
                    <td align='center' bgcolor='white'>
                        <input type='text' name='invent[]' size='11' maxlength='11' value='<?php echo $invent[2] ?>' class='right' onChange='return isDigit(value);'>
                    </td>
                </tr>
                <tr>
                    <td align='center' bgcolor='#ffffcc' class='pt11b'>パート掛率50％</td>
                    <td bgcolor='#ccffcc' class='rightby'>
                        <?php echo $b_part50_z ?>
                    </td>
                    <td bgcolor='#ccffcc' class='rightby'>
                        <?php echo $b_part50 ?>
                    </td>
                </tr>
                <tr>
                    <td align='center' bgcolor='#ffffcc' class='pt11b'>計</td>
                    <td bgcolor='#ccffcc' class='rightby'>
                        <?php echo $b_total_z ?>
                    </td>
                    <td bgcolor='#ccffcc' class='rightby'>
                        <?php echo $b_total ?>
                    </td>
                </tr>
                <tr>
                    <td rowspan='4' align='center' bgcolor='#ccffff' class='pt11b' colspan='2'>試験・修理</td>
                    <td align='center' bgcolor='#ccffff' class='pt11b'>社員数</td>
                    <td bgcolor='#ccffcc' class='rightbb'>
                        <?php echo $s_shain_z ?>
                    </td>
                    <td bgcolor='#ccffcc' class='rightbb'>
                        <?php echo $s_shain ?>
                    </td>
                </tr>
                <tr>
                    <td align='center' bgcolor='white' class='pt11b'>パート数</td>
                    <td align='center' class='rightbb'>
                        <input type='hidden' name='invent_z[]' value='<?php echo $invent_z[3] ?>'>
                        <?php echo $invent_z[3] ?>
                    </td>
                    <td align='center' bgcolor='white'>
                        <input type='text' name='invent[]' size='11' maxlength='11' value='<?php echo $invent[3] ?>' class='right' onChange='return isDigit(value);'>
                    </td>
                </tr>
                <tr>
                    <td align='center' bgcolor='#ccffff' class='pt11b'>パート掛率50％</td>
                    <td bgcolor='#ccffcc' class='rightbb'>
                        <?php echo $s_part50_z ?>
                    </td>
                    <td bgcolor='#ccffcc' class='rightbb'>
                        <?php echo $s_part50 ?>
                    </td>
                </tr>
                <tr>
                    <td align='center' bgcolor='#ccffff' class='pt11b'>計</td>
                    <td bgcolor='#ccffcc' class='rightbb'>
                        <?php echo $s_total_z ?>
                    </td>
                    <td bgcolor='#ccffcc' class='rightbb'>
                        <?php echo $s_total ?>
                    </td>
                </tr>
                <tr>
                    <td rowspan='3' align='center' bgcolor='#ffffcc' class='pt11b'>
                    液体ポンプ社員数
                    <br>
                    計算<font color='red'>※１</font>
                    </td>
                    <?php
                    if ($yyyymm < 201704) {
                    ?>
                    <td align='center' bgcolor='white' class='pt11b'>千田課長</td>
                    <?php
                    } elseif ($yyyymm < 201804) {
                    ?>
                    <td align='center' bgcolor='white' class='pt11b'>石崎課長代理</td>
                    <?php
                    } else {
                    ?>
                    <td align='center' bgcolor='white' class='pt11b'>安田課長</td>
                    <?php
                    }
                    ?>
                    <td align='center' bgcolor='white' class='pt11b'>10%</td>
                    <td align='center' class='rightby'>
                        <input type='hidden' name='invent_z[]' value='<?php echo $invent_z[4] ?>'>
                        <?php echo $invent_z[4] ?>
                    </td>
                    <td align='center' bgcolor='white'>
                        <input type='text' name='invent[]' size='11' maxlength='11' value='<?php echo $invent[4] ?>' class='right' onChange='return isDigit(value);'>
                    </td>
                </tr>
                <tr>
                    <td align='center' bgcolor='white' class='pt11b'>　</td>
                    <td align='center' bgcolor='white' class='pt11b'>　</td>
                    <td align='center' class='rightby'>
                        <input type='hidden' name='invent_z[]' value='<?php echo $invent_z[5] ?>'>
                        <?php echo $invent_z[5] ?>
                    </td>
                    <td align='center' bgcolor='white'>
                        <input type='text' name='invent[]' size='11' maxlength='11' value='<?php echo $invent[5] ?>' class='right' onChange='return isDigit(value);'>
                    </td>
                </tr>
                <tr>
                    <td align='center' bgcolor='white' class='pt11b'>　</td>
                    <td align='center' bgcolor='white' class='pt11b'>　</td>
                    <td align='center' class='rightby'>
                        <input type='hidden' name='invent_z[]' value='<?php echo $invent_z[6] ?>'>
                        <?php echo $invent_z[6] ?>
                    </td>
                    <td align='center' bgcolor='white'>
                        <input type='text' name='invent[]' size='11' maxlength='11' value='<?php echo $invent[6] ?>' class='right' onChange='return isDigit(value);'>
                    </td>
                </tr>
                <tr>
                    <td rowspan='3' align='center' bgcolor='#ccffff' class='pt11b'>
                    試修社員数
                    <br>
                    計算<font color='red'>※１</font>
                    </td>
                    <td align='center' bgcolor='white' class='pt11b'>安達課員</td>
                    <td align='center' bgcolor='white' class='pt11b'>100%</td>
                    <td align='center' class='rightbb'>
                        <input type='hidden' name='invent_z[]' value='<?php echo $invent_z[7] ?>'>
                        <?php echo $invent_z[7] ?>
                    </td>
                    <td align='center' bgcolor='white'>
                        <input type='text' name='invent[]' size='11' maxlength='11' value='<?php echo $invent[7] ?>' class='right' onChange='return isDigit(value);'>
                    </td>
                </tr>
                <tr>
                    <?php
                    if ($yyyymm < 201507) {
                    ?>
                    <td align='center' bgcolor='white' class='pt11b'>萩野課長代理</td>
                    <?php
                    } elseif ($yyyymm < 201704) {
                    ?>
                    <td align='center' bgcolor='white' class='pt11b'>菊地課長</td>
                    <?php
                    } elseif ($yyyymm < 201804) {
                    ?>
                    <td align='center' bgcolor='white' class='pt11b'>安田課長代理</td>
                    <?php
                    } else {
                    ?>
                    <td align='center' bgcolor='white' class='pt11b'>萩野課長</td>
                    <?php
                    }
                    ?>
                    <td align='center' bgcolor='white' class='pt11b'>10%</td>
                    <td align='center' class='rightbb'>
                        <input type='hidden' name='invent_z[]' value='<?php echo $invent_z[8] ?>'>
                        <?php echo $invent_z[8] ?>
                    </td>
                    <td align='center' bgcolor='white'>
                        <input type='text' name='invent[]' size='11' maxlength='11' value='<?php echo $invent[8] ?>' class='right' onChange='return isDigit(value);'>
                    </td>
                </tr>
                <tr>
                    <td align='center' bgcolor='white' class='pt11b'>　</td>
                    <td align='center' bgcolor='white' class='pt11b'>　</td>
                    <td align='center' class='rightbb'>
                        <input type='hidden' name='invent_z[]' value='<?php echo $invent_z[9] ?>'>
                        <?php echo $invent_z[9] ?>
                    </td>
                    <td align='center' bgcolor='white'>
                        <input type='text' name='invent[]' size='11' maxlength='11' value='<?php echo $invent[9] ?>' class='right' onChange='return isDigit(value);'>
                    </td>
                </tr>
                <tr>
                    <td align='center' bgcolor='#ffffcc' class='pt11b' colspan='2'>給与配賦計算<br>(液体ポンプ)<font color='red'>※２入力しない</font></td>
                    <?php
                    if ($yyyymm < 201704) {
                    ?>
                    <td align='center' bgcolor='white' class='pt11b'>千田課長</td>
                    <?php
                    } elseif ($yyyymm < 201804) {
                    ?>
                    <td align='center' bgcolor='white' class='pt11b'>石崎課長代理</td>
                    <?php
                    } else {
                    ?>
                    <td align='center' bgcolor='white' class='pt11b'>安田課長</td>
                    <?php
                    }
                    ?>
                    <td align='center' class='rightby'>
                        <input type='hidden' name='invent_z[]' value='<?php echo $invent_z[10] ?>'>
                        <?php echo $invent_z[10] ?>
                    </td>
                    <td align='center' bgcolor='white'>
                        <input type='text' name='invent[]' size='11' maxlength='11' value='<?php echo $invent[10] ?>' class='right' onChange='return isDigit(value);'>
                    </td>
                </tr>
                <tr>
                    <td align='center' bgcolor='#ccffff' class='pt11b' colspan='2'>給与配賦計算<br>(試験・修理)<font color='red'>※２入力しない</font></td>
                    <?php
                    if ($yyyymm < 201507) {
                    ?>
                    <td align='center' bgcolor='white' class='pt11b'>萩野課長代理</td>
                    <?php
                    } elseif ($yyyymm < 201704) {
                    ?>
                    <td align='center' bgcolor='white' class='pt11b'>菊地課長</td>
                    <?php
                    } elseif ($yyyymm < 201804) {
                    ?>
                    <td align='center' bgcolor='white' class='pt11b'>安田課長代理</td>
                    <?php
                    } else {
                    ?>
                    <td align='center' bgcolor='white' class='pt11b'>萩野課長</td>
                    <?php
                    }
                    ?>
                    <td align='center' class='rightbb'>
                        <input type='hidden' name='invent_z[]' value='<?php echo $invent_z[11] ?>'>
                        <?php echo $invent_z[11] ?>
                    </td>
                    <td align='center' bgcolor='white'>
                        <input type='text' name='invent[]' size='11' maxlength='11' value='<?php echo $invent[11] ?>' class='right' onChange='return isDigit(value);'>
                    </td>
                </tr>
                <tr>
                    <td rowspan='7' align='center' bgcolor='#ccffcc' class='pt11b' colspan='2'>試修給与配賦<font color='red'>※３</font><BR>※\\Fs1\総務課専用\人事関係<BR>\ｼｮｰﾄﾊﾟｰﾄ・ｱﾙﾊﾞｲﾄ給与\2019年度 添田<BR>給与＋賞与<BR>※2019年4・5月は特殊</td>
                    <td align='center' bgcolor='white' class='pt11b'>カプラ配賦率</td>
                    <td align='center' class='rightbg'>
                        <input type='hidden' name='invent_z[]' value='<?php echo $invent_z[12] ?>'>
                        <?php echo $invent_z[12] ?>
                    </td>
                    <td align='center' bgcolor='white'>
                        <input type='text' name='invent[]' size='11' maxlength='11' value='<?php echo $invent[12] ?>' class='right' onChange='return isDigit(value);'>
                    </td>
                </tr>
                <tr>
                    <td align='center' bgcolor='#ccffcc' class='pt11b'>カプラ配賦金額</td>
                    <td align='center' class='rightbg'>
                        <input type='hidden' name='c_hai_kin_z' value='<?php echo $c_hai_kin_z ?>'>
                        <?php echo $c_hai_kin_z ?>
                    </td>
                    <td align='center' class='rightbg'>
                        <input type='hidden' name='c_hai_kin' value='<?php echo $c_hai_kin ?>'>
                        <?php echo $c_hai_kin ?>
                    </td>
                </tr>
                <tr>
                    <td align='center' bgcolor='white' class='pt11b'>リニア配賦率</td>
                    <td align='center' class='rightbg'>
                        <input type='hidden' name='invent_z[]' value='<?php echo $invent_z[13] ?>'>
                        <?php echo $invent_z[13] ?>
                    </td>
                    <td align='center' bgcolor='white'>
                        <input type='text' name='invent[]' size='11' maxlength='11' value='<?php echo $invent[13] ?>' class='right' onChange='return isDigit(value);'>
                    </td>
                </tr>
                <tr>
                    <td align='center' bgcolor='#ccffcc' class='pt11b'>リニア配賦金額</td>
                    <td align='center' class='rightbg'>
                        <input type='hidden' name='l_hai_kin_z' value='<?php echo $l_hai_kin_z ?>'>
                        <?php echo $l_hai_kin_z ?>
                    </td>
                    <td align='center' class='rightbg'>
                        <input type='hidden' name='l_hai_kin' value='<?php echo $l_hai_kin ?>'>
                        <?php echo $l_hai_kin ?>
                    </td>
                </tr>
                <tr>
                    <td align='center' bgcolor='white' class='pt11b'>試験修理配賦率</td>
                    <td align='center' class='rightbg'>
                        <input type='hidden' name='invent_z[]' value='<?php echo $invent_z[14] ?>'>
                        <?php echo $invent_z[14] ?>
                    </td>
                    <td align='center' bgcolor='white'>
                        <input type='text' name='invent[]' size='11' maxlength='11' value='<?php echo $invent[14] ?>' class='right' onChange='return isDigit(value);'>
                    </td>
                </tr>
                <tr>
                    <td align='center' bgcolor='#ccffcc' class='pt11b'>試験修理配賦金額</td>
                    <td align='center' class='rightbg'>
                        <input type='hidden' name='s_hai_kin_z' value='<?php echo $s_hai_kin_z ?>'>
                        <?php echo $s_hai_kin_z ?>
                    </td>
                    <td align='center' class='rightbg'>
                        <input type='hidden' name='s_hai_kin' value='<?php echo $s_hai_kin ?>'>
                        <?php echo $s_hai_kin ?>
                    </td>
                </tr>
                <tr>
                    <td align='center' bgcolor='white' class='pt11b'>給与配賦額</td>
                    <td align='center' class='rightbg'>
                        <input type='hidden' name='invent_z[]' value='<?php echo $invent_z[15] ?>'>
                        <?php echo $invent_z[15] ?>
                    </td>
                    <td align='center' bgcolor='white'>
                        <input type='text' name='invent[]' size='11' maxlength='11' value='<?php echo $invent[15] ?>' class='right' onChange='return isDigit(value);'>
                    </td>
                </tr>
                <tr>
                    <td align='center' bgcolor='#ccffff' class='pt11b' colspan='2'>給与配賦計算<br>(試験・修理)<font color='red'>※４入力しない</font></td>
                    <td align='center' bgcolor='white' class='pt11b'>安達課員</td>
                    <td align='center' class='rightbb'>
                        <input type='hidden' name='invent_z[]' value='<?php echo $invent_z[16] ?>'>
                        <?php echo $invent_z[16] ?>
                    </td>
                    <td align='center' bgcolor='white'>
                        <input type='text' name='invent[]' size='11' maxlength='11' value='<?php echo $invent[16] ?>' class='right' onChange='return isDigit(value);'>
                    </td>
                </tr>
                <tr>
                    <?php
                    if ($yyyymm >= 201704) {
                    ?>
                    <td rowspan='5' align='center' bgcolor='#ccffff' class='pt11b'>
                    <?php
                    } else {
                    ?>
                    <td rowspan='4' align='center' bgcolor='#ccffff' class='pt11b'>
                    <?php
                    }
                    ?>
                    機工配賦
                    <br>
                    計算<font color='red'>※５</font>
                    </td>
                    <?php
                    if ($yyyymm >= 201804) {
                    ?>
                    <td align='center' bgcolor='white' class='pt11b'>　</td>
                    <td align='center' bgcolor='white' class='pt11b'>10%</td>
                    <?php
                    } elseif ($yyyymm >= 201604) {
                    ?>
                    <td align='center' bgcolor='white' class='pt11b'>小森谷副工場長</td>
                    <td align='center' bgcolor='white' class='pt11b'>10%</td>
                    <?php
                    } elseif ($yyyymm >= 201510) {
                    ?>
                    <td align='center' bgcolor='white' class='pt11b'>小森谷部長</td>
                    <td align='center' bgcolor='white' class='pt11b'>10%</td>
                    <?php
                    } elseif ($yyyymm >= 201506) {
                    ?>
                    <td align='center' bgcolor='white' class='pt11b'>小森谷部長</td>
                    <td align='center' bgcolor='white' class='pt11b'>10%</td>
                    <?php
                    } else {
                    ?>
                    <td align='center' bgcolor='white' class='pt11b'>大房部長代理</td>
                    <td align='center' bgcolor='white' class='pt11b'>50%</td>
                    <?php
                    }
                    ?>
                    <td align='center' class='rightbb'>
                        <input type='hidden' name='invent_z[]' value='<?php echo $invent_z[17] ?>'>
                        <?php echo $invent_z[17] ?>
                    </td>
                    <td align='center' bgcolor='white'>
                        <input type='text' name='invent[]' size='11' maxlength='11' value='<?php echo $invent[17] ?>' class='right' onChange='return isDigit(value);'>
                    </td>
                </tr>
                <tr>
                    <?php
                    if ($yyyymm >= 201904) {
                    ?>
                    <td align='center' bgcolor='white' class='pt11b'>入江部長</td>
                    <td align='center' bgcolor='white' class='pt11b'>10%</td>
                    <?php
                    } elseif ($yyyymm >= 201604) {
                    ?>
                    <td align='center' bgcolor='white' class='pt11b'>大房部長</td>
                    <td align='center' bgcolor='white' class='pt11b'>10%</td>
                    <?php
                    } elseif ($yyyymm >= 201510) {
                    ?>
                    <td align='center' bgcolor='white' class='pt11b'>大房部長代理</td>
                    <td align='center' bgcolor='white' class='pt11b'>50%</td>
                    <?php
                    } elseif ($yyyymm >= 201506) {
                    ?>
                    <td align='center' bgcolor='white' class='pt11b'>大房部長代理</td>
                    <td align='center' bgcolor='white' class='pt11b'>50%</td>
                    <?php
                    } else {
                    ?>
                    <td align='center' bgcolor='white' class='pt11b'>千田課長</td>
                    <td align='center' bgcolor='white' class='pt11b'>5%</td>
                    <?php
                    }
                    ?>
                    <td align='center' class='rightbb'>
                        <input type='hidden' name='invent_z[]' value='<?php echo $invent_z[18] ?>'>
                        <?php echo $invent_z[18] ?>
                    </td>
                    <td align='center' bgcolor='white'>
                        <input type='text' name='invent[]' size='11' maxlength='11' value='<?php echo $invent[18] ?>' class='right' onChange='return isDigit(value);'>
                    </td>
                </tr>
                <tr>
                    <?php
                    if ($yyyymm >= 201904) {
                    ?>
                    <td align='center' bgcolor='white' class='pt11b'>中山部長代理</td>
                    <td align='center' bgcolor='white' class='pt11b'>10%</td>
                    <?php
                    } elseif ($yyyymm >= 201804) {
                    ?>
                    <td align='center' bgcolor='white' class='pt11b'>千田副部長</td>
                    <td align='center' bgcolor='white' class='pt11b'>10%</td>
                    <?php
                    } elseif ($yyyymm >= 201704) {
                    ?>
                    <td align='center' bgcolor='white' class='pt11b'>千田副部長</td>
                    <td align='center' bgcolor='white' class='pt11b'>80%</td>
                    <?php
                    } elseif ($yyyymm >= 201604) {
                    ?>
                    <td align='center' bgcolor='white' class='pt11b'>千田課長</td>
                    <td align='center' bgcolor='white' class='pt11b'>40%</td>
                    <?php
                    } elseif ($yyyymm >= 201510) {
                    ?>
                    <td align='center' bgcolor='white' class='pt11b'>千田課長</td>
                    <td align='center' bgcolor='white' class='pt11b'>30%</td>
                    <?php
                    } elseif ($yyyymm >= 201506) {
                    ?>
                    <td align='center' bgcolor='white' class='pt11b'>千田課長</td>
                    <td align='center' bgcolor='white' class='pt11b'>30%</td>
                    <?php
                    } else {
                    ?>
                    <td align='center' bgcolor='white' class='pt11b'>中山課長代理</td>
                    <td align='center' bgcolor='white' class='pt11b'>5%</td>
                    <?php
                    }
                    ?>
                    <td align='center' class='rightbb'>
                        <input type='hidden' name='invent_z[]' value='<?php echo $invent_z[19] ?>'>
                        <?php echo $invent_z[19] ?>
                    </td>
                    <td align='center' bgcolor='white'>
                        <input type='text' name='invent[]' size='11' maxlength='11' value='<?php echo $invent[19] ?>' class='right' onChange='return isDigit(value);'>
                    </td>
                </tr>
                <tr>
                    <?php
                    if ($yyyymm >= 201904) {
                    ?>
                    <td align='center' bgcolor='white' class='pt11b'>吉成課長代理</td>
                    <td align='center' bgcolor='white' class='pt11b'>10%</td>
                    <?php
                    } elseif ($yyyymm >= 201604) {
                    ?>
                    <td align='center' bgcolor='white' class='pt11b'>中山課長</td>
                    <td align='center' bgcolor='white' class='pt11b'>10%</td>
                    <?php
                    } elseif ($yyyymm >= 201510) {
                    ?>
                    <td align='center' bgcolor='white' class='pt11b'>中山課長代理</td>
                    <td align='center' bgcolor='white' class='pt11b'>5%</td>
                    <?php
                    } elseif ($yyyymm >= 201506) {
                    ?>
                    <td align='center' bgcolor='white' class='pt11b'>中山課長代理</td>
                    <td align='center' bgcolor='white' class='pt11b'>5%</td>
                    <?php
                    } else {
                    ?>
                    <td align='center' bgcolor='white' class='pt11b'>　</td>
                    <td align='center' bgcolor='white' class='pt11b'>　</td>
                    <?php
                    }
                    ?>
                    <td align='center' class='rightbb'>
                        <input type='hidden' name='invent_z[]' value='<?php echo $invent_z[20] ?>'>
                        <?php echo $invent_z[20] ?>
                    </td>
                    <td align='center' bgcolor='white'>
                        <input type='text' name='invent[]' size='11' maxlength='11' value='<?php echo $invent[20] ?>' class='right' onChange='return isDigit(value);'>
                    </td>
                </tr>
                <?php
                if ($yyyymm >= 201704) {
                ?>
                <tr>
                    <?php
                    if ($yyyymm >= 201804) {
                    ?>
                    <td align='center' bgcolor='white' class='pt11b'>安田課長</td>
                    <td align='center' bgcolor='white' class='pt11b'>80%</td>
                    <?php
                    } else {
                    ?>
                    <td align='center' bgcolor='white' class='pt11b'>石崎課長代理</td>
                    <td align='center' bgcolor='white' class='pt11b'>20%</td>
                    <?php
                    }
                    ?>
                    <td align='center' class='rightbb'>
                        <input type='hidden' name='invent_z[]' value='<?php echo $invent_z[21] ?>'>
                        <?php echo $invent_z[21] ?>
                    </td>
                    <td align='center' bgcolor='white'>
                        <input type='text' name='invent[]' size='11' maxlength='11' value='<?php echo $invent[21] ?>' class='right' onChange='return isDigit(value);'>
                    </td>
                </tr>
                <?php
                }
                ?>
                <tr>
                    <td align='center' bgcolor='#ccffff' class='pt11b'>
                    機工経費
                    <br>
                    調整<font color='red'>※６</font>
                    </td>
                    <td align='center' bgcolor='white' class='pt11b'>単位：円</td>
                    <td align='center' bgcolor='white' class='pt11b'>　</td>
                    <td align='center' class='rightbb'>
                        <input type='hidden' name='invent_z[]' value='<?php echo $invent_z[22] ?>'>
                        <?php echo $invent_z[22] ?>
                    </td>
                    <td align='center' bgcolor='white'>
                        <input type='text' name='invent[]' size='11' maxlength='11' value='<?php echo $invent[22] ?>' class='right' onChange='return isDigit(value);'>
                    </td>
                </tr>
                <tr>
                    <td align='center' bgcolor='#ccffff' class='pt11b' colspan='2'>給与配賦計算<br>(試験・修理)<BR><font color='red'>耐久に配賦※７</font></td>
                    <?php
                    if ($yyyymm < 201704) {
                    ?>
                    <td align='center' bgcolor='white' class='pt11b'>安田係長</td>
                    <?php
                    } elseif ($yyyymm < 201804) {
                    ?>
                    <td align='center' bgcolor='white' class='pt11b'>安田課長代理</td>
                    <?php
                    } else {
                    ?>
                    <td align='center' bgcolor='white' class='pt11b'>萩野課長</td>
                    <?php
                    }
                    ?>
                    <td align='center' class='rightbb'>
                        <input type='hidden' name='invent_z[]' value='<?php echo $invent_z[23] ?>'>
                        <?php echo $invent_z[23] ?>
                    </td>
                    <td align='center' bgcolor='white'>
                        <input type='text' name='invent[]' size='11' maxlength='11' value='<?php echo $invent[23] ?>' class='right' onChange='return isDigit(value);'>
                    </td>
                </tr>
                <tr>
                    <td colspan='5' align='center'>
                        <input type='submit' name='entry' value='実行' >
                        &nbsp;&nbsp;&nbsp;
                        <input type='submit' name='copy' value='前月データコピー' onClick='return data_copy_click(this)'>
                    </td>
                </tr>
            </table>
            </td></tr>
        </table> <!-- ダミーEnd -->
        </form>
        <br>
        <b>※１ 各部門に携わる社員数を入力してください</b>
        <br>
        <b>※２ 給与配賦を行う方の給与の支給項目・支給合計を入力（１０％を自動配賦）</b>
        <br>
        <b>※３ 登録した給与配賦額を試験修理の労務費より各配賦率で配賦</b>
        <br>
        <b>※４ 給与配賦を行う方の給与の支給項目・支給合計を入力（試修8：リニア2で配賦-試修からマイナス）</b>
        <br>
        <b>※５ 給与配賦を行う方の給与の支給項目・支給合計を入力（各割合で自動配賦）</b>
        <br>
        <b>※６ 560部門以外で機工に配賦する製造経費を入力</b>
        <br>
        <b>※７ 給与配賦を行う方の給与の支給項目・支給合計を入力（２０％を<font color='red'>耐久に自動配賦</font>）</b>
        <br>
    </center>
</body>
</html>
