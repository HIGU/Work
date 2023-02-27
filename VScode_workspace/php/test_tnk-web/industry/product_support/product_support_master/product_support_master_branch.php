<?php
//////////////////////////////////////////////////////////////////////////////
// 生産支援品マスター編集の Branch (分岐)処理 メニュー                      //
// Copyright (C) 2011-     Norihisa.Ohya norihisa_ooya@nitto-kohki.co.jp    //
// Changed history                                                          //
// 2011/11/10 Created   product_support_master_branch.php                   //
//////////////////////////////////////////////////////////////////////////////
//ini_set('error_reporting', E_ALL || E_STRICT);
ob_start('ob_gzhandler');                   // 出力バッファをgzip圧縮
require_once ('../../../function.php');        // define.php と pgsql.php を require_once している
require_once ('../../../tnk_func.php');        // TNK に依存する部分の関数を require_once している
require_once ('../../../MenuHeader.php');      // TNK 全共通 menu class
require_once ('../../../ControllerHTTP_Class.php'); // TNK 全共通 MVC Controller Class
access_log(); 
            
$request = new Request;
$session = new Session;

////// 呼出元の保存
$product_master_referer = 'http:' . WEB_HOST . 'industry/master/product_support_master/product_support_master_menu.php';        // 呼出もとのURLをセッションに保存
// $_SESSION['act_referer'] = $_SERVER['HTTP_REFERER'];     // 呼出もとのURLをセッションに保存
$session->add('product_master_referer', $product_master_referer);

////////// 対象スクリプトの取得
if ($request->get('product_master_name') != '') {
    $product_master_name = $request->get('product_master_name');
} else {
    $product_master_name = '';
}
switch ($request->get('product_master_name')) {
    case '生産支援品マスターの登録' : $script_name = 'product_supportMaster_Main.php'; break;
    //case '製品グループコードの編集' : $script_name = 'product_groupMaster_Main.php' ; break;
    case '支援先の登録' : $script_name = 'product_support_groupMaster_Main.php'; break;
    case '製品グループコードの編集' : $script_name = 'product_groupMaster_Main2.php' ; break;
    
    default: $script_name = 'product_support_master_menu.php';          // 呼出もとへ帰る
             $url_name    = $product_master_referer;        // 呼出もとのURL 別メニューから呼び出された時の対応
}
?>
<!DOCTYPE html>
<HTML>
<HEAD>
<META http-equiv="Content-Type" content="text/html; charset=UTF-8">
<META http-equiv="Content-Style-Type" content="text/css">
<TITLE>生産支援品マスターの編集 分岐処理</TITLE>
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
