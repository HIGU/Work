<?php
//////////////////////////////////////////////////////////////////////////////
// ���������ξȲ� ����������Υ��ޥ꡼�Ȳ�(ǯ��ˤ����ֻ���)              //
// Copyright (C) 2016-     Norihisa.Ohya norihisa_ooya@nitto-kohki.co.jp    //
// Changed history                                                          //
// 2016/01/29 Created inspection_date_summary.php                           //
//////////////////////////////////////////////////////////////////////////////
ini_set('error_reporting', E_ALL);          // E_ALL='2047' debug ��
// ini_set('display_errors', '1');             // Error ɽ�� ON debug �� ��꡼���女����
// ini_set('implicit_flush', 'off');           // echo print �� flush �����ʤ�(�٤��ʤ뤿��) CLI CGI��
// ini_set('max_execution_time', 1200);        // ����¹Ի���=20ʬ CLI CGI��
ob_start('ob_gzhandler');                   // ���ϥХåե���gzip����
session_start();                            // ini_set()�μ��˻��ꤹ�뤳�� Script �Ǿ��

require_once ('../../../function.php');        // define.php �� pgsql.php �� require_once ���Ƥ���
require_once ('../../../tnk_func.php');        // TNK �˰�¸������ʬ�δؿ��� require_once ���Ƥ���
require_once ('../../../MenuHeader.php');      // TNK ������ menu class
require_once ('../../../ControllerHTTP_Class.php');    // TNK ������ MVC Controller Class
access_log();                               // Script Name �ϼ�ư����

///// TNK ���ѥ�˥塼���饹�Υ��󥹥��󥹤����
$menu = new MenuHeader(0);                  // ǧ�ڥ����å�0=���̰ʾ� �����=TOP_MENU �����ȥ�̤����

////////////// ����������
$menu->set_site(30, 10);                    // site_index=30(������˥塼) site_id=10(��ݼ��ӾȲ�Υ��롼��)
////////////// �꥿���󥢥ɥ쥹����
// $menu->set_RetUrl(INDUST_MENU);             // �̾�ϻ��ꤹ��ɬ�פϤʤ�
//////////// �����ȥ�̾(�������Υ����ȥ�̾�ȥե�����Υ����ȥ�̾)
$menu->set_title('�� �� �� �� �� �� �� �� �� �� ��');
//////////// �ƽ����action̾�ȥ��ɥ쥹����
// $menu->set_action('�����������',   INDUST . 'material/materialCost_view.php');

$menu->set_action('��ݼ��ӾȲ�',   INDUST . 'order/inspection_date/inspection_date_view.php');

//////////// JavaScript Stylesheet File ��ɬ���ɤ߹��ޤ���
$uniq = uniqid('target');

//////////// ���å����Υ��󥹥��󥹤�����
$session = new Session();

