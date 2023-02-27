#!/usr/local/bin/php
<?php
//////////////////////////////////////////////////////////////////////////////
// 長期滞留部品の日報処理３年/５年/７年前の入庫品が在庫になっている物を抽出 //
// Copyright (C) 2006-2006 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp      //
// Changed history                                                          //
// 2006/03/31 Created  long_holding_parts_cli.php                           //
//////////////////////////////////////////////////////////////////////////////
ini_set('error_reporting', E_ALL);          // E_ALL='2047' debug 用
ini_set('implicit_flush', '0');             // echo print で flush させない(遅くなるため) CLI版
// ini_set('max_execution_time', 1200);        // 最大実行時間=20分(CLI版以外)
require_once ('/var/www/html/function.php');

$log_date = date('Y-m-d H:i:s');            // 日報用ログの日時
$log_name = '/tmp/nippo.log';
$fp = fopen($log_name, 'a+');               // ログ

/////////// データベースとコネクション確立
if ( !($con = funcConnect()) ) {
    fwrite($fp, "$log_date funcConnect() error \n");
    fclose($fp);      ////// 日報用ログ書込み終了
    exit;
}

///// ここで未登録のリストをDBより取得して配列に格納する
///// 配列のフィールドは assy_no, plan_no, 売上日
$date1 = (date('Y') - 3) . date('md');  // 3年前
$date2 = (date('Y') - 5) . date('md');  // 5年前
$date3 = (date('Y') - 7) . date('md');  // 7年前

///// 配列データから INSERT INTO table SELECT 条件 を実行
$regist1 = 0;
$regist2 = 0;
$regist3 = 0;
/////////// begin トランザクション開始
query_affected_trans($con, 'begin');
/////////// 前日のデータを削除
if (query_affected_trans($con, 'DELETE FROM long_holding_parts_work1') < 0) {   // 更新用クエリーの実行
    query_affected_trans($con, 'rollback');         // トランザクションのロールバック
    $log_date = date('Y-m-d H:i:s');    // 日報用ログの日時
    fwrite($fp, "{$log_date} DELETE FROM long_holding_parts_work1 に失敗！\n");
    fclose($fp);      ////// 日報用ログ書込み終了
    exit;
}
if (query_affected_trans($con, 'DELETE FROM long_holding_parts_work2') < 0) {   // 更新用クエリーの実行
    query_affected_trans($con, 'rollback');         // トランザクションのロールバック
    $log_date = date('Y-m-d H:i:s');    // 日報用ログの日時
    fwrite($fp, "{$log_date} DELETE FROM long_holding_parts_work2 に失敗！\n");
    fclose($fp);      ////// 日報用ログ書込み終了
    exit;
}
if (query_affected_trans($con, 'DELETE FROM long_holding_parts_work3') < 0) {   // 更新用クエリーの実行
    query_affected_trans($con, 'rollback');         // トランザクションのロールバック
    $log_date = date('Y-m-d H:i:s');    // 日報用ログの日時
    fwrite($fp, "{$log_date} DELETE FROM long_holding_parts_work3 に失敗！\n");
    fclose($fp);      ////// 日報用ログ書込み終了
    exit;
}
/////////// 本日分の更新
$query = "
    INSERT INTO long_holding_parts_work1
    SELECT * FROM long_holding_parts({$date1}, '%', 0)
";
if ( ($regist1=query_affected_trans($con, $query)) < 0) {   // 更新用クエリーの実行
    query_affected_trans($con, 'rollback');         // トランザクションのロールバック
    $log_date = date('Y-m-d H:i:s');    // 日報用ログの日時
    fwrite($fp, "{$log_date} INSERT INTO long_holding_parts_work1 SELECT に失敗！\n");
    fclose($fp);      ////// 日報用ログ書込み終了
    exit;
}
$log_date = date('Y-m-d H:i:s');    // 日報用ログの日時
fwrite($fp, "{$log_date} long_holding_parts_work1 に {$regist1} 件 自動抽出しました。\n");

$query = "
    INSERT INTO long_holding_parts_work2
    SELECT * FROM long_holding_parts({$date2}, '%', 0)
";
if ( ($regist2=query_affected_trans($con, $query)) < 0) {   // 更新用クエリーの実行
    query_affected_trans($con, 'rollback');         // トランザクションのロールバック
    $log_date = date('Y-m-d H:i:s');    // 日報用ログの日時
    fwrite($fp, "{$log_date} INSERT INTO long_holding_parts_work2 SELECT に失敗！\n");
    fclose($fp);      ////// 日報用ログ書込み終了
    exit;
}
$log_date = date('Y-m-d H:i:s');    // 日報用ログの日時
fwrite($fp, "{$log_date} long_holding_parts_work2 に {$regist2} 件 自動抽出しました。\n");

$query = "
    INSERT INTO long_holding_parts_work3
    SELECT * FROM long_holding_parts({$date3}, '%', 0)
";
if ( ($regist3=query_affected_trans($con, $query)) < 0) {   // 更新用クエリーの実行
    query_affected_trans($con, 'rollback');         // トランザクションのロールバック
    $log_date = date('Y-m-d H:i:s');    // 日報用ログの日時
    fwrite($fp, "{$log_date} INSERT INTO long_holding_parts_work3 SELECT に失敗！\n");
    fclose($fp);      ////// 日報用ログ書込み終了
    exit;
}
$log_date = date('Y-m-d H:i:s');    // 日報用ログの日時
fwrite($fp, "{$log_date} long_holding_parts_work3 に {$regist3} 件 自動抽出しました。\n");

/////////// commit トランザクションのコミット
query_affected_trans($con, 'commit');
fclose($fp);        ////// 日報用ログ書込み終了
exit();


///// 以下は将来のために残す
if (rewind($fp)) {
    $to = 'tnksys@nitto-kohki.co.jp, usoumu@nitto-kohki.co.jp';
    $subject = "長期滞留品の自動抽出結果 {$log_date}";
    $msg = fread($fp, filesize($log_name));
    $header = "From: tnksys@nitto-kohki.co.jp\r\nReply-To: tnksys@nitto-kohki.co.jp\r\n";
    mb_send_mail($to, $subject, $msg, $header);
}
?>
