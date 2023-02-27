<?php
//////////////////////////////////////////////////////////////////////////////
// クライアントで作成したSVGファイル(template)の整形処理                    //
// テンプレートエンジンはsimplate, クライアント印刷はPXDoc を使用           //
// Copyright (C) 2007      Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp      //
// Changed history                                                          //
// 2007/06/04 Created  svgUpload.php                                        //
// 2007/12/06 Directory を /test/print/ → /pxd/ へ変更                     //
//////////////////////////////////////////////////////////////////////////////
ini_set('error_reporting', E_ALL | E_STRICT);
ini_set('display_errors', '0');             // Error 表示 ON debug 用 リリース後コメント
session_start();                            // ini_set()の次に指定すること Script 最上行
require_once ('../function.php');           // define.php と pgsql.php を require_once している
require_once ('../MenuHeader.php');         // TNK 全共通 menu class
///// TNK 共用メニュークラスのインスタンスを作成
$menu = new MenuHeader(0);                  // 認証チェック0=一般以上 戻り先=TOP_MENU タイトル未設定

$ok_flg = true;

if (!isset($_FILES['svgFile']['name'])) {
    $_SESSION['s_sysmsg'] = 'ファイルが指定されていません！';
    $ok_flg = false;
} elseif (!preg_match('/\.svg$/i', $_FILES['svgFile']['name'])) {
    $_SESSION['s_sysmsg'] = '指定されたファイルは、SVGファイルではありません！';
    $ok_flg = false;
}
if (!isset($_FILES['svgFile']['tmp_name'])) {
    $_SESSION['s_sysmsg'] = '一時ファイルを作成できませんでした！';
    $ok_flg = false;
}
if ($ok_flg === false) {
    header('Location: ' . H_WEB_HOST . $menu->out_RetUrl());    // 直前の呼出元へ戻る
    exit();
}

$ok_flg = true;

$currentFullPathName = realpath(dirname(__FILE__));
$file_name = "{$currentFullPathName}/template/" . $_FILES['svgFile']['name'];
if (file_exists($file_name)) {
    if (!unlink($file_name)) {
        $_SESSION['s_sysmsg'] = "既に存在するファイル{$file_name}を削除できませんでした！";
        $ok_flg = false;
    }
}
if ($ok_flg) {
    if (!rename($_FILES['svgFile']['tmp_name'], $file_name)) {
        $_SESSION['s_sysmsg'] = "一時ファイルから{$file_name}を作成できませんでした！";
        $ok_flg = false;
    }
}
if ($ok_flg) {
    if (!chmod($file_name, 0666)) {
        $_SESSION['s_sysmsg'] = "{$file_name}のパーミッションを変更できませんでした！！";
        $ok_flg = false;
    }
}
$msg = `./svg2template.php {$file_name}`;

/********** デバッグ用
$filename = 'svgUpload-debug.txt';
$fp = fopen($filename, 'w');
fwrite($fp, 'クライアントのファイル名は ' . $_FILES['svgFile']['name'] . "\n");
fwrite($fp, 'サーバー側のファイル名は ' . $_FILES['svgFile']['tmp_name'] . "\n");
fclose($fp);
chmod($filename, 0666);
**********/

if ($ok_flg) {
    $file_name = str_replace('.svg', '.tpl', $file_name);
    $_SESSION['s_sysmsg'] = "SVGアップロード・templateコンバート処理が完了しました。\\n\\nファイルは　「　{$file_name}　」　で作成しました。";
}
header('Location: ' . H_WEB_HOST . $menu->out_RetUrl());    // 直前の呼出元へ戻る
exit();
?>
