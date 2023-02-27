#!/usr/local/bin/php
<?php
//////////////////////////////////////////////////////////////////////////////
// 外出情報メール送信 cron.d tnk_daily 処理で実行                           //
// 月～金の指定時にメール送信 外出【未承認】があれば管理部長へ定期メール    //
// Copyright (C) 2021-2021 Norihisa.Ohya norihisa_ooya@nitto-kohki.co.jp    //
// Changed history                                                          //
// 2022/05/26 Created   outing_mail.php                                     //
// 2022/06/09 当日～2週間 を 当日～ に変更                                  //
//////////////////////////////////////////////////////////////////////////////
//ini_set('error_reporting', E_STRICT);       // E_STRICT=2048(php5) E_ALL=2047 debug 用
//ini_set('display_errors', '1');             // Error 表示 ON debug 用 リリース後コメント
ini_set('implicit_flush', 'off');           // echo print で flush させない(遅くなるため)
require_once ('/var/www/html/function.php');

$log_date = date('Y-m-d H:i:s');        ///// 日報用ログの日時
$fpa = fopen('/tmp/nippo.log', 'a');    ///// 日報用ログファイルへの書込みでオープン
$fpb = fopen('/tmp/as400get_ftp_re.log', 'a');    ///// 日報データ再取得用ログファイルへの書込みでオープン
fwrite($fpb, "定時間外作業申告承認待ち情報メール送信\n");
fwrite($fpb, "/var/www/html/system/daily/over_time_work_report_admit_mail.php\n");
echo "/var/www/html/system/daily/over_time_work_report_admit_mail.php\n";

if ($con = db_connect()) {
    query_affected_trans($con, "begin");
} else {
    echo "$log_date 定時間外作業申告承認待ち情報メール送信 db_connect() error \n";
    fwrite($fpa,"$log_date 定時間外作業申告承認待ち情報メール送信 db_connect() error \n");
    fwrite($fpb,"$log_date 定時間外作業申告承認待ち情報メール送信 db_connect() error \n");
    exit();
}

/////////// begin トランザクション開始

// 当日～2週間の外出【未承認】日付情報取得
//    WHERE  str_time>=CURRENT_DATE AND str_time<=CURRENT_DATE+interval '2 week'
// 当日～の外出【未承認】日付情報取得
$query = sprintf( "
    SELECT to_char(str_time, 'YYYY/MM/DD') AS date
    FROM   meeting_schedule_header
    WHERE  str_time>=CURRENT_DATE
       AND room_no=2200
    ORDER BY date
");
$res_date = array();
if( getResult2($query, $res_date) < 1 ) exit(); // 取得不可なら終了。

// 管理部長のUIDを取得
$query = sprintf( "
    SELECT ud.uid
    FROM   user_detailes ud, section_master sm, position_master pm
    WHERE  ud.sid=sm.sid AND ud.retire_date is null AND ud.uid!='000000' AND ud.pid=pm.pid
       AND sm.section_name='管理部' AND (pm.position_name='部長' OR pm.position_name='部長代理')
");
$res = array();
if( getResult2($query, $res) < 1 ) exit(); // 取得不可なら終了。
$to_uid = $res[0][0];

// 管理部長の不在チェック
$query = sprintf( "
    SELECT uid
    FROM   working_hours_report_data_new
    WHERE  uid='$to_uid' AND working_date=to_char(CURRENT_DATE,'YYYYMMDD')
       AND (absence!='00' OR str_time='0000' OR end_time!='0000')
");
$res = array();
if( getResult2($query, $res) > 0 ) {
    ////////// 総務課長のUIDを取得
    $query = sprintf( "
        SELECT ud.uid
        FROM   user_detailes ud, section_master sm, position_master pm
        WHERE  ud.sid=sm.sid AND ud.retire_date is null AND ud.uid!='000000' AND ud.pid=pm.pid
           AND sm.section_name='管理部 総務課' AND (pm.position_name='課長' OR pm.position_name='課長代理')
    ");
    $res = array();
    if( getResult2($query, $res) < 1 ) exit(); // 取得不可なら終了。
    $to_uid = $res[0][0];
}

// 承認者の名前取得
$query = "SELECT trim(name) FROM user_detailes WHERE uid='$to_uid'";
$res = array();
if( getResult2($query, $res) < 1 ) exit(); // 取得不可なら終了。
$to_name = $res[0][0]; // 名前

// 承認者のメールアドレス取得
//$to_uid = "300667"; // 強制変更 ※リリース時は、コメント化
$query = "SELECT trim(mailaddr) FROM user_detailes LEFT OUTER JOIN user_master USING(uid) WHERE uid='$to_uid'";
$res = array();
if( getResult2($query, $res) < 1 ) exit(); // 取得不可なら終了。
$to_addres = $res[0][0];

// メールタイトル作成
$attenSubject = "【定期メール：外出情報】 $to_name 様　確認下さい。";

// メール内容作成
$message  = "この案内は、外出【未承認】情報があるため送信されたものです。\n\n";
$message .= "対象日\n";
$max = count($res_date);
for($r=0; $r<$max; $r++) {
    $message .= "　{$res_date[$r][0]}\n";
}
$message .= "\n";
$message .= "下記、URLを開きカレンダーより対象日を選択、外出の承認をお願いいたします。\n";
$message .= "http://10.1.3.252/meeting/meeting_schedule_Main.php?calUid={$to_uid}\n\n";
$message .= "以上。";
$add_head = "";

// 承認者へメール送信
mb_send_mail($to_addres, $attenSubject, $message, $add_head);

/////////// commit トランザクション終了
query_affected_trans($con, "commit");
// echo $query . "\n";
fclose($fpa);      ////// 日報用ログ書込み終了
fwrite($fpb, "------------------------------------------------------------------------\n");
fclose($fpb);      ////// 日報データ再取得用ログ書込み終了

exit();

?>
