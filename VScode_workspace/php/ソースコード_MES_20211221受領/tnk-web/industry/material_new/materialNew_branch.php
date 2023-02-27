<?php
//////////////////////////////////////////////////////////////////////////////
// 仕切単価改定処理の Branch (分岐)処理 メニュー                            //
// Copyright (C) 2010 Norihisa.Ohya norihisa_ooya@nitto-kohki.co.jp         //
// Changed history                                                          //
// 2010/05/13 Created   materialNew_branch.php                              //
//////////////////////////////////////////////////////////////////////////////
//ini_set('error_reporting', E_ALL || E_STRICT);
ob_start('ob_gzhandler');                   // 出力バッファをgzip圧縮
require_once ('../../function.php');        // define.php と pgsql.php を require_once している
require_once ('../../tnk_func.php');        // TNK に依存する部分の関数を require_once している
require_once ('../../MenuHeader.php');      // TNK 全共通 menu class
require_once ('../../ControllerHTTP_Class.php'); // TNK 全共通 MVC Controller Class
access_log(); 
            
$request = new Request;
$session = new Session;

////// 呼出元の保存
$product_master_referer = 'http:' . WEB_HOST . 'industry/material_new/materialNew_menu.php';        // 呼出もとのURLをセッションに保存
// $_SESSION['act_referer'] = $_SERVER['HTTP_REFERER'];     // 呼出もとのURLをセッションに保存
$session->add('product_master_referer', $product_master_referer);

////////// 対象スクリプトの取得
if ($request->get('product_master_name') != '') {
    $product_master_name = $request->get('product_master_name');
} else {
    $product_master_name = '';
}
////// 対象 年月の保存
if (isset($_REQUEST['ind_ym'])) {
    $_SESSION['ind_ym'] = $_REQUEST['ind_ym'];  // 対象年月をセッションに保存
}
switch ($request->get('product_master_name')) {
    case 'カプラ仕切登録・照会' : $script_name = 'materialNew_Main.php'; break;
    case 'リニア仕切登録・照会' : $script_name = 'materialNewLinear_Main.php' ; break;
    case 'ツール仕切登録・照会' : $script_name = 'materialNewTool_Main.php' ; break;
    case '仕切掛率の登録' : $script_name = 'materialPartsCredit_Main.php' ; break;
    case '仕切単価影響額の照会' : $script_name = 'materialNewSales_form.php' ; break;
    
    default: $script_name = 'materialNew_menu.php';          // 呼出もとへ帰る
             $url_name    = $product_master_referer;        // 呼出もとのURL 別メニューから呼び出された時の対応
}
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<HTML>
<HEAD>
<META http-equiv="Content-Type" content="text/html; charset=EUC-JP">
<META http-equiv="Content-Style-Type" content="text/css">
<TITLE>組立賃率 分岐処理</TITLE>
<style type="text/css">
<!--
body        {margin:20%;font-size:24pt;}
-->
</style>
<form name='branch_form' method='post' action='<?php if (isset($url_name)) echo $url_name; else echo $script_name; ?>'>
</form>
</head>
<body onLoad='document.branch_form.submit()'>
    <center>
        処理中です。お待ち下さい。<br>
    </center>
</body>
</html>
<?php
ob_end_flush();                 // 出力バッファをgzip圧縮 END
?>
