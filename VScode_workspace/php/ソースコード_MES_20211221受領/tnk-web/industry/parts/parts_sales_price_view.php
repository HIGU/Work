<?php
//////////////////////////////////////////////////////////////////////////////
// ñ���������������(����ñ��)����  ɽ������                             //
// Copyright (C) 2004-2013 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp      //
// Changed history                                                          //
// 2004/11/19 Created  parts_sales_price_view.php                           //
// 2004/12/02 �ǥ���������  border='1' cellspacing='0' cellpadding='3'>     //
// 2004/12/20 ��Ͽ���ʤ����Υե�����ɿ������� $num=0��$num = count($field) //
// 2005/05/13 ��åȤ��绻����Ƥ���Τ�����lot_no��ʬ����å��ֹ���ɲ�  //
// 2009/12/07 ���������ݰ���ˤ�ꡢɽ������Ͽ�ֹ�߽礫����Ͽ���߽碪      //
//            ��Ͽ�ֹ�߽���ѹ�CP00928-0����Ͽ�ֹ�999999�����ä�����  ��ë //
// 2013/01/30 �ǿ�����Ͽ���դ�����ݡ���åȤ�ʣ���ξ�礹�٤ƿ��դ�����  //
//            �褦���ѹ�                                               ��ë //
// 2013/05/27 �̲�ñ��ɽ�����ɲáʱ߰ʳ����ֻ���                            //
// 2013/06/21 SQL�Υ��顼����                                        ��ë //
//////////////////////////////////////////////////////////////////////////////
ini_set('error_reporting', E_ALL);          // E_ALL='2047' debug ��
ini_set('display_errors', '1');             // Error ɽ�� ON debug �� ��꡼���女����
// ini_set('implicit_flush', 'off');           // echo print �� flush �����ʤ�(�٤��ʤ뤿��) CLI CGI��
// ini_set('max_execution_time', 1200);        // ����¹Ի���=20ʬ CLI CGI��
ob_start('ob_gzhandler');                   // ���ϥХåե���gzip����
session_start();                            // ini_set()�μ��˻��ꤹ�뤳�� Script �Ǿ��

require_once ('../../function.php');        // define.php �� pgsql.php �� require_once ���Ƥ���
require_once ('../../tnk_func.php');        // TNK �˰�¸������ʬ�δؿ��� require_once ���Ƥ���
require_once ('../../MenuHeader.php');      // TNK ������ menu class
access_log();                               // Script Name �ϼ�ư����

///// TNK ���ѥ�˥塼���饹�Υ��󥹥��󥹤����
$menu = new MenuHeader(0);                  // ǧ�ڥ����å�0=���̰ʾ� �����=TOP_MENU �����ȥ�̤����

////////////// ����������
$menu->set_site(30, 999);                   // site_index=30(������˥塼) site_id=999(�����Ȥ򳫤�)
////////////// �꥿���󥢥ɥ쥹����
// $menu->set_RetUrl(SYS_MENU);                // �̾�ϻ��ꤹ��ɬ�פϤʤ�
//////////// �����ȥ�̾(�������Υ����ȥ�̾�ȥե�����Υ����ȥ�̾)
$menu->set_title('ñ��������������ʤξȲ�');
//////////// ɽ�������
// $menu->set_caption('�������');
//////////// �ƽ����action̾�ȥ��ɥ쥹����
// $menu->set_action('view',   INDUST . 'parts/parts_sales_price_view.php');

//////////// JavaScript Stylesheet File ��ɬ���ɤ߹��ޤ���
$uniq = uniqid('target');

//////////// GET & POST �ǡ����μ���
if (isset($_REQUEST['parts'])) {
    $parts_no = $_REQUEST['parts'];
    $_SESSION['cost_parts'] = $parts_no;
} else {
    $_SESSION['s_sysmsg'] .= '���ʤ����ꤵ��Ƥ��ޤ���';
    header('Location: ' . H_WEB_HOST . $menu->out_RetUrl());    // ľ���θƽи������
    exit();
}
if (isset($_REQUEST['regdate'])) {
    $regdate = $_REQUEST['regdate'];
    $_SESSION['cost_regdate'] = $regdate;
} else {
    $regdate = $_SESSION['cost_regdate'];       // ���ꤵ��Ƥ��ʤ����ϥ��å���󤫤�
}
if (isset($_REQUEST['sales_rate'])) {
    $sales_rate = $_REQUEST['sales_rate'];
    $_SESSION['cost_sales_rate'] = $sales_rate;
} else {
    $sales_rate = $_SESSION['cost_sales_rate']; // ���ꤵ��Ƥ��ʤ����ϥ��å���󤫤�
}

