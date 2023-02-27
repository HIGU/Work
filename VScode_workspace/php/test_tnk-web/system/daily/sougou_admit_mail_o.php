#!/usr/local/bin/php
<?php
//////////////////////////////////////////////////////////////////////////////
// 総合届承認待ち情報メール送信 cron.d tnk_daily 処理で実行                 //
// 月～金の○時にメール送信 テスト用プログラム                              //
// 現在自分にのみメール送信                                                 //
// Copyright (C) 2020-2020 Norihisa.Ohya norihisa_ooya@nitto-kohki.co.jp    //
// Changed history                                                          //
// 2020/09/24 Created   sougou_admit_mail.php                               //
// 2021/02/17 本文の中に承認ページのアドレスを追加                          //
//////////////////////////////////////////////////////////////////////////////
//ini_set('error_reporting', E_STRICT);       // E_STRICT=2048(php5) E_ALL=2047 debug 用
//ini_set('display_errors', '1');             // Error 表示 ON debug 用 リリース後コメント
ini_set('implicit_flush', 'off');           // echo print で flush させない(遅くなるため)
require_once ('/var/www/html/function.php');

$log_date = date('Y-m-d H:i:s');        ///// 日報用ログの日時
$fpa = fopen('/tmp/nippo.log', 'a');    ///// 日報用ログファイルへの書込みでオープン
$fpb = fopen('/tmp/as400get_ftp_re.log', 'a');    ///// 日報データ再取得用ログファイルへの書込みでオープン
fwrite($fpb, "総合届承認待ち情報メール送信\n");
fwrite($fpb, "/var/www/html/system/daily/sougou_admit_mail.php\n");
echo "/var/www/html/system/daily/sougou_admit_mail.php\n";

if ($con = db_connect()) {
    query_affected_trans($con, "begin");
} else {
    echo "$log_date 総合届承認待ち情報メール送信 db_connect() error \n";
    fwrite($fpa,"$log_date 総合届承認待ち情報メール送信 db_connect() error \n");
    fwrite($fpb,"$log_date 総合届承認待ち情報メール送信 db_connect() error \n");
    exit();
}

/////////// 日付データの取得
$target_ym   = date('Ym');          //201710
$b_target_ym = $target_ym - 100;    //201610
$today       = date('Ymd');         //20171012
$b_today     = $today - 10000;      //20161012

/////////// begin トランザクション開始
$query = sprintf("SELECT DISTINCT admit_status FROM sougou_deteils WHERE admit_status ='300144'");
$res   = array();
$field = array();
if (($rows = getResultWithField3($query, $field, $res)) <= 0) {
    exit();
} else {
    $num = count($field);       // フィールド数取得
    for ($r=0; $r<$rows; $r++) {
        $query_t = "SELECT 
                                count(admit_status) as t_ken
                          FROM sougou_deteils ";
        $search_t = "WHERE admit_status='{$res[$r][0]}'";
        $query_t = sprintf("$query_t %s", $search_t);     // SQL query 文の完成
        $res_t   = array();
        $field_t = array();
        $res_sum_t = array();
        if (getResult($query_t, $res_sum_t) <= 0) {
            exit();
        } else {
            $t_ken     = $res_sum_t[0]['t_ken'];
            $_SESSION['u_t_ken']  = $t_ken;
            if ($t_ken>0) {
                $query_m = "SELECT trim(name), trim(mailaddr)
                                FROM
                                    user_detailes
                                LEFT OUTER JOIN
                                    user_master USING(uid)
                                ";
                //$search_m = "WHERE uid='300144'";
                // 上はテスト用 強制的に自分にメールを送る
                $search_m = "WHERE uid='{$res[$r][0]}'";
                $query_m = sprintf("$query_m %s", $search_m);     // SQL query 文の完成
                $res_m   = array();
                $field_m = array();
                $res_sum_m = array();
                if (getResult($query_m, $res_sum_m) <= 0) {
                    exit();
                } else {
                    $sendna = $res_sum_m[0][0];
                    $mailad = $res_sum_m[0][1];
                    $_SESSION['u_mailad']  = $mailad;
                    $to_addres = $mailad;
                    $add_head = "";
                    $attenSubject = "宛先： {$sendna} 様 総合届承認待ちのお知らせ";
                    $message   = "{$sendna} 様\n\n";
                    $message  .= "総合届の承認待ちが{$t_ken}件あります。\n\n";
                    //テスト用 下に変更すること
                    //$message  = "総合届の承認待ちが{$t_ken}件あります。\n\n";
                    $message .= "総合届の承認処理をお願いします。\n\n";
                    // 承認ページのアドレス(Uid)を表示、クリックで承認ページへ
                    $message .= "http://masterst/per_appli/in_sougou_admit/sougou_admit_Main.php?calUid=";
                    $message .= $res[$r][0];
                    if (mb_send_mail($to_addres, $attenSubject, $message, $add_head)) {
                        // 出席者へのメール送信履歴を保存
                        //$this->setAttendanceMailHistory($serial_no, $atten[$i]);
                    }
                    ///// Debug
                    //if ($cancel) {
                    //    if ($i == 0) mb_send_mail('tnksys@nitto-kohki.co.jp', $attenSubject, $message, $add_head);
                    //}
                }
            } else {
                
            }
        }
    }
}


/////////// commit トランザクション終了
query_affected_trans($con, "commit");
// echo $query . "\n";
fclose($fpa);      ////// 日報用ログ書込み終了
fwrite($fpb, "------------------------------------------------------------------------\n");
fclose($fpb);      ////// 日報データ再取得用ログ書込み終了

exit();

?>
