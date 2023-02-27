<?php
//////////////////////////////////////////////////////////////////////////////
// 月次損益 照会 メニュー                                                   //
// Copyright (C) 2003-2021 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp      //
// Changed history                                                          //
// 2003/01/16 Created   profit_loss_query_menu.php                          //
// 2003/03/04 profit_loss_select.php から 照会の機能のみを抜き出した        //
//            kessan/profit_loss_submit.phpを呼出す(共用している)           //
// 2003/10/15 月次比較棚卸表を追加                                          //
// 2003/12/15 サイトメニューの表示 On / Off 機能を追加                      //
// 2005/02/14 MenuHeader class を使用して共通メニュー化及び認証方式へ変更   //
// 2005/06/15 原価率計算表を $menu->set_action()に追加                      //
// 2005/10/26 E_ALL→ESTRICT へ変更 <body style='overflow-y:hidden;' を追加 //
// 2007/10/10 getsuji_comp_invent.php → invent_comp/invent_comp_view.phpへ //
// 2008/10/07 CL経費差額比較表の追加                                   大谷 //
// 2009/08/19 旧ＣＬ商品別損益照会を追加                               大谷 //
// 2009/08/20 旧ＣＬ経費実績表照会を追加                                    //
//            メニュー追加の為、レイアウトを調整                       大谷 //
// 2010/01/15 損益対前月比較表を追加                                   大谷 //
// 2012/01/16 ２期比較表の照会を追加（テスト）                         大谷 //
// 2012/02/13 ２期比較表の照会範囲を2011年からに変更                        //
//            ２期比較表の照会を公開                                   大谷 //
// 2015/06/04 BLをLTに変更                                             大谷 //
// 2016/07/13 CLT商品別損益を追加                                      大谷 //
// 2017/06/08 focusのJavaScriptエラーを修正                            大谷 //
// 2017/09/08 製造原価計算を追加                                       大谷 //
// 2017/11/09 機工損益修正を10月で一括で行った損益照会を追加           大谷 //
// 2018/01/12 製造原価計算を公開                                       大谷 //
// 2018/05/29 決算報告書を追加（自分のみ）                             大谷 //
// 2018/06/12 勘定科目組替表を追加（自分のみ）                         大谷 //
// 2018/07/05 勘定科目組替表と決算報告書を公開                         大谷 //
// 2018/12/06 設備製作集計を全体に公開                                 大谷 //
// 2019/05/16 四半期の表示を四半期初めの月からに変更                   大谷 //
// 2020/01/27 減価償却費明細表を追加                                   大谷 //
// 2020/06/12 勘定科目組替表を追加（自分のみ）                         大谷 //
// 2021/05/31 商品別損益を2021/04以降ツールなしにしたため                   //
//            セグメント別損益10月一括を機工ありに                          //
//            (選択日付で分岐するが念のため)                           大谷 //
// 2021/08/02 $_SESSION['2ki_ym']のエラーに対応                        大谷 //
//////////////////////////////////////////////////////////////////////////////
ini_set('error_reporting', E_STRICT);       // E_STRICT=2048(php5) E_ALL=2047 debug 用
// ini_set('error_reporting', E_ALL);          // E_ALL='2047' debug 用
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
$menu->set_site(10, 13);                    // site_index=10(損益メニュー) site_id=13(月次損益照会メニュー)
////////////// リターンアドレス設定
// $menu->set_RetUrl(SALES_MENU);              // 通常は指定する必要はない
//////////// タイトル名(ソースのタイトル名とフォームのタイトル名)
$menu->set_title('月次損益 照会 メニュー');
//////////// 表題の設定
$menu->set_caption('月 次 損 益 照 会 の  対象年月の指定');
//////////// 呼出先のaction名とアドレス設定
$menu->set_action('原価率計算表',                   PL . 'profit_loss_cost_rate.php');
$menu->set_action('セグメント別損益',               PL . 'profit_loss_pl_act.php');
$menu->set_action('セグメント別損益10月一括',       PL . 'profit_loss_pl_act10.php');
$menu->set_action('セグメント別損益機工',           PL . 'profit_loss_pl_act_t-bk.php');
$menu->set_action('ＣＬ・商管 経費実績表',          PL . 'profit_loss_cl_keihi.php');
$menu->set_action('経費実績内訳',                   PL . 'profit_loss_keihi.php');
$menu->set_action('貸借対照表',                     PL . 'profit_loss_bs_act.php');
$menu->set_action('比較棚卸表',                     PL . 'invent_comp/invent_comp_view.php');
$menu->set_action('総平均棚卸入力',                 PL . 'profit_loss_invent_gross_average.php');
$menu->set_action('ＣＬ経費差額比較表',             PL . 'profit_loss_cl_keihi_compare.php');
$menu->set_action('旧ＣＬ商品別損益',               PL . 'profit_loss_pl_act_old.php');
$menu->set_action('旧ＣＬ経費実績表',               PL . 'profit_loss_cl_keihi_old.php');
$menu->set_action('ＢＬ 商品別損益',                PL . 'profit_loss_pl_act_bl.php');
$menu->set_action('特注・標準 商品別損益',          PL . 'profit_loss_pl_act_ctoku.php');
$menu->set_action('損益対前月比較表',               PL . 'profit_loss_pl_act_compare.php');
$menu->set_action('ＬＴ 商品別損益',                PL . 'profit_loss_pl_act_lt.php');
$menu->set_action('ＣＬＴ・試修・商管 商品別損益',  PL . 'profit_loss_pl_act_all.php');
$menu->set_action('試験・修理 商品別損益',          PL . 'profit_loss_pl_act_ss.php');
$menu->set_action('売上状況照会',                   PL . 'profit_loss_sales_view.php');

