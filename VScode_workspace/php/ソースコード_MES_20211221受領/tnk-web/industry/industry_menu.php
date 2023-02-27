<?php
//////////////////////////////////////////////////////////////////////////////
// ���� �ط� ���� ��˥塼                                                  //
// Copyright(C) 2003-2017 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp       //
// Changed history                                                          //
// 2003/11/29 Created  industry_menu.php                                    //
// 2003/12/12 define���줿����ǥǥ��쥯�ȥ�ȥ�˥塼����Ѥ��ƴ�������    //
//            ob_start('ob_gzhandler') ���ɲ�                               //
// 2004/02/13 index1.php��index.php���ѹ�(index1��authenticate���ѹ��Τ���) //
// 2004/04/07 ASSY�ֹ�ˤ���������ξȲ���ɲ�                            //
// 2004/05/12 �����ȥ�˥塼ɽ������ɽ�� �ܥ����ɲ� menu_OnOff($script)�ɲ� //
// 2004/06/10 view_user($_SESSION['User_ID']) ���˥塼�إå����β����ɲ�  //
// 2004/09/21 MenuHeader Class ��Ƴ��                                       //
// 2004/10/19 �����Υǡ���Ʊ����ä��Ƹ�������ꥹ�Ȥ��ɲ�                  //
// 2004/11/27 ȯ�����Υ��ƥʥ󥹤��ɲ�                                  //
// 2004/12/21 method='post' �� 'get' ���ѹ�                                 //
// 2004/12/25 style='overflow-y:hidden;' ���ɲ�                             //
// 2005/01/13 PDF��ɽ����application��_blank���ѹ� JavaScript�ζ��̥����к� //
// 2005/01/14 F2/F12������ͭ���������б��Τ��� document.body.focus()���ɲ�  //
// 2005/09/15 �����ƥ�ޥ������ξȲ��Խ����ɲäˤ��쥤�����Ȥ��ѹ�      //
// 2005/10/25 <a href='javascript:noMenu()' ���ѹ�  ��Υ�������backup��    //
// 2006/01/26 ȯ����������и˥�˥塼��ȯ����������ࡦ��Ω��˥塼���ѹ�//
// 2006/11/01 ������ɥ��򾮤������������б���overflow-y�򳰤���nowrap�ɲ�//
//            �ޤ�JavaScript��checkOverFlow()���ɲä�overflowY��ưŪ���б�  //
// 2006/12/05 ��˥塼�Υ쥤�������ѹ������и˽��פȼ����������פ�Ȳ��  //
// 2007/01/17 ����������ǥ�˥塼���ɲ� (���̤ˤϽФ���������Ū��)         //
// 2007/02/20 parts_stock_plan_Main.php �� parts_stock_plan_form.php ���ѹ� //
// 2007/03/12 ���ʺ߸˷���Υǥ��쥯�ȥ��ѹ�                                //
// 2007/03/24 �������ʹ���ɽ�ξȲ��ǥ��쥯�ȥ��ѹ��ȥץ�����ѹ�        //
// 2007/06/08 ������� �߸���ͭ�����ξȲ��˥塼���ɲ�                     //
// 2007/06/14 �嵭�Υ�˥塼̾��߸���ͭ������ʬ�Ϥ��ѹ����åץإ�פ��ѹ�  //
// 2007/08/04 �߸ˡ�ͭ�����Ѥ����ޥ��ʥ��ꥹ�ȥ�˥塼���ɲ�                //
// 2007/09/05 payable_linear_vendor_summary2 (��˥� ������ ���) ���ɲ�    //
//            �����������������å� ���ɲ�                                 //
// 2007/09/18 E_ALL | E_STRICT ���ѹ�                                       //
// 2007/09/25 php�Υ��硼�ȥ��åȥ�����ɸ�ॿ���� ������������ƥ���ɲ�    //
// 2007/10/04 �ǿ����������Ͽ(�����ֹ�)��˥塼���ɲ�  �ǥ����������ѹ�  //
// 2007/10/13 ��󥯤�target°���� application ��_self ���ѹ�               //
// 2008/02/12 ��˥������������������å����ɲ�materialCheckLinear_Main.php//
// 2008/02/14 ��˥������������������å���materialCheckLinear_Main2.php   //
//            ���ѹ�                                                        //
// 2010/05/06 ���ץ顦��˥����������������Ͽ���ɲá�300144�Τߡ�     ��ë //
// 2010/05/13 ����ñ������������˥塼�ˤޤȤ᤿                     ��ë //
// 2011/05/26 ����������Ӥ�������˥塼���ɲ�                       ��ë //
// 2011/05/30 ����������Ӥ��̥�˥塼�ˤޤȤ᤿��                   ��ë //
// 2011/11/10 �����ٱ��ʥޥ�������Ͽ���˥塼���ɲä���               ��ë //
// 2011/11/22 Ǽ���٤����ʾȲ���˥塼���ɲ�                         ��ë //
// 2011/12/21 �����ٱ��Ϣ��˥塼���ɲá������ٱ��ʥޥ������ˤ�������    //
//            �����ٱ��Ϣ��˥塼������                               ��ë //
// 2013/01/28 �����ץ���񡦺����������ɲ�                         ��ë //
//            �Х�������Υݥ�פ��ѹ� ɽ���Τߥǡ����ϥХ����Τޤ� ��ë//
// 2014/10/23 ��JIS�о����ʤ��������ӾȲ��˥塼���ɲ�                ��ë //
// 2014/12/04 ��JIS�б����ʢ���JIS�о����ʤ��ѹ�                       ��ë //
// 2015/05/21 �����������б��ʥХ����ê����ۢ��ġ�����ѹ���         ��ë //
// 2016/03/24 A�������ξȲ��Ȳ��˥塼�ز��ɲ�                      ��ë //
// 2017/04/27 A�������ξȲ��Ĺë�����ɲ�                          ��ë //
// 2017/04/27 ��JIS�о����ʥ�˥塼���о����ʽ��ץ�˥塼���ѹ�        ��ë //
// 2017/06/14 A�������ξȲ��Ȳ��˥塼���ɲ�                        ��ë //
// 2020/12/24 ��Ω�������ʰ�����Ȳ��˥塼���ɲ�                     ���� //
// 2021/01/08 ��Ω�����Խ���ȯ����������ࡦ��Ω��˥塼���ɲ�       ���� //
//////////////////////////////////////////////////////////////////////////////
// ini_set('error_reporting', E_ALL | E_STRICT);
// ini_set('error_reporting', E_ALL);          // E_ALL='2047' debug ��
ini_set('display_errors', '0');             // Error ɽ�� ON debug �� ��꡼���女����
// ini_set('implicit_flush', 'off');           // echo print �� flush �����ʤ�(�٤��ʤ뤿��) CLI��
// ini_set('max_execution_time', 1200);        // ����¹Ի���=20ʬ WEB CGI��
ob_start('ob_gzhandler');                   // ���ϥХåե���gzip����
session_start();                            // ini_set()�μ��˻��ꤹ�뤳�� Script �Ǿ��

require_once ('../function.php');
require_once ('../tnk_func.php');
require_once ('../MenuHeader.php');         // TNK ������ menu class
access_log();                               // Script Name �ϼ�ư����

///// TNK ���ѥ�˥塼���饹�Υ��󥹥��󥹤����
$menu = new MenuHeader(0);                  // ǧ�ڥ����å�0=���̰ʾ� �����=TOP_MENU �����ȥ�̤����

////////////// ����������
$menu->set_site(30, 999);                   // site_index=30(������˥塼) site_id=999(�����ȥ�˥塼�򳫤�)
////////////// �꥿���󥢥ɥ쥹����
// $menu->set_RetUrl(SYS_MENU);                // �̾�ϻ��ꤹ��ɬ�פϤʤ�
//////////// �����ȥ�̾(�������Υ����ȥ�̾�ȥե�����Υ����ȥ�̾)
$menu->set_title('�� �� ��˥塼');
//////////// ɽ�������
// $menu->set_caption('������� �ط� ��˥塼');
//////////// �ƽ����action̾�ȥ��ɥ쥹����
    /************ ������� �ط� ��˥塼 *************/
$menu->set_action('����ñ��������������',         INDUST . 'material/sales_material_comp_form.php');
$menu->set_action('�������ξȲ�ײ��ֹ�',         INDUST . 'material/materialCost_view_plan.php');
$menu->set_action('�������ξȲ�ASSY�ֹ�',         INDUST . 'material/materialCost_view_assy.php');
$menu->set_action('���������������Ψɽ',       INDUST . 'material/materialCost_sales_comp.php');
$menu->set_action('��������̤��Ͽ�Ȳ�',           INDUST . 'material/materialCost_unregist_view.php');
$menu->set_action('����������Ͽ',                 INDUST . 'material/materialCost_entry_plan.php');
$menu->set_action('���ڶ�ʬ���������',             INDUST . 'sales_kubun_material.php');
$menu->set_action('����ñ�����������',             INDUST . 'parts/parts_sales_price_form.php');
$menu->set_action('�����������������å�',         INDUST . 'materialCheck/materialCheck_Main.php');
$menu->set_action('�ǿ�����������Ͽ',             INDUST . 'material/materialCost_entry_assy.php');
$menu->set_action('��˥������������������å�',   INDUST . 'materialCheck/materialCheckLinear_Main2.php');
$menu->set_action('����ñ�����������˥塼',           INDUST . 'material_new/materialNew_menu.php');
$menu->set_action('�����������(ǯ��)',           INDUST . 'material/material_compare/material_compare_form.php');
$menu->set_action('����������ӥ�˥塼',           INDUST . 'material_compare/material_compare_menu.php');
    /************ �������� �Ȳ� ��˥塼 *************/
