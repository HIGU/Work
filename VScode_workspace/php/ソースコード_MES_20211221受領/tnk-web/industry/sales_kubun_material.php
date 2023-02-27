<?php
//////////////////////////////////////////////////////////////////////////////
// ����ñ������Ͽ��ʬ�� �������Ȥ����ɽ                                  //
// Copyright (C) 2004-2006 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp      //
// Changed history                                                          //
// 2004/10/29 Created  sales_kubun_material.php                             //
// 2004/11/01 ���Ƥζ�ʬ�Ǹ�������뵡ǽ���ɲ�                              //
// 2004/11/05 ���������Ф������ñ������Ψɽ�����ɲ�                      //
// 2006/05/10 2006/04/01������ñ�������գФ�ȼ����Ͽ��ʬ���ɲ� ��           //
// 2006/11/29 2006/11/01����龜����ƥ�κ�����ʬ�����ñ����ȿ��(UP)��ʬG    //
//            ��碌�ƣ��ǹԿ������Ǥ���褦���ѹ�(���ꥹ�ȤΥ��ԡ���)    //
// 2007/10/26 E_ALL | E_STRICT�� phpɸ�ॿ���� ����ñ������'H'���ɲ�20071026//
//////////////////////////////////////////////////////////////////////////////
// ini_set('error_reporting', E_ALL);          // E_ALL='2047' debug ��
// ini_set('display_errors', '1');             // Error ɽ�� ON debug �� ��꡼���女����
// ini_set('zend.ze1_compatibility_mode', '1');    // zend 1.X ����ѥ� php4�θߴ��⡼��
// ini_set('implicit_flush', 'off');           // echo print �� flush �����ʤ�(�٤��ʤ뤿��) CLI��
// ini_set('max_execution_time', 1200);        // ����¹Ի���=20ʬ WEB CGI��
ob_start('ob_gzhandler');                   // ���ϥХåե���gzip����
session_start();                            // ini_set()�μ��˻��ꤹ�뤳�� Script �Ǿ��

require_once ('../function.php');           // define.php �� pgsql.php �� require_once ���Ƥ���
require_once ('../tnk_func.php');           // TNK �˰�¸������ʬ�δؿ��� require_once ���Ƥ���
require_once ('../MenuHeader.php');         // TNK ������ menu class
access_log();                               // Script Name �ϼ�ư����

///// TNK ���ѥ�˥塼���饹�Υ��󥹥��󥹤����
$menu = new MenuHeader(0);                  // ǧ�ڥ����å�0=���̰ʾ� �����=TOP_MENU �����ȥ�̤����

////////////// ����������
$menu->set_site(30, 999);                   // site_index=30(������˥塼) site_id=999(�����Ȥ򳫤�)
////////////// �꥿���󥢥ɥ쥹����
// $menu->set_RetUrl(SYS_MENU);                // �̾�ϻ��ꤹ��ɬ�פϤʤ�
//////////// �����ȥ�̾(�������Υ����ȥ�̾�ȥե�����Υ����ȥ�̾)
$menu->set_title('��ʬ����λ���ñ���������������');
//////////// �ƽ����action̾�ȥ��ɥ쥹����
// $menu->set_action('test_template',   SYS . 'log_view/php_error_log.php');

/**********************
////////////// �꥿���󥢥ɥ쥹����(������)
// ���å�����ѿ�̾�ϥ�����ץ�̾�����ĥ����ʬ���������Τ�'_ret'���ղä������UNIQUE����������롣
if (isset($_SESSION['template_ret'])) {
    $url_referer = $_SESSION['template_ret'];   // �ƽи�����¸���Ƥ���꥿���󥢥ɥ쥹�����
} else {
    $url_referer = $_SERVER['HTTP_REFERER'];    // error ����Τ���
    $_SESSION['template_ret'] = $url_referer;
}
**********************/

//////////// JavaScript Stylesheet File ��ɬ���ɤ߹��ޤ���
$uniq = uniqid('target');

