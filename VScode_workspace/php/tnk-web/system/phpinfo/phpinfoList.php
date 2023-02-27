<?php
//////////////////////////////////////////////////////////////////////////////
// PHP インフォメーション システム情報                                      //
// Copyright(C) 2001-2004 2007 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp  //
// History                                                                  //
// 2001/10/01 Created   phpinfo.php                                         //
// 2002/12/03 access_log と権限の追加（サイトメニューに入れたため）         //
// 2004/05/27 phpinfo(options) オプションパラメータ追加と補足説明を追加     //
// 2004/07/20 changed  phpinfoList.php フレーム対応及び MenuHeader 使用     //
// 2007/04/21 斎藤千尋さん用に認証チェックを追加                            //
// 2007/09/11 zend 1コンパチを外す                                          //
// 2007/09/18 E_ALL | E_STRICT へ変更                                       //
//////////////////////////////////////////////////////////////////////////////
// ini_set('error_reporting', E_ALL | E_STRICT);   // E_ALL='2047' debug 用
ini_set('display_errors', '1');                 // Error 表示 ON debug 用 リリース後コメント
// ini_set('zend.ze1_compatibility_mode', '1');    // zend 1.X コンパチ
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
$menu->set_site(99, 51);                    // site_index=99(システム管理メニュー) site_id=51(phpinfo)
//////////// タイトル名(ソースのタイトル名とフォームのタイトル名)
$menu->set_title('PHP Information');

/////////// HTML Header を出力してキャッシュを制御
$menu->out_html_header();
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<meta http-equiv="Content-Style-Type" content="text/css">
<title><?= $menu->out_title() ?></title>
<?= $menu->out_css() ?>
</head>
<body>
<?php
    phpinfo();    // Default
    /*******************************************************************************************************
    以下にあるconstantsビット値をひとつまたは 複数個を加算して、オプションのwhat引数に渡すことによって
        出力をカスタマイズできます。 それぞれの定数やビット値をor演算子で結んで渡すこともできます。
    phpinfo() options
    名前(定数)         値 説明 
    INFO_GENERAL        1 The configuration line, php.ini location, build date, Web Server, System and more.  
    INFO_CREDITS        2 PHP 4 Credits. See also phpcredits().  
    INFO_CONFIGURATION  4 Current Local and Master values for php directives. See also ini_get().  
    INFO_MODULES        8 Loaded modules and their respective settings. See also get_loaded_modules().  
    INFO_ENVIRONMENT   16 Environment Variable information that's also available in $_ENV.  
    INFO_VARIABLES     32 Shows all predefined variables from EGPCS (Environment, GET, POST, Cookie, Server).  
    INFO_LICENSE       64 PHP License information. See also the license faq.  
    INFO_ALL           -1 Shows all of the above. This is the default value.  
    
    <example>
    phpinfo(32);        EGPCS順の変数と値の表示 (debug用)
    phpinfo(32 | 64);   EGPCS順の変数と値の表示 と ライセンス表示
    phpinfo(3);         1+2=3 GENERAL と CREDITS を表示
    *******************************************************************************************************/
?>
</body>
</html>
<?php ob_end_flush(); // 出力バッファをgzip圧縮 END ?>
