#!/usr/local/bin/php
<?php
//////////////////////////////////////////////////////////////////////////////
// 機械 稼動管理システム2 ログ to DataBase cgi-cli版 FWServer 1.30対応      //
// Copyright (C) 2003-2007 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp      //
// Changed history                                                          //
// 2003/07/02 Created  equip_auto_log2data_ftp.php                          //
// 2003/07/03 CLI版とFunctionを共有するためrequire_once()を絶対指定に変更   //
// 2004/01/30 テーブルをequip_work_log2 に変更したため ロジックを対応させた //
// 2004/02/10 FWServer が V1.30で nfs が使用できないため FTP へ切替えた     //
//            equip_auto_log2data_cmd.php → equip_auto_log2data_ftp へ変更 //
// 2004/03/04 BCDデータが無い場合は無条件に電源off=0のロジックを追加 $state //
//            csv_flg != 1 を csv_flg = 3 に変更し FWS=2 と区別する         //
// 2004/03/11 rollbackとexit()をコメントアウト１台のデータがNGで他がOKの対応//
//            $ftp_flg を付けて１台がＮＧでも終了しないように変更           //
// 2004/03/15 Counterが進む場合に自動か無人かのチェックをし違う場合は自動へ //
//            物理信号の取得の問題なのでstate履歴と食い違いがでる可能性が   //
//            あるため将来的には無くす方向へ                                //
// 2004/03/25 ftpのloginに失敗することがある(fwserver2のみ)ので対処 sleep   //
//              2004-03-17 19:16:01 fwserver2のFTPのloginに失敗しました。   //
//            テスト的に fwserver2のlogin部分に sleep(10)を追加しリトライ   //
// 2004/03/29 @ftp_login()にして一発目のエラーメッセージを抑制させる。      //
//            Counterの書き込みに失敗の debug 文を追加 $mac_state $query    //
//            FWS1方式=電源ON/OFF信号なし FWS2方式=電源ON/OFF信号を追加     //
// 2004/04/01 上記の ftp 関係のエラーは php-4.3.5 のバグであった。4.3.6でOK //
// 2004/06/21 4.3.6-cgi → 4.3.7-cgi へ変更  Netmoni方式を取込み完了        //
// 2004/07/12 state_check_netmoni() Netmoni & FWS 方式を統一 スイッチ方式   //
// 2004/07/15 Netmoniタイプの機械の物理信号も equip_mac_state_log2 に取込み //
// 2004/07/26 equip_mac_state_log2の書込みエラーlog内容の変更$queryを出力   //
//            FWS1/2 初回の書込み時にヘッダーの日時をチェックしてUPDATEする //
// 2004/07/27 equip_mac_state_log2は稼動中にかかわらず24時間記録に変更      //
// 2004/08/23 substr(microtime(), 2, 6)をコメントにしdate('Ymd His')に変更  //
//            Netmoni方式の電源OFF対策でwork_cntを前回のデータを引継ぐに変更//
// 2004/10/08 Netmoni4電源OFFだったらを→電源OFFor中断の時work_cntを維持する//
// 2004/11/29 FWS3〜5を追加(1工場)                                          //
// 2004/12/13 #!/usr/local/bin/php-5.0.2-cli → php (内部は5.0.3RC2)に変更  //
// 2005/02/15 sleep(10) → sleep(2) へ変更 時間短縮のため。上記は現在 5.0.3 //
// 2005/02/16 state_check_netmoni() パラメーターの追加 $ftp_con(ストリーム) //
// 2005/02/17 cronで実行なので他のプロセス負荷を考慮して１０秒遅延する。    //
// 2005/02/21 ２重実行防止のためチェック用ファイルをロジックへ組込          //
// 2005/02/25 FTPで失敗した場合は２秒待つロジックは意味が無いので削除1回のみ//
// 2005/05/10 SQLのselect equip_work_log2 を equip_index()を全て使用する    //
//                 equip_mac_state_log2 を equip_index2()を全て使用する     //
// 2005/10/07 NetmoniでFTP上の一時ファイルが前回何らかのトラブルで削除できず//
//            に残っている場合の対応を追加 952(検索値:前回の旧一時ファイル) //
// 2006/03/03 指示番号が変わった時の初回書込み条件を変更(確実に0にするSQL文)//
// 2007/06/15 Webの管理メニューから自動ログ収集を制御するためロジック追加   //
//////////////////////////////////////////////////////////////////////////////
ini_set('error_reporting', E_STRICT);       // E_STRICT=2048(php5) E_ALL=2047 debug 用
// ini_set('error_reporting', E_ALL);          // E_ALL='2047' debug 用
ini_set('implicit_flush', 'off');           // echo print で flush させない(遅くなるため) CLI CGI版
ini_set('max_execution_time', 30);          // 最大実行時間=30秒 CLI版は必要ないが一応
require_once ('/home/www/html/tnk-web/equip/equip_function.php');

$check_file    = '/home/www/html/tnk-web/equip/check_file';
$auto_log_stop = '/home/www/html/tnk-web/equip/equip_auto_log_stop';    // 2007/06/15 ADD
if (file_exists($check_file)) {
    exit(); // 前のプロセスが終了していないのでキャンセル
} elseif (file_exists($auto_log_stop)) {
    exit(); // 自動ログ収集のストップ指示のため終了 2007/06/15 ADD
} else {
    fopen($check_file, 'a');    // チェック用ファイルを作成
}
sleep(8);      // cronで実行なので他のプロセス負荷を考慮して１０秒遅延する。2007/06/15 10→8へ

/****************************** FWS1方式 *********************************/
$ftp_flg = true;
////////// FTP CONNECT
if ( !($ftp_con = ftp_connect(FWS1)) ) {   // テスト用にリテラル表示
    $error_msg = date('Y-m-d H:i:s ') . "fwserver1のFTPの接続に失敗しました。";
    `echo "$error_msg" >> /tmp/equipment_ftp_error.log`;
    $ftp_flg = false;
} else {
    if (!@ftp_login($ftp_con, FWS_USER, FWS_PASS)) {      // 2004/03/29 エラーメッセージを抑制
        $error_msg = date('Y-m-d H:i:s ') . "FWS1のFTPのloginに失敗しました。";
        `echo "$error_msg" >> /tmp/equipment_ftp_error.log`;
        $ftp_flg = false;
    }
}

