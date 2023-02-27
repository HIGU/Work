<?php
//////////////////////////////////////////////////////////////////////////////
// 設備･機械 管理2 (改訂版) ファンクッション ファイル                       //
// Copyright (C) 2002-2018 Kazuhiro Kobayashi tnksys@nitto-kohki.co.jp      //
// Changed history                                                          //
// 2002/02/15 Created  設備管理関係 equip_function.php                      //
// 2003/06/19 equip_header_to_csv()を中村留以外に対応                       //
// 2003/07/01 機械の状態を文字列で返す ロータリースイッチ版 追加            //
// 2003/07/02 equip_state_r()の２番無人運転を８番へ変更 Netmoniにあわせる   //
//            equip_working_chk()で Netmoni 以外に対応 equip_headerを使用   //
// 2003/07/03 CLI版とFunctionを共有するためrequire_once()を絶対指定に変更   //
// 2003/07/07 state_check()ロータリースイッチの適正チェック二段ロジック     //
// 2004/03/05 テーブルを新版へ移行の為、各関数を変更 equip_work_log2等      //
// 2004/06/19 netmoni関係を FTP 転送へ切替 equip_header_to_csv()            //
// 2004/06/24 header file に統一 equip_working_chk() equip_header_field()   //
// 2004/07/12 state_check_netmoni() Netmoni & FWS 方式を統一 スイッチ方式   //
// 2004/07/14 equip_header_to_csv()をmasterst以外はFTP転送しないに設定変更  //
// 2004/07/21 state_check()物理信号が電源OFFでも段取中と同様に故障修理も追加//
// 2004/07/26 equip_machine_state()はtnk_auto_logで使用のため$_SESSIONのchk //
// 2004/10/22 state_check_netmoni()にftp_close()が抜けていたのを訂正        //
// 2004/11/29 FWS3〜7を追加(1工場と5工場まで)                               //
// 2004/12/14 yaxis_min()に40000超えの条件等を追加                          //
// 2005/02/15 state_check_netmoni()にftp_connect()/ftp_loginで失敗した場合に//
//            sleep(2)２秒間遅延させるロジック追加                          //
// 2005/02/16 state_check_netmoni() パラメーターの追加 $ftp_con(ストリーム) //
// 2006/03/02 yaxis_min()に1,700,000超えの条件を追加 default=500,000        //
// 2006/03/03 del_equip_header_work()のequip_work_log2 WHERE区を最適化      //
// 2006/03/27 break_equip_header()の         〃                             //
// 2006/06/12 state_check()等に停止中でも暖機信号を取るように変更           //
// 2007/06/27 realpath(dirname(__FILE__)) に変更                            //
// 2018/05/18 ７工場を追加。コード４の登録を強制的に7に変更            大谷 //
// 2018/12/25 ７工場を真鍮とSUSに分離。後々の為。                      大谷 //
//////////////////////////////////////////////////////////////////////////////
// session_start();     // 呼出元で宣言されている条件だが念のため ２重宣言はエラー
require_once (realpath(dirname(__FILE__)) . "/../function.php");    // 内部でdefine.php pgsql.php を require()している。

// 設備管理のNetmoni用 稼働開始のヘッダーファイル。
define('EQUIP_INDEX',  '/home/netmoni4/data/input.csv');
// 設備管理のNetmoni用 ログデータ保存ディレクトリ
define('EQUIP_LOG_DIR', '/home/netmoni4/data/');
// 設備管理のNetmoni用 ログバックアップ保存ディレクトリ
define("EQUIP_BACKUP_DIR","/home/netmoni4/data/backup/");
// 機械の状態０〜１５の定義 中留製NC Netmoni 版     0:電源OFF 1:自動運転 2:アラーム 3:停止中 は共通
define("M_STAT_MAX_NO",15);
// 機械の状態０〜１１の定義 ロータリースイッチ版        〃
define("R_STAT_MAX_NO",11);

// FTP関係の定義    Netmoni
define('NET_HOST', '10.1.3.145');           // ターゲットホスト
define('NET_USER', 'netmoni4');             // 接続ユーザー名
define('NET_PASS', 'netmoni');              // パスワード
define('REMOTE_INDEX', 'input.csv');        // FTP転送へ変更のため追加
define('LOCAL_NAME', '/home/netmoni4/data/input.csv');   // ローカルのフルパスファイル名
// define('REMOTE_LOG', '134924268CP01037-501500.csv');  機械番号+指示番号+部品番号+工程+計画数

// FTP関係の定義    fws1  fwserver1.tnk.co.jp
define('FWS1', '10.1.3.41');                // 7工場真鍮
//                  fws2  fwserver2.tnk.co.jp
define('FWS2', '10.1.3.42');                // 7工場真鍮
//                  fws3  fwserver3.tnk.co.jp
define('FWS3', '10.1.3.43');                // 7工場SUS
//                  fws4  fwserver4.tnk.co.jp
define('FWS4', '10.1.3.44');                // 4工場
//                  fws5  fwserver5.tnk.co.jp
define('FWS5', '10.1.3.45');                // 1工場
//                  fws6  fwserver6.tnk.co.jp
define('FWS6', '10.1.3.46');                // 5工場
//                  fws7  fwserver7.tnk.co.jp
define('FWS7', '10.1.3.47');                // 5工場

define('FWS_USER', 'fws');                  // 接続ユーザー名
define('FWS_PASS', 'fws');                  // パスワード
define('REMOTE_DIR', '/home/fws/usr/');     // リモートディレクトリ
define('LOCAL_DIR',  '/home/fws/');         // ローカルディレクトリ
///// file は以下の書式
// file=(mac_no)_work_state.log
// file=(mac_no)-bcd1.log
// file=(mac_no)-bcd2.log
// file=(mac_no)-bcd4.log
// file=(mac_no)-bcd8.log
// file=(mac_no)_work_cnt.log

