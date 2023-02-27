<?php
//////////////////////////////////////////////////////////////////////////////
// 社員情報管理の 所属の追加処理                                            //
// Copyright (C) 2001-2005 2007 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp //
// Changed history                                                          //
// 2001/07/07 Created   add_usertransfer.php                                //
// 2002/08/07 register_globals = Off 対応 & セッション管理                  //
// 2005/01/17 access_log の変更と view_file_name(__FILE__) の追加           //
// 2007/10/17 $sid = $res[0]['sid'] は使用していないので削除                //
//////////////////////////////////////////////////////////////////////////////
// ini_set('error_reporting', E_ALL);          // E_ALL='2047' debug 用
// ini_set('display_errors', '1');             // Error 表示 ON debug 用 リリース後コメント
session_start();                            // ini_set()の次に指定すること Script 最上行
require_once ('../function.php');           // 共用 関数
require_once ('emp_function.php');          // 社員メニュー専用
access_log();                               // Script Name 自動設定

$_SESSION['userid'] = $_POST['userid'];
$_SESSION['sect']   = 1;

$query = "select section_name from section_master where sid={$_POST['section']}";
$res = array();
getResult($query,$res);

$section_name = $res[0]['section_name'];
$trans_date = $_POST['trans_date_1'] . "-" . $_POST['trans_date_2'] . "-" . $_POST['trans_date_3'];

//  $userinfo = "&userid=" . $_POST['userid'];
//  $histnum=$histnum-1;
//  $lookupinfo="&lookupkind=$lookupkind&lookupkey=$lookupkey&lookupkeykind=$lookupkeykind" . 
//          "&lookupsection=$lookupsection&lookupposition=$lookupposition&lookupentry=$lookupentry&lookupcapacity=$lookupcapacity&lookupreceive=$lookupreceive&histnum=$histnum&retireflg=$retireflg";
if (addTransfer($_POST['userid'], $_POST['section'], $trans_date, $section_name)) {
//      header("Location: http:" . WEB_HOST . "emp_menu.php?func=" . FUNC_ADMINUSERINFO . "&sect=1" . $userinfo . $lookupinfo);
    header('Location: ' . H_WEB_HOST . EMP . 'emp_menu.php?func=' . FUNC_ADMINUSERINFO);
    exit();
}
$_SESSION['s_sysmsg'] = 'ユーザーの所属に関する変更に失敗しました。<br>管理者にお問い合わせください。';
//  header("Location: http:" . WEB_HOST . "emp_menu.php?func=" . FUNC_ADMINUSERINFO . "&sect=1&sysmsg=" . $sysmsg . $userinfo . $lookupinfo);
header('Location: ' . H_WEB_HOST . EMP . 'emp_menu.php?func=' . FUNC_ADMINUSERINFO . '&sect=1&sysmsg=');
?>
