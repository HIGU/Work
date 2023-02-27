#!/usr/local/bin/php
<?php
//////////////////////////////////////////////////////////////////////////////
// 定時間外作業申告【早出】規程外情報送信 cron.d tnk_daily 処理で実行       //
// 月～金の指定時にメール送信 全体版                                        //
//                           【早出】規程外情報を承認者へ定期メール         //
// Copyright (C) 2022-2022 Norihisa.Ohya norihisa_ooya@nitto-kohki.co.jp    //
// Changed history                                                          //
// 2022/04/11 Created   over_time_work_report_early_outside_mail.php        //
//////////////////////////////////////////////////////////////////////////////
//ini_set('error_reporting', E_STRICT);       // E_STRICT=2048(php5) E_ALL=2047 debug 用
//ini_set('display_errors', '1');             // Error 表示 ON debug 用 リリース後コメント
ini_set('implicit_flush', 'off');           // echo print で flush させない(遅くなるため)
require_once ('/var/www/html/function.php');

$log_date = date('Y-m-d H:i:s');        ///// 日報用ログの日時
$fpa = fopen('/tmp/nippo.log', 'a');    ///// 日報用ログファイルへの書込みでオープン
$fpb = fopen('/tmp/as400get_ftp_re.log', 'a');    ///// 日報データ再取得用ログファイルへの書込みでオープン
fwrite($fpb, "定時間外作業申告【早出】規程外情報メール送信\n");
fwrite($fpb, "/var/www/html/system/daily/over_time_work_report_admit_mail.php\n");
echo "/var/www/html/system/daily/over_time_work_report_admit_mail.php\n";

if ($con = db_connect()) {
    query_affected_trans($con, "begin");
} else {
    echo "$log_date 定時間外作業申告【早出】規程外情報送信 db_connect() error \n";
    fwrite($fpa,"$log_date 定時間外作業申告【早出】規程外情報送信 db_connect() error \n");
    fwrite($fpb,"$log_date 定時間外作業申告【早出】規程外情報送信 db_connect() error \n");
    exit();
}

/////////// begin トランザクション開始
$bumon_array = array("総務課", "商品管理課", "品質保証課", "技術課", "製造部 製造１課", "製造部 製造２課", "生産管理課 計画・購買係", "生産管理課 資材係", "カプラ組立課 標準係ＭＡ", "カプラ組立課 標準係ＨＡ", "カプラ組立課 特注係", "リニア組立課");
$max = count($bumon_array);

