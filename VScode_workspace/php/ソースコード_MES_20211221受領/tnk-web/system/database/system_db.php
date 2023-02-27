<?php
//////////////////////////////////////////////////////////////////////////////
// �����ƥ�����ѥǡ����١�������                                           //
// Copyright (C) 2002-2010 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp      //
// Changed history                                                          //
// 2002/09/10 Created   system_db.php                                       //
//            ���å������� & register_globals = Off �б�                  //
// 2002/12/03 �����ȥ�˥塼�����줿���� access_log �� site_id=30 �ɲ�      //
// 2003/02/26 body �� onLoad ���ɲä�������ϸĽ�� focus() ������          //
//            �ʲ��� E_ALL ���ɲä��ǥХå���٥�(�ٹ�)��ǹ�ˤ���         //
// 2003/05/01 ���顼���Υǡ����١�������礻�ˡ������Υ�å���������      //
//            pgsql.php �δؿ���� $php_errormsg �ν��Ϥ��Ѥ�������         //
// 2003/05/12 SQL ʸ��������ݴɤ��ƤӽФ���褦���ѹ� db_admin_history     //
// 2003/06/16 �������å��Ѥ�SQLȯ�Ի��������ݴɤ����ˣ�ȯ�¹Ԥ��ѹ�       //
// 2003/10/29 �桼����̾����SQLʸ�� limit 60 �� 120 ���ѹ�                  //
// 2003/12/19 ��DB access_log �� access_log2 ���ѹ��ˤ�� SQLʸ�ѹ�       //
// 2004/01/15 db_table_info(�ơ��֥����)�����ˤ�� �������å��ɲ�        //
// 2004/04/15 ���ߤ���³�桼����(default=20ʬ����ǲ��Ѽ�)��Ȳ�ܥ�����ɲ�//
// 2004/05/05 ����0.1.2������1.2.3���ѹ�������黻�Ҥǥ��å�(���Ƚ��)����//
// 2004/05/12 �����ȥ�˥塼ɽ������ɽ�� �ܥ����ɲ� menu_OnOff($script)�ɲ� //
// 2004/07/16 ���������פ��ɲä��ơ�POST��GET(<a href>)���ѹ����ǥ������ѹ� //
// 2004/08/03 stripslashes()����Ѥ��������������¸���Ƥ����Τ��ѹ�ͽ��    //
// 2004/10/18 php5.0.2��track_errors�����б��Τ����ɲäȥơ��֥�ܺ�ɽ���ɲ�//
// 2004/10/22 UID��(�Ұ��ֹ桦��̾�����׾���)���ɲ� �ơ��֥�����windowɽ��//
// 2005/02/23 MenuHeader class ����Ѥ��ƶ��̥�˥塼���ڤ�ǧ���������ѹ�   //
// 2005/03/02 ip_addr<>10.1.3.136��ip_addr!=10.1.3.136���ѹ� class���ѤΤ���//
// 2005/03/22 iframe�Ǥ��ѹ� $userquery �� $_SESSION['userquery'] ����¸    //
// 2005/04/07 ��˥塼���饹��SQL���󥸥���������к��롼������򤱤뤿��   //
//            ���󥹥�����������$_REQUEST['userquery']��������ѿ�����¸//
// 2005/09/20 NN��IE�ȹ�碌��٤� textarea��CSS font-size:10pt ���ɲ�      //
// 2007/01/17 postgresql.conf��standard_conforming_strings = on �ˤ�������  //
//            addslashes($userquery) �� pg_escape_string($userquery)        //
// 2007/05/02 ���������פ� LIMIT 30 �� LIMIT 70 ���ѹ�                      //
// 2007/12/21 SQL����θ�����˥塼���ɲá����硼�ȥ��åȤ�ɸ�ॿ���ؤ���¾ //
// 2007/12/22 ����������뵡ǽ�ɲäΤ��ᥭ�����_�¹���_���ѹ�          //
// 2010/01/19 DB������ơ��֥�̾�Ǥ⸡���Ǥ���褦���ѹ�               ��ë //
//////////////////////////////////////////////////////////////////////////////
// ini_set('error_reporting', E_ALL);          // E_ALL='2047' debug ��
ini_set('display_errors', '1');             // Error ɽ�� ON debug �� ��꡼���女����
ini_set('track_errors', '1');               // Store the last error/warning message in $php_errormsg (boolean)
ob_start('ob_gzhandler');                   // ���ϥХåե���gzip����
session_start();                            // ini_set()�μ��˻��ꤹ�뤳�� Script �Ǿ��

