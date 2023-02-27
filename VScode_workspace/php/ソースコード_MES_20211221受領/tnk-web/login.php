<?php
//////////////////////////////////////////////////////////////////////////////
// 栃木日東工器 ログインチェック                                            //
// Copyright (C) 2001-2004 Kazuhiro.Kobayashi tnkyss@nitto-kohki.co.jp      //
// Changed history                                                          //
// 2001/07/07 Created   login.php                                           //
// 2002/08/07 セッション管理を追加                                          //
// 2002/08/26 フレームを使ってサイトメニューを追加                          //
// 2003/12/15 メッセージを一部変更(入力ミスか→追加, 貴方→あなた)          //
// 2004/02/13 index1.php → authenticate.php へ変更                         //
// 2004/06/10 開発用テンプレート用のリターンアドレスをテスト用に追加        //
// 2005/11/24 セッションにもパスワードの暗号化を追加                        //
//////////////////////////////////////////////////////////////////////////////
ini_set('error_reporting', E_ALL);          // E_ALL='2047' debug 用
// ini_set('display_errors', '1');             // Error 表示 ON debug 用 リリース後コメント
session_start();                            // ini_set()の次に指定すること Script 最上行
require_once ('function.php');              // TNK共通ファンクッション
access_log();                               // Script Name は自動取得

// session_register('web_file','login_time','User_ID','Password','Auth');
$_SESSION['login_time'] = Date('m-d H:i');
$_SESSION['User_ID']    = $_POST['userid'];
$_SESSION['Password']   = md5($_POST['passwd']);
//  $_SESSION['Auth']       = $authority;
$_SESSION['web_file']   = $_SERVER['SCRIPT_NAME'];

if ( authorityUser($_POST['userid'], $_SESSION['Password'], $authority) ) {
    /*
    $uid   = $_POST['userid'];
    $query = "SELECT sid FROM user_detailes WHERE uid='$uid'";
    $res   = array();
    getResult($query,$res);
    if ($res[0][0] == '95') {          // 所属が日東工器の場合
        //$_SESSION['s_sysmsg'] = $res[0][0];
        $_SESSION['Auth'] = $authority;
        header('Location: http:' . WEB_HOST . 'window_ctl_nk.php');
    } else {
    */
        //$_SESSION['s_sysmsg'] = $res[0][0];
        $_SESSION['Auth'] = $authority;
        // setcookie('ckUserid',$userid);       // 全てセッション管理に変更したら削除予定
        // setcookie('ckPasswd',$passwd);
        // setcookie('ckAuthority',$authority);
        // $_SESSION['template_ret'] = 'system_menu.php';  // 開発用テンプレート用に追加←現在は使っていない
        header('Location: http:' . WEB_HOST . 'window_ctl.php');
    /*
    }
    */
} else {
    // setcookie('ckUserid');               // 全てセッション管理に変更したら削除予定
    // setcookie('ckPasswd');
    // setcookie('ckAuthority');
    $_SESSION['s_sysmsg'] = '認証に失敗しました。入力ミスか、あなたの情報が登録されていない可能性があります。' . 
            'この状態が続くようでしたら管理担当者にお問い合わせ下さい。';
    header('Location: http:' . WEB_HOST . 'authenticate.php');
}
?>
