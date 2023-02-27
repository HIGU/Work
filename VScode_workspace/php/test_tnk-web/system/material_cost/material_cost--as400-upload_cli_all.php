#!/usr/local/bin/php
<?php
//////////////////////////////////////////////////////////////////////////////
// 総材料費 データ 自動FTP UPLOAD CLI版  (最新の総材料費を一括登録用）      //
// Web Server (PHP) ----> AS/400                                            //
// Copyright (C) 2010 Norihisa.Ohya norihisa_ooya@nitto-kohki.co.jp         //
// Changed history                                                          //
// 2010/11/12 Created   material_cost--as400-upload_cli_all.php             //
// 2010/11/24 組立費が57.00だったのをassy_rateに変更                        //
//////////////////////////////////////////////////////////////////////////////
ini_set('error_reporting', E_ALL);          // E_ALL='2047' debug 用
ini_set('implicit_flush', 'off');           // echo print で flush させない(遅くなるため)
// ini_set('max_execution_time', 1200);        // 最大実行時間=20分
// session_start();                            // ini_set()の次に指定すること Script 最上行

$currentFullPathName = realpath(dirname(__FILE__));
require_once ("{$currentFullPathName}/../../function.php");
// access_log();                               // Script Name は自動取得

$log_date = date('Y-m-d H:i:s');            ///// 日報用ログの日時
$fpa = fopen('/tmp/material_report.log', 'a'); ///// 日報用ログファイルへの書込みでオープン

// 排他制御用コントロールファイル
define('AS_CTIGMP', 'UKWLIB/C#TIGMP');      // 総材料費コントロール
// 保存先のディレクトリとファイル名
define('C_TIGMP', "{$currentFullPathName}/backup/C#TIGMP.TXT"); // 総材料費コントロール

// FTPのリモートファイル
define('REMOTE_F1', 'UKWLIB/TIGMOTP');      // AS/400の受信ファイル 明細
// ローカルの運転日報 明細 ファイル
define('UPLOAD_F1', "{$currentFullPathName}/backup/material_cost_upload.log");         // 総材料費 textデータ

/////////// AS/400の旧データをチェック
if (!old_data_check($fpa, $currentFullPathName)) {
    $log_date = date('Y-m-d H:i:s');            ///// 日報用ログの日時
    fwrite($fpa, "$log_date 総材料費 の旧データが AS/400 に残っていますので処理を中止しました。 \n");
    fclose($fpa);   ///// 日報用ログファイルのクローズ
    exit();
}

/////////// begin トランザクション開始
if ($con = db_connect()) {
    query_affected_trans($con, 'BEGIN');
    query_affected_trans($con, 'LOCK material_cost_summary');    // サマリー テーブルをロックする
} else {
    fwrite($fpa, "$log_date db_connect() error \n");
    fclose($fpa);   ///// 日報用ログファイルのクローズ
    exit();
}

$where = '';

