<?php
//////////////////////////////////////////////////////////////////////////////
// ���� ���� ��˥塼2 (����)                                               //
// Copyright (C) 2002-2018 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp      //
// Changed history                                                          //
// 2021/03/24 Created   equip_menu.php --> equip_menu_moni.php              //
//////////////////////////////////////////////////////////////////////////////
ini_set('error_reporting', E_ALL);          // E_ALL='2047' debug ��
// ini_set('display_errors', '1');             // Error ɽ�� ON debug �� ��꡼���女����
session_start();                            // ini_set()�μ��˻��ꤹ�뤳�� Script �Ǿ��
ob_start('ob_gzhandler');                   // ���ϥХåե���gzip����

require_once ('equip_function.php');        // �����ط����� (������function.php��ƽФ��Ƥ���)
require_once ('../tnk_func.php');           // menu_bar() �ǻ���
require_once ('../MenuHeader.php');         // TNK ������ menu class
access_log();                               // Script Name �ϼ�ư����

///// TNK ���ѥ�˥塼���饹�Υ��󥹥��󥹤����
$menu = new MenuHeader(0);                  // ǧ�ڥ����å�0=���̰ʾ� �����=''TOP_MENU �����ȥ�̤����

////////////// ����������
$menu->set_site(40, 999);                   // site_index=40(������˥塼2) site_id=999(site�򳫤�)
////////////// �꥿���󥢥ɥ쥹����
// $menu->set_RetUrl(TOP_MENU);
//////////// �����ȥ�̾(�������Υ����ȥ�̾�ȥե�����Υ����ȥ�̾)
// $menu->set_title('�� �� ������ ��˥塼');
if (isset($_REQUEST['factory'])) {
    switch ($_REQUEST['factory']) {
    case 1:
        $_SESSION['factory'] = '1';         // ���� �������˥塼
        $menu->set_title('�� �� ������ ��˥塼');
        break;
    case 2:
        $_SESSION['factory'] = '2';         // ���� �������˥塼
        $menu->set_title('�� �� ������ ��˥塼');
        break;
    case 3:
        $_SESSION['factory'] = '3';         // ���� �������˥塼
        $menu->set_title('�� �� ������ ��˥塼');
        break;
    case 4:
        $_SESSION['factory'] = '4';         // ���� �������˥塼
        $menu->set_title('�� �� ������ ��˥塼');
        break;
    case 5:
        $_SESSION['factory'] = '5';         // ���� �������˥塼
        $menu->set_title('�� �� ������ ��˥塼');
        break;
    case 6:
        $_SESSION['factory'] = '6';         // ���� �������˥塼
        $menu->set_title('�� �� ������ ��˥塼');
        break;
    case 7:
        $_SESSION['factory'] = '7';         // ���� ������(���)��˥塼
        $menu->set_title('�� �� ������(���) ��˥塼');
        break;
    case 8:
        $_SESSION['factory'] = '8';         // ���� ������(SUS)��˥塼
        $menu->set_title('�� �� ������(SUS) ��˥塼');
        break;
    default:
        $_SESSION['factory'] = '';          // ���� �������˥塼
        $menu->set_title('�� �� ������ ��˥塼');
        break;
    }
} else {
    if (isset($_SESSION['factory'])) {
        $factory = $_SESSION['factory'];
    } else {
        $factory = '';
        $_SESSION['factory'] = $factory;
    }
    switch ($factory) {
    case 1:
        $menu->set_title('�� �� ������ ��˥塼');
        break;
    case 2:
        $menu->set_title('�� �� ������ ��˥塼');
        break;
    case 3:
        $menu->set_title('�� �� ������ ��˥塼');
        break;
    case 4:
        $menu->set_title('�� �� ������ ��˥塼');
        break;
    case 5:
        $menu->set_title('�� �� ������ ��˥塼');
        break;
    case 6:
        $menu->set_title('�� �� ������ ��˥塼');
        break;
    case 7:
        $menu->set_title('�� �� ������(���) ��˥塼');
        break;
    case 8:
        $menu->set_title('�� �� ������(SUS) ��˥塼');
        break;
    default:
        $menu->set_title('�� �� ������ ��˥塼');
        break;
    }
}
if (isset($_REQUEST['factory_select'])) {
    // ����Υꥯ�����Ȥ�̵�����POP UP WIN��ɽ����������ե������
    $menu->set_title('�� �� ������ ��˥塼 �ꥯ�����Ȥʤ�');
}

