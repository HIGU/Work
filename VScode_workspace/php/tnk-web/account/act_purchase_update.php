<?php
//////////////////////////////////////////////////////////////////////////////
// 仕入金額の計算 ＆ 更新 実行ロジック  (買掛金 と 有償支給金額)            //
// Copyright(C) 2003-2014 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp       //
// 変更経歴                                                                 //
// 2003/12/09 新規作成  act_purchase_update.php                             //
//            処理が重いため act_purchase_header テーブルを作成し           //
//            現在はヘッダーファイルに合計金額とレコード数を保存            //
//            将来的には明細も別テーブルに持つようにする予定                //
// 2004/04/05 header('Location: http:' . WEB_HOST . 'account/?????' -->     //
//                                  header('Location: ' . H_WEB_HOST . ACT  //
// 2014/02/06 科目0を毎月使用する可能性がある為、科目１から５のみを         //
//            抜き出すようにプログラムを変更                           大谷 //
//////////////////////////////////////////////////////////////////////////////
ini_set('error_reporting',E_ALL);       // E_ALL='2047' debug 用
// ini_set('display_errors','1');          // Error 表示 ON debug 用 リリース後コメント
// ini_set('implicit_flush', 'off');       // echo print で flush させない(遅くなるため) CLI CGI版
// ini_set('max_execution_time', 1200);    // 最大実行時間=20分 CLI CGI版
ob_start('ob_gzhandler');               // 出力バッファをgzip圧縮
session_start();                        // ini_set()の次に指定すること Script 最上行
require_once ('../function.php');       // define.php と pgsql.php を require_once している
require_once ('../tnk_func.php');       // TNK に依存する部分の関数を require_once している
access_log();                           // Script Name は自動取得
$_SESSION['site_index'] = 20;           // 経理日報関係=20 最後のメニュー = 99   システム管理用は９９番
$_SESSION['site_id']    = 31;           // 下位メニュー無し <= 0    テンプレートファイルは６０番
$current_script  = $_SERVER['PHP_SELF'];        // 現在実行中のスクリプト名を保存
// $url_referer     = $_SERVER['HTTP_REFERER'];    // 呼出もとのURLを保存 前のスクリプトで分岐処理をしている場合は使用しない
$url_referer     = $_SESSION['act_referer'];     // 分岐処理前に保存されている呼出元をセットする

//////////////// 認証チェック
// if ( !isset($_SESSION['User_ID']) || !isset($_SESSION['Password']) || !isset($_SESSION['Auth']) ) {
// if ($_SESSION['Auth'] <= 2) {                // 権限レベルが２以下は拒否
if (account_group_check() == FALSE) {        // 特定のグループ以外は拒否
    $_SESSION['s_sysmsg'] = "Accounting Group の権限が必要です！";
    // header("Location: http:" . WEB_HOST . "menu.php");   // 固定呼出元へ戻る
    header("Location: $url_referer");                   // 直前の呼出元へ戻る
    exit();
}

/********** Logic Start **********/
//////////// タイトルの日付・時間設定
$today = date('Y/m/d H:i:s');

//////////// JavaScript Stylesheet File を必ず読み込ませる
$uniq = uniqid('target');

//////////// システムメッセージ変数初期化
// $_SESSION['s_sysmsg'] = "";      // menu_site.php で使用するためここで初期化は不可

//////////// 対象年月を取得 (年月のみに注意)
if ( isset($_SESSION['act_ym']) ) {
    $act_ym = $_SESSION['act_ym'];
    $s_ymd  = $act_ym . '01';   // 開始日
    $e_ymd  = $act_ym . '99';   // 終了日
} else {
    $_SESSION['s_sysmsg'] = '月次対象年月が指定されていません!';
    header('Location: ' . $url_referer);
    exit();
}
//////////// タイトル名(ソースのタイトル名とフォームのタイトル名)
$menu_title = "$act_ym 仕入計上金額 照会";

//////////// 一頁の行数
define('PAGE', '25');

/////////// begin トランザクション開始
if ($con = db_connect()) {
    query_affected_trans($con, 'begin');
} else {
    $_SESSION['s_sysmsg'] .= 'db_connect() error';
    exit();
}

//////////// 既に登録済みかチェック
$query = "select item from act_purchase_header where purchase_ym={$act_ym}";
if ( getResultTrs($con, $query, $res) > 0) {         // レコードがあるか？
    $_SESSION['s_sysmsg'] .= "{$act_ym}：は既に登録済みです！";      // .= メッセージを追加する
    query_affected_trans($con, 'rollback');         // transaction rollback
    header('Location: ' . $url_referer);
    exit();
}

//////////// SQL 文の where 句を 共用する
$search = sprintf("where act_date>=%d and act_date<=%d and vendor !='01111' and vendor !='00222' and vendor !='99999'", $s_ymd, $e_ymd);

