<?php
//////////////////////////////////////////////////////////////////////////////
// 固定資産編集 メイン assemblyRate_capitalAsset_Main.php                   //
//                     (旧 capital_asset_master_main.php)                   //
// Copyright (C) 2007-2011 Norihisa.Ooya usoumu@nitto-kohki.co.jp           //
// Changed history                                                          //
// 2007/12/07 Created  assemblyRate_capitalAsset_Main.php                   //
// 2007/12/13 余分なfontタグの削除 コメントの位置調整                       //
// 2007/12/14 プログラムの最後に改行を追加                                  //
// 2007/12/29 日付データの戻り値を設定                                      //
// 2008/01/09 固定資産の並び順に固定資産Noでのソートを追加                  //
//            コピーした時日付データを渡さなかったのを修正                  //
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
    ////////// TNK 共用メニュークラスのインスタンスを作成
    $menu = new MenuHeader(0);                          // 認証チェック0=一般以上 戻り先=TOP_MENU タイトル未設定
    ////////// タイトル名(ソースのタイトル名とフォームのタイトル名)
    $menu->set_title('固定資産台帳の編集');
    
    $request = new Request;
    $result  = new Result;
    
    ////////////// リターンアドレス設定
    $menu->set_RetUrl($_SESSION['various_referer'] . '?wage_ym=' . $request->get('wage_ym'));             // 通常は指定する必要はない
    
    get_group_master($result, $request);                // グループマスターデータの取得
    get_capital_master ($result, $request);             // 固定資産マスターの取得
    
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
    require_once ('assemblyRate_capitalAsset_View.php');

    ob_end_flush(); 
}

////////////// 処理の分岐を行う
function request_check($request, $result, $menu)
{
    $ok = true;
    if ($request->get('number') != '') $ok = capitalMaster_copy($request, $result);
    if ($request->get('del') != '') $ok = capitalMaster_del($request);
    if ($request->get('entry') != '')  $ok = capitalMaster_entry($request, $result);
    if ($ok) {
        ////// データの初期化
        $request->add('del', '');
        $request->add('entry', '');
        $request->add('number', '');
        $request->add('group_no', '');
        $request->add('asset_no', '');
        $request->add('asset_name', '');
        $request->add('acquisition_money', '');
        $request->add('acquisition_date', '');
        $request->add('durable_years', '');
        $request->add('annual_rate', '');
        $request->add('end_date', '');
        get_group_master($result, $request);       // グループマスターデータの取得
        get_capital_master ($result, $request);    // 固定資産マスターの取得
    }
}

