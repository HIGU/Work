<?php
//////////////////////////////////////////////////////////////////////////////
// PHP インフォメーション システム情報                                      //
// Copyright(C) 2001-2004 2007 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp  //
// History                                                                  //
// 2001/10/01 Created   phpinfo.php                                         //
// 2002/12/03 access_log と権限の追加（サイトメニューに入れたため）         //
// 2004/05/27 phpinfo(options) オプションパラメータ追加と補足説明を追加     //
// 2004/07/20 changed  phpinfoMain.php フレーム対応及び MenuHeader 使用     //
// 2004/07/22 NN7.1対応のためframeのHeader定義にscrolling='no'を追加        //
// 2004/08/10 out_action() → out_frame()に変更                             //
// 2005/09/10 out_site_java()をphpinfoHeaderへ移動 IEのJS構文エラー対策     //
// 2007/04/21 斎藤千尋さん用に認証チェックを追加                            //
//////////////////////////////////////////////////////////////////////////////
ini_set('error_reporting', E_ALL);              // E_ALL='2047' debug 用
ini_set('display_errors', '1');                 // Error 表示 ON debug 用 リリース後コメント
ob_start('ob_gzhandler');                       // 出力バッファをgzip圧縮
session_start();                                // ini_set()の次に指定すること Script 最上行
require_once ('../../function.php');            // TNK 全共通 function
require_once ('../../MenuHeader.php');          // TNK 全共通 menu class
access_log();                                   // Script Name は自動取得

///// TNK 共用メニュークラスのインスタンスを作成
if ($_SESSION['User_ID'] == '300161') {     // 斎藤千尋さんの場合はテスト環境があるので一般ユーザーで
    $menu = new MenuHeader(0);                  // 認証チェック3=admin以上 戻り先=セッションより タイトル未設定
} else {
    $menu = new MenuHeader(3);                  // 認証チェック3=admin以上 戻り先=セッションより タイトル未設定
}

////////////// サイト設定
$menu->set_site(99, 51);                // site_index=99(システム管理メニュー) site_id=51(phpinfo)
//////////// タイトル名(ソースのタイトル名とフォームのタイトル名)
$menu->set_title('PHP Information Main');
//////////// フレームの呼出先のアクション(frame)名とアドレス設定
$menu->set_frame('Header', SYS . 'phpinfo/phpinfoHeader.php');
$menu->set_frame('List'  , SYS . 'phpinfo/phpinfoList.php');
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
<frameset rows='55,*' name='phpinfoMain'>
    <frame src= '<?= $menu->out_frame('Header') ?>' name='Header' scrolling='no'>
    <frame src= '<?= $menu->out_frame('List') ?>'   name='List'>
</frameset>
</html>
<?php ob_end_flush(); // 出力バッファをgzip圧縮 END ?>
