<?php
//////////////////////////////////////////////////////////////////////////////
// 品目名の登録 メイン newjis_groupMaster_Main.php                          //
// Copyright (C) 2014-     Norihisa.Ohya norihisa_ooya@nitto-kohki.co.jp    //
// Changed history                                                          //
// 2014/11/17 Created  newjis_groupMaster_Main.php                          //
// 2014/12/02 編集権限を39に設定                                            //
// 2014/12/08 品目→形式へ変更                                              //
// 2014/12/22 形式→型式へ変更                                              //
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
    $menu->set_title('型式の登録');
    //////////// 戻先へのGETデータ設定
    $menu->set_retGET('page_keep', 'On');    
  
    $request = new Request;
    $result  = new Result;
    
    ////////////// リターンアドレス設定
    $menu->set_RetUrl($_SESSION['newjis_master_referer']);             // 通常は指定する必要はない
    
    get_serch_master($result, $request);                          // 各種データの取得
    
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
    require_once ('newjis_groupMaster_View.php');

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
        $request->add('newjis_group_code', '');
        $request->add('newjis_apply_code', '');
        $request->add('newjis_kind_name', '');
        $request->add('newjis_certification_code', '');
        $request->add('newjis_period_ym', '');
        $request->add('newjis_group_name', '');
        get_serch_master($result, $request);    // 各種データの取得
    }
}