//////////// ɽ�������
$query = "select midsc from miitem where mipn='{$parts_no}'";
if (getUniResult($query, $name) <= 0) {
    $_SESSION['s_sysmsg'] .= '�ޥ�����̤��Ͽ';    // ������parts_cost_form.php�ǥޥ������Υ����å���Ԥ��褦���ѹ�ͽ��
    header('Location: ' . H_WEB_HOST . $menu->out_RetUrl());    // ľ���θƽи������
    exit();
}
$caption = "�����ֹ桧{$parts_no}������̾��{$name}<br>�������" . format_date($regdate) . "����������졼�ȡ�{$sales_rate}";

//////////// ɽ�����Υǡ���ɽ���� Query & �����
$query = "select as_regdate                                 as ��Ͽ��       -- 0
                , reg_no                                    as ��Ͽ�ֹ�     -- 1
                , CASE
                    WHEN kubun = '1' THEN '��³'
                    WHEN kubun = '2' THEN '����'
                    WHEN kubun = '3' THEN '����'
                  END                                       as ��Ͽ��ʬ     -- 2
                , sum(lot_cost)                             as ñ��         -- 3
                , Uround(sum(lot_cost)*{$sales_rate}, 2)    as ����ñ��     -- 4
                , lot_no                                    as ��å��ֹ�   -- 5
            from
                parts_cost_history
            where
                parts_no='{$parts_no}'
                and
                vendor!='88888'
                -- and
                -- as_regdate<={$regdate}
            group by
                reg_no, as_regdate, lot_no, kubun
            having
                (kubun='1' OR kubun='2')    -- GROUP���줿ʪ�˾�������
            order by
                as_regdate DESC, reg_no DESC
            limit 50
