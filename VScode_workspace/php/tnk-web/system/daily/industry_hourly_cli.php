#!/usr/local/bin/php
<?php
//////////////////////////////////////////////////////////////////////////////
// 生産データの６０分毎 → ３２分 のAS/400とデータリンク処理                //
// Copyright (C) 2005-2015 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp      //
// Changed history                                                          //
// 2005/05/09 Created  industry_hourly_cli.php                              //
//            発注工程明細ファイル・発注計画ファイル                        //
// 2005/05/11 コマンドライン出力用の echo 文をコメントアウト                //
// 2005/05/30 order_data_daily_ftp_cli.php の更新で deadlockを起すため待機  //
// 2005/08/11 会社が休業日なら実行せず終了するロジック追加                  //
// 2006/05/08 home/www/ → /home/www/ に修正                                //
// 2007/02/19 発注計画の差異データ更新処理を追加(発注残があり明細が無いもの)//
// 2007/02/20 sleep(1800)を追加                                             //
// 2007/04/26 上記のsleep以下を外して購買納期回答データの同期実行を追加     //
// 2007/04/27 購買納期回答の同期を２回行うように変更sleep(600)１０分後      //
// 2007/05/07 機械運転日報の自動更新を追加。それに伴い上記の600→720(12分)へ//
// 2007/05/15 組立完成経歴の当日分の自動登録 追加                           //
// 2007/05/16 組立工数の当日分の自動登録 追加                               //
// 2007/05/22 組立日程計画の＠生産引当計画のチェック更新を追加              //
// 2007/05/29 上記の組立日程計画＠をデータアップロードでAS側チェックに変更  //
// 2010/11/08 総材料費の自動更新を追加                                 大谷 //
// 2010/11/12 総材料費の自動更新を一日一回に変更の為コメント化         大谷 //
// 2015/03/20 有給残情報の更新を追加(全員分で20分掛かる為)             大谷 //
//            Cron内で実行に変更                                            //
//////////////////////////////////////////////////////////////////////////////
ini_set('error_reporting', E_ALL);          // E_ALL='2047' debug 用
ini_set('implicit_flush', 'off');           // echo print で flush させない(遅くなるためcli版)
// ini_set('display_errors','1');              // Error 表示 ON debug 用 リリース後コメント
// ini_set('max_execution_time', 1200);        // 最大実行時間=20分

require_once ('/var/www/html/tnk_func.php');
if (day_off(mktime())) exit;                // 会社が休業日なら終了
/////////// 日報データ再取得のログファイルの中身を初期化（通常処理でも再取得用ログにデータが出来るため）
$log_name_a = '/tmp/industry_hourly_test.log';
$fpa = fopen($log_name_a, 'a');    // 全てのログ w=過去のログを消す
/////////// データベースとコネクション確立
if ( !($con = funcConnect()) ) {
    fwrite($fpa, "$log_date funcConnect() error \n");
    fclose($fpa);      ////// 日報用ログ書込み終了
    exit;
}
fwrite($fpa, "************************************************************************\n");
fwrite($fpa, "cron 1時間おきの実行 テストログ\n");
fwrite($fpa, "/var/www/html/system/daily/industry_hourly_cli.php\n");
fwrite($fpa, "************************************************************************\n");

fwrite($fpa, "------------------------------------------------------------------------\n");

fclose($fpa);      ////// 日報データ再取得のログファイル初期化完了
// echo "------------------------------------------------------------------------\n";

sleep(50);  // order_data_daily_ftp_cli.php の更新で deadlockを起すため待機させる
// 上記は現在 order_process_lock ファイルで Exclusive しているので外してもOK

/******** 発注工程明細のテーブル更新 *********/
`/var/www/html/system/daily/order_process_ftp_cli.php`;
echo `/var/www/html/system/daily/order_process_ftp_cli.php`;
echo "------------------------------------------------------------------------\n";