if ($ftp_flg) {
////////// 稼動中に関係なく 機械マスター監視する機械番号を取得 (物理状態を24時間監視するため)
$query = "select mac_no, csv_flg from equip_machine_master2 where csv_flg = 2 and survey = 'Y'";
                        // Netmoni=1 fwserver1=2 fwserver2=3 fwserver3=4 fwserver4=5 ... , Net&fws1=101
$res_key = array();
if ( ($rows_key = getResult($query, $res_key)) >= 1) {      // レコードが１以上あれば
    for ($i=0; $i<$rows_key; $i++) {                        // １レコードずつ処理
        ///// insert 用 変数 初期化
        $mac_no   = $res_key[$i]['mac_no'];
        $csv_flg  = $res_key[$i]['csv_flg'];
        /////////// FTP上のファイルの存在チェック
        if (ftp_size($ftp_con, "/home/fws/usr/{$mac_no}_work_state.log") != -1) {
            /////////// FTP rename
            if (ftp_rename($ftp_con, "/home/fws/usr/{$mac_no}_work_state.log", "/home/fws/usr/{$mac_no}_work_state.tmp")) {
                /////////// FTP Download
                if (ftp_get($ftp_con, "/home/fws/{$mac_no}_work_state.log", "/home/fws/usr/{$mac_no}_work_state.tmp", FTP_ASCII)) {
                    // echo 'FTPのDownloadに成功しました。';
                    ftp_delete($ftp_con, "/home/fws/usr/{$mac_no}_work_state.tmp");  // 旧ファイルは削除
                } else {
                    // echo 'FTPのDownloadに失敗しました。';
                    `/bin/rm -f /home/fws/{$mac_no}_work_state.log`;
                }
            } else {
                // echo 'FTPのrenameに失敗しました。';
                `/bin/rm -f /home/fws/{$mac_no}_work_state.log`;
            }
        }
        /////////// begin トランザクション開始
        if ($con = db_connect()) {
            query_affected_trans($con, 'begin');
            ///// State Log File Check        現在の状態(運転中・停止中)・日時を取得 後日に電源OFFも取得予定
            $state_file = "/home/fws/{$mac_no}_work_state.log";     // ディレクトリ・ファイル名生成
            $state_temp = "/home/fws/{$mac_no}_work_state.tmp";     // Rename 用ディレクトリ・ファイル名生成
            if (file_exists($state_file)) {                         // State Log File があれば
                if (rename($state_file, $state_temp)) {
                    $fp = fopen ($state_temp,'r');
                    $row  = 0;                                  // 全レコード
                    $data = array();                            // 年月日,時間,加工数
                    while ($data[$row] = fgetcsv ($fp, 50, ',')) {
                        if ($data[$row][0] != '') {
                            $row++;
                        }
                    }
                    for ($j=0; $j<$row; $j++) {         // Status File にレコードがあれば状態と日時を書込む
                        if ($data[$j][2] == 'auto') {   // ファイルから物理状態番号を取得
                            $state_p = 1;               // 運転中(自動運転)
                        } elseif ($data[$j][2] == 'stop') {
                            $state_p = 3;               // 停止中
                        } elseif ($data[$j][2] == 'on') {
                            $state_p = 3;               // 電源ONの場合のDefault値は停止中=3
                        } else {
                            $state_p = 0;               // 電源OFF "off" の予定
                        }
                        $date_time = $data[$j][0] . " " . $data[$j][1];     // timestamp型の変数生成 注意
                        $state_name = equip_machine_state($mac_no, $state_p, $txt_color, $bg_color);
                        $query = "insert into equip_mac_state_log2
                            (mac_no, state, date_time, state_name, state_type)
                            values($mac_no, $state_p, '$date_time', '$state_name', $csv_flg)";
                        if (query_affected_trans($con, $query) <= 0) {
                            $temp_msg = "$query\n date_time:$date_time mac_no:$mac_no state:$state_p j=$j";
                            `echo "$temp_msg" >> /tmp/equipment_write_error2.log`;
                            // query_affected_trans($con, "rollback");         // transaction rollback
                            // exit();
                        }
                    }
                    unlink($state_temp);        // 一時ファイルの削除
                } else {
                    echo "ステータスファイルの rename() に失敗\n";
                }
            }
            ///// State Log File 処理終了
            /////////// commit トランザクション終了
            query_affected_trans($con, 'commit');
        } else {
            echo "データベースの接続1に失敗\n";
            exit();
        }
    }
}
////////// 稼動中の機械�發鬟悒奪澄璽侫．ぅ襪�ら取得 & 機械マスターから機械名を取得
$query = "select mac_no, siji_no, koutei, parts_no, plan_cnt, mac_name, csv_flg from equip_work_log2_header
        left outer join equip_machine_master2 using(mac_no)
        where work_flg is TRUE and csv_flg = 2 and survey = 'Y'
";      // Netmoni=1&101 fwserver1=2 fwserver2=3 fwserver3=4 fwserver4=5 fwserver5=6 ...
$res_key = array();
if ( ($rows_key = getResult($query, $res_key)) >= 1) {      // レコードが１以上あれば
    for ($i=0; $i<$rows_key; $i++) {                        // １レコードずつ処理
        ///// insert 用 変数 初期化
        $mac_no   = $res_key[$i]['mac_no'];
        $siji_no  = $res_key[$i]['siji_no'];
        $parts_no = $res_key[$i]['parts_no'];
        $koutei   = $res_key[$i]['koutei'];
        $plan_cnt = $res_key[$i]['plan_cnt'];
        $mac_name = $res_key[$i]['mac_name'];
        $csv_flg  = $res_key[$i]['csv_flg'];
        /////////// FTP上のファイルの存在チェック
        if (ftp_size($ftp_con, "/home/fws/usr/{$mac_no}-bcd1") != -1) {
            /////////// FTP Download
            if (!ftp_get($ftp_con, "/home/fws/{$mac_no}-bcd1", "/home/fws/usr/{$mac_no}-bcd1", FTP_ASCII)) {
                // echo 'FTPのDownloadに失敗しました。';
            }
        } else {
            if (file_exists("/home/fws/{$mac_no}-bcd1")) {   // 旧ファイルがあれば削除
                unlink("/home/fws/{$mac_no}-bcd1");
            }
        }
        /////////// FTP上のファイルの存在チェック
        if (ftp_size($ftp_con, "/home/fws/usr/{$mac_no}-bcd2") != -1) {
            if (!ftp_get($ftp_con, "/home/fws/{$mac_no}-bcd2", "/home/fws/usr/{$mac_no}-bcd2", FTP_ASCII)) {
                // echo 'FTPのDownloadに失敗しました。';
            }
        } else {
            if (file_exists("/home/fws/{$mac_no}-bcd2")) {   // 旧ファイルがあれば削除
                unlink("/home/fws/{$mac_no}-bcd2");
            }
        }
        /////////// FTP上のファイルの存在チェック
        if (ftp_size($ftp_con, "/home/fws/usr/{$mac_no}-bcd4") != -1) {
            if (!ftp_get($ftp_con, "/home/fws/{$mac_no}-bcd4", "/home/fws/usr/{$mac_no}-bcd4", FTP_ASCII)) {
                // echo 'FTPのDownloadに失敗しました。';
            }
        } else {
            if (file_exists("/home/fws/{$mac_no}-bcd4")) {   // 旧ファイルがあれば削除
                unlink("/home/fws/{$mac_no}-bcd4");
            }
        }
        /////////// FTP上のファイルの存在チェック
        if (ftp_size($ftp_con, "/home/fws/usr/{$mac_no}-bcd8") != -1) {
            if (!ftp_get($ftp_con, "/home/fws/{$mac_no}-bcd8", "/home/fws/usr/{$mac_no}-bcd8", FTP_ASCII)) {
                // echo 'FTPのDownloadに失敗しました。';
            }
        } else {
            if (file_exists("/home/fws/{$mac_no}-bcd8")) {   // 旧ファイルがあれば削除
                unlink("/home/fws/{$mac_no}-bcd8");
            }
        }
        ///// State File Check BCD演算  現在の状態を取得
        $bcd1_file = "/home/fws/{$mac_no}-bcd1";            // BCD演算用ファイル名生成
        $bcd2_file = "/home/fws/{$mac_no}-bcd2";
        $bcd4_file = "/home/fws/{$mac_no}-bcd4";
        $bcd8_file = "/home/fws/{$mac_no}-bcd8";
        $state_bcd = 0;                                     // 初期化
        if (file_exists($bcd1_file)) {
            $state_bcd += 1;
        }
        if (file_exists($bcd2_file)) {
            $state_bcd += 2;
        }
        if (file_exists($bcd4_file)) {
            $state_bcd += 4;
        }
        if (file_exists($bcd8_file)) {
            $state_bcd += 8;
        }
        $query = "select state from equip_mac_state_log2 where equip_index2(mac_no,date_time) < '{$mac_no}99999999999999' order by equip_index2(mac_no,date_time) DESC limit 1";
        if (getUniResult($query, $state_p) > 0) {
            // 物理信号がセットされていれば
            $state = state_check($state_p, $state_bcd);   // 物理状態信号とスイッチの状態とで適正値をチェック
        } else {
            $state = 0;     // 状態データが無いので無条件に電源off=0
        }
        /////////// FTP上のファイルの存在チェック
        if (ftp_size($ftp_con, "/home/fws/usr/{$mac_no}_work_cnt.log") != -1) {
            /////////// FTP rename
            if (ftp_rename($ftp_con, "/home/fws/usr/{$mac_no}_work_cnt.log", "/home/fws/usr/{$mac_no}_work_cnt.tmp")) {
                /////////// FTP Download
                if (ftp_get($ftp_con, "/home/fws/{$mac_no}_work_cnt.log", "/home/fws/usr/{$mac_no}_work_cnt.tmp", FTP_ASCII)) {
                    // echo 'FTPのDownloadに成功しました。';
                    ftp_delete($ftp_con, "/home/fws/usr/{$mac_no}_work_cnt.tmp");  // 旧ファイルは削除
                } else {
                    // echo 'FTPのDownloadに失敗しました。';
                    `/bin/rm -f /home/fws/{$mac_no}_work_cnt.log`;
                }
            } else {
                // echo 'FTPのrenameに失敗しました。';
                `/bin/rm -f /home/fws/{$mac_no}_work_cnt.log`;
            }
        }
        /////////// begin トランザクション開始
        if ($con = db_connect()) {
            query_affected_trans($con, 'begin');
            ///// Counter File Check        現在の加工数・日時を取得
            $cnt_file = "/home/fws/{$mac_no}_work_cnt.log";     // ディレクトリ・ファイル名生成
            $cnt_temp = "/home/fws/{$mac_no}_work_cnt.tmp";     // Rename 用ディレクトリ・ファイル名生成
            if (file_exists($cnt_file)) {                       // Counter File があれば
                if (rename($cnt_file, $cnt_temp)) {
                    $fp = fopen ($cnt_temp,'r');
                    $row  = 0;                                  // 全レコード
                    $data = array();                            // 年月日,時間,加工数
                    while ($data[$row] = fgetcsv ($fp, 50, ',')) {
                        if ($data[$row][0] != '') {
                            $row++;
                        }
                    }
                    if ($row >= 1) {            // Counter File にレコードがあれば状態と加工数書込み
                        ///// 現在のデータベースの最新レコードを取り込む
                        $query = "
                            SELECT mac_state, work_cnt FROM equip_work_log2
                            WHERE
                                equip_index(mac_no, siji_no, koutei, date_time) < '{$mac_no}{$siji_no}{$koutei}99999999999999'
                                AND
                                equip_index(mac_no, siji_no, koutei, date_time) > '{$mac_no}{$siji_no}{$koutei}00000000000000'
                            ORDER BY equip_index(mac_no, siji_no, koutei, date_time) DESC limit 1
                        ";
                        $res = array();
                        if ( ($rows = getResult($query, $res)) >= 1) {  // 前回の加工数にプラスして書込み
                            for ($j=0; $j<$row; $j++) {
                                $work_cnt  = $res[0]['work_cnt'] + ($j + 1);      // Counter UP
                                $date_time = $data[$j][0] . ' ' . $data[$j][1];     // PostgreSQLのTIMESTAMP型に変更
                                if ( ($state == 1) || ($state == 8) || ($state == 5) ) {     // 自動運転か無人運転又は段取中のはずなのでチェックして必要なら訂正
                                    $mac_state = $state;
                                } else {
                                    $mac_state = 1;     // Counterが進んでいるのに自動でも無人又は段取でもない場合は強制的に自動運転にする
                                }
                                $query = "insert into equip_work_log2
                                    (mac_no, date_time, mac_state, work_cnt, siji_no, koutei)
                                    values($mac_no, '$date_time', $mac_state, $work_cnt, $siji_no, $koutei)";
                                if (query_affected_trans($con, $query) <= 0) {
                                    $temp_msg = "FWS1 Counterの書き込みに失敗 レコード:$j : $mac_no : $date_time : $mac_state \n";
                                    $temp_msg .= $query;    // debug 用
                                    `echo "$temp_msg" >> /tmp/equipment_write_error2.log`;
                                    // query_affected_trans($con, 'rollback');         // transaction rollback
                                    // exit();
                                }
                            }
                        } else {                    // データベースが初回のため無条件に書込み
                            for ($j=0; $j<$row; $j++) {
                                $work_cnt  =  ($j + 1);   // 初回の場合はここが違う
                                $date_time = $data[$j][0] . ' ' . $data[$j][1];     // PostgreSQLのTIMESTAMP型に変更
                                // $mac_state = $state;     // 初回の場合は過去のデータを取込む可能性が高いため以下は必要
                                if ( ($state == 1) || ($state == 8) || ($state == 5) ) {     // 自動運転か無人運転又は段取中のはずなのでチェックして必要なら訂正
                                    $mac_state = $state;
                                } else {
                                    $mac_state = 1;     // Counterが進んでいるのに自動でも無人又は段取でもない場合は強制的に自動運転にする
                                }
                                $query = "insert into equip_work_log2
                                    (mac_no, date_time, mac_state, work_cnt, siji_no, koutei)
                                    values($mac_no, '$date_time', $mac_state, $work_cnt, $siji_no, $koutei)";
                                if (query_affected_trans($con, $query) <= 0) {
                                    $temp_msg = "fwserver1 初回データベースの書き込みに失敗 レコード:$j\n$query";
                                    `echo "$temp_msg" >> /tmp/equipment_write_error2.log`;
                                    // query_affected_trans($con, 'rollback');         // transaction rollback
                                    // exit();
                                } else {
                                    $query = "select str_timestamp from equip_work_log2_header
                                            where mac_no={$mac_no} and siji_no={$siji_no} and koutei={$koutei}
                                    ";
                                    if (getUniResTrs($con, $query, $str_timestamp) > 0) {
                                        $query = "select case when cast('$date_time' as TIMESTAMP) < cast('$str_timestamp' as TIMESTAMP)
                                                then (select 1) else (select 0) end";
                                        if (getUniResTrs($con, $query, $check_time) > 0) {
                                            if ($check_time == 1) {
                                                $query = "update equip_work_log2_header set str_timestamp='{$date_time}'
                                                        where mac_no={$mac_no} and siji_no={$siji_no} and koutei={$koutei}";
                                                if (query_affected_trans($con, $query) <= 0) {
                                                    $temp_msg = "初回データの日時比較でHeaderのUPDATEに失敗 mac_no:{$mac_no}";
                                                    `echo "$temp_msg" >> /tmp/equipment_write_error2.log`;
                                                }
                                            }
                                        }
                                    }
                                }
                            }
                        }
                    } else {                    // Counter File にレコードがないので状態のみチェックし書込み
                        ///// 現在のデータベースの最新レコードを取り込む
                        $query = "
                            SELECT mac_state, work_cnt FROM equip_work_log2
                            WHERE
                                equip_index(mac_no, siji_no, koutei, date_time) < '{$mac_no}{$siji_no}{$koutei}99999999999999'
                                AND
                                equip_index(mac_no, siji_no, koutei, date_time) > '{$mac_no}{$siji_no}{$koutei}00000000000000'
                            ORDER BY equip_index(mac_no, siji_no, koutei, date_time) DESC limit 1
                        ";
                        $res = array();
                        if ( ($rows = getResult($query, $res)) >= 1) {  // レコードがあれば状態をチェックする
                            if ($res[0]['mac_state'] != $state) {       // 状態が違えば書込む
                                $work_cnt  = $res[0]['work_cnt'];       // 前回の加工数を使う
                                // $date_time  = date('Ymd His.');             // 現在の時間を使う PostgreSQLのTIMESTAMP型に変更
                                // $date_time .= substr(microtime(), 2, 6);    // TIMESTAMP(6)で重複キーを回避
                                $date_time  = date('Ymd His');          // 現在の時間を使う PostgreSQLのTIMESTAMP型に変更
                                $mac_state = $state;
                                $query = "insert into equip_work_log2
                                    (mac_no, date_time, mac_state, work_cnt, siji_no, koutei)
                                    values($mac_no, '$date_time', $mac_state, $work_cnt, $siji_no, $koutei)";
                                if (query_affected_trans($con, $query) <= 0) {
                                    $temp_msg = "fwserver1 Counter File があるがレコードが無い場合のデータベースの書き込みに失敗 mac_no={$mac_no}";
                                    `echo "$temp_msg" >> /tmp/equipment_write_error2.log`;
                                    // query_affected_trans($con, 'rollback');         // transaction rollback
                                    // exit();
                                }
                            }
                        } else {            // 初回のため無条件に書込む
                            $work_cnt  = 0;             // 初回の場合は０
                            $date_time = date('Ymd His');   // 現在の時間を使う PostgreSQLのTIMESTAMP型に変更
                            $mac_state = $state;
                            $query = "insert into equip_work_log2
                                (mac_no, date_time, mac_state, work_cnt, siji_no, koutei)
                                values($mac_no, '$date_time', $mac_state, $work_cnt, $siji_no, $koutei)";
                            if (query_affected_trans($con, $query) <= 0) {
                                $temp_msg = "fwserver1 Counter File があるがレコードが無い場合の初回データベースの書き込みに失敗 mac_no={$mac_no}";
                                `echo "$temp_msg" >> /tmp/equipment_write_error2.log`;
                                // query_affected_trans($con, 'rollback');         // transaction rollback
                                // exit();
                            } else {
                                $query = "select str_timestamp from equip_work_log2_header
                                        where mac_no={$mac_no} and siji_no={$siji_no} and koutei={$koutei}
                                ";
                                if (getUniResTrs($con, $query, $str_timestamp) > 0) {
                                    $query = "select case when cast('$date_time' as TIMESTAMP) < cast('$str_timestamp' as TIMESTAMP)
                                            then (select 1) else (select 0) end";
                                    if (getUniResTrs($con, $query, $check_time) > 0) {
                                        if ($check_time == 1) {
                                            $query = "update equip_work_log2_header set str_timestamp='{$date_time}'
                                                    where mac_no={$mac_no} and siji_no={$siji_no} and koutei={$koutei}";
                                            if (query_affected_trans($con, $query) <= 0) {
                                                $temp_msg = "初回データの日時比較でHeaderのUPDATEに失敗 mac_no:{$mac_no}";
                                                `echo "$temp_msg" >> /tmp/equipment_write_error2.log`;
                                            }
                                        }
                                    }
                                }
                            }
                        }
                    }
                    unlink($cnt_temp);      // 一時ファイルの削除
                } else {
                    echo "カウンターファイルの rename() に失敗\n";
                }
            } else {                    // Counter File がないので状態のみ書込み
                ///// 現在のデータベースの最新レコードを取り込む
                $query = "
                    SELECT mac_state, work_cnt FROM equip_work_log2
                    WHERE
                        equip_index(mac_no, siji_no, koutei, date_time) < '{$mac_no}{$siji_no}{$koutei}99999999999999'
                        AND
                        equip_index(mac_no, siji_no, koutei, date_time) > '{$mac_no}{$siji_no}{$koutei}00000000000000'
                    ORDER BY equip_index(mac_no, siji_no, koutei, date_time) DESC limit 1
                ";
                $res = array();
                if ( ($rows = getResult($query, $res)) >= 1) {  // レコードがあれば状態をチェックする
                    if ($res[0]['mac_state'] != $state) {       // 状態が違えば書込む
                        $work_cnt  = $res[0]['work_cnt'];       // 前回の加工数をそのまま使う
                        // $date_time  = date('Ymd His.');             // 現在の時間を使う PostgreSQLのTIMESTAMP型に変更
                        // $date_time .= substr(microtime(), 2, 6);    // TIMESTAMP(6)で重複キーを回避
                        $date_time  = date('Ymd His');          // 現在の時間を使う PostgreSQLのTIMESTAMP型に変更
                        $mac_state = $state;
                        $query = "insert into equip_work_log2
                            (mac_no, date_time, mac_state, work_cnt, siji_no, koutei)
                            values($mac_no, '$date_time', $mac_state, $work_cnt, $siji_no, $koutei)";
                        if (query_affected_trans($con, $query) <= 0) {
                            $temp_msg = date('Y-m-d H:i:s ') . "fwserver1 equip_work_log2 へ状態書込みに失敗";
                            `echo "$temp_msg" >> /tmp/equipment_write_error2.log`;
                            // query_affected_trans($con, 'rollback');         // transaction rollback
                            // exit();
                        }
                    }
                } else {        // 初回のため無条件に書込む
                    $work_cnt  = 0;             // 初回の場合は０
                    $date_time = date('Ymd His');       // 現在の時間を使う PostgreSQLのTIMESTAMP型に変更
                    $mac_state = $state;
                    $query = "insert into equip_work_log2
                        (mac_no, date_time, mac_state, work_cnt, siji_no, koutei)
                        values($mac_no, '$date_time', $mac_state, $work_cnt, $siji_no, $koutei)";
                    if (query_affected_trans($con, $query) <= 0) {
                        $temp_msg = date('Y-m-d H:i:s ') . "fwserver1 equip_work_log2 へ初回の状態書込みに失敗";
                        `echo "$temp_msg" >> /tmp/equipment_write_error2.log`;
                        // query_affected_trans($con, 'rollback');         // transaction rollback
                        // exit();
                    } else {
                        $query = "select str_timestamp from equip_work_log2_header
                                where mac_no={$mac_no} and siji_no={$siji_no} and koutei={$koutei}
                        ";
                        if (getUniResTrs($con, $query, $str_timestamp) > 0) {
                            $query = "select case when cast('$date_time' as TIMESTAMP) < cast('$str_timestamp' as TIMESTAMP)
                                    then (select 1) else (select 0) end";
                            if (getUniResTrs($con, $query, $check_time) > 0) {
                                if ($check_time == 1) {
                                    $query = "update equip_work_log2_header set str_timestamp='{$date_time}'
                                            where mac_no={$mac_no} and siji_no={$siji_no} and koutei={$koutei}";
                                    if (query_affected_trans($con, $query) <= 0) {
                                        $temp_msg = "初回データの日時比較でHeaderのUPDATEに失敗 mac_no:{$mac_no}";
                                        `echo "$temp_msg" >> /tmp/equipment_write_error2.log`;
                                    }
                                }
                            }
                        }
                    }
                }
            }
            /////////// commit トランザクション終了
            query_affected_trans($con, 'commit');
        } else {
            echo "データベースの接続1に失敗\n";
            exit();
        }
    }
}
////////// FTP Close
if ($ftp_con) {
    ftp_close($ftp_con);
}
}   // $ftp_flg End





/****************************** FWS2方式 *********************************/
$ftp_flg = true;
////////// FTP CONNECT
if ( !($ftp_con = ftp_connect(FWS2)) ) {   // テスト用にリテラル表示
    $error_msg = date('Y-m-d H:i:s ') . "fwserver2のFTPの接続に失敗しました。";
    `echo "$error_msg" >> /tmp/equipment_ftp_error.log`;
    $ftp_flg = false;
} else {
    if (!@ftp_login($ftp_con, FWS_USER, FWS_PASS)) {      // 2004/03/29 エラーメッセージを抑制
        $error_msg = date('Y-m-d H:i:s ') . "FWS2のFTPのloginに失敗しました。";
        `echo "$error_msg" >> /tmp/equipment_ftp_error.log`;
        $ftp_flg = false;
    }
}