require_once ('../../function.php');        // define.php �� pgsql.php �� require_once ���Ƥ���
require_once ('../../MenuHeader.php');      // TNK ������ menu class
access_log();                               // Script Name �ϼ�ư����

//////////// ��˥塼���饹�Υ��󥹥��󥹤������˥����Х��ѿ����������
if (isset($_REQUEST['userquery'])) {
    $userquery = $_REQUEST['userquery'];
}

///// TNK ���ѥ�˥塼���饹�Υ��󥹥��󥹤����
$menu = new MenuHeader(3);                  // ǧ�ڥ����å�0=���̰ʾ� 3=admini�ʾ� �����=TOP_MENU �����ȥ�̤����

////////////// ����������
$menu->set_site(99, 30);                    // site_index=99(�����ƥ��˥塼) site_id=60(�ǡ����١���)
////////////// �꥿���󥢥ɥ쥹����
// $menu->set_RetUrl(SYS_MENU);                // �̾�ϻ��ꤹ��ɬ�פϤʤ�
//////////// �����ȥ�̾(�������Υ����ȥ�̾�ȥե�����Υ����ȥ�̾)
$menu->set_title('Data Base Administration (SQL)');
//////////// ɽ�������
// $menu->set_caption('����ץ�ǥ����ƥ�ޥ�������ɽ�����Ƥ��ޤ�');
//////////// �ƽ����action̾�ȥ��ɥ쥹����
// $menu->set_action('test_template',   SYS . 'log_view/php_error_log.php');

//////////// �ѿ��ν����
if (!isset($userquery)) {
    $userquery = "";                            // SQL ʸ �����
    $_SESSION['userquery'] = $userquery;
}

// ����å����к�
$uniq = uniqid('menu');

//////////// ����Υ��ե��å��ͤν����
$current_script  = $_SERVER['PHP_SELF'];        // ���߼¹���Υ�����ץ�̾����¸
$url_referer = $_SERVER["HTTP_REFERER"];        // �ƽФ�Ȥ�URL����¸ ���Υ�����ץȤ�ʬ�������򤷤Ƥ�����ϻ��Ѥ��ʤ�
if (!eregi($current_script, $url_referer)) {    // ��ʬ���ȤǸƤӽФ��Ƥ��ʤ����
    $_SESSION['db_admin_offset'] = 0;           // ���򥪥ե��å��ͣ��˽����
}
if (!isset($_SESSION['db_admin_offset'])) {     // ���å�������Ͽ����Ƥ��ʤ����
    $_SESSION['db_admin_offset'] = 0;           // ���򥪥ե��å��ͣ��˽����
}
$offset = $_SESSION['db_admin_offset'];         // �������ѿ��˥��ե��å�����Ͽ

if (isset($_REQUEST['db_search'])) {
    $db_search = $_REQUEST['db_search'];
} else {
    $db_search = '����';
}
if (isset($_REQUEST['hist_search'])) {
    $hist_search = $_REQUEST['hist_search'];
} else {
    $hist_search = '�����˸������������Ƥ�����Ʋ��������֥�󥯤����Ƥ��оݤǤ���';
}
if (isset($_REQUEST['session_time'])) {
    $session_time = $_REQUEST['session_time'];
    if ($session_time < 1 || $session_time > 59) {  // 59ʬ����礭����SQL���顼�ˤʤ롣
        $session_time = '20';   // ���顼�ξ���default�ͤ�����
    }
} else {
    $session_time = '20';   // default�ͤ�����(10ʬ)
}

