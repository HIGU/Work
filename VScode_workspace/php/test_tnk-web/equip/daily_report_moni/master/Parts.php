<?php
// 設備稼働管理システムの部品マスター保守               Client interface 部 //
//                                                  MVC View の Parent 部   //
// Copyright (C) 2004-2006 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp      //
// Changed history                                                          //
// 2004/07/01 Created   Parts.php                                           //
// 2006/04/12 MenuHeader クラス対応                                         //
//////////////////////////////////////////////////////////////////////////////
// ini_set('error_reporting', E_STRICT);       // E_STRICT=2048(php5) E_ALL=2047 debug 用
ini_set('error_reporting', E_ALL);          // E_STRICT=2048(php5) E_ALL=2047 debug 用
// ini_set('display_errors', '1');             // Error 表示 ON debug 用 リリース後コメント
ob_start('ob_gzhandler');                   // 出力バッファをgzip圧縮
session_start();                            // ini_set()の次に指定すること Script 最上行

require_once ('../../../MenuHeader.php');   // TNK 全共通 menu class
require_once ('../../../function.php');     // access_log()等で使用
require_once ('../com/define.php'); 
require_once ('../com/function.php'); 
access_log();                               // Script Name は自動取得

///// TNK 共用メニュークラスのインスタンスを作成
$menu = new MenuHeader(0);                  // 認証チェック0=一般以上 戻り先=TOP_MENU タイトル未設定
//////////// タイトル名(ソースのタイトル名とフォームのタイトル名)
$menu->set_title('部品マスター');
//////////// フレームの呼出先のアクション(frame)名とアドレス設定
$menu->set_frame('Header', EQUIP2 . 'daily_report_moni/master/PartsSearch.php');
$menu->set_frame('List'  , EQUIP2 . 'daily_report_moni/master/PartsList.php');
// フレーム版は $menu->set_action()ではなく$menu->set_frame()を使用する

// 管理者モードの取得
$AdminUser = AdminUser( FNC_MASTER );

if (@$_REQUEST['RetUrl'] != '') {
    $RetUrl = '?RetUrl='.@$_REQUEST['RetUrl'];
}

?>
<!DOCTYPE html>
<html>
<head>
<?php require_once ('../com/PageHeader.php'); ?>
<title><?php echo $menu->out_title() ?></title>
</head>
<FRAMESET rows="170,*">
    <FRAME src= '<?php echo $menu->out_frame('Header'), $RetUrl?>'  name='SearchFream'>
    <FRAME src= '<?php echo $menu->out_frame('List') ?>' name='ListFream'>
</FRAMESET>
<body>
</body>
</html>
<?php ob_end_flush(); ?>
