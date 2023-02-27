<?php
//////////////////////////////////////////////////////////////////////////////
// リニア仕切見直し 総材料費登録確認用 照会メニュー                         //
// Copyright (C) 2008-2009 Norihisa.Ohya usoumu@nitto-kohki.co.jp           //
// Changed history                                                          //
// 2008/02/12 Created  materialCheckLinear_Main2.php                        //
//                     (materialCheck_Main.phpを改造                        //
// 2008/02/29 対象を2008年3月までに変更                                     //
// 2009/02/17 対象を2009年1月までに変更                                     //
// 2009/09/10 対象を2009年8月までに変更                                     //
// 2009/11/27 対象を2009年11月までに変更                                    //
// 2010/03/10 対象を202年2月までに変更                                      //
//////////////////////////////////////////////////////////////////////////////
// ini_set('error_reporting', E_ALL | E_STRICT)
// ini_set('error_reporting', E_ALL);          // E_ALL='2047' debug 用
ob_start('ob_gzhandler');                   // 出力バッファをgzip圧縮
session_start();                            // ini_set()の次に指定すること Script 最上行

require_once ('../../function.php');                // define.php と pgsql.php を require_once している
require_once ('../../tnk_func.php');                // TNK に依存する部分の関数を require_once している
require_once ('../../MenuHeader.php');              // TNK 全共通 menu class
access_log();                               // Script Name は自動取得

///// TNK 共用メニュークラスのインスタンスを作成
$menu = new MenuHeader(-1);                 // 認証チェック0=一般以上 戻り先=TOP_MENU タイトル未設定

////////////// サイト設定
$menu->set_site(INDEX_INDUST, 999);         // site_index=30(生産メニュー) site_id=999(未定)
////////////// リターンアドレス設定
// $menu->set_RetUrl(INDUST_MENU);             // 通常は指定する必要はない
//////////// タイトル名(ソースのタイトル名とフォームのタイトル名)
$menu->set_title('総材料費の見直し確認用');
//////////// 表題の設定
$menu->set_caption('総材料費の見直し確認用　(リニアで2007年2月から2010年2月までの売上製品が対象)<br>総材料費登録日の青色表示は生産計画は無いが最新の総材料費に見直したもの(クリックで明細表示)');
//////////// 呼出先のaction名とアドレス設定
// $menu->set_action('引当構成表の表示',   INDUST . 'material/allo_conf_parts_view.php');
// $menu->set_action('総材料費の登録',     INDUST . 'material/materialCost_entry.php');
//////////// リターンアドレスへのGETデーターセット
$menu->set_retGET('page_keep', 'on');

//////////// JavaScript Stylesheet File を必ず読み込ませる
$uniq = $menu->set_useNotCache('mtcheck');

?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=EUC-JP">
<meta http-equiv="Content-Style-Type" content="text/css">
<meta http-equiv="Content-Script-Type" content="text/javascript">
<title><?php echo $menu->out_title() ?></title>
<?php echo $menu->out_site_java() ?>
<?php echo $menu->out_css() ?>
<link rel='stylesheet' href='materialCheck.css?id=<?php echo $uniq ?>' type='text/css' media='screen'>
<style type='text/css'><!-- --></style>
<!-- <script type='text/javascript' src='materialCheck.js?<?php echo $uniq ?>'></script> -->
</head>
<!-- background-color:#d6d3ce; -->
<body style='overflow-y:hidden;'>
<center>
<?=$menu->out_title_border()?>
        
        <!----------------- ここは 前頁 次頁 のフォーム ---------------->
        <table width='100%' border='0' cellspacing='0' cellpadding='0'>
            <tr>
                <td align='center' class='caption_font'>
                    <?= $menu->out_caption(), "\n" ?>
                </td>
            </tr>
        </table>
        
<?php
echo "<iframe hspace='0' vspace='0' frameborder='0' scrolling='yes' src='materialCheckLinear_ViewHeader2.html?{$uniq}' name='header' align='center' width='98%' height='46' title='項目'>\n";
echo "    項目を表示しています。\n";
echo "</iframe>\n";
echo "<iframe hspace='0' vspace='0' frameborder='0' scrolling='yes' src='materialCheckLinear_ViewBody2.php?", $_SERVER['QUERY_STRING'], "&{$uniq}#mark' name='list' align='center' width='98%' height='80%' title='一覧'>\n";
echo "    一覧を表示しています。\n";
echo "</iframe>\n";
?>
</center>
</body>
<?php echo $menu->out_alert_java()?>
</html>
