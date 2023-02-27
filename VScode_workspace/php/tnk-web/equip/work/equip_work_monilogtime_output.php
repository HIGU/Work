<?php
//////////////////////////////////////////////////////////////////////////////
// 会議(打合せ)のスケジュール表：表示ユーザーIDの承認待ち総合届件数を表示   //
// Copyright (C) 2021-2021 Ryota.Waki ryota_waki@nitto-kohki.co.jp          //
// Changed history                                                          //
// 2021/11/17 Created   meeting_schedule_sougou_admit_output.php            //
//////////////////////////////////////////////////////////////////////////////
ini_set('error_reporting', E_ALL);          // E_ALL='2047' debug 用
// ini_set('display_errors', '1');             // Error 表示 ON debug 用 リリース後コメント
ini_set('max_execution_time', 60);          // 最大実行時間=60秒 WEB CGI版
//ob_start('ob_gzhandler');                   // 出力バッファをgzip圧縮
//session_start();                            // ini_set()の次に指定すること Script 最上行

require_once ('../../function.php');            // define.php と pgsql.php を require_once している
require_once ('../../ControllerHTTP_Class.php');// TNK 全共通 MVC Controller Class

access_log();                               // Script Name は自動取得

//////////// セッション オブジェクトの取得
$session = new Session();

if (isset($_REQUEST['moni'])) {
    $moni = $_REQUEST['moni'];
} else {
    $moni = "next";
}

$mac_no  = 9999;
$plan_no = "X9999999";
$koutei  = 9;
$date    = "99999999";

if( isset($_SESSION['mac_no']) ) {
    $mac_no  = $_SESSION['mac_no'];
}
if( isset($_SESSION['work_plan_no']) ) {
    $plan_no = $_SESSION['work_plan_no'];
}
if( isset($_SESSION['work_koutei']) ) {
    $koutei  = $_SESSION['work_koutei'];
}
if( isset($_SESSION['select_date']) ) {
    $date    = $_SESSION['select_date'];
}
/** TEST **
$mac_no  = 6000;
$plan_no = "C3565423";
$koutei  = 1;
$date    = "20220303";
/**/
$last_time = "--:--:--";

if( $moni == "first" ) {
    // 最終稼働ログの計画を取得
    $query = "
                SELECT  plan_no
                FROM    equip_work_log2_moni
                WHERE   mac_no=$mac_no
                ORDER BY date_time DESC
                OFFSET 0 LIMIT 1
            ";
    $res = array();
    if( getResult($query, $res) > 0 ) {
        if( $plan_no != $res[0][0] ) $last_time = "99:99:99";
    }
}

// 最終稼働ログを取得
$q_e_where = "mac_no=$mac_no AND plan_no='$plan_no' AND koutei=$koutei AND to_char(date_time, 'YYYYMMDD')=$date";
$query = "
            SELECT  to_char(date_time, 'HH24:MI:SS')
            FROM    equip_work_log2_moni
            WHERE   $q_e_where
            ORDER BY date_time DESC
            OFFSET 0 LIMIT 1
        ";
$res = array();
if( getResult($query, $res) > 0 ) {
    $last_time = $res[0][0];
}

echo $last_time;
?>
