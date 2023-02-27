<?php
//////////////////////////////////////////////////////////////////////////////
// �������칩�� �����ƥ���� ��˥塼                                       //
// Copyright(C) 2001-2015 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp       //
// Changed history                                                          //
// 2001/07/07 Created   system_menu.php                                     //
// 2002/08/08 ���å����������ɲ�                                          //
// 2002/08/27 �ե졼���б�                                                  //
// 2002/12/03 �����ȥ�˥塼�б����� site_id = 99 ��                        //
// 2002/12/27 function menu_bar() �ˤ���˥塼������ư����                //
// 2003/02/14 ���ط��˥塼 �Υե���Ȥ� style �ǻ�����ѹ�                //
//                              �֥饦�����ˤ���ѹ�������ʤ��ͤˤ���      //
// 2003/06/30 ��ȯ�ѥƥ�ץ졼�ȥե������ɽ�����˥塼���ɲ�              //
// 2003/07/15 class patTemplate()��Ƴ�� ��˥塼���ɲ�                      //
// 2003/12/12 define���줿����ǥǥ��쥯�ȥ�ȥ�˥塼����Ѥ��ƴ�������    //
//            ob_start('ob_gzhandler') ���ɲ�                               //
// 2004/02/13 index1.php��index.php���ѹ�(index1��authenticate���ѹ��Τ���) //
// 2004/03/31 header("Location: http:" . WEB_HOST) --->                     //
//                            header("Location: " . H_WEB_HOST . TOP_MENU)  //
// 2004/04/23 /test/patTemplate ������������ system/�ʲ���patexample1�ɲ� //
// 2004/06/10 ��˥塼�إå������ɲä�view_user($_SESSION['User_ID'])���ɲ� //
// 2004/07/07 DATA-SUM����������¹Ի���JavaScript�ǳ�ǧ�Υ�å��������ɲ�  //
// 2004/07/20 MenuHeader class ����Ѥ��ƶ��̥�˥塼��ǧ���������ѹ�       //
//            $uniq=uniqid('menu')���ɲä�����������Υ����С��إåɤ򸺾�  //
// 2004/10/12 php4 �� php5 �إեå����Υ��ѹ�                             //
// 2004/12/25 style='overflow:hidden;' (-xyξ��)���ɲ�                      //
// 2005/01/14 F2/F12������ͭ���������б��Τ��� document.body.focus()���ɲ�  //
// 2005/07/26 <div style='position:absolute; left:15%; bottom:0%;'><img�ɲ� //
// 2006/03/07 ��Ω����Ͽ����������� (AS/400��DB�����С�) ���ɲ�            //
// 2006/03/08 NK����ñ����������� (AS/400��DB�����С�) ���ɲ�              //
// 2006/03/08 ��Ω���������������� (AS/400��DB�����С�) ���ɲ�            //
// 2006/09/04 ���̸��¥ޥ����Խ���˥塼���ɲ�                              //
// 2007/03/31 ������ž����Υǥ��쥯�ȥ���ѹ� equip_report/ ��             //
// 2007/04/04 ������ž����Υ��åץإ�ץ�å��������꡼���Ǥصڤ������ѹ�//
// 2007/04/07 data_sum �Υǥ��쥯�ȥ�� data_sum/ ���ѹ�                    //
// 2007/04/21 php�Υ��硼�ȥ��åȥ�����ɸ�ॿ�����ѹ�(�侩�ͤ�)             //
// 2007/05/01 $menu->out_alert_java() �� out_alert_java(false) ���ѹ�       //
// 2007/05/07 ������ž����μ�ư����ȼ����å��������ѹ�                    //
// 2007/05/15 ������������Υǥ��쥯�ȥ��ѹ� daily/ �� assembly_completion/ //
// 2007/05/16 ��Ω������������Υǥ��쥯�ȥ��ѹ� daily/ �� assembly_time/   //
// 2007/05/17 ����ʬ�λ��߸˥��ޥ꡼(��ͭ������ޤ�)������������ɲ�      //
// 2007/06/15 �����μ�ư��������˥塼���ɲ�                              //
// 2007/09/11 $menu =& new MenuHeader �� $menu = new MenuHeader ���ѹ�      //
// 2007/09/22 ��˥塼������ѹ��������ץ����Υƥ��ȥ�˥塼���ɲ�    //
// 2007/12/06 �����ե����ॳ��С��ȥ�˥塼���ɲ�                          //
// 2007/12/10 �����ץ�����ν񡦥桼�����ե�����ι�����˥塼���ɲ�      //
// 2008/09/29 ������ץ�ǡ���������˥塼���ɲ�                       ��ë //
// 2009/08/03 ���ǡ����Ƽ����������˥塼���ɲ�                          //
//            AS������˥塼 19��60�ǥǡ�����������Ƥ���¹Ԥ��뤳��  ��ë //
// 2009/12/18 AS400��ߡ������������ǡ����Ƽ�������                       //
//            as400get_ftp_re.php���ɲ�                                     //
// 2009/12/25 ��ư���ʥޥ������������ɲ�                               ��ë //
// 2010/01/14 ���ʥޥ������ι�����ư���Ȥ߹�����Τǡ����ξ���          //
//            ��������ǡ����κƼ������ȹ��ߡ�AS�Ǥ��������������          //
//            8:45���ޤǤ˽���ʤ��ä����¹�                         ��ë //
// 2010/03/05 ��ʿ��ñ���������ɲá�AS����ǡ����������                    //
// 2015/03/12 ��������ǡ���������ǡ����κƼ����Υ����Ȥ��ѹ�            //
//            ����ǡ����κƼ����������������ǡ����κƼ�������������    //
//            ���������̡��˹Ԥ��褦�ѹ������١�                            //
//            ή��� AS���좪����ǡ����Ƽ���                               //
//                         ��09��������������������Ե���ˤʤ�ޤ��Ԥ�     //
//                         ����������ǡ����Ƽ�����»�                     //
//               ��daily_cli.php��ή������ˡ��Ե���ˤʤäƤ�������� ��ë //
//////////////////////////////////////////////////////////////////////////////
// ini_set('error_reporting', E_ALL | E_STRICT);
// ini_set('error_reporting', E_ALL);          // E_ALL='2047' debug ��
ini_set('display_errors', '1');             // Error ɽ�� ON debug �� ��꡼���女����
session_start();                            // ini_set()�μ��˻��ꤹ�뤳�� Script �Ǿ��
ob_start('ob_gzhandler');                   // ���ϥХåե���gzip����

