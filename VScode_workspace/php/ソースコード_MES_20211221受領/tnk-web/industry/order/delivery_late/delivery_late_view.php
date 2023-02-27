<?php
//////////////////////////////////////////////////////////////////////////////
// Ǽ���٤����ʤξȲ� �� �����å���                                         //
// Copyright (C) 2011-     Norihisa.Ohya norihisa_ooya@nitto-kohki.co.jp    //
// Changed history                                                          //
// 2011/11/09 Created   delivery_late_view.php                              //
// 2011/11/10 �ǡ��������Ϥ��ǥ��顼��ȯ�������Τ���                      //
//////////////////////////////////////////////////////////////////////////////
// ini_set('error_reporting', E_ALL);          // E_ALL='2047' debug ��
// ini_set('display_errors', '1');             // Error ɽ�� ON debug �� ��꡼���女����
// ini_set('implicit_flush', 'off');           // echo print �� flush �����ʤ�(�٤��ʤ뤿��) CLI CGI��
// ini_set('max_execution_time', 1200);        // ����¹Ի���=20ʬ CLI CGI��
ob_start('ob_gzhandler');                   // ���ϥХåե���gzip����
session_start();                            // ini_set()�μ��˻��ꤹ�뤳�� Script �Ǿ��
require_once ('../../../function.php');        // define.php �� pgsql.php �� require_once ���Ƥ���
require_once ('../../../tnk_func.php');        // TNK �˰�¸������ʬ�δؿ��� require_once ���Ƥ���
require_once ('../../../MenuHeader.php');      // TNK ������ menu class
access_log();                               // Script Name �ϼ�ư����

///// TNK ���ѥ�˥塼���饹�Υ��󥹥��󥹤����
$menu = new MenuHeader(0);                  // ǧ�ڥ����å�0=���̰ʾ� �����=TOP_MENU �����ȥ�̤����

////////////// ����������
$menu->set_site(30, 52);                    // site_index=30(������˥塼) site_id=52(Ǽ���٤����ʤξȲ�)
////////////// �꥿���󥢥ɥ쥹����
// $menu->set_RetUrl(INDUST_MENU);             // �̾�ϻ��ꤹ��ɬ�פϤʤ�
//////////// �����ȥ�̾(�������Υ����ȥ�̾�ȥե�����Υ����ȥ�̾)
$menu->set_title('Ǽ���٤����� �� �� ��');
//////////// ɽ�������     ���Υ��å��ǽ������뤿�ᤳ���Ǥϻ��Ѥ��ʤ�
// $menu->set_caption('������ɬ�פʾ��������������򤷤Ʋ�������');
//////////// �ƽ����action̾�ȥ��ɥ쥹����
$menu->set_action('ñ������ɽ��',   INDUST . 'parts/parts_cost_view.php');
$menu->set_action('�߸�ͽ��',   INDUST . 'parts/parts_stock_plan/parts_stock_plan_Main.php');
$menu->set_action('�߸˷���',   INDUST . 'parts/parts_stock_history/parts_stock_view.php');

if (isset($_REQUEST['material'])) {
    $menu->set_retGET('material', $_REQUEST['material']);
    if (isset($_REQUEST['uke_no'])) {
        $uke_no = $_REQUEST['uke_no'];
        $_SESSION['uke_no'] = $uke_no;
    } else {
        $uke_no = @$_SESSION['uke_no'];     // @�߸˷����ɽ����������ꤵ�줿�����б�(uke_no�ʤ�)
    }
    $current_script = $menu->out_self() . '?material=1';
    $_SESSION['paya_strdate'] = '20001001';     // ʬ�Ҳ�����
    $_SESSION['paya_enddate'] = '99999999';     // �ǿ��ޤ�
} elseif (isset($_REQUEST['uke_no'])) {     // �߸˷���(ñ�Τ���)�ƽл����б�
    $uke_no = $_REQUEST['uke_no'];
    $current_script = $menu->out_self();
    $_SESSION['paya_strdate'] = '20001001';     // ʬ�Ҳ�����
    $_SESSION['paya_enddate'] = '99999999';     // �ǿ��ޤ�
} else {                                    // �ե�����(ñ�Τ���)�ƽл����б�
    $uke_no = '';
    $current_script = $menu->out_self();
}