////////////// 追加・変更ロジック (合計レコード数取得前に行う)
function capitalMaster_entry($request, $result)
{
    if (getCheckAuthority(22)) {                    // 認証チェック
        $group_no = $request->get('group_no');
        $asset_no = $request->get('asset_no');
        $asset_name = $request->get('asset_name');
        $acquisition_money = $request->get('acquisition_money');
        $acquisition_date = $request->get('acquisition_date');
        $durable_years = $request->get('durable_years');
        $annual_rate = $request->get('annual_rate');
        $end_date = $request->get('end_date');
        if ($end_date == 0) {
            $end_date = '';
        }
        $query = sprintf("SELECT asset_no FROM capital_asset_master WHERE asset_no='%s'", $asset_no);
        $res_chk = array();
        if ( getResult($query, $res_chk) > 0 ) {    // 登録あり UPDATE 更新
            $query = sprintf("UPDATE assembly_machine_group_capital_asset SET group_no=%d, asset_no='%s', last_date=CURRENT_TIMESTAMP, last_user='%s' WHERE asset_no='%s'", $group_no, $asset_no, $_SESSION['User_ID'], $asset_no);
            if (query_affected($query) <= 0) {
                $_SESSION['s_sysmsg'] .= "固定資産No.：{$asset_no} 固定資産名：{$asset_name}の変更失敗！";          // .= に注意
                $msg_flg = 'alert';
                return false;
            }
            $query = sprintf("UPDATE capital_asset_master SET asset_no='%s', asset_name='%s', acquisition_money=%d, acquisition_date=%d, durable_years=%d, annual_rate='%s', end_date=%d, last_date=CURRENT_TIMESTAMP, last_user='%s' 
                              WHERE asset_no='%s'", $asset_no, $asset_name, $acquisition_money, $acquisition_date, $durable_years, $annual_rate, $end_date, $_SESSION['User_ID'], $asset_no);
            if (query_affected($query) <= 0) {
                $_SESSION['s_sysmsg'] .= "固定資産No.：{$asset_no} 固定資産名：{$asset_name}の変更失敗！";          // .= に注意
                $msg_flg = 'alert';
                return false;
            }
            $_SESSION['s_sysmsg'] .= "固定資産No.：{$asset_no} 固定資産名：{$asset_name}の内容を変更しました！";    // .= に注意
            return true;
        } else {                                    // 登録なし INSERT 新規   
            $query = sprintf("INSERT INTO capital_asset_master (asset_no, asset_name, acquisition_money, acquisition_date, durable_years, annual_rate, end_date, last_date, last_user)
                              VALUES ('%s', '%s', %d, %d, %d, '%s', %d, CURRENT_TIMESTAMP, '%s')",
                              $asset_no, $asset_name, $acquisition_money, $acquisition_date, $durable_years, $annual_rate, $end_date, $_SESSION['User_ID']);
            if (query_affected($query) <= 0) {
                $_SESSION['s_sysmsg'] .= "固定資産No.：{$asset_no} 固定資産名：{$asset_name}の登録に失敗！";        // .= に注意
                $msg_flg = 'alert';
                return false;
            } else {                                // マスター登録に成功したらグループと資産No.のDB更新
                $query = sprintf("insert into assembly_machine_group_capital_asset (group_no, asset_no, last_date, last_user)
                                  values (%d, '%s', CURRENT_TIMESTAMP, '%s')",
                                  $group_no, $asset_no, $_SESSION['User_ID']);
                if (query_affected($query) <= 0) {  // グループと資産No.の登録に失敗した時マスターからも削除
                    $query = sprintf("DELETE FROM capital_asset_master WHERE asset_no = '%s'", $asset_no);
                    $_SESSION['s_sysmsg'] .= "固定資産No.：{$asset_no} 固定資産名：{$asset_name}の登録に失敗！";    // .= に注意
                    $msg_flg = 'alert';
                    return false;
                } else {
                    $_SESSION['s_sysmsg'] .= "固定資産No.：{$asset_no} 固定資産名：{$asset_name}を追加しました！";  // .= に注意
                    return true;
                }
            }
        }
    } else {                                        // 認証なしエラー
        $_SESSION['s_sysmsg'] .= "編集権限がありません。必要な場合には、担当者に連絡して下さい。";
        return false;
    }
}

////////////// 削除ロジック (合計レコード数取得前に行う)
function capitalMaster_del($request)
{
    if (getCheckAuthority(22)) {    // 認証チェック
        $asset_no = $request->get('asset_no');
        $asset_name = $request->get('asset_name');
        $query = sprintf("DELETE FROM capital_asset_master WHERE asset_no = '%s'", $asset_no);
        if (query_affected($query) <= 0) {
            $_SESSION['s_sysmsg'] .= "固定資産No.：{$asset_no} 固定資産名：{$asset_no}の削除に失敗！";            // .= に注意
            $msg_flg = 'alert';
            return false;
        } else {                    // マスター削除成功後グループと資産No.のDBも削除
            $query = sprintf("DELETE FROM assembly_machine_group_capital_asset WHERE asset_no = '%s'", $asset_no);
            if (query_affected($query) <= 0) {
                $_SESSION['s_sysmsg'] .= "固定資産No.：{$asset_no} 固定資産名：{$asset_name}の削除に失敗！";      // .= に注意
                $msg_flg = 'alert';
                return false;
            } else {
                $_SESSION['s_sysmsg'] .= "固定資産No.：{$asset_no} 固定資産名：{$asset_name}を削除しました！";    // .= に注意
                return true;
            }
        }
    } else {                        // 認証なしエラー
        $_SESSION['s_sysmsg'] .= "編集権限がありません。必要な場合には、担当者に連絡して下さい。";
        return false;
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

////////////// 表示用(一覧表)の固定資産データをSQLで取得
function get_capital_master ($result, $request)
{
    $query = "
        SELECT  groupc.group_no                AS グループ名       -- 0
            ,   groupc.asset_no                AS 固定資産No       -- 1
            ,   cmaster.asset_name             AS 資産名称         -- 2
            ,   cmaster.acquisition_money      AS 取得金額         -- 3
            ,   cmaster.acquisition_date       AS 取得年月         -- 4
            ,   cmaster.durable_years          AS 耐用年数         -- 5
            ,   cmaster.annual_rate            AS 年間率           -- 6
            ,   cmaster.end_date               AS 除却年月         -- 7 
        FROM
            assembly_machine_group_capital_asset AS groupc
        LEFT OUTER JOIN
            capital_asset_master AS cmaster
        ON (groupc.asset_no = cmaster.asset_no)
        ORDER BY
            group_no ASC, cmaster.asset_no ASC
    ";

    $res_c = array();
    if (($rows_c = getResultWithField2($query, $field_c, $res_c)) <= 0) {
        $_SESSION['s_sysmsg'] = "登録がありません！";
    } else {
        $num_c = count($field_c);
        $result->add_array2('res_c', $res_c);
        $result->add_array2('field_c', $field_c);
        $result->add('num_c', $num_c);
        $result->add('rows_c', $rows_c);
    }
    $res_g = $result->get_array2('res_g');
    for ($r=0; $r<$rows_c; $r++) {    // グループ番号とグループ名の置き換え(固定資産）
        for ($i=0; $i<$result->get('rows_g'); $i++) {
            if($res_c[$r][0] == $res_g[$i][0]) {
                $group_name[$r] = $res_g[$i][1];
            }
        }
    }
    $result->add_array2('group_name', $group_name);
}
////////////// コピーのリンクが押された時
function capitalMaster_copy($request, $result)
{
    $res_c = $result->get_array2('res_c');
    $copy_no = $request->get('number');
    $group_no          = $res_c[$copy_no][0];
    $asset_no          = $res_c[$copy_no][1];
    $asset_name        = $res_c[$copy_no][2];
    $acquisition_money = $res_c[$copy_no][3];
    $acquisition_date  = $res_c[$copy_no][4];
    $durable_years     = $res_c[$copy_no][5];
    $annual_rate       = $res_c[$copy_no][6];
    if ($res_c[$copy_no][7] == 0) {
        $end_date = '';
    } else {
        $end_date = $res_c[$copy_no][7];
    }
    $request->add('group_no', $group_no);
    $request->add('asset_no', $asset_no);
    $request->add('asset_name', $asset_name);
    $request->add('acquisition_money', $acquisition_money);
    $request->add('acquisition_date', $acquisition_date);
    $request->add('durable_years', $durable_years);
    $request->add('annual_rate', $annual_rate);
    $request->add('end_date', $end_date);
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
    $listTable .= "    <form name='entry_form' action='assemblyRate_capitalAsset_Main.php' method='post'>\n";
    $listTable .= "        <table bgcolor='#d6d3ce' width='300' align='center' border='1' cellspacing='0' cellpadding='3'>\n";
    $listTable .= "            <tr><td> <!----------- ダミー(デザイン用) ------------>\n";
    $listTable .= "        <table class='winbox_field' width='100%' bgcolor='#d6d3ce' align='center' border='1' cellspacing='0' cellpadding='3'>\n";
    $listTable .= "        <THEAD>\n";
    $listTable .= "            <!-- テーブル ヘッダーの表示 -->\n";
    $listTable .= "            <tr>\n";
    $listTable .= "                <td bgcolor='#ffffc6' align='center' colspan='20'>\n";
    $listTable .= "                    固定資産台帳\n";
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
        $listTable .= "            <th class='winbox' nowrap>グループ名</th>\n";
        $listTable .= "            <th class='winbox' nowrap>固定資産No.</th>\n";
        $listTable .= "            <th class='winbox' nowrap>資産名称</th>\n";
        $listTable .= "            <th class='winbox' nowrap>取得金額</th>\n";
        $listTable .= "            <th class='winbox' nowrap>取得年月</th>\n";
        $listTable .= "            <th class='winbox' nowrap>耐用年数</th>\n";
        $listTable .= "            <th class='winbox' nowrap>年間率</th>\n";
        $listTable .= "            <th class='winbox' nowrap>終了年月</th>\n";
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
    for ($r=0; $r<$result->get('rows_c'); $r++) {
        $listTable .= "        <tr>\n";
        $listTable .= "            <td class='winbox' nowrap align='right'>\n";
        $listTable .= "                <a href='../assemblyRate_capitalAsset_Main.php?number=". $r ."&wage_ym=". $request->get('wage_ym') ."' target='_parent' style='text-decoration:none;'>\n";
        $cnum = $r + 1;
        $listTable .= "                ". $cnum ."\n";
        $listTable .= "                </a>\n";
        $listTable .= "            </td>    <!-- 行ナンバーの表示 -->\n";
        for ($i=0; $i<$result->get('num_c'); $i++) {    // レコード数分繰返し
            switch ($i) {
                case 0:                                 // グループ
                    $listTable .= "<td class='winbox' nowrap align='left'><div class='pt9'>". $group_name[$r] ."</div></td>\n";
                break;
                case 1:                                 // 資産No.
                    $listTable .= "<td class='winbox' nowrap align='center'><div class='pt9'>". $res_c[$r][$i] ."</div></td>\n";
                break;
                case 2:                                 // 名称
                    $listTable .= "<td class='winbox' nowrap align='left'><div class='pt9'>". $res_c[$r][$i] ."</div></td>\n";
                break;
                case 3:                                 // 取得金額
                    $listTable .= "<td class='winbox' nowrap align='right'><div class='pt9'>". number_format($res_c[$r][$i], 0) ."</div></td>\n";
                break;
                case 4:                                 // 取得年月
                    $listTable .= "<td class='winbox' nowrap align='left'><div class='pt9'>". format_date6($res_c[$r][$i]) ."</div></td>\n";
                break;
                case 5:                                 // 耐用年数
                    $listTable .= "<td class='winbox' nowrap align='right'><div class='pt9'>". $res_c[$r][$i] ."</div></td>\n";
                break;
                case 6:                                 // 年間率
                    $listTable .= "<td class='winbox' nowrap align='right'><div class='pt9'>". $res_c[$r][$i] ."</div></td>\n";
                break;
                case 7:                                 // 終了年月
                    $listTable .= "<td class='winbox' nowrap align='left'><div class='pt9'>". format_date6($res_c[$r][$i]) ."</div></td>\n";
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
    $file_name = "list/assemblyRate_capitalAsset_List-{$_SESSION['User_ID']}.html";
    $handle = fopen($file_name, 'w');
    fwrite($handle, $listHTML);
    fclose($handle);
    chmod($file_name, 0666);       // fileを全てrwモードにする
}
