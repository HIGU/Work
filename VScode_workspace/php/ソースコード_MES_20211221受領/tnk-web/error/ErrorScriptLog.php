<?php
//////////////////////////////////////////////////////////////////////////////
// base_class.js 内で定義・宣言された JavaScript エラーログ                 //
// Copyright(C) 2005-2005 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp       //
// Changed History                                                          //
// 2005/08/30 Created   ErrorScriptLog.php                                  //
// 2005/09/03 認証が切れた(又はない)場合があるのでエラーの抑止を追加        //
//////////////////////////////////////////////////////////////////////////////
ini_set('error_reporting', E_ALL);      // E_ALL='2047' debug 用
// ini_set('display_errors', '1');         // Error 表示 ON debug 用 リリース後コメント
session_start();                        // ini_set()の次に指定すること Script 最上行
require_once ('../function.php');       // TNK 全共通 function
access_log();                           // Script Name は自動取得

// 認証が切れた(又はない)場合があるのでエラーの抑止
$User_ID = @$_SESSION['User_ID'];
$Auth    = @$_SESSION['Auth'];

$error   = mb_convert_encoding(stripslashes($_REQUEST['error']), 'EUC-JP', 'SJIS');
$file    = $_REQUEST['file'];
$line    = $_REQUEST['line'];
$browser = $_REQUEST['browser'];

if ( ($fp=fopen('ErrorScriptLog.log', 'a')) ) {
    $log  = date('Y-m-d H:i:m') . " IP_ADDRES = {$_SERVER['REMOTE_ADDR']}  User = {$User_ID}  Auth = {$Auth}\n";
    $log .= "                    Error内容 = {$error}\n";
    $log .= "                    ファイル  = {$file}\n";
    $log .= "                    ライン    = {$line}\n";
    $log .= "                    Browser   = {$browser}\n";
    fwrite($fp, $log);
    fclose($fp);
}

?>
