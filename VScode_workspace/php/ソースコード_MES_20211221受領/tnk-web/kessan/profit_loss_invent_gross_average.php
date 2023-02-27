<?php
//////////////////////////////////////////////////////////////////////////////
// 決算総平均単価による期末棚卸高登録・修正及び照会兼用                     //
// Copyright (C) 2003-2005 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp      //
// Changed history                                                          //
// 2003/03/13 Created   profit_loss_invent_gross_average.php                //
// 2003/03/14 行項目(原材料等)の合計を追加                                  //
// 2005/10/27 MenuHeader class を使用して共通メニュー化及び認証方式へ変更   //
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
// $menu->set_caption('栃木日東工器(株)');
//////////// 呼出先のaction名とアドレス設定
// $menu->set_action('抽象化名',   PL . 'address.php');

$current_script  = $_SERVER['PHP_SELF'];        // 現在実行中のスクリプト名を保存
$url_referer     = $_SESSION['pl_referer'];     // 分岐処理前に保存されている呼出元をセットする

//////////// タイトルの日付・時間設定
$today = date("Y/m/d H:i:s");

///// 期・月の取得
$ki = Ym_to_tnk($_SESSION['pl_ym']);
$tuki = substr($_SESSION['pl_ym'],4,2);

//////////// タイトル名(ソースのタイトル名とフォームのタイトル名)
$menu->set_title("第{$ki}期　{$tuki}月度　決算 総平均 期末棚卸高の登録");

