<?php
//////////////////////////////////////////////////////////////////////////////
// ＤＢテーブルの詳細表示(psqlの\d) ＨＴＭＬ生成 Window Active Check 対応   //
// Copyright (C) 2004-2005 2007 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp //
// HTMLのTITLE(タイトル)名を変更して使用する                                //
// Changed history                                                          //
// 2004/10/18 Created  table_detail.php                                     //
// 2004/11/29 winActiveChk() のタイマーを解除                               //
// 2005/09/26 DBをmasterdbへ分離したため psql -h $db_host のオプションを追加//
// 2007/06/06 テーブル説明とカラム説明の表示を追加 [\dt+] [\d+]             //
//////////////////////////////////////////////////////////////////////////////
ini_set('error_reporting', E_STRICT);       // E_STRICT=2048(php5) E_ALL=2047 debug 用
// ini_set('error_reporting', E_ALL);          // E_ALL='2047' debug 用
ini_set('display_errors', '1');             // Error 表示 ON debug 用 リリース後コメント
// ini_set('implicit_flush', 'off');           // echo print で flush させない(遅くなるため) CLI CGI版
// ini_set('max_execution_time', 1200);        // 最大実行時間=20分 CLI CGI版
ob_start('ob_gzhandler');                   // 出力バッファをgzip圧縮
session_start();                            // ini_set()の次に指定すること Script 最上行
require_once ('../../function.php');        // define.php と pgsql.php を require_once している
access_log();                               // Script Name は自動取得

///// GET/POSTのチェック&設定
if (isset($_REQUEST['table'])) {
    $table = $_REQUEST['table'];
    $db_host = DB_HOST;
    $table_detail  = `/usr/local/pgsql/bin/psql -h $db_host -c '\dt+ $table' TnkSQL`;
    $table_detail .= `/usr/local/pgsql/bin/psql -h $db_host -c '\d+ $table' TnkSQL`;
} else {
    $table = '';
    $table_detail = '';
}

header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");               // 日付が過去
header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");  // 常に修正されている
header("Cache-Control: no-store, no-cache, must-revalidate");   // HTTP/1.1
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");                                     // HTTP/1.0
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<meta http-equiv="Content-Style-Type" content="text/css">
<title>ＤＢのテーブル詳細表示</title>
<script language='JavaScript'>
function winActiveChk() {
    if (document.all) {     // IEなら
        if (document.hasFocus() == false) {     // IE5.5以上で使える
            window.focus();
            return;
        }
        return;
    } else {                // NN ならとワリキッテ
        window.focus();
        return;
    }
    // 使用法：<body onLoad="setInterval('winActiveChk()',100)">
}
</script>
</head>
<body style='margin:1%;' onLoad='winActiveChk()'>
    <center>
        <input type='button' name='closeButton' style='font-size:1.0em;font-weight:bold;' value='Close' onclick='window.close();'>
    </center>
    <pre onClick='/*window.close()*/'>
<?php echo $table_detail ?>
    </pre>
</body>
</html>
<?php ob_end_flush(); // 出力バッファをgzip圧縮 END ?>