//////////// ɽ�������
$menu->set_caption('���� ��ư���� ��˥塼');
//////////// �ƽ����action̾�ȥ��ɥ쥹����
$menu->set_action('��ž����',           EQUIP2 . 'work/equip_working_disp.php');
$menu->set_action('���ߥ����',         EQUIP2 . 'work/equip_working_disp.php');
$menu->set_action('�ù�����',           EQUIP2 . 'hist/equip_jisseki_select_moni.php');
$menu->set_action('��ž����',           EQUIP2 . 'daily_report_moni/EquipMenu.php');
$menu->set_action('���߲�ưɽ',         EQUIP2 . 'work/equip_work_chart.php');
$menu->set_action('��ž�����',         EQUIP2 . 'work/equip_work_graph.php');
//$menu->set_action('�ù��ؼ�'  ,         EQUIP2 . 'work_mnt/equip_workMnt_Main.php');
$menu->set_action('�ù��ؼ�'  ,         EQUIP2 . 'monitoring/monitoring_Main.php');
// $menu->set_action('ͽ���ݼ�',           EQUIP2 . 'equip_plan_mnt.php');
$menu->set_action('���󥰥��',         EQUIP2 . 'daily_report/equip_report_graph.php');
$menu->set_action('�������塼��',       EQUIP2 . 'plan/equip_plan_graph.php');
$menu->set_action('��ž�����',         EQUIP2 . 'work/equip_work_moni.php?view="����"');
$menu->set_action('��ž�����ޥå�',     EQUIP2 . 'work/equip_work_map.php');
// $menu->set_action('�����ޥ�����',       EQUIP2 . 'master/equip_mac_mst_mnt.php');
$menu->set_action('�����ޥ�����',       EQUIP2 . 'master/equip_macMasterMnt_Main.php');
$menu->set_action('���󥿡��ե�����',   EQUIP2 . 'master/equip_interfaceMaster_Main.php');
$menu->set_action('�����󥿡�',         EQUIP2 . 'master/equip_counterMaster_Main.php');
$menu->set_action('��ߤ����',         EQUIP2 . 'master/equip_stopMaster_Main.php');
$menu->set_action('������interface',    EQUIP2 . 'master/equip_machineInterface_Main.php');
$menu->set_action('���롼�ץޥ�����',   EQUIP2 . 'master/equip_groupMaster_Main.php');
// $menu->set_action('����պ���', EQUIP2 . 'equip_graph_create_all.php');
$menu->set_action('���󥰥��',         EQUIP2 . 'hist/equip_MonthReport_graph.php');

//////////////// �ƥ��󥫡����ѿ��ǥ��åȤ��� �ؿ�������Υ����С��إåɤ򣱲�ǺѤޤ��뤿��
$uniq = uniqid('menu');

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

<!-- �������륷���ȤΥե��������򥳥��� HTML���� �����Ȥ�����Ҥ˽���ʤ�������� -->
<link rel='stylesheet' href='equip_menu.css?<?php echo $uniq ?>' type='text/css' media='screen'>

<script type='text/javascript'>
<!--
function set_focus()
{
    // document.body.focus();   // F2/F12������ͭ���������б�
    // document.mhForm.backwardStack.focus();  // �嵭��IE�ΤߤΤ���NN�б�
}
// -->
</script>
</head>
<body style='overflow:hidden;' onLoad='set_focus()'>
    <center>
