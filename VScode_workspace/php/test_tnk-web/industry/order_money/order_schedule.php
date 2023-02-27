<?php
//////////////////////////////////////////////////////////////////////////////
// 納入予定金額の照会  フレーム定義                                         //
// Copyright (C) 2009-2010   Norihisa.Ohya  norihisa_ooya@nitto-kohki.co.jp //
// Changed history                                                          //
// 2009/11/09 Created  /order/order_schedule.php より /order_money/へ流用   //
// 2010/05/26 タイトルが違うので修正                                        //
//////////////////////////////////////////////////////////////////////////////
// ini_set('error_reporting', E_ALL);          // E_ALL='2047' debug 用
// ini_set('display_errors', '1');             // Error 表示 ON debug 用 リリース後コメント
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
$menu->set_title('納入予定と検査仕掛の照会');
//////////// フレームの呼出先のアクション(frame)名とアドレス設定
$menu->set_frame('Header', INDUST . 'order_money/order_schedule_Header.php');
$menu->set_frame('List'  , INDUST . 'order_money/order_schedule_List.php');
// フレーム版は $menu->set_action()ではなく$menu->set_frame()を使用する

///// GET/POSTのチェック&設定
if (isset($_REQUEST['div'])) {
    $parm = '?div=' . $_REQUEST['div'];
    $_SESSION['div'] = $_REQUEST['div'];    // セッションに保存
} else {
    if (isset($_SESSION['div'])) {
        $parm = "?div={$_SESSION['div']}";  // Default(セッションから)
    } else {
        $parm = '?div=C';                   // 初期値はカプラ
    }
}
if (isset($_REQUEST['miken'])) {
    $parm .= '&miken=GO';                   // 未検収リスト
    $_SESSION['select'] = 'miken';          // セッションに保存
} elseif (isset($_REQUEST['insEnd'])) {
    $parm .= '&insEnd=GO';                  // 納入予定グラフ
    $_SESSION['select'] = 'insEnd';         // セッションに保存
} elseif (isset($_REQUEST['graph'])) {
    $parm .= '&graph=GO';                   // 納入予定グラフ
    $_SESSION['select'] = 'graph';          // セッションに保存
} elseif (isset($_REQUEST['list'])) {
    $parm .= '&list=GO';                    // 納入予定集計
    $_SESSION['select'] = 'list';           // セッションに保存
} else {
    if (isset($_SESSION['select'])) {
        $parm .= "&{$_SESSION['select']}=GO";   // Default(セッションから)
    } else {
        $parm .= '&graph=GO';               // 初期値は納入予定グラフ
    }
}

/////////// HTML Header を出力してキャッシュを制御
$menu->out_html_header();
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Frameset//EN"
    "http://www.w3.org/TR/html4/frameset.dtd">
<html>
<head>
<title><?php echo $menu->out_title() ?></title>
<?php if($_SESSION['User_ID'] != '00000A') echo $menu->out_site_java(); ?>
<link rel='shortcut icon' href='/favicon.ico?=<?php echo time() ?>'>
</head>
<frameset rows='120,*'>
    <frame src= '<?php echo $menu->out_frame('Header') . $parm ?>' name='Header' scrolling='no'>
    <frame src= '<?php echo $menu->out_frame('List') . $parm ?>'   name='List'>
</frameset>
</html>
<?php ob_end_flush(); // 出力バッファをgzip圧縮 END ?>
