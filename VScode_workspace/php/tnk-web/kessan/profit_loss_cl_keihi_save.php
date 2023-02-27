<?php
//////////////////////////////////////////////////////////////////////////////
// 月次損益関係 経費配賦実行 ＣＬ別経費実績内訳表 配賦金額保存              //
// Copyright(C) 2003-2018 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp       //
// 変更経歴                                                                 //
// 2003/01/29 新規作成  profit_loss_cl_keihi_save.php                       //
// 2003/01/30 明細フィールドのデータ計算が終了してから単位調整に変更        //
// 2003/02/07 一度実行したデータは不変の値としてファイルに保存するため      //
//            profit_loss_cl_keihi → profit_loss_cl_keihi_save へ変更      //
// 2003/02/12 配賦率計算が実行されていない場合にメッセージを出して終了      //
// 2003/03/04 データ更新をトランザクションに変更 (データの保証)             //
// 2004/05/06 外形標準課税の対応のため事業等の科目追加(7520)B36 $r=35       //
//            kin1=直接費カプラ kin2=直接費リニア kin3=間接費 kin4=販管費   //
//            $actcod=arry(,7520)配列のお尻に追加に注意する事               //
// 2009/06/10 技術部：501部門の経費配賦を追加                          大谷 //
// 2009/08/07 物流損益追加の為、580部門の製造経費と670部門の販管費を        //
//            強制的にカプラに振分ける暫定対応                         大谷 //
//              →商品別損益はカプラから物流を引いて表示するよう対応   大谷 //
// 2009/08/19 物流を検索する際科目9999がactcod[]に入っていなかった為        //
//            エラーになってしまうのを修正                             大谷 //
// 2011/08/04 間接経費の合計と製造経費の合計の計算方法を変更           大谷 //
// 2015/02/20 クレーム対応費の科目追加(7550)B37 $r=36                       //
//            kin1=直接費カプラ kin2=直接費リニア kin3=間接費 kin4=販管費   //
//            $actcod=arry(,7550)配列のお尻に追加に注意する事               //
//            $rec_keihi = 27→28へ変更 (クレーム対応費追加による)          //
// 2016/10/04 生産管理課：545部門の経費配賦を追加                      大谷 //
// 2018/10/10 2018/09 固定資産除却訂正 すべてカプラの販管費へ          大谷 //
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
$actcod_nkb  = array(8101,8102,8103,8104,8105,8106,8121,8123,7501,7502,7503,7504,7505,7506,7508,7509,7510,7512,7521,7522,7523,7524,7525,7526,7527,7528,7530,7531,7532,7533,7536,7537,7538,7540,8000,7520,7550,9999);

