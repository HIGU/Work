#!/usr/local/bin/php
<?php
//////////////////////////////////////////////////////////////////////////////
// 引当データのAS/400との完全データリンクのため CHECK DATA UPLOAD   CLI版   //
// Web Server (PHP) ----> AS/400 AS/400のファイルは 権限 = *ALL で作成      //
// Copyright (C) 2007      Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp      //
// Changed history                                                          //
// 2007/02/16 Created  order_plan_checkDataUpLoad.php                       //
// 2007/02/19 order_q → zan_q になっているミスを修正(正式はorder_qを使用)  //
//////////////////////////////////////////////////////////////////////////////
ini_set('error_reporting', E_ALL);          // E_ALL='2047' debug 用
ini_set('implicit_flush', '0');             // echo print で flush させない(遅くなるため)
// ini_set('max_execution_time', 1200);        // 最大実行時間=20分 CLI版は関係ない
require_once ('/home/www/html/tnk-web/function.php');
sleep(3);      // cronで実行なので他のプロセスとの競合を考慮して３０秒遅延する。

$log_date = date('Y-m-d H:i:s');            // 日報用ログの日時
$fpLog = fopen('/tmp/nippo.log', 'a');      // 日報用ログファイルへの書込みでオープン

$query = "
    SELECT sei_no, order5, plan.parts_no, plan.order_q, utikiri, nyuko
    FROM order_plan AS plan LEFT OUTER JOIN order_process USING(sei_no)
    WHERE  zan_q>0 AND order_process.order_no IS NULL
    ORDER BY sei_no ASC
";
if ( ($rows=getResult2($query, $res)) <= 0) {
    fwrite($fpLog,"$log_date 発注計画チェック用データがありませんでした。\n");
    fclose($fpLog);      ////// ログ書込み終了
    exit();
}
define('UPLOADF', '/home/www/html/tnk-web/system/backup/W#MIOPLS.TXT');  // 発注計画ファイル Check File
$fp = fopen(UPLOADF, 'w');
for ($i=0; $i<$rows; $i++) {
    $data = sprintf('%7s_%5s_%9s_%8s_%8s_%8s', $res[$i][0], $res[$i][1], $res[$i][2], $res[$i][3], $res[$i][4], $res[$i][5]);
    fwrite($fp,"{$data}\n");
}
fclose($fp);

// FTPのリモートファイル
define('AS400_FILE', 'UKWLIB/W#MIOPLS');    // 発注計画チェックファイル Remote File
// FTPのローカルファイル
define('LOCAL_FILE', UPLOADF);              // 発注計画チェックファイル Local File

// コネクションを取る(FTP接続のオープン)
if ($ftp_stream = ftp_connect(AS400_HOST)) {
    if (ftp_login($ftp_stream, AS400_USER, AS400_PASS)) {
        ///// 引当チェックファイルのアップロード
        if (ftp_put($ftp_stream, AS400_FILE, LOCAL_FILE, FTP_ASCII)) {
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


?>
