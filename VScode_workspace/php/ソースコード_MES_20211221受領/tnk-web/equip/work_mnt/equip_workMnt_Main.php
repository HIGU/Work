<?php
//////////////////////////////////////////////////////////////////////////////
// 機械稼動管理システムの 加工指示(指示メンテナンス)  フレーム定義          //
// Copyright (C) 2004 2007 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp      //
// Changed history                                                          //
// 2004/07/27 Created  equip_workMnt_Main.php                               //
// 2004/08/10 out_action() → out_frame()に変更                             //
// 2007/03/27 set_site()メソッドをINDEX_EQUIPへ変更 $_SERVER['QUERY_STRING']//
// 2007/09/18 E_ALL | E_STRICT へ変更                                       //
//////////////////////////////////////////////////////////////////////////////
// ini_set('error_reporting', E_ALL | E_STRICT);
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
$menu->set_site(INDEX_EQUIP, 23);           // site_index=40(設備メニュー) site_id=23(指示メンテナンス)
//////////// タイトル名(ソースのタイトル名とフォームのタイトル名)
$menu->set_title('機械稼動管理 指示メンテナンス');
//////////// フレームの呼出先のアクション(frame)名とアドレス設定
$menu->set_frame('Header', EQUIP2 . 'work_mnt/equip_workMnt_Header.php');
$menu->set_frame('List'  , EQUIP2 . 'work_mnt/equip_workMnt_List.php');
// フレーム版は $menu->set_action()ではなく$menu->set_frame()を使用する


/////////// HTML Header を出力してキャッシュを制御
$menu->out_html_header();
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Frameset//EN"
    "http://www.w3.org/TR/html4/frameset.dtd">
<html>
<head>
<title><?= $menu->out_title() ?></title>
</head>
<frameset rows='146,*'>
    <frame src= '<?= $menu->out_frame('Header'), "?{$_SERVER['QUERY_STRING']}" ?>' name='Header' scrolling='no'>
    <frame src= '<?= $menu->out_frame('List'), "?{$_SERVER['QUERY_STRING']}" ?>'   name='List'>
</frameset>
</html>
<?php ob_end_flush(); // 出力バッファをgzip圧縮 END ?>
