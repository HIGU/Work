#!/usr/local/bin/php
<?php
//////////////////////////////////////////////////////////////////////////////
// タイムカードの打刻時間(出勤・退勤)DAYLY.TXTをデータベースへ更新    CLI版 //
// Copyright (C) 2007-2008 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp      //
// Changed history                                                          //
// 2007/08/28 Created  timePro_update_cli.php                               //
// 2007/08/29 UPDATEの timepro = {$data} → timepro = '{$data}'へ修正       //
// 2007/08/31 Time Pro XG とのタイミング合わせロジックを追加                //
//            出退勤データを使って検査中データのチェックと強制更新処理追加  //
// 2008/10/09 週報Web表示の為タイムプロのデータ自動出力日数を               //
//           （当日から２日間)から(当日から４３日（最大）)に変更       大谷 //
//////////////////////////////////////////////////////////////////////////////
ini_set('error_reporting', E_ALL);      // E_ALL='2047' debug 用
ini_set('implicit_flush', 'off');       // echo print で flush させない(遅くなるため)

$currentFullPathName = realpath(dirname(__FILE__));
require_once ("{$currentFullPathName}/../../function.php");
// require_once ('/var/www/html/function.php');

$fpa = fopen('/tmp/timepro.log', 'a');  // 日報用ログファイルへの書込みでオープン

///// Time Pro XG とのタイミングを合わせるため Wait
while (date('s') < 40) {    // Time Pro が毎分10秒から29秒までに処理が完了しているため
    sleep(2);
}

$log_date = date('Y-m-d H:i:s');        // 日報用ログの日時
/////////// begin トランザクション開始
if ($con = db_connect()) {
    // query_affected_trans($con, 'BEGIN');
} else {
    fwrite($fpa, "$log_date db_connect() error \n");
    exit();
}