<?php echo $menu->out_title_border() ?>
        
        <table width='100%'>
            <tr>
                <td align='center'><img src='<?php echo IMG ?>t_nitto_logo2.gif' width=348 height=83></td>
            </tr>
            <tr>
                <td align='center' class='caption_font'>
                    <?php echo $menu->out_caption(), "\n" ?>
                </td>
            </tr>
        </table>
        
        <br>
        
        <table width='70%' border='0'> <!-- width�Ǵֳ֤�Ĵ�� -->
        <tr>
            <!-- /////////////// left view ////////////// -->
        <td align='center' valign='top'>
            <table border='0'>
<!--
                <tr>
                    <form method='post' action='<?php echo $menu->out_action('��ž����'), '?status=chart' ?>'>
                        <td align='center'>
                            <input type='image' alt='���� ��ž ���� ɽ����(����)�ε��������򤹤�ե�����' border=0
                            src='<?php echo menu_bar("menu_tmp/menu_item_working_disp.png","���߱�ž ɽ���� ��������",11,0)."?$uniq" ?>'>
                            <div>&nbsp;</div>
                        </td>
                    </form>
                </tr>
                
                <tr>
                    <form method='post' action='<?php echo $menu->out_action('���ߥ����'), '?status=graph' ?>'>
                        <td align='center'>
                            <input type='image' alt='���� ��ž ���� ����դε��������򤹤�ե�����' border=0
                            src='<?php echo menu_bar("menu_tmp/menu_item_working_graph.png","���߱�ž ����� ��������",11,0)."?$uniq" ?>'>
                            <div>&nbsp;</div>
                        </td>
                    </form>
                </tr>
-->                
                <tr>
                    <form method='post' action='<?php echo $menu->out_action('�ù�����') ?>'>
                        <td align='center'>
                            <input type='image' alt='�ù����ӾȲ�(����ա�����ɽ)' border=0
                            src='<?php echo menu_bar("menu_tmp/menu_item_equip_jisseki.png","�ù����� (����ա�����ɽ)",11,0)."?$uniq" ?>'>
                            <div>&nbsp;</div>
                        </td>
                    </form>
                </tr>
                
<?php
    //if( $_SESSION['User_ID'] == '300667' || $_SESSION['User_ID'] == '300144') {
?>
                <tr>
                    <form method='post' action='<?php echo $menu->out_action('��ž����') ?>'>
                        <td align='center'>
                            <?php if ($_SESSION['factory'] == '') {?>
                            <img src='<?= IMG ?>menu_item.gif' alt='������⡼�ɤǤϵ�����ž����ϻ��ѤǤ��ޤ��󡪳ƹ�������򤷤Ʋ�������' border='0'>
                            <?php } else { ?>
                            <input type='image' alt='������ž���� ���������ƥ�' border=0
                            src='<?php echo menu_bar("menu_tmp/menu_item_ope_daily_report.png"," �� �� �� ž �� ��",14)."?$uniq" ?>'>
                            <?php } ?>
                            <div>&nbsp;</div>
                        </td>
                    </form>
                </tr>
