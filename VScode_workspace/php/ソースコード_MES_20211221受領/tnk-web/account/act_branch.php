<?php
//////////////////////////////////////////////////////////////////////////////
// ��������ط������� Branch (ʬ��)����                                     //
// Copyright (C) 2003-2017 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp      //
// Changed history                                                          //
// 2003/11/17 Created   act_branch.php                                      //
//            ����Ū�˷�������ط��ν�����Ԥ�������Ū�ˤϷ�����˥塼��    //
// 2003/11/27 tnk-turbine.gif �Υ��˥᡼�������ɲ�                        //
// 2003/12/04 ���å������о�ǯ�̵���������򻻽Ф������ꤹ�롣        //
// 2004/04/05 JavaScript�� "Location = 'http:" . WEB_HOST . "$script_name"  //
//               -->   "Location = '" . H_WEB_HOST . ACT . "$script_name'"  //
// 2005/03/04 ê���Ȼ����������ȶ�ͭ��������$_SESSION['ind_ym']���ɲ�       //
// 2005/05/21 ê���׾������ inventory/ ��ȴ���Ƥ���Τ���                //
// 2010/11/11 �ƥ��������̤�ê��������Ӥ��ɲ�                       ��ë //
// 2015/05/21 �����������б�                                           ��ë //
// 2017/10/24 Ϣ�������ɽ���ɲ�                                     ��ë //
// 2017/11/10 ����������ê������ɲ�                                   ��ë //
//////////////////////////////////////////////////////////////////////////////
ini_set('error_reporting',E_ALL);   // E_ALL='2047' debug ��
// ini_set('display_errors','1');      // Error ɽ�� ON debug �� ��꡼���女����
session_start();                    // ini_set()�μ��˻��ꤹ�뤳�� Script �Ǿ��
require_once ("../function.php");
require_once ("../tnk_func.php");
access_log();                       // Script Name �ϼ�ư����

////// ������� �о� ǯ��������¸
if (isset($_POST['act_ymd'])) {
    $_SESSION['act_ymd'] = $_POST['act_ymd'];   // �о�ǯ�����򥻥å�������¸
} elseif (isset($_GET['act_ymd'])) {
    $_SESSION['act_ymd'] = $_GET['act_ymd'];    // �о�ǯ�����򥻥å�������¸
} else {
    if (!isset($_SESSION['act_ymd'])) {
        $_SESSION['act_ymd'] = '';              // ���å�����ǯ������̵�����
    }
}
////// ��١������� �о� ǯ�����¸
if (isset($_POST['act_ym'])) {
    $_SESSION['act_ym'] = $_POST['act_ym'];     // �о�ǯ��򥻥å�������¸
    $_SESSION['ind_ym'] = $_POST['act_ym'];     // �о�ǯ��򥻥å�������¸
} elseif (isset($_GET['act_ym'])) {
    $_SESSION['act_ym'] = $_GET['act_ym'];      // �о�ǯ��򥻥å�������¸
    $_SESSION['ind_ym'] = $_GET['act_ym'];      // �о�ǯ��򥻥å�������¸
} else {
    if (!isset($_SESSION['act_ym'])) {
        ///// �о�����򻻽�
        $yyyymm = date('Ym');
        if (substr($yyyymm,4,2)!=01) {
            $p1_ym = $yyyymm - 1;
        } else {
            $p1_ym = $yyyymm - 100;
            $p1_ym = $p1_ym + 11;
        }
        $_SESSION['act_ym'] = $p1_ym;               // �о�ǯ��򥻥å�������¸
    }
}
////// �Ȳ��˥塼 �о� ǯ�����¸
if (isset($_POST['actv_ym'])) {
    $_SESSION['actv_ym'] = $_POST['actv_ym'];     // �о�ǯ��򥻥å�������¸
    $_SESSION['indv_ym'] = $_POST['actv_ym'];     // �о�ǯ��򥻥å�������¸
} elseif (isset($_GET['actv_ym'])) {
    $_SESSION['actv_ym'] = $_GET['actv_ym'];      // �о�ǯ��򥻥å�������¸
    $_SESSION['indv_ym'] = $_GET['actv_ym'];      // �о�ǯ��򥻥å�������¸
} else {
    if (!isset($_SESSION['actv_ym'])) {
        ///// �о�����򻻽�
        $yyyymm = date('Ym');
        if (substr($yyyymm,4,2)!=01) {
            $p1_ym = $yyyymm - 1;
        } else {
            $p1_ym = $yyyymm - 100;
            $p1_ym = $p1_ym + 11;
        }
        $_SESSION['actv_ym'] = $p1_ym;               // �о�ǯ��򥻥å�������¸
    }
}
////// �ƽи�����¸
$_SESSION['act_referer'] = 'http:' . WEB_HOST . 'account/act_menu.php';        // �ƽФ�Ȥ�URL�򥻥å�������¸
// $_SESSION['act_referer'] = $_SERVER['HTTP_REFERER'];     // �ƽФ�Ȥ�URL�򥻥å�������¸
$act_referer = $_SESSION['act_referer'];

