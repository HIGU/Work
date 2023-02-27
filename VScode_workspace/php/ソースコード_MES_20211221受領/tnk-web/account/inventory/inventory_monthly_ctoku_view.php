<?php
//////////////////////////////////////////////////////////////////////////////
// ê�� ��� �ξȲ� (���ץ�����)  ������ UKWLIB/W#MVTNPT                    //
// Copyright (C) 2003-2010      Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp //
// Changed history                                                          //
// 2003/11/25 Created   inventory_monthly_ctoku_view.php                    //
//            SERIAL���Υ���ǥå���������� SQL���®������                //
// 2003/12/04 �ơ��֥��month_end��inventory_monthly���ѹ� ��۽�ɽ����     //
// 2003/12/09 �����ȴ�Ф����å��ɲ�(���ξ��ȴ�Ф�)�ȥơ��֥��ѹ�      //
//            �ơ��֥�̾ inventory_monthly �� inventory_monthly_ctoku       //
//   �ե�����̾�ѹ�inventory_month_ctoku_view��inventory_monthly_ctoku_view //
// 2004/01/14 $_SESSION['act_ym'] �� $_SESSION['ind_ym'] ���ѹ�             //
// 2004/05/12 �����ȥ�˥塼ɽ������ɽ�� �ܥ����ɲ� menu_OnOff($script)�ɲ� //
// 2005/02/09 MenuHeader class ����Ѥ��ƶ��̥�˥塼���ڤ�ǧ���������ѹ�   //
// 2005/08/20 set_focus()�ε�ǽ�� MenuHeader �Ǽ������Ƥ���Τ�̵��������   //
// 2007/03/08 �����ֹ楯��å��Ǻ߸�ͽ��Ȳ񡦷���Ȳ�˥�󥯤��ɲ�        //
// 2007/03/22 parts_stock_view.php �� parts_stock_history_Main.php ���ѹ�   //
// 2010/02/24 �ǡ�����Ф��Ѥ�ɽ�������10000�˰���ѹ�                ��ë //
//////////////////////////////////////////////////////////////////////////////
ini_set('error_reporting', E_ALL);          // E_ALL='2047' debug ��
// ini_set('display_errors', '1');             // Error ɽ�� ON debug �� ��꡼���女����
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
$menu->set_site(20, 32);                    // site_index=20(������˥塼) site_id=32(������ ê����׶�ۤ�����)
////////////// �꥿���󥢥ɥ쥹����
// $menu->set_RetUrl(INDUST_MENU);             // �̾�ϻ��ꤹ��ɬ�פϤʤ�
//////////// �����ȥ�̾(�������Υ����ȥ�̾�ȥե�����Υ����ȥ�̾)
$menu->set_title('���ץ������� ê����ۤξȲ�');
//////////// �ƽ����action̾�ȥ��ɥ쥹����
$menu->set_action('�߸�ͽ��',   INDUST . 'parts/parts_stock_plan/parts_stock_plan_Main.php');
$menu->set_action('�߸˷���',   INDUST . 'parts/parts_stock_history/parts_stock_history_Main.php');

//////////// JavaScript Stylesheet File ��ɬ���ɤ߹��ޤ���
$uniq = uniqid('target');

//////////// �о�ǯ������ (ǯ��Τߤ����)
if ( isset($_SESSION['ind_ym']) ) {
    $act_ym = $_SESSION['ind_ym'];
    $s_ymd  = $act_ym . '01';   // ������
    $e_ymd  = $act_ym . '99';   // ��λ��
} else {
    $_SESSION['s_sysmsg'] = '�о�ǯ����ꤵ��Ƥ��ޤ���';
    header('Location: ' . H_WEB_HOST . $menu->out_RetUrl());    // ľ���θƽи������
    exit();
}

//////////// ���ǤιԿ�
define('PAGE', '100');

//////////// �о�ǯ��ǥ��ץ�ȱ���Τߤ�ȴ�Ф����ǡ��������뤫�����å�����Ѥ���
//////////// SQL ʸ�� where ��� ���Ѥ��ʤ�
$search = "where invent_ym={$act_ym} and item='���ץ�����'";

//////////// ��ץ쥳���ɿ�����     (�оݥǡ����κ������ڡ�������˻���)
$query = sprintf("select sum_money_z, sum_money_t, sum_count from inventory_monthly_header %s", $search);
$res_sum = array();         // �����
if ( getResult($query, $res_sum) <= 0) {         // $maxrows �μ���
    $_SESSION['s_sysmsg'] .= "��ץ쥳���ɿ��μ����˼���";      // .= ��å��������ɲä���
    header('Location: ' . H_WEB_HOST . $menu->out_RetUrl());    // ľ���θƽи������
    exit();
}
$sum_kin_z = $res_sum[0][0];  // ��� ê�� ���(����)
$sum_kin   = $res_sum[0][1];  // ��� ê�� ���(����)
$maxrows   = $res_sum[0][2];  // ��ץ쥳���ɿ�

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

