<?php
//////////////////////////////////////////////////////////////////////////////
// 緊急 部品 検査 依頼 照会及びメンテナンス  フレーム定義部                 //
// Copyright (C) 2004-2005 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp      //
// Changed history                                                          //
// 2004/10/19 Created  inspection_recourse.php                              //
// 2005/02/10 out_site_java()を Headerフレームからフレーム定義へ移動        //
//////////////////////////////////////////////////////////////////////////////
ini_set('error_reporting', E_ALL);          // E_ALL='2047' debug 用
ini_set('display_errors', '1');             // Error 表示 ON debug 用 リリース後コメント
// ini_set('implicit_flush', 'off');           // echo print で flush させない(遅くなるため) CLI CGI版
// ini_set('max_execution_time', 1200);        // 最大実行時間=20分 CLI CGI版
ob_start('ob_gzhandler');                   // 出力バッファをgzip圧縮
session_start();                            // ini_set()の次に指定すること Script 最上行
require_once ('../../function.php');        // define.php と pgsql.php を require_once している
require_once ('../../MenuHeader.php');      // TNK 全共通 menu class
access_log();                               // Script Name は自動取得

if (!isset($_SESSION['Auth'])) {
    $_SESSION['Auth'] = 0;
    $_SESSION['User_ID'] = '00000A';
    $_SESSION['site_view'] = 'off';
    $_SESSION['s_sysmsg'] = '';
}

///// TNK 共用メニュークラスのインスタンスを作成
$menu = new MenuHeader();                   // 認証チェックも行っている

////////////// サイト設定
$menu->set_site(30, 50);                    // site_index=30(生産メニュー) site_id=50(納入・検査仕掛)999(サイトを開く)
//////////// タイトル名(ソースのタイトル名とフォームのタイトル名)
$menu->set_title('緊急部品検査依頼の照会');
//////////// フレームの呼出先のアクション(frame)名とアドレス設定
$menu->set_frame('Header', INDUST . 'order/inspection_recourse_Header.php');
$menu->set_frame('List'  , INDUST . 'order/inspection_recourse_List.php');
// フレーム版は $menu->set_action()ではなく$menu->set_frame()を使用する

///// GET/POSTのチェック&設定
if (isset($_REQUEST['div'])) {
    $_SESSION['div'] = $_REQUEST['div'];    // セッションに保存
} else {
    if (!isset($_SESSION['div'])) {
        $_SESSION['div'] = 'C';             // 初期値はカプラ
    }
}
// $_SESSION['select'] = 'inspc';              // セッションに保存

/////////// HTML Header を出力してキャッシュを制御
$menu->out_html_header();
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Frameset//EN"
    "http://www.w3.org/TR/html4/frameset.dtd">
<html>
<head>
<title><?= $menu->out_title() ?></title>
<?php if($_SESSION['User_ID'] != '00000A') echo $menu->out_site_java(); ?>
</head>
<frameset rows='120,*'>
    <frame src= '<?= $menu->out_frame('Header') ?>' name='Header' scrolling='no'>
    <frame src= '<?= $menu->out_frame('List') ?>'   name='List'>
</frameset>
</html>
<?php ob_end_flush(); // 出力バッファをgzip圧縮 END ?>
