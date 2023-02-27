<?php
//////////////////////////////////////////////////////////////////////////////
// 月次損益 照会 メニュー                                                   //
// Copyright (C) 2003-2013 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp      //
// Changed history                                                          //
// 2003/01/16 Created   profit_loss_query_menu.php                          //
// 2003/03/04 profit_loss_select.php から 照会の機能のみを抜き出した        //
//            kessan/profit_loss_submit.phpを呼出す(共用している)           //
// 2003/10/15 月次比較棚卸表を追加                                          //
// 2003/12/12 defineされた定数でディレクトリとメニューを使用して管理する    //
//            ob_start('ob_gzhandler') を追加                               //
// 2003/12/15 サイトメニューの表示 On / Off 機能を追加                      //
// 2005/02/14 MenuHeader class を使用して共通メニュー化及び認証方式へ変更   //
// 2005/06/15 原価率計算表を $menu->set_action()に追加                      //
// 2005/11/02 kessan/ のファイルを変更したが こちらを変更していないので修正 //
// 2013/01/29 kessan/ のファイルを変更したが こちらを変更していないので修正 //
//            こちらには２期比較表入れていない                         大谷 //
// 2015/07/08 BL損益をLT損益に変更                                     大谷 //
//////////////////////////////////////////////////////////////////////////////
ini_set('error_reporting', E_ALL);          // E_ALL='2047' debug 用
// ini_set('display_errors', '1');             // Error 表示 ON debug 用 リリース後コメント
// ini_set('zend.ze1_compatibility_mode', '1');    // zend 1.X コンパチ php4の互換モード
// ini_set('implicit_flush', 'off');           // echo print で flush させない(遅くなるため) CLI版
// ini_set('max_execution_time', 1200);        // 最大実行時間=20分 WEB CGI版
ob_start('ob_gzhandler');                   // 出力バッファをgzip圧縮
session_start();                            // ini_set()の次に指定すること Script 最上行

require_once ('../function.php');           // define.php と pgsql.php を require_once している
require_once ('../tnk_func.php');           // TNK に依存する部分の関数を require_once している
require_once ('../MenuHeader.php');         // TNK 全共通 menu class
access_log();                               // Script Name は自動取得

///// TNK 共用メニュークラスのインスタンスを作成
$menu = new MenuHeader(0);                  // 認証チェック0=一般以上 戻り先=TOP_MENU タイトル未設定

////////////// サイト設定
$menu->set_site( 1,  8);                    // site_index=1(売上メニュー) site_id=60(損益照会メニュー)
////////////// リターンアドレス設定
// $menu->set_RetUrl(SALES_MENU);              // 通常は指定する必要はない
//////////// タイトル名(ソースのタイトル名とフォームのタイトル名)
$menu->set_title('月次損益 照会 メニュー');
//////////// 表題の設定
$menu->set_caption('月 次 損 益 照 会 の  対象年月の指定');
//////////// 呼出先のaction名とアドレス設定
$menu->set_action('原価率計算表',   PL . 'profit_loss_cost_rate.php');
$menu->set_action('ＣＬ・試修・商管 商品別損益', PL . 'profit_loss_pl_act.php');
$menu->set_action('ＣＬ・商管 経費実績表', PL . 'profit_loss_cl_keihi.php');
$menu->set_action('経費実績内訳',   PL . 'profit_loss_keihi.php');
$menu->set_action('貸借対照表',     PL . 'profit_loss_bs_act.php');
$menu->set_action('比較棚卸表',     PL . 'invent_comp/invent_comp_view.php');
$menu->set_action('総平均棚卸入力', PL . 'profit_loss_invent_gross_average.php');
$menu->set_action('ＣＬ経費差額比較表', PL . 'profit_loss_cl_keihi_compare.php');
$menu->set_action('旧ＣＬ商品別損益', PL . 'profit_loss_pl_act_old.php');
$menu->set_action('旧ＣＬ経費実績表', PL . 'profit_loss_cl_keihi_old.php');
$menu->set_action('ＢＬ 商品別損益',         PL . 'profit_loss_pl_act_bl.php');
$menu->set_action('特注・標準 商品別損益',         PL . 'profit_loss_pl_act_ctoku.php');
$menu->set_action('損益対前月比較表',         PL . 'profit_loss_pl_act_compare.php');
$menu->set_action('ＬＴ 商品別損益',         PL . 'profit_loss_pl_act_lt.php');

//////////// 対象年月のセッションデータ取得
if (isset($_SESSION['pl_ym'])) {
    $pl_ym = $_SESSION['pl_ym']; 
} else {
    $pl_ym = date('Ym');        // セッションデータがない場合の初期値(前月)
    if (substr($pl_ym,4,2) != 01) {
        $pl_ym--;
    } else {
        $pl_ym = $pl_ym - 100;
        $pl_ym = $pl_ym + 11;   // 前年の12月にセット
    }
}

