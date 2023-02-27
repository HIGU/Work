#!/usr/local/bin/php
<?php
//////////////////////////////////////////////////////////////////////////////
// カプラ特注の完成品検査成績書 印刷   (MISOCFL1,MIUSERL) 取込 処理用 CLI版 //
// AS/400 ----> Web Server (PHP)                                            //
// Copyright (C) 2007 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp           //
// Changed history                                                          //
// 2007/12/10 Created  inspectionPrintUpdate_cli.php                        //
//////////////////////////////////////////////////////////////////////////////
// ini_set('error_reporting', E_ALL);          // E_ALL='2047' debug 用
ini_set('implicit_flush', '0');             // echo print で flush させない(遅くなるため)
// ini_set('max_execution_time', 1200);        // 最大実行時間=20分 CLI版なので必要ない

$currentFullPathName = realpath(dirname(__FILE__));
require_once ("{$currentFullPathName}/../../function.php");

$log_date = date('Y-m-d H:i:s');        ///// 日報用ログの日時
$fpa = fopen('/tmp/nippo.log', 'a');    ///// 日報用ログファイルへの書込みでオープン

////////// ユーザー名に半角カタカナがあるためFTP転送はしない

/////////// begin トランザクション開始
if ($con = db_connect()) {
    //query_affected_trans($con, 'BEGIN');    // 大量登録用にはコメントアウト
} else {
    $log_date = date('Y-m-d H:i:s');
    fwrite($fpa, "$log_date カプラ特注の要領書番号・ユーザー db_connect() error \n");
    exit();
}
///// assy_develop_user 特注カプラ開発ファイルの更新
$file_orign  = '/home/guest/monthly/MISOCFL1.CSV';
$file_backup = "{$currentFullPathName}/backup/MISOCFL1-BAK.TXT";
$file_debug  = "{$currentFullPathName}/debug/debug-MISOCFL1.TXT";
if (file_exists($file_orign)) {         // ファイルの存在チェック
    $fp  = fopen($file_orign, 'r');
    $fpw = fopen($file_debug, 'w');      // debug 用ファイルのオープン
    $rec = 0;       // レコード№
    $rec_ok = 0;    // 書込み成功レコード数
    $rec_ng = 0;    // 書込み失敗レコード数
    $ins_ok = 0;    // INSERT用カウンター
    $upd_ok = 0;    // UPDATE用カウンター
    $noUpdate = 0;  // 未変更カウンター
    while (($data = fgetcsv($fp, 50, ',')) !== FALSE) {
        $rec++;
        
        if ($data[0] == '') {
            $log_date = date('Y-m-d H:i:s');
            fwrite($fpa, "$log_date AS/400 del record=$rec \n");
            continue;
        }
        $num  = count($data);       // フィールド数の取得
        for ($f=0; $f<$num; $f++) {
            $data[$f] = mb_convert_encoding($data[$f], 'UTF-8', 'SJIS');       // SJISをEUC-JPへ変換
            $data[$f] = addslashes($data[$f]);          // "'"等がデータにある場合に\でエスケープする
            //if ($f == 1) {
            //    $data[$f] = mb_convert_kana($data[$f]); // 半角カナを全角カナに変換
            //}
        }
        
        $query_chk = "
            SELECT assy_no FROM assy_develop_user
            WHERE assy_no='{$data[0]}' AND dev_no='{$data[1]}' AND appro_no='{$data[2]}' AND user_no='{$data[3]}'
        ";
        if (getUniResTrs($con, $query_chk, $res_chk) <= 0) {    // トランザクション内での 照会専用クエリー
            ///// 登録なし insert 使用
            $query = "
                INSERT INTO assy_develop_user (assy_no, dev_no, appro_no, user_no)
                VALUES(
                    '{$data[0]}',
                    '{$data[1]}',
                    '{$data[2]}',
                    '{$data[3]}')
            ";
            if (query_affected_trans($con, $query) <= 0) {      // 更新用クエリーの実行
                $log_date = date('Y-m-d H:i:s');
                fwrite($fpa, "$log_date 製品番号:{$data[0]} : {$rec}:レコード目の書込みに失敗しました!\n");
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
            $noUpdate++;
            continue;
            ///// 登録あり update 使用
            $query = "
                UPDATE assy_develop_user
                SET
                    assy_no             ='{$data[0]}',
                    dev_no              ='{$data[1]}',
                    appro_no            ='{$data[3]}',
                    user_no             ='{$data[3]}'
                WHERE assy_no='{$data[0]}' AND dev_no='{$data[1]}' AND appro_no='{$data[2]}' AND user_no='{$data[3]}'
            ";
            if (query_affected_trans($con, $query) <= 0) {      // 更新用クエリーの実行
                $log_date = date('Y-m-d H:i:s');
                fwrite($fpa, "$log_date 製品番号:{$data[0]} : {$rec}:レコード目のUPDATEに失敗しました!\n");
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
    fwrite($fpa, "$log_date 特注カプラ開発ファイルの更新 : $rec_ok/$rec 件登録しました。\n");
    fwrite($fpa, "$log_date 特注カプラ開発ファイルの更新 : {$ins_ok}/{$rec} 件 追加 \n");
    fwrite($fpa, "$log_date 特注カプラ開発ファイルの更新 : {$upd_ok}/{$rec} 件 変更 \n");
    fwrite($fpa, "$log_date 特注カプラ開発ファイルの更新 : {$noUpdate}/{$rec} 件 未変更 \n");
    echo "$log_date 特注カプラ開発ファイルの更新 : $rec_ok/$rec 件登録しました。\n";
    echo "$log_date 特注カプラ開発ファイルの更新 : {$ins_ok}/{$rec} 件 追加 \n";
    echo "$log_date 特注カプラ開発ファイルの更新 : {$upd_ok}/{$rec} 件 変更 \n";
    echo "$log_date 特注カプラ開発ファイルの更新 : {$noUpdate}/{$rec} 件 未変更 \n";
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
// fclose($fpa);      ////// 日報用ログ書込み終了



///// assy_develop_user_code 客先コードテーブル(ＮＫ用)の更新
$file_orign  = '/home/guest/monthly/MIUSERL.CSV';
$file_backup = "{$currentFullPathName}/backup/MIUSERL-BAK.TXT";
$file_debug  = "{$currentFullPathName}/debug/debug-MIUSERL.TXT";
if (file_exists($file_orign)) {         // ファイルの存在チェック
    $fp  = fopen($file_orign, 'r');
    $fpw = fopen($file_debug, 'w');      // debug 用ファイルのオープン
    $rec = 0;       // レコード№
    $rec_ok = 0;    // 書込み成功レコード数
    $rec_ng = 0;    // 書込み失敗レコード数
    $ins_ok = 0;    // INSERT用カウンター
    $upd_ok = 0;    // UPDATE用カウンター
    $noUpdate = 0;  // 未変更カウンター
    while (($data = fgetcsv($fp, 50, ',')) !== FALSE) {
        $rec++;
        
        if ($data[0] == '') {
            $log_date = date('Y-m-d H:i:s');
            fwrite($fpa, "$log_date AS/400 del record=$rec \n");
            continue;
        }
        $num  = count($data);       // フィールド数の取得
        for ($f=0; $f<$num; $f++) {
            $data[$f] = mb_convert_encoding($data[$f], 'UTF-8', 'SJIS');       // SJISをEUC-JPへ変換
            $data[$f] = addslashes($data[$f]);          // "'"等がデータにある場合に\でエスケープする
            //if ($f == 1) {
            //    $data[$f] = mb_convert_kana($data[$f]); // 半角カナを全角カナに変換
            //}
        }
        
        $query_chk = "
            SELECT user_no FROM assy_develop_user_code
            WHERE user_no='{$data[0]}'
        ";
        if (getUniResTrs($con, $query_chk, $res_chk) <= 0) {    // トランザクション内での 照会専用クエリー
            ///// 登録なし insert 使用
            $query = "
                INSERT INTO assy_develop_user_code (user_no, user_name)
                VALUES(
                    '{$data[0]}',
                    '{$data[1]}')
            ";
            if (query_affected_trans($con, $query) <= 0) {      // 更新用クエリーの実行
                $log_date = date('Y-m-d H:i:s');
                fwrite($fpa, "$log_date ユーザー番号:{$data[0]} : {$rec}:レコード目の書込みに失敗しました!\n");
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
            $query_chk = "
                SELECT user_no FROM assy_develop_user_code
                WHERE user_no='{$data[0]}' AND user_name='{$data[1]}'
            ";
            if (getUniResTrs($con, $query_chk, $res_chk) > 0) {    // トランザクション内での 照会専用クエリー
                // データの変更なし
                $noUpdate++;
                continue;
            }
            ///// 登録ありデータの変更あり update 使用
            $query = "
                UPDATE assy_develop_user_code
                SET
                    user_no             ='{$data[0]}',
                    user_name           ='{$data[1]}'
                WHERE user_no='{$data[0]}' AND user_name='{$data[1]}'
            ";
            if (query_affected_trans($con, $query) <= 0) {      // 更新用クエリーの実行
                $log_date = date('Y-m-d H:i:s');
                fwrite($fpa, "$log_date ユーザー番号:{$data[0]} : {$rec}:レコード目のUPDATEに失敗しました!\n");
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
    fwrite($fpa, "$log_date 客先コードテーブル(ＮＫ用)の更新 : $rec_ok/$rec 件登録しました。\n");
    fwrite($fpa, "$log_date 客先コードテーブル(ＮＫ用)の更新 : {$ins_ok}/{$rec} 件 追加 \n");
    fwrite($fpa, "$log_date 客先コードテーブル(ＮＫ用)の更新 : {$upd_ok}/{$rec} 件 変更 \n");
    fwrite($fpa, "$log_date 客先コードテーブル(ＮＫ用)の更新 : {$noUpdate}/{$rec} 件 未変更 \n");
    echo "$log_date 客先コードテーブル(ＮＫ用)の更新 : $rec_ok/$rec 件登録しました。\n";
    echo "$log_date 客先コードテーブル(ＮＫ用)の更新 : {$ins_ok}/{$rec} 件 追加 \n";
    echo "$log_date 客先コードテーブル(ＮＫ用)の更新 : {$upd_ok}/{$rec} 件 変更 \n";
    echo "$log_date 客先コードテーブル(ＮＫ用)の更新 : {$noUpdate}/{$rec} 件 未変更 \n";
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
