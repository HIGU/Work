<?php
//////////////////////////////////////////////////////////////////////////////
// 商品管理・試験修理の計算データの登録・修正及び照会兼用                   //
// Copyright (C) 2009-2017 Norihisa.Ohya norihisa_ooya@nitto-kohki.co.jp    //
// Changed history                                                          //
// 2009/08/18 Created   profit_loss_nkb_input.php                           //
// 2009/08/19 物流を商品管理に名称変更                                      //
// 2009/10/06 商管の営業外収益が7月しか入力できないようになっていた修正     //
//            200909より商管を売上高直接入力から調整入力に変更              //
// 2009/11/02 10月より商管に間接部門の給与を配賦する処理を追加              //
//            商管への配賦割合は$allo_nkb_kyuを変更すること                 //
//            カプラ・リニアの減算分もここで計算                            //
// 2009/11/09 商管への給与配賦を$allo_nkb_kyuから$allo_nkb_kyu1と2に変更    //
//            09/11/09時点で$allo_nkb_kyu1=0.09 $allo_nkb_kyu2=0.52         //
// 2009/11/10 $allo_nkb_kyu1=0.20 $allo_nkb_kyu2=1.00に変更                 //
// 2009/12/07 試験修理にカプラ分を追加、調整ではなく売上高入力              //
//            売上比率で労務費・経費をCLに按分                              //
// 2009/12/10 コメントの整理                                                //
// 2010/01/27 全てのデータを登録後売上高比を再計算し、営業外の配賦を再度    //
//            実行する（2009/12は計算したが損益上は未適用）                 //
//            いつから実施するかを確認してif分を変更                        //
// 2010/01/28 前半期の売上高比から対象月までの売上高比に変更                //
// 2010/02/01 201001より商管の営業外収益その他を入力できないようにする      //
// 2014/08/06 2014/07より、商品管理課の配賦給与を工場長0.2(20%)→0.05(5%)   //
//            管理部長1.0(100%)→0.5(50%)へ変更(工場長は前期売上割合)       //
// 2014/08/07 商管の配賦給与を元に戻した。                                  //
//            工場長0.05(5%)→0.2(20%)、管理部長0.5(50%)→1.0(100%)         //
// 2016/07/22 修理・耐久の売上高と売上高比を登録                            //
// 2017/07/06 表示を分かりやすく工場長と、管理部長を追加                    //
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
$menu->set_title("第{$ki}期　{$tuki}月度　商品管理・試修 損益の登録");

///// 対象当月
$yyyymm = $_SESSION['pl_ym'];
///// 対象前月
if (substr($yyyymm,4,2)!=01) {
    $p1_ym = $yyyymm - 1;
} else {
    $p1_ym = $yyyymm - 100;
    $p1_ym = $p1_ym + 11;
}
//対象年月日
$ymd_str = $yyyymm . "01";
$ymd_end = $yyyymm . "99";

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
///// 試修売上高のデータ取得
$res_sl = array();
$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='試修売上高'", $yyyymm);
if ((getResult($query,$res_sl)) > 0) {
    $sl_uri = $res_sl[0][0];
} else {
    $sl_uri = 0;
}

///// 商管社員給与按分用割合
///// 1が工場長、2が管理部長
// 2014/08/07 元に戻した。
$allo_nkb_kyu1 = 0.20;
$allo_nkb_kyu2 = 1.00;
// 2014/07 より割合を変更
// $allo_nkb_kyu1 = 0.05;
// $allo_nkb_kyu2 = 0.50;

///////// 項目とインデックスの関連付け
$item = array();
if ($yyyymm >= 200909) {
    $item[0]   = "商管売上調整額";
    $item[1]   = "試修売上調整額";
    $item[2]   = "カプラ試修売上高";
} else {
    $item[0]   = "商管売上高";
    $item[1]   = "試修売上高";
    $item[2]   = "カプラ試修売上高";
}
$item[3]   = "商管期首棚卸高";
$item[4]   = "試修期首棚卸高";
$item[5]   = "商管材料費";
$item[6]   = "試修材料費";
$item[7]   = "カプラ試修材料費";
$item[8]   = "商管労務費";
$item[9]   = "試修労務費";
$item[10]  = "商管製造経費";
$item[11]  = "試修製造経費";
$item[12]  = "商管期末棚卸高";
$item[13]  = "試修期末棚卸高";
$item[14]  = "商管人件費";
$item[15]  = "試修人件費";
$item[16]  = "商管販管費経費";
$item[17]  = "試修販管費経費";
$item[18]  = "商管業務委託収入";
$item[19]  = "試修業務委託収入";
$item[20]  = "商管仕入割引";
$item[21]  = "試修仕入割引";
$item[22]  = "商管営業外収益その他";
$item[23]  = "試修営業外収益その他";
$item[24]  = "商管支払利息";
$item[25]  = "試修支払利息";
$item[26]  = "商管営業外費用その他";
$item[27]  = "試修営業外費用その他";
$item[28]  = "商管社員配布給与１";
$item[29]  = "商管社員配布給与２";
$item[30]  = "商管社員按分給与";
$item[31]  = "カプラ商管社員按分給与";
$item[32]  = "リニア商管社員按分給与";
$item[33]  = "カプラ試修労務費";
$item[34]  = "カプラ試修製造経費";
$item[35]  = "カプラ試修人件費";
$item[36]  = "カプラ試修販管費経費";
$item[37]  = "カプラ試修業務委託収入";
$item[38]  = "カプラ試修仕入割引";
$item[39]  = "カプラ試修営業外収益その他";
$item[40]  = "カプラ試修支払利息";
$item[41]  = "カプラ試修営業外費用その他";
$item[42]  = "リニア試修労務費";
$item[43]  = "リニア試修製造経費";
$item[44]  = "リニア試修人件費";
$item[45]  = "リニア試修販管費経費";
$item[46]  = "リニア試修業務委託収入";
$item[47]  = "リニア試修仕入割引";
$item[48]  = "リニア試修営業外収益その他";
$item[49]  = "リニア試修支払利息";
$item[50]  = "リニア試修営業外費用その他";

