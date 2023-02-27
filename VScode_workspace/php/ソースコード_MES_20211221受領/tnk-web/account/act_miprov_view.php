<?php
//////////////////////////////////////////////////////////////////////////////
// �ٵ빹���ǡ����ξȲ� �� �����å���  ������ UKWLIB/W#MIPROV               //
// Copyright(C) 2003-2015 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp       //
// Changed history                                                          //
// 2003/11/20 Created   act_miprov_view.php (provide = ���뤹��)            //
//            miitem �� left outer join ����Ȥ��ʤ��٤��ʤ� ��Ƥ��         //
//            key field��char(9)���Ǻ��������(�����Ϥ���) ::char(9)����    //
// 2003/11/21 where ��� delete='' ���ɲ�  �����ʬ�Υ����å�ȴ���б�       //
// 2004/05/12 �����ȥ�˥塼ɽ������ɽ�� �ܥ����ɲ� menu_OnOff($script)�ɲ� //
// 2005/02/08 MenuHeader class ����Ѥ��ƶ��̥�˥塼���ڤ�ǧ���������ѹ�   //
// 2005/08/20 set_focus()�ε�ǽ�� MenuHeader �Ǽ������Ƥ���Τ�̵��������   //
// 2015/10/19 Ȭ�����ɸ�����0�򽸷פ��褦�Ȥ����������⤽��Web�˼������  //
//            ���ʤ��١������ᤷ����                                   ��ë //
//////////////////////////////////////////////////////////////////////////////
ini_set('error_reporting', E_ALL);          // E_ALL='2047' debug ��
ini_set('display_errors', '1');             // Error ɽ�� ON debug �� ��꡼���女����
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
$menu->set_site(20, 11);                    // site_index=30(������˥塼)20=(������˥塼) site_id=11(�ٵ����)
////////////// �꥿���󥢥ɥ쥹����
// $menu->set_RetUrl(INDUST_MENU);             // �̾�ϻ��ꤹ��ɬ�פϤʤ�
//////////// �����ȥ�̾(�������Υ����ȥ�̾�ȥե�����Υ����ȥ�̾)
$menu->set_title('�� �� �� �� �� �� ��');
//////////// �ƽ����action̾�ȥ��ɥ쥹����
// $menu->set_action('test_template',   INDUST . 'log_view/php_error_log.php');

//////////// JavaScript Stylesheet File ��ɬ���ɤ߹��ޤ���
$uniq = uniqid('target');

//////////// �о�ǯ���������
$act_ymd = $_SESSION['act_ymd'];    // ind_branch.php�����ꤵ��Ƥ���
if ($act_ymd == '') {
    $act_ymd = date_offset(2);
}
//////////// ɽ�������
$menu->set_caption(format_date($act_ymd) . '��' . $menu->out_title());

//////////// ���ǤιԿ�
define('PAGE', '20');

//////////// SQL ʸ�� where ��� ���Ѥ���
$search = sprintf("where act_date=%d and delete=''", $act_ymd);

//////////// ��ץ쥳���ɿ�����     (�оݥǡ����κ������ڡ�������˻���)
$query = sprintf('select count(*) from act_miprov %s', $search);
if ( getUniResult($query, $maxrows) <= 0) {         // $maxrows �μ���
    $_SESSION['s_sysmsg'] .= "��ץ쥳���ɿ��μ����˼���";      // .= ��å��������ɲä���
}

//////////// SQL ʸ�� where ��� ���Ѥ��� 01111=�������칩�� 00222=��¤������ 99999=���� �������=2(ͭ��)
$search_kin = sprintf("where act_date=%d and vendor !='01111' and vendor !='00222' and vendor !='99999' and mtl_cond='2'", $act_ymd);