/******** 発注計画のテーブル更新 *********/
`/var/www/html/system/daily/order_plan_get_ftp_cli.php`;
echo `/var/www/html/system/daily/order_plan_get_ftp_cli.php`;
echo "------------------------------------------------------------------------\n";


/******** 発注計画の差異データ更新 *********/
/******** AS/400でチェック済みのデータをもらい強制的に更新処理 *********/
`/var/www/html/system/daily/order_plan_get_ftp2.php`;
echo `/var/www/html/system/daily/order_plan_get_ftp2.php`;
echo "------------------------------------------------------------------------\n";

/******** Web Server から チェック用データ送信 *********/
`/var/www/html/system/daily/order_plan_checkDataUpLoad.php`;
echo `/var/www/html/system/daily/order_plan_checkDataUpLoad.php`;
echo "------------------------------------------------------------------------\n";

/******** 購買納期回答データの同期実行(排他制御あり) *********/
`/var/www/html/system/daily/order_delivery_answer_get_ftp.php`;
echo `/var/www/html/system/daily/order_delivery_answer_get_ftp.php`;
echo "------------------------------------------------------------------------\n";

/******** 機械運転日報の自動更新 実行(排他制御あり) *********/
`/var/www/html/system/equip_report/equip_report--as400-upload_cli.php`;
echo `/var/www/html/system/equip_report/equip_report--as400-upload_cli.php`;
echo "------------------------------------------------------------------------\n";

/******** 総材料費の自動更新 実行(排他制御あり) *********/
//`/var/www/html/system/material_cost/material_cost--as400-upload_cli.php`;
//echo `/var/www/html/system/material_cost/material_cost--as400-upload_cli.php`;
//echo "------------------------------------------------------------------------\n";

// /******** 組立日程計画＠生産引当計画のチェック更新 *********/
// `/var/www/html/system/assembly_schedule/assembly_schedule_checkUpdate.php`;
/******** 組立日程計画＠生産引当計画のチェックデータアップロードとダウンロード後の更新 *********/
`/var/www/html/system/assembly_schedule/assembly_schedule_checkDataDownLoadUpdate.php`;
`/var/www/html/system/assembly_schedule/assembly_schedule_checkDataUpLoad.php`;
echo `/var/www/html/system/assembly_schedule/assembly_schedule_checkDataDownLoadUpdate.php`;
echo "------------------------------------------------------------------------\n";
echo `/var/www/html/system/assembly_schedule/assembly_schedule_checkDataUpLoad.php`;
echo "------------------------------------------------------------------------\n";

sleep(720);  // 12分まって２回目を行う cronが32分に１回が前提

/******** 購買納期回答データの同期実行(排他制御あり) *********/
`/var/www/html/system/daily/order_delivery_answer_get_ftp.php`;
echo `/var/www/html/system/daily/order_delivery_answer_get_ftp.php`;
echo "------------------------------------------------------------------------\n";

/******** 機械運転日報の自動更新 実行(排他制御あり) *********/
`/var/www/html/system/equip_report/equip_report--as400-upload_cli.php`;
echo `/var/www/html/system/equip_report/equip_report--as400-upload_cli.php`;
echo "------------------------------------------------------------------------\n";

/******** 総材料費の自動更新 実行(排他制御あり) *********/
//`/var/www/html/system/material_cost/material_cost--as400-upload_cli.php`;
//echo `/var/www/html/system/material_cost/material_cost--as400-upload_cli.php`;
//echo "------------------------------------------------------------------------\n";

/******** 組立完成経歴の当日分の自動登録 *********/
echo `/var/www/html/system/assembly_completion/assembly_completion_history_ftp_cli.php`;
echo `/var/www/html/system/assembly_completion/assembly_completion_history_ftp_cli.php`;
echo "------------------------------------------------------------------------\n";

/******** 組立工数の当日分の自動登録 *********/
echo `/var/www/html/system/assembly_time/assembly_time_header_ftp_cli.php`;
echo `/var/www/html/system/assembly_time/assembly_time_header_ftp_cli.php`;
echo "------------------------------------------------------------------------\n";

?>
