<?php
//////////////////////////////////////////////////////////////////////////////
// ���� ���� ��˥塼                                                       //
// Copyright (C) 2003-2018 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp      //
// Changed history                                                          //
// 2003/11/17 Created   act_menu.php                                        //
//            ����Ū�˷�������ط��ν�����Ԥ�������Ū�ˤϷ�����˥塼��    //
// 2003/11/29 ���� ���� ��˥塼(������˥�menu_sit.php)�ذܹ�              //
// 2003/12/08 monthly_send(name)��javaScript�Ǻ�����<a href'**'>��������    //
// 2003/12/12 define���줿����ǥǥ��쥯�ȥ�ȥ�˥塼����Ѥ��ƴ�������    //
//            ob_start('ob_gzhandler') ���ɲ�                               //
// 2004/02/13 index1.php��index.php���ѹ�(index1��authenticate���ѹ��Τ���) //
// 2004/05/12 �����ȥ�˥塼ɽ������ɽ�� �ܥ����ɲ� menu_OnOff($script)�ɲ� //
// 2004/06/10 view_user($_SESSION['User_ID']) ���˥塼�إå����β����ɲ�  //
// 2004/12/25 style='overflow:hidden;' (-xyξ��)���ɲ�                      //
// 2005/01/18 MenuHeader class ����Ѥ��ƶ��̥�˥塼���ڤ�ǧ���������ѹ�   //
// 2005/05/09 ȯ��ײ�ǡ����μ�ư������ȼ����˥塼����ν������˥�å�����//
// 2005/05/31 �嵭��Ʊ���ͤˣ����������˼�ư������Ƥ��뤿���å������ɲ�//
// 2005/06/07 ���������ǰ���������ǧ����������Ф��Ƥʤ��Τ����Ƥ˽Ф�    //
// 2005/08/06 ����ٵ��ʤ�javaScript:monthly_send()��ȴ���Ƥ���Τ��ɲ�     //
// 2007/01/09 date_offset()��DB�������ѹ��ˤʤä�������֤�������64��26��   //
// 2007/09/07 php�Υ��硼�ȥ��åȥ�����ɸ�ॿ��(�侩��)���ѹ�               //
// 2007/10/13 ��������¤�����δ��񥵥ޥ꡼�Ȳ���ɲ� E_ALL | E_STRICT ��  //
// 2007/10/22 setArrayYMD(),getArrayYMD()�������ǯ����������������®��   //
//            date('Y/m/d', filemtime(YMD_FILE))�򺸲����ɲá��嵭�γ�ǧ��  //
// 2007/10/28 ��������¤�����;ʬ��<form>�����äƤ����ΤǺ��              //
// 2007/11/06 ������������պ�����˥塼���ɲá�table��width������ѹ�      //
// 2009/02/24 2008/01/17���̤�Ƥ����ٶ���Ū�����դ�ɽ������           ��ë //
// 2009/12/25 23�����Ķ����ˤʤä��ٶ���Ū��23����ɽ������褦���ѹ�        //
//            �ѹ��������Ķ������ѹ�������ϻϤ�����Ҷ�ͭ�Υ���������//
//            ���ƥʥ󥹤�arrayYMDmenu.txt����ٺ�����Ƥ��Υ�˥塼��    //
//            �����ʤ����кƺ�������롣                               ��ë //
// 2010/05/19 �δ���ξȲ��ɲäˤ�ꡢ��˥塼����                     ��ë //
// 2010/11/11 �ƥ��������̤�ê��������Ӥ��ɲ�                       ��ë //
// 2013/01/29 ��˥塼�̤����ä����٥����ɥС�����ɽ������           ��ë //
// 2014/12/02 2014/11/06���̤�Ƥ����ٶ���Ū�����դ�ɽ������           ��ë //
// 2015/02/16 ɽ�����դ�60���˱�Ĺ����                                 ��ë //
// 2015/05/21 �����������б�                                           ��ë //
// 2015/11/30 ȴ�������ä���Τ�������뤿�����դ����ѹ�             ��ë //
// 2017/10/24 Ϣ�������ɽ���ɲ�                                     ��ë //
// 2017/11/10 ����������ê������ɲ�                                   ��ë //
// 2018/05/08 �ġ���ê���Ȳ�����Τ˸���                               ��ë //
// 2018/10/16 ���������ѹ�                                             ��ë //
//////////////////////////////////////////////////////////////////////////////
// ini_set('error_reporting', E_ALL);          // E_ALL='2047' debug ��
// ini_set('display_errors', '1');             // Error ɽ�� ON debug �� 
// ini_set('zend.ze1_compatibility_mode', '1');    // zend 1.X ����ѥ� php4�θߴ��⡼��
// ini_set('implicit_flush', 'off');           // echo print �� flush �����ʤ�(�٤��ʤ뤿��) CLI��
// ini_set('max_execution_time', 1200);        // ����¹Ի���=20ʬ WEB CGI��
session_start();                            // ini_set()�μ��˻��ꤹ�뤳�� Script �Ǿ��
ob_start('ob_gzhandler');                   // ���ϥХåե���gzip����

