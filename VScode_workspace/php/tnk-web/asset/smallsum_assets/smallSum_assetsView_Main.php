<?php
//////////////////////////////////////////////////////////////////////////////
// 少額資産管理台帳の照会 メイン smallSum_assetsView_Main.php               //
// Copyright (C) 2010 Norihisa.Ooya norihisa_ooya@nitto-kohki.co.jp         //
// Changed history                                                          //
// 2010/10/05 Created  smallSum_assetsView_Main.php                         //
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
    $menu = new MenuHeader(0);                          // 認証チェック0=一般以上 戻り先=TOP_MENU タイトル未設定
    ////////////// サイト設定
    $menu->set_site(80, 82);                // site_index=4(プログラム開発) site_id=999(子メニューあり)
    ////////// タイトル名(ソースのタイトル名とフォームのタイトル名)
    $menu->set_title('少額資産管理台帳の照会');
    
    $request = new Request;
    $result  = new Result;
    
    ////////////// リターンアドレス設定
    $menu->set_RetUrl(ASSET_MENU);             // 通常は指定する必要はない
    
    //get_group_master($result, $request);                // グループマスターデータの取得
    
    outViewListHTML($request, $menu, $result);          // HTML作成
    
    display($menu, $request, $result);                  // 画面表示
    
    request_check($request, $result, $menu);            // 処理の分岐チェック.
    
    outViewListHTML($request, $menu, $result);          // HTML作成
    
    display($menu, $request, $result);                  // 画面表示
    //get_capital_master ($result, $request);             // 固定資産マスターの取得
    
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
    require_once ('smallSum_assetsView_View.php');

    ob_end_flush(); 
}

////////////// 処理の分岐を行う
function request_check($request, $result, $menu)
{
    $ok = true;
    if ($request->get('search') != '') get_capital_master ($result, $request);    // 固定資産マスターの取得
    if ($ok) {
        ////// データの初期化
        //$request->add('del', '');
        //$request->add('entry', '');
        //$request->add('change', '');
        //$request->add('number', '');
        //$request->add('act_name', '');
        //$request->add('set_place', '');
        //$request->add('assets_name', '');
        //$request->add('assets_model', '');
        //$request->add('buy_ym', '');
        //$request->add('buy_price', '');
        //$request->add('delete_ym', '');
        //$request->add('note', '');
    }
}