///// 総材料費の サマリー アップロード用テーブルからデータ取得
$query = "
    SELECT
        u.assy_no                    AS 製品番号
        ,
        (SELECT plan_no FROM material_cost_header WHERE assy_no = u.assy_no ORDER BY assy_no DESC, regdate DESC LIMIT 1)
                                    AS 総材料計画
        ,
        (SELECT sum_price FROM material_cost_header WHERE assy_no = u.assy_no ORDER BY assy_no DESC, regdate DESC LIMIT 1)
                                    AS 最新総材料費
        ,
        (SELECT m_time FROM material_cost_header WHERE assy_no = u.assy_no ORDER BY assy_no DESC, regdate DESC LIMIT 1)                     AS 手作業工数
        ,
        (SELECT a_time FROM material_cost_header WHERE assy_no = u.assy_no ORDER BY assy_no DESC, regdate DESC LIMIT 1)                     AS 自動機工数
        ,        
        (SELECT g_time FROM material_cost_header WHERE assy_no = u.assy_no ORDER BY assy_no DESC, regdate DESC LIMIT 1)                     AS 外注工数
        ,
        Uround((SELECT assy_rate FROM material_cost_header WHERE assy_no = u.assy_no ORDER BY assy_no DESC, regdate DESC LIMIT 1) * (SELECT m_time FROM material_cost_header WHERE assy_no = u.assy_no ORDER BY assy_no DESC, regdate DESC LIMIT 1), 2)                     AS 手作業組立費
        ,
        Uround((SELECT a_rate FROM material_cost_header WHERE assy_no = u.assy_no ORDER BY assy_no DESC, regdate DESC LIMIT 1) * (SELECT a_time FROM material_cost_header WHERE assy_no = u.assy_no ORDER BY assy_no DESC, regdate DESC LIMIT 1), 2)                     AS 自動機組立費
        ,
        Uround((SELECT assy_rate FROM material_cost_header WHERE assy_no = u.assy_no ORDER BY assy_no DESC, regdate DESC LIMIT 1) * (SELECT g_time FROM material_cost_header WHERE assy_no = u.assy_no ORDER BY assy_no DESC, regdate DESC LIMIT 1), 2)                     AS 外注組立費
        ,
        '01111' AS 組立場所
        ,
        'W'     AS 決算区分
        ,
        (SELECT trim(substr(kanryou,3,4)) FROM assembly_schedule WHERE plan_no=(SELECT plan_no FROM material_cost_header WHERE assy_no = u.assy_no ORDER BY assy_no DESC, regdate DESC LIMIT 1))     AS 原価日付
        ,
        (SELECT to_char(regdate, 'YYMMDD') FROM material_cost_header WHERE assy_no = u.assy_no ORDER BY assy_no DESC, regdate DESC LIMIT 1)      AS 追加日付
        ,
        '20'     AS 原価年
        ,
        '20'     AS 追加年
    FROM
          material_cost_header AS u
    LEFT OUTER JOIN
          miitem AS m
    ON (u.assy_no = m.mipn)
    LEFT OUTER JOIN
          material_old_product AS mate
    ON (u.assy_no = mate.assy_no)
    WHERE mate.assy_no IS NULL AND (SELECT sum_price + Uround((m_time + g_time) * assy_rate, 2) + Uround(a_time * a_rate, 2) FROM material_cost_header WHERE assy_no = u.assy_no ORDER BY assy_no DESC, regdate DESC LIMIT 1) IS NOT NULL
    AND trim(substr((SELECT plan_no FROM material_cost_header WHERE assy_no = u.assy_no ORDER BY assy_no DESC, regdate DESC LIMIT 1),1,1)) <> 'Z'
    GROUP BY u.assy_no, m.midsc
    ORDER BY u.assy_no ASC
    OFFSET 0 LIMIT 15000
