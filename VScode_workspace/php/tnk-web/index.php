<?php
//////////////////////////////////////////////////////////////////////////////
// Document Root Index File アクセスのあったホスト等をセッションに追加      //
// Copyright (C) 2001-2005 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp      //
// Changed history                                                          //
// 2001/07/07 Created  index.php                                            //
// 2002/01/18 セッション管理を追加                                          //
// 2004/02/02 index1.php → authenticate.php へ名前を変更                   //
// 2004/03/10 クライアントのクッキー無効の対策ロジックを追加                //
// 2005/09/13 session_register('r_addr', 'r_hostname', 'web_file')を廃止    //
//            E_ALL → E_STRICT                                             //
// 2005/09/21 gethostbyaddr($r_addr)→gethostbyaddr($_SERVER['REMOTE_ADDR'])//
//////////////////////////////////////////////////////////////////////////////
ini_set('error_reporting', E_ALL);          // E_STRICT=2048(php5) E_ALL=2047 debug 用
ini_set('display_errors', '1');             // Error 表示 ON debug 用 リリース後コメント
session_start();                            // ini_set()の次に指定すること Script 最上行
require_once ('function.php');              // 共通ファンクッション
access_log();                               // Script Name は自動取得
//  session_destroy();
$_SESSION['r_addr']     = $_SERVER['REMOTE_ADDR'];  // 正確には $_SESSION を使用した後に session_register を使用してはいけない
$_SESSION['r_hostname'] = gethostbyaddr($_SERVER['REMOTE_ADDR']);
$_SESSION['web_file']   = $_SERVER['SCRIPT_NAME'];
if ( !isset($_SESSION['Counter']) ) {       // 初回の場合は Counter が登録されていない
    $_SESSION['Counter'] = 0;
}
$_SESSION['Counter']++;
// session_id($r_hostname);
if (isset($_GET['PHPSESSID'])) {
    header('Location: http:' . WEB_HOST . 'authenticate.php?' . SID);   // SIDの付加はクッキー無効の対策
} else {
    header('Location: http:' . WEB_HOST . 'authenticate.php');
}
?>
