<?php
//////////////////////////////////////////////////////////////////////////////
// ��ݥҥ��ȥ�ξȲ� ���ץ������� ������� �����                          //
// Copyright (C) 2004-2005 Kauzhiro.Kobayashi tnksys@nitto-kohki.co.jp      //
// Changed history                                                          //
// 2004/01/14 Created   payable_ctoku_view2.php                             //
//            ������(1)�����ʻųݣ�(2-5) ����(6)- �ι�׶�� ���������     //
//            ��˥��θ�����1 �����  01111 00222 99999������ʤ�           //
//            $_SESSION['act_ym'] �� $_SESSION['ind_ym'] ���ѹ�             //
// 2004/05/12 �����ȥ�˥塼ɽ������ɽ�� �ܥ����ɲ� menu_OnOff($script)�ɲ� //
// 2004/07/05 ���ʤ������ˤ�� ����̾������Υե�����ɤ��ɲ�             //
// 2004/12/07 �ǥ��쥯�ȥ���ز��� industry/payable ���ѹ�                //
// 2005/02/10 MenuHeader class ����Ѥ��ƶ��̥�˥塼���ڤ�ǧ���������ѹ�   //
// 2005/05/20 db_connect() �� funcConnect() ���ѹ� pgsql.php������Τ���    //
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
$menu->set_site(30, 10);                    // site_index=30(������˥塼) site_id=10(��ݼ��ӾȲ�Υ��롼��)
////////////// �꥿���󥢥ɥ쥹����
// $menu->set_RetUrl(INDUST_MENU);             // �̾�ϻ��ꤹ��ɬ�פϤʤ�
//////////// �����ȥ�̾(�������Υ����ȥ�̾�ȥե�����Υ����ȥ�̾)
$menu->set_title('�� �� �� �� �� �� ��');
//////////// �ƽ����action̾�ȥ��ɥ쥹����
// $menu->set_action('�����������',   INDUST . 'material/materialCost_view.php');

//////////// ���ǤιԿ�
define('PAGE', '100');

//////////// JavaScript Stylesheet File ��ɬ���ɤ߹��ޤ���
$uniq = uniqid('target');

/***************
//////////// �о�ǯ���������
$act_ymd = $_SESSION['ind_ymd'];
if ($act_ymd == '') {
    $act_ymd = date_offset(2);
}
***************/

// $_SESSION['act_ym'] = 200312;   // �ƥ�����
//////////// �о�ǯ������ (ǯ��Τߤ����)
if ( isset($_SESSION['ind_ym']) ) {
    $act_ym = $_SESSION['ind_ym'];
    $s_ymd  = $act_ym . '01';   // ������
    $e_ymd  = $act_ym . '99';   // ��λ��
} else {
    $_SESSION['s_sysmsg'] = '�о�ǯ����ꤵ��Ƥ��ޤ���!';
    header('Location: ' . H_WEB_HOST . $menu->out_RetUrl());
    exit();
}

/////////// begin �ȥ�󥶥�����󳫻�
if ($con = funcConnect()) {
    query_affected_trans($con, 'begin');
} else {
    $_SESSION['s_sysmsg'] .= 'funcConnect() error';
    header('Location: ' . H_WEB_HOST . $menu->out_RetUrl());
    exit();
}

//////////// SQL ʸ�� where ��� ���Ѥ��� 01111=�������칩�� 00222=��¤������ 99999=����
$search = sprintf("where act_date>=%d and act_date<=%d", $s_ymd, $e_ymd);

// ���ץ� ����
//////////// SQL ʸ�� where ��� ���Ѥ���
$search_kin = sprintf("%s and kamoku<=5 and paya.div='C' and kouji_no like 'SC%%'", $search);

