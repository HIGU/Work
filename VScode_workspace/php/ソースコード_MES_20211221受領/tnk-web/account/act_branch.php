<?php
//////////////////////////////////////////////////////////////////////////////
// 経理日報関係処理の Branch (分岐)処理                                     //
// Copyright (C) 2003-2017 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp      //
// Changed history                                                          //
// 2003/11/17 Created   act_branch.php                                      //
//            暫定的に経理日報関係の処理を行うが恒久的には経理メニューへ    //
// 2003/11/27 tnk-turbine.gif のアニメーションを追加                        //
// 2003/12/04 セッションに対象年月が無ければ前月を算出して設定する。        //
// 2004/04/05 JavaScriptの "Location = 'http:" . WEB_HOST . "$script_name"  //
//               -->   "Location = '" . H_WEB_HOST . ACT . "$script_name'"  //
// 2005/03/04 棚卸と仕入を生産と共有したため$_SESSION['ind_ym']を追加       //
// 2005/05/21 棚卸計上処理で inventory/ が抜けているのを修正                //
// 2010/11/11 各セグメント別の棚卸増減比較を追加                       大谷 //
// 2015/05/21 機工生産に対応                                           大谷 //
// 2017/10/24 連結取引総括表を追加                                     大谷 //
// 2017/11/10 特注品配賦棚卸高を追加                                   大谷 //
//////////////////////////////////////////////////////////////////////////////
ini_set('error_reporting',E_ALL);   // E_ALL='2047' debug 用
// ini_set('display_errors','1');      // Error 表示 ON debug 用 リリース後コメント
session_start();                    // ini_set()の次に指定すること Script 最上行
require_once ("../function.php");
require_once ("../tnk_func.php");
access_log();                       // Script Name は自動取得

////// 日報処理 対象 年月日の保存
if (isset($_POST['act_ymd'])) {
    $_SESSION['act_ymd'] = $_POST['act_ymd'];   // 対象年月日をセッションに保存
} elseif (isset($_GET['act_ymd'])) {
    $_SESSION['act_ymd'] = $_GET['act_ymd'];    // 対象年月日をセッションに保存
} else {
    if (!isset($_SESSION['act_ymd'])) {
        $_SESSION['act_ymd'] = '';              // セッションに年月日が無ければ
    }
}
////// 月次ベース処理 対象 年月の保存
if (isset($_POST['act_ym'])) {
    $_SESSION['act_ym'] = $_POST['act_ym'];     // 対象年月をセッションに保存
    $_SESSION['ind_ym'] = $_POST['act_ym'];     // 対象年月をセッションに保存
} elseif (isset($_GET['act_ym'])) {
    $_SESSION['act_ym'] = $_GET['act_ym'];      // 対象年月をセッションに保存
    $_SESSION['ind_ym'] = $_GET['act_ym'];      // 対象年月をセッションに保存
} else {
    if (!isset($_SESSION['act_ym'])) {
        ///// 対象前月を算出
        $yyyymm = date('Ym');
        if (substr($yyyymm,4,2)!=01) {
            $p1_ym = $yyyymm - 1;
        } else {
            $p1_ym = $yyyymm - 100;
            $p1_ym = $p1_ym + 11;
        }
        $_SESSION['act_ym'] = $p1_ym;               // 対象年月をセッションに保存
    }
}
////// 照会メニュー 対象 年月の保存
if (isset($_POST['actv_ym'])) {
    $_SESSION['actv_ym'] = $_POST['actv_ym'];     // 対象年月をセッションに保存
    $_SESSION['indv_ym'] = $_POST['actv_ym'];     // 対象年月をセッションに保存
} elseif (isset($_GET['actv_ym'])) {
    $_SESSION['actv_ym'] = $_GET['actv_ym'];      // 対象年月をセッションに保存
    $_SESSION['indv_ym'] = $_GET['actv_ym'];      // 対象年月をセッションに保存
} else {
    if (!isset($_SESSION['actv_ym'])) {
        ///// 対象前月を算出
        $yyyymm = date('Ym');
        if (substr($yyyymm,4,2)!=01) {
            $p1_ym = $yyyymm - 1;
        } else {
            $p1_ym = $yyyymm - 100;
            $p1_ym = $p1_ym + 11;
        }
        $_SESSION['actv_ym'] = $p1_ym;               // 対象年月をセッションに保存
    }
}
////// 呼出元の保存
$_SESSION['act_referer'] = 'http:' . WEB_HOST . 'account/act_menu.php';        // 呼出もとのURLをセッションに保存
// $_SESSION['act_referer'] = $_SERVER['HTTP_REFERER'];     // 呼出もとのURLをセッションに保存
$act_referer = $_SESSION['act_referer'];

