<?php
//////////////////////////////////////////////////////////////////////////////
// 組立機械稼動管理システムの 運転 実績表の 表示  フレーム定義              //
// Copyright (C) 2021-2021 norihisa_ooya@nitto-kohki.co.jp                  //
// Changed history                                                          //
// 2021/03/26 Created  equip_chart_moni.php                                 //
//////////////////////////////////////////////////////////////////////////////
// ini_set('error_reporting', E_ALL | E_STRICT);
// ini_set('display_errors', '1');             // Error 表示 ON debug 用 リリース後コメント
// ini_set('implicit_flush', 'off');        // echo print で flush させない(遅くなるため) CLI CGI版
// ini_set('max_execution_time', 1200);     // 最大実行時間=20分 CLI CGI版
ob_start('ob_gzhandler');                   // 出力バッファをgzip圧縮
session_start();                            // ini_set()の次に指定すること Script 最上行
require_once ('../../function.php');        // define.php と pgsql.php を require_once している
require_once ('../../tnk_func.php');        // TNK に依存する部分の関数を require_once している
require_once ('../../MenuHeader.php');      // TNK 全共通 menu class
access_log();                               // Script Name は自動取得

///// TNK 共用メニュークラスのインスタンスを作成
$menu = new MenuHeader();                   // 認証チェックも行っている

////////////// サイト設定
$menu->set_site(40, 6);                     // site_index=40(設備メニュー) site_id=6(実績照会)
//////////// タイトル名(ソースのタイトル名とフォームのタイトル名)
$menu->set_title('指示番号毎の実績状況表');
//////////// フレームの呼出先のアクション(frame)名とアドレス設定
$menu->set_frame('Header', EQUIP2 . 'hist/equip_chart_moniHeader.php');
$menu->set_frame('List'  , EQUIP2 . 'hist/equip_chart_moniList.php');
// フレーム版は $menu->set_action()ではなく$menu->set_frame()を使用する

///// GET/POSTのチェック&設定
$key_no = '?mac_no=' . @$_REQUEST['mac_no'] . '&plan_no=' . @$_REQUEST['plan_no'] . '&koutei=' . @$_REQUEST['koutei'];
// 上記は現在equip_branch_msg.phpが間に入っているため意味が無いので使用しない。$_SESSIONを使用している。
$key_no = '';

/////////// HTML Header を出力してキャッシュを制御
$menu->out_html_header();
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Frameset//EN"
    "http://www.w3.org/TR/html4/frameset.dtd">
<html>
<head>
<title><?= $menu->out_title() ?></title>
</head>
<frameset rows='120,*'>
    <frame src= '<?= $menu->out_frame('Header'), $key_no ?>' name='Header' scrolling='no'>
    <frame src= '<?= $menu->out_frame('List') ?>'   name='List'>
</frameset>
</html>
<?php ob_end_flush(); // 出力バッファをgzip圧縮 END ?>
