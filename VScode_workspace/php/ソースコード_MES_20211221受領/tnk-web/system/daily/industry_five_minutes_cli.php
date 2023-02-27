#!/usr/local/bin/php
<?php
//////////////////////////////////////////////////////////////////////////////
// 生産データの５分毎のAS/400とデータリンク処理                             //
// Copyright (C) 2007-2016 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp      //
// Changed history                                                          //
// 2007/08/29 Created  industry_five_minutes_cli.php                        //
//            system/daily/daily_allocated_cli.php 引当DATAリアルタイム更新 //
//              (system/daily/allocated_parts_realTime.php)                 //
//              (system/daily/allocated_parts_ftp2.php)                     //
//              (system/daily/allocated_parts_checkDataUpLoad.php)          //
//            industry/order/order_data_daily_ftp_cli.php 注文書発行データ  //
//            system/daily/plan_get_ftp.php 日程計画ファイル                //
//            emp/timepro/timePro_update_cli.php タイムカードのデータ更新   //
// 2010/11/08 ﾃｽﾄ用で総材料費の自動更新を追加・削除                    大谷 //
// 2016/09/30 バケットマスターのデータ更新を追加                       大谷 //
//////////////////////////////////////////////////////////////////////////////
ini_set('error_reporting', E_ALL);          // E_ALL='2047' debug 用
ini_set('implicit_flush', 'off');           // echo print で flush させない(遅くなるためcli版)

require_once ('/home/www/html/tnk-web/tnk_func.php');
if (day_off(mktime())) exit;                // 会社が休業日なら終了

sleep(2);  // 負荷分散

/******** 引当データリアルタイム更新 *********/
`/home/www/html/tnk-web/system/daily/daily_allocated_cli.php`;
// echo `/home/www/html/tnk-web/system/daily/daily_allocated_cli.php`;
// echo "------------------------------------------------------------------------\n";

/******** 注文書発行データ更新 *********/
`/home/www/html/tnk-web/industry/order/order_data_daily_ftp_cli.php`;
// echo `/home/www/html/tnk-web/industry/order/order_data_daily_ftp_cli.php`;
// echo "------------------------------------------------------------------------\n";

/******** 日程計画ファイル更新 *********/
`/home/www/html/tnk-web/system/daily/plan_get_ftp.php`;
// echo `/home/www/html/tnk-web/system/daily/plan_get_ftp.php`;
// echo "------------------------------------------------------------------------\n";

/******** タイムカードのデータ更新 *********/
`/home/www/html/tnk-web/emp/timepro/timePro_update_cli.php`;
// echo `/home/www/html/tnk-web/emp/timepro/timePro_update_cli.php`;
// echo "------------------------------------------------------------------------\n";

/******** バケットマスターのデータ更新 *********/
`/home/www/html/tnk-web/system/daily/daily_backet_ftp_cli.php`;
// echo `/home/www/html/tnk-web/system/daily/daily_backet_ftp_cli.php`;
// echo "------------------------------------------------------------------------\n";

?>