$menu->set_action('���ʺ߸�ͽ��',                   INDUST . 'parts/parts_stock_plan/parts_stock_plan_form.php');
$menu->set_action('���ʺ߸˷���',                   INDUST . 'parts/parts_stock_history/parts_stock_form.php');
$menu->set_action('ñ������ξȲ�',                 INDUST . 'parts/parts_cost_form.php');
$menu->set_action('��������ξȲ�',                 INDUST . 'Aden/aden_master_view_form.php');
$menu->set_action('ȯ��ײ�ξȲ�',                 INDUST . 'order/order_plan_view.php');
$menu->set_action('��ݼ��ӤξȲ�',                 INDUST . 'payable/act_payable_form.php');
$menu->set_action('�ٵ���ӤξȲ�',                 INDUST . 'act_miprov_view.php');
$menu->set_action('�������̤�������پȲ�',         INDUST . 'sales_miken/sales_miken_Main.php');
// $menu->set_action('�������ʹ���ɽ�ξȲ�',           INDUST . 'allo_conf_parts_form.php');
// $menu->set_action('�������ʹ���ɽ�ξȲ�',           INDUST . 'material/allo_conf_parts_form.php');
$menu->set_action('�������ʹ���ɽ�ξȲ�',           INDUST . 'parts/allocate_config/allo_conf_parts_form.php');
$menu->set_action('NKB�����ʰ���',                  INDUST . 'parts_control/parts_storage_space/parts_storage_space_Main.php');
$menu->set_action('Ĺ����α���ʤξȲ�',             INDUST . 'long_holding_parts/in_date/long_holding_parts_Main.php');
$menu->set_action('������ͭ���ʬ��',               INDUST . 'long_holding_parts/inventory_average/inventory_average_Main.php');
$menu->set_action('���ʺ߸˥ޥ��ʥ�',               INDUST . 'parts/parts_stock_avail_minus/parts_stock_avail_minus_Main.php');
$menu->set_action('���������ξȲ�',                 INDUST . 'aden_details/aden_details_form.php');
$menu->set_action('��Ω�������ʰ���',               INDUST . 'assembly/assembly_comp_parts_list/assembly_comp_parts_list_form.php');
    /************ ��١��� �Ȳ� ��˥塼 *************/
$menu->set_action('���ץ�ê�����',                 ACT    . 'inventory/inventory_month_c_view.php');
$menu->set_action('��˥�ê�����',                 ACT    . 'inventory/inventory_month_l_view.php');
$menu->set_action('���ץ����� ê����� �Ȳ�',       ACT    . 'inventory/inventory_monthly_ctoku_view.php');
$menu->set_action('�Х���� ê����� �Ȳ�',         ACT    . 'inventory/inventory_month_bimor_view.php');
$menu->set_action('�ġ��� ê����� �Ȳ�',           ACT    . 'inventory/inventory_month_tool_view.php');
$menu->set_action('������ۤξȲ�',                 ACT    . 'act_purchase_view.php');
$menu->set_action('���ץ�������������',           INDUST . 'payable/payable_ctoku_view.php');
$menu->set_action('���ץ����� ������ ��ݶ��',     INDUST . 'payable/payable_ctoku_vendor_summary.php');
$menu->set_action('��������������',               INDUST . 'payable/payable_ctoku_view2.php');
$menu->set_action('������ ������ ���',             INDUST . 'payable/payable_ctoku_vendor_summary2.php');
$menu->set_action('��ɸ�� ������ ���',             INDUST . 'payable/payable_cstd_vendor_summary2.php');
$menu->set_action('��˥� ������ ���',             INDUST . 'payable/payable_linear_vendor_summary2.php');
$menu->set_action('���ʽи˽���',                   INDUST . 'parts_control/parts_pickup_analyze/parts_pickup_analyze_Main.php');
$menu->set_action('������������',                   INDUST . 'order/acceptance_inspection_analyze/acceptance_inspection_analyze_Main.php');
$menu->set_action('Ǽ��ͽ���۾Ȳ�',               INDUST . 'order_money/order_schedule.php');
    /************ �ޥ������ط� ��˥塼 *************/
$menu->set_action('�����ƥ�ޥ�����',               INDUST . 'master/parts_item/parts_item_Main.php');
$menu->set_action('ȯ����ޥ������Ȳ�',             INDUST . 'vendor_master_view.php');
$menu->set_action('��̾�ˤ���ֹ渡��',             INDUST . 'master/item_name_search/item_name_search_Main.php');
$menu->set_action('���ʥ��롼�ץ�����',             INDUST . 'master/product_master/product_master_menu.php');
    /************ ȯ�� �� ���� �� �и� �� ��Ω �� ��˥塼 *************/
$menu->set_action('Ǽ��ͽ���̤��������',           INDUST . 'order/order_schedule.php');
$menu->set_action('��������',                       INDUST . 'order/inspection_recourse.php');
$menu->set_action('�����������',                   INDUST . 'order/inspectingList.php');
$menu->set_action('ȯ�����Υ���',               INDUST . 'order/order_process_mnt.php');
$menu->set_action('����������������',               TEST   . 'ooya/pdf.php');
$menu->set_action('������������ɸ��',               TEST   . 'ooya/pdf_standard.php');
$menu->set_action('���Ϲ�������ĥꥹ��',           INDUST . 'vendor/vendor_order_list_form.php');
$menu->set_action('������ʽи�',                   INDUST . 'parts_control/parts_pickup_time_Main.php');
$menu->set_action('��Ω�ؼ�',                       INDUST . 'assembly/assembly_process/assembly_process_time_Main.php');
$menu->set_action('��Ω�����Խ�',                   INDUST . 'assembly/assembly_time_edit/assembly_time_edit_Main.php');
$menu->set_action('��Ω�����Ȳ�',                   INDUST . 'assembly/assembly_process_show/assembly_process_show_Main.php');
$menu->set_action('�����ײ�Ȳ�',                   INDUST . 'scheduler/schedule_show/assembly_schedule_show_Main.php');
$menu->set_action('���ӹ����Ȳ�',                   INDUST . 'assembly/assembly_time_show/assembly_time_show_Main.php');
$menu->set_action('������������',                   INDUST . 'assembly/assembly_time_compare/assembly_time_compare_Main.php');
$menu->set_action('�������������Խ�',               INDUST . 'assembly/assembly_time_compare_edit/assembly_time_compare_edit_Main.php');
$menu->set_action('�饤���̹��������',             INDUST . 'assembly/assembly_time_graph/assembly_time_graph_Main.php');
$menu->set_action('�С������ɺ���',                 INDUST . 'BarCode/datasum_barcode.php');
$menu->set_action('��˥����ʽи�',                 INDUST . 'parts_control/parts_pickup_linear/parts_pickup_linear_Main.php');
$menu->set_action('�饤�󥫥�����',               INDUST . 'scheduler/assembly_calendar/assembly_calendar_Main.php');
// $menu->set_action('�ǡ���Ʊ��',                     INDUST . 'order/order_data_difference_update.php');
// $menu->set_action('�ǡ���Ʊ��',                     INDUST . 'order/order_data_ftp_update.php');
$menu->set_action('������������ƥ�',               INDUST . 'punchMark/index.php');
$menu->set_action('�������ӽ����',                 INDUST . 'inspectionPrint/inspectionPrint.php');
$menu->set_action('���֤���ʸ����',                 INDUST . 'order/total_repeat_order/total_repeat_order_Main.php');
$menu->set_action('Ǽ���٤����ʤξȲ�',             INDUST . 'order/delivery_late/delivery_late_form.php');
$menu->set_action('�����ץ�������������˥塼',  INDUST . 'custom_attention/custom_attention_menu.php');

$menu->set_action('�����ٱ��Ϣ��˥塼',           INDUST . 'product_support/product_support_menu.php');
$menu->set_action('�о����ʽ��ץ�˥塼',           INDUST . 'new_jis/new_jis_menu.php');

$menu->set_action('������������',                   INDUST . 'order/inspection_date/inspection_date_form.php');

//////////// �֥饦�����Υ���å����к���
$uniq = $menu->set_useNotCache('target');

//////////// �о�ǯ�����Υ��å����ǡ�������
if (isset($_SESSION['ind_ymd'])) {
    $ind_ymd = $_SESSION['ind_ymd']; 
} else {
    $ind_ymd = date('Ymd');        // ���å����ǡ������ʤ����ν����(����)
}
//////////// �о�ǯ��Υ��å����ǡ�������
if (isset($_SESSION['ind_ym'])) {
    $ind_ym = $_SESSION['ind_ym']; 
} else {
    $ind_ym = date('Ym');        // ���å����ǡ������ʤ����ν����(����)
}


$uid   = $_SESSION['User_ID'];
$query = "SELECT sid FROM user_detailes WHERE uid='$uid'";
$res   = array();
if( getResult($query,$res) <= 0 ) {
    $sid   = "";
} else {
    $sid   = $res[0][0];
}

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
<?php echo $menu->out_site_java() ?>
<?php echo $menu->out_css() ?>
<script type='text/javascript'>
<!--
function monthly_send(script_name)
{
    document.monthly_form.action = 'ind_branch.php?ind_name=' + script_name;
    document.monthly_form.submit();
}
function set_focus()
{
    // document.body.focus();   // F2/F12������ͭ���������б�
    // document.mhForm.backwardStack.focus();  // �嵭��IE�ΤߤΤ���NN�б�
}
function checkOverFlow()
{
    if (document.body.clientHeight) {
        var h = document.body.clientHeight; // IE
    } else {
        var h = window.innerHeight;         // NN
    }
    if (h <= 650) {     // ��˥塼���̤��ѹ��ˤʤä����ϥޥ��å��ʥ�С�650���ѹ������
        document.body.style.overflowY = "scroll";
    } else {
        document.body.style.overflowY = "hidden";
    }
}
// -->
</script>