///////// 入力text 変数 初期化
$invent = array();
for ($i = 0; $i < 51; $i++) {
    if (isset($_POST['invent'][$i])) {
        $invent[$i] = $_POST['invent'][$i];
    } else {
        $invent[$i] = 0;
    }
}
if (!isset($_POST['entry'])) {     // データ入力
    ////////// 登録済みならば金額取得
    for ($i = 0; $i < 51; $i++) {
        $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='%s'", $yyyymm, $item[$i]);
        $res = array();
        if (getResult2($query,$res) > 0) {
            $invent[$i] = $res[0][0];
        }
    }
} else {    // 登録処理  トランザクションで更新しているためレコード有り無しのチェックのみ
    $invent[30] = Uround(($invent[28] * $allo_nkb_kyu1),0) + Uround(($invent[29] * $allo_nkb_kyu2),0);    // 商管社員按分給与
    $invent[31] = Uround(($invent[30] * $allo_c_kyu),0);    // カプラ商管社員按分給与
    $invent[32] = $invent[30] - $invent[31];                // リニア商管社員按分給与
    // カプラ試験修理の各金額の算出
    if (($sl_uri > 0) && ($invent[2] > 0)) {                // 試験修理の売上とカプラ修理の金額があれば
        $ss_uri   = $sl_uri + $invent[1] + $invent[2];      // 試験修理売上高計（リニア＋リニア調整＋カプラ）
        $sc_allo  = Uround(($invent[2] / $ss_uri),5);       // カプラ試験修理売上高率
        // カプラ
        $invent[33] = Uround(($invent[9] * $sc_allo),0);
        $invent[34] = Uround(($invent[11] * $sc_allo),0);
        $invent[35] = Uround(($invent[15] * $sc_allo),0);
        $invent[36] = Uround(($invent[17] * $sc_allo),0);
        $invent[37] = Uround(($invent[19] * $sc_allo),0);
        $invent[38] = Uround(($invent[21] * $sc_allo),0);
        $invent[39] = Uround(($invent[23] * $sc_allo),0);
        $invent[40] = Uround(($invent[25] * $sc_allo),0);
        $invent[41] = Uround(($invent[27] * $sc_allo),0);
        // リニア
        $invent[42] = $invent[9] - $invent[33];
        $invent[43] = $invent[11] - $invent[34];
        $invent[44] = $invent[15] - $invent[35];
        $invent[45] = $invent[17] - $invent[36];
        $invent[46] = $invent[19] - $invent[37];
        $invent[47] = $invent[21] - $invent[38];
        $invent[48] = $invent[23] - $invent[39];
        $invent[49] = $invent[25] - $invent[40];
        $invent[50] = $invent[27] - $invent[41];
    } else {
        $sc_allo = 0;
    }
    for ($i = 0; $i < 51; $i++) {
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
            if ($i == 2) {  // カプラ試験修理売上高の場合、率を登録
                $query = sprintf("insert into act_pl_history (pl_bs_ym, kin, note, allo) values (%d, %d, '%s', %1.5f)", $yyyymm, $invent[$i], $item[$i], $sc_allo);
            } else {
                $query = sprintf("insert into act_pl_history (pl_bs_ym, kin, note) values (%d, %d, '%s')", $yyyymm, $invent[$i], $item[$i]);
            }
            if (query_affected_trans($con, $query) <= 0) {
                $_SESSION["s_sysmsg"] .= sprintf("%sの新規登録に失敗<br>第 %d期 %d月", $item[$i], $ki, $tuki);
                query_affected_trans($con, "rollback");     // transaction rollback
                header("Location: $current_script");
                exit();
            }
            /////////// commit トランザクション終了
            query_affected_trans($con, "commit");
            $_SESSION["s_sysmsg"] = sprintf("<font color='yellow'>第%d期 %d月 商品管理・試修 損益データ 新規 登録完了</font>",$ki,$tuki);
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
            if ($i == 2) {  // カプラ試験修理売上高の場合、率を登録
                $query = sprintf("update act_pl_history set kin=%d, allo=%1.5f where pl_bs_ym=%d and note='%s'", $invent[$i], $sc_allo, $yyyymm, $item[$i]);
            } else {
                $query = sprintf("update act_pl_history set kin=%d where pl_bs_ym=%d and note='%s'", $invent[$i], $yyyymm, $item[$i]);
            }
            if (query_affected_trans($con, $query) <= 0) {
                $_SESSION["s_sysmsg"] .= sprintf("%sのUPDATEに失敗<br>第 %d期 %d月", $item[$i], $ki, $tuki);
                query_affected_trans($con, "rollback");     // transaction rollback
                header("Location: $current_script");
                exit();
            }
            /////////// commit トランザクション終了
            query_affected_trans($con, "commit");
            $_SESSION["s_sysmsg"] = sprintf("<font color='yellow'>第%d期 %d月 商品管理・試修 損益データ 変更 完了</font>",$ki,$tuki);
        }
    }
    
    $p_other_ctoku = Uround((($c_pother - $invent[22]) * $ctoku_allo),0);    // カプラ特注営業外収益その他
    $query_chk = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='カプラ特注営業外収益その他'", $yyyymm);
    if (getResult($query_chk,$res_chk) <= 0) {  // 既登録済みのチェック
        // 新規登録
        $query = sprintf("insert into act_pl_history (pl_bs_ym, kin, note) values (%d, %d, 'カプラ特注営業外収益その他')", $yyyymm, $p_other_ctoku);
        if (query_affected_trans($con, $query) <= 0) {
            $_SESSION['s_sysmsg'] .= sprintf("カプラ特注営業外収益その他の登録に失敗<br>第 %d期 %d月",$ki,$tuki);
            query_affected_trans($con, "rollback");     // Transaction Rollback
            header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
            exit();
        }
    } else {
        $query = sprintf("update act_pl_history set kin=%d where pl_bs_ym=%d and note='カプラ特注営業外収益その他'", $p_other_ctoku, $yyyymm);
        if (query_affected_trans($con, $query) <= 0) {
            $_SESSION['s_sysmsg'] .= sprintf("カプラ特注営業外収益その他の更新失敗<br>第 %d期 %d月",$ki,$tuki);
            query_affected_trans($con, "rollback");     // Transaction Rollback
            header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
            exit();
        }
    }
    
    // 修理・耐久の売上高と売上高比を計算する。
    $ss_uri = $invent[1] + $invent[2];
    $query = sprintf("select sum(Uround(数量*単価,0)) as t_kingaku from hiuuri where 計上日>=%d and 計上日<=%d and 事業部='L' and (assyno like 'SS%%')", $ymd_str, $ymd_end);
    if (getUniResult($query, $st_uri) < 1) {
        $st_uri        = 0;     // 検索失敗
    }
    $s_uri_all = $ss_uri + $st_uri;
    if ($s_uri_all <> 0) {
        $st_uri_allo     = Uround(($st_uri / $s_uri_all), 3);    // 耐久売上高率
        $ss_uri_allo     = 1 - $st_uri_allo;                     // 修理売上高率
    } else {
        $st_uri_allo = 0;
        $st_uri_allo = 0;
    }
    
    $query_chk = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='修理売上高'", $yyyymm);
    if (getResult($query_chk,$res_chk) <= 0) {      // 既登録済みのチェック
        // 新規登録
        $query = sprintf("insert into act_pl_history (pl_bs_ym, kin, note, allo) values (%d, %d, '修理売上高', %1.5f)", $yyyymm, $ss_uri, $ss_uri_allo);
        if (query_affected_trans($con, $query) <= 0) {
            $_SESSION['s_sysmsg'] .= sprintf("修理売上高の登録に失敗<br>第 %d期 %d月",$ki,$tuki);
            query_affected_trans($con, "rollback");     // Transaction Rollback
            header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
            exit();
        }
    } else {
        $query = sprintf("update act_pl_history set kin=%d, allo=%1.5f where pl_bs_ym=%d and note='修理売上高'", $ss_uri, $ss_uri_allo, $yyyymm);
        if (query_affected_trans($con, $query) <= 0) {
            $_SESSION['s_sysmsg'] .= sprintf("修理売上高の更新失敗<br>第 %d期 %d月",$ki,$tuki);
            query_affected_trans($con, "rollback");     // Transaction Rollback
            header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
            exit();
        }
    }
    $query_chk = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='耐久売上高'", $yyyymm);
    if (getResult($query_chk,$res_chk) <= 0) {      // 既登録済みのチェック
        // 新規登録
        $query = sprintf("insert into act_pl_history (pl_bs_ym, kin, note, allo) values (%d, %d, '耐久売上高', %1.5f)", $yyyymm, $st_uri, $st_uri_allo);
        if (query_affected_trans($con, $query) <= 0) {
            $_SESSION['s_sysmsg'] .= sprintf("耐久売上高の登録に失敗<br>第 %d期 %d月",$ki,$tuki);
            query_affected_trans($con, "rollback");     // Transaction Rollback
            header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
            exit();
        }
    } else {
        $query = sprintf("update act_pl_history set kin=%d, allo=%1.5f where pl_bs_ym=%d and note='耐久売上高'", $st_uri, $st_uri_allo, $yyyymm);
        if (query_affected_trans($con, $query) <= 0) {
            $_SESSION['s_sysmsg'] .= sprintf("耐久売上高の更新失敗<br>第 %d期 %d月",$ki,$tuki);
            query_affected_trans($con, "rollback");     // Transaction Rollback
            header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
            exit();
        }
    }
    
    // 全て登録終了後に 率を再計算し 営業外のデータを再計算させる
    if ($yyyymm >= 200912) {    // テスト用で１２月より計算（適用月を確認して変更すること
        ///// 対象前半期        // その際、損益計算書に組み込むこと（〜再計算）
        $yyyy = substr($yyyymm,0,4);
        $mm   = substr($yyyymm,4,2);
        if (($mm>=4) && ($mm<=9)) {
            $z_start_yyyy = $yyyy - 1;
            $z_start_ym   = $z_start_yyyy . '10';
            $z_end_ym     = $yyyy . '03';
            $z_start_ymd  = $z_start_ym . '01';
            $z_end_ymd    = $z_end_ym . '31';
        } elseif (($mm>=10) && ($mm<=12)) {
            $z_start_ym   = $yyyy . '04';
            $z_end_ym     = $yyyy . '09';
            $z_start_ymd  = $z_start_ym . '01';
            $z_end_ymd    = $z_end_ym . '31';
        } else {
            $z_start_yyyy = $yyyy - 1;
            $z_start_ym   = $z_start_yyyy . '04';
            $z_end_ym     = $z_start_yyyy . '09';
            $z_start_ymd  = $z_start_ym . '01';
            $z_end_ymd    = $z_end_ym . '31';
        }
        // 対象月までの売上高比に変更
        $z_end_ym = $yyyymm;
        
        // 各売上高の取得
        if($yyyymm >= 201004) {
            $query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='商管売上高'", $z_start_ym, $z_end_ym);
            if (getUniResult($query, $rui_b_uri) < 1) {
                $rui_b_uri        = 0;          // 検索失敗
                $rui_b_uri_sagaku = 0;
            } else {
                $rui_b_uri_sagaku = 0;
            }
            $query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='商管売上調整額'", $z_start_ym, $z_end_ym);
            if (getUniResult($query, $rui_b_uri_cho) < 1) {
                // 検索失敗 調整が無いので何もしない
                $rui_b_uri_sagaku = 0;
            } else {
                $rui_b_uri        = $rui_b_uri + $rui_b_uri_cho;
                $rui_b_uri_sagaku = $rui_b_uri_cho;
            }
        } else if($yyyymm >= 200909 && $yyyymm <= 201003) {
            $query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='商管売上高'", $z_start_ym, $z_end_ym);
            if (getUniResult($query, $rui_b_uri) < 1) {
                $rui_b_uri        = 0;          // 検索失敗
                $rui_b_uri_sagaku = 0;
            } else {
                $rui_b_uri_sagaku = 0;
            }
            $query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='商管売上調整額'", $z_start_ym, $z_end_ym);
            if (getUniResult($query, $rui_b_uri_cho) < 1) {
                // 検索失敗 調整が無いので何もしない
                $rui_b_uri_sagaku = 0;
            } else {
                $rui_b_uri        = $rui_b_uri + $rui_b_uri_cho;
                $rui_b_uri_sagaku = $rui_b_uri_cho + 25354300;      // 7月8月分の調整を9月に入れた分の戻し
            }
        } else {
            $query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='商管売上高'", $z_start_ym, $z_end_ym);
            if (getUniResult($query, $rui_b_uri) < 1) {
                $rui_b_uri        = 0;          // 検索失敗
                $rui_b_uri_sagaku = 0;
            } else {
                $rui_b_uri_sagaku = $rui_b_uri;
            }
        }
        if ( $yyyymm >= 200911) {
            $query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='カプラ試修売上高'", $z_start_ym, $z_end_ym);
            if (getUniResult($query, $rui_sc_uri) < 1) {
                $rui_sc_uri        = 0;         // 検索失敗
                $rui_sc_uri_sagaku = 0;
                $rui_sc_uri_temp   = 0;
            } else {
                $rui_sc_uri_temp   = $rui_sc_uri;
                $rui_sc_uri_sagaku = $rui_sc_uri;
            }
        } else{
            $rui_sc_uri        = 0;             // 検索失敗
            $rui_sc_uri_sagaku = 0;
            $rui_sc_uri_temp   = 0;
        }
        if ($yyyymm >= 200909) {
            $query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='試修売上高'", $z_start_ym, $z_end_ym);
            if (getUniResult($query, $rui_s_uri) < 1) {
                $rui_s_uri        = 0;          // 検索失敗
                $rui_s_uri_sagaku = 0;
            } else {
                $rui_s_uri_sagaku = $rui_s_uri;
            }
            $query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='試修売上調整額'", $z_start_ym, $z_end_ym);
            if (getUniResult($query, $rui_s_uri_cho) < 1) {
                // 検索失敗
                $rui_s_uri = $rui_s_uri + $rui_sc_uri_sagaku;                   // カプラ試験修理を加味
            } else {
                $rui_s_uri_sagaku = $rui_s_uri_sagaku + $rui_s_uri_cho;
                $rui_s_uri        = $rui_s_uri_sagaku + $rui_sc_uri_sagaku;     // カプラ試験修理を加味（tempの後−リニアからマイナスしてしまう為）
            }
        } else {
            $query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='試修売上高'", $z_start_ym, $z_end_ym);
            if (getUniResult($query, $rui_s_uri) < 1) {
                $rui_s_uri        = 0;          // 検索失敗
                $rui_s_uri_sagaku = 0;
            } else {
                if ($yyyymm == 200905) {
                    $rui_s_uri = $rui_s_uri + 3100900;
                } elseif ($yyyymm == 200904) {
                    $rui_s_uri = $rui_s_uri + 1550450;
                }
                $rui_s_uri_sagaku = $rui_s_uri;
            }
        }
        $rui_sl_uri = $rui_s_uri - $rui_sc_uri;     // リニア試修前半期売上高
        $query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='全体売上高'", $z_start_ym, $z_end_ym);
        if (getUniResult($query, $rui_all_uri) < 1) {
            $rui_all_uri = 0;                   // 検索失敗
        } else {
            if ($yyyymm == 200905) {
                $rui_all_uri = $rui_all_uri + 3100900;
            } elseif ($yyyymm == 200904) {
                $rui_all_uri = $rui_all_uri + 1550450;
            }
            $rui_all_uri = $rui_all_uri + $rui_b_uri_sagaku;
        }
        $query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='カプラ売上高'", $z_start_ym, $z_end_ym);
        if (getUniResult($query, $rui_c_uri) < 1) {
            $rui_c_uri = 0;                     // 検索失敗
        } else {
            $rui_c_uri = $rui_c_uri - $rui_sc_uri_sagaku;                   // カプラ試験修理を加味
        }
        $query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='リニア売上高'", $z_start_ym, $z_end_ym);
        if (getUniResult($query, $rui_l_uri) < 1) {
            $rui_l_uri = 0 - $rui_s_uri_sagaku;     // 検索失敗
        } else {
            $rui_l_uri = $rui_l_uri - $rui_s_uri_sagaku;
            if ($yyyymm == 200905) {
                $rui_l_uri = $rui_l_uri + 3100900;
            } elseif ($yyyymm == 200904) {
                $rui_l_uri = $rui_l_uri + 1550450;
            }
        }
        $query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='カプラ特注売上高'", $z_start_ym, $z_end_ym);
        if (getUniResult($query, $rui_ctoku_uri) < 1) {
            $rui_ctoku_uri         = 0;     // 検索失敗
            $rui_ctoku_uri_sagaku  = 0;
        } else {
            $rui_ctoku_uri_sagaku  = $rui_ctoku_uri;
        }
        $rui_chyou_uri = $rui_c_uri - $rui_ctoku_uri;               // カプラ標準前半期累計売上高
        $query = sprintf("select sum(kin) from act_pl_history where pl_bs_ym>=%d and pl_bs_ym<=%d and note='バイモル売上高'", $z_start_ym, $z_end_ym);
        if (getUniResult($query, $rui_lb_uri) < 1) {
            $rui_lb_uri        = 0;         // 検索失敗
            $rui_lb_uri_sagaku = 0;
        } else {
            $rui_lb_uri_sagaku = $rui_lb_uri;
        }
        $rui_lh_uri = $rui_l_uri - $rui_lb_uri;                     // リニア標準前半期累計売上高
        
        // 売上高比の計算
        $rui_t_uri      = $rui_c_uri + $rui_l_uri + $rui_s_uri;     // カプラ・リニア・試修合計売上高の計算
        // 商管を加味する場合
        $rui_t_uri      = $rui_t_uri + $rui_b_uri;                  // 商管を足して全体の売上高を計算
        $c_uri_allo     = Uround(($rui_c_uri / $rui_t_uri), 4);     // カプラ売上高比
        $l_uri_allo     = Uround(($rui_l_uri / $rui_t_uri), 4);     // リニア売上高比
        // 商管を加味する場合
        $b_uri_allo     = Uround(($rui_b_uri / $rui_t_uri), 4);     // 商管売上高比
        //$s_uri_allo     = 1 - $c_uri_allo - $l_uri_allo;            // 試修売上高比
        $s_uri_allo     = 1 - $c_uri_allo - $l_uri_allo - $b_uri_allo; // 試修売上高比
        
        
        $ctoku_uri_allo = Uround(($rui_ctoku_uri / $rui_c_uri), 4); // カプラ特注売上高比
        $chyou_uri_allo = 1 - $ctoku_uri_allo;                      // カプラ標準売上高比
        $lb_uri_allo    = Uround(($rui_lb_uri / $rui_l_uri), 4);    // バイモル売上高比
        $lh_uri_allo    = 1 - $lb_uri_allo;                         // リニア標準売上高比
        $sc_uri_allo    = Uround(($rui_sc_uri / $rui_s_uri), 4);    // カプラ試修売上高比
        $sl_uri_allo    = 1 - $sc_uri_allo;                         // リニア試修売上高比
        
        // (前半期)累計売上及び売上高比の登録
        $query_chk = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='カプラ累計売上高'", $yyyymm);
        if (getResult($query_chk,$res_chk) <= 0) {  // 既登録済みのチェック
            // 新規登録
            $query = sprintf("insert into act_pl_history (pl_bs_ym, kin, note, allo) values (%d, %d, 'カプラ累計売上高', %1.4f)", $yyyymm, $rui_c_uri, $c_uri_allo);
            if (query_affected_trans($con, $query) <= 0) {
                $_SESSION['s_sysmsg'] .= sprintf("カプラ累計売上高の登録に失敗<br>第 %d期 %d月",$ki,$tuki);
                query_affected_trans($con, "rollback");     // Transaction Rollback
                header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
                exit();
            }
        } else {
            $query = sprintf("update act_pl_history set kin=%d, allo=%1.4f where pl_bs_ym=%d and note='カプラ累計売上高'", $rui_c_uri, $c_uri_allo, $yyyymm);
            if (query_affected_trans($con, $query) <= 0) {
                $_SESSION['s_sysmsg'] .= sprintf("カプラ累計売上高の更新失敗<br>第 %d期 %d月",$ki,$tuki);
                query_affected_trans($con, "rollback");     // Transaction Rollback
                header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
                exit();
            }
        }
        $query_chk = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='カプラ特注累計売上高'", $yyyymm);
        if (getResult($query_chk,$res_chk) <= 0) {  // 既登録済みのチェック
            // 新規登録
            $query = sprintf("insert into act_pl_history (pl_bs_ym, kin, note, allo) values (%d, %d, 'カプラ特注累計売上高', %1.4f)", $yyyymm, $rui_ctoku_uri, $ctoku_uri_allo);
            if (query_affected_trans($con, $query) <= 0) {
                $_SESSION['s_sysmsg'] .= sprintf("カプラ特注累計売上高の登録に失敗<br>第 %d期 %d月",$ki,$tuki);
                query_affected_trans($con, "rollback");     // Transaction Rollback
                header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
                exit();
            }
        } else {
            $query = sprintf("update act_pl_history set kin=%d, allo=%1.4f where pl_bs_ym=%d and note='カプラ特注累計売上高'", $rui_ctoku_uri, $ctoku_uri_allo, $yyyymm);
            if (query_affected_trans($con, $query) <= 0) {
                $_SESSION['s_sysmsg'] .= sprintf("カプラ特注累計売上高の更新失敗<br>第 %d期 %d月",$ki,$tuki);
                query_affected_trans($con, "rollback");     // Transaction Rollback
                header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
                exit();
            }
        }
        $query_chk = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='カプラ標準累計売上高'", $yyyymm);
        if (getResult($query_chk,$res_chk) <= 0) {  // 既登録済みのチェック
            // 新規登録
            $query = sprintf("insert into act_pl_history (pl_bs_ym, kin, note, allo) values (%d, %d, 'カプラ標準累計売上高', %1.4f)", $yyyymm, $rui_chyou_uri, $chyou_uri_allo);
            if (query_affected_trans($con, $query) <= 0) {
                $_SESSION['s_sysmsg'] .= sprintf("カプラ標準累計売上高の登録に失敗<br>第 %d期 %d月",$ki,$tuki);
                query_affected_trans($con, "rollback");     // Transaction Rollback
                header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
                exit();
            }
        } else {
            $query = sprintf("update act_pl_history set kin=%d, allo=%1.4f where pl_bs_ym=%d and note='カプラ標準累計売上高'", $rui_chyou_uri, $chyou_uri_allo, $yyyymm);
            if (query_affected_trans($con, $query) <= 0) {
                $_SESSION['s_sysmsg'] .= sprintf("カプラ標準累計売上高の更新失敗<br>第 %d期 %d月",$ki,$tuki);
                query_affected_trans($con, "rollback");     // Transaction Rollback
                header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
                exit();
            }
        }
        $query_chk = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='リニア累計売上高'", $yyyymm);
        if (getResult($query_chk,$res_chk) <= 0) {  // 既登録済みのチェック
            // 新規登録
            $query = sprintf("insert into act_pl_history (pl_bs_ym, kin, note, allo) values (%d, %d, 'リニア累計売上高', %1.4f)", $yyyymm, $rui_l_uri, $l_uri_allo);
            if (query_affected_trans($con, $query) <= 0) {
                $_SESSION['s_sysmsg'] .= sprintf("リニア累計売上高の登録に失敗<br>第 %d期 %d月",$ki,$tuki);
                query_affected_trans($con, "rollback");     // Transaction Rollback
                header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
                exit();
            }
        } else {
            $query = sprintf("update act_pl_history set kin=%d, allo=%1.4f where pl_bs_ym=%d and note='リニア累計売上高'", $rui_l_uri, $l_uri_allo, $yyyymm);
            if (query_affected_trans($con, $query) <= 0) {
                $_SESSION['s_sysmsg'] .= sprintf("リニア累計売上高の更新失敗<br>第 %d期 %d月",$ki,$tuki);
                query_affected_trans($con, "rollback");     // Transaction Rollback
                header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
                exit();
            }
        }
        $query_chk = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='バイモル累計売上高'", $yyyymm);
        if (getResult($query_chk,$res_chk) <= 0) {  // 既登録済みのチェック
            // 新規登録
            $query = sprintf("insert into act_pl_history (pl_bs_ym, kin, note, allo) values (%d, %d, 'バイモル累計売上高', %1.4f)", $yyyymm, $rui_lb_uri, $lb_uri_allo);
            if (query_affected_trans($con, $query) <= 0) {
                $_SESSION['s_sysmsg'] .= sprintf("バイモル累計売上高の登録に失敗<br>第 %d期 %d月",$ki,$tuki);
                query_affected_trans($con, "rollback");     // Transaction Rollback
                header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
                exit();
            }
        } else {
            $query = sprintf("update act_pl_history set kin=%d, allo=%1.4f where pl_bs_ym=%d and note='バイモル累計売上高'", $rui_lb_uri, $lb_uri_allo, $yyyymm);
            if (query_affected_trans($con, $query) <= 0) {
                $_SESSION['s_sysmsg'] .= sprintf("バイモル累計売上高の更新失敗<br>第 %d期 %d月",$ki,$tuki);
                query_affected_trans($con, "rollback");     // Transaction Rollback
                header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
                exit();
            }
        }
        $query_chk = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='リニア標準累計売上高'", $yyyymm);
        if (getResult($query_chk,$res_chk) <= 0) {  // 既登録済みのチェック
            // 新規登録
            $query = sprintf("insert into act_pl_history (pl_bs_ym, kin, note, allo) values (%d, %d, 'リニア標準累計売上高', %1.4f)", $yyyymm, $rui_lh_uri, $lh_uri_allo);
            if (query_affected_trans($con, $query) <= 0) {
                $_SESSION['s_sysmsg'] .= sprintf("リニア標準累計売上高の登録に失敗<br>第 %d期 %d月",$ki,$tuki);
                query_affected_trans($con, "rollback");     // Transaction Rollback
                header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
                exit();
            }
        } else {
            $query = sprintf("update act_pl_history set kin=%d, allo=%1.4f where pl_bs_ym=%d and note='リニア標準累計売上高'", $rui_lh_uri, $lh_uri_allo, $yyyymm);
            if (query_affected_trans($con, $query) <= 0) {
                $_SESSION['s_sysmsg'] .= sprintf("リニア標準累計売上高の更新失敗<br>第 %d期 %d月",$ki,$tuki);
                query_affected_trans($con, "rollback");     // Transaction Rollback
                header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
                exit();
            }
        }
        $query_chk = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='商管累計売上高'", $yyyymm);
        if (getResult($query_chk,$res_chk) <= 0) {  // 既登録済みのチェック
            // 新規登録
            $query = sprintf("insert into act_pl_history (pl_bs_ym, kin, note, allo) values (%d, %d, '商管累計売上高', %1.4f)", $yyyymm, $rui_b_uri, $b_uri_allo);
            if (query_affected_trans($con, $query) <= 0) {
                $_SESSION['s_sysmsg'] .= sprintf("商管累計売上高の登録に失敗<br>第 %d期 %d月",$ki,$tuki);
                query_affected_trans($con, "rollback");     // Transaction Rollback
                header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
                exit();
            }
        } else {
            $query = sprintf("update act_pl_history set kin=%d, allo=%1.4f where pl_bs_ym=%d and note='商管累計売上高'", $rui_b_uri, $b_uri_allo, $yyyymm);
            if (query_affected_trans($con, $query) <= 0) {
                $_SESSION['s_sysmsg'] .= sprintf("商管累計売上高の更新失敗<br>第 %d期 %d月",$ki,$tuki);
                query_affected_trans($con, "rollback");     // Transaction Rollback
                header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
                exit();
            }
        }
        $query_chk = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='試修累計売上高'", $yyyymm);
        if (getResult($query_chk,$res_chk) <= 0) {  // 既登録済みのチェック
            // 新規登録
            $query = sprintf("insert into act_pl_history (pl_bs_ym, kin, note, allo) values (%d, %d, '試修累計売上高', %1.4f)", $yyyymm, $rui_s_uri, $s_uri_allo);
            if (query_affected_trans($con, $query) <= 0) {
                $_SESSION['s_sysmsg'] .= sprintf("試修累計売上高の登録に失敗<br>第 %d期 %d月",$ki,$tuki);
                query_affected_trans($con, "rollback");     // Transaction Rollback
                header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
                exit();
            }
        } else {
            $query = sprintf("update act_pl_history set kin=%d, allo=%1.4f where pl_bs_ym=%d and note='試修累計売上高'", $rui_s_uri, $s_uri_allo, $yyyymm);
            if (query_affected_trans($con, $query) <= 0) {
                $_SESSION['s_sysmsg'] .= sprintf("試修累計売上高の更新失敗<br>第 %d期 %d月",$ki,$tuki);
                query_affected_trans($con, "rollback");     // Transaction Rollback
                header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
                exit();
            }
        }
        $query_chk = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='カプラ試修累計売上高'", $yyyymm);
        if (getResult($query_chk,$res_chk) <= 0) {  // 既登録済みのチェック
            // 新規登録
            $query = sprintf("insert into act_pl_history (pl_bs_ym, kin, note, allo) values (%d, %d, 'カプラ試修累計売上高', %1.4f)", $yyyymm, $rui_sc_uri, $sc_uri_allo);
            if (query_affected_trans($con, $query) <= 0) {
                $_SESSION['s_sysmsg'] .= sprintf("カプラ試修累計売上高の登録に失敗<br>第 %d期 %d月",$ki,$tuki);
                query_affected_trans($con, "rollback");     // Transaction Rollback
                header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
                exit();
            }
        } else {
            $query = sprintf("update act_pl_history set kin=%d, allo=%1.4f where pl_bs_ym=%d and note='カプラ試修累計売上高'", $rui_sc_uri, $sc_uri_allo, $yyyymm);
            if (query_affected_trans($con, $query) <= 0) {
                $_SESSION['s_sysmsg'] .= sprintf("カプラ試修累計売上高の更新失敗<br>第 %d期 %d月",$ki,$tuki);
                query_affected_trans($con, "rollback");     // Transaction Rollback
                header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
                exit();
            }
        }
        $query_chk = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='リニア試修累計売上高'", $yyyymm);
        if (getResult($query_chk,$res_chk) <= 0) {  // 既登録済みのチェック
            // 新規登録
            $query = sprintf("insert into act_pl_history (pl_bs_ym, kin, note, allo) values (%d, %d, 'リニア試修累計売上高', %1.4f)", $yyyymm, $rui_sl_uri, $sl_uri_allo);
            if (query_affected_trans($con, $query) <= 0) {
                $_SESSION['s_sysmsg'] .= sprintf("リニア試修累計売上高の登録に失敗<br>第 %d期 %d月",$ki,$tuki);
                query_affected_trans($con, "rollback");     // Transaction Rollback
                header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
                exit();
            }
        } else {
            $query = sprintf("update act_pl_history set kin=%d, allo=%1.4f where pl_bs_ym=%d and note='リニア試修累計売上高'", $rui_sl_uri, $sl_uri_allo, $yyyymm);
            if (query_affected_trans($con, $query) <= 0) {
                $_SESSION['s_sysmsg'] .= sprintf("リニア試修累計売上高の更新失敗<br>第 %d期 %d月",$ki,$tuki);
                query_affected_trans($con, "rollback");     // Transaction Rollback
                header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
                exit();
            }
        }
        $query_chk = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='全体累計売上高'", $yyyymm);
        if (getResult($query_chk,$res_chk) <= 0) {  // 既登録済みのチェック
            // 新規登録
            $query = sprintf("insert into act_pl_history (pl_bs_ym, kin, note) values (%d, %d, '全体累計売上高')", $yyyymm, $rui_all_uri);
            if (query_affected_trans($con, $query) <= 0) {
                $_SESSION['s_sysmsg'] .= sprintf("全体累計売上高の登録に失敗<br>第 %d期 %d月",$ki,$tuki);
                query_affected_trans($con, "rollback");     // Transaction Rollback
                header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
                exit();
            }
        } else {
            $query = sprintf("update act_pl_history set kin=%d where pl_bs_ym=%d and note='全体累計売上高'", $rui_all_uri, $yyyymm);
            if (query_affected_trans($con, $query) <= 0) {
                $_SESSION['s_sysmsg'] .= sprintf("全体累計売上高の更新失敗<br>第 %d期 %d月",$ki,$tuki);
                query_affected_trans($con, "rollback");     // Transaction Rollback
                header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
                exit();
            }
        }
        // 各営業外の金額を計算
        /***** 業務委託収入の取得 *****/
        $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='全体業務委託収入'", $yyyymm);
        getUniResult($query,$res_kin);
        
        $gyoumu       = $res_kin;
        $gyoumu_c     = Uround(($gyoumu * $c_uri_allo), 0);         // カプラ業務委託収入
        $gyoumu_l     = Uround(($gyoumu * $l_uri_allo), 0);         // リニア業務委託収入
        // 商管を加味する場合
        $gyoumu_b     = Uround(($gyoumu * $b_uri_allo), 0);         // 商管業務委託収入
        //$gyoumu_s     = $gyoumu - $gyoumu_c - $gyoumu_l;            // 試修業務委託収入
        $gyoumu_s     = $gyoumu - $gyoumu_c - $gyoumu_l - $gyoumu_b;  // 試修業務委託収入
        
        $gyoumu_ctoku = Uround(($gyoumu_c * $ctoku_uri_allo), 0);   // カプラ特注業務委託収入
        $gyoumu_chyou = $gyoumu_c - $gyoumu_ctoku;                  // カプラ標準業務委託収入
        $gyoumu_lb    = Uround(($gyoumu_l * $lb_uri_allo), 0);      // バイモル業務委託収入
        $gyoumu_lh    = $gyoumu_l - $gyoumu_lb;                     // リニア標準業務委託収入
        $gyoumu_sc    = Uround(($gyoumu_s * $sc_uri_allo), 0);      // カプラ試修業務委託収入
        $gyoumu_sl    = $gyoumu_s - $gyoumu_sl;                     // リニア試修業務委託収入
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
        
        /***** 仕入割引の取得 *****/
        $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='全体仕入割引'", $yyyymm);
        getUniResult($query,$s_wari);
        $s_wari       = $s_wari;
        $s_wari_c     = Uround(($s_wari * $c_uri_allo), 0);         // カプラ仕入割引
        $s_wari_l     = Uround(($s_wari * $l_uri_allo), 0);         // リニア仕入割引
        // 商管を加味する場合
        $s_wari_b     = Uround(($s_wari * $b_uri_allo), 0);         // 商管仕入割引
        //$s_wari_s     = $s_wari - $s_wari_c - $s_wari_l;            // 試修仕入割引
        $s_wari_s     = $s_wari - $s_wari_c - $s_wari_l - $s_wari_b;  // 試修仕入割引
        
        $s_wari_ctoku = Uround(($s_wari_c * $ctoku_uri_allo), 0);   // カプラ特注仕入割引
        $s_wari_chyou = $s_wari_c - $s_wari_ctoku;                  // カプラ標準仕入割引
        $s_wari_lb    = Uround(($s_wari_l * $lb_uri_allo), 0);      // バイモル仕入割引
        $s_wari_lh    = $s_wari_l - $s_wari_lb;                     // リニア標準仕入割引
        $s_wari_sc    = Uround(($s_wari_s * $sc_uri_allo), 0);      // カプラ試修仕入割引
        $s_wari_sl    = $s_wari_s - $s_wari_sl;                     // リニア試修仕入割引
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
        
        /***** 営業外収益その他の取得 *****/
        $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='全体営業外収益その他'", $yyyymm);
        getUniResult($query,$p_other);
        $p_other       = $p_other;
        $p_other_c     = Uround(($p_other * $c_uri_allo), 0);         // カプラ営業外収益その他
        $p_other_l     = Uround(($p_other * $l_uri_allo), 0);         // リニア営業外収益その他
        // 商管を加味する場合
        $p_other_b     = Uround(($p_other * $b_uri_allo), 0);         // 商管営業外収益その他
        //$p_other_s     = $p_other - $p_other_c - $p_other_l;            // 試修営業外収益その他
        $p_other_s     = $p_other - $p_other_c - $p_other_l - $p_other_b; // 試修営業外収益その他
        
        
        
        $p_other_ctoku = Uround(($p_other_c * $ctoku_uri_allo), 0);   // カプラ特注営業外収益その他
        $p_other_chyou = $p_other_c - $p_other_ctoku;                  // カプラ標準営業外収益その他
        $p_other_lb    = Uround(($p_other_l * $lb_uri_allo), 0);      // バイモル営業外収益その他
        $p_other_lh    = $p_other_l - $p_other_lb;                     // リニア標準営業外収益その他
        $p_other_sc    = Uround(($p_other_s * $sc_uri_allo), 0);      // カプラ試修営業外収益その他
        $p_other_sl    = $p_other_s - $p_other_sl;                     // リニア試修営業外収益その他
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
        
        /***** 営業外収益計の取得 *****/
        //$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='全体営業外収益計'", $yyyymm);
        //getUniResult($query,$nonope_p_sum);
        //$nonope_p_sum       = $nonope_p_sum;
        //$nonope_p_sum_c     = Uround(($nonope_p_sum * $c_uri_allo), 0);         // カプラ営業外収益計
        //$nonope_p_sum_l     = Uround(($nonope_p_sum * $l_uri_allo), 0);         // リニア営業外収益計
        // 商管を加味する場合
        //$nonope_p_sum_b     = Uround(($nonope_p_sum * $b_uri_allo), 0);         // 商管営業外収益計
        //$nonope_p_sum_s     = $nonope_p_sum - $nonope_p_sum_c - $nonope_p_sum_l;            // 試修営業外収益計
        //$nonope_p_sum_s     = $nonope_p_sum - $nonope_p_sum_c - $nonope_p_sum_l - $nonope_p_sum_b;            // 試修営業外収益計
        //$nonope_p_sum_ctoku = Uround(($nonope_p_sum_c * $ctoku_uri_allo), 0);   // カプラ特注営業外収益計
        //$nonope_p_sum_chyou = $nonope_p_sum_c - $nonope_p_sum_ctoku;                  // カプラ標準営業外収益計
        //$nonope_p_sum_lb    = Uround(($nonope_p_sum_l * $lb_uri_allo), 0);      // バイモル営業外収益計
        //$nonope_p_sum_lh    = $nonope_p_sum_l - $nonope_p_sum_lb;                     // リニア標準営業外収益計
        //$nonope_p_sum_sc    = Uround(($nonope_p_sum_s * $sc_uri_allo), 0);      // カプラ試修営業外収益計
        //$nonope_p_sum_sl    = $nonope_p_sum_s - $nonope_p_sum_sl;                     // リニア試修営業外収益計
        
        // 営業外収益計の計算
        $nonope_p_sum_c     = $gyoumu_c + $s_wari_c + $p_other_c;         // カプラ営業外収益計
        $nonope_p_sum_l     = $gyoumu_l + $s_wari_l + $p_other_l;         // リニア営業外収益計
        // 商管を加味する場合
        $nonope_p_sum_b     = $gyoumu_b + $s_wari_b + $p_other_b;         // 商管営業外収益計
        
        $nonope_p_sum_s     = $gyoumu_s + $s_wari_s + $p_other_s;         // 試修営業外収益計
        $nonope_p_sum_ctoku = $gyoumu_ctoku + $s_wari_ctoku + $p_other_ctoku;   // カプラ特注営業外収益計
        $nonope_p_sum_chyou = $gyoumu_chyou + $s_wari_chyou + $p_other_chyou;   // カプラ標準営業外収益計
        $nonope_p_sum_lb    = $gyoumu_lb + $s_wari_lb + $p_other_lb;            // バイモル営業外収益計
        $nonope_p_sum_lh    = $gyoumu_lh + $s_wari_lh + $p_other_lh;            // リニア標準営業外収益計
        $nonope_p_sum_sc    = $gyoumu_sc + $s_wari_sc + $p_other_sc;            // カプラ試修営業外収益計
        $nonope_p_sum_sl    = $gyoumu_sl + $s_wari_sl + $p_other_sl;            // リニア試修営業外収益計
        
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
        
        /***** 支払利息の取得 *****/
        $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='全体支払利息'", $yyyymm);
        getUniResult($query,$risoku);
        $risoku       = $risoku;
        $risoku_c     = Uround(($risoku * $c_uri_allo), 0);         // カプラ支払利息
        $risoku_l     = Uround(($risoku * $l_uri_allo), 0);         // リニア支払利息
        // 商管を加味する場合
        $risoku_b     = Uround(($risoku * $b_uri_allo), 0);         // 商管支払利息
        //$risoku_s     = $risoku - $risoku_c - $risoku_l;            // 試修支払利息
        $risoku_s     = $risoku - $risoku_c - $risoku_l - $risoku_b;  // 試修支払利息
        
        
        $risoku_ctoku = Uround(($risoku_c * $ctoku_uri_allo), 0);   // カプラ特注支払利息
        $risoku_chyou = $risoku_c - $risoku_ctoku;                  // カプラ標準支払利息
        $risoku_lb    = Uround(($risoku_l * $lb_uri_allo), 0);      // バイモル支払利息
        $risoku_lh    = $risoku_l - $risoku_lb;                     // リニア標準支払利息
        $risoku_sc    = Uround(($risoku_s * $sc_uri_allo), 0);      // カプラ試修支払利息
        $risoku_sl    = $risoku_s - $risoku_sl;                     // リニア試修支払利息
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
        
        /***** 営業外費用その他の取得 *****/
        $query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='全体営業外費用その他'", $yyyymm);
        getUniResult($query,$l_other);
        $l_other       = $l_other;
        $l_other_c     = Uround(($l_other * $c_uri_allo), 0);         // カプラ営業外費用その他
        $l_other_l     = Uround(($l_other * $l_uri_allo), 0);         // リニア営業外費用その他
        // 商管を加味する場合
        $l_other_b     = Uround(($l_other * $b_uri_allo), 0);         // 商管営業外費用その他
        //$l_other_s     = $l_other - $l_other_c - $l_other_l;            // 試修営業外費用その他
        $l_other_s     = $l_other - $l_other_c - $l_other_l - $l_other_b; // 試修営業外費用その他
        
        
        $l_other_ctoku = Uround(($l_other_c * $ctoku_uri_allo), 0);   // カプラ特注営業外費用その他
        $l_other_chyou = $l_other_c - $l_other_ctoku;                  // カプラ標準営業外費用その他
        $l_other_lb    = Uround(($l_other_l * $lb_uri_allo), 0);      // バイモル営業外費用その他
        $l_other_lh    = $l_other_l - $l_other_lb;                     // リニア標準営業外費用その他
        $l_other_sc    = Uround(($l_other_s * $sc_uri_allo), 0);      // カプラ試修営業外費用その他
        $l_other_sl    = $l_other_s - $l_other_sl;                     // リニア試修営業外費用その他
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
            $query = sprintf("insert into act_pl_history (pl_bs_ym, kin, note) values (%d, %d, '商管営業外費用その他再計算')", $yyyymm, $l_other_s);
            if (query_affected_trans($con, $query) <= 0) {
                $_SESSION['s_sysmsg'] .= sprintf("商管営業外費用その他再計算の登録に失敗<br>第 %d期 %d月",$ki,$tuki);
                query_affected_trans($con, "rollback");     // Transaction Rollback
                header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
                exit();
            }
        } else {
            $query = sprintf("update act_pl_history set kin=%d where pl_bs_ym=%d and note='商管営業外費用その他再計算'", $l_other_s, $yyyymm);
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
        
        /***** 営業外費用計の取得 *****/
        //$query = sprintf("select kin from act_pl_history where pl_bs_ym=%d and note='全体営業外費用計'", $yyyymm);
        //getUniResult($query,$nonope_l_sum);
        //$nonope_l_sum       = $nonope_l_sum;
        //$nonope_l_sum_c     = Uround(($nonope_l_sum * $c_uri_allo), 0);         // カプラ営業外費用計
        //$nonope_l_sum_l     = Uround(($nonope_l_sum * $l_uri_allo), 0);         // リニア営業外費用計
        // 商管を加味する場合
        //$nonope_l_sum_b     = Uround(($nonope_l_sum * $b_uri_allo), 0);         // 商管営業外費用計
        //$nonope_l_sum_s     = $nonope_l_sum - $nonope_l_sum_c - $nonope_l_sum_l;            // 試修営業外費用計
        //$nonope_l_sum_s     = $nonope_l_sum - $nonope_l_sum_c - $nonope_l_sum_l - $nonope_l_sum_b;            // 試修営業外費用計
        //$nonope_l_sum_ctoku = Uround(($nonope_l_sum_c * $ctoku_uri_allo), 0);   // カプラ特注営業外費用計
        //$nonope_l_sum_chyou = $nonope_l_sum_c - $nonope_l_sum_ctoku;                  // カプラ標準営業外費用計
        //$nonope_l_sum_lb    = Uround(($nonope_l_sum_l * $lb_uri_allo), 0);      // バイモル営業外費用計
        //$nonope_l_sum_lh    = $nonope_l_sum_l - $nonope_l_sum_lb;                     // リニア標準営業外費用計
        //$nonope_l_sum_sc    = Uround(($nonope_l_sum_s * $sc_uri_allo), 0);      // カプラ試修営業外費用計
        //$nonope_l_sum_sl    = $nonope_l_sum_s - $nonope_l_sum_sl;                     // リニア試修営業外費用計
        
        // 営業外費用計の計算
        $nonope_l_sum_c     = $risoku_c + $l_other_c;         // カプラ営業外費用計
        $nonope_l_sum_l     = $risoku_l + $l_other_l;         // リニア営業外費用計
        // 商管を加味する場合
        $nonope_l_sum_b     = $risoku_b + $l_other_b;         // 商管営業外費用計
        
        $nonope_l_sum_s     = $risoku_s + $l_other_s;         // 試修営業外費用計
        $nonope_l_sum_ctoku = $risoku_ctoku + $l_other_ctoku; // カプラ特注営業外費用計
        $nonope_l_sum_chyou = $risoku_chyou + $l_other_chyou; // カプラ標準営業外費用計
        $nonope_l_sum_lb    = $risoku_lb + $l_other_lb;       // バイモル営業外費用計
        $nonope_l_sum_lh    = $risoku_lh + $l_other_lh;       // リニア標準営業外費用計
        $nonope_l_sum_sc    = $risoku_sc + $l_other_sc;       // カプラ試修営業外費用計
        $nonope_l_sum_sl    = $risoku_sl + $l_other_sl;       // リニア試修営業外費用計
        
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
    document.invent.invent_1.focus();
    document.invent.invent_1.select();
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
                <th colspan='1' bgcolor='#ccffcc' width='110'>　</th>
                <th bgcolor='#ffffcc' width='110'>商品管理</th>
                <th bgcolor='#ccffff' width='110'>リニア試修</th>
                <th bgcolor='#ccffff' width='110'>カプラ試修</th>
                <tr>
                    <td align='center' bgcolor='#ccffcc' class='pt11b'>
                    <?php if ($yyyymm >= 200909) { ?>
                    売上高調整額
                    <font color='red'>※１</font>
                    <?php } else { ?>
                    売上高
                    <?php } ?>
                    </td>
                    </td>
                    <?php if ($yyyymm >= 200907) { ?>
                        <td align='center' bgcolor='white'>
                            <input type='text' name='invent[]' size='11' maxlength='11' value='<?php echo $invent[0] ?>' class='right'>
                        </td>
                    <?php } else { ?>
                        <td align='center' class='rightby'>
                            <input type='hidden' name='invent[]' value='<?php echo $invent[0] ?>'>
                            <?php echo $invent[0] ?>
                        </td>
                    <?php } ?>
                    <?php if ($yyyymm >= 200911) { ?>
                        <td align='center' bgcolor='white'>
                            <input type='text' name='invent[]' size='11' maxlength='11' value='<?php echo $invent[1] ?>' class='right'>
                        </td>
                        <td align='center' bgcolor='white'>
                            <input type='text' name='invent[]' size='11' maxlength='11' value='<?php echo $invent[2] ?>' class='right'>
                        </td>
                    <?php } elseif ($yyyymm >= 200909) { ?>
                        <td align='center' bgcolor='white'>
                            <input type='text' name='invent[]' size='11' maxlength='11' value='<?php echo $invent[1] ?>' class='right'>
                        </td>
                        <td align='center' class='rightbb'>
                            <input type='hidden' name='invent[]' value='<?php echo $invent[2] ?>'>
                            <?php echo $invent[2] ?>
                        </td>
                    <?php } else { ?>
                        <td align='center' class='rightbb'>
                            <input type='hidden' name='invent[]' value='<?php echo $invent[1] ?>'>
                            <?php echo $invent[1] ?>
                        </td>
                        <td align='center' class='rightbb'>
                            <input type='hidden' name='invent[]' value='<?php echo $invent[2] ?>'>
                            <?php echo $invent[2] ?>
                        </td>
                    <?php } ?>
                </tr>
                <tr>
                    <td align='center' bgcolor='#ccffcc' class='pt11b'>期首棚卸高</td>
                    <td align='center' class='rightby'>
                        <input type='hidden' name='invent[]' value='<?php echo $invent[3] ?>'>
                        <?php echo $invent[3] ?>
                    </td>
                    <td align='center' class='rightbb'>
                        <input type='hidden' name='invent[]' value='<?php echo $invent[4] ?>'>
                        <!-- <?php echo $invent[4] ?> -->
                        　
                    </td>
                    <td align='center' class='rightbb'>　</td>
                </tr>
                <tr>
                    <td align='center' bgcolor='#ccffcc' class='pt11b'>材料費</td>
                    <td align='center' class='rightby'>
                        <input type='hidden' name='invent[]' value='<?php echo $invent[5] ?>'>
                        <?php echo $invent[5] ?>
                    </td>
                    <td align='center' bgcolor='white'>
                        <input type='text' name='invent[]' size='11' maxlength='11' value='<?php echo $invent[6] ?>' class='right' onChange='return isDigit(value);'>
                    </td>
                    <?php if ($yyyymm >= 200911) { ?>
                    <td align='center' bgcolor='white'>
                        <input type='text' name='invent[]' size='11' maxlength='11' value='<?php echo $invent[7] ?>' class='right' onChange='return isDigit(value);'>
                    </td>
                    <?php } else { ?>
                    <td align='center' class='rightbb'>
                        <input type='hidden' name='invent[]' value='<?php echo $invent[7] ?>'>
                        <?php echo $invent[7] ?>
                    </td>
                    <?php } ?>
                </tr>
                <tr>
                    <td align='center' bgcolor='#ccffcc' class='pt11b'>
                    労務費
                    <?php if ($yyyymm == 200907) { ?>
                    <font color='red'>※１</font>
                    <?php } ?>
                    </td>
                    <?php if ($yyyymm == 200907) { ?>
                        <td align='center' bgcolor='white'>
                            <input type='text' name='invent[]' size='11' maxlength='11' value='<?php echo $invent[8] ?>' class='right' onChange='return isDigitcho(value);'>
                        </td>
                    <?php } else { ?>
                        <td align='center' class='rightby'>
                            <input type='hidden' name='invent[]' value='<?php echo $invent[8] ?>'>
                            <?php echo $invent[8] ?>
                        </td>
                    <?php } ?>
                    <td align='center' class='rightbb'>
                        <input type='hidden' name='invent[]' value='<?php echo $invent[9] ?>'>
                        <!-- <?php echo $invent[9] ?> -->
                        <?php echo $invent[42] ?>
                    </td>
                    <td align='center' class='rightbb'>
                        <?php echo $invent[33] ?>
                    </td>
                </tr>
                <tr>
                    <td align='center' bgcolor='#ccffcc' class='pt11b'>製造経費</td>
                    <td align='center' class='rightby'>
                        <input type='hidden' name='invent[]' value='<?php echo $invent[10] ?>'>
                        <?php echo $invent[10] ?>
                    </td>
                    <td align='center' class='rightbb'>
                        <input type='hidden' name='invent[]' value='<?php echo $invent[11] ?>'>
                        <!-- <?php echo $invent[11] ?> -->
                        <?php echo $invent[43] ?>
                    </td>
                    <td align='center' class='rightbb'>
                    <?php echo $invent[34] ?>
                    </td>
                </tr>
                <tr>
                    <td align='center' bgcolor='#ccffcc' class='pt11b'>期末棚卸高</td>
                    <td align='center' class='rightby'>
                        <input type='hidden' name='invent[]' value='<?php echo $invent[12] ?>'>
                        <?php echo $invent[12] ?>
                    </td>
                    <td align='center' class='rightbb'>
                        <input type='hidden' name='invent[]' value='<?php echo $invent[13] ?>'>
                        <!-- <?php echo $invent[13] ?> -->
                        　
                    </td>
                    <td align='center' class='rightbb'>　</td>
                </tr>
                <tr>
                    <td align='center' bgcolor='#ccffcc' class='pt11b'>
                    人件費
                    <?php if ($yyyymm == 200907) { ?>
                    <font color='red'>※１</font>
                    <?php } ?>
                    </td>
                    <?php if ($yyyymm == 200907) { ?>
                        <td align='center' bgcolor='white'>
                            <input type='text' name='invent[]' size='11' maxlength='11' value='<?php echo $invent[14] ?>' class='right' onChange='return isDigitcho(value);'>
                        </td>
                    <?php } else { ?>
                        <td align='center' class='rightby'>
                            <input type='hidden' name='invent[]' value='<?php echo $invent[14] ?>'>
                            <?php echo $invent[14] ?>
                        </td>
                    <?php } ?>
                    <td align='center' class='rightbb'>
                        <input type='hidden' name='invent[]' value='<?php echo $invent[15] ?>'>
                        <!-- <?php echo $invent[15] ?> -->
                        <?php echo $invent[44] ?>
                    </td>
                    <td align='center' class='rightbb'>
                    <?php echo $invent[35] ?>
                    </td>
                </tr>
                <tr>
                    <td align='center' bgcolor='#ccffcc' class='pt11b'>販管費経費</td>
                    <td align='center' class='rightby'>
                        <input type='hidden' name='invent[]' value='<?php echo $invent[16] ?>'>
                        <?php echo $invent[16] ?>
                    </td>
                    <td align='center' class='rightbb'>
                        <input type='hidden' name='invent[]' value='<?php echo $invent[17] ?>'>
                        <!-- <?php echo $invent[17] ?> -->
                        <?php echo $invent[45] ?>
                    </td>
                    <td align='center' class='rightbb'>
                    <?php echo $invent[36] ?>
                    </td>
                </tr>
                <tr>
                    <td align='center' bgcolor='#ccffcc' class='pt11b'>業務委託収入</td>
                    <td align='center' class='rightby'>
                        <input type='hidden' name='invent[]' value='<?php echo $invent[18] ?>'>
                        <?php echo $invent[18] ?>
                    </td>
                    <td align='center' class='rightbb'>
                        <input type='hidden' name='invent[]' value='<?php echo $invent[19] ?>'>
                        <!-- <?php echo $invent[19] ?> -->
                        <?php echo $invent[46] ?>
                    </td>
                    <td align='center' class='rightbb'>
                    <?php echo $invent[37] ?>
                    </td>
                </tr>
                <tr>
                    <td align='center' bgcolor='#ccffcc' class='pt11b'>仕入割引</td>
                    <td align='center' class='rightby'>
                        <input type='hidden' name='invent[]' value='<?php echo $invent[20] ?>'>
                        <?php echo $invent[20] ?>
                    </td>
                    <td align='center' class='rightbb'>
                        <input type='hidden' name='invent[]' value='<?php echo $invent[21] ?>'>
                        <!-- <?php echo $invent[21] ?> -->
                        <?php echo $invent[47] ?>
                    </td>
                    <td align='center' class='rightbb'>
                    <?php echo $invent[38] ?>
                    </td>
                </tr>
                <tr>
                    <td align='center' bgcolor='#ccffcc' class='pt11b'>営業外収益その他</td>
                    <?php if ($yyyymm >= 201001) { ?>
                        <td align='center' class='rightby'>
                            <input type='hidden' name='invent[]' value='<?php echo $invent[22] ?>'>
                            <?php echo $invent[22] ?>
                        </td>
                    <?php } elseif ($yyyymm >= 200907) { ?>
                        <td align='center' bgcolor='white'>
                            <input type='text' name='invent[]' size='11' maxlength='11' value='<?php echo $invent[22] ?>' class='right' onChange='return isDigit(value);'>
                        </td>
                    <?php } else { ?>
                        <td align='center' class='rightby'>
                            <input type='hidden' name='invent[]' value='<?php echo $invent[22] ?>'>
                            <?php echo $invent[22] ?>
                        </td>
                    <?php } ?>
                    <td align='center' class='rightbb'>
                        <input type='hidden' name='invent[]' value='<?php echo $invent[23] ?>'>
                        <!-- <?php echo $invent[23] ?> -->
                        <?php echo $invent[48] ?>
                    </td>
                    <td align='center' class='rightbb'>
                    <?php echo $invent[39] ?>
                    </td>
                </tr>
                <tr>
                    <td align='center' bgcolor='#ccffcc' class='pt11b'>支払利息</td>
                    <td align='center' class='rightby'>
                        <input type='hidden' name='invent[]' value='<?php echo $invent[24] ?>'>
                        <?php echo $invent[24] ?>
                    </td>
                    <td align='center' class='rightbb'>
                        <input type='hidden' name='invent[]' value='<?php echo $invent[25] ?>'>
                        <!-- <?php echo $invent[25] ?> -->
                        <?php echo $invent[49] ?>
                    </td>
                    <td align='center' class='rightbb'>
                    <?php echo $invent[40] ?>
                    </td>
                </tr>
                <tr>
                    <td align='center' bgcolor='#ccffcc' class='pt11b'>営業外費用その他</td>
                    <td align='center' class='rightby'>
                        <input type='hidden' name='invent[]' value='<?php echo $invent[26] ?>'>
                        <?php echo $invent[26] ?>
                    </td>
                    <td align='center' class='rightbb'>
                        <input type='hidden' name='invent[]' value='<?php echo $invent[27] ?>'>
                        <!-- <?php echo $invent[27] ?> -->
                        <?php echo $invent[50] ?>
                    </td>
                    <td align='center' class='rightbb'>
                    <?php echo $invent[41] ?>
                    </td>
                </tr>
                <?php if ($yyyymm >= 200910) { ?>
                <tr>
                    <td align='center' bgcolor='#ccffcc' class='pt11b'>商管社員給与按分１(20% 工場長)<font color='red'>※２</font></td>
                    <td align='center' bgcolor='white'>
                            <input type='text' name='invent[]' size='11' maxlength='11' value='<?php echo $invent[28] ?>' class='right' onChange='return isDigit(value);'>
                        </td>
                    <td align='center' class='rightbb'>　</td>
                    <td align='center' class='rightbb'>　</td>
                </tr>
                <tr>
                    <td align='center' bgcolor='#ccffcc' class='pt11b'>商管社員給与按分２(100% 管理部長)<font color='red'>※２</font></td>
                    <td align='center' bgcolor='white'>
                            <input type='text' name='invent[]' size='11' maxlength='11' value='<?php echo $invent[29] ?>' class='right' onChange='return isDigit(value);'>
                        </td>
                    <td align='center' class='rightbb'>　</td>
                    <td align='center' class='rightbb'>　</td>
                </tr>
                <?php } ?>
                <tr>
                    <td colspan='4' align='center'>
                        <input type='submit' name='entry' value='実行' >
                    </td>
                </tr>
            </table>
            </td></tr>
        </table> <!-- ダミーEnd -->
        </form>
        <?php if ($yyyymm == 200907) { ?>
            <br>
            <b>※１ 商品管理の労務費・人件費は調整金額を入力</b>
        <?php } ?>
        <?php if ($yyyymm >= 200911) { ?>
            <br>
            <b>※１ 商品管理・リニア試修の売上高は調整金額を入力</b>
            <br>
            <b>カプラ試修の売上高は売上高を入力</b>
        <?php } elseif ($yyyymm >= 200909) { ?>
            <br>
            <b>※１ 商品管理・試修の売上高は調整金額を入力</b>
        <?php } ?>
        <?php if ($yyyymm >= 200910) { ?>
            <br><br>
            <b>※２ 給与配賦を行う方の給与の支給項目・支給合計を入力（それぞれの％で自動配賦）</b>
        <?php } ?>
    </center>
</body>
</html>
