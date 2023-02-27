<?php
//////////////////////////////////////////////////////////////////////////////
// フリーメモリーチェック(おまけ的なもの)                                   //
// Copyright(C) 2001-2006 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp       //
// Changed history                                                          //
// 2001/10/01 Created  free.chk.php                                         //
// 2002/12/03 サイトメニューに入れたため access_log と権限の追加            //
// 2002/12/27 php-4.3.0 で leak() が使えないので削除した。                  //
// 2005/01/28 MenuHeader class を使用して共通メニュー化及び認証方式へ変更   //
// 2006/04/20 テスト時の確認用にセッションＩＤを表示追加                    //
//////////////////////////////////////////////////////////////////////////////
ini_set('error_reporting', E_ALL);          // E_ALL='2047' debug 用
// ini_set('display_errors', '1');             // Error 表示 ON debug 用 リリース後コメント
session_start();                            // ini_set()の次に指定すること Script 最上行
require_once ('../../function.php');
require_once ('../../MenuHeader.php');      // TNK 全共通 menu class
access_log();                               // Script Name 自動設定

///// TNK 共用メニュークラスのインスタンスを作成
$menu = new MenuHeader(3);                  // 認証チェック0=一般以上 戻り先=TOP_MENU タイトル未設定
// 権限 0 1 2 NG 3のアドミニのみ使用可能
////////////// サイト設定
$menu->set_site(99, 50);                    // site_index=40(システムメニュー) site_id=50(フリーメモリ)
////////////// リターンアドレス設定
// $menu->set_RetUrl(SYS_MENU);                // 通常は指定する必要はない
//////////// タイトル名(ソースのタイトル名とフォームのタイトル名)
$menu->set_title('システム メモリー チェック');
//////////// 表題の設定
$menu->set_caption('現在のシステムのメモリ使用状態は');
//////////// 呼出先のaction名とアドレス設定
// $menu->set_action('test_template',   SYS . 'log_view/php_error_log.php');

$free_memory = `free -ot`;

/////////// HTML Header を出力してキャッシュを制御
$menu->out_html_header();
?>
<!DOCTYPE html>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<meta http-equiv="Content-Style-Type" content="text/css">
<title><?= $menu->out_title() ?></title>
<?= $menu->out_site_java() ?>
<?= $menu->out_css() ?>

<style type='text/css'>
<!--
.pt11b {
    font-size:      11pt;
    font-weight:    bold;
    font-family:    monospace;
}
.margin1 {
    margin: 1%;
}
pre {
    color:          black;
    font-size:      12pt;
    font-weight:    bold;
    font-family:    monospace;
    text-decoration:underline;
}
-->
</style>
<script language='JavaScript'>
<!--
/* 初期入力フォームのエレメントにフォーカスさせる */
function set_focus() {
    // document.body.focus();   // F2/F12キーを有効化する対応
    document.mhForm.backwardStack.focus();  // 上記はIEのみのためNN対応
    // document.form_name.element_name.focus();      // 初期入力フォームがある場合はコメントを外す
    // document.form_name.element_name.select();
}
// -->
</script>
</head>
<body onLoad='set_focus()' style='overflow:hidden;'>
    <center>
<?= $menu->out_title_border() ?>
        <table border='0' cellspacing='0' cellpadding='10'>
            <tr>
                <td class='caption_font'><?=$menu->out_caption()?></td>
            </tr>
            <tr>
                <td>
                <pre>
<?php
                    echo "{$free_memory}\n";
                    echo session_id();
?>
                </pre>
                </td>
            </tr>
        </table>
    </center>
</body>
</html>
