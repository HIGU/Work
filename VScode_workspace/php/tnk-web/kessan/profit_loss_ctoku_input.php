<?php
//////////////////////////////////////////////////////////////////////////////
// カプラ特注・標準の人員比較計算表の登録・修正及び照会兼用                 //
// Copyright (C) 2009-2019 Norihisa.Ohya norihisa_ooya@nitto-kohki.co.jp    //
// Changed history                                                          //
// 2009/08/24 Created   profit_loss_ctoku_input.php                         //
// 2009/11/02 特注・標準を分ける前にカプラの販管費の人件費に                //
//            商管への社員按分給与を加味するよう変更                        //
// 2009/12/09 一部表示がうまくいってなかった点を修正                        //
// 2009/12/10 コメントの整理                                                //
// 2010/06/04 異動に伴い調整人員名の変更                                    //
// 2010/10/06 前月のデータコピーを追加                                      //
// 2012/06/05 特注の人員名を変更                                            //
// 2013/04/12 特注の人員名を変更                                            //
// 2013/06/05 特注組立の人員に小口課員を追加                                //
// 2014/05/07 異動に伴い調整人員名の変更                                    //
// 2014/07/01 特注組立の人員に薄井課員と佐藤課員を追加                      //
// 2015/05/08 異動に伴い人員名の変更                                        //
// 2016/03/03 異動に伴い人員名の変更                                        //
// 2016/04/21 異動に伴い人員名の変更                                        //
// 2017/05/08 異動に伴い人員名の変更                                        //
// 2017/07/06 異動に伴い人員名の変更                                        //
// 2017/11/13 標準→特注の仕入高配賦を追加                                  //
// 2018/10/10 2018/09固定資産訂正分はすべてカプラ標準なので調整        大谷 //
// 2019/05/09 人事異動に伴う名称変更                                   大谷 //
// 2019/11/11 買掛でマイナス分を調整                                   大谷 //
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

$current_script  = $_SERVER['PHP_SELF'];    // 現在実行中のスクリプト名を保存
$url_referer     = $_SESSION['pl_referer']; // 分岐処理前に保存されている呼出元をセットする

///// タイトルの日付・時間設定
$today = date("Y/m/d H:i:s");

///// 期・月の取得
$ki = Ym_to_tnk($_SESSION['pl_ym']);
$tuki = substr($_SESSION['pl_ym'],4,2);

///// タイトル名(ソースのタイトル名とフォームのタイトル名)
$menu->set_title("第{$ki}期　{$tuki}月度　カプラ特注・標準 人員比率計算表");

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

///// カプラ特注売上比率のデータ取得
$res_ctoku_allo = array();
$query = sprintf("select allo from act_pl_history where pl_bs_ym=%d and note='カプラ特注売上高'", $yyyymm);
if ((getResult($query,$res_ctoku_allo)) > 0) {
    $ctoku_allo = $res_ctoku_allo[0][0];
} else {
    $_SESSION['s_sysmsg'] .= "ＣＬ損益計算が実行されていません。<br>";
    header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
    exit();
}
///// 商管社員按分給与（カプラ）データ取得
$c_allo_kin = 0;
if ($yyyymm >= 200910) {
    $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='カプラ商管社員按分給与'", $yyyymm);
    $res = array();
    if ((getResult($query,$res)) > 0) {
        $c_allo_kin = $res[0][0];
    } else {
        $_SESSION['s_sysmsg'] .= sprintf("商管の損益登録がされていません。<br>先に商管への按分給与の入力を行ってください。", $yyyymm);
        query_affected_trans($con, "rollback");     // Transaction Rollback
        header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
        exit();
    }
}
///// カプラ販管費の人件費データ取得
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='カプラ人件費'", $yyyymm);
$res = array();
getResult($query, $res);
if ($res[0][0] != 0) {
    $c_jin = $res[0][0] - $c_allo_kin;
} else {
    $_SESSION['s_sysmsg'] .= sprintf("ＣＬ損益計算が実行されていません。<br>", $yyyymm);
    query_affected_trans($con, "rollback");     // Transaction Rollback
    header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
    exit();
}
///// カプラ販管費の経費データ取得
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='カプラ経費'", $yyyymm);
$res = array();
getResult($query, $res);
if ($res[0][0] != 0) {
    $c_kei = $res[0][0];
} else {
    $_SESSION['s_sysmsg'] .= sprintf("ＣＬ損益計算が実行されていません。<br>", $yyyymm);
    query_affected_trans($con, "rollback");     // Transaction Rollback
    header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
    exit();
}

