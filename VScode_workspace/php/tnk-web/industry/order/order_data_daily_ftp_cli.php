#!/usr/local/bin/php
<?php
//////////////////////////////////////////////////////////////////////////////
// 日報データ(注文書発行データ) 自動FTP Download cronで処理用 cli版         //
// AS/400 ----> Web Server (PHP)                                            //
// Copyright (C) 2004-2005 2007 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp //
// Changed history                                                          //
// 2004/11/11 Created  order_data_daily_ftp_cli.php                         //
// 2004/11/18 注文番号の 1 00000 ? があればUPDATEする →                    //
//                 vendorも含めた検索条件を外し、vendorもUPDATE対象にした   //
// 2004/12/10 order_processに追加が必要な時 locate='30   ' を追加           //
// 2005/05/31 php-5.0.2-cli → php (変更時は5.0.4)常に最新版へ変更          //
//            order_data_difference_update_cron.phpとのdeadlockを避けるsleep//
// 2005/07/25 更新準備 コントロールファイルをロックのロジックを追加         //
// 2007/07/30 AS/400とのFTP error 対応のため ftpCheckAndExecute()関数を追加 //
//////////////////////////////////////////////////////////////////////////////
ini_set('error_reporting', E_ALL);          // E_ALL='2047' debug 用
ini_set('implicit_flush', 'off');           // echo print で flush させない(遅くなるため)
// ini_set('max_execution_time', 1200);        // 最大実行時間=20分(CLI版は対象外)
require_once ('/var/www/html/function.php');

sleep(5);  // order_data_difference_update_cron.php の更新で deadlockを起すため待機させる

$log_date = date('Y-m-d H:i:s');        ///// 同期用ログの日時
$fpa = fopen('/tmp/order_data_daily.log', 'a');    ///// 同期用ログファイルへの書込みでオープン

// FTPのターゲットファイル
$target_file = 'UKWLIB/W#TIORDR';       // 注文書発行ファイル download file
// 保存先のディレクトリとファイル名
$save_file = '/var/www/html/system/backup/W#TIORDR.TXT';     // 注文書当日分ファイル save file

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
    fwrite($fpa,"$log_date ftp_connect() error --> 注文書当日分\n");
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
$file_debug  = '/var/www/html/system/debug/debug-TIORDR.TXT';
if (file_exists($file_orign)) {         // ファイルの存在チェック
    $fp  = fopen($file_orign, 'r');
    $fpw = fopen($file_debug, 'w');      // debug 用ファイルのオープン
    $rec = 0;       // レコード№
    $rec_ok = 0;    // 書込み成功レコード数
    $rec_ng = 0;    // 書込み失敗レコード数
    $ins_ok = 0;    // INSERT用カウンター
    $upd_ok = 0;    // UPDATE用カウンター
    while (!(feof($fp))) {
        $data = fgetcsv($fp, 100, '_');     // 実レコードは82バイトなのでちょっと余裕をデリミタは'_'に注意
        if (feof($fp)) {
            break;
        }
        $rec++;
        
        $num  = count($data);       // フィールド数の取得
        if ($num < 9) {    // 実際には 21 あり(最後がない場合があるため)
            if ($num == 0 || $num == 1) {   // php-4.3.5で0返し php-4.3.6で1を返す仕様になった。fgetcsvの仕様変更による
                fwrite($fpa, "$log_date AS/400 del record=$rec \n");
            } else {
                fwrite($fpa, "$log_date field not 9 record=$rec \n");
            }
           continue;
        } elseif ($data[0] == '0000000') {
            // 日東工器の有償支給品等のため
           continue;
        }
        for ($f=0; $f<$num; $f++) {
            $data[$f] = mb_convert_encoding($data[$f], 'UTF-8', 'SJIS');       // SJISをEUC-JPへ変換
            $data[$f] = addslashes($data[$f]);       // "'"等がデータにある場合に\でエスケープする
            // $data_KV[$f] = mb_convert_kana($data[$f]);           // 半角カナを全角カナに変換
        }
        ///////////// 工事番号をセットする。
        $sql = "select kouji_no from order_plan where sei_no = {$data[2]}";
        $kouji_no = '';     // 初期化
        if (getUniResTrs($con, $sql, $kouji_no) <= 0) {
            fwrite($fpa, "$log_date order_plan Not kouji_no sei_no={$data[2]} \n");
        }
        ///// 正規の注文番号で 例：1492151 があればUPDATEする
        $sql = "select order_no from order_process where sei_no={$data[2]} and order_no={$data[3]} and vendor='{$data[5]}'";
        if (getUniResTrs($con, $sql, $res_chk) > 0) {
            ///// UPDATE 実行
            $query = "UPDATE order_process SET
                            order_date  = {$today},
                            delivery    = {$data[8]},
                            plan_cond   = 'O',
                            order_no    = {$data[3]}
                        where
                            sei_no={$data[2]} and order_no={$data[3]} and vendor='{$data[5]}'
            ";
            if (query_affected_trans($con, $query) <= 0) {      // 更新用クエリーの実行
                fwrite($fpa, "$log_date order_process UPDATE NG:\n{$query}\n");
            }
        } else {
            //////////// order_process の注文番号の 100000? があればUPDATEする
            $serch_order = ('100000' . substr($data[3], -1) );  // 工程番号を追加
            $sql = "select order_no from order_process where sei_no={$data[2]} and order_no={$serch_order}";
            if (getUniResTrs($con, $sql, $res_chk) > 0) {
                ///// UPDATE 実行
                $query = "UPDATE order_process SET
                                order_date  =  {$today}     ,
                                delivery    =  {$data[8]}   ,
                                plan_cond   = 'O'           ,
                                order_no    =  {$data[3]}   ,
                                vendor      = '{$data[5]}'
                            where
                                sei_no={$data[2]} and order_no={$serch_order}
                ";
                if (query_affected_trans($con, $query) <= 0) {      // 更新用クエリーの実行
                    fwrite($fpa, "$log_date order_process UPDATE NG:\n{$query}\n");
                }
            } else {
                ///// INSERT 実行   (手動による発注手順の保守等を行った場合に対応)
                $query = "INSERT INTO order_process
                                (
                                    order_no,
                                    vendor  ,
                                    sei_no  ,
                                    parts_no,
                                    order_q ,
                                    order_price,
                                    delivery,
                                    plan_cond,
                                    locate,
                                    order_date
                                )
                            values
                                (
                                     {$data[3]} ,
                                    '{$data[5]}',
                                     {$data[2]} ,
                                    '{$data[4]}',
                                     {$data[6]} ,
                                     {$data[7]} ,
                                     {$data[8]} ,
                                    'O',
                                    '30   ',
                                    {$today}
                                )
                ";
                if (query_affected_trans($con, $query) <= 0) {      // 更新用クエリーの実行
                    fwrite($fpa, "$log_date order_process INSERT NG:\n{$query}\n");
                }
            }
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
                           {$data[7]} ,
                           {$data[8]} ,
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