////////////// 表示用(一覧表)の少額資産管理台帳データをSQLで取得
function get_capital_master ($result, $request)
{
    $act_name     = $request->get('act_name');
    $set_place    = $request->get('set_place');
    $assets_name  = $request->get('assets_name');
    $assets_model = $request->get('assets_model');
    $delete_in    = $request->get('delete_in');
    // 共通WHERE部
    $search  = '';
    if ($act_name != '' && $set_place != '' && $assets_name != '' && $assets_model != '') {
        if ($delete_in != 'IN') {
            $search .= "WHERE act_name LIKE '%{$act_name}%' AND set_place LIKE '%{$set_place}%' AND assets_name LIKE '%{$assets_name}%' AND assets_model LIKE '%{$assets_model}%' AND delete_ym = 0";
        } else {
            $search .= "WHERE act_name LIKE '%{$act_name}%' AND set_place LIKE '%{$set_place}%' AND assets_name LIKE '%{$assets_name}%' AND assets_model LIKE '%{$assets_model}%'";
        }
    } elseif ($act_name != '' && $set_place != '' && $assets_name != '' && $assets_model == '') {
        if ($delete_in != 'IN') {
            $search .= "WHERE act_name LIKE '%{$act_name}%' AND set_place LIKE '%{$set_place}%' AND assets_name LIKE '%{$assets_name}%' AND delete_ym = 0";
        } else {
            $search .= "WHERE act_name LIKE '%{$act_name}%' AND set_place LIKE '%{$set_place}%' AND assets_name LIKE '%{$assets_name}%'";
        }
    } elseif ($act_name != '' && $set_place != '' && $assets_name == '' && $assets_model != '') {
        if ($delete_in != 'IN') {
            $search .= "WHERE act_name LIKE '%{$act_name}%' AND set_place LIKE '%{$set_place}%' AND assets_model LIKE '%{$assets_model}%' AND delete_ym = 0";
        } else {
            $search .= "WHERE act_name LIKE '%{$act_name}%' AND set_place LIKE '%{$set_place}%' AND assets_model LIKE '%{$assets_model}%'";
        }
    } elseif ($act_name != '' && $set_place == '' && $assets_name != '' && $assets_model != '') {
        if ($delete_in != 'IN') {
            $search .= "WHERE act_name LIKE '%{$act_name}%' AND assets_name LIKE '%{$assets_name}%' AND assets_model LIKE '%{$assets_model}%' AND delete_ym = 0";
        } else {
            $search .= "WHERE act_name LIKE '%{$act_name}%' AND assets_name LIKE '%{$assets_name}%' AND assets_model LIKE '%{$assets_model}%'";
        }
    } elseif ($act_name != '' && $set_place != '' && $assets_name == '' && $assets_model == '') {
        if ($delete_in != 'IN') {
            $search .= "WHERE act_name LIKE '%{$act_name}%' AND set_place LIKE '%{$set_place}%' AND delete_ym = 0";
        } else {
            $search .= "WHERE act_name LIKE '%{$act_name}%' AND set_place LIKE '%{$set_place}%'";
        }
    } elseif ($act_name != '' && $set_place == '' && $assets_name == '' && $assets_model != '') {
        if ($delete_in != 'IN') {
            $search .= "WHERE act_name LIKE '%{$act_name}%' AND assets_model LIKE '%{$assets_model}%' AND delete_ym = 0";
        } else {
            $search .= "WHERE act_name LIKE '%{$act_name}%' AND assets_model LIKE '%{$assets_model}%'";
        }
    } elseif ($act_name != '' && $set_place == '' && $assets_name != '' && $assets_model == '') {
        if ($delete_in != 'IN') {
            $search .= "WHERE act_name LIKE '%{$act_name}%' AND assets_name LIKE '%{$assets_name}%' AND delete_ym = 0";
        } else {
            $search .= "WHERE act_name LIKE '%{$act_name}%' AND assets_name LIKE '%{$assets_name}%'";
        }
    } elseif ($act_name != '' && $set_place == '' && $assets_name == '' && $assets_model == '') {
        if ($delete_in != 'IN') {
            $search .= "WHERE act_name LIKE '%{$act_name}%' AND delete_ym = 0";
        } else {
            $search .= "WHERE act_name LIKE '%{$act_name}%'";
        }
    }
    if ($act_name == '' && $set_place != '' && $assets_name != '' && $assets_model != '') {
        if ($delete_in != 'IN') {
            $search .= "WHERE set_place LIKE '%{$set_place}%' AND assets_name LIKE '%{$assets_name}%' AND assets_model LIKE '%{$assets_model}%' AND delete_ym = 0";
        } else {
            $search .= "WHERE set_place LIKE '%{$set_place}%' AND assets_name LIKE '%{$assets_name}%' AND assets_model LIKE '%{$assets_model}%'";
        }
    } elseif ($act_name == '' && $set_place != '' && $assets_name == '' && $assets_model != '') {
        if ($delete_in != 'IN') {
            $search .= "WHERE set_place LIKE '%{$set_place}%' AND assets_model LIKE '%{$assets_model}%' AND delete_ym = 0";
        } else {
            $search .= "WHERE set_place LIKE '%{$set_place}%' AND assets_model LIKE '%{$assets_model}%'";
        }
    } elseif ($act_name == '' && $set_place != '' && $assets_name != '' && $assets_model == '') {
        if ($delete_in != 'IN') {
            $search .= "WHERE set_place LIKE '%{$set_place}%' AND assets_name LIKE '%{$assets_name}%' AND delete_ym = 0";
        } else {
            $search .= "WHERE set_place LIKE '%{$set_place}%' AND assets_name LIKE '%{$assets_name}%'";
        }
    } elseif ($act_name == '' && $set_place != '' && $assets_name == '' && $assets_model == '') {
        if ($delete_in != 'IN') {
            $search .= "WHERE set_place LIKE '%{$set_place}%' AND delete_ym = 0";
        } else {
            $search .= "WHERE set_place LIKE '%{$set_place}%'";
        }
    }
    if ($act_name == '' && $set_place == '' && $assets_name != '' && $assets_model != '') {
        if ($delete_in != 'IN') {
            $search .= "WHERE assets_name LIKE '%{$assets_name}%' AND assets_model LIKE '%{$assets_model}%' AND delete_ym = 0";
        } else {
            $search .= "WHERE assets_name LIKE '%{$assets_name}%' AND assets_model LIKE '%{$assets_model}%'";
        }
    } elseif ($act_name == '' && $set_place == '' && $assets_name != '' && $assets_model == '') {
        if ($delete_in != 'IN') {
            $search .= "WHERE assets_name LIKE '%{$assets_name}%' AND delete_ym = 0";
        } else {
            $search .= "WHERE assets_name LIKE '%{$assets_name}%'";
        }
    }
    if ($act_name == '' && $set_place == '' && $assets_name == '' && $assets_model != '') {
        if ($delete_in != 'IN') {
            $search .= "WHERE assets_model LIKE '%{$assets_model}%' AND delete_ym = 0";
        } else {
            $search .= "WHERE assets_model LIKE '%{$assets_model}%'";
        }
    }
    if ($act_name == '' && $set_place == '' && $assets_name == '' && $assets_model == '' && $delete_in == 'IN') {
        $query = "
            SELECT  act_name        AS 管理部門         -- 0
                ,   set_place       AS 設置場所         -- 1
                ,   assets_name     AS 品目             -- 2
                ,   assets_model    AS メーカー名・型式 -- 3
                ,   buy_ym          AS 購入年月日       -- 4
                ,   buy_price       AS 購入価格         -- 5
                ,   delete_ym       AS 除却年月日       -- 6
                ,   note            AS 備考             -- 7
            FROM
                smallsum_assets_master
            ORDER BY
                act_name ASC, assets_name ASC
        ";
    } elseif ($act_name == '' && $set_place == '' && $assets_name == '' && $assets_model == '' && $delete_in != 'IN') {
        $query = "
            SELECT  act_name        AS 管理部門         -- 0
                ,   set_place       AS 設置場所         -- 1
                ,   assets_name     AS 品目             -- 2
                ,   assets_model    AS メーカー名・型式 -- 3
                ,   buy_ym          AS 購入年月日       -- 4
                ,   buy_price       AS 購入価格         -- 5
                ,   delete_ym       AS 除却年月日       -- 6
                ,   note            AS 備考             -- 7
            FROM
                smallsum_assets_master
            WHERE
                delete_ym = 0
            ORDER BY
                act_name ASC, assets_name ASC
        ";
    } else {
        $query = "
            SELECT  act_name        AS 管理部門         -- 0
                ,   set_place       AS 設置場所         -- 1
                ,   assets_name     AS 品目             -- 2
                ,   assets_model    AS メーカー名・型式 -- 3
                ,   buy_ym          AS 購入年月日       -- 4
                ,   buy_price       AS 購入価格         -- 5
                ,   delete_ym       AS 除却年月日       -- 6
                ,   note            AS 備考             -- 7
            FROM
                smallsum_assets_master
            {$search}
            ORDER BY
                act_name ASC, assets_name ASC
        ";
    }
    

    $res_c = array();
    if (($rows_c = getResultWithField2($query, $field_c, $res_c)) <= 0) {
        //$_SESSION['s_sysmsg'] = "登録がありません！";
        $result->add_array2('res_c', '');
        $result->add_array2('field_c', '');
        $result->add('num_c', 0);
        $result->add('rows_c', 0);
    } else {
        $code_actname   = array();
        $code_placename = array();
        for ($i=0; $i<$rows_c; $i++) {    // フィールド数分繰返し\n";
            $code_actname[$i] = getActNameCode($res_c[$i][0]);
            $code_placename[$i] = getPlaceNameCode($res_c[$i][1]);
        }
        $num_c = count($field_c);
        $result->add_array2('res_c', $res_c);
        $result->add_array2('code_actname', $code_actname);
        $result->add_array2('code_placename', $code_placename);
        $result->add_array2('field_c', $field_c);
        $result->add('num_c', $num_c);
        $result->add('rows_c', $rows_c);
    }
    //$res_g = $result->get_array2('res_g');
    //for ($r=0; $r<$rows_c; $r++) {    // グループ番号とグループ名の置き換え(固定資産）
    //    for ($i=0; $i<$result->get('rows_g'); $i++) {
    //        if($res_c[$r][0] == $res_g[$i][0]) {
    //            $group_name[$r] = $res_g[$i][1];
    //        }
    //    }
    //}
    //$result->add_array2('group_name', $group_name);
}

