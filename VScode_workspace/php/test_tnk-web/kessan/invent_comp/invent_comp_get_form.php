<?php
//////////////////////////////////////////////////////////////////////////////
// 月次 比較棚卸表のデータ取込みフォーム                                    //
// Copyright (C) 2003 2007 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp      //
// Changed history                                                          //
// 2003/09/27 Created   act_comp_invent_get.php                             //
// if ( (include ('invent_comp_get.php')) == TRUE) 括弧でincludeを優先要    //
// 2007/10/10 MenuHeaderクラスへ変更 ショートを標準へout_alert_java(false)へ//
//////////////////////////////////////////////////////////////////////////////
// ini_set('error_reporting', E_ALL);          // E_ALL='2047' debug 用
// ini_set('display_errors', '1');             // Error 表示 ON debug 用 リリース後コメント
// ini_set('implicit_flush', 'off');           // echo print で flush させない(遅くなるため) CLI CGI版
// ini_set('max_execution_time', 1200);        // 最大実行時間=20分 CLI CGI版
ob_start('ob_gzhandler');                   // 出力バッファをgzip圧縮
session_start();                            // ini_set()の次に指定すること Script 最上行
require_once ('../../function.php');        // define.php と pgsql.php を require_once している
require_once ('../../tnk_func.php');        // TNK に依存する部分の関数を require_once している
require_once ('../../MenuHeader.php');      // TNK 全共通 menu class
access_log();                               // Script Name は自動取得

///// TNK 共用メニュークラスのインスタンスを作成
$menu = new MenuHeader(0);                  // 認証チェック0=一般以上 戻り先=TOP_MENU タイトル未設定

////////////// サイト設定
$menu->set_site(INDEX_PL, 7);               // site_index=10(損益メニュー) site_id=7(損益作成メニュー)
//////////// タイトル名(ソースのタイトル名とフォームのタイトル名)
$menu->set_title('月次 比較棚卸表のデータ取込み');
//////////// 表題の設定
$menu->set_caption('月次の年月を指定して下さい。');
//////////// 呼出先のaction名とアドレス設定
//$menu->set_action('取込実行',   PL . 'invent_comp/invent_comp_get.php');

//////////// ブラウザーのキャッシュ対策用
$uniq = $menu->set_useNotCache('invComp');

/////////// HTML Header を出力してキャッシュを制御
$menu->out_html_header();
?>
<!DOCTYPE html>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<meta http-equiv="Content-Style-Type" content="text/css">
<meta http-equiv="Content-Script-Type" content="text/javascript">
<title><?php echo $menu->out_title() ?></title>
<?php echo $menu->out_site_java() ?>
<?php echo $menu->out_css() ?>

<!--    ファイル指定の場合
<script type='text/javascript' language='JavaScript' src='template.js?<?php echo $uniq ?>'>
</script>
-->

<script type='text/javascript' language='JavaScript'>
<!--
/* 入力文字が数字かどうかチェック */
function isDigit(str) {
    var len=str.length;
    var c;
    for (i=1; i<len; i++) {
        c = str.charAt(i);
        if ((c < "0") || (c > "9")) {
            return true;
        }
    }
    return false;
}
/* 初期入力フォームのエレメントにフォーカスさせる */
function set_focus(){
    document.page_form.yyyymm.focus();      // 初期入力フォームがある場合はコメントを外す
    document.page_form.yyyymm.select();
}
// -->
</script>

<!-- スタイルシートのファイル指定をコメント HTMLタグ コメントは入れ子に出来ない事に注意
<link rel='stylesheet' href='template.css?<?php echo $uniq ?>' type='text/css' media='screen'>
-->

<style type='text/css'>
<!--
.pt8 {
    font:normal 8pt;
    font-family: monospace;
}
.pt10 {
    font:normal 10pt;
    font-family: monospace;
}
.pt10b {
    font:bold 10pt;
    font-family: monospace;
}
.pt11b {
    font:bold 11pt;
}
.pt12b {
    font:bold 12pt;
    font-family: monospace;
}
.title_font {
    font:bold 13.5pt;
    font-family: monospace;
}
.today_font {
    font-size: 10.5pt;
    font-family: monospace;
}
.corporate_name {
    font:bold 10pt;
    font-family: monospace;
}
.margin0 {
    margin:0%;
}
th {
    background-color:yellow;
    color:blue;
    font:bold 11pt;
    font-family: monospace;
}
body {
    overflow-y:             hidden;
    background-image:       url(/img/t_nitto_logo4.png);
    background-repeat:      no-repeat;
    background-attachment:  fixed;
    background-position:    right bottom;
}
-->
</style>
</head>
<body onLoad='set_focus()'>
    <center>
<?php echo $menu->out_title_border() ?>
        <br>
        <br>
        <!----------------- ここは 前頁 次頁 のフォーム ---------------->
        <table width='100%' cellspacing="0" cellpadding="0" border='0'>
            <form name='page_form' method='post' action='<?php echo $menu->out_self() ?>'>
                <tr>
                    <td align='left'>
                        <table align='left' border='3' cellspacing='0' cellpadding='0'>
                            <td align='left'>
                                <input class='pt10b' type='submit' name='backward' value='年月-'>
                            </td>
                        </table>
                    </td>
                    <td align='center' class='pt11b'>
                        <?php echo "{$menu->out_caption()}\n" ?>
                    </td>
                    <td align='right'>
                        <table align='right' border='3' cellspacing='0' cellpadding='0'>
                            <td align='right'>
                                <input class='pt10b' type='submit' name='forward' value='年月+'>
                            </td>
                        </table>
                    </td>
                </tr>
                <tr>
                    <td colspan='3' align='center' class='pt10'>
                        <input type='text' name='yyyymm' size='8' value='<?php echo $yyyymm ?>' maxlength='6'>
                        <br>例：200309 （2003年09月）
                    </td>
                </tr>
                <tr>
                    <td colspan='3' align='center' class='pt10'>
                        <br>
                        <table align='center' border='3' cellspacing='0' cellpadding='0'>
                            <td align='center'>
                                <input class='pt10b' type='submit' name='getInvent' value='実行'>
                            </td>
                        </table>
                    </td>
                </tr>
            </form>
        </table>
    </center>
</body>
<?php echo $menu->out_alert_java(false)?>
</html>
<?php
ob_end_flush();                 // 出力バッファをgzip圧縮 END
?>