if ($ftp_flg) {
////////// 稼動中に関係なく 機械マスター監視する機械番号を取得 (物理状態を24時間監視するため)
$query = "select mac_no, csv_flg from equip_machine_master2 where csv_flg = 3 and survey = 'Y'";
                        // Netmoni=1 fwserver1=2 fwserver2=3 fwserver3=4 fwserver4=5 ... , Net&fws1=101
$res_key = array();
if ( ($rows_key = getResult($query, $res_key)) >= 1) {      // レコードが１以上あれば
    for ($i=0; $i<$rows_key; $i++) {                        // １レコードずつ処理
        ///// insert 用 変数 初期化
        $mac_no   = $res_key[$i]['mac_no'];
        $csv_flg  = $res_key[$i]['csv_flg'];
        /////////// FTP上のファイルの存在チェック
        if (ftp_size($ftp_con, "/home/fws/usr/{$mac_no}_work_state.log") != -1) {
            /////////// FTP rename
            if (ftp_rename($ftp_con, "/home/fws/usr/{$mac_no}_work_state.log", "/home/fws/usr/{$mac_no}_work_state.tmp")) {
                /////////// FTP Download
                if (ftp_get($ftp_con, "/home/fws/{$mac_no}_work_state.log", "/home/fws/usr/{$mac_no}_work_state.tmp", FTP_ASCII)) {
                    // echo 'FTPのDownloadに成功しました。';
                    ftp_delete($ftp_con, "/home/fws/usr/{$mac_no}_work_state.tmp");  // 旧ファイルは削除
                } else {
                    // echo 'FTPのDownloadに失敗しました。';
                    `/bin/rm -f /home/fws/{$mac_no}_work_state.log`;
                }
            } else {
                // echo 'FTPのrenameに失敗しました。';
                `/bin/rm -f /home/fws/{$mac_no}_work_state.log`;
            }
        }
        /////////// begin トランザクション開始
        if ($con = db_connect()) {
            query_affected_trans($con, 'begin');
            ///// State Log File Check        現在の状態(運転中・停止中)・日時を取得 電源OFFも取得OK
            $state_file = "/home/fws/{$mac_no}_work_state.log";     // ディレクトリ・ファイル名生成
            $state_temp = "/home/fws/{$mac_no}_work_state.tmp";     // Rename 用ディレクトリ・ファイル名生成
            if (file_exists($state_file)) {                         // State Log File があれば
                if (rename($state_file, $state_temp)) {
                    $fp = fopen ($state_temp,'r');
                    $row  = 0;                                  // 全レコード
                    $data = array();                            // 年月日,時間,加工数
                    while ($data[$row] = fgetcsv ($fp, 50, ',')) {
                        if ($data[$row][0] != '') {
                            $row++;
                        }
                    }
                    for ($j=0; $j<$row; $j++) {         // Status File にレコードがあれば状態と日時を書込む
                        if ($data[$j][2] == 'auto') {   // ファイルから物理状態番号を取得
                            $state_p = 1;               // 運転中(自動運転)
                        } elseif ($data[$j][2] == 'stop') {
                            $state_p = 3;               // 停止中
                        } elseif ($data[$j][2] == 'on') {
                            $state_p = 3;               // 電源ONの場合のDefault値は停止中=3
                        } else {
                            $state_p = 0;               // 電源OFF "off" の予定
                        }
                        $date_time = $data[$j][0] . " " . $data[$j][1];     // timestamp型の変数生成 注意
                        $state_name = equip_machine_state($mac_no, $state_p, $txt_color, $bg_color);
                        $query = "insert into equip_mac_state_log2
                            (mac_no, state, date_time, state_name, state_type)
                            values($mac_no, $state_p, '$date_time', '$state_name', $csv_flg)";
                        if (query_affected_trans($con, $query) <= 0) {
                            $temp_msg = "$query\n date_time:$date_time mac_no:$mac_no state:$state_p j=$j";
                            `echo "$temp_msg" >> /tmp/equipment_write_error2.log`;
                            // query_affected_trans($con, "rollback");         // transaction rollback
                            // exit();
                        }
                    }
                    unlink($state_temp);        // 一時ファイルの削除
                } else {
                    echo "ステータスファイルの rename() に失敗\n";
                }
            }
            ///// State Log File 処理終了
            /////////// commit トランザクション終了
            query_affected_trans($con, 'commit');
        } else {
            echo "データベースの接続2に失敗\n";
            exit();
        }
    }
}
////////// 稼動中の機械�發鬟悒奪澄璽侫．ぅ襪�ら取得 & 機械マスターから機械名を取得
$query = "select mac_no, siji_no, koutei, parts_no, plan_cnt, mac_name, csv_flg from equip_work_log2_header
        left outer join equip_machine_master2 using(mac_no)
        where work_flg is TRUE and csv_flg = 3 and survey = 'Y'
";      // Netmoni=1&101 fwserver1=2 fwserver2=3 fwserver3=4 fwserver4=5 fwserver5=6 ...
$res_key = array();
if ( ($rows_key = getResult($query, $res_key)) >= 1) {      // レコードが１以上あれば
    for ($i=0; $i<$rows_key; $i++) {                        // １レコードずつ処理
        ///// insert 用 変数 初期化
        $mac_no   = $res_key[$i]['mac_no'];
        $siji_no  = $res_key[$i]['siji_no'];
        $parts_no = $res_key[$i]['parts_no'];
        $koutei   = $res_key[$i]['koutei'];
        $plan_cnt = $res_key[$i]['plan_cnt'];
        $mac_name = $res_key[$i]['mac_name'];
        $csv_flg  = $res_key[$i]['csv_flg'];
        /////////// FTP上のファイルの存在チェック
        if (ftp_size($ftp_con, "/home/fws/usr/{$mac_no}-bcd1") != -1) {
            /////////// FTP Download
            if (!ftp_get($ftp_con, "/home/fws/{$mac_no}-bcd1", "/home/fws/usr/{$mac_no}-bcd1", FTP_ASCII)) {
                // echo 'FTPのDownloadに失敗しました。';
            }
        } else {
            if (file_exists("/home/fws/{$mac_no}-bcd1")) {   // 旧ファイルがあれば削除
                unlink("/home/fws/{$mac_no}-bcd1");
            }
        }
        /////////// FTP上のファイルの存在チェック
        if (ftp_size($ftp_con, "/home/fws/usr/{$mac_no}-bcd2") != -1) {
            if (!ftp_get($ftp_con, "/home/fws/{$mac_no}-bcd2", "/home/fws/usr/{$mac_no}-bcd2", FTP_ASCII)) {
                // echo 'FTPのDownloadに失敗しました。';
            }
        } else {
            if (file_exists("/home/fws/{$mac_no}-bcd2")) {   // 旧ファイルがあれば削除
                unlink("/home/fws/{$mac_no}-bcd2");
            }
        }
        /////////// FTP上のファイルの存在チェック
        if (ftp_size($ftp_con, "/home/fws/usr/{$mac_no}-bcd4") != -1) {
            if (!ftp_get($ftp_con, "/home/fws/{$mac_no}-bcd4", "/home/fws/usr/{$mac_no}-bcd4", FTP_ASCII)) {
                // echo 'FTPのDownloadに失敗しました。';
            }
        } else {
            if (file_exists("/home/fws/{$mac_no}-bcd4")) {   // 旧ファイルがあれば削除
                unlink("/home/fws/{$mac_no}-bcd4");
            }
        }
        /////////// FTP上のファイルの存在チェック
        if (ftp_size($ftp_con, "/home/fws/usr/{$mac_no}-bcd8") != -1) {
            if (!ftp_get($ftp_con, "/home/fws/{$mac_no}-bcd8", "/home/fws/usr/{$mac_no}-bcd8", FTP_ASCII)) {
                // echo 'FTPのDownloadに失敗しました。';
            }
        } else {
            if (file_exists("/home/fws/{$mac_no}-bcd8")) {   // 旧ファイルがあれば削除
                unlink("/home/fws/{$mac_no}-bcd8");
            }
        }
        ///// State File Check BCD演算  現在の状態を取得
        $bcd1_file = "/home/fws/{$mac_no}-bcd1";            // BCD演算用ファイル名生成
        $bcd2_file = "/home/fws/{$mac_no}-bcd2";
        $bcd4_file = "/home/fws/{$mac_no}-bcd4";
        $bcd8_file = "/home/fws/{$mac_no}-bcd8";
        $state_bcd = 0;                                     // 初期化
        if (file_exists($bcd1_file)) {
            $state_bcd += 1;
        }
        if (file_exists($bcd2_file)) {
            $state_bcd += 2;
        }
        if (file_exists($bcd4_file)) {
            $state_bcd += 4;
        }
        if (file_exists($bcd8_file)) {
            $state_bcd += 8;
        }
        $query = "select state from equip_mac_state_log2 where equip_index2(mac_no,date_time) < '{$mac_no}99999999999999' order by equip_index2(mac_no,date_time) DESC limit 1";
        if (getUniResult($query, $state_p) > 0) {
            // 物理信号がセットされていれば
            $state = state_check($state_p, $state_bcd);   // 物理状態信号とスイッチの状態とで適正値をチェック
        } else {
            $state = 0;     // 状態データが無いので無条件に電源off=0
        }
        /////////// FTP上のファイルの存在チェック
        if (ftp_size($ftp_con, "/home/fws/usr/{$mac_no}_work_cnt.log") != -1) {
            /////////// FTP rename
            if (ftp_rename($ftp_con, "/home/fws/usr/{$mac_no}_work_cnt.log", "/home/fws/usr/{$mac_no}_work_cnt.tmp")) {
                /////////// FTP Download
                if (ftp_get($ftp_con, "/home/fws/{$mac_no}_work_cnt.log", "/home/fws/usr/{$mac_no}_work_cnt.tmp", FTP_ASCII)) {
                    // echo 'FTPのDownloadに成功しました。';
                    ftp_delete($ftp_con, "/home/fws/usr/{$mac_no}_work_cnt.tmp");  // 旧ファイルは削除
                } else {
                    // echo 'FTPのDownloadに失敗しました。';
                    `/bin/rm -f /home/fws/{$mac_no}_work_cnt.log`;
                }
            } else {
                // echo 'FTPのrenameに失敗しました。';
                `/bin/rm -f /home/fws/{$mac_no}_work_cnt.log`;
            }
        }
        /////////// begin トランザクション開始
        if ($con = db_connect()) {
            query_affected_trans($con, 'begin');
            ///// Counter File Check        現在の加工数・日時を取得
            $cnt_file = "/home/fws/{$mac_no}_work_cnt.log";     // ディレクトリ・ファイル名生成
            $cnt_temp = "/home/fws/{$mac_no}_work_cnt.tmp";     // Rename 用ディレクトリ・ファイル名生成
            if (file_exists($cnt_file)) {                       // Counter File があれば
                if (rename($cnt_file, $cnt_temp)) {
                    $fp = fopen ($cnt_temp,'r');
                    $row  = 0;                                  // 全レコード
                    $data = array();                            // 年月日,時間,加工数
                    while ($data[$row] = fgetcsv ($fp, 50, ',')) {
                        if ($data[$row][0] != '') {
                            $row++;
                        }
                    }
                    if ($row >= 1) {            // Counter File にレコードがあれば状態と加工数書込み
                        ///// 現在のデータベースの最新レコードを取り込む
                        $query = "
                            SELECT mac_state, work_cnt FROM equip_work_log2
                            WHERE
                                equip_index(mac_no, siji_no, koutei, date_time) < '{$mac_no}{$siji_no}{$koutei}99999999999999'
                                AND
                                equip_index(mac_no, siji_no, koutei, date_time) > '{$mac_no}{$siji_no}{$koutei}00000000000000'
                            ORDER BY equip_index(mac_no, siji_no, koutei, date_time) DESC limit 1
                        ";
                        $res = array();
                        if ( ($rows = getResult($query, $res)) >= 1) {  // 前回の加工数にプラスして書込み
                            for ($j=0; $j<$row; $j++) {
                                $work_cnt  = $res[0]['work_cnt'] + ($j + 1);      // Counter UP
                                $date_time = $data[$j][0] . ' ' . $data[$j][1];     // PostgreSQLのTIMESTAMP型に変更
                                if ( ($state == 1) || ($state == 8) || ($state == 5) ) {     // 自動運転か無人運転又は段取中のはずなのでチェックして必要なら訂正
                                    $mac_state = $state;
                                } else {
                                    $mac_state = 1;     // Counterが進んでいるのに自動でも無人又は段取でもない場合は強制的に自動運転にする
                                }
                                $query = "insert into equip_work_log2
                                    (mac_no, date_time, mac_state, work_cnt, siji_no, koutei)
                                    values($mac_no, '$date_time', $mac_state, $work_cnt, $siji_no, $koutei)";
                                if (query_affected_trans($con, $query) <= 0) {
                                    $temp_msg = "FWS2 Counterの書き込みに失敗 レコード:$j : $mac_no : $date_time : $mac_state \n";
                                    $temp_msg .= $query;    // debug 用
                                    `echo "$temp_msg" >> /tmp/equipment_write_error2.log`;
                                    // query_affected_trans($con, 'rollback');         // transaction rollback
                                    // exit();
                                }
                            }
                        } else {                    // データベースが初回のため無条件に書込み
                            for ($j=0; $j<$row; $j++) {
                                $work_cnt  =  ($j + 1);   // 初回の場合はここが違う
                                $date_time = $data[$j][0] . ' ' . $data[$j][1];     // PostgreSQLのTIMESTAMP型に変更
                                // $mac_state = $state;     // 初回の場合は過去のデータを取込む可能性が高いため以下は必要
                                if ( ($state == 1) || ($state == 8) || ($state == 5) ) {     // 自動運転か無人運転又は段取中のはずなのでチェックして必要なら訂正
                                    $mac_state = $state;
                                } else {
                                    $mac_state = 1;     // Counterが進んでいるのに自動でも無人又は段取でもない場合は強制的に自動運転にする
                                }
                                $query = "insert into equip_work_log2
                                    (mac_no, date_time, mac_state, work_cnt, siji_no, koutei)
                                    values($mac_no, '$date_time', $mac_state, $work_cnt, $siji_no, $koutei)";
                                if (query_affected_trans($con, $query) <= 0) {
                                    $temp_msg = "fwserver2 初回データベースの書き込みに失敗 レコード:$j\n$query";
                                    `echo "$temp_msg" >> /tmp/equipment_write_error2.log`;
                                    // query_affected_trans($con, 'rollback');         // transaction rollback
                                    // exit();
                                } else {
                                    $query = "select str_timestamp from equip_work_log2_header
                                            where mac_no={$mac_no} and siji_no={$siji_no} and koutei={$koutei}
                                    ";
                                    if (getUniResTrs($con, $query, $str_timestamp) > 0) {
                                        $query = "select case when cast('$date_time' as TIMESTAMP) < cast('$str_timestamp' as TIMESTAMP)
                                                then (select 1) else (select 0) end";
                                        if (getUniResTrs($con, $query, $check_time) > 0) {
                                            if ($check_time == 1) {
                                                $query = "update equip_work_log2_header set str_timestamp='{$date_time}'
                                                        where mac_no={$mac_no} and siji_no={$siji_no} and koutei={$koutei}";
                                                if (query_affected_trans($con, $query) <= 0) {
                                                    $temp_msg = "初回データの日時比較でHeaderのUPDATEに失敗 mac_no:{$mac_no}";
                                                    `echo "$temp_msg" >> /tmp/equipment_write_error2.log`;
                                                }
                                            }
                                        }
                                    }
                                }
                            }
                        }
                    } else {                    // Counter File にレコードがないので状態のみチェックし書込み
                        ///// 現在のデータベースの最新レコードを取り込む
                        $query = "
                            SELECT mac_state, work_cnt FROM equip_work_log2
                            WHERE
                                equip_index(mac_no, siji_no, koutei, date_time) < '{$mac_no}{$siji_no}{$koutei}99999999999999'
                                AND
                                equip_index(mac_no, siji_no, koutei, date_time) > '{$mac_no}{$siji_no}{$koutei}00000000000000'
                            ORDER BY equip_index(mac_no, siji_no, koutei, date_time) DESC limit 1
                        ";
                        $res = array();
                        if ( ($rows = getResult($query, $res)) >= 1) {  // レコードがあれば状態をチェックする
                            if ($res[0]['mac_state'] != $state) {       // 状態が違えば書込む
                                $work_cnt  = $res[0]['work_cnt'];       // 前回の加工数を使う
                                // $date_time  = date('Ymd His.');             // 現在の時間を使う PostgreSQLのTIMESTAMP型に変更
                                // $date_time .= substr(microtime(), 2, 6);    // TIMESTAMP(6)で重複キーを回避
                                $date_time  = date('Ymd His');          // 現在の時間を使う PostgreSQLのTIMESTAMP型に変更
                                $mac_state = $state;
                                $query = "insert into equip_work_log2
                                    (mac_no, date_time, mac_state, work_cnt, siji_no, koutei)
                                    values($mac_no, '$date_time', $mac_state, $work_cnt, $siji_no, $koutei)";
                                if (query_affected_trans($con, $query) <= 0) {
                                    $temp_msg = "fwserver2 Counter File があるがレコードが無い場合のデータベースの書き込みに失敗 mac_no={$mac_no}";
                                    `echo "$temp_msg" >> /tmp/equipment_write_error2.log`;
                                    // query_affected_trans($con, 'rollback');         // transaction rollback
                                    // exit();
                                }
                            }
                        } else {            // 初回のため無条件に書込む
                            $work_cnt  = 0;             // 初回の場合は０
                            $date_time = date('Ymd His');   // 現在の時間を使う PostgreSQLのTIMESTAMP型に変更
                            $mac_state = $state;
                            $query = "insert into equip_work_log2
                                (mac_no, date_time, mac_state, work_cnt, siji_no, koutei)
                                values($mac_no, '$date_time', $mac_state, $work_cnt, $siji_no, $koutei)";
                            if (query_affected_trans($con, $query) <= 0) {
                                $temp_msg = "fwserver2 Counter File があるがレコードが無い場合の初回データベースの書き込みに失敗 mac_no={$mac_no}";
                                `echo "$temp_msg" >> /tmp/equipment_write_error2.log`;
                                // query_affected_trans($con, 'rollback');         // transaction rollback
                                // exit();
                            } else {
                                $query = "select str_timestamp from equip_work_log2_header
                                        where mac_no={$mac_no} and siji_no={$siji_no} and koutei={$koutei}
                                ";
                                if (getUniResTrs($con, $query, $str_timestamp) > 0) {
                                    $query = "select case when cast('$date_time' as TIMESTAMP) < cast('$str_timestamp' as TIMESTAMP)
                                            then (select 1) else (select 0) end";
                                    if (getUniResTrs($con, $query, $check_time) > 0) {
                                        if ($check_time == 1) {
                                            $query = "update equip_work_log2_header set str_timestamp='{$date_time}'
                                                    where mac_no={$mac_no} and siji_no={$siji_no} and koutei={$koutei}";
                                            if (query_affected_trans($con, $query) <= 0) {
                                                $temp_msg = "初回データの日時比較でHeaderのUPDATEに失敗 mac_no:{$mac_no}";
                                                `echo "$temp_msg" >> /tmp/equipment_write_error2.log`;
                                            }
                                        }
                                    }
                                }
                            }
                        }
                    }
                    unlink($cnt_temp);      // 一時ファイルの削除
                } else {
                    echo "カウンターファイルの rename() に失敗\n";
                }
            } else {                    // Counter File がないので状態のみ書込み
                ///// 現在のデータベースの最新レコードを取り込む
                $query = "
                    SELECT mac_state, work_cnt FROM equip_work_log2
                    WHERE
                        equip_index(mac_no, siji_no, koutei, date_time) < '{$mac_no}{$siji_no}{$koutei}99999999999999'
                        AND
                        equip_index(mac_no, siji_no, koutei, date_time) > '{$mac_no}{$siji_no}{$koutei}00000000000000'
                    ORDER BY equip_index(mac_no, siji_no, koutei, date_time) DESC limit 1
                ";
                $res = array();
                if ( ($rows = getResult($query, $res)) >= 1) {  // レコードがあれば状態をチェックする
                    if ($res[0]['mac_state'] != $state) {       // 状態が違えば書込む
                        $work_cnt  = $res[0]['work_cnt'];       // 前回の加工数をそのまま使う
                        // $date_time  = date('Ymd His.');             // 現在の時間を使う PostgreSQLのTIMESTAMP型に変更
                        // $date_time .= substr(microtime(), 2, 6);    // TIMESTAMP(6)で重複キーを回避
                        $date_time  = date('Ymd His');          // 現在の時間を使う PostgreSQLのTIMESTAMP型に変更
                        $mac_state = $state;
                        $query = "insert into equip_work_log2
                            (mac_no, date_time, mac_state, work_cnt, siji_no, koutei)
                            values($mac_no, '$date_time', $mac_state, $work_cnt, $siji_no, $koutei)";
                        if (query_affected_trans($con, $query) <= 0) {
                            $temp_msg = date('Y-m-d H:i:s ') . "fwserver2 equip_work_log2 へ状態書込みに失敗";
                            `echo "$temp_msg" >> /tmp/equipment_write_error2.log`;
                            // query_affected_trans($con, 'rollback');         // transaction rollback
                            // exit();
                        }
                    }
                } else {        // 初回のため無条件に書込む
                    $work_cnt  = 0;             // 初回の場合は０
                    $date_time = date('Ymd His');       // 現在の時間を使う PostgreSQLのTIMESTAMP型に変更
                    $mac_state = $state;
                    $query = "insert into equip_work_log2
                        (mac_no, date_time, mac_state, work_cnt, siji_no, koutei)
                        values($mac_no, '$date_time', $mac_state, $work_cnt, $siji_no, $koutei)";
                    if (query_affected_trans($con, $query) <= 0) {
                        $temp_msg = date('Y-m-d H:i:s ') . "fwserver2 equip_work_log2 へ初回の状態書込みに失敗";
                        `echo "$temp_msg" >> /tmp/equipment_write_error2.log`;
                        // query_affected_trans($con, 'rollback');         // transaction rollback
                        // exit();
                    } else {
                        $query = "select str_timestamp from equip_work_log2_header
                                where mac_no={$mac_no} and siji_no={$siji_no} and koutei={$koutei}
                        ";
                        if (getUniResTrs($con, $query, $str_timestamp) > 0) {
                            $query = "select case when cast('$date_time' as TIMESTAMP) < cast('$str_timestamp' as TIMESTAMP)
                                    then (select 1) else (select 0) end";
                            if (getUniResTrs($con, $query, $check_time) > 0) {
                                if ($check_time == 1) {
                                    $query = "update equip_work_log2_header set str_timestamp='{$date_time}'
                                            where mac_no={$mac_no} and siji_no={$siji_no} and koutei={$koutei}";
                                    if (query_affected_trans($con, $query) <= 0) {
                                        $temp_msg = "初回データの日時比較でHeaderのUPDATEに失敗 mac_no:{$mac_no}";
                                        `echo "$temp_msg" >> /tmp/equipment_write_error2.log`;
                                    }
                                }
                            }
                        }
                    }
                }
            }
            /////////// commit トランザクション終了
            query_affected_trans($con, 'commit');
        } else {
            echo "データベースの接続2に失敗\n";
            exit();
        }
    }
}
////////// FTP Close
if ($ftp_con) {
    ftp_close($ftp_con);
}
}   // $ftp_flg End