////////////// ８桁の任意の日付を'/'フォーマットして返す。
function format_date8($date8)
{
    if (0 == $date8) {
        $date8 = '--------';    
    }
    if (8 == strlen($date8)) {
        $nen   = substr($date8, 0, 4);
        $tsuki = substr($date8, 4, 2);
        $hi    = substr($date8, 6, 2);
        return $nen . "/" . $tsuki . "/" . $hi;
    } else {
        return FALSE;
    }
}

////////////// ８桁の任意の日付を'年月'フォーマットして返す。
function format_date_kan($date6)
{
    if (0 == $date8) {
        $date8 = '--------';    
    }
    if (8 == strlen($date6)) {
        $nen   = substr($date8, 0, 4);
        $tsuki = substr($date8, 4, 2);
        $hi    = substr($date8, 6, 2);
        return $nen . "年" . $tsuki . "月" . $hi . "日";
    } else {
        return FALSE;
    }
}

////////////// 固定資産照会画面のHTMLの作成
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
    $listTable .= "    <form name='entry_form' action='smallSum_assetsView_Main.php' method='post'>\n";
    $listTable .= "        <table bgcolor='#d6d3ce' width='300' align='center' border='1' cellspacing='0' cellpadding='3'>\n";
    $listTable .= "            <tr><td> <!----------- ダミー(デザイン用) ------------>\n";
    $listTable .= "        <table class='winbox_field' width='100%' bgcolor='#d6d3ce' align='center' border='1' cellspacing='0' cellpadding='3'>\n";
    $listTable .= "        <THEAD>\n";
    $listTable .= "            <!-- テーブル ヘッダーの表示 -->\n";
    $listTable .= "            <tr>\n";
    $listTable .= "                <td bgcolor='#ffffc6' align='center' colspan='20'>\n";
    $listTable .= "                    少額資産管理台帳\n";
    $listTable .= "                </td>\n";
    $listTable .= "            </tr>\n";
    $listTable .= "            <tr>\n";
    $listTable .= "                <th class='winbox' nowrap width='10'>No</th>        <!-- 行ナンバーの表示 -->\n";
    if ($result->get('num_c') > 0) {
        $field_c = $result->get_array2('field_c');
        for ($i=0; $i<$result->get('num_c'); $i++) {    // フィールド数分繰返し\n";
            $listTable .= "        <th class='winbox' nowrap>". $field_c[$i] ."</th>\n";
        }
    } else {
        $listTable .= "            <th class='winbox' nowrap>管理部門</th>\n";
        $listTable .= "            <th class='winbox' nowrap>設置場所</th>\n";
        $listTable .= "            <th class='winbox' nowrap>品目</th>\n";
        $listTable .= "            <th class='winbox' nowrap>メーカー・型式名</th>\n";
        $listTable .= "            <th class='winbox' nowrap>購入年月日</th>\n";
        $listTable .= "            <th class='winbox' nowrap>購入価格</th>\n";
        $listTable .= "            <th class='winbox' nowrap>除却年月日</th>\n";
        $listTable .= "            <th class='winbox' nowrap>備考</th>\n";
    }
    $listTable .= "            </tr>\n";
    $listTable .= "        </THEAD>\n";
    $listTable .= "        <TFOOT>\n";
    $listTable .= "            <!-- 現在はフッターは何もない -->\n";
    $listTable .= "        </TFOOT>\n";
    $listTable .= "        <TBODY>\n";
    $listTable .= "            <!--  bgcolor='#ffffc6' 薄い黄色 -->\n";
    $listTable .= "            <!-- サンプル<td rowspan='2' colspan='3' width='200' align='center' class='pt10b' bgcolor='#ffffc6'>  </td> -->\n";
    $res_c = $result->get_array2('res_c');
    $group_name = $result->get_array2('group_name');
    $code_actname = $result->get_array2('code_actname');
    $code_placename = $result->get_array2('code_placename');
    for ($r=0; $r<$result->get('rows_c'); $r++) {
        $listTable .= "        <tr>\n";
        $listTable .= "            <td class='winbox' nowrap align='right'>\n";
        $cnum = $r + 1;
        $listTable .= "                ". $cnum ."\n";
        $listTable .= "            </td>    <!-- 行ナンバーの表示 -->\n";
        for ($i=0; $i<$result->get('num_c'); $i++) {    // レコード数分繰返し
            switch ($i) {
                case 0:                                 // 管理部門
                    $listTable .= "<td class='winbox' nowrap align='left'><div class='pt9'>". $code_actname[$r] ."</div></td>\n";
                break;
                case 1:                                 // 設置場所
                    $listTable .= "<td class='winbox' nowrap align='left'><div class='pt9'>". $code_placename[$r] ."</div></td>\n";
                break;
                case 2:                                 // 品目
                    $listTable .= "<td class='winbox' nowrap align='left'><div class='pt9'>". $res_c[$r][$i] ."</div></td>\n";
                break;
                case 3:                                 // メーカー名・型式
                    $listTable .= "<td class='winbox' nowrap align='left'><div class='pt9'>". $res_c[$r][$i] ."</div></td>\n";
                break;
                case 4:                                 // 購入年月日
                    $listTable .= "<td class='winbox' nowrap align='left'><div class='pt9'>". format_date8($res_c[$r][$i]) ."</div></td>\n";
                break;
                case 5:                                 // 購入価格
                    $listTable .= "<td class='winbox' nowrap align='right'><div class='pt9'>". number_format($res_c[$r][$i], 0) ."</div></td>\n";
                break;
                case 6:                                 // 除却・移動年月日
                    $listTable .= "<td class='winbox' nowrap align='left'><div class='pt9'>". format_date8($res_c[$r][$i]) ."</div></td>\n";
                break;
                case 7:                                 // 備考
                    $listTable .= "<td class='winbox' nowrap align='left'><div class='pt9'>". $res_c[$r][$i] ."</div></td>\n";
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
///// 管理部門のHTML <select> option の出力
function getActOptionsBody($request)
{
    $query = "SELECT * FROM smallsum_assets_actname_master ORDER BY code_act ASC";
    $res = array();
    if (($rows=getResult2($query, $res)) <= 0) return '';
    $options = "\n";
    $options .= "<option value='' style='color:red;'>未選択</option>\n";
    for ($i=0; $i<$rows; $i++) {
        if ($request->get('act_name') == $res[$i][0]) {
            $options .= "<option value='{$res[$i][0]}' selected>{$res[$i][1]}</option>\n";
        } else {
            $options .= "<option value='{$res[$i][0]}'>{$res[$i][1]}</option>\n";
        }
    }
    return $options;
}
///// 管理部門コード・名称変換
function getActNameCode($act_code)
{
    $query_chk = sprintf("SELECT name_act FROM smallsum_assets_actname_master WHERE code_act=%d", $act_code);
    $res_chk = array();
    $code_actname = '';
    //$code_actname = array();
    if ( getResult($query_chk, $res_chk) > 0 ) {    // 登録あり UPDATE 更新
        $code_actname = $res_chk[0][0];
    } else {
        $code_actname = "---";
    }
    return $code_actname;
}

///// 設置場所のHTML <select> option の出力
function getPlaceOptionsBody($request)
{
    $query = "SELECT * FROM smallsum_assets_placename_master ORDER BY code_place ASC";
    $res = array();
    if (($rows=getResult2($query, $res)) <= 0) return '';
    $options = "\n";
    $options .= "<option value='' style='color:red;'>未選択</option>\n";
    for ($i=0; $i<$rows; $i++) {
        if ($request->get('set_place') == $res[$i][0]) {
            $options .= "<option value='{$res[$i][0]}' selected>{$res[$i][1]}</option>\n";
        } else {
            $options .= "<option value='{$res[$i][0]}'>{$res[$i][1]}</option>\n";
        }
    }
    return $options;
}

///// 設置場所コード・名称変換
function getPlaceNameCode($place_code)
{
    $query_chk = sprintf("SELECT name_place FROM smallsum_assets_placename_master WHERE code_place=%d", $place_code);
    $res_chk = array();
    $code_placename = '';
    //$code_placename = array();
    if ( getResult($query_chk, $res_chk) > 0 ) {    // 登録あり UPDATE 更新
        $code_placename = $res_chk[0][0];
    } else {
        $code_placename = "---";
    }
    return $code_placename;
}

////////////// 賃率照会画面のHTMLを出力
function outViewListHTML($request, $menu, $result)
{
    $listHTML = getViewHTMLbody($request, $menu, $result);
    $request->add('view_flg', '照会');
    ////////// HTMLファイル出力
    $file_name = "list/smallSum_assetsView_List-{$_SESSION['User_ID']}.html";
    $handle = fopen($file_name, 'w');
    fwrite($handle, $listHTML);
    fclose($handle);
    chmod($file_name, 0666);       // fileを全てrwモードにする
}
