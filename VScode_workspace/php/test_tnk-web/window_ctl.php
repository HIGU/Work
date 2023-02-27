<?php
//////////////////////////////////////////////////////////////////////////////
// 栃木日東工器 Window Control                                              //
// Copyright (C) 2002-2005 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp      //
// Changed history                                                          //
// 2002/08/30 Created   window_ctl.php                                      //
// 2002/08/30 セッション管理を追加 & register_globals = Off 対応            //
// 2003/08/25 Window のオープンに IE 専用のfullscreen=yesを試したが NG      //
// 2004/02/13 index1.php→index.phpへ変更(index1はauthenticateに変更のため) //
// 2005/01/26 location を index.php → authenticate.php?background=onへ変更 //
// 2005/08/31 Window Controlを履歴に残さないためにlocation='http://??? から //
//            location.replace('http://???') へ変更                         //
// 2005/08/31 base_class を使用しクライアントのウィンドウ位置を復元する     //
//////////////////////////////////////////////////////////////////////////////
ini_set('error_reporting', E_ALL);          // E_ALL='2047' debug 用
ini_set('display_errors', '1');             // Error 表示 ON debug 用 リリース後コメント
session_start();                            // ini_set()の次に指定すること Script 最上行
require_once ('function.php');              // 全共通 function call
access_log();                               // Script Name は自動取得
///// ブラウザーのキャッシュを無効
$uniq = uniqid('CTL');
?>
<!DOCTYPE html>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<meta http-equiv="Content-Style-Type" content="text/css">
<meta http-equiv="Content-Script-Type" content="text/javascript">
<title>Window Control</title>
<script type='text/javascript' src='base_class.js?id=<?=$uniq?>'></script>
<script type='text/javascript' src='window_ctl.js?id=<?=$uniq?>'></script>
</head>
<body>
<script type='text/javascript'>
<!--
///// インスタンスの生成
var winCtl = new window_ctl();
// 自分のロケーションを変更
winCtl.chgLocation("<?=H_WEB_HOST?>/authenticate.php?background=on");
// -->
</script>
</body>
</html>
