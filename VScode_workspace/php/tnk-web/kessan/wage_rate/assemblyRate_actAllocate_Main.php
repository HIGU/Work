<?php
//////////////////////////////////////////////////////////////////////////////
// 間接費配賦率 照会 main部 assemblyRate_actAllocate_Main.php               //
//                          (旧 indirect_cost_allocate.php)                 //
// Copyright (C) 2007-2014 Norihisa.Ooya usoumu@nitto-kohki.co.jp           //
// Changed history                                                          //
// 2007/12/06 Created  assemblyRate_reference_Main.php                      //
// 2007/12/13 余分なfontタグの削除 コメントの位置調整                       //
// 2007/12/29 日付の初期値の設定を追加                                      //
//            前画面に戻る時決算処理の対象年月か前画面で選択した日付を返す  //
//            ように変更                                                    //
// 2008/01/10 表示部の余分なタグの削除                                      //
// 2008/05/09 表示項目・サイズの微調整                                      //
// 2009/04/10 新しくリニア修理部門（559）を追加                             //
// 2010/02/04 製造経費の取り込みとサービス割合の製造経費の配賦を行わないと  //
//            処理が出来ないように変更                                      //
// 2010/03/03 上の条件が少しおかしかったので調整                            //
//            期年月の表示を調整。substrの後に+1-1して数字にして0を消す     //
// 2010/12/09 税務調査指摘により、リニア修理(559)を削除 2010/12～           //
// 2011/06/22 format_date系をtnk_funcに移動のためこちらを削除               //
// 2014/04/11 2014/04より組織変更の為、各部を調整                           //
//////////////////////////////////////////////////////////////////////////////
//ini_set('error_reporting', E_ALL || E_STRICT);
session_start();                                 // ini_set()の次に指定すること Script 最上行

require_once ('../../function.php');             // define.php と pgsql.php を require_once している
require_once ('../../tnk_func.php');             // TNK に依存する部分の関数を require_once している
require_once ('../../MenuHeader.php');           // TNK 全共通 menu class
require_once ('../../ControllerHTTP_Class.php'); // TNK 全共通 MVC Controller Class
access_log();                                    // Script Name は自動取得

main();

function main()
{
    ////////// TNK 共用メニュークラスのインスタンスを作成
    $menu = new MenuHeader(0);                       // 認証チェック0=一般以上 戻り先=TOP_MENU タイトル未設定
       
    ////////// タイトル名(ソースのタイトル名とフォームのタイトル名)
    $menu->set_title('間接費配賦率の照会');
    
    $request = new Request;
    $result  = new Result;
    
    if ($request->get('end_ym') !== '') {
        ////// リターンアドレス設定
        $menu->set_RetUrl($_SESSION['wage_referer'] . '?wage_ym=' . $request->get('end_ym'));
    } else {
        ////// リターンアドレス設定
        $menu->set_RetUrl($_SESSION['wage_referer'] . '?wage_ym=' . $request->get('wage_ym'));
    }
    
    request_check($request, $result, $menu);         // 処理の分岐チェック
    
    calculation_branch($request, $result, $menu);    // 配賦率計算の分岐
    
    display($menu, $request, $result);               // 画面表示
}

////////////// 画面表示
function display($menu, $request, $result)
{       
    ////////// ブラウザーのキャッシュ対策用
    $uniq = 'id=' . $menu->set_useNotCache('target');
    
    ////////// メッセージ出力フラグ
    $msg_flg = 'site';

    ob_start('ob_gzhandler');                        // 出力バッファをgzip圧縮
    
    ////////// HTML Header を出力してキャッシュを制御
    $menu->out_html_header();
 
    ////////// Viewの処理
    require_once ('assemblyRate_actAllocate_View.php');

    ob_end_flush(); 
}

////////////// 処理の分岐を行う
function request_check($request, $result, $menu)
{
    $ok = true;
    if ($request->get('delete') != '') $ok = actAllocate_delete($request);
    if ($request->get('input') != '')  $ok = actAllocate_input($request, $result);
    if ($ok) {
        ////// データの初期化
        $str_ym = '';                   // 開始日
        $end_ym = '';                   // 終了日
        $tan_str_ym = '';               // 自由処理の時の開始日
        $tan_end_ym ='';                // 自由処理の時の終了日
        $request->add('delete', '');    // 初期化
        $request->add('input', '');     // 初期化
        if ($request->get('wage_ym') !== '') {
            $request->add('end_ym', $request->get('wage_ym'));    // 初期値の終了年月の設定
            $nen   = substr($request->get('wage_ym'), 0, 4);
            $tsuki = substr($request->get('wage_ym'), 4, 2);
            if (($tsuki < 10) && (3 < $tsuki)) {                    // 初期値の開始年月の設定
                $str_tsuki = '04';
                $str_ym = $nen . $str_tsuki;
                $request->add('str_ym', $str_ym);
            } else if ( 9 < $tsuki) {
                $str_tsuki = '10';
                $str_ym = $nen . $str_tsuki;
                $request->add('str_ym', $str_ym);
            } else {
                $str_nen = $nen - 1;
                $str_tsuki = '10';
                $str_ym = $str_nen . $str_tsuki;
                $request->add('str_ym', $str_ym);
            }
            $request->add('wage_ym', '');                         // 初期値の日付データの初期化
        }
    }
}

////////////// 配賦率計算の分岐
function calculation_branch($request, $result, $menu)
{
    $request->add('view_flg', '');                                     // 照会画面表示のフラグ初期化
    if ($request->get('tangetu') != '') {
        $request->add('rate_register', '登録');                        // 単月の場合は毎回計算を行う為
        $request->add('kessan', '');
    }
    if ($request->get('kessan') != '') {
        $request->add('tangetu', '');
    }
    if ($request->get('kessan') != '' || $request->get('tangetu') != '') {
        if (!registered_data_check($request, $result)) {
            return;
        } else {
            if(!get_registered_data($request, $result)) {              // 登録済みデータの取得
                assembly_actAllocate_cal($request, $result, $menu);    // 賃率計算関数の呼出
            }
            outViewListHTML($request, $menu, $result);                 // 賃率照会画面のHTMLを出力
        }
    }
}

////////////// 必要なデータが登録されているかチェック
function registered_data_check($request, $result)    //配賦率計算の分岐
{
    if ($request->get('kessan') != '') {
        $chk_ym = $request->get('str_ym');
        $end_ym = $request->get('end_ym');
    } elseif ($request->get('tangetu') != '') {
        $chk_ym = $request->get('tan_str_ym');
        $end_ym = $request->get('tan_end_ym');
    }
    for ($chk_ym; $end_ym >= $chk_ym; $chk_ym++) {
        $chk_nen   = substr($chk_ym, 0, 4);             // チェック用年
        $chk_tsuki = substr($chk_ym, 4, 2);             // チェック用月
        if ($chk_tsuki == 13) {                         // 月が13になった時年が繰り上がって月を０１に
            $chk_nen   = $chk_nen + 1;
            $chk_tsuki = '01';
            $chk_ym = $chk_nen . $chk_tsuki;
        }
        $chk_ym4 = substr($end_ym, 2, 4);
        $query = sprintf("SELECT * FROM act_summary WHERE act_yymm=%d", $chk_ym4);
        if ( ($rows=getResult($query, $res)) > 0) {    // 登録済みのチェック
        } else {
            $_SESSION['s_sysmsg'] .= "この処理は先に製造経費の取り込みを行ってから実行してください！";    // .= に注意
            $msg_flg = 'alert';
            return false;
        }
        $query = sprintf("SELECT external_price FROM indirect_cost_allocate WHERE total_date=%d AND external_price >= 0", $chk_ym);
        if ( ($rows=getResult($query, $res)) >= 2) {    // 登録済みのチェック
        } else {
            $_SESSION['s_sysmsg'] .= "この処理は先に各種データ入力より配賦率計算データの入力を行ってください！";    // .= に注意
            $msg_flg = 'alert';
            return false;
        }
        $query = sprintf("SELECT * FROM service_percent_factory_expenses WHERE total_date=%d", $chk_ym);
        if ( ($rows=getResult($query, $res)) > 0) {    // 登録済みのチェック
        } else {
            $_SESSION['s_sysmsg'] .= "この処理は先にサービス割合を入力し、製造経費の配賦を行ってください！";    // .= に注意
            $msg_flg = 'alert';
            return false;
        }
    }
    return true;
}

////////////// 確定解除時のデータの削除ロジック
function actAllocate_delete ($request)
{
    $end_ym = $request->get('end_ym');
    $format_ym = '';
    $format_ym = format_date6_kan($end_ym);
    $query = sprintf("UPDATE indirect_cost_allocate SET indirect_cost=NULL WHERE total_date=%d", $end_ym);
    if (query_affected($query) <= 0) {
        $_SESSION['s_sysmsg'] .= "{$format_ym}の確定解除に失敗！";        // .= に注意
        $msg_flg = 'alert';
        return false;
    } else {
        $_SESSION['s_sysmsg'] .= "{$format_ym}の確定を解除しました！";    // .= に注意
        return true;
    }
}

