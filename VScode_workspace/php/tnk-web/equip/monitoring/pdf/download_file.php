<?php
//////////////////////////////////////////////////////////////////////////////
// 届出・申請書メニュー 日本語ファイルダウンロード                          //
// Copyright (C) 2014-2014 Norihisa.Ohya norihisa_ooya@nitto-kohki.co.jp    //
// Changed history                                                          //
// 2014/09/19 Created  download_file.php                                    //
//////////////////////////////////////////////////////////////////////////////
ini_set('error_reporting', E_ALL);      // E_ALL='2047' debug 用
// ini_set('display_errors', '1');         // Error 表示 ON debug 用 リリース後コメント
session_start();                        // ini_set()の次に指定すること Script 最上行
//ob_start('ob_gzhandler');               // 出力バッファをgzip圧縮

require_once ('../../../function.php');       // TNK 全共通 function
require_once ('../../../MenuHeader.php');     // TNK 全共通 menu class
require_once ('../../../tnk_func.php');
access_log();                           // Script Name は自動取得

///// TNK 共用メニュークラスのインスタンスを作成
$menu = new MenuHeader(0);    // 認証レベル=0, リターンアドレス, タイトルの指定なし

////////////// サイト設定
$menu->set_site(97, 999);                // site_index=4(プログラム開発) site_id=999(子メニューあり)

// ファイルダウンロード設定
$filename_old = substr($_SERVER['PATH_INFO'], 1);
//$filename_old = $filename_old . "#CB24001-0!A1";
// 以下２つのエンコードで使い分けないと、ダウンロードでエラーが発生する
// 実ファイル指定
$filename     = mb_convert_encoding($filename_old, "EUC", "UTF-8");
// ダウンロードファイル名指定
$filename2    = mb_convert_encoding($filename_old, "SJIS", "UTF-8");
header("Content-Type: application/octet-stream"); 
header("Content-Disposition: attachment; filename=".$filename2);
header("Content-Length:".filesize($filename));
readfile($filename);
?>
