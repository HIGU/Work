<?php
//////////////////////////////////////////////////////////////////////////////
// ��������ե�����ξȲ� �� �����å���  ������ UKWLIB/W#MIADIM             //
// Copyright(C) 2003-2015 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp       //
// Changed history                                                          //
// 2003/11/27 Created   aden_master_view.php                                //
// 2004/05/12 �����ȥ�˥塼ɽ������ɽ�� �ܥ����ɲ� menu_OnOff($script)�ɲ� //
// 2004/10/21 MenuHeader class ����Ѥ��ƶ��̥�˥塼���ڤ�ǧ���������ѹ�   //
// 2004/10/22 ���̾���(menuOnOff)�����������������̾��ɽ������ǥ쥤������ //
//            �ӣù��֤������ֹ�assy_no(parts_no)�Ǹ����Ǥ��뵡ǽ�ɲ�       //
// 2004/10/23 SC���֤ϣ������ kouji_no�ϣ���ʤΤ� trim(kouji_no)���ɲ�    //
//            ��Ω�����ײ�ǡ�����ɽ����ǽ�ɲ�(�ܥ����ɽ������ɽ����)      //
// 2004/10/28 ��Ω�ײ�on/off���˸��ߤΥڡ�����ݻ������ȼ������=13�Ԥ��ѹ� //
// 2005/01/18 �������칩��Υ��򱦲���ɽ���ɲ� background-image           //
// 2010/02/02 �кꤵ�����ˤ�ꡢ�����о�ǯ������̵���¤���9ǯ���ޤǤ�     //
//            �ѹ���$ken_date��$ken_date_view������                    ��ë //
// 2011/01/07 �кꤵ�����ˤ�ꡢ�����о�ǯ������̵���¤���8ǯ���ޤǤ�     //
//            �ѹ���$ken_date��$ken_date_view������                    ��ë //
// 2015/02/06 A��̤�����ξȲ���ɲ�aden_mikan                          ��ë //
//////////////////////////////////////////////////////////////////////////////
ini_set('error_reporting', E_ALL);          // E_ALL='2047' debug ��
// ini_set('display_errors', '1');             // Error ɽ�� ON debug �� ��꡼���女����
// ini_set('implicit_flush', 'off');           // echo print �� flush �����ʤ�(�٤��ʤ뤿��) CLI CGI��
// ini_set('max_execution_time', 1200);        // ����¹Ի���=20ʬ CLI CGI��
ob_start('ob_gzhandler');                   // ���ϥХåե���gzip����
session_start();                            // ini_set()�μ��˻��ꤹ�뤳�� Script �Ǿ��

require_once ('../../function.php');        // define.php �� pgsql.php �� require_once ���Ƥ���
require_once ('../../tnk_func.php');        // TNK �˰�¸������ʬ�δؿ�(date_offset()�����)
require_once ('../../MenuHeader.php');      // TNK ������ menu class
access_log();                               // Script Name �ϼ�ư����

//////////// ���ߤκǿ�����ϣ�����(��Ư���Ǥ�����)
$yesterday = format_date(date_offset(1));
//////////// �����о�ǯ���η׻�
$ken_date      = date_offset(1) - 80000;                // ������ǯ����
$ken_date_view = format_date(date_offset(1) - 80000);   // ɽ���Ѹ���ǯ����
//////////// �ѥ�᡼�����μ���
if (isset($_REQUEST['aden_no'])) {
    $aden_no = $_REQUEST['aden_no'];
    $_SESSION['aden_no'] = $aden_no;
    $_SESSION['aden_select'] = 'aden_no';
    $aden_select = $_SESSION['aden_select'];
    $aden_assy_no = '';
    $sc_no = '';
} elseif (isset($_REQUEST['aden_assy_no'])) {
    $aden_assy_no = $_REQUEST['aden_assy_no'];
    $_SESSION['aden_assy_no'] = $aden_assy_no;
    $_SESSION['aden_select'] = 'aden_assy_no';
    $aden_select = $_SESSION['aden_select'];
    $aden_no = '';
    $sc_no = '';
} elseif (isset($_REQUEST['sc_no'])) {
    $sc_no = $_REQUEST['sc_no'];
    $_SESSION['sc_no'] = $sc_no;
    $_SESSION['aden_select'] = 'sc_no';
    $aden_select = $_SESSION['aden_select'];
    $aden_no = '';
    $aden_assy_no = '';
} elseif (isset($_REQUEST['aden_mikan'])) {
    $_SESSION['aden_select'] = 'aden_mikan';
    $aden_select = $_SESSION['aden_select'];
    $aden_no = '';
    $aden_assy_no = '';
    $sc_no = '';
} elseif (isset($_REQUEST['aden_mikanc'])) {
    $_SESSION['aden_select'] = 'aden_mikanc';
    $aden_select = $_SESSION['aden_select'];
    $aden_no = '';
    $aden_assy_no = '';
    $sc_no = '';
} elseif (isset($_REQUEST['aden_mikanl'])) {
    $_SESSION['aden_select'] = 'aden_mikanl';
    $aden_select = $_SESSION['aden_select'];
    $aden_no = '';
    $aden_assy_no = '';
    $sc_no = '';
} else {
    $aden_no      = @$_SESSION['aden_no'];
    $aden_assy_no = @$_SESSION['aden_assy_no'];
    $sc_no        = @$_SESSION['sc_no'];
    $aden_select  = @$_SESSION['aden_select'];
}
$aden_no      = str_replace('*', '%', $aden_no);
$aden_assy_no = str_replace('*', '%', $aden_assy_no);
$sc_no        = str_replace('*', '%', $sc_no);

