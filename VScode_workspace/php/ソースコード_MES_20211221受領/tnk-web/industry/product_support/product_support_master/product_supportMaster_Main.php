<?php
//////////////////////////////////////////////////////////////////////////////
// 生産支援品マスターの登録 メイン product_supportMaster_Main.php           //
// Copyright (C) 2011-     Norihisa.Ohya norihisa_ooya@nitto-kohki.co.jp    //
// Changed history                                                          //
// 2011/11/10 Created  product_supportMaster_Main.php                       //
// 2011/11/11 コピー時のデータ移行がずれていた為訂正                        //
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
    $menu->set_title('生産支援品マスターの登録');
    //////////// 戻先へのGETデータ設定
    $menu->set_retGET('page_keep', 'On');    
  
    $request = new Request;
    $result  = new Result;
    
    ////////////// リターンアドレス設定
    $menu->set_RetUrl($_SESSION['product_master_referer']);             // 通常は指定する必要はない
    
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
    require_once ('product_supportMaster_View.php');

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
        $request->add('assy_name', '');
        $request->add('support_group_code', '');
        get_serch_master($result, $request);    // 各種データの取得
        get_product_master($result, $request); // 機械ワークデータマスターの取得
    }
}

////////////// 追加・変更ロジック (合計レコード数取得前に行う)
function productMaster_entry($request, $result)
{
    if (getCheckAuthority(22)) {                    // 認証チェック
        $assy_no = $request->get('assy_no');
        $support_group_code = $request->get('support_group_code');
        $support_group_name = get_group_name($support_group_code);
        $query = sprintf("SELECT assy_no FROM product_support_master WHERE assy_no='%s'", $assy_no);
        $res_chk = array();
        if ( getResult($query, $res_chk) > 0 ) {    // 登録あり UPDATE 更新
            $query = sprintf("UPDATE product_support_master SET assy_no='%s', support_group_code=%d, last_date=CURRENT_TIMESTAMP, last_user='%s' WHERE assy_no='%s'", $assy_no, $support_group_code, $_SESSION['User_ID'], $assy_no);
            if (query_affected($query) <= 0) {
                $_SESSION['s_sysmsg'] .= "製品番号：{$assy_no} を支援先：{$support_group_name} へ変更失敗！";      // .= に注意
                $msg_flg = 'alert';
                return false;
            } else {
                $_SESSION['s_sysmsg'] .= "製品番号：{$assy_no} を支援先：{$support_group_name} へ変更しました！"; // .= に注意
                return true;
            }
        } else {                                    // 登録なし INSERT 新規   
            $query = sprintf("INSERT INTO product_support_master (assy_no, support_group_code, last_date, last_user)
                              VALUES ('%s', %d, CURRENT_TIMESTAMP, '%s')",
                                $assy_no, $support_group_code, $_SESSION['User_ID']);
            if (query_affected($query) <= 0) {
                $_SESSION['s_sysmsg'] .= "製品番号：{$assy_no} 支援先：{$support_group_name} の追加に失敗！";      // .= に注意
                $msg_flg = 'alert';
                return false;
            } else {
                $_SESSION['s_sysmsg'] .= "製品番号：{$assy_no} 支援先：{$support_group_name} を追加しました！";    // .= に注意
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
    if (getCheckAuthority(22)) {    // 認証チェック
        $assy_no = $request->get('assy_no');
        $support_group_code = $request->get('support_group_code');
        $support_group_name = get_group_name($support_group_code);
        $query = sprintf("DELETE FROM product_support_master WHERE assy_no = '%s'", $assy_no);
        if (query_affected($query) <= 0) {
            $_SESSION['s_sysmsg'] .= "製品番号：{$assy_no} 支援先：{$support_group_name} の削除に失敗！";   // .= に注意
            $msg_flg = 'alert';
            return false;
        } else {
            $_SESSION['s_sysmsg'] .= "製品番号：{$assy_no} 支援先：{$support_group_name} を削除しました！"; // .= に注意
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
        SELECT  groupm.support_group_code              AS 支援先コード     -- 0
            ,   groupm.support_group_name              AS 支援先名         -- 1
        FROM
            product_support_group_master AS groupm
        ORDER BY
            support_group_code
    ";

    $res_g = array();
    if (($rows_g = getResultWithField2($query_g, $field_g, $res_g)) <= 0) {
        $_SESSION['s_sysmsg'] = "支援先の登録がありません！";
        $field_g[0]   = "支援先コード";
        $field_g[1]   = "支援先名";
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
    $query = sprintf("select support_group_name
                        FROM
                            product_support_group_master
                        WHERE
                            support_group_code=%d
                        LIMIT 1
                        ", $group_code);
    getUniResult($query, $group_name);
    return $group_name;
}
////////////// 表示用(一覧表)のグループマスターデータをSQLで取得
function get_product_master ($result, $request)
{
    $query = "
        SELECT  groupm.assy_no                AS 製品番号     -- 0
            ,   m.midsc                       AS 製品名       -- 1
            ,   groupm.support_group_code     AS 支援先       -- 2
        FROM
            product_support_master AS groupm
        LEFT OUTER JOIN
            miitem AS m
                on(groupm.assy_no = m.mipn)
        ORDER BY
            groupm.support_group_code ASC, groupm.assy_no ASC
    ";

    $res = array();
    if (($rows = getResultWithField2($query, $field, $res)) <= 0) {
        $_SESSION['s_sysmsg'] = "製品番号の登録がありません！";
        $field[0]   = "製品番号";
        $field[1]   = "製品名";
        $field[2]   = "支援先";
        $_SESSION['s_sysmsg'] = "登録がありません！";
        $result->add_array2('res', '');
        $result->add_array2('field', $field);
        $result->add('num', 2);
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
                if($res[$r][2] == $res_g[$i][0]) {
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
    $assy_name   = $res[$r][1];
    $support_group_code = $res[$r][2];
    $request->add('assy_no', $assy_no);
    $request->add('assy_name', $assy_name);
    $request->add('support_group_code', $support_group_code);
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
    $listTable .= "    <form name='entry_form' action='product_supportMaster_Main.php' method='post' target='_parent'>\n";
    $listTable .= "        <table bgcolor='#d6d3ce' width='150' align='center' border='1' cellspacing='0' cellpadding='3'>\n";
    $listTable .= "            <tr><td> <!----------- ダミー(デザイン用) ------------>\n";
    $listTable .= "        <table class='winbox_field' width='100%' bgcolor='#d6d3ce' align='center' border='1' cellspacing='0' cellpadding='3'>\n";
    $listTable .= "        <THEAD>\n";
    $listTable .= "            <!-- テーブル ヘッダーの表示 -->\n";
    $listTable .= "            <tr>\n";
    $listTable .= "                <td width='700' bgcolor='#ffffc6' align='center' colspan='4'>\n";
    $listTable .= "                生産支援品マスター\n";
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
        $listTable .= "                <a href='../product_supportMaster_Main.php?number=". $r ."' target='_parent' style='text-decoration:none;'>\n";
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
                case 1:                                 // 製品名
                    $listTable .= "<td class='winbox' nowrap align='left'><div class='pt11'>". $res[$r][$i] ."</div></td>\n";
                break;
                case 2:                                 // 支援先
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
    $file_name = "list/product_supportMaster_List-{$_SESSION['User_ID']}.html";
    $handle = fopen($file_name, 'w');
    fwrite($handle, $listHTML);
    fclose($handle);
    chmod($file_name, 0666);    // fileを全てrwモードにする
}
