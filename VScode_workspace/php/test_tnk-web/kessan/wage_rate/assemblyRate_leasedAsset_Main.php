<?php
//////////////////////////////////////////////////////////////////////////////
// リース資産編集 メイン assemblyRate_leasedAsset_Main.php                  //
//                       (旧 leased_asset_master_main.php)                  //
// Copyright (C) 2007-2011 Norihisa.Ooya usoumu@nitto-kohki.co.jp           //
// Changed history                                                          //
// 2007/12/13 Created  assemblyRate_leasedAsset_Main.php                    //
//            旧ファイルより各処理を関数化 コメントの位置の調整             //
//            余分な<font>タグの削除                                        //
// 2007/12/14 プログラムの最後に改行を追加                                  //
// 2007/12/29 日付データの戻り値を設定                                      //
// 2008/01/09 コピーした時日付データを渡さなかったのを修正                  //
// 2011/06/22 format_date系をtnk_funcに移動のためこちらを削除               //
//////////////////////////////////////////////////////////////////////////////
ini_set('error_reporting', E_ALL || E_STRICT);
ini_set('display_errors', '1');             // Error 表示 ON debug 用 リリース後コメント
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
    $menu->set_title('リース資産台帳の編集');
  
    $request = new Request;
    $result  = new Result;
    
    ////////////// リターンアドレス設定
    $menu->set_RetUrl($_SESSION['various_referer'] . '?wage_ym=' . $request->get('wage_ym'));             // 通常は指定する必要はない
    
    //get_group_master($result, $request);                // グループマスターの取得
    //get_leased_master ($result, $request);              // リース資産マスターの取得
    
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
    require_once ('assemblyRate_leasedAsset_View.php');

    ob_end_flush(); 
}

////////////// 処理の分岐を行う
function request_check($request, $result, $menu)
{
    $ok = true;
    if ($request->get('number') != '') $ok = leasedMaster_copy($request, $result);
    if ($request->get('del') != '') $ok = leasedMaster_del($request);
    if ($request->get('entry') != '')  $ok = leasedMaster_entry($request, $result);
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
        $request->add('annual_lease_money', '');
        $request->add('end_date', '');
        //get_group_master($result, $request);      // グループマスターの取得
        //get_leased_master ($result, $request);    // リース資産マスターの取得
    }
}