/////// 物理信号でロータリースイッチの適正を判断し、状態番号を返す
/////// Netmoni対応版 0:電源OFF 1:自動運転 2:アラーム 3:停止中 のFWS方式と共通部分を
/////// 使用して後はロータリースイッチを使う
/////// 以下はハード信号が正確に出力されている事を前提とする
/////// 2005/02/16 パラメーターの追加 $ftp_con(FTPストリーム)
function state_check_netmoni($ftp_con, $state_p, $mac_no)
{
    $query = "select csv_flg from equip_machine_master2 where mac_no=$mac_no limit 1";
    getUniResult($query, $csv_flg);
    switch ($csv_flg) {
    case 101:         // 101=Net&FWS 対応版
    // case 102:      // 将来増えた場合に対応
    // case 103:      // 将来増えた場合に対応
        break;
    default:
        return $state_p;
    }
    
    if ($ftp_con == false) {
        return $state_p;    // FTP接続できない場合(又はNetmoni単独の場合)は物理信号をそのまま返す
    } else {
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
        $state_bcd = 0;                                     // 初期化 = 電源OFF
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
    }
    ///// 物理信号とロータリースイッチとの条件チェック
    if ($state_p == 0) {            // 電源OFF(物理信号)
        switch ($state_bcd) {
        case (5):                   // 段取中
            return(5);
            break;
        default:                    // その他は電源OFF
            return(0);
        }
    } elseif ($state_p == 1) {      // 運転中(物理信号)
        switch ($state_bcd) {
        case (1):                   // 自動運転
            return(1);
            break;
        case (4):                   // 暖機中
            return(4);
            break;
        case (5):                   // 段取中
            return(5);
            break;
        case (8):                   // 無人運転
            return(8);
            break;
        default:                    // その他は自動運転
            return(1);
        }
    } elseif ($state_p == 2) {      // アラーム(物理信号)
        switch ($state_bcd) {
        case (2):                   // アラーム
            return(2);
            break;
        case (5):                   // 段取中
            return(5);
            break;
        case (6):                   // 故障修理
            return(6);
            break;
        case (7):                   // 刃具交換
            return(7);
            break;
        case (10):                  // 段取待ち
            return(10);
            break;
        case (11):                  // 修理待ち
            return(11);
            break;
        default:                    // その他は停止中
            return(3);
        }
    } elseif ($state_p == 3) {      // 停止中(物理信号)
        switch ($state_bcd) {
        case (2):                   // 材料、刃具待ち
            return(2);
            break;
        case (3):                   // 停止中
            return(3);
            break;
        case (4):                   // 暖機中
            return(4);
            break;
        case (5):                   // 段取中
            return(5);
            break;
        case (6):                   // 故障修理
            return(6);
            break;
        case (7):                   // 刃具交換
            return(7);
            break;
        case (10):                  // 段取待ち
            return(10);
            break;
        case (11):                  // 修理待ち
            return(11);
            break;
        case (9):                   // 中断
            return(9);
            break;
        default:                    // その他は停止中
            return(3);
        }
    } else {                        // その他の自動で出る物理信号は Net起動・終了
        return(9);                  // 中断(無難なところ)
    }
}


/////// 物理信号でロータリースイッチの適正を判断し、状態番号を返す
/////// 以下はハード信号が正確に出力されている事を前提とする
function state_check($state_p, $state_bcd)
{
    if ($state_p == 1) {            // 運転中(物理信号)
        switch ($state_bcd) {
        case (1):                   // 自動運転
            return(1);
            break;
        case (4):                   // 暖機中
            return(4);
            break;
        case (5):                   // 段取中
            return(5);
            break;
        case (8):                   // 無人運転
            return(8);
            break;
        default:                    // その他は自動運転
            return(1);
        }
    } elseif ($state_p == 3) {      // 停止中(物理信号)
        switch ($state_bcd) {
        case (3):                   // 停止中
            return(3);
            break;
        case (2):                   // アラーム(物理信号が無いためロータリースイッチで取る)
            return(2);
            break;
        case (4):                   // 暖機中
            return(4);
            break;
        case (5):                   // 段取中
            return(5);
            break;
        case (6):                   // 故障修理
            return(6);
            break;
        case (7):                   // 刃具交換
            return(7);
            break;
        case (9):                   // 中断
            return(9);
            break;
        case (10):                   // 段取待ち
            return(10);
            break;
        case (11):                   // 修理待ち
            return(11);
            break;
        default:                    // その他は停止中
            return(3);
        }
    } elseif ($state_p == 2) {      // アラーム(物理信号)現在まだ信号はない(予定)
        switch ($state_bcd) {
        case (2):                   // アラーム
            return(2);
            break;
        case (5):                   // 段取中
            return(5);
            break;
        case (6):                   // 故障修理
            return(6);
            break;
        case (7):                   // 刃具交換
            return(7);
            break;
        default:
            return(3);
        }
    } else {                        // 電源OFF(物理信号)
        switch ($state_bcd) {
        case (5):                   // 段取中
            return(5);
            break;
        case (6):                   // 故障修理
            return(6);
            break;
        default:                    // その他は電源OFF
            return(0);
        }
    }
}


///// グラフ用の時系列データ サンプリングタイム設定。
function sampling($log_cnt)
{
    switch (TRUE) {             ////////// true にすればcase分で論理式を入れられる｡
    case ($log_cnt <= 10):
        return(10);             // 10秒
        break;
    case ($log_cnt <= 60):
        return(30);             // 30秒
        break;
    case ($log_cnt <= 100):
        return(60);             //  1分
        break;
    case ($log_cnt <= 200):
        return(120);            //  2分
        break;
    case ($log_cnt <= 2000):
        return(600);            // 10分
        break;
    case ($log_cnt <= 4000):
        return(3600);           // 60分
        break;
    case ($log_cnt <= 8000):
        return(7200);           // 120分
        break;
    case ($log_cnt <= 16000):
        return(14400);           // 240分のサンプリング 2003/07/11 変更
        break;
    default:
        return(28800);           // 480分のサンプリング 2003/07/11 追加
    }
}


