<?php
//////////////////////////////////////////////////////////////////////////////
// 社員情報管理の 職位の有効･無効 設定                                      //
// Copyright (C) 2001-2005 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp      //
// Changed history                                                          //
// 2001/07/07 Created  select_position.php                                  //
// 2002/08/07 register_globals = Off 対応 & セッション管理                  //
// 2004/03/31 アンカー #position を 追加                                    //
// 2005/01/17 access_log の変更と view_file_name(__FILE__) の追加           //
//////////////////////////////////////////////////////////////////////////////
// ini_set('error_reporting', E_ALL);          // E_ALL='2047' debug 用
// ini_set('display_errors', '1');             // Error 表示 ON debug 用 リリース後コメント
session_start();                            // ini_set()の次に指定すること Script 最上行
require_once ('../function.php');           // 共用 関数
require_once ('emp_function.php');          // 社員メニュー専用
access_log();                               // Script Name 自動設定

if ($_SESSION['Auth'] < 2) { 
    $_SESSION['s_sysmsg'] = 'あなたには権限がありません。<br>管理者にお問い合わせ下さい。';
    header('Location: ' . H_WEB_HOST . EMP . 'emp_menu.php');
    exit();
}

if (indPosition($_POST['pid'],$_POST['pflg'])) {
    header('Location: ' . H_WEB_HOST . EMP . 'emp_menu.php?func=' . FUNC_CHGINDICATE . '#position');
    exit();
}
$_SESSION['s_sysmsg'] .= '職位の追加変更に失敗しました。<br>職位名が重複しています。';
header('Location: ' . H_WEB_HOST . EMP . 'emp_menu.php?func=' . FUNC_CHGINDICATE . '#position');
?>
