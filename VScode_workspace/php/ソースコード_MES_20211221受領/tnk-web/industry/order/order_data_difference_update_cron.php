#!/usr/local/bin/php
<?php
//////////////////////////////////////////////////////////////////////////////
// 注文書発行データ差分（当日） 自動FTP Download & Update                   //
// (受付・検収分)     AS/400 ----> Web Server (PHP)   cli(cron)版           //
// Copyright (C) 2004-2005 2007 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp //
// Changed history                                                          //
// 2004/10/13 Created  order_data_difference_update_cron.php                //
// 2004/10/21 order_dataのken_dateをスケジュールで10分おきに更新しているため//
//         更新元のken_dateが'0'で更新先が既に日付が入っていた場合更新しない//
// 2005/05/25 php-5.0.2-cli → php (最新版) 変更時はphp 5.0.4               //
//            order_data を更新した場合に order_process も同期を取るため更新//
// 2005/05/30 order_process の更新対象を製造番号と注文番号があるものに限定  //
// 2005/07/25 更新準備 コントロールファイルをロックのロジックを追加         //
// 2007/02/14 checkTableChange()を作成し既に更新している場合は更新しない    //
// 2007/07/30 AS/400とのFTP error 対応のため ftpCheckAndExecute()関数を追加 //
//////////////////////////////////////////////////////////////////////////////
ini_set('error_reporting', E_ALL);          // E_ALL='2047' debug 用
ini_set('implicit_flush', 'off');           // echo print で flush させない(遅くなるためcli版)
// ini_set('max_execution_time', 1200);        // 最大実行時間=20分
require_once ('/home/www/html/tnk-web/function.php');

$log_date = date('Y-m-d H:i:s');            // 日報用ログの日時
$fpa = fopen('/tmp/order_data_difference.log', 'a');    ///// 日報用ログファイルへの書込みでオープン

// FTPのターゲットファイル
$target_file = 'UKWLIB/W#HIMKUK';       // 注文書発行ファイル download file
// 保存先のディレクトリとファイル名
$save_file = '/home/www/html/tnk-web/industry/order/backup/W#HIMKUK.TXT';     // 注文書発行ファイル save file

