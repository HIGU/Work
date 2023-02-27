#!/usr/local/bin/php
<?php
//////////////////////////////////////////////////////////////////////////////
// 定時間外作業申告承認待ち情報メール送信 cron.d tnk_daily 処理で実行       //
// 月～金の指定時にメール送信 全体版 承認待ちがある全承認者に定期メール     //
// Copyright (C) 2021-2021 Norihisa.Ohya norihisa_ooya@nitto-kohki.co.jp    //
// Changed history                                                          //
// 2021/10/06 Created   over_time_work_report_admit_mail.php                //
// 2022/03/11 一時的に、生産部長承認を技術部長へ                            //
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
// 工場長、部長、課長
$where = "(ud.pid=110)";
$where = "(ud.pid=47 OR ud.pid=70 OR ud.pid=95)";
$where = "(ud.pid=46 OR ud.pid=50)";
$where = "((ud.pid=110) OR (ud.pid=47 OR ud.pid=70 OR ud.pid=95) OR (ud.pid=46 OR ud.pid=50))";

// 指定長の uid と act_id 取得
$query = "
            SELECT          uid, ct.act_id, ud.pid, trim(name)
            FROM            user_detailes   AS ud
            LEFT OUTER JOIN cd_table        AS ct   USING(uid)
            WHERE           retire_date IS NULL AND $where
         ";
$res_list = array();
if( ($rows_list = getResult($query, $res_list)) <= 0) exit(); // 取得不可なら終了。

