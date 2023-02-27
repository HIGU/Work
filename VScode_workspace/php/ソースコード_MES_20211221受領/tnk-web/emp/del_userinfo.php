<?php
//////////////////////////////////////////////////////////////////////////////
// 社員情報管理の include file 従業員情報の完全削除                         //
// Copyright (C) 2001-2005 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp      //
// Changed history                                                          //
// 2001/07/07 Created   del_userinfo.php                                    //
// 2002/08/07 register_globals = Off 対応 & セッション管理対応              //
// 2005/01/17 access_log の変更と view_file_name(__FILE__) の追加           //
//              削除時にテキストファイルにバックアップを残す予定 \copy等    //
//////////////////////////////////////////////////////////////////////////////
// ini_set('error_reporting', E_ALL);          // E_ALL='2047' debug 用
// ini_set('display_errors', '1');             // Error 表示 ON debug 用 リリース後コメント
session_start();                            // ini_set()の次に指定すること Script 最上行
require_once ('../function.php');           // 共用 関数
require_once ('emp_function.php');          // 社員メニュー専用
access_log();                               // Script Name 自動設定

$user = trim($_POST['acount']);
if ( delUser($_POST['userid'], $_POST['photoid'], $user) ) {
    if ($_SESSION['retireflg'] == 0) {
        header('Location: ' . H_WEB_HOST . EMP . 'emp_menu.php?func=' . FUNC_LOOKUP);
        exit();
    } else {
        header('Location: ' . H_WEB_HOST . EMP . 'emp_menu.php?func=' . FUNC_RETIREINFO);
        exit();
    }
}
$_SESSION['s_sysmsg'] = 'ユーザー情報の抹消に失敗しました。<br>この状態が続くようでしたら管理者にお問い合わせください。';
if ($_SESSION['retireflg'] == 0) {
    header('Location: ' . H_WEB_HOST . EMP . 'emp_menu.php?func=' . FUNC_LOOKUP);
} else {
    header('Location: ' . H_WEB_HOST . EMP . 'emp_menu.php?func=' . FUNC_RETIREINFO);
}
?>
