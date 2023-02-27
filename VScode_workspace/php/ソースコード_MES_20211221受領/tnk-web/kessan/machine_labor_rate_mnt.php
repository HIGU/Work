<?php
//////////////////////////////////////////////////////////////////////////////
// 機械賃率計算表 手作業(刻印)・機械運転時間を入力し賃率を自動算出          //
// Copyright (C) 2002-2021      Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp //
// Changed history                                                          //
// 2002/09/23 Created   machine_labor_rate_mnt.php                          //
// 2002/10/09 機械賃率を小数点以下２桁が０でも必ず表示させる(照会)          //
// 2003/02/26 body に onLoad を追加し初期入力個所に focus() させた          //
// 2003/09/08 自由設定(ランダム)時のSQL文の条件に不具合あり (>=)→(=)       //
// 2003/10/08 前期実績賃率の SQL文に offset 1 を追加し前決算時の賃率へ      //
// 2003/12/18 なぜか単月処理時の POST データが送信されない不具合に対応      //
// 2004/10/28 user_check()functionを追加し編集出来るユーザーを限定          //
// 2005/10/27 MenuHeader class を使用して共通メニュー化及び認証方式へ変更   //
// 2005/11/05 単月処理が２重登録される現象が出たためMenuHeaderを最後へ移動  //
// 2007/02/05 account_group_check()で登録できるユーザーの確認を追加         //
// 2007/09/25 変数の初期化を追加                                            //
// 2010/06/03 前期実績賃率のSQL文を訂正                                大谷 //
// 2014/04/11 製造２課の管理経費の配賦を追加                           大谷 //
// 2016/05/23 製造２課追加の際に配賦がおかしくなっていたのを訂正       大谷 //
// 2016/06/09 割合によって配賦差額が発生。→差額は最大の割合部門へ     大谷 //
// 2018/06/05 単月処理時、前期データ取得でエラーの為、修正             大谷 //
// 2021/04/06 2103にリース料の調整 リース資産が527でマイナスの為       大谷 //
//////////////////////////////////////////////////////////////////////////////
ini_set('error_reporting', E_ALL & ~E_NOTICE);  // E_ALL='2047' debug 用
// ini_set('display_errors', '1');          // Error 表示 ON debug 用 リリース後コメント
session_start();                            // ini_set()の次に指定すること Script 最上行

require_once ('../function.php');           // define.php と pgsql.php を require_once している
require_once ('../tnk_func.php');           // TNK に依存する部分の関数を require_once している
require_once ('../MenuHeader.php');         // TNK 全共通 menu class
access_log();                               // Script Name は自動取得

/////////// ユーザーのチェック
$uid = $_SESSION['User_ID'];            // ユーザー
function user_check($uid)
{
    if (account_group_check() == FALSE) {
        $_SESSION['s_sysmsg'] = "社員番号：{$uid}：{$name}さんでは機械賃率の登録は出来ません！ 管理担当者へ連絡して下さい。";
        return false;
    } else {
        return true;
    }
    switch ($uid) {
    case '017850':      // 上野
    case '300055':      // 斎藤
    case '300101':      // 大谷
    case '010561':      // 小林
        return TRUE;
        break;
    default:
        $query = "select trim(name) from user_detailes where uid = '{$uid}' limit 1";
        if (getUniResult($query, $name) <= 0) $name = '';
        $_SESSION['s_sysmsg'] = "社員番号：{$uid}：{$name}さんでは機械賃率の登録は出来ません！ 管理担当者へ連絡して下さい。";
        return FALSE;
    }
}

//////////// POST データ不具合のため以下を追加
if (isset($_POST['rate_ym'])) {
    $_POST['tangetu'] = '単月処理';
}

$today = date("Y-m-d");