/****************************** Netmoni OR (Netmoni & FWS1)方式 *********************************/
$ftp_flg = true;
////////// FTP CONNECT Netmoni
if ( !($ftp_con = ftp_connect(NET_HOST)) ) {   // Netmoni4のIPアドレス
    $error_msg = date('Y-m-d H:i:s ') . "NetmoniのFTPの接続に失敗しました。";
    `echo "$error_msg" >> /tmp/equipment_ftp_error.log`;
    // echo 'FTPの接続に失敗しました。';
    $ftp_flg = false;
} else {
    if (!@ftp_login($ftp_con, NET_USER, NET_PASS)) {      // 2004/03/29 エラーメッセージを抑制
        $error_msg = date('Y-m-d H:i:s ') . "NetmoniのFTPのloginに失敗しました。";
        `echo "$error_msg" >> /tmp/equipment_ftp_error.log`;
        $ftp_flg = false;
    }
}
////////// FTP CONNECT FWS1
if ( !($ftp_con_fws1 = ftp_connect(FWS1)) ) {
    $error_msg = date('Y-m-d H:i:s ') . "FWS1 ftp_connect error state_check_netmoni()";
    `echo "$error_msg" >> /tmp/equipment_ftp_error.log`;
    $ftp_flg = false;
} else {
    if (!@ftp_login($ftp_con_fws1, FWS_USER, FWS_PASS)) {
        $error_msg = date('Y-m-d H:i:s ') . "FWS1 ftp_login error state_check_netmoni()";
        `echo "$error_msg" >> /tmp/equipment_ftp_error.log`;
        $ftp_flg = false;
    }
}

if ($ftp_flg) {
    ////////// 稼動中の機械�發鬟悒奪澄璽侫．ぅ襪�ら取得 & 機械マスターから機械名を取得
    $query = "select mac_no, siji_no, koutei, parts_no, plan_cnt, mac_name, csv_flg from equip_work_log2_header
            left outer join equip_machine_master2 using(mac_no)
            where work_flg is TRUE and (csv_flg = 101 or csv_flg = 1) and survey = 'Y'";
                 // Netmoni=1 FWS1=2 FWS2=3  ... 101=Netmoni & FWS 方式の共通化
    $res_key = array();
    if ( ($rows_key = getResult($query, $res_key)) >= 1) {      // レコードが１以上あれば
        for ($i=0; $i<$rows_key; $i++) {                        // １レコードずつ処理
            ///// insert 用 変数 初期化
            $mac_no   = $res_key[$i]['mac_no'];
            $siji_no  = $res_key[$i]['siji_no'];
            $parts_no = $res_key[$i]['parts_no'];
            $koutei   = $res_key[$i]['koutei'];
            $plan_cnt = $res_key[$i]['plan_cnt'];
            $mac_name = $res_key[$i]['mac_name'];
            $csv_flg  = $res_key[$i]['csv_flg'];
            $log_name = "{$mac_no}{$siji_no}{$parts_no}{$koutei}{$plan_cnt}.csv";   // リモートのログファイル名
            $log_temp = "{$mac_no}{$siji_no}{$parts_no}{$koutei}{$plan_cnt}.tmp";   // リモートの一時ファイル名
            /////////// FTP上のファイルの存在チェック
            if (ftp_size($ftp_con, $log_name) != -1) {
                /////////// FTP上の一時ファイルの存在チェック(前回トラブルで削除できず残っている場合の対応)
                if (ftp_size($ftp_con, $log_temp) != -1) {
                    ftp_delete($ftp_con, $log_temp);  // 前回の旧一時ファイルは削除
                }
                /////////// FTP rename
                if (ftp_rename($ftp_con, $log_name, $log_temp)) {
                    /////////// FTP Download
                    if (ftp_get($ftp_con, "/home/netmoni4/data/{$log_name}", $log_temp, FTP_ASCII)) {
                        // echo 'FTPのDownloadに成功しました。';
                        ftp_delete($ftp_con, $log_temp);  // 旧ファイルは削除
                    } else {
                        $error_msg = date('Y-m-d H:i:s ') . "Netmoniのftp_get()に失敗しました。";
                        `echo "$error_msg" >> /tmp/equipment_ftp_error.log`;
                    }
                } else {
                    $error_msg = date('Y-m-d H:i:s ') . "Netmoniのftp_rename()に失敗しました。";
                    `echo "$error_msg" >> /tmp/equipment_ftp_error.log`;
                }
            }
            ///////////// DownloadしたlogをDBへ登録
            $file_name = EQUIP_LOG_DIR . $log_name;
            // フィルの存在チェック
            if (file_exists($file_name)) {          // ファイルの存在チェック
                $fp      = fopen ($file_name, 'r');
                $data    = array();
                $flag    = array();
                $sel_cnt = 1;           // 変化のあったデータ(データベース取り込み用件数)
                $row     = 0;           // 全レコード(＋１が必要)
                if ($con = db_connect()) {
                    query_affected_trans($con, 'begin');
                }
                while ($data[$row] = fgetcsv ($fp, 200, ',')) {
                    $query_chk = "SELECT state
                                    FROM
                                        equip_mac_state_log2
                                    WHERE
                                        equip_index2(mac_no, date_time) < '{$mac_no}99999999999999'
                                    ORDER BY equip_index2(mac_no, date_time) DESC
                                    limit 1
                    ";
                    if (getUniResTrs($con, $query_chk, $res_chk) <= 0) {    // トランザクション内での 照会専用クエリー
                        // 初回のためデータ無しと見なす
                        $res_chk = -1;  // ありえない数値をセット
                    }
                    if ($res_chk != $data[$row][5]) {   // 状態が変化しているかチェック 変化していれば書込む
                        $date_time = $data[$row][1] . " " . $data[$row][2];     // timestamp型の変数生成 注意
                        switch ($data[$row][5]) {
                        case 0:
                            $state_name = "電源OFF";
                            break;
                        case 1:
                            $state_name = "自動運転";
                            break;
                        case 2:
                            $state_name = "アラーム";
                            break;
                        case 3:
                            $state_name = "停止中";
                            break;
                        case 4:
                            $state_name = "Net起動";
                            break;
                        case 5:
                            $state_name = "Net終了";
                            break;
                        default:
                            $state_name = "未登録";
                        }
                        $query = "insert into equip_mac_state_log2
                                    (mac_no, state, date_time, state_name, state_type)
                                    values($mac_no, {$data[$row][5]}, '$date_time', '$state_name', $csv_flg)
                        ";
                        if (query_affected_trans($con, $query) <= 0) {
                            $temp_msg = "$query\n date_time:$date_time mac_no:$mac_no state:{$data[$row][5]} j=$j";
                            `echo "$temp_msg" >> /tmp/equipment_write_error2.log`;
                            // query_affected_trans($con, "rollback");         // transaction rollback
                        }
                    }
                    $data[$row][5] = state_check_netmoni($ftp_con_fws1, $data[$row][5], $mac_no);   // 物理状態信号とスイッチの状態とで適正値をチェック 2004/07/12 Add k.kobayashi
                    if ($row == 0) {            // 初回のレコードなら無条件でフラグを立てる
                        $flag[$row] = 1;
                        $sel_cnt++;
                    } elseif ( ($data[$row][5]!=$data[$row-1][5]) || ($data[$row][10]!=$data[$row-1][10]) || ($data[$row][14]!=$data[$row-1][14]) ) {
                        $flag[$row] = 1;
                        $sel_cnt++;
                    } else {
                        $flag[$row] = 0;
                    }
                    $row++;
                }
                fclose($fp);
                unlink($file_name);      // ログファイルを削除
                
                ///// equip_work_log2 への書込み
                for ($cnt=0; $cnt<$row; $cnt++) {   // row に注意
                    $mac_no    = $data[$cnt][4];                            // 機械番号
                    $date_time = $data[$cnt][1] . ' ' . $data[$cnt][2];     // TIMESTAMP型で登録
                    $mac_state = $data[$cnt][5];                            // 機械の状態
                    $query = "select mac_state
                                    ,work_cnt
                                from
                                    equip_work_log2
                                where
                                    equip_index(mac_no, siji_no, koutei, date_time) > '{$mac_no}{$siji_no}{$koutei}00000000000000'
                                and
                                    equip_index(mac_no, siji_no, koutei, date_time) < '{$mac_no}{$siji_no}{$koutei}99999999999999'
                                order by
                                    equip_index(mac_no, siji_no, koutei, date_time) DESC
                                offset 0 limit 1
                    ";
                    $res = array();
                    if ( ($rows=getResultTrs($con, $query, $res)) >= 1) {
                        if ( ($mac_state == 0) || ($mac_state == 9) ) {     // 電源OFF or 中断だったら前のwork_cntを維持する
                            $work_cnt = $res[0][1];             // 前のwork_cntの取得
                        } else {
                            $work_cnt = $data[$cnt][10];        // 電源OFFでなければCSVの加工数を入れる
                        }
                    } else {
                        $work_cnt  = $data[$cnt][10];       // 取得出来ない場合はCSVの加工数を入れる
                        $res[0][0] = '';                    // 初期化のみ
                        $res[0][1] = '';                    //     〃
                    }
                    ///// mac_state か work_cntが違えば書込む
                    if( ($res[0][0] != $mac_state) || ($res[0][1] != $work_cnt) ) {
                        $insert_qry = "insert into equip_work_log2
                                (mac_no, date_time, mac_state, work_cnt, siji_no, koutei)
                                values($mac_no, '$date_time', $mac_state, $work_cnt, $siji_no, $koutei)
                        ";
                        if (query_affected_trans($con, $insert_qry) <= 0) {
                            $temp_msg = date('Y/m/d H:i:s', mktime()) . "$file_name_backup : 書込みエラー: $mac_no :" . ($cnt+1);
                            `echo "$temp_msg" >> /tmp/equip_netmoni_write_error.log`;
                        }
                    }
                }
                ///// データベースをコミットして終了
                query_affected_trans($con, 'commit');
            }
        }
    }
}

////////// FTP Close
if ($ftp_con) {
    ftp_close($ftp_con);
}
////////// FTP FWS1 Close
if ($ftp_con_fws1) {
    ftp_close($ftp_con_fws1);
}



















/****************************** FWS3方式 *********************************/
$ftp_flg = true;
////////// FTP CONNECT
if ( !($ftp_con = ftp_connect(FWS3)) ) {   // テスト用にリテラル表示
    $error_msg = date('Y-m-d H:i:s ') . "fwserver3のFTPの接続に失敗しました。";
    `echo "$error_msg" >> /tmp/equipment_ftp_error.log`;
    $ftp_flg = false;
} else {
    if (!@ftp_login($ftp_con, FWS_USER, FWS_PASS)) {      // 2004/03/29 エラーメッセージを抑制
        $error_msg = date('Y-m-d H:i:s ') . "FWS3のFTPのloginに失敗しました。";
        `echo "$error_msg" >> /tmp/equipment_ftp_error.log`;
        $ftp_flg = false;
    }
}