require_once ('../function.php');           // define.php �� pgsql.php �� require_once ���Ƥ���
require_once ('../tnk_func.php');           // TNK �˰�¸������ʬ�δؿ��� require_once ���Ƥ���
require_once ('../MenuHeader.php');         // TNK ������ menu class
require_once ('../ControllerHTTP_Class.php');// TNK ������ MVC Controller Class
//////////// ���å����Υ��󥹥��󥹤���Ͽ
// $session = new Session();
access_log();                               // Script Name �ϼ�ư����

///// TNK ���ѥ�˥塼���饹�Υ��󥹥��󥹤����
$menu = new MenuHeader(0);                  // ǧ�ڥ����å�0=���̰ʾ� �����=TOP_MENU �����ȥ�̤����

////////////// ����������
$menu->set_site(20, 999);                   // site_index=20(������˥塼) site_id=999(�����Ȥ򳫤�)
////////////// �꥿���󥢥ɥ쥹����
// $menu->set_RetUrl(MENU);                 // �̾�ϻ��ꤹ��ɬ�פϤʤ�(�ȥåץ�˥塼)
//////////// �����ȥ�̾(�������Υ����ȥ�̾�ȥե�����Υ����ȥ�̾)
$menu->set_title('���� ���� ��˥塼');
//////////// ɽ�������
$menu->set_caption('���� ���� ���� ��˥塼 &nbsp;&nbsp;&nbsp;������');
//////////// �ƽ����action̾�ȥ��ɥ쥹����
/**************** ������� ****************/
$menu->set_action('��ݶ�ι���',       ACT . 'act_payable_get_ftp.php');
$menu->set_action('��ݶ�Υ����å�',   ACT . 'act_payable_view.php');
$menu->set_action('�ٵ�ɼ�ι���',       ACT . 'act_miprov_get_ftp.php');
$menu->set_action('�ٵ�ɽ�Υ����å�',   ACT . 'act_miprov_view.php');
$menu->set_action('ȯ��ײ�ι���',     ACT . 'order_plan_get_ftp.php');
$menu->set_action('ȯ��ײ�Υ����å�', ACT . 'order_plan_view.php');
$menu->set_action('��������ι���',     ACT . 'aden_master_update.php');
$menu->set_action('��������ξȲ�',     ACT . 'aden_master_view.php');
/**************** ����� ****************/
$menu->set_action('ê���ǡ����ι���',           ACT . 'inventory/inventory_month_update.php');
$menu->set_action('ê���ǡ����Υ����å�',       ACT . 'inventory/inventory_month_view.php');
$menu->set_action('����ٵ��ʤι���',           ACT . 'provide_month_update.php');
$menu->set_action('����ٵ��ʤΥ����å�',       ACT . 'provide_month_view.php');
$menu->set_action('ȯ����ޥ������ι���',       ACT . 'vendor_master_update.php');
$menu->set_action('ȯ����ޥ������Υ����å�',   ACT . 'vendor_master_view.php');
$menu->set_action('ô���ԥޥ������ι���',       ACT . 'vendor_person_master_update.php');
$menu->set_action('ô���ԥޥ������Υ����å�',   ACT . 'vendor_person_master_view.php');
$menu->set_action('������ۤξȲ�',             ACT . 'act_purchase_view.php');
$menu->set_action('�����׾����',               ACT . 'act_purchase_update.php');
$menu->set_action('���ץ�ê�����',             ACT . 'inventory/inventory_month_c_view.php');
$menu->set_action('��˥�ê�����',             ACT . 'inventory/inventory_month_l_view.php');
$menu->set_action('������ê�����',             ACT . 'inventory/inventory_monthly_ctoku_view.php');
// $menu->set_action('������ê������',             ACT . 'inventory_month_ctoku_zen_view.php');
$menu->set_action('�Х����ê�����',           ACT . 'inventory/inventory_month_bimor_view.php');
$menu->set_action('�ġ���ê�����',           ACT . 'inventory/inventory_month_tool_view.php');
$menu->set_action('������ê����۷׾����',     ACT . 'inventory_monthly_header_update.php');
/**************** ����¾ �Ȳ��˥塼 ****************/
$menu->set_action('��������¤����',             ACT . 'act_summary/act_summary_Main.php');
$menu->set_action('�������������',             ACT . 'graphCreate/graphCreate_Form.php');
$menu->set_action('�������δ���',               ACT . 'sga_summary/act_summary_Main.php');
$menu->set_action('��ʿ��ê�����',             ACT . 'inventory/inventory_month_view_average.php');
$menu->set_action('���ץ���ʿ��ê�����',       ACT . 'inventory/inventory_month_c_view_average.php');
$menu->set_action('��˥���ʿ��ê�����',       ACT . 'inventory/inventory_month_l_view_average.php');
$menu->set_action('��������ʿ��ê�����',       ACT . 'inventory/inventory_monthly_ctoku_view_average.php');
$menu->set_action('��������ʿ��ê���������',   ACT . 'inventory/inventory_monthly_ctoku_view_average_allo.php');
$menu->set_action('�Х������ʿ��ê�����',     ACT . 'inventory/inventory_month_bimor_view_average.php');
$menu->set_action('�ġ�����ʿ��ê�����',     ACT . 'inventory/inventory_month_tool_view_average.php');
$menu->set_action('��ʿ��ê��������',         ACT . 'inventory/inventory_month_compare.php');
$menu->set_action('���ץ���ʿ��ê��������',         ACT . 'inventory/inventory_month_c_compare.php');
$menu->set_action('��˥���ʿ��ê��������',         ACT . 'inventory/inventory_month_l_compare.php');
$menu->set_action('��������ʿ��ê��������',         ACT . 'inventory/inventory_month_ctoku_compare.php');
$menu->set_action('�Х������ʿ��ê��������',         ACT . 'inventory/inventory_month_bimor_compare.php');
$menu->set_action('�ġ�����ʿ��ê��������',         ACT . 'inventory/inventory_month_tool_compare.php');
$menu->set_action('Ϣ�������ɽ',             ACT . 'link_trans/link_trans_menu.php');