<!-- �������륷���ȤΥե��������򥳥��� HTML���� �����Ȥ�����Ҥ˽���ʤ�������� 
<link rel='stylesheet' href='<?php echo MENU_FORM . '?' .$uniq ?>' type='text/css' media='screen'>
-->

<style type='text/css'>
<!--
/** font-weight: normal;        **/
/** font-weight: 400;    ��Ʊ�� **/
/** font-weight: bold;          **/
/** font-weight: 700;    ��Ʊ�� **/
/**         100��900�ޤ�100��� **/
select {
    background-color:teal;
    color:white;
}
.pt11b {
    font-size:   11pt;
    font-weight: bold;
}
a:hover {
    background-color: blue;
    color           : white;
}
a:active {
    background-color: white;
    color           : red;
}
a {
    font-size:   11pt;
    font-weight: bold;
    color:       black;
}
.caption_font {
    font-size:          11pt;
    font-weight:        bold;
    background-color:   #ffffa6;
    color:              blue;
    border-style: solid;
    border-width: 1px;
    border-top-color:       #FFFFFF;
    border-left-color:      #FFFFFF;
    border-right-color:     #999999;
    border-bottom-color:    #999999;
}
.winbox {
    border-style: solid;
    border-width: 1px;
    border-top-color:       #FFFFFF;
    border-left-color:      #FFFFFF;
    border-right-color:     #999999;
    border-bottom-color:    #999999;
    /* background-color:#d6d3ce; */
}
.winbox_field {
    border-style: solid;
    border-width: 1px;
    border-top-color:       #999999;
    border-left-color:      #999999;
    border-right-color:     #FFFFFF;
    border-bottom-color:    #FFFFFF;
    /* background-color:#d6d3ce; */
}
body {
    background-image:       url(<?php echo IMG ?>t_nitto_logo4.png);
    background-repeat:      no-repeat;
    background-attachment:  fixed;
    background-position:    center bottom;
    /* overflow-y:             hidden; */
}
-->
</style>
</head>
<body onresize='checkOverFlow()' onLoad='set_focus()'>
    <center>
