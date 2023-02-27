<?php
//////////////////////////////////////////////////////////////////////////////
// 検査中リスト(検査保留・再開処理)の操作説明  ヘルプ表示                   //
// Copyright (C) 2007      Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp      //
// Changed history                                                          //
// 2007/01/19 Created  inspectingList_help.php                              //
//////////////////////////////////////////////////////////////////////////////
ini_set('error_reporting', E_ALL);          // E_ALL='2047' debug 用
// ini_set('display_errors', '1');             // Error 表示 ON debug 用 リリース後コメント
ob_start('ob_gzhandler');                   // 出力バッファをgzip圧縮
session_start();                            // ini_set()の次に指定すること Script 最上行
require_once ('../../function.php');        // TNK 全共通 function
access_log();                               // Script Name は自動取得
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<meta http-equiv="Content-Style-Type" content="text/css">
<title>検査中リスト操作説明ＨＥＬＰ</title>
<script language='JavaScript'>
<!--
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
    // <input type='button' value='TEST' onClick="window.opener.location.reload()">
    // parent.Header.関数名() or オブジェクト;
}
// -->
</script>
</head>
<body style='margin:0%;' onLoad="setInterval('winActiveChk()',100)">
    <center>
        <input type='image' alt='検査中リスト(検査中断・再開処理) 操作説明' border='0' src='inspectingList_help.png' onClick='window.close()'>
    </center>
</body>
</html>
<?php ob_end_flush(); // 出力バッファをgzip圧縮 END ?>
