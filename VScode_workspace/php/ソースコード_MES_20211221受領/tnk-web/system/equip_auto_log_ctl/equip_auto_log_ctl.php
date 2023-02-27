<?php
//////////////////////////////////////////////////////////////////////////////
// 設備稼働管理の自動ログ収集をコントロールするため管理メニューに追加       //
// Copyright (C) 2007       Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp     //
// Changed history                                                          //
// 2007/06/15 Created  equip_auto_log_ctl.php                               //
//////////////////////////////////////////////////////////////////////////////
// ini_set('error_reporting', E_STRICT);       // E_STRICT=2048(php5) E_ALL=2047 debug 用
ini_set('error_reporting', E_ALL);          // E_STRICT=2048(php5) E_ALL=2047 debug 用
ini_set('display_errors', '1');             // Error 表示 ON debug 用 リリース後コメント
// ini_set('zend.ze1_compatibility_mode', '1');    // zend 1.X コンパチ php4の互換モード
// ini_set('implicit_flush', '0');             // echo print で flush させない(遅くなるため) CLI版
    // 現在はCLI版のdefault='1', SAPI版のdefault='0'になっている。CLI版のみスクリプトから変更出来る。
// ini_set('max_execution_time', 1200);        // 最大実行時間=20分 WEB CGI版
ob_start('ob_gzhandler');                   // 出力バッファをgzip圧縮
session_start();                            // ini_set()の次に指定すること Script 最上行

require_once ('../../function.php');        // define.php と pgsql.php を require_once している
require_once ('../../tnk_func.php');        // TNK に依存する部分の関数を require_once している
require_once ('../../MenuHeader.php');      // TNK 全共通 menu class
require_once ('../../ControllerHTTP_Class.php');    // TNK 全共通 MVC Controller Class
access_log();                               // Script Name は自動取得

///// TNK 共用メニュークラスのインスタンスを作成
$menu = new MenuHeader(0);                  // 認証チェック0=一般以上 戻り先=TOP_MENU タイトル未設定

////////////// サイト設定
$menu->set_site(99, 999);                   // site_index=99(システムメニュー) site_id=999(設定無し)
////////////// リターンアドレス設定(絶対指定する場合)
// $menu->set_RetUrl(SYS_MENU);                // 通常は指定する必要はない
//////////// タイトル名(ソースのタイトル名とフォームのタイトル名)
$menu->set_title('設備稼働管理の自動ログ収集の管理');
//////////// 表題の設定
$menu->set_caption('現在の自動ログ収集状況');
//////////// 呼出先のaction名とアドレス設定
// $menu->set_action('test_template',   SYS . 'log_view/php_error_log.php');

//////////// ブラウザーのキャッシュ対策用
$uniq = $menu->set_useNotCache('target');

//////////// リクエストのインスタンス作成
$request = new Request();

//////////// コントロールファイルの指定
$check_file    = '/home/www/html/tnk-web/equip/check_file';
$auto_log_stop = '/home/www/html/tnk-web/equip/equip_auto_log_stop';

//////////// 自動ログ開始・停止のリクエスト取得
if ($request->get('logStart') == 'yes') {
    unlink($auto_log_stop);
}
if ($request->get('logStop') == 'yes') {
    fopen($auto_log_stop, 'a');
}

//////////// 現在の状況取得
if (file_exists($check_file)) {
    $status = "<span style='color:blue;'>現在ログ収集を実行中です。</span>";
} elseif (file_exists($auto_log_stop)) {
    $status = "現在ログ収集は停止中です。";
} else {
    $status = "<span style='color:red;'>現在ログ収集の待機中です。</span>";
}
//////////// メニュー用の停止指示のフラグを取得
if (file_exists($auto_log_stop)) {
    $logStart = '';
    $logStop  = ' disabled';
} else {
    $logStart = ' disabled';
    $logStop  = '';
}

/////////// HTML Header を出力してキャッシュを制御
$menu->out_html_header();
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=EUC-JP">
<meta http-equiv="Content-Style-Type" content="text/css">
<meta http-equiv="Content-Script-Type" content="text/javascript">
<!-- <meta http-equiv="Refresh" content="5;URL=<?php echo $menu->out_self() . "?{$uniq}" ?>"> -->
<title><?php echo $menu->out_title() ?></title>
<?php echo $menu->out_site_java() ?>
<?php echo $menu->out_css() ?>

<!-- JavaScriptのファイル指定をbodyの最後にする。 HTMLタグのコメントは入れ子に出来ない事に注意  
<script type='text/javascript' src='template.js?<?php echo $uniq ?>'></script>
-->

<!-- スタイルシートのファイル指定をコメント HTMLタグのコメントは入れ子に出来ない事に注意  
<link rel='stylesheet' href='template.css?<?php echo $uniq ?>' type='text/css' media='screen'>
-->

<link rel='shortcut icon' href='/favicon.ico?=<?php echo $uniq ?>'>

<style type='text/css'>
<!--
body {
    /* ここにbodyを指定すれば HTMLタグは必要ない */
    background-image:       url(/img/t_nitto_logo4.png);
    background-repeat:      no-repeat;
    background-attachment:  fixed;
    background-position:    right bottom;
    overflow-y:             hidden;'
}
-->
</style>
</head>

<body onLoad='setInterval("location.replace(\"<?php echo $menu->out_self() ?>\")", 5000)'>
    <center>
<?php echo $menu->out_title_border() ?>
        <!--
            <div style='position: absolute; top: 80; left: 7; width: 185; height: 31'>
                絶対値で位置指定
            </div>
        -->
        
        <table width='100%' cellspacing='0' cellpadding='0' border='0'>
            <tr>
                <td align='center' class='caption_font' id='caption'>
                    <?php echo $menu->out_caption() . "\n" ?>
                </td>
            </tr>
        </table>
        <br>
        <br>
        <div><?php echo $status ?></div>
        <br>
        <br>
        
        <!--------------- ここから本文の表を表示する -------------------->
        <table bgcolor='#d6d3ce' align='center' border='1' cellspacing='0' cellpadding='3'>
            <tr><td> <!----------- ダミー(デザイン用) ------------>
        <table width='100%'class='winbox_field' bgcolor='#d6d3ce' align='center' border='1' cellspacing='0' cellpadding='3'>
            <tr>
                <td class='winbox'>
                    <input type='button' name='logStart' value='自動ログ開始' onClick='location.replace("<?php echo $menu->out_self()?>?logStart=yes&<?php echo $uniq ?>");'<?php echo $logStart ?>>
                </td>
                <td class='winbox'>
                    <input type='button' name='logStop'  value='自動ログ停止' onClick='location.replace("<?php echo $menu->out_self()?>?logStop=yes&<?php echo $uniq ?>");'<?php echo $logStop ?>>
                </td>
            </tr>
        </table>
            </td></tr>
        </table> <!----------------- ダミーEnd ------------------>
    </center>
</body>
<?php echo $menu->out_alert_java()?>
</html>
<?php
ob_end_flush();                 // 出力バッファをgzip圧縮 END
?>