if ( isset($_POST['tangetu']) ) {
    $_SESSION['rate_ym'] = $_POST['rate_ym'];
    $_SESSION['tangetu'] = $_POST['tangetu'];
    unset($_SESSION['kessan']);
    $_SESSION['str_ym'] = "";           // 初期化
    $_SESSION['end_ym'] = "";           // 初期化
    $_SESSION['span']   = "";           // 初期化
}
if ( isset($_POST['kessan']) ) {
    $_SESSION['str_ym'] = $_POST['str_ym'];
    $_SESSION['end_ym'] = $_POST['end_ym'];
    $_SESSION['span'] = $_POST['span'];
    $_SESSION['kessan'] = $_POST['kessan'];
    unset($_SESSION['tangetu']);
    unset($_SESSION['rate_ym']);
}
if (isset($_SESSION['rate_ym'])) {
    $rate_ym = $_SESSION['rate_ym'];
} else {
    $rate_ym = "";                      // 初期化
}
if (!isset($_SESSION['str_ym'])) {
    $_SESSION['str_ym'] = "";           // 初期化
}
if (!isset($_SESSION['end_ym'])) {
    $_SESSION['end_ym'] = "";           // 初期化
}
if (!isset($_SESSION['span'])) {
    $_SESSION['span'] = "";             // 初期化
}
if ( isset($_POST['check']) ) {
    $_SESSION['h_cost'] = $_POST['h_cost'];
    $_SESSION['ope_time'] = $_POST['ope_time'];
}
if ( isset($_POST['tangetu']) || isset($_POST['kessan']) || isset($_POST['check']) || isset($_POST['insert']) ) {
    if (isset($_SESSION['tangetu'])) { ////////////// 月次処理 必ず行うこと
        $query = "select * from machine_labor_rate where rate_ym=" . $_SESSION['rate_ym'] . " and settle=0 order by reg_date DESC";
    } else if (isset($_SESSION['kessan'])) {
        if ($_SESSION['span'] == 1) { /////////////// 中間決算
            $query = "select * from machine_labor_rate where rate_ym>=" . $_SESSION['str_ym'] . " and rate_ym<=" . $_SESSION['end_ym'] . " and settle=1 order by reg_date DESC";
        } else if ($_SESSION['span'] == 2) { ////////// 期末決算
            $query = "select * from machine_labor_rate where rate_ym>=" . $_SESSION['str_ym'] . " and rate_ym<=" . $_SESSION['end_ym'] . " and settle=2 order by reg_date DESC";
        } else { ///////////////////////////////////// ランダム(年間・四半期 等)
            $query = "select * from machine_labor_rate where str_ym = " . $_SESSION['str_ym'] . " and rate_ym = " . $_SESSION['end_ym'] . " and settle = 3 order by reg_date DESC";
        }
    }
    $res = array();
    if ( ($rows_act=getResult($query,$res)) >= 1) {      // 登録済みのチェック
        ///////////////////////////// 経歴あり
        $register = "照会";
        $act_id = array();      // 部門コード
        $b_name = array();      // 部門名(短縮)
        $depre = array();       // 減価償却費
        $lease = array();       // リース料
        $repair = array();      // 修繕費
        $w_cost = array();      // 工場消耗品費
        $p_cost = array();      // 人件費
        $e_cost = array();      // 電力料
        $other  = array();      // その他
        $m_cost = array();      // 管理経費配賦
        $h_cost = array();      // 手作業費
        $t_cost = array();      // 合計
        $man    = array();      // 人数
        $ope_time = array();    // 運転時間
        $labor_rate = array();  // 機械賃率
        ////////////////////////// 初期化
        $depre_sum  = 0;
        $lease_sum  = 0;
        $repair_sum = 0;
        $w_cost_sum = 0;
        $p_cost_sum = 0;
        $e_cost_sum = 0;
        $other_sum  = 0;
        $m_cost_sum = 0;
        $man_sum    = 0;
        $h_cost_sum = 0;
        $ope_time_sum = 0;
        ////////////////////////// 初期化 END
        for ($i=0; $i<$rows_act; $i++) {                // $rows_act は下で多用するため要注意
            $act_id[$i] = $res[$i]['act_id'];
            $b_name[$i] = $res[$i]['s_name'];
            $depre[$i] = $res[$i]['depre'];
            $lease[$i] = $res[$i]['lease'];
            $repair[$i] = $res[$i]['repair'];
            $w_cost[$i] = $res[$i]['w_cost'];
            $p_cost[$i] = $res[$i]['p_cost'];
            $e_cost[$i] = $res[$i]['e_cost'];
            $other[$i] = $res[$i]['other'];
            $m_cost_all[$i] = $res[$i]['m_cost'];
            $_SESSION['h_cost'][$i] = $res[$i]['h_cost'];
            $_SESSION['ope_time'][$i] = $res[$i]['ope_time'];
            $man[$i] = $res[$i]['man'];
            $labor_rate[$i] = $res[$i]['labor_rate'];
            ////////////////// 製造合計処理
            $depre_sum  += $depre[$i];
            $lease_sum  += $lease[$i];
            $repair_sum += $repair[$i];
            $w_cost_sum += $w_cost[$i];
            $p_cost_sum += $p_cost[$i];
            $e_cost_sum += $e_cost[$i];
            $other_sum  += $other[$i];
            $m_cost_sum += $m_cost_all[$i];
            ///// $h_cost_sum は下で計算
            ///// $ope_time_sum は下で計算
            $man_sum += $man[$i];
        }
    } else {
        ///////////////////////////// 新規
        $register = "登録";
        ///////////////////////////// 対象部門コード 部門名の取得
        $query = "select act_id,s_name from act_table where rate_flg='1' order by act_id ASC";
        $res_act = array();
        if ( ($rows_act=getResult($query,$res_act)) >= 1) {
            $act_id = array();
            $b_name = array();
            if ( isset($_SESSION['tangetu']) ) {
                $yymm = substr($_SESSION['rate_ym'],2,4);
            } elseif ( isset($_SESSION['kessan']) ) {
                $s_yymm = substr($_SESSION['str_ym'],2,4);
                $e_yymm = substr($_SESSION['end_ym'],2,4);
            }
            $depre = array();   // 減価償却費
            $lease = array();   // リース料
            $repair = array();  // 修繕費
            $w_cost = array();  // 工場消耗品費
            $p_cost = array();  // 人件費
            $e_cost = array();  // 電力料
            $other  = array();  // その他
            $depre_sum  = 0;
            $lease_sum  = 0;
            $repair_sum = 0;
            $w_cost_sum = 0;
            $p_cost_sum = 0;
            $e_cost_sum = 0;
            $other_sum  = 0;
            for ($i=0; $i<$rows_act; $i++) {
                $act_id[$i] = $res_act[$i]['act_id'];
                $b_name[$i] = trim($res_act[$i]['s_name']);
                ///////////////////////////// 月次処理の手作業時間・運転時間のデータ取得
                if ( isset($_SESSION['kessan']) ) {
                    $query = "select sum(h_cost) as h_c,sum(ope_time) as o_t from machine_labor_rate where settle=0 and rate_ym>=" . $_SESSION['str_ym'] . " and rate_ym<=" . $_SESSION['end_ym'] . " and act_id=" . $act_id[$i];
                    $res_rate = array();
                    if ( ($rows_rate=getResult($query,$res_rate)) >= 1) {
                        if ( !isset($_POST['check']) && !isset($_POST['insert']) ) { ////// フォームの送信データがある時は書替えない
                            $_SESSION['h_cost'][$i] = $res_rate[0]['h_c'];
                            $_SESSION['ope_time'][$i] = $res_rate[0]['o_t'];
                        }
                    }
                }
                ////////////////////////////////////////////////// 直接経費の取得
                if ( isset($_SESSION['tangetu']) ) {
                    $query = "select sum(act_monthly) from act_summary where act_yymm=$yymm and actcod=8000 and act_id=" . $act_id[$i];
                } elseif (isset($_SESSION['kessan'])) {
                    $query = "select sum(act_monthly) from act_summary where act_yymm>=$s_yymm and act_yymm<=$e_yymm and actcod=8000 and act_id=" . $act_id[$i];
                }
                $res_summ = array();
                if ( ($rows_summ=getResult($query,$res_summ)) >= 1) {
                    if ($res_summ[0]['sum'] == "") {
                        $depre[$i] = 0;
                    } else {
                        $depre[$i] = $res_summ[0]['sum'];       // 減価償却費
                    }
                }
                if (isset($_SESSION['tangetu'])) {
                    $query = "select sum(act_monthly) from act_summary where act_yymm=$yymm and actcod=7540 and act_id=" . $act_id[$i];
                } elseif (isset($_SESSION['kessan'])) {
                    $query = "select sum(act_monthly) from act_summary where act_yymm>=$s_yymm and act_yymm<=$e_yymm and actcod=7540 and act_id=" . $act_id[$i];
                }
                $res_summ = array();
                if ( ($rows_summ=getResult($query,$res_summ)) >= 1) {
                    if ($res_summ[0]['sum'] == "") {
                        $lease[$i] = 0;
                    }
                    $lease[$i] = $res_summ[0]['sum'];       // リース料
                    if ($_SESSION['tangetu']) {
                        if ($yymm == 2103) {
                            if ($act_id[$i] == 527) {
                                $lease[$i] = $lease[$i] + 1193400;
                            } elseif ($act_id[$i] == 528) {
                                $lease[$i] = $lease[$i] - 1193400;
                            }
                        }
                    }
                }
                if ( isset($_SESSION['tangetu']) ) {
                    $query = "select sum(act_monthly) from act_summary where act_yymm=$yymm and actcod=7524 and act_id=" . $act_id[$i];
                } elseif (isset($_SESSION['kessan'])) {
                    $query = "select sum(act_monthly) from act_summary where act_yymm>=$s_yymm and act_yymm<=$e_yymm and actcod=7524 and act_id=" . $act_id[$i];
                }
                $res_summ = array();
                if ( ($rows_summ=getResult($query,$res_summ)) >= 1) {
                    if ($res_summ[0]['sum'] == "") {
                        $repair[$i] = 0;
                    }
                    $repair[$i] = $res_summ[0]['sum'];      // 修繕費
                }
                if ( isset($_SESSION['tangetu']) ) {
                    $query = "select sum(act_monthly) from act_summary where act_yymm=$yymm and actcod=7527 and act_id=" . $act_id[$i];
                } elseif ( isset($_SESSION['kessan']) ) {
                    $query = "select sum(act_monthly) from act_summary where act_yymm>=$s_yymm and act_yymm<=$e_yymm and actcod=7527 and act_id=" . $act_id[$i];
                }
                $res_summ = array();
                if ( ($rows_summ=getResult($query,$res_summ)) >= 1) {
                    if ($res_summ[0]['sum'] == "") {
                        $w_cost[$i] = 0;
                    }
                    $w_cost[$i] = $res_summ[0]['sum'];      // 工場消耗品費
                }
                ///////////////////////////////////////////// 間接経費の取得
                if ( isset($_SESSION['tangetu']) ) {
                    $query = "select sum(act_monthly) from act_summary where act_yymm=$yymm and actcod>=8101 and actcod<=8123 and act_id=" . $act_id[$i];
                } elseif ( isset($_SESSION['kessan']) ) {
                    $query = "select sum(act_monthly) from act_summary where act_yymm>=$s_yymm and act_yymm<=$e_yymm and actcod>=8101 and actcod<=8123 and act_id=" . $act_id[$i];
                }
                $res_summ = array();
                if ( ($rows_summ=getResult($query,$res_summ)) >= 1) {
                    if ($res_summ[0]['sum'] == "") {
                        $p_cost[$i] = 0;
                    }
                    $p_cost[$i] = $res_summ[0]['sum'];      // 人件費
                }
                if ( isset($_SESSION['tangetu']) ) {
                    $query = "select sum(act_monthly) from act_summary where act_yymm=$yymm and actcod=7531 and aucod=10 and act_id=" . $act_id[$i];
                } elseif (isset($_SESSION['kessan'])) {
                    $query = "select sum(act_monthly) from act_summary where act_yymm>=$s_yymm and act_yymm<=$e_yymm and actcod=7531 and act_id=" . $act_id[$i];
                }
                $res_summ = array();
                if ( ($rows_summ=getResult($query,$res_summ)) >= 1) {
                    if ($res_summ[0]['sum'] == "") {
                        $e_cost[$i] = 0;
                    }
                    $e_cost[$i] = $res_summ[0]['sum'];      // 電力料
                }
                if ( isset($_SESSION['tangetu']) ) {
                    $query = "select sum(act_monthly) from act_summary where act_yymm=$yymm and act_id=" . $act_id[$i];
                } elseif (isset($_SESSION['kessan'])) {
                    $query = "select sum(act_monthly) from act_summary where act_yymm>=$s_yymm and act_yymm<=$e_yymm and act_id=" . $act_id[$i];
                }
                $res_summ = array();
                if ( ($rows_summ=getResult($query,$res_summ)) >= 1) {
                    $other[$i] = $res_summ[0]['sum'];       // 全て
                    $other[$i] -= ($depre[$i] + $lease[$i] + $repair[$i] + $w_cost[$i] + $p_cost[$i] + $e_cost[$i]);
                    if ($other[$i] == "") {
                        $depre[$i] = 0;
                    }
                }
                $depre_sum += $depre[$i];
                $lease_sum += $lease[$i];
                $repair_sum += $repair[$i];
                $w_cost_sum += $w_cost[$i];
                $p_cost_sum += $p_cost[$i];
                $e_cost_sum += $e_cost[$i];
                $other_sum += $other[$i];
            }
        }
        /////////////////////////////////////////// 管理経費の配賦 act_allocation 配賦率マスターから取得するように変更予定
        $query = "select act_id,s_name from act_table where rate_flg='2' order by act_id ASC"; ///// 管理経費の部門取得
        $res_tbl = array();
        if ( ($rows_tbl=getResult($query,$res_tbl)) >= 1) {
            $m_cost   = array();      // 二次元配列
            $m_check  = array();      // 配賦割合差額発生対応
            for ($a=0; $a<$rows_tbl; $a++) {        // 管理部門(配賦元)コードが複数ある場合に対応
                $m_sagaku = 0;            // 配賦割合差額発生対応 差額入れ
                if($res_tbl[$a]['act_id'] == 518) {
                    $query = "select dest_id,allo_rate from act_allocation where orign_id=" . $res_tbl[$a]['act_id'] . " and allo_id=11 order by dest_id ASC";
                } elseif($res_tbl[$a]['act_id'] == 547) {
                    $query = "select dest_id,allo_rate from act_allocation where orign_id=" . $res_tbl[$a]['act_id'] . " and allo_id=17 order by dest_id ASC";
                }
                $res_allo = array();
                if ( ($rows_allo=getResult($query,$res_allo) )>= 1) { ////// 配賦率マスターより配賦先部門・配賦率取得
                    if ( isset($_SESSION['tangetu']) ) {
                        $query = "select sum(act_monthly) from act_summary where act_yymm=$yymm and act_id=" . $res_tbl[$a]['act_id'];
                    } elseif ( isset($_SESSION['kessan']) ) {
                        $query = "select sum(act_monthly) from act_summary where act_yymm>=$s_yymm and act_yymm<=$e_yymm and act_id=" . $res_tbl[$a]['act_id'];
                    }
                    $res_man = array();
                    if ( ($rows_man=getResult($query,$res_man)) >= 1) {
                        for ($b=0; $b<$rows_allo; $b++) { ///////// 配賦率マスターの部門数
                            $m_cost[$a][$b] = corrc_round($res_man[0]['sum'] * ($res_allo[$b]['allo_rate'] / 100)); /////// 製造 各直接部門の配賦率
                            // 配賦割合差額発生対応
                            $m_check[$a] += $m_cost[$a][$b];
                        }
                        // 配賦割合差額発生対応
                        if($res_man[0]['sum'] <> $m_check[$a]) {            // 元金額と配賦金額に差があれば差額調整
                            $m_sagaku = $res_man[0]['sum'] - $m_check[$a];  // 差額を計算
                            // それぞれの最大配賦部門と率を取得
                            if($res_tbl[$a]['act_id'] == 518) {
                                $query = "select dest_id,allo_rate from act_allocation where orign_id=" . $res_tbl[$a]['act_id'] . " and allo_rate=(select max(allo_rate) from act_allocation where orign_id=518) and allo_id=11 order by dest_id ASC";
                            } elseif($res_tbl[$a]['act_id'] == 547) {
                                $query = "select dest_id,allo_rate from act_allocation where orign_id=" . $res_tbl[$a]['act_id'] . " and allo_rate=(select max(allo_rate) from act_allocation where orign_id=547) and allo_id=17 order by dest_id ASC";
                            }
                            $mres_allo  = array();
                            $mrows_allo = getResult($query,$mres_allo);
                            for ($b=0; $b<$rows_allo; $b++) { ///////// 配賦率マスターの部門数
                                if ($mres_allo[0]['allo_rate'] == $res_allo[$b]['allo_rate']) {
                                    $m_cost[$a][$b] = $m_cost[$a][$b] + $m_sagaku;
                                }
                            }
                        }
                    }
                }
            }
            $m_cost_all = array();
            for ($a=0; $a<$rows_tbl; $a++) { ///////////// 管理部門(配賦元)コードが複数ある場合に対応
                for ($b=0; $b<$rows_allo; $b++) { //////////// 配賦率マスターの部門数
                    $m_cost_all[$b] += $m_cost[$a][$b];
                }
            }
            $m_cost_sum = 0;           // 初期化
            for ($b=0; $b<$rows_allo; $b++) { //////////// 配賦率マスターの部門数
                $m_cost_sum     += $m_cost_all[$b];
            }
        }
    }
    /////////////////////// 直接費の小計 算出
    $s1_sum = array();
    for ($i=0; $i<$rows_act; $i++) {
        $s1_sum[$i] = ($depre[$i] + $lease[$i] + $repair[$i] + $w_cost[$i]);
    }
    $s1_sum_all = ($depre_sum + $lease_sum + $repair_sum + $w_cost_sum);
    /////////////////////// 間接費の小計 算出
    $s2_sum = array();
    for ($i=0; $i<$rows_act; $i++) {
        $s2_sum[$i] = ($p_cost[$i] + $e_cost[$i] + $other[$i] + $m_cost_all[$i]);
    }
    $s2_sum_all = ($p_cost_sum + $e_cost_sum + $other_sum + $m_cost_sum);
    /////////////////////// 中計 算出
    $m_sum = array();
    for ($i=0; $i<$rows_act; $i++) {
        $m_sum[$i] = $s1_sum[$i] + $s2_sum[$i];
    }
    $m_sum[$i] = $s1_sum_all + $s2_sum_all;
    /////////////////////// 合計 算出
    $t_sum = array();
    $h_cost_tmp = 0;
    for ($i=0; $i <= $rows_act; $i++) {
        if ($i != $rows_act) {
            $t_sum[$i] = $m_sum[$i] - $_SESSION['h_cost'][$i];      // 中計より手作業を引き
            $h_cost_tmp += $_SESSION['h_cost'][$i];
        } else {
            $t_sum[$i] = $m_sum[$i] - $h_cost_tmp;      // 最後は製造合計なので中計より合計手作業を引き
        }
    }
    /////////////////////// 製造合計 機械運転時間 算出
    $ope_time_sum = 0;   // 初期化
    for ($i=0; $i<$rows_act; $i++) {
        $ope_time_sum += $_SESSION['ope_time'][$i];
    }
    /////////////////////// 直接経費による機械賃率 算出
    if ( !isset($labor_rate[0]) ) { /////////////// 照会じゃなければ 小数点２桁が０でも必ず表示させるため
        $labor_rate = array();
        for ($i=0; $i<$rows_act; $i++) {
            if ($_SESSION['ope_time'][$i] > 0)
                $labor_rate[$i] = Uround(($t_sum[$i] / $_SESSION['ope_time'][$i]),2);       // 合計 ÷ 運転時間
        }
    }
    if ($ope_time_sum > 0) {
        $labor_rate[$rows_act] = Uround(($t_sum[$rows_act] / $ope_time_sum),2);     // 合計 ÷ 運転時間(製造合計)
    }
    /////////////////////////////////////////////////// 前期のデータを取得  2003/10/08 offset 1 を追加
    // 2018/06/05 単月処理時、end_ymが無いので取得エラーとなる為、if文を追加
    if ( isset($_POST['kessan']) || isset($_POST['check']) || isset($_POST['insert']) ) {
        for ($i=0; $i<$rows_act; $i++) {
            //$query = "select t_cost,ope_time,labor_rate from machine_labor_rate where settle>=1 and settle<=2 and act_id=" . $act_id[$i] . " order by rate_ym DESC limit 1 offset 1";
            $query = "select t_cost,ope_time,labor_rate from machine_labor_rate where settle>=1 and settle<=2 and act_id=" . $act_id[$i] . " and rate_ym<" . $_SESSION['end_ym'] . " order by rate_ym DESC limit 1 offset 0";
            $res_pre = array();
            if ( ($rows_pre=getResult($query,$res_pre)) >= 1) {
                $pre_t_cost[$i] = $res_pre[0]['t_cost'];
                $pre_ope_time[$i] = $res_pre[0]['ope_time'];
                $pre_rate[$i] = $res_pre[0]['labor_rate'];
            }
        }
    }
}
/////////////////////////////////////////////////// 賃率計算結果の登録
while ( isset($_POST['insert']) ) {
    if (!user_check($uid)) break;
    if (isset($_SESSION['rate_ym'])) $rate_ym = $_SESSION['rate_ym']; else $rate_ym = '';
    if (isset($_SESSION['end_ym'])) $end_ym = $_SESSION['end_ym']; else $end_ym = '';
    if ( isset($_SESSION['tangetu']) ) {
        $query = "insert into machine_labor_rate (rate_ym,str_ym,settle,reg_date,m_id,depre,lease,repair,w_cost,p_cost,e_cost,
            other,m_cost,h_cost,t_cost,man,ope_time,labor_rate,std_rate,act_id,s_name) values($rate_ym,$rate_ym,0,'$today',1,";
    } elseif ( isset($_SESSION['kessan']) ) {
        if (isset($_SESSION['str_ym'])) $str_ym = $_SESSION['str_ym']; else $str_ym = '';
        if ($_SESSION['span'] == 1) {//////////////// 中間決算
            $query = "insert into machine_labor_rate (rate_ym,str_ym,settle,reg_date,m_id,depre,lease,repair,w_cost,p_cost,e_cost,
                other,m_cost,h_cost,t_cost,man,ope_time,labor_rate,std_rate,act_id,s_name) values($end_ym,$str_ym,1,'$today',1,";
        } elseif ($_SESSION['span'] == 2) {/////////// 期末決算
            $query = "insert into machine_labor_rate (rate_ym,str_ym,settle,reg_date,m_id,depre,lease,repair,w_cost,p_cost,e_cost,
                other,m_cost,h_cost,t_cost,man,ope_time,labor_rate,std_rate,act_id,s_name) values($end_ym,$str_ym,2,'$today',1,";
        } else {////////////////////////////////////// ランダム(年間・四半期など)
            $query = "insert into machine_labor_rate (rate_ym,str_ym,settle,reg_date,m_id,depre,lease,repair,w_cost,p_cost,e_cost,
                other,m_cost,h_cost,t_cost,man,ope_time,labor_rate,std_rate,act_id,s_name) values($end_ym,$str_ym,3,'$today',1,";
        }
    }
    $res_reg = array();
    //////////////////////////////////// クエリー文の 0 チェック
    for ($i=0; $i<$rows_act; $i++) {
        if ($depre[$i] == 0) {
            $query2 = "0,";
        } else {
            $query2 = $depre[$i] . ",";
        }
        if ($lease[$i] == 0) {
            $query2 .= "0,";
        } else {
            $query2 .= $lease[$i] . ",";
        }
        if ($repair[$i] == 0) {
            $query2 .= "0,";
        } else {
            $query2 .= $repair[$i] . ",";
        }
        if ($w_cost[$i] == 0) {
            $query2 .= "0,";
        } else {
            $query2 .= $w_cost[$i] . ",";
        }
        if ($p_cost[$i] == 0) {
            $query2 .= "0,";
        } else {
            $query2 .= $p_cost[$i] . ",";
        }
        if ($e_cost[$i] == 0) {
            $query2 .= "0,";
        } else {
            $query2 .= $e_cost[$i] . ",";
        }
        if ($other[$i] == 0) {
            $query2 .= "0,";
        } else {
            $query2 .= $other[$i] . ",";
        }
        if ($m_cost_all[$i] == 0) {
            $query2 .= "0,";
        } else {
            $query2 .= $m_cost_all[$i] . ",";
        }
        if ($_SESSION['h_cost'][$i] == 0) {
            $query2 .= "0,";
        } else {
            $query2 .= $_SESSION['h_cost'][$i] . ",";
        }
        if ($t_sum[$i] == 0) {
            $query2 .= "0,";
        } else {
            $query2 .= $t_sum[$i] . ",";
        }
        if ($man[$i] == 0) {
            $query2 .= "0,";
        } else {
            $query2 .= $man[$i] . ",";
        }
        if ($_SESSION['ope_time'][$i] == 0) {
            $query2 .= "0,";
        } else {
            $query2 .= $_SESSION['ope_time'][$i] . ",";
        }
        if ($labor_rate[$i] == 0) {
            $query2 .= "0,NULL," . $act_id[$i] . ",'" . $b_name[$i] . "')";
        } else {
            $query2 .= $labor_rate[$i] . ",NULL," . $act_id[$i] . ",'" . $b_name[$i] . "')";
        }
        $query3 = ($query . $query2);
        if ( ($rows_reg=getResult($query3,$res_reg)) >= 0) {
            $_SESSION['s_sysmsg'] .= $act_id[$i] . " : を登録しました。<BR>";
        }
    }
    ////////////////// 管理部門の登録
    for ($a=0; $a<$rows_tbl; $a++) {        // 管理部門コードが複数ある場合に対応
        if ( isset($_SESSION['tangetu']) ) {
            $query = "insert into machine_labor_rate (rate_ym,str_ym,settle,reg_date,m_id,act_id,man,s_name) values ($rate_ym,$rate_ym,0,'$today',2," . $res_tbl[$a]['act_id'] . ",$m_man_sum,'" . trim($res_tbl[$a]['s_name']) . "')";
        } elseif ( isset($_SESSION['kessan']) ) {
            if ($_SESSION['span'] == 1) {//////////////// 中間決算
                $query = "insert into machine_labor_rate (rate_ym,str_ym,settle,reg_date,m_id,act_id,man,s_name) values ($end_ym,$str_ym,1,'$today',2," . $res_tbl[$a]['act_id'] . ",$m_man_sum,'" . trim($res_tbl[$a]['s_name']) . "')";
            } elseif ($_SESSION['span'] == 2) {/////////// 期末決算
                $query = "insert into machine_labor_rate (rate_ym,str_ym,settle,reg_date,m_id,act_id,man,s_name) values ($end_ym,$str_ym,2,'$today',2," . $res_tbl[$a]['act_id'] . ",$m_man_sum,'" . trim($res_tbl[$a]['s_name']) . "')";
            } else {////////////////////////////////////// ランダム(年間・四半期など)
                $query = "insert into machine_labor_rate (rate_ym,str_ym,settle,reg_date,m_id,act_id,man,s_name) values ($end_ym,$str_ym,3,'$today',2," . $res_tbl[$a]['act_id'] . ",$m_man_sum,'" . trim($res_tbl[$a]['s_name']) . "')";
            }
        }
        if ( ($rows_reg=getResult($query,$res_reg)) >= 0) {
            $_SESSION['s_sysmsg'] .= "<BR>" . $res_tbl[$a]['act_id'] . " : 管理部門を登録";
        }
    }
    unset($_SESSION['h_cost']);
    unset($_SESSION['ope_time']);   // 手入力のデータを削除(次のために)
    break;
}

