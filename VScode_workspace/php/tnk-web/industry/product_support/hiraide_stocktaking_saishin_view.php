<?php
//////////////////////////////////////////////////////////////////////////////
// 平出工場の最新単価金額照会                                               //
// Copyright (C) 2012-     Norihisa.Ohya norihisa_ooya@nitto-kohki.co.jp    //
// Changed history                                                          //
// 2012/05/11 Created hiraide_stocktaking_saishin_view.php                  //
//////////////////////////////////////////////////////////////////////////////
ini_set('error_reporting', E_ALL);          // E_ALL='2047' debug 用
// ini_set('display_errors', '1');             // Error 表示 ON debug 用 リリース後コメント
// ini_set('implicit_flush', 'off');           // echo print で flush させない(遅くなるため) CLI CGI版
// ini_set('max_execution_time', 1200);        // 最大実行時間=20分 CLI CGI版
ob_start('ob_gzhandler');                   // 出力バッファをgzip圧縮
session_start();                            // ini_set()の次に指定すること Script 最上行

require_once ('../../function.php');        // define.php と pgsql.php を require_once している
require_once ('../../tnk_func.php');        // TNK に依存する部分の関数を require_once している
require_once ('../../MenuHeader.php');      // TNK 全共通 menu class
access_log();                               // Script Name は自動取得

///// TNK 共用メニュークラスのインスタンスを作成
$menu = new MenuHeader(0);                  // 認証チェック0=一般以上 戻り先=TOP_MENU タイトル未設定

////////////// サイト設定
$menu->set_site(30, 99);                    // site_index=30(生産メニュー) site_id=10(買掛実績照会のグループ)
////////////// リターンアドレス設定
// $menu->set_RetUrl(INDUST_MENU);             // 通常は指定する必要はない
//////////// タイトル名(ソースのタイトル名とフォームのタイトル名)
$menu->set_title('平出工場 最新単価金額の照会');
//////////// 呼出先のaction名とアドレス設定
// $menu->set_action('総材料費明細',   INDUST . 'material/materialCost_view.php');

//////////// 一頁の行数
define('PAGE', '1000');

//////////// JavaScript Stylesheet File を必ず読み込ませる
$uniq = uniqid('target');

$e_ymd = date('Ymd');

/////////// begin トランザクション開始
if ($con = funcConnect()) {
    query_affected_trans($con, 'begin');
} else {
    $_SESSION['s_sysmsg'] .= 'funcConnect() error';
    header('Location: ' . H_WEB_HOST . $menu->out_RetUrl());
    exit();
}