// グラフ用のY軸min設定関数 maxを指定して自動でminを返す。
// JpGraph のバージョンで微調整が必要
function yaxis_min($max_data)
{
    switch (TRUE) {                       // true にすればcase分で論理式を入れられる｡
    case ($max_data <= 0):
        return(-5);
        break;
    case ($max_data <= 5):
        return(-1);
        break;
    case ($max_data <= 10):
        return(-2);
        break;
    case ($max_data <= 40):
        return(-10);
        break;
    case ($max_data <= 150):
        return(-20);
        break;
    case ($max_data < 350):
        return(-50);
        break;
    case ($max_data < 700):
        return(-100);
        break;
    case ($max_data < 1500):
        return(-200);
        break;
    case ($max_data < 3800):
        return(-500);                   // オリジナルは -400 微調整済み 大きめにしておけば問題ないようである
        break;
    case ($max_data < 8000):
        return(-1000);                  // 2003/07/11 8000 を超えたので追加
        break;
    case ($max_data < 13000):
        return(-2000);                  // 2003/07/19 16000 を超えたので追加
        break;
    case ($max_data < 15000):
        return(-4000);                  // 2005/02/23 追加
        break;
    case ($max_data < 40000):
        return(-5000);                  // 2004/12/14 40000 を超えたので追加
        break;
    case ($max_data < 60000):
        return(-10000);                 // 2004/12/17 60000 を超えたので追加
        break;
    case ($max_data < 100000):
        return(-20000);                 // 2004/12/17 予測で追加
        break;
    case ($max_data < 150000):
        return(-40000);                 // 2005/01/25 追加
        break;
    case ($max_data < 300000):
        return(-50000);                 // 2005/03/11 追加
        break;
    case ($max_data < 800000):
        return(-100000);                // 2005/07/22 追加
        break;
    case ($max_data < 1700000):
        return(-200000);                // 2006/03/02 追加
        break;
    default:
        return(-500000);                // 2006/03/02 変更
    }
}


// 設備管理の稼働開始のヘッダーファイル。テスト用
define("EQUIP_TEST","/home/netmoni4/data/test.csv");

// 中村留のＮＣ用にCSV File を 出力する equip_header のwork_flg IS TRUE とend_timestamp IS NULL と
//   equip_machine_master の csv_flg = 1 or csv_flg = 101(Net&FWS) をチェックする｡ 2004/07/13 Add k.kobayashi
function equip_header_to_csv()
{
    $query = "select mac_no
                    ,siji_no
                    ,parts_no
                    ,koutei
                    ,plan_cnt
                from 
                    equip_work_log2_header
                left outer join
                    equip_machine_master2
                using(mac_no)
                where
                    work_flg IS TRUE and
                    end_timestamp IS NULL and
                    (csv_flg = 1 or csv_flg = 101)
                order by str_timestamp
            ";
    $res = array();
    if ( ($rows=getResult($query,$res)) >= 0) {     // データベースのヘッダーより CSV 出力
        if ( ($fp=fopen(EQUIP_INDEX,"w")) ) {;
            for ($i=0; $i<$rows; $i++) {
                $data = NULL;
                for ($f=0; $f<5; $f++) {
                    $data .= $res[$i][$f];
                    if ($f != 4) {
                        $data .= ",";
                    } else {
                        // $data .= "\r\n";
                        $data .= "\n";  // ローカル形式に変更 FTPへ切替えたため
                    }
                }
                if (fwrite($fp,$data) == -1) {
                    $_SESSION['s_sysmsg'] = "CSV File Write Error No:$r";   // debug用
                    fclose($fp);
                    return FALSE;       // CSV ファイルの書込み失敗
                }
            }
            fclose($fp);
        } else {
            $_SESSION['s_sysmsg'] = "CSV File Open Error";                  // debug用
            return FALSE;   // CSV ファイルのオープンに失敗
        }
    } else {
        $_SESSION['s_sysmsg'] = 'equip_machine_master SQL Error';           // debug用
        return FALSE;   // 機械マスターの読込失敗
    }
    // $_SESSION['s_sysmsg'] = "CSV ファイルの書込み成功";           // debug用
    ////////// testerst 等のテスト用に対応するため実際の FTP転送はしないで終了する
    if ($_SERVER['SERVER_ADDR'] != '10.1.3.252') {      // masterst(www.tnk.co.jp)以外は実行しない
        return TRUE;
    }
    ///////// FTP 転送 開始
    // コネクションを取る(FTP接続のオープン)
    if ($ftp_stream = ftp_connect(NET_HOST)) {
        if (ftp_login($ftp_stream, NET_USER, NET_PASS)) {
            ///// Netmoni Server へコントロールファイル(通信中断指示)を送信
            if (ftp_put($ftp_stream, REMOTE_INDEX, LOCAL_NAME, FTP_ASCII)) {
                ///// 成功時の処理は現在ない
            } else {
                $_SESSION['s_sysmsg'] = REMOTE_INDEX . ' upload Error';
            }
        } else {
            $_SESSION['s_sysmsg'] = 'ftp_login() Error';
        }
        ftp_close($ftp_stream);
    } else {
        $_SESSION['s_sysmsg'] = 'ftp_connect() error';
    }
    return TRUE;    // CSV file 出力 OK & FTP 転送 OK
}


// 製造課の予定計画より運転開始処理 (Transaction処理)
function trans_equip_plan_to_start($m_no,$s_no,$b_no,$k_no,$p_no){
    $update_qry = "update equip_plan set plan_flg=FALSE 
        where mac_no='$m_no' and siji_no='$s_no' and buhin_no='$b_no' and koutei='$k_no'"; 
    $str_date = date('Y-m-d H:i:s');
    $insert_qry = "insert into equip_work_log2_header (mac_no, siji_no, koutei, parts_no, plan_no, str_timestamp,work_flg) 
            values($m_no, $s_no, $k_no, '$b_no', $p_no, '$str_date', TRUE)";
    if (funcConnect()) {
        execQuery('begin');
        if(execQuery($update_qry) >= 0) {
            if(execQuery($insert_qry) >= 0) {
                execQuery('commit');
                disConnectDB();
                return true;
            } else {
                execQuery('rollback');
                disConnectDB();
                $error_msg = date('Y/m/d H:i:s', mktime());
                $error_msg .= "-execQuery: $insert_qry";
                `echo "$error_msg" >> /tmp/equipment_write_error2.log`;
            }
        } else {
            execQuery('rollback');
            disConnectDB();
            $error_msg = date('Y/m/d H:i:s', mktime());
            $error_msg .= "-execQuery: $update_qry";
            `echo "$error_msg" >> /tmp/equipment_write_error2.log`;
        }
    } else {
        $error_msg = date('Y/m/d H:i:s', mktime());
        $error_msg .= "-funcConnect: $update_qry";
        `echo "$error_msg" >> /tmp/equipment_write_error2.log`;
    }
    return false;
}