////////////// 追加・変更ロジック (合計レコード数取得前に行う)
function productMaster_entry($request, $result)
{
    if (getCheckAuthority(39)) {                    // 認証チェック
        $newjis_group_code          = $request->get('newjis_group_code');
        $newjis_apply_code          = $request->get('newjis_apply_code');
        $newjis_kind_name           = $request->get('newjis_kind_name');
        $newjis_certification_code  = $request->get('newjis_certification_code');
        $newjis_period_ym           = $request->get('newjis_period_ym');
        $newjis_group_name          = $request->get('newjis_group_name');
        $query = sprintf("SELECT newjis_group_code FROM new_jis_select_master WHERE newjis_group_code=%d", $newjis_group_code);
        $res_chk = array();
        if ( getResult($query, $res_chk) > 0 ) {    // 登録あり UPDATE 更新
            $query = sprintf("UPDATE new_jis_select_master SET newjis_group_code=%d, newjis_apply_code='%s', newjis_kind_name='%s', newjis_certification_code='%s', newjis_period_ym='%s', newjis_group_name='%s', last_date=CURRENT_TIMESTAMP, last_user='%s' WHERE newjis_group_code=%d", $newjis_group_code, $newjis_apply_code, $newjis_kind_name, $newjis_certification_code, $newjis_period_ym, $newjis_group_name, $_SESSION['User_ID'], $newjis_group_code);
            if (query_affected($query) <= 0) {
                $_SESSION['s_sysmsg'] .= "型式コード：{$newjis_group_code} の型式を{$newjis_group_name}へ変更失敗！";      // .= に注意
                $msg_flg = 'alert';
                return false;
            } else {
                $_SESSION['s_sysmsg'] .= "型式コード：{$newjis_group_code}の 型式を{$newjis_group_name}に変更しました！"; // .= に注意
                return true;
            }
        } else {                                    // 登録なし INSERT 新規   
            $query = sprintf("INSERT INTO new_jis_select_master (newjis_group_code, newjis_apply_code, newjis_kind_name, newjis_certification_code, newjis_period_ym, newjis_group_name, last_date, last_user)
                              VALUES (%d, '%s', '%s', '%s', '%s', '%s', CURRENT_TIMESTAMP, '%s')",
                                $newjis_group_code, $newjis_apply_code, $newjis_kind_name, $newjis_certification_code, $newjis_period_ym, $newjis_group_name, $_SESSION['User_ID']);
            if (query_affected($query) <= 0) {
                $_SESSION['s_sysmsg'] .= "型式コード：{$newjis_group_code} 型式：{$newjis_group_name}の追加に失敗！";      // .= に注意
                $msg_flg = 'alert';
                return false;
            } else {
                $_SESSION['s_sysmsg'] .= "型式コード：{$newjis_group_code} 型式：{$newjis_group_name}を追加しました！";    // .= に注意
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
        $newjis_group_code = $request->get('newjis_group_code');
        $newjis_group_name = $request->get('newjis_group_name');
        $query = sprintf("SELECT * FROM new_jis_item_master WHERE newjis_group_code=%d LIMIT 1", $newjis_group_code);
        $res_chk = array();
        if (getResult($query, $res_chk) > 0) {    // 登録あり
            $_SESSION['s_sysmsg'] .= "この支援先コードはすでに他のマスターで使用されています！";
            return false;
        }
        $query = sprintf("DELETE FROM new_jis_select_master WHERE newjis_group_code = %d", $newjis_group_code);
        if (query_affected($query) <= 0) {
            $_SESSION['s_sysmsg'] .= "型式コード：{$newjis_group_code} 型式：{$newjis_group_name}の削除に失敗！";   // .= に注意
            $msg_flg = 'alert';
            return false;
        } else {
            $_SESSION['s_sysmsg'] .= "型式コード：{$newjis_group_code} 型式：{$newjis_group_name}を削除しました！"; // .= に注意
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
        SELECT  selectm.newjis_group_code           AS 型式コード   -- 0
            ,   selectm.newjis_apply_code           AS 申請コード   -- 1
            ,   selectm.newjis_kind_name            AS 品名（種類） -- 2
            ,   selectm.newjis_certification_code   AS 形式認証番号 -- 3
            ,   selectm.newjis_period_ym            AS 有効期限     -- 4
            ,   selectm.newjis_group_name           AS 型式         -- 5
        FROM
            new_jis_select_master AS selectm
        ORDER BY
            newjis_group_code
    ";

    $res_g = array();
    if (($rows_g = getResultWithField2($query_g, $field_g, $res_g)) <= 0) {
        $_SESSION['s_sysmsg'] = "型式の登録がありません！";
        $field_g[0]   = "型式コード";
        $field_g[1]   = "申請コード";
        $field_g[2]   = "品名（種類）";
        $field_g[3]   = "形式認証番号";
        $field_g[4]   = "有効期限";
        $field_g[5]   = "型式";
        $_SESSION['s_sysmsg'] = "登録がありません！";
        $result->add_array2('res_g', '');
        $result->add_array2('field_g', $field_g);
        $result->add('num_g', 6);
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
function productMaster_copy($request, $result)
{
    $res_g = $result->get_array2('res_g');
    $r = $request->get('number');
    $newjis_group_code          = $res_g[$r][0];
    $newjis_apply_code          = $res_g[$r][1];
    $newjis_kind_name           = $res_g[$r][2];
    $newjis_certification_code  = $res_g[$r][3];
    $newjis_period_ym           = $res_g[$r][4];
    $newjis_group_name          = $res_g[$r][5];
    $request->add('newjis_group_code', $newjis_group_code);
    $request->add('newjis_apply_code', $newjis_apply_code);
    $request->add('newjis_kind_name', $newjis_kind_name);
    $request->add('newjis_certification_code', $newjis_certification_code);
    $request->add('newjis_period_ym', $newjis_period_ym);
    $request->add('newjis_group_name', $newjis_group_name);
}

////////////// 照会用グループコード照会画面のHTMLの作成
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
    $listTable .= "    <form name='entry_form' action='newjis_groupMaster_Main.php' method='post' target='_parent'>\n";
    $listTable .= "        <table bgcolor='#d6d3ce' width='150' align='center' border='1' cellspacing='0' cellpadding='3'>\n";
    $listTable .= "            <tr><td> <!----------- ダミー(デザイン用) ------------>\n";
    $listTable .= "        <table class='winbox_field' width='100%' bgcolor='#d6d3ce' align='center' border='1' cellspacing='0' cellpadding='3'>\n";
    $listTable .= "        <THEAD>\n";
    $listTable .= "            <!-- テーブル ヘッダーの表示 -->\n";
    $listTable .= "            <tr>\n";
    $listTable .= "                <td width='700' bgcolor='#ffffc6' align='center' colspan='15'>\n";
    $listTable .= "                型式マスター\n";
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
        $listTable .= "                <a href='../newjis_groupMaster_Main.php?number=". $r ."' target='_parent' style='text-decoration:none;'>\n";
        $cnum = $r + 1;
        $listTable .= "                ". $cnum ."\n";
        $listTable .= "                </a>\n";
        $listTable .= "            </td>\n";
        $res_g = $result->get_array2('res_g');
        for ($i=0; $i<$result->get('num_g'); $i++) {    // レコード数分繰返し
            switch ($i) {
                case 0:                                 // 型式コード
                    $listTable .= "<td class='winbox' nowrap align='center'><div class='pt9'>". $res_g[$r][$i] ."</div></td>\n";
                break;
                case 1:                                 // 申請コード
                    $listTable .= "<td class='winbox' nowrap align='left'><div class='pt9'>". $res_g[$r][$i] ."</div></td>\n";
                break;
                case 2:                                 // 品名(種類)
                    $listTable .= "<td class='winbox' nowrap align='left'><div class='pt9'>". $res_g[$r][$i] ."</div></td>\n";
                break;
                case 3:                                 // 形式認証番号
                    $listTable .= "<td class='winbox' nowrap align='left'><div class='pt9'>". $res_g[$r][$i] ."</div></td>\n";
                break;
                case 4:                                 // 有効期限
                    $listTable .= "<td class='winbox' nowrap align='left'><div class='pt9'>". $res_g[$r][$i] ."</div></td>\n";
                break;
                case 5:                                 // 型式
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

////////////// 照会用製品グループ一覧表示のHTMLを出力
function outViewListHTML($request, $menu, $result)
{
    $listHTML = getViewHTMLbody($request, $menu, $result);
    $request->add('view_flg', '照会');
    ////////// HTMLファイル出力
    $file_name = "list/newjis_groupMaster_List-{$_SESSION['User_ID']}.html";
    $handle = fopen($file_name, 'w');
    fwrite($handle, $listHTML);
    fclose($handle);
    chmod($file_name, 0666);    // fileを全てrwモードにする
}
