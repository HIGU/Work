<?php
//////////////////////////////////////////////////////////////////////////////
// 組立賃率 照会画面 main部 assemblyRate_reference_Main.php(旧wage_rate.php)//
// Copyright (C) 2007-2020 Norihisa.Ooya usoumu@nitto-kohki.co.jp           //
// Changed history                                                          //
// 2007/11/14 Created  assemblyRate_reference_Main.php                      //
// 2007/12/11 余分な<font>タグの削除、コメントの追加、コメント位置の調整    //
//            グループマスター取得関数内の変数の_gを削除                    //
//            作業者と標準賃率の登録ロジック内のsql部分を別関数に分割       //
// 2007/12/12 登録済みデータの取得から手作業賃率各種データ取得と製造経費    //
//            計算を関数として分割                                          //
// 2007/12/29 日付の初期値の設定を追加                                      //
//            前画面に戻る時決算処理の対象年月か前画面で選択した日付を返す  //
//            ように変更                                                    //
// 2008/01/10 機械賃率表示と手作業賃率表示の文字サイズを                    //
//            css(machine_rate/labor_rate)で11から12に変更                  //
//            タイトル部はmachine_rate_title/labor_ratetitleに変更し        //
//            文字サイズは11のまま                                          //
// 2009/04/10 新しくリニア修理部門（559）を追加                             //
// 2010/02/04 製造経費を取り込まないと決算処理が出来ないように変更          //
// 2010/03/03 期年月の表示を調整。substrの後に+1-1して数字にして0を消す     //
// 2010/12/09 税務調査指摘によりリニア修理(559)を削除 2010/12〜             //
// 2011/06/22 format_date系をtnk_funcに移動のためこちらを削除               //
// 2012/01/10 登録済みデータのチェック方法の確認                            //
// 2013/09/05 571,510は存在しない部門なので除外                             //
// 2015/11/05 自動機賃率が4桁だとnumber_formatでカンマが入ってしまうので    //
//            round関数に変更                                               //
// 2020/07/02 横川応援時間が分かりにくいので削除                            //
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
    $menu = new MenuHeader(0);                      // 認証チェック0=一般以上 戻り先=TOP_MENU タイトル未設定
       
    ////////// タイトル名(ソースのタイトル名とフォームのタイトル名)
    $menu->set_title('組立賃率の照会');
    
    $request = new Request;
    $result  = new Result;
    
    if ($request->get('end_ym') !== '') {
        ////// リターンアドレス設定
        $menu->set_RetUrl($_SESSION['wage_referer'] . '?wage_ym=' . $request->get('end_ym'));
    } else {
        ////// リターンアドレス設定
        $menu->set_RetUrl($_SESSION['wage_referer'] . '?wage_ym=' . $request->get('wage_ym'));
    }
    
    get_group_master($result, $request);            // 各種データの取得
    
    request_check($request, $result, $menu);        // 処理の分岐チェック
    
    calculation_branch($request, $result, $menu);   // 賃率計算の分岐
    
    display($menu, $request, $result);              // 画面表示
}

////////////// 画面表示
function display($menu, $request, $result)
{       
    /////////// ブラウザーのキャッシュ対策用
    $uniq = 'id=' . $menu->set_useNotCache('target');
    
    /////////// メッセージ出力フラグ
    $msg_flg = 'site';

    ob_start('ob_gzhandler');                       // 出力バッファをgzip圧縮
    
    ////////// HTML Header を出力してキャッシュを制御
    $menu->out_html_header();
 
    ////////// Viewの処理
    require_once ('assemblyRate_reference_View.php');

    ob_end_flush(); 
}

////////////// 表示用(一覧表)のグループマスターデータをSQLで取得
function get_group_master ($result, $request)
{
    $query = "
        SELECT  groupm.group_no                AS グループ番号     -- 0
            ,   groupm.group_name              AS グループ名       -- 1
        FROM
            assembly_machine_group_master AS groupm
        ORDER BY
            group_no
    ";

    $res = array();
    if (($rows = getResultWithField2($query, $field, $res)) <= 0) {
        $_SESSION['s_sysmsg'] = "グループの登録がありません！";
        $result->add_array2('res_g', '');
        $result->add('num_g', '');
        $result->add('rows_g', '');
    } else {
        $num = count($field);
        $result->add_array2('res_g', $res);
        $result->add('num_g', $num);
        $result->add('rows_g', $rows);
    }
}

