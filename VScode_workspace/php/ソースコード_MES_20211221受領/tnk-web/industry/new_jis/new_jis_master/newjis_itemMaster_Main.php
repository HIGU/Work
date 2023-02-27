<?php
//////////////////////////////////////////////////////////////////////////////
// 新JIS対象製品マスターの登録 メイン newjis_itemMaster_Main.php            //
// Copyright (C) 2014-2017 Norihisa.Ohya norihisa_ooya@nitto-kohki.co.jp    //
// Changed history                                                          //
// 2014/11/17 Created  newjis_itemMaster_Main.php                           //
// 2014/12/02 編集権限を39に設定                                            //
// 2014/12/08 品目→形式へ変更                                              //
// 2014/12/22 形式→型式へ変更                                              //
// 2017/04/27 各メニューの表示より『新JIS』を削除                      大谷 //
//////////////////////////////////////////////////////////////////////////////
//ini_set('error_reporting', E_ALL || E_STRICT);
session_start();                                 // ini_set()の次に指定すること Script 最上行

require_once ('../../../function.php');             // define.php と pgsql.php を require_once している
require_once ('../../../tnk_func.php');             // TNK に依存する部分の関数を require_once している
require_once ('../../../MenuHeader.php');           // TNK 全共通 menu class
require_once ('../../../ControllerHTTP_Class.php'); // TNK 全共通 MVC Controller Class
access_log();                                    // Script Name は自動取得

main();

function main()
{
    ///// TNK 共用メニュークラスのインスタンスを作成
    $menu = new MenuHeader(0);                  // 認証チェック0=一般以上 戻り先=TOP_MENU タイトル未設定
    //////////// タイトル名(ソースのタイトル名とフォームのタイトル名)
    $menu->set_title('対象製品マスターの登録');
    //////////// 戻先へのGETデータ設定
    $menu->set_retGET('page_keep', 'On');    
  
    $request = new Request;
    $result  = new Result;
    
    ////////////// リターンアドレス設定
    $menu->set_RetUrl($_SESSION['newjis_master_referer']);             // 通常は指定する必要はない
    
    get_serch_master($result, $request);                // グループマスターの取得
    get_product_master($result, $request);                          // 各種データの取得
    
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
    require_once ('newjis_itemMaster_View.php');

    ob_end_flush(); 
}

////////////// 処理の分岐を行う
function request_check($request, $result, $menu)
{
    $ok = true;
    if ($request->get('number') != '') $ok = productMaster_copy($request, $result);
    if ($request->get('del') != '') $ok = productMaster_del($request);
    if ($request->get('entry') != '')  $ok = productMaster_entry($request, $result);
    if ($ok) {
        ////// データの初期化
        $request->add('del', '');
        $request->add('entry', '');
        $request->add('number', '');
        $request->add('assy_no', '');
        $request->add('newjis_group_code', '');
        get_serch_master($result, $request);    // 各種データの取得
        get_product_master($result, $request); // 機械ワークデータマスターの取得
    }
}

////////////// 追加・変更ロジック (合計レコード数取得前に行う)
function productMaster_entry($request, $result)
{
    if (getCheckAuthority(39)) {                    // 認証チェック
        $assy_no = $request->get('assy_no');
        $newjis_group_code = $request->get('newjis_group_code');
        $newjis_group_name = get_group_name($newjis_group_code);
        $query = sprintf("SELECT assy_no FROM new_jis_item_master WHERE assy_no='%s'", $assy_no);
        $res_chk = array();
        if ( getResult($query, $res_chk) > 0 ) {    // 登録あり UPDATE 更新
            $query = sprintf("UPDATE new_jis_item_master SET assy_no='%s', newjis_group_code=%d, last_date=CURRENT_TIMESTAMP, last_user='%s' WHERE assy_no='%s'", $assy_no, $newjis_group_code, $_SESSION['User_ID'], $assy_no);
            if (query_affected($query) <= 0) {
                $_SESSION['s_sysmsg'] .= "製品番号：{$assy_no} を型式：{$newjis_group_name} へ変更失敗！";      // .= に注意
                $msg_flg = 'alert';
                return false;
            } else {
                $_SESSION['s_sysmsg'] .= "製品番号：{$assy_no} を型式：{$newjis_group_name} へ変更しました！"; // .= に注意
                return true;
            }
        } else {                                    // 登録なし INSERT 新規   
            $query = sprintf("INSERT INTO new_jis_item_master (assy_no, newjis_group_code, last_date, last_user)
                              VALUES ('%s', %d, CURRENT_TIMESTAMP, '%s')",
                                $assy_no, $newjis_group_code, $_SESSION['User_ID']);
            if (query_affected($query) <= 0) {
                $_SESSION['s_sysmsg'] .= "製品番号：{$assy_no} 型式：{$newjis_group_name} の追加に失敗！";      // .= に注意
                $msg_flg = 'alert';
                return false;
            } else {
                $_SESSION['s_sysmsg'] .= "製品番号：{$assy_no} 型式：{$newjis_group_name} を追加しました！";    // .= に注意
                return true;
            }
        }
    } else {                                        // 権限なしエラー
        $_SESSION['s_sysmsg'] .= "編集権限がありません。必要な場合には、担当者に連絡して下さい。";
        return false;
    }
}

