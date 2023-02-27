<?php
//////////////////////////////////////////////////////////////////////////////
// 配賦率計算データ編集 メイン assemblyRate_costAllocation_Main.php         //
//                             (旧 cost_allocation_rate_main.php)           //
// Copyright (C) 2007-2011 Norihisa.Ooya usoumu@nitto-kohki.co.jp           //
// Changed history                                                          //
// 2007/12/14 Created  assemblyRate_costAllocation_Main.php                 //
//            旧ファイルより各処理を関数化 コメントの位置の調整             //
//            余分な<font>タグの削除                                        //
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
    $menu->set_title('配賦率計算データ編集');
  
    $request = new Request;
    $result  = new Result;
    
    ////////////// リターンアドレス設定
    $menu->set_RetUrl($_SESSION['various_referer'] . '?wage_ym=' . $request->get('wage_ym'));             // 通常は指定する必要はない
    
    //before_date($request, $result);                     // 前月履歴表示の為の前月計算
    //get_costAllocation_master($result, $request);       // 配賦率計算データマスターの取得
    //get_costAllocationBefore_master($result, $request); // 前月分配賦率計算データマスターの取得
    
    //request_check($request, $result, $menu);            // 処理の分岐チェック
    
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
    require_once ('assemblyRate_costAllocation_View.php');

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
    if ($request->get('number') != '') $ok = costAllocation_copy($request, $result);
    if ($request->get('del') != '') $ok = costAllocation_del($request);
    if ($request->get('entry') != '')  $ok = costAllocation_entry($request, $result);
    if ($ok) {
        ////// データの初期化
        $request->add('del', '');
        $request->add('entry', '');
        $request->add('number', '');
        $request->add('total_date', '');
        $request->add('item', '');
        $request->add('external_price', '');
        $request->add('external_assy_price', '');
        $request->add('direct_expense', '');
        get_costAllocation_master($result, $request); // 配賦率計算データマスターの取得
    }
}