";
//$query = "
//    SELECT
//        u.assyno                    AS 製品番号
//        ,
//        (SELECT plan_no FROM material_cost_header WHERE assy_no = u.assyno ORDER BY assy_no DESC, regdate DESC LIMIT 1)
//                                    AS 総材料計画
//        ,
//        (SELECT sum_price FROM material_cost_header WHERE assy_no = u.assyno ORDER BY assy_no DESC, regdate DESC LIMIT 1)
//                                    AS 最新総材料費
//        ,
//        (SELECT m_time FROM material_cost_header WHERE assy_no = u.assyno ORDER BY assy_no DESC, regdate DESC LIMIT 1)                     AS 手作業工数
//        ,
//        (SELECT a_time FROM material_cost_header WHERE assy_no = u.assyno ORDER BY assy_no DESC, regdate DESC LIMIT 1)                     AS 自動機工数
//        ,        
//        (SELECT g_time FROM material_cost_header WHERE assy_no = u.assyno ORDER BY assy_no DESC, regdate DESC LIMIT 1)                     AS 外注工数
//        ,
//        Uround((SELECT assy_rate FROM material_cost_header WHERE assy_no = u.assyno ORDER BY assy_no DESC, regdate DESC LIMIT 1) * (SELECT m_time FROM material_cost_header WHERE assy_no = u.assyno ORDER BY assy_no DESC, regdate DESC LIMIT 1), 2)                     AS 手作業組立費
//        ,
//        Uround((SELECT a_rate FROM material_cost_header WHERE assy_no = u.assyno ORDER BY assy_no DESC, regdate DESC LIMIT 1) * (SELECT a_time FROM material_cost_header WHERE assy_no = u.assyno ORDER BY assy_no DESC, regdate DESC LIMIT 1), 2)                     AS 自動機組立費
//        ,
//        Uround((SELECT assy_rate FROM material_cost_header WHERE assy_no = u.assyno ORDER BY assy_no DESC, regdate DESC LIMIT 1) * (SELECT g_time FROM material_cost_header WHERE assy_no = u.assyno ORDER BY assy_no DESC, regdate DESC LIMIT 1), 2)                     AS 外注組立費
//        ,
//        '01111' AS 組立場所
//        ,
//        'W'     AS 決算区分
//        ,
//        (SELECT trim(substr(kanryou,3,4)) FROM assembly_schedule WHERE plan_no=(SELECT plan_no FROM material_cost_header WHERE assy_no = u.assyno ORDER BY assy_no DESC, regdate DESC LIMIT 1))     AS 原価日付
//        ,
//        (SELECT to_char(regdate, 'YYMMDD') FROM material_cost_header WHERE assy_no = u.assyno ORDER BY assy_no DESC, regdate DESC LIMIT 1)      AS 追加日付
//        ,
//        '20'     AS 原価年
//        ,
//        '20'     AS 追加年
//    FROM
//          hiuuri AS u
//    LEFT OUTER JOIN
//          assembly_schedule AS a
//    ON (u.計画番号 = a.plan_no)
//    LEFT OUTER JOIN
//          miitem AS m
//    ON (u.assyno = m.mipn)
//    LEFT OUTER JOIN
//          material_old_product AS mate
//    ON (u.assyno = mate.assy_no)
//    WHERE datatype='1' AND mate.assy_no IS NULL AND (SELECT sum_price + Uround((m_time + g_time) * assy_rate, 2) + Uround(a_time * a_rate, 2) FROM material_cost_header WHERE assy_no = u.assyno ORDER BY assy_no DESC, regdate DESC LIMIT 1) IS NOT NULL
//    AND trim(substr((SELECT plan_no FROM material_cost_header WHERE assy_no = u.assyno ORDER BY assy_no DESC, regdate DESC LIMIT 1),1,1)) <> 'Z'
//    GROUP BY u.assyno, m.midsc
//    ORDER BY u.assyno ASC
//    OFFSET 0 LIMIT 15000
//";
$res = array();
if ( ($rows=getResultTrs($con, $query, $res)) < 1) {
    $log_date = date('Y-m-d H:i:s');            ///// 日報用ログの日時
    fwrite($fpa, "$log_date 総材料費 サマリーの 確定データがありません！ \n");
    fclose($fpa);   ///// 日報用ログファイルのクローズ
    query_affected_trans($con, 'ROLLBACK');
    exit();
} else {
    $fp = fopen(UPLOAD_F1, 'w');     ///// 日報 サマリー ファイルへの書込みでオープン
    for ($i=0; $i<$rows; $i++) {
        //if ($res[$i][16] != 'E') $res[$i][16] = ' ' ;   // 完了フラグ(end_flg)をチェック０が入ってしまう対策
        //$log_record = sprintf("%9s%8s%11.2f%7.3f%7.3f%7.3f%11.2f%11.2f%11.2f%5s%1s%4s%6s%2d%2d\n",
        //    $res[$i][0], $res[$i][1], $res[$i][2], $res[$i][3], $res[$i][4], $res[$i][5], $res[$i][6], $res[$i][7],
        //    $res[$i][8], $res[$i][9], $res[$i][10], $res[$i][11], $res[$i][12], $res[$i][13], $res[$i][14]
        //);
        $log_record = sprintf("%9s%8s%11s%10s%10s%10s%11s%11s%11s%5s%1s%4s%6s%2s%2s\n",
            $res[$i][0], $res[$i][1], $res[$i][2], $res[$i][3], $res[$i][4], $res[$i][5], $res[$i][6], $res[$i][7],
            $res[$i][8], $res[$i][9], $res[$i][10], $res[$i][11], $res[$i][12], $res[$i][13], $res[$i][14]
        );
        //$log_record = sprintf("%9s%8s\n",
        //    $res[$i][0], $res[$i][1]
        //);
        if (fwrite($fp, $log_record) == FALSE) {
            $log_date = date('Y-m-d H:i:s');            ///// 日報用ログの日時
            fwrite($fpa, "$log_date サマリーのUPLOADデータ生成 error \n");
            fclose($fpa);   ///// 日報用ログファイルのクローズ
            query_affected_trans($con, 'ROLLBACK');
            exit();
        }
    }
    //$sql = "
    //    INSERT INTO material_cost_summary_history
    //    SELECT * FROM material_cost_summary {$where}
    //    ;
    //    DELETE FROM material_cost_summary {$where}
    //";
    //query_affected_trans($con, $sql);
}
fclose($fp);   ///// サマリー ファイルのクローズ
$log_date = date('Y-m-d H:i:s');            ///// 日報用ログの日時
fwrite($fpa, "$log_date 総材料費 サマリーデータ $rows 件 \n");



