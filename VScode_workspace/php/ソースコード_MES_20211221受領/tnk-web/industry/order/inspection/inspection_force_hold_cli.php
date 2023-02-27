#!/usr/local/bin/php
<?php
//////////////////////////////////////////////////////////////////////////////
// 受入検査の中断をタイムカードの打刻時間で制御する(中断忘れの歯止め) CLI版 //
// Copyright (C) 2007      Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp      //
// Changed history                                                          //
// 2007/08/31 Created  inspection_force_hold_cli.php                        //
// 2007/09/04 $_ENV['HOSTNAME']/$_SERVER['HOSTNAME']は使用できない→SYSTEMへ//
//////////////////////////////////////////////////////////////////////////////
ini_set('error_reporting', E_ALL);      // E_ALL='2047' debug 用
ini_set('implicit_flush', 'off');       // echo print で flush させない(遅くなるため)

function main()
{
    $currentFullPathName = realpath(dirname(__FILE__));
    require_once ("{$currentFullPathName}/../../../function.php");
    // require_once ('/home/www/html/tnk-web/function.php');
    
    $fpa = fopen('/tmp/timepro.log', 'a');  // 日報用ログファイルへの書込みでオープン
    $log_date = date('Y-m-d H:i:s');        // 日報用ログの日時
    
    /////////// begin トランザクション開始
    if ($con = db_connect()) {
        query_affected_trans($con, 'BEGIN');
    } else {
        fwrite($fpa, "$log_date db_connect() error \n");
        fclose($fpa);
        return;
    }
    /********** 中断開始の処理 **********/
    setForceHoldStart($fpa, $con);
    
    /********** 中断終了の処理 **********/
    setForceHoldEnd($fpa, $con);
    
    /////////// commit トランザクション終了
    query_affected_trans($con, 'COMMIT');
    fclose($fpa);      ////// 発注計画の差異データ用ログ書込み終了
    return;
}
main();
exit();


/********** 中断開始の処理 **********/
function setForceHoldStart($fpa, $con)
{
    /********** 現在の検査中データを抽出 **********/
    $query = "
        SELECT ken.uid FROM acceptance_kensa AS ken
        WHERE end_timestamp IS NULL AND str_timestamp IS NOT NULL AND
            (SELECT str_timestamp FROM inspection_holding WHERE order_seq = ken.order_seq AND str_timestamp IS NOT NULL AND end_timestamp IS NULL LIMIT 1)
            IS NULL
        GROUP BY ken.uid
    ";
    if ( ($rows=getResultTrs($con, $query, $res)) <= 0) {
        $log_date = date('Y-m-d H:i:s');
        fwrite($fpa, "$log_date 現在の検査中データがありません\n");
        return;
    }
    for ($i=0; $i<$rows; $i++) {
        /********** 上記の社員番号で退勤したかチェック **********/
        $uid = $res[$i][0];
        $date = date('Ymd');
        $query = "
            SELECT end_time FROM timepro_get_time(TEXT '{$uid}', TEXT '{$date}')
        ";
        getUniResTrs($con, $query, $end_time);
        if ($end_time == '') {
            $log_date = date('Y-m-d H:i:s');
            fwrite($fpa, "$log_date $uid さんは退勤してません\n");
        } else {
            $log_date = date('Y-m-d H:i:s');
            fwrite($fpa, "$log_date $uid さんは{$end_time}分に退勤したので強制中断します\n");
            /********** 上記の退勤時間がある社員の検査中データを抽出 (発行連番がキー) **********/
            $query = "
                SELECT ken.order_seq, ken.uid, ken.str_timestamp FROM acceptance_kensa AS ken
                WHERE end_timestamp IS NULL AND str_timestamp IS NOT NULL AND ken.uid = '{$uid}' AND
                    (SELECT str_timestamp FROM inspection_holding WHERE order_seq = ken.order_seq AND str_timestamp IS NOT NULL AND end_timestamp IS NULL LIMIT 1)
                    IS NULL
            ";
            if ( ($rows2=getResultTrs($con, $query, $res2)) <= 0) {
                $log_date = date('Y-m-d H:i:s');
                fwrite($fpa, "$log_date $uid さんの検査中データがなくなりましたので中断を中止します\n");
            } else {
                /********** 発行連番と社員番号で 強制 検査中断 処理 **********/
                for ($j=0; $j<$rows2; $j++) {
                    $sql = "
                        INSERT INTO inspection_holding (order_seq, str_timestamp, client, uid)
                        VALUES ({$res2[$j][0]}, '{$date} {$end_time}00', 'SYSTEM', '{$uid}')
                        ;
                        INSERT INTO inspection_force_hold (order_seq, str_timestamp, uid)
                        VALUES ({$res2[$j][0]}, '{$date} {$end_time}00', '{$uid}')
                    ";
                    if (query_affected_trans($con, $sql) <= 0) {
                        $log_date = date('Y-m-d H:i:s');
                        fwrite($fpa, "$log_date $uid さんの検査中データの強制中断に失敗しました\n");
                    } else {
                        $log_date = date('Y-m-d H:i:s');
                        fwrite($fpa, "$log_date $uid さんの検査中データを強制中断しました\n");
                    }
                }
            }
        }
    }
}

/********** 中断終了の処理 **********/
function setForceHoldEnd($fpa, $con)
{
    /********** 強制中断したものを社員番号で抽出 **********/
    $date = date('Ymd');
    $query = "
        SELECT uid, order_seq, str_timestamp FROM inspection_force_hold
        WHERE end_timestamp IS NULL AND CAST(str_timestamp AS DATE) != DATE '{$date}'
    ";
    if ( ($rows=getResultTrs($con, $query, $res)) <= 0) {
        $log_date = date('Y-m-d H:i:s');
        fwrite($fpa, "$log_date 該当する強制中断データがありません\n");
        return;
    } else {
        for ($i=0; $i<$rows; $i++) {
            /********** 上記の社員が出勤したかチェック **********/
            $uid = $res[$i][0];
            $order_seq = $res[$i][1];
            $query = "
                SELECT start_time FROM timepro_get_time(TEXT '{$uid}', TEXT '{$date}')
            ";
            getUniResTrs($con, $query, $start_time);
            if ($start_time == '') {
                $log_date = date('Y-m-d H:i:s');
                fwrite($fpa, "$log_date $uid さんは出勤していません\n");
            } else {
                /********** 出勤しているので中断終了のため更新 **********/
                $log_date = date('Y-m-d H:i:s');
                fwrite($fpa, "$log_date $uid さんは{$start_time}分に出勤したので強制中断を終了します\n");
                $sql = "
                    UPDATE inspection_holding SET end_timestamp = '{$date} {$start_time}00' WHERE order_seq = {$order_seq} AND str_timestamp = '{$res[$i][2]}'
                    ;
                    UPDATE inspection_force_hold SET end_timestamp = '{$date} {$start_time}00' WHERE order_seq = {$order_seq} AND str_timestamp = '{$res[$i][2]}'
                ";
                if (query_affected_trans($con, $sql) <= 0) {
                    $log_date = date('Y-m-d H:i:s');
                    fwrite($fpa, "$log_date $uid さんの強制中断の終了に失敗しました\n");
                } else {
                    $log_date = date('Y-m-d H:i:s');
                    fwrite($fpa, "$log_date $uid さんの強制中断を終了しました\n");
                }
            }
        }
    }
}


?>