////////////// 確定時のデータの登録ロジック
function actAllocate_input ($request, $result)
{
    if (getCheckAuthority(22)) {                    // 認証チェック
        $end_ym = $request->get('end_ym');
        $format_ym = '';
        $format_ym = format_date6_kan($end_ym);      // 表示用年月度フォーマット
        $acte_ym = substr($end_ym, 2, 4);           // 年月データのフォーマット
        $c_indirect_cost        = number_format($request->get('c_indirect_cost'), 1);       // カプラ工場間接費配賦率
        $c_suppli_section_cost  = number_format($request->get('c_suppli_section_cost'), 1); // カプラ調達部門費配賦率
        $l_indirect_cost        = number_format($request->get('l_indirect_cost'), 1);       // リニア工場間接費配賦率
        $l_suppli_section_cost  = number_format($request->get('l_suppli_section_cost'), 1); // リニア調達部門費配賦率 
        if($end_ym < 201012) { 
            $act_id       = array(518, 519, 520, 526, 527, 528, 556, 176, 510, 522, 523, 525, 551, 175, 560, 571, 572, 559);    //部門コード
        } elseif($end_ym < 201403) {
            $act_id       = array(518, 519, 520, 526, 527, 528, 556, 176, 510, 522, 523, 525, 551, 175, 560, 571, 572);    //部門コード
        } else {
            $act_id       = array(518, 519, 520, 527, 528, 547, 556, 176, 522, 523, 525, 551, 175, 560, 572);    //部門コード
        }
        $rows_act_id  = count($act_id); //部門数
        $total_item   = array('Ｃ製造', 'Ｃ製特注', 'Ｃ組立', 'Ｃ組特注', 'Ｌ組立', 'バイモル', 'Ｃ外注', 'Ｃ外特注', 'Ｌ外注', '外注バイ');    //配賦部門グループ
        $rows_item    = count($total_item); //配賦部門数
        $query = sprintf("SELECT * FROM indirect_cost_allocate WHERE item='カプラ' AND total_date=%d", $end_ym);
        $res_chk = array();
        if ( getResult($query, $res_chk) > 0 ) {    // 登録あり UPDATE更新
            $query = sprintf("UPDATE indirect_cost_allocate SET indirect_cost='%s', suppli_section_cost='%s', last_date=CURRENT_TIMESTAMP, last_user='%s' WHERE item='カプラ' AND total_date=%d", $c_indirect_cost, $c_suppli_section_cost, $_SESSION['User_ID'], $end_ym);
            if (query_affected($query) <= 0) {
            }
        }
        $query = sprintf("SELECT * FROM indirect_cost_allocate WHERE item='リニア' AND total_date=%d", $end_ym);
        $res_chk = array();
        if ( getResult($query, $res_chk) > 0 ) {    // 登録あり UPDATE更新
            $query = sprintf("UPDATE indirect_cost_allocate SET indirect_cost='%s', suppli_section_cost='%s', last_date=CURRENT_TIMESTAMP, last_user='%s' WHERE item='リニア' AND total_date=%d", $l_indirect_cost, $l_suppli_section_cost, $_SESSION['User_ID'], $end_ym);
            if (query_affected($query) <= 0) {
            }
        }
        //製造課部門経費（単月登録用）
        for ($i=0; $i<$rows_act_id; $i++) {
            $query_exp = sprintf("SELECT sum(act_monthly) FROM act_summary WHERE act_yymm=%d AND act_id=%d", $acte_ym, $act_id[$i]);
            $res_exp = array();
            $rows_exp = getResult($query_exp, $res_exp);
            $tan_expense[$i] = $res_exp[0][0];
        }
        //間接配賦経費（単月登録用）
        for ($i=0; $i<$rows_item; $i++) {
            $query_ser = sprintf("SELECT sum(total_cost) FROM service_percent_factory_expenses WHERE total_date=%d AND total_item='%s'", $end_ym, $total_item[$i]);
            $res_ser = array();
            $rows_ser = getResult($query_ser, $res_ser);
            $tan_indirect[$i] = $res_ser[0][0];
        }
        for ($i=0; $i<$rows_act_id; $i++) {
            $query = sprintf("SELECT * FROM assyrate_section_expense WHERE act_id=%d AND total_date=%d", $act_id[$i], $end_ym);
            $res_chk = array();
            if ( getResult($query, $res_chk) > 0 ) {   //////// 登録あり UPDATE更新
                $query = sprintf("UPDATE assyrate_section_expense SET section_expense=%d, last_date=CURRENT_TIMESTAMP, last_user='%s' WHERE act_id=%d AND total_date=%d", $tan_expense[$i], $_SESSION['User_ID'], $act_id[$i], $end_ym);
                if (query_affected($query) <= 0) {
                }
            } else {    //登録なし INSERT 新規
                $query = sprintf("INSERT INTO assyrate_section_expense (total_date, act_id, section_expense, last_date, last_user)
                         VALUES (%d, %d, %d, CURRENT_TIMESTAMP, '%s')",
                         $end_ym, $act_id[$i], $tan_expense[$i], $_SESSION['User_ID']);
                if (query_affected($query) <= 0) {
                }
            }
        } 
        for ($i=0; $i<$rows_item; $i++) {
            $query = sprintf("SELECT * FROM assyrate_indirect_expense WHERE item='%s' AND total_date=%d", $total_item[$i], $end_ym);
            $res_chk = array();
            if ( getResult($query, $res_chk) > 0 ) {   //////// 登録あり UPDATE更新
                $query = sprintf("UPDATE assyrate_indirect_expense SET indirect_expense=%d, last_date=CURRENT_TIMESTAMP, last_user='%s' WHERE item='%s' AND total_date=%d", $tan_indirect[$i], $_SESSION['User_ID'], $total_item[$i], $end_ym);
                if (query_affected($query) <= 0) {
                }
            } else {    //登録なし INSERT 新規
                $query = sprintf("INSERT INTO assyrate_indirect_expense (total_date, item, indirect_expense, last_date, last_user)
                         VALUES (%d, '%s', %d, CURRENT_TIMESTAMP, '%s')",
                         $end_ym, $total_item[$i], $tan_indirect[$i], $_SESSION['User_ID']);
                if (query_affected($query) <= 0) {
                }
            }
        }
        $_SESSION['s_sysmsg'] .= "{$format_ym}の配賦率を登録しました！</font>";
    } else {    //認証なしエラー
        $_SESSION['s_sysmsg'] .= "編集権限が無い為、DBの更新がされませんでした。";
        return false;
    }
}

////////////// 登録済みデータの取得
function get_registered_data($request, $result)
{
    if ($request->get('kessan') != '') {
        $str_ym = $request->get('str_ym');
        $end_ym = $request->get('end_ym');
    } elseif ($request->get('tangetu') != '') {
        $str_ym = $request->get('tan_str_ym');
        $end_ym = $request->get('tan_end_ym');
    }
    $query = sprintf("SELECT * FROM indirect_cost_allocate WHERE total_date=%d", $end_ym);
    $res = array();
    $rows = getResult($query, $res);
    if ($res[0]['indirect_cost'] == '') {                                    // 賃率が登録済みかチェック
        ////// 新規 未登録の場合は賃率計算のプログラムへ
        $request->add('rate_register', '登録');
        return false;
    } else if ($request->get('rate_register') == '登録') {                   // 単月かどうかチェック
        ////// 単月の場合は賃率計算のプログラムへ
        $request->add('rate_register', '登録');
        return false;
    } else {
        ////// 経歴ありの場合は登録済みのデータを取得し一時データを計算する
        $request->add('rate_register', '照会');
        get_various_data($request, $result);         // 各種データ取得
        act_expenses_cal($request, $result);     // 各部門経費計算
        act_indirect_cal($request, $result);     // サービス割合データ計算
        //get_act_expenses ($request, $result);        // 各部門経費取得
        //get_act_indirect($request, $result);         // サービス割合データ取得
        get_indirect_cost($result, $request);        // 工場間接費配賦率取得
    }
    return true;
}

////////////// 各部門経費取得
function get_act_expenses($request, $result)
{
    if ($request->get('kessan') != '') {
        $str_ym = $request->get('str_ym');
        $end_ym = $request->get('end_ym');
    } elseif ($request->get('tangetu') != '') {
        $str_ym = $request->get('tan_str_ym');
        $end_ym = $request->get('tan_end_ym');
    }
    $manu_expenses = 0;
    $c_assembly_expense = 0;
    $l_assembly_expense = 0;
    $c_expense = 0;
    $total_direct_section = 0;
    ////////// 製造課部門経費
    $query_exp = sprintf("SELECT sum(section_expense) FROM assyrate_section_expense WHERE total_date>=%d AND total_date<=%d AND act_id=518", $str_ym, $end_ym);
    $res_exp = array();
    $rows_exp = getResult($query_exp, $res_exp);
    $result->add('expenses_518', $res_exp[0][0]);
    $query_exp = sprintf("SELECT sum(section_expense) FROM assyrate_section_expense WHERE total_date>=%d AND total_date<=%d AND act_id=519", $str_ym, $end_ym);
    $res_exp = array();
    $rows_exp = getResult($query_exp, $res_exp);
    $result->add('expenses_519', $res_exp[0][0]);
    $query_exp = sprintf("SELECT sum(section_expense) FROM assyrate_section_expense WHERE total_date>=%d AND total_date<=%d AND act_id=520", $str_ym, $end_ym);
    $res_exp = array();
    $rows_exp = getResult($query_exp, $res_exp);
    $result->add('expenses_520', $res_exp[0][0]);
    if ($end_ym < 201404) {
        $query_exp = sprintf("SELECT sum(section_expense) FROM assyrate_section_expense WHERE total_date>=%d AND total_date<=%d AND act_id=526", $str_ym, $end_ym);
        $res_exp = array();
        $rows_exp = getResult($query_exp, $res_exp);
        $result->add('expenses_526', $res_exp[0][0]);
    } else {
        $query_exp = sprintf("SELECT sum(section_expense) FROM assyrate_section_expense WHERE total_date>=%d AND total_date<=%d AND act_id=547", $str_ym, $end_ym);
        $res_exp = array();
        $rows_exp = getResult($query_exp, $res_exp);
        $result->add('expenses_547', $res_exp[0][0]);
    }
    $query_exp = sprintf("SELECT sum(section_expense) FROM assyrate_section_expense WHERE total_date>=%d AND total_date<=%d AND act_id=527", $str_ym, $end_ym);
    $res_exp = array();
    $rows_exp = getResult($query_exp, $res_exp);
    $result->add('expenses_527', $res_exp[0][0]);
    $query_exp = sprintf("SELECT sum(section_expense) FROM assyrate_section_expense WHERE total_date>=%d AND total_date<=%d AND act_id=528", $str_ym, $end_ym);
    $res_exp = array();
    $rows_exp = getResult($query_exp, $res_exp);
    $result->add('expenses_528', $res_exp[0][0]);
    $query_exp = sprintf("SELECT sum(section_expense) FROM assyrate_section_expense WHERE total_date>=%d AND total_date<=%d AND act_id=556", $str_ym, $end_ym);
    $res_exp = array();
    $rows_exp = getResult($query_exp, $res_exp);
    $result->add('expenses_556', $res_exp[0][0]);
    
    if ($end_ym < 201404) {
        $manu_expenses = $result->get('expenses_518') + $result->get('expenses_519') + $result->get('expenses_520') + $result->get('expenses_526') + $result->get('expenses_527') + $result->get('expenses_528') + $result->get('expenses_556'); //製造課部門経費
    } else {
        $manu_expenses = $result->get('expenses_518') + $result->get('expenses_519') + $result->get('expenses_520') + $result->get('expenses_547') + $result->get('expenses_527') + $result->get('expenses_528') + $result->get('expenses_556'); //製造課部門経費
    }
    $result->add('manu_expenses', $manu_expenses);
    ////////// C組立部門経費
    $query_exp = sprintf("SELECT sum(section_expense) FROM assyrate_section_expense WHERE total_date>=%d AND total_date<=%d AND act_id=176", $str_ym, $end_ym);
    $res_exp = array();
    $rows_exp = getResult($query_exp, $res_exp);
    $result->add('expenses_176', $res_exp[0][0]);
    if ($end_ym < 201404) {
        $query_exp = sprintf("SELECT sum(section_expense) FROM assyrate_section_expense WHERE total_date>=%d AND total_date<=%d AND act_id=510", $str_ym, $end_ym);
        $res_exp = array();
        $rows_exp = getResult($query_exp, $res_exp);
        $result->add('expenses_510', $res_exp[0][0]);
    }
    $query_exp = sprintf("SELECT sum(section_expense) FROM assyrate_section_expense WHERE total_date>=%d AND total_date<=%d AND act_id=522", $str_ym, $end_ym);
    $res_exp = array();
    $rows_exp = getResult($query_exp, $res_exp);
    $result->add('expenses_522', $res_exp[0][0]);
    $query_exp = sprintf("SELECT sum(section_expense) FROM assyrate_section_expense WHERE total_date>=%d AND total_date<=%d AND act_id=523", $str_ym, $end_ym);
    $res_exp = array();
    $rows_exp = getResult($query_exp, $res_exp);
    $result->add('expenses_523', $res_exp[0][0]);
    $query_exp = sprintf("SELECT sum(section_expense) FROM assyrate_section_expense WHERE total_date>=%d AND total_date<=%d AND act_id=525", $str_ym, $end_ym);
    $res_exp = array();
    $rows_exp = getResult($query_exp, $res_exp);
    $result->add('expenses_525', $res_exp[0][0]);
    if ($end_ym < 201404) {
        $query_exp = sprintf("SELECT sum(section_expense) FROM assyrate_section_expense WHERE total_date>=%d AND total_date<=%d AND act_id=571", $str_ym, $end_ym);
        $res_exp = array();
        $rows_exp = getResult($query_exp, $res_exp);
        $result->add('expenses_571', $res_exp[0][0]);
    }
    if ($end_ym < 201404) {
        $c_assembly_expense = $result->get('expenses_176') + $result->get('expenses_510') + $result->get('expenses_522') + $result->get('expenses_523') + $result->get('expenses_525') + $result->get('expenses_571'); //C組立部門経費
    } else {
        $c_assembly_expense = $result->get('expenses_176') + $result->get('expenses_522') + $result->get('expenses_523') + $result->get('expenses_525'); //C組立部門経費
    }
    $result->add('c_assembly_expense', $c_assembly_expense);
    $c_expense = $result->get('manu_expenses') + $result->get('c_assembly_expense');       // カプラ部門経費合計
    $result->add('c_expense', $c_expense);
    ////////// L組立部門経費
    $query_exp = sprintf("SELECT sum(section_expense) FROM assyrate_section_expense WHERE total_date>=%d AND total_date<=%d AND act_id=551", $str_ym, $end_ym);
    $res_exp = array();
    $rows_exp = getResult($query_exp, $res_exp);
    $result->add('expenses_551', $res_exp[0][0]);
    $query_exp = sprintf("SELECT sum(section_expense) FROM assyrate_section_expense WHERE total_date>=%d AND total_date<=%d AND act_id=175", $str_ym, $end_ym);
    $res_exp = array();
    $rows_exp = getResult($query_exp, $res_exp);
    $result->add('expenses_175', $res_exp[0][0]);
    $query_exp = sprintf("SELECT sum(section_expense) FROM assyrate_section_expense WHERE total_date>=%d AND total_date<=%d AND act_id=560", $str_ym, $end_ym);
    $res_exp = array();
    $rows_exp = getResult($query_exp, $res_exp);
    $result->add('expenses_560', $res_exp[0][0]);
    $query_exp = sprintf("SELECT sum(section_expense) FROM assyrate_section_expense WHERE total_date>=%d AND total_date<=%d AND act_id=572", $str_ym, $end_ym);
    $res_exp = array();
    $rows_exp = getResult($query_exp, $res_exp);
    $result->add('expenses_572', $res_exp[0][0]);
    if($end_ym < 201012) { 
        $query_exp = sprintf("SELECT sum(section_expense) FROM assyrate_section_expense WHERE total_date>=%d AND total_date<=%d AND act_id=559", $str_ym, $end_ym);
        $res_exp = array();
        $rows_exp = getResult($query_exp, $res_exp);
        $result->add('expenses_559', $res_exp[0][0]);
    }
    if($end_ym < 201012) { 
        $l_assembly_expense = $result->get('expenses_551') + $result->get('expenses_175') + $result->get('expenses_560') + $result->get('expenses_572') + $result->get('expenses_559'); //L組立部門経費
    } else {
        $l_assembly_expense = $result->get('expenses_551') + $result->get('expenses_175') + $result->get('expenses_560') + $result->get('expenses_572'); //L組立部門経費
    }
    $result->add('l_assembly_expense', $l_assembly_expense);
    $total_direct_section = $result->get('c_expense') + $result->get('l_assembly_expense'); // 直接部門経費合計
    $result->add('total_direct_section', $total_direct_section);
}        

