<?php
//////////////////////////////////////////////////////////////////////////////
// 製品グループコード編集 メイン product_groupMaster_Main.php               //
// 製品グループ（詳細）の検索用グループ設定                                 //
// Copyright (C) 2009 Norihisa.Ohya norihisa_ooya@nitto-kohki.co.jp         //
// Changed history                                                          //
// 2009/11/24 Created  product_groupMaster_Main.php                         //
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
    ////////////// TNK 共用メニュークラスのインスタンスを作成
    $menu = new MenuHeader(0);                          // 認証チェック0=一般以上 戻り先=TOP_MENU タイトル未設定
    ////////////// タイトル名(ソースのタイトル名とフォームのタイトル名)
    $menu->set_title('製品グループコード編集');
  
    $request = new Request;
    $result  = new Result;
    
    ////////////// リターンアドレス設定
    $menu->set_RetUrl($_SESSION['product_master_referer']);             // 通常は指定する必要はない
    
    get_serch_master($result, $request);                // グループマスターの取得
    get_product_master($result, $request);          // 機械ワークデータマスターの取得
    get_productUnreg_master ($result);
    
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
    require_once ('product_groupMaster_View.php');

    ob_end_flush(); 
}

////////////// 処理の分岐を行う
function request_check($request, $result, $menu)
{
    $ok = true;
    if ($request->get('number') != '') $ok = machineWork_copy($request, $result);
    if ($request->get('entry') != '')  $ok = machineWork_entry($request, $result);
    if ($ok) {
        ////// データの初期化
        $request->add('entry', '');
        $request->add('number', '');
        $request->add('mhgcd', '');
        $request->add('mhgnm', '');
        $request->add('mhggp', '');
        get_serch_master($result, $request);       // グループマスターの取得
        get_product_master($result, $request); // 機械ワークデータマスターの取得
        get_productUnreg_master ($result);
    }
}

