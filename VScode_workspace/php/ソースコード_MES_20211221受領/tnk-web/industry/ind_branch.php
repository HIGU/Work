<?php
//////////////////////////////////////////////////////////////////////////////
// 生産 関係 処理の Branch (分岐)処理                                       //
// Copyright(C) 2003-2004 2007 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp  //
// Changed history                                                          //
// 2003/11/29 Created   ind_branch.php                                      //
// 2003/12/04 セッションに対象年月が無ければ前月を算出して設定する。        //
// 2003/12/15 総材料費の登録追加とディレクトリ定義をdefine.php から取得     //
// 2004/04/07 ASSY番号による総材料費の照会を追加                            //
// 2004/12/07 買掛関係のプログラムを industry/payable へ移動                //
// 2004/12/22 総材料費関係のプログラムを industry/material へ移動           //
// 2007/09/05 payable_linear_vendor_summary2 を追加                         //
//////////////////////////////////////////////////////////////////////////////
ini_set('error_reporting', E_ALL);          // E_ALL='2047' debug 用
// ini_set('display_errors', '1');            // Error 表示 ON debug 用 リリース後コメント
ob_start('ob_gzhandler');                   // 出力バッファをgzip圧縮
session_start();                            // ini_set()の次に指定すること Script 最上行
require_once ('../function.php');
require_once ('../tnk_func.php');
access_log();                               // Script Name は自動取得

////// 日報処理 対象 年月日の保存
if (isset($_REQUEST['act_ymd'])) {
    $_SESSION['act_ymd'] = $_REQUEST['act_ymd'];    // 対象年月日をセッションに保存
} else {
    if (!isset($_SESSION['act_ymd'])) {
        $_SESSION['act_ymd'] = '';                  // セッションに年月日が無ければ
    }
}
////// 月次ベース処理 対象 年月の保存
if (isset($_REQUEST['act_ym'])) {
    $_SESSION['act_ym'] = $_REQUEST['act_ym'];      // 対象年月をセッションに保存
} else {
    ///// 対象前月を算出
    $yyyymm = date('Ym');
    if (substr($yyyymm,4,2)!=01) {
        $p1_ym = $yyyymm - 1;
    } else {
        $p1_ym = $yyyymm - 100;
        $p1_ym = $p1_ym + 11;
    }
    $_SESSION['act_ym'] = $p1_ym;                   // 対象年月をセッションに保存
}
////// 呼出元の保存
$_SESSION['act_referer'] = H_WEB_HOST . INDUST_MENU;        // 呼出もとのURLをセッションに保存
// $_SESSION['act_referer'] = $_SERVER['HTTP_REFERER'];     // 呼出もとのURLをセッションに保存
$act_referer = $_SESSION['act_referer'];

////////////// 認証チェック
//if (!isset($_SESSION["User_ID"]) || !isset($_SESSION["Password"]) || !isset($_SESSION["Auth"])) {
if (!isset($_SESSION["User_ID"]) || !isset($_SESSION["Password"])) {
// if (account_group_check2() == FALSE) {
// if (account_group_check() == FALSE) {
    $_SESSION['s_sysmsg'] = "あなたは許可されていません。<br>管理者に連絡して下さい。";
    header('Location: ' . $act_referer);
    // header('Location: ' . $_SERVER['HTTP_REFERER']);
    exit();
}