//////////// �������ե����फ���POST�ǡ�������
if (isset($_REQUEST['parts_no'])) {
    $parts_no = $_REQUEST['parts_no'];
    $_SESSION['paya_parts_no'] = $parts_no;
} else {
    if (isset($_SESSION['paya_parts_no'])) {
        $parts_no = $_SESSION['paya_parts_no'];
    } else {
        $parts_no = '';
    }
}
if (isset($_REQUEST['div'])) {
    $div = $_REQUEST['div'];
    $_SESSION['payable_div'] = $div;
} else {
    if (isset($_SESSION['payable_div'])) {
        $div = $_SESSION['payable_div'];
    } else {
        $div = ' ';
    }
}
if (isset($_REQUEST['vendor'])) {
    $vendor = $_REQUEST['vendor'];
    $_SESSION['paya_vendor'] = $vendor;
} else {
    if (isset($_SESSION['paya_vendor'])) {
        $vendor = $_SESSION['paya_vendor'];
    } else {
        $vendor = '';
    }
}
if (isset($_REQUEST['kamoku'])) {
    $kamoku = $_REQUEST['kamoku'];
    $_SESSION['paya_kamoku'] = $kamoku;
} else {
    if (isset($_SESSION['paya_kamoku'])) {
        $kamoku = $_SESSION['paya_kamoku'];
    } else {
        $kamoku = '';
    }
}
if (isset($_REQUEST['ken_num'])) {
    $ken_num = $_REQUEST['ken_num'];
    $_SESSION['paya_ken_num'] = $ken_num;
} else {
    if (isset($_SESSION['paya_ken_num'])) {
        $ken_num = $_SESSION['paya_ken_num'];
    } else {
        $ken_num = '';
    }
}
if (isset($_REQUEST['str_date'])) {
    $str_date = $_REQUEST['str_date'];
    $_SESSION['paya_strdate'] = $str_date;
} elseif (isset($_SESSION['paya_strdate'])) {
    $str_date = $_SESSION['paya_strdate'];
} else {
    $year  = date('Y') - 5; // ��ǯ������
    $month = date('m');
    $str_date = $year . $month . '01';
}
if (isset($_REQUEST['end_date'])) {
    $end_date = $_REQUEST['end_date'];
    $_SESSION['paya_enddate'] = $end_date;
} elseif (isset($_SESSION['paya_enddate'])) {
    $end_date = $_SESSION['paya_enddate'];
} else {
    $end_date = '99999999';
}
if (isset($_REQUEST['paya_page'])) {
    $paya_page = $_REQUEST['paya_page'];
    $_SESSION['paya_page'] = $paya_page;
} else {
    if (isset($_SESSION['paya_page'])) {
        $paya_page = $_SESSION['paya_page'];
    } else {
        $paya_page = '';
    }
}
if ($session->get('str_date') != '') {
    $str_date = $session->get('str_date');
    $_SESSION['str_date'] = $str_date;
    $_SESSION['paya_strdate'] = $str_date;
}
if ($session->get('end_date') != '') {
    $end_date = $session->get('end_date');
    $_SESSION['end_date'] = $end_date;
    $_SESSION['paya_enddate'] = $end_date;
}
//////////// ���ǤιԿ�
define('PAGE', $paya_page);

/////////// begin �ȥ�󥶥�����󳫻�
if ($con = funcConnect()) {
    query_affected_trans($con, 'begin');
} else {
    $_SESSION['s_sysmsg'] .= 'funcConnect() error';
    header('Location: ' . H_WEB_HOST . $menu->out_RetUrl());
    exit();
}

//////////// SQL ʸ�� where ��� ���Ѥ��� 01111=�������칩�� 00222=��¤������ 99999=����
$search = sprintf("where act_date>=%d and act_date<=%d", $str_date, $end_date);