////////////// 各部門経費計算
function act_expenses_cal($request, $result)
{
    if ($request->get('kessan') != '') {
        $str_ym = $request->get('str_ym');
        $end_ym = $request->get('end_ym');
    } elseif ($request->get('tangetu') != '') {
        $str_ym = $request->get('tan_str_ym');
        $end_ym = $request->get('tan_end_ym');
    }
    $acts_ym = substr($str_ym, 2, 4);    // 各部門経費・サービス割合取得の為の開始年月
    $acte_ym = substr($end_ym, 2, 4);    // 各部門経費・サービス割合（決算なので入力された開始終了年月より）
    $manu_expenses = 0;
    $c_assembly_expense = 0;
    $l_assembly_expense = 0;
    $c_expense = 0;
    $total_direct_section = 0;
    ////////// 製造課部門経費（累計計算）
    $query_exp = sprintf("SELECT sum(act_monthly) FROM act_summary WHERE act_yymm>=%d AND act_yymm<=%d AND act_id=518", $acts_ym, $acte_ym);
    $res_exp = array();
    $rows_exp = getResult($query_exp, $res_exp);
    $result->add('expenses_518', $res_exp[0][0]);
    $query_exp = sprintf("SELECT sum(act_monthly) FROM act_summary WHERE act_yymm>=%d AND act_yymm<=%d AND act_id=519", $acts_ym, $acte_ym);
    $res_exp = array();
    $rows_exp = getResult($query_exp, $res_exp);
    $result->add('expenses_519', $res_exp[0][0]);
    $query_exp = sprintf("SELECT sum(act_monthly) FROM act_summary WHERE act_yymm>=%d AND act_yymm<=%d AND act_id=520", $acts_ym, $acte_ym);
    $res_exp = array();
    $rows_exp = getResult($query_exp, $res_exp);
    $result->add('expenses_520', $res_exp[0][0]);
    if($end_ym < 201404) { 
        $query_exp = sprintf("SELECT sum(act_monthly) FROM act_summary WHERE act_yymm>=%d AND act_yymm<=%d AND act_id=526", $acts_ym, $acte_ym);
        $res_exp = array();
        $rows_exp = getResult($query_exp, $res_exp);
        $result->add('expenses_526', $res_exp[0][0]);
    } else {
        $query_exp = sprintf("SELECT sum(act_monthly) FROM act_summary WHERE act_yymm>=%d AND act_yymm<=%d AND act_id=547", $acts_ym, $acte_ym);
        $res_exp = array();
        $rows_exp = getResult($query_exp, $res_exp);
        $result->add('expenses_547', $res_exp[0][0]);
    }
    $query_exp = sprintf("SELECT sum(act_monthly) FROM act_summary WHERE act_yymm>=%d AND act_yymm<=%d AND act_id=527", $acts_ym, $acte_ym);
    $res_exp = array();
    $rows_exp = getResult($query_exp, $res_exp);
    $result->add('expenses_527', $res_exp[0][0]);
    $query_exp = sprintf("SELECT sum(act_monthly) FROM act_summary WHERE act_yymm>=%d AND act_yymm<=%d AND act_id=528", $acts_ym, $acte_ym);
    $res_exp = array();
    $rows_exp = getResult($query_exp, $res_exp);
    $result->add('expenses_528', $res_exp[0][0]);
    $query_exp = sprintf("SELECT sum(act_monthly) FROM act_summary WHERE act_yymm>=%d AND act_yymm<=%d AND act_id=556", $acts_ym, $acte_ym);
    $res_exp = array();
    $rows_exp = getResult($query_exp, $res_exp);
    $result->add('expenses_556', $res_exp[0][0]);
    if($end_ym < 201404) { 
        $manu_expenses = $result->get('expenses_518') + $result->get('expenses_519') + $result->get('expenses_520') + $result->get('expenses_526') + $result->get('expenses_527') + $result->get('expenses_528') + $result->get('expenses_556'); //製造課部門経費
    } else {
        $manu_expenses = $result->get('expenses_518') + $result->get('expenses_519') + $result->get('expenses_520') + $result->get('expenses_547') + $result->get('expenses_527') + $result->get('expenses_528') + $result->get('expenses_556'); //製造課部門経費
    }
    $result->add('manu_expenses', $manu_expenses);
    ////////// C組立部門経費（累計計算）
    $query_exp = sprintf("SELECT sum(act_monthly) FROM act_summary WHERE act_yymm>=%d AND act_yymm<=%d AND act_id=176", $acts_ym, $acte_ym);
    $res_exp = array();
    $rows_exp = getResult($query_exp, $res_exp);
    $result->add('expenses_176', $res_exp[0][0]);
    if($end_ym < 201404) { 
        $query_exp = sprintf("SELECT sum(act_monthly) FROM act_summary WHERE act_yymm>=%d AND act_yymm<=%d AND act_id=510", $acts_ym, $acte_ym);
        $res_exp = array();
        $rows_exp = getResult($query_exp, $res_exp);
        $result->add('expenses_510', $res_exp[0][0]);
    }
    $query_exp = sprintf("SELECT sum(act_monthly) FROM act_summary WHERE act_yymm>=%d AND act_yymm<=%d AND act_id=522", $acts_ym, $acte_ym);
    $res_exp = array();
    $rows_exp = getResult($query_exp, $res_exp);
    $result->add('expenses_522', $res_exp[0][0]);
    $query_exp = sprintf("SELECT sum(act_monthly) FROM act_summary WHERE act_yymm>=%d AND act_yymm<=%d AND act_id=523", $acts_ym, $acte_ym);
    $res_exp = array();
    $rows_exp = getResult($query_exp, $res_exp);
    $result->add('expenses_523', $res_exp[0][0]);
    $query_exp = sprintf("SELECT sum(act_monthly) FROM act_summary WHERE act_yymm>=%d AND act_yymm<=%d AND act_id=525", $acts_ym, $acte_ym);
    $res_exp = array();
    $rows_exp = getResult($query_exp, $res_exp);
    $result->add('expenses_525', $res_exp[0][0]);
    if($end_ym < 201404) { 
        $query_exp = sprintf("SELECT sum(act_monthly) FROM act_summary WHERE act_yymm>=%d AND act_yymm<=%d AND act_id=571", $acts_ym, $acte_ym);
        $res_exp = array();
        $rows_exp = getResult($query_exp, $res_exp);
        $result->add('expenses_571', $res_exp[0][0]);
    }
    if($end_ym < 201404) { 
        $c_assembly_expense = $result->get('expenses_176') + $result->get('expenses_510') + $result->get('expenses_522') + $result->get('expenses_523') + $result->get('expenses_525') + $result->get('expenses_571'); //C組立部門経費
    } else {
        $c_assembly_expense = $result->get('expenses_176') + $result->get('expenses_522') + $result->get('expenses_523') + $result->get('expenses_525'); //C組立部門経費
    }
    $result->add('c_assembly_expense', $c_assembly_expense);
    $c_expense = $result->get('manu_expenses') + $result->get('c_assembly_expense');       // カプラ部門経費合計
    $result->add('c_expense', $c_expense);
    ////////// L組立部門経費（累計計算）
    $query_exp = sprintf("SELECT sum(act_monthly) FROM act_summary WHERE act_yymm>=%d AND act_yymm<=%d AND act_id=551", $acts_ym, $acte_ym);
    $res_exp = array();
    $rows_exp = getResult($query_exp, $res_exp);
    $result->add('expenses_551', $res_exp[0][0]);
    $query_exp = sprintf("SELECT sum(act_monthly) FROM act_summary WHERE act_yymm>=%d AND act_yymm<=%d AND act_id=175", $acts_ym, $acte_ym);
    $res_exp = array();
    $rows_exp = getResult($query_exp, $res_exp);
    $result->add('expenses_175', $res_exp[0][0]);
    $query_exp = sprintf("SELECT sum(act_monthly) FROM act_summary WHERE act_yymm>=%d AND act_yymm<=%d AND act_id=560", $acts_ym, $acte_ym);
    $res_exp = array();
    $rows_exp = getResult($query_exp, $res_exp);
    $result->add('expenses_560', $res_exp[0][0]);
    $query_exp = sprintf("SELECT sum(act_monthly) FROM act_summary WHERE act_yymm>=%d AND act_yymm<=%d AND act_id=572", $acts_ym, $acte_ym);
    $res_exp = array();
    $rows_exp = getResult($query_exp, $res_exp);
    $result->add('expenses_572', $res_exp[0][0]);
    if($end_ym < 201012) { 
        $query_exp = sprintf("SELECT sum(act_monthly) FROM act_summary WHERE act_yymm>=%d AND act_yymm<=%d AND act_id=559", $acts_ym, $acte_ym);
        $res_exp = array();
        $rows_exp = getResult($query_exp, $res_exp);
        $result->add('expenses_559', $res_exp[0][0]);
    }
    if($end_ym < 201012) { 
        $l_assembly_expense = $result->get('expenses_551') + $result->get('expenses_175') + $result->get('expenses_560') + $result->get('expenses_572') + $result->get('expenses_559'); //L組立部門経費
    } else {
        $l_assembly_expense = $result->get('expenses_551') + $result->get('expenses_175') + $result->get('expenses_560') + $result->get('expenses_572'); //L組立部門経費
    }
    $result->add('l_assembly_expense', $l_assembly_expense);
    $total_direct_section = $result->get('c_expense') + $result->get('l_assembly_expense'); // 直接部門経費合計
    $result->add('total_direct_section', $total_direct_section);             
}