//////////// ���������׶��
$query = sprintf("select sum(Uround(prov * prov_tan, 0)) from act_miprov %s", $search_kin);
if ( getUniResult($query, $sum_kin) <= 0) {
    $_SESSION['s_sysmsg'] .= '��׶�ۤμ����˼���';      // .= ��å��������ɲä���
}

//////////// ���������׶�� ����1
$query = sprintf("select sum(Uround(prov * prov_tan, 0)) from act_miprov %s and kamoku=1", $search_kin);
getUniResult($query, $kamoku1_kin);

//////////// ���������׶�� ����1�ǥ�˥�  ��������2-5�ؿ�ʬ����
$query = sprintf("select sum(Uround(prov * prov_tan, 0)) from act_miprov %s and kamoku=1 and div='L'", $search_kin);
getUniResult($query, $kamoku1L_kin);
$kamoku1_kin = ($kamoku1_kin - $kamoku1L_kin);      // ���Ū�˲���1�ϥ��ץ�Τ�

//////////// ���������׶�� ����2-5
$query = sprintf("select sum(Uround(prov * prov_tan, 0)) from act_miprov %s 
                  and kamoku>=2 and kamoku<=5", $search_kin);
getUniResult($query, $kamoku2_5_kin);
$kamoku2_5_kin = ($kamoku2_5_kin + $kamoku1L_kin);

//////////// ���������׶�� ����6�ʾ�
$query = sprintf("select sum(Uround(prov * prov_tan, 0)) from act_miprov %s and kamoku>=6", $search_kin);
getUniResult($query, $kamoku6__kin);

//////////// �ڡ������ե��å�����
if ( isset($_POST['forward']) ) {                       // ���Ǥ������줿
    $_SESSION['offset'] += PAGE;
    if ($_SESSION['offset'] >= $maxrows) {
        $_SESSION['offset'] -= PAGE;
        if ($_SESSION['s_sysmsg'] == "") {
            $_SESSION['s_sysmsg'] .= "<font color='yellow'>���ǤϤ���ޤ���</font>";
        } else {
            $_SESSION['s_sysmsg'] .= "<br><font color='yellow'>���ǤϤ���ޤ���</font>";
        }
    }
} elseif ( isset($_POST['backward']) ) {                // ���Ǥ������줿
    $_SESSION['offset'] -= PAGE;
    if ($_SESSION['offset'] < 0) {
        $_SESSION['offset'] = 0;
        if ($_SESSION['s_sysmsg'] == "") {
            $_SESSION['s_sysmsg'] .= "<font color='yellow'>���ǤϤ���ޤ���</font>";
        } else {
            $_SESSION['s_sysmsg'] .= "<br><font color='yellow'>���ǤϤ���ޤ���</font>";
        }
    }
} elseif ( isset($_GET['page_keep']) ) {               // ���ߤΥڡ�����ݻ�����
    $offset = $_SESSION['offset'];
} else {
    $_SESSION['offset'] = 0;                            // ���ξ��ϣ��ǽ����
}
$offset = $_SESSION['offset'];

//////////// �ٵ�ɼ�Υ����å��ꥹ�Ⱥ��� Query & �����
$query = sprintf("
        SELECT
            act_date    as ������,
            prov_no     as �ٵ��ֹ�,
            vendor      as ȯ����,
            substr(name, 1, 10)
                        as ȯ����̾,
            CASE
                WHEN trim(_sei_no) = '' THEN '---'
                ELSE _sei_no
            END         as �ײ��ֹ�,
            parts_no    as �����ֹ�,
            CASE
                WHEN trim(parts_name) != '' THEN parts_name
                WHEN trim(midsc) != '' THEN substr(midsc,1,12)
                ELSE '-----'
            END         as ����̾,
            mtl_cond    as ���,
            require     as ɬ�׿�,
            prov        as �ٵ��,
            prov_tan    as ñ��,
            Uround(prov * prov_tan, 0) as ���,
            CASE
                WHEN trim(mpvpn) = '' THEN '---'
                ELSE mpvpn
            END         as ȯ���ֹ� --,
            --zai_kubun   as �ߵ�,
            --kamoku      as ����,
            --div         as ��
        FROM
            act_miprov left outer join vendor_master using(vendor)
        LEFT OUTER JOIN
             miitem ON parts_no=mipn
        %s 
        ORDER BY div, prov_no OFFSET %d LIMIT %d
        
    ", $search, $offset, PAGE);       // ���� $search �Ǹ���
$res   = array();
$field = array();
if (($rows = getResultWithField2($query, $field, $res)) <= 0) {
    $_SESSION['s_sysmsg'] .= sprintf("�ٵ�ɼ�η׾���:%s ��<br>�ǡ���������ޤ���", format_date($act_ymd) );
    header('Location: ' . H_WEB_HOST . $menu->out_RetUrl());    // ľ���θƽи������
    exit();
} else {
    $num = count($field);       // �ե�����ɿ�����
}

/////////////// HTML Header ����Ϥ��ƥ���å��������
$menu->out_html_header();
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=EUC-JP">
<meta http-equiv="Content-Style-Type" content="text/css">
<title><?= $menu->out_title() ?></title>
<?=$menu->out_site_java()?>
<?=$menu->out_css()?>

<!--    �ե��������ξ��
<script language='JavaScript' src='template.js?<?= $uniq ?>'></script>
-->

<script language="JavaScript">
<!--
/* ����ʸ�����������ɤ��������å� */
function isDigit(str) {
    var len=str.length;
    var c;
    for (i=1; i<len; i++) {
        c = str.charAt(i);
        if ((c < "0") || (c > "9")) {
            return true;
        }
    }
    return false;
}
/* ������ϥե�����Υ�����Ȥ˥ե������������� */
function set_focus(){
    // document.body.focus();   // F2/F12������ͭ���������б�
    // document.mhForm.backwardStack.focus();  // �嵭��IE�ΤߤΤ���NN�б�
}
// -->
</script>

<!-- �������륷���ȤΥե��������򥳥��� HTML���� �����Ȥ�����Ҥ˽���ʤ��������
<link rel='stylesheet' href='template.css?<?= $uniq ?>' type='text/css' media='screen'>
-->

<style type="text/css">
<!--
.pt9 {
    font:normal 9pt;
    font-family: monospace;
}
.pt10b {
    font:bold 10pt;
    font-family: monospace;
}
.pt11b {
    font:bold 11pt;
}
th {
    background-color:yellow;
    color:blue;
    font:bold 10pt;
    font-family: monospace;
}
.winbox {
    border-style:           solid;
    border-width:           1px;
    border-top-color:       #FFFFFF;
    border-left-color:      #FFFFFF;
    border-right-color:     #bdaa90;
    border-bottom-color:    #bdaa90;
    /* background-color:#d6d3ce; */
}
.winbox_field {
    border-style:           solid;
    border-width:           1px;
    border-top-color:       #bdaa90;
    border-left-color:      #bdaa90;
    border-right-color:     #FFFFFF;
    border-bottom-color:    #FFFFFF;
    /* background-color:#d6d3ce; */
}
-->
</style>
</head>
<body onLoad='set_focus()' style='overflow-y:hidden;'>
    <center>
<?=$menu->out_title_border()?>
        
        <table width='250' border='0' cellspacing='0' cellpadding='0'>
            <tr>
                <td nowrap align='left' class='pt10b'>
                    �硡�ס��⡡��
                </td>
                <td nowrap align='right' class='pt11b'>
                    <?= number_format($sum_kin) . "\n" ?>
                </td>
            </tr>
            <tr>
                <td nowrap align='left' class='pt10b'>
                    �� �� �� 1
                </td>
                <td nowrap align='right' class='pt11b'>
                    <?= number_format($kamoku1_kin) . "\n" ?>
                </td>
            </tr>
            <tr>
                <td nowrap align='left' class='pt10b'>
                    ���ʻųݣ�2��5
                </td>
                <td nowrap align='right' class='pt11b'>
                    <?= number_format($kamoku2_5_kin) . "\n" ?>
                </td>
            </tr>
            <tr>
                <td nowrap align='left' class='pt10b'>
                    ����¾ 6��
                </td>
                <td nowrap align='right' class='pt11b'>
                    <?= number_format($kamoku6__kin) . "\n" ?>
                </td>
            </tr>
        </table>
        
        <!----------------- ������ ���� ���� �Υե����� ---------------->
        <table width='100%' border='0' cellspacing='0' cellpadding='0'>
            <form name='page_form' method='post' action='<?= $menu->out_self() ?>'>
                <tr>
                    <td align='left'>
                        <table align='left' border='3' cellspacing='0' cellpadding='0'>
                            <td align='left'>
                                <input class='pt10b' type='submit' name='backward' value='����'>
                            </td>
                        </table>
                    </td>
                    <td nowrap align='center' class='caption_font'>
                        <?= $menu->out_caption(), "\n" ?>
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
        <table bgcolor='#d6d3ce' align='center' border='1' cellspacing='0' cellpadding='3'>
            <tr><td> <!----------- ���ߡ�(�ǥ�������) ------------>
        <table class='winbox_field' width='100%' bgcolor='#d6d3ce' align='center' border='1' cellspacing='0' cellpadding='3'>
            <THEAD>
                <!-- �ơ��֥� �إå�����ɽ�� -->
                <tr>
                    <th class='winbox' nowrap width='10'>No</th>        <!-- �ԥʥ�С���ɽ�� -->
                <?php
                for ($i=0; $i<$num; $i++) {             // �ե�����ɿ�ʬ���֤�
                ?>
                    <th class='winbox' nowrap><?= $field[$i] ?></th>
                <?php
                }
                ?>
                </tr>
            </THEAD>
            <TFOOT>
                <!-- ���ߤϥեå����ϲ���ʤ� -->
            </TFOOT>
            <TBODY>
                        <!--  bgcolor='#ffffc6' �������� -->
                        <!-- ����ץ�<td rowspan='2' colspan='3' width='200' align='center' class='pt10b' bgcolor='#ffffc6'>  </td> -->
                <?php
                for ($r=0; $r<$rows; $r++) {
                ?>
                    <tr>
                        <td class='winbox' nowrap align='right'><div class='pt10b'><?= ($r + $offset + 1) ?></div></td>    <!-- �ԥʥ�С���ɽ�� -->
                    <?php
                    for ($i=0; $i<$num; $i++) {         // �쥳���ɿ�ʬ���֤�
                        switch ($i) {
                        case 3:
                        case 6:
                            echo "<td class='winbox' nowrap align='left'><div class='pt9'>{$res[$r][$i]}</div></td>\n";
                            break;
                        case 8:
                        case 9:
                            echo "<td class='winbox' nowrap align='right'><div class='pt9'>", number_format($res[$r][$i], 3), "</div></td>\n";
                            break;
                        case 10:
                            echo "<td class='winbox' nowrap align='right'><div class='pt9'>", number_format($res[$r][$i], 2), "</div></td>\n";
                            break;
                        case 11:
                            echo "<td class='winbox' nowrap align='right'><div class='pt9'>", number_format($res[$r][$i]), "</div></td>\n";
                            break;
                        default:
                            if ($res[$r][$i] != '         ') {  // �����ֹ������
                                echo "<td class='winbox' nowrap align='center'><div class='pt9'>{$res[$r][$i]}</div></td>\n";
                            } else {
                                echo "<td class='winbox' nowrap align='center'><div class='pt9'>&nbsp;</div></td>\n";
                            }
                        }
                    }
                    ?>
                    </tr>
                <?php
                }
                ?>
            </TBODY>
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
