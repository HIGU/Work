<?php
//////////////////////////////////////////////////////////////////////////////
// ���� �ط� ������ Branch (ʬ��)����                                       //
// Copyright(C) 2003-2004 2007 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp  //
// Changed history                                                          //
// 2003/11/29 Created   ind_branch.php                                      //
// 2003/12/04 ���å������о�ǯ�̵���������򻻽Ф������ꤹ�롣        //
// 2003/12/15 ����������Ͽ�ɲäȥǥ��쥯�ȥ������define.php �������     //
// 2004/04/07 ASSY�ֹ�ˤ���������ξȲ���ɲ�                            //
// 2004/12/07 ��ݴط��Υץ����� industry/payable �ذ�ư                //
// 2004/12/22 �������ط��Υץ����� industry/material �ذ�ư           //
// 2007/09/05 payable_linear_vendor_summary2 ���ɲ�                         //
//////////////////////////////////////////////////////////////////////////////
ini_set('error_reporting', E_ALL);          // E_ALL='2047' debug ��
// ini_set('display_errors', '1');            // Error ɽ�� ON debug �� ��꡼���女����
ob_start('ob_gzhandler');                   // ���ϥХåե���gzip����
session_start();                            // ini_set()�μ��˻��ꤹ�뤳�� Script �Ǿ��
require_once ('../function.php');
require_once ('../tnk_func.php');
access_log();                               // Script Name �ϼ�ư����

////// ������� �о� ǯ��������¸
if (isset($_REQUEST['act_ymd'])) {
    $_SESSION['act_ymd'] = $_REQUEST['act_ymd'];    // �о�ǯ�����򥻥å�������¸
} else {
    if (!isset($_SESSION['act_ymd'])) {
        $_SESSION['act_ymd'] = '';                  // ���å�����ǯ������̵�����
    }
}
////// ��١������� �о� ǯ�����¸
if (isset($_REQUEST['act_ym'])) {
    $_SESSION['act_ym'] = $_REQUEST['act_ym'];      // �о�ǯ��򥻥å�������¸
} else {
    ///// �о�����򻻽�
    $yyyymm = date('Ym');
    if (substr($yyyymm,4,2)!=01) {
        $p1_ym = $yyyymm - 1;
    } else {
        $p1_ym = $yyyymm - 100;
        $p1_ym = $p1_ym + 11;
    }
    $_SESSION['act_ym'] = $p1_ym;                   // �о�ǯ��򥻥å�������¸
}
////// �ƽи�����¸
$_SESSION['act_referer'] = H_WEB_HOST . INDUST_MENU;        // �ƽФ�Ȥ�URL�򥻥å�������¸
// $_SESSION['act_referer'] = $_SERVER['HTTP_REFERER'];     // �ƽФ�Ȥ�URL�򥻥å�������¸
$act_referer = $_SESSION['act_referer'];

////////////// ǧ�ڥ����å�
//if (!isset($_SESSION["User_ID"]) || !isset($_SESSION["Password"]) || !isset($_SESSION["Auth"])) {
if (!isset($_SESSION["User_ID"]) || !isset($_SESSION["Password"])) {
// if (account_group_check2() == FALSE) {
// if (account_group_check() == FALSE) {
    $_SESSION['s_sysmsg'] = "���ʤ��ϵ��Ĥ���Ƥ��ޤ���<br>�����Ԥ�Ϣ���Ʋ�������";
    header('Location: ' . $act_referer);
    // header('Location: ' . $_SERVER['HTTP_REFERER']);
    exit();
}