$file_orign  = '/home/guest/timepro/DAYLY.TXT';
$file_debug  = "{$currentFullPathName}/debug/debug-DAYLY.TXT";
$file_backup  = "{$currentFullPathName}/backup/backup-DAYLY.TXT";
///// 更新ファイルのタイムスタンプを取得
$save_file_time = "{$currentFullPathName}/timestamp.txt";
if (file_exists($save_file_time)) {
    $fpt  = fopen($save_file_time, 'r');
    $timestamp = fgets($fpt, 50);
    fclose($fpt);
} else {
    $timestamp = '';
}
if (file_exists($file_orign)) {         // ファイルの存在チェック
    $now = date('Ymd His', filemtime($file_orign));
    if ($now == $timestamp) {
        $log_date = date('Y-m-d H:i:s');
        fwrite($fpa, "$log_date DAYLY.TXTが変更されていないため処理を中止します。\n");
        fclose($fpa);
        exit();
    } else {
        $fpt  = fopen($save_file_time, 'w');
        fwrite($fpt, $now);
        fclose($fpt);
    }
    $fp  = fopen($file_orign, 'r');
    $fpw = fopen($file_debug, 'w');      // debug 用ファイルのオープン
    $fpb = fopen($file_backup, 'w');     // backup 用ファイルのオープン
    $rec = 0;       // レコード№
    $rec_ok = 0;    // 書込み成功レコード数
    $rec_ng = 0;    // 書込み失敗レコード数
    $ins_ok = 0;    // INSERT用カウンター
    $upd_ok = 0;    // UPDATE用カウンター
    $no_upd = 0;    // 未変更用カウンター
    while (!(feof($fp))) {
        $data = fgets($fp, 300);     // 実レコードは255バイトなのでちょっと余裕を
        if (feof($fp)) {
            break;
        }
        $rec++;
        
        $data = trim($data);       // 179～255のスペースを削除
        ///// バックアップへ書込み
        fwrite($fpb, "{$data}\n");
        if ($data == '') {
            $log_date = date('Y-m-d H:i:s');
            fwrite($fpa, "$log_date 空行なので飛ばします。\n");
            continue;
        }
        ////////// データの存在チェック
        $query = "
            SELECT * FROM timepro_daily_data WHERE timepro_index(timepro) = timepro_index('{$data}')
        ";
        if (getUniResult($query, $res_chk) > 0) {
            if ($res_chk === $data) {   // ===に注意(型も合わせている)
                ///// データの変更が無い なにもしない
                $no_upd++;
            } else {
                ///// 変更あり update 使用
                $query = "
                    UPDATE timepro_daily_data SET timepro = '{$data}' WHERE timepro_index(timepro) = timepro_index('{$data}')
                ";
                if (query_affected($query) <= 0) {      // 更新用クエリーの実行
                    $log_date = date('Y-m-d H:i:s');
                    fwrite($fpa, "$log_date {$rec}:レコード目のUPDATEに失敗しました!\n");
                    $rec_ng++;
                    ////////////////////////////////////////// Debug start
                    fwrite($fpw, "$query \n");              // debug
                    break;                                  // debug
                    ////////////////////////////////////////// Debug end
                } else {
                    $rec_ok++;
                    $upd_ok++;
                }
            }
        } else {    //////// 新規登録
            $query = "
                INSERT INTO timepro_daily_data VALUES ('{$data}')
            ";
            if (query_affected($query) <= 0) {
                $log_date = date('Y-m-d H:i:s');
                fwrite($fpa, "$log_date {$rec}:レコード目のINSERTに失敗しました!\n");
                $rec_ng++;
                ////////////////////////////////////////// Debug start
                fwrite($fpw, "$query \n");              // debug
                break;                                  // debug
                ////////////////////////////////////////// Debug end
            } else {
                $rec_ok++;
                $ins_ok++;
            }
        }
    }
    fclose($fp);
    fclose($fpw);       // debug
    fclose($fpb);       // backup
    $log_date = date('Y-m-d H:i:s');
    fwrite($fpa, "$log_date TimeProデータ更新 : {$rec_ok}/{$rec} 件登録しました。\n");
    fwrite($fpa, "$log_date TimeProデータ更新 : {$ins_ok}/{$rec} 件 追加 \n");
    fwrite($fpa, "$log_date TimeProデータ更新 : {$upd_ok}/{$rec} 件 変更 \n");
    fwrite($fpa, "$log_date TimeProデータ更新 : {$no_upd}/{$rec} 件 未変更 \n");
    // タイムプロデータを就業週報基本データに登録    
    // 登録対象日付の取得（41日前まで）
    $date = getdate();
    $stamp = mktime(
                  $date["hours"]
                , $date["minutes"]
                , $date["seconds"]
                , $date["mon"]
                , $date["mday"] - 41    // 41日前
                , $date["year"]
             );
    $date = getdate($stamp);
    $year = $date["year"];
    $month = $date["mon"];
    $day = $date["mday"];
    if ($month<10) {
        $month = '0' . $month;
    }
    if ($day<10) {
        $day = '0' . $day;
    }
    $str_date = $year . $month . $day;      // 登録対象日付
    
    $query = "SELECT substr(timepro, 3, 6) AS 社員番号      -- 00 uid            CHARACTER(6)
                    ,substr(timepro, 17, 8) AS 年月         -- 01 working_date   CHARACTER(8)
                    ,substr(timepro, 25, 2) AS 曜日         -- 02 working_day    CHARACTER(2)
                    ,substr(timepro, 27, 2) AS カレンダ     -- 03 calendar       CHARACTER(2)
                    ,substr(timepro, 173, 2) AS 不在理由    -- 04 absence        CHARACTER(2)
                    ,substr(timepro, 33, 4) AS 出勤時間     -- 05 str_time       CHARACTER(4)
                    ,substr(timepro, 41, 4) AS 退勤時間     -- 06 end_time       CHARACTER(4)
                    ,substr(timepro, 79, 6) AS 所定時間     -- 07 fixed_time     CHARACTER(6)
                    ,substr(timepro, 97, 6) AS 延長時間     -- 08 extend_time    CHARACTER(6)
                    ,substr(timepro, 85, 6) AS 早出時間     -- 09 earlytime      CHARACTER(6)
                    ,substr(timepro, 91, 6) AS 残業時間     -- 10 overtime       CHARACTER(6)
                    ,substr(timepro, 109, 6) AS 深夜残業    -- 11 midnight_over  CHARACTER(6)
                    ,substr(timepro, 115, 6) AS 休出時間    -- 12 holiday_time   CHARACTER(6)
                    ,substr(timepro, 121, 6) AS 休出残業    -- 13 holiday_over   CHARACTER(6)
                    ,substr(timepro, 127, 6) AS 休出深夜    -- 14 holiday_mid    CHARACTER(6)
                    ,substr(timepro, 155, 6) AS 法定時間    -- 15 legal_time     CHARACTER(6)
                    ,substr(timepro, 161, 6) AS 法定残業    -- 16 legal_over     CHARACTER(6)
                    ,substr(timepro, 133, 6) AS 遅早時間    -- 17 late_time      CHARACTER(6)
                    ,substr(timepro, 37, 2) AS 出勤ＭＣ     -- 18 str_mc         CHARACTER(2)
                    ,substr(timepro, 103, 6) AS 深夜早出    -- 19 early_mid      CHARACTER(6)
                    ,substr(timepro, 167, 6) AS 法定深夜    -- 20 legal_mid      CHARACTER(6)
                    ,substr(timepro, 139, 6) AS 私用外出    -- 21 private_out    CHARACTER(6)
                    ,substr(timepro, 175, 1) AS 集計区分    -- 22 total_div      CHARACTER(1)
              FROM timepro_daily_data 
              WHERE substr(timepro, 17, 8) >= {$str_date} 
              ORDER BY 社員番号 , 年月;
             ";
    
} else {
    $log_date = date('Y-m-d H:i:s');
    fwrite($fpa,"$log_date ファイル$file_orign がありません!\n");
}
/////////// commit トランザクション終了
// query_affected_trans($con, 'COMMIT');
// echo $query . "\n";  // debug
fclose($fpa);      ////// 発注計画の差異データ用ログ書込み終了


/************** タイムレコーダーのデータを使って検査中データのチェックと強制更新処理 **************/
///// 2007/08/31 ADD
`{$currentFullPathName}/../../industry/order/inspection/inspection_force_hold_cli.php`;

?>