/////////// UPLOADデータ生成 OK
$log_date = date('Y-m-d H:i:s');            ///// 日報用ログの日時
fwrite($fpa, "$log_date 総材料費 のUPLOADデータ生成 OK \n");



////////// FTP 転送開始
// OK NG フラグセット
$ftp_flg = false;
// 総材料費 明細とサマリーの日報データ存在チェック
if (file_exists(UPLOAD_F1)) {
    // コネクションを取る(FTP接続のオープン)
    if ($ftp_stream = ftp_connect(AS400_HOST)) {
        if (ftp_login($ftp_stream, AS400_USER, AS400_PASS)) {
            ///// コントロールファイルのレコードチェック（排他制御）
            if (ftpGetCheckAndExecute($ftp_stream, C_TIGMP, AS_CTIGMP, FTP_ASCII)) {
                $log_date = date('Y-m-d H:i:s');        ///// ログの日時
                fwrite($fpa,"$log_date ftp_get コントロール download OK " . AS_CTIGMP . '→' . C_TIGMP . "\n");
                if (checkControlFile($fpa, C_TIGMP)) {
                    fwrite($fpa,"$log_date コントロールファイルにデータがあるで終了します。\n");
                    ftp_close($ftp_stream);
                    query_affected_trans($con, 'ROLLBACK');
                    fclose($fpa);      ////// 強制終了
                    exit();
                }
            } else {
                $log_date = date('Y-m-d H:i:s');        ///// ログの日時
                fwrite($fpa,"$log_date ftp_get() コントロール error " . AS_CTIGMP . "\n");
                ftp_close($ftp_stream);
                query_affected_trans($con, 'ROLLBACK');
                fclose($fpa);      ////// 強制終了
                exit();
            }
            ///// 総材料費 明細データをUPLOADする
            if (ftpPutCheckAndExecute($ftp_stream, REMOTE_F1, UPLOAD_F1, FTP_ASCII)) {
                $log_date = date('Y-m-d H:i:s');            ///// 日報用ログの日時
                fwrite($fpa,"$log_date 総材料費 明細 ftp_put upload OK " . UPLOAD_F1 . '→' . REMOTE_F1 . "\n");
                $ftp_flg = true;
            } else {
                $log_date = date('Y-m-d H:i:s');            ///// 日報用ログの日時
                fwrite($fpa,"$log_date 総材料費 明細 ftp_put() upload error " . REMOTE_F1 . "\n");
            }
            ///// 総材料費 サマリーデータをUPLOADする
            //if (ftpPutCheckAndExecute($ftp_stream, REMOTE_F2, UPLOAD_F2, FTP_ASCII)) {
            //    $log_date = date('Y-m-d H:i:s');            ///// 日報用ログの日時
            //    fwrite($fpa,"$log_date 総材料費 サマリー ftp_put upload OK " . UPLOAD_F2 . '→' . REMOTE_F2 . "\n");
            //    if ($ftp_flg) $ftp_flg = true; else $ftp_flg = false;
            //} else {
            //    $log_date = date('Y-m-d H:i:s');            ///// 日報用ログの日時
            //    fwrite($fpa,"$log_date 総材料費 サマリー ftp_put() upload error " . REMOTE_F2 . "\n");
            //    $ftp_flg = false;
            //}
        } else {
            $log_date = date('Y-m-d H:i:s');            ///// 日報用ログの日時
            fwrite($fpa,"$log_date 総材料費 ftp_login() error \n");
        }
        ftp_close($ftp_stream);
    } else {
        $log_date = date('Y-m-d H:i:s');            ///// 日報用ログの日時
        fwrite($fpa,"$log_date 総材料費 ftp_connect() error\n");
    }
} else {
    $log_date = date('Y-m-d H:i:s');            ///// 日報用ログの日時
    fwrite($fpa,"$log_date 総材料費 明細がありません。\n");
}



