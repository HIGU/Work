<?php
//////////////////////////////////////////////////////////////////////////////
// 会議帯 不在者をウィンドウ表示   フレーム定義                             //
// Copyright (C) 2019-2019 Norihisa.Ohya norihisa_ooya@nitto-kohki.co.jp    //
// Changed history                                                          //
// 2019/03/15 Created  meeting_schedule_absence_Main                        //
//////////////////////////////////////////////////////////////////////////////
ini_set('error_reporting', E_ALL);          // E_ALL='2047' debug 用
// ini_set('display_errors', '1');             // Error 表示 ON debug 用 リリース後コメント
// ini_set('implicit_flush', 'off');           // echo print で flush させない(遅くなるため) CLI CGI版
// ini_set('max_execution_time', 1200);        // 最大実行時間=20分 CLI CGI版
ob_start('ob_gzhandler');                   // 出力バッファをgzip圧縮
session_start();                            // ini_set()の次に指定すること Script 最上行
require_once ('../function.php');     // define.php と pgsql.php を require_once している
require_once ('../MenuHeader.php');   // TNK 全共通 menu class
access_log();                               // Script Name は自動取得

///// TNK 共用メニュークラスのインスタンスを作成
$menu = new MenuHeader();                   // 認証チェックも行っている

////////////// サイト設定
// $menu->set_site(30, 999);                   // site_index=30(生産メニュー) site_id=999(未定)
//////////// フレームの呼出先のアクション(frame)名とアドレス設定
$menu->set_frame('Header', 'meeting_schedule_absence_Header.php');
$menu->set_frame('List'  , 'meeting_schedule_absence_Body.php');
// フレーム版は $menu->set_action()ではなく$menu->set_frame()を使用する

$date = date('Ymd');                    // 初期値(当日)例外発生の場合に対応

$menu->set_title('不在者照会');    // タイトルを入れないとIEの一部のバージョンで表示できない不具合あり

/////////// HTML Header を出力してキャッシュを制御
$menu->out_html_header();
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=EUC-JP">
<meta http-equiv="Content-Style-Type" content="text/css">
<title><?php echo $menu->out_title() ?></title>
<?php echo $menu->out_css() ?>
</head>
<body>
<center>
    <iframe hspace='0' vspace='0' frameborder='0' scrolling='yes' src='<?php echo $menu->out_frame('Header') ?>' name='header' align='center' width='100%' height='15%' title='項目'>
        項目を表示しています。\n";
    </iframe>
    <iframe hspace='0' vspace='0' frameborder='0' scrolling='yes' src='<?php echo $menu->out_frame('List') ?>' name='list' align='center' width='100%' height='85%' title='一覧'>
        一覧を表示しています。
    </iframe>
    <!--
    <iframe hspace='0' vspace='0' frameborder='0' scrolling='yes' src='' name='footer' align='center' width='100%' height='32' title='フッター'>
        フッターを表示しています。
    </iframe>
    -->
</center>
</body>
</html>
<?php ob_end_flush(); // 出力バッファをgzip圧縮 END ?>
