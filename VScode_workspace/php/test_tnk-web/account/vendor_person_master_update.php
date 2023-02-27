<?php
//////////////////////////////////////////////////////////////////////////////
// #!/usr/local/bin/php-4.3.4-cgi -q                                        //
// ベンダーマスター(担当者)の更新  AS400 UKWLIB/W#MITANL                    //
// AS/400 ----> Web Server (PHP) FTP転送は不可 EBCDICの変換が出来ないため   //
// Copyright (C) 2017-2017 Norihisa.Ohya norihisa_ooya@nitto-kohki.co.jp    //
// Changed history                                                          //
// 2017/05/26 Created  vendor_person_master_update.php                      //
//////////////////////////////////////////////////////////////////////////////
ini_set('error_reporting', E_ALL);          // E_ALL='2047' debug 用
// ini_set('implicit_flush', '0');             // echo print で flush させない(遅くなるため)
ini_set('max_execution_time', 1200);        // 最大実行時間=20分
session_start();                            // ini_set()の次に指定すること Script 最上行
require_once ('/var/www/html/function.php');
require_once ('/var/www/html/tnk_func.php');   // account_group_check()で使用
access_log();                               // Script Name 自動取得
// $_SESSION['site_index'] = 20;               // 月次損益関係=10 最後のメニューは 99 を使用
// $_SESSION['site_id']    = 10;               // 下位メニュー無し (0 <=)

//////////// 呼出元の取得
$act_referer = $_SESSION['act_referer'];

//////////// 認証チェック
if (account_group_check() == FALSE) {
    // $_SESSION['s_sysmsg'] = 'あなたは許可されていません!<br>管理者に連絡して下さい!';
    $_SESSION['s_sysmsg'] = "Accounting Group の権限が必要です！";
    header('Location: ' . $act_referer);
    exit();
}

///// 絶対パスを取得
$realDir = realpath( dirname( __FILE__));
///// 文字化け対策のため cli版の呼出へ変更 オリジナルは vendor_master_update_http.php で保存
$_SESSION['s_sysmsg'] = `{$realDir}/vendor_person_master_update_cli.php`;

header('Location: ' . H_WEB_HOST . ACT . 'vendor_person_master_view.php');   // チェックリストへ
// header('Location: http://masterst.tnk.co.jp/account/vendor_master_view.php');
// header('Location: ' . $act_referer);
exit();
?>