////////////// 認証チェック
if (!isset($_SESSION["User_ID"]) || !isset($_SESSION["Password"]) || !isset($_SESSION["Auth"])) {
// if (account_group_check2() == FALSE) {
// if (account_group_check() == FALSE) {
    $_SESSION['s_sysmsg'] = "あなたは許可されていません。<br>管理者に連絡して下さい。";
    header('Location: ' . $act_referer);
    // header('Location: ' . $_SERVER['HTTP_REFERER']);
    exit();
}

////////// 対象スクリプトの取得
if (isset($_POST['act_name'])) {
    $act_name = $_POST['act_name'];
} elseif (isset($_GET['act_name'])) {
    $act_name = $_GET['act_name'];
    // $_SESSION['s_sysmsg'] = $_GET['act_name'];   // Debug用
} else {
    $act_name = '';
}
switch ($act_name) {
    case '買掛金の更新'         : $script_name = 'act_payable_get_ftp.php'      ; break;
    case '買掛金のチェック'     : $script_name = 'act_payable_view.php'         ; break;
    case 'act_payable_view'     : $script_name = 'act_payable_view.php'         ; break;
    
    case '支給票の更新'         : $script_name = 'act_miprov_get_ftp.php'       ; break;
    case '支給票のチェック'     : $script_name = 'act_miprov_view.php'          ; break;
    case 'act_miprov_view'      : $script_name = 'act_miprov_view.php'          ; break;
    
    case '発注計画の更新'       : $script_name = 'order_plan_get_ftp.php'       ; break;
    case 'order_plan_update'    : $script_name = 'order_plan_get_ftp.php'       ; break;
    case '発注計画のチェック'   : $script_name = 'order_plan_view.php'          ; break;
    case 'order_plan_view'      : $script_name = 'order_plan_view.php'          ; break;
    
    case '棚卸データの更新'         : $script_name = 'inventory/inventory_month_update.php'     ; break;
    case 'inventory_month_update'   : $script_name = 'inventory/inventory_month_update.php'     ; break;
    case '棚卸データのチェック'     : $script_name = 'inventory/inventory_month_view.php'       ; break;
    case 'inventory_month_view'     : $script_name = 'inventory/inventory_month_view.php'       ; break;
    
    case '客先支給品の更新'         : $script_name = 'provide_month_update.php'     ; break;
    case 'provide_month_update'     : $script_name = 'provide_month_update.php'     ; break;
    case '客先支給品のチェック'     : $script_name = 'provide_month_view.php'       ; break;
    case 'provide_month_view'       : $script_name = 'provide_month_view.php'       ; break;
    
    case '発注先マスター更新'       : $script_name = 'vendor_master_update.php'     ; break;
    case 'vendor_master_update'     : $script_name = 'vendor_master_update.php'     ; break;
    case '発注先マスターチェック'   : $script_name = 'vendor_master_view.php'       ; break;
    case 'vendor_master_view'       : $script_name = 'vendor_master_view.php'       ; break;
    
    case '担当者マスター更新'           : $script_name = 'vendor_person_master_update.php'     ; break;
    case 'vendor_person_master_update'  : $script_name = 'vendor_person_master_update.php'     ; break;
    case '担当者マスターチェック'       : $script_name = 'vendor_person_master_view.php'       ; break;
    case 'vendor_person_master_view'    : $script_name = 'vendor_person_master_view.php'       ; break;
    
    case '仕入金額の照会'       : $script_name = 'act_purchase_view.php'        ; break;
    case 'act_purchase_view'    : $script_name = 'act_purchase_view.php'        ; break;
    case '仕入計上処理'         : $script_name = 'act_purchase_update.php'      ; break;
    case 'act_purchase_update'  : $script_name = 'act_purchase_update.php'      ; break;
    
    case 'Ｃ特注棚卸金額'               : $script_name = 'inventory/inventory_monthly_ctoku_view.php'   ; break;
    case 'inventory_month_ctoku_view'   : $script_name = 'inventory/inventory_monthly_ctoku_view.php'   ; break;
    case 'Ｃ特注棚卸前月'                   : $script_name = 'inventory_month_ctoku_zen_view.php'   ; break;
    case 'inventory_month_ctoku_zen_view'   : $script_name = 'inventory_month_ctoku_zen_view.php'   ; break;
    
    case 'バイモル棚卸金額'             : $script_name = 'inventory/inventory_month_bimor_view.php'   ; break;
    case 'inventory_month_bimor_view'   : $script_name = 'inventory/inventory_month_bimor_view.php'   ; break;
    
    case 'ツール棚卸金額'             : $script_name = 'inventory/inventory_month_tool_view.php'   ; break;
    case 'inventory_month_tool_view'   : $script_name = 'inventory/inventory_month_tool_view.php'   ; break;
    
    case 'カプラ棚卸金額'           : $script_name = 'inventory/inventory_month_c_view.php'   ; break;
    case 'inventory_month_c_view'   : $script_name = 'inventory/inventory_month_c_view.php'   ; break;
    
    case 'リニア棚卸金額'           : $script_name = 'inventory/inventory_month_l_view.php'   ; break;
    case 'inventory_month_l_view'   : $script_name = 'inventory/inventory_month_l_view.php'   ; break;
    
    case 'Ａ伝情報の更新'       : $script_name = 'aden_master_update.php'     ; break;
    case 'aden_master_update'   : $script_name = 'aden_master_update.php'     ; break;
    case 'Ａ伝情報の照会'       : $script_name = 'aden_master_view.php'       ; break;
    case 'aden_master_view'     : $script_name = 'aden_master_view.php'       ; break;
    
    case '部門別 棚卸金額 計上処理'          : $script_name = 'inventory/inventory_monthly_header_update.php'     ; break;
    case 'inventory_monthly_header_update'   : $script_name = 'inventory/inventory_monthly_header_update.php'     ; break;
    
    case '総平均棚卸金額'           : $script_name = 'inventory/inventory_month_view_average.php'   ; break;
    case 'inventory_month_view_average'   : $script_name = 'inventory/inventory_month_view_average.php'   ; break;
    
    case 'カプラ総平均棚卸金額'           : $script_name = 'inventory/inventory_month_c_view_average.php'   ; break;
    case 'inventory_month_c_view_average'   : $script_name = 'inventory/inventory_month_c_view_average.php'   ; break;
    
    case 'リニア総平均棚卸金額'           : $script_name = 'inventory/inventory_month_l_view_average.php'   ; break;
    case 'inventory_month_l_view_average'   : $script_name = 'inventory/inventory_month_l_view_average.php'   ; break;
    
    case 'Ｃ特注総平均棚卸金額'               : $script_name = 'inventory/inventory_monthly_ctoku_view_average.php'   ; break;
    case 'inventory_month_ctoku_view_average'   : $script_name = 'inventory/inventory_monthly_ctoku_view_average.php'   ; break;
    
    case 'Ｃ特注総平均棚卸金額配賦'               : $script_name = 'inventory/inventory_monthly_ctoku_view_average_allo.php'   ; break;
    case 'inventory_month_ctoku_view_average_allo'   : $script_name = 'inventory/inventory_monthly_ctoku_view_average_allo.php'   ; break;
    
    case 'バイモル総平均棚卸金額'             : $script_name = 'inventory/inventory_month_bimor_view_average.php'   ; break;
    case 'inventory_month_bimor_view_average'   : $script_name = 'inventory/inventory_month_bimor_view_average.php'   ; break;
    
    case 'ツール総平均棚卸金額'             : $script_name = 'inventory/inventory_month_tool_view_average.php'   ; break;
    case 'inventory_month_tool_view_average'   : $script_name = 'inventory/inventory_month_tool_view_average.php'   ; break;
    
    case '総平均棚卸金額比較'           : $script_name = 'inventory/inventory_month_compare.php'   ; break;
    case 'inventory_month_compare'   : $script_name = 'inventory/inventory_month_compare.php'   ; break;
    
    case 'カプラ総平均棚卸金額比較'           : $script_name = 'inventory/inventory_month_c_compare.php'   ; break;
    case 'inventory_month_c_compare'   : $script_name = 'inventory/inventory_month_c_compare.php'   ; break;
    
    case 'リニア総平均棚卸金額比較'           : $script_name = 'inventory/inventory_month_l_compare.php'   ; break;
    case 'inventory_month_l_compare'   : $script_name = 'inventory/inventory_month_l_compare.php'   ; break;
    
    case 'Ｃ特注総平均棚卸金額比較'           : $script_name = 'inventory/inventory_month_ctoku_compare.php'   ; break;
    case 'inventory_month_ctoku_compare'   : $script_name = 'inventory/inventory_month_ctoku_compare.php'   ; break;
    
    case 'バイモル総平均棚卸金額比較'           : $script_name = 'inventory/inventory_month_bimor_compare.php'   ; break;
    case 'inventory_month_bimor_compare'   : $script_name = 'inventory/inventory_month_bimor_compare.php'   ; break;
    
    case 'ツール総平均棚卸金額比較'           : $script_name = 'inventory/inventory_month_tool_compare.php'   ; break;
    case 'inventory_month_tool_compare'   : $script_name = 'inventory/inventory_month_tool_compare.php'   ; break;
    
    case '連結取引総括表'           : $script_name = 'link_trans/link_trans_menu.php'   ; break;
    case 'link_trans'   : $script_name = 'link_trans/link_trans_menu.php'   ; break;
    
    default: $script_name = 'act_menu.php';              // 呼出もとへ帰る
             $url_name    = $act_referer;       // 呼出もとのURL 別メニューから呼び出された時の対応
}
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=EUC-JP">
<meta http-equiv="Content-Style-Type" content="text/css">
<title>経理日報 分岐処理</title>
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
        処理中です。お待ち下さい。<br>
        <img src='../img/tnk-turbine.gif' width=68 height=72>
    </center>
</body>
</html>
