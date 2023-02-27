#!/usr/local/bin/php
<?php
//////////////////////////////////////////////////////////////////////////////
// #!/usr/local/bin/php-5.0.2-cli                                           //
// 部品在庫金額 日報(daily)処理                                             //
// Copyright(C) 2016-2016 Norihisa.Ohya norihisa_ooya@nitto-kohki.co.jp     //
// Changed history                                                          //
// 2016/09/16 新規作成 daily_stock_cli.php                                  //
// 2016/09/20 カプラ特注・標準を追加                                        //
//////////////////////////////////////////////////////////////////////////////
ini_set('error_reporting', E_ALL);          // E_ALL='2047' debug 用
ini_set('implicit_flush', 'off');           // echo print で flush させない(遅くなるためcli版)
// ini_set('display_errors','1');              // Error 表示 ON debug 用 リリース後コメント
// ini_set('max_execution_time', 1200);        // 最大実行時間=20分
require_once ('/home/www/html/tnk-web/function.php');

$log_date = date('Y-m-d H:i:s');            ///// 日報用ログの日時
$fpa = fopen('/tmp/nippo.log', 'a');        ///// 日報用ログファイルへの書込みでオープン
$fpb = fopen('/tmp/as400get_ftp_re.log', 'a');    ///// 日報データ再取得用ログファイルへの書込みでオープン
fwrite($fpb, "日次部品在庫金額の更新\n");
fwrite($fpb, "/home/www/html/tnk-web/system/daily/daily_stock_cli.php\n");

/////////// begin トランザクション開始
if ($con = db_connect()) {
    query_affected_trans($con, 'begin');
} else {
    fwrite($fpa, "$log_date 日次部品在庫金額の更新 db_connect() error \n");
    fwrite($fpb, "$log_date 日次部品在庫金額の更新 db_connect() error \n");
    echo "$log_date 日次部品在庫金額の更新 db_connect() error \n\n";
    exit();
}

//////////// 当日の取得
$today = date('Ymd');

///// 対象当月
$yyyymm = date('Ym');
///// 対象前月
if (substr($yyyymm,4,2)!=01) {
    $p1_ym = $yyyymm - 1;
} else {
    $p1_ym = $yyyymm - 100;
    $p1_ym = $p1_ym + 11;
}
///// 対象前々月
if (substr($p1_ym,4,2)!=01) {
    $p2_ym = $p1_ym - 1;
} else {
    $p2_ym = $p1_ym - 100;
    $p2_ym = $p2_ym + 11;
}

// 総平均単価対象月確認 前月がない場合は前々月
$query_chk = sprintf("SELECT average_cost FROM periodic_average_cost_history2 WHERE period_ym=%d limit 1", $p1_ym);
if (getUniResTrs($con, $query_chk, $res_chk) <= 0) {    // トランザクション内での 照会専用クエリー
    // 前月データ無し、対象月は前々月
    $sou_ym = $p2_ym;
} else {
    // 前月データあり、対象月は前月
    $sou_ym = $p1_ym;
}