//////////// SQL ʸ�� where ��� ���Ѥ���
if ($parts_no != '') {
    $search_kin = sprintf("%s and paya.parts_no='%s'", $search, $parts_no);
    $query = "select trim(substr(midsc,1,18)) from miitem where mipn='{$parts_no}' limit 1";
    if (getUniResult($query, $name) <= 0) {
        $name = '�ޥ�����̤��Ͽ';
    }
    $caption_title = "�����ֹ桧{$parts_no}��<font color='blue'>����̾��{$name}</font>��ǯ�" . format_date($str_date) . '��' . format_date($end_date);
} elseif ($div != ' ') {
    if ($vendor != '') {
        if($div == 'D') {
            $search_kin = sprintf("%s and vendor='%s' and paya.div='C' and kouji_no NOT like 'SC%%'", $search, $vendor);
            $query = "select trim(substr(name, 1, 20)) from vendor_master where vendor='{$vendor}' limit 1";
            if (getUniResult($query, $name) <= 0) {
                $name = '�ޥ�����̤��Ͽ';
            }
            $caption_title = "�����������ץ�ɸ�ࡡǯ�" . format_date($str_date) . '��' . format_date($end_date) . "ȯ���衧" . $name;
        } elseif($div == 'S') {
            $search_kin = sprintf("%s and vendor='%s' and paya.div='C' and kouji_no like 'SC%%'", $search, $vendor);
            $query = "select trim(substr(name, 1, 20)) from vendor_master where vendor='{$vendor}' limit 1";
            if (getUniResult($query, $name) <= 0) {
                $name = '�ޥ�����̤��Ͽ';
            }
            $caption_title = "�����������ץ�����ǯ�" . format_date($str_date) . '��' . format_date($end_date) . "ȯ���衧" . $name;
        } else {
            $search_kin = sprintf("%s and vendor='%s' and paya.div='%s'", $search, $vendor, $div);
            $query = "select trim(substr(name, 1, 20)) from vendor_master where vendor='{$vendor}' limit 1";
            if (getUniResult($query, $name) <= 0) {
                $name = '�ޥ�����̤��Ͽ';
            }
            $caption_title = "��������{$div}��ǯ�" . format_date($str_date) . '��' . format_date($end_date) . "ȯ���衧" . $name;
        }
    } else {
        if($div == 'D') {
            $search_kin = sprintf("%s and paya.div='C' and kouji_no NOT like 'SC%%'", $search);
            $caption_title = "�����������ץ�ɸ�ࡡǯ�" . format_date($str_date) . '��' . format_date($end_date);
        } elseif($div == 'S') {
            $search_kin = sprintf("%s and paya.div='C' and kouji_no like 'SC%%'", $search);
            $caption_title = "�����������ץ�����ǯ�" . format_date($str_date) . '��' . format_date($end_date);
        } else {
            $search_kin = sprintf("%s and paya.div='%s'", $search, $div);
            $caption_title = "��������{$div}��ǯ�" . format_date($str_date) . '��' . format_date($end_date);
        }
    }
} else {
    if ($vendor != '') {
        $search_kin = sprintf("%s and vendor='%s'", $search, $vendor);
        $query = "select trim(substr(name, 1, 20)) from vendor_master where vendor='{$vendor}' limit 1";
        if (getUniResult($query, $name) <= 0) {
            $name = '�ޥ�����̤��Ͽ';
        }
        $caption_title = "�������������硡ǯ�" . format_date($str_date) . '��' . format_date($end_date) . "ȯ���衧" . $name;
    } else {
        $search_kin = $search;
        $caption_title = "�������������硡ǯ�" . format_date($str_date) . '��' . format_date($end_date);
    }
}