////// ��١������� �о� ǯ�����¸
if (isset($_REQUEST['ind_ym'])) {
    $_SESSION['ind_ym'] = $_REQUEST['ind_ym'];  // �о�ǯ��򥻥å�������¸
} else {
    if (!isset($_SESSION['ind_ym'])) {
        ///// �о�����򻻽�
        $yyyymm = date('Ym');
        if (substr($yyyymm,4,2)!=01) {
            $p1_ym = $yyyymm - 1;
        } else {
            $p1_ym = $yyyymm - 100;
            $p1_ym = $p1_ym + 11;
        }
        $_SESSION['ind_ym'] = $p1_ym;               // �о�ǯ��򥻥å�������¸
    }
}
////////// �оݥ�����ץȤμ���
if (isset($_REQUEST['ind_name'])) {
    $ind_name = $_REQUEST['ind_name'];
} else {
    $ind_name = '';
}
switch ($ind_name) {
    case '��������ξȲ�'       : $script_name = INDUST . 'Aden/aden_master_view_form.php'    ; break;
    case 'aden_master_view'     : $script_name = INDUST . 'Aden/aden_master_view_form.php'    ; break;
    
    case '��ݼ��ӤξȲ�'       : $script_name = INDUST . 'payable/act_payable_form.php'         ; break;
    case 'act_payable_view'     : $script_name = INDUST . 'payable/act_payable_form.php'         ; break;
    
    case '�ٵ�ɼ�ξȲ�'         : $script_name = INDUST . 'act_miprov_view.php'          ; break;
    case 'act_miprov_view'      : $script_name = INDUST . 'act_miprov_view.php'          ; break;
    
    case 'ȯ��ײ�ξȲ�'       : $script_name = INDUST . 'order_plan_view.php'          ; break;
    case 'order_plan_view'      : $script_name = INDUST . 'order_plan_view.php'          ; break;
    
    case '���ץ�ê�����'           : $script_name = ACT . 'inventory/inventory_month_c_view.php'   ; break;
    case 'inventory_month_c_view'   : $script_name = ACT . 'inventory/inventory_month_c_view.php'   ; break;
    
    case '��˥�ê�����'           : $script_name = ACT . 'inventory/inventory_month_l_view.php'   ; break;
    case 'inventory_month_l_view'   : $script_name = ACT . 'inventory/inventory_month_l_view.php'   ; break;
    
    case 'ȯ����ޥ������Ȳ�'   : $script_name = INDUST . 'vendor_master_view.php'       ; break;
    case 'vendor_master_view'   : $script_name = INDUST . 'vendor_master_view.php'       ; break;
    
    case '������ۤξȲ�'       : $script_name = ACT . 'act_purchase_view.php'        ; break;
    case 'act_purchase_view'    : $script_name = ACT . 'act_purchase_view.php'        ; break;
    
    case '�Х����ê�����'             : $script_name = ACT . 'inventory/inventory_month_bimor_view.php'   ; break;
    case 'inventory_month_bimor_view'   : $script_name = ACT . 'inventory/inventory_month_bimor_view.php'   ; break;
    case '�ġ���ê�����'             : $script_name = ACT . 'inventory/inventory_month_tool_view.php'   ; break;
    case 'inventory_month_tool_view'   : $script_name = ACT . 'inventory/inventory_month_tool_view.php'   ; break;
    case '������ê�����'               : $script_name = ACT . 'inventory/inventory_monthly_ctoku_view.php'   ; break;
    case 'inventory_month_ctoku_view'   : $script_name = ACT . 'inventory/inventory_monthly_ctoku_view.php'   ; break;
    
    case 'datasum_barcode'   : $script_name = INDUST . 'BarCode/datasum_barcode.php'   ; break;
    
    case '������ê������'                   : $script_name = INDUST . 'inventory_month_ctoku_zen_view.php'   ; break;
    case 'inventory_month_ctoku_zen_view'   : $script_name = INDUST . 'inventory_month_ctoku_zen_view.php'   ; break;
    
    case '���ץ�������ݼ���'            : $script_name = INDUST . 'payable/payable_ctoku_view.php'             ; break;
    case 'payable_ctoku_view'            : $script_name = INDUST . 'payable/payable_ctoku_view.php'             ; break;
    case 'payable_ctoku_vendor_summary'  : $script_name = INDUST . 'payable/payable_ctoku_vendor_summary.php'   ; break;
    case 'payable_ctoku_view2'           : $script_name = INDUST . 'payable/payable_ctoku_view2.php'            ; break;
    case 'payable_ctoku_vendor_summary2' : $script_name = INDUST . 'payable/payable_ctoku_vendor_summary2.php'  ; break;
    case 'payable_cstd_vendor_summary2'  : $script_name = INDUST . 'payable/payable_cstd_vendor_summary2.php'   ; break;
    case 'payable_linear_vendor_summary2': $script_name = INDUST . 'payable/payable_linear_vendor_summary2.php' ; break;
    
    case 'ê���ǡ����ξȲ�'         : $script_name = INDUST . 'inventory_month_view.php'        ; break;
    case 'inventory_month_view'     : $script_name = INDUST . 'inventory_month_view.php'        ; break;
    
    case '����������Ͽ'           : $script_name = INDUST . 'materialCost_entry_plan.php'     ; break;
    case 'materialCost_entry_plan'  : $script_name = INDUST . 'materialCost_entry_plan.php'     ; break;
    case '�������ξȲ�(�ײ��ֹ�)' : $script_name = INDUST . 'materialCost_view_plan.php'      ; break;
    case 'materialCost_view_plan'   : $script_name = INDUST . 'materialCost_view_plan.php'      ; break;
    case '�������ξȲ�(ASSY�ֹ�)' : $script_name = INDUST . 'materialCost_view_assy.php'      ; break;
    case 'materialCost_view_assy'   : $script_name = INDUST . 'materialCost_view_assy.php'      ; break;
    case '���������������Ψ'   : $script_name = INDUST . 'materialCost_sales_comp.php'     ; break;
    case 'materialCost_sales_comp'  : $script_name = INDUST . 'materialCost_sales_comp.php'     ; break;
    case '��������̤��Ͽ�Ȳ�'         : $script_name = INDUST . 'material/materialCost_unregist_view.php'  ; break;
    case 'materialCost_unregist_view'   : $script_name = INDUST . 'material/materialCost_unregist_view.php'  ; break;
    
    case '�������̤�������پȲ�'   : $script_name = INDUST . 'sales_miken_view.php'  ; break;
    case 'sales_miken_view'         : $script_name = INDUST . 'sales_miken_view.php'  ; break;
    
    case '����ñ��������������'   : $script_name = INDUST . 'sales_material_comp_form.php'  ; break;
    case 'sales_material_comp_form' : $script_name = INDUST . 'sales_material_comp_form.php'  ; break;
    
    case 'ñ������Ȳ�'         : $script_name = INDUST . 'parts_cost_form.php'  ; break;
    case 'parts_cost_form'      : $script_name = INDUST . 'parts_cost_form.php'  ; break;
    
    case '�������ʹ���ɽ�ξȲ�' : $script_name = INDUST . 'allo_conf_parts_form.php'  ; break;
    case 'allo_conf_parts_form' : $script_name = INDUST . 'allo_conf_parts_form.php'  ; break;
    
    default: $script_name = INDUST . 'industry_menu.php';              // �ƽФ�Ȥص���
             $url_name    = $act_referer;       // �ƽФ�Ȥ�URL �̥�˥塼����ƤӽФ��줿�����б�
}
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<HTML>
<HEAD>
<META http-equiv="Content-Type" content="text/html; charset=EUC-JP">
<META http-equiv="Content-Style-Type" content="text/css">
<TITLE>������˥塼 ʬ������</TITLE>
<style type="text/css">
<!--
body        {margin:20%;font-size:24pt;}
-->
</style>
</HEAD>
<BODY>
    <center>
        ������Ǥ������Ԥ���������<br>
        <img src='../img/tnk-turbine.gif' width=68 height=72>
    </center>
</BODY>
</HTML>

<script language="JavaScript">
<!--
<?php
    if (isset($url_name)) {
        echo "location = '$url_name'";
    } else {
        echo "location = '" . H_WEB_HOST . "$script_name'";
    }
?>
// -->
</script>
