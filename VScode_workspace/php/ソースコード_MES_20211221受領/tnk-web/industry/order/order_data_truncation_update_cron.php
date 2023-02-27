#!/usr/local/bin/php
<?php
//////////////////////////////////////////////////////////////////////////////
// 注文書の打切データ差分（当日） 自動FTP Download & Update                 //
// (打切トランザクション)     AS/400 ----> Web Server (PHP)   cli(cron)版   //
// Copyright (C) 2004 2007 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp      //
// Changed history                                                          //
// 2004/11/01 Created  order_data_truncation_update_cron.php                //
// 2007/07/30 AS/400とのFTP error 対応のため ftpCheckAndExecute()関数を追加 //
// 2007/08/29 更新準備 コントロールファイルをロックのロジックを追加         //
//////////////////////////////////////////////////////////////////////////////
ini_set('error_reporting',E_ALL);       // E_ALL='2047' debug 用
ini_set('implicit_flush', 'off');       // echo print で flush させない(遅くなるためcli版)
// ini_set('max_execution_time', 1200);    // 最大実行時間=20分
require_once ('/home/www/html/tnk-web/function.php');

$log_date = date('Y-m-d H:i:s');        ///// 日報用ログの日時
$fpa = fopen('/tmp/order_data_truncation.log', 'a');    ///// 日報用ログファイルへの書込みでオープン

// FTPのターゲットファイル
$target_file = 'UKWLIB/W#TIUCHI';       // 打切トランザクションファイル download file
// 保存先のディレクトリとファイル名
$save_file = '/home/www/html/tnk-web/industry/order/backup/W#TIUCHI.TXT';     // 注文書打切ファイル save file

// コネクションを取る(FTP接続のオープン)
if ($ftp_stream = ftp_connect(AS400_HOST)) {
    if (ftp_login($ftp_stream, AS400_USER, AS400_PASS)) {
        ///// 注文打切ファイル
        if (ftpCheckAndExecute($ftp_stream, $save_file, $target_file, FTP_ASCII)) {
            fwrite($fpa,"$log_date ftp_get download 成功 " . $target_file . '→' . $save_file . "\n");
        } else {
            fwrite($fpa,"$log_date ftp_get() error " . $target_file . "\n");
            exit;
        }
    } else {
        fwrite($fpa,"$log_date ftp_login() error \n");
        exit;
    }
    ftp_close($ftp_stream);
} else {
    fwrite($fpa,"$log_date ftp_connect() error --> $target_file\n");
    exit;
}

/////////// 更新準備 コントロールファイルをロック
do {
    if ($fp_ctl = fopen('/tmp/order_process_lock', 'w')) {
        flock($fp_ctl, LOCK_EX);
        fwrite($fp_ctl, "$log_date " . __FILE__ . "\n");
        break;
    } else {
        sleep(5);   // 書込みでオープン出来なければ５秒待機
        continue;
    }
} while (0);