require_once ('../function.php');           // TNK ������ function
require_once ('../MenuHeader.php');         // TNK ������ menu class
require_once ('../tnk_func.php');           // menu_bar() �ǻ���
access_log();                               // Script Name �ϼ�ư����

///// TNK ���ѥ�˥塼���饹�Υ��󥹥��󥹤����
$menu = new MenuHeader(3, TOP_MENU);        // ǧ�ڥ����å�3=admin�ʾ� �����=TOP_MENU �����ȥ�̤����

////////////// ����������
$menu->set_site(99, 999);           // site_index=99(�����ƥ������˥塼) site_id=999(�ҥ�˥塼����)
////////////// �꥿���󥢥ɥ쥹����
// $menu->set_RetUrl(TOP_MENU);
//////////// �����ȥ�̾(�������Υ����ȥ�̾�ȥե�����Υ����ȥ�̾)
$menu->set_title('Administrator System Menu');
//////////// ɽ�������
$menu->set_caption('System Menu');
//////////// �ƽ����action̾�ȥ��ɥ쥹����
$menu->set_action('�������',       SYS . 'system_daily.php');
$menu->set_action('color',          SYS . 'color_check_input.php');
$menu->set_action('�����',       SYS . 'system_getuji_select.php');
$menu->set_action('systemDB',       SYS . 'database/system_db.php');
$menu->set_action('as400file',      SYS . 'system_as400_file.php');
$menu->set_action('free_chk',       SYS . 'top-free/free_chk.php');
$menu->set_action('top_chk',        SYS . 'top-free/top_chk.php');
$menu->set_action('template',       SYS . 'templateSample/template.php');
$menu->set_action('phpinfo',        SYS . 'phpinfo/phpinfoMain.php');
$menu->set_action('tnktemplate',    SYS . 'tnkTemplate.php');
// $menu->set_action('patexample',     SYS . 'patexample1.php');
$menu->set_action('data_sum',       SYS . 'data_sum/data_sum--as400-upload.php');
$menu->set_action('log_view',       SYS . 'log_view/php_log_view_clear.php');
$menu->set_action('��ž����',       SYS . 'equip_report/equip_report--as400-upload.php');
$menu->set_action('��Ω��������',   SYS . 'assembly_time/assembly_timeAllUpdate.php');
$menu->set_action('����ñ������',   SYS . 'daily/sales_price_update.php');
$menu->set_action('������������',   SYS . 'assembly_completion/assembly_completion_history.php');
$menu->set_action('��������',     SYS . 'calendar/companyCalendar_Main.php');
$menu->set_action('���̸���',       SYS . 'common_authority/common_authority_Main.php');
$menu->set_action('�߸˥��ޥ꡼',   SYS . 'inventory_average/inventory_average_summary.php');
$menu->set_action('������ư��',   SYS . 'equip_auto_log_ctl/equip_auto_log_ctl.php');
$menu->set_action('�����ƥ���',     TEST . 'print/svgSimplatePXDocTest.php');
$menu->set_action('������������',   SYS . 'printFormUpload/printFormUpload.php');
$menu->set_action('���ν񹹿�',     INDUST . 'inspectionPrint/inspectionPrintUpdate.php');
$menu->set_action('������ץ���', EMP . 'timepro/timePro_update_cli_manu.php');
$menu->set_action('���ǡ����Ƽ���', SYS . 'daily/sales_get_ftp.php');
$menu->set_action('����ǡ����Ƽ���', SYS . 'daily/as400get_ftp_re.php');
$menu->set_action('���ʥޥ���������', SYS . 'daily/product_master_get_ftp.php');
$menu->set_action('��������ǡ����Ƽ���', SYS . 'daily/daily_cli.php');
$menu->set_action('��ʿ��ñ������', SYS . 'daily/periodic_average_cost_get_ftp.php');

