#!/usr/local/bin/php
<?php
//////////////////////////////////////////////////////////////////////////////
// タイムカードの打刻時間(出勤・退勤)DAYLY.TXTをデータベースへ更新    CLI版 //
// 189行目の日付を直して、スポット使用                                      //
// Copyright (C) 2007-2017 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp      //
// Changed history                                                          //
// 2007/08/28 Created  timePro_update_cli.php                               //
// 2007/08/29 UPDATEの timepro = {$data} → timepro = '{$data}'へ修正       //
// 2007/08/31 Time Pro XG とのタイミング合わせロジックを追加                //
//            出退勤データを使って検査中データのチェックと強制更新処理追加  //
// 2008/10/09 週報Web表示の為タイムプロのデータ自動出力日数を               //
//           （当日から２日間)から(当日から４３日（最大）)に変更       大谷 //
// 2017/05/16 timeproデータを週報用データに変換登録を追加              大谷 //
// 2017/08/02 外出MCを追加 中抜け対応                                  大谷 //
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
    $date  = getdate();
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
    
    $working_data = array();
    
    $query = "SELECT substr(timepro, 3, 6) AS 社員番号    -- 00
                    ,substr(timepro, 17, 8) AS 年月         -- 01
                    ,substr(timepro, 25, 2) AS 曜日         -- 02
                    ,substr(timepro, 27, 2) AS カレンダ     -- 03
                    ,substr(timepro, 173, 2) AS 不在理由    -- 04
                    ,substr(timepro, 33, 4) AS 出勤時間     -- 05
                    ,substr(timepro, 41, 4) AS 退勤時間     -- 06
                    ,substr(timepro, 79, 6) AS 所定時間     -- 07
                    ,substr(timepro, 97, 6) AS 延長時間     -- 08
                    ,substr(timepro, 85, 6) AS 早出時間     -- 09
                    ,substr(timepro, 91, 6) AS 残業時間     -- 10
                    ,substr(timepro, 109, 6) AS 深夜残業    -- 11
                    ,substr(timepro, 115, 6) AS 休出時間    -- 12
                    ,substr(timepro, 121, 6) AS 休出残業    -- 13
                    ,substr(timepro, 127, 6) AS 休出深夜    -- 14
                    ,substr(timepro, 155, 6) AS 法定時間    -- 15
                    ,substr(timepro, 161, 6) AS 法定残業    -- 16
                    ,substr(timepro, 133, 6) AS 遅早時間    -- 17
                    ,substr(timepro, 37, 2) AS 出勤ＭＣ     -- 18
                    ,substr(timepro, 103, 6) AS 深夜早出    -- 19
                    ,substr(timepro, 167, 6) AS 法定深夜    -- 20
                    ,substr(timepro, 139, 6) AS 私用外出    -- 21
                    ,substr(timepro, 175, 1) AS 集計区分    -- 22
                    ,substr(timepro, 45, 2)  AS 退勤ＭＣ    -- 23
                    ,substr(timepro, 53, 2)  AS 外出ＭＣ    -- 24
              FROM timepro_daily_data 
              WHERE substr(timepro, 17, 8) >= 20180301 and substr(timepro, 17, 8) <= 20180631
              ORDER BY 社員番号 , 年月;
            ";
    if (($rows = getResultWithField2($query, $field, $working_data)) <= 0) {
        $num = 0;
    } else {
        $num = count($field) + 1;
        $last_date = date('Y-m-d H:i:s');
        for ($r=0; $r<$rows; $r++) {
            $query_chk = sprintf("SELECT * FROM working_hours_report_data_new WHERE uid='%s' AND working_date=%d", $working_data[$r][0], $working_data[$r][1]);
            $res_chk = array();
            if ( getResult($query_chk, $res_chk) > 0 ) {    // 登録あり UPDATE 更新
                $sql = "
                        UPDATE working_hours_report_data_new SET
                          calendar='{$working_data[$r][3]}', absence='{$working_data[$r][4]}', str_time='{$working_data[$r][5]}'
                        , end_time='{$working_data[$r][6]}', fixed_time='{$working_data[$r][7]}', extend_time='{$working_data[$r][8]}'
                        , earlytime='{$working_data[$r][9]}', overtime='{$working_data[$r][10]}', midnight_over='{$working_data[$r][11]}'
                        , holiday_time='{$working_data[$r][12]}', holiday_over='{$working_data[$r][13]}', holiday_mid='{$working_data[$r][14]}'
                        , legal_time='{$working_data[$r][15]}', legal_over='{$working_data[$r][16]}', late_time='{$working_data[$r][17]}'
                        , str_mc='{$working_data[$r][18]}', early_mid='{$working_data[$r][19]}', legal_mid='{$working_data[$r][20]}'
                        , private_out='{$working_data[$r][21]}', total_div='{$working_data[$r][22]}', end_mc='{$working_data[$r][23]}'
                        , out_mc='{$working_data[$r][24]}', last_date='$last_date'
                        WHERE uid='{$working_data[$r][0]}' AND working_date='{$working_data[$r][1]}'
                        ;
                    ";
            } else {                                        // 登録なし INSERT 追加
                $sql = "
                        INSERT INTO working_hours_report_data_new
                        (uid, working_date, working_day, calendar, absence, str_time, end_time, fixed_time, extend_time, earlytime, overtime, midnight_over, holiday_time, holiday_over, holiday_mid, legal_time, legal_over, late_time, str_mc, early_mid, legal_mid, private_out, total_div, end_mc, out_mc, regdate, last_date)
                        VALUES
                        ('{$working_data[$r][0]}', '{$working_data[$r][1]}', '{$working_data[$r][2]}', '{$working_data[$r][3]}', '{$working_data[$r][4]}', '{$working_data[$r][5]}', '{$working_data[$r][6]}', '{$working_data[$r][7]}', '{$working_data[$r][8]}', '{$working_data[$r][9]}', '{$working_data[$r][10]}', '{$working_data[$r][11]}', '{$working_data[$r][12]}', '{$working_data[$r][13]}', '{$working_data[$r][14]}'
                        , '{$working_data[$r][15]}', '{$working_data[$r][16]}', '{$working_data[$r][17]}', '{$working_data[$r][18]}', '{$working_data[$r][19]}', '{$working_data[$r][20]}', '{$working_data[$r][21]}', '{$working_data[$r][22]}', '{$working_data[$r][23]}', '{$working_data[$r][24]}', '$last_date', '$last_date')
                        ;
                    ";
            }
            query_affected($sql);
        }
    }
    
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
