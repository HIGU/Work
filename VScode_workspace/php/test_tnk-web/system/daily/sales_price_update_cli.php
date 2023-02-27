#!/usr/local/bin/php
<?php
//////////////////////////////////////////////////////////////////////////////
// 日東工器の仕切単価更新 バッチ用 (MGUSI@P) 取込 処理用 CLI版              //
// AS/400 ----> Web Server (PHP)                                            //
// Copyright (C) 2006-2011 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp      //
// Changed history                                                          //
// 2006/03/08 Created  sales_price_update_cli.php                           //
// 2011/11/17 仕切単価を更新する際に、historyにも追加するように変更    大谷 //
//            自動更新に変更する為、ファイルの場所を変更                    //
//////////////////////////////////////////////////////////////////////////////
ini_set('error_reporting', E_ALL);          // E_ALL='2047' debug 用
ini_set('implicit_flush', '0');             // echo print で flush させない(遅くなるため)
// ini_set('max_execution_time', 1200);        // 最大実行時間=20分 CLI版なので必要ない
require_once ('/var/www/html/function.php');

$log_date = date('Y-m-d H:i:s');        ///// 日報用ログの日時
$fpa = fopen('/tmp/nippo.log', 'a');    ///// 日報用ログファイルへの書込みでオープン
$fpb = fopen('/tmp/as400get_ftp_re.log', 'a');    ///// 日報データ再取得用ログファイルへの書込みでオープン
fwrite($fpb, "仕切単価の更新\n");
fwrite($fpb, "/var/www/html/system/daily/sales_price_update_cli.php \n");

/*****************************************************************************************
// FTPのターゲットファイル
$target_file = 'UKWLIB/MGUSI@P';           // AS/400ファイル download
// 保存先のディレクトリとファイル名
$save_file = '/var/www/html/system/backup/MGUSI@P.TXT';     // download file の保存先

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
    // echo "ftp_connect() error --> 仕切単価ファイル\n";
    fwrite($fpa,"$log_date ftp_connect() error --> 仕切単価ファイル\n");
}
*****************************************************************************************/



