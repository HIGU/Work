<?php
//////////////////////////////////////////////////////////////////////////////
// 月次損益処理のSUBMIT Branch(分岐)処理                                    //
// Copyright (C) 2003-2021      Kazuhiro.Kobayashi tnksys@nitto-kohki.co.jp //
// Changed history                                                          //
// 2003/01/17 Created   profit_loss_submit.php                              //
// 2003/01/27 AS/400 との個別データリンクメニュー追加の対応                 //
// 2003/02/07 データ更新準備作業フォーム追加の対応                          //
// 2003/02/22 棚卸高調整・仕入高(要素別買掛データ)調整プログラム追加        //
// 2003/02/24 売上原価調整入力のメニューを追加                              //
// 2003/02/26 業務委託収入の入力をメニューに追加                            //
// 2003/03/07 照会メニューを別のURLに置いたため呼出もとのURLを保存          //
// 2003/03/10 売上高の調整入力メニューを追加                                //
// 2003/05/01 工場長からの指示で認証をAccount_groupから通常へ変更           //
// 2003/08/05 工場長からの指示で認証を通常からAccount_groupへ戻す           //
//              工場長の人件費が分かるため                                  //
// 2003/09/27 月次 比較棚卸表(データ取込みと照会)の追加                     //
// 2003/10/10 月次 データの削除処理追加 profit_loss_clear.php?pl_table=     //
// 2003/10/15 スクリプト別にユーザー権限のチェックを変更 比較棚卸表照会     //
// 2003/11/27 tnk-turbine.gif のアニメーションを追加                        //
// 2004/05/06 リテラルで指定していた'kessan/'→ defineされた PL へ変更      //
// 2007/10/10 セグメント別 損益計算書のデータ取込メニューを追加             //
// 2008/10/07 CL経費差額比較表の追加                                   大谷 //
// 2009/08/18 物流・試修損益登録を追加（11）                                //
//            セグメント別取込を変更(11→12)                           大谷 //
// 2009/08/19 物流を商管に変更                                         大谷 //
//            旧ＣＬ商品別損益照会を追加                               大谷 //
// 2009/08/20 旧CL経費経費実績表照会を追加                                  //
//            メニュー追加の為、レイアウトを調整                       大谷 //
// 2009/08/21 ＢＬ・試験修理 商品別損益照会を追加                      大谷 //
// 2009/12/09 試験修理（ＣＬ）商品別損益照会を追加                     大谷 //
// 2010/01/15 損益対前月比較表を追加                                   大谷 //
// 2010/01/19 作成と照会で管理権限を分けるように変更 新しいメニューを  大谷 //
//            追加する際は認証の分割にも追加すること                   大谷 //
// 2012/01/16 ２期比較表の照会を追加                                   大谷 //
// 2015/06/04 BLをLTに変更                                             大谷 //
// 2015/06/15 LT損益を2015年4月からしか開けないように変更              大谷 //
// 2016/07/13 CLT商品別損益を追加                                      大谷 //
// 2016/07/25 試験修理商品別損益をＣＬから耐久・修理に変更             大谷 //
// 2017/09/08 製造原価計算を追加                                       大谷 //
// 2017/11/09 機工損益修正を10月で一括で行った損益照会を追加           大谷 //
// 2018/05/29 決算報告書を追加（自分のみ）                             大谷 //
// 2018/06/12 勘定科目組替表を追加（自分のみ）                         大谷 //
// 2020/01/27 減価償却費明細表を追加                                   大谷 //
// 2020/06/12 勘定科目内訳明細書を追加                                 大谷 //
// 2021/05/31 2021/04より商品別損益を分岐 2021/04以降照会はツールなし。     //
//            2021/03以前の照会でツール表示                            大谷 //
// 2021/08/02 $_SESSION['2ki_ym']のエラーに対応                        大谷 //
//////////////////////////////////////////////////////////////////////////////
// ini_set('error_reporting', E_ALL | E_STRICT);
// ini_set('display_errors', '1');             // Error 表示 ON debug 用 リリース後コメント
session_start();                            // ini_set()の次に指定すること Script 最上行
require_once ("../function.php");
require_once ("../tnk_func.php");
access_log();       // Script Name は自動取得
if (isset($_POST['pl_ym'])) {
    $_SESSION['pl_ym'] = $_POST['pl_ym'];                   // 対象年月をセッションに保存
}
if (isset($_POST['2ki_ym'])) {
    $_SESSION['2ki_ym'] = $_POST['2ki_ym'];                   // 対象年月をセッションに保存
}

$_SESSION['pl_referer'] = $_SERVER['HTTP_REFERER'];     // 呼出もとのURLをセッションに保存

