#!/usr/local/bin/php
<?php
//////////////////////////////////////////////////////////////////////////////
// 組立計画データ＠分のAS/400との完全データリンク CHECK DATA DOWNLOAD CLI版 //
// AS/400 ----> Web Server (PHP)  とアップデート                            //
// Copyright (C) 2007      Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp      //
// Changed history                                                          //
// 2007/05/28 Created  assembly_schedule_checkDataDownloadUpdate.php        //
// 2007/09/12 AS/400とのFTPエラー回避のためftpGetCheckAndExecute()を追加    //
//////////////////////////////////////////////////////////////////////////////
ini_set('error_reporting', E_ALL);      // E_ALL='2047' debug 用
ini_set('implicit_flush', 'off');       // echo print で flush させない(遅くなるため)
// ini_set('max_execution_time', 1200);    // 最大実行時間=20分

$currentFullPathName = realpath(dirname(__FILE__));
require_once ("{$currentFullPathName}/../../function.php");

$log_date = date('Y-m-d H:i:s');        ///// 日報用ログの日時
$fpa = fopen('/tmp/nippo.log', 'a');    ///// 日報用ログファイルへの書込みでオープン

fwrite($fpa, "$log_date 組立日程計画＠生産引当分差分データDOWNLOAD＆更新開始 \n");

// 排他制御用コントロールファイル
define('CMIPPL', 'UKWLIB/C#MIPPL');        // 組立日程計画＠生産引当コントロール
// 保存先のディレクトリとファイル名
define('C_MIPPL', "{$currentFullPathName}/backup/C#MIPPL.TXT");

// FTPのターゲットファイル2
define('D_TIALLC', 'UKWLIB/W#MIPPLN');      // 日程計画＠ファイル download file
// 保存先のディレクトリとファイル名
define('S_TIALLC', "{$currentFullPathName}/backup/W#MIPPLN.TXT");  // 日程計画＠ファイル save file

// コネクションを取る(FTP接続のオープン)
if ($ftp_stream = ftp_connect(AS400_HOST)) {
    if (ftp_login($ftp_stream, AS400_USER, AS400_PASS)) {
        ///// 組立日程計画＠生産引当コントロールファイルチェック
        if (ftpGetCheckAndExecute($ftp_stream, C_MIPPL, CMIPPL, FTP_ASCII)) {
            $log_date = date('Y-m-d H:i:s');        ///// ログの日時
            fwrite($fpa,"$log_date ftp_get コントロール download OK " . CMIPPL . '→' . C_MIPPL . "\n");
            if (checkControlFile($fpa, C_MIPPL)) {
                fwrite($fpa,"$log_date コントロールファイルにデータがあるので終了します。\n");
                ftp_close($ftp_stream);
                fclose($fpa);      ////// 強制終了
                exit();
            }
        } else {
            $log_date = date('Y-m-d H:i:s');        ///// ログの日時
            fwrite($fpa,"$log_date ftp_get() コントロール error " . CMIPPL . "\n");
            ftp_close($ftp_stream);
            fclose($fpa);      ////// 強制終了
            exit();
        }
        ///// 組立日程＠ファイル
        if (ftpGetCheckAndExecute($ftp_stream, S_TIALLC, D_TIALLC, FTP_ASCII)) {
            $log_date = date('Y-m-d H:i:s');
            // echo 'ftp_get download OK ', D_TIALLC, '→', S_TIALLC, "\n";
            fwrite($fpa,"$log_date ftp_get download OK " . D_TIALLC . '→' . S_TIALLC . "\n");
        } else {
            $log_date = date('Y-m-d H:i:s');
            // echo 'ftp_get() error ', D_TIALLC, "\n";
            fwrite($fpa,"$log_date ftp_get() error " . D_TIALLC . "\n");
        }
    } else {
        $log_date = date('Y-m-d H:i:s');
        // echo "ftp_login() error \n";
        fwrite($fpa,"$log_date ftp_login() error \n");
    }
    ftp_close($ftp_stream);
} else {
    $log_date = date('Y-m-d H:i:s');
    // echo "ftp_connect() error --> 日報処理\n";
    fwrite($fpa,"$log_date ftp_connect() error --> 日報処理\n");
}



