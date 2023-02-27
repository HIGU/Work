<?php
//////////////////////////////////////////////////////////////////////////////
// ������ư���������ƥ�� ��ž����ɽ ɽ��  Header�ե졼��                   //
// Copyright (C) 2021-2021 norihisa_ooya@nitto-kohki.co.jp                  //
// Changed history                                                          //
// 2021/03/26 Created  equip_work_monichartHeader.php                       //
//////////////////////////////////////////////////////////////////////////////
ini_set('error_reporting', E_ALL);          // E_ALL='2047' debug ��
// ini_set('display_errors', '1');             // Error ɽ�� ON debug �� ��꡼���女����
ob_start('ob_gzhandler');                   // ���ϥХåե���gzip����
session_start();                            // ini_set()�μ��˻��ꤹ�뤳�� Script �Ǿ��
require_once ('../../function.php');        // TNK ������ function
require_once ('../../MenuHeader.php');      // TNK ������ menu class
access_log();                               // Script Name �ϼ�ư����

///// TNK ���ѥ�˥塼���饹�Υ��󥹥��󥹤����
$menu = new MenuHeader();                   // ǧ�ڥ����å�0=���̰ʾ� �����=���å������ �����ȥ�̤����

////////////// ����������
$menu->set_site(40, 10);                    // site_index=40(������˥塼) site_id=10(����ɽ)
////////////// target����
// $menu->set_target('application');           // �ե졼���Ǥ�������target°����ɬ��
$menu->set_target('_parent');               // �ե졼���Ǥ�������target°����ɬ��

///// GET/POST�Υ����å�&����
$mac_no = @$_REQUEST['mac_no'];
if ($mac_no == '') {
    $reload = 'disabled';
} else {
    $reload = '';
    $_SESSION['mac_no'] = $mac_no;
}

if (isset($_REQUEST['factory'])) {
    $factory = $_REQUEST['factory'];
} else {
    $factory = '';
}
///// �ꥯ�����Ȥ�̵����Х��å���󤫤鹩���ʬ��������롣(�̾�Ϥ��Υѥ�����)
if ($factory == '') {
    $factory = @$_SESSION['factory'];
}

//////////// �����ޥ��������������ֹ桦����̾�Υꥹ�Ȥ����
if ($factory == '') {
    $query = "select mac_no                 AS mac_no
                    , substr(mac_name,1,7)  AS mac_name
                from
                    equip_machine_master2
                where
                    survey='Y'
                    and
                    mac_no!=9999
                order by mac_no ASC
    ";
} else {
    $query = "select mac_no                 AS mac_no
                    , substr(mac_name,1,7)  AS mac_name
                from
                    equip_machine_master2
                where
                    survey='Y'
                    and
                    mac_no!=9999
                    and
                    factory='{$factory}'
                order by mac_no ASC
    ";
}
$res = array();
if (($rows = getResult($query, $res)) < 1) {
    $_SESSION['s_sysmsg'] .= "<font color='yellow'>�����ޥ���������Ͽ������ޤ���</font>";
} else {
    $mac_no_name = array();
    for ($i=0; $i<$rows; $i++) {
        $mac_no_name[$i] = $res[$i]['mac_no'] . " " . trim($res[$i]['mac_name']);   // �����ֹ��̾�Τδ֤˥��ڡ����ɲ�
    }
}

///// �������ѿ��ν����
$mac_name   = '';
$plan_no    = '��';
$koutei     = '��';
$parts_no   = '��';
$parts_name = '��';
$parts_mate = '��';
$plan_cnt   = '��';
$view       = 'NG';