for( $r=0; $r<$rows_list; $r++ ) {
    $bu_act = 0;    // 初期化
if(date('Ymd')<'20220411') { // 一時的に、act_id 変更 決算処理が終わるまで cd_table を変更できない為
    if($res_list[$r][0] == "012980") $res_list[$r][1] = "500";
    if($res_list[$r][0] == "014524") $res_list[$r][1] = "501";
    if($res_list[$r][0] == "016713") $res_list[$r][1] = "611";
}
    // 条件作成
    $where = "WHERE yo_ad_rt!='-1' AND ";
    if( $res_list[$r][1] == 600 ) {  // 工場長
        if( $res_list[$r][2] == 95 ) {  // 副工場長
            $res_list[$r][1] = 582; // 製造部のact_idセット、後で判断する際に使用。
            $where .= "yo_ad_st=1 AND yo_ad_bu='m' AND (deploy='製造部 製造１課' OR deploy='製造部 製造２課')";
        } else {
            $where .= "yo_ad_st=2 AND yo_ad_ko='m' AND (deploy IS NOT NULL)";
        }
    } else if( $res_list[$r][1] == 610 ) {   // 管理部
        $where .= "yo_ad_st=1 AND yo_ad_bu='m' AND (deploy='総務課' OR deploy='商品管理課')";
    } else if( $res_list[$r][1] == 605 || $res_list[$r][1] == 650 || $res_list[$r][1] == 651 || $res_list[$r][1] == 660 ) { // ＩＳＯ事務局 管理部 総務課 総務 財務
        $where .= "yo_ad_st=0 AND yo_ad_ka='m' AND (deploy='総務課')";
        $bu_act = 610;
    } else if( $res_list[$r][1] == 670 ) {   // 管理部 商品管理課
        $where .= "yo_ad_st=0 AND yo_ad_ka='m' AND (deploy='商品管理課')";
        $bu_act = 610;
    } else if( $res_list[$r][1] == 501 ) {   // 技術部
        if(date('Ymd')<'20220401') { // 2022/03/11 一時的に、生産部長の分も取り込む
        $where .= "yo_ad_st=1 AND yo_ad_bu='m' AND (deploy='品質保証課' OR deploy='技術課' OR deploy='生産管理課 計画・購買係' OR deploy='生産管理課 資材係' OR deploy='カプラ組立課 標準係ＭＡ' OR deploy='カプラ組立課 標準係ＨＡ' OR deploy='カプラ組立課 特注係' OR deploy='リニア組立課')";
        } else {
        $where .= "yo_ad_st=1 AND yo_ad_bu='m' AND (deploy='品質保証課' OR deploy='技術課')";
        }
    } else if( $res_list[$r][1] == 174 || $res_list[$r][1] == 517 || $res_list[$r][1] == 537 || $res_list[$r][1] == 581 ) { // 技術部 品質管理課
        $where .= "yo_ad_st=0 AND yo_ad_ka='m' AND (deploy='品質保証課')";
        $bu_act = 501;
    } else if( $res_list[$r][1] == 173 || $res_list[$r][1] == 515 || $res_list[$r][1] == 535 ) { // 技術部 技術課
        $where .= "yo_ad_st=0 AND yo_ad_ka='m' AND (deploy='技術課')";
        $bu_act = 501;
    } else if( $res_list[$r][1] == 582 ) { // 製造部
        $where .= "yo_ad_st=1 AND yo_ad_bu='m' AND (deploy='製造部 製造１課' OR deploy='製造部 製造２課')";
    } else if( $res_list[$r][1] == 518 || $res_list[$r][1] == 519 || $res_list[$r][1] == 556 || $res_list[$r][1] == 520 ) { // 製造部 製造１課
        $where .= "yo_ad_st=0 AND yo_ad_ka='m' AND (deploy='製造部 製造１課')";
        $bu_act = 582;
    } else if( $res_list[$r][1] == 547 || $res_list[$r][1] == 528 || $res_list[$r][1] == 527 ) { // 製造部 製造２課
        $where .= "yo_ad_st=0 AND yo_ad_ka='m' AND (deploy='製造部 製造２課')";
        $bu_act = 582;
    } else if( $res_list[$r][1] == 500 ) { // 生産部
        $where .= "yo_ad_st=1 AND yo_ad_bu='m' AND (deploy='生産管理課 計画・購買係' OR deploy='生産管理課 資材係' OR deploy='カプラ組立課 標準係ＭＡ' OR deploy='カプラ組立課 標準係ＨＡ' OR deploy='カプラ組立課 特注係' OR deploy='リニア組立課')";
        if(date('Ymd')<'20220401') $where = "WHERE (deploy='dummy')"; // 2022/03/11 一時的に、技術部長へ
    } else if( $res_list[$r][1] == 545 || $res_list[$r][1] == 512 || $res_list[$r][1] == 532 || $res_list[$r][1] == 513|| $res_list[$r][1] == 533 || $res_list[$r][1] == 514 || $res_list[$r][1] == 534 ) { // 生産部 生産管理課
        $where .= "yo_ad_st=0 AND yo_ad_ka='m' AND (deploy='生産管理課 計画・購買係' OR deploy='生産管理課 資材係')";
        $bu_act = 500;
        if(date('Ymd')<'20220401') $bu_act = 501; // 2022/03/11 一時的に、技術部長へ
    } else if( $res_list[$r][1] == 176 || $res_list[$r][1] == 522 || $res_list[$r][1] == 523 || $res_list[$r][1] == 525 ) { // 生産部 カプラ組立課
        $where .= "yo_ad_st=0 AND yo_ad_ka='m' AND (deploy='カプラ組立課 標準係ＭＡ' OR deploy='カプラ組立課 標準係ＨＡ' OR deploy='カプラ組立課 特注係')";
        $bu_act = 500;
        if(date('Ymd')<'20220401') $bu_act = 501; // 2022/03/11 一時的に、技術部長へ
    } else if( $res_list[$r][1] == 551 || $res_list[$r][1] == 175 || $res_list[$r][1] == 572 ) { // 生産部 リニア組立課
        $where .= "yo_ad_st=0 AND yo_ad_ka='m' AND (deploy='リニア組立課')";
        $bu_act = 500;
        if(date('Ymd')<'20220401') $bu_act = 501; // 2022/03/11 一時的に、技術部長へ
    } else {
        $where .= "(deploy IS NULL)";   // エラー
    }
    // 承認待ち件数取得
    $query = "SELECT DISTINCT date, deploy FROM over_time_report $where";
    $res_count = array();
    $rows_ken  = getResult($query, $res_count);
    
    if( $rows_ken <= 0 ) continue; // 承認待ち無しなら次へ
    
    // 不在チェック処理
    $superiors = false;         // 上長通知フラグ（初期化）
    $date = date('Ymd');        // 今日の日付取得
    $uid = $res_list[$r][0];    // 自身のUID
    $query = "
                SELECT uid FROM working_hours_report_data_new
                WHERE  uid='$uid' AND working_date='$date' AND (absence!='00' OR str_time='0000' OR end_time!='0000')
             ";
    $res = array();
    if( getResult2($query, $res) > 0 && $res_list[$r][2] != 110 ) {
        $kojyo = false;     // 工場長通知フラグ（初期化）
        if( $res_list[$r][2]==46 || $res_list[$r][2]==50 ) {
            // 課長になるので、部長の確認、不在なら工場長まで
            for( $n=0; $n<$rows_list; $n++ ) {
                if( $res_list[$n][1] == $bu_act ) {
                    $uid = $res_list[$n][0];
                    break; // 自身の部長 まで
                }
            }
            $query = "
                        SELECT uid FROM working_hours_report_data_new
                        WHERE  uid='$uid' AND working_date='$date' AND (absence!='00' OR str_time='0000' OR end_time!='0000')
                     ";
            $res = array();
            if( getResult2($query, $res) <= 0 ) {
                $superiors = true;  // 上長通知フラグ（ON）
            } else {
                $kojyo = true;  // 工場長通知フラグ（ON）
            }
        } else {
            $kojyo = true;  // 工場長通知フラグ（ON）
        }
        // 工場長チェック
        if( $kojyo ) {
            for( $n=0; $n<$rows_list; $n++ ) {
                if( $res_list[$n][1] == 600 ) {
                    $uid = $res_list[$n][0];
                    break; // 工場長 まで
                }
            }
            $query = "
                        SELECT uid FROM working_hours_report_data_new
                        WHERE  uid='$uid' AND working_date='$date' AND (absence!='00' OR str_time='0000' OR end_time!='0000')
                     ";
            $res = array();
            if( getResult2($query, $res) <= 0 ) {
                $superiors = true;  // 上長通知フラグ（ON）
            }
        }
    }
    
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
//    if( $superiors ) $sendna = $res_list[$n][3];    // 名前 強制変更 ※リリース時は、コメント化
    if( $superiors ) {
        $attenSubject = "{$sendna} 様 【不在未承認】 定時間外作業申告よりお知らせ"; // 宛先： 
    } else {
        $attenSubject = "{$sendna} 様 【未承認】 定時間外作業申告よりお知らせ";
    }
    $message = "{$sendna} 様\n\n";
    if( $superiors ) {
        $message .= "{$res_list[$r][3]} 様 不在の為、代わりに\n\n";
        $message .= "定時間外作業申告（事前申請）承認処理をお願いします。\n\n";
        $message .= "http://masterst/per_appli/over_time_work_report/over_time_work_report_Main.php?calUid={$uid}&showMenu=Judge&select_radio=2\n\n";
    } else {
        $message .= "定時間外作業申告（事前申請）";
        if( $rows_ken <= 0 ) {
            $message .= "承認待ちはありません。\n\n";
        } else {
            $message .= "承認待ちが {$rows_ken} 件あります。\n\n";
            $message .= "承認処理をお願いします。\n\n";
            // 承認ページのアドレス(Uid)を表示、クリックで承認ページへ
            $message .= "http://masterst/per_appli/over_time_work_report/over_time_work_report_Main.php?calUid={$res_list[$r][0]}&showMenu=Judge\n\n";
        }
    }
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
