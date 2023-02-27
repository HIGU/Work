#!/usr/local/bin/php
<?php
//////////////////////////////////////////////////////////////////////////////
// 定時間外作業申告残業結果報告情報メール送信 cron.d tnk_daily 処理で実行   //
// 月〜金の指定時にメール送信 全体版 残業結果報告情報を承認者へ定期メール   //
// Copyright (C) 2021-2021 Norihisa.Ohya norihisa_ooya@nitto-kohki.co.jp    //
// Changed history                                                          //
// 2021/10/06 Created   over_time_work_report_input_mail.php                //
//////////////////////////////////////////////////////////////////////////////
//ini_set('error_reporting', E_STRICT);       // E_STRICT=2048(php5) E_ALL=2047 debug 用
//ini_set('display_errors', '1');             // Error 表示 ON debug 用 リリース後コメント
ini_set('implicit_flush', 'off');           // echo print で flush させない(遅くなるため)
require_once ('/home/www/html/tnk-web/function.php');

$log_date = date('Y-m-d H:i:s');        ///// 日報用ログの日時
$fpa = fopen('/tmp/nippo.log', 'a');    ///// 日報用ログファイルへの書込みでオープン
$fpb = fopen('/tmp/as400get_ftp_re.log', 'a');    ///// 日報データ再取得用ログファイルへの書込みでオープン
fwrite($fpb, "定時間外作業申告残業結果報告情報メール送信\n");
fwrite($fpb, "/home/www/html/tnk-web/system/daily/over_time_work_report_admit_mail.php\n");
echo "/home/www/html/tnk-web/system/daily/over_time_work_report_admit_mail.php\n";

if ($con = db_connect()) {
    query_affected_trans($con, "begin");
} else {
    echo "$log_date 定時間外作業申告残業結果報告情報メール送信 db_connect() error \n";
    fwrite($fpa,"$log_date 定時間外作業申告残業結果報告情報メール送信 db_connect() error \n");
    fwrite($fpb,"$log_date 定時間外作業申告残業結果報告情報メール送信 db_connect() error \n");
    exit();
}

/////////// begin トランザクション開始
// 工場長、部長、課長
$where = "(ud.pid=110)";
$where = "(ud.pid=47 OR ud.pid=70 OR ud.pid=95)";
$where = "(ud.pid=46 OR ud.pid=50)";
$where = "((ud.pid=110) OR (ud.pid=47 OR ud.pid=70 OR ud.pid=95) OR (ud.pid=46 OR ud.pid=50))";

// 指定長の uid と act_id 取得
$query = "
            SELECT          uid, ct.act_id, pid, trim(name)
            FROM            user_detailes   AS ud
            LEFT OUTER JOIN cd_table        AS ct   USING(uid)
            WHERE           retire_date IS NULL AND $where
         ";
$res_list = array();
if( ($rows_list = getResult($query, $res_list)) <= 0) exit(); // 取得不可なら終了。

