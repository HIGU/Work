<?php
//////////////////////////////////////////////////////////////////////////////
// ＤＢテーブルの詳細表示(psqlの\d) ＨＴＭＬ生成 Window Active Check 対応   //
// Copyright (C) 2010 Norihisa.Ohya norihisa_ooya@nitto-kohki.co.jp         //
// HTMLのTITLE(タイトル)名を変更して使用する プログラム登録画面用           //
// Changed history                                                          //
// 2010/01/26 Created  progMaster_input_db_detail.php                       //
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

///// パラメーター取得
if (isset($_REQUEST['db1'])) {
    $db1 = $_REQUEST['db1'];
}
if (isset($_REQUEST['db2'])) {
    $db2 = $_REQUEST['db2'];
}
if (isset($_REQUEST['db3'])) {
    $db3 = $_REQUEST['db3'];
}
if (isset($_REQUEST['db4'])) {
    $db4 = $_REQUEST['db4'];
}
if (isset($_REQUEST['db5'])) {
    $db5 = $_REQUEST['db5'];
}
if (isset($_REQUEST['db6'])) {
    $db6 = $_REQUEST['db6'];
}
if (isset($_REQUEST['db7'])) {
    $db7 = $_REQUEST['db7'];
}
if (isset($_REQUEST['db8'])) {
    $db8 = $_REQUEST['db8'];
}
if (isset($_REQUEST['db9'])) {
    $db9 = $_REQUEST['db9'];
}
if (isset($_REQUEST['db10'])) {
    $db10 = $_REQUEST['db10'];
}
if (isset($_REQUEST['db11'])) {
    $db11 = $_REQUEST['db11'];
}
if (isset($_REQUEST['db12'])) {
    $db12 = $_REQUEST['db12'];
}

if ($db2 == '') {
    $db_search = "WHERE table_name = '{$db1}'";
} elseif ($db3 == '') {
    $db_search = "WHERE (table_name = '{$db1}') OR (table_name = '{$db2}')";
} elseif ($db4 == '') {
    $db_search = "WHERE (table_name = '{$db1}') OR (table_name = '{$db2}') OR (table_name = '{$db3}')";
} elseif ($db5 == '') {
    $db_search = "WHERE (table_name = '{$db1}') OR (table_name = '{$db2}') OR (table_name = '{$db3}') OR (table_name = '{$db4}')";
} elseif ($db6 == '') {
    $db_search = "WHERE (table_name = '{$db1}') OR (table_name = '{$db2}') OR (table_name = '{$db3}') OR (table_name = '{$db4}') OR (table_name = '{$db5}')";
} elseif ($db7 == '') {
    $db_search = "WHERE (table_name = '{$db1}') OR (table_name = '{$db2}') OR (table_name = '{$db3}') OR (table_name = '{$db4}') OR (table_name = '{$db5}') OR (table_name = '{$db6}')";
} elseif ($db8 == '') {
    $db_search = "WHERE (table_name = '{$db1}') OR (table_name = '{$db2}') OR (table_name = '{$db3}') OR (table_name = '{$db4}') OR (table_name = '{$db5}') OR (table_name = '{$db6}') OR (table_name = '{$db7}')";
} elseif ($db9 == '') {
    $db_search = "WHERE (table_name = '{$db1}') OR (table_name = '{$db2}') OR (table_name = '{$db3}') OR (table_name = '{$db4}') OR (table_name = '{$db5}') OR (table_name = '{$db6}') OR (table_name = '{$db7}') OR (table_name = '{$db8}')";
} elseif ($db10 == '') {
    $db_search = "WHERE (table_name = '{$db1}') OR (table_name = '{$db2}') OR (table_name = '{$db3}') OR (table_name = '{$db4}') OR (table_name = '{$db5}') OR (table_name = '{$db6}') OR (table_name = '{$db7}') OR (table_name = '{$db8}') OR (table_name = '{$db9}')";
} elseif ($db11 == '') {
    $db_search = "WHERE (table_name = '{$db1}') OR (table_name = '{$db2}') OR (table_name = '{$db3}') OR (table_name = '{$db4}') OR (table_name = '{$db5}') OR (table_name = '{$db6}') OR (table_name = '{$db7}') OR (table_name = '{$db8}') OR (table_name = '{$db9}') OR (table_name = '{$db10}')";
} elseif ($db12 == '') {
    $db_search = "WHERE (table_name = '{$db1}') OR (table_name = '{$db2}') OR (table_name = '{$db3}') OR (table_name = '{$db4}') OR (table_name = '{$db5}') OR (table_name = '{$db6}') OR (table_name = '{$db7}') OR (table_name = '{$db8}') OR (table_name = '{$db9}') OR (table_name = '{$db10}') OR (table_name = '{$db11}')";
} else {
    $db_search = "WHERE (table_name = '{$db1}') OR (table_name = '{$db2}') OR (table_name = '{$db3}') OR (table_name = '{$db4}') OR (table_name = '{$db5}') OR (table_name = '{$db6}') OR (table_name = '{$db7}') OR (table_name = '{$db8}') OR (table_name = '{$db9}') OR (table_name = '{$db10}') OR (table_name = '{$db11}') OR (table_name = '{$db12}')";
}