//////////// �֥饦�����Υ���å����к���
$uniq = $menu->set_useNotCache('actMenu');  

//////////// �о�ǯ�����Υ��å����ǡ�������
if (isset($_SESSION['act_ymd'])) {
    $act_ymd = $_SESSION['act_ymd']; 
} else {
    $act_ymd = date('Ymd');        // ���å����ǡ������ʤ����ν����(����)
}
//////////// �о�ǯ��Υ��å����ǡ�������
if (isset($_SESSION['act_ym'])) {
    $act_ym = $_SESSION['act_ym']; 
} else {
    $act_ym = date('Ym');        // ���å����ǡ������ʤ����ν����(����)
}
//////////// �о�ǯ��Υ��å����ǡ�������
if (isset($_SESSION['actv_ym'])) {
    $actv_ym = $_SESSION['actv_ym']; 
} else {
    $actv_ym = date('Ym');        // ���å����ǡ������ʤ����ν����(����)
}
/////////// ��Ư����ǯ��������������
define('preDays', 60);  // ��Ư����25��ʬ�����Τܤ�
define('YMD_FILE', 'arrayYMDmenu.txt');
if ( ($ymd=getArrayYMD(YMD_FILE)) === false) {
    $ymd[0] = 20210211;
    for ($i=1; $i<preDays; $i++) {
        $ymd_chk = date_offset($i);     // �Ķ�����$i��ʬ����
        if ($ymd[$i-1] == $ymd_chk) {
            continue;                   // �����Ʊ���ʤ� ������������
        } else {
            $ymd[$i] = date_offset($i);
        }
    }
    setArrayYMD(YMD_FILE, $ymd);
}
////////// ��Ư����ǯ�����������ե��������¸
function setArrayYMD($file, $data)
{
    $data = serialize($data);
    $fp = fopen($file, 'w');
    fwrite($fp, $data);
    fclose($fp);
}
////////// ��Ư����ǯ�����������ե����뤫����� �ե�����ι�����������å�
function getArrayYMD($file)
{
    if (!file_exists($file)) return false;
    if ( date('Ymd') != date('Ymd', filemtime($file)) ) return false;
    $fp = fopen($file, 'r');
    $data = fgets($fp);
    fclose($fp);
    return unserialize($data);
}
//$ymd[0]=20210211;
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
<?php echo $menu->out_site_java()?>
<?php echo $menu->out_css()?>

<script type='text/javascript'>
<!--
function monthly_send(script_name)
{
    document.monthly_form.action = 'act_branch.php?act_name=' + script_name;
    document.monthly_form.submit();
}
function ave_monthly_send(script_name)
{
    document.average_form.action = 'act_branch.php?act_name=' + script_name;
    document.average_form.submit();
}
// -->
</script>

<!-- �������륷���ȤΥե��������򥳥��� HTML���� �����Ȥ�����Ҥ˽���ʤ�������� -->
<link rel='stylesheet' href='<?php echo "act_menu.css?{$uniq}" ?>' type='text/css' media='screen'>

<style type='text/css'>
<!--
select {
    background-color:   teal;
    color:              white;
}
a:hover {
    background-color:   gold;
    color:              black;
}
a {
    font-size:          0.9em;
    font-weight:        bold;
    color:              black;
}
-->
</style>
</head>
<body onLoad='document.mhForm.backwardStack.focus()'>
    <center>
