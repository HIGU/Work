<?php
//////////////////////////////////////////////////////////////////////////////
// 社員情報管理の 資格項目名の新規追加                                      //
// Copyright (C) 2001-2010 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp      //
// Changed history                                                          //
// 2001/07/07 Created   addselect_capacity.php                              //
// 2002/08/07 register_globals = Off 対応 & セッション管理                  //
// 2003/10/22 システムメッセージを[重複]から[項目追加に失敗]に変更 .=       //
//            (addCapacityでメッセージを保存するため)  #anchor を追加       //
// 2005/01/17 access_log の変更と view_file_name(__FILE__) の追加           //
// 2010/03/11 暫定的に大渕さん（970268）が登録できるように変更         大谷 //
// 2019/01/31 暫定的に平石さん（300551）が登録できるように変更         大谷 //
//////////////////////////////////////////////////////////////////////////////
// ini_set('error_reporting', E_ALL);          // E_ALL='2047' debug 用
// ini_set('display_errors', '1');             // Error 表示 ON debug 用 リリース後コメント
session_start();                            // ini_set()の次に指定すること Script 最上行
require_once ('../function.php');           // 共用 関数
require_once ('emp_function.php');          // 社員メニュー専用
access_log();                               // Script Name 自動設定

if ($_SESSION['Auth'] < 2) {
    if ($_SESSION['User_ID'] != '970268' && $_SESSION['User_ID'] != '300551') {
        $_SESSION['s_sysmsg'] = 'あなたには権限がありません。<br>管理者にお問い合わせ下さい。';
        header('Location: ' . H_WEB_HOST . EMP . 'emp_menu.php');
        exit();
    }
}

if (addselectCapacity($_POST['cid'], $_POST['capacity_name'], $_POST['cflg'])) {
    header('Location: ' . H_WEB_HOST . EMP . 'emp_menu.php?func=' . FUNC_CHGINDICATE . '#capacity');
    exit();
}
$_SESSION['s_sysmsg'] .= '<br>資格の項目追加に失敗しました。';  // addselectCapacityでエラーの場合にメッセージがあるため.=に変更
header('Location: ' . H_WEB_HOST . EMP . 'emp_menu.php?func=' . FUNC_CHGINDICATE . '#capacity');
?>