//////////// SQL ʸ�� where ��� ���Ѥ��ʤ�
$search = "where invent_ym={$act_ym}";
//////////// ���ץ�����ξȲ�ե�����κ�ɽ
$query = sprintf("
        select
            parts_no                as �����ֹ�,        -- 0
            substr(m.midsc,1,12)    as ����̾,          -- 1
            par_code                as ������,          -- 2
            tou_zai                 as ����߸�,        -- 3
            gai_tan                 as ����ñ��,        -- 4
            Uround(tou_zai * gai_tan, 0)
                                    as ������,        -- 5
            nai_tan                 as ���ñ��,        -- 6
            Uround(tou_zai * nai_tan, 0)
                                    as �����,        -- 7
            Uround(tou_zai * gai_tan, 0) + Uround(tou_zai * nai_tan, 0)
                                    as ���,            -- 8
            kouji_no                as �����ֹ�,        -- 9
            num_div                 as ������           --10
        from
            inventory_monthly_ctoku as inv
        left outer join
            miitem as m
        on inv.parts_no = m.mipn
        %s 
        order by ��� DESC
        offset %d limit %d
        ", $search, $offset, PAGE);       // ���� $search �ϻ��Ѥ��ʤ�
$res   = array();
$field = array();
if (($rows = getResultWithField2($query, $field, $res)) <= 0) {
    $_SESSION['s_sysmsg'] .= 'ê���Υǡ����������Ǥ��ޤ���!';
    header('Location: ' . H_WEB_HOST . $menu->out_RetUrl());    // ľ���θƽи������
    exit();
} else {
    $num = count($field);       // �ե�����ɿ�����
}

//////////// ɽ�������
$caption = "{$act_ym}��" . $menu->out_title() . "�����=" . number_format($sum_kin) . '�ߡ���۽硡' . number_format($maxrows) . "�� \n";
$menu->set_caption($caption);

////////////// HTML Header ����Ϥ��ƥ֥饦�����Υ���å��������
$menu->out_html_header();
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=EUC-JP">
<meta http-equiv="Content-Style-Type" content="text/css">
<title><?= $menu->out_title() ?></title>
<?= $menu->out_site_java()?>
<?= $menu->out_css()?>

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
function set_focus() {
    // document.body.focus();   // F2/F12������ͭ���������б�
    // document.mhForm.backwardStack.focus();  // �嵭��IE�ΤߤΤ���NN�б�
}
function win_open(url) {
    var w = 900;
    var h = 600;
    var left = (screen.availWidth  - w) / 2;
    var top  = (screen.availHeight - h) / 2;
    // window.open(url, 'view_win2', 'width='+w+',height='+h+',scrollbars=no,status=no,toolbar=no,location=no,menubar=no,top='+top+',left='+left);
    window.open(url, '', 'width='+w+',height='+h+',scrollbars=no,status=no,toolbar=no,location=no,menubar=no,top='+top+',left='+left);
}
// -->
</script>

<!-- �������륷���ȤΥե��������򥳥��� HTML���� �����Ȥ�����Ҥ˽���ʤ��������
<link rel='stylesheet' href='template.css?<?= $uniq ?>' type='text/css' media='screen'>
-->

<style type="text/css">
<!--
.pt9 {
    font-size:      9pt;
    font-weight:    normal;
    font-family: monospace;
}
.pt10b {
    font-size:      10pt;
    font-weight:    bold;
    font-family: monospace;
}
.pt11b {
    font-size:      11pt;
    font-weight:    bold;
    color:          blue;
}
th {
    background-color:yellow;
    color:blue;
    font:bold 10pt;
    font-family: monospace;
}
a {
    color: red;
}
a.link {
    color: blue;
}
a:hover {
    background-color: blue;
    color: white;
}
-->
</style>
</head>
<body onLoad='set_focus()'>
    <center>
<?= $menu->out_title_border()?>
        
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
                    <td nowrap align='center' class='pt11b'>
                        <?= $menu->out_caption() ?>
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
                        case 0:     // �����ֹ�˥�󥯤��ɲ�
                            echo "<td class='winbox' nowrap align='center'><div class='pt9'><a class='link' href='javascript:void(0)' onClick='win_open(\"{$menu->out_action('�߸˷���')}?targetPartsNo=" . urlencode($res[$r][$i]) . "&noMenu=yes\");' target='_self' style='text-decoration:none;'>{$res[$r][$i]}</a></div></td>\n";
                            break;
                        case 1:
                            echo "<td class='winbox' nowrap align='left'><div class='pt9'>{$res[$r][$i]}</div></td>\n";
                            break;
                        case 3:
                        case 5:
                        case 7:
                        case 8:
                            echo "<td class='winbox' nowrap align='right'><div class='pt9'>", number_format($res[$r][$i], 0), "</div></td>\n";
                            break;
                        case 4:
                        case 6:
                            echo "<td class='winbox' nowrap align='right'><div class='pt9'>", number_format($res[$r][$i], 2), "</div></td>\n";
                            break;
                        default:
                            echo "<td class='winbox' nowrap align='center'><div class='pt9'>{$res[$r][$i]}</div></td>\n";
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
<?= $menu->out_alert_java()?>
</html>
<?php
ob_end_flush();                 // ���ϥХåե���gzip���� END
?>