<?php
//}
?>
<!--
                
                <tr>
                    <form method='post' action='<?php echo $menu->out_action('���󥰥��') ?>'>
                        <td align='center'>
                            <input type='image' alt='��ž������б���������դ�ɽ�����ޤ���' border=0
                            src='<?php echo menu_bar("menu_tmp/menu_item_ope_report_graph.png"," �� ž �� �� �����",14)."?$uniq" ?>'>
                            <div>&nbsp;</div>
                        </td>
                    </form>
                </tr>
                
                <tr>
                    <form method='post' action='<?php echo $menu->out_action('���߲�ưɽ') ?>'>
                        <td align='center'>
                            <input type='image' alt='���߲�ư���Ƥ��뵡���α�ž����ɽ��' border=0
                            src='<?php echo menu_bar("menu_tmp/menu_item_work_chart.png","���߱�ž���� ɽ����", 14, 0)."?$uniq" ?>'>
                            <div>&nbsp;</div>
                        </td>
                    </form>
                </tr>
                
                <tr>
                    <form method='post' action='<?php echo $menu->out_action('��ž�����') ?>'>
                        <td align='center'>
                            <input type='image' alt='���߲�ư���Ƥ��뵡���α�ž�����򥰥��ɽ�����ޤ���' border=0
                            src='<?php echo menu_bar("menu_tmp/menu_item_work_graph.png","���߱�ž���� �����",14, 0)."?$uniq" ?>'>
                            <div>&nbsp;</div>
                        </td>
                    </form>
                </tr>
                
                <tr>
                    <form method='post' action='<?php echo $menu->out_action('�������塼��') ?>'>
                        <td align='center'>
                            <input type='image' alt='�������塼�顼�ξȲ�ڤӥ��ƥʥ�' border=0
                            src='<?php echo menu_bar("menu_tmp/menu_item_plan_mnt.png"," �������塼�顼�ݼ�",14, 0)."?$uniq" ?>'>
                            <div>&nbsp;</div>
                        </td>
                    </form>
                </tr>
                
                <tr>
                    <form method='post' action='<?php echo $menu->out_action('���󥰥��') ?>'>
                        <td align='center'>
                            <input type='image' alt='��١����β�ư���֥���դ�ɽ�����ޤ���' border=0
                            src='<?php echo menu_bar("menu_tmp/menu_item_MonthReport_graph.png","���ư���� �����",14)."?$uniq" ?>'>
                            <div>&nbsp;</div>
                        </td>
                    </form>
                </tr>
                
            <!--
                <tr>
                    <form method='post' action='<?php echo EQUIP_MENU2 ?>'>
                        <td align='center'>
                            <input type='image' name='post' alt='���Υ����ƥ�' border=0 src='<?php echo IMG ?>menu_item.gif'>
                            <div>&nbsp;</div>
                        </td>
                    </form>
                </tr>
            -->
                
            </table>
        </td>
            <!-- /////////////// right view ////////////// -->
        <td align='center' valign='top'>
            <table border='0'>
                <tr>
                    <form method='post' action='<?php echo $menu->out_action('�ù��ؼ�') ?>'> <!-- <?php echo IMG ?>menu_item_equip_seizou.gif -->
                        <td align='center'>
                            <input type='image' alt='�ù��ؼ����ϡ��Խ�' border=0
                            src='<?php echo menu_bar("menu_tmp/menu_item_working_mnt.png"," �ù��ؼ����ϡ��Խ�",14)."?$uniq" ?>'>
                            <div>&nbsp;</div>
                        </td>
                    </form>
                </tr>
                
                <tr>
                    <form method='post' action='<?php echo $menu->out_action('��ž�����') ?>'>
                        <td align='center'>
                            <input type='image' alt='������Ư���������ƥ����Ͽ����Ƥ��뵡����������������ɽ�����ޤ���' border=0
                            src='<?php echo menu_bar("menu_tmp/menu_item_work_all.png","���߱�ž��ΰ���ɽ��",14)."?$uniq" ?>'>
                            <div>&nbsp;</div>
                        </td>
                    </form>
                </tr>

<!--
                <tr>
                    <form method='post' action='<?php echo $menu->out_action('��ž�����ޥå�') ?>'>
                        <td align='center'>
                            <input type='image' name='post' alt='�����̥ޥå�(�쥤������)���ǲ�ư������ɽ�����ޤ���' border=0
                            src='<?php echo menu_bar("menu_tmp/menu_item_equip_map4.png","�����̥쥤������ɽ��",14, 0)."?$uniq" ?>'>
                            <div>&nbsp;</div>
                        </td>
                    </form>
                    <!--
                    <form method='post' action='<?php echo $menu->out_action('����պ���') ?>'>
                        <td align='center'>
                            <input type='image' alt='�ù��ؼ��� �̤Υ���հ�����' border=0
                            src='<?php echo menu_bar("menu_tmp/menu_item_equip_graph_create_all.png","�ؼ��̥���հ�����",14)."?$uniq" ?>'>
                            <div>&nbsp;</div>
                        </td>
                    </form>
                    --><!--
                </tr>