//////////// 内作を除く合計金額 (科目1～5)科目6以上を除く
$query = sprintf("select
                            *
                    from
                            parts_stock_master AS m
                    WHERE m.tnk_tana LIKE '%s'
                    ", '8%');
if (($maxrows = getResultTrs($con, $query, $paya_ctoku)) <= 0) {
    $_SESSION['s_sysmsg'] .= sprintf("平出工場棚卸データがありません。");
    query_affected_trans($con, 'rollback');         // transaction rollback
    header('Location: ' . H_WEB_HOST . $menu->out_RetUrl());
    exit();
} else {
    // $sum_kin = $paya_ctoku[0][0];
    // $maxrows = $paya_ctoku[0][1];    // GROUP BY の時は集約関数は使えない
}
$query = sprintf("SELECT SUM(CASE
                    WHEN (SELECT out_id FROM parts_stock_history WHERE parts_no=m.parts_no and ent_date <= {$e_ymd} ORDER BY upd_date DESC LIMIT 1) = '1' AND (SELECT in_id FROM parts_stock_history WHERE parts_no=m.parts_no and ent_date <= {$e_ymd} ORDER BY upd_date DESC LIMIT 1)  = '2'
                        THEN UROUND(((SELECT nk_stock FROM parts_stock_history WHERE parts_no=m.parts_no and ent_date <= {$e_ymd} ORDER BY upd_date DESC LIMIT 1)  - (SELECT stock_mv FROM parts_stock_history WHERE parts_no=m.parts_no and ent_date <= {$e_ymd} ORDER BY upd_date DESC LIMIT 1) + (SELECT stock_mv FROM parts_stock_history WHERE parts_no=m.parts_no and ent_date <= {$e_ymd} ORDER BY upd_date DESC LIMIT 1) + (SELECT tnk_stock FROM parts_stock_history WHERE parts_no=m.parts_no and ent_date <= {$e_ymd} ORDER BY upd_date DESC LIMIT 1)) * h.tanka, 0)
                    WHEN (SELECT out_id FROM parts_stock_history WHERE parts_no=m.parts_no and ent_date <= {$e_ymd} ORDER BY upd_date DESC LIMIT 1) = '1'
                        THEN UROUND(((SELECT nk_stock FROM parts_stock_history WHERE parts_no=m.parts_no and ent_date <= {$e_ymd} ORDER BY upd_date DESC LIMIT 1)  - (SELECT stock_mv FROM parts_stock_history WHERE parts_no=m.parts_no and ent_date <= {$e_ymd} ORDER BY upd_date DESC LIMIT 1) + (SELECT tnk_stock FROM parts_stock_history WHERE parts_no=m.parts_no and ent_date <= {$e_ymd} ORDER BY upd_date DESC LIMIT 1)) * h.tanka, 0)
                    WHEN (SELECT out_id FROM parts_stock_history WHERE parts_no=m.parts_no and ent_date <= {$e_ymd} ORDER BY upd_date DESC LIMIT 1) = '2' AND (SELECT in_id FROM parts_stock_history WHERE parts_no=m.parts_no and ent_date <= {$e_ymd} ORDER BY upd_date DESC LIMIT 1)  = '1'
                        THEN UROUND(((SELECT tnk_stock FROM parts_stock_history WHERE parts_no=m.parts_no and ent_date <= {$e_ymd} ORDER BY upd_date DESC LIMIT 1)  - (SELECT stock_mv FROM parts_stock_history WHERE parts_no=m.parts_no and ent_date <= {$e_ymd} ORDER BY upd_date DESC LIMIT 1) + (SELECT stock_mv FROM parts_stock_history WHERE parts_no=m.parts_no and ent_date <= {$e_ymd} ORDER BY upd_date DESC LIMIT 1) + (SELECT nk_stock FROM parts_stock_history WHERE parts_no=m.parts_no and ent_date <= {$e_ymd} ORDER BY upd_date DESC LIMIT 1)) * h.tanka, 0)
                    WHEN (SELECT out_id FROM parts_stock_history WHERE parts_no=m.parts_no and ent_date <= {$e_ymd} ORDER BY upd_date DESC LIMIT 1) = '2'
                        THEN UROUND(((SELECT tnk_stock FROM parts_stock_history WHERE parts_no=m.parts_no and ent_date <= {$e_ymd} ORDER BY upd_date DESC LIMIT 1) - (SELECT stock_mv FROM parts_stock_history WHERE parts_no=m.parts_no and ent_date <= {$e_ymd} ORDER BY upd_date DESC LIMIT 1) + (SELECT nk_stock FROM parts_stock_history WHERE parts_no=m.parts_no and ent_date <= {$e_ymd} ORDER BY upd_date DESC LIMIT 1)) * h.tanka, 0)
                    WHEN (SELECT in_id FROM parts_stock_history WHERE parts_no=m.parts_no and ent_date <= {$e_ymd} ORDER BY upd_date DESC LIMIT 1)  = '1'
                        THEN UROUND(((SELECT nk_stock FROM parts_stock_history WHERE parts_no=m.parts_no and ent_date <= {$e_ymd} ORDER BY upd_date DESC LIMIT 1)  + (SELECT stock_mv FROM parts_stock_history WHERE parts_no=m.parts_no and ent_date <= {$e_ymd} ORDER BY upd_date DESC LIMIT 1) + (SELECT tnk_stock FROM parts_stock_history WHERE parts_no=m.parts_no and ent_date <= {$e_ymd} ORDER BY upd_date DESC LIMIT 1)) * h.tanka, 0)
                    WHEN (SELECT in_id FROM parts_stock_history WHERE parts_no=m.parts_no and ent_date <= {$e_ymd} ORDER BY upd_date DESC LIMIT 1)  = '2'
                        THEN UROUND(((SELECT tnk_stock FROM parts_stock_history WHERE parts_no=m.parts_no and ent_date <= {$e_ymd} ORDER BY upd_date DESC LIMIT 1) + (SELECT stock_mv FROM parts_stock_history WHERE parts_no=m.parts_no and ent_date <= {$e_ymd} ORDER BY upd_date DESC LIMIT 1) + (SELECT nk_stock FROM parts_stock_history WHERE parts_no=m.parts_no and ent_date <= {$e_ymd} ORDER BY upd_date DESC LIMIT 1)) * h.tanka, 0)
                    ELSE UROUND(((SELECT nk_stock FROM parts_stock_history WHERE parts_no=m.parts_no and ent_date <= {$e_ymd} ORDER BY upd_date DESC LIMIT 1) + (SELECT tnk_stock FROM parts_stock_history WHERE parts_no=m.parts_no and ent_date <= {$e_ymd} ORDER BY upd_date DESC LIMIT 1)) * h.tanka, 0)
                  END)                                       as 合計金額
            FROM
                parts_stock_master as m
                LEFT OUTER JOIN
                parts_stock_master_hiraide as h
                ON (m.parts_no=h.parts_no)
            WHERE m.tnk_tana LIKE '%s'
            ", '8%');
if (getUniResTrs($con, $query, $sum_kin) <= 0) {
    $_SESSION['s_sysmsg'] .= '買掛合計金額の取得が出来ません。';      // .= メッセージを追加する
    query_affected_trans($con, 'rollback');         // transaction rollback
    header('Location: ' . H_WEB_HOST . $menu->out_RetUrl());
    exit();
}
//////////// ページオフセット設定
if ( isset($_POST['forward']) ) {                       // 次頁が押された
    $_SESSION['offset'] += PAGE;
    if ($_SESSION['offset'] >= $maxrows) {
        $_SESSION['offset'] -= PAGE;
        if ($_SESSION['s_sysmsg'] == "") {
            $_SESSION['s_sysmsg'] .= "<font color='yellow'>次頁はありません。</font>";
        } else {
            $_SESSION['s_sysmsg'] .= "<br><font color='yellow'>次頁はありません。</font>";
        }
    }
} elseif ( isset($_POST['backward']) ) {                // 次頁が押された
    $_SESSION['offset'] -= PAGE;
    if ($_SESSION['offset'] < 0) {
        $_SESSION['offset'] = 0;
        if ($_SESSION['s_sysmsg'] == "") {
            $_SESSION['s_sysmsg'] .= "<font color='yellow'>前頁はありません。</font>";
        } else {
            $_SESSION['s_sysmsg'] .= "<br><font color='yellow'>前頁はありません。</font>";
        }
    }
} elseif ( isset($_GET['page_keep']) ) {               // 現在のページを維持する
    $offset = $_SESSION['offset'];
} else {
    $_SESSION['offset'] = 0;                            // 初回の場合は０で初期化
}
$offset = $_SESSION['offset'];

//////////// 表形式のデータ表示用のサンプル Query & 初期化
$page    = PAGE;
$query = "SELECT  m.parts_no as 部品番号       -- 0
                , 
                i.midsc    as 部品名         -- 1
                ,
                m.tnk_tana as 棚番           -- 2
                , 
                CASE
                    WHEN (SELECT out_id FROM parts_stock_history WHERE parts_no=m.parts_no and ent_date <= {$e_ymd} ORDER BY upd_date DESC LIMIT 1) = '1' AND (SELECT in_id FROM parts_stock_history WHERE parts_no=m.parts_no and ent_date <= {$e_ymd} ORDER BY upd_date DESC LIMIT 1)  = '2'
                        THEN
                            CASE
                                WHEN (SELECT nk_stock FROM parts_stock_history WHERE parts_no=m.parts_no and ent_date <= {$e_ymd} ORDER BY upd_date DESC LIMIT 1)  - (SELECT stock_mv FROM parts_stock_history WHERE parts_no=m.parts_no and ent_date <= {$e_ymd} ORDER BY upd_date DESC LIMIT 1) + (SELECT stock_mv FROM parts_stock_history WHERE parts_no=m.parts_no and ent_date <= {$e_ymd} ORDER BY upd_date DESC LIMIT 1) + (SELECT tnk_stock FROM parts_stock_history WHERE parts_no=m.parts_no and ent_date <= {$e_ymd} ORDER BY upd_date DESC LIMIT 1) = 0
                                    THEN 0
                                ELSE (SELECT nk_stock FROM parts_stock_history WHERE parts_no=m.parts_no and ent_date <= {$e_ymd} ORDER BY upd_date DESC LIMIT 1)  - (SELECT stock_mv FROM parts_stock_history WHERE parts_no=m.parts_no and ent_date <= {$e_ymd} ORDER BY upd_date DESC LIMIT 1) + (SELECT stock_mv FROM parts_stock_history WHERE parts_no=m.parts_no and ent_date <= {$e_ymd} ORDER BY upd_date DESC LIMIT 1) + (SELECT tnk_stock FROM parts_stock_history WHERE parts_no=m.parts_no and ent_date <= {$e_ymd} ORDER BY upd_date DESC LIMIT 1)
                            END
                    WHEN (SELECT out_id FROM parts_stock_history WHERE parts_no=m.parts_no and ent_date <= {$e_ymd} ORDER BY upd_date DESC LIMIT 1) = '1'
                        THEN
                            CASE
                                WHEN (SELECT nk_stock FROM parts_stock_history WHERE parts_no=m.parts_no and ent_date <= {$e_ymd} ORDER BY upd_date DESC LIMIT 1)  - (SELECT stock_mv FROM parts_stock_history WHERE parts_no=m.parts_no and ent_date <= {$e_ymd} ORDER BY upd_date DESC LIMIT 1) + (SELECT tnk_stock FROM parts_stock_history WHERE parts_no=m.parts_no and ent_date <= {$e_ymd} ORDER BY upd_date DESC LIMIT 1) = 0
                                    THEN 0
                                ELSE (SELECT nk_stock FROM parts_stock_history WHERE parts_no=m.parts_no and ent_date <= {$e_ymd} ORDER BY upd_date DESC LIMIT 1)  - (SELECT stock_mv FROM parts_stock_history WHERE parts_no=m.parts_no and ent_date <= {$e_ymd} ORDER BY upd_date DESC LIMIT 1) + (SELECT tnk_stock FROM parts_stock_history WHERE parts_no=m.parts_no and ent_date <= {$e_ymd} ORDER BY upd_date DESC LIMIT 1)
                            END
                    WHEN (SELECT out_id FROM parts_stock_history WHERE parts_no=m.parts_no and ent_date <= {$e_ymd} ORDER BY upd_date DESC LIMIT 1) = '2' AND (SELECT in_id FROM parts_stock_history WHERE parts_no=m.parts_no and ent_date <= {$e_ymd} ORDER BY upd_date DESC LIMIT 1)  = '1'
                        THEN
                            CASE
                                WHEN (SELECT tnk_stock FROM parts_stock_history WHERE parts_no=m.parts_no and ent_date <= {$e_ymd} ORDER BY upd_date DESC LIMIT 1)  - (SELECT stock_mv FROM parts_stock_history WHERE parts_no=m.parts_no and ent_date <= {$e_ymd} ORDER BY upd_date DESC LIMIT 1) + (SELECT stock_mv FROM parts_stock_history WHERE parts_no=m.parts_no and ent_date <= {$e_ymd} ORDER BY upd_date DESC LIMIT 1) + (SELECT nk_stock FROM parts_stock_history WHERE parts_no=m.parts_no and ent_date <= {$e_ymd} ORDER BY upd_date DESC LIMIT 1) = 0
                                    THEN 0
                                ELSE (SELECT tnk_stock FROM parts_stock_history WHERE parts_no=m.parts_no and ent_date <= {$e_ymd} ORDER BY upd_date DESC LIMIT 1)  - (SELECT stock_mv FROM parts_stock_history WHERE parts_no=m.parts_no and ent_date <= {$e_ymd} ORDER BY upd_date DESC LIMIT 1) + (SELECT stock_mv FROM parts_stock_history WHERE parts_no=m.parts_no and ent_date <= {$e_ymd} ORDER BY upd_date DESC LIMIT 1) + (SELECT nk_stock FROM parts_stock_history WHERE parts_no=m.parts_no and ent_date <= {$e_ymd} ORDER BY upd_date DESC LIMIT 1)
                            END
                    WHEN (SELECT out_id FROM parts_stock_history WHERE parts_no=m.parts_no and ent_date <= {$e_ymd} ORDER BY upd_date DESC LIMIT 1) = '2'
                        THEN
                            CASE
                                WHEN (SELECT tnk_stock FROM parts_stock_history WHERE parts_no=m.parts_no and ent_date <= {$e_ymd} ORDER BY upd_date DESC LIMIT 1) - (SELECT stock_mv FROM parts_stock_history WHERE parts_no=m.parts_no and ent_date <= {$e_ymd} ORDER BY upd_date DESC LIMIT 1) + (SELECT nk_stock FROM parts_stock_history WHERE parts_no=m.parts_no and ent_date <= {$e_ymd} ORDER BY upd_date DESC LIMIT 1) = 0
                                    THEN 0
                                ELSE (SELECT tnk_stock FROM parts_stock_history WHERE parts_no=m.parts_no and ent_date <= {$e_ymd} ORDER BY upd_date DESC LIMIT 1) - (SELECT stock_mv FROM parts_stock_history WHERE parts_no=m.parts_no and ent_date <= {$e_ymd} ORDER BY upd_date DESC LIMIT 1) + (SELECT nk_stock FROM parts_stock_history WHERE parts_no=m.parts_no and ent_date <= {$e_ymd} ORDER BY upd_date DESC LIMIT 1)
                            END
                    WHEN (SELECT in_id FROM parts_stock_history WHERE parts_no=m.parts_no and ent_date <= {$e_ymd} ORDER BY upd_date DESC LIMIT 1)  = '1'
                        THEN
                            CASE
                                WHEN (SELECT nk_stock FROM parts_stock_history WHERE parts_no=m.parts_no and ent_date <= {$e_ymd} ORDER BY upd_date DESC LIMIT 1)  + (SELECT stock_mv FROM parts_stock_history WHERE parts_no=m.parts_no and ent_date <= {$e_ymd} ORDER BY upd_date DESC LIMIT 1) + (SELECT tnk_stock FROM parts_stock_history WHERE parts_no=m.parts_no and ent_date <= {$e_ymd} ORDER BY upd_date DESC LIMIT 1) = 0
                                    THEN 0
                                ELSE (SELECT nk_stock FROM parts_stock_history WHERE parts_no=m.parts_no and ent_date <= {$e_ymd} ORDER BY upd_date DESC LIMIT 1)  + (SELECT stock_mv FROM parts_stock_history WHERE parts_no=m.parts_no and ent_date <= {$e_ymd} ORDER BY upd_date DESC LIMIT 1) + (SELECT tnk_stock FROM parts_stock_history WHERE parts_no=m.parts_no and ent_date <= {$e_ymd} ORDER BY upd_date DESC LIMIT 1)
                            END
                    WHEN (SELECT in_id FROM parts_stock_history WHERE parts_no=m.parts_no and ent_date <= {$e_ymd} ORDER BY upd_date DESC LIMIT 1)  = '2'
                        THEN
                            CASE
                                WHEN (SELECT tnk_stock FROM parts_stock_history WHERE parts_no=m.parts_no and ent_date <= {$e_ymd} ORDER BY upd_date DESC LIMIT 1) + (SELECT stock_mv FROM parts_stock_history WHERE parts_no=m.parts_no and ent_date <= {$e_ymd} ORDER BY upd_date DESC LIMIT 1) + (SELECT nk_stock FROM parts_stock_history WHERE parts_no=m.parts_no and ent_date <= {$e_ymd} ORDER BY upd_date DESC LIMIT 1) = 0
                                    THEN 0
                                ELSE (SELECT tnk_stock FROM parts_stock_history WHERE parts_no=m.parts_no and ent_date <= {$e_ymd} ORDER BY upd_date DESC LIMIT 1) + (SELECT stock_mv FROM parts_stock_history WHERE parts_no=m.parts_no and ent_date <= {$e_ymd} ORDER BY upd_date DESC LIMIT 1) + (SELECT nk_stock FROM parts_stock_history WHERE parts_no=m.parts_no and ent_date <= {$e_ymd} ORDER BY upd_date DESC LIMIT 1)
                            END
                    ELSE
                        CASE
                            WHEN (SELECT nk_stock FROM parts_stock_history WHERE parts_no=m.parts_no and ent_date <= {$e_ymd} ORDER BY upd_date DESC LIMIT 1) + (SELECT tnk_stock FROM parts_stock_history WHERE parts_no=m.parts_no and ent_date <= {$e_ymd} ORDER BY upd_date DESC LIMIT 1) = 0
                                THEN 0
                            ELSE (SELECT nk_stock FROM parts_stock_history WHERE parts_no=m.parts_no and ent_date <= {$e_ymd} ORDER BY upd_date DESC LIMIT 1) + (SELECT tnk_stock FROM parts_stock_history WHERE parts_no=m.parts_no and ent_date <= {$e_ymd} ORDER BY upd_date DESC LIMIT 1)
                        END
                  END                                       as 現在在庫     -- 3
                ,
                h.tanka                                     as 棚卸単価     -- 4
                , 
                CASE
                    WHEN (SELECT out_id FROM parts_stock_history WHERE parts_no=m.parts_no and ent_date <= {$e_ymd} ORDER BY upd_date DESC LIMIT 1) = '1' AND (SELECT in_id FROM parts_stock_history WHERE parts_no=m.parts_no and ent_date <= {$e_ymd} ORDER BY upd_date DESC LIMIT 1)  = '2'
                        THEN
                            CASE
                                WHEN UROUND(((SELECT nk_stock FROM parts_stock_history WHERE parts_no=m.parts_no and ent_date <= {$e_ymd} ORDER BY upd_date DESC LIMIT 1)  - (SELECT stock_mv FROM parts_stock_history WHERE parts_no=m.parts_no and ent_date <= {$e_ymd} ORDER BY upd_date DESC LIMIT 1) + (SELECT stock_mv FROM parts_stock_history WHERE parts_no=m.parts_no and ent_date <= {$e_ymd} ORDER BY upd_date DESC LIMIT 1) + (SELECT tnk_stock FROM parts_stock_history WHERE parts_no=m.parts_no and ent_date <= {$e_ymd} ORDER BY upd_date DESC LIMIT 1)) * h.tanka, 0) = 0
                                    THEN 0
                                ELSE UROUND(((SELECT nk_stock FROM parts_stock_history WHERE parts_no=m.parts_no and ent_date <= {$e_ymd} ORDER BY upd_date DESC LIMIT 1)  - (SELECT stock_mv FROM parts_stock_history WHERE parts_no=m.parts_no and ent_date <= {$e_ymd} ORDER BY upd_date DESC LIMIT 1) + (SELECT stock_mv FROM parts_stock_history WHERE parts_no=m.parts_no and ent_date <= {$e_ymd} ORDER BY upd_date DESC LIMIT 1) + (SELECT tnk_stock FROM parts_stock_history WHERE parts_no=m.parts_no and ent_date <= {$e_ymd} ORDER BY upd_date DESC LIMIT 1)) * h.tanka, 0)
                            END
                    WHEN (SELECT out_id FROM parts_stock_history WHERE parts_no=m.parts_no and ent_date <= {$e_ymd} ORDER BY upd_date DESC LIMIT 1) = '1'
                        THEN
                            CASE
                                WHEN UROUND(((SELECT nk_stock FROM parts_stock_history WHERE parts_no=m.parts_no and ent_date <= {$e_ymd} ORDER BY upd_date DESC LIMIT 1)  - (SELECT stock_mv FROM parts_stock_history WHERE parts_no=m.parts_no and ent_date <= {$e_ymd} ORDER BY upd_date DESC LIMIT 1) + (SELECT tnk_stock FROM parts_stock_history WHERE parts_no=m.parts_no and ent_date <= {$e_ymd} ORDER BY upd_date DESC LIMIT 1)) * h.tanka, 0) = 0
                                    THEN 0
                                ELSE UROUND(((SELECT nk_stock FROM parts_stock_history WHERE parts_no=m.parts_no and ent_date <= {$e_ymd} ORDER BY upd_date DESC LIMIT 1)  - (SELECT stock_mv FROM parts_stock_history WHERE parts_no=m.parts_no and ent_date <= {$e_ymd} ORDER BY upd_date DESC LIMIT 1) + (SELECT tnk_stock FROM parts_stock_history WHERE parts_no=m.parts_no and ent_date <= {$e_ymd} ORDER BY upd_date DESC LIMIT 1)) * h.tanka, 0)
                            END
                    WHEN (SELECT out_id FROM parts_stock_history WHERE parts_no=m.parts_no and ent_date <= {$e_ymd} ORDER BY upd_date DESC LIMIT 1) = '2' AND (SELECT in_id FROM parts_stock_history WHERE parts_no=m.parts_no and ent_date <= {$e_ymd} ORDER BY upd_date DESC LIMIT 1)  = '1'
                        THEN
                            CASE
                                WHEN UROUND(((SELECT tnk_stock FROM parts_stock_history WHERE parts_no=m.parts_no and ent_date <= {$e_ymd} ORDER BY upd_date DESC LIMIT 1)  - (SELECT stock_mv FROM parts_stock_history WHERE parts_no=m.parts_no and ent_date <= {$e_ymd} ORDER BY upd_date DESC LIMIT 1) + (SELECT stock_mv FROM parts_stock_history WHERE parts_no=m.parts_no and ent_date <= {$e_ymd} ORDER BY upd_date DESC LIMIT 1) + (SELECT nk_stock FROM parts_stock_history WHERE parts_no=m.parts_no and ent_date <= {$e_ymd} ORDER BY upd_date DESC LIMIT 1)) * h.tanka, 0) = 0
                                    THEN 0
                                ELSE UROUND(((SELECT tnk_stock FROM parts_stock_history WHERE parts_no=m.parts_no and ent_date <= {$e_ymd} ORDER BY upd_date DESC LIMIT 1)  - (SELECT stock_mv FROM parts_stock_history WHERE parts_no=m.parts_no and ent_date <= {$e_ymd} ORDER BY upd_date DESC LIMIT 1) + (SELECT stock_mv FROM parts_stock_history WHERE parts_no=m.parts_no and ent_date <= {$e_ymd} ORDER BY upd_date DESC LIMIT 1) + (SELECT nk_stock FROM parts_stock_history WHERE parts_no=m.parts_no and ent_date <= {$e_ymd} ORDER BY upd_date DESC LIMIT 1)) * h.tanka, 0)
                            END
                    WHEN (SELECT out_id FROM parts_stock_history WHERE parts_no=m.parts_no and ent_date <= {$e_ymd} ORDER BY upd_date DESC LIMIT 1) = '2'
                        THEN
                            CASE
                                WHEN UROUND(((SELECT tnk_stock FROM parts_stock_history WHERE parts_no=m.parts_no and ent_date <= {$e_ymd} ORDER BY upd_date DESC LIMIT 1) - (SELECT stock_mv FROM parts_stock_history WHERE parts_no=m.parts_no and ent_date <= {$e_ymd} ORDER BY upd_date DESC LIMIT 1) + (SELECT nk_stock FROM parts_stock_history WHERE parts_no=m.parts_no and ent_date <= {$e_ymd} ORDER BY upd_date DESC LIMIT 1)) * h.tanka, 0) = 0
                                    THEN 0
                                ELSE UROUND(((SELECT tnk_stock FROM parts_stock_history WHERE parts_no=m.parts_no and ent_date <= {$e_ymd} ORDER BY upd_date DESC LIMIT 1) - (SELECT stock_mv FROM parts_stock_history WHERE parts_no=m.parts_no and ent_date <= {$e_ymd} ORDER BY upd_date DESC LIMIT 1) + (SELECT nk_stock FROM parts_stock_history WHERE parts_no=m.parts_no and ent_date <= {$e_ymd} ORDER BY upd_date DESC LIMIT 1)) * h.tanka, 0)
                            END
                    WHEN (SELECT in_id FROM parts_stock_history WHERE parts_no=m.parts_no and ent_date <= {$e_ymd} ORDER BY upd_date DESC LIMIT 1)  = '1'
                        THEN
                            CASE
                                WHEN UROUND(((SELECT nk_stock FROM parts_stock_history WHERE parts_no=m.parts_no and ent_date <= {$e_ymd} ORDER BY upd_date DESC LIMIT 1)  + (SELECT stock_mv FROM parts_stock_history WHERE parts_no=m.parts_no and ent_date <= {$e_ymd} ORDER BY upd_date DESC LIMIT 1) + (SELECT tnk_stock FROM parts_stock_history WHERE parts_no=m.parts_no and ent_date <= {$e_ymd} ORDER BY upd_date DESC LIMIT 1)) * h.tanka, 0) = 0
                                    THEN 0
                                ELSE UROUND(((SELECT nk_stock FROM parts_stock_history WHERE parts_no=m.parts_no and ent_date <= {$e_ymd} ORDER BY upd_date DESC LIMIT 1)  + (SELECT stock_mv FROM parts_stock_history WHERE parts_no=m.parts_no and ent_date <= {$e_ymd} ORDER BY upd_date DESC LIMIT 1) + (SELECT tnk_stock FROM parts_stock_history WHERE parts_no=m.parts_no and ent_date <= {$e_ymd} ORDER BY upd_date DESC LIMIT 1)) * h.tanka, 0)
                            END
                    WHEN (SELECT in_id FROM parts_stock_history WHERE parts_no=m.parts_no and ent_date <= {$e_ymd} ORDER BY upd_date DESC LIMIT 1)  = '2'
                        THEN
                            CASE
                                WHEN UROUND(((SELECT tnk_stock FROM parts_stock_history WHERE parts_no=m.parts_no and ent_date <= {$e_ymd} ORDER BY upd_date DESC LIMIT 1) + (SELECT stock_mv FROM parts_stock_history WHERE parts_no=m.parts_no and ent_date <= {$e_ymd} ORDER BY upd_date DESC LIMIT 1) + (SELECT nk_stock FROM parts_stock_history WHERE parts_no=m.parts_no and ent_date <= {$e_ymd} ORDER BY upd_date DESC LIMIT 1)) * h.tanka, 0) = 0
                                    THEN 0
                                ELSE UROUND(((SELECT tnk_stock FROM parts_stock_history WHERE parts_no=m.parts_no and ent_date <= {$e_ymd} ORDER BY upd_date DESC LIMIT 1) + (SELECT stock_mv FROM parts_stock_history WHERE parts_no=m.parts_no and ent_date <= {$e_ymd} ORDER BY upd_date DESC LIMIT 1) + (SELECT nk_stock FROM parts_stock_history WHERE parts_no=m.parts_no and ent_date <= {$e_ymd} ORDER BY upd_date DESC LIMIT 1)) * h.tanka, 0)
                            END
                    ELSE
                        CASE
                            WHEN UROUND(((SELECT nk_stock FROM parts_stock_history WHERE parts_no=m.parts_no and ent_date <= {$e_ymd} ORDER BY upd_date DESC LIMIT 1) + (SELECT tnk_stock FROM parts_stock_history WHERE parts_no=m.parts_no and ent_date <= {$e_ymd} ORDER BY upd_date DESC LIMIT 1)) * h.tanka, 0) = 0
                                THEN 0
                            ELSE UROUND(((SELECT nk_stock FROM parts_stock_history WHERE parts_no=m.parts_no and ent_date <= {$e_ymd} ORDER BY upd_date DESC LIMIT 1) + (SELECT tnk_stock FROM parts_stock_history WHERE parts_no=m.parts_no and ent_date <= {$e_ymd} ORDER BY upd_date DESC LIMIT 1)) * h.tanka, 0)
                        END
                  END                                       as 在庫金額     -- 5
                ,
                CASE
                    WHEN h.abc_kubun = '' THEN '　'
                    ELSE h.abc_kubun
                END                                         as ランク           -- 6
                ,
                CASE
                    WHEN h.stock_id = '' THEN '　'
                    ELSE h.stock_id
                END                                         as 棚卸区分         -- 7
            FROM
                parts_stock_master as m
            LEFT OUTER JOIN
                miitem as i
            ON (m.parts_no=i.mipn)
            LEFT OUTER JOIN
                parts_stock_master_hiraide as h
            ON (m.parts_no=h.parts_no)
            WHERE m.tnk_tana LIKE '8%'
            ORDER BY m.parts_no ASC
        offset {$offset} limit {$page}
    ";
$res   = array();
$field = array();
if (($rows = getResWithFieldTrs($con, $query, $field, $res)) <= 0) {
    $_SESSION['s_sysmsg'] .= sprintf("平出工場棚卸用データがありません。");
    query_affected_trans($con, 'rollback');         // transaction rollback
    header('Location: ' . H_WEB_HOST . $menu->out_RetUrl());
    exit();
} else {
    query_affected_trans($con, 'commit');         // transaction commit
    $num = count($field);       // フィールド数取得
}

// 最新単価の取得
function get_new_cost($parts_no)
{
    $search2 = sprintf("where p.parts_no='%s' and v.vendor!='88888'", $parts_no);
    //////////// 表形式のデータ表示用のサンプル Query & 初期化
    $query = sprintf("
        select
            p.as_regdate            as 登録日,                  -- 0
            p.reg_no                as \"登録No\",              -- 1
            p.vendor                as コード,                  -- 2
            v.name                  as 発注先名,                -- 3
            CASE
                WHEN p.mtl_cond = '1' THEN '自給'
                WHEN p.mtl_cond = '2' THEN '有償'
                WHEN p.mtl_cond = '3' THEN '無償'
            END                     as 条件,                    -- 4
            CASE
                WHEN p.kubun = '1' THEN '継続'
                WHEN p.kubun = '2' THEN '暫定'
                WHEN p.kubun = '3' THEN '今回'
            END                     as 区分,                    -- 5
            p.pro_no                as 工程,                    -- 6
            p.pro_mark              as 記号,                    -- 7
            p.lot_cost              as 単価,                    -- 8
            p.lot_str               as \"LOT開始\",             -- 9
            p.lot_end               as \"LOT終了\",             --10
            p.lot_no                as \"LOT番号\"              --11
        from
            parts_cost_history as p
        left outer join
            vendor_master as v
        using (vendor)
        %s      -- ここに where句の and を挿入できる
        ORDER BY as_regdate DESC, reg_no DESC, lot_no ASC, pro_no ASC
        ", $search2);       // 共用 $search で検索
    $res      = array();
    $field    = array();
    $new_cost = 0;
    if (($rows = getResultWithField2($query, $field, $res)) <= 0) {
        $new_cost = 0;
        return $new_cost;
    } else {
        $t_no = $res[0][1];
        for ($i=0; $i<$rows; $i++) {
            if($t_no == $res[$i][1]) {
                $new_cost = $new_cost + $res[$i][8];
            } else {
                return $new_cost;
            }
        }
        return $new_cost;
    }
}

//////////// 表題の設定
$caption = "$e_ymd" . '現在　合計金額：' . number_format($sum_kin) . '　合計件数：' . number_format($maxrows);
$caption = "$e_ymd" . '現在　最新単価金額';
$menu->set_caption("{$caption}");

/////////// HTML Header を出力してキャッシュを制御
$menu->out_html_header();
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<meta http-equiv="Content-Style-Type" content="text/css">
<title><?= $menu->out_title() ?></title>
<?=$menu->out_site_java()?>
<?=$menu->out_css()?>

<!--    ファイル指定の場合
<script language='JavaScript' src='template.js?<?= $uniq ?>'></script>
-->

<script language="JavaScript">
<!--
/* 入力文字が数字かどうかチェック */
function isDigit(str) {
    var len=str.length;
    var c;
    for (i=1; i<len; i++) {
        c = str.charAt(i);
        if ((c < "0") || (c > "9")) {
            return true;
        }
    }
    return false;
}
/* 初期入力フォームのエレメントにフォーカスさせる */
function set_focus(){
    // document.body.focus();   // F2/F12キーを有効化する対応
    document.mhForm.backwardStack.focus();  // 上記はIEのみのためNN対応
}
// -->
</script>

<!-- スタイルシートのファイル指定をコメント HTMLタグ コメントは入れ子に出来ない事に注意
<link rel='stylesheet' href='template.css?<?= $uniq ?>' type='text/css' media='screen'>
-->

<style type="text/css">
<!--
.pt9 {
    font-size:      9pt;
    font-weight:    normal;
    font-family:    monospace;
}
.pt10 {
    font-size:      9pt;
    font-weight:    normal;
    font-family:    monospace;
}
.pt10br {
    font-size:      9pt;
    font-weight:    bold;
    font-family:    monospace;
    color:          brown;
}
.pt10b {
    font:           10pt;
    font-weight:    bold;
    font-family:    monospace;
}
.pt11b {
    font:           11pt;
    font-weight:    bold;
    color:          blue;
}
th {
    background-color:   yellow;
    color:              blue;
    font:               10pt;
    font-weight:        bold;
    font-family:        monospace;
}
-->
</style>
</head>
<body onLoad='set_focus()'>
    <center>
<?=$menu->out_title_border()?>
        
        <!----------------- ここは 前頁 次頁 のフォーム ---------------->
        <table width='100%' border='0' cellspacing='0' cellpadding='0'>
            <form name='page_form' method='post' action='<?= $menu->out_self() ?>'>
                <tr>
                    <td align='left'>
                        <table align='left' border='3' cellspacing='0' cellpadding='0'>
                            <td align='left'>
                                <input class='pt10b' type='submit' name='backward' value='前頁'>
                            </td>
                        </table>
                    </td>
                    <td nowrap align='center' class='pt11b'>
                        <?= $menu->out_caption(), "\n" ?>
                    </td>
                    <td align='right'>
                        <table align='right' border='3' cellspacing='0' cellpadding='0'>
                            <td align='right'>
                                <input class='pt10b' type='submit' name='forward' value='次頁'>
                            </td>
                        </table>
                    </td>
                </tr>
            </form>
        </table>
        
        <!--------------- ここから本文の表を表示する -------------------->
        <table bgcolor='#d6d3ce' align='center' border='1' cellspacing='0' cellpadding='3'>
            <tr><td> <!----------- ダミー(デザイン用) ------------>
        <table class='winbox_field' width='100%' bgcolor='#d6d3ce' align='center' border='1' cellspacing='0' cellpadding='3'>
            <THEAD>
                <!-- テーブル ヘッダーの表示 -->
                <tr>
                    <th class='winbox' nowrap width='10'>No</th>        <!-- 行ナンバーの表示 -->
                <?php
                for ($i=0; $i<$num; $i++) {             // フィールド数分繰返し
                    if ($i==4) {
                ?>
                    <th class='winbox' nowrap><div class='pt10b'>最新単価</div></th>
                <?php
                    } else {
                ?>
                    <th class='winbox' nowrap><div class='pt10b'><?= $field[$i] ?></div></th>
                <?php
                    }
                }
                ?>
                </tr>
            </THEAD>
            <TFOOT>
                <!-- 現在はフッターは何もない -->
            </TFOOT>
            <TBODY>
                        <!--  bgcolor='#ffffc6' 薄い黄色 -->
                        <!-- サンプル<td rowspan='2' colspan='3' width='200' align='center' class='pt10b' bgcolor='#ffffc6'>  </td> -->
                <?php
                for ($r=0; $r<$rows; $r++) {
                    $new_cost = 0;
                    $new_flg  = 0;
                    $new_cost = get_new_cost($res[$r][0]);
                    if ($new_cost > 0) {
                        $res[$r][4] = $new_cost;
                        $res[$r][5] = round($res[$r][3] * $new_cost);
                        $new_flg    = 1;
                    }
                ?>
                    <tr>
                        <td class='winbox' nowrap align='right'><div class='pt10b'><?= ($r + $offset + 1) ?></div></td>    <!-- 行ナンバーの表示 -->
                    <?php
                    for ($i=0; $i<$num; $i++) {         // レコード数分繰返し
                        switch ($i) {
                        case 1:
                            echo "<td class='winbox' nowrap align='left'><div class='pt10'>{$res[$r][$i]}</div></td>\n";
                            break;
                        case 3:
                            echo "<td class='winbox' nowrap align='right'><div class='pt10'>", number_format($res[$r][$i]), "</div></td>\n";
                            break;
                        case 4:
                            if ($new_flg==0) {
                                echo "<td class='winbox' nowrap align='right'><div class='pt10br'>", number_format($res[$r][$i], 2), "</div></td>\n";
                            } else {
                                echo "<td class='winbox' nowrap align='right'><div class='pt10'>", number_format($res[$r][$i], 2), "</div></td>\n";
                            }
                            break;
                        case 5:
                            echo "<td class='winbox' nowrap align='right'><div class='pt10'>", number_format($res[$r][$i]), "</div></td>\n";
                            break;
                        case 7:
                            if($res[$r][$i] == 'E') {
                                echo "<td class='winbox' nowrap align='center'><div class='pt10'>TNKCC</div></td>\n";
                            } elseif($res[$r][$i] == 'C') {
                                echo "<td class='winbox' nowrap align='center'><div class='pt10'>CC部品</div></td>\n";
                            } elseif($res[$r][$i] == 'X') {
                                echo "<td class='winbox' nowrap align='center'><div class='pt10'>対象外</div></td>\n";
                            } else {
                                echo "<td class='winbox' nowrap align='center'><div class='pt10'>{$res[$r][$i]}</div></td>\n";
                            }
                            break;
                        default:
                            echo "<td class='winbox' nowrap align='center'><div class='pt10'>{$res[$r][$i]}</div></td>\n";
                        }
                    }
                    ?>
                    </tr>
                <?php
                }
                ?>
            </TBODY>
        </table>
            </td></tr>
        </table> <!----------------- ダミーEnd ------------------>
    </center>
</body>
<?=$menu->out_alert_java()?>
</html>
<?php
ob_end_flush();     // 出力バッファーをgzip圧縮 END
?>
