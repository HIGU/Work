<?php
//////////////////////////////////////////////////////////////////////////////
// 規程メニュー専用 Document Root Index File                                //
// Copyright (C) 2006-2006 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp      //
// Changed history                                                          //
// 2006/04/17 Created  regulation/index.php                                 //
//////////////////////////////////////////////////////////////////////////////
ini_set('error_reporting', E_STRICT);       // E_STRICT=2048(php5) E_ALL=2047 debug 用
// ini_set('display_errors', '1');             // Error 表示 ON debug 用 リリース後コメント
session_start();                            // ini_set()の次に指定すること Script 最上行
require_once ('../function.php');           // 共通ファンクッション
access_log();                               // Script Name は自動取得

$_SESSION['r_addr']     = $_SERVER['REMOTE_ADDR'];  // 正確には $_SESSION を使用した後に session_register を使用してはいけない
$_SESSION['r_hostname'] = gethostbyaddr($_SERVER['REMOTE_ADDR']);
$_SESSION['web_file']   = $_SERVER['SCRIPT_NAME'];

if ( !isset($_SESSION['Counter']) ) {       // 初回の場合は Counter が登録されていない
    $_SESSION['Counter'] = 0;
}
$_SESSION['Counter']++;
if (isset($_GET['PHPSESSID'])) {
    header('Location: http:' . WEB_HOST . 'regulation/authenticate.php?' . SID);   // SIDの付加はクッキー無効の対策
} else {
    header('Location: http:' . WEB_HOST . 'regulation/authenticate.php');
}
?>