//////////// ���������׶�� (����1��5)����6�ʾ�����
$query = sprintf("select    sum(Uround(order_price * siharai,0)),
                            count(*)
                    from
                            act_payable as paya
                    left outer join
                            order_plan
                    using(sei_no)
                    %s", $search_kin);
if ( getResultTrs($con, $query, $paya_ctoku) <= 0) {
    $_SESSION['s_sysmsg'] .= '���ץ��������� ��ۤμ����˼���';      // .= ��å��������ɲä���
    query_affected_trans($con, 'rollback');         // transaction rollback
    header('Location: ' . H_WEB_HOST . $menu->out_RetUrl());
    exit();
} else {
    $sum_kin = $paya_ctoku[0][0];
    $maxrows = $paya_ctoku[0][1];
}

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

//////////// ɽ�����Υǡ���ɽ���ѤΥ���ץ� Query & �����
$query = sprintf("
        select
            act_date            as ������,                  -- 00
            uke_no              as ����,                    -- 01
            uke_date            as ������,                  -- 02
            ken_date            as ������,                  -- 03
            vendor              as ȯ����,                  -- 04
            trim(name)          as ȯ����̾,                -- 05
            paya.parts_no       as �����ֹ�,                -- 06
            trim(substr(midsc, 1, 26))  as ����̾,          -- 07
            trim(mzist)         as ���,                    -- 08
            order_no            as ��ʸ��,                  -- 09
            koutei              as ����,                    -- 10
            order_price         as ȯ��ñ��,                -- 11
            siharai             as ��ʧ��,                  -- 12
            Uround(order_price * siharai,0) as ȯ����,    -- 13
            kouji_no            as �����ֹ�,                -- 14
            sei_no              as ��¤�ֹ�                 -- 15
        from
            (act_payable as paya left outer join vendor_master using(vendor))
        left outer join
                order_plan
        using(sei_no)
        left outer join
            miitem
        on paya.parts_no=mipn
        %s 
        ORDER BY vendor, uke_no, type_no, seq ASC
        offset %d limit %d
    ", $search_kin, $offset, PAGE);       // ���� $search �Ǹ���
$res   = array();
$field = array();
if (($rows = getResWithFieldTrs($con, $query, $field, $res)) <= 0) {
    $_SESSION['s_sysmsg'] .= sprintf("��ݶ�η׾���:%s ��<br>�ǡ���������ޤ���", $act_ym );
    query_affected_trans($con, 'rollback');         // transaction rollback
    header('Location: ' . H_WEB_HOST . $menu->out_RetUrl());
    exit();
} else {
    query_affected_trans($con, 'commit');         // transaction commit
    $num = count($field);       // �ե�����ɿ�����
}

//////////// ɽ�������
$caption = $act_ym . '����׶�ۡ�' . number_format($sum_kin) . '����׷����' . number_format($maxrows);
$menu->set_caption("���ץ������ʡ����ڤӽ�����ޤࡡ��$caption");

/////////// HTML Header ����Ϥ��ƥ���å��������
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
    document.mhForm.backwardStack.focus();  // �嵭��IE�ΤߤΤ���NN�б�
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
    font-family:    monospace;
}
.pt10b {
    font:           10pt;
    font-weight:    bold;
    font-family:    monospace;
}
.pt11b {
    font:           11pt;
    font-weight:    bold;
    color:          blue;
}
th {
    background-color:   yellow;
    color:              blue;
    font:               10pt;
    font-weight:        bold;
    font-family:        monospace;
}
-->
</style>
</head>
<body onLoad='set_focus()'>
    <center>
<?=$menu->out_title_border()?>
        
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
                        case 5:
                        case 7:
                        case 8:
                            echo "<td class='winbox' nowrap align='left'><div class='pt9'>{$res[$r][$i]}</div></td>\n";
                            break;
                        case 11:
                        case 12:
                            echo "<td class='winbox' nowrap align='right'><div class='pt9'>", number_format($res[$r][$i], 2), "</div></td>\n";
                            break;
                        case 13:
                            echo "<td class='winbox' nowrap align='right'><div class='pt9'>", number_format($res[$r][$i]), "</div></td>\n";
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
<?=$menu->out_alert_java()?>
</html>
<?php
ob_end_flush();     // ���ϥХåե�����gzip���� END
?>