if ($mac_no != '') {
    //////////////// �����ޥ��������鵡��̾�����
    $query = "select trim(mac_name) as mac_name from equip_machine_master2 where mac_no={$mac_no} limit 1";
    if (getUniResult($query, $mac_name) <= 0) {
        $mac_name = '  ';   // error���ϵ���̾��֥��
    }
    //////////// �إå�����곫�������Ƚ�λ�����μ���
    $query = "select to_char(str_timestamp AT TIME ZONE 'JST', 'YYYY-MM-DD HH24:MI:SS') as str_timestamp
                , to_char(end_timestamp AT TIME ZONE 'JST', 'YYYY-MM-DD HH24:MI:SS') as end_timestamp
                , plan_no
                , koutei
                , parts_no
                , plan_cnt
            from
                equip_work_log2_header_moni
            where
                mac_no={$mac_no} and work_flg IS TRUE
    ";
    $res_head = array();
    if ( getResult($query, $res_head) <= 0) {
        $_SESSION['s_sysmsg'] .= "<font color='yellow'>{$mac_no}��{$mac_name} �ϱ�ž���Ϥ���Ƥ��ޤ���</font>";
    } else {
        $str_timestamp = $res_head[0]['str_timestamp'];
        $end_timestamp = $res_head[0]['end_timestamp'];
        $plan_no   = $res_head[0]['plan_no'];
        $koutei    = $res_head[0]['koutei'];
        $parts_no  = $res_head[0]['parts_no'];
        $plan_cnt  = $res_head[0]['plan_cnt'];
        $query = "select substr(midsc, 1, 12) as midsc, mzist from miitem where mipn='{$parts_no}'";
        $res_mi = array();
        if ( getResult($query, $res_mi) <= 0) {
            $_SESSION['s_sysmsg'] .= "<font color='yellow'>{$parts_no}�����ʥޥ�������̤��Ͽ��</font>";
        } else {
            $parts_name = $res_mi[0]['midsc'];
            $parts_mate = $res_mi[0]['mzist'];
            $_SESSION['work_mac_no']  = $mac_no;
            $_SESSION['work_plan_no'] = $plan_no;
            $_SESSION['work_koutei']  = $koutei;
            $view = 'OK';
        }
    }
}
// ������ꤷ�Ƥ��뤬�ºݤ�ɽ��OK�ˤʤäƤ��뤫�����å�
if ($view != 'OK') {
    $reload = 'disabled';
} else {
    $reload = '';
}

//////////// �����ȥ�̾(�������Υ����ȥ�̾�ȥե�����Υ����ȥ�̾)
$menu->set_title("{$mac_no}  {$mac_name}  ��ž ���� ɽ����(����)");
//////////// ɽ�������
$menu->set_caption('�����ֹ������');

/////////// HTML Header ����Ϥ��ƥ���å��������
$menu->out_html_header();
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=EUC-JP">
<meta http-equiv="Content-Style-Type" content="text/css">
<meta http-equiv="Content-Script-Type" content="text/javascript">
<title><?php echo $menu->out_title() ?></title>
<?php echo $menu->out_css() ?>
<?php echo $menu->out_site_java() ?>
<style type='text/css'>
<!--
select {
    background-color:   teal;
    color:              white;
}
.sub_font {
    font-size:      11.5pt;
    font-weight:    bold;
    font-family:    monospace;
}
.pick_font {
    font-size:      9.5pt;
    font-weight:    bold;
    font-family: monospace;
}
th {
    font-size:      11.5pt;
    font-weight:    bold;
    font-family:    monospace;
    color:              blue;
    background-color:   yellow;
}
.item {
    position: absolute;
    top:    90px;
    left:   90px;
}
-->
</style>
<form name='MainForm' method='post'>
    <input type='hidden' name='select' value=''>
</form>
<script language='JavaScript'>
<!--
/* ������ϥե�����Υ�����Ȥ˥ե������������� */
function set_focus() {
    // document.body.focus();   // F2/F12������ͭ���������б�
    document.mhForm.backwardStack.focus();  // �嵭��IE�ΤߤΤ���NN�б�
}
// -->
</script>
</head>
<body onLoad='set_focus()'>
    <center>
