<?php
//////////////////////////////////////////////////////////////////////////////
// 月次損益関係 ＣＬ別 経費(間接費・販管費)配賦計算                         //
// Copyright(C) 2003-2016 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp       //
// 変更経歴                                                                 //
// 2003/02/03 新規作成  profit_loss_cl_allocation.php                       //
//            使用テーブル 読込 bm_km_summary (科目別部門経費)              //
//                         書込 act_allo_history (月次配賦率計算経歴)       //
// 2003/02/07 配賦率の少数桁数変更 %1.3f → %1.5f Uround(???.5) へ          //
//                                      Excel と見た目を合わせるため        //
// 2003/02/12 対象データのチェックを select pl_bs_ym → select sum(kin)     //
//              へ変更 合計金額が 0 の場合はエラーとする。                  //
// 2003/03/04 データ更新をトランザクションに変更 (データの保証)             //
// 2004/02/05 sprintfで $allo を %d → %01.5f へ修正 173 174 500 の部分     //
//            (PostgreSQL V7.4.1 PHP V4.3.5RC2 でトラブルあり)              //
// 2004/07/02 面積比をカプラ=83.133% リニア=16.867% へ変更 ６月分より適用   //
// 2009/06/10 技術部：501部門の経費配賦計算を追加                      大谷 //
// 2009/08/07 物流損益追加の為、580部門の製造経費と670部門の販管費を        //
//            強制的にカプラに振分ける暫定対応                         大谷 //
// 2009/08/20 商品別損益及び経費実績表で暫定的にカプラに振分けた経費を      //
//            それぞれの部門に正しく戻し表示するように変更した         大谷 //
// 2012/11/06 500部門のCLの割合を変更 C:30・L:70→C:70・L:30           大谷 //
// 2016/10/04 545部門のCLの割合を追加 C:70・L:30                       大谷 //
// 2016/10/14 2016/10よりすべての割合をC:80・L:20へ変更                大谷 //
//////////////////////////////////////////////////////////////////////////////
ini_set('error_reporting',E_ALL);   // E_ALL='2047' debug 用
// ini_set('display_errors','1');      // Error 表示 ON debug 用 リリース後コメント
session_start();                    // ini_set()の次に指定すること Script 最上行
require_once ("../function.php");
require_once ("../tnk_func.php");
access_log();       // Script Name は自動取得
$_SESSION["site_index"] = 10;       // 月次損益関係=10 最後のメニューは 99 を使用
$_SESSION["site_id"] = 7;           // 下位メニュー無し (0 <=)
// if(!isset($_SESSION["User_ID"]) || !isset($_SESSION["Password"]) || !isset($_SESSION["Auth"])){
if (account_group_check() == FALSE) {
    $_SESSION["s_sysmsg"] = "あなたは許可されていません。<br>管理者に連絡して下さい。";
    header("Location: http:" . WEB_HOST . "menu.php");
    exit();
}
///// 期・月の取得
$ki = Ym_to_tnk($_SESSION['pl_ym']);
$tuki = substr($_SESSION['pl_ym'],4,2);
///// 対象当月
$yyyymm = $_SESSION['pl_ym'];
///// 対象前月
if (substr($yyyymm,4,2)!=01) {
    $p1_ym = $yyyymm - 1;
} else {
    $p1_ym = $yyyymm - 100;
    $p1_ym = $p1_ym + 11;
}
//////////// 173 174 500 501 545 部門の人件費 配賦率
if ($yyyymm > 201609) {
    $allo_173_c = 0.80;     // 173 配賦率カプラ
    $allo_173_l = 0.20;     // 173 配賦率リニア
    $allo_174_c = 0.80;     // 174 配賦率カプラ
    $allo_174_l = 0.20;     // 174 配賦率リニア
    $allo_500_c = 0.80;     // 500 配賦率カプラ
    $allo_500_l = 0.20;     // 500 配賦率リニア
    $allo_501_c = 0.80;     // 501 配賦率カプラ
    $allo_501_l = 0.20;     // 501 配賦率リニア
    $allo_545_c = 0.80;     // 545 配賦率カプラ
    $allo_545_l = 0.20;     // 545 配賦率リニア
} else {
    $allo_173_c = 0.70;     // 173 配賦率カプラ
    $allo_173_l = 0.30;     // 173 配賦率リニア
    $allo_174_c = 0.60;     // 174 配賦率カプラ
    $allo_174_l = 0.40;     // 174 配賦率リニア
    $allo_500_c = 0.70;     // 500 配賦率カプラ
    $allo_500_l = 0.30;     // 500 配賦率リニア
    $allo_501_c = 0.70;     // 501 配賦率カプラ
    $allo_501_l = 0.30;     // 501 配賦率リニア
    $allo_545_c = 0.70;     // 545 配賦率カプラ
    $allo_545_l = 0.30;     // 545 配賦率リニア
}
//////////// 対象データのチェック
$res     = array();     // 取得データの配列
$query   = sprintf("select sum(kin) from bm_km_summary where pl_bs_ym=%d", $yyyymm);
$rows = getResult($query,$res);
if ($res[0][0] == 0) {      ///// データ無しのチェック
    $_SESSION['s_sysmsg'] .= sprintf("部門別経費 対象データなし 第%d期%d月", $ki, $tuki);
    header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
    exit();
}
/////////// begin トランザクション開始
if ($con = db_connect()) {
    query_affected_trans($con, "begin");
} else {
    $_SESSION["s_sysmsg"] .= "データベースに接続できません";
    header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
    exit();
}
/********** 科目別部門経費より読込 **********/
$res     = array();     // 取得データの配列
$act_id  = 173;         // 部門コード 173 の 人件費関係の科目(8101～) 金額取得
$query   = sprintf("select act_id, actcod, k_kubun, div, kin from bm_km_summary where pl_bs_ym=%d and act_id=%d and actcod>=8101 order by actcod ASC", $yyyymm, $act_id);
if (($rows=getResult($query,$res)) > 0) {       ///// データ無しのチェック 優先順位の括弧に注意
    foreach ($res as $res1) {
        /********** 経歴テーブルのデータチェック *************/
        $res_hist = array();
        $dest_id  = 1;          // カプラ
        $query = sprintf("select pl_bs_ym from act_allo_history where pl_bs_ym=%d and actcod=%d and orign_id=%d and dest_id=%d", $yyyymm, $res1['actcod'], $res1['act_id'], $dest_id);
        if ((getResult($query,$res_hist)) <= 0) {     ///// データ無しのチェック 優先順位の括弧に注意
            ////// 配賦率金額計算
            $allo     = $allo_173_c;
            $dest_kin = Uround(($res1['kin'] * $allo), 0);
            /********** 経歴テーブルに書込み ***********/
            $query = sprintf("insert into act_allo_history (pl_bs_ym, actcod, orign_id, orign_kin, k_kubun, div, dest_id, dest_kin, allo) 
                values (%d, %d, %d, %d, '%s', '%s', %d, %d, %01.5f)", $yyyymm, $res1['actcod'], $res1['act_id'], $res1['kin'], $res1['k_kubun'], $res1['div'], $dest_id, $dest_kin, $allo);
            if (query_affected_trans($con, $query) <= 0) {      ///// トランザクション用クエリーの実行
                $_SESSION['s_sysmsg'] .= sprintf("年月=%d:科目=%d:部門=%d:配布先=%d の経歴へ新規登録に失敗しました<br>", $yyyymm, $res1['actcod'], $res1['act_id'], $dest_id);
                query_affected_trans($con, "rollback");     // transaction rollback
                header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
                exit();
            }
        } else {
            $_SESSION['s_sysmsg'] .= sprintf("年月=%d:科目=%d:部門=%d:配布先=%d は配賦済み<br>", $yyyymm, $res1['actcod'], $res1['act_id'], $dest_id);
            query_affected_trans($con, "rollback");     // transaction rollback
            header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
            exit();
        }
        $dest_id  = 2;          // リニア
        $query = sprintf("select pl_bs_ym from act_allo_history where pl_bs_ym=%d and actcod=%d and orign_id=%d and dest_id=%d", $yyyymm, $res1['actcod'], $res1['act_id'], $dest_id);
        if ((getResult($query,$res_hist)) <= 0) {     ///// データ無しのチェック 優先順位の括弧に注意
            ////// 配賦率金額計算
            $allo     = $allo_173_l;
            $dest_kin = $res1['kin'] - $dest_kin;
            /********** 経歴テーブルに書込み ***********/
            $query = sprintf("insert into act_allo_history (pl_bs_ym, actcod, orign_id, orign_kin, k_kubun, div, dest_id, dest_kin, allo) 
                values (%d, %d, %d, %d, '%s', '%s', %d, %d, %01.5f)", $yyyymm, $res1['actcod'], $res1['act_id'], $res1['kin'], $res1['k_kubun'], $res1['div'], $dest_id, $dest_kin, $allo);
            if (query_affected_trans($con, $query) <= 0) {      ///// トランザクション用クエリーの実行
                $_SESSION['s_sysmsg'] .= sprintf("年月=%d:科目=%d:部門=%d:配布先=%d の経歴へ新規登録に失敗しました<br>", $yyyymm, $res1['actcod'], $res1['act_id'], $dest_id);
                query_affected_trans($con, "rollback");     // transaction rollback
                header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
                exit();
            }
        } else {
            $_SESSION['s_sysmsg'] .= sprintf("年月=%d:科目=%d:部門=%d:配布先=%d は配賦済み<br>", $yyyymm, $res1['actcod'], $res1['act_id'], $dest_id);
            query_affected_trans($con, "rollback");     // transaction rollback
            header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
            exit();
        }
    }
} else {
    $_SESSION["s_sysmsg"] .= sprintf("人件費の間接費 対象データなし 部門=%d 第%d期%d月<br>", $act_id, $ki, $tuki);
    query_affected_trans($con, "rollback");     // transaction rollback
    header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
    exit();
}
$act_id  = 174;         // 部門コード 174 の 人件費関係の科目(8101～) 金額取得
$query   = sprintf("select act_id, actcod, k_kubun, div, kin from bm_km_summary where pl_bs_ym=%d and act_id=%d and actcod>=8101 order by actcod ASC", $yyyymm, $act_id);
if (($rows=getResult($query,$res)) > 0) {       ///// データ無しのチェック 優先順位の括弧に注意
    foreach ($res as $res1) {
        /********** 経歴テーブルのデータチェック *************/
        $res_hist = array();
        $dest_id  = 1;          // カプラ
        $query = sprintf("select pl_bs_ym from act_allo_history where pl_bs_ym=%d and actcod=%d and orign_id=%d and dest_id=%d", $yyyymm, $res1['actcod'], $res1['act_id'], $dest_id);
        if ((getResult($query,$res_hist)) <= 0) {     ///// データ無しのチェック 優先順位の括弧に注意
            ////// 配賦率金額計算
            $allo     = $allo_174_c;
            $dest_kin = Uround(($res1['kin'] * $allo), 0);
            /********** 経歴テーブルに書込み ***********/
            $query = sprintf("insert into act_allo_history (pl_bs_ym, actcod, orign_id, orign_kin, k_kubun, div, dest_id, dest_kin, allo) 
                values (%d, %d, %d, %d, '%s', '%s', %d, %d, %01.5f)", $yyyymm, $res1['actcod'], $res1['act_id'], $res1['kin'], $res1['k_kubun'], $res1['div'], $dest_id, $dest_kin, $allo);
            if (query_affected_trans($con, $query) <= 0) {      ///// トランザクション用クエリーの実行
                $_SESSION['s_sysmsg'] .= sprintf("年月=%d:科目=%d:部門=%d:配布先=%d の経歴へ新規登録に失敗しました<br>", $yyyymm, $res1['actcod'], $res1['act_id'], $dest_id);
                query_affected_trans($con, "rollback");     // transaction rollback
                header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
                exit();
            }
        } else {
            $_SESSION['s_sysmsg'] .= sprintf("年月=%d:科目=%d:部門=%d:配布先=%d は配賦済み<br>", $yyyymm, $res1['actcod'], $res1['act_id'], $dest_id);
            query_affected_trans($con, "rollback");     // transaction rollback
            header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
            exit();
        }
        $dest_id  = 2;          // リニア
        $query = sprintf("select pl_bs_ym from act_allo_history where pl_bs_ym=%d and actcod=%d and orign_id=%d and dest_id=%d", $yyyymm, $res1['actcod'], $res1['act_id'], $dest_id);
        if ((getResult($query,$res_hist)) <= 0) {     ///// データ無しのチェック 優先順位の括弧に注意
            ////// 配賦率金額計算
            $allo     = $allo_174_l;
            $dest_kin = $res1['kin'] - $dest_kin;
            /********** 経歴テーブルに書込み ***********/
            $query = sprintf("insert into act_allo_history (pl_bs_ym, actcod, orign_id, orign_kin, k_kubun, div, dest_id, dest_kin, allo) 
                values (%d, %d, %d, %d, '%s', '%s', %d, %d, %01.5f)", $yyyymm, $res1['actcod'], $res1['act_id'], $res1['kin'], $res1['k_kubun'], $res1['div'], $dest_id, $dest_kin, $allo);
            if (query_affected_trans($con, $query) <= 0) {      ///// トランザクション用クエリーの実行
                $_SESSION['s_sysmsg'] .= sprintf("年月=%d:科目=%d:部門=%d:配布先=%d の経歴へ新規登録に失敗しました<br>", $yyyymm, $res1['actcod'], $res1['act_id'], $dest_id);
                query_affected_trans($con, "rollback");     // transaction rollback
                header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
                exit();
            }
        } else {
            $_SESSION['s_sysmsg'] .= sprintf("年月=%d:科目=%d:部門=%d:配布先=%d は配賦済み<br>", $yyyymm, $res1['actcod'], $res1['act_id'], $dest_id);
            query_affected_trans($con, "rollback");     // transaction rollback
            header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
            exit();
        }
    }
} else {
    $_SESSION["s_sysmsg"] .= sprintf("人件費の間接費 対象データなし 部門=%d 第%d期%d月<br>", $act_id, $ki, $tuki);
    query_affected_trans($con, "rollback");     // transaction rollback
    header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
    exit();
}
$act_id  = 500;         // 部門コード 174 の 人件費関係の科目(8101～) 金額取得
$query   = sprintf("select act_id, actcod, k_kubun, div, kin from bm_km_summary where pl_bs_ym=%d and act_id=%d and actcod>=8101 order by actcod ASC", $yyyymm, $act_id);
if (($rows=getResult($query,$res)) > 0) {       ///// データ無しのチェック 優先順位の括弧に注意
    foreach ($res as $res1) {
        /********** 経歴テーブルのデータチェック *************/
        $res_hist = array();
        $dest_id  = 1;          // カプラ
        $query = sprintf("select pl_bs_ym from act_allo_history where pl_bs_ym=%d and actcod=%d and orign_id=%d and dest_id=%d", $yyyymm, $res1['actcod'], $res1['act_id'], $dest_id);
        if ((getResult($query,$res_hist)) <= 0) {     ///// データ無しのチェック 優先順位の括弧に注意
            ////// 配賦率金額計算
            $allo     = $allo_500_c;
            $dest_kin = Uround(($res1['kin'] * $allo), 0);
            /********** 経歴テーブルに書込み ***********/
            $query = sprintf("insert into act_allo_history (pl_bs_ym, actcod, orign_id, orign_kin, k_kubun, div, dest_id, dest_kin, allo) 
                values (%d, %d, %d, %d, '%s', '%s', %d, %d, %01.5f)", $yyyymm, $res1['actcod'], $res1['act_id'], $res1['kin'], $res1['k_kubun'], $res1['div'], $dest_id, $dest_kin, $allo);
            if (query_affected_trans($con, $query) <= 0) {      ///// トランザクション用クエリーの実行
                $_SESSION['s_sysmsg'] .= sprintf("年月=%d:科目=%d:部門=%d:配布先=%d の経歴へ新規登録に失敗しました<br>", $yyyymm, $res1['actcod'], $res1['act_id'], $dest_id);
                query_affected_trans($con, "rollback");     // transaction rollback
                header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
                exit();
            }
        } else {
            $_SESSION['s_sysmsg'] .= sprintf("年月=%d:科目=%d:部門=%d:配布先=%d は配賦済み<br>", $yyyymm, $res1['actcod'], $res1['act_id'], $dest_id);
            query_affected_trans($con, "rollback");     // transaction rollback
            header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
            exit();
        }
        $dest_id  = 2;          // リニア
        $query = sprintf("select pl_bs_ym from act_allo_history where pl_bs_ym=%d and actcod=%d and orign_id=%d and dest_id=%d", $yyyymm, $res1['actcod'], $res1['act_id'], $dest_id);
        if ((getResult($query,$res_hist)) <= 0) {     ///// データ無しのチェック 優先順位の括弧に注意
            ////// 配賦率金額計算
            $allo     = $allo_500_l;
            $dest_kin = $res1['kin'] - $dest_kin;
            /********** 経歴テーブルに書込み ***********/
            $query = sprintf("insert into act_allo_history (pl_bs_ym, actcod, orign_id, orign_kin, k_kubun, div, dest_id, dest_kin, allo) 
                values (%d, %d, %d, %d, '%s', '%s', %d, %d, %01.5f)", $yyyymm, $res1['actcod'], $res1['act_id'], $res1['kin'], $res1['k_kubun'], $res1['div'], $dest_id, $dest_kin, $allo);
            if (query_affected_trans($con, $query) <= 0) {      ///// トランザクション用クエリーの実行
                $_SESSION['s_sysmsg'] .= sprintf("年月=%d:科目=%d:部門=%d:配布先=%d の経歴へ新規登録に失敗しました<br>", $yyyymm, $res1['actcod'], $res1['act_id'], $dest_id);
                query_affected_trans($con, "rollback");     // transaction rollback
                header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
                exit();
            }
        } else {
            $_SESSION['s_sysmsg'] .= sprintf("年月=%d:科目=%d:部門=%d:配布先=%d は配賦済み<br>", $yyyymm, $res1['actcod'], $res1['act_id'], $dest_id);
            query_affected_trans($con, "rollback");     // transaction rollback
            header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
            exit();
        }
    }
} else {
    $_SESSION["s_sysmsg"] .= sprintf("人件費の間接費 対象データなし 部門=%d 第%d期%d月<br>", $act_id, $ki, $tuki);
    query_affected_trans($con, "rollback");     // transaction rollback
    header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
    exit();
}
$act_id  = 501;         // 部門コード 501 の 人件費関係の科目(8101～) 金額取得
$query   = sprintf("select act_id, actcod, k_kubun, div, kin from bm_km_summary where pl_bs_ym=%d and act_id=%d and actcod>=8101 order by actcod ASC", $yyyymm, $act_id);
if (($rows=getResult($query,$res)) > 0) {       ///// データ無しのチェック 優先順位の括弧に注意
    foreach ($res as $res1) {
        /********** 経歴テーブルのデータチェック *************/
        $res_hist = array();
        $dest_id  = 1;          // カプラ
        $query = sprintf("select pl_bs_ym from act_allo_history where pl_bs_ym=%d and actcod=%d and orign_id=%d and dest_id=%d", $yyyymm, $res1['actcod'], $res1['act_id'], $dest_id);
        if ((getResult($query,$res_hist)) <= 0) {     ///// データ無しのチェック 優先順位の括弧に注意
            ////// 配賦率金額計算
            $allo     = $allo_501_c;
            $dest_kin = Uround(($res1['kin'] * $allo), 0);
            /********** 経歴テーブルに書込み ***********/
            $query = sprintf("insert into act_allo_history (pl_bs_ym, actcod, orign_id, orign_kin, k_kubun, div, dest_id, dest_kin, allo) 
                values (%d, %d, %d, %d, '%s', '%s', %d, %d, %01.5f)", $yyyymm, $res1['actcod'], $res1['act_id'], $res1['kin'], $res1['k_kubun'], $res1['div'], $dest_id, $dest_kin, $allo);
            if (query_affected_trans($con, $query) <= 0) {      ///// トランザクション用クエリーの実行
                $_SESSION['s_sysmsg'] .= sprintf("年月=%d:科目=%d:部門=%d:配布先=%d の経歴へ新規登録に失敗しました<br>", $yyyymm, $res1['actcod'], $res1['act_id'], $dest_id);
                query_affected_trans($con, "rollback");     // transaction rollback
                header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
                exit();
            }
        } else {
            $_SESSION['s_sysmsg'] .= sprintf("年月=%d:科目=%d:部門=%d:配布先=%d は配賦済み<br>", $yyyymm, $res1['actcod'], $res1['act_id'], $dest_id);
            query_affected_trans($con, "rollback");     // transaction rollback
            header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
            exit();
        }
        $dest_id  = 2;          // リニア
        $query = sprintf("select pl_bs_ym from act_allo_history where pl_bs_ym=%d and actcod=%d and orign_id=%d and dest_id=%d", $yyyymm, $res1['actcod'], $res1['act_id'], $dest_id);
        if ((getResult($query,$res_hist)) <= 0) {     ///// データ無しのチェック 優先順位の括弧に注意
            ////// 配賦率金額計算
            $allo     = $allo_501_l;
            $dest_kin = $res1['kin'] - $dest_kin;
            /********** 経歴テーブルに書込み ***********/
            $query = sprintf("insert into act_allo_history (pl_bs_ym, actcod, orign_id, orign_kin, k_kubun, div, dest_id, dest_kin, allo) 
                values (%d, %d, %d, %d, '%s', '%s', %d, %d, %01.5f)", $yyyymm, $res1['actcod'], $res1['act_id'], $res1['kin'], $res1['k_kubun'], $res1['div'], $dest_id, $dest_kin, $allo);
            if (query_affected_trans($con, $query) <= 0) {      ///// トランザクション用クエリーの実行
                $_SESSION['s_sysmsg'] .= sprintf("年月=%d:科目=%d:部門=%d:配布先=%d の経歴へ新規登録に失敗しました<br>", $yyyymm, $res1['actcod'], $res1['act_id'], $dest_id);
                query_affected_trans($con, "rollback");     // transaction rollback
                header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
                exit();
            }
        } else {
            $_SESSION['s_sysmsg'] .= sprintf("年月=%d:科目=%d:部門=%d:配布先=%d は配賦済み<br>", $yyyymm, $res1['actcod'], $res1['act_id'], $dest_id);
            query_affected_trans($con, "rollback");     // transaction rollback
            header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
            exit();
        }
    }
} else {
    $_SESSION["s_sysmsg"] .= sprintf("人件費の間接費 対象データなし 部門=%d 第%d期%d月<br>", $act_id, $ki, $tuki);
    query_affected_trans($con, "rollback");     // transaction rollback
    header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
    exit();
}
$act_id  = 545;         // 部門コード 545 の 人件費関係の科目(8101～) 金額取得
$query   = sprintf("select act_id, actcod, k_kubun, div, kin from bm_km_summary where pl_bs_ym=%d and act_id=%d and actcod>=8101 order by actcod ASC", $yyyymm, $act_id);
if (($rows=getResult($query,$res)) > 0) {       ///// データ無しのチェック 優先順位の括弧に注意
    foreach ($res as $res1) {
        /********** 経歴テーブルのデータチェック *************/
        $res_hist = array();
        $dest_id  = 1;          // カプラ
        $query = sprintf("select pl_bs_ym from act_allo_history where pl_bs_ym=%d and actcod=%d and orign_id=%d and dest_id=%d", $yyyymm, $res1['actcod'], $res1['act_id'], $dest_id);
        if ((getResult($query,$res_hist)) <= 0) {     ///// データ無しのチェック 優先順位の括弧に注意
            ////// 配賦率金額計算
            $allo     = $allo_545_c;
            $dest_kin = Uround(($res1['kin'] * $allo), 0);
            /********** 経歴テーブルに書込み ***********/
            $query = sprintf("insert into act_allo_history (pl_bs_ym, actcod, orign_id, orign_kin, k_kubun, div, dest_id, dest_kin, allo) 
                values (%d, %d, %d, %d, '%s', '%s', %d, %d, %01.5f)", $yyyymm, $res1['actcod'], $res1['act_id'], $res1['kin'], $res1['k_kubun'], $res1['div'], $dest_id, $dest_kin, $allo);
            if (query_affected_trans($con, $query) <= 0) {      ///// トランザクション用クエリーの実行
                $_SESSION['s_sysmsg'] .= sprintf("年月=%d:科目=%d:部門=%d:配布先=%d の経歴へ新規登録に失敗しました<br>", $yyyymm, $res1['actcod'], $res1['act_id'], $dest_id);
                query_affected_trans($con, "rollback");     // transaction rollback
                header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
                exit();
            }
        } else {
            $_SESSION['s_sysmsg'] .= sprintf("年月=%d:科目=%d:部門=%d:配布先=%d は配賦済み<br>", $yyyymm, $res1['actcod'], $res1['act_id'], $dest_id);
            query_affected_trans($con, "rollback");     // transaction rollback
            header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
            exit();
        }
        $dest_id  = 2;          // リニア
        $query = sprintf("select pl_bs_ym from act_allo_history where pl_bs_ym=%d and actcod=%d and orign_id=%d and dest_id=%d", $yyyymm, $res1['actcod'], $res1['act_id'], $dest_id);
        if ((getResult($query,$res_hist)) <= 0) {     ///// データ無しのチェック 優先順位の括弧に注意
            ////// 配賦率金額計算
            $allo     = $allo_545_l;
            $dest_kin = $res1['kin'] - $dest_kin;
            /********** 経歴テーブルに書込み ***********/
            $query = sprintf("insert into act_allo_history (pl_bs_ym, actcod, orign_id, orign_kin, k_kubun, div, dest_id, dest_kin, allo) 
                values (%d, %d, %d, %d, '%s', '%s', %d, %d, %01.5f)", $yyyymm, $res1['actcod'], $res1['act_id'], $res1['kin'], $res1['k_kubun'], $res1['div'], $dest_id, $dest_kin, $allo);
            if (query_affected_trans($con, $query) <= 0) {      ///// トランザクション用クエリーの実行
                $_SESSION['s_sysmsg'] .= sprintf("年月=%d:科目=%d:部門=%d:配布先=%d の経歴へ新規登録に失敗しました<br>", $yyyymm, $res1['actcod'], $res1['act_id'], $dest_id);
                query_affected_trans($con, "rollback");     // transaction rollback
                header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
                exit();
            }
        } else {
            $_SESSION['s_sysmsg'] .= sprintf("年月=%d:科目=%d:部門=%d:配布先=%d は配賦済み<br>", $yyyymm, $res1['actcod'], $res1['act_id'], $dest_id);
            query_affected_trans($con, "rollback");     // transaction rollback
            header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
            exit();
        }
    }
} else {
    $_SESSION["s_sysmsg"] .= sprintf("人件費の間接費 対象データなし 部門=%d 第%d期%d月<br>", $act_id, $ki, $tuki);
    query_affected_trans($con, "rollback");     // transaction rollback
    header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
    exit();
}
$act_id  = 580;         // 部門コード 580 の 全金額取得
$query   = sprintf("select act_id, actcod, k_kubun, div, kin from bm_km_summary where pl_bs_ym=%d and act_id=%d order by actcod ASC", $yyyymm, $act_id);
if (($rows=getResult($query,$res)) > 0) {       ///// データ無しのチェック 優先順位の括弧に注意
    foreach ($res as $res1) {
        /********** 経歴テーブルのデータチェック *************/
        $res_hist = array();
        $dest_id  = 1;          // カプラ
        $query = sprintf("select pl_bs_ym from act_allo_history where pl_bs_ym=%d and actcod=%d and orign_id=%d and dest_id=%d", $yyyymm, $res1['actcod'], $res1['act_id'], $dest_id);
        if ((getResult($query,$res_hist)) <= 0) {     ///// データ無しのチェック 優先順位の括弧に注意
            ////// 配賦率金額計算
            $dest_kin = $res1['kin'];
            /********** 経歴テーブルに書込み ***********/
            $query = sprintf("insert into act_allo_history (pl_bs_ym, actcod, orign_id, orign_kin, k_kubun, div, dest_id, dest_kin) 
                values (%d, %d, %d, %d, '%s', '%s', %d, %d)", $yyyymm, $res1['actcod'], $res1['act_id'], $res1['kin'], $res1['k_kubun'], $res1['div'], $dest_id, $dest_kin);
            if (query_affected_trans($con, $query) <= 0) {      ///// トランザクション用クエリーの実行
                $_SESSION['s_sysmsg'] .= sprintf("年月=%d:科目=%d:部門=%d:配布先=%d の経歴へ新規登録に失敗しました<br>", $yyyymm, $res1['actcod'], $res1['act_id'], $dest_id);
                query_affected_trans($con, "rollback");     // transaction rollback
                header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
                exit();
            }
        } else {
            $_SESSION['s_sysmsg'] .= sprintf("年月=%d:科目=%d:部門=%d:配布先=%d は配賦済み<br>", $yyyymm, $res1['actcod'], $res1['act_id'], $dest_id);
            query_affected_trans($con, "rollback");     // transaction rollback
            header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
            exit();
        }
    }
} else {
    $_SESSION["s_sysmsg"] .= sprintf("人件費の間接費 対象データなし 部門=%d 第%d期%d月<br>", $act_id, $ki, $tuki);
    query_affected_trans($con, "rollback");     // transaction rollback
    header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
    exit();
}
$act_id  = 670;         // 部門コード 670 の 全金額取得
$query   = sprintf("select act_id, actcod, k_kubun, div, kin from bm_km_summary where pl_bs_ym=%d and act_id=%d order by actcod ASC", $yyyymm, $act_id);
if (($rows=getResult($query,$res)) > 0) {       ///// データ無しのチェック 優先順位の括弧に注意
    foreach ($res as $res1) {
        /********** 経歴テーブルのデータチェック *************/
        $res_hist = array();
        $dest_id  = 1;          // カプラ
        $query = sprintf("select pl_bs_ym from act_allo_history where pl_bs_ym=%d and actcod=%d and orign_id=%d and dest_id=%d", $yyyymm, $res1['actcod'], $res1['act_id'], $dest_id);
        if ((getResult($query,$res_hist)) <= 0) {     ///// データ無しのチェック 優先順位の括弧に注意
            ////// 配賦率金額計算
            $dest_kin = $res1['kin'];
            /********** 経歴テーブルに書込み ***********/
            $query = sprintf("insert into act_allo_history (pl_bs_ym, actcod, orign_id, orign_kin, k_kubun, div, dest_id, dest_kin) 
                values (%d, %d, %d, %d, '%s', '%s', %d, %d)", $yyyymm, $res1['actcod'], $res1['act_id'], $res1['kin'], $res1['k_kubun'], $res1['div'], $dest_id, $dest_kin);
            if (query_affected_trans($con, $query) <= 0) {      ///// トランザクション用クエリーの実行
                $_SESSION['s_sysmsg'] .= sprintf("年月=%d:科目=%d:部門=%d:配布先=%d の経歴へ新規登録に失敗しました<br>", $yyyymm, $res1['actcod'], $res1['act_id'], $dest_id);
                query_affected_trans($con, "rollback");     // transaction rollback
                header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
                exit();
            }
        } else {
            $_SESSION['s_sysmsg'] .= sprintf("年月=%d:科目=%d:部門=%d:配布先=%d は配賦済み<br>", $yyyymm, $res1['actcod'], $res1['act_id'], $dest_id);
            query_affected_trans($con, "rollback");     // transaction rollback
            header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
            exit();
        }
    }
} else {
    $_SESSION["s_sysmsg"] .= sprintf("人件費の間接費 対象データなし 部門=%d 第%d期%d月<br>", $act_id, $ki, $tuki);
    query_affected_trans($con, "rollback");     // transaction rollback
    header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
    exit();
}
/*********** 販管費の人件費 ＣＬ 配賦率計算 *************/
// 直接費のＣＬ給料比から 配賦率を計算
$actcod = 8102;     // 給料手当
    // カプラ
$query = sprintf("select sum(kin) from bm_km_summary where pl_bs_ym=%d and actcod=%d and k_kubun='1' and div='C'", $yyyymm, $actcod);
if ((getResult($query,$res)) > 0) {
    $kin_c = $res[0][0];
}
if ($kin_c != 0) {      ///// データ無しのチェック
        // リニア
    $query = sprintf("select sum(kin) from bm_km_summary where pl_bs_ym=%d and actcod=%d and k_kubun='1' and div='L'", $yyyymm, $actcod);
    if ((getResult($query,$res)) > 0) {       ///// データ無しのチェック 優先順位の括弧に注意
        $kin_l  = $res[0][0];
        $kin    = $kin_c + $kin_l;
        $allo_c = Uround(($kin_c / $kin),5);
        $allo_l = 1 - $allo_c;
        $query = sprintf("select pl_bs_ym from act_allo_history where pl_bs_ym=%d and actcod=%d and orign_id=0 and k_kubun=' ' and div=' '", $yyyymm, $actcod);
        if (($rows=getResult($query,$res)) <= 0) {       ///// データ無しのチェック 優先順位の括弧に注意
            // カプラ
            $query = sprintf("insert into act_allo_history (pl_bs_ym, actcod, orign_id, orign_kin, k_kubun, div, dest_id, dest_kin, allo, note) values (%d, %d, 0, %d, ' ', ' ', 1, %d, %1.5f, 'カプラ給料比率')",
                $yyyymm, $actcod, $kin, $kin_c, $allo_c);
            if ((query_affected_trans($con, $query)) <= 0) {    // トランザクション用クエリー実行
                $_SESSION['s_sysmsg'] .= sprintf("カプラの直接給料手当比率=%1.5f の経歴へ新規登録に失敗しました<br>", $allo_c);
                query_affected_trans($con, "rollback");     // transaction rollback
                header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
                exit();
            }
            // リニア
            $query = sprintf("insert into act_allo_history (pl_bs_ym, actcod, orign_id, orign_kin, k_kubun, div, dest_id, dest_kin, allo, note) values (%d, %d, 0, %d, ' ', ' ', 2, %d, %1.5f, 'リニア給料比率')",
                $yyyymm, $actcod, $kin, $kin_l, $allo_l);
            if ((query_affected_trans($con, $query)) <= 0) {    // トランザクション用クエリー実行
                $_SESSION['s_sysmsg'] .= sprintf("リニアの直接給料手当比率=%1.5f の経歴へ新規登録に失敗しました<br>", $allo_l);
                query_affected_trans($con, "rollback");     // transaction rollback
                header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
                exit();
            }
        } else {
            // 既に経歴ファイルに登録れている
            $_SESSION["s_sysmsg"] .= sprintf("人件費の販管費 配賦率 登録済み 第%d期%d月<br>", $ki, $tuki);
            query_affected_trans($con, "rollback");     // transaction rollback
            header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
            exit();
        }
    } else {
        $_SESSION["s_sysmsg"] .= sprintf("人件費の販管費 リニア対象データなし 第%d期%d月<br>", $ki, $tuki);
        query_affected_trans($con, "rollback");     // transaction rollback
        header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
        exit();
    }
} else {
    $_SESSION["s_sysmsg"] .= sprintf("人件費の販管費 カプラ対象データなし 第%d期%d月<br>", $ki, $tuki);
    query_affected_trans($con, "rollback");     // transaction rollback
    header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
    exit();
}
/*********** 経費の間接費・販管費 ＣＬ 配賦率計算 *************/
// 経費合計 直接ＣＬ比率による 配賦率
    // カプラの合計直接費を取得
$query = sprintf("select sum(kin) from bm_km_summary where pl_bs_ym=%d and actcod>=7501 and actcod<=8000 and k_kubun='1' and div='C'", $yyyymm);
if ((getResult($query,$res)) > 0) {
    $kin_c = $res[0][0];
}
if ($kin_c != 0) {      ///// データ無しのチェック
        // リニアの合計直接費を取得
    $query = sprintf("select sum(kin) from bm_km_summary where pl_bs_ym=%d and actcod>=7501 and actcod<=8000 and k_kubun='1' and div='L'", $yyyymm);
    if ((getResult($query,$res)) > 0) {       ///// データ無しのチェック 優先順位の括弧に注意
        $kin_l = $res[0][0];
        $kin    = $kin_c + $kin_l;
        $allo_c = Uround(($kin_c / $kin),5);
        $allo_l = 1 - $allo_c;
        $query = sprintf("select pl_bs_ym from act_allo_history where pl_bs_ym=%d and actcod=0 and orign_id=0 and k_kubun='1' and div='C'", $yyyymm);
        if (($rows=getResult($query,$res)) <= 0) {       ///// データ無しのチェック 優先順位の括弧に注意
            // カプラ
            $query = sprintf("insert into act_allo_history (pl_bs_ym, actcod, orign_id, orign_kin, k_kubun, div, dest_id, dest_kin, allo, note) values (%d, 0, 0, %d, '1', 'C', 1, %d, %1.5f, 'カプラ経費比率')",
                $yyyymm, $kin, $kin_c, $allo_c);
            if ((query_affected_trans($con, $query)) <= 0) {    // トランザクション用クエリー実行
                $_SESSION['s_sysmsg'] .= sprintf("カプラの経費合計比率=%1.5f の経歴へ新規登録に失敗しました<br>", $allo_c);
                query_affected_trans($con, "rollback");     // transaction rollback
                header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
                exit();
            }
            // リニア
            $query = sprintf("insert into act_allo_history (pl_bs_ym, actcod, orign_id, orign_kin, k_kubun, div, dest_id, dest_kin, allo, note) values (%d, 0, 0, %d, '1', 'L', 2, %d, %1.5f, 'リニア経費比率')",
                $yyyymm, $kin, $kin_l, $allo_l);
            if ((query_affected_trans($con, $query)) <= 0) {    // トランザクション用クエリー実行
                $_SESSION['s_sysmsg'] .= sprintf("リニアの経費合計比率=%1.5f の経歴へ新規登録に失敗しました<br>", $allo_l);
                query_affected_trans($con, "rollback");     // transaction rollback
                header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
                exit();
            }
        } else {
            // 既に経歴ファイルに登録れている
            $_SESSION["s_sysmsg"] .= sprintf("経費合計 配賦率 登録済み 第%d期%d月<br>", $ki, $tuki);
            query_affected_trans($con, "rollback");     // transaction rollback
            header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
            exit();
        }
    } else {
        $_SESSION["s_sysmsg"] .= sprintf("経費の直接費 リニア対象データなし 第%d期%d月<br>", $ki, $tuki);
        query_affected_trans($con, "rollback");     // transaction rollback
        header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
        exit();
    }
} else {
    $_SESSION["s_sysmsg"] .= sprintf("経費の直接費 カプラ対象データなし 第%d期%d月<br>", $ki, $tuki);
    query_affected_trans($con, "rollback");     // transaction rollback
    header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
    exit();
}
//////////// ＣＬ面積比 配賦率
$allo_c = 0.72439;     // 配賦率カプラ 第５期の５月まで
$allo_l = 0.27561;     // 配賦率リニア 第５期の５月まで
// 第５期の６月～以下の様に変更
$allo_c = 0.83133;
$allo_l = 0.16867;
$query = sprintf("select pl_bs_ym from act_allo_history where pl_bs_ym=%d and actcod=7512 and orign_id=0 and k_kubun='1' and div='C'", $yyyymm, $actcod);
if (($rows=getResult($query,$res)) <= 0) {       ///// データ無しのチェック 優先順位の括弧に注意
    // カプラ
    $actcod = 7512;     // 業務委託費
    $query = sprintf("insert into act_allo_history (pl_bs_ym, actcod, orign_id, orign_kin, k_kubun, div, dest_id, dest_kin, allo, note) values (%d, %d, 0, 0, '1', 'C', 1, 0, %1.5f, 'カプラ面積比率')",
        $yyyymm, $actcod, $allo_c);
    if ((query_affected_trans($con, $query)) <= 0) {    // トランザクション用クエリー実行
        $_SESSION['s_sysmsg'] .= sprintf("カプラの面積比率=%1.5f の経歴へ新規登録に失敗しました<br>", $allo_c);
        query_affected_trans($con, "rollback");     // transaction rollback
        header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
        exit();
    }
    // リニア
    $query = sprintf("insert into act_allo_history (pl_bs_ym, actcod, orign_id, orign_kin, k_kubun, div, dest_id, dest_kin, allo, note) values (%d, %d, 0, 0, '1', 'L', 2, 0, %1.5f, 'リニア面積比率')",
        $yyyymm, $actcod, $allo_l);
    if ((query_affected_trans($con, $query)) <= 0) {    // トランザクション用クエリー実行
        $_SESSION['s_sysmsg'] .= sprintf("リニアの面積比率=%1.5f の経歴へ新規登録に失敗しました<br>", $allo_l);
        query_affected_trans($con, "rollback");     // transaction rollback
        header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
        exit();
    }
    $actcod = 7540;     // 賃借料
    $query = sprintf("insert into act_allo_history (pl_bs_ym, actcod, orign_id, orign_kin, k_kubun, div, dest_id, dest_kin, allo, note) values (%d, %d, 0, 0, '1', 'C', 1, 0, %1.5f, 'カプラ面積比率')",
        $yyyymm, $actcod, $allo_c);
    if ((query_affected_trans($con, $query)) <= 0) {    // トランザクション用クエリー実行
        $_SESSION['s_sysmsg'] .= sprintf("カプラの面積比率=%1.5f の経歴へ新規登録に失敗しました<br>", $allo_c);
        query_affected_trans($con, "rollback");     // transaction rollback
        header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
        exit();
    }
    // リニア
    $query = sprintf("insert into act_allo_history (pl_bs_ym, actcod, orign_id, orign_kin, k_kubun, div, dest_id, dest_kin, allo, note) values (%d, %d, 0, 0, '1', 'L', 2, 0, %1.5f, 'リニア面積比率')",
        $yyyymm, $actcod, $allo_l);
    if ((query_affected_trans($con, $query)) <= 0) {    // トランザクション用クエリー実行
        $_SESSION['s_sysmsg'] .= sprintf("リニアの面積比率=%1.5f の経歴へ新規登録に失敗しました<br>", $allo_l);
        query_affected_trans($con, "rollback");     // transaction rollback
        header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
        exit();
    }
    $actcod = 8000;     // 減価償却費
    $query = sprintf("insert into act_allo_history (pl_bs_ym, actcod, orign_id, orign_kin, k_kubun, div, dest_id, dest_kin, allo, note) values (%d, %d, 0, 0, '1', 'C', 1, 0, %1.5f, 'カプラ面積比率')",
        $yyyymm, $actcod, $allo_c);
    if ((query_affected_trans($con, $query)) <= 0) {    // トランザクション用クエリー実行
        $_SESSION['s_sysmsg'] .= sprintf("カプラの面積比率=%1.5f の経歴へ新規登録に失敗しました<br>", $allo_c);
        query_affected_trans($con, "rollback");     // transaction rollback
        header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
        exit();
    }
    // リニア
    $query = sprintf("insert into act_allo_history (pl_bs_ym, actcod, orign_id, orign_kin, k_kubun, div, dest_id, dest_kin, allo, note) values (%d, %d, 0, 0, '1', 'L', 2, 0, %1.5f, 'リニア面積比率')",
        $yyyymm, $actcod, $allo_l);
    if ((query_affected_trans($con, $query)) <= 0) {    // トランザクション用クエリー実行
        $_SESSION['s_sysmsg'] .= sprintf("リニアの面積比率=%1.5f の経歴へ新規登録に失敗しました<br>", $allo_l);
        query_affected_trans($con, "rollback");     // transaction rollback
        header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
        exit();
    }
} else {
    // 既に経歴ファイルに登録れている
    $_SESSION["s_sysmsg"] .= sprintf("面積による 配賦率 登録済み 第%d期%d月<br>", $ki, $tuki);
    query_affected_trans($con, "rollback");     // transaction rollback
    header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
    exit();
}

/*********** 工場消耗品費 直接費のＣＬ比率による 配賦率計算 *************/
$actcod = 7527;     // 工場消耗品費
    // カプラの合計直接費を取得
$query = sprintf("select sum(kin) from bm_km_summary where pl_bs_ym=%d and actcod=%d and k_kubun='1' and div='C'", $yyyymm, $actcod);
if ((getResult($query,$res)) > 0) {
    $kin_c = $res[0][0];
}
if ($kin_c != 0) {      ///// データ無しのチェック
        // リニアの合計直接費を取得
    $query = sprintf("select sum(kin) from bm_km_summary where pl_bs_ym=%d and actcod=%d and k_kubun='1' and div='L'", $yyyymm, $actcod);
    if ((getResult($query,$res)) > 0) {       ///// データ無しのチェック 優先順位の括弧に注意
        $kin_l = $res[0][0];
        $kin    = $kin_c + $kin_l;
        $allo_c = Uround(($kin_c / $kin),5);
        $allo_l = 1 - $allo_c;
        $query = sprintf("select pl_bs_ym from act_allo_history where pl_bs_ym=%d and actcod=%d and orign_id=0 and k_kubun='1' and div='C'", $yyyymm, $actcod);
        if (($rows=getResult($query,$res)) <= 0) {       ///// データ無しのチェック 優先順位の括弧に注意
            // カプラ
            $query = sprintf("insert into act_allo_history (pl_bs_ym, actcod, orign_id, orign_kin, k_kubun, div, dest_id, dest_kin, allo, note) values (%d, %d, 0, %d, '1', 'C', 1, %d, %1.5f, 'カプラ消耗品比率')",
                $yyyymm, $actcod, $kin, $kin_c, $allo_c);
            if ((query_affected_trans($con, $query)) <= 0) {    // トランザクション用クエリー実行
                $_SESSION['s_sysmsg'] .= sprintf("カプラの消耗品費比率=%1.5f の経歴へ新規登録に失敗しました<br>", $allo_c);
                query_affected_trans($con, "rollback");     // transaction rollback
                header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
                exit();
            }
            // リニア
            $query = sprintf("insert into act_allo_history (pl_bs_ym, actcod, orign_id, orign_kin, k_kubun, div, dest_id, dest_kin, allo, note) values (%d, %d, 0, %d, '1', 'L', 2, %d, %1.5f, 'リニア消耗品比率')",
                $yyyymm, $actcod, $kin, $kin_l, $allo_l);
            if ((query_affected_trans($con, $query)) <= 0) {    // トランザクション用クエリー実行
                $_SESSION['s_sysmsg'] .= sprintf("リニアの消耗品費比率=%1.5f の経歴へ新規登録に失敗しました<br>", $allo_l);
                query_affected_trans($con, "rollback");     // transaction rollback
                header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
                exit();
            }
        } else {
            // 既に経歴ファイルに登録れている
            $_SESSION["s_sysmsg"] .= sprintf("消耗品費 配賦率 登録済み 第%d期%d月<br>", $ki, $tuki);
            query_affected_trans($con, "rollback");     // transaction rollback
            header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
            exit();
        }
    } else {
        $_SESSION["s_sysmsg"] .= sprintf("消耗品費 リニア対象データなし 第%d期%d月<br>", $ki, $tuki);
        query_affected_trans($con, "rollback");     // transaction rollback
        header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
        exit();
    }
} else {
    $_SESSION["s_sysmsg"] .= sprintf("消耗品費 カプラ対象データなし 第%d期%d月<br>", $ki, $tuki);
    query_affected_trans($con, "rollback");     // transaction rollback
    header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
    exit();
}

/////////// commit トランザクション終了
query_affected_trans($con, "commit");
$_SESSION["s_sysmsg"] .= sprintf("<font color='yellow'>第%d期 %d月の配賦率計算完了</font>",$ki,$tuki);
header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
exit();


//////////// タイトルの日付・時間設定
$today = date("Y/m/d H:m:s");

header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");    // 日付が過去
header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT"); // 常に修正されている
header("Cache-Control: no-store, no-cache, must-revalidate");  // HTTP/1.1
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");                          // HTTP/1.0
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<HTML>
<HEAD>
<META http-equiv="Content-Type" content="text/html; charset=UTF-8">
<META http-equiv="Content-Style-Type" content="text/css">
<TITLE>ＣＬ経費配賦率計算</TITLE>
<script language="JavaScript">
<!--
    parent.menu_site.location = 'http:<?php echo(WEB_HOST) ?>menu_site.php';
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
.pt8 {
    font:normal 8pt;
}
.pt10 {
    font:normal 10pt;
}
.pt10b {
    font:bold 10pt;
}
.pt12b {
    font:bold 12pt;
}
.margin0 {
    margin:0%;
}
-->
</style>
</HEAD>
<BODY class='margin0'>
    <center>
    </center>
</BODY>
</HTML>
