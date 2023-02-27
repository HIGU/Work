<?php
//////////////////////////////////////////////////////////////////////////////
// 社員情報管理の 教育の追加処理                                            //
// Copyright (C) 2001-2005 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp      //
// Changed history                                                          //
// 2001/07/07 Created   add_userreceive.php                                 //
// 2002/08/07 register_globals = Off 対応 & セッション管理                  //
// 2005/01/17 access_log の変更と view_file_name(__FILE__) の追加           //
//////////////////////////////////////////////////////////////////////////////
// ini_set('error_reporting', E_ALL);          // E_ALL='2047' debug 用
// ini_set('display_errors', '1');             // Error 表示 ON debug 用 リリース後コメント
session_start();                            // ini_set()の次に指定すること Script 最上行
require_once ('../function.php');           // 共用 関数
require_once ('emp_function.php');          // 社員メニュー専用
access_log();                               // Script Name 自動設定

$_SESSION['userid'] = $_POST['userid'];
$_SESSION['recv']   = 1;

$begin_date = $_POST['begin_date_1'] . "-" . $_POST['begin_date_2'] . "-" . $_POST['begin_date_3'];
$end_date   = $_POST['end_date_1'] . "-" . $_POST['end_date_2'] . "-" . $_POST['end_date_3'];
if (addReceive($_POST['userid'], $_POST['receive'], $begin_date,$end_date)) {
    header('Location: ' . H_WEB_HOST . EMP . 'emp_menu.php?func=' . FUNC_ADMINUSERINFO);
    exit();
}
$_SESSION['s_sysmsg'] = 'ユーザーの教育に関する変更に失敗しました。<br>管理者にお問い合わせください。';
header('Location: ' . H_WEB_HOST . EMP . 'emp_menu.php?func=' . FUNC_ADMINUSERINFO);
?>
