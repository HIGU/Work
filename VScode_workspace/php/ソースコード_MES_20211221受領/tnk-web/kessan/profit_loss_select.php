<?php
//////////////////////////////////////////////////////////////////////////////
// 月次損益関係 作成・照会 選択フォーム                                     //
// Copyright (C) 2003-2021      Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp //
// Changed history                                                          //
// 2003/01/16 Created   profit_loss_select.php                              //
// 2003/01/27 AS/400 との個別データリンクメニューを追加                     //
// 2003/02/06 データのリンク時に確認ダイアログを追加 JavaScript             //
// 2003/02/07 経費配賦処理時に確認ダイアログと識別のため薄い赤に変更        //
//     データ更新の準備作業フォーム追加 作業手順追加 1 AS 2 配賦 3 実行     //
// 2003/02/19 文字サイズをブラウザーで変更できなくした title-font 等        //
// 2003/02/22 棚卸高調整・仕入高(要素別買掛)調整メニュー追加                //
// 2003/02/23 date("Y/m/d H:m:s") → H:i:s のミス修正                       //
// 2003/02/24 売上原価調整入力のメニューを追加                              //
// 2003/02/26 業務委託収入の入力をメニューに追加                            //
// 2003/03/04 AS/400 との個別データリンクで AS/400 のメニュー説明を追加     //
// 2003/03/10 売上高の調整入力メニューを追加                                //
// 2003/09/27 月次 比較棚卸表の データ取込み と 照会 を追加                 //
// 2003/10/10 月次データを決算データに置換するためデータクリアーを追加      //
// 2003/12/15 サイトメニューの表示 On / Off 機能を追加                      //
// 2005/05/30 MenuHeader class を使用して共通メニュー化及び認証方式へ変更   //
// 2005/08/29 原価率計算表を $menu->set_action()に追加                      //
// 2005/10/26 ＣＬ商品別損益を上記同様追加 E_ALL→E_STRICT へ変更           //
// 2007/10/09 set_focus()をコメント phpのショットカットを標準タグへ その他  //
// 2007/10/10 セグメント別 損益計算書のデータ取込メニューを追加             //
// 2009/08/18 物流・試修損益登録を追加                                 大谷 //
// 2009/08/19 物流を商管に変更                                         大谷 //
//            旧ＣＬ商品別損益照会を追加                               大谷 //
// 2009/08/20 旧ＣＬ経費実績表照会を追加                                    //
//            メニュー追加の為、レイアウトを調整                       大谷 //
// 2009/08/21 ＢＬ・試験修理 商品別損益照会を追加                      大谷 //
// 2009/12/09 試験修理（ＣＬ）商品別損益照会を追加                     大谷 //
// 2010/01/14 BL商品別損益の位置を変更、損益対前月比較表を追加         大谷 //
// 2010/01/15 商品別損益作成テスト用アイコンを表示（コメント化）       大谷 //
// 2010/01/27 商品別テスト用リンクを復活（テスト完了後コメント化）     大谷 //
// 2010/02/05 BL試修損益計算書未使用のためリンク解除                   大谷 //
// 2013/03/05 2013/02・03で調整を先行入力する為、日付の表示を変更      大谷 //
// 2015/06/04 BLをLTに変更                                             大谷 //
// 2016/07/13 CLTの商品別損益を追加(セグメント別損益へ)                大谷 //
// 2016/07/22 試験修理商品別損益をＣＬから耐久・修理へ変更             大谷 //
// 2017/11/08 LTの商品別損益を一覧から削除(セグメント別損益で対応)     大谷 //
// 2017/11/09 機工損益修正を10月で一括で行った損益照会を追加           大谷 //
// 2020/01/27 四半期毎の減価償却費明細表のデータ取り込みを追加         大谷 //
// 2021/08/02 $_SESSION['2ki_ym']のエラーに対応                        大谷 //
//////////////////////////////////////////////////////////////////////////////
// ini_set('error_reporting', E_STRICT);       // E_STRICT=2048(php5) E_ALL=2047 debug 用
// ini_set('error_reporting', E_ALL);          // E_ALL='2047' debug 用
// ini_set('display_errors', '1');             // Error 表示 ON debug 用 リリース後コメント
ob_start('ob_gzhandler');                   // 出力バッファをgzip圧縮
session_start();                            // ini_set()の次に指定すること Script 最上行