/******************* 買掛金の取得 ****************************/
// 全体
//////////// SQL 文の where 句を 共用する 01111=栃木日東工器 00222=製造課特注 99999=諸口
$search_kin = sprintf("%s and kamoku<=5 and kamoku>=1", $search);

//////////// 内作を除く合計金額 (科目1～5)科目6以上を除く
$query = sprintf("select sum(Uround(order_price * siharai,0)), count(*) from act_payable %s", $search_kin);
if ( getResultTrs($con, $query, $paya_all) <= 0) {
    $_SESSION['s_sysmsg'] .= '全体の買掛 金額の取得に失敗';      // .= メッセージを追加する
    query_affected_trans($con, 'rollback');         // transaction rollback
    header("Location: $url_referer");               // 直前の呼出元へ戻る
    exit();
}

// カプラ
//////////// SQL 文の where 句を 共用する 01111=栃木日東工器 00222=製造課特注 99999=諸口
$search_kin = sprintf("%s and kamoku<=5 and kamoku>=1 and div='C'", $search);

//////////// 内作を除く合計金額 (科目1～5)科目6以上を除く
$query = sprintf("select sum(Uround(order_price * siharai,0)), count(*) from act_payable %s", $search_kin);
if ( getResultTrs($con, $query, $paya_c) <= 0) {
    $_SESSION['s_sysmsg'] .= 'カプラの買掛 金額の取得に失敗';      // .= メッセージを追加する
    query_affected_trans($con, 'rollback');         // transaction rollback
    header("Location: $url_referer");               // 直前の呼出元へ戻る
    exit();
}

// カプラ 特注
//////////// SQL 文の where 句を 共用する 01111=栃木日東工器 00222=製造課特注 99999=諸口
$search_kin = sprintf("%s and kamoku<=5 and kamoku>=1 and paya.div='C' and kouji_no like 'SC%%'", $search);

//////////// 内作を除く合計金額 (科目1～5)科目6以上を除く
$query = sprintf("select sum(Uround(order_price * siharai,0)), count(*) from act_payable as paya left outer join order_plan using(sei_no) %s", $search_kin);
if ( getResultTrs($con, $query, $paya_ctoku) <= 0) {
    $_SESSION['s_sysmsg'] .= 'カプラ特注の買掛 金額の取得に失敗';      // .= メッセージを追加する
    query_affected_trans($con, 'rollback');         // transaction rollback
    header("Location: $url_referer");               // 直前の呼出元へ戻る
    exit();
}

// リニア
//////////// SQL 文の where 句を 共用する 01111=栃木日東工器 00222=製造課特注 99999=諸口
$search_kin = sprintf("%s and kamoku<=5 and kamoku>=1 and div='L'", $search);

//////////// 内作を除く合計金額 (科目1～5)科目6以上を除く
$query = sprintf("select sum(Uround(order_price * siharai,0)), count(*) from act_payable %s", $search_kin);
if ( getResultTrs($con, $query, $paya_l) <= 0) {
    $_SESSION['s_sysmsg'] .= 'リニアの買掛 金額の取得に失敗';      // .= メッセージを追加する
    query_affected_trans($con, 'rollback');         // transaction rollback
    header("Location: $url_referer");               // 直前の呼出元へ戻る
    exit();
}

// リニア BIMOR
//////////// SQL 文の where 句を 共用する 01111=栃木日東工器 00222=製造課特注 99999=諸口
$search_kin = sprintf("%s and kamoku<=5 and kamoku>=1 and div='L' and (parts_no like 'LR%%' or parts_no like 'LC%%')", $search);

//////////// 内作を除く合計金額 (科目1～5)科目6以上を除く
$query = sprintf("select sum(Uround(order_price * siharai,0)), count(*) from act_payable %s", $search_kin);
if ( getResultTrs($con, $query, $paya_bimor) <= 0) {
    $_SESSION['s_sysmsg'] .= 'バイモルの買掛 金額の取得に失敗';      // .= メッセージを追加する
    query_affected_trans($con, 'rollback');         // transaction rollback
    header("Location: $url_referer");               // 直前の呼出元へ戻る
    exit();
}

/******************************************************************/


/******************* 有償支給金額の取得 ****************************/
// 全体
//////////// SQL 文の where 句を 共用する 01111=栃木日東工器 00222=製造課特注 99999=諸口 材料条件=2(有償)
$search_kin = sprintf("%s and mtl_cond='2'", $search);

//////////// 内作を除く合計金額
$query = sprintf("select sum(Uround(prov * prov_tan, 0)), count(*) from act_miprov %s", $search_kin);
if ( getResultTrs($con, $query, $prov_all) <= 0) {
    $_SESSION['s_sysmsg'] .= '全体の有償支給 金額の取得に失敗';      // .= メッセージを追加する
    query_affected_trans($con, 'rollback');         // transaction rollback
    header("Location: $url_referer");               // 直前の呼出元へ戻る
    exit();
}