// ２期比較表
$menu->set_action('２期 本決算損益表',      PL . 'profit_loss_pl_act_2ki.php');
$menu->set_action('２期 貸借対照表',        PL . 'profit_loss_bs_act_2ki.php');
$menu->set_action('２期 ＣＬ商品別損益',    PL . 'profit_loss_pl_act_2ki_cl.php');
$menu->set_action('２期 経費実績内訳',      PL . 'profit_loss_keihi_2ki.php');
$menu->set_action('製造原価計算',           PL . 'manufacture_cost_total.php');
$menu->set_action('決算報告書',             PL . 'financial_report_view.php');
$menu->set_action('勘定科目組替表',         PL . 'account_transfer_view.php');
$menu->set_action('設備製作集計',           PL . 'machine_production_view.php');
$menu->set_action('減価償却費明細表',           PL . 'depreciation_statement/depreciation_statement_view.php');
$menu->set_action('勘定科目内訳明細書',         PL . 'account_statement_view.php');

// 消費税申告書
$menu->set_action('未払金計上仕入額',      PL . 'sales_tax_miharai_view.php');
$menu->set_action('中間納付確認',          PL . 'sales_tax_chukan_view.php');
$menu->set_action('消費税集計表',          PL . 'sales_tax_zeishukei_view.php');
$menu->set_action('控除税額計算表',        PL . 'sales_tax_koujyo_view.php');
$menu->set_action('消費税等計算表',        PL . 'sales_tax_syozei_allo_view.php');
$menu->set_action('消費税申告資料',        PL . 'sales_tax_syozei_shinkoku_view.php');
$menu->set_action('確定申告書第1表',       PL . 'print/sales_tax_kakutei_shinkoku1_pdf.php');
$menu->set_action('第2表',                 PL . 'print/sales_tax_kakutei_shinkoku2_pdf.php');
$menu->set_action('付表1-1',               PL . 'print/sales_tax_kakutei_fuhyo1-1_pdf.php');
$menu->set_action('付表1-2',               PL . 'print/sales_tax_kakutei_fuhyo1-2_pdf.php');
$menu->set_action('付表2-1',               PL . 'print/sales_tax_kakutei_fuhyo2-1_pdf.php');
$menu->set_action('付表2-2',               PL . 'print/sales_tax_kakutei_fuhyo2-2_pdf.php');

//////////// 対象年月のセッションデータ取得
if (isset($_SESSION['pl_ym'])) {
    $pl_ym = $_SESSION['pl_ym']; 
} else {
    $pl_ym = date("Ym");        // セッションデータがない場合の初期値(前月)
    if (substr($pl_ym,4,2) != 01) {
        $pl_ym--;
    } else {
        $pl_ym = $pl_ym - 100;
        $pl_ym = $pl_ym + 11;   // 前年の12月にセット
    }
}

