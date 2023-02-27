<?php
//////////////////////////////////////////////////////////////////////////////
// 機械運転日報(製造課) データ 自動FTP UPLOAD HTTP/CGI版                    //
// Web Server (PHP) ----> AS/400                                            //
// Copyright (C) 2004-2005 2007 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp //
// Changed history                                                          //
// 2004/08/05 Created   equip_report--as400-upload.php                      //
// 2005/07/06 error処理部tでfclose()/header()が２箇所抜けていたのを追加     //
// 2007/03/29 機械運転日報のテーブルequip_uploadのレイアウト変更及びサマリー//
//            equip_upload_summary 追加によるロジックの追加変更             //
//            equip_upload 読込み時の ORDER BY に from_time ASC 追加        //
// 2007/03/30 equip_upload_summary テーブルを トランザクション内に追加      //
// 2007/03/31 ROLLBACK と 完了フラグの０チェックを追加                      //
// 2007/04/07 AS/400に旧データがあるかチェックする関数old_data_check()追加  //
// 2007/04/11 明細・サマリーの件数をログに追加                              //
// 2007/05/02 明細のみに ORDER BY があったが、サマリーにも ORDER BY を追加  //
//          月初から２営業日までは当月の機械運転日報は更新しないロジック追加//
// 2007/05/07 排他制御用のコントロールファイルのデータチェックを追加        //
//////////////////////////////////////////////////////////////////////////////
ini_set('error_reporting', E_ALL);          // E_ALL='2047' debug 用
ini_set('implicit_flush', 'off');           // echo print で flush させない(遅くなるため)
ini_set('max_execution_time', 1200);        // 最大実行時間=20分
session_start();                            // ini_set()の次に指定すること Script 最上行

$currentFullPathName = realpath(dirname(__FILE__));
require_once ("{$currentFullPathName}/../../function.php");
access_log();                               // Script Name は自動取得

$log_date = date('Y-m-d H:i:s');            ///// 日報用ログの日時
$fpa = fopen('/tmp/equip_report.log', 'a'); ///// 日報用ログファイルへの書込みでオープン

// 排他制御用コントロールファイル
define('AS_CTGMAP', 'UKWLIB/C#TGMAP');      // 機械運転日報コントロール
// 保存先のディレクトリとファイル名
define('C_TGMAP', "{$currentFullPathName}/backup/C#TGMAP.TXT"); // 機械運転日報コントロール

// FTPのリモートファイル
define('REMOTE_F1', 'UKWLIB/TGMATMP');      // AS/400の受信ファイル 明細
define('REMOTE_F2', 'UKWLIB/TGMADVP');      // AS/400の受信ファイル サマリー
// ローカルの運転日報 明細 ファイル
define('UPLOAD_F1', "{$currentFullPathName}/backup/equip_upload.log");         // 機械運転日報 明細 textデータ
// ローカルの運転日報 サマリー ファイル
define('UPLOAD_F2', "{$currentFullPathName}/backup/equip_upload_summary.log"); // 機械運転日報 サマリー textデータ

/////////// AS/400の旧データをチェック
if (!old_data_check($fpa)) {
    $log_date = date('Y-m-d H:i:s');            ///// 日報用ログの日時
    fwrite($fpa, "$log_date 機械運転日報 の旧データが AS/400 に残っていますので処理を中止しました。 \n");
    $_SESSION['s_sysmsg'] = "<span style='color:yellow;'>機械運転日報 の旧データが AS/400 に残っていますので処理を中止しました。</span><br>";
    fclose($fpa);   ///// 日報用ログファイルのクローズ
    header('Location: ' . H_WEB_HOST . SYS_MENU);
    exit();
}

/////////// begin トランザクション開始
if ($con = db_connect()) {
    query_affected_trans($con, 'BEGIN');
    query_affected_trans($con, 'LOCK equip_upload');            // 明細 テーブルをロックする
    query_affected_trans($con, 'LOCK equip_upload_summary');    // サマリー テーブルをロックする
} else {
    fwrite($fpa, "$log_date db_connect() error \n");
    $_SESSION['s_sysmsg'] = '機械運転日報 db_connect() error ';
    fclose($fpa);   ///// 日報用ログファイルのクローズ
    header('Location: ' . H_WEB_HOST . SYS_MENU);
    exit();
}

///// 月初から２営業日までは当月の機械運転日報は更新しない
///// 製造課の仕掛（棚卸）報告のため（仕掛リストが随時変化してしまうため）
if (workingDayCheck($con, date('Ymd')) <= 2) {
    $dateStart = date('Ym') . '01';
    $where = "WHERE work_date < {$dateStart}";
} else {
    $where = '';
}

