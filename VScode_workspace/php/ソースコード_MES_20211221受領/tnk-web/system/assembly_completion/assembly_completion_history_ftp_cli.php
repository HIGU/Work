#!/usr/local/bin/php
<?php
//////////////////////////////////////////////////////////////////////////////
// 組立 完成 経歴の更新 バッチ用 (HIKANS\2) 取込 処理用 CLI版               //
// AS/400 ----> Web Server (PHP)                                            //
// Copyright (C) 2006-2007 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp      //
// Changed history                                                          //
// 2006/03/09 Created  assembly_completion_history_ftp_cli.php              //
//            ユニークキーが無いため INSERT のみの処理 初回のみ日付チェック //
// 2007/05/15 自動更新対応版へ変更 (FTPを使用する)                          //
//////////////////////////////////////////////////////////////////////////////
ini_set('error_reporting', E_ALL);          // E_ALL='2047' debug 用
ini_set('implicit_flush', '0');             // echo print で flush させない(遅くなるため)
// ini_set('max_execution_time', 1200);        // 最大実行時間=20分 CLI版なので必要ない

$currentFullPathName = realpath(dirname(__FILE__));
require_once ("{$currentFullPathName}/../../function.php");

$log_date = date('Y-m-d H:i:s');        ///// 日報用ログの日時
$fpa = fopen('/tmp/nippo.log', 'a');    ///// 日報用ログファイルへの書込みでオープン

// FTPのターゲットファイル
$target_file = 'UKWLIB/W#HIKANS';           // AS/400ファイル download
// 保存先のディレクトリとファイル名
$save_file = "{$currentFullPathName}/backup/W#HIKANS.TXT";      // download file の保存先

// コネクションを取る(FTP接続のオープン)
$ftp_flg = false;   // 転送の成功・失敗フラグ
if ($ftp_stream = ftp_connect(AS400_HOST)) {
    if (ftp_login($ftp_stream, AS400_USER, AS400_PASS)) {
        ///// ターゲットファイル
        if (ftp_get($ftp_stream, $save_file, $target_file, FTP_ASCII)) {
            $log_date = date('Y-m-d H:i:s');
            fwrite($fpa,"$log_date 組立完成経歴 ftp_get download OK " . $target_file . '→' . $save_file . "\n");
            $ftp_flg = true;
        } else {
            $log_date = date('Y-m-d H:i:s');
            fwrite($fpa,"$log_date 組立完成経歴 ftp_get() error " . $target_file . "\n");
        }
    } else {
        $log_date = date('Y-m-d H:i:s');
        fwrite($fpa,"$log_date 組立完成経歴 ftp_login() error \n");
    }
    ftp_close($ftp_stream);
} else {
    $log_date = date('Y-m-d H:i:s');
    fwrite($fpa,"$log_date ftp_connect() error --> 組立完成経歴ファイル\n");
}
if (!$ftp_flg) {
    fclose($fpa);      ////// 日報用ログ書込み終了
    exit();
}