/////////// begin トランザクション開始
if ($con = db_connect()) {
    query_affected_trans($con, 'begin');
} else {
    fwrite($fpa, "$log_date db_connect() error \n");
    exit;
}
// 注文書打切ファイル 差分更新処理 準備作業
$file_orign  = $save_file;
$file_debug  = '/home/www/html/tnk-web/industry/order/backup/debug-TIUCHI.TXT';
if (file_exists($file_orign)) {         // ファイルの存在チェック
    $fp  = fopen($file_orign, 'r');
    $fpw = fopen($file_debug, 'w');      // debug 用ファイルのオープン
    $rec = 0;       // レコード��
    $rec_ok = 0;    // 書込み成功レコード数
    $rec_ng = 0;    // 書込み失敗レコード数
    $upd_ok = 0;    // UPDATE用カウンター
    $notupd = 0;    // 非生産品用カウンター
    $upd_no = 0;    // 非更新カウンター
    while (!(feof($fp))) {
        $data = fgetcsv($fp, 100, '_');     // 実レコードは50バイトなのでちょっと余裕をデリミタは'_'に注意
        if (feof($fp)) {
            break;
        }
        $rec++;
        
        $num  = count($data);       // フィールド数の取得
        if ($num < 5) {
            if ($num == 0 || $num == 1) {   // php-4.3.5で0返し php-4.3.6で1を返す仕様になった。fgetcsvの仕様変更による
                fwrite($fpa, "$log_date AS/400 del record=$rec \n");
            } else {
                fwrite($fpa, "$log_date field not 5 record=$rec \n");
            }
           continue;
        }
        for ($f=0; $f<$num; $f++) {
            $data[$f] = mb_convert_encoding($data[$f], 'EUC-JP', 'SJIS');       // SJISをEUC-JPへ変換
            $data[$f] = addslashes($data[$f]);       // "'"等がデータにある場合に\でエスケープする
            // $data_KV[$f] = mb_convert_kana($data[$f]);           // 半角カナを全角カナに変換
        }
        $query_chk = sprintf("SELECT cut_date FROM order_data
                                WHERE order_seq=%d",
                                $data[0]);
        if (getUniResTrs($con, $query_chk, $cut_date) <= 0) {    // トランザクション内での 照会専用クエリー
            ///// 登録なし insert は使用せず エラーとする(差分更新のため)
            $notupd++;
            // fwrite($fpa, "$log_date 発行連番：{$data[0]} が見つからない 非生産品 \n");
        } else {
            ///// 登録あり update 使用
            if ($cut_date != 0) {
                $upd_no++;
                continue;    // 既に更新済みのため更新しない
            }
            $query = "UPDATE order_data SET
                            cut_genpin  = {$data[1]} ,
                            cut_siharai = {$data[2]} ,
                            cut_kubun   ='{$data[3]}',
                            cut_date    = {$data[4]}
                WHERE order_seq={$data[0]}
            ";
            if (query_affected_trans($con, $query) <= 0) {      // 更新用クエリーの実行
                fwrite($fpa, "$log_date 発行番号:{$data[0]} : {$rec}:レコード目のUPDATEに失敗しました!\n");
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
    fwrite($fpa, "$log_date 注文書打切の差分更新:{$data[2]} : $rec_ok/$rec 件登録しました。\n");
    fwrite($fpa, "$log_date 注文書打切の差分更新:{$data[2]} : {$upd_ok}/{$rec} 件 変更 \n");
    fwrite($fpa, "$log_date 注文書打切の差分更新:{$data[2]} : {$notupd}/{$rec} 件 非生産品 \n");
    fwrite($fpa, "$log_date 注文書打切の差分更新:{$data[2]} : {$upd_no}/{$rec} 件 非更新 \n");
} else {
    fwrite($fpa,"$log_date ファイル$file_orign がありません!\n");
}
/////////// commit トランザクション終了
query_affected_trans($con, 'commit');
fclose($fp_ctl);   ////// Exclusive用ファイルクローズ
fclose($fpa);      ////// 日報用ログ書込み終了

exit();

function ftpCheckAndExecute($stream, $local_file, $as400_file, $ftp)
{
    $errorLogFile = '/tmp/as400FTP_Error.log';
    $trySec = 10;
    
    $flg = @ftp_get($stream, $local_file, $as400_file, $ftp);
    if ($flg) return $flg;
    $log_date = date('Y-m-d H:i:s');
    $errorLog = fopen($errorLogFile, 'a');
    fwrite($errorLog, "$log_date ftp_get Error try1 File=>" . __FILE__ . "\n");
    fclose($errorLog);
    sleep($trySec);
    
    $flg = @ftp_get($stream, $local_file, $as400_file, $ftp);
    if ($flg) return $flg;
    $log_date = date('Y-m-d H:i:s');
    $errorLog = fopen($errorLogFile, 'a');
    fwrite($errorLog, "$log_date ftp_get Error try2 File=>" . __FILE__ . "\n");
    fclose($errorLog);
    sleep($trySec);
    
    $flg = @ftp_get($stream, $local_file, $as400_file, $ftp);
    if ($flg) return $flg;
    $log_date = date('Y-m-d H:i:s');
    $errorLog = fopen($errorLogFile, 'a');
    fwrite($errorLog, "$log_date ftp_get Error try3 File=>" . __FILE__ . "\n");
    fclose($errorLog);
    
    return $flg;
}
?>
