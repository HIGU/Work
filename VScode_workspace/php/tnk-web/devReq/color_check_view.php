<?php
//////////////////////////////////////////////////////////////////////////////
// カラーチェック１０進数→１６進数変換                                     //
// Copyright(C) 2002-2004 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp       //
// History                                                                  //
// 2002/09/09 Created color_check_view.php  register global off 対応        //
// 2002/12/03 サイトメニューに追加のため site_index site_id=20 を追加       //
// 2004/07/20 Class MenuHeader を使用                                       //
//////////////////////////////////////////////////////////////////////////////
ini_set('error_reporting', E_ALL);      // E_ALL='2047' debug 用
ini_set('display_errors', '1');         // Error 表示 ON debug 用 リリース後コメント
session_start();                        // ini_set()の次に指定すること Script 最上行
require_once ('../function.php');       // TNK 全共通 function
require_once ('../MenuHeader.php');     // TNK 全共通 menu class
access_log();                           // Script Name は自動取得

///// TNK 共用メニュークラスのインスタンスを作成
$menu = new MenuHeader(0);    // 認証チェック1=一般以上 戻り先=セッションより タイトル未設定

////////////// サイト設定
$menu->set_site(4, 20);                 // site_index=99(システム管理メニュー) site_id=20(color check)
////////////// リターンアドレス設定
// $menu->set_RetUrl(SYS_MENU);
//////////// タイトル名(ソースのタイトル名とフォームのタイトル名)
$menu->set_title('ページデザイン用カラービュー');
//////////// 表題の設定
$menu->set_caption('カラーチェック１０進数→１６進数変換');
//////////// 呼出先のaction名とアドレス設定
// $menu->set_action('color_check_view', DEV . 'color_check_view.php');

if (isset($_POST['r'])) {
    $r = $_POST['r'];   // RGB → R
    $g = $_POST['g'];   // RGB → G
    $b = $_POST['b'];   // RGB → B
    $_SESSION['r'] = $r;
    $_SESSION['g'] = $g;
    $_SESSION['b'] = $b;
} else {
    $r = $_SESSION['r'];
    $g = $_SESSION['g'];
    $b = $_SESSION['b'];
}

/////////// HTML Header を出力してキャッシュを制御
$menu->out_html_header();
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
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
<body>
    <center>
<?= $menu->out_title_border() ?>
        <hr>
        <?php
            print("$r の１６進数は:". dechex ($r) . "<br>\n");
            print("$g の１６進数は:". dechex ($g) . "<br>\n");
            print("$b の１６進数は:". dechex ($b) . "<br>\n");
            $rgb = sprintf("%02x%02x%02x",$r,$g,$b);
            print("<div class='caption_font'>#{$rgb}</div>\n");
        ?>
        <table width ='90%' bgcolor='#<?php echo $rgb ?>' border='1' cellspacing='0' cellpadding='1'>
            <tr><td>
        <table width ='100%' bgcolor='#<?php echo $rgb ?>' border='1'>
            <tr><td width ='100%' height='100'></td></tr>
        </table>
            </td></tr>
        </table>
        <hr>
        <form action='<?= $menu->out_RetUrl() ?>' method='post'>
            <input type='hidden' name='r' value='<?php echo $r ?>'>
            <input type='hidden' name='g' value='<?php echo $g ?>'>
            <input type='hidden' name='b' value='<?php echo $b ?>'>
            <input type="submit" name="input" value="戻る" class='ret_font'>
        </form>
    </center>
</body>
</html>