//////////// �֥饦�����Υ���å����к���
$uniq = $menu->set_useNotCache('sysMenu');

/////////// HTML Header ����Ϥ��ƥ���å��������
$menu->out_html_header();
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html;charset=EUC-JP">
<meta http-equiv="Content-Style-Type" content="text/css">
<meta http-equiv="Content-Script-Type" content="text/javascript">
<title><?php echo $menu->out_title() ?></title>
<?php echo $menu->out_site_java() ?>
<?php echo $menu->out_css() ?>

<!-- �������륷���ȤΥե��������򥳥��� HTML���� �����Ȥ�����Ҥ˽���ʤ�������� 
<link rel='stylesheet' href='<?php echo MENU_FORM . '?' . $uniq ?>' type='text/css' media='screen'>
-->

<style type='text/css'>
<!--
.top-font {
    font-size: 12pt;
    font-weight: bold;
    font-family: serif;
}
-->
</style>
<script type='text/javascript'>
<!--
function upload_click(msg) {
    return confirm(msg + "\n\n�������Ǥ�����");
}
function set_focus()
{
    // document.body.focus();   // F2/F12������ͭ���������б�
    // document.mhForm.backwardStack.focus();  // �嵭��IE�ΤߤΤ���NN�б�
}
// -->
</script>
</head>
<body bgcolor='#ffffff' text='#000000' style='overflow:hidden' onLoad='set_focus()'>
<center>
<?php echo $menu->out_title_border() ?>
    
    <table width='80%' border='0' cellspacing='0' cellpadding='0'> <!-- width�Ǵֳ֤�Ĵ�� height��bottom�ΰ���Ĵ�� -->
    <tr>
    <td valign='top'>
        <table width='100%' border='0' cellspacing='0' cellpadding='0'>
            <tr>
                <td><p><img src='<?php echo IMG ?>t_nitto_logo3.gif' width='348' height='83'></p></td>
            </tr>
            <tr>
                <td align='center' class='top-font'>
                    <?php echo $menu->out_caption(), "\n" ?>
                </td>
            </tr>
        </table>
        
        <table width='100%' border='0' cellspacing='0' cellpadding='0'>
            <tr><td align='center'>
            <img src='<?php echo IMG ?>tnk-turbine.gif' width='68' height='72'>
            </td></tr>
        </table>
        
        <table width='100%' border='0' cellspacing='0' cellpadding='0'>
        <tr>
            <!-- /////////////// left view ////////////// -->
        <td align='center' valign='top'>
            <table width='100%' border='0' cellspacing='0' cellpadding='3'>
                <tr>
                    <td align='center'>
                        <form method='post' action='<?php echo $menu->out_action('�������') ?>'><?php echo "\n"; // ��ե�����menu_item_edp_nippou.gif ?>
                            <input type='image' alt='�������' border='0' src='<?php echo menu_bar('menu_tmp/menu_item_edp_nippou.png','  ��  ��  ��  ��'), '?',$uniq ?>'>
                        </form>
                    </td>
                </tr>
                
                <tr>
                    <td align='center'>
                        <form method='post' action='<?php echo $menu->out_action('�����') ?>'><?php echo "\n"; // ��ե�����menu_item_edp_getuji.gif ?>
                            <input type='image' alt='�����' border='0' src='<?php echo menu_bar('menu_tmp/menu_item_edp_getuji.png', '  ��  ��  ��  ��'), '?',$uniq ?>'>
                        </form>
                    </td>
                </tr>
                
                <tr>
                    <td align='center'>
                        <form method='post' action='<?php echo $menu->out_action('as400file') ?>'><?php echo "\n"; // ��ե�����menu_item.gif ?>
                            <input type='image' alt='AS/400 �ե�����Ȳ�' border='0' src='<?php echo menu_bar('menu_tmp/menu_item_as400_query.png', '  AS/400 �ե�����'), '?',$uniq ?>'>
                        </form>
                    </td>
                </tr>
                
                <tr>
                    <td align='center'>
                        <form method='post' action='<?php echo $menu->out_action('data_sum') ?>'>
                            <input type='image' alt='�ǡ��������������(�����˰�����)�塢ü���� CALL GBY049C ��¹Ԥ���' border='0'
                            src='<?php echo menu_bar('menu_tmp/menu_item_data_sum_nippo.png', '�ǡ��������������'), '?',$uniq ?>'
                            onClick="return upload_click('����DATA-SUM����������ϼ�ư������Ƥ��ޤ���\n\n����Ǥ�¹Ԥ��ޤ���')">
                        </form>
                    </td>
                </tr>
                
                <tr>
                    <td align='center'>
                        <form method='post' action='<?php echo $menu->out_action('��������') ?>'>
                            <input type='image' alt='��Ҥε����ʤɤΥ����������ƥʥ�' border='0'
                            src='<?php echo menu_bar('menu_tmp/menu_item_calendar.png', '���������Υ���', 14, 0), '?',$uniq ?>'
                            onClick="//return upload_click('��Ҥε����ʤɤΥ����������ƥʥ󥹤�Ԥ��ޤ���')">
                        </form>
                    </td>
                </tr>
                
                <tr>
                    <td align='center'>
                        <form method='post' action='<?php echo $menu->out_action('��Ω��������') ?>'>
                            <input type='image' alt='��Ω����Ͽ������AS/400��DB�����С��ع������ޤ���' border='0'
                            src='<?php echo menu_bar('menu_tmp/menu_item_assemblyTimeAllUPDATE.png', '��Ω ��Ͽ���� ����'), '?',$uniq ?>'
                            onClick="return upload_click('��Ω����Ͽ�����ڤӹ����ޥ�������AS/400��DB�����С��ع������ޤ���')">
                        </form>
                    </td>
                </tr>
                
                <tr>
                    <td align='center'>
                        <form method='post' action='<?php echo $menu->out_action('����ñ������') ?>'>
                            <input type='image' alt='���칩���Ѥλ���ñ����AS/400��DB�����С��ع������ޤ���' border='0'
                            src='<?php echo menu_bar('menu_tmp/menu_item_salesPriceUPDATE.png', 'NK����ñ�� �������'), '?',$uniq ?>'
                            onClick="return upload_click('���칩���Ѥλ���ñ����AS/400��DB�����С��ع������ޤ���')">
                        </form>
                    </td>
                </tr>
                
                <tr>
                    <td align='center'>
                        <form method='post' action='<?php echo $menu->out_action('������������') ?>'>
                            <input type='image' alt='��Ω���������AS/400��DB�����С��ع������ޤ���' border='0'
                            src='<?php echo menu_bar('menu_tmp/menu_item_assyComplete.png', ' ��Ω���� �������'), '?',$uniq ?>'
                            onClick="return upload_click('������Ω�����������������ϼ�ư������Ƥ��ޤ���\n\n����Ǥ�¹Ԥ��ޤ���\n\n��Ω���������AS/400��DB�����С��ع������ޤ���')">
                        </form>
                    </td>
                </tr>
                
                <tr>
                    <td align='center'>
                        <form method='post' action='<?php echo $menu->out_action('���̸���') ?>'>
                            <input type='image' alt='���̸��¥ޥ��������Խ���Ԥ��ޤ���' border='0'
                            src='<?php echo menu_bar('menu_tmp/menu_item_common_authority.png', ' ���̸��¥ޥ����Խ�', 14, 0), '?',$uniq ?>'
                            onClick="//return upload_click('���̸��¥ޥ������Υ��ƥʥ󥹤�Ԥ��ޤ���')">
                        </form>
                    </td>
                </tr>
                
                <tr>
                    <td align='center'>
                        <form method='post' action='<?php echo $menu->out_action('�߸˥��ޥ꡼') ?>'>
                            <input type='image' alt='����ʬ�λ��߸˥��ޥ꡼��AS/400��DB�����С��ع������ޤ���' border='0'
                            src='<?php echo menu_bar('menu_tmp/menu_item_inventoryAverageSummary.png', '�߸˥��ޥ꡼ �������'), '?',$uniq ?>'
                            onClick="return upload_click('����ʬ�λ��߸˥��ޥ꡼��AS/400��DB�����С��ع������ޤ���')">
                        </form>
                    </td>
                </tr>
                
                <!--
                <tr>
                    <td align='center'>
                        <form method='post' action='<?php echo $menu->out_self() ?>'>
                            <input type='image' border='0'
                                alt='���Υ����ƥ�'
                                src='<?php echo IMG ?>menu_item.gif'
                            >
                        </form>
                    </td>
                </tr>
                -->
                
            </table>
        </td>
            <!-- /////////////// center view ////////////// -->
        <td align='center' valign='top'>
            <table width='100%' border='0' cellspacing='0' cellpadding='3'>
                <tr>
                    <td align='center'>
                        <form method='post' action='<?php echo $menu->out_action('color') ?>'><?php echo "\n"; // ��ե�����menu_item_system_color.gif ?>
                            <input type='image' alt='���顼�����å�' border='0' src='<?php echo menu_bar('menu_tmp/menu_item_system_color.png', '  ���顼�����å�'), '?',$uniq ?>'>
                        </form>
                    </td>
                </tr>
                
                <tr>
                    <td align='center'>
                        <form method='post' action='<?php echo $menu->out_action('systemDB') ?>'><?php echo "\n"; // ��ե�����menu_item_system_db.gif ?>
                            <input type='image' alt='�ģ½���' border='0' src='<?php echo menu_bar('menu_tmp/menu_item_system_db.png', '  �ǡ����١�������'), '?',$uniq ?>'>
                        </form>
                    </td>
                </tr>
                
                <tr>
                    <td align='center'>
                        <form method='post' action='<?php echo $menu->out_action('top_chk') ?>'><?php echo "\n"; ?>
                            <input type='image' alt='Free Memory' border='0' src='<?php echo menu_bar('menu_tmp/menu_item_top.png', ' System status view'), '?',$uniq ?>'>
                        </form>
                    </td>
                </tr>
                
                <tr>
                    <td align='center'>
                        <form method='post' action='<?php echo $menu->out_action('free_chk') ?>'><?php echo "\n"; // ��ե�����menu_item_free.gif ?>
                            <input type='image' alt='Free Memory' border='0' src='<?php echo menu_bar('menu_tmp/menu_item_free.png', '    Free Memory'), '?',$uniq ?>'>
                        </form>
                    </td>
                </tr>
                
                <tr>
                    <td align='center'>
                        <form method='post' action='<?php echo $menu->out_action('phpinfo') ?>'><?php echo "\n"; // ��ե�����menu_item_phpinfo.gif ?>
                            <input type='image' alt='PHP Information' border='0' src='<?php echo menu_bar('menu_tmp/menu_item_phpinfo.png', '   PHP Information'), '?',$uniq ?>'>
                        </form>
                    </td>
                </tr>
                
                <tr>
                    <td align='center'>
                        <form method='post' action='<?php echo $menu->out_action('log_view') ?>'>
                            <input type='image' alt='php��apache��error�ڤӥ������å�' border='0'
                            src='<?php echo menu_bar('menu_tmp/menu_item_log_view.png', 'php apache log check'), '?',$uniq ?>'>
                        </form>
                    </td>
                </tr>
                
                <tr>
                    <td align='center'>
                        <form method='post' action='<?php echo $menu->out_action('template') ?>'>
                            <input type='image' alt='��ȯ�ѥƥ�ץ졼�Ȥ�ɽ��' border='0' src='<?php echo menu_bar('menu_tmp/menu_item_template.png', ' ��ȯ�ѥƥ�ץ졼��'), '?',$uniq ?>'>
                        </form>
                    </td>
                </tr>
                
                <tr>
                    <td align='center'>
                        <form method='post' action='<?php echo $menu->out_action('tnktemplate') ?>'>
                            <input type='image' alt='��ȯ�ѥƥ�ץ졼�� ���饹��ɽ��' border='0'
                            src='<?php echo menu_bar('menu_tmp/menu_item_patTemplate.png', '�ƥ�ץ졼�� ���饹'), '?',$uniq ?>'
                            onClick="return upload_click('��ȯ�ѥƥ�ץ졼�ȥ��饹��ɽ�����ޤ���')">
                        </form>
                    </td>
                </tr>
                
                <tr>
                    <td align='center'>
                        <form method='post' action='<?php echo $menu->out_action('��ž����') ?>'>
                            <input type='image' alt='������ž����ǡ������������(�����˲���Ǥ������ǽ)�塢ü���� CALL GOKK201C ��¹Ԥ���' border='0'
                            src='<?php echo menu_bar('menu_tmp/menu_item_equip_report.png', '������ž�������'), '?',$uniq ?>'
                            onClick="return upload_click('���˵�����ž�������������ϼ�ư������Ƥ��ޤ���\n\n����Ǥ�¹Ԥ��ޤ���')">
                        </form>
                    </td>
                </tr>
                
                <tr>
                    <td align='center'>
                        <form method='post' action='<?php echo $menu->out_action('������ư��') ?>'>
                            <input type='image' alt='������Ư���������ƥ�μ�ư��������������ޤ���' border='0'
                            src='<?php echo menu_bar('menu_tmp/menu_item_equipAutoLogCtl.png', '��������ư������', 14, 0), '?',$uniq ?>'
                            onClick="//return upload_click('������Ư���������ƥ�μ�ư��������������ޤ���')">
                        </form>
                    </td>
                </tr>
                
                <!--
                <tr>
                    <td align='center'>
                        <form method='post' action='<?php echo $menu->out_self() ?>'>
                            <input type='image' border='0'
                                alt='���Υ����ƥ�'
                                src='<?php echo IMG ?>menu_item.gif'
                            >
                        </form>
                    </td>
                </tr>
                -->
                
            </table>
        </td>
            <!-- /////////////// right view ////////////// -->
        <td align='center' valign='top'>
            <table width='100%' border='0' cellspacing='0' cellpadding='3'>
                <tr>
                    <td align='center'>
                        <form method='post' action='<?php echo $menu->out_action('�����ƥ���') ?>'>
                            <input type='image' border='0'
                                alt='simplate(�ƥ�ץ졼�ȥ��󥸥�) �� PXDoc(������Scalable Vector Graphics���󥸥�) �Υƥ��ȥץ����'
                                src='<?php echo menu_bar('menu_tmp/menu_item_test_simplate_pxdoc.png', '�����ץ����ƥ���', 14, 0), "?{$uniq}" ?>'
                            >
                        </form>
                    </td>
                </tr>
                
                <tr>
                    <td align='center'>
                        <form method='post' action='<?php echo $menu->out_action('������������') ?>'>
                            <input type='image' border='0'
                                alt='simplate(�ƥ�ץ졼�ȥ��󥸥�) �� PXDoc(������Scalable Vector Graphics���󥸥�) �Υƥ�ץ졼���ѤΣӣ֣Ǥ򥢥åץ��ɤ��ƺ������ޤ���'
                                src='<?php echo menu_bar('menu_tmp/menu_item_printFormUpload.png', '�����ե����ॳ��С���', 13, 0), "?{$uniq}" ?>'
                            >
                        </form>
                    </td>
                </tr>
                
                <tr>
                    <td align='center'>
                        <form method='post' action='<?php echo $menu->out_action('���ν񹹿�') ?>'>
                            <input type='image' border='0'
                                alt='�����ץ�γ�ȯ�ե�����(���ν񡦾�ǧ�ޡ��桼�����ֹ���)�ڤӵ��襳���ɤμ�ư������Ԥ��ޤ���'
                                src='<?php echo menu_bar('menu_tmp/menu_item_inspectionPrintUpload.png', '�����ץ����ν񹹿�', 13, 0), "?{$uniq}" ?>'
                            >
                        </form>
                    </td>
                </tr>
                
                <tr>
                    <td align='center'>
                        <form method='post' action='<?php echo $menu->out_action('������ץ���') ?>'>
                            <input type='image' alt='������ץ�ǡ����������¹����˥�����ץ�Υǡ���(DAIRY_MANU.txt)��ü���Ǻ������ե����������Ƥ���' border='0'
                            src='<?php echo menu_bar('menu_tmp/menu_item_timepro.png', '������ץ�ǡ�������'), '?',$uniq ?>'
                            onClick="return upload_click('������ץ�Υǡ����򹹿����ޤ���\n\n�ǡ����Ϥ��Ǥ˺������ޤ�������')">
                        </form>
                    </td>
                </tr>
                
                <tr>
                    <td align='center'>
                        <form method='post' action='<?php echo $menu->out_action('���ǡ����Ƽ���') ?>'>
                            <input type='image' alt='���ǡ����Ƽ������¹�����AS400�����ǡ��������ե��������Ȥ��Ƥ���' border='0'
                            src='<?php echo menu_bar('menu_tmp/salse_get.png', '���ǡ����Ƽ���'), '?',$uniq ?>'
                            onClick="return upload_click('���ǡ�����Ƽ������ޤ���\n\n�ǡ����Ϥ��Ǥ˺������ޤ�������')">
                        </form>
                    </td>
                </tr>
                
                <tr>
                    <td align='center'>
                        <form method='post' action='<?php echo $menu->out_action('��ʿ��ñ������') ?>'>
                            <input type='image' alt='��ʿ��ñ���������¹����˷�ե���������ʿ��ñ��������¹ԡ�3�9��Ϸ軻������ʳ��ϲ���' border='0'
                            src='<?php echo menu_bar('menu_tmp/average_cost_get.png', '��ʿ��ñ������'), '?',$uniq ?>'
                            onClick="return upload_click('��ʿ��ñ����������ޤ���\n\n�ǡ����Ϥ��Ǥ˺������ޤ�������')">
                        </form>
                    </td>
                </tr>
                
                <tr>
                    <td align='center'>
                        <form method='post' action='<?php echo $menu->out_self() ?>'>
                            <input type='image' border='0'
                                alt='���Υ����ƥ�'
                                src='<?php echo menu_bar('menu_tmp/menu_item_empty.png', ''), "?{$uniq}" ?>'
                            >
                        </form>
                    </td>
                </tr>
                
                <tr>
                    <td align='center'>
                        <form method='post' action='<?php echo $menu->out_self() ?>'>
                            <input type='image' border='0'
                                alt='���Υ����ƥ�'
                                src='<?php echo menu_bar('menu_tmp/menu_item_empty.png', ''), "?{$uniq}" ?>'
                            >
                        </form>
                    </td>
                </tr>
                
                <tr>
                    <td align='center'>
                        <form method='post' action='<?php echo $menu->out_action('��������ǡ����Ƽ���') ?>'>
                            <input type='image' alt='AS����������ν������٤줿����8:45�ʹߡ��ˤ˹Ԥ�(AS����֥Хå��ʤɤ���ߤ��Ƥ������ϡ�����ǡ����Ƽ���������¹ԡˡ�AS400�������������������¹Ԥ����Ե���ˤʤä���Ԥ���' border='0'
                            src='<?php echo menu_bar('menu_tmp/daily_cli.png', '��������ǡ����Ƽ���'), '?',$uniq ?>'
                            onClick="return upload_click('��������ǡ�����Ƽ������ޤ���\n\n������������������Ե���ˤʤäƤ��ޤ�����\n\n�������ǡ����Ƽ�����Ԥ��ޤ�������')">
                        </form>
                    </td>
                </tr>
                
                <tr>
                    <td align='center'>
                        <form method='post' action='<?php echo $menu->out_action('����ǡ����Ƽ���') ?>'>
                            <input type='image' alt='AS400����֥Хå�����ߤ����ݡ������˹Ԥ����̾�7:00����ή���ץ����ʰ١�AS400����夹����ή���Ƥ�����������������������Ե�����Ԥ�ɬ�פϤʤ���' border='0'
                            src='<?php echo menu_bar('menu_tmp/as400get_ftp_re.png', '����ǡ����Ƽ���'), '?',$uniq ?>'
                            onClick="return upload_click('����ǡ�����Ƽ������ޤ���\n\nAS400�����줷�Ƥ��ޤ�����')">
                        </form>
                    </td>
                </tr>
                
                <!--
                <tr>
                    <td align='center'>
                        <form method='post' action='<?php echo $menu->out_self() ?>'>
                            <input type='image' border='0'
                                alt='���Υ����ƥ�'
                                src='<?php echo menu_bar('menu_tmp/menu_item_empty.png', ''), "?{$uniq}" ?>'
                            >
                        </form>
                    </td>
                </tr>
                -->
                
            </table>
        </td>
        </tr>
        </table>
    </td>
    </tr>
    </table>
    <div style='position:absolute; left:15%; bottom:0%;'>
        <!-- <img src='<?php echo IMG ?>php4.gif'   width='64'  height='32'> -->
        <img src='<?php echo IMG ?>php5_logo.gif'>
        <img src='<?php echo IMG ?>linux.gif'  width='74'  height='32'>
        <img src='<?php echo IMG ?>redhat.gif' width='96'  height='32'>
        <img src='<?php echo IMG ?>apache.gif' width='259' height='32'>
        <img src='<?php echo IMG ?>pgsql.gif'  width='160' height='32'>
    </div>
</center>
</body>
<?php echo $menu->out_alert_java(false)?>
</html>
<?php
ob_end_flush();                 // ���ϥХåե���gzip���� END
?>
