#!/usr/local/bin/php
<?php
//////////////////////////////////////////////////////////////////////////////
// 生産データの２分毎のAS/400とデータリンク処理                             //
// Copyright (C) 2007      Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp      //
// Changed history                                                          //
// 2007/08/29 Created  industry_two_minutes_cli.php                         //
//            industry/order/order_data_difference_update_cron.php          //
//                                               注文書発行データ差分の更新 //
//            industry/order/order_data_truncation_update_cron.php          //
//                                             注文書打切りデータ差分の更新 //
//            industry/order/order_data_bun_ftp_cli.php                     //
//                                     受付・検収の分納伝票(当日注文書発行) //
//////////////////////////////////////////////////////////////////////////////
ini_set('error_reporting', E_ALL);          // E_ALL='2047' debug 用
ini_set('implicit_flush', 'off');           // echo print で flush させない(遅くなるためcli版)

require_once ('/var/www/html/tnk_func.php');
if (day_off(mktime())) exit;                // 会社が休業日なら終了

sleep(1);  // 負荷分散

/******** 注文書発行データ差分の更新 *********/
`/var/www/html/industry/order/order_data_difference_update_cron.php`;
// echo `/var/www/html/industry/order/order_data_difference_update_cron.php`;
// echo "------------------------------------------------------------------------\n";

/******** 注文書打切りデータ差分の更新 *********/
`/var/www/html/industry/order/order_data_truncation_update_cron.php`;
// echo `/var/www/html/industry/order/order_data_truncation_update_cron.php`;
// echo "------------------------------------------------------------------------\n";

/******** 受付・検収の分納伝票(当日注文書発行) 更新 *********/
`/var/www/html/industry/order/order_data_bun_ftp_cli.php`;
// echo `/var/www/html/industry/order/order_data_bun_ftp_cli.php`;
// echo "------------------------------------------------------------------------\n";

?>
