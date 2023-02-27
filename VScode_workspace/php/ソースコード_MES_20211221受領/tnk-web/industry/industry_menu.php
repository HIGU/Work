<?php
//////////////////////////////////////////////////////////////////////////////
// 生産 関係 処理 メニュー                                                  //
// Copyright(C) 2003-2017 Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp       //
// Changed history                                                          //
// 2003/11/29 Created  industry_menu.php                                    //
// 2003/12/12 defineされた定数でディレクトリとメニューを使用して管理する    //
//            ob_start('ob_gzhandler') を追加                               //
// 2004/02/13 index1.php→index.phpへ変更(index1はauthenticateに変更のため) //
// 2004/04/07 ASSY番号による総材料費の照会を追加                            //
// 2004/05/12 サイトメニュー表示・非表示 ボタン追加 menu_OnOff($script)追加 //
// 2004/06/10 view_user($_SESSION['User_ID']) をメニューヘッダーの下に追加  //
// 2004/09/21 MenuHeader Class を導入                                       //
// 2004/10/19 検査のデータ同期を消して検査依頼リストを追加                  //
// 2004/11/27 発注工程のメンテナンスを追加                                  //
// 2004/12/21 method='post' → 'get' へ変更                                 //
// 2004/12/25 style='overflow-y:hidden;' を追加                             //
// 2005/01/13 PDFの表示をapplication→_blankへ変更 JavaScriptの共通キー対策 //
// 2005/01/14 F2/F12キーを有効化する対応のため document.body.focus()を追加  //
// 2005/09/15 アイテムマスターの照会・編集の追加によるレイアウトを変更      //
// 2005/10/25 <a href='javascript:noMenu()' へ変更  旧のソースはbackupへ    //
// 2006/01/26 発注＆検査＆出庫メニュー→発注・検査・資材・組立メニューへ変更//
// 2006/11/01 ウィンドウを小さくした場合の対応でoverflow-yを外してnowrap追加//
//            またJavaScriptのcheckOverFlow()を追加しoverflowYを動的に対応  //
// 2006/12/05 メニューのレイアウト変更。資材出庫集計と受入検査集計を照会へ  //
// 2007/01/17 検査中の中断メニューを追加 (画面には出さずに内部的に)         //
// 2007/02/20 parts_stock_plan_Main.php → parts_stock_plan_form.php へ変更 //
// 2007/03/12 部品在庫経歴のディレクトリ変更                                //
// 2007/03/24 引当部品構成表の照会をディレクトリ変更とプログラム変更        //
// 2007/06/08 資材部品 在庫保有月等の照会メニューを追加                     //
// 2007/06/14 上記のメニュー名を在庫保有月等の分析へ変更チップヘルプも変更  //
// 2007/08/04 在庫・有効利用すうマイナスリストメニューを追加                //
// 2007/09/05 payable_linear_vendor_summary2 (リニア 外注別 買掛) を追加    //
//            仕切用総材料費チェック も追加                                 //
// 2007/09/18 E_ALL | E_STRICT へ変更                                       //
// 2007/09/25 phpのショートカットタグを標準タグへ 刻印管理システムを追加    //
// 2007/10/04 最新総材料費登録(製品番号)メニューを追加  デザインを一部変更  //
// 2007/10/13 リンクのtarget属性を application →_self へ変更               //
// 2008/02/12 リニア仕切用総材料費チェックを追加materialCheckLinear_Main.php//
// 2008/02/14 リニア仕切用総材料費チェックをmaterialCheckLinear_Main2.php   //
//            に変更                                                        //
// 2010/05/06 カプラ・リニア仕切用総材料費登録を追加（300144のみ）     大谷 //
// 2010/05/13 仕切単価改定処理をメニューにまとめた                     大谷 //
// 2011/05/26 総材料費の比較を生産メニューに追加                       大谷 //
// 2011/05/30 総材料費の比較を別メニューにまとめた。                   大谷 //
// 2011/11/10 生産支援品マスター登録をメニューに追加した               大谷 //
// 2011/11/22 納期遅れ部品照会をメニューに追加                         大谷 //
// 2011/12/21 生産支援関連メニューを追加（生産支援品マスター）を削除して    //
//            生産支援関連メニューに統合                               大谷 //
// 2013/01/28 特注カプラ冶具・作業注意点を追加                         大谷 //
//            バイモルを液体ポンプへ変更 表示のみデータはバイモルのまま 大谷//
// 2014/10/23 新JIS対象製品の生産実績照会メニューを追加                大谷 //
// 2014/12/04 新JIS対応製品→新JIS対象製品へ変更                       大谷 //
// 2015/05/21 機工生産に対応（バイモル棚卸金額→ツールに変更）         大谷 //
// 2016/03/24 A伝状況の照会を照会メニューへ仮追加                      大谷 //
// 2017/04/27 A伝状況の照会に長谷川さんを追加                          大谷 //
// 2017/04/27 新JIS対象製品メニュー→対象製品集計メニューへ変更        大谷 //
// 2017/06/14 A伝状況の照会を照会メニューへ追加                        大谷 //
// 2020/12/24 組立完成部品一覧を照会メニューへ追加                     和氣 //
// 2021/01/08 組立完成編集を発注・検査・資材・組立メニューへ追加       和氣 //
//////////////////////////////////////////////////////////////////////////////
// ini_set('error_reporting', E_ALL | E_STRICT);
// ini_set('error_reporting', E_ALL);          // E_ALL='2047' debug 用
ini_set('display_errors', '0');             // Error 表示 ON debug 用 リリース後コメント
// ini_set('implicit_flush', 'off');           // echo print で flush させない(遅くなるため) CLI版
// ini_set('max_execution_time', 1200);        // 最大実行時間=20分 WEB CGI版
ob_start('ob_gzhandler');                   // 出力バッファをgzip圧縮
session_start();                            // ini_set()の次に指定すること Script 最上行

require_once ('../function.php');
require_once ('../tnk_func.php');
require_once ('../MenuHeader.php');         // TNK 全共通 menu class
access_log();                               // Script Name は自動取得

///// TNK 共用メニュークラスのインスタンスを作成
$menu = new MenuHeader(0);                  // 認証チェック0=一般以上 戻り先=TOP_MENU タイトル未設定

////////////// サイト設定
$menu->set_site(30, 999);                   // site_index=30(生産メニュー) site_id=999(サイトメニューを開く)
////////////// リターンアドレス設定
// $menu->set_RetUrl(SYS_MENU);                // 通常は指定する必要はない
//////////// タイトル名(ソースのタイトル名とフォームのタイトル名)
$menu->set_title('生 産 メニュー');
//////////// 表題の設定
// $menu->set_caption('総材料費 関係 メニュー');
//////////// 呼出先のaction名とアドレス設定
    /************ 総材料費 関係 メニュー *************/
$menu->set_action('仕切単価と総材料費比較',         INDUST . 'material/sales_material_comp_form.php');
$menu->set_action('総材料費の照会計画番号',         INDUST . 'material/materialCost_view_plan.php');
$menu->set_action('総材料費の照会ASSY番号',         INDUST . 'material/materialCost_view_assy.php');
$menu->set_action('総材料費と売上高の比率表',       INDUST . 'material/materialCost_sales_comp.php');
$menu->set_action('総材料費の未登録照会',           INDUST . 'material/materialCost_unregist_view.php');
$menu->set_action('総材料費の登録',                 INDUST . 'material/materialCost_entry_plan.php');
$menu->set_action('仕切区分と総材料費',             INDUST . 'sales_kubun_material.php');
$menu->set_action('部品単価と販売価格',             INDUST . 'parts/parts_sales_price_form.php');
$menu->set_action('仕切用総材料費チェック',         INDUST . 'materialCheck/materialCheck_Main.php');
$menu->set_action('最新総材料費の登録',             INDUST . 'material/materialCost_entry_assy.php');
$menu->set_action('リニア仕切用総材料費チェック',   INDUST . 'materialCheck/materialCheckLinear_Main2.php');
$menu->set_action('仕切単価改定処理メニュー',           INDUST . 'material_new/materialNew_menu.php');
$menu->set_action('総材料費の比較(年月)',           INDUST . 'material/material_compare/material_compare_form.php');
$menu->set_action('総材料費の比較メニュー',           INDUST . 'material_compare/material_compare_menu.php');
    /************ 生産管理 照会 メニュー *************/