//////////// ���������׶�� (����1��5)����6�ʾ�����
$query = sprintf("select
                            count(*)
                    from
                            (act_payable as paya left outer join vendor_master using(vendor))
                    left outer join
                            order_plan
                    using(sei_no)
                    %s
                    ", $search_kin);
if (getResultTrs($con, $query, $paya_ctoku) <= 0) {
    $_SESSION['s_sysmsg'] .= sprintf("��ݶ�η׾���:%s��%s��<br>�ǡ���������ޤ���", $str_date, $end_date );
    $_SESSION['s_sysmsg'] .= '��� ����μ���������ޤ���';      // .= ��å��������ɲä���
    query_affected_trans($con, 'rollback');         // transaction rollback
    header('Location: ' . H_WEB_HOST . $menu->out_RetUrl());
    exit();
} else {
    $maxrows = $paya_ctoku[0][0];
    // $sum_kin = $paya_ctoku[0][0];
    // $maxrows = $paya_ctoku[0][1];    // GROUP BY �λ��Ͻ���ؿ��ϻȤ��ʤ�
}

$query = sprintf("select
                        sum(Uround(order_price * siharai,0))
                    from
                            (act_payable as paya left outer join vendor_master using(vendor))
                    left outer join
                            order_plan
                    using(sei_no)
                    %s
                    ", $search_kin);
if (getUniResTrs($con, $query, $sum_kin) <= 0) {
    $_SESSION['s_sysmsg'] .= '��ݹ�׶�ۤμ���������ޤ���';      // .= ��å��������ɲä���
    query_affected_trans($con, 'rollback');         // transaction rollback
    header('Location: ' . H_WEB_HOST . $menu->out_RetUrl());
    exit();
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
        SELECT
            (to_date(ken_date, 'YYYYMMDD') - to_date(uke_date, 'YYYYMMDD')) - (SELECT count(*) FROM company_calendar WHERE tdate>=to_date(uke_date, 'YYYYMMDD') and tdate<=to_date(ken_date, 'YYYYMMDD') and bd_flg='f')            as ��������,      -- 01
            COUNT((to_date(ken_date, 'YYYYMMDD') - to_date(uke_date, 'YYYYMMDD')) - (SELECT count(*) FROM company_calendar WHERE tdate>=to_date(uke_date, 'YYYYMMDD') and tdate<=to_date(ken_date, 'YYYYMMDD') and bd_flg='f'))     as ���,          -- 02
            ((to_date(ken_date, 'YYYYMMDD') - to_date(uke_date, 'YYYYMMDD')) - (SELECT count(*) FROM company_calendar WHERE tdate>=to_date(uke_date, 'YYYYMMDD') and tdate<=to_date(ken_date, 'YYYYMMDD') and bd_flg='f')) 
            * (COUNT((to_date(ken_date, 'YYYYMMDD') - to_date(uke_date, 'YYYYMMDD')) - (SELECT count(*) FROM company_calendar WHERE tdate>=to_date(uke_date, 'YYYYMMDD') and tdate<=to_date(ken_date, 'YYYYMMDD') and bd_flg='f'))) as ����           -- 03
        FROM
            act_payable AS paya
        LEFT OUTER JOIN
            vendor_master USING(vendor)
        left outer join
            order_plan using(sei_no)
        %s
        GROUP BY ��������
        ORDER BY �������� DESC
    ", $search_kin);       // ���� $search �Ǹ���
$res   = array();
$field = array();
if (($rows = getResWithFieldTrs($con, $query, $field, $res)) <= 0) {
    $_SESSION['s_sysmsg'] .= sprintf("��ݶ�η׾���:%s��%s��<br>�ǡ���������ޤ���", $str_date, $end_date );
    query_affected_trans($con, 'rollback');         // transaction rollback
    header('Location: ' . H_WEB_HOST . $menu->out_RetUrl());
    exit();
} else {
    query_affected_trans($con, 'commit');         // transaction commit
    $num = count($field);       // �ե�����ɿ�����
}

//////////// ɽ�������
$menu->set_caption("{$caption_title}");

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
.pt12b {
    font:           12pt;
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
                $ken_total = 0;
                $day_total = 0;
                for ($r=0; $r<$rows; $r++) {
                ?>
                    <tr>
                        <td class='winbox' nowrap align='right'><div class='pt12b'><?= ($r + $offset + 1) ?></div></td>    <!-- �ԥʥ�С���ɽ�� -->
                    <?php
                    for ($i=0; $i<$num; $i++) {         // �쥳���ɿ�ʬ���֤�
                        switch ($i) {
                        case 0:
                            echo "<td class='winbox' nowrap align='right'><div class='pt10b'>{$res[$r][$i]}</div></td>\n";
                            break;
                        case 1:
                            echo "<td class='winbox' nowrap align='right'><div class='pt10b'><a href='", $menu->out_action('��ݼ��ӾȲ�'), "?parts_no={$parts_no}&div={$div}&str_date={$str_date}&end_date={$end_date}&div={$div}&vendor={$vendor}&ken_num={$res[$r][0]}&paya_page={$paya_page}#mark'>" . number_format($res[$r][$i]) . "</a></div></td>\n";
                            $ken_total += $res[$r][$i];
                            break;
                        case 2:
                            echo "<td class='winbox' nowrap align='right'><div class='pt10b'>", number_format($res[$r][$i]), "</div></td>\n";
                            $day_total += $res[$r][$i];
                            break;
                        default:
                            echo "<td class='winbox' nowrap align='right'><div class='pt10b'>{$res[$r][$i]}</div></td>\n";
                        }
                    }
                    ?>
                    </tr>
                <?php
                
                }
                ?>
                
                 <tr>
                    <td class='winbox' nowrap align='right'><div class='pt12b'>��</div></td>    <!-- �ԥʥ�С���ɽ�� -->
                    <td class='winbox' nowrap align='right'><div class='pt12b'>���</div></td>    <!-- �ԥʥ�С���ɽ�� -->
                    <?php
                    echo "<td class='winbox' nowrap align='right'><div class='pt10b'><a href='", $menu->out_action('��ݼ��ӾȲ�'), "?parts_no={$parts_no}&div={$div}&str_date={$str_date}&end_date={$end_date}&div={$div}&vendor={$vendor}&ken_num=&paya_page={$paya_page}#mark'>" . number_format($ken_total) . "</a></div></td>\n";
                    echo "<td class='winbox' nowrap align='right'><div class='pt10b'>", number_format($day_total), "</div></td>\n";
                    ?>
                 </tr>
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