///////////// �����꡼�¹�ʸ�������ݴ�
if (isset($_REQUEST['exec'])) {
    $_SESSION['db_admin_offset'] = 0;           // ���򥪥ե��å��ͣ��˽����
    $offset = $_SESSION['db_admin_offset'];     // �������ѿ��˥��ե��å�����Ͽ
    if ($userquery != '') {                     // 2005/04/07 change
        $hist_txt = stripslashes($userquery);       // 2007/12/22 MenuHeader���饹��addslashes()���줿��Τ��б�
        $hist_txt = pg_escape_string($hist_txt);    // �嵭�ǽ������줿��Τ��б���2007/01/17 change (postgresql.conf��standard_conforming_strings = on �ˤ�������)
        $query_insert = "insert into db_admin_history (hist) values ('$hist_txt')";
        if ( ($ret = query_affected($query_insert)) > 0) {
            $_SESSION['s_sysmsg'] .= "<font color='white'>�����ݴ�...OK<br></font>";
        } else {
            $_SESSION['s_sysmsg'] .= "�����ݴ�...NG code=$ret <br>";
        }
        $userquery = stripslashes($userquery);     // �������ѿ��˥��ԡ� // 2005/04/07 change
    } else {
        // SQL ʸ���֥�󥯤��ä��� ���⤷�ʤ���
    }
    $_SESSION['userquery'] = $userquery;
}

///////////// �����꡼�¹�ʸ������ƽ�
if (isset($_REQUEST['hist'])) {
    $query = "SELECT hist FROM db_admin_history ORDER BY regdate DESC OFFSET $offset LIMIT 1";
    if (getUniResult($query, $hist) > 0) {
        $userquery = stripslashes($hist);
        $_SESSION['userquery'] = $userquery;
    } else {
        $_SESSION['s_sysmsg'] .= "����ƽФ˼���<br>";
    }
    $_SESSION['db_admin_offset']++;         // ���򥪥ե��å��ͥ��󥯥����
}

///////////// ���ꥢ���������򥪥ե��åȤ�����
if (isset($_REQUEST['clr'])) {
    $_SESSION['db_admin_offset'] = 0;           // ���򥪥ե��å��ͣ��˽����
    $offset = $_SESSION['db_admin_offset'];     // �������ѿ��˥��ե��å�����Ͽ
    $userquery = "";                            // SQL ʸ �����
    $_SESSION['userquery'] = $userquery;
}

//////////// �������å��Υ����꡼����ڤ�����
if (isset($_REQUEST['cpy'])) {
    if ($_REQUEST['cpy'] == "������ץ���") {
        $userquery = "SELECT script,count(*) FROM access_log2 WHERE ip_addr != '10.1.3.136' GROUP BY script ORDER BY count DESC LIMIT 300";
    } elseif ($_REQUEST['cpy'] == "�ۥ�����") {
        $userquery = "SELECT host,count(*) FROM access_log2 WHERE ip_addr != '10.1.3.136' GROUP BY host ORDER BY count DESC LIMIT 100";
    } elseif ($_REQUEST['cpy'] == "�գɣ���") {
        $userquery = "SELECT acc.uid AS �Ұ��ֹ�, usr.name AS �ᡡ̾, count(*) AS ���������� FROM access_log2 AS acc LEFT OUTER JOIN user_detailes AS usr USING(uid) GROUP BY �Ұ��ֹ�, �ᡡ̾ ORDER BY ���������� DESC LIMIT 100 OFFSET 0";
    } elseif ($_REQUEST['cpy'] == "�桼����̾") {
        $userquery = "SELECT a.ip_addr, a.host, a.uid, u.name, CAST(time_log AS DATE) AS ����, CAST(time_log AS time) AS ����, script FROM access_log2 AS a LEFT OUTER JOIN user_detailes AS u USING(uid) WHERE a.ip_addr != '10.1.3.136' ORDER BY a.time_log DESC LIMIT 500 OFFSET 0";
    } elseif ($_REQUEST['cpy'] == 'DB ����') {
        $userquery = "SELECT db_name, table_name AS _�ơ��֥�̾_, table_comment AS �ơ��֥����� FROM db_table_info WHERE (table_comment LIKE '%{$db_search}%') OR (table_name LIKE '%{$db_search}%')";
    } elseif ($_REQUEST['cpy'] == '���򸡺�') {
        $userquery = "SELECT /* to_char(regdate, 'YY/MM/DD HH24:MI:SS') AS �¹��� */ regdate AS _�¹���_, hist AS \"SQL����\" FROM db_admin_history WHERE hist LIKE '%{$hist_search}%' ORDER BY regdate DESC LIMIT 100";
    } elseif ($_REQUEST['cpy'] == '��³user') {
        $userquery = "SELECT a.ip_addr, a.host, a.uid, u.name FROM access_log2 AS a LEFT OUTER JOIN user_detailes AS u USING(uid) WHERE a.time_log>=(CURRENT_TIMESTAMP - time '00:{$session_time}:00') GROUP BY a.ip_addr, a.host, a.uid, u.name LIMIT 120 OFFSET 0";
    } elseif ($_REQUEST['cpy'] == '����������') {
        $day = date('Ymd');
        $userquery = "SELECT a.host, a.uid, u.name, count(*) FROM access_log2 AS a LEFT OUTER JOIN user_detailes AS u USING(uid) WHERE time_log>=CAST('$day 000000' AS timestamp) AND time_log<=CAST('$day 240000' AS timestamp) AND ip_addr != '10.1.3.136' GROUP BY a.uid, u.name, a.host ORDER BY count DESC LIMIT 70";
    }
    $_SESSION['userquery'] = $userquery;
    $_REQUEST['exec'] = "�¹�";    // �ܥ����ĤǼ¹Ԥ��� �����ݴɤ��ʤ�
}