$menu->set_action('部品在庫予定',                   INDUST . 'parts/parts_stock_plan/parts_stock_plan_form.php');
$menu->set_action('部品在庫経歴',                   INDUST . 'parts/parts_stock_history/parts_stock_form.php');
$menu->set_action('単価経歴の照会',                 INDUST . 'parts/parts_cost_form.php');
$menu->set_action('Ａ伝情報の照会',                 INDUST . 'Aden/aden_master_view_form.php');
$menu->set_action('発注計画の照会',                 INDUST . 'order/order_plan_view.php');
$menu->set_action('買掛実績の照会',                 INDUST . 'payable/act_payable_form.php');
$menu->set_action('支給実績の照会',                 INDUST . 'act_miprov_view.php');
$menu->set_action('製品売上未検収明細照会',         INDUST . 'sales_miken/sales_miken_Main.php');
// $menu->set_action('引当部品構成表の照会',           INDUST . 'allo_conf_parts_form.php');
// $menu->set_action('引当部品構成表の照会',           INDUST . 'material/allo_conf_parts_form.php');
$menu->set_action('引当部品構成表の照会',           INDUST . 'parts/allocate_config/allo_conf_parts_form.php');
$menu->set_action('NKB入庫品一覧',                  INDUST . 'parts_control/parts_storage_space/parts_storage_space_Main.php');
$menu->set_action('長期滞留部品の照会',             INDUST . 'long_holding_parts/in_date/long_holding_parts_Main.php');
$menu->set_action('部品保有月の分析',               INDUST . 'long_holding_parts/inventory_average/inventory_average_Main.php');
$menu->set_action('部品在庫マイナス',               INDUST . 'parts/parts_stock_avail_minus/parts_stock_avail_minus_Main.php');
$menu->set_action('Ａ伝状況の照会',                 INDUST . 'aden_details/aden_details_form.php');
$menu->set_action('組立完成部品一覧',               INDUST . 'assembly/assembly_comp_parts_list/assembly_comp_parts_list_form.php');
    /************ 月次ベース 照会 メニュー *************/
$menu->set_action('カプラ棚卸金額',                 ACT    . 'inventory/inventory_month_c_view.php');
$menu->set_action('リニア棚卸金額',                 ACT    . 'inventory/inventory_month_l_view.php');
$menu->set_action('カプラ特注 棚卸金額 照会',       ACT    . 'inventory/inventory_monthly_ctoku_view.php');
$menu->set_action('バイモル 棚卸金額 照会',         ACT    . 'inventory/inventory_month_bimor_view.php');
$menu->set_action('ツール 棚卸金額 照会',           ACT    . 'inventory/inventory_month_tool_view.php');
$menu->set_action('仕入金額の照会',                 ACT    . 'act_purchase_view.php');
$menu->set_action('カプラ特注の買掛明細',           INDUST . 'payable/payable_ctoku_view.php');
$menu->set_action('カプラ特注 外注別 買掛金額',     INDUST . 'payable/payable_ctoku_vendor_summary.php');
$menu->set_action('Ｃ特注の買掛明細',               INDUST . 'payable/payable_ctoku_view2.php');
$menu->set_action('Ｃ特注 外注別 買掛',             INDUST . 'payable/payable_ctoku_vendor_summary2.php');
$menu->set_action('Ｃ標準 外注別 買掛',             INDUST . 'payable/payable_cstd_vendor_summary2.php');
$menu->set_action('リニア 外注別 買掛',             INDUST . 'payable/payable_linear_vendor_summary2.php');
$menu->set_action('部品出庫集計',                   INDUST . 'parts_control/parts_pickup_analyze/parts_pickup_analyze_Main.php');
$menu->set_action('受入検査集計',                   INDUST . 'order/acceptance_inspection_analyze/acceptance_inspection_analyze_Main.php');
$menu->set_action('納入予定金額照会',               INDUST . 'order_money/order_schedule.php');
    /************ マスター関係 メニュー *************/
$menu->set_action('アイテムマスター',               INDUST . 'master/parts_item/parts_item_Main.php');
$menu->set_action('発注先マスター照会',             INDUST . 'vendor_master_view.php');
$menu->set_action('品名による番号検索',             INDUST . 'master/item_name_search/item_name_search_Main.php');
$menu->set_action('製品グループコード',             INDUST . 'master/product_master/product_master_menu.php');
    /************ 発注 → 検査 → 出庫 → 組立 用 メニュー *************/
$menu->set_action('納入予定と未検収明細',           INDUST . 'order/order_schedule.php');
$menu->set_action('検査依頼',                       INDUST . 'order/inspection_recourse.php');
$menu->set_action('検査中の中断',                   INDUST . 'order/inspectingList.php');
$menu->set_action('発注工程のメンテ',               INDUST . 'order/order_process_mnt.php');
$menu->set_action('新規オーダー特注',               TEST   . 'ooya/pdf.php');
$menu->set_action('新規オーダー標準',               TEST   . 'ooya/pdf_standard.php');
$menu->set_action('協力工場別注残リスト',           INDUST . 'vendor/vendor_order_list_form.php');
$menu->set_action('資材部品出庫',                   INDUST . 'parts_control/parts_pickup_time_Main.php');
$menu->set_action('組立指示',                       INDUST . 'assembly/assembly_process/assembly_process_time_Main.php');
$menu->set_action('組立実績編集',                   INDUST . 'assembly/assembly_time_edit/assembly_time_edit_Main.php');
$menu->set_action('組立状況照会',                   INDUST . 'assembly/assembly_process_show/assembly_process_show_Main.php');
$menu->set_action('日程計画照会',                   INDUST . 'scheduler/schedule_show/assembly_schedule_show_Main.php');
$menu->set_action('実績工数照会',                   INDUST . 'assembly/assembly_time_show/assembly_time_show_Main.php');
$menu->set_action('完成一覧工数',                   INDUST . 'assembly/assembly_time_compare/assembly_time_compare_Main.php');
$menu->set_action('完成一覧工数編集',               INDUST . 'assembly/assembly_time_compare_edit/assembly_time_compare_edit_Main.php');
$menu->set_action('ライン別工数グラフ',             INDUST . 'assembly/assembly_time_graph/assembly_time_graph_Main.php');
$menu->set_action('バーコード作成',                 INDUST . 'BarCode/datasum_barcode.php');
$menu->set_action('リニア部品出庫',                 INDUST . 'parts_control/parts_pickup_linear/parts_pickup_linear_Main.php');
$menu->set_action('ラインカレンダー',               INDUST . 'scheduler/assembly_calendar/assembly_calendar_Main.php');
// $menu->set_action('データ同期',                     INDUST . 'order/order_data_difference_update.php');
// $menu->set_action('データ同期',                     INDUST . 'order/order_data_ftp_update.php');
$menu->set_action('刻印管理システム',               INDUST . 'punchMark/index.php');
$menu->set_action('特注成績書印刷',                 INDUST . 'inspectionPrint/inspectionPrint.php');
$menu->set_action('繰返し注文集計',                 INDUST . 'order/total_repeat_order/total_repeat_order_Main.php');
$menu->set_action('納期遅れ部品の照会',             INDUST . 'order/delivery_late/delivery_late_form.php');
$menu->set_action('特注カプラ冶具作業注意点メニュー',  INDUST . 'custom_attention/custom_attention_menu.php');

$menu->set_action('生産支援関連メニュー',           INDUST . 'product_support/product_support_menu.php');
$menu->set_action('対象製品集計メニュー',           INDUST . 'new_jis/new_jis_menu.php');

$menu->set_action('検査日数集計',                   INDUST . 'order/inspection_date/inspection_date_form.php');

//////////// ブラウザーのキャッシュ対策用
$uniq = $menu->set_useNotCache('target');

//////////// 対象年月日のセッションデータ取得
if (isset($_SESSION['ind_ymd'])) {
    $ind_ymd = $_SESSION['ind_ymd']; 
} else {
    $ind_ymd = date('Ymd');        // セッションデータがない場合の初期値(当月)
}
//////////// 対象年月のセッションデータ取得
if (isset($_SESSION['ind_ym'])) {
    $ind_ym = $_SESSION['ind_ym']; 
} else {
    $ind_ym = date('Ym');        // セッションデータがない場合の初期値(当月)
}


