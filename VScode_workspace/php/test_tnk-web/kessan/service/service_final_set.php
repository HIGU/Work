<?php
//////////////////////////////////////////////////////////////////////////
// サービス割合 月次確定処理  ＆ 確定解除                               //
// 2003/11/05 Copyright(C) 2003 K.Kobayashi tnksys@nitto-kohki.co.jp    //
// 変更経歴                                                             //
// 2003/11/05 新規作成  service_final_set.php?para  para = set || unset //
//////////////////////////////////////////////////////////////////////////
ini_set('error_reporting',E_ALL);   // E_ALL='2047' debug 用
// ini_set('display_errors','1');      // Error 表示 ON debug 用 
session_start();                    // ini_set()の次に指定すること Script 最上行
require_once ('../../function.php');
require_once ('../../tnk_func.php');
access_log();                       // Script Name は自動取得
$_SESSION['site_index'] = 10;       // 月次・中間・決算メニュー = 10 最後のメニューは 99 を使用
$_SESSION['site_id']    =  5;       // サーブす割合 = 5  下位メニュー無し (0 <=)
$current_script  = $_SERVER['PHP_SELF'];        // 現在実行中のスクリプト名を保存
// $url_referer     = $_SERVER['HTTP_REFERER'];    // 呼出もとのURLを保存 前のスクリプトで分岐処理をしている場合は使用しない
$url_referer     = $_SESSION['service_referer'];    // 分岐処理前に保存されている呼出元をセットする

////////////// 認証チェック
if (account_group_check() == FALSE) {
    $_SESSION["s_sysmsg"] = "あなたは許可されていません。<br>管理者に連絡して下さい。";
    header("Location: $url_referer");                   // 直前の呼出元へ戻る
// if (!isset($_SESSION["User_ID"]) || !isset($_SESSION["Password"]) || !isset($_SESSION["Auth"])) {
//    $_SESSION["s_sysmsg"] = "認証されていないか認証期限が切れました。ログインからお願いします。";
//    header("Location: http:" . WEB_HOST . "menu.php");
    exit();
}

//////////// 対象年月のセッションデータ取得
if (isset($_SESSION['service_ym'])) {
    $service_ym = $_SESSION['service_ym']; 
} else {
    $service_ym = date('Ym');        // セッションデータがない場合の初期値(前月)
    if (substr($service_ym,4,2) != 01) {
        $service_ym--;
    } else {
        $service_ym = $service_ym - 100;
        $service_ym = $service_ym + 11;   // 前年の12月にセット
    }
}

//////////// 月次確定処理のセット＆アンセット
if (isset($_GET['set'])) {
    if (`/bin/touch final/{$service_ym}` == 0) {
        $_SESSION['s_sysmsg'] = "<font color='yellow'>{$service_ym}：を確定しました！</font>";
        header("Location: $url_referer");                   // 直前の呼出元へ戻る
        exit();
    } else {
        $_SESSION['s_sysmsg'] = "{$service_ym}：を確定処理に失敗しました！";
        header("Location: $url_referer");                   // 直前の呼出元へ戻る
        exit();
    }
} elseif (isset($_GET['unset'])) {
    if (`/bin/rm -f final/{$service_ym}` == 0) {
        $_SESSION['s_sysmsg'] = "<font color='yellow'>{$service_ym}：を確定解除しました！</font>";
        header("Location: $url_referer");                   // 直前の呼出元へ戻る
        exit();
    } else {
        $_SESSION['s_sysmsg'] = "{$service_ym}：を確定解除に失敗しました！";
        header("Location: $url_referer");                   // 直前の呼出元へ戻る
        exit();
    }
} else {
    $_SESSION['s_sysmsg'] = 'パラメーターが不正です！';
    header("Location: $url_referer");                   // 直前の呼出元へ戻る
    exit();
}

?>
