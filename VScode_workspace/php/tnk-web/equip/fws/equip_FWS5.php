<?php
//////////////////////////////////////////////////////////////////////////////
// 第４工場の追加で配線した１４台を仮で表示するため                         //
//   FwServer2 の機能を利用。 /cgi-bin/mon.cgi?argHtmlFile=stsmon.html      //
// 2004/02/26 Copyright(C) 2004 K.Kobayashi tnksys@nitto-kohki.co.jp        //
// 変更経歴                                                                 //
// 2004/02/26 新規作成  equip_4factory.php                                  //
//////////////////////////////////////////////////////////////////////////////
ini_set('error_reporting',E_ALL);       // E_ALL='2047' debug 用
ini_set('display_errors','1');          // Error 表示 ON debug 用 リリース後コメント
// ini_set('implicit_flush', 'off');       // echo print で flush させない(遅くなるため) CLI CGI版
// ini_set('max_execution_time', 1200);    // 最大実行時間=20分 CLI CGI版
// ob_start('ob_gzhandler');               // 出力バッファをgzip圧縮
session_start();                        // ini_set()の次に指定すること Script 最上行

require_once ('../../function.php');       // define.php と pgsql.php を require_once している
require_once ('../../tnk_func.php');       // TNK に依存する部分の関数を require_once している
access_log();                           // Script Name は自動取得

////////////// サイトメニュー設定
$_SESSION['site_index'] = 40;           // 最後のメニュー    = 99   システム管理用は９９番
$_SESSION['site_id']    = 93;           // 下位メニュー無し <= 0    テンプレートファイルは６０番


////////////// リターンアドレス設定
$current_script  = $_SERVER['PHP_SELF'];        // 現在実行中のスクリプト名を保存
if ( !(isset($_POST['forward']) || isset($_POST['backward'])) ) {
    $url_referer = $_SERVER['HTTP_REFERER'];    // 呼出もとのURLを保存 前のスクリプトで分岐処理を
    $_SESSION['ret_addr'] = $url_referer;       // している場合は使用しない site_menuからの呼出では使用できない
} else {
    $url_referer = $_SESSION['ret_addr'];       // 次頁・前頁の時はセッションから読込む
}
// $url_referer     = $_SESSION['pl_referer'];     // 分岐処理前に保存されている呼出元をセットする

//////////////// 認証チェック
if ( !isset($_SESSION['User_ID']) || !isset($_SESSION['Password']) || !isset($_SESSION['Auth']) ) {
// if ($_SESSION['Auth'] <= 2) {                // 権限レベルが２以下は拒否
// if (account_group_check() == FALSE) {        // 特定のグループ以外は拒否
    $_SESSION['s_sysmsg'] = '認証されていないか認証期限が切れました。ログインからお願いします。';
    // header('Location: http:' . WEB_HOST . 'menu.php');   // 固定呼出元へ戻る
    header("Location: $url_referer");                   // 直前の呼出元へ戻る
    exit();
}

/********** Logic Start **********/
//////////// タイトルの日付・時間設定
$today = date('Y/m/d H:i:s');
//////////// タイトル名(ソースのタイトル名とフォームのタイトル名)
$menu_title = 'FWServer5チェック';
//////////// 表題の設定
$caption    = 'チェック用のグラフィック表示';

//////////// JavaScript Stylesheet File を必ず読み込ませる
$uniq = uniqid('target');

?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<meta http-equiv="Content-Style-Type" content="text/css">
<title><?= $menu_title ?></title>
<script language="JavaScript">
<!--
    parent.menu_site.location = '<?= H_WEB_HOST . SITE_MENU ?>';
// -->
</script>
</head>
<body>
</body>
</html>
<script language='JavaScript'>
<!--
location.replace('http://fwserver5.tnk.co.jp/cgi-bin/mon.cgi?argHtmlFile=stsmon5.html');        // 目的のスクリプトを呼出す
// -->
</script>
