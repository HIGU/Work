#!/usr/local/bin/php
<?php
//////////////////////////////////////////////////////////////////////////////
// データサムの日報データ 自動FTP UPLOAD cli版                              //
// Web Server (PHP) ----> AS/400                                            //
// Copyright (C) 2004 2007 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp      //
// Changed history                                                          //
// 2004/06/14 Created data_sum--as400-upload_cli.php                        //
// 2007/04/07 cgi版 → cli版へ変更 ファイル名を_cli.php へ変更              //
// 2007/04/11 AS/400に旧データがあるかチェックする関数old_data_check()追加  //
//////////////////////////////////////////////////////////////////////////////
ini_set('error_reporting', E_ALL);          // E_ALL='2047' debug 用
ini_set('implicit_flush', 'off');           // echo print で flush させない(遅くなるため)
// ini_set('max_execution_time', 1200);        // 最大実行時間=20分
// session_start();                        // ini_set()の次に指定すること Script 最上行

$currentFullPathName = realpath(dirname(__FILE__));
require_once ("{$currentFullPathName}/../../function.php");
// access_log();                           // Script Name は自動取得

$log_date = date('Y-m-d H:i:s');            ///// 日報用ログの日時
$fpa = fopen('/tmp/data_sum.log', 'a');     ///// 日報用ログファイルへの書込みでオープン

// FTPのリモートファイル
define('REMOTE_F', 'NITTO/TGDTSMP');            // AS/400の受信ファイル
// ローカルのオリジナルファイル
define('ORIGIN_F', "{$currentFullPathName}/backup/data_sum_nippo.log"); // データサムの日報データ
// FTPのローカルファイル
define('LOCAL_F', "{$currentFullPathName}/backup/data_sum_upload.log"); // リネーム後のローカルファイル

/////////// AS/400の旧データをチェック
if (!old_data_check($fpa, $currentFullPathName)) {
    $log_date = date('Y-m-d H:i:s');            ///// 日報用ログの日時
    fwrite($fpa, "$log_date データサム の旧データが AS/400 に残っていますので処理を中止しました。 \n");
    fclose($fpa);   ///// 日報用ログファイルのクローズ
    exit();
}

// 前回のアップロード用のファイルをチェック
if (file_exists(LOCAL_F)) {
    unlink(LOCAL_F);        // 前回のデータを削除
}

// データサムの日報データ存在チェック
if (file_exists(ORIGIN_F)) {
    // データサムの日報ファイルをリネームする
    if (rename(ORIGIN_F, LOCAL_F)) {
        // コネクションを取る(FTP接続のオープン)
        if ($ftp_stream = ftp_connect(AS400_HOST)) {
            if (ftp_login($ftp_stream, AS400_USER, AS400_PASS)) {
                ///// データサムの日報データをUPLOADする
                if (ftp_put($ftp_stream, REMOTE_F, LOCAL_F, FTP_ASCII)) {
                    fwrite($fpa,"$log_date ftp_put upload OK " . LOCAL_F . '→' . REMOTE_F . "\n");
                } else {
                    fwrite($fpa,"$log_date ftp_put() upload error " . REMOTE_F . "\n");
                }
            } else {
                fwrite($fpa,"$log_date DATA SUM ftp_login() error \n");
            }
            ftp_close($ftp_stream);
        } else {
            fwrite($fpa,"$log_date DATA SUM ftp_connect() error\n");
        }
    } else {
        fwrite($fpa,"$log_date DATA SUM rename() Error\n");
    }
} else {
    fwrite($fpa,"$log_date DATA SUM 日報ファイルがありません。\n");
}
fclose($fpa);   ///// 日報用ログファイルのクローズ

exit();



///////////////// AS/400 に旧データがあるかチェックする
function old_data_check($fpa, $dir)
{
    $log_date = date('Y-m-d H:i:s');            ///// 日報用ログの日時
    // FTPのターゲットファイル 上記で指定されている
    // 保存先のディレクトリとファイル名
    define('OLD_DATA', "{$dir}/backup/dataSum_download.txt");   // save file
    
    // コネクションを取る(FTP接続のオープン)
    if ($ftp_stream = ftp_connect(AS400_HOST)) {
        if (ftp_login($ftp_stream, AS400_USER, AS400_PASS)) {
            ///// 発注計画ファイル
            if (ftp_get($ftp_stream, OLD_DATA, REMOTE_F, FTP_ASCII)) {
                fwrite($fpa, "$log_date ftp_get download OK " . REMOTE_F . '→' . OLD_DATA . "\n");
            } else {
                fwrite($fpa, "$log_date ftp_get() error " . REMOTE_F . "\n");
                return false;
            }
        } else {
            fwrite($fpa, "$log_date ftp_login() error \n");
            return false;
        }
        ftp_close($ftp_stream);
    } else {
        fwrite($fpa, "$log_date ftp_connect() error -->\n");
        return false;
    }
    if (file_exists(OLD_DATA)) {         // ファイルの存在チェック
        $fpt = fopen(OLD_DATA, 'r');
        $i = 0;
        while (!(feof($fpt))) {
            $data = fgets($fpt, 300);
            if (feof($fpt)) {
                break;
            }
            $i++;
        }
        fclose($fpt);
        if ($i > 0) return false; else return true;
    }
    return true;
}
?>