require_once ('../function.php');           // define.php と pgsql.php を require_once している
require_once ('../tnk_func.php');           // TNK に依存する部分の関数を require_once している
require_once ('../MenuHeader.php');         // TNK 全共通 menu class
access_log();                               // Script Name は自動取得

///// TNK 共用メニュークラスのインスタンスを作成
$menu = new MenuHeader(0);                  // 認証チェック0=一般以上 戻り先=TOP_MENU タイトル未設定

////////////// サイト設定
$menu->set_site(10,  7);                    // site_index=10(損益メニュー) site_id= 7(損益作成メニュー)

//////////// タイトル名(ソースのタイトル名とフォームのタイトル名)
$menu->set_title('月次損益関係 選択フォーム');
//////////// 表題の設定
$menu->set_caption('対象年月の指定');
//////////// 呼出先のaction名とアドレス設定
$menu->set_action('原価率計算表',           PL . 'profit_loss_cost_rate.php');
$menu->set_action('セグメント別損益',         PL . 'profit_loss_pl_act.php');
$menu->set_action('セグメント別損益10月一括',         PL . 'profit_loss_pl_act10.php');
$menu->set_action('ＣＬ・商管 経費実績表',         PL . 'profit_loss_cl_keihi.php');
$menu->set_action('経費実績内訳',           PL . 'profit_loss_keihi.php');
$menu->set_action('貸借対照表',             PL . 'profit_loss_bs_act.php');
$menu->set_action('比較棚卸表取込',         PL . 'invent_comp/invent_comp_get_form.php');
$menu->set_action('比較棚卸表',             PL . 'invent_comp/invent_comp_view.php');
$menu->set_action('商管・試修損益登録',     PL . 'profit_loss_nkb_input.php');
$menu->set_action('セグメント別',           PL . 'pl_segment/pl_segment_get_form.php');
$menu->set_action('ＢＬ・試修 商品別損益',         PL . 'profit_loss_pl_act_bls.php');
$menu->set_action('特注・標準 商品別損益',         PL . 'profit_loss_pl_act_ctoku.php');
$menu->set_action('試験・修理 商品別損益',         PL . 'profit_loss_pl_act_ss.php');
$menu->set_action('旧ＣＬ商品別損益',      PL . 'profit_loss_pl_act_old.php');
$menu->set_action('旧ＣＬ経費実績表',       PL . 'profit_loss_cl_keihi_old.php');
$menu->set_action('Ｌ人員比率計算',     PL . 'profit_loss_bls_input.php');
$menu->set_action('Ｃ人員比率計算',     PL . 'profit_loss_ctoku_input.php');
$menu->set_action('ＢＬ 商品別損益',         PL . 'profit_loss_pl_act_bl.php');
$menu->set_action('損益対前月比較表',         PL . 'profit_loss_pl_act_compare.php');
$menu->set_action('ＣＬ経費差額比較表',         PL . 'profit_loss_cl_keihi_compare.php');
$menu->set_action('商品別テスト',         PL . 'profit_loss_cl_keihi_compare.php');
$menu->set_action('全社人員比率計算',     PL . 'profit_loss_staff_input.php');
$menu->set_action('ＬＴ 商品別損益',         PL . 'profit_loss_pl_act_lt.php');
$menu->set_action('ＣＬＴ・試修・商管 商品別損益',         PL . 'profit_loss_pl_act_all.php');
$menu->set_action('減価償却費明細表取込',         PL . 'depreciation_statement/depreciation_statement_get_form.php');
$menu->set_action('減価償却費明細表',         PL . 'depreciation_statement/depreciation_statement_view.php');

