<?php
//////////////////////////////////////////////////////////////////////////////
// ������ư���������ƥ�� �������塼�� ����ȥ���� ����  Header�ե졼��  //
// Copyright (C) 2004-2018 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp      //
// Changed history                                                          //
// 2004/09/02 Created  equip_plan_graphHeader.php                           //
// 2005/06/24 F2/F12��������뤿����б��� JavaScript�� set_focus()���ɲ�   //
// 2018/05/18 ��������ɲá������ɣ�����Ͽ����Ū��7���ѹ�            ��ë //
//////////////////////////////////////////////////////////////////////////////
ini_set('error_reporting', E_ALL);          // E_ALL='2047' debug ��
// ini_set('display_errors', '1');             // Error ɽ�� ON debug �� ��꡼���女����
ob_start('ob_gzhandler');                   // ���ϥХåե���gzip����
session_start();                            // ini_set()�μ��˻��ꤹ�뤳�� Script �Ǿ��
require_once ('../equip_function.php');     // TNK ������ function
require_once ('../../MenuHeader.php');      // TNK ������ menu class
access_log();                               // Script Name �ϼ�ư����

///// TNK ���ѥ�˥塼���饹�Υ��󥹥��󥹤����
$menu = new MenuHeader();                   // ǧ�ڥ����å�0=���̰ʾ� �����=���å������ �����ȥ�̤����

////////////// ����������
$menu->set_site(40, 8);                     // site_index=40(������˥塼) site_id=8(�������塼�顼)
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

/////////// ����դ�X���λ����ϰϤ����
if (isset($_REQUEST['equip_xtime'])) {
    $_SESSION['equip_xtime'] = $_REQUEST['equip_xtime'];
} else {
    $_SESSION['equip_xtime'] = 'max';
}
$equip_xtime = $_SESSION['equip_xtime'];

if (isset($_REQUEST['reset_page'])) {
    @$_SESSION['equip_graph_page'] = 1;     // �����
}

///// �������ѿ��ν����
$view = 'NG';

