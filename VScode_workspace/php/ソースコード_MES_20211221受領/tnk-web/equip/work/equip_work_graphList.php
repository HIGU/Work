<?php
//////////////////////////////////////////////////////////////////////////////
// ������ư���������ƥ�� ��ž ����� ɽ��  Graph���Υե졼��               //
// Copyright (C) 2004-2004 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp      //
// Changed history                                                          //
// 2004/08/09 Created  equip_work_graphList.php                             //
// 2004/08/30 ɽ���ڡ����ֹ���ɲ� $graph_page=$_SESSION['equip_graph_page']//
// 2005/08/20 php5 �ذܹ� =& new �� = new �� new by reference is deprecated //
//////////////////////////////////////////////////////////////////////////////
ini_set('error_reporting', E_STRICT);       // E_STRICT=2048(php5) E_ALL=2047 debug ��
// ini_set('display_errors', '1');             // Error ɽ�� ON debug �� ��꡼���女����
ob_start('ob_gzhandler');                   // ���ϥХåե���gzip����
session_start();                            // ini_set()�μ��˻��ꤹ�뤳�� Script �Ǿ��
require_once ('../equip_function.php');     // ������˥塼 ���� function (function.php��ޤ�)
require_once ('../../MenuHeader.php');      // TNK ������ menu class
require_once ('../EquipGraphClass.php');    // ������Ư���� Graph class
access_log();                               // Script Name �ϼ�ư����

///// TNK ���ѥ�˥塼���饹�Υ��󥹥��󥹤����
$menu = new MenuHeader();                   // ǧ�ڥ����å�0=���̰ʾ� �����=���å������ �����ȥ�̤����

////////////// ����������
$menu->set_site(40, 11);                    // site_index=40(������˥塼) site_id=10(����ɽ)
////////////// target����
$menu->set_target('_parent');               // �ե졼���Ǥ�������target°����ɬ��

$mac_no  = @$_SESSION['work_mac_no'];
$siji_no = @$_SESSION['work_siji_no'];
$koutei  = @$_SESSION['work_koutei'];
if (isset($_REQUEST['select'])) {
    $select = $_REQUEST['select'];
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
        $equip_xtime = 12;
    }
    /////////// ������������դΥ��󥹥��󥹺��� ������180(������٤�360)
    $equip_graph = new EquipGraph($mac_no, $siji_no, $koutei, 180);
    $equip_graph->set_xtime($equip_xtime);      // ����դδ�˾�λ��ּ�������
    $equip_xtime = $equip_graph->out_xtime();   // ����դλ��ּ��Υ������������ͤ����
    // ����դ��ϰ���ν��� DATE TIME �μ���  ����(strDate, strTime, endDate, endTime)
    $graphDateTime = $equip_graph->out_graph_timestamp();
    // $graph_name = ('graph/equip' . session_id() . '.png');
    $graph_name = 'graph/equip_work_graph.png';
    $equip_graph->out_graph($graph_name);
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
                        <option value='6'  <?php if ($equip_xtime==6) echo 'selected';?>>&nbsp;6����</option>
                        <option value='12' <?php if ($equip_xtime==12) echo 'selected';?>>12����</option>
                        <option value='24' <?php if ($equip_xtime==24) echo 'selected';?>>24����</option>
                        <option value='48' <?php if ($equip_xtime==48) echo 'selected';?>>&nbsp;2����</option>
                        <option value='96' <?php if ($equip_xtime==96) echo 'selected';?>>&nbsp;4����</option>
                        <option value='192' <?php if ($equip_xtime==192) echo 'selected';?>>&nbsp;8����</option>
                        <option value='384' <?php if ($equip_xtime==384) echo 'selected';?>>16����</option>
                        <option value='768' <?php if ($equip_xtime==768) echo 'selected';?>>32����</option>
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
<?php if ($select == 'GO') { ?>
<script language='JavaScript'>
<!--
setTimeout('location.replace("equip_work_graphList.php?select=<?=$select?>&equip_xtime=<?=$equip_xtime?>")',10000);      // ������ѣ�����
// -->
</script>
<? } ?>
</html>
<?php ob_end_flush(); // ���ϥХåե���gzip���� END ?>