// カプラ
//////////// SQL 文の where 句を 共用する 01111=栃木日東工器 00222=製造課特注 99999=諸口 材料条件=2(有償)
$search_kin = sprintf("%s and mtl_cond='2' and div='C'", $search);

//////////// 内作を除く合計金額
$query = sprintf("select sum(Uround(prov * prov_tan, 0)), count(*) from act_miprov %s", $search_kin);
if ( getResultTrs($con, $query, $prov_c) <= 0) {
    $_SESSION['s_sysmsg'] .= 'カプラの有償支給 金額の取得に失敗';      // .= メッセージを追加する
    query_affected_trans($con, 'rollback');         // transaction rollback
    header("Location: $url_referer");               // 直前の呼出元へ戻る
    exit();
}

// カプラ 特注
//////////// SQL 文の where 句を 共用する 01111=栃木日東工器 00222=製造課特注 99999=諸口 材料条件=2(有償)
$search_kin = sprintf("%s and mtl_cond='2' and mi.div='C' and kouji_no like 'SC%%'", $search);

//////////// 内作を除く合計金額
$query = sprintf("select sum(Uround(prov * prov_tan, 0)), count(*) from act_miprov as mi left outer join order_plan on substr(_sei_no,2,7)=sei_no %s", $search_kin);
if ( getResultTrs($con, $query, $prov_ctoku) <= 0) {
    $_SESSION['s_sysmsg'] .= 'カプラ特注の有償支給 金額の取得に失敗';      // .= メッセージを追加する
    query_affected_trans($con, 'rollback');         // transaction rollback
    header("Location: $url_referer");               // 直前の呼出元へ戻る
    exit();
}

// リニア
//////////// SQL 文の where 句を 共用する 01111=栃木日東工器 00222=製造課特注 99999=諸口 材料条件=2(有償)
$search_kin = sprintf("%s and mtl_cond='2' and div='L'", $search);

//////////// 内作を除く合計金額
$query = sprintf("select sum(Uround(prov * prov_tan, 0)), count(*) from act_miprov %s", $search_kin);
if ( getResultTrs($con, $query, $prov_l) <= 0) {
    $_SESSION['s_sysmsg'] .= 'リニアの有償支給 金額の取得に失敗';      // .= メッセージを追加する
    query_affected_trans($con, 'rollback');         // transaction rollback
    header("Location: $url_referer");               // 直前の呼出元へ戻る
    exit();
}

// リニア BIMOR
//////////// SQL 文の where 句を 共用する 01111=栃木日東工器 00222=製造課特注 99999=諸口 材料条件=2(有償)
$search_kin = sprintf("%s and mtl_cond='2' and div='L' and (parts_no like 'LR%%' or parts_no like 'LC%%')", $search);

//////////// 内作を除く合計金額
$query = sprintf("select sum(Uround(prov * prov_tan, 0)), count(*) from act_miprov %s", $search_kin);
if ( getResultTrs($con, $query, $prov_bimor) <= 0) {
    $_SESSION['s_sysmsg'] .= 'バイモルの有償支給 金額の取得に失敗';      // .= メッセージを追加する
    query_affected_trans($con, 'rollback');         // transaction rollback
    header("Location: $url_referer");               // 直前の呼出元へ戻る
    exit();
}

/******************************************************************/


//////////// 全体をヘッダーに書込み
if ($paya_all[0][0] == '') $paya_all[0][0] = 0;     // レコード無しのチェック
if ($paya_all[0][1] == '') $paya_all[0][1] = 0;     //   〃
if ($prov_all[0][0] == '') $prov_all[0][0] = 0;     //   〃
if ($prov_all[0][1] == '') $prov_all[0][1] = 0;     //   〃
$query = "insert into act_purchase_header (purchase_ym, item, sum_payable, sum_provide, cnt_payable, cnt_provide)
                values ({$act_ym}, '全体', {$paya_all[0][0]}, {$prov_all[0][0]}, {$paya_all[0][1]}, {$prov_all[0][1]})";
/////////// トランザクション内で更新実行
if (($rows = query_affected_trans($con, $query)) <= 0) {
    $_SESSION['s_sysmsg'] .= '全体のヘッダー書込みに失敗';      // .= メッセージを追加する
    query_affected_trans($con, 'rollback');         // transaction rollback
    header("Location: $url_referer");               // 直前の呼出元へ戻る
    exit();
}