/////////// commit トランザクション終了
if ($ftp_flg) {
    query_affected_trans($con, 'COMMIT');
} else {
    query_affected_trans($con, 'ROLLBACK');
}
fclose($fpa);   ///// 日報用ログファイルのクローズ
exit();



///////////////// AS/400 に旧データがあるかチェックする
function old_data_check($fpa, $dir)
{
    $log_date = date('Y-m-d H:i:s');            ///// 日報用ログの日時
    // FTPのターゲットファイル 上記で指定されている
    // 保存先のディレクトリとファイル名
    define('OLD_DATA', "{$dir}/backup/material_cost_download.txt");   // save file
    
    // コネクションを取る(FTP接続のオープン)
    if ($ftp_stream = ftp_connect(AS400_HOST)) {
        if (ftp_login($ftp_stream, AS400_USER, AS400_PASS)) {
            ///// 発注計画ファイル
            if (ftpGetCheckAndExecute($ftp_stream, OLD_DATA, REMOTE_F1, FTP_ASCII)) {
                fwrite($fpa,"$log_date 旧データチェック用 download OK " . REMOTE_F1 . '→' . OLD_DATA . "\n");
            } else {
                fwrite($fpa,"$log_date ftp_get() error " . REMOTE_F1 . "\n");
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
        fwrite($fpa, "$log_date 総材料費 : 使用端末は {$data}");
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

function ftpPutCheckAndExecute($stream, $as400_file, $local_file, $ftp)
{
    $errorLogFile = '/tmp/as400FTP_Error.log';
    $trySec = 10;
    
    $flg = @ftp_put($stream, $as400_file, $local_file, $ftp);
    if ($flg) return $flg;
    $log_date = date('Y-m-d H:i:s');
    $errorLog = fopen($errorLogFile, 'a');
    fwrite($errorLog, "$log_date ftp_put Error try1 File=>" . __FILE__ . "\n");
    fclose($errorLog);
    sleep($trySec);
    
    $flg = @ftp_put($stream, $as400_file, $local_file, $ftp);
    if ($flg) return $flg;
    $log_date = date('Y-m-d H:i:s');
    $errorLog = fopen($errorLogFile, 'a');
    fwrite($errorLog, "$log_date ftp_put Error try2 File=>" . __FILE__ . "\n");
    fclose($errorLog);
    sleep($trySec);
    
    $flg = @ftp_put($stream, $as400_file, $local_file, $ftp);
    if ($flg) return $flg;
    $log_date = date('Y-m-d H:i:s');
    $errorLog = fopen($errorLogFile, 'a');
    fwrite($errorLog, "$log_date ftp_put Error try3 File=>" . __FILE__ . "\n");
    fclose($errorLog);
    
    return $flg;
}
?>
