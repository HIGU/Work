#!/usr/local/bin/php
<?php
//////////////////////////////////////////////////////////////////////////////
// 製品マスター関連 一括自動FTP Download 管理メニュー手動処理用             //
// AS/400 ----> Web Server (PHP)                                            //
// Copyright (C) 2009 Norihisa.Ohya norihisa_ooya@nitto-kohki.co.jp         //
// Changed histoy                                                           //
// 2009/12/25 Created  product_master_get_ftp.php                           //
// 2009/12/28 テスト用に一時呼出先を変更して戻した                     大谷 //
//////////////////////////////////////////////////////////////////////////////
ini_set('error_reporting', E_ALL);      // E_ALL='2047' debug 用
ini_set('implicit_flush', 'off');       // echo print で flush させない(遅くなるため)
// ini_set('max_execution_time', 1200);    // 最大実行時間=20分
require_once ('/var/www/html/function.php');

echo "------------------------------------------------------------------------\n";

/******** 製品グループコードの更新 *********/
echo `/var/www/html/system/daily/product_code_get_ftp.php`;
echo "------------------------------------------------------------------------\n";

/******** 製品グループコードマスターの更新 *********/
echo `/var/www/html/system/daily/product_code_master_get_ftp.php`;
echo "------------------------------------------------------------------------\n";

?>