// 製造課の予定計画 登録
function add_equip_plan($m_no,$s_no,$b_no,$k_no,$p_no,$str_d,$end_d){
    $rec_date_time = mktime();
    $insert_qry = "insert into equip_plan (mac_no, siji_no, buhin_no, koutei, plan_su, plan_str,plan_end,rec_date) 
            values('$m_no','$s_no','$b_no','$k_no',$p_no,$str_d,$end_d,$rec_date_time)";
    if (funcConnect()) {
        execQuery("begin");
        if (execQuery($insert_qry) >= 0) {
            execQuery("commit");
            disConnectDB();
            return true;
        } else {
            execQuery("rollback");
            disConnectDB();
            $error_msg = date("Y/m/d H:i:s",mktime());
            $error_msg .= "-execQuery: $insert_qry";
            `echo "$error_msg" >> /tmp/equipment_write_error.log`;
        }
    } else {
        $error_msg = date("Y/m/d H:i:s",mktime());
        $error_msg .= "-funcConnect: $insert_qry";
        `echo "$error_msg" >> /tmp/equipment_write_error.log`;
    }
    return false;
}

// 製造課の予定計画 データ修正
function chg_equip_plan($pm_no,$ps_no,$pb_no,$pk_no,$m_no,$s_no,$b_no,$k_no,$p_no,$s_date,$e_date){
    $update_qry = "update equip_plan set mac_no='$m_no', siji_no='$s_no', buhin_no='$b_no',koutei='$k_no', plan_su=$p_no,plan_str=$s_date,plan_end=$e_date 
        where mac_no='$pm_no' and siji_no='$ps_no' and buhin_no='$pb_no' and koutei='$pk_no'"; 
    if (funcConnect()) {
        execQuery("begin");
        if (execQuery($update_qry) >= 0) {
            execQuery("commit");
            disConnectDB();
            return true;
        } else {
            execQuery("rollback");
            disConnectDB();
            $error_msg = date("Y/m/d H:i:s",mktime());
            $error_msg .= "-execQuery: $update_qry";
            `echo "$error_msg" >> /tmp/equipment_write_error.log`;
        }
    } else {
        $error_msg = date("Y/m/d H:i:s",mktime());
        $error_msg .= "-funcConnect: $update_qry";
        `echo "$error_msg" >> /tmp/equipment_write_error.log`;
    }
    return false;
}

// 製造課の予定計画データ削除
function del_equip_plan($kikaino,$seizousiji,$buhinno,$kouteino){
    $delete_qry = "delete from equip_plan where mac_no='$kikaino' and siji_no='$seizousiji' and 
            buhin_no='$buhinno' and koutei='$kouteino'";
    if (funcConnect()) {
        execQuery("begin");
        if (execQuery($delete_qry) >= 0) {
            execQuery("commit");
            disConnectDB();
            return true;
        } else {
            execQuery("rollback");
            disConnectDB();
            $error_msg = date("Y/m/d H:i:s",mktime());
            $error_msg .= "-execQuery: $delete_qry";
            `echo "$error_msg" >> /tmp/equipment_write_error.log`;
        }
    } else {
        $error_msg = date("Y/m/d H:i:s",mktime());
        $error_msg .= "-funcConnect: $delete_qry";
        `echo "$error_msg" >> /tmp/equipment_write_error.log`;
    }
    return false;
}



// 製造課の機械運転 完了指示 equip_header のend_timestampに完了時間書込み work_flg を FALSE へ
function end_equip_header($m_no, $s_no, $b_no, $k_no, $jisseki)
{
    $end_timestamp = date('Y-m-d H:i:s');
    $update_qry = "update equip_work_log2_header set end_timestamp='$end_timestamp', work_flg=FALSE, jisseki={$jisseki}
        where mac_no={$m_no} and siji_no={$s_no} and koutei={$k_no}"; 
    if (funcConnect()) {
        execQuery('begin');
        if (execQuery($update_qry) >= 0) {
            execQuery('commit');
            disConnectDB();
        } else {
            execQuery('rollback');
            disConnectDB();
            $error_msg = date('Y/m/d H:i:s', mktime());
            $error_msg .= "-execQuery:完了:$update_qry";
            `echo "$error_msg" >> /tmp/equipment_write_error2.log`;
        }
    } else {
        $error_msg = date('Y/m/d H:i:s', mktime());
        $error_msg .= "-funcConnect:完了:$update_qry";
        `echo "$error_msg" >> /tmp/equipment_write_error2.log`;
    }
}