$uid   = $_SESSION['User_ID'];
$query = "SELECT sid FROM user_detailes WHERE uid='$uid'";
$res   = array();
if( getResult($query,$res) <= 0 ) {
    $sid   = "";
} else {
    $sid   = $res[0][0];
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
<script type='text/javascript'>
<!--
function monthly_send(script_name)
{
    document.monthly_form.action = 'ind_branch.php?ind_name=' + script_name;
    document.monthly_form.submit();
}
function set_focus()
{
    // document.body.focus();   // F2/F12キーを有効化する対応
    // document.mhForm.backwardStack.focus();  // 上記はIEのみのためNN対応
}
function checkOverFlow()
{
    if (document.body.clientHeight) {
        var h = document.body.clientHeight; // IE
    } else {
        var h = window.innerHeight;         // NN
    }
    if (h <= 650) {     // メニューの量が変更になった場合はマジックナンバー650を変更する事
        document.body.style.overflowY = "scroll";
    } else {
        document.body.style.overflowY = "hidden";
    }
}
// -->
</script>

<!-- スタイルシートのファイル指定をコメント HTMLタグ コメントは入れ子に出来ない事に注意 
<link rel='stylesheet' href='<?php echo MENU_FORM . '?' .$uniq ?>' type='text/css' media='screen'>
-->

<style type='text/css'>
<!--
/** font-weight: normal;        **/
/** font-weight: 400;    と同じ **/
/** font-weight: bold;          **/
/** font-weight: 700;    と同じ **/
/**         100〜900まで100刻み **/
select {
    background-color:teal;
    color:white;
}
.pt11b {
    font-size:   11pt;
    font-weight: bold;
}
a:hover {
    background-color: blue;
    color           : white;
}
a:active {
    background-color: white;
    color           : red;
}
a {
    font-size:   11pt;
    font-weight: bold;
    color:       black;
}
.caption_font {
    font-size:          11pt;
    font-weight:        bold;
    background-color:   #ffffa6;
    color:              blue;
    border-style: solid;
    border-width: 1px;
    border-top-color:       #FFFFFF;
    border-left-color:      #FFFFFF;
    border-right-color:     #999999;
    border-bottom-color:    #999999;
}
.winbox {
    border-style: solid;
    border-width: 1px;
    border-top-color:       #FFFFFF;
    border-left-color:      #FFFFFF;
    border-right-color:     #999999;
    border-bottom-color:    #999999;
    /* background-color:#d6d3ce; */
}
.winbox_field {
    border-style: solid;
    border-width: 1px;
    border-top-color:       #999999;
    border-left-color:      #999999;
    border-right-color:     #FFFFFF;
    border-bottom-color:    #FFFFFF;
    /* background-color:#d6d3ce; */
}
body {
    background-image:       url(<?php echo IMG ?>t_nitto_logo4.png);
    background-repeat:      no-repeat;
    background-attachment:  fixed;
    background-position:    center bottom;
    /* overflow-y:             hidden; */
}
-->
</style>
</head>
<body onresize='checkOverFlow()' onLoad='set_focus()'>
    <center>
<?php echo $menu->out_title_border() ?>
        <?php
        if ($sid != '95') {
        ?>
        <form action='ind_branch.php' method='get'>
        <table bgcolor='#d6d3ce'  border='1' cellspacing='0' cellpadding='3'>
            <tr><td> <!-- ダミー(デザイン用) -->
            <table class='winbox_field' bgcolor='#d6d3ce' border='1' cellspacing='0' cellpadding='3'>
                <tr>
                    <td nowrap width='810' bgcolor='#ffffa6' align='center' colspan='4' class='caption_font'>
                        総材料費 関係 メニュー
                    </td>
                </tr>
                <tr>
                    <td nowrap class='winbox' colspan='1' align='center' bgcolor='#d6d3ce'>
                        <a href='javascript:function(){}'
                            target='_self' style='text-decoration:none;'
                            onClick='location.replace("<?php echo $menu->out_action('仕切単価と総材料費比較')?>"); return false;'
                            onMouseover="status='売上の仕切単価と総材料費の比較リストを照会します。';return true;"
                            onMouseout="status=''"
                            title='売上の仕切単価と総材料費の比較リストを照会します。'
                        >
                            仕切単価と総材料費比較
                        </a>
                    </td>
                    <td nowrap class='winbox' colspan='1' align='center' bgcolor='#d6d3ce'>
                        <a href='javascript:function(){}'
                            target='_self' style='text-decoration:none;'
                            onClick='location.replace("<?php echo $menu->out_action('総材料費の照会計画番号')?>"); return false;'
                            onMouseover="status='計画番号で製品の総材料費の照会を行います。';return true;"
                            onMouseout="status=''"
                            title='計画番号で製品の総材料費の照会を行います。'
                        >
                            総材料費の照会(計画番号)
                        </a>
                    </td>
                    <td nowrap class='winbox' colspan='1' align='center' bgcolor='#d6d3ce'>
                        <a href='javascript:function(){}'
                            target='_self' style='text-decoration:none;'
                            onClick='location.replace("<?php echo $menu->out_action('総材料費の照会ASSY番号')?>"); return false;'
                            onMouseover="status='ASSY番号(製品番号)で製品の総材料費の照会を行います。';return true;"
                            onMouseout="status=''"
                            title='ASSY番号(製品番号)で製品の総材料費の照会を行います。'
                        >
                            総材料費の照会(ASSY番号)
                        </a>
                    </td>
                    <td nowrap class='winbox' colspan='1' align='center' bgcolor='#d6d3ce'>
                        <a href='javascript:function(){}'
                            target='_self' style='text-decoration:none;'
                            onClick='location.replace("<?php echo $menu->out_action('総材料費の比較メニュー')?>"); return false;'
                            onMouseover="status='総材料費の比較関連メニュー。';return true;"
                            onMouseout="status=''"
                            title='総材料費の比較関連メニュー。'
                        >
                            総材料費の比較メニュー
                        </a>
                    </td>
                </tr>
                <tr>
                    <td nowrap class='winbox' colspan='1' align='center' bgcolor='#d6d3ce'>
                        <a href='javascript:function(){}'
                            target='_self' style='text-decoration:none;'
                            onClick='location.replace("<?php echo $menu->out_action('総材料費と売上高の比率表')?>"); return false;'
                            onMouseover="status='売上高に占める外作費(部品・材料費)と内作費(加工・組立費)の比率表を照会します。';return true;"
                            onMouseout="status=''"
                            title='売上高に占める外作費(部品・材料費)と内作費(加工・組立費)の比率表を照会します。'
                        >
                            総材料費と売上高の比率表
                        </a>
                    </td>
                    <td nowrap class='winbox' colspan='1' align='center' bgcolor='#d6d3ce'>
                        <a href='javascript:function(){}'
                            target='_self' style='text-decoration:none;'
                            onClick='location.replace("<?php echo $menu->out_action('総材料費の未登録照会')?>"); return false;'
                            onMouseover="status='売上製品の中で総材料費が未登録のものを照会します。(半期ベースでの照会)';return true;"
                            onMouseout="status=''"
                            title='売上製品の中で総材料費が未登録のものを照会します。(半期ベースでの照会)'
                        >
                            総材料費の未登録照会
                        </a>
                    </td>
                    <td nowrap class='winbox' colspan='1' align='center' bgcolor='#d6d3ce'>
                        <a href='javascript:function(){}'
                            target='_self' style='text-decoration:none;'
                            onClick='location.replace("<?php echo $menu->out_action('総材料費の登録')?>"); return false;'
                            onMouseover="status='計画番号単位で製品の総材料費の登録を行います。';return true;"
                            onMouseout="status=''"
                            title='計画番号単位で製品の総材料費の登録を行います。'
                        >
                            総材料費の登録
                        </a>
                    </td>
                    <td nowrap class='winbox' colspan='1' align='center' bgcolor='#d6d3ce'>
                        <a href='javascript:function(){}'
                            target='_self' style='text-decoration:none;'
                            onClick='location.replace("<?php echo $menu->out_action('最新総材料費の登録')?>"); return false;'
                            onMouseover="status='最新の総材料費を製品番号で登録を行います。計画番号は自動発番です。';return true;"
                            onMouseout="status=''"
                            title='最新の総材料費を製品番号で登録を行います。計画番号は自動発番です。'
                        >
                            最新総材料費の登録
                        </a>
                    </td>
                </tr>
                <tr>
                    <td nowrap class='winbox' colspan='1' align='center' bgcolor='#d6d3ce'>
                        <a href='javascript:function(){}'
                            target='_self' style='text-decoration:none;'
                            onClick='location.replace("<?php echo $menu->out_action('仕切区分と総材料費')?>"); return false;'
                            onMouseover="status='仕切単価の決定条件 及びコストダウンされた製品の照会を行います。';return true;"
                            onMouseout="status=''"
                            title='仕切単価の決定条件 及びコストダウンされた製品の照会を行います。'
                        >
                            仕切区分と総材料費比較
                        </a>
                    </td>
                    <td nowrap class='winbox' colspan='1' align='center' bgcolor='#d6d3ce'>
                        <a href='javascript:function(){}'
                            target='_self' style='text-decoration:none;'
                            onClick='location.replace("<?php echo $menu->out_action('部品単価と販売価格')?>"); return false;'
                            onMouseover="status='部品の購入単価より日東工器への販売価格(仕切単価)を照会します。';return true;"
                            onMouseout="status=''"
                            title='部品の購入単価より日東工器への販売価格(仕切単価)を照会します。'
                        >
                            部品単価より販売価格照会
                        </a>
                    </td>
                    <td nowrap class='winbox' colspan='1' align='center' bgcolor='#d6d3ce'>
                        &nbsp;
                    </td>
                    <!--
                    <td nowrap class='winbox' colspan='1' align='center' bgcolor='#d6d3ce'>
                        <a href='javascript:function(){}'
                            target='_self' style='text-decoration:none;'
                            onClick='location.replace("<?php echo $menu->out_action('仕切用総材料費チェック')?>"); return false;'
                            onMouseover="status='仕切価格見直しのための総材料費のチェック。';return true;"
                            onMouseout="status=''"
                            title='仕切価格見直しのための総材料費のチェック。'
                        >
                            仕切用総材料費チェック
                        </a>
                    </td>
                    -->
                    <td nowrap class='winbox' colspan='1' align='center' bgcolor='#d6d3ce'>
                        <a href='javascript:function(){}'
                            target='_self' style='text-decoration:none;'
                            onClick='location.replace("<?php echo $menu->out_action('仕切単価改定処理メニュー')?>"); return false;'
                            onMouseover="status='仕切単価改定の処理メニュー';return true;"
                            onMouseout="status=''"
                            title='仕切単価改定の処理メニュー'
                        >
                            仕切単価改定処理メニュー
                        </a>
                    </td>
                    <!--
                    <td nowrap class='winbox' colspan='1' align='center' bgcolor='#d6d3ce'>
                        <a href='javascript:function(){}'
                            target='_self' style='text-decoration:none;'
                            onClick='location.replace("<?php echo $menu->out_action('リニア仕切用総材料費チェック')?>"); return false;'
                            onMouseover="status='リニア仕切価格見直しのための総材料費のチェック。';return true;"
                            onMouseout="status=''"
                            title='リニア仕切価格見直しのための総材料費のチェック。'
                        >
                            リニア仕切用総材料費チェック
                        </a>
                    </td>
                    -->
                </tr>
            </table>
            </td></tr>
        </table> <!-- ダミーEnd -->
        </form>
        
        <hr color='797979' width='95%'>
        
        <?php
        }
        ?>
        
        <form action='ind_branch.php' method='get'>
        <table bgcolor='#d6d3ce'  border='1' cellspacing='0' cellpadding='3'>
            <tr><td> <!-- ダミー(デザイン用) -->
            <table class='winbox_field' bgcolor='#d6d3ce' border='1' cellspacing='0' cellpadding='3'>
                <tr>
                    <td nowrap width='810' style='background-color:#ffffa6;' align='center' colspan='5' class='caption_font'>
                        照会  メ ニ ュ ー
                    </td>
                </tr>
                <?php
                if ($sid != '95') {
                ?>
                <tr>
                    <td nowrap class='winbox' align='center' bgcolor='#d6d3ce' nowrap>
                        <a href='javascript:function(){}'
                            target='_self' style='text-decoration:none;'
                            onClick='location.replace("<?php echo $menu->out_action('部品在庫予定')?>"); return false;'
                            onMouseover="status='部品の在庫予定(引当・発注状況)の照会を行います。';return true;"
                            onMouseout="status=''"
                            title='部品の在庫予定(引当・発注状況)の照会を行います。'
                        >
                            在庫予定状況照会
                        </a>
                    </td>
                    <td nowrap class='winbox' align='center' bgcolor='#d6d3ce'>
                        <a href='javascript:function(){}'
                            target='_self' style='text-decoration:none;'
                            onClick='location.replace("<?php echo $menu->out_action('単価経歴の照会')?>"); return false;'
                            onMouseover="status='部品の単価登録の経歴を照会します。';return true;"
                            onMouseout="status=''"
                            title='部品の単価登録の経歴を照会します。'
                        >
                            単価経歴の照会
                        </a>
                    </td>
                    <td nowrap class='winbox' align='center' bgcolor='#d6d3ce'>
                        <a href='javascript:function(){}'
                            target='_self' style='text-decoration:none;'
                            onClick='location.replace("ind_branch.php?ind_name=aden_master_view"); return false;'
                            onMouseover="status='特注品等のＡ伝情報の照会を行います。照会時に組立計画をアドオンさせる事もできます。';return true;"
                            onMouseout="status=''"
                            title='特注品等のＡ伝情報の照会を行います。照会時に組立計画をアドオンさせる事もできます。'
                        >
                            Ａ伝情報の照会
                        </a>
                    </td>
                    <td nowrap class='winbox' align='center' bgcolor='#d6d3ce'>
                        <a href='javascript:function(){}'
                            target='_self' style='text-decoration:none;'
                            onClick='location.replace("<?php echo $menu->out_action('発注計画の照会')?>"); return false;'
                            onMouseover="status='発注計画データの照会を行います。現在は確認用だけに存在しますが、将来的には製造番号や部品番号等で照会できるようにします。';return true;"
                            onMouseout="status=''"
                            title='発注計画データの照会を行います。現在は確認用だけに存在しますが、将来的には製造番号や部品番号等で照会できるようにします。'
                        >
                            発注計画の照会
                        </a>
                    </td>
                    <td nowrap class='winbox' align='center' bgcolor='#d6d3ce'>
                        <a href='javascript:function(){}'
                            target='_self' style='text-decoration:none;'
                            onClick='location.replace("<?php echo $menu->out_action('部品出庫集計')?>"); return false;'
                            onMouseover="status='資材の部品出庫の集計を行います。';return true;"
                            onMouseout="status=''"
                            title='資材の部品出庫の集計を行います。'
                        >
                            資材部品出庫集計
                        </a>
                    </td>
                </tr>
                <tr>
                    <td nowrap class='winbox' colspan='1' align='center' bgcolor='#d6d3ce'>
                        <a href='javascript:function(){}'
                            target='_self' style='text-decoration:none;'
                            onClick='location.replace("<?php echo $menu->out_action('部品在庫経歴')?>"); return false;'
                            onMouseover="status='部品の在庫経歴の照会を行います。';return true;"
                            onMouseout="status=''"
                            title='部品の在庫経歴の照会を行います。'
                        >
                            部品在庫経歴照会
                        </a>
                    </td>
                    <td nowrap class='winbox' align='center' bgcolor='#d6d3ce'>
                        <a href='javascript:function(){}'
                            target='_self' style='text-decoration:none;'
                            onClick='location.replace("<?php echo $menu->out_action('買掛実績の照会')?>"); return false;'
                            onMouseover="status='生産用部品の買掛金(購入金額)の照会を行います。';return true;"
                            onMouseout="status=''"
                            title='生産用部品の買掛金(購入金額)の照会を行います。'
                        >
                            買掛実績の照会
                        </a>
                    </td>
                    <td nowrap class='winbox' align='center' bgcolor='#d6d3ce'>
                        <a href='javascript:function(){}'
                            target='_self' style='text-decoration:none;'
                            onClick='location.replace("ind_branch.php?ind_name=act_miprov_view"); return false;'
                            onMouseover="status='生産用部材の支給実績照会を行います。現在は確認用のみで、将来的に部品番号や支給番号での照会ができるようにします。';return true;"
                            onMouseout="status=''"
                            title='生産用部材の支給実績照会を行います。現在は確認用のみで、将来的に部品番号や支給番号での照会ができるようにします。'
                        >
                            支給実績の照会
                        </a>
                    </td>
                    <td nowrap class='winbox' align='center' bgcolor='#d6d3ce'>
                        <a href='javascript:function(){}'
                            target='_self' style='text-decoration:none;'
                            onClick='location.replace("<?php echo $menu->out_action('長期滞留部品の照会')?>"); return false;'
                            onMouseover="status='指定の最終入庫日で現在まだ在庫があるものを 照会します。';return true;"
                            onMouseout="status=''"
                            title='指定の最終入庫日で現在まだ在庫があるものを 照会します。'
                        >
                            長期滞留部品の照会
                        </a>
                    </td>
                    <td nowrap class='winbox' align='center' bgcolor='#d6d3ce'>
                        <a href='javascript:function(){}'
                            target='_self' style='text-decoration:none;'
                            onClick='location.replace("<?php echo $menu->out_action('受入検査集計')?>"); return false;'
                            onMouseover="status='受入検査の集計を行います。';return true;"
                            onMouseout="status=''"
                            title='受入検査の集計を行います。'
                        >
                            受入検査の集計
                        </a>
                    </td>
                </tr>
                <tr>
                    <td nowrap class='winbox' align='center' bgcolor='#d6d3ce' nowrap>
                        <a href='javascript:function(){}'
                            target='_self' style='text-decoration:none;'
                            onClick='location.replace("<?php echo $menu->out_action('NKB入庫品一覧')?>"); return false;'
                            onMouseover="status='部品の検収で検収日と入庫場所を指定して一覧で照会します。';return true;"
                            onMouseout="status=''"
                            title='部品の検収で検収日と入庫場所を指定して一覧で照会します。'
                        >
                            ＮＫＢ入庫品一覧
                        </a>
                    </td>
                    <td nowrap class='winbox' colspan='1' align='center' bgcolor='#d6d3ce'>
                        <?php // onClick='location.replace("ind_branch.php?ind_name=sales_miken_view"); return false;' ?>
                        <a href='javascript:function(){}'
                            target='_self' style='text-decoration:none;'
                            onClick='location.replace("<?php echo $menu->out_action('製品売上未検収明細照会')?>"); return false;'
                            onMouseover="status='日東工器へ納品(完成)した未検収品の照会を行います。(売上未計上品)';return true;"
                            onMouseout="status=''"
                            title='日東工器へ納品(完成)した未検収品の照会を行います。(売上未計上品)'
                        >
                            製品売上未検収照会
                        </a>
                    </td>
                    <td nowrap class='winbox' colspan='1' align='center' bgcolor='#d6d3ce'>
                        <a href='javascript:function(){}'
                            target='_self' style='text-decoration:none;'
                            onClick='location.replace("<?php echo $menu->out_action('引当部品構成表の照会')?>"); return false;'
                            onMouseover="status='引当部品構成表 兼 部品表 兼 出庫表 の照会を行います。';return true;"
                            onMouseout="status=''"
                            title='引当部品構成表 兼 部品表 兼 出庫表 の照会を行います。'
                        >
                            引当部品構成表の照会
                        </a>
                    </td>
                    <td nowrap class='winbox' align='center' bgcolor='#d6d3ce' nowrap>
                        <a href='javascript:function(){}'
                            target='_self' style='text-decoration:none;'
                            onClick='location.replace("<?php echo $menu->out_action('部品保有月の分析')?>"); return false;'
                            onMouseover="status='資材部品の在庫保有月数・月平均出庫数・在庫金額等を 要因毎に集計し分析を行います。';return true;"
                            onMouseout="status=''"
                            title='資材部品の在庫保有月数・月平均出庫数・在庫金額等を 要因毎に集計し分析を行います。'
                        >
                            部品在庫分析メニュー
                        </a>
                    </td>
                    <td nowrap class='winbox' align='center' bgcolor='#d6d3ce' nowrap>
                        <a href='javascript:function(){}'
                            target='_self' style='text-decoration:none;'
                            onClick='location.replace("<?php echo $menu->out_action('部品在庫マイナス')?>"); return false;'
                            onMouseover="status='部品 在庫・有効利用数(予定在庫数)マイナスリストメニューを実行します。';return true;"
                            onMouseout="status=''"
                            title='部品 在庫・有効利用数(予定在庫数)マイナスリストメニューを実行します。'
                        >
                            部品有効数マイナス
                        </a>
                    </td>
                </tr>
                <?php
                }
                ?>
                <tr>
                    <td nowrap class='winbox' align='center' bgcolor='#d6d3ce' nowrap>
                        <a href='javascript:function(){}'
                            target='_self' style='text-decoration:none;'
                            onClick='location.replace("<?php echo $menu->out_action('Ａ伝状況の照会')?>"); return false;'
                            onMouseover="status='特注品等のＡ伝情報の処理状況の照会を行います。';return true;"
                            onMouseout="status=''"
                            title='特注品等のＡ伝情報の処理状況の照会を行います。'
                        >
                            Ａ伝状況の照会
                        </a>
                    </td>
                    <td nowrap class='winbox' align='center' bgcolor='#d6d3ce' nowrap>
                        <a href='javascript:function(){}'
                            target='_self' style='text-decoration:none;'
                            onClick='location.replace("<?php echo $menu->out_action('組立完成部品一覧')?>"); return false;'
                            onMouseover="status='組立完成部品一覧を表示します。';return true;"
                            onMouseout="status=''"
                            title='組立完成部品一覧を表示します。'
                        >
                            組立完成部品一覧
                        </a>
                    </td>
                    <td nowrap class='winbox' colspan='1' align='center' bgcolor='#d6d3ce'>
                        &nbsp;
                    </td>
                    <td nowrap class='winbox' align='center' bgcolor='#d6d3ce' nowrap>
                        &nbsp;
                    </td>
                    <td nowrap class='winbox' align='center' bgcolor='#d6d3ce' nowrap>
                        &nbsp;
                    </td>
                </tr>
            </table>
            </td></tr>
        </table> <!-- ダミーEnd -->
        </form>
        
        <?php
        if ($sid != '95') {
        ?>
        
        <hr color='797979' width='95%'>
        
        <form name='monthly_form' action='ind_branch.php' method='post'>
        <table bgcolor='#d6d3ce'  border='1' cellspacing='0' cellpadding='3'>
            <tr><td> <!-- ダミー(デザイン用) -->
            <table class='winbox_field' bgcolor='#d6d3ce' border='1' cellspacing='0' cellpadding='3'>
                <tr>
                    <td nowrap width='810' align='center' colspan='4' class='caption_font'>
                        月次ベース 照会 メニュー  処理年月
                        <select name='ind_ym' class='pt11b'>
                            <?php
                            $ym = date("Ym");
                            while(1) {
                                if (substr($ym,4,2)!=01) {
                                    $ym--;
                                } else {
                                    $ym = $ym - 100;
                                    $ym = $ym + 11;
                                }
                                if ($ind_ym == $ym) {
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
                    <td nowrap class='winbox' align='center' bgcolor='#d6d3ce'>
                        <a href='javascript:function(){}'
                            target='_self' style='text-decoration:none;'
                            onClick='javaScript:monthly_send("inventory_month_c_view"); return false;'
                            onMouseover="status='月次ベースのカプラ全体の棚卸金額を照会します。';return true;"
                            onMouseout="status=''"
                            title='月次ベースのカプラ全体の棚卸金額を照会します。'
                        >
                            カプラ棚卸金額
                        </a>
                    </td>
                    <td nowrap class='winbox' align='center' bgcolor='#d6d3ce'>
                        <a href='javascript:function(){}'
                            target='_self' style='text-decoration:none;'
                            onClick='javaScript:monthly_send("inventory_month_ctoku_view"); return false;'
                            onMouseover="status='月次ベースのカプラ特注品の棚卸金額を照会します。';return true;"
                            onMouseout="status=''"
                            title='月次ベースのカプラ特注品の棚卸金額を照会します。'
                        >
                            カプラ特注棚卸金額
                        </a>
                    </td>
                    <td nowrap class='winbox' align='center' bgcolor='#d6d3ce'>
                        <a href='javascript:function(){}'
                            target='_self' style='text-decoration:none;'
                            onClick='javaScript:monthly_send("inventory_month_l_view"); return false;'
                            onMouseover="status='月次ベースのリニア全体の棚卸金額を照会します。';return true;"
                            onMouseout="status=''"
                            title='月次ベースのリニア全体の棚卸金額を照会します。'
                        >
                            リニア棚卸金額
                        </a>
                    </td>
                    <?php
                    if ($_SESSION['User_ID'] == '300144') {
                    ?>
                    <td nowrap class='winbox' align='center' bgcolor='#d6d3ce'>
                        <a href='javascript:function(){}'
                            target='_self' style='text-decoration:none;'
                            onClick='javaScript:monthly_send("inventory_month_tool_view"); return false;'
                            onMouseover="status='月次ベースのツールの棚卸金額を照会します。';return true;"
                            onMouseout="status=''"
                            title='月次ベースのツールの棚卸金額を照会します。'
                        >
                            ツール棚卸金額
                        </a>
                    </td>
                    <?php
                    } else {
                    ?>
                    
                    <td nowrap class='winbox' align='center' bgcolor='#d6d3ce'>
                        <a href='javascript:function(){}'
                            target='_self' style='text-decoration:none;'
                            onClick='javaScript:monthly_send("inventory_month_bimor_view"); return false;'
                            onMouseover="status='月次ベースの液体ポンプの棚卸金額を照会します。';return true;"
                            onMouseout="status=''"
                            title='月次ベースの液体ポンプの棚卸金額を照会します。'
                        >
                            液体ポンプ棚卸金額
                        </a>
                    </td>
                    <?php
                    }
                    ?>
                </tr>
                <tr>
                    <td nowrap class='winbox' colspan='1' align='center' bgcolor='#d6d3ce'>
                        <a href='javascript:function(){}'
                            target='_self' style='text-decoration:none;'
                            onClick='javaScript:monthly_send("act_purchase_view"); return false;'
                            onMouseover="status='月次ベースの原材料・部品等の仕入金額を照会します。';return true;"
                            onMouseout="status=''"
                            title='月次ベースの原材料・部品等の仕入金額を照会します。'
                        >
                            仕入金額の照会
                        </a>
                    </td>
                    <td nowrap class='winbox' align='center' bgcolor='#d6d3ce'>
                        <a href='javascript:function(){}'
                            target='_self' style='text-decoration:none;'
                            onClick='javaScript:monthly_send("payable_cstd_vendor_summary2"); return false;'
                            onMouseover="status='月次ベースのカプラ標準品の外注別買掛金額(内作・諸口を含む)を照会します。';return true;"
                            onMouseout="status=''"
                            title='月次ベースのカプラ標準品の外注別買掛金額(内作・諸口を含む)を照会します。'
                        >
                            Ｃ標準 外注別 買掛(内作・諸口)
                        </a>
                    </td>
                    <td nowrap class='winbox' align='center' bgcolor='#d6d3ce'>
                        <a href='javascript:function(){}'
                            target='_self' style='text-decoration:none;'
                            onClick='javaScript:monthly_send("payable_ctoku_view"); return false;'
                            onMouseover="status='月次ベースのカプラ特注品の買掛金額(協力工場順)を照会します。';return true;"
                            onMouseout="status=''"
                            title='月次ベースのカプラ特注品の買掛金額(協力工場順)を照会します。'
                        >
                            カプラ特注の買掛明細
                        </a>
                    </td>
                    <td nowrap class='winbox' align='center' bgcolor='#d6d3ce'>
                        <a href='javascript:function(){}'
                            target='_self' style='text-decoration:none;'
                            onClick='javaScript:monthly_send("payable_ctoku_vendor_summary"); return false;'
                            onMouseover="status='月次ベースのカプラ特注品の買掛金額(協力工場毎の合計金額)を照会します。';return true;"
                            onMouseout="status=''"
                            title='月次ベースのカプラ特注品の買掛金額(協力工場毎の合計金額)を照会します。'
                        >
                            カプラ特注 外注別 買掛
                        </a>
                    </td>
                </tr>
                <tr>
                    <td nowrap class='winbox' align='center' bgcolor='#d6d3ce'>
                        <a href='javascript:function(){}'
                            target='_self' style='text-decoration:none;'
                            onClick='javaScript:monthly_send("payable_ctoku_view2"); return false;'
                            onMouseover="status='月次ベースのカプラ特注品の買掛金額(協力工場順)を照会します。(内作・諸口を含む)';return true;"
                            onMouseout="status=''"
                            title='月次ベースのカプラ特注品の買掛金額(協力工場順)を照会します。(内作・諸口を含む)'
                        >
                            Ｃ特注の買掛(内作・諸口)
                        </a>
                    </td>
                    <td nowrap class='winbox' align='center' bgcolor='#d6d3ce'>
                        <a href='javascript:function(){}'
                            target='_self' style='text-decoration:none;'
                            onClick='javaScript:monthly_send("payable_ctoku_vendor_summary2"); return false;'
                            onMouseover="status='月次ベースのカプラ特注品の買掛金額(協力工場毎の合計金額)を照会します。(内作・諸口を含む)';return true;"
                            onMouseout="status=''"
                            title='月次ベースのカプラ特注品の買掛金額(協力工場毎の合計金額)を照会します。(内作・諸口を含む)'
                        >
                            Ｃ特注 外注別 買掛(内作・諸口)
                        </a>
                    </td>
                    <td nowrap class='winbox' align='center' bgcolor='#d6d3ce'>
                        <a href='javascript:function(){}'
                            target='_self' style='text-decoration:none;'
                            onClick='javaScript:monthly_send("payable_linear_vendor_summary2"); return false;'
                            onMouseover="status='月次ベースのリニアの買掛金額(協力工場毎の合計金額)を照会します。(内作・諸口を含む)';return true;"
                            onMouseout="status=''"
                            title='月次ベースのリニアの買掛金額(協力工場毎の合計金額)を照会します。(内作・諸口を含む)'
                        >
                            リニア 外注別 買掛(内作・諸口)
                        </a>
                    </td>
                    <td nowrap class='winbox' align='center' bgcolor='#d6d3ce'>
                        <a href='javascript:function(){}'
                            target='_self' style='text-decoration:none;'
                            onClick='location.replace("<?php echo $menu->out_action('納入予定金額照会')?>"); return false;'
                            onMouseover="status='部品の在庫経歴の照会を行います。';return true;"
                            onMouseout="status=''"
                            title='部品の在庫経歴の照会を行います。'
                        >
                            納入予定金額照会
                        </a>
                    </td>
                </tr>
            </table>
            </td></tr>
        </table> <!-- ダミーEnd -->
        </form>
        
        <hr color='797979' width='95%'>
        
        <table bgcolor='#d6d3ce'  border='1' cellspacing='0' cellpadding='3'>
            <tr><td> <!-- ダミー(デザイン用) -->
            <table class='winbox_field' width='100%' border='1' cellspacing='0' cellpadding='3'>
                <tr>
                    <td nowrap width='810' class='winbox' colspan='4' align='center' style='font-size:11pt; font-weight:bold; background-color:#ffffa6; color:blue;'>
                        マスター照会・編集メニュー
                    </td>
                </tr>
                <tr>
                    <td nowrap class='winbox' align='center' bgcolor='#d6d3ce' width='170' nowrap>
                        <a href='javascript:function(){}'
                            target='_self' style='text-decoration:none;'
                            onClick='location.replace("<?php echo $menu->out_action('アイテムマスター')?>"); return false;'
                            onMouseover="status='部品・製品のアイテムマスターの照会・編集を行います。';return true;"
                            onMouseout="status=''"
                            title='部品・製品のアイテムマスターの照会・編集を行います。'
                        >部品・製品のアイテム</a>
                    </td>
                    <td nowrap class='winbox' align='center' bgcolor='#d6d3ce' width='170' nowrap>
                        <a href='javascript:function(){}'
                            target='_self' style='text-decoration:none;'
                            onClick='location.replace("ind_branch.php?ind_name=vendor_master_view"); return false;'
                            onMouseover="status='発注先マスターの照会・編集を行います。(現在は編集は出来ません)';return true;"
                            onMouseout="status=''"
                            title='発注先マスターの照会・編集を行います。(現在は編集は出来ません)'
                        >発注先マスター照会</a>
                    </td>
                    <td nowrap class='winbox' align='center' bgcolor='#d6d3ce' width='170' nowrap>
                        <a href='javascript:function(){}'
                            target='_self' style='text-decoration:none;'
                            onClick='location.replace("<?php echo $menu->out_action('品名による番号検索')?>"); return false;'
                            onMouseover="status='部品・製品のアイテムを品名による全文検索を行います。';return true;"
                            onMouseout="status=''"
                            title='部品・製品のアイテムを品名による全文検索を行います。'
                        >品名によるマスタ検索</a>
                    </td>
                    <td nowrap class='winbox' align='center' bgcolor='#d6d3ce' width='170' nowrap>
                        <a href='javascript:function(){}'
                            target='_self' style='text-decoration:none;'
                            onClick='location.replace("<?php echo $menu->out_action('製品グループコード')?>"); return false;'
                            onMouseover="status='製品グループコードのマスターを照会･登録します。';return true;"
                            onMouseout="status=''"
                            title='製品グループコードのマスターを照会･登録します。'
                        >製品グループコード</a>
                    </td>
                <tr>
                    <td nowrap class='winbox' align='center' bgcolor='#d6d3ce' width='170' nowrap>
                        &nbsp;
                    </td>
                    <td nowrap class='winbox' align='center' bgcolor='#d6d3ce' width='170' nowrap>
                        &nbsp;
                    </td>
                    <td nowrap class='winbox' align='center' bgcolor='#d6d3ce' width='170' nowrap>
                        &nbsp;
                    </td>
                    </td>
                    <td nowrap class='winbox' align='center' bgcolor='#d6d3ce' width='170' nowrap>
                        &nbsp;
                    </td>
                </tr>
                </tr>
            </table>
            </td></tr>
        </table> <!-- ダミーEnd -->
        
        <hr color='797979' width='95%'>
        
        <table bgcolor='#d6d3ce'  border='1' cellspacing='0' cellpadding='3'>
            <tr><td> <!-- ダミー(デザイン用) -->
            <table class='winbox_field' width='100%' border='1' cellspacing='0' cellpadding='3'>
                <tr>
                    <td nowrap width='810' class='winbox' colspan='5' align='center' style='font-size:11pt; font-weight:bold; background-color:#ffffa6; color:blue;'>
                        発注・検査・資材・組立メニュー
                    </td>
                </tr>
                <tr>
                    <td nowrap class='winbox' align='center' bgcolor='#d6d3ce'>
                        <a href='javascript:function(){}'
                            target='_self' style='text-decoration:none;'
                            onClick='location.replace("<?php echo $menu->out_action('納入予定と未検収明細')?>"); return false;'
                            onMouseover="status='部品の納入予定(次工程品を含む)と納期遅れ、及び検査仕掛明細を照会します。';return true;"
                            onMouseout="status=''"
                            title='部品の納入予定(次工程品を含む)と納期遅れ、及び検査仕掛明細を照会します。'
                        >
                            納入予定と検査仕掛明細
                        </a>
                    </td>
                    <td nowrap class='winbox' align='center' bgcolor='#d6d3ce'>
                        <a href='javascript:function(){}'
                            target='_self' style='text-decoration:none;'
                            onClick='location.replace("order/order_branch.php?script_name=<?php echo $menu->out_action('検査依頼')?>"); return false;'
                            onMouseover="status='部品の検査仕掛品又は納入予定品に対して検査依頼を行います。';return true;"
                            onMouseout="status=''"
                            title='部品の検査仕掛品又は納入予定品に対して検査依頼を行います。'
                        >
                            検査依頼リスト
                        </a>
                    </td>
                    <td nowrap class='winbox' align='center' bgcolor='#d6d3ce'>
                        <a href='javascript:function(){}'
                            target='_self' style='text-decoration:none;'
                            onClick='location.replace("<?php echo $menu->out_action('発注工程のメンテ')?>"); return false;'
                            onMouseover="status='部品の発注工程のメンテナンスを行います。';return true;"
                            onMouseout="status=''"
                            title='部品の発注工程のメンテナンスを行います。'
                        >
                        発注工程のメンテ
                        </a>
                    </td>
                    <td nowrap class='winbox' align='center' bgcolor='#d6d3ce'>
                        <a href='javascript:function(){}'
                            target='_blank' style='text-decoration:none;'
                            onClick='location.replace("<?php echo $menu->out_action('新規オーダー特注')?>"); return false;'
                            onMouseover="status='特注カプラ用の新規オーダーのご案内をＰＤＦ出力(印刷)します。';return true;"
                            onMouseout="status=''"
                            title='特注カプラ用の新規オーダーのご案内をＰＤＦ出力(印刷)します。'
                        >
                            新規オーダー特注
                        </a>
                    </td>
                    <td nowrap class='winbox' align='center' bgcolor='#d6d3ce'>
                        <a href='javascript:function(){}'
                            target='_blank' style='text-decoration:none;'
                            onClick='location.replace("<?php echo $menu->out_action('新規オーダー標準')?>"); return false;'
                            onMouseover="status='標準カプラ用の新規オーダーのご案内をＰＤＦ出力(印刷)します。';return true;"
                            onMouseout="status=''"
                            title='標準カプラ用の新規オーダーのご案内をＰＤＦ出力(印刷)します。'
                        >
                            新規オーダー標準
                        </a>
                    </td>
                </tr>
                <tr>
                    <td nowrap class='winbox' align='center' bgcolor='#d6d3ce'>
                        <a href='javascript:function(){}'
                            target='_self' style='text-decoration:none;'
                            onClick='location.replace("<?php echo $menu->out_action('協力工場別注残リスト')?>"); return false;'
                            onMouseover="status='指定された条件の注残リストをポップアップ表示します。';return true;"
                            onMouseout="status=''"
                            title='指定された条件の注残リストをポップアップ表示します。'
                        >
                            協力工場別注残リスト
                        </a>
                    </td>
                    <td nowrap class='winbox' align='center' bgcolor='#d6d3ce'>
                        <a href='javascript:function(){}'
                            target='_self' style='text-decoration:none;'
                            onClick='location.replace("<?php echo $menu->out_action('組立実績編集')?>"); return false;'
                            onMouseover="status='組立実績の照会及び追加・修正を行います。';return true;"
                            onMouseout="status=''"
                            title='組立実績の照会及び追加・修正を行います。'
                        >
                            組立実績データの編集
                        </a>
                    </td>
                    <td nowrap class='winbox' align='center' bgcolor='#d6d3ce'>
                        <a href='javascript:function(){}'
                            target='_self' style='text-decoration:none;'
                            onClick='location.replace("<?php echo $menu->out_action('組立状況照会')?>"); return false;'
                            onMouseover="status='組立の着手・完了の照会を行います。';return true;"
                            onMouseout="status=''"
                            title='組立の着手・完了の照会を行います。'
                        >
                            組立状況の照会
                        </a>
                    </td>
                    <td nowrap class='winbox' align='center' bgcolor='#d6d3ce'>
                        <a href='javascript:function(){}'
                            target='_self' style='text-decoration:none;'
                            onClick='location.replace("<?php echo $menu->out_action('組立指示')?>"); return false;'
                            onMouseover="status='組立指示メニュー 開始指示 及び 完了指示 を行います。';return true;"
                            onMouseout="status=''"
                            title='組立指示メニュー 開始指示 及び 完了指示 を行います。'
                        >
                            組立指示メニュー
                        </a>
                    </td>
                    <td nowrap class='winbox' align='center' bgcolor='#d6d3ce'>
                        <a href='javascript:function(){}'
                            target='_self' style='text-decoration:none;'
                            onClick='location.replace("<?php echo $menu->out_action('資材部品出庫')?>"); return false;'
                            onMouseover="status='資材管理の組立へ部品出庫 開始指示 及び 完了指示 を行います。';return true;"
                            onMouseout="status=''"
                            title='資材管理の組立へ部品出庫 開始指示 及び 完了指示 を行います。'
                        >
                            資材部品出庫メニュー
                        </a>
                    </td>
                </tr>
                <tr>
                    <td nowrap class='winbox' align='center' bgcolor='#d6d3ce'>
                        <a href='javascript:function(){}'
                            target='_self' style='text-decoration:none;'
                            onClick='location.replace("<?php echo $menu->out_action('日程計画照会')?>"); return false;'
                            onMouseover="status='大日程(AS/400)の組立日程計画表の照会を行います。';return true;"
                            onMouseout="status=''"
                            title='大日程(AS/400)の組立日程計画表の照会を行います。'
                        >
                            組立日程計画の照会
                        </a>
                    </td>
                    <td nowrap class='winbox' align='center' bgcolor='#d6d3ce'>
                        <a href='javascript:function(){}'
                            target='_self' style='text-decoration:none;'
                            onClick='location.replace("<?php echo $menu->out_action('実績工数照会')?>"); return false;'
                            onMouseover="status='組立の登録工数と実際の工数を比較照会します。';return true;"
                            onMouseout="status=''"
                            title='組立の登録工数と実際の工数を比較照会します。'
                        >
                            実績工数の照会
                        </a>
                    </td>
                    <td nowrap class='winbox' align='center' bgcolor='#d6d3ce'>
                        <a href='javascript:function(){}'
                            target='_self' style='text-decoration:none;'
                            onClick='location.replace("<?php echo $menu->out_action('完成一覧工数')?>"); return false;'
                            onMouseover="status='組立の完成一覧より実績工数と登録工数の比較 照会します。';return true;"
                            onMouseout="status=''"
                            title='組立の完成一覧より実績工数と登録工数の比較 照会します。'
                        >
                            組立完成工数比較
                        </a>
                    </td>
                    <td nowrap class='winbox' align='center' bgcolor='#d6d3ce'>
                        <a href='javascript:function(){}'
                            target='_self' style='text-decoration:none;'
                            onClick='location.replace("<?php echo $menu->out_action('ライン別工数グラフ')?>"); return false;'
                            onMouseover="status='組立のライン別 工数 グラフ 照会します。';return true;"
                            onMouseout="status=''"
                            title='組立のライン別 工数 グラフ 照会します。'
                        >
                            ライン別工数グラフ
                        </a>
                    </td>
                    <td nowrap class='winbox' colspan='1' align='center' bgcolor='#d6d3ce'>
                        <a href='javascript:function(){}'
                            target='_self' style='text-decoration:none;'
                            onClick='location.replace("ind_branch.php?ind_name=datasum_barcode"); return false;'
                            onMouseover="status='データサムのバーコードカードを個人毎に作成します。';return true;"
                            onMouseout="status=''"
                            title='データサムのバーコードカードを個人毎に作成します。'
                        >
                            バーコード作成
                        </a>
                    </td>
                </tr>
                <tr>
                    <td nowrap class='winbox' align='center' bgcolor='#d6d3ce'>
                        <a href='javascript:function(){}'
                            target='_self' style='text-decoration:none;'
                            onClick='location.replace("<?php echo $menu->out_action('ラインカレンダー')?>"); return false;'
                            onMouseover="status='組立ラインのカレンダーによるスケジュールの編集を行います。';return true;"
                            onMouseout="status=''"
                            title='組立ラインのカレンダーによるスケジュールの編集を行います。'
                        >
                            ラインカレンダー編集
                        </a>
                    </td>
                    <td nowrap class='winbox' align='center' bgcolor='#d6d3ce'>
                        <a href='javascript:function(){}'
                            target='_self' style='text-decoration:none;'
                            onClick='location.replace("<?php echo $menu->out_action('刻印管理システム')?>"); return false;'
                            onMouseover="status='刻印管理システム メニューへ進みます。マスター編集・検索・貸出管理等を行います。';return true;"
                            onMouseout="status=''"
                            title='刻印管理システム メニューへ進みます。マスター編集・検索・貸出管理等を行います。'
                        >
                            刻印管理システム
                        </a>
                    </td>
                    <td nowrap class='winbox' align='center' bgcolor='#d6d3ce'>
                        <a href='javascript:function(){}'
                            target='_self' style='text-decoration:none;'
                            onClick='location.replace("<?php echo $menu->out_action('特注成績書印刷')?>"); return false;'
                            onMouseover="status='特注カプラの完成品検査成績書の印刷を計画番号をバーコードで入力する事により行います。';return true;"
                            onMouseout="status=''"
                            title='特注カプラの完成品検査成績書の印刷を計画番号をバーコードで入力する事により行います。'
                        >
                            特注成績書印刷
                        </a>
                    </td>
                    <td nowrap class='winbox' align='center' bgcolor='#d6d3ce'>
                        <a href='javascript:function(){}'
                            target='_self' style='text-decoration:none;'
                            onClick='location.replace("<?php echo $menu->out_action('繰返し注文集計')?>"); return false;'
                            onMouseover="status='リピート部品発注の多い順に集計を行います。';return true;"
                            onMouseout="status=''"
                            title='リピート部品発注の多い順に集計を行います。'
                        >
                            リピート発注の集計
                        </a>
                    </td>
                    <td nowrap class='winbox' align='center' bgcolor='#d6d3ce'>
                        <a href='javascript:function(){}'
                            target='_self' style='text-decoration:none;'
                            onClick='location.replace("<?php echo $menu->out_action('リニア部品出庫')?>"); return false;'
                            onMouseover="status='リニア資材専用の組立へ部品出庫 開始指示 及び 完了指示 を行います。';return true;"
                            onMouseout="status=''"
                            title='リニア資材専用の組立へ部品出庫 開始指示 及び 完了指示 を行います。'
                        >
                            リニア出庫メニュー
                        </a>
                    </td>
                </tr>
                <tr>
                    <td nowrap class='winbox' align='center' bgcolor='#d6d3ce'>
                        <a href='javascript:function(){}'
                            target='_self' style='text-decoration:none;'
                            onClick='location.replace("<?php echo $menu->out_action('納期遅れ部品の照会')?>"); return false;'
                            onMouseover="status='納期遅れが発生した部品を照会します。';return true;"
                            onMouseout="status=''"
                            title='納期遅れが発生した部品を照会します。'
                        >
                            納期遅れ部品の照会
                        </a>
                    </td>
                    <td nowrap class='winbox' align='center' bgcolor='#d6d3ce'>
                        <a href='javascript:function(){}'
                            target='_self' style='text-decoration:none;'
                            onClick='location.replace("<?php echo $menu->out_action('特注カプラ冶具作業注意点メニュー')?>"); return false;'
                            onMouseover="status='特注カプラの不適合連絡書の照会と組立冶工具・方法を照会します。';return true;"
                            onMouseout="status=''"
                            title='特注カプラの不適合連絡書の照会と組立冶工具・方法を照会します。'
                        >
                            特注カプラ冶具・作業注意点
                        </a>
                    </td>
                    <?php
                    if ($_SESSION['User_ID'] == '300144') {
                    ?>
                    <td nowrap class='winbox' align='center' bgcolor='#d6d3ce'>
                        <a href='javascript:function(){}'
                            target='_self' style='text-decoration:none;'
                            onClick='location.replace("<?php echo $menu->out_action('検査日数集計')?>"); return false;'
                            onMouseover="status='検査日数集計を照会します。';return true;"
                            onMouseout="status=''"
                            title='検査日数集計を照会します。'
                        >
                            検査日数集計
                        </a>
                    </td>
                    <?php
                     } else {
                      ?>
                    <td nowrap class='winbox' colspan='1' align='center' bgcolor='#d6d3ce'>
                        &nbsp;
                    </td>
                    <?php
                    }
                    ?>
                    <td nowrap class='winbox' align='center' bgcolor='#d6d3ce' width='170' nowrap>
                        <a href='javascript:function(){}'
                            target='_self' style='text-decoration:none;'
                            onClick='location.replace("<?php echo $menu->out_action('対象製品集計メニュー')?>"); return false;'
                            onMouseover="status='対応製品関連のメニューを表示します。';return true;"
                            onMouseout="status=''"
                            title='対象製品関連のメニューを表示します。'
                        >
                            対象製品集計メニュー
                        </a>
                    </td>
                    <td nowrap class='winbox' align='center' bgcolor='#d6d3ce' width='170' nowrap>
                        <a href='javascript:function(){}'
                            target='_self' style='text-decoration:none;'
                            onClick='location.replace("<?php echo $menu->out_action('生産支援関連メニュー')?>"); return false;'
                            onMouseover="status='生産支援関連のメニューを表示します。';return true;"
                            onMouseout="status=''"
                            title='生産支援関連のメニューを表示します。'
                        >
                            生産支援関連メニュー
                        </a>
                    </td>
                </tr>
                <tr>
                    <?php
                    if ($_SESSION['User_ID'] == '300667' || $_SESSION['User_ID'] == '300144') {
                    ?>
                    <td nowrap class='winbox' align='center' bgcolor='#d6d3ce'>
                        <a href='javascript:function(){}'
                            target='_self' style='text-decoration:none;'
                            onClick='location.replace("<?php echo $menu->out_action('完成一覧工数編集')?>"); return false;'
                            onMouseover="status='完成一覧工数を編集します。';return true;"
                            onMouseout="status=''"
                            title='完成一覧工数を編集します。'
                        >
                            組立完成編集(工数/金額)
                        </a>
                    </td>
                    <?php
                    } else {
                      ?>
                    <td nowrap class='winbox' colspan='1' align='center' bgcolor='#d6d3ce'>
                        &nbsp;
                    </td>
                    <?php
                    }
                    ?>
                    <td nowrap class='winbox' colspan='1' align='center' bgcolor='#d6d3ce'>
                        &nbsp;
                    </td>
                    <td nowrap class='winbox' colspan='1' align='center' bgcolor='#d6d3ce'>
                        &nbsp;
                    </td>
                    <td nowrap class='winbox' colspan='1' align='center' bgcolor='#d6d3ce'>
                        &nbsp;
                    </td>
                    <td nowrap class='winbox' colspan='1' align='center' bgcolor='#d6d3ce'>
                        &nbsp;
                    </td>
                </tr>
            </table>
            </td></tr>
        </table> <!-- ダミーEnd -->
        
        <hr color='797979' width='95%'>
        <?php
        }
        ?>
        
    </center>
</body>
<?php echo $menu->out_alert_java()?>
</html>
<?php
ob_end_flush();                 // 出力バッファをgzip圧縮 END
?>
