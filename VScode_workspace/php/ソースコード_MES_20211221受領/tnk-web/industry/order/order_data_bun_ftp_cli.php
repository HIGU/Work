#!/usr/local/bin/php
<?php
//////////////////////////////////////////////////////////////////////////////
// 受付・検収の分納伝票(当日注文書発行) 自動FTP Download cronで処理用 cli版 //
// AS/400 ----> Web Server (PHP)                                            //
// Copyright (C) 2004 2007 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp      //
// Changed history                                                          //
// 2004/11/15 Created  order_data_bun_ftp_cli.php                           //
// 2004/11/16 order_data_supple(補足事項テーブル)を追加し同期事に書込み     //
// 2007/07/30 AS/400とのFTP error 対応のため ftpCheckAndExecute()関数を追加 //
// 2007/08/29 更新準備 コントロールファイルをロックのロジックを追加         //
//////////////////////////////////////////////////////////////////////////////
ini_set('error_reporting', E_ALL);          // E_ALL='2047' debug 用
ini_set('implicit_flush', 'off');           // echo print で flush させない(遅くなるため)
// ini_set('max_execution_time', 1200);        // 最大実行時間=20分
require_once ('/home/www/html/tnk-web/function.php');

$log_date = date('Y-m-d H:i:s');        ///// 同期用ログの日時
$fpa = fopen('/tmp/order_data_bun.log', 'a');    ///// 同期用ログファイルへの書込みでオープン

// FTPのターゲットファイル
$target_file = 'UKWLIB/W#WIINST';       // 分納伝票ファイル download file
// 保存先のディレクトリとファイル名
$save_file = '/home/www/html/tnk-web/system/backup/W#WIINST.TXT';     // 分納伝票当日分ファイル save file

