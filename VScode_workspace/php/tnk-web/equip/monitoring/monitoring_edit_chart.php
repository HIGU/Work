<?php
////////////////////////////////////////////////////////////////////////////////
// 機械稼動管理システムの 指示変更及びログ編集  フレーム定義                  //
// Copyright (C) 2021-2021 Ryota.waki ryota_waki@nitto-kohki.co.jp            //
// Changed history                                                            //
// 2021/03/24 Created  equip_edit_chart.php -> monitaring_edit_chart.php      //
// 2021/03/24 Release.                                                        //
////////////////////////////////////////////////////////////////////////////////
// ini_set('error_reporting', E_ALL | E_STRICT);
// ini_set('error_reporting', E_ALL);          // E_ALL='2047' debug 用
// ini_set('display_errors', '1');             // Error 表示 ON debug 用 リリース後コメント
// ini_set('implicit_flush', 'off');           // echo print で flush させない(遅くなるため) CLI CGI版
// ini_set('max_execution_time', 120);         // 最大実行時間=120秒 SAPI CGI版
ob_start('ob_gzhandler');                   // 出力バッファをgzip圧縮
session_start();                            // ini_set()の次に指定すること Script 最上行
require_once ('../../function.php');        // define.php と pgsql.php を require_once している
require_once ('../../MenuHeader.php');      // TNK 全共通 menu class
access_log();                               // Script Name は自動取得

///// TNK 共用メニュークラスのインスタンスを作成
$menu = new MenuHeader();                   // 認証チェックも行っている

////////////// サイト設定
$menu->set_site(40, 11);                    // site_index=40(設備メニュー) site_id=11(指示変更)
//////////// タイトル名(ソースのタイトル名とフォームのタイトル名)
$menu->set_title('指示内容の編集');
//////////// フレームの呼出先のアクション(frame)名とアドレス設定
$menu->set_frame('Header', EQUIP2 . 'monitoring/monitoring_edit_chartHeader.php');
$menu->set_frame('List'  , EQUIP2 . 'monitoring/monitoring_edit_chartList.php');
// フレーム版は $menu->set_action()ではなく$menu->set_frame()を使用する

///// GET/POSTのチェック&保存
if (isset($_REQUEST['mac_no'])) {
    $_SESSION['mac_no']  = $_REQUEST['mac_no'];
    $_SESSION['plan_no'] = $_REQUEST['plan_no'];
    $_SESSION['koutei']  = $_REQUEST['koutei'];
}
///// エラー終了時のセッションに残っている変数を削除
if (isset($_SESSION['chg_time'])) {
    unset($_SESSION['chg_time']);
}
if (isset($_SESSION['cnt_chg_time'])) {
    unset($_SESSION['cnt_chg_time']);
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
<frameset rows='150,*'>
    <frame src= '<?= $menu->out_frame('Header') ?>' name='Header' scrolling='no'>
    <frame src= '<?= $menu->out_frame('List') ?>'   name='List'>
</frameset>
</html>
<?php ob_end_flush();   // 出力バッファをgzip圧縮 END ?>