-->
                
                <tr>
                    <form method='post' action='<?php echo $menu->out_action('�����ޥ�����') ?>'> <!-- <?php echo IMG ?>menu_item_equip_mac_mst.gif -->
                        <td align='center'>
                            <input type='image' alt='�����������ޥ������ݼ�' border=0
                            src='<?php echo menu_bar("menu_tmp/menu_item_mac_mst_mnt.png"," �����ޥ��������ݼ�", 14, 0)."?$uniq" ?>'>
                            <div>&nbsp;</div>
                        </td>
                    </form>
                </tr>
                
                <tr>
                    <form method='post' action='<?php echo $menu->out_action('���󥿡��ե�����') ?>'> <!-- <?php echo IMG ?>menu_item_equip_mac_mst.gif -->
                        <td align='center'>
                            <input type='image' alt='�������������󥿡��ե����� �ޥ������ݼ�' border=0
                            src='<?php echo menu_bar("menu_tmp/menu_item_interfaceMaster.png","  ���󥿡��ե�����", 14, 0)."?$uniq" ?>'>
                            <div>&nbsp;</div>
                        </td>
                    </form>
                </tr>
                
                <tr>
                    <form method='post' action='<?php echo $menu->out_action('�����󥿡�') ?>'>
                        <td align='center'>
                            <input type='image' alt='���������� �����󥿡� �ޥ������ݼ�' border=0
                            src='<?php echo menu_bar("menu_tmp/menu_item_counterMaster.png"," �����󥿡� �ޥ�����", 14, 0)."?$uniq" ?>'>
                            <div>&nbsp;</div>
                        </td>
                    </form>
                </tr>
                
                <tr>
                    <form method='post' action='<?php echo $menu->out_action('��ߤ����') ?>'>
                        <td align='center'>
                            <input type='image' alt='���������� ��ߤ���� �ޥ������ݼ�' border=0
                            src='<?php echo menu_bar("menu_tmp/menu_item_stopMaster.png"," ��ߤ���� �ޥ�����", 14, 0)."?$uniq" ?>'>
                            <div>&nbsp;</div>
                        </td>
                    </form>
                </tr>
                
                <tr>
                    <form method='post' action='<?php echo $menu->out_action('������interface') ?>'>
                        <td align='center'>
                            <input type='image' alt='������Υ��󥿡��ե����� �ޥ������Ȳ�ڤ��ݼ�' border=0
                            src='<?php echo menu_bar("menu_tmp/menu_item_machineInterface.png","�����ȥ��󥿡��ե�����", 13, 0)."?$uniq" ?>'>
                            <div>&nbsp;</div>
                        </td>
                    </form>
                </tr>
<!--                
                <tr>
                    <form method='post' action='<?php echo $menu->out_action('���롼�ץޥ�����') ?>'>
                        <td align='center'>
                            <input type='image' alt='�����ʬ(���롼��) �ޥ������Ȳ�ڤ��ݼ�' border=0
                            src='<?php echo menu_bar("menu_tmp/menu_item_groupMaster.png"," �����ʬ(���롼��)", 14, 0)."?$uniq" ?>'>
                            <div>&nbsp;</div>
                        </td>
                    </form>
                </tr>
                
            <!--
                <tr>
                    <form method='post' action='<?php echo EQUIP_MENU2 ?>'>
                        <td align='center'>
                            <input type='image' name='post' alt='���Υ����ƥ�' border=0 src='<?php echo IMG ?>menu_item.gif'>
                            <div>&nbsp;</div>
                        </td>
                    </form>
                </tr>
            -->
                
            <!--
                <tr>
                    <form method='post' action='/test/test_gantt_graph.php'>
                        <td align='center'>
                            <input type='image' name='post' alt='����γ�ȯͽ��ɽ' border=0 src='<?php echo IMG ?>menu_item.gif'>
                            <div>&nbsp;</div>
                        </td>
                    </form>
                </tr>
            -->
                
            </table>
        </td>
        </tr>
        </table>
    </center>
</body>
<?php echo $menu->out_site_java() ?>
<?php echo $menu->out_alert_java() ?>
</html>
<?php
ob_end_flush();                 // ���ϥХåե���gzip���� END
?>
