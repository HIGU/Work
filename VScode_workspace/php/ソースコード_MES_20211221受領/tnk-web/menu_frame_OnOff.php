<?php
//////////////////////////////////////////////////////////////////////////////
// 栃木日東工器 フレーム(サイトメニュー)表示 ON/OFF                         //
// Copyright(C) 2003-2005 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp       //
// Changed history                                                          //
// 2003/12/15 Created   menu_frame_OnOff.php                                //
// 2004/07/21 <title>TNK Web MenuOFF→TNK Web Systemへ タグを小文字へ変更   //
// 2005/08/06 site_viewのセッション存在チェックを追加(windowを開いていない) //
// 2005/09/05 暫定<framesetにname='topFrame'を追加今後はmenu_frame.phpに統一//
//////////////////////////////////////////////////////////////////////////////
ini_set('error_reporting', E_ALL);      // E_ALL='2047' debug 用
// ini_set('display_errors', '1');         // Error 表示 ON debug 用 リリース後コメント
session_start();                        // ini_set()の次に指定すること Script 最上行
require_once ('function.php');          // TNK Web 全共通function
access_log();                           // Script Name は自動取得

///// menu_frame_OnOff.php は今後使用しなくなるため menu_frame.php へリダイレクトする
///// 2005/09/05 追加
header('Location: ' . H_WEB_HOST . TOP . 'menu_frame.php?' . $_SERVER['QUERY_STRING']);
exit;

////////////// 実行スクリプト名の取得
// if ( isset($_SERVER['HTTP_REFERER']) ) {     // ← これは使えない
//     $exec_name = $_SERVER['HTTP_REFERER'];
if ( isset($_GET['name']) ) {
    $exec_name = $_GET['name'];
} else {
    $exec_name = TOP_MENU;
}

////////////// 表示の On Off 取得
if (!isset($_SESSION['site_view'])) $_SESSION['site_view'] = 'off';
if ($_SESSION['site_view'] == 'on') {
    $_SESSION['site_view'] = 'off';
    $frame_cols = '0%,*';
} else {
    $_SESSION['site_view'] = 'on';
    $frame_cols = '10%,*';
}
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Frameset//EN"
    "http://www.w3.org/TR/html4/frameset.dtd">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=EUC-JP">
<meta http-equiv="Content-Style-Type" content="text/css">
<title>TNK Web System</title>
</head>
<frameset name='topFrame' cols='<?php echo $frame_cols ?>' border='0' onLoad='self.focus()'>
    <frame src='menu_site.php' name='menu_site' scrolling='no'>
    <frame src='<?php echo $exec_name ?>' name='application'>
</frameset>
<noframes>
<p>栃木日東工器(株)のWebサイトではフレームを使う前提になっています。</p>
<p>フレームを使用しない設定にしている場合は変更して下さい。</p>
<p>未対応のブラウザーの場合は対応ブラウザーに変更して下さい。<p>
</noframes>
</html>