$query = 'SELECT db_name, table_name AS _テーブル名_, table_comment AS テーブル説明 FROM db_table_info '. $db_search;

header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");               // 日付が過去
header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");  // 常に修正されている
header("Cache-Control: no-store, no-cache, must-revalidate");   // HTTP/1.1
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");                                     // HTTP/1.0
?>
<!DOCTYPE html>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<meta http-equiv="Content-Style-Type" content="text/css">
<title>ＤＢのテーブル詳細表示</title>
<style type='text/css'>
<!--
textarea {
    background-color:black;
    color:white;
}
td.gb {
    background-color:   #d6d3ce;
    color:              black;
}
.white {
    color:              white;
}
.pt6 {
    font-size:      6pt;
    font-weight:    normal;
}
.pt7 {
    font-size:      7pt;
    font-weight:    normal;
}
.pt8 {
    font-size:      8pt;
    font-weight:    normal;
}
.pt9 {
    font-size:      9pt;
    font-weight:    normal;
    font-family:    monospace;
}
.pt10 {
    font-size:      10pt;
    font-weight:    normal;
    font-family:    monospace;
}
.pt11b {
    font-size:      11pt;
    font-weight:    bold;
    font-family:    monospace;
}
.t_border {
    border-collapse: collapse;
}
.select_font {
    font-size:      10pt;
    font-weight:    bold;
    width:          100px;
}
a {
    color:              blue;
    text-decoration:    none;
}
a:hover {
    background-color:   yellow;
    font-weight:        bold;
}
a:active {
    background-color:   gold;
    color:              black;
}
th {
    background-color:       yellow;
    color:                  blue;
    border-style:           solid;
    border-width:           1px;
    border-top-color:       #FFFFFF;
    border-left-color:      #FFFFFF;
    border-right-color:     #bdaa90;
    border-bottom-color:    #bdaa90;
    font-size:              11pt;
}
td {
    border-style:           solid;
    border-width:           1px;
    border-top-color:       #FFFFFF;
    border-left-color:      #FFFFFF;
    border-right-color:     #bdaa90;
    border-bottom-color:    #bdaa90;
    font-size:              12pt;
}
-->
</style>
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
    <?php
                    $field = array(
                        "_テーブル名_",
                        "テーブル説明"
                    );
                    $res   = array(
                        ['_テーブル名_'=>"db1", "dummy database"],
                        ['_テーブル名_'=>"db2", "dummy database"],
                        ['_テーブル名_'=>"db3", "dummy database"],
                        ['_テーブル名_'=>"db4", "dummy database"],
                        ['_テーブル名_'=>"db5", "dummy database"],
                        ['_テーブル名_'=>"db6", "dummy database"],
                        ['_テーブル名_'=>"db7", "dummy database"],
                        ['_テーブル名_'=>"db8", "dummy database"],
                        ['_テーブル名_'=>"db9", "dummy database"],
                        ['_テーブル名_'=>"db10", "dummy database"],
                    );
                    $rows= count($res);
                    //if ( ($rows=getResultWithField($query,$field,$res)) >= 0) {
                    echo "<center>\n";
                    echo "<tr align='center'><td style='border-width:0px;'><table bgcolor='black' border='1' cellspacing='1' cellpadding='1'></tr>\n";
                            ///// $num に フィールド数を入れる
                    $num = count($field);
                    for ($n=0; $n<$num; $n++) {
                        if ($n == 0) {
                            echo "<th nowrap>No</th>\n";
                        }
                        echo "<th nowrap>{$field[$n]}</th>\n";
                    }
                    for ($r=0; $r<$rows; $r++) {
                        echo "<tr>\n";
                        echo "<td class='gb' nowrap align='right'>", ($r+1), "</td>\n";
                        echo "<td class='gb' nowrap>{$res[$r]['_テーブル名_']}</td>\n";
                        echo "<td class='gb' nowrap>{$res[$r][0]}</td>\n";
                        echo "</tr>\n";
                    }
                    echo "</tr></table></td></tr>";
    ?>
    </pre>
</body>
</html>
<?php ob_end_flush(); // 出力バッファをgzip圧縮 END ?>