if ($ftp_flg) {
////////// 稼動中に関係なく 機械マスター監視する機械番号を取得 (物理状態を24時間監視するため)
$query = "select mac_no, csv_flg from equip_machine_master2 where csv_flg = 4 and survey = 'Y'";
                        // Netmoni=1 fwserver1=2 fwserver2=3 fwserver3=4 fwserver4=5 ... , Net&fws1=101
$res_key = array();
if ( ($rows_key = getResult($query, $res_key)) >= 1) {      // レコードが１以上あれば
    for ($i=0; $i<$rows_key; $i++) {                        // １レコードずつ処理
        ///// insert 用 変数 初期化
        $mac_no   = $res_key[$i]['mac_no'];
        $csv_flg  = $res_key[$i]['csv_flg'];
        /////////// FTP上のファイルの存在チェック
        if (ftp_size($ftp_con, "/home/fws/usr/{$mac_no}_work_state.log") != -1) {
            /////////// FTP rename
            if (ftp_rename($ftp_con, "/home/fws/usr/{$mac_no}_work_state.log", "/home/fws/usr/{$mac_no}_work_state.tmp")) {
                /////////// FTP Download
                if (ftp_get($ftp_con, "/home/fws/{$mac_no}_work_state.log", "/home/fws/usr/{$mac_no}_work_state.tmp", FTP_ASCII)) {
                    // echo 'FTPのDownloadに成功しました。';
                    ftp_delete($ftp_con, "/home/fws/usr/{$mac_no}_work_state.tmp");  // 旧ファイルは削除
                } else {
                    // echo 'FTPのDownloadに失敗しました。';
                    `/bin/rm -f /home/fws/{$mac_no}_work_state.log`;
                }
            } else {
                // echo 'FTPのrenameに失敗しました。';
                `/bin/rm -f /home/fws/{$mac_no}_work_state.log`;
            }
        }
        /////////// begin トランザクション開始
        if ($con = db_connect()) {
            query_affected_trans($con, 'begin');
            ///// State Log File Check        現在の状態(運転中・停止中)・日時を取得 電源OFFも取得OK
            $state_file = "/home/fws/{$mac_no}_work_state.log";     // ディレクトリ・ファイル名生成
            $state_temp = "/home/fws/{$mac_no}_work_state.tmp";     // Rename 用ディレクトリ・ファイル名生成
            if (file_exists($state_file)) {                         // State Log File があれば
                if (rename($state_file, $state_temp)) {
                    $fp = fopen ($state_temp,'r');
                    $row  = 0;                                  // 全レコード
                    $data = array();                            // 年月日,時間,加工数
                    while ($data[$row] = fgetcsv ($fp, 50, ',')) {
                        if ($data[$row][0] != '') {
                            $row++;
                        }
                    }
                    for ($j=0; $j<$row; $j++) {         // Status File にレコードがあれば状態と日時を書込む
                        if ($data[$j][2] == 'auto') {   // ファイルから物理状態番号を取得
                            $state_p = 1;               // 運転中(自動運転)
                        } elseif ($data[$j][2] == 'stop') {
                            $state_p = 3;               // 停止中
                        } elseif ($data[$j][2] == 'on') {
                            $state_p = 3;               // 電源ONの場合のDefault値は停止中=3
                        } else {
                            $state_p = 0;               // 電源OFF "off" の予定
                        }
                        $date_time = $data[$j][0] . " " . $data[$j][1];     // timestamp型の変数生成 注意
                        $state_name = equip_machine_state($mac_no, $state_p, $txt_color, $bg_color);
                        $query = "insert into equip_mac_state_log2
                            (mac_no, state, date_time, state_name, state_type)
                            values($mac_no, $state_p, '$date_time', '$state_name', $csv_flg)";
                        if (query_affected_trans($con, $query) <= 0) {
                            $temp_msg = "$query\n date_time:$date_time mac_no:$mac_no state:$state_p j=$j";
                            `echo "$temp_msg" >> /tmp/equipment_write_error2.log`;
                            // query_affected_trans($con, "rollback");         // transaction rollback
                            // exit();
                        }
                    }
                    unlink($state_temp);        // 一時ファイルの削除
                } else {
                    echo "ステータスファイルの rename() に失敗\n";
                }
            }
            ///// State Log File 処理終了
            /////////// commit トランザクション終了
            query_affected_trans($con, 'commit');
        } else {
            echo "データベースの接続2に失敗\n";
            exit();
        }
    }
}
////////// 稼動中の機械�發鬟悒奪澄璽侫．ぅ襪�ら取得 & 機械マスターから機械名を取得
$query = "select mac_no, siji_no, koutei, parts_no, plan_cnt, mac_name, csv_flg from equip_work_log2_header
        left outer join equip_machine_master2 using(mac_no)
        where work_flg is TRUE and csv_flg = 4 and survey = 'Y'
";
        // Netmoni=1&101 fwserver1=2 fwserver2=3 fwserver3=4 fwserver4=5 fwserver5=6 ...
$res_key = array();
if ( ($rows_key = getResult($query, $res_key)) >= 1) {      // レコードが１以上あれば
    for ($i=0; $i<$rows_key; $i++) {                        // １レコードずつ処理
        ///// insert 用 変数 初期化
        $mac_no   = $res_key[$i]['mac_no'];
        $siji_no  = $res_key[$i]['siji_no'];
        $parts_no = $res_key[$i]['parts_no'];
        $koutei   = $res_key[$i]['koutei'];
        $plan_cnt = $res_key[$i]['plan_cnt'];
        $mac_name = $res_key[$i]['mac_name'];
        $csv_flg  = $res_key[$i]['csv_flg'];
        /////////// FTP上のファイルの存在チェック
        if (ftp_size($ftp_con, "/home/fws/usr/{$mac_no}-bcd1") != -1) {
            /////////// FTP Download
            if (!ftp_get($ftp_con, "/home/fws/{$mac_no}-bcd1", "/home/fws/usr/{$mac_no}-bcd1", FTP_ASCII)) {
                // echo 'FTPのDownloadに失敗しました。';
            }
        } else {
            if (file_exists("/home/fws/{$mac_no}-bcd1")) {   // 旧ファイルがあれば削除
                unlink("/home/fws/{$mac_no}-bcd1");
            }
        }
        /////////// FTP上のファイルの存在チェック
        if (ftp_size($ftp_con, "/home/fws/usr/{$mac_no}-bcd2") != -1) {
            if (!ftp_get($ftp_con, "/home/fws/{$mac_no}-bcd2", "/home/fws/usr/{$mac_no}-bcd2", FTP_ASCII)) {
                // echo 'FTPのDownloadに失敗しました。';
            }
        } else {
            if (file_exists("/home/fws/{$mac_no}-bcd2")) {   // 旧ファイルがあれば削除
                unlink("/home/fws/{$mac_no}-bcd2");
            }
        }
        /////////// FTP上のファイルの存在チェック
        if (ftp_size($ftp_con, "/home/fws/usr/{$mac_no}-bcd4") != -1) {
            if (!ftp_get($ftp_con, "/home/fws/{$mac_no}-bcd4", "/home/fws/usr/{$mac_no}-bcd4", FTP_ASCII)) {
                // echo 'FTPのDownloadに失敗しました。';
            }
        } else {
            if (file_exists("/home/fws/{$mac_no}-bcd4")) {   // 旧ファイルがあれば削除
                unlink("/home/fws/{$mac_no}-bcd4");
            }
        }
        /////////// FTP上のファイルの存在チェック
        if (ftp_size($ftp_con, "/home/fws/usr/{$mac_no}-bcd8") != -1) {
            if (!ftp_get($ftp_con, "/home/fws/{$mac_no}-bcd8", "/home/fws/usr/{$mac_no}-bcd8", FTP_ASCII)) {
                // echo 'FTPのDownloadに失敗しました。';
            }
        } else {
            if (file_exists("/home/fws/{$mac_no}-bcd8")) {   // 旧ファイルがあれば削除
                unlink("/home/fws/{$mac_no}-bcd8");
            }
        }
        ///// State File Check BCD演算  現在の状態を取得
        $bcd1_file = "/home/fws/{$mac_no}-bcd1";            // BCD演算用ファイル名生成
        $bcd2_file = "/home/fws/{$mac_no}-bcd2";
        $bcd4_file = "/home/fws/{$mac_no}-bcd4";
        $bcd8_file = "/home/fws/{$mac_no}-bcd8";
        $state_bcd = 0;                                     // 初期化
        if (file_exists($bcd1_file)) {
            $state_bcd += 1;
        }
        if (file_exists($bcd2_file)) {
            $state_bcd += 2;
        }
        if (file_exists($bcd4_file)) {
            $state_bcd += 4;
        }
        if (file_exists($bcd8_file)) {
            $state_bcd += 8;
        }
        $query = "select state from equip_mac_state_log2 where equip_index2(mac_no,date_time) < '{$mac_no}99999999999999' order by equip_index2(mac_no,date_time) DESC limit 1";
        if (getUniResult($query, $state_p) > 0) {
            // 物理信号がセットされていれば
            $state = state_check($state_p, $state_bcd);   // 物理状態信号とスイッチの状態とで適正値をチェック
        } else {
            $state = 0;     // 状態データが無いので無条件に電源off=0
        }
        /////////// FTP上のファイルの存在チェック
        if (ftp_size($ftp_con, "/home/fws/usr/{$mac_no}_work_cnt.log") != -1) {
            /////////// FTP rename
            if (ftp_rename($ftp_con, "/home/fws/usr/{$mac_no}_work_cnt.log", "/home/fws/usr/{$mac_no}_work_cnt.tmp")) {
                /////////// FTP Download
                if (ftp_get($ftp_con, "/home/fws/{$mac_no}_work_cnt.log", "/home/fws/usr/{$mac_no}_work_cnt.tmp", FTP_ASCII)) {
                    // echo 'FTPのDownloadに成功しました。';
                    ftp_delete($ftp_con, "/home/fws/usr/{$mac_no}_work_cnt.tmp");  // 旧ファイルは削除
                } else {
                    // echo 'FTPのDownloadに失敗しました。';
                    `/bin/rm -f /home/fws/{$mac_no}_work_cnt.log`;
                }
            } else {
                // echo 'FTPのrenameに失敗しました。';
                `/bin/rm -f /home/fws/{$mac_no}_work_cnt.log`;
            }
        }
        /////////// begin トランザクション開始
        if ($con = db_connect()) {
            query_affected_trans($con, 'begin');
            ///// Counter File Check        現在の加工数・日時を取得
            $cnt_file = "/home/fws/{$mac_no}_work_cnt.log";     // ディレクトリ・ファイル名生成
            $cnt_temp = "/home/fws/{$mac_no}_work_cnt.tmp";     // Rename 用ディレクトリ・ファイル名生成
            if (file_exists($cnt_file)) {                       // Counter File があれば
                if (rename($cnt_file, $cnt_temp)) {
                    $fp = fopen ($cnt_temp,'r');
                    $row  = 0;                                  // 全レコード
                    $data = array();                            // 年月日,時間,加工数
                    while ($data[$row] = fgetcsv ($fp, 50, ',')) {
                        if ($data[$row][0] != '') {
                            $row++;
                        }
                    }
                    if ($row >= 1) {            // Counter File にレコードがあれば状態と加工数書込み
                        ///// 現在のデータベースの最新レコードを取り込む
                        $query = "
                            SELECT mac_state, work_cnt FROM equip_work_log2
                            WHERE
                                equip_index(mac_no, siji_no, koutei, date_time) < '{$mac_no}{$siji_no}{$koutei}99999999999999'
                                AND
                                equip_index(mac_no, siji_no, koutei, date_time) > '{$mac_no}{$siji_no}{$koutei}00000000000000'
                            ORDER BY equip_index(mac_no, siji_no, koutei, date_time) DESC limit 1
                        ";
                        $res = array();
                        if ( ($rows = getResult($query, $res)) >= 1) {  // 前回の加工数にプラスして書込み
                            for ($j=0; $j<$row; $j++) {
                                $work_cnt  = $res[0]['work_cnt'] + ($j + 1);      // Counter UP
                                $date_time = $data[$j][0] . ' ' . $data[$j][1];     // PostgreSQLのTIMESTAMP型に変更
                                if ( ($state == 1) || ($state == 8) || ($state == 5) ) {     // 自動運転か無人運転又は段取中のはずなのでチェックして必要なら訂正
                                    $mac_state = $state;
                                } else {
                                    $mac_state = 1;     // Counterが進んでいるのに自動でも無人又は段取でもない場合は強制的に自動運転にする
                                }
                                $query = "insert into equip_work_log2
                                    (mac_no, date_time, mac_state, work_cnt, siji_no, koutei)
                                    values($mac_no, '$date_time', $mac_state, $work_cnt, $siji_no, $koutei)";
                                if (query_affected_trans($con, $query) <= 0) {
                                    $temp_msg = "FWS2 Counterの書き込みに失敗 レコード:$j : $mac_no : $date_time : $mac_state \n";
                                    $temp_msg .= $query;    // debug 用
                                    `echo "$temp_msg" >> /tmp/equipment_write_error2.log`;
                                    // query_affected_trans($con, 'rollback');         // transaction rollback
                                    // exit();
                                }
                            }
                        } else {                    // データベースが初回のため無条件に書込み
                            for ($j=0; $j<$row; $j++) {
                                $work_cnt  =  ($j + 1);   // 初回の場合はここが違う
                                $date_time = $data[$j][0] . ' ' . $data[$j][1];     // PostgreSQLのTIMESTAMP型に変更
                                // $mac_state = $state;     // 初回の場合は過去のデータを取込む可能性が高いため以下は必要
                                if ( ($state == 1) || ($state == 8) || ($state == 5) ) {     // 自動運転か無人運転又は段取中のはずなのでチェックして必要なら訂正
                                    $mac_state = $state;
                                } else {
                                    $mac_state = 1;     // Counterが進んでいるのに自動でも無人又は段取でもない場合は強制的に自動運転にする
                                }
                                $query = "insert into equip_work_log2
                                    (mac_no, date_time, mac_state, work_cnt, siji_no, koutei)
                                    values($mac_no, '$date_time', $mac_state, $work_cnt, $siji_no, $koutei)";
                                if (query_affected_trans($con, $query) <= 0) {
                                    $temp_msg = "fwserver3 初回データベースの書き込みに失敗 レコード:$j\n$query";
                                    `echo "$temp_msg" >> /tmp/equipment_write_error2.log`;
                                    // query_affected_trans($con, 'rollback');         // transaction rollback
                                    // exit();
                                } else {
                                    $query = "select str_timestamp from equip_work_log2_header
                                            where mac_no={$mac_no} and siji_no={$siji_no} and koutei={$koutei}
                                    ";
                                    if (getUniResTrs($con, $query, $str_timestamp) > 0) {
                                        $query = "select case when cast('$date_time' as TIMESTAMP) < cast('$str_timestamp' as TIMESTAMP)
                                                then (select 1) else (select 0) end";
                                        if (getUniResTrs($con, $query, $check_time) > 0) {
                                            if ($check_time == 1) {
                                                $query = "update equip_work_log2_header set str_timestamp='{$date_time}'
                                                        where mac_no={$mac_no} and siji_no={$siji_no} and koutei={$koutei}";
                                                if (query_affected_trans($con, $query) <= 0) {
                                                    $temp_msg = "初回データの日時比較でHeaderのUPDATEに失敗 mac_no:{$mac_no}";
                                                    `echo "$temp_msg" >> /tmp/equipment_write_error2.log`;
                                                }
                                            }
                                        }
                                    }
                                }
                            }
                        }
                    } else {                    // Counter File にレコードがないので状態のみチェックし書込み
                        ///// 現在のデータベースの最新レコードを取り込む
                        $query = "
                            SELECT mac_state, work_cnt FROM equip_work_log2
                            WHERE
                                equip_index(mac_no, siji_no, koutei, date_time) < '{$mac_no}{$siji_no}{$koutei}99999999999999'
                                AND
                                equip_index(mac_no, siji_no, koutei, date_time) > '{$mac_no}{$siji_no}{$koutei}00000000000000'
                            ORDER BY equip_index(mac_no, siji_no, koutei, date_time) DESC limit 1
                        ";
                        $res = array();
                        if ( ($rows = getResult($query, $res)) >= 1) {  // レコードがあれば状態をチェックする
                            if ($res[0]['mac_state'] != $state) {       // 状態が違えば書込む
                                $work_cnt  = $res[0]['work_cnt'];       // 前回の加工数を使う
                                // $date_time  = date('Ymd His.');             // 現在の時間を使う PostgreSQLのTIMESTAMP型に変更
                                // $date_time .= substr(microtime(), 2, 6);    // TIMESTAMP(6)で重複キーを回避
                                $date_time  = date('Ymd His');          // 現在の時間を使う PostgreSQLのTIMESTAMP型に変更
                                $mac_state = $state;
                                $query = "insert into equip_work_log2
                                    (mac_no, date_time, mac_state, work_cnt, siji_no, koutei)
                                    values($mac_no, '$date_time', $mac_state, $work_cnt, $siji_no, $koutei)";
                                if (query_affected_trans($con, $query) <= 0) {
                                    $temp_msg = "fwserver3 Counter File があるがレコードが無い場合のデータベースの書き込みに失敗 mac_no={$mac_no}";
                                    `echo "$temp_msg" >> /tmp/equipment_write_error2.log`;
                                    // query_affected_trans($con, 'rollback');         // transaction rollback
                                    // exit();
                                }
                            }
                        } else {            // 初回のため無条件に書込む
                            $work_cnt  = 0;             // 初回の場合は０
                            $date_time = date('Ymd His');   // 現在の時間を使う PostgreSQLのTIMESTAMP型に変更
                            $mac_state = $state;
                            $query = "insert into equip_work_log2
                                (mac_no, date_time, mac_state, work_cnt, siji_no, koutei)
                                values($mac_no, '$date_time', $mac_state, $work_cnt, $siji_no, $koutei)";
                            if (query_affected_trans($con, $query) <= 0) {
                                $temp_msg = "fwserver3 Counter File があるがレコードが無い場合の初回データベースの書き込みに失敗 mac_no={$mac_no}";
                                `echo "$temp_msg" >> /tmp/equipment_write_error2.log`;
                                // query_affected_trans($con, 'rollback');         // transaction rollback
                                // exit();
                            } else {
                                $query = "select str_timestamp from equip_work_log2_header
                                        where mac_no={$mac_no} and siji_no={$siji_no} and koutei={$koutei}
                                ";
                                if (getUniResTrs($con, $query, $str_timestamp) > 0) {
                                    $query = "select case when cast('$date_time' as TIMESTAMP) < cast('$str_timestamp' as TIMESTAMP)
                                            then (select 1) else (select 0) end";
                                    if (getUniResTrs($con, $query, $check_time) > 0) {
                                        if ($check_time == 1) {
                                            $query = "update equip_work_log2_header set str_timestamp='{$date_time}'
                                                    where mac_no={$mac_no} and siji_no={$siji_no} and koutei={$koutei}";
                                            if (query_affected_trans($con, $query) <= 0) {
                                                $temp_msg = "初回データの日時比較でHeaderのUPDATEに失敗 mac_no:{$mac_no}";
                                                `echo "$temp_msg" >> /tmp/equipment_write_error2.log`;
                                            }
                                        }
                                    }
                                }
                            }
                        }
                    }
                    unlink($cnt_temp);      // 一時ファイルの削除
                } else {
                    echo "カウンターファイルの rename() に失敗\n";
                }
            } else {                    // Counter File がないので状態のみ書込み
                ///// 現在のデータベースの最新レコードを取り込む
                $query = "
                    SELECT mac_state, work_cnt FROM equip_work_log2
                    WHERE
                        equip_index(mac_no, siji_no, koutei, date_time) < '{$mac_no}{$siji_no}{$koutei}99999999999999'
                        AND
                        equip_index(mac_no, siji_no, koutei, date_time) > '{$mac_no}{$siji_no}{$koutei}00000000000000'
                    ORDER BY equip_index(mac_no, siji_no, koutei, date_time) DESC limit 1
                ";
                $res = array();
                if ( ($rows = getResult($query, $res)) >= 1) {  // レコードがあれば状態をチェックする
                    if ($res[0]['mac_state'] != $state) {       // 状態が違えば書込む
                        $work_cnt  = $res[0]['work_cnt'];       // 前回の加工数をそのまま使う
                        // $date_time  = date('Ymd His.');             // 現在の時間を使う PostgreSQLのTIMESTAMP型に変更
                        // $date_time .= substr(microtime(), 2, 6);    // TIMESTAMP(6)で重複キーを回避
                        $date_time  = date('Ymd His');          // 現在の時間を使う PostgreSQLのTIMESTAMP型に変更
                        $mac_state = $state;
                        $query = "insert into equip_work_log2
                            (mac_no, date_time, mac_state, work_cnt, siji_no, koutei)
                            values($mac_no, '$date_time', $mac_state, $work_cnt, $siji_no, $koutei)";
                        if (query_affected_trans($con, $query) <= 0) {
                            $temp_msg = date('Y-m-d H:i:s ') . "fwserver3 equip_work_log2 へ状態書込みに失敗";
                            `echo "$temp_msg" >> /tmp/equipment_write_error2.log`;
                            // query_affected_trans($con, 'rollback');         // transaction rollback
                            // exit();
                        }
                    }
                } else {        // 初回のため無条件に書込む
                    $work_cnt  = 0;             // 初回の場合は０
                    $date_time = date('Ymd His');       // 現在の時間を使う PostgreSQLのTIMESTAMP型に変更
                    $mac_state = $state;
                    $query = "insert into equip_work_log2
                        (mac_no, date_time, mac_state, work_cnt, siji_no, koutei)
                        values($mac_no, '$date_time', $mac_state, $work_cnt, $siji_no, $koutei)";
                    if (query_affected_trans($con, $query) <= 0) {
                        $temp_msg = date('Y-m-d H:i:s ') . "fwserver3 equip_work_log2 へ初回の状態書込みに失敗";
                        `echo "$temp_msg" >> /tmp/equipment_write_error2.log`;
                        // query_affected_trans($con, 'rollback');         // transaction rollback
                        // exit();
                    } else {
                        $query = "select str_timestamp from equip_work_log2_header
                                where mac_no={$mac_no} and siji_no={$siji_no} and koutei={$koutei}
                        ";
                        if (getUniResTrs($con, $query, $str_timestamp) > 0) {
                            $query = "select case when cast('$date_time' as TIMESTAMP) < cast('$str_timestamp' as TIMESTAMP)
                                    then (select 1) else (select 0) end";
                            if (getUniResTrs($con, $query, $check_time) > 0) {
                                if ($check_time == 1) {
                                    $query = "update equip_work_log2_header set str_timestamp='{$date_time}'
                                            where mac_no={$mac_no} and siji_no={$siji_no} and koutei={$koutei}";
                                    if (query_affected_trans($con, $query) <= 0) {
                                        $temp_msg = "初回データの日時比較でHeaderのUPDATEに失敗 mac_no:{$mac_no}";
                                        `echo "$temp_msg" >> /tmp/equipment_write_error2.log`;
                                    }
                                }
                            }
                        }
                    }
                }
            }
            /////////// commit トランザクション終了
            query_affected_trans($con, 'commit');
        } else {
            echo "データベースの接続2に失敗\n";
            exit();
        }
    }
}
////////// FTP Close
if ($ftp_con) {
    ftp_close($ftp_con);
}
}   // $ftp_flg End