///////////// 自スクリプト名を取得
$current_script = $menu->out_self();

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
if ($pl_ym >= 202104) {
    $menu->set_action('セグメント別損益',         PL . 'profit_loss_pl_act.php');
} else {
    $menu->set_action('セグメント別損益',         PL . 'profit_loss_pl_act_t.php');
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
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=EUC-JP">
<meta http-equiv="Content-Style-Type" content="text/css">
<meta http-equiv="Content-Script-Type" content="text/javascript">
<title><?php echo $menu->out_title() ?></title>
<?php echo $menu->out_site_java() ?>
<?php echo $menu->out_css() ?>

<script language='JavaScript'>
<!--
/* 初期入力フォームのエレメントにフォーカスさせる */
function set_focus() {
    // document.body.focus();   // F2/F12キーを有効化する対応
    // document.mhForm.backwardStack.focus();  // 上記はIEのみのため、こちらに変更しNN対応
}
function as_ftp_click(obj) {
    return confirm("AS/400との一括データリンクを実行します。\n既にデータがある場合は上書きされます。\n元には戻せません。");
}
function act_allo_click(obj) {
    return confirm("経費のＣＬ配賦率を計算します。\nよろしいですか？");
}
function act_save_click(obj) {
    return confirm("経費の配賦処理を実行します。\nよろしいですか？");
}
function cl_pl_click(obj) {
    return confirm("ＣＬ商品別 損益計算を実行します。\nよろしいですか？");
}
function data_update_submit(obj){
    var YM = obj.pl_ym.value;
    if (confirm("月次年月は 「 " + YM + "」 です。間違いありませんか？")) {
        return confirm("本当に実行していいですね？");
    }
    return false
}
function as_ftp_submit(obj){
    var YM = obj.pl_ym.value;
    if (confirm(YM+" 月次 のAS/400とのデータリンクを実行します。\n既にデータがある場合は上書きされます。\n元には戻せません。")) {
        return confirm("本当に実行していいですね？");
    }
    return false
}
function monthly_clear(obj){
    var YM = document.clear.pl_ym.value;
    var name = obj.value;
    if (confirm("[ "+YM+" ] 月次の "+name+" を実行します。\n宜しいですか？")) {
        return confirm("本当に実行していいですね？");
    }
    return false
}
// -->
</script>
<style type='text/css'>
<!--
select {
    background-color:teal;
    color:white;
    font:bold 11pt;
}
/** font-weight: normal;        **/
/** font-weight: 400;    と同じ **/
/** font-weight: bold;          **/
/** font-weight: 700;    と同じ **/
/**         100〜900まで100刻み **/
.pt11 {
    font-size:11pt;
}
.pt11b {
    font-size: 10pt;
}
.pt12b {
    font-size:   11pt;
    font-weight: bold;
}
.explain_font {
    font-size: 8.5pt;
    font-family: monospace;
}
-->
</style>
</head>
<body onLoad='set_focus()'>
    <center>
<?php echo $menu->out_title_border() ?>
        
        <form action='profit_loss_submit.php' method='post' onSubmit='return data_update_submit(this)'>
            <table bgcolor='#d6d3ce' align='center' border='1' cellspacing='0' cellpadding='3'>
                <tr><td> <!----------- ダミー(デザイン用) ------------>
            <table width='100%'class='winbox_field' bgcolor='#d6d3ce' align='center' border='1' cellspacing='0' cellpadding='3'>
                <tr>
                    <td align='center' colspan='4' class='winbox'>
                        <span class='pt12b'>
                        月次データ更新の作業　<?php echo $menu->out_caption()?>
                        </span>
                        <select name='pl_ym' class='pt12b'>>
                            <?php
                            $ym = date('Ym');
                            while(1) {
                                if (substr($ym, 4, 2) != 01) {
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
                    </td>
                </tr>
                <tr>
                    <td align='center' bgcolor='#ffa4a4' class='winbox'>
                        <input class='pt11b' type='submit' name='pl_name' value='1 AS/400→TNK' onClick='return as_ftp_click(this)'>
                    </td>
                    <td align='center' bgcolor='#ffa4a4' class='winbox'>
                        <input class='pt11b' type='submit' name='pl_name' value='2 CL配賦率計算' onClick='return act_allo_click(this)'>
                    </td>
                    <td align='center' bgcolor='#ffa4a4' class='winbox'>
                        <input class='pt11b' type='submit' name='pl_name' value='3 経費配賦実行' onClick='return act_save_click(this)'>
                    </td>
                    <td align='center' bgcolor='#ffa4a4' class='winbox'>
                        <input class='pt11b' type='submit' name='pl_name' value='4 棚卸高入力'>
                    </td>
                </tr>
                <tr>
                    <td align='center' bgcolor='#ffa4a4' class='winbox'>
                        <input class='pt11b' type='submit' name='pl_name' value='5 棚卸高調整'>
                    </td>
                    <td align='center' bgcolor='#ffa4a4' class='winbox'>
                        <input class='pt11b' type='submit' name='pl_name' value='6 仕入高調整'>
                    </td>
                    <td align='center' bgcolor='#ffa4a4' class='winbox'>
                        <input class='pt11b' type='submit' name='pl_name' value='7 売上原価調整'>
                    </td>
                    <td align='center' bgcolor='#ffa4a4' class='winbox'>
                        <input class='pt11b' type='submit' name='pl_name' value='8 業務委託入力'>
                    </td>
                </tr>
                <tr>
                    <td align='center' bgcolor='#ffa4a4' class='winbox'>
                        <input class='pt11b' type='submit' name='pl_name' value='9 売上高調整'>
                    </td>
                    <td align='center' bgcolor='#ffa4a4' class='winbox'>
                        <input class='pt11b' type='submit' name='pl_name' value='10 ＣＬ損益計算' onClick='return cl_pl_click(this)'>
                    </td>
                    <td align='center' bgcolor='#ffa4a4' class='winbox'>
                        　
                    </td>
                    <td align='center' bgcolor='#ffa4a4' class='winbox'>
                        　
                    </td>
                </tr>
                <tr>
                    <td align='center' bgcolor='#ffa4a4' class='winbox'>
                        <input class='pt11b' type='submit' name='pl_name' value='11 商管・試修損益登録'>
                    </td>
                    <td align='center' bgcolor='#ffa4a4' class='winbox'>
                        <input class='pt11b' type='submit' name='pl_name' value='12 Ｌ人員比率計算'>
                    </td>
                    <td align='center' bgcolor='#ffa4a4' class='winbox'>
                        <input class='pt11b' type='submit' name='pl_name' value='13 Ｃ人員比率計算'>
                    </td>
                    <td align='center' bgcolor='#ffa4a4' class='winbox'>
                        <input class='pt11b' type='submit' name='pl_name' value='14 全社人員比率計算'>
                    </td>
                </tr>
            </table>
                </td></tr>
            </table> <!----------------- ダミーEnd ------------------>
        </form>
        
        <br>
        
        <form action='profit_loss_submit.php' method='post'>
            <table bgcolor='#d6d3ce' align='center' border='1' cellspacing='0' cellpadding='3'>
                <tr><td> <!----------- ダミー(デザイン用) ------------>
            <table width='100%'class='winbox_field' bgcolor='#d6d3ce' align='center' border='1' cellspacing='0' cellpadding='3'>
                <tr>
                    <td align='center' colspan='4' class='winbox'>
                        <span class='pt12b'>
                        月　次　損　益　関　係　照　会　の　<?php echo $menu->out_caption()?>
                        </span>
                        <select name='pl_ym' class='pt12b'>
                            <?php
                            $ym = date('Ym');
                            while(1) {
                                if (substr($ym, 4, 2) != 01) {
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
                    </td>
                </tr>
                <tr>
                    <td align='center' bgcolor='#ffffc6' class='winbox'>
                        <input class='pt11b' type='submit' name='pl_name' value='経費実績内訳'>
                    </td>
                    <td align='center' bgcolor='#ffffc6' class='winbox'>
                        <input class='pt11b' type='submit' name='pl_name' value='ＣＬ・商管 経費実績表'>
                    </td>
                    <td align='center' bgcolor='#ffffc6' class='winbox'>
                        <input class='pt11b' type='submit' name='pl_name' value='セグメント別損益'>
                    </td>
                    <td align='center' bgcolor='#ffffc6' class='winbox'>
                        <input class='pt11b' type='submit' name='pl_name' value='貸借対照表'>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' bgcolor='#ffffc6' align='center'>
                        <input class='pt11b' type='submit' name='pl_name' value='ＣＬ経費差額比較表'>
                    </td>
                    <td align='center' bgcolor='#ffffc6' class='winbox'>
                        <input class='pt11b' type='submit' name='pl_name' value='原価率計算表'>
                    </td>
                    <td align='center' bgcolor='#ffffc6' class='winbox'>
                        <input class='pt11b' type='submit' name='pl_name' value='ＣＬ予実比損益'>
                    </td>
                    <td align='center' bgcolor='#ffffc6' class='winbox'>
                        <input class='pt11b' type='submit' name='pl_name' value='面積配布表'>
                    </td>
                </tr>
                <tr>
                    <td align='center' bgcolor='#ffffc6' class='winbox'>
                        <input class='pt11b' type='submit' name='pl_name' value='セグメント別損益10月一括'>
                    </td>
                    <!--
                    <td class='winbox' align='center'>　</td>
                    <td align='center' bgcolor='#ffffc6' class='winbox'>
                        <input class='pt11b' type='submit' name='pl_name' value='ＬＴ 商品別損益'>
                    </td>
                    -->
                    <td align='center' bgcolor='#ffffc6' class='winbox'>
                        <input class='pt11b' type='submit' name='pl_name' value='特注・標準 商品別損益'>
                    </td>
                    <td align='center' bgcolor='#ffffc6' class='winbox'>
                        <input class='pt11b' type='submit' name='pl_name' value='試験・修理 商品別損益'>
                    </td>
                    <td align='center' bgcolor='#ffffc6' class='winbox'>
                        <input class='pt11b' type='submit' name='pl_name' value='損益対前月比較表'>
                    </td>
                </tr>
                <tr>
                    <td class='winbox' align='center'>
                    <input class='pt11b' type='submit' name='pl_name' value='旧ＣＬ経費実績表'>
                    </td>
                    <td class='winbox' align='center'>
                        <input class='pt11b' type='submit' name='pl_name' value='旧ＣＬ商品別損益'>
                    </td>
                    <!--
                    <td class='winbox' align='center'>　</td>
                    -->
                    <td align='center' bgcolor='#ffffc6' class='winbox'>
                        <input class='pt11b' type='submit' name='pl_name' value='商品別テスト'>
                    </td>
                    <td class='winbox' align='center'>
                        <input class='pt11b' type='submit' name='pl_name' value='ＢＬ 商品別損益'>
                    </td>
                </tr>
            </table>
                </td></tr>
            </table> <!----------------- ダミーEnd ------------------>
        </form>
        
        <br>
        
        <form action='profit_loss_submit.php' method='post' onSubmit='return as_ftp_submit(this)'>
            <table bgcolor='#d6d3ce' align='center' border='1' cellspacing='0' cellpadding='3'>
                <tr><td> <!----------- ダミー(デザイン用) ------------>
            <table width='100%'class='winbox_field' bgcolor='#d6d3ce' align='center' border='1' cellspacing='0' cellpadding='3'>
                <tr>
                    <td align='center' colspan='3' class='winbox'>
                        <span class='pt12b'>
                        ＡＳ／４００との個別データリンク　　<?php echo $menu->out_caption()?>
                        </span>
                        <select name='pl_ym' class='pt12b'>
                            <?php
                            $ym = date('Ym');
                            while(1) {
                                if (substr($ym, 4, 2) != 01) {
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
                    </td>
                </tr>
                <tr>
                    <td align='center' bgcolor='#7fbeff' class='winbox'> <!-- 薄い青 -->
                        <input class='pt11b' type='submit' name='pl_name' value='経費内訳データ' >
                    </td>
                    <td align='center' bgcolor='#7fbeff' class='winbox'>
                        <input class='pt11b' type='submit' name='pl_name' value='ＣＬ経費データ' >
                    </td>
                    <td align='center' bgcolor='#7fbeff' class='winbox'>
                        <input class='pt11b' type='submit' name='pl_name' value='科目別部門経費' >
                    </td>
                </tr>
                <tr>
                    <td align='center' valign='middle' height='10' class='winbox'>
                        <span class='explain_font'>AS/400 02→23→21 D</span>
                    </td>
                    <td align='center' valign='middle' height='10' class='winbox'>
                        <span class='explain_font'>AS/400 77→77→31→04 B</span>
                    </td>
                    <td align='center' valign='middle' height='10' class='winbox'>
                        <span class='explain_font'>AS/400 77→77→31→04 B1</span>
                    </td>
                </tr>
                <tr>
                    <td align='center' bgcolor='#7fbeff' class='winbox'>
                        <input class='pt11b' type='submit' name='pl_name' value='要素買掛データ' >
                    </td>
                    <td align='center' bgcolor='#7fbeff' class='winbox'>
                        <input class='pt11b' type='submit' name='pl_name' value='ＣＬ損益データ' >
                    </td>
                    <td align='center' bgcolor='#7fbeff' class='winbox'>
                        <input class='pt11b' type='submit' name='pl_name' value='貸借対照データ' >
                    </td>
                </tr>
                <tr>
                    <td align='center' valign='middle' height='10' class='winbox'>
                        <span class='explain_font'>AS/400 02→26→37 E</span>
                    </td>
                    <td align='center' valign='middle' height='10' class='winbox'>
                        <span class='explain_font'>02→33→23→10→06 AC</span>
                    </td>
                    <td align='center' valign='middle' height='10' class='winbox'>
                        <span class='explain_font'>02→33→23→10→02 F</span>
                    </td>
                </tr>
            </table>
                </td></tr>
            </table> <!----------------- ダミーEnd ------------------>
        </form>
    
    <br>
    
        <form name='clear' action='profit_loss_submit.php' method='post'>
            <table bgcolor='#d6d3ce' align='center' border='1' cellspacing='0' cellpadding='3'>
                <tr><td> <!----------- ダミー(デザイン用) ------------>
            <table width='100%'class='winbox_field' bgcolor='#d6d3ce' align='center' border='1' cellspacing='0' cellpadding='3'>
                <tr>
                    <td align='center' colspan='3' class='winbox'>
                        <span class='pt12b'>
                        月次データ置換作業のため各履歴クリアー　　<?php echo $menu->out_caption()?>
                        </span>
                        <select name='pl_ym' class='pt12b'>
                            <?php
                            $ym = date('Ym');
                            while(1) {
                                if (substr($ym, 4, 2) != 01) {
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
                    </td>
                </tr>
                <tr>
                    <td align='center' bgcolor='#ffa4a4' class='winbox'>
                        <input class='pt11b' type='submit' name='pl_name' value='CL配賦Clear' onClick='return monthly_clear(this)'>
                    </td>
                    <td align='center' bgcolor='#ffa4a4' class='winbox'>
                        <input class='pt11b' type='submit' name='pl_name' value='経費配賦Clear' onClick='return monthly_clear(this)'>
                    </td>
                    <td align='center' bgcolor='#ffa4a4' class='winbox'>
                        <input class='pt11b' type='submit' name='pl_name' value='CL損益Clear' onClick='return monthly_clear(this)'>
                    </td>
                </tr>
            </table>
                </td></tr>
            </table> <!----------------- ダミーEnd ------------------>
        </form>
    
    <br>
    
        <form action='profit_loss_submit.php' method='post'>
            <table bgcolor='#d6d3ce' align='center' border='1' cellspacing='0' cellpadding='3'>
                <tr><td> <!----------- ダミー(デザイン用) ------------>
            <table width='100%'class='winbox_field' bgcolor='#d6d3ce' align='center' border='1' cellspacing='0' cellpadding='3'>
                <tr>
                    <td align='center' colspan='2' class='winbox'>
                        <span class='pt12b'>
                        月次比較棚卸表の<?php echo $menu->out_caption()?>
                        </span>
                        <select name='pl_ym' class='pt12b'>
                            <?php
                            $ym = date('Ym');
                            while(1) {
                                if (substr($ym, 4, 2) != 01) {
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
                    </td>
                </tr>
                <tr>
                    <td align='center' bgcolor='#ceffce' class='winbox'>
                        <input class='pt11b' type='submit' name='pl_name' value='データ取込み' >
                    </td>
                    <td align='center' bgcolor='#ceffce' class='winbox'>
                        <input class='pt11b' type='submit' name='pl_name' value='比較棚卸表' >
                    </td>
                </tr>
            </table>
                </td></tr>
            </table> <!----------------- ダミーEnd ------------------>
        </form>
    
    <br>
    
    <br>
    
        <form action='profit_loss_submit.php' method='post'>
            <table bgcolor='#d6d3ce' align='center' border='1' cellspacing='0' cellpadding='3'>
                <tr><td> <!----------- ダミー(デザイン用) ------------>
            <table width='100%'class='winbox_field' bgcolor='#d6d3ce' align='center' border='1' cellspacing='0' cellpadding='3'>
                <tr>
                    <td align='center' colspan='2' class='winbox'>
                        <span class='pt12b'>
                        四半期減価償却費明細表の<?php echo $menu->out_caption()?>
                        </span>
                        <select name='2ki_ym' class='pt12b'>
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
                    </td>
                </tr>
                <tr>
                    <td align='center' bgcolor='#ceffce' class='winbox'>
                        <input class='pt11b' type='submit' name='pl_name' value='減価償却費明細表取込' >
                    </td>
                    <td align='center' bgcolor='#ceffce' class='winbox'>
                        <input class='pt11b' type='submit' name='pl_name' value='減価償却費明細表' >
                    </td>
                </tr>
            </table>
                </td></tr>
            </table> <!----------------- ダミーEnd ------------------>
        </form>
    
    <br>
    
    <!----------------------------------------------------------- コメントに変更
        <form action='profit_loss_submit.php' method='post'>
            <table bgcolor='#d6d3ce' align='center' border='1' cellspacing='0' cellpadding='3'>
                <tr><td>
            <table width='100%'class='winbox_field' bgcolor='#d6d3ce' align='center' border='1' cellspacing='0' cellpadding='3'>
                <tr>
                    <td align='center' colspan='5' class='winbox'>
                        <span class='pt12b'>
                        部　門　別　損　益　関　係　の　<?php echo $menu->out_caption()?>
                        </span>
                        <select name='pl_ym' class='pt12b'>
                            <?php
                            $ym = date('Ym');
                            while(1) {
                                if (substr($ym, 4, 2) != 01) {
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
                    </td>
                </tr>
                <tr>
                    <td align='center' bgcolor='#ceffce' class='winbox'>
                        <input class='pt11b' type='submit' name='pl_name' value='バイモル事業' >
                    </td>
                    <td align='center' bgcolor='#ceffce' class='winbox'>
                        <input class='pt11b' type='submit' name='pl_name' value='リニア組立' >
                    </td>
                    <td align='center' bgcolor='#ceffce' class='winbox'>
                        <input class='pt11b' type='submit' name='pl_name' value='カプラ組立' >
                    </td>
                    <td align='center' bgcolor='#ceffce' class='winbox'>
                        <input class='pt11b' type='submit' name='pl_name' value='Ｃ組立特注' >
                    </td>
                    <td align='center' bgcolor='#ceffce' class='winbox'>
                        <input class='pt11b' type='submit' name='pl_name' value='Ｃ組立標準' >
                    </td>
                </tr>
                <tr>
                    <td align='center' bgcolor='#ceffce' class='winbox'>
                        <input class='pt11b' type='submit' name='pl_name' value='製造・特注' >
                    </td>
                    <td align='center' bgcolor='#ceffce' class='winbox'>
                        <input class='pt11b' type='submit' name='pl_name' value='製造・1NC' >
                    </td>
                    <td align='center' bgcolor='#ceffce' class='winbox'>
                        <input class='pt11b' type='submit' name='pl_name' value='製造・6軸' >
                    </td>
                    <td align='center' bgcolor='#ceffce' class='winbox'>
                        <input class='pt11b' type='submit' name='pl_name' value='製造・4NC' >
                    </td>
                    <td align='center' bgcolor='#ceffce' class='winbox'>
                        <input class='pt11b' type='submit' name='pl_name' value='製造・PF' >
                    </td>
                </tr>
            </table>
                </td></tr>
            </table>
        </form>
    ここまで ------------------------------------------>
    </center>
</body>
</html>
<?php
ob_end_flush();                 // 出力バッファをgzip圧縮 END
?>