<?php echo $menu->out_title_border() ?>
        <?php
        if ($sid != '95') {
        ?>
        <form action='ind_branch.php' method='get'>
        <table bgcolor='#d6d3ce'  border='1' cellspacing='0' cellpadding='3'>
            <tr><td> <!-- ���ߡ�(�ǥ�������) -->
            <table class='winbox_field' bgcolor='#d6d3ce' border='1' cellspacing='0' cellpadding='3'>
                <tr>
                    <td nowrap width='810' bgcolor='#ffffa6' align='center' colspan='4' class='caption_font'>
                        ������� �ط� ��˥塼
                    </td>
                </tr>
                <tr>
                    <td nowrap class='winbox' colspan='1' align='center' bgcolor='#d6d3ce'>
                        <a href='javascript:function(){}'
                            target='_self' style='text-decoration:none;'
                            onClick='location.replace("<?php echo $menu->out_action('����ñ��������������')?>"); return false;'
                            onMouseover="status='���λ���ñ��������������ӥꥹ�Ȥ�Ȳ񤷤ޤ���';return true;"
                            onMouseout="status=''"
                            title='���λ���ñ��������������ӥꥹ�Ȥ�Ȳ񤷤ޤ���'
                        >
                            ����ñ��������������
                        </a>
                    </td>
                    <td nowrap class='winbox' colspan='1' align='center' bgcolor='#d6d3ce'>
                        <a href='javascript:function(){}'
                            target='_self' style='text-decoration:none;'
                            onClick='location.replace("<?php echo $menu->out_action('�������ξȲ�ײ��ֹ�')?>"); return false;'
                            onMouseover="status='�ײ��ֹ�����ʤ��������ξȲ��Ԥ��ޤ���';return true;"
                            onMouseout="status=''"
                            title='�ײ��ֹ�����ʤ��������ξȲ��Ԥ��ޤ���'
                        >
                            �������ξȲ�(�ײ��ֹ�)
                        </a>
                    </td>
                    <td nowrap class='winbox' colspan='1' align='center' bgcolor='#d6d3ce'>
                        <a href='javascript:function(){}'
                            target='_self' style='text-decoration:none;'
                            onClick='location.replace("<?php echo $menu->out_action('�������ξȲ�ASSY�ֹ�')?>"); return false;'
                            onMouseover="status='ASSY�ֹ�(�����ֹ�)�����ʤ��������ξȲ��Ԥ��ޤ���';return true;"
                            onMouseout="status=''"
                            title='ASSY�ֹ�(�����ֹ�)�����ʤ��������ξȲ��Ԥ��ޤ���'
                        >
                            �������ξȲ�(ASSY�ֹ�)
                        </a>
                    </td>
                    <td nowrap class='winbox' colspan='1' align='center' bgcolor='#d6d3ce'>
                        <a href='javascript:function(){}'
                            target='_self' style='text-decoration:none;'
                            onClick='location.replace("<?php echo $menu->out_action('����������ӥ�˥塼')?>"); return false;'
                            onMouseover="status='����������Ӵ�Ϣ��˥塼��';return true;"
                            onMouseout="status=''"
                            title='����������Ӵ�Ϣ��˥塼��'
                        >
                            ����������ӥ�˥塼
                        </a>
                    </td>
                </tr>
                <tr>
                    <td nowrap class='winbox' colspan='1' align='center' bgcolor='#d6d3ce'>
                        <a href='javascript:function(){}'
                            target='_self' style='text-decoration:none;'
                            onClick='location.replace("<?php echo $menu->out_action('���������������Ψɽ')?>"); return false;'
                            onMouseover="status='��������볰����(���ʡ�������)�������(�ù�����Ω��)����Ψɽ��Ȳ񤷤ޤ���';return true;"
                            onMouseout="status=''"
                            title='��������볰����(���ʡ�������)�������(�ù�����Ω��)����Ψɽ��Ȳ񤷤ޤ���'
                        >
                            ���������������Ψɽ
                        </a>
                    </td>
                    <td nowrap class='winbox' colspan='1' align='center' bgcolor='#d6d3ce'>
                        <a href='javascript:function(){}'
                            target='_self' style='text-decoration:none;'
                            onClick='location.replace("<?php echo $menu->out_action('��������̤��Ͽ�Ȳ�')?>"); return false;'
                            onMouseover="status='������ʤ�����������̤��Ͽ�Τ�Τ�Ȳ񤷤ޤ���(Ⱦ���١����ǤξȲ�)';return true;"
                            onMouseout="status=''"
                            title='������ʤ�����������̤��Ͽ�Τ�Τ�Ȳ񤷤ޤ���(Ⱦ���١����ǤξȲ�)'
                        >
                            ��������̤��Ͽ�Ȳ�
                        </a>
                    </td>
                    <td nowrap class='winbox' colspan='1' align='center' bgcolor='#d6d3ce'>
                        <a href='javascript:function(){}'
                            target='_self' style='text-decoration:none;'
                            onClick='location.replace("<?php echo $menu->out_action('����������Ͽ')?>"); return false;'
                            onMouseover="status='�ײ��ֹ�ñ�̤����ʤ�����������Ͽ��Ԥ��ޤ���';return true;"
                            onMouseout="status=''"
                            title='�ײ��ֹ�ñ�̤����ʤ�����������Ͽ��Ԥ��ޤ���'
                        >
                            ����������Ͽ
                        </a>
                    </td>
                    <td nowrap class='winbox' colspan='1' align='center' bgcolor='#d6d3ce'>
                        <a href='javascript:function(){}'
                            target='_self' style='text-decoration:none;'
                            onClick='location.replace("<?php echo $menu->out_action('�ǿ�����������Ͽ')?>"); return false;'
                            onMouseover="status='�ǿ����������������ֹ����Ͽ��Ԥ��ޤ����ײ��ֹ�ϼ�ưȯ�֤Ǥ���';return true;"
                            onMouseout="status=''"
                            title='�ǿ����������������ֹ����Ͽ��Ԥ��ޤ����ײ��ֹ�ϼ�ưȯ�֤Ǥ���'
                        >
                            �ǿ�����������Ͽ
                        </a>
                    </td>
                </tr>
                <tr>
                    <td nowrap class='winbox' colspan='1' align='center' bgcolor='#d6d3ce'>
                        <a href='javascript:function(){}'
                            target='_self' style='text-decoration:none;'
                            onClick='location.replace("<?php echo $menu->out_action('���ڶ�ʬ���������')?>"); return false;'
                            onMouseover="status='����ñ���η����� �ڤӥ����ȥ����󤵤줿���ʤξȲ��Ԥ��ޤ���';return true;"
                            onMouseout="status=''"
                            title='����ñ���η����� �ڤӥ����ȥ����󤵤줿���ʤξȲ��Ԥ��ޤ���'
                        >
                            ���ڶ�ʬ������������
                        </a>
                    </td>
                    <td nowrap class='winbox' colspan='1' align='center' bgcolor='#d6d3ce'>
                        <a href='javascript:function(){}'
                            target='_self' style='text-decoration:none;'
                            onClick='location.replace("<?php echo $menu->out_action('����ñ�����������')?>"); return false;'
                            onMouseover="status='���ʤι���ñ��������칩��ؤ��������(����ñ��)��Ȳ񤷤ޤ���';return true;"
                            onMouseout="status=''"
                            title='���ʤι���ñ��������칩��ؤ��������(����ñ��)��Ȳ񤷤ޤ���'
                        >
                            ����ñ�����������ʾȲ�
                        </a>
                    </td>
                    <td nowrap class='winbox' colspan='1' align='center' bgcolor='#d6d3ce'>
                        &nbsp;
                    </td>
                    <!--
                    <td nowrap class='winbox' colspan='1' align='center' bgcolor='#d6d3ce'>
                        <a href='javascript:function(){}'
                            target='_self' style='text-decoration:none;'
                            onClick='location.replace("<?php echo $menu->out_action('�����������������å�')?>"); return false;'
                            onMouseover="status='���ڲ��ʸ�ľ���Τ�����������Υ����å���';return true;"
                            onMouseout="status=''"
                            title='���ڲ��ʸ�ľ���Τ�����������Υ����å���'
                        >
                            �����������������å�
                        </a>
                    </td>
                    -->
                    <td nowrap class='winbox' colspan='1' align='center' bgcolor='#d6d3ce'>
                        <a href='javascript:function(){}'
                            target='_self' style='text-decoration:none;'
                            onClick='location.replace("<?php echo $menu->out_action('����ñ�����������˥塼')?>"); return false;'
                            onMouseover="status='����ñ������ν�����˥塼';return true;"
                            onMouseout="status=''"
                            title='����ñ������ν�����˥塼'
                        >
                            ����ñ�����������˥塼
                        </a>
                    </td>
                    <!--
                    <td nowrap class='winbox' colspan='1' align='center' bgcolor='#d6d3ce'>
                        <a href='javascript:function(){}'
                            target='_self' style='text-decoration:none;'
                            onClick='location.replace("<?php echo $menu->out_action('��˥������������������å�')?>"); return false;'
                            onMouseover="status='��˥����ڲ��ʸ�ľ���Τ�����������Υ����å���';return true;"
                            onMouseout="status=''"
                            title='��˥����ڲ��ʸ�ľ���Τ�����������Υ����å���'
                        >
                            ��˥������������������å�
                        </a>
                    </td>
                    -->
                </tr>
            </table>
            </td></tr>
        </table> <!-- ���ߡ�End -->
        </form>
        
        <hr color='797979' width='95%'>
        
        <?php
        }
        ?>
        
        <form action='ind_branch.php' method='get'>
        <table bgcolor='#d6d3ce'  border='1' cellspacing='0' cellpadding='3'>
            <tr><td> <!-- ���ߡ�(�ǥ�������) -->
            <table class='winbox_field' bgcolor='#d6d3ce' border='1' cellspacing='0' cellpadding='3'>
                <tr>
                    <td nowrap width='810' style='background-color:#ffffa6;' align='center' colspan='5' class='caption_font'>
                        �Ȳ�  �� �� �� ��
                    </td>
                </tr>
                <?php
                if ($sid != '95') {
                ?>
                <tr>
                    <td nowrap class='winbox' align='center' bgcolor='#d6d3ce' nowrap>
                        <a href='javascript:function(){}'
                            target='_self' style='text-decoration:none;'
                            onClick='location.replace("<?php echo $menu->out_action('���ʺ߸�ͽ��')?>"); return false;'
                            onMouseover="status='���ʤκ߸�ͽ��(������ȯ�����)�ξȲ��Ԥ��ޤ���';return true;"
                            onMouseout="status=''"
                            title='���ʤκ߸�ͽ��(������ȯ�����)�ξȲ��Ԥ��ޤ���'
                        >
                            �߸�ͽ������Ȳ�
                        </a>
                    </td>
                    <td nowrap class='winbox' align='center' bgcolor='#d6d3ce'>
                        <a href='javascript:function(){}'
                            target='_self' style='text-decoration:none;'
                            onClick='location.replace("<?php echo $menu->out_action('ñ������ξȲ�')?>"); return false;'
                            onMouseover="status='���ʤ�ñ����Ͽ�η����Ȳ񤷤ޤ���';return true;"
                            onMouseout="status=''"
                            title='���ʤ�ñ����Ͽ�η����Ȳ񤷤ޤ���'
                        >
                            ñ������ξȲ�
                        </a>
                    </td>
                    <td nowrap class='winbox' align='center' bgcolor='#d6d3ce'>
                        <a href='javascript:function(){}'
                            target='_self' style='text-decoration:none;'
                            onClick='location.replace("ind_branch.php?ind_name=aden_master_view"); return false;'
                            onMouseover="status='���������Σ�������ξȲ��Ԥ��ޤ����Ȳ������Ω�ײ�򥢥ɥ��󤵤������Ǥ��ޤ���';return true;"
                            onMouseout="status=''"
                            title='���������Σ�������ξȲ��Ԥ��ޤ����Ȳ������Ω�ײ�򥢥ɥ��󤵤������Ǥ��ޤ���'
                        >
                            ��������ξȲ�
                        </a>
                    </td>
                    <td nowrap class='winbox' align='center' bgcolor='#d6d3ce'>
                        <a href='javascript:function(){}'
                            target='_self' style='text-decoration:none;'
                            onClick='location.replace("<?php echo $menu->out_action('ȯ��ײ�ξȲ�')?>"); return false;'
                            onMouseover="status='ȯ��ײ�ǡ����ξȲ��Ԥ��ޤ������ߤϳ�ǧ�Ѥ�����¸�ߤ��ޤ���������Ū�ˤ���¤�ֹ�������ֹ����ǾȲ�Ǥ���褦�ˤ��ޤ���';return true;"
                            onMouseout="status=''"
                            title='ȯ��ײ�ǡ����ξȲ��Ԥ��ޤ������ߤϳ�ǧ�Ѥ�����¸�ߤ��ޤ���������Ū�ˤ���¤�ֹ�������ֹ����ǾȲ�Ǥ���褦�ˤ��ޤ���'
                        >
                            ȯ��ײ�ξȲ�
                        </a>
                    </td>
                    <td nowrap class='winbox' align='center' bgcolor='#d6d3ce'>
                        <a href='javascript:function(){}'
                            target='_self' style='text-decoration:none;'
                            onClick='location.replace("<?php echo $menu->out_action('���ʽи˽���')?>"); return false;'
                            onMouseover="status='�������ʽиˤν��פ�Ԥ��ޤ���';return true;"
                            onMouseout="status=''"
                            title='�������ʽиˤν��פ�Ԥ��ޤ���'
                        >
                            ������ʽи˽���
                        </a>
                    </td>
                </tr>
                <tr>
                    <td nowrap class='winbox' colspan='1' align='center' bgcolor='#d6d3ce'>
                        <a href='javascript:function(){}'
                            target='_self' style='text-decoration:none;'
                            onClick='location.replace("<?php echo $menu->out_action('���ʺ߸˷���')?>"); return false;'
                            onMouseover="status='���ʤκ߸˷���ξȲ��Ԥ��ޤ���';return true;"
                            onMouseout="status=''"
                            title='���ʤκ߸˷���ξȲ��Ԥ��ޤ���'
                        >
                            ���ʺ߸˷���Ȳ�
                        </a>
                    </td>
                    <td nowrap class='winbox' align='center' bgcolor='#d6d3ce'>
                        <a href='javascript:function(){}'
                            target='_self' style='text-decoration:none;'
                            onClick='location.replace("<?php echo $menu->out_action('��ݼ��ӤξȲ�')?>"); return false;'
                            onMouseover="status='���������ʤ���ݶ�(�������)�ξȲ��Ԥ��ޤ���';return true;"
                            onMouseout="status=''"
                            title='���������ʤ���ݶ�(�������)�ξȲ��Ԥ��ޤ���'
                        >
                            ��ݼ��ӤξȲ�
                        </a>
                    </td>
                    <td nowrap class='winbox' align='center' bgcolor='#d6d3ce'>
                        <a href='javascript:function(){}'
                            target='_self' style='text-decoration:none;'
                            onClick='location.replace("ind_branch.php?ind_name=act_miprov_view"); return false;'
                            onMouseover="status='����������λٵ���ӾȲ��Ԥ��ޤ������ߤϳ�ǧ�ѤΤߤǡ�����Ū�������ֹ��ٵ��ֹ�ǤξȲ񤬤Ǥ���褦�ˤ��ޤ���';return true;"
                            onMouseout="status=''"
                            title='����������λٵ���ӾȲ��Ԥ��ޤ������ߤϳ�ǧ�ѤΤߤǡ�����Ū�������ֹ��ٵ��ֹ�ǤξȲ񤬤Ǥ���褦�ˤ��ޤ���'
                        >
                            �ٵ���ӤξȲ�
                        </a>
                    </td>
                    <td nowrap class='winbox' align='center' bgcolor='#d6d3ce'>
                        <a href='javascript:function(){}'
                            target='_self' style='text-decoration:none;'
                            onClick='location.replace("<?php echo $menu->out_action('Ĺ����α���ʤξȲ�')?>"); return false;'
                            onMouseover="status='����κǽ��������Ǹ��ߤޤ��߸ˤ������Τ� �Ȳ񤷤ޤ���';return true;"
                            onMouseout="status=''"
                            title='����κǽ��������Ǹ��ߤޤ��߸ˤ������Τ� �Ȳ񤷤ޤ���'
                        >
                            Ĺ����α���ʤξȲ�
                        </a>
                    </td>
                    <td nowrap class='winbox' align='center' bgcolor='#d6d3ce'>
                        <a href='javascript:function(){}'
                            target='_self' style='text-decoration:none;'
                            onClick='location.replace("<?php echo $menu->out_action('������������')?>"); return false;'
                            onMouseover="status='���������ν��פ�Ԥ��ޤ���';return true;"
                            onMouseout="status=''"
                            title='���������ν��פ�Ԥ��ޤ���'
                        >
                            ���������ν���
                        </a>
                    </td>
                </tr>
                <tr>
                    <td nowrap class='winbox' align='center' bgcolor='#d6d3ce' nowrap>
                        <a href='javascript:function(){}'
                            target='_self' style='text-decoration:none;'
                            onClick='location.replace("<?php echo $menu->out_action('NKB�����ʰ���')?>"); return false;'
                            onMouseover="status='���ʤθ����Ǹ����������˾�����ꤷ�ư����ǾȲ񤷤ޤ���';return true;"
                            onMouseout="status=''"
                            title='���ʤθ����Ǹ����������˾�����ꤷ�ư����ǾȲ񤷤ޤ���'
                        >
                            �Σˣ������ʰ���
                        </a>
                    </td>
                    <td nowrap class='winbox' colspan='1' align='center' bgcolor='#d6d3ce'>
                        <?php // onClick='location.replace("ind_branch.php?ind_name=sales_miken_view"); return false;' ?>
                        <a href='javascript:function(){}'
                            target='_self' style='text-decoration:none;'
                            onClick='location.replace("<?php echo $menu->out_action('�������̤�������پȲ�')?>"); return false;'
                            onMouseover="status='���칩���Ǽ��(����)����̤�����ʤξȲ��Ԥ��ޤ���(���̤�׾���)';return true;"
                            onMouseout="status=''"
                            title='���칩���Ǽ��(����)����̤�����ʤξȲ��Ԥ��ޤ���(���̤�׾���)'
                        >
                            �������̤�����Ȳ�
                        </a>
                    </td>
                    <td nowrap class='winbox' colspan='1' align='center' bgcolor='#d6d3ce'>
                        <a href='javascript:function(){}'
                            target='_self' style='text-decoration:none;'
                            onClick='location.replace("<?php echo $menu->out_action('�������ʹ���ɽ�ξȲ�')?>"); return false;'
                            onMouseover="status='�������ʹ���ɽ �� ����ɽ �� �и�ɽ �ξȲ��Ԥ��ޤ���';return true;"
                            onMouseout="status=''"
                            title='�������ʹ���ɽ �� ����ɽ �� �и�ɽ �ξȲ��Ԥ��ޤ���'
                        >
                            �������ʹ���ɽ�ξȲ�
                        </a>
                    </td>
                    <td nowrap class='winbox' align='center' bgcolor='#d6d3ce' nowrap>
                        <a href='javascript:function(){}'
                            target='_self' style='text-decoration:none;'
                            onClick='location.replace("<?php echo $menu->out_action('������ͭ���ʬ��')?>"); return false;'
                            onMouseover="status='������ʤκ߸���ͭ�������ʿ�ѽи˿����߸˶������ �װ���˽��פ�ʬ�Ϥ�Ԥ��ޤ���';return true;"
                            onMouseout="status=''"
                            title='������ʤκ߸���ͭ�������ʿ�ѽи˿����߸˶������ �װ���˽��פ�ʬ�Ϥ�Ԥ��ޤ���'
                        >
                            ���ʺ߸�ʬ�ϥ�˥塼
                        </a>
                    </td>
                    <td nowrap class='winbox' align='center' bgcolor='#d6d3ce' nowrap>
                        <a href='javascript:function(){}'
                            target='_self' style='text-decoration:none;'
                            onClick='location.replace("<?php echo $menu->out_action('���ʺ߸˥ޥ��ʥ�')?>"); return false;'
                            onMouseover="status='���� �߸ˡ�ͭ�����ѿ�(ͽ��߸˿�)�ޥ��ʥ��ꥹ�ȥ�˥塼��¹Ԥ��ޤ���';return true;"
                            onMouseout="status=''"
                            title='���� �߸ˡ�ͭ�����ѿ�(ͽ��߸˿�)�ޥ��ʥ��ꥹ�ȥ�˥塼��¹Ԥ��ޤ���'
                        >
                            ����ͭ�����ޥ��ʥ�
                        </a>
                    </td>
                </tr>
                <?php
                }
                ?>
                <tr>
                    <td nowrap class='winbox' align='center' bgcolor='#d6d3ce' nowrap>
                        <a href='javascript:function(){}'
                            target='_self' style='text-decoration:none;'
                            onClick='location.replace("<?php echo $menu->out_action('���������ξȲ�')?>"); return false;'
                            onMouseover="status='���������Σ�������ν��������ξȲ��Ԥ��ޤ���';return true;"
                            onMouseout="status=''"
                            title='���������Σ�������ν��������ξȲ��Ԥ��ޤ���'
                        >
                            ���������ξȲ�
                        </a>
                    </td>
                    <td nowrap class='winbox' align='center' bgcolor='#d6d3ce' nowrap>
                        <a href='javascript:function(){}'
                            target='_self' style='text-decoration:none;'
                            onClick='location.replace("<?php echo $menu->out_action('��Ω�������ʰ���')?>"); return false;'
                            onMouseover="status='��Ω�������ʰ�����ɽ�����ޤ���';return true;"
                            onMouseout="status=''"
                            title='��Ω�������ʰ�����ɽ�����ޤ���'
                        >
                            ��Ω�������ʰ���
                        </a>
                    </td>
                    <td nowrap class='winbox' colspan='1' align='center' bgcolor='#d6d3ce'>
                        &nbsp;
                    </td>
                    <td nowrap class='winbox' align='center' bgcolor='#d6d3ce' nowrap>
                        &nbsp;
                    </td>
                    <td nowrap class='winbox' align='center' bgcolor='#d6d3ce' nowrap>
                        &nbsp;
                    </td>
                </tr>
            </table>
            </td></tr>
        </table> <!-- ���ߡ�End -->
        </form>
        
        <?php
        if ($sid != '95') {
        ?>
        
        <hr color='797979' width='95%'>
        
        <form name='monthly_form' action='ind_branch.php' method='post'>
        <table bgcolor='#d6d3ce'  border='1' cellspacing='0' cellpadding='3'>
            <tr><td> <!-- ���ߡ�(�ǥ�������) -->
            <table class='winbox_field' bgcolor='#d6d3ce' border='1' cellspacing='0' cellpadding='3'>
                <tr>
                    <td nowrap width='810' align='center' colspan='4' class='caption_font'>
                        ��١��� �Ȳ� ��˥塼  ����ǯ��
                        <select name='ind_ym' class='pt11b'>
                            <?php
                            $ym = date("Ym");
                            while(1) {
                                if (substr($ym,4,2)!=01) {
                                    $ym--;
                                } else {
                                    $ym = $ym - 100;
                                    $ym = $ym + 11;
                                }
                                if ($ind_ym == $ym) {
                                    printf("<option value='%d' selected>%sǯ%s��</option>\n",$ym,substr($ym,0,4),substr($ym,4,2));
                                    $init_flg = 0;
                                } else
                                    printf("<option value='%d'>%sǯ%s��</option>\n",$ym,substr($ym,0,4),substr($ym,4,2));
                                if ($ym <= 200010)
                                    break;
                            }
                            ?>
                        </select>
                    </td>
                </tr>
                <tr>
                    <td nowrap class='winbox' align='center' bgcolor='#d6d3ce'>
                        <a href='javascript:function(){}'
                            target='_self' style='text-decoration:none;'
                            onClick='javaScript:monthly_send("inventory_month_c_view"); return false;'
                            onMouseover="status='��١����Υ��ץ����Τ�ê����ۤ�Ȳ񤷤ޤ���';return true;"
                            onMouseout="status=''"
                            title='��١����Υ��ץ����Τ�ê����ۤ�Ȳ񤷤ޤ���'
                        >
                            ���ץ�ê�����
                        </a>
                    </td>
                    <td nowrap class='winbox' align='center' bgcolor='#d6d3ce'>
                        <a href='javascript:function(){}'
                            target='_self' style='text-decoration:none;'
                            onClick='javaScript:monthly_send("inventory_month_ctoku_view"); return false;'
                            onMouseover="status='��١����Υ��ץ������ʤ�ê����ۤ�Ȳ񤷤ޤ���';return true;"
                            onMouseout="status=''"
                            title='��١����Υ��ץ������ʤ�ê����ۤ�Ȳ񤷤ޤ���'
                        >
                            ���ץ�����ê�����
                        </a>
                    </td>
                    <td nowrap class='winbox' align='center' bgcolor='#d6d3ce'>
                        <a href='javascript:function(){}'
                            target='_self' style='text-decoration:none;'
                            onClick='javaScript:monthly_send("inventory_month_l_view"); return false;'
                            onMouseover="status='��١����Υ�˥����Τ�ê����ۤ�Ȳ񤷤ޤ���';return true;"
                            onMouseout="status=''"
                            title='��١����Υ�˥����Τ�ê����ۤ�Ȳ񤷤ޤ���'
                        >
                            ��˥�ê�����
                        </a>
                    </td>
                    <?php
                    if ($_SESSION['User_ID'] == '300144') {
                    ?>
                    <td nowrap class='winbox' align='center' bgcolor='#d6d3ce'>
                        <a href='javascript:function(){}'
                            target='_self' style='text-decoration:none;'
                            onClick='javaScript:monthly_send("inventory_month_tool_view"); return false;'
                            onMouseover="status='��١����Υġ����ê����ۤ�Ȳ񤷤ޤ���';return true;"
                            onMouseout="status=''"
                            title='��١����Υġ����ê����ۤ�Ȳ񤷤ޤ���'
                        >
                            �ġ���ê�����
                        </a>
                    </td>
                    <?php
                    } else {
                    ?>
                    
                    <td nowrap class='winbox' align='center' bgcolor='#d6d3ce'>
                        <a href='javascript:function(){}'
                            target='_self' style='text-decoration:none;'
                            onClick='javaScript:monthly_send("inventory_month_bimor_view"); return false;'
                            onMouseover="status='��١����α��Υݥ�פ�ê����ۤ�Ȳ񤷤ޤ���';return true;"
                            onMouseout="status=''"
                            title='��١����α��Υݥ�פ�ê����ۤ�Ȳ񤷤ޤ���'
                        >
                            ���Υݥ��ê�����
                        </a>
                    </td>
                    <?php
                    }
                    ?>
                </tr>
                <tr>
                    <td nowrap class='winbox' colspan='1' align='center' bgcolor='#d6d3ce'>
                        <a href='javascript:function(){}'
                            target='_self' style='text-decoration:none;'
                            onClick='javaScript:monthly_send("act_purchase_view"); return false;'
                            onMouseover="status='��١����θ��������������λ�����ۤ�Ȳ񤷤ޤ���';return true;"
                            onMouseout="status=''"
                            title='��١����θ��������������λ�����ۤ�Ȳ񤷤ޤ���'
                        >
                            ������ۤξȲ�
                        </a>
                    </td>
                    <td nowrap class='winbox' align='center' bgcolor='#d6d3ce'>
                        <a href='javascript:function(){}'
                            target='_self' style='text-decoration:none;'
                            onClick='javaScript:monthly_send("payable_cstd_vendor_summary2"); return false;'
                            onMouseover="status='��١����Υ��ץ�ɸ���ʤγ�������ݶ��(��������ޤ�)��Ȳ񤷤ޤ���';return true;"
                            onMouseout="status=''"
                            title='��١����Υ��ץ�ɸ���ʤγ�������ݶ��(��������ޤ�)��Ȳ񤷤ޤ���'
                        >
                            ��ɸ�� ������ ���(������)
                        </a>
                    </td>
                    <td nowrap class='winbox' align='center' bgcolor='#d6d3ce'>
                        <a href='javascript:function(){}'
                            target='_self' style='text-decoration:none;'
                            onClick='javaScript:monthly_send("payable_ctoku_view"); return false;'
                            onMouseover="status='��١����Υ��ץ������ʤ���ݶ��(���Ϲ����)��Ȳ񤷤ޤ���';return true;"
                            onMouseout="status=''"
                            title='��١����Υ��ץ������ʤ���ݶ��(���Ϲ����)��Ȳ񤷤ޤ���'
                        >
                            ���ץ�������������
                        </a>
                    </td>
                    <td nowrap class='winbox' align='center' bgcolor='#d6d3ce'>
                        <a href='javascript:function(){}'
                            target='_self' style='text-decoration:none;'
                            onClick='javaScript:monthly_send("payable_ctoku_vendor_summary"); return false;'
                            onMouseover="status='��١����Υ��ץ������ʤ���ݶ��(���Ϲ�����ι�׶��)��Ȳ񤷤ޤ���';return true;"
                            onMouseout="status=''"
                            title='��١����Υ��ץ������ʤ���ݶ��(���Ϲ�����ι�׶��)��Ȳ񤷤ޤ���'
                        >
                            ���ץ����� ������ ���
                        </a>
                    </td>
                </tr>
                <tr>
                    <td nowrap class='winbox' align='center' bgcolor='#d6d3ce'>
                        <a href='javascript:function(){}'
                            target='_self' style='text-decoration:none;'
                            onClick='javaScript:monthly_send("payable_ctoku_view2"); return false;'
                            onMouseover="status='��١����Υ��ץ������ʤ���ݶ��(���Ϲ����)��Ȳ񤷤ޤ���(��������ޤ�)';return true;"
                            onMouseout="status=''"
                            title='��١����Υ��ץ������ʤ���ݶ��(���Ϲ����)��Ȳ񤷤ޤ���(��������ޤ�)'
                        >
                            ����������(������)
                        </a>
                    </td>
                    <td nowrap class='winbox' align='center' bgcolor='#d6d3ce'>
                        <a href='javascript:function(){}'
                            target='_self' style='text-decoration:none;'
                            onClick='javaScript:monthly_send("payable_ctoku_vendor_summary2"); return false;'
                            onMouseover="status='��١����Υ��ץ������ʤ���ݶ��(���Ϲ�����ι�׶��)��Ȳ񤷤ޤ���(��������ޤ�)';return true;"
                            onMouseout="status=''"
                            title='��١����Υ��ץ������ʤ���ݶ��(���Ϲ�����ι�׶��)��Ȳ񤷤ޤ���(��������ޤ�)'
                        >
                            ������ ������ ���(������)
                        </a>
                    </td>
                    <td nowrap class='winbox' align='center' bgcolor='#d6d3ce'>
                        <a href='javascript:function(){}'
                            target='_self' style='text-decoration:none;'
                            onClick='javaScript:monthly_send("payable_linear_vendor_summary2"); return false;'
                            onMouseover="status='��١����Υ�˥�����ݶ��(���Ϲ�����ι�׶��)��Ȳ񤷤ޤ���(��������ޤ�)';return true;"
                            onMouseout="status=''"
                            title='��١����Υ�˥�����ݶ��(���Ϲ�����ι�׶��)��Ȳ񤷤ޤ���(��������ޤ�)'
                        >
                            ��˥� ������ ���(������)
                        </a>
                    </td>
                    <td nowrap class='winbox' align='center' bgcolor='#d6d3ce'>
                        <a href='javascript:function(){}'
                            target='_self' style='text-decoration:none;'
                            onClick='location.replace("<?php echo $menu->out_action('Ǽ��ͽ���۾Ȳ�')?>"); return false;'
                            onMouseover="status='���ʤκ߸˷���ξȲ��Ԥ��ޤ���';return true;"
                            onMouseout="status=''"
                            title='���ʤκ߸˷���ξȲ��Ԥ��ޤ���'
                        >
                            Ǽ��ͽ���۾Ȳ�
                        </a>
                    </td>
                </tr>
            </table>
            </td></tr>
        </table> <!-- ���ߡ�End -->
        </form>
        
        <hr color='797979' width='95%'>
        
        <table bgcolor='#d6d3ce'  border='1' cellspacing='0' cellpadding='3'>
            <tr><td> <!-- ���ߡ�(�ǥ�������) -->
            <table class='winbox_field' width='100%' border='1' cellspacing='0' cellpadding='3'>
                <tr>
                    <td nowrap width='810' class='winbox' colspan='4' align='center' style='font-size:11pt; font-weight:bold; background-color:#ffffa6; color:blue;'>
                        �ޥ������Ȳ��Խ���˥塼
                    </td>
                </tr>
                <tr>
                    <td nowrap class='winbox' align='center' bgcolor='#d6d3ce' width='170' nowrap>
                        <a href='javascript:function(){}'
                            target='_self' style='text-decoration:none;'
                            onClick='location.replace("<?php echo $menu->out_action('�����ƥ�ޥ�����')?>"); return false;'
                            onMouseover="status='���ʡ����ʤΥ����ƥ�ޥ������ξȲ��Խ���Ԥ��ޤ���';return true;"
                            onMouseout="status=''"
                            title='���ʡ����ʤΥ����ƥ�ޥ������ξȲ��Խ���Ԥ��ޤ���'
                        >���ʡ����ʤΥ����ƥ�</a>
                    </td>
                    <td nowrap class='winbox' align='center' bgcolor='#d6d3ce' width='170' nowrap>
                        <a href='javascript:function(){}'
                            target='_self' style='text-decoration:none;'
                            onClick='location.replace("ind_branch.php?ind_name=vendor_master_view"); return false;'
                            onMouseover="status='ȯ����ޥ������ξȲ��Խ���Ԥ��ޤ���(���ߤ��Խ��Ͻ���ޤ���)';return true;"
                            onMouseout="status=''"
                            title='ȯ����ޥ������ξȲ��Խ���Ԥ��ޤ���(���ߤ��Խ��Ͻ���ޤ���)'
                        >ȯ����ޥ������Ȳ�</a>
                    </td>
                    <td nowrap class='winbox' align='center' bgcolor='#d6d3ce' width='170' nowrap>
                        <a href='javascript:function(){}'
                            target='_self' style='text-decoration:none;'
                            onClick='location.replace("<?php echo $menu->out_action('��̾�ˤ���ֹ渡��')?>"); return false;'
                            onMouseover="status='���ʡ����ʤΥ����ƥ����̾�ˤ����ʸ������Ԥ��ޤ���';return true;"
                            onMouseout="status=''"
                            title='���ʡ����ʤΥ����ƥ����̾�ˤ����ʸ������Ԥ��ޤ���'
                        >��̾�ˤ��ޥ�������</a>
                    </td>
                    <td nowrap class='winbox' align='center' bgcolor='#d6d3ce' width='170' nowrap>
                        <a href='javascript:function(){}'
                            target='_self' style='text-decoration:none;'
                            onClick='location.replace("<?php echo $menu->out_action('���ʥ��롼�ץ�����')?>"); return false;'
                            onMouseover="status='���ʥ��롼�ץ����ɤΥޥ�������Ȳ���Ͽ���ޤ���';return true;"
                            onMouseout="status=''"
                            title='���ʥ��롼�ץ����ɤΥޥ�������Ȳ���Ͽ���ޤ���'
                        >���ʥ��롼�ץ�����</a>
                    </td>
                <tr>
                    <td nowrap class='winbox' align='center' bgcolor='#d6d3ce' width='170' nowrap>
                        &nbsp;
                    </td>
                    <td nowrap class='winbox' align='center' bgcolor='#d6d3ce' width='170' nowrap>
                        &nbsp;
                    </td>
                    <td nowrap class='winbox' align='center' bgcolor='#d6d3ce' width='170' nowrap>
                        &nbsp;
                    </td>
                    </td>
                    <td nowrap class='winbox' align='center' bgcolor='#d6d3ce' width='170' nowrap>
                        &nbsp;
                    </td>
                </tr>
                </tr>
            </table>
            </td></tr>
        </table> <!-- ���ߡ�End -->
        
        <hr color='797979' width='95%'>
        
        <table bgcolor='#d6d3ce'  border='1' cellspacing='0' cellpadding='3'>
            <tr><td> <!-- ���ߡ�(�ǥ�������) -->
            <table class='winbox_field' width='100%' border='1' cellspacing='0' cellpadding='3'>
                <tr>
                    <td nowrap width='810' class='winbox' colspan='5' align='center' style='font-size:11pt; font-weight:bold; background-color:#ffffa6; color:blue;'>
                        ȯ����������ࡦ��Ω��˥塼
                    </td>
                </tr>
                <tr>
                    <td nowrap class='winbox' align='center' bgcolor='#d6d3ce'>
                        <a href='javascript:function(){}'
                            target='_self' style='text-decoration:none;'
                            onClick='location.replace("<?php echo $menu->out_action('Ǽ��ͽ���̤��������')?>"); return false;'
                            onMouseover="status='���ʤ�Ǽ��ͽ��(�������ʤ�ޤ�)��Ǽ���٤졢�ڤӸ����ų����٤�Ȳ񤷤ޤ���';return true;"
                            onMouseout="status=''"
                            title='���ʤ�Ǽ��ͽ��(�������ʤ�ޤ�)��Ǽ���٤졢�ڤӸ����ų����٤�Ȳ񤷤ޤ���'
                        >
                            Ǽ��ͽ��ȸ����ų�����
                        </a>
                    </td>
                    <td nowrap class='winbox' align='center' bgcolor='#d6d3ce'>
                        <a href='javascript:function(){}'
                            target='_self' style='text-decoration:none;'
                            onClick='location.replace("order/order_branch.php?script_name=<?php echo $menu->out_action('��������')?>"); return false;'
                            onMouseover="status='���ʤθ����ų�������Ǽ��ͽ���ʤ��Ф��Ƹ��������Ԥ��ޤ���';return true;"
                            onMouseout="status=''"
                            title='���ʤθ����ų�������Ǽ��ͽ���ʤ��Ф��Ƹ��������Ԥ��ޤ���'
                        >
                            ��������ꥹ��
                        </a>
                    </td>
                    <td nowrap class='winbox' align='center' bgcolor='#d6d3ce'>
                        <a href='javascript:function(){}'
                            target='_self' style='text-decoration:none;'
                            onClick='location.replace("<?php echo $menu->out_action('ȯ�����Υ���')?>"); return false;'
                            onMouseover="status='���ʤ�ȯ�����Υ��ƥʥ󥹤�Ԥ��ޤ���';return true;"
                            onMouseout="status=''"
                            title='���ʤ�ȯ�����Υ��ƥʥ󥹤�Ԥ��ޤ���'
                        >
                        ȯ�����Υ���
                        </a>
                    </td>
                    <td nowrap class='winbox' align='center' bgcolor='#d6d3ce'>
                        <a href='javascript:function(){}'
                            target='_blank' style='text-decoration:none;'
                            onClick='location.replace("<?php echo $menu->out_action('����������������')?>"); return false;'
                            onMouseover="status='�����ץ��Ѥο������������Τ������Уģƽ���(����)���ޤ���';return true;"
                            onMouseout="status=''"
                            title='�����ץ��Ѥο������������Τ������Уģƽ���(����)���ޤ���'
                        >
                            ����������������
                        </a>
                    </td>
                    <td nowrap class='winbox' align='center' bgcolor='#d6d3ce'>
                        <a href='javascript:function(){}'
                            target='_blank' style='text-decoration:none;'
                            onClick='location.replace("<?php echo $menu->out_action('������������ɸ��')?>"); return false;'
                            onMouseover="status='ɸ�५�ץ��Ѥο������������Τ������Уģƽ���(����)���ޤ���';return true;"
                            onMouseout="status=''"
                            title='ɸ�५�ץ��Ѥο������������Τ������Уģƽ���(����)���ޤ���'
                        >
                            ������������ɸ��
                        </a>
                    </td>
                </tr>
                <tr>
                    <td nowrap class='winbox' align='center' bgcolor='#d6d3ce'>
                        <a href='javascript:function(){}'
                            target='_self' style='text-decoration:none;'
                            onClick='location.replace("<?php echo $menu->out_action('���Ϲ�������ĥꥹ��')?>"); return false;'
                            onMouseover="status='���ꤵ�줿������ĥꥹ�Ȥ�ݥåץ��å�ɽ�����ޤ���';return true;"
                            onMouseout="status=''"
                            title='���ꤵ�줿������ĥꥹ�Ȥ�ݥåץ��å�ɽ�����ޤ���'
                        >
                            ���Ϲ�������ĥꥹ��
                        </a>
                    </td>
                    <td nowrap class='winbox' align='center' bgcolor='#d6d3ce'>
                        <a href='javascript:function(){}'
                            target='_self' style='text-decoration:none;'
                            onClick='location.replace("<?php echo $menu->out_action('��Ω�����Խ�')?>"); return false;'
                            onMouseover="status='��Ω���ӤξȲ�ڤ��ɲá�������Ԥ��ޤ���';return true;"
                            onMouseout="status=''"
                            title='��Ω���ӤξȲ�ڤ��ɲá�������Ԥ��ޤ���'
                        >
                            ��Ω���ӥǡ������Խ�
                        </a>
                    </td>
                    <td nowrap class='winbox' align='center' bgcolor='#d6d3ce'>
                        <a href='javascript:function(){}'
                            target='_self' style='text-decoration:none;'
                            onClick='location.replace("<?php echo $menu->out_action('��Ω�����Ȳ�')?>"); return false;'
                            onMouseover="status='��Ω����ꡦ��λ�ξȲ��Ԥ��ޤ���';return true;"
                            onMouseout="status=''"
                            title='��Ω����ꡦ��λ�ξȲ��Ԥ��ޤ���'
                        >
                            ��Ω�����ξȲ�
                        </a>
                    </td>
                    <td nowrap class='winbox' align='center' bgcolor='#d6d3ce'>
                        <a href='javascript:function(){}'
                            target='_self' style='text-decoration:none;'
                            onClick='location.replace("<?php echo $menu->out_action('��Ω�ؼ�')?>"); return false;'
                            onMouseover="status='��Ω�ؼ���˥塼 ���ϻؼ� �ڤ� ��λ�ؼ� ��Ԥ��ޤ���';return true;"
                            onMouseout="status=''"
                            title='��Ω�ؼ���˥塼 ���ϻؼ� �ڤ� ��λ�ؼ� ��Ԥ��ޤ���'
                        >
                            ��Ω�ؼ���˥塼
                        </a>
                    </td>
                    <td nowrap class='winbox' align='center' bgcolor='#d6d3ce'>
                        <a href='javascript:function(){}'
                            target='_self' style='text-decoration:none;'
                            onClick='location.replace("<?php echo $menu->out_action('������ʽи�')?>"); return false;'
                            onMouseover="status='����������Ω�����ʽи� ���ϻؼ� �ڤ� ��λ�ؼ� ��Ԥ��ޤ���';return true;"
                            onMouseout="status=''"
                            title='����������Ω�����ʽи� ���ϻؼ� �ڤ� ��λ�ؼ� ��Ԥ��ޤ���'
                        >
                            ������ʽи˥�˥塼
                        </a>
                    </td>
                </tr>
                <tr>
                    <td nowrap class='winbox' align='center' bgcolor='#d6d3ce'>
                        <a href='javascript:function(){}'
                            target='_self' style='text-decoration:none;'
                            onClick='location.replace("<?php echo $menu->out_action('�����ײ�Ȳ�')?>"); return false;'
                            onMouseover="status='������(AS/400)����Ω�����ײ�ɽ�ξȲ��Ԥ��ޤ���';return true;"
                            onMouseout="status=''"
                            title='������(AS/400)����Ω�����ײ�ɽ�ξȲ��Ԥ��ޤ���'
                        >
                            ��Ω�����ײ�ξȲ�
                        </a>
                    </td>
                    <td nowrap class='winbox' align='center' bgcolor='#d6d3ce'>
                        <a href='javascript:function(){}'
                            target='_self' style='text-decoration:none;'
                            onClick='location.replace("<?php echo $menu->out_action('���ӹ����Ȳ�')?>"); return false;'
                            onMouseover="status='��Ω����Ͽ�����ȼºݤι�������ӾȲ񤷤ޤ���';return true;"
                            onMouseout="status=''"
                            title='��Ω����Ͽ�����ȼºݤι�������ӾȲ񤷤ޤ���'
                        >
                            ���ӹ����ξȲ�
                        </a>
                    </td>
                    <td nowrap class='winbox' align='center' bgcolor='#d6d3ce'>
                        <a href='javascript:function(){}'
                            target='_self' style='text-decoration:none;'
                            onClick='location.replace("<?php echo $menu->out_action('������������')?>"); return false;'
                            onMouseover="status='��Ω�δ������������ӹ�������Ͽ��������� �Ȳ񤷤ޤ���';return true;"
                            onMouseout="status=''"
                            title='��Ω�δ������������ӹ�������Ͽ��������� �Ȳ񤷤ޤ���'
                        >
                            ��Ω�����������
                        </a>
                    </td>
                    <td nowrap class='winbox' align='center' bgcolor='#d6d3ce'>
                        <a href='javascript:function(){}'
                            target='_self' style='text-decoration:none;'
                            onClick='location.replace("<?php echo $menu->out_action('�饤���̹��������')?>"); return false;'
                            onMouseover="status='��Ω�Υ饤���� ���� ����� �Ȳ񤷤ޤ���';return true;"
                            onMouseout="status=''"
                            title='��Ω�Υ饤���� ���� ����� �Ȳ񤷤ޤ���'
                        >
                            �饤���̹��������
                        </a>
                    </td>
                    <td nowrap class='winbox' colspan='1' align='center' bgcolor='#d6d3ce'>
                        <a href='javascript:function(){}'
                            target='_self' style='text-decoration:none;'
                            onClick='location.replace("ind_branch.php?ind_name=datasum_barcode"); return false;'
                            onMouseover="status='�ǡ�������ΥС������ɥ����ɤ�Ŀ���˺������ޤ���';return true;"
                            onMouseout="status=''"
                            title='�ǡ�������ΥС������ɥ����ɤ�Ŀ���˺������ޤ���'
                        >
                            �С������ɺ���
                        </a>
                    </td>
                </tr>
                <tr>
                    <td nowrap class='winbox' align='center' bgcolor='#d6d3ce'>
                        <a href='javascript:function(){}'
                            target='_self' style='text-decoration:none;'
                            onClick='location.replace("<?php echo $menu->out_action('�饤�󥫥�����')?>"); return false;'
                            onMouseover="status='��Ω�饤��Υ��������ˤ�륹�����塼����Խ���Ԥ��ޤ���';return true;"
                            onMouseout="status=''"
                            title='��Ω�饤��Υ��������ˤ�륹�����塼����Խ���Ԥ��ޤ���'
                        >
                            �饤�󥫥������Խ�
                        </a>
                    </td>
                    <td nowrap class='winbox' align='center' bgcolor='#d6d3ce'>
                        <a href='javascript:function(){}'
                            target='_self' style='text-decoration:none;'
                            onClick='location.replace("<?php echo $menu->out_action('������������ƥ�')?>"); return false;'
                            onMouseover="status='������������ƥ� ��˥塼�ؿʤߤޤ����ޥ������Խ����������߽д�������Ԥ��ޤ���';return true;"
                            onMouseout="status=''"
                            title='������������ƥ� ��˥塼�ؿʤߤޤ����ޥ������Խ����������߽д�������Ԥ��ޤ���'
                        >
                            ������������ƥ�
                        </a>
                    </td>
                    <td nowrap class='winbox' align='center' bgcolor='#d6d3ce'>
                        <a href='javascript:function(){}'
                            target='_self' style='text-decoration:none;'
                            onClick='location.replace("<?php echo $menu->out_action('�������ӽ����')?>"); return false;'
                            onMouseover="status='�����ץ�δ����ʸ������ӽ�ΰ�����ײ��ֹ��С������ɤ����Ϥ�����ˤ��Ԥ��ޤ���';return true;"
                            onMouseout="status=''"
                            title='�����ץ�δ����ʸ������ӽ�ΰ�����ײ��ֹ��С������ɤ����Ϥ�����ˤ��Ԥ��ޤ���'
                        >
                            �������ӽ����
                        </a>
                    </td>
                    <td nowrap class='winbox' align='center' bgcolor='#d6d3ce'>
                        <a href='javascript:function(){}'
                            target='_self' style='text-decoration:none;'
                            onClick='location.replace("<?php echo $menu->out_action('���֤���ʸ����')?>"); return false;'
                            onMouseover="status='��ԡ�������ȯ���¿����˽��פ�Ԥ��ޤ���';return true;"
                            onMouseout="status=''"
                            title='��ԡ�������ȯ���¿����˽��פ�Ԥ��ޤ���'
                        >
                            ��ԡ���ȯ��ν���
                        </a>
                    </td>
                    <td nowrap class='winbox' align='center' bgcolor='#d6d3ce'>
                        <a href='javascript:function(){}'
                            target='_self' style='text-decoration:none;'
                            onClick='location.replace("<?php echo $menu->out_action('��˥����ʽи�')?>"); return false;'
                            onMouseover="status='��˥�������Ѥ���Ω�����ʽи� ���ϻؼ� �ڤ� ��λ�ؼ� ��Ԥ��ޤ���';return true;"
                            onMouseout="status=''"
                            title='��˥�������Ѥ���Ω�����ʽи� ���ϻؼ� �ڤ� ��λ�ؼ� ��Ԥ��ޤ���'
                        >
                            ��˥��и˥�˥塼
                        </a>
                    </td>
                </tr>
                <tr>
                    <td nowrap class='winbox' align='center' bgcolor='#d6d3ce'>
                        <a href='javascript:function(){}'
                            target='_self' style='text-decoration:none;'
                            onClick='location.replace("<?php echo $menu->out_action('Ǽ���٤����ʤξȲ�')?>"); return false;'
                            onMouseover="status='Ǽ���٤줬ȯ���������ʤ�Ȳ񤷤ޤ���';return true;"
                            onMouseout="status=''"
                            title='Ǽ���٤줬ȯ���������ʤ�Ȳ񤷤ޤ���'
                        >
                            Ǽ���٤����ʤξȲ�
                        </a>
                    </td>
                    <td nowrap class='winbox' align='center' bgcolor='#d6d3ce'>
                        <a href='javascript:function(){}'
                            target='_self' style='text-decoration:none;'
                            onClick='location.replace("<?php echo $menu->out_action('�����ץ�������������˥塼')?>"); return false;'
                            onMouseover="status='�����ץ����Ŭ��Ϣ���ξȲ����Ω�깩����ˡ��Ȳ񤷤ޤ���';return true;"
                            onMouseout="status=''"
                            title='�����ץ����Ŭ��Ϣ���ξȲ����Ω�깩����ˡ��Ȳ񤷤ޤ���'
                        >
                            �����ץ���񡦺�������
                        </a>
                    </td>
                    <?php
                    if ($_SESSION['User_ID'] == '300144') {
                    ?>
                    <td nowrap class='winbox' align='center' bgcolor='#d6d3ce'>
                        <a href='javascript:function(){}'
                            target='_self' style='text-decoration:none;'
                            onClick='location.replace("<?php echo $menu->out_action('������������')?>"); return false;'
                            onMouseover="status='�����������פ�Ȳ񤷤ޤ���';return true;"
                            onMouseout="status=''"
                            title='�����������פ�Ȳ񤷤ޤ���'
                        >
                            ������������
                        </a>
                    </td>
                    <?php
                     } else {
                      ?>
                    <td nowrap class='winbox' colspan='1' align='center' bgcolor='#d6d3ce'>
                        &nbsp;
                    </td>
                    <?php
                    }
                    ?>
                    <td nowrap class='winbox' align='center' bgcolor='#d6d3ce' width='170' nowrap>
                        <a href='javascript:function(){}'
                            target='_self' style='text-decoration:none;'
                            onClick='location.replace("<?php echo $menu->out_action('�о����ʽ��ץ�˥塼')?>"); return false;'
                            onMouseover="status='�б����ʴ�Ϣ�Υ�˥塼��ɽ�����ޤ���';return true;"
                            onMouseout="status=''"
                            title='�о����ʴ�Ϣ�Υ�˥塼��ɽ�����ޤ���'
                        >
                            �о����ʽ��ץ�˥塼
                        </a>
                    </td>
                    <td nowrap class='winbox' align='center' bgcolor='#d6d3ce' width='170' nowrap>
                        <a href='javascript:function(){}'
                            target='_self' style='text-decoration:none;'
                            onClick='location.replace("<?php echo $menu->out_action('�����ٱ��Ϣ��˥塼')?>"); return false;'
                            onMouseover="status='�����ٱ��Ϣ�Υ�˥塼��ɽ�����ޤ���';return true;"
                            onMouseout="status=''"
                            title='�����ٱ��Ϣ�Υ�˥塼��ɽ�����ޤ���'
                        >
                            �����ٱ��Ϣ��˥塼
                        </a>
                    </td>
                </tr>
                <tr>
                    <?php
                    if ($_SESSION['User_ID'] == '300667' || $_SESSION['User_ID'] == '300144') {
                    ?>
                    <td nowrap class='winbox' align='center' bgcolor='#d6d3ce'>
                        <a href='javascript:function(){}'
                            target='_self' style='text-decoration:none;'
                            onClick='location.replace("<?php echo $menu->out_action('�������������Խ�')?>"); return false;'
                            onMouseover="status='���������������Խ����ޤ���';return true;"
                            onMouseout="status=''"
                            title='���������������Խ����ޤ���'
                        >
                            ��Ω�����Խ�(����/���)
                        </a>
                    </td>
                    <?php
                    } else {
                      ?>
                    <td nowrap class='winbox' colspan='1' align='center' bgcolor='#d6d3ce'>
                        &nbsp;
                    </td>
                    <?php
                    }
                    ?>
                    <td nowrap class='winbox' colspan='1' align='center' bgcolor='#d6d3ce'>
                        &nbsp;
                    </td>
                    <td nowrap class='winbox' colspan='1' align='center' bgcolor='#d6d3ce'>
                        &nbsp;
                    </td>
                    <td nowrap class='winbox' colspan='1' align='center' bgcolor='#d6d3ce'>
                        &nbsp;
                    </td>
                    <td nowrap class='winbox' colspan='1' align='center' bgcolor='#d6d3ce'>
                        &nbsp;
                    </td>
                </tr>
            </table>
            </td></tr>
        </table> <!-- ���ߡ�End -->
        
        <hr color='797979' width='95%'>
        <?php
        }
        ?>
        
    </center>
</body>
<?php echo $menu->out_alert_java()?>
</html>
<?php
ob_end_flush();                 // ���ϥХåե���gzip���� END
?>
