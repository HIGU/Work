<?php
//////////////////////////////////////////////////////////////////////////////
// 損益対象部門別に棚卸金額の更新(カプラ・リニア・カプラ特注・バイモル      //
//                                全体・その他・等)                         //
// Copyright (C) 2003-2017 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp      //
// Changed history                                                          //
// 2003/12/09 Created   inventory_monthly_header_update.php                 //
// 2003/12/10 無償支給品・客先支給品の登録チェックロジックを追加            //
// 2004/01/07 上記のロジックの rollback が抜けているのを訂正 その他の棚卸が //
//        ない場合の対応ロジックを追加(前もって調整すればない場合がほとんど)//
//        全体の無償支給品を除くロジックが抜けているのを修正(リニアと全体)  //
// 2005/02/09 ディレクトリを変更 account/ → account/inventory/ へ          //
// 2017/10/12 2017/10より割合に従い標準から特注へ一部配賦              大谷 //
//////////////////////////////////////////////////////////////////////////////
ini_set('error_reporting', E_ALL);          // E_ALL='2047' debug 用
// ini_set('display_errors', '1');             // Error 表示 ON debug 用 リリース後コメント
ini_set('implicit_flush', 'off');           // echo print で flush させない(遅くなるため) CLI CGI版
ini_set('max_execution_time', 1200);        // 最大実行時間=20分 CLI CGI版
// ob_start('ob_gzhandler');                   // 出力バッファをgzip圧縮
session_start();                            // ini_set()の次に指定すること Script 最上行
require_once ('/var/www/html/function.php');
require_once ('/var/www/html/tnk_func.php');   // account_group_check()で使用
access_log();                               // Script Name は自動取得

$_SESSION['site_index'] = 20;               // 経理日報関係=20 最後のメニュー = 99   システム管理用は９９番
$_SESSION['site_id']    = 32;               // 下位メニュー無し <= 0    テンプレートファイルは６０番

$current_script  = $_SERVER['PHP_SELF'];        // 現在実行中のスクリプト名を保存
// $url_referer     = $_SERVER['HTTP_REFERER'];    // 呼出もとのURLを保存 前のスクリプトで分岐処理をしている場合は使用しない
$url_referer     = $_SESSION['act_referer'];     // 分岐処理前に保存されている呼出元をセットする

//////////////// 認証チェック
// if ( !isset($_SESSION['User_ID']) || !isset($_SESSION['Password']) || !isset($_SESSION['Auth']) ) {
// if ($_SESSION['Auth'] <= 2) {                // 権限レベルが２以下は拒否
if (account_group_check() == FALSE) {        // 特定のグループ以外は拒否
    // $_SESSION['s_sysmsg'] = "認証されていないか認証期限が切れました。ログインからお願いします。";
    $_SESSION['s_sysmsg'] = "Accounting Group の権限が必要です！";
    // header("Location: http:" . WEB_HOST . "account/act_menu.php");   // 固定呼出元へ戻る
    header("Location: $url_referer");                   // 直前の呼出元へ戻る
    exit();
}

//////////// 対象年月を取得 (年月のみに注意)
if ( isset($_SESSION['act_ym']) ) {
    $act_ym = $_SESSION['act_ym'];
    $s_ymd  = $act_ym . '01';   // 開始日
    $e_ymd  = $act_ym . '99';   // 終了日
} else {
    $_SESSION['s_sysmsg'] = '対象年月が指定されていません！';
    header("Location: $url_referer");                   // 直前の呼出元へ戻る
    exit();
}

/////////// begin トランザクション開始
if ($con = db_connect()) {
    query_affected_trans($con, 'begin');
} else {
    $_SESSION['s_sysmsg'] .= 'db_connect() error';
    exit();
}

////////// 無償支給品・客先支給品の月次登録がされているかチェック
$query_chk = "SELECT parts_no FROM provide_item WHERE reg_ym={$act_ym} limit 1";
if (getUniResTrs($con, $query_chk, $res_chk) <= 0) {    // トランザクション内での 照会専用クエリー
    ///// 登録なし(先に登録が必要)
    query_affected_trans($con, 'rollback');             // transaction rollback
    $_SESSION['s_sysmsg'] .= "{$act_ym}：の客先支給品の更新がされていません！<br>先に登録して下さい。";
    header("Location: $url_referer");                   // 直前の呼出元へ戻る
    exit();
}

