<?php
//////////////////////////////////////////////////////////////////////////////
// 集荷納期別納入予定の照会 明細をウィンドウ表示   フレーム定義             //
// Copyright (C) 2017-2017 Norihisa.Ohya norihisa_ooya@nitto-kohki.co.jp    //
// Changed history                                                          //
// 2017/07/27 Created  order_collect_Main.php(order_details_Main.phpを改造) //
//////////////////////////////////////////////////////////////////////////////
ini_set('error_reporting', E_ALL);          // E_ALL='2047' debug 用
// ini_set('display_errors', '1');             // Error 表示 ON debug 用 リリース後コメント
// ini_set('implicit_flush', 'off');           // echo print で flush させない(遅くなるため) CLI CGI版
// ini_set('max_execution_time', 1200);        // 最大実行時間=20分 CLI CGI版
ob_start('ob_gzhandler');                   // 出力バッファをgzip圧縮
session_start();                            // ini_set()の次に指定すること Script 最上行
require_once ('../../../function.php');     // define.php と pgsql.php を require_once している
require_once ('../../../MenuHeader.php');   // TNK 全共通 menu class
access_log();                               // Script Name は自動取得

///// TNK 共用メニュークラスのインスタンスを作成
$menu = new MenuHeader();                   // 認証チェックも行っている

////////////// サイト設定
// $menu->set_site(30, 999);                   // site_index=30(生産メニュー) site_id=999(未定)
//////////// フレームの呼出先のアクション(frame)名とアドレス設定
$menu->set_frame('Header', INDUST . 'order/order_details/order_collect_Main_Header.php');
$menu->set_frame('List'  , INDUST . 'order/order_details/order_collect_Main_Body.php');
// フレーム版は $menu->set_action()ではなく$menu->set_frame()を使用する

//////// 指定日のパラメータ取得 & 設定
if (isset($_REQUEST['date'])) {
    if ($_REQUEST['date'] == 'OLD') {
        $date = $_REQUEST['date'];
    } else {
        $date = $_REQUEST['date'];              // 明細を表示する指定日付
        $date = ('20' . substr($date, 0, 2) . substr($date, 3, 2) . substr($date, 6, 2));
            // YYYYMMDDの形式に変換
    }
} else {
    $date = date('Ymd');                    // 初期値(当日)例外発生の場合に対応
}

//////////// タイトル名(ソースのタイトル名とフォームのタイトル名)
if ($date == 'OLD') {
    $menu->set_title('集荷納期の明細 照会');    // タイトルを入れないとIEの一部のバージョンで表示できない不具合あり
} else {
    $menu->set_title("{$_REQUEST['date']} 集荷納期の明細 照会");
}

///// GET/POSTのチェック&設定
if (isset($_REQUEST['div'])) {
    $parm = '?div=' . $_REQUEST['div'];
} else {
    if (isset($_SESSION['div'])) {
        $parm = "?div={$_SESSION['div']}";  // Default(セッションから)
    } else {
        $parm = '?div=C';                   // 初期値はカプラ
    }
}
if (isset($_REQUEST['date'])) {
    $parm .= '&date=' . $_REQUEST['date'];  // 指定日付をセット
}

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
    <iframe hspace='0' vspace='0' frameborder='0' scrolling='yes' src='<?php echo $menu->out_frame('Header') . $parm ?>' name='header' align='center' width='100%' height='40' title='項目'>
        項目を表示しています。\n";
    </iframe>
    <iframe hspace='0' vspace='0' frameborder='0' scrolling='yes' src='<?php echo $menu->out_frame('List') . $parm ?>' name='list' align='center' width='100%' height='94%' title='一覧'>
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
