#!/usr/local/bin/php
<?php
//////////////////////////////////////////////////////////////////////////////
//      旧タイプ #!/usr/local/bin/php-4.3.8-cgi -q                          //
// 日報データ 自動FTP Download  cron で処理用       コマンドライン版        //
// AS/400 ----> Web Server (PHP)                                            //
// Copyright (C) 2002-2020 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp      //
// Changed history                                                          //
// 2002/03/11 Created  as400get_ftp.php                                     //
// 2002/11/28 テスト版で debug 済 のため正式にリリース                      //
// 2003/05/30 組立日程計画データの日報処理を追加                            //
// 2003/06/06 AS/400のTIPPLNP等のトランザクションファイルはキー無しの物理   //
//             ファイルを頭から順番に読込書込しないといけない重複レコードが //
//             あるため最新に保てない。                                     //
// 2003/06/20 W#MIITEMをFTP_BINARYでDownloadしたが半角カナ(EBCDIC)の変換が要//
// 2003/11/14 php → php-4.3.4-cgi へ変更(明確にcgiを使うことが分かるように)//
// 2003/11/17 cgi → cli版へ変更出来るように requier_once を絶対指定へ      //
// 2004/04/21 FTPのターゲットとローカルファイルをdefine()で統一しbackup/ へ //
// 2004/04/30 FTP項目 売上未検収データを追加 FTP Download のみ              //
// 2004/05/19 単価経歴ファイル 日報処理追加                                 //
// 2004/05/21 引当部品ファイル 日報処理追加                                 //
// 2004/05/25 製造用部品表経歴ファイル 日報処理追加                         //
// 2004/06/07 php-4.3.6-cgi -q → php-4.3.7-cgi -q  バージョンアップに伴う  //
// 2004/06/30 内作指示・工程明細ファイル 日報処理追加                       //
// 2004/09/14 発注の標準工程ファイル・注文書発行ファイルの日報処理追加      //
// 2004/11/18 php-5.0.2-cliへバージョンアップ *シェルスクリプトに対応に変更 //
// 2004/12/13 #!/usr/local/bin/php-5.0.2-cli → php (内部は5.0.3RC2)に変更  //
// 2005/06/02 echo '発注標準工程データの更新';を追加(内部でechoをオフにした)//
//            総材料費の自動登録を追加 (売上更新後実行)                     //
// 2006/02/10 部品売上の材料費の自動登録を追加                              //
// 2007/04/27 購買納期回答の同期を前日分の更新処理(スクリプトは同じ) 追加   //
// 2007/05/15 組立完成経歴の前日分の自動登録及びAS/400側クリアー処理 追加   //
// 2007/05/16 組立工数の前日分の自動登録及びAS/400側クリアー処理 追加       //
// 2007/05/18 資材在庫サマリーの自動更新処理 追加                           //
// 2009/12/18 日報データ再取得のログファイルの中身を初期化するよう変更 大谷 //
// 2009/12/25 製品グループマスター関連の更新を追加                     大谷 //
// 2009/12/28 製品グループマスター関連の更新をdaily_cli.phpへ移動      大谷 //
// 2010/01/19 メールを分かりやすくする為、タイトル等を追加             大谷 //
//            購買納期回答へのリンクにechoを追加                       大谷 //
// 2010/01/22 総材料費・部品のリンクをメールに表示するように変更       大谷 //
// 2011/07/15 損益予測の自動計算処理を追加                             大谷 //
// 2011/07/19 損益予測のリンクが間違っていたので訂正                   大谷 //
// 2011/07/21 損益予測が自動実行されないため訂正                       大谷 //
// 2011/11/24 損益予測をdaily_cliへ移動しようとしたがそのままに        大谷 //
// 2016/09/15 バケマス用データの自動更新を追加                         大谷 //
// 2017/10/12 特注・標準の配賦率計算処理を追加しようとしたが重いので   大谷 //
//            早い時間に直接実行するよう変更                           大谷 //
// 2020/03/30 総材料費の自動登録をしないように変更                     大谷 //
//////////////////////////////////////////////////////////////////////////////
ini_set('error_reporting', E_ALL);      // E_ALL='2047' debug 用
ini_set('implicit_flush', 'off');       // echo print で flush させない(遅くなるため)
// ini_set('max_execution_time', 1200);    // 最大実行時間=20分
require_once ('/home/www/html/tnk-web/function.php');

