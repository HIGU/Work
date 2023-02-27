<?php
//////////////////////////////////////////////////////////////////////////////
// �����ƥ�����ѥǡ����١������� ����饤��ե졼����                      //
// Copyright (C) 2005 2007 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp      //
// Changed history                                                          //
// 2005/03/22 Created   system_db_iframe.php                                //
//            system_db.php����query�����ڤ�Υ��Iframe���ǽ���              //
// 2007/03/14 win_open()�� resizable=yes ���ɲ�                             //
// 2007/06/07 win_open()�Υ�����ɥ��������� 800X600 �� 980X400 ���ѹ�      //
// 2007/12/21 pg_escape_string()���б���stripslashes($res[$r][$n])���ɲ�    //
// 2007/12/22 �ǡ����١���������������뵡ǽ�ȥ��ԡ����뵡ǽ���ɲ�        //
//////////////////////////////////////////////////////////////////////////////
// ini_set('error_reporting', E_ALL);          // E_ALL='2047' debug ��
ini_set('display_errors', '1');             // Error ɽ�� ON debug �� ��꡼���女����
ini_set('track_errors', '1');               // Store the last error/warning message in $php_errormsg (boolean)
ob_start('ob_gzhandler');                   // ���ϥХåե���gzip����
session_start();                            // ini_set()�μ��˻��ꤹ�뤳�� Script �Ǿ��

require_once ('../../function.php');        // define.php �� pgsql.php �� require_once ���Ƥ���
require_once ('../../MenuHeader.php');      // TNK ������ menu class
access_log();                               // Script Name �ϼ�ư����

///// TNK ���ѥ�˥塼���饹�Υ��󥹥��󥹤����
$menu = new MenuHeader(3);                  // ǧ�ڥ����å�0=���̰ʾ� 3=admini�ʾ� �����=TOP_MENU �����ȥ�̤����

$menu->set_title('SQL����');

///// �ѥ�᡼��������
if (isset($_SESSION['userquery'])) {
    $userquery = $_SESSION['userquery'];
}
///// ����κ������
if (isset($_REQUEST['historyDelete'])) {
    $query = "
        DELETE FROM db_admin_history WHERE regdate = '{$_REQUEST['historyDelete']}'
    ";
    $rows = query_affected($query);
    $_SESSION['s_sysmsg'] = "{$_REQUEST['historyDelete']} ������� {$rows} �� ������ޤ�����";
}

////////// HTML Header ����Ϥ��ƥ֥饦�����Υ���å��������
$menu->out_html_header();
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=EUC-JP">
<meta http-equiv="Content-Style-Type" content="text/css">
<title><?=$menu->out_title()?></title>
<?=$menu->out_css()?>

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
<!--
function win_open(url)
{
    var w = 980;
    var h = 400;
    var left = (screen.availWidth  - w) / 2;
    var top  = (screen.availHeight - h) / 2;
    window.open(url, 'view_win', 'width='+w+',height='+h+',resizable=yes,scrollbars=yes,status=no,toolbar=no,location=no,menubar=no,top='+top+',left='+left);
}
function historyDelete(key)
{
    if (key == "") return;
    if (confirm(key + "\n\n������������ޤ�����")) {
        document.historyDeleteForm.historyDelete.value = key;
        document.historyDeleteForm.submit();
    } else {
        return;
    }
}
function copySQL(sql)
{
    if (sql == "") return;
    parent.window.document.ini_form.userquery.value = sql;
}
// -->
</script>
<form name='historyDeleteForm' action='<?php echo $menu->out_self()?>' method='post' target='_self'>
    <input type='hidden' name='historyDelete' value=''>
</form>
</head>
<body>
<center>
<?php
    if ($userquery != '') {
        echo "<table width='100%'>\n";
        $len = strlen($userquery);
        $query = "";
        for ($i=0; $i<$len; $i++) {
            $query .= substr($userquery, $i, 1);
            if (substr($query, strlen($query)-1, 1) == ";" || $i == $len-1) {
                if ($query) {
                    $field = array();
                    $res   = array();
                    if ( ($rows=getResultWithField($query,$field,$res)) >= 0) {
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
                                        if ($res[$r][$n] == $res[$r]['_�ơ��֥�̾_']) {
                                            echo "<td class='gb' nowrap><a href='JavaScript:win_open(\"table_detail.php?table={$res[$r][$n]}\")' title='����å��ǥơ��֥�ξܺ٤�ɽ�����ޤ���'>{$res[$r][$n]}</a></td>\n";
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
                }
                $query="";
            }
        }
        echo "</table>\n";
    }
?>
</center>
</body>
<?php echo $menu->out_alert_java()?>
</html>
<?php
ob_end_flush();                 // ���ϥХåե���gzip���� END
?>
