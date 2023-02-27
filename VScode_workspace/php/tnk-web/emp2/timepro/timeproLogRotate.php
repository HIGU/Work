#!/usr/local/bin/php
<?php
//////////////////////////////////////////////////////////////////////////////
// タイムカードの打刻時間(出勤・退勤) ログファイルローテーション処理        //
// Copyright (C) 2007 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp           //
// Changed history                                                          //
// 2007/11/07 Created  timeproLogRotate.php                                 //
//////////////////////////////////////////////////////////////////////////////
// ini_set('error_reporting', E_ALL);          // E_ALL='2047' debug 用
// ini_set('error_reporting', E_STRICT);       // E_STRICT=2048(php5) E_ALL=2047 debug 用
ini_set('implicit_flush', 'off');           // echo print で flush させない(遅くなるため) CLI CGI版

define('LOG_FILE', '/tmp/timepro.log');

function main()
{
    if (filesize(LOG_FILE) >= 2500000) {
        $rotateName = LOG_FILE . date('.Ymd', time() - 86400);
        rename(LOG_FILE, $rotateName);
    }
    exit();
}

main();

?>
