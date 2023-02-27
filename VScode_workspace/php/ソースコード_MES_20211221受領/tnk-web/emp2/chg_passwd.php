<?php
//////////////////////////////////////////////////////////////////////////////
// 社員情報管理の 自己情報照会からパスワード変更処理                        //
// Copyright (C) 2001-2005 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp      //
// Changed history                                                          //
// 2001/07/07 Created chg_passwd.php                                        //
// 2002/08/07 register_globals = Off 対応 & セッション管理                  //
//                         サーバーのローカルユーザー変更処理をコメント     //
// 2004/04/19 table定義を変更 詳しくは kk_table_create を参照 user_master   //
// 2005/01/17 access_log の変更と view_file_name(__FILE__) の追加           //
// 2005/11/24 パスワードを暗号化し登録する パスワード変更完了のメッセージ追 //
//////////////////////////////////////////////////////////////////////////////
// ini_set('error_reporting', E_ALL);          // E_ALL='2047' debug 用
// ini_set('display_errors', '1');             // Error 表示 ON debug 用 リリース後コメント
session_start();                            // ini_set()の次に指定すること Script 最上行
require_once ('../function.php');           // 共用 関数
require_once ('emp_function.php');          // 社員メニュー専用
// require("../define.php");                   // function.php で require されている
// define("CMD_STR_PASSWD","/usr/bin/sudo /usr/sbin/newusers ");
access_log();                               // Script Name 自動設定

$_SESSION['userid'] = $_POST['userid'];
$_SESSION['kana']   = $_POST['kana'];
$_SESSION['name']   = $_POST['name'];
$_SESSION['section_name'] = $_POST['section_name'];
$_POST['passwd']    = md5($_POST['passwd']);

//  $userinfo = "&userid=" . $_POST['userid'];
//  $histnum = $_POST['histnum'] - 1;
//  $lookupinfo = "&lookupkind=" . $_POST['lookupkind'] . "&lookupkey=" . $_POST['lookupkey'] . "&lookupkeykind=" . $_POST['lookupkeykind'] . 
//      "&lookupsection=" . $_POST['lookupsection'] . "&lookupposition=" . $_POST['lookupposition'] . "&lookupentry=" . $_POST['lookupentry'] . 
//      "&lookupcapacity=" . $_POST['lookupcapacity'] . "&lookupreceive=" . $_POST['lookupreceive'] . "&histnum=" . $_POST['histnum'] . "&retireflg=" . $_POST['retireflg'];

if (funcConnect()) {
    execQuery("begin");

    $query="update user_master set passwd='" . $_POST['passwd'] . "' where uid='" . $_POST['userid'] . "'";
    if (!execQuery($query)) {
        execQuery("end");
        disConnectDB();
        
        /* add 09/27 begin */
//      $file=TEMP_DIR . "user";
//      $str=sprintf("%s:%s:::::\n",$acount,$passwd);
//      if($fp=fopen($file,"w")){
//          fwrite($fp,$str,strlen($str));
//          fclose($fp);
//          $cmd=escapeshellcmd(CMD_STR_PASSWD . $file);
//          exec($cmd);
//          unlink($file);
//      }
        /* end */
        
        $_SESSION['s_sysmsg'] = "パスワードを変更しました。";
        if ($_GET['func'] == FUNC_MINEINFO) {
            $_SESSION['Password'] = $_POST['passwd'];
            header('Location: ' . H_WEB_HOST . EMP . 'emp_menu.php?func=' . $_GET['func']);
        } else {
            $_SESSION['Password'] = $_POST['passwd'];
            header('Location: ' . H_WEB_HOST . EMP . 'emp_menu.php?func=' . $_POST['func'] . '&pwd=' . $_POST['pwd']);
        }
        exit();
    } else {
        execQuery('rollback');
        disConnectDB();
    }
}
$_SESSION['s_sysmsg'] = "パスワードの変更に失敗しました。<br>管理者に連絡して下さい。";
if ($_GET['func'] == FUNC_MINEINFO) {
    header('Location: ' . H_WEB_HOST . EMP . 'emp_menu.php?func=' . FUNC_MINEINFO . '&pwd=1');
} else {
    header('Location: ' . H_WEB_HOST . EMP . 'emp_menu.php?func=' . FUNC_CHGUSERINFO . '&pwd=1');
}
?>
