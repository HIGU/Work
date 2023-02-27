<?php
//////////////////////////////////////////////////////////////////////////////
// ê�� ��� �ξȲ� (���ץ�����)  ������ UKWLIB/W#MVTNPT                    //
// Copyright(C) 2003-2004 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp       //
// �ѹ�����                                                                 //
// 2003/11/25 ��������  inventory_monthly_ctoku_view.php                    //
//            SERIAL���Υ���ǥå���������� SQL���®������                //
// 2003/12/04 �ơ��֥��month_end��inventory_monthly���ѹ� ��۽�ɽ����     //
// 2003/12/09 �����ȴ�Ф����å��ɲ�(���ξ��ȴ�Ф�)�ȥơ��֥��ѹ�      //
//            �ơ��֥�̾ inventory_monthly �� inventory_monthly_ctoku       //
//   �ե�����̾�ѹ�inventory_month_ctoku_view��inventory_monthly_ctoku_view //
// 2004/01/14 $_SESSION['act_ym'] �� $_SESSION['ind_ym'] ���ѹ�             //
// 2004/05/12 �����ȥ�˥塼ɽ������ɽ�� �ܥ����ɲ� menu_OnOff($script)�ɲ� //
//////////////////////////////////////////////////////////////////////////////
ini_set('error_reporting',E_ALL);       // E_ALL='2047' debug ��
ini_set('display_errors','1');          // Error ɽ�� ON debug �� ��꡼���女����
// ini_set('implicit_flush', 'off');       // echo print �� flush �����ʤ�(�٤��ʤ뤿��) CLI CGI��
// ini_set('max_execution_time', 1200);    // ����¹Ի���=20ʬ CLI CGI��
ob_start('ob_gzhandler');               // ���ϥХåե���gzip����
session_start();                        // ini_set()�μ��˻��ꤹ�뤳�� Script �Ǿ��
require_once ('../function.php');       // define.php �� pgsql.php �� require_once ���Ƥ���
require_once ('../tnk_func.php');       // TNK �˰�¸������ʬ�δؿ��� require_once ���Ƥ���
access_log();                           // Script Name �ϼ�ư����
$_SESSION['site_index'] = 30;           // ��������ط�=20 �Ǹ�Υ�˥塼 = 99   �����ƥ�����Ѥϣ�����
$_SESSION['site_id']    = 32;           // ���̥�˥塼̵�� <= 0    �ƥ�ץ졼�ȥե�����ϣ�����
$current_script  = $_SERVER['PHP_SELF'];        // ���߼¹���Υ�����ץ�̾����¸
// $url_referer     = $_SERVER['HTTP_REFERER'];    // �ƽФ�Ȥ�URL����¸ ���Υ�����ץȤ�ʬ�������򤷤Ƥ�����ϻ��Ѥ��ʤ�
$url_referer     = $_SESSION['act_referer'];     // ʬ������������¸����Ƥ���ƽи��򥻥åȤ���

//////////////// ǧ�ڥ����å�
if ( !isset($_SESSION['User_ID']) || !isset($_SESSION['Password']) || !isset($_SESSION['Auth']) ) {
// if ($_SESSION['Auth'] <= 2) {                // ���¥�٥뤬���ʲ��ϵ���
// if (account_group_check() == FALSE) {        // ����Υ��롼�װʳ��ϵ���
    $_SESSION['s_sysmsg'] = "ǧ�ڤ���Ƥ��ʤ���ǧ�ڴ��¤��ڤ�ޤ����������󤫤餪�ꤤ���ޤ���";
    // header("Location: http:" . WEB_HOST . "menu.php");   // ����ƽи������
    header("Location: $url_referer");                   // ľ���θƽи������
    exit();
}

/********** Logic Start **********/
//////////// �����ȥ�����ա���������
$today = date('Y/m/d H:i:s');

//////////// JavaScript Stylesheet File ��ɬ���ɤ߹��ޤ���
$uniq = uniqid('target');

//////////// �����ƥ��å������ѿ� �����
// $_SESSION['s_sysmsg'] = "";      // menu_site.php �ǻ��Ѥ��뤿�ᤳ���ǽ�������Բ�

//////////// �о�ǯ������ (ǯ��Τߤ����)
if ( isset($_SESSION['ind_ym']) ) {
    $act_ym = $_SESSION['ind_ym'];
    $s_ymd  = $act_ym . '01';   // ������
    $e_ymd  = $act_ym . '99';   // ��λ��
} else {
    $_SESSION['s_sysmsg'] = '�о�ǯ����ꤵ��Ƥ��ޤ���';
    header("Location: $url_referer");                   // ľ���θƽи������
    exit();
}

//////////// �����ȥ�̾(�������Υ����ȥ�̾�ȥե�����Υ����ȥ�̾)
$menu_title = "���ץ������� ê����ۤξȲ�";

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
    header("Location: $url_referer");                   // ľ���θƽи������
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
    header("Location: $url_referer");                   // ľ���θƽи������
    exit();
} else {
    $num = count($field);       // �ե�����ɿ�����
}

/********** Logic End   **********/
header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");               // ���դ����
header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");  // ��˽�������Ƥ���
header("Cache-Control: no-store, no-cache, must-revalidate");   // HTTP/1.1
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");                                     // HTTP/1.0