//////////// カプラをヘッダーに書込み
if ($paya_c[0][0] == '') $paya_c[0][0] = 0;     // レコード無しのチェック
if ($paya_c[0][1] == '') $paya_c[0][1] = 0;     //   〃
if ($prov_c[0][0] == '') $prov_c[0][0] = 0;     //   〃
if ($prov_c[0][1] == '') $prov_c[0][1] = 0;     //   〃
$query = "insert into act_purchase_header (purchase_ym, item, sum_payable, sum_provide, cnt_payable, cnt_provide)
                values ({$act_ym}, 'カプラ', {$paya_c[0][0]}, {$prov_c[0][0]}, {$paya_c[0][1]}, {$prov_c[0][1]})";
/////////// トランザクション内で更新実行
if (($rows = query_affected_trans($con, $query)) <= 0) {
    $_SESSION['s_sysmsg'] .= 'カプラのヘッダー書込みに失敗';      // .= メッセージを追加する
    query_affected_trans($con, 'rollback');         // transaction rollback
    header("Location: $url_referer");               // 直前の呼出元へ戻る
    exit();
}

//////////// リニアをヘッダーに書込み
if ($paya_l[0][0] == '') $paya_l[0][0] = 0;     // レコード無しのチェック
if ($paya_l[0][1] == '') $paya_l[0][1] = 0;     //   〃
if ($prov_l[0][0] == '') $prov_l[0][0] = 0;     //   〃
if ($prov_l[0][1] == '') $prov_l[0][1] = 0;     //   〃
$query = "insert into act_purchase_header (purchase_ym, item, sum_payable, sum_provide, cnt_payable, cnt_provide)
                values ({$act_ym}, 'リニア', {$paya_l[0][0]}, {$prov_l[0][0]}, {$paya_l[0][1]}, {$prov_l[0][1]})";
/////////// トランザクション内で更新実行
if (($rows = query_affected_trans($con, $query)) <= 0) {
    $_SESSION['s_sysmsg'] .= 'リニアのヘッダー書込みに失敗';      // .= メッセージを追加する
    query_affected_trans($con, 'rollback');         // transaction rollback
    header("Location: $url_referer");               // 直前の呼出元へ戻る
    exit();
}

//////////// カプラ特注をヘッダーに書込み
if ($paya_ctoku[0][0] == '') $paya_ctoku[0][0] = 0;     // レコード無しのチェック
if ($paya_ctoku[0][1] == '') $paya_ctoku[0][1] = 0;     //   〃
if ($prov_ctoku[0][0] == '') $prov_ctoku[0][0] = 0;     //   〃
if ($prov_ctoku[0][1] == '') $prov_ctoku[0][1] = 0;     //   〃
$query = "insert into act_purchase_header (purchase_ym, item, sum_payable, sum_provide, cnt_payable, cnt_provide)
                values ({$act_ym}, 'カプラ特注', {$paya_ctoku[0][0]}, {$prov_ctoku[0][0]}, {$paya_ctoku[0][1]}, {$prov_ctoku[0][1]})";
/////////// トランザクション内で更新実行
if (($rows = query_affected_trans($con, $query)) <= 0) {
    $_SESSION['s_sysmsg'] .= 'カプラ特注のヘッダー書込みに失敗';      // .= メッセージを追加する
    query_affected_trans($con, 'rollback');         // transaction rollback
    header("Location: $url_referer");               // 直前の呼出元へ戻る
    exit();
}

//////////// バイモルをヘッダーに書込み
if ($paya_bimor[0][0] == '') $paya_bimor[0][0] = 0;     // レコード無しのチェック
if ($paya_bimor[0][1] == '') $paya_bimor[0][1] = 0;     //   〃
if ($prov_bimor[0][0] == '') $prov_bimor[0][0] = 0;     //   〃
if ($prov_bimor[0][1] == '') $prov_bimor[0][1] = 0;     //   〃
$query = "insert into act_purchase_header (purchase_ym, item, sum_payable, sum_provide, cnt_payable, cnt_provide)
                values ({$act_ym}, 'バイモル', {$paya_bimor[0][0]}, {$prov_bimor[0][0]}, {$paya_bimor[0][1]}, {$prov_bimor[0][1]})";
/////////// トランザクション内で更新実行
if (($rows = query_affected_trans($con, $query)) <= 0) {
    $_SESSION['s_sysmsg'] .= 'バイモルのヘッダー書込みに失敗';      // .= メッセージを追加する
    query_affected_trans($con, 'rollback');         // transaction rollback
    header("Location: $url_referer");               // 直前の呼出元へ戻る
    exit();
}

/////////// commit トランザクション終了
query_affected_trans($con, 'commit');
$_SESSION['s_sysmsg'] .= "<font color='yellow'>{$act_ym}：仕入計上 処理 終了</font>";
header('Location: ' . H_WEB_HOST . ACT . 'act_purchase_view.php');   // 照会スクリプトへ
// header('Location: http:' . WEB_HOST . 'account/act_purchase_view.php');   // 照会スクリプトへ
exit();

/********** Logic End   **********/
?>
