<?php
//////////////////////////////////////////////////////////////////////////////
// 全社人員比の計算データの登録・修正及び照会兼用                           //
// 人員比で営業外損益部分を再計算する                                       //
// Copyright (C) 2010-2016 Norihisa.Ohya norihisa_ooya@nitto-kohki.co.jp    //
// Changed history                                                          //
// 2010/02/01 Created   profit_loss_staff_input.php                         //
// 2010/03/04 201002度営業外収益その他の調整を追加。201003には戻し          //
// 2010/10/06 前月のデータコピーを追加                                      //
// 2011/04/06 商管の営業外費用その他の登録部のミスを訂正                    //
// 2012/07/07 2012年6月の営業外費用その他はすべてリニア標準の為             //
//            手動入力                                                      //
// 2012/09/05 2012年8月の営業外収益その他の固定資産売却益はすべてカプラ標準 //
//            の為手動入力                                                  //
// 2012/10/09 2012年9月の営業外収益その他の固定資産売却益 訂正分はすべて    //
//            カプラ標準の為手動入力                                        //
// 2013/01/28 バイモルを液体ポンプへ変更（表示のみデータはバイモルのまま）  //
// 2013/06/06 NKIT有償支給為替差損益の入力を追加                            //
// 2013/07/05 為替差損益の差損と差益が両方収益だったのを費用に配分          //
// 2014/03/05 2014年2月の営業外収益その他の固定資産売却益はすべてカプラ標準 //
//            の為手動入力                                                  //
// 2014/04/03 2014年3月の営業外収益その他の雑収入(PCカプラ金型管理費)は     //
//            すべてカプラ標準の為手動入力                                  //
// 2014/04/04 2014年4月の営業外収益その他の雑収入(PCカプラ金型管理費の戻し) //
//            はすべてカプラ標準の為手動入力                                //
// 2014/08/07 2014年7月の営業外収益その他の雑収入(八下田からの入金分)       //
//            はすべて商管の為手動入力(1,754,636円)                         //
// 2014/09/03 2014年8月の営業外収益その他の雑収入(7月の戻し分)              //
//            はすべて商管の為手動入力(-1,754,636円)                        //
// 2014/10/01 営業外収益その他と営業外費用その他の入力画面を作成し          //
//            調整を行えるようにした。                                      //
// 2016/07/22 修理・耐久損益用に営業外を計算                                //
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
$menu->set_title("第{$ki}期　{$tuki}月度　全社 人員の登録");