////////// 分岐先のスクリプト名を取得
if ($_SESSION['2ki_ym'] >= 202104) {
    if ($_SESSION['pl_ym'] >= 202104) {
        switch ($_POST['pl_name']) {
            case '1 AS/400→TNK'            : $script_name = 'profit_loss_ftp_to_db_all.php'; break;
            case '2 CL配賦率計算'           : $script_name = 'profit_loss_cl_allocation.php'; break;
            case '3 経費配賦実行'           : $script_name = 'profit_loss_cl_keihi_save.php'; break;
            case '4 棚卸高入力'             : $script_name = 'profit_loss_inventory_put.php'; break;
            case '5 棚卸高調整'             : $script_name = 'profit_loss_adjust_invent.php'; break;
            case '6 仕入高調整'             : $script_name = 'profit_loss_adjust_shiire.php'; break;
            case '7 売上原価調整'           : $script_name = 'profit_loss_adjust_ugenka.php'; break;
            case '8 業務委託入力'           : $script_name = 'profit_loss_gyoumu_put.php'   ; break;
            case '9 売上高調整'             : $script_name = 'profit_loss_adjust_uriage.php'; break;
            case '10 ＣＬ損益計算'          : $script_name = 'profit_loss_pl_act_save.php'  ; break;
            case '11 商管・試修損益登録'    : $script_name = 'profit_loss_nkb_input.php'    ; break;
            //case '12 セグメント別取込'    : $script_name = 'pl_segment/pl_segment_get_form.php'   ; break;
            case '12 Ｌ人員比率計算'        : $script_name = 'profit_loss_bls_input.php'    ; break;
            case '13 Ｃ人員比率計算'        : $script_name = 'profit_loss_ctoku_input.php'  ; break;
            case '14 全社人員比率計算'      : $script_name = 'profit_loss_staff_input.php'  ; break;
            
            case '経費実績内訳'                     : $script_name = 'profit_loss_keihi.php'            ; break;
            case 'ＣＬ・商管 経費実績表'            : $script_name = 'profit_loss_cl_keihi.php'         ; break;
            case 'セグメント別損益'                 : $script_name = 'profit_loss_pl_act.php'           ; break;
            case 'セグメント別損益10月一括'         : $script_name = 'profit_loss_pl_act10.php'         ; break;
            case 'セグメント別損益機工'             : $script_name = 'profit_loss_pl_act_t-bk.php'      ; break;
            case '貸借対照表'                       : $script_name = 'profit_loss_bs_act.php'           ; break;
            case 'ＣＬ予実比損益'                   : $script_name = 'profit_loss_select.php'           ; break;
            case '原価率計算表'                     : $script_name = 'profit_loss_cost_rate.php'        ; break;
            case '面積配布表'                       : $script_name = 'profit_loss_select.php'           ; break;
            case 'ＣＬ経費差額比較表'               : $script_name = 'profit_loss_cl_keihi_compare.php' ; break;
            case 'ＢＬ・試修 商品別損益'            : $script_name = 'profit_loss_pl_act_bls.php'       ; break;
            case '特注・標準 商品別損益'            : $script_name = 'profit_loss_pl_act_ctoku.php'     ; break;
            case '試験・修理 商品別損益'            : $script_name = 'profit_loss_pl_act_ss.php'        ; break;
            case '旧ＣＬ商品別損益'                 : $script_name = 'profit_loss_pl_act_old.php'       ; break;
            case '旧ＣＬ経費実績表'                 : $script_name = 'profit_loss_cl_keihi_old.php'     ; break;
            case 'ＢＬ 商品別損益'                  : $script_name = 'profit_loss_pl_act_bl.php'        ; break;
            case '損益対前月比較表'                 : $script_name = 'profit_loss_pl_act_compare.php'   ; break;
            case '商品別テスト'                     : $script_name = 'profit_loss_pl_act_compare.php'   ; break;
            case 'ＬＴ 商品別損益'                  : $script_name = 'profit_loss_pl_act_lt.php'        ; break;
            case 'ＣＬＴ・試修・商管 商品別損益'    : $script_name = 'profit_loss_pl_act_all.php'       ; break;
            case '売上状況照会'                     : $script_name = 'profit_loss_sales_view.php'       ; break;
            
            case '経費内訳データ' : $script_name = 'profit_loss_ftp_to_db_D.php'    ; break;
            case 'ＣＬ経費データ' : $script_name = 'profit_loss_ftp_to_db_B.php'    ; break;
            case '科目別部門経費' : $script_name = 'profit_loss_ftp_to_db_B1.php'   ; break;
            case '要素買掛データ' : $script_name = 'profit_loss_ftp_to_db_E.php'    ; break;
            case 'ＣＬ損益データ' : $script_name = 'profit_loss_ftp_to_db_AC.php'   ; break;
            case '貸借対照データ' : $script_name = 'profit_loss_ftp_to_db_F.php'    ; break;
            
            case '総平均棚卸入力' : $script_name = 'profit_loss_invent_gross_average.php'   ; break;
            
            case 'CL配賦Clear'    : $script_name = 'profit_loss_clear.php?pl_table=allo_history'; break;
            case '経費配賦Clear'  : $script_name = 'profit_loss_clear.php?pl_table=cl_history'  ; break;
            case 'CL損益Clear'    : $script_name = 'profit_loss_clear.php?pl_table=pl_history'  ; break;
            
            case '比較棚卸表'   : $script_name = 'invent_comp/invent_comp_view.php'     ; break;
            case 'データ取込み' : $script_name = 'invent_comp/invent_comp_get_form.php' ; break;
            
            case '減価償却費明細表'   : $script_name = 'depreciation_statement/depreciation_statement_view.php'     ; break;
            case '減価償却費明細表取込' : $script_name = 'depreciation_statement/depreciation_statement_get_form.php' ; break;
            
            // ２期比較表
            case '２期 本決算損益表'    : $script_name = 'profit_loss_pl_act_2ki.php'   ; break;
            case '２期 貸借対照表'      : $script_name = 'profit_loss_bs_act_2ki.php'   ; break;
            case '２期 ＣＬ商品別損益'  : $script_name = 'profit_loss_pl_act_2ki_cl.php'; break;
            case '２期 経費実績内訳'    : $script_name = 'profit_loss_keihi_2ki.php'    ; break;
            case '製造原価計算'         : $script_name = 'manufacture_cost_total.php'   ; break;
            case '決算報告書'           : $script_name = 'financial_report_view.php'    ; break;
            case '勘定科目組替表'       : $script_name = 'account_transfer_view.php'    ; break;
            case '設備製作集計'         : $script_name = 'machine_production_view.php'  ; break;
            case '勘定科目内訳明細書'   : $script_name = 'account_statement_view.php'    ; break;
            
            // 消費税申告書
            case '未払金計上仕入額'    : $script_name = 'sales_tax_miharai_view.php'   ; break;
            case '中間納付確認'        : $script_name = 'sales_tax_chukan_view.php'   ; break;
            case '消費税集計表'        : $script_name = 'sales_tax_zeishukei_view.php'   ; break;
            case '控除税額計算表'      : $script_name = 'sales_tax_koujyo_view.php'   ; break;
            case '消費税等計算表'      : $script_name = 'sales_tax_syozei_allo_view.php'   ; break;
            case '消費税申告資料'      : $script_name = 'sales_tax_syozei_shinkoku_view.php'   ; break;
            case '確定申告書第1表'     : $script_name = 'print/sales_tax_kakutei_shinkoku1_pdf.php'   ; break;
            case '第2表'               : $script_name = 'print/sales_tax_kakutei_shinkoku2_pdf.php'   ; break;
            case '付表1-1'             : $script_name = 'print/sales_tax_kakutei_fuhyo1-1_pdf.php'   ; break;
            case '付表1-2'             : $script_name = 'print/sales_tax_kakutei_fuhyo1-2_pdf.php'   ; break;
            case '付表2-1'             : $script_name = 'print/sales_tax_kakutei_fuhyo2-1_pdf.php'   ; break;
            case '付表2-2'             : $script_name = 'print/sales_tax_kakutei_fuhyo2-2_pdf.php'   ; break;
            
            default: $script_name = 'profit_loss_select.php';       // 呼出もとへ帰る
                 $url_name    = $_SESSION['pl_referer'];        // 呼出もとのURL 別メニューから呼び出された時の対応
        }
    } else {
        switch ($_POST['pl_name']) {
            case '1 AS/400→TNK'            : $script_name = 'profit_loss_ftp_to_db_all.php'; break;
            case '2 CL配賦率計算'           : $script_name = 'profit_loss_cl_allocation.php'; break;
            case '3 経費配賦実行'           : $script_name = 'profit_loss_cl_keihi_save.php'; break;
            case '4 棚卸高入力'             : $script_name = 'profit_loss_inventory_put.php'; break;
            case '5 棚卸高調整'             : $script_name = 'profit_loss_adjust_invent.php'; break;
            case '6 仕入高調整'             : $script_name = 'profit_loss_adjust_shiire.php'; break;
            case '7 売上原価調整'           : $script_name = 'profit_loss_adjust_ugenka.php'; break;
            case '8 業務委託入力'           : $script_name = 'profit_loss_gyoumu_put.php'   ; break;
            case '9 売上高調整'             : $script_name = 'profit_loss_adjust_uriage.php'; break;
            case '10 ＣＬ損益計算'          : $script_name = 'profit_loss_pl_act_save.php'  ; break;
            case '11 商管・試修損益登録'    : $script_name = 'profit_loss_nkb_input.php'    ; break;
            //case '12 セグメント別取込'    : $script_name = 'pl_segment/pl_segment_get_form.php'   ; break;
            case '12 Ｌ人員比率計算'        : $script_name = 'profit_loss_bls_input.php'    ; break;
            case '13 Ｃ人員比率計算'        : $script_name = 'profit_loss_ctoku_input.php'  ; break;
            case '14 全社人員比率計算'      : $script_name = 'profit_loss_staff_input.php'  ; break;
            
            case '経費実績内訳'                     : $script_name = 'profit_loss_keihi.php'            ; break;
            case 'ＣＬ・商管 経費実績表'            : $script_name = 'profit_loss_cl_keihi.php'         ; break;
            case 'セグメント別損益'                 : $script_name = 'profit_loss_pl_act_t-bk.php'       ; break;
            case 'セグメント別損益10月一括'         : $script_name = 'profit_loss_pl_act10.php'         ; break;
            case 'セグメント別損益機工'             : $script_name = 'profit_loss_pl_act_t-bk.php'      ; break;
            case '貸借対照表'                       : $script_name = 'profit_loss_bs_act.php'           ; break;
            case 'ＣＬ予実比損益'                   : $script_name = 'profit_loss_select.php'           ; break;
            case '原価率計算表'                     : $script_name = 'profit_loss_cost_rate.php'        ; break;
            case '面積配布表'                       : $script_name = 'profit_loss_select.php'           ; break;
            case 'ＣＬ経費差額比較表'               : $script_name = 'profit_loss_cl_keihi_compare.php' ; break;
            case 'ＢＬ・試修 商品別損益'            : $script_name = 'profit_loss_pl_act_bls.php'       ; break;
            case '特注・標準 商品別損益'            : $script_name = 'profit_loss_pl_act_ctoku.php'     ; break;
            case '試験・修理 商品別損益'            : $script_name = 'profit_loss_pl_act_ss.php'        ; break;
            case '旧ＣＬ商品別損益'                 : $script_name = 'profit_loss_pl_act_old.php'       ; break;
            case '旧ＣＬ経費実績表'                 : $script_name = 'profit_loss_cl_keihi_old.php'     ; break;
            case 'ＢＬ 商品別損益'                  : $script_name = 'profit_loss_pl_act_bl.php'        ; break;
            case '損益対前月比較表'                 : $script_name = 'profit_loss_pl_act_compare.php'   ; break;
            case '商品別テスト'                     : $script_name = 'profit_loss_pl_act_compare.php'   ; break;
            case 'ＬＴ 商品別損益'                  : $script_name = 'profit_loss_pl_act_lt.php'        ; break;
            case 'ＣＬＴ・試修・商管 商品別損益'    : $script_name = 'profit_loss_pl_act_all.php'       ; break;
            case '売上状況照会'                     : $script_name = 'profit_loss_sales_view.php'       ; break;
            
            case '経費内訳データ' : $script_name = 'profit_loss_ftp_to_db_D.php'    ; break;
            case 'ＣＬ経費データ' : $script_name = 'profit_loss_ftp_to_db_B.php'    ; break;
            case '科目別部門経費' : $script_name = 'profit_loss_ftp_to_db_B1.php'   ; break;
            case '要素買掛データ' : $script_name = 'profit_loss_ftp_to_db_E.php'    ; break;
            case 'ＣＬ損益データ' : $script_name = 'profit_loss_ftp_to_db_AC.php'   ; break;
            case '貸借対照データ' : $script_name = 'profit_loss_ftp_to_db_F.php'    ; break;
            
            case '総平均棚卸入力' : $script_name = 'profit_loss_invent_gross_average.php'   ; break;
            
            case 'CL配賦Clear'    : $script_name = 'profit_loss_clear.php?pl_table=allo_history'; break;
            case '経費配賦Clear'  : $script_name = 'profit_loss_clear.php?pl_table=cl_history'  ; break;
            case 'CL損益Clear'    : $script_name = 'profit_loss_clear.php?pl_table=pl_history'  ; break;
            
            case '比較棚卸表'   : $script_name = 'invent_comp/invent_comp_view.php'     ; break;
            case 'データ取込み' : $script_name = 'invent_comp/invent_comp_get_form.php' ; break;
            
            case '減価償却費明細表'   : $script_name = 'depreciation_statement/depreciation_statement_view.php'     ; break;
            case '減価償却費明細表取込' : $script_name = 'depreciation_statement/depreciation_statement_get_form.php' ; break;
            
            // ２期比較表
            case '２期 本決算損益表'    : $script_name = 'profit_loss_pl_act_2ki.php'   ; break;
            case '２期 貸借対照表'      : $script_name = 'profit_loss_bs_act_2ki.php'   ; break;
            case '２期 ＣＬ商品別損益'  : $script_name = 'profit_loss_pl_act_2ki_cl.php'; break;
            case '２期 経費実績内訳'    : $script_name = 'profit_loss_keihi_2ki.php'    ; break;
            case '製造原価計算'         : $script_name = 'manufacture_cost_total.php'   ; break;
            case '決算報告書'           : $script_name = 'financial_report_view.php'    ; break;
            case '勘定科目組替表'       : $script_name = 'account_transfer_view.php'    ; break;
            case '設備製作集計'         : $script_name = 'machine_production_view.php'  ; break;
            case '勘定科目内訳明細書'   : $script_name = 'account_statement_view.php'    ; break;
            
            // 消費税申告書
            case '未払金計上仕入額'    : $script_name = 'sales_tax_miharai_view.php'   ; break;
            case '中間納付確認'        : $script_name = 'sales_tax_chukan_view.php'   ; break;
            case '消費税集計表'        : $script_name = 'sales_tax_zeishukei_view.php'   ; break;
            case '控除税額計算表'      : $script_name = 'sales_tax_koujyo_view.php'   ; break;
            case '消費税等計算表'      : $script_name = 'sales_tax_syozei_allo_view.php'   ; break;
            case '消費税申告資料'      : $script_name = 'sales_tax_syozei_shinkoku_view.php'   ; break;
            case '確定申告書第1表'     : $script_name = 'print/sales_tax_kakutei_shinkoku1_pdf.php'   ; break;
            case '第2表'               : $script_name = 'print/sales_tax_kakutei_shinkoku2_pdf.php'   ; break;
            case '付表1-1'             : $script_name = 'print/sales_tax_kakutei_fuhyo1-1_pdf.php'   ; break;
            case '付表1-2'             : $script_name = 'print/sales_tax_kakutei_fuhyo1-2_pdf.php'   ; break;
            case '付表2-1'             : $script_name = 'print/sales_tax_kakutei_fuhyo2-1_pdf.php'   ; break;
            case '付表2-2'             : $script_name = 'print/sales_tax_kakutei_fuhyo2-2_pdf.php'   ; break;
            
            default: $script_name = 'profit_loss_select.php';       // 呼出もとへ帰る
                 $url_name    = $_SESSION['pl_referer'];        // 呼出もとのURL 別メニューから呼び出された時の対応
        }
    }
} else {
    if ($_SESSION['pl_ym'] >= 202104) {
        switch ($_POST['pl_name']) {
            case '1 AS/400→TNK'            : $script_name = 'profit_loss_ftp_to_db_all.php'; break;
            case '2 CL配賦率計算'           : $script_name = 'profit_loss_cl_allocation.php'; break;
            case '3 経費配賦実行'           : $script_name = 'profit_loss_cl_keihi_save.php'; break;
            case '4 棚卸高入力'             : $script_name = 'profit_loss_inventory_put.php'; break;
            case '5 棚卸高調整'             : $script_name = 'profit_loss_adjust_invent.php'; break;
            case '6 仕入高調整'             : $script_name = 'profit_loss_adjust_shiire.php'; break;
            case '7 売上原価調整'           : $script_name = 'profit_loss_adjust_ugenka.php'; break;
            case '8 業務委託入力'           : $script_name = 'profit_loss_gyoumu_put.php'   ; break;
            case '9 売上高調整'             : $script_name = 'profit_loss_adjust_uriage.php'; break;
            case '10 ＣＬ損益計算'          : $script_name = 'profit_loss_pl_act_save.php'  ; break;
            case '11 商管・試修損益登録'    : $script_name = 'profit_loss_nkb_input.php'    ; break;
            //case '12 セグメント別取込'    : $script_name = 'pl_segment/pl_segment_get_form.php'   ; break;
            case '12 Ｌ人員比率計算'        : $script_name = 'profit_loss_bls_input.php'    ; break;
            case '13 Ｃ人員比率計算'        : $script_name = 'profit_loss_ctoku_input.php'  ; break;
            case '14 全社人員比率計算'      : $script_name = 'profit_loss_staff_input.php'  ; break;
            
            case '経費実績内訳'                     : $script_name = 'profit_loss_keihi.php'            ; break;
            case 'ＣＬ・商管 経費実績表'            : $script_name = 'profit_loss_cl_keihi.php'         ; break;
            case 'セグメント別損益'                 : $script_name = 'profit_loss_pl_act.php'           ; break;
            case 'セグメント別損益10月一括'         : $script_name = 'profit_loss_pl_act10.php'         ; break;
            case 'セグメント別損益機工'             : $script_name = 'profit_loss_pl_act_t-bk.php'      ; break;
            case '貸借対照表'                       : $script_name = 'profit_loss_bs_act.php'           ; break;
            case 'ＣＬ予実比損益'                   : $script_name = 'profit_loss_select.php'           ; break;
            case '原価率計算表'                     : $script_name = 'profit_loss_cost_rate.php'        ; break;
            case '面積配布表'                       : $script_name = 'profit_loss_select.php'           ; break;
            case 'ＣＬ経費差額比較表'               : $script_name = 'profit_loss_cl_keihi_compare.php' ; break;
            case 'ＢＬ・試修 商品別損益'            : $script_name = 'profit_loss_pl_act_bls.php'       ; break;
            case '特注・標準 商品別損益'            : $script_name = 'profit_loss_pl_act_ctoku.php'     ; break;
            case '試験・修理 商品別損益'            : $script_name = 'profit_loss_pl_act_ss.php'        ; break;
            case '旧ＣＬ商品別損益'                 : $script_name = 'profit_loss_pl_act_old.php'       ; break;
            case '旧ＣＬ経費実績表'                 : $script_name = 'profit_loss_cl_keihi_old.php'     ; break;
            case 'ＢＬ 商品別損益'                  : $script_name = 'profit_loss_pl_act_bl.php'        ; break;
            case '損益対前月比較表'                 : $script_name = 'profit_loss_pl_act_compare.php'   ; break;
            case '商品別テスト'                     : $script_name = 'profit_loss_pl_act_compare.php'   ; break;
            case 'ＬＴ 商品別損益'                  : $script_name = 'profit_loss_pl_act_lt.php'        ; break;
            case 'ＣＬＴ・試修・商管 商品別損益'    : $script_name = 'profit_loss_pl_act_all.php'       ; break;
            case '売上状況照会'                     : $script_name = 'profit_loss_sales_view.php'       ; break;
            
            case '経費内訳データ' : $script_name = 'profit_loss_ftp_to_db_D.php'    ; break;
            case 'ＣＬ経費データ' : $script_name = 'profit_loss_ftp_to_db_B.php'    ; break;
            case '科目別部門経費' : $script_name = 'profit_loss_ftp_to_db_B1.php'   ; break;
            case '要素買掛データ' : $script_name = 'profit_loss_ftp_to_db_E.php'    ; break;
            case 'ＣＬ損益データ' : $script_name = 'profit_loss_ftp_to_db_AC.php'   ; break;
            case '貸借対照データ' : $script_name = 'profit_loss_ftp_to_db_F.php'    ; break;
            
            case '総平均棚卸入力' : $script_name = 'profit_loss_invent_gross_average.php'   ; break;
            
            case 'CL配賦Clear'    : $script_name = 'profit_loss_clear.php?pl_table=allo_history'; break;
            case '経費配賦Clear'  : $script_name = 'profit_loss_clear.php?pl_table=cl_history'  ; break;
            case 'CL損益Clear'    : $script_name = 'profit_loss_clear.php?pl_table=pl_history'  ; break;
            
            case '比較棚卸表'   : $script_name = 'invent_comp/invent_comp_view.php'     ; break;
            case 'データ取込み' : $script_name = 'invent_comp/invent_comp_get_form.php' ; break;
            
            case '減価償却費明細表'   : $script_name = 'depreciation_statement/depreciation_statement_view.php'     ; break;
            case '減価償却費明細表取込' : $script_name = 'depreciation_statement/depreciation_statement_get_form.php' ; break;
            
            // ２期比較表
            case '２期 本決算損益表'    : $script_name = 'profit_loss_pl_act_2ki.php'   ; break;
            case '２期 貸借対照表'      : $script_name = 'profit_loss_bs_act_2ki.php'   ; break;
            case '２期 ＣＬ商品別損益'  : $script_name = 'profit_loss_pl_act_2ki_cl.php'; break;
            case '２期 経費実績内訳'    : $script_name = 'profit_loss_keihi_2ki.php'    ; break;
            case '製造原価計算'         : $script_name = 'manufacture_cost_total.php'   ; break;
            case '決算報告書'           : $script_name = 'financial_report_view.php'    ; break;
            case '勘定科目組替表'       : $script_name = 'account_transfer_view.php'    ; break;
            case '設備製作集計'         : $script_name = 'machine_production_view.php'  ; break;
            case '勘定科目内訳明細書'   : $script_name = 'account_statement_view.php'    ; break;
            
            // 消費税申告書
            case '未払金計上仕入額'    : $script_name = 'sales_tax_miharai_view.php'   ; break;
            case '中間納付確認'        : $script_name = 'sales_tax_chukan_view.php'   ; break;
            case '消費税集計表'        : $script_name = 'sales_tax_zeishukei_view.php'   ; break;
            case '控除税額計算表'      : $script_name = 'sales_tax_koujyo_view.php'   ; break;
            case '消費税等計算表'      : $script_name = 'sales_tax_syozei_allo_view.php'   ; break;
            case '消費税申告資料'      : $script_name = 'sales_tax_syozei_shinkoku_view.php'   ; break;
            case '確定申告書第1表'     : $script_name = 'print/sales_tax_kakutei_shinkoku1_pdf.php'   ; break;
            case '第2表'               : $script_name = 'print/sales_tax_kakutei_shinkoku2_pdf.php'   ; break;
            case '付表1-1'             : $script_name = 'print/sales_tax_kakutei_fuhyo1-1_pdf.php'   ; break;
            case '付表1-2'             : $script_name = 'print/sales_tax_kakutei_fuhyo1-2_pdf.php'   ; break;
            case '付表2-1'             : $script_name = 'print/sales_tax_kakutei_fuhyo2-1_pdf.php'   ; break;
            case '付表2-2'             : $script_name = 'print/sales_tax_kakutei_fuhyo2-2_pdf.php'   ; break;
            
            default: $script_name = 'profit_loss_select.php';       // 呼出もとへ帰る
                     $url_name    = $_SESSION['pl_referer'];        // 呼出もとのURL 別メニューから呼び出された時の対応
        }
    } else {
        switch ($_POST['pl_name']) {
            case '1 AS/400→TNK'            : $script_name = 'profit_loss_ftp_to_db_all.php'; break;
            case '2 CL配賦率計算'           : $script_name = 'profit_loss_cl_allocation.php'; break;
            case '3 経費配賦実行'           : $script_name = 'profit_loss_cl_keihi_save.php'; break;
            case '4 棚卸高入力'             : $script_name = 'profit_loss_inventory_put.php'; break;
            case '5 棚卸高調整'             : $script_name = 'profit_loss_adjust_invent.php'; break;
            case '6 仕入高調整'             : $script_name = 'profit_loss_adjust_shiire.php'; break;
            case '7 売上原価調整'           : $script_name = 'profit_loss_adjust_ugenka.php'; break;
            case '8 業務委託入力'           : $script_name = 'profit_loss_gyoumu_put.php'   ; break;
            case '9 売上高調整'             : $script_name = 'profit_loss_adjust_uriage.php'; break;
            case '10 ＣＬ損益計算'          : $script_name = 'profit_loss_pl_act_save.php'  ; break;
            case '11 商管・試修損益登録'    : $script_name = 'profit_loss_nkb_input.php'    ; break;
            //case '12 セグメント別取込'    : $script_name = 'pl_segment/pl_segment_get_form.php'   ; break;
            case '12 Ｌ人員比率計算'        : $script_name = 'profit_loss_bls_input.php'    ; break;
            case '13 Ｃ人員比率計算'        : $script_name = 'profit_loss_ctoku_input.php'  ; break;
            case '14 全社人員比率計算'      : $script_name = 'profit_loss_staff_input.php'  ; break;
            
            case '経費実績内訳'                     : $script_name = 'profit_loss_keihi.php'            ; break;
            case 'ＣＬ・商管 経費実績表'            : $script_name = 'profit_loss_cl_keihi.php'         ; break;
            case 'セグメント別損益'                 : $script_name = 'profit_loss_pl_act_t-bk.php'      ; break;
            case 'セグメント別損益10月一括'         : $script_name = 'profit_loss_pl_act10.php'         ; break;
            case 'セグメント別損益機工'             : $script_name = 'profit_loss_pl_act_t-bk.php'      ; break;
            case '貸借対照表'                       : $script_name = 'profit_loss_bs_act.php'           ; break;
            case 'ＣＬ予実比損益'                   : $script_name = 'profit_loss_select.php'           ; break;
            case '原価率計算表'                     : $script_name = 'profit_loss_cost_rate.php'        ; break;
            case '面積配布表'                       : $script_name = 'profit_loss_select.php'           ; break;
            case 'ＣＬ経費差額比較表'               : $script_name = 'profit_loss_cl_keihi_compare.php' ; break;
            case 'ＢＬ・試修 商品別損益'            : $script_name = 'profit_loss_pl_act_bls.php'       ; break;
            case '特注・標準 商品別損益'            : $script_name = 'profit_loss_pl_act_ctoku.php'     ; break;
            case '試験・修理 商品別損益'            : $script_name = 'profit_loss_pl_act_ss.php'        ; break;
            case '旧ＣＬ商品別損益'                 : $script_name = 'profit_loss_pl_act_old.php'       ; break;
            case '旧ＣＬ経費実績表'                 : $script_name = 'profit_loss_cl_keihi_old.php'     ; break;
            case 'ＢＬ 商品別損益'                  : $script_name = 'profit_loss_pl_act_bl.php'        ; break;
            case '損益対前月比較表'                 : $script_name = 'profit_loss_pl_act_compare.php'   ; break;
            case '商品別テスト'                     : $script_name = 'profit_loss_pl_act_compare.php'   ; break;
            case 'ＬＴ 商品別損益'                  : $script_name = 'profit_loss_pl_act_lt.php'        ; break;
            case 'ＣＬＴ・試修・商管 商品別損益'    : $script_name = 'profit_loss_pl_act_all.php'       ; break;
            case '売上状況照会'                     : $script_name = 'profit_loss_sales_view.php'       ; break;
            
            case '経費内訳データ' : $script_name = 'profit_loss_ftp_to_db_D.php'    ; break;
            case 'ＣＬ経費データ' : $script_name = 'profit_loss_ftp_to_db_B.php'    ; break;
            case '科目別部門経費' : $script_name = 'profit_loss_ftp_to_db_B1.php'   ; break;
            case '要素買掛データ' : $script_name = 'profit_loss_ftp_to_db_E.php'    ; break;
            case 'ＣＬ損益データ' : $script_name = 'profit_loss_ftp_to_db_AC.php'   ; break;
            case '貸借対照データ' : $script_name = 'profit_loss_ftp_to_db_F.php'    ; break;
            
            case '総平均棚卸入力' : $script_name = 'profit_loss_invent_gross_average.php'   ; break;
            
            case 'CL配賦Clear'    : $script_name = 'profit_loss_clear.php?pl_table=allo_history'; break;
            case '経費配賦Clear'  : $script_name = 'profit_loss_clear.php?pl_table=cl_history'  ; break;
            case 'CL損益Clear'    : $script_name = 'profit_loss_clear.php?pl_table=pl_history'  ; break;
            
            case '比較棚卸表'   : $script_name = 'invent_comp/invent_comp_view.php'     ; break;
            case 'データ取込み' : $script_name = 'invent_comp/invent_comp_get_form.php' ; break;
            
            case '減価償却費明細表'   : $script_name = 'depreciation_statement/depreciation_statement_view.php'     ; break;
            case '減価償却費明細表取込' : $script_name = 'depreciation_statement/depreciation_statement_get_form.php' ; break;
            
            // ２期比較表
            case '２期 本決算損益表'    : $script_name = 'profit_loss_pl_act_2ki.php'   ; break;
            case '２期 貸借対照表'      : $script_name = 'profit_loss_bs_act_2ki.php'   ; break;
            case '２期 ＣＬ商品別損益'  : $script_name = 'profit_loss_pl_act_2ki_cl.php'; break;
            case '２期 経費実績内訳'    : $script_name = 'profit_loss_keihi_2ki.php'    ; break;
            case '製造原価計算'         : $script_name = 'manufacture_cost_total.php'   ; break;
            case '決算報告書'           : $script_name = 'financial_report_view.php'    ; break;
            case '勘定科目組替表'       : $script_name = 'account_transfer_view.php'    ; break;
            case '設備製作集計'         : $script_name = 'machine_production_view.php'  ; break;
            case '勘定科目内訳明細書'   : $script_name = 'account_statement_view.php'    ; break;
            
            // 消費税申告書
            case '未払金計上仕入額'    : $script_name = 'sales_tax_miharai_view.php'   ; break;
            case '中間納付確認'        : $script_name = 'sales_tax_chukan_view.php'   ; break;
            case '消費税集計表'        : $script_name = 'sales_tax_zeishukei_view.php'   ; break;
            case '控除税額計算表'      : $script_name = 'sales_tax_koujyo_view.php'   ; break;
            case '消費税等計算表'      : $script_name = 'sales_tax_syozei_allo_view.php'   ; break;
            case '消費税申告資料'      : $script_name = 'sales_tax_syozei_shinkoku_view.php'   ; break;
            case '確定申告書第1表'     : $script_name = 'print/sales_tax_kakutei_shinkoku1_pdf.php'   ; break;
            case '第2表'               : $script_name = 'print/sales_tax_kakutei_shinkoku2_pdf.php'   ; break;
            case '付表1-1'             : $script_name = 'print/sales_tax_kakutei_fuhyo1-1_pdf.php'   ; break;
            case '付表1-2'             : $script_name = 'print/sales_tax_kakutei_fuhyo1-2_pdf.php'   ; break;
            case '付表2-1'             : $script_name = 'print/sales_tax_kakutei_fuhyo2-1_pdf.php'   ; break;
            case '付表2-2'             : $script_name = 'print/sales_tax_kakutei_fuhyo2-2_pdf.php'   ; break;
            
            default: $script_name = 'profit_loss_select.php';       // 呼出もとへ帰る
                     $url_name    = $_SESSION['pl_referer'];        // 呼出もとのURL 別メニューから呼び出された時の対応
        }
    }
}
// 作成系のプログラムは認証を分ける
$auth = 0;  // チェック用変数初期化 1=照会 2=作成
switch ($_POST['pl_name']) {
    case '1 AS/400→TNK'            : $auth = 2 ; break;
    case '2 CL配賦率計算'           : $auth = 2 ; break;
    case '3 経費配賦実行'           : $auth = 2 ; break;
    case '4 棚卸高入力'             : $auth = 2 ; break;
    case '5 棚卸高調整'             : $auth = 2 ; break;
    case '6 仕入高調整'             : $auth = 2 ; break;
    case '7 売上原価調整'           : $auth = 2 ; break;
    case '8 業務委託入力'           : $auth = 2 ; break;
    case '9 売上高調整'             : $auth = 2 ; break;
    case '10 ＣＬ損益計算'          : $auth = 2 ; break;
    case '11 商管・試修損益登録'    : $auth = 2 ; break;
    //case '12 セグメント別取込'    : $auth = 2 ; break;
    case '12 Ｌ人員比率計算'        : $auth = 2 ; break;
    case '13 Ｃ人員比率計算'        : $auth = 2 ; break;
    case '14 全社人員比率計算'      : $auth = 2 ; break;
    
    case '経費実績内訳'             : $auth = 1 ; break;
    case 'ＣＬ・商管 経費実績表'    : $auth = 1 ; break;
    case 'セグメント別損益'         : $auth = 1 ; break;
    case 'セグメント別損益10月一括' : $auth = 1 ; break;
    case 'セグメント別損益機工'     : $auth = 1 ; break;
    case '貸借対照表'               : $auth = 1 ; break;
    case 'ＣＬ予実比損益'           : $auth = 1 ; break;
    case '原価率計算表'             : $auth = 1 ; break;
    case '面積配布表'               : $auth = 1 ; break;
    case 'ＣＬ経費差額比較表'       : $auth = 1 ; break;
    case 'ＢＬ・試修 商品別損益'    : $auth = 1 ; break;
    case '特注・標準 商品別損益'    : $auth = 1 ; break;
    case '試験・修理 商品別損益'    : $auth = 1 ; break;
    case '旧ＣＬ商品別損益'         : $auth = 1 ; break;
    case '旧ＣＬ経費実績表'         : $auth = 1 ; break;
    case 'ＢＬ 商品別損益'          : $auth = 1 ; break;
    case '損益対前月比較表'         : $auth = 1 ; break;
    case '商品別テスト'             : $auth = 1 ; break;
    case 'ＬＴ 商品別損益'          : $auth = 1 ; break;
    case 'ＣＬＴ・試修・商管 商品別損益'    : $auth = 1 ; break;
    case '売上状況照会'             : $auth = 1 ; break;
    
    case '経費内訳データ'   : $auth = 2 ; break;
    case 'ＣＬ経費データ'   : $auth = 2 ; break;
    case '科目別部門経費'   : $auth = 2 ; break;
    case '要素買掛データ'   : $auth = 2 ; break;
    case 'ＣＬ損益データ'   : $auth = 2 ; break;
    case '貸借対照データ'   : $auth = 2 ; break;
    
    case '総平均棚卸入力'   : $auth = 2 ; break;
    
    case 'CL配賦Clear'      : $auth = 2 ; break;
    case '経費配賦Clear'    : $auth = 2 ; break;
    case 'CL損益Clear'      : $auth = 2 ; break;
    
    case '比較棚卸表'       : $auth = 1 ; break;
    case 'データ取込み'     : $auth = 2 ; break;
    
    case '減価償却費明細表'       : $auth = 1 ; break;
    case '減価償却費明細表取込'     : $auth = 2 ; break;
    
    // ２期比較表
    case '２期 本決算損益表'    : $auth = 1 ; break;
    case '２期 貸借対照表'      : $auth = 1 ; break;
    case '２期 ＣＬ商品別損益'  : $auth = 1 ; break;
    case '２期 経費実績内訳'    : $auth = 1 ; break;
    case '製造原価計算'         : $auth = 1 ; break;
    case '決算報告書'           : $auth = 1 ; break;
    case '勘定科目組替表'       : $auth = 1 ; break;
    case '設備製作集計'         : $auth = 1 ; break;
    case '勘定科目内訳明細書'   : $auth = 1 ; break;
    
    // 消費税申告書
    case '未払金計上仕入額'    : $auth = 1 ; break;
    case '中間納付確認'        : $auth = 1 ; break;
    case '消費税集計表'        : $auth = 1 ; break;
    case '控除税額計算表'      : $auth = 1 ; break;
    case '消費税等計算表'      : $auth = 1 ; break;
    case '消費税申告資料'      : $auth = 1 ; break;
    case '確定申告書第1表'     : $auth = 1 ; break;
    case '第2表'               : $auth = 1 ; break;
    case '付表1-1'             : $auth = 1 ; break;
    case '付表1-2'             : $auth = 1 ; break;
    case '付表2-1'             : $auth = 1 ; break;
    case '付表2-2'             : $auth = 1 ; break;
    
    default: $script_name = 'profit_loss_select.php';       // 呼出もとへ帰る
             $url_name    = $_SESSION['pl_referer'];        // 呼出もとのURL 別メニューから呼び出された時の対応
}