";
$res   = array();
$field = array();
if (($rows = getResultWithField2($query, $field, $res)) <= 0) {
    $_SESSION['s_sysmsg'] .= sprintf("<font color='yellow'>�����ֹ�:%s <br>��³������Ǥ�ñ�����򤬤���ޤ���</font>", $parts_no);
    $num = count($field);       // �ե�����ɿ�����
    // $num = 0;
} else {
    $num = count($field);       // �ե�����ɿ�����
    $set_rows   = (-1);          // �����(-1=���åȤ��ʤ�����)
    $set_first  = '';            // �����פ������ɤ�����Ƚ��
    $set_second = '';            // �����ܹ��פ������ɤ�����Ƚ��
    // ��åȤ�ʣ����ä��Ȥ����б�
    $set_rows1  = (-1);          // �����(-1=���åȤ��ʤ�����)
    $set_rows2  = (-1);          // �����(-1=���åȤ��ʤ�����)
    $set_rows3  = (-1);          // �����(-1=���åȤ��ʤ�����)
    $set_date   = 0;             // ���ץ쥳���ɤ�AS��Ͽ��
    $set_reg    = 0;             // ���ץ쥳���ɤ���Ͽ�ֹ�
    //if ($regdate > 0) {         // ����������åȤ���Ƥ����
    //    for ($i=0; $i<$rows; $i++) {
    //        if ($res[$i][0] <= $regdate) {  // ��Ͽ����������ʲ��ˤʤä���
    //            $set_rows = $i;     // ���פ����쥳���ɤ򥻥åȤ���
    //            break;
    //        }
    //    }
    //}
    if ($regdate > 0) {                             // ����������åȤ���Ƥ����
        for ($i=0; $i<$rows; $i++) {
            if ($res[$i][0] <= $regdate) {          // ��Ͽ����������ʲ��ˤʤä���
                if ($set_first == '1') {            // �����פ��Ƥ��뤫
                    if ($set_second == '1') {       // �����ܹ��פ��Ƥ��뤫
                        if ($set_date == $res[$i][0] && $set_reg == $res[$i][1]) {  // ����AS��Ͽ������Ͽ�ֹ椬�����ܤ�Ʊ����
                            $set_rows3 = $i;        // �����ܹ��פ����쥳���ɤ򥻥åȤ���
                            break;                  // ��åȤϣ��ޤǤʤΤǣ�����פ����齪λ
                        } else {
                            break;                  // �����ܤϰ㤦ñ����Ͽ�ʤΤǽ�λ
                        }
                    } else {
                        if ($set_date == $res[$i][0] && $set_reg == $res[$i][1]) {  // ����AS��Ͽ������Ͽ�ֹ椬�����ܤ�Ʊ����
                            $set_rows2  = $i;       // �����ܹ��פ����쥳���ɤ򥻥åȤ���
                            $set_second = '1';      // �����ܹ��ץե饰��Ω�Ƥ�
                        } else {
                            break;                  // �����ܤϰ㤦ñ����Ͽ�ʤΤǽ�λ
                        }
                    }
                } else {
                    $set_rows1 = $i;                // �����פ����쥳���ɤ򥻥åȤ���
                    $set_date  = $res[$i][0];       // �����׻���AS��Ͽ���򥻥å�
                    $set_reg   = $res[$i][1];       // �����׻�����Ͽ�ֹ�򥻥å�
                    $set_first = '1';               // �����ץե饰��Ω�Ƥ�
                }
            }
        }
    }
    //if ($set_rows == (-1) ) $set_rows = 0;
    //if ($set_rows1 == (-1) ) $set_rows1 = 0;
    if ($set_rows1 == (-1)) {                   // ñ����Ͽ����� �����˷�³���������Ͽ��̵�����
        for ($i=0; $i<$rows; $i++) {
            if ($set_first == '1') {            // �쥳����0�򥻥åȤ�����
                if ($set_second == '1') {       // �쥳����1�ν�����Ԥä���
                    if ($set_date == $res[2][0] && $set_reg == $res[2][1]) {  // �쥳����0��AS��Ͽ������Ͽ�ֹ椬�쥳����2��Ʊ����
                        $set_rows3 = 2;         // ɬ���쥳����2
                        break;                  // ��åȤϣ��ޤǤʤΤǽ�λ
                    } else {
                        break;                  // �쥳����2��0�Ȱ㤦ñ����Ͽ�ʤΤǽ�λ
                    }
                } else {
                    if ($set_date == $res[1][0] && $set_reg == $res[1][1]) {  // �쥳����0��AS��Ͽ������Ͽ�ֹ椬�쥳����1��Ʊ����
                        $set_rows2  = 1;        // ɬ���쥳����1
                        $set_second = '1';      // �쥳����1�Υ��åȥե饰��Ω�Ƥ�
                    } else {
                        break;                  // �쥳����1��0�Ȱ㤦ñ����Ͽ�ʤΤǽ�λ
                    }
                }
            } else {
                $set_rows1 = 0;                 // ɬ���쥳����0
                $set_date  = $res[0][0];        // �쥳����0��AS��Ͽ���򥻥å�
                $set_reg   = $res[0][1];        // �쥳����0����Ͽ�ֹ�򥻥å�
                $set_first = '1';               // �쥳����0�Υ��åȥե饰��Ω�Ƥ�
            }
        }
    }
}
//////////// ɽ�����Υǡ���ɽ���� Query & ����� �졼�ȶ�ʬ����
for ($r=0; $r<$rows; $r++) {
    $query_r = "select        
                    h.rate_div                as �졼�ȶ�ʬ   -- 0
                    , d.rate_sign               as �졼�ȵ���   -- 1
                    , d.rate_name               as ̾��         -- 2
                    , d.rev_par                 as ����Ψ       -- 3
                from
                    parts_rate_history as h
                left outer join
                    rate_div_master as d
                ON h.rate_div=d.rate_div
                where
                    h.parts_no='{$parts_no}' and h.reg_no='{$res[$r][1]}'
                limit 1
    ";
    $res_r   = array();
    $field_r = array();
    if (($rows_r = getResultWithField2($query_r, $field_r, $res_r)) <= 0) {
        $rate_div[$r]  = '\\';       // �졼�ȶ�ʬ��Ͽ���ʤ���б�
        $rate_name[$r] = '���ܱ�';   // �졼�ȶ�ʬ��Ͽ���ʤ���б�
        $rev_par[$r]   = 1.000;      // �졼�ȶ�ʬ��Ͽ���ʤ���б�
    } else {
        $rate_div[$r]  = $res_r[0][1];       // �졼�ȵ���
        $rate_name[$r] = $res_r[0][2];       // ̾��
        $rev_par[$r]   = $res_r[0][3];       // ����Ψ
    }
}
/////////// HTML Header ����Ϥ��ƥ���å��������
$menu->out_html_header();
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=EUC-JP">
<meta http-equiv="Content-Style-Type" content="text/css">
<title><?= $menu->out_title() ?></title>
<?php // $menu->out_site_java() ?>
<?= $menu->out_css() ?>

<!--    �ե��������ξ��
<script language='JavaScript' src='template.js?<?= $uniq ?>'>
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
function set_focus(){
//    document.form_name.element_name.focus();      // ������ϥե����ब������ϥ����Ȥ򳰤�
//    document.form_name.element_name.select();
}
// -->
</script>

