#!/usr/local/bin/php
<?php
//////////////////////////////////////////////////////////////////////////////
// 組立工程マスター (MGUKTEL) 取込 assembly_process_master.phpの自動実行    //
// AS/400 ----> Web Server (PHP)                                            //
// Copyright (C) 2010 Norihisa.Ohya norihisa_ooya@nitto-kohki.co.jp         //
// Changed history                                                          //
// 2010/01/14 Created  assembly_process_master_cli_once.php                 //
//////////////////////////////////////////////////////////////////////////////
ini_set('error_reporting', E_ALL);          // E_ALL='2047' debug 用
ini_set('implicit_flush', '0');             // echo print で flush させない(遅くなるため)
// ini_set('max_execution_time', 1200);        // 最大実行時間=20分 CLI版なので必要ない
require_once ('/var/www/html/function.php');

$log_date = date('Y-m-d H:i:s');        ///// 日報用ログの日時
$fpa = fopen('/tmp/nippo.log', 'a');    ///// 日報用ログファイルへの書込みでオープン
$fpb = fopen('/tmp/as400get_ftp_re.log', 'a');    ///// 日報データ再取得用ログファイルへの書込みでオープン
fwrite($fpb, "組立工程マスターの自動登録\n");
fwrite($fpb, "/var/www/html/system/assembly_time/assembly_process_master_cli_once.php\n");

/*****************************************************************************************
// FTPのターゲットファイル
$target_file = 'UKWLIB/W#MGUKTE';           // AS/400ファイル download
// 保存先のディレクトリとファイル名
$save_file = '/var/www/html/system/backup/W#MGUKTE.TXT';     // download file の保存先

// コネクションを取る(FTP接続のオープン)
if ($ftp_stream = ftp_connect(AS400_HOST)) {
    if (ftp_login($ftp_stream, AS400_USER, AS400_PASS)) {
        ///// ターゲットファイル
        if (ftp_get($ftp_stream, $save_file, $target_file, FTP_ASCII)) {
            // echo 'ftp_get download OK ', $target_file, '→', $save_file, "\n";
            fwrite($fpa,"$log_date ftp_get download OK " . $target_file . '→' . $save_file . "\n");
        } else {
            // echo 'ftp_get() error ', $target_file, "\n";
            fwrite($fpa,"$log_date ftp_get() error " . $target_file . "\n");
        }
    } else {
        // echo "ftp_login() error \n";
        fwrite($fpa,"$log_date ftp_login() error \n");
    }
    ftp_close($ftp_stream);
} else {
    // echo "ftp_connect() error --> 組立工程マスター\n";
    fwrite($fpa,"$log_date ftp_connect() error --> 組立工程マスター\n");
}
*****************************************************************************************/