for($i=0; $i<$max; $i++) {
    $bumon = $bumon_array[$i];
    $act_id = "";
    if( $bumon == "総務課" ) {
        $act_id = "(ct.act_id=605 OR ct.act_id=610 OR ct.act_id=650 OR ct.act_id=651 OR ct.act_id=660) ";
    } else if( $bumon == "商品管理課" ) {
        $act_id = "(ct.act_id=670) ";
    } else if( $bumon == "品質保証課" ) {
        $act_id = "(ct.act_id=174 OR ct.act_id=517 OR ct.act_id=537 OR ct.act_id=581) ";
    } else if( $bumon == "技術課" ) {
        $act_id = "(ct.act_id=501 OR ct.act_id=173 OR ct.act_id=515 OR ct.act_id=535) ";
    } else if( $bumon == "製造部 製造１課" ) {
        $act_id = "(ct.act_id=518 OR ct.act_id=519 OR ct.act_id=556 OR ct.act_id=520) ";
    } else if( $bumon == "製造部 製造２課" ) { // 600 も含んでいる為、条件に、"AND ud.uid!=999999 AND ud.pid!=110" 追加
        $act_id = "(ct.act_id=600 OR ct.act_id=582 OR ct.act_id=547 OR ct.act_id=527 OR ct.act_id=528) AND ud.uid!=999999 AND ud.pid!=110";
    } else if( $bumon == "生産管理課 計画・購買係" ) {
        $act_id = "(ct.act_id=500 OR ct.act_id=545 OR ct.act_id=512 OR ct.act_id=532 OR ct.act_id=513 OR ct.act_id=533) ";
    } else if( $bumon == "生産管理課 資材係" ) {
        $act_id = "(ct.act_id=545 OR ct.act_id=514 OR ct.act_id=534) ";
    } else if( $bumon == "カプラ組立課 標準係ＭＡ" ) {
//            $act_id = "(ct.act_id=522) ";
        $act_id = "((ct.act_id=176 AND uid!='970225') OR ct.act_id=522 OR (ct.act_id=523 AND uid='970328')) ";  // 「菅 純子さん」強制的に、ＭＡへ表示
    } else if( $bumon == "カプラ組立課 標準係ＨＡ" ) {
//            $act_id = "(ct.act_id=176 OR ct.act_id=523) ";
        $act_id = "(ct.act_id=176 OR (ct.act_id=523 AND uid!='970328')) ";   // 「菅 純子さん」強制的に、ＨＡから除外
    } else if( $bumon == "カプラ組立課 特注係" ) {
        $act_id = "((ct.act_id=176 AND uid!='970225') OR ct.act_id=525) ";
    } else if( $bumon == "リニア組立課" ) {
        $act_id = "(ct.act_id=551 OR ct.act_id=175 OR ct.act_id=572) ";
    }
    $where = "WHERE " . $act_id . " AND (cast(ud.class as integer) < 8 OR ud.class IS NULL) AND ud.retire_date IS NULL ";
    $order = "ORDER BY ud.sid DESC, ud.pid DESC, ud.uid ASC";   // 通常
    // 指定部署 8級職 未満の uid 一覧を取得
    $query = "SELECT ud.uid, ud.name FROM user_detailes AS ud LEFT OUTER JOIN cd_table AS ct USING(uid) LEFT OUTER JOIN act_table AS at USING(act_id) $where $order";
    $res_uid  = array();
    $rows_uid = getResult($query, $res_uid);
    
    $msg = "";
    $date_array[] = "";
    array_shift($date_array);
    $str_date = "20220411"; // 開始日
    for($r=0; $r<$rows_uid; $r++) {
        $uid = $res_uid[$r][0];
        $str_time="0820";
        // 商管（村上）
        if( $uid == '300349' ) $str_time="0905";
        // 出勤時間を規程外を取得
        $query = "SELECT to_char((CAST(working_date AS TIMESTAMP)), 'YYYY/MM/DD'), str_time FROM working_hours_report_data_new WHERE uid='$uid' AND working_date>='$str_date' AND (str_time!='0000' AND str_time < '$str_time') ORDER BY working_date";
        $res_time  = array();
        $rows_time = getResult($query, $res_time);
        if( $rows_time <= 0 ) continue; // 無ければ、次の人
        
        for($n=0; $n<$rows_time; $n++) {
            $date = $res_time[$n][0];
            if( in_array($date, $date_array) ) continue; // あれば、次の日
            // 申請状況
            $query = "SELECT uid FROM over_time_report_early WHERE uid='$uid' AND date='$date' AND (yo_str_h IS NOT NULL OR ji_ad_rt > 0)";
            $res  = array();
            if( getResult($query, $res) > 0 ) continue; // あれば、申請済み
            // 商管（村上）// substr(timepro, 29, 2) 勤務区分 '01'=8:30～17:30、'18'=9:15～18:00
            if( $uid == '300349' ) {
                if( $res_time[$n][1] >= "0820" && $res_time[$n][1] <= "0830" ) continue; // 勤務区分[一般]の為、次のデータへ
                // 正確には、上記ではなく以下の処理で勤務区分をチェックする必要がある。
                $query = "SELECT timepro FROM timepro_daily_data WHERE substr(timepro, 17, 8) = to_char((CAST('$date' AS TIMESTAMP)), 'YYYYMMDD') AND substr(timepro, 3, 6)='$uid' AND substr(timepro, 29, 2)='01'";
                $res  = array();
                if( getResult($query, $res) > 0 ) {
                    if( $res_time[$n][1] >= "0820" ) continue;  // 勤務区分[一般]の為、次のデータへ
                }
            }
            array_push($date_array, $date);
            $msg .= "$date\n";
        }
    }
    sort($date_array);
    $rows_array = count($date_array);
    if( $rows_array == 0 ) continue; // 対象者なし、次の課へ
    
    // 指定部門の課長いる？
    $query = "SELECT uid FROM user_detailes AS ud LEFT OUTER JOIN cd_table AS ct USING(uid) WHERE retire_date IS NULL AND (ud.pid=46 OR ud.pid=50) AND $act_id";
    $res  = array();
    if( getResult($query, $res) <= 0 ) {
        // いなければ部長へ
        if( $bumon == "総務課" || $bumon == "商品管理課" ) {
            $act_id = "ct.act_id=610 ";
        } else if( $bumon == "品質保証課" || $bumon == "技術課" ) {
            $act_id = "ct.act_id=501 ";
        } else if( $bumon == "製造部 製造１課" || $bumon == "製造部 製造２課" ) {
            $act_id = "ct.act_id=600 ";
        } else if( $bumon == "生産管理課 計画・購買係" || $bumon == "生産管理課 資材係" || $bumon == "カプラ組立課 標準係ＭＡ" || $bumon == "カプラ組立課 標準係ＨＡ" || $bumon == "カプラ組立課 特注係" || $bumon == "リニア組立課"  ) {
            $act_id = "ct.act_id=500 ";
        }
        $query = "SELECT uid FROM user_detailes AS ud LEFT OUTER JOIN cd_table AS ct USING(uid) WHERE retire_date IS NULL AND (ud.pid=47 OR ud.pid=70 OR ud.pid=95) AND $act_id";
        $res  = array();
        if( getResult($query, $res) <= 0 ) continue; // なし。
    }
    $uid = $res[0][0];
    
    // メースアドレス取得
    $query = "SELECT trim(name), trim(mailaddr) FROM user_detailes LEFT OUTER JOIN user_master USING(uid)";
    $where = "WHERE uid='$uid'";   // uid
//    $where = "WHERE uid='300667'";   // uid 強制変更 ※リリース時は、コメント化
    $query .= $where;   // SQL query 文の完成
    $res_mail = array();
    if( getResult($query, $res_mail) <= 0 ) continue; // メールアドレス取得不可なら次へ
    
    // メール作成、送信
    $sendna = $res_mail[0][0];  // 名前
//    $sendna = $uid; // 名前 強制変更 ※リリース時は、コメント化
    $mailad = $res_mail[0][1];  // メールアドレス
    $_SESSION['u_mailad']  = $mailad;
    $to_addres = $mailad;
    $add_head = "";
    $attenSubject = "{$sendna} 様 【規程外】 定時間外作業申告よりお知らせ"; // 宛先： 
    $message = "{$sendna} 様\n\n";
    $message .= "{$bumon}\n\n";
    for($a=0; $a<$rows_array; $a++ ) {
        $message .= array_shift($date_array);
        $message .= "\n\n";
    }
    $message .= "上記、日付に事前申請なしで出勤打刻時間が、規程時間外の方がいます。\n";
    $message .= "定時間外作業申告【早出】より結果報告の [登録] をするよう指示をお願いします。\n\n";
    $message .= "http://masterst/per_appli/over_time_work_report/over_time_work_report_Main.php?calUid={$uid}&showMenu=Appli&ddlist_v_type=0\n";
    $message .= "リンクを開き日付と部署を選択後、[読み込み]ボタンをクリック。\n";
    $message .= "[出勤時間]が黄色表示で、事前申請無しの人が対象。\n\n";
    $message .= "  早出なし → 延長及び残業なしにチェックを入れ [登録] 。\n";
    $message .= "  早出した → 実際作業時間に早出作業時間、内容入れ [登録] 。\n\n";
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