// 製造課の機械運転 中断/再開時の ヘッダーファイル処理 work_flg IS FALSE(中断) TRUE(再開)
function break_equip_header($m_no, $s_no, $b_no, $k_no, $flag)
{
    if ($flag == FALSE) {
        ///// 機械マスターの csv_flg から Netmoni/ロータリースイッチ方式の取得
        $query = "select mac_name, csv_flg from equip_machine_master2 where mac_no={$m_no} limit 1";
        $res = array();
        if (($rows=getResult($query,$res))>=1) {      // 機械マスターから機械名を取得する
            $name = substr($res[0][0],0,10);
            $csv_flg = $res[0][1];
        } else {
            $name = "     ";
            $csv_flg = 0;       // 1以外はロータリースイッチ方式とする
        }
        
        // equip_work_log へ中断データを書き込むため最新データを確認する
            // 旧SQL = where mac_no={$m_no} and siji_no={$s_no} and koutei={$k_no} and mac_state<>0 order by date_time DESC limit 1";
        $query = "select work_cnt from equip_work_log2
            WHERE
            equip_index(mac_no, siji_no, koutei, date_time) > '{$m_no}{$s_no}{$k_no}00000000000000'
            AND
            equip_index(mac_no, siji_no, koutei, date_time) < '{$m_no}{$s_no}{$k_no}99999999999999'
            AND
            mac_state != 0
            ORDER BY equip_index(mac_no, siji_no, koutei, date_time) DESC
            LIMIT 1
        ";
        $res=array();
        if (($rows=getResult($query,$res))>=1) {      // 最新データがあれば前のデータをセットする
            $pre_cnt  = $res[0][0];
            if ($csv_flg == 1) {    // Netmoni方式 = 15(中断)
                $insert_qry = "insert into equip_work_log2 (mac_no, date_time, mac_state, work_cnt, siji_no, koutei)
                            values({$m_no}, '" . date('Y-m-d H:i:s') . "', 15, $pre_cnt, {$s_no}, {$k_no})
                        ";
            } else {                // ロータリースイッチ方式 = 9(中断)
                $insert_qry = "insert into equip_work_log2 (mac_no, date_time, mac_state, work_cnt, siji_no, koutei)
                            values({$m_no}, '" . date('Y-m-d H:i:s') . "', 9, $pre_cnt, {$s_no}, {$k_no})
                        ";
            }
        } else {
            if ($csv_flg == 1) {    // Netmoni方式 = 15(中断)
                $insert_qry = "insert into equip_work_log2 (mac_no, date_time, mac_state, work_cnt, siji_no, koutei)
                                values({$m_no}, '" . date('Y-m-d H:i:s') . "', 15, 0, {$s_no}, {$k_no})
                            ";
            } else {                // ロータリースイッチ方式 = 9(中断)
                $insert_qry = "insert into equip_work_log2 (mac_no, date_time, mac_state, work_cnt, siji_no, koutei)
                                values({$m_no}, '" . date('Y-m-d H:i:s') . "', 9, 0, {$s_no}, {$k_no})
                            ";
            }
        }
        if (funcConnect()) {
            execQuery('begin');
            if (execQuery($insert_qry) >= 0) {
                execQuery('commit');
                disConnectDB();
            } else {
                execQuery("rollback");
                disConnectDB();
                $error_msg = date('Y/m/d H:i:s', mktime());
                $error_msg .= "-execQuery: $insert_qry";
                `echo "$error_msg" >> /tmp/equipment_write_error2.log`;
            }
        } else {
            $error_msg = date('Y/m/d H:i:s', mktime());
            $error_msg .= "-funcConnect: $insert_qry";
            `echo "$error_msg" >> /tmp/equipment_write_error2.log`;
        }
        $update_qry = "update equip_work_log2_header set work_flg = FALSE where mac_no={$m_no} and siji_no={$s_no} and koutei={$k_no}";
    } else {
        $update_qry = "update equip_work_log2_header set work_flg = TRUE where mac_no={$m_no} and siji_no={$s_no} and koutei={$k_no}";
    }
    if (funcConnect()) {
        execQuery('begin');
        if (execQuery($update_qry) >= 0) {
            execQuery("commit");
            disConnectDB();
            return true;
        } else {
            execQuery('rollback');
            disConnectDB();
            $error_msg = date('Y/m/d H:i:s', mktime());
            $error_msg .= "-execQuery: $update_qry";
            `echo "$error_msg" >> /tmp/equipment_write_error2.log`;
        }
    } else {
        $error_msg = date('Y/m/d H:i:s', mktime());
        $error_msg .= "-funcConnect: $update_qry";
        `echo "$error_msg" >> /tmp/equipment_write_error2.log`;
    }
    return false;
}

// 製造課の機械運転開始データ登録(ヘッダーファイル & 経歴)
function add_equip_header($kikaino, $seizousiji, $buhinno, $kouteino, $seisansuu, $str_timestamp)
{
    $insert_qry = "insert into equip_work_log2_header (mac_no, siji_no, parts_no, koutei, plan_cnt, str_timestamp,work_flg) 
            values($kikaino, $seizousiji, '$buhinno', $kouteino, $seisansuu, '$str_timestamp', TRUE)";
    if (funcConnect()) {
        execQuery('begin');
        if (execQuery($insert_qry)>=0) {
            execQuery('commit');
            disConnectDB();
            return true;
        } else {
            execQuery('rollback');
            disConnectDB();
            $error_msg  = date('Y/m/d H:i:s', mktime());
            $error_msg .= "-execQuery: $insert_qry";
            `echo "$error_msg" >> /tmp/equipment_write_error2.log`;
        }
    } else {
        $error_msg  = date('Y/m/d H:i:s', mktime());
        $error_msg .= "-funcConnect: $insert_qry";
        `echo "$error_msg" >> /tmp/equipment_write_error2.log`;
    }
    return false;
}

// 製造課の機械運転開始データ削除(ヘッダーファイル & 経歴)(トランザクション処理)
function del_equip_header_work($kikaino, $seizousiji, $buhinno, $kouteino)
{
    $delete_header = "delete from equip_work_log2_header where mac_no={$kikaino} and siji_no={$seizousiji} and 
                        koutei={$kouteino}";
    $delete_work = "
        DELETE FROM equip_work_log2
        WHERE
        equip_index(mac_no, siji_no, koutei, date_time) > '{$kikaino}{$seizousiji}{$kouteino}00000000000000'
        AND
        equip_index(mac_no, siji_no, koutei, date_time) < '{$kikaino}{$seizousiji}{$kouteino}99999999999999'
    ";
    if (funcConnect()) {
        execQuery('begin');
        if (execQuery($delete_header)>=0) {
            if (execQuery($delete_work)>=0) {
                execQuery('commit');
                disConnectDB();
                return true;
            } else {
                execQuery('rollback');
                disConnectDB();
                $error_msg = date('Y/m/d H:i:s', mktime());
                $error_msg .= "-execQuery: $delete_work Transaction";
                `echo "$error_msg" >> /tmp/equipment_write_error.log`;
            }
        } else {
            execQuery('rollback');
            disConnectDB();
            $error_msg = date('Y/m/d H:i:s', mktime());
            $error_msg .= "-execQuery: $delete_header Transaction";
            `echo "$error_msg" >> /tmp/equipment_write_error.log`;
        }
    } else {
        $error_msg = date('Y/m/d H:i:s', mktime());
        $error_msg .= "-funcConnect: $delete_header Transaction";
        `echo "$error_msg" >> /tmp/equipment_write_error.log`;
    }
    return false;
}