/////////// 日報データ再取得のログファイルの中身を初期化（通常処理でも再取得用ログにデータが出来るため）
$log_name_a = '/tmp/as400get_ftp_re.log';
$fpa = fopen($log_name_a, 'w+');    // 全てのログ w=過去のログを消す
/////////// データベースとコネクション確立
if ( !($con = funcConnect()) ) {
    fwrite($fpa, "$log_date funcConnect() error \n");
    fclose($fpa);      ////// 日報用ログ書込み終了
    exit;
}
fwrite($fpa, "************************************************************************\n");
fwrite($fpa, "日報データ 自動FTP Download\n");
fwrite($fpa, "/home/www/html/tnk-web/system/daily/as400get_ftp.php\n");
fwrite($fpa, "************************************************************************\n");

fwrite($fpa, "------------------------------------------------------------------------\n");

fclose($fpa);      ////// 日報データ再取得のログファイル初期化完了
////////////////////////////////////////////////////////////////////////////////////////////////////////
echo "************************************************************************\n";
echo "日報データ 自動FTP Download\n";
echo "/home/www/html/tnk-web/system/daily/as400get_ftp.php\n";
echo "************************************************************************\n";

echo "------------------------------------------------------------------------\n";

/******** 売上データの更新(前日分)・売上未検収データ *********/
echo "売上データの更新(前日分)・売上未検収データ\n";
echo `/home/www/html/tnk-web/system/daily/sales_get_ftp.php`;
echo "------------------------------------------------------------------------\n";

/******** 日程計画データの更新 *********/
echo "日程計画データの更新\n";
echo `/home/www/html/tnk-web/system/daily/plan_get_ftp.php`;
echo "------------------------------------------------------------------------\n";

/******** 単価経歴データの更新 *********/
echo "単価経歴データの更新\n";
echo `/home/www/html/tnk-web/system/daily/parts_cost_ftp.php`;
echo "------------------------------------------------------------------------\n";

/******** 引当部品データの更新 *********/
echo "引当部品データの更新\n";
echo `/home/www/html/tnk-web/system/daily/allocated_parts_ftp.php`;
echo "------------------------------------------------------------------------\n";

/******** 製造用部品表 構成データの更新 *********/
echo "製造用部品表 構成データの更新\n";
echo `/home/www/html/tnk-web/system/daily/parts_configuration_ftp.php`;
echo "------------------------------------------------------------------------\n";

/******** 内作指示のヘッダーと明細データの更新 *********/
echo "内作指示のヘッダーと明細データの更新\n";
echo `/home/www/html/tnk-web/system/daily/equip_work_inst_ftp.php`;
echo "------------------------------------------------------------------------\n";

/******** 発注の標準工程データの更新 *********/
echo "発注標準工程データの更新\n";
echo `/home/www/html/tnk-web/system/daily/order_process_ftp_cli.php`;
echo "------------------------------------------------------------------------\n";

/******** 注文書発行データの更新 *********/
echo "注文書発行データの更新\n";
echo `/home/www/html/tnk-web/system/daily/order_data_ftp_cli.php`;
echo "------------------------------------------------------------------------\n";

/******** 総材料費の自動登録 *********/
/*  2020/03/30 自動登録なし
echo "総材料費の自動登録\n";
echo "/home/www/html/tnk-web/industry/material/material_auto_registry_cli.php\n";
echo `/home/www/html/tnk-web/industry/material/material_auto_registry_cli.php`;
echo "------------------------------------------------------------------------\n";
*/

/******** 部品売上の材料費の自動登録 *********/
echo "部品売上の材料費の自動登録\n";
echo "/home/www/html/tnk-web/system/daily/parts_material_auto_registry_cli.php\n";
echo `/home/www/html/tnk-web/system/daily/parts_material_auto_registry_cli.php`;
echo "------------------------------------------------------------------------\n";

/******** 購買納期回答データの同期実行(排他制御あり) *********/
echo "購買納期回答データの同期実行(排他制御あり)\n";
echo `/home/www/html/tnk-web/system/daily/order_delivery_answer_get_ftp.php`;
echo "------------------------------------------------------------------------\n";

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

/******** バケマス用データの自動更新処理 *********/
echo "バケマス用データの自動更新処理実行\n";
echo `/home/www/html/tnk-web/system/daily/daily_backet_ftp_cli.php`;
echo "------------------------------------------------------------------------\n";

/******** 損益予測の自動計算処理 *********/
echo "損益予測の自動計算処理実行\n";
echo `/home/www/html/tnk-web/system/daily/profit_loss_estimate_cal.php`;
echo "------------------------------------------------------------------------\n";

/******** 特注標準配賦率の自動計算処理 *********/
/*
echo "特注標準配賦率の自動計算処理実行\n";
echo `/home/www/html/tnk-web/system/daily/ctoku_allocation_cal.php`;
echo "------------------------------------------------------------------------\n";
*/
?>