////////// HTML Header ����Ϥ��ƥ֥饦�����Υ���å��������
$menu->out_html_header();
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=EUC-JP">
<meta http-equiv="Content-Style-Type" content="text/css">
<title><?php echo $menu->out_title() ?></title>
<?php echo $menu->out_site_java()?>
<?php echo $menu->out_css()?>

<script language="JavaScript">
<!--
function db_search() {
    var db_search = prompt('�������������Ϥ��Ʋ�������', '<?php echo $db_search ?>');
    if (db_search == null) {
        return;
    }
    document.ini_form.action = '<?php echo $current_script, '?cpy=', urlencode('DB ����') ?>';
    document.ini_form.db_search.value = db_search;
    document.ini_form.submit();
}

function session_time() {
    var session_time = prompt('��³���֤�1��59�����Ϥ��Ʋ�������(ñ�̡�ʬ)', '<?php echo $session_time ?>');
    if (session_time == null) {
        return;
    }
    document.ini_form.action = '<?php echo $current_script, '?cpy=', urlencode('��³user') ?>';
    document.ini_form.session_time.value = session_time;
    document.ini_form.submit();
}
function history_search() {
    var hist = prompt('SQL�θ������������Ϥ��Ʋ�������', '<?php echo $hist_search ?>');
    if (hist == null) {
        return;
    }
    document.ini_form.action = '<?php echo $current_script, '?cpy=', urlencode('���򸡺�') ?>';
    document.ini_form.hist_search.value = hist;
    document.ini_form.submit();
}
// -->
</script>
<style type="text/css">
<!--
textarea {
    background-color:black;
    color:white;
    font-size:      10pt;
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
}
a:hover {
    background-color:   yellow;
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
}
td {
    border-style:           solid;
    border-width:           1px;
    border-top-color:       #FFFFFF;
    border-left-color:      #FFFFFF;
    border-right-color:     #bdaa90;
    border-bottom-color:    #bdaa90;
}
-->
</style>
<script language='JavaScript'>
<!--
function win_open(url) {
    var w = 800;
    var h = 600;
    var left = (screen.availWidth  - w) / 2;
    var top  = (screen.availHeight - h) / 2;
    window.open(url, 'view_win', 'width='+w+',height='+h+',scrollbars=yes,status=no,toolbar=no,location=no,menubar=no,top='+top+',left='+left);
}
// -->
</script>
</head>
<body onLoad='document.ini_form.userquery.focus()' style='overflow:hidden;'>
<center>
<?php echo $menu->out_title_border()?>
    
    <form name='ini_form' method='post' action='<?php echo $menu->out_self() ?>'>
        <table width='100%' border='0'>
            <table bgcolor='#d6d3ce' border='1' cellspacing='0' cellpadding='1'>
                <tr><td> <!----------- ���ߡ�(�ǥ�������) ------------>
            <table width='100%' class='winbox_field' bgcolor='#d6d3ce' border='1' cellspacing='0' cellpadding='1'>
                <tr>
                    <td align='center' class='select_font' nowrap>
                        <a href='<?php echo $menu->out_self(), '?cpy=', urlencode('������ץ���') ?>' style='text-decoration:none;'>������ץ���</a>
                    </td>
                    <td align='center' class='select_font'>
                        <a href='<?php echo $menu->out_self(), '?cpy=', urlencode('�ۥ�����') ?>' style='text-decoration:none;'>�ۥ�����</a>
                    </td>
                    <td align='center' class='select_font'>
                        <a href='<?php echo $menu->out_self(), '?cpy=', urlencode('�գɣ���') ?>' style='text-decoration:none;'>�գɣ���</a>
                    </td>
                    <td align='center' class='select_font'>
                        <a href='<?php echo $menu->out_self(), '?cpy=', urlencode('�桼����̾') ?>' style='text-decoration:none;'>�桼����̾</a>
                    </td>
                    <td align='center' class='select_font'>
                        <a href='JavaScript:db_search()' style='text-decoration:none;'>DB ����</a>
                        <input type='hidden' name='db_search' value='<?php echo $db_search ?>'>
                    </td>
                    <td align='center' class='select_font'>
                        <a href='JavaScript:history_search()' style='text-decoration:none;'>���򸡺�</a>
                        <input type='hidden' name='hist_search' value='<?php echo $hist_search ?>'>
                    </td>
                    <td align='center' class='select_font'>
                        <a href='JavaScript:session_time()' style='text-decoration:none;'>��³user</a>
                        <input type='hidden' name='session_time' value='<?php echo $session_time ?>'>
                    </td>
                    <td align='center' class='select_font'>
                        <a href='<?php echo $menu->out_self(), '?cpy=', urlencode('����������') ?>' style='text-decoration:none;'>����������</a>
                    </td>
                </tr>
            </table>
                </td></tr>
            </table> <!----------------- ���ߡ�End ------------------>
            <table width='100%' border='0'>
                <tr>
                    <td valign='middle' align='right' width='50' style='border-width:0px;'>
                        <input type='submit' name='hist' value='����<?php echo isset($_REQUEST['hist']) ? $offset+1 : '' ?>'>
                    </td>
                    <td align='center' style='border-width:0px;'>
                        <textarea name='userquery' cols='100' rows=5 wrap='virtual'><?php echo $userquery ?></textarea>
                    </td>
                    <td valign='middle' align='center' class='pt8' style='border-width:0px;'>
                        <input type='submit' name='exec' value='�� ��'>
                        
                        <input type='submit' name='clr'  value='���ꥢ'>
                        <br>
                        <a href="../../emp/help.htm" target="_blank">
                            <img border=0 src="../../img/help.gif" alt="�إ��" width=22 height=16>
                        </a>
                    </td>
                    <td valign='bottom' align='center' class='pt8'>
                    </td>
                </tr>
            </table>
        </table>
    </form>
<?php if ( (isset($_REQUEST['exec'])) && (!isset($_REQUEST['clr'])) && (isset($userquery)) ) { ?>
    <iframe hspace='0' vspace='0' scrolling='yes' src='system_db_iframe.php?id=<?php echo $uniq?>'
        name='system_db_view' align='center' width='100%' height='75%' title='DataBase view'>
        DataBase �� Query View ��ɽ�����Ƥ��ޤ���
    </iframe>
<?php } else { ?>
    <hr> <!-- ------------------------------------------- -->
<?php } ?>
</center>
</body>
<?php // = $menu->out_alert_java()?>
</html>
<?php
ob_end_flush();                 // ���ϥХåե���gzip���� END
?>