?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<HTML>
<HEAD>
<META http-equiv="Content-Type" content="text/html; charset=EUC-JP">
<META http-equiv="Content-Style-Type" content="text/css">
<TITLE><?= $menu_title ?></TITLE>
<script language="JavaScript">
<!--
    parent.menu_site.location = 'http:<?php echo(WEB_HOST) ?>menu_site.php';
// -->
</script>

<!--    �ե��������ξ��
<script language='JavaScript' src='template.js?<?= $uniq ?>'>
</script>
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
//    document.form_name.element_name.focus();      // ������ϥե����ब������ϥ����Ȥ򳰤�
//    document.form_name.element_name.select();
}
// -->
</script>

<!-- �������륷���ȤΥե��������򥳥��� HTML���� �����Ȥ�����Ҥ˽���ʤ��������
<link rel='stylesheet' href='template.css?<?= $uniq ?>' type='text/css' media='screen'>
-->

<style type="text/css">
<!--
.pt8 {
    font:normal 8pt;
    font-family: monospace;
}
.pt9 {
    font:normal 9pt;
    font-family: monospace;
}
.pt10 {
    font:normal 10pt;
    font-family: monospace;
}
.pt10b {
    font:bold 10pt;
    font-family: monospace;
}
.pt11b {
    font:bold 11pt;
}
.pt12b {
    font:bold 12pt;
    font-family: monospace;
}
.title_font {
    font:bold 13.5pt;
    font-family: monospace;
}
.today_font {
    font-size: 10.5pt;
    font-family: monospace;
}
.corporate_name {
    font:bold 10pt;
    font-family: monospace;
}
.margin0 {
    margin:0%;
}
th {
    background-color:yellow;
    color:blue;
    font:bold 10pt;
    font-family: monospace;
}
-->
</style>
</HEAD>
<BODY class='margin0' onLoad='set_focus()'>
    <center>
        <!----------------- ������ �����ȥ��ɽ������ ------------------->
        <table width='100%' bgcolor='#d6d3ce'  cellspacing='0' cellpadding='1' border='1'>
            <tr><td> <!----------- ���ߡ�(�ǥ�������) ------------>
        <table width='100%' bordercolordark='white' bordercolorlight='#bdaa90' bgcolor='#d6d3ce' cellspacing='0' cellpadding='1' border='1'>
            <tr>
                <form name='return_form' method='post' action='<?= $url_referer ?>'>
                    <td width='60' bgcolor='blue' align='center' valign='center'>
                        <input class='pt12b' type='submit' name='return' value='���'>
                    </td>
                </form>
                <?= menu_OnOff($current_script . '?page_keep=1') ?>
                <td class='title_font' colspan='1' bgcolor='#d6d3ce' align='center'>
                    <?= $menu_title . "\n" ?>
                </td>
                <td class='today_font' colspan='1' bgcolor='#d6d3ce' align='center' width='140'>
                    <?= $today . "\n" ?>
                </td>
            </tr>
        </table>
            </td></tr>
        </table> <!----------------- ���ߡ�End ------------------>
        
        <br>
        
        <!----------------- ������ ���� ���� �Υե����� ---------------->
        <table width='100%' cellspacing="0" cellpadding="0" border='0'>
            <form name='page_form' method='post' action='<?= $current_script ?>'>
                <tr>
                    <td align='left'>
                        <table align='left' border='3' cellspacing='0' cellpadding='0'>
                            <td align='left'>
                                <input class='pt10b' type='submit' name='backward' value='����'>
                            </td>
                        </table>
                    </td>
                    <td nowrap align='center' class='pt11b'>
                        <?= "{$act_ym}��{$menu_title}�����=" . number_format($sum_kin) . '�ߡ���۽硡' . number_format($maxrows) . "�� \n" ?>
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
        <table bgcolor='#d6d3ce' align='center' cellspacing="0" cellpadding="3" border='1'>
            <tr><td> <!----------- ���ߡ�(�ǥ�������) ------------>
        <table width='100%' bordercolordark='white' bordercolorlight='#bdaa90' bgcolor='#d6d3ce' align='center' cellspacing="0" cellpadding="3" border='1'>
            <THEAD>
                <!-- �ơ��֥� �إå�����ɽ�� -->
                <tr>
                    <th nowrap width='10'>No</th>        <!-- �ԥʥ�С���ɽ�� -->
                <?php
                for ($i=0; $i<$num; $i++) {             // �ե�����ɿ�ʬ���֤�
                ?>
                    <th nowrap><?= $field[$i] ?></th>
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
                        <td nowrap class='pt10b' align='right'><?= ($r + $offset + 1) ?></td>    <!-- �ԥʥ�С���ɽ�� -->
                    <?php
                    for ($i=0; $i<$num; $i++) {         // �쥳���ɿ�ʬ���֤�
                        switch ($i) {
                        case 1:
                            echo "<td nowrap align='left' class='pt9'>{$res[$r][$i]}</td>\n";
                            break;
                        case 3:
                        case 5:
                        case 7:
                        case 8:
                            echo "<td nowrap align='right' class='pt9'>" . number_format($res[$r][$i], 0) . "</td>\n";
                            break;
                        case 4:
                        case 6:
                            echo "<td nowrap align='right' class='pt9'>" . number_format($res[$r][$i], 2) . "</td>\n";
                            break;
                        default:
                            echo "<td nowrap align='center' class='pt9'>{$res[$r][$i]}</td>\n";
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
</BODY>
</HTML>
