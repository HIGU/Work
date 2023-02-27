#!/usr/local/bin/php
<?php
//////////////////////////////////////////////////////////////////////////////
//      旧タイプ #!/usr/local/bin/php-4.3.8-cgi -q                          //
// 日報データ 自動FTP Download  cron で処理用       AS400復旧時の処理版     //
// Webの早朝起動時 as400get_ftp.phpとinventory_average_summary_ftp_cli.php  //
// 及びdaily_cli.phpを実行しているがASに問題があった場合手動で実行する      //
// 必ず次の日に実行すること（ASには前日分のワークファイルしかない為）       //
// AS/400 ----> Web Server (PHP)                                            //
// Copyright (C) 2009-2020 Norihisa.Ohya norihisa_ooya@nitto-kohki.co.jp    //
// Changed history                                                          //
// 2009/12/18 Created  as400get_ftp_re.php(as400get_ftp.php)                //
// 2009/12/25 製品グループマスター関連の更新を追加                          //
// 2010/01/14 組立工程マスターの自動登録を追加                              //
// 2010/01/19 メールを分かりやすくする為、タイトル等を追加             大谷 //
// 2011/07/15 損益予測の自動計算処理を追加                             大谷 //
// 2011/07/19 損益予測のリンクが間違っていたので訂正                   大谷 //
// 2015/03/12 daily_cli.phpの実行を解除                                     //
//            daily_cli.phpは別メニューで実行                               //
// 2020/08/18 総材料費の自動登録をしないように修正                     大谷 //
//////////////////////////////////////////////////////////////////////////////
ini_set('error_reporting', E_ALL);      // E_ALL='2047' debug 用
ini_set('implicit_flush', 'off');       // echo print で flush させない(遅くなるため)
// ini_set('max_execution_time', 1200);    // 最大実行時間=20分
require_once ('/home/www/html/tnk-web/function.php');

$log_date = date('Y-m-d H:i:s');            // 日報用ログの日時
$log_name_a = '/tmp/as400get_ftp_re.log';
$fpa = fopen($log_name_a, 'w+');    // 全てのログ w=過去のログを消す

fwrite($fpa, "************************************************************************\n");
fwrite($fpa, "日報データ 再取得 Download\n");
fwrite($fpa, "/home/www/html/tnk-web/system/daily/as400get_ftp_re.php\n");
fwrite($fpa, "************************************************************************\n");

/////////// データベースとコネクション確立
if ( !($con = funcConnect()) ) {
    fwrite($fpa, "$log_date funcConnect() error \n");
    fclose($fpa);      ////// 日報用ログ書込み終了
    exit;
}

fwrite($fpa, "------------------------------------------------------------------------\n");

echo "************************************************************************\n";
echo "日報データ 再取得 Download\n";
echo "/home/www/html/tnk-web/system/daily/as400get_ftp_re.php\n";
echo "************************************************************************\n";

echo "------------------------------------------------------------------------\n";

/******** 売上データの更新(前日分)・売上未検収データ *********/
echo `/home/www/html/tnk-web/system/daily/sales_get_ftp.php`;
echo "------------------------------------------------------------------------\n";

/******** 日程計画データの更新 *********/
echo `/home/www/html/tnk-web/system/daily/plan_get_ftp.php`;
echo "------------------------------------------------------------------------\n";

/******** 単価経歴データの更新 *********/
echo `/home/www/html/tnk-web/system/daily/parts_cost_ftp.php`;
echo "------------------------------------------------------------------------\n";

/******** 引当部品データの更新 *********/
echo `/home/www/html/tnk-web/system/daily/allocated_parts_ftp.php`;
echo "------------------------------------------------------------------------\n";

/******** 製造用部品表 構成データの更新 *********/
echo `/home/www/html/tnk-web/system/daily/parts_configuration_ftp.php`;
echo "------------------------------------------------------------------------\n";

/******** 内作指示のヘッダーと明細データの更新 *********/
echo `/home/www/html/tnk-web/system/daily/equip_work_inst_ftp.php`;
echo "------------------------------------------------------------------------\n";

/******** 発注の標準工程データの更新 *********/
echo "発注標準工程データの更新\n";
echo `/home/www/html/tnk-web/system/daily/order_process_ftp_cli.php`;
echo "------------------------------------------------------------------------\n";

/******** 注文書発行データの更新 *********/
echo `/home/www/html/tnk-web/system/daily/order_data_ftp_cli.php`;
echo "------------------------------------------------------------------------\n";

/******** 総材料費の自動登録 *********/
/*
echo "総材料費の自動登録\n";
echo `/home/www/html/tnk-web/industry/material/material_auto_registry_cli.php`;
echo "------------------------------------------------------------------------\n";
*/

/******** 部品売上の材料費の自動登録 *********/
echo "部品売上の材料費の自動登録\n";
echo `/home/www/html/tnk-web/system/daily/parts_material_auto_registry_cli.php`;
echo "------------------------------------------------------------------------\n";

/******** 購買納期回答データの同期実行(排他制御あり) *********/
`/home/www/html/tnk-web/system/daily/order_delivery_answer_get_ftp.php`;

/******** 組立完成経歴の前日分の自動登録及びAS/400側クリアー処理 *********/
echo "組立完成経歴の自動登録及びAS/400側クリアー処理\n";
echo `/home/www/html/tnk-web/system/assembly_completion/assembly_completion_history_ftp_cli_once.php`;
echo "------------------------------------------------------------------------\n";

/******** 組立工数の前日分の自動登録及びAS/400側クリアー処理 *********/
echo "組立工数の自動登録及びAS/400側クリアー処理\n";
echo `/home/www/html/tnk-web/system/assembly_time/assembly_time_header_ftp_cli_once.php`;
echo "------------------------------------------------------------------------\n";

/******** 資材在庫サマリーの自動更新処理 *********/
echo "資材在庫サマリーの自動更新処理実行\n";
echo `/home/www/html/tnk-web/system/inventory_average/inventory_average_summary_ftp_cli.php`;
echo "------------------------------------------------------------------------\n";

/******** 損益予測の自動計算処理 *********/
echo "損益予測の自動計算処理実行\n";
echo `/home/www/html/tnk-web/system/daily/profit_loss_estimate_cal.php`;
echo "------------------------------------------------------------------------\n";

/******** 日報(daily)処理  *********/
//echo "日報(daily)処理\n";
//echo `/home/www/html/tnk-web/system/daily/daily_cli.php`;
//echo "------------------------------------------------------------------------\n";

/******** メール送信  *********/
if (rewind($fpa)) {
    $to = 'tnksys@nitto-kohki.co.jp, usoumu@nitto-kohki.co.jp, norihisa_ooya@nitto-kohki.co.jp';
    $subject = "日報データの再取得結果 {$log_date}";
    $msg = fread($fpa, filesize($log_name_a));
    $header = "From: tnksys@nitto-kohki.co.jp\r\nReply-To: tnksys@nitto-kohki.co.jp\r\n";
    mb_send_mail($to, $subject, $msg, $header);
}

fclose($fpa);      ////// 日報データ再取得用ログ書込み終了
?>