/////////// begin トランザクション開始
if ($con = db_connect()) {
    query_affected_trans($con, 'BEGIN');
} else {
    $log_date = date('Y-m-d H:i:s');
    fwrite($fpa, "$log_date db_connect() error \n");
    echo "$log_date 組立完成経歴 db_connect() error \n";
    exit();
}
///// 準備作業
$startCheck = 0;    // 初回のフラグ
$file_orign  = $save_file;
$file_backup = "{$currentFullPathName}/backup/W#HIKANS-BAK.TXT";
$file_debug  = "{$currentFullPathName}/debug/debug-W#HIKANS.TXT";
if (file_exists($file_orign)) {         // ファイルの存在チェック
    $fp  = fopen($file_orign, 'r');
    $fpw = fopen($file_debug, 'w');      // debug 用ファイルのオープン
    $rec = 0;       // レコード��
    $rec_ok = 0;    // 書込み成功レコード数
    $rec_ng = 0;    // 書込み失敗レコード数
    $ins_ok = 0;    // INSERT用カウンター
    $upd_ok = 0;    // UPDATE用カウンター
    while (($data = fgetcsv($fp, 100, '_')) !== FALSE) {
        ///// 実レコードは50〜70バイトなのでちょっと余裕をデリミタは('_'アンダバー)
        $rec++;
        
        if ($data[0] == '') {
            $log_date = date('Y-m-d H:i:s');
            fwrite($fpa, "$log_date AS/400 del record=$rec \n");
            echo "$log_date AS/400 del record=$rec \n";
            continue;
        }
        $num  = count($data);       // フィールド数の取得
        for ($f=0; $f<$num; $f++) {
            $data[$f] = mb_convert_encoding($data[$f], 'EUC-JP', 'SJIS');       // SJISをEUC-JPへ変換
            $data[$f] = addslashes($data[$f]);          // "'"等がデータにある場合に\でエスケープする
            //if ($f == 6) {          // 備考
            //    $data[$f] = mb_convert_kana($data[$f]); // 半角カナを全角カナに変換
            //}
        }
        if ($startCheck == 0) {
            $startCheck = 1;
            $query_chk = "
                SELECT plan_no FROM assembly_completion_history
                WHERE comp_date = {$data[3]}
            ";
            if (getUniResTrs($con, $query_chk, $res_chk) > 0) {     // トランザクション内での 照会専用クエリー
                /***
                fwrite($fpa, "$log_date {$data[3]}：の完成日は既に登録されています。 \n");
                break;
                ***/
                $del_sql = "DELETE FROM assembly_completion_history WHERE comp_date = {$data[3]}";
                if (($del_cnt=query_affected_trans($con, $del_sql)) <= 0) { // 更新用クエリーの実行
                    $log_date = date('Y-m-d H:i:s');
                    fwrite($fpa, "$log_date {$data[3]}：の重複完成日を削除できませんでした。 \n");
                    break;
                }
                $log_date = date('Y-m-d H:i:s');
                fwrite($fpa, "$log_date {$data[3]}：の重複完成日 {$del_cnt} 件を削除して実行します。 \n");
            }
        }
        $query = "
            INSERT INTO assembly_completion_history
                (plan_no, assy_no, line_group, comp_date, comp_pcs, comp_no, in_no)
            VALUES(
                '{$data[0]}',   -- 計画番号
                '{$data[1]}',   -- 製品番号
                '{$data[2]}',   -- 集計用ライン
                 {$data[3]} ,   -- 完成日
                 {$data[4]} ,   -- 完成数
                 {$data[5]} ,   -- 組立完了番号
                '{$data[6]}')   -- 入庫場所
        ";
        if (query_affected_trans($con, $query) <= 0) {      // 更新用クエリーの実行
            $log_date = date('Y-m-d H:i:s');
            fwrite($fpa, "$log_date 計画番号:{$data[0]} : {$rec}:レコード目の書込みに失敗しました!\n");
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
    }
    fclose($fp);
    fclose($fpw);       // debug
    $log_date = date('Y-m-d H:i:s');
    fwrite($fpa, "$log_date 組立完成経歴ファイルの更新 : $rec_ok/$rec 件登録しました。\n");
    fwrite($fpa, "$log_date 組立完成経歴ファイルの更新 : {$ins_ok}/{$rec} 件 追加 \n");
    fwrite($fpa, "$log_date 組立完成経歴ファイルの更新 : {$upd_ok}/{$rec} 件 変更 \n");
    if ($rec_ng == 0) {     // 書込みエラーがなければ ファイルを削除
        if (file_exists($file_backup)) {
            unlink($file_backup);       // Backup ファイルの削除
        }
        if (!rename($file_orign, $file_backup)) {
            $log_date = date('Y-m-d H:i:s');
            fwrite($fpa,"$log_date DownLoad File $file_orign をBackupできません！\n");
        }
    }
} else {
    $log_date = date('Y-m-d H:i:s');
    fwrite($fpa,"$log_date ファイル$file_orign がありません!\n");
}
/////////// commit トランザクション終了
query_affected_trans($con, 'COMMIT');
// echo $query . "\n";  // debug
fclose($fpa);      ////// 日報用ログ書込み終了
?>