///// 機械運転日報の 明細 アップロード用テーブルからデータ取得
$query = "
    SELECT  
        siji_no
        , work_date
        , mac_no
        , koutei
        , from_time
        , to_time
        , cut_time
        , mac_state
    FROM
        equip_upload
    {$where}
    ORDER BY
        work_date ASC, mac_no ASC, siji_no ASC, koutei ASC, from_time ASC
";
$res = array();
if ( ($rows=getResultTrs($con, $query, $res)) < 1) {
    $log_date = date('Y-m-d H:i:s');            ///// 日報用ログの日時
    fwrite($fpa, "$log_date 機械運転日報 明細の 確定データがありません！ \n");
    $_SESSION['s_sysmsg'] = "<font color='yellow'>機械運転日報 明細の 確定データがありません！</font>";
    fclose($fpa);   ///// 日報用ログファイルのクローズ
    query_affected_trans($con, 'ROLLBACK');
    header('Location: ' . H_WEB_HOST . SYS_MENU);
    exit();
} else {
    $fp = fopen(UPLOAD_F1, 'w');     ///// 日報用 明細 ファイルへの書込みでオープン
    for ($i=0; $i<$rows; $i++) {
        $log_record = sprintf("%5s%8s%4s%2s%4s%4s%4s%1s\n", $res[$i][0], $res[$i][1], $res[$i][2], $res[$i][3], $res[$i][4], $res[$i][5], $res[$i][6], $res[$i][7]);
        if (fwrite($fp, $log_record) == FALSE) {
            $log_date = date('Y-m-d H:i:s');            ///// 日報用ログの日時
            fwrite($fpa, "$log_date 日報のUPLOADデータ生成 error \n");
            $_SESSION['s_sysmsg'] = '日報のUPLOADデータ生成 error';
            fclose($fpa);   ///// 日報用ログファイルのクローズ
            query_affected_trans($con, 'ROLLBACK');
            header('Location: ' . H_WEB_HOST . SYS_MENU);
            exit();
        }
    }
    $sql = "
        INSERT INTO equip_upload_history
        SELECT * FROM equip_upload {$where}
        ;
        DELETE FROM equip_upload {$where}
    ";
    query_affected_trans($con, $sql);
}
fclose($fp);   ///// 明細 ファイルのクローズ
$log_date = date('Y-m-d H:i:s');            ///// 日報用ログの日時
fwrite($fpa, "$log_date 機械運転日報 明細データ $rows 件 \n");
$_SESSION['s_sysmsg'] = "<span style='color:white;'>明細 $rows 件</span><br>";


///// 機械運転日報の サマリー アップロード用テーブルからデータ取得
$query = "
    SELECT
        work_date       ,
        mac_no          ,
        siji_no         ,
        koutei          ,
        item_code       ,
        plan_time       ,
        running_time    ,
        repair_time     ,
        edge_time       ,
        stop_time       ,
        idling_time     ,
        auto_time       ,
        others_time     ,
        ok_item_num     ,
        ng_item_num     ,
        plan_num        ,
        end_flg         ,
        ng_code         ,
        stop_count      ,
        plan_count      ,
        repair_count    ,
        processing_date ,
        injection_item  ,
        injection       
    FROM
        equip_upload_summary
    {$where}
    ORDER BY
        work_date ASC, mac_no ASC, siji_no ASC, koutei ASC
";
$res = array();
if ( ($rows=getResultTrs($con, $query, $res)) < 1) {
    $log_date = date('Y-m-d H:i:s');            ///// 日報用ログの日時
    fwrite($fpa, "$log_date 機械運転日報 サマリーの 確定データがありません！ \n");
    $_SESSION['s_sysmsg'] .= "<font color='yellow'>機械運転日報 サマリーの 確定データがありません！</font>";
    fclose($fpa);   ///// 日報用ログファイルのクローズ
    query_affected_trans($con, 'ROLLBACK');
    header('Location: ' . H_WEB_HOST . SYS_MENU);
    exit();
} else {
    $fp = fopen(UPLOAD_F2, 'w');     ///// 日報 サマリー ファイルへの書込みでオープン
    for ($i=0; $i<$rows; $i++) {
        if ($res[$i][16] != 'E') $res[$i][16] = ' ' ;   // 完了フラグ(end_flg)をチェック０が入ってしまう対策
        $log_record = sprintf("%8s%4s%8s%2s%9s%4s%4s%4s%4s%4s%4s%4s%4s%5s%5s%5s%1s%2s%3s%3s%3s%8s%7s%9s\n",
            $res[$i][0], $res[$i][1], $res[$i][2], $res[$i][3], $res[$i][4], $res[$i][5], $res[$i][6], $res[$i][7],
            $res[$i][8], $res[$i][9], $res[$i][10], $res[$i][11], $res[$i][12], $res[$i][13], $res[$i][14], $res[$i][15],
            $res[$i][16], $res[$i][17], $res[$i][18], $res[$i][19], $res[$i][20], $res[$i][21], $res[$i][22], $res[$i][23]
        );
        if (fwrite($fp, $log_record) == FALSE) {
            $log_date = date('Y-m-d H:i:s');            ///// 日報用ログの日時
            fwrite($fpa, "$log_date 日報のUPLOADデータ生成 error \n");
            $_SESSION['s_sysmsg'] .= '日報のUPLOADデータ生成 error';
            fclose($fpa);   ///// 日報用ログファイルのクローズ
            query_affected_trans($con, 'ROLLBACK');
            header('Location: ' . H_WEB_HOST . SYS_MENU);
            exit();
        }
    }
    $sql = "
        INSERT INTO equip_upload_summary_history
        SELECT * FROM equip_upload_summary {$where}
        ;
        DELETE FROM equip_upload_summary {$where}
    ";
    query_affected_trans($con, $sql);
}
fclose($fp);   ///// サマリー ファイルのクローズ
$log_date = date('Y-m-d H:i:s');            ///// 日報用ログの日時
fwrite($fpa, "$log_date 機械運転日報 サマリーデータ $rows 件 \n");
$_SESSION['s_sysmsg'] .= "<span style='color:white;'>サマリー $rows 件</span><br>";