<!-- �������륷���ȤΥե��������򥳥��� HTML���� �����Ȥ�����Ҥ˽���ʤ��������
<link rel='stylesheet' href='<?= MENU_FORM . '?' . $uniq ?>' type='text/css' media='screen'>
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
.margin0 {
    margin:0%;
}
form {
    margin:0%;
}
th {
    background-color: blue;
    color:            yellow;
    font-size:        14pt;
    font-weight:      bold;
    font-family:      monospace;
}
td {
    font-size:   12pt;
    font-weight: bold;
    /* font-family: monospace; */
}
.winbox {
    border-style: solid;
    border-width: 1px;
    border-top-color:       #FFFFFF;
    border-left-color:      #FFFFFF;
    border-right-color:     #999999;
    border-bottom-color:    #999999;
    /* background-color:#d6d3ce; */
}
.winboxr {
    border-style: solid;
    border-width: 1px;
    border-top-color:       #FFFFFF;
    border-left-color:      #FFFFFF;
    border-right-color:     #999999;
    border-bottom-color:    #999999;
    color:                  red;
    /* background-color:#d6d3ce; */
}
.winbox_field {
    border-style: solid;
    border-width: 1px;
    border-top-color:       #999999;
    border-left-color:      #999999;
    border-right-color:     #FFFFFF;
    border-bottom-color:    #FFFFFF;
    background-color:#d6d3ce;
}
-->
</style>
</head>
<body class='margin0' onLoad='set_focus()'>
    <center>
<?= $menu->out_title_border() ?>
        
        <table width='100%' align='center' border='0'>
            <tr>
                <td class='pt12b' align='center'>
                    <?= $caption, "\n"?>
                </td>
            </tr>
        </table>
        
        <!--------------- ����������ʸ��ɽ��ɽ������ -------------------->
        <table bgcolor='#d6d3ce' align='center' border='1' cellspacing='0' cellpadding='3'>
            <tr><td> <!----------- ���ߡ�(�ǥ�������) ------------>
        <table class='winbox_field' width='100%'align='center' border='1' cellspacing='0' cellpadding='3'>
            <thead>
                <!-- �ơ��֥� �إå�����ɽ�� -->
                <tr>
                    <th class='winbox' nowrap width='10'>No.</th>        <!-- �ԥʥ�С���ɽ�� -->
                <?php
                for ($i=0; $i<$num; $i++) {             // �ե�����ɿ�ʬ���֤�
                ?>
                    <th class='winbox' nowrap><?= $field[$i] ?></th>
                <?php
                }
                ?>
                    <th class='winbox' nowrap>�̲�ñ��</th>
                </tr>
            </thead>
            <tfoot>
                <!-- ���ߤϥեå����ϲ���ʤ� -->
            </tfoot>
            <tbody>
                <?php
                for ($r=0; $r<$rows; $r++) {
                    //if ($set_rows == $r) {
                    //    echo "<tr bgcolor='yellow'>\n";
                    //} else {
                    //    echo "<tr>\n";
                    //}
                    if ($set_rows1 == $r) {
                        echo "<tr bgcolor='yellow'>\n";
                    } elseif ($set_rows2 == $r) {
                        echo "<tr bgcolor='yellow'>\n";
                    } elseif ($set_rows3 == $r) {
                        echo "<tr bgcolor='yellow'>\n";
                    } else {
                        echo "<tr>\n";
                    }
                    echo "    <td nowrap class='winbox' align='right'>" . ($r + 1) . "</td>    <!-- �ԥʥ�С���ɽ�� -->\n";
                    for ($i=0; $i<$num; $i++) {         // �쥳���ɿ�ʬ���֤�
                        // <!--  bgcolor='#ffffc6' �������� --> 
                        switch ($i) {
                        case 0:     // ��Ͽ��
                            echo "<td nowrap align='center' class='winbox'>" . format_date($res[$r][$i]) . "</td>\n";
                            break;
                        case 1:     // ��Ͽ�ֹ�
                            echo "<td nowrap align='center' class='winbox'>" . $res[$r][$i] . "</td>\n";
                            break;
                        case 2:     // ��Ͽ��ʬ
                            echo "<td nowrap align='center' class='winbox'>" . $res[$r][$i] . "</td>\n";
                            break;
                        case 3:     // ñ��
                            echo "<td width='80' nowrap align='right' class='winbox'>" . number_format($res[$r][$i], 2) . "</td>\n";
                            break;
                        case 4:    // ����ñ��
                            echo "<td width='80' nowrap align='right' class='winbox'>" . number_format($res[$r][$i], 2) . "</td>\n";
                            break;
                        case 5:    // ��å��ֹ�
                            echo "<td width='100' nowrap align='center' class='winbox'>{$res[$r][$i]}</td>\n";
                            if($rate_div[$r] == '\\') {
                                echo "<td width='100' nowrap align='center' class='winbox'>{$rate_div[$r]}</td>\n";
                            } else {
                                echo "<td width='100' nowrap align='center' class='winboxr'>{$rate_div[$r]}</td>\n";
                            }
                            break;
                        default:
                            echo "<td nowrap align='center' class='winbox'>" . $res[$r][$i] . "</td>\n";
                        }
                        // <!-- ����ץ�<td rowspan='2' colspan='3' width='200' align='center' class='pt10b' bgcolor='#ffffc6'>  </td> -->
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
<?=$menu->out_alert_java()?>
</html>
<?php
ob_end_flush();                 // ���ϥХåե���gzip���� END
?>
