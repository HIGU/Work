<?php
//////////////////////////////////////////////////////////////////////////////
// 機械ワークデータ編集 メイン assemblyRate_machineWork_Main.php            //
//                             (旧 machine_group_work_main.php)             //
// Copyright (C) 2007-2011 Norihisa.Ooya usoumu@nitto-kohki.co.jp           //
// Changed history                                                          //
// 2007/12/13 Created  assemblyRate_machineWork_Main.php                    //
//            旧ファイルより各処理を関数化 コメントの位置の調整             //
// 2007/12/14 プログラムの最後に改行を追加                                  //
// 2007/12/21 グループ毎の計算データの更新を関数化 machineWork_groupEntry   //
// 2007/12/29 日付データの戻り値を設定                                      //
// 2011/06/22 format_date系をtnk_funcに移動のためこちらを削除               //
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
    ////////////// TNK 共用メニュークラスのインスタンスを作成
    $menu = new MenuHeader(0);                          // 認証チェック0=一般以上 戻り先=TOP_MENU タイトル未設定
    ////////////// タイトル名(ソースのタイトル名とフォームのタイトル名)
    $menu->set_title('機械ワークデータ編集');
  
    $request = new Request;
    $result  = new Result;
    
    ////////////// リターンアドレス設定
    $menu->set_RetUrl($_SESSION['various_referer'] . '?wage_ym=' . $request->get('wage_ym'));             // 通常は指定する必要はない
    
    before_date($request, $result);                     // 前月履歴表示の為の前月計算
    get_group_master($result, $request);                // グループマスターの取得
    get_machineWork_master($result, $request);          // 機械ワークデータマスターの取得
    get_machineWorkBefore_master($result, $request);    // 前月分機械ワークデータマスターの取得
    
    request_check($request, $result, $menu);            // 処理の分岐チェック
    
    outViewListHTML($request, $menu, $result);          // HTML作成
    
    display($menu, $request, $result);                  // 画面表示
}

////////////// 画面表示
function display($menu, $request, $result)
{       
    ////////// ブラウザーのキャッシュ対策用
    $uniq = 'id=' . $menu->set_useNotCache('target');
    
    ////////// メッセージ出力フラグ
    $msg_flg = 'site';

    ob_start('ob_gzhandler');                   // 出力バッファをgzip圧縮
    
    ////////// HTML Header を出力してキャッシュを制御
    $menu->out_html_header();
 
    ////////// Viewの処理
    require_once ('assemblyRate_machineWork_View.php');

    ob_end_flush(); 
}

////////////// 前月履歴表示の為の前月計算
function before_date($request, $result)
{
    $wage_ym = $request->get('wage_ym');
    $nen   = substr($wage_ym, 0, 4);
    $tsuki = substr($wage_ym, 4, 2);
    if (1 == $tsuki) {
        $nen   = $nen - 1;
        $tsuki = 12;
    } else {
        $tsuki = $tsuki - 1;
        if ($tsuki < 10) {
            $tsuki = 0 . $tsuki;
        }
    }
    $wage_ym_b = $nen . $tsuki;
    $result->add('wage_ym_b', $wage_ym_b);
}

////////////// 処理の分岐を行う
function request_check($request, $result, $menu)
{
    $ok = true;
    if ($request->get('number') != '') $ok = machineWork_copy($request, $result);
    if ($request->get('del') != '') $ok = machineWork_del($request);
    if ($request->get('entry') != '')  $ok = machineWork_entry($request, $result);
    if ($ok) {
        ////// データの初期化
        $request->add('del', '');
        $request->add('entry', '');
        $request->add('number', '');
        $request->add('group_no', '');
        $request->add('total_date', '');
        $request->add('mac_no', '');
        $request->add('setup_time', '');
        $request->add('operation_time', '');
        $request->add('repairing_expenses', '');
        get_group_master($result, $request);       // グループマスターの取得
        get_machineWork_master($result, $request); // 機械ワークデータマスターの取得
        machineWork_groupEntry($request, $result); // グループ毎の集計結果の更新
    }
}