//////////// ���칩��ٵ��ʤ��б�
if (isset($_REQUEST['kei_ym'])) {
    $kei_ym = $_REQUEST['kei_ym'];
    $kei_ym = format_date8($kei_ym);
    $_SESSION['kei_ym'] = $kei_ym;
} else {
    $kei_ym = @$_SESSION['kei_ym'];     // @ñ��������������б�(�դξ���̵�뤹��)
}

//////////// JavaScript Stylesheet File ��ɬ���ɤ߹��ޤ���
$uniq = uniqid('target');

//////////// �������ե����फ���POST�ǡ�������
if (isset($_REQUEST['parts_no'])) {
    $parts_no = $_REQUEST['parts_no'];
    $_SESSION['paya_parts_no'] = $parts_no;
} else {
    $parts_no = '';
    $_SESSION['paya_parts_no'] = $parts_no;
    ///// �����ֹ��ɬ��
}
if (isset($_REQUEST['div'])) {
    $div = $_REQUEST['div'];
    $_SESSION['payable_div'] = $div;
} else {
    if (isset($_SESSION['payable_div'])) {
        $div = $_SESSION['payable_div'];
    } else {
        $div = ' ';
        $_SESSION['payable_div'] = $div;
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
        $_SESSION['paya_vendor'] = $vendor;
    }
}
if (isset($_REQUEST['str_date'])) {
    $str_date = $_REQUEST['str_date'];
    $_SESSION['paya_strdate'] = $str_date;
} elseif (isset($_SESSION['paya_strdate'])) {
    $str_date = $_SESSION['paya_strdate'];
} else {
    $str_date = '';     // �����
    $_SESSION['paya_strdate'] = $str_date;
}
if (isset($_REQUEST['end_date'])) {
    $end_date = $_REQUEST['end_date'];
    $_SESSION['paya_enddate'] = $end_date;
} elseif (isset($_SESSION['paya_enddate'])) {
    $end_date = $_SESSION['paya_enddate'];
} else {
    $end_date = '';     // �����
    $_SESSION['paya_enddate'] = $end_date;
}
if (isset($_REQUEST['paya_page'])) {
    $paya_page = $_REQUEST['paya_page'];
    $_SESSION['payable_page'] = $paya_page;
} else {
    if (isset($_SESSION['payable_page'])) {
        $paya_page = $_SESSION['payable_page'];
    } else {
        $paya_page = 23;
    }
}

//////////// ���ǤιԿ�
define('PAGE', $paya_page);

switch ($div) {
case ' ';    // ����
    $caption_title = '���硧���Ρ�';
    break;
case 'C';    // ���ץ� ����
    $caption_title = '���硧���ץ����Ρ�';
    break;
case 'SC';   // ���ץ� ����
    $caption_title = '���硧���ץ�����';
    break;
case 'CS';   // ���ץ� ɸ��
    $caption_title = '���硧���ץ�ɸ�ࡡ';
    break;
case 'L';    // ��˥� ����
    $caption_title = '���硧��˥����Ρ�';
    break;
case 'LN';   // ��˥� �Τ�
    $caption_title = '���硧��˥��Τߡ�';
    break;
case 'B';    // �Х����
    $caption_title = '���硧�Х���롡';
    break;
case 'T';    // �ġ���¾
    $caption_title = '���硧�ġ���¾��';
    break;
}

$caption_title .= 'ǯ�' . format_date($str_date) . '��' . format_date($end_date);

