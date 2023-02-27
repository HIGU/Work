<?php
//////////////////////////////////////////////////////////////////////////////
// 月次損益関係 ＣＬ別 経費(間接費・販管費)配賦計算                         //
// Copyright(C) 2003-2016 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp       //
// 変更経歴                                                                 //
// 2003/02/03 新規作成  profit_loss_cl_allocation.php                       //
//            使用テーブル 読込 bm_km_summary (科目別部門経費)              //
//                         書込 act_allo_history (月次配賦率計算経歴)       //
// 2003/02/07 配賦率の少数桁数変更 %1.3f → %1.5f Uround(???.5) へ          //
//                                      Excel と見た目を合わせるため        //
// 2003/02/12 対象データのチェックを select pl_bs_ym → select sum(kin)     //
//              へ変更 合計金額が 0 の場合はエラーとする。                  //
// 2003/03/04 データ更新をトランザクションに変更 (データの保証)             //
// 2004/02/05 sprintfで $allo を %d → %01.5f へ修正 173 174 500 の部分     //
//            (PostgreSQL V7.4.1 PHP V4.3.5RC2 でトラブルあり)              //
// 2004/07/02 面積比をカプラ=83.133% リニア=16.867% へ変更 ６月分より適用   //
// 2009/06/10 技術部：501部門の経費配賦計算を追加                      大谷 //
// 2009/08/07 物流損益追加の為、580部門の製造経費と670部門の販管費を        //
//            強制的にカプラに振分ける暫定対応                         大谷 //
// 2009/08/20 商品別損益及び経費実績表で暫定的にカプラに振分けた経費を      //
//            それぞれの部門に正しく戻し表示するように変更した         大谷 //
// 2012/11/06 500部門のCLの割合を変更 C:30・L:70→C:70・L:30           大谷 //
// 2016/10/04 545部門のCLの割合を追加 C:70・L:30                       大谷 //
// 2016/10/14 2016/10よりすべての割合をC:80・L:20へ変更                大谷 //
//////////////////////////////////////////////////////////////////////////////
ini_set('error_reporting',E_ALL);   // E_ALL='2047' debug 用
// ini_set('display_errors','1');      // Error 表示 ON debug 用 リリース後コメント
session_start();                    // ini_set()の次に指定すること Script 最上行
require_once ("../function.php");
require_once ("../tnk_func.php");
///// 期・月の取得
$ki = 22;
$tuki = 11;
///// 対象当月
$yyyymm = 202211;
///// 対象前月
if (substr($yyyymm,4,2)!=01) {
    $p1_ym = $yyyymm - 1;
} else {
    $p1_ym = $yyyymm - 100;
    $p1_ym = $p1_ym + 11;
}
//////////// 173 174 500 501 545 部門の人件費 配賦率
if ($yyyymm > 201609) {
    $allo_173_c = 0.80;     // 173 配賦率カプラ
    $allo_173_l = 0.20;     // 173 配賦率リニア
    $allo_174_c = 0.80;     // 174 配賦率カプラ
    $allo_174_l = 0.20;     // 174 配賦率リニア
    $allo_500_c = 0.80;     // 500 配賦率カプラ
    $allo_500_l = 0.20;     // 500 配賦率リニア
    $allo_501_c = 0.80;     // 501 配賦率カプラ
    $allo_501_l = 0.20;     // 501 配賦率リニア
    $allo_545_c = 0.80;     // 545 配賦率カプラ
    $allo_545_l = 0.20;     // 545 配賦率リニア
} else {
    $allo_173_c = 0.70;     // 173 配賦率カプラ
    $allo_173_l = 0.30;     // 173 配賦率リニア
    $allo_174_c = 0.60;     // 174 配賦率カプラ
    $allo_174_l = 0.40;     // 174 配賦率リニア
    $allo_500_c = 0.70;     // 500 配賦率カプラ
    $allo_500_l = 0.30;     // 500 配賦率リニア
    $allo_501_c = 0.70;     // 501 配賦率カプラ
    $allo_501_l = 0.30;     // 501 配賦率リニア
    $allo_545_c = 0.70;     // 545 配賦率カプラ
    $allo_545_l = 0.30;     // 545 配賦率リニア
}


//////////// タイトルの日付・時間設定
$today = date("Y/m/d H:m:s");

header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");    // 日付が過去
header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT"); // 常に修正されている
header("Cache-Control: no-store, no-cache, must-revalidate");  // HTTP/1.1
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");                          // HTTP/1.0
?>
<!DOCTYPE html>
<HTML>
<HEAD>
<META http-equiv="Content-Type" content="text/html; charset=UTF-8">
<META http-equiv="Content-Style-Type" content="text/css">
<TITLE>ＣＬ経費配賦率計算</TITLE>
<script language="JavaScript">
<!--
    parent.menu_site.location = 'http:<?php echo(WEB_HOST) ?>menu_site.php';
// -->
</script>
<style type="text/css">
<!--
select {
    background-color:teal;
    color:white;
}
textarea {
    background-color:black;
    color:white;
}
input.sousin {
    background-color:red;
}
input.text {
    background-color:black;
    color:white;
}
.pt8 {
    font:normal 8pt;
}
.pt10 {
    font:normal 10pt;
}
.pt10b {
    font:bold 10pt;
}
.pt12b {
    font:bold 12pt;
}
.margin0 {
    margin:0%;
}
-->
</style>
</HEAD>
<BODY class='margin0'>
    <center>
    </center>
</BODY>
</HTML>