///// 対象当月
$yyyymm = $_SESSION['pl_ym'];
///// 対象前月
if (substr($yyyymm,4,2)!=01) {
    $p1_ym = $yyyymm - 1;
} else {
    $p1_ym = $yyyymm - 100;
    $p1_ym = $p1_ym + 11;
}
///////// 項目とインデックスの関連付け
$item = array();
$item[0]  = "カプラ原材料材料費";
$item[1]  = "カプラ原材料組立費";
$item[2]  = "カプラ原材料工作費";
$item[3]  = "カプラ原材料間接費";
$item[4]  = "カプラ資材部品材料費";
$item[5]  = "カプラ資材部品組立費";
$item[6]  = "カプラ資材部品工作費";
$item[7]  = "カプラ資材部品間接費";
$item[8]  = "カプラ工作仕掛材料費";
$item[9]  = "カプラ工作仕掛組立費";
$item[10] = "カプラ工作仕掛工作費";
$item[11] = "カプラ工作仕掛間接費";
$item[12] = "カプラ外注仕掛材料費";
$item[13] = "カプラ外注仕掛組立費";
$item[14] = "カプラ外注仕掛工作費";
$item[15] = "カプラ外注仕掛間接費";
$item[16] = "カプラ検査仕掛材料費";
$item[17] = "カプラ検査仕掛組立費";
$item[18] = "カプラ検査仕掛工作費";
$item[19] = "カプラ検査仕掛間接費";
$item[20] = "カプラＣＣ部品材料費";
$item[21] = "カプラＣＣ部品組立費";
$item[22] = "カプラＣＣ部品工作費";
$item[23] = "カプラＣＣ部品間接費";
$item[24] = "カプラ組立仕掛材料費";
$item[25] = "カプラ組立仕掛組立費";
$item[26] = "カプラ組立仕掛工作費";
$item[27] = "カプラ組立仕掛間接費";
$item[28]  = "リニア原材料材料費";
$item[29]  = "リニア原材料組立費";
$item[30]  = "リニア原材料工作費";
$item[31]  = "リニア原材料間接費";
$item[32]  = "リニア資材部品材料費";
$item[33]  = "リニア資材部品組立費";
$item[34]  = "リニア資材部品工作費";
$item[35]  = "リニア資材部品間接費";
$item[36]  = "リニア工作仕掛材料費";
$item[37]  = "リニア工作仕掛組立費";
$item[38] = "リニア工作仕掛工作費";
$item[39] = "リニア工作仕掛間接費";
$item[40] = "リニア外注仕掛材料費";
$item[41] = "リニア外注仕掛組立費";
$item[42] = "リニア外注仕掛工作費";
$item[43] = "リニア外注仕掛間接費";
$item[44] = "リニア検査仕掛材料費";
$item[45] = "リニア検査仕掛組立費";
$item[46] = "リニア検査仕掛工作費";
$item[47] = "リニア検査仕掛間接費";
$item[48] = "リニアＣＣ部品材料費";
$item[49] = "リニアＣＣ部品組立費";
$item[50] = "リニアＣＣ部品工作費";
$item[51] = "リニアＣＣ部品間接費";
$item[52] = "リニア組立仕掛材料費";
$item[53] = "リニア組立仕掛組立費";
$item[54] = "リニア組立仕掛工作費";
$item[55] = "リニア組立仕掛間接費";
///////// 入力text 変数 初期化
$invent = array();
for ($i = 0; $i < 56; $i++) {
    if (isset($_POST['invent'][$i])) {
        $invent[$i] = $_POST['invent'][$i];
    } else {
        $invent[$i] = "";
    }
}
if (!isset($_POST['entry'])) {     // データ入力
    ////////// 登録済みならば棚卸金額取得
    $query = sprintf("select kin from act_invent_gross_average_history where pl_bs_ym=%d order by id ASC", $yyyymm);
    $res = array();
    if (getResult2($query,$res) > 0) {
        for ($i = 0; $i < 56; $i++) {
            $invent[$i] = $res[$i][0];
        }
    }
    ////////// 部品計の取得
    $query = sprintf("select sum(kin) from act_invent_gross_average_history where pl_bs_ym=%d and 
        note like 'カプラ%%材料費' and note<>'カプラ組立仕掛材料費'", $yyyymm);
    getUniResult($query, $d_zai_c);
    $zai_c = number_format($d_zai_c);
    $query = sprintf("select sum(kin) from act_invent_gross_average_history where pl_bs_ym=%d and 
        note like 'カプラ%%組立費' and note<>'カプラ組立仕掛組立費'", $yyyymm);
    getUniResult($query, $d_kumi_c);
    $kumi_c = number_format($d_kumi_c);
    $query = sprintf("select sum(kin) from act_invent_gross_average_history where pl_bs_ym=%d and 
        note like 'カプラ%%工作費' and note<>'カプラ組立仕掛工作費'", $yyyymm);
    getUniResult($query, $d_kou_c);
    $kou_c = number_format($d_kou_c);
    $query = sprintf("select sum(kin) from act_invent_gross_average_history where pl_bs_ym=%d and 
        note like 'カプラ%%間接費' and note<>'カプラ組立仕掛間接費'", $yyyymm);
    getUniResult($query, $d_kan_c);
    $kan_c = number_format($d_kan_c);
    $query = sprintf("select sum(kin) from act_invent_gross_average_history where pl_bs_ym=%d and 
        note like 'リニア%%材料費' and note<>'リニア組立仕掛材料費'", $yyyymm);
    getUniResult($query, $d_zai_l);
    $zai_l = number_format($d_zai_l);
    $query = sprintf("select sum(kin) from act_invent_gross_average_history where pl_bs_ym=%d and 
        note like 'リニア%%組立費' and note<>'リニア組立仕掛組立費'", $yyyymm);
    getUniResult($query, $d_kumi_l);
    $kumi_l = number_format($d_kumi_l);
    $query = sprintf("select sum(kin) from act_invent_gross_average_history where pl_bs_ym=%d and 
        note like 'リニア%%工作費' and note<>'リニア組立仕掛工作費'", $yyyymm);
    getUniResult($query, $d_kou_l);
    $kou_l = number_format($d_kou_l);
    $query = sprintf("select sum(kin) from act_invent_gross_average_history where pl_bs_ym=%d and 
        note like 'リニア%%間接費' and note<>'リニア組立仕掛間接費'", $yyyymm);
    getUniResult($query, $d_kan_l);
    $kan_l = number_format($d_kan_l);
    ////////// 在庫計の取得
    $query = sprintf("select sum(kin) from act_invent_gross_average_history where pl_bs_ym=%d and 
        note like 'カプラ%%材料費'", $yyyymm);
    getUniResult($query, $d_zai_all_c);
    $zai_all_c = number_format($d_zai_all_c);
    $query = sprintf("select sum(kin) from act_invent_gross_average_history where pl_bs_ym=%d and 
        note like 'カプラ%%組立費'", $yyyymm);
    getUniResult($query, $d_kumi_all_c);
    $kumi_all_c = number_format($d_kumi_all_c);
    $query = sprintf("select sum(kin) from act_invent_gross_average_history where pl_bs_ym=%d and 
        note like 'カプラ%%工作費'", $yyyymm);
    getUniResult($query, $d_kou_all_c);
    $kou_all_c = number_format($d_kou_all_c);
    $query = sprintf("select sum(kin) from act_invent_gross_average_history where pl_bs_ym=%d and 
        note like 'カプラ%%間接費'", $yyyymm);
    getUniResult($query, $d_kan_all_c);
    $kan_all_c = number_format($d_kan_all_c);
    /********** ここから リニア *********/
    $query = sprintf("select sum(kin) from act_invent_gross_average_history where pl_bs_ym=%d and 
        note like 'リニア%%材料費'", $yyyymm);
    getUniResult($query, $d_zai_all_l);
    $zai_all_l = number_format($d_zai_all_l);
    $query = sprintf("select sum(kin) from act_invent_gross_average_history where pl_bs_ym=%d and 
        note like 'リニア%%組立費'", $yyyymm);
    getUniResult($query, $d_kumi_all_l);
    $kumi_all_l = number_format($d_kumi_all_l);
    $query = sprintf("select sum(kin) from act_invent_gross_average_history where pl_bs_ym=%d and 
        note like 'リニア%%工作費'", $yyyymm);
    getUniResult($query, $d_kou_all_l);
    $kou_all_l = number_format($d_kou_all_l);
    $query = sprintf("select sum(kin) from act_invent_gross_average_history where pl_bs_ym=%d and 
        note like 'リニア%%間接費'", $yyyymm);
    getUniResult($query, $d_kan_all_l);
    $kan_all_l = number_format($d_kan_all_l);
    ////////// 横の小計 仕掛品目毎
        // カプラ原材料
    $query = sprintf("select sum(kin) from act_invent_gross_average_history where pl_bs_ym=%d and 
        note like 'カプラ原材料%%'", $yyyymm);
    getUniResult($query, $gen_c);
    $gen_c = number_format($gen_c);
        // カプラ資材部品
    $query = sprintf("select sum(kin) from act_invent_gross_average_history where pl_bs_ym=%d and 
        note like 'カプラ資材部品%%'", $yyyymm);
    getUniResult($query, $shi_c);
    $shi_c = number_format($shi_c);
        // カプラ工作仕掛
    $query = sprintf("select sum(kin) from act_invent_gross_average_history where pl_bs_ym=%d and 
        note like 'カプラ工作仕掛%%'", $yyyymm);
    getUniResult($query, $kshi_c);
    $kshi_c = number_format($kshi_c);
        // カプラ外注仕掛
    $query = sprintf("select sum(kin) from act_invent_gross_average_history where pl_bs_ym=%d and 
        note like 'カプラ外注仕掛%%'", $yyyymm);
    getUniResult($query, $gai_c);
    $gai_c = number_format($gai_c);
        // カプラ検査仕掛
    $query = sprintf("select sum(kin) from act_invent_gross_average_history where pl_bs_ym=%d and 
        note like 'カプラ検査仕掛%%'", $yyyymm);
    getUniResult($query, $ken_c);
    $ken_c = number_format($ken_c);
        // カプラＣＣ部品
    $query = sprintf("select sum(kin) from act_invent_gross_average_history where pl_bs_ym=%d and 
        note like 'カプラＣＣ部品%%'", $yyyymm);
    getUniResult($query, $cc_c);
    $cc_c = number_format($cc_c);
        // カプラ資材部品
    $query = sprintf("select sum(kin) from act_invent_gross_average_history where pl_bs_ym=%d and 
        note like 'カプラ資材部品%%'", $yyyymm);
    getUniResult($query, $shi_c);
    $shi_c = number_format($shi_c);
        // カプラ組立仕掛
    $query = sprintf("select sum(kin) from act_invent_gross_average_history where pl_bs_ym=%d and 
        note like 'カプラ組立仕掛%%'", $yyyymm);
    getUniResult($query, $kushi_c);
    $kushi_c = number_format($kushi_c);
        // カプラ部品計
    $buhin_c = number_format($d_zai_c + $d_kumi_c + $d_kou_c + $d_kan_c);
        // カプラ在庫計
    $zaiko_c = number_format($d_zai_all_c + $d_kumi_all_c + $d_kou_all_c + $d_kan_all_c);
    /*********** ここからリニア **********/
        // リニア原材料
    $query = sprintf("select sum(kin) from act_invent_gross_average_history where pl_bs_ym=%d and 
        note like 'リニア原材料%%'", $yyyymm);
    getUniResult($query, $gen_l);
    $gen_l = number_format($gen_l);
        // リニア資材部品
    $query = sprintf("select sum(kin) from act_invent_gross_average_history where pl_bs_ym=%d and 
        note like 'リニア資材部品%%'", $yyyymm);
    getUniResult($query, $shi_l);
    $shi_l = number_format($shi_c);
        // リニア工作仕掛
    $query = sprintf("select sum(kin) from act_invent_gross_average_history where pl_bs_ym=%d and 
        note like 'リニア工作仕掛%%'", $yyyymm);
    getUniResult($query, $kshi_l);
    $kshi_l = number_format($kshi_l);
        // リニア外注仕掛
    $query = sprintf("select sum(kin) from act_invent_gross_average_history where pl_bs_ym=%d and 
        note like 'リニア外注仕掛%%'", $yyyymm);
    getUniResult($query, $gai_l);
    $gai_l = number_format($gai_l);
        // リニア検査仕掛
    $query = sprintf("select sum(kin) from act_invent_gross_average_history where pl_bs_ym=%d and 
        note like 'リニア検査仕掛%%'", $yyyymm);
    getUniResult($query, $ken_l);
    $ken_l = number_format($ken_l);
        // リニアＣＣ部品
    $query = sprintf("select sum(kin) from act_invent_gross_average_history where pl_bs_ym=%d and 
        note like 'リニアＣＣ部品%%'", $yyyymm);
    getUniResult($query, $cc_l);
    $cc_l = number_format($cc_l);
        // リニア資材部品
    $query = sprintf("select sum(kin) from act_invent_gross_average_history where pl_bs_ym=%d and 
        note like 'リニア資材部品%%'", $yyyymm);
    getUniResult($query, $shi_l);
    $shi_l = number_format($shi_l);
        // リニア組立仕掛
    $query = sprintf("select sum(kin) from act_invent_gross_average_history where pl_bs_ym=%d and 
        note like 'リニア組立仕掛%%'", $yyyymm);
    getUniResult($query, $kushi_l);
    $kushi_l = number_format($kushi_l);
        // リニア部品計
    $buhin_l = number_format($d_zai_l + $d_kumi_l + $d_kou_l + $d_kan_l);
        // リニア在庫計
    $zaiko_l = number_format($d_zai_all_l + $d_kumi_all_l + $d_kou_all_l + $d_kan_all_l);
    ////////// 全体在庫金額合計
    $zaiko = number_format($d_zai_all_c + $d_kumi_all_c + $d_kou_all_c + $d_kan_all_c + $d_zai_all_l + $d_kumi_all_l + $d_kou_all_l + $d_kan_all_l);
} else {                            // 登録処理  トランザクションで更新しているためレコード有り無しのチェックのみ
    $query = sprintf("select kin from act_invent_gross_average_history where pl_bs_ym=%d order by id ASC", $yyyymm);
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
        for ($i = 0; $i < 56; $i++) {
            $query = sprintf("insert into act_invent_gross_average_history (pl_bs_ym, kin, note, id) values (%d, %d, '%s', %d)", $yyyymm, $invent[$i], $item[$i], $i);
            if (query_affected_trans($con, $query) <= 0) {
                $_SESSION["s_sysmsg"] .= sprintf("%sの新規登録に失敗<br>第 %d期 %d月", $item[$i], $ki, $tuki);
                query_affected_trans($con, "rollback");     // transaction rollback
                header("Location: $current_script");
                exit();
            }
        }
        /////////// commit トランザクション終了
        query_affected_trans($con, "commit");
        $_SESSION["s_sysmsg"] .= sprintf("<font color='yellow'>第%d期 %d月 総平均 期末棚卸高 新規 登録完了</font>",$ki,$tuki);
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
        for ($i = 0; $i < 56; $i++) {
            $query = sprintf("update act_invent_gross_average_history set kin=%d where pl_bs_ym=%d and note='%s'", $invent[$i], $yyyymm, $item[$i]);
            if (query_affected_trans($con, $query) <= 0) {
                $_SESSION["s_sysmsg"] .= sprintf("%sのUPDATEに失敗<br>第 %d期 %d月", $item[$i], $ki, $tuki);
                query_affected_trans($con, "rollback");     // transaction rollback
                header("Location: $current_script");
                exit();
            }
        }
        /////////// commit トランザクション終了
        query_affected_trans($con, "commit");
        $_SESSION["s_sysmsg"] .= sprintf("<font color='yellow'>第%d期 %d月 総平均 期末棚卸高 変更 完了</font>",$ki,$tuki);
    }
    ////////// 部品計の取得
    $query = sprintf("select sum(kin) from act_invent_gross_average_history where pl_bs_ym=%d and 
        note like 'カプラ%%材料費' and note<>'カプラ組立仕掛材料費'", $yyyymm);
    getUniResult($query, $d_zai_c);
    $zai_c = number_format($d_zai_c);
    $query = sprintf("select sum(kin) from act_invent_gross_average_history where pl_bs_ym=%d and 
        note like 'カプラ%%組立費' and note<>'カプラ組立仕掛組立費'", $yyyymm);
    getUniResult($query, $d_kumi_c);
    $kumi_c = number_format($d_kumi_c);
    $query = sprintf("select sum(kin) from act_invent_gross_average_history where pl_bs_ym=%d and 
        note like 'カプラ%%工作費' and note<>'カプラ組立仕掛工作費'", $yyyymm);
    getUniResult($query, $d_kou_c);
    $kou_c = number_format($d_kou_c);
    $query = sprintf("select sum(kin) from act_invent_gross_average_history where pl_bs_ym=%d and 
        note like 'カプラ%%間接費' and note<>'カプラ組立仕掛間接費'", $yyyymm);
    getUniResult($query, $d_kan_c);
    $kan_c = number_format($d_kan_c);
    /********** ここからリニア **********/
    $query = sprintf("select sum(kin) from act_invent_gross_average_history where pl_bs_ym=%d and 
        note like 'リニア%%材料費' and note<>'リニア組立仕掛材料費'", $yyyymm);
    getUniResult($query, $d_zai_l);
    $zai_l = number_format($d_zai_l);
    $query = sprintf("select sum(kin) from act_invent_gross_average_history where pl_bs_ym=%d and 
        note like 'リニア%%組立費' and note<>'リニア組立仕掛組立費'", $yyyymm);
    getUniResult($query, $d_kumi_l);
    $kumi_l = number_format($d_kumi_l);
    $query = sprintf("select sum(kin) from act_invent_gross_average_history where pl_bs_ym=%d and 
        note like 'リニア%%工作費' and note<>'リニア組立仕掛工作費'", $yyyymm);
    getUniResult($query, $d_kou_l);
    $kou_l = number_format($d_kou_l);
    $query = sprintf("select sum(kin) from act_invent_gross_average_history where pl_bs_ym=%d and 
        note like 'リニア%%間接費' and note<>'リニア組立仕掛間接費'", $yyyymm);
    getUniResult($query, $d_kan_l);
    $kan_l = number_format($d_kan_l);
    ////////// 在庫計の取得
    $query = sprintf("select sum(kin) from act_invent_gross_average_history where pl_bs_ym=%d and 
        note like 'カプラ%%材料費'", $yyyymm);
    getUniResult($query, $d_zai_all_c);
    $zai_all_c = number_format($d_zai_all_c);
    $query = sprintf("select sum(kin) from act_invent_gross_average_history where pl_bs_ym=%d and 
        note like 'カプラ%%組立費'", $yyyymm);
    getUniResult($query, $d_kumi_all_c);
    $kumi_all_c = number_format($d_kumi_all_c);
    $query = sprintf("select sum(kin) from act_invent_gross_average_history where pl_bs_ym=%d and 
        note like 'カプラ%%工作費'", $yyyymm);
    getUniResult($query, $d_kou_all_c);
    $kou_all_c = number_format($d_kou_all_c);
    $query = sprintf("select sum(kin) from act_invent_gross_average_history where pl_bs_ym=%d and 
        note like 'カプラ%%間接費'", $yyyymm);
    getUniResult($query, $d_kan_all_c);
    $kan_all_c = number_format($d_kan_all_c);
    /********** ここからリニア *********/
    $query = sprintf("select sum(kin) from act_invent_gross_average_history where pl_bs_ym=%d and 
        note like 'リニア%%材料費'", $yyyymm);
    getUniResult($query, $d_zai_all_l);
    $zai_all_l = number_format($d_zai_all_l);
    $query = sprintf("select sum(kin) from act_invent_gross_average_history where pl_bs_ym=%d and 
        note like 'リニア%%組立費'", $yyyymm);
    getUniResult($query, $d_kumi_all_l);
    $kumi_all_l = number_format($d_kumi_all_l);
    $query = sprintf("select sum(kin) from act_invent_gross_average_history where pl_bs_ym=%d and 
        note like 'リニア%%工作費'", $yyyymm);
    getUniResult($query, $d_kou_all_l);
    $kou_all_l = number_format($d_kou_all_l);
    $query = sprintf("select sum(kin) from act_invent_gross_average_history where pl_bs_ym=%d and 
        note like 'リニア%%間接費'", $yyyymm);
    getUniResult($query, $d_kan_all_l);
    $kan_all_l = number_format($d_kan_all_l);
    ////////// 横の小計 仕掛品目毎
        // カプラ原材料
    $query = sprintf("select sum(kin) from act_invent_gross_average_history where pl_bs_ym=%d and 
        note like 'カプラ原材料%%'", $yyyymm);
    getUniResult($query, $gen_c);
    $gen_c = number_format($gen_c);
        // カプラ資材部品
    $query = sprintf("select sum(kin) from act_invent_gross_average_history where pl_bs_ym=%d and 
        note like 'カプラ資材部品%%'", $yyyymm);
    getUniResult($query, $shi_c);
    $shi_c = number_format($shi_c);
        // カプラ工作仕掛
    $query = sprintf("select sum(kin) from act_invent_gross_average_history where pl_bs_ym=%d and 
        note like 'カプラ工作仕掛%%'", $yyyymm);
    getUniResult($query, $kshi_c);
    $kshi_c = number_format($kshi_c);
        // カプラ外注仕掛
    $query = sprintf("select sum(kin) from act_invent_gross_average_history where pl_bs_ym=%d and 
        note like 'カプラ外注仕掛%%'", $yyyymm);
    getUniResult($query, $gai_c);
    $gai_c = number_format($gai_c);
        // カプラ検査仕掛
    $query = sprintf("select sum(kin) from act_invent_gross_average_history where pl_bs_ym=%d and 
        note like 'カプラ検査仕掛%%'", $yyyymm);
    getUniResult($query, $ken_c);
    $ken_c = number_format($ken_c);
        // カプラＣＣ部品
    $query = sprintf("select sum(kin) from act_invent_gross_average_history where pl_bs_ym=%d and 
        note like 'カプラＣＣ部品%%'", $yyyymm);
    getUniResult($query, $cc_c);
    $cc_c = number_format($cc_c);
        // カプラ資材部品
    $query = sprintf("select sum(kin) from act_invent_gross_average_history where pl_bs_ym=%d and 
        note like 'カプラ資材部品%%'", $yyyymm);
    getUniResult($query, $shi_c);
    $shi_c = number_format($shi_c);
        // カプラ組立仕掛
    $query = sprintf("select sum(kin) from act_invent_gross_average_history where pl_bs_ym=%d and 
        note like 'カプラ組立仕掛%%'", $yyyymm);
    getUniResult($query, $kushi_c);
    $kushi_c = number_format($kushi_c);
        // カプラ部品計
    $buhin_c = number_format($d_zai_c + $d_kumi_c + $d_kou_c + $d_kan_c);
        // カプラ在庫計
    $zaiko_c = number_format($d_zai_all_c + $d_kumi_all_c + $d_kou_all_c + $d_kan_all_c);
    /*********** ここからリニア **********/
        // リニア原材料
    $query = sprintf("select sum(kin) from act_invent_gross_average_history where pl_bs_ym=%d and 
        note like 'リニア原材料%%'", $yyyymm);
    getUniResult($query, $gen_l);
    $gen_l = number_format($gen_l);
        // リニア資材部品
    $query = sprintf("select sum(kin) from act_invent_gross_average_history where pl_bs_ym=%d and 
        note like 'リニア資材部品%%'", $yyyymm);
    getUniResult($query, $shi_l);
    $shi_l = number_format($shi_c);
        // リニア工作仕掛
    $query = sprintf("select sum(kin) from act_invent_gross_average_history where pl_bs_ym=%d and 
        note like 'リニア工作仕掛%%'", $yyyymm);
    getUniResult($query, $kshi_l);
    $kshi_l = number_format($kshi_l);
        // リニア外注仕掛
    $query = sprintf("select sum(kin) from act_invent_gross_average_history where pl_bs_ym=%d and 
        note like 'リニア外注仕掛%%'", $yyyymm);
    getUniResult($query, $gai_l);
    $gai_l = number_format($gai_l);
        // リニア検査仕掛
    $query = sprintf("select sum(kin) from act_invent_gross_average_history where pl_bs_ym=%d and 
        note like 'リニア検査仕掛%%'", $yyyymm);
    getUniResult($query, $ken_l);
    $ken_l = number_format($ken_l);
        // リニアＣＣ部品
    $query = sprintf("select sum(kin) from act_invent_gross_average_history where pl_bs_ym=%d and 
        note like 'リニアＣＣ部品%%'", $yyyymm);
    getUniResult($query, $cc_l);
    $cc_l = number_format($cc_l);
        // リニア資材部品
    $query = sprintf("select sum(kin) from act_invent_gross_average_history where pl_bs_ym=%d and 
        note like 'リニア資材部品%%'", $yyyymm);
    getUniResult($query, $shi_l);
    $shi_l = number_format($shi_l);
        // リニア組立仕掛
    $query = sprintf("select sum(kin) from act_invent_gross_average_history where pl_bs_ym=%d and 
        note like 'リニア組立仕掛%%'", $yyyymm);
    getUniResult($query, $kushi_l);
    $kushi_l = number_format($kushi_l);
        // リニア部品計
    $buhin_l = number_format($d_zai_l + $d_kumi_l + $d_kou_l + $d_kan_l);
        // リニア在庫計
    $zaiko_l = number_format($d_zai_all_l + $d_kumi_all_l + $d_kou_all_l + $d_kan_all_l);
    ////////// 全体在庫金額合計
    $zaiko = number_format($d_zai_all_c + $d_kumi_all_c + $d_kou_all_c + $d_kan_all_c + $d_zai_all_l + $d_kumi_all_l + $d_kou_all_l + $d_kan_all_l);
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
                <th colspan='2'>　</th><th bgcolor='#ffff94'>材料費</th><th bgcolor='#ffff94'>組立費</th>
                <th bgcolor='#ffff94'>工作費</th><th bgcolor='#ffff94'>間接費</th><th bgcolor='#ffff94'>合　計</th>
                <tr>
                    <td align='center' width='10' rowspan='9' class='pt12b'>カプラ</td>
                    <td align='center' bgcolor='#e6e6e6' class='pt11b' width='110'>原材料</td>
                    <td align='center' bgcolor='#e6e6e6' width='110'>
                        <input type='text' name='invent[]' size='11' maxlength='11' value='<?php echo $invent[0] ?>' class='rightb' onChange='return isDigit(value);'>
                    </td>
                    <td align='center' bgcolor='#e6e6e6' width='110'>
                        <input type='text' name='invent[]' size='11' maxlength='11' value='<?php echo $invent[1] ?>' class='rightb' onChange='return isDigit(value);'>
                    </td>
                    <td align='center' bgcolor='#e6e6e6' width='110'>
                        <input type='text' name='invent[]' size='11' maxlength='11' value='<?php echo $invent[2] ?>' class='rightb' onChange='return isDigit(value);'>
                    </td>
                    <td align='center' bgcolor='#e6e6e6' width='110'>
                        <input type='text' name='invent[]' size='11' maxlength='11' value='<?php echo $invent[3] ?>' class='rightb' onChange='return isDigit(value);'>
                    </td>
                    <td align='right' bgcolor='#ceffce' class='pt12b' width='110'><?php echo $gen_c ?></td>
                </tr>
                <tr>
                    <td align='center' bgcolor='white' class='pt11b'>資材部品</td>
                    <td align='center' bgcolor='white'>
                        <input type='text' name='invent[]' size='11' maxlength='11' value='<?php echo $invent[4] ?>' class='right' onChange='return isDigit(value);'>
                    </td>
                    <td align='center' bgcolor='white'>
                        <input type='text' name='invent[]' size='11' maxlength='11' value='<?php echo $invent[5] ?>' class='right' onChange='return isDigit(value);'>
                    </td>
                    <td align='center' bgcolor='white'>
                        <input type='text' name='invent[]' size='11' maxlength='11' value='<?php echo $invent[6] ?>' class='right' onChange='return isDigit(value);'>
                    </td>
                    <td align='center' bgcolor='white'>
                        <input type='text' name='invent[]' size='11' maxlength='11' value='<?php echo $invent[7] ?>' class='right' onChange='return isDigit(value);'>
                    </td>
                    <td align='right' bgcolor='#ceffce' class='pt12b' width='110'><?php echo $shi_c ?></td>
                </tr>
                <tr>
                    <td align='center' bgcolor='#e6e6e6' class='pt11b'>工作仕掛</td>
                    <td align='center' bgcolor='#e6e6e6'>
                        <input type='text' name='invent[]' size='11' maxlength='11' value='<?php echo $invent[8] ?>' class='rightb' onChange='return isDigit(value);'>
                    </td>
                    <td align='center' bgcolor='#e6e6e6'>
                        <input type='text' name='invent[]' size='11' maxlength='11' value='<?php echo $invent[9] ?>' class='rightb' onChange='return isDigit(value);'>
                    </td>
                    <td align='center' bgcolor='#e6e6e6'>
                        <input type='text' name='invent[]' size='11' maxlength='11' value='<?php echo $invent[10] ?>' class='rightb' onChange='return isDigit(value);'>
                    </td>
                    <td align='center' bgcolor='#e6e6e6'>
                        <input type='text' name='invent[]' size='11' maxlength='11' value='<?php echo $invent[11] ?>' class='rightb' onChange='return isDigit(value);'>
                    </td>
                    <td align='right' bgcolor='#ceffce' class='pt12b' width='110'><?php echo $kshi_c ?></td>
                </tr>
                <tr>
                    <td align='center' bgcolor='white' class='pt11b'>外注仕掛</td>
                    <td align='center' bgcolor='white'>
                        <input type='text' name='invent[]' size='11' maxlength='11' value='<?php echo $invent[12] ?>' class='right' onChange='return isDigit(value);'>
                    </td>
                    <td align='center' bgcolor='white'>
                        <input type='text' name='invent[]' size='11' maxlength='11' value='<?php echo $invent[13] ?>' class='right' onChange='return isDigit(value);'>
                    </td>
                    <td align='center' bgcolor='white'>
                        <input type='text' name='invent[]' size='11' maxlength='11' value='<?php echo $invent[14] ?>' class='right' onChange='return isDigit(value);'>
                    </td>
                    <td align='center' bgcolor='white'>
                        <input type='text' name='invent[]' size='11' maxlength='11' value='<?php echo $invent[15] ?>' class='right' onChange='return isDigit(value);'>
                    </td>
                    <td align='right' bgcolor='#ceffce' class='pt12b' width='110'><?php echo $gai_c ?></td>
                </tr>
                <tr>
                    <td align='center' bgcolor='#e6e6e6' class='pt11b'>検査仕掛</td>
                    <td align='center' bgcolor='#e6e6e6'>
                        <input type='text' name='invent[]' size='11' maxlength='11' value='<?php echo $invent[16] ?>' class='rightb' onChange='return isDigit(value);'>
                    </td>
                    <td align='center' bgcolor='#e6e6e6'>
                        <input type='text' name='invent[]' size='11' maxlength='11' value='<?php echo $invent[17] ?>' class='rightb' onChange='return isDigit(value);'>
                    </td>
                    <td align='center' bgcolor='#e6e6e6'>
                        <input type='text' name='invent[]' size='11' maxlength='11' value='<?php echo $invent[18] ?>' class='rightb' onChange='return isDigit(value);'>
                    </td>
                    <td align='center' bgcolor='#e6e6e6'>
                        <input type='text' name='invent[]' size='11' maxlength='11' value='<?php echo $invent[19] ?>' class='rightb' onChange='return isDigit(value);'>
                    </td>
                    <td align='right' bgcolor='#ceffce' class='pt12b' width='110'><?php echo $ken_c ?></td>
                </tr>
                <tr>
                    <td align='center' bgcolor='white' class='pt11b'>ＣＣ部品</td>
                    <td align='center' bgcolor='white'>
                        <input type='text' name='invent[]' size='11' maxlength='11' value='<?php echo $invent[20] ?>' class='right' onChange='return isDigit(value);'>
                    </td>
                    <td align='center' bgcolor='white'>
                        <input type='text' name='invent[]' size='11' maxlength='11' value='<?php echo $invent[21] ?>' class='right' onChange='return isDigit(value);'>
                    </td>
                    <td align='center' bgcolor='white'>
                        <input type='text' name='invent[]' size='11' maxlength='11' value='<?php echo $invent[22] ?>' class='right' onChange='return isDigit(value);'>
                    </td>
                    <td align='center' bgcolor='white'>
                        <input type='text' name='invent[]' size='11' maxlength='11' value='<?php echo $invent[23] ?>' class='right' onChange='return isDigit(value);'>
                    </td>
                    <td align='right' bgcolor='#ceffce' class='pt12b' width='110'><?php echo $cc_c ?></td>
                </tr>
                <tr>
                    <td align='center' bgcolor='#ceffce' class='pt11b'>部品計</td>
                    <td align='right' bgcolor='#ceffce' class= 'pt12b'><?php echo $zai_c ?></td>
                    <td align='right' bgcolor='#ceffce' class= 'pt12b'><?php echo $kumi_c ?></td>
                    <td align='right' bgcolor='#ceffce' class= 'pt12b'><?php echo $kou_c ?></td>
                    <td align='right' bgcolor='#ceffce' class= 'pt12b'><?php echo $kan_c ?></td>
                    <td align='right' bgcolor='#ceffce' class='pt12b' width='110'><?php echo $buhin_c ?></td>
                </tr>
                <tr>
                    <td align='center' bgcolor='white' class='pt11b'>組立仕掛</td>
                    <td align='center' bgcolor='white'>
                        <input type='text' name='invent[]' size='11' maxlength='11' value='<?php echo $invent[24] ?>' class='right' onChange='return isDigit(value);'>
                    </td>
                    <td align='center' bgcolor='white'>
                        <input type='text' name='invent[]' size='11' maxlength='11' value='<?php echo $invent[25] ?>' class='right' onChange='return isDigit(value);'>
                    </td>
                    <td align='center' bgcolor='white'>
                        <input type='text' name='invent[]' size='11' maxlength='11' value='<?php echo $invent[26] ?>' class='right' onChange='return isDigit(value);'>
                    </td>
                    <td align='center' bgcolor='white'>
                        <input type='text' name='invent[]' size='11' maxlength='11' value='<?php echo $invent[27] ?>' class='right' onChange='return isDigit(value);'>
                    </td>
                    <td align='right' bgcolor='#ceffce' class='pt12b' width='110'><?php echo $kushi_c ?></td>
                </tr>
                <tr>
                    <td align='center' bgcolor='#ceffce' class='pt11b'>在庫計</td>
                    <td align='right' bgcolor='#ceffce' class='pt12b'><?php echo $zai_all_c ?></td>
                    <td align='right' bgcolor='#ceffce' class='pt12b'><?php echo $kumi_all_c ?></td>
                    <td align='right' bgcolor='#ceffce' class='pt12b'><?php echo $kou_all_c ?></td>
                    <td align='right' bgcolor='#ceffce' class='pt12b'><?php echo $kan_all_c ?></td>
                    <td align='right' bgcolor='#ceffce' class='pt12b' width='110'><?php echo $zaiko_c ?></td>
                </tr>
                <tr>
                    <td align='center' width='10' rowspan='9' class='pt12b'>リニア</td>
                    <td align='center' bgcolor='#e6e6e6' class='pt11b'>原材料</td>
                    <td align='center' bgcolor='#e6e6e6'>
                        <input type='text' name='invent[]' size='11' maxlength='11' value='<?php echo $invent[28] ?>' class='rightb' onChange='return isDigit(value);'>
                    </td>
                    <td align='center' bgcolor='#e6e6e6'>
                        <input type='text' name='invent[]' size='11' maxlength='11' value='<?php echo $invent[29] ?>' class='rightb' onChange='return isDigit(value);'>
                    </td>
                    <td align='center' bgcolor='#e6e6e6'>
                        <input type='text' name='invent[]' size='11' maxlength='11' value='<?php echo $invent[30] ?>' class='rightb' onChange='return isDigit(value);'>
                    </td>
                    <td align='center' bgcolor='#e6e6e6'>
                        <input type='text' name='invent[]' size='11' maxlength='11' value='<?php echo $invent[31] ?>' class='rightb' onChange='return isDigit(value);'>
                    </td>
                    <td align='right' bgcolor='#ceffce' class='pt12b' width='110'><?php echo $gen_l ?></td>
                </tr>
                <tr>
                    <td align='center' bgcolor='white' class='pt11b'>資材部品</td>
                    <td align='center' bgcolor='white'>
                        <input type='text' name='invent[]' size='11' maxlength='11' value='<?php echo $invent[32] ?>' class='right' onChange='return isDigit(value);'>
                    </td>
                    <td align='center' bgcolor='white'>
                        <input type='text' name='invent[]' size='11' maxlength='11' value='<?php echo $invent[33] ?>' class='right' onChange='return isDigit(value);'>
                    </td>
                    <td align='center' bgcolor='white'>
                        <input type='text' name='invent[]' size='11' maxlength='11' value='<?php echo $invent[34] ?>' class='right' onChange='return isDigit(value);'>
                    </td>
                    <td align='center' bgcolor='white'>
                        <input type='text' name='invent[]' size='11' maxlength='11' value='<?php echo $invent[35] ?>' class='right' onChange='return isDigit(value);'>
                    </td>
                    <td align='right' bgcolor='#ceffce' class='pt12b' width='110'><?php echo $shi_l ?></td>
                </tr>
                <tr>
                    <td align='center' bgcolor='#e6e6e6' class='pt11b'>工作仕掛</td>
                    <td align='center' bgcolor='#e6e6e6'>
                        <input type='text' name='invent[]' size='11' maxlength='11' value='<?php echo $invent[36] ?>' class='rightb' onChange='return isDigit(value);'>
                    </td>
                    <td align='center' bgcolor='#e6e6e6'>
                        <input type='text' name='invent[]' size='11' maxlength='11' value='<?php echo $invent[37] ?>' class='rightb' onChange='return isDigit(value);'>
                    </td>
                    <td align='center' bgcolor='#e6e6e6'>
                        <input type='text' name='invent[]' size='11' maxlength='11' value='<?php echo $invent[38] ?>' class='rightb' onChange='return isDigit(value);'>
                    </td>
                    <td align='center' bgcolor='#e6e6e6'>
                        <input type='text' name='invent[]' size='11' maxlength='11' value='<?php echo $invent[39] ?>' class='rightb' onChange='return isDigit(value);'>
                    </td>
                    <td align='right' bgcolor='#ceffce' class='pt12b' width='110'><?php echo $kshi_l ?></td>
                </tr>
                <tr>
                    <td align='center' bgcolor='white' class='pt11b'>外注仕掛</td>
                    <td align='center' bgcolor='white'>
                        <input type='text' name='invent[]' size='11' maxlength='11' value='<?php echo $invent[40] ?>' class='right' onChange='return isDigit(value);'>
                    </td>
                    <td align='center' bgcolor='white'>
                        <input type='text' name='invent[]' size='11' maxlength='11' value='<?php echo $invent[41] ?>' class='right' onChange='return isDigit(value);'>
                    </td>
                    <td align='center' bgcolor='white'>
                        <input type='text' name='invent[]' size='11' maxlength='11' value='<?php echo $invent[42] ?>' class='right' onChange='return isDigit(value);'>
                    </td>
                    <td align='center' bgcolor='white'>
                        <input type='text' name='invent[]' size='11' maxlength='11' value='<?php echo $invent[43] ?>' class='right' onChange='return isDigit(value);'>
                    </td>
                    <td align='right' bgcolor='#ceffce' class='pt12b' width='110'><?php echo $gai_l ?></td>
                </tr>
                <tr>
                    <td align='center' bgcolor='#e6e6e6' class='pt11b'>検査仕掛</td>
                    <td align='center' bgcolor='#e6e6e6'>
                        <input type='text' name='invent[]' size='11' maxlength='11' value='<?php echo $invent[44] ?>' class='rightb' onChange='return isDigit(value);'>
                    </td>
                    <td align='center' bgcolor='#e6e6e6'>
                        <input type='text' name='invent[]' size='11' maxlength='11' value='<?php echo $invent[45] ?>' class='rightb' onChange='return isDigit(value);'>
                    </td>
                    <td align='center' bgcolor='#e6e6e6'>
                        <input type='text' name='invent[]' size='11' maxlength='11' value='<?php echo $invent[46] ?>' class='rightb' onChange='return isDigit(value);'>
                    </td>
                    <td align='center' bgcolor='#e6e6e6'>
                        <input type='text' name='invent[]' size='11' maxlength='11' value='<?php echo $invent[47] ?>' class='rightb' onChange='return isDigit(value);'>
                    </td>
                    <td align='right' bgcolor='#ceffce' class='pt12b' width='110'><?php echo $ken_l ?></td>
                </tr>
                <tr>
                    <td align='center' bgcolor='white' class='pt11b'>ＣＣ部品</td>
                    <td align='center' bgcolor='white'>
                        <input type='text' name='invent[]' size='11' maxlength='11' value='<?php echo $invent[48] ?>' class='right' onChange='return isDigit(value);'>
                    </td>
                    <td align='center' bgcolor='white'>
                        <input type='text' name='invent[]' size='11' maxlength='11' value='<?php echo $invent[49] ?>' class='right' onChange='return isDigit(value);'>
                    </td>
                    <td align='center' bgcolor='white'>
                        <input type='text' name='invent[]' size='11' maxlength='11' value='<?php echo $invent[50] ?>' class='right' onChange='return isDigit(value);'>
                    </td>
                    <td align='center' bgcolor='white'>
                        <input type='text' name='invent[]' size='11' maxlength='11' value='<?php echo $invent[51] ?>' class='right' onChange='return isDigit(value);'>
                    </td>
                    <td align='right' bgcolor='#ceffce' class='pt12b' width='110'><?php echo $cc_l ?></td>
                </tr>
                <tr>
                    <td align='center' bgcolor='#ceffce' class='pt11b'>部品計</td>
                    <td align='right' bgcolor='#ceffce' class='pt12b'><?php echo $zai_l ?></td>
                    <td align='right' bgcolor='#ceffce' class='pt12b'><?php echo $kumi_l ?></td>
                    <td align='right' bgcolor='#ceffce' class='pt12b'><?php echo $kou_l ?></td>
                    <td align='right' bgcolor='#ceffce' class='pt12b'><?php echo $kan_l ?></td>
                    <td align='right' bgcolor='#ceffce' class='pt12b' width='110'><?php echo $buhin_l ?></td>
                </tr>
                <tr>
                    <td align='center' bgcolor='white' class='pt11b'>組立仕掛</td>
                    <td align='center' bgcolor='white'>
                        <input type='text' name='invent[]' size='11' maxlength='11' value='<?php echo $invent[52] ?>' class='right' onChange='return isDigit(value);'>
                    </td>
                    <td align='center' bgcolor='white'>
                        <input type='text' name='invent[]' size='11' maxlength='11' value='<?php echo $invent[53] ?>' class='right' onChange='return isDigit(value);'>
                    </td>
                    <td align='center' bgcolor='white'>
                        <input type='text' name='invent[]' size='11' maxlength='11' value='<?php echo $invent[54] ?>' class='right' onChange='return isDigit(value);'>
                    </td>
                    <td align='center' bgcolor='white'>
                        <input type='text' name='invent[]' size='11' maxlength='11' value='<?php echo $invent[55] ?>' class='right' onChange='return isDigit(value);'>
                    </td>
                    <td align='right' bgcolor='#ceffce' class='pt12b' width='110'><?php echo $kushi_l ?></td>
                </tr>
                <tr>
                    <td align='center'  bgcolor='#ceffce' class='pt11b'>在庫計</td>
                    <td align='right' bgcolor='#ceffce' class='pt12b'><?php echo $zai_all_l ?></td>
                    <td align='right' bgcolor='#ceffce' class='pt12b'><?php echo $kumi_all_l ?></td>
                    <td align='right' bgcolor='#ceffce' class='pt12b'><?php echo $kou_all_l ?></td>
                    <td align='right' bgcolor='#ceffce' class='pt12b'><?php echo $kan_all_l ?></td>
                    <td align='right' bgcolor='#ceffce' class='pt12b' width='110'><?php echo $zaiko_l ?></td>
                </tr>
                <tr>
                    <td colspan='6' align='center'>
                        <input type='submit' name='entry' value='実行' >
                    </td>
                    <td align='right' bgcolor='#ceffce' class='pt12b'><?php echo $zaiko ?></td>
                </tr>
            </table>
            </td></tr>
        </table> <!-- ダミーEnd -->
        </form>
    </center>
</body>
</html>
