<?php
//////////////////////////////////////////////////////////////////////////////
// データサムの日報データ 自動FTP UPLOAD HTTP/CGI版                         //
// Web Server (PHP) ----> AS/400                                            //
// Copyright (C) 2004-2004 2007 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp //
// 変更経歴                                                                 //
// 2004/06/14 新規作成 data_sum--as400-upload.php                           //
// 2007/04/11 AS/400に旧データがあるかチェックする関数old_data_check()追加  //
//////////////////////////////////////////////////////////////////////////////
ini_set('error_reporting', E_ALL);      // E_ALL='2047' debug 用
ini_set('implicit_flush', 'off');       // echo print で flush させない(遅くなるため)
ini_set('max_execution_time', 1200);    // 最大実行時間=20分
session_start();                        // ini_set()の次に指定すること Script 最上行
require_once ('/home/www/html/tnk-web/function.php');
access_log();                           // Script Name は自動取得

$log_date = date('Y-m-d H:i:s');            ///// 日報用ログの日時
$fpa = fopen('/tmp/data_sum.log', 'a');     ///// 日報用ログファイルへの書込みでオープン

// FTPのリモートファイル
define('REMOTE_F', 'NITTO/TGDTSMP');            // AS/400の受信ファイル
// ローカルのオリジナルファイル
define('ORIGIN_F', 'backup/data_sum_nippo.log');     // データサムの日報データ
// FTPのローカルファイル
define('LOCAL_F', 'backup/data_sum_upload.log');    // リネーム後のローカルファイル

/////////// AS/400の旧データをチェック
if (!old_data_check($fpa)) {
    $log_date = date('Y-m-d H:i:s');            ///// 日報用ログの日時
    fwrite($fpa, "$log_date データサム の旧データが AS/400 に残っていますので処理を中止しました。 \n");
    $_SESSION['s_sysmsg'] = "<span style='color:yellow;'>データサム の旧データが AS/400 に残っていますので処理を中止しました。</span><br>";
    fclose($fpa);   ///// 日報用ログファイルのクローズ
    header('Location: ' . H_WEB_HOST . SYS_MENU);
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
                    $_SESSION['s_sysmsg'] = "<font color='white'>ftp_put upload OK " . LOCAL_F . '→' . REMOTE_F . '</font><br>';
                    fwrite($fpa,"$log_date ftp_put upload OK " . LOCAL_F . '→' . REMOTE_F . "\n");
                } else {
                    $_SESSION['s_sysmsg'] = 'ftp_put() upload error ' . REMOTE_F;
                    fwrite($fpa,"$log_date ftp_put() upload error " . REMOTE_F . "\n");
                }
            } else {
                $_SESSION['s_sysmsg'] = 'DATA SUM ftp_login() error ';
                fwrite($fpa,"$log_date DATA SUM ftp_login() error \n");
            }
            ftp_close($ftp_stream);
        } else {
            $_SESSION['s_sysmsg'] = 'DATA SUM ftp_connect() error';
            fwrite($fpa,"$log_date DATA SUM ftp_connect() error\n");
        }
    } else {
        $_SESSION['s_sysmsg'] = 'DATA SUM rename() Error';
        fwrite($fpa,"$log_date DATA SUM rename() Error\n");
    }
} else {
    $_SESSION['s_sysmsg'] = 'DATA SUM 日報ファイルがありません！';
    fwrite($fpa,"$log_date DATA SUM 日報ファイルがありません。\n");
}
fclose($fpa);   ///// 日報用ログファイルのクローズ

header('Location: ' . H_WEB_HOST . SYS_MENU);



///////////////// AS/400 に旧データがあるかチェックする
function old_data_check($fpa)
{
    $log_date = date('Y-m-d H:i:s');            ///// 日報用ログの日時
    // FTPのターゲットファイル 上記で指定されている
    // 保存先のディレクトリとファイル名
    define('OLD_DATA', 'backup/dataSum_download.txt');    // save file
    
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
