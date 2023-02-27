<?php
//////////////////////////////////////////////////////////////////////////////
// #!/usr/local/bin/php-4.3.4-cgi -q                                        //
// ベンダーマスター(発注先マスター)の更新  AS400 UKWLIB/W#MIWKCK            //
// AS/400 ----> Web Server (PHP) FTP転送は不可 EBCDICの変換が出来ないため   //
// Copyright (C) 2003-2005 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp      //
// Changed history                                                          //
// 2003/11/18 Created  vendor_master_update.php                             //
//                                  act_payable_get_ftp.phpを雛型に使用     //
// 2003/11/18 http → cli版へ変更出来るように requier_once を絶対指定へ     //
//            AS/400 で UKPLIB/Q#MIWKCK RUNQRY で実行し ExcelでTXTに変換    //
// 2003/11/28 ログをコメントにしていたのを monthly_update.log にして追加    //
// 2003/12/08 SJIS → EUC 変換ロジック追加   (NULL → SPACE へ変換)         //
//                    (SJISでEUCにない文字はNULLバイトに変換される事に注意) //
// 2004/01/07 代表者が入っていない場合の対応 $data[6] = '' を追加           //
// 2004/04/05 header('Location: http:' . WEB_HOST . 'account/?????' -->     //
//                                  header('Location: ' . H_WEB_HOST . ACT  //
// 2004/12/02 mb_ereg_replace('��','（株）',$data);機種依存文字を規格文字へ //
// 2005/03/04 dir変更 /home/www/html/weekly/ → /home/guest/monthly/       //
//////////////////////////////////////////////////////////////////////////////
ini_set('error_reporting', E_ALL);          // E_ALL='2047' debug 用
// ini_set('implicit_flush', '0');             // echo print で flush させない(遅くなるため)
ini_set('max_execution_time', 1200);        // 最大実行時間=20分
session_start();                            // ini_set()の次に指定すること Script 最上行
require_once ('/home/www/html/tnk-web/function.php');
require_once ('/home/www/html/tnk-web/tnk_func.php');   // account_group_check()で使用
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
$_SESSION['s_sysmsg'] = `{$realDir}/vendor_master_update_cli.php`;

header('Location: ' . H_WEB_HOST . ACT . 'vendor_master_view.php');   // チェックリストへ
// header('Location: http://masterst.tnk.co.jp/account/vendor_master_view.php');
// header('Location: ' . $act_referer);
exit();
?>