////////////// サービス割合データ取得
function get_act_indirect($request, $result)
{
    if ($request->get('kessan') != '') {
        $str_ym = $request->get('str_ym');
        $end_ym = $request->get('end_ym');
    } elseif ($request->get('tangetu') != '') {
        $str_ym = $request->get('tan_str_ym');
        $end_ym = $request->get('tan_end_ym');
    }
    $manu_service           = 0;      // 工場間接費製造課（サービス割合の値）
    $c_assembly_service     = 0;      // 工場間接費C組立課（サービス割合の値）
    $factory_indirect       = 0;      // 工場間接費合計
    $fact_indirect_l        = 0;      // リニア工場間接費間接配賦経費（サービス割合の値）
    $suppli_indirect_c      = 0;      // カプラ調達部門費間接配賦経費（サービス割合の値）
    $suppli_indirect_l      = 0;      // リニア調達部門費間接配賦経費（サービス割合の値）
    $suppli_indirect_t      = 0;      // 調達部門費間接配賦経費合計
    ////////// 工場間接費製造課
    ////////// C製造
    $query_ser = sprintf("SELECT sum(indirect_expense) FROM assyrate_indirect_expense WHERE total_date>=%d AND total_date<=%d AND item='Ｃ製造'", $str_ym, $end_ym);
    $res_ser = array();
    $rows_ser = getResult($query_ser, $res_ser);
    $manu_service += $res_ser[0][0];
    ////////// C製特注
    $query_ser = sprintf("SELECT sum(indirect_expense) FROM assyrate_indirect_expense WHERE total_date>=%d AND total_date<=%d AND item='Ｃ製特注'", $str_ym, $end_ym);
    $res_ser = array();
    $rows_ser = getResult($query_ser, $res_ser);
    $manu_service += $res_ser[0][0];
    $result->add('manu_service', $manu_service);
    ////////// 工場間接費C組立課
    ////////// C組立
    $query_ser = sprintf("SELECT sum(indirect_expense) FROM assyrate_indirect_expense WHERE total_date>=%d AND total_date<=%d AND item='Ｃ組立'", $str_ym, $end_ym);
    $res_ser = array();
    $rows_ser = getResult($query_ser, $res_ser);
    $c_assembly_service += $res_ser[0][0];
    ////////// C組特注
    $query_ser = sprintf("SELECT sum(indirect_expense) FROM assyrate_indirect_expense WHERE total_date>=%d AND total_date<=%d AND item='Ｃ組特注'", $str_ym, $end_ym);
    $res_ser = array();
    $rows_ser = getResult($query_ser, $res_ser);
    $c_assembly_service += $res_ser[0][0];
    $result->add('c_assembly_service', $c_assembly_service);
    ////////// リニア工場間接費間接配賦経費
    ////////// L組立
    $query_ser = sprintf("SELECT sum(indirect_expense) FROM assyrate_indirect_expense WHERE total_date>=%d AND total_date<=%d AND item='Ｌ組立'", $str_ym, $end_ym);
    $res_ser = array();
    $rows_ser = getResult($query_ser, $res_ser);
    $fact_indirect_l += $res_ser[0][0];
    ////////// バイモル
    $query_ser = sprintf("SELECT sum(indirect_expense) FROM assyrate_indirect_expense WHERE total_date>=%d AND total_date<=%d AND item='バイモル'", $str_ym, $end_ym);
    $res_ser = array();
    $rows_ser = getResult($query_ser, $res_ser);
    $fact_indirect_l += $res_ser[0][0];
    $result->add('fact_indirect_l', $fact_indirect_l);
    ////////// カプラ調達部門費間接配賦経費
    ////////// C外注
    $query_ser = sprintf("SELECT sum(indirect_expense) FROM assyrate_indirect_expense WHERE total_date>=%d AND total_date<=%d AND item='Ｃ外注'", $str_ym, $end_ym);
    $res_ser = array();
    $rows_ser = getResult($query_ser, $res_ser);
    $suppli_indirect_c += $res_ser[0][0];
    ////////// C外特注
    $query_ser = sprintf("SELECT sum(indirect_expense) FROM assyrate_indirect_expense WHERE total_date>=%d AND total_date<=%d AND item='Ｃ外特注'", $str_ym, $end_ym);
    $res_ser = array();
    $rows_ser = getResult($query_ser, $res_ser);
    $suppli_indirect_c += $res_ser[0][0];
    $result->add('suppli_indirect_c', $suppli_indirect_c);
    ////////// リニア調達部門費間接配賦経費
    ////////// L外注
    $query_ser = sprintf("SELECT sum(indirect_expense) FROM assyrate_indirect_expense WHERE total_date>=%d AND total_date<=%d AND item='Ｌ外注'", $str_ym, $end_ym);
    $res_ser = array();
    $rows_ser = getResult($query_ser, $res_ser);
    $suppli_indirect_l += $res_ser[0][0];
    ////////// 外注バイ
    $query_ser = sprintf("SELECT sum(indirect_expense) FROM assyrate_indirect_expense WHERE total_date>=%d AND total_date<=%d AND item='外注バイ'", $str_ym, $end_ym);
    $res_ser = array();
    $rows_ser = getResult($query_ser, $res_ser);
    $suppli_indirect_l += $res_ser[0][0];
    $result->add('suppli_indirect_l', $suppli_indirect_l);
    $suppli_indirect_t = $suppli_indirect_c + $suppli_indirect_l; // 調達部門費間接配賦経費合計
    $result->add('suppli_indirect_t', $suppli_indirect_t);
}

////////////// サービス割合データ計算
function act_indirect_cal($request, $result)
{
    if ($request->get('kessan') != '') {
        $str_ym = $request->get('str_ym');
        $end_ym = $request->get('end_ym');
    } elseif ($request->get('tangetu') != '') {
        $str_ym = $request->get('tan_str_ym');
        $end_ym = $request->get('tan_end_ym');
    }
    $manu_service           = 0;      // 工場間接費製造課（サービス割合の値）
    $c_assembly_service     = 0;      // 工場間接費C組立課（サービス割合の値）
    $factory_indirect       = 0;      // 工場間接費合計
    $fact_indirect_l        = 0;      // リニア工場間接費間接配賦経費（サービス割合の値）
    $suppli_indirect_c      = 0;      // カプラ調達部門費間接配賦経費（サービス割合の値）
    $suppli_indirect_l      = 0;      // リニア調達部門費間接配賦経費（サービス割合の値）
    $suppli_indirect_t      = 0;      // 調達部門費間接配賦経費合計
    ////////// 工場間接費製造課（累計計算）
    ////////// C製造
    $query_ser = sprintf("SELECT sum(total_cost) FROM service_percent_factory_expenses WHERE total_date>=%d AND total_date<=%d AND total_item='Ｃ製造'", $str_ym, $end_ym);
    $res_ser = array();
    $rows_ser = getResult($query_ser, $res_ser);
    $manu_service += $res_ser[0][0];
    ////////// C製特注
    $query_ser = sprintf("SELECT sum(total_cost) FROM service_percent_factory_expenses WHERE total_date>=%d AND total_date<=%d AND total_item='Ｃ製特注'", $str_ym, $end_ym);
    $res_ser = array();
    $rows_ser = getResult($query_ser, $res_ser);
    $manu_service += $res_ser[0][0];
    $result->add('manu_service', $manu_service);
    ////////// 工場間接費C組立課（累計計算）
    ////////// C組立
    $query_ser = sprintf("SELECT sum(total_cost) FROM service_percent_factory_expenses WHERE total_date>=%d AND total_date<=%d AND total_item='Ｃ組立'", $str_ym, $end_ym);
    $res_ser = array();
    $rows_ser = getResult($query_ser, $res_ser);
    $c_assembly_service += $res_ser[0][0];
    ////////// C組特注
    $query_ser = sprintf("SELECT sum(total_cost) FROM service_percent_factory_expenses WHERE total_date>=%d AND total_date<=%d AND total_item='Ｃ組特注'", $str_ym, $end_ym);
    $res_ser = array();
    $rows_ser = getResult($query_ser, $res_ser);
    $c_assembly_service += $res_ser[0][0];
    $result->add('c_assembly_service', $c_assembly_service);
    ////////// リニア工場間接費間接配賦経費（累計計算）
    ////////// L組立
    $query_ser = sprintf("SELECT sum(total_cost) FROM service_percent_factory_expenses WHERE total_date>=%d AND total_date<=%d AND total_item='Ｌ組立'", $str_ym, $end_ym);
    $res_ser = array();
    $rows_ser = getResult($query_ser, $res_ser);
    $fact_indirect_l += $res_ser[0][0];
    ////////// バイモル
    $query_ser = sprintf("SELECT sum(total_cost) FROM service_percent_factory_expenses WHERE total_date>=%d AND total_date<=%d AND total_item='バイモル'", $str_ym, $end_ym);
    $res_ser = array();
    $rows_ser = getResult($query_ser, $res_ser);
    $fact_indirect_l += $res_ser[0][0];
    $result->add('fact_indirect_l', $fact_indirect_l);
    ////////// カプラ調達部門費間接配賦経費（累計計算）
    ////////// C外注
    $query_ser = sprintf("SELECT sum(total_cost) FROM service_percent_factory_expenses WHERE total_date>=%d AND total_date<=%d AND total_item='Ｃ外注'", $str_ym, $end_ym);
    $res_ser = array();
    $rows_ser = getResult($query_ser, $res_ser);
    $suppli_indirect_c += $res_ser[0][0];
    ////////// C外特注
    $query_ser = sprintf("SELECT sum(total_cost) FROM service_percent_factory_expenses WHERE total_date>=%d AND total_date<=%d AND total_item='Ｃ外特注'", $str_ym, $end_ym);
    $res_ser = array();
    $rows_ser = getResult($query_ser, $res_ser);
    $suppli_indirect_c += $res_ser[0][0];
    $result->add('suppli_indirect_c', $suppli_indirect_c);
    ////////// リニア調達部門費間接配賦経費（累計計算）
    ////////// L外注
    $query_ser = sprintf("SELECT sum(total_cost) FROM service_percent_factory_expenses WHERE total_date>=%d AND total_date<=%d AND total_item='Ｌ外注'", $str_ym, $end_ym);
    $res_ser = array();
    $rows_ser = getResult($query_ser, $res_ser);
    $suppli_indirect_l += $res_ser[0][0];
    ////////// 外注バイ
    $query_ser = sprintf("SELECT sum(total_cost) FROM service_percent_factory_expenses WHERE total_date>=%d AND total_date<=%d AND total_item='外注バイ'", $str_ym, $end_ym);
    $res_ser = array();
    $rows_ser = getResult($query_ser, $res_ser);
    $suppli_indirect_l += $res_ser[0][0];
    $result->add('suppli_indirect_l', $suppli_indirect_l);
    $suppli_indirect_t = $suppli_indirect_c + $suppli_indirect_l; // 調達部門費間接配賦経費合計
    $result->add('suppli_indirect_t', $suppli_indirect_t);
}