/****************************** FWS4方式 *********************************/
$ftp_flg = true;
////////// FTP CONNECT
if ( !($ftp_con = ftp_connect(FWS4)) ) {   // テスト用にリテラル表示
    $error_msg = date('Y-m-d H:i:s ') . "fwserver4のFTPの接続に失敗しました。";
    `echo "$error_msg" >> /tmp/equipment_ftp_error.log`;
    $ftp_flg = false;
} else {
    if (!@ftp_login($ftp_con, FWS_USER, FWS_PASS)) {      // 2004/03/29 エラーメッセージを抑制
        $error_msg = date('Y-m-d H:i:s ') . "FWS4のFTPのloginに失敗しました。";
        `echo "$error_msg" >> /tmp/equipment_ftp_error.log`;
        $ftp_flg = false;
    }
}

if ($ftp_flg) {
////////// 稼動中に関係なく 機械マスター監視する機械番号を取得 (物理状態を24時間監視するため)
$query = "select mac_no, csv_flg from equip_machine_master2 where csv_flg = 5 and survey = 'Y'";
                        // Netmoni=1 fwserver1=2 fwserver2=3 fwserver3=4 fwserver4=5 ... , Net&fws1=101
$res_key = array();
if ( ($rows_key = getResult($query, $res_key)) >= 1) {      // レコードが１以上あれば
    for ($i=0; $i<$rows_key; $i++) {                        // １レコードずつ処理
        ///// insert 用 変数 初期化
        $mac_no   = $res_key[$i]['mac_no'];
        $csv_flg  = $res_key[$i]['csv_flg'];
        /////////// FTP上のファイルの存在チェック
        if (ftp_size($ftp_con, "/home/fws/usr/{$mac_no}_work_state.log") != -1) {
            /////////// FTP rename
            if (ftp_rename($ftp_con, "/home/fws/usr/{$mac_no}_work_state.log", "/home/fws/usr/{$mac_no}_work_state.tmp")) {
                /////////// FTP Download
                if (ftp_get($ftp_con, "/home/fws/{$mac_no}_work_state.log", "/home/fws/usr/{$mac_no}_work_state.tmp", FTP_ASCII)) {
                    // echo 'FTPのDownloadに成功しました。';
                    ftp_delete($ftp_con, "/home/fws/usr/{$mac_no}_work_state.tmp");  // 旧ファイルは削除
                } else {
                    // echo 'FTPのDownloadに失敗しました。';
                    `/bin/rm -f /home/fws/{$mac_no}_work_state.log`;
                }
            } else {
                // echo 'FTPのrenameに失敗しました。';
                `/bin/rm -f /home/fws/{$mac_no}_work_state.log`;
            }
        }
        /////////// begin トランザクション開始
        if ($con = db_connect()) {
            query_affected_trans($con, 'begin');
            ///// State Log File Check        現在の状態(運転中・停止中)・日時を取得 電源OFFも取得OK
            $state_file = "/home/fws/{$mac_no}_work_state.log";     // ディレクトリ・ファイル名生成
            $state_temp = "/home/fws/{$mac_no}_work_state.tmp";     // Rename 用ディレクトリ・ファイル名生成
            if (file_exists($state_file)) {                         // State Log File があれば
                if (rename($state_file, $state_temp)) {
                    $fp = fopen ($state_temp,'r');
                    $row  = 0;                                  // 全レコード
                    $data = array();                            // 年月日,時間,加工数
                    while ($data[$row] = fgetcsv ($fp, 50, ',')) {
                        if ($data[$row][0] != '') {
                            $row++;
                        }
                    }
                    for ($j=0; $j<$row; $j++) {         // Status File にレコードがあれば状態と日時を書込む
                        if ($data[$j][2] == 'auto') {   // ファイルから物理状態番号を取得
                            $state_p = 1;               // 運転中(自動運転)
                        } elseif ($data[$j][2] == 'stop') {
                            $state_p = 3;               // 停止中
                        } elseif ($data[$j][2] == 'on') {
                            $state_p = 3;               // 電源ONの場合のDefault値は停止中=3
                        } else {
                            $state_p = 0;               // 電源OFF "off" の予定
                        }
                        $date_time = $data[$j][0] . " " . $data[$j][1];     // timestamp型の変数生成 注意
                        $state_name = equip_machine_state($mac_no, $state_p, $txt_color, $bg_color);
                        $query = "insert into equip_mac_state_log2
                            (mac_no, state, date_time, state_name, state_type)
                            values($mac_no, $state_p, '$date_time', '$state_name', $csv_flg)";
                        if (query_affected_trans($con, $query) <= 0) {
                            $temp_msg = "$query\n date_time:$date_time mac_no:$mac_no state:$state_p j=$j";
                            `echo "$temp_msg" >> /tmp/equipment_write_error2.log`;
                            // query_affected_trans($con, "rollback");         // transaction rollback
                            // exit();
                        }
                    }
                    unlink($state_temp);        // 一時ファイルの削除
                } else {
                    echo "ステータスファイルの rename() に失敗\n";
                }
            }
            ///// State Log File 処理終了
            /////////// commit トランザクション終了
            query_affected_trans($con, 'commit');
        } else {
            echo "データベースの接続2に失敗\n";
            exit();
        }
    }
}
////////// 稼動中の機械�發鬟悒奪澄璽侫．ぅ襪�ら取得 & 機械マスターから機械名を取得
$query = "select mac_no, siji_no, koutei, parts_no, plan_cnt, mac_name, csv_flg from equip_work_log2_header
        left outer join equip_machine_master2 using(mac_no)
        where work_flg is TRUE and csv_flg = 5 and survey = 'Y'
";
        // Netmoni=1&101 fwserver1=2 fwserver2=3 fwserver3=4 fwserver4=5 fwserver5=6 ...
$res_key = array();
if ( ($rows_key = getResult($query, $res_key)) >= 1) {      // レコードが１以上あれば
    for ($i=0; $i<$rows_key; $i++) {                        // １レコードずつ処理
        ///// insert 用 変数 初期化
        $mac_no   = $res_key[$i]['mac_no'];
        $siji_no  = $res_key[$i]['siji_no'];
        $parts_no = $res_key[$i]['parts_no'];
        $koutei   = $res_key[$i]['koutei'];
        $plan_cnt = $res_key[$i]['plan_cnt'];
        $mac_name = $res_key[$i]['mac_name'];
        $csv_flg  = $res_key[$i]['csv_flg'];
        /////////// FTP上のファイルの存在チェック
        if (ftp_size($ftp_con, "/home/fws/usr/{$mac_no}-bcd1") != -1) {
            /////////// FTP Download
            if (!ftp_get($ftp_con, "/home/fws/{$mac_no}-bcd1", "/home/fws/usr/{$mac_no}-bcd1", FTP_ASCII)) {
                // echo 'FTPのDownloadに失敗しました。';
            }
        } else {
            if (file_exists("/home/fws/{$mac_no}-bcd1")) {   // 旧ファイルがあれば削除
                unlink("/home/fws/{$mac_no}-bcd1");
            }
        }
        /////////// FTP上のファイルの存在チェック
        if (ftp_size($ftp_con, "/home/fws/usr/{$mac_no}-bcd2") != -1) {
            if (!ftp_get($ftp_con, "/home/fws/{$mac_no}-bcd2", "/home/fws/usr/{$mac_no}-bcd2", FTP_ASCII)) {
                // echo 'FTPのDownloadに失敗しました。';
            }
        } else {
            if (file_exists("/home/fws/{$mac_no}-bcd2")) {   // 旧ファイルがあれば削除
                unlink("/home/fws/{$mac_no}-bcd2");
            }
        }
        /////////// FTP上のファイルの存在チェック
        if (ftp_size($ftp_con, "/home/fws/usr/{$mac_no}-bcd4") != -1) {
            if (!ftp_get($ftp_con, "/home/fws/{$mac_no}-bcd4", "/home/fws/usr/{$mac_no}-bcd4", FTP_ASCII)) {
                // echo 'FTPのDownloadに失敗しました。';
            }
        } else {
            if (file_exists("/home/fws/{$mac_no}-bcd4")) {   // 旧ファイルがあれば削除
                unlink("/home/fws/{$mac_no}-bcd4");
            }
        }
        /////////// FTP上のファイルの存在チェック
        if (ftp_size($ftp_con, "/home/fws/usr/{$mac_no}-bcd8") != -1) {
            if (!ftp_get($ftp_con, "/home/fws/{$mac_no}-bcd8", "/home/fws/usr/{$mac_no}-bcd8", FTP_ASCII)) {
                // echo 'FTPのDownloadに失敗しました。';
            }
        } else {
            if (file_exists("/home/fws/{$mac_no}-bcd8")) {   // 旧ファイルがあれば削除
                unlink("/home/fws/{$mac_no}-bcd8");
            }
        }
        ///// State File Check BCD演算  現在の状態を取得
        $bcd1_file = "/home/fws/{$mac_no}-bcd1";            // BCD演算用ファイル名生成
        $bcd2_file = "/home/fws/{$mac_no}-bcd2";
        $bcd4_file = "/home/fws/{$mac_no}-bcd4";
        $bcd8_file = "/home/fws/{$mac_no}-bcd8";
        $state_bcd = 0;                                     // 初期化
        if (file_exists($bcd1_file)) {
            $state_bcd += 1;
        }
        if (file_exists($bcd2_file)) {
            $state_bcd += 2;
        }
        if (file_exists($bcd4_file)) {
            $state_bcd += 4;
        }
        if (file_exists($bcd8_file)) {
            $state_bcd += 8;
        }
        $query = "select state from equip_mac_state_log2 where equip_index2(mac_no,date_time) < '{$mac_no}99999999999999' order by equip_index2(mac_no,date_time) DESC limit 1";
        if (getUniResult($query, $state_p) > 0) {
            // 物理信号がセットされていれば
            $state = state_check($state_p, $state_bcd);   // 物理状態信号とスイッチの状態とで適正値をチェック
        } else {
            $state = 0;     // 状態データが無いので無条件に電源off=0
        }
        /////////// FTP上のファイルの存在チェック
        if (ftp_size($ftp_con, "/home/fws/usr/{$mac_no}_work_cnt.log") != -1) {
            /////////// FTP rename
            if (ftp_rename($ftp_con, "/home/fws/usr/{$mac_no}_work_cnt.log", "/home/fws/usr/{$mac_no}_work_cnt.tmp")) {
                /////////// FTP Download
                if (ftp_get($ftp_con, "/home/fws/{$mac_no}_work_cnt.log", "/home/fws/usr/{$mac_no}_work_cnt.tmp", FTP_ASCII)) {
                    // echo 'FTPのDownloadに成功しました。';
                    ftp_delete($ftp_con, "/home/fws/usr/{$mac_no}_work_cnt.tmp");  // 旧ファイルは削除
                } else {
                    // echo 'FTPのDownloadに失敗しました。';
                    `/bin/rm -f /home/fws/{$mac_no}_work_cnt.log`;
                }
            } else {
                // echo 'FTPのrenameに失敗しました。';
                `/bin/rm -f /home/fws/{$mac_no}_work_cnt.log`;
            }
        }
        /////////// begin トランザクション開始
        if ($con = db_connect()) {
            query_affected_trans($con, 'begin');
            ///// Counter File Check        現在の加工数・日時を取得
            $cnt_file = "/home/fws/{$mac_no}_work_cnt.log";     // ディレクトリ・ファイル名生成
            $cnt_temp = "/home/fws/{$mac_no}_work_cnt.tmp";     // Rename 用ディレクトリ・ファイル名生成
            if (file_exists($cnt_file)) {                       // Counter File があれば
                if (rename($cnt_file, $cnt_temp)) {
                    $fp = fopen ($cnt_temp,'r');
                    $row  = 0;                                  // 全レコード
                    $data = array();                            // 年月日,時間,加工数
                    while ($data[$row] = fgetcsv ($fp, 50, ',')) {
                        if ($data[$row][0] != '') {
                            $row++;
                        }
                    }
                    if ($row >= 1) {            // Counter File にレコードがあれば状態と加工数書込み
                        ///// 現在のデータベースの最新レコードを取り込む
                        $query = "
                            SELECT mac_state, work_cnt FROM equip_work_log2
                            WHERE
                                equip_index(mac_no, siji_no, koutei, date_time) < '{$mac_no}{$siji_no}{$koutei}99999999999999'
                                AND
                                equip_index(mac_no, siji_no, koutei, date_time) > '{$mac_no}{$siji_no}{$koutei}00000000000000'
                            ORDER BY equip_index(mac_no, siji_no, koutei, date_time) DESC limit 1
                        ";
                        $res = array();
                        if ( ($rows = getResult($query, $res)) >= 1) {  // 前回の加工数にプラスして書込み
                            for ($j=0; $j<$row; $j++) {
                                $work_cnt  = $res[0]['work_cnt'] + ($j + 1);      // Counter UP
                                $date_time = $data[$j][0] . ' ' . $data[$j][1];     // PostgreSQLのTIMESTAMP型に変更
                                if ( ($state == 1) || ($state == 8) || ($state == 5) ) {     // 自動運転か無人運転又は段取中のはずなのでチェックして必要なら訂正
                                    $mac_state = $state;
                                } else {
                                    $mac_state = 1;     // Counterが進んでいるのに自動でも無人又は段取でもない場合は強制的に自動運転にする
                                }
                                $query = "insert into equip_work_log2
                                    (mac_no, date_time, mac_state, work_cnt, siji_no, koutei)
                                    values($mac_no, '$date_time', $mac_state, $work_cnt, $siji_no, $koutei)";
                                if (query_affected_trans($con, $query) <= 0) {
                                    $temp_msg = "FWS2 Counterの書き込みに失敗 レコード:$j : $mac_no : $date_time : $mac_state \n";
                                    $temp_msg .= $query;    // debug 用
                                    `echo "$temp_msg" >> /tmp/equipment_write_error2.log`;
                                    // query_affected_trans($con, 'rollback');         // transaction rollback
                                    // exit();
                                }
                            }
                        } else {                    // データベースが初回のため無条件に書込み
                            for ($j=0; $j<$row; $j++) {
                                $work_cnt  =  ($j + 1);   // 初回の場合はここが違う
                                $date_time = $data[$j][0] . ' ' . $data[$j][1];     // PostgreSQLのTIMESTAMP型に変更
                                // $mac_state = $state;     // 初回の場合は過去のデータを取込む可能性が高いため以下は必要
                                if ( ($state == 1) || ($state == 8) || ($state == 5) ) {     // 自動運転か無人運転又は段取中のはずなのでチェックして必要なら訂正
                                    $mac_state = $state;
                                } else {
                                    $mac_state = 1;     // Counterが進んでいるのに自動でも無人又は段取でもない場合は強制的に自動運転にする
                                }
                                $query = "insert into equip_work_log2
                                    (mac_no, date_time, mac_state, work_cnt, siji_no, koutei)
                                    values($mac_no, '$date_time', $mac_state, $work_cnt, $siji_no, $koutei)";
                                if (query_affected_trans($con, $query) <= 0) {
                                    $temp_msg = "fwserver4 初回データベースの書き込みに失敗 レコード:$j\n$query";
                                    `echo "$temp_msg" >> /tmp/equipment_write_error2.log`;
                                    // query_affected_trans($con, 'rollback');         // transaction rollback
                                    // exit();
                                } else {
                                    $query = "select str_timestamp from equip_work_log2_header
                                            where mac_no={$mac_no} and siji_no={$siji_no} and koutei={$koutei}
                                    ";
                                    if (getUniResTrs($con, $query, $str_timestamp) > 0) {
                                        $query = "select case when cast('$date_time' as TIMESTAMP) < cast('$str_timestamp' as TIMESTAMP)
                                                then (select 1) else (select 0) end";
                                        if (getUniResTrs($con, $query, $check_time) > 0) {
                                            if ($check_time == 1) {
                                                $query = "update equip_work_log2_header set str_timestamp='{$date_time}'
                                                        where mac_no={$mac_no} and siji_no={$siji_no} and koutei={$koutei}";
                                                if (query_affected_trans($con, $query) <= 0) {
                                                    $temp_msg = "初回データの日時比較でHeaderのUPDATEに失敗 mac_no:{$mac_no}";
                                                    `echo "$temp_msg" >> /tmp/equipment_write_error2.log`;
                                                }
                                            }
                                        }
                                    }
                                }
                            }
                        }
                    } else {                    // Counter File にレコードがないので状態のみチェックし書込み
                        ///// 現在のデータベースの最新レコードを取り込む
                        $query = "
                            SELECT mac_state, work_cnt FROM equip_work_log2
                            WHERE
                                equip_index(mac_no, siji_no, koutei, date_time) < '{$mac_no}{$siji_no}{$koutei}99999999999999'
                                AND
                                equip_index(mac_no, siji_no, koutei, date_time) > '{$mac_no}{$siji_no}{$koutei}00000000000000'
                            ORDER BY equip_index(mac_no, siji_no, koutei, date_time) DESC limit 1
                        ";
                        $res = array();
                        if ( ($rows = getResult($query, $res)) >= 1) {  // レコードがあれば状態をチェックする
                            if ($res[0]['mac_state'] != $state) {       // 状態が違えば書込む
                                $work_cnt  = $res[0]['work_cnt'];       // 前回の加工数を使う
                                // $date_time  = date('Ymd His.');             // 現在の時間を使う PostgreSQLのTIMESTAMP型に変更
                                // $date_time .= substr(microtime(), 2, 6);    // TIMESTAMP(6)で重複キーを回避
                                $date_time  = date('Ymd His');          // 現在の時間を使う PostgreSQLのTIMESTAMP型に変更
                                $mac_state = $state;
                                $query = "insert into equip_work_log2
                                    (mac_no, date_time, mac_state, work_cnt, siji_no, koutei)
                                    values($mac_no, '$date_time', $mac_state, $work_cnt, $siji_no, $koutei)";
                                if (query_affected_trans($con, $query) <= 0) {
                                    $temp_msg = "fwserver4 Counter File があるがレコードが無い場合のデータベースの書き込みに失敗 mac_no={$mac_no}";
                                    `echo "$temp_msg" >> /tmp/equipment_write_error2.log`;
                                    // query_affected_trans($con, 'rollback');         // transaction rollback
                                    // exit();
                                }
                            }
                        } else {            // 初回のため無条件に書込む
                            $work_cnt  = 0;             // 初回の場合は０
                            $date_time = date('Ymd His');   // 現在の時間を使う PostgreSQLのTIMESTAMP型に変更
                            $mac_state = $state;
                            $query = "insert into equip_work_log2
                                (mac_no, date_time, mac_state, work_cnt, siji_no, koutei)
                                values($mac_no, '$date_time', $mac_state, $work_cnt, $siji_no, $koutei)";
                            if (query_affected_trans($con, $query) <= 0) {
                                $temp_msg = "fwserver4 Counter File があるがレコードが無い場合の初回データベースの書き込みに失敗 mac_no={$mac_no}";
                                `echo "$temp_msg" >> /tmp/equipment_write_error2.log`;
                                // query_affected_trans($con, 'rollback');         // transaction rollback
                                // exit();
                            } else {
                                $query = "select str_timestamp from equip_work_log2_header
                                        where mac_no={$mac_no} and siji_no={$siji_no} and koutei={$koutei}
                                ";
                                if (getUniResTrs($con, $query, $str_timestamp) > 0) {
                                    $query = "select case when cast('$date_time' as TIMESTAMP) < cast('$str_timestamp' as TIMESTAMP)
                                            then (select 1) else (select 0) end";
                                    if (getUniResTrs($con, $query, $check_time) > 0) {
                                        if ($check_time == 1) {
                                            $query = "update equip_work_log2_header set str_timestamp='{$date_time}'
                                                    where mac_no={$mac_no} and siji_no={$siji_no} and koutei={$koutei}";
                                            if (query_affected_trans($con, $query) <= 0) {
                                                $temp_msg = "初回データの日時比較でHeaderのUPDATEに失敗 mac_no:{$mac_no}";
                                                `echo "$temp_msg" >> /tmp/equipment_write_error2.log`;
                                            }
                                        }
                                    }
                                }
                            }
                        }
                    }
                    unlink($cnt_temp);      // 一時ファイルの削除
                } else {
                    echo "カウンターファイルの rename() に失敗\n";
                }
            } else {                    // Counter File がないので状態のみ書込み
                ///// 現在のデータベースの最新レコードを取り込む
                $query = "
                    SELECT mac_state, work_cnt FROM equip_work_log2
                    WHERE
                        equip_index(mac_no, siji_no, koutei, date_time) < '{$mac_no}{$siji_no}{$koutei}99999999999999'
                        AND
                        equip_index(mac_no, siji_no, koutei, date_time) > '{$mac_no}{$siji_no}{$koutei}00000000000000'
                    ORDER BY equip_index(mac_no, siji_no, koutei, date_time) DESC limit 1
                ";
                $res = array();
                if ( ($rows = getResult($query, $res)) >= 1) {  // レコードがあれば状態をチェックする
                    if ($res[0]['mac_state'] != $state) {       // 状態が違えば書込む
                        $work_cnt  = $res[0]['work_cnt'];       // 前回の加工数をそのまま使う
                        // $date_time  = date('Ymd His.');             // 現在の時間を使う PostgreSQLのTIMESTAMP型に変更
                        // $date_time .= substr(microtime(), 2, 6);    // TIMESTAMP(6)で重複キーを回避
                        $date_time  = date('Ymd His');          // 現在の時間を使う PostgreSQLのTIMESTAMP型に変更
                        $mac_state = $state;
                        $query = "insert into equip_work_log2
                            (mac_no, date_time, mac_state, work_cnt, siji_no, koutei)
                            values($mac_no, '$date_time', $mac_state, $work_cnt, $siji_no, $koutei)";
                        if (query_affected_trans($con, $query) <= 0) {
                            $temp_msg = date('Y-m-d H:i:s ') . "fwserver4 equip_work_log2 へ状態書込みに失敗";
                            `echo "$temp_msg" >> /tmp/equipment_write_error2.log`;
                            // query_affected_trans($con, 'rollback');         // transaction rollback
                            // exit();
                        }
                    }
                } else {        // 初回のため無条件に書込む
                    $work_cnt  = 0;             // 初回の場合は０
                    $date_time = date('Ymd His');       // 現在の時間を使う PostgreSQLのTIMESTAMP型に変更
                    $mac_state = $state;
                    $query = "insert into equip_work_log2
                        (mac_no, date_time, mac_state, work_cnt, siji_no, koutei)
                        values($mac_no, '$date_time', $mac_state, $work_cnt, $siji_no, $koutei)";
                    if (query_affected_trans($con, $query) <= 0) {
                        $temp_msg = date('Y-m-d H:i:s ') . "fwserver4 equip_work_log2 へ初回の状態書込みに失敗";
                        `echo "$temp_msg" >> /tmp/equipment_write_error2.log`;
                        // query_affected_trans($con, 'rollback');         // transaction rollback
                        // exit();
                    } else {
                        $query = "select str_timestamp from equip_work_log2_header
                                where mac_no={$mac_no} and siji_no={$siji_no} and koutei={$koutei}
                        ";
                        if (getUniResTrs($con, $query, $str_timestamp) > 0) {
                            $query = "select case when cast('$date_time' as TIMESTAMP) < cast('$str_timestamp' as TIMESTAMP)
                                    then (select 1) else (select 0) end";
                            if (getUniResTrs($con, $query, $check_time) > 0) {
                                if ($check_time == 1) {
                                    $query = "update equip_work_log2_header set str_timestamp='{$date_time}'
                                            where mac_no={$mac_no} and siji_no={$siji_no} and koutei={$koutei}";
                                    if (query_affected_trans($con, $query) <= 0) {
                                        $temp_msg = "初回データの日時比較でHeaderのUPDATEに失敗 mac_no:{$mac_no}";
                                        `echo "$temp_msg" >> /tmp/equipment_write_error2.log`;
                                    }
                                }
                            }
                        }
                    }
                }
            }
            /////////// commit トランザクション終了
            query_affected_trans($con, 'commit');
        } else {
            echo "データベースの接続2に失敗\n";
            exit();
        }
    }
}
////////// FTP Close
if ($ftp_con) {
    ftp_close($ftp_con);
}
}   // $ftp_flg End





