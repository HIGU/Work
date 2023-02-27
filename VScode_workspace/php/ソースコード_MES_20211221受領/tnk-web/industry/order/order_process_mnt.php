<?php
//////////////////////////////////////////////////////////////////////////////
// 発注工程メンテナンス(発注手順の保守)   フレーム定義                      //
// Copyright (C) 2004-2004 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp      //
// Changed history                                                          //
// 2004/11/27 Created  order_process_mnt.php                                //
//////////////////////////////////////////////////////////////////////////////
ini_set('error_reporting', E_ALL);          // E_ALL='2047' debug 用
// ini_set('display_errors', '1');             // Error 表示 ON debug 用 リリース後コメント
// ini_set('implicit_flush', 'off');           // echo print で flush させない(遅くなるため) CLI CGI版
// ini_set('max_execution_time', 1200);        // 最大実行時間=20分 CLI CGI版
ob_start('ob_gzhandler');                   // 出力バッファをgzip圧縮
session_start();                            // ini_set()の次に指定すること Script 最上行
require_once ('../../function.php');        // define.php と pgsql.php を require_once している
require_once ('../../MenuHeader.php');      // TNK 全共通 menu class
access_log();                               // Script Name は自動取得

///// TNK 共用メニュークラスのインスタンスを作成
$menu = new MenuHeader();                   // 認証チェックも行っている

////////////// サイト設定
// $menu->set_site(30, 999);                   // site_index=30(生産メニュー) site_id=999(未定)
//////////// タイトル名(ソースのタイトル名とフォームのタイトル名)
$menu->set_title('発注工程のメンテナンス');     // タイトルを入れないとIEの一部のバージョンで表示できない不具合あり
//////////// フレームの呼出先のアクション(frame)名とアドレス設定
$menu->set_frame('Header', INDUST . 'order/order_process_mnt_Header.php');
$menu->set_frame('List'  , INDUST . 'order/order_process_mnt_List.php');
// フレーム版は $menu->set_action()ではなく$menu->set_frame()を使用する

/////// パラメーターの取得
if (isset($_REQUEST['sei_no'])) {
    $parm = "?sei_no={$_REQUEST['sei_no']}";
    if ($_REQUEST['sei_no'] != '') {
        $_SESSION['order_sei_no'] = $_REQUEST['sei_no'];
    } else {
        $_SESSION['order_sei_no'] = '';
    }
} else {
    $parm = '';
    $_SESSION['order_sei_no'] = '';
}

/////////// HTML Header を出力してキャッシュを制御
$menu->out_html_header();
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Frameset//EN"
    "http://www.w3.org/TR/html4/frameset.dtd">
<html>
<head>
<title><?= $menu->out_title() ?></title>
</head>
<frameset rows='160,*'>
    <frame src= '<?= $menu->out_frame('Header') . $parm ?>' name='Header' scrolling='no'>
    <frame src= '<?= $menu->out_frame('List') . $parm ?>'   name='List'>
</frameset>
</html>
<?php ob_end_flush(); // 出力バッファをgzip圧縮 END ?>
