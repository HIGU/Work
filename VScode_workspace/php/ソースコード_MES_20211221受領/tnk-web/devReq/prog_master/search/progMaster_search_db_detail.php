<?php
//////////////////////////////////////////////////////////////////////////////
// �ģ¥ơ��֥�ξܺ�ɽ��(psql��\d) �ȣԣͣ����� Window Active Check �б�   //
// Copyright (C) 2010 Norihisa.Ohya norihisa_ooya@nitto-kohki.co.jp         //
// HTML��TITLE(�����ȥ�)̾���ѹ����ƻ��Ѥ��� �ץ���ม��������           //
// Changed history                                                          //
// 2010/01/26 Created  progMaster_search_db_detail.php                      //
//////////////////////////////////////////////////////////////////////////////
ini_set('error_reporting', E_STRICT);       // E_STRICT=2048(php5) E_ALL=2047 debug ��
// ini_set('error_reporting', E_ALL);          // E_ALL='2047' debug ��
ini_set('display_errors', '1');             // Error ɽ�� ON debug �� ��꡼���女����
// ini_set('implicit_flush', 'off');           // echo print �� flush �����ʤ�(�٤��ʤ뤿��) CLI CGI��
// ini_set('max_execution_time', 1200);        // ����¹Ի���=20ʬ CLI CGI��
ob_start('ob_gzhandler');                   // ���ϥХåե���gzip����
session_start();                            // ini_set()�μ��˻��ꤹ�뤳�� Script �Ǿ��
require_once ('../../../function.php');        // define.php �� pgsql.php �� require_once ���Ƥ���
access_log();                               // Script Name �ϼ�ư����

///// �ѥ�᡼��������
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
if (isset($_REQUEST['key'])) {
    $key = $_REQUEST['key'];
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

$query = 'SELECT db_name, table_name AS _�ơ��֥�̾_, table_comment AS �ơ��֥����� FROM db_table_info '. $db_search;

header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");               // ���դ����
header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");  // ��˽�������Ƥ���
header("Cache-Control: no-store, no-cache, must-revalidate");   // HTTP/1.1
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");                                     // HTTP/1.0
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=EUC-JP">
<meta http-equiv="Content-Style-Type" content="text/css">
<title>�ģ¤Υơ��֥�ܺ�ɽ��</title>
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
    if (document.all) {     // IE�ʤ�
        if (document.hasFocus() == false) {     // IE5.5�ʾ�ǻȤ���
            window.focus();
            return;
        }
        return;
    } else {                // NN �ʤ�ȥ�ꥭ�å�
        window.focus();
        return;
    }
    // ����ˡ��<body onLoad="setInterval('winActiveChk()',100)">
}
</script>
</head>
<body style='margin:1%;' onLoad='winActiveChk()'>
    <center>
        <input type='button' name='closeButton' style='font-size:1.0em;font-weight:bold;' value='Close' onclick='window.close();'>
    </center>
    <pre onClick='/*window.close()*/'>
    <?php
    $field = array();
                    $res   = array();
                    if ( ($rows=getResultWithField($query,$field,$res)) >= 0) {
                    echo "<center>\n";
                    echo "<tr align='center'><td style='border-width:0px;'><table bgcolor='black' border='1' cellspacing='1' cellpadding='1'></tr>\n";
                            ///// $num �� �ե�����ɿ��������
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
                            for ($n=0; $n<$num; $n++) {
                                if ($res[$r][$n] == "") {
                                    echo "<td class='gb' nowrap align='center'>---</td>\n";
                                } else {
                                    if (isset($res[$r]['_�ơ��֥�̾_'])) {
                                        if ($key != '') {
                                            $p_id = $res[$r][$n];
                                            $div_id = $key;
                                            $p_id = ereg_replace($div_id, "<B>{$div_id}</B>", $p_id);
                                            echo "<td class='gb' nowrap>{$p_id}</td>\n";
                                        } else {
                                            echo "<td class='gb' nowrap>{$res[$r][$n]}</td>\n";
                                        }
                                    } elseif (isset($res[$r]['_�¹���_'])) {
                                        if ($res[$r][$n] == $res[$r]['_�¹���_']) {
                                            echo "<td class='gb'><a href='JavaScript:historyDelete(\"{$res[$r][$n]}\")' title='����å�����������Ǥ��ޤ���'>{$res[$r][$n]}</a></td>\n";
                                        } elseif ($res[$r][$n] == $res[$r]['SQL����']) {
                                            $res[$r][$n] = stripslashes($res[$r][$n]);  // ����addslashes()�ǽ������줿��Τ��б�(2007/12/22�����)
                                            $valueSQL = str_replace("\r\n", ' ', $res[$r][$n]); // JavaScript���Ϥ�����˻�Ժ�������̣��ʳ����Ѵ���ɬ��
                                            $valueSQL = addslashes($valueSQL);
                                            $valueSQL = htmlspecialchars($valueSQL, ENT_QUOTES);
                                            echo "<td class='gb'><a href='javascript:copySQL(\"{$valueSQL}\")' title='����å���SQL�����Ƥ򥳥ԡ����ޤ���'>{$res[$r][$n]}</a></td>\n";
                                        } else {
                                            echo "<td class='gb'>{$res[$r][$n]}</td>\n";
                                        }
                                    } else {
                                        echo "<td class='gb' nowrap>{$res[$r][$n]}</td>\n";
                                    }
                                }
                            }
                            echo "</tr>\n";
                        }
                        echo "</tr></table></td></tr>";
                    } else {
                        echo "<tr><td>�¹ԥ����꡼ <br>{$query}</td></tr>\n";
                    }
    ?>
    </pre>
</body>
</html>
<?php ob_end_flush(); // ���ϥХåե���gzip���� END ?>