///////// 項目とインデックスの関連付け
$item = array();
$item[0]   = "製造社員数";
$item[1]   = "製造パート数";
$item[2]   = "特注製造パート数";
$item[3]   = "組立社員数";
$item[4]   = "組立パート数";
$item[5]   = "特注組立パート数";
$item[6]   = "特注製造社員数１";
$item[7]   = "特注製造社員数２";
$item[8]   = "特注製造社員数３";
$item[9]   = "特注組立社員数１";
$item[10]  = "特注組立社員数２";
$item[11]  = "特注組立社員数３";
$item[12]  = "横川カプラ支払額";
///////// 入力text 変数 初期化
$invent = array();
for ($i = 0; $i < 13; $i++) {
    if (isset($_POST['invent'][$i])) {
        $invent[$i]   = $_POST['invent'][$i];
        $invent_z[$i] = $_POST['invent_z'][$i];
    } else {
        $invent[$i]   = 0;
        $invent_z[$i] = 0;
    }
}
if (!isset($_POST['entry'])) {     // データ入力
    ////////// 登録済みならば棚卸金額取得（当月）
    for ($i = 0; $i < 13; $i++) {
        if ($i >= 12) {
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
    $sei_part50      = UROUND(($invent[1] * 0.5),2);                // 製造課パート掛率50％（当月）
    $sei_total       = $invent[0] + $sei_part50;                    // 製造課合計（当月）
    $seitoku_shain   = $invent[6] + $invent[7] + $invent[8];        // 製造課特注社員数（当月）
    $seitoku_part50  = UROUND(($invent[2] * 0.5),2);                // 製造課特注パート掛率50％（当月）
    $seitoku_total   = $seitoku_shain + $seitoku_part50;            // 製造課特注合計（当月）
    $kumi_part50     = UROUND(($invent[4] * 0.5),2);                // 組立パート掛率50％（当月）
    $kumi_total      = $invent[3] + $kumi_part50;                   // 組立合計（当月）
    $kumitoku_shain  = $invent[9] + $invent[10] + $invent[11];      // 特注組立社員数（当月）
    $kumitoku_part50 = UROUND(($invent[5] * 0.5),2);                // 特注組立パート掛率50％（当月）
    $kumitoku_total  = $kumitoku_shain + $kumitoku_part50;          // 特注組立合計（当月）
    for ($i = 0; $i < 13; $i++) {
        if ($i >= 12) {
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
    $sei_part50_z      = UROUND(($invent_z[1] * 0.5),2);                // 製造課パート掛率50％（前月）
    $sei_total_z       = $invent_z[0] + $sei_part50_z;                  // 製造課合計（前月）
    $seitoku_shain_z   = $invent_z[6] + $invent_z[7] + $invent_z[8];    // 製造課特注社員数（前月）
    $seitoku_part50_z  = UROUND(($invent_z[2] * 0.5),2);                // 製造課特注パート掛率50％（前月）
    $seitoku_total_z   = $seitoku_shain_z + $seitoku_part50_z;          // 製造課特注合計（前月）
    $kumi_part50_z     = UROUND(($invent_z[4] * 0.5),2);                // 組立パート掛率50％（前月）
    $kumi_total_z      = $invent_z[3] + $kumi_part50_z;                 // 組立合計（前月）
    $kumitoku_shain_z  = $invent_z[9] + $invent_z[10] + $invent_z[11];  // 特注組立社員数（前月）
    $kumitoku_part50_z = UROUND(($invent_z[5] * 0.5),2);                // 特注組立パート掛率50％（前月）
    $kumitoku_total_z  = $kumitoku_shain_z + $kumitoku_part50_z;        // 特注組立合計（前月）
    
    if (isset($_POST['copy'])) {                        // 前月データのコピー
        $sei_part50      = $sei_part50_z;               // 製造課パート掛率50％（前月）
        $sei_total       = $sei_total_z;                // 製造課合計（前月）
        $seitoku_shain   = $seitoku_shain_z;            // 製造課特注社員数（前月）
        $seitoku_part50  = $seitoku_part50_z;           // 製造課特注パート掛率50％（前月）
        $seitoku_total   = $seitoku_total_z;            // 製造課特注合計（前月）
        $kumi_part50     = $kumi_part50_z;              // 組立パート掛率50％（前月）
        $kumi_total      = $kumi_total_z;               // 組立合計（前月）
        $kumitoku_shain  = $kumitoku_shain_z;           // 特注組立社員数（前月）
        $kumitoku_part50 = $kumitoku_part50_z;          // 特注組立パート掛率50％（前月）
        $kumitoku_total  = $kumitoku_total_z;           // 特注組立合計（前月）
        for ($i = 0; $i < 13; $i++) {
            $invent[$i] = $invent_z[$i];
        }
    }
    
} else {    // 登録処理  トランザクションで更新しているためレコード有り無しのチェックのみ
    $sei_part50      = UROUND(($invent[1] * 0.5),2);
    $sei_total       = $invent[0] + $sei_part50;
    $seitoku_shain   = $invent[6] + $invent[7] + $invent[8];
    $seitoku_part50  = UROUND(($invent[2] * 0.5),2);
    $seitoku_total   = $seitoku_shain + $seitoku_part50;
    $kumi_part50     = UROUND(($invent[4] * 0.5),2);
    $kumi_total      = $invent[3] + $kumi_part50;
    $kumitoku_shain  = $invent[9] + $invent[10] + $invent[11];
    $kumitoku_part50 = UROUND(($invent[5] * 0.5),2);
    $kumitoku_total  = $kumitoku_shain + $kumitoku_part50;
    
    $sei_part50_z      = UROUND(($invent_z[1] * 0.5),2);
    $sei_total_z       = $invent_z[0] + $sei_part50_z;
    $seitoku_shain_z   = $invent_z[6] + $invent_z[7] + $invent_z[8];
    $seitoku_part50_z  = UROUND(($invent_z[2] * 0.5),2);
    $seitoku_total_z   = $seitoku_shain_z + $seitoku_part50_z;
    $kumi_part50_z     = UROUND(($invent_z[4] * 0.5),2);
    $kumi_total_z      = $invent_z[3] + $kumi_part50_z;
    $kumitoku_shain_z  = $invent_z[9] + $invent_z[10] + $invent_z[11];
    $kumitoku_part50_z = UROUND(($invent_z[5] * 0.5),2);
    $kumitoku_total_z  = $kumitoku_shain_z + $kumitoku_part50_z;
    for ($i = 0; $i < 13; $i++) {
        if ($i >= 12) {
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
    for ($i = 0; $i < 13; $i++) {
        if ($i >= 12) {
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
            if ($i >= 12) {
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
            if ($i >= 12) {
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
    
    //////////////////////////////////////  販管費登録
    //////////////////////////////////////  ここから入れる
    // カプラ特注販管費人件費の登録
    if ($sei_total == 0) {
        $ctoku_han_jin = 0;
    } elseif ($kumi_total == 0) {
        $ctoku_han_jin = 0;
    } elseif ($seitoku_total == 0) {
        $ctoku_han_jin = 0;
    } elseif ($kumitoku_total == 0) {
        $ctoku_han_jin = 0;
    } elseif ($c_jin == 0) {
        $ctoku_han_jin = 0;
    } else {
        $ctoku_han_jin = Uround(($c_jin * $seitoku_total / $sei_total),0);
        $ctoku_han_jin += Uround(($c_jin * $kumitoku_total / $kumi_total),0);
    }
    $query_chk = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='カプラ特注人件費'", $yyyymm);
    if (getResult($query_chk,$res_chk) <= 0) {  ///// 既登録済みのチェック
        // 新規登録
        $query = sprintf("insert into act_pl_history (pl_bs_ym, kin, note) values (%d, %d, 'カプラ特注人件費')", $yyyymm, $ctoku_han_jin);
        if (query_affected_trans($con, $query) <= 0) {
            $_SESSION['s_sysmsg'] .= sprintf("カプラ特注人件費の登録に失敗<br>第 %d期 %d月",$ki,$tuki);
            query_affected_trans($con, "rollback");     // Transaction Rollback
            header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
            exit();
        }
    } else {
        $query = sprintf("update act_pl_history set kin=%d where pl_bs_ym=%d and note='カプラ特注人件費'", $ctoku_han_jin, $yyyymm);
        if (query_affected_trans($con, $query) <= 0) {
            $_SESSION['s_sysmsg'] .= sprintf("カプラ特注人件費の更新失敗<br>第 %d期 %d月",$ki,$tuki);
            query_affected_trans($con, "rollback");     // Transaction Rollback
            header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
            exit();
        }
    }
    // カプラ特注販管費経費の登録
    if ($sei_total == 0) {
        $ctoku_han_kei = 0;
    } elseif ($kumi_total == 0) {
        $ctoku_han_kei = 0;
    } elseif ($seitoku_total == 0) {
        $ctoku_han_kei = 0;
    } elseif ($kumitoku_total == 0) {
        $ctoku_han_kei = 0;
    } elseif ($c_kei == 0) {
        $ctoku_han_kei = 0;
    } else {
        // 2018/09 固定資産除却訂正 販管費経費で 標準なので特注に配賦を行わない。
        if ($yyymm==201809) {
            $c_kei = $c_kei - 270803;
        }
        $ctoku_han_kei = Uround(($c_kei * $seitoku_total / $sei_total),0);
        $ctoku_han_kei += Uround(($c_kei * $kumitoku_total / $kumi_total),0);
        if ($yyymm==201809) {
            $c_kei = $c_kei + 270803;
        }
    }
    if ($yyymm==201809) {
        $ctoku_han_kei = 4957036;
    }
    $query_chk = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='カプラ特注販管費経費'", $yyyymm);
    if (getResult($query_chk,$res_chk) <= 0) {  ///// 既登録済みのチェック
        // 新規登録
        $query = sprintf("insert into act_pl_history (pl_bs_ym, kin, note) values (%d, %d, 'カプラ特注販管費経費')", $yyyymm, $ctoku_han_kei);
        if (query_affected_trans($con, $query) <= 0) {
            $_SESSION['s_sysmsg'] .= sprintf("カプラ特注販管費経費の登録に失敗<br>第 %d期 %d月",$ki,$tuki);
            query_affected_trans($con, "rollback");     // Transaction Rollback
            header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
            exit();
        }
    } else {
        $query = sprintf("update act_pl_history set kin=%d where pl_bs_ym=%d and note='カプラ特注販管費経費'", $ctoku_han_kei, $yyyymm);
        if (query_affected_trans($con, $query) <= 0) {
            $_SESSION['s_sysmsg'] .= sprintf("カプラ特注販管費経費の更新失敗<br>第 %d期 %d月",$ki,$tuki);
            query_affected_trans($con, "rollback");     // Transaction Rollback
            header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
            exit();
        }
    }
    // カプラ特注
    $query = "select sum_payable, sum_provide, cnt_payable, cnt_provide
                from act_purchase_header
                where purchase_ym={$yyyymm} and item='カプラ特注'";
    $res = array();     // 初期化
    if ( getResultTrs($con, $query, $res) <= 0) {
        $paya_c_toku_kin = 0;         // 買掛
        $prov_c_toku_kin = 0;         // 有償支給
    } else {
        $paya_c_toku_kin = $res[0][0];         // 買掛
        $prov_c_toku_kin = $res[0][1];         // 有償支給
    }
    $c_toku_sum_kin = ($paya_c_toku_kin - $prov_c_toku_kin);
    $c_toku_sum_kin = $c_toku_sum_kin + UROUND(($invent[12] * 0.5),0);
    // 標準配賦追加
    $str_ymd = $yyyymm . '01';
    $end_ymd = $yyyymm . '99';
    //////////// 配賦率年月を取得
    $allo_mm  = substr($yyyymm, -2, 2);
    $allo_yy  = substr($yyyymm,  0, 4);
    $allo_mm  = $allo_mm * 1;
    if ($allo_mm > 9) {          // 下期(10～12月)の場合
        $allo_ym  = $allo_yy . '09';
    } elseif ($allo_mm < 4)  {   // 下期(1～3月)の場合
        $allo_ym  = $allo_yy - 1 . '09';
        $allo_ym  = $allo_ym * 1;
    } else {                    // 上期の場合
        $allo_ym  = $allo_yy . '03';
    }
    $query = "select SUM(Uround(Uround(cast(siharai * p.ctoku_allo as numeric), 0) * order_price, 0))
                from
                    (act_payable as paya left outer join vendor_master using(vendor))
                left outer join
                    order_plan as o using(sei_no)
                LEFT OUTER JOIN
                    parts_stock_master AS m ON (m.parts_no=paya.parts_no)
                LEFT OUTER JOIN
                    inventory_ctoku_par as p ON (p.parts_no=paya.parts_no and p.ctoku_ym={$allo_ym})
                LEFT OUTER JOIN
                    inventory_monthly_ctoku as t ON (p.parts_no = t.parts_no and t.invent_ym={$yyyymm})
                where act_date>={$str_ymd} and act_date<={$end_ymd} and kamoku<=5 and paya.div='C' and o.kouji_no NOT like 'SC%%'  and p.parts_no is not NULL and t.parts_no is NULL";
    $res = array();     // 初期化
    if ( getResultTrs($con, $query, $res) <= 0) {
        $hyo_c_toku_kin = 0;            // 標準→特注分
    } else {
        $hyo_c_toku_kin = $res[0][0];   // 標準→特注分
    }
    
    if ($yyyymm==201910) {
        $c_toku_sum_kin = $c_toku_sum_kin + $hyo_c_toku_kin - 16000000;
    } else {
        $c_toku_sum_kin = $c_toku_sum_kin + $hyo_c_toku_kin;
    }
    $query_chk = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='カプラ特注仕入高'", $yyyymm);
    if (getResult($query_chk,$res_chk) <= 0) {  ///// 既登録済みのチェック
        $query = sprintf("insert into act_pl_history (pl_bs_ym, kin, note, allo) values (%d, %d, 'カプラ特注仕入高', 1.00000)", $yyyymm, $c_toku_sum_kin);
        if (query_affected_trans($con, $query) <= 0) {
            $_SESSION['s_sysmsg'] .= sprintf("カプラ特注仕入高の登録に失敗<br>第 %d期 %d月",$ki,$tuki);
            query_affected_trans($con, "rollback");     // Transaction Rollback
            header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
            exit();
        }
    } else {
        $query = sprintf("update act_pl_history set kin=%d where pl_bs_ym=%d and note='カプラ特注仕入高'", $c_toku_sum_kin, $yyyymm);
        if (query_affected_trans($con, $query) <= 0) {
            $_SESSION['s_sysmsg'] .= sprintf("カプラ特注仕入高の登録に失敗<br>第 %d期 %d月",$ki,$tuki);
            query_affected_trans($con, "rollback");     // Transaction Rollback
            header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
            exit();
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
                    <td rowspan='4' align='center' bgcolor='#ccffcc' class='pt11b' colspan='2'>製　造　課</td>
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
                        <?php echo $sei_part50_z ?>
                    </td>
                    <td bgcolor='#ccffcc' class='rightbg'>
                        <?php echo $sei_part50 ?>
                    </td>
                </tr>
                <tr>
                    <td align='center' bgcolor='#ccffcc' class='pt11b'>計</td>
                    <td bgcolor='#ccffcc' class='rightbg'>
                        <?php echo $sei_total_z ?>
                    </td>
                    <td bgcolor='#ccffcc' class='rightbg'>
                        <?php echo $sei_total ?>
                    </td>
                </tr>
                <tr>
                    <td rowspan='4' align='center' bgcolor='#ffffcc' class='pt11b' colspan='2'>特注製造</td>
                    <td align='center' bgcolor='#ffffcc' class='pt11b'>社員数</td>
                    <td bgcolor='#ccffcc' class='rightby'>
                        <?php echo $seitoku_shain_z ?>
                    </td>
                    <td bgcolor='#ccffcc' class='rightby'>
                        <?php echo $seitoku_shain ?>
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
                        <?php echo $seitoku_part50_z ?>
                    </td>
                    <td bgcolor='#ccffcc' class='rightby'>
                        <?php echo $seitoku_part50 ?>
                    </td>
                </tr>
                <tr>
                    <td align='center' bgcolor='#ffffcc' class='pt11b'>計</td>
                    <td bgcolor='#ccffcc' class='rightby'>
                        <?php echo $seitoku_total_z ?>
                    </td>
                    <td bgcolor='#ccffcc' class='rightby'>
                        <?php echo $seitoku_total ?>
                    </td>
                </tr>
                <tr>
                    <td rowspan='4' align='center' bgcolor='#ccffcc' class='pt11b' colspan='2'>組立担当</td>
                    <td align='center' bgcolor='white' class='pt11b'>社員数</td>
                    <td align='center' class='rightbg'>
                        <input type='hidden' name='invent_z[]' value='<?php echo $invent_z[0] ?>'>
                        <?php echo $invent_z[3] ?>
                    </td>
                    <td align='center' bgcolor='white'>
                        <input type='text' name='invent[]' size='11' maxlength='11' value='<?php echo $invent[3] ?>' class='right' onChange='return isDigit(value);'>
                    </td>
                </tr>
                <tr>
                    <td align='center' bgcolor='white' class='pt11b'>パート数</td>
                    <td align='center' class='rightbg'>
                        <input type='hidden' name='invent_z[]' value='<?php echo $invent_z[4] ?>'>
                        <?php echo $invent_z[4] ?>
                    </td>
                    <td align='center' bgcolor='white'>
                        <input type='text' name='invent[]' size='11' maxlength='11' value='<?php echo $invent[4] ?>' class='right' onChange='return isDigit(value);'>
                    </td>
                </tr>
                <tr>
                    <td align='center' bgcolor='#ccffcc' class='pt11b'>パート掛率50％</td>
                    <td bgcolor='#ccffcc' class='rightbg'>
                        <?php echo $kumi_part50_z ?>
                    </td>
                    <td bgcolor='#ccffcc' class='rightbg'>
                        <?php echo $kumi_part50 ?>
                    </td>
                </tr>
                <tr>
                    <td align='center' bgcolor='#ccffcc' class='pt11b'>計</td>
                    <td bgcolor='#ccffcc' class='rightbg'>
                        <?php echo $kumi_total_z ?>
                    </td>
                    <td bgcolor='#ccffcc' class='rightbg'>
                        <?php echo $kumi_total ?>
                    </td>
                </tr>
                <tr>
                    <td rowspan='4' align='center' bgcolor='#ccffff' class='pt11b' colspan='2'>特注組立</td>
                    <td align='center' bgcolor='#ccffff' class='pt11b'>社員数</td>
                    <td bgcolor='#ccffcc' class='rightbb'>
                        <?php echo $kumitoku_shain_z ?>
                    </td>
                    <td bgcolor='#ccffcc' class='rightbb'>
                        <?php echo $kumitoku_shain ?>
                    </td>
                </tr>
                <tr>
                    <td align='center' bgcolor='white' class='pt11b'>パート数</td>
                    <td align='center' class='rightbb'>
                        <input type='hidden' name='invent_z[]' value='<?php echo $invent_z[5] ?>'>
                        <?php echo $invent_z[5] ?>
                    </td>
                    <td align='center' bgcolor='white'>
                        <input type='text' name='invent[]' size='11' maxlength='11' value='<?php echo $invent[5] ?>' class='right' onChange='return isDigit(value);'>
                    </td>
                </tr>
                <tr>
                    <td align='center' bgcolor='#ccffff' class='pt11b'>パート掛率50％</td>
                    <td bgcolor='#ccffcc' class='rightbb'>
                        <?php echo $kumitoku_part50_z ?>
                    </td>
                    <td bgcolor='#ccffcc' class='rightbb'>
                        <?php echo $kumitoku_part50 ?>
                    </td>
                </tr>
                <tr>
                    <td align='center' bgcolor='#ccffff' class='pt11b'>計</td>
                    <td bgcolor='#ccffcc' class='rightbb'>
                        <?php echo $kumitoku_total_z ?>
                    </td>
                    <td bgcolor='#ccffcc' class='rightbb'>
                        <?php echo $kumitoku_total ?>
                    </td>
                </tr>
                <tr>
                    <td rowspan='3' align='center' bgcolor='#ffffcc' class='pt11b'>特注製造<br>社員数計算<font color='red'>※１</font></td>
                    <?php
                    if ($yyyymm < 201706) {
                    ?>
                    <td align='center' bgcolor='white' class='pt11b'>増山課長・名畑目係長</td>
                    <?php
                    } else {
                    ?>
                    <td align='center' bgcolor='white' class='pt11b'>阿久津課長代理・名畑目係長</td>
                    <?php
                    }
                    ?>
                    <td align='center' bgcolor='white' class='pt11b'>20%</td>
                    <td align='center' class='rightby'>
                        <input type='hidden' name='invent_z[]' value='<?php echo $invent_z[6] ?>'>
                        <?php echo $invent_z[6] ?>
                    </td>
                    <td align='center' bgcolor='white'>
                        <input type='text' name='invent[]' size='11' maxlength='11' value='<?php echo $invent[6] ?>' class='right' onChange='return isDigit(value);'>
                    </td>
                </tr>
                <tr>
                    <?php
                    if ($yyyymm < 201706) {
                    ?>
                    <td align='center' bgcolor='white' class='pt11b'>手塚・目澤・入間川・続田</td>
                    <?php
                    } else {
                    ?>
                    <td align='center' bgcolor='white' class='pt11b'>556部門社員</td>
                    <?php
                    }
                    ?>
                    <td align='center' bgcolor='white' class='pt11b'>100%</td>
                    <td align='center' class='rightby'>
                        <input type='hidden' name='invent_z[]' value='<?php echo $invent_z[7] ?>'>
                        <?php echo $invent_z[7] ?>
                    </td>
                    <td align='center' bgcolor='white'>
                        <input type='text' name='invent[]' size='11' maxlength='11' value='<?php echo $invent[7] ?>' class='right' onChange='return isDigit(value);'>
                    </td>
                </tr>
                <tr>
                    <td align='center' bgcolor='white' class='pt11b'>　</td>
                    <td align='center' bgcolor='white' class='pt11b'>　</td>
                    <td align='center' class='rightby'>
                        <input type='hidden' name='invent_z[]' value='<?php echo $invent_z[8] ?>'>
                        <?php echo $invent_z[8] ?>
                    </td>
                    <td align='center' bgcolor='white'>
                        <input type='text' name='invent[]' size='11' maxlength='11' value='<?php echo $invent[8] ?>' class='right' onChange='return isDigit(value);'>
                    </td>
                </tr>
                <tr>
                    <td rowspan='3' align='center' bgcolor='#ccffff' class='pt11b'>特注組立<br>社員数計算<font color='red'>※１</font></td>
                    <?php
                    if ($yyyymm >= 201904) {
                    ?>
                    <td align='center' bgcolor='white' class='pt11b'>八木沢課員</td>
                    <td align='center' bgcolor='white' class='pt11b'>20%</td>
                    <?php
                    } else {
                    ?>
                    <td align='center' bgcolor='white' class='pt11b'>八木沢課長</td>
                    <td align='center' bgcolor='white' class='pt11b'>20%</td>
                    <?php
                    }
                    ?>
                    <td align='center' class='rightbb'>
                        <input type='hidden' name='invent_z[]' value='<?php echo $invent_z[9] ?>'>
                        <?php echo $invent_z[9] ?>
                    </td>
                    <td align='center' bgcolor='white'>
                        <input type='text' name='invent[]' size='11' maxlength='11' value='<?php echo $invent[9] ?>' class='right' onChange='return isDigit(value);'>
                    </td>
                </tr>
                <tr>
                    <?php
                    if ($yyyymm >= 201904) {
                    ?>
                    <td align='center' bgcolor='white' class='pt11b'>小山課長代理</td>
                    <td align='center' bgcolor='white' class='pt11b'>80%</td>
                    <?php
                    } else {
                    ?>
                    <td align='center' bgcolor='white' class='pt11b'>小山係長</td>
                    <td align='center' bgcolor='white' class='pt11b'>100%</td>
                    <?php
                    }
                    ?>
                    <td align='center' class='rightbb'>
                        <input type='hidden' name='invent_z[]' value='<?php echo $invent_z[10] ?>'>
                        <?php echo $invent_z[10] ?>
                    </td>
                    <td align='center' bgcolor='white'>
                        <input type='text' name='invent[]' size='11' maxlength='11' value='<?php echo $invent[10] ?>' class='right' onChange='return isDigit(value);'>
                    </td>
                </tr>
                <tr>
                    <?php
                    if ($yyyymm < 201704) {
                    ?>
                    <td align='center' bgcolor='white' class='pt11b'>薄井・佐藤・小川課員</td>
                    <?php
                    } else {
                    ?>
                    <td align='center' bgcolor='white' class='pt11b'>係長以外525部門社員</td>
                    <?php
                    }
                    ?>
                    <td align='center' bgcolor='white' class='pt11b'>100%</td>
                    <td align='center' class='rightbb'>
                        <input type='hidden' name='invent_z[]' value='<?php echo $invent_z[11] ?>'>
                        <?php echo $invent_z[11] ?>
                    </td>
                    <td align='center' bgcolor='white'>
                        <input type='text' name='invent[]' size='11' maxlength='11' value='<?php echo $invent[11] ?>' class='right' onChange='return isDigit(value);'>
                    </td>
                </tr>
                <tr>
                    <td align='center' bgcolor='#ffffcc' class='pt11b' colspan='3'>横川カプラ支払額<font color='red'>※２</font></td>
                    <td align='center' class='rightby'>
                        <input type='hidden' name='invent_z[]' value='<?php echo $invent_z[10] ?>'>
                        <?php echo $invent_z[12] ?>
                    </td>
                    <td align='center' bgcolor='white'>
                        <input type='text' name='invent[]' size='11' maxlength='11' value='<?php echo $invent[12] ?>' class='right' onChange='return isDigit(value);'>
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
        <b>
        ※２ AS経理メニュー 26→20→20 外注＃：<font color='red'>01298</font>
        <br>
        　　　　　年月を入力しリスト印刷<font color='red'>Ｙ</font>でリストを印刷し最下部
        <br>
        　　　　　　　　　当月支払予定の金額を入力（50%を特注仕入高に配賦）
        <br>
        </b>
    </center>
</body>
</html>