/////////// ���̾���μ���
if ($_SESSION['site_view'] == 'on') {
    $display = 'normal';
} else {
    $display = 'wide';
}
/////////// ��Ω�����ײ��ɽ������
if (isset($_REQUEST['aden_schedule'])) {
    if (!isset($_SESSION['aden_schedule'])) {
        $_SESSION['aden_schedule'] = 'on';
    } else {
        unset($_SESSION['aden_schedule']);
    }
    $_REQUEST['page_keep'] = 'on';      // ���ߤΥڡ�����ݻ�
}
$aden_schedule = @$_SESSION['aden_schedule'];

///// TNK ���ѥ�˥塼���饹�Υ��󥹥��󥹤����
$menu = new MenuHeader(0);                  // ǧ�ڥ����å�0=���̰ʾ� �����=TOP_MENU �����ȥ�̤����

////////////// ����������
$menu->set_site(30, 13);                    // ����=20 ����=13

////////////// �꥿���󥢥ɥ쥹����
// $menu->set_RetUrl(INDUST_MENU);          // �̾�ϻ��ꤹ��ɬ�פϤʤ�
//////////// �����ȥ�̾(�������Υ����ȥ�̾�ȥե�����Υ����ȥ�̾)
$menu->set_title('�� �� �� �� �� �� ��');
//////////// �ƽ����action̾�ȥ��ɥ쥹����
// $menu->set_action('test_template',   INDUST . 'Aden/aden_master_view.php');

//////////// JavaScript Stylesheet File ����cache�ɻ�
$uniq = uniqid('target');

//////////// ���ǤιԿ�
if ($aden_schedule) {
    define('PAGE', '20');
} else {
    define('PAGE', '20');   // old=25
}

//////////// SQL ʸ�� where ��� ���Ѥ���
if ($aden_select == 'aden_no') {
    if ($aden_no != '') {
        //$search = "WHERE aden_no LIKE '{$aden_no}'";
        $search = "WHERE aden.aden_no LIKE '{$aden_no}' AND aden.espoir_deli >= '{$ken_date}'";
    } else {
        //$search = '';
        $search = "WHERE aden.espoir_deli >= '{$ken_date}'";
    }
} elseif ($aden_select == 'aden_assy_no') {
    if ($aden_assy_no != '') {
        //$search = "where aden.parts_no like '{$aden_assy_no}'";
        $search = "where aden.parts_no like '{$aden_assy_no}' AND aden.espoir_deli >= '{$ken_date}'";
    } else {
        //$search = "where aden.parts_no != ''";
        $search = "where aden.parts_no != '' AND aden.espoir_deli >= '{$ken_date}'";
    }
} elseif ($aden_select == 'sc_no') {
    if ($sc_no != '') {
        //$search = "where trim(kouji_no) like '{$sc_no}'";
        $search = "where trim(aden.kouji_no) like '{$sc_no}' AND aden.espoir_deli >= '{$ken_date}'";
    } else {
        //$search = "where trim(kouji_no) LIKE 'SC%'";
        $search = "where trim(aden.kouji_no) LIKE 'SC%' AND aden.espoir_deli >= '{$ken_date}'";
    }
} elseif ($aden_select == 'aden_mikan') {
    $search = "where aden.delivery = 0 AND aden.plan_no <> '' AND aden.kouji_no <> '' AND (sche.plan - sche.cut_plan) > 0 AND p_kubun = 'P' AND aden.espoir_deli >= '{$ken_date}'";
} elseif ($aden_select == 'aden_mikanc') {
    $search = "where aden.delivery = 0 AND aden.parts_no LIKE 'C%' AND aden.kouji_no = '' AND sche.assy_site='01111' AND (sche.plan - sche.cut_plan) > 0 AND aden.espoir_deli >= '{$ken_date}'";
} elseif ($aden_select == 'aden_mikanl') {
    $search = "where aden.delivery = 0 AND aden.parts_no LIKE 'L%' AND aden.kouji_no = '' AND sche.assy_site='01111' AND (sche.plan - sche.cut_plan) > 0 AND aden.espoir_deli >= '{$ken_date}'";
} else {
    $search = '';
}