$search = "where proc.delivery <= {$end_date} and proc.delivery >= {$str_date} and uke_date >= 0 and uke_date > proc.delivery and data.sei_no > 0 and (data.order_q - data.cut_genpin) > 0";
//////// ���������鶦�̤� where�������
if ($parts_no != '') {
    $search .= sprintf(" and data.parts_no='%s'", $parts_no);
    $query = "select trim(substr(midsc,1,30)) from miitem where mipn='{$parts_no}' limit 1";
    if (getUniResult($query, $name) <= 0) {
        $name = '�ޥ�����̤��Ͽ';
    }
    $caption_title = "�����ֹ桧{$parts_no}��<font color='blue'>����̾��{$name}</font><BR>ǯ�" . format_date($str_date) . '��' . format_date($end_date);
} elseif ($div != ' ') {
    if ($vendor != '') {
        switch ($div) {
        case ' ';    // ����
            $div_name = '���硧���Ρ�';
        case 'C':       // C����
            $div_name = '���硧���ץ����Ρ�';
            $search .= " and data.parts_no like 'C%' and proc.locate != '52   '";
            break;
        case 'SC':      // C����
            $div_name = '���硧���ץ�����';
            $search .= " and data.parts_no like 'C%' and data.kouji_no like '%SC%' and proc.locate != '52   '";
            break;
        case 'CS':      // Cɸ��
            $div_name = '���硧���ץ�ɸ�ࡡ';
            $search .= " and data.parts_no like 'C%' and data.kouji_no not like '%SC%' and proc.locate != '52   '";
            break;
        case 'L':       // L����
            $div_name = '���硧��˥����Ρ�';
            $search .= " and data.parts_no like 'L%' and proc.locate != '52   '";
            break;
        case 'LN';  // ��˥� �Τ�
            $div_name = '���硧��˥��Τߡ�';
            $search .= " and (data.parts_no like 'L%' and data.parts_no NOT like 'LC%%' AND data.parts_no NOT like 'LR%%') and proc.locate != '52   '";
            break;
        case 'B';   // �Х����
            $div_name = '���硧�Х���롡';
            $search .= " and (data.parts_no like 'LC%%' OR data.parts_no like 'LR%%') and proc.locate != '52   '";
            break;
        case 'T';   // �ġ���¾
            $div_name = '���硧�ġ���¾��';
            $search .= " and (data.parts_no NOT like 'C%%' AND data.parts_no NOT like 'L%%') and proc.locate != '52   '";
            break;
        }
        $search .= sprintf(" and data.vendor = '%s'", $vendor);
        $query = "select trim(substr(name, 1, 30)) from vendor_master where vendor='{$vendor}' limit 1";
        if (getUniResult($query, $name) <= 0) {
            $name = '�ޥ�����̤��Ͽ';
        }
        $caption_title = "<font color='blue'>���Ϲ��졧{$name}</font>��" . $div_name ."ǯ�" . format_date($str_date) . '��' . format_date($end_date) . "<BR>";
    }
    switch ($div) {
    case ' ';    // ����
        $div_name = '���硧���Ρ�';
    case 'C':       // C����
        $div_name = '���硧���ץ����Ρ�';
        $search .= " and data.parts_no like 'C%' and proc.locate != '52   '";
        break;
    case 'SC':      // C����
        $div_name = '���硧���ץ�����';
        $search .= " and data.parts_no like 'C%' and data.kouji_no like '%SC%' and proc.locate != '52   '";
        break;
    case 'CS':      // Cɸ��
        $div_name = '���硧���ץ�ɸ�ࡡ';
        $search .= " and data.parts_no like 'C%' and data.kouji_no not like '%SC%' and proc.locate != '52   '";
        break;
    case 'L':       // L����
        $div_name = '���硧��˥����Ρ�';
        $search .= " and data.parts_no like 'L%' and proc.locate != '52   '";
        break;
    case 'LN';  // ��˥� �Τ�
        $div_name = '���硧��˥��Τߡ�';
        $search .= " and (data.parts_no like 'L%' and data.parts_no NOT like 'LC%%' AND data.parts_no NOT like 'LR%%') and proc.locate != '52   '";
        break;
    case 'B';   // �Х����
        $div_name = '���硧�Х���롡';
        $search .= " and (data.parts_no like 'LC%%' OR data.parts_no like 'LR%%') and proc.locate != '52   '";
        break;
    case 'T';   // �ġ���¾
        $div_name = '���硧�ġ���¾��';
        $search .= " and (data.parts_no NOT like 'C%%' AND data.parts_no NOT like 'L%%') and proc.locate != '52   '";
        break;
    }
} elseif ($vendor != '') {
    $search .= sprintf(" and data.vendor = '%s'", $vendor);
    $query = "select trim(substr(name, 1, 30)) from vendor_master where vendor='{$vendor}' limit 1";
    if (getUniResult($query, $name) <= 0) {
        $name = '�ޥ�����̤��Ͽ';
    }
    switch ($div) {
    case ' ';    // ����
        $div_name = '���硧���Ρ�';
        break;
    case 'C';    // ���ץ� ����
        $div_name = '���硧���ץ����Ρ�';
        break;
    case 'SC';    // ���ץ� ����
        $div_name = '���硧���ץ�����';
        break;
    case 'CS';    // ���ץ� ɸ��
        $div_name = '���硧���ץ�ɸ�ࡡ';
        break;
    case 'L';    // ��˥� ����
        $div_name = '���硧��˥����Ρ�';
        break;
    case 'LN';   // ��˥� �Τ�
        $caption_title = '���硧��˥��Τߡ�';
        break;
    case 'B';    // �Х����
        $caption_title = '���硧�Х���롡';
        break;
    case 'T';    // �ġ���¾
        $caption_title = '���硧�ġ���¾��';
        break;
    }
    $caption_title = "<font color='blue'>���Ϲ��졧{$name}</font>��" . $div_name ."ǯ�" . format_date($str_date) . '��' . format_date($end_date) . "<BR>";
}
//////////// ��ץ쥳���ɿ�����     (�оݥǡ����κ������ڡ�������˻���)
$query = sprintf('select count(*), sum(Uround(data.order_q * data.order_price,0)) from
                order_data      AS data
            left outer join
                order_process   AS proc
                                        using(sei_no, order_no, vendor)
            LEFT OUTER JOIN
                order_plan      AS plan     USING (sei_no)
            left outer join
                vendor_master   AS mast
                                        on(data.vendor = mast.vendor)
            left outer join
                miitem          AS item
                                        on(data.parts_no = item.mipn) %s', $search);
$res_max = array();
if ( getResult2($query, $res_max) <= 0) {         // $maxrows �μ���
    $_SESSION['s_sysmsg'] .= "��ץ쥳���ɿ��μ����˼���";      // .= ��å��������ɲä���
    header('Location: ' . H_WEB_HOST . $menu->out_RetUrl());    // ľ���θƽи������
    exit();
} else {
    $maxrows = $res_max[0][0];                  // ��ץ쥳���ɿ��μ���
    $caption_title .= '����׶�ۡ�' . number_format($res_max[0][1]);   // �����ݶ�ۤ򥭥�ץ���󥿥��ȥ�˥��å�
    $caption_title .= '����׷����' . number_format($res_max[0][0]);   // �����ݷ���򥭥�ץ���󥿥��ȥ�˥��å�
}

//////////// �ڡ������ե��å�����
if ( isset($_REQUEST['forward']) ) {                       // ���Ǥ������줿
    $_SESSION['paya_offset'] += PAGE;
    if ($_SESSION['paya_offset'] >= $maxrows) {
        $_SESSION['paya_offset'] -= PAGE;
        if ($_SESSION['s_sysmsg'] == "") {
            $_SESSION['s_sysmsg'] .= "<font color='yellow'>���ǤϤ���ޤ���</font>";
        } else {
            $_SESSION['s_sysmsg'] .= "<br><font color='yellow'>���ǤϤ���ޤ���</font>";
        }
    }
} elseif ( isset($_REQUEST['backward']) ) {                // ���Ǥ������줿
    $_SESSION['paya_offset'] -= PAGE;
    if ($_SESSION['paya_offset'] < 0) {
        $_SESSION['paya_offset'] = 0;
        if ($_SESSION['s_sysmsg'] == "") {
            $_SESSION['s_sysmsg'] .= "<font color='yellow'>���ǤϤ���ޤ���</font>";
        } else {
            $_SESSION['s_sysmsg'] .= "<br><font color='yellow'>���ǤϤ���ޤ���</font>";
        }
    }
} elseif ( isset($_REQUEST['page_keep']) ) {               // ���ߤΥڡ�����ݻ�����
    $offset = $_SESSION['paya_offset'];
} else {
    $_SESSION['paya_offset'] = 0;                            // ���ξ��ϣ��ǽ����
}
$offset = $_SESSION['paya_offset'];

//////////// ɽ�����Υǡ���ɽ���ѤΥ���ץ� Query & �����
$query = sprintf("
        select    data.order_seq          AS ȯ��Ϣ��
                  , substr(to_char(data.date_issue, 'FM9999/99/99'), 6, 5)          AS ȯ����
                  , data.pre_seq            AS ����Ϣ��
                  , to_char(data.sei_no,'FM0000000')        AS ��¤�ֹ�
                  , data.order_no           AS ��ʸ�ֹ�
                  , data.parts_no           AS �����ֹ�
                  , data.vendor             AS ȯ���襳����
                  , data.order_q            AS ��ʸ��
                  , data.order_price        AS ñ��
                  , substr(to_char(proc.delivery, 'FM9999/99/99'), 0, 11)            AS Ǽ��
                  , substr(to_char(data.uke_date, 'FM9999/99/99'), 0, 11)            AS Ǽ����
                  , data.kouji_no           AS �����ֹ�
                  , proc.pro_mark           AS ����
                  , proc.mtl_cond           AS �������
                  , proc.pro_kubun          AS ����ñ����ʬ
                  , proc.order_date         AS ȯ����
                  , proc.order_q            AS ����ʸ��
                  , proc.locate             AS Ǽ�����
                  , proc.kamoku             AS ����
                  , proc.order_ku           AS ȯ���ʬ
                  , proc.plan_cond          AS ȯ��ײ��ʬ
                  , proc.next_pro           AS ������
                  , trim(substr(mast.name, 1, 10))           AS ȯ����̾
                  , trim(mast.name)                         AS vendor_name
                  , trim(substr(item.midsc, 1, 18))         AS ����̾
                  , CASE
                          WHEN trim(item.mzist) = '' THEN '---'         --NULL�Ǥʤ��ƥ��ڡ�������ޤäƤ�����Ϥ��졪
                          ELSE substr(item.mzist, 1, 8)
                    END                     AS ���
                  , CASE
                          WHEN trim(item.mepnt) = '' THEN '---'         --NULL�Ǥʤ��ƥ��ڡ�������ޤäƤ�����Ϥ��졪
                          ELSE substr(item.mepnt, 1, 8)
                    END                     AS �Ƶ���
            from
                order_data      AS data
            left outer join
                order_process   AS proc
                                        using(sei_no, order_no, vendor)
            LEFT OUTER JOIN
                order_plan      AS plan     USING (sei_no)
            left outer join
                vendor_master   AS mast
                                        on(data.vendor = mast.vendor)
            left outer join
                miitem          AS item
                                        on(data.parts_no = item.mipn)
            %s
            order by proc.delivery ASC, data.uke_date ASC, data.parts_no ASC
            OFFSET %d LIMIT %d
    ", $search, $offset, PAGE);       // ���� $search �Ǹ���
$res = array();
if (($rows = getResult($query, $res)) < 1) {
    $_SESSION['s_sysmsg'] = 'Ǽ���٤�ǡ���������ޤ���';
    $view = 'NG';
} else {
    $num_res = count($res);
}

// �����Ȥ���Ͽ���å�
if(isset($_POST['comment_input'])) {
    $comment  = array();                // ������
    $sei_no   = array();                // ��¤No.
    $parts_no = array();                // ����No.
    $comment  = $_POST['comment'];
    $sei_no   = $_POST['sei_no'];
    $parts_no = $_POST['c_parts_no'];
    $num = count($sei_no) + 1;
    for($r=1; $r<$num; $r++) {
        $query = sprintf("SELECT comment FROM order_details_comment WHERE sei_no='%s' AND parts_no='%s'", $sei_no[$r], $parts_no[$r]);
        $res_chk = array();
        if ( getResult($query, $res_chk) > 0 ) {    // ��Ͽ���� UPDATE ����
            $query = sprintf("UPDATE order_details_comment SET comment='%s', last_date=CURRENT_TIMESTAMP, last_user='%s' WHERE sei_no='%s' AND parts_no='%s'", $comment[$r], $_SESSION['User_ID'], $sei_no[$r], $parts_no[$r]);
            if (query_affected($query) <= 0) {
                $_SESSION['s_sysmsg'] = "�����Ȥ��ѹ����ԡ�";      // .= �����
                $msg_flg = 'alert';
            } else {
                $_SESSION['s_sysmsg'] = "�����Ȥ���Ͽ���ޤ���"; // .= �����
            }
        } else {                                    // ��Ͽ�ʤ� INSERT ����   
            $query = sprintf("INSERT INTO order_details_comment (sei_no, parts_no, comment, last_date, last_user)
                              VALUES ('%s', '%s', '%s', CURRENT_TIMESTAMP, '%s')",
                                $sei_no[$r], $parts_no[$r], $comment[$r], $_SESSION['User_ID']);
            if (query_affected($query) <= 0) {
                $_SESSION['s_sysmsg'] = "�����Ȥ��ɲä˼��ԡ�";      // .= �����
                $msg_flg = 'alert';
            } else {
                $_SESSION['s_sysmsg'] = "�����Ȥ��ɲä��ޤ�����";    // .= �����
            }
        }
    }
    
}

// 'YY/MM/DD'�ե����ޥåȤΣ�������դ�YYYYMMDD�Σ���˥ե����ޥåȤ����֤���
function format_date8($date8)
{
    if (0 == $date8) {
        $date8 = '--------';    
    }
    if (8 == strlen($date8)) {
        $nen   = substr($date8,0,2);
        $tsuki = substr($date8,3,2);
        $hi    = substr($date8,6,2);
        return '20' . $nen . $tsuki . $hi;
    } else {
        return FALSE;
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
<?=$menu->out_site_java()?>
<?=$menu->out_css()?>

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
    // document.body.focus();   // F2/F12������ͭ���������б�
    document.mhForm.backwardStack.focus();  // �嵭��IE�ΤߤΤ���NN�б�
    // document.form_name.element_name.focus();      // ������ϥե����ब������ϥ����Ȥ򳰤�
    // document.form_name.element_name.select();
}
function init() {
}
function winActiveChk() {
    window.focus();
    return;
    /***** �ʲ��ν�����setInterval()����Ѥ������˻Ȥ� *****/
    if (document.all) {     // IE�ʤ�
        if (document.hasFocus() == false) {     // IE5.5�ʾ�ǻȤ���
            window.focus();
            return;
        }
        return;
    } else {                // NN �ʤ�ȥ�ꥭ�å�
        window.focus();
        return;
    }
    // ����ˡ <body onLoad="setInterval('winActiveChk()',100)">
    // <input type='button' value='TEST' onClick="window.opener.location.reload()">
    // parent.Header.�ؿ�̾() or ���֥�������;
}
function inspection_recourse(order_seq, parts_no, parts_name) {
    if (confirm('�����ֹ桧' + parts_no + '\n\n����̾�Ρ�' + parts_name + " ��\n\n�۵����� ���������ͽ��򤷤ޤ���\n\n�������Ǥ�����")) {
        // �¹Ԥ��ޤ���
        document.inspection_form.order_seq.value = order_seq;
        document.inspection_form.submit();
    } else {
        alert('��ä��ޤ�����');
    }
}
function vendor_code_view(vendor, vendor_name) {
    alert('ȯ���襳���ɡ�' + vendor + '\n\nȯ����̾��' + vendor_name + '\n\n');
}
function input_details(comment) {
        alert('�ƥ���' + comment + '\n\n');
}
function win_open(url) {
    var w = 900;
    var h = 680;
    var left = (screen.availWidth  - w) / 2;
    var top  = (screen.availHeight - h) / 2;
    window.open(url, 'view_win2', 'width='+w+',height='+h+',scrollbars=no,status=no,toolbar=no,location=no,menubar=no,top='+top+',left='+left);
}
// -->
</script>

<!-- �������륷���ȤΥե��������򥳥��� HTML���� �����Ȥ�����Ҥ˽���ʤ��������
<link rel='stylesheet' href='template.css?<?= $uniq ?>' type='text/css' media='screen'>
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
a:hover {
    background-color:   blue;
    color:              white;
}
a {
    color:              blue;
    text-decoration:    none;
}
-->
</style>
</head>
<body onLoad='set_focus()'>
    <center>
<?=$menu->out_title_border()?>
        
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
                    <td nowrap align='center' class='caption_font'>
                        <?= $caption_title . "\n" ?>
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
        <form name='comment_form' action="" method="post">
            <thead>
                <!-- �ơ��֥� �إå�����ɽ�� -->
                <tr>
                    <th class='winbox' nowrap width=' 4%'>No</th>
                    <th class='winbox' nowrap width=' 8%' style='font-size:9.5pt;'>Ǽ ��</th>
                    <th class='winbox' nowrap width=' 8%' style='font-size:9.5pt;'>Ǽ����</th>
                    <th class='winbox' nowrap width=' 7%' style='font-size:9.5pt;'>��¤<BR>�ֹ�</th>
                    <th class='winbox' nowrap width='12%'>�����ֹ�</th>
                    <th class='winbox' nowrap width='15%'>����̾</th>
                    <th class='winbox' nowrap width=' 6%'>��ʸ��</th>
                    <th class='winbox' nowrap width=' 3%' style='font-size:10.5pt;'>��<br>��</th>
                    <th class='winbox' nowrap width='18%'>ȯ����̾</th>
                    <th class='winbox' nowrap width='19%'>������</th>
                </tr>
            </thead>
            <tfoot>
                <!-- ���ߤϥեå����ϲ���ʤ� -->
            </tfoot>
            <tbody>
                <?php
            $i = 0;
            foreach ($res as $rec) {
                $i++;
                    // ��¤No.�������ֹ��ꥳ���Ȥ����
                    $query_c = sprintf("SELECT comment FROM order_details_comment WHERE sei_no='%s' AND parts_no='%s'", $rec['��¤�ֹ�'], $rec['�����ֹ�']);
                    $res_chk_c = array();
                    if ( $rows_c = getResult($query_c, $res_c) < 1 ) {    // ��Ͽ�ʤ�
                        $comment = "";
                    } else {
                        $comment = $res_c[0][0];
                    }
                    echo "<td class='winbox' align='right'  width=' 4%'  bgcolor='#d6d3ce'><span class='pt10b'>", ($i + $offset), "</span></td>\n";
                    echo "<td class='winbox' align='center' width=' 8%'  bgcolor='#d6d3ce'><span class='pt9'>{$rec['Ǽ��']}</span></td>\n";
                    echo "<td class='winbox' align='center' width=' 8%'  bgcolor='#d6d3ce'><span class='pt9'>{$rec['Ǽ����']}</span></td>\n";
                    echo "<td class='winbox' align='center' width=' 7%'  bgcolor='#d6d3ce'><span class='pt9'>{$rec['��¤�ֹ�']}</span></td>\n";
                    echo "<td class='winbox' nowrap align='center' width='12%'  bgcolor='#d6d3ce' onClick='win_open(\"{$menu->out_action('�߸�ͽ��')}?showMenu=CondForm&noMenu=yes&targetPartsNo=" . urlencode($rec['�����ֹ�']) . "\");'>\n";
                    echo "    <a class='link' href='javascript:void(0);' target='_self' style='text-decoration:none;'><span class='pt11b'>{$rec['�����ֹ�']}</a></td>\n";
                    echo "<td class='winbox' nowrap align='left'   width='15%' bgcolor='#d6d3ce'><span class='pt9'>" . mb_convert_kana($rec['����̾'], 'k') . "</span></td>\n";
                    echo "<td class='winbox' align='right'  width=' 6%'  bgcolor='#d6d3ce'><span class='pt9'>" . number_format($rec['��ʸ��'], 0) . "</span></td>\n";
                    echo "<td class='winbox' align='center' width=' 3%'  bgcolor='#d6d3ce' style='font-size:9.5pt;'><span class='pt9'>{$rec['����']}</span></td>\n";
                    echo "<td class='winbox' nowrap align='left' width='18%' bgcolor='#d6d3ce' onClick='vendor_code_view(\"{$rec['ȯ���襳����']}\",\"{$rec['vendor_name']}\")'><span class='pt9'>{$rec['ȯ����̾']}</span></td>\n";
                    if ($comment=="") {                                 // �����Ȥ���Ͽ���ʤ����(���ϥե������ɽ��)
                        echo "<td class='winbox' align='left'   width='19%' bgcolor='#d6d3ce'><span class='pt11'>
                                    <input type='text' name='comment[". $i ."]' size='20' maxlength='10' value='". $comment . "' style='text-align:left; font-size:12pt; font-weight:bold;'>
                                    <input type='hidden' name='sei_no[". $i ."]' value='{$rec['��¤�ֹ�']}'>
                                    <input type='hidden' name='c_parts_no[". $i ."]' value='{$rec['�����ֹ�']}'>
                            </span></td>\n";
                        echo "</tr>\n";
                    } else if (isset($_POST['comment_change'])){        // �����Ȥν����ܥ��󤬲����줿��(���ϥե������ɽ��)
                        echo "<td class='winbox' align='left'   width='19%' bgcolor='#d6d3ce'><span class='pt11'>
                                    <input type='text' name='comment[". $i ."]' size='20' maxlength='10' value='". $comment . "' style='text-align:left; font-size:12pt; font-weight:bold;'>
                                    <input type='hidden' name='sei_no[". $i ."]' value='{$rec['��¤�ֹ�']}'>
                                    <input type='hidden' name='c_parts_no[". $i ."]' value='{$rec['�����ֹ�']}'>
                            </span></td>\n";
                        echo "</tr>\n";
                    } else {                                            // ����ʳ�(�����Ȥ����Ǥ���Ͽ����Ƥ�����)(���ϤǤ��ʤ��褦�ˤ���)
                        echo "<td class='winbox' align='left' width='19%' bgcolor='#d6d3ce'><span class='pt11'>{$comment}</span></td>
                                    <input type='hidden' name='comment[". $i ."]' value='{$comment}'>
                                    <input type='hidden' name='sei_no[". $i ."]' value='{$rec['��¤�ֹ�']}'>
                                    <input type='hidden' name='c_parts_no[". $i ."]' value='{$rec['�����ֹ�']}'>";
                        echo "</tr>\n";
                    }
            }
        ?>
        <td colspan='9'>��</td>
        <td>
            <input type='submit' class='entry_font' name='comment_input' value='��������Ͽ'>
            <input type='submit' class='entry_font' name='comment_change' value='�����Ƚ���'>
        </td>
            </tbody>
        </form>
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