///// TNK 共用メニュークラスのインスタンスを作成
$menu = new MenuHeader(0);                  // 認証チェック0=一般以上 戻り先=TOP_MENU タイトル未設定
    // 実際の認証はprofit_loss_submit.phpで行っているaccount_group_check()を使用

////////////// サイト設定
$menu->set_site(10, 3);                     // site_index=10(損益メニュー) site_id=7(機械賃率の照会・登録)
//////////// タイトル名(ソースのタイトル名とフォームのタイトル名)
$menu->set_title('機械賃率計算表の作成・照会');

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

<script type='text/javascript' language='JavaScript' src='machine_labor_rate_mnt.js'></script>

<style type='text/css'>
<!--
body {
    font-size:9.0pt;
    margin:0%;
}
th {
    font-size:11.0pt;
}
td {
    font-size:9.0pt;
}
.title-font {
    font:bold 13.5pt;
    font-family: monospace;
    border-top:1.0pt solid windowtext;
    border-right:none;
    border-bottom:1.0pt solid windowtext;
    border-left:0.5pt solid windowtext;
}
.today-font {
    font-size: 10.5pt;
    font-family: monospace;
    border-top:1.0pt solid windowtext;
    border-right:1.0pt solid windowtext;
    border-bottom:1.0pt solid windowtext;
    border-left:0.5pt solid windowtext;
}
select          {background-color:teal;
                color:white;}
