<?php
//////////////////////////////////////////////////////////////////////////////
// 社員情報管理の 資格の登録 実行                                           //
// Copyright (C) 2001-2010 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp      //
// Changed history                                                          //
// 2001/07/07 Created   add_capacityentry.php                               //
// 2002/08/07 register_globals = Off 対応 & セッション管理                  //
// 2005/01/17 access_log の変更と view_file_name(__FILE__) の追加           //
// 2007/02/09 一括登録で空白の場合は飛ばして登録できるように対応            //
//            登録可能者が居ないときのチェックを追加 大谷                   //
// 2007/02/15 POSTをREQUESTに変更                                           //
//            社員名の表示を追加    大谷                                    //
// 2007/07/06 登録時資格名を表示するように変更 大谷                         //
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
        header('Location: http:' . H_WEB_HOST . EMP . 'emp_menu.php?func=' . FUNC_CHGCAPACITY);
        exit();
    }
}

$rows = count($_REQUEST['uid']);
$_SESSION['s_sysmsg'] = "資格の登録　{$_REQUEST['capacity_name']}\\n";
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
    if (addCapaentry($_REQUEST['uid'], $_REQUEST['acq_date'], $_REQUEST['capacity'])) {
        header("Location: http:" . WEB_HOST . "emp/emp_menu.php?func=" . FUNC_CHGCAPACITY);
        exit();
    }
    $_SESSION['s_sysmsg'] = "資格の追加に失敗しました。管理者にお問い合わせください。";
    header('Location: ' . H_WEB_HOST . EMP . 'emp_menu.php?func=' . FUNC_CHGCAPACITY);
} else {
    $_SESSION['s_sysmsg'] = "登録できる社員がいませんでした。社員番号を確認して再度登録してください。";
    header('Location: ' . H_WEB_HOST . EMP . 'emp_menu.php?func=' . FUNC_CHGCAPACITY);
}
?>