for( $r=0; $r<$rows_list; $r++ ) {
    // 条件作成
    if( $res_list[$r][1] == 600 ) {  // 工場長
        if( $res_list[$r][0] == '012394' ) {  // 副工場長
            $deploy = "(deploy='製造部 製造１課' OR deploy='製造部 製造２課')";
        } else {
            $deploy = "(deploy IS NOT NULL)";
        }
    } else if( $res_list[$r][1] == 610 ) {  // 管理部
        $deploy = "(deploy='総務課' OR deploy='商品管理課')";
    } else if( $res_list[$r][1] == 605 || $res_list[$r][1] == 650 || $res_list[$r][1] == 651 || $res_list[$r][1] == 660 ) { // ＩＳＯ事務局 管理部 総務課 総務 財務
        $deploy = "(deploy='総務課')";
    } else if( $res_list[$r][1] == 670 ) {  // 管理部 商品管理課
        $deploy = "(deploy='商品管理課')";
    } else if( $res_list[$r][1] == 501 ) {  // 技術部
        $deploy = "(deploy='品質保証課' OR deploy='技術課')";
    } else if( $res_list[$r][1] == 174 || $res_list[$r][1] == 517 || $res_list[$r][1] == 537 || $res_list[$r][1] == 581 ) { // 技術部 品質管理課
        $deploy = "(deploy='品質保証課')";
    } else if( $res_list[$r][1] == 173 || $res_list[$r][1] == 515 || $res_list[$r][1] == 535 ) { // 技術部 技術課
        $deploy = "(deploy='技術課')";
    } else if( $res_list[$r][1] == 582 ) {  // 製造部
        $deploy = "(deploy='製造部 製造１課' OR deploy='製造部 製造２課')";
    } else if( $res_list[$r][1] == 518 || $res_list[$r][1] == 519 || $res_list[$r][1] == 556 || $res_list[$r][1] == 520 ) { // 製造部 製造１課
        $deploy = "(deploy='製造部 製造１課')";
    } else if( $res_list[$r][1] == 547 || $res_list[$r][1] == 528 || $res_list[$r][1] == 527 ) { // 製造部 製造２課
        $deploy = "(deploy='製造部 製造２課')";
    } else if( $res_list[$r][1] == 500 ) {  // 生産部
        $deploy = "(deploy='生産管理課 計画・購買係' OR deploy='生産管理課 資材係' OR deploy='カプラ組立課 標準係ＭＡ' OR deploy='カプラ組立課 標準係ＨＡ' OR deploy='カプラ組立課 特注係' OR deploy='リニア組立課')";
    } else if( $res_list[$r][1] == 545 || $res_list[$r][1] == 512 || $res_list[$r][1] == 532 || $res_list[$r][1] == 513|| $res_list[$r][1] == 533 || $res_list[$r][1] == 514 || $res_list[$r][1] == 534 ) { // 生産部 生産管理課
        $deploy = "(deploy='生産管理課 計画・購買係' OR deploy='生産管理課 資材係')";
    } else if( $res_list[$r][1] == 176 || $res_list[$r][1] == 522 || $res_list[$r][1] == 523 || $res_list[$r][1] == 525 ) { // 生産部 カプラ組立課
        $deploy = "(deploy='カプラ組立課 標準係ＭＡ' OR deploy='カプラ組立課 標準係ＨＡ' OR deploy='カプラ組立課 特注係')";
    } else if( $res_list[$r][1] == 551 || $res_list[$r][1] == 175 || $res_list[$r][1] == 572 ) { // 生産部 リニア組立課
        $deploy = "(deploy='リニア組立課')";
    } else {
        $deploy = "(deploy IS NULL)";   // エラー
    }
    // 件数取得条件
    $noinput1 = "yo_ad_rt!='-1' AND yo_ad_rt<=yo_ad_st AND ji_ad_rt=0 AND date!=date('today')";
    if( $res_list[$r][2] == 110 ) {
        $noinput = "yo_ad_ka IS NULL AND yo_ad_bu IS NULL";
        $noadmit = "ji_ad_ko='m' AND (ji_ad_ka IS NULL OR ji_ad_ka!='m') AND (ji_ad_bu IS NULL OR ji_ad_bu!='m')";
    } else if( $res_list[$r][2] == 47 || $res_list[$r][2] == 70 || $res_list[$r][2] == 95 ) {
        $noinput = "yo_ad_ka IS NULL";
        $noadmit = "ji_ad_bu='m' AND (ji_ad_ka IS NULL OR ji_ad_ka!='m')";
    } else if( $res_list[$r][2] == 46 || $res_list[$r][2] == 50 ) {
        $noinput = "yo_ad_ka!=''";
        $noadmit = "ji_ad_ka='m'";
    } else {
        $noinput = $noadmit = $deploy;
    }
    $where_noinput = "WHERE {$noinput1} AND {$noinput} AND {$deploy}";
    $where_noadmit = "WHERE {$noadmit} AND {$deploy}";
    
    // 結果報告未入力取得
    $query = "SELECT DISTINCT date, deploy FROM over_time_report $where_noinput";
    $res_noinput  = array();
    $rows_noinput = getResult($query, $res_noinput);
/**    
    // 結果報告未承認取得
    $query = "SELECT DISTINCT date, deploy FROM over_time_report $where_noadmit";
    $res_noadmit  = array();
    $rows_noadmit = getResult($query, $res_noadmit);
/**/    
    $uid = $res_list[$r][0];
    // メースアドレス取得
    $query = "SELECT trim(name), trim(mailaddr) FROM user_detailes LEFT OUTER JOIN user_master USING(uid)";
    $where = "WHERE uid='{$uid}'";  // uid
//    $where = "WHERE uid='300667'";  // uid 強制変更 ※リリース時は、コメント化
    $query .= $where;   // SQL query 文の完成
    $res_mail = array();
    if( getResult($query, $res_mail) <= 0 ) continue; // メールアドレス取得不可なら次へ
    
    // メール作成、送信
    $sendna = $res_mail[0][0];  // 名前
//    $sendna = $res_list[$r][3]; // 名前 強制変更 ※リリース時は、コメント化
    $mailad = $res_mail[0][1];  // メールアドレス
    $_SESSION['u_mailad']  = $mailad;
    $to_addres = $mailad;
    $add_head = "";
    $attenSubject = "{$sendna} 様 【未入力】 定時間外作業申告よりお知らせ"; // 宛先： 
    $message = "{$sendna} 様\n\n";
    $message .= "定時間外作業申告（残業結果報告）";
    
    if( $rows_noinput <= 0 ) continue; // 承認待ち無しなら次へ
    
    if( $rows_noinput <= 0 ) {
        $message .= "　未 入 力　ありません。\n\n";
    } else {
        $message .= "未入力が {$rows_noinput} 件あります。\n";
        $message .= "------------------------------------------------------------------\n";
        for( $n=0; $n<$rows_noinput; $n++ ) {
            $week   = array(' (日)',' (月)',' (火)',' (水)',' (木)',' (金)',' (土)');
            $date   = $res_noinput[$n][0];
            $day_no = date('w', strtotime($date));
            $date   = $res_noinput[$n][0] . $week[$day_no];
            $message .= "　作業日：{$date}\t部署名：{$res_noinput[$n][1]}\n";
        }
        $message .= "------------------------------------------------------------------\n";
        $message .= "入力するよう連絡して下さい。\n\n";
    }
/**    
    if( $rows_noadmit <= 0 ) {
        $message .= "　承認待ち　ありません。\n\n";
    } else {
        $message .= "　承認待ち　{$rows_noadmit} 件あります。承認処理をお願いします。\n\n";
        // 承認ページのアドレス(Uid)を表示、クリックで承認ページへ
        $message .= "http://masterst/per_appli/over_time_work_report/over_time_work_report_Main.php?calUid={$res_list[$r][0]}&showMenu=Judge&select_radio=3\n\n";
    }
/**/    
    $message .= "以上。";
    
    if (mb_send_mail($to_addres, $attenSubject, $message, $add_head)) {
        // 出席者へのメール送信履歴を保存
        //$this->setAttendanceMailHistory($serial_no, $atten[$i]);
    }
    ///// Debug
    //if ($cancel) {
    //    if ($i == 0) mb_send_mail('tnksys@nitto-kohki.co.jp', $attenSubject, $message, $add_head);
    //}
}

/////////// commit トランザクション終了
query_affected_trans($con, "commit");
// echo $query . "\n";
fclose($fpa);      ////// 日報用ログ書込み終了
fwrite($fpb, "------------------------------------------------------------------------\n");
fclose($fpb);      ////// 日報データ再取得用ログ書込み終了

exit();

?>