////////////// 各種データ取得
function get_various_data($request, $result)
{
    if ($request->get('kessan') != '') {
        $str_ym = $request->get('str_ym');
        $end_ym = $request->get('end_ym');
    } elseif ($request->get('tangetu') != '') {
        $str_ym = $request->get('tan_str_ym');
        $end_ym = $request->get('tan_end_ym');
    }
    $item                = array();         // 配賦率対象グループ
    $external_price      = array();         // 外注費
    $external_assy_price = array();         // 外注ASSY費
    $direct_expenses     = 0;               // 直接費
    $query_in = sprintf("SELECT * FROM indirect_cost_allocate WHERE total_date=%d", $end_ym);
    $res_in = array();
    $rows_in = getResult($query_in, $res_in);
    $result->add_array2('res_in', $res_in);
    $result->add('rows_in', $rows_in);
    for ($i=0; $i<$rows_in; $i++) {
        $external_price[$i] = 0;
    }
    for ($i=0; $i<$rows_in; $i++) {
        $external_assy_price[$i] = 0;
    }
    for ($i=0; $i<$rows_in; $i++) {
        $item[$i] = $res_in[$i]['item'];    // 作業グループ
    }
    for ($i=0; $i<$rows_in; $i++) {
        $query = sprintf("SELECT sum(external_price) FROM indirect_cost_allocate WHERE total_date>=%d AND total_date<=%d AND item='%s'", $str_ym, $end_ym, $item[$i]);
        $res_sum = array();
        $rows_sum = getResult($query, $res_sum);
        if ($res_sum[0]['sum'] == "") {
            $external_price[$i] = 0;        // 外注費集計
        } else {
            $external_price[$i] += $res_sum[0]['sum'];
        }
        $query = sprintf("SELECT sum(external_assy_price) FROM indirect_cost_allocate WHERE total_date>=%d AND total_date<=%d AND item='%s'", $str_ym, $end_ym, $item[$i]);
        $res_sum = array();
        $rows_sum = getResult($query, $res_sum);
        if ($res_sum[0]['sum'] == "") {
            $external_assy_price[$i] = 0;   // 外注ASSY費集計
        } else {
            $external_assy_price[$i] += $res_sum[0]['sum'];
        }
        $query = sprintf("SELECT sum(direct_expense) FROM indirect_cost_allocate WHERE total_date>=%d AND total_date<=%d AND item='%s'", $str_ym, $end_ym, $item[$i]);
        $res_sum = array();
        $rows_sum = getResult($query, $res_sum);
        if ($res_sum[0]['sum'] == "") {
            $direct_expenses = 0;           // 直接費集計
        } else {
            $direct_expenses += $res_sum[0]['sum'];
        }
    }
    $result->add_array2('item', $item);
    $result->add_array2('external_price', $external_price);
    $result->add_array2('external_assy_price', $external_assy_price);
    $result->add('direct_expenses', $direct_expenses);
}

////////////// 工場間接費配賦率取得
function get_indirect_cost($result, $request)
{
    $item                = $result->get_array2('item');
    $external_price      = $result->get_array2('external_price');
    $external_assy_price = $result->get_array2('external_assy_price');
    $res_in              = $result->get_array2('res_in');
    $indirect_cost       = array();                                 // 工場間接費配賦率
    $suppli_section_cost = array();                                 // 調達部門費配賦率
    $total_indirect_section = 0;                                    // 間接部門費合計
    $fact_indirect_c        = 0;                                    // カプラ工場間接費間接配賦経費
    $exp_ext_assy_c         = 0;                                    // カプラ経費＋外注ASSY費
    $exp_ext_assy_l         = 0;                                    // リニア経費＋外注ASSY費
    $fact_indirect_t        = 0;                                    // 工場間接費間接配賦経費合計
    $c_indirect_cost        = 0;                                    // カプラ工場間接費配賦率
    $c_suppli_section_cost  = 0;                                    // カプラ調達部門費配賦率
    $l_indirect_cost        = 0;                                    // リニア工場間接費配賦率
    $l_suppli_section_cost  = 0;                                    // リニア調達部門費配賦率
    $external_price_c       = 0;                                    // カプラ外注費（表示用）
    $external_price_l       = 0;                                    // リニア外注費（表示用）
    $external_assy_price_c  = 0;                                    // カプラ外注ASSY費（表示用）
    $external_assy_price_l  = 0;                                    // カプラ外注ASSY費（表示用）
    $fact_indirect_c = $result->get('manu_service') + $result->get('c_assembly_service') - $result->get('direct_expenses'); // カプラ工場間接費間接配賦経費
    $fact_indirect_t = $fact_indirect_c + $result->get('fact_indirect_l') + $result->get('direct_expenses'); // 工場間接配賦経費計
    $total_indirect_section = $fact_indirect_t + $result->get('suppli_indirect_t'); // 間接部門費合計
    $exp_ext_assy_c = $result->get('c_expense');                    // カプラ経費＋外注ASSY費計算①
    $exp_ext_assy_l = $result->get('l_assembly_expense');           // リニア経費＋外注ASSY費計算①
    for ($i=0; $i<$result->get('rows_in'); $i++) {
        switch ($item[$i]) {
            case 'カプラ':    //対象グループがカプラの時
                $external_price_c       = $external_price[$i];      // カプラ外注費（表示用）
                $external_assy_price_c  = $external_assy_price[$i]; // カプラ外注ASSY費（表示用）
                $exp_ext_assy_c += $external_assy_price[$i];        // カプラ経費＋外注ASSY費計算②
                $indirect_cost[$i] = $res_in[$i]['indirect_cost'];  // 工場間接費配賦率の取得
                $c_indirect_cost = $indirect_cost[$i];
                break;
            case 'リニア':    //対象グループがリニアの時
                $external_price_l       = $external_price[$i];      // リニア外注費（表示用）
                $external_assy_price_l  = $external_assy_price[$i]; // カプラ外注ASSY費（表示用）
                $exp_ext_assy_l += $external_assy_price[$i];        // リニア経費＋外注ASSY費計算②
                $indirect_cost[$i] = $res_in[$i]['indirect_cost'];  // 工場間接費配賦率の取得
                $l_indirect_cost = $indirect_cost[$i];
                break;
            default:
                break;
        }
    }
    ////////// 調達部門費配賦率取得
    for ($i=0; $i<$result->get('rows_in'); $i++) {
        switch ($item[$i]) {
            case 'カプラ':                                                     // 対象グループがカプラの時
                $suppli_section_cost[$i] = $res_in[$i]['suppli_section_cost']; // 調達部門費配賦率の取得
                $c_suppli_section_cost = $suppli_section_cost[$i];
                break;
            case 'リニア':                                                     // 対象グループがリニアの時
                $suppli_section_cost[$i] = $res_in[$i]['suppli_section_cost']; // 調達部門費配賦率の取得
                $l_suppli_section_cost = $suppli_section_cost[$i];
                break;
            default:
                break;
        }
    }
    $result->add('fact_indirect_c', $fact_indirect_c);
    $result->add('fact_indirect_t', $fact_indirect_t);
    $result->add('total_indirect_section', $total_indirect_section);
    $result->add('exp_ext_assy_c', $exp_ext_assy_c);
    $result->add('exp_ext_assy_l', $exp_ext_assy_l);
    $result->add('external_price_c', $external_price_c);
    $result->add('external_price_l', $external_price_l);
    $result->add('external_assy_price_c', $external_assy_price_c);
    $result->add('external_assy_price_l', $external_assy_price_l);
    $result->add('c_indirect_cost', $c_indirect_cost);
    $result->add('l_indirect_cost', $l_indirect_cost);
    $result->add('c_suppli_section_cost', $c_suppli_section_cost);
    $result->add('l_suppli_section_cost', $l_suppli_section_cost);
}

////////////// 工場間接費配賦率計算
function indirect_cost_cal($result, $request)
{
    $item                = $result->get_array2('item');
    $external_price      = $result->get_array2('external_price');
    $external_assy_price = $result->get_array2('external_assy_price');
    $res_in              = $result->get_array2('res_in');
    $indirect_cost       = array();                                 // 工場間接費配賦率
    $suppli_section_cost = array();                                 // 調達部門費配賦率
    $total_indirect_section = 0;                                    // 間接部門費合計
    $fact_indirect_c        = 0;                                    // カプラ工場間接費間接配賦経費
    $exp_ext_assy_c         = 0;                                    // カプラ経費＋外注ASSY費
    $exp_ext_assy_l         = 0;                                    // リニア経費＋外注ASSY費
    $fact_indirect_t        = 0;                                    // 工場間接費間接配賦経費合計
    $c_indirect_cost        = 0;                                    // カプラ工場間接費配賦率
    $c_suppli_section_cost  = 0;                                    // カプラ調達部門費配賦率
    $l_indirect_cost        = 0;                                    // リニア工場間接費配賦率
    $l_suppli_section_cost  = 0;                                    // リニア調達部門費配賦率
    $external_price_c       = 0;                                    // カプラ外注費（表示用）
    $external_price_l       = 0;                                    // リニア外注費（表示用）
    $external_assy_price_c  = 0;                                    // カプラ外注ASSY費（表示用）
    $external_assy_price_l  = 0;                                    // カプラ外注ASSY費（表示用）
    $fact_indirect_c = $result->get('manu_service') + $result->get('c_assembly_service') - $result->get('direct_expenses');      //カプラ工場間接費間接配賦経費
    $fact_indirect_t = $fact_indirect_c + $result->get('fact_indirect_l') + $result->get('direct_expenses'); // 工場間接配賦経費計
    $total_indirect_section = $fact_indirect_t + $result->get('suppli_indirect_t'); // 間接部門費合計
    $exp_ext_assy_c = $result->get('c_expense');                    // カプラ経費＋外注ASSY費計算①
    $exp_ext_assy_l = $result->get('l_assembly_expense');           // リニア経費＋外注ASSY費計算①
    for ($i=0; $i<$result->get('rows_in'); $i++) {
        switch ($item[$i]) {
            case 'カプラ':                                          // 対象グループがカプラの時
                $external_price_c       = $external_price[$i];      // カプラ外注費（表示用）
                $external_assy_price_c  = $external_assy_price[$i]; // カプラ外注ASSY費（表示用）
                $exp_ext_assy_c += $external_assy_price[$i];        // カプラ経費＋外注ASSY費計算②
                $indirect_cost[$i] = $fact_indirect_c / $exp_ext_assy_c * 100;
                $c_indirect_cost = $indirect_cost[$i];
                break;
            case 'リニア':                                          // 対象グループがリニアの時
                $external_price_l       = $external_price[$i];      // リニア外注費（表示用）
                $external_assy_price_l  = $external_assy_price[$i]; // カプラ外注ASSY費（表示用）
                $exp_ext_assy_l += $external_assy_price[$i];        // リニア経費＋外注ASSY費計算②
                $indirect_cost[$i] = $result->get('fact_indirect_l') / $exp_ext_assy_l * 100;
                $l_indirect_cost = $indirect_cost[$i];
                break;
            default:
                break;
        }
    }
    ////////// 調達部門費配賦率計算
    for ($i=0; $i<$result->get('rows_in'); $i++) {
        switch ($item[$i]) {
            case 'カプラ':                                          // 対象グループがカプラの時
                $suppli_section_cost[$i] = $result->get('suppli_indirect_c') / $external_price[$i] * 100;
                $c_suppli_section_cost = $suppli_section_cost[$i];
                break;
            case 'リニア':                                          // 対象グループがリニアの時
                $suppli_section_cost[$i] = $result->get('suppli_indirect_l') / $external_price[$i] * 100;
                $l_suppli_section_cost = $suppli_section_cost[$i];
                break;
            default:
                break;
        }
    }
    $result->add('fact_indirect_c', $fact_indirect_c);
    $result->add('fact_indirect_t', $fact_indirect_t);
    $result->add('total_indirect_section', $total_indirect_section);
    $result->add('exp_ext_assy_c', $exp_ext_assy_c);
    $result->add('exp_ext_assy_l', $exp_ext_assy_l);
    $result->add('external_price_c', $external_price_c);
    $result->add('external_price_l', $external_price_l);
    $result->add('external_assy_price_c', $external_assy_price_c);
    $result->add('external_assy_price_l', $external_assy_price_l);
    $result->add('c_indirect_cost', $c_indirect_cost);
    $result->add('l_indirect_cost', $l_indirect_cost);
    $result->add('c_suppli_section_cost', $c_suppli_section_cost);
    $result->add('l_suppli_section_cost', $l_suppli_section_cost);
}