/////////// UPLOADデータ生成 OK
$log_date = date('Y-m-d H:i:s');            ///// 日報用ログの日時
fwrite($fpa, "$log_date 機械運転日報 のUPLOADデータ生成 OK \n");
$_SESSION['s_sysmsg'] .= "<font color='white'>機械運転日報 のUPLOADデータ生成 OK</font><br>";



////////// FTP 転送開始
// OK NG フラグセット
$ftp_flg = false;
// 機械運転日報 明細とサマリーの日報データ存在チェック
if (file_exists(UPLOAD_F1) && file_exists(UPLOAD_F2)) {
    // コネクションを取る(FTP接続のオープン)
    if ($ftp_stream = ftp_connect(AS400_HOST)) {
        if (ftp_login($ftp_stream, AS400_USER, AS400_PASS)) {
            ///// コントロールファイルのレコードチェック（排他制御）
            if (ftp_get($ftp_stream, C_TGMAP, AS_CTGMAP, FTP_ASCII)) {
                $log_date = date('Y-m-d H:i:s');        ///// ログの日時
                fwrite($fpa,"$log_date ftp_get コントロール download OK " . AS_CTGMAP . '→' . C_TGMAP . "\n");
                if (checkControlFile($fpa, C_TGMAP)) {
                    fwrite($fpa,"$log_date コントロールファイルにデータがあるで終了します。\n");
                    $_SESSION['s_sysmsg'] .= "<span style='color:yellow;'>コントロールファイルにデータがあるで終了します。</span>";
                    ftp_close($ftp_stream);
                    query_affected_trans($con, 'ROLLBACK');
                    fclose($fpa);      ////// 強制終了
                    header('Location: ' . H_WEB_HOST . SYS_MENU);
                    exit();
                }
            } else {
                $log_date = date('Y-m-d H:i:s');        ///// ログの日時
                fwrite($fpa,"$log_date ftp_get() コントロール error " . AS_CTGMAP . "\n");
                $_SESSION['s_sysmsg'] .= "<span style='color:red;'>ftp_get() コントロール error</span>";
                ftp_close($ftp_stream);
                query_affected_trans($con, 'ROLLBACK');
                fclose($fpa);      ////// 強制終了
                header('Location: ' . H_WEB_HOST . SYS_MENU);
                exit();
            }
            ///// 機械運転日報 明細データをUPLOADする
            if (ftp_put($ftp_stream, REMOTE_F1, UPLOAD_F1, FTP_ASCII)) {
                $_SESSION['s_sysmsg'] .= "<span style='color:white;'>機械運転日報 明細の UPLOAD が正常に完了しました。 " . UPLOAD_F1 . '→' . REMOTE_F1 . '</span>';
                $log_date = date('Y-m-d H:i:s');            ///// 日報用ログの日時
                fwrite($fpa,"$log_date 機械運転日報 明細 ftp_put upload OK " . UPLOAD_F1 . '→' . REMOTE_F1 . "\n");
                $ftp_flg = true;
            } else {
                $_SESSION['s_sysmsg'] .= 'ftp_put() upload error ' . REMOTE_F1;
                $log_date = date('Y-m-d H:i:s');            ///// 日報用ログの日時
                fwrite($fpa,"$log_date 機械運転日報 明細 ftp_put() upload error " . REMOTE_F1 . "\n");
            }
            ///// 機械運転日報 サマリーデータをUPLOADする
            if (ftp_put($ftp_stream, REMOTE_F2, UPLOAD_F2, FTP_ASCII)) {
                $_SESSION['s_sysmsg'] .= "<span style='color:white;'>機械運転日報 サマリーの UPLOAD が正常に完了しました。 " . UPLOAD_F2 . '→' . REMOTE_F2 . '</span>';
                $log_date = date('Y-m-d H:i:s');            ///// 日報用ログの日時
                fwrite($fpa,"$log_date 機械運転日報 サマリー ftp_put upload OK " . UPLOAD_F2 . '→' . REMOTE_F2 . "\n");
                if ($ftp_flg) $ftp_flg = true; else $ftp_flg = false;
            } else {
                $_SESSION['s_sysmsg'] .= 'ftp_put() upload error ' . REMOTE_F2;
                $log_date = date('Y-m-d H:i:s');            ///// 日報用ログの日時
                fwrite($fpa,"$log_date 機械運転日報 サマリー ftp_put() upload error " . REMOTE_F2 . "\n");
                $ftp_flg = false;
            }
        } else {
            $_SESSION['s_sysmsg'] .= '機械運転日報 ftp_login() error ';
            $log_date = date('Y-m-d H:i:s');            ///// 日報用ログの日時
            fwrite($fpa,"$log_date 機械運転日報 ftp_login() error \n");
        }
        ftp_close($ftp_stream);
    } else {
        $_SESSION['s_sysmsg'] .= '機械運転日報 ftp_connect() error';
        $log_date = date('Y-m-d H:i:s');            ///// 日報用ログの日時
        fwrite($fpa,"$log_date 機械運転日報 ftp_connect() error\n");
    }
} else {
    $_SESSION['s_sysmsg'] .= '機械運転日報 明細またはサマリー 日報ファイルがありません！';
    $log_date = date('Y-m-d H:i:s');            ///// 日報用ログの日時
    fwrite($fpa,"$log_date 機械運転日報 明細またはサマリー 日報ファイルがありません。\n");
}