/********************* 全体の登録済みのチェック ****************************/
$search = "where invent_ym={$act_ym} and item='全体'";
//////////// ヘッダーにレコードがあるか？
$query = sprintf("select sum_money_t, sum_count from inventory_monthly_header %s", $search);
$res_sum = array();         // 初期化
if ( getResultTrs($con, $query, $res_sum) > 0) {         // $maxrows の取得
    $_SESSION['s_sysmsg'] .= "<font color='white'>全体のデータは登録済みです。</font><br>";      // .= メッセージを追加する
} else {
    //////////// 合計 金額・レコード数取得
    $search = "where invent_ym={$act_ym} and pro.type is null";     // 無償支給品を除外
    // $search = "where invent_ym={$act_ym}";     // num_div 1=機工 3=リニア 5=カプラ
    $query = sprintf('select
                            count(*),
                            sum(Uround(zen_zai * gai_tan, 0) + Uround(zen_zai * nai_tan, 0)) as 金額_z,
                            sum(Uround(tou_zai * gai_tan, 0) + Uround(tou_zai * nai_tan, 0)) as 金額_t
                      from
                            inventory_monthly as inv
                      left outer join
                            provide_item as pro
                      on (inv.invent_ym=pro.reg_ym and inv.parts_no=pro.parts_no)
                      %s', $search);
    $res_sum = array();     // 初期化
    if ( getResultTrs($con, $query, $res_sum) <= 0) {         // $maxrows の取得
        $_SESSION['s_sysmsg'] .= "全体の合計金額が取得できません！";      // .= メッセージを追加する
        query_affected_trans($con, 'rollback');         // transaction rollback
        header("Location: $url_referer");                   // 直前の呼出元へ戻る
        exit();
    }
    $maxrows   = $res_sum[0][0];  // 合計レコード数
    $sum_kin_z = $res_sum[0][1];  // 合計 棚卸 金額(前月)
    $sum_kin_t = $res_sum[0][2];  // 合計 棚卸 金額(当月)
    /////////// レコードの有無をチェック
    if ( $maxrows == 0) {         // $maxrows でチェック
        $_SESSION['s_sysmsg'] .= "{$act_ym}：棚卸データがない！";   // .= メッセージを追加する
        query_affected_trans($con, 'rollback');             // transaction rollback
        header("Location: $url_referer");                   // 直前の呼出元へ戻る
        exit();
    }
    /////////// header テーブルに書込む
    $query = "insert into inventory_monthly_header (invent_ym, item, sum_money_z, sum_money_t, sum_count)
                    values ({$act_ym}, '全体', {$sum_kin_z}, {$sum_kin_t}, {$maxrows})";
    if (($rows = query_affected_trans($con, $query)) <= 0) {
        $_SESSION['s_sysmsg'] .= '全体のヘッダー書込みに失敗！';
        query_affected_trans($con, 'rollback');         // transaction rollback
        header("Location: $url_referer");               // 直前の呼出元へ戻る
        exit();
    } else {
        $_SESSION['s_sysmsg'] .= "<font color='yellow'>全体の棚卸金額を更新しました。</font><br>";      // .= メッセージを追加する
    }
}

/********************* カプラの登録済みのチェック ****************************/
$search = "where invent_ym={$act_ym} and item='カプラ'";
//////////// ヘッダーにレコードがあるか？
$query = sprintf("select sum_money_t, sum_count from inventory_monthly_header %s", $search);
$res_sum = array();         // 初期化
if ( getResultTrs($con, $query, $res_sum) > 0) {         // $maxrows の取得
    $_SESSION['s_sysmsg'] .= "<font color='white'>カプラのデータは登録済みです。</font><br>";      // .= メッセージを追加する
} else {
    //////////// 合計 金額・レコード数取得
    $search = "where invent_ym={$act_ym} and num_div='5'";     // num_div 1=機工 3=リニア 5=カプラ
    $query = sprintf('select
                            count(*),
                            sum(Uround(zen_zai * gai_tan, 0) + Uround(zen_zai * nai_tan, 0)) as 金額_z,
                            sum(Uround(tou_zai * gai_tan, 0) + Uround(tou_zai * nai_tan, 0)) as 金額_t
                      from inventory_monthly as inv %s', $search);
    $res_sum = array();     // 初期化
    if ( getResultTrs($con, $query, $res_sum) <= 0) {         // $maxrows の取得
        $_SESSION['s_sysmsg'] .= "カプラの合計金額が取得できません！";      // .= メッセージを追加する
        query_affected_trans($con, 'rollback');         // transaction rollback
        header("Location: $url_referer");                   // 直前の呼出元へ戻る
        exit();
    }
    $maxrows   = $res_sum[0][0];  // 合計レコード数
    $sum_kin_z = $res_sum[0][1];  // 合計 棚卸 金額(前月)
    $sum_kin_t = $res_sum[0][2];  // 合計 棚卸 金額(当月)
    /////////// レコードの有無をチェック
    if ( $maxrows == 0) {         // $maxrows でチェック
        $_SESSION['s_sysmsg'] .= "{$act_ym}：棚卸データがない！";   // .= メッセージを追加する
        query_affected_trans($con, 'rollback');             // transaction rollback
        header("Location: $url_referer");                   // 直前の呼出元へ戻る
        exit();
    }
    /////////// header テーブルに書込む
    $query = "insert into inventory_monthly_header (invent_ym, item, sum_money_z, sum_money_t, sum_count)
                    values ({$act_ym}, 'カプラ', {$sum_kin_z}, {$sum_kin_t}, {$maxrows})";
    if (($rows = query_affected_trans($con, $query)) <= 0) {
        $_SESSION['s_sysmsg'] .= 'カプラのヘッダー書込みに失敗！';
        query_affected_trans($con, 'rollback');         // transaction rollback
        header("Location: $url_referer");               // 直前の呼出元へ戻る
        exit();
    } else {
        $_SESSION['s_sysmsg'] .= "<font color='yellow'>カプラの棚卸金額を更新しました。</font><br>";      // .= メッセージを追加する
    }
}

/********************* リニアの登録済みのチェック ****************************/
$search = "where invent_ym={$act_ym} and item='リニア'";
//////////// ヘッダーにレコードがあるか？
$query = sprintf("select sum_money_t, sum_count from inventory_monthly_header %s", $search);
$res_sum = array();         // 初期化
if ( getResultTrs($con, $query, $res_sum) > 0) {         // $maxrows の取得
    $_SESSION['s_sysmsg'] .= "<font color='white'>リニアのデータは登録済みです。</font><br>";      // .= メッセージを追加する
} else {
    //////////// 合計 金額・レコード数取得
    $search = "where invent_ym={$act_ym} and num_div='3' and pro.type is null";     // num_div 1=機工 3=リニア 5=カプラ
    $query = sprintf('select
                            count(*),
                            sum(Uround(zen_zai * gai_tan, 0) + Uround(zen_zai * nai_tan, 0)) as 金額_z,
                            sum(Uround(tou_zai * gai_tan, 0) + Uround(tou_zai * nai_tan, 0)) as 金額_t
                      from
                            inventory_monthly as inv
                      left outer join
                            provide_item as pro
                      on (inv.invent_ym=pro.reg_ym and inv.parts_no=pro.parts_no)
                      %s', $search);
    $res_sum = array();     // 初期化
    if ( getResultTrs($con, $query, $res_sum) <= 0) {         // $maxrows の取得
        $_SESSION['s_sysmsg'] .= "リニアの合計金額が取得できません！";      // .= メッセージを追加する
        query_affected_trans($con, 'rollback');         // transaction rollback
        header("Location: $url_referer");                   // 直前の呼出元へ戻る
        exit();
    }
    $maxrows   = $res_sum[0][0];  // 合計レコード数
    $sum_kin_z = $res_sum[0][1];  // 合計 棚卸 金額(前月)
    $sum_kin_t = $res_sum[0][2];  // 合計 棚卸 金額(当月)
    /////////// レコードの有無をチェック
    if ( $maxrows == 0) {         // $maxrows でチェック
        $_SESSION['s_sysmsg'] .= "{$act_ym}：棚卸データがない！";   // .= メッセージを追加する
        query_affected_trans($con, 'rollback');             // transaction rollback
        header("Location: $url_referer");                   // 直前の呼出元へ戻る
        exit();
    }
    /////////// header テーブルに書込む
    $query = "insert into inventory_monthly_header (invent_ym, item, sum_money_z, sum_money_t, sum_count)
                    values ({$act_ym}, 'リニア', {$sum_kin_z}, {$sum_kin_t}, {$maxrows})";
    if (($rows = query_affected_trans($con, $query)) <= 0) {
        $_SESSION['s_sysmsg'] .= 'リニアのヘッダー書込みに失敗！';
        query_affected_trans($con, 'rollback');         // transaction rollback
        header("Location: $url_referer");               // 直前の呼出元へ戻る
        exit();
    } else {
        $_SESSION['s_sysmsg'] .= "<font color='yellow'>リニアの棚卸金額を更新しました。</font><br>";      // .= メッセージを追加する
    }
}

/********************* バイモルの登録済みのチェック ****************************/
$search = "where invent_ym={$act_ym} and item='バイモル'";
//////////// ヘッダーにレコードがあるか？
$query = sprintf("select sum_money_t, sum_count from inventory_monthly_header %s", $search);
$res_sum = array();         // 初期化
if ( getResultTrs($con, $query, $res_sum) > 0) {         // $maxrows の取得
    $_SESSION['s_sysmsg'] .= "<font color='white'>バイモルのデータは登録済みです。</font><br>";      // .= メッセージを追加する
} else {
    //////////// 合計 金額・レコード数取得
    $search = "where invent_ym={$act_ym} and (inv.parts_no like 'LR%%' or inv.parts_no like 'LC%%')"; // num_div 1=機工 3=リニア 5=カプラ
    $query = sprintf('select
                            count(*),
                            sum(Uround(zen_zai * gai_tan, 0) + Uround(zen_zai * nai_tan, 0)) as 金額_z,
                            sum(Uround(tou_zai * gai_tan, 0) + Uround(tou_zai * nai_tan, 0)) as 金額_t
                      from inventory_monthly as inv %s', $search);
    $res_sum = array();     // 初期化
    if ( getResultTrs($con, $query, $res_sum) <= 0) {         // $maxrows の取得
        $_SESSION['s_sysmsg'] .= "バイモルの合計金額が取得できません！";      // .= メッセージを追加する
        query_affected_trans($con, 'rollback');         // transaction rollback
        header("Location: $url_referer");                   // 直前の呼出元へ戻る
        exit();
    }
    $maxrows   = $res_sum[0][0];  // 合計レコード数
    $sum_kin_z = $res_sum[0][1];  // 合計 棚卸 金額(前月)
    $sum_kin_t = $res_sum[0][2];  // 合計 棚卸 金額(当月)
    /////////// レコードの有無をチェック
    if ( $maxrows == 0) {         // $maxrows でチェック
        $_SESSION['s_sysmsg'] .= "{$act_ym}：棚卸データがない！";   // .= メッセージを追加する
        query_affected_trans($con, 'rollback');             // transaction rollback
        header("Location: $url_referer");                   // 直前の呼出元へ戻る
        exit();
    }
    /////////// header テーブルに書込む
    $query = "insert into inventory_monthly_header (invent_ym, item, sum_money_z, sum_money_t, sum_count)
                    values ({$act_ym}, 'バイモル', {$sum_kin_z}, {$sum_kin_t}, {$maxrows})";
    if (($rows = query_affected_trans($con, $query)) <= 0) {
        $_SESSION['s_sysmsg'] .= 'リニアのヘッダー書込みに失敗！';
        query_affected_trans($con, 'rollback');         // transaction rollback
        header("Location: $url_referer");               // 直前の呼出元へ戻る
        exit();
    } else {
        $_SESSION['s_sysmsg'] .= "<font color='yellow'>バイモルの棚卸金額を更新しました。</font><br>";      // .= メッセージを追加する
    }
}

/********************* カプラ特注の登録済みのチェック ****************************/
$search = "where invent_ym={$act_ym} and item='カプラ特注'";
//////////// ヘッダーにレコードがあるか？
$query = sprintf("select sum_money_t, sum_count from inventory_monthly_header %s", $search);
$res_sum = array();         // 初期化
if ( getResultTrs($con, $query, $res_sum) > 0) {         // $maxrows の取得
    $_SESSION['s_sysmsg'] .= "<font color='white'>カプラ特注のデータは登録済みです。</font><br>";      // .= メッセージを追加する
} else {
    //////////// 対象年月でカプラ特注のみを抜出したデータがあるかチェックを兼用する
    //////////// SQL 文の where 句を 共用する
    $search = "where  invent_ym={$act_ym} and num_div='5' and (select kouji_no
                                                      from
                                                            act_payable as act
                                                      left outer join
                                                            order_plan
                                                      using(sei_no)
                                                      where
                                                            act_date<={$e_ymd} and
                                                            act.parts_no=inv.parts_no
                                                      order by act_date DESC limit 1)
                                                      like 'SC%%'";     // num_div 1=機工 3=リニア 5=カプラ
    
    //////////// 表形式のデータ表示用のサンプル Query & 初期化
    $query = sprintf("insert into inventory_monthly_ctoku
            select
                invent_ym     as 年月,
                parts_no      as 部品番号,
                par_code      as 親製品,
                zen_zai       as 前月在庫,
                tou_zai       as 当月在庫,
                gai_tan       as 外注単価,
                nai_tan       as 内作単価,
                num_div       as 事業部,
                (select kouji_no from act_payable as act
                    left outer join order_plan using(sei_no)
                    where act_date<={$e_ymd} and act.parts_no=inv.parts_no
                    order by act_date DESC limit 1) as kouji_no
            from
                inventory_monthly as inv
            %s 
            ", $search);       // 共用 $search は使用
    
    /////////// トランザクション内で更新実行
    if (($rows = query_affected_trans($con, $query)) <= 0) {
        $_SESSION['s_sysmsg'] .= 'カプラ特注のデータ抜出しに失敗！';
        query_affected_trans($con, 'rollback');         // transaction rollback
        header("Location: $url_referer");               // 直前の呼出元へ戻る
        exit();
    }
    /*
    // さらに標準品の中から特注部品と共用の物の分を配賦率に従い登録 2017/10 より
    if ($act_ym >= 201710) {
        //////////// SQL 文の where 句を 共用する
        $search = "where  invent_ym={$act_ym} and num_div='5' and ctoku_allo > 0 and (select kouji_no
                                                          from
                                                                act_payable as act
                                                          left outer join
                                                                order_plan
                                                          using(sei_no)
                                                          where
                                                                act_date<={$e_ymd} and
                                                                act.parts_no=inv.parts_no
                                                          order by act_date DESC limit 1)
                                                          not like 'SC%%'";     // num_div 1=機工 3=リニア 5=カプラ
    
        //////////// 表形式のデータ表示用のサンプル Query & 初期化
        $query = sprintf("insert into inventory_monthly_ctoku
                select
                    invent_ym     as 年月,
                    parts_no      as 部品番号,
                    par_code      as 親製品,
                    round(zen_zai * ctoku_allo) as 前月在庫,
                    round(tou_zai * ctoku_allo) as 当月在庫,
                    gai_tan       as 外注単価,
                    nai_tan       as 内作単価,
                    num_div       as 事業部,
                    (select kouji_no from act_payable as act
                        left outer join order_plan using(sei_no)
                        where act_date<={$e_ymd} and act.parts_no=inv.parts_no
                        order by act_date DESC limit 1) as kouji_no
                from
                    inventory_monthly as inv
                left outer join
                    inventory_ctoku_par using(parts_no)
                %s 
                ", $search);       // 共用 $search は使用
    }
    /////////// トランザクション内で更新実行
    if (($rows = query_affected_trans($con, $query)) <= 0) {
        $_SESSION['s_sysmsg'] .= 'カプラ特注のデータ抜出しに失敗！';
        query_affected_trans($con, 'rollback');         // transaction rollback
        header("Location: $url_referer");               // 直前の呼出元へ戻る
        exit();
    }
    */
    //////////// 合計 金額・レコード数取得     (対象データの最大数をページ制御に使用)
    $search = "where  invent_ym={$act_ym}";     // 
    $query = sprintf('select
                        count(*),
                        sum(Uround(zen_zai * gai_tan, 0) + Uround(zen_zai * nai_tan, 0)) as 金額_z,
                        sum(Uround(tou_zai * gai_tan, 0) + Uround(tou_zai * nai_tan, 0)) as 金額_t
                      from inventory_monthly_ctoku as inv %s', $search);
    $res_sum = array();     // 初期化
    if ( getResultTrs($con, $query, $res_sum) <= 0) {         // $maxrows の取得
        $_SESSION['s_sysmsg'] .= "カプラ特注の合計金額が取得できません！";      // .= メッセージを追加する
        query_affected_trans($con, 'rollback');         // transaction rollback
        header("Location: $url_referer");                   // 直前の呼出元へ戻る
        exit();
    }
    $maxrows   = $res_sum[0][0];  // 合計レコード数
    $sum_kin_z = $res_sum[0][1];  // 合計 棚卸 金額(前月)
    $sum_kin_t = $res_sum[0][2];  // 合計 棚卸 金額(当月)
    /////////// レコードの有無をチェック
    if ( $maxrows == 0) {         // $maxrows でチェック
        $_SESSION['s_sysmsg'] .= "{$act_ym}：棚卸データがない！";   // .= メッセージを追加する
        query_affected_trans($con, 'rollback');             // transaction rollback
        header("Location: $url_referer");                   // 直前の呼出元へ戻る
        exit();
    }
    /////////// header テーブルに書込む
    $query = "insert into inventory_monthly_header (invent_ym, item, sum_money_z, sum_money_t, sum_count)
                    values ({$act_ym}, 'カプラ特注', {$sum_kin_z}, {$sum_kin_t}, {$maxrows})";
    if (($rows = query_affected_trans($con, $query)) <= 0) {
        $_SESSION['s_sysmsg'] .= 'カプラ特注のヘッダー書込みに失敗！';
        query_affected_trans($con, 'rollback');         // transaction rollback
        header("Location: $url_referer");               // 直前の呼出元へ戻る
        exit();
    } else {
        $_SESSION['s_sysmsg'] .= "<font color='yellow'>カプラ特注の棚卸金額を更新しました。</font><br>";      // .= メッセージを追加する
    }
}

/********************* その他の登録済みのチェック ****************************/
$search = "where invent_ym={$act_ym} and item='その他'";
//////////// ヘッダーにレコードがあるか？
$query = sprintf("select sum_money_t, sum_count from inventory_monthly_header %s", $search);
$res_sum = array();         // 初期化
if ( getResultTrs($con, $query, $res_sum) > 0) {         // $maxrows の取得
    $_SESSION['s_sysmsg'] .= "<font color='white'>その他のデータは登録済みです。</font><br>";      // .= メッセージを追加する
} else {
    //////////// 合計 金額・レコード数取得
    $search = "where invent_ym={$act_ym} and num_div != '3' and num_div != '5'";     // num_div 1=機工 3=リニア 5=カプラ
    $query = sprintf('select
                            count(*),
                            sum(Uround(zen_zai * gai_tan, 0) + Uround(zen_zai * nai_tan, 0)) as 金額_z,
                            sum(Uround(tou_zai * gai_tan, 0) + Uround(tou_zai * nai_tan, 0)) as 金額_t
                      from inventory_monthly as inv %s', $search);
    $res_sum = array();     // 初期化
    if ( getResultTrs($con, $query, $res_sum) <= 0) {         // $maxrows の取得
        $_SESSION['s_sysmsg'] .= "その他の合計金額が取得できません！";      // .= メッセージを追加する
        query_affected_trans($con, 'rollback');         // transaction rollback
        header("Location: $url_referer");                   // 直前の呼出元へ戻る
        exit();
    }
    $maxrows   = $res_sum[0][0];  // 合計レコード数
    $sum_kin_z = $res_sum[0][1];  // 合計 棚卸 金額(前月)
    $sum_kin_t = $res_sum[0][2];  // 合計 棚卸 金額(当月)
    /////////// レコードの有無をチェック
    if ( $maxrows == 0) {         // $maxrows でチェック
        $_SESSION['s_sysmsg'] .= "<font color='white'>{$act_ym}：その他の棚卸はありませんでした。</font>";   // .= メッセージを追加する
    } else {
        /////////// header テーブルに書込む
        $query = "insert into inventory_monthly_header (invent_ym, item, sum_money_z, sum_money_t, sum_count)
                        values ({$act_ym}, 'その他', {$sum_kin_z}, {$sum_kin_t}, {$maxrows})";
        if (($rows = query_affected_trans($con, $query)) <= 0) {
            $_SESSION['s_sysmsg'] .= 'その他のヘッダー書込みに失敗！';
            query_affected_trans($con, 'rollback');         // transaction rollback
            header("Location: $url_referer");               // 直前の呼出元へ戻る
            exit();
        } else {
            $_SESSION['s_sysmsg'] .= "<font color='yellow'>その他の棚卸金額を更新しました。</font><br>";      // .= メッセージを追加する
        }
    }
}

/////////// commit トランザクション終了
query_affected_trans($con, 'commit');
$_SESSION['s_sysmsg'] .= "<font color='white'>{$act_ym}：全て処理終了</font>";
header("Location: $url_referer");                   // 直前の呼出元へ戻る
// header('Location: http:' . WEB_HOST . 'account/inventory_monthly_ctoku_view.php');   // 照会スクリプトへ
exit();

/********** Logic End   **********/
?>
