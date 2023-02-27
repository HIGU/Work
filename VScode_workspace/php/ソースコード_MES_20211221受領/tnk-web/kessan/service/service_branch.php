<?php
//////////////////////////////////////////////////////////////////////////
// 原価計算で使用するサービス割合の Branch (分岐)処理                   //
// 2003/10/20 Copyright(C) 2003 K.Kobayashi tnksys@nitto-kohki.co.jp    //
// 変更経歴                                                             //
// 2003/10/20 新規作成  service_branch.php                              //
// 2003/10/24 service_category_select.php?exec=entry OR view(照会)を追加//
//////////////////////////////////////////////////////////////////////////
ini_set('error_reporting',E_ALL);   // E_ALL='2047' debug 用
// ini_set('display_errors','1');      // Error 表示 ON debug 用 リリース後コメント
session_start();                    // ini_set()の次に指定すること Script 最上行
require_once ("../../function.php");
require_once ("../../tnk_func.php");
access_log();                       // Script Name は自動取得

////////////// 認証チェック
if (!isset($_SESSION["User_ID"]) || !isset($_SESSION["Password"]) || !isset($_SESSION["Auth"])) {
// if (account_group_check2() == FALSE) {
// if (account_group_check() == FALSE) {
    $_SESSION["s_sysmsg"] = "あなたは許可されていません。<br>管理者に連絡して下さい。";
    // header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
    header("Location: " . $_SERVER["HTTP_REFERER"]);
    exit();
}

$_SESSION['service_ym']      = $_POST['service_ym'];            // 対象年月をセッションに保存
//////// service_category_selectは対象から外す service_percentage_menuと共用させるため
if ( !preg_match('/service_category_select.php/', $_SERVER['HTTP_REFERER']) ) {
    $_SESSION['service_referer'] = $_SERVER['HTTP_REFERER'];        // 呼出もとのURLをセッションに保存
}
switch ($_POST['service_name']) {
    case 'サービス割合入力' : $script_name = 'kessan/service/service_category_select.php?exec=entry'; break;
    case 'サービス割合照会' : $script_name = 'kessan/service/service_category_select.php?exec=view' ; break;
    case '割合 全体 照会'   : $script_name = 'kessan/service/service_percent_view_total.php'        ; break;
    case '製造経費の配賦'   : $script_name = 'kessan/service/service_percent_act_allo.php'          ; break;
    case 'マスター編集'     : $script_name = 'kessan/service/service_item_master_mnt.php'           ; break;
    case '予測配賦率算定用' : $script_name = 'kessan/service/service_percent_act_allo_plan.php'     ; break;
    case '月次確定処理'     : $script_name = 'kessan/service/service_final_set.php?set'             ; break;
    case '月次確定解除'     : $script_name = 'kessan/service/service_final_set.php?unset'           ; break;
    case '製造経費配賦仮締' : $script_name = 'kessan/service/service_percent_act_allo_kari.php'     ; break;
    
    default: $script_name = 'kessan/service/service_percentage_menu.php';          // 呼出もとへ帰る
             $url_name    = $_SESSION['service_referer'];        // 呼出もとのURL 別メニューから呼び出された時の対応
}
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<HTML>
<HEAD>
<META http-equiv="Content-Type" content="text/html; charset=EUC-JP">
<META http-equiv="Content-Style-Type" content="text/css">
<TITLE>サービス割合 分岐処理</TITLE>
<style type="text/css">
<!--
body        {margin:20%;font-size:24pt;}
-->
</style>
</HEAD>
<BODY>
    <center>処理中です。お待ち下さい。</center>

    <script language="JavaScript">
    <!--
    <?php
        if (isset($url_name)) {
            echo "location = '$url_name'";
        } else {
            echo "location = 'http:" . WEB_HOST . "$script_name'";
        }
    ?>
    // -->
    </script>
</BODY>
</HTML>