// コネクションを取る(FTP接続のオープン)
if ($ftp_stream = ftp_connect(AS400_HOST)) {
    if (ftp_login($ftp_stream, AS400_USER, AS400_PASS)) {
        ///// 注文書発行ファイル
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
$log_date = date('Y-m-d H:i:s');            // 日報用ログの日時
if ($con = db_connect()) {
    query_affected_trans($con, 'begin');
} else {
    fwrite($fpa, "$log_date db_connect() error \n");
    exit;
}
// 注文書発行ファイル 差分更新処理 準備作業
$file_orign  = $save_file;
$file_debug  = '/home/www/html/tnk-web/industry/order/backup/debug-HIMKUK.TXT';
if (file_exists($file_orign)) {         // ファイルの存在チェック
    $fp  = fopen($file_orign, 'r');
    $fpw = fopen($file_debug, 'w');      // debug 用ファイルのオープン
    $rec = 0;       // レコード��
    $rec_ok = 0;    // 書込み成功レコード数
    $rec_ng = 0;    // 書込み失敗レコード数
    $upd_ok = 0;    // UPDATE用カウンター
    $upd2_ok= 0;    // UPDATE用カウンター2
    $notupd = 0;    // 非生産品用カウンター
    $upd_no = 0;    // 非更新カウンター
    $log_date = date('Y-m-d H:i:s');            // 日報用ログの日時
    while (!(feof($fp))) {
        $data = fgetcsv($fp, 100, '_');     // 実レコードは75バイトなのでちょっと余裕をデリミタは'_'に注意
        if (feof($fp)) {
            break;
        }
        $rec++;
        
        $num  = count($data);       // フィールド数の取得
        if ($num < 7) {
            if ($num == 0 || $num == 1) {   // php-4.3.5で0返し php-4.3.6で1を返す仕様になった。fgetcsvの仕様変更による
                fwrite($fpa, "$log_date AS/400 del record=$rec \n");
            } else {
                fwrite($fpa, "$log_date field not 20 record=$rec \n");
            }
           continue;
        }
        for ($f=0; $f<$num; $f++) {
            $data[$f] = mb_convert_encoding($data[$f], 'EUC-JP', 'SJIS');       // SJISをEUC-JPへ変換
            $data[$f] = addslashes($data[$f]);       // "'"等がデータにある場合に\でエスケープする
            // $data_KV[$f] = mb_convert_kana($data[$f]);           // 半角カナを全角カナに変換
        }
        $query_chk = sprintf("SELECT order_seq, uke_no, uke_date, uke_q, ken_date, genpin, siharai FROM order_data
                                WHERE order_seq=%d",
                                $data[0]);
        if (getResultTrs($con, $query_chk, $res_chk) <= 0) {    // トランザクション内での 照会専用クエリー
            ///// 登録なし insert は使用せず エラーとする(差分更新のため)
            $notupd++;
            // fwrite($fpa, "$log_date 発行連番：{$data[0]} が見つからない 非生産品 \n");
        } else {
            ///// 登録あり update 使用
            if ( ($res_chk[0][4] != 0) && ($data[4] == 0) ) {  // $res_chk[0][4]=ken_date
                $upd_no++;
                continue;   // 検査員が検済にした場合
            }
            if (checkTableChange($data, $res_chk[0])) {
                $upd_no++;
                continue;   // 既に更新済みのため更新しない
            }
            $query = "UPDATE order_data SET
                            uke_no      ='{$data[1]}',
                            uke_date    = {$data[2]} ,
                            uke_q       = {$data[3]} ,
                            ken_date    = {$data[4]} ,
                            genpin      = {$data[5]} ,
                            siharai     = {$data[6]}
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
                $query = "SELECT sei_no, order_no, vendor FROM order_data WHERE order_seq={$data[0]}";
                if (getResultTrs($con, $query, $res) <= 0) {    // トランザクション内での 照会専用クエリー
                    fwrite($fpa, "$log_date 発行番号:{$data[0]} : の製造番号・注文番号・ベンダーの取得に失敗しました!\n");
                } else {
                    if ( ($res[0][0] != 0) && ($res[0][1] != 0) ) { // 製造番号と注文番号があるものが対象
                        $query = "SELECT sum(genpin), sum(siharai), sum(cut_genpin), sum(cut_siharai) FROM order_data WHERE sei_no={$res[0][0]} and order_no={$res[0][1]} and vendor='{$res[0][2]}'";
                        if (getResultTrs($con, $query, $res2) <= 0) {    // トランザクション内での 照会専用クエリー
                            fwrite($fpa, "$log_date 製造番号:{$res[0][0]} : 注文番号:{$res[0][1]} : ベンダー:{$res[0][2]} の合計現品数・支払数の取得に失敗しました!\n");
                        } else {
                            $query = "UPDATE order_process SET genpin={$res2[0][0]}, siharai={$res2[0][1]}, cut_genpin={$res2[0][2]}, cut_siharai={$res2[0][3]} WHERE sei_no={$res[0][0]} and order_no={$res[0][1]} and vendor='{$res[0][2]}'";
                            if (query_affected_trans($con, $query) <= 0) {      // 更新用クエリーの実行
                                fwrite($fpa, "$log_date UPDATEに失敗しました!\n  SQL文={$query}\n");
                            } else {
                                $upd2_ok++;
                            }
                        }
                    }
                }
            }
        }
    }
    fclose($fp);
    fclose($fpw);       // debug
    fwrite($fpa, "$log_date 注文書発行Fの差分更新:{$data[2]} : $rec_ok/$rec 件登録しました。\n");
    fwrite($fpa, "$log_date 注文書発行Fの差分更新:{$data[2]} : {$upd_ok}/{$rec} 件 変更 \n");
    fwrite($fpa, "$log_date 注文書発行Fの差分更新:{$data[2]} : {$notupd}/{$rec} 件 非生産品 \n");
    fwrite($fpa, "$log_date 注文書発行Fの差分更新:{$data[2]} : {$upd_no}/{$rec} 件 非更新 \n");
    fwrite($fpa, "$log_date 発注工程明細の差分更新:{$data[2]}: {$upd2_ok}/{$rec} 件 変更 \n");
} else {
    fwrite($fpa,"$log_date ファイル$file_orign がありません!\n");
}
/////////// commit トランザクション終了
query_affected_trans($con, 'commit');
fclose($fp_ctl);   ////// Exclusive用ファイルクローズ
fclose($fpa);      ////// 日報用ログ書込み終了

exit();

/***** テーブルが変更されている場合はfalseを返す     *****/
/***** 引数は比較するデータの配列とテーブルの配列   *****/
function checkTableChange($data, $res)
{
    for ($i=1; $i<7; $i++) {    // $data[6]までの7colmunをチェックする
        // 比較に邪魔をするスペースを削除
        if (trim($data[$i]) != trim($res[$i])) {
            return false;
        }
    }
    return true;
}

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
