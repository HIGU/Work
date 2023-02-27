<?php
//////////////////////////////////////////////////////////////////////////////
// 納入予定グラフ・検査仕掛明細の照会(検査の仕事量把握)  ヘルプ表示         //
// Copyright (C) 2004-2004 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp      //
// Changed history                                                          //
// 2004/11/01 Created  order_help.php                                       //
//////////////////////////////////////////////////////////////////////////////
ini_set('error_reporting', E_ALL);          // E_ALL='2047' debug 用
// ini_set('display_errors', '1');             // Error 表示 ON debug 用 リリース後コメント
ob_start('ob_gzhandler');                   // 出力バッファをgzip圧縮
session_start();                            // ini_set()の次に指定すること Script 最上行
require_once ('../../function.php');        // TNK 全共通 function
access_log();                               // Script Name は自動取得
?>
<!DOCTYPE html>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<meta http-equiv="Content-Style-Type" content="text/css">
<title>検査仕掛 照会画面ＨＥＬＰ</title>
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
        <?php
        if ($_SESSION['select'] == 'miken') {
            echo "        <input type='image' alt='検査仕掛 照会画面ＨＥＬＰ' border='0' src='order_help.png' onClick='window.close()'>\n";
        } else {
            echo "        <input type='image' alt='納入予定グラフの画面ＨＥＬＰ' border='0' src='order_graph_help.png' onClick='window.close()'>\n";
        }
        ?>
    </center>
</body>
</html>
<?php ob_end_flush(); // 出力バッファをgzip圧縮 END ?>
