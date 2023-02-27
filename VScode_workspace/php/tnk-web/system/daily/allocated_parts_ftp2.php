#!/usr/local/bin/php
<?php
//////////////////////////////////////////////////////////////////////////////
// 引当データのAS/400との完全データリンクのため CHECK DATA DOWNLOAD   CLI版 //
// AS/400 ----> Web Server (PHP)                                            //
// Copyright (C) 2007      Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp      //
// Changed history                                                          //
// 2007/02/10 Created  allocated_parts_ftp2.php                             //
//       allocated_parts_ftp.php のD_TIALLC, S_TIALLC, $file_debug 変更のみ //
//            realTime2 との違いやデータの未変更チェックをしない点          //
// 2007/09/10 AS/400とのFTP error 対応のため ftpCheckAndExecute()関数を追加 //
//////////////////////////////////////////////////////////////////////////////
ini_set('error_reporting', E_ALL);      // E_ALL='2047' debug 用
ini_set('implicit_flush', 'off');       // echo print で flush させない(遅くなるため)
// ini_set('max_execution_time', 1200);    // 最大実行時間=20分 http/cgi版のみ
require_once ('/var/www/html/function.php');

$log_date = date('Y-m-d H:i:s');        ///// 日報用ログの日時
$fpa = fopen('/tmp/nippo.log', 'a');    ///// 日報用ログファイルへの書込みでオープン

// FTPのターゲットファイル2
define('D_TIALLC', 'UKWLIB/W#MIALLD');      // 引当部品ファイル download file
// 保存先のディレクトリとファイル名
define('S_TIALLC', '/var/www/html/system/backup/W#MIALLD.TXT');  // 引当部品ファイル save file

// コネクションを取る(FTP接続のオープン)
if ($ftp_stream = ftp_connect(AS400_HOST)) {
    if (ftp_login($ftp_stream, AS400_USER, AS400_PASS)) {
        ///// 引当部品ファイル
        if (ftpCheckAndExecute($ftp_stream, S_TIALLC, D_TIALLC, FTP_ASCII)) {
            // echo 'ftp_get download OK ', D_TIALLC, '→', S_TIALLC, "\n";
            fwrite($fpa,"$log_date ftp_get download OK " . D_TIALLC . '→' . S_TIALLC . "\n");
        } else {
            // echo 'ftp_get() error ', D_TIALLC, "\n";
            fwrite($fpa,"$log_date ftp_get() error " . D_TIALLC . "\n");
        }
    } else {
        // echo "ftp_login() error \n";
        fwrite($fpa,"$log_date ftp_login() error \n");
    }
    ftp_close($ftp_stream);
} else {
    // echo "ftp_connect() error --> 日報処理\n";
    fwrite($fpa,"$log_date ftp_connect() error --> 日報処理\n");
}