/////////// commit トランザクション終了
if ($ftp_flg) {
    query_affected_trans($con, 'COMMIT');
} else {
    query_affected_trans($con, 'ROLLBACK');
}
fclose($fpa);   ///// 日報用ログファイルのクローズ
header('Location: ' . H_WEB_HOST . SYS_MENU);
exit();



///////////////// AS/400 に旧データがあるかチェックする
function old_data_check($fpa)
{
    $log_date = date('Y-m-d H:i:s');            ///// 日報用ログの日時
    // FTPのターゲットファイル 上記で指定されている
    // 保存先のディレクトリとファイル名
    define('OLD_DATA', 'backup/equip_download.txt');    // save file
    
    // コネクションを取る(FTP接続のオープン)
    if ($ftp_stream = ftp_connect(AS400_HOST)) {
        if (ftp_login($ftp_stream, AS400_USER, AS400_PASS)) {
            ///// 発注計画ファイル
            if (ftp_get($ftp_stream, OLD_DATA, REMOTE_F2, FTP_ASCII)) {
                fwrite($fpa,"$log_date 旧データチェック用 download OK " . REMOTE_F2 . '→' . OLD_DATA . "\n");
            } else {
                fwrite($fpa,"$log_date ftp_get() error " . REMOTE_F2 . "\n");
                return false;
            }
        } else {
            fwrite($fpa,"$log_date ftp_login() error \n");
            return false;
        }
        ftp_close($ftp_stream);
    } else {
        fwrite($fpa,"$log_date ftp_connect() error -->\n");
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

///////////////// 対象月の営業日の日数を返す
function workingDayCheck($con, $date='')
{
    if (!$date) $date = date('Ymd');
    if (strlen($date) != 8) return false;
    if (!is_numeric($date)) return false;
    // 月初の１日からスタート 営業日は未定なので0セット
    $i = 1; $workingDay = 0;
    $dateStart = sprintf(substr($date, 0, 6) . '%02d', $i);
    $con = db_connect();
    while ($dateStart <= $date) {
        $query = "
            SELECT bd_flg FROM company_calendar WHERE tdate='{$dateStart}'
        ";
        $bd_flg = 'f';
        getUniResTrs($con, $query, $bd_flg);
        if ($bd_flg == 't') {
            $workingDay++;
        }
        $i++;
        $dateStart = sprintf(substr($date, 0, 6) . '%02d', $i);
    }
    return $workingDay;
}

function checkControlFile($fpa, $ctl_file)
{
    $fp = fopen($ctl_file, 'r');
    $data = fgets($fp, 20);     // 実レコードは11バイトなのでちょっと余裕を
    if (feof($fp)) {
        return false;
    } else {
        $log_date = date('Y-m-d H:i:s');        ///// ログの日時
        fwrite($fpa, "$log_date 機械運転日報 : 使用端末は {$data}");
        return true;
    }
}
?>
