<?php
//////////////////////////////////////////////////////////////////////////
// �����ӥ���� �����ƥ�ޥ��������ƥʥ�                            //
// Copyright(C) 2003 2007 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp   //
// Changed history                                                      //
// 2003/10/21 Created   service_item_master_mnt.php                     //
// 2003/10/22 �ɲá��ѹ������ �ڤ� ���ԡ��ܥ������å��˼�����    //
// 2003/10/24 ��������(���������)���������(Ĵã������)��intext�����//
// 2003/11/12 div(������)section(������)order_no(ɽ����)�Υ������ɲ�  //
// 2007/01/24 MenuHeader���饹�б�                                      //
//////////////////////////////////////////////////////////////////////////
ini_set('error_reporting',E_ALL);           // E_ALL='2047' debug ��
// ini_set('display_errors','1');              // Error ɽ�� ON debug �� 
ob_start('ob_gzhandler');                   // ���ϥХåե���gzip����
session_start();                            // ini_set()�μ��˻��ꤹ�뤳�� Script �Ǿ��

require_once ('../../function.php');
require_once ('../../tnk_func.php');
require_once ('../../MenuHeader.php');      // TNK ������ menu class
access_log();                               // Script Name �ϼ�ư����

///// TNK ���ѥ�˥塼���饹�Υ��󥹥��󥹤����
$menu = new MenuHeader(0);                  // ǧ�ڥ����å�0=���̰ʾ� �����=TOP_MENU �����ȥ�̤����

////////////// ����������
$menu->set_site(10,  5);                    // site_index=10(»�ץ�˥塼) site_id=5(�����ӥ�����˥塼)
////////////// �꥿���󥢥ɥ쥹����(���л��ꤹ����)
$menu->set_RetUrl($_SESSION['service_referer']);    // ʬ������������¸����Ƥ���ƽи��򥻥åȤ���
//////////// �ƽ����action̾�ȥ��ɥ쥹����
// $menu->set_action('test_template',   SYS . 'log_view/php_error_log.php');

$current_script  = $_SERVER['PHP_SELF'];        // ���߼¹���Υ�����ץ�̾����¸
$url_referer     = $_SESSION['service_referer'];    // ʬ������������¸����Ƥ���ƽи��򥻥åȤ���

////////////// ǧ�ڥ����å�
if (account_group_check() == FALSE) {
    $_SESSION["s_sysmsg"] = "���ʤ��ϵ��Ĥ���Ƥ��ޤ���<br>�����Ԥ�Ϣ���Ʋ�������";
    header("Location: $url_referer");                   // ľ���θƽи������
    exit();
}

//////////// �����ȥ�����ա���������
$today = date("Y/m/d H:i:s");
//////////// �����ȥ�̾(�������Υ����ȥ�̾�ȥե�����Υ����ȥ�̾)
$menu_title = "�����ӥ���� �ޥ��������ƥʥ�";
//////////// �����ȥ�̾(�������Υ����ȥ�̾�ȥե�����Υ����ȥ�̾)
$menu->set_title($menu_title);

//////////// �о�ǯ��Υ��å����ǡ�������
if (isset($_SESSION['service_ym'])) {
    $service_ym = $_SESSION['service_ym']; 
} else {
    $service_ym = date('Ym');        // ���å����ǡ������ʤ����ν����(����)
    if (substr($service_ym,4,2) != 01) {
        $service_ym--;
    } else {
        $service_ym = $service_ym - 100;
        $service_ym = $service_ym + 11;   // ��ǯ��12��˥��å�
    }
}
//////////// �����ƥ��å������ν����
$_SESSION['s_sysmsg'] = '';

