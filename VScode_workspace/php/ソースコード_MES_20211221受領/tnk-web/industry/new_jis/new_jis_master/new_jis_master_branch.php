<?php
//////////////////////////////////////////////////////////////////////////////
// 新JIS対象製品マスター編集の Branch (分岐)処理 メニュー                   //
// Copyright (C) 2014-2017 Norihisa.Ohya norihisa_ooya@nitto-kohki.co.jp    //
// Changed history                                                          //
// 2014/11/17 Created   new_jis_master_branch.php                           //
// 2014/12/08 品目→形式へ変更                                              //
// 2014/12/22 形式→型式へ変更                                              //
// 2017/04/27 各メニューの表示より『新JIS』を削除                      大谷 //
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
$newjis_master_referer = 'http:' . WEB_HOST . 'industry/new_jis/new_jis_master/new_jis_master_menu.php';        // 呼出もとのURLをセッションに保存
// $_SESSION['act_referer'] = $_SERVER['HTTP_REFERER'];     // 呼出もとのURLをセッションに保存
$session->add('newjis_master_referer', $newjis_master_referer);

////////// 対象スクリプトの取得
if ($request->get('newjis_master_name') != '') {
    $newjis_master_name = $request->get('newjis_master_name');
} else {
    $newjis_master_name = '';
}
switch ($request->get('newjis_master_name')) {
    case '対象製品の登録' : $script_name = 'newjis_itemMaster_Main.php'; break;
    case '型式の登録' : $script_name = 'newjis_groupMaster_Main.php'; break;
    
    default: $script_name = 'new_jis_master_menu.php';          // 呼出もとへ帰る
             $url_name    = $newjis_master_referer;        // 呼出もとのURL 別メニューから呼び出された時の対応
}
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<HTML>
<HEAD>
<META http-equiv="Content-Type" content="text/html; charset=EUC-JP">
<META http-equiv="Content-Style-Type" content="text/css">
<TITLE>対象製品マスターの編集 分岐処理</TITLE>
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
