#!/usr/local/bin/php
<?php
//////////////////////////////////////////////////////////////////////////////
// 設備稼動管理システム ログファイルローテーション処理                      //
// Copyright (C) 2007 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp           //
// Changed history                                                          //
// 2007/07/26 Created  equipLogRotate.php                                   //
//////////////////////////////////////////////////////////////////////////////
ini_set('error_reporting', E_ALL);          // E_ALL='2047' debug 用
// ini_set('error_reporting', E_STRICT);       // E_STRICT=2048(php5) E_ALL=2047 debug 用
ini_set('implicit_flush', 'off');           // echo print で flush させない(遅くなるため) CLI CGI版

define('LOG_FILE', '/tmp/EquipAutoLogClassMoni.log');

function main()
{
    $rotateName = LOG_FILE . date('.Ymd', time() - 86400);
    rename(LOG_FILE, $rotateName);
    exit();
}

main();

?>