////////////// 表示用(一覧表)のグループマスターデータをSQLで取得
function request_check($request, $result, $menu)
{
    $ok = true;
    if ($request->get('delete') != '') $ok = wageRate_delete($request);
    if ($request->get('entry') != '')  $ok = wageRate_workerEntry($request, $result);
    if ($request->get('input') != '')  $ok = wageRate_input($request, $result);
    if ($ok) {
        ////// データの初期化
        $request->add('delete', '');     // 初期化
        $request->add('entry', '');      // 初期化
        $request->add('input', '');      // 初期化
        if ($request->get('wage_ym') !== '') {
            $request->add('end_ym', $request->get('wage_ym'));    // 初期値の終了年月の設定
            $nen   = substr($request->get('wage_ym'), 0, 4);
            $tsuki = substr($request->get('wage_ym'), 4, 2);
            if (($tsuki < 10) && (3 < $tsuki)) {                  // 初期値の開始年月の設定
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

////////////// 賃率計算の分岐
function calculation_branch($request, $result, $menu)
{
    $request->add('view_flg', '');                               // 照会画面表示のフラグ初期化
    if ($request->get('tangetu') != '') {                        // 日付のデータ取得
        $request->add('rate_register', '登録');                  // 単月の場合は毎回計算を行う為
        $request->add('kessan', '');
    }
    if ($request->get('kessan') != '') {
        $request->add('tangetu', '');
    }
    if ($request->get('kessan') != '' || $request->get('tangetu') != '') {
        if (!registered_data_check($request, $result)) {
            if ($request->get('data_check') == 4) {
                if ($request->get('tangetu') != '') {
                    $_SESSION['s_sysmsg'] .= "対象期間でデータが登録されていない月があります。担当者に確認してください。";    // .= に注意
                    $msg_flg = 'alert';
                    return;
                } elseif ($request->get('kessan') != '') {
                    if (getCheckAuthority(22)) {
                        before_date ($request);                  // 前月履歴表示の為の前月計算
                        get_before_figure ($request, $result);
                        outInputHTML($request, $menu, $result);  // 作業者数と標準賃率の入力画面を出力
                        return;
                    } else {
                        $_SESSION['s_sysmsg'] .= "対象月のデータが登録されていません。担当者に確認してください。";    // .= に注意
                        $msg_flg = 'alert';
                        return;
                    }
                }
            } else {
                $_SESSION['s_sysmsg'] .= "対象月のデータが登録されていません。担当者に確認してください。";    // .= に注意
                $msg_flg = 'alert';
                return;
            }
        } else {
            before_date ($request);                              // 前月履歴表示の為の前月計算
            if(!get_registered_data($request, $result)) {        // 登録済みデータの取得
                assembly_rate_cal ($request, $result, $menu);    // 賃率計算関数の呼出
            }
            outViewListHTML($request, $menu, $result);           // 賃率照会画面のHTMLを出力
        }
    }
}
////////////// 確定解除時のデータの削除ロジック
function wageRate_delete ($request)
{
    $end_ym = $request->get('end_ym');
    $format_ym = '';
    $format_ym = format_date6_kan($end_ym);
    $query = sprintf("DELETE FROM assembly_machine_group_rate WHERE total_date=%d", $end_ym);
    if (query_affected($query) <= 0) {
        $_SESSION['s_sysmsg'] .= "{$format_ym}の確定解除に失敗！";                            // .= に注意
        return false;
    } else {
        $_SESSION['s_sysmsg'] .= "{$format_ym}の確定を解除しました！";                        // .= に注意
        $query = sprintf("DELETE FROM worker_figure_master WHERE total_date=%d", $end_ym);    // 作業者の登録も削除
        query_affected($query);
    }
    return true;
}

////////////// 作業者と標準賃率の登録ロジック
function wageRate_workerEntry($request, $result)
{
    $format_ym = '';
    $format_ym = format_date6_kan($request->get('end_ym'));
    if (wageRate_workerCheck($request)) {
        if (!wageRate_stRateEntryBody($request, $result)) {
            return false;
        }
        if (!wageRate_workerEntryBody($request, $result)) {
            return false;
        }
        $_SESSION['s_sysmsg'] .= "{$format_ym}の作業者数と標準賃率を追加しました！";    // .= に注意
        return true;
    } else {
        return false;
    }
}

////////////// 作業者と標準賃率 標準賃率登録 本体 ロジック
function wageRate_stRateEntryBody($request, $result)
{
    $res_g = $result->get_array2('res_g');
    $standard_rate = $request->get('standard_rate');
    for ($i=0; $i<$request->get('rows_g'); $i++) {
        $query = sprintf("SELECT standard_rate FROM assembly_machine_group_rate WHERE group_no=%d AND total_date=%d",
                            $res_g[$i][0], $request->get('end_ym'));
        $res_chk = array();
        if ( getResult($query, $res_chk) > 0 ) {    // 登録あり UPDATE更新
            $query = sprintf("UPDATE assembly_machine_group_rate SET standard_rate='%s', last_date=CURRENT_TIMESTAMP, last_user='%s' WHERE group_no=%d AND total_date=%d", $standard_rate[$i], $_SESSION['User_ID'], $res_g[$i][0], $request->get('end_ym'));                
            if (query_affected($query) <= 0) {
                $_SESSION['s_sysmsg'] .= "{$res_g[$i][1]}：グループ{$res_g[$i][0]}の作業者数と標準賃率の登録失敗！";    // .= に注意
                $msg_flg = 'alert';
                return false;
            }
        } else {                                    // 登録なし INSERT 新規   
            $query = sprintf("INSERT INTO assembly_machine_group_rate (group_no, total_date, standard_rate, last_date, last_user)
                                VALUES (%d, %d, '%s', CURRENT_TIMESTAMP, '%s')",
                                $res_g[$i][0], $request->get('end_ym'), $standard_rate[$i], $_SESSION['User_ID']);
            if (query_affected($query) <= 0) {
                $_SESSION['s_sysmsg'] .= "{$res_g[$i][1]}：グループ{$res_g[$i][0]}の作業者数と標準賃率の登録失敗！";    // .= に注意
                $msg_flg = 'alert';
                return false;
            }
        }
    }
    return true;
}

////////////// 作業者と標準賃率 作業者登録 本体 ロジック
function wageRate_workerEntryBody($request, $result)
{
    $res_g = $result->get_array2('res_g');
    $worker_figure_s = $request->get('worker_figure_s');
    $worker_figure_p = $request->get('worker_figure_p');
    for ($i=0; $i<$request->get('rows_g'); $i++) {
        $query = sprintf("SELECT worker_figure FROM worker_figure_master WHERE group_no=%d AND total_date=%d AND worker_type=1",
                            $res_g[$i][0], $request->get('end_ym'));
        $res_chk = array();
        if ( getResult($query, $res_chk) > 0 ) {    // 登録あり UPDATE更新
            $query = sprintf("UPDATE worker_figure_master SET worker_type=1, worker_figure='%s', worker_rate='%s', last_date=CURRENT_TIMESTAMP, last_user='%s' WHERE group_no=%d AND total_date=%d", $worker_figure_s[$i], $request->get('worker_rate_s'), $_SESSION['User_ID'], $res_g[$i][0], $request->get('end_ym'));
            if (query_affected($query) <= 0) {
                $_SESSION['s_sysmsg'] .= "{$res_g[$i][1]}：グループ{$res_g[$i][0]}の作業者数と標準賃率の登録失敗！";    // .= に注意
                $msg_flg = 'alert';
                return false;
            }
        } else {                                    // 登録なし INSERT 新規   
            $query = sprintf("INSERT INTO worker_figure_master (group_no, total_date, worker_type, worker_figure, worker_rate, last_date, last_user)
                                VALUES (%d, %d, %d, '%s', '%s', CURRENT_TIMESTAMP, '%s')",
                                $res_g[$i][0], $request->get('end_ym'), '1', $worker_figure_s[$i], $request->get('worker_rate_s'), $_SESSION['User_ID']);
            if (query_affected($query) <= 0) {
                $_SESSION['s_sysmsg'] .= "{$res_g[$i][1]}：グループ{$res_g[$i][0]}の作業者数と標準賃率の登録失敗！";    // .= に注意
                $msg_flg = 'alert';
                return false;
            }
        }
        $query = sprintf("SELECT worker_figure FROM worker_figure_master WHERE group_no=%d AND total_date=%d AND worker_type=2",
                            $res_g[$i][0], $request->get('end_ym'));
        $res_chk = array();
        if ( getResult($query, $res_chk) > 0 ) {    // 登録あり UPDATE更新
            $query = sprintf("UPDATE worker_figure_master SET worker_type=2, worker_figure='%s', worker_rate='%s', last_date=CURRENT_TIMESTAMP, last_user='%s' WHERE group_no=%d AND total_date=%d", $worker_figure_p[$i], $request->get('worker_rate_p'), $_SESSION['User_ID'], $res_g[$i][0], $request->get('end_ym'));
            if (query_affected($query) <= 0) {
                $_SESSION['s_sysmsg'] .= "{$res_g[$i][1]}：グループ{$res_g[$i][0]}の作業者数と標準賃率の登録失敗！";    // .= に注意
                $msg_flg = 'alert';
                return false;
            }
        } else {                                    // 登録なし INSERT 新規   
            $query = sprintf("INSERT INTO worker_figure_master (group_no, total_date, worker_type, worker_figure, worker_rate, last_date, last_user)
                                VALUES (%d, %d, %d, '%s', '%s', CURRENT_TIMESTAMP, '%s')",
                                $res_g[$i][0], $request->get('end_ym'), '2', $worker_figure_p[$i], $request->get('worker_rate_p'), $_SESSION['User_ID']);
            if (query_affected($query) <= 0) {
                $_SESSION['s_sysmsg'] .= "{$res_g[$i][1]}：グループ{$res_g[$i][0]}の作業者数と標準賃率の登録失敗！";    // .= に注意
                $msg_flg = 'alert';
                return false;
            }
        }
    }
    return true;
}

////////////// 組立賃率登録ロジック 計算したデータをDBへ更新 登録は決算処理で登録ボタンを押したときのみ
function wageRate_input($request, $result)
{
    if (getCheckAuthority(22)) {                                  //認証チェック
        $format_ym = '';
        $format_ym = format_date6_kan($request->get('end_ym'));
        if (!wageRate_inputAll($request)) {                       // 全体手作業賃率登録
            return false;
        }
        if (!wageRate_inputCupla($request)) {                     // カプラ手作業賃率登録
            return false;
        }
        if (!wageRate_inputLinear($request)) {                    // リニア手作業賃率登録
            return false;
        }
        if (!wageRate_inputMachine($request)) {                   // 組立自動機賃率登録
            return false;
        }
        $_SESSION['s_sysmsg'] .= "{$format_ym}の賃率を登録しました！";
        return true;
    } else {                                                      // 認証なしエラー
        $_SESSION['s_sysmsg'] .= "編集権限が無い為、DBの更新がされませんでした。";
        return false;
    }
}

////////////// 組立賃率登録本体 全体手作業賃率
function wageRate_inputAll($request)
{
    $labor_rate = number_format($request->get('labor_rate'), 2);
    $query = sprintf("SELECT * FROM assembly_man_labor_rate WHERE item='全体' AND total_date=%d", $request->get('end_ym'));
    $res_chk = array();
    if ( getResult($query, $res_chk) > 0 ) {                  // 登録あり UPDATE更新
        $query = sprintf("UPDATE assembly_man_labor_rate SET cut_expense=%d, expense=%d, assistance_time=%d, worker_time=%d, labor_rate='%s', last_date=CURRENT_TIMESTAMP, last_user='%s' WHERE item='全体' AND total_date='%d'", $request->get('total_cut_expense'), $request->get('total_expense'), $request->get('total_assistance_time'), $request->get('total_worker_time'), $labor_rate, $_SESSION['User_ID'], $request->get('end_ym'));
        if (query_affected($query) <= 0) {
            $_SESSION['s_sysmsg'] .= "賃率の登録失敗！";      // .= に注意
            $msg_flg = 'alert';
            return false;
        }
    } else {                                                  // 登録なし INSERT 新規   
        $query = sprintf("INSERT INTO assembly_man_labor_rate (cut_expense, expense, assistance_time, worker_time, item, total_date, labor_rate, last_date, last_user)
                          VALUES (%d, %d, %d, %d, '全体', %d, '%s',CURRENT_TIMESTAMP, '%s')",
                            $request->get('total_cut_expense'), $request->get('total_expense'), $request->get('total_assistance_time'), $request->get('total_worker_time'), $request->get('end_ym'), $labor_rate, $_SESSION['User_ID']);
        if (query_affected($query) <= 0) {
            $_SESSION['s_sysmsg'] .= "賃率の登録失敗！";      // .= に注意
            $msg_flg = 'alert';
            return false;
        }
    }
    return true;
}

////////////// 組立賃率登録本体 カプラ手作業賃率
function wageRate_inputCupla($request)
{
    $labor_rate_c = number_format($request->get('labor_rate_c'), 2);
    $query = sprintf("SELECT * FROM assembly_man_labor_rate WHERE item='カプラ' AND total_date=%d", $request->get('end_ym'));
    $res_chk = array();
    if ( getResult($query, $res_chk) > 0 ) {                  // 登録あり UPDATE更新
        $query = sprintf("UPDATE assembly_man_labor_rate SET cut_expense=%d, expense=%d, labor_rate='%s', last_date=CURRENT_TIMESTAMP, last_user='%s' WHERE item='カプラ' AND total_date=%d", $request->get('cut_expense_c'), $request->get('expense_c'), $labor_rate_c, $_SESSION['User_ID'], $request->get('end_ym'));
        if (query_affected($query) <= 0) {
            $_SESSION['s_sysmsg'] .= "賃率の登録失敗！";      // .= に注意
            $msg_flg = 'alert';
            return false;
        }
    } else {                                                  // 登録なし INSERT 新規   
        $query = sprintf("INSERT INTO assembly_man_labor_rate (cut_expense, expense, item, total_date, labor_rate, last_date, last_user)
                          VALUES (%d, %d, 'カプラ', %d, '%s',CURRENT_TIMESTAMP, '%s')",
                            $request->get('cut_expense_c'), $request->get('expense_c'), $request->get('end_ym'), $labor_rate_c, $_SESSION['User_ID']);
        if (query_affected($query) <= 0) {
            $_SESSION['s_sysmsg'] .= "賃率の登録失敗！";      // .= に注意
            $msg_flg = 'alert';
            return false;
        }
    }
    return true;
}

////////////// 組立賃率登録本体 リニア手作業賃率
function wageRate_inputLinear($request)
{
    $labor_rate_l = number_format($request->get('labor_rate_l'), 2);
    $query = sprintf("SELECT * FROM assembly_man_labor_rate WHERE item='リニア' AND total_date=%d", $request->get('end_ym'));
    $res_chk = array();
    if ( getResult($query, $res_chk) > 0 ) {                  // 登録あり UPDATE更新
        $query = sprintf("UPDATE assembly_man_labor_rate SET cut_expense=%d, expense=%d, labor_rate='%s', last_date=CURRENT_TIMESTAMP, last_user='%s' WHERE item='リニア' AND total_date='%d'", $request->get('cut_expense_l'), $request->get('expense_l'), $labor_rate_l, $_SESSION['User_ID'], $request->get('end_ym'));
        if (query_affected($query) <= 0) {
            $_SESSION['s_sysmsg'] .= "賃率の登録失敗！";      // .= に注意
            $msg_flg = 'alert';
            return false;
        }
    } else {                                                  // 登録なし INSERT 新規   
        $query = sprintf("INSERT INTO assembly_man_labor_rate (cut_expense, expense, item, total_date, labor_rate, last_date, last_user)
                          VALUES (%d, %d, 'リニア', %d, '%s',CURRENT_TIMESTAMP, '%s')",
                            $request->get('cut_expense_l'), $request->get('expense_l'), $request->get('end_ym'), $labor_rate_l, $_SESSION['User_ID']);
        if (query_affected($query) <= 0) {
            $_SESSION['s_sysmsg'] .= "賃率の登録失敗！";      // .= に注意
            $msg_flg = 'alert';
            return false;
        }
    }
    return true;
}

////////////// 組立賃率登録本体 組立自動機賃率
function wageRate_inputMachine($request)
{
    $group_machine_rate = $request->get('group_machine_rate');
    $res_g              = $request->get('res_g');
    for ($i=0; $i<$request->get('rows_g'); $i++) {
        $group_machine_rate[$i] = round($group_machine_rate[$i], 2);
        $query = sprintf("SELECT * FROM assembly_machine_group_rate WHERE group_no=%d AND total_date=%d",
                            $res_g[$i], $request->get('end_ym'));
        $res_chk = array();
        if ( getResult($query, $res_chk) > 0 ) {              // 登録あり UPDATE更新
            $query = sprintf("UPDATE assembly_machine_group_rate SET group_machine_rate='%s', last_date=CURRENT_TIMESTAMP, last_user='%s' WHERE group_no=%d AND total_date=%d", $group_machine_rate[$i], $_SESSION['User_ID'], $res_g[$i], $request->get('end_ym'));
            if (query_affected($query) <= 0) {
                $_SESSION['s_sysmsg'] .= "賃率の登録失敗！";  // .= に注意
                $msg_flg = 'alert';
                return false;
            }
        } else {                                              // 登録なし INSERT 新規   
            $query = sprintf("INSERT INTO assembly_machine_group_rate (group_no, total_date, group_machine_rate, last_date, last_user)
                              VALUES (%d, %d, '%s',CURRENT_TIMESTAMP, '%s')",
                                $res_g[$i], $request->get('end_ym'), $group_machine_rate[$i], $_SESSION['User_ID']);
            if (query_affected($query) <= 0) {
                $_SESSION['s_sysmsg'] .= "賃率の登録失敗！";  // .= に注意
                $msg_flg = 'alert';
                return false;
            }
        }
    }
    return true;
}

////////////// 作業者・標準賃率の入力チェック
function wageRate_workerCheck ($request)
{
    $worker_figure_s = $request->get('worker_figure_s');
    $worker_figure_p = $request->get('worker_figure_p');
    $worker_rate_s   = $request->get('worker_rate_s');
    $worker_rate_p   = $request->get('worker_rate_p');
    $standard_rate   = $request->get('standard_rate');
    for ($i=0; $i<$request->get('rows_g'); $i++) {    // 未入力のデータが存在しないかチェック
        if ($worker_figure_s[$i] == '') {
            $_SESSION['s_sysmsg'] .= "作業者数(社員)が入力されていません！";
            return false;
        }
        if ($worker_figure_p[$i] == '') {
            $_SESSION['s_sysmsg'] .= "作業者数(パート)が入力されていません！";
            return false;
        }
        if ($worker_rate_s == '') {
            $_SESSION['s_sysmsg'] .= "作業者賃率(社員)が入力されていません！";
            return false;
        }
        if ($worker_rate_p == '') {
            $_SESSION['s_sysmsg'] .= "作業者賃率(パート)が入力されていません！";
            return false;
        }
        if ($standard_rate[$i] == '') {
            $_SESSION['s_sysmsg'] .= "標準賃率が入力されていません！";
            return false;
        }
    }
    for ($i=0; $i<$request->get('rows_g'); $i++) {    // 数値以外の文字が入力されていないかチェック
        if (!is_numeric($worker_figure_s[$i])) {
            $_SESSION['s_sysmsg'] .= "作業者数(社員)には数値以外の文字は入力出来ません｡";
            return false;
        }
        if (!is_numeric($worker_figure_p[$i])) {
            $_SESSION['s_sysmsg'] .= "作業者数(パート)には数値以外の文字は入力出来ません｡";
            return false;
        }
        if (!is_numeric($worker_rate_s)) {
            $_SESSION['s_sysmsg'] .= "作業者賃率(社員)には数値以外の文字は入力出来ません｡";
            return false;
        }
        if (!is_numeric($worker_rate_p)) {
            $_SESSION['s_sysmsg'] .= "作業者賃率(パート)には数値以外の文字は入力出来ません｡";
            return false;
        }
        if (!is_numeric($standard_rate[$i])) {
            $_SESSION['s_sysmsg'] .= "標準賃率には数値以外の文字は入力出来ません｡";
            return false;
        }
    }
    return true;
}


////////////// データ登録のチェック
function registered_data_check($request, $result)
{
    if ($request->get('kessan') != '') {
        $chk_ym = $request->get('str_ym');
        $end_ym = $request->get('end_ym');
    } elseif ($request->get('tangetu') != '') {
        $chk_ym = $request->get('tan_str_ym');
        $end_ym = $request->get('tan_end_ym');
    }
    for ($chk_ym; $end_ym >= $chk_ym; $chk_ym++) {
        $chk_nen   = substr($chk_ym, 0, 4);                                               // チェック用年
        $chk_tsuki = substr($chk_ym, 4, 2);                                               // チェック用月
        if ($chk_tsuki == 13) {                                                           // 月が13になった時年が繰り上がって月を０１に
            $chk_nen   = $chk_nen + 1;
            $chk_tsuki = '01';
            $chk_ym = $chk_nen . $chk_tsuki;
        }
        $query = sprintf("SELECT group_capital FROM assembly_machine_group_rate WHERE total_date=%d AND group_capital >=0", $chk_ym);
        if ( !($rows=getResult($query, $res)) >= $result->get('rows_g')) return false;    // 登録済みのチェック
        $query = sprintf("SELECT group_lease FROM assembly_machine_group_rate WHERE total_date=%d AND group_lease >=0", $chk_ym);
        if ( !($rows=getResult($query, $res)) >= $result->get('rows_g')) return false;    // 登録済みのチェック
        $query = sprintf("SELECT group_repair FROM assembly_machine_group_rate WHERE total_date=%d AND group_repair >=0", $chk_ym);
        if ( !($rows=getResult($query, $res)) >= $result->get('rows_g')) return false;    // 登録済みのチェック
        $query = sprintf("SELECT group_time FROM assembly_machine_group_rate WHERE total_date=%d AND group_time >=0", $chk_ym);
        if ( !($rows=getResult($query, $res)) >= $result->get('rows_g')) return false;    // 登録済みのチェック
        $query = sprintf("SELECT worker_time FROM assembly_man_labor_rate WHERE total_date=%d AND worker_time >=0", $chk_ym);
        if ( !($rows=getResult($query, $res)) >= 2) return false;                         // 登録済みのチェック
        $query = sprintf("SELECT assistance_time FROM assembly_man_labor_rate WHERE total_date=%d AND assistance_time >=0", $chk_ym);
        if ( !($rows=getResult($query, $res)) >= 2) return false;                         // 登録済みのチェック
        $query = sprintf("SELECT worker_figure FROM worker_figure_master WHERE total_date=%d AND worker_figure >=0", $chk_ym);
        if ( ($rows=getResult($query, $res)) >= $result->get('rows_g')) {                 // 登録済みのチェック
            $request->add('data_check', 1);                                               // 登録済み
        } else {
            $request->add('data_check', 4);                                               // 作業者未登録 ※どこでエラーになったかを判断するのに
                                                                                          // data_checkの数字を変えていたが現在は未使用
            return false;
        }
    }
    if ( ($rows=getResult($query, $res)) > 0) {    // 登録済みのチェック
    } else {
        $_SESSION['s_sysmsg'] .= "この処理は先に製造経費の取り込みを行ってから実行してください！";    // .= に注意
        $msg_flg = 'alert';
        return false;
    }
    if ($request->get('data_check') == 1) {
        return true;
    }
}

////////////// 前月履歴表示の為の前月計算
function before_date ($request)
{
    $before_ym = '';
    if ($request->get('kessan') != '') {
        $end_ym = $request->get('end_ym');
    } elseif ($request->get('tangetu') != '') {
        $end_ym = $request->get('tan_end_ym');
    }
    $nen   = substr($end_ym, 0, 4);
    $tsuki = substr($end_ym, 4, 2);
    if (1 == $tsuki) {
        $nen   = $nen - 1;
        $tsuki = 12;
    } else {
        $tsuki = $tsuki - 1;
        if ($tsuki < 10) {
            $tsuki = 0 . $tsuki;
        }
    }
    $before_ym = $nen . $tsuki;
    $request->add('before_ym', $before_ym);
}

////////////// 前月のデータの取得
function get_before_date ($request, $result)
{
    $before_standard_rate = array();                             // 前月の標準（予測）賃率
    $before_machine_rate = array();                              // 前月の標準（予測）賃率
    $be_worker_figure_s = array();                               // 前月作業者数（社員）
    $be_worker_figure_p = array();                               // 前月作業者数（パート）
    $be_worker_rate_s = '';                                      // 前月作業者賃率（社員）
    $be_worker_rate_p = '';                                      // 前月作業者賃率（パート）
    $group_no_be = array();                                      // 前月のグループ番号
    $before_labor_rate_t = 0;                                    // 前月の手作業賃率(合計)
    $before_labor_rate_c = 0;                                    // 前月の手作業賃率(カプラ)
    $before_labor_rate_l = 0;                                    // 前月の手作業賃率(リニア)
    $res_g    = $result->get_array2('res_g');
    $query = sprintf("SELECT * FROM assembly_machine_group_rate WHERE total_date=%d order by group_no", $request->get('before_ym'));
    $res = array();
    $rows_act = getResult($query, $res);
    for ($i=0; $i<$rows_act; $i++) {
        $group_no_be[$i] = $res[$i]['group_no'];
    }
    for ($i=0; $i<$result->get('rows_g'); $i++) {                // 前月の標準賃率の習得
        $query = sprintf("SELECT * FROM assembly_machine_group_rate WHERE total_date=%d AND group_no=%d", $request->get('before_ym'), $group_no_be[$i]);
        $res = array();
        $rows_act = getResult($query, $res);
        for ($r=0; $r<$rows_act; $r++) {
            $before_standard_rate[$i] = $res[$r]['standard_rate'];
        }
    }
    for ($i=0; $i<$result->get('rows_g'); $i++) {                // 前月の自動機賃率の習得
        $query = sprintf("SELECT * FROM assembly_machine_group_rate WHERE total_date=%d AND group_no=%d", $request->get('before_ym'), $group_no_be[$i]);
        $res = array();
        $rows_act = getResult($query, $res);
        for ($r=0; $r<$rows_act; $r++) {
            $before_machine_rate[$i] = $res[$r]['group_machine_rate'];
        }
    }
    $query = sprintf("SELECT * FROM assembly_man_labor_rate WHERE total_date=%d", $request->get('before_ym'));
    $res_be = array();
    $rows_be = getResult($query, $res_be);
    for ($i=0; $i<$rows_be; $i++) {
        if ($res_be[$i]['item'] == '全体') {
            $before_labor_rate_t = $res_be[$i]['labor_rate'];    // 前月の手作業賃率（合計）
        } else if ($res_be[$i]['item'] == 'カプラ') {
            $before_labor_rate_c = $res_be[$i]['labor_rate'];    // 前月の手作業賃率（カプラ）
        } else if ($res_be[$i]['item'] == 'リニア') {
            $before_labor_rate_l = $res_be[$i]['labor_rate'];    // 前月の手作業賃率（リニア）
        }
    }
    $result->add('before_labor_rate_t', $before_labor_rate_t);
    $result->add('before_labor_rate_c', $before_labor_rate_c);
    $result->add('before_labor_rate_l', $before_labor_rate_l);
    $result->add_array2('before_machine_rate', $before_machine_rate);
    $result->add_array2('before_standard_rate', $before_standard_rate);
}

////////////// 前月の付帯作業者データの取得
function get_before_figure ($request, $result)
{
    $before_standard_rate = array();                 // 前月の標準（予測）賃率
    $before_machine_rate = array();                  // 前月の標準（予測）賃率
    $be_worker_figure_s = array();                   // 前月作業者数（社員）
    $be_worker_figure_p = array();                   // 前月作業者数（パート）
    $be_worker_rate_s = '';                          // 前月作業者賃率（社員）
    $be_worker_rate_p = '';                          // 前月作業者賃率（パート）
    $group_no_be = array();                          // 前月のグループ番号
    $before_labor_rate_t = 0;                        // 前月の手作業賃率(合計)
    $before_labor_rate_c = 0;                        // 前月の手作業賃率(カプラ)
    $before_labor_rate_l = 0;                        // 前月の手作業賃率(リニア)
    $res_g    = $result->get_array2('res_g');
    $query = sprintf("SELECT * FROM assembly_machine_group_rate WHERE total_date=%d order by group_no", $request->get('before_ym'));
    $res = array();
    $rows_act = getResult($query, $res);
    for ($i=0; $i<$rows_act; $i++) {
        $group_no_be[$i] = $res[$i]['group_no'];
    }
    for ($i=0; $i<$result->get('rows_g'); $i++) {    // 前月の標準賃率の習得
        $query = sprintf("SELECT * FROM assembly_machine_group_rate WHERE total_date=%d AND group_no=%d", $request->get('before_ym'), $group_no_be[$i]);
        $res = array();
        $rows_act = getResult($query, $res);
        for ($r=0; $r<$rows_act; $r++) {
            $before_standard_rate[$i] = $res[$r]['standard_rate'];
        }
    }
    for ($i=0; $i<$result->get('rows_g'); $i++) {    // 前月の付帯作業者数の習得
        $query = sprintf("SELECT * FROM worker_figure_master WHERE group_no=%d AND total_date=%d AND worker_type=1", $group_no_be[$i], $request->get('before_ym'));
        $res_wo = array();
        $rows_wo = getResult($query, $res_wo);
        for ($r=0; $r<$rows_wo; $r++) {
            $be_worker_figure_s[$i] = $res_wo[$r]['worker_figure'];
            $be_worker_rate_s = $res_wo[$r]['worker_rate'];
        }
    }
    for ($i=0; $i<$result->get('rows_g'); $i++) {    // 前月の付帯作業者数の習得
        $query = sprintf("SELECT * FROM worker_figure_master WHERE group_no=%d AND total_date=%d AND worker_type=2", $group_no_be[$i], $request->get('before_ym'));
        $res_wo = array();
        $rows_wo = getResult($query, $res_wo);
        for ($r=0; $r<$rows_wo; $r++) {
            $be_worker_figure_p[$i] = $res_wo[$r]['worker_figure'];
            $be_worker_rate_p = $res_wo[$r]['worker_rate'];
        }
    }
    $result->add_array2('be_worker_figure_s', $be_worker_figure_s);
    $result->add_array2('be_worker_figure_p', $be_worker_figure_p);
    $result->add_array2('before_standard_rate', $before_standard_rate);
    $result->add('be_worker_rate_s', $be_worker_rate_s);
    $result->add('be_worker_rate_p', $be_worker_rate_p);
}

////////////// 表示用データ計算
function show_data_cal($result) 
{
    $total_expense_sen = $result->get('total_expense') / 1000;                                                // 全体直接経費計（千円）
    $expense_c_sen     = $result->get('expense_c') / 1000;                                                    // カプラ直接経費計（千円）
    //$expense_c_sen     = $result->get('expense_c');                                                    // カプラ直接経費計（千円）
    $expense_l_sen     = $result->get('expense_l') / 1000;                                                    // リニア直接経費計（千円）
    //$expense_l_sen     = $result->get('expense_l');                                                    // リニア直接経費計（千円）
    //$assist_expense    = $result->get('total_assistance_time') / 60 * 1090 / 1000;                            // 横川応援経費全体（千円）
    $assist_expense_c  = $result->get('assist_c') / 60 * 1090 / 1000;                                         // 横川応援経費カプラ（千円）
    //$assist_expense_c  = $result->get('assist_c') / 60 * 1090;                                         // 横川応援経費カプラ（千円）
    $assist_expense_l  = $result->get('assist_l') / 60 * 1090 / 1000;                                         // 横川応援経費リニア（千円）
    //$assist_expense_l  = $result->get('assist_l') / 60 * 1090;                                         // 横川応援経費リニア（千円）
    $total_keihi       = $total_expense_sen + $assist_expense;                                                // 全体の経費部分の合計
    $total_keihi_c     = $expense_c_sen + $assist_expense_c;                                                  // カプラの経費部分の合計
    $total_keihi_l     = $expense_l_sen + $assist_expense_l;                                                  // リニア経費部分の合計
    $total_keihi_cut   = $total_keihi - $result->get('direct_expenses') - $result->get('total_man_expenses'); // 全体減額分を抜いた経費の合計
    $keihi_cut_c       = $total_keihi_c - $result->get('direct_expenses_c') - $result->get('man_expenses_c'); // カプラ減額分を抜いた経費の合計
    $keihi_cut_l       = $total_keihi_l - $result->get('direct_expenses_l') - $result->get('man_expenses_l'); // リニア減額分を抜いた経費の合計
    $total_assemble    = $result->get('total_worker_time') - $result->get('total_assistance_time');           // 全体組立作業時間計
    $assemble_c        = $result->get('worker_time_c') - $result->get('assist_c');                            // カプラ組立作業時間計
    $assemble_l        = $result->get('worker_time_l') - $result->get('assist_l');                            // リニア組立作業時間計

    $result->add('expense_c_sen', $expense_c_sen);
    $result->add('expense_l_sen', $expense_l_sen);
    $result->add('assist_expense', $assist_expense);
    $result->add('assist_expense_c', $assist_expense_c);
    $result->add('assist_expense_l', $assist_expense_l);
    $result->add('total_keihi', $total_keihi);
    $result->add('total_keihi_c', $total_keihi_c);
    $result->add('total_keihi_l', $total_keihi_l);
    $result->add('total_keihi_cut', $total_keihi_cut);
    $result->add('keihi_cut_c', $keihi_cut_c);
    $result->add('keihi_cut_l', $keihi_cut_l);
    $result->add('total_assemble', $total_assemble);
    $result->add('assemble_c', $assemble_c);
    $result->add('assemble_l', $assemble_l);
}

////////////// 手作業経費へ渡すデータの計算(全体)
function laborRate_data_all($result, $request)
{
    $total_capital      = 0;                                            // 全体の減価償却費
    $total_lease        = 0;                                            // 全体のリース料
    $total_repair       = 0;                                            // 全体の修繕費
    $total_time         = 0;                                            // 全体の運転時間
    $total_man_expenses = 0;                                            // 全体の付帯作業者経費
    $total_cut_expense  = 0;                                            // 除く経費合計
    $group_capital_sen = $result->get_array2('group_capital_sen');
    $group_lease_sen = $result->get_array2('group_lease_sen');
    $group_repair_sen = $result->get_array2('group_repair_sen');
    $group_time = $result->get_array2('group_time');
    $man_expenses = $result->get_array2('man_expenses');
    $group_expenses = $result->get_array2('group_expenses');
    for ($i=0; $i<$result->get('rows'); $i++) {
        $total_capital      += $group_capital_sen[$i];                  // 減価償却費の合計
        $total_lease        += $group_lease_sen[$i];                    // リース料の合計
        $total_repair       += $group_repair_sen[$i];                   // 修繕費の合計
        $total_time         += $group_time[$i];                         // 稼働時間の合計
        $total_man_expenses += $man_expenses[$i];                       // 付帯作業者経費の合計
        if ($group_time[$i] > 0 ) {
            $total_rate = $group_expenses[$i] / $group_time[$i] * 1000; // 賃率 ＝ 直接経費÷稼働時間×1000（単位円）
        } else {
            $total_rate = 0;
        }
            $total_cut_expense  += $group_expenses[$i];                 // 除く経費合計の計算
            $total_cut_expense  += $man_expenses[$i];                   // 全体の手作業賃率に使用
    }
    $request->add('total_lease', $total_lease);
    $request->add('total_capital', $total_capital);
    $result->add('total_man_expenses', $total_man_expenses);
    $request->add('total_repair', $total_repair);
    $request->add('total_time', $total_time);
    $direct_expenses = $total_capital + $total_lease + $total_repair;   // 直接経費合計
    if ($direct_expenses[$i] > 0 ) {
        if ($total_time[$i] > 0 ) {
            $total_rate = $direct_expenses / $total_time * 1000;        // 賃率 ＝ 直接経費÷稼働時間×1000（単位円）
        } else {
            $total_rate = 0;
        }
    } else {
        $total_rate = 0;
    }
    $result->add('direct_expenses', $direct_expenses);
    $result->add('total_rate', $total_rate);
    $result->add('total_cut_expense', $total_cut_expense);
}

////////////// 手作業経費へ渡すデータの計算(全体)
function laborRate_data_cl($result, $request)
{
    $cut_expense_c      = 0;                                  // カプラ除く製造経費
    $cut_expense_l      = 0;                                  // リニア除く製造経費
    $direct_expenses_c  = 0;                                  // カプラ直接経費
    $direct_expenses_l  = 0;                                  // リニア直接経費
    $man_expenses_c     = 0;                                  // カプラ作業経費
    $man_expenses_l     = 0;                                  // リニア作業経費
    $group_expenses = $result->get_array2('group_expenses');
    $man_expenses = $result->get_array2('man_expenses');
    $group_no = $result->get_array2('group_no');
    $res_g    = $result->get_array2('res_g');
    for ($i=0; $i<$result->get('rows'); $i++) {
        ////// 手作業経費へ渡すデータの計算CL
        switch (format_number_name($group_no[$i], $res_g, $result->get('rows_g'))) {
            case 'ピストン':                                  // 現在リニアはピストンのみ
                $cut_expense_l     += $group_expenses[$i];    // リニア除く製造経費
                $direct_expenses_l += $group_expenses[$i];    // リニア直接経費
                $cut_expense_l     += $man_expenses[$i];      
                $man_expenses_l    += $man_expenses[$i];      // リニア作業経費
                break;
            default:                                          // ピストン以外はカプラ
                $cut_expense_c     += $group_expenses[$i];    // カプラ除く製造経費
                $direct_expenses_c += $group_expenses[$i];    // カプラ直接経費
                $cut_expense_c     += $man_expenses[$i];
                $man_expenses_c    += $man_expenses[$i];      // カプラ作業経費
                break;
        }                    
    }
    $result->add('direct_expenses_c', $direct_expenses_c);
    $result->add('direct_expenses_l', $direct_expenses_l);
    $result->add('man_expenses_c', $man_expenses_c);
    $result->add('man_expenses_l', $man_expenses_l);
    $result->add('cut_expense_c', $cut_expense_c);
    $result->add('cut_expense_l', $cut_expense_l);
}

////////////// 各種データの取得
function get_various_data($request, $result)
{
    if ($request->get('kessan') != '') {
        $str_ym = $request->get('str_ym');
        $end_ym = $request->get('end_ym');
    } elseif ($request->get('tangetu') != '') {
        $str_ym = $request->get('tan_str_ym');
        $end_ym = $request->get('tan_end_ym');
    }
    $query = sprintf("SELECT * FROM assembly_machine_group_rate WHERE total_date=%d  order by group_no", $end_ym);
    $res = array();
    $rows = getResult($query, $res);
    for ($i=0; $i<$rows; $i++) {
        $group_no[$i]           = $res[$i]['group_no'];
        $group_capital[$i]      = $res[$i]['group_capital'];
        $group_lease[$i]        = $res[$i]['group_lease'];
        $group_machine_rate[$i] = $res[$i]['group_machine_rate'];
        $standard_rate[$i]      = $res[$i]['standard_rate'];
    }
    $result->add_array2('group_no', $group_no);
    $result->add_array2('group_capital', $group_capital);
    $result->add_array2('group_lease', $group_lease);
    $result->add_array2('group_machine_rate', $group_machine_rate);
    $result->add_array2('standard_rate', $standard_rate);
    for ($i=0; $i<$rows; $i++) {    // 付帯作業者(社員)取得
        $query = sprintf("SELECT * FROM worker_figure_master WHERE group_no=%d AND total_date=%d AND worker_type=1", $group_no[$i], $end_ym);
        $res_wf = array();
        $rows_wf = getResult($query, $res_wf);
        for ($r=0; $r<$rows_wf; $r++) {
            $worker_figure_s[$i] = $res_wf[$r]['worker_figure'];
            $worker_rate_s[$i] = $res_wf[$r]['worker_rate'];
        }
    }
    for ($i=0; $i<$rows; $i++) {    // 付帯作業者(パート)取得
        $query = sprintf("SELECT * FROM worker_figure_master WHERE group_no=%d AND total_date=%d AND worker_type=2", $group_no[$i], $end_ym);
        $res_wf = array();
        $rows_wf = getResult($query, $res_wf);
        for ($r=0; $r<$rows_wf; $r++) {
            $worker_figure_p[$i] = $res_wf[$r]['worker_figure'];
            $worker_rate_p[$i] = $res_wf[$r]['worker_rate'];
        }
    }
    $result->add_array2('worker_figure_s', $worker_figure_s);
    $result->add_array2('worker_figure_p', $worker_figure_p);
    $result->add_array2('worker_rate_s', $worker_rate_s);
    $result->add_array2('worker_rate_p', $worker_rate_p);
}

////////////// 登録済みデータの取得
function get_registered_data($request, $result)
{
    $res_g = $result->get_array2('res_g');
    if ($request->get('kessan') != '') {
        $str_ym = $request->get('str_ym');
        $end_ym = $request->get('end_ym');
    } elseif ($request->get('tangetu') != '') {
        $str_ym = $request->get('tan_str_ym');
        $end_ym = $request->get('tan_end_ym');
    }
    $query = sprintf("SELECT * FROM assembly_machine_group_rate WHERE total_date=%d", $end_ym);
    $res = array();
    $rows = getResult($query, $res);
    $result->add('rows', $rows);
    if ($res[0]['group_machine_rate'] == '') {                // 賃率が登録済みかチェック
        ////// 新規 未登録の場合は賃率計算のプログラムへ
        $request->add('rate_register', '登録');
        return false;
    } else if ($request->get('rate_register') == '登録') {    // 単月かどうかチェック
        ////// 単月の場合は賃率計算のプログラムへ
        $request->add('rate_register', '登録');
        return false;
    } else {
        ////// 経歴ありの場合は登録済みのデータを取得し一時データを計算する
        $request->add('rate_register', '照会');
        get_various_data ($request, $result);                 // 各種データの取得
        get_group_data ($result, $request);                   // グループ別データの取得
        get_manRate_data ($result, $request);                 // 手作業賃率各種データ取得
        cal_registered_tempData ($result, $request);          // 登録済み一時データ計算
        laborRate_data_all($result, $request);                // 手作業経費へ渡すデータの計算(全体)
        laborRate_data_cl($result, $request);                 // 手作業経費へ渡すデータの計算(CL)
        get_expense_data ($result, $request);                 // 製造経費取得
        show_data_cal($result);                               // 表示用データ計算
        get_before_date ($request, $result);                  // 前月のデータの取得
        return true;
    }
}
////////////////// 登録済み一時データ計算
function cal_registered_tempData ($result, $request)
{
    $group_capital   = $result->get_array2('group_capital');
    $group_lease     = $result->get_array2('group_lease');
    $group_repair    = $result->get_array2('group_repair');
    $group_time      = $result->get_array2('group_time');
    $worker_figure_s = $result->get_array2('worker_figure_s');
    $worker_figure_p = $result->get_array2('worker_figure_p');
    $worker_rate_s   = $result->get_array2('worker_rate_s');
    $worker_rate_p   = $result->get_array2('worker_rate_p');            
    for ($i=0; $i<$result->get('rows'); $i++) {
        $group_capital_sen[$i] = $group_capital[$i] / 1000;              // グループ別減価償却費(単位千円)
        $group_lease_sen[$i]   = $group_lease[$i] / 1000;                // グループ別リース料(単位千円)
        $group_repair_sen[$i]  = $group_repair[$i] / 1000;               // グループ別修繕費(単位千円)
        ////////// 直接経費（単位千円）
        $group_expenses[$i] = ($group_capital[$i] + $group_lease[$i] + $group_repair[$i]) / 1000;
        if ($group_time[$i] <= 0) {                                      // 稼働時間が０以下なら賃率も０
            $rate[$i] = 0;
        } else {
            $rate[$i] = $group_expenses[$i] / $group_time[$i] * 1000;    // 賃率 ＝ 直接経費÷稼働時間×1000（単位円）
        }
            $man_expenses[$i] = $group_time[$i] * $worker_figure_s[$i] * $worker_rate_s[$i] / 1000 + $group_time[$i] * $worker_figure_p[$i] * $worker_rate_p[$i] / 1000; //付帯作業者経費 ＝ 稼働時間×作業者数×標準賃率÷1000（単位千円）
    }
    $result->add_array2('group_capital_sen', $group_capital_sen);
    $result->add_array2('group_lease_sen', $group_lease_sen);
    $result->add_array2('group_repair_sen', $group_repair_sen);
    $result->add_array2('group_time', $group_time);
    $result->add_array2('man_expenses', $man_expenses);
    $result->add_array2('group_expenses', $group_expenses);
    $result->add_array2('rate', $rate);
}
////////////////// 手作業賃率各種データ取得
function get_manRate_data ($result, $request)
{
    if ($request->get('kessan') != '') {
        $str_ym = $request->get('str_ym');
        $end_ym = $request->get('end_ym');
    } elseif ($request->get('tangetu') != '') {
        $str_ym = $request->get('tan_str_ym');
        $end_ym = $request->get('tan_end_ym');
    }
    $query_man = sprintf("SELECT * FROM assembly_man_labor_rate WHERE total_date=%d", $end_ym);
    $res_man = array();
    $rows_man = getResult($query_man, $res_man);
    $result->add('rows_man', $rows_man);
    for ($i=0; $i<$rows_man; $i++) {                                     // 手作業賃率各種データ取得
        $item[$i]            = $res_man[$i]['item'];
        $worker_time[$i]     = $res_man[$i]['worker_time'];
        $assistance_time[$i] = $res_man[$i]['assistance_time'];
        $expense[$i]         = $res_man[$i]['expense'];
        $man_labor_rate[$i]  = $res_man[$i]['labor_rate'];
    }
    $result->add_array2('item', $item);
    $result->add_array2('worker_time', $worker_time);
    $result->add_array2('assistance_time', $assistance_time);
    $result->add_array2('expense', $expense);
    for ($i=0; $i<$rows_man; $i++) {                                     // 手作業賃率を各部門へ振分
        if ($item[$i] == 'カプラ') {
            $labor_rate_c = $man_labor_rate[$i];
        } else if ($item[$i] == 'リニア') {
            $labor_rate_l = $man_labor_rate[$i];
        } else {
            $labor_rate = $man_labor_rate[$i];
        }
    }
    $result->add('labor_rate', $labor_rate);
    $result->add('labor_rate_c', $labor_rate_c);
    $result->add('labor_rate_l', $labor_rate_l);   
}

////////////////// グループ別データの取得
function get_group_data ($result, $request)
{
    if ($request->get('kessan') != '') {
        $str_ym = $request->get('str_ym');
        $end_ym = $request->get('end_ym');
    } elseif ($request->get('tangetu') != '') {
        $str_ym = $request->get('tan_str_ym');
        $end_ym = $request->get('tan_end_ym');
    }
    $group_no = $result->get_array2('group_no');
    for ($i=0; $i<$result->get('rows_g'); $i++) {
        $query = sprintf("SELECT sum(group_repair) FROM assembly_machine_group_rate WHERE total_date>=%d AND total_date<=%d AND group_no=%d", $str_ym, $end_ym, $group_no[$i]);
        $res_sum = array();                        // グループ別の修繕費計算
        $rows_sum = getResult($query, $res_sum);
        if ($res_sum[0]['sum'] == "") {
            $group_repair[$i] = 0;
        } else {
            $group_repair[$i] = $res_sum[0]['sum'];
        }
        $query = sprintf("SELECT sum(group_time) FROM assembly_machine_group_rate WHERE total_date>=%d AND total_date<=%d AND group_no=%d", $str_ym, $end_ym, $group_no[$i]);
        $res_sum = array();                        // グループ別稼働時間計算
        $rows_sum = getResult($query, $res_sum);
        if ($res_sum[0]['sum'] == "") {
            $group_time[$i] = 0;
        } else {
            $group_time[$i] = $res_sum[0]['sum'];
        }
    }
    for ($i=0; $i<$result->get('rows'); $i++) {    // グループ毎の機械番号の取得
        $query = sprintf("SELECT * FROM assembly_machine_group_work WHERE group_no=%d AND total_date=%d", $group_no[$i], $end_ym);
        $res_mac = array();
        $rows_mac = getResult($query, $res_mac);
        for ($r=0; $r<$rows_mac; $r++) {
            $group_mac_no[$i][$r] = $res_mac[$r]['mac_no'];
        }
    }
    $result->add_array2('group_repair', $group_repair);
    $result->add_array2('group_time', $group_time);
    $result->add_array2('group_mac_no', $group_mac_no);
}

////////////////// 製造経費取得
function get_expense_data ($result, $request)
{
    if ($request->get('kessan') != '') {
        $str_ym = $request->get('str_ym');
        $end_ym = $request->get('end_ym');
    } elseif ($request->get('tangetu') != '') {
        $str_ym = $request->get('tan_str_ym');
        $end_ym = $request->get('tan_end_ym');
    }
    $item            = $result->get_array2('item');
    $worker_time     = $result->get_array2('worker_time');
    $assistance_time = $result->get_array2('assistance_time');
    $expense         = $result->get_array2('expense');
    for ($i=0; $i<$result->get('rows_man'); $i++) {
        switch ($item[$i]) {
            case '全体':
                $total_assistance_time = $assistance_time[$i];           // 合計応援時間計算
                $total_worker_time = $worker_time[$i];                   // 合計作業時間計算
                break;
            case 'カプラ':                                               // 対象グループがカプラの時
                $query = sprintf("SELECT sum(worker_time) FROM assembly_man_labor_rate WHERE total_date>=%d AND total_date<=%d AND item='カプラ'", $str_ym, $end_ym);
                $res_sum = array();
                $rows_sum = getResult($query, $res_sum);
                if ($res_sum[0]['sum'] == "") {
                    $worker_time_c = 0;                                  // 組立時間計算
                } else {
                    $worker_time_c = $res_sum[0]['sum'];
                }
                $query = sprintf("SELECT sum(assistance_time) FROM assembly_man_labor_rate WHERE total_date>=%d AND total_date<=%d AND item='カプラ'", $str_ym, $end_ym);
                $res_sum = array();
                $rows_sum = getResult($query, $res_sum);
                if ($res_sum[0]['sum'] == "") {
                    $assist_c = 0;                                       // 応援時間計算
                } else {
                    $assist_c = $res_sum[0]['sum'];
                }
                $expense_c = $expense[$i];
                break;
            case 'リニア':                                               // 対象グループがリニアの時
                $query = sprintf("SELECT sum(worker_time) FROM assembly_man_labor_rate WHERE total_date>=%d AND total_date<=%d AND item='リニア'", $str_ym, $end_ym);
                $res_sum = array();
                $rows_sum = getResult($query, $res_sum);
                if ($res_sum[0]['sum'] == "") {
                    $worker_time_l = 0;                                  // 組立時間計算
                } else {
                    $worker_time_l = $res_sum[0]['sum'];
                }
                $query = sprintf("SELECT sum(assistance_time) FROM assembly_man_labor_rate WHERE total_date>=%d AND total_date<=%d AND item='リニア'", $str_ym, $end_ym);
                $res_sum = array();
                $rows_sum = getResult($query, $res_sum);
                if ($res_sum[0]['sum'] == "") {
                    $assist_l = 0;                                       // 応援時間計算
                } else {
                    $assist_l = $res_sum[0]['sum'];
                }
                $expense_l = $expense[$i];
                break;
            default:
                break;
        }
    }
    $total_expense = $expense_c + $expense_l;                            // 合計製造経費計算
    $result->add('total_assistance_time', $total_assistance_time);
    $result->add('total_worker_time', $total_worker_time);
    $result->add('worker_time_c', $worker_time_c);
    $result->add('worker_time_l', $worker_time_l);    
    $result->add('assist_c', $assist_c);
    $result->add('assist_l', $assist_l);
    $result->add('total_expense', $total_expense);
    $result->add('expense_c', $expense_c);
    $result->add('expense_l', $expense_l);
}

////////////////// 賃率計算
function assembly_rate_cal ($request, $result, $menu)
{
    $res_g = $result->get_array2('res_g');
    if ($request->get('kessan') != '') {
        $str_ym = $request->get('str_ym');
        $end_ym = $request->get('end_ym');
    } elseif ($request->get('tangetu') != '') {
        $str_ym = $request->get('tan_str_ym');
        $end_ym = $request->get('tan_end_ym');
    }
    get_various_data($request, $result);                                       // 各種データの取得
    get_group_data ($result, $request);                                        // グループ別データの取得
    cal_temp_data ($result, $request);                                         // 一時データの計算
    laborRate_data_all($result, $request);                                     // 手作業経費へ渡すデータの計算
    laborRate_data_cl($result, $request);
    cal_manRate_data ($result, $request);                                      // 手作業賃率各種データ計算
    cal_expense_data ($result, $request);                                      // 製造経費計算
    show_data_cal($result);                                                    // 表示用データ計算
    cal_labor_rate ($result);                                                  // 手作業賃率計算
    get_before_date ($request, $result);                                       // 前月のデータの取得
}

////////////////// 手作業賃率計算
function cal_labor_rate ($result)
{
    $labor_rate    = 0;                          // 全体手作業賃率
    $labor_rate_c  = 0;                          // カプラ手作業賃率
    $labor_rate_l  = 0;                          // リニア手作業賃率
    if ($result->get('total_expense') == 0) {    // 合計の製造経費が０だった場合手作業賃率は計算不可
        $labor_rate = '----';
    } else {                                                                   
        ////////// 全体・カプラ・リニアの手作業賃率計算 （直接経費計＋横川応援経費ー除く経費（直接・作業経費減額分））÷組立作業時間
        $labor_rate   = ( $result->get('total_expense') + ( $result->get('total_assistance_time') / 60 * 1090) - ($result->get('total_cut_expense') * 1000) ) / $result->get('total_worker_time');
        $labor_rate_c = ( $result->get('expense_c') + ( $result->get('assist_c') / 60 * 1090) - ($result->get('cut_expense_c') * 1000) ) / $result->get('worker_time_c');
        $labor_rate_l = ( $result->get('expense_l') + ( $result->get('assist_l') / 60 * 1090) - ($result->get('cut_expense_l') * 1000) ) / $result->get('worker_time_l');
    }
    $result->add('labor_rate', $labor_rate);
    $result->add('labor_rate_c', $labor_rate_c);
    $result->add('labor_rate_l', $labor_rate_l);
}

////////////////// 一時データ計算
function cal_temp_data ($result, $request)
{
    if ($request->get('kessan') != '') {
        $str_ym = $request->get('str_ym');
        $end_ym = $request->get('end_ym');
    } elseif ($request->get('tangetu') != '') {
        $str_ym = $request->get('tan_str_ym');
        $end_ym = $request->get('tan_end_ym');
    }
    $group_capital = $result->get_array2('group_capital');
    $group_repair = $result->get_array2('group_repair');
    $group_lease = $result->get_array2('group_lease');
    $group_time = $result->get_array2('group_time');
    $worker_figure_s = $result->get_array2('worker_figure_s');
    $worker_figure_p = $result->get_array2('worker_figure_p');
    $worker_rate_s = $result->get_array2('worker_rate_s');
    $worker_rate_p = $result->get_array2('worker_rate_p');
    $query = sprintf("SELECT * FROM assembly_machine_group_rate WHERE total_date=%d  order by group_no", $end_ym);
    $res = array();
    $rows_act = getResult($query, $res);
    $result->add('rows', $rows_act);
    for ($i=0; $i<$rows_act; $i++) {
        $group_capital_sen[$i] = $group_capital[$i] / 1000;              // グループ別減価償却費(単位千円)
        $group_lease_sen[$i]   = $group_lease[$i] / 1000;                // グループ別リース料(単位千円)
        $group_repair_sen[$i]  = $group_repair[$i] / 1000;               // グループ別修繕費(単位千円)
        /*
        $group_capital_sen[$i] = $group_capital[$i];              // グループ別減価償却費(単位千円)
        $group_lease_sen[$i]   = $group_lease[$i];                // グループ別リース料(単位千円)
        $group_repair_sen[$i]  = $group_repair[$i];               // グループ別修繕費(単位千円)
        */
        ////////// 直接経費（単位千円）
        $group_expenses[$i] = ($group_capital[$i] + $group_lease[$i] + $group_repair[$i]) / 1000;
        //$group_expenses[$i] = ($group_capital[$i] + $group_lease[$i] + $group_repair[$i]);
        if ($group_time[$i] <= 0) {                                      // 稼働時間が０以下なら賃率も０
            $rate[$i] = 0;
        } else {
            $rate[$i] = $group_expenses[$i] / $group_time[$i] * 1000;    // 賃率 ＝ 直接経費÷稼働時間×1000（単位円）
        }
        $man_expenses[$i] = $group_time[$i] * $worker_figure_s[$i] * $worker_rate_s[$i] / 1000 + $group_time[$i] * $worker_figure_p[$i] * $worker_rate_p[$i] / 1000; //付帯作業者経費 ＝ 稼働時間×作業者数×標準賃率÷1000（単位千円）
        //$man_expenses[$i] = $group_time[$i] * $worker_figure_s[$i] * $worker_rate_s[$i] + $group_time[$i] * $worker_figure_p[$i] * $worker_rate_p[$i]; //付帯作業者経費 ＝ 稼働時間×作業者数×標準賃率÷1000（単位千円）
    }
    for ($i=0; $i<$rows_act; $i++) {
        ////////// 自動機賃率計算
        $group_machine_rate[$i] = $rate[$i] + $worker_figure_s[$i] * $worker_rate_s[$i] + $worker_figure_p[$i] * $worker_rate_p[$i]; //自動機賃率 賃率＋標準賃率×作業者数
        if ($group_time[$i]==0 & $man_expenses[$i]==0) {                 // 付帯作業者経費と稼働時間が0なら自動機賃率も０
            $group_machine_rate[$i] = 0;
        }
    }
    $result->add_array2('group_machine_rate', $group_machine_rate);
    $result->add_array2('group_capital_sen', $group_capital_sen);
    $result->add_array2('group_lease_sen', $group_lease_sen);
    $result->add_array2('group_repair_sen', $group_repair_sen);
    $result->add_array2('group_time', $group_time);
    $result->add_array2('man_expenses', $man_expenses);
    $result->add_array2('group_expenses', $group_expenses);
    $result->add_array2('rate', $rate);
}

////////////////// 手作業賃率各種データ計算
function cal_manRate_data ($result, $request)
{
    $total_worker_time = 0;                  // 作業時間合計
    $total_assistance_time = 0;              // 応援時間合計
    if ($request->get('kessan') != '') {
        $str_ym = $request->get('str_ym');
        $end_ym = $request->get('end_ym');
    } elseif ($request->get('tangetu') != '') {
        $str_ym = $request->get('tan_str_ym');
        $end_ym = $request->get('tan_end_ym');
    }
    $query_man = sprintf("SELECT * FROM assembly_man_labor_rate WHERE total_date=%d", $end_ym);
    $res_man = array();
    $rows_man = getResult($query_man, $res_man);
    for ($i=0; $i<$rows_man; $i++) {
        $item[$i] = $res_man[$i]['item'];    // 作業グループ
    }
    for ($i=0; $i<$rows_man; $i++) {
        $query = sprintf("SELECT sum(worker_time) FROM assembly_man_labor_rate WHERE total_date>=%d AND total_date<=%d AND item='%s'", $str_ym, $end_ym, $item[$i]);
        $res_sum = array();
        $rows_sum = getResult($query, $res_sum);
        if ($res_sum[0]['sum'] == "") {
            $worker_time[$i] = 0;            // 組立時間計算
        } else {
            $worker_time[$i] = $res_sum[0]['sum'];
        }
        $query = sprintf("SELECT sum(assistance_time) FROM assembly_man_labor_rate WHERE total_date>=%d AND total_date<=%d AND item='%s'", $str_ym, $end_ym, $item[$i]);
        $res_sum = array();
        $rows_sum = getResult($query, $res_sum);
        if ($res_sum[0]['sum'] == "") {
            $assistance_time[$i] = 0;        // 応援時間計算
        } else {
            $assistance_time[$i] = $res_sum[0]['sum'];
        }
    }
    $result->add_array2('item', $item);
    $result->add_array2('worker_time', $worker_time);
    $result->add_array2('assistance_time', $assistance_time);
}


////////////////// 製造経費計算
function cal_expense_data ($result, $request)
{
    $total_worker_time = 0;                                                    // 作業時間合計
    $total_assistance_time = 0;                                                // 応援時間合計
    if ($request->get('kessan') != '') {
        $str_ym = $request->get('str_ym');
        $end_ym = $request->get('end_ym');
    } elseif ($request->get('tangetu') != '') {
        $str_ym = $request->get('tan_str_ym');
        $end_ym = $request->get('tan_end_ym');
    }
    $item            = $result->get_array2('item');
    $worker_time     = $result->get_array2('worker_time');
    $assistance_time = $result->get_array2('assistance_time');
    $expense         = $result->get_array2('expense');
    $query_man = sprintf("SELECT * FROM assembly_man_labor_rate WHERE total_date=%d", $end_ym);
    $res_man = array();
    $rows_man = getResult($query_man, $res_man);
    $acts_ym = substr($str_ym, 2, 4);                                          // 製造経費取得の為の開始年月
    $acte_ym = substr($end_ym, 2, 4);                                          // 製造経費取得の為の終了年月（決算なので入力された開始終了年月より）                
    for ($i=0; $i<$rows_man; $i++) {
        switch ($item[$i]) {
            case 'カプラ':                                                     // 対象グループがカプラの時
                $assist_c = $assistance_time[$i];
                $worker_time_c = $worker_time[$i];
                $total_worker_time += $worker_time[$i];                        // 合計作業時間計算
                $total_assistance_time += $assistance_time[$i];                // 合計応援時間計算
                // 176 経費全体取得
                $query_exp = sprintf("SELECT sum(act_monthly) FROM act_summary WHERE act_yymm>=%d AND act_yymm<=%d AND act_id=176", $acts_ym, $acte_ym);
                $res_exp = array();
                $rows_exp = getResult($query_exp, $res_exp);
                $expense_c = $res_exp[0][0];
                // 176 熱）電気取得
                $query_exp2 = sprintf("SELECT sum(act_monthly) FROM act_summary WHERE act_yymm>=%d AND act_yymm<=%d AND act_id=176 AND actcod=7531 AND aucod=10", $acts_ym, $acte_ym);
                $res_exp2 = array();
                $rows_exp2 = getResult($query_exp2, $res_exp2);
                if ($res_exp2[0][0] != '') {
                    $expense_c = $expense_c - $res_exp2[0][0];
                }
                // 522 経費全体取得
                $query_exp = sprintf("SELECT sum(act_monthly) FROM act_summary WHERE act_yymm>=%d AND act_yymm<=%d AND act_id=522", $acts_ym, $acte_ym);
                $res_exp = array();
                $rows_exp = getResult($query_exp, $res_exp);
                $expense_c += $res_exp[0][0];
                // 522 熱）電気取得
                $query_exp2 = sprintf("SELECT sum(act_monthly) FROM act_summary WHERE act_yymm>=%d AND act_yymm<=%d AND act_id=522 AND actcod=7531 AND aucod=10", $acts_ym, $acte_ym);
                $res_exp2 = array();
                $rows_exp2 = getResult($query_exp2, $res_exp2);
                if ($res_exp2[0][0] != '') {
                    $expense_c = $expense_c - $res_exp2[0][0];
                }
                // 523 経費全体取得
                $query_exp = sprintf("SELECT sum(act_monthly) FROM act_summary WHERE act_yymm>=%d AND act_yymm<=%d AND act_id=523", $acts_ym, $acte_ym);
                $res_exp = array();
                $rows_exp = getResult($query_exp, $res_exp);
                $expense_c += $res_exp[0][0];
                // 523 熱）電気取得
                $query_exp2 = sprintf("SELECT sum(act_monthly) FROM act_summary WHERE act_yymm>=%d AND act_yymm<=%d AND act_id=523 AND actcod=7531 AND aucod=10", $acts_ym, $acte_ym);
                $res_exp2 = array();
                $rows_exp2 = getResult($query_exp2, $res_exp2);
                if ($res_exp2[0][0] != '') {
                    $expense_c = $expense_c - $res_exp2[0][0];
                }
                // 525 経費全体取得
                $query_exp = sprintf("SELECT sum(act_monthly) FROM act_summary WHERE act_yymm>=%d AND act_yymm<=%d AND act_id=525", $acts_ym, $acte_ym);
                $res_exp = array();
                $rows_exp = getResult($query_exp, $res_exp);
                $expense_c += $res_exp[0][0];
                // 525 熱）電気取得
                $query_exp2 = sprintf("SELECT sum(act_monthly) FROM act_summary WHERE act_yymm>=%d AND act_yymm<=%d AND act_id=525 AND actcod=7531 AND aucod=10", $acts_ym, $acte_ym);
                $res_exp2 = array();
                $rows_exp2 = getResult($query_exp2, $res_exp2);
                if ($res_exp2[0][0] != '') {
                    $expense_c = $expense_c - $res_exp2[0][0];
                }
                // 510 経費全体取得
                //$query_exp = sprintf("SELECT sum(act_monthly) FROM act_summary WHERE act_yymm>=%d AND act_yymm<=%d AND act_id=510", $acts_ym, $acte_ym);
                //$res_exp = array();
                //$rows_exp = getResult($query_exp, $res_exp);
                //$expense_c += $res_exp[0][0];
                // 510 熱）電気取得
                //$query_exp2 = sprintf("SELECT sum(act_monthly) FROM act_summary WHERE act_yymm>=%d AND act_yymm<=%d AND act_id=510 AND actcod=7531 AND aucod=10", $acts_ym, $acte_ym);
                //$res_exp2 = array();
                //$rows_exp2 = getResult($query_exp2, $res_exp2);
                //if ($res_exp2[0][0] != '') {
                //    $expense_c = $expense_c - $res_exp2[0][0];
                //}
                // 571 経費全体取得
                //$query_exp = sprintf("SELECT sum(act_monthly) FROM act_summary WHERE act_yymm>=%d AND act_yymm<=%d AND act_id=571", $acts_ym, $acte_ym);
                //$res_exp = array();
                //$rows_exp = getResult($query_exp, $res_exp);
                //$expense_c += $res_exp[0][0];
                // 571 熱）電気取得
                //$query_exp2 = sprintf("SELECT sum(act_monthly) FROM act_summary WHERE act_yymm>=%d AND act_yymm<=%d AND act_id=571 AND actcod=7531 AND aucod=10", $acts_ym, $acte_ym);
                //$res_exp2 = array();
                //$rows_exp2 = getResult($query_exp2, $res_exp2);
                //if ($res_exp2[0][0] != '') {
                //    $expense_c = $expense_c - $res_exp2[0][0];
                //}
                break;
            case 'リニア':                                                     //対象グループがリニアの時
                $assist_l = $assistance_time[$i];
                $worker_time_l = $worker_time[$i];
                $total_worker_time += $worker_time[$i];                        // 合計作業時間計算
                $total_assistance_time += $assistance_time[$i];                // 合計応援時間計算
                // 175 経費全体取得
                $query_exp = sprintf("SELECT sum(act_monthly) FROM act_summary WHERE act_yymm>=%d AND act_yymm<=%d AND act_id=175", $acts_ym, $acte_ym);
                $res_exp = array();
                $rows_exp = getResult($query_exp, $res_exp);
                $expense_l = $res_exp[0][0];
                // 175 熱）電気取得
                $query_exp2 = sprintf("SELECT sum(act_monthly) FROM act_summary WHERE act_yymm>=%d AND act_yymm<=%d AND act_id=175 AND actcod=7531 AND aucod=10", $acts_ym, $acte_ym);
                $res_exp2 = array();
                $rows_exp2 = getResult($query_exp2, $res_exp2);
                if ($res_exp2[0][0] != '') {
                    $expense_l = $expense_l - $res_exp2[0][0];
                }
                // 560 経費全体取得
                $query_exp = sprintf("SELECT sum(act_monthly) FROM act_summary WHERE act_yymm>=%d AND act_yymm<=%d AND act_id=560", $acts_ym, $acte_ym);
                $res_exp = array();
                $rows_exp = getResult($query_exp, $res_exp);
                $expense_l += $res_exp[0][0];
                // 560 熱）電気取得
                $query_exp2 = sprintf("SELECT sum(act_monthly) FROM act_summary WHERE act_yymm>=%d AND act_yymm<=%d AND act_id=560 AND actcod=7531 AND aucod=10", $acts_ym, $acte_ym);
                $res_exp2 = array();
                $rows_exp2 = getResult($query_exp2, $res_exp2);
                if ($res_exp2[0][0] != '') {
                    $expense_l = $expense_l - $res_exp2[0][0];
                }
                // 551 経費全体取得
                $query_exp = sprintf("SELECT sum(act_monthly) FROM act_summary WHERE act_yymm>=%d AND act_yymm<=%d AND act_id=551", $acts_ym, $acte_ym);
                $res_exp = array();
                $rows_exp = getResult($query_exp, $res_exp);
                $expense_l += $res_exp[0][0];
                // 551 熱）電気取得
                $query_exp2 = sprintf("SELECT sum(act_monthly) FROM act_summary WHERE act_yymm>=%d AND act_yymm<=%d AND act_id=551 AND actcod=7531 AND aucod=10", $acts_ym, $acte_ym);
                $res_exp2 = array();
                $rows_exp2 = getResult($query_exp2, $res_exp2);
                if ($res_exp2[0][0] != '') {
                    $expense_l = $expense_l - $res_exp2[0][0];
                }
                // 572 経費全体取得
                $query_exp = sprintf("SELECT sum(act_monthly) FROM act_summary WHERE act_yymm>=%d AND act_yymm<=%d AND act_id=572", $acts_ym, $acte_ym);
                $res_exp = array();
                $rows_exp = getResult($query_exp, $res_exp);
                $expense_l += $res_exp[0][0];
                // 572 熱）電気取得
                $query_exp2 = sprintf("SELECT sum(act_monthly) FROM act_summary WHERE act_yymm>=%d AND act_yymm<=%d AND act_id=572 AND actcod=7531 AND aucod=10", $acts_ym, $acte_ym);
                $res_exp2 = array();
                $rows_exp2 = getResult($query_exp2, $res_exp2);
                if ($res_exp2[0][0] != '') {
                    $expense_l = $expense_l - $res_exp2[0][0];
                }
                
                //if ($end_ym < 201012) {
                //    $query_exp = sprintf("SELECT sum(act_monthly)-(SELECT sum(act_monthly) FROM act_summary WHERE act_yymm>=%d AND act_yymm<=%d AND act_id=559 AND actcod=7531 AND aucod=10) FROM act_summary WHERE act_yymm>=%d AND act_yymm<=%d AND act_id=559", $acts_ym, $acte_ym, $acts_ym, $acte_ym);
                //    $res_exp = array();
                //    $rows_exp = getResult($query_exp, $res_exp);
                //    $expense_l += $res_exp[0][0];
                //}
                break;
            default:
                break;
        }
    }
    $total_expense = $expense_c + $expense_l;                                  // 合計製造経費計算
    
    $result->add('total_assistance_time', $total_assistance_time);
    $result->add('total_worker_time', $total_worker_time);
    $result->add('worker_time_c', $worker_time_c);
    $result->add('worker_time_l', $worker_time_l);    
    $result->add('assist_c', $assist_c);
    $result->add('assist_l', $assist_l);
    $result->add('total_expense', $total_expense);
    $result->add('expense_c', $expense_c);
    $result->add('expense_l', $expense_l);
}

////////////////// 賃率照会画面のHTMLの作成
function getViewHTMLbody($request, $menu, $result)
{
    $listTable = '';
    $listTable .= "<!DOCTYPE HTML PUBLIC '-//W3C//DTD HTML 4.01 Transitional//EN'>\n";
    $listTable .= "<html>\n";
    $listTable .= "<head>\n";
    $listTable .= "<meta http-equiv='Content-Type' content='text/html; charset=EUC-JP'>\n";
    $listTable .= "<meta http-equiv='Content-Style-Type' content='text/css'>\n";
    $listTable .= "<meta http-equiv='Content-Script-Type' content='text/javascript'>\n";
    $listTable .= "<script type='text/javascript' src='../assemblyRate_reference.js'></script>\n";
    $listTable .= "<link rel='stylesheet' href='../assemblyRate_reference.css' type='text/css'>\n";
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
    $res_g                 = $result->get_array2('res_g');
    $group_no              = $result->get_array2('group_no');
    $group_lease_sen       = $result->get_array2('group_lease_sen');
    $group_capital_sen     = $result->get_array2('group_capital_sen');
    $group_repair_sen      = $result->get_array2('group_repair_sen');
    $group_expenses        = $result->get_array2('group_expenses');
    $group_time            = $result->get_array2('group_time');
    $group_machine_rate    = $result->get_array2('group_machine_rate');
    $before_machine_rate   = $result->get_array2('before_machine_rate');
    $rate                  = $result->get_array2('rate');
    $worker_figure_s       = $result->get_array2('worker_figure_s');
    $worker_figure_p       = $result->get_array2('worker_figure_p');
    $man_expenses          = $result->get_array2('man_expenses');
    $standard_rate         = $result->get_array2('standard_rate');
    $group_mac_no          = $result->get_array2('group_mac_no');
    
    $direct_expenses       = $result->get('direct_expenses');
    $total_rate            = $result->get('total_rate');
    $expense_c_sen         = $result->get('expense_c_sen');
    $expense_l_sen         = $result->get('expense_l_sen');
    $assist_expense        = $result->get('assist_expense');
    $total_keihi           = $result->get('total_keihi');
    $total_keihi_cut       = $result->get('total_keihi_cut');
    $total_keihi_c         = $result->get('total_keihi_c');
    $total_keihi_l         = $result->get('total_keihi_l');
    $labor_rate            = $result->get('labor_rate');
    $labor_rate_c          = $result->get('labor_rate_c');
    $labor_rate_l          = $result->get('labor_rate_l');
    $total_assemble        = $result->get('total_assemble');
    
    $total_assistance_time = $result->get('total_assistance_time');
    $total_worker_time     = $result->get('total_worker_time');
    $assist_expense_c      = $result->get('assist_expense_c');
    $assist_expense_l      = $result->get('assist_expense_l');
    $direct_expenses_c     = $result->get('direct_expenses_c');
    $direct_expenses_l     = $result->get('direct_expenses_l');
    $man_expenses_c        = $result->get('man_expenses_c');
    $man_expenses_l        = $result->get('man_expenses_l');
    $keihi_cut_c           = $result->get('keihi_cut_c');
    $keihi_cut_l           = $result->get('keihi_cut_l');
    $assemble_c            = $result->get('assemble_c');
    $assemble_l            = $result->get('assemble_l');
    $assist_c              = $result->get('assist_c');
    $assist_l              = $result->get('assist_l');
    $before_labor_rate_t   = $result->get('before_labor_rate_t');
    $before_labor_rate_c   = $result->get('before_labor_rate_c');
    $before_labor_rate_l   = $result->get('before_labor_rate_l');
    $worker_time_c         = $result->get('worker_time_c');
    $worker_time_l         = $result->get('worker_time_l');
    
    $listTable .= "    <table x:str border=0 cellpadding=0 cellspacing=0 width=664 class=border-none style='border-collapse:collapse;table-layout:fixed;width:559pt'>\n";
    $listTable .= "        <col class=border-none width=16 style='mso-width-source:userset;mso-width-alt:512;width:0pt'>\n";
    $listTable .= "        <col class=border-none width=16 style='mso-width-source:userset;mso-width-alt:512;width:49pt'>\n";
    $listTable .= "        <col class=border-none width=88 style='mso-width-source:userset;mso-width-alt:2816;width:50pt'>\n";
    $listTable .= "        <col class=border-none width=32 style='mso-width-source:userset;mso-width-alt:1024;width:35pt'>\n";
    $listTable .= "        <col class=border-none width=69 style='mso-width-source:userset;mso-width-alt:2208;width:50pt'>\n";
    $listTable .= "        <col class=border-none width=81 style='mso-width-source:userset;mso-width-alt:2592;width:50pt'>\n";
    $listTable .= "        <col class=border-none width=68 style='mso-width-source:userset;mso-width-alt:2176;width:38pt'>\n";
    $listTable .= "        <col class=border-none width=43 style='mso-width-source:userset;mso-width-alt:1376;width:50pt'>\n";
    $listTable .= "        <col class=border-none width=76 style='mso-width-source:userset;mso-width-alt:2432;width:47pt'>\n";
    $listTable .= "        <col class=border-none width=89 style='mso-width-source:userset;mso-width-alt:2848;width:68pt'>\n";
    $listTable .= "        <col class=border-none width=89 style='mso-width-source:userset;mso-width-alt:2848;width:62pt'>\n";
    $listTable .= "        <col class=border-none width=102 style='mso-width-source:userset;mso-width-alt:3264;width:63pt'>\n";
    $listTable .= "        <tr height=20 style='mso-height-source:userset;height:15.0pt'>\n";
    $listTable .= "            <td height=20 class=border-none width=16 style='height:15.0pt;width:12pt'></td>\n";
    $listTable .= "            <br>\n";
    $listTable .= "            <td colspan=4 class=border-none width=273><font size = 4><B>組立自動機の賃率計算</B></font></td>\n";
    $listTable .= "            <td colspan=7 class=border-none><B><font size = 4>". format_date6_ki($end_ym) . "迄実績（{$str_m} 月〜 {$end_m} 月）</font></B></td>\n";
    $listTable .= "        </tr>\n";
    $listTable .= "        <tr height=20 style='mso-height-source:userset;height:15.0pt'>\n";
    $listTable .= "            <td class=border-none colspan=12 align=right>円</td>\n";
    $listTable .= "        </tr>\n";
    $listTable .= "        <tr height=20 style='mso-height-source:userset;height:15.0pt'>\n";
    $listTable .= "            <td height=20 class=border-none style='height:15.0pt'></td>\n";
    $listTable .= "            <td colspan=2 class=border-on align=center style='border-bottom:none'>グループ名</td>\n";
    $listTable .= "            <td class=border-on rowspan=2 style='border-bottom:none'>　</td>\n";
    $listTable .= "            <td class=border-on align=right style='border-bottom:none'>千円</td>\n";
    $listTable .= "            <td class=border-on style='border-bottom:none'>実績　分</td>\n";
    $listTable .= "            <td class=border-on align=right style='border-bottom:none'>円</td>\n";
    $listTable .= "            <td class=pt9 colspan=2>付帯作業者経費(千円)</td>\n";
    $listTable .= "            <td class=machine_rate_title align=center style='border-top:1.0pt solid windowtext'><B>". format_date6_ki($end_ym) . "</B></td>\n";
    $listTable .= "            <td class=border-on align=center style='border-bottom:none'>". format_ki_before($end_ym) . "</td>\n";
    $listTable .= "            <td class=border-on align=center style='border-bottom:none'>". format_date6_term($end_ym) . "</td>\n";
    $listTable .= "        </tr>\n";
    $listTable .= "        <tr height=20 style='mso-height-source:userset;height:15.0pt'>\n";
    $listTable .= "            <td height=20 class=border-none style='height:15.0pt'></td>\n";
    $listTable .= "            <td colspan=2 class=border-on align=center style='border-bottom:none;border-top:none'>機械番号</td>\n";
    $listTable .= "            <td class=border-on align=center style='border-bottom:none;border-top:none'>直接経費</td>\n";
    $listTable .= "            <td class=border-on align=center style='border-bottom:none;border-top:none'>稼働時間</td>\n";
    $listTable .= "            <td class=border-on align=center style='border-bottom:none;border-top:none'>賃率</td>\n";
    $listTable .= "            <td class=pt9 colspan=2 style='border-top:none'>作業者数(社員/パート)</td>\n";
    $listTable .= "            <td class=machine_rate_title align=center><B>実際賃率</B></td>\n";
    $listTable .= "            <td class=border-on align=center style='border-bottom:none;border-top:none'>実際賃率</td>\n";
    $listTable .= "            <td class=border-on align=center style='border-bottom:none;border-top:none'>標準賃率</td>\n";
    $listTable .= "        </tr>\n";
    for ($r=0; $r<$result->get('rows_g'); $r++) {
        $g_num = $res_g[$r][0];
        $listTable .= "    <tr>\n";
        for ($i=0; $i<$result->get('rows_g'); $i++) {         // レコード数分繰返し
            if ($g_num == $group_no[$i]) {
                $listTable .= "<tr height=20 style='mso-height-source:userset;height:15.0pt'>\n";
                $listTable .= "    <td height=20 class=border-none style='height:15.0pt'></td>\n";
                $listTable .= "    <td rowspan=1 colspan=2 class=border-on align=center valign=middle style='border-bottom:none'>". format_number_name($group_no[$i], $res_g, $result->get('rows_g')) . "</td>\n";
                $listTable .= "    <td class=border-on style='border-bottom:none'><center>賃</center></td>\n";
                $listTable .= "    <td class=border-on align=right style='border-bottom:none'>". number_format($group_lease_sen[$i], 0) . "</td>\n";
                $listTable .= "    <td class=border-on rowspan=3 style='border-bottom:none'>　</td>\n";
                $listTable .= "    <td class=border-on rowspan=3 style='border-bottom:none'>　</td>\n";
                $listTable .= "    <td class=border-on rowspan=3 style='border-right:none;border-bottom:none'>　</td>\n";
                $listTable .= "    <td class=border-on align=right style='border-left:none;border-bottom:none'>". number_format($man_expenses[$i], 0) . "</td>\n";
                $listTable .= "    <td class=machine_rate rowspan=3 style='border-top:1.0pt solid windowtext'>　</td>\n";
                $listTable .= "    <td class=border-on rowspan=3 style='border-bottom:none'>　</td>\n";
                $listTable .= "    <td class=border-on rowspan=3 style='border-bottom:none'>　</td>\n";
                $listTable .= "</tr>\n";
                $listTable .= "<tr height=20 style='mso-height-source:userset;height:15.0pt'>\n";
                $listTable .= "    <td height=20 class=border-none style='height:15.0pt'></td>\n";
                if (isset($group_mac_no[$i][0])) {
                    $listTable .= "<td colspan=2 class=border-on align=center style='border-top:none;border-bottom:none'>{$group_mac_no[$i][0]}</td>\n";
                } else {
                    $listTable .= "<td colspan=2 class=border-on align=center style='border-top:none;border-bottom:none'></td>\n";    
                }
                $listTable .= "    <td class=border-none><center>減</center></td>\n";
                $listTable .= "    <td class=border-on align=right style='border-top:none;border-bottom:none'>". number_format($group_capital_sen[$i], 0) . "</td>\n";
                $listTable .= "    <td class=border-none style='border-right:1.0pt solid windowtext'></td>\n";
                $listTable .= "</tr>\n";
                $listTable .= "<tr height=20 style='mso-height-source:userset;height:15.0pt'>\n";
                $listTable .= "    <td height=20 class=border-none style='height:15.0pt'></td>\n";
                if (isset($group_mac_no[$i][1])) {
                    $listTable .= "<td colspan=2 class=border-on align=center style='border-top:none;border-bottom:none'>{$group_mac_no[$i][1]}</td>\n";
                } else {
                    $listTable .= "<td colspan=2 class=border-on align=center style='border-top:none;border-bottom:none'></td>\n";    
                }
                    $listTable .= "<td class=border-none><center>修</center></td>\n";
                    $listTable .= "<td class=border-on align=right style='border-top:none;border-bottom:none'>". number_format($group_repair_sen[$i], 0) . "</td>\n";
                    $listTable .= "<td class=border-none style='border-right:1.0pt solid windowtext'></td>\n";
                $listTable .= "</tr>\n";
                $listTable .= "<tr height=20 style='mso-height-source:userset;height:15.0pt'>\n";
                $listTable .= "    <td height=20 class=border-none style='height:15.0pt'></td>\n";
                if (isset($group_mac_no[$i][2])) {
                    $listTable .= "<td colspan=2 class=border-on align=center style='border-top:none;border-bottom:none'>{$group_mac_no[$i][2]}</td>\n";
                } else {
                    $listTable .= "<td colspan=2 class=border-on align=center style='border-top:none;border-bottom:none'></td>\n";    
                }
                $listTable .= "    <td class=border-on style='border-top:.5pt dotted windowtext;border-bottom:none'><center>計</center></td>\n";
                $listTable .= "    <td class=border-on align=right style='border-top:.5pt dotted windowtext;border-bottom:none'>". number_format($group_expenses[$i], 0) . "</td>\n";
                $listTable .= "    <td class=border-on align=right style='border-top:none;border-bottom:none'>". number_format($group_time[$i], 0) . "</td>\n";
                $listTable .= "    <td class=border-on align=right style='border-top:none;border-bottom:none'>". number_format($rate[$i], 2) . "</td>\n";
                $listTable .= "    <td class=border-none>". number_format($worker_figure_s[$i], 2) . "人 /</td>\n";
                $listTable .= "    <td class=border-none>". number_format($worker_figure_p[$i], 2) . "人</td>\n";
                if ($group_machine_rate[$i] == 0) {
                    $listTable .= "<td class=machine_rate align=right><B>---</B></td>\n";
                } else {
                    $listTable .= "<td class=machine_rate align=right><B>". number_format($group_machine_rate[$i], 2) . "</B></td>\n";
                }
                if ($before_machine_rate[$r] == 0 || $before_machine_rate[$r] == "") {
                    $listTable .= "<td class=border-on align=right style='border-top:none;border-bottom:none'>---</td>\n";
                } else {
                    $listTable .= "<td class=border-on align=right style='border-top:none;border-bottom:none'>". number_format($before_machine_rate[$r], 2) . "</td>\n";
                }
                if ($standard_rate[$i] == 0) {
                    $listTable .= "<td class=border-on align=right style='border-top:none;border-bottom:none'>---</td>\n";
                } else {
                    $listTable .= "<td class=border-on align=right style='border-top:none;border-bottom:none'>". number_format($standard_rate[$i], 2) . "</td>\n";
                }
                $listTable .= "</tr>\n";
            }
        }
            $listTable .= "</tr>\n";
    }
    $listTable .= "        <tr height=20 style='mso-height-source:userset;height:15.0pt'>\n";
    $listTable .= "            <td height=20 class=border-none style='height:15.0pt'></td>\n";
    $listTable .= "            <td rowspan=4 colspan=2 class=border-on align=center valign=middle>合計</td>\n";
    $listTable .= "            <td class=border-on style='border-bottom:none'><center>賃</center></td>\n";
    $listTable .= "            <td class=border-on align=right style='border-bottom:none'>". number_format($request->get('total_lease'), 0) . "</td>\n";
    $listTable .= "            <td class=border-on rowspan=3 style='border-bottom:none'>　</td>\n";
    $listTable .= "            <td class=border-on rowspan=3 style='border-bottom:none'>　</td>\n";
    $listTable .= "            <td class=border-on rowspan=4 style='border-right:none'>　</td>\n";
    $listTable .= "            <td class=man_expense align=right style='border-left:none;border-top:1.0pt solid windowtext;border-right:1.0pt solid windowtext'>". number_format($result->get('total_man_expenses'), 0) . "</td>\n";
    $listTable .= "            <td class=border-on rowspan=4 colspan=3>　</td>\n";
    $listTable .= "        </tr>\n";
    $listTable .= "        <tr height=20 style='mso-height-source:userset;height:15.0pt'>\n";
    $listTable .= "            <td height=20 class=border-none style='height:15.0pt'></td>\n";
    $listTable .= "            <td class=border-none><center>減</center></td>\n";
    $listTable .= "            <td class=border-on align=right style='border-top:none;border-bottom:none'>". number_format($request->get('total_capital'), 0) . "</td>\n";
    $listTable .= "            <td class=border-none rowspan=2 style='border-right:1.0pt solid windowtext'>　</td>\n";
    $listTable .= "        </tr>\n";
    $listTable .= "        <tr height=20 style='mso-height-source:userset;height:15.0pt'>\n";
    $listTable .= "            <td height=20 class=border-none style='height:15.0pt'></td>\n";
    $listTable .= "            <td class=border-none><center>修</center></td>\n";
    $listTable .= "            <td class=border-on align=right style='border-top:none;border-bottom:none'>". number_format($request->get('total_repair'), 0) . "</td>\n";
    $listTable .= "        </tr>\n";
    $listTable .= "        <tr height=20 style='mso-height-source:userset;height:15.0pt'>\n";
    $listTable .= "            <td height=20 class=border-none style='height:15.0pt'></td>\n";
    $listTable .= "            <td class=border-on align=center style='border-top:.5pt dotted windowtext'>計</td>\n";
    $listTable .= "            <td class=direct_expense align=right align=right style='border-top:.5pt dotted black;border-bottom:1.0pt solid windowtext;border-left:1.0pt solid windowtext'>". number_format($direct_expenses, 0) . "</td>\n";
    $listTable .= "            <td class=border-on align=right style='border-top:none'>". number_format($request->get('total_time'), 0) . "</td>\n";
    $listTable .= "            <td class=border-on align=right style='border-top:none'>". number_format($total_rate, 2) . "</td>\n";
    $listTable .= "            <td class=border-on style='border-top:none;border-left:none'>　</td>\n";
    $listTable .= "        </tr>\n";
    $listTable .= "        <tr class='pagebreak'></tr>\n";
    $listTable .= "        <tr height=30 style='height:20.0pt'>\n";
    $listTable .= "        <tr height=20 style='mso-height-source:userset;height:15.0pt'>\n";
    $listTable .= "            <td height=20 class=border-none width=16 style='height:15.0pt;width:12pt'></td>\n";
    $listTable .= "            <td colspan=4 class=border-none width=273><font size = 4><B>組立手作業の賃率計算</B></font></td>\n";
    $listTable .= "            <td colspan=7 class=border-none><B><font size = 4>". format_date6_ki($end_ym) . "迄実績（{$str_m} 月〜 {$end_m} 月）</font></B></td>\n";
    $listTable .= "        </tr>\n";
    $listTable .= "        <tr height=20 style='mso-height-source:userset;height:15.0pt'>\n";
    $listTable .= "        </tr>\n";
    $listTable .= "        <tr height=20 style='mso-height-source:userset;height:15.0pt'>\n";
    $listTable .= "            <td height=20 class=border-none style='height:15.0pt'></td>\n";
    $listTable .= "            <td class=machine_rate_title style='border-left:none'>全体</td>\n";
    $listTable .= "        </tr>\n";
    $listTable .= "        <tr height=20 style='mso-height-source:userset;height:15.0pt'>\n";
    $listTable .= "            <td height=20 class=border-none style='height:15.0pt'></td>\n";
    $listTable .= "            <td class=border-none colspan=3>Ｃ組立経費(千円)</td>\n";
    $listTable .= "            <td class=border-none align=right>". number_format($expense_c_sen, 0) . "</td>\n";
    $listTable .= "        </tr>\n";
    $listTable .= "        <tr height=20 style='mso-height-source:userset;height:15.0pt'>\n";
    $listTable .= "            <td height=20 class=border-none style='height:15.0pt'></td>\n";
    $listTable .= "            <td class=border-none colspan=3>Ｌ組立経費</td>\n";
    $listTable .= "            <td class=border-none align=right>". number_format($expense_l_sen, 0) . "</td>\n";
    $listTable .= "        </tr>\n";
    $listTable .= "        <tr height=20 style='mso-height-source:userset;height:15.0pt'>\n";
    $listTable .= "            <td height=20 class=border-none style='height:15.0pt'></td>\n";
    $listTable .= "            <td class=border-none colspan=3>横川応援経費加算</td>\n";
    $listTable .= "            <td class=assist align=right>". number_format($assist_expense, 0) . "</td>\n";
    $listTable .= "        </tr>\n";
    $listTable .= "        <tr height=20 style='mso-height-source:userset;height:15.0pt'>\n";
    $listTable .= "            <td height=20 class=border-none style='height:15.0pt'></td>\n";
    $listTable .= "            <td class=border-none colspan=3 align=right style='border-top:1.0pt solid windowtext'>計</td>\n";
    $listTable .= "            <td class=border-none align=right>". number_format($total_keihi, 0) . "</td>\n";
    $listTable .= "        </tr>\n";
    $listTable .= "        <tr height=20 style='mso-height-source:userset;height:15.0pt'>\n";
    $listTable .= "            <td height=20 class=border-none style='height:15.0pt'></td>\n";
    $listTable .= "            <td class=border-none colspan=3>自動組立機直接経費減額</td>\n";
    $listTable .= "            <td class=direct_expense align=right>". number_format(-$direct_expenses, 0) . "</td>\n";
    $listTable .= "        </tr>\n";
    $listTable .= "        <tr height=20 style='mso-height-source:userset;height:15.0pt'>\n";
    $listTable .= "            <td height=20 class=border-none style='height:15.0pt'></td>\n";
    $listTable .= "            <td class=border-none colspan=3>自動組立機作業経費減額</td>\n";
    $listTable .= "            <td class=man_expense align=right>". number_format(-$result->get('total_man_expenses'), 0) . "</td>\n";
    $listTable .= "            <td></td>\n";
    $listTable .= "            <td></td>\n";
    $listTable .= "            <td colspan=3 class=labor_rate_title align=center><B>". format_date6_ki($end_ym) . "実際賃率</B></td>\n";
    $listTable .= "        </tr>\n";
    $listTable .= "        <tr height=20 style='mso-height-source:userset;height:15.0pt'>\n";
    $listTable .= "            <td height=20 class=border-none style='height:15.0pt'></td>\n";
    $listTable .= "            <td class=border-none align=right colspan=3 style='border-top:1.0pt solid windowtext'>合計</td>\n";
    $listTable .= "            <td class=border-none align=right style='border-top:1.0pt solid windowtext'>". number_format($total_keihi_cut, 0) . "</td>\n";
    $listTable .= "            <td class=border-none align=center>÷</td>\n";
    $listTable .= "            <td class=border-none>Ａ＝</td>\n";
    $listTable .= "            <td class=labor_rate align=right>". number_format($labor_rate, 2) . "</td>\n";
    $listTable .= "            <td class=border-none>円／分</td>\n";
    $listTable .= "        </tr>\n";
    $listTable .= "        <tr height=20 style='mso-height-source:userset;height:15.0pt'>\n";
    $listTable .= "        </tr>\n";
    $listTable .= "        <tr height=20 style='mso-height-source:userset;height:15.0pt'>\n";
    $listTable .= "            <td height=20 class=border-none style='height:15.0pt'></td>\n";
    $listTable .= "            <td class=border-none colspan=3>組立作業時間計</td>\n";
    $listTable .= "            <td class=assemble align=right>". number_format($total_worker_time, 0) . "</td>\n";
    $listTable .= "            <td class=border-none>Ａ</td>\n";
    /*
    $listTable .= "            <td class=assemble align=right>". number_format($total_assemble, 0) . "</td>\n";
    $listTable .= "            <td class=border-none>分</td>\n";
    */
    $listTable .= "            <td></td>\n";
    $listTable .= "            <td colspan=3 class=border-on align=center>". format_date6_ki($request->get('before_ym')) . "実際賃率</td>\n";
    $listTable .= "        </tr>\n";
    $listTable .= "        <tr height=20 style='mso-height-source:userset;height:15.0pt'>\n";
    /*
    $listTable .= "            <td height=20 class=border-none style='height:15.0pt'></td>\n";
    $listTable .= "            <td class=border-none colspan=3>横川応援時間加算</td>\n";
    $listTable .= "            <td class=assist align=right>". number_format($total_assistance_time, 0) . "</td>\n";
    $listTable .= "            <td class=border-none>分</td>\n";
    $listTable .= "            <td></td>\n";
    */
    $listTable .= "            <td height=20 style='height:15.0pt'></td>\n";
    $listTable .= "            <td align=right colspan=3 style='border-top:1.0pt solid windowtext'>　</td>\n";
    $listTable .= "            <td align=right style='border-top:1.0pt solid windowtext'>　</td>\n";
    $listTable .= "            <td>　</td>\n";
    $listTable .= "            <td></td>\n";
    if ($before_labor_rate_t == "") {
        $listTable .= "        <td class=border-on align=right>---</td>\n";
    } else {
        $listTable .= "        <td class=border-on align=right>". number_format($before_labor_rate_t, 2) . "</td>\n";
    }
    $listTable .= "            <td class=border-on style='border-right:none;border-bottom:none'>円／分</td>\n";
    $listTable .= "        </tr>\n";
    /*
    $listTable .= "        <tr height=20 style='mso-height-source:userset;height:15.0pt'>\n";
    $listTable .= "            <td height=20 class=border-none style='height:15.0pt'></td>\n";
    $listTable .= "            <td class=border-none align=right colspan=3 style='border-top:1.0pt solid windowtext'>合計</td>\n";
    $listTable .= "            <td class=border-none align=right>". number_format($total_worker_time, 0) . "</td>\n";
    $listTable .= "            <td class=border-none>Ａ</td>\n";
    $listTable .= "        </tr>\n";
    */
    $listTable .= "        <tr height=20 style='height:15.0pt'>\n";
    $listTable .= "        </tr>\n";
    $listTable .= "        <tr height=20 style='mso-height-source:userset;height:15.0pt'>\n";
    $listTable .= "           <td height=20 class=border-none style='height:15.0pt'></td>\n";
    $listTable .= "            <td class=machine_rate_title style='border-left:none'>カプラ</td>\n";
    $listTable .= "        </tr>\n";
    $listTable .= "        <tr height=20 style='mso-height-source:userset;height:15.0pt'>\n";
    $listTable .= "            <td height=20 class=border-none style='height:15.0pt'></td>\n";
    $listTable .= "            <td class=border-none colspan=3>Ｃ組立経費(千円)</td>\n";
    $listTable .= "            <td class=border-none align=right>". number_format($expense_c_sen, 0) . "</td>\n";
    $listTable .= "        </tr>\n";
    $listTable .= "        <tr height=20 style='mso-height-source:userset;height:15.0pt'>\n";
    $listTable .= "            <td height=20 class=border-none style='height:15.0pt'></td>\n";
    $listTable .= "            <td class=border-none colspan=2>Ｌ組立経費</td>\n";
    $listTable .= "        </tr>\n";
    $listTable .= "        <tr height=20 style='mso-height-source:userset;height:15.0pt'>\n";
    $listTable .= "            <td height=20 class=border-none style='height:15.0pt'></td>\n";
    $listTable .= "            <td class=border-none colspan=3>横川応援経費加算</td>\n";
    $listTable .= "            <td class=assist align=right>". number_format($assist_expense_c, 0) . "</td>\n";
    $listTable .= "        </tr>\n";
    $listTable .= "        <tr height=20 style='mso-height-source:userset;height:15.0pt'>\n";
    $listTable .= "            <td height=20 class=border-none style='height:15.0pt'></td>\n";
    $listTable .= "            <td class=border-none align=right colspan=3 style='border-top:1.0pt solid windowtext'>計</td>\n";
    $listTable .= "            <td class=border-none align=right>". number_format($total_keihi_c, 0) . "</td>\n";
    $listTable .= "        </tr>\n";
    $listTable .= "        <tr height=20 style='mso-height-source:userset;height:15.0pt'>\n";
    $listTable .= "            <td height=20 class=border-none style='height:15.0pt'></td>\n";
    $listTable .= "            <td class=border-none colspan=3>自動組立機直接経費減額</td>\n";
    $listTable .= "            <td class=direct_expense align=right>". number_format(-$direct_expenses_c, 0) . "</td>\n";
    $listTable .= "        </tr>\n";
    $listTable .= "        <tr height=20 style='mso-height-source:userset;height:15.0pt'>\n";
    $listTable .= "            <td height=20 class=border-none style='height:15.0pt'></td>\n";
    $listTable .= "            <td class=border-none colspan=3>自動組立機作業経費減額</td>\n";
    $listTable .= "            <td class=man_expense align=right>". number_format(-$man_expenses_c, 0) . "</td>\n";
    $listTable .= "            <td></td>\n";
    $listTable .= "            <td></td>\n";
    $listTable .= "            <td colspan=3 class=labor_rate_title align=center><B>". format_date6_ki($end_ym) . "実際賃率</B></td>\n";
    $listTable .= "        </tr>\n";
    $listTable .= "        <tr height=20 style='mso-height-source:userset;height:15.0pt'>\n";
    $listTable .= "            <td height=20 class=border-none style='height:15.0pt'></td>\n";
    $listTable .= "            <td class=border-none align=right colspan=3 style='border-top:1.0pt solid windowtext'>合計</td>\n";
    $listTable .= "            <td class=border-none align=right style='border-top:1.0pt solid windowtext'>". number_format($keihi_cut_c, 0) . "</td>\n";
    $listTable .= "            <td class=border-none align=center>÷</td>\n";
    $listTable .= "            <td class=border-none>Ｂ＝</td>\n";
    $listTable .= "            <td class=labor_rate align=right>". number_format($labor_rate_c, 2) . "</td>\n";
    $listTable .= "            <td class=border-none>円／分</td>\n";
    $listTable .= "        </tr>\n";
    $listTable .= "        <tr height=20 style='mso-height-source:userset;height:15.0pt'>\n";
    $listTable .= "        </tr>\n";
    $listTable .= "        <tr height=20 style='mso-height-source:userset;height:15.0pt'>\n";
    $listTable .= "            <td height=20 class=border-none style='height:15.0pt'></td>\n";
    $listTable .= "            <td class=border-none colspan=3>組立作業時間計</td>\n";
    $listTable .= "            <td class=assemble align=right>". number_format($worker_time_c, 0) . "</td>\n";
    $listTable .= "            <td class=border-none>Ｂ</td>\n";
    /*
    $listTable .= "            <td class=assemble align=right>". number_format($assemble_c, 0) . "</td>\n";
    $listTable .= "            <td class=border-none>分</td>\n";
    */
    $listTable .= "            <td></td>\n";
    $listTable .= "            <td colspan=3 class=border-on align=center>". format_date6_ki($request->get('before_ym')) . "実際賃率</td>\n";
    $listTable .= "        </tr>\n";
    $listTable .= "        <tr height=20 style='mso-height-source:userset;height:15.0pt'>\n";
    $listTable .= "            <td height=20 style='height:15.0pt'></td>\n";
    $listTable .= "            <td colspan=3 style='border-top:1.0pt solid windowtext'>　</td>\n";
    $listTable .= "            <td align=right style='border-top:1.0pt solid windowtext'>　</td>\n";
    $listTable .= "            <td>　</td>\n";
    $listTable .= "            <td></td>\n";
    /*
    $listTable .= "            <td height=20 class=border-none style='height:15.0pt'></td>\n";
    $listTable .= "            <td class=border-none colspan=3>横川応援時間加算</td>\n";
    $listTable .= "            <td class=assist align=right>". number_format($assist_c, 0) . "</td>\n";
    $listTable .= "            <td class=border-none>分</td>\n";
    $listTable .= "            <td></td>\n";
    */
    if ($before_labor_rate_c == "") {
        $listTable .= "        <td class=border-on align=right>---</td>\n";
    } else {
        $listTable .= "        <td class=border-on align=right>". number_format($before_labor_rate_c, 2) . "</td>\n";
    }
    $listTable .= "            <td class=border-on style='border-right:none;border-bottom:none'>円／分</td>\n";
    $listTable .= "        </tr>\n";
    /*
    $listTable .= "        <tr height=20 style='mso-height-source:userset;height:15.0pt'>\n";
    $listTable .= "            <td height=20 class=border-none style='height:15.0pt'></td>\n";
    $listTable .= "            <td class=border-none align=right colspan=3 style='border-top:1.0pt solid windowtext'>合計</td>\n";
    $listTable .= "            <td class=border-none align=right>". number_format($worker_time_c, 0) . "</td>\n";
    $listTable .= "            <td class=border-none>Ｂ</td>\n";
    $listTable .= "        </tr>\n";
    */
    $listTable .= "        <tr height=20 style='height:15.0pt'>\n";
    $listTable .= "        </tr>\n";
    $listTable .= "        <tr height=20 style='mso-height-source:userset;height:15.0pt'>\n";
    $listTable .= "            <td height=20 class=border-none style='height:15.0pt'></td>\n";
    $listTable .= "            <td class=machine_rate_title style='border-left:none'>リニア</td>\n";
    $listTable .= "        </tr>\n";
    $listTable .= "        <tr height=20 style='mso-height-source:userset;height:15.0pt'>\n";
    $listTable .= "            <td height=20 class=border-none style='height:15.0pt'></td>\n";
    $listTable .= "            <td class=border-none colspan=3>Ｃ組立経費(千円)</td>\n";
    $listTable .= "        </tr>\n";
    $listTable .= "        <tr height=20 style='mso-height-source:userset;height:15.0pt'>\n";
    $listTable .= "            <td height=20 class=border-none style='height:15.0pt'></td>\n";
    $listTable .= "            <td class=border-none colspan=3>Ｌ組立経費</td>\n";
    $listTable .= "            <td class=border-none align=right>". number_format($expense_l_sen, 0) . "</td>\n";
    $listTable .= "        </tr>\n";
    $listTable .= "        <tr height=20 style='mso-height-source:userset;height:15.0pt'>\n";
    $listTable .= "            <td height=20 class=border-none style='height:15.0pt'></td>\n";
    $listTable .= "            <td class=border-none colspan=3>横川応援経費加算</td>\n";
    $listTable .= "            <td class=assist align=right>". number_format($assist_expense_l, 0) . "</td>\n";
    $listTable .= "        </tr>\n";
    $listTable .= "        <tr height=20 style='mso-height-source:userset;height:15.0pt'>\n";
    $listTable .= "            <td height=20 class=border-none style='height:15.0pt'></td>\n";
    $listTable .= "            <td class=border-none align=right colspan=3 style='border-top:1.0pt solid windowtext'>計</td>\n";
    $listTable .= "            <td class=border-none align=right>". number_format($total_keihi_l, 0) . "</td>\n";
    $listTable .= "        </tr>\n";
    $listTable .= "        <tr height=20 style='mso-height-source:userset;height:15.0pt'>\n";
    $listTable .= "            <td height=20 class=border-none style='height:15.0pt'></td>\n";
    $listTable .= "            <td class=border-none colspan=3>自動組立機直接経費減額</td>\n";
    $listTable .= "            <td class=direct_expense align=right>". number_format(-$direct_expenses_l, 0) . "</td>\n";
    $listTable .= "        </tr>\n";
    $listTable .= "        <tr height=20 style='mso-height-source:userset;height:15.0pt'>\n";
    $listTable .= "            <td height=20 class=border-none style='height:15.0pt'></td>\n";
    $listTable .= "            <td class=border-none colspan=3>自動組立機作業経費減額</td>\n";
    $listTable .= "            <td class=man_expense align=right>". number_format(-$man_expenses_l, 0) . "</td>\n";
    $listTable .= "            <td></td>\n";
    $listTable .= "            <td></td>\n";
    $listTable .= "            <td colspan=3 class=labor_rate_title align=center><B>". format_date6_ki($end_ym) . "実際賃率</B></td>\n";
    $listTable .= "        </tr>\n";
    $listTable .= "        <tr height=20 style='mso-height-source:userset;height:15.0pt'>\n";
    $listTable .= "            <td height=20 class=border-none style='height:15.0pt'></td>\n";
    $listTable .= "            <td class=border-none align=right colspan=3 style='border-top:1.0pt solid windowtext'>合計</td>\n";
    $listTable .= "            <td class=border-none align=right style='border-top:1.0pt solid windowtext'>". number_format($keihi_cut_l, 0) . "</td>\n";
    $listTable .= "            <td class=border-none align=center>÷</td>\n";
    $listTable .= "            <td class=border-none>Ｃ＝</td>\n";
    $listTable .= "            <td class=labor_rate align=right>". number_format($labor_rate_l, 2) . "</td>\n";
    $listTable .= "            <td class=border-none>円／分</td>\n";
    $listTable .= "        </tr>\n";
    $listTable .= "        <tr height=20 style='mso-height-source:userset;height:15.0pt'>\n";
    $listTable .= "        </tr>\n";
    $listTable .= "        <tr height=20 style='mso-height-source:userset;height:15.0pt'>\n";
    $listTable .= "            <td height=20 class=border-none style='height:15.0pt'></td>\n";
    $listTable .= "            <td class=border-none colspan=3>組立作業時間計</td>\n";
    $listTable .= "            <td class=assemble align=right>". number_format($worker_time_l, 0) . "</td>\n";
    $listTable .= "            <td class=border-none>Ｃ</td>\n";
    /*
    $listTable .= "            <td class=assemble align=right>". number_format($assemble_l, 0) . "</td>\n";
    $listTable .= "            <td class=border-none>分</td>\n";
    */
    $listTable .= "            <td></td>\n";
    $listTable .= "            <td colspan=3 class=border-on align=center>". format_date6_ki($request->get('before_ym')) . "実際賃率</td>\n";
    $listTable .= "        </tr>\n";
    $listTable .= "        <tr height=20 style='mso-height-source:userset;height:15.0pt'>\n";
    $listTable .= "            <td height=20 class=border-none style='height:15.0pt'></td>\n";
    $listTable .= "            <td colspan=3 style='border-top:1.0pt solid windowtext'>　</td>\n";
    $listTable .= "            <td align=right style='border-top:1.0pt solid windowtext'>　</td>\n";
    $listTable .= "            <td>　</td>\n";
    /*
    $listTable .= "            <td class=border-none colspan=3>横川応援時間加算</td>\n";
    $listTable .= "            <td class=assist align=right>". number_format($assist_l, 0) . "</td>\n";
    $listTable .= "            <td class=border-none>分</td>\n";
    */
    $listTable .= "            <td></td>\n";
    if ($before_labor_rate_l == "") {
        $listTable .= "        <td class=border-on align=right>---</td>\n";
    } else {
        $listTable .= "        <td class=border-on align=right>". number_format($before_labor_rate_l, 2) . "</td>\n";
    }
    $listTable .= "            <td class=border-on style='border-right:none;border-bottom:none'>円／分</td>\n";
    $listTable .= "        </tr>\n";
    /*
    $listTable .= "        <tr height=20 style='mso-height-source:userset;height:15.0pt'>\n";
    $listTable .= "            <td height=20 class=border-none style='height:15.0pt'></td>\n";
    $listTable .= "            <td class=border-none align=right colspan=3 style='border-top:1.0pt solid windowtext'>合計</td>\n";
    $listTable .= "            <td class=border-none align=right>". number_format($worker_time_l, 0) . "</td>\n";
    $listTable .= "            <td class=border-none>Ｃ</td>\n";
    $listTable .= "        </tr>\n";
    */
    $listTable .= "    </table>\n";
    $listTable .= "</center>\n";
    $listTable .= "</body>\n";
    $listTable .= "</html>\n";
    return $listTable;
}

////////////////// 作業者数と標準賃率の入力画面の作成
function getInputHTMLbody($request, $menu, $result)
{
    $res_g                = $result->get_array2('res_g');
    $be_worker_figure_s   = $result->get_array2('be_worker_figure_s');
    $be_worker_figure_p   = $result->get_array2('be_worker_figure_p');
    $before_standard_rate = $result->get_array2('before_standard_rate');
    $be_worker_rate_s     = $result->get('be_worker_rate_s');
    $be_worker_rate_p     = $result->get('be_worker_rate_p');
    
    $listTable = '';
    $listTable .= "<!DOCTYPE HTML PUBLIC '-//W3C//DTD HTML 4.01 Transitional//EN'>\n";
    $listTable .= "<html>\n";
    $listTable .= "<head>\n";
    $listTable .= "<meta http-equiv='Content-Type' content='text/html; charset=EUC-JP'>\n";
    $listTable .= "<meta http-equiv='Content-Style-Type' content='text/css'>\n";
    $listTable .= "<meta http-equiv='Content-Script-Type' content='text/javascript'>\n";
    $listTable .= "<script type='text/javascript' src='../assemblyRate_reference.js'></script>\n";
    $listTable .= "<link rel='stylesheet' href='../assemblyRate_reference.css' type='text/css'>\n";
    $listTable .= "</head>\n";
    $listTable .= "<body>\n";
    $listTable .= "<center>\n";
    $listTable .= "    <form name='entry_form' action='../assemblyRate_reference_Main.php' method='post' onSubmit='return chk_entry(this)' target='_parent'>\n";
    $listTable .= "    <table bgcolor='#d6d3ce' width='300' align='center' border='1' cellspacing='0' cellpadding='3'>\n";
    $listTable .= "        <tr><td> <!----------- ダミー(デザイン用) ------------>\n";
    $listTable .= "        <table class='winbox_field' width='100%' bgcolor='#d6d3ce' align='center' border='1' cellspacing='0' cellpadding='3'>\n";
    $listTable .= "        <THEAD>\n";
    $listTable .= "            <!-- テーブル ヘッダーの表示 -->\n";
    $listTable .= "            <tr>\n";
    $listTable .= "                <td colspan=4 rowspan=3 bgcolor='#ffffc6' align='center'>\n";
    $listTable .= "                    作業者数と標準賃率の登録<BR>\n";
    $listTable .= "                    <font color='red'>\n";
    $listTable .= "                    ※初期値は前月のデータ・登録履歴があればそのデータを表示します。<BR>\n";
    $listTable .= "                    （前月未登録・履歴なしの場合は空欄）\n";
    $listTable .= "                    </font>\n";
    $listTable .= "                </td>\n";
    $listTable .= "            </tr>\n";
    $listTable .= "            <tr></tr>\n";
    $listTable .= "            <tr></tr>\n";
    $listTable .= "            <tr>\n";
    $listTable .= "                <th rowspan=2 nowrap>グループ名</th>\n";
    $listTable .= "                <th rowspan=2 nowrap>作業者数<BR>(社員)</th>\n";
    $listTable .= "                <th rowspan=2 nowrap>作業者数<BR>(パート)</th>\n";
    $listTable .= "                <th rowspan=2 nowrap>標準賃率<BR>(円)</th>\n";
    $listTable .= "            </tr>\n";
    $listTable .= "        </THEAD>\n";
    $listTable .= "        <TFOOT>\n";
    $listTable .= "            <!-- 現在はフッターは何もない -->\n";
    $listTable .= "        </TFOOT>\n";
    $listTable .= "        <TBODY>\n";
    $listTable .= "            <tr>\n";
    for ($i=0; $i<$result->get('rows_g'); $i++) {
        $listTable .= "            <tr>\n";
        $listTable .= "                <td class='winbox' nowrap align='left'><div class='pt9'>{$res_g[$i][1]}</div></td>\n";
        $listTable .= "                <td class='winbox' align='center'><input type='text' class='price_font' name='worker_figure_s[". $i . "]' value='{$be_worker_figure_s[$i]}' size='15'></td>\n";
        $listTable .= "                <td class='winbox' align='center'><input type='text' class='price_font' name='worker_figure_p[". $i . "]' value='{$be_worker_figure_p[$i]}' size='15'></td>\n";
        $listTable .= "                <td class='winbox' align='center'><input type='text' class='price_font' name='standard_rate[". $i ."]' value='{$before_standard_rate[$i]}' size='15'></td>\n";
        $listTable .= "           </tr>\n";
    }
    $listTable .= "            </tr>\n";
    $listTable .= "            <tr>\n";
    $listTable .= "                <th rowspan=2 nowrap>　</th>\n";
    $listTable .= "                <th rowspan=2 nowrap>作業者賃率<BR>(社員)</th>\n";
    $listTable .= "                <th rowspan=2 nowrap>作業者賃率<BR>(パート)</th>\n";
    $listTable .= "                <th rowspan=2 nowrap>　</th>\n";
    $listTable .= "            </tr>\n";
    $listTable .= "            <tr></tr>\n";
    $listTable .= "            <tr>\n";
    $listTable .= "                <td>　</td>\n";
    $listTable .= "                    <td class='winbox' align='center'><input type='text' class='price_font' name='worker_rate_s' value='{$be_worker_rate_s}' size='15'></td>\n";
    $listTable .= "                    <td class='winbox' align='center'><input type='text' class='price_font' name='worker_rate_p' value='{$be_worker_rate_p}' size='15'></td>\n";
    $listTable .= "                <td>　</td>\n";
    $listTable .= "            </tr>\n";
    $listTable .= "            <tr>\n";
    $listTable .= "                <td colspan=4 class='winbox' align='center'>\n";
    $listTable .= "                    <input type='submit' class='entry_font' name='entry' value='登録'>\n";
    $listTable .= "                    <input type='hidden' name='rows_g' value='". $result->get('rows_g') . "'>\n";
    $listTable .= "                    <input type='hidden' name='end_ym' value='". $request->get('end_ym') . "'>\n";
    $listTable .= "                    <input type='hidden' name='str_ym' value='". $request->get('str_ym') . "'>\n";
    $listTable .= "                </td>\n";
    $listTable .= "            </tr>\n";
    $listTable .= "        </TBODY>\n";
    $listTable .= "        </table>\n";
    $listTable .= "        </td></tr>\n";
    $listTable .= "    </table>\n";
    $listTable .= "    </form>\n";
    $listTable .= "    </center>\n";
    $listTable .= "</body>\n";
    $listTable .= "</html>\n";
    return $listTable;
}

////////////////// 賃率照会画面のHTMLを出力
function outViewListHTML($request, $menu, $result)
{
    $listHTML = getViewHTMLbody($request, $menu, $result);
    $request->add('view_flg', '照会');
    ////////////// HTMLファイル出力
    $file_name = "list/assemblyRate_reference_List-{$_SESSION['User_ID']}.html";
    $handle = fopen($file_name, 'w');
    fwrite($handle, $listHTML);
    fclose($handle);
    chmod($file_name, 0666);       // fileを全てrwモードにする
}

////////////////// 作業者数と標準賃率の入力画面を出力
function outInputHTML($request, $menu, $result)
{
    $listHTML = getInputHTMLbody($request, $menu, $result);
    $request->add('view_flg', '入力');
    ////////////// HTMLファイル出力
    $file_name = "list/assemblyRate_workerInput_List-{$_SESSION['User_ID']}.html";
    $handle = fopen($file_name, 'w');
    fwrite($handle, $listHTML);
    fclose($handle);
    chmod($file_name, 0666);       // fileを全てrwモードにする
}

////////////////// ６桁の任意の日付を前月の'期月'フォーマットして返す。
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
        $ki    = substr($nen, 2, 2);
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

////////////////// グループ番号をグループ名に変換
function format_number_name($number, $res_nn, $rows_nn)
{
    for ($n=0; $n<$rows_nn; $n++) {
        if ($res_nn[$n][0] == $number) {
            $group_name = $res_nn[$n][1];
            return $group_name;
        }
    }
}

?>