//////////// �����ޥ��������������ֹ桦����̾�Υꥹ�Ȥ����
if (isset($_REQUEST['factory'])) {
    $factory = $_REQUEST['factory'];
} else {
    $factory = '';
}
///// �ꥯ�����Ȥ�̵����Х��å���󤫤鹩���ʬ��������롣(�̾�Ϥ��Υѥ�����)
if ($factory == '') {
    $factory = @$_SESSION['factory'];
}
if ($factory == '') {
    $query = "select mac_no                 AS mac_no
                    -- , substr(mac_name,1,7)  AS mac_name
                    , mac_name
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
                    -- , substr(mac_name,1,7)  AS mac_name
                    , mac_name
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
$res_sel = array();
if (($rows_sel = getResult($query, $res_sel)) < 1) {
    $_SESSION['s_sysmsg'] .= "<font color='yellow'>�����ޥ���������Ͽ������ޤ���</font>";
} else {
    $mac_no_name = array();
    for ($i=0; $i<$rows_sel; $i++) {
        $mac_no_name[$i] = $res_sel[$i]['mac_no'] . " " . trim($res_sel[$i]['mac_name']);   // �����ֹ��̾�Τδ֤˥��ڡ����ɲ�
    }
}

if ($mac_no != '') {
    //////////////// �����ޥ��������鹩���ʬ���᡼�����������᡼����̾�����
    $query = "select factory
                    ,maker_name
                    ,maker
                    ,mac_name
                from
                    equip_machine_master2
                where
                    mac_no={$mac_no}
                limit 1
    ";
    $res = array();
    if (getResult($query, $res) <= 0) {
        $factory = '��'; $maker_name = '��'; $maker = '��'; $mac_name = '��'; // error���ϥ֥��
    } else {
        $factory = $res[0]['factory'];
        $maker_name = $res[0]['maker_name'];
        $maker   = $res[0]['maker'];
        $mac_name = $res[0]['mac_name'];
        switch ($factory) {
        case 1:
            $factory = '������';
            break;
        case 2:
            $factory = '������';
            break;
        case 3:
            $factory = '������';
            break;
        case 4:
            $factory = '������';
            break;
        case 5:
            $factory = '������';
            break;
        case 6:
            $factory = '������';
            break;
        case 7:
            $factory = '������';
            break;
        default:
            $factory = '̤���';
            break;
        }
        $view = 'OK';
    }
}

//////////// �����ȥ�̾(�������Υ����ȥ�̾�ȥե�����Υ����ȥ�̾)
if ($view == 'NG') {
    $menu->set_title('�������塼�顼�ξȲ�ڤӥ��ƥʥ�');
} else {
    $menu->set_title("{$mac_no}��{$mac_name}���������塼�顼�ξȲ�ڤӥ��ƥʥ�");
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
<?= $menu->out_css() ?>
<?= $menu->out_site_java() ?>
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
    font-family:    monospace;
}
th {
    font-size:          10.5pt;
    font-weight:        bold;
    font-family:        monospace;
    color:              blue;
    /* background-color:   yellow; */
}
.item {
    position:       absolute;
    top:            90px;
    left:           90px;
}
.table_font {
    font-size:      11.5pt;
    font-family:    monospace;
}
.ext_font {
    /* background-color:   yellow; */
    color:              blue;
    font-size:          10.5pt;
    font-weight:        bold;
    font-family:        monospace;
}
.winbox {
    border-style: solid;
    border-width: 1px;
    border-top-color: #FFFFFF;
    border-left-color: #FFFFFF;
    border-right-color: #DFDFDF;
    border-bottom-color: #DFDFDF;
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
    // document.mhForm.backwardStack.focus();  // �嵭��IE�ΤߤΤ���NN�б�
    // document.mac_form.mac_no.focus();  // �������륭���ǵ������ѹ������褦�ˤ���
}
// -->
</script>
</head>
<body onLoad='set_focus()'>
    <center>
<?= $menu->out_title_border() ?>
        
        <!----------------- ���Ф���ɽ�� ------------------------>
        <table bgcolor='#d6d3ce'  border='1' cellspacing='0' cellpadding='1'>
            <tr><td> <!-- ���ߡ�(�ǥ�������) -->
        <table width=100% bordercolordark='white' bordercolorlight='#bdaa90' bgcolor='#d6d3ce' align='center' border='1' cellspacing='0' cellpadding='1'>
            <tr class='sub_font'>
                <td align='center' width='100'>
                    <form name='mac_form' method='post' action='<?= $menu->out_self() ?>'>
                        <select name='mac_no' class='ret_font' onChange='document.mac_form.submit()'>
                        <?php if ($mac_no == '') echo "<option value=''>��������</option>\n" ?>
                        <?php
                        for ($j=0; $j<$rows_sel; $j++) {
                            if ($mac_no == $res_sel[$j]['mac_no']) {
                                printf("<option value='%s' selected>%s</option>\n", $res_sel[$j]['mac_no'], $mac_no_name[$j]);
                            } else {
                                printf("<option value='%s'>%s</option>\n", $res_sel[$j]['mac_no'], $mac_no_name[$j]);
                            }
                        }
                        ?>
                        </select>
                        <input type='hidden' name='reset_page' value=''>
                    </form>
                </td>
                <?php if ($view == 'OK') { ?>
                <td align='center' nowrap width='100'><?=$factory?></td>
                <td align='center' nowrap width='100'><?=$maker_name?></td>
                <td align='center' nowrap width='150'><?=$maker?></td>
                <?php } ?>
            </tr>
        </table>
            </td></tr>
        </table> <!-- ���ߡ�End -->
        
    </center>
</body>
</html>
<Script Language='JavaScript'>
    document.MainForm.select.value = '<?=$view?>';
    document.MainForm.target = 'List';
    document.MainForm.action = 'equip_plan_graphList.php';
    document.MainForm.submit();
</Script>
<?=$menu->out_alert_java()?>
<?php ob_end_flush(); // ���ϥХåե���gzip���� END ?>