//////////// ��ץ쥳���ɿ�����     (�оݥǡ����κ������ڡ�������˻���)
if ($aden_select == 'aden_mikan') {
    $query = sprintf('select count(*) from aden_master AS aden LEFT OUTER JOIN assembly_schedule AS sche using(plan_no) %s', $search);
    if ( getUniResult($query, $maxrows) <= 0) {         // $maxrows �μ���
        $_SESSION['s_sysmsg'] .= "��ץ쥳���ɿ��μ����˼���";      // .= ��å��������ɲä���
    }
} elseif ($aden_select == 'aden_mikanc') {
    $query = sprintf('select count(*) from aden_master AS aden LEFT OUTER JOIN assembly_schedule AS sche using(plan_no) %s', $search);
    if ( getUniResult($query, $maxrows) <= 0) {         // $maxrows �μ���
        $_SESSION['s_sysmsg'] .= "��ץ쥳���ɿ��μ����˼���";      // .= ��å��������ɲä���
    }
} elseif ($aden_select == 'aden_mikanl') {
    $query = sprintf('select count(*) from aden_master AS aden LEFT OUTER JOIN assembly_schedule AS sche using(plan_no) %s', $search);
    if ( getUniResult($query, $maxrows) <= 0) {         // $maxrows �μ���
        $_SESSION['s_sysmsg'] .= "��ץ쥳���ɿ��μ����˼���";      // .= ��å��������ɲä���
    }
} else {
    $query = sprintf('select count(*) from aden_master AS aden %s', $search);
    if ( getUniResult($query, $maxrows) <= 0) {         // $maxrows �μ���
        $_SESSION['s_sysmsg'] .= "��ץ쥳���ɿ��μ����˼���";      // .= ��å��������ɲä���
    }
}
$total = number_format($maxrows);
//////////// ɽ�������ȥ����Ƚ������
if ($aden_select == 'aden_no') {
    if ($aden_no == '') {
        $menu->set_caption("<font color='blue'>�ǿ��Σ����ֹ��</font>&nbsp;&nbsp;&nbsp;{$ken_date_view}��{$yesterday}���ߤξ���Ǥ���&nbsp;&nbsp;��׷��={$total}");
        $sort = 'aden.aden_no DESC';     // eda_no ASC ������դ�����٤��ʤ�
    } else {
        $menu->set_caption("��<font color='red'>{$_SESSION['aden_no']}</font>�פǸ����������&nbsp;&nbsp;&nbsp;{$ken_date_view}��{$yesterday}���ߤξ���Ǥ���&nbsp;&nbsp;��׷��={$total}");
        $sort = 'aden.aden_no DESC';
    }
} elseif ($aden_select =='aden_assy_no') {
    if ($aden_assy_no == '') {
        $menu->set_caption("<font color='blue'>���ʡ������ֹ��</font>&nbsp;&nbsp;&nbsp;{$ken_date_view}��{$yesterday}���ߤξ���Ǥ���&nbsp;&nbsp;��׷��={$total}");
        $sort = 'aden.parts_no DESC, aden.delivery DESC';
    } else {
        $menu->set_caption("��<font color='red'>{$_SESSION['aden_assy_no']}</font>�פǸ����������&nbsp;&nbsp;&nbsp;{$ken_date_view}��{$yesterday}���ߤξ���Ǥ���&nbsp;&nbsp;��׷��={$total}");
        $sort = 'aden.parts_no DESC, aden.delivery DESC';
    }
} elseif ($aden_select == 'sc_no') {
    if ($sc_no == '') {
        $menu->set_caption("<font color='blue'>�ǿ��Σӣù��ֽ�</font>&nbsp;&nbsp;&nbsp;{$ken_date_view}��{$yesterday}���ߤξ���Ǥ���&nbsp;&nbsp;��׷��={$total}");
        $sort = 'aden.kouji_no DESC';
    } else {
        $menu->set_caption("��<font color='red'>{$_SESSION['sc_no']}</font>�פǸ����������&nbsp;&nbsp;&nbsp;{$ken_date_view}��{$yesterday}���ߤξ���Ǥ���&nbsp;&nbsp;��׷��={$total}");
        $sort = 'aden.kouji_no DESC';
    }
} elseif ($aden_select == 'aden_mikan') {
    $menu->set_caption("<font color='blue'>�����ײ��������</font>&nbsp;&nbsp;&nbsp;{$ken_date_view}��{$yesterday}���ߤξ���Ǥ���&nbsp;&nbsp;��׷��={$total}");
    $sort = 'sche.crt_date ASC';
} elseif ($aden_select == 'aden_mikanc') {
    $menu->set_caption("<font color='blue'>�����ײ��������</font>&nbsp;&nbsp;&nbsp;{$ken_date_view}��{$yesterday}���ߤξ���Ǥ���&nbsp;&nbsp;��׷��={$total}");
    $sort = 'sche.crt_date ASC';
} elseif ($aden_select == 'aden_mikanl') {
    $menu->set_caption("<font color='blue'>�����ײ��������</font>&nbsp;&nbsp;&nbsp;{$ken_date_view}��{$yesterday}���ߤξ���Ǥ���&nbsp;&nbsp;��׷��={$total}");
    $sort = 'sche.crt_date ASC';
} else {
    $menu->set_caption("<font color='blue'>�ǿ��Σ����ֹ��</font>&nbsp;&nbsp;&nbsp;{$ken_date_view}��{$yesterday}���ߤξ���Ǥ���&nbsp;&nbsp;��׷��={$total}");
    $sort = 'aden.aden_no DESC';
}

