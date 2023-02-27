<?php
//////////////////////////////////////////////////////////////////////////////
// 組立賃率計算の Branch (分岐)処理 メニュー                                //
// Copyright (C) 2006-2007 Norihisa.Ohya usoumu@nitto-kohki.co.jp           //
// Changed history                                                          //
// 2006/09/13 Created   wage_branch.php                                     //
// 2007/10/05 フォルダooyaを削除した為アドレスを変更                        //
// 2007/10/19 E_ALLをE_STRICTへ→コメント化                                 //
// 2007/10/24 プログラムの最後に改行を追加                                  //
// 2007/12/13 対象年月の受け渡し用に$requestを設定                          //
// 2007/12/29 減価償却費・組立賃率・間接費配賦率を新プログラムへリンク変更  //
// 2008/01/09 呼び出し元の保存を$sessionに変更                              //
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

$wage_ym = $request->get('wage_ym');            // 対象年月を保存

////// 呼出元の保存
$wage_referer = 'http:' . WEB_HOST . 'kessan/wage_rate/wage_rate_menu.php';        // 呼出もとのURLをセッションに保存
// $_SESSION['act_referer'] = $_SERVER['HTTP_REFERER'];     // 呼出もとのURLをセッションに保存
$session->add('wage_referer', $wage_referer);

////////// 対象スクリプトの取得
if ($request->get('wage_name') != '') {
    $wage_name = $request->get('wage_name');
} else {
    $wage_name = '';
}
switch ($request->get('wage_name')) {
    case '各種データ入力' : $script_name = 'wage_various_data_input_menu.php'; break;
    case '減価償却費照会' : $script_name = 'assemblyRate_depreciationCal_Main.php' ; break;
    case '組立賃率の照会' : $script_name = 'assemblyRate_reference_Main.php'        ; break;
    case '間接費配賦率の照会' : $script_name = 'assemblyRate_actAllocate_Main.php'        ; break;
    
    default: $script_name = 'wage_rate_menu.php';          // 呼出もとへ帰る
             $url_name    = $wage_referer;        // 呼出もとのURL 別メニューから呼び出された時の対応
}
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<HTML>
<HEAD>
<META http-equiv="Content-Type" content="text/html; charset=UTF-8">
<META http-equiv="Content-Style-Type" content="text/css">
<TITLE>組立賃率 分岐処理</TITLE>
<style type="text/css">
<!--
body        {margin:20%;font-size:24pt;}
-->
</style>
<form name='branch_form' method='post' action='<?php if (isset($url_name)) echo $url_name; else echo $script_name; ?>'>
<input type='hidden' name='wage_ym' value='<?php echo $wage_ym ?>'>
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
