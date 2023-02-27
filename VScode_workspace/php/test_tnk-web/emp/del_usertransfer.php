<?php
//////////////////////////////////////////////////////////////////////////////
// 社員情報管理の 所属 経歴の削除 & 所属名変更 処理                         //
// Copyright (C) 2001-2005 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp      //
// Changed history                                                          //
// 2001/07/07 Created   del_usertransfer.php                                //
// 2002/08/07 register_globals = Off 対応 & セッション管理                  //
// 2005/01/17 access_log の変更と view_file_name(__FILE__) の追加           //
// 2006/01/11 oid を廃止して trans_date へ変更                              //
// 2006/02/14 chg_Sectionname() の oid → trans_date, sid へ変更            //
//////////////////////////////////////////////////////////////////////////////
// ini_set('error_reporting', E_ALL);          // E_ALL='2047' debug 用
// ini_set('display_errors', '1');             // Error 表示 ON debug 用 リリース後コメント
session_start();                            // ini_set()の次に指定すること Script 最上行
require_once ('../function.php');           // 共用 関数
require_once ('emp_function.php');          // 社員メニュー専用
access_log();                               // Script Name 自動設定

$_SESSION['userid'] = $_POST['userid'];
$_SESSION['sect']   = 1;

if ( isset($_POST['del']) ) {
    // if ( delTransfer($_POST['userid'], $_POST['oid'], $_POST['sid']) ) {
    if ( delTransfer($_POST['userid'], $_POST['trans_date'], $_POST['sid']) ) {
        header('Location: ' . H_WEB_HOST . EMP . 'emp_menu.php?func=' . FUNC_ADMINUSERINFO);
        exit();
    }
    $_SESSION['s_sysmsg'] = 'ユーザーの所属の削除に失敗しました。<br>管理者にお問い合わせください。';
    header('Location: ' . H_WEB_HOST . EMP . 'emp_menu.php?func=' . FUNC_ADMINUSERINFO);
} elseif ( isset($_POST['chg']) ) {
    // if ( chg_Sectionname($_POST['userid'], $_POST['oid'], $_POST['section_name']) ) {
    if ( chg_Sectionname($_POST['userid'], $_POST['trans_date'], $_POST['sid'], $_POST['section_name']) ) {
        header('Location: ' . H_WEB_HOST . EMP . 'emp_menu.php?func=' . FUNC_ADMINUSERINFO);
        exit();
    }
    $_SESSION['s_sysmsg'] = 'ユーザーの所属名の変更に失敗しました。<br>管理者にお問い合わせください。';
    header('Location: ' . H_WEB_HOST . EMP . 'emp_menu.php?func=' . FUNC_ADMINUSERINFO);
}
?>