////////////// 賃率計算
function assembly_actAllocate_cal ($request, $result, $menu)
{    
    if ($request->get('kessan') != '') {
        $str_ym = $request->get('str_ym');
        $end_ym = $request->get('end_ym');
    } elseif ($request->get('tangetu') != '') {
        $str_ym = $request->get('tan_str_ym');
        $end_ym = $request->get('tan_end_ym');
    }
    get_various_data($request, $result);     // 各種データ取得
    act_expenses_cal($request, $result);     // 各部門経費計算
    act_indirect_cal($request, $result);     // サービス割合データ計算
    indirect_cost_cal($result, $request);    // 工場間接費配賦率計算
}

////////////// ６桁の任意の日付を前月の'期月'フォーマットして返す。
function format_ki_before($date6)
{
    if (0 == $date6) {
        $date6 = '--------';    
    }
    if ($date6 < 200000) {
        $date6 = '--------';    
    }
    if (6 == strlen($date6)) {
        $nen   = substr($date6, 0, 4);
        $tsuki = substr($date6, 4, 2);
        if (1 == $tsuki) {
            $nen   = $nen - 1;
            $tsuki = 12;
        } else {
            $tsuki = $tsuki - 1;
        }
    }
    if (6 == strlen($date6)) {
        $ki    = substr($nen, 3, 1);
        if (0 < $tsuki && $tsuki < 4) {
            return "第" . $ki . "期" . $tsuki . "月";
        } else {
            $ki = $ki + 1;
            return "第" . $ki . "期" . $tsuki . "月";
        }
    } else {
        return FALSE;
    }
}

////////////// グループ番号をグループ名に変換
function format_number_name($number, $res_nn, $rows_nn)
{
    for ($n=0; $n<$rows_nn; $n++) {
        if ($res_nn[$n][0] == $number) {
            $group_name = $res_nn[$n][1];
            return $group_name;
        }
    }
}