textarea        {background-color:black;
                color:white;}
input.sousin    {background-color:red;}
input.text      {background-color:black;
                color:white;}
.right          {text-align:right;}
.center         {text-align:center;}
.left           {text-align:left;}
.pt10           {font-size:10pt;}
.pt10b          {font-size:10pt;
                font:bold;}
.pt11           {font-size:11pt;}
.pt11b          {font-size:11pt;
                font:bold;}
.pt12b          {font-size:12pt;
                font:bold;}
.fc_red         {color:red;}
.fc_blue        {color:blue;}
.margin1        {margin:1%;}
-->
</style>
</head>
<body style='overflow-y:hidden;' onLoad='document.ini_form.rate_ym.focus()'>
    <center>
<?= $menu->out_title_border() ?>
        <div class='pt10'>作成する場合はシステム管理メニューの月次処理で製造経費の対象月の取込みを行った後、実行する。</div>
        <table bgcolor='#d6d3ce' cellspacing='0' cellpadding='3' border='1'>
            <form name='ini_form' action='<?=$menu->out_self()?>' method='post' onSubmit='return ym_chk(this)'>
                <tr>
                    <td colspan='2' align='right' valign='middle' class='pt11'>
                        対象年月を指定して下さい。例：200204 (2002年04月)
                        <input type='text' name='rate_ym' size='7' value='<?php echo $rate_ym ?>' maxlength='6'>
                    </td>
                    <td align='left'>
                        <input class='pt11b' type='submit' name='tangetu' value='単月処理'>
                    </td>
                </tr>
            </form>
            <form action='machine_labor_rate_mnt.php' method='post' onSubmit='return kessan_chk(this)'>
                <tr>
                    <td align='left' class='pt11'>
                        対象年月 範囲を指定して下さい。
                        <input type='text' name='str_ym' size='7' value='<?php echo($_SESSION['str_ym']); ?>' maxlength='6'>
                        〜
                        <input type='text' name='end_ym' size='7' value='<?php echo($_SESSION['end_ym']); ?>' maxlength='6'>
                    </td>
                    <td align='left' class='pt11'>
                        <label for='1'>中間</label><input type='radio' name='span' value='1' id='1'<?php if($_SESSION['span']==1)echo ' checked' ?>>
                        <label for='2'>期末</label><input type='radio' name='span' value='2' id='2'<?php if($_SESSION['span']==2)echo ' checked' ?>>
                        <label for='3'>自由</label><input type='radio' name='span' value='3' id='3'<?php if($_SESSION['span']==3)echo ' checked' ?>>
                    </td>
                    <td align='left'>
                        <input class='pt11b' type='submit' name='kessan' value='決算処理'>
                    </td>
                </tr>
            </form>
        </table>
    <?php
    if(isset($_POST['insert'])){
        echo "<hr>\n";
        if (user_check($uid)) {
            echo "<br><font class='pt12b fc_blue'>登録しました。</font>\n";
        } else {
            echo "<br><font class='pt12b fc_red'>登録出来ませんでした。</font>\n";
        }
    }
    else if(isset($_POST['tangetu']) || isset($_POST['kessan']) || isset($_POST['check'])){
    ?>
        <hr>
        <table bgcolor='#d6d3ce' cellspacing='0' cellpadding='3' border='1'>
            <caption class='pt12b'>機械賃率計算表</caption>
            <?php
            if($register == "登録")
                echo "<th colspan='3' class='fc_red'>$register</th>\n";
            else
                echo "<th colspan='3' class='fc_blue'>$register</th>\n";
            for($i=0;$i<$rows_act;$i++){
                echo "<th>" . $b_name[$i] . "(" . $act_id[$i] . ")</th>\n";
            }
            ?>
            <th nowrap>製造合計</th>
            <tr>
                <td rowspan='10' width='10'>直接部門費</td>
                <td rowspan='4' width='10'>直接費</td>
                <td nowrap>減価償却費</td>
                <?php
                for($i=0;$i<$rows_act;$i++){
                    if($depre[$i] == "")
                        echo "<td nowrap align='right'>0</td>\n";
                    else
                        echo "<td nowrap align='right'>" . number_format($depre[$i]) . "</td>\n";
                }
                echo "<td nowrap align='right'>" . number_format($depre_sum) . "</td>\n";
                ?>
            </tr>
            <tr>
                <td>リース料</td>
                <?php
                for($i=0;$i<$rows_act;$i++){
                    if($lease[$i] == "")
                        echo "<td nowrap align='right'>0</td>\n";
                    else
                        echo "<td nowrap align='right'>" . number_format($lease[$i]) . "</td>\n";
                }
                echo "<td nowrap align='right'>" . number_format($lease_sum) . "</td>\n";
                ?>
            </tr>
            <tr>
                <td>修繕費</td>
                <?php
                for($i=0;$i<$rows_act;$i++){
                    if($repair[$i] == "")
                        echo "<td nowrap align='right'>0</td>\n";
                    else
                        echo "<td nowrap align='right'>" . number_format($repair[$i]) . "</td>\n";
                }
                echo "<td nowrap align='right'>" . number_format($repair_sum) . "</td>\n";
                ?>
            </tr>
            <tr>
                <td>工場消耗品費</td>
                <?php
                for($i=0;$i<$rows_act;$i++){
                    if($w_cost[$i] == "")
                        echo "<td nowrap align='right'>0</td>\n";
                    else
                        echo "<td nowrap align='right'>" . number_format($w_cost[$i]) . "</td>\n";
                }
                echo "<td nowrap align='right'>" . number_format($w_cost_sum) . "</td>\n";
                ?>
            </tr>
            <tr>
                <td colspan='2' align='center'>小計</td>
                <?php
                for($i=0;$i<$rows_act;$i++){
                    if($s1_sum[$i] == "")
                        echo "<td nowrap align='right'>0</td>\n";
                    else
                        echo "<td nowrap align='right'>" . number_format($s1_sum[$i]) . "</td>\n";
                }
                echo "<td nowrap align='right'>" . number_format($s1_sum_all) . "</td>\n";
                ?>
            </tr>
            <tr>
                <td rowspan='4' width='10'>間接費</td>
                <td>人件費</td>
                <?php
                for($i=0;$i<$rows_act;$i++){
                    if($p_cost[$i] == "")
                        echo "<td nowrap align='right'>0</td>\n";
                    else
                        echo "<td nowrap align='right'>" . number_format($p_cost[$i]) . "</td>\n";
                }
                echo "<td nowrap align='right'>" . number_format($p_cost_sum) . "</td>\n";
                ?>
            </tr>
            <tr>
                <td>電力料</td>
                <?php
                for($i=0;$i<$rows_act;$i++){
                    if($e_cost[$i] == "")
                        echo "<td nowrap align='right'>0</td>\n";
                    else
                        echo "<td nowrap align='right'>" . number_format($e_cost[$i]) . "</td>\n";
                }
                echo "<td nowrap align='right'>" . number_format($e_cost_sum) . "</td>\n";
                ?>
            </tr>
            <tr>
                <td>その他</td>
                <?php
                for($i=0;$i<$rows_act;$i++){
                    if($other[$i] == "")
                        echo "<td nowrap align='right'>0</td>\n";
                    else
                        echo "<td nowrap align='right'>" . number_format($other[$i]) . "</td>\n";
                }
                echo "<td nowrap align='right'>" . number_format($other_sum) . "</td>\n";
                ?>
            </tr>
            <tr>
                <td>管理経費配賦</td>
                <?php
                for($i=0;$i<$rows_act;$i++){
                    if($m_cost_all[$i] == "")
                        echo "<td nowrap align='right'>0</td>\n";
                    else
                        echo "<td nowrap align='right'>" . number_format($m_cost_all[$i]) . "</td>\n";
                }
                echo "<td nowrap align='right'>" . number_format($m_cost_sum) . "</td>\n";
                ?>
            </tr>
            <tr>
                <td colspan='2' align='center'>小計</td>
                <?php
                for($i=0;$i<$rows_act;$i++){
                    if($s2_sum[$i] == "")
                        echo "<td nowrap align='right'>0</td>\n";
                    else
                        echo "<td nowrap align='right'>" . number_format($s2_sum[$i]) . "</td>\n";
                }
                echo "<td nowrap align='right'>" . number_format($s2_sum_all) . "</td>\n";
                ?>
            </tr>
            <tr>
                <td colspan='3' align='center'>中計</td>
                <?php
                for($i=0;$i<=$rows_act;$i++){
                    if($m_sum[$i] == "")
                        echo "<td nowrap align='right'>0</td>\n";
                    else
                        echo "<td nowrap align='right'>" . number_format($m_sum[$i]) . "</td>\n";
                }
                ?>
            </tr>
            <?php
            if((!isset($_POST['check'])) && $register <> "照会")
                echo "<form action='machine_labor_rate_mnt.php' method='post'>\n";
            ?>
            <tr>
                <td colspan='3' align='center'>手作業経費を除く</td>
                <?php
                if(isset($_POST['check']) || $register == "照会"){
                    $h_cost_sum = 0;    // 初期化
                    for($i=0;$i<$rows_act;$i++){
                        if($_SESSION['h_cost'][$i] > 0)
                            echo "<td nowrap align='right'>△" . number_format($_SESSION['h_cost'][$i]) . "</td>\n";
                        else
                            echo "<td nowrap align='right'>" . number_format($_SESSION['h_cost'][$i]) . "</td>\n";
                        $h_cost_sum += $_SESSION['h_cost'][$i];
                    }
                    if($h_cost_sum > 0)
                        echo "<td nowrap align='right'>△" . number_format($h_cost_sum) . "</td>\n";
                    else
                        echo "<td nowrap align='right'>" . number_format($h_cost_sum) . "</td>\n";
                }else{
                    for($i=0;$i<$rows_act;$i++){
                        echo "<td nowrap align='right'><input type='text' class='right' name='h_cost[]' size='9' value='" . $_SESSION['h_cost'][$i] . "' maxlength='8'></td>\n";
                    }
                    echo "<td nowrap align='center'><input type='submit' name='check' value='確認'></td>\n";
                }
                ?>
            </tr>
            <tr>
                <td colspan='3' align='center'>合計</td>
                <?php
                for($i=0;$i<=$rows_act;$i++){
                    if($t_sum[$i] == "")
                        echo "<td nowrap align='right'>0</td>\n";
                    else
                        echo "<td nowrap align='right'>" . number_format($t_sum[$i]) . "</td>\n";
                }
                ?>
            </tr>
            <tr>
                <td colspan='3' align='center'>所属人員</td>
                <?php
                for($i=0;$i<$rows_act;$i++){
                    if($man[$i] == "")
                        echo "<td nowrap align='right'>0</td>\n";
                    else
                        echo "<td nowrap align='right'>" . number_format($man[$i]) . "</td>\n";
                }
                echo "<td nowrap align='right'>" . number_format($man_sum) . "</td>\n";
                ?>
            </tr>
            <tr>
                <td colspan='3' align='center'>段取含む機械運転時間</td>
                <?php
                if(isset($_POST['check']) || $register == "照会"){
                    for($i=0;$i<$rows_act;$i++){
                        echo "<td nowrap align='right'>" . number_format($_SESSION['ope_time'][$i]) . "</td>\n";
                    }
                    echo "<td nowrap align='right'>" . number_format($ope_time_sum) . "</td>\n";
                }else{
                    for($i=0;$i<$rows_act;$i++){
                        echo "<td nowrap align='right'><input type='text' class='right' name='ope_time[]' size='9' value='" . $_SESSION['ope_time'][$i] . "' maxlength='8'></td>\n";
                    }
                    echo "<td nowrap align='center'><input type='submit' name='check' value='確認'></td>\n";
                }
                ?>
            </tr>
            <?php
            if((!isset($_POST['check'])) && $register <> "照会")
                echo "</form>\n";
            ?>
            <tr>
                <td colspan='3' align='center' class='fc_red'>直接経費 機械賃率</td>
                <?php
                for($i=0;$i<$rows_act;$i++){
                    if($_SESSION['ope_time'][$i] > 0)
                        echo "<td nowrap align='right' class='fc_red'>" . $labor_rate[$i] . "</td>\n";
                    else
                        echo "<td nowrap align='right'>---</td>\n";
                }
                if($labor_rate[$i] > 0)
                    echo "<td nowrap align='right' class='fc_red'>" . $labor_rate[$i] . "</td>\n";
                else
                    echo "<td nowrap align='right'>---</td>\n";
                ?>
            </tr>
            <tr>
                <td colspan='3' align='center'>標　準　賃　率</td>
                <?php
                for($i=0;$i<=$rows_act;$i++){
                    echo "<td nowrap align='right'>---</td>\n";
                }
                ?>
            </tr>
            <tr>
                <td colspan='<?php echo (4+$rows_act) ?>' align='center' bgcolor='white'>前　　期　　実　　績</td>
            </tr>
            <tr>
                <td colspan='3' align='center'>直接経費</td>
                <?php
                for($i=0;$i<$rows_act;$i++){
                    if($pre_t_cost[$i] > 0)
                        echo "<td nowrap align='right'>" . number_format($pre_t_cost[$i]) . "</td>\n";
                    else
                        echo "<td nowrap align='right'>---</td>\n";
                }
                echo "<td nowrap align='right'>---</td>\n";
                ?>
            </tr>
            <tr>
                <td colspan='3' align='center' nowrap>段取含む機械運転時間</td>
                <?php
                for($i=0;$i<$rows_act;$i++){
                    if($pre_ope_time[$i] > 0)
                        echo "<td nowrap align='right'>" . number_format($pre_ope_time[$i]) . "</td>\n";
                    else
                        echo "<td nowrap align='right'>---</td>\n";
                }
                echo "<td nowrap align='right'>---</td>\n";
                ?>
            </tr>
            <tr>
                <td colspan='3' align='center'>直接経費 機械賃率A</td>
                <?php
                for($i=0;$i<$rows_act;$i++){
                    if($pre_rate[$i] > 0)
                        echo "<td nowrap align='right'>" . $pre_rate[$i] . "</td>\n";
                    else
                        echo "<td nowrap align='right'>---</td>\n";
                }
                echo "<td nowrap align='right'>---</td>\n";
                ?>
            </tr>
        </table>
    <?php
    }
    if(isset($_POST['check'])){
        echo "<form action='machine_labor_rate_mnt.php' method='post'>\n";
        echo "<td nowrap align='center'><input type='submit' name='insert' value='登録' class='fc_red'></td>\n";
        echo "</form>\n";
    }
    ?>
    </center>
</body>
</html>