////////////// 削除ロジック (合計レコード数取得前に行う)
function productMaster_del($request)
{
    if (getCheckAuthority(39)) {    // 認証チェック
        $assy_no = $request->get('assy_no');
        $newjis_group_code = $request->get('newjis_group_code');
        $newjis_group_name = get_group_name($newjis_group_code);
        $query = sprintf("DELETE FROM new_jis_item_master WHERE assy_no = '%s'", $assy_no);
        if (query_affected($query) <= 0) {
            $_SESSION['s_sysmsg'] .= "製品番号：{$assy_no} 型式：{$newjis_group_name} の削除に失敗！";   // .= に注意
            $msg_flg = 'alert';
            return false;
        } else {
            $_SESSION['s_sysmsg'] .= "製品番号：{$assy_no} 型式：{$newjis_group_name} を削除しました！"; // .= に注意
            return true;
        }
    } else {                        // 権限なしエラー
        $_SESSION['s_sysmsg'] .= "編集権限がありません。必要な場合には、担当者に連絡して下さい。";
        return false;
    }
}
////////////// 表示用(一覧表)のグループマスターデータをSQLで取得
function get_serch_master ($result, $request)
{
    $query_g = "
        SELECT  selectm.newjis_group_code              AS 支援先コード     -- 0
            ,   selectm.newjis_group_name              AS 支援先名         -- 1
        FROM
            new_jis_select_master AS selectm
        ORDER BY
            newjis_group_code
    ";

    $res_g = array();
    if (($rows_g = getResultWithField2($query_g, $field_g, $res_g)) <= 0) {
        $_SESSION['s_sysmsg'] = "型式の登録がありません！";
        $field_g[0]   = "型式コード";
        $field_g[1]   = "型式";
        $_SESSION['s_sysmsg'] = "登録がありません！";
        $result->add_array2('res_g', '');
        $result->add_array2('field_g', $field_g);
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
////////////// メッセージ表示用の支援先名取得
function get_group_name ($group_code)
{
    $query = sprintf("select newjis_group_name
                        FROM
                            new_jis_select_master
                        WHERE
                            newjis_group_code=%d
                        LIMIT 1
                        ", $group_code);
    getUniResult($query, $group_name);
    return $group_name;
}
////////////// 表示用(一覧表)のグループマスターデータをSQLで取得
function get_product_master ($result, $request)
{
    $query = "
        SELECT  selectm.assy_no                AS 製品番号     -- 0
            ,   selectm.newjis_group_code      AS 型式         -- 1
        FROM
            new_jis_item_master AS selectm
        ORDER BY
            selectm.newjis_group_code ASC, selectm.assy_no ASC
    ";

    $res = array();
    if (($rows = getResultWithField2($query, $field, $res)) <= 0) {
        $_SESSION['s_sysmsg'] = "製品番号の登録がありません！";
        $field[0]   = "製品番号";
        $field[1]   = "型式";
        $_SESSION['s_sysmsg'] = "登録がありません！";
        $result->add_array2('res', '');
        $result->add_array2('field', $field);
        $result->add('num', 1);
        $result->add('rows', '');
    } else {
        $num = count($field);
        $result->add_array2('res', $res);
        $result->add_array2('field', $field);
        $result->add('num', $num);
        $result->add('rows', $rows);
        $res_g = $result->get_array2('res_g');
        for ($r=0; $r<$rows; $r++) {    // グループ番号とグループ名の置き換え
            $group_name[$r] = "　";
            for ($i=0; $i<$result->get('rows_g'); $i++) {
                if($res[$r][1] == $res_g[$i][0]) {
                    $group_name[$r] = $res_g[$i][1];
                }
            }
        }
        $result->add_array2('group_name', $group_name);
    }
}

////////////// コピーのリンクが押された時
function productMaster_copy($request, $result)
{
    $res = $result->get_array2('res');
    $r = $request->get('number');
    $assy_no     = $res[$r][0];
    $newjis_group_code = $res[$r][1];
    $request->add('assy_no', $assy_no);
    $request->add('newjis_group_code', $newjis_group_code);
}

////////////// 照会用グループコード照会画面のHTMLの作成
function getViewHTMLbody($request, $menu, $result)
{
    $listTable = '';
    $listTable .= "<!DOCTYPE HTML PUBLIC '-//W3C//DTD HTML 4.01 Transitional//EN'>\n";
    $listTable .= "<html>\n";
    $listTable .= "<head>\n";
    $listTable .= "<meta http-equiv='Content-Type' content='text/html; charset=EUC-JP'>\n";
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
    $listTable .= ".pt11 {\n";
    $listTable .= "    font-size:          11pt;\n";
    $listTable .= "    font-weight:        normal;\n";
    $listTable .= "    font-family:        monospace;\n";
    $listTable .= "}\n";
    $listTable .= ".pt11b {\n";
    $listTable .= "    font-size:          11pt;\n";
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
    $listTable .= "    <form name='entry_form' action='newjis_itemMaster_Main.php' method='post' target='_parent'>\n";
    $listTable .= "        <table bgcolor='#d6d3ce' width='150' align='center' border='1' cellspacing='0' cellpadding='3'>\n";
    $listTable .= "            <tr><td> <!----------- ダミー(デザイン用) ------------>\n";
    $listTable .= "        <table class='winbox_field' width='100%' bgcolor='#d6d3ce' align='center' border='1' cellspacing='0' cellpadding='3'>\n";
    $listTable .= "        <THEAD>\n";
    $listTable .= "            <!-- テーブル ヘッダーの表示 -->\n";
    $listTable .= "            <tr>\n";
    $listTable .= "                <td bgcolor='#ffffc6' align='center' colspan='4' nowrap>\n";
    $listTable .= "                対象製品マスター\n";
    $listTable .= "                </td>\n";
    $listTable .= "            </tr>\n";
    $listTable .= "            <tr>\n";
    $listTable .= "                <th class='winbox' nowrap width='10'>No</th>        <!-- 行ナンバーの表示 -->\n";
    $field = $result->get_array2('field');
    for ($i=0; $i<$result->get('num'); $i++) {        // フィールド数分繰返し\n";
        $listTable .= "            <th class='winbox' nowrap>". $field[$i] ."</th>\n";
    }
    $listTable .= "            </tr>\n";
    $listTable .= "        </THEAD>\n";
    $listTable .= "        <TFOOT>\n";
    $listTable .= "            <!-- 現在はフッターは何もない -->\n";
    $listTable .= "        </TFOOT>\n";
    $listTable .= "        <TBODY>\n";
    for ($r=0; $r<$result->get('rows'); $r++) {
        $listTable .= "        <tr>\n";
        $listTable .= "            <td class='winbox' nowrap align='right'>    <!-- 削除変更用に入力欄にコピー  -->\n";
        $listTable .= "                <a href='../newjis_itemMaster_Main.php?number=". $r ."' target='_parent' style='text-decoration:none;'>\n";
        $cnum = $r + 1;
        $listTable .= "                ". $cnum ."\n";
        $listTable .= "                </a>\n";
        $listTable .= "            </td>\n";
        $res = $result->get_array2('res');
        $group_name = $result->get_array2('group_name');
        for ($i=0; $i<$result->get('num'); $i++) {    // レコード数分繰返し
            switch ($i) {
                case 0:                                 // 製品番号
                    $listTable .= "<td class='winbox' nowrap align='center'><div class='pt11'>". $res[$r][$i] ."</div></td>\n";
                break;
                case 1:                                 // 型式
                    $listTable .= "<td class='winbox' nowrap align='left'><div class='pt11b'>". $group_name[$r] ."</div></td>\n";
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

////////////// 照会用製品グループ一覧表示のHTMLを出力
function outViewListHTML($request, $menu, $result)
{
    $listHTML = getViewHTMLbody($request, $menu, $result);
    $request->add('view_flg', '照会');
    ////////// HTMLファイル出力
    $file_name = "list/newjis_itemMaster_List-{$_SESSION['User_ID']}.html";
    $handle = fopen($file_name, 'w');
    fwrite($handle, $listHTML);
    fclose($handle);
    chmod($file_name, 0666);    // fileを全てrwモードにする
}