/////////// HTML Header を出力してキャッシュを制御
$menu->out_html_header();
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<meta http-equiv="Content-Style-Type" content="text/css">
<title><?= $menu->out_title() ?></title>
<?= $menu->out_site_java() ?>
<?= $menu->out_css() ?>

<style type='text/css'>
<!--
select {
    background-color:   teal;
    color:              white;
}
/** font-weight: normal;        **/
/** font-weight: 400;    と同じ **/
/** font-weight: bold;          **/
/** font-weight: 700;    と同じ **/
/**         100～900まで100刻み **/
.pt10b {
    font-size:   10.5pt;
    font-weight: bold;
}
.pt11b {
    font-size:   11pt;
    font-weight: bold;
}
.pt12b {
    font-size:   12pt;
    font-weight: bold;
    font-family: monospace;
}
-->
</style>
<script language='JavaScript'>
<!--
/* 初期入力フォームのエレメントにフォーカスさせる */
function set_focus() {
    // document.body.focus();   // F2/F12キーを有効化する対応
    // document.mhForm.backwardStack.focus();  // 上記はIEのみのためNN対応
    document.pl_form.pl_ym.focus();
}
// -->
</script>
</head>
<body onLoad='set_focus()'>
    <center>
<?= $menu->out_title_border() ?>
        
        <form name='pl_form' action='/kessan/profit_loss_submit.php' method='post'>
        <table bgcolor='#d6d3ce'  border='1' cellspacing='0' cellpadding='6'>
            <tr><td> <!-- ダミー(デザイン用) -->
        <table class='winbox_field' bgcolor='#d6d3ce' border='1' cellspacing='0' cellpadding='3'>
            <tr>
                <td class='winbox' align='center' colspan='5'>
                    <span class='pt12b'>
                        月　次　損　益　照　会　の　対象年月の指定
                        <select name='pl_ym' class='pt11b'>
                            <?php
                            $ym = date("Ym");
                            while(1) {
                                if (substr($ym,4,2)!=01) {
                                    $ym--;
                                } else {
                                    $ym = $ym - 100;
                                    $ym = $ym + 11;
                                }
                                if ($pl_ym == $ym) {
                                    printf("<option value='%d' selected>%s年%s月</option>\n",$ym,substr($ym,0,4),substr($ym,4,2));
                                    $init_flg = 0;
                                } else
                                    printf("<option value='%d'>%s年%s月</option>\n",$ym,substr($ym,0,4),substr($ym,4,2));
                                if ($ym <= 200010)
                                    break;
                            }
                            ?>
                        </select>
                    </span>
                </td>
            </tr>
            <tr>
                <td class='winbox' align='center' bgcolor='#ffffc6'>
                    <input class='pt11b' type='submit' name='pl_name' value='経費実績内訳'>
                </td>
                <td class='winbox' align='center' bgcolor='#ffffc6'>
                    <input class='pt10b' type='submit' name='pl_name' value='ＣＬ・商管 経費実績表'>
                </td>
                <td class='winbox' align='center' bgcolor='#ffffc6'>
                    <input class='pt10b' type='submit' name='pl_name' value='ＣＬ・試修・商管 商品別損益'>
                </td>
                <td class='winbox' align='center' bgcolor='#ffffc6'>
                    <input class='pt11b' type='submit' name='pl_name' value='貸借対照表'>
                </td>
            </tr>
            <tr>
                <td class='winbox' align='center' bgcolor='#ffffc6'>
                    <input class='pt10b' type='submit' name='pl_name' value='ＣＬ経費差額比較表'>
                </td>
                <td class='winbox' align='center' bgcolor='#ffffc6'>
                    <input class='pt11b' type='submit' name='pl_name' value='原価率計算表'>
                </td>
                <td class='winbox' align='center' bgcolor='#ffffc6'>
                    <input class='pt11b' type='submit' name='pl_name' value='比較棚卸表'>
                </td>
                <td class='winbox' align='center' bgcolor='#ffffc6'>
                    <input class='pt10b' type='submit' name='pl_name' value='損益対前月比較表'>
                </td>
            </tr>
            <tr>
                <td class='winbox' align='center'>　</td>
                <td class='winbox' align='center' bgcolor='#ffffc6'>
                    <input class='pt10b' type='submit' name='pl_name' value='ＬＴ 商品別損益'>
                </td>
                <td class='winbox' align='center' bgcolor='#ffffc6'>
                    <input class='pt10b' type='submit' name='pl_name' value='特注・標準 商品別損益'>
                </td>
                <td class='winbox' align='center'>　</td>
            </tr>
        </table>
            </td></tr>
        </table> <!-- ダミーEnd -->
        </form>
    </center>
</body>
<?=$menu->out_alert_java()?>
</html>
<?php
ob_end_flush();                 // 出力バッファをgzip圧縮 END
?>
