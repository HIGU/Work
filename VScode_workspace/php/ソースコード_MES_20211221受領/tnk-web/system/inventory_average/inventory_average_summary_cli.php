#!/usr/local/bin/php
<?php
//////////////////////////////////////////////////////////////////////////////
// 資材在庫部品 保有月等のサマリーファイル (SIDZKIL4) 取込 処理用 CLI版     //
// AS/400 ----> Web Server (PHP)                                            //
// Copyright (C) 2007 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp           //
// Changed history                                                          //
// 2007/05/17 Created  inventory_average_summary_cli.php                    //
// 2007/06/09 在庫0になったものは対象外のため、更新前に一旦全て削除を追加   //
//////////////////////////////////////////////////////////////////////////////
ini_set('error_reporting', E_ALL);          // E_ALL='2047' debug 用
ini_set('implicit_flush', '0');             // echo print で flush させない(遅くなるため)
// ini_set('max_execution_time', 1200);        // 最大実行時間=20分 CLI版なので必要ない

$currentFullPathName = realpath(dirname(__FILE__));
require_once ("{$currentFullPathName}/../../function.php");

$log_date = date('Y-m-d H:i:s');        ///// 日報用ログの日時
$fpa = fopen('/tmp/nippo.log', 'a');    ///// 日報用ログファイルへの書込みでオープン

/*****************************************************************************************
// FTPのターゲットファイル
$target_file = 'UKWLIB/W#SIDZKI';           // AS/400ファイル download
// 保存先のディレクトリとファイル名
$save_file = "{$currentFullPathName}/backup/W#SIDZKI.TXT";     // download file の保存先

// コネクションを取る(FTP接続のオープン)
$ftp_flg = false;   // 転送の成功・失敗フラグ
if ($ftp_stream = ftp_connect(AS400_HOST)) {
    if (ftp_login($ftp_stream, AS400_USER, AS400_PASS)) {
        ///// ターゲットファイル
        if (ftp_get($ftp_stream, $save_file, $target_file, FTP_ASCII)) {
            $log_date = date('Y-m-d H:i:s');
            fwrite($fpa,"$log_date 資材在庫サマリー ftp_get download OK " . $target_file . '→' . $save_file . "\n");
            $ftp_flg = true;
        } else {
            $log_date = date('Y-m-d H:i:s');
            fwrite($fpa,"$log_date 資材在庫サマリー ftp_get() error " . $target_file . "\n");
        }
    } else {
        $log_date = date('Y-m-d H:i:s');
        fwrite($fpa,"$log_date 資材在庫サマリー ftp_login() error \n");
    }
    ftp_close($ftp_stream);
} else {
    $log_date = date('Y-m-d H:i:s');
    fwrite($fpa,"$log_date 資材在庫サマリー ftp_connect() error \n");
}
if (!$ftp_flg) {
    fclose($fpa);      ////// 日報用ログ書込み終了
    exit();
}
*****************************************************************************************/



