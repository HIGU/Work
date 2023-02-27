#!/usr/local/bin/php
<?php
//////////////////////////////////////////////////////////////////////////////
// 日報(daily)処理  (各スクリプトをrequireする親)                           //
// Copyright (C) 2004-2016 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp      //
// Changed history                                                          //
// 2004/10/15 Created  daily_cli.php                                        //
//            アイテムマスター ＣＣ部品マスター Ａ伝情報  在庫経歴・マスター//
// 2004/12/08 MICCC を追加                                                  //
// 2004/12/13 #!/usr/local/bin/php-5.0.2-cli → php (内部は5.0.3RC2)に変更  //
// 2005/02/07 在庫経歴・在庫マスターの前日分の更新処理追加                  //
// 2009/12/28 製品グループマスター関連の更新を追加                     大谷 //
// 2010/01/14 組立工程マスターの自動登録を追加                         大谷 //
// 2010/01/19 メールを分かりやすくする為、日付・リンク先等を追加       大谷 //
// 2011/11/17 仕切単価の自動更新を追加                                 大谷 //
// 2013/01/29 不適合処置連絡書の自動更新を追加                         大谷 //
// 2015/02/13 有給取得情報の自動更新を追加                             大谷 //
// 2016/09/15 日次部品棚卸金額の更新を追加                             大谷 //
// 2017/06/14 A伝詳細情報daily_aden_details_cli.phpを追加              大谷 //
//////////////////////////////////////////////////////////////////////////////
ini_set('error_reporting', E_ALL);          // E_ALL='2047' debug 用
ini_set('implicit_flush', '0');             // echo print で flush させない(遅くなるためcli版)
    // 現在はCLI版のdefault='1', SAPI版のdefault='0'になっている。CLI版のみスクリプトから変更出来る。
// ini_set('display_errors','1');              // Error 表示 ON debug 用 リリース後コメント
// ini_set('max_execution_time', 1200);        // 最大実行時間=20分
echo "************************************************************************\n";
echo "日報(daily)処理\n";
echo "/home/www/html/tnk-web/system/daily/daily_cli.php\n";
echo "************************************************************************\n";

echo "------------------------------------------------------------------------\n";

/******** アイテムマスターの更新 *********/
echo "アイテムマスターの更新\n";
echo "/home/www/html/tnk-web/system/daily/daily_miitem_cli.php\n";
echo `/home/www/html/tnk-web/system/daily/daily_miitem_cli.php`;
echo "------------------------------------------------------------------------\n";

/******** ＣＣ部品ＴＮＫＣＣ部品のテーブル更新 *********/
echo "ＣＣ部品ＴＮＫＣＣ部品のテーブル更新\n";
echo "/home/www/html/tnk-web/system/daily/daily_miccc_ftp_cli.php\n";
echo `/home/www/html/tnk-web/system/daily/daily_miccc_ftp_cli.php`;
echo "------------------------------------------------------------------------\n";

/******** Ａ伝情報の更新 *********/
echo "Ａ伝情報の更新\n";
echo "/home/www/html/tnk-web/system/daily/daily_aden_cli.php\n";
echo `/home/www/html/tnk-web/system/daily/daily_aden_cli.php`;
echo "------------------------------------------------------------------------\n";

/******** Ａ伝詳細情報の更新 *********/
echo "Ａ伝詳細情報の更新\n";
echo "/home/www/html/tnk-web/system/daily/daily_aden_details_cli.php\n";
echo `/home/www/html/tnk-web/system/daily/daily_aden_details_cli.php`;
echo "------------------------------------------------------------------------\n";

/******** 在庫経歴・在庫マスター前日分の更新(残業時間18:00以降の対応) *********/
echo "在庫経歴・在庫マスター前日分の更新(残業時間18:00以降の対応)\n";
echo "/home/www/html/tnk-web/system/daily/parts_stock_history_master_ftp_cli3.php\n";
echo `/home/www/html/tnk-web/system/daily/parts_stock_history_master_ftp_cli3.php`;
echo "------------------------------------------------------------------------\n";

/******** 日次部品棚卸金額の更新 *********/
echo "日次部品棚卸金額の更新\n";
echo "/home/www/html/tnk-web/system/daily/daily_stock_cli.php\n";
echo `/home/www/html/tnk-web/system/daily/daily_stock_cli.php`;
echo "------------------------------------------------------------------------\n";

/******** 製品グループコードの更新 *********/
echo "製品グループコードの更新\n";
echo "/home/www/html/tnk-web/system/daily/product_code_get_ftp.php\n";
echo `/home/www/html/tnk-web/system/daily/product_code_get_ftp.php`;
echo "------------------------------------------------------------------------\n";

/******** 製品グループコードマスターの更新 *********/
echo "製品グループコードマスターの更新\n";
echo "/home/www/html/tnk-web/system/daily/product_code_master_get_ftp.php\n";
echo `/home/www/html/tnk-web/system/daily/product_code_master_get_ftp.php`;
echo "------------------------------------------------------------------------\n";

/******** 組立工程マスターの自動登録 *********/
echo "組立工程マスターの自動登録\n";
echo "/home/www/html/tnk-web/system/assembly_time/assembly_process_master_cli_once.php\n";
echo `/home/www/html/tnk-web/system/assembly_time/assembly_process_master_cli_once.php`;
echo "------------------------------------------------------------------------\n";

/******** 仕切単価の更新 *********/
echo "仕切単価の更新\n";
echo "/home/www/html/tnk-web/system/daily/sales_price_update_cli.php\n";
echo `/home/www/html/tnk-web/system/daily/sales_price_update_cli.php`;
echo "------------------------------------------------------------------------\n";

/******** 不適合処置連絡書の更新 *********/
echo "不適合処置連絡書の更新\n";
echo "/home/www/html/tnk-web/system/daily/claim_disposal_details_update_cli.php\n";
echo `/home/www/html/tnk-web/system/daily/claim_disposal_details_update_cli.php`;
echo "------------------------------------------------------------------------\n";

/******** 単価レート区分マスターの更新 *********/
echo "単価レート区分マスターの更新\n";
echo "/home/www/html/tnk-web/system/daily/parts_ratediv_master_update_ftp.php\n";
echo `/home/www/html/tnk-web/system/daily/parts_ratediv_master_update_ftp.php`;
echo "------------------------------------------------------------------------\n";

/******** 部品単価レート経歴マスターの更新 *********/
echo "部品単価レート経歴マスターの更新\n";
echo "/home/www/html/tnk-web/system/daily/parts_rate_history_update_ftp.php\n";
echo `/home/www/html/tnk-web/system/daily/parts_rate_history_update_ftp.php`;
echo "------------------------------------------------------------------------\n";

/******** 有給休暇取得情報の更新 *********/
echo "有給休暇取得情報の更新\n";
echo "/home/www/html/tnk-web/system/daily/daily_yukyu_cli.php\n";
echo `/home/www/html/tnk-web/system/daily/daily_yukyu_cli.php`;
echo "------------------------------------------------------------------------\n";

/******** 月初売上予定の保管 *********/
echo "月初売上予定の保管\n";
echo "/home/www/html/tnk-web/system/daily/sales_actual_set_plan.php\n";
echo `/home/www/html/tnk-web/system/daily/sales_actual_set_plan.php`;
echo "------------------------------------------------------------------------\n";
?>