// 製造課の機械運転 equip_work_log2_header equip_work_log データ修正(トランザクション)
function chg_equip_header_work($pm_no, $ps_no, $pb_no, $pk_no, $m_no, $s_no, $b_no, $k_no, $p_no)
{
    $update_header = "update
                            equip_work_log2_header
                        set mac_no={$m_no}
                            , siji_no={$s_no}
                            , parts_no='{$b_no}'
                            , koutei={$k_no}
                            , plan_cnt={$p_no}
                        where
                            mac_no={$pm_no} and siji_no={$ps_no} and koutei={$pk_no}
                    "; 
    $update_work = "update
                            equip_work_log2
                        set mac_no={$m_no}
                            , siji_no={$s_no}
                            , koutei={$k_no}
                        where
                            mac_no={$pm_no} and siji_no={$ps_no} and koutei={$pk_no}
                    ";
    if (funcConnect()) {
        execQuery('begin');
        if (execQuery($update_header) >= 0) {
            if (execQuery($update_work) >= 0) {
                execQuery('commit');
                disConnectDB();
                return true;
            } else {
                execQuery('rollback');
                disConnectDB();
                $error_msg = date('Y/m/d H:i:s', mktime());
                $error_msg .= "-execQuery: $update_work Transaction";
                `echo "$error_msg" >> /tmp/equipment_write_error2.log`;
            }
        } else {
            execQuery('rollback');
            disConnectDB();
            $error_msg = date('Y/m/d H:i:s', mktime());
            $error_msg .= "-execQuery: $update_header Transaction";
            `echo "$error_msg" >> /tmp/equipment_write_error2.log`;
        }
    } else {
        $error_msg = date('Y/m/d H:i:s', mktime());
        $error_msg .= "-funcConnect: $update_header_work";
        `echo "$error_msg" >> /tmp/equipment_write_error2.log`;
    }
    return false;
}



// 製造課の機械運転 equip_work_log データ修正
function chg_equip_work_log($pm_no, $ps_no, $pb_no, $pk_no, $m_no, $s_no, $b_no, $k_no, $p_no)
{
    $update_qry = "update equip_work_log2 set mac_no={$m_no}, siji_no={$s_no}, koutei={$k_no}
        where mac_no={$pm_no} and siji_no={$ps_no} and koutei={$pk_no}"; 
    if (funcConnect()) {
        execQuery('begin');
        if (execQuery($update_qry) >= 0) {
            execQuery('commit');
            disConnectDB();
            return true;
        } else {
            execQuery('rollback');
            disConnectDB();
            $error_msg = date('Y/m/d H:i:s', mktime());
            $error_msg .= "-execQuery: $update_qry";
            `echo "$error_msg" >> /tmp/equipment_write_error2.log`;
        }
    } else {
        $error_msg = date('Y/m/d H:i:s', mktime());
        $error_msg .= "-funcConnect: $update_qry";
        `echo "$error_msg" >> /tmp/equipment_write_error2.log`;
    }
    return false;
}



// 製造課の機械運転開始データ修正(ヘッダーファイル & 経歴)
function chg_equip_header($pm_no, $ps_no, $pb_no, $pk_no, $m_no, $s_no, $b_no, $k_no, $p_no)
{
    $update_qry = "update equip_work_log2_header set mac_no={$m_no}, siji_no={$s_no}, parts_no='$b_no', koutei={$k_no}, plan_cnt={$p_no}
        where mac_no={$pm_no} and siji_no={$ps_no} and koutei={$pk_no}"; 
    if (funcConnect()) {
        execQuery('begin');
        if (execQuery($update_qry) >= 0) {
            execQuery('commit');
            disConnectDB();
            return true;
        } else {
            execQuery('rollback');
            disConnectDB();
            $error_msg = date('Y/m/d H:i:s', mktime());
            $error_msg .= "-execQuery: $update_qry";
            `echo "$error_msg" >> /tmp/equipment_write_error2.log`;
        }
    } else {
        $error_msg = date('Y/m/d H:i:s', mktime());
        $error_msg .= "-funcConnect: $update_qry";
        `echo "$error_msg" >> /tmp/equipment_write_error2.log`;
    }
    return false;
}