/////////// begin トランザクション開始
if ($con = db_connect()) {
    query_affected_trans($con, 'begin');    // 大量登録用にはコメントアウト
} else {
    fwrite($fpa, "$log_date db_connect() error \n");
    fwrite($fpb, "$log_date db_connect() error \n");
    echo "$log_date db_connect() error \n";
    exit();
}
///// 準備作業
//$save_file = '/home/guest/monthly/MGUKTEL.CSV';     // FTPの処理を行う場合はこの行を削除
$save_file = '/home/guest/daily/MGUKTEL.CSV';     // FTPの処理を行う場合はこの行を削除
$file_orign  = $save_file;
$file_backup = '/var/www/html/system/backup/W#MGUKTE-BAK.TXT';
$file_debug  = '/var/www/html/system/debug/debug-MGUKTE.TXT';
if (file_exists($file_orign)) {         // ファイルの存在チェック
    $fp  = fopen($file_orign, 'r');
    $fpw = fopen($file_debug, 'w');      // debug 用ファイルのオープン
    $rec = 0;       // レコード№
    $rec_ok = 0;    // 書込み成功レコード数
    $rec_ng = 0;    // 書込み失敗レコード数
    $ins_ok = 0;    // INSERT用カウンター
    $upd_ok = 0;    // UPDATE用カウンター
    while (($data = fgetcsv($fp, 100, ',')) !== FALSE) {
        ///// 実レコードは50バイトなのでちょっと余裕をデリミタは','に注意('_'アンダバーではない)
        $rec++;
        
        if ($data[0] == '') {
            fwrite($fpa, "$log_date AS/400 del record=$rec \n");
            fwrite($fpb, "$log_date AS/400 del record=$rec \n");
            echo "$log_date AS/400 del record=$rec \n";
            continue;
        }
        $num  = count($data);       // フィールド数の取得
        for ($f=0; $f<$num; $f++) {
            $data[$f] = mb_convert_encoding($data[$f], 'UTF-8', 'SJIS');       // SJISをEUC-JPへ変換
            $data[$f] = addslashes($data[$f]);          // "'"等がデータにある場合に\でエスケープする
            if ($f == 1) {
                $data[$f] = mb_convert_kana($data[$f]); // 半角カナを全角カナに変換
            }
        }
        
        $query_chk = sprintf("SELECT pro_mark FROM assembly_process_master
                                WHERE pro_mark='%s'",
                                $data[0]
                    );
        if (getUniResTrs($con, $query_chk, $res_chk) <= 0) {    // トランザクション内での 照会専用クエリー
            ///// 登録なし insert 使用
            $query = "INSERT INTO assembly_process_master (pro_mark, pro_name, pro_seg, regdate)
                      VALUES(
                      '{$data[0]}',
                      '{$data[1]}',
                      '{$data[2]}',
                       {$data[3]} )";
            if (query_affected_trans($con, $query) <= 0) {      // 更新用クエリーの実行
                fwrite($fpa, "$log_date 工程記号:{$data[0]} : {$rec}:レコード目の書込みに失敗しました!\n");
                fwrite($fpb, "$log_date 工程記号:{$data[0]} : {$rec}:レコード目の書込みに失敗しました!\n");
                echo "$log_date 工程記号:{$data[0]} : {$rec}:レコード目の書込みに失敗しました!\n";
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
                UPDATE assembly_process_master
                SET
                    pro_mark    ='{$data[0]}',
                    pro_name    ='{$data[1]}',
                    pro_seg     ='{$data[2]}',
                    regdate     = {$data[3]}
                WHERE pro_mark='{$data[0]}'
            ";
            if (query_affected_trans($con, $query) <= 0) {      // 更新用クエリーの実行
                fwrite($fpa, "$log_date 工程記号:{$data[0]} : {$rec}:レコード目のUPDATEに失敗しました!\n");
                fwrite($fpb, "$log_date 工程記号:{$data[0]} : {$rec}:レコード目のUPDATEに失敗しました!\n");
                echo "$log_date 工程記号:{$data[0]} : {$rec}:レコード目のUPDATEに失敗しました!\n";
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
    fwrite($fpa, "$log_date 組立工程マスターの更新:{$data[1]} : $rec_ok/$rec 件登録しました。\n");
    fwrite($fpa, "$log_date 組立工程マスターの更新:{$data[1]} : {$ins_ok}/{$rec} 件 追加 \n");
    fwrite($fpa, "$log_date 組立工程マスターの更新:{$data[1]} : {$upd_ok}/{$rec} 件 変更 \n");
    fwrite($fpb, "$log_date 組立工程マスターの更新:{$data[1]} : $rec_ok/$rec 件登録しました。\n");
    fwrite($fpb, "$log_date 組立工程マスターの更新:{$data[1]} : {$ins_ok}/{$rec} 件 追加 \n");
    fwrite($fpb, "$log_date 組立工程マスターの更新:{$data[1]} : {$upd_ok}/{$rec} 件 変更 \n");
    echo "$log_date 組立工程マスターの更新:{$data[1]} : $rec_ok/$rec 件登録しました。\n";
    echo "$log_date 組立工程マスターの更新:{$data[1]} : {$ins_ok}/{$rec} 件 追加 \n";
    echo "$log_date 組立工程マスターの更新:{$data[1]} : {$upd_ok}/{$rec} 件 変更 \n";
    if ($rec_ng == 0) {     // 書込みエラーがなければ ファイルを削除
        if (file_exists($file_backup)) {
            unlink($file_backup);       // Backup ファイルの削除
        }
        if (!rename($file_orign, $file_backup)) {
            // echo "$log_date DownLoad File $file_orign をBackupできません！\n";
            fwrite($fpa,"$log_date DownLoad File $file_orign をBackupできません！\n");
            fwrite($fpb,"$log_date DownLoad File $file_orign をBackupできません！\n");
        }
    }
} else {
    fwrite($fpa,"$log_date ファイル$file_orign がありません!\n");
    fwrite($fpb,"$log_date ファイル$file_orign がありません!\n");
    echo "{$log_date}: file:{$file_orign} がありません!\n";
}
/////////// commit トランザクション終了
query_affected_trans($con, 'commit');    // 大量登録用にはコメントアウト
// echo $query . "\n";  // debug
fclose($fpa);      ////// 日報用ログ書込み終了
fwrite($fpb, "------------------------------------------------------------------------\n");
fclose($fpb);      ////// 日報データ再取得用ログ書込み終了
?>