//////////// �ڡ������ե��å�����
if ( isset($_REQUEST['forward']) ) {                    // ���Ǥ������줿
    $_SESSION['offset'] += PAGE;
    if ($_SESSION['offset'] >= $maxrows) {
        $_SESSION['offset'] -= PAGE;
        if ($_SESSION['s_sysmsg'] == "") {
            $_SESSION['s_sysmsg'] .= "<font color='yellow'>���ǤϤ���ޤ���</font>";
        } else {
            $_SESSION['s_sysmsg'] .= "<br><font color='yellow'>���ǤϤ���ޤ���</font>";
        }
    }
} elseif ( isset($_REQUEST['backward']) ) {             // ���Ǥ������줿
    $_SESSION['offset'] -= PAGE;
    if ($_SESSION['offset'] < 0) {
        $_SESSION['offset'] = 0;
        if ($_SESSION['s_sysmsg'] == "") {
            $_SESSION['s_sysmsg'] .= "<font color='yellow'>���ǤϤ���ޤ���</font>";
        } else {
            $_SESSION['s_sysmsg'] .= "<br><font color='yellow'>���ǤϤ���ޤ���</font>";
        }
    }
} elseif ( isset($_REQUEST['page_keep']) ) {            // ���ߤΥڡ�����ݻ�����
    $offset = $_SESSION['offset'];
} else {
    $_SESSION['offset'] = 0;                            // ���ξ��ϣ��ǽ����
}
$offset = $_SESSION['offset'];