////////////// 追加・変更ロジック (合計レコード数取得前に行う)
function costAllocation_entry($request, $result)
{
    if (getCheckAuthority(22)) {                            // 認証チェック
        $total_date = $request->get('wage_ym');
        $query = sprintf("SELECT indirect_cost FROM indirect_cost_allocate WHERE total_date=%d", $total_date);
        $res_check = array();
        $rows_check = getResult($query,$res_check);
        if ($rows_check <= 0) {                             // 配賦率が登録済みかチェック
            $item = $request->get('item');
            $external_price = $request->get('external_price');
            $external_assy_price = $request->get('external_assy_price');
            $direct_expense = $request->get('direct_expense');
            $query = sprintf("SELECT total_date, item FROM indirect_cost_allocate WHERE total_date=%d AND item='%s'", $total_date, $item);
            $res_chk = array();
            if ( getResult($query, $res_chk) > 0 ) {        // 登録あり UPDATE 更新
                $query = sprintf("UPDATE indirect_cost_allocate SET total_date=%d, item='%s', external_price=%d, external_assy_price=%d, direct_expense=%d, last_date=CURRENT_TIMESTAMP, last_user='%s' 
                                    WHERE total_date=%d AND item='%s'", $total_date, $item, $external_price, $external_assy_price, $direct_expense, $_SESSION['User_ID'], $total_date, $item);
                if (query_affected($query) <= 0) {
                    $_SESSION['s_sysmsg'] .= "対象月{$total_date} 対象グループ：{$item}のデータ変更失敗！";               // .= に注意
                    $msg_flg = 'alert';
                    return false;
                } else {
                    $_SESSION['s_sysmsg'] .= "対象月{$total_date} 対象グループ：{$item}のデータを変更しました！";         // .= に注意
                    return true;
                }
            } else {                                        // 登録なし INSERT 新規   
                $query = sprintf("INSERT INTO indirect_cost_allocate (total_date, item, external_price, external_assy_price, direct_expense, last_date, last_user)
                             VALUES (%d, '%s', %d, %d, %d, CURRENT_TIMESTAMP, '%s')",
                             $total_date, $item, $external_price, $external_assy_price, $direct_expense, $_SESSION['User_ID']);
                if (query_affected($query) <= 0) {
                    $_SESSION['s_sysmsg'] .= "対象月{$total_date} 対象グループ：{$item}のデータ登録に失敗！";             // .= に注意
                    $msg_flg = 'alert';
                    return false;
                } else {
                    $_SESSION['s_sysmsg'] .= "対象月{$total_date} 対象グループ：{$item}のデータ登録を追加しました！";     // .= に注意
                    return true;
                }
            }
        } else {
            if ($res_check[0]['indirect_cost'] == '') {     // 配賦率が登録済みかチェック
                $item = $request->get('item');
                $external_price = $request->get('external_price');
                $external_assy_price = $request->get('external_assy_price');
                $direct_expense = $request->get('direct_expense');
                $query = sprintf("SELECT total_date, item FROM indirect_cost_allocate WHERE total_date=%d AND item='%s'", $total_date, $item);
                $res_chk = array();
                if ( getResult($query, $res_chk) > 0 ) {    // 登録あり UPDATE 更新
                    $query = sprintf("UPDATE indirect_cost_allocate SET total_date=%d, item='%s', external_price=%d, external_assy_price=%d, direct_expense=%d, last_date=CURRENT_TIMESTAMP, last_user='%s' 
                                        WHERE total_date=%d AND item='%s'", $total_date, $item, $external_price, $external_assy_price, $direct_expense, $_SESSION['User_ID'], $total_date, $item);
                    if (query_affected($query) <= 0) {
                        $_SESSION['s_sysmsg'] .= "対象月{$total_date} 対象グループ：{$item}のデータ変更失敗！";           // .= に注意
                        $msg_flg = 'alert';
                        return false;
                    } else {
                        $_SESSION['s_sysmsg'] .= "対象月{$total_date} 対象グループ：{$item}のデータを変更しました！";     // .= に注意
                        return true;
                    }
                } else {                                    // 登録なし INSERT 新規   
                    $query = sprintf("INSERT INTO indirect_cost_allocate (total_date, item, external_price, external_assy_price, direct_expense, last_date, last_user)
                                 VALUES (%d, '%s', %d, %d, %d, CURRENT_TIMESTAMP, '%s')",
                                 $total_date, $item, $external_price, $external_assy_price, $direct_expense, $_SESSION['User_ID']);
                    if (query_affected($query) <= 0) {
                        $_SESSION['s_sysmsg'] .= "対象月{$total_date} 対象グループ：{$item}のデータ登録に失敗！";         // .= に注意
                        $msg_flg = 'alert';
                        return false;
                    } else {
                        $_SESSION['s_sysmsg'] .= "対象月{$total_date} 対象グループ：{$item}のデータ登録を追加しました！"; // .= に注意
                        return true;
                    }
                }
            } else {
                $_SESSION['s_sysmsg'] .= "配賦率がすでに確定されています。";
                return false;
            }
        }
    } else {                                                // 認証なしエラー
        $_SESSION['s_sysmsg'] .= "編集権限がありません。必要な場合には、担当者に連絡して下さい。";
        return false;
    }
}

////////////// 削除ロジック (合計レコード数取得前に行う)
function costAllocation_del($request)
{
    if (getCheckAuthority(22)) {                           // 認証チェック
        $total_date = $request->get('wage_ym');
        $query = sprintf("SELECT indirect_cost FROM indirect_cost_allocate WHERE total_date=%d", $total_date);
        $res_check = array();
        $rows_check = getResult($query,$res_check);
        if ($rows_check <= 0) {                            // 配賦率が登録済みかチェック
            $item = $request->get('item');
            $query = sprintf("DELETE FROM indirect_cost_allocate WHERE total_date=%d AND item='%s'", $total_date, $item);
            if (query_affected($query) <= 0) {
                $_SESSION['s_sysmsg'] .= "対象月{$total_date} 対象グループ：{$item}の削除に失敗！";       // .= に注意
                $msg_flg = 'alert';
                return false;
            } else {
                $_SESSION['s_sysmsg'] .= "対象月{$total_date} 対象グループ：{$item}を削除しました！";     // .= に注意
                return true;
            }
        } else {
            if ($res_check[0]['indirect_cost'] == '') {    // 配賦率が登録済みかチェック
                $item = $request->get('item');
                $query = sprintf("DELETE FROM indirect_cost_allocate WHERE total_date=%d AND item='%s'", $total_date, $item);
                if (query_affected($query) <= 0) {
                    $_SESSION['s_sysmsg'] .= "対象月{$total_date} 対象グループ：{$item}の削除に失敗！";   // .= に注意
                    $msg_flg = 'alert';
                    return false;
                } else {
                    $_SESSION['s_sysmsg'] .= "対象月{$total_date} 対象グループ：{$item}を削除しました！"; // .= に注意
                    return true;
                }
            } else {
                $_SESSION['s_sysmsg'] .= "配賦率がすでに確定されています。";
                return false;
            }
        }
    } else {                                               // 認証なしエラー
        $_SESSION['s_sysmsg'] .= "編集権限がありません。必要な場合には、担当者に連絡して下さい。";
        return false;
    }
}

////////////// 表示用(一覧表)の配賦率計算データをSQLで取得
function get_costAllocation_master ($result, $request)
{
    $wage_ym = $request->get('wage_ym');
    $query = "
        SELECT  incost.total_date            AS 集計年月         -- 0
            ,   incost.item                  AS 対象グループ     -- 1
            ,   incost.external_price        AS 外注費           -- 2
            ,   incost.external_assy_price   AS 外注Assy費       -- 3
            ,   incost.direct_expense        AS 直接費           -- 4
        FROM
            indirect_cost_allocate AS incost
        WHERE
            incost.total_date = $wage_ym
        ORDER BY
            item
    ";

    $res = array();
    $num = 0;
    if (($rows = getResultWithField2($query, $field, $res)) <= 0) {
        $_SESSION['s_sysmsg'] = "登録がありません！";
    } else {
        $num = count($field);
        $result->add_array2('res_c', $res);
        $result->add_array2('field_c', $field);
        $result->add('num_c', $num);
        $result->add('rows_c', $rows);
    }
}

////////////// 表示用(前月分)の配賦率計算データをSQLで取得
function get_costAllocationBefore_master($result, $request)
{
    $wage_ym_b = $result->get('wage_ym_b');
    $query = "
        SELECT  incostb.total_date            AS 集計年月         -- 0
            ,   incostb.item                  AS 対象グループ     -- 1
            ,   incostb.external_price        AS 外注費           -- 2
            ,   incostb.external_assy_price   AS 外注Assy費       -- 3
            ,   incostb.direct_expense        AS 直接費           -- 4
        FROM
            indirect_cost_allocate AS incostb
        WHERE
            incostb.total_date = $wage_ym_b
        ORDER BY
            item
    ";

    $res = array();
    $num = 0;
    if (($rows = getResultWithField2($query, $field, $res)) <= 0) {
        $_SESSION['s_sysmsg'] = "登録がありません！";
    } else {
        $num = count($field);
        $result->add_array2('res_b', $res);
        $result->add_array2('field_b', $field);
        $result->add('num_b', $num);
        $result->add('rows_b', $rows);
    }
}

////////////// コピーのリンクが押された時
function costAllocation_copy($request, $result)
{
    $r = $request->get('number');
    $res = $result->get_array2('res_c');
    $total_date            = $res[$r][0];
    $item                  = $res[$r][1];
    $external_price        = $res[$r][2];
    $external_assy_price   = $res[$r][3];
    $direct_expense        = $res[$r][4];
    
    $request->add('total_date', $total_date);
    $request->add('item', $item);
    $request->add('external_price', $external_price);
    $request->add('external_assy_price', $external_assy_price);
    $request->add('direct_expense', $direct_expense);
}

////////////// 配賦率計算データ照会画面のHTMLの作成
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
    $listTable .= "    <form name='entry_form' action='assemblyRate_costAllocation_Main.php' method='post'>\n";
    $listTable .= "        <table bgcolor='#d6d3ce' width='300' align='center' border='1' cellspacing='0' cellpadding='3'>\n";
    $listTable .= "            <tr><td> <!----------- ダミー(デザイン用) ------------>\n";
    $listTable .= "        <table class='winbox_field' width='100%' bgcolor='#d6d3ce' align='center' border='1' cellspacing='0' cellpadding='3'>\n";
    $listTable .= "        <THEAD>\n";
    $listTable .= "            <!-- テーブル ヘッダーの表示 -->\n";
    $listTable .= "            <tr>\n";
    $listTable .= "               <td bgcolor='#ffffc6' align='center' colspan='20'>\n";
    $wage_ym = $request->get('wage_ym');
    $listTable .= "                   ". format_date6_kan($wage_ym) ."\n";
    $listTable .= "                   配賦率計算データ\n";
    $listTable .= "                   <font size=2>\n";
    $listTable .= "                   (単位:円)\n";
    $listTable .= "                   </font>\n";
    $listTable .= "                </td>\n";
    $listTable .= "            </tr>\n";
    $listTable .= "            <tr>\n";
    $listTable .= "                <th class='winbox' nowrap width='10'>No</th>        <!-- 行ナンバーの表示 -->\n";
    $field = array(
        "集計年月",
        "対象グループ",
        "外注費",
        "外注Assy費",
        "直接費",
    );
    for ($i=0; $i<count($field); $i++) {    // フィールド数分繰返し\n";
        if ($i == 1) {
        } else {
            $listTable .= "        <th class='winbox' nowrap>". $field[$i] ."</th>\n";
        }
    }
    $listTable .= "            </tr>\n";
    $listTable .= "        </THEAD>\n";
    $listTable .= "        <TFOOT>\n";
    $listTable .= "            <!-- 現在はフッターは何もない -->\n";
    $listTable .= "        </TFOOT>\n";
    $listTable .= "        <TBODY>\n";
    $listTable .= "            <!--  bgcolor='#ffffc6' 薄い黄色 -->\n";
    $listTable .= "            <!-- サンプル<td rowspan='2' colspan='3' width='200' align='center' class='pt10b' bgcolor='#ffffc6'>  </td> -->\n";
    $res = array(
        [202201, "dummy", 1,  1],
        [202202, "dummy", 2,  2],
        [202203, "dummy", 3,  3],
        [202204, "dummy", 4,  4],
        [202205, "dummy", 5,  5],
        [202206, "dummy", 6,  6],
        [202207, "dummy", 7,  7],
        [202208, "dummy", 8,  8],
        [202209, "dummy", 9,  9],
        [202210, "dummy", 10, 10],
    );
    $group_name = array(
        "test1", 
        "test2", 
        "test3", 
        "test4", 
        "test5", 
        "test6", 
        "test7", 
        "test8", 
        "test9", 
        "test10",
    );
    for ($r=0; $r<count($group_name); $r++) {
        $listTable .= "        <tr>\n";
        $listTable .= "            <td class='winbox' nowrap align='right'>\n";
        $listTable .= "                <a href='../assemblyRate_costAllocation_Main.php?number=". $r ."&wage_ym=". $request->get('wage_ym') ."' target='_parent' style='text-decoration:none;'>\n";
        $cnum = $r + 1;
        $listTable .= "                ". $cnum ."\n";
        $listTable .= "                </a>\n";
        $listTable .= "            </td>    <!-- 行ナンバーの表示 -->\n";
        for ($i=0; $i<count($res); $i++) {    // レコード数分繰返し
            switch ($i) {
                case 0:                                 // 集計年月
                    break;
                case 1:                                 // 対象グループ
                    $listTable .= "<td class='winbox' nowrap align='left'><div class='pt9'>". $res[$r][$i] ."</div></td>\n";
                    break;
                case 2:                                 // 外注費
                    $listTable .= "<td class='winbox' nowrap align='right'><div class='pt9'>". number_format($res[$r][$i], 0) ."</div></td>\n";
                    break;
                case 3:                                 // 外注ASSY費
                    $listTable .= "<td class='winbox' nowrap align='right'><div class='pt9'>". number_format($res[$r][$i], 0) ."</div></td>\n";
                    break;
                case 4:                                 // 直接費
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
    $listTable .= "    <form name='entry_form' action='assemblyRate_costAllocation_Main.php' method='post'>\n";
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
    $listTable .= "                   配賦率計算データ\n";
    $listTable .= "                   <font size=2>\n";
    $listTable .= "                   (単位:円)\n";
    $listTable .= "                   </font>\n";
    $listTable .= "                </td>\n";
    $listTable .= "            </tr>\n";
    $listTable .= "            <tr>\n";
    $listTable .= "                <th class='winbox' nowrap width='10'>No</th>        <!-- 行ナンバーの表示 -->\n";
    for ($i=0; $i<count($field); $i++) {    // フィールド数分繰返し\n";
        if ($i == 1) {
        } else {
            $listTable .= "        <th class='winbox' nowrap>". $field[$i] ."</th>\n";
        }
    }
    $listTable .= "            </tr>\n";
    $listTable .= "        </THEAD>\n";
    $listTable .= "        <TFOOT>\n";
    $listTable .= "            <!-- 現在はフッターは何もない -->\n";
    $listTable .= "        </TFOOT>\n";
    $listTable .= "        <TBODY>\n";
    $listTable .= "            <!--  bgcolor='#ffffc6' 薄い黄色 -->\n";
    $listTable .= "            <!-- サンプル<td rowspan='2' colspan='3' width='200' align='center' class='pt10b' bgcolor='#ffffc6'>  </td> -->\n";
    for ($r=0; $r<count($group_name); $r++) {
        $listTable .= "        <tr>\n";
        $listTable .= "            <td class='winbox' nowrap align='right'>\n";
        $cnum = $r + 1;
        $listTable .= "            ". $cnum ."\n";
        $listTable .= "            </td>    <!-- 行ナンバーの表示 -->\n";
        for ($i=0; $i<count($res); $i++) {    // レコード数分繰返し
            switch ($i) {
                case 0:                                 // 集計年月
                    break;
                case 1:                                 // 対象グループ
                    $listTable .= "<td class='winbox' nowrap align='left'><div class='pt9'>". $res[$r][$i] ."</div></td>\n";
                    break;
                case 2:                                 // 外注費
                    $listTable .= "<td class='winbox' nowrap align='right'><div class='pt9'>". number_format($res[$r][$i], 0) ."</div></td>\n";
                    break;
                case 3:                                 // 外注ASSY費
                    $listTable .= "<td class='winbox' nowrap align='right'><div class='pt9'>". number_format($res[$r][$i], 0) ."</div></td>\n";
                    break;
                case 4:                                 // 直接費
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

////////////// 配賦率計算データ照会画面のHTMLを出力
function outViewListHTML($request, $menu, $result)
{
    $listHTML = getViewHTMLbody($request, $menu, $result);
    $request->add('view_flg', '照会');
    ////////// HTMLファイル出力
    $file_name = "list/assemblyRate_costAllocation_List-test.html";
    $handle = fopen($file_name, 'w');
    fwrite($handle, $listHTML);
    fclose($handle);
    chmod($file_name, 0666);       // fileを全てrwモードにする
}