////////////// ǧ�ڥ����å�
if (!isset($_SESSION["User_ID"]) || !isset($_SESSION["Password"]) || !isset($_SESSION["Auth"])) {
// if (account_group_check2() == FALSE) {
// if (account_group_check() == FALSE) {
    $_SESSION['s_sysmsg'] = "���ʤ��ϵ��Ĥ���Ƥ��ޤ���<br>�����Ԥ�Ϣ���Ʋ�������";
    header('Location: ' . $act_referer);
    // header('Location: ' . $_SERVER['HTTP_REFERER']);
    exit();
}

////////// �оݥ�����ץȤμ���
if (isset($_POST['act_name'])) {
    $act_name = $_POST['act_name'];
} elseif (isset($_GET['act_name'])) {
    $act_name = $_GET['act_name'];
    // $_SESSION['s_sysmsg'] = $_GET['act_name'];   // Debug��
} else {
    $act_name = '';
}
switch ($act_name) {
    case '��ݶ�ι���'         : $script_name = 'act_payable_get_ftp.php'      ; break;
    case '��ݶ�Υ����å�'     : $script_name = 'act_payable_view.php'         ; break;
    case 'act_payable_view'     : $script_name = 'act_payable_view.php'         ; break;
    
    case '�ٵ�ɼ�ι���'         : $script_name = 'act_miprov_get_ftp.php'       ; break;
    case '�ٵ�ɼ�Υ����å�'     : $script_name = 'act_miprov_view.php'          ; break;
    case 'act_miprov_view'      : $script_name = 'act_miprov_view.php'          ; break;
    
    case 'ȯ��ײ�ι���'       : $script_name = 'order_plan_get_ftp.php'       ; break;
    case 'order_plan_update'    : $script_name = 'order_plan_get_ftp.php'       ; break;
    case 'ȯ��ײ�Υ����å�'   : $script_name = 'order_plan_view.php'          ; break;
    case 'order_plan_view'      : $script_name = 'order_plan_view.php'          ; break;
    
    case 'ê���ǡ����ι���'         : $script_name = 'inventory/inventory_month_update.php'     ; break;
    case 'inventory_month_update'   : $script_name = 'inventory/inventory_month_update.php'     ; break;
    case 'ê���ǡ����Υ����å�'     : $script_name = 'inventory/inventory_month_view.php'       ; break;
    case 'inventory_month_view'     : $script_name = 'inventory/inventory_month_view.php'       ; break;
    
    case '����ٵ��ʤι���'         : $script_name = 'provide_month_update.php'     ; break;
    case 'provide_month_update'     : $script_name = 'provide_month_update.php'     ; break;
    case '����ٵ��ʤΥ����å�'     : $script_name = 'provide_month_view.php'       ; break;
    case 'provide_month_view'       : $script_name = 'provide_month_view.php'       ; break;
    
    case 'ȯ����ޥ���������'       : $script_name = 'vendor_master_update.php'     ; break;
    case 'vendor_master_update'     : $script_name = 'vendor_master_update.php'     ; break;
    case 'ȯ����ޥ����������å�'   : $script_name = 'vendor_master_view.php'       ; break;
    case 'vendor_master_view'       : $script_name = 'vendor_master_view.php'       ; break;
    
    case 'ô���ԥޥ���������'           : $script_name = 'vendor_person_master_update.php'     ; break;
    case 'vendor_person_master_update'  : $script_name = 'vendor_person_master_update.php'     ; break;
    case 'ô���ԥޥ����������å�'       : $script_name = 'vendor_person_master_view.php'       ; break;
    case 'vendor_person_master_view'    : $script_name = 'vendor_person_master_view.php'       ; break;
    
    case '������ۤξȲ�'       : $script_name = 'act_purchase_view.php'        ; break;
    case 'act_purchase_view'    : $script_name = 'act_purchase_view.php'        ; break;
    case '�����׾����'         : $script_name = 'act_purchase_update.php'      ; break;
    case 'act_purchase_update'  : $script_name = 'act_purchase_update.php'      ; break;
    
    case '������ê�����'               : $script_name = 'inventory/inventory_monthly_ctoku_view.php'   ; break;
    case 'inventory_month_ctoku_view'   : $script_name = 'inventory/inventory_monthly_ctoku_view.php'   ; break;
    case '������ê������'                   : $script_name = 'inventory_month_ctoku_zen_view.php'   ; break;
    case 'inventory_month_ctoku_zen_view'   : $script_name = 'inventory_month_ctoku_zen_view.php'   ; break;
    
    case '�Х����ê�����'             : $script_name = 'inventory/inventory_month_bimor_view.php'   ; break;
    case 'inventory_month_bimor_view'   : $script_name = 'inventory/inventory_month_bimor_view.php'   ; break;
    
    case '�ġ���ê�����'             : $script_name = 'inventory/inventory_month_tool_view.php'   ; break;
    case 'inventory_month_tool_view'   : $script_name = 'inventory/inventory_month_tool_view.php'   ; break;
    
    case '���ץ�ê�����'           : $script_name = 'inventory/inventory_month_c_view.php'   ; break;
    case 'inventory_month_c_view'   : $script_name = 'inventory/inventory_month_c_view.php'   ; break;
    
    case '��˥�ê�����'           : $script_name = 'inventory/inventory_month_l_view.php'   ; break;
    case 'inventory_month_l_view'   : $script_name = 'inventory/inventory_month_l_view.php'   ; break;
    
    case '��������ι���'       : $script_name = 'aden_master_update.php'     ; break;
    case 'aden_master_update'   : $script_name = 'aden_master_update.php'     ; break;
    case '��������ξȲ�'       : $script_name = 'aden_master_view.php'       ; break;
    case 'aden_master_view'     : $script_name = 'aden_master_view.php'       ; break;
    
    case '������ ê����� �׾����'          : $script_name = 'inventory/inventory_monthly_header_update.php'     ; break;
    case 'inventory_monthly_header_update'   : $script_name = 'inventory/inventory_monthly_header_update.php'     ; break;
    
    case '��ʿ��ê�����'           : $script_name = 'inventory/inventory_month_view_average.php'   ; break;
    case 'inventory_month_view_average'   : $script_name = 'inventory/inventory_month_view_average.php'   ; break;
    
    case '���ץ���ʿ��ê�����'           : $script_name = 'inventory/inventory_month_c_view_average.php'   ; break;
    case 'inventory_month_c_view_average'   : $script_name = 'inventory/inventory_month_c_view_average.php'   ; break;
    
    case '��˥���ʿ��ê�����'           : $script_name = 'inventory/inventory_month_l_view_average.php'   ; break;
    case 'inventory_month_l_view_average'   : $script_name = 'inventory/inventory_month_l_view_average.php'   ; break;
    
    case '��������ʿ��ê�����'               : $script_name = 'inventory/inventory_monthly_ctoku_view_average.php'   ; break;
    case 'inventory_month_ctoku_view_average'   : $script_name = 'inventory/inventory_monthly_ctoku_view_average.php'   ; break;
    
    case '��������ʿ��ê���������'               : $script_name = 'inventory/inventory_monthly_ctoku_view_average_allo.php'   ; break;
    case 'inventory_month_ctoku_view_average_allo'   : $script_name = 'inventory/inventory_monthly_ctoku_view_average_allo.php'   ; break;
    
    case '�Х������ʿ��ê�����'             : $script_name = 'inventory/inventory_month_bimor_view_average.php'   ; break;
    case 'inventory_month_bimor_view_average'   : $script_name = 'inventory/inventory_month_bimor_view_average.php'   ; break;
    
    case '�ġ�����ʿ��ê�����'             : $script_name = 'inventory/inventory_month_tool_view_average.php'   ; break;
    case 'inventory_month_tool_view_average'   : $script_name = 'inventory/inventory_month_tool_view_average.php'   ; break;
    
    case '��ʿ��ê��������'           : $script_name = 'inventory/inventory_month_compare.php'   ; break;
    case 'inventory_month_compare'   : $script_name = 'inventory/inventory_month_compare.php'   ; break;
    
    case '���ץ���ʿ��ê��������'           : $script_name = 'inventory/inventory_month_c_compare.php'   ; break;
    case 'inventory_month_c_compare'   : $script_name = 'inventory/inventory_month_c_compare.php'   ; break;
    
    case '��˥���ʿ��ê��������'           : $script_name = 'inventory/inventory_month_l_compare.php'   ; break;
    case 'inventory_month_l_compare'   : $script_name = 'inventory/inventory_month_l_compare.php'   ; break;
    
    case '��������ʿ��ê��������'           : $script_name = 'inventory/inventory_month_ctoku_compare.php'   ; break;
    case 'inventory_month_ctoku_compare'   : $script_name = 'inventory/inventory_month_ctoku_compare.php'   ; break;
    
    case '�Х������ʿ��ê��������'           : $script_name = 'inventory/inventory_month_bimor_compare.php'   ; break;
    case 'inventory_month_bimor_compare'   : $script_name = 'inventory/inventory_month_bimor_compare.php'   ; break;
    
    case '�ġ�����ʿ��ê��������'           : $script_name = 'inventory/inventory_month_tool_compare.php'   ; break;
    case 'inventory_month_tool_compare'   : $script_name = 'inventory/inventory_month_tool_compare.php'   ; break;
    
    case 'Ϣ�������ɽ'           : $script_name = 'link_trans/link_trans_menu.php'   ; break;
    case 'link_trans'   : $script_name = 'link_trans/link_trans_menu.php'   ; break;
    
    default: $script_name = 'act_menu.php';              // �ƽФ�Ȥص���
             $url_name    = $act_referer;       // �ƽФ�Ȥ�URL �̥�˥塼����ƤӽФ��줿�����б�
}
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=EUC-JP">
<meta http-equiv="Content-Style-Type" content="text/css">
<title>�������� ʬ������</title>
<style type="text/css">
<!--
body {
    margin:     20%;
    font-size:  24pt;
}
-->
</style>
<form name='branch_form' method='post' action='<?php if (isset($url_name)) echo $url_name; else echo $script_name; ?>'>
</form>
</head>
<body onLoad='document.branch_form.submit()'>
    <center>
        ������Ǥ������Ԥ���������<br>
        <img src='../img/tnk-turbine.gif' width=68 height=72>
    </center>
</body>
</html>
