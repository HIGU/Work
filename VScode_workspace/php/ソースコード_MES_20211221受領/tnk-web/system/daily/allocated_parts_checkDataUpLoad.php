#!/usr/local/bin/php
<?php
//////////////////////////////////////////////////////////////////////////////
// 引当データのAS/400との完全データリンクのため CHECK DATA UPLOAD   CLI版   //
// Web Server (PHP) ----> AS/400 AS/400のファイルは 権限 = *ALL で作成      //
// Copyright (C) 2007      Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp      //
// Changed history                                                          //
// 2007/02/09 Created  allocated_parts_checkDataUpLoad.php                  //
// 2007/02/14 ログ日時を直前で取得し、UPLOAD件数をログに書込み追加          //
// 2007/02/28 補用引当のメンテナンス対応のため以下のSQL文をコメント         //
//         AND (plan_no LIKE 'C%' OR plan_no LIKE 'L%' OR plan_no LIKE '@%')//
// 2007/07/30 AS/400とのFTP error 対応のため ftpCheckAndExecute()関数を追加 //
//////////////////////////////////////////////////////////////////////////////
ini_set('error_reporting', E_ALL);          // E_ALL='2047' debug 用
ini_set('implicit_flush', '0');             // echo print で flush させない(遅くなるため)
// ini_set('max_execution_time', 1200);        // 最大実行時間=20分 CLI版は関係ない
require_once ('/home/www/html/tnk-web/function.php');
sleep(3);      // cronで実行なので他のプロセスとの競合を考慮して３０秒遅延する。

$log_date = date('Y-m-d H:i:s');            // 日報用ログの日時
$fpLog = fopen('/tmp/nippo.log', 'a');      // 日報用ログファイルへの書込みでオープン

$query = "
    SELECT plan_no, parts_no, assy_no, unit_qt, allo_qt, sum_qt, assy_str FROM allocated_parts
    WHERE parts_no NOT LIKE '9%' AND (allo_qt-sum_qt) > 0
        -- AND (plan_no LIKE 'C%' OR plan_no LIKE 'L%' OR plan_no LIKE '@%')
    ORDER BY parts_no DESC, (allo_qt-sum_qt) DESC LIMIT 50000 OFFSET 0
";
//        (plan_no LIKE 'C%' OR plan_no LIKE 'L%' OR plan_no LIKE '@%')
//    ORDER BY parts_no DESC, (allo_qt-sum_qt) DESC LIMIT 100 OFFSET 40650
if ( ($rows=getResult2($query, $res)) <= 0) {
    fwrite($fpLog,"$log_date 引当チェック用データがありませんでした。\n");
    fclose($fpLog);      ////// ログ書込み終了
    exit();
}
define('UPLOADF', '/home/www/html/tnk-web/system/backup/W#MIALLS.TXT');  // 引当部品ファイル Check File
$fp = fopen(UPLOADF, 'w');
for ($i=0; $i<$rows; $i++) {
    $data = sprintf('%8s_%9s_%9s_%8s_%7s_%7s_%8s', $res[$i][0], $res[$i][1], $res[$i][2], $res[$i][3], $res[$i][4], $res[$i][5], $res[$i][6]);
    fwrite($fp,"{$data}\n");
}
fclose($fp);

// FTPのリモートファイル
define('AS400_FILE', 'UKWLIB/W#MIALLS');    // 引当チェックファイル Remote File
// FTPのローカルファイル
define('LOCAL_FILE', UPLOADF);              // 引当チェックファイル Local File

// コネクションを取る(FTP接続のオープン)
if ($ftp_stream = ftp_connect(AS400_HOST)) {
    if (ftp_login($ftp_stream, AS400_USER, AS400_PASS)) {
        ///// 引当チェックファイルのアップロード
        if (ftpCheckAndExecute($ftp_stream, AS400_FILE, LOCAL_FILE, FTP_ASCII)) {
            // echo 'ftp_put UPLOAD OK ', LOCAL_FILE, '→', AS400_FILE, "\n";
            $log_date = date('Y-m-d H:i:s');            // 日報用ログの日時
            fwrite($fpLog,"$log_date ftp_put UPLOAD OK 件数:{$rows} " . LOCAL_FILE . '→' . AS400_FILE . "\n");
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
    // echo "ftp_connect() error --> 引当チェック自動更新処理\n";
    $log_date = date('Y-m-d H:i:s');            // 日報用ログの日時
    fwrite($fpLog,"$log_date ftp_connect() error 件数:{$rows} --> 引当チェック自動更新処理\n");
}


fclose($fpLog);      ////// ログ書込み終了

exit();

function ftpCheckAndExecute($stream, $as400_file, $local_file, $ftp)
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