////// 月次ベース処理 対象 年月の保存
if (isset($_REQUEST['ind_ym'])) {
    $_SESSION['ind_ym'] = $_REQUEST['ind_ym'];  // 対象年月をセッションに保存
} else {
    if (!isset($_SESSION['ind_ym'])) {
        ///// 対象前月を算出
        $yyyymm = date('Ym');
        if (substr($yyyymm,4,2)!=01) {
            $p1_ym = $yyyymm - 1;
        } else {
            $p1_ym = $yyyymm - 100;
            $p1_ym = $p1_ym + 11;
        }
        $_SESSION['ind_ym'] = $p1_ym;               // 対象年月をセッションに保存
    }
}
////////// 対象スクリプトの取得
if (isset($_REQUEST['ind_name'])) {
    $ind_name = $_REQUEST['ind_name'];
} else {
    $ind_name = '';
}
switch ($ind_name) {
    case 'Ａ伝情報の照会'       : $script_name = INDUST . 'Aden/aden_master_view_form.php'    ; break;
    case 'aden_master_view'     : $script_name = INDUST . 'Aden/aden_master_view_form.php'    ; break;
    
    case '買掛実績の照会'       : $script_name = INDUST . 'payable/act_payable_form.php'         ; break;
    case 'act_payable_view'     : $script_name = INDUST . 'payable/act_payable_form.php'         ; break;
    
    case '支給票の照会'         : $script_name = INDUST . 'act_miprov_view.php'          ; break;
    case 'act_miprov_view'      : $script_name = INDUST . 'act_miprov_view.php'          ; break;
    
    case '発注計画の照会'       : $script_name = INDUST . 'order_plan_view.php'          ; break;
    case 'order_plan_view'      : $script_name = INDUST . 'order_plan_view.php'          ; break;
    
    case 'カプラ棚卸金額'           : $script_name = ACT . 'inventory/inventory_month_c_view.php'   ; break;
    case 'inventory_month_c_view'   : $script_name = ACT . 'inventory/inventory_month_c_view.php'   ; break;
    
    case 'リニア棚卸金額'           : $script_name = ACT . 'inventory/inventory_month_l_view.php'   ; break;
    case 'inventory_month_l_view'   : $script_name = ACT . 'inventory/inventory_month_l_view.php'   ; break;
    
    case '発注先マスター照会'   : $script_name = INDUST . 'vendor_master_view.php'       ; break;
    case 'vendor_master_view'   : $script_name = INDUST . 'vendor_master_view.php'       ; break;
    
    case '仕入金額の照会'       : $script_name = ACT . 'act_purchase_view.php'        ; break;
    case 'act_purchase_view'    : $script_name = ACT . 'act_purchase_view.php'        ; break;
    
    case 'バイモル棚卸金額'             : $script_name = ACT . 'inventory/inventory_month_bimor_view.php'   ; break;
    case 'inventory_month_bimor_view'   : $script_name = ACT . 'inventory/inventory_month_bimor_view.php'   ; break;
    case 'ツール棚卸金額'             : $script_name = ACT . 'inventory/inventory_month_tool_view.php'   ; break;
    case 'inventory_month_tool_view'   : $script_name = ACT . 'inventory/inventory_month_tool_view.php'   ; break;
    case 'Ｃ特注棚卸金額'               : $script_name = ACT . 'inventory/inventory_monthly_ctoku_view.php'   ; break;
    case 'inventory_month_ctoku_view'   : $script_name = ACT . 'inventory/inventory_monthly_ctoku_view.php'   ; break;
    
    case 'datasum_barcode'   : $script_name = INDUST . 'BarCode/datasum_barcode.php'   ; break;
    
    case 'Ｃ特注棚卸前月'                   : $script_name = INDUST . 'inventory_month_ctoku_zen_view.php'   ; break;
    case 'inventory_month_ctoku_zen_view'   : $script_name = INDUST . 'inventory_month_ctoku_zen_view.php'   ; break;
    
    case 'カプラ特注買掛実績'            : $script_name = INDUST . 'payable/payable_ctoku_view.php'             ; break;
    case 'payable_ctoku_view'            : $script_name = INDUST . 'payable/payable_ctoku_view.php'             ; break;
    case 'payable_ctoku_vendor_summary'  : $script_name = INDUST . 'payable/payable_ctoku_vendor_summary.php'   ; break;
    case 'payable_ctoku_view2'           : $script_name = INDUST . 'payable/payable_ctoku_view2.php'            ; break;
    case 'payable_ctoku_vendor_summary2' : $script_name = INDUST . 'payable/payable_ctoku_vendor_summary2.php'  ; break;
    case 'payable_cstd_vendor_summary2'  : $script_name = INDUST . 'payable/payable_cstd_vendor_summary2.php'   ; break;
    case 'payable_linear_vendor_summary2': $script_name = INDUST . 'payable/payable_linear_vendor_summary2.php' ; break;
    
    case '棚卸データの照会'         : $script_name = INDUST . 'inventory_month_view.php'        ; break;
    case 'inventory_month_view'     : $script_name = INDUST . 'inventory_month_view.php'        ; break;
    
    case '総材料費の登録'           : $script_name = INDUST . 'materialCost_entry_plan.php'     ; break;
    case 'materialCost_entry_plan'  : $script_name = INDUST . 'materialCost_entry_plan.php'     ; break;
    case '総材料費の照会(計画番号)' : $script_name = INDUST . 'materialCost_view_plan.php'      ; break;
    case 'materialCost_view_plan'   : $script_name = INDUST . 'materialCost_view_plan.php'      ; break;
    case '総材料費の照会(ASSY番号)' : $script_name = INDUST . 'materialCost_view_assy.php'      ; break;
    case 'materialCost_view_assy'   : $script_name = INDUST . 'materialCost_view_assy.php'      ; break;
    case '総材料費と売上高の比率'   : $script_name = INDUST . 'materialCost_sales_comp.php'     ; break;
    case 'materialCost_sales_comp'  : $script_name = INDUST . 'materialCost_sales_comp.php'     ; break;
    case '総材料費の未登録照会'         : $script_name = INDUST . 'material/materialCost_unregist_view.php'  ; break;
    case 'materialCost_unregist_view'   : $script_name = INDUST . 'material/materialCost_unregist_view.php'  ; break;
    
    case '製品売上未検収明細照会'   : $script_name = INDUST . 'sales_miken_view.php'  ; break;
    case 'sales_miken_view'         : $script_name = INDUST . 'sales_miken_view.php'  ; break;
    
    case '仕切単価と総材料費比較'   : $script_name = INDUST . 'sales_material_comp_form.php'  ; break;
    case 'sales_material_comp_form' : $script_name = INDUST . 'sales_material_comp_form.php'  ; break;
    
    case '単価経歴照会'         : $script_name = INDUST . 'parts_cost_form.php'  ; break;
    case 'parts_cost_form'      : $script_name = INDUST . 'parts_cost_form.php'  ; break;
    
    case '引当部品構成表の照会' : $script_name = INDUST . 'allo_conf_parts_form.php'  ; break;
    case 'allo_conf_parts_form' : $script_name = INDUST . 'allo_conf_parts_form.php'  ; break;
    
    default: $script_name = INDUST . 'industry_menu.php';              // 呼出もとへ帰る
             $url_name    = $act_referer;       // 呼出もとのURL 別メニューから呼び出された時の対応
}
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<HTML>
<HEAD>
<META http-equiv="Content-Type" content="text/html; charset=EUC-JP">
<META http-equiv="Content-Style-Type" content="text/css">
<TITLE>生産メニュー 分岐処理</TITLE>
<style type="text/css">
<!--
body        {margin:20%;font-size:24pt;}
-->
</style>
</HEAD>
<BODY>
    <center>
        処理中です。お待ち下さい。<br>
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