////////////// 追加・変更ロジック (合計レコード数取得前に行う)
function machineWork_entry($request, $result)
{
    if (getCheckAuthority(22)) {                             // 認証チェック
        $mhgcd = $request->get('mhgcd');
        $mhgnm = $request->get('mhgnm');
        $mhggp = $request->get('mhggp');
        $query = sprintf("SELECT mhgcd FROM mshgnm WHERE mhgcd='%s'", $mhgcd);
        $res_chk = array();
        if ( getResult($query, $res_chk) > 0 ) {         // 登録あり UPDATE 更新
            $query = sprintf("UPDATE mshgnm SET mhggp=%d, last_date=CURRENT_TIMESTAMP, last_user='%s' 
                                WHERE mhgcd='%s'", $mhggp, $_SESSION['User_ID'], $mhgcd);
            if (query_affected($query) <= 0) {
                $_SESSION['s_sysmsg'] .= "グループコード：{$mhgcd} グループ名：{$mhgnm}のデータ変更失敗！";    // .= に注意
                $msg_flg = 'alert';
                return false;
            } else {
                $_SESSION['s_sysmsg'] .= "グループコード：{$mhgcd} グループ名：{$mhgnm}のデータを変更しました！";    // .= に注意
                return true;
            }
        }
    } else {                                                 // 認証なしエラー
        $_SESSION['s_sysmsg'] .= "編集権限がありません。必要な場合には、担当者に連絡して下さい。";
        return false;
    }
}

////////////// 表示用(一覧表)のグループマスターデータをSQLで取得
function get_serch_master ($result, $request)
{
    $query_g = "
        SELECT  groupm.group_no                AS グループ番号     -- 0
            ,   groupm.group_name              AS グループ名       -- 1
        FROM
            product_serchGroup AS groupm
        ORDER BY
            group_name
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

////////////// 表示用(一覧表)の製品グループコードデータをSQLで取得
function get_product_master ($result, $request)
{
    $query = "
        SELECT  mshgn.mhgcd                 AS 製品グループコード  -- 0
            ,   mshgn.mhgnm                 AS 製品グループ名      -- 1
            ,   mshgn.mhggp                 AS 検索用グループ      -- 2
        FROM
            mshgnm AS mshgn
        ORDER BY
            mhgcd, mhggp
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

////////////// 表示用(一覧表)の検索用グループ未登録検数をSQLで取得
function get_productUnreg_master ($result)
{
    $query_num = "
        SELECT  count(*) as num
        FROM
            mshgnm
        WHERE 
            mhggp IS NULL
    ";

    $res_num = array();
    if (getResult($query_num, $res_num) <= 0) {
        $unreg_num = 0;
        //$result->add('unreg_num', $unreg_num);
    } else {
        $unreg_num = $res_num[0]['num'];
        //$result->add('unreg_num', $unreg_num);
    }
    $query_num = "
        SELECT  count(*) as num
        FROM
            mshgnm
        WHERE 
            mhggp = 0
    ";

    $res_num = array();
    if (getResult($query_num, $res_num) <= 0) {
        $result->add('unreg_num', $unreg_num);
    } else {
        $unreg_num += $res_num[0]['num'];
        $result->add('unreg_num', $unreg_num);
    }
}

////////////// コピーのリンクが押された時
function machineWork_copy($request, $result)
{
    $r = $request->get('number');
    $res = $result->get_array2('res_m');
    $mhgcd = $res[$r][0];
    $mhgnm = $res[$r][1];
    $mhggp = $res[$r][2];
    
    $request->add('mhgcd', $mhgcd);
    $request->add('mhgnm', $mhgnm);
    $request->add('mhggp', $mhggp);
}

////////////// 製品グループコード編集画面のHTMLの作成
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
    $listTable .= "    <form name='entry_form' action='product_groupMaster_Main.php' method='post'>\n";
    $listTable .= "        <table bgcolor='#d6d3ce' width='300' align='center' border='1' cellspacing='0' cellpadding='3'>\n";
    $listTable .= "            <tr><td> <!----------- ダミー(デザイン用) ------------>\n";
    $listTable .= "        <table class='winbox_field' width='100%' bgcolor='#d6d3ce' align='center' border='1' cellspacing='0' cellpadding='3'>\n";
    $listTable .= "        <THEAD>\n";
    $listTable .= "            <!-- テーブル ヘッダーの表示 -->\n";
    $listTable .= "            <tr>\n";
    $listTable .= "               <td bgcolor='#ffffc6' align='center' colspan='20'>\n";
    $listTable .= "                   製品グループコード一覧\n";
    $listTable .= "                </td>\n";
    $listTable .= "            </tr>\n";
    $listTable .= "            <tr>\n";
    $listTable .= "                <th class='winbox' nowrap width='10'>No</th>        <!-- 行ナンバーの表示 -->\n";
    if ($result->get('num_m') > 0) {
        $field = $result->get_array2('field_m');
        for ($i=0; $i<$result->get('num_m'); $i++) {    // フィールド数分繰返し\n";
            $listTable .= "        <th class='winbox' nowrap>". $field[$i] ."</th>\n";
        }
    } else {
        $listTable .= "            <th class='winbox' nowrap>製品グループコード</th>\n";
        $listTable .= "            <th class='winbox' nowrap>製品グループ名</th>\n";
        $listTable .= "            <th class='winbox' nowrap>検索用グループ</th>\n";
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
        $listTable .= "                <a href='../product_groupMaster_Main.php?number=". $r ."' target='_parent' style='text-decoration:none;'>\n";
        $cnum = $r + 1;
        $listTable .= "                ". $cnum ."\n";
        $listTable .= "                </a>\n";
        $listTable .= "            </td>    <!-- 行ナンバーの表示 -->\n";
        for ($i=0; $i<$result->get('num_m'); $i++) {    // レコード数分繰返し
            switch ($i) {
                case 0:                                 // グループコード
                    $listTable .= "<td class='winbox' nowrap align='left'><div class='pt9'>". $res[$r][$i] ."</div></td>\n";
                    break;
                case 1:                                 // グループ名
                    $listTable .= "<td class='winbox' nowrap align='left'><div class='pt9'>". $res[$r][$i] ."</div></td>\n";
                    break;
                case 2:                                 // 照会用グループ名
                    $listTable .= "<td class='winbox' nowrap align='left'><div class='pt9'>". $group_name[$r] ."</div></td>\n";
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

////////////// 製品グループコード照会画面のHTMLを出力
function outViewListHTML($request, $menu, $result)
{
    $listHTML = getViewHTMLbody($request, $menu, $result);
    $request->add('view_flg', '照会');
    ////////// HTMLファイル出力
    $file_name = "list/product_groupMaster_List-{$_SESSION['User_ID']}.html";
    $handle = fopen($file_name, 'w');
    fwrite($handle, $listHTML);
    fclose($handle);
    chmod($file_name, 0666);       // fileを全てrwモードにする
}
