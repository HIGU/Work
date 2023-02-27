<?php
//////////////////////////////////////////////////////////////////////////////
// 社員情報管理の 計画有給の登録 実行                                       //
// Copyright (C) 2015-2015 Norihisa.Ohya norihisa_ooya@nitto-kohki.co.jp    //
// Changed history                                                          //
// 2015/06/18 Created   add_holydayentry.php                                //
// 2015/06/19 処理の実行権限に野澤さんを追加                                //
// 2015/06/22 権限エラーを修正                                              //
//////////////////////////////////////////////////////////////////////////////
// ini_set('error_reporting', E_ALL);          // E_ALL='2047' debug 用
// ini_set('display_errors', '1');             // Error 表示 ON debug 用 リリース後コメント
session_start();                            // ini_set()の次に指定すること Script 最上行
require_once ('../function.php');           // 共用 関数
require_once ('emp_function.php');          // 社員メニュー専用
access_log();                               // Script Name 自動設定

if ($_SESSION['Auth'] < 2) { 
    if ($_SESSION['User_ID'] != '970227' && $_SESSION['User_ID'] != '015806') {
        $_SESSION['s_sysmsg'] = 'あなたには権限がありません。<br>管理者にお問い合わせ下さい。';
        header('Location: http:' . H_WEB_HOST . EMP . 'emp_menu.php?func=' . FUNC_ADDPHOLYDAY);
        exit();
    }
}

$rows = count($_REQUEST['uid']);
$i = 0; // 登録者数
for ($r=0; $r<$rows; $r++) {
    if (!$_REQUEST['uid'][$r] == "") {
        if ($_REQUEST['chk_name'][$r] < 2) {
            $uid[$r] = $_REQUEST['uid'][$r];
            $uname[$r] = $_REQUEST['uname'][$r];
            if (!$uid[$r]) continue;
            $_SESSION['s_sysmsg'] .= "社員番号={$uid[$r]} ";
            $_SESSION['s_sysmsg'] .= "社員名={$uname[$r]}\\n";
            $i++;
        }
    }
}
$_REQUEST['uid'] = ""; //初期化
$rows_uid = count($uid);    //登録可能者数
for ($r=0; $r<$rows_uid; $r++) {
    $_REQUEST['uid'][$r] = $uid[$r];
}
if ($rows_uid > 0) {
    $_SESSION['s_sysmsg'] .= "合計登録者数は{$i}人";
    if (addHolyday($_REQUEST['uid'], $_REQUEST['acq_date'])) {
        header("Location: http:" . WEB_HOST . "emp/emp_menu.php?func=" . FUNC_ADDPHOLYDAY);
        exit();
    }
    $_SESSION['s_sysmsg'] = "計画有給の追加に失敗しました。管理者にお問い合わせください。";
    header('Location: ' . H_WEB_HOST . EMP . 'emp_menu.php?func=' . FUNC_ADDPHOLYDAY);
} else {
    $_SESSION['s_sysmsg'] = "登録できる社員がいませんでした。社員番号を確認して再度登録してください。";
    header('Location: ' . H_WEB_HOST . EMP . 'emp_menu.php?func=' . FUNC_ADDPHOLYDAY);
}
?>
