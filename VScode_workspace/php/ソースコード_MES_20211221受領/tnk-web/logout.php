<?php
//////////////////////////////////////////////////////////////////////////////
// 栃木日東工器 ログアウト処理 認証を削除                                   //
// Copyright (C) 2001-2017 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp      //
// Changed history                                                          //
// 2001/07/07 Created   logout.php                                          //
// 2002/08/07 セッション管理を追加 & register_globals = Off 対応            //
// 2002/08/27 フレーム 対応 (JavaScript & form target='_top')               //
// 2003/03/08 $_SESSION['User_ID']=NULL → unset($_SESSION['User_ID'])      //
// 2004/02/13 index1.php→index.phpへ変更(index1はauthenticateに変更のため) //
// 2005/07/17 フレームチェックを追加 WEB_HOST.index.php → H_WEB_HOSTへ変更 //
// 2005/09/02 終了時の処理にJavaScriptでクライアントの画面位置を保存する    //
// 2005/09/07 終了時の処理にJavaScriptでクライアントの画面サイズを保存する  //
// 2005/09/11 onUnload=''でサブウィンドウが親ウィンドウの場合、先に終了して //
//            いるとエラーになるため try{}catch(){}を追加 e=[object Error]  //
//            認証終了処理をArrayCookie()版に対応(preg_match()を使用)       //
// 2006/07/07 ショートカットタグとJSP/ASPタグを標準タグへ変更               //
// 2017/06/12 先に親ウィンドウを閉じているとエラーになるのでonUnloadを      //
//            一時的にコメント化                                       大谷 //
//////////////////////////////////////////////////////////////////////////////
ini_set('error_reporting', E_ALL);          // E_ALL='2047' debug 用
// ini_set('display_errors', '1');             // Error 表示 ON debug 用 リリース後コメント
session_start();                            // ini_set()の次に指定すること Script 最上行
require_once ('function.php');              // 共通ファンクション(access_logのためだけにある)
access_log();                               // Script Name は自動取得
$_SESSION['logout'] = date('H:i:s');
$_SESSION['site_index'] = 999;

// セッションの終了チェック
///// 以下のforループ'WINMAX'値はwindow_ctl.jsと同じ条件である事
define('WINMAX', 15);
$session_end = true;    $count = 0;
// 自分を除く他のウィンドウがあれば終了しない
for ($i=1; $i<=WINMAX; $i++) {
    $key = '/win' . $i . '=1/i';
    $cookie = 'win' . $i;
    if (preg_match($key, @$_COOKIE[$cookie])) $count++;
}
if ($count > 1) $session_end = false;
/*
if ($session_end) {
    unset($_SESSION['User_ID']);
    unset($_SESSION['Password']);
    unset($_SESSION['Auth']);
}
*/
// header('Location: http:' . WEB_HOST . 'index.php');
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=EUC-JP">
<meta http-equiv="Content-Style-Type" content="text/css">
<meta http-equiv="Content-Script-Type" content="text/javascript">
<title></title>
<script type='text/javascript' src='base_class.js?time=<?php echo date('YmdHis') ?>'></script>
</head>
<!--
<body onUnload='try{window.opener.location.href="<?php echo H_WEB_HOST ?>"}catch(e){baseJS.Debug(e,"logout.php->onUnload->window.opener.location.href",63)}'>
-->
<body>
</body>
<script type='text/javascript' src='logout.js?time=<?php echo date('YmdHis') ?>'></script>
</html>
