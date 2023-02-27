<?php
//////////////////////////////////////////////////////////////////////////////
// カラーチェック１０進数→１６進数変換 post data (select)                  //
// Copyright(C) 2002-2004 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp       //
// History                                                                  //
// 2002/09/09 Created color_check_input.php  register global off 対応       //
// 2002/12/03 サイトメニューに追加のため site_index site_id=20 を追加       //
// 2003/02/26 body に onLoad を追加し初期入力個所に focus() させた          //
// 2004/07/20 Class MenuHeader を使用                                       //
//////////////////////////////////////////////////////////////////////////////
ini_set('error_reporting', E_ALL);          // E_ALL='2047' debug 用
ini_set('display_errors', '1');             // Error 表示 ON debug 用 リリース後コメント
session_start();                            // ini_set()の次に指定すること Script 最上行
require_once ('../function.php');           // TNK 全共通 function
require_once ('../MenuHeader.php');         // TNK 全共通 menu class
access_log();                               // Script Name は自動取得

///// TNK 共用メニュークラスのインスタンスを作成
$menu = new MenuHeader(0);    // 認証チェック1=一般以上 戻り先=セッションより タイトル未設定

////////////// サイト設定
$menu->set_site(99, 20);                // site_index=99(システム管理メニュー) site_id=20(color check)
////////////// リターンアドレス設定
// $menu->set_RetUrl(SYS_MENU);
//////////// タイトル名(ソースのタイトル名とフォームのタイトル名)
$menu->set_title('ページデザイン用カラーチェック');
//////////// 表題の設定
$menu->set_caption('RGB の順番で色番号を指定して下さい。(10進数)');
//////////// 呼出先のaction名とアドレス設定
$menu->set_action('color_check_view', SYS . 'color_check_view.php');

if (isset($_POST['r']) && isset($_POST['g']) && isset($_POST['b'])) {
    $r = $_POST['r'];               // POST データで初期化
    $g = $_POST['g'];
    $b = $_POST['b'];
} else {
    $r = "";                        // 初期化
    $g = "";
    $b = "";
}
/////////// HTML Header を出力してキャッシュを制御
$menu->out_html_header();
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=EUC-JP">
<meta http-equiv="Content-Style-Type" content="text/css">
<title><?= $menu->out_title() ?></title>
<?= $menu->out_site_java() ?>
<?= $menu->out_css() ?>
<style type='text/css'>
<!--
.pt12b {
    font-size:      12pt;
    font-weight:    bold;
}
.pt10 {
    font-size:      10pt;
}
.fllbp{
    font-size:      16pt;
    font-weight:    bold;
}
-->
</style>
</head>
<body onLoad='document.ini_form.r.focus()'>
    <center>
<?= $menu->out_title_border() ?>
    
        <br>
        <form name='ini_form' action='<?= $menu->out_action('color_check_view') ?>' method='post'>
            <div class='caption_font'>RGB の順番で色番号を指定して下さい。(10進数)</div>
            <br>
            <input type='text' name='r' size='3' value='<?php echo $r ?>' maxlength='3'>
            <input type='text' name='g' size='3' value='<?php echo $g ?>' maxlength='3'>
            <input type='text' name='b' size='3' value='<?php echo $b ?>' maxlength='3'>
            <input type='submit' name='view' value='実行' >
        </form>
       <br>
        <div class='pt10'>
            例として Windows2000までのデフォルト値のグレー色は R G B の順番で 214 211 206 です。
        </div>
    </center>
</body>
</html>
