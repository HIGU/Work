<?php
//////////////////////////////////////////////////////////////////////////////
// 機械マスター編集 メイン pressTool_machine_master_Main.php                //
// Copyright (C) 2011 Norihisa.Ooya usoumu@nitto-kohki.co.jp                //
// Changed history                                                          //
// 2011/09/28 Created  pressTool_machine_master_Main.php                    //
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
    ///// TNK 共用メニュークラスのインスタンスを作成
    $menu = new MenuHeader(0);                  // 認証チェック0=一般以上 戻り先=TOP_MENU タイトル未設定
    //////////// タイトル名(ソースのタイトル名とフォームのタイトル名)
    $menu->set_title('機械マスターの編集');
    //////////// 戻先へのGETデータ設定
    $menu->set_retGET('page_keep', 'On');    
  
    $request = new Request;
    $result  = new Result;
    
    ////////////// リターンアドレス設定
    //$menu->set_RetUrl($_SESSION['various_referer'] . '?wage_ym=' . $request->get('wage_ym'));             // 通常は指定する必要はない
    
    get_group_master($result, $request);                          // 各種データの取得
    
    request_check($request, $result, $menu);           // 処理の分岐チェック
    
    outViewListHTML($request, $menu, $result);    // HTML作成
    
    display($menu, $request, $result);          // 画面表示
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
    require_once ('pressTool_machine_master_View.php');

    ob_end_flush(); 
}

////////////// 処理の分岐を行う
function request_check($request, $result, $menu)
{
    $ok = true;
    if ($request->get('number') != '') $ok = groupMaster_copy($request, $result);
    if ($request->get('del') != '') $ok = groupMaster_del($request);
    if ($request->get('entry') != '')  $ok = groupMaster_entry($request, $result);
    if ($ok) {
        ////// データの初期化
        $request->add('del', '');
        $request->add('entry', '');
        $request->add('number', '');
        $request->add('machine_no', '');
        $request->add('machine_name', '');
        get_group_master($result, $request);    // 各種データの取得
    }
}

////////////// 追加・変更ロジック (合計レコード数取得前に行う)
function groupMaster_entry($request, $result)
{
    if (getCheckAuthority(22)) {                    // 認証チェック
        $machine_no = $request->get('machine_no');
        $machine_name = $request->get('machine_name');
        $query = sprintf("SELECT machine_no FROM press_tool_machine_master WHERE machine_no=%d", $machine_no);
        $res_chk = array();
        if ( getResult($query, $res_chk) > 0 ) {    // 登録あり UPDATE 更新
            $query = sprintf("UPDATE press_tool_machine_master SET machine_no=%d, machine_name='%s', last_date=CURRENT_TIMESTAMP, last_user='%s' WHERE machine_no=%d", $machine_no, $machine_name, $_SESSION['User_ID'], $machine_no);
            if (query_affected($query) <= 0) {
                $_SESSION['s_sysmsg'] .= "機械番号：{$machine_no} の機械名称を{$machine_name}へ変更失敗！";      // .= に注意
                $msg_flg = 'alert';
                return false;
            } else {
                $_SESSION['s_sysmsg'] .= "機械番号：{$machine_no}の 機械名称を{$machine_name}に変更しました！"; // .= に注意
                return true;
            }
        } else {                                    // 登録なし INSERT 新規   
            $query = sprintf("INSERT INTO press_tool_machine_master (machine_no, machine_name, last_date, last_user)
                              VALUES (%d, '%s', CURRENT_TIMESTAMP, '%s')",
                                $machine_no, $machine_name, $_SESSION['User_ID']);
            if (query_affected($query) <= 0) {
                $_SESSION['s_sysmsg'] .= "機械番号：{$machine_no} 機械名称：{$machine_name}の追加に失敗！";      // .= に注意
                $msg_flg = 'alert';
                return false;
            } else {
                $_SESSION['s_sysmsg'] .= "機械番号：{$machine_no} 機械名称：{$machine_name}を追加しました！";    // .= に注意
                return true;
            }
        }
    } else {                                        // 権限なしエラー
        $_SESSION['s_sysmsg'] .= "編集権限がありません。必要な場合には、担当者に連絡して下さい。";
        return false;
    }
}