//////////// �ɲåܥ��󤬲����줿��
if (isset($_POST['add'])) {
    $query = sprintf('select item_no from service_item_master where item_no=%d', $_POST['item_no']);
    $res_chk = array();
    if ( getResult2($query, $res_chk) > 0 ) {
        $_SESSION['s_sysmsg'] .= sprintf('%d :�ϴ�����Ͽ�ѤߤǤ���', $_POST['item_no']);    // .= �����
    } else {
        $query = sprintf("insert into service_item_master (item_no, intext, item, note, div, section, order_no)
                          values (%d, %d, '%s', '%s', '%s', '%s', %d)",
                $_POST['item_no'], $_POST['intext'], $_POST['item'], $_POST['note'], $_POST['div'], $_POST['section'], $_POST['order_no']);
        if (query_affected($query) <= 0) {
            $_SESSION['s_sysmsg'] .= sprintf('%d :����Ͽ�˼��ԡ�', $_POST['item_no']);    // .= �����
        } else {
            $_SESSION['s_sysmsg'] .= sprintf("<font color='yellow'>%d: %s: ����Ͽ���ޤ�����</font>",
                    $_POST['item_no'], $_POST['item']);    // .= �����
        }
    }
}

//////////// ����ܥ��󤬲����줿��
if (isset($_POST['del'])) {
    $query = sprintf('select item_no from service_item_master where item_no=%d', $_POST['item_no']);
    $res_chk = array();
    if ( getResult2($query, $res_chk) <= 0 ) {
        $_SESSION['s_sysmsg'] .= sprintf('%d :����Ͽ����Ƥ��ޤ���', $_POST['item_no']);    // .= �����
    } else {
        $query = sprintf("delete from service_item_master where item_no=%d",
                $_POST['item_no']);
        if (query_affected($query) <= 0) {
            $_SESSION['s_sysmsg'] .= sprintf('%d :�κ���˼��ԡ�', $_POST['item_no']);    // .= �����
        } else {
            $_SESSION['s_sysmsg'] .= sprintf("<font color='yellow'>%d: %s: �������ޤ�����</font>",
                    $_POST['item_no'], $_POST['item']);    // .= �����
        }
    }
}

//////////// �ѹ��ܥ��󤬲����줿��
if (isset($_POST['chg'])) {
    $query = sprintf('select item_no from service_item_master where item_no=%d', $_POST['item_no']);
    $res_chk = array();
    if ( getResult2($query, $res_chk) <= 0 ) {
        $_SESSION['s_sysmsg'] .= sprintf('%d :����Ͽ����Ƥ��ޤ���', $_POST['item_no']);    // .= �����
    } else {
        $query = sprintf("update service_item_master set item_no=%d, intext=%d, item='%s', note='%s',
                          div='%s', section='%s', order_no=%s where item_no=%d",
                          $_POST['item_no'], $_POST['intext'], $_POST['item'], $_POST['note'],
                          $_POST['div'], $_POST['section'], $_POST['order_no'],
                          $_POST['item_no']);
        if (query_affected($query) <= 0) {
            $_SESSION['s_sysmsg'] .= sprintf('%d :���ѹ��˼��ԡ�', $_POST['item_no']);    // .= �����
        } else {
            $_SESSION['s_sysmsg'] .= sprintf("<font color='yellow'>%d: %s: ���ѹ����ޤ�����</font>",
                    $_POST['item_no'], $_POST['item']);    // .= �����
        }
    }
}

//////////// service_item_master ����ޥ������ǡ�������
$query = "select item_no as ������, intext as �⳰������, item as ľ������, note as ����,
          div as ������, section as ������, order_no as ɽ����,
          regdate::date as �����Ͽ, last_date::date as ������
          from service_item_master order by order_no ASC";
$res = array();
if (($rows = getResultWithField2($query, $field, $res)) <= 0) {
// if ( ($rows=getResult2($query, $res)) <= 0) {
    $_SESSION['s_sysmsg'] = '�����������٤�̵���������Ǥ��ޤ���';
    // header("Location: $url_referer");                   // ľ���θƽи������
    // exit();
    $num = 0;
} else {
    $num = count($field);       // �ե�����ɿ�����
}