/****************************** FWS5方式 *********************************/
$ftp_flg = true;
////////// FTP CONNECT
if ( !($ftp_con = ftp_connect(FWS5)) ) {   // テスト用にリテラル表示
    $error_msg = date('Y-m-d H:i:s ') . "fwserver5のFTPの接続に失敗しました。";
    `echo "$error_msg" >> /tmp/equipment_ftp_error.log`;
    $ftp_flg = false;
} else {
    if (!@ftp_login($ftp_con, FWS_USER, FWS_PASS)) {      // 2004/03/29 エラーメッセージを抑制
        $error_msg = date('Y-m-d H:i:s ') . "FWS5のFTPのloginに失敗しました。";
        `echo "$error_msg" >> /tmp/equipment_ftp_error.log`;
        $ftp_flg = false;
    }
}

if ($ftp_flg) {
////////// 稼動中に関係なく 機械マスター監視する機械番号を取得 (物理状態を24時間監視するため)
$query = "select mac_no, csv_flg from equip_machine_master2 where csv_flg = 6 and survey = 'Y'";
                        // Netmoni=1 fwserver1=2 fwserver2=3 fwserver3=4 fwserver4=5 ... , Net&fws1=101
$res_key = array();
if ( ($rows_key = getResult($query, $res_key)) >= 1) {      // レコードが１以上あれば
    for ($i=0; $i<$rows_key; $i++) {                        // １レコードずつ処理
        ///// insert 用 変数 初期化
        $mac_no   = $res_key[$i]['mac_no'];
        $csv_flg  = $res_key[$i]['csv_flg'];
        /////////// FTP上のファイルの存在チェック
        if (ftp_size($ftp_con, "/home/fws/usr/{$mac_no}_work_state.log") != -1) {
            /////////// FTP rename
            if (ftp_rename($ftp_con, "/home/fws/usr/{$mac_no}_work_state.log", "/home/fws/usr/{$mac_no}_work_state.tmp")) {
                /////////// FTP Download
                if (ftp_get($ftp_con, "/home/fws/{$mac_no}_work_state.log", "/home/fws/usr/{$mac_no}_work_state.tmp", FTP_ASCII)) {
                    // echo 'FTPのDownloadに成功しました。';
                    ftp_delete($ftp_con, "/home/fws/usr/{$mac_no}_work_state.tmp");  // 旧ファイルは削除
                } else {
                    // echo 'FTPのDownloadに失敗しました。';
                    `/bin/rm -f /home/fws/{$mac_no}_work_state.log`;
                }
            } else {
                // echo 'FTPのrenameに失敗しました。';
                `/bin/rm -f /home/fws/{$mac_no}_work_state.log`;
            }
        }
        /////////// begin トランザクション開始
        if ($con = db_connect()) {
            query_affected_trans($con, 'begin');
            ///// State Log File Check        現在の状態(運転中・停止中)・日時を取得 電源OFFも取得OK
            $state_file = "/home/fws/{$mac_no}_work_state.log";     // ディレクトリ・ファイル名生成
            $state_temp = "/home/fws/{$mac_no}_work_state.tmp";     // Rename 用ディレクトリ・ファイル名生成
            if (file_exists($state_file)) {                         // State Log File があれば
                if (rename($state_file, $state_temp)) {
                    $fp = fopen ($state_temp,'r');
                    $row  = 0;                                  // 全レコード
                    $data = array();                            // 年月日,時間,加工数
                    while ($data[$row] = fgetcsv ($fp, 50, ',')) {
                        if ($data[$row][0] != '') {
                            $row++;
                        }
                    }
                    for ($j=0; $j<$row; $j++) {         // Status File にレコードがあれば状態と日時を書込む
                        if ($data[$j][2] == 'auto') {   // ファイルから物理状態番号を取得
                            $state_p = 1;               // 運転中(自動運転)
                        } elseif ($data[$j][2] == 'stop') {
                            $state_p = 3;               // 停止中
                        } elseif ($data[$j][2] == 'on') {
                            $state_p = 3;               // 電源ONの場合のDefault値は停止中=3
                        } else {
                            $state_p = 0;               // 電源OFF "off" の予定
                        }
                        $date_time = $data[$j][0] . " " . $data[$j][1];     // timestamp型の変数生成 注意
                        $state_name = equip_machine_state($mac_no, $state_p, $txt_color, $bg_color);
                        $query = "insert into equip_mac_state_log2
                            (mac_no, state, date_time, state_name, state_type)
                            values($mac_no, $state_p, '$date_time', '$state_name', $csv_flg)";
                        if (query_affected_trans($con, $query) <= 0) {
                            $temp_msg = "$query\n date_time:$date_time mac_no:$mac_no state:$state_p j=$j";
                            `echo "$temp_msg" >> /tmp/equipment_write_error2.log`;
                            // query_affected_trans($con, "rollback");         // transaction rollback
                            // exit();
                        }
                    }
                    unlink($state_temp);        // 一時ファイルの削除
                } else {
                    echo "ステータスファイルの rename() に失敗\n";
                }
            }
            ///// State Log File 処理終了
            /////////// commit トランザクション終了
            query_affected_trans($con, 'commit');
        } else {
            echo "データベースの接続2に失敗\n";
            exit();
        }
    }
}
////////// 稼動中の機械�發鬟悒奪澄璽侫．ぅ襪�ら取得 & 機械マスターから機械名を取得
$query = "select mac_no, siji_no, koutei, parts_no, plan_cnt, mac_name, csv_flg from equip_work_log2_header
        left outer join equip_machine_master2 using(mac_no)
        where work_flg is TRUE and csv_flg = 6 and survey = 'Y'
";
        // Netmoni=1&101 fwserver1=2 fwserver2=3 fwserver3=4 fwserver4=5 fwserver5=6 ...
$res_key = array();
if ( ($rows_key = getResult($query, $res_key)) >= 1) {      // レコードが１以上あれば
    for ($i=0; $i<$rows_key; $i++) {                        // １レコードずつ処理
        ///// insert 用 変数 初期化
        $mac_no   = $res_key[$i]['mac_no'];
        $siji_no  = $res_key[$i]['siji_no'];
        $parts_no = $res_key[$i]['parts_no'];
        $koutei   = $res_key[$i]['koutei'];
        $plan_cnt = $res_key[$i]['plan_cnt'];
        $mac_name = $res_key[$i]['mac_name'];
        $csv_flg  = $res_key[$i]['csv_flg'];
        /////////// FTP上のファイルの存在チェック
        if (ftp_size($ftp_con, "/home/fws/usr/{$mac_no}-bcd1") != -1) {
            /////////// FTP Download
            if (!ftp_get($ftp_con, "/home/fws/{$mac_no}-bcd1", "/home/fws/usr/{$mac_no}-bcd1", FTP_ASCII)) {
                // echo 'FTPのDownloadに失敗しました。';
            }
        } else {
            if (file_exists("/home/fws/{$mac_no}-bcd1")) {   // 旧ファイルがあれば削除
                unlink("/home/fws/{$mac_no}-bcd1");
            }
        }
        /////////// FTP上のファイルの存在チェック
        if (ftp_size($ftp_con, "/home/fws/usr/{$mac_no}-bcd2") != -1) {
            if (!ftp_get($ftp_con, "/home/fws/{$mac_no}-bcd2", "/home/fws/usr/{$mac_no}-bcd2", FTP_ASCII)) {
                // echo 'FTPのDownloadに失敗しました。';
            }
        } else {
            if (file_exists("/home/fws/{$mac_no}-bcd2")) {   // 旧ファイルがあれば削除
                unlink("/home/fws/{$mac_no}-bcd2");
            }
        }
        /////////// FTP上のファイルの存在チェック
        if (ftp_size($ftp_con, "/home/fws/usr/{$mac_no}-bcd4") != -1) {
            if (!ftp_get($ftp_con, "/home/fws/{$mac_no}-bcd4", "/home/fws/usr/{$mac_no}-bcd4", FTP_ASCII)) {
                // echo 'FTPのDownloadに失敗しました。';
            }
        } else {
            if (file_exists("/home/fws/{$mac_no}-bcd4")) {   // 旧ファイルがあれば削除
                unlink("/home/fws/{$mac_no}-bcd4");
            }
        }
        /////////// FTP上のファイルの存在チェック
        if (ftp_size($ftp_con, "/home/fws/usr/{$mac_no}-bcd8") != -1) {
            if (!ftp_get($ftp_con, "/home/fws/{$mac_no}-bcd8", "/home/fws/usr/{$mac_no}-bcd8", FTP_ASCII)) {
                // echo 'FTPのDownloadに失敗しました。';
            }
        } else {
            if (file_exists("/home/fws/{$mac_no}-bcd8")) {   // 旧ファイルがあれば削除
                unlink("/home/fws/{$mac_no}-bcd8");
            }
        }
        ///// State File Check BCD演算  現在の状態を取得
        $bcd1_file = "/home/fws/{$mac_no}-bcd1";            // BCD演算用ファイル名生成
        $bcd2_file = "/home/fws/{$mac_no}-bcd2";
        $bcd4_file = "/home/fws/{$mac_no}-bcd4";
        $bcd8_file = "/home/fws/{$mac_no}-bcd8";
        $state_bcd = 0;                                     // 初期化
        if (file_exists($bcd1_file)) {
            $state_bcd += 1;
        }
        if (file_exists($bcd2_file)) {
            $state_bcd += 2;
        }
        if (file_exists($bcd4_file)) {
            $state_bcd += 4;
        }
        if (file_exists($bcd8_file)) {
            $state_bcd += 8;
        }
        $query = "select state from equip_mac_state_log2 where equip_index2(mac_no,date_time) < '{$mac_no}99999999999999' order by equip_index2(mac_no,date_time) DESC limit 1";
        if (getUniResult($query, $state_p) > 0) {
            // 物理信号がセットされていれば
            $state = state_check($state_p, $state_bcd);   // 物理状態信号とスイッチの状態とで適正値をチェック
        } else {
            $state = 0;     // 状態データが無いので無条件に電源off=0
        }
        /////////// FTP上のファイルの存在チェック
        if (ftp_size($ftp_con, "/home/fws/usr/{$mac_no}_work_cnt.log") != -1) {
            /////////// FTP rename
            if (ftp_rename($ftp_con, "/home/fws/usr/{$mac_no}_work_cnt.log", "/home/fws/usr/{$mac_no}_work_cnt.tmp")) {
                /////////// FTP Download
                if (ftp_get($ftp_con, "/home/fws/{$mac_no}_work_cnt.log", "/home/fws/usr/{$mac_no}_work_cnt.tmp", FTP_ASCII)) {
                    // echo 'FTPのDownloadに成功しました。';
                    ftp_delete($ftp_con, "/home/fws/usr/{$mac_no}_work_cnt.tmp");  // 旧ファイルは削除
                } else {
                    // echo 'FTPのDownloadに失敗しました。';
                    `/bin/rm -f /home/fws/{$mac_no}_work_cnt.log`;
                }
            } else {
                // echo 'FTPのrenameに失敗しました。';
                `/bin/rm -f /home/fws/{$mac_no}_work_cnt.log`;
            }
        }
        /////////// begin トランザクション開始
        if ($con = db_connect()) {
            query_affected_trans($con, 'begin');
            ///// Counter File Check        現在の加工数・日時を取得
            $cnt_file = "/home/fws/{$mac_no}_work_cnt.log";     // ディレクトリ・ファイル名生成
            $cnt_temp = "/home/fws/{$mac_no}_work_cnt.tmp";     // Rename 用ディレクトリ・ファイル名生成
            if (file_exists($cnt_file)) {                       // Counter File があれば
                if (rename($cnt_file, $cnt_temp)) {
                    $fp = fopen ($cnt_temp,'r');
                    $row  = 0;                                  // 全レコード
                    $data = array();                            // 年月日,時間,加工数
                    while ($data[$row] = fgetcsv ($fp, 50, ',')) {
                        if ($data[$row][0] != '') {
                            $row++;
                        }
                    }
                    if ($row >= 1) {            // Counter File にレコードがあれば状態と加工数書込み
                        ///// 現在のデータベースの最新レコードを取り込む
                        $query = "
                            SELECT mac_state, work_cnt FROM equip_work_log2
                            WHERE
                                equip_index(mac_no, siji_no, koutei, date_time) < '{$mac_no}{$siji_no}{$koutei}99999999999999'
                                AND
                                equip_index(mac_no, siji_no, koutei, date_time) > '{$mac_no}{$siji_no}{$koutei}00000000000000'
                            ORDER BY equip_index(mac_no, siji_no, koutei, date_time) DESC limit 1
                        ";
                        $res = array();
                        if ( ($rows = getResult($query, $res)) >= 1) {  // 前回の加工数にプラスして書込み
                            for ($j=0; $j<$row; $j++) {
                                $work_cnt  = $res[0]['work_cnt'] + ($j + 1);      // Counter UP
                                $date_time = $data[$j][0] . ' ' . $data[$j][1];     // PostgreSQLのTIMESTAMP型に変更
                                if ( ($state == 1) || ($state == 8) || ($state == 5) ) {     // 自動運転か無人運転又は段取中のはずなのでチェックして必要なら訂正
                                    $mac_state = $state;
                                } else {
                                    $mac_state = 1;     // Counterが進んでいるのに自動でも無人又は段取でもない場合は強制的に自動運転にする
                                }
                                $query = "insert into equip_work_log2
                                    (mac_no, date_time, mac_state, work_cnt, siji_no, koutei)
                                    values($mac_no, '$date_time', $mac_state, $work_cnt, $siji_no, $koutei)";
                                if (query_affected_trans($con, $query) <= 0) {
                                    $temp_msg = "FWS2 Counterの書き込みに失敗 レコード:$j : $mac_no : $date_time : $mac_state \n";
                                    $temp_msg .= $query;    // debug 用
                                    `echo "$temp_msg" >> /tmp/equipment_write_error2.log`;
                                    // query_affected_trans($con, 'rollback');         // transaction rollback
                                    // exit();
                                }
                            }
                        } else {                    // データベースが初回のため無条件に書込み
                            for ($j=0; $j<$row; $j++) {
                                $work_cnt  =  ($j + 1);   // 初回の場合はここが違う
                                $date_time = $data[$j][0] . ' ' . $data[$j][1];     // PostgreSQLのTIMESTAMP型に変更
                                // $mac_state = $state;     // 初回の場合は過去のデータを取込む可能性が高いため以下は必要
                                if ( ($state == 1) || ($state == 8) || ($state == 5) ) {     // 自動運転か無人運転又は段取中のはずなのでチェックして必要なら訂正
                                    $mac_state = $state;
                                } else {
                                    $mac_state = 1;     // Counterが進んでいるのに自動でも無人又は段取でもない場合は強制的に自動運転にする
                                }
                                $query = "insert into equip_work_log2
                                    (mac_no, date_time, mac_state, work_cnt, siji_no, koutei)
                                    values($mac_no, '$date_time', $mac_state, $work_cnt, $siji_no, $koutei)";
                                if (query_affected_trans($con, $query) <= 0) {
                                    $temp_msg = "fwserver5 初回データベースの書き込みに失敗 レコード:$j\n$query";
                                    `echo "$temp_msg" >> /tmp/equipment_write_error2.log`;
                                    // query_affected_trans($con, 'rollback');         // transaction rollback
                                    // exit();
                                } else {
                                    $query = "select str_timestamp from equip_work_log2_header
                                            where mac_no={$mac_no} and siji_no={$siji_no} and koutei={$koutei}
                                    ";
                                    if (getUniResTrs($con, $query, $str_timestamp) > 0) {
                                        $query = "select case when cast('$date_time' as TIMESTAMP) < cast('$str_timestamp' as TIMESTAMP)
                                                then (select 1) else (select 0) end";
                                        if (getUniResTrs($con, $query, $check_time) > 0) {
                                            if ($check_time == 1) {
                                                $query = "update equip_work_log2_header set str_timestamp='{$date_time}'
                                                        where mac_no={$mac_no} and siji_no={$siji_no} and koutei={$koutei}";
                                                if (query_affected_trans($con, $query) <= 0) {
                                                    $temp_msg = "初回データの日時比較でHeaderのUPDATEに失敗 mac_no:{$mac_no}";
                                                    `echo "$temp_msg" >> /tmp/equipment_write_error2.log`;
                                                }
                                            }
                                        }
                                    }
                                }
                            }
                        }
                    } else {                    // Counter File にレコードがないので状態のみチェックし書込み
                        ///// 現在のデータベースの最新レコードを取り込む
                        $query = "
                            SELECT mac_state, work_cnt FROM equip_work_log2
                            WHERE
                                equip_index(mac_no, siji_no, koutei, date_time) < '{$mac_no}{$siji_no}{$koutei}99999999999999'
                                AND
                                equip_index(mac_no, siji_no, koutei, date_time) > '{$mac_no}{$siji_no}{$koutei}00000000000000'
                            ORDER BY equip_index(mac_no, siji_no, koutei, date_time) DESC limit 1
                        ";
                        $res = array();
                        if ( ($rows = getResult($query, $res)) >= 1) {  // レコードがあれば状態をチェックする
                            if ($res[0]['mac_state'] != $state) {       // 状態が違えば書込む
                                $work_cnt  = $res[0]['work_cnt'];       // 前回の加工数を使う
                                // $date_time  = date('Ymd His.');             // 現在の時間を使う PostgreSQLのTIMESTAMP型に変更
                                // $date_time .= substr(microtime(), 2, 6);    // TIMESTAMP(6)で重複キーを回避
                                $date_time  = date('Ymd His');          // 現在の時間を使う PostgreSQLのTIMESTAMP型に変更
                                $mac_state = $state;
                                $query = "insert into equip_work_log2
                                    (mac_no, date_time, mac_state, work_cnt, siji_no, koutei)
                                    values($mac_no, '$date_time', $mac_state, $work_cnt, $siji_no, $koutei)";
                                if (query_affected_trans($con, $query) <= 0) {
                                    $temp_msg = "fwserver5 Counter File があるがレコードが無い場合のデータベースの書き込みに失敗 mac_no={$mac_no}";
                                    `echo "$temp_msg" >> /tmp/equipment_write_error2.log`;
                                    // query_affected_trans($con, 'rollback');         // transaction rollback
                                    // exit();
                                }
                            }
                        } else {            // 初回のため無条件に書込む
                            $work_cnt  = 0;             // 初回の場合は０
                            $date_time = date('Ymd His');   // 現在の時間を使う PostgreSQLのTIMESTAMP型に変更
                            $mac_state = $state;
                            $query = "insert into equip_work_log2
                                (mac_no, date_time, mac_state, work_cnt, siji_no, koutei)
                                values($mac_no, '$date_time', $mac_state, $work_cnt, $siji_no, $koutei)";
                            if (query_affected_trans($con, $query) <= 0) {
                                $temp_msg = "fwserver5 Counter File があるがレコードが無い場合の初回データベースの書き込みに失敗 mac_no={$mac_no}";
                                `echo "$temp_msg" >> /tmp/equipment_write_error2.log`;
                                // query_affected_trans($con, 'rollback');         // transaction rollback
                                // exit();
                            } else {
                                $query = "select str_timestamp from equip_work_log2_header
                                        where mac_no={$mac_no} and siji_no={$siji_no} and koutei={$koutei}
                                ";
                                if (getUniResTrs($con, $query, $str_timestamp) > 0) {
                                    $query = "select case when cast('$date_time' as TIMESTAMP) < cast('$str_timestamp' as TIMESTAMP)
                                            then (select 1) else (select 0) end";
                                    if (getUniResTrs($con, $query, $check_time) > 0) {
                                        if ($check_time == 1) {
                                            $query = "update equip_work_log2_header set str_timestamp='{$date_time}'
                                                    where mac_no={$mac_no} and siji_no={$siji_no} and koutei={$koutei}";
                                            if (query_affected_trans($con, $query) <= 0) {
                                                $temp_msg = "初回データの日時比較でHeaderのUPDATEに失敗 mac_no:{$mac_no}";
                                                `echo "$temp_msg" >> /tmp/equipment_write_error2.log`;
                                            }
                                        }
                                    }
                                }
                            }
                        }
                    }
                    unlink($cnt_temp);      // 一時ファイルの削除
                } else {
                    echo "カウンターファイルの rename() に失敗\n";
                }
            } else {                    // Counter File がないので状態のみ書込み
                ///// 現在のデータベースの最新レコードを取り込む
                $query = "
                    SELECT mac_state, work_cnt FROM equip_work_log2
                    WHERE
                        equip_index(mac_no, siji_no, koutei, date_time) < '{$mac_no}{$siji_no}{$koutei}99999999999999'
                        AND
                        equip_index(mac_no, siji_no, koutei, date_time) > '{$mac_no}{$siji_no}{$koutei}00000000000000'
                    ORDER BY equip_index(mac_no, siji_no, koutei, date_time) DESC limit 1
                ";
                $res = array();
                if ( ($rows = getResult($query, $res)) >= 1) {  // レコードがあれば状態をチェックする
                    if ($res[0]['mac_state'] != $state) {       // 状態が違えば書込む
                        $work_cnt  = $res[0]['work_cnt'];       // 前回の加工数をそのまま使う
                        // $date_time  = date('Ymd His.');             // 現在の時間を使う PostgreSQLのTIMESTAMP型に変更
                        // $date_time .= substr(microtime(), 2, 6);    // TIMESTAMP(6)で重複キーを回避
                        $date_time  = date('Ymd His');          // 現在の時間を使う PostgreSQLのTIMESTAMP型に変更
                        $mac_state = $state;
                        $query = "insert into equip_work_log2
                            (mac_no, date_time, mac_state, work_cnt, siji_no, koutei)
                            values($mac_no, '$date_time', $mac_state, $work_cnt, $siji_no, $koutei)";
                        if (query_affected_trans($con, $query) <= 0) {
                            $temp_msg = date('Y-m-d H:i:s ') . "fwserver5 equip_work_log2 へ状態書込みに失敗";
                            `echo "$temp_msg" >> /tmp/equipment_write_error2.log`;
                            // query_affected_trans($con, 'rollback');         // transaction rollback
                            // exit();
                        }
                    }
                } else {        // 初回のため無条件に書込む
                    $work_cnt  = 0;             // 初回の場合は０
                    $date_time = date('Ymd His');       // 現在の時間を使う PostgreSQLのTIMESTAMP型に変更
                    $mac_state = $state;
                    $query = "insert into equip_work_log2
                        (mac_no, date_time, mac_state, work_cnt, siji_no, koutei)
                        values($mac_no, '$date_time', $mac_state, $work_cnt, $siji_no, $koutei)";
                    if (query_affected_trans($con, $query) <= 0) {
                        $temp_msg = date('Y-m-d H:i:s ') . "fwserver5 equip_work_log2 へ初回の状態書込みに失敗";
                        `echo "$temp_msg" >> /tmp/equipment_write_error2.log`;
                        // query_affected_trans($con, 'rollback');         // transaction rollback
                        // exit();
                    } else {
                        $query = "select str_timestamp from equip_work_log2_header
                                where mac_no={$mac_no} and siji_no={$siji_no} and koutei={$koutei}
                        ";
                        if (getUniResTrs($con, $query, $str_timestamp) > 0) {
                            $query = "select case when cast('$date_time' as TIMESTAMP) < cast('$str_timestamp' as TIMESTAMP)
                                    then (select 1) else (select 0) end";
                            if (getUniResTrs($con, $query, $check_time) > 0) {
                                if ($check_time == 1) {
                                    $query = "update equip_work_log2_header set str_timestamp='{$date_time}'
                                            where mac_no={$mac_no} and siji_no={$siji_no} and koutei={$koutei}";
                                    if (query_affected_trans($con, $query) <= 0) {
                                        $temp_msg = "初回データの日時比較でHeaderのUPDATEに失敗 mac_no:{$mac_no}";
                                        `echo "$temp_msg" >> /tmp/equipment_write_error2.log`;
                                    }
                                }
                            }
                        }
                    }
                }
            }
            /////////// commit トランザクション終了
            query_affected_trans($con, 'commit');
        } else {
            echo "データベースの接続2に失敗\n";
            exit();
        }
    }
}
////////// FTP Close
if ($ftp_con) {
    ftp_close($ftp_con);
}
}   // $ftp_flg End





unlink($check_file);    // チェック用ファイルを削除
exit();
?>