function getViewHTMLbody($request, $menu, $result)
{
    $listTable = '';
    $listTable .= "<!DOCTYPE HTML PUBLIC '-//W3C//DTD HTML 4.01 Transitional//EN'>\n";
    $listTable .= "<html>\n";
    $listTable .= "<head>\n";
    $listTable .= "<meta http-equiv='Content-Type' content='text/html; charset=UTF-8'>\n";
    $listTable .= "<meta http-equiv='Content-Style-Type' content='text/css'>\n";
    $listTable .= "<meta http-equiv='Content-Script-Type' content='text/javascript'>\n";
    $listTable .= "<script type='text/javascript' src='../assemblyRate_actAllocate.js'></script>\n";
    $listTable .= "<link rel='stylesheet' href='../assemblyRate_actAllocate.css' type='text/css'>\n";
    $listTable .= "</head>\n";
    $listTable .= "<body>\n";
    $listTable .= "<center>\n";
    if ($request->get('kessan') != '') {
        $str_ym = $request->get('str_ym');
        $end_ym = $request->get('end_ym');
        $end_m  = substr($end_ym, 4, 2);
        $end_m  = $end_m + 1 - 1;
        $str_m  = substr($str_ym, 4, 2);
        $str_m  = $str_m + 1 - 1;
    } elseif ($request->get('tangetu') != '') {
        $str_ym = $request->get('tan_str_ym');
        $end_ym = $request->get('tan_end_ym');
        $end_m  = substr($end_ym, 4, 2);
        $end_m  = $end_m + 1 - 1;
        $str_m  = substr($str_ym, 4, 2);
        $str_m  = $str_m + 1 - 1;
    }
    $expenses_175 = $result->get('expenses_175');
    $expenses_176 = $result->get('expenses_176');
    if($end_ym < 201404) { 
        $expenses_510 = $result->get('expenses_510');
    }
    $expenses_518 = $result->get('expenses_518');
    $expenses_519 = $result->get('expenses_519');
    $expenses_520 = $result->get('expenses_520');
    $expenses_522 = $result->get('expenses_522');
    $expenses_523 = $result->get('expenses_523');
    $expenses_525 = $result->get('expenses_525');
    if($end_ym < 201404) { 
        $expenses_526 = $result->get('expenses_526');
    } else {
        $expenses_547 = $result->get('expenses_547');
    }
    $expenses_527 = $result->get('expenses_527');
    $expenses_528 = $result->get('expenses_528');
    $expenses_551 = $result->get('expenses_551');
    $expenses_556 = $result->get('expenses_556');
    $expenses_560 = $result->get('expenses_560');
    if($end_ym < 201404) { 
        $expenses_571 = $result->get('expenses_571');
    }
    $expenses_572 = $result->get('expenses_572');
    if($end_ym < 201012) { 
        $expenses_559 = $result->get('expenses_559');
    } 
    $c_suppli_section_cost = $result->get('c_suppli_section_cost');
    $l_suppli_section_cost = $result->get('l_suppli_section_cost');
    $total_indirect_section = $result->get('total_indirect_section');
    $total_direct_section = $result->get('total_direct_section');
    $external_assy_price_c = $result->get('external_assy_price_c');
    $external_assy_price_l = $result->get('external_assy_price_l');
    $fact_indirect_c = $result->get('fact_indirect_c');
    $fact_indirect_l = $result->get('fact_indirect_l');
    $fact_indirect_t = $result->get('fact_indirect_t');
    $manu_service = $result->get('manu_service');
    $manu_expenses = $result->get('manu_expenses');
    $c_assembly_service = $result->get('c_assembly_service');
    $direct_expenses = $result->get('direct_expenses');
    $exp_ext_assy_c = $result->get('exp_ext_assy_c');
    $exp_ext_assy_l = $result->get('exp_ext_assy_l');
    $c_indirect_cost = $result->get('c_indirect_cost');
    $l_indirect_cost = $result->get('l_indirect_cost');
    $c_assembly_expense = $result->get('c_assembly_expense');
    $l_assembly_expense = $result->get('l_assembly_expense');
    $c_expense = $result->get('c_expense');
    $suppli_indirect_c = $result->get('suppli_indirect_c');
    $suppli_indirect_l = $result->get('suppli_indirect_l');
    $suppli_indirect_t = $result->get('suppli_indirect_t');
    $external_price_c = $result->get('external_price_c');
    $external_price_l = $result->get('external_price_l');
    $listTable .= "    <table class=border-none border=0 cellpadding=0 cellspacing=0 nowrap>\n";
    $listTable .= "        <col class=border-none nowrap width=108 style='mso-width-source:userset;mso-width-alt:3456;width:81pt'>\n";
    $listTable .= "        <col class=border-none nowrap width=107 style='mso-width-source:userset;mso-width-alt:3424;width:80pt'>\n";
    $listTable .= "        <col class=border-none nowrap width=73 style='mso-width-source:userset;mso-width-alt:2336;width:55pt'>\n";
    $listTable .= "        <col class=border-none nowrap width=125 style='mso-width-source:userset;mso-width-alt:4000;width:94pt'>\n";
    $listTable .= "        <col class=border-none nowrap width=18 style='mso-width-source:userset;mso-width-alt:576;width:14pt'>\n";
    $listTable .= "        <col class=border-none nowrap width=53 style='mso-width-source:userset;mso-width-alt:1696;width:40pt'>\n";
    $listTable .= "        <col class=border-none nowrap width=100 style='mso-width-source:userset;mso-width-alt:3200;width:75pt'>\n";
    $listTable .= "        <col class=border-none nowrap width=72 style='mso-width-source:userset;mso-width-alt:2304;width:54pt'>\n";
    $listTable .= "        <col class=border-none nowrap width=125 style='mso-width-source:userset;mso-width-alt:4000;width:94pt'>\n";
    $listTable .= "        <col class=border-none nowrap width=25 style='mso-width-source:userset;mso-width-alt:800;width:19pt'>\n";
    $listTable .= "        <col class=border-none nowrap width=140 style='mso-width-source:userset;mso-width-alt:4480;width:105pt'>\n";
    $listTable .= "        <col class=border-none nowrap width=88 span=4>\n";
    $listTable .= "        <tr>\n";
    $listTable .= "            <td class=border-none colspan=2></td>\n";
    $listTable .= "            <td colspan=3 nowrap class=16pt>間接費配賦率</td>\n";
    $listTable .= "            <td class=16pt colspan=6 nowrap>". format_date6_ki($end_ym). "迄実績（". $str_m . "月～" . $end_m . "月）</td>\n";
    $listTable .= "        </tr>\n";
    $listTable .= "        <tr>\n";
    $listTable .= "            <td colspan=3 class=bold-11pt align=left>加工費・組立費の実際発生額</td>\n";
    $listTable .= "            <td colspan=7 class=border-none></td>\n";
    $listTable .= "            <td class=border-none>(単位：千円)</td>\n";
    $listTable .= "        </tr>\n";
    $listTable .= "        <tr>\n";
    $listTable .= "            <td class=border-none style='border-top:.5pt solid windowtext;border-left:.5pt solid windowtext'>製造部門経費</td>\n";
    $listTable .= "            <td class=border-none style='border-top:.5pt solid windowtext'>製造1課(518)</td>\n";
    $listTable .= "            <td class=border-none style='border-top:.5pt solid windowtext'>". number_format($expenses_518/1000, 0) ."</td>\n";
    $listTable .= "            <td class=border-none style='border-top:.5pt solid windowtext;border-right:.5pt solid windowtext'>　</td>\n";
    $listTable .= "            <td class=border-none></td>\n";
    $listTable .= "            <td colspan=4 class=bold-11pt nowrap>配賦率の計算(間接配賦経費／実際発生額)</td>\n";
    $listTable .= "            <td class=border-none></td>\n";
    $listTable .= "            <td class=back_orange style='border-bottom:none'>間接部門合計e+f</td>\n";
    $listTable .= "        </tr>\n";
    $listTable .= "        <tr>\n";
    $listTable .= "            <td class=border-none style='border-left:.5pt solid windowtext'>　</td>\n";
    $listTable .= "            <td class=border-none>第1 NC(519)</td>\n";
    $listTable .= "            <td class=border-none>". number_format($expenses_519/1000, 0) ."</td>\n";
    $listTable .= "            <td class=border-none style='border-right:.5pt solid windowtext'>　</td>\n";
    $listTable .= "            <td colspan=6 class=border-none></td>\n";
    $listTable .= "            <td class=back_orange style='border-top:none;border-bottom:none'>". number_format($total_indirect_section/1000, 0) ."</td>\n";
    $listTable .= "        </tr>\n";
    $listTable .= "        <tr>\n";
    $listTable .= "            <td class=border-none style='border-left:.5pt solid windowtext'>　</td>\n";
    $listTable .= "            <td class=border-none>第4 NC(520)</td>\n";
    $listTable .= "            <td class=border-none>". number_format($expenses_520/1000, 0) ."</td>\n";
    $listTable .= "            <td class=border-none style='border-right:.5pt solid windowtext'>　</td>\n";
    $listTable .= "            <td class=border-none></td>\n";
    $listTable .= "            <td colspan=3 class=border-none-left style='border-top:.5pt solid windowtext;border-left:.5pt solid windowtext'>１）工場間接費</td>\n";
    $listTable .= "            <td colspan=2 class=border-none style='border-top:.5pt solid windowtext'>　</td>\n";
    $listTable .= "            <td class=border-none style='border-top:.5pt solid windowtext;border-right:.5pt solid windowtext'>　</td>\n";
    $listTable .= "        </tr>\n";
    $listTable .= "        <tr>\n";
    $listTable .= "            <td class=border-none style='border-left:.5pt solid windowtext'>　</td>\n";
    if($end_ym < 201404) { 
        $listTable .= "            <td class=border-none>第1 6軸(526)</td>\n";
        $listTable .= "            <td class=border-none>". number_format($expenses_526/1000, 0) ."</td>\n";
    } else {
        $listTable .= "            <td class=border-none>製造2課(547)</td>\n";
        $listTable .= "            <td class=border-none>". number_format($expenses_547/1000, 0) ."</td>\n";
    
    }
    $listTable .= "            <td class=border-none style='border-right:.5pt solid windowtext'>　</td>\n";
    $listTable .= "            <td class=border-none></td>\n";
    $listTable .= "            <td class=border-none style='border-left:.5pt solid windowtext'>　</td>\n";
    $listTable .= "            <td class=border-none>製造</td>\n";
    $listTable .= "            <td class=border-none>". number_format($manu_service/1000, 0) ."</td>\n";
    $listTable .= "            <td colspan=2 class=border-none></td>\n";
    $listTable .= "            <td class=border-none style='border-right:.5pt solid windowtext'>　</td>\n";
    $listTable .= "        </tr>\n";
    $listTable .= "        <tr>\n";
    $listTable .= "            <td class=border-none style='border-left:.5pt solid windowtext'>　</td>\n";
    $listTable .= "            <td class=border-none>第5 PF(527)</td>\n";
    $listTable .= "            <td class=border-none>". number_format($expenses_527/1000, 0) ."</td>\n";
    $listTable .= "            <td class=border-none style='border-right:.5pt solid windowtext'>　</td>\n";
    $listTable .= "            <td class=border-none></td>\n";
    $listTable .= "            <td class=border-none style='border-left:.5pt solid windowtext'>　</td>\n";
    $listTable .= "            <td class=border-none>+　C組立</td>\n";
    $listTable .= "            <td class=border-none>". number_format($c_assembly_service/1000, 0) ."</td>\n";
    $listTable .= "            <td colspan=2 class=border-none></td>\n";
    $listTable .= "            <td class=border-none style='border-right:.5pt solid windowtext'>　</td>\n";
    $listTable .= "        </tr>\n";
    $listTable .= "        <tr>\n";
    $listTable .= "            <td class=border-none style='border-left:.5pt solid windowtext'>　</td>\n";
    $listTable .= "            <td class=border-none>2次加工(528)</td>\n";
    $listTable .= "            <td class=border-none>". number_format($expenses_528/1000, 0) ."</td>\n";
    $listTable .= "            <td class=border-none style='border-right:.5pt solid windowtext'>　</td>\n";
    $listTable .= "            <td class=border-none></td>\n";
    $listTable .= "            <td class=border-none style='border-left:.5pt solid windowtext'>　</td>\n";
    $listTable .= "            <td class=border-none style='border-bottom:.5pt solid windowtext'>- 直接費</td>\n";
    $listTable .= "            <td class=border-none style='border-bottom:.5pt solid windowtext'>". number_format($direct_expenses/1000, 0) ."</td>\n";
    $listTable .= "            <td colspan=2 class=border-none></td>\n";
    $listTable .= "            <td class=border-none style='border-right:.5pt solid windowtext'>　</td>\n";
    $listTable .= "        </tr>\n";
    $listTable .= "        <tr>\n";
    $listTable .= "            <td class=border-none style='border-left:.5pt solid windowtext'>　</td>\n";
    $listTable .= "            <td class=border-none>特注(556)</td>\n";
    $listTable .= "            <td class=border-none>". number_format($expenses_556/1000, 0) ."</td>\n";
    $listTable .= "            <td class=border-none style='border-right:.5pt solid windowtext'>　</td>\n";
    $listTable .= "            <td class=border-none></td>\n";
    $listTable .= "            <td class=border-none style='border-left:.5pt solid windowtext'>　</td>\n";
    $listTable .= "            <td class=border-none>カプラ合計</td>\n";
    $listTable .= "            <td class=back_blue style='border:none'>". number_format($fact_indirect_c/1000, 0) ."</td>\n";
    $listTable .= "            <td colspan=2 class=border-none></td>\n";
    $listTable .= "            <td class=border-none style='border-right:.5pt solid windowtext'>　</td>\n";
    $listTable .= "        </tr>\n";
    $listTable .= "        <tr>\n";
    $listTable .= "            <td class=border-none style='border-bottom:.5pt solid windowtext;border-left:.5pt solid windowtext'>　</td>\n";
    $listTable .= "            <td class=border-none style='border-top:.5pt solid windowtext;border-bottom:.5pt solid windowtext'>合計</td>\n";
    $listTable .= "            <td class=back_green style='border-left:none;border-right:none'>". number_format($manu_expenses/1000, 0) ."</td>\n";
    $listTable .= "            <td class=border-none-left style='border-right:.5pt solid windowtext;border-bottom:.5pt solid windowtext'>(a)</td>\n";
    $listTable .= "            <td class=border-none></td>\n";
    $listTable .= "            <td class=border-none style='border-left:.5pt solid windowtext'>　</td>\n";
    $listTable .= "            <td class=border-none>リニア組立</td>\n";
    $listTable .= "            <td class=back_blue style='border:none'>". number_format($fact_indirect_l/1000, 0) ."</td>\n";
    $listTable .= "            <td colspan=2 class=border-none></td>\n";
    $listTable .= "            <td class=border-none style='border-right:.5pt solid windowtext'>　</td>\n";
    $listTable .= "        </tr>\n";
    $listTable .= "        <tr>\n";
    $listTable .= "            <td colspan=5 class=border-none></td>\n";
    $listTable .= "            <td class=border-none style='border-left:.5pt solid windowtext'>　</td>\n";
    $listTable .= "            <td colspan=4 class=border-none></td>\n";
    $listTable .= "            <td class=bold-11pt align=center style='border-right:.5pt solid windowtext;border-top:.5pt solid windowtext;border-left:.5pt solid windowtext'>". format_date6_ki($end_ym) ."</td>\n";
    $listTable .= "        </tr>\n";
    $listTable .= "        <tr>\n";
    $listTable .= "            <td class=border-none style='border-top:.5pt solid windowtext;border-left:.5pt solid windowtext' nowrap>C組立部門経費</td>\n";
    $listTable .= "            <td class=border-none style='border-top:.5pt solid windowtext' nowrap>組立1課(176)</td>\n";
    $listTable .= "            <td class=border-none style='border-top:.5pt solid windowtext'>". number_format($expenses_176/1000, 0) ."</td>\n";
    $listTable .= "            <td class=border-none style='border-top:.5pt solid windowtext;border-right:.5pt solid windowtext'>　</td>\n";
    $listTable .= "            <td class=border-none></td>\n";
    $listTable .= "            <td class=border-none style='border-left:.5pt solid windowtext'>　</td>\n";
    $listTable .= "            <td class=border-none>間接配賦経費</td>\n";
    $listTable .= "            <td class=border-none-center>÷</td>\n";
    $listTable .= "            <td class=border-none nowrap>経費+外注ASSY費</td>\n";
    $listTable .= "            <td class=border-none-center>＝</td>\n";
    $listTable .= "            <td class=bold-11pt style='border-right:.5pt solid windowtext;border-bottom:.5pt solid windowtext;border-left:.5pt solid windowtext' nowrap>工場間接費配賦率</td>\n";
    $listTable .= "        </tr>\n";
    $listTable .= "        <tr>\n";
    $listTable .= "            <td class=border-none style='border-left:.5pt solid windowtext'>　</td>\n";
    if($end_ym < 201404) { 
        $listTable .= "            <td class=border-none>生産部C(510)</td>\n";
        $listTable .= "            <td class=border-none>". number_format($expenses_510/1000, 0) ."</td>\n";
    } else {
        $listTable .= "            <td class=border-none>　</td>\n";
        $listTable .= "            <td class=border-none>　</td>\n";
    }
    $listTable .= "            <td class=border-none style='border-right:.5pt solid windowtext'>　</td>\n";
    $listTable .= "            <td class=border-none></td>\n";
    $listTable .= "            <td class=border-none style='border-left:.5pt solid windowtext'>カプラ</td>\n";
    $listTable .= "            <td class=border-none>". number_format($fact_indirect_c/1000, 0) ."</td>\n";
    $listTable .= "            <td class=border-none-center>÷</td>\n";
    $listTable .= "            <td class=border-none>". number_format($exp_ext_assy_c/1000, 0) ."</td>\n";
    $listTable .= "            <td class=border-none-center>＝</td>\n";
    $listTable .= "            <td class=back_blue_red>". number_format($c_indirect_cost, 1) ."%</td>\n";
    $listTable .= "        </tr>\n";
    $listTable .= "        <tr>\n";
    $listTable .= "            <td class=border-none style='border-left:.5pt solid windowtext'>　</td>\n";
    $listTable .= "            <td class=border-none>ＭＡ係(522)</td>\n";
    $listTable .= "            <td class=border-none>". number_format($expenses_522/1000, 0) ."</td>\n";
    $listTable .= "            <td class=border-none style='border-right:.5pt solid windowtext'>　</td>\n";
    $listTable .= "            <td class=border-none></td>\n";
    $listTable .= "            <td class=border-none style='border-left:.5pt solid windowtext'>リニア</td>\n";
    $listTable .= "            <td class=border-none>". number_format($fact_indirect_l/1000, 0) ."</td>\n";
    $listTable .= "            <td class=border-none-center>÷</td>\n";
    $listTable .= "            <td class=border-none>". number_format($exp_ext_assy_l/1000, 0) ."</td>\n";
    $listTable .= "            <td class=border-none-center>＝</td>\n";
    $listTable .= "            <td class=back_blue_red style='border-bottom:.5pt solid windowtext'>". number_format($l_indirect_cost, 1) ."%</td>\n";
    $listTable .= "        </tr>\n";
    $listTable .= "        <tr>\n";
    $listTable .= "            <td class=border-none style='border-left:.5pt solid windowtext'>　</td>\n";
    $listTable .= "            <td class=border-none>ＨＡ係(523)</td>\n";
    $listTable .= "            <td class=border-none>". number_format($expenses_523/1000, 0) ."</td>\n";
    $listTable .= "            <td class=border-none style='border-right:.5pt solid windowtext'>　</td>\n";
    $listTable .= "            <td class=border-none></td>\n";
    $listTable .= "            <td class=border-none style='border-bottom:.5pt solid windowtext;border-left:.5pt solid windowtext'>　</td>\n";
    $listTable .= "            <td class=back_orange style='border-top:none;border-right:none;border-left:none'>". number_format($fact_indirect_t/1000, 0) ."</td>\n";
    $listTable .= "            <td class=border-none-left style='border-bottom:.5pt solid windowtext'>(e)</td>\n";
    $listTable .= "            <td colspan=2 class=border-none style='border-bottom:.5pt solid windowtext'>　</td>\n";
    $listTable .= "            <td class=border-none style='border-bottom:.5pt solid windowtext;border-right:.5pt solid windowtext'>　</td>\n";
    $listTable .= "        </tr>\n";
    $listTable .= "        <tr>\n";
    $listTable .= "            <td class=border-none style='border-left:.5pt solid windowtext'>　</td>\n";
    $listTable .= "            <td class=border-none>C特注(525)</td>\n";
    $listTable .= "            <td class=border-none>". number_format($expenses_525/1000, 0) ."</td>\n";
    $listTable .= "            <td class=border-none style='border-right:.5pt solid windowtext'>　</td>\n";
    $listTable .= "            <td class=border-none></td>\n";
    $listTable .= "            <td colspan=6 class=border-none-left style='border-top:none;border-left:.5pt solid windowtext;border-right:.5pt solid windowtext' nowrap>２）調達部門費（間接部門経費配賦額／外注費）</td>\n";
    $listTable .= "        </tr>\n";
    $listTable .= "        <tr>\n";
    $listTable .= "            <td class=border-none style='border-left:.5pt solid windowtext'>　</td>\n";
    if($end_ym < 201404) { 
        $listTable .= "            <td class=border-none style='border-bottom:.5pt solid windowtext'>第4組立C(571)</td>\n";
        $listTable .= "            <td class=border-none style='border-bottom:.5pt solid windowtext'>". number_format($expenses_571/1000, 0) ."</td>\n";
    } else {
        $listTable .= "            <td class=border-none style='border-bottom:.5pt solid windowtext'>　</td>\n";
        $listTable .= "            <td class=border-none style='border-bottom:.5pt solid windowtext'>　</td>\n";
    }
    $listTable .= "            <td class=border-none style='border-right:.5pt solid windowtext'>　</td>\n";
    $listTable .= "            <td class=border-none style='border-left:.5pt solid windowtext'></td>\n";
    $listTable .= "            <td class=border-none style='border-left:.5pt solid windowtext'>　</td>\n";
    $listTable .= "            <td colspan=4 class=border-none></td>\n";
    $listTable .= "            <td class=bold-11pt align=center style='border-right:.5pt solid windowtext;border-top:.5pt solid windowtext;border-left:.5pt solid windowtext'>". format_date6_ki($end_ym) ."</td>\n";
    $listTable .= "        </tr>\n";
    $listTable .= "        <tr>\n";
    $listTable .= "            <td class=border-none style='border-left:.5pt solid windowtext'>　</td>\n";
    $listTable .= "            <td class=border-none>合計</td>\n";
    $listTable .= "            <td class=back_green style='border:none'>". number_format($c_assembly_expense/1000, 0) ."</td>\n";
    $listTable .= "            <td class=border-none-left style='border-right:.5pt solid windowtext'>(b)</td>\n";
    $listTable .= "            <td class=border-none></td>\n";
    $listTable .= "            <td class=border-none style='border-left:.5pt solid windowtext'>　</td>\n";
    $listTable .= "            <td class=border-none nowrap>間接配賦経費</td>\n";
    $listTable .= "            <td class=border-none-center>÷</td>\n";
    $listTable .= "            <td class=border-none>外注費</td>\n";
    $listTable .= "            <td class=border-none-center>＝</td>\n";
    $listTable .= "            <td class=bold-11pt style='border-right:.5pt solid windowtext;border-bottom:.5pt solid windowtext;border-left:.5pt solid windowtext'>調達部門費配賦率</td>\n";
    $listTable .= "        </tr>\n";
    $listTable .= "        <tr>\n";
    $listTable .= "            <td class=border-none style='border-left:.5pt solid windowtext;border-bottom:.5pt solid windowtext'>　</td>\n";
    $listTable .= "            <td class=class=border-none style='border-bottom:.5pt solid windowtext'>C経費</td>\n";
    $listTable .= "            <td class=back_green style='border-top:none;border-right:none;border-left:none'>". number_format($c_expense/1000, 0) ."</td>\n";
    $listTable .= "            <td class=border-none-left style='border-bottom:.5pt solid windowtext;border-right:.5pt solid windowtext'>(c)=(a+b)</td>\n";
    $listTable .= "            <td class=border-none></td>\n";
    $listTable .= "            <td class=border-none style='border-left:.5pt solid windowtext' nowrap>カプラ</td>\n";
    $listTable .= "            <td class=border-none>". number_format($suppli_indirect_c/1000, 0) ."</td>\n";
    $listTable .= "            <td class=border-none-center>÷</td>\n";
    $listTable .= "            <td class=border-none>". number_format($external_price_c/1000, 0) ."</td>\n";
    $listTable .= "            <td class=border-none-center>＝</td>\n";
    $listTable .= "            <td class=back_blue_red>". number_format($c_suppli_section_cost, 1) ."%</td>\n";
    $listTable .= "        </tr>\n";
    $listTable .= "        <tr>\n";
    $listTable .= "            <td colspan=5 class=border-none></td>\n";
    $listTable .= "            <td class=border-none style='border-left:.5pt solid windowtext'>リニア</td>\n";
    $listTable .= "            <td class=border-none>". number_format($suppli_indirect_l/1000, 0) ."</td>\n";
    $listTable .= "            <td class=border-none-center>÷</td>\n";
    $listTable .= "            <td class=border-none>". number_format($external_price_l/1000, 0) ."</td>\n";
    $listTable .= "            <td class=border-none-center>＝</td>\n";
    $listTable .= "            <td class=back_blue_red style='border-bottom:.5pt solid windowtext'>". number_format($l_suppli_section_cost, 1) ."%</td>\n";
    $listTable .= "        </tr>\n";
    $listTable .= "        <tr>\n";
    $listTable .= "            <td class=border-none style='border-top:.5pt solid windowtext;border-left:.5pt solid windowtext'>L組立部門経費</td>\n";
    $listTable .= "            <td class=border-none style='border-top:.5pt solid windowtext'>L組立課(551)</td>\n";
    $listTable .= "            <td class=border-none style='border-top:.5pt solid windowtext'>". number_format($expenses_551/1000, 0) ."</td>\n";
    $listTable .= "            <td class=border-none style='border-top:.5pt solid windowtext;border-right:.5pt solid windowtext'>　</td>\n";
    $listTable .= "            <td class=border-none></td>\n";
    $listTable .= "            <td class=border-none style='border-left:.5pt solid windowtext;border-bottom:.5pt solid windowtext'>　</td>\n";
    $listTable .= "            <td class=back_orange style='border-top:none;border-right:none;border-left:none'>". number_format($suppli_indirect_t/1000, 0) ."</td>\n";
    $listTable .= "            <td class=border-none-left style='border-bottom:.5pt solid windowtext'>(f)</td>\n";
    $listTable .= "            <td colspan=2 class=border-none style='border-bottom:.5pt solid windowtext'>　</td>\n";
    $listTable .= "            <td class=border-none style='border-right:.5pt solid windowtext;border-bottom:.5pt solid windowtext'>　</td>\n";
    $listTable .= "        </tr>\n";
    $listTable .= "        <tr>\n";
    $listTable .= "            <td class=border-none style='border-left:.5pt solid windowtext'>　</td>\n";
    $listTable .= "            <td class=border-none>L組立(175)</td>\n";
    $listTable .= "            <td class=border-none>". number_format($expenses_175/1000, 0) ."</td>\n";
    $listTable .= "            <td class=border-none style='border-right:.5pt solid windowtext'>　</td>\n";
    $listTable .= "        </tr>\n";
    $listTable .= "        <tr>\n";
    $listTable .= "            <td class=border-none style='border-left:.5pt solid windowtext'>　</td>\n";
    $listTable .= "            <td class=border-none>バイモル(560)</td>\n";
    $listTable .= "            <td class=border-none'>". number_format($expenses_560/1000, 0) ."</td>\n";
    $listTable .= "            <td class=border-none style='border-right:.5pt solid windowtext'>　</td>\n";
    $listTable .= "        </tr>\n";
    $listTable .= "        <tr>\n";
    $listTable .= "            <td class=border-none style='border-left:.5pt solid windowtext'>　</td>\n";
    $listTable .= "            <td class=border-none>第4組立L(572)</td>\n";
    $listTable .= "            <td class=border-none>". number_format($expenses_572/1000, 0) ."</td>\n";
    $listTable .= "            <td class=border-none style='border-right:.5pt solid windowtext'>　</td>\n";
    $listTable .= "        </tr>\n";
    $listTable .= "        <tr>\n";
    $listTable .= "            <td class=border-none style='border-left:.5pt solid windowtext'>　</td>\n";
    if($end_ym < 201012) { 
        $listTable .= "            <td class=border-none style='border-bottom:.5pt solid windowtext'>L修理(559)</td>\n";
        $listTable .= "            <td class=border-none style='border-bottom:.5pt solid windowtext'>". number_format($expenses_559/1000, 0) ."</td>\n";
    } else {
        $listTable .= "            <td class=border-none style='border-bottom:.5pt solid windowtext'>　</td>\n";
        $listTable .= "            <td class=border-none style='border-bottom:.5pt solid windowtext'>　</td>\n";
    }
    $listTable .= "            <td class=border-none style='border-right:.5pt solid windowtext'>　</td>\n";
    $listTable .= "        </tr>\n";
    $listTable .= "        <tr>\n";
    $listTable .= "            <td class=border-none style='border-left:.5pt solid windowtext;border-bottom:.5pt solid windowtext'>　</td>\n";
    $listTable .= "            <td class=border-none style='border-bottom:.5pt solid windowtext'>合計（L経費）</td>\n";
    $listTable .= "            <td class=back_green style='border-right:none;border-left:none;border-top:none'>". number_format($l_assembly_expense/1000, 0) ."</td>\n";
    $listTable .= "            <td class=border-none-left style='border-right:.5pt solid windowtext;border-bottom:.5pt solid windowtext'>(d)</td>\n";
    $listTable .= "        </tr>\n";
    $listTable .= "        <tr>\n";
    $listTable .= "            <td class=bold-11pt>外注費の実績</td>\n";
    $listTable .= "            <td class=border-none style='border-left:.5pt solid windowtext'>C外注費</td>\n";
    $listTable .= "            <td class=back_yellow>". number_format($external_price_c/1000, 0) ."</td>\n";
    $listTable .= "            <td class=back_green style='border-bottom:none;border-top:none;border-left:none' nowrap>直接部門合計c+d</td>\n";
    $listTable .= "        </tr>\n";
    $listTable .= "        <tr>\n";
    $listTable .= "            <td class=border-none></td>\n";
    $listTable .= "            <td class=border-none style='border-bottom:.5pt solid windowtext;border-left:.5pt solid windowtext'>L外注費</td>\n";
    $listTable .= "            <td class=back_yellow style='border-bottom:.5pt solid windowtext'>". number_format($external_price_l/1000, 0) ."</td>\n";
    $listTable .= "            <td class=back_green style='border-top:none;border-left:none'>". number_format($total_direct_section/1000, 0) ."</td>\n";
    $listTable .= "        </tr>\n";
    $listTable .= "        <tr>\n";
    $listTable .= "            <td class=border-none>　</td>\n";
    $listTable .= "        </tr>\n";
    $listTable .= "        <tr>\n";
    $listTable .= "            <td class=bold-11pt>外注ASSY費</td>\n";
    $listTable .= "            <td class=border-none style='border-top:.5pt solid windowtext;border-left:.5pt solid windowtext'>C外注ASSY</td>\n";
    $listTable .= "            <td class=light_blue style='border-top:.5pt solid windowtext'>". number_format($external_assy_price_c/1000, 0) ."</td>\n";
    $listTable .= "        </tr>\n";
    $listTable .= "        <tr>\n";
    $listTable .= "            <td class=border-none></td>\n";
    $listTable .= "            <td class=border-none style='border-bottom:.5pt solid windowtext;border-left:.5pt solid windowtext'>L外注ASSY</td>\n";
    $listTable .= "            <td class=light_blue style='border-bottom:.5pt solid windowtext'>". number_format($external_assy_price_l/1000, 0) ."</td>\n";
    $listTable .= "        </tr>\n";
    $listTable .= "    </table>\n";
    $listTable .= "</body>\n";
    $listTable .= "</html>\n";
    return $listTable;
}

////////////// 賃率照会画面のHTMLを出力
function outViewListHTML($request, $menu, $result)
{
    $listHTML = getViewHTMLbody($request, $menu, $result);
    $request->add('view_flg', '照会');
    ////////// HTMLファイル出力
    $file_name = "list/assemblyRate_actAllocate_List-{$_SESSION['User_ID']}.html";
    $handle = fopen($file_name, 'w');
    fwrite($handle, $listHTML);
    fclose($handle);
    chmod($file_name, 0666);    // fileを全てrwモードにする
}