//////////// カプラ日次部品在庫金額の更新
$query_csv = sprintf("SELECT
                        SUM(CASE
                                WHEN (SELECT max(period_ym) FROM periodic_average_cost_history2 WHERE parts_no=m.parts_no) = %d
                                THEN ROUND(m.tnk_stock*(SELECT average_cost FROM periodic_average_cost_history2 WHERE parts_no=m.parts_no and period_ym = %d))
                            ELSE ROUND(m.tnk_stock*(SELECT SUM(lot_cost) FROM parts_cost_history where parts_no=m.parts_no and vendor <> '88888' and reg_no = (SELECT max(reg_no) FROM parts_cost_history where parts_no=m.parts_no and vendor <> '88888' and as_regdate=(SELECT max(as_regdate) FROM parts_cost_history where parts_no=m.parts_no and vendor <> '88888'))))
                            END)  as 在庫金額
                        FROM parts_stock_master AS m 
                        WHERE m.tnk_stock <> 0
                        and m.stock_id <> 'C'
                        and substr(m.parts_no, 1, 1)='C'
                    ", $sou_ym, $sou_ym);
$res_csv   = array();
$field_csv = array();
if (($rows_csv = getResultWithField3($query_csv, $field_csv, $res_csv)) <= 0) {
    echo "$log_date カプラ日次部品在庫金額更新失敗 \n";
    fwrite($fpa,"$log_date カプラ日次部品在庫金額更新失敗 \n");
    fwrite($fpb,"$log_date カプラ日次部品在庫金額更新失敗 \n");
    echo "$log_date カプラ日次部品在庫金額更新失敗 \n";
} else {
    $query_chk = sprintf("SELECT kin FROM daily_stock_money_history WHERE stock_ymd=%d and note='カプラ'", $today);
    if (getUniResTrs($con, $query_chk, $res_chk) <= 0) {    // トランザクション内での 照会専用クエリー
        ///// 登録なし insert 使用
        $query = sprintf("insert into daily_stock_money_history (stock_ymd, kin, note, last_date) values (%d, %d, 'カプラ', CURRENT_TIMESTAMP)", $today, $res_csv[0][0]);
        if (query_affected_trans($con, $query) <= 0) {      // 更新用クエリーの実行
            fwrite($fpa, "$log_date カプラ日次部品在庫金額追加失敗 \n");
            fwrite($fpb, "$log_date カプラ日次部品在庫金額追加失敗 \n");
            echo "$log_date カプラ日次部品在庫金額追加失敗 \n";
        } else {
            $kin = number_format($res_csv[0][0]);
            fwrite($fpa, "$log_date カプラ日次部品在庫金額追加 : {$kin} \n");
            fwrite($fpb, "$log_date カプラ日次部品在庫金額追加 : {$kin} \n");
            echo "$log_date カプラ日次部品在庫金額追加 : ", $kin, "\n";
        }
    } else {
        ///// 登録あり update 使用
        $query = sprintf("update daily_stock_money_history set kin=%d, last_date=CURRENT_TIMESTAMP where stock_ymd=%d and note='カプラ'", $res_csv[0][0], $today);
        if (query_affected_trans($con, $query) <= 0) {      // 更新用クエリーの実行
            fwrite($fpa, "$log_date カプラ日次部品在庫金額更新失敗 \n");
            fwrite($fpb, "$log_date カプラ日次部品在庫金額更新失敗 \n");
            echo "$log_date カプラ日次部品在庫金額更新失敗 \n";
        } else {
            $kin = number_format($res_csv[0][0]);
            fwrite($fpa, "$log_date カプラ日次部品在庫金額更新 : {$kin} \n");
            fwrite($fpb, "$log_date カプラ日次部品在庫金額更新 : {$kin} \n");
            echo "$log_date カプラ日次部品在庫金額更新 : ", $kin, "\n";
        }
    }
}

//////////// リニア日次部品在庫金額の更新
$query_csv = sprintf("SELECT
                        SUM(CASE
                                WHEN (SELECT max(period_ym) FROM periodic_average_cost_history2 WHERE parts_no=m.parts_no) = %d
                                THEN ROUND(m.tnk_stock*(SELECT average_cost FROM periodic_average_cost_history2 WHERE parts_no=m.parts_no and period_ym = %d))
                            ELSE ROUND(m.tnk_stock*(SELECT SUM(lot_cost) FROM parts_cost_history where parts_no=m.parts_no and vendor <> '88888' and reg_no = (SELECT max(reg_no) FROM parts_cost_history where parts_no=m.parts_no and vendor <> '88888' and as_regdate=(SELECT max(as_regdate) FROM parts_cost_history where parts_no=m.parts_no and vendor <> '88888'))))
                            END)  as 在庫金額
                        FROM parts_stock_master AS m 
                        WHERE m.tnk_stock <> 0
                        and m.stock_id <> 'C'
                        and substr(m.parts_no, 1, 1)='L'
                    ", $sou_ym, $sou_ym);
$res_csv   = array();
$field_csv = array();
if (($rows_csv = getResultWithField3($query_csv, $field_csv, $res_csv)) <= 0) {
    echo "$log_date リニア日次部品在庫金額更新失敗 \n";
    fwrite($fpa,"$log_date リニア日次部品在庫金額更新失敗 \n");
    fwrite($fpb,"$log_date リニア日次部品在庫金額更新失敗 \n");
} else {
    $query_chk = sprintf("SELECT kin FROM daily_stock_money_history WHERE stock_ymd=%d and note='リニア'", $today);
    if (getUniResTrs($con, $query_chk, $res_chk) <= 0) {    // トランザクション内での 照会専用クエリー
        ///// 登録なし insert 使用
        $query = sprintf("insert into daily_stock_money_history (stock_ymd, kin, note, last_date) values (%d, %d, 'リニア', CURRENT_TIMESTAMP)", $today, $res_csv[0][0]);
        if (query_affected_trans($con, $query) <= 0) {      // 更新用クエリーの実行
            fwrite($fpa, "$log_date リニア日次部品在庫金額追加失敗 \n");
            fwrite($fpb, "$log_date リニア日次部品在庫金額追加失敗 \n");
            echo "$log_date リニア日次部品在庫金額追加失敗 \n";
        } else {
            $kin = number_format($res_csv[0][0]);
            fwrite($fpa, "$log_date リニア日次部品在庫金額追加 : {$kin} \n");
            fwrite($fpb, "$log_date リニア日次部品在庫金額追加 : {$kin} \n");
            echo "$log_date リニア日次部品在庫金額追加 : ", $kin, "\n";
        }
    } else {
        ///// 登録あり update 使用
        $query = sprintf("update daily_stock_money_history set kin=%d, last_date=CURRENT_TIMESTAMP where stock_ymd=%d and note='リニア'", $res_csv[0][0], $today);
        if (query_affected_trans($con, $query) <= 0) {      // 更新用クエリーの実行
            fwrite($fpa, "$log_date リニア日次部品在庫金額更新失敗 \n");
            fwrite($fpb, "$log_date リニア日次部品在庫金額更新失敗 \n");
            echo "$log_date リニア日次部品在庫金額更新失敗 \n";
        } else {
            $kin = number_format($res_csv[0][0]);
            fwrite($fpa, "$log_date リニア日次部品在庫金額更新 : {$kin} \n");
            fwrite($fpb, "$log_date リニア日次部品在庫金額更新 : {$kin} \n");
            echo "$log_date リニア日次部品在庫金額更新 : ", $kin, "\n";
        }
    }
}

//////////// ツール日次部品在庫金額の更新
$query_csv = sprintf("SELECT
                        SUM(CASE
                                WHEN (SELECT max(period_ym) FROM periodic_average_cost_history2 WHERE parts_no=m.parts_no) = %d
                                THEN ROUND(m.tnk_stock*(SELECT average_cost FROM periodic_average_cost_history2 WHERE parts_no=m.parts_no and period_ym = %d))
                            ELSE ROUND(m.tnk_stock*(SELECT SUM(lot_cost) FROM parts_cost_history where parts_no=m.parts_no and vendor <> '88888' and reg_no = (SELECT max(reg_no) FROM parts_cost_history where parts_no=m.parts_no and vendor <> '88888' and as_regdate=(SELECT max(as_regdate) FROM parts_cost_history where parts_no=m.parts_no and vendor <> '88888'))))
                            END)  as 在庫金額
                        FROM parts_stock_master AS m 
                        WHERE m.tnk_stock <> 0
                        and m.stock_id <> 'C'
                        and substr(m.parts_no, 1, 1)<>'C' and substr(m.parts_no, 1, 1)<>'L'
                    ", $sou_ym, $sou_ym);
$res_csv   = array();
$field_csv = array();
if (($rows_csv = getResultWithField3($query_csv, $field_csv, $res_csv)) <= 0) {
    echo "$log_date ツール日次部品在庫金額更新失敗 \n";
    fwrite($fpa,"$log_date ツール日次部品在庫金額更新失敗 \n");
    fwrite($fpb,"$log_date ツール日次部品在庫金額更新失敗 \n");
} else {
    $query_chk = sprintf("SELECT kin FROM daily_stock_money_history WHERE stock_ymd=%d and note='ツール'", $today);
    if (getUniResTrs($con, $query_chk, $res_chk) <= 0) {    // トランザクション内での 照会専用クエリー
        ///// 登録なし insert 使用
        $query = sprintf("insert into daily_stock_money_history (stock_ymd, kin, note, last_date) values (%d, %d, 'ツール', CURRENT_TIMESTAMP)", $today, $res_csv[0][0]);
        if (query_affected_trans($con, $query) <= 0) {      // 更新用クエリーの実行
            fwrite($fpa, "$log_date ツール日次部品在庫金額追加失敗 \n");
            fwrite($fpb, "$log_date ツール日次部品在庫金額追加失敗 \n");
            echo "$log_date ツール日次部品在庫金額追加失敗 \n";
        } else {
            $kin = number_format($res_csv[0][0]);
            fwrite($fpa, "$log_date ツール日次部品在庫金額追加 : {$kin} \n");
            fwrite($fpb, "$log_date ツール日次部品在庫金額追加 : {$kin} \n");
            echo "$log_date ツール日次部品在庫金額追加 : ", $kin, "\n";
        }
    } else {
        ///// 登録あり update 使用
        $query = sprintf("update daily_stock_money_history set kin=%d, last_date=CURRENT_TIMESTAMP where stock_ymd=%d and note='ツール'", $res_csv[0][0], $today);
        if (query_affected_trans($con, $query) <= 0) {      // 更新用クエリーの実行
            fwrite($fpa, "$log_date ツール日次部品在庫金額更新失敗 \n");
            fwrite($fpb, "$log_date ツール日次部品在庫金額更新失敗 \n");
            echo "$log_date ツール日次部品在庫金額更新失敗 \n";
        } else {
            $kin = number_format($res_csv[0][0]);
            fwrite($fpa, "$log_date ツール日次部品在庫金額更新 : {$kin} \n");
            fwrite($fpb, "$log_date ツール日次部品在庫金額更新 : {$kin} \n");
            echo "$log_date ツール日次部品在庫金額更新 : ", $kin, "\n";
        }
    }
}

$act_date = $yyyymm . '99';

//////////// カプラ標準日次部品在庫金額の更新
$query_csv = sprintf("SELECT
                        SUM(CASE
                                WHEN (SELECT max(period_ym) FROM periodic_average_cost_history2 WHERE parts_no=m.parts_no) = %d
                                THEN ROUND(m.tnk_stock*(SELECT average_cost FROM periodic_average_cost_history2 WHERE parts_no=m.parts_no and period_ym = %d))
                            ELSE ROUND(m.tnk_stock*(SELECT SUM(lot_cost) FROM parts_cost_history where parts_no=m.parts_no and vendor <> '88888' and reg_no = (SELECT max(reg_no) FROM parts_cost_history where parts_no=m.parts_no and vendor <> '88888' and as_regdate=(SELECT max(as_regdate) FROM parts_cost_history where parts_no=m.parts_no and vendor <> '88888'))))
                            END)  as 在庫金額
                        FROM parts_stock_master AS m 
                        WHERE m.tnk_stock <> 0
                            and m.stock_id <> 'C'
                            and substr(m.parts_no, 1, 1)='C'
                            and (case when   
                                    (select kouji_no
                                    from
                                        act_payable as act
                                    left outer join
                                        order_plan
                                        using(sei_no)
                                    where
                                        act_date<=%d and act.parts_no=m.parts_no
                                        order by act_date DESC limit 1) is null THEN ''
                                ELSE
                                    (select kouji_no
                                    from
                                        act_payable as act
                                    left outer join
                                        order_plan
                                        using(sei_no)
                                    where
                                        act_date<=%d and act.parts_no=m.parts_no
                                    order by act_date DESC limit 1)
                                END
                                ) not like 'SC%%'
                        ", $sou_ym, $sou_ym, $act_date, $act_date);
$res_csv   = array();
$field_csv = array();
if (($rows_csv = getResultWithField3($query_csv, $field_csv, $res_csv)) <= 0) {
    echo "$log_date カプラ標準日次部品在庫金額更新失敗 \n";
    fwrite($fpa,"$log_date カプラ標準日次部品在庫金額更新失敗 \n");
    fwrite($fpb,"$log_date カプラ標準日次部品在庫金額更新失敗 \n");
    echo "$log_date カプラ標準日次部品在庫金額更新失敗 \n";
} else {
    $query_chk = sprintf("SELECT kin FROM daily_stock_money_history WHERE stock_ymd=%d and note='カプラ標準'", $today);
    if (getUniResTrs($con, $query_chk, $res_chk) <= 0) {    // トランザクション内での 照会専用クエリー
        ///// 登録なし insert 使用
        $query = sprintf("insert into daily_stock_money_history (stock_ymd, kin, note, last_date) values (%d, %d, 'カプラ標準', CURRENT_TIMESTAMP)", $today, $res_csv[0][0]);
        if (query_affected_trans($con, $query) <= 0) {      // 更新用クエリーの実行
            fwrite($fpa, "$log_date カプラ標準日次部品在庫金額追加失敗 \n");
            fwrite($fpb, "$log_date カプラ標準日次部品在庫金額追加失敗 \n");
            echo "$log_date カプラ標準日次部品在庫金額追加失敗 \n";
        } else {
            $kin = number_format($res_csv[0][0]);
            fwrite($fpa, "$log_date カプラ標準日次部品在庫金額追加 : {$kin} \n");
            fwrite($fpb, "$log_date カプラ標準日次部品在庫金額追加 : {$kin} \n");
            echo "$log_date カプラ標準日次部品在庫金額追加 : ", $kin, "\n";
        }
    } else {
        ///// 登録あり update 使用
        $query = sprintf("update daily_stock_money_history set kin=%d, last_date=CURRENT_TIMESTAMP where stock_ymd=%d and note='カプラ標準'", $res_csv[0][0], $today);
        if (query_affected_trans($con, $query) <= 0) {      // 更新用クエリーの実行
            fwrite($fpa, "$log_date カプラ標準日次部品在庫金額更新失敗 \n");
            fwrite($fpb, "$log_date カプラ標準日次部品在庫金額更新失敗 \n");
            echo "$log_date カプラ標準日次部品在庫金額更新失敗 \n";
        } else {
            $kin = number_format($res_csv[0][0]);
            fwrite($fpa, "$log_date カプラ標準日次部品在庫金額更新 : {$kin} \n");
            fwrite($fpb, "$log_date カプラ標準日次部品在庫金額更新 : {$kin} \n");
            echo "$log_date カプラ標準日次部品在庫金額更新 : ", $kin, "\n";
        }
    }
}

//////////// カプラ特注日次部品在庫金額の更新
$query_csv = sprintf("SELECT
                        SUM(CASE
                                WHEN (SELECT max(period_ym) FROM periodic_average_cost_history2 WHERE parts_no=m.parts_no) = %d
                                THEN ROUND(m.tnk_stock*(SELECT average_cost FROM periodic_average_cost_history2 WHERE parts_no=m.parts_no and period_ym = %d))
                            ELSE ROUND(m.tnk_stock*(SELECT SUM(lot_cost) FROM parts_cost_history where parts_no=m.parts_no and vendor <> '88888' and reg_no = (SELECT max(reg_no) FROM parts_cost_history where parts_no=m.parts_no and vendor <> '88888' and as_regdate=(SELECT max(as_regdate) FROM parts_cost_history where parts_no=m.parts_no and vendor <> '88888'))))
                            END)  as 在庫金額
                        FROM parts_stock_master AS m 
                        WHERE m.tnk_stock <> 0
                            and m.stock_id <> 'C'
                            and substr(m.parts_no, 1, 1)='C'
                            and (select kouji_no
                                from
                                    act_payable as act
                                left outer join
                                    order_plan
                                    using(sei_no)
                                where
                                    act_date<=%d and act.parts_no=m.parts_no
                                order by act_date DESC limit 1) like 'SC%%'
                        ", $sou_ym, $sou_ym, $act_date);
$res_csv   = array();
$field_csv = array();
if (($rows_csv = getResultWithField3($query_csv, $field_csv, $res_csv)) <= 0) {
    echo "$log_date カプラ特注日次部品在庫金額更新失敗 \n";
    fwrite($fpa,"$log_date カプラ特注日次部品在庫金額更新失敗 \n");
    fwrite($fpb,"$log_date カプラ特注日次部品在庫金額更新失敗 \n");
    echo "$log_date カプラ特注日次部品在庫金額更新失敗 \n";
} else {
    $query_chk = sprintf("SELECT kin FROM daily_stock_money_history WHERE stock_ymd=%d and note='カプラ特注'", $today);
    if (getUniResTrs($con, $query_chk, $res_chk) <= 0) {    // トランザクション内での 照会専用クエリー
        ///// 登録なし insert 使用
        $query = sprintf("insert into daily_stock_money_history (stock_ymd, kin, note, last_date) values (%d, %d, 'カプラ特注', CURRENT_TIMESTAMP)", $today, $res_csv[0][0]);
        if (query_affected_trans($con, $query) <= 0) {      // 更新用クエリーの実行
            fwrite($fpa, "$log_date カプラ特注日次部品在庫金額追加失敗 \n");
            fwrite($fpb, "$log_date カプラ特注日次部品在庫金額追加失敗 \n");
            echo "$log_date カプラ特注日次部品在庫金額追加失敗 \n";
        } else {
            $kin = number_format($res_csv[0][0]);
            fwrite($fpa, "$log_date カプラ特注日次部品在庫金額追加 : {$kin} \n");
            fwrite($fpb, "$log_date カプラ特注日次部品在庫金額追加 : {$kin} \n");
            echo "$log_date カプラ特注日次部品在庫金額追加 : ", $kin, "\n";
        }
    } else {
        ///// 登録あり update 使用
        $query = sprintf("update daily_stock_money_history set kin=%d, last_date=CURRENT_TIMESTAMP where stock_ymd=%d and note='カプラ特注'", $res_csv[0][0], $today);
        if (query_affected_trans($con, $query) <= 0) {      // 更新用クエリーの実行
            fwrite($fpa, "$log_date カプラ特注日次部品在庫金額更新失敗 \n");
            fwrite($fpb, "$log_date カプラ特注日次部品在庫金額更新失敗 \n");
            echo "$log_date カプラ特注日次部品在庫金額更新失敗 \n";
        } else {
            $kin = number_format($res_csv[0][0]);
            fwrite($fpa, "$log_date カプラ特注日次部品在庫金額更新 : {$kin} \n");
            fwrite($fpb, "$log_date カプラ特注日次部品在庫金額更新 : {$kin} \n");
            echo "$log_date カプラ特注日次部品在庫金額更新 : ", $kin, "\n";
        }
    }
}

/////////// commit トランザクション終了
query_affected_trans($con, 'commit');
fclose($fpa);      ////// 日報用ログ書込み終了
fwrite($fpb, "------------------------------------------------------------------------\n");
fclose($fpb);      ////// 日報データ再取得用ログ書込み終了
?>