// コネクションを取る(FTP接続のオープン)
if ($ftp_stream = ftp_connect(AS400_HOST)) {
    if (ftp_login($ftp_stream, AS400_USER, AS400_PASS)) {
        if (ftpCheckAndExecute($ftp_stream, $save_file, $target_file, FTP_ASCII)) {
            fwrite($fpa,"$log_date ftp_get download OK " . $target_file . '→' . $save_file . "\n");
        } else {
            fwrite($fpa,"$log_date ftp_get() error " . $target_file . "\n");
        }
    } else {
        fwrite($fpa,"$log_date ftp_login() error \n");
    }
    ftp_close($ftp_stream);
} else {
    fwrite($fpa,"$log_date ftp_connect() error --> 分納伝票当日分\n");
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
    echo "$log_date db_connect() error \n";
    exit();
}
// 注文書当日分発行ファイル 同期処理 準備作業
$today = date('Ymd');   // 発行日のセット
$file_orign  = $save_file;
$file_debug  = '/home/www/html/tnk-web/system/debug/debug-WIINST.TXT';
if (file_exists($file_orign)) {         // ファイルの存在チェック
    $fp  = fopen($file_orign, 'r');
    $fpw = fopen($file_debug, 'w');      // debug 用ファイルのオープン
    $rec = 0;       // レコード��
    $rec_ok = 0;    // 書込み成功レコード数
    $rec_ng = 0;    // 書込み失敗レコード数
    $ins_ok = 0;    // INSERT用カウンター
    $upd_ok = 0;    // UPDATE用カウンター
    while (!(feof($fp))) {
        $data = fgetcsv($fp, 130, '_');     // 実レコードは115バイトなのでちょっと余裕をデリミタは'_'に注意
        if (feof($fp)) {
            break;
        }
        $rec++;
        
        $num  = count($data);       // フィールド数の取得
        if ($num < 11) {    // 実際には 21 あり(最後がない場合があるため)
            if ($num == 0 || $num == 1) {   // php-4.3.5で0返し php-4.3.6で1を返す仕様になった。fgetcsvの仕様変更による
                fwrite($fpa, "$log_date AS/400 del record=$rec \n");
            } else {
                fwrite($fpa, "$log_date field not 11 record=$rec \n");
            }
           continue;
        } elseif ($data[0] == '0000000') {
            // 理由は分納伝票が、まだ印刷指示されていない状態のため
           continue;
        }
        for ($f=0; $f<$num; $f++) {
            $data[$f] = mb_convert_encoding($data[$f], 'EUC-JP', 'SJIS');       // SJISをEUC-JPへ変換
            $data[$f] = addslashes($data[$f]);       // "'"等がデータにある場合に\でエスケープする
            // $data_KV[$f] = mb_convert_kana($data[$f]);           // 半角カナを全角カナに変換
        }
        ///////////// 発注単価をセットする。
        $sql = "select order_price, delivery, kouji_no from order_data where order_seq = {$data[1]}";
        $order_price = 0 ;     // 初期化
        $delivery    = 0 ;     // 初期化
        $kouji_no    = '';     // 初期化
        $res = array();
        if (getResultTrs($con, $sql, $res) <= 0) {
            fwrite($fpa, "$log_date order_data Not order_price order_seq={$data[0]}:{$data[1]} \n");
            continue;   // 同期出来ないため更新しない
        } else {
            $order_price = $res[0][0];
            $delivery    = $res[0][1];
            $kouji_no    = $res[0][2];
        }
        $query_chk = sprintf("SELECT order_seq FROM order_data
                                WHERE order_seq=%d",
                                $data[0]);
        if (getUniResTrs($con, $query_chk, $res_chk) <= 0) {    // トランザクション内での 照会専用クエリー
            ///// 登録なし insert 使用
            $query = "INSERT INTO order_data
                            (
                                order_seq   ,
                                pre_seq     ,
                                sei_no      ,
                                order_no    ,
                                parts_no    ,
                                vendor      ,
                                order_q     ,
                                order_price ,
                                delivery    ,
                                date_issue  ,
                                kouji_no    ,
                                uke_date    ,
                                ken_date    ,
                                cut_date    ,
                                cut_genpin  ,
                                cut_siharai
                            )
                      VALUES
                      (
                           {$data[0]} ,
                           {$data[1]} ,
                           {$data[2]} ,
                           {$data[3]} ,
                          '{$data[4]}',
                          '{$data[5]}',
                           {$data[6]} ,
                           {$order_price} ,
                           {$delivery},
                           {$today}   ,
                          '{$kouji_no}',
                           0,
                           0,
                           0,
                           0,
                           0
                      )
            ";
            if (query_affected_trans($con, $query) <= 0) {      // 更新用クエリーの実行
                fwrite($fpa, "$log_date 発行番号:{$data[0]} : {$rec}:レコード目の書込みに失敗しました!\n");
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
            ///// 登録あり update 使用せず とばす。
            $upd_ok++;
        }
        ////////// order_data の補足テーブルに書込み
        $query_chk = sprintf("SELECT order_seq FROM order_data_supple
                                WHERE order_seq=%d",
                                $data[0]);
        if (getUniResTrs($con, $query_chk, $res_chk) <= 0) {    // トランザクション内での 照会専用クエリー
            ///// 登録なし insert 使用
            $query = "INSERT INTO order_data_supple VALUES({$data[0]}, {$data[7]}, {$data[8]}, {$data[9]}, {$data[10]})";
            if (query_affected_trans($con, $query) <= 0) {      // 更新用クエリーの実行
                fwrite($fpa, "$log_date 発行番号:{$data[0]} : {$rec}:レコード目の書込みに失敗しました! order_data_supple\n");
            }
        }
        // 登録のある場合は何もしない
    }
    fclose($fp);
    fclose($fpw);       // debug
    fwrite($fpa, "$log_date order_data Chk_ok/recorde: {$rec_ok}/{$rec} 件対象\n");
    fwrite($fpa, "$log_date order_data INSERT/recorde: {$ins_ok}/{$rec} 件 追加 \n");
    fwrite($fpa, "$log_date order_data   NOT UPDATE  : {$upd_ok}/{$rec} 件 \n");
} else {
    fwrite($fpa,"$log_date ファイル$file_orign がありません!\n");
}
/////////// commit トランザクション終了
query_affected_trans($con, 'commit');
// echo $query . "\n";  // debug
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