<?php echo $menu->out_title_border() ?>
        
        <!----------------- ���Ф���ɽ�� ------------------------>
        <table width='100%' bgcolor='#d6d3ce'  border='1' cellspacing='0' cellpadding='1'>
            <tr><td> <!-- ���ߡ�(�ǥ�������) -->
        <table width=100% class='winbox_field' border='1' cellspacing='0' cellpadding='1'>
            <tr class='sub_font'>
                <td class='winbox' align='center' width='13%'>
                    <form name='mac_form' method='post' action='<?php echo $menu->out_self() ?>'>
                    <select style='width:100%;' name='mac_no' class='ret_font' onChange='document.mac_form.submit()'>
                        <?php if ($mac_no == '') echo "<option value=''>�������</option>\n" ?>
                    <?php
                        for ($j=0; $j<$rows; $j++) {
                            if ($mac_no == $res[$j]['mac_no']) {
                                printf("<option value='%s' selected>%s</option>\n", $res[$j]['mac_no'], $mac_no_name[$j]);
                            } else {
                                printf("<option value='%s'>%s</option>\n", $res[$j]['mac_no'], $mac_no_name[$j]);
                            }
                        }
                    ?>
                    </select>
                    </form>
                </td>
                <td class='winbox' align='center' nowrap width='7%'>����No</td>
                <td class='winbox' align='center' nowrap width='9%'><?php echo $parts_no ?></td>
                <td class='winbox' align='center' nowrap width='7%'>����̾</td>
                <td class='winbox pick_font' align='left' nowrap width='15%'><?php echo $parts_name ?></td>
                <td class='winbox' align='center' nowrap width='5%'>���</td>
                <td class='winbox pick_font' align='center' nowrap width='9%'><?php echo $parts_mate ?></td>
                <td class='winbox' align='center' nowrap width='7%'>�ײ�No</td>
                <td class='winbox' align='center' nowrap width='7%'><?php echo $plan_no ?></td>
                <td class='winbox' align='center' nowrap width='5%'>����</td>
                <td class='winbox' align='center' nowrap width='2%'><?php echo $koutei ?></td>
                <td class='winbox' align='center' nowrap width='7%'>�ײ��</td>
                <td class='winbox' align='right'  nowrap width='7%'><?php echo number_format($plan_cnt) ?></td>
            </tr>
        </table>
            </td></tr>
        </table> <!-- ���ߡ�End -->
        <!-- <hr color='797979'> -->
        <table align='left' border='2' cellspacing='0' cellpadding='0'>
            <form action='equip_work_monichartList.php' method='post' target='List'>
                <tr>
                <td>
                    <input type='hidden' name='select' value='OK' >
                    <input style='font-size:10pt; color: red;' type='submit' name='reload' value='����' <?php echo $reload?>>
                </td>
                <td>
                    <input style='font-size:10pt; color:blue;' type='submit' name='backward' value='����' <?php echo $reload?>>
                </td>
                </tr>
            </form>
        </table>
        <table class='item' width='78.6%' bgcolor='#d6d3ce'  border='1' cellspacing='0' cellpadding='2'>
           <tr><td> <!-- ���ߡ�(�ǥ�������) -->
        <table width=100% class='winbox_field' border='1' cellspacing='1' cellpadding='1'>
            <th class='winbox' nowrap width='10%'>No</th>
            <th class='winbox' nowrap width='15%'>ǯ����</th>
            <th class='winbox' nowrap width='15%'>��ʬ��</th>
            <th class='winbox' nowrap width='15%'>����</th>
            <th class='winbox' nowrap width='15%'>��Ω��</th>
            <th class='winbox' nowrap width='20%'>��������(��)</th>
            <th class='winbox' nowrap width='10%'>ʬ:��</th>
        </table> <!----- ���ߡ� End ----->
            </td></tr>
        </table>
        <table align='right' border='2' cellspacing='0' cellpadding='0'>
            <form action='equip_work_monichartList.php' method='post' target='List'>
                <tr><td>
                    <input type='hidden' name='select' value='OK' >
                    <input style='font-size:10pt; color:blue;' type='submit' name='forward' value='����' <?php echo $reload?>>
                </td></tr>
            </form>
        </table>
    </center>
</body>
</html>
<Script Language='JavaScript'>
    document.MainForm.select.value = '<?php echo $view?>';
    document.MainForm.target = 'List';
    document.MainForm.action = 'equip_work_monichartList.php';
    document.MainForm.submit();
</Script>
<?php echo $menu->out_alert_java()?>
<?php ob_end_flush(); // ���ϥХåե���gzip���� END ?>