//////////// ���ԡ��ܥ��󤬲����줿��
if (isset($_POST['cpy'])) {
    $tmp_item_no = $res[$_POST['cpy']-1][0];
    $intext      = $res[$_POST['cpy']-1][1];
    $tmp_item    = $res[$_POST['cpy']-1][2];
    $tmp_note    = $res[$_POST['cpy']-1][3];
    $div         = $res[$_POST['cpy']-1][4];
    $section     = $res[$_POST['cpy']-1][5];
    $order_no    = $res[$_POST['cpy']-1][6];
} else {
    $tmp_item_no = '';
    $intext      = '';
    $tmp_item    = '';
    $tmp_note    = '';
    $div         = '';
    $section     = '';
    $order_no    = '';
}

/////////// HTML Header ����Ϥ��ƥ���å��������
$menu->out_html_header();
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=EUC-JP">
<meta http-equiv="Content-Style-Type" content="text/css">
<title><?php echo $menu->out_title() ?></title>
<?php echo $menu->out_site_java() ?>
<?php echo $menu->out_css() ?>
<?php echo $menu->out_jsBaseClass() ?>
<style type="text/css">
<!--
select {
    background-color:teal;
    color:white;
}
textarea {
    background-color:black;
    color:white;
}
input.sousin {
    background-color:red;
}
input.text {
    background-color:black;
    color:white;
}
.pt10 {
    font-size: 10pt;
}
.pt10b {
    font:bold 10pt;
}
.pt11b {
    font:bold 11pt;
}
.pt12b {
    font:bold 12pt;
    font-family: monospace;
}
th {
    font:bold 11pt;
}
.title-font {
    font:bold 13.5pt;
    font-family: monospace;
    border-top:1.0pt solid windowtext;
    border-right:none;
    border-bottom:1.0pt solid windowtext;
    border-left:0.5pt solid windowtext;
}
.today-font {
    font-size: 10.5pt;
    font-family: monospace;
    border-top:1.0pt solid windowtext;
    border-right:1.0pt solid windowtext;
    border-bottom:1.0pt solid windowtext;
    border-left:0.5pt solid windowtext;
}
.explain_font {
    font-size: 8.5pt;
    font-family: monospace;
}
.margin0 {
    margin:0%;
}
.right{
    text-align:right;
    font:bold 10pt;
    font-family: monospace;
}
-->
</style>
</head>
<body>
    <center>
