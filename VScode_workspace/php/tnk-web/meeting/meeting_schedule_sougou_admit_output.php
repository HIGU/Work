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
require_once ('../function.php');     // define.php と pgsql.php を require_once している
require_once ('../MenuHeader.php');   // TNK 全共通 menu class
require_once ('../ControllerHTTP_Class.php');       // TNK 全共通 MVC Controller Class
access_log();                               // Script Name は自動取得

//////////// セッション オブジェクトの取得
$session = new Session();

// 承認待ちを確認するユーザー
$login_uid = '000000';
if(isset($_SESSION['User_ID'])) $login_uid = $_SESSION['User_ID'];

if( $login_uid == '300667' ) $debug = true; else $debug = false; 
if($debug){
//$login_uid = '300144';// 係長
//$login_uid = '017507';// 課長
//$login_uid = '016713';// 部長
//$login_uid = '300055';// 総務課長
//$login_uid = '017850';// 管理部長
//$login_uid = '011061';// 工場長
}

// 承認待ち件数取得
$query = "SELECT count(*) FROM sougou_deteils where admit_status='$login_uid'";
$res2 = array();
$cnt = getResult2($query, $res2);
if( $cnt > 0 ) $cnt = $res2[0][0];

echo $cnt;  // ０以外なら承認待ち情報ウィンドウを表示
?>