// 設備管理の機械の状態を文字列で返す。中留製NC Netmoni版と
// 設備管理の機械の状態を文字列で返す。ロータリースイッチ版
// ２番の無人運転を８番へ変更 Netmoni に合わせるため ０〜３(電源OFF・自動運転・アラーム・停止)を共通化
function equip_machine_state($mac_no, $state, &$bg_color, &$txt_color)
{
   ///// 機械マスターより稼動方式取得
    $query = "select csv_flg from equip_machine_master2 where mac_no={$mac_no}";
    if (getUniResult($query, $state_type) <= 0) {
        if (isset($_SESSION)) {
            $_SESSION['s_sysmsg'] .= "機械マスターから稼動方式の取得に失敗 mac_no=$state ";    // debug 用
            //return FALSE;
        } else {
            `echo "$query" >> /tmp/equipment_write_error2.log`;
        }
    }
    if ($state_type == 1) {                             // Netmoni タイプ
        switch ($state) {
        case 0:
            $bg_color = "black";
            $txt_color = "white";
            return "電源OFF";
            break;
        case 1:
            $bg_color = "green";
            $txt_color = "white";
            return "自動運転";
            break;
        case 2:
            $bg_color = "red";
            $txt_color = "white";
            return "アラーム";
            break;
        /*
        case 2:
            $bg_color = "red";
            $txt_color = "white";
            return "材料・刃具待ち";
            break;
        */
        case 3:
            $bg_color = "yellow";
            $txt_color = "black";
            return "停 止 中";
            break;
        /*
        case 3:
            $bg_color = "yellow";
            $txt_color = "black";
            return "チョコ停";
            break;
        */
        case 4:
            $bg_color = "orange";
            $txt_color = "black";
            return "Net起動";
            break;
        case 5:
            $bg_color = "maroon";
            $txt_color = "white";
            return "Net終了";
            break;
        case 10:
            $bg_color = "purple";
            $txt_color = "white";
            return "暖 機 中";
            break;
        /*
        case 10:
            $bg_color = "purple";
            $txt_color = "white";
            return "立上準備";
            break;
        */
        case 11:
            $bg_color = "aqua";
            $txt_color = "black";
            return "段 取 中";
            break;
        case 12:
            $bg_color = "gray";
            $txt_color = "white";
            return "故障修理";
            break;
        case 13:
            $bg_color = "silver";
            $txt_color = "black";
            return "刃具交換";
            break;
        case 14:
            $bg_color = "blue";
            $txt_color = "white";
            return "無人運転";
            break;
        case 15:
            $bg_color = "magenta";
            $txt_color = "black";
            return "中　　断";
            break;
        /*
        case 15:
            $bg_color = "magenta";
            $txt_color = "black";
            return "その他停止";
            break;
        case 16:
            $bg_color = "maroon";
            $txt_color = "white";
            return "段取待ち";
            break;
        case 17:
            $bg_color = "magenta";
            $txt_color = "black";
            return "修理待ち";
            break;
        */
        default:
            $bg_color = "";
            $txt_color = "red";
            return "未 登 録";
        }
    } else {                                            // その他(ロータリースイッチ等)
        switch ($state) {
        case 0:
            $bg_color = "black";
            $txt_color = "white";
            return "電源OFF";
            break;
        case 1:
            $bg_color = "green";
            $txt_color = "white";
            return "自動運転";
            break;
        case 2:
            $bg_color = "red";
            $txt_color = "white";
            return "アラーム";
            break;
        /*
        case 2:
            $bg_color = "red";
            $txt_color = "white";
            return "材料・刃具待ち";
            break;
        */
        case 3:
            $bg_color = "yellow";
            $txt_color = "black";
            return "停 止 中";
            break;
        /*
        case 3:
            $bg_color = "yellow";
            $txt_color = "black";
            return "チョコ停";
            break;
        */
        case 4:
            $bg_color = "purple";
            $txt_color = "white";
            return "暖 機 中";
            break;
        /*
        case 4:
            $bg_color = "purple";
            $txt_color = "white";
            return "立上準備";
            break;
        */
        case 5:
            $bg_color = "aqua";
            $txt_color = "black";
            return "段 取 中";
            break;
        case 6:
            $bg_color = "gray";
            $txt_color = "white";
            return "故障修理";
            break;
        case 7:
            $bg_color = "silver";
            $txt_color = "black";
            return "刃具交換";
            break;
        case 8:
            $bg_color = "blue";
            $txt_color = "white";
            return "無人運転";
            break;
        case 9:
            $bg_color = "magenta";
            $txt_color = "black";
            return "中　　断";
            break;
        /*
        case 9:
            $bg_color = "magenta";
            $txt_color = "black";
            return "その他停止";
            break;
        */
        case 10:
            $bg_color = "orange";
            $txt_color = "black";
            return "予 備 １";
            break;
        /*
        case 10:
            $bg_color = "orange";
            $txt_color = "black";
            return "段取待ち";
            break;
        */
        case 11:
            $bg_color = "maroon";
            $txt_color = "white";
            return "予 備 ２";
            break;
        /*
        case 11:
            $bg_color = "maroon";
            $txt_color = "white";
            return "修理待ち";
            break;
        */
        default:
            $bg_color = "white";    // 未登録はバックと同じ白に変更
            $txt_color = "red";
            return "未 登 録";
        }
    }
}


// 現在は使用していない 上記の equip_machine_state()を改造
// 設備管理の機械の状態を文字列で返す。ロータリースイッチ版
// ２番の無人運転を８番へ変更 Netmoni に合わせるため ０〜３(電源OFF・自動運転・アラーム・停止)
function equip_state_r($no, &$bg_color, &$txt_color)
{
    switch ($no) {
    case 0:
        $bg_color = "black";
        $txt_color = "white";
        return "電源OFF";
        break;
    case 1:
        $bg_color = "green";
        $txt_color = "white";
        return "自動運転";
        break;
    case 2:
        $bg_color = "red";
        $txt_color = "white";
        return "アラーム";
        break;
    /*
    case 2:
        $bg_color = "red";
        $txt_color = "white";
        return "材料・刃具待ち";
        break;
    */
    case 3:
        $bg_color = "yellow";
        $txt_color = "black";
        return "停止中";
        break;
    /*
    case 3:
        $bg_color = "yellow";
        $txt_color = "black";
        return "チョコ停";
        break;
    */
    case 4:
        $bg_color = "purple";
        $txt_color = "white";
        return "暖機中";
        break;
    /*
    case 4:
        $bg_color = "purple";
        $txt_color = "white";
        return "立上準備";
        break;
    */
    case 5:
        $bg_color = "aqua";
        $txt_color = "black";
        return "段取中";
        break;
    case 6:
        $bg_color = "gray";
        $txt_color = "white";
        return "故障修理";
        break;
    case 7:
        $bg_color = "silver";
        $txt_color = "black";
        return "刃具交換";
        break;
    case 8:
        $bg_color = "blue";
        $txt_color = "white";
        return "無人運転";
        break;
    case 9:
        $bg_color = "magenta";
        $txt_color = "black";
        return "中 断";
        break;
    /*
    case 9:
        $bg_color = "magenta";
        $txt_color = "black";
        return "その他停止";
        break;
    */
    case 10:
        $bg_color = "orange";
        $txt_color = "black";
        return "予備１";
        break;
    /*
    case 10:
        $bg_color = "orange";
        $txt_color = "black";
        return "段取待ち";
        break;
    */
    case 11:
        $bg_color = "maroon";
        $txt_color = "white";
        return "予備２";
        break;
    /*
    case 11:
        $bg_color = "maroon";
        $txt_color = "white";
        return "修理待ち";
        break;
    */
    default:
        $bg_color = "";
        $txt_color = "red";
        return "未登録";
    }
}


