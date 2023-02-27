#!/usr/local/bin/php
<?php
//////////////////////////////////////////////////////////////////////////////
// 引当データのリアルタイム更新処理 日報(daily)処理                         //
// Copyright (C) 2007      Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp      //
// Changed history                                                          //
// 2007/02/13 Created  daily_allocated_cli.php                              //
//////////////////////////////////////////////////////////////////////////////
ini_set('error_reporting', E_ALL);          // E_ALL='2047' debug 用
ini_set('implicit_flush', '0');             // echo print で flush させない(遅くなるためcli版)
    // 現在はCLI版のdefault='1', SAPI版のdefault='0'になっている。CLI版のみスクリプトから変更出来る。
// ini_set('display_errors','1');              // Error 表示 ON debug 用 リリース後コメント
// ini_set('max_execution_time', 1200);        // 最大実行時間=20分

$flag = substr(date('i'), 1, 1);            // crond で５分刻みにやる前提
    //echo "------------------------------------------------------------------------\n";
if ($flag == '0') {     // 00分/10/20/30...
    /******** トランザクションファイル W#TIALLCの更新 *********/
    echo `/var/www/html/system/daily/allocated_parts_realTime.php`;
    //echo "------------------------------------------------------------------------\n";
} else {                // 05分/15/25/35...
    /******** AS/400でチェック済みのデータをもらい強制的に更新処理 *********/
    echo `/var/www/html/system/daily/allocated_parts_ftp2.php`;
    //echo "------------------------------------------------------------------------\n";
    
    /******** Web Server から チェック用データ送信 *********/
    echo `/var/www/html/system/daily/allocated_parts_checkDataUpLoad.php`;
    //echo "------------------------------------------------------------------------\n";
}
?>