<?php echo $menu->out_title_border()?>
        <!--
        <table width='100%'>
            <tr>
                <td align='center'><img src='<?php echo IMG ?>t_nitto_logo2.gif' width=348 height=83></td>
            </tr>
        </table>
        -->
        <BR>
        <form name='daily_form' action='act_branch.php' method='post'>
        <table width='516' bgcolor='#d6d3ce'  border='1' cellspacing='0' cellpadding='5'>
            <tr><td> <!-- ���ߡ�(�ǥ�������) -->
            <table width='100%' class='winbox_field' bgcolor='#d6d3ce' border='1' cellspacing='0' cellpadding='3'>
                <tr>
                    <td class='winbox' align='center' colspan='5' style='background-color:#ffffc6;'>
                    <div class='caption_font'>
                        <?php echo $menu->out_caption(), "\n"?>
                        <select name='act_ymd' class='pt11b'>
                            <?php
                            for ($i=1; $i<preDays; $i++) {
                                if ($act_ymd == $ymd[$i]) {
                                    printf("<option value='%d' selected>%sǯ%s��%s��</option>\n", $ymd[$i], substr($ymd[$i], 0, 4), substr($ymd[$i], 4, 2), substr($ymd[$i], 6, 2));
                                } else {
                                    printf("<option value='%d'>%sǯ%s��%s��</option>\n", $ymd[$i], substr($ymd[$i], 0, 4), substr($ymd[$i], 4, 2), substr($ymd[$i], 6, 2));
                                }
                            }
                            ?>
                        </select>
                    </div>
                    </td>
                </tr>
                <tr>
                    <td width='50%' class='winbox' align='center' bgcolor='#ceffce'> <!-- #ffffc6 �������� -->
                        <input class='pt10b' type='submit' name='act_name' value='��ݶ�ι���'
                            onClick="return confirm('��ݶ�ι���������¹Ԥ��ޤ���\n\n���ν���������١����ν����Ǥ���\n\n����/������������Ͻ�λ���Ƥ��ޤ�����')"
                        >
                    </td>
                    <td width='50%' class='winbox' align='center' bgcolor='#ceffce'>
                        <input class='pt10b' type='submit' name='act_name' value='�ٵ�ɼ�ι���'
                            onClick="return confirm('�ٵ�ɼ�ι���������¹Ԥ��ޤ���\n\n���ν���������١����ν����Ǥ���\n\n����/������������Ͻ�λ���Ƥ��ޤ�����')"
                        >
                    </td>
                </tr>
                <tr>
                    <td class='winbox' align='center' bgcolor='#d6d3ce'>
                        <input class='pt10b' type='submit' name='act_name' value='��ݶ�Υ����å�'>
                        <!-- <a href='act_branch.php?act_name=act_payable_view' target='application' style='text-decoration:none;'>��ݶ�Υ����å��ꥹ��</a> -->
                    </td> <!-- ;�� -->
                    <td class='winbox' align='center' bgcolor='#d6d3ce'>
                        <input class='pt10b' type='submit' name='act_name' value='�ٵ�ɼ�Υ����å�'>
                        <!-- <a href='act_branch.php?act_name=act_miprov_view' target='application' style='text-decoration:none;'>�ٵ�ɼ�Υ����å��ꥹ��</a> -->
                    </td> <!-- ;�� -->
                </tr>
                <tr>
                    <td class='winbox' align='center' bgcolor='#ceffce'> <!-- #ffffc6 �������� -->
                        <!-- <input class='pt10b' type='submit' name='act_name' value='ȯ��ײ�ι���'> -->
                        <a href='act_branch.php?act_name=order_plan_update' target='application'
                            style='text-decoration:none;'
                            onClick="return confirm('ȯ��ײ�ǡ����ι���������¹Ԥ��ޤ���\n\n���ν����ϸ��߼�ư������Ƥ��ޤ���\n\n����Ǥ�¹Ԥ��ޤ�����')"
                        >
                            ȯ��ײ�ι���
                        </a>
                    </td>
                    <td class='winbox' align='center' bgcolor='#ceffce'>
                        <!-- <input class='pt10b' type='submit' name='act_name' value='��������ι���'> -->
                        <a href='act_branch.php?act_name=aden_master_update' target='application'
                            style='text-decoration:none;'
                            onClick="return confirm('��������ǡ����ι���������¹Ԥ��ޤ���\n\n���ν����ϸ��߼�ư������Ƥ��ޤ���\n\n����Ǥ�¹Ԥ��ޤ�����')"
                        >
                            ��������ι���
                        </a>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' align='center' bgcolor='#d6d3ce'>
                        <!-- <input class='pt10b' type='submit' name='act_name' value='ȯ��ײ�Υ����å�'> -->
                        <a href='act_branch.php?act_name=order_plan_view' target='application' style='text-decoration:none;'>ȯ��ײ�Υ����å��ꥹ��</a>
                    </td> <!-- ;�� -->
                    <td class='winbox' align='center' bgcolor='#d6d3ce'>
                        <!-- <input class='pt10b' type='submit' name='act_name' value='��������Υ����å�'> -->
                        <a href='act_branch.php?act_name=aden_master_view' target='application' style='text-decoration:none;'>��������Υ����å��ꥹ��</a>
                    </td> <!-- ;�� -->
                </tr>
            </table>
            </td></tr>
        </table> <!-- ���ߡ�End -->
        </form>
        
        <br>
        
        <form name='monthly_form' action='act_branch.php' method='post'>
        <table width='516' bgcolor='#d6d3ce' border='1' cellspacing='0' cellpadding='5'>
            <tr><td> <!-- ���ߡ�(�ǥ�������) -->
            <table width='100%' class='winbox_field' bgcolor='#d6d3ce' border='1' cellspacing='0' cellpadding='3'>
                <tr>
                    <td class='winbox' align='center' colspan='2' style='background-color:#ffffc6;'>
                    <div class='caption_font'>
                        ��١��� ���� ��˥塼 &nbsp;&nbsp;&nbsp;������
                        <select name='act_ym' class='pt11b'>
                            <?php
                            $ym = date("Ym");
                            while(1) {
                                if (substr($ym,4,2)!=01) {
                                    $ym--;
                                } else {
                                    $ym = $ym - 100;
                                    $ym = $ym + 11;
                                }
                                if ($act_ym == $ym) {
                                    printf("<option value='%d' selected>%sǯ%s��</option>\n",$ym,substr($ym,0,4),substr($ym,4,2));
                                    $init_flg = 0;
                                } else
                                    printf("<option value='%d'>%sǯ%s��</option>\n",$ym,substr($ym,0,4),substr($ym,4,2));
                                if ($ym <= 200410)
                                    break;
                            }
                            ?>
                        </select>
                    </div>
                    </td>
                </tr>
                <tr>
                    <td width='50%' class='winbox' align='center' bgcolor='#ceffce'>
                        <!-- <a href='act_branch.php?act_name=inventory_month_update' target='application' style='text-decoration:none;'>ê���ǡ����ι���</a> -->
                        <input class='pt10b' type='submit' name='act_name' value='ê���ǡ����ι���'>
                    </td>
                    <td width='50%' class='winbox' align='center' bgcolor='#ceffce'>
                        <!-- <a href='act_branch.php?act_name=provide_month_update' target='application' style='text-decoration:none;'>����ٵ��ʤι���</a> -->
                        <input class='pt10b' type='submit' name='act_name' value='����ٵ��ʤι���'>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' align='center' bgcolor='#d6d3ce'>
                        <a href='javaScript:monthly_send("inventory_month_view")' target='application' style='text-decoration:none;'>ê���ǡ����Υ����å��ꥹ��</a>
                        <!-- <input class='pt10b' type='submit' name='act_name' value='ê���ǡ����Υ����å�'> -->
                    </td>
                    <td class='winbox' align='center' bgcolor='#d6d3ce'>
                        <a href='javaScript:monthly_send("provide_month_view")' target='application' style='text-decoration:none;'>����ٵ��ʤΥ����å��ꥹ��</a>
                        <!-- <input class='pt10b' type='submit' name='act_name' value='����ٵ��ʤΥ����å��ꥹ��'> -->
                    </td>
                </tr>
                <tr>
                    <td class='winbox' align='center' bgcolor='#ceffce'>
                        <!-- <input class='pt10b' type='submit' name='act_name' value='ȯ����ޥ���������'> -->
                        <a href='act_branch.php?act_name=vendor_master_update' target='application'
                            style='text-decoration:none;'
                            onClick="return confirm('ȯ����ޥ������ι���������¹Ԥ��ޤ���\n\n���ν����Ϸ�١����ǹԤäƤ��ޤ���\n\n����/�����������ž���Ͻ�λ���Ƥ��ޤ�����')"
                        >
                            ȯ����ޥ���������
                        </a>
                    </td>
                    <td class='winbox' align='center' bgcolor='#ceffce'>
                        <!-- <input class='pt10b' type='submit' name='act_name' value='�����׾����'> -->
                        <a href='javaScript:monthly_send("act_purchase_update")' target='application'
                            style='text-decoration:none;'
                            onClick="return confirm('�����η׾������¹Ԥ��ޤ���\n\n���ν����Ϸ�١�����ɬ���Ԥ��ޤ���\n\n�¹Ԥ��Ƥ⵹�����Ǥ��礦����')"
                        >
                            ���� �׾� ����
                        </a>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' align='center' bgcolor='#d6d3ce'>
                        <!-- <input class='pt10b' type='submit' name='act_name' value='ȯ����ޥ����������å�'> -->
                        <a href='act_branch.php?act_name=vendor_master_view' target='application' style='text-decoration:none;'>ȯ����ޥ����������å��ꥹ��</a>
                    </td>
                    <td class='winbox' align='center' bgcolor='#d6d3ce'>
                        <a href='javaScript:monthly_send("act_purchase_view")' target='application' style='text-decoration:none;'>������ۤξȲ�</a>
                        <!-- <input class='pt10b' type='submit' name='act_name' value='������ۤξȲ�'> -->
                    </td>
                </tr>
                <tr>
                    <td class='winbox' align='center' bgcolor='#ceffce'>
                        <!-- <input class='pt10b' type='submit' name='act_name' value='ȯ����ޥ���������'> -->
                        <a href='act_branch.php?act_name=vendor_person_master_update' target='application'
                            style='text-decoration:none;'
                            onClick="return confirm('ô���ԥޥ������ι���������¹Ԥ��ޤ���\n\n���ν����Ϸ�١����ǹԤäƤ��ޤ���\n\n����/�����������ž���Ͻ�λ���Ƥ��ޤ�����')"
                        >
                            ô���ԥޥ���������
                        </a>
                    </td>
                    <td class='winbox' align='center' bgcolor='#d6d3ce'>
                        <!-- <input class='pt10b' type='submit' name='act_name' value='ȯ����ޥ����������å�'> -->
                        <a href='act_branch.php?act_name=vendor_person_master_view' target='application' style='text-decoration:none;'>ô���ԥޥ����������å��ꥹ��</a>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' align='center' bgcolor='#d6d3ce'>
                        <a href='javaScript:monthly_send("inventory_month_c_view")' target='application' style='text-decoration:none;'>���ץ�ê�����</a>
                        <!-- <input class='pt10b' type='submit' name='act_name' value='���ץ�ê�����'> -->
                    </td>
                    <td class='winbox' align='center' bgcolor='#d6d3ce'>
                        <a href='javaScript:monthly_send("inventory_month_l_view")' target='application' style='text-decoration:none;'>��˥�ê�����</a>
                        <!-- <input class='pt10b' type='submit' name='act_name' value='��˥�ê�����'> -->
                    </td>
                </tr>
                <tr>
                    <td class='winbox' align='center' bgcolor='#d6d3ce'>
                        <a href='javaScript:monthly_send("inventory_month_ctoku_view")' target='application' style='text-decoration:none;'>���ץ����� ê����� �Ȳ�</a>
                        <!-- <input class='pt10b' type='submit' name='act_name' value='���ץ�����ê����ۤξȲ�'> -->
                    </td>
                    <td class='winbox' align='center' bgcolor='#d6d3ce'>
                        <a href='javaScript:monthly_send("inventory_month_tool_view")' target='application' style='text-decoration:none;'>�ġ��� ê����� �Ȳ�</a>
                        <!-- <input class='pt10b' type='submit' name='act_name' value='�ġ���ê����ۤξȲ�'> -->
                    </td>
                    <!--
                    <td class='winbox' align='center' bgcolor='#d6d3ce'> 
                        <a href='javaScript:monthly_send("inventory_month_bimor_view")' target='application' style='text-decoration:none;'>���Υݥ�� ê����� �Ȳ�</a> -->
                        <!-- <input class='pt10b' type='submit' name='act_name' value='���Υݥ��ê����ۤξȲ�'> -->
                    <!--
                    </td>
                    -->
                </tr>
                <tr>
                    <td class='winbox' colspan='2' align='center' bgcolor='#ceffce'>
                        <!-- <input class='pt10b' type='submit' name='act_name' value='������ ê����� �׾����'> -->
                        <a href='javaScript:monthly_send("inventory_monthly_header_update")'
                            target='application' style='text-decoration:none;'
                            onClick="return confirm('������ ê����ۤη׾������¹Ԥ��ޤ���\n\n���ν�����ê���ǡ����ι����Ǽ�ưŪ�˹Ԥ��ޤ���\n\n����Ǥ�ñ�ȤǼ¹Ԥ��ޤ�����')"
                        >
                            ������ ê����� �׾����
                        </a>
                    </td>
                </tr>
            </table>
            </td></tr>
        </table> <!-- ���ߡ�End -->
        </form>
        
        <br>
        
        <form name='average_form' action='act_branch.php' method='post'>
        <table width='516' bgcolor='#d6d3ce' border='1' cellspacing='0' cellpadding='5'>
            <tr><td> <!-- ���ߡ�(�ǥ�������) -->
            <table width='100%' class='winbox_field' bgcolor='#d6d3ce' border='1' cellspacing='0' cellpadding='3'>
                <tr>
                    <td class='winbox' align='center' colspan='2' style='background-color:#ffffc6;'>
                    <div class='caption_font'>
                        ����¾ �Ȳ� ��˥塼 &nbsp;&nbsp;&nbsp;������
                        <select name='actv_ym' class='pt11b'>
                            <?php
                            $ym = date("Ym");
                            while(1) {
                                if (substr($ym,4,2)!=01) {
                                    $ym--;
                                } else {
                                    $ym = $ym - 100;
                                    $ym = $ym + 11;
                                }
                                if ($actv_ym == $ym) {
                                    printf("<option value='%d' selected>%sǯ%s��</option>\n",$ym,substr($ym,0,4),substr($ym,4,2));
                                    $init_flg = 0;
                                } else
                                    printf("<option value='%d'>%sǯ%s��</option>\n",$ym,substr($ym,0,4),substr($ym,4,2));
                                if ($ym <= 200410)
                                    break;
                            }
                            ?>
                        </select>
                    </div>
                    </td>
                </tr>
                <tr>
                    <td width='50%' class='winbox' align='center' bgcolor='#d6d3ce'>
                        <?php
                        if (getCheckAuthority(35)) {
                        ?>
                        <a href='javascript:function(){}'
                            target='_self' style='text-decoration:none;'
                            onClick='location.replace("<?php echo $menu->out_action('��������¤����')?>"); return false;'
                            onMouseover="status='������ ��¤�����δ���ξȲ��˥塼��ɽ�����ޤ���';return true;"
                            onMouseout="status=''"
                            title='������ ��¤�����δ���ξȲ��˥塼��ɽ�����ޤ���'
                        >
                            ������ ��¤���δ���ξȲ�
                        <?php
                        } else {
                        ?>
                        <a href='javascript:function(){}'
                            target='_self' style='text-decoration:none;'
                            onClick='location.replace("<?php echo $menu->out_action('��������¤����')?>"); return false;'
                            onMouseover="status='������ ��¤����ξȲ��˥塼��ɽ�����ޤ���';return true;"
                            onMouseout="status=''"
                            title='������ ��¤����ξȲ��˥塼��ɽ�����ޤ���'
                        >
                            ������ ��¤����ξȲ�
                        </a>
                        <?php 
                        }
                        ?>
                    </td>
                    <td width='50%' class='winbox' align='center' bgcolor='#d6d3ce'>
                        <a href='javascript:function(){}'
                            target='_self' style='text-decoration:none;'
                            onClick='location.replace("<?php echo $menu->out_action('�������������')?>"); return false;'
                            onMouseover="status='����������ʬ���ѥ���պ�����˥塼��ɽ�����ޤ���';return true;"
                            onMouseout="status=''"
                            title='����������ʬ���ѥ���պ�����˥塼��ɽ�����ޤ���'
                        >
                            ������������պ�����˥塼
                        </a>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' align='center' bgcolor='#d6d3ce'>
                        <a href='javaScript:ave_monthly_send("inventory_month_view_average")' target='application' style='text-decoration:none;'>��ʿ��ê�����</a>
                        <!-- <input class='pt10b' type='submit' name='act_name' value='��ʿ��ê�����'> -->
                    </td>
                    <td class='winbox' align='center' bgcolor='#d6d3ce'>
                        <a href='javaScript:ave_monthly_send("link_trans")' target='application' style='text-decoration:none;'>Ϣ�������ɽ</a>
                        <!-- <input class='pt10b' type='submit' name='act_name' value='Ϣ�������ɽ'> -->
                    </td>
                </tr>
                <tr>
                    <td class='winbox' align='center' bgcolor='#d6d3ce'>
                        <a href='javaScript:ave_monthly_send("inventory_month_c_view_average")' target='application' style='text-decoration:none;'>���ץ���ʿ��ê�����</a>
                        <!-- <input class='pt10b' type='submit' name='act_name' value='���ץ���ʿ��ê�����'> -->
                    </td>
                    <td class='winbox' align='center' bgcolor='#d6d3ce'>
                        <a href='javaScript:ave_monthly_send("inventory_month_l_view_average")' target='application' style='text-decoration:none;'>��˥���ʿ��ê�����</a>
                        <!-- <input class='pt10b' type='submit' name='act_name' value='��˥���ʿ��ê�����'> -->
                    </td>
                </tr>
                <tr>
                    <td class='winbox' align='center' bgcolor='#d6d3ce'>
                        <a href='javaScript:ave_monthly_send("inventory_month_ctoku_view_average")' target='application' style='text-decoration:none;'>��������ʿ��ê�����</a>
                        <!-- <input class='pt10b' type='submit' name='act_name' value='��������ʿ��ê�����'> -->
                    </td>
                    <td class='winbox' align='center' bgcolor='#d6d3ce'>
                        <a href='javaScript:ave_monthly_send("inventory_month_tool_view_average")' target='application' style='text-decoration:none;'>�ġ�����ʿ��ê�����</a>
                        <!-- <input class='pt10b' type='submit' name='act_name' value='�ġ�����ʿ��ê�����'> -->
                    </td>
                    <!--
                    <td class='winbox' align='center' bgcolor='#d6d3ce'>
                        <a href='javaScript:ave_monthly_send("inventory_month_bimor_view_average")' target='application' style='text-decoration:none;'>���Υݥ����ʿ��ê�����</a> -->
                        <!-- <input class='pt10b' type='submit' name='act_name' value='���Υݥ����ʿ��ê�����'> -->
                    <!--
                    </td>
                    -->
                </tr>
                <tr>
                    <td class='winbox' align='center' bgcolor='#d6d3ce'>
                        <a href='javaScript:ave_monthly_send("inventory_month_ctoku_view_average_allo")' target='application' style='text-decoration:none;'>��������ʿ��ê���������</a>
                        <!-- <input class='pt10b' type='submit' name='act_name' value='��������ʿ��ê���������'> -->
                    </td>
                    <td width='50%' class='winbox' align='center' bgcolor='#d6d3ce'>
                        ��
                    </td>
                </tr>
                <tr>
                    <td class='winbox' align='center' bgcolor='#d6d3ce'>
                        <a href='javaScript:ave_monthly_send("inventory_month_compare")' target='application' style='text-decoration:none;'>��ʿ��ê��������</a>
                        <!-- <input class='pt10b' type='submit' name='act_name' value='��ʿ��ê��������'> -->
                    </td>
                    <td width='50%' class='winbox' align='center' bgcolor='#d6d3ce'>
                        ��
                    </td>
                </tr>
                <tr>
                    <td class='winbox' align='center' bgcolor='#d6d3ce'>
                        <a href='javaScript:ave_monthly_send("inventory_month_c_compare")' target='application' style='text-decoration:none;'>���ץ���ʿ��ê��������</a>
                        <!-- <input class='pt10b' type='submit' name='act_name' value='���ץ���ʿ��ê��������'> -->
                    </td>
                    <td class='winbox' align='center' bgcolor='#d6d3ce'>
                        <a href='javaScript:ave_monthly_send("inventory_month_l_compare")' target='application' style='text-decoration:none;'>��˥���ʿ��ê��������</a>
                        <!-- <input class='pt10b' type='submit' name='act_name' value='��˥���ʿ��ê��������'> -->
                    </td>
                </tr>
                <tr>
                    <td class='winbox' align='center' bgcolor='#d6d3ce'>
                        <a href='javaScript:ave_monthly_send("inventory_month_ctoku_compare")' target='application' style='text-decoration:none;'>��������ʿ��ê��������</a>
                        <!-- <input class='pt10b' type='submit' name='act_name' value='��������ʿ��ê��������'> -->
                    </td>
                    <td class='winbox' align='center' bgcolor='#d6d3ce'>
                        <a href='javaScript:ave_monthly_send("inventory_month_tool_compare")' target='application' style='text-decoration:none;'>�ġ�����ʿ��ê��������</a>
                        <!-- <input class='pt10b' type='submit' name='act_name' value='�ġ�����ʿ��ê��������'> -->
                    </td>
                    <!--
                    <td class='winbox' align='center' bgcolor='#d6d3ce'>
                        <a href='javaScript:ave_monthly_send("inventory_month_bimor_compare")' target='application' style='text-decoration:none;'>���Υݥ����ʿ��ê��������</a> -->
                        <!-- <input class='pt10b' type='submit' name='act_name' value='���Υݥ����ʿ��ê��������'> -->
                    <!--
                    </td>
                    -->
                </tr>
<!--
                <tr>
                    <td width='50%' class='winbox' align='center' bgcolor='#ceffce'>
                        <a href='javascript:function(){}'
                            target='_self' style='text-decoration:none;'
                            onClick='location.replace("<?php echo $menu->out_action('�������δ���')?>"); return false;'
                            onMouseover="status='������ ��¤�����δ���ξȲ��˥塼��ɽ�����ޤ���';return true;"
                            onMouseout="status=''"
                            title='������ ��¤�����δ���ξȲ��˥塼��ɽ�����ޤ���'
                        >
                            ������ �δ���ξȲ�
                        </a>
                    </td>
                    <td width='50%' class='winbox' align='center' bgcolor='#ceffce'>
                        ��
                    </td>
                </tr>
-->
            </table>
            </td></tr>
        </table> <!-- ���ߡ�End -->
        </form>
        <div class='ymd'><?php echo date('Y/m/d', filemtime(YMD_FILE)) ?></div>
    </center>
</body>
<?php echo $menu->out_alert_java()?>
</html>
<?php
ob_end_flush();                 // ���ϥХåե���gzip���� END
?>