if (isset($_REQUEST['reg_kubun'])) {
    $_SESSION['reg_kubun'] = $_REQUEST['reg_kubun'];
    $reg_kubun = $_SESSION['reg_kubun'];
} else {
    if (isset($_SESSION['reg_kubun'])) {
        $reg_kubun = $_SESSION['reg_kubun'];
    } else {
        $reg_kubun = ' ';                               // Default
    }
}
//////////// ɽ�������
$menu->set_caption("���줿����ñ���������������");
//////////// SQL ʸ�� where ��� ���Ѥ���
$search = sprintf("WHERE reg_kubun='%s'", $reg_kubun);

//////////// ���ǤιԿ�
if (isset($_REQUEST['pageRows']) && $_REQUEST['pageRows'] > 0 && $_REQUEST['pageRows'] <= 5000) {
    define('PAGE', $_REQUEST['pageRows']);
} else {
    define('PAGE', '25');
}
$pageRows = PAGE;

//////////// ��ץ쥳���ɿ�����     (�оݥơ��֥�κ������ڡ�������˻���)
$query = sprintf('SELECT count(*) FROM sales_price_nk %s', $search);
if ( getUniResult($query, $maxrows) <= 0) {         // $maxrows �μ���
    $_SESSION['s_sysmsg'] .= '��ץ쥳���ɿ��μ����˼���<br>DB����³���ǧ��';  // .= ��å��������ɲä���
}
//////////// �ڡ������ե��å�����(offset�ϻ��Ѥ������̾�����ѹ� �㡧sales_offset)
if ( isset($_POST['forward']) ) {                       // ���Ǥ������줿
    $_SESSION['offset'] += PAGE;
    if ($_SESSION['offset'] >= $maxrows) {
        $_SESSION['offset'] -= PAGE;
        if ($_SESSION['s_sysmsg'] == '') {
            $_SESSION['s_sysmsg'] .= "<font color='yellow'>���ǤϤ���ޤ���</font>";
        } else {
            $_SESSION['s_sysmsg'] .= "<br><font color='yellow'>���ǤϤ���ޤ���</font>";
        }
    }
} elseif ( isset($_POST['backward']) ) {                // ���Ǥ������줿
    $_SESSION['offset'] -= PAGE;
    if ($_SESSION['offset'] < 0) {
        $_SESSION['offset'] = 0;
        if ($_SESSION['s_sysmsg'] == '') {
            $_SESSION['s_sysmsg'] .= "<font color='yellow'>���ǤϤ���ޤ���</font>";
        } else {
            $_SESSION['s_sysmsg'] .= "<br><font color='yellow'>���ǤϤ���ޤ���</font>";
        }
    }
} elseif ( isset($_GET['page_keep']) ) {                // ���ߤΥڡ�����ݻ����� GET�����
    $offset = $_SESSION['offset'];
} else {
    $_SESSION['offset'] = 0;                            // ���ξ��ϣ��ǽ����
}
$offset = $_SESSION['offset'];