//////////// 対象年月のセッションデータ取得
if (isset($_SESSION['2ki_ym'])) {
    $pl_ym_2ki = $_SESSION['2ki_ym']; 
} else {
    $pl_ym_2ki = date("Ym");        // セッションデータがない場合の初期値(前月)
    if (substr($pl_ym_2ki,4,2) != 01) {
        $pl_ym_2ki--;
    } else {
        $pl_ym_2ki = $pl_ym_2ki - 100;
        $pl_ym_2ki = $pl_ym_2ki + 11;   // 前年の12月にセット
    }
    $_SESSION['2ki_ym'] = $pl_ym_2ki;
}

/////////// HTML Header を出力してキャッシュを制御
$menu->out_html_header();
?>
<!DOCTYPE html>
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
    // document.pl_form.pl_ym.focus();
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
                    <input class='pt10b' type='submit' name='pl_name' value='セグメント別損益'>
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
                <td class='winbox' align='center' bgcolor='#ffffc6'>
                    <input class='pt10b' type='submit' name='pl_name' value='セグメント別損益機工'>
                </td>
                <td class='winbox' align='center' bgcolor='#ffffc6'>
                    <input class='pt10b' type='submit' name='pl_name' value='試験・修理 商品別損益'>
                </td>
                <td class='winbox' align='center' bgcolor='#ffffc6'>
                    <input class='pt10b' type='submit' name='pl_name' value='特注・標準 商品別損益'>
                </td>
                <td class='winbox' align='center' bgcolor='#ffffc6'>
                    <input class='pt10b' type='submit' name='pl_name' value='売上状況照会'>
                </td>
            </tr>
            <tr>
                <td class='winbox' align='center'>
                    <input class='pt11b' type='submit' name='pl_name' value='総平均棚卸入力'>
                </td>
                <td class='winbox' align='center'>
                    <input class='pt11b' type='submit' name='pl_name' value='ＢＬ 商品別損益'>
                </td>
                <td class='winbox' align='center'>
                    <input class='pt11b' type='submit' name='pl_name' value='旧ＣＬ経費実績表'>
                </td>
                <td class='winbox' align='center'>
                    <input class='pt11b' type='submit' name='pl_name' value='旧ＣＬ商品別損益'>
                </td>
            </tr>
        </table>
            </td></tr>
        </table> <!-- ダミーEnd -->
        </form>
        <BR>
        <form name='pl_form' action='/kessan/profit_loss_submit.php' method='post'>
        <table bgcolor='#d6d3ce'  border='1' cellspacing='0' cellpadding='6'>
            <tr><td> <!-- ダミー(デザイン用) -->
        <table class='winbox_field' bgcolor='#d6d3ce' border='1' cellspacing='0' cellpadding='3'>
            <tr>
                <td class='winbox' align='center' colspan='5'>
                    <span class='pt12b'>
                        ２　期　比　較　表　の　対象年月の指定
                        <select name='2ki_ym' class='pt11b'>
                            <?php
                            $ym_2ki = date("Ym");
                            while(1) {
                                if (substr($ym_2ki,4,2)!=01) {
                                    $ym_2ki--;
                                } else {
                                    $ym_2ki = $ym_2ki - 100;
                                    $ym_2ki = $ym_2ki + 11;
                                }
                                if ($pl_ym_2ki == $ym_2ki) {                                    
                                    $ki = Ym_to_tnk($ym_2ki);
                                    $tuki_chk = substr($ym_2ki,4,2);
                                    if ($tuki_chk == 3 || $tuki_chk == 6 || $tuki_chk == 9 || $tuki_chk == 12) {
                                        if ($tuki_chk >= 1 && $tuki_chk <= 3) {
                                            printf("<option value='%d' selected>第%s期 第４四半期</option>\n",$ym_2ki,$ki);
                                            $init_flg = 0;
                                        } elseif ($tuki_chk >= 4 && $tuki_chk <= 6) {
                                            printf("<option value='%d' selected>第%s期 第１四半期</option>\n",$ym_2ki,$ki);
                                            $init_flg = 0;
                                        } elseif ($tuki_chk >= 7 && $tuki_chk <= 9) {
                                            printf("<option value='%d' selected>第%s期 第２四半期</option>\n",$ym_2ki,$ki);
                                            $init_flg = 0;
                                        } elseif ($tuki_chk >= 10) {
                                            printf("<option value='%d' selected>第%s期 第３四半期</option>\n",$ym_2ki,$ki);
                                            $init_flg = 0;
                                        }
                                    }
                                } else {
                                    $ki = Ym_to_tnk($ym_2ki);
                                    $tuki_chk = substr($ym_2ki,4,2);
                                    if ($tuki_chk == 3 || $tuki_chk == 6 || $tuki_chk == 9 || $tuki_chk == 12) {
                                        if ($tuki_chk >= 1 && $tuki_chk <= 3) {
                                            printf("<option value='%d'>第%s期 第４四半期</option>\n",$ym_2ki,$ki);
                                        } elseif ($tuki_chk >= 4 && $tuki_chk <= 6) {
                                            printf("<option value='%d'>第%s期 第１四半期</option>\n",$ym_2ki,$ki);
                                        } elseif ($tuki_chk >= 7 && $tuki_chk <= 9) {
                                            printf("<option value='%d'>第%s期 第２四半期</option>\n",$ym_2ki,$ki);
                                        } elseif ($tuki_chk >= 10) {
                                            printf("<option value='%d'>第%s期 第３四半期</option>\n",$ym_2ki,$ki);
                                        }
                                    }
                                }
                                if ($ym_2ki <= 201006)
                                    break;
                            }
                            ?>
                        </select>
                    </span>
                </td>
            </tr>
            <tr>
                <td class='winbox' align='center' bgcolor='#ffffc6'>
                    <input class='pt11b' type='submit' name='pl_name' value='２期 本決算損益表'>
                </td>
                <td class='winbox' align='center' bgcolor='#ffffc6'>
                    <input class='pt10b' type='submit' name='pl_name' value='２期 貸借対照表'>
                </td>
                <td class='winbox' align='center' bgcolor='#ffffc6'>
                    <input class='pt10b' type='submit' name='pl_name' value='２期 ＣＬ商品別損益'>
                </td>
                <td class='winbox' align='center' bgcolor='#ffffc6'>
                    <input class='pt11b' type='submit' name='pl_name' value='２期 経費実績内訳'>
                </td>
            </tr>
            <tr>
                <td class='winbox' align='center'>　</td>
                
                <td class='winbox' align='center'>　</td>
                
                <td class='winbox' align='center'>　</td>
                
                <td class='winbox' align='center'>　</td>
            </tr>
            <tr>
                <td class='winbox' align='center' bgcolor='#ffffc6'>
                    <input class='pt11b' type='submit' name='pl_name' value='製造原価計算'>
                </td>
                <td class='winbox' align='center' bgcolor='#ffffc6'>
                    <input class='pt11b' type='submit' name='pl_name' value='決算報告書'>
                </td>
                <td class='winbox' align='center' bgcolor='#ffffc6'>
                    <input class='pt11b' type='submit' name='pl_name' value='勘定科目組替表'>
                </td>
                <td class='winbox' align='center' bgcolor='#ffffc6'>
                    <input class='pt11b' type='submit' name='pl_name' value='減価償却費明細表'>
                </td>
            </tr>
            <tr>
                <td class='winbox' align='center'>　</td>
                
                <td class='winbox' align='center'>　</td>
                <td class='winbox' align='center' bgcolor='#ffffc6'>
                    <input class='pt11b' type='submit' name='pl_name' value='勘定科目内訳明細書'>
                </td>
                <?php if ($_SESSION['User_ID'] == '300144') { ?>
                <?php } else { ?>
                <td class='winbox' align='center'>　</td>
                <?php } ?>
                <td class='winbox' align='center' bgcolor='#ffffc6'>
                    <input class='pt11b' type='submit' name='pl_name' value='設備製作集計'>
                </td>
            </tr>
        </table>
            </td></tr>
        </table> <!-- ダミーEnd -->
        </form>
        <BR>
        <form name='pl_form' action='/kessan/profit_loss_submit.php' method='post'>
        <table bgcolor='#d6d3ce'  border='1' cellspacing='0' cellpadding='6'>
            <tr><td> <!-- ダミー(デザイン用) -->
        <table class='winbox_field' bgcolor='#d6d3ce' border='1' cellspacing='0' cellpadding='3'>
            <tr>
                <td class='winbox' align='center' colspan='5'>
                    <span class='pt12b'>
                        消　費　税　申　告　書　の　対象年月の指定
                        <select name='2ki_ym' class='pt11b'>
                            <?php
                            $ym_2ki = date("Ym");
                            while(1) {
                                if (substr($ym_2ki,4,2)!=01) {
                                    $ym_2ki--;
                                } else {
                                    $ym_2ki = $ym_2ki - 100;
                                    $ym_2ki = $ym_2ki + 11;
                                }
                                if ($pl_ym_2ki == $ym_2ki) {                                    
                                    $ki = Ym_to_tnk($ym_2ki);
                                    $tuki_chk = substr($ym_2ki,4,2);
                                    if ($tuki_chk == 3 || $tuki_chk == 6 || $tuki_chk == 9 || $tuki_chk == 12) {
                                        if ($tuki_chk >= 1 && $tuki_chk <= 3) {
                                            printf("<option value='%d' selected>第%s期 第４四半期</option>\n",$ym_2ki,$ki);
                                            $init_flg = 0;
                                        }
                                    }
                                } else {
                                    $ki = Ym_to_tnk($ym_2ki);
                                    $tuki_chk = substr($ym_2ki,4,2);
                                    if ($tuki_chk == 3 || $tuki_chk == 6 || $tuki_chk == 9 || $tuki_chk == 12) {
                                        if ($tuki_chk >= 1 && $tuki_chk <= 3) {
                                            printf("<option value='%d'>第%s期 第４四半期</option>\n",$ym_2ki,$ki);
                                        }
                                    }
                                }
                                if ($ym_2ki <= 202102)
                                    break;
                            }
                            ?>
                        </select>
                    </span>
                </td>
            </tr>
            <tr>
                <td class='winbox' align='center' bgcolor='#ffffc6'>
                    <input class='pt11b' type='submit' name='pl_name' value='未払金計上仕入額'>
                </td>
                <td class='winbox' align='center' bgcolor='#ffffc6'>
                    <input class='pt10b' type='submit' name='pl_name' value='中間納付確認'>
                </td>
                <td class='winbox' align='center' bgcolor='#ffffc6'>
                    <input class='pt10b' type='submit' name='pl_name' value='消費税集計表'>
                </td>
                <td class='winbox' align='center' bgcolor='#ffffc6'>
                    <input class='pt10b' type='submit' name='pl_name' value='控除税額計算表'>
                </td>
            </tr>
            <tr>
                <td class='winbox' align='center'>
                    <input class='pt10b' type='submit' name='pl_name' value='消費税等計算表'>
                </td>
                
                <td class='winbox' align='center'>　</td>
                
                <td class='winbox' align='center'>
                    <input class='pt10b' type='submit' name='pl_name' value='消費税申告資料'>
                </td>
                
                <td class='winbox' align='center'>　</td>
            </tr>
            <tr>
                <td class='winbox' align='center'>
                    <input class='pt10b' type='submit' name='pl_name' value='確定申告書第1表'>
                </td>
                <td class='winbox' align='center'>
                    <input class='pt10b' type='submit' name='pl_name' value='第2表'>
                </td>
                <td class='winbox' align='center'>
                    <input class='pt10b' type='submit' name='pl_name' value='付表1-1'>
                </td>
                <td class='winbox' align='center'>
                    <input class='pt10b' type='submit' name='pl_name' value='付表1-2'>
                </td>
            </tr>
            <tr>
                <td class='winbox' align='center'>
                    <input class='pt10b' type='submit' name='pl_name' value='付表2-1'>
                </td>
                
                <td class='winbox' align='center'>
                    <input class='pt10b' type='submit' name='pl_name' value='付表2-2'>
                </td>
                <td class='winbox' align='center'>　</td>
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
