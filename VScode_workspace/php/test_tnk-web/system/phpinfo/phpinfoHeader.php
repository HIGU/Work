<?php
//////////////////////////////////////////////////////////////////////////////
// PHP インフォメーション システム情報                                      //
// Copyright(C) 2001-2004 2007 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp  //
// History                                                                  //
// 2001/10/01 Created   phpinfo.php                                         //
// 2002/12/03 access_log と権限の追加（サイトメニューに入れたため）         //
// 2004/05/27 phpinfo(options) オプションパラメータ追加と補足説明を追加     //
// 2004/07/20 changed  phpinfoHeader.php フレーム対応及び MenuHeader 使用   //
// 2004/07/21 mhForm.target→document.mhForm.targetへ変更 NN7.1で省略不可   //
// 2005/09/10 phpinfoMainのout_site_java()こちらに移動 IEのJS構文エラー対策 //
// 2007/04/21 斎藤千尋さん用に認証チェックを追加                            //
//////////////////////////////////////////////////////////////////////////////
ini_set('error_reporting', E_ALL);          // E_ALL='2047' debug 用
ini_set('display_errors', '1');             // Error 表示 ON debug 用 リリース後コメント
session_start();                            // ini_set()の次に指定すること Script 最上行
require_once ('../../function.php');        // TNK 全共通 function
require_once ('../../MenuHeader.php');      // TNK 全共通 menu class
access_log();                               // Script Name は自動取得

///// TNK 共用メニュークラスのインスタンスを作成
if ($_SESSION['User_ID'] == '300161') {     // 斎藤千尋さんの場合はテスト環境があるので一般ユーザーで
    $menu = new MenuHeader(0);                  // 認証チェック3=admin以上 戻り先=セッションより タイトル未設定
} else {
    $menu = new MenuHeader(3);                  // 認証チェック3=admin以上 戻り先=セッションより タイトル未設定
}

////////////// サイト設定
$menu->set_site(99, 51);                // site_index=99(システム管理メニュー) site_id=51(phpinfo)
//////////// タイトル名(ソースのタイトル名とフォームのタイトル名)
$menu->set_title('PHP Information');
////////////// target設定
// $menu->set_target('application');       // フレーム版の戻り先はtarget属性が必須
$menu->set_target('_parent');           // フレーム版の戻り先はtarget属性が必須

// 下のJavaScriptのfunctionで使用しているフォーム名 mhForm はMenuHeader classでdefault定義されてる

/////////// HTML Header を出力してキャッシュを制御
$menu->out_html_header();
?>
<!DOCTYPE html>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<meta http-equiv="Content-Style-Type" content="text/css">
<title><?= $menu->out_title() ?></title>
<?= $menu->out_site_java() ?>
<?= $menu->out_css() ?>
<Script Language='JavaScript'>
<!--
function setTarget() {
    document.mhForm.target = 'application';
    // document.mhForm.target = '_parent'; //相対Window(frame)名から実Window名へ変更
    // NV7.1ではdocumentを省略できない事に注意
    // 使用すると時は <body onLoad='setTarget()'>
}
-->
</Script>
</head>
<body>
    <center>
<?= $menu->out_title_border() ?>
    </center>
</body>
</html>