/////////// begin トランザクション開始
if ($con = db_connect()) {
    query_affected_trans($con, 'begin');
} else {
    fwrite($fpa, "$log_date db_connect() error \n");
    // echo "$log_date db_connect() error \n";
    exit();
}
// 引当部品ファイル 日報処理 準備作業
$file_orign  = S_TIALLC;
$file_debug  = '/var/www/html/system/debug/debug-MIALLD.TXT';
if (file_exists($file_orign)) {         // ファイルの存在チェック
    $fp  = fopen($file_orign, 'r');
    $fpw = fopen($file_debug, 'w');      // debug 用ファイルのオープン
    $rec = 0;       // レコード№
    $rec_ok = 0;    // 書込み成功レコード数
    $rec_ng = 0;    // 書込み失敗レコード数
    $ins_ok = 0;    // INSERT用カウンター
    $upd_ok = 0;    // UPDATE用カウンター
    $del_ok = 0;    // DELETE用カウンター
    $del_old = 0;   // 削除済用カウンター
    while (!(feof($fp))) {
        $data = fgetcsv($fp, 200, '_');     // 実レコードは95バイトなのでちょっと余裕をデリミタは'_'に注意
        if (feof($fp)) {
            break;
        }
        $rec++;
        
        $num  = count($data);       // フィールド数の取得
        if ($num != 11) {
            if ($num == 0 || $num == 1) {   // php-4.3.5で0返し php-4.3.6で1を返す仕様になった。fgetcsvの仕様変更による
                fwrite($fpa, "$log_date AS/400 del record=$rec \n");
                // echo "$log_date AS/400 del record=$rec \n";
            } else {
                fwrite($fpa, "$log_date field not 11 record=$rec \n");
                // echo "$log_date field not 11 record=$rec \n";
            }
           continue;
        }
        for ($f=0; $f<$num; $f++) {
            $data[$f] = mb_convert_encoding($data[$f], 'UTF-8', 'SJIS');       // SJISをEUC-JPへ変換
            $data[$f] = addslashes($data[$f]);       // "'"等がデータにある場合に\でエスケープする
            // $data_KV[$f] = mb_convert_kana($data[$f]);           // 半角カナを全角カナに変換
        }
        ////////// ステータスチェック (A=追加分 C=変更分 D=削除分)
        if ($data[9] == 'A' || $data[9] == 'C') {
            $query_chk = sprintf("SELECT parts_no FROM allocated_parts
                                    WHERE plan_no='%s' and parts_no='%s'",
                                    $data[0], $data[1]);
            if (getUniResTrs($con, $query_chk, $res_chk) <= 0) {    // トランザクション内での 照会専用クエリー
                ///// 登録なし insert 使用
                $query = "INSERT INTO allocated_parts (plan_no, parts_no, assy_no, unit_qt, allo_qt, sum_qt,
                                assy_str, cond, price, as_regdate)
                          VALUES(
                          '{$data[0]}',
                          '{$data[1]}',
                          '{$data[2]}',
                           {$data[3]} ,
                           {$data[4]} ,
                           {$data[5]} ,
                           {$data[6]} ,
                          '{$data[7]}',
                           {$data[8]},
                           {$data[10]})";
                if (query_affected_trans($con, $query) <= 0) {      // 更新用クエリーの実行
                    fwrite($fpa, "$log_date 計画番号:{$data[0]} : {$rec}:レコード目の書込みに失敗しました!\n");
                    // echo "$log_date 計画番号:{$data[0]} : {$rec}:レコード目の書込みに失敗しました!\n";
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
                $query = "UPDATE allocated_parts SET
                                plan_no     ='{$data[0]}',
                                parts_no    ='{$data[1]}',
                                assy_no     ='{$data[2]}',
                                unit_qt     = {$data[3]} ,
                                allo_qt     = {$data[4]} ,
                                sum_qt      = {$data[5]} ,
                                assy_str    = {$data[6]} ,
                                cond        ='{$data[7]}',
                                price       = {$data[8]} ,
                                as_regdate  = {$data[10]}
                    WHERE plan_no='{$data[0]}' and parts_no='{$data[1]}'";
                if (query_affected_trans($con, $query) <= 0) {      // 更新用クエリーの実行
                    fwrite($fpa, "$log_date 計画番号:{$data[0]} : {$rec}:レコード目のUPDATEに失敗しました!\n");
                    // echo "$log_date 計画番号:{$data[0]} : {$rec}:レコード目のUPDATEに失敗しました!\n";
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
        } elseif ($data[9] == 'D') {    //////// D=削除分の処理
            $query_chk = sprintf("SELECT parts_no FROM allocated_parts
                                    WHERE plan_no='%s' and parts_no='%s'",
                                    $data[0], $data[1]);
            if (getUniResTrs($con, $query_chk, $res_chk) > 0) {    // トランザクション内での 照会専用クエリー
                ////// 登録あり 削除実行
                $query_del = sprintf("DELETE FROM allocated_parts
                                    WHERE plan_no='%s' and parts_no='%s'",
                                    $data[0], $data[1]);
                if ( ($del_num = query_affected_trans($con, $query_del)) != 1) {  // 更新用クエリーの実行
                    fwrite($fpa, "$log_date 計画番号:{$data[0]} : {$rec}：削除レコード数{$del_num}:レコード目のDELETEに失敗しました!\n");
                    // echo "$log_date 計画番号:{$data[0]} : {$rec}：削除レコード数{$del_num}:レコード目のDELETEに失敗しました!\n";
                    // query_affected_trans($con, "rollback");     // transaction rollback
                    $rec_ng++;
                    ////////////////////////////////////////// Debug start
                    for ($f=0; $f<$num; $f++) {
                        fwrite($fpw,"'{$data[$f]}',");      // debug
                    }
                    fwrite($fpw,"\n");                      // debug
                    fwrite($fpw, "$query_del \n");              // debug
                    break;                                  // debug
                    ////////////////////////////////////////// Debug end
                } else {
                    $rec_ok++;
                    $del_ok++;
                }
            } else {
                $del_old++;
            }
        }
    }
    fclose($fp);
    fclose($fpw);       // debug
    fwrite($fpa, "$log_date 引当部品の更新:{$data[1]} : $rec_ok/$rec 件登録しました。\n");
    fwrite($fpa, "$log_date 引当部品の更新:{$data[1]} : {$ins_ok}/{$rec} 件 追加 \n");
    fwrite($fpa, "$log_date 引当部品の更新:{$data[1]} : {$upd_ok}/{$rec} 件 変更 \n");
    fwrite($fpa, "$log_date 引当部品の更新:{$data[1]} : {$del_ok}/{$rec} 件 削除 \n");
    fwrite($fpa, "$log_date 引当部品の更新:{$data[1]} : {$del_old}/{$rec} 件 削除済 \n");
    // echo "$log_date 引当部品の更新:{$data[1]} : $rec_ok/$rec 件登録しました。\n";
    // echo "$log_date 引当部品の更新:{$data[1]} : {$ins_ok}/{$rec} 件 追加 \n";
    // echo "$log_date 引当部品の更新:{$data[1]} : {$upd_ok}/{$rec} 件 変更 \n";
    // echo "$log_date 引当部品の更新:{$data[1]} : {$del_ok}/{$rec} 件 削除 \n";
    // echo "$log_date 引当部品の更新:{$data[1]} : {$del_old}/{$rec} 件 削除済 \n";
} else {
    fwrite($fpa,"$log_date ファイル$file_orign がありません!\n");
    // echo "{$log_date}: file:{$file_orign} がありません!\n";
}
/////////// commit トランザクション終了
query_affected_trans($con, "commit");
// echo $query . "\n";  // debug
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
