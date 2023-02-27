<?php
//////////////////////////////////////////////////////////////////////////////
// System status view(システム状況表示)                                     //
// Copyright(C) 2005-2005 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp       //
// Changed history                                                          //
// 2005/03/03 Created   top.chk_iframe.php                                  //
//////////////////////////////////////////////////////////////////////////////
ini_set('error_reporting', E_ALL);          // E_ALL='2047' debug 用
// ini_set('display_errors', '1');             // Error 表示 ON debug 用 リリース後コメント
session_start();                            // ini_set()の次に指定すること Script 最上行
require_once ('../../function.php');
access_log();                               // Script Name 自動設定

$top_status = `top -n1 -b`;
$top_status = preg_replace("/^\n\n/", '', $top_status, 1);

?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<meta http-equiv="Content-Style-Type" content="text/css">
<title></title>

<style type='text/css'>
<!--
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
    // document.form_name.element_name.focus();      // 初期入力フォームがある場合はコメントを外す
    // document.form_name.element_name.select();
}
// -->
</script>
</head>
<body style='margin:0%; background-color:#d6d3ce;'>
    <center>
        <table align='left' border='0' cellspacing='0' cellpadding='0'>
            <tr>
                <td>
                    <pre>
<?=$top_status?>
                    </pre>
                </td>
            </tr>
        </table>
    </center>
</body>
</html>
