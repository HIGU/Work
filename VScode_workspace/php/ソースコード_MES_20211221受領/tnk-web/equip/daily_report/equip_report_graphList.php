<?php
//////////////////////////////////////////////////////////////////////////////
// ������ư���������ƥ�� ��ž�����б� ����� ɽ��  Graph���Υե졼��       //
// Copyright (C) 2004-2005 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp      //
// Changed history                                                          //
// 2004/08/24 Created  equip_report_graphList.php                           //
// 2004/08/30 ɽ���ڡ����ֹ���ɲ� $graph_page=$_SESSION['equip_graph_page']//
// 2005/06/24 ����դι⤵���ѹ�default��=350��370��set_graphWH()Y����������//
// 2005/09/30 �����̾��session()ID��equipReport_' . $_SESSION['User_ID']�� //
//////////////////////////////////////////////////////////////////////////////
ini_set('error_reporting', E_ALL);          // E_ALL='2047' debug ��
// ini_set('display_errors', '1');             // Error ɽ�� ON debug �� ��꡼���女����
ob_start('ob_gzhandler');                   // ���ϥХåե���gzip����
session_start();                            // ini_set()�μ��˻��ꤹ�뤳�� Script �Ǿ��
require_once ('../equip_function.php');     // ������˥塼 ���� function (function.php��ޤ�)
require_once ('../../MenuHeader.php');      // TNK ������ menu class
require_once ('../EquipGraphClass_report.php');    // ������Ư���� Graph class
access_log();                               // Script Name �ϼ�ư����

///// TNK ���ѥ�˥塼���饹�Υ��󥹥��󥹤����
$menu = new MenuHeader();                   // ǧ�ڥ����å�0=���̰ʾ� �����=���å������ �����ȥ�̤����

////////////// ����������
$menu->set_site(40, 7);                     // site_index=40(������˥塼2) site_id=7(������ž����)
////////////// target����
$menu->set_target('_parent');               // �ե졼���Ǥ�������target°����ɬ��

$mac_no   = @$_SESSION['work_mac_no'];
$str_date = @$_SESSION['work_date'];
if (isset($_POST['select'])) {
    $select = $_POST['select'];
} else {
    $select = 'NG';
}
if ($mac_no == '') {
    $select = 'NG';
}
if (isset($_REQUEST['equip_xtime'])) {
    $_SESSION['equip_xtime'] = $_REQUEST['equip_xtime'];
}

if ($select == 'GO') {
    if (isset($_SESSION['equip_xtime'])) {
        $equip_xtime = $_SESSION['equip_xtime'];
        unset($_SESSION['equip_xtime']);
    } else {
        $equip_xtime = 24;
    }
    /////////// ������������դΥ��󥹥��󥹺��� ������180(������٤�360)
    $equip_graph = new EquipGraphReport($mac_no, $str_date);
    $equip_graph->set_xtime($equip_xtime);
    // ����դ��ϰ���ν��� DATE TIME �μ���  ����(strDate, strTime, endDate, endTime)
    $graphDateTime = $equip_graph->out_graph_timestamp();
    $equip_graph->set_graphWH(670, 370);    // default=670, 350
    $graph_name = ('graph/equipReport_' . $_SESSION['User_ID'] . '.png');
    $equip_graph->out_graph($graph_name, 'yes');
    $graph_page = $_SESSION['equip_graph_page'];    // ɽ���ڡ������μ���
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
<?php if ($_SESSION['s_sysmsg'] != '') echo $menu->out_site_java() ?>
<?= $menu->out_css() ?>
<style type='text/css'>
<!--
th {
    font-size:      11.5pt;
    font-weight:    bold;
    font-family:    monospace;
}
.item {
    position: absolute;
    /* top: 100px; */
    left: 90px;
}
.msg {
    position: absolute;
    top:  100px;
    left: 350px;
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
<script language='JavaScript'>
function init() {
<?php if ($select == 'OK') { ?>
    document.MainForm.submit();
<?php } ?>
}
</script>
<?php if ($select == 'OK') { ?>
<form name='MainForm' action='<?= $menu->out_self() ?>' method='post'>
    <input type='hidden' name='select' value='GO'>
</form>
<?php } ?>
</head>
<body onLoad='init()'>
    <center>
        <?php if ($select == 'NG') { ?>
        <table border='0' class='msg'>
            <tr>
                <td>
                    <b style='color: teal;'>���������򤷤Ʋ�������</b>
                </td>
            </tr>
        </table>
        <?php } elseif ($select == 'OK') { ?>
        <table border='0' class='msg'>
            <tr>
                <td>
                    <b style='color: blue;'>������Ǥ������Ԥ���������</b>
                </td>
            </tr>
        </table>
        <?php } else { ?>
        <!-------------- �����ɽ���Υڡ�������ȥ������ -------------->
        <table bgcolor='#d6d3ce'  border='1' cellspacing='0' cellpadding='1'>
           <tr><td> <!-- ���ߡ�(�ǥ�������) -->
        <table class='winbox_field' width='100%' border='0' cellspacing='0' cellpadding='2'>
            <tr>
                <td class='winbox' style='font-size:11pt;'><?=$graphDateTime['strDate'], ' ', $graphDateTime['strTime']?></td>
                <td class='winbox' style='font-size:11pt;'>��</td>
                <td class='winbox' style='font-size:11pt;'><?=$graphDateTime['endDate'], ' ', $graphDateTime['endTime']?></td>
                <td class='winbox' style='font-size:11pt;'>
                    <?php if ($equip_xtime<=24) echo $equip_xtime, '����'; else echo ($equip_xtime/24), '����';?>
                    ���ϰϤ�ɽ��
                </td>
                <td class='winbox' style='font-size:11pt;'>ɽ���ڡ����ֹ桧<?=$graph_page?></td>
            <!--
                <td>
                <form name='page_ctl' method='post' action='<?=$menu->out_self()?>' target='_self'>
                    <input class='pt11b' type='submit' name='backward' value='���ڡ���' disabled>
                    <select name='equip_xtime' class='ret_font'>
                        <?=$equip_graph->out_select_xtime($equip_xtime)?>
                    </select>
                    <input class='pt11b' type='submit' name='forward' value='���ڡ���' disabled>
                    <input type='hidden' name='select' value='OK'>
                </form>
                </td>
            -->
            </tr>
        </table> <!----- ���ߡ� End ----->
            </td></tr>
        </table>
        <table width='100%' border='0'>
            <tr><td align='center'>
            <?= "<img src='", $graph_name, "?", uniqid(rand(),1), "' alt='�ù��� ���� �����' border='0'>\n"; ?>
            </td></tr>
        </table>
        <?=$equip_graph->out_state_summary()?>
        <?php } ?>
    </center>
</body>
</html>
<?php ob_end_flush(); // ���ϥХåե���gzip���� END ?>