////////////// 追加・変更ロジック (合計レコード数取得前に行う)
function machineWork_entry($request, $result)
{
    if (getCheckAuthority(22)) {                             // 認証チェック
        $total_date = $request->get('wage_ym');
        $query = sprintf("SELECT group_machine_rate FROM assembly_machine_group_rate WHERE total_date=%d", $total_date);
        $res_check = array();
        $rows_check = getResult($query, $res_check);
        if ($rows_check <= 0) {                              // 賃率が登録済みかチェック登録済みの場合はワークデータの更新はしない
            $group_no = $request->get('group_no');
            $mac_no = $request->get('mac_no');
            $setup_time = $request->get('setup_time');
            $operation_time = $request->get('operation_time');
            $repairing_expenses = $request->get('repairing_expenses');
            $query = sprintf("SELECT total_date, mac_no FROM assembly_machine_group_work WHERE total_date=%d AND mac_no=%d", $total_date, $mac_no);
            $res_chk = array();
            if ( getResult($query, $res_chk) > 0 ) {         // 登録あり UPDATE 更新
                $query = sprintf("UPDATE assembly_machine_group_work SET group_no=%d, total_date=%d, mac_no=%d, setup_time=%d, operation_time=%d, repairing_expenses=%d, last_date=CURRENT_TIMESTAMP, last_user='%s' 
                                    WHERE total_date=%d AND mac_no=%d", $group_no, $total_date, $mac_no, $setup_time, $operation_time, $repairing_expenses, $_SESSION['User_ID'], $total_date, $mac_no);
                if (query_affected($query) <= 0) {
                    $_SESSION['s_sysmsg'] .= "対象月{$total_date} 機械No.：{$mac_no}のデータ変更失敗！";    // .= に注意
                    $msg_flg = 'alert';
                    return false;
                } else {
                    $_SESSION['s_sysmsg'] .= "対象月{$total_date} 機械No.：{$mac_no}のデータを変更しました！";    // .= に注意
                    return true;
                }
            } else {                                         // 登録なし INSERT 新規   
                $query = sprintf("INSERT INTO assembly_machine_group_work (group_no, total_date, mac_no, setup_time, operation_time, repairing_expenses, last_date, last_user)
                             VALUES (%d, %d, %d, %d, %d, %d, CURRENT_TIMESTAMP, '%s')",
                             $group_no, $total_date, $mac_no, $setup_time, $operation_time, $repairing_expenses, $_SESSION['User_ID']);
                if (query_affected($query) <= 0) {
                    $_SESSION['s_sysmsg'] .= "対象月{$total_date} 機械No.：{$mac_no}のデータ登録に失敗！";    // .= に注意
                    $msg_flg = 'alert';
                    return false;
                } else {
                    $_SESSION['s_sysmsg'] .= "対象月{$total_date} 機械No.：{$mac_no}のデータ登録を追加しました！";    // .= に注意
                    return true;
                }
            }
        } else {
            if ($res_check[0]['group_machine_rate'] == '') { // 賃率が登録済みかチェック登録済みの場合はワークデータの更新はしない
                $group_no = $request->get('group_no');
                $mac_no = $request->get('mac_no');
                $setup_time = $request->get('setup_time');
                $operation_time = $request->get('operation_time');
                $repairing_expenses = $request->get('repairing_expenses');
                $query = sprintf("SELECT total_date, mac_no FROM assembly_machine_group_work WHERE total_date='%d' AND mac_no='%d'", $total_date, $mac_no);
                $res_chk = array();
                if ( getResult($query, $res_chk) > 0 ) {     // 登録あり UPDATE 更新
                    $query = sprintf("UPDATE assembly_machine_group_work SET group_no=%d, total_date=%d, mac_no=%d, setup_time=%d, operation_time=%d, repairing_expenses=%d, last_date=CURRENT_TIMESTAMP, last_user='%s' 
                                        WHERE total_date=%d AND mac_no=%d", $group_no, $total_date, $mac_no, $setup_time, $operation_time, $repairing_expenses, $_SESSION['User_ID'], $total_date, $mac_no);
                    if (query_affected($query) <= 0) {
                        $_SESSION['s_sysmsg'] .= "対象月{$total_date} 機械No.：{$mac_no}のデータ変更失敗！";    // .= に注意
                        $msg_flg = 'alert';
                        return false;
                    } else {
                        $_SESSION['s_sysmsg'] .= "対象月{$total_date} 機械No.：{$mac_no}のデータを変更しました！";    // .= に注意
                        return true;
                    }
                } else {                                     // 登録なし INSERT 新規   
                    $query = sprintf("INSERT INTO assembly_machine_group_work (group_no, total_date, mac_no, setup_time, operation_time, repairing_expenses, last_date, last_user)
                                 VALUES (%d, %d, %d, %d, %d, %d, CURRENT_TIMESTAMP, '%s')",
                                 $group_no, $total_date, $mac_no, $setup_time, $operation_time, $repairing_expenses, $_SESSION['User_ID']);
                    if (query_affected($query) <= 0) {
                        $_SESSION['s_sysmsg'] .= "対象月{$total_date} 機械No.：{$mac_no}のデータ登録に失敗！";    // .= に注意
                        $msg_flg = 'alert';
                        return false;
                    } else {
                        $_SESSION['s_sysmsg'] .= "対象月{$total_date} 機械No.：{$mac_no}のデータ登録を追加しました！";    // .= に注意
                        return true;
                    }
                }
            } else {
                $_SESSION['s_sysmsg'] .= "機械賃率がすでに確定されています。";
                return false;
            }
        }
    } else {                                                 // 認証なしエラー
        $_SESSION['s_sysmsg'] .= "編集権限がありません。必要な場合には、担当者に連絡して下さい。";
        return false;
    }
}

////////////// 削除ロジック (合計レコード数取得前に行う)
function machineWork_del($request)
{
    if (getCheckAuthority(22)) {     // 認証チェック
        $total_date = $request->get('wage_ym');
        $query = sprintf("SELECT group_machine_rate FROM assembly_machine_group_rate WHERE total_date=%d", $total_date);
        $res_check = array();
        $rows_check = getResult($query, $res_check);
        if ($rows_check <= 0) {      // 賃率が登録済みかチェック登録済みの場合はワークデータの更新はしない
            $mac_no = $request->get('mac_no');
            $query = sprintf("DELETE FROM assembly_machine_group_work WHERE total_date=%d AND mac_no=%d", $total_date, $mac_no);
            if (query_affected($query) <= 0) {
                $_SESSION['s_sysmsg'] .= "対象月{$total_date} 機械No.：{$mac_no}の削除に失敗！";    // .= に注意
                $msg_flg = 'alert';
                return false;
            } else {
                $_SESSION['s_sysmsg'] .= "対象月{$total_date} 機械No.：{$mac_no}を削除しました！";    // .= に注意
                return true;
            }
        } else {
            $_SESSION['s_sysmsg'] .= "機械賃率がすでに確定されています。";
            return false;
        }
    } else {                         // 認証なしエラー
        $_SESSION['s_sysmsg'] .= "編集権限がありません。必要な場合には、担当者に連絡して下さい。";
        return false;
    }
}

////////////// グループ別の集計結果を登録
function machineWork_groupEntry($request, $result)
{
    $res = $result->get_array2('res_m');
    $res_g = $result->get_array2('res_g');
    $query = sprintf("SELECT group_machine_rate FROM assembly_machine_group_rate WHERE total_date=%d", $request->get('wage_ym'));
    $res_check = array();
    $rows_check = getResult($query, $res_check);
    if ($rows_check <= 0) {      // 賃率が登録済みかチェック
        ///////////////////////////// 登録なしの場合のみ集計結果の更新を行う
        $group_time = array();    //グループ別運転時間の計算
        for ($i=0; $i<$result->get('rows_g'); $i++) {
            $group_time[$i] = 0;
        }
        $group_repair[$i] = array();    //グループ別修繕費の計算
        for ($i=0; $i<$result->get('rows_g'); $i++) {
            $group_repair[$i] = 0;
        }

        for ($r=0; $r<$result->get('rows_m'); $r++) {    //償却額をグループ別に振分
            for ($i=0; $i<$result->get('rows_g'); $i++) {
                if($res[$r][0] == $res_g[$i][0]) {
                    $group_time[$i]   = $group_time[$i] + $res[$r][3] + $res[$r][4];    //グループ別運転時間の計算
                    $group_repair[$i] = $group_repair[$i] + $res[$r][5];    //グループ別修繕費の計算
                }
            }
        }
        ////////////////////////////////// 集計結果の更新
        for ($i=0; $i<$result->get('rows_g'); $i++) {
            $query = sprintf("SELECT * FROM assembly_machine_group_rate WHERE group_no=%d AND total_date=%d",
                                $res_g[$i][0], $request->get('wage_ym'));
            $res_chk = array();
            if ( getResult($query, $res_chk) > 0 ) {   //////// 登録あり UPDATE更新
                $query = sprintf("UPDATE assembly_machine_group_rate SET group_time=%d, group_repair=%d, last_date=CURRENT_TIMESTAMP, last_user='%s' WHERE group_no='%d' AND total_date='%d'", $group_time[$i], $group_repair[$i], $_SESSION['User_ID'], $res_g[$i][0], $request->get('wage_ym'));
                if (query_affected($query) <= 0) {
                }
            } else {                                    //////// 登録なし INSERT 新規   
                $query = sprintf("INSERT INTO assembly_machine_group_rate (group_no, total_date, group_time, group_repair, last_date, last_user)
                                    VALUES (%d, %d, %d, %d, CURRENT_TIMESTAMP, '%s')",
                                    $res_g[$i][0], $request->get('wage_ym'), $group_time[$i], $group_repair[$i], $_SESSION['User_ID']);
                if (query_affected($query) <= 0) {
                }
            }
        }
    } else {
        if ($res_check[0]['group_machine_rate'] == '') {      // 賃率が登録済みかチェック
            ///////////////////////////// 登録なしの場合のみ集計結果の更新を行う
            $group_time = array();    //グループ別運転時間の計算
            for ($i=0; $i<$result->get('rows_g'); $i++) {
                $group_time[$i] = 0;
            }
            $group_repair[$i] = array();    //グループ別修繕費の計算
            for ($i=0; $i<$result->get('rows_g'); $i++) {
                $group_repair[$i] = 0;
            }

            for ($r=0; $r<$result->get('rows_m'); $r++) {    //償却額をグループ別に振分
                for ($i=0; $i<$result->get('rows_g'); $i++) {
                    if($res[$r][0] == $res_g[$i][0]) {
                        $group_time[$i]   = $group_time[$i] + $res[$r][3] + $res[$r][4];    //グループ別運転時間の計算
                        $group_repair[$i] = $group_repair[$i] + $res[$r][5];    //グループ別修繕費の計算
                    }
                }
            }
            ////////////////////////////////// 集計結果の更新
            for ($i=0; $i<$result->get('rows_g'); $i++) {
                $query = sprintf("SELECT * FROM assembly_machine_group_rate WHERE group_no=%d AND total_date=%d",
                                    $res_g[$i][0], $request->get('wage_ym'));
                $res_chk = array();
                if ( getResult($query, $res_chk) > 0 ) {   //////// 登録あり UPDATE更新
                    $query = sprintf("UPDATE assembly_machine_group_rate SET group_time=%d, group_repair=%d, last_date=CURRENT_TIMESTAMP, last_user='%s' WHERE group_no='%d' AND total_date='%d'", $group_time[$i], $group_repair[$i], $_SESSION['User_ID'], $res_g[$i][0], $request->get('wage_ym'));
                    if (query_affected($query) <= 0) {
                    }
                } else {                                    //////// 登録なし INSERT 新規   
                    $query = sprintf("INSERT INTO assembly_machine_group_rate (group_no, total_date, group_time, group_repair, last_date, last_user)
                                        VALUES (%d, %d, %d, %d, CURRENT_TIMESTAMP, '%s')",
                                        $res_g[$i][0], $request->get('wage_ym'), $group_time[$i], $group_repair[$i], $_SESSION['User_ID']);
                    if (query_affected($query) <= 0) {
                    }
                }
            }
        }
    }
}
////////////// 表示用(一覧表)のグループマスターデータをSQLで取得
function get_group_master ($result, $request)
{
    $query_g = "
        SELECT  groupm.group_no                AS グループ番号     -- 0
            ,   groupm.group_name              AS グループ名       -- 1
        FROM
            assembly_machine_group_master AS groupm
        ORDER BY
            group_no
    ";

    $res_g = array();
    if (($rows_g = getResultWithField2($query_g, $field_g, $res_g)) <= 0) {
        $_SESSION['s_sysmsg'] = "グループの登録がありません！";
        $field[0]   = "グループ番号";
        $field[1]   = "グループ名";
        $_SESSION['s_sysmsg'] = "登録がありません！";
        $result->add_array2('res_g', '');
        $result->add_array2('field_g', '');
        $result->add('num_g', 2);
        $result->add('rows_g', '');
    } else {
        $num_g = count($field_g);
        $result->add_array2('res_g', $res_g);
        $result->add_array2('field_g', $field_g);
        $result->add('num_g', $num_g);
        $result->add('rows_g', $rows_g);
    }
}

////////////// 表示用(一覧表)の機械ワークデータをSQLで取得
function get_machineWork_master ($result, $request)
{
    $wage_ym = $request->get('wage_ym');
    $query = "
        SELECT  mwork.group_no              AS グループ名    -- 0
            ,   mwork.total_date            AS 集計年月      -- 1
            ,   mwork.mac_no                AS 機械番号      -- 2
            ,   mwork.setup_time            AS 段取時間      -- 3
            ,   mwork.operation_time        AS 本稼働時間    -- 4
            ,   mwork.repairing_expenses    AS 修繕費        -- 5
        FROM
            assembly_machine_group_work AS mwork
        WHERE
            mwork.total_date = $wage_ym
        ORDER BY
            group_no, mac_no
    ";

    $res = array();
    if (($rows = getResultWithField2($query, $field, $res)) <= 0) {
        $_SESSION['s_sysmsg'] = "登録がありません！";
    } else {
        $num = count($field);
        $result->add_array2('res_m', $res);
        $result->add_array2('field_m', $field);
        $result->add('num_m', $num);
        $result->add('rows_m', $rows);
        $res_g = $result->get_array2('res_g');
        for ($r=0; $r<$rows; $r++) {    // グループ番号とグループ名の置き換え(固定資産）
            for ($i=0; $i<$result->get('rows_g'); $i++) {
                if($res[$r][0] == $res_g[$i][0]) {
                    $group_name[$r] = $res_g[$i][1];
                }
            }
        }
        $result->add_array2('group_name', $group_name);
    }
}

////////////// 表示用(前月分)の機械ワークデータをSQLで取得
function get_machineWorkBefore_master($result, $request)
{
    $wage_ym_b = $result->get('wage_ym_b');
    $query = "
        SELECT  mworkb.group_no              AS グループ名    -- 0
            ,   mworkb.total_date            AS 集計年月      -- 1
            ,   mworkb.mac_no                AS 機械番号      -- 2
            ,   mworkb.setup_time            AS 段取時間      -- 3
            ,   mworkb.operation_time        AS 本稼働時間    -- 4
            ,   mworkb.repairing_expenses    AS 修繕費        -- 5
        FROM
            assembly_machine_group_work AS mworkb
        WHERE
            mworkb.total_date = $wage_ym_b
        ORDER BY
            group_no, mac_no
    ";
    
    $res = array();
    if (($rows = getResultWithField2($query, $field, $res)) <= 0) {
        $_SESSION['s_sysmsg'] = "登録がありません！";
    } else {
        $num = count($field);
        $result->add_array2('res_b', $res);
        $result->add_array2('field_b', $field);
        $result->add('num_b', $num);
        $result->add('rows_b', $rows);
        $res_g = $result->get_array2('res_g');
        for ($r=0; $r<$rows; $r++) {    // グループ番号とグループ名の置き換え(固定資産）
            for ($i=0; $i<$result->get('rows_g'); $i++) {
                if($res[$r][0] == $res_g[$i][0]) {
                    $group_name[$r] = $res_g[$i][1];
                }
            }
        }
        $result->add_array2('group_name_b', $group_name);
    }
}

////////////// コピーのリンクが押された時
function machineWork_copy($request, $result)
{
    $r = $request->get('number');
    $res = $result->get_array2('res_m');
    $group_no              = $res[$r][0];
    $total_date            = $res[$r][1];
    $mac_no                = $res[$r][2];
    $setup_time            = $res[$r][3];
    $operation_time        = $res[$r][4];
    $repairing_expenses    = $res[$r][5];
    
    $request->add('group_no', $group_no);
    $request->add('total_date', $total_date);
    $request->add('mac_no', $mac_no);
    $request->add('setup_time', $setup_time);
    $request->add('operation_time', $operation_time);
    $request->add('repairing_expenses', $repairing_expenses);
}

////////////// 機械データ画面のHTMLの作成
function getViewHTMLbody($request, $menu, $result)
{
    $listTable = '';
    $listTable .= "<!DOCTYPE HTML>\n";
    $listTable .= "<html>\n";
    $listTable .= "<head>\n";
    $listTable .= "<meta http-equiv='Content-Type' content='text/html; charset=UTF-8'>\n";
    $listTable .= "<meta http-equiv='Content-Style-Type' content='text/css'>\n";
    $listTable .= "<meta http-equiv='Content-Script-Type' content='text/javascript'>\n";
    $listTable .= "<style type='text/css'>\n";
    $listTable .= "<!--\n";
    $listTable .= "th {\n";
    $listTable .= "    background-color:   blue;\n";
    $listTable .= "    color:              yellow;\n";
    $listTable .= "    font-size:          10pt;\n";
    $listTable .= "    font-weight:        bold;\n";
    $listTable .= "    font-family:        monospace;\n";
    $listTable .= "}\n";
    $listTable .= "a:hover {\n";
    $listTable .= "    background-color:   blue;\n";
    $listTable .= "    color:              white;\n";
    $listTable .= "}\n";
    $listTable .= "a:active {\n";
    $listTable .= "    background-color:   gold;\n";
    $listTable .= "    color:              black;\n";
    $listTable .= "}\n";
    $listTable .= "a {\n";
    $listTable .= "    color:   blue;\n";
    $listTable .= "}\n";
    $listTable .= "-->\n";
    $listTable .= "</style>\n";
    $listTable .= "</head>\n";
    $listTable .= "<body>\n";
    $listTable .= "<center>\n";
    $listTable .= "    <form name='entry_form' action='assemblyRate_machineWork_Main.php' method='post'>\n";
    $listTable .= "        <table bgcolor='#d6d3ce' width='300' align='center' border='1' cellspacing='0' cellpadding='3'>\n";
    $listTable .= "            <tr><td> <!----------- ダミー(デザイン用) ------------>\n";
    $listTable .= "        <table class='winbox_field' width='100%' bgcolor='#d6d3ce' align='center' border='1' cellspacing='0' cellpadding='3'>\n";
    $listTable .= "        <THEAD>\n";
    $listTable .= "            <!-- テーブル ヘッダーの表示 -->\n";
    $listTable .= "            <tr>\n";
    $listTable .= "               <td bgcolor='#ffffc6' align='center' colspan='20'>\n";
    $wage_ym = $request->get('wage_ym');
    $listTable .= "                   ". format_date6_kan($wage_ym) ."\n";
    $listTable .= "                   機械ワークデータ\n";
    $listTable .= "                   <font size=2>\n";
    $listTable .= "                   (単位:分・円)\n";
    $listTable .= "                   </font>\n";
    $listTable .= "                </td>\n";
    $listTable .= "            </tr>\n";
    $listTable .= "            <tr>\n";
    $listTable .= "                <th class='winbox' nowrap width='10'>No</th>        <!-- 行ナンバーの表示 -->\n";
    if ($result->get('num_m') > 0) {
        $field = $result->get_array2('field_m');
        for ($i=0; $i<$result->get('num_m'); $i++) {    // フィールド数分繰返し\n";
            if ($i == 1) {
            } else {
                $listTable .= "        <th class='winbox' nowrap>". $field[$i] ."</th>\n";
            }
        }
    } else {
        $listTable .= "            <th class='winbox' nowrap>グループ名</th>\n";
        $listTable .= "            <th class='winbox' nowrap>機械番号</th>\n";
        $listTable .= "            <th class='winbox' nowrap>段取時間</th>\n";
        $listTable .= "            <th class='winbox' nowrap>本稼働時間</th>\n";
        $listTable .= "            <th class='winbox' nowrap>修繕費</th>\n";
    }
    $listTable .= "            </tr>\n";
    $listTable .= "        </THEAD>\n";
    $listTable .= "        <TFOOT>\n";
    $listTable .= "            <!-- 現在はフッターは何もない -->\n";
    $listTable .= "        </TFOOT>\n";
    $listTable .= "        <TBODY>\n";
    $listTable .= "            <!--  bgcolor='#ffffc6' 薄い黄色 -->\n";
    $listTable .= "            <!-- サンプル<td rowspan='2' colspan='3' width='200' align='center' class='pt10b' bgcolor='#ffffc6'>  </td> -->\n";
    $res = $result->get_array2('res_m');
    $group_name = $result->get_array2('group_name');
    for ($r=0; $r<$result->get('rows_m'); $r++) {
        $listTable .= "        <tr>\n";
        $listTable .= "            <td class='winbox' nowrap align='right'>\n";
        $listTable .= "                <a href='../assemblyRate_machineWork_Main.php?number=". $r ."&wage_ym=". $request->get('wage_ym') ."' target='_parent' style='text-decoration:none;'>\n";
        $cnum = $r + 1;
        $listTable .= "                ". $cnum ."\n";
        $listTable .= "                </a>\n";
        $listTable .= "            </td>    <!-- 行ナンバーの表示 -->\n";
        for ($i=0; $i<$result->get('num_m'); $i++) {    // レコード数分繰返し
            switch ($i) {
                case 0:                                 // グループ
                    $listTable .= "<td class='winbox' nowrap align='left'><div class='pt9'>". $group_name[$r] ."</div></td>\n";
                break;
                case 1:                                 // 集計年月
                    break;
                case 2:                                 // 機械番号
                    $listTable .= "<td class='winbox' nowrap align='center'><div class='pt9'>". $res[$r][$i] ."</div></td>\n";
                    break;
                case 3:                                 // 段取時間
                    $listTable .= "<td class='winbox' nowrap align='right'><div class='pt9'>". number_format($res[$r][$i], 0) ."</div></td>\n";
                    break;
                case 4:                                 // 本稼働時間
                    $listTable .= "<td class='winbox' nowrap align='right'><div class='pt9'>". number_format($res[$r][$i], 0) ."</div></td>\n";
                    break;
                case 5:                                 // 修繕費
                    $listTable .= "<td class='winbox' nowrap align='right'><div class='pt9'>". number_format($res[$r][$i], 0) ."</div></td>\n";
                    break;
                default:
                    break;
            }
        }
        $listTable .= "        </tr>\n";
    }
    $listTable .= "        </TBODY>\n";
    $listTable .= "        </table>\n";
    $listTable .= "            </td></tr>\n";
    $listTable .= "        </table> <!----------------- ダミーEnd ------------------>\n";
    $listTable .= "    </form>\n";
    $listTable .= "    <form name='entry_form' action='assemblyRate_machineWork_Main.php' method='post'>\n";
    $listTable .= "        <table bgcolor='#d6d3ce' width='300' align='center' border='1' cellspacing='0' cellpadding='3'>\n";
    $listTable .= "            <tr><td> <!----------- ダミー(デザイン用) ------------>\n";
    $listTable .= "        <table class='winbox_field' width='100%' bgcolor='#d6d3ce' align='center' border='1' cellspacing='0' cellpadding='3'>\n";
    $listTable .= "        <THEAD>\n";
    $listTable .= "            <!-- テーブル ヘッダーの表示 -->\n";
    $listTable .= "            <tr>\n";
    $listTable .= "               <td bgcolor='#ffffc6' align='center' colspan='20'>\n";
    $wage_ym_b = $result->get('wage_ym_b');
    $listTable .= "                   ※参考\n";
    $listTable .= "                   ". format_date6_kan($wage_ym_b) ."\n";
    $listTable .= "                   機械ワークデータ\n";
    $listTable .= "                   <font size=2>\n";
    $listTable .= "                   (単位:分・円)\n";
    $listTable .= "                   </font>\n";
    $listTable .= "                </td>\n";
    $listTable .= "            </tr>\n";
    $listTable .= "            <tr>\n";
    $listTable .= "                <th class='winbox' nowrap width='10'>No</th>        <!-- 行ナンバーの表示 -->\n";
    if ($result->get('num_b') > 0) {
        $field = $result->get_array2('field_b');
        for ($i=0; $i<$result->get('num_b'); $i++) {    // フィールド数分繰返し\n";
            if ($i == 1) {
            } else {
                $listTable .= "        <th class='winbox' nowrap>". $field[$i] ."</th>\n";
            }
        }
    } else {
        $listTable .= "            <th class='winbox' nowrap>グループ名</th>\n";
        $listTable .= "            <th class='winbox' nowrap>機械番号</th>\n";
        $listTable .= "            <th class='winbox' nowrap>段取時間</th>\n";
        $listTable .= "            <th class='winbox' nowrap>本稼働時間</th>\n";
        $listTable .= "            <th class='winbox' nowrap>修繕費</th>\n";
    }
    $listTable .= "            </tr>\n";
    $listTable .= "        </THEAD>\n";
    $listTable .= "        <TFOOT>\n";
    $listTable .= "            <!-- 現在はフッターは何もない -->\n";
    $listTable .= "        </TFOOT>\n";
    $listTable .= "        <TBODY>\n";
    $listTable .= "            <!--  bgcolor='#ffffc6' 薄い黄色 -->\n";
    $listTable .= "            <!-- サンプル<td rowspan='2' colspan='3' width='200' align='center' class='pt10b' bgcolor='#ffffc6'>  </td> -->\n";
    $res = $result->get_array2('res_b');
    $group_name = $result->get_array2('group_name_b');
    for ($r=0; $r<$result->get('rows_b'); $r++) {
        $listTable .= "        <tr>\n";
        $listTable .= "            <td class='winbox' nowrap align='right'>\n";
        $cnum = $r + 1;
        $listTable .= "            ". $cnum ."\n";
        $listTable .= "            </td>    <!-- 行ナンバーの表示 -->\n";
        for ($i=0; $i<$result->get('num_b'); $i++) {    // レコード数分繰返し
            switch ($i) {
                case 0:                                 // グループ
                    $listTable .= "<td class='winbox' nowrap align='left'><div class='pt9'>". $group_name[$r] ."</div></td>\n";
                break;
                case 1:                                 // 集計年月
                    break;
                case 2:                                 // 機械番号
                    $listTable .= "<td class='winbox' nowrap align='center'><div class='pt9'>". $res[$r][$i] ."</div></td>\n";
                    break;
                case 3:                                 // 段取時間
                    $listTable .= "<td class='winbox' nowrap align='right'><div class='pt9'>". number_format($res[$r][$i], 0) ."</div></td>\n";
                    break;
                case 4:                                 // 本稼働時間
                    $listTable .= "<td class='winbox' nowrap align='right'><div class='pt9'>". number_format($res[$r][$i], 0) ."</div></td>\n";
                    break;
                case 5:                                 // 修繕費
                    $listTable .= "<td class='winbox' nowrap align='right'><div class='pt9'>". number_format($res[$r][$i], 0) ."</div></td>\n";
                    break;
                default:
                    break;
            }
        }
        $listTable .= "        </tr>\n";
    }
    $listTable .= "        </TBODY>\n";
    $listTable .= "        </table>\n";
    $listTable .= "            </td></tr>\n";
    $listTable .= "        </table> <!----------------- ダミーEnd ------------------>\n";
    $listTable .= "    </form>\n";
    $listTable .= "</center>\n";
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
    $file_name = "list/assemblyRate_machineWork_List-{$_SESSION['User_ID']}.html";
    $handle = fopen($file_name, 'w');
    fwrite($handle, $listHTML);
    fclose($handle);
    chmod($file_name, 0666);       // fileを全てrwモードにする
}