/////////// begin トランザクション開始
if ($con = db_connect()) {
    query_affected_trans($con, "begin");
} else {
    $_SESSION["s_sysmsg"] .= "データベースに接続できません";
    header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
    exit();
}
////// データベースよりデータ取り込み
$res = array();     /*** 当月のデータ取得 ***/  // kin1=直接費カプラ kin2=直接費リニア kin3=間接費 kin4=販管費
$query = sprintf("select kin1,kin2,kin3,kin4 from pl_bs_summary where pl_bs_ym=%d and t_id='B' order by t_id, t_row ASC", $yyyymm);
if (($rows=getResult($query,$res)) > 0) {     ///// データ無しのチェック 優先順位の括弧に注意
    for ($i=0; $i<$rows; $i++) {
        /*
        if ($res[$i][0] != 0)
            $res[$i][0] = ($res[$i][0] / $tani);
        if ($res[$i][1] != 0)
            $res[$i][1] = ($res[$i][1] / $tani);
        if ($res[$i][2] != 0)
            $res[$i][2] = ($res[$i][2] / $tani);
        if ($res[$i][3] != 0)
            $res[$i][3] = ($res[$i][3] / $tani);
        */
    }
    ///// 直接費 給料比率のデータ取得
    $res_jin = array();
    $query = sprintf("select allo from act_allo_history where pl_bs_ym=%d and note='カプラ給料比率'", $yyyymm);
    if ((getResult($query,$res_jin)) > 0) {
        $allo_c_kyu = $res_jin[0][0];
    } else {
        $allo_c_kyu = 0;
        $_SESSION['s_sysmsg'] .= "経費配賦率計算が実行されていません。<br>";
        header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
        exit();
    }
    ///// 直接費 経費合計比率のデータ取得
    $res_kei = array();
    $query = sprintf("select allo from act_allo_history where pl_bs_ym=%d and note='カプラ経費比率'", $yyyymm);
    if ((getResult($query,$res_kei)) > 0) {
        $allo_c_kei = $res_kei[0][0];
    } else {
        $allo_c_kei = 0;
        $_SESSION['s_sysmsg'] .= "経費配賦率計算が実行されていません。<br>";
        header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
        exit();
    }
    ///// 面積比率のデータ取得
    $res_kei = array();
    $query = sprintf("select allo from act_allo_history where pl_bs_ym=%d and note='カプラ面積比率'", $yyyymm);
    if ((getResult($query,$res_kei)) > 0) {
        $allo_c_men = $res_kei[0][0];
    } else {
        $allo_c_men = 0;
        $_SESSION['s_sysmsg'] .= "経費配賦率計算が実行されていません。<br>";
        header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
        exit();
    }
    ///// 直接費 工場消耗品比率のデータ取得
    $res_kei = array();
    $query = sprintf("select allo from act_allo_history where pl_bs_ym=%d and note='カプラ消耗品比率'", $yyyymm);
    if ((getResult($query,$res_kei)) > 0) {
        $allo_c_shou = $res_kei[0][0];
    } else {
        $allo_c_shou = 0;
        $_SESSION['s_sysmsg'] .= "経費配賦率計算が実行されていません。<br>";
        query_affected_trans($con, "rollback");     // transaction rollback
        header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
        exit();
    }
    
    ///// 人件費と経費の明細部
    $data      = array();       // 計算用変数 配列で初期化
    $view_data = array();       // 表示用変数 配列で初期化
    for ($r=0; $r<$rows; $r++) {
        for ($c=0; $c<$f_mei; $c++) {
            switch ($c) {
                case  0:        // 製造経費合計のカプラ
                    if ($r >= 0 && $r < $rec_jin) {     // 人件費の間接費は部門毎に違う比率
                        $res_jin = array();
                        $query = sprintf("select sum(dest_kin) from act_allo_history where pl_bs_ym=%d and actcod=%d and (orign_id=173 or orign_id=174 or orign_id=500 or orign_id=501 or orign_id=545) and k_kubun='1' and div=' ' and dest_id=1", $yyyymm, $jin_act[$r]);
                        if ((getResult($query,$res_jin)) > 0) {
                            $data[$r][6] = $res_jin[0][0];                  // 間接費 カプラ
                        }
                        $res_580 = array();
                        $query = sprintf("select sum(dest_kin) from act_allo_history where pl_bs_ym=%d and actcod=%d and orign_id=580 and k_kubun='1' and div=' ' and dest_id=1", $yyyymm, $actcod[$r]);
                        if ((getResult($query,$res_580)) > 0) {
                            $data[$r][6] = $data[$r][6] + $res_580[0][0];   // 580 間接費 カプラ 加算
                        }
                        // $data[$r][6] = Uround($res[$r][2] * 0.65);       // 間接費 カプラ
                        $data[$r][$c] = $res[$r][0] + $data[$r][6];
                    } elseif (($r == 17) || ($r == 33) || ($r == 34)) {     // 業務委託費 7512 賃借料 7540 減価償却費 8000 ＣＬ面積比率
                        $res_580 = array();
                        $query = sprintf("select sum(dest_kin) from act_allo_history where pl_bs_ym=%d and actcod=%d and orign_id=580 and k_kubun='1' and div=' ' and dest_id=1", $yyyymm, $actcod[$r]);
                        if ((getResult($query,$res_580)) > 0) {
                            $res[$r][2] = $res[$r][2] - $res_580[0][0];     // 580 間接費 カプラ 減算
                        }
                        $data[$r][6] = Uround($res[$r][2] * $allo_c_men);   // 間接費 カプラ
                        if ((getResult($query,$res_580)) > 0) {
                            $data[$r][6] = $data[$r][6] + $res_580[0][0];   // 580 間接費 カプラ 加算
                        }
                        $data[$r][$c] = $res[$r][0] + $data[$r][6];
                        if ((getResult($query,$res_580)) > 0) {
                            $res[$r][2] = $res[$r][2] + $res_580[0][0];         // 間接費計 580 戻し
                        }
                    } elseif ($r == 24) {                                   // 工場消耗品費 7527
                        $res_580 = array();
                        $query = sprintf("select sum(dest_kin) from act_allo_history where pl_bs_ym=%d and actcod=%d and orign_id=580 and k_kubun='1' and div=' ' and dest_id=1", $yyyymm, $actcod[$r]);
                        if ((getResult($query,$res_580)) > 0) {
                            $res[$r][2] = $res[$r][2] - $res_580[0][0];     // 580 間接費 カプラ 減算
                        }
                        $data[$r][6] = Uround($res[$r][2] * $allo_c_shou);  // 間接費 カプラ
                        if ((getResult($query,$res_580)) > 0) {
                            $data[$r][6] = $data[$r][6] + $res_580[0][0];   // 580 間接費 カプラ 加算
                        }
                        $data[$r][$c] = $res[$r][0] + $data[$r][6];
                        if ((getResult($query,$res_580)) > 0) {
                            $res[$r][2] = $res[$r][2] + $res_580[0][0];     // 間接費計 580 戻し
                        }
                    } else {                            // 経費はＣＬ直接費 経費合計比率
                        // $data[$r][6] = Uround($res[$r][2] * 0.853);      // 間接費 カプラ
                        $res_580 = array();
                        $query = sprintf("select sum(dest_kin) from act_allo_history where pl_bs_ym=%d and actcod=%d and orign_id=580 and k_kubun='1' and div=' ' and dest_id=1", $yyyymm, $actcod_nkb[$r]);
                        if ((getResult($query,$res_580)) > 0) {
                            $res[$r][2] = $res[$r][2] - $res_580[0][0];     // 580 間接費 カプラ 減算
                        }
                        $data[$r][6] = Uround($res[$r][2] * $allo_c_kei);   // 間接費 カプラ
                        if ((getResult($query,$res_580)) > 0) {
                            $data[$r][6] = $data[$r][6] + $res_580[0][0];   // 580 間接費 カプラ 加算
                        }
                        $data[$r][$c] = $res[$r][0] + $data[$r][6];
                        if ((getResult($query,$res_580)) > 0) {
                            $res[$r][2] = $res[$r][2] + $res_580[0][0];     // 間接費計 580 戻し
                        }
                    }
                    break;
                case  1:        // 製造経費合計のリニア
                    if ($r >= 0 && $r < $rec_jin) {     // 人件費は部門毎に違う比率
                        $res_jin = array();
                        $query = sprintf("select sum(dest_kin) from act_allo_history where pl_bs_ym=%d and actcod=%d and (orign_id=173 or orign_id=174 or orign_id=500 or orign_id=501 or orign_id=545) and k_kubun='1' and div=' ' and dest_id=2", $yyyymm, $jin_act[$r]);
                        if ((getResult($query,$res_jin)) > 0) {
                            $data[$r][7] = $res_jin[0][0];
                        }
                        // $data[$r][7] = ($res[$r][2] - (Uround($res[$r][2] * 0.65)));    // 間接費 リニア
                        $data[$r][$c] = $res[$r][1] + $data[$r][7];
                    } elseif (($r == 17) || ($r == 33) || ($r == 34)) {     // 業務委託費 7512 賃借料 7540 減価償却費 8000 ＣＬ面積比率
                        $res_580 = array();
                        $query = sprintf("select sum(dest_kin) from act_allo_history where pl_bs_ym=%d and actcod=%d and orign_id=580 and k_kubun='1' and div=' ' and dest_id=1", $yyyymm, $actcod[$r]);
                        if ((getResult($query,$res_580)) > 0) {
                            $res[$r][2] = $res[$r][2] - $res_580[0][0];     // 580 間接費 カプラ 減算
                        }
                        $data[$r][7] = ($res[$r][2] - (Uround($res[$r][2] * $allo_c_men)));  // 間接費 リニア
                        $data[$r][$c] = $res[$r][1] + $data[$r][7];
                        if ((getResult($query,$res_580)) > 0) {
                            $res[$r][2] = $res[$r][2] + $res_580[0][0];     // 間接費計 580 戻し
                        }
                    } elseif ($r == 24) {                                   // 工場消耗品費 7527
                        $res_580 = array();
                        $query = sprintf("select sum(dest_kin) from act_allo_history where pl_bs_ym=%d and actcod=%d and orign_id=580 and k_kubun='1' and div=' ' and dest_id=1", $yyyymm, $actcod[$r]);
                        if ((getResult($query,$res_580)) > 0) {
                            $res[$r][2] = $res[$r][2] - $res_580[0][0];     // 580 間接費 カプラ 減算
                        }
                        $data[$r][7] = ($res[$r][2] - (Uround($res[$r][2] * $allo_c_shou))); // 間接費 リニア
                        $data[$r][$c] = $res[$r][1] + $data[$r][7];
                        if ((getResult($query,$res_580)) > 0) {
                            $res[$r][2] = $res[$r][2] + $res_580[0][0];     // 間接費計 580 戻し
                        }
                    } else {                            // 経費はＣＬ直接費 経費合計比率
                        // $data[$r][7] = ($res[$r][2] - (Uround($res[$r][2] * 0.853)));    // 間接費 リニア
                                                        // カプラ分を引いた残りがリニア
                        $res_580 = array();
                        $query = sprintf("select sum(dest_kin) from act_allo_history where pl_bs_ym=%d and actcod=%d and orign_id=580 and k_kubun='1' and div=' ' and dest_id=1", $yyyymm, $actcod_nkb[$r]);
                        if ((getResult($query,$res_580)) > 0) {
                            $res[$r][2] = $res[$r][2] - $res_580[0][0];     // 580 間接費 カプラ 減算
                        }
                        $data[$r][7] = ($res[$r][2] - (Uround($res[$r][2] * $allo_c_kei))); // 間接費 リニア
                        $data[$r][$c] = $res[$r][1] + $data[$r][7];
                        if ((getResult($query,$res_580)) > 0) {
                            $res[$r][2] = $res[$r][2] + $res_580[0][0];     // 間接費計 580 戻し
                        }
                    }
                    break;
                case  2:        // 製造経費の合計
                    $data[$r][$c] = $data[$r][0] + $data[$r][1];
                    break;
                case  3:        // 直接経費 カプラ
                    $data[$r][$c] = $res[$r][0];
                    break;
                case  4:        // 直接経費 リニア
                    $data[$r][$c] = $res[$r][1];
                    break;
                case  5:        // 直接経費 合計
                    $data[$r][$c] = $data[$r][3] + $data[$r][4];
                    break;
                case  6:        // 間接経費 カプラ
                    // case 0 で計算
                    break;
                case  7:        // 間接経費 リニア
                    // case 1 で計算
                    break;
                case  8:        // 間接経費 合計
                    //$data[$r][$c] = $res[$r][2];
                    // 2011/08/04 修正
                    $data[$r][$c] = $data[$r][6] + $data[$r][7];
                    break;
                case  9:        // 製造経費 合計
                    //$data[$r][$c] = $res[$r][0] + $res[$r][1] + $res[$r][2];
                    // 2011/08/04 修正
                    $data[$r][$c] = $data[$r][3] + $data[$r][4] + $data[$r][6] + $data[$r][7];
                    break;
                case 10:        // 販管費 カプラ
                    if ($r >= 0 && $r < $rec_jin) {     // 人件費はＣＬ直接給料比
                        // $data[$r][$c] = Uround($res[$r][3] * 0.784);
                        $res_670 = array();
                        $query = sprintf("select sum(dest_kin) from act_allo_history where pl_bs_ym=%d and actcod=%d and orign_id=670 and k_kubun=' ' and div=' ' and dest_id=1", $yyyymm, $actcod[$r]);
                        if ((getResult($query,$res_670)) > 0) {
                            $res[$r][3] = $res[$r][3] - $res_670[0][0];     // 670 販管費 カプラ 減算
                        }
                        $data[$r][$c] = Uround($res[$r][3] * $allo_c_kyu);
                        if ((getResult($query,$res_670)) > 0) {
                            $data[$r][$c] = $data[$r][$c] + $res_670[0][0]; // 670 販管費 カプラ 加算
                        }
                        if ((getResult($query,$res_670)) > 0) {
                            $res[$r][3] = $res[$r][3] + $res_670[0][0];     // 670 販管費 カプラ 戻し
                        }
                    } elseif (($r == 17) || ($r == 33) || ($r == 34)) {     // 業務委託費 7512 賃借料 7540 減価償却費 8000 ＣＬ面積比率
                        $res_670 = array();
                        $query = sprintf("select sum(dest_kin) from act_allo_history where pl_bs_ym=%d and actcod=%d and orign_id=670 and k_kubun=' ' and div=' ' and dest_id=1", $yyyymm, $actcod[$r]);
                        if ((getResult($query,$res_670)) > 0) {
                            $res[$r][3] = $res[$r][3] - $res_670[0][0];     // 670 販管費 カプラ 減算
                        }
                        // 2018/09 固定資産除却取消 販管費カプラのみに
                        if ($r == 34) {
                            if ($yyyymm==201809) {
                                $res[$r][3] = $res[$r][3] - 270803;
                            }
                        }
                        $data[$r][$c] = Uround($res[$r][3] * $allo_c_men);
                        if ((getResult($query,$res_670)) > 0) {
                            $data[$r][$c] = $data[$r][$c] + $res_670[0][0]; // 670 販管費 カプラ 加算
                        }
                        // 2018/09 固定資産除却取消 販管費カプラのみに
                        if ($r == 34) {
                            if ($yyyymm==201809) {
                                $data[$r][$c] = $data[$r][$c] + 270803;
                            }
                        }
                        if ((getResult($query,$res_670)) > 0) {
                            $res[$r][3] = $res[$r][3] + $res_670[0][0];     // 670 販管費 カプラ 戻し
                        }
                        // 2018/09 固定資産除却取消 販管費カプラのみに
                        if ($r == 34) {
                            if ($yyyymm==201809) {
                                $res[$r][3] = $res[$r][3] + 270803;
                            }
                        }
                    } elseif ($r == 24) {                                   // 工場消耗品費 7527
                        $res_670 = array();
                        $query = sprintf("select sum(dest_kin) from act_allo_history where pl_bs_ym=%d and actcod=%d and orign_id=670 and k_kubun=' ' and div=' ' and dest_id=1", $yyyymm, $actcod[$r]);
                        if ((getResult($query,$res_670)) > 0) {
                            $res[$r][3] = $res[$r][3] - $res_670[0][0];     // 670 販管費 カプラ 減算
                        }
                        $data[$r][$c] = Uround($res[$r][3] * $allo_c_shou);
                        if ((getResult($query,$res_670)) > 0) {
                            $data[$r][$c] = $data[$r][$c] + $res_670[0][0]; // 670 販管費 カプラ 加算
                        }
                        if ((getResult($query,$res_670)) > 0) {
                            $res[$r][3] = $res[$r][3] + $res_670[0][0];     // 670 販管費 カプラ 戻し
                        }
                    } else {                            // 経費はＣＬ直接費 経費合計比率
                        // $data[$r][$c] = Uround($res[$r][3] * 0.853);
                        $res_670 = array();
                        $query = sprintf("select sum(dest_kin) from act_allo_history where pl_bs_ym=%d and actcod=%d and orign_id=670 and k_kubun=' ' and div=' ' and dest_id=1", $yyyymm, $actcod_nkb[$r]);
                        if ((getResult($query,$res_670)) > 0) {
                            $res[$r][3] = $res[$r][3] - $res_670[0][0];     // 670 販管費 カプラ 減算
                        }
                        $data[$r][$c] = Uround($res[$r][3] * $allo_c_kei);  // 事業等はここに入る予定
                        if ((getResult($query,$res_670)) > 0) {
                            $data[$r][$c] = $data[$r][$c] + $res_670[0][0]; // 670 販管費 カプラ 加算
                        }
                        if ((getResult($query,$res_670)) > 0) {
                            $res[$r][3] = $res[$r][3] + $res_670[0][0];     // 670 販管費 カプラ 戻し
                        }
                    }
                    break;
                case 11:        // 販管費 リニア
                    if ($r >= 0 && $r < $rec_jin) {     // 人件費はＣＬ直接給料比
                        // $data[$r][$c] = ($res[$r][3]-(Uround($res[$r][3] * 0.784)));
                        $res_670 = array();
                        $query = sprintf("select sum(dest_kin) from act_allo_history where pl_bs_ym=%d and actcod=%d and orign_id=670 and k_kubun=' ' and div=' ' and dest_id=1", $yyyymm, $actcod[$r]);
                        if ((getResult($query,$res_670)) > 0) {
                            $res[$r][3] = $res[$r][3] - $res_670[0][0];     // 670 販管費 カプラ 減算
                        }
                        $data[$r][$c] = ($res[$r][3]-(Uround($res[$r][3] * $allo_c_kyu)));    // カプラ分を引いた残りがリニア
                        if ((getResult($query,$res_670)) > 0) {
                            $res[$r][3] = $res[$r][3] + $res_670[0][0];     // 670 販管費 カプラ 戻し
                        }
                    } elseif (($r == 17) || ($r == 33) || ($r == 34)) {     // 業務委託費 7512 賃借料 7540 減価償却費 8000 ＣＬ面積比率
                        $res_670 = array();
                        $query = sprintf("select sum(dest_kin) from act_allo_history where pl_bs_ym=%d and actcod=%d and orign_id=670 and k_kubun=' ' and div=' ' and dest_id=1", $yyyymm, $actcod[$r]);
                        if ((getResult($query,$res_670)) > 0) {
                            $res[$r][3] = $res[$r][3] - $res_670[0][0];     // 670 販管費 カプラ 減算
                        }
                        // 2018/09 固定資産除却取消 販管費カプラのみに
                        if ($r == 34) {
                            if ($yyyymm==201809) {
                                $res[$r][3] = $res[$r][3] - 270803;
                            }
                        }
                        $data[$r][$c] = ($res[$r][3]-(Uround($res[$r][3] * $allo_c_men)));
                        if ((getResult($query,$res_670)) > 0) {
                            $res[$r][3] = $res[$r][3] + $res_670[0][0];     // 670 販管費 カプラ 戻し
                        }
                        // 2018/09 固定資産除却取消 販管費カプラのみに
                        if ($r == 34) {
                            if ($yyyymm==201809) {
                                $res[$r][3] = $res[$r][3] + 270803;
                            }
                        }
                    } elseif ($r == 24) {                                   // 工場消耗品費 7527
                        $res_670 = array();
                        $query = sprintf("select sum(dest_kin) from act_allo_history where pl_bs_ym=%d and actcod=%d and orign_id=670 and k_kubun=' ' and div=' ' and dest_id=1", $yyyymm, $actcod[$r]);
                        if ((getResult($query,$res_670)) > 0) {
                            $res[$r][3] = $res[$r][3] - $res_670[0][0];     // 670 販管費 カプラ 減算
                        }
                        $data[$r][$c] = ($res[$r][3]-(Uround($res[$r][3] * $allo_c_shou)));
                        if ((getResult($query,$res_670)) > 0) {
                            $res[$r][3] = $res[$r][3] + $res_670[0][0];     // 670 販管費 カプラ 戻し
                        }
                    } else {                            // 経費はＣＬ直接費 経費合計比率
                        // $data[$r][$c] = ($res[$r][3]-(Uround($res[$r][3] * 0.853)));
                        $res_670 = array();
                        $query = sprintf("select sum(dest_kin) from act_allo_history where pl_bs_ym=%d and actcod=%d and orign_id=670 and k_kubun=' ' and div=' ' and dest_id=1", $yyyymm, $actcod_nkb[$r]);
                        if ((getResult($query,$res_670)) > 0) {
                            $res[$r][3] = $res[$r][3] - $res_670[0][0];     // 670 販管費 カプラ 減算
                        }
                        $data[$r][$c] = ($res[$r][3]-(Uround($res[$r][3] * $allo_c_kei)));  // 事業等はここに入る予定
                        if ((getResult($query,$res_670)) > 0) {
                            $res[$r][3] = $res[$r][3] + $res_670[0][0];     // 670 販管費 カプラ 戻し
                        }
                    }
                    break;
                case 12:        // 販管費 合計
                    $data[$r][$c] = $res[$r][3];
                    break;
                default:        // その他は無いが
                    $data[$r][$c] = $res[$r][$c];
                    break;
            }
        }
    }
    ///////// 登録済みのチェック
    $res_chk = array();
    $query = sprintf("select pl_bs_ym from act_cl_history where pl_bs_ym=%d", $yyyymm);
    if ((getResult($query,$res_chk)) > 0) {
        $_SESSION["s_sysmsg"] .= sprintf("既に配賦処理 実行済みです<br>第 %d期 %d月",$ki,$tuki);
        // $_SESSION['s_sysmsg'] .= "$query <br>";     // debug
        header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
        exit();
    } else {
        ///////// テーブル保存用データの生成
        $query_ins = "insert into act_cl_history (pl_bs_ym, actcod, kin00, kin01, kin02, kin03, kin04, kin05, 
            kin06, kin07, kin08, kin09, kin10, kin11, kin12) values (%d, %d, %d, %d, %d, %d, %d, %d, %d, %d, %d, %d, %d, %d, %d)";
        $rec = count($actcod);
        for ($r=0; $r < $rec; $r++) {
        // foreach ($actcod as $code) {
            $query = sprintf($query_ins, $yyyymm, $actcod[$r], $data[$r][0], $data[$r][1], $data[$r][2], $data[$r][3], $data[$r][4]
                , $data[$r][5], $data[$r][6], $data[$r][7], $data[$r][8], $data[$r][9], $data[$r][10], $data[$r][11], $data[$r][12]);
            if (query_affected_trans($con, $query) <= 0) {      ///// トランザクション用クエリーの実行
                $_SESSION['s_sysmsg'] .= sprintf("年月=%d:科目=%d の配賦金額の登録に失敗しました<br>", $yyyymm, $actcod[$r]);
                $_SESSION['s_sysmsg'] .= "$query <br>";     // debug
                query_affected_trans($con, "rollback");     // transaction rollback
                header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
                exit();
            }
        }       ///// 最後のレコードは その他の科目 金額があるかチェックのため
        $query = sprintf($query_ins, $yyyymm, 9999, $data[$r][0], $data[$r][1], $data[$r][2], $data[$r][3], $data[$r][4]
            , $data[$r][5], $data[$r][6], $data[$r][7], $data[$r][8], $data[$r][9], $data[$r][10], $data[$r][11], $data[$r][12]);
        if (query_affected_trans($con, $query) <= 0) {      ///// トランザクション用クエリーの実行
            $_SESSION['s_sysmsg'] .= sprintf("年月=%d:科目=%d の配賦金額の登録に失敗しました<br>", $yyyymm, $actcod[$r]);
            $_SESSION['s_sysmsg'] .= "$query <br>";     // debug
            query_affected_trans($con, "rollback");     // transaction rollback
            header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
            exit();
        }
    }
} else {
    $_SESSION["s_sysmsg"] .= sprintf("対象データがありません。<br>第 %d期 %d月",$ki,$tuki);
    header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
    exit();
}

/////////// commit トランザクション終了
query_affected_trans($con, "commit");
$_SESSION["s_sysmsg"] .= sprintf("<font color='yellow'>第%d期 %d月の配賦処理完了</font>",$ki,$tuki);
header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
exit();