//////////// ��������Υꥹ�Ⱥ��� Query & �����
$query = sprintf("
        SELECT
            aden.aden_no     as ����,                        -- 0
            aden.eda_no      as ��,                          -- 1
            CASE
                WHEN trim(aden.parts_no) = '' THEN '---'
                ELSE aden.parts_no
            END         as �����ֹ�,                    -- 2
            CASE
                WHEN trim(aden.sale_name) = '' THEN '&nbsp;'
                ELSE trim(aden.sale_name)
            END         as ���侦��̾,                  -- 3
            CASE
                WHEN trim(midsc) IS NULL THEN '---'
                ELSE substr(midsc, 1, 12)
            END         as ��������̾,                  -- 4
            CASE
                WHEN trim(aden.plan_no) = '' THEN '---'
                ELSE aden.plan_no
            END         as �ײ��ֹ�,                    -- 5
            CASE
                WHEN trim(aden.approval) = '' THEN '---'
                ELSE aden.approval
            END         as ��ǧ��,                      -- 6
            CASE
                WHEN trim(aden.ropes_no) = '' THEN '---'
                ELSE aden.ropes_no
            END         as ���ν�,                      -- 7
            CASE
                WHEN trim(aden.kouji_no) = '' THEN '---'
                ELSE aden.kouji_no
            END         as �����ֹ�,                    -- 8
            aden.order_q     as �������,                    -- 9
            aden.order_price as ����ñ��,                    --10
            Uround(aden.order_q * aden.order_price, 0) as ���,   --11
            aden.espoir_deli as ��˾Ǽ��,                    --12
            aden.delivery    as ����Ǽ��,                    --13
            aden.publish_day    AS  ȯ����,                  --14
            
            sche.syuka      AS  ������,                 --15
            sche.chaku      AS  �����,                 --16
            sche.kanryou    AS  ��λ��,                 --17
            (sche.plan - sche.cut_plan - sche.kansei)
                            AS  �ײ��,                 --18
            sche.line_no    AS  �饤��                  --19
        FROM
            aden_master             AS aden
        LEFT OUTER JOIN
            miitem                              ON aden.parts_no=mipn
        LEFT OUTER JOIN
            assembly_schedule       AS sche     using(plan_no)
        %s 
        ORDER BY
            {$sort}
        OFFSET %d LIMIT %d
        
    ", $search, $offset, PAGE);       // ���� $search �Ǹ���
$res   = array();
$field = array();
if (($rows = getResultWithField2($query, $field, $res)) <= 0) {
    $_SESSION['s_sysmsg'] .= '��������Υǡ����������Ǥ��ޤ���';
    header('Location: ' . $menu->out_retUrl());                   // ľ���θƽи������
    exit();
} else {
    $num = count($field);       // �ե�����ɿ�����
}

// SQL�Υ������������ܸ��ѻ����ѹ���'�⥨�顼�ˤʤ�Τ�/�˰���ѹ�
$csv_search = str_replace('\'','/',$search);
$csv_sort = str_replace('\'','/',$sort);

/////////// HTML Header ����Ϥ��ƥ���å��������
$menu->out_html_header();
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=EUC-JP">
<meta http-equiv="Content-Style-Type" content="text/css">
<title><?= $menu->out_title() ?></title>
<?= $menu->out_site_java() ?>
<?= $menu->out_css() ?>

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
.pt9 {
    font-size:      9pt;
    font-weight:    normal;
    font-family:    monospace;
}
.pt10b {
    font-size:      10pt;
    font-weight:    bold;
    font-family:    monospace;
}
.pt11b {
    font-size:      11pt;
    font-weight:    bold;
}
th {
    border-style: solid;
    border-width: 1px;
    border-top-color:       #FFFFFF;
    border-left-color:      #FFFFFF;
    border-right-color:     #999999;
    border-bottom-color:    #999999;
    background-color:yellow;
    color:          blue;
    font-size:      10pt;
    font-weight:    bold;
    font-family:    monospace;
}
.winbox {
    border-style: solid;
    border-width: 1px;
    border-top-color:       #FFFFFF;
    border-left-color:      #FFFFFF;
    border-right-color:     #999999;
    border-bottom-color:    #999999;
    background-color:#d6d3ce;
}
.winbox_mark {
    border-style: solid;
    border-width: 1px;
    border-top-color:       #FFFFFF;
    border-left-color:      #FFFFFF;
    border-right-color:     #999999;
    border-bottom-color:    #999999;
    background-color:#e6e6e6;
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
body {
    background-image:url(<?= IMG ?>t_nitto_logo4.png);
    background-repeat:no-repeat;
    background-attachment:fixed;
    background-position:right bottom;
}
-->
</style>
</head>
<body onLoad='set_focus()'>
    <center>
<?= $menu->out_title_border() ?>
        
        <!----------------- ������ ���� ���� �Υե����� ---------------->
        <table width='100%' cellspacing="0" cellpadding="0" border='0'>
            <form name='page_form' method='get' action='<?= $menu->out_self() ?>'>
                <tr>
                    <td align='left'>
                        <table align='left' border='3' cellspacing='0' cellpadding='0'>
                            <td align='left'>
                                <input class='pt10b' type='submit' name='backward' value='����'>
                            </td>
                        </table>
                    </td>
                    <td nowrap align='center' class='pt11b'>
                        <?= $menu->out_caption() . "\n" ?>
                        <?php if ($aden_schedule) { ?>
                        <input class='pt10b' type='submit' name='aden_schedule' value='��Ω�ײ�OFF' style='color:blue;'>
                        <?php } else { ?>
                        <input class='pt10b' type='submit' name='aden_schedule' value='��Ω�ײ�ON' style='color:blue;'>
                        <?php } ?>
                    </td>
                    <td nowrap align='center' class='pt11b'>
                    <a href='aden_master_csv.php?csvsearch=<?php echo $csv_search ?>&csvsort=<?php echo $csv_sort ?>'>
                        CSV����
                    </a>
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
        <table class='winbox_field' width='100%' bgcolor='#d6d3ce' align='center' cellspacing="0" cellpadding="3" border='1'>
            <THEAD>
                <!-- �ơ��֥� �إå�����ɽ�� -->
                <tr>
                    <th nowrap width='10'>No</th>        <!-- �ԥʥ�С���ɽ�� -->
                <?php
                for ($i=0; $i<$num; $i++) {             // �ե�����ɿ�ʬ���֤�
                    if ( ($i == 4) && ($display == 'normal') ) continue;
                    if ($i >= 15) break;
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
                        <td class='winbox' nowrap style='font-size:10pt; font-weight:bold;' align='right'><?= ($r + $offset + 1) ?></td>    <!-- �ԥʥ�С���ɽ�� -->
                    <?php
                    for ($i=0; $i<$num; $i++) {         // �쥳���ɿ�ʬ���֤�
                        switch ($i) {
                        case 3:
                        case 4:
                            if ( ($i == 4) && ($display == 'normal') ) continue;
                            echo "<td class='winbox' nowrap align='left' style='font-size:9pt;'>{$res[$r][$i]}</td>\n";
                            break;
                        case  9:
                        case 10:
                        case 11:
                            echo "<td class='winbox' nowrap align='right' style='font-size:9pt;'>" . number_format($res[$r][$i], 0) . "</td>\n";
                            break;
                        case 12:
                        case 13:
                            echo "<td class='winbox' nowrap align='center' style='font-size:9pt;'>" . format_date($res[$r][$i]) . "</td>\n";
                            break;
                        case 14:
                            echo "<td class='winbox' nowrap align='center' style='font-size:9pt;'>" . format_date($res[$r][$i]) . "</td>\n";
                            break;
                        case 15:
                        case 16:
                        case 17:
                        case 18:
                        case 19:
                            break;
                        default:
                            echo "<td class='winbox' nowrap align='center' style='font-size:9pt;'>{$res[$r][$i]}</td>\n";
                        }
                    }
                    if ( ($res[$r][15]) && ($aden_schedule) ) {
                        echo "                    </tr>\n";
                        echo "                    <tr>\n";
                        if ($display == 'normal') echo "<td colspan='4' class='winbox' nowrap align='right' style='font-size:9pt;'>&nbsp;</td>\n"; else echo "<td colspan='5' class='winbox' nowrap align='right' style='font-size:9pt;'>&nbsp;</td>\n";
                        echo "<td class='winbox_mark' nowrap align='right' style='font-size:9pt;'>��Ω�ײ� Line</td>\n";
                        echo "<td class='winbox_mark' nowrap align='left' style='font-size:9pt;'>{$res[$r][19]}</td>\n";
                        echo "<td class='winbox_mark' nowrap align='right' style='font-size:9pt;'>�ײ��</td>\n";
                        echo "<td class='winbox_mark' nowrap align='left' style='font-size:9pt;'>", number_format($res[$r][18]), "</td>\n";
                        echo "<td class='winbox_mark' nowrap align='right' style='font-size:9pt;'>������</td>\n";
                        echo "<td class='winbox_mark' nowrap align='right' style='font-size:9pt;'>", substr(format_date($res[$r][15]), 2), "</td>\n";
                        echo "<td class='winbox_mark' nowrap align='right' style='font-size:9pt;'>�����</td>\n";
                        echo "<td class='winbox_mark' nowrap align='right' style='font-size:9pt;'>", substr(format_date($res[$r][16]), 2), "</td>\n";
                        echo "<td class='winbox_mark' nowrap align='right' style='font-size:9pt;'>��λ��</td>\n";
                        echo "<td class='winbox_mark' nowrap align='right' style='font-size:9pt;'>", substr(format_date($res[$r][17]), 2), "</td>\n";
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
</html>
