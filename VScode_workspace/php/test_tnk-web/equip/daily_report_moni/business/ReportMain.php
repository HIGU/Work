<?php
//////////////////////////////////////////////////////////////////////////////
// 組立設備稼働管理システムの機械運転日報 日報検索フォーム                  //
// Copyright (C) 2021-2021 norihisa_ooya@nitto-kohki.co.jp                  //
// Original by yamagishi@matehan.co.jp                                      //
// Changed history                                                          //
// 2021/03/26 Created  ReportMain.php                                       //
//////////////////////////////////////////////////////////////////////////////
ini_set('error_reporting', E_ALL);          // E_ALL='2047' debug 用
require_once ('../../../function.php');     // TNK 全共通 function
require_once ('../../../MenuHeader.php');   // TNK 全共通 menu class
require_once ('../com/define.php'); 
require_once ('../com/function.php'); 
require_once ('MakeReport.php'); 
access_log();                               // Script Name は自動取得

///// TNK 共用メニュークラスのインスタンスを作成
$menu = new MenuHeader();                   // 認証チェックも行っている

//////////// フレームの呼出先のアクション(frame)名とアドレス設定
$menu->set_frame('Header', EQUIP2 . 'daily_report_moni/business/ReportSearch.php');
$menu->set_frame('List'  , EQUIP2 . 'daily_report_moni/business/ReportList.php');

// 管理者モードの取得
$AdminUser = AdminUser( FNC_REPORT );

// 戻り先の取得
$RetUrl = '?RetUrl='.@$_REQUEST['RetUrl'];

// コネクションの取得
$con = getConnection();

// 機械運転ログの集計
MakeReport();

ob_start('ob_gzhandler');
?>
<!DOCTYPE HTML>
<html>
<head>
<?php require_once ('../com/PageHeader.php'); ?>
<title>機械運転日報</title>
</head>
<FRAMESET rows='220,*'>
    <FRAME src= 'ReportSearch.php<?=$RetUrl?>'  name='SearchFream'>
    <FRAME src= 'ReportList.php'    name='ListFream'>
</FRAMESET>
<body>
</body>
</html>
<?php ob_end_flush(); ?>