/////////// begin トランザクション開始
if ($con = db_connect()) {
    //query_affected_trans($con, 'begin');    // 大量登録用にはコメントアウト
} else {
    fwrite($fpa, "$log_date db_connect() error \n");
    echo "$log_date db_connect() error \n";
    exit();
}
///// 準備作業
$save_file = '/home/guest/daily/MGUSI@P.CSV';     // FTPの処理を行う場合はこの行を削除
$file_orign  = $save_file;
$file_backup = '/home/guest/daily/backup/MGUSI@P-BAK.TXT';
$file_debug  = '/home/guest/daily/debug/debug-MGUSI@P.TXT';
if (file_exists($file_orign)) {         // ファイルの存在チェック
    $fp  = fopen($file_orign, 'r');
    $fpw = fopen($file_debug, 'w');      // debug 用ファイルのオープン
    $rec = 0;         // レコード№
    $rec_ok = 0;      // 書込み成功レコード数
    $h_rec_ok = 0;    // 履歴書込み成功レコード数
    $rec_ng = 0;      // 書込み失敗レコード数
    $h_rec_ng = 0;    // 履歴書込み失敗レコード数
    $ins_ok = 0;      // INSERT用カウンター
    $upd_ok = 0;      // UPDATE用カウンター
    while (($data = fgetcsv($fp, 100, ',')) !== FALSE) {
        ///// 実レコードは50～70バイトなのでちょっと余裕をデリミタは','に注意('_'アンダバーではない)
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
            if ($f == 6) {          // 備考
                $data[$f] = mb_convert_kana($data[$f]); // 半角カナを全角カナに変換
            }
        }
        
        $query_chk = "
            SELECT parts_no FROM sales_price_nk
            WHERE parts_no='{$data[0]}'
        ";
        if (getUniResTrs($con, $query_chk, $res_chk) <= 0) {    // トランザクション内での 照会専用クエリー
            ///// 登録なし insert 使用
            $query = "
                INSERT INTO sales_price_nk
                    (parts_no, price, nk_kubun, regdate, div, lot, note, reg_kubun)
                VALUES(
                    '{$data[0]}',
                     {$data[1]} ,
                    '{$data[2]}',
                     {$data[3]} ,
                    '{$data[4]}',
                     {$data[5]} ,
                    '{$data[6]}',
                    '{$data[7]}')
            ";
            if (query_affected_trans($con, $query) <= 0) {      // 更新用クエリーの実行
                fwrite($fpa, "$log_date 製品番号:{$data[0]} : {$rec}:レコード目の書込みに失敗しました!\n");
                fwrite($fpb, "$log_date 製品番号:{$data[0]} : {$rec}:レコード目の書込みに失敗しました!\n");
                echo "$log_date 製品番号:{$data[0]} : {$rec}:レコード目の書込みに失敗しました!\n";
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
                UPDATE sales_price_nk
                SET
                    parts_no    ='{$data[0]}',
                    price       = {$data[1]} ,
                    nk_kubun    ='{$data[2]}',
                    regdate     = {$data[3]} ,
                    div         ='{$data[4]}',
                    lot         ='{$data[5]}',
                    note        ='{$data[6]}',
                    reg_kubun   ='{$data[7]}'
                WHERE parts_no='{$data[0]}'
            ";
            if (query_affected_trans($con, $query) <= 0) {      // 更新用クエリーの実行
                fwrite($fpa, "$log_date 製品番号:{$data[0]} : {$rec}:レコード目のUPDATEに失敗しました!\n");
                fwrite($fpb, "$log_date 製品番号:{$data[0]} : {$rec}:レコード目のUPDATEに失敗しました!\n");
                echo "$log_date 製品番号:{$data[0]} : {$rec}:レコード目のUPDATEに失敗しました!\n";
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
        $query_chk = "
            SELECT parts_no FROM sales_price_nk_history
            WHERE parts_no='{$data[0]}' and price='{$data[1]}' and nk_kubun='{$data[2]}' and regdate='{$data[3]}' and div='{$data[4]}' and lot='{$data[5]}' and note='{$data[6]}' and reg_kubun='{$data[7]}'
        ";
        if (getUniResTrs($con, $query_chk, $res_chk) <= 0) {    // トランザクション内での 照会専用クエリー
            ///// 登録なし insert 使用
            $query = "
                INSERT INTO sales_price_nk_history
                    (parts_no, price, nk_kubun, regdate, div, lot, note, reg_kubun)
                VALUES(
                    '{$data[0]}',
                     {$data[1]} ,
                    '{$data[2]}',
                     {$data[3]} ,
                    '{$data[4]}',
                     {$data[5]} ,
                    '{$data[6]}',
                    '{$data[7]}')
            ";
            if (query_affected_trans($con, $query) <= 0) {      // 更新用クエリーの実行
                fwrite($fpa, "$log_date 製品番号:{$data[0]} : {$rec}:レコード目の書込みに失敗しました!\n");
                fwrite($fpb, "$log_date 製品番号:{$data[0]} : {$rec}:レコード目の書込みに失敗しました!\n");
                echo "$log_date 製品番号:{$data[0]} : {$rec}:レコード目の書込みに失敗しました!\n";
                // query_affected_trans($con, "rollback");     // transaction rollback
                $h_rec_ng++;
                ////////////////////////////////////////// Debug start
                for ($f=0; $f<$num; $f++) {
                    fwrite($fpw,"'{$data[$f]}',");      // debug
                }
                fwrite($fpw,"\n");                      // debug
                fwrite($fpw, "$query \n");              // debug
                break;                                  // debug
                ////////////////////////////////////////// Debug end
            } else {
                $h_rec_ok++;
            }
        }
        
    }
    fclose($fp);
    fclose($fpw);       // debug
    fwrite($fpa, "$log_date 仕切単価ファイルの更新:{$data[1]} : $rec_ok/$rec 件登録しました。\n");
    fwrite($fpa, "$log_date 仕切単価ファイルの更新:{$data[1]} : {$ins_ok}/{$rec} 件 追加 \n");
    fwrite($fpa, "$log_date 仕切単価ファイルの更新:{$data[1]} : {$upd_ok}/{$rec} 件 変更 \n");
    fwrite($fpa, "$log_date 仕切単価履歴ファイルの更新:{$data[1]} : $h_rec_ok/$rec 件登録しました。\n");
    fwrite($fpb, "$log_date 仕切単価ファイルの更新:{$data[1]} : $rec_ok/$rec 件登録しました。\n");
    fwrite($fpb, "$log_date 仕切単価ファイルの更新:{$data[1]} : {$ins_ok}/{$rec} 件 追加 \n");
    fwrite($fpb, "$log_date 仕切単価ファイルの更新:{$data[1]} : {$upd_ok}/{$rec} 件 変更 \n");
    fwrite($fpb, "$log_date 仕切単価履歴ファイルの更新:{$data[1]} : $h_rec_ok/$rec 件登録しました。\n");
    echo "$log_date 仕切単価ファイルの更新:{$data[1]} : $rec_ok/$rec 件登録しました。\n";
    echo "$log_date 仕切単価ファイルの更新:{$data[1]} : {$ins_ok}/{$rec} 件 追加 \n";
    echo "$log_date 仕切単価ファイルの更新:{$data[1]} : {$upd_ok}/{$rec} 件 変更 \n";
    echo "$log_date 仕切単価履歴ファイルの更新:{$data[1]} : $h_rec_ok/$rec 件登録しました。\n";
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
//query_affected_trans($con, 'commit');    // 大量登録用にはコメントアウト
// echo $query . "\n";  // debug
fclose($fpa);      ////// 日報用ログ書込み終了
fwrite($fpb, "------------------------------------------------------------------------\n");
fclose($fpb);      ////// 日報データ再取得用ログ書込み終了
?>
