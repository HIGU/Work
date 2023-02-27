<?php
//////////////////////////////////////////////////////////////////////////////
// System status view(システム状況表示)                                     //
// Copyright(C) 2005-2005 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp       //
// Changed history                                                          //
// 2005/03/03 Created   top.chk.php                                         //
//////////////////////////////////////////////////////////////////////////////
ini_set('error_reporting', E_ALL);          // E_ALL='2047' debug 用
// ini_set('display_errors', '1');             // Error 表示 ON debug 用 リリース後コメント
// ob_start('ob_gzhandler');                   // 出力バッファをgzip圧縮
session_start();                            // ini_set()の次に指定すること Script 最上行
require_once ('../../function.php');
require_once ('../../MenuHeader.php');      // TNK 全共通 menu class
access_log();                               // Script Name 自動設定

///// TNK 共用メニュークラスのインスタンスを作成
$menu = new MenuHeader(3);                  // 認証チェック0=一般以上 戻り先=TOP_MENU タイトル未設定
// 権限 0 1 2 NG 3のアドミニのみ使用可能
////////////// サイト設定
$menu->set_site(99, 52);                    // site_index=40(システムメニュー) site_id=52(top)
////////////// リターンアドレス設定
// $menu->set_RetUrl(SYS_MENU);                // 通常は指定する必要はない
//////////// タイトル名(ソースのタイトル名とフォームのタイトル名)
$menu->set_title('System status view');
//////////// 表題の設定
$menu->set_caption('システム状況表示 リソース使用量順');
//////////// 呼出先のaction名とアドレス設定
// $menu->set_action('test_template',   SYS . 'log_view/php_error_log.php');

//////////// Iframe File を必ず読み込ませる
$uniq = uniqid('target');

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
.pt11b {
    font-size:      11pt;
    font-weight:    bold;
    font-family:    monospace;
}
pre {
    color:          black;
    font-size:      12pt;
    font-weight:    bold;
    font-family:    monospace;
    /* text-decoration:underline; */
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
<body onLoad='set_focus()' style='overflow-y:hidden; background-color:#ffffc6;'>
    <center>
<?= $menu->out_title_border() ?>
        <table border='0' cellspacing='0' cellpadding='3'>
            <tr>
                <td align='center' class='caption_font'><?=$menu->out_caption()?></td>
            </tr>
            <tr>
                <td style='background-color:#d6d3ce;'>
                    <iframe hspace='0' vspace='0' scrolling='yes' src='top_chk_iframe.php?name=<?=$uniq?>' name='top_chk' align='center' width='760' height='590' title='top_check'>
                        システム状況（CPU使用状況等）を表示します。
                    </iframe>
                </td>
            </tr>
        </table>
    </center>
</body>
</html>
