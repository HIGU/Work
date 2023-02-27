<?php
//////////////////////////////////////////////////////////////////////////
// 月次 データ(ＣＬ配賦率・経費・損益計算書)のクリアー                  //
//      月次ベースを決算ベース等へ置換する場合や、やり直し等に使用      //
// 2003/10/10 Copyright(C) 2003 K.Kobayashi tnksys@nitto-kohki.co.jp    //
// 変更経歴                                                             //
// 2003/10/10 新規作成  profit_loss_clear.php?pl_table=allo_history...  //
//////////////////////////////////////////////////////////////////////////
ini_set('error_reporting',E_ALL);       // E_ALL='2047' debug 用
ini_set('display_errors','1');          // Error 表示 ON debug 用 リリース後コメント
// ini_set('implicit_flush', 'off');       // echo print で flush させない(遅くなるため) CLI CGI版
// ini_set('max_execution_time', 1200);    // 最大実行時間=20分 CLI CGI版
ob_start("ob_gzhandler");               // 出力バッファをgzip圧縮
session_start();                        // ini_set()の次に指定すること Script 最上行
require_once ('../function.php');       // define.php と pgsql.php を require_once している
require_once ('../tnk_func.php');       // TNK に依存する部分の関数を require_once している
access_log();                           // Script Name は自動取得
$_SESSION['site_index'] = 10;           // 最後のメニュー    = 99   システム管理用は９９番
$_SESSION['site_id'] = 7;               // 下位メニュー無し <= 0    テンプレートファイルは６０番
$current_script  = $_SERVER['PHP_SELF'];        // 現在実行中のスクリプト名を保存
// $url_referer     = $_SERVER["HTTP_REFERER"];    // 呼出もとのURLを保存 前のスクリプトで分岐処理をしている場合は使用しない
$url_referer     = $_SESSION['pl_referer'];     // 分岐処理前に保存されている呼出元をセットする

//////////////// 認証チェック
// if ( !isset($_SESSION['User_ID']) || !isset($_SESSION['Password']) || !isset($_SESSION['Auth']) ) {
// if ($_SESSION['Auth'] <= 2) {                // 権限レベルが２以下は拒否
if (account_group_check() == FALSE) {        // 特定のグループ以外は拒否
    $_SESSION['s_sysmsg'] = "Account Group の許可が必要です。";
    // header("Location: http:" . WEB_HOST . "menu.php");   // 固定呼出元へ戻る
    header("Location: $url_referer");                   // 直前の呼出元へ戻る
    exit();
}

/********** Logic Start **********/

//////////// 月次年月の取得
if (isset($_SESSION['pl_ym'])) {
    $yyyymm = 202211
} else {
    $yyyymm = '';
}

//////////// 対象テーブルの取得
if (isset($_GET['pl_table'])) {
    if ($_GET['pl_table'] == 'allo_history') {
        $table_name = 'act_allo_history';
    } elseif ($_GET['pl_table'] == 'cl_history') {
        $table_name = 'act_cl_history';
    } elseif ($_GET['pl_table'] == 'pl_history') {
        $table_name = 'act_pl_history';
    } else {
        $_SESSION['s_sysmsg'] = '対象テーブルの指定が無効です！';
        header("Location: $url_referer");               // 直前の呼出元へ戻る
        exit();
    }
} else {
    $_SESSION['s_sysmsg'] = '対象テーブルが指定されていません！';
    header("Location: $url_referer");                   // 直前の呼出元へ戻る
    exit();
}

//////////// 指定月次年月の指定テーブルからデータ削除(実際には年月を変えてUPDATEする)
    ///// データベースとコネクションを取る
if ( ($con = db_connect()) ) {
    ///// begin トランザクション開始
    query_affected_trans($con, 'begin');
    ///// 対象データがあるかチェック
    $query_chk = "select pl_bs_ym from $table_name where pl_bs_ym = $yyyymm limit 1";
    if (getUniResTrs($con, $query_chk, $res_chk) > 0) {     // トランザクション内での 照会専用クエリー
        for ($i=1; $i<=100; $i++) {     // 前にバックアップがあるかチェック
            $query_chk = "select pl_bs_ym from $table_name where pl_bs_ym = $yyyymm" . $i . " limit 1";
            if (getUniResTrs($con, $query_chk, $res_chk) <= 0) {     // トランザクション内での 照会専用クエリー
                break;
            }
        }
        $query = "update $table_name set pl_bs_ym = $yyyymm" . $i . " where pl_bs_ym = $yyyymm";
        if (query_affected_trans($con, $query) > 0) {      // 更新用クエリーの実行
            query_affected_trans($con, 'commit');           // transaction commit
            $_SESSION['s_sysmsg'] = "<font color='yellow'>データを削除(UPDATE)しました！<br>月次年月：$yyyymm</font>";
            header("Location: $url_referer");               // 直前の呼出元へ戻る
            exit();
        } else {
            query_affected_trans($con, 'rollback');         // transaction rollback
            $_SESSION['s_sysmsg'] = "データの削除(UPDATE)に失敗！<br>月次年月：$yyyymm";
            header("Location: $url_referer");               // 直前の呼出元へ戻る
            exit();
        }
    } else {
        query_affected_trans($con, 'rollback');         // transaction rollback
        $_SESSION['s_sysmsg'] = '対象データがありません！';
        header("Location: $url_referer");               // 直前の呼出元へ戻る
        exit();
    }
} else {
    $_SESSION['s_sysmsg'] = 'データベースに接続できません！';
    header("Location: $url_referer");                   // 直前の呼出元へ戻る
    exit();
}

?>