////////// スクリプトによってユーザーの権限チェックを変える
//if ($script_name == 'profit_loss_cost_rate.php') {      // 原価率計算表
//    if (!isset($_SESSION["User_ID"]) || !isset($_SESSION["Password"]) || !isset($_SESSION["Auth"])) {
//        header("Location: " . $_SESSION['pl_referer']);
//        exit();
//    }
//} elseif ($script_name == 'profit_loss_bs_act.php') {   // 貸借対照表
//    if (!isset($_SESSION["User_ID"]) || !isset($_SESSION["Password"]) || !isset($_SESSION["Auth"])) {
//        header("Location: " . $_SESSION['pl_referer']);
//        exit();
//    }
//} elseif ($script_name == 'getsuji_comp_invent.php') {  // 月次比較棚卸表
//    if (!isset($_SESSION["User_ID"]) || !isset($_SESSION["Password"]) || !isset($_SESSION["Auth"])) {
//        header("Location: " . $_SESSION['pl_referer']);
//        exit();
//    }
//} else {
//    if (account_group_check() == FALSE) {               // 上記以外はアカウントグループ
//        $_SESSION["s_sysmsg"] = "あなたは許可されていません。<br>管理者に連絡して下さい。";
//        // header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
//        header("Location: " . $_SESSION['pl_referer']);
//        exit();
//    }
//}
if ($auth == 1) {
    if (account_group_check() == FALSE) {               // 上記以外はアカウントグループ
        $_SESSION["s_sysmsg"] = "あなたは許可されていません。<br>管理者に連絡して下さい。";
        // header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
        header("Location: " . $_SESSION['pl_referer']);
        exit();
    }
} elseif ($auth == 2) {
    if (!getCheckAuthority(31)) {
        $_SESSION["s_sysmsg"] = "あなたは許可されていません。<br>管理者に連絡して下さい。";
        // header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
        header("Location: " . $_SESSION['pl_referer']);
        exit();
    }
}
if ($_POST['pl_name'] == 'ＬＴ 商品別損益') {
    if ($_POST['pl_ym'] <= 201503) {               // 上記以外はアカウントグループ
        $_SESSION["s_sysmsg"] = "LT商品別損益は2015年4月からです。";
        // header("Location: http:" . WEB_HOST . "kessan/profit_loss_select.php");
        header("Location: " . $_SESSION['pl_referer']);
        exit();
    }
}
?>
<!DOCTYPE html>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<meta http-equiv="Content-Style-Type" content="text/css">
<meta http-equiv="Content-Script-Type" content="text/javascript">
<title>月次損益分岐処理</title>
<style type='text/css'>
<!--
body        {margin:20%;font-size:24pt;}
-->
</style>
</head>
<body>
    <center>
        処理中です。お待ち下さい。<br>
        <img src='../img/tnk-turbine.gif' width=68 height=72>
    </center>

    <script language="JavaScript">
    <!--
    <?php
        if (isset($url_name)) {
            echo "location = '$url_name'";
        } else {
            // echo "location = 'http:" . WEB_HOST . "kessan/" . "$script_name'";
            echo "location = '" . H_WEB_HOST . PL . "$script_name'";
        }
    ?>
    // -->
    </script>
</body>
</html>