<?php echo $menu->out_title_border() ?>
        
        <!----------------- ������ ���� ���� �Υե����� ---------------->
        <table cellspacing="0" cellpadding="0" border='0'>
            <form name='page_form' method='post' action='<?= $current_script ?>'>
                <tr>
                    <td align='center' class='pt11b'>
                        <table bgcolor='#d6d3ce' align='center' cellspacing="0" cellpadding="3" border='1'>
                            <tr><td> <!----------- ���ߡ�(�ǥ�������) ------------>
                        <table bordercolordark='white' bordercolorlight='#bdaa90' bgcolor='#d6d3ce' align='center' cellspacing="0" cellpadding="3" border='1'>
                            <th>������</th> <th nowrap>�⳰��</th> <th nowrap>ľ������̾</th> <th>��  ��</th> <th>������</th> <th>������</th> <th>ɽ����</th>
                            <tr>
                                <td align='center'>
                                    <input type='text' class='right' name='item_no' size='4' maxlength='4' value='<?= $tmp_item_no ?>'>
                                </td>
                                <td align='center'>
                                    <input type='text' class='right' name='intext' size='1' maxlength='1' value='<?= $intext ?>'>
                                </td>
                                <td align='center'>
                                    <input type='text' class='pt10' name='item' size='10' maxlength='20' value='<?= $tmp_item ?>'>
                                </td>
                                <td align='center'>
                                    <input type='text' class='pt10' name='note' size='40' maxlength='30' value='<?= $tmp_note ?>'>
                                </td>
                                <td align='center'>
                                    <input type='text' class='right' name='div' size='1' maxlength='1' value='<?= $div ?>'>
                                </td>
                                <td align='center'>
                                    <input type='text' class='right' name='section' size='1' maxlength='1' value='<?= $section ?>'>
                                </td>
                                <td align='center'>
                                    <input type='text' class='right' name='order_no' size='4' maxlength='5' value='<?= $order_no ?>'>
                                </td>
                            </tr>
                        </table>
                            </td></tr>
                        </table> <!----------------- ���ߡ�End ------------------>
                    </td>
                    <td width='65' align='center'>
                        <table align='center' border='3' cellspacing='0' cellpadding='0'>
                            <td align='left'>
                                <input class='pt10b' type='submit' name='add' value='�ɲ�'>
                            </td>
                        </table>
                    </td>
                    <td width='60' align='center'>
                        <table align='center' border='3' cellspacing='0' cellpadding='0'>
                            <td align='right'>
                                <input class='pt10b' type='submit' name='del' value='���'>
                            </td>
                        </table>
                    </td>
                    <td width='60' align='center'>
                        <table align='center' border='3' cellspacing='0' cellpadding='0'>
                            <td align='right'>
                                <input class='pt10b' type='submit' name='chg' value='�ѹ�'>
                            </td>
                        </table>
                    </td>
                </tr>
            </form>
        </table>
        
        <br>
        
        <!--------------- ����������ʸ��ɽ��ɽ������ -------------------->
        <caption>
            <font class='pt10'>
                �⳰�����񡧡���������(���������)�ᣱ�����������(Ĵã������)�ᣲ��
                �����̡�H=ɸ���ʡ�B=�̥Х�������硡S=����������
            </font>
        </caption>
        <table bgcolor='#d6d3ce' align='center' cellspacing="0" cellpadding="3" border='1'>
            <tr><td> <!----------- ���ߡ�(�ǥ�������) ------------>
        <table bordercolordark='white' bordercolorlight='#bdaa90' bgcolor='#d6d3ce' align='center' cellspacing="0" cellpadding="3" border='1'>
            <THEAD>
                <!-- �ơ��֥� �إå�����ɽ�� -->
                <tr>
                    <th width='10' bgcolor='yellow'>No.</th>        <!-- �ԥʥ�С���ɽ�� -->
                <?php
                for ($i=0; $i<$num; $i++) {             // �ե�����ɿ�ʬ���֤�
                    echo "<th bgcolor='yellow'>{$field[$i]}</th>\n";
                }
                ?>
                </tr>
            </THEAD>
            <TFOOT>
                <!-- ���ߤϥեå����ϲ���ʤ� -->
            </TFOOT>
            <TBODY>
            <form name='page_form' method='post' action='<?= $current_script ?>'>
                <?php
                for ($r=0; $r<$rows; $r++) {
                    echo "<tr>\n";
                        printf("<td class='pt10b' align='right'><input class='pt10' type='submit' name='cpy' value='%d'></td>\n", $r + 1);    // ���ֹ��ɽ��
                    for ($i=0; $i<$num; $i++) {         // �쥳���ɿ�ʬ���֤�
                        echo "<!--  bgcolor='#ffffc6' �������� -->\n";
                        if ($i == 3) {          // ����
                            echo "<td nowrap align='left' class='pt10b'>{$res[$r][$i]}</td>\n";
                        } elseif ($i == 6) {    // ����(�����Ƚ�)
                            echo "<td nowrap align='right' class='pt10b'>{$res[$r][$i]}</td>\n";
                        } else {
                            echo "<td nowrap align='center' class='pt10b'>{$res[$r][$i]}</td>\n";
                        }
                        echo "<!-- ����ץ�<td rowspan='2' colspan='3' width='200' align='center' class='pt10b' bgcolor='#ffffc6'>  </td> -->\n";
                    }
                    echo "</tr>\n";
                }
                ?>
            </form>
            </TBODY>
        </table>
            </td></tr>
        </table> <!----------------- ���ߡ�End ------------------>
    </center>
</body>
</html>
<?php ob_end_flush(); // ���ϥХåե���gzip���� END ?>