////////////// 削除ロジック (合計レコード数取得前に行う)
function groupMaster_del($request)
{
    if (getCheckAuthority(22)) {    // 認証チェック
        $machine_no = $request->get('machine_no');
        $machine_name = $request->get('machine_name');
        $query = sprintf("SELECT * FROM press_tool_stok_master WHERE machine_no=%d LIMIT 1", $machine_no);
        $res_chk = array();
        if (getResult($query, $res_chk) > 0) {    // 登録あり
            $_SESSION['s_sysmsg'] .= "この機械番号はすでに他のマスターで使用されています！";
            return false;
        }
        $query = sprintf("SELECT * FROM press_tool_use_history WHERE machine_no=%d LIMIT 1", $machine_no);
        $res_chk = array();
        if (getResult($query, $res_chk) > 0) {    // 登録あり
            $_SESSION['s_sysmsg'] .= "この機械番号はすでに他のマスターで使用されています！";
            return false;
        }
        $query = sprintf("SELECT * FROM press_tool_stok_money WHERE machine_no=%d LIMIT 1", $machine_no);
        $res_chk = array();
        if (getResult($query, $res_chk) > 0) {    // 登録あり
            $_SESSION['s_sysmsg'] .= "この機械番号はすでに他のマスターで使用されています！";
            return false;
        }
        $query = sprintf("DELETE FROM press_tool_machine_master WHERE machine_no = %d", $machine_no);
        if (query_affected($query) <= 0) {
            $_SESSION['s_sysmsg'] .= "機械番号：{$machine_no} 機械名称：{$machine_name}の削除に失敗！";   // .= に注意
            $msg_flg = 'alert';
            return false;
        } else {
            $_SESSION['s_sysmsg'] .= "機械番号：{$machine_no} 機械名称：{$machine_name}を削除しました！"; // .= に注意
            return true;
        }
    } else {                        // 権限なしエラー
        $_SESSION['s_sysmsg'] .= "編集権限がありません。必要な場合には、担当者に連絡して下さい。";
        return false;
    }
}
////////////// 表示用(一覧表)の機械マスターデータをSQLで取得
function get_group_master ($result, $request)
{
    $query_g = "
        SELECT  groupm.machine_no                AS 機械番号     -- 0
            ,   groupm.machine_name              AS 機械名称       -- 1
        FROM
            press_tool_machine_master AS groupm
        ORDER BY
            machine_no
    ";

    $res_g = array();
    if (($rows_g = getResultWithField2($query_g, $field_g, $res_g)) <= 0) {
        $_SESSION['s_sysmsg'] = "機械の登録がありません！";
        $field[0]   = "機械番号";
        $field[1]   = "機械名称";
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

////////////// コピーのリンクが押された時
function groupMaster_copy($request, $result)
{
    $res_g = $result->get_array2('res_g');
    $r = $request->get('number');
    $machine_no   = $res_g[$r][0];
    $machine_name = $res_g[$r][1];
    $request->add('machine_no', $machine_no);
    $request->add('machine_name', $machine_name);
}

////////////// グループマスター照会画面のHTMLの作成
function getViewHTMLbody($request, $menu, $result)
{
    $listTable = '';
    $listTable .= "<!DOCTYPE HTML PUBLIC '-//W3C//DTD HTML 4.01 Transitional//EN'>\n";
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
    $listTable .= "    a:active {\n";
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
    $listTable .= "    <form name='entry_form' action='pressTool_machine_master_Main.php' method='post' target='_parent'>\n";
    $listTable .= "        <table bgcolor='#d6d3ce' width='150' align='center' border='1' cellspacing='0' cellpadding='3'>\n";
    $listTable .= "            <tr><td> <!----------- ダミー(デザイン用) ------------>\n";
    $listTable .= "        <table class='winbox_field' width='100%' bgcolor='#d6d3ce' align='center' border='1' cellspacing='0' cellpadding='3'>\n";
    $listTable .= "        <THEAD>\n";
    $listTable .= "            <!-- テーブル ヘッダーの表示 -->\n";
    $listTable .= "            <tr>\n";
    $listTable .= "                <td width='700' bgcolor='#ffffc6' align='center' colspan='15'>\n";
    $listTable .= "                機械マスター\n";
    $listTable .= "                </td>\n";
    $listTable .= "            </tr>\n";
    $listTable .= "            <tr>\n";
    $listTable .= "                <th class='winbox' nowrap width='10'>No</th>        <!-- 行ナンバーの表示 -->\n";
    $field_g = $result->get_array2('field_g');
    for ($i=0; $i<$result->get('num_g'); $i++) {        // フィールド数分繰返し\n";
        $listTable .= "            <th class='winbox' nowrap>". $field_g[$i] ."</th>\n";
    }
    $listTable .= "            </tr>\n";
    $listTable .= "        </THEAD>\n";
    $listTable .= "        <TFOOT>\n";
    $listTable .= "            <!-- 現在はフッターは何もない -->\n";
    $listTable .= "        </TFOOT>\n";
    $listTable .= "        <TBODY>\n";
    for ($r=0; $r<$result->get('rows_g'); $r++) {
        $listTable .= "        <tr>\n";
        $listTable .= "            <td class='winbox' nowrap align='right'>    <!-- 削除変更用に入力欄にコピー  -->\n";
        $listTable .= "                <a href='../pressTool_machine_master_Main.php?number=". $r ."' target='_parent' style='text-decoration:none;'>\n";
        $cnum = $r + 1;
        $listTable .= "                ". $cnum ."\n";
        $listTable .= "                </a>\n";
        $listTable .= "            </td>\n";
        $res_g = $result->get_array2('res_g');
        for ($i=0; $i<$result->get('num_g'); $i++) {    // レコード数分繰返し
            switch ($i) {
                case 0:                                 // 機械番号
                    $listTable .= "<td class='winbox' nowrap align='center'><div class='pt9'>". $res_g[$r][$i] ."</div></td>\n";
                break;
                case 1:                                 // 機械名称
                    $listTable .= "<td class='winbox' nowrap align='left'><div class='pt9'>". $res_g[$r][$i] ."</div></td>\n";
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
    $file_name = "list/pressTool_machine_master_List-{$_SESSION['User_ID']}.html";
    $handle = fopen($file_name, 'w');
    fwrite($handle, $listHTML);
    fclose($handle);
    chmod($file_name, 0666);    // fileを全てrwモードにする
}