/////////// begin トランザクション開始
if ($con = db_connect()) {
    query_affected_trans($con, 'BEGIN');
} else {
    $log_date = date('Y-m-d H:i:s');
    fwrite($fpa, "$log_date db_connect() error \n");
    // echo "$log_date db_connect() error \n";
    exit();
}
// 組立日程＠ファイル 日報処理 準備作業
$file_orign  = S_TIALLC;
$file_debug  = "{$currentFullPathName}/debug/debug-MIPPLN.TXT";
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
        $data = fgetcsv($fp, 120, '_');     // 実レコードは95バイトなのでちょっと余裕をデリミタは'_'に注意
        if (feof($fp)) {
            break;
        }
        $rec++;
        
        $num  = count($data);       // フィールド数の取得
        if ($num != 15) {
            if ($num == 0 || $num == 1) {   // php-4.3.5で0返し php-4.3.6で1を返す仕様になった。fgetcsvの仕様変更による
                $log_date = date('Y-m-d H:i:s');
                fwrite($fpa, "$log_date AS/400 del record=$rec \n");
                // echo "$log_date AS/400 del record=$rec \n";
            } else {
                $log_date = date('Y-m-d H:i:s');
                fwrite($fpa, "$log_date field not 15 record=$rec \n");
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
        if ($data[0] == 'C') {
            ///// 登録あり update 使用
            $query = "UPDATE assembly_schedule SET
                            parts_no    ='{$data[2]}',
                            syuka       = {$data[3]} ,
                            chaku       = {$data[4]} ,
                            kanryou     = {$data[5]} ,
                            plan        = {$data[6]} ,
                            cut_plan    = {$data[7]} ,
                            kansei      = {$data[8]} ,
                            nyuuko      ='{$data[9]}',
                            sei_kubun   ='{$data[10]}',
                            line_no     ='{$data[11]}',
                            p_kubun     ='{$data[12]}',
                            assy_site   ='{$data[13]}',
                            dept        ='{$data[14]}'
                WHERE plan_no='{$data[1]}'";
            if (query_affected_trans($con, $query) <= 0) {      // 更新用クエリーの実行
                $log_date = date('Y-m-d H:i:s');
                fwrite($fpa, "$log_date 計画番号:{$data[1]} : {$rec}:レコード目のUPDATEに失敗しました!\n");
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
        } elseif ($data[0] == 'D') {    //////// D=削除分の処理
            $query_chk = sprintf("SELECT plan_no FROM assembly_schedule WHERE plan_no='%s'", $data[1]);
            if (getUniResTrs($con, $query_chk, $res_chk) > 0) {    // トランザクション内での 照会専用クエリー
                ////// 登録あり 削除実行
                $query_del = sprintf("DELETE FROM assembly_schedule WHERE plan_no='%s'", $data[1]);
                if ( ($del_num = query_affected_trans($con, $query_del)) != 1) {  // 更新用クエリーの実行
                    $log_date = date('Y-m-d H:i:s');
                    fwrite($fpa, "$log_date 計画番号:{$data[1]} : {$rec}：削除レコード数{$del_num}:レコード目のDELETEに失敗しました!\n");
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
    $log_date = date('Y-m-d H:i:s');
    fwrite($fpa, "$log_date 組立日程＠の更新:{$data[1]} : $rec_ok/$rec 件登録しました。\n");
    fwrite($fpa, "$log_date 組立日程＠の更新:{$data[1]} : {$ins_ok}/{$rec} 件 追加 \n");
    fwrite($fpa, "$log_date 組立日程＠の更新:{$data[1]} : {$upd_ok}/{$rec} 件 変更 \n");
    fwrite($fpa, "$log_date 組立日程＠の更新:{$data[1]} : {$del_ok}/{$rec} 件 削除 \n");
    fwrite($fpa, "$log_date 組立日程＠の更新:{$data[1]} : {$del_old}/{$rec} 件 削除済 \n");
    // echo "$log_date 組立日程＠の更新:{$data[1]} : $rec_ok/$rec 件登録しました。\n";
    // echo "$log_date 組立日程＠の更新:{$data[1]} : {$ins_ok}/{$rec} 件 追加 \n";
    // echo "$log_date 組立日程＠の更新:{$data[1]} : {$upd_ok}/{$rec} 件 変更 \n";
    // echo "$log_date 組立日程＠の更新:{$data[1]} : {$del_ok}/{$rec} 件 削除 \n";
    // echo "$log_date 組立日程＠の更新:{$data[1]} : {$del_old}/{$rec} 件 削除済 \n";
} else {
    $log_date = date('Y-m-d H:i:s');
    fwrite($fpa,"$log_date ファイル$file_orign がありません!\n");
    // echo "{$log_date}: file:{$file_orign} がありません!\n";
}
/////////// commit トランザクション終了
query_affected_trans($con, 'COMMIT');
// echo $query . "\n";  // debug
fclose($fpa);      ////// 日報用ログ書込み終了

exit();


function checkControlFile($fpa, $ctl_file)
{
    $fp = fopen($ctl_file, 'r');
    $data = fgets($fp, 20);     // 実レコードは11バイトなのでちょっと余裕を
    if (feof($fp)) {
        return false;
    } else {
        $log_date = date('Y-m-d H:i:s');        ///// ログの日時
        fwrite($fpa, "$log_date 組立計画＠生産引当 : 使用状況は {$data}");
        return true;
    }
}

function ftpGetCheckAndExecute($stream, $local_file, $as400_file, $ftp)
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