/////////// begin トランザクション開始
if ($con = db_connect()) {
    //query_affected_trans($con, 'BEGIN');    // 大量登録用にはコメントアウト
} else {
    $log_date = date('Y-m-d H:i:s');
    fwrite($fpa, "$log_date 資材在庫サマリー db_connect() error \n");
    exit();
}
///// 準備作業
$save_file = '/home/guest/monthly/W#SIDZKI.TXT';     // FTPの処理を行う場合はこの行を削除
$file_orign  = $save_file;
$file_backup = "{$currentFullPathName}/backup/W#SIDZKI-BAK.TXT";
$file_debug  = "{$currentFullPathName}/debug/debug-SIDZKI.TXT";
if (file_exists($file_orign)) {         // ファイルの存在チェック
    // 更新前に既存のデータを削除
    $del_sql = "
        DELETE FROM inventory_average_summary
    ";
    $delRec = query_affected_trans($con, $del_sql);
    $log_date = date('Y-m-d H:i:s');
    fwrite($fpa, "$log_date 資材在庫サマリー 前回のデータを $delRec 件 削除完了 \n");
    echo "$log_date 資材在庫サマリー 前回のデータを $delRec 件 削除完了 \n";
    
    $fp  = fopen($file_orign, 'r');
    $fpw = fopen($file_debug, 'w');      // debug 用ファイルのオープン
    $rec = 0;       // レコード��
    $rec_ok = 0;    // 書込み成功レコード数
    $rec_ng = 0;    // 書込み失敗レコード数
    $ins_ok = 0;    // INSERT用カウンター
    $upd_ok = 0;    // UPDATE用カウンター
    while (($data = fgetcsv($fp, 100, ',')) !== FALSE) {
        $rec++;
        
        if ($data[0] == '') {
            $log_date = date('Y-m-d H:i:s');
            fwrite($fpa, "$log_date AS/400 del record=$rec \n");
            continue;
        }
        $num  = count($data);       // フィールド数の取得
        for ($f=0; $f<$num; $f++) {
            $data[$f] = mb_convert_encoding($data[$f], 'EUC-JP', 'SJIS');       // SJISをEUC-JPへ変換
            $data[$f] = addslashes($data[$f]);          // "'"等がデータにある場合に\でエスケープする
            //if ($f == 1) {
            //    $data[$f] = mb_convert_kana($data[$f]); // 半角カナを全角カナに変換
            //}
        }
        
        $query_chk = "
            SELECT parts_no FROM inventory_average_summary
            WHERE parts_no='{$data[1]}'
        ";
        if (getUniResTrs($con, $query_chk, $res_chk) <= 0) {    // トランザクション内での 照会専用クエリー
            ///// 登録なし insert 使用
            $query = "
                INSERT INTO inventory_average_summary
                    (div, parts_no, invent_pcs, month_pickup_avr, hold_monthly_avr, once_pickup_avr, hold_pickup_avr)
                VALUES(
                    '{$data[0]}',
                    '{$data[1]}',
                     {$data[2]} ,
                     {$data[3]} ,
                     {$data[4]} ,
                     {$data[5]} ,
                     {$data[6]} )
            ";
            if (query_affected_trans($con, $query) <= 0) {      // 更新用クエリーの実行
                $log_date = date('Y-m-d H:i:s');
                fwrite($fpa, "$log_date 部品番号:{$data[1]} : {$rec}:レコード目の書込みに失敗しました!\n");
                // query_affected_trans($con, "rollback");     // transaction rollback
                $rec_ng++;
                ////////////////////////////////////////// Debug start
                for ($f=0; $f<$num; $f++) {
                    fwrite($fpw,"'{$data[$f]}',");      // debug
                }
                fwrite($fpw,"\n");                      // debug
                fwrite($fpw, "$query \n");              // debug
                break;                                  // debug
                ////////////////////////////////////////// Debug end
            } else {
                $rec_ok++;
                $ins_ok++;
            }
        } else {
            ///// 登録あり update 使用
            $query = "
                UPDATE inventory_average_summary
                SET
                    div                 ='{$data[0]}',
                    parts_no            ='{$data[1]}',
                    invent_pcs          = {$data[2]} ,
                    month_pickup_avr    = {$data[3]} ,
                    hold_monthly_avr    = {$data[4]} ,
                    once_pickup_avr     = {$data[5]} ,
                    hold_pickup_avr     = {$data[6]}
                WHERE parts_no='{$data[1]}'
            ";
            if (query_affected_trans($con, $query) <= 0) {      // 更新用クエリーの実行
                $log_date = date('Y-m-d H:i:s');
                fwrite($fpa, "$log_date 部品番号:{$data[1]} : {$rec}:レコード目のUPDATEに失敗しました!\n");
                // query_affected_trans($con, "rollback");     // transaction rollback
                $rec_ng++;
                ////////////////////////////////////////// Debug start
                for ($f=0; $f<$num; $f++) {
                    fwrite($fpw,"'{$data[$f]}',");      // debug
                }
                fwrite($fpw,"\n");                      // debug
                fwrite($fpw, "$query \n");              // debug
                break;                                  // debug
                ////////////////////////////////////////// Debug end
            } else {
                $rec_ok++;
                $upd_ok++;
            }
        }
    }
    fclose($fp);
    fclose($fpw);       // debug
    $log_date = date('Y-m-d H:i:s');
    fwrite($fpa, "$log_date 資材在庫サマリーの更新 : $rec_ok/$rec 件登録しました。\n");
    fwrite($fpa, "$log_date 資材在庫サマリーの更新 : {$ins_ok}/{$rec} 件 追加 \n");
    fwrite($fpa, "$log_date 資材在庫サマリーの更新 : {$upd_ok}/{$rec} 件 変更 \n");
    echo "$log_date 資材在庫サマリーの更新 : $rec_ok/$rec 件登録しました。\n";
    echo "$log_date 資材在庫サマリーの更新 : {$ins_ok}/{$rec} 件 追加 \n";
    echo "$log_date 資材在庫サマリーの更新 : {$upd_ok}/{$rec} 件 変更 \n";
    if ($rec_ng == 0) {     // 書込みエラーがなければ ファイルを削除
        if (file_exists($file_backup)) {
            unlink($file_backup);       // Backup ファイルの削除
        }
        if (!rename($file_orign, $file_backup)) {
            $log_date = date('Y-m-d H:i:s');
            fwrite($fpa,"$log_date DownLoad File $file_orign をBackupできません！\n");
            echo "$log_date DownLoad File $file_orign をBackupできません！\n";
        }
    }
} else {
    $log_date = date('Y-m-d H:i:s');
    fwrite($fpa,"$log_date ファイル$file_orign がありません!\n");
    echo "$log_date ファイル$file_orign がありません!\n";
}
/////////// commit トランザクション終了
//query_affected_trans($con, 'COMMIT');    // 大量登録用にはコメントアウト
// echo $query . "\n";  // debug
fclose($fpa);      ////// 日報用ログ書込み終了

?>
