<?php
//////////////////////////////////////////////////////////////////////////////
// Ajaxを使って site_menu の 表示・非表示の状態をサーバーに書込む           //
// Copyright(C) 2005-2005 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp       //
// Changed History                                                          //
// 2005/09/05 Created   setMenuOnOff.php                                    //
// 2005/09/11 表示のOnOff強制設定をbase_classのmenuOnOff()→Ajax()のため追加//
//////////////////////////////////////////////////////////////////////////////
ini_set('error_reporting', E_ALL);      // E_ALL='2047' debug 用
// ini_set('display_errors', '1');         // Error 表示 ON debug 用 リリース後コメント
session_start();                        // ini_set()の次に指定すること Script 最上行
require_once ('function.php');          // TNK 全共通 function
access_log();                           // Script Name は自動取得

////////////// 表示の On Off 強制設定
if (isset($_REQUEST['site'])) {
    $_SESSION['site_view'] = $_REQUEST['site'];
    exit;
}

////////////// 表示の On Off 設定切替
if (!isset($_SESSION['site_view'])) $_SESSION['site_view'] = 'off';
if ($_SESSION['site_view'] == 'on') {
    $_SESSION['site_view'] = 'off';
} else {
    $_SESSION['site_view'] = 'on';
}

?>