// 設備管理のヘッダーファイルのレコード数を返す。なければ0
function equip_header_cnt()
{
    $row = 0;       // 初期レコード番号
    $fp = fopen (EQUIP_INDEX,"r");
    $data = array();
    while ($data[$row] = fgetcsv ($fp, 100, ",")) {
//      if((strlen($data[$row][0])!=4)){
//          $row--; // データ不整合ならレコードをマイナス
//      }
        $row++;
    }
    fclose ($fp);
    return $row;
}


// 与えられた引数(レコード番号)の機械�發鯤屬后�なければFALSE
function equip_kikaino($no){
    $row = 0;       // 初期レコード番号
    $fp = fopen (EQUIP_INDEX,"r");
    $data = array();
    while ($data[$row] = fgetcsv ($fp, 100, ",")) {
        if($row==$no){
            fclose ($fp);
            return $data[$row][0];
        }
        $row++;
    }
    fclose ($fp);
    return FALSE;
}


// 与えられた引数の機械�發�データ出力中ならTRUE
function equip_working_chk($no)
{
    ///// 機械マスターより稼動方式取得
    $query = "select csv_flg from equip_machine_master2 where mac_no={$no}";
    if (getUniResult($query, $state_type) <= 0) {
        $_SESSION['s_sysmsg'] .= "機械マスターから稼動方式の取得に失敗";    // debug 用
        return FALSE;
    }
    $state_type = 2;    // header file に統一するため 追加 2004/06/24
    if ($state_type == 1) {                             // Netmoni タイプ
        $row1 = 0;
        $fp1 = fopen(EQUIP_INDEX, 'r');
        $data1 = array();
        while ($data1[$row1] = fgetcsv($fp1, 100, ',')) {
            if ($data1[$row1][0]==$no) {                // 稼動チェック Netmoni Type
                fclose ($fp1);
                return TRUE;
            }
            $row1++;
        }
        fclose ($fp1);
        return FALSE;
    } else {                                            // その他(ロータリースイッチ等)
        ///// equip_work_log2_header テーブルから取得
        $query = "select mac_no from equip_work_log2_header where mac_no={$no} and work_flg is TRUE";
        if (getUniResult($query, $tmp) <= 0) {          // 稼動チェック
            return FALSE;
        } else {
            return TRUE;
        }
    }
}


// 引数の機械�發妊悒奪澄璽侫．ぅ襪�らログファイル名生成
// ヘッダーファイルに機械�發�なければNULL出力
function equip_file_name_create($no)
{
    $row1 = 0;
    $fp1 = fopen (EQUIP_INDEX,"r");
    $data1 = array();
    while ($data1[$row1] = fgetcsv ($fp1, 100, ",")) {
        if ($data1[$row1][0]==$no) {
            fclose ($fp1);
            return $data1[$row1][0] . $data1[$row1][1] . $data1[$row1][2] . $data1[$row1][3] . $data1[$row1][4];
        }
        $row1++;
    }
    fclose ($fp1);
    return NULL;
}


// 引数の機械�發妊悒奪澄璽侫．ぅ襪�ら指定フィールドを一つ返す
// ヘッダーファイルに機械�發�なければNULL出力(エラー時)
function equip_header_field($no, $field)
{
   ///// 機械マスターより稼動方式取得
    $query = "select csv_flg from equip_machine_master2 where mac_no={$no}";
    if (getUniResult($query, $state_type) <= 0) {
        $_SESSION['s_sysmsg'] .= "機械マスターから稼動方式の取得に失敗";    // debug 用
        return FALSE;
    }
    $state_type = 2;    // header file に統一するため 追加 2004/06/24
    if ($state_type == 1) {                             // 0=非監視 1=Netmoni 2=FWS1 3=FWS2 タイプ
        $row1 = 0;
        $fp1 = fopen (EQUIP_INDEX,"r");
        $data1 = array();
        while ($data1[$row1] = fgetcsv ($fp1, 100, ",")) {
            if ($data1[$row1][0] == $no) {
                fclose ($fp1);
                if ( ($field >= 0) && ($field <= 4) ) {
                    return $data1[$row1][$field];
                } else {
                    return NULL;
                }
            }
            $row1++;
        }
        fclose ($fp1);
        return NULL;
    } else {                                            // その他(ロータリースイッチ等)
        ///// equip_work_log2_header テーブルから取得
        $query = "select mac_no, siji_no, parts_no, koutei, plan_cnt from equip_work_log2_header where mac_no='$no' and work_flg is TRUE";
        if (getResult2($query, $data) <= 0) {          // 稼動チェック
            return FALSE;
        } else {
            return $data[0][$field];
        }
    }
}

// --------------------------------------------------
// 設備管理用の各種 権限を判断
// --------------------------------------------------
function equipAuthUser($function)
{
    // @session_start();
    $LoginUser = $_SESSION['User_ID'];
    $query = "select * from equip_account where function='$function' and staff='$LoginUser'";
    if (getUniResult($query, $res) > 0) {
        return true;
    } else {
        return false;
    }
}

// ------------------------------------------------------
// リクエスト又はセッションから工場区分と工場名を取得する
// ------------------------------------------------------
function getFactory(&$factory='')
{
    if (isset($_REQUEST['factory'])) {
        // $factory = $_REQUEST['factory'];
        $factory = @$_SESSION['factory'];
    } else {
        ///// リクエストが無ければセッションから工場区分を取得する。(通常はこのパターン)
        $factory = @$_SESSION['factory'];
    }
    switch ($factory) {
    case 1:
        $fact_name = '１工場';
        break;
    case 2:
        $fact_name = '２工場';
        break;
    case 4:
        $fact_name = '４工場';
        break;
    case 5:
        $fact_name = '５工場';
        break;
    case 6:
        $fact_name = '６工場';
        break;
    case 7:
        $fact_name = '７工場(真鍮)';
        break;
    case 8:
        $fact_name = '７工場(SUS)';
        break;
    default:
        $fact_name = '全工場';
        break;
    }
    return $fact_name;
}