////////////// 追加・変更ロジック (合計レコード数取得前に行う)
function leasedMaster_entry($request, $result)
{
    if (getCheckAuthority(22)) {                    // 認証チェック
        $group_no = $request->get('group_no');
        $asset_no = $request->get('asset_no');
        $asset_name = $request->get('asset_name');
        $acquisition_money = $request->get('acquisition_money');
        $acquisition_date = $request->get('acquisition_date');
        $annual_lease_money = $request->get('annual_lease_money');
        $end_date = $request->get('end_date');
        if ($end_date == 0) {
            $end_date = '';
        }
        $query = sprintf("SELECT asset_no FROM leased_asset_master WHERE asset_no='%s'", $asset_no);
        $res_chk = array();
        if ( getResult($query, $res_chk) > 0 ) {    // 登録あり UPDATE 更新
            $query = sprintf("UPDATE assembly_machine_group_leased_asset SET group_no=%d, asset_no='%s', last_date=CURRENT_TIMESTAMP, last_user='%s' WHERE asset_no='%s'", $group_no, $asset_no, $_SESSION['User_ID'], $asset_no);
            if (query_affected($query) <= 0) {
                $_SESSION['s_sysmsg'] .= "リース資産No.：{$asset_no} リース資産名：{$asset_name}の変更失敗！";    // .= に注意
                $msg_flg = 'alert';
                return false;
            }                                       // グループと資産Noのデータベース変更後マスター変更
            $query = sprintf("UPDATE leased_asset_master SET asset_no='%s', asset_name='%s', acquisition_money=%d, acquisition_date=%d, annual_lease_money=%d, end_date=%d, last_date=CURRENT_TIMESTAMP, last_user='%s' 
                                WHERE asset_no='%s'", $asset_no, $asset_name, $acquisition_money, $acquisition_date, $annual_lease_money, $end_date, $_SESSION['User_ID'], $asset_no);
            if (query_affected($query) <= 0) {
                $_SESSION['s_sysmsg'] .= "リース資産No.：{$asset_no} リース資産名：{$asset_name}の変更失敗！";    // .= に注意
                $msg_flg = 'alert';
                return false;
            }
            $_SESSION['s_sysmsg'] .= "リース資産No.：{$asset_no} リース資産名：{$asset_name}の内容を変更しました！";    // .= に注意
            return true;
        } else {                                    // 登録なし INSERT 新規   
            $query = sprintf("INSERT INTO leased_asset_master (asset_no, asset_name, acquisition_money, acquisition_date, annual_lease_money, end_date, last_date, last_user)
                              VALUES ('%s', '%s', %d, %d, %d, %d, CURRENT_TIMESTAMP, '%s')",
                              $asset_no, $asset_name, $acquisition_money, $acquisition_date, $annual_lease_money, $end_date, $_SESSION['User_ID']);
            if (query_affected($query) <= 0) {
                $_SESSION['s_sysmsg'] .= "リース資産No.：{$asset_no} リース資産名：{$asset_name}の登録に失敗！";    // .= に注意
                $msg_flg = 'alert';
                return false;
            } else {                                // マスター追加後グループと資産NoのDB更新
                $query = sprintf("INSERT INTO assembly_machine_group_leased_asset (group_no, asset_no, last_date, last_user)
                                  VALUES (%d, '%s', CURRENT_TIMESTAMP, '%s')",
                                  $group_no, $asset_no, $_SESSION['User_ID']);
                if (query_affected($query) <= 0) {  // グループと資産NoDB更新失敗時はマスターも削除
                    $query = sprintf("DELETE FROM leased_asset_master WHERE asset_no = '%s'", $asset_no);
                    $_SESSION['s_sysmsg'] .= "リース資産No.：{$asset_no} リース資産名：{$asset_name}の登録に失敗！";    // .= に注意
                    $msg_flg = 'alert';
                    return false;
                } else {
                    $_SESSION['s_sysmsg'] .= "リース資産No.：{$asset_no} リース資産名：{$asset_name}を追加しました！";    // .= に注意
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
function leasedMaster_del($request)
{
    if (getCheckAuthority(22)) {    // 認証チェック
        $asset_no = $request->get('asset_no');
        $asset_name = $request->get('asset_name');
        $query = sprintf("DELETE FROM leased_asset_master WHERE asset_no = '%s'", $asset_no);
        if (query_affected($query) <= 0) {
            $_SESSION['s_sysmsg'] .= "リース資産No.：{$asset_no} リース資産名：{$asset_no}の削除に失敗！";    // .= に注意
            $msg_flg = 'alert';
            return false;
        } else {                    // マスター削除成功後グループと資産NoのDBからも削除
            $query = sprintf("DELETE FROM assembly_machine_group_leased_asset WHERE asset_no = '%s'", $asset_no);
            if (query_affected($query) <= 0) {
                $_SESSION['s_sysmsg'] .= "リース資産No.：{$asset_no} リース資産名：{$asset_name}の削除に失敗！";    // .= に注意
                $msg_flg = 'alert';
                return false;
            } else {
                $_SESSION['s_sysmsg'] .= "リース資産No.：{$asset_no} リース資産名：{$asset_name}を削除しました！";    // .= に注意
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

////////////// 表示用(一覧表)のリース資産データをSQLで取得
function get_leased_master ($result, $request)
{
    $query = "
        SELECT  groupl.group_no                AS グループ名       -- 0
            ,   groupl.asset_no                AS 固定資産No       -- 1
            ,   lmaster.asset_name             AS 資産名称         -- 2
            ,   lmaster.acquisition_money      AS 取得金額         -- 3
            ,   lmaster.acquisition_date       AS 取得年月         -- 4
            ,   lmaster.annual_lease_money     AS 年間リース料     -- 5
            ,   lmaster.end_date               AS 終了年月         -- 6
        FROM
            assembly_machine_group_leased_asset AS groupl
        LEFT OUTER JOIN
            leased_asset_master AS lmaster
        ON (groupl.asset_no = lmaster.asset_no)
        ORDER BY
            group_no
        ";
    $res = array();
    if (($rows = getResultWithField2($query, $field, $res)) <= 0) {
        $_SESSION['s_sysmsg'] = "登録がありません！";
    } else {
        $num = count($field);
        $result->add_array2('res_l', $res);
        $result->add_array2('field_l', $field);
        $result->add('num_l', $num);
        $result->add('rows_l', $rows);
    }
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
////////////// コピーのリンクが押された時
function leasedMaster_copy($request, $result)
{
    $r = $request->get('number');
    $res = $result->get_array2('res_l');
    $group_no           = $res[$r][0];
    $asset_no           = $res[$r][1];
    $asset_name         = $res[$r][2];
    $acquisition_money  = $res[$r][3];
    $acquisition_date   = $res[$r][4];
    $annual_lease_money = $res[$r][5];
    if ($res[$r][6] == 0) {
        $end_date = '';
    } else {
        $end_date = $res[$r][6];
    }
    $request->add('group_no', $group_no);
    $request->add('asset_no', $asset_no);
    $request->add('asset_name', $asset_name);
    $request->add('acquisition_money', $acquisition_money);
    $request->add('acquisition_date', $acquisition_date);
    $request->add('annual_lease_money', $annual_lease_money);
    $request->add('end_date', $end_date);
}

////////////// リース資産照会画面のHTMLの作成
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
    $listTable .= "    <form name='entry_form' action='assemblyRate_leasedAsset_Main.php' method='post'>\n";
    $listTable .= "        <table bgcolor='#d6d3ce' width='300' align='center' border='1' cellspacing='0' cellpadding='3'>\n";
    $listTable .= "            <tr><td> <!----------- ダミー(デザイン用) ------------>\n";
    $listTable .= "        <table class='winbox_field' width='100%' bgcolor='#d6d3ce' align='center' border='1' cellspacing='0' cellpadding='3'>\n";
    $listTable .= "        <THEAD>\n";
    $listTable .= "            <!-- テーブル ヘッダーの表示 -->\n";
    $listTable .= "            <tr>\n";
    $listTable .= "                <td bgcolor='#ffffc6' align='center' colspan='20'>\n";
    $listTable .= "                    リース資産台帳\n";
    $listTable .= "                </td>\n";
    $listTable .= "            </tr>\n";
    $listTable .= "            <tr>\n";
    $listTable .= "                <th class='winbox' nowrap width='10'>No</th>        <!-- 行ナンバーの表示 -->\n";
    $field = array(
        "グループ名",
        "リース資産No.",
        "資産名称",
        "取得金額",
        "取得年月",
        "年間リース",
        "終了年月",
    );
    for ($i=0; $i<count($field); $i++) {    // フィールド数分繰返し\n";
        $listTable .= "        <th class='winbox' nowrap>". $field[$i] ."</th>\n";
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
        [1, 1, "dummy", 100, 202201, 1, 202601],
        [2, 2, "dummy", 200, 202202, 2, 202602],
        [3, 3, "dummy", 300, 202203, 3, 202603],
        [4, 4, "dummy", 400, 202204, 4, 202604],
        [5, 5, "dummy", 500, 202205, 5, 202605],
        [6, 6, "dummy", 600, 202206, 6, 202606],
        [7, 7, "dummy", 700, 202207, 7, 202607],
        [8, 8, "dummy", 800, 202208, 8, 202608],
        [9, 9, "dummy", 900, 202209, 9, 202609],
        [10,10, "dummy", 1000, 202210, 10, 202610],
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
    for ($r=0; $r<count($res); $r++) {
        $listTable .= "        <tr>\n";
        $listTable .= "            <td class='winbox' nowrap align='right'>\n";
        $listTable .= "                <a href='../assemblyRate_leasedAsset_Main.php?number=". $r ."&wage_ym=". $request->get('wage_ym') ."' target='_parent' style='text-decoration:none;'>\n";
        $cnum = $r + 1;
        $listTable .= "                ". $cnum ."\n";
        $listTable .= "                </a>\n";
        $listTable .= "            </td>    <!-- 行ナンバーの表示 -->\n";
        for ($i=0; $i<count($res); $i++) {    // レコード数分繰返し
            switch ($i) {
                case 0:                                 // グループ
                    $listTable .= "<td class='winbox' nowrap align='left'><div class='pt9'>". $group_name[$r] ."</div></td>\n";
                    break;
                case 1:                                 // 資産No.
                    $listTable .= "<td class='winbox' nowrap align='center'><div class='pt9'>". $res[$r][$i] ."</div></td>\n";
                    break;
                case 2:                                 // 名称
                    $listTable .= "<td class='winbox' nowrap align='left'><div class='pt9'>". $res[$r][$i] ."</div></td>\n";
                    break;
                case 3:                                 // 取得金額
                    $listTable .= "<td class='winbox' nowrap align='right'><div class='pt9'>". number_format($res[$r][$i], 0) ."</div></td>\n";
                    break;
                case 4:                                 // 取得年月
                    $listTable .= "<td class='winbox' nowrap align='left'><div class='pt9'>". format_date6($res[$r][$i]) ."</div></td>\n";
                    break;
                case 5:                                 // 年間リース料
                    $listTable .= "<td class='winbox' nowrap align='right'><div class='pt9'>". number_format($res[$r][$i], 0) ."</div></td>\n";
                    break;
                case 6:                                 // 終了年月
                    $listTable .= "<td class='winbox' nowrap align='left'><div class='pt9'>". format_date6($res[$r][$i]) ."</div></td>\n";
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
    $file_name = "list/assemblyRate_leasedAsset_List-test.html";
    $handle = fopen($file_name, 'w');
    fwrite($handle, $listHTML);
    fclose($handle);
    chmod($file_name, 0666);       // fileを全てrwモードにする
}