///// 対象当月
$yyyymm = $_SESSION['pl_ym'];
///// 対象前月
if (substr($yyyymm,4,2)!=01) {
    $p1_ym = $yyyymm - 1;
} else {
    $p1_ym = $yyyymm - 100;
    $p1_ym = $p1_ym + 11;
}
///// カプラ営業外収益その他データ取得
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='カプラ営業外収益その他'", $yyyymm);
$res = array();
getResult($query, $res);
if ($res[0][0] != 0) {
    $c_pother = $res[0][0];
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

///////// 項目とインデックスの関連付け
$item = array();
$item[0]    = "カプラ人員比";
$item[1]    = "カプラ特注人員比";
$item[2]    = "カプラ標準人員比";
$item[3]    = "リニア人員比";
$item[4]    = "バイモル人員比";
$item[5]    = "リニア標準人員比";
$item[6]    = "試修人員比";
$item[7]    = "カプラ試修人員比";
$item[8]    = "リニア試修人員比";
$item[9]    = "商管人員比";

$item[10]   = "カプラ標準為替差益";
$item[11]   = "カプラ標準為替差損";
$item[12]   = "カプラ特注為替差益";
$item[13]   = "カプラ特注為替差損";
$item[14]   = "リニア標準為替差益";
$item[15]   = "リニア標準為替差損";
$item[16]   = "液体ポンプ為替差益";
$item[17]   = "液体ポンプ為替差損";

$item[18]   = "カプラ標準営業外収益その他調整";
$item[19]   = "カプラ特注営業外収益その他調整";
$item[20]   = "リニア標準営業外収益その他調整";
$item[21]   = "液体ポンプ営業外収益その他調整";
$item[22]   = "試修営業外収益その他調整";
$item[23]   = "商管営業外収益その他調整";

$item[24]   = "カプラ標準営業外費用その他調整";
$item[25]   = "カプラ特注営業外費用その他調整";
$item[26]   = "リニア標準営業外費用その他調整";
$item[27]   = "液体ポンプ営業外費用その他調整";
$item[28]   = "試修営業外費用その他調整";
$item[29]   = "商管営業外費用その他調整";
// 前月分データの取得
for ($i = 0; $i < 30; $i++) {
    $query_b = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='%s'", $p1_ym, $item[$i]);
    $res_b = array();
    if (getResult2($query_b,$res_b) > 0) {
        $jin_b[$i] = $res_b[0][0];
    } else {
        $jin_b[$i] = 0;
    }
}
for ($i = 0; $i < 30; $i++) {
    $query_b = sprintf("select allo from act_pl_history where pl_bs_ym=%d and note='%s'", $p1_ym, $item[$i]);
    $res_b = array();
    if (getResult2($query_b,$res_b) > 0) {
        $allo_b[$i] = $res_b[0][0];
    } else {
        $allo_b[$i] = 0;
    }
}
///////// 入力text 変数 初期化
$jin = array();
for ($i = 0; $i < 30; $i++) {
    if (isset($_POST['jin'][$i])) {
        $jin[$i] = $_POST['jin'][$i];
    } else {
        $jin[$i] = 0;
    }
    if (isset($_POST['allo'][$i])) {
        $allo[$i] = $_POST['allo'][$i];
    } else {
        $allo[$i] = 0;
    }
}
if (!isset($_POST['entry'])) {     // データ入力
    ////////// 登録済みならば人員取得
    for ($i = 0; $i < 30; $i++) {
        $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='%s'", $yyyymm, $item[$i]);
        $res = array();
        if (getResult2($query,$res) > 0) {
            $jin[$i] = $res[0][0];
        }
    }
    for ($i = 0; $i < 30; $i++) {
        $query = sprintf("select allo from act_pl_history where pl_bs_ym=%d and note='%s'", $yyyymm, $item[$i]);
        $res = array();
        if (getResult2($query,$res) > 0) {
            $allo[$i] = $res[0][0];
        }
    }
    
    if (isset($_POST['copy'])) {                        // 前月データのコピー
        for ($i = 0; $i < 30; $i++) {
            $jin[$i]  = $jin_b[$i];
            $allo[$i] = $allo_b[$i];
        }
    }
    
} else {    // 登録処理  トランザクションで更新しているためレコード有り無しのチェックのみ
    // 全て登録終了後に 率を計算し 営業外のデータを再計算させる
    if ($yyyymm >= 200912) {    // テスト用で１２月より計算（適用月を確認して変更すること
        // 人員比の計算
        $jin[0]  = $jin[1] + $jin[2];                        // カプラ人員
        $jin[3]  = $jin[4] + $jin[5];                        // リニア人員
        $jin[6]  = $jin[7] + $jin[8];                        // 試修人員
        
        $t_jin   = $jin[0] + $jin[3] + $jin[6] + $jin[9];    // カプラ・リニア・試修・商管の合計人員の計算
        $allo[0] = Uround(($jin[0] / $t_jin), 4);            // カプラ人員比
        $allo[3] = Uround(($jin[3] / $t_jin), 4);            // リニア人員比
        $allo[6] = Uround(($jin[6] / $t_jin), 4);            // 試修人員比
        $allo[9] = 1 - $allo[0] - $allo[3] - $allo[6];       // 商管人員比
        
        $allo[1] = Uround(($jin[1] / $jin[0]), 4);           // カプラ特注人員比
        $allo[2] = 1 - $allo[1];                             // カプラ標準人員比
        $allo[4] = Uround(($jin[4] / $jin[3]), 4);           // バイモル人員比
        $allo[5] = 1 - $allo[4];                             // リニア標準人員比
        $allo[7] = Uround(($jin[7] / $jin[6]), 4);           // カプラ試修人員比
        $allo[8] = 1 - $allo[7];                             // リニア試修人員比
        
        // 各人員数・人員比の登録
        for ($i = 0; $i < 30; $i++) {
            $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='%s'", $yyyymm, $item[$i]);
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
                $query = sprintf("insert into act_pl_history (pl_bs_ym, kin, note, allo) values (%d, %d, '%s', %1.4f)", $yyyymm, $jin[$i], $item[$i], $allo[$i]);
                if (query_affected_trans($con, $query) <= 0) {
                    $_SESSION["s_sysmsg"] .= sprintf("%sの新規登録に失敗<br>第 %d期 %d月", $item[$i], $ki, $tuki);
                    query_affected_trans($con, "rollback");     // transaction rollback
                    header("Location: $current_script");
                    exit();
                }
                /////////// commit トランザクション終了
                query_affected_trans($con, "commit");
                $_SESSION["s_sysmsg"] = sprintf("<font color='yellow'>第%d期 %d月 全社人員比データ 新規 登録完了</font>",$ki,$tuki);
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
                $query = sprintf("update act_pl_history set kin=%d, allo=%1.4f where pl_bs_ym=%d and note='%s'", $jin[$i], $allo[$i], $yyyymm, $item[$i]);
                if (query_affected_trans($con, $query) <= 0) {
                    $_SESSION["s_sysmsg"] .= sprintf("%sのUPDATEに失敗<br>第 %d期 %d月", $item[$i], $ki, $tuki);
                    query_affected_trans($con, "rollback");     // transaction rollback
                    header("Location: $current_script");
                    exit();
                }
                /////////// commit トランザクション終了
                query_affected_trans($con, "commit");
                $_SESSION["s_sysmsg"] = sprintf("<font color='yellow'>第%d期 %d月 全社人員比データ 変更 完了</font>",$ki,$tuki);
            }
        }
        
        // 各営業外の金額を計算
        /***** 業務委託収入の取得 *****/
        $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='全体業務委託収入'", $yyyymm);
        getUniResult($query,$res_kin);
        
        $gyoumu       = $res_kin;
        $gyoumu_c     = Uround(($gyoumu * $allo[0]), 0);                // カプラ業務委託収入
        $gyoumu_l     = Uround(($gyoumu * $allo[3]), 0);                // リニア業務委託収入
        $gyoumu_b     = Uround(($gyoumu * $allo[9]), 0);                // 商管業務委託収入
        $gyoumu_s     = $gyoumu - $gyoumu_c - $gyoumu_l - $gyoumu_b;    // 試修業務委託収入
        
        $gyoumu_ctoku = Uround(($gyoumu_c * $allo[1]), 0);              // カプラ特注業務委託収入
        $gyoumu_chyou = $gyoumu_c - $gyoumu_ctoku;                      // カプラ標準業務委託収入
        $gyoumu_lb    = Uround(($gyoumu_l * $allo[4]), 0);              // バイモル業務委託収入
        $gyoumu_lh    = $gyoumu_l - $gyoumu_lb;                         // リニア標準業務委託収入
        $gyoumu_sc    = Uround(($gyoumu_s * $allo[7]), 0);              // カプラ試修業務委託収入
        $gyoumu_sl    = $gyoumu_s - $gyoumu_sl;                         // リニア試修業務委託収入
        
        // 修理・耐久計算
        $gyoumu_st = Uround(($gyoumu_s * $st_uri_allo), 0);
        $gyoumu_ss = $gyoumu_s - $gyoumu_st;
        
        // 業務委託収入の登録
        $query_chk = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='カプラ業務委託収入再計算'", $yyyymm);
        if (getResult($query_chk,$res_chk) <= 0) {  // 既登録済みのチェック
            // 新規登録
            $query = sprintf("insert into act_pl_history (pl_bs_ym, kin, note) values (%d, %d, 'カプラ業務委託収入再計算')", $yyyymm, $gyoumu_c);
            if (query_affected_trans($con, $query) <= 0) {
                $_SESSION['s_sysmsg'] .= sprintf("カプラ業務委託収入再計算の登録に失敗<br>第 %d期 %d月",$ki,$tuki);
                query_affected_trans($con, "rollback");     // Transaction Rollback
                header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
                exit();
            }
        } else {
            $query = sprintf("update act_pl_history set kin=%d where pl_bs_ym=%d and note='カプラ業務委託収入再計算'", $gyoumu_c, $yyyymm);
            if (query_affected_trans($con, $query) <= 0) {
                $_SESSION['s_sysmsg'] .= sprintf("カプラ業務委託収入再計算の更新失敗<br>第 %d期 %d月",$ki,$tuki);
                query_affected_trans($con, "rollback");     // Transaction Rollback
                header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
                exit();
            }
        }
        $query_chk = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='リニア業務委託収入再計算'", $yyyymm);
        if (getResult($query_chk,$res_chk) <= 0) {  // 既登録済みのチェック
            // 新規登録
            $query = sprintf("insert into act_pl_history (pl_bs_ym, kin, note) values (%d, %d, 'リニア業務委託収入再計算')", $yyyymm, $gyoumu_l);
            if (query_affected_trans($con, $query) <= 0) {
                $_SESSION['s_sysmsg'] .= sprintf("リニア業務委託収入再計算の登録に失敗<br>第 %d期 %d月",$ki,$tuki);
                query_affected_trans($con, "rollback");     // Transaction Rollback
                header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
                exit();
            }
        } else {
            $query = sprintf("update act_pl_history set kin=%d where pl_bs_ym=%d and note='リニア業務委託収入再計算'", $gyoumu_l, $yyyymm);
            if (query_affected_trans($con, $query) <= 0) {
                $_SESSION['s_sysmsg'] .= sprintf("リニア業務委託収入再計算の更新失敗<br>第 %d期 %d月",$ki,$tuki);
                query_affected_trans($con, "rollback");     // Transaction Rollback
                header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
                exit();
            }
        }
        // 商管を加味する場合
        $query_chk = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='商管業務委託収入再計算'", $yyyymm);
        if (getResult($query_chk,$res_chk) <= 0) {  // 既登録済みのチェック
            // 新規登録
            $query = sprintf("insert into act_pl_history (pl_bs_ym, kin, note) values (%d, %d, '商管業務委託収入再計算')", $yyyymm, $gyoumu_b);
            if (query_affected_trans($con, $query) <= 0) {
                $_SESSION['s_sysmsg'] .= sprintf("商管業務委託収入再計算の登録に失敗<br>第 %d期 %d月",$ki,$tuki);
                query_affected_trans($con, "rollback");     // Transaction Rollback
                header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
                exit();
            }
        } else {
            $query = sprintf("update act_pl_history set kin=%d where pl_bs_ym=%d and note='商管業務委託収入再計算'", $gyoumu_b, $yyyymm);
            if (query_affected_trans($con, $query) <= 0) {
                $_SESSION['s_sysmsg'] .= sprintf("商管業務委託収入再計算の更新失敗<br>第 %d期 %d月",$ki,$tuki);
                query_affected_trans($con, "rollback");     // Transaction Rollback
                header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
                exit();
            }
        }
        
        $query_chk = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='試修業務委託収入再計算'", $yyyymm);
        if (getResult($query_chk,$res_chk) <= 0) {  // 既登録済みのチェック
            // 新規登録
            $query = sprintf("insert into act_pl_history (pl_bs_ym, kin, note) values (%d, %d, '試修業務委託収入再計算')", $yyyymm, $gyoumu_s);
            if (query_affected_trans($con, $query) <= 0) {
                $_SESSION['s_sysmsg'] .= sprintf("試修業務委託収入再計算の登録に失敗<br>第 %d期 %d月",$ki,$tuki);
                query_affected_trans($con, "rollback");     // Transaction Rollback
                header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
                exit();
            }
        } else {
            $query = sprintf("update act_pl_history set kin=%d where pl_bs_ym=%d and note='試修業務委託収入再計算'", $gyoumu_s, $yyyymm);
            if (query_affected_trans($con, $query) <= 0) {
                $_SESSION['s_sysmsg'] .= sprintf("試修業務委託収入再計算の更新失敗<br>第 %d期 %d月",$ki,$tuki);
                query_affected_trans($con, "rollback");     // Transaction Rollback
                header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
                exit();
            }
        }
        $query_chk = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='カプラ特注業務委託収入再計算'", $yyyymm);
        if (getResult($query_chk,$res_chk) <= 0) {  // 既登録済みのチェック
            // 新規登録
            $query = sprintf("insert into act_pl_history (pl_bs_ym, kin, note) values (%d, %d, 'カプラ特注業務委託収入再計算')", $yyyymm, $gyoumu_ctoku);
            if (query_affected_trans($con, $query) <= 0) {
                $_SESSION['s_sysmsg'] .= sprintf("カプラ特注業務委託収入再計算の登録に失敗<br>第 %d期 %d月",$ki,$tuki);
                query_affected_trans($con, "rollback");     // Transaction Rollback
                header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
                exit();
            }
        } else {
            $query = sprintf("update act_pl_history set kin=%d where pl_bs_ym=%d and note='カプラ特注業務委託収入再計算'", $gyoumu_ctoku, $yyyymm);
            if (query_affected_trans($con, $query) <= 0) {
                $_SESSION['s_sysmsg'] .= sprintf("カプラ特注業務委託収入再計算の更新失敗<br>第 %d期 %d月",$ki,$tuki);
                query_affected_trans($con, "rollback");     // Transaction Rollback
                header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
                exit();
            }
        }
        $query_chk = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='カプラ標準業務委託収入再計算'", $yyyymm);
        if (getResult($query_chk,$res_chk) <= 0) {  // 既登録済みのチェック
            // 新規登録
            $query = sprintf("insert into act_pl_history (pl_bs_ym, kin, note) values (%d, %d, 'カプラ標準業務委託収入再計算')", $yyyymm, $gyoumu_chyou);
            if (query_affected_trans($con, $query) <= 0) {
                $_SESSION['s_sysmsg'] .= sprintf("カプラ標準業務委託収入再計算の登録に失敗<br>第 %d期 %d月",$ki,$tuki);
                query_affected_trans($con, "rollback");     // Transaction Rollback
                header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
                exit();
            }
        } else {
            $query = sprintf("update act_pl_history set kin=%d where pl_bs_ym=%d and note='カプラ標準業務委託収入再計算'", $gyoumu_chyou, $yyyymm);
            if (query_affected_trans($con, $query) <= 0) {
                $_SESSION['s_sysmsg'] .= sprintf("カプラ標準業務委託収入再計算の更新失敗<br>第 %d期 %d月",$ki,$tuki);
                query_affected_trans($con, "rollback");     // Transaction Rollback
                header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
                exit();
            }
        }
        $query_chk = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='バイモル業務委託収入再計算'", $yyyymm);
        if (getResult($query_chk,$res_chk) <= 0) {  // 既登録済みのチェック
            // 新規登録
            $query = sprintf("insert into act_pl_history (pl_bs_ym, kin, note) values (%d, %d, 'バイモル業務委託収入再計算')", $yyyymm, $gyoumu_lb);
            if (query_affected_trans($con, $query) <= 0) {
                $_SESSION['s_sysmsg'] .= sprintf("バイモル業務委託収入再計算の登録に失敗<br>第 %d期 %d月",$ki,$tuki);
                query_affected_trans($con, "rollback");     // Transaction Rollback
                header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
                exit();
            }
        } else {
            $query = sprintf("update act_pl_history set kin=%d where pl_bs_ym=%d and note='バイモル業務委託収入再計算'", $gyoumu_lb, $yyyymm);
            if (query_affected_trans($con, $query) <= 0) {
                $_SESSION['s_sysmsg'] .= sprintf("バイモル業務委託収入再計算の更新失敗<br>第 %d期 %d月",$ki,$tuki);
                query_affected_trans($con, "rollback");     // Transaction Rollback
                header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
                exit();
            }
        }
        $query_chk = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='リニア標準業務委託収入再計算'", $yyyymm);
        if (getResult($query_chk,$res_chk) <= 0) {  // 既登録済みのチェック
            // 新規登録
            $query = sprintf("insert into act_pl_history (pl_bs_ym, kin, note) values (%d, %d, 'リニア標準業務委託収入再計算')", $yyyymm, $gyoumu_lh);
            if (query_affected_trans($con, $query) <= 0) {
                $_SESSION['s_sysmsg'] .= sprintf("リニア標準業務委託収入再計算の登録に失敗<br>第 %d期 %d月",$ki,$tuki);
                query_affected_trans($con, "rollback");     // Transaction Rollback
                header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
                exit();
            }
        } else {
            $query = sprintf("update act_pl_history set kin=%d where pl_bs_ym=%d and note='リニア標準業務委託収入再計算'", $gyoumu_lh, $yyyymm);
            if (query_affected_trans($con, $query) <= 0) {
                $_SESSION['s_sysmsg'] .= sprintf("リニア標準業務委託収入再計算の更新失敗<br>第 %d期 %d月",$ki,$tuki);
                query_affected_trans($con, "rollback");     // Transaction Rollback
                header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
                exit();
            }
        }
        $query_chk = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='カプラ試修業務委託収入再計算'", $yyyymm);
        if (getResult($query_chk,$res_chk) <= 0) {  // 既登録済みのチェック
            // 新規登録
            $query = sprintf("insert into act_pl_history (pl_bs_ym, kin, note) values (%d, %d, 'カプラ試修業務委託収入再計算')", $yyyymm, $gyoumu_sc);
            if (query_affected_trans($con, $query) <= 0) {
                $_SESSION['s_sysmsg'] .= sprintf("カプラ試修業務委託収入再計算の登録に失敗<br>第 %d期 %d月",$ki,$tuki);
                query_affected_trans($con, "rollback");     // Transaction Rollback
                header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
                exit();
            }
        } else {
            $query = sprintf("update act_pl_history set kin=%d where pl_bs_ym=%d and note='カプラ試修業務委託収入再計算'", $gyoumu_sc, $yyyymm);
            if (query_affected_trans($con, $query) <= 0) {
                $_SESSION['s_sysmsg'] .= sprintf("カプラ試修業務委託収入再計算の更新失敗<br>第 %d期 %d月",$ki,$tuki);
                query_affected_trans($con, "rollback");     // Transaction Rollback
                header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
                exit();
            }
        }
        $query_chk = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='リニア試修業務委託収入再計算'", $yyyymm);
        if (getResult($query_chk,$res_chk) <= 0) {  // 既登録済みのチェック
            // 新規登録
            $query = sprintf("insert into act_pl_history (pl_bs_ym, kin, note) values (%d, %d, 'リニア試修業務委託収入再計算')", $yyyymm, $gyoumu_sl);
            if (query_affected_trans($con, $query) <= 0) {
                $_SESSION['s_sysmsg'] .= sprintf("リニア試修業務委託収入再計算の登録に失敗<br>第 %d期 %d月",$ki,$tuki);
                query_affected_trans($con, "rollback");     // Transaction Rollback
                header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
                exit();
            }
        } else {
            $query = sprintf("update act_pl_history set kin=%d where pl_bs_ym=%d and note='リニア試修業務委託収入再計算'", $gyoumu_sl, $yyyymm);
            if (query_affected_trans($con, $query) <= 0) {
                $_SESSION['s_sysmsg'] .= sprintf("リニア試修業務委託収入再計算の更新失敗<br>第 %d期 %d月",$ki,$tuki);
                query_affected_trans($con, "rollback");     // Transaction Rollback
                header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
                exit();
            }
        }
        $query_chk = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='修理業務委託収入再計算'", $yyyymm);
        if (getResult($query_chk,$res_chk) <= 0) {  // 既登録済みのチェック
            // 新規登録
            $query = sprintf("insert into act_pl_history (pl_bs_ym, kin, note) values (%d, %d, '修理業務委託収入再計算')", $yyyymm, $gyoumu_ss);
            if (query_affected_trans($con, $query) <= 0) {
                $_SESSION['s_sysmsg'] .= sprintf("修理業務委託収入再計算の登録に失敗<br>第 %d期 %d月",$ki,$tuki);
                query_affected_trans($con, "rollback");     // Transaction Rollback
                header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
                exit();
            }
        } else {
            $query = sprintf("update act_pl_history set kin=%d where pl_bs_ym=%d and note='修理業務委託収入再計算'", $gyoumu_ss, $yyyymm);
            if (query_affected_trans($con, $query) <= 0) {
                $_SESSION['s_sysmsg'] .= sprintf("修理業務委託収入再計算の更新失敗<br>第 %d期 %d月",$ki,$tuki);
                query_affected_trans($con, "rollback");     // Transaction Rollback
                header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
                exit();
            }
        }
        $query_chk = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='耐久業務委託収入再計算'", $yyyymm);
        if (getResult($query_chk,$res_chk) <= 0) {  // 既登録済みのチェック
            // 新規登録
            $query = sprintf("insert into act_pl_history (pl_bs_ym, kin, note) values (%d, %d, '耐久業務委託収入再計算')", $yyyymm, $gyoumu_st);
            if (query_affected_trans($con, $query) <= 0) {
                $_SESSION['s_sysmsg'] .= sprintf("耐久業務委託収入再計算の登録に失敗<br>第 %d期 %d月",$ki,$tuki);
                query_affected_trans($con, "rollback");     // Transaction Rollback
                header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
                exit();
            }
        } else {
            $query = sprintf("update act_pl_history set kin=%d where pl_bs_ym=%d and note='耐久業務委託収入再計算'", $gyoumu_st, $yyyymm);
            if (query_affected_trans($con, $query) <= 0) {
                $_SESSION['s_sysmsg'] .= sprintf("耐久業務委託収入再計算の更新失敗<br>第 %d期 %d月",$ki,$tuki);
                query_affected_trans($con, "rollback");     // Transaction Rollback
                header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
                exit();
            }
        }
        
        /***** 仕入割引の取得 *****/
        $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='全体仕入割引'", $yyyymm);
        getUniResult($query,$s_wari);
        $s_wari       = $s_wari;
        $s_wari_c     = Uround(($s_wari * $allo[0]), 0);                // カプラ仕入割引
        $s_wari_l     = Uround(($s_wari * $allo[3]), 0);                // リニア仕入割引
        $s_wari_b     = Uround(($s_wari * $allo[9]), 0);                // 商管仕入割引
        $s_wari_s     = $s_wari - $s_wari_c - $s_wari_l - $s_wari_b;    // 試修仕入割引
        
        $s_wari_ctoku = Uround(($s_wari_c * $allo[1]), 0);              // カプラ特注仕入割引
        $s_wari_chyou = $s_wari_c - $s_wari_ctoku;                      // カプラ標準仕入割引
        $s_wari_lb    = Uround(($s_wari_l * $allo[4]), 0);              // バイモル仕入割引
        $s_wari_lh    = $s_wari_l - $s_wari_lb;                         // リニア標準仕入割引
        $s_wari_sc    = Uround(($s_wari_s * $allo[7]), 0);              // カプラ試修仕入割引
        $s_wari_sl    = $s_wari_s - $s_wari_sl;                         // リニア試修仕入割引
        
        // 修理・耐久計算
        $s_wari_st = Uround(($s_wari_s * $st_uri_allo), 0);
        $s_wari_ss = $s_wari_s - $s_wari_st;
        
        // 仕入割引の登録
        $query_chk = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='カプラ仕入割引再計算'", $yyyymm);
        if (getResult($query_chk,$res_chk) <= 0) {  // 既登録済みのチェック
            // 新規登録
            $query = sprintf("insert into act_pl_history (pl_bs_ym, kin, note) values (%d, %d, 'カプラ仕入割引再計算')", $yyyymm, $s_wari_c);
            if (query_affected_trans($con, $query) <= 0) {
                $_SESSION['s_sysmsg'] .= sprintf("カプラ仕入割引再計算の登録に失敗<br>第 %d期 %d月",$ki,$tuki);
                query_affected_trans($con, "rollback");     // Transaction Rollback
                header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
                exit();
            }
        } else {
            $query = sprintf("update act_pl_history set kin=%d where pl_bs_ym=%d and note='カプラ仕入割引再計算'", $s_wari_c, $yyyymm);
            if (query_affected_trans($con, $query) <= 0) {
                $_SESSION['s_sysmsg'] .= sprintf("カプラ仕入割引再計算の更新失敗<br>第 %d期 %d月",$ki,$tuki);
                query_affected_trans($con, "rollback");     // Transaction Rollback
                header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
                exit();
            }
        }
        $query_chk = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='リニア仕入割引再計算'", $yyyymm);
        if (getResult($query_chk,$res_chk) <= 0) {  // 既登録済みのチェック
            // 新規登録
            $query = sprintf("insert into act_pl_history (pl_bs_ym, kin, note) values (%d, %d, 'リニア仕入割引再計算')", $yyyymm, $s_wari_l);
            if (query_affected_trans($con, $query) <= 0) {
                $_SESSION['s_sysmsg'] .= sprintf("リニア仕入割引再計算の登録に失敗<br>第 %d期 %d月",$ki,$tuki);
                query_affected_trans($con, "rollback");     // Transaction Rollback
                header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
                exit();
            }
        } else {
            $query = sprintf("update act_pl_history set kin=%d where pl_bs_ym=%d and note='リニア仕入割引再計算'", $s_wari_l, $yyyymm);
            if (query_affected_trans($con, $query) <= 0) {
                $_SESSION['s_sysmsg'] .= sprintf("リニア仕入割引再計算の更新失敗<br>第 %d期 %d月",$ki,$tuki);
                query_affected_trans($con, "rollback");     // Transaction Rollback
                header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
                exit();
            }
        }
        // 商管を加味する場合
        $query_chk = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='商管仕入割引再計算'", $yyyymm);
        if (getResult($query_chk,$res_chk) <= 0) {  // 既登録済みのチェック
            // 新規登録
            $query = sprintf("insert into act_pl_history (pl_bs_ym, kin, note) values (%d, %d, '商管仕入割引再計算')", $yyyymm, $s_wari_b);
            if (query_affected_trans($con, $query) <= 0) {
                $_SESSION['s_sysmsg'] .= sprintf("商管仕入割引再計算の登録に失敗<br>第 %d期 %d月",$ki,$tuki);
                query_affected_trans($con, "rollback");     // Transaction Rollback
                header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
                exit();
            }
        } else {
            $query = sprintf("update act_pl_history set kin=%d where pl_bs_ym=%d and note='商管仕入割引再計算'", $s_wari_b, $yyyymm);
            if (query_affected_trans($con, $query) <= 0) {
                $_SESSION['s_sysmsg'] .= sprintf("商管仕入割引再計算の更新失敗<br>第 %d期 %d月",$ki,$tuki);
                query_affected_trans($con, "rollback");     // Transaction Rollback
                header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
                exit();
            }
        }
        
        $query_chk = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='試修仕入割引再計算'", $yyyymm);
        if (getResult($query_chk,$res_chk) <= 0) {  // 既登録済みのチェック
            // 新規登録
            $query = sprintf("insert into act_pl_history (pl_bs_ym, kin, note) values (%d, %d, '試修仕入割引再計算')", $yyyymm, $s_wari_s);
            if (query_affected_trans($con, $query) <= 0) {
                $_SESSION['s_sysmsg'] .= sprintf("試修仕入割引再計算の登録に失敗<br>第 %d期 %d月",$ki,$tuki);
                query_affected_trans($con, "rollback");     // Transaction Rollback
                header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
                exit();
            }
        } else {
            $query = sprintf("update act_pl_history set kin=%d where pl_bs_ym=%d and note='試修仕入割引再計算'", $s_wari_s, $yyyymm);
            if (query_affected_trans($con, $query) <= 0) {
                $_SESSION['s_sysmsg'] .= sprintf("試修仕入割引再計算の更新失敗<br>第 %d期 %d月",$ki,$tuki);
                query_affected_trans($con, "rollback");     // Transaction Rollback
                header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
                exit();
            }
        }
        $query_chk = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='カプラ特注仕入割引再計算'", $yyyymm);
        if (getResult($query_chk,$res_chk) <= 0) {  // 既登録済みのチェック
            // 新規登録
            $query = sprintf("insert into act_pl_history (pl_bs_ym, kin, note) values (%d, %d, 'カプラ特注仕入割引再計算')", $yyyymm, $s_wari_ctoku);
            if (query_affected_trans($con, $query) <= 0) {
                $_SESSION['s_sysmsg'] .= sprintf("カプラ特注仕入割引再計算の登録に失敗<br>第 %d期 %d月",$ki,$tuki);
                query_affected_trans($con, "rollback");     // Transaction Rollback
                header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
                exit();
            }
        } else {
            $query = sprintf("update act_pl_history set kin=%d where pl_bs_ym=%d and note='カプラ特注仕入割引再計算'", $s_wari_ctoku, $yyyymm);
            if (query_affected_trans($con, $query) <= 0) {
                $_SESSION['s_sysmsg'] .= sprintf("カプラ特注仕入割引再計算の更新失敗<br>第 %d期 %d月",$ki,$tuki);
                query_affected_trans($con, "rollback");     // Transaction Rollback
                header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
                exit();
            }
        }
        $query_chk = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='カプラ標準仕入割引再計算'", $yyyymm);
        if (getResult($query_chk,$res_chk) <= 0) {  // 既登録済みのチェック
            // 新規登録
            $query = sprintf("insert into act_pl_history (pl_bs_ym, kin, note) values (%d, %d, 'カプラ標準仕入割引再計算')", $yyyymm, $s_wari_chyou);
            if (query_affected_trans($con, $query) <= 0) {
                $_SESSION['s_sysmsg'] .= sprintf("カプラ標準仕入割引再計算の登録に失敗<br>第 %d期 %d月",$ki,$tuki);
                query_affected_trans($con, "rollback");     // Transaction Rollback
                header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
                exit();
            }
        } else {
            $query = sprintf("update act_pl_history set kin=%d where pl_bs_ym=%d and note='カプラ標準仕入割引再計算'", $s_wari_chyou, $yyyymm);
            if (query_affected_trans($con, $query) <= 0) {
                $_SESSION['s_sysmsg'] .= sprintf("カプラ標準仕入割引再計算の更新失敗<br>第 %d期 %d月",$ki,$tuki);
                query_affected_trans($con, "rollback");     // Transaction Rollback
                header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
                exit();
            }
        }
        $query_chk = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='バイモル仕入割引再計算'", $yyyymm);
        if (getResult($query_chk,$res_chk) <= 0) {  // 既登録済みのチェック
            // 新規登録
            $query = sprintf("insert into act_pl_history (pl_bs_ym, kin, note) values (%d, %d, 'バイモル仕入割引再計算')", $yyyymm, $s_wari_lb);
            if (query_affected_trans($con, $query) <= 0) {
                $_SESSION['s_sysmsg'] .= sprintf("バイモル仕入割引再計算の登録に失敗<br>第 %d期 %d月",$ki,$tuki);
                query_affected_trans($con, "rollback");     // Transaction Rollback
                header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
                exit();
            }
        } else {
            $query = sprintf("update act_pl_history set kin=%d where pl_bs_ym=%d and note='バイモル仕入割引再計算'", $s_wari_lb, $yyyymm);
            if (query_affected_trans($con, $query) <= 0) {
                $_SESSION['s_sysmsg'] .= sprintf("バイモル仕入割引再計算の更新失敗<br>第 %d期 %d月",$ki,$tuki);
                query_affected_trans($con, "rollback");     // Transaction Rollback
                header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
                exit();
            }
        }
        $query_chk = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='リニア標準仕入割引再計算'", $yyyymm);
        if (getResult($query_chk,$res_chk) <= 0) {  // 既登録済みのチェック
            // 新規登録
            $query = sprintf("insert into act_pl_history (pl_bs_ym, kin, note) values (%d, %d, 'リニア標準仕入割引再計算')", $yyyymm, $s_wari_lh);
            if (query_affected_trans($con, $query) <= 0) {
                $_SESSION['s_sysmsg'] .= sprintf("リニア標準仕入割引再計算の登録に失敗<br>第 %d期 %d月",$ki,$tuki);
                query_affected_trans($con, "rollback");     // Transaction Rollback
                header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
                exit();
            }
        } else {
            $query = sprintf("update act_pl_history set kin=%d where pl_bs_ym=%d and note='リニア標準仕入割引再計算'", $s_wari_lh, $yyyymm);
            if (query_affected_trans($con, $query) <= 0) {
                $_SESSION['s_sysmsg'] .= sprintf("リニア標準仕入割引再計算の更新失敗<br>第 %d期 %d月",$ki,$tuki);
                query_affected_trans($con, "rollback");     // Transaction Rollback
                header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
                exit();
            }
        }
        $query_chk = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='カプラ試修仕入割引再計算'", $yyyymm);
        if (getResult($query_chk,$res_chk) <= 0) {  // 既登録済みのチェック
            // 新規登録
            $query = sprintf("insert into act_pl_history (pl_bs_ym, kin, note) values (%d, %d, 'カプラ試修仕入割引再計算')", $yyyymm, $s_wari_sc);
            if (query_affected_trans($con, $query) <= 0) {
                $_SESSION['s_sysmsg'] .= sprintf("カプラ試修仕入割引再計算の登録に失敗<br>第 %d期 %d月",$ki,$tuki);
                query_affected_trans($con, "rollback");     // Transaction Rollback
                header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
                exit();
            }
        } else {
            $query = sprintf("update act_pl_history set kin=%d where pl_bs_ym=%d and note='カプラ試修仕入割引再計算'", $s_wari_sc, $yyyymm);
            if (query_affected_trans($con, $query) <= 0) {
                $_SESSION['s_sysmsg'] .= sprintf("カプラ試修仕入割引再計算の更新失敗<br>第 %d期 %d月",$ki,$tuki);
                query_affected_trans($con, "rollback");     // Transaction Rollback
                header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
                exit();
            }
        }
        $query_chk = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='リニア試修仕入割引再計算'", $yyyymm);
        if (getResult($query_chk,$res_chk) <= 0) {  // 既登録済みのチェック
            // 新規登録
            $query = sprintf("insert into act_pl_history (pl_bs_ym, kin, note) values (%d, %d, 'リニア試修仕入割引再計算')", $yyyymm, $s_wari_sl);
            if (query_affected_trans($con, $query) <= 0) {
                $_SESSION['s_sysmsg'] .= sprintf("リニア試修仕入割引再計算の登録に失敗<br>第 %d期 %d月",$ki,$tuki);
                query_affected_trans($con, "rollback");     // Transaction Rollback
                header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
                exit();
            }
        } else {
            $query = sprintf("update act_pl_history set kin=%d where pl_bs_ym=%d and note='リニア試修仕入割引再計算'", $s_wari_sl, $yyyymm);
            if (query_affected_trans($con, $query) <= 0) {
                $_SESSION['s_sysmsg'] .= sprintf("リニア試修仕入割引再計算の更新失敗<br>第 %d期 %d月",$ki,$tuki);
                query_affected_trans($con, "rollback");     // Transaction Rollback
                header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
                exit();
            }
        }
        $query_chk = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='修理仕入割引再計算'", $yyyymm);
        if (getResult($query_chk,$res_chk) <= 0) {  // 既登録済みのチェック
            // 新規登録
            $query = sprintf("insert into act_pl_history (pl_bs_ym, kin, note) values (%d, %d, '修理仕入割引再計算')", $yyyymm, $s_wari_ss);
            if (query_affected_trans($con, $query) <= 0) {
                $_SESSION['s_sysmsg'] .= sprintf("修理仕入割引再計算の登録に失敗<br>第 %d期 %d月",$ki,$tuki);
                query_affected_trans($con, "rollback");     // Transaction Rollback
                header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
                exit();
            }
        } else {
            $query = sprintf("update act_pl_history set kin=%d where pl_bs_ym=%d and note='修理仕入割引再計算'", $s_wari_ss, $yyyymm);
            if (query_affected_trans($con, $query) <= 0) {
                $_SESSION['s_sysmsg'] .= sprintf("修理仕入割引再計算の更新失敗<br>第 %d期 %d月",$ki,$tuki);
                query_affected_trans($con, "rollback");     // Transaction Rollback
                header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
                exit();
            }
        }
        $query_chk = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='耐久仕入割引再計算'", $yyyymm);
        if (getResult($query_chk,$res_chk) <= 0) {  // 既登録済みのチェック
            // 新規登録
            $query = sprintf("insert into act_pl_history (pl_bs_ym, kin, note) values (%d, %d, '耐久仕入割引再計算')", $yyyymm, $s_wari_st);
            if (query_affected_trans($con, $query) <= 0) {
                $_SESSION['s_sysmsg'] .= sprintf("耐久仕入割引再計算の登録に失敗<br>第 %d期 %d月",$ki,$tuki);
                query_affected_trans($con, "rollback");     // Transaction Rollback
                header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
                exit();
            }
        } else {
            $query = sprintf("update act_pl_history set kin=%d where pl_bs_ym=%d and note='耐久仕入割引再計算'", $s_wari_st, $yyyymm);
            if (query_affected_trans($con, $query) <= 0) {
                $_SESSION['s_sysmsg'] .= sprintf("耐久仕入割引再計算の更新失敗<br>第 %d期 %d月",$ki,$tuki);
                query_affected_trans($con, "rollback");     // Transaction Rollback
                header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
                exit();
            }
        }
        
        /***** 営業外収益その他の取得 *****/
        $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='全体営業外収益その他'", $yyyymm);
        getUniResult($query,$p_other);
        $p_other       = $p_other;
        if ($yyyymm == 201002) {
            $p_other = $p_other + 600000;
        } elseif ($yyyymm == 201003) {
            $p_other = $p_other - 600000;
        }
        // 2012年8月 固定資産売却益はカプラな為、配賦前にマイナスする
        if ($yyyymm == 201208) {
            $p_other = $p_other - 2808462;
        }
        // 2012年9月 固定資産売却益 訂正分はカプラな為、配賦前にクリアする
        if ($yyyymm == 201209) {
            $p_other = $p_other + 2808462 - 2528029;
        }
        // 2014年2月 固定資産売却益はカプラ標準な為、配賦前にマイナスする
        if ($yyyymm == 201402) {
            $p_other = $p_other - 1169700;
        }
        // 2014年3月 雑収入はカプラ標準な為、配賦前にマイナスする
        if ($yyyymm == 201403) {
            $p_other = $p_other - 1795407;
        }
        // 2014年4月 雑収入はカプラ標準な為、配賦前にプラスする（３月の戻し分）
        if ($yyyymm == 201404) {
            $p_other = $p_other + 1795407;
        }
        // 2014年7月 八下田雑収入は商管な為、配賦前にマイナスする
        if ($yyyymm == 201407) {
            $p_other = $p_other - 1754636;
        }
        
        // 2014年8月 八下田雑収入の戻し（マイナス）は商管な為、配賦前にプラスする
        if ($yyyymm == 201408) {
            $p_other = $p_other + 1754636;
        }
        
        // NKIT有償支給為替差損益計算の為、配賦前に一度抜出（差損は費用、差益は収益）
        // 差益
        $p_other       = $p_other - $jin[10] - $jin[12] - $jin[14] - $jin[16];
        
        // 営業外収益を各セグメントのみに振り分ける為、配賦前に一度抜き出し
        $p_other       = $p_other - $jin[18] - $jin[19] - $jin[20] - $jin[21] - $jin[22] - $jin[23];
        
        // 各セグメント別の営業外収益その他の計算
        $p_other_c     = Uround(($p_other * $allo[0]), 0);                  // カプラ営業外収益その他
        $p_other_l     = Uround(($p_other * $allo[3]), 0);                  // リニア営業外収益その他
        $p_other_b     = Uround(($p_other * $allo[9]), 0);                  // 商管営業外収益その他
        $p_other_s     = $p_other - $p_other_c - $p_other_l - $p_other_b;   // 試修営業外収益その他
        
        $p_other_ctoku = Uround(($p_other_c * $allo[1]), 0);                // カプラ特注営業外収益その他
        $p_other_chyou = $p_other_c - $p_other_ctoku;                       // カプラ標準営業外収益その他
        $p_other_lb    = Uround(($p_other_l * $allo[4]), 0);                // バイモル営業外収益その他
        $p_other_lh    = $p_other_l - $p_other_lb;                          // リニア標準営業外収益その他
        $p_other_sc    = Uround(($p_other_s * $allo[7]), 0);                // カプラ試修営業外収益その他
        $p_other_sl    = $p_other_s - $p_other_sl;                          // リニア試修営業外収益その他
        
        // 各セグメントの為替差損益を戻す（差損は費用、差益は収益）
        $p_other_c     = $p_other_c + $jin[10] + $jin[12];                  // カプラ営業外収益その他
        $p_other_l     = $p_other_l + $jin[14] + $jin[16];                  // リニア営業外収益その他
        
        $p_other_ctoku = $p_other_ctoku + $jin[12];                         // カプラ特注営業外収益その他
        $p_other_chyou = $p_other_chyou + $jin[10];                         // カプラ標準営業外収益その他
        $p_other_lb    = $p_other_lb    + $jin[16];                         // バイモル営業外収益その他
        $p_other_lh    = $p_other_lh    + $jin[14];                         // リニア標準営業外収益その他
        
        // 各セグメントの営業外収益を戻す
        $p_other_c     = $p_other_c + $jin[18] + $jin[19];                  // カプラ営業外収益その他
        $p_other_l     = $p_other_l + $jin[20] + $jin[21];                  // リニア営業外収益その他
        $p_other_s     = $p_other_s + $jin[22];                             // 試修営業外収益その他
        $p_other_b     = $p_other_b + $jin[23];                             // 商管営業外収益その他
        
        $p_other_chyou = $p_other_chyou + $jin[18];                         // カプラ標準営業外収益その他
        $p_other_ctoku = $p_other_ctoku + $jin[19];                         // カプラ特注営業外収益その他
        $p_other_lh    = $p_other_lh    + $jin[20];                         // リニア標準営業外収益その他
        $p_other_lb    = $p_other_lb    + $jin[21];                         // バイモル営業外収益その他
        
        // 2012年8月 固定資産売却益はカプラな為、カプラにのみプラスする
        if ($yyyymm == 201208) {
            $p_other_c     = $p_other_c + 2808462;      // カプラ営業外収益その他
            $p_other_chyou = $p_other_chyou + 2808462;  // カプラ標準営業外収益その他
        }
        // 2012年9月 固定資産売却益 訂正分はカプラな為、カプラにのみ計上する
        if ($yyyymm == 201209) {
            $p_other_c     = $p_other_c - 2808462 + 2528029;      // カプラ営業外収益その他
            $p_other_chyou = $p_other_chyou - 2808462 + 2528029;  // カプラ標準営業外収益その他
        }
        // 2014年2月 固定資産売却益はカプラ標準な為、カプラと標準にプラスする
        if ($yyyymm == 201402) {
            $p_other_c     = $p_other_c + 1169700;      // カプラ営業外収益その他
            $p_other_chyou = $p_other_chyou + 1169700;  // カプラ標準営業外収益その他
        }
        // 2014年3月 雑収入はカプラ標準な為、カプラと標準にプラスする
        if ($yyyymm == 201403) {
            $p_other_c     = $p_other_c + 1795407;      // カプラ営業外収益その他
            $p_other_chyou = $p_other_chyou + 1795407;  // カプラ標準営業外収益その他
        }
        // 2014年4月 雑収入はカプラ標準な為、カプラと標準にプラスする(３月の戻し分)
        if ($yyyymm == 201404) {
            $p_other_c     = $p_other_c - 1795407;      // カプラ営業外収益その他
            $p_other_chyou = $p_other_chyou - 1795407;  // カプラ標準営業外収益その他
        }
        // 2014年7月 八下田雑収入は商管な為、商管にプラスする
        if ($yyyymm == 201407) {
            $p_other_b     = $p_other_b + 1754636;      // 商管営業外収益その他
        }
        // 2014年8月 八下田雑収入の戻し（マイナス)は商管な為、商管にマイナスする
        if ($yyyymm == 201408) {
            $p_other_b     = $p_other_b - 1754636;      // 商管営業外収益その他
        }
        
        // 修理・耐久計算
        $p_other_st = Uround(($p_other_s * $st_uri_allo), 0);
        $p_other_ss = $p_other_s - $p_other_st;
        
        // 営業外収益その他の登録
        $query_chk = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='カプラ営業外収益その他再計算'", $yyyymm);
        if (getResult($query_chk,$res_chk) <= 0) {  // 既登録済みのチェック
            // 新規登録
            $query = sprintf("insert into act_pl_history (pl_bs_ym, kin, note) values (%d, %d, 'カプラ営業外収益その他再計算')", $yyyymm, $p_other_c);
            if (query_affected_trans($con, $query) <= 0) {
                $_SESSION['s_sysmsg'] .= sprintf("カプラ営業外収益その他再計算の登録に失敗<br>第 %d期 %d月",$ki,$tuki);
                query_affected_trans($con, "rollback");     // Transaction Rollback
                header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
                exit();
            }
        } else {
            $query = sprintf("update act_pl_history set kin=%d where pl_bs_ym=%d and note='カプラ営業外収益その他再計算'", $p_other_c, $yyyymm);
            if (query_affected_trans($con, $query) <= 0) {
                $_SESSION['s_sysmsg'] .= sprintf("カプラ営業外収益その他再計算の更新失敗<br>第 %d期 %d月",$ki,$tuki);
                query_affected_trans($con, "rollback");     // Transaction Rollback
                header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
                exit();
            }
        }
        $query_chk = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='リニア営業外収益その他再計算'", $yyyymm);
        if (getResult($query_chk,$res_chk) <= 0) {  // 既登録済みのチェック
            // 新規登録
            $query = sprintf("insert into act_pl_history (pl_bs_ym, kin, note) values (%d, %d, 'リニア営業外収益その他再計算')", $yyyymm, $p_other_l);
            if (query_affected_trans($con, $query) <= 0) {
                $_SESSION['s_sysmsg'] .= sprintf("リニア営業外収益その他再計算の登録に失敗<br>第 %d期 %d月",$ki,$tuki);
                query_affected_trans($con, "rollback");     // Transaction Rollback
                header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
                exit();
            }
        } else {
            $query = sprintf("update act_pl_history set kin=%d where pl_bs_ym=%d and note='リニア営業外収益その他再計算'", $p_other_l, $yyyymm);
            if (query_affected_trans($con, $query) <= 0) {
                $_SESSION['s_sysmsg'] .= sprintf("リニア営業外収益その他再計算の更新失敗<br>第 %d期 %d月",$ki,$tuki);
                query_affected_trans($con, "rollback");     // Transaction Rollback
                header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
                exit();
            }
        }
        // 商管を加味する場合
        $query_chk = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='商管営業外収益その他再計算'", $yyyymm);
        if (getResult($query_chk,$res_chk) <= 0) {  // 既登録済みのチェック
            // 新規登録
            $query = sprintf("insert into act_pl_history (pl_bs_ym, kin, note) values (%d, %d, '商管営業外収益その他再計算')", $yyyymm, $p_other_b);
            if (query_affected_trans($con, $query) <= 0) {
                $_SESSION['s_sysmsg'] .= sprintf("商管営業外収益その他再計算の登録に失敗<br>第 %d期 %d月",$ki,$tuki);
                query_affected_trans($con, "rollback");     // Transaction Rollback
                header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
                exit();
            }
        } else {
            $query = sprintf("update act_pl_history set kin=%d where pl_bs_ym=%d and note='商管営業外収益その他再計算'", $p_other_b, $yyyymm);
            if (query_affected_trans($con, $query) <= 0) {
                $_SESSION['s_sysmsg'] .= sprintf("商管営業外収益その他再計算の更新失敗<br>第 %d期 %d月",$ki,$tuki);
                query_affected_trans($con, "rollback");     // Transaction Rollback
                header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
                exit();
            }
        }
        
        $query_chk = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='試修営業外収益その他再計算'", $yyyymm);
        if (getResult($query_chk,$res_chk) <= 0) {  // 既登録済みのチェック
            // 新規登録
            $query = sprintf("insert into act_pl_history (pl_bs_ym, kin, note) values (%d, %d, '試修営業外収益その他再計算')", $yyyymm, $p_other_s);
            if (query_affected_trans($con, $query) <= 0) {
                $_SESSION['s_sysmsg'] .= sprintf("試修営業外収益その他再計算の登録に失敗<br>第 %d期 %d月",$ki,$tuki);
                query_affected_trans($con, "rollback");     // Transaction Rollback
                header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
                exit();
            }
        } else {
            $query = sprintf("update act_pl_history set kin=%d where pl_bs_ym=%d and note='試修営業外収益その他再計算'", $p_other_s, $yyyymm);
            if (query_affected_trans($con, $query) <= 0) {
                $_SESSION['s_sysmsg'] .= sprintf("試修営業外収益その他再計算の更新失敗<br>第 %d期 %d月",$ki,$tuki);
                query_affected_trans($con, "rollback");     // Transaction Rollback
                header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
                exit();
            }
        }
        $query_chk = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='カプラ特注営業外収益その他再計算'", $yyyymm);
        if (getResult($query_chk,$res_chk) <= 0) {  // 既登録済みのチェック
            // 新規登録
            $query = sprintf("insert into act_pl_history (pl_bs_ym, kin, note) values (%d, %d, 'カプラ特注営業外収益その他再計算')", $yyyymm, $p_other_ctoku);
            if (query_affected_trans($con, $query) <= 0) {
                $_SESSION['s_sysmsg'] .= sprintf("カプラ特注営業外収益その他再計算の登録に失敗<br>第 %d期 %d月",$ki,$tuki);
                query_affected_trans($con, "rollback");     // Transaction Rollback
                header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
                exit();
            }
        } else {
            $query = sprintf("update act_pl_history set kin=%d where pl_bs_ym=%d and note='カプラ特注営業外収益その他再計算'", $p_other_ctoku, $yyyymm);
            if (query_affected_trans($con, $query) <= 0) {
                $_SESSION['s_sysmsg'] .= sprintf("カプラ特注営業外収益その他再計算の更新失敗<br>第 %d期 %d月",$ki,$tuki);
                query_affected_trans($con, "rollback");     // Transaction Rollback
                header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
                exit();
            }
        }
        $query_chk = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='カプラ標準営業外収益その他再計算'", $yyyymm);
        if (getResult($query_chk,$res_chk) <= 0) {  // 既登録済みのチェック
            // 新規登録
            $query = sprintf("insert into act_pl_history (pl_bs_ym, kin, note) values (%d, %d, 'カプラ標準営業外収益その他再計算')", $yyyymm, $p_other_chyou);
            if (query_affected_trans($con, $query) <= 0) {
                $_SESSION['s_sysmsg'] .= sprintf("カプラ標準営業外収益その他再計算の登録に失敗<br>第 %d期 %d月",$ki,$tuki);
                query_affected_trans($con, "rollback");     // Transaction Rollback
                header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
                exit();
            }
        } else {
            $query = sprintf("update act_pl_history set kin=%d where pl_bs_ym=%d and note='カプラ標準営業外収益その他再計算'", $p_other_chyou, $yyyymm);
            if (query_affected_trans($con, $query) <= 0) {
                $_SESSION['s_sysmsg'] .= sprintf("カプラ標準営業外収益その他再計算の更新失敗<br>第 %d期 %d月",$ki,$tuki);
                query_affected_trans($con, "rollback");     // Transaction Rollback
                header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
                exit();
            }
        }
        $query_chk = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='バイモル営業外収益その他再計算'", $yyyymm);
        if (getResult($query_chk,$res_chk) <= 0) {  // 既登録済みのチェック
            // 新規登録
            $query = sprintf("insert into act_pl_history (pl_bs_ym, kin, note) values (%d, %d, 'バイモル営業外収益その他再計算')", $yyyymm, $p_other_lb);
            if (query_affected_trans($con, $query) <= 0) {
                $_SESSION['s_sysmsg'] .= sprintf("バイモル営業外収益その他再計算の登録に失敗<br>第 %d期 %d月",$ki,$tuki);
                query_affected_trans($con, "rollback");     // Transaction Rollback
                header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
                exit();
            }
        } else {
            $query = sprintf("update act_pl_history set kin=%d where pl_bs_ym=%d and note='バイモル営業外収益その他再計算'", $p_other_lb, $yyyymm);
            if (query_affected_trans($con, $query) <= 0) {
                $_SESSION['s_sysmsg'] .= sprintf("バイモル営業外収益その他再計算の更新失敗<br>第 %d期 %d月",$ki,$tuki);
                query_affected_trans($con, "rollback");     // Transaction Rollback
                header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
                exit();
            }
        }
        $query_chk = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='リニア標準営業外収益その他再計算'", $yyyymm);
        if (getResult($query_chk,$res_chk) <= 0) {  // 既登録済みのチェック
            // 新規登録
            $query = sprintf("insert into act_pl_history (pl_bs_ym, kin, note) values (%d, %d, 'リニア標準営業外収益その他再計算')", $yyyymm, $p_other_lh);
            if (query_affected_trans($con, $query) <= 0) {
                $_SESSION['s_sysmsg'] .= sprintf("リニア標準営業外収益その他再計算の登録に失敗<br>第 %d期 %d月",$ki,$tuki);
                query_affected_trans($con, "rollback");     // Transaction Rollback
                header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
                exit();
            }
        } else {
            $query = sprintf("update act_pl_history set kin=%d where pl_bs_ym=%d and note='リニア標準営業外収益その他再計算'", $p_other_lh, $yyyymm);
            if (query_affected_trans($con, $query) <= 0) {
                $_SESSION['s_sysmsg'] .= sprintf("リニア標準営業外収益その他再計算の更新失敗<br>第 %d期 %d月",$ki,$tuki);
                query_affected_trans($con, "rollback");     // Transaction Rollback
                header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
                exit();
            }
        }
        $query_chk = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='カプラ試修営業外収益その他再計算'", $yyyymm);
        if (getResult($query_chk,$res_chk) <= 0) {  // 既登録済みのチェック
            // 新規登録
            $query = sprintf("insert into act_pl_history (pl_bs_ym, kin, note) values (%d, %d, 'カプラ試修営業外収益その他再計算')", $yyyymm, $p_other_sc);
            if (query_affected_trans($con, $query) <= 0) {
                $_SESSION['s_sysmsg'] .= sprintf("カプラ試修営業外収益その他再計算の登録に失敗<br>第 %d期 %d月",$ki,$tuki);
                query_affected_trans($con, "rollback");     // Transaction Rollback
                header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
                exit();
            }
        } else {
            $query = sprintf("update act_pl_history set kin=%d where pl_bs_ym=%d and note='カプラ試修営業外収益その他再計算'", $p_other_sc, $yyyymm);
            if (query_affected_trans($con, $query) <= 0) {
                $_SESSION['s_sysmsg'] .= sprintf("カプラ試修営業外収益その他再計算の更新失敗<br>第 %d期 %d月",$ki,$tuki);
                query_affected_trans($con, "rollback");     // Transaction Rollback
                header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
                exit();
            }
        }
        $query_chk = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='リニア試修営業外収益その他再計算'", $yyyymm);
        if (getResult($query_chk,$res_chk) <= 0) {  // 既登録済みのチェック
            // 新規登録
            $query = sprintf("insert into act_pl_history (pl_bs_ym, kin, note) values (%d, %d, 'リニア試修営業外収益その他再計算')", $yyyymm, $p_other_sl);
            if (query_affected_trans($con, $query) <= 0) {
                $_SESSION['s_sysmsg'] .= sprintf("リニア試修営業外収益その他再計算の登録に失敗<br>第 %d期 %d月",$ki,$tuki);
                query_affected_trans($con, "rollback");     // Transaction Rollback
                header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
                exit();
            }
        } else {
            $query = sprintf("update act_pl_history set kin=%d where pl_bs_ym=%d and note='リニア試修営業外収益その他再計算'", $p_other_sl, $yyyymm);
            if (query_affected_trans($con, $query) <= 0) {
                $_SESSION['s_sysmsg'] .= sprintf("リニア試修営業外収益その他再計算の更新失敗<br>第 %d期 %d月",$ki,$tuki);
                query_affected_trans($con, "rollback");     // Transaction Rollback
                header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
                exit();
            }
        }
        $query_chk = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='修理営業外収益その他再計算'", $yyyymm);
        if (getResult($query_chk,$res_chk) <= 0) {  // 既登録済みのチェック
            // 新規登録
            $query = sprintf("insert into act_pl_history (pl_bs_ym, kin, note) values (%d, %d, '修理営業外収益その他再計算')", $yyyymm, $p_other_ss);
            if (query_affected_trans($con, $query) <= 0) {
                $_SESSION['s_sysmsg'] .= sprintf("修理営業外収益その他再計算の登録に失敗<br>第 %d期 %d月",$ki,$tuki);
                query_affected_trans($con, "rollback");     // Transaction Rollback
                header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
                exit();
            }
        } else {
            $query = sprintf("update act_pl_history set kin=%d where pl_bs_ym=%d and note='修理営業外収益その他再計算'", $p_other_ss, $yyyymm);
            if (query_affected_trans($con, $query) <= 0) {
                $_SESSION['s_sysmsg'] .= sprintf("修理営業外収益その他再計算の更新失敗<br>第 %d期 %d月",$ki,$tuki);
                query_affected_trans($con, "rollback");     // Transaction Rollback
                header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
                exit();
            }
        }
        $query_chk = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='耐久営業外収益その他再計算'", $yyyymm);
        if (getResult($query_chk,$res_chk) <= 0) {  // 既登録済みのチェック
            // 新規登録
            $query = sprintf("insert into act_pl_history (pl_bs_ym, kin, note) values (%d, %d, '耐久営業外収益その他再計算')", $yyyymm, $p_other_st);
            if (query_affected_trans($con, $query) <= 0) {
                $_SESSION['s_sysmsg'] .= sprintf("耐久営業外収益その他再計算の登録に失敗<br>第 %d期 %d月",$ki,$tuki);
                query_affected_trans($con, "rollback");     // Transaction Rollback
                header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
                exit();
            }
        } else {
            $query = sprintf("update act_pl_history set kin=%d where pl_bs_ym=%d and note='耐久営業外収益その他再計算'", $p_other_st, $yyyymm);
            if (query_affected_trans($con, $query) <= 0) {
                $_SESSION['s_sysmsg'] .= sprintf("耐久営業外収益その他再計算の更新失敗<br>第 %d期 %d月",$ki,$tuki);
                query_affected_trans($con, "rollback");     // Transaction Rollback
                header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
                exit();
            }
        }
        
        /***** 営業外収益計の取得 *****/
        //$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='全体営業外収益計'", $yyyymm);
        //getUniResult($query,$nonope_p_sum);
        //$nonope_p_sum       = $nonope_p_sum;
        // 営業外収益計の計算
        $nonope_p_sum_c     = $gyoumu_c + $s_wari_c + $p_other_c;               // カプラ営業外収益計
        $nonope_p_sum_l     = $gyoumu_l + $s_wari_l + $p_other_l;               // リニア営業外収益計
        $nonope_p_sum_b     = $gyoumu_b + $s_wari_b + $p_other_b;               // 商管営業外収益計
        $nonope_p_sum_s     = $gyoumu_s + $s_wari_s + $p_other_s;               // 試修営業外収益計
        
        $nonope_p_sum_ctoku = $gyoumu_ctoku + $s_wari_ctoku + $p_other_ctoku;   // カプラ特注営業外収益計
        $nonope_p_sum_chyou = $gyoumu_chyou + $s_wari_chyou + $p_other_chyou;   // カプラ標準営業外収益計
        $nonope_p_sum_lb    = $gyoumu_lb + $s_wari_lb + $p_other_lb;            // バイモル営業外収益計
        $nonope_p_sum_lh    = $gyoumu_lh + $s_wari_lh + $p_other_lh;            // リニア標準営業外収益計
        $nonope_p_sum_sc    = $gyoumu_sc + $s_wari_sc + $p_other_sc;            // カプラ試修営業外収益計
        $nonope_p_sum_sl    = $gyoumu_sl + $s_wari_sl + $p_other_sl;            // リニア試修営業外収益計
        
        // 修理・耐久計算
        $nonope_p_sum_ss    = $gyoumu_ss + $s_wari_ss + $p_other_ss;            // 修理営業外収益計
        $nonope_p_sum_st    = $gyoumu_st + $s_wari_st + $p_other_st;            // 耐久営業外収益計
        
        // 営業外収益計の登録
        $query_chk = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='カプラ営業外収益計再計算'", $yyyymm);
        if (getResult($query_chk,$res_chk) <= 0) {  // 既登録済みのチェック
            // 新規登録
            $query = sprintf("insert into act_pl_history (pl_bs_ym, kin, note) values (%d, %d, 'カプラ営業外収益計再計算')", $yyyymm, $nonope_p_sum_c);
            if (query_affected_trans($con, $query) <= 0) {
                $_SESSION['s_sysmsg'] .= sprintf("カプラ営業外収益計再計算の登録に失敗<br>第 %d期 %d月",$ki,$tuki);
                query_affected_trans($con, "rollback");     // Transaction Rollback
                header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
                exit();
            }
        } else {
            $query = sprintf("update act_pl_history set kin=%d where pl_bs_ym=%d and note='カプラ営業外収益計再計算'", $nonope_p_sum_c, $yyyymm);
            if (query_affected_trans($con, $query) <= 0) {
                $_SESSION['s_sysmsg'] .= sprintf("カプラ営業外収益計再計算の更新失敗<br>第 %d期 %d月",$ki,$tuki);
                query_affected_trans($con, "rollback");     // Transaction Rollback
                header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
                exit();
            }
        }
        $query_chk = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='リニア営業外収益計再計算'", $yyyymm);
        if (getResult($query_chk,$res_chk) <= 0) {  // 既登録済みのチェック
            // 新規登録
            $query = sprintf("insert into act_pl_history (pl_bs_ym, kin, note) values (%d, %d, 'リニア営業外収益計再計算')", $yyyymm, $nonope_p_sum_l);
            if (query_affected_trans($con, $query) <= 0) {
                $_SESSION['s_sysmsg'] .= sprintf("リニア営業外収益計再計算の登録に失敗<br>第 %d期 %d月",$ki,$tuki);
                query_affected_trans($con, "rollback");     // Transaction Rollback
                header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
                exit();
            }
        } else {
            $query = sprintf("update act_pl_history set kin=%d where pl_bs_ym=%d and note='リニア営業外収益計再計算'", $nonope_p_sum_l, $yyyymm);
            if (query_affected_trans($con, $query) <= 0) {
                $_SESSION['s_sysmsg'] .= sprintf("リニア営業外収益計再計算の更新失敗<br>第 %d期 %d月",$ki,$tuki);
                query_affected_trans($con, "rollback");     // Transaction Rollback
                header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
                exit();
            }
        }
        // 商管を加味する場合
        $query_chk = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='商管営業外収益計再計算'", $yyyymm);
        if (getResult($query_chk,$res_chk) <= 0) {  // 既登録済みのチェック
            // 新規登録
            $query = sprintf("insert into act_pl_history (pl_bs_ym, kin, note) values (%d, %d, '商管営業外収益計再計算')", $yyyymm, $nonope_p_sum_b);
            if (query_affected_trans($con, $query) <= 0) {
                $_SESSION['s_sysmsg'] .= sprintf("商管営業外収益計再計算の登録に失敗<br>第 %d期 %d月",$ki,$tuki);
                query_affected_trans($con, "rollback");     // Transaction Rollback
                header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
                exit();
            }
        } else {
            $query = sprintf("update act_pl_history set kin=%d where pl_bs_ym=%d and note='商管営業外収益計再計算'", $nonope_p_sum_b, $yyyymm);
            if (query_affected_trans($con, $query) <= 0) {
                $_SESSION['s_sysmsg'] .= sprintf("商管営業外収益計再計算の更新失敗<br>第 %d期 %d月",$ki,$tuki);
                query_affected_trans($con, "rollback");     // Transaction Rollback
                header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
                exit();
            }
        }
        
        $query_chk = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='試修営業外収益計再計算'", $yyyymm);
        if (getResult($query_chk,$res_chk) <= 0) {  // 既登録済みのチェック
            // 新規登録
            $query = sprintf("insert into act_pl_history (pl_bs_ym, kin, note) values (%d, %d, '試修営業外収益計再計算')", $yyyymm, $nonope_p_sum_s);
            if (query_affected_trans($con, $query) <= 0) {
                $_SESSION['s_sysmsg'] .= sprintf("試修営業外収益計再計算の登録に失敗<br>第 %d期 %d月",$ki,$tuki);
                query_affected_trans($con, "rollback");     // Transaction Rollback
                header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
                exit();
            }
        } else {
            $query = sprintf("update act_pl_history set kin=%d where pl_bs_ym=%d and note='試修営業外収益計再計算'", $nonope_p_sum_s, $yyyymm);
            if (query_affected_trans($con, $query) <= 0) {
                $_SESSION['s_sysmsg'] .= sprintf("試修営業外収益計再計算の更新失敗<br>第 %d期 %d月",$ki,$tuki);
                query_affected_trans($con, "rollback");     // Transaction Rollback
                header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
                exit();
            }
        }
        $query_chk = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='カプラ特注営業外収益計再計算'", $yyyymm);
        if (getResult($query_chk,$res_chk) <= 0) {  // 既登録済みのチェック
            // 新規登録
            $query = sprintf("insert into act_pl_history (pl_bs_ym, kin, note) values (%d, %d, 'カプラ特注営業外収益計再計算')", $yyyymm, $nonope_p_sum_ctoku);
            if (query_affected_trans($con, $query) <= 0) {
                $_SESSION['s_sysmsg'] .= sprintf("カプラ特注営業外収益計再計算の登録に失敗<br>第 %d期 %d月",$ki,$tuki);
                query_affected_trans($con, "rollback");     // Transaction Rollback
                header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
                exit();
            }
        } else {
            $query = sprintf("update act_pl_history set kin=%d where pl_bs_ym=%d and note='カプラ特注営業外収益計再計算'", $nonope_p_sum_ctoku, $yyyymm);
            if (query_affected_trans($con, $query) <= 0) {
                $_SESSION['s_sysmsg'] .= sprintf("カプラ特注営業外収益計再計算の更新失敗<br>第 %d期 %d月",$ki,$tuki);
                query_affected_trans($con, "rollback");     // Transaction Rollback
                header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
                exit();
            }
        }
        $query_chk = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='カプラ標準営業外収益計再計算'", $yyyymm);
        if (getResult($query_chk,$res_chk) <= 0) {  // 既登録済みのチェック
            // 新規登録
            $query = sprintf("insert into act_pl_history (pl_bs_ym, kin, note) values (%d, %d, 'カプラ標準営業外収益計再計算')", $yyyymm, $nonope_p_sum_chyou);
            if (query_affected_trans($con, $query) <= 0) {
                $_SESSION['s_sysmsg'] .= sprintf("カプラ標準営業外収益計再計算の登録に失敗<br>第 %d期 %d月",$ki,$tuki);
                query_affected_trans($con, "rollback");     // Transaction Rollback
                header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
                exit();
            }
        } else {
            $query = sprintf("update act_pl_history set kin=%d where pl_bs_ym=%d and note='カプラ標準営業外収益計再計算'", $nonope_p_sum_chyou, $yyyymm);
            if (query_affected_trans($con, $query) <= 0) {
                $_SESSION['s_sysmsg'] .= sprintf("カプラ標準営業外収益計再計算の更新失敗<br>第 %d期 %d月",$ki,$tuki);
                query_affected_trans($con, "rollback");     // Transaction Rollback
                header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
                exit();
            }
        }
        $query_chk = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='バイモル営業外収益計再計算'", $yyyymm);
        if (getResult($query_chk,$res_chk) <= 0) {  // 既登録済みのチェック
            // 新規登録
            $query = sprintf("insert into act_pl_history (pl_bs_ym, kin, note) values (%d, %d, 'バイモル営業外収益計再計算')", $yyyymm, $nonope_p_sum_lb);
            if (query_affected_trans($con, $query) <= 0) {
                $_SESSION['s_sysmsg'] .= sprintf("バイモル営業外収益計再計算の登録に失敗<br>第 %d期 %d月",$ki,$tuki);
                query_affected_trans($con, "rollback");     // Transaction Rollback
                header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
                exit();
            }
        } else {
            $query = sprintf("update act_pl_history set kin=%d where pl_bs_ym=%d and note='バイモル営業外収益計再計算'", $nonope_p_sum_lb, $yyyymm);
            if (query_affected_trans($con, $query) <= 0) {
                $_SESSION['s_sysmsg'] .= sprintf("バイモル営業外収益計再計算の更新失敗<br>第 %d期 %d月",$ki,$tuki);
                query_affected_trans($con, "rollback");     // Transaction Rollback
                header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
                exit();
            }
        }
        $query_chk = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='リニア標準営業外収益計再計算'", $yyyymm);
        if (getResult($query_chk,$res_chk) <= 0) {  // 既登録済みのチェック
            // 新規登録
            $query = sprintf("insert into act_pl_history (pl_bs_ym, kin, note) values (%d, %d, 'リニア標準営業外収益計再計算')", $yyyymm, $nonope_p_sum_lh);
            if (query_affected_trans($con, $query) <= 0) {
                $_SESSION['s_sysmsg'] .= sprintf("リニア標準営業外収益計再計算の登録に失敗<br>第 %d期 %d月",$ki,$tuki);
                query_affected_trans($con, "rollback");     // Transaction Rollback
                header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
                exit();
            }
        } else {
            $query = sprintf("update act_pl_history set kin=%d where pl_bs_ym=%d and note='リニア標準営業外収益計再計算'", $nonope_p_sum_lh, $yyyymm);
            if (query_affected_trans($con, $query) <= 0) {
                $_SESSION['s_sysmsg'] .= sprintf("リニア標準営業外収益計再計算の更新失敗<br>第 %d期 %d月",$ki,$tuki);
                query_affected_trans($con, "rollback");     // Transaction Rollback
                header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
                exit();
            }
        }
        $query_chk = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='カプラ試修営業外収益計再計算'", $yyyymm);
        if (getResult($query_chk,$res_chk) <= 0) {  // 既登録済みのチェック
            // 新規登録
            $query = sprintf("insert into act_pl_history (pl_bs_ym, kin, note) values (%d, %d, 'カプラ試修営業外収益計再計算')", $yyyymm, $nonope_p_sum_sc);
            if (query_affected_trans($con, $query) <= 0) {
                $_SESSION['s_sysmsg'] .= sprintf("カプラ試修営業外収益計再計算の登録に失敗<br>第 %d期 %d月",$ki,$tuki);
                query_affected_trans($con, "rollback");     // Transaction Rollback
                header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
                exit();
            }
        } else {
            $query = sprintf("update act_pl_history set kin=%d where pl_bs_ym=%d and note='カプラ試修営業外収益計再計算'", $nonope_p_sum_sc, $yyyymm);
            if (query_affected_trans($con, $query) <= 0) {
                $_SESSION['s_sysmsg'] .= sprintf("カプラ試修営業外収益計再計算の更新失敗<br>第 %d期 %d月",$ki,$tuki);
                query_affected_trans($con, "rollback");     // Transaction Rollback
                header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
                exit();
            }
        }
        $query_chk = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='リニア試修営業外収益計再計算'", $yyyymm);
        if (getResult($query_chk,$res_chk) <= 0) {  // 既登録済みのチェック
            // 新規登録
            $query = sprintf("insert into act_pl_history (pl_bs_ym, kin, note) values (%d, %d, 'リニア試修営業外収益計再計算')", $yyyymm, $nonope_p_sum_sl);
            if (query_affected_trans($con, $query) <= 0) {
                $_SESSION['s_sysmsg'] .= sprintf("リニア試修営業外収益計再計算の登録に失敗<br>第 %d期 %d月",$ki,$tuki);
                query_affected_trans($con, "rollback");     // Transaction Rollback
                header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
                exit();
            }
        } else {
            $query = sprintf("update act_pl_history set kin=%d where pl_bs_ym=%d and note='リニア試修営業外収益計再計算'", $nonope_p_sum_sl, $yyyymm);
            if (query_affected_trans($con, $query) <= 0) {
                $_SESSION['s_sysmsg'] .= sprintf("リニア試修営業外収益計再計算の更新失敗<br>第 %d期 %d月",$ki,$tuki);
                query_affected_trans($con, "rollback");     // Transaction Rollback
                header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
                exit();
            }
        }
        $query_chk = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='修理営業外収益計再計算'", $yyyymm);
        if (getResult($query_chk,$res_chk) <= 0) {  // 既登録済みのチェック
            // 新規登録
            $query = sprintf("insert into act_pl_history (pl_bs_ym, kin, note) values (%d, %d, '修理営業外収益計再計算')", $yyyymm, $nonope_p_sum_ss);
            if (query_affected_trans($con, $query) <= 0) {
                $_SESSION['s_sysmsg'] .= sprintf("修理営業外収益計再計算の登録に失敗<br>第 %d期 %d月",$ki,$tuki);
                query_affected_trans($con, "rollback");     // Transaction Rollback
                header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
                exit();
            }
        } else {
            $query = sprintf("update act_pl_history set kin=%d where pl_bs_ym=%d and note='修理営業外収益計再計算'", $nonope_p_sum_ss, $yyyymm);
            if (query_affected_trans($con, $query) <= 0) {
                $_SESSION['s_sysmsg'] .= sprintf("修理営業外収益計再計算の更新失敗<br>第 %d期 %d月",$ki,$tuki);
                query_affected_trans($con, "rollback");     // Transaction Rollback
                header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
                exit();
            }
        }
        $query_chk = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='耐久営業外収益計再計算'", $yyyymm);
        if (getResult($query_chk,$res_chk) <= 0) {  // 既登録済みのチェック
            // 新規登録
            $query = sprintf("insert into act_pl_history (pl_bs_ym, kin, note) values (%d, %d, '耐久営業外収益計再計算')", $yyyymm, $nonope_p_sum_st);
            if (query_affected_trans($con, $query) <= 0) {
                $_SESSION['s_sysmsg'] .= sprintf("耐久営業外収益計再計算の登録に失敗<br>第 %d期 %d月",$ki,$tuki);
                query_affected_trans($con, "rollback");     // Transaction Rollback
                header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
                exit();
            }
        } else {
            $query = sprintf("update act_pl_history set kin=%d where pl_bs_ym=%d and note='耐久営業外収益計再計算'", $nonope_p_sum_st, $yyyymm);
            if (query_affected_trans($con, $query) <= 0) {
                $_SESSION['s_sysmsg'] .= sprintf("耐久営業外収益計再計算の更新失敗<br>第 %d期 %d月",$ki,$tuki);
                query_affected_trans($con, "rollback");     // Transaction Rollback
                header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
                exit();
            }
        }
        
        /***** 支払利息の取得 *****/
        $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='全体支払利息'", $yyyymm);
        getUniResult($query,$risoku);
        $risoku       = $risoku;
        $risoku_c     = Uround(($risoku * $allo[0]), 0);                 // カプラ支払利息
        $risoku_l     = Uround(($risoku * $allo[3]), 0);                // リニア支払利息
        $risoku_b     = Uround(($risoku * $allo[9]), 0);                // 商管支払利息
        $risoku_s     = $risoku - $risoku_c - $risoku_l - $risoku_b;    // 試修支払利息
        
        $risoku_ctoku = Uround(($risoku_c * $allo[1]), 0);          // カプラ特注支払利息
        $risoku_chyou = $risoku_c - $risoku_ctoku;                  // カプラ標準支払利息
        $risoku_lb    = Uround(($risoku_l * $allo[4]), 0);          // バイモル支払利息
        $risoku_lh    = $risoku_l - $risoku_lb;                     // リニア標準支払利息
        $risoku_sc    = Uround(($risoku_s * $allo[7]), 0);          // カプラ試修支払利息
        $risoku_sl    = $risoku_s - $risoku_sl;                     // リニア試修支払利息
        
        // 修理・耐久計算
        $risoku_st = Uround(($risoku_s * $st_uri_allo), 0);
        $risoku_ss = $risoku_s - $risoku_st;
        
        // 支払利息の登録
        $query_chk = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='カプラ支払利息再計算'", $yyyymm);
        if (getResult($query_chk,$res_chk) <= 0) {  // 既登録済みのチェック
            // 新規登録
            $query = sprintf("insert into act_pl_history (pl_bs_ym, kin, note) values (%d, %d, 'カプラ支払利息再計算')", $yyyymm, $risoku_c);
            if (query_affected_trans($con, $query) <= 0) {
                $_SESSION['s_sysmsg'] .= sprintf("カプラ支払利息再計算の登録に失敗<br>第 %d期 %d月",$ki,$tuki);
                query_affected_trans($con, "rollback");     // Transaction Rollback
                header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
                exit();
            }
        } else {
            $query = sprintf("update act_pl_history set kin=%d where pl_bs_ym=%d and note='カプラ支払利息再計算'", $risoku_c, $yyyymm);
            if (query_affected_trans($con, $query) <= 0) {
                $_SESSION['s_sysmsg'] .= sprintf("カプラ支払利息再計算の更新失敗<br>第 %d期 %d月",$ki,$tuki);
                query_affected_trans($con, "rollback");     // Transaction Rollback
                header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
                exit();
            }
        }
        $query_chk = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='リニア支払利息再計算'", $yyyymm);
        if (getResult($query_chk,$res_chk) <= 0) {  // 既登録済みのチェック
            // 新規登録
            $query = sprintf("insert into act_pl_history (pl_bs_ym, kin, note) values (%d, %d, 'リニア支払利息再計算')", $yyyymm, $risoku_l);
            if (query_affected_trans($con, $query) <= 0) {
                $_SESSION['s_sysmsg'] .= sprintf("リニア支払利息再計算の登録に失敗<br>第 %d期 %d月",$ki,$tuki);
                query_affected_trans($con, "rollback");     // Transaction Rollback
                header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
                exit();
            }
        } else {
            $query = sprintf("update act_pl_history set kin=%d where pl_bs_ym=%d and note='リニア支払利息再計算'", $risoku_l, $yyyymm);
            if (query_affected_trans($con, $query) <= 0) {
                $_SESSION['s_sysmsg'] .= sprintf("リニア支払利息再計算の更新失敗<br>第 %d期 %d月",$ki,$tuki);
                query_affected_trans($con, "rollback");     // Transaction Rollback
                header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
                exit();
            }
        }
        // 商管を加味する場合
        $query_chk = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='商管支払利息再計算'", $yyyymm);
        if (getResult($query_chk,$res_chk) <= 0) {  // 既登録済みのチェック
            // 新規登録
            $query = sprintf("insert into act_pl_history (pl_bs_ym, kin, note) values (%d, %d, '商管支払利息再計算')", $yyyymm, $risoku_b);
            if (query_affected_trans($con, $query) <= 0) {
                $_SESSION['s_sysmsg'] .= sprintf("商管支払利息再計算の登録に失敗<br>第 %d期 %d月",$ki,$tuki);
                query_affected_trans($con, "rollback");     // Transaction Rollback
                header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
                exit();
            }
        } else {
            $query = sprintf("update act_pl_history set kin=%d where pl_bs_ym=%d and note='商管支払利息再計算'", $risoku_b, $yyyymm);
            if (query_affected_trans($con, $query) <= 0) {
                $_SESSION['s_sysmsg'] .= sprintf("商管支払利息再計算の更新失敗<br>第 %d期 %d月",$ki,$tuki);
                query_affected_trans($con, "rollback");     // Transaction Rollback
                header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
                exit();
            }
        }
        
        $query_chk = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='試修支払利息再計算'", $yyyymm);
        if (getResult($query_chk,$res_chk) <= 0) {  // 既登録済みのチェック
            // 新規登録
            $query = sprintf("insert into act_pl_history (pl_bs_ym, kin, note) values (%d, %d, '試修支払利息再計算')", $yyyymm, $risoku_s);
            if (query_affected_trans($con, $query) <= 0) {
                $_SESSION['s_sysmsg'] .= sprintf("試修支払利息再計算の登録に失敗<br>第 %d期 %d月",$ki,$tuki);
                query_affected_trans($con, "rollback");     // Transaction Rollback
                header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
                exit();
            }
        } else {
            $query = sprintf("update act_pl_history set kin=%d where pl_bs_ym=%d and note='試修支払利息再計算'", $risoku_s, $yyyymm);
            if (query_affected_trans($con, $query) <= 0) {
                $_SESSION['s_sysmsg'] .= sprintf("試修支払利息再計算の更新失敗<br>第 %d期 %d月",$ki,$tuki);
                query_affected_trans($con, "rollback");     // Transaction Rollback
                header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
                exit();
            }
        }
        $query_chk = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='カプラ特注支払利息再計算'", $yyyymm);
        if (getResult($query_chk,$res_chk) <= 0) {  // 既登録済みのチェック
            // 新規登録
            $query = sprintf("insert into act_pl_history (pl_bs_ym, kin, note) values (%d, %d, 'カプラ特注支払利息再計算')", $yyyymm, $risoku_ctoku);
            if (query_affected_trans($con, $query) <= 0) {
                $_SESSION['s_sysmsg'] .= sprintf("カプラ特注支払利息再計算の登録に失敗<br>第 %d期 %d月",$ki,$tuki);
                query_affected_trans($con, "rollback");     // Transaction Rollback
                header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
                exit();
            }
        } else {
            $query = sprintf("update act_pl_history set kin=%d where pl_bs_ym=%d and note='カプラ特注支払利息再計算'", $risoku_ctoku, $yyyymm);
            if (query_affected_trans($con, $query) <= 0) {
                $_SESSION['s_sysmsg'] .= sprintf("カプラ特注支払利息再計算の更新失敗<br>第 %d期 %d月",$ki,$tuki);
                query_affected_trans($con, "rollback");     // Transaction Rollback
                header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
                exit();
            }
        }
        $query_chk = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='カプラ標準支払利息再計算'", $yyyymm);
        if (getResult($query_chk,$res_chk) <= 0) {  // 既登録済みのチェック
            // 新規登録
            $query = sprintf("insert into act_pl_history (pl_bs_ym, kin, note) values (%d, %d, 'カプラ標準支払利息再計算')", $yyyymm, $risoku_chyou);
            if (query_affected_trans($con, $query) <= 0) {
                $_SESSION['s_sysmsg'] .= sprintf("カプラ標準支払利息再計算の登録に失敗<br>第 %d期 %d月",$ki,$tuki);
                query_affected_trans($con, "rollback");     // Transaction Rollback
                header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
                exit();
            }
        } else {
            $query = sprintf("update act_pl_history set kin=%d where pl_bs_ym=%d and note='カプラ標準支払利息再計算'", $risoku_chyou, $yyyymm);
            if (query_affected_trans($con, $query) <= 0) {
                $_SESSION['s_sysmsg'] .= sprintf("カプラ標準支払利息再計算の更新失敗<br>第 %d期 %d月",$ki,$tuki);
                query_affected_trans($con, "rollback");     // Transaction Rollback
                header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
                exit();
            }
        }
        $query_chk = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='バイモル支払利息再計算'", $yyyymm);
        if (getResult($query_chk,$res_chk) <= 0) {  // 既登録済みのチェック
            // 新規登録
            $query = sprintf("insert into act_pl_history (pl_bs_ym, kin, note) values (%d, %d, 'バイモル支払利息再計算')", $yyyymm, $risoku_lb);
            if (query_affected_trans($con, $query) <= 0) {
                $_SESSION['s_sysmsg'] .= sprintf("バイモル支払利息再計算の登録に失敗<br>第 %d期 %d月",$ki,$tuki);
                query_affected_trans($con, "rollback");     // Transaction Rollback
                header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
                exit();
            }
        } else {
            $query = sprintf("update act_pl_history set kin=%d where pl_bs_ym=%d and note='バイモル支払利息再計算'", $risoku_lb, $yyyymm);
            if (query_affected_trans($con, $query) <= 0) {
                $_SESSION['s_sysmsg'] .= sprintf("バイモル支払利息再計算の更新失敗<br>第 %d期 %d月",$ki,$tuki);
                query_affected_trans($con, "rollback");     // Transaction Rollback
                header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
                exit();
            }
        }
        $query_chk = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='リニア標準支払利息再計算'", $yyyymm);
        if (getResult($query_chk,$res_chk) <= 0) {  // 既登録済みのチェック
            // 新規登録
            $query = sprintf("insert into act_pl_history (pl_bs_ym, kin, note) values (%d, %d, 'リニア標準支払利息再計算')", $yyyymm, $risoku_lh);
            if (query_affected_trans($con, $query) <= 0) {
                $_SESSION['s_sysmsg'] .= sprintf("リニア標準支払利息再計算の登録に失敗<br>第 %d期 %d月",$ki,$tuki);
                query_affected_trans($con, "rollback");     // Transaction Rollback
                header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
                exit();
            }
        } else {
            $query = sprintf("update act_pl_history set kin=%d where pl_bs_ym=%d and note='リニア標準支払利息再計算'", $risoku_lh, $yyyymm);
            if (query_affected_trans($con, $query) <= 0) {
                $_SESSION['s_sysmsg'] .= sprintf("リニア標準支払利息再計算の更新失敗<br>第 %d期 %d月",$ki,$tuki);
                query_affected_trans($con, "rollback");     // Transaction Rollback
                header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
                exit();
            }
        }
        $query_chk = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='カプラ試修支払利息再計算'", $yyyymm);
        if (getResult($query_chk,$res_chk) <= 0) {  // 既登録済みのチェック
            // 新規登録
            $query = sprintf("insert into act_pl_history (pl_bs_ym, kin, note) values (%d, %d, 'カプラ試修支払利息再計算')", $yyyymm, $risoku_sc);
            if (query_affected_trans($con, $query) <= 0) {
                $_SESSION['s_sysmsg'] .= sprintf("カプラ試修支払利息再計算の登録に失敗<br>第 %d期 %d月",$ki,$tuki);
                query_affected_trans($con, "rollback");     // Transaction Rollback
                header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
                exit();
            }
        } else {
            $query = sprintf("update act_pl_history set kin=%d where pl_bs_ym=%d and note='カプラ試修支払利息再計算'", $risoku_sc, $yyyymm);
            if (query_affected_trans($con, $query) <= 0) {
                $_SESSION['s_sysmsg'] .= sprintf("カプラ試修支払利息再計算の更新失敗<br>第 %d期 %d月",$ki,$tuki);
                query_affected_trans($con, "rollback");     // Transaction Rollback
                header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
                exit();
            }
        }
        $query_chk = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='リニア試修支払利息再計算'", $yyyymm);
        if (getResult($query_chk,$res_chk) <= 0) {  // 既登録済みのチェック
            // 新規登録
            $query = sprintf("insert into act_pl_history (pl_bs_ym, kin, note) values (%d, %d, 'リニア試修支払利息再計算')", $yyyymm, $risoku_sl);
            if (query_affected_trans($con, $query) <= 0) {
                $_SESSION['s_sysmsg'] .= sprintf("リニア試修支払利息再計算の登録に失敗<br>第 %d期 %d月",$ki,$tuki);
                query_affected_trans($con, "rollback");     // Transaction Rollback
                header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
                exit();
            }
        } else {
            $query = sprintf("update act_pl_history set kin=%d where pl_bs_ym=%d and note='リニア試修支払利息再計算'", $risoku_sl, $yyyymm);
            if (query_affected_trans($con, $query) <= 0) {
                $_SESSION['s_sysmsg'] .= sprintf("リニア試修支払利息再計算の更新失敗<br>第 %d期 %d月",$ki,$tuki);
                query_affected_trans($con, "rollback");     // Transaction Rollback
                header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
                exit();
            }
        }
        $query_chk = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='修理支払利息再計算'", $yyyymm);
        if (getResult($query_chk,$res_chk) <= 0) {  // 既登録済みのチェック
            // 新規登録
            $query = sprintf("insert into act_pl_history (pl_bs_ym, kin, note) values (%d, %d, '修理支払利息再計算')", $yyyymm, $risoku_ss);
            if (query_affected_trans($con, $query) <= 0) {
                $_SESSION['s_sysmsg'] .= sprintf("修理支払利息再計算の登録に失敗<br>第 %d期 %d月",$ki,$tuki);
                query_affected_trans($con, "rollback");     // Transaction Rollback
                header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
                exit();
            }
        } else {
            $query = sprintf("update act_pl_history set kin=%d where pl_bs_ym=%d and note='修理支払利息再計算'", $risoku_ss, $yyyymm);
            if (query_affected_trans($con, $query) <= 0) {
                $_SESSION['s_sysmsg'] .= sprintf("修理支払利息再計算の更新失敗<br>第 %d期 %d月",$ki,$tuki);
                query_affected_trans($con, "rollback");     // Transaction Rollback
                header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
                exit();
            }
        }
        $query_chk = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='耐久支払利息再計算'", $yyyymm);
        if (getResult($query_chk,$res_chk) <= 0) {  // 既登録済みのチェック
            // 新規登録
            $query = sprintf("insert into act_pl_history (pl_bs_ym, kin, note) values (%d, %d, '耐久支払利息再計算')", $yyyymm, $risoku_st);
            if (query_affected_trans($con, $query) <= 0) {
                $_SESSION['s_sysmsg'] .= sprintf("耐久支払利息再計算の登録に失敗<br>第 %d期 %d月",$ki,$tuki);
                query_affected_trans($con, "rollback");     // Transaction Rollback
                header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
                exit();
            }
        } else {
            $query = sprintf("update act_pl_history set kin=%d where pl_bs_ym=%d and note='耐久支払利息再計算'", $risoku_st, $yyyymm);
            if (query_affected_trans($con, $query) <= 0) {
                $_SESSION['s_sysmsg'] .= sprintf("耐久支払利息再計算の更新失敗<br>第 %d期 %d月",$ki,$tuki);
                query_affected_trans($con, "rollback");     // Transaction Rollback
                header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
                exit();
            }
        }
        
        /***** 営業外費用その他の取得 *****/
        $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='全体営業外費用その他'", $yyyymm);
        getUniResult($query,$l_other);
        
        
        // NKIT有償支給為替差損益計算の為、配賦前に一度抜出（差損は費用、差益は収益）
        $l_other       = $l_other - $jin[11] - $jin[13] - $jin[15] - $jin[17];
        
        // 営業外費用を各セグメントのみに振り分ける為、配賦前に一度抜き出し
        $l_other       = $l_other - $jin[24] - $jin[25] - $jin[26] - $jin[27] - $jin[28] - $jin[29];
        
        // 各セグメント別の営業外収益その他の計算
        $l_other_c     = Uround(($l_other * $allo[0]), 0);                  // カプラ営業外費用その他
        $l_other_l     = Uround(($l_other * $allo[3]), 0);                  // リニア営業外費用その他
        $l_other_b     = Uround(($l_other * $allo[9]), 0);                  // 商管営業外費用その他
        $l_other_s     = $l_other - $l_other_c - $l_other_l - $l_other_b;   // 試修営業外費用その他
        
        $l_other_ctoku = Uround(($l_other_c * $allo[1]), 0);                // カプラ特注営業外費用その他
        $l_other_chyou = $l_other_c - $l_other_ctoku;                       // カプラ標準営業外費用その他
        $l_other_lb    = Uround(($l_other_l * $allo[4]), 0);                // バイモル営業外費用その他
        $l_other_lh    = $l_other_l - $l_other_lb;                          // リニア標準営業外費用その他
        $l_other_sc    = Uround(($l_other_s * $allo[7]), 0);                // カプラ試修営業外費用その他
        $l_other_sl    = $l_other_s - $l_other_sl;                          // リニア試修営業外費用その他
        
        // 各セグメントの為替差損益を戻す（差損は費用、差益は収益）
        $l_other_c     = $l_other_c + $jin[11] + $jin[13];                  // カプラ営業外収益その他
        $l_other_l     = $l_other_l + $jin[15] + $jin[17];                  // リニア営業外収益その他
        
        $l_other_ctoku = $l_other_ctoku + $jin[13];                         // カプラ特注営業外収益その他
        $l_other_chyou = $l_other_chyou + $jin[11];                         // カプラ標準営業外収益その他
        $l_other_lb    = $l_other_lb    + $jin[17];                         // バイモル営業外収益その他
        $l_other_lh    = $l_other_lh    + $jin[15];                         // リニア標準営業外収益その他
        
        // 各セグメントの営業外費用を戻す
        $l_other_c     = $l_other_c + $jin[24] + $jin[25];                  // カプラ営業外費用その他
        $l_other_l     = $l_other_l + $jin[26] + $jin[27];                  // リニア営業外費用その他
        $l_other_s     = $l_other_s + $jin[28];                             // 試修営業外費用その他
        $l_other_b     = $l_other_b + $jin[29];                             // 商管営業外費用その他
        
        $l_other_chyou = $l_other_chyou + $jin[24];                         // カプラ標準営業外費用その他
        $l_other_ctoku = $l_other_ctoku + $jin[25];                         // カプラ特注営業外費用その他
        $l_other_lh    = $l_other_lh    + $jin[26];                         // リニア標準営業外費用その他
        $l_other_lb    = $l_other_lb    + $jin[27];                         // バイモル営業外費用その他
        
        // 2012年6月のみすべてリニアな為手動入力
        if ($yyyymm == 201206) {
            $l_other_c     = 0;         // カプラ営業外費用その他
            $l_other_l     = 238144;    // リニア営業外費用その他
            $l_other_b     = 0;         // 商管営業外費用その他
            $l_other_s     = 0;         // 試修営業外費用その他
            
            $l_other_ctoku = 0;         // カプラ特注営業外費用その他
            $l_other_chyou = 0;         // カプラ標準営業外費用その他
            $l_other_lb    = 0;         // バイモル営業外費用その他
            $l_other_lh    = 238144;    // リニア標準営業外費用その他
            $l_other_sc    = 0;         // カプラ試修営業外費用その他
            $l_other_sl    = 0;         // リニア試修営業外費用その他
        }
        
        // 修理・耐久計算
        $l_other_st = Uround(($l_other_s * $st_uri_allo), 0);
        $l_other_ss = $l_other_s - $l_other_st;
        
        // 営業外費用その他の登録
        $query_chk = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='カプラ営業外費用その他再計算'", $yyyymm);
        if (getResult($query_chk,$res_chk) <= 0) {  // 既登録済みのチェック
            // 新規登録
            $query = sprintf("insert into act_pl_history (pl_bs_ym, kin, note) values (%d, %d, 'カプラ営業外費用その他再計算')", $yyyymm, $l_other_c);
            if (query_affected_trans($con, $query) <= 0) {
                $_SESSION['s_sysmsg'] .= sprintf("カプラ営業外費用その他再計算の登録に失敗<br>第 %d期 %d月",$ki,$tuki);
                query_affected_trans($con, "rollback");     // Transaction Rollback
                header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
                exit();
            }
        } else {
            $query = sprintf("update act_pl_history set kin=%d where pl_bs_ym=%d and note='カプラ営業外費用その他再計算'", $l_other_c, $yyyymm);
            if (query_affected_trans($con, $query) <= 0) {
                $_SESSION['s_sysmsg'] .= sprintf("カプラ営業外費用その他再計算の更新失敗<br>第 %d期 %d月",$ki,$tuki);
                query_affected_trans($con, "rollback");     // Transaction Rollback
                header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
                exit();
            }
        }
        $query_chk = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='リニア営業外費用その他再計算'", $yyyymm);
        if (getResult($query_chk,$res_chk) <= 0) {  // 既登録済みのチェック
            // 新規登録
            $query = sprintf("insert into act_pl_history (pl_bs_ym, kin, note) values (%d, %d, 'リニア営業外費用その他再計算')", $yyyymm, $l_other_l);
            if (query_affected_trans($con, $query) <= 0) {
                $_SESSION['s_sysmsg'] .= sprintf("リニア営業外費用その他再計算の登録に失敗<br>第 %d期 %d月",$ki,$tuki);
                query_affected_trans($con, "rollback");     // Transaction Rollback
                header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
                exit();
            }
        } else {
            $query = sprintf("update act_pl_history set kin=%d where pl_bs_ym=%d and note='リニア営業外費用その他再計算'", $l_other_l, $yyyymm);
            if (query_affected_trans($con, $query) <= 0) {
                $_SESSION['s_sysmsg'] .= sprintf("リニア営業外費用その他再計算の更新失敗<br>第 %d期 %d月",$ki,$tuki);
                query_affected_trans($con, "rollback");     // Transaction Rollback
                header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
                exit();
            }
        }
        // 商管を加味する場合
        $query_chk = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='商管営業外費用その他再計算'", $yyyymm);
        if (getResult($query_chk,$res_chk) <= 0) {  // 既登録済みのチェック
            // 新規登録
            $query = sprintf("insert into act_pl_history (pl_bs_ym, kin, note) values (%d, %d, '商管営業外費用その他再計算')", $yyyymm, $l_other_b);
            if (query_affected_trans($con, $query) <= 0) {
                $_SESSION['s_sysmsg'] .= sprintf("商管営業外費用その他再計算の登録に失敗<br>第 %d期 %d月",$ki,$tuki);
                query_affected_trans($con, "rollback");     // Transaction Rollback
                header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
                exit();
            }
        } else {
            $query = sprintf("update act_pl_history set kin=%d where pl_bs_ym=%d and note='商管営業外費用その他再計算'", $l_other_b, $yyyymm);
            if (query_affected_trans($con, $query) <= 0) {
                $_SESSION['s_sysmsg'] .= sprintf("商管営業外費用その他再計算の更新失敗<br>第 %d期 %d月",$ki,$tuki);
                query_affected_trans($con, "rollback");     // Transaction Rollback
                header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
                exit();
            }
        }
        
        $query_chk = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='試修営業外費用その他再計算'", $yyyymm);
        if (getResult($query_chk,$res_chk) <= 0) {  // 既登録済みのチェック
            // 新規登録
            $query = sprintf("insert into act_pl_history (pl_bs_ym, kin, note) values (%d, %d, '試修営業外費用その他再計算')", $yyyymm, $l_other_s);
            if (query_affected_trans($con, $query) <= 0) {
                $_SESSION['s_sysmsg'] .= sprintf("試修営業外費用その他再計算の登録に失敗<br>第 %d期 %d月",$ki,$tuki);
                query_affected_trans($con, "rollback");     // Transaction Rollback
                header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
                exit();
            }
        } else {
            $query = sprintf("update act_pl_history set kin=%d where pl_bs_ym=%d and note='試修営業外費用その他再計算'", $l_other_s, $yyyymm);
            if (query_affected_trans($con, $query) <= 0) {
                $_SESSION['s_sysmsg'] .= sprintf("試修営業外費用その他再計算の更新失敗<br>第 %d期 %d月",$ki,$tuki);
                query_affected_trans($con, "rollback");     // Transaction Rollback
                header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
                exit();
            }
        }
        $query_chk = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='カプラ特注営業外費用その他再計算'", $yyyymm);
        if (getResult($query_chk,$res_chk) <= 0) {  // 既登録済みのチェック
            // 新規登録
            $query = sprintf("insert into act_pl_history (pl_bs_ym, kin, note) values (%d, %d, 'カプラ特注営業外費用その他再計算')", $yyyymm, $l_other_ctoku);
            if (query_affected_trans($con, $query) <= 0) {
                $_SESSION['s_sysmsg'] .= sprintf("カプラ特注営業外費用その他再計算の登録に失敗<br>第 %d期 %d月",$ki,$tuki);
                query_affected_trans($con, "rollback");     // Transaction Rollback
                header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
                exit();
            }
        } else {
            $query = sprintf("update act_pl_history set kin=%d where pl_bs_ym=%d and note='カプラ特注営業外費用その他再計算'", $l_other_ctoku, $yyyymm);
            if (query_affected_trans($con, $query) <= 0) {
                $_SESSION['s_sysmsg'] .= sprintf("カプラ特注営業外費用その他再計算の更新失敗<br>第 %d期 %d月",$ki,$tuki);
                query_affected_trans($con, "rollback");     // Transaction Rollback
                header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
                exit();
            }
        }
        $query_chk = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='カプラ標準営業外費用その他再計算'", $yyyymm);
        if (getResult($query_chk,$res_chk) <= 0) {  // 既登録済みのチェック
            // 新規登録
            $query = sprintf("insert into act_pl_history (pl_bs_ym, kin, note) values (%d, %d, 'カプラ標準営業外費用その他再計算')", $yyyymm, $l_other_chyou);
            if (query_affected_trans($con, $query) <= 0) {
                $_SESSION['s_sysmsg'] .= sprintf("カプラ標準営業外費用その他再計算の登録に失敗<br>第 %d期 %d月",$ki,$tuki);
                query_affected_trans($con, "rollback");     // Transaction Rollback
                header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
                exit();
            }
        } else {
            $query = sprintf("update act_pl_history set kin=%d where pl_bs_ym=%d and note='カプラ標準営業外費用その他再計算'", $l_other_chyou, $yyyymm);
            if (query_affected_trans($con, $query) <= 0) {
                $_SESSION['s_sysmsg'] .= sprintf("カプラ標準営業外費用その他再計算の更新失敗<br>第 %d期 %d月",$ki,$tuki);
                query_affected_trans($con, "rollback");     // Transaction Rollback
                header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
                exit();
            }
        }
        $query_chk = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='バイモル営業外費用その他再計算'", $yyyymm);
        if (getResult($query_chk,$res_chk) <= 0) {  // 既登録済みのチェック
            // 新規登録
            $query = sprintf("insert into act_pl_history (pl_bs_ym, kin, note) values (%d, %d, 'バイモル営業外費用その他再計算')", $yyyymm, $l_other_lb);
            if (query_affected_trans($con, $query) <= 0) {
                $_SESSION['s_sysmsg'] .= sprintf("バイモル営業外費用その他再計算の登録に失敗<br>第 %d期 %d月",$ki,$tuki);
                query_affected_trans($con, "rollback");     // Transaction Rollback
                header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
                exit();
            }
        } else {
            $query = sprintf("update act_pl_history set kin=%d where pl_bs_ym=%d and note='バイモル営業外費用その他再計算'", $l_other_lb, $yyyymm);
            if (query_affected_trans($con, $query) <= 0) {
                $_SESSION['s_sysmsg'] .= sprintf("バイモル営業外費用その他再計算の更新失敗<br>第 %d期 %d月",$ki,$tuki);
                query_affected_trans($con, "rollback");     // Transaction Rollback
                header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
                exit();
            }
        }
        $query_chk = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='リニア標準営業外費用その他再計算'", $yyyymm);
        if (getResult($query_chk,$res_chk) <= 0) {  // 既登録済みのチェック
            // 新規登録
            $query = sprintf("insert into act_pl_history (pl_bs_ym, kin, note) values (%d, %d, 'リニア標準営業外費用その他再計算')", $yyyymm, $l_other_lh);
            if (query_affected_trans($con, $query) <= 0) {
                $_SESSION['s_sysmsg'] .= sprintf("リニア標準営業外費用その他再計算の登録に失敗<br>第 %d期 %d月",$ki,$tuki);
                query_affected_trans($con, "rollback");     // Transaction Rollback
                header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
                exit();
            }
        } else {
            $query = sprintf("update act_pl_history set kin=%d where pl_bs_ym=%d and note='リニア標準営業外費用その他再計算'", $l_other_lh, $yyyymm);
            if (query_affected_trans($con, $query) <= 0) {
                $_SESSION['s_sysmsg'] .= sprintf("リニア標準営業外費用その他再計算の更新失敗<br>第 %d期 %d月",$ki,$tuki);
                query_affected_trans($con, "rollback");     // Transaction Rollback
                header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
                exit();
            }
        }
        $query_chk = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='カプラ試修営業外費用その他再計算'", $yyyymm);
        if (getResult($query_chk,$res_chk) <= 0) {  // 既登録済みのチェック
            // 新規登録
            $query = sprintf("insert into act_pl_history (pl_bs_ym, kin, note) values (%d, %d, 'カプラ試修営業外費用その他再計算')", $yyyymm, $l_other_sc);
            if (query_affected_trans($con, $query) <= 0) {
                $_SESSION['s_sysmsg'] .= sprintf("カプラ試修営業外費用その他再計算の登録に失敗<br>第 %d期 %d月",$ki,$tuki);
                query_affected_trans($con, "rollback");     // Transaction Rollback
                header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
                exit();
            }
        } else {
            $query = sprintf("update act_pl_history set kin=%d where pl_bs_ym=%d and note='カプラ試修営業外費用その他再計算'", $l_other_sc, $yyyymm);
            if (query_affected_trans($con, $query) <= 0) {
                $_SESSION['s_sysmsg'] .= sprintf("カプラ試修営業外費用その他再計算の更新失敗<br>第 %d期 %d月",$ki,$tuki);
                query_affected_trans($con, "rollback");     // Transaction Rollback
                header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
                exit();
            }
        }
        $query_chk = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='リニア試修営業外費用その他再計算'", $yyyymm);
        if (getResult($query_chk,$res_chk) <= 0) {  // 既登録済みのチェック
            // 新規登録
            $query = sprintf("insert into act_pl_history (pl_bs_ym, kin, note) values (%d, %d, 'リニア試修営業外費用その他再計算')", $yyyymm, $l_other_sl);
            if (query_affected_trans($con, $query) <= 0) {
                $_SESSION['s_sysmsg'] .= sprintf("リニア試修営業外費用その他再計算の登録に失敗<br>第 %d期 %d月",$ki,$tuki);
                query_affected_trans($con, "rollback");     // Transaction Rollback
                header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
                exit();
            }
        } else {
            $query = sprintf("update act_pl_history set kin=%d where pl_bs_ym=%d and note='リニア試修営業外費用その他再計算'", $l_other_sl, $yyyymm);
            if (query_affected_trans($con, $query) <= 0) {
                $_SESSION['s_sysmsg'] .= sprintf("リニア試修営業外費用その他再計算の更新失敗<br>第 %d期 %d月",$ki,$tuki);
                query_affected_trans($con, "rollback");     // Transaction Rollback
                header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
                exit();
            }
        }
        $query_chk = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='修理営業外費用その他再計算'", $yyyymm);
        if (getResult($query_chk,$res_chk) <= 0) {  // 既登録済みのチェック
            // 新規登録
            $query = sprintf("insert into act_pl_history (pl_bs_ym, kin, note) values (%d, %d, '修理営業外費用その他再計算')", $yyyymm, $l_other_ss);
            if (query_affected_trans($con, $query) <= 0) {
                $_SESSION['s_sysmsg'] .= sprintf("修理営業外費用その他再計算の登録に失敗<br>第 %d期 %d月",$ki,$tuki);
                query_affected_trans($con, "rollback");     // Transaction Rollback
                header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
                exit();
            }
        } else {
            $query = sprintf("update act_pl_history set kin=%d where pl_bs_ym=%d and note='修理営業外費用その他再計算'", $l_other_ss, $yyyymm);
            if (query_affected_trans($con, $query) <= 0) {
                $_SESSION['s_sysmsg'] .= sprintf("修理営業外費用その他再計算の更新失敗<br>第 %d期 %d月",$ki,$tuki);
                query_affected_trans($con, "rollback");     // Transaction Rollback
                header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
                exit();
            }
        }
        $query_chk = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='耐久営業外費用その他再計算'", $yyyymm);
        if (getResult($query_chk,$res_chk) <= 0) {  // 既登録済みのチェック
            // 新規登録
            $query = sprintf("insert into act_pl_history (pl_bs_ym, kin, note) values (%d, %d, '耐久営業外費用その他再計算')", $yyyymm, $l_other_st);
            if (query_affected_trans($con, $query) <= 0) {
                $_SESSION['s_sysmsg'] .= sprintf("耐久営業外費用その他再計算の登録に失敗<br>第 %d期 %d月",$ki,$tuki);
                query_affected_trans($con, "rollback");     // Transaction Rollback
                header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
                exit();
            }
        } else {
            $query = sprintf("update act_pl_history set kin=%d where pl_bs_ym=%d and note='耐久営業外費用その他再計算'", $l_other_st, $yyyymm);
            if (query_affected_trans($con, $query) <= 0) {
                $_SESSION['s_sysmsg'] .= sprintf("耐久営業外費用その他再計算の更新失敗<br>第 %d期 %d月",$ki,$tuki);
                query_affected_trans($con, "rollback");     // Transaction Rollback
                header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
                exit();
            }
        }
        
        /***** 営業外費用計の取得 *****/
        //$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='全体営業外費用計'", $yyyymm);
        //getUniResult($query,$nonope_l_sum);
        //$nonope_l_sum       = $nonope_l_sum;
        // 営業外費用計の計算
        $nonope_l_sum_c     = $risoku_c + $l_other_c;         // カプラ営業外費用計
        $nonope_l_sum_l     = $risoku_l + $l_other_l;         // リニア営業外費用計
        $nonope_l_sum_b     = $risoku_b + $l_other_b;         // 商管営業外費用計
        $nonope_l_sum_s     = $risoku_s + $l_other_s;         // 試修営業外費用計
        
        $nonope_l_sum_ctoku = $risoku_ctoku + $l_other_ctoku; // カプラ特注営業外費用計
        $nonope_l_sum_chyou = $risoku_chyou + $l_other_chyou; // カプラ標準営業外費用計
        $nonope_l_sum_lb    = $risoku_lb + $l_other_lb;       // バイモル営業外費用計
        $nonope_l_sum_lh    = $risoku_lh + $l_other_lh;       // リニア標準営業外費用計
        $nonope_l_sum_sc    = $risoku_sc + $l_other_sc;       // カプラ試修営業外費用計
        $nonope_l_sum_sl    = $risoku_sl + $l_other_sl;       // リニア試修営業外費用計
        
        // 修理・耐久計算
        $nonope_l_sum_ss    = $risoku_ss + $l_other_ss;       // 修理営業外費用計
        $nonope_l_sum_st    = $risoku_st + $l_other_st;       // 耐久営業外費用計
        
        // 営業外費用計の登録
        $query_chk = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='カプラ営業外費用計再計算'", $yyyymm);
        if (getResult($query_chk,$res_chk) <= 0) {  // 既登録済みのチェック
            // 新規登録
            $query = sprintf("insert into act_pl_history (pl_bs_ym, kin, note) values (%d, %d, 'カプラ営業外費用計再計算')", $yyyymm, $nonope_l_sum_c);
            if (query_affected_trans($con, $query) <= 0) {
                $_SESSION['s_sysmsg'] .= sprintf("カプラ営業外費用計再計算の登録に失敗<br>第 %d期 %d月",$ki,$tuki);
                query_affected_trans($con, "rollback");     // Transaction Rollback
                header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
                exit();
            }
        } else {
            $query = sprintf("update act_pl_history set kin=%d where pl_bs_ym=%d and note='カプラ営業外費用計再計算'", $nonope_l_sum_c, $yyyymm);
            if (query_affected_trans($con, $query) <= 0) {
                $_SESSION['s_sysmsg'] .= sprintf("カプラ営業外費用計再計算の更新失敗<br>第 %d期 %d月",$ki,$tuki);
                query_affected_trans($con, "rollback");     // Transaction Rollback
                header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
                exit();
            }
        }
        $query_chk = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='リニア営業外費用計再計算'", $yyyymm);
        if (getResult($query_chk,$res_chk) <= 0) {  // 既登録済みのチェック
            // 新規登録
            $query = sprintf("insert into act_pl_history (pl_bs_ym, kin, note) values (%d, %d, 'リニア営業外費用計再計算')", $yyyymm, $nonope_l_sum_l);
            if (query_affected_trans($con, $query) <= 0) {
                $_SESSION['s_sysmsg'] .= sprintf("リニア営業外費用計再計算の登録に失敗<br>第 %d期 %d月",$ki,$tuki);
                query_affected_trans($con, "rollback");     // Transaction Rollback
                header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
                exit();
            }
        } else {
            $query = sprintf("update act_pl_history set kin=%d where pl_bs_ym=%d and note='リニア営業外費用計再計算'", $nonope_l_sum_l, $yyyymm);
            if (query_affected_trans($con, $query) <= 0) {
                $_SESSION['s_sysmsg'] .= sprintf("リニア営業外費用計再計算の更新失敗<br>第 %d期 %d月",$ki,$tuki);
                query_affected_trans($con, "rollback");     // Transaction Rollback
                header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
                exit();
            }
        }
        // 商管を加味する場合
        $query_chk = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='商管営業外費用計再計算'", $yyyymm);
        if (getResult($query_chk,$res_chk) <= 0) {  // 既登録済みのチェック
            // 新規登録
            $query = sprintf("insert into act_pl_history (pl_bs_ym, kin, note) values (%d, %d, '商管営業外費用計再計算')", $yyyymm, $nonope_l_sum_b);
            if (query_affected_trans($con, $query) <= 0) {
                $_SESSION['s_sysmsg'] .= sprintf("商管営業外費用計再計算の登録に失敗<br>第 %d期 %d月",$ki,$tuki);
                query_affected_trans($con, "rollback");     // Transaction Rollback
                header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
                exit();
            }
        } else {
            $query = sprintf("update act_pl_history set kin=%d where pl_bs_ym=%d and note='商管営業外費用計再計算'", $nonope_l_sum_b, $yyyymm);
            if (query_affected_trans($con, $query) <= 0) {
                $_SESSION['s_sysmsg'] .= sprintf("商管営業外費用計再計算の更新失敗<br>第 %d期 %d月",$ki,$tuki);
                query_affected_trans($con, "rollback");     // Transaction Rollback
                header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
                exit();
            }
        }
        
        $query_chk = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='試修営業外費用計再計算'", $yyyymm);
        if (getResult($query_chk,$res_chk) <= 0) {  // 既登録済みのチェック
            // 新規登録
            $query = sprintf("insert into act_pl_history (pl_bs_ym, kin, note) values (%d, %d, '試修営業外費用計再計算')", $yyyymm, $nonope_l_sum_s);
            if (query_affected_trans($con, $query) <= 0) {
                $_SESSION['s_sysmsg'] .= sprintf("試修営業外費用計再計算の登録に失敗<br>第 %d期 %d月",$ki,$tuki);
                query_affected_trans($con, "rollback");     // Transaction Rollback
                header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
                exit();
            }
        } else {
            $query = sprintf("update act_pl_history set kin=%d where pl_bs_ym=%d and note='試修営業外費用計再計算'", $nonope_l_sum_s, $yyyymm);
            if (query_affected_trans($con, $query) <= 0) {
                $_SESSION['s_sysmsg'] .= sprintf("試修営業外費用計再計算の更新失敗<br>第 %d期 %d月",$ki,$tuki);
                query_affected_trans($con, "rollback");     // Transaction Rollback
                header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
                exit();
            }
        }
        $query_chk = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='カプラ特注営業外費用計再計算'", $yyyymm);
        if (getResult($query_chk,$res_chk) <= 0) {  // 既登録済みのチェック
            // 新規登録
            $query = sprintf("insert into act_pl_history (pl_bs_ym, kin, note) values (%d, %d, 'カプラ特注営業外費用計再計算')", $yyyymm, $nonope_l_sum_ctoku);
            if (query_affected_trans($con, $query) <= 0) {
                $_SESSION['s_sysmsg'] .= sprintf("カプラ特注営業外費用計再計算の登録に失敗<br>第 %d期 %d月",$ki,$tuki);
                query_affected_trans($con, "rollback");     // Transaction Rollback
                header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
                exit();
            }
        } else {
            $query = sprintf("update act_pl_history set kin=%d where pl_bs_ym=%d and note='カプラ特注営業外費用計再計算'", $nonope_l_sum_ctoku, $yyyymm);
            if (query_affected_trans($con, $query) <= 0) {
                $_SESSION['s_sysmsg'] .= sprintf("カプラ特注営業外費用計再計算の更新失敗<br>第 %d期 %d月",$ki,$tuki);
                query_affected_trans($con, "rollback");     // Transaction Rollback
                header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
                exit();
            }
        }
        $query_chk = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='カプラ標準営業外費用計再計算'", $yyyymm);
        if (getResult($query_chk,$res_chk) <= 0) {  // 既登録済みのチェック
            // 新規登録
            $query = sprintf("insert into act_pl_history (pl_bs_ym, kin, note) values (%d, %d, 'カプラ標準営業外費用計再計算')", $yyyymm, $nonope_l_sum_chyou);
            if (query_affected_trans($con, $query) <= 0) {
                $_SESSION['s_sysmsg'] .= sprintf("カプラ標準営業外費用計再計算の登録に失敗<br>第 %d期 %d月",$ki,$tuki);
                query_affected_trans($con, "rollback");     // Transaction Rollback
                header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
                exit();
            }
        } else {
            $query = sprintf("update act_pl_history set kin=%d where pl_bs_ym=%d and note='カプラ標準営業外費用計再計算'", $nonope_l_sum_chyou, $yyyymm);
            if (query_affected_trans($con, $query) <= 0) {
                $_SESSION['s_sysmsg'] .= sprintf("カプラ標準営業外費用計再計算の更新失敗<br>第 %d期 %d月",$ki,$tuki);
                query_affected_trans($con, "rollback");     // Transaction Rollback
                header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
                exit();
            }
        }
        $query_chk = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='バイモル営業外費用計再計算'", $yyyymm);
        if (getResult($query_chk,$res_chk) <= 0) {  // 既登録済みのチェック
            // 新規登録
            $query = sprintf("insert into act_pl_history (pl_bs_ym, kin, note) values (%d, %d, 'バイモル営業外費用計再計算')", $yyyymm, $nonope_l_sum_lb);
            if (query_affected_trans($con, $query) <= 0) {
                $_SESSION['s_sysmsg'] .= sprintf("バイモル営業外費用計再計算の登録に失敗<br>第 %d期 %d月",$ki,$tuki);
                query_affected_trans($con, "rollback");     // Transaction Rollback
                header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
                exit();
            }
        } else {
            $query = sprintf("update act_pl_history set kin=%d where pl_bs_ym=%d and note='バイモル営業外費用計再計算'", $nonope_l_sum_lb, $yyyymm);
            if (query_affected_trans($con, $query) <= 0) {
                $_SESSION['s_sysmsg'] .= sprintf("バイモル営業外費用計再計算の更新失敗<br>第 %d期 %d月",$ki,$tuki);
                query_affected_trans($con, "rollback");     // Transaction Rollback
                header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
                exit();
            }
        }
        $query_chk = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='リニア標準営業外費用計再計算'", $yyyymm);
        if (getResult($query_chk,$res_chk) <= 0) {  // 既登録済みのチェック
            // 新規登録
            $query = sprintf("insert into act_pl_history (pl_bs_ym, kin, note) values (%d, %d, 'リニア標準営業外費用計再計算')", $yyyymm, $nonope_l_sum_lh);
            if (query_affected_trans($con, $query) <= 0) {
                $_SESSION['s_sysmsg'] .= sprintf("リニア標準営業外費用計再計算の登録に失敗<br>第 %d期 %d月",$ki,$tuki);
                query_affected_trans($con, "rollback");     // Transaction Rollback
                header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
                exit();
            }
        } else {
            $query = sprintf("update act_pl_history set kin=%d where pl_bs_ym=%d and note='リニア標準営業外費用計再計算'", $nonope_l_sum_lh, $yyyymm);
            if (query_affected_trans($con, $query) <= 0) {
                $_SESSION['s_sysmsg'] .= sprintf("リニア標準営業外費用計再計算の更新失敗<br>第 %d期 %d月",$ki,$tuki);
                query_affected_trans($con, "rollback");     // Transaction Rollback
                header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
                exit();
            }
        }
        $query_chk = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='カプラ試修営業外費用計再計算'", $yyyymm);
        if (getResult($query_chk,$res_chk) <= 0) {  // 既登録済みのチェック
            // 新規登録
            $query = sprintf("insert into act_pl_history (pl_bs_ym, kin, note) values (%d, %d, 'カプラ試修営業外費用計再計算')", $yyyymm, $nonope_l_sum_sc);
            if (query_affected_trans($con, $query) <= 0) {
                $_SESSION['s_sysmsg'] .= sprintf("カプラ試修営業外費用計再計算の登録に失敗<br>第 %d期 %d月",$ki,$tuki);
                query_affected_trans($con, "rollback");     // Transaction Rollback
                header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
                exit();
            }
        } else {
            $query = sprintf("update act_pl_history set kin=%d where pl_bs_ym=%d and note='カプラ試修営業外費用計再計算'", $nonope_l_sum_sc, $yyyymm);
            if (query_affected_trans($con, $query) <= 0) {
                $_SESSION['s_sysmsg'] .= sprintf("カプラ試修営業外費用計再計算の更新失敗<br>第 %d期 %d月",$ki,$tuki);
                query_affected_trans($con, "rollback");     // Transaction Rollback
                header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
                exit();
            }
        }
        $query_chk = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='リニア試修営業外費用計再計算'", $yyyymm);
        if (getResult($query_chk,$res_chk) <= 0) {  // 既登録済みのチェック
            // 新規登録
            $query = sprintf("insert into act_pl_history (pl_bs_ym, kin, note) values (%d, %d, 'リニア試修営業外費用計再計算')", $yyyymm, $nonope_l_sum_sl);
            if (query_affected_trans($con, $query) <= 0) {
                $_SESSION['s_sysmsg'] .= sprintf("リニア試修営業外費用計再計算の登録に失敗<br>第 %d期 %d月",$ki,$tuki);
                query_affected_trans($con, "rollback");     // Transaction Rollback
                header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
                exit();
            }
        } else {
            $query = sprintf("update act_pl_history set kin=%d where pl_bs_ym=%d and note='リニア試修営業外費用計再計算'", $nonope_l_sum_sl, $yyyymm);
            if (query_affected_trans($con, $query) <= 0) {
                $_SESSION['s_sysmsg'] .= sprintf("リニア試修営業外費用計再計算の更新失敗<br>第 %d期 %d月",$ki,$tuki);
                query_affected_trans($con, "rollback");     // Transaction Rollback
                header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
                exit();
            }
        }
        $query_chk = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='修理営業外費用計再計算'", $yyyymm);
        if (getResult($query_chk,$res_chk) <= 0) {  // 既登録済みのチェック
            // 新規登録
            $query = sprintf("insert into act_pl_history (pl_bs_ym, kin, note) values (%d, %d, '修理営業外費用計再計算')", $yyyymm, $nonope_l_sum_ss);
            if (query_affected_trans($con, $query) <= 0) {
                $_SESSION['s_sysmsg'] .= sprintf("修理営業外費用計再計算の登録に失敗<br>第 %d期 %d月",$ki,$tuki);
                query_affected_trans($con, "rollback");     // Transaction Rollback
                header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
                exit();
            }
        } else {
            $query = sprintf("update act_pl_history set kin=%d where pl_bs_ym=%d and note='修理営業外費用計再計算'", $nonope_l_sum_ss, $yyyymm);
            if (query_affected_trans($con, $query) <= 0) {
                $_SESSION['s_sysmsg'] .= sprintf("修理営業外費用計再計算の更新失敗<br>第 %d期 %d月",$ki,$tuki);
                query_affected_trans($con, "rollback");     // Transaction Rollback
                header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
                exit();
            }
        }
        $query_chk = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='耐久営業外費用計再計算'", $yyyymm);
        if (getResult($query_chk,$res_chk) <= 0) {  // 既登録済みのチェック
            // 新規登録
            $query = sprintf("insert into act_pl_history (pl_bs_ym, kin, note) values (%d, %d, '耐久営業外費用計再計算')", $yyyymm, $nonope_l_sum_st);
            if (query_affected_trans($con, $query) <= 0) {
                $_SESSION['s_sysmsg'] .= sprintf("耐久営業外費用計再計算の登録に失敗<br>第 %d期 %d月",$ki,$tuki);
                query_affected_trans($con, "rollback");     // Transaction Rollback
                header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
                exit();
            }
        } else {
            $query = sprintf("update act_pl_history set kin=%d where pl_bs_ym=%d and note='耐久営業外費用計再計算'", $nonope_l_sum_st, $yyyymm);
            if (query_affected_trans($con, $query) <= 0) {
                $_SESSION['s_sysmsg'] .= sprintf("耐久営業外費用計再計算の更新失敗<br>第 %d期 %d月",$ki,$tuki);
                query_affected_trans($con, "rollback");     // Transaction Rollback
                header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
                exit();
            }
        }
        
        // 最後に営業外収益が変更されたので経常利益を再計算する
        // 各営業利益の取得
        $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='カプラ営業利益'", $yyyymm);
        if (getUniResult($query, $c_ope_profit) < 1) {
            $c_ope_profit = 0;                          // 検索失敗
        }
        $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='リニア営業利益'", $yyyymm);
        if (getUniResult($query, $l_ope_profit) < 1) {
            $l_ope_profit = 0;                          // 検索失敗
        }
        // 経常利益再計算
        $c_kei = $c_ope_profit + $nonope_p_sum_c - $nonope_l_sum_c;     // カプラ経常利益再計算
        $l_kei = $l_ope_profit + $nonope_p_sum_l - $nonope_l_sum_l;     // リニア経常利益再計算
        
        // 再計算した経常利益を登録
        $query_chk = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='カプラ経常利益再計算'", $yyyymm);
        if (getResult($query_chk,$res_chk) <= 0) {  // 既登録済みのチェック
            // 新規登録
            $query = sprintf("insert into act_pl_history (pl_bs_ym, kin, note) values (%d, %d, 'カプラ経常利益再計算')", $yyyymm, $c_kei);
            if (query_affected_trans($con, $query) <= 0) {
                $_SESSION['s_sysmsg'] .= sprintf("カプラ経常利益再計算の登録に失敗<br>第 %d期 %d月",$ki,$tuki);
                query_affected_trans($con, "rollback");     // Transaction Rollback
                header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
                exit();
            }
        } else {
            $query = sprintf("update act_pl_history set kin=%d where pl_bs_ym=%d and note='カプラ経常利益再計算'", $c_kei, $yyyymm);
            if (query_affected_trans($con, $query) <= 0) {
                $_SESSION['s_sysmsg'] .= sprintf("カプラ経常利益再計算の更新失敗<br>第 %d期 %d月",$ki,$tuki);
                query_affected_trans($con, "rollback");     // Transaction Rollback
                header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
                exit();
            }
        }
        $query_chk = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='リニア経常利益再計算'", $yyyymm);
        if (getResult($query_chk,$res_chk) <= 0) {  // 既登録済みのチェック
            // 新規登録
            $query = sprintf("insert into act_pl_history (pl_bs_ym, kin, note) values (%d, %d, 'リニア経常利益再計算')", $yyyymm, $l_kei);
            if (query_affected_trans($con, $query) <= 0) {
                $_SESSION['s_sysmsg'] .= sprintf("リニア経常利益再計算の登録に失敗<br>第 %d期 %d月",$ki,$tuki);
                query_affected_trans($con, "rollback");     // Transaction Rollback
                header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
                exit();
            }
        } else {
            $query = sprintf("update act_pl_history set kin=%d where pl_bs_ym=%d and note='リニア経常利益再計算'", $l_kei, $yyyymm);
            if (query_affected_trans($con, $query) <= 0) {
                $_SESSION['s_sysmsg'] .= sprintf("リニア経常利益再計算の更新失敗<br>第 %d期 %d月",$ki,$tuki);
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
<meta http-equiv="Content-Type" content="text/html; charset=EUC-JP">
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
.rightbo{
    text-align:right;
    font:bold 12pt;
    font-family: monospace;
    background-color: '#ffcc99';
}
.rightbg{
    text-align:right;
    font:bold 12pt;
    font-family: monospace;
    background-color: '#ffffcc';
}
.rightbgr{
    text-align:right;
    font:bold 12pt;
    font-family: monospace;
    background-color: '#d6d3ce';
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
        <form name='jin' action='<?php echo $menu->out_self() ?>' method='post'>
        <table bgcolor='#d6d3ce' align='center' cellspacing='0' cellpadding='3' border='1'>
            <tr><td> <!-- ダミー(デザイン用) -->
            <table bgcolor='#d6d3ce' cellspacing='0' cellpadding='2' border='1'>
                <th colspan='2' rowspan='2' bgcolor='#ccffcc'>事業部</th>
                <th bgcolor='#d6d3ce' colspan='2'><?php echo $p1_ym ?></th>
                <th bgcolor='#ccffcc' colspan='2'><?php echo $yyyymm ?></th>
                <tr>
                    <th bgcolor='#d6d3ce'>人員</th>
                    <th bgcolor='#d6d3ce'>人員比</th>
                    <th bgcolor='#ccffcc'>人員</th>
                    <th bgcolor='#ccffcc'>人員比</th>
                </tr>
                <tr>
                    <td align='center' bgcolor='#ffffcc' class='pt11b'>
                    カプラ
                    </td>
                    <td align='center' bgcolor='#ffffcc' class='pt11b'>　</td>
                    <td align='center' class='rightbgr'>
                        <?php echo $jin_b[0] ?>
                    </td>
                    <td align='center' class='rightbgr'>
                        <?php echo $allo_b[0] ?>
                    </td>
                    <td align='center' class='rightby'>
                        <input type='hidden' name='jin[]' value='<?php echo $jin[0] ?>'>
                        <?php echo $jin[0] ?>
                    </td>
                    <td align='center' class='rightby'>
                        <input type='hidden' name='allo[]' value='<?php echo $allo[0] ?>'>
                        <?php echo $allo[0] ?>
                    </td>
                </tr>
                <tr>
                    <td align='center' bgcolor='#ffffcc' class='pt11b'>　</td>
                    <td align='center' bgcolor='#ffffcc' class='pt11b'>
                    カプラ特注
                    </td>
                    <td align='center' class='rightbgr'>
                        <?php echo $jin_b[1] ?>
                    </td>
                    <td align='center' class='rightbgr'>
                        <?php echo $allo_b[1] ?>
                    </td>
                    <td align='center' class='rightby'>
                        <input type='text' name='jin[]' size='11' maxlength='11' value='<?php echo $jin[1] ?>' class='right' onChange='return isDigit(value);'>
                    </td>
                    <td align='center' class='rightby'>
                        <input type='hidden' name='allo[]' value='<?php echo $allo[1] ?>'>
                        <?php echo $allo[1] ?>
                    </td>
                </tr>
                <tr>
                    <td align='center' bgcolor='#ffffcc' class='pt11b'>　</td>
                    <td align='center' bgcolor='#ffffcc' class='pt11b'>
                    カプラ標準
                    </td>
                    <td align='center' class='rightbgr'>
                        <?php echo $jin_b[2] ?>
                    </td>
                    <td align='center' class='rightbgr'>
                        <?php echo $allo_b[2] ?>
                    </td>
                    <td align='center' class='rightby'>
                        <input type='text' name='jin[]' size='11' maxlength='11' value='<?php echo $jin[2] ?>' class='right' onChange='return isDigit(value);'>
                    </td>
                    <td align='center' class='rightby'>
                        <input type='hidden' name='allo[]' value='<?php echo $allo[2] ?>'>
                        <?php echo $allo[2] ?>
                    </td>
                </tr>
                <tr>
                    <td align='center' bgcolor='#ccffff' class='pt11b'>
                    リニア
                    </td>
                    <td align='center' bgcolor='#ccffff' class='pt11b'>　</td>
                    <td align='center' class='rightbgr'>
                        <?php echo $jin_b[3] ?>
                    </td>
                    <td align='center' class='rightbgr'>
                        <?php echo $allo_b[3] ?>
                    </td>
                    <td align='center' class='rightbb'>
                        <input type='hidden' name='jin[]' value='<?php echo $jin[3] ?>'>
                        <?php echo $jin[3] ?>
                    </td>
                    <td align='center' class='rightbb'>
                        <input type='hidden' name='allo[]' value='<?php echo $allo[3] ?>'>
                        <?php echo $allo[3] ?>
                    </td>
                </tr>
                <tr>
                    <td align='center' bgcolor='#ccffff' class='pt11b'>　</td>
                    <td align='center' bgcolor='#ccffff' class='pt11b'>
                    液体ポンプ
                    </td>
                    <td align='center' class='rightbgr'>
                        <?php echo $jin_b[4] ?>
                    </td>
                    <td align='center' class='rightbgr'>
                        <?php echo $allo_b[4] ?>
                    </td>
                    <td align='center' class='rightbb'>
                        <input type='text' name='jin[]' size='11' maxlength='11' value='<?php echo $jin[4] ?>' class='right' onChange='return isDigit(value);'>
                    </td>
                    <td align='center' class='rightbb'>
                        <input type='hidden' name='allo[]' value='<?php echo $allo[4] ?>'>
                        <?php echo $allo[4] ?>
                    </td>
                </tr>
                <tr>
                    <td align='center' bgcolor='#ccffff' class='pt11b'>　</td>
                    <td align='center' bgcolor='#ccffff' class='pt11b'>
                    リニア標準
                    </td>
                    <td align='center' class='rightbgr'>
                        <?php echo $jin_b[5] ?>
                    </td>
                    <td align='center' class='rightbgr'>
                        <?php echo $allo_b[5] ?>
                    </td>
                    <td align='center' class='rightbb'>
                        <input type='text' name='jin[]' size='11' maxlength='11' value='<?php echo $jin[5] ?>' class='right' onChange='return isDigit(value);'>
                    </td>
                    <td align='center' class='rightbb'>
                        <input type='hidden' name='allo[]' value='<?php echo $allo[5] ?>'>
                        <?php echo $allo[5] ?>
                    </td>
                </tr>
                <tr>
                    <td align='center' bgcolor='#ffcc99' class='pt11b'>
                    試験修理
                    </td>
                    <td align='center' bgcolor='#ffcc99' class='pt11b'>　</td>
                    <td align='center' class='rightbgr'>
                        <?php echo $jin_b[6] ?>
                    </td>
                    <td align='center' class='rightbgr'>
                        <?php echo $allo_b[6] ?>
                    </td>
                    <td align='center' class='rightbo'>
                        <input type='hidden' name='jin[]' value='<?php echo $jin[6] ?>'>
                        <?php echo $jin[6] ?>
                    </td>
                    <td align='center' class='rightbo'>
                        <input type='hidden' name='allo[]' value='<?php echo $allo[6] ?>'>
                        <?php echo $allo[6] ?>
                    </td>
                </tr>
                <tr>
                    <td align='center' bgcolor='#ffcc99' class='pt11b'>　</td>
                    <td align='center' bgcolor='#ffcc99' class='pt11b'>
                    カプラ試修
                    </td>
                    <td align='center' class='rightbgr'>
                        <?php echo $jin_b[7] ?>
                    </td>
                    <td align='center' class='rightbgr'>
                        <?php echo $allo_b[7] ?>
                    </td>
                    <td align='center' class='rightbo'>
                        <input type='text' name='jin[]' size='11' maxlength='11' value='<?php echo $jin[7] ?>' class='right' onChange='return isDigit(value);'>
                    </td>
                    <td align='center' class='rightbo'>
                        <input type='hidden' name='allo[]' value='<?php echo $allo[7] ?>'>
                        <?php echo $allo[7] ?>
                    </td>
                </tr>
                <tr>
                    <td align='center' bgcolor='#ffcc99' class='pt11b'>　</td>
                    <td align='center' bgcolor='#ffcc99' class='pt11b'>
                    リニア試修
                    </td>
                    <td align='center' class='rightbgr'>
                        <?php echo $jin_b[8] ?>
                    </td>
                    <td align='center' class='rightbgr'>
                        <?php echo $allo_b[8] ?>
                    </td>
                    <td align='center' class='rightbo'>
                        <input type='text' name='jin[]' size='11' maxlength='11' value='<?php echo $jin[8] ?>' class='right' onChange='return isDigit(value);'>
                    </td>
                    <td align='center' class='rightbo'>
                        <input type='hidden' name='allo[]' value='<?php echo $allo[8] ?>'>
                        <?php echo $allo[8] ?>
                    </td>
                </tr>
                <tr>
                    <td align='center' bgcolor='#ccffcc' class='pt11b'>
                    商品管理
                    </td>
                    <td align='center' bgcolor='#ccffcc' class='pt11b'>　</td>
                    <td align='center' class='rightbgr'>
                        <?php echo $jin_b[9] ?>
                    </td>
                    <td align='center' class='rightbgr'>
                        <?php echo $allo_b[9] ?>
                    </td>
                    <td align='center' class='rightbg'>
                        <input type='text' name='jin[]' size='11' maxlength='11' value='<?php echo $jin[9] ?>' class='right' onChange='return isDigit(value);'>
                    </td>
                    <td align='center' class='rightbg'>
                        <input type='hidden' name='allo[]' value='<?php echo $allo[9] ?>'>
                        <?php echo $allo[9] ?>
                    </td>
                </tr>
                <tr>
                    <th align='center' bgcolor='#ccffcc' colspan='6' rowspan='1' class='pt11b'>
                        NKIT有償支給
                    </th>
                </tr>
                <tr>
                    <td align='center' bgcolor='#ffffcc' class='pt11b'>
                    カプラ標準
                    </td>
                    <td align='center' bgcolor='#ffffcc' class='pt11b' >為替差益</td>
                    <td align='center' class='rightbg'>
                        <input type='text' name='jin[]' size='11' value='<?php echo $jin[10] ?>' class='right' onChange='return isDigitcho(value);'>
                    </td>
                    <td align='center' bgcolor='#ffffcc' class='pt11b' colspan='2'>為替差損</td>
                    <td align='center' class='rightbg'>
                        <input type='text' name='jin[]' size='11' value='<?php echo $jin[11] ?>' class='right' onChange='return isDigitcho(value);'>
                    </td>
                </tr>
                <tr>
                    <td align='center' bgcolor='#ffffcc' class='pt11b'>
                    カプラ特注
                    </td>
                    <td align='center' bgcolor='#ffffcc' class='pt11b' >為替差益</td>
                    <td align='center' class='rightbg'>
                        <input type='text' name='jin[]' size='11' value='<?php echo $jin[12] ?>' class='right' onChange='return isDigitcho(value);'>
                    </td>
                    <td align='center' bgcolor='#ffffcc' class='pt11b' colspan='2'>為替差損</td>
                    <td align='center' class='rightbg'>
                        <input type='text' name='jin[]' size='11' value='<?php echo $jin[13] ?>' class='right' onChange='return isDigitcho(value);'>
                    </td>
                </tr>
                <tr>
                    <td align='center' bgcolor='#ccffff' class='pt11b'>
                    リニア標準
                    </td>
                    <td align='center' bgcolor='#ccffff' class='pt11b' >為替差益</td>
                    <td align='center' class='rightbb'>
                        <input type='text' name='jin[]' size='11' value='<?php echo $jin[14] ?>' class='right' onChange='return isDigitcho(value);'>
                    </td>
                    <td align='center' bgcolor='#ccffff' class='pt11b' colspan='2'>為替差損</td>
                    <td align='center' class='rightbb'>
                        <input type='text' name='jin[]' size='11' value='<?php echo $jin[15] ?>' class='right' onChange='return isDigitcho(value);'>
                    </td>
                </tr>
                <tr>
                    <td align='center' bgcolor='#ccffff' class='pt11b'>
                    液体ポンプ
                    </td>
                    <td align='center' bgcolor='#ccffff' class='pt11b' >為替差益</td>
                    <td align='center' class='rightbb'>
                        <input type='text' name='jin[]' size='11' value='<?php echo $jin[16] ?>' class='right' onChange='return isDigitcho(value);'>
                    </td>
                    <td align='center' bgcolor='#ccffff' class='pt11b' colspan='2'>為替差損</td>
                    <td align='center' class='rightbb'>
                        <input type='text' name='jin[]' size='11' value='<?php echo $jin[17] ?>' class='right' onChange='return isDigitcho(value);'>
                    </td>
                </tr>
                <tr>
                    <th align='center' bgcolor='#ccffcc' colspan='6' rowspan='1' class='pt11b'>
                        営業外収益その他調整
                    </th>
                </tr>
                <tr>
                    <td align='center' bgcolor='#ffffcc' class='pt11b' colspan='2'>
                    カプラ標準
                    </td>
                    <td align='center' class='rightbg'>
                        <input type='text' name='jin[]' size='11' value='<?php echo $jin[18] ?>' class='right' onChange='return isDigitcho(value);'>
                    </td>
                    <td align='center' bgcolor='#ffffcc' class='pt11b' colspan='2'>カプラ特注</td>
                    <td align='center' class='rightbg'>
                        <input type='text' name='jin[]' size='11' value='<?php echo $jin[19] ?>' class='right' onChange='return isDigitcho(value);'>
                    </td>
                </tr>
                <tr>
                    <td align='center' bgcolor='#ccffff' class='pt11b' colspan='2'>
                    リニア標準
                    </td>
                    <td align='center' class='rightbb'>
                        <input type='text' name='jin[]' size='11' value='<?php echo $jin[20] ?>' class='right' onChange='return isDigitcho(value);'>
                    </td>
                    <td align='center' bgcolor='#ccffff' class='pt11b' colspan='2'>液体ポンプ</td>
                    <td align='center' class='rightbb'>
                        <input type='text' name='jin[]' size='11' value='<?php echo $jin[21] ?>' class='right' onChange='return isDigitcho(value);'>
                    </td>
                </tr>
                <tr>
                    <td align='center' bgcolor='#ffcc99' class='pt11b' colspan='2'>
                    試験・修理
                    </td>
                    <td align='center' class='rightbo'>
                        <input type='text' name='jin[]' size='11' value='<?php echo $jin[22] ?>' class='right' onChange='return isDigitcho(value);'>
                    </td>
                    <td align='center' bgcolor='#ffcc99' class='pt11b' colspan='2'>商品管理</td>
                    <td align='center' class='rightbo'>
                        <input type='text' name='jin[]' size='11' value='<?php echo $jin[23] ?>' class='right' onChange='return isDigitcho(value);'>
                    </td>
                </tr>
                <tr>
                    <th align='center' bgcolor='#ccffcc' colspan='6' rowspan='1' class='pt11b'>
                        営業外費用その他調整
                    </th>
                </tr>
                <tr>
                    <td align='center' bgcolor='#ffffcc' class='pt11b' colspan='2'>
                    カプラ標準
                    </td>
                    <td align='center' class='rightbg'>
                        <input type='text' name='jin[]' size='11' value='<?php echo $jin[24] ?>' class='right' onChange='return isDigitcho(value);'>
                    </td>
                    <td align='center' bgcolor='#ffffcc' class='pt11b' colspan='2'>カプラ特注</td>
                    <td align='center' class='rightbg'>
                        <input type='text' name='jin[]' size='11' value='<?php echo $jin[25] ?>' class='right' onChange='return isDigitcho(value);'>
                    </td>
                </tr>
                <tr>
                    <td align='center' bgcolor='#ccffff' class='pt11b' colspan='2'>
                    リニア標準
                    </td>
                    <td align='center' class='rightbb'>
                        <input type='text' name='jin[]' size='11' value='<?php echo $jin[26] ?>' class='right' onChange='return isDigitcho(value);'>
                    </td>
                    <td align='center' bgcolor='#ccffff' class='pt11b' colspan='2'>液体ポンプ</td>
                    <td align='center' class='rightbb'>
                        <input type='text' name='jin[]' size='11' value='<?php echo $jin[27] ?>' class='right' onChange='return isDigitcho(value);'>
                    </td>
                </tr>
                <tr>
                    <td align='center' bgcolor='#ffcc99' class='pt11b' colspan='2'>
                    試験・修理
                    </td>
                    <td align='center' class='rightbo'>
                        <input type='text' name='jin[]' size='11' value='<?php echo $jin[28] ?>' class='right' onChange='return isDigitcho(value);'>
                    </td>
                    <td align='center' bgcolor='#ffcc99' class='pt11b' colspan='2'>商品管理</td>
                    <td align='center' class='rightbo'>
                        <input type='text' name='jin[]' size='11' value='<?php echo $jin[29] ?>' class='right' onChange='return isDigitcho(value);'>
                    </td>
                </tr>
                <tr>
                    <td colspan='6' align='center'>
                        <input type='submit' name='entry' value='実行' >
                        &nbsp;&nbsp;&nbsp;
                        <input type='submit' name='copy' value='前月データコピー' onClick='return data_copy_click(this)'>
                    </td>
                </tr>
            </table>
            </td></tr>
        </table> <!-- ダミーEnd -->
        </form>
    </center>
</body>
</html>
