<?php
//////////////////////////////////////////////////////////////////////////////
// 設備稼動管理システムの現在運転状況一覧マップ表示(レイアウト)フレーム定義 //
// Copyright (C) 2004-2004 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp      //
// Changed history                                                          //
// 2004/09/23 Created  equip_work_map.php                                   //
//            直感的に分かる様に $factory→$parm に変更                     //
//////////////////////////////////////////////////////////////////////////////
ini_set('error_reporting', E_ALL);          // E_ALL='2047' debug 用
// ini_set('display_errors', '1');             // Error 表示 ON debug 用 リリース後コメント
// ini_set('implicit_flush', 'off');           // echo print で flush させない(遅くなるため) CLI CGI版
// ini_set('max_execution_time', 1200);        // 最大実行時間=20分 CLI CGI版
ob_start('ob_gzhandler');                   // 出力バッファをgzip圧縮
session_start();                            // ini_set()の次に指定すること Script 最上行
require_once ('../../function.php');        // define.php と pgsql.php を require_once している
require_once ('../../tnk_func.php');        // TNK に依存する部分の関数を require_once している
require_once ('../../MenuHeader.php');      // TNK 全共通 menu class
access_log();                               // Script Name は自動取得

///// TNK 共用メニュークラスのインスタンスを作成
$menu = new MenuHeader();                   // 認証チェックも行っている

////////////// サイト設定
$menu->set_site(40, 12);                    // site_index=40(設備メニュー) site_id=12(マップ一覧)
//////////// タイトル名(ソースのタイトル名とフォームのタイトル名)
$menu->set_title('稼動状況 レイアウト 表示');
//////////// フレームの呼出先のアクション(frame)名とアドレス設定
$menu->set_frame('Header', EQUIP2 . 'work/equip_work_mapHeader.php');
$menu->set_frame('List'  , EQUIP2 . 'work/equip_work_mapList.php');
// フレーム版は $menu->set_action()ではなく$menu->set_frame()を使用する

///// 以下は現在、セッションより工場区分を取得するため通常使用しないが強制的に工場区分を変更したい時にGET/POSTで指定出来るように残す
///// GET/POSTのチェック&設定
if (isset($_REQUEST['factory'])) {
    $parm = '?factory=' . $_REQUEST['factory'];
} else {
    $parm = '';
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
<frameset rows='100,*'>
    <frame src= '<?= $menu->out_frame('Header') . $parm ?>' name='Header' scrolling='no'>
    <frame src= '<?= $menu->out_frame('List') . $parm ?>'   name='List'>
</frameset>
</html>
<?php ob_end_flush(); // 出力バッファをgzip圧縮 END ?>
