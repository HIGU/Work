#!/usr/local/bin/php
<?php
//////////////////////////////////////////////////////////////////////////////
// 組立計画データ＠分のAS/400との完全データリンク CHECK DATA UPLOAD   CLI版 //
// Web Server (PHP) ----> AS/400 AS/400のファイルは 権限 = *ALL で作成      //
// Copyright (C) 2007      Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp      //
// Changed history                                                          //
// 2007/05/28 Created  assembly_schedule_checkDataUpLoad.php                //
// 2007/05/29 １ヶ月前→２ヶ月前  ３ヶ月先→５ヶ月先 へ変更                 //
// 2007/07/30 AS/400とのFTP error対応のためftp???CheckAndExecute()関数を追加//
//////////////////////////////////////////////////////////////////////////////
ini_set('error_reporting', E_ALL);          // E_ALL='2047' debug 用
ini_set('implicit_flush', '0');             // echo print で flush させない(遅くなるため)
// ini_set('max_execution_time', 1200);        // 最大実行時間=20分 CLI版は関係ない

$currentFullPathName = realpath(dirname(__FILE__));
require_once ("{$currentFullPathName}/../../function.php");

// sleep(3);      // industry_hourly_cli.phpから実行なのでコメント

$log_date = date('Y-m-d H:i:s');            // 日報用ログの日時
$fpLog = fopen('/tmp/nippo.log', 'a');      // 日報用ログファイルへの書込みでオープン

fwrite($fpLog, "$log_date 組立日程計画＠生産引当分データ抜出し＆UPLOAD開始 \n");

/////////// 開始日の算出
$year = date('Y'); $month = date('m');
if ($month == 1) {
    $month = 12;
    $year -= 1;
} else {
    $month -= 2;    // 2ヶ月前
    $month = sprintf('%02d', $month);
}
$startDate = ($year . $month . '01');
/////////// 終了日の算出
$year = date('Y'); $month = date('m');
$month += 5;        // 5ヶ月先
if ($month > 12) {
    $month -= 12;
    $year  += 1;
}
$month = sprintf('%02d', $month);
$endDate = ($year . $month . '01');

$query = "
    SELECT
        plan_no, parts_no, syuka, chaku, kanryou, plan, cut_plan, kansei, nyuuko, sei_kubun, line_no, p_kubun, assy_site, dept
    FROM
        assembly_schedule
    WHERE
        kanryou >= {$startDate} AND kanryou < {$endDate} AND plan_no LIKE '@%' AND (plan-cut_plan-kansei) > 0
        -- AND assy_site='01111'
";
if ( ($rows=getResult2($query, $res)) <= 0) {
    $log_date = date('Y-m-d H:i:s');
    fwrite($fpLog, "$log_date 組立日程計画 対照データがありません。$startDate 〜 $endDate \n");
    fclose($fpLog);
    exit();
}
define('UPLOADF', "{$currentFullPathName}/backup/W#MIPPLS.TXT");  // 組立日程＠生産引当ファイル Check File
$fp = fopen(UPLOADF, 'w');
for ($i=0; $i<$rows; $i++) {
    $data = sprintf('%8s_%9s_%8s_%8s_%8s_%8s_%8s_%8s_%2s_%1s_%4s_%1s_%5s_%1s',
        $res[$i][0], $res[$i][1], $res[$i][2], $res[$i][3], $res[$i][4], $res[$i][5], $res[$i][6],
        $res[$i][7], $res[$i][8], $res[$i][9], $res[$i][10], $res[$i][11], $res[$i][12], $res[$i][13]);
    fwrite($fp,"{$data}\n");
}
fclose($fp);

// 排他制御用コントロールファイル
define('CMIPPL', 'UKWLIB/C#MIPPL');        // 組立日程計画＠生産引当コントロール
// 保存先のディレクトリとファイル名
define('C_MIPPL', "{$currentFullPathName}/backup/C#MIPPL.TXT");

// FTPのリモートファイル
define('AS400_FILE', 'UKWLIB/W#MIPPLS');    // 組立日程計画＠生産引当チェックファイル Remote File
// FTPのローカルファイル
define('LOCAL_FILE', UPLOADF);              // 組立日程計画＠生産引当チェックファイル Local File

// コネクションを取る(FTP接続のオープン)
if ($ftp_stream = ftp_connect(AS400_HOST)) {
    if (ftp_login($ftp_stream, AS400_USER, AS400_PASS)) {
        ///// 組立日程計画＠生産引当コントロールファイルチェック
        if (ftpGetCheckAndExecute($ftp_stream, C_MIPPL, CMIPPL, FTP_ASCII)) {
            $log_date = date('Y-m-d H:i:s');        ///// ログの日時
            fwrite($fpLog,"$log_date ftp_get コントロール download OK " . CMIPPL . '→' . C_MIPPL . "\n");
            if (checkControlFile($fpLog, C_MIPPL)) {
                fwrite($fpLog,"$log_date コントロールファイルにデータがあるので終了します。\n");
                ftp_close($ftp_stream);
                fclose($fpLog);      ////// 強制終了
                exit();
            }
        } else {
            $log_date = date('Y-m-d H:i:s');        ///// ログの日時
            fwrite($fpLog,"$log_date ftp_get() コントロール error " . CMIPPL . "\n");
            ftp_close($ftp_stream);
            fclose($fpLog);      ////// 強制終了
            exit();
        }
        ///// 組立日程＠チェックファイルのアップロード
        if (ftpPutCheckAndExecute($ftp_stream, AS400_FILE, LOCAL_FILE, FTP_ASCII)) {
            // echo 'ftp_put UPLOAD OK ', LOCAL_FILE, '→', AS400_FILE, "\n";
            $log_date = date('Y-m-d H:i:s');            // 日報用ログの日時
            fwrite($fpLog,"$log_date 組立日程計画＠生産引当分ftp_put UPLOAD OK 件数:{$rows} " . LOCAL_FILE . '→' . AS400_FILE . "\n");
        } else {
            // echo 'ftp_put() error ', AS400_FILE, "\n";
            $log_date = date('Y-m-d H:i:s');            // 日報用ログの日時
            fwrite($fpLog,"$log_date ftp_put() error 件数:{$rows} " . AS400_FILE . "\n");
        }
    } else {
        // echo "ftp_login() error \n";
        $log_date = date('Y-m-d H:i:s');            // 日報用ログの日時
        fwrite($fpLog,"$log_date ftp_login() error 件数:{$rows} \n");
    }
    ftp_close($ftp_stream);
} else {
    // echo "ftp_connect() error --> 組立日程＠チェック自動更新処理\n";
    $log_date = date('Y-m-d H:i:s');            // 日報用ログの日時
    fwrite($fpLog,"$log_date ftp_connect() error 件数:{$rows} --> 組立日程＠チェック自動更新処理\n");
}


fclose($fpLog);      ////// ログ書込み終了

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