//////////// ɽ�����Υǡ���ɽ���ѤΥ���ץ� Query & �����
$query = sprintf("
        SELECT
            sal.parts_no            AS �����ֹ�,                -- 0
            substr(midsc, 1, 26)    AS ����̾,                  -- 1
            sal.regdate             AS ��Ͽ��,                  -- 2
            CASE
                WHEN trim(sal.note) = '' THEN
                    '---'
                ELSE
                    sal.note
            END                     AS \"����=S\",              -- 3
            sal.price               AS ����ñ��,                -- 4
            (SELECT mate.sum_price + Uround(mate.assy_time * assy_rate, 2) FROM material_cost_header AS mate WHERE mate.assy_no=sal.parts_no ORDER BY mate.regdate DESC LIMIT 1)
                                    AS �������                 -- 5
        FROM
            sales_price_nk          AS sal
        LEFT OUTER JOIN
            miitem                  ON (sal.parts_no=mipn)
        %s      -- ������ where��� and �������Ǥ���
        ORDER BY sal.parts_no ASC
        offset %d LIMIT %d
    ", $search, $offset, PAGE);       // ���� $search �Ǹ���
$res   = array();
$field = array();
if (($rows = getResultWithField2($query, $field, $res)) <= 0) {
    $_SESSION['s_sysmsg'] .= '�ǡ���������ޤ���';
    // header('Location: ' . $menu->out_retUrl());                   // ľ���θƽи������
    // exit();
}
$num = count($field);       // �ե�����ɿ�����

/////////// HTML Header ����Ϥ��ƥ���å��������
$menu->out_html_header();
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=EUC-JP">
<meta http-equiv="Content-Style-Type" content="text/css">
<meta http-equiv="Content-Script-Type" content="text/javascript">
<title><?php echo $menu->out_title() ?></title>
<?php echo $menu->out_site_java() ?>
<?php echo $menu->out_css() ?>
<!--    �ե��������ξ��
<script language='JavaScript' src='template.js?<?php echo $uniq ?>'>
</script>
-->

<script language="JavaScript">
<!--
/* ����ʸ�����������ɤ��������å�(ASCII code check) */
function isDigit(str) {
    var len = str.length;
    var c;
    for (i=0; i<len; i++) {
        c = str.charAt(i);
        if ((c < '0') || (c > '9')) {
            return false;
        }
    }
    return true;
}

/* ����ʸ��������ե��٥åȤ��ɤ��������å� isDigit()�ε� */
function isABC(str) {
    // var str = str.toUpperCase();    // ɬ�פ˱�������ʸ�����Ѵ�
    var len = str.length;
    var c;
    for (i=0; i<len; i++) {
        c = str.charAt(i);
        if ((c < 'A') || (c > 'Z')) {
            if (c == ' ') continue; // ���ڡ�����OK
            return false;
        }
    }
    return true;
}

/* ����ʸ�����������ɤ��������å� �������б� */
function isDigitDot(str) {
    var len = str.length;
    var c;
    var cnt_dot = 0;
    for (i=0; i<len; i++) {
        c = str.charAt(i);
        if (c == '.') {
            if (cnt_dot == 0) {     // 1���ܤ������å�
                cnt_dot++;
            } else {
                return false;       // 2���ܤ� false
            }
        } else {
            if (('0' > c) || (c > '9')) {
                return false;
            }
        }
    }
    return true;
}

/* ������ϥե�����Υ�����Ȥ˥ե������������� */
function set_focus() {
//    document.form_name.element_name.focus();      // ������ϥե����ब������ϥ����Ȥ򳰤�
//    document.form_name.element_name.select();
}
// -->
</script>

<!-- �������륷���ȤΥե��������򥳥��� HTML���� �����Ȥ�����Ҥ˽���ʤ��������
<link rel='stylesheet' href='<?php echo MENU_FORM . '?' . $uniq ?>' type='text/css' media='screen'>
 -->

<style type="text/css">
<!--
.pt8 {
    font-size:   8pt;
    font-weight: normal;
    font-family: monospace;
}
.pt9 {
    font-size:   9pt;
    font-weight: normal;
    font-family: monospace;
}
.pt10 {
    font-size:   10pt;
    font-weight: normal;
    font-family: monospace;
}
.pt10b {
    font-size:   10pt;
    font-weight: bold;
    font-family: monospace;
}
.pt11b {
    font-size:   11pt;
    font-weight: bold;
    font-family: monospace;
}
.pt12b {
    font-size:   12pt;
    font-weight: bold;
    font-family: monospace;
}
th {
    background-color: yellow;
    color:            blue;
    font-size:        10pt;
    font-weight:      bold;
    font-family:      monospace;
}
<?php
/************
table {
    border-top:    1.0pt outset #bdaa90;
    border-right:  1.0pt outset white;
    border-bottom: 1.0pt outset white;
    border-left:   0.5pt outset #bdaa90;
}
td {
    border-top:    1.0pt outset #bdaa90;
    border-right:  1.0pt outset white;
    border-bottom: 1.0pt outset white;
    border-left:   0.5pt outset #bdaa90;
}
**************/
?>
.tuborgbox {
    border-style: solid;
    border-width: 1px;
    border-top-color: #FFFFFF;
    border-left-color: #FFFFFF;
    border-right-color: #AAAAAA;
    border-bottom-color: #AAAAAA;
}
.tuborgboxsimple {
    border-style: solid;
    border-width: 1px;
    border-color: #AAAAAA;
}
.rappsbox {
    border-style: solid;
    border-width: 1px;
    border-top-color: #FFFFFF;
    border-left-color: #FFFFFF;
    border-right-color: #DFDFDF;
    border-bottom-color: #DFDFDF;
}
.rappsboxsimple {
    border-style: solid;
    border-width: 1px;
    border-color: #DFDFDF;
}
-->
</style>
</head>
<body onLoad='set_focus()'>
    <center>
<?php echo $menu->out_title_border() ?>
        <!--
            <div style='position: absolute; top: 80; left: 7; width: 185; height: 31'>
                �����ͤǰ��ֻ���
            </div>
        -->
        
        <!----------------- ������ ���� ���� �Υե����� ---------------->
        <table width='100%' cellspacing='0' cellpadding='0' border='0'>
            <form name='page_form' method='post' action='<?php echo $menu->out_self() ?>'>
                <tr>
                    <td align='left'>
                        <table align='left' border='3' cellspacing='0' cellpadding='0'>
                            <td align='left'>
                                <input class='pt10b' type='submit' name='backward' value='����'>
                            </td>
                        </table>
                    </td>
                    <td align='center' class='caption_font'>
                        <select name='reg_kubun' class='ret_font' onChange='document.page_form.submit()' style='color:white; background-color:blue;'>
                            <option value=' ' <?php if($reg_kubun == ' ') echo 'selected'; ?>>������(��ư����)</option>
                            <option value='1' <?php if($reg_kubun == '1') echo 'selected'; ?>>��������1.21��</option>
                            <option value='2' <?php if($reg_kubun == '2') echo 'selected'; ?>>��������1.05��</option>
                            <option value='3' <?php if($reg_kubun == '3') echo 'selected'; ?>>�ơ������ۡ�ʬ</option>
                            <option value='4' <?php if($reg_kubun == '4') echo 'selected'; ?>>��������������</option>
                            <option value='A' <?php if($reg_kubun == 'A') echo 'selected'; ?>>���󥳥��ȥ�����</option>
                            <option value='C' <?php if($reg_kubun == 'C') echo 'selected'; ?>>���󥳥��ȥ�����</option>
                            <option value='D' <?php if($reg_kubun == 'D') echo 'selected'; ?>>���󥳥��ȥ�����</option>
                            <option value='E' <?php if($reg_kubun == 'E') echo 'selected'; ?>>���󥳥��ȥ�����</option>
                            <option value='F' <?php if($reg_kubun == 'F') echo 'selected'; ?>>�����ȥ��å�</option>
                            <option value='G' <?php if($reg_kubun == 'G') echo 'selected'; ?>>��﫡�SUS�ॢ�å�</option>
                            <option value='H' <?php if($reg_kubun == 'H') echo 'selected'; ?>>2007/10/01���ڲ���</option>
                        </select>
                        <?php echo $menu->out_caption(), '&nbsp;&nbsp;&nbsp;��׷����', number_format($maxrows), "��\n" ?>
                    </td>
                    <td align='left' class='caption_font'>
                        �ǹԿ�
                        <select name='pageRows' class='ret_font' onChange='document.page_form.submit()'>
                            <option value='25'<?php if($pageRows == '25') echo ' selected'; ?>>&nbsp;&nbsp;25</option>
                            <option value='100'<?php if($pageRows == '100') echo ' selected'; ?>>&nbsp;100</option>
                            <option value='500'<?php if($pageRows == '500') echo ' selected'; ?>>&nbsp;500</option>
                            <option value='1000'<?php if($pageRows == '1000') echo ' selected'; ?>>1000</option>
                            <option value='3000'<?php if($pageRows == '3000') echo ' selected'; ?>>3000</option>
                        </select>
                    </td>
                    <td align='right'>
                        <table align='right' border='3' cellspacing='0' cellpadding='0'>
                            <td align='right'>
                                <input class='pt10b' type='submit' name='forward' value='����'>
                            </td>
                        </table>
                    </td>
                </tr>
            </form>
        </table>
        
        <!--------------- ����������ʸ��ɽ��ɽ������ -------------------->
        <table bgcolor='#d6d3ce' align='center' cellspacing='0' cellpadding='3' border='1'>
            <tr><td> <!----------- ���ߡ�(�ǥ�������) ------------>
        <table width='100%' bordercolordark='white' bordercolorlight='#bdaa90' bgcolor='#d6d3ce' align='center' cellspacing="0" cellpadding="3" border='1'>
            <thead>
                <!-- �ơ��֥� �إå�����ɽ�� -->
                <tr>
                    <th nowrap width='30'>No.</th>        <!-- �ԥʥ�С���ɽ�� -->
                <?php
                for ($i=0; $i<$num; $i++) {             // �ե�����ɿ�ʬ���֤�
                    switch ($i) {
                    case 0: $w=60;  break;
                    case 1: $w=200; break;
                    case 2: $w=70;  break;
                    case 3: $w=50;  break;
                    case 4: $w=60;  break;
                    case 5: $w=60;  break;
                    default:$w=60;  break;
                    }
                    echo "<th nowrap width='{$w}'>", $field[$i], "</th>\n";
                }
                ?>
                    <th nowrap width='50'>Ψ</th>
                </tr>
            </thead>
            <tfoot>
                <!-- ���ߤϥեå����ϲ���ʤ� -->
            </tfoot>
            <tbody>
                <?php
                for ($r=0; $r<$rows; $r++) {
                    echo "<tr>\n";
                    echo "    <td nowrap class='pt10b' align='right'>", ($r + $offset + 1), "</td>    <!-- �ԥʥ�С���ɽ�� -->\n";
                    for ($i=0; $i<$num; $i++) {         // �쥳���ɿ�ʬ���֤�
                        // <!--  bgcolor='#ffffc6' �������� --> 
                        switch ($i) {
                        case 1:
                            echo "<td nowrap align='left' class='pt9'>", $res[$r][$i], "</td>\n";
                            break;
                        case 2:
                            echo "<td nowrap align='center' class='pt9'>", format_date($res[$r][$i]), "</td>\n";
                            break;
                        case 4:     // ����ñ��
                        case 5:     // �������
                            if ($res[$r][$i]) {
                                echo "<td nowrap align='right' class='pt9'>", number_format($res[$r][$i], 2), "</td>\n";
                            } else {
                                echo "<td nowrap align='right' class='pt9'>---</td>\n";
                            }
                            break;
                        default:
                            echo "<td nowrap align='center' class='pt9'>", $res[$r][$i], "</td>\n";
                        }
                        // <!-- ����ץ�<td rowspan='2' colspan='3' width='200' align='center' class='pt10b' bgcolor='#ffffc6'>  </td> -->
                    }
                    if ( ($res[$r][4] != 0) && ($res[$r][5]) ) {   // Ψ��ɽ��
                        echo "<td nowrap align='right' class='pt9'>", number_format(Uround($res[$r][4] / $res[$r][5], 4) * 100, 2), "</td>\n";
                    } else {
                        echo "<td nowrap align='center' class='pt9'>---</td>\n";
                    }
                    echo "</tr>\n";
                }
                ?>
            </tbody>
        </table>
            </td></tr>
        </table> <!----------------- ���ߡ�End ------------------>
    </center>
</body>
</html>
<?php
ob_end_flush();                 // ���ϥХåե���gzip���� END
?>
